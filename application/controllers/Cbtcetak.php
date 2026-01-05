<?php


class Cbtcetak extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && (!$this->auth->in_group('panitia') && $this->session->userdata('role') != '4') && !$this->auth->in_group('guru')) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->library('upload');
        if ($this->config->item('migration_enabled')) {
            $this->load->library('migration');
            if ($this->migration->current() === FALSE) {
                show_error($this->migration->error_string());
            }
        }
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_error_delimiters('', '');

        $this->load->model('Dashboard_model', 'dashboard');
        if ($this->auth->in_group('panitia')) {
            $user = $this->auth->user();
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            if ($panitia && $panitia->jabatan === 'Pengawas') {
                $allowed = [
                    'index',
                    'beritaAcara',
                    'paktaIntegritas',
                    'saveBeritaAcara',
                    'savePaktaIntegritas',
                    'getSiswaRuang',
                    'getJadwalMapel',
                    'getSiswaKelas'
                ];
                $method = $this->router->fetch_method(); // method name is case-insensitive in CI usually but returns as defined? fetch_method returns lower case usually or exact? CodeIgniter 3 returns exact.
                $method_lower = strtolower($method);
                $allowed_lower = array_map('strtolower', $allowed);

                if (!in_array($method_lower, $allowed_lower)) {
                    // Redirect to allowed page or show error
                    redirect('cbtcetak');
                }
            }
        }
    }

    private function getDistinctRuang($tp, $smt, $arrKelas)
    {
        $this->db->distinct();
        $this->db->select('a.ruang_id, a.sesi_id, b.kode_ruang, b.nama_ruang, c.kode_sesi, c.nama_sesi, c.waktu_mulai, c.waktu_akhir, c.istirahat');
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

        $sesis = [];
        if ($result) {
            foreach ($result as $row) {
                $sesis[$row->sesi_id] = $row;
            }
        }
        return $sesis;
    }

    public function index()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Cetak Data Penilaian',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['kop'] = $this->cbt->getSettingKopAbsensi();
        $data['is_admin'] = $this->auth->is_admin() || $this->auth->in_group('panitia') || $this->session->userdata('role') == '4';

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/cetak/data', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/cetak/data', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $pengawas = $this->cbt->getPengawasHariIni(date('Y-m-d'));
            $data['pengawas'] = $pengawas;
            $ids_pengawas = [];
            foreach ($pengawas as $pws) {
                $ids = explode(',', $pws->id_guru ?? '');
                foreach ($ids as $id) {
                    if (!in_array($id, $ids_pengawas) && $id != '') {
                        $ids_pengawas[] = $id;
                    }
                }
            }
            $data['ids_pengawas'] = $ids_pengawas;
            $this->render_view(2, 'cbt/cetak/data', $data);
        }
    }

    public function kartuPeserta()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Rapor_model', 'rapor');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Cetak Kartu Peserta',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['kartu'] = $this->cbt->getSettingKartu();
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruang'] = $this->dropdown->getAllRuang();
        $data['setting_rapor'] = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/cetak/kartu', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/cetak/kartu', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/cetak/kartu', $data);
        }
    }

    public function kartuMeja()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Rapor_model', 'rapor');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Cetak Kartu Meja',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['kartu_meja'] = $this->cbt->getSettingKartu();
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruang'] = $this->dropdown->getAllRuang();
        $data['setting_rapor'] = $this->rapor->getRaporSetting($tp->id_tp, $smt->id_smt);

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/cetak/kartumeja', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/cetak/kartumeja', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/cetak/kartumeja', $data);
        }
    }

    function uploadFile($logo)
    {
        if (isset($_FILES["logo"]["name"])) {
            //$newName = $_FILES["logo"]["name"].".".$_FILES['logo']['type'];

            $config['upload_path'] = './uploads/settings/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
            $config['overwrite'] = true;
            $config['file_name'] = $logo;

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('logo')) {
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            } else {
                $result = $this->upload->data();
                $data['src'] = base_url() . 'uploads/settings/' . $result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;
            }
            $data['type'] = $_FILES['logo']['type'];
            $data['size'] = $_FILES['logo']['size'];
        } else {
            $data['src'] = '';
        }
        $this->output_json($data);
    }

    function deleteFile()
    {
        $src = $this->input->post('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        if (unlink($file_name)) {
            echo 'File Delete Successfully';
        }
    }

    public function saveKartu()
    {
        $header_1 = $this->input->post('header_1', true);
        $header_2 = $this->input->post('header_2', true);
        $header_3 = $this->input->post('header_3', true);
        $header_4 = $this->input->post('header_4', true);
        $tanggal = $this->input->post('tanggal', true);

        $insert = [
            'id_set_kartu' => 123456,
            'header_1' => $header_1,
            'header_2' => $header_2,
            'header_3' => $header_3,
            'header_4' => $header_4,
            'tanggal' => $tanggal,
        ];

        $update = $this->db->replace('cbt_kop_kartu', $insert);
        $this->output_json($update);
    }

    public function saveKartuMeja()
    {
        $header_1 = $this->input->post('header_1', true);
        $header_2 = $this->input->post('header_2', true);
        $header_3 = $this->input->post('header_3', true);
        $header_4 = $this->input->post('header_4', true);
        $tanggal = $this->input->post('tanggal', true);

        $insert = [
            'id_set_kartu' => 123456,
            'header_1' => $header_1,
            'header_2' => $header_2,
            'header_3' => $header_3,
            'header_4' => $header_4,
            'tanggal' => $tanggal,
        ];

        $update = $this->db->replace('cbt_kop_kartu', $insert);
        $this->output_json($update);
    }

    public function getSiswaKelas()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');

        $sesi = $this->input->get('sesi');
        $jadwal = $this->input->get('jadwal');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $kelas = $this->input->get('kelas');
        if ($kelas == 'all') {
            $ikelas = $this->kelas->getIdKelas($tp->id_tp, $smt->id_smt);
            $kelas = $ikelas;
        } else {
            $ikelas = $this->master->getKelasById($kelas);
        }

        $s = !$sesi ? null : $sesi;
        $isesi = null;
        if ($s != null) {
            $isesi = $this->cbt->getSesiById($s);
        }

        $ijadwal = null;
        $arrKls = [];
        $tgl_sama = [];
        if ($jadwal != null && $jadwal != 'null') {
            $ijadwal = $this->cbt->getJadwalById($jadwal, $s);
            $ijadwal->bank_kelas = $this->maybe_unserialize($ijadwal->bank_kelas ?? []);
            foreach ($ijadwal->bank_kelas as $kl) {
                if (!in_array($kl->kelas_id, $arrKls)) {
                    $arrKls[] = $kl->kelas_id;
                }
            }
            $tgl_sama = $this->cbt->getJadwalByTanggal($ijadwal->tgl_mulai);
        }

        $pengawass = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jadwal, $sesi);
        $pengawas = [];
        foreach ($pengawass as $p) {
            if (count(explode(",", $p->id_guru ?? '')) > 0) {
                $pengawas = $this->master->getGuruByArrId(explode(",", $p->id_guru ?? ''));
            }
        }

        $data['siswa'] = [];
        $siswas = $this->cbt->getRuangSiswaByKelas($tp->id_tp, $smt->id_smt, $kelas, $s);
        foreach ($siswas as $siswa) {
            $siswa->foto = file_exists($siswa->foto) ? $siswa->foto : 'assets/img/siswa.png';
            if ($ijadwal) {
                $sig_file = 'uploads/ttd_siswa/' . $siswa->id_siswa . '_' . $ijadwal->id_bank . '_' . $ijadwal->id_jadwal . '.png';
                if (file_exists($sig_file)) {
                    $siswa->signature = base_url() . $sig_file;
                } else {
                    $siswa->signature = null;
                }
            }
            $data['siswa'][] = $siswa;
        }

        $data['info'] = ['kelas' => $ikelas, 'sesi' => $isesi, 'jadwal' => $ijadwal, 'pengawas' => $pengawas];

        $sesis = [];
        if (count($arrKls) > 0) {
            $sesis = $sesis = $this->db->get('cbt_sesi')->result();
        }
        $data['all_sesi'] = array_values($sesis);
        $data['all_tgl'] = $tgl_sama;

        $pakta_integritas = $this->cbt->getPaktaIntegritas($jadwal, null, $s);
        $data['info']['pakta_integritas'] = $pakta_integritas;

        $this->output_json($data);
    }

    public function getSiswaRuang()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');
        $jadwal = $this->input->get('jadwal');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $pengawass = $this->cbt->getPengawas($tp->id_tp . $smt->id_smt . $jadwal . $ruang . $sesi);
        $pengawas = [];
        if ($pengawass != null && count(explode(",", $pengawass->id_guru ?? '')) > 0) {
            $pengawas = $this->master->getGuruByArrId(explode(",", $pengawass->id_guru ?? ''));
        }

        $iruang = $this->cbt->getRuangById($ruang);

        $s = $sesi == "null" ? null : $sesi;
        $isesi = null;
        if ($s != null) {
            $isesi = $this->cbt->getSesiById($s);
        }

        $ijadwal = null;
        $tgl_sama = [];
        $arrKls = [];
        if ($jadwal != null && $jadwal != 'null') {
            $ijadwal = $this->cbt->getJadwalById($jadwal, $s);
            if ($ijadwal) {
                $tgl_sama = $this->cbt->getJadwalByTanggal($ijadwal->tgl_mulai);
                foreach ($tgl_sama as $jdwl) {
                    $bank_kelas = $this->maybe_unserialize($jdwl->bank_kelas ?? []);
                    $jdwl->bank_kelas = array_map('strval', array_column($bank_kelas, 'kelas_id'));
                    //$jdwl->pengawas = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $jdwl->id_jadwal);
                }

                $ijadwal->bank_kelas = $this->maybe_unserialize($ijadwal->bank_kelas ?? []);
                foreach ($ijadwal->bank_kelas as $kl) {
                    if (!in_array($kl->kelas_id, $arrKls)) {
                        $arrKls[] = $kl->kelas_id;
                    }
                }
            }
            //$ijadwal->pengawas = $pengawass;
        }

        $sesis = [];
        $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKls);
        if (count($arrKls) > 0) {
            $sesis = $this->db->get('cbt_sesi')->result();
        }
        $data['all_sesi'] = $sesis;
        $data['all_tgl'] = $tgl_sama;
        $data['all_ruang'] = array_values($ruangs);
        $data['all_pengawas'] = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt, null, null);
        $data['all_guru'] = $this->dropdown->getAllGuru();

        $siswa_data = $this->cbt->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $s);

        // Fix foto path
        foreach ($siswa_data as $siswa) {
            $siswa->foto = file_exists($siswa->foto) ? $siswa->foto : 'assets/img/siswa.png';
            if ($ijadwal) {
                // Format: id_siswa + id_bank + id_jadwal
                // Check format in Cbt_model log or Cbtsiswa controller? 
                // Based on user memory: id_siswa_id_bank_id_jadwal.png
                $sig_file = 'uploads/ttd_siswa/' . $siswa->id_siswa . '_' . $ijadwal->id_bank . '_' . $ijadwal->id_jadwal . '.png';
                if (file_exists($sig_file)) {
                    $siswa->signature = base_url() . $sig_file;
                } else {
                    $siswa->signature = null;
                }
            }
        }

        $berita_acara = $this->cbt->getBeritaAcara($jadwal, $ruang, $s);
        $pakta_integritas = $this->cbt->getPaktaIntegritas($jadwal, $ruang, $s);

        $data['siswa'] = $siswa_data;
        $data['info'] = [
            'ruang' => $iruang,
            'sesi' => $isesi,
            'jadwal' => $ijadwal,
            'pengawas' => $pengawas,
            'berita_acara' => $berita_acara,
            'pakta_integritas' => $pakta_integritas
        ];
        $this->output_json($data);
    }

    public function saveBeritaAcara()
    {
        $input = $this->input->post(null, true);
        $update = $this->cbt->saveBeritaAcara($input);
        $this->output_json($update);
    }

    public function savePaktaIntegritas()
    {
        $input = $this->input->post(null, true);
        $update = $this->cbt->savePaktaIntegritas($input);
        $this->output_json($update);
    }


    public function saveKop()
    {
        $header_1 = $this->input->post('header_1', true);
        $header_2 = $this->input->post('header_2', true);
        $header_3 = $this->input->post('header_3', true);
        $header_4 = $this->input->post('header_4', true);
        $proktor = $this->input->post('proktor', true);
        $pengawas_1 = $this->input->post('pengawas_1', true);
        $pengawas_2 = $this->input->post('pengawas_2', true);

        $insert = [
            'id_kop' => 123456,
            'header_1' => $header_1,
            'header_2' => $header_2,
            'header_3' => $header_3,
            'header_4' => $header_4,
            'proktor' => $proktor,
            'pengawas_1' => $pengawas_1,
            'pengawas_2' => $pengawas_2,
        ];

        $update = $this->db->replace('cbt_kop_absensi', $insert);
        $this->output_json($update);
    }

    public function absenPeserta()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Cetak Daftar Kehadiran',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['jadwal'] = $this->dropdown->getAllJadwal($tp->id_tp, $smt->id_smt);
        $data['mapel'] = $this->dropdown->getAllJadwalMapel($tp->id_tp, $smt->id_smt);
        $data['mapel_jadwal'] = $this->dropdown->getAllMapelJadwal($tp->id_tp, $smt->id_smt);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruang'] = $this->dropdown->getAllRuang();
        $data['sesi'] = $this->dropdown->getAllSesi();
        $data['kop'] = $this->cbt->getSettingKopAbsensi();

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/cetak/absen', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/cetak/absen', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/cetak/absen', $data);
        }
    }

    public function getJadwalMapel()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $mapel = $this->input->get('mapel');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $arr_jadwal = $this->cbt->getDataJadwalByMapel($tp->id_tp, $smt->id_smt, $mapel);
        $arr_id_kelas = [];
        $arr_id_jadwal = [];
        $arr_tgl_jadwal = [];
        foreach ($arr_jadwal as $jadwal) {
            $arr_id_jadwal[] = $jadwal->id_jadwal;
            $arr_tgl_jadwal[] = $jadwal->tgl_mulai;
            $jadwal->bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? []);
            foreach ($jadwal->bank_kelas as $kls) {
                $arr_id_kelas[] = $kls->kelas_id;
            }
        }

        $ruangs = $this->cbt->getRuangSesi($tp->id_tp, $smt->id_smt);
        $pengawass = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $arr_id_jadwal);
        $pengawas = [];
        foreach ($pengawass as $p) {
            if (count(explode(",", $p->id_guru ?? '')) > 0) {
                $guru = $this->master->getGuruByArrId(explode(",", $p->id_guru ?? ''));
                $pengawas[] = [
                    'ruang' => $p->id_ruang,
                    'sesi' => $p->id_sesi,
                    'jadwal' => $p->id_jadwal,
                    'guru' => $guru
                ];
            }
        }

        $kopAbsensi = $this->cbt->getSettingKopAbsensi();
        $kopBerita = $this->cbt->getSettingKopBeritaAcara();

        $siswas = $this->cbt->getSiswaByKelas($tp->id_tp, $smt->id_smt, $arr_id_kelas);

        foreach ($siswas as $siswa) {
            $siswa->signatures = [];
            foreach ($arr_jadwal as $jdwl) {
                $sig_file = 'uploads/ttd_siswa/' . $siswa->id_siswa . '_' . $jdwl->id_bank . '_' . $jdwl->id_jadwal . '.png';
                if (file_exists($sig_file)) {
                    $siswa->signatures[$jdwl->id_jadwal] = base_url() . $sig_file;
                }
            }
        }

        $all_ruang = $this->db->get('cbt_ruang')->result();
        $all_sesi = $this->db->get('cbt_sesi')->result();

        $sesis = [];
        if (count($arr_id_kelas) > 0) {
            $sesis = $all_sesi;
        }
        $data['all_sesi'] = array_values($sesis);

        $tgl_sama = [];
        if (count($arr_tgl_jadwal) > 0) {
            $res_jadwal = $this->cbt->getJadwalByTanggal($arr_tgl_jadwal);
            if ($res_jadwal) {
                foreach ($res_jadwal as $jadwal) {
                    $tgl_sama[$jadwal->tgl_mulai][] = $jadwal;
                }
            }
        }
        $data['all_tgl'] = $tgl_sama;

        $data['ruang'] = $all_ruang;
        $data['sesi'] = $all_sesi;
        $data['siswa'] = $siswas;
        $data['ruang_kelas'] = $ruangs;
        $data['kop'] = ['kop_absensi' => $kopAbsensi, 'kop_berita' => $kopBerita];
        $data['pengawas'] = $pengawas;
        $data['jadwal'] = $arr_jadwal;
        $this->output_json($data);
    }

    public function getAllJadwal()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $mapel = $this->input->get('mapel');

        $arr_jadwal = $this->cbt->getDataJadwalByMapel($tp->id_tp, $smt->id_smt, $mapel);
        $arr_id_kelas = [];
        $arr_id_jadwal = [];

        $group_jadwal = [];
        foreach ($arr_jadwal as $jadwal) {
            $arr_id_jadwal[] = $jadwal->id_jadwal;
            $bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? []);
            $jadwal->bank_kelas = array_map('strval', array_column($bank_kelas, 'kelas_id'));

            foreach ($jadwal->bank_kelas as $kl) {
                if (!in_array($kl, $arr_id_kelas)) {
                    $arr_id_kelas[] = $kl;
                }
            }
            $group_jadwal[$jadwal->tgl_mulai][] = $jadwal;
        }

        $pengawass = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $arr_id_jadwal);
        if (count($pengawass) > 0) {
            foreach ($pengawass as $p) {
                if ($p != null && count(explode(",", $p->id_guru ?? '')) > 0) {
                    $p->pengawas = $this->master->getGuruByArrId(explode(",", $p->id_guru ?? ''));
                }
            }
        }

        $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arr_id_kelas);

        $data['all_ruang'] = array_values($ruangs);
        $data['all_pengawas'] = $pengawass;
        $data['all_guru'] = $this->dropdown->getAllGuru();
        $data['all_tgl'] = $group_jadwal;
        $data['siswa'] = $this->cbt->getRuangSiswaByKelas($tp->id_tp, $smt->id_smt, $arr_id_kelas);

        foreach ($data['siswa'] as $siswa) {
            $siswa->signatures = [];
            foreach ($arr_jadwal as $jdwl) {
                $sig_file = 'uploads/ttd_siswa/' . $siswa->id_siswa . '_' . $jdwl->id_bank . '_' . $jdwl->id_jadwal . '.png';
                if (file_exists($sig_file)) {
                    $siswa->signatures[$jdwl->id_jadwal] = base_url() . $sig_file;
                }
            }
        }

        $kopAbsensi = $this->cbt->getSettingKopAbsensi();
        $kopBerita = $this->cbt->getSettingKopBeritaAcara();
        $data['kop'] = ['kop_absensi' => $kopAbsensi, 'kop_berita' => $kopBerita];
        $this->output_json($data);

    }

    public function beritaAcara()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Cetak Berita Acara',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['jadwal'] = $this->dropdown->getAllJadwal($tp->id_tp, $smt->id_smt);
        $data['mapel'] = $this->dropdown->getAllJadwalMapel($tp->id_tp, $smt->id_smt);
        $data['mapel_jadwal'] = $this->dropdown->getAllMapelJadwal($tp->id_tp, $smt->id_smt);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruang'] = $this->dropdown->getAllRuang();
        $data['sesi'] = $this->dropdown->getAllSesi();
        $data['kop'] = $this->cbt->getSettingKopBeritaAcara();
        $data['saved_ba'] = $this->cbt->getAllBeritaAcara($tp->id_tp, $smt->id_smt);

        if ($this->auth->is_admin()) {
            $data['profile'] = (object) [
                'nama_lengkap' => $user->first_name . ' ' . $user->last_name,
                'nip' => '-',
                'jabatan' => 'Administrator'
            ];
            $this->render_view(1, 'cbt/cetak/beritaacara', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['profile'] = $data['panitia'];
            $this->render_view(4, 'cbt/cetak/beritaacara', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['profile'] = $data['guru'];
            $this->render_view(2, 'cbt/cetak/beritaacara', $data);
        }
    }

    public function saveKopBerita()
    {
        $header_1 = $this->input->post('header_1', true);
        $header_2 = $this->input->post('header_2', true);
        $header_3 = $this->input->post('header_3', true);
        $header_4 = $this->input->post('header_4', true);

        $insert = [
            'id_kop' => 123456,
            'header_1' => $header_1,
            'header_2' => $header_2,
            'header_3' => $header_3,
            'header_4' => $header_4,
        ];

        $update = $this->db->replace('cbt_kop_berita', $insert);
        $this->output_json($update);
    }

    public function paktaIntegritas()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Cetak Pakta Integritas',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['jadwal'] = $this->dropdown->getAllJadwal($tp->id_tp, $smt->id_smt);
        $data['mapel'] = $this->dropdown->getAllJadwalMapel($tp->id_tp, $smt->id_smt);
        $data['mapel_jadwal'] = $this->dropdown->getAllMapelJadwal($tp->id_tp, $smt->id_smt);
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruang'] = $this->dropdown->getAllRuang();
        $data['sesi'] = $this->dropdown->getAllSesi();
        $data['kop'] = $this->cbt->getSettingKopBeritaAcara();
        $data['saved_pi'] = $this->cbt->getAllPaktaIntegritas($tp->id_tp, $smt->id_smt);

        if ($this->auth->is_admin()) {
            $data['profile'] = (object) [
                'nama_lengkap' => $user->first_name . ' ' . $user->last_name,
                'nip' => '-',
                'jabatan' => 'Administrator'
            ];
            $this->render_view(1, 'cbt/cetak/paktaintegritas', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['panitia'] = $panitia;
            $data['profile'] = $panitia;
            $this->render_view(4, 'cbt/cetak/paktaintegritas', $data);
        } else {
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['guru'] = $guru;
            $data['profile'] = $guru;
            $this->render_view(2, 'cbt/cetak/paktaintegritas', $data);
        }
    }

    public function pesertaUjian($mode = null)
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Cetak Daftar Peserta',
            'subjudul' => 'Cetak',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['kelass'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['ruangs'] = $this->dropdown->getAllRuang();
        $data['sesis'] = $this->cbt->getAllKodeSesi();
        $data['kop'] = $this->dashboard->getSetting();
        $data['ujian'] = $this->dropdown->getAllJenisUjian();

        $data['mode'] = $mode;
        if ($mode == '1' || $mode == null) {
            $data['siswa'] = $this->cbt->getAllPesertaByRuang($tp->id_tp, $smt->id_smt);
        } else {
            $data['siswa'] = $this->cbt->getAllPesertaByKelas($tp->id_tp, $smt->id_smt);
        }

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/cetak/pesertaujian', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/cetak/pesertaujian', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            //$this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/cetak/pesertaujian', $data);
        }
    }

    public function pengawas()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $jenis_selected = $this->input->get('jenis', true);
        $jenis_ujian = $this->cbt->getJenisById($jenis_selected);
        $data = [
            'user' => $user,
            'judul' => 'Jadwal Pengawas',
            'subjudul' => 'Cetak Jadwal Pengawas',
            'setting' => $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $id_jenis = $this->cbt->getDistinctJenisJadwal($tp->id_tp, $smt->id_smt);
        $ids = [];
        if (count($id_jenis) > 0) {
            foreach ($id_jenis as $jenis) {
                array_push($ids, $jenis->id_jenis);
            }
        }

        if (count($ids) > 0) {
            $data['jenis'] = $this->cbt->getAllJenisUjianByArrJenis($ids);
        } else {
            $data['jenis'] = ['' => 'belum ada jadwal ujian'];
        }

        $filter_selected = $this->input->get('filter', true);
        $dari_selected = $this->input->get('dari', true);
        $sampai_selected = $this->input->get('sampai', true);

        $data['filter'] = ['0' => 'Semua', '1' => 'Tanggal'];
        $data['jenis_selected'] = $jenis_selected;
        $data['jenis_ujian'] = $jenis_ujian;
        $data['filter_selected'] = $filter_selected;
        $data['dari_selected'] = $dari_selected;
        $data['sampai_selected'] = $sampai_selected;

        $jadwals = [];
        if ($jenis_selected != null) {
            $jadwals = $this->cbt->getJadwalByJenis($tp->id_tp, $smt->id_smt, $jenis_selected, $dari_selected, $sampai_selected);
        }

        $arrKls = [];
        foreach ($jadwals as $jadwal) {
            $bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? []);
            $jadwal->bank_kelas = array_map('strval', array_column($bank_kelas, 'kelas_id'));
            foreach ($jadwal->bank_kelas as $kl) {
                if (!in_array($kl, $arrKls)) {
                    $arrKls[] = $kl;
                }
            }
        }

        $pengawass = [];
        if ($jenis_selected != null) {
            $pengawass = $this->cbt->getAllPengawas($tp->id_tp, $smt->id_smt);
        }
        $data['pengawas'] = $pengawass;
        $data['gurus'] = $this->dropdown->getAllGuru();

        $sesis = [];
        if (count($arrKls) > 0) {
            $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arrKls);
            foreach ($ruangs as $row) {
                if (!isset($sesis[$row->ruang_id])) {
                    $sesis[$row->ruang_id] = (object) [
                        'ruang_id' => $row->ruang_id,
                        'kode_ruang' => $row->kode_ruang,
                        'nama_ruang' => $row->nama_ruang,
                        'sesi' => []
                    ];
                }

                $sesis[$row->ruang_id]->sesi[$row->sesi_id] = (object) [
                    'sesi_id' => $row->sesi_id,
                    'kode_sesi' => $row->kode_sesi,
                    'nama_sesi' => $row->nama_sesi,
                    'waktu_mulai' => $row->waktu_mulai,
                    'waktu_akhir' => $row->waktu_akhir,
                    'istirahat' => $row->istirahat,
                    'kelas_id' => $row->kelas_id,
                    'nama_kelas' => $row->nama_kelas,
                    'peserta' => $row->peserta,
                    'jadwals' => [],
                ];

                foreach ($jadwals as $jadwal) {
                    foreach ($jadwal->bank_kelas as $bk) {
                        if (in_array($bk, $sesis[$row->ruang_id]->sesi[$row->sesi_id]->kelas_id)) {
                            $sesis[$row->ruang_id]->sesi[$row->sesi_id]->jadwals[] = $jadwal;
                        }
                    }
                }

                usort($sesis[$row->ruang_id]->sesi[$row->sesi_id]->jadwals, static function ($a, $b) {
                    return ($a->tgl_mulai ?? '') <=> ($b->tgl_mulai ?? '');
                });
            }
        }
        $data['ruang'] = array_values($sesis);
        $data['jadwals'] = $jadwals;

        if ($this->auth->is_admin()) {
            $this->render_view(1, 'cbt/cetak/pengawas', $data);
        } elseif ($this->auth->in_group('panitia') || $this->session->userdata('role') == '4') {
            $data['panitia'] = $this->dashboard->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(4, 'cbt/cetak/pengawas', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'cbt/cetak/pengawas', $data);
        }
    }

    public function getKelas()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $sesi = $this->input->get('sesi');
        $mapel = $this->input->get('mapel');
        $kelas = $this->input->get('kelas');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $arr_jadwal = $this->cbt->getDataJadwalByMapel($tp->id_tp, $smt->id_smt);
        $arr_id_kelas = [];
        $arr_id_jadwal = [];

        $group_jadwal = [];
        foreach ($arr_jadwal as $jadwal) {
            $arr_id_jadwal[] = $jadwal->id_jadwal;
            $bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? []);
            $jadwal->bank_kelas = array_map('strval', array_column($bank_kelas, 'kelas_id'));

            foreach ($jadwal->bank_kelas as $kl) {
                if (!in_array($kl, $arr_id_kelas)) {
                    $arr_id_kelas[] = $kl;
                }
            }
            $group_jadwal[$jadwal->tgl_mulai][] = $jadwal;
        }
        $pengawass = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $arr_id_jadwal);
        if (count($pengawass) > 0) {
            foreach ($pengawass as $p) {
                if ($p != null && count(explode(",", $p->id_guru ?? '')) > 0) {
                    $p->pengawas = $this->master->getGuruByArrId(explode(",", $p->id_guru ?? ''));
                }
            }
        }

        // Handle sesi parameter properly
        $s = (!$sesi || $sesi === '' || $sesi === 'null') ? null : $sesi;
        $isesi = null;
        if ($s != null) {
            $isesi = $this->cbt->getSesiById($s);
        }
        $ikelas = $this->master->getKelasById($kelas);
        $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arr_id_kelas);

        $data['all_ruang'] = array_values($ruangs);
        $data['all_pengawas'] = $pengawass;
        $data['all_guru'] = $this->dropdown->getAllGuru();
        $data['all_tgl'] = $group_jadwal;

        $data['siswa'] = $this->cbt->getRuangSiswaByKelas($tp->id_tp, $smt->id_smt, $kelas, $s);
        $data['info'] = ['kelas' => $ikelas, 'sesi' => $isesi, 'mapel' => $mapel];

        $this->output_json($data);
    }

    public function getRuang()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dropdown_model', 'dropdown');

        $ruang = $this->input->get('ruang');
        $sesi = $this->input->get('sesi');
        $mapel = $this->input->get('mapel');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $arr_jadwal = $this->cbt->getDataJadwalByMapel($tp->id_tp, $smt->id_smt);
        $arr_id_kelas = [];
        $arr_id_jadwal = [];

        $group_jadwal = [];
        foreach ($arr_jadwal as $jadwal) {
            $arr_id_jadwal[] = $jadwal->id_jadwal;
            $bank_kelas = $this->maybe_unserialize($jadwal->bank_kelas ?? []);
            $jadwal->bank_kelas = array_map('strval', array_column($bank_kelas, 'kelas_id'));

            foreach ($jadwal->bank_kelas as $kl) {
                if (!in_array($kl, $arr_id_kelas)) {
                    $arr_id_kelas[] = $kl;
                }
            }
            $group_jadwal[$jadwal->tgl_mulai][] = $jadwal;
        }

        $pengawass = $this->cbt->getPengawasByJadwal($tp->id_tp, $smt->id_smt, $arr_id_jadwal);
        if (count($pengawass) > 0) {
            foreach ($pengawass as $p) {
                if ($p != null && count(explode(",", $p->id_guru ?? '')) > 0) {
                    $p->pengawas = $this->master->getGuruByArrId(explode(",", $p->id_guru ?? ''));
                }
            }
        }

        $ruangs = $this->cbt->getDistinctSesi($tp->id_tp, $smt->id_smt, $arr_id_kelas);

        $data['all_ruang'] = array_values($ruangs);
        $data['all_pengawas'] = $pengawass;
        $data['all_guru'] = $this->dropdown->getAllGuru();
        $data['all_tgl'] = $group_jadwal;

        $iruang = $this->cbt->getRuangById($ruang);
        // Handle sesi parameter properly
        $s = (!$sesi || $sesi === '' || $sesi === 'null' || $sesi === "null") ? null : $sesi;
        $isesi = null;
        if ($s != null) {
            $isesi = $this->cbt->getSesiById($s);
        }
        $data['siswa'] = $this->cbt->getRuangSiswaByKelas($tp->id_tp, $smt->id_smt, $arr_id_kelas);
        //$data['siswa'] = $this->cbt->getSiswaByRuang($tp->id_tp, $smt->id_smt, $ruang, $s);
        $data['info'] = [
            'ruang' => $iruang,
            'sesi' => $isesi,
            'mapel' => $mapel
        ];
        $this->output_json($data);
    }

}


