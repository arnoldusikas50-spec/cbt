<?php


class Kelascatatan extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
		} else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Dropdown_model', 'dropdown');
		$this->load->model('Kelas_model', 'kelas');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
		$user = $this->auth->user();
		$setting = $this->dashboard->getSetting();
		$data = [
			'user' => $user,
			'judul' => 'Catatan Guru',
			'subjudul' => 'Catatan Selama Pembelajaran',
            'setting'		=> $setting
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $id_kelas = $this->input->get('kelas', true);
        $id_mapel = $this->input->get('mapel', true);
        $data['kelas_selected'] = $id_kelas;
        $data['mapel_selected'] = $id_mapel;
        if ($id_kelas!=null) {
            $cat_kelas = $this->kelas->getCatatanMapelKelas($id_kelas, $id_mapel, $tp->id_tp, $smt->id_smt);
            foreach ($cat_kelas as $ck) {
                $ck->reading = $this->maybe_unserialize($ck->reading);
            }
            $data['cat_kelas'] = $cat_kelas;
            $data['cat_siswa'] = $this->kelas->getCatatanMapelSiswa($tp->id_tp, $smt->id_smt, $id_kelas, $id_mapel);
        }

        if($this->auth->is_admin()){

            $data['mapel'] = $this->dropdown->getAllMapel();
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

            $this->render_view(1, 'members/guru/kelas/catatan/data', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrId = [];
            if ($mapel != null) {
                foreach ($mapel as $mpl) {
                    foreach ($mpl->kelas_mapel as $id_mapel) {
                        array_push($arrId, $id_mapel->kelas);
                    }
                }
            }
            $kelasses = [];
            if (count($arrId)>0) $kelasses = $this->dropdown->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId);

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;

                    foreach ($m->kelas_mapel as $kls_mapel) {
                        foreach ($kelasses as $key => $kelass) {
                            if ($kls_mapel->kelas == $key) {
                                $arrKelas[$m->id_mapel][$key] = $kelass;
                            }
                        }
                    }
                }
            }

            $data['mapel'] = $arrMapel;
            $data['kelas'] = $arrKelas;

            $this->render_view(2, 'members/guru/kelas/catatan/data', $data);
        }
	}

	public function siswa() {
        $id_siswa = $this->input->get('id');
        $id_mapel = $this->input->get('mapel');
        $id_kelas = $this->input->get('kelas');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Catatan Siswa',
            'subjudul' => 'Catatan Siswa',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['siswa'] = $this->master->getSiswaById($id_siswa);
        $data['catatan_siswa'] = $this->kelas->getAllCatatanMapelSiswa($id_siswa, $id_mapel, $tp->id_tp, $smt->id_smt);
        $data['mapel'] = $id_mapel;
        $data['kelas'] = $id_kelas;

        if($this->auth->is_admin()){
            $this->render_view(1, 'members/guru/kelas/catatan/persiswa', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->render_view(2, 'members/guru/kelas/catatan/persiswa', $data);
        }
    }

    /*
	public function loadCatatan() {
        $id_kelas = $this->input->post('kelas', true);
        $id_mapel = $this->input->post('mapel', true);
        //$tipe = $this->input->post('tipe', true);

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $res['kelas'] = $this->kelas->getCatatanMapelKelas($id_kelas, $id_mapel, $tp->id_tp, $smt->id_smt);
        $res['siswa'] = $this->kelas->getCatatanMapelSiswa($tp->id_tp, $smt->id_smt, $id_kelas, $id_mapel);

        $this->output_json($res);
    }
    */

    public function saveCatatanKelas(){
        $this->load->model('Log_model', 'logging');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        $id_kelas = $this->input->post('id_kelas');
        $id_mapel = $this->input->post('id_mapel', true);
        $text = $this->input->post('text', true);
        $level = $this->input->post('level', true);
        $tgl = date('Y-m-d');

        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '1',
            'id_mapel' => $id_mapel,
            'id_kelas' => $id_kelas,
            'id_guru' => $guru->id_guru,
            'level' => $level,
            //'tgl' => $tgl,
            'text' => $text,
            'reading' => json_encode([])
        ];
        $insert = $this->master->create('kelas_catatan_mapel', $data);
        if ($insert) {
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Membuat catatan mapel');
        }

        $this->output_json($insert);
    }

    public function saveCatatanSiswa(){
        $this->load->model('Log_model', 'logging');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        $id_siswa = $this->input->post('id_siswa');
        $id_mapel = $this->input->post('id_mapel', true);
        $text = $this->input->post('text', true);
        $level = $this->input->post('level', true);
        //$tgl = date('Y-m-d');
        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '2',
            'id_mapel' => $id_mapel,
            'id_siswa' => $id_siswa,
            'id_guru' => $guru->id_guru,
            'level' => $level,
            //'tgl' => $tgl,
            'text' => $text,
            'reading' => json_encode([])
        ];
        $insert = $this->master->create('kelas_catatan_mapel', $data);
        if ($insert) {
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Membuat catatan siswa');
        }

        $this->output_json($insert);
    }

    public function hapus($id_catatan){
        $this->load->model('Log_model', 'logging');

        $delete = $this->master->delete('kelas_catatan_mapel', $id_catatan, 'id_catatan');
        $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Menghapus catatn mapel/siswa');

        $this->output_json($delete);
    }
}