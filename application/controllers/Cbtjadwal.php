<?php


class Cbtjadwal extends MY_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->auth->logged_in()){
			redirect('auth');
		}else if ( !$this->auth->is_admin() && !$this->auth->in_group('guru') ){
			show_error('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini, <a href="'.base_url('dashboard').'">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['datatables', 'form_validation']);// Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('','');
	}

    private function getJadwalTerpakai($id_jadwal = null) {
        $this->db->select('id_bank,id_jadwal,id_siswa');
        $this->db->from('cbt_siswa');
        if ($id_jadwal != null) {
            $this->db->where('id_jadwal', $id_jadwal);
        }
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->id_jadwal][$row->id_siswa] = $row;
        }
        return $ret;
    }

	public function index() {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Dropdown_model', 'dropdown');

        $lvl = $this->input->get('level', true);
        $level = $lvl == null ? '0' : $lvl;
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
		$data = [
			'user' => $user,
			'judul'	=> 'Jadwal Penilaian',
			'subjudul'=> 'PH/PTS/PAT/USBK',
			'setting'		=> $setting
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

        $mode = $this->input->get('mode');
        $type = $this->input->get('type');
        $data['mode'] = $mode == null ? '1' : $mode;

		$data['ruangs'] = $this->cbt->getAllRuang();
		$data['sesis'] = $this->dropdown->getAllSesi();
		$data['jenis'] = $this->cbt->getAllJenisUjian();
		$data['jadwal'] = json_decode(json_encode($this->cbt->dummyJadwal()));

        $data['jmlIst'] = [];
        $data['jmlMapel'] = [];
        $data['level'] = $level;
        if ($mode) {
            $terpakai = $this->getJadwalTerpakai();
            $jadwal_terpakai = [];
            foreach ($terpakai as $idj=>$rows) {
                $jadwal_terpakai[$idj] = count($rows);
            }
            $data['total_siswa'] = $jadwal_terpakai;
        }
        $data['ada_ujian'] = $this->cbt->getDataJadwalByTgl(date('Y-m-d'));
        $data['levels'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
        $data['rekaps'] = $this->cekRekapSiswa();

        if($this->auth->is_admin()){
            $data['gurus'] = $this->dropdown->getAllGuru();
            $data['mapels'] = $this->dropdown->getAllMapel();

            $data['filters'] = ['0'=>'Semua', '1'=>'Guru', '2'=>'Mapel', '3'=>'Level'];
            $data['id_filter'] = $type == null ? '' : $type;
            $jadwals = [];
            if ($type == '0') {
                $jadwals = $this->cbt->getAllDataJadwal($tp->id_tp, $smt->id_smt);
                $data['id_guru'] = null;
                $data['id_mapel'] = null;
                $data['id_level'] = null;
            } elseif ($type == '1') {
                $id_guru = $this->input->get('id');
                $data['id_guru'] = $id_guru;
                $jadwals = $this->cbt->getAllDataJadwal($tp->id_tp, $smt->id_smt, $id_guru);

                $data['id_mapel'] = '';
                $data['id_level'] = '';
            } elseif ($type == '2') {
                $id_mapel = $this->input->get('id');
                $data['id_mapel'] = $id_mapel;
                $jadwals = $this->cbt->getAllDataJadwal($tp->id_tp, $smt->id_smt,null, $id_mapel);

                $data['id_guru'] = '';
                $data['id_level'] = '';
            } elseif ($type == '3') {
                $id_level = $this->input->get('id');
                $data['id_level'] = $id_level;
                $jadwals = $this->cbt->getAllDataJadwal($tp->id_tp, $smt->id_smt,null, null, $id_level);

                $data['id_guru'] = '';
                $data['id_mapel'] = '';
            } else {
                $data['id_guru'] = null;
                $data['id_mapel'] = null;
                $data['id_level'] = null;
            }
            if (count($jadwals) > 0) {
                foreach ($jadwals as $title => $arrjadwal) {
                    foreach ($arrjadwal as $lvl => $sjadwal) {
                        foreach ($sjadwal as $keys=>$jadwal) {
                            $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? '[]');
                        }
                    }
                }
            }
            $data['jadwals'] = $jadwals;
            $this->render_view(1, 'cbt/jadwal/data', $data);
		} else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
			$data['guru'] = $guru;

            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
            $arrMapel = [];
            if ($mapel) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                }
            }
            $data['mapels'] = $arrMapel;

            $data['filters'] = ['0'=>'Semua', '2'=>'Mapel', '3'=>'Level'];
            $data['id_filter'] = $type == null ? '' : $type;
            $jadwals = [];
            if ($type == '0') {
                $jadwals = $this->cbt->getAllDataJadwal($tp->id_tp, $smt->id_smt, $guru->id_guru);
                $data['id_guru'] = null;
                $data['id_mapel'] = null;
                $data['id_level'] = null;
            } elseif ($type == '2') {
                $id_mapel = $this->input->get('id');
                $data['id_mapel'] = $id_mapel;
                $jadwals = $this->cbt->getAllDataJadwal($tp->id_tp, $smt->id_smt, $guru->id_guru, $id_mapel);

                $data['id_guru'] = '';
                $data['id_level'] = '';
            } elseif ($type == '3') {
                $id_level = $this->input->get('id');
                $data['id_level'] = $id_level;
                $jadwals = $this->cbt->getAllDataJadwal($tp->id_tp, $smt->id_smt, $guru->id_guru, null, $id_level);

                $data['id_guru'] = '';
                $data['id_mapel'] = '';
            } else {
                $data['id_guru'] = null;
                $data['id_mapel'] = null;
                $data['id_level'] = null;
            }
            if (count($jadwals) > 0) {
                foreach ($jadwals as $title => $arrjadwal) {
                    foreach ($arrjadwal as $lvl => $sjadwal) {
                        foreach ($sjadwal as $keys=>$jadwal) {
                            $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? '[]');
                        }
                    }
                }
            }
            $data['jadwals'] = $jadwals;
			$this->render_view(2, 'cbt/jadwal/data', $data);
		}
	}

	public function add($id_jadwal) {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Dropdown_model', 'dropdown');

        $enable = $this->input->get('enable', true);
		$user = $this->auth->user();
		$data = [
			'user' => $user,
			'judul'	=> $id_jadwal==0 ? 'Tambah Jadwal Ujian' : 'Edit Jadwal Ujian',
			'subjudul'=> 'Jadwal Ujian',
			'setting'		=> $this->dashboard->getSetting()
		];

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();
		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $tp;
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $smt;

		if ($id_jadwal==0) {
            $jadwal = json_decode(json_encode($this->cbt->dummyJadwal()));
            $jadwal->bank_kelas = [];
        } else {
			$jadwal = $this->cbt->getJadwalById($id_jadwal);
            $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? '[]');
        }
        $data['jadwal'] = $jadwal;
        $gurus = $this->dropdown->getAllGuru();
		$data['ruangs'] = $this->cbt->getAllRuang();
		$data['sesis'] = $this->dropdown->getAllSesi();
		$data['jenis'] = $this->cbt->getAllJenisUjian();
		$data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
		$data['disable_opsi'] = $enable != null && $enable == 1;

		if($this->auth->is_admin()){
            $data['guru'] = $gurus;
            $data['mapel'] = $this->dropdown->getAllMapel();
			$this->render_view(1, 'cbt/jadwal/add', $data);
		}else{
			$guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
            $arrMapel = [];
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
            }
            $data['mapel'] = $arrMapel;
			$this->render_view(2, 'cbt/jadwal/add', $data);
		}
	}

    public function getBankMapel($id_mapel){
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dashboard_model', 'dashboard');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        if($this->auth->is_admin()){
            $banks = $this->cbt->getAllBankSoalByMapel($tp->id_tp, $smt->id_smt, $id_mapel);
        } else {
            $user = $this->auth->user();
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $banks = $this->cbt->getAllBankSoalByMapel($tp->id_tp, $smt->id_smt, $id_mapel, $guru->id_guru);
        }
        $filtered = [];
        $digunakan = [];
        $kelas = [];
        foreach ($banks as $key=>$bank) {
            $cek_soal = $this->cbt->getJumlahJenisSoal($key);
            $num1 = isset($cek_soal['1']) ? count($cek_soal['1']) : 0;
            $num2 = isset($cek_soal['2']) ? count($cek_soal['2']) : 0;
            $num3 = isset($cek_soal['3']) ? count($cek_soal['3']) : 0;
            $num4 = isset($cek_soal['4']) ? count($cek_soal['4']) : 0;
            $num5 = isset($cek_soal['5']) ? count($cek_soal['5']) : 0;
            $ada1 = $num1 == (int) $bank->tampil_pg;
            $ada2 = $num2 == (int) $bank->tampil_kompleks;
            $ada3 = $num3 == (int) $bank->tampil_jodohkan;
            $ada4 = $num4 == (int) $bank->tampil_isian;
            $ada5 = $num5 == (int) $bank->tampil_esai;
            if ($ada1 && $ada2 && $ada3 && $ada4 && $ada5) {
                $kelas[$key] = $this->maybe_unserialize($bank->bank_kelas ?? '[]');
                $filtered[$key] = $bank->bank_kode;
                if ($bank->id_jadwal) {
                    $digunakan[] = $bank->id_bank;
                }
            }
        }
        $all_kelas = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
        $this->output_json(['bank' => $filtered, 'jadwal'=>$digunakan, 'kelas'=>$kelas, 'all_kelas' => $all_kelas]);
        //$this->output_json($this->cbt->getAllBankSoalByMapel($tp->id_tp, $smt->id_smt, $id_mapel));
    }

	public function saveJadwal() {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

		$tp = $this->dashboard->getTahunActive();
		$smt = $this->dashboard->getSemesterActive();

        $id = $this->input->post('id_jadwal', true);
        $res = $this->cbt->saveJadwalUjian($tp->id_tp, $smt->id_smt);

        if (!$res['success']) {
            $data = [
                'success' => false,
                'message' => $res['errors'] ?? 'Jadwal gagal disimpan'
            ];
            $this->output_json($data);
            return;
        }

        if ($this->auth->in_group('guru')) {
            $this->load->model('Kelas_model', 'kelas');

            $user = $this->auth->user();
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $mapel = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel->mapel_kelas = $this->maybe_unserialize($mapel->mapel_kelas ?? '[]');
            $mapel->ekstra_kelas = $this->maybe_unserialize($mapel->ekstra_kelas ?? '[]');

            if ($id == '') {
                $jadwal = $this->cbt->getJadwalById($res['insert_id']);
            } else {
                $jadwal = $this->cbt->getJadwalById($id);
            }
            $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? []);

            $arrKls = array_map('strval', array_column($jadwal->bank_kelas, 'kelas_id'));
            $ruangs = $this->cbt->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);
            $peserta = [];
            foreach ($jadwal->bank_kelas as $kb) {
                if ($kb->kelas_id) {
                    $klss = $this->cbt->getKelasUjian($tp->id_tp, $smt->id_smt, $kb->kelas_id);
                    foreach ($ruangs as $id_ruang=>$ruang) {
                        foreach ($ruang as $id_sesi=>$sesi) {
                            if (isset($klss[$kb->kelas_id][$id_ruang][$id_sesi])) {
                                if (isset($peserta[$id_ruang][$id_sesi])) {
                                    $peserta[$id_ruang][$id_sesi] += count($klss[$kb->kelas_id][$id_ruang][$id_sesi]);
                                } else {
                                    $peserta[$id_ruang][$id_sesi] = count($klss[$kb->kelas_id][$id_ruang][$id_sesi]);
                                }
                            }
                        }
                    }
                }
            }

            $inserted = [];
            foreach ($peserta as $idRuang=>$rng) {
                foreach ($rng as $idSesi=>$ses) {
                    if ($ses > 0) {
                        $id_pengawas = $tp->id_tp.$smt->id_smt.$jadwal->id_jadwal.$idRuang.$idSesi;
                        $exist = $this->cbt->getPengawas($id_pengawas);
                        if ($exist) {
                            $exist_peng = explode(",", $exist->id_guru);
                            if (!in_array($guru->id_guru, $exist_peng)) {
                                $exist_peng[] = $guru->id_guru;
                            }
                            $dataInsert = [
                                'id_pengawas'	=> $exist->id_pengawas,
                                'id_jadwal'	=> $exist->id_jadwal,
                                'id_tp' => $exist->id_tp,
                                'id_smt' => $exist->id_smt,
                                'id_ruang' => $exist->id_ruang,
                                'id_sesi' => $exist->id_sesi,
                                'id_guru' => implode(",", $exist_peng),
                            ];
                        } else {
                            $dataInsert = [
                                'id_pengawas'	=> $id_pengawas,
                                'id_jadwal'	=> $jadwal->id_jadwal,
                                'id_tp' => $tp->id_tp,
                                'id_smt' => $smt->id_smt,
                                'id_ruang' => $idRuang,
                                'id_sesi' => $idSesi,
                                'id_guru' => $guru->id_guru,
                            ];
                        }
                        $inserted[] = $dataInsert;
                        $this->db->replace('cbt_pengawas', $dataInsert);
                    }
                }
            }
            $data['peserta'] = $peserta;
            $data['insert'] = $inserted;
        }

        $status = $res['success'];
        $data['success'] = $status;
        $data['message'] = 'Jadwal berhasil disimpan';

		if (!$id) {
			$this->logging->saveLog(3, 'menambah jadwal pelajaran');
		} else {
			$this->logging->saveLog(4, 'mengedit jadwal pelajaran');
		}

		$this->output_json($data);
	}

    public function aktifkan_jadwal() {
        $this->load->model('Log_model', 'logging');

        $method = $this->input->post('method', true);
        $id = $this->input->post('id_jadwal', true);
        $stat = $method == '1' ? '0' : '1';

        $this->db->set('status', $stat);
        $this->db->where('id_jadwal', $id);
        $this->db->update('cbt_jadwal');

        $this->logging->saveLog($stat == '1' ? $this->LOG_TYPE->ADD : $this->LOG_TYPE->EDIT,
            ($stat == '1' ? 'Mengaktifkan' : 'Menonaktifkan').' jadwal ujian');

        $this->output_json(['status' => true]);
    }

    public function deleteJadwal() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Log_model', 'logging');

		$id = $this->input->get('id_jadwal', true);
        $jadwal_dikerjakan = $this->getJadwalTerpakai();
        $terpakai = isset($jadwal_dikerjakan[$id]) && count($jadwal_dikerjakan[$id]) > 0;
        $rekap_jadwal = $this->db->get_where('cbt_rekap_jadwal', ['id_jadwal'=>$id])->num_rows();
        if ($terpakai && $rekap_jadwal == 0) {
            $data['status'] = false;
            $data['message'] = 'Hasil Ujian belum direkap';
            $this->output_json($data);
            return;
        }

        if ($this->master->delete('cbt_jadwal', $id, 'id_jadwal')) {
            $hapusNilaiSiswa = $this->master->delete('cbt_siswa', $id, 'id_jadwal');
            $hapusPengawas = $this->master->delete('cbt_pengawas', $id, 'id_jadwal');
            $this->logging->saveLog(5, 'menghapus jadwal ujian');
            $data['status'] = $hapusNilaiSiswa && $hapusPengawas;
            $data['message'] = 'berhasil';
        } else {
            $data['status'] = false;
            $data['message'] = 'Jadwal Ujian gagal dihapus';
        }
		$this->output_json($data);
	}

    public function deleteAllJadwal() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Log_model', 'logging');

        $arrId = json_decode($this->input->post('checked', true));
        ob_start();
        $jadwals = $this->cbt->getJadwalByArrId($arrId);
        $jadwal_dikerjakan = $this->getJadwalTerpakai();

        //$cek_rekaps = $this->cekRekapSiswa();
        $this->db->where_in('id_jadwal', $arrId);
        $backuped = $this->db->get_where('cbt_rekap_jadwal')->num_rows();

        $digunakan = [];
        foreach ($jadwals as $jadwal) {
            $terpakai = isset($jadwal_dikerjakan[$jadwal->id_jadwal]) && count($jadwal_dikerjakan[$jadwal->id_jadwal]) > 0 ? 1 : 0;
            $digunakan[] = $terpakai;
        }
        $count_terpakai = array_count_values($digunakan);

        if ($count_terpakai[1] > 0 && $backuped > 0) {
            ob_end_clean();
            $data['status'] = false;
            $data['message'] = 'Ada hasil Ujian yang belum direkap';
        } else {
            if ($this->master->delete('cbt_jadwal', $arrId, 'id_jadwal')) {
                $hapusNilaiSiswa = $this->master->delete('cbt_siswa', $arrId, 'id_jadwal');
                $hapusPengawas = $this->master->delete('cbt_pengawas', $arrId, 'id_jadwal');
                $this->logging->saveLog(5, 'menghapus semua jadwal ujian');

                ob_end_clean();
                $data['status'] = $hapusNilaiSiswa && $hapusPengawas;
                $data['message'] = 'berhasil';
            } else {
                ob_end_clean();
                $data['status'] = false;
                $data['message'] = 'Jadwal Ujian sedang digunakan';
            }
        }
        $data['digunakan'] = $count_terpakai;
        $data['backup'] = $backuped;
        $this->output_json($data);
    }

    public function get_all_norekap()
    {
        $arrId = json_decode($this->input->post('ids_jadwal', true));

        $this->db->select('a.id_rekap_nilai, a.id_jadwal, a.id_tp, a.tp, a.id_smt, a.smt, a.id_jenis, a.kode_jenis,'.
            'a.id_bank, a.id_mapel, a.id_siswa, a.id_kelas, a.kelas, a.mulai, a.selesai, a.durasi, a.bobot_pg,'.
            'a.jawaban_pg, a.nilai_pg, a.bobot_esai, a.jawaban_esai, a.nilai_esai, a.id_guru, a.nama_siswa, a.no_peserta,'.
            'a.soal_kompleks, a.soal_jodohkan, a.soal_isian, a.soal_essai,'.
            'b.siswa_id, b.kelas_id, b.ruang_id, b.sesi_id, b.tp_id, b.smt_id,'.
            'c.*,d.*,g.reset as reset_izin,'.
            'e.id_nilai, e.pg_benar, e.pg_nilai, e.essai_nilai, e.kompleks_nilai, e.jodohkan_nilai, e.isian_nilai,e.dikoreksi,'.
            'f.id_durasi, f.status, f.lama_ujian, f.mulai, f.selesai, f.reset as reset_waktu');
        $this->db->from('cbt_rekap_nilai a');
        $this->db->join('cbt_sesi_siswa b', 'a.id_siswa = b.siswa_id AND a.id_tp = b.tp_id AND a.id_smt = b.smt_id', 'left');
        $this->db->join('cbt_ruang c', 'c.id_ruang=b.ruang_id', 'left');
        $this->db->join('cbt_sesi d', 'd.id_sesi=b.sesi_id', 'left');
        $this->db->join('cbt_nilai e', 'e.id_siswa = a.id_siswa AND a.id_jadwal = e.id_jadwal', 'left');
        $this->db->join('cbt_durasi_siswa f', 'f.id_siswa = a.id_siswa AND a.id_jadwal = f.id_jadwal', 'left');
        $this->db->join('log_ujian g', 'g.id_siswa = a.id_siswa AND a.id_jadwal = g.id_jadwal AND g.log_type=\'1\'', 'left');
        $this->db->where_in('a.id_jadwal', $arrId);
        $result = $this->db->get()->result();
        $arrIdSiswa = [];
        $arrTp = [];
        $arrSmt = [];
        foreach ($result as $row) {
            $arrIdSiswa[] = $row->id_siswa;
            $arrTp[$row->id_siswa] = $row->id_tp;
            $arrSmt[$row->id_siswa] = $row->id_smt;
            $row->jawaban_pg = $this->maybe_unserialize($row->jawaban_pg);
            $row->soal_kompleks = $this->maybe_unserialize($row->soal_kompleks);
            $row->soal_jodohkan = $this->maybe_unserialize($row->soal_jodohkan);
            $row->soal_isian = $this->maybe_unserialize($row->soal_isian);
            $row->soal_essai = $this->maybe_unserialize($row->soal_essai);
        }

        $this->db->select('*');
        $this->db->from('cbt_soal_siswa');
        $this->db->where_in('id_jadwal', $arrId);
        $this->db->where_in('id_siswa', $arrIdSiswa);
        $soalSiswa = $this->db->get()->result();

        $data['success'] = true;
        $data['rekap'] = $result;
        $data['soal_siswa'] = $soalSiswa;
        $this->output_json($data);
    }
}
