<?php


/**
 * @property CI_User_agent $agent
 */
class Cbtsiswa extends MY_Controller
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

    public function leavecbt()
    {
        $this->load->model('Log_model', 'logging');

        // Validate and sanitize input
        $id_siswa = (int) $this->input->post('siswa');
        $id_jadwal = (int) $this->input->post('jadwal');

        if ($id_siswa <= 0 || $id_jadwal <= 0) {
            $this->output_json([
                'success' => false,
                'message' => 'Missing or invalid parameters'
            ]);
            return;
        }

        // Use transaction to keep updates consistent
        $this->db->trans_start();

        // Find active login log (type 1)
        $log = $this->db
            ->from('log_ujian')
            ->where([
                'id_siswa' => $id_siswa,
                'id_jadwal' => $id_jadwal,
                'log_type' => '1'
            ])
            ->get()
            ->row();

        $logUpdated = true;
        if ($log) {
            // use a clear sentinel value instead of ambiguous literal
            $logUpdated = $this->db
                ->where('id_log', $log->id_log)
                ->update('log_ujian', [
                    'agent' => 'reset_by_system',
                    'device' => 'reset_by_system'
                ]);
        }

        // Reset izin flag on cbt_siswa
        $cbtUpdated = $this->db
            ->where([
                'id_siswa' => $id_siswa,
                'id_jadwal' => $id_jadwal
            ])
            ->update('cbt_siswa', ['reset_izin' => 0]);

        $this->db->trans_complete();
        $ok = $this->db->trans_status() && $logUpdated && $cbtUpdated;

        // Log the operation only after successful transaction
        if ($ok) {
            $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Memerlukan reset izin');
            $this->output_json([
                'success' => true,
                'status' => true,
                'message' => 'Reset izin applied'
            ]);
        } else {
            // In case of failure, transaction already rolled back by trans_complete()
            $this->output_json([
                'success' => false,
                'status' => false,
                'message' => 'Failed to apply reset izin'
            ]);
        }
    }

    /*
    public function leavecbt()
    {
        $this->load->model('Log_model', 'logging');

        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');

        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_jadwal', $id_jadwal);
        $this->db->where('log_type', '1');
        $log = $this->db->get('log_ujian')->row();

        if ($log) {
            $log->agent = 'illegal agent';
            $log->device = 'illegal device';
            $this->db->where('id_log', $log->id_log);
            $update = $this->db->update('log_ujian', $log);

            $this->db->set('reset_izin', 0);
            $this->db->where('id_siswa', $id_siswa);
            $this->db->where('id_jadwal', $id_jadwal);
            $update2 = $this->db->update('cbt_siswa');

            $data['status'] = $update && $update2;
        }

        $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Memerlukan reset izin');

        $data['success'] = true;
        $this->output_json($data);
    }
    */

    public function hitung_pelanggaran()
    {
        $this->load->model('Log_model', 'logging');

        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');
        $jenis = $this->input->post('jenis');

        $pelanggaran_siswa = $this->db->select('id_cbt_siswa,pelanggaran')
            ->from('cbt_siswa')
            ->where('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()
            ->row();

        $pelanggaran = $this->maybe_unserialize($pelanggaran_siswa->pelanggaran ?? '{}');
        if (!isset($pelanggaran->{$jenis})) {
            $pelanggaran = (object) ['ringan' => 0, 'sedang' => 0, 'berat' => 0];
        }

        $pelanggaran->{$jenis} += 1;

        $this->db->set('pelanggaran', json_encode($pelanggaran));
        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_jadwal', $id_jadwal);
        $update2 = $this->db->update('cbt_siswa');

        $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Melakukan pelanggaran');

        $data['success'] = $update2;
        $data['pelanggaran'] = $pelanggaran;
        $this->output_json($data);
    }

    public function cbt()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Penilaian',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $this->render_view(3, 'members/siswa/cbt/data_jadwal', $data);
    }

    public function get_cbt_siswa()
    {
        $id_siswa = $this->input->get('id_siswa');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');
        $this->load->model('Cbt_bank_model', 'bank_model');
        $this->load->model('Cbt_sesi_model', 'sesi_model');
        $this->load->model('Master_siswa_model', 'master_siswa_model');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $siswa = $this->master_siswa_model
            ->fields('id_siswa,nama,nis,nisn,agama,foto')
            ->where(['id_siswa' => $id_siswa])
            ->with_sesisiswa([
                'fields' => 'siswa_id,kelas_id,ruang_id,sesi_id,tp_id,smt_id',
                'where' => ['tp_id' => $tp->id_tp, 'smt_id' => $smt->id_smt],
                'with' => [
                    ['relation' => 'peserta', 'fields' => 'nomor_peserta'],
                    ['relation' => 'kelas', 'fields' => 'kode_kelas,nama_kelas,level_id'],
                    ['relation' => 'ruang', 'fields' => 'kode_ruang,nama_ruang'],
                    ['relation' => 'sesi', 'fields' => 'kode_sesi,nama_sesi,waktu_mulai,waktu_akhir'],
                ]
            ])
            ->with_cbtsiswa([
                'where' => [],
                'fields' => 'id_jadwal,status,lama_ujian,mulai,selesai,reset_waktu,reset_izin'
            ])
            ->get();

        $jadwal_today = [];
        $jadwal_before = [];
        $cbt_bank = (object) [];
        if ($siswa->sesisiswa) {
            $cbt_bank = $this->bank_model
                ->fields('bank_kode,bank_kelas,bank_level,opsi,jml_soal,tampil_pg,bobot_pg,jml_kompleks,tampil_kompleks,bobot_kompleks,jml_jodohkan,tampil_jodohkan,bobot_jodohkan,jml_isian,tampil_isian,bobot_isian,jml_esai,tampil_esai,bobot_esai,id_tp,id_smt,soal_agama')
                ->where(['id_tp' => $tp->id_tp, "id_smt" => $smt->id_smt, 'bank_level' => $siswa->sesisiswa->kelas->level_id, 'status_soal' => '1'])
                ->with_jenis()
                ->with_guru(['fields' => 'id_guru,kode_guru,nama_guru'])
                ->with_mapel(['fields' => 'id_mapel,kode,nama_mapel'])
                ->with_jadwal([
                    'fields' => 'id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,pelanggaran,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak',
                    'with' => [
                        ['relation' => 'jenis'],
                        [
                            'relation' => 'pengawas',
                            'fields' => 'id_pengawas,id_ruang,id_sesi,jam_ke,id_guru',
                            'where' => ['id_ruang' => $siswa->sesisiswa->ruang->id_ruang, 'id_sesi' => $siswa->sesisiswa->sesi->id_sesi],
                        ]
                    ]
                ])
                ->get_all();

            $today = strtotime(date('Y-m-d'));

            if ($cbt_bank) {
                foreach ($cbt_bank as $idx => $bank) {
                    $bank->bank_kelas = $this->maybe_unserialize($bank->bank_kelas ?? '[]');
                    $arrKelasCbt = [];
                    foreach ($bank->bank_kelas as $k) {
                        $arrKelasCbt[] = $k->kelas_id;
                    }
                    if (isset($bank->jadwal)) {
                        if (isset($bank->jadwal->pengawas) && $bank->jadwal->pengawas != null) {
                            $allpengawass = array_filter((array) $bank->jadwal->pengawas, function ($p) use ($bank, $siswa) {
                                return $p->id_jadwal == $bank->jadwal->id_jadwal && $p->id_ruang == $siswa->sesisiswa->ruang_id && $p->id_sesi == $siswa->sesisiswa->sesi_id;
                            });
                            $pengawass = array_values($allpengawass);
                            $bank->jadwal->pengawas = $pengawass[0] ?? null;
                            $bank->jadwal->jam_ke = $pengawass[0]->jam_ke ?? ($bank->jadwal->jam_ke ?? '1');
                        }

                        if ($siswa != null && in_array($siswa->sesisiswa->kelas_id, $arrKelasCbt) && $bank->jadwal->status == '1') {
                            $mulai = strtotime($bank->jadwal->tgl_mulai ?? '');
                            $selesai = strtotime($bank->jadwal->tgl_selesai ?? '');
                            if ($bank->soal_agama == '-' || $bank->soal_agama == '0' || $bank->soal_agama == $siswa->agama) {
                                if ($today == $mulai) {
                                    $jadwal_today[$bank->jadwal->tgl_mulai][] = $bank;
                                } else if ($today > $mulai && $today <= $selesai) {
                                    $jadwal_before[$bank->jadwal->tgl_mulai][] = $bank;
                                }
                            }
                        }
                    } else {
                        unset($cbt_bank->{$idx});
                    }
                }
            }
        }
        $data['bank'] = $cbt_bank;
        $siswa->cbtsiswa = (array) $siswa->cbtsiswa;
        $data['jadwal_today'] = $jadwal_today;
        $data['jadwal_before'] = $jadwal_before;
        $data['siswa'] = $siswa;
        $data['today'] = date('Y-m-d H:i:s');

        $this->output_json($data);

    }

    public function absensi()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Absensi Ujian',
            'subjudul' => 'Daftar Kehadiran Ujian',
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $jadwals = $this->cbt->getJadwalCbtSiswa($tp->id_tp, $smt->id_smt, $siswa->level_id);

        // Filter jadwal for this student based on class/bank
        $my_jadwals = [];
        foreach ($jadwals as $jadwal) {
            $bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? '[]');
            $kelas_ids = array_column($bank_kelas, 'kelas_id');

            if (in_array($siswa->id_kelas, $kelas_ids)) {
                $sig_file = 'uploads/ttd_siswa/' . $siswa->id_siswa . '_' . $jadwal->id_bank . '_' . $jadwal->id_jadwal . '.png';
                $jadwal->signature = file_exists($sig_file) ? base_url() . $sig_file : null;

                // Check attendance status from cbt_siswa
                // We need to check if they have started/finished the exam
                $log = $this->db->where('id_siswa', $siswa->id_siswa)
                    ->where('id_jadwal', $jadwal->id_jadwal)
                    ->get('cbt_siswa')->row();

                $jadwal->hadir = ($log != null);
                $jadwal->dikewrjakan = ($log && $log->selesai != null);

                $my_jadwals[] = $jadwal;
            }
        }

        // Sort by date desc
        usort($my_jadwals, function ($a, $b) {
            return strtotime($b->tgl_mulai) - strtotime($a->tgl_mulai);
        });

        $data['jadwals'] = $my_jadwals;

        $this->render_view(3, 'members/siswa/cbt/absensi', $data);
    }

    public function konfirmasi($id_jadwal)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Penilaian',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $curr_address = $this->input->ip_address();
        if ($this->agent->is_browser()) {
            $curr_agent = $this->agent->browser() . ' ' . $this->agent->version();
        } elseif ($this->agent->is_mobile()) {
            $curr_agent = $this->agent->mobile();
        } else {
            $curr_agent = 'unknown';
        }

        $curr_device = $this->agent->platform();
        $support = $curr_agent != 'unknown';
        $data['support'] = $support;
        $err = $support ? '' : '<li>Browser tidak support</li>';

        $jadwal = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak,pelanggaran')
            ->where(['id_jadwal' => $id_jadwal])
            ->with_jenis()
            ->with_tp()
            ->with_smt()
            ->with_bank([
                'fields' => 'bank_kode,bank_kelas,opsi,jml_soal,tampil_pg,bobot_pg,jml_kompleks,tampil_kompleks,bobot_kompleks,jml_jodohkan,tampil_jodohkan,bobot_jodohkan,jml_isian,tampil_isian,bobot_isian,jml_esai,tampil_esai,bobot_esai,id_tp,id_smt,soal_agama',
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt],
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel'],
                    ['relation' => 'guru', 'fields' => 'kode_guru,nama_guru'],
                ]
            ])
            ->get();

        if ($jadwal->reset_login == '1') {
            //jika jadwal menggunakan reset izin
            $cbtsiswa = $this->cbt_siswa_model
                ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
                ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $siswa->id_siswa])
                ->with_log([
                    'where' => ['id_jadwal' => $id_jadwal, 'log_type' => '1']
                ])
                ->get();

            if (isset($cbtsiswa->log)) {
                //jika siswa sudah login ujian
                if ($cbtsiswa->reset_izin == 1) {
                    //jika sudah direset izin
                    $this->db->set('address', $curr_address);
                    $this->db->set('agent', $curr_agent);
                    $this->db->set('device', $curr_device);
                    $this->db->where('id_log', $cbtsiswa->log->id_log);
                    if ($this->db->update('log_ujian')) {
                        $this->db->set('reset_izin', '0');
                        $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
                        if ($this->db->update('cbt_siswa')) {
                            $cbtsiswa = $this->cbt_siswa_model
                                ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
                                ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $siswa->id_siswa])
                                ->with_log([
                                    'where' => ['id_jadwal' => $id_jadwal, 'log_type' => '1']
                                ])
                                ->get();
                        }
                    }
                }
                $valid = $cbtsiswa->log->address == $curr_address && $cbtsiswa->log->agent == $curr_agent
                    && $cbtsiswa->log->device == $curr_device;
            } else {
                $valid = true;
            }
        } else {
            $valid = true;
        }

        $err .= $valid ? '' : '<li>Anda harus meminta reset izin ke pengawas!</li>';
        $data['valid'] = $valid;
        if ($valid) {
            $jadwal->bank->bank_kelas = $this->maybe_unserialize($jadwal->bank->bank_kelas ?? []);
            $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
            $guru = $this->cbt->getDataGuru();

            $cbt_info = $this->cbt->getSiswaCbtInfo($siswa->id_siswa, $tp->id_tp, $smt->id_smt);
            $pengawass = $this->cbt->getPengawas($tp->id_tp . $smt->id_smt . $id_jadwal . $cbt_info->id_ruang . $cbt_info->id_sesi);
            $pengawas = [];
            if ($pengawass != null && count(explode(",", $pengawass->id_guru ?? '')) > 0) {
                $pengawas = $this->master->getGuruByArrId(explode(",", $pengawass->id_guru ?? ''));
            }

            $data['jadwal'] = $jadwal;
            $data['guru'] = $guru;
            $data['pengawas'] = $pengawas;
        }
        $data['error'] = '<ul>' . $err . '</ul>';

        $this->render_view(3, 'members/siswa/cbt/konfirmasi', $data);

    }

    public function validasi_siswa()
    {
        $id_jadwal = $this->input->post('jadwal');
        $id_siswa = $this->input->post('siswa');
        $token_siswa = $this->input->post('token');

        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_log_model', 'cbt_log_model');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $jadwal = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,pelanggaran,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak,pelanggaran')
            ->where(['id_jadwal' => $id_jadwal])
            ->with_jenis()
            ->with_tp()
            ->with_smt()
            ->with_bank([
                'fields' => 'bank_kode,bank_kelas,opsi,jml_soal,tampil_pg,bobot_pg,jml_kompleks,tampil_kompleks,bobot_kompleks,jml_jodohkan,tampil_jodohkan,bobot_jodohkan,jml_isian,tampil_isian,bobot_isian,jml_esai,tampil_esai,bobot_esai,id_tp,id_smt,soal_agama',
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt],
                'with' => [
                    ['relation' => 'soal', 'fields' => '*count*', 'where' => ['tampilkan' => '1']],
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel', 'where' => []],
                    ['relation' => 'guru', 'fields' => 'kode_guru,nama_guru', 'where' => []],
                ]
            ])
            ->get();
        //$jadwal->bank->soal = (array) $jadwal->bank->soal;
        //$info = $this->cbt->getJadwalById($id_jadwal);

        $token_valid = true;
        // cek token
        if ($jadwal->token == '1') {
            $token = $this->cbt->getToken();
            if ($token == null) {
                $token_valid = false;
                $data['token_msg'] = 'Token tidak ada';
            } else {
                $token_valid = $token->token == $token_siswa;
                if ($token_valid) {
                    $this->session->set_userdata('token', $token_siswa);
                }
                $data['token_msg'] = $token_valid ? '' : 'Token salah';
            }
        }
        $data['token'] = $token_valid;

        if ($token_valid) {
            $curr_address = $this->input->ip_address();
            if ($this->agent->is_browser()) {
                $curr_agent = $this->agent->browser() . ' ' . $this->agent->version();
            } elseif ($this->agent->is_mobile()) {
                $curr_agent = $this->agent->mobile();
            } else {
                $curr_agent = 'unknown';
            }
            $curr_device = $this->agent->platform();
            $support = $curr_agent != 'unknown';
            $data['support'] = $support;

            if ($support) {
                // cek log_ujian
                $cek_reset_waktu = false;

                $cbtsiswa = $this->cbt_siswa_model
                    ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,jawaban,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
                    ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa])
                    ->with_log([
                        'where' => ['id_jadwal' => $id_jadwal, 'log_type' => '1']
                    ])
                    ->get();

                $izinkan = false;
                if ($cbtsiswa) { // ada cbt_siswa & log_ujian
                    $mulai_baru = false; // tidak perlu buat antrian soal
                    // cek reset izin
                    if ($jadwal->reset_login == '1') { // jadwal ceklis reset izin
                        if (
                            $cbtsiswa->log && $cbtsiswa->log->address == $curr_address
                            && $cbtsiswa->log->agent == $curr_agent
                            && $cbtsiswa->log->device == $curr_device
                        ) { // tidak pindah komputer
                            $izinkan = true;
                        } else { // pindah komputer, harus ada izin admin
                            // ($reset_izin == '1') sudah diizinkan, '0' = belum diijinkan
                            if ($cbtsiswa->reset_izin == '1') {
                                $izinkan = true;
                                // cek apakah kehabisan waktu?
                                $cek_reset_waktu = true;
                            }
                        }
                    } else { // jadwal tidak ceklist reset izin
                        $izinkan = true;
                    }
                } else { // tidak ada log = mulai baru
                    // buat log
                    $izinkan = true;
                    $mulai_baru = true;
                }
                $data['izinkan'] = $izinkan;

                $ada_waktu = false;
                if ($izinkan) {
                    if ($mulai_baru) {
                        // buat antrian soal di dalam jawaban siswa
                        $ada_waktu = true;
                    } else {
                        // lanjutkan antrian yang ada
                        // cek reset waktu
                        // 0=tidak, 1=reset dari 0, 2=reset dari sisa waktu
                        if ($cbtsiswa->reset_waktu == '0') {
                            $mulai = new DateTime($cbtsiswa->mulai);
                            $interval = $mulai->diff(new DateTime());
                            $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                            $data['interval'] = [
                                'days' => $interval->days,
                                'hari' => $interval->d,
                                'jam' => $interval->h,
                                'menit' => $interval->i,
                                'detik' => $interval->s,
                                'total' => $minutes
                            ];

                            // status
                            // 0 = ada waktu, 1 = habis waktu
                            $ada_waktu = $minutes < $jadwal->durasi_ujian;
                            $data['warn'] = [
                                'durasi_ujian' => $jadwal->durasi_ujian,
                                'siswa_mulai' => $cbtsiswa->mulai,
                                'durasi_siswa' => $cbtsiswa->lama_ujian,
                                'timer_elapsed' => $minutes,
                                'terlampaui' => $minutes - $jadwal->durasi_ujian,
                                'status' => $ada_waktu ? 0 : 1,
                                'msg' => $ada_waktu ? '' : 'Waktu ujian sudah habis'
                            ];
                        } elseif ($cbtsiswa->reset_waktu == '1') {
                            $ada_waktu = true;
                        } elseif ($cbtsiswa->reset_waktu == '2') {
                            $ada_waktu = true;
                        } elseif ($cbtsiswa->reset_waktu == '3') {
                            $ada_waktu = true;
                        }
                    }
                }

                $data['ada_waktu'] = $ada_waktu;
            }
        }
        $data['jml_soal'] = isset($jadwal->bank->soal) ? (array) $jadwal->bank->soal : [];
        $data['jadwal'] = $jadwal;
        $this->output_json($data);
    }

    public function createQueueNumber($jadwal)
    {
        $items = [];

        if (count($jadwal->bank->soal) == 0) {
            return $items;
        }

        // Filter soal yang ditampilkan
        $soals = array_filter($jadwal->bank->soal, function ($soal) {
            return $soal->tampilkan == '1';
        });

        usort($soals, static function ($a, $b) {
            // Bandingkan jenis terlebih dahulu
            if ($a->jenis !== $b->jenis) {
                return $a->jenis <=> $b->jenis;
            }
            // Jika jenis sama, bandingkan nomor_soal
            return $a->nomor_soal <=> $b->nomor_soal;
        });

        $total = count($soals);

        if ($total == 0) {
            return $items;
        }

        // Tentukan opsi jawaban berdasarkan jumlah opsi
        $opsiMap = [
            '2' => ['A', 'B'],
            '3' => ['A', 'B', 'C'],
            '4' => ['A', 'B', 'C', 'D'],
        ];
        $arrOpsiDefault = ['A', 'B', 'C', 'D', 'E'];
        $arrOpsi = $opsiMap[$jadwal->bank->opsi] ?? $arrOpsiDefault;

        // Acak nomor soal jika diatur acak
        $arrNum = range(1, $total);
        if ($jadwal->acak_soal == '1') {
            shuffle($arrNum);
        }

        $j = 0;
        foreach ($soals as $soal) {
            $jenis = $soal->jenis;
            $item_soal = [
                "id_bank" => $jadwal->bank->id_bank,
                "id_jadwal" => $jadwal->id_jadwal,
                "id_soal" => $soal->id_soal,
                "jenis" => $jenis,
                "no_soal_alias" => $arrNum[$j],
                "jawaban_benar" => $this->maybe_unserialize($soal->jawaban),
                "jawaban_alias" => null,
                "jawaban_siswa" => null,
            ];

            // Untuk soal pilihan ganda
            if ($jenis == '1') {
                $opsi = $arrOpsi;
                if ($jadwal->acak_opsi == '1') {
                    shuffle($opsi);
                }

                $item_soal["opsi_alias_a"] = $opsi[0] ?? '';
                $item_soal["opsi_alias_b"] = $opsi[1] ?? '';
                $item_soal["opsi_alias_c"] = $opsi[2] ?? '';
                $item_soal["opsi_alias_d"] = $opsi[3] ?? '';
                $item_soal["opsi_alias_e"] = $opsi[4] ?? '';
                $item_soal["point_soal"] = $jadwal->bank->bobot_pg > 0 ? round($jadwal->bank->bobot_pg / $jadwal->bank->tampil_pg, 2) : 0;
            } // Soal kompleks
            elseif ($jenis == '2') {
                $item_soal["opsi_alias_a"] = 'A';
                $item_soal["point_soal"] = $jadwal->bank->bobot_kompleks > 0 ? round($jadwal->bank->bobot_kompleks / $jadwal->bank->tampil_kompleks, 2) : 0;
            } // Jodohkan
            elseif ($jenis == '3') {
                $item_soal["point_soal"] = $jadwal->bank->bobot_jodohkan > 0 ? round($jadwal->bank->bobot_jodohkan / $jadwal->bank->tampil_jodohkan, 2) : 0;
            } // Isian
            elseif ($jenis == '4') {
                $item_soal["point_soal"] = $jadwal->bank->bobot_isian > 0 ? round($jadwal->bank->bobot_isian / $jadwal->bank->tampil_isian, 2) : 0;
            } // Esai
            elseif ($jenis == '5') {
                $item_soal["point_soal"] = $jadwal->bank->bobot_esai > 0 ? round($jadwal->bank->bobot_esai / $jadwal->bank->tampil_esai, 2) : 0;
            }

            $items[] = $item_soal;
            $j++;
        }

        // Urutkan berdasarkan nomor soal alias
        usort($items, static function ($a, $b) {
            return $a['no_soal_alias'] <=> $b['no_soal_alias'];
        });

        return $items;
    }

    private function jadwalSebelumnyaBelumSelesai($tanggal, $jadwals, $log_by_jadwal)
    {
        foreach ($jadwals as $item) {
            if ($item->tgl_mulai != $tanggal && isset($log_by_jadwal[$item->id_jadwal])) {
                $log = $log_by_jadwal[$item->id_jadwal];
                if ($log->status == "1" && !$log->selesai) {
                    return $item->id_jadwal;
                }
            }
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function penilaian($id_jadwal)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        $data = [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => 'Penilaian',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['running_text'] = $this->dashboard->getRunningText();

        $jadwal = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,reset_login,pelanggaran')
            ->where(['id_jadwal' => $id_jadwal])
            ->with_jenis()
            ->with_bank([
                'fields' => 'id_bank,bank_kode',
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel']
                ]
            ])
            ->get();

        $data['reset_login'] = $jadwal->reset_login;
        $data['pelanggaran'] = $jadwal->pelanggaran;
        $data['id_jadwal'] = $jadwal->id_jadwal;
        $data['id_bank'] = $jadwal->bank->id_bank;
        $data['nama_mapel'] = $jadwal->bank->mapel->nama_mapel ?? 'Mata Pelajaran';

        $this->render_view(3, 'members/siswa/cbt/ujian', $data);
    }

    public function validasi_jadwal()
    {
        $this->session->set_userdata('validasi', '0');
        $id_jadwal = $this->input->post('jadwal');

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_bank_model', 'bank_model');
        $this->load->model('Cbt_log_model', 'cbt_log_model');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');
        $this->load->model('Master_siswa_model', 'master_siswa_model');
        $this->load->model('Log_model', 'logging');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $support_info = $this->isDeviceSupported();
        $support = $support_info['status'];
        $is_support = $support && $support_info['agent'] != 'unknown';
        if (!$is_support) {
            $this->output_json([
                'approved' => false,
                'message' => $support_info['agent'] . ':' . $support_info['status']
            ]);
            return;
        }

        $jadwal = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,pelanggaran,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak')
            ->where('id_jadwal', $id_jadwal)
            ->with_bank([
                'fields' => 'bank_kode,bank_kelas,opsi,jml_soal,tampil_pg,bobot_pg,jml_kompleks,tampil_kompleks,bobot_kompleks,jml_jodohkan,tampil_jodohkan,bobot_jodohkan,jml_isian,tampil_isian,bobot_isian,jml_esai,tampil_esai,bobot_esai,id_tp,id_smt,soal_agama',
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt, 'bank_level' => $siswa->level_id, 'status_soal' => '1'],
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel'],
                    ['relation' => 'guru', 'fields' => 'kode_guru,nama_guru'],
                    [
                        'relation' => 'soal',
                        'fields' => 'id_soal, bank_id, mapel_id, jenis, nomor_soal, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan',
                        'where' => ['tampilkan' => '1']
                    ]
                ],
            ])
            ->with_jenis()
            ->get();

        // cek jadwal ada
        if (!$jadwal) {
            $this->output_json([
                'approved' => false,
                'message' => 'Jadwal tidak sesuai'
            ]);
            return;
        }
        // cek token
        if ($jadwal->token == '1') {
            $tokenSiswa = $this->session->token;
            $token = $this->cbt->getToken();
            $tokenValid = $token->token == $tokenSiswa;
            if (!$tokenValid) {
                $this->output_json([
                    'approved' => false,
                    'message' => 'Token tidak valid'
                ]);
                return;
            }
        }
        // cek jadwal aktif
        $aktif = $jadwal->status == '1';
        if (!$aktif) {
            $this->output_json([
                'approved' => false,
                'message' => 'Jadwal nonaktif'
            ]);
            return;
        }
        // Cek tgl ujian
        $today = date('Y-m-d'); // Ambil tanggal hari ini
        $startDay = $jadwal->tgl_mulai ?? '';
        $endDay = $jadwal->tgl_selesai ?? '';
        $tgl_cocok = $today >= $startDay && $today <= $endDay;
        if (!$tgl_cocok) {
            $this->output_json([
                'approved' => false,
                'message' => 'Waktu ujian tidak sesuai'
            ]);
            return;
        }
        // cek bank
        if (!isset($jadwal->bank)) {
            $this->output_json([
                'approved' => false,
                'message' => 'Data bank soal tidak ditemukan untuk kelas Anda'
            ]);
            return;
        }
        // cek bank
        $bank = $jadwal->bank;
        if (!$bank) {
            $this->output_json([
                'approved' => false,
                'message' => 'Data bank soal tidak ditemukan'
            ]);
            return;
        } else {
            $bank->bank_kelas = $this->maybe_unserialize($bank->bank_kelas ?? []);
            $bank->soal = isset($bank->soal) ? (array) $bank->soal : [];
        }
        // cek soal
        $ada_soal = count($bank->soal) > 0;
        if (!$ada_soal) {
            $this->output_json([
                'approved' => false,
                'message' => 'Tidak ada soal'
            ]);
            return;
        }
        // cek kelas
        $kelass = array_column($bank->bank_kelas, 'kelas_id');
        $kelas_cocok = in_array($siswa->kelas_id, $kelass);
        if (!$kelas_cocok) {
            $this->output_json([
                'approved' => false,
                'message' => 'Kelas tidak sesuai'
            ]);
            return;
        }
        // cek agama
        $bkn_agama = $bank->soal_agama != '-' && $bank->soal_agama != '0' && $bank->soal_agama != $siswa->agama;
        if ($bkn_agama) {
            $this->output_json([
                'approved' => false,
                'message' => 'Soal agama tidak sesuai'
            ]);
            return;
        }

        $sesisiswa = $this->sesisiswa_model
            ->where(['siswa_id' => $siswa->id_siswa, 'tp_id' => $tp->id_tp, 'smt_id' => $smt->id_smt])
            ->with_ruang()
            ->with_sesi()
            ->get();

        // Cek waktu sesi
        $now = new DateTime();
        $mulai = new DateTime($sesisiswa->sesi->waktu_mulai);
        $akhir = new DateTime($sesisiswa->sesi->waktu_akhir);
        // Jika sesi melewati tengah malam (akhir < mulai)
        if ($akhir < $mulai) {
            // Tambah 1 hari ke waktu akhir
            $akhir->modify('+1 day');
        }
        if ($now > $akhir) {
            $this->output_json([
                'approved' => false,
                'message' => 'Sesi ujian sudah berakhir'
            ]);
            return;
        }

        if ($now < $mulai) {
            $this->output_json([
                'approved' => false,
                'message' => 'Sesi ujian belum dimulai'
            ]);
            return;
        }

        $cbtsiswas = $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,jawaban,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
            ->where('id_siswa', $siswa->id_siswa)
            ->with_log([
                'where' => ['id_jadwal' => $id_jadwal, 'log_type' => '1']
            ])
            ->get_all();

        $map_cbt = [];
        $cbtsiswa = null;
        if ($cbtsiswas) {
            foreach ($cbtsiswas as $cbt) {
                if ($cbt->id_jadwal == $id_jadwal) {
                    $cbtsiswa = $cbt;
                }
                $map_cbt[$cbt->id_jadwal] = $cbt;
            }
        }

        if ($cbtsiswa && $cbtsiswa->status == '9') {
            $this->output_json([
                'approved' => false,
                'message' => 'Anda telah DIBLOKIR oleh pengawas.',
                'error' => 'blocked'
            ]);
            return;
        }

        $izin_info = $this->isSiswaDiizinkan($jadwal, $cbtsiswa, $support_info['agent'], $support_info['address'], $support_info['device']);
        $izinkan = $izin_info['izinkan'];
        if (!$izinkan) {
            $this->output_json([
                'approved' => false,
                'message' => $izin_info['msg']
            ]);
            return;
        }

        $waktu_info = $this->isWaktuMasihAda($cbtsiswa, $jadwal);
        $ada_waktu = $waktu_info['ada_waktu'];
        if (!$ada_waktu) {
            $this->output_json([
                'approved' => false,
                'message' => 'Waktu Anda sudah habis'
            ]);
            return;
        }

        // cek apakah sudah selesai?
        $selesai = $cbtsiswa != null && $cbtsiswa->selesai != '';
        if ($selesai) {
            $this->output_json([
                'approved' => false,
                'message' => 'Anda sudah selesai ujian'
            ]);
            return;
        }

        // query semua jadwal dengan fields id_jadwal, jam_ke dan tgl_mulai
        $all_jadwals = $this->cbt_jadwal_model
            ->fields('id_jadwal,tgl_mulai,jam_ke')
            ->with_pengawas()
            ->with_bank([
                'fields' => 'id_bank,bank_kode',
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt, 'bank_level' => $siswa->level_id, 'status_soal' => '1'],
            ])
            ->get_all();

        $jadwals = [];
        if ($all_jadwals != null) {
            $all_jadwals = array_values((array) $all_jadwals);
            foreach ($all_jadwals as $jdl) {
                if (isset($jdl->pengawas) && $jdl->pengawas != null) {
                    $allPengawass = array_filter((array) $jdl->pengawas, function ($p) use ($jdl, $sesisiswa) {
                        return $p->id_jadwal == $jdl->id_jadwal && $p->id_ruang == $sesisiswa->ruang->id_ruang && $p->id_sesi == $sesisiswa->sesi->id_sesi;
                    });
                    $pengawass = array_values($allPengawass);
                    $jdl->pengawas = $pengawass[0] ?? null;
                    $jdl->jam_ke = $pengawass[0]->jam_ke ?? ($jdl->jam_ke ?? '1');
                }

                $jadwals[$jdl->id_jadwal] = $jdl;
            }
        }

        $jadwal_grouped = [];
        foreach ($jadwals as $item) {
            $tgl = $item->tgl_mulai;
            $jadwal_grouped[$tgl][] = $item;
        }

        /*
        $output = [];
        foreach ($jadwal_grouped as $tgl => $jdwls) {
            // Urutkan berdasarkan jam_ke (kecuali 0 tetap di bawah)
            usort($jdwls, function ($a, $b) {
                if ($a->jam_ke == $b->jam_ke) return 0;
                if ($a->jam_ke == 0) return 1;
                if ($b->jam_ke == 0) return -1;
                return $a->jam_ke - $b->jam_ke;
            });

            $sebelumnya_belum_selesai = $this->jadwalSebelumnyaBelumSelesai($tgl, $jadwals, $map_cbt);

            $jam_status = []; // menandai jam_ke yang masih menunggu
            foreach ($jdwls as $item) {
                $id_jadwal_s = $item->id_jadwal;
                $jam_ke = intval($item->jam_ke);
                $status_pengerjaan = 1;
                $msg = '';

                if ($sebelumnya_belum_selesai) {
                    $status_pengerjaan = 3;
                    $jadwal_found = $jadwals[$sebelumnya_belum_selesai];
                    $id_bank_found = $jadwal_found->id_bank;
                    $bank_filter = array_filter($all_jadwals, function ($bank) use ($id_bank_found) {
                        return $bank->bank->id_bank == $id_bank_found;
                    });
                    $bank_found = array_values($bank_filter)[0];
                    $msg = 'Anda sedang mengerjakan ujian dengan kode soal '. $bank_found->bank->bank_kode . ' pada tanggal ' . $jadwal_found->tgl_mulai . ' jam ke ' . $jadwal_found->jam_ke;
                } else {
                    if ($jam_ke > 0) {
                        // Cek apakah jam sebelumnya sudah selesai
                        $menunggu = false;
                        for ($i = 1; $i < $jam_ke; $i++) {
                            $found = false;
                            foreach ($jdwls as $check) {
                                if (intval($check->jam_ke) == $i) {
                                    $found = true;
                                    $log = $map_cbt[$check->id_jadwal] ?? null;
                                    if ($log && $log->status == "1" && !$log->selesai) {
                                        $menunggu = true;
                                        $id_bank_found = $check->id_bank;
                                        $bank_filter = array_filter($all_jadwals, function ($bank) use ($id_bank_found) {
                                            return $bank->bank->id_bank == $id_bank_found;
                                        });
                                        $bank_found = array_values($bank_filter)[0];
                                        $msg = 'Anda sedang mengerjakan ujian dengan kode soal '. $bank_found->bank->bank_kode . ' pada tanggal ' . $check->tgl_mulai . ' jam ke ' . $check->jam_ke;
                                        break;
                                    }
                                }
                            }
                            if ($menunggu) break;
                        }
                        $status_pengerjaan = $menunggu ? 2 : 1;
                    }
                }
                $output[$id_jadwal_s] = [
                    "message" => $msg,
                    "status_pengerjaan" => $status_pengerjaan,
                    "id_jadwal" => $id_jadwal_s,
                    "tgl_mulai" => $item->tgl_mulai,
                    "jam_ke" => $item->jam_ke
                ];
            }
        }

        if (isset($output[$id_jadwal]) && $output[$id_jadwal]['status_pengerjaan'] > 1) {
            $this->output_json([
                'approved' => false,
                'message' => $output[$id_jadwal]['message']
            ]);
            return;
        }
        */

        $mulai_baru = $izin_info['mulai_baru'];

        if (!empty($izin_info['update_reset_izin'])) {
            $cbtsiswa = $this->handleResetLogin($cbtsiswa, $support_info['address'], $support_info['agent'], $support_info['device'], $id_jadwal, $siswa);
        }

        if (!empty($waktu_info['reset_type'])) {
            $this->handleResetWaktu($cbtsiswa, $waktu_info['reset_type']);
        }

        $jawaban = $this->inisialisasiJawaban($siswa, $jadwal, $cbtsiswa, $mulai_baru);
        $this->session->set_userdata('validasi', '1');

        $this->output_json([
            'ada_soal' => $ada_soal,
            'queue' => $jawaban,
            'jml_soal' => count($jawaban),
            'approved' => true,
            'bank' => $bank,
            'jadwal' => $jadwal,
            'jadwals' => $jadwal_grouped,
            'sesisiswa' => $sesisiswa,
            'today' => $today,
            'tgl_cocok' => $tgl_cocok,
        ]);
    }

    public function save_played()
    {
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $id_siswa = $this->input->post('siswa', true);
        $id_jadwal = $this->input->post('jadwal', true);
        $id_soal = $this->input->post('soal', false);
        $id_media = $this->input->post('media', false);
        $type = $this->input->post('type', false);
        $play = $this->input->post('play', false);

        $cbtsiswa = $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,jawaban')
            ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa])->get();

        $cbtsiswa->jawaban = json_decode($cbtsiswa->jawaban);
        $idx_jawaban = array_search($id_soal, array_column($cbtsiswa->jawaban, 'id_soal'));
        $jawaban = $cbtsiswa->jawaban[$idx_jawaban];

        $played = [
            'type' => $type,
            'play' => $play,
            'id' => $id_media
        ];
        if (isset($jawaban->media)) {
            $jwb_media = array_filter($jawaban->media, function ($media) use ($id_media, $type) {
                return $media->id == $id_media && $media->type == $type;
            });
            if (!empty($jwb_media)) {
                $key_media = array_keys($jwb_media)[0];
                $jawaban->media[$key_media] = $played;
            } else {
                $jawaban->media[] = $played;
            }
        } else {
            $jawaban->media[] = $played;
        }

        $cbtsiswa->jawaban[$idx_jawaban] = $jawaban;

        $this->db->set('jawaban', json_encode($cbtsiswa->jawaban));
        $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
        $update = $this->db->update('cbt_siswa');
        $data['success'] = $update;

        $this->output_json($data);
    }

    public function toggle_ragu()
    {
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $id_siswa = $this->input->post('siswa', true);
        $id_jadwal = $this->input->post('jadwal', true);
        $id_soal = $this->input->post('soal', true);
        $ragu = $this->input->post('ragu', true) === 'true';

        $cbtsiswa = $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,jawaban')
            ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa])->get();

        if ($cbtsiswa && $cbtsiswa->jawaban) {
            $cbtsiswa->jawaban = json_decode($cbtsiswa->jawaban);
            $found = false;
            foreach ($cbtsiswa->jawaban as $key => $answer) {
                if ($answer->id_soal == $id_soal) {
                    $cbtsiswa->jawaban[$key]->ragu = $ragu;
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $this->db->set('jawaban', json_encode($cbtsiswa->jawaban));
                $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
                $update = $this->db->update('cbt_siswa');
                $this->output_json(['success' => $update]);
            } else {
                $this->output_json(['success' => false, 'message' => 'Soal not found']);
            }
        } else {
            $this->output_json(['success' => false, 'message' => 'Data not found']);
        }
    }

    public function loadNomorSoal()
    {
        $id_jadwal = $this->input->post('jadwal');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_soal_model', 'soal_model');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $id_siswa = $this->input->post('siswa');
        $id_bank = $this->input->post('bank');
        $nomor = $this->input->post('nomor');
        $timer = $this->input->post('timer');

        $support_info = $this->isDeviceSupported();
        $curr_address = $support_info['status'];
        $curr_agent = $support_info['agent'];
        $curr_device = $support_info['device'];
        $is_support = $support_info['status'];
        $support = $is_support && $curr_agent != 'unknown';

        if (!$support) {
            $this->output_json(['approved' => false, 'message' => 'Perangkat/Browser tidak dikenal!']);
            return;
        }

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $jadwal = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,pelanggaran,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak,pelanggaran')
            ->where(['id_jadwal' => $id_jadwal])
            ->get();

        if (!$jadwal) {
            $this->output_json(['approved' => false, 'message' => 'Jadwal tidak sesuai']);
            return;
        }

        $soals = $this->soal_model
            ->fields('id_soal,bank_id,mapel_id,jenis,nomor_soal,soal,opsi_a,opsi_b,opsi_c,opsi_d,opsi_e,jawaban,tampilkan')
            ->where(['bank_id' => $id_bank, 'tampilkan' => '1'])
            ->get_all();

        if (!$soals) {
            $this->output_json(['approved' => false, 'message' => 'No questions found.']);
            return;
        }

        // cek log_ujian
        $cbtsiswa = $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
            ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa])
            ->with_log([
                'where' => ['id_jadwal' => $id_jadwal, 'log_type' => '1']
            ])
            ->get();

        if (!$cbtsiswa) {
            $this->output_json([
                'approved' => false,
                'message' => 'Tidak ada data SOAL, silahkan kembali ke menu utama dan coba lagi.'
            ]);
            return;
        }

        if ($cbtsiswa->status == '9') {
            $this->output_json([
                'approved' => false,
                'message' => 'Anda telah DIBLOKIR oleh pengawas.',
                'error' => 'blocked'
            ]);
            return;
        }

        if ($cbtsiswa->selesai != null) {
            $this->output_json([
                'approved' => false,
                'message' => 'Ujian telah diselesaikan oleh Pengawas.',
                'error' => 'finished'
            ]);
            return;
        }

        // cek reset izin
        if ($jadwal->reset_login == '1') { // jadwal ceklis reset izin
            if (
                $cbtsiswa->log && $cbtsiswa->log->address == $curr_address
                && $cbtsiswa->log->agent == $curr_agent
                && $cbtsiswa->log->device == $curr_device
            ) { // tidak pindah komputer
                $izinkan = true;
            } else { // pindah komputer, harus ada izin admin
                // ($reset_izin == '1') sudah diizinkan, '0' = belum diijinkan
                $izinkan = $cbtsiswa->reset_izin == '1';
            }
        } else { // jadwal tidak ceklist reset izin
            $izinkan = true;
        }

        if (!$izinkan) {
            $this->output_json([
                'approved' => false,
                'message' => 'Tidak ada izin untuk melanjutkan.<br />Hubungi Admin!',
                'error' => 'Belum reset izin'
            ]);
            return;
        }

        // lanjutkan antrian yang ada
        // cek reset waktu
        $mulai = new DateTime($cbtsiswa->mulai);
        $interval = $mulai->diff(new DateTime());
        $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        $ada_waktu = $cbtsiswa->reset_waktu == '0' && $minutes < $jadwal->durasi_ujian;
        /*
        if (!$ada_waktu) {
            $this->output_json([
                'approved' => false,
                'message' => 'Waktu Ujian sudah habis!',
                'expired' => true,
                'interval' => $interval
            ]);
            return;
        }
        */

        $elapsed = $interval->format('%H:%I:%S');
        // 0=tidak, 1=reset dari 0, 2=reset dari sisa waktu
        if ($cbtsiswa->reset_waktu == '0') {
            // tidak ada reset
            $this->db->set('lama_ujian', $elapsed);
        } elseif ($cbtsiswa->reset == '1') {
            // reset menit ke 00:00:00
            $this->db->set('lama_ujian', '00:00:00');
            $this->db->set('reset_waktu', 0);
        } else {
            // reset dan lanjutkan
            $this->db->set('lama_ujian', $elapsed);
            $this->db->set('reset_waktu', 0);
        }
        $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
        $this->db->update('cbt_siswa');

        $cbtsiswa = $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,jawaban,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
            ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa])
            ->with_log([
                'where' => ['id_jadwal' => $id_jadwal, 'log_type' => '1']
            ])
            ->get();

        $cbtsiswa->jawaban = json_decode($cbtsiswa->jawaban);
        usort($cbtsiswa->jawaban, function ($a, $b) {
            return $a->no_soal_alias <=> $b->no_soal_alias;
        });

        $siswa_jawab = array_filter($cbtsiswa->jawaban, function ($jwb) use ($nomor) {
            return $jwb->no_soal_alias == $nomor;
        });
        $item_jawab = array_values($siswa_jawab)[0];
        $soals = (array) $soals;

        $item_soal = null;
        $modal = '<div class="question-popup"><ul>';
        $arrJawaban = [];
        foreach ($cbtsiswa->jawaban as $key => $answer) {
            $idSoal = $answer->id_soal;
            $item_q = array_filter($soals, function ($soal) use ($idSoal) {
                return $soal->id_soal == $idSoal;
            });
            $soal = array_values($item_q)[0];
            $soal->jawaban = $this->maybe_unserialize($soal->jawaban ?? '');
            $soal->opsi_a = $this->maybe_unserialize($soal->opsi_a ?? '');

            if ($answer->jawaban_siswa != null) {
                if ($soal->jenis == '3') {
                    $jawabanData = isset($answer->jawaban_siswa->jawaban) ? $answer->jawaban_siswa->jawaban : null;
                    $terjawab = false;

                    if ($jawabanData) {
                        // Cek Format Lama: LinkerList (Array Multidimensi dengan indeks numerik)
                        // Biasanya dimulai dengan indeks 0 yang berisi array header
                        $isLegacy = is_array($jawabanData) && isset($jawabanData[0]) && is_array($jawabanData[0]);

                        if ($isLegacy) {
                            foreach ($jawabanData as $keyi => $jwbn_siswa) {
                                if ($keyi > 0 && is_array($jwbn_siswa)) {
                                    foreach ($jwbn_siswa as $keyj => $jwbn) {
                                        if ($keyj > 0 && $jwbn == '1') {
                                            $terjawab = true;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        } else {
                            // Format Baru: Object Key-Value ( { "L1":"R2", ... } )
                            // Atau Array asosiatif sederhana
                            $cnt = 0;
                            if (is_array($jawabanData)) {
                                $cnt = count($jawabanData);
                            } elseif (is_object($jawabanData)) {
                                $cnt = count(get_object_vars($jawabanData));
                            }
                            $terjawab = $cnt > 0;
                        }
                    }
                } elseif ($soal->jenis == '6') {
                    // Type 6: Urutan - check if order array exists
                    $jawabanData = $answer->jawaban_siswa;
                    if (is_string($jawabanData)) {
                        $jawabanData = json_decode($jawabanData);
                    }
                    $terjawab = is_array($jawabanData) && count($jawabanData) > 0;
                } elseif ($soal->jenis == '7') {
                    // Type 7: Cloze - check if any blanks are filled
                    $jawabanData = $answer->jawaban_siswa;
                    if (is_string($jawabanData)) {
                        $jawabanData = json_decode($jawabanData);
                    }
                    $terjawab = false;
                    if (is_object($jawabanData)) {
                        foreach ($jawabanData as $key => $val) {
                            if (!empty($val)) {
                                $terjawab = true;
                                break;
                            }
                        }
                    } elseif (is_array($jawabanData)) {
                        foreach ($jawabanData as $val) {
                            if (!empty($val)) {
                                $terjawab = true;
                                break;
                            }
                        }
                    }
                } else {
                    $terjawab = $answer->jawaban_siswa != "";
                }
            } else {
                $terjawab = false;
            }

            $selectedClass = $nomor == $answer->no_soal_alias ? 'current' : "";
            $answeredClass = $terjawab ? 'selected' : '';
            $raguClass = isset($answer->ragu) && $answer->ragu ? 'ragu' : '';

            $modal .= '<li class="' . $selectedClass . ' ' . $answeredClass . ' ' . $raguClass . '" id="box' . $answer->no_soal_alias . '">';
            $modal .= '<a href="javascript:void(0)" ' .
                'data-pos="' . ($key) . '" data-nomorsoal="' . $answer->no_soal_alias . '" ' .
                'data-idsoal="' . $soal->id_soal . '" data-jenis="' . $soal->jenis . '" ' .
                'onclick="loadSoal(this)">' . $answer->no_soal_alias . '</a>';

            if ($terjawab) {
                $txt_badge = $soal->jenis == '1' ? $answer->jawaban_alias : '&check;';
                $arrJawaban[] = $answer->jawaban_alias;
                $modal .= '<span id="badge' . $answer->no_soal_alias . '">' . $txt_badge . '</span>';
            }
            $modal .= '</li>';

            if ($item_jawab->id_soal == $soal->id_soal) {
                $item_soal = $soal;
            }
        }

        $max_jawaban = [];
        $opsis = [];
        switch ($item_soal->jenis) {
            case '1': // Pilihan Ganda Tunggal
                $jwbSiswa = strtoupper($item_jawab->jawaban_siswa ?? '');
                $labels = ['A', 'B', 'C', 'D', 'E'];
                $opsis = [];
                foreach ($labels as $label) {
                    $aliasKey = 'opsi_alias_' . strtolower($label ?? '');
                    $opsiKey = 'opsi_' . strtolower($label ?? '');
                    $opsis[] = [
                        'valAlias' => $item_jawab->$aliasKey ?? $label,
                        'opsi' => $item_soal->$opsiKey ?? '',
                        'value' => $label,
                        'checked' => $label == $jwbSiswa ? 'checked' : ''
                    ];
                }

                // Bersihkan opsi dari item soal
                foreach ($labels as $label) {
                    unset($item_soal->{'opsi_' . strtolower($label ?? '')});
                }

                usort($opsis, function ($a, $b) {
                    return $a['valAlias'] <=> $b['valAlias'];
                });
                break;

            case '2': // Pilihan Ganda Jamak
                $jwbSiswa = $item_jawab->jawaban_siswa ?? [];
                $opsis = [];
                foreach ($item_soal->opsi_a as $key => $opsi) {
                    $opsis[] = [
                        'opsi' => $opsi,
                        'value' => $key,
                        'checked' => in_array(strtoupper($key ?? ''), $jwbSiswa) ? 'checked="true"' : ''
                    ];
                }

                usort($opsis, function ($a, $b) {
                    return $a['value'] <=> $b['value'];
                });

                $max_jawaban = [count(array_filter($item_soal->jawaban ?? []))];
                break;

            case '3': // Menjodohkan / Tabel
                $jwbs = $item_soal->jawaban;
                $max_jawaban = [];
                $thead = [];
                $tbody = [];

                if (isset($jwbs->jawaban)) {
                    foreach ($jwbs->jawaban as $jwb) {
                        $key = $jwb[0];
                        $max_jawaban[$key] = count(array_filter(array_slice($jwb, 1)));
                    }
                }

                $ada_jawab = $item_jawab->jawaban_siswa !== null;
                $jawaban_siswa = $ada_jawab ? $item_jawab->jawaban_siswa : json_decode(json_encode($item_soal->jawaban));

                if (isset($jawaban_siswa->jawaban)) {
                    foreach ($jawaban_siswa->jawaban as $key => $jawaban) {
                        if ($key == 0) {
                            $thead = $jawaban;
                        } else {
                            if ($ada_jawab) {
                                $tbody[] = $jawaban;
                            } else {
                                $barisKosong = [$jawaban[0]];
                                for ($i = 1; $i < count($jawaban); $i++) {
                                    $barisKosong[] = '';
                                }
                                $tbody[] = $barisKosong;
                            }
                        }
                    }
                }

                $opsis = [
                    'tabel' => $jwbs->jawaban ?? [],
                    'thead' => $thead,
                    'tbody' => $tbody,
                    'model' => $jwbs->model ?? '2',
                    'type' => $jwbs->type ?? null,
                ];
                break;

            case '4': // Isian Singkat
                // No special options needed, just pass empty
                $opsis = [];
                break;

            case '5': // Uraian/Essay
                // No special options needed
                $opsis = [];
                break;

            case '6': // Urutan (Ordering)
                // Parse items from opsi_a which contains the ordering items
                $opsi_data = $item_soal->opsi_a;
                $items = [];
                $correct_order = [];

                if (is_string($opsi_data)) {
                    $opsi_data = json_decode($opsi_data);
                }

                if (is_object($opsi_data)) {
                    $items = isset($opsi_data->items) ? (array) $opsi_data->items : [];
                    $correct_order = isset($opsi_data->correct_order) ? (array) $opsi_data->correct_order : [];
                } elseif (is_array($opsi_data)) {
                    $items = isset($opsi_data['items']) ? $opsi_data['items'] : array_values($opsi_data);
                    $correct_order = isset($opsi_data['correct_order']) ? $opsi_data['correct_order'] : [];
                }

                $opsis = [
                    'items' => $items,
                    'correct_order' => $correct_order
                ];
                break;

            case '7': // Isian Rumpang (Cloze)
                // Parse blanks from opsi_a which contains the blank definitions
                $opsi_data = $item_soal->opsi_a;
                $blanks = [];

                if (is_string($opsi_data)) {
                    $opsi_data = json_decode($opsi_data);
                }

                if (is_object($opsi_data)) {
                    $blanks = isset($opsi_data->blanks) ? (array) $opsi_data->blanks : [];
                } elseif (is_array($opsi_data)) {
                    $blanks = isset($opsi_data['blanks']) ? $opsi_data['blanks'] : $opsi_data;
                }

                $opsis = [
                    'blanks' => $blanks
                ];
                break;
        }

        $modal .= '</ul></div>';
        $data['soal_modal'] = $modal;

        $durasi = [
            "status" => $cbtsiswa->status ?? '',
            "mulai" => $cbtsiswa->mulai ?? '',
            "selesai" => $cbtsiswa->selesai ?? '',
            "lama_ujian" => $cbtsiswa->lama_ujian ?? '',
            "reset_izin" => $cbtsiswa->reset_izin ?? '',
            "reset_waktu" => $cbtsiswa->reset_waktu ?? ''
        ];
        $data['media'] = $item_jawab->media ?? [];
        $data['durasi'] = $durasi;
        $data['timer'] = $timer;
        $data['soal_id'] = $item_soal->id_soal;
        $data['soal_siswa_id'] = $cbtsiswa->id_cbt_siswa;
        $data['soal_nomor'] = $item_jawab->no_soal_alias;
        $data['soal_nomor_asli'] = $item_soal->nomor_soal;
        $data['soal_jenis'] = $item_soal->jenis;
        $data['soal_soal'] = $item_soal->soal;
        $data['soal_opsi'] = $opsis;
        $data['soal_jawaban_siswa'] = $item_jawab->jawaban_siswa ?? '';
        $data['soal_ragu'] = $item_jawab->ragu ?? false;
        $data['max_jawaban'] = $max_jawaban;

        $data['soal_total'] = count($soals);
        $data['soal_terjawab'] = count($arrJawaban);

        $data['jarak'] = $jadwal->jarak;
        $data['success'] = true;

        $data['approved'] = true;
        $data['expired'] = false;
        $data['interval'] = $interval;
        $data['log'] = $cbtsiswa->log;

        $data['jadwal'] = $jadwal;
        $this->output_json($data);
    }

    private function isDeviceSupported()
    {
        $agent = $this->agent;
        $is_supported = $agent->is_browser() || $agent->is_mobile();
        return [
            'status' => $is_supported,
            'agent' => $agent->is_browser() ? $agent->browser() . ' ' . $agent->version() :
                ($agent->is_mobile() ? $agent->mobile() : 'unknown'),
            'device' => $agent->platform(),
            'address' => $this->input->ip_address()
        ];
    }

    private function isSiswaDiizinkan($jadwal, $cbtsiswa, $curr_agent, $curr_address, $curr_device)
    {
        if (!$cbtsiswa)
            return ['izinkan' => true, 'mulai_baru' => true, 'msg' => 'mulai baru'];

        $izinkan = false;
        $update_reset_izin = false;

        $msg = '';
        if ($jadwal->reset_login == '1') {
            if (
                $cbtsiswa->log && $cbtsiswa->log->address == $curr_address &&
                $cbtsiswa->log->agent == $curr_agent && $cbtsiswa->log->device == $curr_device
            ) {
                $izinkan = true;
            } elseif ($cbtsiswa->reset_izin == '1') {
                $izinkan = true;
                $update_reset_izin = true;
            } else {
                $msg = 'Harus reset izin';
            }
        } else {
            $izinkan = true;
        }

        return [
            'izinkan' => $izinkan,
            'mulai_baru' => false,
            'update_reset_izin' => $update_reset_izin,
            'msg' => $msg
        ];
    }

    private function isWaktuMasihAda($cbtsiswa, $jadwal)
    {
        if (!$cbtsiswa)
            return ['ada_waktu' => true];

        switch ($cbtsiswa->reset_waktu) {
            case '0':
                $mulai = new DateTime($cbtsiswa->mulai);
                $interval = $mulai->diff(new DateTime());
                $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                return ['ada_waktu' => $minutes < $jadwal->durasi_ujian];
            case '1':
            case '2':
            case '3':
                return ['ada_waktu' => true, 'reset_type' => $cbtsiswa->reset_waktu];
        }

        return ['ada_waktu' => false];
    }

    private function inisialisasiJawaban($siswa, $jadwal, $cbtsiswa, $mulai_baru)
    {
        if ($mulai_baru) {
            $this->cbt->saveLog($siswa->id_siswa, $jadwal->id_jadwal, 1, 'Memulai Ujian');
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Memulai Ujian');

            $jawaban = $this->createQueueNumber($jadwal);

            if (count($jawaban) > 0) {
                $this->db->insert('cbt_siswa', [
                    'id_siswa' => $siswa->id_siswa,
                    'id_jadwal' => $jadwal->id_jadwal,
                    'id_bank' => $jadwal->bank->id_bank,
                    'jawaban' => json_encode($jawaban),
                    'nilai' => '[]',
                    'nilai_input' => '{}',
                    'reset_waktu' => '0',
                    'reset_izin' => '0',
                    'status' => '1',
                    'mulai' => date('Y-m-d H:i:s'),
                    'lama_ujian' => '00:00:00'
                ]);
            }
        } else {
            $jawaban = json_decode($cbtsiswa->jawaban, true);
            if (empty($jawaban)) {
                $jawaban = $this->createQueueNumber($jadwal);
                $this->db->set('jawaban', json_encode($jawaban))
                    ->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa)
                    ->update('cbt_siswa');
            }
        }

        return $jawaban;
    }

    private function handleResetLogin($cbtsiswa, $curr_address, $curr_agent, $curr_device, $id_jadwal, $siswa)
    {
        $this->db->set('address', $curr_address)
            ->set('agent', $curr_agent)
            ->set('device', $curr_device)
            ->where('id_log', $cbtsiswa->log->id_log)
            ->update('log_ujian');

        $this->db->set('reset_izin', '0')
            ->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa)
            ->update('cbt_siswa');

        return $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status')
            ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $siswa->id_siswa])
            ->with_log(['where' => ['id_jadwal' => $id_jadwal, 'log_type' => '1']])
            ->get();
    }

    private function handleResetWaktu($cbtsiswa, $reset_type)
    {
        switch ($reset_type) {
            case '1':
            case '3':
                $this->db->set([
                    'lama_ujian' => '00:00:00',
                    'mulai' => date('Y-m-d H:i:s'),
                    'reset_waktu' => 0
                ])->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa)
                    ->update('cbt_siswa');
                break;
            case '2':
                $dt = explode(':', $cbtsiswa->lama_ujian);
                $time = new DateTime();
                $time->sub(new DateInterval('PT' . $dt[0] . 'H' . $dt[1] . 'M' . $dt[2] . 'S'));

                $this->db->set([
                    'mulai' => $time->format('Y-m-d H:i:s'),
                    'reset_waktu' => 0
                ])->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa)
                    ->update('cbt_siswa');
                break;
        }
    }

    public function saveJawaban()
    {
        try {
            $id_bank = $this->input->post('bank', true);
            $timer = $this->input->post('waktu', true);
            $id_siswa = $this->input->post('siswa', true);
            $id_jadwal = $this->input->post('jadwal', true);
            $elapsed = $this->input->post('elapsed', true);

            $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');

            if (empty($id_jadwal)) {
                $this->output_json(['status' => false, 'message' => 'ID Jadwal tidak valid']);
                return;
            }

            // Check time validity using direct DB access
            $jadwal = $this->db->where('id_jadwal', $id_jadwal)->get('cbt_jadwal')->row();
            if (!$jadwal) {
                $this->output_json(['status' => false, 'message' => 'Jadwal tidak ditemukan']);
                return;
            }

            $cbtsiswa = $this->cbt_siswa_model
                ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,jawaban,lama_ujian,mulai,selesai,status')
                ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa])->get();

            if ($cbtsiswa && $cbtsiswa->mulai) {
                $start_time = strtotime($cbtsiswa->mulai);
                $duration_minutes = (int) $jadwal->durasi_ujian;
                if ($duration_minutes <= 0)
                    $duration_minutes = 90; // Fallback default

                $end_time = $start_time + ($duration_minutes * 60) + 120; // +2 minutes tolerance
                $current_time = time();

                if ($current_time > $end_time) {
                    $this->output_json([
                        'status' => false,
                        'message' => 'Waktu ujian telah habis. Jawaban tidak dapat disimpan.',
                        'expired' => true
                    ]);
                    return;
                }
            }

            $jawab = $this->input->post('data', false);

            if (isset($jawab['id_soal'])) {
                $id_soal = $jawab['id_soal'];
                $cbtsiswa->jawaban = json_decode($cbtsiswa->jawaban);
                $idx_jawaban = array_search($id_soal, array_column($cbtsiswa->jawaban, 'id_soal'));
                if ($idx_jawaban === false) {
                    // Handle case where question ID is not found in answer list
                    $this->output_json(['status' => false, 'message' => 'Soal tidak ditemukan dalam paket soal siswa']);
                    return;
                }

                $jawaban = $cbtsiswa->jawaban[$idx_jawaban];

                if ($jawab != null && isset($jawab['jenis'])) {
                    if ($jawab['jenis'] == 1) {
                        $jawaban->jawaban_siswa = $jawab['jawaban_siswa'];
                        $jawaban->jawaban_alias = $jawab['jawaban_alias'];
                    } elseif ($jawab['jenis'] == 2) {
                        $jawaban->jawaban_siswa = json_decode($jawab['jawaban_siswa']);
                        $jawaban->jawaban_alias = '';
                    } elseif ($jawab['jenis'] == 3) {
                        $jawaban->jawaban_siswa = json_decode($jawab['jawaban_siswa']);
                        $jawaban->jawaban_alias = '';
                    } elseif ($jawab['jenis'] == 4) {
                        $jawab_isian = $this->input->post('jawaban', false);
                        $jawaban->jawaban_siswa = $jawab_isian;
                        $jawaban->jawaban_alias = '';
                    } elseif ($jawab['jenis'] == 5) {
                        // Essay - store as HTML string
                        $jawaban->jawaban_siswa = $jawab['jawaban_siswa'];
                        $jawaban->jawaban_alias = '';
                    } elseif ($jawab['jenis'] == 6) {
                        // Urutan - store as array of indices
                        $answer_data = $jawab['jawaban_siswa'];
                        if (is_string($answer_data)) {
                            $answer_data = json_decode($answer_data, true);
                        }
                        $jawaban->jawaban_siswa = $answer_data;
                        $jawaban->jawaban_alias = '';
                    } elseif ($jawab['jenis'] == 7) {
                        // Cloze - store as object with blank answers
                        $answer_data = $jawab['jawaban_siswa'];
                        if (is_string($answer_data)) {
                            $answer_data = json_decode($answer_data, true);
                        }
                        $jawaban->jawaban_siswa = $answer_data;
                        $jawaban->jawaban_alias = '';
                    } else {
                        $jawaban->jawaban_siswa = $jawab['jawaban_siswa'];
                        $jawaban->jawaban_alias = '';
                    }
                }
                $cbtsiswa->jawaban[$idx_jawaban] = $jawaban;
                $this->db->set('jawaban', json_encode($cbtsiswa->jawaban));
                $this->db->set('lama_ujian', $elapsed);
                $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
                $update = $this->db->update('cbt_siswa');
                $data['success'] = $update;

                $arrJawaban = [];
                if ($update && $id_bank != null) {
                    $cbtsiswa = $this->cbt_siswa_model
                        ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,jawaban,lama_ujian,mulai,selesai,status')
                        ->where(['id_jadwal' => $id_jadwal, 'id_siswa' => $id_siswa])->get();
                    $cbtsiswa->jawaban = json_decode($cbtsiswa->jawaban);

                    foreach ($cbtsiswa->jawaban as $jawab) {
                        if ($jawab->jawaban_siswa != null && $jawab->jawaban_siswa != "") {
                            $arrJawaban[] = $jawab;
                        }
                    }
                }
                $data['soal_terjawab'] = count($arrJawaban);
                $this->output_json($data);
            } else {
                // Jika tidak ada data jawaban, hanya update waktu (navigation/auto-save)
                $this->db->set('lama_ujian', $elapsed);
                $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
                $update = $this->db->update('cbt_siswa');
                $data['status'] = $update;

                // Hitung ulang soal terjawab
                $arrJawaban = [];
                if (is_string($cbtsiswa->jawaban))
                    $cbtsiswa->jawaban = json_decode($cbtsiswa->jawaban);
                if (is_array($cbtsiswa->jawaban)) {
                    foreach ($cbtsiswa->jawaban as $jawab) {
                        if (isset($jawab->jawaban_siswa) && $jawab->jawaban_siswa != null && $jawab->jawaban_siswa != "") {
                            $arrJawaban[] = $jawab;
                        }
                    }
                }
                $data['soal_terjawab'] = count($arrJawaban);

                $this->output_json($data);
            }
        } catch (Exception $e) {
            $this->output_json(['status' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
        }
    }

    public function selesaiUjian()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Log_model', 'logging');

        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');

        $this->db->set('selesai', date('Y-m-d H:i:s'));
        $this->db->set('status', 2);
        $this->db->where('id_siswa', $id_siswa);
        $this->db->where('id_jadwal', $id_jadwal);
        $update = $this->db->update('cbt_siswa');

        $this->cbt->saveLog($id_siswa, $id_jadwal, 2, 'Menyelesaikan Ujian');
        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menyelesaikan Ujian');

        $data['status'] = $update;
        $data['success'] = true;
        $this->output_json($data);
    }

    public function save_signature()
    {
        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');
        $id_bank = $this->input->post('bank');
        $img_base64 = $this->input->post('signature_data');

        if (!$id_siswa || !$id_jadwal || !$id_bank || !$img_base64) {
            $this->output_json(['status' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        $img_base64 = str_replace('data:image/png;base64,', '', $img_base64);
        $img_base64 = str_replace(' ', '+', $img_base64);
        $data = base64_decode($img_base64);

        $file_name = $id_siswa . '_' . $id_bank . '_' . $id_jadwal . '.png';
        $upload_dir = './uploads/ttd_siswa/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (file_put_contents($upload_dir . $file_name, $data)) {
            $this->output_json(['status' => true, 'message' => 'Tanda tangan berhasil disimpan']);
        } else {
            $this->output_json(['status' => false, 'message' => 'Gagal menyimpan tanda tangan']);
        }
    }
}
