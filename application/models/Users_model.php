<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model {

    public function getDuplicateUsernames($table)
    {
        $this->db->select('username, COUNT(*) as total');
        $this->db->from($table);
        $this->db->group_by('username');
        $this->db->having('total >', 1);
        $query = $this->db->get();

        return $query->result();
        // hasil: array username yang duplikat beserta jumlahnya
    }

    public function usernameCheck($username, $table)
    {
        switch ($table) {
            case 'master_siswa':
                $select_name = 'nama';
                break;
            case 'master_panitia':
                $select_name = 'nama_panitia';
                break;
            default:
                $select_name = 'nama_guru';
        }

        $this->db->select('username, '.$select_name);
        $check = $this->db->get_where($table, ['username' => $username])->result();
        return $check;
    }

    public function getDatausers($id = null) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->datatables->select('users.id, username, first_name, last_name, email, FROM_UNIXTIME(created_on) as created_on, last_login, active, groups.name as level');
        $this->datatables->from('users_groups');
        $this->datatables->join('users', 'users_groups.user_id=users.id');
        $this->datatables->join('groups', 'users_groups.group_id=groups.id');
        if($id !== null){
            $this->datatables->where('users.id !=', $id);
        }
        return $this->datatables->generate();
    }

	public function getLevelGuru() {
        return $this->db->get('level_guru')->result();
	}

    public function getAllAdmin() {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('users.id, username, first_name, last_name, email, FROM_UNIXTIME(created_on) as created_on, last_login, active, groups.name as level');
        $this->db->from('users_groups');
        $this->db->join('users', 'users_groups.user_id=users.id');
        $this->db->join('groups', 'users_groups.group_id=groups.id');
        $this->db->where('group_id =', 1);
        return $this->db->get()->result();
    }

	public function getDataadmin() {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->datatables->select('users.id, username, first_name, last_name, email, FROM_UNIXTIME(created_on) as created_on, last_login, active, groups.name as level');
		$this->datatables->from('users_groups');
		$this->datatables->join('users', 'users_groups.user_id=users.id');
		$this->datatables->join('groups', 'users_groups.group_id=groups.id');
		$this->datatables->where('group_id =', 1);
		return $this->datatables->generate();
	}

	public function getUserGuru($tp, $smt) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->select('a.id_guru, a.nama_guru, a.username, a.password, a.foto, c.level, e.id, e.active, '.
            '(SELECT COUNT(login) FROM login_attempts WHERE login_attempts.login = a.username) AS reset');
		$this->db->from('master_guru a');
		$this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru AND b.id_tp='.$tp.' AND b.id_smt='.$smt.'', 'left');
		$this->db->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
		$this->db->join('users e', 'a.id_user=e.id', 'left');
        return $this->db->get()->result();
	}

	public function getDataGuru($id) {
		$this->db->select('*');
		$this->db->from('master_guru');
		$this->db->where('id_guru', $id);
        return $this->db->get()->row();
	}

    public function getDataGuruByUsername($username) {
        $this->db->select('id_guru, nama_guru, username, password');
        $this->db->from('master_guru');
        $this->db->where('username', $username);
        return $this->db->get()->row();
    }

	public function getDetailGuru($id) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->db->select('a.id_guru, a.nama_guru, a.username, a.password, a.email, c.level, e.id, e.active');
		$this->db->from('master_guru a');
		$this->db->join('jabatan_guru b', 'a.id_guru=b.id_guru', 'left');
		$this->db->join('level_guru c', 'b.id_jabatan=c.id_level', 'left');
		$this->db->join('users e', 'a.username=e.username', 'left');
		$this->db->where('a.id_guru', $id);
        return $this->db->get()->row();
	}

	public function getGuruByUsername($username) {
		$this->db->where('username', $username);
        return $this->db->get('master_guru')->row();
	}

	public function getUsers($username) {
		$this->db->where('username', $username);
        return $this->db->get('users')->row();
	}

    public function getUserPanitia()
    {
        if (!$this->db->table_exists('master_panitia')) {
            return [];
        }
        
        // Check if table has any records
        $count = $this->db->count_all('master_panitia');
        if ($count == 0) {
            return [];
        }
        
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('a.id_panitia, a.nama_panitia, a.username, a.password, a.jabatan, a.foto, a.status, u.id, u.active,'.
            ' (SELECT COUNT(login) FROM login_attempts WHERE login_attempts.login = a.username) AS reset');
        $this->db->from('master_panitia a');
        $this->db->join('users u', 'a.id_user = u.id', 'left');
        $this->db->order_by('a.nama_panitia', 'ASC');
        
        $result = $this->db->get()->result();
        
        // Add foto path processing
        foreach ($result as $row) {
            if ($row->foto && file_exists($row->foto)) {
                $row->foto = base_url($row->foto);
            } else {
                $row->foto = base_url('assets/img/profile-default.png');
            }
        }
        
        return $result;
    }

    public function getDataPanitia($id)
    {
        if (!$this->db->table_exists('master_panitia')) {
            return null;
        }
        return $this->db->get_where('master_panitia', ['id_panitia' => $id])->row();
    }

    public function getPanitiaByUsername($username)
    {
        if (!$this->db->table_exists('master_panitia')) {
            return null;
        }
        return $this->db->get_where('master_panitia', ['username' => $username])->row();
    }

    public function getGroupSiswa() {
	    $this->db->select('*');
	    $this->db->from('users_groups a');
	    $this->db->join('users b', 'a.user_id=b.id', 'left');
        $this->db->where('group_id', 3);
        return $this->db->get()->result();
    }

	public function getKelas($tp, $smt) {
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        return $this->db->get('master_kelas')->result();
	}

	public function getMapel() {
        return $this->db->get('master_mapel')->result();
	}

    public function getUserSiswaPage($id_tp, $id_smt, $offset, $limit, $search=null, $sort=null, $order=null) {
        $this->db->select('a.id_siswa, a.nis, a.foto, a.nama, a.username, a.password, d.id_kelas, '.
            'f.nama_kelas, active, '.
            '(SELECT COUNT(login) FROM login_attempts WHERE login_attempts.login = a.username) AS reset');
        $this->db->from('master_siswa a');
        $this->db->limit($limit, $offset);
        $this->db->join('buku_induk bu', 'bu.id_siswa=a.id_siswa AND bu.status="1"');
        $this->db->join('kelas_siswa d', 'd.id_siswa=a.id_siswa AND d.id_tp = '.$id_tp.' AND d.id_smt = '.$id_smt.'', 'left');
        $this->db->join('master_kelas f', 'f.id_kelas=d.id_kelas', 'left');
        $this->db->join('users u', 'u.username=a.username', 'left');
        $this->db->order_by('ISNULL(f.level_id), f.level_id ASC');
        $this->db->order_by('f.nama_kelas', 'ASC');
        $this->db->order_by('a.nama', 'ASC');

        if ($search != null) {
            $this->db->like('a.nama', $search);
            $this->db->or_like('a.nis', $search);
            $this->db->or_like('a.nisn', $search);
        }
        return $this->db->get()->result();
    }

    public function getUserSiswaTotalPage($search=null) {
        $this->db->select('a.id_siswa');
        $this->db->from('master_siswa a');
        $this->db->join('buku_induk bu', 'bu.id_siswa=a.id_siswa AND bu.status="1"');
        if ($search != null) {
            $this->db->like('a.nama', $search);
            $this->db->or_like('a.nis', $search);
            $this->db->or_like('a.nisn', $search);
        }
        return $this->db->get()->num_rows();
    }

	public function getUserSiswa($tp, $smt) {
        $this->db->query('SET SQL_BIG_SELECTS=1');
		$this->datatables->select('a.id_siswa, a.nis,.a.nama, a.username, a.password, c.nama_kelas, d.id, d.active');
		$this->datatables->from('master_siswa a');
		$this->datatables->join('kelas_siswa b', 'b.id_siswa=a.id_siswa AND b.id_tp='.$tp.' AND b.id_smt='.$smt.'', 'left');
		$this->datatables->join('master_kelas c', 'c.id_kelas=b.id_kelas', 'left');
		$this->datatables->join('users d', 'd.username=a.username', 'left');
		return $this->datatables->generate();
	}

	public function getDataSiswa($id) {
		$this->db->select('nis, nisn, nama, username, password');
		$this->db->from('master_siswa');
		$this->db->where('id_siswa', $id);
        return $this->db->get()->row();
	}
}
