<?php


class Cbtjenis extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
		} else if (!$this->auth->is_admin()) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index()
	{
		$this->load->model('Dashboard_model', 'dashboard');
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul' => 'Jenis Ujian',
			'subjudul' => 'Data Jenis Ujian',
			'profile' => null,
			'setting' => $this->dashboard->getSetting()
		];

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();

		$this->load->model('Cbt_model', 'cbt');
		$data['jenis_ujian'] = $this->cbt->getAllJenisUjian();
		$data['active_jenis'] = $this->cbt->get_where('cbt_jenis', 'status', '1')->row();

		$this->render_view(1, 'cbt/jenis/data', $data);
	}

	public function data()
	{
		$this->load->model('Cbt_model', 'cbt');
		$this->output_json($this->cbt->getJenis(), false);
	}

	public function add()
	{
		$this->load->model('Master_model', 'master');
		$insert = [
			'nama_jenis' => $this->input->post('nama_jenis', true),
			'kode_jenis' => $this->input->post('kode_jenis', true),
			'status' => '1' // Default aktif
		];
		$this->master->create('cbt_jenis', $insert, false);

		$data['status'] = $insert;
		$this->output_json($data);
	}

	public function update()
	{
		$this->load->model('Cbt_model', 'cbt');
		$data = $this->cbt->updateJenis();
		$this->output->set_content_type('application/json')->set_output($data);
	}

	/**
	 * Toggle status aktif/non-aktif jenis ujian
	 * Hanya 1 jenis ujian yang bisa aktif dalam satu waktu
	 */
	public function toggleStatus()
	{
		$id = $this->input->post('id_jenis', true);
		$status = $this->input->post('status', true);
		$new_status = $status == '1' ? '0' : '1';

		// Jika mengaktifkan, nonaktifkan semua jenis lain terlebih dahulu
		if ($new_status == '1') {
			$this->db->update('cbt_jenis', ['status' => '0']);
		}

		// Update status jenis yang dipilih
		$this->db->where('id_jenis', $id);
		$result = $this->db->update('cbt_jenis', ['status' => $new_status]);

		// Get nama jenis untuk pesan
		$jenis = $this->db->get_where('cbt_jenis', ['id_jenis' => $id])->row();
		$nama = $jenis ? $jenis->nama_jenis : '';

		$message = $new_status == '1'
			? "Jenis \"$nama\" diaktifkan, yang lain dinonaktifkan"
			: "Jenis \"$nama\" dinonaktifkan";

		$this->output_json([
			'success' => $result,
			'new_status' => $new_status,
			'message' => $result ? $message : 'Gagal mengubah status'
		]);
	}

	/**
	 * Get only active jenis ujian (for dropdown, berita acara, etc.)
	 */
	public function getActiveJenis()
	{
		$this->load->model('Cbt_model', 'cbt');
		$jenis = $this->cbt->getActiveJenisUjian();
		$this->output_json($jenis);
	}

	public function delete()
	{
		$this->load->model('Master_model', 'master');
		$chk = $this->input->post('checked', true);
		if (!$chk) {
			$this->output_json(['status' => false]);
		} else {
			if ($this->master->delete('cbt_jenis', $chk, 'id_jenis')) {
				$this->output_json(['status' => true, 'total' => count($chk)]);
			}
		}
	}

	public function saveLog($type, $desc)
	{
		$this->load->model('Log_model', 'logging');
		$user = $this->auth->user();
		$this->logging->saveLog($type, $desc);
		//$this->output_json($res);
	}
}

