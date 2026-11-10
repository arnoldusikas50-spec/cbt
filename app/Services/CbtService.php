<?php

namespace App\Services;

use App\Models\CbtModel;
use App\Models\CbtSiswaModel;
use App\Models\DashboardModel;
use App\Models\CbtKartuSoalModel;
use App\Models\TujuanPembelajaranModel;
use App\Models\MasterModel;

/**
 * CbtService - Business Logic Layer for CBT
 * 
 * Centralizes common CBT operations to avoid code duplication
 * across controllers. Implements caching for frequently accessed data.
 */
class CbtService
{
    protected $cbtModel;
    protected $cbtSiswaModel;
    protected $dashboardModel;
    protected $kartuModel;
    protected $tujuanModel;
    protected $masterModel;
    protected $db;
    protected $cache;

    // Cache TTL in seconds
    const CACHE_TTL_SHORT = 60;      // 1 minute
    const CACHE_TTL_MEDIUM = 300;    // 5 minutes
    const CACHE_TTL_LONG = 3600;     // 1 hour

    // Flag untuk penggerak mandiri antrian scoring (tanpa cron).
    const SCORING_QUEUE_FLAG     = 'cbt_scoring_queue_flag';
    const SCORING_QUEUE_FLAG_TTL = 1800; // 30 menit

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->cbtSiswaModel = new CbtSiswaModel();
        $this->dashboardModel = new DashboardModel();
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
        $this->kartuModel = new CbtKartuSoalModel();
        $this->tujuanModel = new TujuanPembelajaranModel();
        $this->tujuanModel->initialize();
        $this->masterModel = new MasterModel();
    }

    /**
     * Get current student data from session
     * Cached per request (static) untuk menghindari multiple DB calls dalam satu request
     */
    public function getCurrentSiswa(): ?object
    {
        static $siswa = null;
        static $checked = false;

        if ($checked) {
            return $siswa;
        }
        $checked = true;

        try {
            $session = session();
            $username = $session->get('username');

            if (!$username) {
                return null;
            }

            $tp  = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return null;
            }

            $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

            return $siswa;
        } catch (\Throwable $e) {
            log_message('error', 'CbtService::getCurrentSiswa error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get active academic period (cached)
     */
    public function getActivePeriod(): array
    {
        $cacheKey = 'cbt_active_period';

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'tp' => $tp,
            'smt' => $smt
        ];

        $this->cache->save($cacheKey, $data, self::CACHE_TTL_LONG);

        return $data;
    }

    /**
     * Get jadwal with all related data (optimized single query)
     */
    public function getJadwalComplete(int $idJadwal): ?object
    {
        $cacheKey = "cbt_jadwal_complete_{$idJadwal}";

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $sql = "
            SELECT 
                j.*,
                b.id_bank, b.bank_kode, b.bank_nama, b.bank_level, b.bank_kelas,
                b.bank_mapel_id, b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan,
                b.tampil_isian, b.tampil_esai, b.tampil_urutan,
                b.soal_agama,
                jn.nama_jenis,
                m.nama_mapel, m.kode as kode_mapel
            FROM cbt_jadwal j
            LEFT JOIN cbt_bank_soal b ON j.id_bank = b.id_bank
            LEFT JOIN cbt_jenis jn ON j.id_jenis = jn.id_jenis
            LEFT JOIN master_mapel m ON b.bank_mapel_id = m.id_mapel
            WHERE j.id_jadwal = ?
        ";

        $query = $this->db->query($sql, [$idJadwal]);
        $jadwal = $query ? $query->getRow() : null;

        if ($jadwal) {
            $this->cache->save($cacheKey, $jadwal, self::CACHE_TTL_MEDIUM);
        }

        return $jadwal;
    }

    /**
     * Get available exams for student (optimized)
     */
    public function getAvailableExams(int $idSiswa, int $levelId, int $kelasId): array
    {
        $period = $this->getActivePeriod();

        $sql = "
            SELECT DISTINCT
                j.id_jadwal, j.durasi_ujian, j.tgl_mulai, j.tgl_selesai, j.status,
                b.bank_kode, b.bank_nama,
                jn.nama_jenis,
                m.nama_mapel, m.kode as kode_mapel,
                cs.id_cbt_siswa, cs.status as siswa_status, cs.mulai, cs.selesai
            FROM cbt_jadwal j
            JOIN cbt_bank_soal b ON j.id_bank = b.id_bank
            LEFT JOIN cbt_jenis jn ON j.id_jenis = jn.id_jenis
            LEFT JOIN master_mapel m ON b.bank_mapel_id = m.id_mapel
            LEFT JOIN cbt_siswa cs ON j.id_jadwal = cs.id_jadwal AND cs.id_siswa = ?
            WHERE j.id_tp = ? 
              AND j.id_smt = ?
              AND j.status = '1'
              AND (b.bank_level = ? OR b.bank_level = 0)
            ORDER BY j.tgl_mulai DESC
        ";

        $exams = $this->db->query($sql, [
            $idSiswa,
            $period['tp']->id_tp,
            $period['smt']->id_smt,
            $levelId
        ])->getResult();

        // Filter by class
        $filtered = [];
        foreach ($exams as $exam) {
            // Get bank_kelas from jadwal
            $jadwal = $this->getJadwalComplete($exam->id_jadwal);
            if (!$jadwal)
                continue;

            $bankKelas = $this->parseKelasIds($jadwal->bank_kelas);

            // Include if class matches or "all classes" (empty)
            if (empty($bankKelas) || in_array($kelasId, $bankKelas)) {
                $filtered[] = $exam;
            }
        }

        return $filtered;
    }

    /**
     * Parse bank_kelas serialized/JSON data to array of class IDs
     */
    public function parseKelasIds($bankKelas): array
    {
        if (empty($bankKelas)) {
            return [];
        }

        // Try JSON decode
        $decoded = json_decode($bankKelas, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_column($decoded, 'kelas_id');
        }

        // Try unserialize
        $unserialized = @unserialize($bankKelas);
        if ($unserialized !== false && is_array($unserialized)) {
            return array_column($unserialized, 'kelas_id');
        }

        return [];
    }

    /**
     * Check if student can start exam
     */
    public function canStartExam(int $idSiswa, int $idJadwal): array
    {
        $jadwal = $this->getJadwalComplete($idJadwal);

        if (!$jadwal) {
            return ['allowed' => false, 'reason' => 'Jadwal tidak ditemukan'];
        }

        // Check time window
        $now = time();
        $start = strtotime($jadwal->tgl_mulai);
        $end = strtotime($jadwal->tgl_selesai);

        if ($now < $start) {
            return ['allowed' => false, 'reason' => 'Ujian belum dimulai'];
        }

        if ($now > $end) {
            return ['allowed' => false, 'reason' => 'Waktu ujian sudah berakhir'];
        }

        // Check exam status
        if ($jadwal->status != '1') {
            return ['allowed' => false, 'reason' => 'Ujian tidak aktif'];
        }

        // Check if already finished
        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($idSiswa, $idJadwal);
        if ($cbtSiswa && $cbtSiswa->status == '2') {
            return ['allowed' => false, 'reason' => 'Anda sudah menyelesaikan ujian ini'];
        }

        return ['allowed' => true, 'jadwal' => $jadwal, 'cbtSiswa' => $cbtSiswa];
    }

    /**
     * Get exam duration remaining (in seconds)
     */
    public function getRemainingTime(object $cbtSiswa, object $jadwal): int
    {
        $durasi = ($jadwal->durasi_ujian ?? 60) * 60; // Duration in seconds
        $mulai = strtotime($cbtSiswa->mulai);
        $elapsed = time() - $mulai;

        // Add reset time if any
        $extraMinutes = (int) ($cbtSiswa->reset_waktu ?? 0);
        $totalDurasi = $durasi + ($extraMinutes * 60);

        $remaining = $totalDurasi - $elapsed;

        return max(0, $remaining);
    }

    /**
     * Hitung sisa waktu yang authoritative (server-side).
     * Dipanggil dari getSoal() agar timer JS di-sync dengan server
     * setiap kali siswa navigasi soal, mencegah manipulasi timer.
     *
     * @return array ['remaining' => int detik, 'elapsed' => int detik, 'expired' => bool]
     */
    public function getTimerState(object $cbtSiswa, object $jadwal): array
    {
        $durasiDetik = (int) ($jadwal->durasi_ujian ?? 60) * 60;
        $extraDetik  = (int) ($cbtSiswa->reset_waktu ?? 0) * 60;
        $totalDetik  = $durasiDetik + $extraDetik;

        $mulaiStr = $cbtSiswa->mulai ?? date('Y-m-d H:i:s');
        if (empty($mulaiStr) || strpos($mulaiStr, '0000') !== false) {
            $mulaiStr = date('Y-m-d H:i:s');
        }

        $elapsed   = time() - strtotime($mulaiStr);
        $remaining = max(0, $totalDetik - $elapsed);

        return [
            'remaining' => $remaining,
            'elapsed'   => $elapsed,
            'expired'   => $remaining <= 0,
            'total'     => $totalDetik,
        ];
    }

    /**
     * Get questions for exam (paginated for performance)
     */
    public function getQuestions(int $idBank, int $limit = 0, int $offset = 0): array
    {
        $builder = $this->db->table('cbt_soal')
            ->where('bank_id', $idBank)
            ->orderBy('nomor_soal', 'ASC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->get()->getResult();
    }

    /**
     * Get question count by type
     */
    public function getQuestionCountByType(int $idBank): array
    {
        $cacheKey = "cbt_soal_count_{$idBank}";

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $sql = "
            SELECT jenis, COUNT(*) as jumlah
            FROM cbt_soal
            WHERE bank_id = ?
            GROUP BY jenis
        ";

        $query = $this->db->query($sql, [$idBank]);
        $result = $query ? $query->getResultArray() : [];
        $counts = array_column($result, 'jumlah', 'jenis');

        $this->cache->save($cacheKey, $counts, self::CACHE_TTL_LONG);

        return $counts;
    }

    /**
     * Calculate and save score (delegates to model)
     */
    public function calculateScore(int $idSiswa, int $idJadwal): bool
    {
        return $this->cbtModel->hitungNilai($idSiswa, $idJadwal);
    }

    /**
     * Masukkan (id_siswa, id_jadwal) ke antrian scoring.
     *
     * Menggantikan pola exec('cbt:hitung_nilai ...') yang me-spawn 1 proses
     * PHP CLI per siswa — bahaya saat 1000 siswa submit bersamaan.
     *
     * Ini murni INSERT/UPSERT ringan tanpa memulai proses baru. Konsumen
     * cron (cbt:proses_scoring_queue) memproses batch-nya.
     *
     * Upsert: item yang sudah selesai (status=2) dipertahankan, yang
     * pending/failed di-reset menjadi pending agar di-retry.
     *
     * @param bool $force Saat true, item apa pun (termasuk status=2 selesai)
     *                     di-reset ke status=0 untuk memaksa hitung ulang.
     *                     Dipakai oleh recalculateAll() untuk mengulang
     *                     penilaian seluruh siswa dalam satu jadwal.
     */
    public function enqueueScoring(int $idSiswa, int $idJadwal, bool $force = false): bool
    {
        try {
            if ($force) {
                $sql = "INSERT INTO cbt_scoring_queue (id_siswa, id_jadwal, status, created_at, updated_at)
                        VALUES (?, ?, 0, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE
                           status     = 0,
                           attempts   = 0,
                           error      = NULL,
                           updated_at = NOW()";
            } else {
                // CATATAN: urutan assignment penting. MySQL mengevaluasi kolom
                // kiri-ke-kanan; `attempts` harus dihitung dulu (berbasis status
                // LAMA) sebelum `status` diubah, agar item yang gagal permanen
                // (status=3) benar-benar di-reset attempts-nya untuk di-retry.
                $sql = "INSERT INTO cbt_scoring_queue (id_siswa, id_jadwal, status, created_at, updated_at)
                        VALUES (?, ?, 0, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE
                           attempts   = IF(status = 3, 0, attempts),
                           status     = IF(status = 2, 2, 0),
                           error      = NULL,
                           updated_at = NOW()";
            }
            $this->db->query($sql, [$idSiswa, $idJadwal]);
            // Tandai ada pekerjaan menunggu → pemicu lazy drain (tanpa cron).
            $this->cache->save(self::SCORING_QUEUE_FLAG, 1, self::SCORING_QUEUE_FLAG_TTL);
            return true;
        } catch (\Throwable $e) {
            log_message('error', '[enqueueScoring] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Penggerak mandiri antrian scoring (self-delivering).
     *
     * Dipanggil dari heartbeat siswa (poll ujian) TANPA bergantung cron.
     * Hanya aktif bila flag Redis menunjukkan ada item menunggu, sehingga
     * biayanya ~0 saat antrian kosong. Klaim per baris bersifat atomik
     * (& status=0) sehingga aman bila beberapa request memanggil bersamaan:
     * tidak ada kebutaan, hanya worker yang menang akan memproses baris.
     */
    public function drainScoringQueueIfNeeded(int $max = 5, int $maxAttempts = 3): void
    {
        if ((int) $this->cache->get(self::SCORING_QUEUE_FLAG) !== 1) {
            return;
        }

        $this->processScoringQueue($max, $maxAttempts);

        $sisa = (int) $this->db->table('cbt_scoring_queue')
            ->where('status', 0)
            ->where('attempts <', $maxAttempts)
            ->countAllResults();
        if ($sisa === 0) {
            $this->cache->delete(self::SCORING_QUEUE_FLAG);
        }
    }

    /**
     * Proses batch antrian scoring (konsumen).
     *
     * Dipakai oleh command CLI `cbt:proses_scoring_queue` maupun web-cron
     * `Cron::scoring_queue`. Klaim atomik per baris agar aman bila beberapa
     * worker (cron + web-cron) berjalan bersamaan.
     *
     * @return array{ok:int, failed:int, skipped:int, claimed:int}
     */
    public function processScoringQueue(int $limit = 50, int $maxAttempts = 3): array
    {
        $limit       = max(1, min($limit, 500));
        $maxAttempts = max(1, $maxAttempts);

        $rows = $this->db->table('cbt_scoring_queue')
            ->where('status', 0)
            ->where('attempts <', $maxAttempts)
            ->orderBy('id', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        $ok      = 0;
        $failed  = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $idQueue   = (int) $row['id'];
            $idSiswa   = (int) $row['id_siswa'];
            $idJadwal  = (int) $row['id_jadwal'];

            // Claim atomik: hanya satu worker yang menang (& status=0)
            $this->db->query(
                'UPDATE cbt_scoring_queue SET status = 1, updated_at = NOW() WHERE id = ? AND status = 0',
                [$idQueue]
            );
            if ($this->db->affectedRows() === 0) {
                $skipped++;
                continue;
            }

            try {
                $this->calculateScore($idSiswa, $idJadwal);
                $this->db->table('cbt_scoring_queue')
                    ->where('id', $idQueue)
                    ->update([
                        'status'     => 2,
                        'error'      => null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                $ok++;
            } catch (\Throwable $e) {
                $newAttempts = (int) $row['attempts'] + 1;
                $finalStatus = $newAttempts >= $maxAttempts ? 3 : 0;
                $this->db->table('cbt_scoring_queue')
                    ->where('id', $idQueue)
                    ->update([
                        'status'     => $finalStatus,
                        'attempts'   => $newAttempts,
                        'error'      => substr($e->getMessage(), 0, 500),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                log_message('error', "[processScoringQueue] gagal siswa={$idSiswa}, jadwal={$idJadwal}: " . $e->getMessage());
                $failed++;
            }
        }

        log_message('info', "[processScoringQueue] ok={$ok}, failed={$failed}, skipped={$skipped}, limit={$limit}");

        return ['ok' => $ok, 'failed' => $failed, 'skipped' => $skipped, 'claimed' => count($rows)];
    }

    /**
     * Invalidate cache for a jadwal
     */
    public function invalidateJadwalCache(int $idJadwal): void
    {
        $this->cache->delete("cbt_jadwal_complete_{$idJadwal}");
    }

    /**
     * Get token (no cache - token changes frequently)
     */
    public function getCurrentToken(): ?object
    {
        // Token changes frequently, don't cache to avoid validation issues
        return $this->cbtModel->getToken();
    }

    /**
     * Get questions with deterministic shuffle (cached per student+jadwal)
     *
     * Optimasi:
     * - Soal di-cache terpisah per bank (soal_base) sehingga 100 siswa dalam
     *   jadwal yang sama tidak 100x query DB.
     * - Urutan per siswa (soal_order) di-cache terpisah sehingga setiap navigasi
     *   tidak perlu shuffle ulang.
     * - Shuffle menggunakan Fisher-Yates yang benar-benar deterministik.
     */
    public function getSoalCached(int $idBank, int $idSiswa, int $idJadwal, string $acak = '0'): array
    {
        // Kunci urutan per siswa
        $orderKey = "cbt_soal_order_{$idBank}_{$idSiswa}_{$idJadwal}_{$acak}";
        $cached   = $this->cache->get($orderKey);
        if ($cached !== null) {
            return $cached;
        }

        // Kunci data soal mentah per bank (shared antar semua siswa)
        $baseKey  = "cbt_soal_base_{$idBank}";
        $questions = $this->cache->get($baseKey);

        if ($questions === null) {
            // Gunakan index idx_soal_bank_tampil_nomor yang baru kita buat
            $questions = $this->db->table('cbt_soal')
                ->where('bank_id', $idBank)
                ->where('tampilkan', '1')
                ->orderBy('nomor_soal', 'ASC')
                ->orderBy('id_soal', 'ASC')
                ->get()
                ->getResult();

            // Cache soal mentah 2 jam — soal tidak berubah selama ujian berlangsung
            $this->cache->save($baseKey, $questions, 7200);
        }

        // Terapkan Fisher-Yates deterministik jika acak diaktifkan
        if ($acak == '1' && !empty($questions)) {
            $seed = ($idSiswa * 31) ^ ($idJadwal * 17) ^ $idBank;
            $arr  = $questions;
            $n    = count($arr);

            mt_srand($seed);
            for ($i = $n - 1; $i > 0; $i--) {
                $j = mt_rand(0, $i);
                [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
            }
            mt_srand(); // Reset agar tidak bocor ke proses lain
            $questions = $arr;
        }

        // Cache urutan per siswa selama 2 jam (durasi ujian terpanjang)
        $this->cache->save($orderKey, $questions, 7200);

        return $questions;
    }

    /**
     * Invalidate cache soal base saat soal diubah admin/guru
     */
    public function invalidateSoalBaseCache(int $idBank): void
    {
        $this->cache->delete("cbt_soal_base_{$idBank}");
    }

    /**
     * Get all jawaban for student+jadwal (cached during exam)
     *
     * Optimasi: TTL diperpanjang ke 300 detik (5 menit).
     * invalidateJawabanCache() dipanggil setiap saveJawaban, jadi data
     * tetap fresh saat siswa navigasi ke soal yang baru saja dijawab.
     * Ini mengurangi query DB dari O(navigasi) menjadi O(navigasi/cache_hit_rate).
     */
    public function getJawabanCached(int $idSiswa, int $idJadwal): array
    {
        $cacheKey = "cbt_jawaban_{$idSiswa}_{$idJadwal}";

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $result = $this->db->table('cbt_jawaban')
            ->select('id_jawaban, id_soal, jawaban, ragu, waktu')  // hanya kolom yang diperlukan
            ->where('id_siswa', $idSiswa)
            ->where('id_jadwal', $idJadwal)
            ->get()
            ->getResult();

        // Index by id_soal untuk O(1) lookup
        $data = [];
        foreach ($result as $row) {
            $data[$row->id_soal] = $row;
        }

        $this->cache->save($cacheKey, $data, 300); // 5 menit

        return $data;
    }

    /**
     * Invalidate jawaban cache when answer is saved
     */
    public function invalidateJawabanCache(int $idSiswa, int $idJadwal): void
    {
        $this->cache->delete("cbt_jawaban_{$idSiswa}_{$idJadwal}");
    }

    /**
     * Get cbt_siswa record (with request-level caching)
     */
    public function getCbtSiswaCached(int $idSiswa, int $idJadwal): ?object
    {
        static $cache = [];
        $key = "{$idSiswa}_{$idJadwal}";

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $cache[$key] = $this->cbtSiswaModel->getCbtSiswa($idSiswa, $idJadwal);
        return $cache[$key];
    }

    /**
     * Clear request-level cbt_siswa cache (call after updates)
     */
    public function clearCbtSiswaRequestCache(): void
    {
        // Note: static cache is automatically cleared per request
    }

    /**
     * Get active context for a guru (Active Year, Semester, and Guru Data)
     */
    public function getGuruContext(int $userId): array
    {
        $tp  = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        
        if (!$tp || !$smt) {
            return ['tp' => null, 'smt' => null, 'guru' => null];
        }

        $guru = $this->dashboardModel->getDataGuruByUserId($userId, (int) $tp->id_tp, (int) $smt->id_smt);
        
        return [
            'tp'   => $tp,
            'smt'  => $smt,
            'guru' => $guru
        ];
    }

    /**
     * Get detailed workflow status for a guru in a specific mapel
     */
    public function getWorkflowStatus(int $userId, int $guruId, int $mapelId, int $tpId, int $smtId, ?int $kartuId = null): array
    {
        if ($mapelId <= 0) {
            return [
                'ready' => false,
                'tujuan_count' => 0,
                'kartu_count' => 0,
                'kartu_approved_count' => 0,
                'bank_count' => 0,
                'message' => 'Mapel tidak valid.',
            ];
        }

        $tujuanCount = $this->tujuanModel->where('id_guru', $guruId)
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('id_mapel', $mapelId)
            ->countAllResults();

        $kartuBaseQuery = $this->kartuModel->where('mapel_id', $mapelId)
            ->where('tp_id', $tpId)
            ->where('smt_id', $smtId)
            ->where('created_by', $userId)
            ->where('deleted_at', null);
            
        if ($kartuId) {
            $kartuBaseQuery->where('id_kartu', $kartuId);
        }
        $kartuCount = $kartuBaseQuery->countAllResults();

        $kartuApprovedQuery = $this->kartuModel->where('mapel_id', $mapelId)
            ->where('tp_id', $tpId)
            ->where('smt_id', $smtId)
            ->where('created_by', $userId)
            ->where('deleted_at', null)
            ->where('workflow_status', 'approved');
        
        if ($kartuId) {
            $kartuApprovedQuery->where('id_kartu', $kartuId);
        }
        $kartuApprovedCount = $kartuApprovedQuery->countAllResults();

        $bankCount = $this->db->table('cbt_bank_soal')
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('bank_mapel_id', $mapelId)
            ->where('bank_guru_id', $guruId)
            ->countAllResults();

        $soalCount = $this->db->table('cbt_soal s')
            ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id', 'inner')
            ->where('b.id_tp', $tpId)
            ->where('b.id_smt', $smtId)
            ->where('b.bank_mapel_id', $mapelId)
            ->where('b.bank_guru_id', $guruId)
            ->countAllResults();

        $ready = $tujuanCount > 0;
        if (!$ready) {
            $message = 'Mapel ini belum punya Tujuan Pembelajaran. Susun TP terlebih dahulu.';
        } elseif ($kartuCount <= 0) {
            $message = 'Tujuan Pembelajaran tersedia (' . $tujuanCount . ' TP). Langkah berikutnya: buat Kartu Soal (kisi-kisi) di modul Kartu Soal.';
        } elseif ($kartuApprovedCount <= 0) {
            $message = 'Kartu Soal sudah ada (' . $kartuCount . ' kartu) tapi belum ada yang berstatus Approved. Ajukan kartu soal untuk disetujui.';
        } else {
            $message = 'Siap. ' . $tujuanCount . ' TP tersedia, ' . $kartuApprovedCount . ' Kartu Soal sudah disetujui.';
        }

        return [
            'ready' => $ready,
            'tujuan_count' => $tujuanCount,
            'kartu_count' => $kartuCount,
            'kartu_approved_count' => $kartuApprovedCount,
            'bank_count' => $bankCount,
            'soal_count' => $soalCount,
            'message' => $message,
        ];
    }

    /**
     * Get guru profile for specific period
     */
    public function getGuruProfile(int $userId, int $tpId, int $smtId): ?object
    {
        return $this->dashboardModel->getDataGuruByUserId($userId, $tpId, $smtId);
    }

    /**
     * Get learning objectives (TP) categorized by mapel for a guru
     * @param int $guruId ID guru
     * @param int $tpId ID tahun pelajaran
     * @param int $smtId ID semester
     * @param int $mapelId ID mapel
     * @param bool $activeOnly Jika true, hanya ambil TP yang aktif (is_active = 1)
     * @return array Array of tujuan pembelajaran
     */
    public function getTujuanByMapel(int $guruId, int $tpId, int $smtId, int $mapelId, bool $activeOnly = false): array
    {
        return $this->tujuanModel->getTujuanByGuru($guruId, $smtId, $mapelId, null, null, $activeOnly);
    }

    /**
     * Validate if a mapel-kelas combination is assigned to a guru
     */
    public function isValidMapelKelas(int $userId, bool $isAdminOrPanitia, int $mapelId, int $kelasId, int $tpId, int $smtId): bool
    {
        if ($mapelId <= 0 || $kelasId <= 0 || $tpId <= 0 || $smtId <= 0) {
            return false;
        }

        if ($isAdminOrPanitia) {
            return true;
        }

        $guru = $this->getGuruProfile($userId, $tpId, $smtId);
        if (!$guru) {
            return false;
        }

        $mapelGuru = $this->masterModel->getGuruMapelKelas((int) $guru->id_guru, $tpId, $smtId);
        if (!$mapelGuru) {
            return false;
        }

        $mapelList = $this->maybe_unserialize($mapelGuru->mapel_kelas ?? '[]');
        if (!is_array($mapelList)) {
            return false;
        }

        foreach ($mapelList as $mp) {
            $assignedMapelId = (int) $this->pickMixed($mp, ['id_mapel', 'mapel', 'id'], 0);
            if ($assignedMapelId <= 0 && is_scalar($mp) && is_numeric((string) $mp)) {
                $assignedMapelId = (int) $mp;
            }

            if ($assignedMapelId !== $mapelId) {
                continue;
            }

            $kelasMapelList = $this->pickMixed($mp, ['kelas_mapel'], []);
            if (!is_array($kelasMapelList) || empty($kelasMapelList)) {
                return true;
            }

            foreach ($kelasMapelList as $kelasMapel) {
                $kid = (int) $this->pickMixed($kelasMapel, ['kelas', 'id_kelas'], 0);
                if ($kid <= 0 && is_scalar($kelasMapel) && is_numeric((string) $kelasMapel)) {
                    $kid = (int) $kelasMapel;
                }
                if ($kid === $kelasId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Catat aktivitas siswa selama ujian ke tabel cbt_log_aktivitas.
     *
     * Fire-and-forget: error diabaikan agar tidak mengganggu flow ujian.
     * Gunakan aksi yang konsisten:
     *   mulai | buka_soal | simpan_jawaban | ragu
     *   tab_switch | blur | fullscreen_exit | copy_paste
     *   selesai | paksa_selesai
     *
     * @param int    $idSiswa
     * @param int    $idJadwal
     * @param string $aksi
     * @param int|null $idSoal   Isi jika aksi terkait soal tertentu
     * @param string|null $detail Informasi tambahan (misalnya nomor soal, IP)
     */
    public function logAktivitas(
        int $idSiswa,
        int $idJadwal,
        string $aksi,
        ?int $idSoal  = null,
        ?string $detail = null
    ): void {
        try {
            // Cek tabel ada dulu (aman jika migration belum dijalankan)
            static $tableExists = null;
            if ($tableExists === null) {
                $tableExists = $this->db->tableExists('cbt_log_aktivitas');
            }
            if (!$tableExists) return;

            $ip = service('request')->getIPAddress();

            $this->db->table('cbt_log_aktivitas')->insert([
                'id_siswa'   => $idSiswa,
                'id_jadwal'  => $idJadwal,
                'id_soal'    => $idSoal,
                'aksi'       => $aksi,
                'detail'     => $detail ? mb_substr($detail, 0, 255) : null,
                'ip_address' => $ip,
                'waktu'      => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Jangan sampai log error mengganggu ujian
            log_message('error', '[logAktivitas] ' . $e->getMessage());
        }
    }

    /**
     * Helper to pick values from mixed source (array or object)
     */
    private function pickMixed($source, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if (is_array($source) && array_key_exists($key, $source)) {
                return $source[$key];
            }
            if (is_object($source) && property_exists($source, $key)) {
                return $source->$key;
            }
        }
        return $default;
    }

    /**
     * Decode data that might be serialized or JSON
     */
    private function maybe_unserialize($data)
    {
        if (empty($data)) return [];
        if (is_array($data) || is_object($data)) return $data;
        $decoded = json_decode($data, true);
        if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        $unserialized = @unserialize($data);
        if ($unserialized !== false || $data === 'b:0;') return $unserialized;
        return $data;
    }
}
