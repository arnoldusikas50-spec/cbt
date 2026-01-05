<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * MasterModel - Migrated from CI3 Master_model
 * Handles master data operations (siswa, guru, kelas, mapel)
 */
class MasterModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // ==========================================
    // SISWA FUNCTIONS
    // ==========================================

    /**
     * Get all siswa with pagination
     */
    public function getAllSiswa(int $tp, int $smt, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        $builder = $this->db->table('master_siswa a')
            ->select('a.*, b.id_kelas, c.nama_kelas, c.kode_kelas')
            ->join('kelas_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->join('master_kelas c', 'b.id_kelas = c.id_kelas', 'left')
            ->where('b.id_tp', $tp)
            ->where('b.id_smt', $smt);

        if ($search) {
            $builder->groupStart()
                ->like('a.nama', $search)
                ->orLike('a.nis', $search)
                ->orLike('a.nisn', $search)
                ->groupEnd();
        }

        return $builder->orderBy('a.nama', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    /**
     * Count total siswa
     */
    public function getTotalSiswa(int $tp, int $smt, ?string $search = null): int
    {
        $builder = $this->db->table('master_siswa a')
            ->join('kelas_siswa b', 'a.id_siswa = b.id_siswa', 'left')
            ->where('b.id_tp', $tp)
            ->where('b.id_smt', $smt);

        if ($search) {
            $builder->groupStart()
                ->like('a.nama', $search)
                ->orLike('a.nis', $search)
                ->orLike('a.nisn', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Get siswa by ID
     */
    public function getSiswa(int $id): ?object
    {
        return $this->db->table('master_siswa')
            ->where('id_siswa', $id)
            ->get()
            ->getRow();
    }

    /**
     * Get siswa by username
     */
    public function getSiswaByUsername(string $username): ?object
    {
        return $this->db->table('master_siswa')
            ->where('username', $username)
            ->get()
            ->getRow();
    }

    /**
     * Save siswa
     */
    public function saveSiswa(array $data): bool
    {
        return $this->db->table('master_siswa')->insert($data);
    }

    /**
     * Update siswa
     */
    public function updateSiswa(int $id, array $data): bool
    {
        return $this->db->table('master_siswa')
            ->where('id_siswa', $id)
            ->update($data);
    }

    /**
     * Delete siswa
     */
    public function deleteSiswa(int $id): bool
    {
        return $this->db->table('master_siswa')
            ->where('id_siswa', $id)
            ->delete();
    }

    // ==========================================
    // GURU FUNCTIONS
    // ==========================================

    /**
     * Get all guru
     */
    public function getAllGuru(?string $search = null): array
    {
        $builder = $this->db->table('master_guru');

        if ($search) {
            $builder->groupStart()
                ->like('nama_guru', $search)
                ->orLike('nip', $search)
                ->orLike('nuptk', $search)
                ->groupEnd();
        }

        return $builder->orderBy('nama_guru', 'ASC')->get()->getResult();
    }

    /**
     * Get guru by ID
     */
    public function getGuru(int $id): ?object
    {
        return $this->db->table('master_guru')
            ->where('id_guru', $id)
            ->get()
            ->getRow();
    }

    /**
     * Get guru by user ID
     */
    public function getGuruByUserId(int $userId): ?object
    {
        return $this->db->table('master_guru')
            ->where('id_user', $userId)
            ->get()
            ->getRow();
    }

    /**
     * Save guru
     */
    public function saveGuru(array $data): bool
    {
        return $this->db->table('master_guru')->insert($data);
    }

    /**
     * Update guru
     */
    public function updateGuru(int $id, array $data): bool
    {
        return $this->db->table('master_guru')
            ->where('id_guru', $id)
            ->update($data);
    }

    /**
     * Delete guru
     */
    public function deleteGuru(int $id): bool
    {
        return $this->db->table('master_guru')
            ->where('id_guru', $id)
            ->delete();
    }

    // ==========================================
    // KELAS FUNCTIONS
    // ==========================================

    /**
     * Get all kelas by TP and semester
     */
    public function getAllKelas(int $tp, int $smt): array
    {
        return $this->db->table('master_kelas a')
            ->select('a.*, b.nama_guru as wali')
            ->join('master_guru b', 'a.guru_id = b.id_guru', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('a.level', 'ASC')
            ->orderBy('a.nama_kelas', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get kelas by ID
     */
    public function getKelas(int $id): ?object
    {
        return $this->db->table('master_kelas')
            ->where('id_kelas', $id)
            ->get()
            ->getRow();
    }

    /**
     * Get kelas by level
     */
    public function getKelasByLevel(int $tp, int $smt, int $level): array
    {
        return $this->db->table('master_kelas')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->where('level', $level)
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Save kelas
     */
    public function saveKelas(array $data): bool
    {
        return $this->db->table('master_kelas')->insert($data);
    }

    /**
     * Update kelas
     */
    public function updateKelas(int $id, array $data): bool
    {
        return $this->db->table('master_kelas')
            ->where('id_kelas', $id)
            ->update($data);
    }

    /**
     * Delete kelas
     */
    public function deleteKelas(int $id): bool
    {
        return $this->db->table('master_kelas')
            ->where('id_kelas', $id)
            ->delete();
    }

    /**
     * Count siswa in kelas
     */
    public function countSiswaInKelas(int $idKelas, int $tp, int $smt): int
    {
        return $this->db->table('kelas_siswa')
            ->where('id_kelas', $idKelas)
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->countAllResults();
    }

    // ==========================================
    // MAPEL FUNCTIONS
    // ==========================================

    /**
     * Get all mapel
     */
    public function getAllMapel(?int $jenjang = null): array
    {
        $builder = $this->db->table('master_mapel');

        if ($jenjang !== null) {
            $builder->where('jenjang', $jenjang);
        }

        return $builder->orderBy('urut', 'ASC')
            ->orderBy('nama_mapel', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get mapel by ID
     */
    public function getMapel(int $id): ?object
    {
        return $this->db->table('master_mapel')
            ->where('id_mapel', $id)
            ->get()
            ->getRow();
    }

    /**
     * Save mapel
     */
    public function saveMapel(array $data): bool
    {
        return $this->db->table('master_mapel')->insert($data);
    }

    /**
     * Update mapel
     */
    public function updateMapel(int $id, array $data): bool
    {
        return $this->db->table('master_mapel')
            ->where('id_mapel', $id)
            ->update($data);
    }

    /**
     * Delete mapel
     */
    public function deleteMapel(int $id): bool
    {
        return $this->db->table('master_mapel')
            ->where('id_mapel', $id)
            ->delete();
    }

    // ==========================================
    // JURUSAN/PROGRAM FUNCTIONS
    // ==========================================

    /**
     * Get all jurusan
     */
    public function getAllJurusan(): array
    {
        return $this->db->table('master_jurusan')
            ->orderBy('nama_jurusan', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get jurusan by ID
     */
    public function getJurusan(int $id): ?object
    {
        return $this->db->table('master_jurusan')
            ->where('id_jurusan', $id)
            ->get()
            ->getRow();
    }

    // ==========================================
    // DROPDOWN HELPERS
    // ==========================================

    /**
     * Get kelas for dropdown
     */
    public function getDropdownKelas(int $tp, int $smt): array
    {
        $result = [];
        $kelas = $this->getAllKelas($tp, $smt);

        foreach ($kelas as $k) {
            $result[$k->id_kelas] = $k->nama_kelas;
        }

        return $result;
    }

    /**
     * Get mapel for dropdown
     */
    public function getDropdownMapel(): array
    {
        $result = [];
        $mapels = $this->getAllMapel();

        foreach ($mapels as $m) {
            $result[$m->id_mapel] = $m->nama_mapel;
        }

        return $result;
    }

    /**
     * Get guru for dropdown
     */
    public function getDropdownGuru(): array
    {
        $result = [];
        $gurus = $this->getAllGuru();

        foreach ($gurus as $g) {
            $result[$g->id_guru] = $g->nama_guru;
        }

        return $result;
    }

    /**
     * Create record in any table
     */
    public function create(string $table, array $data)
    {
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    /**
     * Update record in any table
     */
    public function updateData(string $table, array $data, string $pk, $id)
    {
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
     * Get Jml Hari Efektif
     */
    public function getJmlHariEfektif(string $id)
    {
        return $this->db->table('master_hari_efektif')
            ->where('id_hari_efektif', $id)
            ->get()
            ->getRow();
    }

    /**
     * Get guru mapel and kelas
     */
    public function getGuruMapelKelas(int $idGuru, int $tp, int $smt)
    {
        return $this->db->table('jabatan_guru')
            ->where('id_guru', $idGuru)
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->get()
            ->getRow();
    }

    /**
     * Get all kelas by array of IDs
     */
    public function getAllKelasByArrayId(int $tp, int $smt, array $ids): array
    {
        if (empty($ids))
            return [];
        return $this->db->table('master_kelas')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->whereIn('id_kelas', $ids)
            ->orderBy('level', 'ASC')
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get levels by jenjang
     */
    public function getLevelsByJenjang(int $jenjang): array
    {
        return $this->db->table('level_kelas')
            ->join('master_jenjang', 'level_kelas.id_jenjang = master_jenjang.id_jenjang')
            ->where('master_jenjang.id_jenjang', $jenjang)
            ->get()
            ->getResult();
    }

    /**
     * Get unique religions from master_siswa
     */
    public function getAgamaSiswa(): array
    {
        $result = $this->db->table('master_siswa')
            ->select('agama')
            ->distinct()
            ->where('agama IS NOT NULL', null, false)
            ->where('agama != "0"', null, false)
            ->notLike('agama', 'Pilih')
            ->get()
            ->getResult();

        $ret = ['-' => 'Bukan Mapel Agama'];
        foreach ($result as $row) {
            $ret[$row->agama] = $row->agama;
        }
        return $ret;
    }

    public function getAllKodeKelas($tp, $smt)
    {
        $result = $this->db->table('master_kelas')
            ->select('id_kelas, kode_kelas')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->get()
            ->getResult();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_kelas] = $row->kode_kelas;
        }
        return $ret;
    }

}
