<?php


class Cbtnilai extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru') && !$this->auth->in_group('panitia') && $this->session->userdata('role') != '4') {
            show_error('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['form_validation']); // Load Library Ignited-Datatables
        //$this->load->library('upload');
        //$this->load->model('Master_model', 'master');
        $this->form_validation->set_error_delimiters('', '');
    }

    /**
     * @throws JsonException
     */
    public function index()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Hasil Ujian Siswa',
            'subjudul' => 'Nilai Siswa',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $kelas_selected = $this->input->get('id_kelas');
        $jadwal_selected = $this->input->get('id_jadwal');
        $data['kelas_selected'] = $kelas_selected;
        $data['jadwal_selected'] = $jadwal_selected;

        $ya = $this->input->get('ya');
        $yb = $this->input->get('yb');
        $xa = $this->input->get('xa');
        $xb = $this->input->get('xb');

        if ($this->auth->in_group('guru')) {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $where = ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt, 'bank_guru_id' => $guru->id_guru];
            $id_guru = $guru->id_guru;
        } else {
            $where = ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt];
            $id_guru = null;
        }

        if ($jadwal_selected !== null) {
            if ($ya != null) {
                $convert = ['ya' => $ya, 'yb' => $yb, 'xa' => $xa, 'xb' => $xb];
                $data['convert'] = $convert;
            }

            $jadwals = $this->cbt_jadwal_model
                ->fields('id_jadwal,id_tp,id_smt,id_bank,id_jenis')
                ->where(['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt])
                ->with_bank([
                    'where' => $where,
                    'with' => [
                        ['relation' => 'mapel', 'fields' => 'kode,nama_mapel']
                    ]
                ])
                ->get_all();

            $jdwl = [];
            foreach ($jadwals as $jadwal) {
                $kls = $this->maybe_unserialize($jadwal['bank']->bank_kelas ?? []);
                foreach ($kls as $kl) {
                    if ($kl->kelas_id == $kelas_selected) {
                        $jdwl[$jadwal['id_jadwal']] = $jadwal['bank']->mapel->kode . ' (' . $jadwal['bank']->bank_kode . ')';
                    }
                }
            }

            $data['jadwal'] = $jdwl;
        } else {
            $data['jadwal'] = [];
            $data['nilai'] = [];
        }
        $data['tp_jadwal'] = $tp->tahun;

        if ($this->auth->is_admin()) {
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $this->render_view(1, 'cbt/nilai/data_nilai', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/nilai/data_nilai', $data);
        } else {
            $jadwals = $this->cbt->getJadwalGuru($tp->id_tp, $smt->id_smt, $id_guru);
            $arrKls = [];
            foreach ($jadwals as $jad) {
                $kls = $this->maybe_unserialize($jad->bank_kelas ?? []);
                foreach ($kls as $kl) {
                    $arrKls[] = $kl->kelas_id;
                }
            }
            $data['kelas'] = count($arrKls) > 0 ? $this->dropdown->getNamaKelasByArrId($tp->id_tp, $smt->id_smt, $arrKls) : [];
            $this->render_view(2, 'cbt/nilai/data_nilai', $data);
        }
    }

    public function get_nilai_siswa()
    {
        $this->load->model('Master_kelas_model', 'master_kelas_model');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');

        $kelas_selected = $this->input->post('id_kelas');
        $jadwal_selected = $this->input->post('id_jadwal');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['tp_jadwal'] = $tp->tahun;

        try {
            $jadwal = $this->olah_nilai(1, $tp->id_tp, $smt->id_smt, $kelas_selected, $jadwal_selected, true);
            if ($jadwal && is_object($jadwal)) {
                unset($jadwal->soal_kompleks, $jadwal->soal_jodohkan, $jadwal->soal_isian, $jadwal->soal_esai);
            }

            $data['kelas'] = $this->master_kelas_model->where(['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt])->as_dropdown('nama_kelas')->get_all();
            $data['jadwal'] = $jadwal;
            $data['kelas_selected'] = $kelas_selected;
            $data['jadwal_selected'] = $jadwal_selected;

            $data['success'] = true;
            $this->output_json($data);
        } catch (\Throwable $e) {
            $this->output_json([
                'success' => false,
                'message' => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
            ]);
        }
    }

    public function save_pengurangan()
    {
        $id_jadwal = $this->input->post('id_jadwal', true);
        $siswas = json_decode($this->input->post('id_siswa', true), true);
        $nilai = json_decode($this->input->post('arr_point'));
        $data['post'] = $nilai;

        $id_siswa = array_column($siswas, 'id_siswa');
        $allCbtSiswa = $this->db->select('id_cbt_siswa,id_siswa,id_jadwal,pelanggaran')
            ->from('cbt_siswa')
            ->where_in('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()
            ->result();

        $mapCbt = [];
        foreach ($allCbtSiswa as $siswa) {
            $mapCbt[$siswa->id_siswa] = $siswa;
        }

        $allRekapSiswa = $this->db->select('id_rekap_siswa,id_siswa,id_jadwal,pelanggaran')
            ->from('cbt_rekap_siswa')
            ->where_in('id_siswa', $id_siswa)
            ->where('id_jadwal', $id_jadwal)
            ->get()
            ->result();

        $mapRekap = [];
        foreach ($allRekapSiswa as $siswa) {
            $mapRekap[$siswa->id_siswa] = $siswa;
        }

        $updateCbt = [];
        $updateRekap = [];
        foreach ($nilai as $nil) {
            $id_siswa = $nil->id_siswa;
            if (isset($mapCbt[$id_siswa])) {
                $cbt = $mapCbt[$id_siswa];
                $cbt->pelanggaran = json_encode($nil->pelanggaran);
                $updateCbt[] = $cbt;
            }

            if (isset($mapRekap[$id_siswa])) {
                $rekap = $mapRekap[$id_siswa];
                $rekap->pelanggaran = json_encode($nil->pelanggaran);
                $updateRekap[] = $rekap;
            }
        }

        $successCbt = false;
        $successRekap = false;
        if (count($updateCbt) > 0) {
            $successCbt = $this->db->update_batch('cbt_siswa', $updateCbt, 'id_cbt_siswa');
        }

        if (count($updateRekap) > 0) {
            $successRekap = $this->db->update_batch('cbt_rekap_siswa', $updateRekap, 'id_rekap_siswa');
        }
        $data['cbt'] = $updateCbt;
        $data['rekap'] = $updateRekap;
        $data['success'] = $successCbt || $successRekap;
        $this->output_json($data);
    }

    public function koreksi()
    {
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Koreksi Hasil Siswa',
            'subjudul' => 'Hasil Siswa',
            'setting' => $this->dashboard->getSetting(),
        ];

        $id_siswa = $this->input->get('id_siswa');
        $id_jadwal = $this->input->get('id_jadwal');

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
        $data['id_bank'] = $jadwal->id_bank;
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

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/nilai/koreksi', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/nilai/koreksi', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->render_view(2, 'cbt/nilai/koreksi', $data);
        }
    }

    public function inputEssai()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Master_kelas_model', 'master_kelas_model');
        $this->load->model('Cbt_jadwal_model', 'cbt_jadwal_model');
        $this->load->model('cbt_sesisiswa_model', 'sesisiswa_model');

        $kelas_selected = $this->input->get('id_kelas');
        $jadwal_selected = $this->input->get('id_jadwal');

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Input Nilai Manual',
            'subjudul' => '',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $data['kelas'] = $this->master_kelas_model
            ->where(['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt, 'id_kelas' => $kelas_selected])
            ->get();
        $data['kelas_selected'] = $kelas_selected;
        $data['jadwal_selected'] = $jadwal_selected;
        $jadwals = $this->olah_nilai(1, $tp->id_tp, $smt->id_smt, $kelas_selected, $jadwal_selected);
        $data['jadwal'] = $jadwals;

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/nilai/input_nilai', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/nilai/input_nilai', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $this->render_view(2, 'cbt/nilai/input_nilai', $data);
        }
    }
}
