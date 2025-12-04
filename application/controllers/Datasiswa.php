<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Datasiswa extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        }else if ( !$this->auth->is_admin() && !$this->auth->in_group('guru') ){
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library('upload');
        $this->load->library(['form_validation']); // Load Library Ignited-Datatables
        $this->form_validation->set_error_delimiters('', '');
    }

    public function index() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = ['user' => $user, 'judul' => 'Siswa', 'subjudul' => 'Data Siswa', 'setting' => $this->dashboard->getSetting()];

        $tp = $this->dashboard->getTahun();
        $smt = $this->dashboard->getSemester();
        $data['tp'] = $tp;
        $data['smt'] = $smt;

        $searchTp = array_search('1', array_column($tp, 'active'));
        $searchSmt = array_search('1', array_column($smt, 'active'));
        $tpAktif = $tp[$searchTp];
        $smtAktif = $smt[$searchSmt];
        $data['tp_active'] = $tpAktif;
        $data['smt_active'] = $smtAktif;

        $data['kelass'] = $this->dropdown->getAllKelas($tpAktif->id_tp, $smtAktif->id_smt);

        $this->render_view(1, 'master/siswa/data', $data);
    }

    public function data() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $this->output_json($this->master->getDataSiswa($tp->id_tp, $smt->id_smt), false);
    }

    public function list() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $page = $this->input->post('page', true);
        $limit = $this->input->post('limit', true);
        $search = $this->input->post('search', true);
        $filter = $this->input->post('filter', true);
        $kelas = $this->input->post('kelas', true);

        $offset = ($page - 1) * $limit;
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $count_siswa = $this->master->getSiswaTotalPage($tp->id_tp, $smt->id_smt, $filter, $kelas, $search);
        $lists = $this->master->getSiswaPage($tp->id_tp, $smt->id_smt, $offset, $limit, $filter, $kelas, $search);
        foreach ($lists as $list) {
            $list->foto_1 = base_url($list->foto);
            $list->foto = $list->foto != null && $list->foto != "" && file_exists($list->foto) ? base_url($list->foto) : base_url('assets/img/siswa_sm.png');
        }

        usort($lists, function ($a, $b) {
            $namaA = $a->nama;
            $namaB = $b->nama;
            return strnatcasecmp($namaA, $namaB);
        });
        $data = [
            'lists' => $lists,
            'total' => $count_siswa,
            'pages' => ceil($count_siswa / $limit),
            'search' => $search,
            'perpage' => $limit,
            'filter' => $filter
        ];

        $this->output_json($data);
    }

    public function add($id_kelas = null) {
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $data = ['user' => $user, 'judul' => 'Siswa', 'subjudul' => 'Import Siswa', 'setting' => $this->dashboard->getSetting()];
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        if ($id_kelas) {
            $this->load->model('Master_model', 'master');
            $data['kelas'] = $this->master->getKelasById($id_kelas);
        }

        $this->render_view(1, 'master/siswa/add', $data);
    }

    public function create() {
        $this->load->model('Log_model', 'logging');
        $this->load->model('Master_model', 'master');

        $nis           = $this->input->post('nis', true);
        $nisn          = $this->input->post('nisn', true);
        $username      = $this->input->post('username', true);

        $u_nis = '|is_unique[master_siswa.nis]';
        $u_nisn = '|is_unique[master_siswa.nisn]';
        $u_name = '|is_unique[master_siswa.username]|is_unique[users.username]';

        $this->form_validation->set_rules('nama_siswa', 'Nama', 'required|trim');
        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[5]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[5]|max_length[20]'. $u_nisn);
        $this->form_validation->set_rules('username', 'Username', 'required|trim' . $u_name);
        $this->form_validation->set_rules('password', 'Username', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $data['insert'] = false;
            $data['error'] = [
                'nama' => form_error('nama_siswa'),
                'nis' => form_error('nis'),
                'nisn' => form_error('nisn'),
                'username' => form_error('username'),
                'password' => form_error('password'),
            ];
            $data['text'] = 'Data Sudah ada, Pastikan NIS, NISN dan Username belum digunakan siswa lain';
        } else {
            $nama_siswa = $this->input->post('nama_siswa', true);
            $nama = explode(' ', $nama_siswa ?? '');
            $first_name = $nama[0];
            $last_name = end($nama);
            $insert = [
                "nama"          => $nama_siswa,
                "nis"           => $nis,
                "nisn"          => $nisn,
                "jenis_kelamin" => $this->input->post('jenis_kelamin', true),
                "agama"         => $this->input->post('agama', true),
                "kelas_awal"    => $this->input->post('kelas_awal', true),
                "tahun_masuk"   => $this->input->post('tahun_masuk', true),
                "username"      => $username,
                "password"      => $this->input->post('password', true),
                "foto"          => 'uploads/foto_siswa/'.$nis.'jpg'
            ];

            $this->db->set('uid','UUID()',FALSE);
            $data['insert'] = $this->db->insert('master_siswa', $insert);
            $id = $this->db->insert_id();

            $siswa = $this->master->getSiswaById($id);
            $induk = [
                "id_siswa"   => $id,
                "uid"        => $siswa->uid,
                "status"     => 1
            ];

            $this->db->insert('buku_induk', $induk);
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menambahkan Siswa: '. $nama_siswa);

            $user = [
                'username' => $siswa->username,
                'password' => password_hash($siswa->password, PASSWORD_BCRYPT),
                'first_name' => $first_name,
                'last_name' => $last_name
            ];
            $this->auth->register($user, '3');

            $data['text'] = 'Siswa berhasil ditambahkan';
        }
        $this->output_json($data);
    }

    public function edit($id) {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $siswa = $this->master->getSiswaById($id);
        $inputData = [
            [
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'value' => $siswa->nama,
                'icon' => 'far fa-user',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'NIS',
                'name' => 'nis',
                'value' => $siswa->nis,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'number'
            ],
            [
                'name' => 'nisn',
                'label' => 'NISN',
                'value' => $siswa->nisn,
                'icon' => 'far fa-id-card',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'value' => $siswa->jenis_kelamin,
                'icon' => 'fas fa-venus-mars',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'kelas_awal',
                'label' => 'Diterima di kelas',
                'value' => $siswa->kelas_awal,
                'icon' => 'fas fa-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tahun_masuk',
                'label' => 'Tgl diterima',
                'value' => $siswa->tahun_masuk,
                'icon' => 'tahun far fa-calendar-alt',
                'class' => 'tahun',
                'type' => 'text'
            ],
            [
                'name' => 'sekolah_asal',
                'label' => 'Sekolah Asal',
                'value' => $siswa->sekolah_asal,
                'icon' => 'fas fa-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
            'name' => 'status',
            'label' => 'Status',
            'value' => $siswa->status,
                'icon' => 'far fa-user',
            'class' => 'status',
            'type' => 'text'
        ]
        ];
        $inputBio = [
            [
                'name' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'value' => $siswa->tempat_lahir,
                'icon' => 'far fa-map',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'value' => $siswa->tanggal_lahir,
                'icon' => 'far fa-calendar',
                'class' => 'tahun',
                'type' => 'text'
            ],
            ['class' => '', 'name' => 'agama', 'label' => 'Agama', 'value' => $siswa->agama, 'icon' => 'far fa-calendar', 'type' => 'text'],
            ['class' => '', 'name' => 'alamat', 'label' => 'Alamat', 'value' => $siswa->alamat, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rt', 'label' => 'Rt', 'value' => $siswa->rt, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rw', 'label' => 'Rw', 'value' => $siswa->rw, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kelurahan', 'label' => 'Kelurahan/Desa', 'value' => $siswa->kelurahan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kecamatan', 'label' => 'Kecamatan', 'value' => $siswa->kecamatan, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kabupaten', 'label' => 'Kabupaten/Kota', 'value' => $siswa->kabupaten, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kode_pos', 'label' => 'Kode Pos', 'value' => $siswa->kode_pos, 'icon' => 'far fa-user', 'type' => 'text'],
            ['class' => '', 'name' => 'hp', 'label' => 'Hp', 'value' => $siswa->hp, 'icon' => 'far fa-user', 'type' => 'text']
        ];

        $inputOrtu = [
            ['name' => 'status_keluarga', 'label' => 'Status Keluarga', 'value' => $siswa->status_keluarga, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'anak_ke', 'label' => 'Anak ke', 'value' => $siswa->anak_ke, 'icon' => 'far fa-user', 'type' => 'number'],
            ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $siswa->nama_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            //['name' => 'pendidikan_ayah', 'label' => 'Pendidikan Ayah', 'value' => $siswa->pendidikan_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'value' => $siswa->pekerjaan_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'alamat_ayah', 'label' => 'Alamat Ayah', 'value' => $siswa->alamat_ayah, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'nohp_ayah', 'label' => 'No. HP Ayah', 'value' => $siswa->nohp_ayah, 'icon' => 'far fa-user', 'type' => 'number'],
            ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $siswa->nama_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            //['name' => 'pendidikan_ibu', 'label' => 'Pendidikan Ibu', 'value' => $siswa->pendidikan_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'value' => $siswa->pekerjaan_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'alamat_ibu', 'label' => 'Alamat Ibu', 'value' => $siswa->alamat_ibu, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'nohp_ibu', 'label' => 'No. HP Ibu', 'value' => $siswa->nohp_ibu, 'icon' => 'far fa-user', 'type' => 'number']
        ];

        $inputWali = [
            ['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $siswa->nama_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            //['name' => 'pendidikan_wali', 'label' => 'Pendidikan Wali', 'value' => $siswa->pendidikan_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'pekerjaan_wali', 'label' => 'Pekerjaan Wali', 'value' => $siswa->pekerjaan_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'alamat_wali', 'label' => 'Alamat Wali', 'value' => $siswa->alamat_wali, 'icon' => 'far fa-user', 'type' => 'text'],
            ['name' => 'nohp_wali', 'label' => 'No. HP Wali', 'value' => $siswa->nohp_wali, 'icon' => 'far fa-user', 'type' => 'number']
        ];

        $user = $this->auth->user();
        $data = ['user' => $user, 'judul' => 'Siswa', 'subjudul' => 'Edit Data Siswa', 'siswa' => $siswa, 'setting' => $this->dashboard->getSetting()];

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $data['tp'] = $this->dashboard->getTahun();
        $data['smt'] = $this->dashboard->getSemester();
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;
        $data['input_data'] = json_decode(json_encode($inputData), FALSE);
        $data['input_bio'] = json_decode(json_encode($inputBio), FALSE);
        $data['input_ortu'] = json_decode(json_encode($inputOrtu), FALSE);
        $data['input_wali'] = json_decode(json_encode($inputWali), FALSE);

        if ( $this->auth->is_admin() ) {
            $this->render_view(1, 'master/siswa/edit', $data);
        } else {
            $data['guru'] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $this->render_view(2, 'master/siswa/edit', $data);
        }
    }

    public function updateData() {
        $this->load->model('Master_model', 'master');

        $id_siswa = $this->input->post('id_siswa', true);
        $nis           = $this->input->post('nis', true);
        $nisn          = $this->input->post('nisn', true);

        $siswa = $this->master->getSiswaById($id_siswa);
        $u_nis = $siswa->nis == $nis ? "" : "|is_unique[master_siswa.nis]";
        $u_nisn = $siswa->nisn == $nisn ? "" : "|is_unique[master_siswa.nisn]";

        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('nisn', 'NISN', 'required|trim|numeric'. $u_nisn);

        if (!$this->form_validation->run()) {
            $data['insert'] = false;
            //$data['text'] = 'Nama belum diisi atau NIS kurang dari 6 angka, atau NIS Sudah ada, Pastikan NIS dan NISN belum digunakan siswa lain';
            $data['text'] = validation_errors();
        } else {
            $tgl_lahir = $this->input->post('tanggal_lahir', true);
            $tgl_masuk = $this->input->post('tahun_masuk', true);

            $input = [
            'nisn'   => $this->input->post('nisn', true),
            'nis'     => $this->input->post('nis', true),
            'nama'   => $this->input->post('nama', true),
            'jenis_kelamin'   => $this->input->post('jenis_kelamin', true),
            'tempat_lahir'   => $this->input->post('tempat_lahir', true),
            'tanggal_lahir'   => $this->strContains($tgl_lahir, '0000-') ? null : $tgl_lahir,
            'agama'   => $this->input->post('agama', true),
            'status_keluarga'   => $this->input->post('status_keluarga', true),
            'anak_ke'   => $this->input->post('anak_ke', true),
            'alamat'   => $this->input->post('alamat', true),
            'rt'   =>	$this->input->post('rt', true),
            'rw'   =>	$this->input->post('rw', true),
            'kelurahan'   => $this->input->post('kelurahan', true),
            'kecamatan'   => $this->input->post('kecamatan', true),
            'kabupaten'   => $this->input->post('kabupaten', true),
            'provinsi'   =>	$this->input->post('provinsi', true),
            'kode_pos'   =>	$this->input->post('kode_pos', true),
            'hp'   =>	$this->input->post('hp', true),
            'nama_ayah'   =>	$this->input->post('nama_ayah', true),
            'nohp_ayah'   =>	$this->input->post('nohp_ayah', true),
            'pendidikan_ayah'   =>	$this->input->post('pendidikan_ayah', true),
            'pekerjaan_ayah'   =>	$this->input->post('pekerjaan_ayah', true),
            'alamat_ayah'   =>	$this->input->post('alamat_ayah', true),
            'nama_ibu'   =>	$this->input->post('nama_ibu', true),
            'nohp_ibu'   =>	$this->input->post('nohp_ibu', true),
            'pendidikan_ibu'   =>	$this->input->post('pendidikan_ibu', true),
            'pekerjaan_ibu'   =>	$this->input->post('pekerjaan_ibu', true),
            'alamat_ibu'   =>	$this->input->post('alamat_ibu', true),
            'nama_wali'   =>	$this->input->post('nama_wali', true),
            'pendidikan_wali'   =>	$this->input->post('pendidikan_wali', true),
            'pekerjaan_wali'   =>	$this->input->post('pekerjaan_wali', true),
            'nohp_wali'   =>	$this->input->post('nohp_wali', true),
            'alamat_wali'   =>	$this->input->post('alamat_wali', true),
            'tahun_masuk'   =>	$this->strContains($tgl_masuk, '0000-') ? null : $tgl_masuk,
            'kelas_awal'   =>	$this->input->post('kelas_awal', true),
            'tgl_lahir_ayah'   =>	$this->input->post('tgl_lahir_ayah', true),
            'tgl_lahir_ibu'   =>	$this->input->post('tgl_lahir_ibu', true),
            'tgl_lahir_wali'   =>	$this->input->post('tgl_lahir_wali', true),
            'sekolah_asal'   =>	$this->input->post('sekolah_asal', true),
            'foto'   =>	$siswa->foto != null && $siswa->foto != "" ? $siswa->foto : 'uploads/foto_siswa/'.$nis.'.jpg'
            ];

            $this->master->update('master_siswa', $input, 'id_siswa', $id_siswa);

            $this->db->set('status', $this->input->post('status', true));
            $this->db->where('id_siswa', $siswa->id_siswa);
            $this->db->update('buku_induk');

            $data['insert'] = $input;
            $data['text'] = 'Siswa berhasil diperbaharui';
        }

        $this->output_json($data);
    }

    function strContains($string, $val) {
        return strpos($string, $val) !== false;
    }

    function uploadFile($id_siswa){
        $this->load->model('Master_model', 'master');

        $siswa = $this->master->getSiswaById($id_siswa);
        if(isset($_FILES["foto"]["name"])){

            $config['upload_path'] = './uploads/foto_siswa/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
            $config['overwrite'] = true;
            //$config['encrypt_name'] = TRUE;
            $config['file_name'] = $siswa->nis;

            $this->upload->initialize($config);
            if(!$this->upload->do_upload('foto')){
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            }else{
                $result = $this->upload->data();
                $data['src'] = base_url().'uploads/foto_siswa/'.$result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;

                $this->db->set('foto', 'uploads/foto_siswa/'.$result['file_name']);
                $this->db->where('id_siswa', $id_siswa);
                $this->db->update('master_siswa');
            }

            $data['type'] = $_FILES['foto']['type'];
            $data['size'] = $_FILES['foto']['size'];
        } else {
            $data['src'] = '';
        }


        $this->output_json($data);
    }

    function deleteFile($id_siswa) {
        $src = $this->input->post('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        if ($file_name != 'assets/img/siswa.png') {
            if (unlink($file_name)) {
                $this->db->set('foto', '');
                $this->db->where('id_siswa', $id_siswa);
                $this->db->update('master_siswa');

                echo 'File Delete Successfully';
            }
        }
    }

    public function delete() {
        $this->load->model('Master_model', 'master');

        $chk = $this->input->post('checked', true);
        $aksi = $this->input->post('aksi', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        if (!$chk) {
            $this->output_json(['status' => false]);
        } else {
            $last = $aksi;
            $status = true;
            $messages = [];
            $actives = [];
            $deletable = [];
            $resultAll = [];

            $splitTahun = explode('/', $tp->tahun ?? '');
            $thnSmt = $smt->id_smt = '1' ? 0 : 1;
            $siswas = $this->master->getSiswaByArrId($chk);

            if ($aksi == 'pindah') {
                foreach ($chk as $id) {
                    $this->db->set('kelas_akhir', $siswas[$id]->nama_kelas);
                    $this->db->set('tgl_pindah',date('Y-m-d'));
                    $this->db->set('status', '3');
                    $this->db->where('id_siswa', $id);
                    $this->db->update('buku_induk');
                }
            } elseif ($aksi == 'keluar') {
                foreach ($chk as $id) {
                    $this->db->set('kelas_akhir', $siswas[$id]->nama_kelas);
                    $this->db->set('tgl_pindah',date('Y-m-d'));
                    $this->db->set('status', '4');
                    $this->db->where('id_siswa', $id);
                    $this->db->update('buku_induk');
                }
            } elseif ($aksi == 'hapus') {
                $siswas = $this->auth->usersSiswa($chk);
                foreach ($siswas as $siswa) {
                    if ($siswa->active == '0') {
                        $deletable[] = $siswa->id;
                    } else {
                        $actives[$siswa->id_siswa] = $siswa->active;
                    }
                }
                if (count($actives)>0) {
                    $messages[] = 'Nonaktifkan dahulu akun siswa';
                }

                // ==> check
                $tables = [
                    'kelas_siswa'       => 'Keluarkan dahulu siswa dari kelas.',
                    'cbt_siswa'         => 'Rekap dahulu hasil ujian siswa.',
                    'rapor_nilai_akhir' => 'Tidak bisa dihapus, silahkan set sebagai siswa pindah/keluar.'
                ];
                foreach ($tables as $table=>$msg) {
                    if ($table == 'cbt_siswa') {
                        $this->db->select('a.id_siswa, b.id_rekap_siswa');
                        $this->db->join('cbt_rekap_siswa b', 'a.id_siswa = b.id_siswa', 'left');
                    } elseif ($table == 'kelas_siswa') {
                        $this->db->where('a.id_kelas !=', '0');
                        $this->db->where('a.id_tp', $tp->id_tp);
                        $this->db->where('a.id_smt', $smt->id_smt);
                    } else {
                        $this->db->select('a.id_siswa, a.id_nilai_akhir, b.status');
                        $this->db->join('buku_induk b', 'a.id_siswa = b.id_siswa', 'left');
                    }
                    $this->db->where_in('a.id_siswa', $chk);
                    $nums = $this->db->get($table. ' a')->result();
                    $resultAll[$table] = $nums;

                    if ($table == 'cbt_siswa') {
                        foreach ($nums as $key => $num) {
                            if ($num->id_rekap_siswa) {
                                unset($nums[$key]);
                            }
                        }
                    } elseif ($table == 'rapor_nilai_akhir') {
                        foreach ($nums as $key => $num) {
                            if ($num->status && $num->status > 2) {
                                unset($nums[$key]);
                            }
                        }
                    }

                    if (count($nums) > 0) {
                        $messages[] = $msg;
                    }
                }

                if (count($messages) > 0) {
                    $status = false;
                } else {
                    // ==> hapus CBT
                    // cbt_sesi_siswa
                    $this->master->delete('cbt_sesi_siswa', $chk, 'siswa_id');
                    // cbt_nomor_peserta
                    $this->master->delete('cbt_nomor_peserta', $chk, 'id_siswa');
                    // log_ujian, nilai harus sudah direkap
                    $this->master->delete('log_ujian', $chk, 'id_siswa');
                    // cbt_siswa, nilai harus sudah direkap
                    $this->master->delete('cbt_siswa', $chk, 'id_siswa');

                    // ==> optional, bisa dihapus via menu rekap
                    // cbt_rekap_siswa
                    //$this->master->delete('cbt_rekap_siswa', $chk, 'id_siswa');

                    // ==> hapus MASTER
                    // kelas_siswa, harus dikeluarkan dari kelas
                    $this->master->delete('kelas_siswa', $chk, 'id_siswa');
                    $this->master->delete('master_siswa', $chk, 'id_siswa');

                    // ==> elearning
                    // kelas_catatan_mapel
                    $this->master->delete('kelas_catatan_mapel', $chk, 'id_siswa');
                    // kelas_catatan_wali
                    $this->master->delete('kelas_catatan_wali', $chk, 'id_siswa');
                    // log_materi
                    $this->master->delete('log_materi', $chk, 'id_siswa');

                    // users
                    $this->master->delete('users', $deletable, 'id');
                    $this->master->delete('users_groups', $deletable, 'user_id');
                }
            }

            $msg = $aksi == 'hapus' ? 'dihapus' : 'di'.$aksi.'kan';
            $this->output_json([
                'status' => $status,
                'total' => count($chk),
                'last'=>$last,
                'data'=> $siswas ?? [],
                'count' => count($messages),
                'msg' => $messages,
                'actives' => $actives,
                'deletable' => $deletable,
                'results' => $resultAll,
                'message' => $status ? 'Siswa berhasil '.$msg : '<b>Siswa tidak bisa dihapus:</b><br>'.implode('<br>', $messages)
            ]);
        }
    }

    public function do_import() {
        $id_kelas = $this->input->post('id_kelas', true);
        $input = $this->input->post('siswa', true);
        $errors = [];
        $wrong_file = false;

        $users = [];
        foreach ($input as $key=>$value) {
            if (isset($value['id_siswa'])) {
                $errors[] = $key + 1;
                $wrong_file = true;
                break;
            } else {
                $this->form_validation->set_data($value);

                $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
                $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[5]|max_length[30]|is_unique[master_siswa.nis]');
                $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[5]|max_length[20]|is_unique[master_siswa.nisn]');
                $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[5]|is_unique[master_siswa.username]');
                $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[5]');
                if (!$this->form_validation->run()) {
                    $errors[$key + 1] = [
                        'nama' => form_error('nama'),
                        'nis' => form_error('nis'),
                        'nisn' => form_error('nisn'),
                        'username' => form_error('username'),
                        'password' => form_error('password'),
                    ];
                    break;
                } else {
                    $nama_siswa = $value['nama'];
                    $nama = explode(' ', $nama_siswa ?? '');
                    $first_name = $nama[0];
                    $last_name = end($nama);
                    $users[] = [
                        'username' => $value['username'],
                        'password' => password_hash($value['password'], PASSWORD_BCRYPT),
                        'first_name' => $first_name,
                        'last_name' => $last_name
                    ];
                }
            }
        }

        if (count($users) > 0) {
            $this->auth->registerAll($users, '3');
            $this->db->insert_batch('master_siswa', $input);

            $uids = $this->db->select('id_siswa, uid')->from('master_siswa')->get()->result();

            foreach ($uids as $ind=>$uid) {
                $uid->status = 1;
                $check = $this->db->select('id_siswa')->from('buku_induk')->where('id_siswa', $uid->id_siswa);
                if ($check->get()->num_rows() > 0) {
                    unset($uids[$ind]);
                }
            }

            $this->db->insert_batch('buku_induk', $uids);

            // update kelas
            if ($id_kelas != '0') {
                $this->load->model('Master_model', 'master');
                $usernames = array_column($users, 'username');
                $siswas = $this->master->getSiswaByUsername($usernames);

                $id_tp = $this->master->getTahunActive()->id_tp;
                $id_smt = $this->master->getSemesterActive()->id_smt;
                $kelas = $this->master->getKelasById($id_kelas);
                $siswakelas = $this->maybe_unserialize($kelas->jumlah_siswa);
                $arr_siswa = array_column($siswakelas, 'id');
                $insertKelasSiswa = [];
                foreach ($siswas as $siswa) {
                    $idsiswa = $siswa->id_siswa;
                    if (!in_array($idsiswa, $arr_siswa)) {
                        $siswakelas[] = ['id' => $idsiswa];

                        $insertKelasSiswa[$id_tp . $id_smt . $idsiswa] = [
                            'id_kelas_siswa' => $id_tp . $id_smt . $idsiswa,
                            'id_tp' => $id_tp,
                            'id_smt' => $id_smt,
                            'id_kelas' => $id_kelas,
                            'id_siswa' => $idsiswa,
                        ];
                    }
                }
                $jumlah = json_encode($siswakelas);
                $update_kelas = ['jumlah_siswa' => $jumlah];
                $this->db->where('id_kelas', $id_kelas);
                $this->db->update('master_kelas', $update_kelas);

                if (count($insertKelasSiswa) > 0) {
                    $this->db->insert_batch('kelas_siswa', $insertKelasSiswa);
                }
            }

            $data = [
                'status'	=> true,
                'data'      => $input,
                'user'      => count($input),
            ];
        } else {
            $data = [
                'status' => false,
                'errors' => $errors,
                'data' => $input,
                'user' => 0,
                'wrong' => $wrong_file
            ];
        }
        $this->output_json($data);
    }

    public function update() {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = ['user' => $user, 'judul' => 'Update Data Siswa', 'subjudul' => 'Update Data Siswa', 'setting' => $this->dashboard->getSetting()];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $data['tp'] = $this->dashboard->getTahun();
        $data['smt'] = $this->dashboard->getSemester();

        $data['tipe'] = 'update';
        $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);

        $this->render_view(1, 'master/siswa/update', $data);
    }

    public function downloadData($id_kelas) {
        $this->load->model('Master_model', 'master');

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $siswas = $this->master->getSiswaByKelas($tp->id_tp, $smt->id_smt, $id_kelas);

        foreach ($siswas as $ind=>$siswa) {
            $siswa->no = ($ind + 1);
            $siswa->foto = $siswa->foto != null && $siswa->foto != "" && file_exists($siswa->foto) ? base_url($siswa->foto) : base_url('assets/img/siswa_sm.png');
        }
        $this->output_json(['status' => true, 'siswa'=>$siswas]);
    }

    public function updateAll() {
        $ids = $this->input->post('ids', true);
        $update_siswa = $this->input->post('siswa', true);

        $this->db->update_batch('master_siswa', $update_siswa, 'id_siswa');
        $update_user = [];
        $users = $this->auth->usersSiswa(json_decode($ids));
        foreach ($users as $siswa) {
            $update_user[] = [
                'id'       => $siswa->id,
                'username' => $siswa->username,
                'password' => password_hash($siswa->password, PASSWORD_BCRYPT)
            ];
        }
        $this->db->update_batch('users', $update_user, 'id');
        $data = [
            'status'	=> true,
            'siswa'     => count($update_siswa),
            'user'      => count($update_user)
        ];
        $this->output_json($data);
    }

    public function update_foto() {
        $input = $this->input->post('siswa', true);
        $errors = [];
        $duplikat = [];

        foreach ($input as $value) {
            $this->form_validation->set_data($value);
            $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]');
            if ($this->form_validation->run() == FALSE) {
                $duplikat[] = $value;
                $errors[$value['nama']] = [
                    'nis' => form_error('nis'),
                ];
            }
        }

        if (count($errors) > 0) {
            $data = [
                'status'	=> false,
                'errors'	=> $errors,
                'duplikat'  => $duplikat
            ];
        } else {
            $siswas = [];
            foreach ($input as $value) {
                $foto = 'uploads/foto_siswa/' . trim($value['nis'] ?? '00') . '.jpg';
                if (isset($value['foto'])) {
                    $base64_image_string = $value['foto'];
                    $extension = $value['ext'];
                    if ($extension == 'jpeg') $extension = 'jpg';
                    $output_file = trim($value['nis'] ?? '00') . '.' . $extension;
                    file_put_contents('./uploads/foto_siswa/' . $output_file, base64_decode($base64_image_string));
                    $foto = 'uploads/foto_siswa/'.$output_file;
                }

                $siswas[] = [
                    "id_siswa" => $value['id_siswa'],
                    "nis" => $value["nis"]  ?? '',
                    "foto" => $foto
                ];
            }
            $this->db->update_batch('master_siswa', $siswas, 'id_siswa');

            $data = [
                'status'	=> true,
                'user'	=> count($siswas),
            ];
        }
        $this->output_json($data);
    }

    public function updateNisByNisn() {
        $input = json_decode($this->input->post('siswa', true));
        //$this->db->trans_start();

        foreach ($input as $val) {
            $this->db->set('nis', trim($val->nis ?? ''));
            $this->db->where('nisn', trim($val->nisn ?? ''));
            $save = $this->db->update('master_siswa');
        }
        $this->db->trans_complete();

        $this->output_json($save);
    }

    public function editLogin() {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $id_siswa 	= $this->input->post('id_siswa', true);
        $username 	= $this->input->post('username', true);
        $pass 	= $this->input->post('new', true);

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $siswa_check = $this->dashboard->checkUsernameSiswa($username);
        $siswa_lain = array_filter($siswa_check, function($siswa) use ($id_siswa) {
            return $siswa->id_siswa != $id_siswa;
        });

        $this->form_validation->set_rules('old', 'Password lama', 'required');
        $this->form_validation->set_rules('new', 'Password baru', 'required|min_length[5]|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', 'Konfirmasi password baru', 'required');

        if (count($siswa_lain) > 0) {
            $data = [
                //'siswa' => $siswa,
                'status' => false,
                'errors' => ['username' => 'Username sudah digunakan']
            ];
        } elseif ($this->form_validation->run() == FALSE){
            $data = [
                'status' => false,
                'errors' => [
                    'old' => form_error('old'),
                    'new' => form_error('new'),
                    'new_confirm' => form_error('new_confirm')
                ]
            ];
        } else {
            $siswa = $this->auth->userSiswa($id_siswa);
            $user = [
                'username' => $username,
                'password' => password_hash($pass, PASSWORD_BCRYPT),
            ];
            $changed = $this->auth->changeUser($siswa->id, $user);

            if ($changed) {
                $this->db->set('username', $username);
                $this->db->set('password', $pass);
                $this->db->where('id_siswa', $id_siswa);
                $status = $this->db->update('master_siswa');
                $msg	 = !$status ? 'Gagal mengganti username/passsword.' : 'berhasil mengganti username/passsword.';
            } else {
                $status = false;
                $msg	 = 'Gagal mengganti username/passsword.';
            }

            $data['status'] = $status;
            $data['text'] = $msg;
        }
        $this->output_json($data);
    }
}
