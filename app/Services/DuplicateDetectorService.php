<?php

namespace App\Services;

/**
 * DuplicateDetectorService
 *
 * Mendeteksi duplikasi atau kemiripan tinggi antar butir soal lintas bank soal.
 * Menggunakan pendekatan Bag-of-Words fingerprint (MD5) yang ringan —
 * tidak membutuhkan ekstensi PHP tambahan.
 *
 * Cara kerja:
 *  1. Strip HTML, lowercase, hilangkan stopword
 *  2. Urutkan kata-kata unik yang tersisa
 *  3. MD5 dari string hasil → fingerprint
 *  4. Bandingkan fingerprint dengan seluruh soal di bank lain
 *
 * Keterbatasan: tidak mendeteksi kemiripan parsial (parafrase).
 * Untuk deteksi parafrase diperlukan model NLP — di luar scope ini.
 */
class DuplicateDetectorService
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ================================================================
    // API PUBLIK
    // ================================================================

    /**
     * Hitung fingerprint soal dari teks HTML.
     */
    public function fingerprint(string $html): string
    {
        $teks  = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $teks  = mb_strtolower($teks, 'UTF-8');
        $teks  = preg_replace('/[^a-z0-9\s]/u', ' ', $teks);
        $teks  = preg_replace('/\s+/', ' ', trim($teks));

        $kata  = explode(' ', $teks);
        $kata  = array_filter($kata, fn($w) => mb_strlen($w) > 2);
        $kata  = array_diff(array_unique($kata), $this->stopwords());
        sort($kata);

        return md5(implode(' ', $kata));
    }

    /**
     * Periksa apakah soal sudah ada di bank lain (duplikat identik).
     *
     * @param  string   $soalHtml    Teks soal lengkap (HTML boleh)
     * @param  int      $bankId      Bank soal milik soal ini (dikecualikan)
     * @param  int|null $excludeId   ID soal yang sedang diedit (dikecualikan)
     * @return array    ['is_duplicate', 'fingerprint', 'matches']
     */
    public function cek(string $soalHtml, int $bankId, ?int $excludeId = null): array
    {
        $hash = $this->fingerprint($soalHtml);

        if (empty($hash)) {
            return ['is_duplicate' => false, 'fingerprint' => $hash, 'matches' => []];
        }

        $builder = $this->db->table('cbt_soal s')
            ->select('s.id_soal, s.nomor_soal, s.jenis, b.bank_kode, m.nama_mapel, b.id_bank')
            ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id', 'left')
            ->join('master_mapel m',  'm.id_mapel = b.bank_mapel_id', 'left')
            ->where('s.soal_hash', $hash)
            ->where('s.bank_id !=', $bankId);

        if ($excludeId) {
            $builder->where('s.id_soal !=', $excludeId);
        }

        $matches = $builder->limit(10)->get()->getResult();

        return [
            'is_duplicate' => count($matches) > 0,
            'fingerprint'  => $hash,
            'matches'      => $matches,
        ];
    }

    /**
     * Hitung dan simpan hash untuk semua soal dalam satu bank.
     * Dipanggil sekali setelah bank soal selesai diisi atau saat maintenance.
     *
     * @return int Jumlah soal yang diperbarui hashnya
     */
    public function rehashBank(int $bankId): int
    {
        $soals = $this->db->table('cbt_soal')
            ->select('id_soal, soal')
            ->where('bank_id', $bankId)
            ->where('soal IS NOT NULL')
            ->get()->getResult();

        $updated = 0;
        foreach ($soals as $soal) {
            $hash = $this->fingerprint((string) ($soal->soal ?? ''));
            if (empty($hash)) {
                continue;
            }
            $this->db->table('cbt_soal')
                ->where('id_soal', $soal->id_soal)
                ->update(['soal_hash' => $hash]);
            $updated++;
        }
        return $updated;
    }

    /**
     * Hitung hash untuk semua soal yang belum punya hash (batch maintenance).
     *
     * @param  int $limit  Maksimal soal yang diproses per pemanggilan
     * @return int
     */
    public function rehashMissing(int $limit = 500): int
    {
        $soals = $this->db->table('cbt_soal')
            ->select('id_soal, soal')
            ->where('soal_hash IS NULL')
            ->where('soal IS NOT NULL')
            ->limit($limit)
            ->get()->getResult();

        $updated = 0;
        foreach ($soals as $soal) {
            $hash = $this->fingerprint((string) ($soal->soal ?? ''));
            if (empty($hash)) {
                continue;
            }
            $this->db->table('cbt_soal')
                ->where('id_soal', $soal->id_soal)
                ->update(['soal_hash' => $hash]);
            $updated++;
        }
        return $updated;
    }

    /**
     * Laporan duplikasi seluruh bank soal aktif dalam satu periode.
     * Mengembalikan hash yang muncul di lebih dari satu bank soal.
     *
     * @return array  Array objek: soal_hash, jml_duplikat, lokasi (bank_kode#nomor)
     */
    public function laporanDuplikasi(int $tpId, int $smtId): array
    {
        // Cari hash yang muncul di lebih dari satu bank berbeda
        $query = $this->db->query("
            SELECT
                s.soal_hash,
                COUNT(DISTINCT s.bank_id)                AS jml_bank,
                COUNT(*)                                  AS jml_soal,
                GROUP_CONCAT(
                    CONCAT(b.bank_kode, '#', s.nomor_soal,
                           ' (', IFNULL(m.nama_mapel,'?'), ')')
                    ORDER BY b.bank_kode
                    SEPARATOR ' | '
                )                                        AS lokasi,
                LEFT(MIN(s.soal), 100)                   AS cuplikan
            FROM cbt_soal s
            JOIN cbt_bank_soal b ON b.id_bank = s.bank_id
            LEFT JOIN master_mapel m ON m.id_mapel = b.bank_mapel_id
            WHERE b.id_tp = ?
              AND b.id_smt = ?
              AND s.soal_hash IS NOT NULL
            GROUP BY s.soal_hash
            HAVING jml_bank > 1
            ORDER BY jml_bank DESC, jml_soal DESC
            LIMIT 100
        ", [$tpId, $smtId]);

        return $query ? $query->getResult() : [];
    }

    /**
     * Laporan duplikasi dalam satu bank soal (nomor soal yang persis sama).
     */
    public function duplikasiDalamBank(int $bankId): array
    {
        return $this->db->query("
            SELECT soal_hash, COUNT(*) AS jml,
                   GROUP_CONCAT(nomor_soal ORDER BY nomor_soal) AS nomor_soal_list
            FROM cbt_soal
            WHERE bank_id = ?
              AND soal_hash IS NOT NULL
            GROUP BY soal_hash
            HAVING jml > 1
        ", [$bankId])->getResult();
    }

    // ================================================================
    // PRIVATE
    // ================================================================

    /**
     * Stopword Bahasa Indonesia yang dikecualikan dari fingerprint.
     * Daftar diperpendek agar fingerprint tetap unik per konten soal.
     */
    private function stopwords(): array
    {
        return [
            'yang','dan','di','ke','dari','adalah','ini','itu','dengan',
            'untuk','pada','dalam','tidak','ada','atau','jika','maka',
            'sebagai','dapat','akan','telah','sudah','oleh','juga','lebih',
            'antara','sebuah','setiap','berikut','manakah','pernyataan',
            'dibawah','benar','salah','tepat','nilai','soal','berikut',
            'tentang','berdasarkan','tersebut','suatu','satu','dua','tiga',
        ];
    }
}
