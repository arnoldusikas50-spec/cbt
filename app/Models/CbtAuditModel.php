<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CbtAuditModel - Model untuk audit trail sistem penilaian CBT
 * Menyimpan riwayat perubahan nilai dan jawaban siswa
 */
class CbtAuditModel extends Model
{
    protected $table = 'cbt_nilai_audit';
    protected $primaryKey = 'id_audit';
    
    protected $allowedFields = [
        'id_siswa',
        'id_jadwal',
        'action',
        'old_values',
        'new_values',
        'changed_by',
        'changed_by_role',
        'ip_address',
        'user_agent',
        'reason',
        'created_at'
    ];
    
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    
    protected $returnType = 'object';
    
    /**
     * Log perubahan nilai
     * 
     * @param int $siswaId
     * @param int $jadwalId
     * @param string $action INSERT, UPDATE, DELETE, RECALCULATE, CORRECT
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param string|null $reason
     * @return bool|int
     */
    public function logNilaiChange(
        int $siswaId,
        int $jadwalId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null
    ) {
        $request = service('request');
        $session = session();
        
        $data = [
            'id_siswa' => $siswaId,
            'id_jadwal' => $jadwalId,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'changed_by' => $session->get('user_id'),
            'changed_by_role' => $session->get('role_name'),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            log_message('error', '[CbtAuditModel] Failed to log nilai change: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log perubahan jawaban siswa
     * 
     * @param int $siswaId
     * @param int $jadwalId
     * @param int $soalId
     * @param string $action
     * @param string|null $oldJawaban
     * @param string|null $newJawaban
     * @param string $syncMethod
     * @return bool|int
     */
    public function logJawabanChange(
        int $siswaId,
        int $jadwalId,
        int $soalId,
        string $action,
        ?string $oldJawaban = null,
        ?string $newJawaban = null,
        string $syncMethod = 'direct'
    ) {
        $request = service('request');
        
        $data = [
            'id_siswa' => $siswaId,
            'id_jadwal' => $jadwalId,
            'id_soal' => $soalId,
            'action' => $action,
            'old_jawaban' => $oldJawaban,
            'new_jawaban' => $newJawaban,
            'sync_method' => $syncMethod,
            'ip_address' => $request->getIPAddress(),
            'changed_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            return $this->db->table('cbt_jawaban_audit')->insert($data);
        } catch (\Exception $e) {
            log_message('error', '[CbtAuditModel] Failed to log jawaban change: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ambil history audit untuk siswa dan jadwal tertentu
     * 
     * @param int $siswaId
     * @param int $jadwalId
     * @param int|null $limit
     * @return array
     */
    public function getAuditHistory(int $siswaId, int $jadwalId, ?int $limit = null): array
    {
        $builder = $this->builder();
        $builder->where('id_siswa', $siswaId);
        $builder->where('id_jadwal', $jadwalId);
        $builder->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        $results = $builder->get()->getResult();
        
        // Decode JSON fields
        foreach ($results as &$row) {
            if ($row->old_values) {
                $row->old_values = json_decode($row->old_values, true);
            }
            if ($row->new_values) {
                $row->new_values = json_decode($row->new_values, true);
            }
        }
        
        return $results;
    }
    
    /**
     * Ambil audit trail dengan filter
     * 
     * @param array $filters
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getAuditTrail(array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $builder = $this->builder();
        
        if (!empty($filters['siswa_id'])) {
            $builder->where('id_siswa', $filters['siswa_id']);
        }
        
        if (!empty($filters['jadwal_id'])) {
            $builder->where('id_jadwal', $filters['jadwal_id']);
        }
        
        if (!empty($filters['action'])) {
            $builder->where('action', $filters['action']);
        }
        
        if (!empty($filters['changed_by'])) {
            $builder->where('changed_by', $filters['changed_by']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to']);
        }
        
        $builder->orderBy('created_at', 'DESC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        $results = $builder->get()->getResult();
        
        // Decode JSON fields
        foreach ($results as &$row) {
            if ($row->old_values) {
                $row->old_values = json_decode($row->old_values, true);
            }
            if ($row->new_values) {
                $row->new_values = json_decode($row->new_values, true);
            }
        }
        
        return $results;
    }
    
    /**
     * Count audit records untuk pagination
     * 
     * @param array $filters
     * @return int
     */
    public function countAuditTrail(array $filters = []): int
    {
        $builder = $this->builder();
        
        if (!empty($filters['siswa_id'])) {
            $builder->where('id_siswa', $filters['siswa_id']);
        }
        
        if (!empty($filters['jadwal_id'])) {
            $builder->where('id_jadwal', $filters['jadwal_id']);
        }
        
        if (!empty($filters['action'])) {
            $builder->where('action', $filters['action']);
        }
        
        return $builder->countAllResults();
    }
    
    /**
     * Get statistik perubahan nilai
     * 
     * @param int $jadwalId
     * @return array
     */
    public function getAuditStats(int $jadwalId): array
    {
        $builder = $this->builder();
        
        $stats = $builder->select([
            'action',
            'COUNT(*) as count'
        ])
        ->where('id_jadwal', $jadwalId)
        ->groupBy('action')
        ->get()->getResult();
        
        $result = [
            'total_changes' => 0,
            'by_action' => []
        ];
        
        foreach ($stats as $stat) {
            $result['total_changes'] += $stat->count;
            $result['by_action'][$stat->action] = $stat->count;
        }
        
        return $result;
    }
}
