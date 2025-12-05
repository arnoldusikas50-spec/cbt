<?php


class Cbtpengawas extends MY_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->auth->logged_in()){
			redirect('auth');
		}else if ( !$this->auth->is_admin() && !$this->auth->in_group('panitia')){
			show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="'.base_url('dashboard').'">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
		}
		$this->load->library(['form_validation']);// Load Library Ignited-Datatables
		$this->form_validation->set_error_delimiters('','');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->model('Cbt_model', 'cbt');
		$this->load->model('Dropdown_model', 'dropdown');
	}

    protected function getDistinctTgl($tp, $smt, $jenis) {
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
        // TEMPORARILY DISABLED TRY-CATCH TO SEE ERROR
        // try {
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

            // Use standard CI query instead of custom model methods
            $this->db->select('a.id_jadwal, a.id_tp, a.id_smt, a.id_bank, a.id_jenis, a.tgl_mulai, a.tgl_selesai, a.status, a.jam_ke');
            $this->db->select('b.bank_kode, b.bank_kelas, b.soal_agama, b.bank_guru_id');
            $this->db->select('c.kode as mapel_kode, c.nama_mapel');
            $this->db->from('cbt_jadwal a');
            $this->db->join('cbt_bank_soal b', 'b.id_bank = a.id_bank', 'left');
            $this->db->join('master_mapel c', 'c.id_mapel = b.bank_mapel_id', 'left');
            $this->db->where('a.id_tp', $tp->id_tp);
            $this->db->where('a.id_smt', $smt->id_smt);
            $this->db->where('a.tgl_mulai', $tanggal_selected);
            $this->db->where('a.id_jenis', $jenis_selected);
            $jadwals_query = $this->db->get();
            $jadwals = $jadwals_query->result();
            
            // Restructure result to match expected format
            if ($jadwals && count($jadwals) > 0) {
                foreach ($jadwals as $jdwl) {
                    // Unserialize bank_kelas
                    $bank_kelas_unserialized = $this->maybe_unserialize($jdwl->bank_kelas ?? '');
                    if (!is_array($bank_kelas_unserialized)) {
                        $bank_kelas_unserialized = [];
                    }
                    
                    // Create bank object
                    $jdwl->bank = (object)[
                        'bank_kode' => $jdwl->bank_kode ?? '',
                        'bank_kelas' => $bank_kelas_unserialized,  // Array, not string!
                        'soal_agama' => $jdwl->soal_agama ?? '',
                        'bank_guru_id' => $jdwl->bank_guru_id ?? '',
                        'mapel' => (object)[
                            'kode' => $jdwl->mapel_kode ?? '',
                            'nama_mapel' => $jdwl->nama_mapel ?? ''
                        ]
                    ];
                    
                    // Clean up duplicate properties
                    unset($jdwl->bank_kode);
                    unset($jdwl->bank_kelas);
                    unset($jdwl->soal_agama);
                    unset($jdwl->bank_guru_id);
                    unset($jdwl->mapel_kode);
                    unset($jdwl->nama_mapel);
                }
            }

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
                $data['arr_kelas'] = [];
                $data['jadwals_ujian'] = [];
                $this->output_json($data);
                return;
            }

            // Minimal safe implementation
            $ids_jadwal = [];
            $arrKelas = [];
            $result = [];

            // Process jadwals safely
            if ($jadwals && is_array($jadwals)) {
                foreach ($jadwals as $jadwal) {
                    if (isset($jadwal->id_jadwal)) {
                        $ids_jadwal[] = $jadwal->id_jadwal;
                    }
                    
                    // Try to get kelas safely
                    if (isset($jadwal->bank)) {
                        $bank_kelas = isset($jadwal->bank->bank_kelas) ? $jadwal->bank->bank_kelas : '';
                        if ($bank_kelas) {
                            $kelas_arr = $this->maybe_unserialize($bank_kelas);
                            if (is_array($kelas_arr)) {
                                foreach ($kelas_arr as $kls) {
                                    if (is_object($kls) && isset($kls->kelas_id)) {
                                        $arrKelas[] = $kls->kelas_id;
                                    }
                                }
                            }
                        }
                    }
                    
                    $result[] = $jadwal;
                }
            }
            
            // Remove duplicates
            $arrKelas = array_unique($arrKelas);
            
            // Get ruang data safely
            $ruangs = [];
            if (count($arrKelas) > 0) {
                try {
                    $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKelas);
                    if (!is_array($ruangs)) {
                        $ruangs = [];
                    }
                } catch (Exception $e) {
                    $ruangs = [];
                }
            }
            
            // Return data
            $data['sesisiswa'] = [];  // Will populate later
            $data['cbtsiswa'] = [];   // Will populate later  
            $data['ruangs'] = array_values($ruangs);
            $data['arr_kelas'] = array_values($arrKelas);
            $data['jadwals_ujian'] = $result;
            
            $this->output_json($data);
            
        /*} catch (Exception $e) {
            $this->output_json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (Error $e) {
            $this->output_json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }*/
    }

    public function save() {
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

    protected function getTpSmt() {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        return (object)[
            'tp_active' => $tp,
            'smt_active' => $smt
        ];
    }
}

