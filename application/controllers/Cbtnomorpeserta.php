<?php


class Cbtnomorpeserta extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
            show_error('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->library('upload');
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Cbt_model', 'cbt');
		$this->load->model('Dropdown_model', 'dropdown');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul' => 'Nomor Peserta',
			'subjudul' => 'Generate Nomor Peserta Ujian',
			'profile'		=> null,
			'setting'		=> $this->dashboard->getSetting()
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

		$data['ruang'] = $this->dropdown->getAllRuang();
		$data['sesi'] = $this->dropdown->getAllSesi();

        if ($this->auth->is_admin()) {
            $data['jadwal'] = $this->dropdown->getAllJadwal($tp->id_tp, $smt->id_smt);
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $this->render_view(1, 'cbt/nomorpeserta/data', $data);
        } else {
            $this->load->model('Kelas_model', 'kelas');
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;

            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrId = [];
            if ($mapel && count($mapel) > 0) {
                foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                    $arrId[] = $id_mapel->kelas;
                }
            }

            $data['kelas'] = count($arrId) > 0 ? $this->dropdown->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

            $this->render_view(2, 'cbt/nomorpeserta/data', $data);
        }
	}

	public function saveNomor() {
        $this->load->model('Log_model', 'logging');
		$input = json_decode($this->input->post('siswa', true));

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $ids_nomor = [];
		foreach ($input as $in) {
            $ids_nomor[] = $in->id . $tp->id_tp . $smt->id_smt;
		}

        $update = [];
        $insert = [];
        if (count($ids_nomor)>0) {
            $arrNomor = $this->cbt->cekNomorPeserta($ids_nomor);
            foreach ($input as $in) {
                $nomorAda = $arrNomor[$in->id . $tp->id_tp . $smt->id_smt] ?? null;
                if ($nomorAda) {
                    $update[] = [
                        'id_nomor' => $nomorAda->id_nomor,
                        'id_siswa' => $in->id,
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'nomor_peserta' => $in->nomor,
                    ];
                } else {
                    $insert[] = [
                        'id_nomor' => $in->id . $tp->id_tp . $smt->id_smt,
                        'id_siswa' => $in->id,
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'nomor_peserta' => $in->nomor,
                    ];
                }
            }
        }


        if (count($insert)>0) {
            $this->db->insert_batch('cbt_nomor_peserta', $insert);
        }
        if (count($update)>0) {
            $this->db->update_batch('cbt_nomor_peserta', $update, 'id_nomor');
        }
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Membuat nomor peserta ujian');
        $res['status'] = true;
        $res['input'] = $insert;
        $res['insert'] = $insert;
        $res['update'] = $update;
		$this->output_json($res);
	}

	public function resetNomor() {
		$input = json_decode($this->input->get('kelas', true));
		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$siswas = $this->cbt->getSiswaByKelasArray($tp->id_tp, $smt->id_smt, $input);
        $id_siswa_array = array_column($siswas, 'id_siswa');
        $arrNomor = $this->cbt->getAllNomorPeserta($tp->id_tp, $smt->id_smt, $id_siswa_array);

        $update = [];
		foreach ($siswas as $siswa) {
            if (isset($arrNomor[$siswa->id_siswa])) {
                $update[] = [
                    'id_nomor' => $arrNomor[$siswa->id_siswa]->id_nomor,
                    'nomor_peserta' => '',
                ];
            }
        }
        if (count($update)>0) {
            $this->db->update_batch('cbt_nomor_peserta', $update, 'id_nomor');
        }
		$res['status'] = true;
		$this->output_json($res);
	}

	public function getSiswaKelas() {
        $kelas = json_decode($this->input->post('kelas', true));

        $tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

        if ($this->auth->is_admin()) {
            $siswas = $this->cbt->getSiswaByKelasArray($tp->id_tp, $smt->id_smt, $kelas);
        } else {
            $this->load->model('Kelas_model', 'kelas');

            $user = $this->auth->user();
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrId = [];
            if ($mapel && count($mapel) > 0) {
                foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                    $arrId[] = $id_mapel->kelas;
                }
            }
            $siswas = $this->cbt->getSiswaByKelasArray($tp->id_tp, $smt->id_smt, $arrId);
        }

        $id_siswa_array = array_column($siswas, 'id_siswa');
        $arrNomor = [];
        if (count($id_siswa_array) > 0) {
            $arrNomor = $this->cbt->getAllNomorPeserta($tp->id_tp, $smt->id_smt, $id_siswa_array);
        }
        $data['kelas'] = $kelas;
		$data['siswa'] = $siswas;
		$data['nomor'] = $arrNomor;
		$this->output_json($data);
	}

}
