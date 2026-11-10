<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * CbtDeadlineGuruModel - Model untuk mengelola deadline pada Bank Soal
 * Menggunakan deadline global dari tabel setting (ba_waktu)
 */
class CbtDeadlineGuruModel extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id_setting';
    protected $returnType = 'object';

    /**
     * Get global deadline from setting
     */
    public function getGlobalDeadline(): ?object
    {
        $db = \Config\Database::connect();
        $setting = $db->table('setting')->select('ba_waktu, ba_aktif')->get()->getRow();
        
        if (!$setting || empty($setting->ba_waktu) || $setting->ba_waktu === '0000-00-00 00:00:00') {
            return null;
        }
        
        return $setting;
    }

    /**
     * Check if global deadline has expired
     */
    public function isDeadlineExpired(): bool
    {
        $deadline = $this->getGlobalDeadline();
        
        if (!$deadline) {
            return false; // No deadline set
        }
        
        $now = new \DateTime();
        $deadlineTime = new \DateTime($deadline->ba_waktu);
        
        return $now > $deadlineTime;
    }

    /**
     * Get remaining time for deadline
     */
    public function getRemainingTime(): ?array
    {
        $deadline = $this->getGlobalDeadline();
        
        if (!$deadline) {
            return null;
        }
        
        $now = new \DateTime();
        $deadlineTime = new \DateTime($deadline->ba_waktu);
        
        if ($now > $deadlineTime) {
            return [
                'expired' => true,
                'days' => 0,
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'total_seconds' => 0,
                'deadline_waktu' => $deadline->ba_waktu
            ];
        }
        
        $interval = $now->diff($deadlineTime);
        
        return [
            'expired' => false,
            'days' => (int) $interval->format('%a'),
            'hours' => (int) $interval->format('%h'),
            'minutes' => (int) $interval->format('%i'),
            'seconds' => (int) $interval->format('%s'),
            'total_seconds' => $deadlineTime->getTimestamp() - $now->getTimestamp(),
            'deadline_waktu' => $deadline->ba_waktu
        ];
    }

    /**
     * Get deadline info for display
     */
    public function getDeadlineInfoForDisplay(): array
    {
        $remaining = $this->getRemainingTime();
        $deadline = $this->getGlobalDeadline();
        
        $info = [
            'has_deadline' => false,
            'is_expired' => false,
            'deadline_waktu' => null,
            'remaining' => null,
            'warning_level' => 'none' // none, info, warning, danger
        ];
        
        if ($deadline) {
            $info['has_deadline'] = true;
            $info['deadline_waktu'] = $deadline->ba_waktu;
        }
        
        if ($remaining) {
            $info['is_expired'] = $remaining['expired'];
            $info['remaining'] = $remaining;
            
            // Set warning level based on remaining time
            if ($remaining['expired']) {
                $info['warning_level'] = 'danger';
            } elseif ($remaining['total_seconds'] < 86400) { // Less than 24 hours
                $info['warning_level'] = 'danger';
            } elseif ($remaining['total_seconds'] < 259200) { // Less than 3 days
                $info['warning_level'] = 'warning';
            } elseif ($remaining['total_seconds'] < 604800) { // Less than 7 days
                $info['warning_level'] = 'info';
            }
        }
        
        return $info;
    }
}
