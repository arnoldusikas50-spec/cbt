<?php


class Cbtanalisis extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if ( !$this->auth->is_admin() && !$this->auth->in_group('panitia') && !$this->auth->in_group('guru')){
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        //$this->load->model('Master_model', 'master');
        //$this->load->model('Log_model', 'logging');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function index()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = [
            'user' 			=> $user,
            'judul' => 'Analisis Hasil Ujian',
            'subjudul' => 'Analisis',
            'profile'		=> null,
            'setting'		=> $this->dashboard->getSetting()
        ];

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $jadwals = $this->cbt->getAllJadwalByJenis($tp->id_tp, $smt->id_smt);
        foreach ($jadwals as $tgl => $mapels) {
            foreach ($mapels as $id_mapel => $jdwls) {
                foreach ($jdwls as $index => $jdwl) {
                    $jdwl->bank_kelas = $this->maybe_unserialize($jdwl->bank_kelas);
                }
            }
        }
        $data['jadwal'] = $jadwals;
        $data['jenis_ujian'] = $this->cbt->getAllJenisUjian();
        $data['mapel'] = $this->dropdown->getAllMapel();
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['guru'] = $this->dropdown->getAllGuru();

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/analisis/data', $data);
        } elseif ($this->auth->in_group('panitia')) {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/analisis/data', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/analisis/data', $data);
        }
    }

    public function analisis()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Rekap_jadwal_model', 'rekap_jadwal');
        $this->load->model('Rekap_siswa_model', 'rekap_siswa');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Analisis Soal',
            'subjudul' => 'Analisis Butir Soal',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp_smt = $this->getTpSmt();
        $tp = $tp_smt->tp_active;
        $smt = $tp_smt->smt_active;
        $data['tp'] = $tp_smt->tp;
        $data['smt'] = $tp_smt->smt;
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $jadwal = $this->input->get('id_jadwal');

        $data['is_rekap'] = true;
        $data['jadwal_selected'] = $jadwal;

        $rekap = $this->rekap_jadwal
            ->where(['id_jadwal'=>$jadwal])
            ->get();

        if (!$rekap) {
            redirect('cbtanalisis');
        }

        $rekap_siswa = $this->rekap_siswa
            ->where(['id_jadwal'=>$jadwal])
            ->fields('jawaban')
            ->with_siswa(['fields'=>'id_siswa,nama,nis,agama'])
            ->get_all();

        $rekap->jadwal = $this->maybe_unserialize($rekap->jadwal);
        if (!isset($rekap->jadwal->tgl_mulai)) {
            $rekap->jadwal->tgl_mulai = $rekap->tgl_mulai;
        }
        $rekap->jadwal->guru = $rekap->nama_guru;
        $rekap->jadwal->tp = $rekap->tp;
        $rekap->jadwal->smt = $rekap->smt;
        $rekap->jadwal->pengawas = $this->maybe_unserialize($rekap->pengawas);
        $rekap->jadwal->nama_mapel = $rekap->nama_mapel;
        $rekap->jadwal->kode_mapel = $rekap->kode_mapel;
        $rekap->jadwal->kode_jenis = $rekap->kode_jenis;

        $rekap->bank_soal = $this->maybe_unserialize($rekap->bank_soal);
        $rekap->kelas_bank = $this->maybe_unserialize($rekap->kelas_bank);
        $rekap->soal_pg = $this->maybe_unserialize($rekap->soal_pg);
        $rekap->soal_kompleks = $this->maybe_unserialize($rekap->soal_kompleks);
        $rekap->soal_jodohkan = $this->maybe_unserialize($rekap->soal_jodohkan);
        $rekap->soal_isian = $this->maybe_unserialize($rekap->soal_isian);
        $rekap->soal_esai = $this->maybe_unserialize($rekap->soal_esai);

        $jawabanSiswa = [];
        $jawabanSoal = [];
        if ($rekap_siswa) {
            foreach ($rekap_siswa as $ks=>$rekapsiswa) {
                if ($rekap->bank_soal->soal_agama != '-' && $rekap->bank_soal->soal_agama != '0'
                    && $rekap->bank_soal->soal_agama !== $rekapsiswa->siswa->agama) {
                    unset($rekap_siswa->{$ks});
                } else {
                    $jawabans = $this->maybe_unserialize($rekapsiswa->jawaban);
                    $answers = [];
                    foreach ($jawabans as $jawaban) {
                        $jawab_siswa = $jawaban->jawaban_siswa;
                        $answers[$jawaban->id_soal] = $jawab_siswa;
                        if ($jawab_siswa == 'A') {
                            $jawabanSoal[$jawaban->id_soal]['jawab_a'][] = $jawab_siswa;
                        } elseif ($jawab_siswa == 'B') {
                            $jawabanSoal[$jawaban->id_soal]['jawab_b'][] = $jawab_siswa;
                        } elseif ($jawab_siswa == 'C') {
                            $jawabanSoal[$jawaban->id_soal]['jawab_c'][] = $jawab_siswa;
                        } elseif ($jawab_siswa == 'D') {
                            $jawabanSoal[$jawaban->id_soal]['jawab_d'][] = $jawab_siswa;
                        } elseif ($jawab_siswa == 'E') {
                            $jawabanSoal[$jawaban->id_soal]['jawab_e'][] = $jawab_siswa;
                        }
                    }
                    $jawabanSiswa[] = $answers;
                }

            }
        }
        $data['siswa'] = count((array)$rekap_siswa);

        $kunci = [];
        if (isset($rekap->soal_pg)) {
            foreach ($rekap->soal_pg as $soal_pg) {
                $kunci[$soal_pg->id_soal] = $soal_pg->jawaban;
            }
        }

        $normalizedSiswa = $this->normalizeData($jawabanSiswa, $kunci);
        $hasilAnalisis = $this->analyzeData($normalizedSiswa, $kunci, $rekap->bank_soal->opsi);
        $data['analisis'] = $hasilAnalisis;
        $data['soal_pg'] = array_filter($rekap->soal_pg, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $data['soal_kompleks'] = array_filter($rekap->soal_kompleks, function ($soal) {
                return $soal->tampilkan == '1';
            });
        $data['soal_jodohkan'] = array_filter($rekap->soal_jodohkan, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $data['soal_isian'] = array_filter($rekap->soal_isian, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $data['soal_esai'] = array_filter($rekap->soal_esai, function ($soal) {
            return $soal->tampilkan == '1';
        });
        $data['jawab_siswa'] = $jawabanSoal;
        $data['info'] = $rekap->bank_soal;
        $data['jadwal'] = $rekap->jadwal;

        $arrKelas = [];
        foreach ($rekap->kelas_bank as $kls) {
            if (isset($kls->kode_kelas)) {
                $arrKelas[] = $kls->kode_kelas;
            }
        }
        $data['kelas'] = implode(', ', $arrKelas);

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/analisis/data_analisis', $data);
        } elseif ($this->auth->in_group('panitia')) {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/analisis/data_analisis', $data);
        } else {
            //$guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $nguru[$guru->id_guru] = $guru->nama_guru;
            $data['guru'] = $guru;
            $this->render_view(2, 'cbt/analisis/data_analisis', $data);
        }
    }

    private function pearson($x, $y){
        $cx = count($x);
        $cy = count($y);
        if ($cx == 0 || $cy == 0) return -1;
        if ($cx < $cy) {
            $d = $cy - $cx;
            for ($i = 0; $i < $d; $i++) {
                array_pop($y);
            }
        }
        if ($cx > $cy) {
            $d = $cx - $cy;
            for ($i = 0; $i < $d; $i++) {
                array_pop($x);
            }
        }

        if (count($x) !== count($y)) {
            return -1;
        }
        $x = array_values($x);
        $y = array_values($y);
        $xs = array_sum($x) / count($x);
        $ys = array_sum($y) / count($y);
        $a = 0;
        $bx = 0;
        $by = 0;
        for ($i = 0; $i < count($x); $i++) {
            $xr = $x[$i] - $xs;
            $yr = $y[$i] - $ys;
            $a += $xr * $yr;
            $bx += pow($xr, 2);
            $by += pow($yr, 2);
        }
        $b = sqrt($bx * $by);
        $ret = -1;
        if ($b > 0) {
            $ret = $a / $b;
        }
        return $ret;
    }

    public function getJadwalUjianByJadwal()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $jadwal = $this->input->get('jadwal');
        $tp = $this->input->get('thn');
        $smt = $this->input->get('smt');
        $info = $this->cbt->getJadwalById($jadwal);

        $kelas = $this->maybe_unserialize($info->bank_kelas ?? '[]');
        $kelases = [];
        foreach ($kelas as $key => $value) {
            $kelases[$value->kelas_id] = $this->dropdown->getNamaKelasById($info->id_tp, $info->id_smt, $value->kelas_id);
        }

        $this->output_json($kelases);
    }

    // Fungsi korelasi Pearson
    private function calculatePearsonCorrelation($x, $y)
    {
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] ** 2;
            $sumY2 += $y[$i] ** 2;
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denominator = sqrt(($n * $sumX2 - $sumX ** 2) * ($n * $sumY2 - $sumY ** 2));

        return $denominator == 0 ? 0 : $numerator / $denominator;
    }

}

