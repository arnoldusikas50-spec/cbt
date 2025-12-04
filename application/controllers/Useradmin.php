<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Useradmin extends MY_Controller {

	public function __construct(){
		parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin()) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }

		$this->load->library(['datatables', 'form_validation']);// Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('','');
	}

    public function data() {
        $this->load->model('Users_model', 'users');
		$this->output_json($this->users->getDataadmin(), false);
    }

    public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Users_model', 'users');
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul'	=> 'Admin Management',
			'subjudul'=> 'Data Admin',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['admins'] = $this->users->getAllAdmin();
        $data['is_admin'] = $this->auth->is_admin();

        $this->render_view(1, 'users/admin/data', $data);
	}
	
	public function edit($id) {
        $this->load->model('Dashboard_model', 'dashboard');
		$user = $this->auth->user();
		$data = [
			'user' 		=> $user,
			'judul'		=> 'Administrator',
			'subjudul'	=> 'Edit Data Admin',
			'users' 	=> $this->auth->user($id),
			'groups'	=> $this->auth->groups()->result(),
            'level'		=> $this->auth->is_admin() ? '1' : '2',
			'setting'		=> $this->dashboard->getSetting()
		];
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['is_admin'] = $this->auth->is_admin();

		$this->render_view(1,'users/admin/edit', $data);
	}

	public function create() {
		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('first_name', 'First Name', 'required');
		//$this->form_validation->set_rules('last_name', 'Last Name', 'required');
		//$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('password','Password','trim|min_length[6]|max_length[20]|required');
		$this->form_validation->set_rules('confirm_password','Confirm password','trim|matches[password]|required');

		if($this->form_validation->run()==FALSE){
			$data['status'] = false;
			$data['errors'] = [
				'username' => form_error('username'),
				'first_name' => form_error('first_name'),
				//'last_name' => form_error('last_name'),
				//'email' => form_error('email'),
				'password' => form_error('password'),
				'confirm_password' => form_error('confirm_password')
			];
		}else{
			$username = $this->input->post('username', true);
			$password = $this->input->post('password', true);
			$email = $this->input->post('email', true);
            $first_name	= $this->input->post('first_name', true);
			$last_name	= $this->input->post('last_name', true);

			if ($this->auth->username_check($username)) {
				$data = [
					'status' => false,
					'msg'	 => 'Username tidak tersedia (sudah digunakan).'
				];
			} else {
                $user = [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
				$this->auth->register($user, '1');
				$data = [
					'status'	=> true,
					'msg'	 => 'User berhasil dibuat.'
				];
			}
		}
		$this->output_json($data);
	}

	public function edit_info() {
        $this->load->model('Master_model', 'master');
		$this->form_validation->set_rules('first_name', 'First Name', 'required');

		if($this->form_validation->run()==FALSE){
			$data['status'] = false;
			$data['errors'] = [
				'first_name' => form_error('first_name'),
			];
		} else {
			$id = $this->input->post('id', true);
			$input = [
				'first_name'	=> $this->input->post('first_name', true),
				'last_name'		=> $this->input->post('last_name', true),
				'email'			=> $this->input->post('email', true),
                'jabatan' 		=> $this->input->post('jabatan', true),
			];
			$update = $this->master->update('users', $input, 'id', $id);
			$data['status'] = (bool)$update;
		}
		$this->output_json($data);
	}

	public function edit_status()
	{
        $this->load->model('Master_model', 'master');
		$this->form_validation->set_rules('status', 'Status', 'required');
		
		if($this->form_validation->run()==FALSE){
			$data['status'] = false;
			$data['errors'] = [
				'status' => form_error('status'),
			];
		}else{
			$id = $this->input->post('id', true);
			$input = [
				'active' 		=> $this->input->post('status', true),
			];
			$update = $this->master->update('users', $input, 'id', $id);
			$data['status'] = (bool)$update;
		}
		$this->output_json($data);
	}
	
	public function edit_level()
	{
        $this->load->model('Master_model', 'master');

        $this->form_validation->set_rules('level', 'Level', 'required');
		
		if($this->form_validation->run()==FALSE){
			$data['status'] = false;
			$data['errors'] = [
				'level' => form_error('level'),
			];
		}else{
			$id = $this->input->post('id', true);
			$input = [
				'group_id' 		=> $this->input->post('level', true),
			];
			$update = $this->master->update('users_groups', $input, 'user_id', $id);
			$data['status'] = $update ? true : false;
		}
		$this->output_json($data);
	}

	public function change_password()
	{
		$this->form_validation->set_rules('old', 'Password lama', 'required');
		$this->form_validation->set_rules('new', 'Password baru', 'required|min_length[5]|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', 'Konfirmasi password baru', 'required');

        $username 	= $this->input->post('username', true);
        $id 	    = $this->input->post('id', true);
        if ($this->auth->username_check($username, $id)) {
            $data = [
                'status' => false,
                'errors' => ['username' => 'Username sudah digunakan']
            ];
        } elseif ($this->form_validation->run() == FALSE){
			$data = [
				'status' => false,
				'errors' => [
					'old' => form_error('old'),
					'new' => form_error('new'),
					'new_confirm' => form_error('new_confirm'),
				]
			];
		}else{
            $old = $this->input->post('old');
            $new = $this->input->post('new');
            $user = [
                'username' => $username,
                'password' => password_hash($new, PASSWORD_BCRYPT),
            ];
            $changed = $this->auth->changeUser($id, $user);
			if($changed){
				$data['status'] = true;
			}
			else{
				$data = [
					'status' 	=> false,
					'msg'		=> 'Gagal menyimpan data'
				];
			}
		}
		$this->output_json($data);
	}

	public function delete($id)
	{
		$data['status'] = (bool)$this->auth->delete_user($id);
		$this->output_json($data);
	}

	function uploadFile($id_user){
        $this->load->library('upload');
		if(isset($_FILES["foto"]["name"])){
			//$newName = $_FILES["logo"]["name"].".".$_FILES['logo']['type'];

			$config['upload_path'] = './uploads/profiles/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
			$config['overwrite'] = true;
			$config['file_name'] = 'foto_'.$id_user;

			$this->upload->initialize($config);
			if(!$this->upload->do_upload('foto')){
				$data['status'] = false;
				$data['src'] = $this->upload->display_errors();
			}else{
                $id_user = $this->input->post('id_user', true);
                $result = $this->upload->data();
                $src = 'uploads/profiles/'.$result['file_name'];
                $profile = $this->auth->user_profile($id_user)->row();
                if ($profile) {
                    $data = array('foto' => $src);
                    $this->db->where('id', $profile->id);
                    $this->db->update('users', $data);
                } else {
                    $data = array('id'=>$id_user, 'foto' => $src);
                    $this->db->insert('users', $data);
                }

                $data['src'] = base_url().$src;
				$data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
				$data['status'] = true;
			}
			$data['type'] = $_FILES['foto']['type'];
			$data['size'] = $_FILES['foto']['size'];
		} else {
			$data['src'] = '';
		}
		$this->output_json($data);
	}

	function deleteFile() {
		$src = $this->input->post('src');
		$file_name = str_replace(base_url(), '', $src ?? '');
		if (unlink($file_name)) {
			echo 'File Delete Successfully';
		}
	}

	function saveProfile() {
		$nama = $this->input->post('nama_lengkap');
		$jabatan = $this->input->post('jabatan');
		$foto = $this->input->post('foto');

		$user = $this->auth->user();

		$insert = [
			'id_user' => $user->id,
			'nama_lengkap' => $nama,
			'jabatan' => $jabatan,
			'foto' => str_replace(base_url(), '', $foto ?? ''),
		];
		$update = $this->db->update('users', $insert);

		$res['status'] = $update;
		$this->output_json($res);
	}
}
