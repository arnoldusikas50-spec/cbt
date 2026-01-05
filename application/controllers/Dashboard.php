<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Elearning_model', 'elearning');
        $this->load->model('Dropdown_model', 'dropdown');
    }

    //public $user;
    //public $tp;
    //public $smt;

    public function admin_box($setting, $tp, $smt)
    {
        $where = '';
        if ($setting->jenjang == "1") {
            $where = 'jenjang=0 OR jenjang=1';
        } elseif ($setting->jenjang == "2") {
            $where = 'jenjang=2 OR jenjang=1';
        }

        $box = [
            [
                'box' => 'blue',
                'total' => $this->master->getSiswaTotalPage($tp, $smt, '0'),
                'title' => 'Siswa',
                'url' => 'datasiswa',
                'icon' => 'users'
            ],
            [
                'box' => 'cyan',
                'total' => $this->dashboard->total('master_kelas', 'id_tp=' . $tp . ' AND id_smt=' . $smt),
                'title' => 'Rombel',
                'url' => 'datakelas',
                'icon' => 'bell'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboard->total('master_guru'),
                'title' => 'Guru',
                'url' => 'dataguru',
                'icon' => 'user-circle'
            ],
            [
                'box' => 'fuchsia',
                'total' => $this->dashboard->totalWaliKelas($tp, $smt),
                'title' => 'Wali Kelas',
                'url' => 'dataguru',
                'icon' => 'user-o'
            ],
            [
                'box' => 'success',
                'total' => $this->dashboard->total('master_mapel', $where),
                'title' => 'Mapel',
                'url' => 'datamapel',
                'icon' => 'book'
            ],
            [
                'box' => 'info',
                'total' => $this->dashboard->total('master_ekstra'),
                'title' => 'Ekstrakurikuler',
                'url' => 'dataekstra',
                'icon' => 'universal-access'
            ],
        ];
        return json_decode(json_encode($box), FALSE);
    }

    public function ujian_box($tp, $smt)
    {
        $box = [
            [
                'box' => 'indigo',
                'total' => $this->dashboard->total('cbt_ruang'),
                'title' => 'Ruang',
                'url' => 'cbtruang',
                'icon' => 'school'
            ],
            [
                'box' => 'maroon',
                'total' => $this->dashboard->total('cbt_sesi'),
                'title' => 'Sesi',
                'url' => 'cbtsesi',
                'icon' => 'clock'
            ],
            [
                'box' => 'green',
                'total' => $this->dashboard->total('cbt_bank_soal'),
                'title' => 'Bank Soal',
                'url' => 'cbtbanksoal',
                'icon' => 'folder'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboard->totalJadwal($tp, $smt),
                'title' => 'Jadwal',
                'url' => 'cbtjadwal',
                'icon' => 'clock'
            ],
        ];
        return json_decode(json_encode($box), FALSE);
    }

    public function panitia_box($tp, $smt)
    {
        // 1. Data Guru
        $total_guru = $this->dashboard->total('master_guru');

        // 2. Data Siswa
        $total_siswa = $this->master->getSiswaTotalPage($tp, $smt, '0');

        // 3. Data Pengawas (Panitia)
        $total_pengawas = $this->dashboard->total('master_panitia');

        // 4. Jadwal Aktif
        $now = date('Y-m-d H:i:s');
        $this->db->where('tgl_mulai <=', $now);
        $this->db->where('tgl_selesai >=', $now);
        $total_active_exams = $this->db->count_all_results('cbt_jadwal');

        // 5. Siswa Belum Ujian
        $belum_ujian = 0;
        // Get active schedule IDs first
        $this->db->select('id_jadwal');
        $this->db->where('tgl_mulai <=', $now);
        $this->db->where('tgl_selesai >=', $now);
        $active_schedules = $this->db->get('cbt_jadwal')->result_array();
        $schedule_ids = array_column($active_schedules, 'id_jadwal');

        if (!empty($schedule_ids)) {
            $this->db->where_in('id_jadwal', $schedule_ids);
            $this->db->group_start();
            $this->db->where('selesai', null);
            $this->db->group_end();
            $belum_ujian = $this->db->count_all_results('cbt_siswa');
        }

        $box = [
            [
                'box' => 'blue',
                'total' => $total_guru,
                'title' => 'Guru',
                'url' => 'dataguru',
                'icon' => 'chalkboard-teacher'
            ],
            [
                'box' => 'cyan',
                'total' => $total_siswa,
                'title' => 'Siswa',
                'url' => 'datasiswa',
                'icon' => 'users'
            ],
            [
                'box' => 'teal',
                'total' => $total_pengawas,
                'title' => 'Pengawas',
                'url' => 'datapanitia',
                'icon' => 'user-tie'
            ],
            [
                'box' => 'fuchsia',
                'total' => $total_active_exams,
                'title' => 'Jadwal Aktif',
                'url' => 'cbtjadwal',
                'icon' => 'clock'
            ],
            [
                'box' => 'maroon',
                'total' => $belum_ujian,
                'title' => 'Belum Ujian',
                'url' => 'cbtstatus/belum_ujian',
                'icon' => 'user-clock'
            ]
        ];
        return json_decode(json_encode($box), FALSE);
    }

    public function menu_siswa_box()
    {
        $box = [
            [
                'title' => 'Jadwal Pelajaran',
                'icon' => 'ic_online.gif',
                'link' => 'kbm/jadwal_pelajaran'
            ],
            [
                'title' => 'Materi',
                'icon' => 'ic_elearning.gif',
                'link' => 'kbm/materi'
            ],
            [
                'title' => 'Tugas',
                'icon' => 'ic_questions.gif',
                'link' => 'kbm/tugas'
            ],
            /* ikasmedia */
            /*[
                'title'		=> 'Ujian / Ulangan',
                'icon'		=> 'ic_question.png',
                'link'		=> 'cbtsiswa/cbt'
            ],
            */
            [
                'title' => 'Nilai Hasil',
                'icon' => 'ic_exam.gif',
                'link' => 'kbm/hasil'
            ],
            [
                'title' => 'Absensi',
                'icon' => 'absensi.png',
                'link' => 'kbm/kehadiran'
            ],
            [
                'title' => 'Catatan Guru',
                'icon' => 'ic_teacher.png',
                'link' => 'siswa/catatan'
            ],
            [
                'title' => 'Arsip',
                'icon' => 'icon-arsip.gif',
                'link' => 'kbm/arsip'
            ],
            [
                'title' => 'E-Perpus',
                'icon' => 'ic_library.png',
                'link' => 'siswa/perpus'
            ],
        ];
        return json_decode(json_encode($box), FALSE);
    }

    public function index()
    {
        //$this->load->model('Master_model', 'master');
        //$this->load->model('Dashboard_model', 'dashboard');
        //$this->load->model('Elearning_model', 'elearning');
        //$this->load->model('Dropdown_model', 'dropdown');
        //$this->load->model('Cbt_model', 'cbt');

        $setting = $this->dashboard->getSetting();
        $user = $this->auth->user();

        $data = [
            'user' => $user,
            'judul' => 'Beranda',
            'subjudul' => 'Halaman Utama',
            'setting' => $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $kelass = ($tp && $smt) ? $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt) : [];

        $isUpdated = $this->isUpdated();
        $data['uptodate'] = $isUpdated;

        if (!$isUpdated->is_latest) {
            if ($this->auth->is_admin()) {
                redirect(base_url());
            } else {
                $this->load->view('maintenance');
            }
            return;
        }

        // Persiapan Jadwal Harian
        $id_hari = (int) date('w');
        $id_hari = $id_hari == 0 ? 7 : $id_hari;

        $jadwal_mapel = ($tp && $smt) ? $this->elearning->getJadwalHarian($tp->id_tp, $smt->id_smt, $id_hari) : [];
        $jadk = ($tp && $smt) ? $this->elearning->getJadwalKbm($tp->id_tp, $smt->id_smt) : [];

        if (empty($jadwal_mapel)) {
            foreach ($kelass as $keyKls => $kelas) {
                for ($i = 0; $i < 6; $i++) {
                    for ($j = 1; $j < 7; $j++) {
                        $jadwal_mapel[$keyKls][] = [
                            'dari' => '',
                            'sampai' => '',
                            'rows' => 1,
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
        $maxTime = "07:00";
        $libur = '7';
        $kelasKeys = array_keys($kelass);

        foreach ($jadk as $idKelas => $kelas) {
            if (!in_array($idKelas, $kelasKeys))
                continue;

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
            } catch (Exception $e) {
            }
        }

        $data['times'] = $timeSlots;
        $data['longdays'] = $longDay;
        $data['setting_kbm'] = ['mulai' => $minTime, 'selesai' => $maxTime, 'libur' => $libur];
        $data['mapels'] = $this->elearning->getAllKodeMapel();

        // Role: Siswa
        if ($this->auth->in_group('siswa')) {
            if (!$user) {
                $this->load->view('disable_login', $data);
                return;
            }
            $siswa = $this->dashboard->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
            if (!$siswa) {
                $this->load->view('disable_login', $data);
                return;
            } else {
                $data['login'] = $data['siswa'] = $siswa;
                $data['menu'] = $this->menu_siswa_box();
                $data['jadwals'] = $jadk[$siswa->id_kelas] ?? [];
                $data['running_text'] = $this->dashboard->getRunningText();
                $this->render_view(3, 'members/siswa/dashboard', $data);
                return;
            }
        }

        // Data umum untuk selain siswa
        $token = $this->cbt->getToken();
        $data['token'] = $token ?? (object) ['token' => '', 'auto' => '0', 'jarak' => '1', 'elapsed' => '00:00:00'];
        $data['ada_ujian'] = $this->cbt->getDataJadwalByTgl(date('Y-m-d'));
        $data['jadwal_kbm'] = $jadk ?: [];
        $data['mapels'] = $this->master->getAllMapel();
        $data['kelases'] = $kelass;

        // Role: Admin
        if ($this->auth->is_admin()) {
            $data['info_box'] = $this->admin_box($setting, $tp->id_tp, $smt->id_smt);
            $data['ujian_box'] = $this->ujian_box($tp->id_tp, $smt->id_smt);
            $data['is_profile_incomplete'] = false; // Prevent JS error
            $this->render_view(1, 'dashboard', $data);
            return;
        }

        // Role: Guru
        if ($this->auth->in_group('guru')) {
            if (!$user) {
                $this->load->view('disable_login', $data);
                return;
            }
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if (!$guru) {
                $this->load->view('disable_login', $data);
            } else {
                $data['session'] = $data['guru'] = $guru;
                $data['info_box'] = $this->admin_box($setting, $tp->id_tp, $smt->id_smt);
                $data['ujian_box'] = $this->ujian_box($tp->id_tp, $smt->id_smt);
                $data['is_profile_incomplete'] = false; // Prevent JS error
                $this->render_view(2, 'members/guru/dashboard', $data);
            }
            return;
        }

        // Role: Panitia
        if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            if (!$user) {
                $this->load->view('disable_login', $data);
                return;
            }
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if (!$panitia) {
                $this->load->view('disable_login', $data);
            } else {
                $data['panitia'] = $panitia;
                $data['is_profile_incomplete'] = false; // TODO: Define actual logic for incomplete profile
                $data['info_box'] = $this->panitia_box($tp->id_tp, $smt->id_smt);
                $data['ujian_box'] = []; // Ujian box data is now integrated into info_box or separate if you wish
                $this->render_view(4, 'members/panitia/dashboard', $data);
            }
            return;
        }

        show_error('Akses tidak diizinkan atau Role tidak dikenali. Session Role: ' . $this->session->userdata('role_name') . ' (ID: ' . $this->session->userdata('role') . ')', 403);
    }

    public function get_realtime_stats()
    {
        //$this->load->model('Master_model', 'master');
        //$this->load->model('Dashboard_model', 'dashboard');
        //$this->load->model('Cbt_model', 'cbt');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $setting = $this->dashboard->getSetting();
        session_write_close();

        if ($tp && $smt && $setting) {
            if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
                $data['info_box'] = $this->panitia_box($tp->id_tp, $smt->id_smt);
                $data['ujian_box'] = [];
            } else {
                $data['info_box'] = $this->admin_box($setting, $tp->id_tp, $smt->id_smt);
                $data['ujian_box'] = $this->ujian_box($tp->id_tp, $smt->id_smt);
            }
        } else {
            $data['info_box'] = [];
            $data['ujian_box'] = [];
        }
        $this->output_json($data);
    }

    public function get_status_siswa()
    {
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        session_write_close();

        $ruang_pengawas = null;
        if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $user = $this->auth->user();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($panitia && $panitia->jabatan === 'Pengawas' && !empty($panitia->ruang)) {
                $ruang_pengawas = $panitia->ruang;
            }
        }

        $jadwals = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,status,jam_ke')
            ->where('id_tp', $tp->id_tp)
            ->where('id_smt', $smt->id_smt)
            ->where('tgl_mulai <=', date('Y-m-d 23:59:59'))
            ->where('tgl_selesai >=', date('Y-m-d'))
            ->with_bank([
                'fields' => 'bank_kode,bank_kelas,soal_agama',
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt],
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel'],
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

        $data['panitias'] = [];
        if ($this->db->table_exists('master_panitia')) {
            $panitias = $this->db->get('master_panitia')->result();
            $panitia_map = [];
            foreach ($panitias as $p) {
                $panitia_map[$p->id_panitia] = $p->nama_panitia;
            }
            $data['panitias'] = $panitia_map;
        }

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
                if (!is_array($kelasIds)) {
                    $kelasIds = json_decode(json_encode($kelasIds), true);
                    if (!is_array($kelasIds))
                        $kelasIds = [];
                }
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
        if ($ruang_pengawas) {
            $ruangs = array_filter($ruangs, function ($r) use ($ruang_pengawas) {
                return $r->ruang_id == $ruang_pengawas;
            });
        }

        $siswas_query = $this->sesisiswa_model
            ->where('kelas_id', $arrKelas)
            ->where(['tp_id' => $tp->id_tp, 'smt_id' => $smt->id_smt]);

        if ($ruang_pengawas) {
            $siswas_query = $siswas_query->where('ruang_id', $ruang_pengawas);
        }

        $siswas = $siswas_query
            ->with_siswa(['fields' => 'id_siswa,nama,nis,nisn,agama'])
            ->with_peserta()
            ->with_kelas(['fields' => 'kode_kelas,nama_kelas'])
            ->with_ruang(['fields' => 'kode_ruang,nama_ruang'])
            ->with_sesi(['fields' => 'kode_sesi,nama_sesi'])
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
        $data['cbtsiswa'] = (array) $cbtsiswa;
        $data['ruangs'] = array_values($ruangs);
        $data['arr_kelas'] = $arrKelas;
        $data['jadwals_ujian'] = $result;

        $this->output_json($data);
    }

    public function check_version()
    {
        $isUpdated = $this->isUpdated();
        $this->output_json($isUpdated);
    }

    public function need_update()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'UPDATE',
            'subjudul' => 'Update Aplikasi',
            'setting' => $setting
        ];
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $isUpdated = $this->isUpdated();
        $data['uptodate'] = $isUpdated;
        if ($isUpdated->is_latest) {
            redirect('dashboard');
        }

        if ($this->auth->is_admin()) {
            $this->load->view('install/header', $data);
            $this->load->view('init');
            $this->load->view('install/footer');
        } else {
            $this->load->view('maintenance');
        }
    }

    public function checkTokenJadwal()
    {
        $this->load->model('Cbt_model', 'cbt');
        $ada_ujian = $this->cbt->getDataJadwalByTgl(date('Y-m-d'));
        $data['ada_ujian'] = count($ada_ujian);

        $token = $this->getValidToken(count($ada_ujian));
        $data['token'] = $token;
        $this->output_json($data);
    }

    public function gantiTahun()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $aktif = $this->input->post('active', true);
        $rows = count($this->input->post('tahun', true));

        for ($i = 0; $i <= $rows; $i++) {
            $id_tp = $this->input->post('id_tp[' . $i . ']', true);
            $tahun = $this->input->post('tahun[' . $i . ']', true);

            if ($id_tp == $aktif) {
                $active = 1;
            } else {
                $active = 0;
            }

            $update[] = array(
                'id_tp' => $id_tp,
                'tahun' => $tahun,
                'active' => $active
            );
        }

        $this->dashboard->update('master_tp', $update, 'id_tp', null, true);
        $data['update'] = $update;
        $data['status'] = true;
        $this->logging->saveLog(4, 'mengganti tahun ajaran aktif');
        $this->output_json($data);
    }

    public function gantiSemester()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $aktif = $this->input->post('active', true);
        $rows = count($this->input->post('smt', true));

        for ($i = 1; $i <= $rows; $i++) {
            $id_smt = $this->input->post('id_smt[' . $i . ']', true);
            $smt = $this->input->post('smt[' . $i . ']', true);

            if ($id_smt == $aktif) {
                $active = 1;
            } else {
                $active = 0;
            }

            $update[] = array(
                'id_smt' => $id_smt,
                'smt' => $smt,
                'active' => $active
            );
        }

        $this->dashboard->update('master_smt', $update, 'id_smt', null, true);
        $data['update'] = $update;
        $data['status'] = true;
        $this->logging->saveLog(4, 'mengganti semester aktif');
        $this->output_json($data);
    }

    public function getNotifikasi()
    {
    }

    public function getLog()
    {
        $this->load->model('Log_model', 'logging');

        $page = $this->input->get('page', true);
        $limit = $this->input->get('limit', true);
        $filter = $this->input->get('filter', true);
        $offset = ($page - 1) * $limit;
        $search = $this->input->get('search', true);

        session_write_close();

        $total = $this->logging->getCountLog($search, $filter);
        $logs = $this->logging->getLog($offset, $limit, $search, $filter);

        $this->addPhotos($logs, count($total));
    }

    private function addPhotos($logs, $total)
    {
        $ids_guru = [];
        $ids_siswa = [];
        $admins = [];
        foreach ($logs as $log) {
            if ($log->id_group == '2') {
                if (isset($log->id_user) && !in_array($log->id_user, $ids_guru)) {
                    $ids_guru[] = $log->id_user;
                }
            } elseif ($log->id_group == '3') {
                if (isset($log->id_user) && !in_array($log->id_user, $ids_siswa)) {
                    $ids_siswa[] = $log->id_user;
                }
            } else {
                if (isset($log->id_user) && !in_array($log->id_user, $admins)) {
                    $admins[] = $log->id_user;
                }
            }
        }
        if (count($ids_siswa) > 0) {
            $allSiswa = $this->auth->allSiswa($ids_siswa);
        }
        if (count($ids_guru) > 0) {
            $allGuru = $this->auth->allGuru($ids_guru);
        }
        if (count($admins) > 0) {
            $allAdmin = $this->auth->all_profiles($admins);
        }
        $result = [
            'siswa' => $allSiswa ?? [],
            'guru' => $allGuru ?? [],
            'logs' => array_values($logs),
            'admins' => $allAdmin ?? [],
            'total' => $total
        ];
        $this->output_json($result);
    }

    public function hapusLog($id_log)
    {
        $this->db->trans_start();
        if ($id_log == 'all') {
            if ($this->db->empty_table('log')) {
                $deleted = ['status' => true, 'message' => 'berhasil'];
            } else {
                $deleted = ['status' => false, 'message' => 'gagal'];
            }
        } elseif ($id_log == 'selected') {
            $ids = $this->input->get('ids', true);
            $this->db->where_in('id_log', $ids);
            $deleted = $this->db->delete('log');
        } else {
            $this->db->where('id_log', $id_log);
            $deleted = $this->db->delete('log');
        }
        $this->db->trans_complete();
        $this->output_json($deleted);
    }

    public function getLogSiswa($limit)
    {
        session_write_close();
        $this->load->model('Log_model', 'logging');
        $this->output_json($this->logging->loadAktifitasSiswa($limit));
    }

    public function getPengumuman($for)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->output_json($this->dashboard->loadPengumuman($for));
    }

    public function getJadwalHariIni($id_kelas, $id_hari)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $this->output_json($this->dashboard->loadJadwalHariIni($tp->id_tp, $smt->id_smt, $id_kelas, $id_hari));
    }

    public function getJadwalKbm($id_kelas)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $jadwal = $this->dashboard->getJadwalKbm($tp->id_tp, $smt->id_smt, $id_kelas);

        if (isset($jadwal->istirahat)) {
            $jadwal->istirahat = $this->maybe_unserialize($jadwal->istirahat);
        }
        $istirahat = $jadwal->istirahat ?? [];

        $this->output_json(array('jadwal' => $jadwal, 'istirahat' => $istirahat));
    }

    /**
     * Get sync status for dashboard widget (admin only)
     */
    public function get_sync_status()
    {
        if (!$this->auth->is_admin()) {
            $this->output_json(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        // Check if sync tables exist
        if (!$this->db->table_exists('sync_config')) {
            $this->output_json([
                'enabled' => false,
                'message' => 'Sync belum dikonfigurasi'
            ]);
            return;
        }

        $this->load->model('Sync_model');

        $config = $this->Sync_model->get_config();

        if (!$config) {
            $this->output_json([
                'enabled' => false,
                'message' => 'Sync belum dikonfigurasi'
            ]);
            return;
        }

        $status = $this->Sync_model->get_sync_status();

        $data = [
            'enabled' => true,
            'server_type' => $config->server_type,
            'is_pusat' => $config->server_type === 'pusat',
            'auto_sync' => (bool) $config->auto_sync,
            'last_download' => $config->last_sync_download,
            'last_upload' => $config->last_sync_upload,
            'pending_upload' => $status['pending_upload'] ?? 0,
            'queue_count' => $status['queue_count'] ?? 0,
            'connection' => null
        ];

        // Test connection for local servers
        if ($config->server_type === 'lokal' && !empty($config->pusat_url)) {
            $connection = $this->Sync_model->test_connection();
            $data['connection'] = $connection['success'] ?? false;
        }

        // For pusat server, count registered schools
        if ($config->server_type === 'pusat') {
            $data['school_count'] = $this->Sync_model->count_schools();
        }

        $this->output_json($data);
    }
}
