<?php

namespace App\Models;

use App\Traits\SiswaAktifTrait;
use CodeIgniter\Model;

/**
 * CbtSiswaModel - Migrated from CI3 Cbt_siswa_model
 * Handles student CBT records (exam sessions)
 */
class CbtSiswaModel extends Model
{
    use SiswaAktifTrait;

    protected $table = 'cbt_siswa';
    protected $primaryKey = 'id_cbt_siswa';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_siswa',
        'id_jadwal',
        'id_bank',
        'mulai',
        'selesai',
        'lama_ujian',
        'status',
        'reset_izin',
        'reset_waktu',
        'ip_address',
        'user_agent',
        'device_type',
        'queue_number',
        'remedial_attempt'  // Jumlah percobaan remedial
    ];

    protected $useTimestamps = false;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get CBT siswa record
     */
    public function getCbtSiswa(int $idSiswa, int $idJadwal): ?object
    {
        return $this->where('id_siswa', $idSiswa)
            ->where('id_jadwal', $idJadwal)
            ->first();
    }

    /**
     * Get all CBT siswa by jadwal
     *
     * Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang ditampilkan.
     */
    public function getAllByJadwal(int $idJadwal): array
    {
        $builder = $this->db->table('cbt_siswa a')
            ->select('a.*, b.nama, b.nis, b.nisn, c.nama_kelas')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->join('kelas_siswa d', 'a.id_siswa = d.id_siswa', 'left')
            ->join('master_kelas c', 'd.id_kelas = c.id_kelas', 'left')
            ->where('a.id_jadwal', $idJadwal);
        $this->onlySiswaAktif($builder, 'a');

        return $builder->get()->getResult();
    }

    /**
     * Get siswa sedang ujian (in progress)
     */
    public function getSiswaSedangUjian(int $idJadwal): array
    {
        $builder = $this->db->table('cbt_siswa a')
            ->select('a.*, b.nama, b.nis, b.nisn, c.nama_kelas')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->join('kelas_siswa d', 'a.id_siswa = d.id_siswa', 'left')
            ->join('master_kelas c', 'd.id_kelas = c.id_kelas', 'left')
            ->where('a.id_jadwal', $idJadwal)
            ->where('a.mulai IS NOT NULL')
            ->where('a.selesai', null);
        $this->onlySiswaAktif($builder, 'a');

        return $builder->get()->getResult();
    }

    /**
     * Get siswa sudah selesai ujian
     */
    public function getSiswaSelesaiUjian(int $idJadwal): array
    {
        $builder = $this->db->table('cbt_siswa a')
            ->select('a.*, b.nama, b.nis, b.nisn, c.nama_kelas')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->join('kelas_siswa d', 'a.id_siswa = d.id_siswa', 'left')
            ->join('master_kelas c', 'd.id_kelas = c.id_kelas', 'left')
            ->where('a.id_jadwal', $idJadwal)
            ->where('a.selesai IS NOT NULL');
        $this->onlySiswaAktif($builder, 'a');

        return $builder->get()->getResult();
    }

    /**
     * Get any active (started but not finished) exam for a student
     * Optimized to use index idx_siswa_aktif
     */
    public function getActiveExam(int $idSiswa): ?object
    {
        return $this->db->table('cbt_siswa')
            ->select('*')
            ->where('id_siswa', $idSiswa)
            ->where('mulai IS NOT NULL')
            ->where('selesai IS NULL')
            ->where('status', '0')
            ->limit(1)
            ->get()
            ->getRow();
    }

    /**
     * Start exam — OPTIMASI: gunakan INSERT IGNORE + UPDATE agar atomik.
     * Menghindari SELECT + INSERT race condition saat banyak siswa start bersamaan.
     */
    public function startExam(int $idSiswa, int $idJadwal, int $idBank): int
    {
        $now = date('Y-m-d H:i:s');

        // INSERT IGNORE: tidak gagal jika sudah ada (karena UNIQUE key)
        // Hanya insert jika belum ada record sama sekali
        $this->db->query(
            "INSERT IGNORE INTO cbt_siswa (id_siswa, id_jadwal, id_bank, mulai, status)
             VALUES (?, ?, ?, ?, '0')",
            [$idSiswa, $idJadwal, $idBank, $now]
        );

        // Ambil record yang ada (bisa hasil insert baru atau yang lama)
        $existing = $this->getCbtSiswa($idSiswa, $idJadwal);

        if ($existing && empty($existing->mulai)) {
            // Sudah ada tapi mulai masih kosong (di-reset) → set waktu mulai
            $this->update($existing->id_cbt_siswa, ['mulai' => $now]);
        }

        return $existing ? (int) $existing->id_cbt_siswa : (int) $this->db->insertID();
    }

    /**
     * End exam (set selesai time)
     */
    public function endExam(int $idCbtSiswa): bool
    {
        $cbtSiswa = $this->find($idCbtSiswa);

        if (!$cbtSiswa) {
            return false;
        }

        $mulai = strtotime($cbtSiswa->mulai);
        $selesai = time();
        $lamaUjian = gmdate('H:i:s', $selesai - $mulai);

        return $this->update($idCbtSiswa, [
            'selesai' => date('Y-m-d H:i:s'),
            'lama_ujian' => $lamaUjian,
            'status' => '1'
        ]);
    }

    /**
     * Reset selesai status (allow continue exam if time still available)
     */
    public function resetSelesai(int $idCbtSiswa): bool
    {
        return $this->update($idCbtSiswa, [
            'selesai' => null,
            'status' => '0'
        ]);
    }

    /**
     * Reset login (allow re-login from different device)
     */
    public function resetLogin(int $idCbtSiswa): bool
    {
        return $this->update($idCbtSiswa, [
            'reset_izin' => '1'
        ]);
    }

    /**
     * Reset waktu (extend exam time)
     */
    public function resetWaktu(int $idCbtSiswa, int $additionalMinutes = 15): bool
    {
        $cbtSiswa = $this->find($idCbtSiswa);

        if (!$cbtSiswa) {
            return false;
        }

        $currentReset = (int) ($cbtSiswa->reset_waktu ?? 0);

        return $this->update($idCbtSiswa, [
            'reset_waktu' => $currentReset + $additionalMinutes
        ]);
    }

    /**
     * Count statistics for jadwal — OPTIMASI: 4 query → 1 query dengan CASE
     */
    public function getStatistikJadwal(int $idJadwal): object
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*)                                                    AS total,
                SUM(CASE WHEN mulai IS NOT NULL AND selesai IS NULL THEN 1 ELSE 0 END) AS sedang_ujian,
                SUM(CASE WHEN selesai IS NOT NULL                  THEN 1 ELSE 0 END) AS selesai,
                SUM(CASE WHEN mulai IS NULL                        THEN 1 ELSE 0 END) AS belum_mulai
             FROM cbt_siswa
             WHERE id_jadwal = ?",
            [$idJadwal]
        )->getRow();

        return (object) [
            'total'        => (int) ($row->total        ?? 0),
            'sedang_ujian' => (int) ($row->sedang_ujian ?? 0),
            'selesai'      => (int) ($row->selesai      ?? 0),
            'belum_mulai'  => (int) ($row->belum_mulai  ?? 0),
        ];
    }

    // ==========================================
    // REMEDIAL FUNCTIONS
    // ==========================================

    /**
     * Mulai sesi remedial — OPTIMASI: find + update → single UPDATE dengan counter
     */
    public function startRemedial(int $idCbtSiswa): bool
    {
        // Gunakan SQL expression untuk increment counter secara atomik
        // Menghindari SELECT + UPDATE race condition
        $result = $this->db->query(
            "UPDATE cbt_siswa
             SET selesai          = NULL,
                 status           = '0',
                 mulai            = NOW(),
                 lama_ujian       = '00:00:00',
                 remedial_attempt = COALESCE(remedial_attempt, 0) + 1
             WHERE id_cbt_siswa = ?",
            [$idCbtSiswa]
        );

        return $result && $this->db->affectedRows() > 0;
    }

    /**
     * Cek apakah siswa eligible untuk remedial (nilai < KKM).
     * OPTIMASI: reuse getSoalDenganStatus agar tidak ada query ganda
     * saat hasilUjian memanggil keduanya berurutan.
     *
     * @param int   $idSiswa
     * @param int   $idJadwal
     * @param float $kkm
     * @param int   $idBank   Opsional — jika sudah diketahui, skip query jadwal
     */
    public function cekEligibleRemedial(int $idSiswa, int $idJadwal, float $kkm = 70.0, int $idBank = 0): array
    {
        // Cari id_bank jika belum diberikan
        if ($idBank <= 0) {
            $jadwal = $this->db->table('cbt_jadwal')->select('id_bank')->where('id_jadwal', $idJadwal)->get()->getRow();
            $idBank = (int) ($jadwal->id_bank ?? 0);
        }

        if ($idBank <= 0) {
            return [
                'eligible'      => false,
                'nilai'         => 0,
                'kkm'           => $kkm,
                'total_soal'    => 0,
                'jumlah_benar'  => 0,
                'jumlah_salah'  => 0,
                'soal_benar'    => [],
                'soal_salah'    => [],
            ];
        }

        // Gunakan getSoalDenganStatus (sudah 1-query JOIN)
        $soalStatus = $this->getSoalDenganStatus($idSiswa, $idJadwal, $idBank);

        $soalBenar = [];
        $soalSalah = [];

        foreach ($soalStatus as $s) {
            // Sertakan field lengkap agar view hasil (detail jawaban) tidak
            // error "Undefined array key" saat mengakses is_benar / jenis.
            $item = [
                'id_soal'       => $s['id_soal'],
                'jawaban_siswa' => $s['jawaban_siswa'],
                'soal'          => $s['soal'],
                'jenis'         => $s['jenis'],
                'is_benar'      => $s['is_benar'],
                'is_salah'      => !$s['is_benar'],
            ];

            if ($s['is_benar']) {
                $soalBenar[] = $item;
            } else {
                $soalSalah[] = $item;
            }
        }

        $totalSoal    = count($soalStatus);
        $jumlahBenar  = count($soalBenar);
        $nilai        = $totalSoal > 0 ? round(($jumlahBenar / $totalSoal) * 100, 2) : 0;

        return [
            'eligible'     => $nilai < $kkm,
            'nilai'        => $nilai,
            'kkm'          => $kkm,
            'total_soal'   => $totalSoal,
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => count($soalSalah),
            'soal_benar'   => $soalBenar,
            'soal_salah'   => $soalSalah,
        ];
    }

    /**
     * Get soal dengan status benar/salah untuk tampilan remedial.
     * OPTIMASI: 2 query terpisah → 1 LEFT JOIN query.
     */
    public function getSoalDenganStatus(int $idSiswa, int $idJadwal, int $idBank): array
    {
        // Satu query: soal LEFT JOIN jawaban siswa
        $rows = $this->db->query(
            "SELECT
                s.id_soal, s.soal, s.jenis, s.jawaban AS kunci,
                j.jawaban AS jawaban_siswa, j.ragu
             FROM cbt_soal s
             LEFT JOIN cbt_jawaban j
                    ON j.id_soal  = s.id_soal
                   AND j.id_siswa = ?
                   AND j.id_jadwal = ?
             WHERE s.bank_id   = ?
               AND s.tampilkan = '1'
             ORDER BY s.nomor_soal ASC",
            [$idSiswa, $idJadwal, $idBank]
        )->getResult();

        $result = [];
        foreach ($rows as $s) {
            $isBenar = false;
            if ($s->jawaban_siswa !== null) {
                $isBenar = $this->cekJawabanBenar($s->jawaban_siswa, $s->kunci, $s->jenis);
            }

            $result[] = [
                'id_soal'       => $s->id_soal,
                'soal'          => $s->soal,
                'jenis'         => $s->jenis,
                'jawaban_siswa' => $s->jawaban_siswa,
                'is_benar'      => $isBenar,
                'is_salah'      => $s->jawaban_siswa !== null ? !$isBenar : true,
                'bisa_edit'     => !$isBenar,
                'ragu'          => $s->ragu ?? '0',
            ];
        }

        return $result;
    }

    /**
     * Private helper: Cek kebenaran jawaban
     */
    private function cekJawabanBenar($jawabanSiswa, $kunciJawaban, $jenis): bool
    {
        if (empty($jawabanSiswa)) return false;

        switch ($jenis) {
            case '1': // Pilihan ganda
                return strtoupper(trim($jawabanSiswa)) === strtoupper(trim($kunciJawaban));
                
            case '2': // Kompleks (multiple choice)
                $jArr = is_string($jawabanSiswa) ? json_decode($jawabanSiswa, true) : $jawabanSiswa;
                $kArr = is_string($kunciJawaban) ? json_decode($kunciJawaban, true) : $kunciJawaban;
                
                if (!is_array($jArr) || !is_array($kArr)) return false;
                
                sort($jArr);
                sort($kArr);
                return $jArr === $kArr;
                
            case '3': // Menjodohkan
                $jArr = is_string($jawabanSiswa) ? json_decode($jawabanSiswa, true) : $jawabanSiswa;
                $kArr = is_string($kunciJawaban) ? json_decode($kunciJawaban, true) : $kunciJawaban;
                return $jArr === $kArr;
                
            default:
                return strtolower(trim($jawabanSiswa)) === strtolower(trim($kunciJawaban));
        }
    }
}
