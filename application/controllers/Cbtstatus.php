<?php


class Cbtstatus extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && (!$this->auth->in_group('guru') && !$this->auth->in_group('panitia') && $this->session->userdata('role') != '4')) {
            show_error('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['datatables', 'form_validation', 'upload', 'user_agent']); // Load Library Ignited-Datatables
        $this->form_validation->set_error_delimiters('', '');
    }

    private function getDistinctRuang($tp, $smt, $arrKelas)
    {
        $this->db->distinct();
        $this->db->select('a.ruang_id, a.sesi_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi');
        $this->db->from('cbt_sesi_siswa a');
        $this->db->where('a.tp_id', $tp);
        $this->db->where('a.smt_id', $smt);
        $this->db->join('cbt_ruang b', 'b.id_ruang=a.ruang_id');
        $this->db->join('cbt_sesi c', 'c.id_sesi=a.sesi_id');
        if (count($arrKelas) > 0) {
            $this->db->where_in('kelas_id', $arrKelas);
        }
        $this->db->order_by('b.nama_ruang', 'ASC');
        $this->db->order_by('c.nama_sesi', 'ASC');
        $result = $this->db->get()->result();
        //$ret = [];
        $ruangs = [];
        $sesis = [];
        if ($result) {
            foreach ($result as $key => $row) {
                //$ret [$row->ruang_id][$row->sesi_id] = $row;
                $ruangs[$row->ruang_id] = $row->nama_ruang;
                $sesis[$row->sesi_id] = $row->nama_sesi;
            }
        }
        return ['ruang' => $ruangs, 'sesi' => $sesis];
        //return $ret;
    }

    private function getPengawasByGuru($tp, $smt, $id_guru)
    {
        $this->db->select('a.id_pengawas, a.id_jadwal, a.id_ruang, a.id_sesi, a.id_guru, a.jam_ke,' .
            ' b.tgl_mulai, b.tgl_selesai, c.bank_kode, c.bank_kelas, c.bank_level, c.bank_mapel_id, d.kode_jenis, e.kode, e.nama_mapel');
        $this->db->from('cbt_pengawas a');
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);
        $this->db->like('a.id_guru', $id_guru);
        $this->db->join('cbt_jadwal b', 'b.id_jadwal=a.id_jadwal');
        $this->db->join('cbt_bank_soal c', 'b.id_bank=c.id_bank');
        $this->db->join('cbt_jenis d', 'd.id_jenis=b.id_jenis', "left");
        $this->db->join('master_mapel e', 'e.id_mapel=c.bank_mapel_id', "left");
        return $this->db->get()->result();
    }

    private function getJadwalById($id_jadwal, $sesi = null)
    {
        $this->db->select('a.*, b.opsi, b.bank_kode, b.bank_level, b.bank_kelas,'
            . ' b.tampil_pg, b.tampil_kompleks, b.tampil_jodohkan, b.tampil_isian, b.tampil_esai,'
            . ' b.bobot_pg, b.bobot_kompleks, b.bobot_jodohkan, b.bobot_isian, b.bobot_esai,'
            . ' b.id_bank, b.bank_guru_id, b.soal_agama, c.kode_jenis, c.nama_jenis,'
            . ' d.id_mapel, d.kode, d.nama_mapel, f.id_guru, f.nama_guru');
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank', "left");
        $this->db->join('cbt_jenis c', 'c.id_jenis=a.id_jenis', "left");
        $this->db->join('master_mapel d', 'd.id_mapel=b.bank_mapel_id', "left");
        if ($sesi != null) {
            $this->db->join('cbt_sesi e', 'e.id_sesi=' . $sesi, "left");
        }
        $this->db->join('master_guru f', 'f.id_guru=b.bank_guru_id', "left");
        $this->db->where('a.id_jadwal', $id_jadwal);

        return $this->db->get()->row();
    }

    private function getAllJadwal($tp, $smt, $id_guru = null)
    {
        $this->db->select('a.bank_kode, a.bank_kelas, b.id_jadwal, d.kode');
        $this->db->from('cbt_bank_soal a');
        $this->db->join('cbt_jadwal b', 'b.id_bank=a.id_bank');
        $this->db->join('master_mapel d', 'd.id_mapel=a.bank_mapel_id', "left");
        if ($id_guru != null)
            $this->db->where('a.bank_guru_id', $id_guru);
        $this->db->where('b.id_tp', $tp);
        $this->db->where('b.id_smt', $smt);

        return $this->db->get()->result();
    }

    private function getDropJadwalGuru($tp, $smt, $guru)
    {
        $this->db->from('cbt_jadwal a');
        $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank AND b.bank_guru_id=' . $guru);
        $this->db->where('a.id_tp', $tp);
        $this->db->where('a.id_smt', $smt);

        $result = $this->db->get()->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_jadwal] = $row->bank_kode;
            }
        }
        return $ret;
    }

    public function getNamaKelasByArrId($tp, $smt, $ids)
    {
        $this->db->select('id_kelas,nama_kelas');
        $this->db->where_in('id_kelas', $ids);
        $this->db->where('id_tp', $tp);
        $this->db->where('id_smt', $smt);
        $result = $this->db->get('master_kelas')->result();
        $ret = [];
        if ($result) {
            foreach ($result as $key => $row) {
                $ret[$row->id_kelas] = $row->nama_kelas;
            }
        }
        return $ret;
    }

    public function index()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_ruang_model', 'ruang_model');
        $this->load->model('Cbt_sesi_model', 'sesi_model');
        $this->load->model('Cbt_model', 'cbt');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Status Ujian Siswa',
            'subjudul' => 'Status Siswa',
            'setting' => $this->dashboard->getSetting(),
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['is_admin'] = $this->auth->is_admin() || $this->auth->in_group('panitia') || $this->session->userdata('role') == '4';

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/status/data_status', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            // Panitia uses admin-style view for status
            $this->render_view(4, 'cbt/status/data_status', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['jadwal'] = $this->getDropJadwalGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);
            $data['ruang'] = $this->ruang_model->as_dropdown('nama_ruang')->get_all();
            $data['sesi'] = $this->sesi_model->as_dropdown('nama_sesi')->get_all();
            $id_guru = $guru->id_guru;
            $pengawas = $this->getPengawasByGuru($tp->id_tp, $smt->id_smt, $id_guru);
            $hasil = [];
            $allKls = [];

            $ruang_sesi = $this->cbt->getRuangSesi($tp->id_tp, $smt->id_smt);
            foreach ($pengawas as $item) {
                $kls = $this->maybe_unserialize($item->bank_kelas ?? []);
                $arrKls = [];
                foreach ($kls as $kl) {
                    $arrKls[] = $kl->kelas_id;
                    $allKls[] = $kl->kelas_id;
                }
                if (!isset($ruang_sesi[$item->id_sesi][$item->id_ruang])) {
                    continue;
                }
                $keyKls = array_keys($ruang_sesi[$item->id_sesi][$item->id_ruang]);
                if (!in_array($keyKls[0], $arrKls)) {
                    continue;
                }
                // Gunakan kombinasi kunci yang diinginkan
                $key = $item->id_ruang . '-' . $item->id_sesi . '-' . $item->tgl_mulai . '-' . $item->bank_mapel_id;

                if (!isset($hasil[$key])) {
                    $hasil[$key] = [
                        'id_ruang' => $item->id_ruang,
                        'id_sesi' => $item->id_sesi,
                        'tgl_mulai' => $item->tgl_mulai,
                        'nama_mapel' => $item->nama_mapel,
                        'id_mapel' => $item->bank_mapel_id,
                        'bank_soal' => []
                    ];
                }

                $hasil[$key]['bank_soal'][] = [
                    'id_pengawas' => $item->id_pengawas,
                    'id_jadwal' => $item->id_jadwal,
                    'id_guru' => $item->id_guru,
                    'tgl_selesai' => $item->tgl_selesai,
                    'bank_kode' => $item->bank_kode,
                    'bank_level' => $item->bank_level,
                    'kode_jenis' => $item->kode_jenis,
                    'kode' => $item->kode
                ];

            }

            $output = json_decode(json_encode(array_values($hasil)));
            $data['pengawas'] = $output;
            $data['ruang_sesi'] = $ruang_sesi;
            $data['ruangs'] = $this->getDistinctRuang($tp->id_tp, $smt->id_smt, $allKls);
            $data['kelass'] = $allKls;

            $this->render_view(2, 'members/guru/cbt/status/data', $data);
        }
    }

    public function getJadwalUjianByJadwal()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $jadwal = $this->input->get('id_jadwal');
        $info = $this->getJadwalById($jadwal);

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $kelas = $this->maybe_unserialize($info->bank_kelas ?? '[]');
        $arrKls = [];
        foreach ($kelas as $key => $value) {
            $arrKls[] = $value->kelas_id;
        }
        $kelases = $this->dropdown->getNamaKelasByArrId($info->id_tp, $info->id_smt, $arrKls);

        $this->output_json($kelases);
    }

    public function getTglJadwalUjian()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_ruang_model', 'ruang_model');
        $this->load->model('Cbt_sesi_model', 'sesi_model');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $jadwals = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai')
            ->where(['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt])
            ->with_bank([
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt],
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel']
                ],
            ])
            ->as_array()
            ->get_all();

        if ($this->auth->is_admin() || $this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $tgl_jadwal = [];
            $kelases = [];
            $ruangs = [];
            $sesis = [];

            $dropJadwal = [];
            if ($jadwals !== false) {
                foreach ($jadwals as $k_jad => $jad) {
                    $jad['bank']->bank_kelas = $this->maybe_unserialize($jad['bank']->bank_kelas ?? []);
                    $kls = $jad['bank']->bank_kelas;
                    $arrKls = [];
                    foreach ($kls as $kl) {
                        if (!in_array($kl->kelas_id, $arrKls)) {
                            $arrKls[] = $kl->kelas_id;
                        }
                    }
                    $timestamp = strtotime($jad['tgl_mulai'] ?? '');
                    $hari = $this->namaHari[date('l', $timestamp)];
                    $tanggal = date('d', $timestamp);
                    $bulan = $this->namaBulan[date('F', $timestamp)];
                    $tahun = date('Y', $timestamp);

                    $tgl_jadwal[$jad['tgl_mulai']] = "$hari, $tanggal $bulan $tahun";
                    $ruang_sesi = $this->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);

                    foreach ($ruang_sesi['ruang'] as $id_ruang => $ruang) {
                        $ruangs[$jad['tgl_mulai']][$id_ruang] = $ruang;
                    }
                    foreach ($ruang_sesi['sesi'] as $id_sesi => $sesi) {
                        $sesis[$jad['tgl_mulai']][$id_sesi] = $sesi;
                    }
                    $kelases[$jad['tgl_mulai']][] = $this->getNamaKelasByArrId($tp->id_tp, $smt->id_smt, $arrKls);

                    $dropJadwal[$jad['tgl_mulai']][$jad['id_jadwal']] = $jad['bank']->bank_kode;
                    unset($jadwals[$k_jad]['bank']);
                }
            }
            $data['kelas'] = $kelases;
            $data['jadwal'] = $tgl_jadwal;
            $data['ruang'] = $ruangs;
            $data['sesi'] = $sesis;
            $data['all_jadwal'] = $dropJadwal;
        } else {
            $this->load->model('Cbt_model', 'cbt');
            $user = $this->auth->user();
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $all_pengawas = $this->getPengawasByGuru($tp->id_tp, $smt->id_smt, $guru->id_guru);
            $ids_jadwal = array_column($all_pengawas, 'id_jadwal');
            $data['pengawas'] = $all_pengawas;
            $today = date('Y-m-d');

            $arrAllKls = [];
            $kelases = [];
            $ruangs = [];
            $sesis = [];
            $jadwal_all = [];
            if ($jadwals) {
                foreach ($jadwals as $key => $jad) {
                    if (!in_array($jad['id_jadwal'], $ids_jadwal) && (isset($jad['bank']) && $jad['bank']->bank_guru_id != $guru->id_guru)) {
                        unset($jadwals[$key]);
                    } else {
                        $jad['bank']->bank_kelas = $this->maybe_unserialize($jad['bank']->bank_kelas ?? []);
                        $kls = $jad['bank']->bank_kelas;
                        $arrKls = [];
                        foreach ($kls as $kl) {
                            $arrKls[] = $kl->kelas_id;
                            $arrAllKls[] = $kl->kelas_id;
                        }
                        $jad['pengawas'] = in_array($jad['id_jadwal'], $ids_jadwal);
                        $jad['pengampu'] = $jad['bank']->bank_guru_id == $guru->id_guru;

                        $ruang_sesi = $this->getDistinctRuang($tp->id_tp, $smt->id_smt, $arrKls);
                        foreach ($ruang_sesi['ruang'] as $id_ruang => $ruang) {
                            $ruangs[$jad['id_jadwal']][$id_ruang] = $ruang;
                        }
                        foreach ($ruang_sesi['sesi'] as $id_sesi => $sesi) {
                            $sesis[$jad['id_jadwal']][$id_sesi] = $sesi;
                        }
                        $kelases[$jad['id_jadwal']] = $this->getNamaKelasByArrId($tp->id_tp, $smt->id_smt, $arrKls);

                        $jadwal_all[$jad['tgl_mulai']][] = $jad;
                    }
                }
            }

            $data['hari_ini'] = $today;
            $data['jadwals'] = $jadwal_all;
            $data['kelas'] = $kelases;
            $data['ruang'] = $ruangs;
            $data['sesi'] = $sesis;
            $ruangs_sesi = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrAllKls);
            $data['ruangs_sesi'] = array_values($ruangs_sesi);
        }
        $this->output_json($data);
    }

    public function getJadwalUjianByKelas()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $kelas = $this->input->get('id_kelas');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        if ($this->auth->in_group('guru')) {
            $user = $this->auth->user();
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $id_guru = $guru->id_guru;
        } else {
            $id_guru = null;
        }

        $jadwals = $this->getAllJadwal($tp->id_tp, $smt->id_smt, $id_guru);

        $jdwl = [];
        foreach ($jadwals as $jadwal) {
            $kls = $this->maybe_unserialize($jadwal->bank_kelas ?? []);
            foreach ($kls as $kl) {
                if ($kl->kelas_id == $kelas) {
                    $jdwl[$jadwal->id_jadwal] = $jadwal->kode . ' (' . $jadwal->bank_kode . ')';
                }
            }
        }

        $this->output_json($jdwl);
    }

    // pengawas status siswa
    public function pengawas()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Guru_model', 'guru_model');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');
        $jadwal = $this->input->get('jadwal');
        //$allJadwal = explode(',', $jadwal);
        $data['jadwal_id'] = $jadwal;
        $data['ruang_id'] = $ruang;
        $data['sesi_id'] = $sesi;

        $user = $this->auth->user();
        $data['user'] = $user;
        $data['judul'] = 'Status Ujian Siswa';
        $data['subjudul'] = 'Status Siswa';
        $data['setting'] = $this->dashboard->getSetting();

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        $data['guru'] = $guru;

        $status = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,pelanggaran,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak,pelanggaran')
            ->where('id_jadwal', $jadwal)
            ->with_pengawas([
                'where' => ['id_ruang' => $ruang, 'id_sesi' => $sesi],
            ])
            ->get();

        $ids_pengawas = [];
        if ($status) {
            $pengawas = $status->pengawas;
            if ($pengawas) {
                foreach ($pengawas as $pws) {
                    $ids = explode(',', $pws->id_guru ?? '');
                    foreach ($ids as $id) {
                        if (!in_array($id, $ids_pengawas)) {
                            $ids_pengawas[] = $id;
                        }
                    }
                }
            }
        }

        $data['ids_pengawas'] = $ids_pengawas;

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'members/guru/cbt/status/pengawas', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'members/guru/cbt/status/pengawas', $data);
        } else {
            $this->render_view(2, 'members/guru/cbt/status/pengawas', $data);
        }
    }

    // new akun guru
    public function get_status_ruang()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Tp_model', 'tp');
        $this->load->model('Smt_model', 'smt');
        $this->load->model('Guru_model', 'guru_model');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('cbt_sesisiswa_model', 'sesisiswa_model');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_log_model', 'cbt_log_model');

        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');
        $jadwal = $this->input->get('jadwal');
        //$allJadwal = explode(',', $jadwal);

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $status = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,pelanggaran,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak')
            ->where('id_jadwal', $jadwal)
            ->with_bank([
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt],
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel'],
                    ['relation' => 'guru', 'fields' => 'kode_guru,nama_guru']
                ],
            ])
            ->with_jenis()
            ->with_pengawas([
                'where' => ['id_ruang' => $ruang, 'id_sesi' => $sesi],
            ])
            ->as_array()
            ->get();

        if (!$status) {
            $this->output_json([
                'jadwal' => [],
                'ids_pengawas' => [],
                'pengawas' => [],
                'today' => date('Y-m-d H:i:s'),
                'sesisiswa' => [],
                'is_empty' => true,
                'message' => 'no jadwal'
            ]);
            return;
        }

        $ids_pengawas = [];
        $arrKls = [];
        $status['bank']->bank_kelas = $this->maybe_unserialize($status['bank']->bank_kelas ?? []);
        $kls = $status['bank']->bank_kelas;
        foreach ($kls as $kl) {
            if (!in_array($kl->kelas_id, $arrKls)) {
                $arrKls[] = $kl->kelas_id;
            }
        }

        $pengawas = $status['pengawas'];
        if ($pengawas && count($pengawas) > 0) {
            foreach ($pengawas as $pws) {
                $ids = explode(',', $pws->id_guru ?? '');
                $ids_pengawas = array_values(array_filter($ids));
            }
        }

        $guru_ngawas = [];
        if (count($ids_pengawas) > 0) {
            $guru_ngawas = $this->guru_model->fields('nama_guru,nip')
                ->where('id_guru', $ids_pengawas)
                ->as_array()
                ->get_all();
        }

        $where['tp_id'] = $tp->id_tp;
        $where['smt_id'] = $smt->id_smt;
        $where['ruang_id'] = $ruang;
        $where['sesi_id'] = $sesi;

        $siswas = $this->sesisiswa_model
            ->where($where)
            ->with_siswa(['fields' => 'id_siswa,nama,nis,nisn,agama'])
            ->with_peserta()
            ->with_kelas(['fields' => 'kode_kelas,nama_kelas'])
            ->with_ruang(['fields' => 'kode_ruang,nama_ruang'])
            ->with_sesi(['fields' => 'kode_sesi,nama_sesi'])
            ->as_array()
            ->get_all();

        if (!$siswas) {
            $this->output_json([
                'jadwal' => $status,
                'ids_pengawas' => $ids_pengawas,
                'pengawas' => $guru_ngawas,
                'today' => date('Y-m-d H:i:s'),
                'sesisiswa' => [],
                'is_empty' => true,
                'message' => 'no siswa'
            ]);
            return;
        }

        $ids_siswa = [];
        foreach ($siswas as $siswa) {
            if (!in_array($siswa['siswa_id'], $ids_siswa)) {
                $ids_siswa[] = $siswa['siswa_id'];
            }
        }

        $cbtsiswa = $this->cbt_siswa_model
            ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status,pelanggaran')
            ->where('id_siswa', $ids_siswa)
            ->where('id_jadwal', $jadwal)
            //->with_jadwal()
            ->get_all();

        if (!$cbtsiswa) {
            $this->output_json([
                'jadwal' => $status,
                'ids_pengawas' => $ids_pengawas,
                'pengawas' => $guru_ngawas,
                'today' => date('Y-m-d H:i:s'),
                'sesisiswa' => $siswas,
                'idsiswa' => $ids_siswa,
                'idjadwal' => $jadwal,
                'is_empty' => true,
                'message' => 'no cbtsiswa'
            ]);
            return;
        }
        $map_cbt = [];
        foreach ($cbtsiswa as $cbt) {
            $cbt->pelanggaran = $this->maybe_unserialize($cbt->pelanggaran ?? '{}');
            $map_cbt[$cbt->id_siswa][$cbt->id_jadwal] = $cbt;
        }

        $logsiswa = $this->cbt_log_model->where('id_siswa', $ids_siswa)
            ->where('id_jadwal', $jadwal)
            ->get_all();

        if (!$logsiswa) {
            $this->output_json([
                'jadwal' => $status,
                'ids_pengawas' => $ids_pengawas,
                'pengawas' => $guru_ngawas,
                'today' => date('Y-m-d H:i:s'),
                'sesisiswa' => $siswas,
                'idsiswa' => $ids_siswa,
                'idjadwal' => $jadwal,
                'is_empty' => true,
                'map_cbt' => $map_cbt,
                'message' => 'no logs'
            ]);
            return;
        }

        $map_log = [];
        foreach ($logsiswa as $log) {
            $map_log[$log->id_siswa][$log->id_jadwal][] = $log;
        }

        $all_siswa = [];
        foreach ($siswas as $siswa) {
            if (in_array($siswa['kelas_id'], $arrKls)) {
                $siswa['cbtsiswa'] = $map_cbt[$siswa['siswa_id']] ?? null;
                $siswa['log'] = $map_log[$siswa['siswa_id']] ?? [];
                $all_siswa[] = $siswa;
            }
        }

        $data['ids_pengawas'] = $ids_pengawas;
        $data['pengawas'] = $guru_ngawas;
        $data['jadwal'] = $status;
        $data['today'] = date('Y-m-d H:i:s');
        $data['sesisiswa'] = $all_siswa;
        $data['is_empty'] = !$cbtsiswa;

        $this->output_json($data);
    }

    public function belum_ujian()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Cbt_model', 'cbt');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Siswa Belum Ujian',
            'subjudul' => 'Data Siswa Belum Ujian',
            'setting' => $this->dashboard->getSetting(),
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruang'] = $this->cbt->getAllRuang();
        $data['mapel'] = $this->dropdown->getAllMapelJadwal($tp->id_tp, $smt->id_smt);

        if ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'members/panitia/cbt/status/belum_ujian', $data);
        } else {
            // For Admin default
            $this->render_view(1, 'cbt/status/belum_ujian', $data);
        }
    }

    public function get_siswa_belum_ujian()
    {
        ini_set('memory_limit', '512M');
        try {
            $this->load->model('Dashboard_model', 'dashboard');
            $filter_kelas = $this->input->post('kelas');
            $filter_ruang = $this->input->post('ruang');
            $filter_mapel = $this->input->post('mapel');

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            // 1. Get Active Schedules (Today)
            $today = date('Y-m-d');
            $now = date('Y-m-d H:i:s');
            $this->db->select('a.id_jadwal, a.tgl_mulai, a.tgl_selesai, a.jam_ke, a.durasi_ujian, b.bank_kelas, b.bank_kode, b.bank_mapel_id, c.nama_mapel, c.kode as kode_mapel');
            $this->db->from('cbt_jadwal a');
            $this->db->join('cbt_bank_soal b', 'b.id_bank=a.id_bank');
            $this->db->join('master_mapel c', 'c.id_mapel=b.bank_mapel_id');
            $this->db->where('a.tgl_mulai <=', $today . ' 23:59:59');
            $this->db->where('a.tgl_selesai >=', $today . ' 00:00:00');
            $this->db->where('a.id_tp', $tp->id_tp);
            $this->db->where('a.id_smt', $smt->id_smt);
            if ($filter_mapel) {
                $this->db->where('b.bank_mapel_id', $filter_mapel);
            }
            $jadwals = $this->db->get()->result();

            $result = [];
            $stats = [
                'total_siswa' => 0,
                'belum_mulai' => 0,
                'sedang_mengerjakan' => 0,
                'selesai' => 0
            ];

            foreach ($jadwals as $jadwal) {
                // Get Eligible Classes for this Jadwal
                $bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas);
                $kelas_ids = [];

                if (!empty($bank_kelas) && (is_array($bank_kelas) || is_object($bank_kelas))) {
                    foreach ($bank_kelas as $bk) {
                        $kid = null;
                        if (is_object($bk)) {
                            if (isset($bk->kelas_id))
                                $kid = $bk->kelas_id;
                            elseif (isset($bk->id))
                                $kid = $bk->id;
                        } elseif (is_array($bk)) {
                            if (isset($bk['kelas_id']))
                                $kid = $bk['kelas_id'];
                            elseif (isset($bk['id']))
                                $kid = $bk['id'];
                        } else {
                            $kid = $bk;
                        }

                        if (!empty($kid) && (is_string($kid) || is_numeric($kid))) {
                            if ($filter_kelas && $kid != $filter_kelas)
                                continue;
                            $kelas_ids[] = $kid;
                        }
                    }
                }

                if (empty($kelas_ids))
                    continue;

                // Pre-fetch Pengawas for this Jadwal
                $this->db->select('a.id_ruang, b.nama_guru');
                $this->db->from('cbt_pengawas a');
                $this->db->join('master_guru b', 'b.id_guru=a.id_guru');
                $this->db->where('a.id_jadwal', $jadwal->id_jadwal);
                $pengawas_list = $this->db->get()->result();
                $map_pengawas = [];
                foreach ($pengawas_list as $pl) {
                    $map_pengawas[$pl->id_ruang] = $pl->nama_guru;
                }

                // Get Students in these classes
                $this->db->select('a.id_siswa, a.nama, a.nis, b.nama_kelas, c.nama_ruang, d.nama_sesi, e.kelas_id, e.ruang_id, e.sesi_id');
                $this->db->from('cbt_sesi_siswa e');
                $this->db->join('master_siswa a', 'a.id_siswa=e.siswa_id');
                $this->db->join('master_kelas b', 'b.id_kelas=e.kelas_id');
                $this->db->join('cbt_ruang c', 'c.id_ruang=e.ruang_id', 'left');
                $this->db->join('cbt_sesi d', 'd.id_sesi=e.sesi_id', 'left');
                $this->db->where('e.tp_id', $tp->id_tp);
                $this->db->where('e.smt_id', $smt->id_smt);
                $this->db->where_in('e.kelas_id', $kelas_ids);

                if ($filter_ruang) {
                    $this->db->where('e.ruang_id', $filter_ruang);
                }

                $siswas = $this->db->get()->result();

                // Get CBT Siswa status for this jadwal
                $this->db->select('id_siswa, mulai, selesai, status');
                $this->db->where('id_jadwal', $jadwal->id_jadwal);
                $cbt_siswa_list = $this->db->get('cbt_siswa')->result();

                $cbt_siswa_map = [];
                foreach ($cbt_siswa_list as $cs) {
                    $cbt_siswa_map[$cs->id_siswa] = $cs;
                }

                // Calculate time remaining
                $jadwal_selesai = strtotime($jadwal->tgl_selesai);
                $now_time = strtotime($now);

                if ($siswas) {
                    foreach ($siswas as $siswa) {
                        $stats['total_siswa']++;

                        $cbt_data = $cbt_siswa_map[$siswa->id_siswa] ?? null;
                        $status = 'belum_mulai';
                        $status_label = 'Belum Mulai';

                        if ($cbt_data) {
                            if (!empty($cbt_data->selesai)) {
                                $status = 'selesai';
                                $status_label = 'Selesai';
                                $stats['selesai']++;
                                continue; // Skip finished students from the list
                            } elseif (!empty($cbt_data->mulai)) {
                                $status = 'sedang_mengerjakan';
                                $status_label = 'Sedang Mengerjakan';
                                $stats['sedang_mengerjakan']++;
                                continue; // Also skip students currently taking exam
                            } else {
                                $stats['belum_mulai']++;
                            }
                        } else {
                            $stats['belum_mulai']++;
                        }

                        // Calculate time remaining until jadwal ends
                        $time_remaining = $jadwal_selesai - $now_time;
                        $time_remaining_minutes = max(0, floor($time_remaining / 60));
                        $urgency = 'normal';
                        if ($time_remaining_minutes <= 30) {
                            $urgency = 'critical';
                        } elseif ($time_remaining_minutes <= 60) {
                            $urgency = 'warning';
                        }

                        $ruang_id = $siswa->ruang_id ?? null;
                        $pengawas_nama = ($ruang_id && isset($map_pengawas[$ruang_id])) ? $map_pengawas[$ruang_id] : '-';

                        $tgl_mulai = $jadwal->tgl_mulai ? date('H:i', strtotime($jadwal->tgl_mulai)) : '-';
                        $tgl_selesai = $jadwal->tgl_selesai ? date('H:i', strtotime($jadwal->tgl_selesai)) : '-';

                        $result[] = [
                            'id_siswa' => $siswa->id_siswa,
                            'nis' => $siswa->nis,
                            'nama_siswa' => $siswa->nama,
                            'kelas' => $siswa->nama_kelas,
                            'mapel' => $jadwal->nama_mapel,
                            'jadwal' => $tgl_mulai . ' - ' . $tgl_selesai,
                            'sesi' => $siswa->nama_sesi ?? '-',
                            'ruang' => $siswa->nama_ruang ?? '-',
                            'pengawas' => $pengawas_nama,
                            'status' => $status,
                            'status_label' => $status_label,
                            'time_remaining' => $time_remaining_minutes,
                            'urgency' => $urgency
                        ];
                    }
                }
            }

            $this->output_json([
                'data' => $result,
                'stats' => $stats,
                'last_update' => date('H:i:s')
            ]);
        } catch (\Throwable $e) {
            $this->output->set_status_header(200);
            $this->output_json(['data' => [], 'stats' => [], 'error' => $e->getMessage(), 'line' => $e->getLine()]);
        }
    }


    // old
    public function getSiswaKelas()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $kelas = $this->input->get('kelas');
        //$sesi = $this->input->get('sesi');
        $jadwal = $this->input->get('jadwal');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $info = $this->cbt->getJadwalById($jadwal);
        $siswas = $this->cbt->getSiswaByKelas($tp->id_tp, $smt->id_smt, $kelas);

        $durasies = $this->cbt->getDurasiSiswaByJadwal($jadwal);
        $logs = $this->cbt->getLogUjianByJadwal($jadwal);
        $pengawas = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal);
        $ids_pengawas = [];
        foreach ($pengawas as $pws) {
            $ids_pengawas = explode(',', $pws->id_guru ?? '');
        }

        $arrDur = [];
        foreach ($siswas as $ks => $siswa) {
            if ($info->soal_agama != '-' && $info->soal_agama != '0' && $info->soal_agama !== $siswa->agama) {
                unset($siswas[$ks]);
            }

            $dur_siswa = null;
            foreach ($durasies as $durasi) {
                if ($durasi->id_siswa == $siswa->id_siswa) {
                    $mulai = new DateTime($durasi->mulai);
                    $interval = $mulai->diff(new DateTime());
                    $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                    $durasi->ada_waktu = $minutes < $info->durasi_ujian;
                    if ($durasi->lama_ujian == null) {
                        $mins = (strtotime($durasi->selesai ?? '') - strtotime($durasi->mulai ?? '')) / 60;
                        $durasi->lama_ujian = round($mins, 2) . ' m';
                    } else {
                        $lamanya = $durasi->lama_ujian;
                        if (strpos($lamanya, ':') !== false) {
                            $elap = explode(":", $lamanya ?? '');
                            $ed = $elap[2] == "00" ? 0 : 1;
                            $ej = $elap[0] == "00" ? '' : (int) $elap[0] . ' j ';
                            $em = $elap[1] == "00" ? '' : ((int) $elap[1] + $ed) . ' m';

                            $dd = $ej . $em;
                            $durasi->lama_ujian = $dd == '' ? '0 m' : $dd;
                        } else {
                            $durasi->lama_ujian .= 'm';
                        }
                    }

                    $dur_siswa = $durasi;
                }
            }

            $log_siswa = [];
            foreach ($logs as $log) {
                if ($log->id_siswa == $siswa->id_siswa) {
                    $log_siswa[] = $log;
                }
            }
            $arrDur[$siswa->id_siswa] = [
                'dur' => $dur_siswa,
                'log' => $log_siswa
            ];
        }

        $data['siswa'] = array_values($siswas);
        $data['durasi'] = $arrDur;
        $data['info'] = $info;
        $data['pengawas'] = $this->master->getGuruByArrId($ids_pengawas);

        $this->output_json($data);
    }

    // old
    public function getSiswaRuang()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');
        $jadwal = $this->input->get('jadwal');

        $this->db->trans_start();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $info = $this->cbt->getJadwalById($jadwal);
        $siswas = $this->cbt->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $sesi, $info->bank_level);

        $durasies = $this->cbt->getDurasiSiswaByJadwal($jadwal);
        $logs = $this->cbt->getLogUjianByJadwal($jadwal);
        $pengawas = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal, $sesi, $ruang);
        $ids_pengawas = [];
        foreach ($pengawas as $pws) {
            $ids_pengawas = explode(',', $pws->id_guru ?? '');
        }

        $arrDur = [];
        foreach ($siswas as $ks => $siswa) {
            if ($info->soal_agama != '-' && $info->soal_agama != '0' && $info->soal_agama !== $siswa->agama) {
                unset($siswas[$ks]);
            }
            $dur_siswa = null;
            foreach ($durasies as $durasi) {
                if ($durasi->id_siswa == $siswa->id_siswa) {
                    $mulai = new DateTime($durasi->mulai);
                    $interval = $mulai->diff(new DateTime());
                    $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
                    $durasi->ada_waktu = $minutes < $info->durasi_ujian;

                    if ($durasi->lama_ujian == null) {
                        if (!empty($durasi->mulai) && !empty($durasi->selesai)) {
                            $start = strtotime($durasi->mulai ?? '');
                            $end = strtotime($durasi->selesai ?? '');

                            if ($start !== false && $end !== false && $end >= $start) {
                                $mins = ($end - $start) / 60;
                                $durasi->lama_ujian = round($mins, 2) . ' m';
                            } else {
                                $durasi->lama_ujian = '0 m';
                            }
                        } else {
                            $durasi->lama_ujian = '0 m';
                        }
                    } else {
                        $lamanya = $durasi->lama_ujian;
                        if (strpos($lamanya, ':') !== false) {
                            $elap = explode(":", $lamanya ?? '');
                            $ed = $elap[2] == "00" ? 0 : 1;
                            $ej = $elap[0] == "00" ? '' : intval($elap[0]) . ' j ';
                            $em = $elap[1] == "00" ? '' : (intval($elap[1]) + $ed) . ' m';

                            $dd = $ej . $em;
                            $durasi->lama_ujian = $dd == '' ? '0 m' : $dd;
                        } else {
                            $durasi->lama_ujian .= 'm';
                        }
                    }

                    $dur_siswa = $durasi;
                }
            }

            $log_siswa = [];
            foreach ($logs as $log) {
                if ($log->id_siswa == $siswa->id_siswa) {
                    $log_siswa[] = $log;
                }
            }
            $arrDur[$siswa->id_siswa] = [
                'dur' => $dur_siswa,//$this->cbt->getDurasiSiswa($siswa->id_siswa . $jadwal),
                'log' => $log_siswa//$this->cbt->getLogUjian($siswa->id_siswa, $jadwal)
            ];
        }
        $this->db->trans_complete();

        $data['siswa'] = array_values($siswas);
        $data['durasi'] = $arrDur;
        $data['info'] = $info;
        $data['pengawas'] = $this->master->getGuruByArrId($ids_pengawas);
        //$data['pengawas'] = $pengawas;

        $this->output_json($data);
    }

    // new (merge functions)
    public function getStatusSiswa()
    {
        $this->load->model('Tp_model', 'tp');
        $this->load->model('Smt_model', 'smt');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('Cbt_siswa_model', 'cbt_siswa_model');
        $this->load->model('Cbt_sesisiswa_model', 'sesisiswa_model');
        $this->load->model('Cbt_log_model', 'cbt_log_model');

        $tgl = $this->input->get('tgl');
        $kelas = $this->input->get('kelas');
        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');

        $tp = $this->tp->fields('id_tp,tahun,active')->get(['active' => 1]);
        $smt = $this->smt->fields('id_smt,nama_smt,smt,active')->get(['active' => 1]);
        $where['tp_id'] = $tp->id_tp;
        $where['smt_id'] = $smt->id_smt;
        if ($ruang) {
            $where['ruang_id'] = $ruang;
            $where['sesi_id'] = $sesi;
        } else {
            $where['kelas_id'] = $kelas;
        }
        $jadwals = $this->cbt_jadwal_model
            ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis,tgl_mulai,tgl_selesai,durasi_ujian,acak_soal,acak_opsi,hasil_tampil,token,status,ulang,reset_login,rekap,jam_ke,jarak,pelanggaran')
            ->where(['tgl_mulai' => $tgl])
            ->with_jenis()
            ->with_bank([
                'fields' => 'bank_kode,bank_kelas,opsi,jml_soal,tampil_pg,bobot_pg,jml_kompleks,tampil_kompleks,bobot_kompleks,jml_jodohkan,tampil_jodohkan,bobot_jodohkan,jml_isian,tampil_isian,bobot_isian,jml_esai,tampil_esai,bobot_esai,id_tp,id_smt,soal_agama',
                'where' => ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt],
                'with' => [
                    ['relation' => 'mapel', 'fields' => 'kode,nama_mapel'],
                    ['relation' => 'guru', 'fields' => 'kode_guru,nama_guru'],
                ]
            ])
            ->as_array()
            ->get_all();


        $arrKls = [];
        $all_siswa = [];
        $cbtsiswa = null;
        if ($jadwals && count($jadwals) > 0) {
            $ids_jadwal = [];
            $ids_siswa = [];
            foreach ($jadwals as $jad) {
                $ids_jadwal[] = $jad['id_jadwal'];
                $jad['bank']->bank_kelas = $this->maybe_unserialize($jad['bank']->bank_kelas ?? []);
                $kls = $jad['bank']->bank_kelas;
                foreach ($kls as $kl) {
                    if (!in_array($kl->kelas_id, $arrKls)) {
                        $arrKls[] = $kl->kelas_id;
                    }
                }
            }

            $siswas = $this->sesisiswa_model
                ->where($where)
                ->with_siswa(['fields' => 'id_siswa,nama,nis,nisn,agama'])
                ->with_peserta()
                ->with_kelas(['fields' => 'kode_kelas,nama_kelas'])
                ->with_ruang(['fields' => 'kode_ruang,nama_ruang'])
                ->with_sesi(['fields' => 'kode_sesi,nama_sesi'])
                ->as_array()
                ->get_all();

            if ($siswas && count($siswas) > 0) {
                foreach ($siswas as $siswa) {
                    if (!in_array($siswa['siswa_id'], $ids_siswa)) {
                        $ids_siswa[] = $siswa['siswa_id'];
                    }
                }
            }

            if (count($ids_siswa) > 0 && count($ids_jadwal) > 0) {
                $cbtsiswa = $this->cbt_siswa_model
                    ->fields('id_bank,id_cbt_siswa,id_jadwal,id_siswa,lama_ujian,mulai,reset_izin,reset_waktu,selesai,status,pelanggaran')
                    ->where('id_siswa', $ids_siswa)
                    ->where('id_jadwal', $ids_jadwal)
                    //->with_jadwal()
                    ->get_all();

                $logsiswa = $this->cbt_log_model->where('id_siswa', $ids_siswa)
                    ->where('id_jadwal', $ids_jadwal)
                    ->get_all();

                $map_cbt = [];
                $map_log = [];
                if ($cbtsiswa) {
                    foreach ($cbtsiswa as $cbt) {
                        $cbt->pelanggaran = $this->maybe_unserialize($cbt->pelanggaran ?? '{}');
                        $map_cbt[$cbt->id_siswa][] = $cbt;
                    }
                    foreach ($logsiswa as $log) {
                        $map_log[$log->id_siswa][$log->id_jadwal][] = $log;
                    }
                }
                foreach ($siswas as $siswa) {
                    if (in_array($siswa['kelas_id'], $arrKls)) {
                        $siswa['cbtsiswa'] = $map_cbt[$siswa['siswa_id']] ?? [];
                        $siswa['log'] = $map_log[$siswa['siswa_id']] ?? [];
                        $all_siswa[] = $siswa;
                    }
                }
            }
        }

        $this->output_json(
            [
                'jadwals' => $jadwals,
                'sesisiswa' => $all_siswa,
                'is_empty' => $cbtsiswa == null,
                'today' => date('Y-m-d H:i:s')
            ]
        );
    }

    public function applyAction()
    {
        $datas = json_decode($this->input->post('data', true));
        $reset = $datas->reset;
        $force = $datas->force;
        $ulang = $datas->ulang;

        // reset izin
        $data['update_reset'] = true;
        if (count($reset) > 0) {
            $data['reset'] = true;
            foreach ($reset as $rSiswa) {
                $this->db->set('reset_izin', 1);
                $this->db->where_in('id_cbt_siswa', $rSiswa->id_cbt_siswa);
                $this->db->update('cbt_siswa');
            }
        }

        // selesai
        $data['update_selesai'] = true;
        if (count($force) > 0) {
            $this->load->model('Cbt_model', 'cbt');
            $data['selesai'] = true;
            foreach ($force as $fSiswa) {
                foreach ($fSiswa->id_siswa as $ids) {
                    $this->cbt->saveLog($ids, $fSiswa->id_jadwal, 2, 'Menyelesaikan Ujian');
                }

                $this->db->set('selesai', date('Y-m-d H:i:s'));
                $this->db->set('status', 2);
                $this->db->set('reset_waktu', 3);
                $this->db->where_in('id_cbt_siswa', $fSiswa->id_cbt_siswa);
                $this->db->update('cbt_siswa');
            }
        }

        // ulang
        $data['update_ulangi'] = true;
        if (count($ulang) > 0) {
            $data['ulangi'] = true;
            foreach ($ulang as $uSiswa) {
                $this->db->where('id_jadwal', $uSiswa->id_jadwal);
                $this->db->where_in('id_siswa', $uSiswa->id_siswa);
                if ($this->db->delete('log_ujian')) {
                    $this->db->where('id_jadwal', $uSiswa->id_jadwal);
                    $this->db->where_in('id_cbt_siswa', $uSiswa->id_cbt_siswa);
                    if ($this->db->delete('cbt_siswa')) {
                        $this->db->where('id_jadwal', $uSiswa->id_jadwal);
                        $this->db->where_in('id_siswa', $uSiswa->id_siswa);
                        $this->db->delete('cbt_rekap_siswa');
                    }
                }
            }
        }

        // blokir
        $blokir = $datas->blokir ?? [];
        if (count($blokir) > 0) {
            foreach ($blokir as $bSiswa) {
                $this->db->set('status', 9);
                $this->db->where_in('id_cbt_siswa', $bSiswa->id_cbt_siswa);
                $this->db->update('cbt_siswa');
            }
        }

        // unblokir
        $unblokir = $datas->unblokir ?? [];
        if (count($unblokir) > 0) {
            foreach ($unblokir as $ubSiswa) {
                $this->db->set('status', 1);
                $this->db->where_in('id_cbt_siswa', $ubSiswa->id_cbt_siswa);
                $this->db->update('cbt_siswa');
            }
        }
        $this->output_json($data);
    }

    public function resetTimer()
    {
        $id_cbt_siswa = $this->input->post('id_cbt_siswa', true);
        $reset = $this->input->post('reset', true);
        if ($reset == '1') {
            $this->db->set('lama_ujian', '00:00:00');
        } elseif ($reset == '3') {
            $this->db->set('lama_ujian', '00:00:00');
        }
        $this->db->set('reset_waktu', $reset);
        $this->db->where('id_cbt_siswa', $id_cbt_siswa);
        $update = $this->db->update('cbt_siswa');

        $data['status'] = $update;
        $this->output_json($data);
    }

}
