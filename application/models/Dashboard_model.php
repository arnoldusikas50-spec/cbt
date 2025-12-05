<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    public function check_data_by_id($tabel, $key, $val) {
        $this->db->where($key, $val);
        $query = $this->db->get($tabel);

        if ($query->num_rows() > 0) {
            return TRUE; // Data ditemukan
        } else {
            return FALSE; // Data tidak ditemukan
        }
    }

    public function getSetting() {
		return $this->db->get('setting')->row();
	}

    public function getRunningText() {
        return $this->db->get('running_text')->result();
    }

    public function total($table, $where=null) {
        if ($where !=null) $this->db->where($where);
        return $this->db->get($table)->num_rows();
    }

    public function hapus($table, $data, $pk) {
        $this->db->where_in($pk, $data);
        return $this->db->delete($table);
    }

    public function getProfileAdmin($id_user) {
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('id', $id_user);
        return $this->db->get()->row();
	}

	public function totalWaliKelas($id_tp, $id_smt) {
        $this->db->where('id_jabatan', '4');
        $this->db->where('id_tp', $id_tp);
        $this->db->where('id_smt', $id_smt);
        return $this->db->get('jabatan_guru')->num_rows();
	}

	public function totalSiswaKelas($id_kelas, $id_tp, $id_smt) {
		$this->db->select('a.id_siswa');
		$this->db->from('kelas_siswa a');
		$this->db->where('a.id_tp', $id_tp);
		$this->db->where('a.id_smt', $id_smt);
		$this->db->where('a.id_kelas', $id_kelas);
        return $this->db->get()->num_rows();
	}

	public function totalPengawas() {
		$this->db->select('*');
		$this->db->where('id_jadwal !=', 'a:0:{}');
        return $this->db->get('cbt_pengawas')->num_rows();
	}

    public function totalJadwal($tp, $smt) {
        $this->db->select('*');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        return $this->db->get('cbt_jadwal')->num_rows();
    }

    public function getDataTahun() {
        $this->datatables->select('id_tp, tahun, active');
        $this->datatables->from('master_tp');
        return $this->datatables->generate();
    }

	public function getTahun() {
        $this->db->select('id_tp, tahun, active');
        $this->db->order_by('tahun', 'ASC');
        return $this->db->get('master_tp')->result();
    }

    public function getTahunById($id) {
        return $this->db->get_where('master_tp', 'id_tp='.$id)->row();
    }

    public function getTahunByTahun($tahun) {
        return $this->db->get_where('master_tp', 'tahun='.'"' . $tahun .'"')->row();
    }

    public function getTpBefore($tahun)
    {
        $thns = explode('/', $tahun);
        $thn_before = ($thns[0] - 1) . '/' . $thns[0];
        return $this->getTahunByTahun($thn_before);
    }

    public function getTahunActive() {
        $this->db->select('id_tp, tahun');
        $this->db->from('master_tp');
        $this->db->where('active', 1);
        return $this->db->get()->row();
    }

    public function getSemester() {
        $this->db->order_by('smt', 'ASC');
        return $this->db->get('master_smt')->result();
    }

    public function getSemesterById($id) {
        return $this->db->get_where('master_smt', 'id_smt='.$id)->row();
    }

    public function getSemesterByNama($nama_smt) {
        return $this->db->get_where('master_smt', 'nama_smt='.'"'.$nama_smt.'"')->row();
    }

    public function getSemesterActive() {
        $this->db->select('id_smt, nama_smt, smt');
        $this->db->from('master_smt');
        $this->db->where('active', 1);
        return $this->db->get()->row();
    }

	public function getDataGuruByUserId($id_user, $id_tp, $id_smt) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->select('a.id_guru, a.nama_guru, a.nama_guru as nama_lengkap, a.nip, a.id_user, a.foto, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level, g.level as jabatan');
		$this->db->from('master_guru a');
		$this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp='.$id_tp.' AND b.id_smt='.$id_smt, 'left');
        $this->db->join('level_guru e', 'b.id_jabatan=e.id_level', 'left');
		$this->db->join('master_kelas f', 'b.id_kelas=f.id_kelas AND f.id_tp='.$id_tp.' AND f.id_smt='.$id_smt, 'left');
        $this->db->join('level_kelas g', 'f.level_id=g.id_level', 'left');
		$this->db->where('a.id_user', $id_user);
        return $this->db->get()->row();
	}

    public function getDataGuruByUsername($username, $id_tp, $id_smt) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->select('a.id_guru, a.nama_guru as nama_lengkap, a.nip, a.id_user, a.foto, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level, g.level as jabatan');
        $this->db->from('master_guru a');
        $this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp='.$id_tp.' AND b.id_smt='.$id_smt, 'left');
        $this->db->join('level_guru e', 'b.id_jabatan=e.id_level', 'left');
        $this->db->join('master_kelas f', 'b.id_kelas=f.id_kelas AND f.id_tp='.$id_tp.' AND f.id_smt='.$id_smt, 'left');
        $this->db->join('level_kelas g', 'f.level_id=g.id_level', 'left');
        $this->db->where('a.username', $username);
        return $this->db->get()->row();
    }

    public function getDataGuruById($id_guru, $id_tp, $id_smt) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_guru, a.nama_guru, a.nama_guru as nama_lengkap, a.nip, a.id_user, a.foto, b.id_jabatan, b.id_kelas as wali_kelas, f.level_id, g.level, g.level as jabatan');
        $this->db->from('master_guru a');
        $this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp='.$id_tp.' AND b.id_smt='.$id_smt, 'left');
        $this->db->join('level_guru e', 'b.id_jabatan=e.id_level', 'left');
        $this->db->join('master_kelas f', 'b.id_kelas=f.id_kelas AND f.id_tp='.$id_tp.' AND f.id_smt='.$id_smt, 'left');
        $this->db->join('level_kelas g', 'f.level_id=g.id_level', 'left');
        $this->db->where('a.id_guru', $id_guru);
        return $this->db->get()->row();
    }

    public function getDataPanitiaByUserId($id_user, $id_tp, $id_smt) {
        if (!$this->db->table_exists('master_panitia')) {
            return null;
        }
        
        $this->db->select('a.id_panitia, a.nama_panitia, a.nama_panitia as nama_lengkap, a.username, a.id_user, a.foto, a.ttd, a.jabatan, a.no_hp, a.email');
        $this->db->from('master_panitia a');
        $this->db->where('a.id_user', $id_user);
        return $this->db->get()->row();
    }

	public function getDetailGuruByUserId($id_user, $id_tp, $id_smt) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->select('a.*,b.id_jabatan, b.id_kelas, b.mapel_kelas, b.ekstra_kelas, b.id_tp, b.id_smt,'.
            'e.*, f.id_kelas, f.nama_kelas, f.kode_kelas, f.jurusan_id, f.level_id, f.guru_id, f.siswa_id, f.jumlah_siswa, f.set_siswa');
		$this->db->from('master_guru a');
		$this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp='.$id_tp.' AND b.id_smt='.$id_smt, 'left');
		$this->db->join('level_guru e', 'b.id_jabatan=e.id_level', 'left');
        $this->db->join('master_kelas f', 'b.id_kelas=f.id_kelas AND f.id_tp='.$id_tp.' AND f.id_smt='.$id_smt, 'left');
		$this->db->where('a.id_user', $id_user);
        return $this->db->get()->row();
	}

	public function get_where($table, $pk, $id, $join = null, $order = null) {
        $this->db->select('*');
        $this->db->from($table);
        $this->db->where($pk, $id);

        if($join !== null){
            foreach($join as $table => $field){
                $this->db->join($table, $field);
            }
        }

        if($order !== null){
            foreach($order as $field => $sort){
                $this->db->order_by($field, $sort);
            }
        }

        return $this->db->get();
    }

    public function create($table, $data) {
        return $this->db->insert($table, $data);
    }

    public function update($table, $data, $pk, $id = null, $batch = false)
    {
        if($batch == false){
            $insert = $this->db->update($table, $data, array($pk => $id));
        }else{
            $insert = $this->db->update_batch($table, $data, $pk);
        }
        return $insert;
    }

	public function getDataSiswa($username, $id_tp, $id_smt) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->select('a.*, c.*,d.*');
		$this->db->from('master_siswa a');
		$this->db->join('kelas_siswa b', 'a.id_siswa=b.id_siswa AND b.id_tp='.$id_tp.' AND b.id_smt='.$id_smt, 'left');
        $this->db->join('master_kelas c', 'b.id_kelas=c.id_kelas AND c.id_tp='.$id_tp.' AND c.id_smt='.$id_smt, 'left');
		$this->db->join('cbt_sesi_siswa d', 'a.id_siswa=d.siswa_id AND b.id_tp = d.tp_id AND b.id_smt = d.smt_id', 'left');
		$this->db->where('username', $username);
        return $this->db->get()->row();
	}

    public function checkUsernameSiswa($username) {
        $this->db->select('id_siswa,username');
        $this->db->from('master_siswa');
        $this->db->where('username', $username);
        return $this->db->get()->result();
    }

	public function loadPengumuman($id_for) {
		$this->db->select('a.*, b.nama_guru, b.foto');
		$this->db->from('pengumuman a');
		$this->db->join('master_guru b', 'a.dari=b.id_guru', 'left');
		$this->db->where('kepada', $id_for);
        return $this->db->get()->result();
	}

	public function loadJadwalHariIni($id_tp, $id_smt, $id_kelas = null, $id_hari=null) {
		$this->db->select('a.*, b.id_mapel as mapel_id, b.nama_mapel, b.kode');
		$this->db->from('kelas_jadwal_mapel a');
		$this->db->join('master_mapel b', 'b.id_mapel=a.id_mapel', 'left');

		$this->db->where('a.id_tp', $id_tp);
		$this->db->where('a.id_smt', $id_smt);
		if ($id_kelas != null) {
            $this->db->where('a.id_kelas', $id_kelas);
        }
		if ($id_hari!=null) {
			$this->db->where('a.id_hari', $id_hari);
		}

        return $this->db->get()->result();
	}

	public function getJadwalKbm($id_tp, $id_smt, $id_kelas=null) {
		$this->db->select('*');
		$this->db->from('kelas_jadwal_kbm');

		$this->db->where('id_tp', $id_tp);
		$this->db->where('id_smt', $id_smt);
        if ($id_kelas != null) {
            $this->db->where('id_kelas', $id_kelas);
            $query = $this->db->get()->row();
        } else {
            $query = $this->db->get()->result();
        }
		return $query;
	}

}
