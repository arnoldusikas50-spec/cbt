<?php


class Elearning extends MY_Controller {

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

    private function getKelasByName($nama, $tp, $smt)
    {
        $this->db->select('id_kelas, nama_kelas');
        $this->db->where('nama_kelas', $nama);
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        return $this->db->get('master_kelas')->row();
    }

    private function getKelasByArrName($arr_nama, $tp, $smt)
    {
        $this->db->select('id_kelas, nama_kelas, kode_kelas');
        $this->db->where_in('nama_kelas', $arr_nama);
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        return $this->db->get('master_kelas')->result();
    }

    private function getKelasById($id, $tp, $smt)
    {
        $this->db->select('id_kelas, nama_kelas');
        $this->db->where('id_kelas', $id);
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        return $this->db->get('master_kelas')->row();
    }

    private function getKelasByIds($ids)
    {
        $this->db->select('id_kelas, nama_kelas, kode_kelas');
        $this->db->where_in('id_kelas', $ids);
        return $this->db->get('master_kelas')->result();
    }

    public function index()
    {
        redirect('elearning/jadwal');
    }

	public function jadwal() {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

		$user = $this->auth->user();
		$data = [
			'user' => $user,
            'judul' => 'Jadwal Pelajaran',
            'subjudul' => 'Set Jadwal Pelajaran',
            'setting'		=> $this->dashboard->getSetting()
		];
        $data['uptodate'] = $isUpdated;

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $data['method'] = '';
        $data['id_hari'] = '-1';
        $jadk = $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt);
        if (empty($jadk)) {
            $data['setting_kbm'] = (object)[
                'libur'=> '7',
                'istirahat' => [],
                'ada'=>false
            ];
        } else {
            $exist = array_key_first($jadk);
            $jadwal = $jadk[$exist];
            $jadwal->istirahat = $this->maybe_unserialize($jadwal->istirahat ?? '[]');
            $jadwal->upacara = $this->maybe_unserialize($jadwal->upacara ?? '{}');
            $data['setting_kbm'] = $jadwal;
        }
        if($this->auth->is_admin()) {
            $all_kelas = $this->elearning->getAllKelasSort($tp->id_tp, $smt->id_smt);
            $data['kelas']	= $all_kelas;
            $data['is_admin'] = 1;
            $this->render_view(1, 'elearning/jadwalkelas', $data);
        }else{
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['is_admin'] = 0;

            $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $data['mapel_guru'] = $mapel;
            $arrMapel = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    foreach ($m->kelas_mapel as $item) {
                        $arrMapel[$item->kelas][$m->id_mapel] = $m->nama_mapel;
                    }
                }
            }

            $arrId = [];
            if ($mapel != null) {
                foreach ($mapel as $mpl_kls) {
                    foreach ($mpl_kls->kelas_mapel as $id_mapel) {
                        $arrId[] = $id_mapel->kelas ?? '';
                    }
                }
            }

            $data['kelas'] = count($arrId)>0 ? $this->elearning->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];
            $data['mapels'] = $arrMapel;

            $this->render_view(2, 'elearning/jadwalkelas', $data);
        }
	}

    public function hari($hari)
    {
        // Validasi parameter hari
        if (!is_numeric($hari) || $hari < 0 || $hari > 6) {
            $this->output_json(['error' => 'Parameter hari tidak valid']);
            return;
        }

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model', 'elearning');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $data = [
            'tp' => $tp_smt->tp,
            'smt' => $tp_smt->smt,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'is_admin' => $this->auth->is_admin(),
            'id_hari' => $hari
        ];

        $all_kelas = $this->elearning->getAllKelasSort($tp->id_tp, $smt->id_smt);
        $jadk = $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt);
        $jadwal_mapel = $this->elearning->getJadwalHarian($tp->id_tp, $smt->id_smt, $hari);

        // Dummy jadwal jika kosong
        if (empty($jadwal_mapel)) {
            foreach ($all_kelas as $keyKls => $kelas) {
                for ($i = 0; $i < 6; $i++) {
                    for ($j = 1; $j < 7; $j++) {
                        $jadwal_mapel[$keyKls][] = [
                            'dari' => '',
                            'sampai' => '',
                            'rows' => '1',
                            'id_kelas' => $keyKls,
                            'id_hari' => $i,
                            'id_mapel' => '',
                            'kode' => ''
                        ];
                    }
                }
            }
        }

        // Inisialisasi waktu
        $minTime = "23:59";
        $maxTime = "06:00";
        $libur = '7';
        $kelasKeys = array_keys($all_kelas);

        foreach ($jadk as $idKelas => $kelas) {
            if (!in_array($idKelas, $kelasKeys)) continue;

            if ($kelas->kbm_jam_selesai > $maxTime) {
                $maxTime = $kelas->kbm_jam_selesai;
            }
            if ($kelas->kbm_jam_mulai < $minTime) {
                $minTime = $kelas->kbm_jam_mulai;
            }

            $details = [];
            $mapel = $jadwal_mapel[$idKelas] ?? [];
            $lastDari = '00:00';
            $lastSampai = '01:00';
            $latestMpl = '0';
            $lastDay = '';

            foreach ($mapel as $jadwal) {
                $dari = $jadwal->dari ?? $lastSampai;
                $sampai = $jadwal->sampai ?? $lastSampai;
                $id_mapel = $jadwal->id_mapel ?? '0';
                $id_hari = $jadwal->id_hari ?? '0';

                $durasiMapel = round(abs(strtotime($sampai) - strtotime($dari)) / 60, 2);
                $rows = isset($jadwal->dari) ? ($durasiMapel / 5) : 1;

                if ($latestMpl !== '0' && $jadwal->id_mapel == $latestMpl && $jadwal->id_hari == $lastDay && $jadwal->dari == $lastSampai) {
                    if (!isset($details[$lastDari]['rows'])) {
                        $details[$lastDari]['rows'] = 0;
                    }
                    $details[$lastDari]['rows'] += $rows;
                    $details[$lastDari]['sampai'] = $sampai;
                } else {
                    $lastDay = $id_hari;
                    $latestMpl = $id_mapel;
                    $details[$dari] = [
                        'id_jadwal' => $jadwal->id_jadwal ?? '0',
                        'dari' => $dari,
                        'sampai' => $sampai,
                        'rows' => $rows,
                        'id_kelas' => $jadwal->id_kelas ?? '',
                        'id_hari' => $id_hari,
                        'id_mapel' => $id_mapel,
                        'kode' => $jadwal->kode ?? ''
                    ];
                    $lastDari = $dari;
                }

                $lastSampai = $sampai;
            }

            unset($kelas->istirahat);
            $kelas->detail = $details;
            $libur = $kelas->libur ?? $libur;
        }

        // Buat timeline 5 menit jika jadwal kosong
        if ($minTime == "23:59") {
            $minTime = "07:00";
            $maxTime = "08:00";
        }

        $longDay = round(abs(strtotime($maxTime) - strtotime($minTime)) / 60, 2) + 5;
        $startTime = new DateTime($minTime);
        $timeSlots = [];

        for ($m = 0; $m < ($longDay / 5); $m++) {
            try {
                $formattedStart = $startTime->format('H:i');

                if (strpos($formattedStart, '00') == 3 || strpos($formattedStart, '30') == 3) {
                    $lastIndex = max(0, $m - 6);
                    $currentDiff = isset($timeSlots[$lastIndex]['start'])
                        ? round(abs(strtotime($timeSlots[$lastIndex]['start']) - strtotime($formattedStart)) / 60, 2)
                        : 0;

                    if ($currentDiff <= 30 && isset($timeSlots[$lastIndex]['start'])) {
                        $timeSlots[$lastIndex]['span'] = ($currentDiff / 5) * 2;
                    }
                }

                $timeSlots[] = ['start' => $formattedStart];
                $startTime->add(new DateInterval('PT5M'));
            } catch (Exception $e) {}
        }

        $data['times'] = $timeSlots;
        $data['longdays'] = $longDay;
        $data['jadwal_kbm'] = count($jadk) > 0 ? $jadk : [];
        $data['setting_kbm'] = ['mulai' => $minTime, 'selesai' => $maxTime, 'libur' => $libur];

        if ($data['is_admin']) {
            $data['mapels'] = $this->elearning->getAllKodeMapel();
            $data['kelas'] = $all_kelas;
        } else {
            $user = $this->auth->user();
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $mapel_guru ? $this->maybe_unserialize($mapel_guru->mapel_kelas) : [];

            $arrMapel = [];
            $arrId = [];

            if ($mapel != null) {
                foreach ($mapel as $m) {
                    foreach ($m->kelas_mapel as $item) {
                        $arrMapel[$item->kelas][$m->id_mapel] = $m->nama_mapel;
                    }
                }

                foreach ($mapel as $mpl_kls) {
                    foreach ($mpl_kls->kelas_mapel as $id_mapel) {
                        $arrId[] = $id_mapel->kelas ?? '';
                    }
                }
            }

            $data['kelas'] = count($arrId) > 0
                ? $this->elearning->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId)
                : [];

            $data['mapels'] = $arrMapel;
            $data['mapel_guru'] = $mapel;
        }

        $this->output_json($data);
    }

    /**
     * Menggenerasi jadwal kelas dengan konfigurasi seting jadwal.
     * @return array Array jadwal untuk operasi update/insert.
     */
    private function generate_class_schedule($schedule_config, $tp, $smt)
    {
        $this->db->select('id_jadwal,id_tp,id_smt,id_kelas,id_hari,jam_ke,dari,sampai,id_mapel');
        $this->db->from('kelas_jadwal_mapel');
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->where_in('id_kelas', $schedule_config['id_kelas']);
        $initial_schedule_data = $this->db->get()->result_array();

        $data_to_update = []; // Untuk data jadwal yang sudah ada (punya id_jadwal)
        $data_to_insert = []; // Untuk data jadwal baru (belum punya id_jadwal)

        $kbm_jam_mulai = $schedule_config['kbm_jam_mulai'];
        $kbm_jam_pel = $schedule_config['kbm_jam_pel'];
        $kbm_jml_mapel_hari = $schedule_config['kbm_jml_mapel_hari'];

        $upacara_config = $schedule_config['upacara'] ?? null;
        $istirahat_config = $schedule_config['istirahat'] ?? [];

        // Ambil ID kelas dari konfigurasi. Jika hanya satu, tetap jadikan array.
        $class_ids_to_process = is_array($schedule_config['id_kelas']) ? $schedule_config['id_kelas'] : [(string)$schedule_config['id_kelas']];

        // Siapkan data jadwal yang ada agar mudah dicari (dikelompokkan per kelas, per hari, lalu per jam_ke)
        $existing_by_class_day = [];
        foreach ($initial_schedule_data as $item) {
            $existing_by_class_day[$item['id_kelas']][$item['id_hari']][$item['jam_ke']] = $item;
        }

        // --- Loop utama untuk setiap kelas yang akan diproses ---
        foreach ($class_ids_to_process as $class_id) {
            // Loop untuk setiap hari dalam seminggu (Senin sampai Sabtu)
            for ($day_id = 1; $day_id <= 7; $day_id++) {
                // Inisialisasi waktu mulai untuk hari ini
                $current_time = DateTime::createFromFormat('H:i', $kbm_jam_mulai);

                $current_mapel_queue = []; // Antrean untuk id_mapel reguler yang perlu ditempatkan

                // 1. Kumpulkan semua id_mapel reguler dari jadwal awal hari ini untuk kelas ini
                if (isset($existing_by_class_day[$class_id][$day_id])) {
                    foreach ($existing_by_class_day[$class_id][$day_id] as $jam_ke => $item) {
                        if ($item['id_mapel'] != '-1' && $item['id_mapel'] != '-2' && $item['id_mapel'] != '0') {
                            $current_mapel_queue[] = [
                                'id_mapel' => $item['id_mapel'],
                                'id_jadwal' => $item['id_jadwal'] // Pertahankan id_jadwal asli
                            ];
                        }
                    }
                }

                // 2. Generasi slot jadwal untuk hari ini, dari jam ke-1 hingga kbm_jml_mapel_hari
                for ($jam_ke = 1; $jam_ke <= $kbm_jml_mapel_hari; $jam_ke++) {
                    $slot_data = [
                        'id_tp' => $tp,
                        'id_smt' => $smt,
                        'id_hari' => (string)$day_id,
                        'id_kelas' => (string)$class_id,
                        'jam_ke' => (string)$jam_ke,
                        'dari' => $current_time->format('H:i'),
                        'sampai' => '',
                        'id_mapel' => '',
                    ];

                    $is_special_slot_filled = false;

                    // Cek Upacara (hanya pada hari dan jam_ke yang ditentukan)
                    if ($upacara_config && $day_id == $upacara_config['id_hari'] && $jam_ke == $upacara_config['jam_ke']) {
                        $duration_minutes = (int)$upacara_config['durasi'];
                        $current_time->modify("+$duration_minutes minutes");
                        $slot_data['sampai'] = $current_time->format('H:i');
                        $slot_data['id_mapel'] = '-2';
                        $is_special_slot_filled = true;
                    }

                    // Cek Istirahat (jika slot belum diisi upacara)
                    if (!$is_special_slot_filled) {
                        foreach ($istirahat_config as $istirahat_item) {
                            if ($jam_ke == $istirahat_item['ist']) {
                                $duration_minutes = (int)$istirahat_item['dur'];
                                $current_time->modify("+$duration_minutes minutes");
                                $slot_data['sampai'] = $current_time->format('H:i');
                                $slot_data['id_mapel'] = '-1';
                                $is_special_slot_filled = true;
                                break;
                            }
                        }
                    }

                    // Tugaskan Mata Pelajaran Reguler atau Slot Kosong (jika belum diisi spesial)
                    if (!$is_special_slot_filled) {
                        $duration_minutes = (int)$kbm_jam_pel;
                        $current_time->modify("+$duration_minutes minutes");
                        $slot_data['sampai'] = $current_time->format('H:i');

                        if (!empty($current_mapel_queue)) {
                            $next_mapel = array_shift($current_mapel_queue);
                            $slot_data['id_mapel'] = $next_mapel['id_mapel'];
                            if (isset($next_mapel['id_jadwal'])) {
                                $slot_data['id_jadwal'] = $next_mapel['id_jadwal'];
                            }
                        } else {
                            $slot_data['id_mapel'] = '0';
                        }
                    }

                    // Pertahankan id_jadwal untuk istirahat yang posisinya tetap
                    if ($slot_data['id_mapel'] == '-1' && isset($existing_by_class_day[$class_id][$day_id][$jam_ke]) && $existing_by_class_day[$class_id][$day_id][$jam_ke]['id_mapel'] == '-1') {
                        $slot_data['id_jadwal'] = $existing_by_class_day[$class_id][$day_id][$jam_ke]['id_jadwal'];
                    }

                    // --- Pemisahan data untuk UPDATE atau INSERT ---
                    if (isset($slot_data['id_jadwal'])) {
                        $data_to_update[] = $slot_data;
                    } else {
                        $data_to_insert[] = $slot_data;
                    }
                }
            }
        }

        $current_ids_in_generated_output = array_column($data_to_update, 'id_jadwal');
        $existing_id_map = array_column($initial_schedule_data, 'id_jadwal');
        $ids_to_delete = array_diff($existing_id_map, $current_ids_in_generated_output);
        return [
            'delete'  => array_values($ids_to_delete),
            'update' => $data_to_update,
            'insert' => $data_to_insert
        ];
    }

    public function setJadwal() {
        $this->load->model('Elearning_model',  'elearning');
        $this->load->model('Log_model', 'logging');

        $mulai = $this->input->post('jam_mulai', true);
        $selesai = $this->input->post('jam_selesai', true);
        $libur = $this->input->post('hari_libur', true);

        $hari_upacara = $this->input->post('hari_upacara', true);
        $jam_upacara = $this->input->post('jam_upacara', true);
        $durasi_upacara = $this->input->post('durasi_upacara', true);
        $upacara = [
            'id_hari' => $hari_upacara,
            'jam_ke' => $jam_upacara,
            'durasi' => $durasi_upacara
        ];

        $tp = $this->input->post('id_tp', true);
        $smt = $this->input->post('id_smt', true);

        $formatted = $this->input->post('format', true);
        $jam_mapel = $this->input->post('jam_mapel', true);
        $jml_mapel = $this->input->post('jml_mapel', true);
        $istirahat = [];
        for ($i = 0; $i < 5; $i++) {
            $jamke = $this->input->post('ist' . $i, true);
            $durasi = $this->input->post('dur_ist' . $i, true);
            if ($jamke) {
                $istirahat[] = [
                    'ist' => $jamke,
                    'dur' => $durasi
                ];
            }
        }

        $jadk = $this->elearning->getJadwalKbm($tp, $smt); // array id_kelas
        $all_kelas = $this->elearning->getAllKelas($tp, $smt);

        $update = 1;
        $insert = 1;
        $insertKbm = [];
        $updateKbm = [];
        if ($jadk) {
            foreach ($all_kelas as $id_kelas=>$kelas) {
                $ids_kelas = array_keys($jadk);
                if (in_array($id_kelas, $ids_kelas)) {
                    $jad_kelas = $jadk[$id_kelas];
                    $jad_kelas->kbm_jam_mulai = $mulai;
                    $jad_kelas->kbm_jam_selesai = $selesai;
                    $jad_kelas->libur = $libur;
                    $jad_kelas->upacara = json_encode($upacara);
                    if ($formatted) {
                        $jad_kelas->kbm_jam_pel = $jam_mapel;
                        $jad_kelas->kbm_jml_mapel_hari = $jml_mapel;
                        $jad_kelas->istirahat = json_encode($istirahat);
                    }
                    $updateKbm[$id_kelas] = $jad_kelas;
                } else {
                    $jadwal = [
                        'id_tp'					=> $tp,
                        'id_smt'				=> $smt,
                        'id_kelas' 				=> $id_kelas,
                        'kbm_jam_mulai' 		=> $mulai,
                        'kbm_jam_selesai' 		=> $selesai,
                        'upacara'               => json_encode($upacara),
                    ];

                    if ($formatted) {
                        $jadwal['kbm_jam_pel'] =  $jam_mapel;
                        $jadwal['kbm_jml_mapel_hari'] = $jml_mapel;
                        $jadwal['istirahat'] = json_encode($istirahat);
                    }
                    $insertKbm[$id_kelas] = $jadwal;
                }
            }
        } else {
            foreach ($all_kelas as $idKls=>$kelas) {
                $jadwal = [
                    'id_tp'					=> $tp,
                    'id_smt'				=> $smt,
                    'id_kelas' 				=> $idKls,
                    'kbm_jam_mulai' 		=> $mulai,
                    'kbm_jam_selesai' 		=> $selesai,
                    'upacara'               => json_encode($upacara)
                ];

                if ($formatted) {
                    $jadwal['kbm_jam_pel'] =  $jam_mapel;
                    $jadwal['kbm_jml_mapel_hari'] = $jml_mapel;
                    $jadwal['istirahat'] = json_encode($istirahat);
                }
                $insertKbm[] = $jadwal;
            }
        }
        if (!empty($insertKbm)) {
            $insert = $this->db->insert_batch('kelas_jadwal_kbm', $insertKbm);
        }
        if (!empty($updateKbm)) {
            $update = $this->db->update_batch('kelas_jadwal_kbm', $updateKbm, 'id_kbm');
        }
        $data['data_insert'] = $insertKbm;
        $data['data_update'] = $updateKbm;

        //$jadwal_mapel = [];
        if ($formatted) {
            $jadwal_config = [
                'id_kelas' => array_keys($all_kelas),
                'kbm_jam_mulai' => $mulai,
                'kbm_jam_selesai' => $selesai,
                'upacara' => $upacara,
                'kbm_jam_pel' => $jam_mapel,
                'kbm_jml_mapel_hari' => $jml_mapel,
                'istirahat' => $istirahat,
                //'mapel' => []
            ];
            $result = $this->generate_class_schedule($jadwal_config, $tp, $smt);

            if (!empty($result['delete'])) {
                $this->db->where_in('id_jadwal', $result['delete'])->delete('kelas_jadwal_mapel');
            }
            if (!empty($result['update'])) {
                $this->db->update_batch('kelas_jadwal_mapel', $result['update'], 'id_jadwal');
            }
            if (!empty($result['insert'])) {
                $this->db->insert_batch('kelas_jadwal_mapel', $result['insert']);
            }
        }
        if ($insert || $update) {
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Membuat Jadwal Pelajaran');
        }
        $data['jadwal_kelas'] = $result ?? [];
        $data['status'] = $insert || $update;
        $this->output_json($data);
    }

    public function delJadwal()
    {
        $type = $this->input->post('type', true);
        $all = $this->input->post('all', true);
        $id_hari = $this->input->post('id_hari', true);
        $tp = $this->input->post('id_tp', true);
        $smt = $this->input->post('id_smt', true);

        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        if ($type == 'r1') {
            $this->db->where('id_hari', $id_hari);
        }
        if (!$all) {
            $this->db->where('id_mapel', '0');
        }
        $deleted_mapel = $this->db->delete('kelas_jadwal_mapel');

        $data['status'] = $deleted_mapel;
        $this->output_json($data);
    }

    public function reformat_jadwal()
    {
        $tp = $this->input->post('id_tp', true);
        $smt = $this->input->post('id_smt', true);

        $jadwal_kbm = $this->db->select('id_kbm,kbm_jam_mulai,kbm_jam_selesai')
            ->where(['id_tp'=>$tp, 'id_smt'=>$smt])
            ->get('kelas_jadwal_kbm')->result();

        $jadwal_mapel = $this->db->select('id_jadwal,dari,sampai')
            ->where(['id_tp'=>$tp, 'id_smt'=>$smt])
            ->get('kelas_jadwal_mapel')->result();

        $minTime = "23:59";
        $maxTime = "06:00";
        if (count($jadwal_mapel) > 0) {
            foreach ($jadwal_mapel as $mapel) {
                if ($mapel->sampai > $maxTime) {
                    $maxTime = $mapel->sampai;
                }
                if ($mapel->dari < $minTime) {
                    $minTime = $mapel->dari;
                }
            }

            foreach ($jadwal_kbm as $kbm) {
                $kbm->kbm_jam_mulai = $minTime;
                $kbm->kbm_jam_selesai = $maxTime;
            }

            if ($minTime != "23:59") {
                $this->db->update_batch('kelas_jadwal_kbm', $jadwal_kbm, 'id_kbm');
            }
        }

        $data['max_time'] = $maxTime;
        $data['min_time'] = $minTime;
        $data['kbm'] = $jadwal_kbm;
        $data['mapel'] = $jadwal_mapel;
        $data['status'] = true;
        $this->output_json($data);
    }

    public function copy_jadwal()
    {
        $this->load->model('Elearning_model',  'elearning');
        $this->load->model('Dropdown_model', 'dropdown');
        $tp = $this->input->post('tp', true);
        $smt = $this->input->post('smt', true);
        $thnLalu = $this->input->post('thnLalu', true);
        $smtLalu = $this->input->post('smtLalu', true);

        $thn_lalu = $this->db->select('id_tp,tahun')
            ->get_where('master_tp', 'tahun='.'"' . $thnLalu .'"')->row();

        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->delete('kelas_jadwal_kbm');

        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $this->db->delete('kelas_jadwal_mapel');

        $jadwal_kbm = $this->elearning->getAllJadwalKbm($thn_lalu->id_tp, $smtLalu);
        $kelas_now = $this->dropdown->getAllKodeKelas($tp, $smt);
        $flip_now = [];
        foreach ($kelas_now as $ids=>$kode_now) {
            $flip_now[$this->normalize($kode_now)] = $ids;
        }
        $kbm_now = $this->elearning->getAllJadwalKbm($tp, $smt);
        $map_now = [];
        foreach ($kbm_now as $know) {
            $map_now[$know->id_kelas] = $know;
        }

        if (count($jadwal_kbm) > 0) {
            $insert_kbm = [];
            $update_kbm = [];
            foreach ($jadwal_kbm as $key=>$kbm) {
                $kode_kelas = $this->normalize($kbm->kode_kelas);
                $kelasSkrg = $flip_now[$kode_kelas] ?? null;
                if ($kelasSkrg) {
                    $new_kbm = [
                        'id_tp' => $tp,
                        'id_smt' => $smt,
                        'id_kelas' => $kelasSkrg,
                        'kbm_jam_pel' => $kbm->kbm_jam_pel,
                        'kbm_jam_mulai' => $kbm->kbm_jam_mulai,
                        'kbm_jam_selesai' => $kbm->kbm_jam_selesai,
                        'kbm_jml_mapel_hari' => $kbm->kbm_jml_mapel_hari,
                        'istirahat' => $kbm->istirahat,
                        'libur' => $kbm->libur
                    ];

                    $kbm_skrg = $map_now[$kelasSkrg] ?? null;
                    if ($kbm_skrg) {
                        $new_kbm['id_kbm'] = $kbm_skrg->id_kbm;
                        $update_kbm[] = $new_kbm;
                    } else {
                        $insert_kbm[] = $new_kbm;
                    }
                }
            }

            if (count($insert_kbm)>0) {
                $this->db->insert_batch('kelas_jadwal_kbm', $insert_kbm);
            }

            if (count($update_kbm)>0) {
                $this->db->update_batch('kelas_jadwal_kbm', $update_kbm, 'id_kbm');
            }
        }

        $jadwal_mapel = $this->elearning->getAllJadwalHarian($thn_lalu->id_tp, $smtLalu);
        $jadwal_now = $this->elearning->getAllJadwalHarian($tp, $smt);
        $mapel_now = [];
        foreach ($jadwal_now as $jnow) {
            $key = $jnow->id_kelas.$jnow->id_mapel.$jnow->id_hari;
            $mapel_now[$key] = $jnow;
        }

        if (count($jadwal_mapel) >0) {
            $insert_mapel = [];
            $update_mapel = [];
            foreach ($jadwal_mapel as $mapel) {
                $kode_kelas = $this->normalize($mapel->kode_kelas);
                $kelasSkrg = $flip_now[$kode_kelas] ?? null;
                if ($kelasSkrg) {
                    $new_jadwal = [
                        'id_tp' => $tp,
                        'id_smt' => $smt,
                        'id_kelas' => $kelasSkrg,
                        'id_hari' => $mapel->id_hari,
                        'jam_ke' => $mapel->jam_ke,
                        'id_mapel' => $mapel->id_mapel,
                        'dari' => $mapel->dari,
                        'sampai' => $mapel->sampai
                    ];

                    $key = $kelasSkrg.$mapel->id_mapel.$mapel->id_hari;
                    $mpl_skrg = $mapel_now[$key] ?? null;
                    if ($mpl_skrg) {
                        $new_jadwal['id_jadwal'] = $mpl_skrg->id_jadwal;
                        $update_mapel[] = $new_jadwal;
                    } else {
                        $insert_mapel[] = $new_jadwal;
                    }
                }
            }
            if (count($insert_mapel)>0) {
                $this->db->insert_batch('kelas_jadwal_mapel', $insert_mapel);
            }
            if (count($update_mapel)>0) {
                $this->db->update_batch('kelas_jadwal_mapel', $update_mapel, 'id_jadwal');
            }
        }

        $data['status'] = true;
        $this->output_json($data);
    }

    public function setMapel()
    {
        $id_tp = $this->input->post('tp', true);
        $id_smt = $this->input->post('smt', true);
        $id_kelas = $this->input->post('kelas', true);
        $id_hari = $this->input->post('hari', true);
        $id_jadwal = $this->input->post('jadwal', true);
        $id_mapel = $this->input->post('mapel', true);
        $dari = $this->input->post('dari', true);
        $sampai = $this->input->post('sampai', true);

        $kbm_id = $this->input->post('id_kbm', true);
        $kbm_mulai = $this->input->post('kbm_mulai', true);
        $kbm_selesai = $this->input->post('kbm_selesai', true);
        $kbm_libur = $this->input->post('kbm_libur', true);

        $insertMapel = [
            'id_tp'		=> $id_tp,
            'id_smt'	=> $id_smt,
            'id_kelas'	=> $id_kelas,
            'id_hari'	=> $id_hari,
            'id_mapel'	=> $id_mapel,
            'dari'      => $dari,
            'sampai'    => $sampai
        ];

        $insertKbm = [
            "id_tp" => $id_tp,
            "id_smt" => $id_smt,
            'id_kelas' => $id_kelas,
            "kbm_jam_mulai" => $kbm_mulai,
            "kbm_jam_selesai" => $kbm_selesai,
            "libur" => $kbm_libur
        ];

        if ($kbm_id) {
            $this->db->where('id_kbm', $kbm_id);
            $this->db->update('kelas_jadwal_kbm', $insertKbm);
        } else {
            $this->db->insert('kelas_jadwal_kbm', $insertKbm);
        }

        if ($id_jadwal) {
            $this->db->where('id_jadwal', $id_jadwal);
            $update = $this->db->update('kelas_jadwal_mapel', $insertMapel);
        } else {
            $update = $this->db->insert('kelas_jadwal_mapel', $insertMapel);
        }

        $data['status'] = $update;
        $data['mulai'] = $kbm_mulai;
        $data['selesai'] = $kbm_selesai;
        $data['libur'] = $kbm_libur;
        $data['data'] = $insertMapel;
        $this->output_json($data);
    }

    public function delMapel($id_jadwal)
    {
        $this->db->where('id_jadwal', $id_jadwal);
        $delete = $this->db->delete('kelas_jadwal_mapel');
        $data['status'] = $delete;
        $this->output_json($data);
    }

    public function materi()
    {
        $this->materiTugas('1');
    }

    public function tugas()
    {
        $this->materiTugas('2');
    }

    private function materiTugas($jenis) {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $this->load->model('Master_model', 'master');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $title = $jenis == '1' ? 'Materi Belajar' : 'Tugas Belajar';
        $subtitle = $jenis == '1' ? 'Materi' : 'Tugas';
        $data = [
            'user' => $user,
            'judul' => $title,
            'subjudul' => $subtitle,
            'setting'		=> $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['is_admin'] = $this->auth->is_admin();

        $data['uptodate'] = $isUpdated;
        if (!$isUpdated->is_latest) {
            if ($this->auth->is_admin()) {
                $this->load->view('init.php');
            } else {
                $this->load->view('maintenance.php');
            }
        } else {
            $jadk = $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt);
            if (empty($jadk)) {
                $data['setting_kbm'] = [
                    'libur'=> '7',
                    'istirahat' => [],
                    'ada'=>false
                ];
            } else {
                $exist = array_key_first($jadk);
                $jadwal = $jadk[$exist];
                $jadwal->istirahat = $this->maybe_unserialize($jadwal->istirahat ?? '[]');
                $jadwal->upacara = $this->maybe_unserialize($jadwal->upacara ?? '{}');
                $data['setting_kbm'] = $jadwal;
            }

            $data['jurusan'] = $this->elearning->getAllJurusan();
            $data['level'] = $this->elearning->getAllLevel($setting->jenjang);

            $arr_kelas = $this->elearning->getAllKelas();
            $data['kelas'] = $arr_kelas;
            $data['jenis'] = $jenis;

            $jadmpl = $this->elearning->getJadwalMapel($tp->id_tp, $smt->id_smt);
            $latestMpl = '0';
            $lastInd = -1;
            $lastDay = '';
            foreach ($jadmpl as $ind => $jad) {
                if ($latestMpl !== '0' && $jad->id_mapel == $latestMpl && $jad->id_hari == $lastDay) {
                    $jadmpl[$lastInd]->sampai = $jad->sampai;
                    unset($jadmpl[$ind]);
                } else {
                    $lastInd = $ind;
                    $lastDay = $jad->id_hari;
                    $latestMpl = $jad->id_mapel ?? '0';
                }
            }
            $data['jadwal_mapel'] = array_values($jadmpl);

            if ($this->auth->is_admin()) {
                $id_guru = $this->input->get('id');
                $allGuru = $this->elearning->getAllGuru();
                $this->array_splice_assoc( $allGuru, 1, 0, ['00' => 'Semua Guru']);
                $data['gurus'] = $allGuru;
                $data['id_guru'] = $id_guru == null ? '' : $id_guru;

                $materi = [];
                $kelas_materi = [];
                $jadwal_materi = [];
                $ids_guru = [];
                if ($id_guru != null) {
                    $materi = $this->elearning->getAllMateriKelas($id_guru, $jenis);
                    foreach ($materi as $key=>$m) {
                        $mapel_guru = $this->elearning->getGuruMapelKelas($m->id_guru, $tp->id_tp, $smt->id_smt);
                        if ($mapel_guru != null && $mapel_guru->mapel_kelas != null && $mapel_guru->ekstra_kelas != null) {
                            $arrKls = $this->maybe_unserialize($m->materi_kelas);
                            if (count($arrKls) > 0) {
                                $km = $this->elearning->getNamaKelasById($this->maybe_unserialize($m->materi_kelas));
                                if ($km == null) {
                                    $km = $this->elearning->getNamaKelasByKode($this->maybe_unserialize($m->materi_kelas));
                                }
                                $kelas_materi[$m->id_materi] = $km;
                                $jadwal_materi[$m->id_materi] = $this->elearning->getJadwalByMateri($m->id_materi, $jenis, $tp->id_tp, $smt->id_smt);
                            }
                            $m->materi_kelas = $arrKls;
                        } else {
                            if (in_array($m->id_guru, $ids_guru)) {
                                $ids_guru[] = $m->id_guru;
                            }
                            unset($materi[$key]);
                        }
                    }
                }
                $data['no_mapel_guru'] = count($ids_guru);
                $data['materi'] = $materi;
                $data['kelas_materi'] = $kelas_materi;
                $data['jadwal_materi'] = $jadwal_materi;

                $this->render_view(1, 'elearning/materi', $data);
            } else {
                $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
                $materi = $this->elearning->getAllMateriKelas($guru->id_guru, $jenis);
                $kelas_materi = [];
                $jadwal_materi = [];
                foreach ($materi as $m) {
                    $m->materi_kelas = $this->maybe_unserialize($m->materi_kelas);
                    $kelas_materi[$m->id_materi] = $this->elearning->getNamaKelasById($m->materi_kelas);
                    $jadwal_materi[$m->id_materi] = $this->elearning->getJadwalByMateri($m->id_materi, $jenis, $tp->id_tp, $smt->id_smt);
                }

                $nguru[$guru->id_guru] = $guru->nama_guru;
                $data['gurus'] = $nguru;
                $data['guru'] = $guru;
                $data['no_mapel_guru'] = $mapel_guru != null && $mapel_guru->mapel_kelas != null && $mapel_guru->ekstra_kelas != null ? 0 : 1;
                $data['id_guru'] = $guru->id_guru;
                $data['materi'] = $materi;
                $data['kelas_materi'] = $kelas_materi;
                $data['jadwal_materi'] = $jadwal_materi;

                $this->render_view(2, 'elearning/materi', $data);
            }
        }
    }

    function array_splice_assoc(&$input, $offset, $length = 0, $replacement = array()) {
        $keys = array_keys($input);
        $values = array_values($input);
        array_splice($keys, $offset, $length, array_keys($replacement));
        array_splice($values, $offset, $length, array_values($replacement));
        $input = array_combine($keys, $values);
    }

    public function saveJadwalMateri() {
        $this->load->model('Log_model', 'logging');

        $id_materi = $this->input->post('id_materi',true);
        $id_mapel = $this->input->post('id_mapel',true);
        $id_kelas = $this->input->post('id_kelas',true);
        $jenis = $this->input->post('jenis',true);
        $jadwal = $this->input->post('jadwal_materi', true);

        $tp = $this->input->post('id_tp', true);
        $smt = $this->input->post('id_smt', true);

        $check = $this->db->get_where('kelas_jadwal_materi', [
            'id_tp' => $tp,
            'id_smt' => $smt,
            'id_kelas' => $id_kelas,
            'id_materi' => $id_materi,
            'id_mapel' => $id_mapel,
            'jadwal_materi' => $jadwal,
            'jenis' => $jenis])->row();

        if ($check) {
            $update = false;
            $msg = 'Jadwal tanggal '.$jadwal.' sudah ada';
        } else {
            $insert = [
                'id_tp' => $tp,
                'id_smt' => $smt,
                'id_kelas' => $id_kelas,
                'id_materi' => $id_materi,
                'id_mapel' => $id_mapel,
                'jadwal_materi' => $jadwal,
                'jenis' => $jenis
            ];

            $update = $this->db->insert('kelas_jadwal_materi', $insert);
            $this->logging->saveLog(3, 'merubah jadwal materi');
            $msg = 'success';
        }
        $this->output_json(['success'=>$update, 'message'=> $msg]);
    }

    public function delJadwalMateri($id) {
        $this->load->model('Log_model', 'logging');

        $this->db->where('id_kjm', $id);
        $update = $this->db->delete('kelas_jadwal_materi');
        $this->logging->saveLog($this->LOG_TYPE->DELETE, 'menghapus jadwal materi');

        $this->output_json($update);
    }

    public function addMateri($jenis, $id_materi = null) {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $title = $jenis=='1' ? 'Materi' : 'Tugas';
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => $title,
            'subjudul' => $id_materi == null ? 'Buat ' . $title .' Baru' : 'Edit '. $title,
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['is_admin'] = $this->auth->is_admin();

        $data['uptodate'] = $isUpdated;
        $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['id_materi'] = $id_materi;
        $data['jenis'] = $jenis;

        if ($this->auth->is_admin()) {
            if ($id_materi == null) {
                $data['materi'] = json_decode(json_encode($this->elearning->getDummyMateri()));
                $data['id_guru'] = '';
            } else {
                $materi = $this->elearning->getMateriKelasById($id_materi, $jenis);
                $materi->materi_kelas = $this->maybe_unserialize($materi->materi_kelas ?? '[]');
                $materi->file = $this->maybe_unserialize($materi->file ?? '[]');
                $materi->kelas_guru = $this->maybe_unserialize($materi->kelas_guru ?? '[]');
                $data['materi'] = $materi;
                $data['id_guru'] = $materi->id_guru;
            }
            $data['gurus'] = $this->elearning->getAllGuru();
            $this->render_view(1, 'elearning/materi_add', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($id_materi == null) {
                $data['materi'] = json_decode(json_encode($this->elearning->getDummyMateri()));
            } else {
                $materi = $this->elearning->getMateriKelasById($id_materi, $jenis);
                $materi->materi_kelas = $this->maybe_unserialize($materi->materi_kelas ?? '[]');
                $materi->file = $this->maybe_unserialize($materi->file ?? '[]');
                $materi->kelas_guru = $this->maybe_unserialize($materi->kelas_guru ?? '[]');
                $data['materi'] = $materi;
            }
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['gurus'] = $nguru;
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $this->render_view(2, 'elearning/materi_add', $data);
        }
    }

    public function saveMateri() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $jenis = $this->input->post('jenis', true);
        $id_materi = $this->input->post('id_materi', true);
        $kelas = count($this->input->post('kelas', true));
        $attach = json_decode($this->input->post('attach', true));

        $src_file = [];
        foreach ($attach as $at) {
            if ($at->name != null) {
                $src_file[] = [
                    'src' => $at->src,
                    'size' => $at->size,
                    'type' => $at->type,
                    'name' => $at->name
                ];
            }
        }

        $id_kelas = [];
        for ($i = 0; $i < $kelas; $i++) {
            $id_kelas[] = $this->input->post('kelas[' . $i . ']', true);
        }

        $isi_materi = $this->input->post('isi_materi', false);

        $dom = new DOMDocument();
        $dom->loadHTML($isi_materi, LIBXML_HTML_NODEFDTD);
        $images = $dom->getElementsByTagName('img');
        $numimg = 1;
        foreach ($images as $image) {
            $base64_image_string = $image->getAttribute('src');
            if (strpos($base64_image_string, 'http') !== false) {
                $pathUpload = 'uploads';
                $forReplace = explode($pathUpload, $base64_image_string);
                $image->setAttribute('src', $pathUpload.$forReplace[1]);
            } else {
                $splited = explode(',', substr( $base64_image_string , 5 ) , 2);
                $mime=$splited[0];
                $data=$splited[1];

                $mime_split_without_base64=explode(';', $mime,2);
                $mime_split=explode('/', $mime_split_without_base64[0],2);
                $output_file = '';
                if(count($mime_split)==2) {
                    $extension=$mime_split[1];
                    if($extension == 'jpeg')$extension='jpg';
                    $output_file = 'img_'.date('YmdHis').$numimg.'.'.$extension;
                }
                file_put_contents( './uploads/materi/' . $output_file, base64_decode($data) );
                $image->setAttribute('src', 'uploads/materi/' . $output_file);
                $numimg ++;
            }
        }

        $isi = $dom->saveHTML();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data = [
            'jenis' => $jenis,
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'kode_materi' => $this->input->post('kode_materi', true),
            'id_guru' => $this->input->post('guru', true),
            'id_mapel' => $this->input->post('mapel', true),
            'judul_materi' => $this->input->post('judul', true),
            'isi_materi' => $isi,
            'materi_kelas' => json_encode($id_kelas),
            'file' => json_encode($src_file)
        ];

        if ($id_materi == '') {
            $data['created_on'] = date('Y-m-d H:i:s');
            $data['updated_on'] = date('Y-m-d H:i:s');
            $saved = $this->master->create('kelas_materi', $data);

            $result['result_id'] = $this->db->insert_id();
            $result['status'] = $saved;
            $result['message'] = 'Materi berhasil dibuat';
            $this->logging->saveLog(3, 'membuat materi');
        } else {
            $cek_materi = $this->elearning->getMateriKelasById($id_materi, $jenis);
            if ($cek_materi->id_tp == $tp->id_tp && $cek_materi->id_smt == $smt->id_smt) {
                $data['updated_on'] = date('Y-m-d H:i:s');
                $data['id_materi'] = $id_materi;
                $saved = $this->master->update('kelas_materi', $data, 'id_materi', $id_materi);

                $result['status'] = $saved;
                $result['message'] = 'Materi berhasil diupdate';
                $this->logging->saveLog(4, 'mengedit materi');
            } else {
                $data['created_on'] = date('Y-m-d H:i:s');
                $data['updated_on'] = date('Y-m-d H:i:s');
                $saved =  $this->master->create('kelas_materi', $data);

                $result['status'] = $saved;
                $result['message'] = 'Materi berhasil dibuat';
                $this->logging->saveLog(3, 'membuat materi');
            }
        }

        $this->output_json($result);
    }

    public function copyMateri($id_materi, $jenis) {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $materi = $this->elearning->getMateriKelasById($id_materi, $jenis);
        $arrKls = $this->maybe_unserialize($materi->materi_kelas);

        $kelasLalu = $this->getKelasByIds($arrKls);
        $map_lalu = [];
        foreach ($kelasLalu as $item) {
            $kode = $this->normalize($item->kode_kelas);
            $map_lalu[] = $kode;
        }
        $kelas_now = $this->dropdown->getAllKodeKelas($tp->id_tp, $smt->id_smt);
        $kelasSkrg = [];
        foreach ($kelas_now as $ids=>$kode_now) {
            $kode_n = $this->normalize($kode_now); // 10a
            if (in_array($kode_n, $map_lalu)) {
                $kelasSkrg[] = $ids;
            }
        }

        $data = [
            'jenis' => $jenis,
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'kode_materi' => $materi->kode_materi,
            'id_guru' => $materi->id_guru,
            'id_mapel' => $materi->id_mapel == null ? 0 : $materi->id_mapel,
            'judul_materi' => $materi->judul_materi,
            'isi_materi' => $materi->isi_materi,
            'materi_kelas' => json_encode($kelasSkrg),
            'file' => $materi->file,
            'created_on' => date('Y-m-d H:i:s'),
            'updated_on' => date('Y-m-d H:i:s')
        ];
        $materi_ada = $this->elearning->getMateriGuruMapel($tp->id_tp, $smt->id_smt, $jenis, $materi->kode_materi, $materi->id_guru, $materi->id_mapel ?? '-2');
        if ($materi_ada) {
            $this->db->where('id_materi', $materi_ada->id_materi);
            $result = $this->db->update('kelas_materi', $data);
        } else {
            $result = $this->db->insert('kelas_materi', $data);
        }
        $this->logging->saveLog(3, 'menyalin materi/tugas');

        $this->output_json($data);
    }

    public function delMateri() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $id = $this->input->post('id_materi', true);
        $ids = explode(',', $id);
        $this->db->where_in('id_materi', $ids);
        if ($this->db->delete('kelas_materi')) {
            $this->db->where_in('id_materi', $ids);
            if ($this->db->delete('kelas_jadwal_materi')) {
                $this->logging->saveLog(5, 'menghapus materi');
                $this->output_json(['status' => true]);
            }
        }
    }

    public function aktifkanMateri() {
        $this->load->model('Log_model', 'logging');
        $method = $this->input->post('method', true);
        $id = $this->input->post('id_materi', true);
        $stat = $method == '1' ? '0' : '1';

        $this->db->set('status', $stat);
        $this->db->where('id_materi', $id);
        $this->db->update('kelas_materi');

        $this->logging->saveLog(3, 'mengaktifkan materi');
        $this->output_json(['status' => true]);
    }

    public function dataAddKelas($guru) {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->elearning->getGuruMapelKelas($guru, $tp->id_tp, $smt->id_smt);
        $kelas = $this->maybe_unserialize($guru->mapel_kelas ?? '[]');
        $this->output_json($kelas);
    }

    public function dataAddJadwal() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');
        $id_kelas = $this->input->get('kelas');
        $id_mapel = $this->input->get('mapel');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $mapel = $this->elearning->getJadwalMapelByMapel($tp->id_tp, $smt->id_smt, $id_mapel, $id_kelas );
        $latestMpl = '0';
        $lastInd = -1;
        $lastDay = '';
        foreach ($mapel as $ind=>$jad) {
            if ($latestMpl !== '0' && $jad->id_mapel == $latestMpl && $jad->id_hari == $lastDay) {
                $mapel[$lastInd]->sampai = $jad->sampai;
                unset($mapel[$ind]);
            } else {
                $lastInd = $ind;
                $lastDay = $jad->id_hari;
                $latestMpl = $jad->id_mapel ?? '0';
            }
        }

        $jadwal_terisi = $this->elearning->getJadwalTerisi('kelas_jadwal_materi', $id_kelas, $id_mapel, $tp->id_tp, $smt->id_smt);
        $this->output_json(['mapel'=>array_values($mapel), 'terisi'=>$jadwal_terisi]);
    }

    public function perpustakaan()
    {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->auth->user();
        $data = [
            'user'      => $user,
            'judul'     => 'Perpustakaan Digital',
            'subjudul'  => 'Daftar Perpustakaan',
            'setting'	=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        $data['uptodate'] = $isUpdated;

        $pustaka = $this->db->get('kelas_libraries')->result();
        $data['pustaka'] = $pustaka;

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'elearning/perpustakaan', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;
            $this->render_view(2, 'elearning/perpustakaan', $data);
        }
    }

    public function add_pustaka()
    {
        $insert = [
            'url' => $this->input->post('url_address', true),
            'title' => $this->input->post('title', true),
            'siteName' => $this->input->post('site_name', true),
            'description' => $this->input->post('description', true)
        ];

        $id_url = $this->input->post('id', true);
        if ($id_url) {
            $this->db->where('id', $id_url);
            $insert = $this->db->update('kelas_libraries', $insert);
        } else {
            $insert['image'] = $this->input->post('image', true);
            $insert = $this->db->insert('kelas_libraries', $insert);
        }

        $data = [
            'status' => $insert,
            'msg' => !$insert ? 'Gagal menyimpan pustaka' : 'Pustaka berhasil disimpan'];
        $this->output_json($data);
    }

    public function del_pustaka()
    {
        $id_url = $this->input->get('id_url');
        $deleted = $this->db->delete('kelas_libraries', ['id' => $id_url]);
        $this->output_json(['status' => $deleted, 'msg' => !$deleted ? 'Gagal menghapus URL pustaka' : 'URL pustaka berhasil dihapus']);
    }

    public function status()
    {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->auth->user();
        $data = [
            'user'      => $user,
            'judul'     => 'Nilai Harian Siswa',
            'subjudul'  => 'Nilai',
            'setting'	=> $this->dashboard->getSetting()
        ];
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        $data['uptodate'] = $isUpdated;
        if ($this->auth->is_admin()) {

            $guru = $this->elearning->getAllGuru();
            $data['gurus'] = $guru;
            $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['mapels'] = $this->elearning->getAllMapel();

            $this->render_view(1, 'elearning/siswa_status', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['guru'] = $guru;
            $data['gurus'] = $nguru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $kelass = $this->elearning->getNamaKelasById([$kls->kelas]);
                        if ($kelass) {
                            foreach ($kelass as $idkls=>$kelas) {
                                $arrKelas[$idkls] = $kelas;
                            }
                        }
                    }
                }
            }

            $data['mapel'] = $mapel;
            $data['mapels'] = $arrMapel;
            $data['kelas'] = $arrKelas;

            $this->render_view(2, 'elearning/siswa_status', $data);
        }
    }

    public function getMateriMapel() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $id_mapel = $this->input->get('id', true);
        $id_guru = $this->input->get('id_guru', true);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $materi = $this->elearning->getKodeMateriMapel($tp->id_tp, $smt->id_smt, $id_mapel, $id_guru);
        $arrKelasMateri = [];
        $arrKelasTugas = [];
        $arrKelas = [];
        foreach ($materi as $m) {
            $kode_mapel = $m->kode_mapel == null ? '--' : $m->kode_mapel;
            if ($m->jenis == '1') {
                $arrMateri = [
                    'id_materi'=> $m->id_materi,
                    'id_kjm'=> $m->id_kjm,
                    'jadwal'=> $m->jadwal_materi,
                    'kode'=>$m->kode_materi,
                    'mapel'=>$kode_mapel,
                    'guru'=>$m->nama_guru,
                    'jenis'=>$m->jenis
                ];

                if (!isset($arrKelasMateri[$m->id_kelas])) {
                    $arrKelasMateri[$m->id_kelas] = [];
                }
                $arrKelasMateri[$m->id_kelas][] = $arrMateri;
            } else {
                $arrTugas = [
                    'id_materi'=> $m->id_materi,
                    'id_kjm'=> $m->id_kjm,
                    'jadwal'=> $m->jadwal_materi,
                    'kode'=>$m->kode_materi,
                    'mapel'=>$kode_mapel,
                    'guru'=>$m->nama_guru,
                    'jenis'=>$m->jenis
                ];

                if (!isset($arrKelasTugas[$m->id_kelas])) {
                    $arrKelasTugas[$m->id_kelas] = [];
                }
                $arrKelasTugas[$m->id_kelas][] = $arrTugas;
            }

            if (isset($arrKelas[$m->jenis])) {
                if (!in_array($m->id_kelas, $arrKelas[$m->jenis], true)) {
                    $arrKelas[$m->jenis][] = $m->id_kelas;
                }
            } else {
                $arrKelas[$m->jenis] = [];
                $arrKelas[$m->jenis][] = $m->id_kelas;
            }
        }

        $this->output_json(array(
            'materi'=>$arrKelasMateri,
            'tugas'=>$arrKelasTugas,
            'kelas' => $arrKelas,
        ));
    }

    public function loadStatus() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $label = $this->input->post('label', true);
        $id_kelas = $this->input->post('id_kelas', true);
        $id_kjm = $this->input->post('id_kjm', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;
        $jenis = $label == "Materi" ? '1' : '2';

        $siswa = $this->elearning->getKelasSiswa($id_kelas, $id_tp, $id_smt);
        $logs = $this->elearning->getStatusMateriSiswa($id_kjm);

        $info = $this->elearning->getJadwalKbmByKelas($id_tp, $id_smt, $id_kelas);
        if ($info != null) {
            $info->istirahat = $this->maybe_unserialize($info->istirahat ?? '');
        }
        $materi = $this->elearning->getMateriKelasSiswa($id_kjm, $jenis);
        $detail = [];
        $jam_materi = [];
        if ($materi) {
            $kelas_materi = $this->elearning->getNamaKelasById([$id_kelas]);

            $numday = date('N', strtotime($materi->jadwal_materi ?? ''));
            $jadwals = $this->elearning->loadJadwalSiswaHariIni($id_tp, $id_smt, $id_kelas, $numday);
            $key = array_search($materi->id_mapel, array_column($jadwals, 'id_mapel'));
            $jadwal = $jadwals[$key];

            $jam_materi = [
                "dari"=> $jadwal->dari,
                "sampai"=> $jadwal->sampai,
                "tgl"=>$materi->jadwal_materi
            ];

            $detail = [
                "mapel" => $materi->nama_mapel,
                "judul" => $materi->judul_materi,
                "guru" => $materi->nama_guru,
                "kelas" => $kelas_materi[$id_kelas],
                "waktu" => $jam_materi,
            ];
        }

        $log = [];
        foreach ($siswa as $s) {
            $mulai = isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->log_time : null;
            $selesai = isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->finish_time : null;

            $diff = null;
            if ($selesai) {
                $jam_jadwal = new DateTime(date('Y-m-d H:i:s', strtotime(($materi->jadwal_materi ?? '') . ' ' .$jam_materi['sampai'])));
                $jam_siswa = new DateTime(date('Y-m-d H:i:s', strtotime($mulai ?? '')));
                $interval = $jam_siswa->diff($jam_jadwal);
                $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                $time_jadwal = strtotime(($materi->jadwal_materi ?? '') . ' ' .$jam_materi['sampai']);
                $time_siswa = strtotime($mulai ?? '');
                $diff = [
                    'days'  => $interval->days,
                    'hari'  => $interval->d,
                    'jam'   => $interval->h,
                    'menit' => $interval->i,
                    'detik' => $interval->s,
                    'total' => $minutes,
                    'interval' => (int)$interval->format( '%r%H:%i:%s' ),
                    'terlambat' => ($time_siswa - $time_jadwal) > 0
                ];
            }

            $log[$s->id_siswa] = [
                'nama' =>$s->nama,
                'nis' =>$s->nis,
                'kelas' =>$s->nama_kelas,
                'login' => $this->elearning->getLoginSiswa($s->username),
                'ulang' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->ulang : '',
                'mulai' => $mulai,
                'selesai' => $selesai,
                'text' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->text : '',
                'nilai' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->nilai : '',
                'catatan' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->catatan : '',
                'jadwal_materi' => isset($logs[$s->id_siswa]) ? $logs[$s->id_siswa]->jadwal_materi : null,
                'file' => isset($logs[$s->id_siswa]) && $logs[$s->id_siswa]->file != null ? $this->maybe_unserialize($logs[$s->id_siswa]->file ?? '') : [],
                'diff' => $diff,
                'j_materi' => $jam_materi['sampai']
            ];
        }
        $this->output_json([
            "log"=>$log,
            "jadwal"=>$info,
            "materi"=>$materi,
            "detail"=>$detail,
            //"kelas"=>$kelas_materi,
            //"mapel"=>$mapel
        ]);
    }

    public function saveNilai() {
        $id_siswa = $this->input->post('id_siswa', true);
        $id_materi = $this->input->post('id_materi', true);
        $nilai = $this->input->post('nilai', true);
        $catatan = $this->input->post('catatan', true);

        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_materi', $id_materi);
        $q = $this->db->get('log_materi');

        if ($q->num_rows() > 0) {
            $insert = [
                'nilai'		=> $nilai,
                'catatan'	=> $catatan,
            ];

            $this->db->where('id_siswa', $id_siswa);
            $this->db->where('id_materi', $id_materi);
            $update = $this->db->update('log_materi', $insert);
        } else {
            $insert = [
                'id_siswa'  => $id_siswa,
                'id_materi'  => $id_materi,
                'nilai'		=> $nilai,
                'catatan'	=> $catatan,
            ];

            $update = $this->db->insert('log_materi', $insert);
        }
        $this->output_json($update);
    }

    public function ulangi() {
        $id_siswa = $this->input->post('id_siswa', true);
        $id_materi = $this->input->post('id_materi', true);

        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_materi', $id_materi);
        $update = $this->db->update('log_materi', ['ulang' => '1', 'nilai' => 0]);

        $this->output_json($update);
    }

    public function harian()
    {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->auth->user();
        $data = [
            'user'      => $user,
            'judul'     => 'Kehadiran Harian Siswa',
            'subjudul'  => 'Data Kehadiran Siswa',
            'setting'	=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        $data['uptodate'] = $isUpdated;
        $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['mapel'] = $this->elearning->getAllMapel();

        if ($this->auth->is_admin()) {
            $data['guru'] = $this->elearning->getAllGuru();
            $this->render_view(1, 'elearning/siswa_harian', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $this->render_view(2, 'elearning/siswa_harian', $data);
        }
    }

    public function loadAbsensi() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $id_kelas = $this->input->post('kelas', true);
        $tahun = $this->input->post('thn', true);
        $bulan = $this->input->post('bln', true);
        $tanggal = $this->input->post('tgl', true);
        $hari = $this->input->post('hari', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;

        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $tanggal = str_pad($tanggal, 2, '0', STR_PAD_LEFT);

        $info = $this->elearning->getJadwalKbmByKelas($id_tp, $id_smt, $id_kelas);
        unset($info->istirahat);

        $jadwal = $this->elearning->loadJadwalHariIni($id_tp, $id_smt, $id_kelas, $hari);
        $arrIdMapel = [];
        $latestMpl = '0';
        $lastInd = -1;
        $lastDay = '';

        foreach ($jadwal as $ind=>$jad) {
            $arrIdMapel[] = $jad->id_mapel;
            if ($latestMpl !== '0' && $jad->id_mapel == $latestMpl && $jad->id_hari == $lastDay) {
                $jadwal[$lastInd]->sampai = $jad->sampai;
                unset($jadwal[$ind]);
            } else if ($jad->id_mapel == '-1') {
                unset($jadwal[$ind]);
            } else {
                $lastInd = $ind;
                $lastDay = $jad->id_hari;
                $latestMpl = $jad->id_mapel ?? '0';
            }
        }
        $jadwal = array_values($jadwal);

        $jadwal_materi = [];
        if (count($arrIdMapel)>0) {
            $jadwal_materi = $this->elearning->getAllMateriByTgl($id_kelas, $tahun.'-'.$bulan.'-'.$tanggal, $arrIdMapel);
        }

        $arrIdKjm = [];
        foreach ($jadwal_materi as $jmtr) {
            $arrIdKjm[] = $jmtr->id_kjm;
        }

        $siswa = $this->elearning->getKelasSiswa($id_kelas, $id_tp, $id_smt);
        $logs = $this->elearning->getRekapMateriKelas($id_kelas, $arrIdKjm);

        $log = [];
        if ($info != null) {
            foreach ($siswa as $s) {
                $log[$s->id_siswa] = [
                    'nama' => $s->nama,
                    'nis' => $s->nis,
                    'kelas' => $s->nama_kelas,
                    'status' => $logs[$s->id_siswa] ?? [],
                    //'test' => $logSiswa
                ];
            }
        }

        $this->output_json(array(
                'log'=>$log,
                'info'=>$info,
                'jadwal'=>$jadwal,
                'materi'=>$jadwal_materi,
            )
        );
    }

    public function bulanan()
    {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Daftar Hadir Bulanan',
            'subjudul' => 'Daftar Hadir Bulanan Siswa',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        $data['uptodate'] = $isUpdated;
        $data['bulan'] = $this->elearning->getBulan();

        if ($this->auth->is_admin()) {

            $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['guru'] = $this->elearning->getAllGuru();
            $data['mapel'] = $this->elearning->getAllMapel();

            $this->render_view(1, 'elearning/siswa_bulanan', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $arrKelas[$m->id_mapel][] = [
                            'id_kelas' => $kls->kelas,
                            'nama_kelas' => $this->elearning->getNamaKelasById([$kls->kelas])
                        ];
                    }
                }
            }

            $arrId = [];
            if ($mapel != null) {
                foreach ($mapel as $mpl_kls) {
                    foreach ($mpl_kls->kelas_mapel as $id_mapel) {
                        $arrId[] = $id_mapel->kelas ?? '';
                    }
                }
            }

            $data['mapel'] = $arrMapel;
            $data['arrkelas'] = $arrKelas;
            $data['kelas'] = count($arrId)>0 ? $this->elearning->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

            $this->render_view(2, 'elearning/siswa_bulanan', $data);
        }
    }

    public function loadAbsensiMapel() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Elearning_model',  'elearning');

        $id_kelas = $this->input->post('kelas', true);
        $id_mapel = $this->input->post('mapel', true);
        $tahun = $this->input->post('thn', true);
        $bulan = $this->input->post('bln', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;

        $jadwal = $this->elearning->getJadwalKbmByKelas($id_tp, $id_smt, $id_kelas);
        $arrTgl = [];
        if ($jadwal != null) {
            unset($jadwal->istirahat);
            //$tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
            $tgl = date('t', strtotime("$tahun-$bulan-01"));
            for ($i=0;$i<$tgl;$i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $b = $bulan < 10 ? '0'.($bulan) : $bulan;
                $arrTgl[] = $tahun.'-'.$b.'-'.$t;
            }
            $jadwal_materi = $this->elearning->getAllMateriByArrTgl($id_kelas, $arrTgl, $id_mapel);

            $materi_perbulan = $this->elearning->getRekapBulananSiswa($id_mapel, $id_kelas, $tahun, $bulan);
            $log = [];

            $siswa = $this->elearning->getKelasSiswa($id_kelas, $id_tp, $id_smt);
            foreach ($siswa as $s) {
                $arrMateri = [];
                for ($i=0;$i<$tgl;$i++) {
                    $t = ($i+1) < 10 ? '0'.($i+1) : $i+1;
                    $b = $bulan < 10 ? '0'.($bulan) : $bulan;
                    $arrMateri[1][] = $materi_perbulan !== null && isset($materi_perbulan[$s->id_siswa][1]) && isset($materi_perbulan[$s->id_siswa][1][$tahun . '-' . $b . '-' . $t]) ?
                        $materi_perbulan[$s->id_siswa][1][$tahun.'-'.$b.'-'.$t] : null;

                    $arrMateri[2][] = $materi_perbulan !== null && isset($materi_perbulan[$s->id_siswa][2]) && isset($materi_perbulan[$s->id_siswa][2][$tahun . '-' . $b . '-' . $t]) ?
                        $materi_perbulan[$s->id_siswa][2][$tahun.'-'.$b.'-'.$t] : null;

                }

                $log[$s->id_siswa] = [
                    'nama' =>$s->nama,
                    'nis' =>$s->nis,
                    'kelas' =>$s->nama_kelas,
                    'materi' => $arrMateri[1],
                    'tugas' => $arrMateri[2]
                ];
            }

            $mapel_bulan_ini = [];
            $infos = $this->elearning->getJadwalMapelByMapel($id_tp, $id_smt, $id_mapel, $id_kelas);
            $latestMpl = '0';
            $lastInd = -1;
            $lastDay = '';

            foreach ($infos as $ind=>$info) {
                if ($latestMpl !== '0' && $info->id_mapel == $latestMpl && $info->id_hari == $lastDay) {
                    if (isset($infos[$lastInd])) {
                        $infos[$lastInd]->sampai = $info->sampai;
                        unset($infos[$ind]);
                    }
                } else if ($info->id_mapel == '-1') {
                    unset($infos[$ind]);
                } else {
                    $lastInd = $ind;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';

                    $dates = $this->getDateFromWeekday($info->id_hari, $bulan, $tahun);
                    foreach ($dates as $date) {
                        $d = explode('-', $date ?? '');
                        $mapel_bulan_ini[$d[2]] = $date;
                    }
                }
            }

            $this->output_json([
                "log"=>$log,
                "jadwal"=>$jadwal,
                "materi"=>$jadwal_materi,
                "mapels"=>$mapel_bulan_ini,
                "info" => array_values($infos)
            ]);
        } else {
            $this->output_json(["jadwal"=>$jadwal]);
        }
    }

    public function nilai()
    {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Rekapitulasi Nilai Siswa',
            'subjudul' => 'Nilai dalam satu semester',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        $data['uptodate'] = $isUpdated;
        if (!$isUpdated->is_latest) {
            if ($this->auth->is_admin()) {
                $this->load->view('init.php');
            } else {
                $this->load->view('maintenance.php');
            }
        } else {

            if ($this->auth->is_admin()) {

                $data['mapel'] = $this->elearning->getAllMapel();
                $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);

                $this->render_view(1, 'elearning/siswa_nilai.php', $data);
            } else {
                $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $data['guru'] = $guru;
                $data['id_guru'] = $guru->id_guru;

                $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
                $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

                $arrMapel = [];
                $arrKelas = [];
                if ($mapel != null) {
                    foreach ($mapel as $m) {
                        $arrMapel[$m->id_mapel] = $m->nama_mapel;
                        foreach ($m->kelas_mapel as $kls) {
                            $arrKelas[$m->id_mapel][] = [
                                'id_kelas' => $kls->kelas,
                                'nama_kelas' => $this->elearning->getNamaKelasById([$kls->kelas])
                            ];
                        }
                    }
                }

                $arrId = [];
                if ($mapel != null) {
                    foreach ($mapel as $mpl_kls) {
                        foreach ($mpl_kls->kelas_mapel as $id_mapel) {
                            $arrId[] = $id_mapel->kelas ?? '';
                        }
                    }
                }

                $data['mapel'] = $arrMapel;
                $data['arrkelas'] = $arrKelas;
                $data['kelas'] = count($arrId) > 0 ? $this->elearning->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

                $this->render_view(2, 'elearning/siswa_nilai.php', $data);
            }
        }
    }

    public function loadNilaiMapel() {
        $this->load->model('Elearning_model',  'elearning');

        $kelas = $this->input->get('kelas');
        $mapel = $this->input->get('mapel');
        $tahun = $this->input->get('tahun');
        $smt = $this->input->get('smt');
        $stahun = $this->input->get('stahun');

        $siswa = $this->elearning->getKelasSiswa($kelas, $tahun, $smt);

        if ($smt=='1') {
            $arrBulan = ['07','08','09','10','11','12'];
        } else {
            $arrBulan = ['01','02','03','04','05','06'];
        }

        //$namaBulan = ["", "Januari", "Februari","Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember"];
        //$namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

        $infos = $this->elearning->getJadwalMapelByMapel($tahun, $smt, $mapel, $kelas);
        $log_siswa = $this->elearning->getRekapMateriSemester($kelas);

        $jadwal_per_bulan = [];
        //$cols = 0;

        $arrTgl = [];
        $dates = [];
        foreach ($arrBulan as $bulan) {
            $latestMpl = '0';
            $lastDay = '';
            foreach ($infos as $info) {
                //$dates = array_merge($dates, $this->getDateFromWeekday($info->id_hari, $bulan, $stahun));
                $currentDates = $this->getDateFromWeekday($info->id_hari, $bulan, $stahun);
                foreach ($currentDates as $date) {
                    $dates[] = $date; // Menambahkan elemen baru ke array $dates
                }
                if ($latestMpl !== '0' && $info->id_mapel == $latestMpl && $info->id_hari == $lastDay) {
                    $jadwal_per_bulan[$bulan][$info->id_hari]->sampai = $info->sampai;
                } else {
                    $jadwal_per_bulan[$bulan][$info->id_hari] = $info;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';
                }
            }

            //$tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $stahun);
            $tgl = date('t', strtotime("$tahun-$bulan-01"));
            for ($i=0;$i<$tgl;$i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $arrTgl[] = $stahun.'-'.$bulan.'-'.$t;
            }
        }

        $all_jadwal =  $this->elearning->getAllMateriByArrTgl($kelas, $arrTgl, $mapel);
        $jadwal_materi = [];
        foreach ($dates as $date) {
            $d = explode('-', $date ?? '');
            $b = $d[1];
            $t = $d[2];
            $jadwal_materi[$b][$t] = $all_jadwal[$date] ?? null;
        }

        $log = [];
        if (count($siswa)>0 && count($jadwal_per_bulan)>0) {
            foreach ($siswa as $s) {
                $log[$s->id_siswa] = [
                    'nama' =>$s->nama,
                    'nis' =>$s->nis,
                    'kelas' =>$s->nama_kelas,
                    'nilai' => $log_siswa[$s->id_siswa] ?? [],
                ];
            }

            $data = [
                'log' => $log,
                'materi'=>$jadwal_materi,
                'bulans'=>$arrBulan,
                'mapels'=>$jadwal_per_bulan,
                'nilai'=> $log_siswa,
                'tahun'=>$stahun
                //'cols'=>$cols,
                //'all'=>$all_jadwal,
                //'dates'=>$dates
            ];
        } else {
            $data['mapels'] = [];
        }

        $this->output_json($data);
    }

    public function nilaiSemester()
    {
        $isUpdated = $this->isUpdated();

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model',  'elearning');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Rekapitulasi Nilai Siswa',
            'subjudul' => 'Nilai dalam satu semester',
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tps = $this->dashboard->getTahun();
        $smts = $this->dashboard->getSemester();
        $data['tp'] = $tps;
        $data['tp_active'] = $tp;
        $data['smt'] = $smts;
        $data['smt_active'] = $smt;
        $data['uptodate'] = $isUpdated;
        if (!$isUpdated->is_latest) {
            if ($this->auth->is_admin()) {
                $this->load->view('init.php');
            } else {
                $this->load->view('maintenance.php');
            }
        } else {
            if ($smt->id_smt == '1') {
                $arrBulan = [
                    '00' => "Satu Semeter",
                    '07' => "Juli",
                    '08' => "Agustus",
                    '09' => "September",
                    '10' => "Oktober",
                    '11' => "Nopember",
                    '12' => "Desember"
                ];
            } else {
                $arrBulan = [
                    '00' => "Satu Semeter",
                    '01' => "Januari",
                    '02' => "Februari",
                    '03' => "Maret",
                    '04' => "April",
                    '05' => "Mei",
                    '06' => "Juni"
                ];
            }
            $data['bulans'] = $arrBulan;

            if ($this->auth->is_admin()) {

                $data['mapel'] = $this->elearning->getAllMapel();
                $data['kelas'] = $this->elearning->getAllKelas($tp->id_tp, $smt->id_smt);

                $this->render_view(1, 'elearning/siswa_semester', $data);
            } else {
                $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $data['guru'] = $guru;
                $data['id_guru'] = $guru->id_guru;

                $mapel_guru = $this->elearning->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
                $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

                $arrMapel = [];
                $arrKelas = [];
                if ($mapel != null) {
                    foreach ($mapel as $m) {
                        $arrMapel[$m->id_mapel] = $m->nama_mapel;
                        foreach ($m->kelas_mapel as $kls) {
                            $arrKelas[$m->id_mapel][] = [
                                'id_kelas' => $kls->kelas,
                                'nama_kelas' => $this->elearning->getNamaKelasById([$kls->kelas])
                            ];
                        }
                    }
                }

                $arrId = [];
                if ($mapel != null) {
                    foreach ($mapel as $mpl_kls) {
                        foreach ($mpl_kls->kelas_mapel as $id_mapel) {
                            $arrId[] = $id_mapel->kelas ?? '';
                        }
                    }
                }

                $data['mapel'] = $arrMapel;
                $data['arrkelas'] = $arrKelas;
                $data['kelas'] = count($arrId) > 0 ? $this->elearning->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

                $this->render_view(2, 'elearning/siswa_semester', $data);
            }
        }
    }

    public function loadNilaiSemester() {
        $this->load->model('Elearning_model',  'elearning');

        $kelas = $this->input->get('kelas');
        $mapel = $this->input->get('mapel');
        $tahun = $this->input->get('tahun');
        $smt = $this->input->get('smt');
        $stahun = $this->input->get('stahun');

        $siswa = $this->elearning->getKelasSiswa($kelas, $tahun, $smt);

        if ($smt=='1') {
            $arrBulan = ['07','08','09','10','11','12'];
        } else {
            $arrBulan = ['01','02','03','04','05','06'];
        }

        //$namaBulan = ["", "Januari", "Februari","Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "Nopember", "Desember"];
        //$namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

        $infos = $this->elearning->getJadwalMapelByMapel($tahun, $smt, $mapel, $kelas);
        $log_siswa = $this->elearning->getRekapMateriSemester($kelas);

        $jadwal_per_bulan = [];
        //$cols = 0;

        $arrTgl = [];
        $dates = [];
        foreach ($arrBulan as $bulan) {
            $latestMpl = '0';
            $lastDay = '';
            foreach ($infos as $info) {
                //$dates = array_merge($dates, $this->getDateFromWeekday($info->id_hari, $bulan, $stahun));
                $currentDates = $this->getDateFromWeekday($info->id_hari, $bulan, $stahun);
                foreach ($currentDates as $date) {
                    $dates[] = $date; // Menambahkan elemen baru ke array $dates
                }
                if ($latestMpl !== '0' && $info->id_mapel == $latestMpl && $info->id_hari == $lastDay) {
                    $jadwal_per_bulan[$bulan][$info->id_hari]->sampai = $info->sampai;
                } else {
                    $jadwal_per_bulan[$bulan][$info->id_hari] = $info;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';
                }
            }

            //$tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $stahun);
            $tgl = date('t', strtotime("$tahun-$bulan-01"));
            for ($i=0;$i<$tgl;$i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $arrTgl[] = $stahun.'-'.$bulan.'-'.$t;
            }
        }
        $all_jadwal =  $this->elearning->getAllMateriByArrTgl($kelas, $arrTgl, $mapel);
        $jadwal_materi = [];
        foreach ($dates as $date) {
            $d = explode('-', $date ?? '');
            $b = $d[1];
            $t = $d[2];
            $jadwal_materi[$b][$t] = $all_jadwal[$date] ?? null;
        }

        $log = [];
        if (count($siswa)>0 && count($jadwal_per_bulan)>0) {
            foreach ($siswa as $s) {
                $log[$s->id_siswa] = [
                    'nama' =>$s->nama,
                    'nis' =>$s->nis,
                    'kelas' =>$s->nama_kelas,
                    'nilai' => $log_siswa[$s->id_siswa] ?? null,
                ];
            }

            $data = [
                'log' => $log,
                'materi'=>$jadwal_materi,
                'bulans'=>$arrBulan,
                'mapels'=>$jadwal_per_bulan,
                'nilai'=> $log_siswa,
                'tahun'=>$stahun,
                //'tp'=> $tahun,
                //'smt'=> $smt
                //'cols'=>$cols,
                //'all'=>$all_jadwal,
                //'dates'=>$dates
            ];
        } else {
            $data['mapels'] = [];
        }

        $this->output_json($data);
    }

    function getDateFromWeekday($dayOfWeek, $month, $year) {
        $dates = [];
        $date = new DateTime("$year-$month-01");

        $firstDayOfWeek = $date->format('N');

        $dayOffset = $dayOfWeek - $firstDayOfWeek;

        if ($dayOffset < 0) {
            $dayOffset += 7;
        }

        $date->modify("+$dayOffset day");

        while ($date->format('m') == $month) {
            $dates[] = $date->format('Y-m-d');
            $date->modify('+7 days');
        }

        return $dates;
    }

    function uploadFile(){
        $this->load->library('upload');
        $max_size = $this->input->post('max-size', true);
        if(isset($_FILES["file_uploads"]["name"])){
            $config['upload_path'] = './uploads/materi/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4|avi|doc|docx|xls|xlsx|ppt|pptx|csv|pdf|rtf|txt';
            //$config['encrypt_name'] = TRUE;
            $config['max_size'] = $max_size;
            $config['overwrite'] = TRUE;

            $this->upload->initialize($config);
            if(!$this->upload->do_upload('file_uploads')){
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            }else{
                $result = $this->upload->data();
                $data['src'] = 'uploads/materi/'.$result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;
            }

            $data['type'] = $_FILES['file_uploads']['type'];
            $data['size'] = $_FILES['file_uploads']['size'];
        }
        $this->output_json($data);
    }

    function deleteFile() {
        $src = $this->input->post('src');
        if (unlink($src)) {
            echo 'File Delete Successfully';
        } else {
            echo 'Gagal';
        }
    }

    private function normalize($str) {
        return strtolower(preg_replace('/[\s.\-_]/', '', $str ?? ''));
    }
}
