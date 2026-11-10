<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtPraktikLogModel extends Model
{
    protected $table = 'cbt_praktik_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'siswa_id', 'hasil_id', 'tipe_aktivitas',
        'detail', 'ip_address', 'user_agent', 'created_at'
    ];

    protected $useTimestamps = false; // Manual timestamp
    protected $dateFormat = 'datetime';

    // Activity type constants
    const AKTIVITAS_MULAI = 'mulai_ujian';
    const AKTIVITAS_SUBMIT = 'submit_ujian';
    const AKTIVITAS_KELUAR_FULLSCREEN = 'keluar_fullscreen';
    const AKTIVITAS_TAB_BARU = 'buka_tab_baru';
    const AKTIVITAS_COPY_PASTE = 'copy_paste';
    const AKTIVITAS_SCREENSHOT = 'screenshot_detected';
    const AKTIVITAS_NAVIGASI = 'navigasi_jawaban';
    const AKTIVITAS_UBAH_JAWABAN = 'ubah_jawaban';
    const AKTIVITAS_SIMPAN_DRAFT = 'simpan_draft';
    const AKTIVITAS_KONEKSI_PUTUS = 'koneksi_putus';
    const AKTIVITAS_KONEKSI_KEMBALI = 'koneksi_kembali';
    const AKTIVITAS_KECURANGAN = 'pelanggaran_kecurangan';
    const AKTIVITAS_WEBCAM_TIDAK_AKTIF = 'webcam_tidak_aktif';
    const AKTIVITAS_WAKTU_HAMPIR_HABIS = 'waktu_hampir_habis';

    /**
     * Log an activity
     */
    public function logActivity(int $praktikId, int $siswaId, string $tipe, ?int $hasilId = null, ?array $detail = null): bool
    {
        $request = service('request');

        $data = [
            'praktik_id' => $praktikId,
            'siswa_id' => $siswaId,
            'hasil_id' => $hasilId,
            'tipe_aktivitas' => $tipe,
            'detail' => $detail ? json_encode($detail) : null,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return (bool) $this->insert($data);
    }

    /**
     * Get logs by student and exam
     */
    public function getLogsBySiswaPraktik(int $siswaId, int $praktikId, ?int $hasilId = null): array
    {
        $builder = $this->where('siswa_id', $siswaId)
                       ->where('praktik_id', $praktikId);

        if ($hasilId !== null) {
            $builder->where('hasil_id', $hasilId);
        }

        return $builder->orderBy('created_at', 'ASC')->findAll();
    }

    /**
     * Get suspicious activities for anti-cheat analysis
     */
    public function getSuspiciousActivities(int $praktikId, ?int $siswaId = null): array
    {
        $suspiciousTypes = [
            self::AKTIVITAS_KELUAR_FULLSCREEN,
            self::AKTIVITAS_TAB_BARU,
            self::AKTIVITAS_COPY_PASTE,
            self::AKTIVITAS_SCREENSHOT,
            self::AKTIVITAS_KECURANGAN,
            self::AKTIVITAS_WEBCAM_TIDAK_AKTIF,
        ];

        $builder = $this->whereIn('tipe_aktivitas', $suspiciousTypes)
                       ->where('praktik_id', $praktikId);

        if ($siswaId !== null) {
            $builder->where('siswa_id', $siswaId);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Count violations per student
     */
    public function countViolations(int $praktikId, int $siswaId): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('tipe_aktivitas, COUNT(*) as total');
        $builder->where('praktik_id', $praktikId);
        $builder->where('siswa_id', $siswaId);
        $builder->groupBy('tipe_aktivitas');

        $result = $builder->get()->getResultArray();

        $violations = [];
        foreach ($result as $row) {
            $violations[$row['tipe_aktivitas']] = (int) $row['total'];
        }

        return $violations;
    }

    /**
     * Get students with suspicious activities for a practical exam
     */
    public function getStudentsWithViolations(int $praktikId): array
    {
        $suspiciousTypes = [
            self::AKTIVITAS_KELUAR_FULLSCREEN,
            self::AKTIVITAS_TAB_BARU,
            self::AKTIVITAS_COPY_PASTE,
            self::AKTIVITAS_KECURANGAN,
        ];

        $builder = $this->db->table($this->table . ' l');
        $builder->select('
            l.siswa_id,
            s.nama as nama_siswa,
            s.nis,
            COUNT(*) as total_pelanggaran,
            GROUP_CONCAT(DISTINCT l.tipe_aktivitas) as jenis_pelanggaran,
            MIN(l.created_at) as waktu_awal,
            MAX(l.created_at) as waktu_akhir
        ');
        $builder->join('master_siswa s', 'l.siswa_id = s.id_siswa');
        $builder->where('l.praktik_id', $praktikId);
        $builder->whereIn('l.tipe_aktivitas', $suspiciousTypes);
        $builder->groupBy('l.siswa_id');
        $builder->orderBy('total_pelanggaran', 'DESC');

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get time analysis for a student (start, end, duration)
     */
    public function getTimeAnalysis(int $praktikId, int $siswaId): ?object
    {
        $sql = "
            SELECT 
                MIN(CASE WHEN tipe_aktivitas = ? THEN created_at END) as waktu_mulai,
                MAX(CASE WHEN tipe_aktivitas = ? THEN created_at END) as waktu_submit,
                TIMESTAMPDIFF(SECOND, 
                    MIN(CASE WHEN tipe_aktivitas = ? THEN created_at END),
                    MAX(CASE WHEN tipe_aktivitas = ? THEN created_at END)
                ) as durasi_detik
            FROM {$this->table}
            WHERE praktik_id = ? AND siswa_id = ?
        ";

        return $this->db->query($sql, [
            self::AKTIVITAS_MULAI,
            self::AKTIVITAS_SUBMIT,
            self::AKTIVITAS_MULAI,
            self::AKTIVITAS_SUBMIT,
            $praktikId,
            $siswaId
        ])->getRow();
    }

    /**
     * Clean old logs (for maintenance)
     */
    public function cleanOldLogs(string $beforeDate): int
    {
        return $this->where('created_at <', $beforeDate)->delete();
    }
}
