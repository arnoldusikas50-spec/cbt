<?php


class Walicatatan extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin() && !$this->auth->in_group('guru')) {
            show_error('Hanya Administrator dan guru yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['datatables', 'form_validation']);// Load Library Ignited-Datatables
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function index() {
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Catatan Wali Kelas',
            'subjudul' => 'Catatan Kelas',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        $data['guru'] = $guru;
        $data['catatan_kelas'] = $this->kelas->getCatatanKelas($guru->wali_kelas, $tp->id_tp, $smt->id_smt);
        $data['catatan_siswa'] = $this->kelas->getCatatanSiswa($tp->id_tp, $smt->id_smt, $guru->wali_kelas);
        $this->render_view(2, 'members/guru/wali/catatan', $data);
    }

    public function siswa() {
        $id_siswa = $this->input->get('id_siswa');
        $id_kelas = $this->input->get('id_kelas');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Catatan Siswa',
            'subjudul' => 'Catatan Siswa',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);
        $data['guru'] = $guru;
        $data['siswa'] = $this->master->getSiswaById($id_siswa);
        $data['catatan_siswa'] = $this->kelas->getAllCatatanSiswa($id_siswa, $tp->id_tp, $smt->id_smt);
        $data['id_kelas'] = $id_kelas;

        $this->render_view(2, 'members/guru/wali/persiswa', $data);
    }

    public function saveCatatanKelas(){
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);

        $text = $this->input->post('text', true);
        $level = $this->input->post('level', true);

        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '1',
            'level' => $level,
            'id_kelas' => $guru->wali_kelas,
            'text' => $text,
            'reading' => json_encode([])
        ];
        $insert = $this->master->create('kelas_catatan_wali', $data);
        $this->output_json($insert);
    }

    public function saveCatatanSiswa(){
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->auth->user();
        $guru = $this->dashboard->getDataGuruByUsername($user->username, $tp->id_tp, $smt->id_smt);

        $id_siswa = $this->input->post('id_siswa');
        $text = $this->input->post('text', true);
        $level = $this->input->post('level', true);

        $data = [
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt,
            'type' => '2',
            'level' => $level,
            'id_kelas' => $guru->wali_kelas,
            'id_siswa' => $id_siswa,
            'text' => $text,
            'reading' => json_encode([])
        ];
        $insert = $this->master->create('kelas_catatan_wali', $data);
        $this->output_json($insert);
    }

    public function updateCatatanKelas(){
    }

    public function hapus($id_catatan){
        $delete = $this->master->delete('kelas_catatan_wali', $id_catatan, 'id_catatan');
        $this->output_json($delete);
    }
}
