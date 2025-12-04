<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Datakelas extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
		} else if (!$this->auth->is_admin()) {
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
		$this->load->model('Kelas_model', 'kelas');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Rapor_model', 'rapor');
		$this->form_validation->set_error_delimiters('', '');
	}

	public function index() {
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
			'judul' => 'Kelas',
			'subjudul' => 'Data Kelas',
            'setting'		=> $setting
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;


        $chek = $this->kelas->count_all();
        $kelas = [];
        $kelas_lama = [];
        if ($chek > 0) {
            $kelas = $this->kelas->getKelasList($tp->id_tp, $smt->id_smt);
            $tp_before = $this->dashboard->getTpBefore($tp->tahun);
            if ($tp_before) {
                $kelas_lama = $this->kelas->getKelasList($tp_before->id_tp, '2');
            }
        }
		$data['kelas'] = $kelas;
        $data['kelas_lama'] = $kelas_lama;

		$data['jurusan'] = $this->kelas->get_jurusan();
        $data['level'] = $this->kelas->getLevel($setting->jenjang);
		$data['guru'] = $this->kelas->get_guru();
		//$data['siswa'] = $this->kelas->getAllSiswa($tp->id_tp, $smt->id_smt);

		$this->render_view(1, 'master/kelas/data', $data);
	}

    public function detail($id) {
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Detail Kelas',
            'subjudul' => 'Detail Kelas',
            'setting'		=> $setting
        ];
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $kelas = $this->kelas->get_one($id);
        if ($kelas) $kelas->siswa = $this->maybe_unserialize($kelas->jumlah_siswa);
        $data['kelas'] = $kelas;
        $data['jurusan'] = $this->kelas->get_jurusan();
        $data['level'] = $this->kelas->getLevel($setting->jenjang);
        $data['guru'] = $this->kelas->get_guru();
        $data['siswas'] = $this->kelas->get_siswa_kelas($id, $tp->id_tp, $smt->id_smt);
        $struktur = $this->kelas->getStrukturKelas($id);
        if ($struktur==null) {
            $data['struktur'] = json_decode(json_encode($this->kelas->dummyStruktur()));
        } else {
            $data['struktur'] = $struktur;
        }

        $this->render_view(1, 'master/kelas/detail', $data);
    }

	public function add() {
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
			'judul' => 'Kelas',
			'subjudul' => 'Tambah Kelas',
            'setting'		=> $setting
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

		$data['kelas'] = json_decode(json_encode($this->kelas->dummy()));
		$data['jurusan'] = $this->kelas->get_jurusan();
        $data['level'] = $this->kelas->getLevel($setting->jenjang);
		$data['guru'] = $this->kelas->get_guru();
		$siswa = $this->kelas->getAllSiswa($tp->id_tp, $smt->id_smt);
		$data['siswa'] = $siswa;
		$data['siswakelas'] = array();

		$this->render_view(1, 'master/kelas/add', $data);
	}

	public function edit($id = '') {
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
			'judul' => 'Kelas',
			'subjudul' => 'Edit Kelas',
            'setting'		=> $setting
		];
		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

        $data['id_kelas'] = $id;
        $kelas = $this->kelas->get_one($id);
        if ($kelas) $kelas->jumlah_siswa = $this->maybe_unserialize($kelas->jumlah_siswa);
        $data['kelas'] = $kelas;
		$data['jurusan'] = $this->kelas->get_jurusan();
        $data['level'] = $this->kelas->getLevel($setting->jenjang);
		$data['guru'] = $this->kelas->getWaliKelas($tp->id_tp, $smt->id_smt);
		$data['siswa'] = $this->kelas->getAllSiswa($tp->id_tp, $smt->id_smt);
		$data['siswakelas'] = $this->kelas->get_siswa_kelas($id, $tp->id_tp, $smt->id_smt);

		$this->render_view(1, 'master/kelas/add', $data);
	}

	public function save() {
		$id = $this->input->post('id_kelas', true);
        $guru_id = $this->input->post('guru_id', TRUE);
		$id_tp = $this->master->getTahunActive()->id_tp;
		$id_smt = $this->master->getSemesterActive()->id_smt;
        $siswas = $this->input->post('siswa', true) ?? [];

		$config = array(
			array(
				'field' => 'nama_kelas',
				'label' => 'Nama Kelas',
				'rules' => 'trim'
			),
            array(
                'field' => 'kode_kelas',
                'label' => 'Kode Kelas',
                'rules' => 'trim'
            ),
			array(
				'field' => 'jurusan_id',
				'label' => 'Jurusan',
				'rules' => 'trim'
			),
			array(
				'field' => 'level_id',
				'label' => 'Level',
				'rules' => 'trim'
			),
			array(
				'field' => 'guru_id',
				'label' => 'Guru',
				'rules' => 'trim'
			),
			array(
				'field' => 'siswa_id',
				'label' => 'Siswa',
				'rules' => 'trim'
			)
		);

        $siswakelas = [];
        for ($i = 0, $iMax = count($siswas); $i <= $iMax; $i++) {
            $id_siswa = $siswas[$i] ?? null;
            if ($id_siswa!==null) {
                $siswakelas[] = ['id' => $id_siswa];
            }
        }
        $jumlah = json_encode($siswakelas);
        $insert = array(
            'nama_kelas' => $this->input->post('nama_kelas', TRUE),
            'kode_kelas' => $this->input->post('kode_kelas', TRUE),
            'jurusan_id' => $this->input->post('jurusan_id', TRUE) ?? '',
            'id_tp' => $id_tp,
            'id_smt' => $id_smt,
            'level_id' => $this->input->post('level_id', TRUE),
            'guru_id' => $this->input->post('guru_id', TRUE) ?? '',
            'siswa_id' => $this->input->post('siswa_id', TRUE),
            'jumlah_siswa' => $jumlah
        );

        $id_new = null;
        if ($id != null && $id != '') {
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == TRUE) {
                $this->db->where('id_kelas', $id);
                $status = $this->db->update('master_kelas', $insert);
            } else {
                $status = FALSE;
            }
		} else {
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == TRUE) {
                $status = $this->db->insert('master_kelas', $insert);
                $id_new = $this->db->insert_id();
            } else {
                $status = FALSE;
            }
		}

		$updated = false;
        $siswa_inserted = 0;
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
		if ($status) {
            $this->db->set('id_kelas', $id);
            $this->db->where('id_jabatan_guru', $guru_id.$id_tp.$id_smt);
            $updated = $this->db->update('jabatan_guru');
            if ($updated) {
                $insert = [];
                if ($id != null && $id != '') {
                    $siswa_kelas = $this->kelas->get_status_siswa_kelas($id, $id_tp, $id_smt);
                    if (count($siswa_kelas) > 0) {
                        foreach ($siswa_kelas as $id_siswa=>$sis) {
                            $insert[$id_tp . $id_smt . $id_siswa] = [
                                'id_kelas_siswa' => $id_tp . $id_smt . $id_siswa,
                                'id_tp' => $id_tp,
                                'id_smt' => $id_smt,
                                'id_kelas' => 0,
                                'id_siswa' => $id_siswa,
                            ];
                        }
                    }
                }

                for ($i = 0; $i <= count($siswas); $i++) {
                    $idsiswa = isset($siswas[$i]) ? $siswas[$i] : null;
                    $new_id_kelas = $id != null && $id != '' ? $id : $id_new;
                    if ($idsiswa!=null) {
                        if (isset($insert[$id_tp . $id_smt . $idsiswa])) {
                            $insert[$id_tp . $id_smt . $idsiswa]['id_kelas'] = $new_id_kelas;
                        } else {
                            $insert[$id_tp . $id_smt . $idsiswa] = [
                                'id_kelas_siswa' => $id_tp . $id_smt . $idsiswa,
                                'id_tp' => $id_tp,
                                'id_smt' => $id_smt,
                                'id_kelas' => $new_id_kelas,
                                'id_siswa' => $idsiswa,
                            ];
                        }
                    }
                }

                foreach ($insert as $ins) {
                    if ($this->db->replace('kelas_siswa', $ins)) {
                        $siswa_inserted ++;
                    };
                }
                $data['insert'] = $insert;
            }
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $data['siswa'] = $siswa_inserted;
        $data['update'] = $updated;
		$data['status'] = $status;
		$this->output_json($data);
	}

    public function bulk_create() {
        $posts = json_decode($this->input->post('kelas', true));
        $insert = $this->db->insert_batch('master_kelas', $posts);
        $this->output_json(['success'=> $insert, "insert" => $insert]);
    }

	public function update_kelas($id) {
		$id_tp = $this->master->getTahunActive()->id_tp;
		$id_smt = $this->master->getSemesterActive()->id_smt;
        $siswakelas = $this->kelas->get_status_siswa_kelas($id, $id_tp, $id_smt);

        if (count($siswakelas) > 0) {
            foreach ($siswakelas as $id_siswa=>$sis) {
                $insert = [
                    'id_kelas_siswa' => $id_tp . $id_smt . $id_siswa,
                    'id_tp' => $id_tp,
                    'id_smt' => $id_smt,
                    'id_kelas' => 0,
                    'id_siswa' => $id_siswa,
                ];
                $this->db->replace('kelas_siswa', $insert);
            }
        }

		$rowsSelect = count($this->input->post('siswa', true));
		for ($i = 0; $i <= $rowsSelect; $i++) {
			$id_siswa = $this->input->post('siswa['.$i.']', true);

			if ($id_siswa!=null) {
				$insert = [
					'id_kelas_siswa' => $id_tp . $id_smt . $id_siswa,
					'id_tp' => $id_tp,
					'id_smt' => $id_smt,
					'id_kelas' => $id,
					'id_siswa' => $id_siswa,
				];
				$this->db->replace('kelas_siswa', $insert);
			}
		}
		return $siswakelas;
	}

    public function manage() {
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Copy Kelas',
            'subjudul' => 'Copy Data Kelas ke SMT II',
            'setting'		=> $this->dashboard->getSetting(),
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, '1');
        $data['kelas2'] = $this->dropdown->getAllKelas($tp->id_tp, '2');

        $this->render_view(1, 'master/kelas/persemester', $data);
    }

	public function getFromSmt1($kelas) {
        $tp = $this->dashboard->getTahunActive();
        $data1 = $this->kelas->getKelasSiswa($kelas, $tp->id_tp, '1');
        $data2 = $this->kelas->getKelasSiswa($kelas, $tp->id_tp, '2');

        $ids = [];
        if (count($data2)>0) {
            foreach ($data2 as $s) {
                $ids[] = $s->id_siswa;
            }
        }
        $this->output_json(['smt1'=>$data1, 'smt2'=>$ids]);
    }

    public function copyFromSmt1() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $kelas1 = $this->input->post('kelas_lama', true);
        $kelas2 = $this->input->post('kelas_baru', true);

        $kelas = $this->kelas->get_one($kelas1, $tp->id_tp, '1');
        $data = array(
            'nama_kelas' => $kelas2,
            'kode_kelas' => $kelas->kode_kelas,
            'jurusan_id' => $kelas->jurusan_id ?? '',
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'level_id' => $kelas->level_id,
            'guru_id' => $kelas->guru_id,
            'siswa_id' => $kelas->siswa_id,
            'jumlah_siswa' => $kelas->jumlah_siswa
        );

        $this->db->insert('master_kelas', $data);
        $idk = $this->db->insert_id();

        $res = [];
        $arrSiswa = $this->maybe_unserialize($kelas->jumlah_siswa);
        foreach ($arrSiswa as $value) {
            $id_siswa = $value['id'];
            if ($id_siswa!=null) {
                $insert = [
                    'id_kelas_siswa' => $tp->id_tp . $smt->id_smt . $id_siswa,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'id_kelas' => $idk,
                    'id_siswa' => $id_siswa,
                ];
                $res[] = $this->db->replace('kelas_siswa', $insert);
            }
        }
        $this->output_json($res);
    }

    public function copyAllFromSmt1() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $kelas1 = $this->input->post('kelas_satu', true);
        $kelas2 = $this->input->post('kelas_dua', true);

        $kelass = $this->kelas->getAllKelas($kelas1, $tp->id_tp, '1');
        $datas = [];
        $res = [];
        foreach ($kelass as $kelas) {
            if (isset($kelas2[$kelas->id_kelas])) {
                $data = array(
                    'nama_kelas' => $kelas2[$kelas->id_kelas],
                    'kode_kelas' => $kelas->kode_kelas,
                    'jurusan_id' => $kelas->jurusan_id ?? '',
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'level_id' => $kelas->level_id,
                    'guru_id' => $kelas->guru_id,
                    'siswa_id' => $kelas->siswa_id,
                    'jumlah_siswa' => $kelas->jumlah_siswa
                );
                $this->db->insert('master_kelas', $data);
                $idk = $this->db->insert_id();

                $arrSiswa = $this->maybe_unserialize($kelas->jumlah_siswa);
                foreach ($arrSiswa as $value) {
                    $id_siswa = $value->id;
                    if ($id_siswa != null) {
                        $insert = [
                            'id_kelas_siswa' => $tp->id_tp . $smt->id_smt . $id_siswa,
                            'id_tp' => $tp->id_tp,
                            'id_smt' => $smt->id_smt,
                            'id_kelas' => $idk,
                            'id_siswa' => $id_siswa,
                        ];
                        //$res[] = $insert;
                        $res[] = $this->db->replace('kelas_siswa', $insert);
                    }
                }
            }
        }

        $this->output_json($res);
    }

    public function copyKelasBefore() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $kelasLalu = $this->input->post('kelas_satu', true);
        $kelasSkrg = $this->input->post('kelas_dua', true);

        $kelass = $this->kelas->getAllKelas($kelasLalu, $tp->id_tp, '2');
        $datas = [];
        $res = [];
        foreach ($kelass as $kelas) {
            if (isset($kelasSkrg[$kelas->id_kelas])) {
                $data = array(
                    'nama_kelas' => $kelasSkrg[$kelas->id_kelas],
                    'kode_kelas' => $kelas->kode_kelas,
                    'jurusan_id' => $kelas->jurusan_id ?? '',
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'level_id' => $kelas->level_id,
                    'guru_id' => $kelas->guru_id,
                    'siswa_id' => $kelas->siswa_id,
                    'jumlah_siswa' => $kelas->jumlah_siswa
                );
                $this->db->insert('master_kelas', $data);
                $idk = $this->db->insert_id();

                $arrSiswa = $this->maybe_unserialize($kelas->jumlah_siswa);
                foreach ($arrSiswa as $value) {
                    $id_siswa = $value['id'];
                    if ($id_siswa != null) {
                        $insert = [
                            'id_kelas_siswa' => $tp->id_tp . $smt->id_smt . $id_siswa,
                            'id_tp' => $tp->id_tp,
                            'id_smt' => $smt->id_smt,
                            'id_kelas' => $idk,
                            'id_siswa' => $id_siswa,
                        ];
                        //$res[] = $insert;
                        $res[] = $this->db->replace('kelas_siswa', $insert);
                    }
                }
            }
        }

        $this->output_json($res);
    }

    public function copySiswaFromSmt1() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $posts = json_decode($this->input->post('kelas', true));

        $idkelases = [];
        $siswakelas = [];
        foreach ($posts as $d) {
            $idkelases[] = $d->id_kelas;
            $siswakelas[$d->id_kelas][] = [
                'id' => $d->id_siswa
            ];
        }
        $idkelases = array_unique($idkelases);

        $res = [];
        foreach ($idkelases as $ik) {
            if ($ik != '') {
                $kelas = $this->kelas->get_one($ik, $tp->id_tp, '1');

                $jumlah = json_encode($siswakelas[$ik]);
                $data = array(
                    'nama_kelas' => $kelas->nama_kelas,
                    'kode_kelas' => $kelas->kode_kelas,
                    'jurusan_id' => $kelas->jurusan_id ?? '',
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'level_id' => $kelas->level_id,
                    'guru_id' => $kelas->guru_id,
                    'siswa_id' => $kelas->siswa_id,
                    'jumlah_siswa' => $jumlah
                );
                $this->db->insert('master_kelas', $data);
                $idk = $this->db->insert_id();

                foreach ($siswakelas[$ik] as $s) {
                    $insert = [
                        'id_kelas_siswa' => $tp->id_tp . $smt->id_smt . $s['id'],
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'id_kelas' => $idk,
                        'id_siswa' => $s['id'],
                    ];
                    $res[] = $this->db->replace('kelas_siswa', $insert);
                }
            }
        }
        $this->output_json($res);
    }

    public function kenaikan() {
        $kelas = $this->input->get('kelas', true);
        $type = $this->input->get('type', true);
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Kenaikkan Kelas',
            'subjudul' => 'Naik Kelas Siswa',
            'setting'		=> $setting,
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $level = $setting->jenjang == '1' ? '6' : ($setting->jenjang == '2' ? '9' : '12');

        $tp_before = $this->dashboard->getTpBefore($tp->tahun);
        $kelas_lama = [];
        $kelas_baru = [];
        $siswa_kelas = [];
        $all_kelas_baru = [];
        if ($tp_before) {
            $kelas_lama = $this->dropdown->getAllKelas($tp_before->id_tp, '2', '!='.$level);
            $kelas_baru = $this->dropdown->getAllKelas($tp->id_tp, '1');

            $arrKls = array_keys($kelas_baru);
            if (count($arrKls) > 0) {
                $all_siswa_baru = $this->kelas->getKelasSiswa($arrKls);
                foreach ($all_siswa_baru as $value) {
                    $siswa_kelas[$value->nama_kelas][] = $value->id_siswa;
                }
            }

            $all_kelas_lama = $this->dropdown->getAllKelas($tp_before->id_tp, '2');
            $arrKlsLama = array_keys($all_kelas_lama);
            if (count($arrKlsLama) > 0) {
                $all_siswa_lama = $this->kelas->getKelasSiswa($arrKlsLama);
                foreach ($all_siswa_lama as $s_kelas) {
                    $siswa_kelas[$s_kelas->id_kelas][] = $s_kelas->id_siswa;
                }
            }
            $result_baru = $this->group_kelas_by_level($all_kelas_lama); // id_kelas lama, nama_kelas baru
            foreach ($result_baru as $lvl=>$klss) {
                foreach ($klss as $id_kls=>$kls)
                    $all_kelas_baru[$id_kls] = $kls;
            }
        }
        $data['kelas_lama'] = $kelas_lama;
        $data['kelas_baru'] = $kelas_baru;

        $result = $this->group_kelas_by_level($kelas_lama);
        $data['buat_kelas_baru'] = $result;

        $data['all_kelas_baru'] = $all_kelas_baru;
        $data['siswa_kelas'] = $siswa_kelas;

        if ($kelas != null) {
            $data['siswa_kelas_baru'] = $this->master->getSiswaKelasBaru($tp->id_tp, $smt->id_smt);

            $siswa_naik = [];
            $level = '0';
            if ($tp_before) {
                $siswa_naik = $this->rapor->getKenaikanSiswa($kelas, $tp_before->id_tp, '2');

                $lvlKls = $this->db->select()
                    ->where('id_kelas', $kelas)
                    ->where('id_tp', $tp_before->id_tp)
                    ->where('id_smt', '2')
                    ->get('master_kelas')->row();
                $level = $lvlKls ? $lvlKls->level_id+1 : '0';
            }
            $data['siswas'] = $siswa_naik;
            $data['kelas_selected'] = $kelas;
            $data['type_selected'] = $type;

            $data['level'] = $level;
            $data['kelases'] = $result_baru[$level] ?? [];
        }

        $this->render_view(1, 'master/kelas/naikkelas', $data);
    }

    public function group_kelas_by_level($nama_kls)
    {
        $romawi_to_angka = [
            'XII'  => 12,
            'XI'   => 11,
            'X'    => 10,
            'IX'   => 9,
            'VIII' => 8,
            'VII'  => 7,
            'VI'   => 6,
            'V'    => 5,
            'IV'   => 4,
            'III'  => 3,
            'II'   => 2,
            'I'    => 1,
        ];

        $angka_to_romawi = [
            1  => 'I',
            2  => 'II',
            3  => 'III',
            4  => 'IV',
            5  => 'V',
            6  => 'VI',
            7  => 'VII',
            8  => 'VIII',
            9  => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $result = [];

        foreach ($nama_kls as $key => $kls) {
            $kelas_baru = $kls;
            $level = '-';

            // Simpan kls asli, uppercase-kan untuk pencocokan saja
            $clean = strtoupper(trim($kls));

            // ROMAWI
            foreach ($romawi_to_angka as $romawi => $angka) {
                if (preg_match('/^' . $romawi . '([\s\-A-Z0-9].*)?$/i', $clean, $matches)) {
                    if ($angka >= 12) {
                        $kelas_baru = 'Lulus';
                        $level = 'Lulus';
                    } else {
                        $sisa = $matches[1] ?? '';
                        $kelas_baru = $angka_to_romawi[$angka + 1] . $sisa;
                        $level = $angka + 1;
                    }
                    break; // romawi match
                }
            }

            // ANGKA biasa, jika belum match romawi
            if ($level == '-') {
                if (preg_match('/^(\d{1,2})([\s\-A-Z0-9].*)?$/', $kls, $matches)) {
                    $angka = (int)$matches[1];
                    $sisa = $matches[2] ?? '';
                    if ($angka >= 12) {
                        $kelas_baru = 'Lulus';
                        $level = 'Lulus';
                    } else {
                        $kelas_baru = ($angka + 1) . $sisa;
                        $level = $angka + 1;
                    }
                }
            }

            // Tetapkan level berdasarkan hasil kelas_baru (bukan dari angka sebelumnya!)
            if (preg_match('/^(\d{1,2})[\s\-]*/', $kelas_baru, $match)) {
                $level = (int)$match[1];
            } elseif (preg_match('/^(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I)[\s\-]*/', strtoupper($kelas_baru), $match)) {
                $level = $romawi_to_angka[$match[1]];
            } elseif ($kelas_baru == 'Lulus') {
                $level = 'Lulus';
            } else {
                $level = '-';
            }

            if (!isset($result[$level])) {
                $result[$level] = [];
            }

            $result[$level][$key] = $kelas_baru;
        }

        return $result;
    }

    public function naikKelas() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $posts = json_decode($this->input->post('kelas', true));
        $mode = $this->input->post('mode', true);

        $idkelases = []; // id lama
        $nama_kelass = []; // nama baru
        $siswakelas = []; // ambil dari kelas lama
        foreach ($posts as $d) {
            $idkelases[] = $d->id_kls_lama;
            $nama_kelass[$d->id_kls_lama] = $d->nama_kls_baru;
            $siswakelas[$d->id_kls_lama][] = (object)['id' => $d->id_siswa];
        }
        $idkelases = array_unique($idkelases);
        $all_kls_lama = $this->kelas->getAllKelas($idkelases); // kelas lama pasti sudah ada
        $kls_lama = [];
        foreach ($all_kls_lama as $lama) {
            $kls_lama[$lama->id_kelas] = $lama;
        }

        $all_kls_baru = $this->kelas->getKelasByNama($nama_kelass, $tp->id_tp, $smt->id_smt);
        $kls_baru = [];
        foreach ($all_kls_baru as $baru) {
            $kls_baru[$baru->nama_kelas] = $baru;
        }

        $inserted = [];
        $updated = [];
        $all_siswa = [];
        foreach ($nama_kelass as $id_lama=>$nama_baru) {
            $kelas_lama = $kls_lama[$id_lama];
            $kelas_baru = $kls_baru[$nama_baru] ?? null;
            if ($kelas_baru == null) {
                $jumlah = $siswakelas[$id_lama];
                $insert = array(
                    'nama_kelas' => $nama_baru,
                    'kode_kelas' => $nama_baru,
                    'jurusan_id' => $kelas_lama->jurusan_id ?? '',
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'level_id' => (int)$kelas_lama->level_id +1,
                    'guru_id' => $kelas_lama->guru_id,
                    'siswa_id' => $kelas_lama->siswa_id,
                    'jumlah_siswa' => json_encode($jumlah)
                );
                $inserted[] = $insert;
                $this->db->insert('master_kelas', $insert);
                $id = $this->db->insert_id();
                $all_siswa[$id] = $jumlah;
            } else {
                if ($mode == 'persiswa') {
                    $siswakelas_baru = $this->maybe_unserialize($kelas_baru->jumlah_siswa);
                    $merged_ids = array_merge(array_column($siswakelas[$id_lama], 'id'), array_column($siswakelas_baru, 'id'));
                    $merge = array_unique($merged_ids);
                    $baru = [];
                    if (count($merge) > 0) {
                        foreach ($merge as $id_siswa) {
                            $baru[] = (object)['id' => $id_siswa];
                        }
                    }
                    $jumlah = $baru;
                } else {
                    $jumlah = $siswakelas[$id_lama];
                }
                $all_siswa[$kelas_baru->id_kelas] = $jumlah;
                $update = array(
                    'nama_kelas' => $kelas_baru->nama_kelas,
                    'kode_kelas' => $kelas_baru->kode_kelas,
                    'jurusan_id' => $kelas_baru->jurusan_id ?? '',
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'level_id' => $kelas_baru->level_id,
                    'guru_id' => $kelas_baru->guru_id,
                    'siswa_id' => $kelas_baru->siswa_id,
                    'jumlah_siswa' => json_encode($jumlah)
                );
                $this->db->where('id_kelas', $kelas_baru->id_kelas);
                $this->db->update('master_kelas', $update);
                $updated[] = $update;
            }
        }

        $res = [];
        foreach ($all_siswa as $id_kls=>$siswas) {
            foreach ($siswas as $siswa) {
                $insert_siswa = [
                    'id_kelas_siswa' => $tp->id_tp . $smt->id_smt . $siswa->id,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'id_kelas' => $id_kls,
                    'id_siswa' => $siswa->id,
                ];
                //$res[] = $insert_siswa;
                $res[] = $this->db->replace('kelas_siswa', $insert_siswa);
            }
        }

        $data['lama'] = $all_kls_lama;
        $data['baru'] = $all_kls_baru;
        $data['update'] = $updated;
        $data['insert'] = $inserted;
        $data['res'] = $res;
        $this->output_json($data);
    }

    public function naikkan_semua() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $posts = $this->input->post('kelas_baru', true);

        $tp_before = $this->dashboard->getTpBefore($tp->tahun);
        $all_kelas = $this->kelas->getAllKelas();

        $all_asal = []; // ambil data siswa, guru, jurusan
        $all_lama = []; // kelas next level untuk ambil nama_kelas, kode_kelas
        $all_baru = []; // gabungkan ke kelas asal

        foreach ($all_kelas as $kelas) {
            if (in_array($kelas->id_kelas, array_keys($posts))) {
                $all_asal[$kelas->id_kelas] = $kelas;
            } elseif ($tp_before != null && $kelas->id_tp == $tp_before->id_tp && $kelas->id_smt == '2' && in_array($kelas->nama_kelas, $posts)) {
                $all_lama[$kelas->nama_kelas] = $kelas;
            } else {
                if ($kelas->id_tp == $tp->id_tp && $kelas->id_smt == $smt->id_smt) {
                    $all_baru[$kelas->nama_kelas] = $kelas;
                }
            }
        }
        $res = [];
        $idks = [];
        $all_siswa = [];
        $inserted = [];
        $updated = [];
        foreach ($posts as $ik=>$nama_kelas) {
            if ($ik != '') {
                $kelas_baru = $all_baru[$nama_kelas] ?? null; // digabungkan ke kelas asal
                $kelas_lama = $all_lama[$nama_kelas] ?? null; // ambil nama_kelas, kode_kelas
                $kelas_asal = $all_asal[$ik]; // ambil data siswa, guru, jurusan
                $siswakelas = $this->maybe_unserialize($kelas_asal->jumlah_siswa);
                if ($kelas_baru == null) {
                    $insert = array(
                        'nama_kelas' => $nama_kelas,
                        'kode_kelas' => $kelas_lama->kode_kelas ?? $nama_kelas,
                        'jurusan_id' => $kelas_asal->id_jurusan ?? '',
                        'id_tp' => $tp->id_tp,
                        'id_smt' => $smt->id_smt,
                        'level_id' => (int)$kelas_asal->level_id + 1,
                        'guru_id' => $kelas_asal->guru_id ?? '',
                        'siswa_id' => $kelas_asal->siswa_id,
                        'jumlah_siswa' => json_encode($siswakelas)
                    );
                    $inserted[] = $insert;
                    $this->db->insert('master_kelas', $insert);
                    $id = $this->db->insert_id();
                    $all_siswa[$id] = $siswakelas;
                    $idks[] = $id;
                } else {
                    $siswakelas_baru = $this->maybe_unserialize($kelas_baru->jumlah_siswa);
                    $merged_ids = array_merge(array_column($siswakelas, 'id'), array_column($siswakelas_baru, 'id'));
                    $merge = array_unique($merged_ids);
                    $baru = [];
                    if (count($merge) > 0) {
                        foreach ($merge as $id_siswa) {
                            $baru[] = (object)['id' => $id_siswa];
                        }
                    }

                    $idks[] = $kelas_baru->id_kelas;
                    $all_siswa[$kelas_baru->id_kelas] = $baru;

                    $update = array(
                        'nama_kelas' => $kelas_baru->nama_kelas,
                        'kode_kelas' => $kelas_baru->kode_kelas,
                        'jurusan_id' => $kelas_baru->jurusan_id ?? '',
                        'id_tp' => $kelas_baru->id_tp,
                        'id_smt' => $kelas_baru->id_smt,
                        'level_id' => $kelas_baru->level_id,
                        'guru_id' => $kelas_baru->guru_id,
                        'siswa_id' => $kelas_baru->siswa_id,
                        'jumlah_siswa' => json_encode($baru),
                    );
                    $this->db->where('id_kelas', $kelas_baru->id_kelas);
                    $this->db->update('master_kelas', $update);
                    $updated[] = $update;
                }
            }
        }

        $insert_siswa = [];
        foreach ($idks as $idk) {
            foreach ($all_siswa[$idk] as $s) {
                $insert = [
                    'id_kelas_siswa' => $tp->id_tp . $smt->id_smt . $s->id,
                    'id_tp' => $tp->id_tp,
                    'id_smt' => $smt->id_smt,
                    'id_kelas' => $idk,
                    'id_siswa' => $s->id,
                ];
                $insert_siswa[] = $insert;
                $res[] = $this->db->replace('kelas_siswa', $insert);
            }
        }

        $data['insert'] = $inserted;
        $data['update'] = $updated;
        $data['siswa'] = $all_siswa;

        /*
        $data['lama'] = $all_lama;
        $data['baru'] = $all_baru;
        $data['asal'] = $all_asal;
        */

        $data['id_kelas'] = $idks;
        $data['res'] = count($res);
        $this->output_json($data);
    }

    public function hapus($id_kelas) {
        //$delete['siswa'] = $this->master->delete('kelas_siswa', [$id_kelas, '0'], 'id_kelas');
        $delete['siswa'] = $this->master->delete('kelas_siswa', $id_kelas, 'id_kelas');
        $delete['kelas'] = $this->master->delete('master_kelas', $id_kelas, 'id_kelas');
        $this->output_json($delete);
    }

    private function normalize($str) {
        return strtolower(preg_replace('/[\s.\-_]/', '', $str ?? ''));
    }
}
