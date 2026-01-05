<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{

    protected $LOG_TYPE = [];

    protected $default_data = [];

    protected $namaHari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    protected $namaBulan = [
        'January' => 'Jan',
        'February' => 'Feb',
        'March' => 'Mar',
        'April' => 'Apr',
        'May' => 'Mei',
        'June' => 'Jun',
        'July' => 'Jul',
        'August' => 'Agu',
        'September' => 'Sep',
        'October' => 'Okt',
        'November' => 'Nov',
        'December' => 'Des'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        $sessionExist = $this->config->item('sess_driver') != 'database' || ($this->config->item('sess_driver') == 'database' && $this->db->table_exists('ci_sessions'));
        $url = $this->uri->segment(1);
        if ($url !== 'reset_password' && $url !== 'migrate') {
            if ($sessionExist) {
                $this->load->library('session');
                $this->load->model('Auth_model', 'auth');
                if (!$this->session->userdata('is_login')) {
                    redirect('auth');
                }
            } else {
                redirect('init');
            }
        }

        $tahun_sekarang = date('Y');
        $bulan_sekarang = date('n'); // 1-12

        if ($bulan_sekarang >= 7) {
            // Semester Ganjil dimulai Juli
            $tahun_ajaran = $tahun_sekarang . '/' . ($tahun_sekarang + 1);
            $semester = '1'; // atau 'Ganjil'
        } else {
            // Semester Genap dimulai Januari
            $tahun_ajaran = ($tahun_sekarang - 1) . '/' . $tahun_sekarang;
            $semester = '2'; // atau 'Genap'
        }

        $this->default_data['thn_sekarang'] = $tahun_sekarang;
        $this->default_data['tp_sekarang'] = $tahun_ajaran;
        $this->default_data['smt_sekarang'] = $semester;

        $this->LOG_TYPE = (object) [
            'LOGIN' => 1,
            'LOGOUT' => 2,
            'ADD' => 3,
            'EDIT' => 4,
            'DELETE' => 5,
            'MATERI_OPEN' => 6,
            'MATERI_CLOSE' => 7,
            'TUGAS_SUBMIT' => 8,
            'TUGAS_SEND' => 9,
            'UJIAN_START' => 10,
            'UJIAN_FINISH' => 11,
            'UJIAN_VIEW_SCORE' => 12
        ];
    }

    protected function render_view($role, $main_view, $data = [])
    {
        $data = array_merge($this->default_data, $data);

        if ($role == 1) {
            $this->load->view("_templates/dashboard/_header", $data);
            $this->load->view($main_view);
            $this->load->view("_templates/dashboard/_footer");
        } elseif ($role == 2) {
            $this->load->view('members/guru/templates/header', $data);
            $this->load->view($main_view);
            $this->load->view('members/guru/templates/footer');
        } elseif ($role == 3) {
            $this->load->view('members/siswa/templates/header', $data);
            $this->load->view($main_view);
            $this->load->view('members/siswa/templates/footer');
        } elseif ($role == 4) {
            $this->load->view('members/panitia/templates/header', $data);
            $this->load->view($main_view);
            $this->load->view('members/panitia/templates/footer');
        } else {
            show_error('Unauthorized access', 403, 'Forbidden');
        }
    }

    public function submit($data)
    {
        foreach ($data as $key => $val) {
            $data[$key] = $this->security->xss_clean($val);
        }
        return $data;
    }


    protected function output_json($data, $encode = true)
    {
        if (ob_get_length())
            ob_end_clean();
        header_remove();

        if ($encode)
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR); //json_encode($data);
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function output_json_stream($data)
    {
        $this->output->set_content_type('application/json');
        echo '[';
        $count = count($data);
        foreach ($data as $i => $row) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE);
            if ($i < $count - 1)
                echo ',';
            flush(); // kirim sebagian data ke browser
        }
        echo ']';
    }

    protected function buildData($data)
    {
        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['is_admin'] = $this->auth->is_admin();
        return $data;
    }

    protected function getTpSmt()
    {
        $this->load->model('Tp_model', 'tp');
        $this->load->model('Smt_model', 'smt');
        $tps = (array) $this->tp->get_all();
        $smts = (array) $this->smt->get_all();
        $data['tp'] = $tps;
        $data['smt'] = $smts;
        $tp_fil = array_filter($tps, function ($item) {
            return $item->active == '1';
        });
        $smt_fil = array_filter($smts, function ($item) {
            return $item->active == '1';
        });
        $tp = reset($tp_fil);
        $smt = reset($smt_fil);

        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        return (object) $data;
    }

    private function arrToUpper($val)
    {
        return strtoupper($val ?? '');
    }

    protected function sortArrays(&$array)
    {
        foreach ($array as &$subArray) {
            if ($subArray) {
                sort($subArray);
            }
        }
    }

    protected function isUpdated()
    {
        $this->config->load('migration');
        $latest = $this->config->item('migration_version');
        $uptodate = ['latest' => $latest];
        if (
            $this->db->table_exists('migrations') &&
            $this->db->field_exists('version', 'migrations')
        ) {
            $versi = $this->db->get('migrations')->first_row();
            $uptodate['current'] = (int) $versi->version;
            $uptodate['is_latest'] = (int) $versi->version >= $latest;
        } else {
            $uptodate['current'] = 0;
            $uptodate['is_latest'] = false;
        }
        return json_decode(json_encode($uptodate));
    }

    protected function is_serialized($data, $strict = true)
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false == $semicolon && false == $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== $data[strlen($data) - 2]) {
                        return false;
                    }
                } elseif (false == strpos($data, '"')) {
                    return false;
                    //} elseif ( ! str_contains( $data, '"' ) ) {
                    //    return false;
                }
            // Or else fall through.
            case 'a':
            case 'O':
            case 'E':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }
        return false;
    }

    /**
     * @throws JsonException
     */
    protected function maybe_unserialize($data)
    {
        // Don't attempt to unserialize data that wasn't serialized going in.
        if ($this->is_serialized($data)) {
            $result = @unserialize(trim($data));
            return json_decode(json_encode($result));
        }

        $json = is_string($data) ? json_decode($data) : $data;
        if (json_last_error() == JSON_ERROR_NONE && (is_object($json) || is_array($json))) {
            return $json;
        }

        return $data;
    }

    protected function maybe_decode($data)
    {
        if ($data[0] == '[' || $data[0] == '{') {
            // new
            $data = json_decode($data);
            // return return ['1','2']
        } else {
            // old
            $kelasIds = $this->maybe_unserialize($data);
            // return = ['kelas_id'=>'1','kelas_id'=>'2']
            // convert to new
            $data = array_column($kelasIds, 'kelas_id');
            // return ['1','2']
        }
        return $data;
    }

    private function getToken()
    {
        return $this->db->get('cbt_token')->row();
    }

    protected function getValidToken($ada_ujian)
    {
        $token = $this->getToken();
        if (!$token) {
            $data['token'] = '';
            $data['auto'] = '0';
            $data['elapsed'] = '00:00:00';
            return $data;
        }

        if ($token->auto == '0') {
            return $token;
        }

        $mulai = new DateTime($token->updated);
        $diff = $mulai->diff(new DateTime());
        $total_minutes = ($diff->days * 24 * 60);
        $total_minutes += ($diff->h * 60);
        $total_minutes += $diff->i;

        $updated = date('Y-m-d H:i:s');
        if ($total_minutes >= $token->jarak && $ada_ujian > 0) {
            $new = $this->createNewToken();
            $token->token = $new;
            $token->updated = $updated;
            $this->cbt->saveToken($token);
        }
        $token->now = date('Y-m-d H:i:s');
        return $token;
    }

    protected function createNewToken()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($chars);
        $new_token = '';
        for ($i = 0; $i < 6; $i++) {
            $random_character = $chars[mt_rand(0, $input_length - 1)];
            $new_token .= $random_character;
        }
        return $new_token;
    }

    protected function cekRekapSiswa()
    {
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');

        $rek = [];
        if ($this->db->table_exists('cbt_rekap_nilai')) {
            $this->db->select('id_rekap_nilai, id_jadwal');
            $rekap_nilai = $this->db->get('cbt_rekap_nilai')->result();
            foreach ($rekap_nilai as $rekap) {
                $rek[$rekap->id_jadwal][] = $rekap;
            }
        }

        $jadwals = $this->rekap_jadwal
            ->fields('id_jadwal')
            ->with_rekapsiswa('fields:*count*')
            ->get_all();

        $res = [];
        $ids_jadwal = [];
        if ($jadwals) {
            foreach ($jadwals as $jadwal) {
                $jml_nilai = count($rek[$jadwal->id_jadwal] ?? []);
                $jadwal->rekap_nilai = $jml_nilai;
                $jml_rekap = 0;
                if (isset($jadwal->rekapsiswa)) {
                    $jml_rekap = intval($jadwal->rekapsiswa->{0}->counted_rows);
                    $jadwal->rekap_siswa = $jml_rekap;
                    unset($jadwal->rekapsiswa);
                } else {
                    $jadwal->rekap_siswa = 0;
                }
                $jadwal->rekap = $jml_rekap < $jml_nilai ? '0' : '1';
                if ($jml_rekap < $jml_nilai) {
                    $res[$jadwal->id_jadwal] = $jadwal;
                }
            }
        }

        $data['rekaps'] = $res;
        $data['ids_jadwal'] = array_keys($res);
        return $data;
    }

    /*
    private function simpanNilai($arr_siswa) {
        $nilai = [];
        foreach ($arr_siswa as $cbt) {
            $input = $cbt->cbtsiswa->nilai_input;
            $nilai_in['id_siswa'] = $input->id_siswa;
            $nilai_in['id_jadwal'] = $input->id_jadwal;
            $nilai_in['dikoreksi'] = '0';

            $nilai_s = [
                'id_cbt_siswa' => $cbt->cbtsiswa->id_cbt_siswa,
                'nilai' => []
            ];
            if (isset($input->skor_pg['detail'])) {
                foreach ($input->skor_pg['detail'] as $detail) {
                    if (isset($detail->id_soal)) {
                        $nilai_s['nilai'][] = [
                            'id_bank'   => $cbt->cbtsiswa->id_bank,
                            'id_jadwal' => $cbt->cbtsiswa->id_jadwal,
                            'id_soal'   => $detail->id_soal,
                            'nilai_koreksi' => 0,
                            'nilai_otomatis' => 0,
                            'point_soal' => $detail->point_soal
                        ];
                        $nilai_in['pg_benar'] = $input->pg_benar;
                        $nilai_in['pg_nilai'] = $input->pg_nilai;
                    }
                }
            }
            if (isset($input->skor_kompleks['detail'])) {
                foreach ($input->skor_kompleks['detail'] as $detail) {
                    if (isset($detail->id_soal)) {
                        $nilai_s['nilai'][] = [
                            'id_bank' => $cbt->cbtsiswa->id_bank,
                            'id_jadwal' => $cbt->cbtsiswa->id_jadwal,
                            'id_soal' => $detail->id_soal,
                            'nilai_koreksi' => 0,
                            'nilai_otomatis' => 0,
                            'point_soal' => $detail->point_soal
                        ];
                    }
                }
            }
            if (isset($input->skor_jodohkan['detail'])) {
                foreach ($input->skor_jodohkan['detail'] as $detail) {
                    if (isset($detail->id_soal)) {
                        $nilai_s['nilai'][] = [
                            'id_bank' => $cbt->cbtsiswa->id_bank,
                            'id_jadwal' => $cbt->cbtsiswa->id_jadwal,
                            'id_soal' => $detail->id_soal,
                            'nilai_koreksi' => 0,
                            'nilai_otomatis' => 0,
                            'point_soal' => $detail->point_soal
                        ];
                    }
                }
            }
            if (isset($input->skor_isian['detail'])) {
                foreach ($input->skor_isian['detail'] as $detail) {
                    if (isset($detail->id_soal)) {
                        $nilai_s['nilai'][] = [
                            'id_bank' => $cbt->cbtsiswa->id_bank,
                            'id_jadwal' => $cbt->cbtsiswa->id_jadwal,
                            'id_soal' => $detail->id_soal,
                            'nilai_koreksi' => 0,
                            'nilai_otomatis' => 0,
                            'point_soal' => $detail->point_soal
                        ];
                    }
                }
            }
            if (isset($input->skor_essai['detail'])) {
                foreach ($input->skor_essai['detail'] as $detail) {
                    if (isset($detail->id_soal)) {
                        $nilai_s['nilai'][] = [
                            'id_bank' => $cbt->cbtsiswa->id_bank,
                            'id_jadwal' => $cbt->cbtsiswa->id_jadwal,
                            'id_soal' => $detail->id_soal,
                            'nilai_koreksi' => 0,
                            'nilai_otomatis' => 0,
                            'point_soal' => $detail->point_soal
                        ];
                    }
                }
            }
            $nilai_in['kompleks_nilai'] = '0';
            $nilai_in['jodohkan_nilai'] = '0';
            $nilai_in['isian_nilai'] = '0';
            $nilai_in['essai_nilai'] = '0';

            $nilai_s['nilai_input'] = json_encode($nilai_in);
            $nilai_s['nilai'] = json_encode($nilai_s['nilai']);
            $nilai[$cbt->cbtsiswa->id_cbt_siswa] = $nilai_s;
        }
        return $nilai;
    }
    */

    protected function simpanNilai($arr_siswa)
    {
        $nilai = [];

        foreach ($arr_siswa as $cbt) {
            $input = $cbt->cbtsiswa->nilai_input;
            $id_cbt_siswa = $cbt->cbtsiswa->id_cbt_siswa;
            $id_bank = $cbt->cbtsiswa->id_bank;
            $id_jadwal = $cbt->cbtsiswa->id_jadwal;

            $nilai_in = [
                'id_siswa' => $input->id_siswa,
                'id_jadwal' => $id_jadwal,
                'dikoreksi' => '0',
                'pg_benar' => $input->pg_benar ?? 0,
                'pg_nilai' => $input->pg_nilai ?? 0,
                'kompleks_nilai' => 0,
                'jodohkan_nilai' => 0,
                'isian_nilai' => 0,
                'essai_nilai' => 0,
                'urutan_nilai' => 0,
                'cloze_nilai' => 0
            ];

            $skorSemua = [];
            foreach (['skor_pg', 'skor_kompleks', 'skor_jodohkan', 'skor_isian', 'skor_essai', 'skor_urutan', 'skor_cloze'] as $tipe) {
                // Safely get the property
                $scoreData = $input->{$tipe} ?? null;
                $details = [];

                if (is_array($scoreData) && isset($scoreData['detail'])) {
                    $details = $scoreData['detail'];
                } elseif (is_object($scoreData) && isset($scoreData->detail)) {
                    $details = $scoreData->detail;
                }

                if (!empty($details)) {
                    $skorSemua = array_merge($skorSemua, (array) $details);
                }
            }

            $nilai_detail = [];
            foreach ($skorSemua as $detail) {
                $detail = (object) $detail;
                if (!isset($detail->id_soal))
                    continue;
                $nilai_detail[] = [
                    'id_bank' => $id_bank,
                    'id_jadwal' => $id_jadwal,
                    'id_soal' => $detail->id_soal,
                    'nilai_koreksi' => 0,
                    'nilai_otomatis' => 0,
                    'point_soal' => $detail->point_soal
                ];
            }

            $nilai[$id_cbt_siswa] = [
                'id_cbt_siswa' => $id_cbt_siswa,
                'nilai_input' => json_encode($nilai_in),
                'nilai' => json_encode($nilai_detail)
            ];
        }

        return $nilai;
    }

    /**
     * @throws JsonException
     */
    protected function olah_nilai($type_query, $id_tp, $id_smt, $query, $id_jadwal, $koreksi = false)
    {
        // type query: 1=kelas, 2=id_siswa
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_log_model', 'cbt_log_model');

        if ($type_query == 1) {
            $sesisiswa_where = [
                'kelas_id' => $query,
                'tp_id' => $id_tp,
                'smt_id' => $id_smt
            ];
        } else if ($type_query == 2) {
            $sesisiswa_where = [
                'siswa_id' => $query,
                'tp_id' => $id_tp,
                'smt_id' => $id_smt
            ];
        } else {
            $sesisiswa_where = [];
        }

        $jadwal = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,pelanggaran,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak,pelanggaran,katrol_max,katrol_min')
            ->where(['id_jadwal' => $id_jadwal])
            ->with_jenis()
            ->with_sesisiswa([
                'fields' => 'siswa_id,kelas_id,ruang_id,sesi_id,tp_id,smt_id',
                'where' => $sesisiswa_where,
                'with' => [
                    ['relation' => 'siswa', 'fields' => 'id_siswa,nama,nis,agama'],
                    ['relation' => 'peserta', 'fields' => 'nomor_peserta'],
                    ['relation' => 'kelas', 'fields' => 'kode_kelas,nama_kelas'],
                    ['relation' => 'ruang', 'fields' => 'kode_ruang,nama_ruang'],
                    ['relation' => 'sesi', 'fields' => 'kode_sesi,nama_sesi'],
                ]
            ])
            ->with_bank([
                'fields' => 'bank_kode,bank_kelas,opsi,jml_soal,tampil_pg,bobot_pg,jml_kompleks,tampil_kompleks,bobot_kompleks,jml_jodohkan,tampil_jodohkan,bobot_jodohkan,jml_isian,tampil_isian,bobot_isian,jml_esai,tampil_esai,bobot_esai,id_tp,id_smt,soal_agama,tampil_urutan,bobot_urutan,tampil_cloze,bobot_cloze',
                'where' => ['id_tp' => $id_tp, 'id_smt' => $id_smt],
                'with' => [
                    [
                        'relation' => 'soal',
                        'fields' => 'id_soal, bank_id, mapel_id, jenis, nomor_soal, soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan',
                        'where' => ['tampilkan' => '1']
                    ],
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel', 'where' => []],
                    ['relation' => 'guru', 'fields' => 'kode_guru,nama_guru', 'where' => []],
                ]
            ])
            ->get();

        if (isset($jadwal->bank)) {
            $info = $jadwal->bank;
            foreach ($info->soal as $soal) {
                $soal->opsi_a = $this->maybe_unserialize($soal->opsi_a ?? '');
                $soal->jawaban = $this->maybe_unserialize($soal->jawaban ?? '');
            }
            $soals = (array) $info->soal;
            $info->soal = $soals;

            if (!empty($info->bank_kelas) && is_string($info->bank_kelas) && ($info->bank_kelas[0] == '[' || $info->bank_kelas[0] == '{')) {
                $info->bank_kelas = $this->maybe_unserialize($info->bank_kelas);
            } else {
                $kelasIds = $this->maybe_unserialize($info->bank_kelas ?? []);
                $info->bank_kelas = array_column((array) $kelasIds, 'kelas_id');
            }

            // OPTIMASI: Ambil ID siswa yang relevan dari hasil filter jadwal.
            if ($type_query == 1) {
                $siswa_ids = array_column((array) ($jadwal->sesisiswa ?? []), 'siswa_id');
            } else {
                $siswa_ids = [$query];
            }

            if (empty($siswa_ids)) {
                // Jika tidak ada siswa yang cocok dengan filter, keluar lebih awal
                unset($info->soal);
                return (object) $jadwal;
            }

            $cbtsiswa = $this->cbt_siswa_model
                ->where('id_jadwal', $id_jadwal)
                ->where('id_siswa', $siswa_ids)
                ->get_all();
            $logsiswa = $this->cbt_log_model
                ->where('id_jadwal', $id_jadwal)
                ->where('id_siswa', $siswa_ids)
                ->get_all();

            if ($cbtsiswa && isset($jadwal->sesisiswa)) {
                $map_cbt = [];
                $map_pelanggaran = [];
                foreach ($cbtsiswa as $cbt) {
                    $map_pelanggaran[$cbt->id_siswa] = $this->maybe_unserialize($cbt->pelanggaran ?? '{}');
                    $map_cbt[$cbt->id_siswa] = $cbt;
                }
                $map_log = [];
                foreach ($logsiswa as $log) {
                    $map_log[$log->id_siswa][] = $log;
                }
                $arr_cbt_empty = [];
                foreach ($jadwal->sesisiswa as $idx => $siswa) {
                    if ($info->soal_agama != '-' && $info->soal_agama != '0' && $info->soal_agama !== $siswa->siswa->agama) {
                        unset($jadwal->sesisiswa[$idx]);
                        $siswa->cbtsiswa = (object) [];
                        $siswa->log = [];
                    } else {
                        if (isset($map_cbt[$siswa->siswa_id])) {
                            if ($map_cbt[$siswa->siswa_id]->nilai == '[]') {
                                $arr_cbt_empty[] = $map_cbt[$siswa->siswa_id]->id_cbt_siswa;
                            }
                        }
                        $siswa->cbtsiswa = $map_cbt[$siswa->siswa_id] ?? (object) [];
                        $siswa->log = $map_log[$siswa->siswa_id] ?? [];
                        $siswa->pelanggaran = $map_pelanggaran[$siswa->siswa_id] ?? (object) [];
                    }
                }
                $adaNilaiKosong = count($arr_cbt_empty) > 0;
                $jadwal->sesisiswa = array_values((array) $jadwal->sesisiswa);
                $this->createPointSiswa($jadwal->sesisiswa, $info, $adaNilaiKosong);

                if ($adaNilaiKosong) {
                    $arr_siswa = array_filter($jadwal->sesisiswa, function ($cbt) use ($arr_cbt_empty) {
                        return in_array($cbt->id_cbt_siswa, $arr_cbt_empty);
                    });
                    $resultSiampan = $this->simpanNilai($arr_siswa);
                    $jadwal->ada_nilai_kosong = $resultSiampan;

                    $db_debug = $this->db->db_debug;
                    $this->db->db_debug = FALSE;

                    // Ensure indexed array and catch errors
                    if (!$this->db->update_batch('cbt_siswa', array_values($resultSiampan), 'id_cbt_siswa')) {
                        $error = $this->db->error();
                        $this->db->db_debug = $db_debug;
                        throw new \Exception("DB Update Error " . $error['code'] . ": " . $error['message']);
                    }
                    $this->db->db_debug = $db_debug;
                }
                $jadwal->type_query = $type_query;
                if ($koreksi) {
                    $jadwal->soal_pg = array_values(array_filter($soals, static function ($val) {
                        return ($val->jenis == '1' && $val->tampilkan == '1');
                    }));
                    $jadwal->soal_kompleks = array_values(array_filter($soals, static function ($val) {
                        return ($val->jenis == '2' && $val->tampilkan == '1');
                    }));
                    $jadwal->soal_jodohkan = array_values(array_filter($soals, static function ($val) {
                        return ($val->jenis == '3' && $val->tampilkan == '1');
                    }));
                    $jadwal->soal_isian = array_values(array_filter($soals, static function ($val) {
                        return ($val->jenis == '4' && $val->tampilkan == '1');
                    }));
                    $jadwal->soal_esai = array_values(array_filter($soals, static function ($val) {
                        return ($val->jenis == '5' && $val->tampilkan == '1');
                    }));
                    $jadwal->soal_urutan = array_values(array_filter($soals, static function ($val) {
                        return ($val->jenis == '6' && $val->tampilkan == '1');
                    }));
                    $jadwal->soal_cloze = array_values(array_filter($soals, static function ($val) {
                        return ($val->jenis == '7' && $val->tampilkan == '1');
                    }));
                }
            }
            unset($info->soal);
        }
        return (object) $jadwal;
    }

    private function createMenit($durasi)
    {
        if (empty($durasi) || !is_string($durasi) || strpos($durasi, ':') == false) {
            return 0;
        }

        $parts = explode(":", $durasi);
        $jam = isset($parts[0]) ? (int) $parts[0] : 0;
        $menit = isset($parts[1]) ? (int) $parts[1] : 0;
        $detik = isset($parts[2]) ? (int) $parts[2] : 0;

        $totalMenit = ($jam * 60) + $menit + ($detik / 60);
        return round($totalMenit, 2);
    }

    private function createPointSiswa($jadwal, $info, $adaNilaiKosong)
    {
        $bobot = $this->calculateWeights($info);
        $soals = $info->soal;
        foreach ($jadwal as $status) {
            $status->cbtsiswa->jawaban = $this->maybe_unserialize($status->cbtsiswa->jawaban ?? '[]');
            $status->cbtsiswa->nilai = $this->maybe_unserialize($status->cbtsiswa->nilai ?? '[]');
            $nilai_input = $this->maybe_unserialize($status->cbtsiswa->nilai_input ?? '{}');

            if (!is_array($status->cbtsiswa->jawaban))
                $status->cbtsiswa->jawaban = [];
            if (!is_array($status->cbtsiswa->nilai))
                $status->cbtsiswa->nilai = [];
            if (!is_object($nilai_input))
                $nilai_input = new stdClass();

            $durasi = (object) [
                "status" => $status->cbtsiswa->status ?? '',
                "mulai" => $status->cbtsiswa->mulai ?? '',
                "selesai" => $status->cbtsiswa->selesai ?? '',
                "lama_ujian" => $status->cbtsiswa->lama_ujian ?? '',
                "reset_izin" => $status->cbtsiswa->reset_izin ?? '',
                "reset_waktu" => $status->cbtsiswa->reset_waktu ?? '',
                "menit" => $this->createMenit($status->cbtsiswa->lama_ujian ?? '')
            ];

            if (!isset($nilai_input->id_siswa) && isset($status->cbtsiswa->id_siswa)) {
                $nilai_input->id_siswa = $status->cbtsiswa->id_siswa;
                $nilai_input->id_jadwal = $status->cbtsiswa->id_jadwal;
                $nilai_input->dikoreksi = '0';
                $nilai_input->kompleks_nilai = '0';
                $nilai_input->jodohkan_nilai = '0';
                $nilai_input->isian_nilai = '0';
                $nilai_input->essai_nilai = '0';
            }
            $nilai_input->skor_pg = [];
            $nilai_input->skor_kompleks = [];
            $nilai_input->skor_jodohkan = [];
            $nilai_input->skor_isian = [];
            $nilai_input->skor_essai = [];
            $nilai_input->skor_urutan = [];
            $nilai_input->skor_cloze = [];

            $jawabans_siswa = [];
            // Proses pengelompokan soal berdasarkan jenis_soal
            foreach ($soals as $dataSoal) {
                $idx_jwbn = array_search($dataSoal->id_soal, array_column($status->cbtsiswa->jawaban, 'id_soal'), true);
                $idx_nilai = array_search($dataSoal->id_soal, array_column($status->cbtsiswa->nilai, 'id_soal'), true);

                if ($idx_jwbn !== false) {
                    $no_soal = $dataSoal->nomor_soal;
                    $jawaban_siswa = (object) $status->cbtsiswa->jawaban[$idx_jwbn];
                    if ($dataSoal->jenis == '3') {
                        $arrAlphabet = range('A', 'Z');
                        if (!isset($jawaban_siswa->jawaban_siswa) || !isset($jawaban_siswa->jawaban_siswa->links)) {
                            $arrjwbnSiswa = [];
                            if ($jawaban_siswa->jawaban_siswa) {
                                foreach ($jawaban_siswa->jawaban_siswa->jawaban as $idx => $jbs) {
                                    if ($idx > 0) {
                                        $arrjwbnSiswa[$idx] = [];
                                        if (is_array($jbs) || is_object($jbs)) {
                                            foreach ($jbs as $idxs => $jb) {
                                                if ($idxs > 0) {
                                                    if ($jb == '1') {
                                                        $arrjwbnSiswa[$idx][] = $arrAlphabet[$idxs - 1];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($jawaban_siswa->jawaban_siswa) {
                                $jawaban_siswa->jawaban_siswa->links = json_decode(json_encode($arrjwbnSiswa));
                            } else {
                                $jawaban_siswa->jawaban_siswa = ['links' => $arrjwbnSiswa];
                                $jawaban_siswa->jawaban_siswa = json_decode(json_encode($jawaban_siswa->jawaban_siswa));
                            }
                        }

                        $dataSoal->jawaban = json_decode(json_encode($dataSoal->jawaban));
                        $arrjwbn = [];
                        foreach ($dataSoal->jawaban->jawaban as $idx => $jbs) {
                            if ($idx > 0) {
                                $arrjwbn[$idx] = [];
                                if (is_array($jbs) || is_object($jbs)) {
                                    foreach ($jbs as $idxs => $jb) {
                                        if ($idxs > 0) {
                                            if ($jb == '1') {
                                                $arrjwbn[$idx][] = $arrAlphabet[$idxs - 1];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $dataSoal->jawaban->links = json_decode(json_encode($arrjwbn));
                        $jawaban_siswa->jawaban_benar = $dataSoal->jawaban;
                        if ($jawaban_siswa->jawaban_benar) {
                            $jawaban_siswa->jawaban_benar->links = json_decode(json_encode($arrjwbn));
                        } else {
                            $jawaban_siswa->jawaban_benar = ['links' => $arrjwbn];
                            $jawaban_siswa->jawaban_benar = json_decode(json_encode($jawaban_siswa->jawaban_benar));
                        }
                    }
                    $nilai = $status->cbtsiswa->nilai[$idx_nilai] ?? [];
                    $jawabans_siswa[$dataSoal->jenis][$no_soal] = [
                        'jawab' => $jawaban_siswa,
                        'nilai' => (object) $nilai
                    ];
                } else {
                    $jawabans_siswa[$dataSoal->jenis][$dataSoal->nomor_soal] = [
                        'jawab' => null,
                        'nilai' => (object) []
                    ];
                }
            }
            $ada_jawaban = count($status->cbtsiswa->jawaban) > 0;
            $ada_jawaban_pg = $ada_jawaban && isset($jawabans_siswa['1']);
            if ($ada_jawaban_pg)
                $this->processNilaiPg($info, $jawabans_siswa['1'], $bobot, $nilai_input, $status->cbtsiswa->nilai);
            $ada_jawaban_pg2 = $ada_jawaban && isset($jawabans_siswa['2']);
            if ($ada_jawaban_pg2)
                $this->processNilaiKompleks($info, $jawabans_siswa['2'], $bobot, $nilai_input, $status->cbtsiswa->nilai);
            $ada_jawaban_jodoh = $ada_jawaban && isset($jawabans_siswa['3']);
            if ($ada_jawaban_jodoh)
                $this->processNilaiJodohkan($info, $jawabans_siswa['3'], $bobot, $nilai_input, $status->cbtsiswa->nilai);
            $ada_jawaban_isian = $ada_jawaban && isset($jawabans_siswa['4']);
            if ($ada_jawaban_isian)
                $this->processNilaiIsian($info, $jawabans_siswa['4'], $bobot, $nilai_input, $status->cbtsiswa->nilai);
            $ada_jawaban_essai = $ada_jawaban && isset($jawabans_siswa['5']);
            if ($ada_jawaban_essai)
                $this->processNilaiEsai($info, $jawabans_siswa['5'], $bobot, $nilai_input, $status->cbtsiswa->nilai);
            // Type 6: Urutan (Ordering)
            $ada_jawaban_urutan = $ada_jawaban && isset($jawabans_siswa['6']);
            if ($ada_jawaban_urutan)
                $this->processNilaiUrutan($info, $jawabans_siswa['6'], $bobot, $nilai_input, $status->cbtsiswa->nilai);
            // Type 7: Cloze (Fill in the blanks)
            $ada_jawaban_cloze = $ada_jawaban && isset($jawabans_siswa['7']);
            if ($ada_jawaban_cloze)
                $this->processNilaiCloze($info, $jawabans_siswa['7'], $bobot, $nilai_input, $status->cbtsiswa->nilai);

            $total = ($nilai_input->skor_pg['skor_total'] ?? 0) + ($nilai_input->skor_kompleks['skor_total'] ?? 0)
                + ($nilai_input->skor_jodohkan['skor_total'] ?? 0) + ($nilai_input->skor_isian['skor_total'] ?? 0)
                + ($nilai_input->skor_essai['skor_total'] ?? 0) + ($nilai_input->skor_urutan['skor_total'] ?? 0)
                + ($nilai_input->skor_cloze['skor_total'] ?? 0);
            $nilai_input->skor_total = round($total, 2);

            $total_asli = ($nilai_input->skor_pg['skor_total'] ?? 0) + ($nilai_input->skor_kompleks['skor_asli'] ?? 0)
                + ($nilai_input->skor_jodohkan['skor_asli'] ?? 0) + ($nilai_input->skor_isian['skor_asli'] ?? 0)
                + ($nilai_input->skor_essai['skor_asli'] ?? 0) + ($nilai_input->skor_urutan['skor_asli'] ?? 0)
                + ($nilai_input->skor_cloze['skor_asli'] ?? 0);
            $nilai_input->skor_asli = round($total_asli, 2);

            $this->kalkulasiWaktu($durasi);
            $sudahMulai = $durasi->mulai != '';
            $sudahSelesai = $durasi->selesai != '';
            $iconLoading = $sudahMulai ? '<i class="bi bi-spinner fa-spin"></i> ' : '';
            $loading = $sudahSelesai ? '<i class="bi bi-check"></i> ' : $iconLoading;
            $durasi->status_ujian = $loading . $durasi->durasi;
            $status->durasi = $durasi;

            $status->cbtsiswa->nilai_input = $nilai_input;
            $status->nilai_akhir = $nilai_input;
            $status->id_cbt_siswa = $status->cbtsiswa->id_cbt_siswa ?? '';
            if (!$adaNilaiKosong) {
                unset($status->cbtsiswa);
            }
        }
    }

    protected function processNilaiPg($info, $jawabans, $bobot, $nilai_input, $nilai)
    {
        $arrJawabanPg = [];
        $skor_pg = 0;
        if ($info->tampil_pg > 0) {
            $soals = $info->soal;
            $benar_pg = 0;

            // 🚀 Optimasi 1: Buat indeks jawaban agar pencarian cepat O(1)
            $mapJawaban = [];
            foreach ($jawabans as $j) {
                if (isset($j['jawab']->id_soal)) {
                    $mapJawaban[(string) $j['jawab']->id_soal] = $j['jawab'];
                }
            }

            // 🚀 Optimasi 2: Hitung sekali nilai poin benar
            $point_benar = $info->bobot_pg > 0 ? round($info->bobot_pg / $info->tampil_pg, 2) : 0;

            foreach ($soals as $soal) {
                if ($soal->jenis != '1')
                    continue;

                $idsoal = (string) $soal->id_soal;
                $jawab = $mapJawaban[$idsoal] ?? null;

                $jawaban_siswa = strtoupper($jawab->jawaban_siswa ?? '-');
                $jawaban_benar = strtoupper($jawab->jawaban_benar ?? '');
                $benar = $jawaban_siswa == $jawaban_benar;

                if ($benar)
                    $benar_pg++;

                $arrJawabanPg[$soal->nomor_soal] = (object) [
                    'nomor' => $soal->nomor_soal,
                    'id_soal' => $idsoal,
                    'jawaban_benar' => $jawaban_benar,
                    'jawaban_siswa' => $jawaban_siswa,
                    'analisa' => $benar
                        ? '<i class="bi bi-check-circle text-green text-lg"></i>'
                        : '<i class="bi bi-x-circle text-red text-lg"></i>',
                    'skor_jawaban' => $benar ? $point_benar : 0,
                    'benar' => $benar,
                    'point_soal' => $point_benar
                ];
            }

            // 🚀 Optimasi 3: Hilangkan usort jika sudah urut
            ksort($arrJawabanPg);

            // 🚀 Optimasi 4: Minimalisasi pembulatan
            $skor_pg = ($benar_pg / $bobot->bagi_pg) * $bobot->bobot_pg;
            $nilai_input->pg_benar = $benar_pg;
            $nilai_input->pg_nilai = $skor_pg;

            $nilai_input->skor_pg = [
                'detail' => array_values($arrJawabanPg),
                'skor_total' => round($skor_pg, 2)
            ];
        }
    }

    /*
    protected function processNilaiPg($info, $jawabans, $bobot, $nilai_input, $nilai) {
        $arrJawabanPg = [];
        $skor_pg = 0;
        if ($info->tampil_pg > 0) {
            $soals = $info->soal;
            $benar_pg = 0;
            foreach ($soals as $soal) {
                if ($soal->jenis != '1') {
                    continue; // Skip if not an essay question
                }
                $idsoal = $soal->id_soal;
                $num = $soal->nomor_soal;
                $ada_jawaban = array_filter($jawabans, function ($item) use ($idsoal) {
                    return isset($item['jawab']->id_soal) && (string)$item['jawab']->id_soal == (string)$idsoal;
                });
                $jawaban = null;
                if (count($ada_jawaban) > 0) {
                    $jawaban = array_shift($ada_jawaban);
                }

                $jawab = $jawaban['jawab'] ?? null;
                $point_benar = $info->bobot_pg > 0 ? round($info->bobot_pg / $info->tampil_pg, 2) : 0;
                if ($jawab && isset($jawab->jawaban_siswa)) {
                    $benar = strtoupper($jawab->jawaban_siswa ?? '-') == strtoupper($jawab->jawaban_benar ?? '');
                    if ($benar) {
                        $benar_pg += 1;
                    }
                    $arrJawabanPg[$num] = (object)[
                        'nomor' => $num,
                        'id_soal' => $jawab->id_soal,
                        'jawaban_benar' => strtoupper($jawab->jawaban_benar ?? ''),
                        'jawaban_siswa' => strtoupper($jawab->jawaban_siswa ?? ''),
                        'analisa'=> $benar ? '<i class="bi bi-check-circle text-green text-lg"></i>' : '<i class="bi bi-x-circle text-red text-lg"></i>',
                        'skor_jawaban' => !$benar || $info->bobot_pg == 0 ? 0 : round($info->bobot_pg / $info->tampil_pg, 2),
                        'benar' => $benar,
                        'point_soal' => $point_benar
                    ];
                } else {
                    $arrJawabanPg[$num] = (object)[
                        'nomor' => $num,
                        'jawaban_benar' => strtoupper($jawab->jawaban_benar ?? ''),
                        'jawaban_siswa' => '',
                        'analisa'=> '<i class="bi bi-x-circle text-red text-lg"></i>',
                        'benar' => false,
                        'skor_jawaban' => 0
                    ];
                }

            }
            $skor_pg = ($benar_pg / $bobot->bagi_pg) * $bobot->bobot_pg;
            if (!isset($nilai_input->pg_benar)) {
                $nilai_input->pg_benar = $benar_pg;
                $nilai_input->pg_nilai = $skor_pg;
            }
        }
        usort($arrJawabanPg, function ($item1, $item2) {
            return $item1->nomor <=> $item2->nomor;
        });
        $nilai_input->skor_pg['detail'] = array_values($arrJawabanPg);
        $nilai_input->skor_pg['skor_total'] = round($skor_pg, 2);
    }
    */

    protected function processNilaiKompleks($info, $jawabans, $bobot, $nilai_input, $nilai)
    {
        $benar_pg2 = 0;
        $skor_total = 0.0;
        $detail = [];
        if ($info->tampil_kompleks > 0) {
            $soals = $info->soal;
            foreach ($soals as $soal) {
                if ($soal->jenis != '2') {
                    continue; // Skip if not an essay question
                }
                $idsoal = $soal->id_soal;
                $num = $soal->nomor_soal;
                $ada_jawaban = array_filter($jawabans, function ($item) use ($idsoal) {
                    return isset($item['jawab']->id_soal) && (string) $item['jawab']->id_soal == (string) $idsoal;
                });
                $jawaban = null;
                if (count($ada_jawaban) > 0) {
                    $jawaban = array_shift($ada_jawaban);
                }

                $jawab = $jawaban['jawab'] ?? null;
                $point_benar = $info->bobot_kompleks > 0 ? round($info->bobot_kompleks / $info->tampil_kompleks, 2) : 0;
                $nilai_pg2 = $jawaban['nilai'] ?? null;
                if ($jawab && isset($jawab->jawaban_siswa) && is_array($jawab->jawaban_siswa)) {
                    $jawaban_siswa = array_map('strtoupper', array_filter($jawab->jawaban_siswa, function ($item) {
                        return !is_null($item);
                    }));
                    $jawaban_benar = array_map('strtoupper', array_filter($jawab->jawaban_benar, function ($item) {
                        return !is_null($item);
                    }));
                    $arr_benar = array_intersect($jawaban_siswa, $jawaban_benar);
                    if (count($jawaban_benar) > 0) {
                        $benar_pg2 += (1 / count($jawaban_benar)) * count($arr_benar);
                    }

                    $point_item = count($jawaban_benar) > 0 ? $point_benar / count($jawaban_benar) : 0;
                    $pk = $point_item * count($arr_benar);

                    $jml_benar = count($arr_benar);
                    if ($jml_benar == count($jawaban_benar)) {
                        $analisa = '<i class="bi bi-check-circle text-green text-lg"></i>';
                    } elseif (($jml_benar > 0) && ($jml_benar < count($jawaban_benar))) {
                        $analisa = '<i class="bi bi-x-circle text-yellow text-lg"></i>';
                    } else {
                        $analisa = '<i class="bi bi-x-circle text-red text-lg"></i>';
                    }

                    $point = round($pk, 2);

                    // nilai_otomatis: 0=otomatis (dari app), 1=nilai koreksi dari guru
                    // nilai_koreksi = dari menu koreksi
                    $skor_jawaban = $point;
                    if ($nilai_pg2 && isset($nilai_pg2->nilai_otomatis)) {
                        $skor_jawaban = $nilai_pg2->nilai_otomatis == '1' ? $nilai_pg2->nilai_koreksi : $point;
                    }

                    // skor_total = gabungan dari nilai koreksi dan nilai dari app
                    $skor_total += is_numeric($skor_jawaban) ? $skor_jawaban : 0;
                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $jawab->id_soal,
                        'jawaban_benar' => $jawaban_benar,
                        'jawaban_siswa' => $jawaban_siswa,
                        'analisa' => $analisa,
                        'nilai_otomatis' => $nilai_pg2->nilai_otomatis ?? '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                        'nilai_asli' => $point, // dari aplikasi
                        'nilai_koreksi' => $nilai_pg2->nilai_koreksi ?? $point, // dari guru
                        'skor_jawaban' => $skor_jawaban,
                        'point_soal' => $point_benar
                    ];
                } else {
                    $ada_nilai = array_filter($nilai, function ($item) use ($idsoal) {
                        return (string) $item->id_soal == (string) $idsoal;
                    });
                    if (count($ada_nilai) > 0) {
                        $nilai_pg2 = array_shift($ada_nilai);
                        $skor_total += is_numeric($nilai_pg2->nilai_koreksi) ? $nilai_pg2->nilai_koreksi : 0;
                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => $soal->jawaban ?? '--',
                            'jawaban_siswa' => $jawab->jawaban_siswa ?? '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => $nilai_is->nilai_otomatis ?? '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => $nilai_is->nilai_koreksi ?? '0', // dari guru
                            'skor_jawaban' => $nilai_is->nilai_koreksi ?? '0',
                            'point_soal' => $point_benar
                        ];
                    } else {
                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => '--',
                            'jawaban_siswa' => '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => '0', // dari guru
                            'skor_jawaban' => '0',
                            'point_soal' => $point_benar
                        ];
                    }
                }
            }
        }
        $nilai_input->skor_kompleks['jml_benar'] = round($benar_pg2, 2);
        $nilai_input->skor_kompleks['detail'] = array_values($detail);
        $nilai_input->skor_kompleks['skor_total'] = isset($nilai_input->kompleks_nilai) ? round($nilai_input->kompleks_nilai == '0' ? $skor_total : $nilai_input->kompleks_nilai, 2) : 0;
        $nilai_input->skor_kompleks['skor_asli'] = round($skor_total, 2);

        // skor dari app
        $s_pg2 = $bobot->bagi_pg2 == 0 ? 0 : ($benar_pg2 / $bobot->bagi_pg2) * $bobot->bobot_pg2;

        // $nilai_input->kompleks_nilai = dari input nilai tanpa koreksi
        // cek apakah ada nilai input
        $ada_nilai_input = isset($nilai_input->kompleks_nilai) && $nilai_input->kompleks_nilai != '0';

        // jika ada $nilai_input maka abaikan gabungan nilai koreksi dan nilai dari app
        $skor_total_app = $ada_nilai_input ? $nilai_input->kompleks_nilai : $s_pg2;
        $nilai_input->skor_kompleks['skor_total_app'] = round($skor_total_app, 2);
    }

    protected function processNilaiJodohkan($info, $jawabans, $bobot, $nilai_input, $nilai)
    {
        $benar_jod = 0;
        $skor_total = 0.0;
        $detail = [];

        if ($info->tampil_jodohkan > 0) {
            $soals = $info->soal;
            foreach ($soals as $soal) {
                if ($soal->jenis != '3') {
                    continue; // Skip if not an essay question
                }
                $idsoal = $soal->id_soal;
                $num = $soal->nomor_soal;
                $ada_jawaban = array_filter($jawabans, function ($item) use ($idsoal) {
                    return isset($item['jawab']->id_soal) && (string) $item['jawab']->id_soal == (string) $idsoal;
                });
                $jawaban = null;
                if (count($ada_jawaban) > 0) {
                    $jawaban = array_shift($ada_jawaban);
                }

                $jawab = $jawaban['jawab'] ?? null;
                $point_benar = $info->bobot_jodohkan > 0 ? round($info->bobot_jodohkan / $info->tampil_jodohkan, 2) : 0;
                if ($jawab && isset($jawab->jawaban_siswa) && isset($jawab->jawaban_siswa->type)) {
                    $typeSoal = $jawab->jawaban_siswa->type;
                    $arrSoal = (array) $jawab->jawaban_benar->jawaban;

                    $item_benar = 0;
                    $item_salah = 0;
                    $item_kurang = 0;
                    $items = 0;
                    $arrBenar = []; // count same each subitem

                    if (isset($jawab->jawaban_siswa->links)) {
                        $array1 = (array) $jawab->jawaban_benar->links;
                        $this->sortArrays($array1);
                        $array2 = (array) $jawab->jawaban_siswa->links;
                        $this->sortArrays($array2);

                        foreach ($array1 as $key => $subArray1) {
                            $arrBenar[$key] = new stdClass();
                            $arrBenar[$key]->benar = 0;
                            $arrBenar[$key]->salah = 0;
                            $arrBenar[$key]->kurang = 0;
                            $items += count($subArray1);
                            if (isset($array2[$key])) {
                                $subArray2 = $array2[$key];

                                // Count the same items
                                $sameItems = array_intersect($subArray1, $subArray2);
                                //$sameCount += count($sameItems);
                                $item_benar += count($sameItems);
                                $arrBenar[$key]->benar += count($sameItems);

                                // Count the different items
                                $diffItems1 = array_diff($subArray1, $subArray2);
                                $diffItems2 = array_diff($subArray2, $subArray1);
                                //$differentCount += count($diffItems1) + count($diffItems2);
                                $item_kurang += count($diffItems1) + count($diffItems2);
                                $arrBenar[$key]->kurang += count($diffItems1);
                            } else {
                                // If the key doesn't exist in the second array, all items are different
                                $item_kurang += count($subArray1);
                                $arrBenar[$key]->kurang += count($subArray1);
                            }
                        }
                    }

                    $point_soal = $items == 0 ? 0 : ((1 / $items) * $item_benar) * $point_benar;
                    $benar_jod += $items == 0 ? 0 : (1 / $items) * $item_benar;

                    $headSoal = array_shift($arrSoal);
                    $arrJwbSoal = [];
                    foreach ($arrSoal as $kolSoal) {
                        $jwb = new stdClass();
                        foreach ($kolSoal as $pos => $kol) {
                            if ($kol == '1') {
                                $jwb->subtitle[] = $headSoal[$pos];
                            }
                        }
                        $kolSoalArr = (array) $kolSoal;
                        $jwb->title = array_shift($kolSoalArr);
                        $arrJwbSoal[] = $jwb;
                    }

                    $arrJawab = [];
                    $headJawab = [];
                    if (isset($jawab->jawaban_siswa->jawaban)) {
                        $arrJawab = (array) $jawab->jawaban_siswa->jawaban;
                        $headJawab = array_shift($arrJawab);
                    }
                    $arrJwbJawab = [];
                    foreach ($arrJawab as $kolJawab) {
                        $jwbs = new stdClass();
                        if (is_array($kolJawab) || is_object($kolJawab)) {
                            foreach ($kolJawab as $po => $kol) {
                                if ($kol == '1') {
                                    $sub = $headJawab[$po] ?? '';
                                    $jwbs->subtitle[] = $sub;
                                }
                            }
                        }
                        $kolJawabArr = (array) $kolJawab;
                        $jwbs->title = array_shift($kolJawabArr);
                        $arrJwbJawab[] = $jwbs;
                    }

                    $point = round($point_soal, 2);
                    $nilai_jod = $jawaban['nilai'];
                    if ($nilai_jod) {
                        $skor_jawaban = isset($nilai_jod->nilai_otomatis) && $nilai_jod->nilai_otomatis == '1' ? $nilai_jod->nilai_koreksi : $point;
                    } else {
                        $skor_jawaban = $point;
                    }

                    $skor_total += is_numeric($skor_jawaban) ? $skor_jawaban : 0;

                    if ($item_benar == $items && $item_salah == 0 && $item_kurang == 0) {
                        $analisa = '<i class="bi bi-check-circle text-green text-lg"></i>';
                    } elseif ($item_benar == 0) {
                        $analisa = '<i class="bi bi-x-circle text-red text-lg"></i>';
                    } else {
                        $analisa = '<i class="bi bi-x-circle text-yellow text-lg"></i>';
                    }

                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $jawab->id_soal,
                        'jawaban_benar' => $jawab->jawaban_benar,
                        'jawaban_siswa' => $jawab->jawaban_siswa,
                        'type_soal' => $typeSoal,
                        'tabel_soal' => $arrJwbSoal,
                        'tabel_benar' => $arrBenar,
                        'tabel_jawab' => $arrJwbJawab,
                        'analisa' => $analisa,
                        'nilai_otomatis' => $nilai_jod->nilai_otomatis ?? '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                        'nilai_asli' => $point, // dari aplikasi
                        'nilai_koreksi' => $nilai_jod->nilai_koreksi ?? $point, // dari guru
                        'skor_jawaban' => $skor_jawaban,
                        'point_soal' => $point_benar
                    ];
                } else {
                    $ada_nilai = array_filter($nilai, function ($item) use ($idsoal) {
                        return (string) $item->id_soal == (string) $idsoal;
                    });
                    if (count($ada_nilai) > 0) {
                        $nilai_is = array_shift($ada_nilai);
                        $point_benar = $info->bobot_esai > 0 ? round($info->bobot_esai / $info->tampil_esai, 2) : 0;
                        $skor_total += is_numeric($nilai_is->nilai_koreksi) ? $nilai_is->nilai_koreksi : 0;

                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => $soal->jawaban ?? '--',
                            'jawaban_siswa' => $jawab->jawaban_siswa ?? '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => $nilai_is->nilai_otomatis ?? '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => $nilai_is->nilai_koreksi ?? '0', // dari guru
                            'skor_jawaban' => $nilai_is->nilai_koreksi ?? '0',
                            'point_soal' => $point_benar,
                            'type_soal' => $typeSoal ?? '',
                            'tabel_soal' => $arrJwbSoal ?? '',
                            'tabel_benar' => $arrBenar ?? '',
                            'tabel_jawab' => $arrJwbJawab ?? '',
                        ];
                    } else {
                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => '--',
                            'jawaban_siswa' => '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => '0', // dari guru
                            'skor_jawaban' => '0',
                            'point_soal' => $point_benar,
                            'type_soal' => $typeSoal ?? '',
                            'tabel_soal' => $arrJwbSoal ?? '',
                            'tabel_benar' => $arrBenar ?? '',
                            'tabel_jawab' => $arrJwbJawab ?? ''
                        ];
                    }
                }
            }
        }
        $nilai_input->skor_jodohkan['jml_benar'] = round($benar_jod, 2);
        $nilai_input->skor_jodohkan['detail'] = array_values($detail);
        $nilai_input->skor_jodohkan['skor_total'] = isset($nilai_input->jodohkan_nilai) ? round($nilai_input->jodohkan_nilai == '0' ? $skor_total : $nilai_input->jodohkan_nilai, 2) : 0;
        $nilai_input->skor_jodohkan['skor_asli'] = round($skor_total, 2);

        // skor dari app
        $s_jod = $bobot->bagi_jodoh == 0 ? 0 : ($benar_jod / $bobot->bagi_jodoh) * $bobot->bobot_jodoh;

        // $nilai_input->isian_nilai = dari input nilai tanpa koreksi
        // cek apakah ada nilai input
        $ada_nilai_input = isset($nilai_input->jodohkan_nilai) && $nilai_input->jodohkan_nilai != '0';

        // jika ada $nilai_input maka abaikan gabungan nilai koreksi dan nilai dari app
        $skor_total_app = $ada_nilai_input ? $nilai_input->jodohkan_nilai : $s_jod;
        $nilai_input->skor_jodohkan['skor_total_app'] = round($skor_total_app, 2);
    }

    protected function processNilaiIsian($info, $jawabans, $bobot, $nilai_input, $nilai)
    {
        $skor_total = 0.0;
        $benar_is = 0;
        $detail = [];

        if ($info->tampil_isian > 0) {
            $soals = $info->soal;
            foreach ($soals as $soal) {
                if ($soal->jenis != '4') {
                    continue; // Skip if not an essay question
                }
                $idsoal = $soal->id_soal;
                $num = $soal->nomor_soal;
                $ada_jawaban = array_filter($jawabans, function ($item) use ($idsoal) {
                    return isset($item['jawab']->id_soal) && (string) $item['jawab']->id_soal == (string) $idsoal;
                });
                $jawaban = null;
                if (count($ada_jawaban) > 0) {
                    $jawaban = array_shift($ada_jawaban);
                }

                $jawab = $jawaban['jawab'] ?? null;
                $point_benar = $info->bobot_isian > 0 ? round($info->bobot_isian / $info->tampil_isian, 2) : 0;
                if ($jawab && isset($jawab->jawaban_siswa)) {
                    $jawaban_siswa = strtolower(strip_tags($jawab->jawaban_siswa ?? '-'));
                    $jawaban_benar = strtolower(strip_tags($jawab->jawaban_benar ?? ''));
                    $benar = $jawaban_siswa == $jawaban_benar;

                    if ($benar) {
                        $benar_is++;
                    }

                    // Hitung poin hanya jika jawaban benar
                    $point = $benar ? $point_benar : 0; // Jawaban salah dapat 0 point

                    // Ambil nilai koreksi jika tersedia
                    $nilai_is = $jawaban['nilai'] ?? null;
                    $skor_jawaban = $nilai_is && isset($nilai_is->nilai_otomatis) && $nilai_is->nilai_otomatis == '1'
                        ? $nilai_is->nilai_koreksi
                        : $point;

                    $skor_total += is_numeric($skor_jawaban) ? $skor_jawaban : 0;

                    // Analisa benar atau salah
                    $analisa = $benar
                        ? '<i class="bi bi-check-circle text-green text-lg"></i>'
                        : '<i class="bi bi-x-circle text-yellow text-lg"></i>';

                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $jawab->id_soal ?? null,
                        'jawaban_benar' => $jawab->jawaban_benar ?? '--',
                        'jawaban_siswa' => $jawab->jawaban_siswa ?? '-',
                        'analisa' => $analisa,
                        'nilai_otomatis' => $nilai_is->nilai_otomatis ?? '0',
                        'nilai_asli' => $point,
                        'nilai_koreksi' => $nilai_is->nilai_koreksi ?? $point,
                        'skor_jawaban' => $skor_jawaban,
                        'point_soal' => $point_benar
                    ];
                } else {
                    $ada_nilai = array_filter($nilai, function ($item) use ($idsoal) {
                        return (string) $item->id_soal == (string) $idsoal;
                    });
                    if (count($ada_nilai) > 0) {
                        $nilai_is = array_shift($ada_nilai);
                        $skor_total += is_numeric($nilai_is->nilai_koreksi) ? $nilai_is->nilai_koreksi : 0;
                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => $soal->jawaban ?? '--',
                            'jawaban_siswa' => $jawab->jawaban_siswa ?? '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => $nilai_is->nilai_otomatis ?? '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => $nilai_is->nilai_koreksi ?? '0', // dari guru
                            'skor_jawaban' => $nilai_is->nilai_koreksi ?? '0',
                            'point_soal' => $point_benar
                        ];
                    } else {
                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => '--',
                            'jawaban_siswa' => '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => '0', // dari guru
                            'skor_jawaban' => '0',
                            'point_soal' => $point_benar
                        ];
                    }
                }
            }
        }

        $nilai_input->skor_isian['jml_benar'] = round($benar_is, 2);
        $nilai_input->skor_isian['detail'] = array_values($detail);
        $nilai_input->skor_isian['skor_total'] = isset($nilai_input->isian_nilai)
            ? round($nilai_input->isian_nilai == '0' ? $skor_total : $nilai_input->isian_nilai, 2)
            : 0;
        $nilai_input->skor_isian['skor_asli'] = round($skor_total, 2);

        // Perhitungan skor dari aplikasi
        $s_is = $bobot->bagi_isian > 0
            ? ($benar_is / $bobot->bagi_isian) * $bobot->bobot_isian
            : 0;

        $ada_nilai_koreksi = isset($nilai_input->isian_nilai) && $nilai_input->isian_nilai != '0';
        $skor_total_app = $ada_nilai_koreksi ? $nilai_input->isian_nilai : $s_is;
        $nilai_input->skor_isian['skor_total_app'] = round($skor_total_app, 2);
    }

    protected function processNilaiEsai($info, $jawabans, $bobot, $nilai_input, $nilai)
    {
        $skor_total = 0.0;
        $benar_es = 0;
        $detail = [];

        if ($info->tampil_esai > 0) {
            $soals = $info->soal;
            foreach ($soals as $soal) {
                if ($soal->jenis != '5') {
                    continue; // Skip if not an essay question
                }
                $idsoal = $soal->id_soal;
                $num = $soal->nomor_soal;
                $ada_jawaban = array_filter($jawabans, function ($item) use ($idsoal) {
                    return isset($item['jawab']->id_soal) && (string) $item['jawab']->id_soal == (string) $idsoal;
                });

                $jawaban = null;
                if (count($ada_jawaban) > 0) {
                    $jawaban = array_shift($ada_jawaban);
                }

                $jawab = $jawaban['jawab'] ?? null;
                $point_benar = $info->bobot_esai > 0 ? round($info->bobot_esai / $info->tampil_esai, 2) : 0;
                if ($jawab && isset($jawab->jawaban_siswa)) {
                    $jawaban_siswa = strtolower(strip_tags($jawab->jawaban_siswa ?? '-'));
                    $jawaban_benar = strtolower(strip_tags($jawab->jawaban_benar ?? ''));
                    $benar = $jawaban_siswa == $jawaban_benar;

                    if ($benar) {
                        $benar_es++;
                    }

                    $point = $benar ? $point_benar : 0;

                    $nilai_es = $jawaban['nilai'] ?? null;
                    $skor_jawaban = $nilai_es && isset($nilai_es->nilai_otomatis) && $nilai_es->nilai_otomatis == '1'
                        ? $nilai_es->nilai_koreksi
                        : $point;

                    $skor_total += is_numeric($skor_jawaban) ? $skor_jawaban : 0;

                    $analisa = $benar
                        ? '<i class="bi bi-check-circle text-green text-lg"></i>'
                        : '<i class="bi bi-x-circle text-yellow text-lg"></i>';

                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $jawab->id_soal ?? null,
                        'jawaban_benar' => $jawab->jawaban_benar ?? '--',
                        'jawaban_siswa' => $jawab->jawaban_siswa ?? '-',
                        'analisa' => $analisa,
                        'nilai_otomatis' => $nilai_es->nilai_otomatis ?? '0',
                        'nilai_asli' => $point,
                        'nilai_koreksi' => $nilai_es->nilai_koreksi ?? $point,
                        'skor_jawaban' => $skor_jawaban,
                        'point_soal' => $point_benar
                    ];
                } else {
                    $ada_nilai = array_filter($nilai, function ($item) use ($idsoal) {
                        return (string) $item->id_soal == (string) $idsoal;
                    });
                    if (count($ada_nilai) > 0) {
                        $nilai_es = array_shift($ada_nilai);
                        $skor_total += is_numeric($nilai_es->nilai_koreksi) ? $nilai_es->nilai_koreksi : 0;
                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => $soal->jawaban ?? '--',
                            'jawaban_siswa' => $jawab->jawaban_siswa ?? '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => $nilai_es->nilai_otomatis ?? '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => $nilai_es->nilai_koreksi ?? '0', // dari guru
                            'skor_jawaban' => $nilai_es->nilai_koreksi ?? '0',
                            'point_soal' => $point_benar
                        ];
                    } else {
                        $detail[$num] = (object) [
                            'nomor' => $num,
                            'id_soal' => $soal->id_soal,
                            'jawaban_benar' => '--',
                            'jawaban_siswa' => '-',
                            'analisa' => '<i class="bi bi-x-circle text-yellow text-lg"></i>',
                            'nilai_otomatis' => '0', // 0=otomatis (dari app), 1=nilai koreksi dari guru
                            'nilai_asli' => '0', // dari aplikasi
                            'nilai_koreksi' => '0', // dari guru
                            'skor_jawaban' => '0',
                            'point_soal' => $point_benar
                        ];
                    }
                }
            }
        }

        $nilai_input->skor_essai['jml_benar'] = round($benar_es, 2);
        $nilai_input->skor_essai['detail'] = array_values($detail);
        $nilai_input->skor_essai['skor_total'] = isset($nilai_input->essai_nilai) ? round($nilai_input->essai_nilai == '0' ? $skor_total : $nilai_input->essai_nilai, 2) : 0;
        $nilai_input->skor_essai['skor_asli'] = round($skor_total, 2);

        $s_es = $bobot->bagi_essai == 0 ? 0 : ($benar_es / $bobot->bagi_essai) * $bobot->bobot_essai;
        $ada_nilai_koreksi = isset($nilai_input->essai_nilai) && $nilai_input->essai_nilai != '0';
        $skor_total_app = $ada_nilai_koreksi ? $nilai_input->essai_nilai : $s_es;
        $nilai_input->skor_essai['skor_total_app'] = round($skor_total_app, 2);
    }

    /**
     * Process scoring for Type 6: Urutan (Ordering) questions
     * Compares student order with correct order
     */
    protected function processNilaiUrutan($info, $jawabans, $bobot, $nilai_input, $nilai)
    {
        $benar_urutan = 0;
        $skor_total = 0.0;
        $detail = [];

        $tampil_urutan = $info->tampil_urutan ?? 0;
        $bobot_urutan = $info->bobot_urutan ?? 0;

        if ($tampil_urutan > 0) {
            $soals = $info->soal;
            foreach ($soals as $soal) {
                if ($soal->jenis != '6')
                    continue;

                $idsoal = $soal->id_soal;
                $num = $soal->nomor_soal;
                $point_benar = $bobot_urutan > 0 ? round($bobot_urutan / $tampil_urutan, 2) : 0;

                $ada_jawaban = array_filter($jawabans, function ($item) use ($idsoal) {
                    return isset($item['jawab']->id_soal) && (string) $item['jawab']->id_soal == (string) $idsoal;
                });

                $jawaban = count($ada_jawaban) > 0 ? array_shift($ada_jawaban) : null;
                $jawab = $jawaban['jawab'] ?? null;

                if ($jawab && isset($jawab->jawaban_siswa)) {
                    // Normalize Student Answer
                    $jawaban_siswa = $jawab->jawaban_siswa;
                    if (is_string($jawaban_siswa)) {
                        $jawaban_siswa = json_decode($jawaban_siswa, true);
                    } elseif (is_object($jawaban_siswa)) {
                        $jawaban_siswa = (array) $jawaban_siswa;
                    }
                    if (!is_array($jawaban_siswa))
                        $jawaban_siswa = [];
                    $jawaban_siswa = array_values($jawaban_siswa);

                    // Normalize Correct Answer
                    $jawaban_benar = $jawab->jawaban_benar;
                    if (is_string($jawaban_benar)) {
                        $jawaban_benar = json_decode($jawaban_benar, true);
                    } elseif (is_object($jawaban_benar)) {
                        $jawaban_benar = (array) $jawaban_benar;
                    }
                    if (!is_array($jawaban_benar))
                        $jawaban_benar = [];
                    $jawaban_benar = array_values($jawaban_benar);

                    // Calculate how many positions are correct
                    $total_items = count($jawaban_benar);
                    $correct_positions = 0;

                    if ($total_items > 0 && count($jawaban_siswa) == $total_items) {
                        for ($i = 0; $i < $total_items; $i++) {
                            // Loose comparison (==) allows string "1" to match int 1
                            if ($jawaban_siswa[$i] == $jawaban_benar[$i]) {
                                $correct_positions++;
                            }
                        }
                    }

                    $ratio = $total_items > 0 ? $correct_positions / $total_items : 0;
                    $point = round($point_benar * $ratio, 2);
                    $benar_urutan += $ratio;
                    $skor_total += $point;

                    $analisa = $ratio == 1 ? '<i class="bi bi-check-circle text-green text-lg"></i>' :
                        ($ratio > 0 ? '<i class="bi bi-x-circle text-yellow text-lg"></i>' :
                            '<i class="bi bi-x-circle text-red text-lg"></i>');

                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $idsoal,
                        'jawaban_benar' => $jawaban_benar,
                        'jawaban_siswa' => $jawaban_siswa,
                        'correct_positions' => $correct_positions,
                        'total_items' => $total_items,
                        'analisa' => $analisa,
                        'skor_jawaban' => $point,
                        'point_soal' => $point_benar
                    ];
                } else {
                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $idsoal,
                        'jawaban_benar' => '--',
                        'jawaban_siswa' => '-',
                        'analisa' => '<i class="bi bi-x-circle text-red text-lg"></i>',
                        'skor_jawaban' => 0,
                        'point_soal' => $point_benar
                    ];
                }
            }
        }

        $nilai_input->skor_urutan['jml_benar'] = round($benar_urutan, 2);
        $nilai_input->skor_urutan['detail'] = array_values($detail);
        $nilai_input->skor_urutan['skor_total'] = round($skor_total, 2);
        $nilai_input->skor_urutan['skor_asli'] = round($skor_total, 2);
    }

    /**
     * Process scoring for Type 7: Cloze (Fill in the Blanks) questions
     * Compares each blank answer with correct answers
     */
    protected function processNilaiCloze($info, $jawabans, $bobot, $nilai_input, $nilai)
    {
        $benar_cloze = 0;
        $skor_total = 0.0;
        $detail = [];

        $tampil_cloze = $info->tampil_cloze ?? 0;
        $bobot_cloze = $info->bobot_cloze ?? 0;

        if ($tampil_cloze > 0) {
            $soals = $info->soal;
            foreach ($soals as $soal) {
                if ($soal->jenis != '7')
                    continue;

                $idsoal = $soal->id_soal;
                $num = $soal->nomor_soal;
                $point_benar = $bobot_cloze > 0 ? round($bobot_cloze / $tampil_cloze, 2) : 0;

                $ada_jawaban = array_filter($jawabans, function ($item) use ($idsoal) {
                    return isset($item['jawab']->id_soal) && (string) $item['jawab']->id_soal == (string) $idsoal;
                });

                $jawaban = count($ada_jawaban) > 0 ? array_shift($ada_jawaban) : null;
                $jawab = $jawaban['jawab'] ?? null;

                if ($jawab && isset($jawab->jawaban_siswa)) {
                    // Safe decode Student Answer
                    $jawaban_siswa = $jawab->jawaban_siswa;
                    if (is_string($jawaban_siswa)) {
                        $jawaban_siswa = json_decode($jawaban_siswa, true);
                    } elseif (is_object($jawaban_siswa)) {
                        $jawaban_siswa = (array) $jawaban_siswa;
                    }
                    if (!is_array($jawaban_siswa))
                        $jawaban_siswa = [];

                    // Safe decode Correct Answer
                    $jawaban_benar = $jawab->jawaban_benar;
                    if (is_string($jawaban_benar)) {
                        $jawaban_benar = json_decode($jawaban_benar, true);
                    } elseif (is_object($jawaban_benar)) {
                        $jawaban_benar = (array) $jawaban_benar;
                    }
                    if (!is_array($jawaban_benar))
                        $jawaban_benar = [];

                    // Count correct blanks
                    $total_blanks = count($jawaban_benar);
                    $correct_blanks = 0;
                    $blank_results = [];

                    foreach ($jawaban_benar as $blank_id => $correct_answer) {
                        $student_answer = $jawaban_siswa[$blank_id] ?? '';
                        // Case insensitive comparison by default
                        $is_correct = strtolower(trim($student_answer)) == strtolower(trim($correct_answer));
                        if ($is_correct)
                            $correct_blanks++;
                        $blank_results[$blank_id] = [
                            'correct' => $correct_answer,
                            'student' => $student_answer,
                            'is_correct' => $is_correct
                        ];
                    }

                    $ratio = $total_blanks > 0 ? $correct_blanks / $total_blanks : 0;
                    $point = round($point_benar * $ratio, 2);
                    $benar_cloze += $ratio;
                    $skor_total += $point;

                    $analisa = $ratio == 1 ? '<i class="bi bi-check-circle text-green text-lg"></i>' :
                        ($ratio > 0 ? '<i class="bi bi-x-circle text-yellow text-lg"></i>' :
                            '<i class="bi bi-x-circle text-red text-lg"></i>');

                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $idsoal,
                        'jawaban_benar' => $jawaban_benar,
                        'jawaban_siswa' => $jawaban_siswa,
                        'blank_results' => $blank_results,
                        'correct_blanks' => $correct_blanks,
                        'total_blanks' => $total_blanks,
                        'analisa' => $analisa,
                        'skor_jawaban' => $point,
                        'point_soal' => $point_benar
                    ];
                } else {
                    $detail[$num] = (object) [
                        'nomor' => $num,
                        'id_soal' => $idsoal,
                        'jawaban_benar' => '--',
                        'jawaban_siswa' => '-',
                        'analisa' => '<i class="bi bi-x-circle text-red text-lg"></i>',
                        'skor_jawaban' => 0,
                        'point_soal' => $point_benar
                    ];
                }
            }
        }

        $nilai_input->skor_cloze['jml_benar'] = round($benar_cloze, 2);
        $nilai_input->skor_cloze['detail'] = array_values($detail);
        $nilai_input->skor_cloze['skor_total'] = round($skor_total, 2);
        $nilai_input->skor_cloze['skor_asli'] = round($skor_total, 2);
    }

    function kalkulasiWaktu($durasi)
    {
        // Reset waktu bisa dilakukan sebelum tanggal ujian berakhir
        // Jika tgl ujian sudah berakhir, silakan tambahkan tgl akhir ujian
        $lamanya = $durasi->lama_ujian;
        $mulai = strtotime($durasi->mulai ?? '') * 1000; // Konversi ke timestamp dalam milidetik
        if (!$lamanya || strpos($lamanya, ':') == false) {
            $selesai = strtotime($durasi->selesai ?? '') * 1000;
            $lama = $selesai - $mulai;
            $lamanya = gmdate('H:i:s', $lama / 1000); // Format H:i:s
        }
        //if (!empty($durasi->mulai) && !empty($durasi->selesai)) {}

        $durasi->lama_ujian = $lamanya;

        $elap = $lamanya ? explode(':', $lamanya) : [];
        $ej = ($elap[0] == "00") ? '' : ((int) $elap[0]) . ' jam ';
        $em = ($elap[1] == "00") ? '' : ((int) $elap[1]) . ' mnt ';
        $ed = ($elap[2] == "00") ? '' : ((int) $elap[2]) . ' dtk';
        $durasi->mulai_ujian = $durasi->mulai ? date('H:i', strtotime($durasi->mulai ?? '')) : '';
        if ($ej == '' && $em == '') {
            $durasi->durasi = $ed;
        } else {
            $durasi->durasi = $ej . $em; // . $ed;
        }
    }

    /**
     * Calculate weights for different question types
     * @param object $info Question bank info
     * @return object Calculated weights
     */
    protected function calculateWeights($info)
    {
        return (object) [
            'bagi_pg' => max(1, $info->tampil_pg) / 100,
            'bobot_pg' => $info->bobot_pg / 100,
            'bagi_pg2' => max(1, $info->tampil_kompleks) / 100,
            'bobot_pg2' => $info->bobot_kompleks / 100,
            'bagi_jodoh' => max(1, $info->tampil_jodohkan) / 100,
            'bobot_jodoh' => $info->bobot_jodohkan / 100,
            'bagi_isian' => max(1, $info->tampil_isian) / 100,
            'bobot_isian' => $info->bobot_isian / 100,
            'bagi_essai' => max(1, $info->tampil_esai) / 100,
            'bobot_essai' => $info->bobot_esai / 100,
            'bagi_urutan' => max(1, $info->tampil_urutan ?? 0) / 100,
            'bobot_urutan' => ($info->bobot_urutan ?? 0) / 100,
            'bagi_cloze' => max(1, $info->tampil_cloze ?? 0) / 100,
            'bobot_cloze' => ($info->bobot_cloze ?? 0) / 100
        ];
    }

    protected function createSkorSiswa($rekap, $koreksi)
    {
        $info = $rekap->bank_soal;
        $bobot = $this->calculateWeights($info);
        $soals = array_merge(
            $rekap->soal_pg,
            $rekap->soal_kompleks,
            $rekap->soal_jodohkan,
            $rekap->soal_isian,
            $rekap->soal_esai,
            $rekap->soal_urutan ?? [],
            $rekap->soal_cloze ?? []
        );
        $info->soal = $soals;

        foreach ($rekap->rekapsiswa as $rkp_siswa) {
            $ruang_sesi = $rkp_siswa->ruang_sesi;
            $rkp_siswa->ruang_sesi = $this->maybe_unserialize($ruang_sesi);

            $nilai = $rkp_siswa->nilai ?? '[]';
            $rkp_siswa->nilai = $this->maybe_unserialize($nilai);

            $jawaban = $rkp_siswa->jawaban ?? '[]';
            $rkp_jawaban = $this->maybe_unserialize($jawaban);

            $input = $rkp_siswa->nilai_input ?? '{}';
            $nilai_input = $this->maybe_unserialize($input);

            $durasi = (object) [
                "status" => $rkp_siswa->status ?? '',
                "mulai" => $rkp_siswa->mulai ?? '',
                "selesai" => $rkp_siswa->selesai ?? '',
                "lama_ujian" => $rkp_siswa->lama_ujian ?? '',
                "menit" => $this->createMenit($rkp_siswa->lama_ujian ?? '')
            ];

            $nilai_input->skor_pg = [];
            $nilai_input->skor_kompleks = [];
            $nilai_input->skor_jodohkan = [];
            $nilai_input->skor_isian = [];
            $nilai_input->skor_essai = [];
            $nilai_input->skor_urutan = [];
            $nilai_input->skor_cloze = [];

            $jawabans_siswa = [];
            // Proses pengelompokan soal berdasarkan jenis_soal
            foreach ($rkp_jawaban as $jawaban_siswa) {
                $idx_soal = array_search($jawaban_siswa->id_soal, array_column($soals, 'id_soal'), true);
                // cek soal
                $dataSoal = null;
                if ($idx_soal !== false) {
                    $dataSoal = $soals[$idx_soal];
                    $jawaban_benar = $dataSoal->jawaban;
                } else {
                    $jawaban_benar = $jawaban_siswa->jawaban_benar ?? null;
                }
                $jawaban_siswa->jawaban_benar = $jawaban_benar;

                if ($dataSoal && $dataSoal->jenis == '3') {
                    $arrAlphabet = range('A', 'Z');
                    $jawaban_siswa->jawaban_siswa = $this->maybe_unserialize($jawaban_siswa->jawaban_siswa);
                    if (!isset($jawaban_siswa->jawaban_siswa) || !isset($jawaban_siswa->jawaban_siswa->links)) {
                        $arrjwbnSiswa = [];
                        if ($jawaban_siswa->jawaban_siswa) {
                            foreach ($jawaban_siswa->jawaban_siswa->jawaban as $idx => $jbs) {
                                if ($idx > 0) {
                                    $arrjwbnSiswa[$idx] = [];
                                    if (is_array($jbs) || is_object($jbs)) {
                                        foreach ($jbs as $idxs => $jb) {
                                            if ($idxs > 0) {
                                                if ($jb == '1') {
                                                    $arrjwbnSiswa[$idx][] = $arrAlphabet[$idxs - 1];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if ($jawaban_siswa->jawaban_siswa) {
                            $jawaban_siswa->jawaban_siswa->links = json_decode(json_encode($arrjwbnSiswa));
                        } else {
                            $jawaban_siswa->jawaban_siswa = ['links' => $arrjwbnSiswa];
                            $jawaban_siswa->jawaban_siswa = json_decode(json_encode($jawaban_siswa->jawaban_siswa));
                        }
                    }

                    if ($jawaban_benar) {
                        $arrjwbn = [];
                        foreach ($jawaban_benar->jawaban as $idx => $jbs) {
                            if ($idx > 0) {
                                $arrjwbn[$idx] = [];
                                if (is_array($jbs) || is_object($jbs)) {
                                    foreach ($jbs as $idxs => $jb) {
                                        if ($idxs > 0) {
                                            if ($jb == '1') {
                                                $arrjwbn[$idx][] = $arrAlphabet[$idxs - 1];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ($jawaban_siswa->jawaban_benar) {
                            $jawaban_siswa->jawaban_benar->links = json_decode(json_encode($arrjwbn));
                        } else {
                            $jawaban_siswa->jawaban_benar = ['links' => $arrjwbn];
                            $jawaban_siswa->jawaban_benar = json_decode(json_encode($jawaban_siswa->jawaban_benar));
                        }
                    }
                }

                $idx_nilai = array_search(
                    $jawaban_siswa->id_soal,
                    array_column($rkp_siswa->nilai, 'id_soal'),
                    true
                );
                if ($dataSoal) {
                    $jawabans_siswa[$dataSoal->jenis][$jawaban_siswa->id_soal] = [
                        'jawab' => $jawaban_siswa,
                        'nilai' => $idx_nilai !== false ? (object) $rkp_siswa->nilai[$idx_nilai] : null,
                    ];
                }
            }

            $ada_jawaban = count($rkp_jawaban) > 0;
            $ada_jawaban_pg = $ada_jawaban && isset($jawabans_siswa['1']);
            if ($ada_jawaban_pg)
                $this->processNilaiPg($info, $jawabans_siswa['1'], $bobot, $nilai_input, $rkp_siswa->nilai);
            $ada_jawaban_pg2 = $ada_jawaban && isset($jawabans_siswa['2']);
            if ($ada_jawaban_pg2)
                $this->processNilaiKompleks($info, $jawabans_siswa['2'], $bobot, $nilai_input, $rkp_siswa->nilai);
            $ada_jawaban_jodoh = $ada_jawaban && isset($jawabans_siswa['3']);
            if ($ada_jawaban_jodoh)
                $this->processNilaiJodohkan($info, $jawabans_siswa['3'], $bobot, $nilai_input, $rkp_siswa->nilai);
            $ada_jawaban_isian = $ada_jawaban && isset($jawabans_siswa['4']);
            if ($ada_jawaban_isian)
                $this->processNilaiIsian($info, $jawabans_siswa['4'], $bobot, $nilai_input, $rkp_siswa->nilai);
            $ada_jawaban_essai = $ada_jawaban && isset($jawabans_siswa['5']);
            if ($ada_jawaban_essai)
                $this->processNilaiEsai($info, $jawabans_siswa['5'], $bobot, $nilai_input, $rkp_siswa->nilai);
            $ada_jawaban_urutan = $ada_jawaban && isset($jawabans_siswa['6']);
            if ($ada_jawaban_urutan)
                $this->processNilaiUrutan($info, $jawabans_siswa['6'], $bobot, $nilai_input, $rkp_siswa->nilai);
            $ada_jawaban_cloze = $ada_jawaban && isset($jawabans_siswa['7']);
            if ($ada_jawaban_cloze)
                $this->processNilaiCloze($info, $jawabans_siswa['7'], $bobot, $nilai_input, $rkp_siswa->nilai);

            $total = ($nilai_input->skor_pg['skor_total'] ?? 0) + ($nilai_input->skor_kompleks['skor_total'] ?? 0)
                + ($nilai_input->skor_jodohkan['skor_total'] ?? 0) + ($nilai_input->skor_isian['skor_total'] ?? 0)
                + ($nilai_input->skor_essai['skor_total'] ?? 0) + ($nilai_input->skor_urutan['skor_total'] ?? 0)
                + ($nilai_input->skor_cloze['skor_total'] ?? 0);
            $nilai_input->skor_total = round($total, 2);

            $total_asli = ($nilai_input->skor_pg['skor_total'] ?? 0) + ($nilai_input->skor_kompleks['skor_asli'] ?? 0)
                + ($nilai_input->skor_jodohkan['skor_asli'] ?? 0) + ($nilai_input->skor_isian['skor_asli'] ?? 0)
                + ($nilai_input->skor_essai['skor_asli'] ?? 0) + ($nilai_input->skor_urutan['skor_asli'] ?? 0)
                + ($nilai_input->skor_cloze['skor_asli'] ?? 0);
            $nilai_input->skor_asli = round($total_asli, 2);

            $this->kalkulasiWaktu($durasi);
            $sudahMulai = $durasi->mulai != '';
            $sudahSelesai = $durasi->selesai != '';
            $iconLoading = $sudahMulai ? '<i class="bi bi-spinner fa-spin"></i> ' : '';
            $loading = $sudahSelesai ? '<i class="bi bi-check"></i> ' : $iconLoading;
            $durasi->status_ujian = $loading . $durasi->durasi;
            $rkp_siswa->durasi = $durasi;

            $rkp_siswa->nilai_akhir = $nilai_input;
            $rkp_siswa->pelanggaran = $this->maybe_unserialize($rkp_siswa->pelanggaran ?? '{}');

            //unset($rkp_siswa->jawaban, $rkp_siswa->nilai_input);
        }
        //if (!$koreksi) {
        //    unset($rekap->soal_pg, $rekap->soal_kompleks, $rekap->soal_jodohkan, $rekap->soal_isian, $rekap->soal_esai);
        //}
    }

    public function get_koreksi()
    {
        $is_rekap = $this->input->post('is_rekap');
        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');
        if ($is_rekap > 0) {
            $this->getKoreksiRekap($id_siswa, $id_jadwal);
        } else {
            $this->getKoreksiNilai($id_siswa, $id_jadwal);
        }
    }

    private function getKoreksiNilai($id_siswa, $id_jadwal)
    {
        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $jadwal = $this->olah_nilai(2, $tp->id_tp, $smt->id_smt, $id_siswa, $id_jadwal, true);
        $jadwal->sesisiswa = $jadwal->sesisiswa[0];

        $data['siswa'] = $jadwal->sesisiswa;
        $data['info'] = $jadwal->bank;
        $data['id_jadwal'] = $jadwal->id_jadwal;
        $data['jenis'] = $jadwal->jenis;
        $data['tp_jadwal'] = $tp->tahun;
        $data['jadwal'] = $jadwal;

        $soal_1 = array_filter($jadwal->soal_pg, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_2 = array_filter($jadwal->soal_kompleks, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_3 = array_filter($jadwal->soal_jodohkan, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_4 = array_filter($jadwal->soal_isian, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_5 = array_filter($jadwal->soal_esai, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_6 = array_filter($jadwal->soal_urutan ?? [], function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_7 = array_filter($jadwal->soal_cloze ?? [], function ($soal) {
            return $soal->tampilkan == '1';
        });
        $data['soal_pg'] = array_values($soal_1);
        $data['soal_kompleks'] = array_values($soal_2);
        $data['soal_jodohkan'] = array_values($soal_3);
        $data['soal_isian'] = array_values($soal_4);
        $data['soal_esai'] = array_values($soal_5);
        $data['soal_urutan'] = array_values($soal_6);
        $data['soal_cloze'] = array_values($soal_7);

        $data['is_rekap'] = 0;
        $data['success'] = true;
        $this->output_json($data);
    }

    /**
     * @throws JsonException
     */
    private function getKoreksiRekap($id_siswa, $id_jadwal)
    {
        $id_siswa = $this->input->post('siswa');
        $id_jadwal = $this->input->post('jadwal');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $rekap = $this->rekap_jadwal
            ->where(['id_jadwal' => $id_jadwal])
            ->with_rekapsiswa([
                'where' => ['id_siswa' => $id_siswa],
                /*
                'with' => [
                    ['relation'=>'siswa', 'fields'=>'id_siswa,nama,nis,agama']
                ]
                */
            ])
            ->get();

        $rekap->jadwal = $this->maybe_unserialize($rekap->jadwal);
        $rekap->bank_soal = $this->maybe_unserialize($rekap->bank_soal);
        $rekap->kelas_bank = $this->maybe_unserialize($rekap->kelas_bank);
        $arrIdKelas = [];
        foreach ($rekap->kelas_bank as $kls) {
            if (isset($kls->id_kelas)) {
                $arrIdKelas[$kls->id_kelas] = $kls->nama_kelas;
            }
        }
        $rekap->pengawas = $this->maybe_unserialize($rekap->pengawas);
        $soal_1 = array_filter($this->maybe_unserialize($rekap->soal_pg), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_2 = array_filter($this->maybe_unserialize($rekap->soal_kompleks), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_3 = array_filter($this->maybe_unserialize($rekap->soal_jodohkan), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_4 = array_filter($this->maybe_unserialize($rekap->soal_isian), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_5 = array_filter($this->maybe_unserialize($rekap->soal_esai), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_6 = array_filter($this->maybe_unserialize($rekap->soal_urutan ?? []), function ($soal) {
            return $soal->tampilkan == '1';
        });
        $soal_7 = array_filter($this->maybe_unserialize($rekap->soal_cloze ?? []), function ($soal) {
            return $soal->tampilkan == '1';
        });

        $rekap->soal_pg = array_values($soal_1);
        $rekap->soal_kompleks = array_values($soal_2);
        $rekap->soal_jodohkan = array_values($soal_3);
        $rekap->soal_isian = array_values($soal_4);
        $rekap->soal_esai = array_values($soal_5);
        $rekap->soal_urutan = array_values($soal_6);
        $rekap->soal_cloze = array_values($soal_7);
        $rekap->rekapsiswa = (array) $rekap->rekapsiswa;

        $this->createSkorSiswa($rekap, true);

        $jadwal = $rekap->jadwal;
        $jadwal->sesisiswa = $rekap->rekapsiswa[0];
        unset($rekap->rekapsiswa);

        $data['soal_pg'] = $rekap->soal_pg;
        $data['soal_kompleks'] = $rekap->soal_kompleks;
        $data['soal_jodohkan'] = $rekap->soal_jodohkan;
        $data['soal_isian'] = $rekap->soal_isian;
        $data['soal_esai'] = $rekap->soal_esai;
        $data['soal_urutan'] = $rekap->soal_urutan;
        $data['soal_cloze'] = $rekap->soal_cloze;

        $data['is_rekap'] = $jadwal->sesisiswa->id_rekap_siswa;
        $data['rekap_mapel'] = $rekap->nama_mapel;
        $data['rekap_guru'] = $rekap->nama_guru;
        $data['siswa'] = $jadwal->sesisiswa;
        $data['info'] = $rekap->bank_soal;
        $data['id_jadwal'] = $rekap->id_jadwal;
        $data['tp_jadwal'] = $rekap->tp;
        $data['jenis'] = (object) ['id_jenis' => $rekap->id_jenis, 'kode_jenis' => $rekap->kode_jenis];

        $data['success'] = true;
        $this->output_json($data);
    }

    public function simpanKoreksi()
    {
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Rekap_siswa_model', 'rekap_siswa');
        $this->load->model('Log_model', 'logging');

        $isRekap = $this->input->post('is_rekap', true);
        $siswa = $this->input->post('siswa', true);
        $jadwal = $this->input->post('jadwal', true);
        $bank = $this->input->post('bank', true);
        $field = $this->input->post('field', true);

        // Pastikan JSON yang diterima valid
        $nilais = json_decode($this->input->post('nilai', true));
        if (!is_array($nilais)) {
            $this->output_json(['success' => false, 'message' => 'Format data nilai tidak valid']);
            return;
        }

        $updated1 = true;
        $cbtsiswa = $this->cbt_siswa_model->fields('id_cbt_siswa,nilai,jawaban')->where(['id_siswa' => $siswa, 'id_jadwal' => $jadwal])->get();
        $missing_ids = [];

        if ($cbtsiswa && isset($cbtsiswa->nilai)) {
            $cbtsiswa->nilai = $this->maybe_unserialize($cbtsiswa->nilai);
            if (!is_array($cbtsiswa->nilai)) {
                $cbtsiswa->nilai = [];
            }

            $point_soal = 0;
            foreach ($cbtsiswa->nilai as $key => $nilai) {
                if ($nilai->point_soal != 0) {
                    $point_soal = $nilai->point_soal;
                }
                $idsoal = $nilai->id_soal;
                $for_update = array_filter($nilais, function ($item) use ($idsoal) {
                    return (string) $item->id_soal == (string) $idsoal;
                });
                if (!empty($for_update)) {
                    $nilai_siswa = array_values($for_update)[0];
                    $cbtsiswa->nilai[$key]->nilai_koreksi = $nilai_siswa->koreksi;
                    $cbtsiswa->nilai[$key]->nilai_otomatis = 1;
                }
            }

            $cbt_ids = array_column($cbtsiswa->nilai, 'id_soal');
            $missing_ids = array_filter($nilais, function ($item) use ($cbt_ids) {
                return !in_array($item->id_soal, $cbt_ids);
            });

            if (!empty($missing_ids)) {
                foreach ($missing_ids as $missing) {
                    $cbtsiswa->nilai[] = [
                        "id_bank" => (string) $bank,
                        "id_jadwal" => (string) $jadwal,
                        "id_soal" => (string) $missing->id_soal,
                        "nilai_koreksi" => $missing->koreksi,
                        "nilai_otomatis" => '1',
                        "point_soal" => (string) $point_soal
                    ];
                }
            }

            if (isset($cbtsiswa->id_cbt_siswa)) {
                $this->db->set('nilai', json_encode($cbtsiswa->nilai));
                $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
                $updated1 = $this->db->update('cbt_siswa');
            }
        }

        $updated2 = true;
        $rekapsiswa = $this->rekap_siswa->fields('id_rekap_siswa,nilai')->where(['id_siswa' => $siswa, 'id_jadwal' => $jadwal])->get();
        if ($rekapsiswa && isset($rekapsiswa->nilai)) {
            $rekapsiswa->nilai = $this->maybe_unserialize($rekapsiswa->nilai);
            if (!is_array($rekapsiswa->nilai)) {
                $rekapsiswa->nilai = [];
            }

            $point_soal = 0;
            foreach ($rekapsiswa->nilai as $key => $nilai) {
                $idsoal = $nilai->id_soal;
                if ($nilai->point_soal != 0) {
                    $point_soal = $nilai->point_soal;
                }
                $for_update = array_filter($nilais, function ($item) use ($idsoal) {
                    return (string) $item->id_soal == (string) $idsoal;
                });
                if (!empty($for_update)) {
                    $nilai_siswa = array_values($for_update)[0];
                    $rekapsiswa->nilai[$key]->nilai_koreksi = $nilai_siswa->koreksi;
                    $rekapsiswa->nilai[$key]->nilai_otomatis = 1;
                }
            }

            $cbt_ids = array_column($rekapsiswa->nilai, 'id_soal');
            $missing_ids = array_filter($nilais, function ($item) use ($cbt_ids) {
                return !in_array($item->id_soal, $cbt_ids);
            });

            if (!empty($missing_ids)) {
                foreach ($missing_ids as $missing) {
                    $rekapsiswa->nilai[] = [
                        "id_bank" => (string) $bank,
                        "id_jadwal" => (string) $jadwal,
                        "id_soal" => (string) $missing->id_soal,
                        "nilai_koreksi" => $missing->koreksi,
                        "nilai_otomatis" => '1',
                        "point_soal" => (string) $point_soal
                    ];
                }
            }

            $this->db->set('nilai', json_encode($rekapsiswa->nilai));
            $this->db->where('id_rekap_siswa', $rekapsiswa->id_rekap_siswa);
            $updated2 = $this->db->update('cbt_rekap_siswa');
        }

        $cbtsiswa = $this->cbt_siswa_model->fields('id_cbt_siswa,nilai,jawaban')->where(['id_siswa' => $siswa, 'id_jadwal' => $jadwal])->get();
        if ($cbtsiswa) {
            $cbtsiswa->nilai = $this->maybe_unserialize($cbtsiswa->nilai);
        }
        $rekapsiswa = $this->rekap_siswa->fields('id_rekap_siswa,nilai')->where(['id_siswa' => $siswa, 'id_jadwal' => $jadwal])->get();
        if ($rekapsiswa) {
            $rekapsiswa->nilai = $this->maybe_unserialize($rekapsiswa->nilai);
        }
        $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengkoreksi hasil ujian siswa');

        $this->output_json([
            'success' => $updated1 && $updated2,
            'cbt_siswa' => $cbtsiswa,
            'miss' => $missing_ids,
            'rekap_siswa' => $rekapsiswa
        ]);
    }

    public function tandaiKoreksi()
    {
        $siswa = $this->input->post('siswa', true);
        $jadwal = $this->input->post('jadwal', true);

        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Rekap_siswa_model', 'rekap_siswa');
        $this->load->model('Log_model', 'logging');

        $cbtsiswa = $this->cbt_siswa_model->fields('id_cbt_siswa,nilai_input')->where(['id_jadwal' => $jadwal, 'id_siswa' => $siswa])->get();

        $updated = true;
        if ($cbtsiswa) {
            $nilai_input = $this->maybe_unserialize($cbtsiswa->nilai_input ?? '{}');
            $nilai_input->dikoreksi = '1';

            $set_update = ['nilai_input' => json_encode($nilai_input)];
            $this->db->where('id_cbt_siswa', $cbtsiswa->id_cbt_siswa);
            $updated = $this->db->update('cbt_siswa', $set_update);
            $data['cbtsiswa'] = $set_update;
        }

        $updated2 = true;
        $rekapsiswa = $this->rekap_siswa->fields('id_rekap_siswa,nilai_input')->where(['id_siswa' => $siswa, 'id_jadwal' => $jadwal])->get();
        if ($rekapsiswa) {
            $nilai_input2 = $this->maybe_unserialize($rekapsiswa->nilai_input ?? '{}');
            $nilai_input2->dikoreksi = '1';

            $set_update2 = ['nilai_input' => json_encode($nilai_input2)];
            $this->db->where('id_rekap_siswa', $rekapsiswa->id_rekap_siswa);
            $updated2 = $this->db->update('cbt_rekap_siswa', $set_update2);
            $data['rekapsiswa'] = $set_update2;
        }
        $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengkoreksi hasil ujian siswa');

        $data['success'] = $updated && $updated2;
        $this->output_json($data);
    }

    public function mark_all()
    {
        //$this->load->model('Cbt_model', 'cbt');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Rekap_siswa_model', 'rekap_siswa');
        $this->load->model('Log_model', 'logging');

        $id_jadwal = $this->input->post('id_jadwal', true);
        $id_kelas = $this->input->post('id_kelas', true);
        $siswas = json_decode($this->input->post('id_siswa', true), true);
        $id_siswa = array_column($siswas, 'id_siswa');
        $map_mulai = [];
        foreach ($siswas as $mulai) {
            $map_mulai[$mulai['id_siswa']] = $mulai['memulai'];
        }

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $cbtsiswa = $this->cbt_siswa_model->fields('id_cbt_siswa,id_siswa,nilai_input')
            ->where(['id_jadwal' => $id_jadwal])
            ->where('id_siswa', $id_siswa)
            ->get_all();

        if ($cbtsiswa) {
            foreach ($cbtsiswa as $cbt) {
                if (isset($map_mulai[$cbt->id_siswa])) {
                    $nilai_input = json_decode($cbt->nilai_input ?? '{}');
                    $nilai_input->dikoreksi = $map_mulai[$cbt->id_siswa] == '2' ? '0' : '1';
                    $cbt->nilai_input = json_encode($nilai_input);
                }
            }
            $this->db->update_batch('cbt_siswa', (array) $cbtsiswa, 'id_cbt_siswa');
        }

        $rekapsiswa = $this->rekap_siswa->fields('id_rekap_siswa,id_siswa,nilai_input')
            ->where(['id_jadwal' => $id_jadwal])
            ->where('id_siswa', $id_siswa)
            ->get_all();

        if ($rekapsiswa) {
            foreach ($rekapsiswa as $rekap) {
                if (isset($map_mulai[$rekap->id_siswa])) {
                    $nilai_input2 = json_decode($rekap->nilai_input ?? '{}');
                    $nilai_input2->dikoreksi = $map_mulai[$rekap->id_siswa] == '2' ? '0' : '1';
                    $rekap->nilai_input = json_encode($nilai_input2);
                }
            }
            $this->db->update_batch('cbt_rekap_siswa', (array) $rekapsiswa, 'id_rekap_siswa');
        }

        $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengkoreksi hasil ujian siswa');
        $data['success'] = true;
        $data['siswa'] = $siswas;
        $data['rekap'] = (array) $rekapsiswa;
        $data['cbt'] = (array) $cbtsiswa;
        $this->output_json($data);
    }

    public function simpanNilaiInput()
    {
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Rekap_siswa_model', 'rekap_siswa');
        $this->load->model('Log_model', 'logging');

        $isRekap = $this->input->post('is_rekap', true);
        $jadwal = $this->input->post('jadwal', true);
        $banksoal = $this->input->post('bank_soal', true);
        $nilais = json_decode($this->input->post('nilai', true));
        $idSiswa = json_decode($this->input->post('id_siswa', true));

        $cbtSiswa = $this->cbt_siswa_model
            ->fields('id_cbt_siswa,id_siswa,nilai_input,status')
            ->where(['id_jadwal' => $jadwal])
            ->where('id_siswa', $idSiswa)
            ->get_all();

        $map_cbt = [];
        foreach ($cbtSiswa as $cbt) {
            $map_cbt[$cbt->id_siswa] = $cbt;
        }

        $rekapSiswa = $this->rekap_siswa
            ->fields('id_rekap_siswa,id_siswa,nilai_input,status')
            ->where(['id_jadwal' => $jadwal])
            ->where('id_siswa', $idSiswa)
            ->get_all();

        $map_rekap = [];
        if ($rekapSiswa) {
            foreach ($rekapSiswa as $rekap) {
                $map_rekap[$rekap->id_siswa] = $rekap;
            }
        }

        $insert_cbt = [];
        $insert_rekap = [];
        $updated = [];
        $rekaped = [];
        $blm_selesai = [];
        foreach ($nilais as $nilai) {
            $adaCbt = isset($map_cbt[$nilai->id_siswa]);
            if ($adaCbt) {
                $siswa = $map_cbt[$nilai->id_siswa];
                if ($siswa->status != '2') {
                    $blm_selesai[] = $siswa->id_siswa;
                } else {
                    $nilai_siswa = json_decode($siswa->nilai_input);
                    if (!is_object($nilai_siswa))
                        $nilai_siswa = new stdClass();

                    $nilai_siswa->kompleks_nilai = isset($nilai->kompleks_nilai) && $nilai->kompleks_nilai != null ? $nilai->kompleks_nilai : '0';
                    $nilai_siswa->jodohkan_nilai = isset($nilai->jodohkan_nilai) && $nilai->jodohkan_nilai != null ? $nilai->jodohkan_nilai : '0';
                    $nilai_siswa->isian_nilai = isset($nilai->isian_nilai) && $nilai->isian_nilai != null ? $nilai->isian_nilai : '0';
                    $nilai_siswa->essai_nilai = isset($nilai->essai_nilai) && $nilai->essai_nilai != null ? $nilai->essai_nilai : '0';
                    $nilai_siswa->urutan_nilai = isset($nilai->urutan_nilai) && $nilai->urutan_nilai != null ? $nilai->urutan_nilai : '0';
                    $nilai_siswa->cloze_nilai = isset($nilai->cloze_nilai) && $nilai->cloze_nilai != null ? $nilai->cloze_nilai : '0';
                    $forUpdate = [
                        'id_cbt_siswa' => $map_cbt[$nilai->id_siswa]->id_cbt_siswa,
                        'nilai_input' => json_encode($nilai_siswa)
                    ];
                    $updated[] = $forUpdate;
                }
            } else {
                continue; // Skip if no CBT record found for student
                /*
                $nilai_in['id_siswa'] = $nilai->id_siswa;
                $nilai_in['id_jadwal'] = $nilai->id_jadwal;
                $nilai_in['id_bank'] = $banksoal;
                $nilai_in['nilai_input'] = json_encode($nilai);
                $insert_cbt[] = $nilai_in;
                */
            }

            $adaRekap = isset($map_rekap[$nilai->id_siswa]);
            if ($adaRekap) {
                $siswa = $map_rekap[$nilai->id_siswa];
                if ($siswa->status != '2') {
                    $blm_selesai[] = $siswa->id_siswa;
                } else {
                    $nilai_siswa = json_decode($siswa->nilai_input);
                    $nilai_siswa->kompleks_nilai = isset($nilai->kompleks_nilai) && $nilai->kompleks_nilai != null ? $nilai->kompleks_nilai : '0';
                    $nilai_siswa->jodohkan_nilai = isset($nilai->jodohkan_nilai) && $nilai->jodohkan_nilai != null ? $nilai->jodohkan_nilai : '0';
                    $nilai_siswa->isian_nilai = isset($nilai->isian_nilai) && $nilai->isian_nilai != null ? $nilai->isian_nilai : '0';
                    $nilai_siswa->essai_nilai = isset($nilai->essai_nilai) && $nilai->essai_nilai != null ? $nilai->essai_nilai : '0';
                    $nilai_siswa->urutan_nilai = isset($nilai->urutan_nilai) && $nilai->urutan_nilai != null ? $nilai->urutan_nilai : '0';
                    $nilai_siswa->cloze_nilai = isset($nilai->cloze_nilai) && $nilai->cloze_nilai != null ? $nilai->cloze_nilai : '0';
                    $forRekap = [
                        'id_rekap_siswa' => $map_rekap[$nilai->id_siswa]->id_rekap_siswa,
                        'nilai_input' => json_encode($nilai_siswa)
                    ];
                    $rekaped[] = $forRekap;
                }
            } else {
                $nilai_in['id_siswa'] = $nilai->id_siswa;
                $nilai_in['id_jadwal'] = $nilai->id_jadwal;
                $nilai_in['id_bank'] = $banksoal;
                $nilai_in['nilai_input'] = json_encode($nilai);
                $insert_rekap[] = $nilai_in;
            }
        }
        if (count($updated) > 0) {
            $this->db->update_batch('cbt_siswa', $updated, 'id_cbt_siswa');
        }
        if (count($rekaped) > 0) {
            $this->db->update_batch('cbt_rekap_siswa', $rekaped, 'id_rekap_siswa');
        }
        $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Menginput nilai ujian siswa');
        $data['success'] = true;
        $data['data'] = $updated;
        $data['cbt'] = $cbtSiswa;
        $data['insert_cbt'] = $insert_cbt;
        $data['insert_rekap'] = $insert_rekap;
        $data['blm_selesai'] = count($blm_selesai);
        $this->output_json($data);
    }

    // new analysis
    // Fungsi untuk normalisasi data
    protected function normalizeData($siswa, $kunci)
    {
        $normalizedData = [];
        foreach ($siswa as $jawaban) {
            $filteredJawaban = [];
            foreach ($kunci as $key => $value) {
                if (isset($jawaban[$key])) {
                    $filteredJawaban[$key] = $jawaban[$key];
                }
            }
            $normalizedData[] = $filteredJawaban;
        }
        return $normalizedData;
    }

    // Fungsi untuk menghitung tingkat kesulitan, daya pembeda, dan validitas
    protected function analyzeData($siswa, $kunci, $opsi)
    {
        $results = [];
        $totalStudents = count($siswa);

        // Hitung total skor setiap siswa
        $totalScores = [];
        $responseSiswa = [];
        foreach ($siswa as $jawabanSiswa) {
            $score = 0;
            $response = [];
            foreach ($kunci as $key => $correctAnswer) {
                if (isset($jawabanSiswa[$key]) && strtoupper($jawabanSiswa[$key] ?? '') == strtoupper($correctAnswer ?? '')) {
                    $score++;
                    $response[] = 1;
                } else {
                    $response[] = 0;
                }
            }
            $totalScores[] = $score;
            $responseSiswa[] = $response;
        }

        // Kelompokkan siswa berdasarkan skor
        array_multisort($totalScores, SORT_DESC, $siswa);
        $upperGroup = array_slice($siswa, 0, ceil(0.27 * $totalStudents));
        $lowerGroup = array_slice($siswa, -ceil(0.27 * $totalStudents));

        // Analisis per soal
        foreach ($kunci as $key => $correctAnswer) {
            $totalCorrect = 0;
            $upperCorrect = 0;
            $lowerCorrect = 0;

            $choices = ['A', 'B', 'C']; // Asumsi pilihan jawaban
            if ($opsi == '4') {
                $choices[] = 'D';
            } elseif ($opsi == '5') {
                $choices[] = 'D';
                $choices[] = 'E';
            }
            $distraktor = array_fill_keys($choices, ['top' => 0, 'bottom' => 0, 'total' => 0]);

            foreach ($siswa as $jawabanSiswa) {
                if (isset($jawabanSiswa[$key]) && strtoupper($jawabanSiswa[$key] ?? '') == strtoupper($correctAnswer ?? '')) {
                    $totalCorrect++;
                }
                $jawaban = strtoupper($jawabanSiswa[$key] ?? '');
                if (in_array($jawaban, $choices)) {
                    $distraktor[$jawaban]['total']++;
                }
            }
            $discriminationUpper = 0;
            if (count($upperGroup) > 0) {
                foreach ($upperGroup as $jawabanSiswa) {
                    if (isset($jawabanSiswa[$key]) && strtoupper($jawabanSiswa[$key] ?? '') == strtoupper($correctAnswer ?? '')) {
                        $upperCorrect++;
                    }
                    $jawaban = strtoupper($jawabanSiswa[$key] ?? '');
                    if (in_array($jawaban, $choices)) {
                        $distraktor[$jawaban]['top']++;
                    }
                }
                $discriminationUpper = $upperCorrect / count($upperGroup);
            }

            $discriminationLower = 0;
            if (count($lowerGroup) > 0) {
                foreach ($lowerGroup as $jawabanSiswa) {
                    if (isset($jawabanSiswa[$key]) && strtoupper($jawabanSiswa[$key] ?? '') == strtoupper($correctAnswer ?? '')) {
                        $lowerCorrect++;
                    }
                    $jawaban = strtoupper($jawabanSiswa[$key] ?? '');
                    if (in_array($jawaban, $choices)) {
                        $distraktor[$jawaban]['bottom']++;
                    }
                }
                $discriminationLower = $lowerCorrect / count($lowerGroup);
            }

            $difficulty = $totalStudents > 0 ? $totalCorrect / $totalStudents : 0;
            $discrimination = $discriminationUpper - $discriminationLower;

            // Hitung validitas (korelasi biserial)
            $correctResponses = array_map(function ($jawabanSiswa) use ($key, $correctAnswer) {
                return (isset($jawabanSiswa[$key]) && strtoupper($jawabanSiswa[$key] ?? '') == strtoupper($correctAnswer ?? '')) ? 1 : 0;
            }, $siswa);

            $validity = $this->calculateBiserialCorrelation($correctResponses, $totalScores);

            /*
             * tingkat kesulitan
             * Mudah:  P > 0.70
             * Sedang: 0.30 ≤ P ≤ 0.70
             * Sulit: P < 0.30
             */
            $kesulitan = round($difficulty, 2);
            if ($kesulitan >= 0.70) {
                $lvl_sulit = 'Mudah';
            } elseif ($kesulitan < 0.30) {
                $lvl_sulit = 'Sulit';
            } else {
                $lvl_sulit = 'Sedang';
            }

            /*
             * DAYA PEMBEDA
             * D > 0.40: Baik
             * 0.20 ≤ D ≤ 0.40: Cukup
             * D < 0.20: Buruk
             */
            $pembeda = round($discrimination, 2);
            if ($pembeda >= 0.70) {
                $lvl_pembeda = 'Sangat Baik';
            } else if ($pembeda > 0.39 && $pembeda < 0.70) {
                $lvl_pembeda = 'Baik';
            } else if ($pembeda < 0.20) {
                $lvl_pembeda = 'Jelek';
            } else {
                $lvl_pembeda = 'Cukup';
            }

            /*
             * VALIDITAS
             * r ≥ 0.70: Sangat valid
             * 0.30 ≤ r <0.70: Valid
             * r < 0.30: Tidak valid
             */
            $validitas = round($validity, 2);
            if ($validitas >= 0.70) {
                $lvl_valid = 'Sangat Valid';
            } else if ($validitas < 0.30) {
                $lvl_valid = 'Tidak Valid';
            } else {
                $lvl_valid = 'Valid';
            }

            // Tambahkan analisis distraktor ke hasil
            $distractorAnalysis = [];
            foreach ($distraktor as $choice => $data) {
                $top = count($upperGroup) > 0 ? $data['top'] / count($upperGroup) : 0;
                $bottom = count($lowerGroup) > 0 ? $data['bottom'] / count($lowerGroup) : 0;
                $total = $totalStudents > 0 ? $data['total'] / $totalStudents : 0;

                $distractorAnalysis[$choice] = [
                    'IKD_top' => round($top, 2),
                    'IKD_bottom' => round($bottom, 2),
                    'IKD_total' => round($total, 2),
                ];
            }

            $results[$key] = [
                "soal" => $key,
                "jawaban_benar" => $correctAnswer,
                "tingkat_kesulitan" => $kesulitan,
                "level_kesulitan" => $lvl_sulit,
                "daya_pembeda" => $pembeda,
                "level_pembeda" => $lvl_pembeda,
                "validitas" => $validitas,
                "level_validitas" => $lvl_valid,
                "distraktor" => $distractorAnalysis
            ];
        }
        $alphaCronbach = $this->calculateAlphaCronbach($responseSiswa);
        $kr20 = $this->calculateKR20($responseSiswa);
        $kr20 = round($kr20, 2);
        /*
         * > 0.90	    Sangat tinggi (excellent)
         * 0.80 - 0.89	Tinggi (good)
         * 0.70 - 0.79	Cukup (acceptable)
         * 0.60 - 0.69	Rendah (questionable)
         * 0.50 - 0.59	Sangat rendah (poor)
         * < 0.50	    Tidak dapat diterima (unacceptable)
         */
        if ($kr20 >= 0.90) {
            $ket = 'Sangat tinggi (Excellent)';
        } elseif ($kr20 > 0.79 && $kr20 < 0.90) {
            $ket = 'Tinggi (good)';
        } elseif ($kr20 > 0.69 && $kr20 < 0.80) {
            $ket = 'Cukup (acceptable)';
        } elseif ($kr20 > 0.59 && $kr20 < 0.70) {
            $ket = 'Rendah (questionable)';
        } elseif ($kr20 > 0.49 && $kr20 < 0.60) {
            $ket = 'Sangat rendah (poor)';
        } else {
            $ket = 'Tidak dapat diterima (unacceptable)';
        }
        $results['reliabilitas'] = [
            "alpha_cronbach" => round($alphaCronbach, 2),
            "kr20" => $kr20,
            "ket" => $ket
        ];

        return $results;
    }

    // Fungsi untuk menghitung korelasi biserial
    protected function calculateBiserialCorrelation($responses, $totalScores)
    {
        $sum = array_sum($totalScores);
        $stdDevTotal = 0;
        $p = 0;
        $q = 0;
        $meanCorrect = 0;
        $meanTotal = 0;
        if ($sum > 0) {
            $meanTotal = array_sum($totalScores) / count($totalScores);
            $stdDevTotal = sqrt(array_sum(array_map(function ($score) use ($meanTotal) {
                return pow($score - $meanTotal, 2);
            }, $totalScores)) / count($totalScores));

            $meanCorrect = array_sum(array_filter($totalScores, function ($score, $index) use ($responses) {
                return $responses[$index] == 1;
            }, ARRAY_FILTER_USE_BOTH)) / max(1, array_sum($responses));

            $p = array_sum($responses) / count($responses);
            $q = 1 - $p;
        }

        //return ($meanCorrect - $meanTotal) / $stdDevTotal * sqrt($p * $q);
        return ($stdDevTotal > 0 && $p > 0 && $q > 0)
            ? ($meanCorrect - $meanTotal) / $stdDevTotal * sqrt($p * $q)
            : 0;
    }

    private function calculateAlphaCronbach($responses)
    {
        $numStudents = count($responses);
        $numQuestions = isset($responses[0]) ? count($responses[0]) : 0;

        // Cek apakah data cukup untuk dihitung
        if ($numStudents < 2 || $numQuestions < 2) {
            return 0; // Tidak cukup data
        }

        // Hitung skor total per siswa
        $totalScores = array_map('array_sum', $responses);
        $sum = array_sum($totalScores);
        $meanTotal = $sum / $numStudents;

        // Hitung varian total skor
        $varTotal = array_sum(array_map(function ($score) use ($meanTotal) {
            return pow($score - $meanTotal, 2);
        }, $totalScores)) / $numStudents;

        if ($varTotal == 0) {
            return 0; // Variasi total tidak ada, alpha tidak bisa dihitung
        }

        // Hitung varian per soal
        $itemVariances = [];
        for ($i = 0; $i < $numQuestions; $i++) {
            $itemScores = array_column($responses, $i);
            $meanItem = array_sum($itemScores) / $numStudents;

            $itemVariances[$i] = array_sum(array_map(function ($score) use ($meanItem) {
                return pow($score - $meanItem, 2);
            }, $itemScores)) / $numStudents;
        }

        $sumItemVariance = array_sum($itemVariances);

        if ($sumItemVariance == 0 || $numQuestions <= 1) {
            return 0; // Tidak cukup variasi soal atau soal terlalu sedikit
        }

        // Hitung Alpha Cronbach
        $alpha = ($numQuestions / ($numQuestions - 1)) * (1 - ($sumItemVariance / $varTotal));
        return $alpha;
    }

    private function calculateKR20($responses)
    {
        $numStudents = count($responses);
        $numQuestions = isset($responses[0]) ? count($responses[0]) : 0;

        // Validasi awal: minimal 2 siswa dan 2 soal
        if ($numStudents < 2 || $numQuestions < 2) {
            return 0;
        }

        // Hitung skor total tiap siswa
        $totalScores = array_map('array_sum', $responses);
        $sum = array_sum($totalScores);
        $meanTotal = $sum / $numStudents;

        // Hitung varian total skor
        $varTotal = array_sum(array_map(function ($score) use ($meanTotal) {
            return pow($score - $meanTotal, 2);
        }, $totalScores)) / $numStudents;

        if ($varTotal == 0) {
            return 0; // Variasi total tidak ada, KR-20 tidak dapat dihitung
        }

        // Hitung jumlah pq tiap soal
        $pqSum = 0;
        for ($i = 0; $i < $numQuestions; $i++) {
            $itemScores = array_column($responses, $i);
            $p = array_sum($itemScores) / $numStudents;
            $q = 1 - $p;
            $pqSum += $p * $q;
        }

        if ($pqSum == 0) {
            return 0; // Tidak ada variasi antar item
        }

        // Hitung KR-20
        $kr20 = ($numQuestions / ($numQuestions - 1)) * (1 - ($pqSum / $varTotal));
        return $kr20;
    }

    public function get_web_info()
    {
        // Mengatur header CORS untuk mengizinkan permintaan lintas-origin
        // Di produksi, ganti '*' dengan domain frontend Anda yang spesifik (misal: 'http://localhost:8080')
        header('Access-Control-Allow-Origin: ' . base_url());
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
        header('Content-Type: application/json');

        // Tangani permintaan OPTIONS (preflight request untuk CORS)
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            //http_response_code(200);
            //exit();
            $data['success'] = false;
            $data['message'] = 'http_response_code(200)';
            $this->output_json($data);
        }

        // Ambil URL dari POST request
        // CodeIgniter secara otomatis mem-parsing JSON body jika Content-Type adalah application/json
        $url = $this->input->post('url_address');

        if (empty($url)) {
            //echo json_encode(['error' => 'URL tidak disediakan.']);
            //$this->output->set_status_header(400); // Mengatur status HTTP
            //exit();

            $data['success'] = false;
            $data['message'] = 'URL tidak disediakan.';
            $this->output_json($data);
        }

        // Validasi URL dasar
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            //echo json_encode(['error' => 'URL tidak valid.']);
            //$this->output->set_status_header(400);
            //exit();
            $data['success'] = false;
            $data['message'] = 'URL tidak valid.';
            $this->output_json($data);
        }

        $extracted_info = [
            'title' => 'Tidak ada judul',
            'description' => 'Tidak ada deskripsi yang tersedia.',
            'image' => '',
            'url' => $url,
            'siteName' => parse_url($url, PHP_URL_HOST)
        ];

        try {
            // Menggunakan cURL untuk mengambil konten URL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Accept-Language: en-US,en;q=0.9,id;q=0.8', // Tambahkan bahasa Indonesia
                'Referer: ' . $url, // Mengatur referer ke URL yang sama
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'DNT: 1' // Do Not Track
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Hati-hati: Nonaktifkan verifikasi SSL untuk pengembangan. Aktifkan di produksi.
            curl_setopt($ch, CURLOPT_ENCODING, "");
            $html = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception('cURL Error: ' . curl_error($ch));
            }
            curl_close($ch);

            if ($html == false || empty($html)) {
                throw new Exception('Gagal mengambil konten dari URL.');
            }

            // Menggunakan DOMDocument untuk mem-parsing HTML
            $dom = new DOMDocument();
            @$dom->loadHTML($html); // Menggunakan @ untuk menekan peringatan HTML yang tidak valid
            $xpath = new DOMXPath($dom);

            // Ekstrak Open Graph (OG) tags
            $og_title = $xpath->query('//meta[@property="og:title"]/@content')->item(0);
            $og_description = $xpath->query('//meta[@property="og:description"]/@content')->item(0);
            $og_image = $xpath->query('//meta[@property="og:image"]/@content')->item(0);
            $og_site_name = $xpath->query('//meta[@property="og:site_name"]/@content')->item(0);
            $og_url = $xpath->query('//meta[@property="og:url"]/@content')->item(0);

            if ($og_title)
                $extracted_info['title'] = $og_title->nodeValue;
            if ($og_description)
                $extracted_info['description'] = $og_description->nodeValue;
            if ($og_image)
                $extracted_info['image'] = $og_image->nodeValue;
            if ($og_site_name)
                $extracted_info['siteName'] = $og_site_name->nodeValue;
            if ($og_url)
                $extracted_info['url'] = $og_url->nodeValue;

            // Fallback jika OG tags tidak ada
            if ($extracted_info['title'] == 'Tidak ada judul') {
                $title_tag = $xpath->query('//title')->item(0);
                if ($title_tag)
                    $extracted_info['title'] = $title_tag->nodeValue;
            }
            if ($extracted_info['description'] == 'Tidak ada deskripsi yang tersedia.') {
                $meta_description = $xpath->query('//meta[@name="description"]/@content')->item(0);
                if ($meta_description)
                    $extracted_info['description'] = $meta_description->nodeValue;
            }
            if (empty($extracted_info['image'])) {
                $link_image_src = $xpath->query('//link[@rel="image_src"]/@href')->item(0);
                if ($link_image_src) {
                    $extracted_info['image'] = $link_image_src->nodeValue;
                } else {
                    // Coba ambil gambar pertama dari body
                    $first_img = $xpath->query('//img')->item(0);
                    if ($first_img)
                        $extracted_info['image'] = $first_img->getAttribute('src');
                }
            }

            // Pastikan URL gambar adalah absolut
            if (!empty($extracted_info['image']) && !preg_match('/^https?:\/\//i', $extracted_info['image'])) {
                $base_url_parts = parse_url($url);
                $scheme = isset($base_url_parts['scheme']) ? $base_url_parts['scheme'] . '://' : '';
                $host = isset($base_url_parts['host']) ? $base_url_parts['host'] : '';
                $path = isset($base_url_parts['path']) ? dirname($base_url_parts['path']) : '';

                $base_for_relative = rtrim($scheme . $host . $path, '/') . '/';
                $extracted_info['image'] = $base_for_relative . ltrim($extracted_info['image'], '/');
            }

        } catch (Exception $e) {
            //echo json_encode(['error' => 'Gagal mengambil atau mem-parsing informasi dari URL: ' . $e->getMessage()]);
            //$this->output->set_status_header(500);
            //exit();
            $data['success'] = false;
            $data['message'] = 'Gagal mengambil atau mem-parsing informasi dari URL: ' . $e->getMessage();
            $this->output_json($data);
        }

        // Mengirimkan informasi yang diekstrak sebagai respons JSON
        //echo json_encode($extracted_info);
        //exit(); // Penting untuk menghentikan eksekusi setelah mengirim JSON

        $data['success'] = true;
        $data['info'] = $extracted_info;
        $this->output_json($data);

    }
}
