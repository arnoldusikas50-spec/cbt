<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * DashboardModel - Migrated from CI3 Dashboard_model
 * Handles dashboard data aggregation with Redis caching
 */
class DashboardModel extends Model
{
    protected $db;
    protected $cache;

    // Cache keys
    protected const CACHE_SETTING = 'dashboard_setting';
    protected const CACHE_TAHUN_ACTIVE = 'dashboard_tahun_active';
    protected const CACHE_SMT_ACTIVE = 'dashboard_semester_active';
    protected const CACHE_TAHUN_ALL = 'dashboard_tahun_all';
    protected const CACHE_SMT_ALL = 'dashboard_semester_all';
    protected const CACHE_RUNNING_TEXT = 'dashboard_running_text';

    // Cache TTL in seconds (5 minutes for most data)
    protected const CACHE_TTL = 300;
    protected const CACHE_TTL_LONG = 3600; // 1 hour for rarely changed data

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
    }

    /**
     * Clear all dashboard-related cache
     */
    public function clearCache(): void
    {
        $this->cache->delete(self::CACHE_SETTING);
        $this->cache->delete(self::CACHE_TAHUN_ACTIVE);
        $this->cache->delete(self::CACHE_SMT_ACTIVE);
        $this->cache->delete(self::CACHE_TAHUN_ALL);
        $this->cache->delete(self::CACHE_SMT_ALL);
        $this->cache->delete(self::CACHE_RUNNING_TEXT);
    }

    /**
     * Get application settings with caching
     */
    public function getSetting(): ?object
    {
        // Try cache first
        $cached = $this->cache->get(self::CACHE_SETTING);
        if ($cached !== null) {
            return $cached;
        }

        $query = $this->db->table('setting')->get();
        $setting = $query ? $query->getRow() : null;

        if ($setting) {
            // Add aliases for compatibility with views
            if (isset($setting->sekolah) && !isset($setting->nama_sekolah)) {
                $setting->nama_sekolah = $setting->sekolah;
            }
            if (isset($setting->logo_kiri) && !isset($setting->logo)) {
                $setting->logo = $setting->logo_kiri;
            }
            if (isset($setting->web) && !isset($setting->website)) {
                $setting->website = $setting->web;
            }
        }

        // Save to cache (long TTL as settings rarely change)
        $this->cache->save(self::CACHE_SETTING, $setting, self::CACHE_TTL_LONG);

        return $setting;
    }

    /**
     * Get active tahun pelajaran with caching
     */
    public function getTahunActive(): ?object
    {
        $cached = $this->cache->get(self::CACHE_TAHUN_ACTIVE);
        if ($cached !== null) {
            return $cached;
        }

        $query = $this->db->table('master_tp')
            ->where('active', 1)
            ->get();
        $result = $query ? $query->getRow() : null;

        $this->cache->save(self::CACHE_TAHUN_ACTIVE, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Get active semester with caching
     */
    public function getSemesterActive(): ?object
    {
        $cached = $this->cache->get(self::CACHE_SMT_ACTIVE);
        if ($cached !== null) {
            return $cached;
        }

        $query = $this->db->table('master_smt')
            ->where('active', 1)
            ->get();
        $result = $query ? $query->getRow() : null;

        $this->cache->save(self::CACHE_SMT_ACTIVE, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Get all tahun pelajaran with caching
     */
    public function getTahun(): array
    {
        $cached = $this->cache->get(self::CACHE_TAHUN_ALL);
        if ($cached !== null) {
            return $cached;
        }

        $query = $this->db->table('master_tp')
            ->orderBy('id_tp', 'DESC')
            ->get();
        $result = $query ? $query->getResult() : [];

        $this->cache->save(self::CACHE_TAHUN_ALL, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Get all semesters with caching
     */
    public function getSemester(): array
    {
        $cached = $this->cache->get(self::CACHE_SMT_ALL);
        if ($cached !== null) {
            return $cached;
        }

        $query = $this->db->table('master_smt')
            ->get();
        $result = $query ? $query->getResult() : [];

        $this->cache->save(self::CACHE_SMT_ALL, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Get running text/announcements with caching
     */
    public function getRunningText(): array
    {
        // Check if running_text table exists
        if (!$this->db->tableExists('running_text')) {
            return [];
        }

        $cached = $this->cache->get(self::CACHE_RUNNING_TEXT);
        if ($cached !== null) {
            return $cached;
        }

        $query = $this->db->table('running_text')->get();
        $result = $query ? $query->getResult() : [];

        $result = $result ?? [];

        $this->cache->save(self::CACHE_RUNNING_TEXT, $result, self::CACHE_TTL);

        return $result;
    }

    /**
     * Count total records in a table
     */
    public function total(string $table, ?string $where = null): int
    {
        if (empty($table))
            return 0;
        $builder = $this->db->table($table);

        if ($where) {
            $builder->where($where, null, false);
        }

        return $builder->countAllResults();
    }

    /**
     * Get total siswa per page
     */
    public function getTotalSiswa(int $tp, int $smt): int
    {
        return $this->db->table('kelas_siswa a')
            ->join('buku_induk b', 'a.id_siswa = b.id_siswa')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->where('b.status', '1')
            ->countAllResults();
    }

    /**
     * Get total wali kelas (teachers assigned to classes)
     */
    public function totalWaliKelas(int $tp, int $smt): int
    {
        try {
            return $this->db->table('master_kelas')
                ->where('id_tp', $tp)
                ->where('id_smt', $smt)
                ->where('guru_id IS NOT NULL')
                ->where('guru_id !=', 0)
                ->countAllResults();
        } catch (\Throwable $e) {
            // If column doesn't exist, return 0
            return 0;
        }
    }

    /**
     * Get total jadwal ujian
     */
    public function totalJadwal(int $tp, int $smt): int
    {
        return $this->db->table('cbt_jadwal')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->countAllResults();
    }

    /**
     * Get data siswa by username
     */
    public function getDataSiswa(string $username, int $tp, int $smt): ?object
    {
        $query = $this->db->table('master_siswa a')
            ->select('a.*, b.id_kelas, c.nama_kelas, c.kode_kelas, c.level_id')
            ->join('kelas_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('master_kelas c', 'b.id_kelas = c.id_kelas')
            ->where('a.username', $username)
            ->where('b.id_tp', $tp)
            ->where('b.id_smt', $smt)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get data guru by user ID
     */
    /**
     * Get data guru by user ID including role and wali kelas info
     */
    public function getDataGuruByUserId(int $userId, ?int $tp = null, ?int $smt = null): ?object
    {
        // If tp and smt not provided, get active ones
        if ($tp === null) {
            $tpObj = $this->getTahunActive();
            $tp = $tpObj ? $tpObj->id_tp : null;
        }
        if ($smt === null) {
            $smtObj = $this->getSemesterActive();
            $smt = $smtObj ? $smtObj->id_smt : null;
        }

        $builder = $this->db->table('master_guru a')
            ->select('a.*, a.nama_guru as nama_lengkap, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level as jabatan');
            
        if ($tp !== null && $smt !== null) {
            $builder->join('jabatan_guru b', 'a.id_guru = b.id_guru AND b.id_tp = ' . $tp . ' AND b.id_smt = ' . $smt, 'left')
                    ->join('master_kelas f', 'b.id_kelas = f.id_kelas AND f.id_tp = ' . $tp . ' AND f.id_smt = ' . $smt, 'left')
                    ->join('level_kelas g', 'f.level_id = g.id_level', 'left');
        } else {
            $builder->join('jabatan_guru b', 'a.id_guru = b.id_guru', 'left')
                    ->join('master_kelas f', 'b.id_kelas = f.id_kelas', 'left')
                    ->join('level_kelas g', 'f.level_id = g.id_level', 'left');
        }
            
        $query = $builder->where('a.id_user', $userId)->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get data panitia by user ID
     */
    public function getDataPanitiaByUserId(int $userId, int $tp, int $smt): ?object
    {
        $query = $this->db->table('master_panitia')
            ->where('id_user', $userId)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get data guru by username including wali_kelas info
     */
    public function getDataGuruByUsername(string $username, int $tp, int $smt): ?object
    {
        $query = $this->db->table('master_guru a')
            ->select('a.*, a.nama_guru as nama_lengkap, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level as jabatan')
            ->join('jabatan_guru b', 'a.id_guru = b.id_guru AND b.id_tp = ' . $tp . ' AND b.id_smt = ' . $smt, 'left')
            ->join('master_kelas f', 'b.id_kelas = f.id_kelas AND f.id_tp = ' . $tp . ' AND f.id_smt = ' . $smt, 'left')
            ->join('level_kelas g', 'f.level_id = g.id_level', 'left')
            ->where('a.username', $username)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get data guru by ID including wali_kelas info
     */
    public function getDataGuruById(int $id_guru, int $tp, int $smt): ?object
    {
        $query = $this->db->table('master_guru a')
            ->select('a.*, a.nama_guru as nama_lengkap, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level as jabatan')
            ->join('jabatan_guru b', 'a.id_guru = b.id_guru AND b.id_tp = ' . $tp . ' AND b.id_smt = ' . $smt, 'left')
            ->join('master_kelas f', 'b.id_kelas = f.id_kelas AND f.id_tp = ' . $tp . ' AND f.id_smt = ' . $smt, 'left')
            ->join('level_kelas g', 'f.level_id = g.id_level', 'left')
            ->where('a.id_guru', $id_guru)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Update any table (Multi or Single)
     */
    public function updateData(string $table, $data, string $pk, $id = null, bool $batch = false): bool
    {
        if ($batch) {
            return $this->db->table($table)->updateBatch($data, $pk);
        }
        return $this->db->table($table)->where($pk, $id)->update($data);
    }

    /**
     * Delete from any table
     */
    public function hapus(string $table, $ids, string $pk): bool
    {
        if (is_array($ids)) {
            return $this->db->table($table)->whereIn($pk, $ids)->delete();
        }
        return $this->db->table($table)->where($pk, $ids)->delete();
    }

    /**
     * Get Data Tahun for Datatable
     */
    public function getDataTahun(): array
    {
        return $this->db->table('master_tp')
            ->orderBy('id_tp', 'ASC')
            ->get()
            ->getResult();
    }

    public function getTahunByTahun($tahun)
    {
        $query = $this->db->table('master_tp')
            ->where('tahun', $tahun)
            ->get();
        return $query ? $query->getRow() : null;
    }

    public function getTpBefore($tahun)
    {
        $thns = explode('/', $tahun ?? '');
        if (count($thns) < 2)
            return null;
        $thn_before = ($thns[0] - 1) . '/' . $thns[0];
        return $this->getTahunByTahun($thn_before);
    }

    /**
     * Get setting rapor
     */
    public function getSettingRapor()
    {
        if (!$this->db->tableExists('setting_rapor')) {
            return null;
        }
        $query = $this->db->table('setting_rapor')->get();
        return $query ? $query->getRow() : null;
    }
}
