<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * DashboardModel - Migrated from CI3 Dashboard_model
 * Handles dashboard data aggregation
 */
class DashboardModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get application settings
     * Adds compatibility aliases for property names used in different views
     */
    public function getSetting(): ?object
    {
        $setting = $this->db->table('setting')->get()->getRow();

        if ($setting) {
            // Add aliases for compatibility with views
            // CI3 uses 'sekolah', but some CI4 views use 'nama_sekolah'
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

        return $setting;
    }

    /**
     * Get active tahun pelajaran
     */
    public function getTahunActive(): ?object
    {
        return $this->db->table('master_tp')
            ->where('active', 1)
            ->get()
            ->getRow();
    }

    /**
     * Get active semester
     */
    public function getSemesterActive(): ?object
    {
        return $this->db->table('master_smt')
            ->where('active', 1)
            ->get()
            ->getRow();
    }

    /**
     * Get all tahun pelajaran
     */
    public function getTahun(): array
    {
        return $this->db->table('master_tp')
            ->orderBy('id_tp', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Get all semesters
     */
    public function getSemester(): array
    {
        return $this->db->table('master_smt')
            ->get()
            ->getResult();
    }

    /**
     * Count total records in a table
     */
    public function total(string $table, ?string $where = null): int
    {
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
        return $this->db->table('master_siswa a')
            ->select('a.*, b.id_kelas, c.nama_kelas, c.kode_kelas')
            ->join('kelas_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('master_kelas c', 'b.id_kelas = c.id_kelas')
            ->where('a.username', $username)
            ->where('b.id_tp', $tp)
            ->where('b.id_smt', $smt)
            ->get()
            ->getRow();
    }

    /**
     * Get data guru by user ID
     */
    public function getDataGuruByUserId(int $userId, int $tp, int $smt): ?object
    {
        return $this->db->table('master_guru a')
            ->select('a.*')
            ->where('a.id_user', $userId)
            ->get()
            ->getRow();
    }

    /**
     * Get data panitia by user ID
     */
    public function getDataPanitiaByUserId(int $userId, int $tp, int $smt): ?object
    {
        return $this->db->table('master_panitia a')
            ->join('users b', 'a.username = b.username')
            ->where('b.id', $userId)
            ->get()
            ->getRow();
    }

    /**
     * Get running text/announcements
     */
    public function getRunningText(): string
    {
        $result = $this->db->table('setting')
            ->select('running_text')
            ->get()
            ->getRow();

        return $result->running_text ?? '';
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
}
