<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_model extends CI_Model
{

    public function check_data_by_id($tabel, $key, $val)
    {
        $this->db->where($key, $val);
        $query = $this->db->get($tabel);

        if ($query->num_rows() > 0) {
            return TRUE; // Data ditemukan
        } else {
            return FALSE; // Data tidak ditemukan
        }
    }

    public function create($table, $data, $batch = false)
    {
        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', $table)) {
            if ($batch) {
                foreach ($data as &$row) {
                    if (is_array($row))
                        $row['sync_school_uuid'] = $school_uuid;
                }
            } else {
                $data['sync_school_uuid'] = $school_uuid;
            }
        }

        if ($batch == false) {
            $insert = $this->db->insert($table, $data);
        } else {
            $insert = $this->db->insert_batch($table, $data);
        }
        return $insert;
    }

    public function update($table, $data, $pk, $id = null, $batch = false)
    {
        if ($batch == false) {
            $insert = $this->db->update($table, $data, array($pk => $id));
        } else {
            $insert = $this->db->update_batch($table, $data, $pk);
        }
        return $insert;
    }

    public function delete($table, $data, $pk)
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        // Security: Ensure we only delete our own data
        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', $table)) {
            $this->db->where('sync_school_uuid', $school_uuid);
        }

        $this->db->where_in($pk, $data);
        $deleted = $this->db->delete($table);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        return $deleted;
    }

    public function delete_not($table, $data, $pk, $col, $not)
    {
        $this->db->where_in($pk, $data);
        $this->db->where($col . '!=' . $not);
        return $this->db->delete($table);
    }
    /**
     * Data Kelas
     */

    public function getDataKelas()
    {
        $this->datatables->select('id_kelas, nama_kelas, id_jurusan, nama_jurusan');
        $this->datatables->from('master_kelas');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_kelas')) {
            $this->datatables->where('master_kelas.sync_school_uuid', $school_uuid);
        }

        $this->datatables->join('master_jurusan', 'jurusan_id=id_jurusan');
        $this->datatables->add_column('bulk_select', '<div class="text-center"><input type="checkbox" class="check" name="checked[]" value="$1"/></div>', 'id_kelas, nama_kelas, id_jurusan, nama_jurusan');
        return $this->datatables->generate();
    }

    public function getKelasById($id)
    {
        $this->db->select('id_kelas, nama_kelas, level_id, jumlah_siswa');
        $this->db->from('master_kelas');
        $this->db->where('id_kelas', $id);
        $this->db->order_by('nama_kelas');
        return $this->db->get()->row();
    }

    /**
     * Data Jurusan
     */

    public function getDataJurusan()
    {
        $this->db->select('*');
        $this->db->from('master_jurusan');
        return $this->db->get()->result();
    }

    public function getDataJurusanMapel($arrIds)
    {
        $this->db->select('id_mapel, nama_mapel');
        $this->db->from('master_mapel');
        $this->db->where_in('id_mapel', $arrIds);
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_mapel] = $row->nama_mapel;
            }
        }
        return $ret;
    }

    public function getDataTableJurusan()
    {
        $this->datatables->select('*');
        $this->datatables->from('master_jurusan');
        //$this->datatables->add_column('mapels', '<div class="text-center"><input type="checkbox" id="check$1" class="check" name="checked[]" value="$1"/></div>', 'id_jurusan, nama_jurusan');
        $this->db->order_by('id_jurusan');
        return $this->datatables->generate();
    }

    public function getJurusanById($id)
    {
        $this->db->where_in('id_jurusan', $id);
        $this->db->order_by('nama_jurusan');
        return $this->db->get('master_jurusan')->result();
    }

    function updateJurusan()
    {
        $id = $this->input->post('id_jurusan');
        $name = $this->input->post('nama_jurusan', true);
        $kode = $this->input->post('kode_jurusan', true);

        $mapels = [];
        $check_mapel = $this->input->post('mapel', true);
        if ($check_mapel) {
            $row_mapels = count($this->input->post('mapel', true));
            for ($i = 0; $i <= $row_mapels; $i++) {
                array_push($mapels, $this->input->post('mapel[' . $i . ']', true));
            }
        }

        $this->db->set('nama_jurusan', $name);
        $this->db->set('kode_jurusan', $kode);
        $this->db->set('mapel_peminatan', implode(',', $mapels));
        $this->db->set('status', '1');
        $this->db->where('id_jurusan', $id);
        return $this->db->update('master_jurusan');
    }

    public function inputJurusan()
    {
        $data = ["nama_jurusan" => $this->input->post('nama_jurusan', true), "kode_jurusan" => $this->input->post('kode_jurusan', true),];
        return $this->db->insert('master_jurusan', $data);
    }
    //end

    /**
     * Data Siswa
     * @param $id_tp
     * @param $id_smt
     * @return int
     */
    public function getAllDataSiswa($id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.*, c.nama_kelas');
        $this->db->from('master_siswa a');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_siswa')) {
            $this->db->where('a.sync_school_uuid', $school_uuid);
        }
        $this->db->join('kelas_siswa b', 'b.id_siswa=a.id_siswa AND b.id_tp=' . $id_tp . ' AND b.id_smt=' . $id_smt . '', 'left');
        $this->db->join('master_kelas c', 'c.id_kelas=b.id_kelas', 'left');
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');
        $this->db->order_by('b.id_kelas');
        $this->db->order_by('a.nama');
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function getSiswaByKelas($id_tp, $id_smt, $id_kelas)
    {
        $this->db->select('b.*');
        $this->db->from('kelas_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_siswa')) {
            $this->db->where('b.sync_school_uuid', $school_uuid);
        }
        //$this->db->join('buku_induk i', 'i.id_siswa=a.id_siswa AND =i.status=1');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_siswa is NOT NULL', NULL, FALSE);
        $this->db->where('b.id_siswa is NOT NULL', NULL, FALSE);

        $this->db->where('a.id_kelas', $id_kelas);
        $this->db->order_by('b.nama', 'ASC');

        return $this->db->get()->result();
    }

    public function getDataSiswa($id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->datatables->select('a.id_siswa, a.foto, a.nama, a.nis, a.nisn, a.jenis_kelamin, f.level_id, f.nama_kelas, b.status');
        $this->datatables->from('master_siswa a');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_siswa')) {
            $this->datatables->where('a.sync_school_uuid', $school_uuid);
        }
        $this->datatables->join('buku_induk b', 'a.id_siswa=b.id_siswa', 'left');
        $this->datatables->join('users c', 'a.username=c.username');
        $this->datatables->join('kelas_siswa d', 'd.id_siswa = a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
        $this->datatables->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
        $this->db->order_by('ISNULL(f.level_id), f.level_id ASC');
        $this->db->order_by('f.nama_kelas', 'ASC');
        $this->db->order_by('b.status', 'ASC');
        return $this->datatables->generate();
    }

    public function getAllSiswa($id_tp, $id_smt, $offset, $limit, $search = null, $sort = null, $order = null)
    {
        $this->db->select('a.id_siswa, a.foto, a.nama, a.nis, a.nisn, a.jenis_kelamin, f.level_id, f.nama_kelas,' .
            ' us.active');
        $this->db->from('master_siswa a');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_siswa')) {
            $this->db->where('a.sync_school_uuid', $school_uuid);
        }

        $this->db->limit($limit, $offset);
        $this->db->order_by('a.nama', 'ASC');
        $this->db->join('kelas_siswa d', 'd.id_siswa = a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
        $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
        $this->db->join('users us', 'us.username=a.username');

        if ($search != null) {
            $this->db->group_start();
            $this->db->like('a.nama', $search);
            $this->db->or_like('a.nis', $search);
            $this->db->or_like('a.nisn', $search);
            //$this->db->or_like('f.nama_kelas', $search);
            $this->db->group_end();
        }
        //if ($sort != null) {
        //$this->db->order_by('ISNULL(f.level_id), f.level_id ASC');
        //$this->db->order_by('f.nama_kelas', 'ASC');
        //$this->db->order_by('b.status', 'ASC');
        //}
        return $this->db->get()->result();
    }

    public function getSiswaPage($id_tp, $id_smt, $offset, $limit, $filter, $kelas, $search = null, $sort = null, $order = null)
    {
        $this->db->select('a.id_siswa, a.foto, a.nama, a.nis, a.nisn, a.jenis_kelamin, d.id_kelas, a.agama, ' .
            'f.nama_kelas, us.active, u.status');
        $this->db->from('master_siswa a');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_siswa')) {
            $this->db->where('a.sync_school_uuid', $school_uuid);
        }

        $this->db->join('buku_induk u', 'u.id_siswa=a.id_siswa AND u.status="1"');
        $this->db->order_by('LENGTH(nama), nama');
        $this->db->limit($limit, $offset);

        if ($filter == "0") {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
            $this->db->join('users us', 'us.username=a.username');
        } elseif ($filter == "1") {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
            $this->db->join('users us', 'us.username=a.username AND us.active = "1"');
        } elseif ($filter == "3") {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = '
                . $id_tp . ' AND d.id_smt = ' . $id_smt . ' AND d.id_kelas = ' . $kelas . '');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas');
            $this->db->join('users us', 'us.username=a.username AND us.active = "1"');
        } else {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
            $this->db->join('users us', 'us.username=a.username AND us.active = "0"');
        }

        if ($search != null) {
            $this->db->group_start();
            $this->db->like('a.nama', $search);
            $this->db->or_like('a.nis', $search);
            $this->db->or_like('a.nisn', $search);
            $this->db->group_end();
        }
        return $this->db->get()->result();
    }

    public function getSiswaTotalPage($id_tp, $id_smt, $filter, $kelas = null, $search = null)
    {
        $this->db->select('a.id_siswa, us.active');
        $this->db->from('master_siswa a');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_siswa')) {
            $this->db->where('a.sync_school_uuid', $school_uuid);
        }

        $this->db->join('buku_induk u', 'u.id_siswa=a.id_siswa AND u.status="1"');

        if ($filter == "0") {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
            $this->db->join('users us', 'us.username=a.username');
        } elseif ($filter == "1") {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
            $this->db->join('users us', 'us.username=a.username AND us.active = "1"');
        } elseif ($filter == "3") {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = '
                . $id_tp . ' AND d.id_smt = ' . $id_smt . ' AND d.id_kelas = ' . $kelas . '');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas');
            $this->db->join('users us', 'us.username=a.username AND us.active = "1"');
        } else {
            $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = ' . $id_tp . ' AND d.id_smt = ' . $id_smt . '', 'left');
            $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
            $this->db->join('users us', 'us.username=a.username AND us.active = "0"');
        }

        if ($search != null) {
            $this->db->group_start();
            $this->db->like('a.nama', $search);
            $this->db->or_like('a.nis', $search);
            $this->db->or_like('a.nisn', $search);
            $this->db->group_end();
        }
        return $this->db->get()->num_rows();
    }

    public function getDataSiswaByKelas($id_tp, $id_smt, $id_kelas, $offset, $limit, $search = null, $sort = null, $order = null)
    {
        //$this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('b.id_siswa, b.nama, b.nis, b.nisn, b.jenis_kelamin, b.username, b.password, b.foto, b.agama,' .
            ' f.nama_kelas, us.active');
        $this->db->from('kelas_siswa a');
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        $this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa', 'right');
        if ($search != null) {
            $this->db->like('b.nama', $search);
            $this->db->or_like('b.nis', $search);
            $this->db->or_like('b.nisn', $search);
        }
        $this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas');
        $this->db->join('users us', 'us.username=b.username', 'left');
        //$this->db->join('buku_induk i', 'a.id_siswa=i.id_siswa', 'left');

        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_kelas', $id_kelas);
        return $this->db->get()->result();
    }

    public function getDataSiswaByKelasPage($id_tp, $id_smt, $id_kelas, $search = null)
    {
        $this->db->select('a.id_siswa');
        $this->db->from('kelas_siswa a');
        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);
        $this->db->where('a.id_kelas', $id_kelas);
        $this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa');
        if ($search != null) {
            $this->db->like('b.nama', $search);
            $this->db->or_like('b.nis', $search);
            $this->db->or_like('b.nisn', $search);
        }
        return $this->db->get()->num_rows();
    }

    public function getSiswaById($id)
    {
        $this->db->select('a.*, b.status');
        $this->db->from('master_siswa a');
        $this->db->join('buku_induk b', 'a.id_siswa=b.id_siswa', 'left');
        $this->db->where('a.id_siswa', $id);
        return $this->db->get()->row();
    }

    public function getSiswaByArrId($ids)
    {
        $this->db->select('a.id_siswa, f.nama_kelas');
        $this->db->from('master_siswa a');
        $this->db->join('kelas_siswa b', 'b.id_siswa=a.id_siswa', 'left');
        $this->db->join('master_kelas f', 'f.id_kelas=b.id_kelas');
        $this->db->where_in('a.id_siswa', $ids);
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_siswa] = $row;
            }
        }
        return $ret;
    }

    public function getSiswaByArrNisn($arr_nisn, $arr_nis, $arr_username)
    {
        $this->db->select('id_siswa, nama, nisn, nis, username');
        $this->db->from('master_siswa');
        $this->db->where_in('nisn', $arr_nisn);
        $this->db->or_where_in('nis', $arr_nis);
        $this->db->or_where_in('username', $arr_username);
        return $this->db->get()->result();
    }

    public function getSiswaByUsername($usernames)
    {
        $this->db->select('id_siswa, username');
        $this->db->where_in('username', $usernames);
        return $this->db->get('master_siswa')->result();
    }

    public function getSiswaKelasBaru($id_tp, $id_smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('b.id_siswa, b.nama, f.id_kelas, f.nama_kelas, f.kode_kelas');
        $this->db->from('kelas_siswa a');
        $this->db->join('master_siswa b', 'b.id_siswa=a.id_siswa');
        $this->db->join('master_kelas f', 'f.id_kelas=a.id_kelas');
        $this->db->where('a.id_tp', $id_tp);
        $this->db->where('a.id_smt', $id_smt);

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_siswa] = $row;
            }
        }
        return $ret;
    }

    public function getAgamaSiswa()
    {
        $this->db->select('agama');
        $this->db->distinct();
        $this->db->from('master_siswa a');
        $this->db->where('a.agama is NOT NULL', NULL, FALSE);
        $this->db->where('a.agama != "0"', NULL, FALSE);
        $this->db->not_like('a.agama', 'Pilih');
        $result = $this->db->get()->result();
        $ret['-'] = 'Bukan Mapel Agama';
        foreach ($result as $row) {
            $ret[$row->agama] = $row->agama;
        }
        return $ret;
    }

    public function getJurusan()
    {
        $this->db->select('id_jurusan, nama_jurusan');
        $this->db->from('master_kelas');
        $this->db->join('master_jurusan', 'jurusan_id=id_jurusan');
        $this->db->order_by('nama_jurusan', 'ASC');
        $this->db->group_by('id_jurusan');
        $query = $this->db->get();
        return $query->result();
    }

    public function getAllJurusan($id = null)
    {
        if ($id == null) {
            $this->db->order_by('nama_jurusan', 'ASC');
            return $this->db->get('jurusan')->result();
        } else {
            $this->db->select('jurusan_id');
            $this->db->from('jurusan_mapel');
            $this->db->where('mapel_id', $id);
            $jurusan = $this->db->get()->result();
            $id_jurusan = [];
            foreach ($jurusan as $j) {
                $id_jurusan[] = $j->jurusan_id;
            }
            if ($id_jurusan == []) {
                $id_jurusan = null;
            }

            $this->db->select('*');
            $this->db->from('master_jurusan');
            $this->db->where_not_in('id_jurusan', $id_jurusan);
            return $this->db->get()->result();
        }
    }

    public function getKelasByJurusan($id)
    {
        $query = $this->db->where(array('jurusan_id' => $id))->get('master_kelas');
        return $query->result();
    }

    /**
     * Data Guru
     * @param $tp
     * @param $smt
     * @return string
     */
    public function getDataGuru($tp, $smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->datatables->select('a.id_guru, a.nama_guru, a.nip, a.kode_guru, a.jenis_kelamin, a.foto, b.id_jabatan, b.id_kelas, b.mapel_kelas, c.id_level, c.level, d.nama_kelas, e.tahun, f.nama_smt');
        $this->datatables->from('master_guru a');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_guru')) {
            $this->datatables->where('a.sync_school_uuid', $school_uuid);
        }
        $this->datatables->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp=' . $tp . ' AND b.id_smt=' . $smt . '', 'left');
        $this->datatables->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
        $this->datatables->join('master_kelas d', 'b.id_kelas=d.id_kelas AND d.id_tp=' . $tp . ' AND d.id_smt=' . $smt . '', 'left');
        $this->datatables->join('master_tp e', 'b.id_tp=e.id_tp', 'left');
        $this->datatables->join('master_smt f', 'b.id_smt=f.id_smt', 'left');
        //$this->db->order_by('c.id_level', 'asc');
        return $this->datatables->generate();
    }

    public function getAllDataGuru($tp, $smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_guru, a.nama_guru, a.nip, a.kode_guru, a.jenis_kelamin, a.foto, b.id_jabatan, b.id_kelas, b.mapel_kelas, b.ekstra_kelas, c.id_level, c.level, d.nama_kelas, e.tahun, f.nama_smt, g.active');
        $this->db->from('master_guru a');

        $school_uuid = $this->session->userdata('school_uuid');
        if ($school_uuid && $this->db->field_exists('sync_school_uuid', 'master_guru')) {
            $this->db->where('a.sync_school_uuid', $school_uuid);
        }
        $this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp=' . $tp . ' AND b.id_smt=' . $smt . '', 'left');
        $this->db->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
        $this->db->join('master_kelas d', 'b.id_kelas=d.id_kelas AND d.id_tp=' . $tp . ' AND d.id_smt=' . $smt . '', 'left');
        $this->db->join('master_tp e', 'b.id_tp=e.id_tp', 'left');
        $this->db->join('master_smt f', 'b.id_smt=f.id_smt', 'left');
        $this->db->join('users g', 'a.username=g.username', 'left');
        $this->db->order_by('c.id_level', 'desc');
        $this->db->order_by('a.id_guru', 'asc');
        return $this->db->get()->result();
    }

    public function getGuruById($id, $id_tp = null, $id_smt = null)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $select = 'a.*, b.id_jabatan, b.id_kelas, b.mapel_kelas, b.ekstra_kelas, c.*,';
        $this->db->from('master_guru a');
        $this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru', 'left');
        $this->db->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
        if ($id_tp != null && $id_smt != null) {
            $select .= ' d.*';
            $this->db->join('master_kelas d', 'a.id_guru=d.guru_id AND d.id_tp=' . $id_tp . ' AND d.id_smt=' . $id_smt, 'left');
        }
        $this->db->select($select);
        $this->db->where('a.id_guru', $id);
        return $this->db->get()->row();
    }

    public function getGuruByArrId($arr_id)
    {
        $this->db->select('nama_guru, nip, ttd');
        $this->db->from('master_guru');
        if (count($arr_id) > 0)
            $this->db->where_in('id_guru', $arr_id);
        return $this->db->get()->result();
    }

    public function getUserIdGuruByUsername($username)
    {
        $this->db->select('id_guru, username');
        $this->db->from('master_guru');
        $this->db->where('username', $username);
        return $this->db->get()->result();
    }

    public function getDetailJabatanGuru($id_guru)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_guru, a.nama_guru, b.id_tp, b.id_smt, b.mapel_kelas, b.ekstra_kelas, c.id_level, c.level, d.id_kelas, d.nama_kelas');
        $this->db->from('master_guru a');
        $this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru', 'left');
        $this->db->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
        $this->db->join('master_kelas d', 'b.id_kelas=d.id_kelas', 'left');
        $this->db->where('a.id_guru', $id_guru);
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_tp][$row->id_smt] = $row;
        }
        return $ret;
    }

    public function getJabatanGuru($id_guru, $tp, $smt)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_guru, a.nama_guru, b.mapel_kelas, b.ekstra_kelas, c.id_level, c.level, d.id_kelas, d.nama_kelas');
        $this->db->from('master_guru a');
        $this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp=' . $tp . ' AND b.id_smt=' . $smt . '', 'left');
        $this->db->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
        $this->db->join('master_kelas d', 'b.id_kelas=d.id_kelas AND d.id_tp=' . $tp . ' AND d.id_smt=' . $smt . '', 'left');
        $this->db->where('a.id_guru', $id_guru);
        return $this->db->get()->row();
    }

    public function getGuruMapel($tp, $smt, $ids_guru = null)
    {
        $this->db->select('a.mapel_kelas, a.ekstra_kelas, a.id_jabatan, a.id_kelas, b.id_guru, b.nama_guru');
        $this->db->from('jabatan_guru a');
        $this->db->join('master_guru b', 'a.id_guru=b.id_guru');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        if ($ids_guru)
            $this->db->where_in('a.id_gur', $ids_guru);
        return $this->db->get()->result();
    }

    /**
     * Data Mapel
     */

    public function getKodeKelompokMapel()
    {
        $this->db->select('*');
        $this->db->from('master_kelompok_mapel');
        $this->db->order_by('kode_kel_mapel');
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kode_kel_mapel] = $row;
        }
        return $ret;
    }

    public function getDataKelompokMapel()
    {
        $this->db->select('*');
        $this->db->from('master_kelompok_mapel');
        $this->db->where('id_parent', '0');
        $this->db->order_by('kode_kel_mapel');
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_kel_mapel] = $row;
        }
        return $ret;
    }

    public function getKategoriKelompokMapel()
    {
        $this->db->select('kode_kel_mapel, kategori');
        $this->db->where('kategori', 'WAJIB')->or_where('kategori', 'AGAMA (Kemenag)');
        $this->db->from('master_kelompok_mapel');
        return $this->db->get()->result();
    }

    public function getDataSubKelompokMapel()
    {
        $this->db->select('*');
        $this->db->from('master_kelompok_mapel');
        $this->db->where('id_parent <> 0');
        $this->db->order_by('kode_kel_mapel');
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_kel_mapel] = $row;
        }
        return $ret;
    }

    public function getDataMapel()
    {
        $this->db->select('id_mapel, nama_mapel, kode');
        $this->db->from('master_mapel');
        $result = $this->db->get()->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->id_mapel] = $row;
        }
        return $ret;
    }

    public function getAllMapel($arrKelompok = null, $arrMapel = null)
    {
        if ($arrMapel != null) {
            $this->db->where_in('kelompok', $arrKelompok);
        }
        if ($arrMapel != null) {
            $this->db->or_where_in('id_mapel', explode(',', $arrMapel));
        }
        $this->db->where('status', '1');
        $this->db->order_by('urutan_tampil');
        return $this->db->get('master_mapel')->result();
    }

    public function getAllStatusMapel($arrKelompok = null, $arrMapel = null)
    {
        if ($arrMapel != null) {
            $this->db->where_in('kelompok', $arrKelompok);
        }
        if ($arrMapel != null) {
            $this->db->or_where_in('id_mapel', explode(',', $arrMapel));
        }
        $this->db->order_by('urutan_tampil');
        return $this->db->get('master_mapel')->result();
    }

    public function getAllMapelByKelompok($jenjang)
    {
        /*
        if ($jenjang == "1") {
            $this->db->where('jenjang=0 OR jenjang=1');
        } elseif ($jenjang == "2") {
            $this->db->where('jenjang=2 OR jenjang=1');
        }
        */
        $this->db->where('status', '1');
        $this->db->order_by('urutan');
        $this->db->order_by('urutan_tampil');
        $result = $this->db->get('master_mapel')->result();

        $ret = [];
        foreach ($result as $row) {
            $ret[$row->kelompok][] = $row;
        }
        return $ret;
    }

    public function getAllMapelNonAktif($jenjang)
    {
        /*
        if ($jenjang == "1") {
            $this->db->where('jenjang=0 OR jenjang=1');
        } elseif ($jenjang == "2") {
            $this->db->where('jenjang=2 OR jenjang=1');
        }
        */
        $this->db->where('status', '0');
        return $this->db->get('master_mapel')->result();
    }

    public function getMapelById($id, $single = false)
    {
        if ($single == false) {
            $this->db->where_in('id_mapel', $id);
            $this->db->order_by('nama_mapel');
            $query = $this->db->get('master_mapel')->result();
        } else {
            $query = $this->db->where(array('id_mapel' => $id))->get('master_mapel')->row();
        }
        return $query;
    }

    function updateMapel()
    {
        $id = $this->input->post('id_mapel');
        $name = $this->input->post('nama_mapel', true);
        $kode = $this->input->post('kode_mapel', true);
        $kelompok = $this->input->post('kelompok', true);
        $status = $this->input->post('status', true);
        $urut = $this->input->post('urutan_tampil', true);

        $this->db->set('nama_mapel', $name);
        $this->db->set('kode', $kode);
        if ($kelompok != null)
            $this->db->set('kelompok', $kelompok);
        $this->db->set('status', $status);
        $this->db->set('urutan_tampil', $urut);
        $this->db->where('id_mapel', $id);
        return $this->db->update('master_mapel');
    }

    public function getAllEkstra()
    {
        return $this->db->get('master_ekstra')->result();
    }

    public function getEkstraById($id, $single = false)
    {
        if ($single == false) {
            $this->db->where_in('id_ekstra', $id);
            $this->db->order_by('nama_ekstra');
            $query = $this->db->get('master_ekstra')->result();
        } else {
            $query = $this->db->where(array('id_ekstra' => $id))->get('master_ekstra')->row();
        }
        return $query;
    }

    function updateEkstra()
    {
        $id = $this->input->post('id_ekstra');
        $name = $this->input->post('nama_ekstra', true);
        $kode = $this->input->post('kode_ekstra', true);

        $this->db->set('nama_ekstra', $name);
        $this->db->set('kode_ekstra', $kode);
        $this->db->where('id_ekstra', $id);
        return $this->db->update('master_ekstra');
    }
    /**
     * Data Kelas Guru
     */

    public function getKelasGuru()
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->datatables->select('kelas_guru.id, guru.id_guru, guru.nip, guru.nama_guru, GROUP_CONCAT(master_kelas.nama_kelas) as kelas');
        $this->datatables->from('kelas_guru');
        $this->datatables->join('master_kelas', 'kelas_id=id_kelas');
        $this->datatables->join('master_guru', 'guru_id=id_guru');
        $this->datatables->group_by('guru.nama_guru');
        return $this->datatables->generate();
    }

    public function getKelasByGuru($id)
    {
        $this->db->select('kelas.id_kelas');
        $this->db->from('kelas_guru');
        $this->db->join('master_kelas', 'kelas_guru.kelas_id=kelas.id_kelas');
        $this->db->where('guru_id', $id);
        return $this->db->get()->result();
    }

    public function getAllJabatanGuru($id)
    {
        $result = $this->db->get_where('jabatan_guru', 'id_guru=' . $id)->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_tp][$row->id_smt] = $row->id_kelas;
            }
        }
        return $ret;
    }

    /**
     * Data Jurusan Mapel
     */

    public function getJurusanMapel()
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->datatables->select('jurusan_mapel.id, mapel.id_mapel, mapel.nama_mapel, jurusan.id_jurusan, GROUP_CONCAT(jurusan.nama_jurusan) as nama_jurusan');
        $this->datatables->from('jurusan_mapel');
        $this->datatables->join('master_mapel', 'mapel_id=id_mapel');
        $this->datatables->join('master_jurusan', 'jurusan_id=id_jurusan');
        $this->datatables->group_by('master_mapel.nama_mapel');
        return $this->datatables->generate();
    }

    public function getMapel($id = null)
    {
        $this->db->select('mapel_id');
        $this->db->from('jurusan_mapel');
        if ($id !== null) {
            $this->db->where_not_in('mapel_id', [$id]);
        }
        $mapel = $this->db->get()->result();
        $id_mapel = [];
        foreach ($mapel as $d) {
            $id_mapel[] = $d->mapel_id;
        }
        if ($id_mapel == []) {
            $id_mapel = null;
        }

        $this->db->select('id_mapel, nama_mapel');
        $this->db->from('master_mapel');
        $this->db->where_not_in('id_mapel', $id_mapel);
        return $this->db->get()->result();
    }

    public function getJurusanByIdMapel($id)
    {
        $this->db->select('jurusan.id_jurusan');
        $this->db->from('jurusan_mapel');
        $this->db->join('master_jurusan', 'jurusan_mapel.jurusan_id=jurusan.id_jurusan');
        $this->db->where('mapel_id', $id);
        return $this->db->get()->result();
    }

    public function getTahunActive()
    {
        $this->db->select('id_tp, tahun');
        $this->db->from('master_tp');
        $this->db->where('active', 1);
        return $this->db->get()->row();
    }

    public function getSemesterActive()
    {
        $this->db->select('id_smt, nama_smt, smt');
        $this->db->from('master_smt');
        $this->db->where('active', 1);
        return $this->db->get()->row();
    }

    public function getJmlHariEfektif($id)
    {
        $this->db->select('*');
        $this->db->from('master_hari_efektif');
        $this->db->where('id_hari_efektif', $id);
        return $this->db->get()->row();
    }

    public function getDistinctTahunLulus()
    {
        $this->db->select('tahun_lulus');
        $this->db->distinct();
        $result = $this->db->get('buku_induk')->result();
        $ret = [];
        foreach ($result as $row) {
            if ($row->tahun_lulus != '')
                $ret[$row->tahun_lulus] = $row->tahun_lulus;
        }
        return $ret;
    }

    public function getDistinctKelasAkhir()
    {
        $this->db->select('kelas_akhir');
        //$this->db->where('kelas_akhir');
        $this->db->distinct();
        $result = $this->db->get('buku_induk')->result();
        $ret = [];
        foreach ($result as $row) {
            if ($row->kelas_akhir != '')
                $ret[$row->kelas_akhir] = $row->kelas_akhir;
        }
        return $ret;
    }

    public function getAlumniByTahun($tahun, $kelas = null)
    {
        $this->db->select('*');
        $this->db->from('buku_induk a');
        $this->db->join('master_siswa b', 'a.id_siswa=b.id_siswa');
        $this->db->where('a.tahun_lulus', $tahun);
        if ($kelas != null)
            $this->db->where('a.kelas_akhir', $kelas);
        return $this->db->get()->result();
    }

    public function getAlumniFilter($filter)
    {
        $this->db->select('*');
        $this->db->from('buku_induk a');
        $this->db->join('master_siswa b', 'a.id_siswa=b.id_siswa');
        $this->db->where('a.status', $filter);
        return $this->db->get()->result();
    }

    public function getAlumniById($id)
    {
        $this->db->select('*');
        $this->db->from('master_siswa a');
        $this->db->join('buku_induk b', 'a.id_siswa=b.id_siswa');
        $this->db->where('a.id_siswa', $id);
        return $this->db->get()->row();
    }

    public function getAllWaliKelas()
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_tp, a.id_smt, a.id_guru, b.nama_guru, c.id_level, c.level, d.id_kelas, d.nama_kelas');
        $this->db->from('jabatan_guru a');
        $this->db->join('master_guru b', 'a.id_guru=b.id_guru', 'left');
        $this->db->join('level_guru c', 'a.id_jabatan=c.id_level', 'left');
        $this->db->join('master_kelas d', 'a.id_kelas=d.id_kelas', 'left');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                if ($row->id_level == "4") {
                    $ret[$row->id_tp][$row->id_smt][$row->id_kelas] = $row;
                }
            }
        }
        return $ret;
    }

    public function getAllGuru()
    {
        $this->db->select('id_guru');
        $this->db->from('jabatan_guru');
        $guru = $this->db->get()->result();
        $id_guru = [];
        foreach ($guru as $d) {
            $id_guru[] = $d->id_guru;
        }

        $this->db->select('id_guru, nip, nama_guru');
        $this->db->from('master_guru');
        $this->db->where_in('id_guru', $id_guru);
        return $this->db->get()->result();
    }

    public function getAllKelas($tp = null, $smt = null)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_kelas, a.id_tp, a.id_smt, a.nama_kelas, a.kode_kelas, a.level_id, b.nama_jurusan, b.kode_jurusan, c.nama_guru');
        $this->db->from('master_kelas a');
        if ($tp != null && $smt != null) {
            $this->db->where('a.id_tp', $tp)->where('a.id_smt', $smt);
        }
        $this->db->join('jabatan_guru f', 'f.id_kelas=a.id_kelas', 'left');
        $this->db->join('master_jurusan b', 'a.jurusan_id=b.id_jurusan', 'left');
        $this->db->join('master_guru c', 'f.id_guru=c.id_guru', 'left');
        $this->db->order_by('a.nama_kelas');
        $result = $this->db->get()->result();

        $ret = [];
        if ($tp != null && $smt != null) {
            if ($result) {
                foreach ($result as $key => $row) {
                    $ret[$row->id_kelas] = $row;
                }
            }
        } else {
            if ($result) {
                foreach ($result as $key => $row) {
                    $ret[$row->id_tp][$row->id_smt][$row->id_kelas] = $row;
                }
            }
        }
        return $ret;
    }

    public function getAllKelasSiswa()
    {
        $this->db->select('*');
        $this->db->from('kelas_siswa');
        $result = $this->db->get()->result();

        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_kelas][$row->id_siswa] = $row;
            }
        }
        return $ret;
    }

    public function getDataInduk()
    {
        $this->db->select('a.*, b.*,');
        $this->db->from('master_siswa a');
        $this->db->join('buku_induk b', 'a.id_siswa=b.id_siswa', 'left');
        $this->db->order_by('a.nama', 'ASC');
        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_siswa] = $row;
            }
        }
        return $ret;
    }
}
