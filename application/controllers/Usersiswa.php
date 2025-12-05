<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usersiswa extends MY_Controller {

	public function __construct(){
		parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }else if ( !$this->auth->is_admin() && !$this->auth->in_group('panitia')){
            show_error('Hanya Administrator dan Panitia yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
		$this->load->model('Siswa_user_model', 'siswa_user');
	}

	public function data(){
        $this->load->model('Users_model', 'users');
        $this->load->model('Dashboard_model', 'dashboard');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
		$this->output_json($this->users->getUserSiswa($tp->id_tp, $smt->id_smt), false);
	}

	public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
		$user = $this->auth->user();

		$data = [
			'user' => $user,
			'judul'	=> 'User Management',
			'subjudul'=> 'Data User Siswa',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        $role = $this->auth->is_admin() ? 1 : ($this->auth->in_group('panitia') ? 4 : 0);
        $this->render_view($role, 'users/siswa/data', $data);
	}

    public function list() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Users_model', 'users');

        $page = $this->input->post('page', true);
        $limit = $this->input->post('limit', true);
        $search = $this->input->post('search', true);

        $offset = ($page - 1) * $limit;
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $count_siswa = $this->users->getUserSiswaTotalPage($search);
        $lists = $this->users->getUserSiswaPage($tp->id_tp, $smt->id_smt, $offset, $limit, $search);
        foreach ($lists as $list) {
            $list->foto = $list->foto != null && $list->foto != "" && file_exists($list->foto) ? $list->foto : 'assets/img/siswa_sm.png';
        }

        $data = [
            'lists' => $lists,
            'total' => $count_siswa,
            'pages' => ceil($count_siswa / $limit),
            'search' => $search,
            'perpage' => $limit
        ];

        $this->output_json($data);
    }

	public function activate() {
        $this->load->model('Users_model', 'users');
        $id_siswa = $this->input->post('id_siswa', true);
        $is_active = $this->input->post('is_active', true);
        $nama = $this->input->post('nama', true);
        $msg = $is_active == 'aktifkan' ? ' diaktifkan' : ' dinonaktifkan';

        $duplikat = $this->users->getDuplicateUsernames('master_siswa');
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

        $active = $this->auth->aktifkanSiswa($is_active, $id_siswa);
        $data = [
            'status'	=> $active,
            'msg'	 => $active ? 'Siswa '.$nama.$msg : 'Siswa '.$nama.' gagal'.$msg
        ];

        $this->output_json($data);
	}

    public function aktifkanSemua() {
        $this->load->model('Users_model', 'users');
        $duplikat = $this->users->getDuplicateUsernames('master_siswa');
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
        $res = $this->auth->aktifkanAllSiswa($is_active);

        $msg = $is_active == 'aktifkan' ? ' siswa diaktifkan.' : ' siswa dinonaktifkan.';
        $data = [
            'status' => true,
            'jumlah' => $res,
            'msg'	 => count($res) . $msg
        ];
        $this->output_json($data);
    }

    private function nonaktifkan($user, $nama) {
        if (!$this->auth->logged_in() || (!$this->auth->is_admin() && !$this->auth->in_group('panitia'))) {
            $data = [
                'status'	=> false,
                'msg'		=> 'You must be an administrator or panitia to view this page.'
            ];
        } else {
            if ($this->auth->logged_in() && ($this->auth->is_admin() || $this->auth->in_group('panitia'))) {
                $deleted = $this->auth->delete_user($user->id);
                $data = [
                    'status'	=> $deleted,
                    'msg'	 => $deleted ? 'Siswa '.urldecode($nama).' dinonaktifkan.' : 'Siswa '.urldecode($nama).' gagal dinonaktifkan.'
                ];
            } else {
                $data = [
                    'status'	=> false,
                    'msg'	 => 'Anda bukan admin.'
                ];
            }
        }
        return $data;
    }

    public function reset_login() {
        $username = $this->input->post('username', true);
        $nama = $this->input->post('nama', true);
        if (!$this->auth->logged_in() || (!$this->auth->is_admin() && !$this->auth->in_group('panitia'))) {
            $data = [
                'status'	=> false,
                'msg'		=> 'You must be an administrator or panitia to view this page.'
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

	public function update() {
		$this->load->library('form_validation');

		$id_siswa = (int)$this->input->post('id_siswa', true);
		$username = trim($this->input->post('username', true));
		$oldPass = trim($this->input->post('old', true));
		$newPass = trim($this->input->post('new', true));

		$this->form_validation->set_rules('username', 'Username', 'required|numeric|trim|min_length[6]');
		$this->form_validation->set_rules('old', 'Password Lama', 'required|numeric|trim|min_length[6]');
		$this->form_validation->set_rules('new', 'Password Baru', 'required|numeric|trim|min_length[6]');

		if ($this->form_validation->run() === false) {
			$data = [
				'status' => false,
				'errors' => [
					'username' => form_error('username'),
					'old' => form_error('old'),
					'new' => form_error('new')
				]
			];
			$this->output_json($data);
			return;
		}

		if (!$id_siswa) {
			$this->output_json(['status' => false, 'msg' => 'ID siswa tidak valid']);
			return;
		}

		$current = $this->db->select('id_siswa, username, password')->from('master_siswa')->where('id_siswa', $id_siswa)->get()->row();
		if (!$current) {
			$this->output_json(['status' => false, 'msg' => 'Data siswa tidak ditemukan']);
			return;
		}

		if ($current->password !== $oldPass) {
			$this->output_json(['status' => false, 'msg' => 'Password lama tidak sesuai']);
			return;
		}

		if (!$this->siswa_user->is_username_available($username, $id_siswa)) {
			$this->output_json(['status' => false, 'msg' => 'Username sudah digunakan']);
			return;
		}

		$hashed = $this->hash_password($newPass);
		if ($hashed === false) {
			$this->output_json(['status' => false, 'msg' => 'Password baru tidak valid']);
			return;
		}

		$updated = $this->siswa_user->update_credentials($id_siswa, $username, $newPass, $hashed);
		if (!$updated) {
			$this->output_json(['status' => false, 'msg' => 'Gagal memperbarui data user']);
			return;
		}

		$this->output_json(['status' => true, 'msg' => 'User siswa berhasil diperbarui']);
	}

	public function change_password() {
        $this->load->library('form_validation');// Load Library Ignited-Datatables

		$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		if ($this->form_validation->run() == FALSE){
			$data = [
				'status' => false,
				'errors' => [
					'old' => form_error('old'),
					'new' => form_error('new'),
					'new_confirm' => form_error('new_confirm')
				]
			];
		}else{
			$identity = $this->session->userdata('identity');
			$change = $this->auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));
			if($change){
				$data['status'] = true;
			}
			else{
				$data = [
					'status' 	=> false,
					'msg'		=> $this->auth->errors()
				];
			}
		}
		$this->output_json($data);
	}

	public function delete($id) {
		$data['status'] = (bool)$this->auth->delete_user($id);
		$this->output_json($data);
	}

    private function hash_password($password) {
        if (empty($password) || strpos($password, "\0") !== FALSE || strlen($password) > 4096) {
            return FALSE;
        }
        return password_hash($password, PASSWORD_BCRYPT);
    }

}
