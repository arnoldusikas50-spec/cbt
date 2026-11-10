<?php

namespace App\Services;

/**
 * ItemAnalysisService
 *
 * Menghitung statistik kualitas butir soal berdasarkan data jawaban nyata siswa.
 *
 * Metode yang diimplementasikan:
 *  - Indeks Kesulitan (p): proporsi siswa yang menjawab benar
 *  - Daya Beda (D): metode 27% kelompok atas-bawah (klasik BSNK)
 *  - Tag Kesulitan Aktual: mudah / sedang / sulit berdasarkan p empiris
 *  - Analisis Distraktor: efektivitas setiap opsi PG
 *
 * Referensi: Allen & Yen (1979), Gronlund & Waugh (2009), Panduan BSNK 2023
 */
class ItemAnalysisService
{
    private \CodeIgniter\Database\BaseConnection $db;

    /** Threshold 27% untuk kelompok atas dan bawah (standar klasik) */
    private const PCT_KELOMPOK = 0.27;

    /** Minimum siswa untuk analisis valid */
    private const MIN_SISWA = 10;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ================================================================
    // API PUBLIK
    // ================================================================

    /**
     * Hitung statistik satu butir soal berdasarkan satu jadwal.
     *
     * @param int $soalId
     * @param int $jadwalId
     * @return array|null  null jika data tidak cukup
     */
    public function hitungSatuSoal(int $soalId, int $jadwalId): ?array
    {
        // Ambil kunci jawaban & jenis soal
        $soal = $this->db->table('cbt_soal')
            ->select('id_soal, jenis, jawaban, bank_id')
            ->where('id_soal', $soalId)
            ->get()->getRow();

        if (!$soal) {
            return null;
        }

        // Ambil semua jawaban siswa + total skor mereka
        $rows = $this->db->table('cbt_jawaban j')
            ->select('j.id_siswa, j.jawaban as jwb_siswa, n.pg_nilai as total_skor')
            ->join('cbt_nilai n', 'j.id_siswa = n.id_siswa AND j.id_jadwal = n.id_jadwal', 'left')
            ->where('j.id_soal', $soalId)
            ->where('j.id_jadwal', $jadwalId)
            ->get()->getResult();

        $total = count($rows);
        if ($total < self::MIN_SISWA) {
            return null; // Tidak cukup data untuk analisis yang bermakna
        }

        $kunci      = $soal->jawaban ?? '';
        $benarFlags = [];
        $jawabanCount = []; // Untuk analisis distraktor

        foreach ($rows as $row) {
            $jwbSiswa = strtolower(trim((string) ($row->jwb_siswa ?? '')));
            $kunciNorm = strtolower(trim((string) $kunci));
            $benar = ($jwbSiswa === $kunciNorm && $jwbSiswa !== '');

            $benarFlags[] = [
                'benar'       => $benar,
                'total_skor'  => (float) ($row->total_skor ?? 0),
                'jawaban'     => $jwbSiswa,
            ];

            // Hitung frekuensi tiap opsi (untuk distraktor)
            $jawabanCount[$jwbSiswa] = ($jawabanCount[$jwbSiswa] ?? 0) + 1;
        }

        // --- Hitung p (Indeks Kesulitan) ---
        $jumlahBenar = count(array_filter($benarFlags, fn($r) => $r['benar']));
        $p = $jumlahBenar / $total;

        // --- Hitung D (Daya Beda) ---
        $d = $this->hitungDayaBeda($benarFlags);

        // --- Tag kesulitan berdasarkan p empiris ---
        $tag = match (true) {
            $p >= 0.70 => 'mudah',
            $p >= 0.30 => 'sedang',
            default    => 'sulit',
        };

        // --- Analisis Distraktor (hanya untuk PG) ---
        $distraktor = null;
        if ((int) $soal->jenis === 1) {
            $distraktor = $this->analisisDistraktor($jawabanCount, $total, strtolower(trim($kunci)));
        }

        $result = [
            'soal_id'      => $soalId,
            'total_siswa'  => $total,
            'jumlah_benar' => $jumlahBenar,
            'p'            => round($p, 3),
            'd'            => round($d, 3),
            'tag'          => $tag,
            'distraktor'   => $distraktor,
            'interpretasi' => $this->interpretasi($p, $d),
        ];

        // Simpan ke database
        $this->simpanHasil($soalId, $result);

        return $result;
    }

    /**
     * Hitung statistik massal untuk semua soal PG dalam satu jadwal.
     * Guard: mengembalikan error jika kolom stat_* belum ada.
     */
    public function hitungMassal(int $jadwalId): array
    {
        // Guard: kolom stat_* baru ada setelah migrasi dijalankan
        if (!$this->db->fieldExists('stat_d', 'cbt_soal')) {
            return [
                'processed' => 0,
                'skipped'   => 0,
                'results'   => [],
                'error'     => 'Kolom analisis belum ada. Jalankan migrasi terlebih dahulu: php spark migrate',
            ];
        }

        // [1] Ambil data jadwal → bank → list soal
        $jadwal = $this->db->table('cbt_jadwal j')
            ->select('j.id_jadwal, j.id_bank')
            ->where('j.id_jadwal', $jadwalId)
            ->get()->getRow();

        if (!$jadwal) {
            return ['processed' => 0, 'skipped' => 0, 'results' => [], 'error' => 'Jadwal tidak ditemukan'];
        }

        // [2] Ambil semua soal PG (jenis=1) di bank ini
        $soals = $this->db->table('cbt_soal')
            ->select('id_soal, jawaban, jenis')
            ->where('bank_id', $jadwal->id_bank)
            ->where('jenis', 1)   // Item analysis klasik hanya untuk PG
            ->where('tampilkan', 1)
            ->get()->getResult();

        if (empty($soals)) {
            return ['processed' => 0, 'skipped' => 0, 'results' => [], 'error' => 'Tidak ada soal PG'];
        }

        $soalIds  = array_column($soals, 'id_soal');
        $soalMap  = [];
        foreach ($soals as $s) {
            $soalMap[$s->id_soal] = $s;
        }

        // [3] Bulk fetch semua jawaban siswa + total skor (1 query)
        $allJawaban = $this->db->table('cbt_jawaban j')
            ->select('j.id_siswa, j.id_soal, j.jawaban as jwb_siswa, n.pg_nilai as total_skor')
            ->join('cbt_nilai n', 'j.id_siswa = n.id_siswa AND j.id_jadwal = n.id_jadwal', 'left')
            ->where('j.id_jadwal', $jadwalId)
            ->whereIn('j.id_soal', $soalIds)
            ->get()->getResult();

        // Kelompokkan jawaban per soal
        $jawabanPerSoal = [];
        foreach ($allJawaban as $j) {
            $jawabanPerSoal[$j->id_soal][] = [
                'benar'      => strtolower(trim((string) $j->jwb_siswa)) ===
                                strtolower(trim((string) ($soalMap[$j->id_soal]->jawaban ?? ''))),
                'total_skor' => (float) ($j->total_skor ?? 0),
                'jawaban'    => strtolower(trim((string) $j->jwb_siswa)),
            ];
        }

        // [4] Hitung statistik per soal
        $results   = [];
        $processed = 0;
        $skipped   = 0;

        foreach ($soals as $soal) {
            $rows  = $jawabanPerSoal[$soal->id_soal] ?? [];
            $total = count($rows);

            if ($total < self::MIN_SISWA) {
                $skipped++;
                continue;
            }

            $kunci       = strtolower(trim((string) $soal->jawaban));
            $jmlBenar    = count(array_filter($rows, fn($r) => $r['benar']));
            $p           = $jmlBenar / $total;
            $d           = $this->hitungDayaBeda($rows);
            $tag         = match (true) {
                $p >= 0.70 => 'mudah',
                $p >= 0.30 => 'sedang',
                default    => 'sulit',
            };

            // Frekuensi opsi untuk distraktor
            $opsiCount = [];
            foreach ($rows as $r) {
                $opsiCount[$r['jawaban']] = ($opsiCount[$r['jawaban']] ?? 0) + 1;
            }

            $stat = [
                'soal_id'      => $soal->id_soal,
                'total_siswa'  => $total,
                'jumlah_benar' => $jmlBenar,
                'p'            => round($p, 3),
                'd'            => round($d, 3),
                'tag'          => $tag,
                'distraktor'   => $this->analisisDistraktor($opsiCount, $total, $kunci),
                'interpretasi' => $this->interpretasi($p, $d),
            ];

            $this->simpanHasil($soal->id_soal, $stat);
            $results[$soal->id_soal] = $stat;
            $processed++;
        }

        log_message('info', "[ItemAnalysis] Jadwal {$jadwalId}: {$processed} soal dianalisis, {$skipped} dilewati.");

        return [
            'processed' => $processed,
            'skipped'   => $skipped,
            'results'   => $results,
        ];
    }

    /**
     * Ambil statistik soal-soal dalam satu bank, lengkap dengan info soal.
     * Digunakan untuk tampilan tabel analisis di view.
     * Guard: mengembalikan [] jika kolom stat_* belum ada.
     */
    public function getLaporanBank(int $bankId): array
    {
        // Kolom stat_* baru ada setelah migrasi
        if (!$this->db->fieldExists('stat_d', 'cbt_soal')) {
            return [];
        }

        try {
            $q = $this->db->table('cbt_soal s')
                ->select('
                    s.id_soal,
                    s.nomor_soal,
                    s.jenis,
                    s.kesulitan   AS level_kisi,
                    s.stat_p,
                    s.stat_d,
                    s.tag_kesulitan,
                    s.stat_used_count,
                    s.stat_last_calc,
                    LEFT(s.soal, 120) AS cuplikan_soal
                ')
                ->where('s.bank_id', $bankId)
                ->where('s.tampilkan', 1)
                ->orderBy('s.jenis', 'ASC')
                ->orderBy('s.nomor_soal', 'ASC')
                ->get();

            return $q ? $q->getResult() : [];
        } catch (\Throwable $e) {
            log_message('error', '[ItemAnalysisService::getLaporanBank] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ringkasan statistik satu bank soal.
     * Guard: mengembalikan object kosong jika kolom stat_* belum ada.
     */
    public function getRingkasanBank(int $bankId): object
    {
        // Kolom stat_d baru ada setelah migrasi Sprint 1-2
        if (!$this->db->fieldExists('stat_d', 'cbt_soal')) {
            return (object) [
                'total'            => $this->db->table('cbt_soal')
                    ->where('bank_id', $bankId)->where('tampilkan', 1)->countAllResults(),
                'sudah_dianalisis' => 0,
                'avg_p'            => null,
                'avg_d'            => null,
                'jml_mudah'        => 0,
                'jml_sedang'       => 0,
                'jml_sulit'        => 0,
                'soal_perlu_revisi'=> 0,
            ];
        }

        try {
            $q = $this->db->table('cbt_soal')
                ->select('
                    COUNT(*)                                  AS total,
                    SUM(stat_p IS NOT NULL)                   AS sudah_dianalisis,
                    AVG(stat_p)                               AS avg_p,
                    AVG(stat_d)                               AS avg_d,
                    SUM(tag_kesulitan = "mudah")              AS jml_mudah,
                    SUM(tag_kesulitan = "sedang")             AS jml_sedang,
                    SUM(tag_kesulitan = "sulit")              AS jml_sulit,
                    SUM(stat_d IS NOT NULL AND stat_d < 0.20) AS soal_perlu_revisi
                ')
                ->where('bank_id', $bankId)
                ->where('tampilkan', 1)
                ->get();

            return ($q ? $q->getRow() : null) ?? (object) [];
        } catch (\Throwable $e) {
            log_message('error', '[ItemAnalysisService::getRingkasanBank] ' . $e->getMessage());
            return (object) [];
        }
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    /**
     * Hitung daya beda menggunakan metode 27% kelompok atas-bawah.
     *
     * @param array $rows  Array ['benar' => bool, 'total_skor' => float]
     */
    private function hitungDayaBeda(array $rows): float
    {
        if (empty($rows)) {
            return 0.0;
        }

        // Urutkan berdasarkan skor total (tinggi ke rendah)
        usort($rows, fn($a, $b) => $b['total_skor'] <=> $a['total_skor']);

        $n    = count($rows);
        $n27  = max(1, (int) round(self::PCT_KELOMPOK * $n));

        $atas  = array_slice($rows, 0, $n27);
        $bawah = array_slice($rows, -$n27);

        $benarAtas  = count(array_filter($atas,  fn($r) => $r['benar']));
        $benarBawah = count(array_filter($bawah, fn($r) => $r['benar']));

        return ($benarAtas - $benarBawah) / $n27;
    }

    /**
     * Hitung efektivitas distraktor untuk soal PG.
     *
     * @param array  $opsiCount  ['a' => 5, 'b' => 12, ...]
     * @param int    $total      Total siswa yang menjawab
     * @param string $kunci      Opsi kunci (a/b/c/d/e)
     * @return array
     */
    private function analisisDistraktor(array $opsiCount, int $total, string $kunci): array
    {
        $result = [];
        foreach ($opsiCount as $opsi => $frekuensi) {
            if ($opsi === '') {
                continue;
            }
            $pct    = round(($frekuensi / $total) * 100, 1);
            $isKey  = ($opsi === $kunci);
            $efektif = !$isKey && $pct >= 5.0; // Distraktor efektif jika dipilih ≥ 5%

            $result[$opsi] = [
                'frekuensi' => $frekuensi,
                'pct'       => $pct,
                'is_kunci'  => $isKey,
                'efektif'   => $efektif,
                'status'    => $isKey ? 'kunci' : ($efektif ? 'efektif' : 'tidak_efektif'),
            ];
        }
        // Urutkan berdasarkan nama opsi
        ksort($result);
        return $result;
    }

    /**
     * Buat teks interpretasi kualitas butir soal.
     */
    private function interpretasi(float $p, float $d): array
    {
        $masalah = [];
        $saran   = [];

        // Indeks Kesulitan
        if ($p < 0.20) {
            $masalah[] = 'Soal terlalu sulit';
            $saran[]   = 'Tinjau ulang formulasi soal atau pilihan jawaban';
        } elseif ($p > 0.80) {
            $masalah[] = 'Soal terlalu mudah';
            $saran[]   = 'Pertimbangkan menaikkan level kognitif (Bloom)';
        }

        // Daya Beda
        if ($d < 0) {
            $masalah[] = 'Daya beda negatif — siswa pandai lebih banyak salah';
            $saran[]   = 'Periksa kunci jawaban, kemungkinan kunci salah';
        } elseif ($d < 0.20) {
            $masalah[] = 'Daya beda rendah — soal tidak membedakan kemampuan siswa';
            $saran[]   = 'Revisi stem soal agar lebih diskriminatif';
        }

        $kualitas = match (true) {
            $d >= 0.40 && $p >= 0.30 && $p <= 0.70 => 'baik',
            $d >= 0.20                               => 'cukup',
            $d >= 0.00                               => 'perlu_revisi',
            default                                  => 'buruk',
        };

        return [
            'kualitas' => $kualitas,
            'masalah'  => $masalah,
            'saran'    => $saran,
        ];
    }

    /**
     * Simpan hasil analisis ke kolom stat_* di cbt_soal.
     * Skip jika kolom belum ada (migrasi belum dijalankan).
     */
    private function simpanHasil(int $soalId, array $stat): void
    {
        if (!$this->db->fieldExists('stat_d', 'cbt_soal')) {
            return;
        }

        $this->db->query(
            'UPDATE cbt_soal
             SET stat_p          = ?,
                 stat_d          = ?,
                 tag_kesulitan   = ?,
                 stat_last_calc  = NOW(),
                 stat_used_count = COALESCE(stat_used_count, 0) + 1
             WHERE id_soal = ?',
            [
                $stat['p'],
                $stat['d'],
                $stat['tag'],
                $soalId,
            ]
        );
    }
}
