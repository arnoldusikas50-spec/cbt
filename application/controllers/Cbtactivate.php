<?php


class Cbtactivate extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Cbt_model', 'cbt');

		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul' => 'Aktivasi Siswa',
			'subjudul' => 'Aktifkan/Nonaktifkan Siswa',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

        if($this->auth->is_admin()){

            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();

            $jadwals = $this->cbt->getJadwalKelas($tp->id_tp, $smt->id_smt);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $kls = $this->maybe_unserialize($jad->bank_kelas ?? '[]');
                foreach ($kls as $kl) {
                    $arrKls[] = $kl->kelas_id;
                }
            }
            $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);
            $this->render_view(1, 'cbt/aktivasi/data.php', $data);
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['sesi'] = $this->dropdown->getAllSesi();
            $data['pengawas'] = $this->cbt->getPengawasByGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);

            $jadwals = $this->cbt->getJadwalGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $kls = $this->maybe_unserialize($jad->bank_kelas ?? '[]');
                foreach ($kls as $kl) {
                    $arrKls[] = $kl->kelas_id;
                }
            }
            $data['ruangs'] = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);
            $this->render_view(2, 'cbt/aktivasi/data.php', $data);
        }
	}

    public function getSiswaRuang() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $ruang = $this->input->get('ruang', true);
        $sesi = $this->input->get('sesi', true);

        if (empty($ruang) || empty($sesi)) {
            $this->output_json(['status' => false, 'msg' => 'Ruang dan sesi harus diisi.']);
            return;
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswas = $this->cbt->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $sesi);
        $this->output_json(['status' => true, 'siswa' => $siswas]);
    }

    public function data() {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->getJenis(), false);
    }

    public function aktifkan_semua() {
        $posts = $this->input->post('ids', true);
        $active = $this->input->post('active', true);
        $this->load->model('Log_model', 'logging');

        $siswas = $this->auth->usersSiswa($posts);
        $for_update = [];
        foreach ($siswas as $siswa) {
            $for_update[] = [
                'id' => $siswa->id,
                'active' => $active == 'aktifkan' ? '1' : '0'
            ];
        }
        if (!$this->db->update_batch('users', $for_update, 'id')) {
            $data = [
                'status' => false,
                'msg'	 => 'Gagal mengupdate data siswa.'
            ];
            $this->output_json($data);
            return;
        }

        $this->logging->saveLog($this->LOG_TYPE->EDIT, ($active == 'aktifkan' ? 'Mengaktifkan' : 'Menonaktifkan') . 'semua siswa');
        $jum = count($siswas);
        $data = [
            'status' => true,
            'jumlah' => $jum,
            'data'   => $siswas,
            'msg'	 => $jum . ' siswa diaktifkan.'
        ];
        $this->output_json($data);
    }

    public function aktifkan_sesi() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $posts = $this->input->post('ids', true);
        $active = $this->input->post('active', true);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $arrRuang = [];
        $arrSesi = [];
        foreach ($posts as $post) {
            $split = explode('-', $post);
            $arrRuang[] = $split[0];
            $arrSesi[] = $split[1];
        }
        // get all ruang & sesi
        $this->db->select('a.siswa_id, a.ruang_id, a.sesi_id, b.nama, b.username, b.password, b.nis, d.id');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->join('master_siswa b', 'a.siswa_id=b.id_siswa');
        $this->db->join('kelas_siswa c', 'a.siswa_id=c.id_siswa');
        $this->db->join('users d', 'b.username=d.username');
        $this->db->where('a.tp_id', $tp->id_tp);
        $this->db->where('a.smt_id', $smt->id_smt);
        $this->db->where_in('a.ruang_id', $arrRuang);
        $this->db->where_in('a.sesi_id', $arrSesi);
        $siswas = $this->db->get()->result();

        $for_update = [];
        foreach ($siswas as $siswa) {
            $for_update[] = [
                'id' => $siswa->id,
                'active' => $active == 'aktifkan' ? '1' : '0'
            ];
        }
        $this->db->update_batch('users', $for_update, 'id');
        $jum = count($siswas);
        if ($jum > 0) {
            $this->logging->saveLog($this->LOG_TYPE->EDIT, ($active == 'aktifkan' ? 'Mengaktifkan' : 'Menonaktifkan') . ' siswa sesi');
        }
        $data = [
            'status' => true,
            'jumlah' => $jum,
            'data' => $siswas,
            'msg'	 => $jum . ' siswa dinonaktifkan.'
        ];
        $this->output_json($data);
    }

}
