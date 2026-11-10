<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * ElearningModel
 *
 * Residual dari modul E-Learning/KBM. Fitur "Jadwal Pelajaran" (pengelolaan
 * slot KBM) telah dihapus karena tidak pernah dipakai — tabel
 * kelas_jadwal_kbm / kelas_jadwal_mapel / kelas_jadwal_materi kosong.
 *
 * Yang tersisa hanya lookup mapel-kelas yang dipakai Guru\Tujuan.
 * Tabel kelas_jadwal_* beserta method terkait di KelasModel tetap
 * dipertahankan karena masih dipakai Siswa::absensi().
 */
class ElearningModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Get guru's assigned classes and subjects
     */
    public function getGuruMapelKelas($id_guru, $tp, $smt)
    {
        return $this->db->table('jabatan_guru')
            ->where('id_guru', $id_guru)
            ->where('id_tp', $tp)
            ->where('id_smt', $smt)
            ->get()->getRow();
    }
}
