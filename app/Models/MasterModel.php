<?php

namespace App\Models;

use App\Traits\SiswaAktifTrait;
use CodeIgniter\Model;

/**
 * MasterModel - Migrated from CI3 Master_model
 * Handles master data operations (siswa, guru, kelas, mapel)
 */
class MasterModel extends Model
{
    use SiswaAktifTrait;

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
     * Returns students including those not yet assigned to a class
     */
    public function getAllSiswa(int $tp, int $smt, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        $builder = $this->db->table('master_siswa a')
            ->select('a.*, b.id_kelas, c.nama_kelas, c.kode_kelas')
            // Use LEFT JOIN with tp/smt in JOIN condition (not WHERE) to include unassigned students
            ->join('kelas_siswa b', "a.id_siswa = b.id_siswa AND b.id_tp = $tp AND b.id_smt = $smt", 'left')
            ->join('master_kelas c', 'b.id_kelas = c.id_kelas', 'left')
            ->join('buku_induk bi', 'bi.id_siswa = a.id_siswa AND bi.status = "1"'); // Only active students

        if ($search) {
            $builder->groupStart()
                ->like('a.nama', $search)
                ->orLike('a.nis', $search)
                ->orLike('a.nisn', $search)
                ->groupEnd();
        }

        $query = $builder->orderBy('a.nama', 'ASC')
            ->limit($limit, $offset)
            ->get();
        return $query ? $query->getResult() : [];
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
        $query = $this->db->table('master_siswa')
            ->where('id_siswa', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get siswa by username
     */
    public function getSiswaByUsername(string $username): ?object
    {
        $query = $this->db->table('master_siswa')
            ->where('username', $username)
            ->get();
        return $query ? $query->getRow() : null;
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

    /**
     * Get siswa by ID including buku_induk status
     */
    public function getSiswaById(int $id): ?object
    {
        $query = $this->db->table('master_siswa a')
            ->select('a.*, b.status')
            ->join('buku_induk b', 'a.id_siswa = b.id_siswa', 'left')
            ->where('a.id_siswa', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    public function getAllGuru()
    {
        $query = $this->db->table('master_guru')->orderBy('nama_guru', 'ASC')->get();
        return $query ? $query->getResult() : [];
    }

    public function getAllGuruWithUserId()
    {
        $query = $this->db->table('master_guru a')
            ->select('a.id_guru, a.nama_guru, b.id as id_user')
            ->join('users b', 'a.username = b.username')
            ->orderBy('a.nama_guru', 'ASC')
            ->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get students with custom pagination and filtering (Ported from CI3)
     *
     * Master data siswa TIDAK disaring berdasarkan status: admin harus tetap
     * bisa melihat siswa LULUS/PINDAH/KELUAR untuk mengubah statusnya kembali
     * menjadi AKTIF. Penyaringan status hanya berlaku pada daftar peserta
     * ujian/nilai/kehadiran (lihat SiswaAktifTrait).
     *
     * @param string $filter 0 = semua, 1 = siswa aktif, 2 = non-aktif
     *                       (lulus/pindah/keluar), 3 = per kelas
     */
    public function getSiswaPage(int $tp, int $smt, int $offset, int $limit, string $filter, ?string $kelas = null, ?string $search = null): array
    {
        $builder = $this->db->table('master_siswa a')
            ->select('a.id_siswa, a.dapo_id, a.foto, a.nama, a.nis, a.nisn, a.nik, a.jenis_kelamin, a.tempat_lahir, a.tanggal_lahir, a.alamat, a.nama_ayah, a.nama_ibu, a.hp, a.email, d.id_kelas, a.agama, f.nama_kelas, us.active, u.status')
            ->join('buku_induk u', 'u.id_siswa = a.id_siswa', 'left')
            ->limit($limit, $offset);

        if ($filter == "0") {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('master_kelas f', 'f.id_kelas = d.id_kelas', 'left')
                ->join('users us', 'us.username = a.username');
        } elseif ($filter == "1") {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('master_kelas f', 'f.id_kelas = d.id_kelas', 'left')
                ->join('users us', 'us.username = a.username AND us.active = "1"')
                ->where('u.status', '1');
        } elseif ($filter == "2") {
            // Non-aktif: LULUS / PINDAH / KELUAR — tanpa syarat status akun
            // supaya admin tetap bisa finds & mengubah statusnya kembali.
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('master_kelas f', 'f.id_kelas = d.id_kelas', 'left')
                ->join('users us', 'us.username = a.username')
                ->whereIn('u.status', $this->statusSiswaTidakAktif());
        } elseif ($filter == "3") {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt AND d.id_kelas = $kelas")
                ->join('master_kelas f', 'f.id_kelas = d.id_kelas')
                ->join('users us', 'us.username = a.username AND us.active = "1"');
        } else {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('master_kelas f', 'f.id_kelas = d.id_kelas', 'left')
                ->join('users us', 'us.username = a.username AND us.active = "0"');
        }

        if ($search) {
            $builder->groupStart()
                ->like('a.nama', $search)
                ->orLike('a.nis', $search)
                ->orLike('a.nisn', $search)
                ->groupEnd();
        }

        $query = $builder->orderBy('a.nama', 'ASC')->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Count total students with filtering (Ported from CI3)
     *
     * Harus selalu sinkron dengan getSiswaPage() agar paginasi konsisten.
     */
    public function getSiswaTotalPage(int $tp, int $smt, string $filter, ?string $kelas = null, ?string $search = null): int
    {
        $builder = $this->db->table('master_siswa a')
            ->join('buku_induk u', 'u.id_siswa = a.id_siswa', 'left');

        if ($filter == "0") {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('users us', 'us.username = a.username');
        } elseif ($filter == "1") {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('users us', 'us.username = a.username AND us.active = "1"')
                ->where('u.status', '1');
        } elseif ($filter == "2") {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('users us', 'us.username = a.username')
                ->whereIn('u.status', $this->statusSiswaTidakAktif());
        } elseif ($filter == "3") {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt AND d.id_kelas = $kelas")
                ->join('users us', 'us.username = a.username AND us.active = "1"');
        } else {
            $builder->join('kelas_siswa d', "d.id_siswa = a.id_siswa AND d.id_tp = $tp AND d.id_smt = $smt", 'left')
                ->join('users us', 'us.username = a.username AND us.active = "0"');
        }

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
     * Get students by array of IDs
     */
    public function getSiswaByArrId(array $ids): array
    {
        if (empty($ids))
            return [];
        $builder = $this->db->table('master_siswa a')
            ->select('a.id_siswa, a.nama, f.nama_kelas')
            ->join('kelas_siswa b', 'b.id_siswa = a.id_siswa', 'left')
            ->join('master_kelas f', 'f.id_kelas = b.id_kelas', 'left')
            ->whereIn('a.id_siswa', $ids);

        $query = $builder->get();
        $result = $query ? $query->getResult() : [];

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_siswa] = $row;
        }
        return $ret;
    }

    public function getSiswaByArrNisn(array $arr_nisn, array $arr_nis, array $arr_username): array
    {
        $query = $this->db->table('master_siswa')
            ->select('id_siswa, nama, nisn, nis, username')
            ->whereIn('nisn', $arr_nisn)
            ->orWhereIn('nis', $arr_nis)
            ->orWhereIn('username', $arr_username)
            ->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get students by list of usernames
     */
    public function getSiswaByUsernames(array $usernames): array
    {
        if (empty($usernames))
            return [];
        $query = $this->db->table('master_siswa')
            ->select('id_siswa, username')
            ->whereIn('username', $usernames)
            ->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get students for new class assignment
     */
    public function getSiswaKelasBaru(int $tp, int $smt): array
    {
        $builder = $this->db->table('kelas_siswa a')
            ->select('b.id_siswa, b.nama, f.id_kelas, f.nama_kelas, f.kode_kelas')
            ->join('master_siswa b', 'b.id_siswa = a.id_siswa')
            ->join('master_kelas f', 'f.id_kelas = a.id_kelas')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt);

        $query = $builder->get();
        $result = $query ? $query->getResult() : [];

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_siswa] = $row;
        }
        return $ret;
    }

    /**
     * Get data siswa by kelas with optional pagination and search
     *
     * @param bool $aktifOnly Jika true, hanya siswa berstatus AKTIF
     *                        (buku_induk.status = 1) yang dikembalikan.
     *                        WAJIB true untuk daftar peserta ujian/penilaian.
     *                        Biarkan false untuk keperluan administrasi
     *                        (roster kelas, rapor) agar siswa yang sudah
     *                        LULUS/PINDAH/KELUAR tetap bisa dikelola.
     */
    public function getDataSiswaByKelas(int $tp, int $smt, int $id_kelas, int $offset = 0, int $limit = 0, ?string $search = null, bool $aktifOnly = false): array
    {
        $builder = $this->db->table('kelas_siswa a')
            ->select('b.id_siswa, b.nama, b.nis, b.nisn, b.jenis_kelamin, b.username, b.password, b.foto, b.agama, f.nama_kelas, f.id_kelas, us.active')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa', 'right')
            ->join('master_kelas f', 'a.id_kelas = f.id_kelas')
            ->join('users us', 'b.username = us.username', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->where('a.id_kelas', $id_kelas);

        if ($aktifOnly) {
            $this->onlySiswaAktif($builder, 'b');
        }

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        if ($search) {
            $builder->groupStart()
                ->like('b.nama', $search)
                ->orLike('b.nis', $search)
                ->orLike('b.nisn', $search)
                ->groupEnd();
        }

        $query = $builder->orderBy('b.nama', 'ASC')->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Count data siswa by kelas for pagination
     */
    public function getDataSiswaByKelasPage(int $tp, int $smt, int $id_kelas, ?string $search = null): int
    {
        $builder = $this->db->table('kelas_siswa a')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->where('a.id_kelas', $id_kelas);

        if ($search) {
            $builder->groupStart()
                ->like('b.nama', $search)
                ->orLike('b.nis', $search)
                ->orLike('b.nisn', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Alias for getDataSiswaByKelas for backward compatibility
     *
     * @param bool $aktifOnly Lihat getDataSiswaByKelas().
     */
    public function getSiswaByKelas(int $id_kelas, int $tp, int $smt, bool $aktifOnly = false): array
    {
        return $this->getDataSiswaByKelas($tp, $smt, $id_kelas, 0, 0, null, $aktifOnly);
    }

    // ==========================================
    // GURU FUNCTIONS
    // ==========================================

    /**
     * Get all guru with full details (Ported from CI3)
     */
    public function getAllDataGuru(int $tp, int $smt): array
    {
        return $this->db->table('master_guru a')
            ->select('a.*, b.id_jabatan, b.id_kelas, b.mapel_kelas, b.ekstra_kelas, c.id_level, c.level, d.nama_kelas, e.tahun, f.nama_smt, g.active')
            ->join('jabatan_guru b', "a.id_guru=b.id_guru AND b.id_tp=$tp AND b.id_smt=$smt", 'left')
            ->join('level_guru c', 'b.id_jabatan=c.id_level', 'left')
            ->join('master_kelas d', "b.id_kelas=d.id_kelas AND d.id_tp=$tp AND d.id_smt=$smt", 'left')
            ->join('master_tp e', 'b.id_tp=e.id_tp', 'left')
            ->join('master_smt f', 'b.id_smt=f.id_smt', 'left')
            ->join('users g', 'a.username=g.username', 'left')
            ->orderBy('c.id_level', 'DESC')
            ->orderBy('a.id_guru', 'ASC')
            ->get()->getResult();
    }

    /**
     * Get data guru for DataTables (Ported from CI3)
     */
    public function getDataGuru(int $tp, int $smt, ?string $search = null): array
    {
        $builder = $this->db->table('master_guru a')
            ->select('a.*, b.id_jabatan, b.id_kelas, b.mapel_kelas, c.id_level, c.level, d.nama_kelas, e.tahun, f.nama_smt')
            ->join('jabatan_guru b', "a.id_guru=b.id_guru AND b.id_tp=$tp AND b.id_smt=$smt", 'left')
            ->join('level_guru c', 'b.id_jabatan=c.id_level', 'left')
            ->join('master_kelas d', "b.id_kelas=d.id_kelas AND d.id_tp=$tp AND d.id_smt=$smt", 'left')
            ->join('master_tp e', 'b.id_tp=e.id_tp', 'left')
            ->join('master_smt f', 'b.id_smt=f.id_smt', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('a.nama_guru', $search)
                ->orLike('a.nip', $search)
                ->orLike('a.kode_guru', $search)
                ->groupEnd();
        }

        return $builder->get()->getResult();
    }

    /**
     * Get guru by ID including jabatan (Ported from CI3)
     */
    public function getGuruById(int $id, ?int $id_tp = null, ?int $id_smt = null): ?object
    {
        $builder = $this->db->table('master_guru a')
            ->select('a.*, b.id_jabatan, b.id_kelas, b.mapel_kelas, b.ekstra_kelas, c.*')
            ->join('jabatan_guru b', 'a.id_guru=b.id_guru', 'left')
            ->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');

        if ($id_tp !== null && $id_smt !== null) {
            $builder->select('d.*')
                ->join('master_kelas d', "a.id_guru=d.guru_id AND d.id_tp=$id_tp AND d.id_smt=$id_smt", 'left');
        }

        $query = $builder->where('a.id_guru', $id)->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Detail jabatan guru (Ported from CI3)
     */
    public function getDetailJabatanGuru(int $id_guru): array
    {
        $builder = $this->db->table('master_guru a')
            ->select('a.id_guru, a.nama_guru, b.id_tp, b.id_smt, b.mapel_kelas, b.ekstra_kelas, c.id_level, c.level, d.id_kelas, d.nama_kelas')
            ->join('jabatan_guru b', 'a.id_guru=b.id_guru', 'left')
            ->join('level_guru c', 'b.id_jabatan=c.id_level', 'left')
            ->join('master_kelas d', 'b.id_kelas=d.id_kelas', 'left')
            ->where('a.id_guru', $id_guru);

        $query = $builder->get();
        $result = $query ? $query->getResult() : [];

        $ret = [];
        foreach ($result as $row) {
            if ($row->id_tp) {
                $ret[$row->id_tp][$row->id_smt] = $row;
            }
        }
        return $ret;
    }

    /**
     * Jabatan guru for specific TP/SMT (Ported from CI3)
     */
    public function getJabatanGuru(int $id_guru, int $tp, int $smt): ?object
    {
        $query = $this->db->table('master_guru a')
            ->select('a.id_guru, a.nama_guru, b.mapel_kelas, b.ekstra_kelas, c.id_level, c.level, d.id_kelas, d.nama_kelas')
            ->join('jabatan_guru b', "a.id_guru=b.id_guru AND b.id_tp=$tp AND b.id_smt=$smt", 'left')
            ->join('level_guru c', 'b.id_jabatan=c.id_level', 'left')
            ->join('master_kelas d', "b.id_kelas=d.id_kelas AND d.id_tp=$tp AND d.id_smt=$smt", 'left')
            ->where('a.id_guru', $id_guru)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Class/Mapel assignment for guru (Ported from CI3)
     */
    public function getGuruMapel(int $tp, int $smt, ?array $ids_guru = null): array
    {
        $builder = $this->db->table('jabatan_guru a')
            ->select('a.mapel_kelas, a.ekstra_kelas, a.id_jabatan, a.id_kelas, b.id_guru, b.nama_guru')
            ->join('master_guru b', 'a.id_guru=b.id_guru')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt);

        if ($ids_guru) {
            $builder->whereIn('a.id_guru', $ids_guru);
        }

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Update guru by ID
     */
    public function updateGuru(int $id, array $data): bool
    {
        return $this->db->table('master_guru')
            ->where('id_guru', $id)
            ->update($data);
    }

    /**
     * Delete guru by ID
     */
    public function deleteGuru($id): bool
    {
        if (is_array($id)) {
            return $this->db->table('master_guru')->whereIn('id_guru', $id)->delete();
        }
        return $this->db->table('master_guru')->where('id_guru', $id)->delete();
    }

    /**
     * Delete teacher from linked tables
     */
    public function deleteGuruLinked(int $id_guru): bool
    {
        // This is a custom helper to avoid manual table listing
        $tables = [
            'jabatan_guru' => 'id_guru',
            'kelas_materi' => 'id_guru',
            'kelas_catatan_mapel' => 'id_guru',
            'cbt_bank_soal' => 'bank_guru_id',
            'post' => 'dari',
            'post_comments' => 'dari',
            'post_reply' => 'dari'
        ];

        foreach ($tables as $table => $field) {
            $this->db->table($table)->where($field, $id_guru)->delete();
        }

        return $this->deleteGuru($id_guru);
    }

    // ==========================================
    // KELAS FUNCTIONS
    // ==========================================

    /**
     * Get all kelas by TP and semester
     */
    public function getAllKelas(int $tp, int $smt): array
    {
        $builder = $this->db->table('master_kelas a')
            ->select('a.*, b.nama_guru as wali')
            ->join('master_guru b', 'a.guru_id = b.id_guru', 'left')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->orderBy('a.level_id', 'ASC')
            ->orderBy('a.nama_kelas', 'ASC');

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get kelas by ID
     */
    public function getKelasById(int $id): ?object
    {
        $query = $this->db->table('master_kelas')
            ->select('id_kelas, nama_kelas, level_id, jumlah_siswa')
            ->where('id_kelas', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get kelas by level
     */
    public function getKelasByLevel(int $tp, int $smt, int $level): array
    {
        return $this->db->table('master_kelas')
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->where('level_id', $level)
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

        $query = $builder->orderBy('urutan_tampil', 'ASC')
            ->orderBy('nama_mapel', 'ASC')
            ->get();
        return $query ? $query->getResult() : [];
    }

    public function getDataKelompokMapel()
    {
        $result = $this->db->table('master_kelompok_mapel')
            ->where('id_parent', '0')
            ->orderBy('kode_kel_mapel')
            ->get()
            ->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_kel_mapel] = $row;
        }
        return $ret;
    }

    public function getDataSubKelompokMapel()
    {
        $result = $this->db->table('master_kelompok_mapel')
            ->where('id_parent <> 0')
            ->orderBy('kode_kel_mapel')
            ->get()
            ->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_kel_mapel] = $row;
        }
        return $ret;
    }

    public function getKategoriKelompokMapel()
    {
        return $this->db->table('master_kelompok_mapel')
            ->select('kode_kel_mapel, kategori')
            ->groupStart()
            ->where('kategori', 'WAJIB')
            ->orWhere('kategori', 'AGAMA (Kemenag)')
            ->groupEnd()
            ->get()
            ->getResult();
    }

    /**
     * Get mapel by ID
     */
    public function getMapel(int $id): ?object
    {
        $query = $this->db->table('master_mapel')
            ->where('id_mapel', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    public function getMapelById(int $id): ?object
    {
        return $this->getMapel($id);
    }

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
     * Get panitia for dropdown
     */
    public function getDropdownPanitia(): array
    {
        $result = [];
        $query = $this->db->table('master_panitia')->get();
        $panitias = $query ? $query->getResult() : [];

        foreach ($panitias as $p) {
            $result['P' . $p->id_panitia] = $p->nama_panitia;
        }

        return $result;
    }

    /**
     * Create record in any table
     */
    public function create(string $table, array $data)
    {
        return $this->db->table($table)->insert($data);
    }

    /**
     * Create multiple records
     */
    public function insertBatchData(string $table, array $data)
    {
        return $this->db->table($table)->insertBatch($data);
    }

    /**
     * Update record in any table
     */
    public function updateData(string $table, array $data, string $pk, $id)
    {
        return $this->db->table($table)->where($pk, $id)->update($data);
    }

    /**
     * Update multiple records
     */
    public function updateBatchData(string $table, array $data, string $pk)
    {
        return $this->db->table($table)->updateBatch($data, $pk);
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
        $query = $this->db->table('master_hari_efektif')
            ->where('id_hari_efektif', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get guru mapel and kelas
     */
    public function getGuruMapelKelas(int $idGuru, int $tp, int $smt)
    {
        $query = $this->db->table('jabatan_guru')
            ->where('id_guru', $idGuru)
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->get();
        return $query ? $query->getRow() : null;
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
            ->orderBy('level_id', 'ASC')
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get all jurusan
     */
    public function getAllJurusan(): array
    {
        // Prefer master_jurusan if exists and has data, otherwise fallback to jurusan (legacy)
        $builder = $this->db->table('master_jurusan');
        if ($this->db->tableExists('master_jurusan')) {
            $rows = $builder->orderBy('nama_jurusan', 'ASC')->get()->getResult();
            $normalized = [];
            foreach ($rows as $r) {
                $r->singkatan = $r->kode_jurusan ?? '';
                $normalized[] = $r;
            }
            if (count($normalized) > 0) {
                return $normalized;
            }
        }
        // Fallback legacy table (column jurusan_id -> id_jurusan)
        if ($this->db->tableExists('jurusan')) {
            $rows = $this->db->table('jurusan')->orderBy('nama_jurusan', 'ASC')->get()->getResult();
            $normalized = [];
            foreach ($rows as $r) {
                $obj = (object) [
                    'id_jurusan' => $r->jurusan_id ?? ($r->id_jurusan ?? null),
                    'nama_jurusan' => $r->nama_jurusan ?? '',
                    'singkatan' => $r->singkatan ?? '',
                ];
                $normalized[] = $obj;
            }
            return $normalized;
        }
        return [];
    }

    /**
     * Get jurusan by ID
     */
    public function getJurusan(int $id): ?object
    {
        $query = $this->db->table('master_jurusan')
            ->where('id_jurusan', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get levels by jenjang
     */
    public function getLevelsByJenjang(int $jenjang): array
    {
        $mapLevel = [
            1 => [1, 2, 3, 4, 5, 6],   // SD/MI
            2 => [7, 8, 9],            // SMP/MTS
            3 => [10, 11, 12],         // SMA/MA/SMK
            4 => [10, 11, 12],         // SMK
        ];

        // Jenjang belum/ salah diisi di Pengaturan (0, kosong, atau nilai di luar daftar)?
        // Jangan sampai list Level kosong total, karena dropdown Level dan Kelas
        // di Bank Soal, Jadwal, serta Alokasi ikut terkunci tidak bisa dipilih.
        $levelNumbers = $mapLevel[$jenjang] ?? range(1, 12);

        $levels = [];
        foreach ($levelNumbers as $levelNumber) {
            $levels[] = (object) ['level' => $levelNumber];
        }

        return $levels;
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

    // ==========================================
    // GURU/JABATAN FUNCTIONS (additional)
    // ==========================================
    public function getAllJabatanGuru(int $id_guru): array
    {
        $result = $this->db->table('jabatan_guru')
            ->where('id_guru', $id_guru)
            ->get()->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_tp][$row->id_smt] = $row->id_kelas;
        }
        return $ret;
    }

    // ==========================================
    // MAPEL/Kelompok FUNCTIONS (additional)
    // ==========================================
    public function getKodeKelompokMapel(): array
    {
        $result = $this->db->table('master_kelompok_mapel')
            ->orderBy('kode_kel_mapel')
            ->get()->getResult();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_kel_mapel] = $row;
        }
        return $ret;
    }

    public function getAllStatusMapel(?array $arrKelompok = null, ?string $arrMapel = null): array
    {
        $builder = $this->db->table('master_mapel');

        if ($arrKelompok !== null) {
            $builder->whereIn('kelompok', $arrKelompok);
        }
        if ($arrMapel !== null) {
            $builder->orWhereIn('id_mapel', explode(',', $arrMapel));
        }

        return $builder->orderBy('urutan_tampil')->get()->getResult();
    }

    // ==========================================
    // ALUMNI FUNCTIONS (Ported from CI3)
    // ==========================================
    public function getDistinctTahunLulus(): array
    {
        $result = $this->db->table('buku_induk')
            ->select('tahun_lulus')
            ->distinct()
            ->where('tahun_lulus IS NOT NULL', null, false)
            ->where('tahun_lulus != ""')
            ->get()
            ->getResult();

        $ret = [];
        foreach ($result as $row) {
            if ($row->tahun_lulus !== '') {
                $ret[$row->tahun_lulus] = $row->tahun_lulus;
            }
        }
        return $ret;
    }

    public function getDistinctKelasAkhir(): array
    {
        $result = $this->db->table('buku_induk')
            ->select('kelas_akhir')
            ->distinct()
            ->where('kelas_akhir IS NOT NULL', null, false)
            ->where('kelas_akhir != ""')
            ->get()
            ->getResult();
        $ret = [];
        foreach ($result as $row) {
            if ($row->kelas_akhir !== '') {
                $ret[$row->kelas_akhir] = $row->kelas_akhir;
            }
        }
        return $ret;
    }

    public function getAlumniByTahun(string $tahun, ?string $kelas = null): array
    {
        $builder = $this->db->table('buku_induk a')
            ->select('*')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->where('a.tahun_lulus', $tahun);
        if ($kelas !== null) {
            $builder->where('a.kelas_akhir', $kelas);
        }
        return $builder->get()->getResult();
    }

    public function getAlumniFilter(string $filter): array
    {
        return $this->db->table('buku_induk a')
            ->select('*')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->where('a.status', $filter)
            ->get()
            ->getResult();
    }

    public function getAlumniById(int $id)
    {
        $query = $this->db->table('master_siswa a')
            ->select('*')
            ->join('buku_induk b', 'a.id_siswa = b.id_siswa')
            ->where('a.id_siswa', $id)
            ->get();
        return $query ? $query->getRow() : null;
    }

    /**
     * Get guru with pagination, search, and dapodik filter
     */
    public function getGuruPage(int $tp, int $smt, int $offset, int $limit, ?string $search = null, ?string $dapodik = null): array
    {
        $builder = $this->db->table('master_guru a')
            ->select('a.*, b.id_jabatan, b.id_kelas, b.mapel_kelas, c.id_level, c.level, d.nama_kelas, e.tahun, f.nama_smt, g.active')
            ->join('jabatan_guru b', "a.id_guru=b.id_guru AND b.id_tp=$tp AND b.id_smt=$smt", 'left')
            ->join('level_guru c', 'b.id_jabatan=c.id_level', 'left')
            ->join('master_kelas d', "b.id_kelas=d.id_kelas AND d.id_tp=$tp AND d.id_smt=$smt", 'left')
            ->join('master_tp e', 'b.id_tp=e.id_tp', 'left')
            ->join('master_smt f', 'b.id_smt=f.id_smt', 'left')
            ->join('users g', 'a.username=g.username', 'left')
            ->orderBy('c.id_level', 'DESC')
            ->orderBy('a.nama_guru', 'ASC')
            ->limit($limit, $offset);

        if ($search) {
            $builder->groupStart()
                ->like('a.nama_guru', $search)
                ->orLike('a.nip', $search)
                ->orLike('a.kode_guru', $search)
                ->groupEnd();
        }

        if ($dapodik === 'synced') {
            $builder->groupStart()
                ->where('a.dapo_id IS NOT NULL', null, false)
                ->where('a.dapo_id !=', '')
                ->orWhere('a.ptk_id IS NOT NULL', null, false)
                ->where('a.ptk_id !=', '')
                ->groupEnd();
        } elseif ($dapodik === 'local') {
            $builder->groupStart()
                ->where('a.dapo_id IS NULL')
                ->orWhere('a.dapo_id', '')
                ->groupEnd();
        }

        return $builder->get()->getResult();
    }

    /**
     * Count total guru for pagination
     */
    public function getGuruTotalPage(int $tp, int $smt, ?string $search = null, ?string $dapodik = null): int
    {
        $builder = $this->db->table('master_guru a');

        if ($search) {
            $builder->groupStart()
                ->like('a.nama_guru', $search)
                ->orLike('a.nip', $search)
                ->orLike('a.kode_guru', $search)
                ->groupEnd();
        }

        if ($dapodik === 'synced') {
            $builder->groupStart()
                ->where('a.dapo_id IS NOT NULL', null, false)
                ->where('a.dapo_id !=', '')
                ->orWhere('a.ptk_id IS NOT NULL', null, false)
                ->where('a.ptk_id !=', '')
                ->groupEnd();
        } elseif ($dapodik === 'local') {
            $builder->groupStart()
                ->where('a.dapo_id IS NULL')
                ->orWhere('a.dapo_id', '')
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Get students by array of kelas IDs
     * Used for practical exam participant lists
     */
    /**
     * Get students by an array of class IDs.
     *
     * Dipakai untuk daftar peserta ujian praktik & penilaian manual, sehingga
     * hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dikembalikan.
     */
    public function getSiswaByKelasIds(array $kelasIds, int $tp, int $smt): array
    {
        if (empty($kelasIds)) {
            return [];
        }

        $builder = $this->db->table('kelas_siswa a')
            ->select('b.id_siswa, b.nama, b.nis, b.nisn, b.jenis_kelamin, b.agama, c.nama_kelas, c.id_kelas')
            ->join('master_siswa b', 'a.id_siswa = b.id_siswa')
            ->join('master_kelas c', 'a.id_kelas = c.id_kelas')
            ->where('a.id_tp', $tp)
            ->where('a.id_smt', $smt)
            ->whereIn('a.id_kelas', $kelasIds)
            ->orderBy('c.nama_kelas', 'ASC')
            ->orderBy('b.nama', 'ASC');

        $this->onlySiswaAktif($builder, 'a');

        $query = $builder->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Get classes by array of IDs
     */
    public function getKelasByIds(array $kelasIds): array
    {
        if (empty($kelasIds)) {
            return [];
        }

        $query = $this->db->table('master_kelas')
            ->whereIn('id_kelas', $kelasIds)
            ->get();
        return $query ? $query->getResult() : [];
    }

    /**
     * Update mapel by ID
     */
    public function updateMapel($id, array $data): bool
    {
        return $this->updateData('master_mapel', $data, 'id_mapel', $id);
    }

    /**
     * Parse mapel_kelas from serialized PHP array or JSON string.
     */
    public function parseMapelKelasData($raw): array
    {
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;

        $decoded = @json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $unserialized = @unserialize($raw);
        if (is_array($unserialized)) {
            return $unserialized;
        }

        return [];
    }

    /**
     * Get subject allocations & teacher assignments for a specific class (Kurikulum Merdeka)
     */
    public function getMapelPerKelasDetail(int $tp, int $smt, int $idKelas): array
    {
        // 1. Fetch all active mapel
        $allMapel = $this->db->table('master_mapel')
            ->where('status', '1')
            ->orderBy('urutan_tampil', 'ASC')
            ->orderBy('nama_mapel', 'ASC')
            ->get()->getResult();

        // 2. Fetch all jabatan_guru records for this TP & SMT
        $jabatans = $this->db->table('jabatan_guru jg')
            ->select('jg.*, g.nama_guru')
            ->join('master_guru g', 'g.id_guru = jg.id_guru', 'inner')
            ->where('jg.id_tp', $tp)
            ->where('jg.id_smt', $smt)
            ->get()->getResult();

        $mapelTeachers = []; // mapel_id => array of teachers
        foreach ($jabatans as $j) {
            $mkList = $this->parseMapelKelasData($j->mapel_kelas);
            foreach ($mkList as $mk) {
                $mId = $mk['id_mapel'] ?? null;
                if (!$mId) continue;

                $kelasMapel = $mk['kelas_mapel'] ?? [];
                foreach ($kelasMapel as $km) {
                    $kId = is_array($km) ? ($km['kelas'] ?? null) : $km;
                    if ((int)$kId === $idKelas) {
                        if (!isset($mapelTeachers[$mId])) {
                            $mapelTeachers[$mId] = [];
                        }
                        $mapelTeachers[$mId][] = [
                            'id_guru' => $j->id_guru,
                            'nama_guru' => $j->nama_guru
                        ];
                    }
                }
            }
        }

        $result = [
            'wajib' => [],
            'pilihan' => [],
            'mulok' => [],
            'total_wajib' => 0,
            'total_pilihan' => 0,
            'total_mulok' => 0,
            'total_assigned' => 0,
        ];

        foreach ($allMapel as $m) {
            $kel = strtoupper(trim($m->kelompok ?? 'A'));
            $teachers = $mapelTeachers[$m->id_mapel] ?? [];
            $isAssigned = !empty($teachers);

            // Determine Kurikulum Merdeka type
            if (in_array($kel, ['C', 'LM', 'PILIHAN']) || mb_stripos($m->nama_mapel, 'Pilihan') !== false || mb_stripos($m->nama_mapel, 'Lintas Minat') !== false) {
                $type = 'pilihan';
            } elseif ($kel === 'MULOK' || mb_stripos($m->nama_mapel, 'Muatan Lokal') !== false) {
                $type = 'mulok';
            } else {
                $type = 'wajib';
            }

            $item = [
                'id_mapel' => $m->id_mapel,
                'dapo_id' => $m->dapo_id,
                'kode' => $m->kode,
                'nama_mapel' => $m->nama_mapel,
                'kelompok' => $m->kelompok,
                'urutan_tampil' => $m->urutan_tampil,
                'type' => $type,
                'is_assigned' => $isAssigned,
                'teachers' => $teachers,
            ];

            if ($type === 'pilihan') {
                $result['pilihan'][] = $item;
                $result['total_pilihan']++;
            } elseif ($type === 'mulok') {
                $result['mulok'][] = $item;
                $result['total_mulok']++;
            } else {
                $result['wajib'][] = $item;
                $result['total_wajib']++;
            }

            if ($isAssigned) {
                $result['total_assigned']++;
            }
        }

        return $result;
    }

    /**
     * Toggle or update teacher assignment for a subject and class
     */
    public function toggleTeacherMapelKelas(int $tp, int $smt, int $idKelas, int $idMapel, int $idGuru, bool $isAssign): bool
    {
        $idJabatanGuru = $idGuru . $tp . $smt;
        $row = $this->db->table('jabatan_guru')
            ->where('id_jabatan_guru', $idJabatanGuru)
            ->get()->getRow();

        $mkList = $row ? $this->parseMapelKelasData($row->mapel_kelas) : [];
        $foundMapelKey = null;

        foreach ($mkList as $idx => $mk) {
            if ((int)($mk['id_mapel'] ?? 0) === $idMapel) {
                $foundMapelKey = $idx;
                break;
            }
        }

        if ($isAssign) {
            if ($foundMapelKey === null) {
                $mkList[] = [
                    'id_mapel' => (string)$idMapel,
                    'kelas_mapel' => [['kelas' => (string)$idKelas]]
                ];
            } else {
                $kelasArray = $mkList[$foundMapelKey]['kelas_mapel'] ?? [];
                $exists = false;
                foreach ($kelasArray as $k) {
                    $kId = is_array($k) ? ($k['kelas'] ?? null) : $k;
                    if ((int)$kId === $idKelas) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $kelasArray[] = ['kelas' => (string)$idKelas];
                    $mkList[$foundMapelKey]['kelas_mapel'] = $kelasArray;
                }
            }
        } else {
            if ($foundMapelKey !== null) {
                $kelasArray = $mkList[$foundMapelKey]['kelas_mapel'] ?? [];
                $newKelasArray = [];
                foreach ($kelasArray as $k) {
                    $kId = is_array($k) ? ($k['kelas'] ?? null) : $k;
                    if ((int)$kId !== $idKelas) {
                        $newKelasArray[] = is_array($k) ? $k : ['kelas' => (string)$kId];
                    }
                }
                $mkList[$foundMapelKey]['kelas_mapel'] = $newKelasArray;
            }
        }

        $jsonMapelKelas = json_encode(array_values($mkList));
        if ($row) {
            return $this->db->table('jabatan_guru')
                ->where('id_jabatan_guru', $idJabatanGuru)
                ->update(['mapel_kelas' => $jsonMapelKelas]);
        } else {
            return $this->db->table('jabatan_guru')->insert([
                'id_jabatan_guru' => $idJabatanGuru,
                'id_guru' => $idGuru,
                'id_tp' => $tp,
                'id_smt' => $smt,
                'mapel_kelas' => $jsonMapelKelas,
                'ekstra_kelas' => '[]',
                'id_jabatan' => '3'
            ]);
        }
    }
}
