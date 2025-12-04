<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Userguru extends MY_Controller {

	public function __construct(){
		parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['form_validation']); // Load Library Ignited-Datatables
		//$this->form_validation->set_error_delimiters('','');
	}

    public function data(){
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Users_model', 'users');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data = $this->users->getUserGuru($tp->id_tp, $smt->id_smt);
        foreach ($data as $list) {
            $list->foto = $list->foto != null && $list->foto != "" && file_exists($list->foto) ? base_url($list->foto) : base_url('assets/img/siswa_sm.png');
        }

        $this->output_json($data);
    }

    public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul'	=> 'User Management',
			'subjudul'=> 'Data User Guru',
            'profile'		=> null,
            'setting'		=> $this->dashboard->getSetting()
        ];

		if ($this->auth->is_admin()) {
			$data['tp'] = $this->dashboard->getTahun();
			$data['tp_active'] = $this->dashboard->getTahunActive();
			$data['smt'] = $this->dashboard->getSemester();
			$data['smt_active'] = $this->dashboard->getSemesterActive();

			$this->render_view(1, 'users/guru/data', $data);
		} else {
			$id = $this->users->getGuruByUsername($user->username);
			$this->edit($id->id_guru);
		}
	}

	public function activate() {
        $this->load->model('Users_model', 'users');
        $id_guru = $this->input->post('id_guru', true);
        $id_user = $this->input->post('id', true);
        $nama_guru = $this->input->post('nama', true);
        $is_active = $this->input->post('is_active', true);

        $this->load->model('Master_model', 'master');
        $guru = $this->users->getDataGuru($id_guru);

        $usernames = $this->users->usernameCheck($guru->username ?? '-', 'master_guru');
        if (count($usernames) > 1 && $is_active == 'aktifkan') {
            $data = [
                'status'	=> false,
                'msg'		=> 'ada '.count($usernames).' username menggunakan '.$guru->username.', silahkan ganti username',
                'users'     => $usernames
            ];
            $this->output_json($data);
            return;
        }

        if ($id_user) {
            $active = $this->auth->aktifkanGuru($is_active, $id_guru);
            $msg = $is_active == 'aktifkan' ? ' diaktifkan' : ' dinonaktifkan';
        } else {
            $nama = explode(' ', $guru->nama_guru ?? '');
            $first_name = $nama[0];
            $last_name = end($nama);

            $user = [
                'username' => trim($guru->username ?? ''),
                'password' => password_hash(trim($guru->password ?? ''), PASSWORD_BCRYPT),
                'first_name' => $first_name,
                'last_name' => $last_name
            ];
            $id_user = $this->auth->register($user, '2');

            $input = [
                'id_guru'		=> $id_guru,
                'id_user'		=> $id_user,
            ];

            $active = $this->master->update('master_guru', $input, 'id_guru', $id_guru);
            $msg = ' diaktifkan';
        }
        $data = [
            'status'	=> $active,
            'msg'	 => $active ? 'Guru '.$nama_guru.$msg : 'Guru '.$nama_guru.' gagal'.$msg
        ];

        $this->output_json($data);
	}

    public function aktifkanSemua() {
        $this->load->model('Users_model', 'users');
        $duplikat = $this->users->getDuplicateUsernames('master_guru');
        if (!empty($duplikat)) {
            $msg = '';
            foreach ($duplikat as $row) {
                $msg .= "<br>Username: {$row->username} duplikat {$row->total} kali<br>";
            }
            $data = [
                'status'	=> false,
                'msg'		=> 'Terdapat username duplikat, silahkan ganti username terlebih dahulu!'.$msg
            ];
            $this->output_json($data);
            return;
        }

        $is_active = $this->input->post('is_active', true);
        $res = $this->auth->aktifkanAllGuru($is_active);

        $msg = $is_active == 'aktifkan' ? ' guru diaktifkan.' : ' guru dinonaktifkan.';
        $data = [
            'status' => true,
            'jumlah' => $res,
            'msg'	 => count($res) . $msg
        ];
        $this->output_json($data);
    }

    public function update_iduser()
    {
        $this->db->select('a.id_guru, c.id');
        $this->db->join('users c', 'a.username=c.username', 'left');
        $result = $this->db->get('master_guru a')->result();
        $forupdate = [];
        foreach ($result as &$row) {
            $forupdate[] = [
                'id_guru' => $row->id_guru,
                'id_user' => $row->id
            ];
        }
        $updated = $this->db->update_batch('master_guru', $forupdate, 'id_guru');
        $this->output_json(['status' => $updated]);
    }

    public function edit($id) {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
		$guru = $this->users->getDetailGuru($id);
		$users = $this->users->getUsers($guru->username);
		$user = $this->auth->user();
		$data = [
			'user' 		=> $user,
			'judul'		=> 'User Management',
			'subjudul'	=> 'Edit Data User',
            'setting'		=> $this->dashboard->getSetting()
		];
		$data['users'] = $users;
		$data['guru'] = $guru;
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['is_admin'] = $this->auth->is_admin();

		if ($this->auth->is_admin()) {
			$data['groups'] = $this->auth->groups()->result();
			$data['kelass'] = $this->users->getKelas($tp->id_tp, $smt->id_smt);
			$data['mapels'] = $this->users->getMapel();
			$data['levels'] = $this->users->getLevelGuru();
			$this->render_view(1, 'users/guru/edit', $data);
		} else {
			$this->render_view(2, 'users/guru/edit', $data);
		}
	}

	public function editLogin() {
        $this->load->model('Master_model', 'master');

        $id_guru 	= $this->input->post('id_guru', true);
        $username 	= $this->input->post('username', true);
        $pass 	= $this->input->post('new', true);
        $guru_check = $this->master->getUserIdGuruByUsername($username);
        $guru_lain = array_filter($guru_check, function($g) use ($id_guru) {
            return $g->id_guru != $id_guru;
        });

		$this->form_validation->set_rules('old', 'Password lama', 'required');
		$this->form_validation->set_rules('new', 'Password lama', 'required|min_length[5]|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', 'Konfirmasi password baru', 'required');

        if (count($guru_lain) > 0) {
            $data = [
                //'guru' => $guru,
                'status' => false,
                'errors' => ['username' => 'Username sudah digunakan']
            ];

        } elseif ($this->form_validation->run() == FALSE){
			$data = [
				'status' => false,
				'errors' => [
                    //'username' => form_error('username'),
					'old' => form_error('old'),
					'new' => form_error('new'),
					'new_confirm' => form_error('new_confirm')
				]
			];
		} else {
            $guru = $this->auth->userGuru($id_guru);
            $user = [
                'username' => $username,
                'password' => password_hash($pass, PASSWORD_BCRYPT),
            ];
            $changed = $this->auth->changeUser($guru->id, $user);
            if ($changed) {
                $this->db->set('username', $username);
                $this->db->set('password', $pass);
                $this->db->where('id_guru', $id_guru);
                $status = $this->db->update('master_guru');
                $msg	 = !$status ? 'Gagal mengganti username/passsword.' : 'berhasil mengganti username/passsword.';
            } else {
                $status = false;
                $msg	 = 'Gagal mengganti username/passsword.';
            }

            $data['status'] = $status;
            $data['text'] = $msg;
        }
		$this->output_json($data);
	}

	function buangspasi($teks){
		$teks= trim($teks ?? '');
		$hasil=$teks;
		while( strpos($teks, ' ')){
			$remove[] = "'";
			$remove[] = ".";
			$remove[] = " ";
			$hasil= str_replace($remove, '', $teks ?? '');
		}
		return $hasil;
	}

    public function reset_login() {
        $username 	= $this->input->post('username', true);
        $nama = $this->input->post('nama', true);
        if (!$this->auth->logged_in() || !$this->auth->is_admin()) {
            $data = [
                'status'	=> false,
                'msg'		=> 'You must be an administrator to view this page.'
            ];
        } else {
            $this->db->where('login', $username);
            if ($this->db->delete('login_attempts')) {
                $data = [
                    'status'	=> true,
                    'msg'		=> 'User '.$nama.' berhasil direset'
                ];
            } else {
                $data = [
                    'status'	=> false,
                    'msg'		=> 'User '.$nama.' gagal direset'
                ];
            }
        }

        $this->output_json($data, true);
    }
}
