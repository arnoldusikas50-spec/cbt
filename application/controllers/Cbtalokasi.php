<?php


class Cbtalokasi extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if (!$this->auth->logged_in()) {
			redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('panitia') && !$this->auth->in_group('guru')) {
            show_error('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['form_validation']); // Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('', '');
	}

    private function getDistinctTgl($tp, $smt, $jenis) {
        $this->db->distinct('a.tgl_mulai');
        $this->db->select('a.id_jenis, a.tgl_mulai');
        $this->db->from('cbt_jadwal a');
        $this->db->where('a.id_jenis', $jenis);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->order_by('a.tgl_mulai', 'ASC');

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->tgl_mulai] = date('d F Y', strtotime($row->tgl_mulai ?? ''));
            }
        }
        return $ret;
    }

    private function getDistinctRuang($tp, $smt, $arrKelas) {
        $this->db->distinct('a.ruang_id');
        $this->db->select('a.ruang_id, a.sesi_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi, c.waktu_mulai, c.waktu_akhir');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->where('a.tp_id', $tp);
        $this->db->where('a.smt_id', $smt);
        $this->db->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $this->db->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        if (count($arrKelas)>0) {
            $this->db->where_in('kelas_id', $arrKelas);
        }
        $this->db->order_by('b.nama_ruang', 'ASC');
        $this->db->order_by('c.nama_sesi', 'ASC');
        $result = $this->db->get()->result();

        $sesis = [];
        if ($result) {
            foreach ($result as $row) {
                if (!isset($sesis[$row->sesi_id])) {
                    $sesis[$row->sesi_id] = (object)[
                        'sesi_id' => $row->sesi_id,
                        'kode_sesi' => $row->kode_sesi,
                        'nama_sesi' => $row->nama_sesi,
                        'waktu_mulai' => $row->waktu_mulai,
                        'waktu_akhir' => $row->waktu_akhir,
                        'ruangan' => []
                    ];
                }
                $sesis[$row->sesi_id]->ruangan[$row->ruang_id] = (object)[
                    'ruang_id' => $row->ruang_id,
                    'kode_ruang' => $row->kode_ruang,
                    'nama_ruang' => $row->nama_ruang
                ];
            }
        }
        return $sesis;
    }

    public function index() {
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul'	=> 'Alokasi Waktu',
            'subjudul'=> 'Alokasi Waktu Ujian',
            'setting'		=> $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $id_jenis = $this->cbt->getDistinctJenisJadwal($tp->id_tp, $smt->id_smt);
        $ids = [];
        if ($id_jenis && count($id_jenis)>0) {
            foreach ($id_jenis as $jenis) {
                $ids[] = $jenis->id_jenis;
            }
        }

        if ($ids && count($ids)>0) {
            $data['jenis'] = $this->cbt->getAllJenisUjianByArrJenis($ids);
        } else {
            $data['jenis'] = [''=>'belum ada jadwal ujian'];
        }

        $jenis_selected = $this->input->get('jenis', true);

        $data['jenis_selected'] = $jenis_selected;

        $levels = [];
        if ($setting->jenjang == "1") {
            $levels = [ "0"=>"Pilih Level", "1" => "1", "2"=> "2", "3" => "3", "4"=>"4", "5"=>"5", "6"=>"6"];
        } elseif ($setting->jenjang == "2") {
            $levels = [ "0"=>"Pilih Level", "7" => "7", "8"=> "8", "9" => "9"];
        } elseif ($setting->jenjang == "3") {
            $levels = [ "0"=>"Pilih Level", "10" => "10", "11"=> "11", "12" => "12"];
        }
        $data['levels'] = $levels;

        if ($this->auth->is_admin()) {
            $jadwals = [];
            $pengawas = [];
            if ($jenis_selected) {
                $jadwals = $this->cbt->getAllJadwalByTanggal($tp->id_tp, $smt->id_smt, $jenis_selected);
                $pengawas = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt);
            }

            $arrKls = [];
            foreach ($jadwals as $jad) {
                $jad->bank_kelas = $this->maybe_unserialize($jad->bank_kelas ?? []);
                foreach ($jad->bank_kelas as $kl) {
                    if (!in_array($kl->kelas_id, $arrKls)) {
                        $arrKls[] = $kl->kelas_id;
                    }
                }
            }
            $sesis = [];
            if (count($arrKls)>0) {
                $all_sesis = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKls);
                foreach ($all_sesis as $row) {
                    if (!isset($sesis[$row->ruang_id])) {
                        $sesis[$row->ruang_id] = (object)[
                            'ruang_id' => $row->ruang_id,
                            'kode_ruang' => $row->kode_ruang,
                            'nama_ruang' => $row->nama_ruang,
                            'sesi' => []
                        ];
                    }
                    $sesis[$row->ruang_id]->sesi[$row->sesi_id] = (object)[
                        'sesi_id' => $row->sesi_id,
                        'kode_sesi' => $row->kode_sesi,
                        'nama_sesi' => $row->nama_sesi,
                        'waktu_mulai' => $row->waktu_mulai,
                        'waktu_akhir' => $row->waktu_akhir,
                        'istirahat' => $row->istirahat,
                        'kelas_id' => $row->kelas_id,
                        'nama_kelas' => $row->nama_kelas,
                        'peserta' => $row->peserta,
                    ];
                }
            }
            $data['sesis'] = array_values($sesis);
            $data['jadwals'] = $jadwals;
            $data['pengawas'] = $pengawas;
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['tanggal'] = $this->getDistinctTgl($tp->id_tp, $smt->id_smt, $jenis_selected);

            $this->render_view(1, 'cbt/alokasi/data', $data);
        } else {
            $this->load->model('Kelas_model', 'kelas');
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $jadwals = [];
            $pengawas = [];
            if ($jenis_selected) {
                $jadwals = $this->cbt->getAllJadwalByTanggal($tp->id_tp, $smt->id_smt, $jenis_selected, $guru->id_guru);
                $pengawas = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt);
            }

            $arrKls = [];
            foreach ($jadwals as $jad) {
                $jad->bank_kelas = $this->maybe_unserialize($jad->bank_kelas ?? []);
                foreach ($jad->bank_kelas as $kl) {
                    if (!in_array($kl->kelas_id, $arrKls)) {
                        $arrKls[] = $kl->kelas_id;
                    }
                }
            }
            $sesis = [];
            if (count($arrKls)>0) {
                $all_sesis = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKls);
                foreach ($all_sesis as $row) {
                    if (!isset($sesis[$row->ruang_id])) {
                        $sesis[$row->ruang_id] = (object)[
                            'ruang_id' => $row->ruang_id,
                            'kode_ruang' => $row->kode_ruang,
                            'nama_ruang' => $row->nama_ruang,
                            'sesi' => []
                        ];
                    }
                    $sesis[$row->ruang_id]->sesi[$row->sesi_id] = (object)[
                        'sesi_id' => $row->sesi_id,
                        'kode_sesi' => $row->kode_sesi,
                        'nama_sesi' => $row->nama_sesi,
                        'waktu_mulai' => $row->waktu_mulai,
                        'waktu_akhir' => $row->waktu_akhir,
                        'istirahat' => $row->istirahat,
                        'kelas_id' => $row->kelas_id,
                        'nama_kelas' => $row->nama_kelas,
                        'peserta' => $row->peserta,
                    ];
                }
            }
            $data['sesis'] = array_values($sesis);
            $data['jadwals'] = $jadwals;
            $data['pengawas'] = $pengawas;
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['ruang'] = $this->dropdown->getAllRuang();
            $data['tanggal'] = $this->getDistinctTgl($tp->id_tp, $smt->id_smt, $jenis_selected);


            $this->render_view(2, 'cbt/alokasi/data', $data);
        }
    }

    public function detail()
    {
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul'	=> 'Alokasi Waktu',
            'subjudul'=> 'Alokasi Waktu Ujian',
            'setting'		=> $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $id_jenis = $this->cbt->getDistinctJenisJadwal($tp->id_tp, $smt->id_smt);
        $ids = [];
        if ($id_jenis && count($id_jenis)>0) {
            foreach ($id_jenis as $jenis) {
                $ids[] = $jenis->id_jenis;
            }
        }

        if ($ids && count($ids)>0) {
            $data['jenis'] = $this->cbt->getAllJenisUjianByArrJenis($ids);
        } else {
            $data['jenis'] = [''=>'belum ada jadwal ujian'];
        }

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/alokasi/data_detail', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->render_view(2, 'cbt/alokasi/data_detail', $data);
        }
    }

    public function get_tanggal()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $jenis = json_decode($this->input->get('jenis', true));
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $jadwal = $this->getDistinctTgl($tp->id_tp, $smt->id_smt, $jenis);
        $this->output_json($jadwal);
    }

    public function get_alokasi() {
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $jenis_selected = $this->input->post('jenis', true);
        $tanggal_selected = $this->input->post('tanggal', true);

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $jadwals = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,status,jam_ke')
            ->where(['id_tp'=>$tp->id_tp, 'id_smt'=>$smt->id_smt, 'tgl_mulai'=>$tanggal_selected, 'id_jenis'=>$jenis_selected])
            ->with_bank([
                'fields'=> 'bank_kode,bank_kelas,soal_agama,bank_guru_id',
                'where'=> ['id_tp' => $tp->id_tp, 'id_smt'=>$smt->id_smt],
                'with' => [
                    ['relation' => 'mapel', 'fields'=>'kode,nama_mapel'],
                ]
            ])
            ->get_all();

        $arrKelas = [];
        $result = [];
        $all_siswa = [];
        $cbt_siswa = [];

        $data['today'] = date('Y-m-d');
        $data['pengawas'] = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt, null, null);
        $data['gurus'] = $this->dropdown->getAllGuru();
        $data['kelases'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

        if (!$jadwals) {
            $data['sesisiswa'] = $all_siswa;
            $data['cbtsiswa'] = $cbt_siswa;
            $data['ruangs'] = [];
            $data['arr_kelas'] = $arrKelas;
            $data['jadwals_ujian'] = $result;
            $this->output_json($data);
            return;
        }

        $ids_jadwal = [];
        foreach ($jadwals as $jadwal) {
            $ids_jadwal[] = $jadwal->id_jadwal;
            if (isset($jadwal->bank)) {
                $info = $jadwal->bank;
                $kelasIds = $this->maybe_unserialize($info->bank_kelas);
                $info->bank_kelas = array_map('strval', array_column($kelasIds, 'kelas_id'));

                foreach ($info->bank_kelas as $kls) {
                    if ($kls) {
                        $arrKelas[] = $kls;
                    }
                }
                $result[] = $jadwal;
            }
        }
        usort($result, static function ($a, $b) {
            return ($a->bank->bank_mapel_id ?? '') <=> ($b->bank->bank_mapel_id ?? '');
        });

        $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKelas);

        $siswas = $this->sesisiswa_model
            ->where('kelas_id', $arrKelas)
            ->with_siswa(['fields' => 'id_siswa,nama,nis,nisn,agama'])
            ->with_peserta()
            ->with_kelas(['fields'=>'kode_kelas,nama_kelas'])
            ->with_ruang(['fields'=>'kode_ruang,nama_ruang'])
            ->with_sesi(['fields'=>'kode_sesi,nama_sesi'])
            ->as_array()
            ->get_all();

        if (!$siswas) {
            $data['sesisiswa'] = $all_siswa;
            $data['cbtsiswa'] = $cbt_siswa;
            $data['ruangs'] = array_values($ruangs);
            $data['arr_kelas'] = $arrKelas;
            $data['jadwals_ujian'] = $result;
            $this->output_json($data);
            return;
        }

        $ids_siswa = [];
        foreach ($siswas as $siswa) {
            if (!in_array($siswa['siswa_id'], $ids_siswa)) {
                $ids_siswa[] = $siswa['siswa_id'];
            }
            $all_siswa[] = $siswa;
        }

        $cbtsiswa = $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
            ->where('id_siswa', $ids_siswa)
            ->where('id_jadwal', $ids_jadwal)
            ->with_jadwal()
            ->get_all();

        if (!$cbtsiswa) {
            $data['sesisiswa'] = $all_siswa;
            $data['cbtsiswa'] = $cbt_siswa;
            $data['ruangs'] = array_values($ruangs);
            $data['arr_kelas'] = $arrKelas;
            $data['jadwals_ujian'] = $result;
            $this->output_json($data);
            return;
        }

        $data['sesisiswa'] = $all_siswa;
        $data['cbtsiswa'] = (array)$cbtsiswa;
        $data['ruangs'] = array_values($ruangs);
        $data['arr_kelas'] = $arrKelas;
        $data['jadwals_ujian'] = $result;

        $this->output_json($data);
    }

    public function saveAlokasi() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $input = json_decode($this->input->post('alokasi', true));
        //$ids_pengawas = json_decode($this->input->post('ids', true));

        $for_insert = [];
        $for_update = [];
        foreach ($input as $d) {
            if (isset($d->id_pengawas) && $d->id_pengawas != "0") {
                $insert = [
                    'id_pengawas' => $d->id_pengawas,
                    'id_jadwal' => $d->id_jadwal,
                    'id_ruang' => $d->id_ruang,
                    'id_sesi' => $d->id_sesi,
                    'id_tp' => $d->id_tp,
                    'id_smt' => $d->id_smt,
                    'jam_ke' => $d->jam_ke
                ];

                if ($this->auth->in_group('guru')) {
                    $this->load->model('Cbt_model', 'cbt');
                    $user = $this->auth->user();
                    $guru = $this->dashboard->getDataGuruByUserId($user->id, $d->id_tp, $d->id_smt);

                    $id_pengawas = $d->id_tp.$d->id_smt.$d->id_jadwal.$d->id_ruang.$d->id_sesi;
                    $exist = $this->cbt->getPengawas($id_pengawas);
                    if ($exist) {
                        $exist_peng = explode(",", $exist->id_guru);
                        if (!in_array($guru->id_guru, $exist_peng)) {
                            $exist_peng[] = $guru->id_guru;
                        }
                        $insert['id_guru'] = implode(",", $exist_peng);
                    } else {
                        $insert['id_guru'] = $guru->id_guru;
                    }
                }

                if ($this->dashboard->check_data_by_id('cbt_pengawas', 'id_pengawas', $d->id_pengawas)) {
                    $for_update[] = $insert;
                } else {
                    $for_insert[] = $insert;
                }
            }
        }
        $updated = 0;
        if (count($for_insert)>0) {
            $updated1 = $this->db->insert_batch('cbt_pengawas', $for_insert);
            if ($updated1) {
                $updated++;
                $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menambah alokasi waktu ujian');
            }
        }

        if (count($for_update) > 0) {
            $updated2 = $this->db->update_batch('cbt_pengawas', $for_update, 'id_pengawas');
            if ($updated2) {
                $updated++;
                $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengupdate alokasi waktu ujian');
            }
        }

        $data['status'] = $updated;
        $data['insert'] = $for_insert;
        $data['update'] = $for_update;
        $this->output_json($data);

    }
}

