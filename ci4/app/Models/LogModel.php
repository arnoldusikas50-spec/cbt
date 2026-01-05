<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LogModel - Migrated from CI3 Log_model
 * Handles activity logging
 */
class LogModel extends Model
{
    protected $table = 'logs';
    protected $primaryKey = 'id_log';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_user',
        'aktivitas',
        'keterangan',
        'ip_address',
        'user_agent',
        'waktu'
    ];

    protected $useTimestamps = false;

    /**
     * Save activity log
     * 
     * @param int    $aktivitas Activity type (1=Login, 2=Logout, etc)
     * @param string $keterangan Description of activity
     * @param int|null $userId Optional user ID (if not from session)
     */
    public function saveLog(int $aktivitas, string $keterangan = '', ?int $userId = null): bool
    {
        $userId = $userId ?? session()->get('user_id');

        if (!$userId) {
            return false;
        }

        try {
            $data = [
                'id_user' => $userId,
                'aktivitas' => $aktivitas,
                'keterangan' => $keterangan,
                'ip_address' => service('request')->getIPAddress(),
                'user_agent' => service('request')->getUserAgent()->getAgentString(),
                'waktu' => date('Y-m-d H:i:s')
            ];

            return $this->insert($data) !== false;
        } catch (\Exception $e) {
            // Log table may not exist, ignore error
            return false;
        }
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs(int $limit = 50): array
    {
        return $this->orderBy('waktu', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get logs by user
     */
    public function getLogsByUser(int $userId, int $limit = 50): array
    {
        return $this->where('id_user', $userId)
            ->orderBy('waktu', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Delete log by ID
     */
    public function hapusLog(int $idLog): bool
    {
        return $this->delete($idLog);
    }
}
