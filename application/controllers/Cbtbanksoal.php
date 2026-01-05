<?php


class Cbtbanksoal extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru') && !$this->auth->in_group('panitia') && $this->session->userdata('role') != '4') {
            show_error('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library('upload');
        //$this->load->library('MY_Upload', 'my_upload');
        $this->load->library(['datatables', 'form_validation']);// Load Library Ignited-Datatables
        $this->form_validation->set_error_delimiters('', '');
        //$this->load->helper('my');// Load Library Ignited-Datatables
    }

    private function getKelasByIds($ids)
    {
        $this->db->select('id_kelas, nama_kelas, kode_kelas');
        $this->db->where_in('id_kelas', $ids);
        return $this->db->get('master_kelas')->result();
    }

    private function getBankTerpakai($id_banks = null)
    {
        $this->db->select('id_bank,id_siswa');
        $this->db->from('cbt_siswa');
        if ($id_banks != null)
            $this->db->where_in('id_bank', $id_banks);
        $result = $this->db->get()->result();
        $ret = [];
        foreach ($result as $key => $row) {
            $ret[$row->id_bank][$row->id_siswa] = $row;
        }
        return $ret;
    }

    public function index()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Bank Soal',
            'subjudul' => 'Soal',
            'setting' => $setting
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['levels'] = $this->dropdown->getAllLevel($setting->jenjang);

        $mode = $this->input->get('mode');
        $type = $this->input->get('type');
        $data['mode'] = $mode == null ? '1' : $mode;
        //$data['type'] = $type == null ? '0' : $mode;

        if ($this->auth->is_admin() || $this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['mapels'] = $this->dropdown->getAllMapel();
            $data['gurus'] = $this->dropdown->getAllGuru();
            $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);

            $data['filters'] = ['0' => 'Semua', '1' => 'Guru', '2' => 'Mapel', '3' => 'Level'];
            $data['id_filter'] = $type == null ? '' : $type;
            $banks = [];
            if ($type == '0') {
                $banks = $this->cbt->getDataBank();
                $data['id_guru'] = null;
                $data['id_mapel'] = null;
                $data['id_level'] = null;
            } elseif ($type == '1') {
                $id_guru = $this->input->get('id');
                $data['id_guru'] = $id_guru;
                $banks = $this->cbt->getDataBank($id_guru);

                $data['id_mapel'] = '';
                $data['id_level'] = '';
            } elseif ($type == '2') {
                $id_mapel = $this->input->get('id');
                $data['id_mapel'] = $id_mapel;
                $banks = $this->cbt->getDataBank(null, $id_mapel);

                $data['id_guru'] = '';
                $data['id_level'] = '';
            } elseif ($type == '3') {
                $id_level = $this->input->get('id');
                $data['id_level'] = $id_level;
                $banks = $this->cbt->getDataBank(null, null, $id_level);

                $data['id_guru'] = '';
                $data['id_mapel'] = '';
            } else {
                //$data['banks'] = $this->cbt->getDataBank();
                $data['id_guru'] = null;
                $data['id_mapel'] = null;
                $data['id_level'] = null;
            }

            if ($type != null) {
                $jadwal_terpakai = [];
                if ($banks && count($banks) > 0) {
                    $ids = [];
                    foreach ($banks as $bank) {
                        foreach ($bank as $tp_loop) {
                            foreach ($tp_loop as $smt_loop) {
                                $ids[] = $smt_loop->id_bank;
                                $smt_loop->bank_kelas = $this->maybe_unserialize($smt_loop->bank_kelas ?? '[]');
                            }
                        }
                    }
                    if ($ids && count($ids) > 0) {
                        $terpakai = $this->getBankTerpakai($ids);
                        foreach ($terpakai as $idj => $rows) {
                            if ($rows)
                                $jadwal_terpakai[$idj] = count($rows);
                        }
                    }
                }
                $data['banks'] = $banks;
                $data['total_siswa'] = $jadwal_terpakai;
            }

            if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
                $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                $this->render_view(4, 'cbt/banksoal/data', $data);
            } else {
                $this->render_view(1, 'cbt/banksoal/data', $data);
            }
        } else {
            $this->load->model('Kelas_model', 'kelas');
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['guru'] = $guru;
            $data['gurus'] = $nguru;
            $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);

            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                }
            }
            $data['mapels'] = $arrMapel;

            $data['filters'] = ['0' => 'Semua', '2' => 'Mapel', '3' => 'Level'];
            $data['id_filter'] = $type == null ? '' : $type;
            if ($type == '2') {
                $id_mapel = $this->input->get('id');
                $data['id_mapel'] = $id_mapel;
                $banks = $this->cbt->getDataBank($guru->id_guru, $id_mapel);

                $data['id_guru'] = '';
                $data['id_level'] = '';
            } elseif ($type == '3') {
                $id_level = $this->input->get('id');
                $data['id_level'] = $id_level;
                $banks = $this->cbt->getDataBank($guru->id_guru, null, $id_level);

                $data['id_guru'] = '';
                $data['id_mapel'] = '';
            } else {
                $data['id_guru'] = $guru->id_guru;
                $banks = $this->cbt->getDataBank($guru->id_guru);
                $data['id_mapel'] = '';
                $data['id_level'] = '';
            }

            if ($type != null) {
                $jadwal_terpakai = [];
                if ($banks && count($banks) > 0) {
                    $ids = [];
                    foreach ($banks as $bank) {
                        foreach ($bank as $tp) {
                            foreach ($tp as $smt) {
                                $ids[] = $smt->id_bank;
                                $smt->bank_kelas = $this->maybe_unserialize($smt->bank_kelas ?? '[]');
                            }
                        }
                    }
                    if ($ids && count($ids) > 0) {
                        $terpakai = $this->getBankTerpakai($ids);
                        foreach ($terpakai as $idj => $rows) {
                            if ($rows)
                                $jadwal_terpakai[$idj] = count($rows);
                        }
                    }
                }
                $data['banks'] = $banks;
                $data['total_siswa'] = $jadwal_terpakai;
            }

            $this->render_view(2, 'cbt/banksoal/data', $data);
        }
    }

    public function data($guru = null)
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->getDataBank($guru), false);
    }

    public function dataTable($guru = null)
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->getDataTableBank($guru), false);
    }

    public function getMapelGuru()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Kelas_model', 'kelas');

        $id_guru = $this->input->get('id_guru', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $mapel_guru = $this->kelas->getGuruMapelKelas($id_guru, $tp->id_tp, $smt->id_smt);
        $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

        $arrMapel = [];
        if ($mapel != null) {
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
            }
        }

        $this->output_json($arrMapel);
    }

    public function getGuruMapel()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Kelas_model', 'kelas');

        $id_mapel = $this->input->get('id_mapel', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $mapel_guru = $this->kelas->getMapelGuruKelas($tp->id_tp, $smt->id_smt);
        $arrGuru = [];
        foreach ($mapel_guru as $guru) {
            $mapel = $this->maybe_unserialize($guru->mapel_kelas ?? '[]');
            if ($mapel != null) {
                foreach ($mapel as $m) {
                    if (isset($m->id_mapel) && $m->id_mapel == $id_mapel) {
                        $arrGuru[$guru->id_guru] = $guru->nama_guru;
                    }
                }
            }
        }
        $this->output_json($arrGuru);
    }

    public function getKelasLevel()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $level = $this->input->get('level', true);
        $id_guru = $this->input->get('id_guru', true);
        $id_mapel = $this->input->get('mapel', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $mapel_guru = $this->kelas->getGuruMapelKelas($id_guru, $tp->id_tp, $smt->id_smt);
        $arrKelas = [];
        $arrMapel = [];
        $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');
        if ($mapel !== false) {
            foreach ($mapel as $m) {
                $arrMapel[$m->id_mapel] = $m->nama_mapel;
                if ($id_mapel == $m->id_mapel) {
                    foreach ($m->kelas_mapel as $kls) {
                        array_push($arrKelas, $kls->kelas);
                    }
                }
            }
        }

        $this->output_json([
            'mapel' => $arrMapel,
            'kelas' => count($arrKelas) > 0 ? $this->cbt->getKelasByLevel($level, $arrKelas) : []
        ]);
    }

    public function addBank()
    {
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            show_error('Akses Terlarang: Panitia tidak diizinkan membuat Bank Soal', 403, 'Akses Terlarang');
            return;
        }

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Bank Soal',
            'subjudul' => 'Buat Bank Soal',
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['setting'] = $this->dashboard->getSetting();
        $data['bank'] = json_decode(json_encode($this->cbt->dummy($setting->jenjang)));
        $data['jenis'] = $this->cbt->getAllJenisUjian();
        $data['jurusan'] = $this->cbt->getAllJurusan();
        $data['level'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['mapel_agama'] = $this->master->getAgamaSiswa();
        $data['is_admin'] = $this->auth->is_admin();

        if ($this->auth->is_admin()) {
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $data['id_guru'] = '';
            $data['gurus'] = $this->dropdown->getAllGuru();
            $data['mapel'] = $this->dropdown->getAllMapel();
            $this->render_view(1, 'cbt/banksoal/add', $data);
        } else {
            //$guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['gurus'] = $nguru;
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;

            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            $arrKelas = [];
            if ($mapel !== false) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                    foreach ($m->kelas_mapel as $kls) {
                        $arrKelas[$m->id_mapel][] = [
                            'id_kelas' => $kls->kelas,
                            'nama_kelas' => $this->dropdown->getNamaKelasById($tp->id_tp, $smt->id_smt, $kls->kelas)
                        ];
                    }
                }
            }

            $arrId = [];
            if ($mapel && count($mapel) > 0) {
                foreach ($mapel[0]->kelas_mapel as $id_mapel) {
                    $arrId[] = $id_mapel->kelas;
                }
            }

            $data['mapel_guru'] = $mapel_guru;
            $data['mapel'] = $arrMapel;
            $data['arrkelas'] = $arrKelas;
            $data['kelas'] = count($arrId) > 0 ? $this->dropdown->getAllKelasByArrayId($tp->id_tp, $smt->id_smt, $arrId) : [];

            $this->render_view(2, 'cbt/banksoal/add', $data);
        }
    }

    public function editBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_model', 'cbt');

        $id_bank = $this->input->get('id_bank', true);
        $id_guru = $this->input->get('id_guru', true);
        $setting = $this->dashboard->getSetting();

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Edit Bank Soal',
            'subjudul' => 'Edit Bank Soal'
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['bulan'] = $this->dropdown->getBulan();
        $data['is_admin'] = $this->auth->is_admin();

        $data['setting'] = $this->dashboard->getSetting();
        $data['jenis'] = $this->cbt->getAllJenisUjian();
        $data['jurusan'] = $this->cbt->getAllJurusan();
        $data['level'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

        $bank = $this->cbt->getDataBankById($id_bank);
        $bank->bank_kelas = $this->maybe_unserialize($bank->bank_kelas ?? '[]');
        $data['bank'] = $bank;
        $data['mapel_agama'] = $this->master->getAgamaSiswa();

        if ($this->auth->is_admin()) {
            $data['id_guru'] = $id_guru;
            $data['gurus'] = $this->dropdown->getAllGuru();
            $data['mapel'] = $this->dropdown->getAllMapel();

            //$arrMapel = [];
            $mapel_guru = $this->kelas->getGuruMapelKelas($id_guru, $tp->id_tp, $smt->id_smt);
            $data['mapel_guru'] = $mapel_guru;
            $this->render_view(1, 'cbt/banksoal/add', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            // Panitia cannot edit bank details directly or sees limit view
            // For now, redirect or show error since adding is blocked, editing might be too
            show_error('Akses Terlarang: Panitia tidak diizinkan mengedit Bank Soal', 403, 'Akses Terlarang');
            return;
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $mapel_guru = $this->kelas->getGuruMapelKelas($guru->id_guru, $tp->id_tp, $smt->id_smt);
            $mapel = $this->maybe_unserialize($mapel_guru->mapel_kelas ?? '[]');

            $arrMapel = [];
            if ($mapel !== false) {
                foreach ($mapel as $m) {
                    $arrMapel[$m->id_mapel] = $m->nama_mapel;
                }
            }

            $data['gurus'] = $nguru;
            $data['mapel_guru'] = $mapel_guru;
            $data['guru'] = $guru;
            $data['id_guru'] = $guru->id_guru;
            $data['mapel'] = $arrMapel;

            $this->render_view(2, 'cbt/banksoal/add', $data);
        }
    }

    public function saveBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');
        $this->load->model('Cbt_model', 'cbt');

        if ($this->input->post()) {
            $tp = $this->master->getTahunActive();
            $smt = $this->master->getSemesterActive();
            $old_kode = $this->input->post('old_kode', true);
            $bank_kode = $this->input->post('kode', true);
            if ($old_kode && $old_kode != $bank_kode) {
                $this->form_validation->set_rules('kode', 'Kode Bank Soal', 'required|trim|is_unique[cbt_bank_soal.bank_kode]');
                if (!$this->form_validation->run()) {
                    $data = [
                        'status' => false,
                        'errors' => form_error('kode')
                    ];
                    $this->output_json($data);
                    return;
                }
            }

            $this->cbt->saveBankSoal($tp->id_tp, $smt->id_smt);
            $status = TRUE;
        } else {
            $status = FALSE;
        }
        $data['status'] = $status;

        $id = $this->input->post('id_bank', true);
        if (!$id) {
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'menambah bank soal');
        } else {
            $this->logging->saveLog($this->LOG_TYPE->EDIT, 'mengedit bank soal');
        }

        $this->output_json($data);
    }

    public function deleteBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');
        $this->load->model('Cbt_model', 'cbt');

        $id = $this->input->get('id_bank', true);
        if ($this->cbt->cekJadwalBankSoal($id) > 0) {
            $this->output_json(['status' => false, 'message' => 'Ada jadwal ujian yang menggunakan bank soal ini']);
        } else {
            if ($this->master->delete('cbt_soal', $id, 'bank_id')) {
                if ($this->master->delete('cbt_bank_soal', $id, 'id_bank')) {
                    $this->logging->saveLog($this->LOG_TYPE->DELETE, 'menghapus bank soal');
                    $this->output_json(['status' => true, 'message' => 'berhasil']);
                }
            }
        }
    }

    public function deleteAllBank()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');
        $this->load->model('Cbt_model', 'cbt');

        $ids = json_decode($this->input->post('ids', true));
        if ($this->cbt->cekJadwalBankSoal($ids) > 0) {
            $this->output_json(['status' => false, 'message' => 'Ada jadwal ujian yang menggunakan bank soal ini']);
        } else {
            if ($this->master->delete('cbt_soal', $ids, 'bank_id')) {
                if ($this->master->delete('cbt_bank_soal', $ids, 'id_bank')) {
                    $this->logging->saveLog($this->LOG_TYPE->DELETE, 'menghapus semua bank soal');
                    $this->output_json(['status' => true, 'message' => 'berhasil']);
                }
            }
        }
    }

    public function get_detail()
    {
        $id = $this->input->post('id_bank', true);
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');
        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $bank = $this->cbt->getDataBankById($id);
        $bank->bank_kelas = $this->maybe_unserialize($bank->bank_kelas ?? '[]');
        $data['bank'] = $bank;
        $soals = $this->cbt->getAllSoalByBank($id);
        foreach ($soals as $key => $soal) {
            if ($soal->jenis == '2') {
                $soal->opsi_a = $this->maybe_unserialize($soal->opsi_a);
                $soal->jawaban = $this->maybe_unserialize($soal->jawaban);
            } elseif ($soal->jenis == '3') {
                $soal->jawaban = $this->maybe_unserialize($soal->jawaban);
            }
        }
        $data['soals'] = $soals;
        $data['kelas'] = $this->cbt->getKelas($tp->id_tp, $smt->id_smt);
        $terpakai = $this->getBankTerpakai([$id]);
        $data['total_siswa'] = isset($terpakai[$id]) ? count($terpakai[$id]) : 0;
        $data['success'] = true;

        $this->output_json($data);
    }

    public function detail($id)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Detail Soal',
            'subjudul' => 'Detail Soal'
        ];
        $data['setting'] = $this->dashboard->getSetting();

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['id_bank'] = $id;

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/banksoal/detail', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/banksoal/detail', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/banksoal/detail', $data);
        }
    }

    public function get_soals($id)
    {
        $this->load->model('Cbt_model', 'cbt');
        $data = $this->cbt->getAllSoalByBank($id);
        $this->output_json($data);
    }

    public function saveSelected()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Log_model', 'logging');

        $bank_id = $this->input->post('id_bank', true);
        $jenis = $this->input->post('jenis', true);
        $jml = $this->input->post('soal', true);
        $soal = $jml != null ? count($jml) : 0;
        $unchek = json_decode($this->input->post('uncheck', true));
        $arrId = [];
        for ($i = 0; $i <= $soal; $i++) {
            $id = $this->input->post('soal[' . $i . ']', true);
            if ($id != null)
                array_push($arrId, $id);
        }

        $updated = 0;
        foreach ($arrId as $id) {
            $this->db->set('tampilkan', 1);
            $this->db->where('id_soal', $id);
            $this->db->update('cbt_soal');
            $updated++;
        }

        foreach ($unchek as $id) {
            $this->db->set('tampilkan', 0);
            $this->db->where('id_soal', $id);
            $this->db->update('cbt_soal');
        }

        sleep(1);
        $bank = $this->cbt->getDataBankById($bank_id);
        $soals = $this->cbt->getAllSoalByBank($bank_id);

        $total_soal_tampil = isset(array_count_values(array_column($soals, 'tampilkan'))['1']) ? array_count_values(array_column($soals, 'tampilkan'))['1'] : 0;
        $total_soal_seharusnya_tampil = $bank->tampil_pg + $bank->tampil_kompleks + $bank->tampil_jodohkan + $bank->tampil_isian + $bank->tampil_esai;
        $tampil_kurang = $total_soal_tampil < $total_soal_seharusnya_tampil;
        $status_soal = $tampil_kurang ? '0' : '1';

        $this->db->set('status_soal', $status_soal);
        $this->db->where('id_bank', $bank_id);
        $this->db->update('cbt_bank_soal');

        $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Menyimpan soal terpilih dari bank soal');
        $data['check'] = $updated;
        $this->output_json($data);
    }

    public function copyBankSoal($id_bank)
    {
        if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $this->output_json(['status' => false, 'msg' => 'Panitia tidak diizinkan menyalin Bank Soal']);
            return;
        }
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        // Use where()->get() to avoid potential get_where argument type strictness in custom overrides
        $bank = $this->db->where('id_bank', $id_bank)->get('cbt_bank_soal')->row();
        $soals = $this->cbt->getAllSoalByBank($id_bank);

        $arrKls = $this->maybe_unserialize($bank->bank_kelas);
        $kelasLalu = $this->getKelasByIds(array_column($arrKls, 'kelas_id'));
        $map_lalu = [];
        foreach ($kelasLalu as $item) {
            $kode = $this->normalize($item->kode_kelas);
            $map_lalu[] = $kode;
        }
        $kelas_now = $this->dropdown->getAllKodeKelas($tp->id_tp, $smt->id_smt);
        $kelasSkrg = [];
        foreach ($kelas_now as $ids => $kode_now) {
            $kode_n = $this->normalize($kode_now); // 10a
            if (in_array($kode_n, $map_lalu)) {
                $kelasSkrg[] = ['kelas_id' => $ids];
            }
        }

        $unik = $this->generateUniqueId();
        $bank->id_tp = $tp->id_tp;
        $bank->id_smt = $smt->id_smt;
        $bank->bank_kode = $bank->bank_kode . '_' . $unik;
        $bank->bank_kelas = json_encode($kelasSkrg);
        $bank->date = date('Y-m-d H:i:s');
        unset($bank->id_bank);

        $result = $this->db->insert('cbt_bank_soal', $bank);
        $id = $this->db->insert_id();

        if ($soals && count($soals) > 0) {
            foreach ($soals as $soal) {
                unset($soal->id_soal);
                $soal->bank_id = $id;
                $soal->created_on = time();
                $soal->updated_on = time();
            }
            $this->db->insert_batch('cbt_soal', $soals);
        }

        $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menyalin bank soal');
        $this->output_json($result);
    }

    private function normalize($str)
    {
        return strtolower(preg_replace('/[\s.\-_]/', '', $str ?? ''));
    }

    public function buatsoal($id_bank)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $_no = $this->input->get('no', true);
        $_jns = $this->input->get('jns', true);

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Buat Soal',
            'subjudul' => 'Buat Soal'
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $setting = $this->dashboard->getSetting();
        $data['setting'] = $setting;

        $data['p_no'] = $_no != null ? $_no : '1';
        $act_tab = $_jns != null ? $_jns : '1';
        $data['p_jns'] = $act_tab;

        $tab = $this->input->get('tab', true);
        $jenis = $tab == null ? $act_tab : $tab;
        $data['tab_active'] = $jenis;
        $bank = $this->cbt->getDataBankById($id_bank);

        $data['soal'] = null;
        $data['soal_ada'] = $this->cbt->cekSoalAda($id_bank, $jenis);
        $data_komplit = $this->cbt->cekSoalBelumKomplit($jenis, $bank->opsi);
        $data['soal_belum_komplit'] = isset($data_komplit[$id_bank]) ? $data_komplit[$id_bank] : [];

        if ($jenis == "1")
            $data['jml_pg'] = $this->cbt->getNomorSoalTerbesar($id_bank, 1);
        elseif ($jenis == "2")
            $data['jml_pg2'] = $this->cbt->getNomorSoalTerbesar($id_bank, 2);
        elseif ($jenis == "3")
            $data['jml_jodohkan'] = $this->cbt->getNomorSoalTerbesar($id_bank, 3);
        elseif ($jenis == "4")
            $data['jml_isian'] = $this->cbt->getNomorSoalTerbesar($id_bank, 4);
        elseif ($jenis == "5")
            $data['jml_essai'] = $this->cbt->getNomorSoalTerbesar($id_bank, 5);
        elseif ($jenis == "6")
            $data['jml_urutan'] = $this->cbt->getNomorSoalTerbesar($id_bank, 6);
        elseif ($jenis == "7")
            $data['jml_cloze'] = $this->cbt->getNomorSoalTerbesar($id_bank, 7);
        $data['bank'] = $bank;

        $data['soals'] = $this->cbt->getAllSoalByBank($id_bank, $jenis);
        $data['jurusan'] = $this->cbt->getAllJurusan();
        $data['level'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/banksoal/soal', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/banksoal/soal', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/banksoal/soal', $data);
        }
    }

    public function getSoalByNomor()
    {
        $this->load->model('Cbt_model', 'cbt');

        $bank_id = $this->input->get('bank_id', true);
        $nomor = $this->input->get('nomor', true);
        $jenis = $this->input->get('jenis', true);
        $soal = $this->cbt->getSoalByNomor($bank_id, $nomor, $jenis);
        $data = $soal;
        if ($data != null) {
            $data->file = $this->maybe_unserialize($soal->file ?? '');
            if ($jenis == '2') {
                $t = $this->maybe_unserialize($soal->opsi_a ?? '');
                // If unserialize fails (returns string or false), try explicit JSON decode as fallback if it looks like JSON
                if (is_string($t) && (strpos($t, '{') === 0 || strpos($t, '[') === 0)) {
                    $json = json_decode($t);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $t = $json;
                    }
                }

                if ($t !== false) {
                    $data->opsi_a = $t;
                    // For type 2, frontend often expects 'opsi' property, map it:
                    $data->opsi = $t;
                } else {
                    $data->opsi_a = false;
                }

                $j = $this->maybe_unserialize($soal->jawaban ?? '');
                // Fallback for JSON answers
                if (is_string($j) && (strpos($j, '[') === 0)) {
                    $jsonJ = json_decode($j);
                    if (json_last_error() === JSON_ERROR_NONE)
                        $j = $jsonJ;
                }

                if ($j !== false)
                    $data->jawaban = $j;
                else
                    $data->jawaban = false;
            } elseif ($jenis == '3') {
                $j = $this->maybe_unserialize($soal->jawaban ?? '');
                if ($j !== false)
                    $data->jawaban = $j;
                else
                    ($data->jawaban = false);
            } elseif ($jenis == '6' || $jenis == '7') {
                // Type 6: Urutan, Type 7: Cloze - data stored as JSON
                $opsi = $soal->opsi_a ?? '';
                if (is_string($opsi) && (strpos($opsi, '{') === 0 || strpos($opsi, '[') === 0)) {
                    $data->opsi_a = json_decode($opsi);
                    $data->soal_opsi = $data->opsi_a;
                }

                $j = $soal->jawaban ?? '';
                if (is_string($j) && (strpos($j, '{') === 0 || strpos($j, '[') === 0)) {
                    $data->jawaban = json_decode($j);
                }
            }
        } else {
            if ($nomor != 1) {
                $data = [
                    'bank_id' => $bank_id,
                    'jenis' => $jenis,
                    'nomor_soal' => $nomor,
                ];
            }
        }
        $this->output_json($data);
    }

    public function tambahSoal()
    {
        $this->load->model('Log_model', 'logging');
        $bank = $this->input->post('bank', true);
        $nomor = $this->input->post('nomor', true);
        $jenis = $this->input->post('jenis', true);

        $data = [
            'bank_id' => $bank,
            'nomor_soal' => $nomor,
            'jenis' => $jenis,
            'tampilkan' => 0,
            'created_on' => time(),
            'updated_on' => time()
        ];

        $insert = $this->db->insert('cbt_soal', $data);
        if ($insert) {
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'menambah soal nomor:' . $nomor . ', jenis:' . $jenis);
        }
        $this->output_json($insert);
    }

    public function importsoal($id)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Import Bank Soal',
            'subjudul' => 'Import Bank Soal'
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['setting'] = $setting;
        $data['bank'] = $this->cbt->getDataBankById($id);
        $data['jenis'] = $this->cbt->getAllJenisUjian();
        $data['jurusan'] = $this->cbt->getAllJurusan();
        $data['level'] = $this->dropdown->getAllLevel($setting->jenjang);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/banksoal/import', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/banksoal/import', $data);
        }
    }

    public function getSoalSiswa($id_bank)
    {
        $this->load->model('Cbt_model', 'cbt');
        $soals = $this->cbt->getAllSoalByBank($id_bank);
        foreach ($soals as $soal) {
            if (isset($soal->file))
                $soal->file = $this->maybe_unserialize($soal->file ?? '');
            if ($soal->jenis == '2') {
                $soal->jawaban = $this->maybe_unserialize($soal->jawaban ?? '');
                $soal->opsi_a = $this->maybe_unserialize($soal->opsi_a ?? '');
            } elseif ($soal->jenis == '3') {
                $soal->jawaban = $this->maybe_unserialize($soal->jawaban ?? '');
            }
        }
        $data['soal'] = $soals;
        $this->output_json($data);
    }

    function innerXML($node)
    {
        $doc = $node->ownerDocument;
        $frag = $doc->createDocumentFragment();
        foreach ($node->childNodes as $child) {
            $frag->appendChild($child->cloneNode(TRUE));
        }
        return $doc->saveXML($frag);
    }

    public function file_config()
    {
        $allowed_type = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/gif",
            "audio/mpeg",
            "audio/mpg",
            "audio/mpeg3",
            "audio/mp3",
            "audio/x-wav",
            "audio/wave",
            "audio/wav",
            "video/mp4",
            "application/octet-stream"
        ];
        $config['upload_path'] = FCPATH . 'uploads/bank_soal/';
        $config['allowed_types'] = 'jpeg|jpg|png|gif|mpeg|mpg|mpeg3|mp3|wav|wave|mp4';
        $config['encrypt_name'] = TRUE;

        return $this->load->library('upload', $config);
    }

    public function validasi($jenis)
    {
        $this->form_validation->set_rules('soal', 'Soal', 'required');
        if ($jenis == 1) {
            $this->form_validation->set_rules('jawaban_pg', 'Kunci Jawaban', 'required');
        } elseif ($jenis == 2) {
            $this->form_validation->set_rules('jawaban2_a', 'Kunci Jawaban', 'required');
            $this->form_validation->set_rules('jawaban_benar_pg2[]', 'Kunci Jawaban', 'required');
        } elseif ($jenis == 3) {
            $this->form_validation->set_rules('jawaban[][]', 'Kunci Jawaban', 'required');
        } elseif ($jenis == 4) {
            $this->form_validation->set_rules('jawaban_isian', 'Kunci Jawaban', 'required');
        } elseif ($jenis == 6) {
            // Type 6: Urutan - validation handled in JS
            $this->form_validation->set_rules('soal_opsi', 'Item Urutan', 'required');
        } elseif ($jenis == 7) {
            // Type 7: Isian Rumpang - validation handled in JS
            $this->form_validation->set_rules('soal_opsi', 'Kunci Jawaban Cloze', 'required');
        } else {
            $this->form_validation->set_rules('jawaban_essai', 'Kunci Jawaban', 'required');
        }
    }

    public function saveSoal()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $method = $this->input->post('method', true);
        $jenis = $this->input->post('jenis', true);
        $bank_id = $this->input->post('bank_id', true);
        $nomor_soal = $this->input->post('nomor_soal', true);
        $soal = $this->input->post('soal', false);

        $this->validasi($jenis);
        $this->file_config();

        $data = [
            'bank_id' => $bank_id,
            'jenis' => $jenis,
            'nomor_soal' => $nomor_soal,
            'soal' => $this->decode_data($soal, $bank_id, $jenis, $nomor_soal),
        ];

        $test = [];
        if ($jenis == 1) {
            $abjad = ['a', 'b', 'c', 'd', 'e'];
            foreach ($abjad as $abj) {
                $opsi = $this->input->post('jawaban_' . $abj, false);
                $data['opsi_' . $abj] = $this->decode_data($opsi, $bank_id, $jenis, $nomor_soal);
            }
            $data['jawaban'] = $this->input->post('jawaban_pg', true);
        } elseif ($jenis == 2) {
            $opsis = [];
            for ($i = 97; $i < 117; $i++) {
                $op = $this->input->post('jawaban2_' . chr($i), false);
                if ($op != null) {
                    $opsis[chr($i)] = $this->decode_data($op, $bank_id, $jenis, $nomor_soal); //$op;
                }
            }
            $data['opsi_a'] = json_encode($opsis);

            $jawabans = [];
            $jwb_pg2 = count($this->input->post('jawaban_benar_pg2', true));
            for ($i = 0; $i <= $jwb_pg2; $i++) {
                $jwb = $this->input->post('jawaban_benar_pg2[' . $i . ']', true);
                $jawabans[] = $jwb;
            }
            $data['jawaban'] = json_encode($jawabans);
        } elseif ($jenis == 3) {
            $jawabans = $this->input->post('jawaban', false);
            foreach ($jawabans as $key => $values) {
                if (is_array($values)) {
                    foreach ($values as $key2 => $value) {
                        $replaced = str_replace('<!--?xml encoding="UTF-8"--><meta charset="UTF-8">', '', $value ?? '');
                        $jawabans[$key][$key2] = $this->decode_data($replaced, $bank_id, $jenis, $nomor_soal);
                    }
                }
            }
            $jwb_jodohkan = [
                'model' => $this->input->post('model', true),
                'type' => $this->input->post('type', true),
                'jawaban' => $jawabans
            ];
            $data['jawaban'] = json_encode($jwb_jodohkan);
        } elseif ($jenis == 4) {
            $jwb4 = $this->input->post('jawaban_isian', true);
            $data['jawaban'] = $this->decode_data($jwb4, $bank_id, $jenis, $nomor_soal);
        } elseif ($jenis == 6) {
            // TYPE 6: URUTAN (ORDERING)
            $soal_opsi = $this->input->post('soal_opsi', true);
            $jawaban = $this->input->post('jawaban', true);
            $data['opsi_a'] = $soal_opsi; // JSON: { items: [...], correct_order: [...] }
            $data['jawaban'] = $jawaban;  // JSON: [0, 1, 2, 3, ...]
        } elseif ($jenis == 7) {
            // TYPE 7: ISIAN RUMPANG (CLOZE)
            $soal_opsi = $this->input->post('soal_opsi', true);
            $jawaban = $this->input->post('jawaban', true);
            $data['opsi_a'] = $soal_opsi; // JSON: { blanks: { "1": {...}, "2": {...} } }
            $data['jawaban'] = $jawaban;  // JSON: { "1": "answer1", "2": "answer2" }
        } else {
            $jwb5 = $this->input->post('jawaban_essai', false);
            $data['jawaban'] = $this->decode_data($jwb5, $bank_id, $jenis, $nomor_soal);
        }

        if ($this->form_validation->run() == FALSE) {
            $result['status'] = 'error';
            $result['error'] = validation_errors();
        } else if ($method == 'add') {
            $data['created_on'] = time();
            $data['updated_on'] = time();
            $this->master->create('cbt_soal', $data);
            $result['status'] = 'Soal berhasil dibuat';
            $this->logging->saveLog(3, 'membuat soal');
        } else if ($method == 'edit') {
            $id_soal = $this->input->post('soal_id', true);
            $data['updated_on'] = time();
            $this->master->update('cbt_soal', $data, 'id_soal', $id_soal);
            $result['status'] = 'Soal berhasil diupdate';
            $this->logging->saveLog(4, 'mengedit soal');
        } else {
            $result['status'] = '400 Method not found';
        }
        $this->output_json($result);
    }

    function base64_to_jpeg($base64_string, $output_file)
    {
        // open the output file for writing
        $ifp = fopen($output_file, 'wb');

        // split the string on commas
        // $data[ 0 ] == "data:image/png;base64"
        // $data[ 1 ] == <actual base64 string>
        $data = explode(',', $base64_string);

        // we could add validation here with ensuring count( $data ) > 1
        fwrite($ifp, base64_decode($data[1]));

        // clean up the file resource
        fclose($ifp);

        return $output_file;
    }

    public function hapusSoal()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Log_model', 'logging');

        $id_soal = $this->input->post('soal_id', true);
        $result = $this->cbt->getNomorSoalById($id_soal);
        $nomor = $result->nomor_soal;

        $this->db->where('id_soal', $id_soal);
        $this->db->delete('cbt_soal');

        $all_soal = $this->cbt->getNomorSoalByBankJenis($result->bank_id, $result->jenis);
        $update = [];
        $nomor_baru = 1;
        foreach ($all_soal as $soal) {
            $update[] = [
                'id_soal' => $soal->id_soal,
                'nomor_soal' => $nomor_baru
            ];
            $nomor_baru++;
        }
        if (count($update) > 0) {
            $this->db->update_batch('cbt_soal', $update, 'id_soal');
            $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Menghapus soal nomor ' . $nomor);
        }
        $this->output_json($update);
    }

    public function resetNumber()
    {
        $ids_soal = explode(',', $this->input->post('soal_id', true));
        $update = [];
        $nomor_baru = 1;
        foreach ($ids_soal as $id) {
            $update[] = [
                'id_soal' => $id,
                'nomor_soal' => $nomor_baru
            ];
            $nomor_baru++;
        }
        if (count($update) > 0)
            $this->db->update_batch('cbt_soal', $update, 'id_soal');
        $this->output_json($update);

    }

    function uploadFile()
    {
        $this->load->model('Cbt_model', 'cbt');
        $id_soal = $this->input->get('id_soal', true);
        $soal = $this->cbt->getFileSoalById($id_soal);
        $files = $soal == null || $soal->file == null ? [] : $this->maybe_unserialize($soal->file ?? '');

        if (isset($_FILES["file_uploads"]["name"])) {
            $nama_file_asal = $_FILES["file_uploads"]["name"];
            $kode_file = $id_soal . '_' . time();

            $config['upload_path'] = './uploads/bank_soal/';
            $config['allowed_types'] = 'mpeg|mpg|mpeg3|mp3|wav|wave|mp4|avi';
            $config['file_name'] = $kode_file;

            //$config['encrypt_name'] = TRUE;
            //$config['max_size'] = '0';
            //$config['overwrite'] = FALSE;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file_uploads')) {
                $data['status'] = false;
                $src = '';
                $filename = '';
                $data['src'] = $this->upload->display_errors();
            } else {
                $file = $this->upload->data();
                /*
                $config['image_library']='gd2';
                $config['source_image']='./uploads/bank_soal/'.$file['file_name'];
                $config['create_thumb']= FALSE;
                $config['maintain_ratio']= TRUE;
                $config['quality']= '60%';
                $config['width']= 800;
                $config['height']= 800;
                $config['new_image']= './uploads/bank_soal/'.$file['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();
                */

                //echo base_url().'uploads/bank_soal/'.$file['file_name'];
                $ext = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                $src = 'uploads/bank_soal/' . $kode_file . '.' . $ext;
                $data['src'] = $src;
                //$filename = pathinfo($file['file_name'], PATHINFO_FILENAME);
                //$data['filename'] = $filename;
                $data['filename'] = $nama_file_asal;
                $data['status'] = true;

                $type = $_FILES['file_uploads']['type'];
                $data['type'] = $type;
                $data['size'] = $_FILES['file_uploads']['size'];
                $data['soal'] = $soal;

                $files[] = [
                    'file_name' => $nama_file_asal,
                    'alias' => $kode_file,
                    'src' => $src,
                    'type' => $type
                ];
                //array_push($files, ['files'=>$addFiles]);

                $this->db->set('file', json_encode($files));
                $this->db->where('id_soal', $id_soal);
                $this->db->update('cbt_soal');
            }
        }

        $data['files'] = $files;
        $this->output_json($data);
    }

    //Upload image summernote
    function upload_image()
    {
        //$data['tag_id'] = $this->input->post('tag_id', true);
        $status = false;
        if (isset($_FILES["file"]["name"])) {
            $config['upload_path'] = './uploads/bank_soal/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|mp3|ogg|wav|mp4|mpeg|webm';
            $config['file_name'] = 'file_' . date('YmdHis');
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $this->upload->display_errors();
                $status = false;
            } else {
                $uploaded = $this->upload->data();
                //Compress Image
                if ($uploaded['is_image'] == 1) {
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = './uploads/bank_soal/' . $uploaded['file_name'];
                    $config['create_thumb'] = FALSE;
                    $config['maintain_ratio'] = TRUE;
                    $config['quality'] = '60%';
                    $config['width'] = 800;
                    $config['height'] = 800;
                    $config['new_image'] = './uploads/bank_soal/' . $uploaded['file_name'];
                    $this->load->library('image_lib', $config);
                    $this->image_lib->resize();
                    $this->image_lib->clear();
                }

                $data['filename'] = 'uploads/bank_soal/' . $uploaded['file_name'];
                $status = true;
            }
        }
        $data['status'] = $status;
        $this->output_json($data);
    }

    function uploadSoalImage()
    {
        $name = $this->input->post('name');
        $src = $this->input->post('src');

        str_replace('%2B', '+', $src ?? '');
        $data['status'] = file_put_contents('./uploads/bank_soal/' . $name, base64_decode($src));

        // Compress Image
        $config['image_library'] = 'gd2';
        $config['source_image'] = './uploads/bank_soal/' . $name;
        $config['create_thumb'] = FALSE;
        $config['maintain_ratio'] = TRUE;
        $config['quality'] = '60%';
        $config['width'] = 800;
        $config['height'] = 800;
        $config['new_image'] = './uploads/bank_soal/' . $name;
        $this->load->library('image_lib', $config);
        $this->image_lib->resize();
        $this->image_lib->clear();

        $data['src'] = 'uploads/bank_soal/' . $name;
        $this->output_json($data);
    }

    //Delete image summernote
    function deleteFile()
    {
        $src = $this->input->post('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        if (unlink($file_name)) {
            echo 'File Delete Successfully';
        }
    }

    function uploadSoal()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Log_model', 'logging');
        $bank_id = $this->input->post('id_bank', true);
        $datas = $this->input->post('soal', false);
        $bank = $this->cbt->getDataBankById($bank_id);

        $jml_spg1 = 0;
        $jml_spg2 = 0;
        $jml_sjod = 0;
        $jml_siss = 0;
        $jml_sess = 0;
        $data_insert = [];
        foreach ($datas as $jenis => $nomor) {
            foreach ($nomor as $no => $v) {
                $isi_soal = isset($v['soal']) ? $this->decode_data(rawurldecode($v['soal']), $bank_id, $jenis, $no) : "";
                if ($isi_soal != "") {
                    $insert = [
                        'jenis' => $jenis,
                        'nomor_soal' => $no,
                        'soal' => $isi_soal,
                        'file' => json_encode([]),
                    ];

                    if ($jenis == 1) {
                        $insert['opsi_a'] = isset($v['opsi']) && isset($v['opsi']['A']) ? $this->decode_data(rawurldecode($v['opsi']['A']), $bank_id, $jenis, $no) : '';
                        $insert['opsi_b'] = isset($v['opsi']) && isset($v['opsi']['B']) ? $this->decode_data(rawurldecode($v['opsi']['B']), $bank_id, $jenis, $no) : '';
                        $insert['opsi_c'] = isset($v['opsi']) && isset($v['opsi']['C']) ? $this->decode_data(rawurldecode($v['opsi']['C']), $bank_id, $jenis, $no) : '';
                        $insert['opsi_d'] = isset($v['opsi']) && isset($v['opsi']['D']) ? $this->decode_data(rawurldecode($v['opsi']['D']), $bank_id, $jenis, $no) : '';
                        $insert['opsi_e'] = isset($v['opsi']) && isset($v['opsi']['E']) ? $this->decode_data(rawurldecode($v['opsi']['E']), $bank_id, $jenis, $no) : '';
                        $insert['jawaban'] = isset($v['kunci']) && count($v['kunci']) > 0 ? $v['kunci'][0] : '';
                        $jml_spg1++;
                    } elseif ($jenis == "2") {
                        $opsis = [];
                        $kuncis = [];
                        if (isset($v['opsi'])) {
                            foreach ($v['opsi'] as $opsi => $jawaban) {
                                $opsis[strtolower($opsi ?? '')] = $this->decode_data(rawurldecode($jawaban), $bank_id, $jenis, $no);
                            }
                        }

                        if (isset($v['kunci'])) {
                            foreach ($v['kunci'] as $jawaban) {
                                $kuncis[] = strtolower($jawaban ?? '');
                            }
                        }
                        $insert['opsi_a'] = json_encode($opsis);
                        $insert['jawaban'] = json_encode($kuncis);
                        $jml_spg2++;
                    } elseif ($jenis == "3") {
                        $baris = [];
                        $kolom = [];

                        $header = [];
                        $header[] = '#';

                        $arrKol = [];
                        if (isset($v['kolom'])) {
                            foreach ($v['kolom'] as $kd_kol => $kol) {
                                $kolom[$kd_kol] = $kol;
                                if ($kol != "")
                                    $header[] = $this->decode_data(rawurldecode($kol), $bank_id, $jenis, $no);

                                foreach ($v['kunci'] as $kd_bar => $kd_kol) {
                                    if ($kd_kol != "")
                                        $arrKol[$kd_bar] = array_map('trim', explode(',', $kd_kol ?? ''));
                                    //explode(',', $kd_kol ?? '');
                                }
                            }
                        }

                        $baris[] = $header;
                        $jwbnBaris = [];
                        if (isset($v['baris'])) {
                            foreach ($v['baris'] as $kd_bar => $bar) {
                                $jwbn = [];
                                if ($kd_bar != "")
                                    $jwbn[] = $this->decode_data(rawurldecode($bar), $bank_id, $jenis, $no);

                                foreach ($kolom as $kk => $val) {
                                    if ($kd_bar != "" && $val != "" && isset($arrKol[$kd_bar])) {
                                        $match = in_array($kk, $arrKol[$kd_bar]);
                                        $jwbn[] = $match ? '1' : '0';
                                    }
                                }
                                if (count($jwbn) > 0)
                                    $baris[] = $jwbn;

                                if ($kd_bar != "") {
                                    array_shift($jwbn);
                                    $jwbnBaris[$kd_bar] = $jwbn;
                                }
                            }
                        }
                        $types = [];
                        foreach ($jwbnBaris as $brs => $jml) {
                            $jmlType = array_count_values($jml);
                            if (isset($jmlType[1]) && $jmlType[1] > 1)
                                $types[] = 'checkbox';
                        }
                        $type = count($types) > 0 ? '1' : '2';

                        $jml_baris = count($baris);
                        $jml_kolom = count($baris[0]);

                        $jwb_jodohkan = [
                            'model' => $jml_baris == $jml_kolom ? '1' : '2',
                            'type' => $type,
                            'jawaban' => $baris
                        ];
                        $insert['jawaban'] = json_encode($jwb_jodohkan);
                        $jml_sjod++;
                    } elseif ($jenis == "4") {
                        if (isset($v['kunci']))
                            $insert['jawaban'] = $v['kunci'];
                        $jml_siss++;
                    } else {
                        if (isset($v['kunci']))
                            $insert['jawaban'] = $this->decode_data(rawurldecode($v['kunci']), $bank_id, $jenis, $no);
                        $jml_sess++;
                    }

                    $data_insert[] = $insert;
                }
            }
        }

        $tmpl['1'] = $jml_spg1 == $bank->tampil_pg ? '1' : '0';
        $tmpl['2'] = $jml_spg2 == $bank->tampil_kompleks ? '1' : '0';
        $tmpl['3'] = $jml_sjod == $bank->tampil_jodohkan ? '1' : '0';
        $tmpl['4'] = $jml_siss == $bank->tampil_isian ? '1' : '0';
        $tmpl['5'] = $jml_sess == $bank->tampil_esai ? '1' : '0';

        $inserted = [];
        //$total_soal = count($data_insert);
        foreach ($data_insert as $dins) {
            $inserted[] = [
                'bank_id' => $bank_id,
                'jenis' => $dins['jenis'],
                'nomor_soal' => $dins['nomor_soal'],
                'soal' => $dins['soal'],
                'deskripsi' => '',
                'kesulitan' => '8',
                'timer' => '0',
                'timer_menit' => '0',
                'file' => $dins['file'],
                'created_on' => time(),
                'updated_on' => time(),
                'opsi_a' => $dins['opsi_a'] ?? '',
                'opsi_b' => $dins['opsi_b'] ?? '',
                'opsi_c' => $dins['opsi_c'] ?? '',
                'opsi_d' => $dins['opsi_d'] ?? '',
                'opsi_e' => $dins['opsi_e'] ?? '',
                'jawaban' => $dins['jawaban'],
                'tampilkan' => $tmpl[$dins['jenis']]
            ];
        }

        //$data['data_insert'] = $inserted;
        $data['total'] = count($inserted);
        if (count($inserted) > 0) {
            $this->db->where('bank_id', $bank_id);
            if ($this->db->delete('cbt_soal')) {
                $data['insert'] = $this->db->insert_batch('cbt_soal', $inserted);
            }
        } else {
            $data['insert'] = 0;
        }

        if (count($inserted) > 0) {
            $sttmpl['1'] = $jml_spg1 >= $bank->tampil_pg ? '1' : '0';
            $sttmpl['2'] = $jml_spg2 >= $bank->tampil_kompleks ? '1' : '0';
            $sttmpl['3'] = $jml_sjod >= $bank->tampil_jodohkan ? '1' : '0';
            $sttmpl['4'] = $jml_siss >= $bank->tampil_isian ? '1' : '0';
            $sttmpl['5'] = $jml_sess >= $bank->tampil_esai ? '1' : '0';

            $status_soal = $sttmpl['1'] == '1' && $sttmpl['2'] == '1' && $sttmpl['3'] == '1' && $sttmpl['4'] == '1' && $sttmpl['5'] == '1' ? '1' : '0';
            $this->db->set('status_soal', $status_soal);
            $this->db->where('id_bank', $bank_id);
            $soal_updated = $this->db->update('cbt_bank_soal');
            $data['selesai'] = $soal_updated;

            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Mengimpor soal');
        }

        $this->output_json($data);
    }

    function isHtml($string)
    {
        $string = ltrim($string);
        // Cek apakah string diawali dengan tag HTML
        return preg_match('/^<\s*\w+.*?>/', $string) == 1;
    }

    function decode_data($html, $id_bank, $jenis, $nomor)
    {
        $search = array(
            '<!DOCTYPE html>',
            '<html>',
            '<body>',
            '<?xml encoding="UTF-8">',
            '<!--?xml encoding="UTF-8"-->',
            '<meta charset="UTF-8">',
            "\r",
            "\n"
        );
        $repla = array('', '', '', '', '', '');

        if ($html == null || !$this->isHtml($html) || empty($html)) {
            return str_replace($search, $repla, $html ?? '');
        }
        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // suppress warning
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $html = '<meta charset="UTF-8">' . $html;
        $dom->loadHTML('<!DOCTYPE html><html><body><?xml encoding="UTF-8">' . $html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

        $images = $dom->getElementsByTagName('img');
        if ($images) {
            $numimg = 1;
            foreach ($images as $image) {
                if ($image instanceof DOMElement) {
                    $src = $image->getAttribute('src');
                    if (substr($src, 0, 5) == "data:") {
                        $base64_image_string = $image->getAttribute('src');
                        $splited = explode(',', substr($base64_image_string, 5), 2);
                        $mime = $splited[0];
                        $data = $splited[1];

                        $mime_split_without_base64 = explode(';', $mime, 2);
                        $mime_split = explode('/', $mime_split_without_base64[0], 2);
                        if (count($mime_split) == 2) {
                            $extension = $mime_split[1];
                            if ($extension == 'jpeg')
                                $extension = 'jpg';

                            try {
                                $bytes = random_bytes(10);
                            } catch (Exception $e) {
                            }
                            $output_file = 'img_' . $id_bank . $jenis . $nomor . '_' . bin2hex($bytes) . '.' . $extension;
                            file_put_contents('./uploads/bank_soal/' . $output_file, base64_decode($data));

                            // Compress Image
                            $config['image_library'] = 'gd2';
                            $config['source_image'] = './uploads/bank_soal/' . $output_file;
                            $config['create_thumb'] = FALSE;
                            $config['maintain_ratio'] = TRUE;
                            $config['quality'] = '60%';
                            $config['width'] = 800;
                            $config['height'] = 800;
                            $config['new_image'] = './uploads/bank_soal/' . $output_file;
                            $this->load->library('image_lib', $config);
                            $this->image_lib->resize();
                            $this->image_lib->clear();

                            $image->setAttribute('src', 'uploads/bank_soal/' . $output_file);
                            $numimg++;
                        }
                    } else {
                        $image->setAttribute('src', str_replace(base_url(), '', $src ?? ''));
                    }
                }
            }
            $res = $dom->saveHTML();
            return str_replace($search, $repla, $res ?? '');
        }
        libxml_clear_errors();
        return str_replace($search, $repla, $html ?? '');
    }

    private function generateUniqueId($length = 4)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        try {
            $bytes = random_bytes($length);
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[ord($bytes[$i]) % $charactersLength];
            }
        } catch (Exception $e) {
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
            }
        }
        return $randomString;
        /*
         * // Contoh penggunaan:
            $id3 = generateUniqueId();
            $id4 = generateUniqueId();
            echo "ID 3 (lebih aman): " . $id3 . "\n";
            echo "ID 4 (lebih aman): " . $id4 . "\n";
         */
    }
    public function cleanup_images()
    {
        $this->load->helper('file');
        $this->load->helper('number'); // For byte_format
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();

        $path = FCPATH . 'uploads/bank_soal/';
        $files = get_filenames($path); // Uses CI file helper

        if ($files === false) {
            $files = [];
        }

        // Get all content from cbt_soal
        $this->db->select('soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban');
        $query = $this->db->get('cbt_soal');

        $used_files = [];
        foreach ($query->result() as $row) {
            $content = $row->soal . $row->opsi_a . $row->opsi_b . $row->opsi_c . $row->opsi_d . $row->opsi_e . $row->jawaban;
            // Match filenames stored in uploads/bank_soal/
            // Matches: any character that is alphanumeric, underscore, dot, hyphen, parentheses, percent (url encoded)
            preg_match_all('/uploads\/bank_soal\/([a-zA-Z0-9_.\\-\\(\)\%]+)/', $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $file) {
                    $used_files[] = urldecode($file);
                }
            }
        }
        $used_files = array_unique($used_files);

        $unused_files = [];
        $total_size = 0;

        foreach ($files as $file) {
            // Skip system or known safe files
            if ($file == 'index.html' || $file == 'index.php')
                continue;

            if (!in_array($file, $used_files)) {
                $size = filesize($path . $file);
                $unused_files[] = [
                    'name' => $file,
                    'size' => $size,
                    'date' => date("Y-m-d H:i:s", filemtime($path . $file))
                ];
                $total_size += $size;
            }
        }

        // Handle deletion
        if ($this->input->post('delete')) {
            $count = 0;
            foreach ($unused_files as $f) {
                if (unlink($path . $f['name'])) {
                    $count++;
                }
            }
            $this->session->set_flashdata('message', $count . ' file berhasil dihapus.');
            redirect('cbtbanksoal/cleanup_images');
        }

        $data = [
            'user' => $user,
            'judul' => 'Cleanup Images',
            'subjudul' => 'Hapus Gambar Tak Terpakai',
            'setting' => $setting,
            'unused' => $unused_files,
            'total_size' => $total_size,
            'path' => $path,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        $role_id = $this->auth->is_admin() ? '1' : ($this->auth->in_group('guru') ? '2' : ($this->auth->in_group('siswa') ? '3' : '4'));
        $this->render_view($role_id, 'cbt/banksoal/cleanup', $data);
    }

    private function getApiKey()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Master_model', 'master');

        $setting = $this->dashboard->getSetting();
        $sysKey = isset($setting->api_key) ? $setting->api_key : '';

        // Default to system key
        $apiKey = $sysKey;

        // If user is Guru, try to get personal key
        if ($this->auth->in_group('guru') || $this->session->userdata('role') == '2') {
            $user = $this->auth->user();
            $tp = $this->master->getTahunActive();
            $smt = $this->master->getSemesterActive();

            $guru = $this->dashboard->getDataGuruByUserId($user->id, isset($tp->id_tp) ? $tp->id_tp : 0, isset($smt->id_smt) ? $smt->id_smt : 0);
            if ($guru && !empty($guru->api_key)) {
                $apiKey = $guru->api_key;
            }
        }

        return $apiKey;
    }

    public function generate_ai()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dashboard_model', 'dashboard');

        $bank_id = $this->input->post('bank_id', true);
        $topic = $this->input->post('topic', true);
        $jumlah = $this->input->post('jumlah_soal', true);
        $tipe = $this->input->post('tipe_soal', true);
        $kesulitan = $this->input->post('tingkat_kesulitan', true);
        $jenjang = $this->input->post('jenjang', true);
        $kelas = $this->input->post('kelas', true);

        $bank = $this->cbt->getDataBankById($bank_id);
        if (!$bank) {
            $this->output_json(['success' => false, 'message' => 'Bank not found']);
            return;
        }

        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            $this->output_json(['success' => false, 'message' => 'API Key belum ditemukan. Silakan atur Google API Key di Profil Anda atau hubungi Admin.']);
            return;
        }

        // Construct Prompt
        $prompt = $this->constructPrompt($jumlah, $topic, $tipe, $kesulitan, $jenjang, $kelas);

        // Fetch AI Response
        $aiResponse = $this->sendToAI($apiKey, $prompt);
        $generated = $this->parseAIResponse($aiResponse);

        if ($generated && is_array($generated) && count($generated) > 0) {
            $data = [];
            $last_no = $this->cbt->getNomorSoalTerbesar($bank_id, $tipe);
            $nomor = ($last_no && isset($last_no->nomor_soal)) ? $last_no->nomor_soal + 1 : 1;

            foreach ($generated as $g) {
                // Determine format based on type
                $item = [
                    'bank_id' => $bank_id,
                    'mapel_id' => $bank->bank_mapel_id,
                    'jenis' => $tipe,
                    'nomor_soal' => $nomor++,
                    'soal' => $g['soal'] ?? 'Soal tidak terbaca',
                    'created_on' => time(),
                    'updated_on' => time(),
                    'tampilkan' => 1,
                    // Default fields
                    'opsi_a' => '',
                    'opsi_b' => '',
                    'opsi_c' => '',
                    'opsi_d' => '',
                    'opsi_e' => '',
                    'jawaban' => ''
                ];

                if ($tipe == '1') { // PG
                    $item['opsi_a'] = $g['opsi_a'] ?? '';
                    $item['opsi_b'] = $g['opsi_b'] ?? '';
                    $item['opsi_c'] = $g['opsi_c'] ?? '';
                    $item['opsi_d'] = $g['opsi_d'] ?? '';
                    $item['opsi_e'] = $g['opsi_e'] ?? '';
                    $item['jawaban'] = strtolower($g['jawaban'] ?? 'a');
                } elseif ($tipe == '2') { // PG Kompleks
                    $aiOpsi = $g['opsi'] ?? [];
                    // Remap arbitrary keys from AI (like "Pernyataan 1") to formatted keys if needed
                    // or just ensure they are stored properly.
                    // Ideally, we want to normalize keys to 'a', 'b', 'c'... or keep valid structure.
                    $opsi = [];
                    // Using values since AI keys might be "Pernyataan 1"
                    $idx = 0;
                    $alphas = range('a', 'z');
                    // We need a mapping to correct the answer keys too
                    $keyMap = [];

                    foreach ($aiOpsi as $k => $v) {
                        // $k might be "Pernyataan 1", $v is "Statement text"
                        // Or AI might assume keys are the statements themselves.
                        // Let's protect against "undefined": ensure $v is string.
                        $val = is_string($v) ? $v : json_encode($v);

                        // Assign new alpha key
                        $newKey = isset($alphas[$idx]) ? $alphas[$idx] : $k;
                        $opsi[$newKey] = $val;
                        $keyMap[strtolower($k)] = $newKey; // Map old key to new key
                        $keyMap[$k] = $newKey;
                        $idx++;
                    }

                    // Revert to json_encode. JS needs JSON, not PHP serialized string.
                    // The remapping to 'a', 'b', 'c' above solves the ID selector issues.
                    $item['opsi_a'] = json_encode($opsi);

                    // Answers as JSON array of keys, mapped to new keys
                    $jwbs = $g['jawaban'] ?? [];
                    $mappedJwbs = [];
                    foreach ($jwbs as $j) {
                        $jLower = strtolower($j);
                        // Check if the answer key exists in our map (e.g. "Pernyataan 1" -> "a")
                        // If not found, check if the value itself was the key (rare but possible with AI)
                        if (isset($keyMap[$jLower])) {
                            $mappedJwbs[] = $keyMap[$jLower];
                        } elseif (isset($keyMap[$j])) {
                            $mappedJwbs[] = $keyMap[$j];
                        } else {
                            // If answer not found in keys, maybe AI returned index? 
                            $mappedJwbs[] = $j;
                        }
                    }
                    $item['jawaban'] = json_encode($mappedJwbs);
                } elseif ($tipe == '3') { // Menjodohkan
                    $pairs = $g['pairs'] ?? [];
                    // Transform pairs into matrix structure
                    // Headers (Right side unique values + dummy)
                    $rightSide = [];
                    foreach ($pairs as $p) {
                        $rightSide[] = $p['kanan'];
                    }
                    $rightSide = array_unique($rightSide);
                    $headerRow = array_merge(['#'], $rightSide);

                    $matrix = [$headerRow];
                    foreach ($pairs as $p) {
                        $left = $p['kiri'];
                        $right = $p['kanan'];
                        $row = [$left];
                        foreach ($rightSide as $h) {
                            $row[] = ($h == $right) ? '1' : '0';
                        }
                        $matrix[] = $row;
                    }

                    $jwbStruct = [
                        'model' => '1',
                        'type' => '1',
                        'jawaban' => $matrix
                    ];
                    $item['jawaban'] = json_encode($jwbStruct);
                } elseif ($tipe == '4' || $tipe == '5') { // Isian / Essai
                    $item['jawaban'] = $g['jawaban'] ?? '';
                } elseif ($tipe == '6') { // Urutan
                    $items = $g['items'] ?? [];
                    // Urutan stored in opsi_a as JSON { items: [...] }
                    $item['opsi_a'] = json_encode(['items' => $items]);
                    // Jawaban correct order is implicit in items for now, or stored in jawaban? 
                    // Based on previous logic, we simply store the items. 
                    // The system randomizes them on display.
                } elseif ($tipe == '7') { // Cloze
                    $soalText = $g['soal'] ?? '';
                    // Parse blanks [[...]] to generate jawaban keys
                    // Regex to find content inside [[...]]
                    preg_match_all('/\[\[(.*?)\]\]/', $soalText, $matches);
                    $answers = [];
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $idx => $val) {
                            // Blank ID usually starts at 1
                            $answers[($idx + 1)] = $val;
                        }
                    }
                    $item['jawaban'] = json_encode($answers);
                    // Ensure the 'soal' saved contains the brackets
                    $item['soal'] = $soalText;
                }

                $data[] = $item;
            }

            if (!empty($data)) {
                $this->db->insert_batch('cbt_soal', $data);
                $this->output_json(['success' => true, 'message' => count($data) . ' Soal berhasil digenerate.']);
            } else {
                $this->output_json(['success' => false, 'message' => 'Gagal memproses respons AI']);
            }
        } else {
            $msg = is_string($generated) ? $generated : 'Gagal menghubungi AI Service atau format respons salah.';
            $this->output_json(['success' => false, 'message' => $msg]);
        }
    }

    public function paraphrase_ai()
    {
        $soal_original = $this->input->post('soal', false);
        $bank_id = $this->input->post('bank_id');
        $jenis = $this->input->post('jenis'); // 1=PG, 2=Komp, 3=Jodoh, 4=Isian, 5=Esai

        if (empty($soal_original)) {
            $this->output_json(['success' => false, 'message' => 'Konten soal kosong']);
            return;
        }

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        // Get API Key and Config
        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            $this->output_json(['success' => false, 'message' => 'API Key belum ditemukan. Silakan atur Google API Key di Profil Anda atau hubungi Admin.']);
            return;
        }

        // Context (Optional)
        $context = "";
        if ($bank_id) {
            $bank = $this->cbt->getDataBankById($bank_id);
            if ($bank) {
                $mapel = isset($bank->nama_mapel) ? $bank->nama_mapel : '';
                $context = "Mata Pelajaran: " . $mapel . ". ";
            }
        }

        $prompt = "Bertindaklah sebagai Ahli Pembuat Soal Ujian (Assessment Specialist). \n";
        $prompt .= "Instruksi: Tulis ulang (Parafrase) narasi soal berikut agar:\n";
        $prompt .= "1. Lebih kontekstual dengan kehidupan nyata (Real-world scenario).\n";
        $prompt .= "2. Menguji kemampuan berpikir tingkat tinggi (HOTS: C4-C6).\n";
        $prompt .= "3. Memiliki STIMULUS yang jelas (Wacana/Kasus/Data) di awal soal.\n";
        $prompt .= "4. Bahasa formal, jelas, dan sesuai EYD.\n";

        // Specific instruction based on Type
        if ($jenis == '2') { // Pilihan Ganda Kompleks
            $prompt .= "5. KHUSUS Pilihan Ganda Kompleks: Tambahkan kalimat instruksi di akhir pertanyaan, contoh: 'Berilah tanda centang (√) pada setiap pernyataan yang benar!'. \n";
        } elseif ($jenis == '3') { // Menjodohkan
            $prompt .= "5. KHUSUS Menjodohkan: Pastikan narasi mengarah pada kegiatan mencocokkan atau menghubungkan dua kelompok data. \n";
        } elseif ($jenis == '4') { // Isian Singkat
            $prompt .= "5. KHUSUS Isian Singkat: Pastikan pertanyaan spesifik dan jawabannya pasti/singkat. \n";
        } elseif ($jenis == '5') { // Uraian / Essay
            $prompt .= "5. KHUSUS Uraian: Gunakan kata tanya 'Jelaskan', 'Uraikan', 'Mengapa', 'Bagaimana' untuk memancing jawaban eksploratif. \n";
        }

        $opsi = $this->input->post('opsi');
        $opsiText = "";
        if ($opsi) {
            $opsiArr = json_decode($opsi, true);
            if ($opsiArr) {
                $opsiText .= "PILIHAN JAWABAN YANG ADA (JANGAN DIUBAH, HANYA REFERENSI):\n";
                foreach ($opsiArr as $k => $v) {
                    $opsiText .= strtoupper($k) . ". " . strip_tags($v) . "\n";
                }
            }
        }

        $prompt .= "6. PENTING: Gunakan PILIHAN JAWABAN di bawah sebagai referensi agar pertanyaan tetap valid dan mengarah pada opsi yang tersedia. JANGAN ubah makna inti soal sehingga kunci jawaban berubah.\n";
        $prompt .= "7. PENTING: Output HANYA HTML konten soal yang baru (termasuk tag <p>, <b>, dll). JANGAN ubah atau sertakan opsi jawaban di output, hanya teks pertanyaan dan stimulusnya. \n\n";
        $prompt .= "Konteks: $context\n";
        $prompt .= "SOAL LAMA:\n" . $soal_original . "\n\n";
        if ($opsiText)
            $prompt .= $opsiText . "\n";
        $prompt .= "Format Output WAJIB JSON: { \"soal\": \"...html content...\" }";

        $response = $this->sendToAI($apiKey, $prompt);
        $parsed = $this->parseAIResponse($response);

        if (is_string($parsed) && strpos($parsed, 'Error') === 0) {
            $this->output_json(['success' => false, 'message' => $parsed]);
        } else {
            $newSoal = $parsed['soal'] ?? '';
            // Fallback if AI returned raw string despite JSON instruction
            if (empty($newSoal) && is_string($parsed) && !is_array($parsed)) {
                $newSoal = $parsed;
            }

            if ($newSoal) {
                $this->output_json(['success' => true, 'data' => $newSoal]);
            } else {
                $this->output_json(['success' => false, 'message' => 'Gagal memparsing hasil AI.']);
            }
        }
    }

    private function constructPrompt($jumlah, $topic, $tipe, $kesulitan, $jenjang, $kelas)
    {
        // Instruksi Utama: PARAFRASE & PENGEMBANGAN SOAL BERBASIS AKM/LITERASI-NUMERASI
        $prompt = "Buatkan {$jumlah} buah soal ujian untuk jenjang {$jenjang} kelas {$kelas} dengan topik '{$topic}'.\n";
        $prompt .= "Tingkat Kesulitan: {$kesulitan}.\n\n";

        // Deep Learning & HOTS Context
        $prompt .= "INSTRUKSI KHUSUS (Wajib diikuti):\n";
        $prompt .= "1. PARAFRASE & KONTEKSTUAL: Jangan buat soal hafalan sederhana. Ubah konsep materi menjadi studi kasus atau skenario dunia nyata yang relevan dengan siswa.\n";
        $prompt .= "2. LITERASI & NUMERASI: Setiap soal HARUS didahului oleh STIMULUS berupa wacana singkat, data statistik, grafik (deskripsi teks), atau situasi masalah yang harus dianalisis siswa sebelum menjawab.\n";
        $prompt .= "3. HOTS (Higher Order Thinking Skills): Soal harus menguji kemampuan Menganalisis (C4), Mengevaluasi (C5), atau Mencipta (C6). Hindari level C1 (Mengingat)/C2 (Memahami) murni.\n";
        $prompt .= "4. SIFAT SOAL: Soal harus menuntut siswa untuk berpikir kritis, memecahkan masalah, dan mengambil keputusan berdasarkan stimulus yang diberikan.\n\n";

        switch ($tipe) {
            case '1':
                $prompt .= "Tipe: Pilihan Ganda (5 opsi: A,B,C,D,E). \n";
                $prompt .= "Format JSON Wajib: [\n  { \n    \"soal\": \"<p><strong>Stimulus:</strong> [Tuliskan wacana/kasus/data disini...]</p><p>[Pertanyaan HOTS terkait stimulus...]</p>\", \n    \"opsi_a\": \"...\", \n    \"opsi_b\": \"...\", \n    \"opsi_c\": \"...\", \n    \"opsi_d\": \"...\", \n    \"opsi_e\": \"...\", \n    \"jawaban\": \"key (a/b/c/d/e)\" \n  }\n]";
                break;
            case '2':
                $prompt .= "Tipe: Pilihan Ganda Kompleks (Benar/Salah atau Centang Banyak). \n";
                $prompt .= "Format JSON Wajib: [\n  { \n    \"soal\": \"<p><strong>Wacana:</strong> [Tuliskan wacana kontekstual...]</p><p>[Pertanyaan...]</p>\", \n    \"opsi\": {\"Pernyataan 1\": \"...\", \"Pernyataan 2\": \"...\", \"Pernyataan 3\": \"...\"}, \n    \"jawaban\": [\"Pernyataan 1\", \"Pernyataan 3\"] \n  }\n] (Jawaban berisi array key/teks opsi yang benar)";
                break;
            case '3':
                $prompt .= "Tipe: Menjodohkan (Matching). \n";
                $prompt .= "Format JSON Wajib: [\n  { \n    \"soal\": \"<p><strong>Data/Situasi:</strong> [Deskripsi situasi...]</p><p>Jodohkanlah premis berikut dengan pasangannya yang tepat berdasarkan data di atas:</p>\", \n    \"pairs\": [\n      { \"kiri\": \"Konsep/Masalah A\", \"kanan\": \"Solusi/Dampak A\" }, \n      { \"kiri\": \"Konsep/Masalah B\", \"kanan\": \"Solusi/Dampak B\" }\n    ] \n  }\n]";
                break;
            case '4':
                $prompt .= "Tipe: Isian Singkat. \n";
                $prompt .= "Format JSON Wajib: [\n  { \n    \"soal\": \"<p>[Sajikan data angka/fakta...]</p><p>Berdasarkan data tersebut, nilai/hasil dari ... adalah?</p>\", \n    \"jawaban\": \"kunci jawaban singkat (angka/kata)\" \n  }\n]";
                break;
            case '5':
                $prompt .= "Tipe: Uraian/Essai. \n";
                $prompt .= "Format JSON Wajib: [\n  { \n    \"soal\": \"<p><strong>Studi Kasus:</strong> [Jelaskan kasus kompleks...]</p><p>Analisislah masalah di atas dan berikan solusi yang tepat...</p>\", \n    \"jawaban\": \"Rubrik: Poin 1... Poin 2...\" \n  }\n]";
                break;
            case '6':
                $prompt .= "Tipe: Mengurutkan (Sequencing). \n";
                $prompt .= "Instruksi: Buatlah soal yang meminta siswa mengurutkan tahapan, prosedur, atau peristiwa kronologis. Pastikan urutannya logis dan tunggal (tidak ambigu).\n";
                $prompt .= "Format JSON Wajib: [\n  { \n    \"soal\": \"<p><strong>Instruksi:</strong> Urutkan langkah-langkah berikut dengan benar...</p>\", \n    \"items\": [\"Langkah 1 (Pertama)\", \"Langkah 2\", \"Langkah 3\", \"Langkah 4 (Terakhir)\"] \n  }\n] (Array 'items' HARUS ditulis dalam urutan KUNCI JAWABAN yang BENAR)";
                break;
            case '7':
                $prompt .= "Tipe: Isian Rumpang (Cloze Passage). \n";
                $prompt .= "Instruksi: Buatlah wacana atau paragraf pendek yang koheren. Kosongkan kata-kata kunci (Key Concepts) dengan tanda [[jawaban]].\n";
                $prompt .= "Format JSON Wajib: [\n  { \n    \"soal\": \"<p>Proses fotosintesis terjadi pada organ tumbuhan yang disebut [[daun]]. Zat hijau daun yang berperan penting disebut [[klorofil]].</p>\" \n  }\n]";
                break;
        }
        $prompt .= "\n\nPENTING: Output HANYA boleh berupa valid JSON array. Jangan ada teks pembuka/penutup lain. Pastikan 'soal' mengandung HTML formatting paragraf yang rapih.";
        return $prompt;
    }

    public function tidy_ai()
    {
        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            $this->output_json(['success' => false, 'message' => 'API Key belum ditemukan. Silakan atur Google API Key di Profil Anda atau hubungi Admin.']);
            return;
        }

        $soal_html = $this->input->post('soal');
        $opsi = $this->input->post('opsi'); // JSON string of options

        if (empty($soal_html)) {
            $this->output_json(['success' => false, 'message' => 'Konten soal kosong']);
            return;
        }

        $prompt = "Kamu adalah asisten yang ahli dalam memperbaiki kode HTML yang berantakan (cleaning & tidying). \n";
        $prompt .= "Tugasmu: Perbaiki struktur HTML berikut ini agar rapi, valid, dan responsive. \n";
        $prompt .= "Aturan Penting:\n";
        $prompt .= "1. JANGAN mengubah teks, kata, angka, atau makna apapun dari konten. Hanya perbaiki tag HTML, table structure, dan styling.\n";
        $prompt .= "2. Jika ada tabel, pastikan gunakan class='table table-bordered' agar rapi. Pastikan tr dan td valid.\n";
        $prompt .= "3. Jika ada gambar, pastikan tag img valid. Jangan ubah src-nya.\n";
        $prompt .= "4. Hapus tag-tag span/font/style inline yang membuat styling berantakan (misal hasil copy paste Word), tapi pertahankan bold/italic/underline jika penting.\n";
        $prompt .= "5. Output HANYA JSON dengan format: { \"soal\": \"...html result...\", \"opsi\": { \"a\": \"...\", \"b\": \"...\" } }\n";
        $prompt .= "6. Pastikan properti 'soal' berisi string HTML yang valid dan bisa dirender browser.\n";

        if ($opsi) {
            $prompt .= "7. Perbaiki juga opsi jawaban berikut dengan aturan yang sama (rapikan HTML-nya). Sertakan hasilnya dalam JSON properti 'opsi'.\n\n";
            $prompt .= "OPSI JAWABAN LAIN (JSON):\n" . $opsi . "\n\n";
        }

        $prompt .= "HTML SOAL:\n" . $soal_html;

        $response = $this->sendToAI($apiKey, $prompt);
        $parsed = $this->parseAIResponse($response);

        if (is_string($parsed) && strpos($parsed, 'Error') === 0) {
            $this->output_json(['success' => false, 'message' => $parsed]);
        } else {
            $newSoal = $parsed['soal'] ?? '';
            // If parsed is string, it failed to be JSON
            if (empty($newSoal) && is_string($parsed) && !is_array($parsed)) {
                $newSoal = $parsed;
                $newOpsi = [];
            } else {
                $newOpsi = $parsed['opsi'] ?? [];
            }

            if ($newSoal) {
                // Remove Markdown code blocks if any remain
                $newSoal = preg_replace('/^```html\s*/', '', $newSoal);
                $newSoal = preg_replace('/^```/', '', $newSoal);
                $newSoal = preg_replace('/\s*```$/', '', $newSoal);

                $dataRes = ['success' => true, 'data' => $newSoal];
                if (!empty($newOpsi)) {
                    $dataRes['opsi'] = $newOpsi;
                }
                $this->output_json($dataRes);
            } else {
                $this->output_json(['success' => false, 'message' => 'Gagal memparsing hasil AI.']);
            }
        }
    }

    public function tidy_manual()
    {
        $soal_html = $this->input->post('soal');
        $opsi = $this->input->post('opsi');

        if (empty($soal_html)) {
            $this->output_json(['success' => false, 'message' => 'Konten soal kosong']);
            return;
        }

        // Clean Soal (Maintain paragraphs)
        $newSoal = $this->clean_html_string($soal_html, false);

        // Clean Opsi (Force single line)
        $newOpsi = [];
        if ($opsi) {
            $opsiArr = json_decode($opsi, true);
            if (is_array($opsiArr)) {
                foreach ($opsiArr as $k => $v) {
                    $newOpsi[$k] = $this->clean_html_string($v, true);
                }
            }
        }

        $this->output_json([
            'success' => true,
            'data' => $newSoal,
            'opsi' => $newOpsi
        ]);
    }

    private function clean_html_string($html, $singleLine = false)
    {
        if (empty(trim($html)))
            return $html;

        // Replace block tags with newlines to preserve structure
        $html = str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</h1>', '<h2>', '<h3>', '</h4>', '</h5>', '</h6>', '</li>'], "\n", $html);
        // Replace cell closings with space
        $html = str_replace(['</td>', '</th>'], ' ', $html);

        // Strip tags BUT allow <img>
        $clean = strip_tags($html, '<img>');

        // Split by newlines
        $lines = preg_split("/[\r\n]+/", $clean);

        $collectedLines = [];
        foreach ($lines as $line) {
            // Trim whitespace
            $trimmed = trim(str_replace('&nbsp;', ' ', $line));
            $trimmed = trim(preg_replace('/\s+/', ' ', $trimmed)); // Collapse multiple spaces

            if ($trimmed === '')
                continue;

            // Handle Images if present
            if (strpos($trimmed, '<img') !== false) {
                // Use regex to clean up <img> tags
                // Remove style, width, height, and force class="img-fluid"
                $trimmed = preg_replace_callback('/<img[^>]+>/i', function ($matches) {
                    $img = $matches[0];

                    // Remove unwanted attributes
                    $img = preg_replace('/(style|width|height|class)="[^"]*"/i', '', $img);
                    $img = preg_replace("/(style|width|height|class)='[^']*'/i", '', $img);

                    // Normalize spaces inside tag
                    $img = preg_replace('/\s+/', ' ', $img);

                    // Rebuild with img-fluid
                    // Note: This naive replace assumes 'src' is preserved.
                    // Instead of full rebuild, we just injection.

                    // Simpler approach: Just strip known bad attributes and add class
                    return str_replace('<img', '<img class="img-fluid"', $img);
                }, $trimmed);
            }

            // Capitalize first letter if it's text (and not starting with a tag like <img)
            // Even if it starts with <img, we might want to capitalize text after it? 
            // Complexity arises. Let's simple check: if line does not start with <, capitalize.
            if (mb_substr($trimmed, 0, 1) !== '<') {
                $firstChar = mb_substr($trimmed, 0, 1);
                $rest = mb_substr($trimmed, 1);
                $trimmed = mb_strtoupper($firstChar) . $rest;
            }

            $collectedLines[] = $trimmed;
        }

        if (empty($collectedLines))
            return '';

        if ($singleLine) {
            // Merge all lines into one sentence/paragraph
            return '<p>' . implode(' ', $collectedLines) . '</p>';
        } else {
            // Preserve paragraphs
            $output = '';
            foreach ($collectedLines as $l) {
                $output .= '<p>' . $l . '</p>';
            }
            return $output;
        }
    }

    private function sendToAI($apiKey, $prompt)
    {
        $messages = [
            ['role' => 'system', 'content' => 'Anda adalah asisten pembuat soal ujian profesional yang outputnya selalu JSON valid.'],
            ['role' => 'user', 'content' => $prompt]
        ];

        // Simple heuristic: Jika key mulai dengan 'sk-', asumsi OpenAI. Jika tidak, coba Gemini endpoint.
        $isOpenAI = (strpos($apiKey, 'sk-') === 0);

        $apiKey = trim($apiKey); // Ensure no whitespace

        if ($isOpenAI) {
            $url = 'https://api.openai.com/v1/chat/completions';
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'temperature' => 0.7
            ];
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ];

            return $this->executeCurl($url, $data, $headers);
        } else {
            // Assume Gemini - First, dynamically fetch available models to avoid 404s
            $listUrl = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $listUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $listResponse = curl_exec($ch);
            curl_close($ch);

            $availableModel = '';
            $listData = json_decode($listResponse, true);

            if (isset($listData['models'])) {
                // Find the first model that supports generateContent
                foreach ($listData['models'] as $m) {
                    if (isset($m['supportedGenerationMethods']) && in_array('generateContent', $m['supportedGenerationMethods'])) {
                        // Prefer 1.5-flash or pro if available
                        if (strpos($m['name'], 'flash') !== false) {
                            $availableModel = $m['name']; // e.g. models/gemini-1.5-flash
                            break;
                        }
                        if (empty($availableModel)) {
                            $availableModel = $m['name'];
                        }
                    }
                }
            } else {
                // If we can't list models, it's a strong sign the API is not enabled or Key is wrong
                $errorDetails = isset($listData['error']) ? json_encode($listData['error']) : $listResponse;
                return "Error: Gagal mengambil daftar model Gemini. \nKemungkinan 'Generative Language API' BELUM AKTIF. \nResponse Google: " . $errorDetails;
            }

            if (empty($availableModel)) {
                // Fallback hardcoded if discovery failed but no error returned (rare)
                $availableModel = 'models/gemini-1.5-flash';
            }

            // The model name from API comes as "models/gemini-pro", so we use it directly
            // or if we hardcoded, we ensure the format. 
            // The generate URL expects: /v1beta/{model}:generateContent
            // If $availableModel is "models/gemini-1.5-flash", strictly speaking the URL is /v1beta/models/gemini-1.5-flash:generateContent
            // But usually the name field IS "models/gemini-..."

            // Clean up name if it has "models/" prefix because we might construct URL differently or same
            // Standard endpoint: https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent
            // If $availableModel is "models/gemini-pro", we need to be careful not to double "models/"

            if (strpos($availableModel, 'models/') === 0) {
                $modelName = substr($availableModel, 7); // remove models/
            } else {
                $modelName = $availableModel;
            }

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $modelName . ':generateContent?key=' . $apiKey;

            $geminiPrompt = "System: Anda adalah asisten pembuat soal ujian profesional yang outputnya selalu JSON valid.\nUser: " . $prompt;
            $data = [
                'contents' => [
                    ['parts' => [['text' => $geminiPrompt]]]
                ]
            ];
            $headers = ['Content-Type: application/json'];

            return $this->executeCurl($url, $data, $headers);
        }
    }

    private function executeCurl($url, $data, $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = 'Curl Error: ' . curl_error($ch);
            curl_close($ch);
            return 'Error: ' . $error_msg;
        }
        curl_close($ch);

        if ($httpCode >= 400) {
            $errorData = json_decode($result, true);
            $friendlyMsg = '';

            // Handle Gemini specific errors
            if (isset($errorData['error']['message'])) {
                $rawMsg = $errorData['error']['message'];
                if ($httpCode == 429 || strpos($rawMsg, 'quota') !== false || strpos($rawMsg, 'RESOURCE_EXHAUSTED') !== false) {
                    $friendlyMsg = "Kuota API AI (Gemini) Anda telah habis untuk saat ini. Silakan tunggu beberapa saat atau periksa billing Google Cloud Anda.";
                } elseif ($httpCode == 400 || strpos($rawMsg, 'INVALID_ARGUMENT') !== false) {
                    $friendlyMsg = "Format permintaan tidak valid. Kemungkinan Prompt terlalu panjang atau mengandung karakter yang tidak didukung.";
                } elseif ($httpCode == 403 || strpos($rawMsg, 'PERMISSION_DENIED') !== false) {
                    $friendlyMsg = "Akses Ditolak. Pastikan API Key Anda valid dan memiliki izin yang benar.";
                } elseif ($httpCode == 404 || strpos($rawMsg, 'NOT_FOUND') !== false) {
                    $friendlyMsg = "Model AI tidak ditemukan atau tidak tersedia untuk akun ini. Coba ganti model lain.";
                } elseif ($httpCode >= 500) {
                    $friendlyMsg = "Terjadi gangguan pada Server Google Gemini (Server Error). Silakan coba lagi nanti.";
                } else {
                    $friendlyMsg = "AI Error (" . $httpCode . "): " . $rawMsg;
                }
            } else {
                $friendlyMsg = "Gagal terhubung ke AI (HTTP " . $httpCode . "). Silakan periksa koneksi atau API Key Anda.";
            }

            return 'Error: ' . $friendlyMsg;
        }

        return $result;
    }

    private function parseAIResponse($response)
    {
        if (strpos($response, 'Error:') === 0)
            return $response;

        $json = json_decode($response, true);
        $content = null;

        // Try OpenAI format
        if (isset($json['choices'][0]['message']['content'])) {
            $content = $json['choices'][0]['message']['content'];
        }
        // Try Gemini format
        elseif (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            $content = $json['candidates'][0]['content']['parts'][0]['text'];
        }

        if ($content) {
            // Clean Markdown if present
            $content = preg_replace('/^```json\s*/', '', $content);
            $content = preg_replace('/^```/', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);

            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                // Return raw content if JSON decode fails, as it might be plain text or HTML requested
                return $content;
            }
        }

        return 'Error: No content found in Response or Invalid Structure. Raw: ' . substr($response, 0, 200);
    }
}
