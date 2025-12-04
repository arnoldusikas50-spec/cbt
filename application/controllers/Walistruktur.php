<?php


class Walistruktur extends MY_Controller {

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
            'judul' => 'Struktur Organisasi',
            'subjudul' => 'Struktur Organisasi',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        //$data['kelas'] = $guru->wali_kelas;
        $struktur = $this->kelas->getStrukturKelas($guru->wali_kelas);
        if ($struktur==null) {
            $data['struktur'] = json_decode(json_encode($this->kelas->dummyStruktur()));
        } else {
            $data['struktur'] = $struktur;
        }
        $data['guru'] = $guru;
        $data['gurus'] = $this->dropdown->getAllGuru();
        $siswa = $this->kelas->getKelasSiswa($guru->wali_kelas, $tp->id_tp, $smt->id_smt);
        $siswas[''] = 'Pilih Siswa';
        foreach ($siswa as $key=>$value) {
            $siswas[$value->id_siswa] = $value->nama;
        }
        $data['siswas'] = $siswas;
        $data['id_kelas'] = $guru->wali_kelas;
        $this->render_view(2, 'members/guru/wali/struktur', $data);
    }

    public function save(){
        $data = [
            'id_kelas'=>$this->input->post('id_kelas'),
            'ketua'=>$this->input->post('ketua'),
            'wakil_ketua'=>$this->input->post('wakil_ketua'),
            'sekretaris_1'=>$this->input->post('sekretaris_1'),
            'sekretaris_2'=>$this->input->post('sekretaris_2'),
            'bendahara_1'=>$this->input->post('bendahara_1'),
            'bendahara_2'=>$this->input->post('bendahara_2'),
            'sie_ekstrakurikuler'=>$this->input->post('sie_ekstrakurikuler'),
            'sie_upacara'=>$this->input->post('sie_upacara'),
            'sie_olahraga'=>$this->input->post('sie_olahraga'),
            'sie_keagamaan'=>$this->input->post('sie_keagamaan'),
            'sie_keamanan'=>$this->input->post('sie_keamanan'),
            'sie_ketertiban'=>$this->input->post('sie_ketertiban'),
            'sie_kebersihan'=>$this->input->post('sie_kebersihan'),
            'sie_keindahan'=>$this->input->post('sie_keindahan'),
            'sie_kesehatan'=>$this->input->post('sie_kesehatan'),
            'sie_kekeluargaan'=>$this->input->post('sie_kekeluargaan'),
            'sie_humas'=>$this->input->post('sie_humas')
        ];

        $insert = $this->db->replace('kelas_struktur', $data);
        $this->output_json($insert);
    }

}
