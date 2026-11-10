<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk Audit Trail perubahan nilai pada ujian praktik
 * Melacak setiap perubahan nilai dengan detail lengkap
 */
class CbtPraktikNilaiAuditModel extends Model
{
    protected $table = 'cbt_praktik_nilai_audit';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'hasil_id', 'siswa_id', 'assessor_id',
        'tipe_aksi', 'field_yang_diubah', 'nilai_lama', 'nilai_baru',
        'rincian_lama', 'rincian_baru', 'alasan_perubahan',
        'ip_address', 'user_agent', 'created_at'
    ];

    protected $useTimestamps = false; // Manual created_at handling
    protected $dateFormat = 'datetime';

    // Tipe aksi constants
    const AKSI_CREATE = 'create';
    const AKSI_UPDATE = 'update';
    const AKSI_DELETE = 'delete';
    const AKSI_VERIFY = 'verify';
    const AKSI_APPROVE = 'approve';
    const AKSI_REJECT = 'reject';

    /**
     * Log perubahan nilai ke audit trail
     */
    public function logPerubahan(
        int $praktikId,
        int $hasilId,
        int $siswaId,
        ?int $assessorId,
        string $tipeAksi,
        ?string $fieldDiubah = null,
        ?float $nilaiLama = null,
        ?float $nilaiBaru = null,
        ?array $rincianLama = null,
        ?array $rincianBaru = null,
        ?string $alasan = null
    ): bool {
        $request = service('request');

        $data = [
            'praktik_id' => $praktikId,
            'hasil_id' => $hasilId,
            'siswa_id' => $siswaId,
            'assessor_id' => $assessorId,
            'tipe_aksi' => $tipeAksi,
            'field_yang_diubah' => $fieldDiubah,
            'nilai_lama' => $nilaiLama,
            'nilai_baru' => $nilaiBaru,
            'rincian_lama' => $rincianLama ? json_encode($rincianLama) : null,
            'rincian_baru' => $rincianBaru ? json_encode($rincianBaru) : null,
            'alasan_perubahan' => $alasan,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return (bool) $this->insert($data);
    }

    /**
     * Log pembuatan nilai baru
     */
    public function logCreate(
        int $praktikId,
        int $hasilId,
        int $siswaId,
        int $assessorId,
        float $nilaiBaru,
        array $rincianBaru,
        ?string $alasan = null
    ): bool {
        return $this->logPerubahan(
            $praktikId,
            $hasilId,
            $siswaId,
            $assessorId,
            self::AKSI_CREATE,
            'nilai_akhir',
            null,
            $nilaiBaru,
            null,
            $rincianBaru,
            $alasan
        );
    }

    /**
     * Log update nilai
     */
    public function logUpdate(
        int $praktikId,
        int $hasilId,
        int $siswaId,
        int $assessorId,
        ?float $nilaiLama,
        float $nilaiBaru,
        ?array $rincianLama,
        array $rincianBaru,
        ?string $fieldDiubah = 'nilai_akhir',
        ?string $alasan = null
    ): bool {
        return $this->logPerubahan(
            $praktikId,
            $hasilId,
            $siswaId,
            $assessorId,
            self::AKSI_UPDATE,
            $fieldDiubah,
            $nilaiLama,
            $nilaiBaru,
            $rincianLama,
            $rincianBaru,
            $alasan
        );
    }

    /**
     * Log verifikasi nilai
     */
    public function logVerify(
        int $praktikId,
        int $hasilId,
        int $siswaId,
        int $verifierId,
        float $nilai,
        ?string $alasan = null
    ): bool {
        return $this->logPerubahan(
            $praktikId,
            $hasilId,
            $siswaId,
            $verifierId,
            self::AKSI_VERIFY,
            'status_penilaian',
            null,
            null,
            null,
            null,
            $alasan
        );
    }

    /**
     * Get audit trail untuk sebuah hasil
     */
    public function getAuditTrail(int $hasilId, ?int $limit = null): array
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('a.*, g.nama_guru as nama_assessor');
        $builder->join('master_guru g', 'a.assessor_id = g.id_guru', 'left');
        $builder->where('a.hasil_id', $hasilId);
        $builder->orderBy('a.created_at', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get audit trail untuk praktik (semua siswa)
     */
    public function getAuditTrailPraktik(int $praktikId, ?string $tipeAksi = null, ?int $limit = 100): array
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('a.*, g.nama_guru as nama_assessor, s.nama as nama_siswa');
        $builder->join('master_guru g', 'a.assessor_id = g.id_guru', 'left');
        $builder->join('master_siswa s', 'a.siswa_id = s.id_siswa', 'left');
        $builder->where('a.praktik_id', $praktikId);

        if ($tipeAksi) {
            $builder->where('a.tipe_aksi', $tipeAksi);
        }

        $builder->orderBy('a.created_at', 'DESC');
        $builder->limit($limit);

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get statistik perubahan per assessor
     */
    public function getStatistikPerAssessor(int $praktikId): array
    {
        $sql = "
            SELECT 
                a.assessor_id,
                g.nama_guru,
                COUNT(*) as total_perubahan,
                COUNT(DISTINCT a.siswa_id) as total_siswa,
                SUM(CASE WHEN a.tipe_aksi = 'create' THEN 1 ELSE 0 END) as total_create,
                SUM(CASE WHEN a.tipe_aksi = 'update' THEN 1 ELSE 0 END) as total_update,
                SUM(CASE WHEN a.tipe_aksi = 'verify' THEN 1 ELSE 0 END) as total_verify,
                MAX(a.created_at) as terakhir_aktif
            FROM {$this->table} a
            LEFT JOIN master_guru g ON a.assessor_id = g.id_guru
            WHERE a.praktik_id = ?
            GROUP BY a.assessor_id
            ORDER BY total_perubahan DESC
        ";

        $query = $this->db->query($sql, [$praktikId]);
        return $query ? $query->getResult() : [];
    }

    /**
     * Get riwayat perubahan nilai per siswa
     */
    public function getRiwayatPerSiswa(int $hasilId): array
    {
        $audit = $this->getAuditTrail($hasilId);
        $riwayat = [];

        foreach ($audit as $a) {
            $riwayat[] = [
                'waktu' => $a->created_at,
                'aksi' => $a->tipe_aksi,
                'penilai' => $a->nama_assessor ?? 'System',
                'nilai_lama' => $a->nilai_lama,
                'nilai_baru' => $a->nilai_baru,
                'selisih' => $a->nilai_baru !== null && $a->nilai_lama !== null
                    ? round($a->nilai_baru - $a->nilai_lama, 2)
                    : null,
                'alasan' => $a->alasan_perubahan,
            ];
        }

        return $riwayat;
    }

    /**
     * Detect anomalous grade changes
     */
    public function detectAnomalousChanges(int $praktikId, float $threshold = 20.0): array
    {
        $sql = "
            SELECT 
                a.*,
                g.nama_guru,
                s.nama as nama_siswa,
                ABS(a.nilai_baru - a.nilai_lama) as selisih
            FROM {$this->table} a
            LEFT JOIN master_guru g ON a.assessor_id = g.id_guru
            LEFT JOIN master_siswa s ON a.siswa_id = s.id_siswa
            WHERE a.praktik_id = ?
            AND a.tipe_aksi = 'update'
            AND ABS(a.nilai_baru - a.nilai_lama) > ?
            ORDER BY selisih DESC
        ";

        $query = $this->db->query($sql, [$praktikId, $threshold]);
        return $query ? $query->getResult() : [];
    }

    /**
     * Get summary untuk dashboard
     */
    public function getDashboardSummary(?int $tpId = null, ?int $smtId = null): object
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('COUNT(*) as total_audit, COUNT(DISTINCT a.praktik_id) as total_praktik');
        $builder->join('cbt_praktik p', 'a.praktik_id = p.id', 'left');

        if ($tpId) {
            $builder->where('p.tp_id', $tpId);
        }
        if ($smtId) {
            $builder->where('p.smt_id', $smtId);
        }

        $query = $builder->get();
        $result = $query ? $query->getRow() : null;

        return $result ?: (object)[
            'total_audit' => 0,
            'total_praktik' => 0
        ];
    }
}
