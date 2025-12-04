<?php


class Cbttoken extends MY_Controller {

	public function __construct() {
		parent::__construct();
        $this->load->dbforge();
		if (!$this->auth->logged_in()) {
			redirect('auth');
		} else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Cbt_model', 'cbt');
		$this->load->model('Log_model', 'logging');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul' => 'Token Ujian',
			'subjudul' => 'Token',
            'setting'		=> $this->dashboard->getSetting()
		];

		$tp = $this->master->getTahunActive();
		$smt = $this->master->getSemesterActive();

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;
        $token = $this->cbt->getToken();

        $tkn['token'] = '';
        $tkn['auto'] = '0';
        $tkn['jarak'] = '1';
        $tkn['elapsed'] = '00:00:00';
        $data['token'] = $token != null ? $token : json_decode(json_encode($tkn));

		if($this->auth->is_admin()){
			$this->render_view(1, 'cbt/token/data', $data);
		}else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
			$this->render_view(2, 'members/guru/cbt/token/data', $data);
		}
	}

	public function generateToken() {
	    $post_token = json_decode($this->input->get('data'));
        $force = $this->input->get('force');
        $token = $this->cbt->getToken();
        $updated = date('Y-m-d H:i:s');
        if ($force== '1') {
            $new = $this->createNewToken();
            $post_token->token = $new;
            $post_token->updated = $updated;
            $this->cbt->saveToken($post_token);
        } else {
            $mulai = new DateTime($token->updated);
            $diff = $mulai->diff(new DateTime());
            $total_minutes = ($diff->days * 24 * 60);
            $total_minutes += ($diff->h * 60);
            $total_minutes += $diff->i;

            if ($total_minutes >= $post_token->jarak) {
                $new = $this->createNewToken();
                $post_token->token = $new;
                $post_token->updated = $updated;
                $this->cbt->saveToken($post_token);
            }
        }

        $token = $this->cbt->getToken();
        $token->now = $updated;
        $this->output_json($token);
	}

	public function loadToken() {
        $this->load->model('Cbt_model', 'cbt');
        $ada_ujian = $this->cbt->getDataJadwalByTgl(date('Y-m-d'));
        $token = $this->getValidToken(count($ada_ujian));
        $this->output_json($token);
	}
}
