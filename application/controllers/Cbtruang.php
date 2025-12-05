<?php
/**
 * Created by IntelliJ IDEA.
 * User: multazam
 * Date: 07/07/20
 * Time: 14:26
 */

class Cbtruang extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
		} else if (!$this->auth->is_admin() && !$this->auth->in_group('panitia')) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Cbt_model', 'cbt');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul' => 'Ruang Ujian',
			'subjudul' => 'Data Ruang Ujian',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();

		$this->render_view(1, 'cbt/ruang/data', $data);
	}

    public function data() {
        $this->output_json($this->cbt->getRuang(), false);
    }

    public function add() {
		$insert = [
			'nama_ruang' => $this->input->post('nama_ruang', true),
			'kode_ruang' => $this->input->post('kode_ruang', true)
		];
		$this->master->create('cbt_ruang', $insert, false);

		$data['status'] = $insert;
		$this->output_json($data);
	}

	public function update() {
		$data = $this->cbt->updateRuang();
		$this->output->set_content_type('application/json')->set_output($data);
	}

	public function delete() {
		$chk = $this->input->post('checked', true);
		if (!$chk) {
			$this->output_json(['status' => false]);
		} else {
			if ($this->master->delete('cbt_ruang', $chk, 'id_ruang')) {
				$this->output_json(['status' => true, 'total' => count($chk)]);
			}
		}
	}

}

