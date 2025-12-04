<?php


class Cbtsesisiswa extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
            show_error('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Master_model', 'master');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Cbt_model', 'cbt');
		$this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul' => 'Atur Ruang dan Sesi Siswa',
			'subjudul' => 'Ruang dan Sesi Siswa',
			'setting' => $this->dashboard->getSetting(),
			'tp' 		=> $this->dashboard->getTahun(),
			'tp_active' => $tp,
			'smt' 		=> $this->dashboard->getSemester(),
			'smt_active' => $smt,
			'profile'		=> null,
		];

        $kls = $this->input->get('kls', true);
        $kelas_selected = $kls != null ? $kls : '0';
        $siswas = [];
        if ($kelas_selected != '0') {
            $siswas = $this->cbt->getRuangSesiSiswa($kls, $tp->id_tp, $smt->id_smt);
        }
        $data['siswas'] = $siswas;
        $data['kelas_selected'] = $kelas_selected;
        $data['sesi']		= $this->dropdown->getAllSesi();
        $data['ruang']		= $this->cbt->getAllRuang();
        $is_admin = $this->auth->is_admin();
        $data['is_admin'] = $is_admin;

        if ($is_admin) {
            $data['kelas']		= $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $this->render_view(1, 'cbt/sesisiswa/data', $data);
        } else {
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
            $this->render_view(2, 'cbt/sesisiswa/data', $data);
        }
    }

	public function getAllRuang() {
		$this->output_json($this->cbt->getAllRuang());
	}

	public function getAllSesi() {
		$this->output_json($this->dropdown->getAllSesi());
	}

	public function add() {
		$insert = [
			'nama_sesi' => $this->input->post('nama_sesi', true),
			'kode_sesi' => $this->input->post('kode_sesi', true),
			'waktu_mulai' => $this->input->post('waktu_mulai', true),
			'waktu_akhir' => $this->input->post('waktu_akhir', true)
		];
		$this->master->create('cbt_sesi', $insert, false);

		$data['status'] = $insert;
		$this->output_json($data);
	}

	public function update() {
		$data = $this->cbt->updateSesi();
		$this->output->set_content_type('application/json')->set_output($data);
	}

	public function delete() {
		$chk = $this->input->post('checked', true);
		if (!$chk) {
			$this->output_json(['status' => false]);
		} else {
			if ($this->master->delete('cbt_sesi', $chk, 'id_sesi')) {
				$this->output_json(['status' => true, 'total' => count($chk)]);
			}
		}
	}

	/*
	public function getSiswaRuang() {
		$kelas = $this->input->get('kelas', true);
		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$this->output_json($this->cbt->getRuangSesiSiswa($kelas, $tp->id_tp, $smt->id_smt));
	}
	*/

	public function editsesisiswa() {
        $this->load->model('Log_model', 'logging');

        $rs = $this->input->post('ruang-sesi', true);
        $kls = $this->input->post('kelas', true);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $siswas = $this->cbt->getRuangSesiSiswa($kls, $tp->id_tp, $smt->id_smt);

        $map_siswa = [];
        foreach ($siswas as $siswa) {
            $map_siswa[$siswa->siswa_id] = $siswa;
        }

        $update = [];
        $insert = [];
        foreach ($rs as $id=>$klss) {
            foreach ($klss as $idkls=>$kls) {
                $data = [
                    'siswa_id'	=> $id,
                    'kelas_id'	=> $idkls,
                    'ruang_id'	=> $kls['ruang'],
                    'sesi_id'	=> $kls['sesi'],
                    'tp_id'		=> $tp->id_tp,
                    'smt_id'	=> $smt->id_smt
                ];

                if (isset($map_siswa[$id])) {
                    $data['id_sesi_siswa'] = $map_siswa[$id]->id_sesi_siswa;
                    $update[] = $data;
                } else {
                    $insert[] = $data;
                }
            }
        }
        if (count($insert)>0) {
            $this->db->insert_batch('cbt_sesi_siswa', $insert);
        }
        if (count($update)>0) {
            $this->db->update_batch('cbt_sesi_siswa', $update, 'id_sesi_siswa');
        }
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Megatur ruang dan sesi ujian siswa');

        $data['status'] = true;
		$this->output_json($data);
	}
}
