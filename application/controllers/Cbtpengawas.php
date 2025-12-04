<?php


class Cbtpengawas extends MY_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->auth->logged_in()){
			redirect('auth');
		}else if ( !$this->auth->is_admin()){
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="'.base_url('dashboard').'">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['form_validation']);// Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('','');
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

	public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
		$data = [
			'user' => $user,
			'judul'	=> 'Atur Pengawas',
			'subjudul'=> 'Pengawas Ujian/Ulangan',
            'setting'		=> $setting
		];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $kelass = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['kelas'] = $kelass;

        $data['gurus'] = $this->dropdown->getAllGuru();
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
        //$data['tanggal'] = $this->getDistinctTgl($tp->id_tp, $smt->id_smt, $jenis_selected);
        /*
        $jenis_selected = $this->input->get('jenis', true);
        $tanggal_selected = $this->input->get('tanggal', true);
        $data['jenis_selected'] = $jenis_selected;
        $data['tanggal_selected'] = $tanggal_selected;

        $allJadwals = [];
        if ($jenis_selected && $tanggal_selected) {
            $tglJadwals = $this->cbt->getAllJadwalByTanggal($tp->id_tp, $smt->id_smt, $jenis_selected, $tanggal_selected);
            foreach ($tglJadwals as $idx=>$jadwal) {
                $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? '');
                $arrKelas = array_column($jadwal->bank_kelas, 'kelas_id');
                $peserta = $this->cbt->getKelasUjian($tp->id_tp, $smt->id_smt, $arrKelas);
                $kls_psrta = [];
                foreach ($peserta as $kls=>$psrt) {
                    foreach ($psrt as $ruang=>$sesies) {
                        foreach ($sesies as $ses=>$id_kelas) {
                            $key = $jadwal->tgl_mulai.'|'.$ruang.'-'.$ses;
                            if (!isset($kls_psrta[$key])) {
                                $kls_psrta[$key] = count($id_kelas);
                                $jadwal->peserta = count($id_kelas);
                                $allJadwals[$key][$jadwal->id_mapel][] = $jadwal;
                            }
                        }
                    }
                }
            }
        }
        $data['tgl_jadwals'] = $allJadwals;
        $ruangs = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, []);
        $data['ruangs'] = $ruangs;
        $data['pengawas'] = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt);
        */

        $this->render_view(1, 'cbt/pengawas/data', $data);
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

    public function get_pengawas() {
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
                $kelasIds = $this->maybe_unserialize($info->bank_kelas ?? []);
                $info->bank_kelas = array_map('strval', array_column($kelasIds, 'kelas_id'));

                foreach ($info->bank_kelas as $kls) {
                    if ($kls) {
                        $arrKelas[] = $kls;
                    }
                }
                $result[] = $jadwal;
            }
        }

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

    public function savePengawas() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $input = json_decode($this->input->post('data', true));
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $id_tp = $tp->id_tp;
        $id_smt = $smt->id_smt;

        $for_insert = [];
        $for_update = [];
        foreach ($input as $d) {
            if (isset($d->jadwal)) {
                $ruang = $d->ruang;
                $sesi = $d->sesi;
                $jadwal = $d->jadwal;
                $id_pengawas = $id_tp.$id_smt.$jadwal.$ruang.$sesi;

                $dataInsert = [
                    'id_pengawas'	=> $id_pengawas,
                    'id_jadwal'	=> $jadwal,
                    'id_tp' => $id_tp,
                    'id_smt' => $id_smt,
                    'id_ruang' => $ruang,
                    'id_sesi' => $sesi,
                    'id_guru' => implode(",", $d->guru),

                ];

                if ($this->dashboard->check_data_by_id('cbt_pengawas', 'id_pengawas', $id_pengawas)) {
                    $for_update[] = $dataInsert;
                } else {
                    $for_insert[] = $dataInsert;
                }
            }
        }
        $updated = 0;
        if (count($for_insert)>0) {
            $updated1 = $this->db->insert_batch('cbt_pengawas', $for_insert);
            if ($updated1) {
                $updated++;
                $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menambah pengawas ujian');
            }
        }

        if (count($for_update) > 0) {
            $updated2 = $this->db->update_batch('cbt_pengawas', $for_update, 'id_pengawas');
            if ($updated2) {
                $updated++;
                $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengupdate pengawas ujian');
            }
        }

        $data['insert'] = $for_insert;
        $data['update'] = $for_update;
        $data['status'] = $updated;
		$this->output_json($data);
	}
}
