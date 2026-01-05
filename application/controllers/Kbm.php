<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kbm extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->library('user_agent');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function index()
    {

    }

    public function jadwal_pelajaran()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Elearning_model', 'elearning');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Jadwal Pelajaran',
            'subjudul' => 'Set Jadwal Pelajaran',
            'setting' => $setting
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/elearning/jadwal', $data);
    }

    public function get_jadwal_harian()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model', 'elearning');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $kelas = $this->elearning->getJadwalKbmByKelas($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $mapel = $this->elearning->getJadwalPerkelas($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        $timeSlots = [];
        if ($kelas == null) {
            $data['jadwal_kbm'] = json_decode(json_encode([
                'id_tp' => $tp->tahun,
                'id_smt' => $smt->smt,
                'id_kelas' => $siswa->id_kelas,
                'kbm_jam_pel' => '',
                'kbm_jam_mulai' => '',
                'kbm_jml_mapel_hari' => '',
                'istirahat' => json_encode([]),
                'ada' => false
            ]));
        } else {
            $minTime = "07:00";
            $maxTime = "08:00";
            $libur = '7';
            $kelas->upacara = $this->maybe_unserialize($kelas->upacara ?? '{}');

            if ($kelas->kbm_jam_selesai > $maxTime) {
                $maxTime = $kelas->kbm_jam_selesai;
            }
            if ($kelas->kbm_jam_mulai < $minTime) {
                $minTime = $kelas->kbm_jam_mulai;
                $libur = $kelas->libur;
            }
            $details = [];
            $lastDari = '23:50';
            $lastSampai = '01:00';
            $latestMpl = '0';
            $lastDay = '';
            foreach ($mapel as $hari => $jadwals) {
                $details[$hari] = [];
                foreach ($jadwals as $jadwal) {
                    $durasiMapel = round(abs(strtotime($jadwal->sampai ?? $lastSampai) - strtotime($jadwal->dari ?? $lastDari)) / 60, 2);
                    if ($latestMpl !== '0' && $jadwal->id_mapel == $latestMpl && $jadwal->id_hari == $lastDay) {
                        $details[$hari][$lastDari]['rows'] += (isset($jadwal->dari) ? $durasiMapel / 5 : 1);
                        $details[$hari][$lastDari]['sampai'] = $jadwal->sampai ?? $lastSampai;
                    } else {
                        $lastDay = $jadwal->id_hari ?? '0';
                        $latestMpl = $jadwal->id_mapel ?? '0';
                        $details[$hari][$jadwal->dari ?? $lastDari] = [
                            'id_jadwal' => $jadwal->id_jadwal ?? '0',
                            'dari' => $jadwal->dari ?? $lastDari,
                            'sampai' => $jadwal->sampai ?? $lastSampai,
                            'rows' => isset($jadwal->dari) ? $durasiMapel / 5 : 1,
                            'id_kelas' => $jadwal->id_kelas ?? '',
                            'id_hari' => $jadwal->id_hari ?? '',
                            'id_mapel' => $jadwal->id_mapel ?? '0',
                            'kode' => $jadwal->kode ?? ''
                        ];
                    }
                    $lastDari = $jadwal->dari ?? $lastDari;
                    $lastSampai = $jadwal->sampai ?? $lastSampai;
                }
            }
            unset($kelas->istirahat);
            $kelas->detail = $details;

            $longDay = round(abs(strtotime($maxTime) - strtotime($minTime)) / 60, 2) + 5;
            $startTime = new DateTime($minTime);
            $currentTime = clone $startTime;

            for ($m = 0; $m < ($longDay / 5); $m++) {
                try {
                    $currentTime->add(new DateInterval('PT5M'));
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
            // Akhir timeline
            $data['jadwal_kbm'] = $kelas;
        }
        $data['times'] = $timeSlots;
        $this->output_json($data);
    }

    public function materi()
    {
        $this->getTugasMateri('1');
    }

    public function tugas()
    {
        $this->getTugasMateri('2');
    }

    private function getTugasMateri($jenis)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Elearning_model', 'elearning');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => $jenis == '1' ? 'Materi' : 'Tugas',
            'subjudul' => $jenis == '1' ? 'materi' : 'tugas',
            'setting' => $setting
        ];
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['jenis'] = $jenis;
        $data['jurusan'] = $this->dropdown->getAllJurusan();
        $data['level'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/elearning/materi_jadwal', $data);
    }

    public function get_jadwal()
    {
        $this->load->model('Elearning_model', 'elearning');

        $id_kelas = $this->input->get('kelas', true);
        $id_siswa = $this->input->get('siswa', true);
        $jenis = $this->input->get('jenis', true);

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $now = new DateTime();
        $id_hari = $now->format('w');
        $id_hari = $id_hari == 0 ? 7 : $id_hari;
        //$id_hari = '1';
        $jadwal_mapel = $this->elearning->getJadwalHarian($tp->id_tp, $smt->id_smt, $id_hari, $id_kelas);
        $jadwal_materi = $this->elearning->getAllMateriByTgl($id_kelas, $now->format('Y-m-d'));
        $data['materis'] = $jadwal_materi;

        $jadwal_kbm = $this->elearning->getJadwalKbmByKelas($tp->id_tp, $smt->id_smt, $id_kelas);
        if ($jadwal_kbm) {
            unset($jadwal_kbm->istirahat);
        } else {
            $jadwal_kbm = new stdClass();
            $jadwal_kbm->libur = '7';
        }
        $jadwal_kbm->detail = [];
        $log_siswa = $this->elearning->getLogMateriSiswa($id_siswa);

        $lastDari = '23:59';
        $lastSampai = '01:00';
        $latestMpl = '0';
        $lastDay = '';

        $mapels = $jadwal_mapel[$id_kelas] ?? [];
        $details = [];
        foreach ($mapels as $jadwal) {
            $dari = $jadwal->dari;
            $durasiMapel = round(abs(strtotime($jadwal->sampai ?? $lastSampai) - strtotime($jadwal->dari ?? $lastDari)) / 60, 2);
            if (
                $latestMpl !== '0' &&
                $jadwal->id_mapel == $latestMpl &&
                $jadwal->id_hari == $lastDay && $lastDari == $jadwal->dari
            ) {
                if (!isset($details[$lastDari]['rows'])) {
                    $details[$lastDari]['rows'] = 0;
                }
                $details[$lastDari]['rows'] += (isset($jadwal->dari) ? $durasiMapel / 5 : 1);
                $details[$lastDari]['sampai'] = $jadwal->sampai ?? $lastSampai;
            } else {
                $lastDay = $jadwal->id_hari ?? '0';
                $latestMpl = $jadwal->id_mapel ?? '0';
                $filter_materi = array_filter($jadwal_materi, function ($val) use ($jenis, $jadwal) {
                    $tgl_jadwal = new DateTime($val->jadwal_materi);
                    $id_hari = $tgl_jadwal->format('w');
                    $id_hari = $id_hari == 0 ? 7 : $id_hari;
                    return (
                        $jadwal->id_hari == $id_hari
                        and $val->id_mapel == $jadwal->id_mapel
                        and $val->id_kelas == $jadwal->id_kelas
                        and $val->jenis == $jenis
                    );
                });
                $log = [];
                foreach ($filter_materi as $film) {
                    if ($film->jenis == $jenis)
                        $log[] = $log_siswa[$film->id_kjm] ?? [];
                }
                $details[$jadwal->dari ?? $lastDari] = [
                    'id_jadwal' => $jadwal->id_jadwal ?? '0',
                    'dari' => $jadwal->dari ?? $lastDari,
                    'sampai' => $jadwal->sampai ?? $lastSampai,
                    'rows' => isset($jadwal->dari) ? $durasiMapel / 5 : 1,
                    'id_kelas' => $jadwal->id_kelas ?? '',
                    'id_hari' => $jadwal->id_hari ?? '',
                    'id_mapel' => $jadwal->id_mapel ?? '0',
                    'kode' => $jadwal->kode ?? '',
                    'mapel' => $jadwal->nama_mapel,
                    'materi' => array_values($filter_materi),
                    'log' => $log,
                    'durasi' => $durasiMapel,
                    'now' => $now->format('Hi')
                ];
            }
            if ($jadwal->dari != $lastDari) {
                $lastDari = $jadwal->dari;
            }
            if ($jadwal->sampai != $lastSampai) {
                $lastSampai = $jadwal->sampai;
            }
        }

        $jadwal_kbm->detail = $details;
        $data['jadwal_materi'] = $jadwal_kbm;

        $dates = [];
        $dayNames = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
        $monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

        $libur = $jadwal_kbm->libur == '7' ? 0 : $jadwal_kbm->libur;
        for ($i = -15; $i <= 15; $i++) {
            $date = clone $now;
            $date->modify("$i days");

            $dayName = $dayNames[$date->format('w')];
            $day = $date->format('j');
            $monthName = $monthNames[$date->format('n') - 1];
            $dateString = $date->format('Y-m-d');

            $today = $dateString == $now->format('Y-m-d');
            $is_libur = false;
            if ($date->format('w') == $libur) {
                $is_libur = true;
            }
            $dates[] = [
                'hari' => $dayName,
                'tgl' => $day,
                'bln' => $monthName,
                'today' => $today,
                'libur' => $is_libur
            ];
        }
        $data['dates'] = $dates;
        $data['libur'] = $libur;
        $this->output_json($data);
    }

    public function materi_tugas_harian()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Elearning_model', 'elearning');

        $tanggal = $this->input->post('tgl', true);
        $id_siswa = $this->input->post('id_siswa', true);
        $tp = $this->input->post('tp', true);
        $smt = $this->input->post('smt', true);
        $jenis = $this->input->post('jenis', true);
        if ($jenis) {
            $id_kelas = $this->input->post('id_kelas', true);
            $id_hari = $this->input->post('id_hari', true);
        } else {
            $kelas_siswa = $this->elearning->getSiswaKelas($id_siswa, $tp, $smt);
            $id_kelas = $kelas_siswa->id_kelas ?? '"-"';
            $id_hari = date('N', strtotime($tanggal ?? ''));
        }

        $jadwal_mapel = $this->elearning->getJadwalHarian($tp, $smt, $id_hari, $id_kelas);
        $jadwal_materi = $this->elearning->getAllMateriByTgl($id_kelas, $tanggal);
        $jadwal_kbm = $this->elearning->getJadwalKbmByKelas($tp, $smt, $id_kelas);
        $log_siswa = $this->elearning->getLogMateriSiswa($id_siswa);

        $lastDari = '23:59';
        $lastSampai = '01:00';
        $latestMpl = '0';
        $lastDay = '';

        $mapels = $jadwal_mapel[$id_kelas] ?? [];
        $details = [];
        foreach ($mapels as $jadwal) {
            $durasiMapel = round(abs(strtotime($jadwal->sampai ?? $lastSampai) - strtotime($jadwal->dari ?? $lastDari)) / 60, 2);
            if ($latestMpl !== '0' && $jadwal->id_mapel == $latestMpl && $jadwal->id_hari == $lastDay) {
                if (!isset($details[$lastDari]['rows'])) {
                    $details[$lastDari]['rows'] = 0;
                }
                $details[$lastDari]['rows'] += (isset($jadwal->dari) ? $durasiMapel / 5 : 1);
                $details[$lastDari]['sampai'] = $jadwal->sampai ?? $lastSampai;
            } else {
                $lastDay = $jadwal->id_hari ?? '0';
                $latestMpl = $jadwal->id_mapel ?? '0';
                $log = [];
                if ($jenis) {
                    $filter_materi = array_filter($jadwal_materi, function ($val) use ($jenis, $jadwal) {
                        $tgl_jadwal = new DateTime($val->jadwal_materi);
                        $id_hari = $tgl_jadwal->format('w');
                        $id_hari = $id_hari == 0 ? 7 : $id_hari;
                        return ($val->jenis == $jenis
                            and $jadwal->id_hari == $id_hari
                            and $val->id_mapel == $jadwal->id_mapel
                            and $val->id_kelas == $jadwal->id_kelas);
                    });
                    foreach ($filter_materi as $film) {
                        if ($film->jenis == $jenis)
                            $log[] = $log_siswa[$film->id_kjm] ?? [];
                    }
                } else {
                    $filter_materi = array_filter($jadwal_materi, function ($val) use ($jadwal) {
                        $tgl_jadwal = new DateTime($val->jadwal_materi);
                        $id_hari = $tgl_jadwal->format('w');
                        $id_hari = $id_hari == 0 ? 7 : $id_hari;
                        return ($jadwal->id_hari == $id_hari
                            and $val->id_mapel == $jadwal->id_mapel
                            and $val->id_kelas == $jadwal->id_kelas);
                    });
                    foreach ($filter_materi as $film) {
                        $log[] = $log_siswa[$film->id_kjm] ?? [];
                    }
                }
                $details[$jadwal->dari ?? $lastDari] = [
                    'id_jadwal' => $jadwal->id_jadwal ?? '0',
                    'dari' => $jadwal->dari ?? $lastDari,
                    'sampai' => $jadwal->sampai ?? $lastSampai,
                    'rows' => isset($jadwal->dari) ? $durasiMapel / 5 : 1,
                    'id_kelas' => $jadwal->id_kelas ?? '',
                    'id_hari' => $jadwal->id_hari ?? '',
                    'id_mapel' => $jadwal->id_mapel ?? '0',
                    'kode' => $jadwal->kode ?? '',
                    'materi' => array_values($filter_materi),
                    'log' => $log
                ];
            }
            $lastDari = $jadwal->dari ?? $lastDari;
            $lastSampai = $jadwal->sampai ?? $lastSampai;
        }

        if ($jadwal_kbm) {
            unset($jadwal_kbm->istirahat);
            $jadwal_kbm->detail = $details;
            $jadwal_kbm->tgl = $tanggal;
        }
        $this->output_json($jadwal_kbm);
    }

    public function bukaMateri($id_kjm)
    {
        $this->load->model('Log_model', 'logging');
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Membuka Materi');
        $this->bukaTugasMateri($id_kjm, '1');
    }

    public function bukaTugas($id_kjm)
    {
        $this->load->model('Log_model', 'logging');
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Membuka Tugas');
        $this->bukaTugasMateri($id_kjm, '2');
    }

    private function bukaTugasMateri($id_kjm, $jenis)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model', 'elearning');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => $jenis == '1' ? 'Materi' : 'Tugas',
            'subjudul' => 'Kerjakan',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $materi = $this->elearning->getMateriKelasSiswa($id_kjm, $jenis);
        $materi->file = $this->maybe_unserialize($materi->file ?? '[]');
        $data['materi'] = $materi;
        $logs = $this->elearning->getStatusMateriSiswa($id_kjm);
        if (isset($logs[$siswa->id_siswa])) {
            $logs[$siswa->id_siswa]->file = $this->maybe_unserialize($logs[$siswa->id_siswa]->file ?? '');
        }

        $data['kjm'] = $id_kjm;
        $data['logs'] = isset($logs[$siswa->id_siswa]) ? $logs[$siswa->id_siswa] : null;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/elearning/materi_detail', $data);
    }

    public function saveLogMateri()
    {
        $this->load->model('Elearning_model', 'elearning');
        $this->load->model('Kelas_model', 'kelas');
        $id_siswa = $this->input->get('id_siswa', true);
        $id_kjm = $this->input->get('id_kjm', true);
        $mapel = $this->input->get('mapel', true);

        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_materi', $id_kjm);
        $q = $this->db->get('log_materi');
        $is_update = $q->num_rows() > 0;
        $this->output_json($this->elearning->saveLog('log_materi', $id_siswa, $id_kjm, $mapel, 'Membuka materi', $is_update));
    }

    public function saveLogTugas()
    {
        $this->load->model('Elearning_model', 'elearning');
        $this->load->model('Kelas_model', 'kelas');
        $id_siswa = $this->input->get('id_siswa', true);
        $id_kjm = $this->input->get('id_kjm', true);
        $mapel = $this->input->get('mapel', true);

        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_materi', $id_kjm);
        $q = $this->db->get('log_materi');
        $is_update = $q->num_rows() > 0;

        $this->output_json($this->elearning->saveLog('log_materi', $id_siswa, $id_kjm, $mapel, 'Membuka tugas', $is_update));
    }

    public function saveFileMateriSelesai()
    {
        $this->load->model('Log_model', 'logging');
        $id_siswa = $this->input->post('id_siswa', true);
        $id_kjm = $this->input->post('id_kjm', true);
        $isi_materi = $this->input->post('isi_materi', true);
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

        $id_log = $id_siswa . $id_kjm;
        $insert = [
            'id_siswa' => $id_siswa,
            'id_materi' => $id_kjm,
            'finish_time' => date('Y-m-d H:i:s'),
            'log_desc' => 'Menyelesaikan materi',
            'text' => $isi_materi,
            'file' => json_encode($src_file)
        ];

        $this->db->where('id_log', $id_log);
        $q = $this->db->get('log_materi');

        $update = 1;
        if ($q->num_rows() > 0) {
            $this->db->where('id_log', $id_log);
            $update = $this->db->update('log_materi', $insert);
        } else {
            $this->db->set('id_log', $id_log);
            $update = $this->db->insert('log_materi', $insert);
        }
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menyelesaikan Materi');

        $data['status'] = $update;
        $this->output_json($data);
    }

    public function saveFileTugasSelesai()
    {
        $this->load->model('Log_model', 'logging');

        $id_siswa = $this->input->post('id_siswa', true);
        $id_kjm = $this->input->post('id_kjm', true);
        $isi_tugas = $this->input->post('isi_tugas', true);
        $jamke = $this->input->post('jamke', true);
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

        $id_log = $id_siswa . $id_kjm;
        $insert = [
            'id_siswa' => $id_siswa,
            'id_materi' => $id_kjm,
            'jam_ke' => $jamke,
            'log_desc' => 'Menyelesaikan tugas',
            'text' => $isi_tugas,
            'file' => json_encode($src_file),
        ];

        $this->db->where('id_log', $id_log);
        $q = $this->db->get('log_tugas');

        if ($q->num_rows() > 0) {
            $this->db->where('id_log', $id_log);
            $update = $this->db->update('log_tugas', $insert);
        } else {
            $this->db->set('id_log', $id_log);
            $update = $this->db->insert('log_tugas', $insert);
        }
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menyelesaikan Tugas');

        $data['status'] = $update;
        $this->output_json($data);
    }

    public function kehadiran()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model', 'elearning');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Absensi',
            'subjudul' => 'Kehadiran Siswa',
            'setting' => $this->dashboard->getSetting()
        ];

        //$date = new DateTime('2024-08-12');
        $date = new DateTime();
        $hari = $date->format('w');
        $today = $date->format('Y-m-d');

        $info = $this->elearning->getJadwalKbmByKelas($tp->id_tp, $smt->id_smt, $siswa->id_kelas);
        unset($info->istirahat);

        $jadwals = $this->elearning->loadJadwalHariIni($tp->id_tp, $smt->id_smt, $siswa->id_kelas, $hari);
        $arrIdMapel = [];
        $latestMpl = '0';
        $lastInd = -1;
        $lastDay = '';

        foreach ($jadwals as $ind => $jad) {
            $arrIdMapel[] = $jad->id_mapel;
            if ($latestMpl !== '0' && $jad->id_mapel == $latestMpl && $jad->id_hari == $lastDay) {
                $jadwals[$lastInd]->sampai = $jad->sampai;
                unset($jadwals[$ind]);
            } else {
                $lastInd = $ind;
                $lastDay = $jad->id_hari;
                $latestMpl = $jad->id_mapel ?? '0';
            }
        }
        $jadwals = array_values($jadwals);
        usort($jadwals, function ($a, $b) {
            return $a->dari <=> $b->dari;
        });

        $jadwal_materi = [];
        if (count($arrIdMapel) > 0) {
            $jadwal_materi = $this->elearning->getAllMateriByTgl($siswa->id_kelas, $today, $arrIdMapel);
        }
        $data['jadwal_materi'] = $jadwal_materi;

        $arrIdKjm = [];
        foreach ($jadwal_materi as $jmtr) {
            $arrIdKjm[] = $jmtr->id_kjm;
        }
        $log = count($arrIdKjm) > 0 ? $this->elearning->getRekapMateriKelas($siswa->id_kelas, $arrIdKjm, $siswa->id_siswa) : [];
        $data['log'] = $log;
        foreach ($jadwal_materi as $materi_item) {
            $materi_item->materi_kelas = $this->maybe_unserialize($materi_item->materi_kelas ?? '[]');
            $filter = array_filter($log, function ($val) use ($materi_item) {
                return $val->id_materi == $materi_item->id_kjm;
            });
            $log_siswa = count($filter) > 0 ? array_values($filter)[0] : null;

            $idx_jad = array_search($materi_item->id_mapel, array_column($jadwals, 'id_mapel'));
            $jam_materi = $idx_jad >= 0 ? $jadwals[$idx_jad] : null;
            $status = [
                'status' => '',
                'terlambat' => false,
            ];
            if ($log_siswa) {
                $mulai = $log_siswa->log_time ?? null;
                $selesai = $log_siswa->finish_time ?? null;
                $nilai = $log_siswa->nilai ?? null;

                if ($jam_materi) {
                    if (is_null($mulai)) {
                        $status['status'] = 'Blm dikerjakan';
                    } elseif (is_null($selesai)) {
                        $status['status'] = 'Belum selesai';
                    } elseif (is_null($nilai)) {
                        $status['status'] = 'Menunggu dikoreksi';
                    } elseif ($nilai == '0') {
                        $status['status'] = 'Diulang';
                    } elseif ($nilai > 0) {
                        //$jam_materi = $jadwals[$idx_jad];
                        $jam_jadwal = new DateTime(date('Y-m-d H:i:s', strtotime(($materi_item->jadwal_materi ?? '') . ' ' . $jam_materi->sampai)));
                        $jam_siswa = new DateTime(date('Y-m-d H:i:s', strtotime($mulai ?? '')));
                        $interval = $jam_siswa->diff($jam_jadwal);
                        $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                        $time_jadwal = strtotime(($materi_item->jadwal_materi ?? '') . ' ' . $jam_materi->sampai);
                        $time_siswa = strtotime($mulai ?? '');
                        if (($time_siswa - $time_jadwal) > 0) {
                            $status['terlambat'] = true;
                            $status['status'] = 'Terlambat';

                            $ketMulai = '';
                            if ($interval->days > 0) {
                                $ketMulai .= $interval->days . ' hari ';
                            }
                            if ($interval->h > 0) {
                                $ketMulai .= $interval->h . ' jam ';
                            }
                            if ($interval->i > 0) {
                                $ketMulai .= $interval->i . ' mnt';
                            }
                            $status['ket'] = $ketMulai;
                        } else {
                            $status['status'] = 'Selesai';
                        }
                    }
                }
            } else {
                $status = [
                    'status' => 'Belum dikerjakan',
                    'terlambat' => false,
                ];
            }
            $materi_item->status = (object) $status;
        }

        foreach ($jadwals as $jadwal) {
            $materi = array_filter($jadwal_materi, function ($mtr) use ($jadwal) {
                return $mtr->id_mapel == $jadwal->id_mapel and $mtr->jenis == '1';
            });
            $jadwal->materi = array_values($materi);

            $tugas = array_filter($jadwal_materi, function ($mtr) use ($jadwal) {
                return $mtr->id_mapel == $jadwal->id_mapel and $mtr->jenis == '2';
            });
            $jadwal->tugas = array_values($tugas);
        }
        $data['jadwals'] = array_values($jadwals);

        if ($smt->id_smt == '1') {
            $arrBulan = [
                '07' => "Juli",
                '08' => "Agustus",
                '09' => "September",
                '10' => "Oktober",
                '11' => "Nopember",
                '12' => "Desember"
            ];
        } else {
            $arrBulan = [
                '01' => "Januari",
                '02' => "Februari",
                '03' => "Maret",
                '04' => "April",
                '05' => "Mei",
                '06' => "Juni"
            ];
        }
        $data['bulans'] = $arrBulan;
        $data['kbm'] = $info;
        $namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
        $split = explode('-', $today);
        if (isset($arrBulan[$split[1]])) {
            $data['today'] = $namaHari[$hari] . ', ' . $split[2] . ' ' . $arrBulan[$split[1]] . ' ' . $split[0];
        } else {
            $data['today'] = '';
        }

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/elearning/kehadiran', $data);
    }

    public function load_absensi_bulanan()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Elearning_model', 'elearning');

        $id_kelas = $this->input->post('id_kelas', true);
        $id_siswa = $this->input->post('id_siswa', true);
        $tahun = date('Y');
        $bulan = $this->input->post('bulan', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;

        $mapels = $this->master->getAllMapel();
        $arrIdMapel = [];
        foreach ($mapels as $mpl) {
            $arrIdMapel[] = $mpl->id_mapel;
        }
        $jadwal = $this->elearning->getJadwalKbmByKelas($id_tp, $id_smt, $id_kelas);
        $libur = isset($jadwal->libur) && $jadwal->libur != '0' ? $jadwal->libur : '7';
        if ($jadwal != null) {
            unset($jadwal->istirahat);
            $arrTgl = [];
            $tgl = date('t', strtotime("$tahun-$bulan-01"));
            for ($i = 0; $i < $tgl; $i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $arrTgl[] = $tahun . '-' . $bulan . '-' . $t;
            }
            $jadwal_materi = $this->elearning->getAllMateriByArrTgl($id_kelas, $arrTgl, $arrIdMapel);
            $logs_perbulan = $this->elearning->getRekapBulananSiswa($arrIdMapel, $id_kelas, $tahun, $bulan, $id_siswa);

            $infos = $this->elearning->getJadwalMapelByMapel($id_tp, $id_smt, null, $id_kelas);
            $latestMpl = '0';
            $lastInd = -1;
            $lastDay = '';

            foreach ($infos as $ind => $info) {
                if ($latestMpl !== '0' && $info->id_mapel == $latestMpl && $info->id_hari == $lastDay) {
                    if (isset($infos[$lastInd])) {
                        $infos[$lastInd]->sampai = $info->sampai;
                        unset($infos[$ind]);
                    }
                } else {
                    $lastInd = $ind;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';
                }
            }
            $infos = array_values($infos);

            $namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
            $dataJadwal = [];
            foreach ($arrTgl as $tanggal) {
                $date = new DateTime($tanggal);
                $hari = $date->format('w');
                $hari = $hari == 0 ? 7 : $hari;

                $filter_jad = array_filter($infos, function ($val) use ($hari) {
                    return ($val->id_hari == $hari);
                });

                $dataJadwal[] = [
                    'is_libur' => $libur == $hari,
                    'id_hari' => $hari,
                    'tanggal' => $tanggal,
                    'hari' => $namaHari[$hari],
                    'jadwal' => array_values($filter_jad),
                ];
            }
            $this->output_json([
                "jadwal" => $jadwal,
                "jadwal_materi" => $jadwal_materi,
                "logs" => $logs_perbulan,
                //"info" => $infos,
                "jadwal_mapel" => $dataJadwal
            ]);
        } else {
            $this->output_json(["jadwal" => []]);
        }
    }

    public function hasil()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model', 'elearning');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Nilai',
            'subjudul' => 'Nilai Hasil Belajar',
            'setting' => $this->dashboard->getSetting()
        ];
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['id_siswa'] = $siswa->id_siswa;

        $logs = $this->elearning->getNilaiMateriSiswa($siswa->id_siswa);
        $data['log'] = $logs;
        $data['nilai_materi'] = $logs[1] ?? [];
        $data['nilai_tugas'] = $logs[2] ?? [];

        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/elearning/nilai', $data);
    }

    public function arsip()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Elearning_model', 'elearning');
        $this->load->model('Dropdown_model', 'dropdown');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Arsip Materi/Tugas',
            'subjudul' => 'Lihat Materi/Tugas',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['mapels'] = $this->dropdown->getAllMapel();
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/elearning/arsip', $data);
    }

    public function materi_bulanan()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Elearning_model', 'elearning');

        $id_kelas = $this->input->post('id_kelas', true);
        $id_siswa = $this->input->post('id_siswa', true);
        $tahun = date('Y');
        $bulan = $this->input->post('bulan', true);

        $id_tp = $this->master->getTahunActive()->id_tp;
        $id_smt = $this->master->getSemesterActive()->id_smt;

        $mapels = $this->master->getAllMapel();
        $arrIdMapel = [];
        foreach ($mapels as $mpl) {
            $arrIdMapel[] = $mpl->id_mapel;
        }
        $jadwal = $this->elearning->getJadwalKbmByKelas($id_tp, $id_smt, $id_kelas);
        $libur = isset($jadwal->libur) && $jadwal->libur != '0' ? $jadwal->libur : '7';
        if ($jadwal != null) {
            unset($jadwal->istirahat);
            $arrTgl = [];
            //$tgl = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
            $tgl = date('t', strtotime("$tahun-$bulan-01"));
            for ($i = 0; $i < $tgl; $i++) {
                $t = ($i + 1) < 10 ? '0' . ($i + 1) : $i + 1;
                $arrTgl[] = $tahun . '-' . $bulan . '-' . $t;
            }
            $jadwal_materi = $this->elearning->getAllMateriByArrTgl($id_kelas, $arrTgl, $arrIdMapel);
            $logs_perbulan = $this->elearning->getRekapBulananSiswa($arrIdMapel, $id_kelas, $tahun, $bulan, $id_siswa);

            $infos = $this->elearning->getJadwalMapelByMapel($id_tp, $id_smt, null, $id_kelas);
            $latestMpl = '0';
            $lastInd = -1;
            $lastDay = '';

            foreach ($infos as $ind => $info) {
                if ($latestMpl !== '0' && $info->id_mapel == $latestMpl && $info->id_hari == $lastDay) {
                    if (isset($infos[$lastInd])) {
                        $infos[$lastInd]->sampai = $info->sampai;
                        unset($infos[$ind]);
                    }
                } else {
                    $lastInd = $ind;
                    $lastDay = $info->id_hari;
                    $latestMpl = $info->id_mapel ?? '0';
                }
            }
            $infos = array_values($infos);

            $namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
            $dataJadwal = [];
            foreach ($arrTgl as $tanggal) {
                $date = new DateTime($tanggal);
                $hari = $date->format('w');
                $hari = $hari == 0 ? 7 : $hari;

                $filter_jad = array_filter($infos, function ($val) use ($hari) {
                    return ($val->id_hari == $hari);
                });

                $dataJadwal[] = [
                    'is_libur' => $libur == $hari,
                    'id_hari' => $hari,
                    'tanggal' => $tanggal,
                    'hari' => $namaHari[$hari],
                    'jadwal' => array_values($filter_jad),
                ];
            }
            $this->output_json([
                "jadwal" => $jadwal,
                "jadwal_materi" => $jadwal_materi,
                "logs" => $logs_perbulan,
                //"info" => $infos,
                "jadwal_mapel" => $dataJadwal
            ]);
        } else {
            $this->output_json(["jadwal" => []]);
        }
    }

    public function get_all_nilai()
    {
        $this->load->model('Elearning_model', 'elearning');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $user = $this->auth->user();
        $siswa = $this->elearning->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        $id_siswa = $siswa->id_siswa;
        if ($siswa->id_siswa == null) {
            $this->output_json(['status' => false, 'message' => 'Belum terdaftar dalam kelas']);
            return;
        } else {
            unset($siswa->password);
        }

        $all_jadwal = $this->cbt_jadwal_model
            ->fields('id_jadwal,tgl_mulai,tgl_selesai,hasil_tampil')
            ->with_bank([
                'fields' => 'bank_kelas,soal_agama,bank_kode',
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel', 'where' => []],
                    ['relation' => 'guru', 'fields' => 'kode_guru,nama_guru', 'where' => []],
                ]
            ])
            ->with_cbtsiswa(['fields' => 'id_siswa,mulai,selesai', 'where' => ['id_siswa' => $id_siswa]])
            ->with_jenis()
            ->as_array()
            ->get_all();

        if ($all_jadwal && count((array) $all_jadwal) > 0) {
            $bank_kelas = [];
            foreach ($all_jadwal as $key => $jadwal) {
                $kelasIds = $this->maybe_unserialize($jadwal['bank']->bank_kelas ?? []);
                $kelass = array_map('strval', array_column($kelasIds, 'kelas_id'));
                if (!in_array($siswa->id_kelas, $kelass)) {
                    unset($all_jadwal[$key]);
                } else {
                    $all_jadwal[$key]['bank']->bank_kelas = $kelasIds;
                    $bank_kelas[$jadwal['id_jadwal']] = $kelass;
                }
            }

            $data['all_jadwal'] = array_values((array) $all_jadwal);
            $data['bank_kelas'] = $bank_kelas;
        }
        $data['siswa'] = $siswa;
        $data['id_siswa'] = $id_siswa;
        $this->output_json($data);
    }

    public function get_nilai_jadwal()
    {
        // Bypass CI buffering for manual JSON output
        if (ob_get_length())
            ob_clean();
        header('Content-Type: application/json');

        try {
            $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
            $id_siswa = $this->input->get('siswa', true);
            $id_jadwal = $this->input->get('jadwal', true);

            if (empty($id_siswa) || empty($id_jadwal)) {
                throw new \Exception("Parameter request tidak lengkap");
            }

            $jadwal = $this->cbt_jadwal_model
                ->fields('id_jadwal')
                ->with_bank(['fields' => 'bank_kelas,soal_agama,bank_kode'])
                ->get($id_jadwal);

            if (!$jadwal)
                throw new \Exception("Data jadwal tidak ditemukan");

            $tp_smt = $this->getTpSmt();
            $tp = $tp_smt->tp_active;
            $smt = $tp_smt->smt_active;

            $nilai = $this->olah_nilai(2, $tp->id_tp, $smt->id_smt, $id_siswa, $id_jadwal, false);

            if (!is_object($nilai))
                throw new \Exception("Gagal mengolah nilai (Result Invalid)");

            if (isset($nilai->sesisiswa) && count($nilai->sesisiswa) > 0) {
                $jadwal->bank = $nilai->bank ?? null;
                $jadwal->jenis = $nilai->jenis ?? null;
                $jadwal->tgl_mulai = $nilai->tgl_mulai ?? null;
                $jadwal->hasil_tampil = $nilai->hasil_tampil ?? null;
                $jadwal->sesisiswa = $nilai->sesisiswa[0];
            }

            echo json_encode(['success' => true, 'jadwal' => $jadwal]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
            exit;
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => "Fatal: " . $e->getMessage()]);
            exit;
        }
    }

    function uploadFile()
    {
        $this->load->library('upload');
        $max_size = $this->input->post('max-size', true);
        if (isset($_FILES["file_uploads"]["name"])) {
            $config['upload_path'] = './uploads/file_siswa/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4|avi|doc|docx|xls|xlsx|ppt|pptx|csv|pdf|rtf|txt';
            //$config['encrypt_name'] = TRUE;
            $config['max_size'] = $max_size;
            $config['overwrite'] = FALSE;

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file_uploads')) {
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            } else {
                $result = $this->upload->data();
                $data['src'] = 'uploads/file_siswa/' . $result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;
            }

            $data['type'] = $_FILES['file_uploads']['type'];
            $data['size'] = $_FILES['file_uploads']['size'];
        }
        $this->output_json($data);
    }

    function deleteFile()
    {
        $src = $this->input->post('src');
        //$file_name = str_replace(base_url(), '', $src);
        if (unlink($src)) {
            echo 'File Delete Successfully';
        }
    }

    function getDateFromWeekday($dayOfWeek, $month, $year)
    {
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

    public function generateMateri()
    {
        $this->load->model('Master_model', 'master');
        $allMapel = $this->master->getDataMapel();
        $insert = [];
        for ($j = 0; $j < 2; $j++) {
            $smt = $j + 1;
            for ($i = 0; $i < 20; $i++) {
                $tp = floor($i / 4) + 1;
                //$smt = $i < 10 ? '1' : '2';
                $mapels = $this->master->getGuruMapel($tp, $smt);

                $mapel_kelas_guru = [];
                foreach ($mapels as $mapel) {
                    $mapel_kelas = $this->maybe_unserialize($mapel->mapel_kelas ?? '[]');
                    $mapel_kelas_guru[$mapel->id_guru] = $mapel_kelas;
                    $mapel->mapel_kelas = $mapel_kelas;
                    $mapel->kelas_guru = $mapel_kelas_guru;
                }
                $data[$tp][$smt][] = $mapels;

                foreach ($mapel_kelas_guru as $id_guru => $item) {
                    if ($item == null || count($item) == 0)
                        continue;
                    foreach ($item as $mkls) {
                        $idmpl = $mkls['id_mapel'];
                        $insert[] = [
                            'jenis' => $i < 10 ? '1' : '2',
                            'id_tp' => $tp,
                            'id_smt' => $smt,
                            'kode_materi' => $this->randomString(5, true),
                            'id_guru' => $id_guru,
                            'id_mapel' => $idmpl,
                            'kode_mapel' => $allMapel[$idmpl]->kode,
                            'judul_materi' => $this->randomString(5, false),
                            'isi_materi' => $this->randomText(200),
                            'materi_kelas' => json_encode(array_column($mkls['kelas_mapel'], 'kelas')),
                            'created_on' => date('Y-m-d H:i:s'),
                            'updated_on' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
        }
        $this->db->insert_batch('kelas_materi', $insert);
        $this->output_json(['insert' => $insert, 'success' => true]);
    }

    function randomString($length = 5, $uppercase = true)
    {
        $characters = $uppercase ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomStr = '';
        for ($i = 0; $i < $length; $i++) {
            $randomStr .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomStr;
    }

    function randomText($length = 200)
    {
        $words = explode(' ', str_repeat('lorem ipsum dolor sit amet consectetur adipiscing elit ', ceil($length / 6)));
        shuffle($words);
        return substr(implode(' ', $words), 0, $length);
    }

    public function generateJadwalMateri()
    {
        $this->db->select('a.id_materi, a.kode_materi, a.kode_mapel, a.judul_materi, a.materi_kelas,' .
            ' a.id_mapel, a.status, a.id_tp, a.id_smt');
        $this->db->from('kelas_materi a');
        $allMateri = $this->db->get()->result();

        $this->output_json($allMateri);
    }
    public function save_katrol_config()
    {
        $id_jadwal = $this->input->post('id_jadwal', true);
        $min = $this->input->post('min', true);
        $max = $this->input->post('max', true);

        $this->load->dbforge();
        if (!$this->db->field_exists('katrol_min', 'cbt_jadwal')) {
            $fields = [
                'katrol_min' => ['type' => 'INT', 'default' => 0],
                'katrol_max' => ['type' => 'INT', 'default' => 100]
            ];
            $this->dbforge->add_column('cbt_jadwal', $fields);
        }

        $data = ['katrol_min' => $min, 'katrol_max' => $max];
        $this->db->where('id_jadwal', $id_jadwal);
        $updated = $this->db->update('cbt_jadwal', $data);

        $this->output_json(['success' => $updated]);
    }
}