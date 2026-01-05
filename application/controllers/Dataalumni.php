<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dataalumni extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin()) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library('upload');
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Rapor_model', 'rapor');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function index()
    {
        $filter = $this->input->get('filter', true);
        $tahun = $this->input->get('tahun', true);
        $kelas_akhir = $this->input->get('kelas', true);

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Data Kelulusan & Alumni',
            'subjudul' => 'Data Alumni',
            'setting' => $setting,
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $allTp = $this->dashboard->getTahun();
        $data['tp'] = $allTp;
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        $data['tahun_lulus'] = $this->master->getDistinctTahunLulus();
        $data['kelas_akhir'] = $this->master->getDistinctKelasAkhir();
        $data['filter_selected'] = $filter ?? 2;
        $data['tahun_selected'] = $tahun;
        $data['kelas_selected'] = $kelas_akhir;

        $level = $setting->jenjang == '1' ? '6' : ($setting->jenjang == '2' ? '9' : '12');
        $tp_before = $this->dashboard->getTpBefore($tp->tahun);
        $data['tp_before'] = $tp_before;
        $lulus = 0;
        if ($tp_before) {
            $jumlah_lulus = $this->rapor->getJumlahLulus($tp_before->id_tp, '2', $level);
            $splitTahun = explode('/', $tp_before->tahun ?? '-/-');
            $alumnis = $this->master->getAlumniByTahun($splitTahun[1]);
            if ($jumlah_lulus > count($alumnis)) {
                $lulus = $jumlah_lulus;
            }
        }
        $data['jumlah_lulus'] = $lulus;

        if (!$tahun) {
            $count_siswa = $this->db->count_all('master_siswa');
            $count_induk = $this->db->count_all('buku_induk');

            if ($count_siswa > $count_induk) {
                $uids = $this->db->select('id_siswa, uid')->from('master_siswa')->get()->result();

                foreach ($uids as $uid) {
                    $check = $this->db->select('id_siswa')->from('buku_induk')->where('id_siswa', $uid->id_siswa);
                    if ($check->get()->num_rows() == 0) {
                        $this->db->insert('buku_induk', $uid);
                    }
                }
            }
            if ($filter && $filter != '2') {
                $data['alumnis'] = $this->master->getAlumniFilter($filter);
            }
        } elseif ($tahun != null && $tahun != '') {
            $data['alumnis'] = $this->master->getAlumniByTahun($tahun, $kelas_akhir);
        }

        $this->render_view(1, 'master/alumni/data', $data);
    }

    public function generateAlumni()
    {
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        //$allTp = $this->dashboard->getTahun();
        //$searchId = array_search('1', array_column($allTp, 'active'));
        //$idBefore = $tp_before->id_tp ?? '-';
        $tp_before = $this->dashboard->getTpBefore($tp->tahun);
        $level = $setting->jenjang == '1' ? '6' : ($setting->jenjang == '2' ? '9' : '12');
        $siswas = [];
        $splitTahun = '-';
        if ($tp_before) {
            $splitTahun = explode('/', $tp_before->tahun ?? '-/-');
            $siswas = $this->rapor->getSiswaLulus($tp_before->id_tp, '2', $level);
        }

        $ids = [];
        $this->db->trans_start();
        if (count($siswas) > 0) {
            foreach ($siswas as $siswa) {
                if ($siswa->naik != null && $siswa->naik == '0') {
                } else {
                    $ids[] = $siswa->id_siswa;

                    $this->db->where('id_siswa', $siswa->id_siswa);
                    $this->db->set('status', '2');
                    $this->db->set('tahun_lulus', $splitTahun[1]);
                    $this->db->set('no_ijazah', '- -');
                    $this->db->set('kelas_akhir', $siswa->kelas_akhir);
                    $this->db->update('buku_induk');

                }
            }
        }

        $this->db->trans_complete();

        $this->output_json($ids);
    }

    public function detail($id)
    {
        $alumni = $this->master->getAlumniById($id);
        $inputData = [
            [
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'value' => $alumni->nama,
                'icon' => 'bi bi-user',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'NIS',
                'name' => 'nis',
                'value' => $alumni->nis,
                'icon' => 'bi bi-id-card',
                'class' => '',
                'type' => 'number'
            ],
            [
                'name' => 'nisn',
                'label' => 'NISN',
                'value' => $alumni->nisn,
                'icon' => 'bi bi-id-card',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'value' => $alumni->jenis_kelamin,
                'icon' => 'bi bi-venus-mars',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'kelas_awal',
                'label' => 'Diterima di kelas',
                'value' => $alumni->kelas_awal,
                'icon' => 'bi bi-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tahun_masuk',
                'label' => 'Tgl diterima',
                'value' => $alumni->tahun_masuk,
                'icon' => 'tahun bi bi-calendar-alt',
                'class' => 'tahun',
                'type' => 'text'
            ]
        ];
        $inputBio = [
            [
                'name' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'value' => $alumni->tempat_lahir,
                'icon' => 'bi bi-map',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'value' => $alumni->tanggal_lahir,
                'icon' => 'bi bi-calendar',
                'class' => 'tahun',
                'type' => 'text'
            ],
            ['class' => '', 'name' => 'agama', 'label' => 'Agama', 'value' => $alumni->agama, 'icon' => 'bi bi-calendar', 'type' => 'text'],
            ['class' => '', 'name' => 'alamat', 'label' => 'Alamat', 'value' => $alumni->alamat, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rt', 'label' => 'Rt', 'value' => $alumni->rt, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rw', 'label' => 'Rw', 'value' => $alumni->rw, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kelurahan', 'label' => 'Kelurahan/Desa', 'value' => $alumni->kelurahan, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kecamatan', 'label' => 'Kecamatan', 'value' => $alumni->kecamatan, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kabupaten', 'label' => 'Kabupaten/Kota', 'value' => $alumni->kabupaten, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kode_pos', 'label' => 'Kode Pos', 'value' => $alumni->kode_pos, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'hp', 'label' => 'Hp', 'value' => $alumni->hp, 'icon' => 'bi bi-user', 'type' => 'text']
        ];

        $inputOrtu = [['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $alumni->nama_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pendidikan_ayah', 'label' => 'Pendidikan Ayah', 'value' => $alumni->pendidikan_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'value' => $alumni->pekerjaan_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nohp_ayah', 'label' => 'No. HP Ayah', 'value' => $alumni->nohp_ayah, 'icon' => 'bi bi-user', 'type' => 'number'], ['name' => 'alamat_ayah', 'label' => 'Alamat Ayah', 'value' => $alumni->alamat_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $alumni->nama_ibu, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pendidikan_ibu', 'label' => 'Pendidikan Ibu', 'value' => $alumni->pendidikan_ibu, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'value' => $alumni->pekerjaan_ibu, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nohp_ibu', 'label' => 'No. HP Ibu', 'value' => $alumni->nohp_ibu, 'icon' => 'bi bi-user', 'type' => 'number'], ['name' => 'alamat_ibu', 'label' => 'Alamat Ibu', 'value' => $alumni->alamat_ibu, 'icon' => 'bi bi-user', 'type' => 'text']];

        $inputWali = [['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $alumni->nama_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pendidikan_wali', 'label' => 'Pendidikan Wali', 'value' => $alumni->pendidikan_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pekerjaan_wali', 'label' => 'Pekerjaan Wali', 'value' => $alumni->pekerjaan_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'alamat_wali', 'label' => 'Alamat Wali', 'value' => $alumni->alamat_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nohp_wali', 'label' => 'No. HP Wali', 'value' => $alumni->nohp_wali, 'icon' => 'bi bi-user', 'type' => 'number']];
        $user = $this->auth->user();
        $data = ['user' => $user, 'judul' => 'Alumni', 'subjudul' => 'Edit Data Alumni', 'alumni' => $alumni, 'setting' => $this->dashboard->getSetting()];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['input_data'] = json_decode(json_encode($inputData), FALSE);
        $data['input_bio'] = json_decode(json_encode($inputBio), FALSE);
        $data['input_ortu'] = json_decode(json_encode($inputOrtu), FALSE);
        $data['input_wali'] = json_decode(json_encode($inputWali), FALSE);

        $this->render_view(1, 'master/alumni/edit', $data);
    }

    public function add()
    {
        $user = $this->auth->user();
        $data = ['user' => $user, 'judul' => 'Alumni', 'subjudul' => 'Tambah Data Alumni', 'setting' => $this->dashboard->getSetting()];
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['tipe'] = 'add';
        $this->render_view(1, 'master/alumni/add', $data);
    }

    public function create()
    {
        $nis = $this->input->post('nis', true);
        $nisn = $this->input->post('nisn', true);

        $u_nis = '|is_unique[master_siswa.nis]';
        $u_nisn = '|is_unique[master_siswa.nisn]';

        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[6]|max_length[20]' . $u_nisn);

        if ($this->form_validation->run() == FALSE) {
            $data['insert'] = false;
            $data['text'] = 'Data Sudah ada, Pastikan NIS, NISN dan Username belum digunakan alumni lain';
        } else {
            $insert = [
                "nama" => $this->input->post('nama_alumni', true),
                "nis" => $nis,
                "nisn" => $nisn,
                "jenis_kelamin" => $this->input->post('jenis_kelamin', true),
                "foto" => 'uploads/foto_siswa/' . $nis . 'jpg',
            ];

            $this->db->set('uid', 'UUID()', FALSE);
            $this->db->insert('master_siswa', $insert);
            $last_id = $this->db->insert_id();
            $uid = $this->db->select('uid')->from('master_siswa')->where('id_siswa', $last_id)->get()->row();

            $induk = [
                "id_siswa" => $last_id,
                "uid" => $uid->uid,
                "kelas_akhir" => $this->input->post('kelas_akhir', true),
                "tahun_lulus" => $this->input->post('tahun_lulus', true),
                "no_ijazah" => $this->input->post('no_ijazah', true),
                "status" => 2
            ];

            $data['insert'] = $this->db->insert('buku_induk', $induk);
            $data['text'] = 'Alumni berhasil ditambahkan';
        }
        $this->output_json($data);
    }

    public function edit()
    {
        $id = $this->input->get('id', true);
        $alumni = $this->master->getAlumniById($id);
        $inputData = [
            [
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'value' => $alumni->nama,
                'icon' => 'bi bi-user',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'NIS',
                'name' => 'nis',
                'value' => $alumni->nis,
                'icon' => 'bi bi-id-card',
                'class' => '',
                'type' => 'number'
            ],
            [
                'name' => 'nisn',
                'label' => 'NISN',
                'value' => $alumni->nisn,
                'icon' => 'bi bi-id-card',
                'class' => '',
                'type' => 'text'
            ],
            [
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'value' => $alumni->jenis_kelamin,
                'icon' => 'bi bi-venus-mars',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'kelas_awal',
                'label' => 'Diterima di kelas',
                'value' => $alumni->kelas_awal,
                'icon' => 'bi bi-graduation-cap',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tahun_masuk',
                'label' => 'Tgl diterima',
                'value' => $alumni->tahun_masuk,
                'icon' => 'tahun bi bi-calendar-alt',
                'class' => 'tahun',
                'type' => 'text'
            ]
        ];
        $inputBio = [
            [
                'name' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'value' => $alumni->tempat_lahir,
                'icon' => 'bi bi-map',
                'class' => '',
                'type' => 'text'
            ],
            [
                'name' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'value' => $alumni->tanggal_lahir,
                'icon' => 'bi bi-calendar',
                'class' => 'tahun',
                'type' => 'text'
            ],
            ['class' => '', 'name' => 'agama', 'label' => 'Agama', 'value' => $alumni->agama, 'icon' => 'bi bi-calendar', 'type' => 'text'],
            ['class' => '', 'name' => 'alamat', 'label' => 'Alamat', 'value' => $alumni->alamat, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rt', 'label' => 'Rt', 'value' => $alumni->rt, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'rw', 'label' => 'Rw', 'value' => $alumni->rw, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kelurahan', 'label' => 'Kelurahan/Desa', 'value' => $alumni->kelurahan, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kecamatan', 'label' => 'Kecamatan', 'value' => $alumni->kecamatan, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kabupaten', 'label' => 'Kabupaten/Kota', 'value' => $alumni->kabupaten, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'kode_pos', 'label' => 'Kode Pos', 'value' => $alumni->kode_pos, 'icon' => 'bi bi-user', 'type' => 'text'],
            ['class' => '', 'name' => 'hp', 'label' => 'Hp', 'value' => $alumni->hp, 'icon' => 'bi bi-user', 'type' => 'text']
        ];

        $inputOrtu = [['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'value' => $alumni->nama_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pendidikan_ayah', 'label' => 'Pendidikan Ayah', 'value' => $alumni->pendidikan_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'value' => $alumni->pekerjaan_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nohp_ayah', 'label' => 'No. HP Ayah', 'value' => $alumni->nohp_ayah, 'icon' => 'bi bi-user', 'type' => 'number'], ['name' => 'alamat_ayah', 'label' => 'Alamat Ayah', 'value' => $alumni->alamat_ayah, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'value' => $alumni->nama_ibu, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pendidikan_ibu', 'label' => 'Pendidikan Ibu', 'value' => $alumni->pendidikan_ibu, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'value' => $alumni->pekerjaan_ibu, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nohp_ibu', 'label' => 'No. HP Ibu', 'value' => $alumni->nohp_ibu, 'icon' => 'bi bi-user', 'type' => 'number'], ['name' => 'alamat_ibu', 'label' => 'Alamat Ibu', 'value' => $alumni->alamat_ibu, 'icon' => 'bi bi-user', 'type' => 'text']];

        $inputWali = [['name' => 'nama_wali', 'label' => 'Nama Wali', 'value' => $alumni->nama_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pendidikan_wali', 'label' => 'Pendidikan Wali', 'value' => $alumni->pendidikan_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'pekerjaan_wali', 'label' => 'Pekerjaan Wali', 'value' => $alumni->pekerjaan_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'alamat_wali', 'label' => 'Alamat Wali', 'value' => $alumni->alamat_wali, 'icon' => 'bi bi-user', 'type' => 'text'], ['name' => 'nohp_wali', 'label' => 'No. HP Wali', 'value' => $alumni->nohp_wali, 'icon' => 'bi bi-user', 'type' => 'number']];
        $user = $this->auth->user();
        $data = ['user' => $user, 'judul' => 'Alumni', 'subjudul' => 'Edit Data Alumni', 'alumni' => $alumni, 'setting' => $this->dashboard->getSetting()];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['input_data'] = json_decode(json_encode($inputData), FALSE);
        $data['input_bio'] = json_decode(json_encode($inputBio), FALSE);
        $data['input_ortu'] = json_decode(json_encode($inputOrtu), FALSE);
        $data['input_wali'] = json_decode(json_encode($inputWali), FALSE);

        $this->render_view(1, 'master/alumni/edit', $data);
    }

    public function batalkan()
    {
        $id = $this->input->post('id', true);
        $this->db->set('kelas_akhir', null);
        $this->db->set('tgl_pindah', null);
        $this->db->set('status', '1');
        $this->db->where('id_siswa', $id);
        $this->db->update('buku_induk');

        $this->output_json(['status' => true, 'message' => 'Data alumni berhasil diaktifkan kembali']);
    }

    public function updateData()
    {
        $id_siswa = $this->input->post('id_siswa', true);
        $nis = $this->input->post('nis', true);
        $nisn = $this->input->post('nisn', true);

        $alumni = $this->master->getAlumniById($id_siswa);
        $u_nis = $alumni->nis == $nis ? "" : "|is_unique[mater_alumni.nis]";
        $u_nisn = $alumni->nisn == $nisn ? "" : "|is_unique[mater_alumni.nisn]";

        $this->form_validation->set_rules('nis', 'NIS', 'required|numeric|trim|min_length[6]|max_length[30]' . $u_nis);
        $this->form_validation->set_rules('nisn', 'NISN', 'required|numeric|trim|min_length[6]|max_length[20]' . $u_nisn);


        if ($this->form_validation->run() == FALSE) {
            $data['insert'] = false;
            $data['text'] = 'Data Sudah ada, Pastikan NIS, dan NISN belum digunakan alumni lain';
        } else {
            /*
            $alumni->nisn            = $this->input->post('nisn', true);
            $alumni->nis             = $this->input->post('nis', true);
            $alumni->nama            = $this->input->post('nama', true);
            $alumni->jenis_kelamin   = $this->input->post('jenis_kelamin', true);
            $alumni->tempat_lahir    = $this->input->post('tempat_lahir', true);
            $alumni->tanggal_lahir   = $this->input->post('tanggal_lahir', true);
            $alumni->agama           = $this->input->post('agama', true);
            $alumni->status_keluarga = $this->input->post('status_keluarga', true);
            $alumni->anak_ke         = $this->input->post('anak_ke', true);
            $alumni->alamat	        = $this->input->post('alamat', true);
            $alumni->rt	=	$this->input->post('rt', true);
            $alumni->rw	=	$this->input->post('rw', true);
            $alumni->kelurahan	= $this->input->post('kelurahan', true);
            $alumni->kecamatan	= $this->input->post('kecamatan', true);
            $alumni->kabupaten	= $this->input->post('kabupaten', true);
            $alumni->provinsi	=	$this->input->post('provinsi', true);
            $alumni->kode_pos	=	$this->input->post('kode_pos', true);
            $alumni->hp	=	$this->input->post('hp', true);
            $alumni->nama_ayah	=	$this->input->post('nama_ayah', true);
            $alumni->nohp_ayah	=	$this->input->post('nohp_ayah', true);
            $alumni->pendidikan_ayah	=	$this->input->post('pendidikan_ayah', true);
            $alumni->pekerjaan_ayah	=	$this->input->post('pekerjaan_ayah', true);
            $alumni->alamat_ayah	=	$this->input->post('alamat_ayah = ', true);
            $alumni->nama_ibu	=	$this->input->post('nama_ibu = ', true);
            $alumni->nohp_ibu	=	$this->input->post('nohp_ibu', true);
            $alumni->pendidikan_ibu	=	$this->input->post('pendidikan_ibu', true);
            $alumni->pekerjaan_ibu	=	$this->input->post('pekerjaan_ibu', true);
            $alumni->alamat_ibu	=	$this->input->post('alamat_ibu', true);
            $alumni->nama_wali	=	$this->input->post('nama_wali', true);
            $alumni->pendidikan_wali	=	$this->input->post('pendidikan_wali', true);
            $alumni->pekerjaan_wali	=	$this->input->post('pekerjaan_wali', true);
            $alumni->nohp_wali	=	$this->input->post('nohp_wali', true);
            $alumni->alamat_wali	=	$this->input->post('alamat_wali', true);
            $alumni->tahun_masuk	=	$this->input->post('tahun_masuk', true);
            $alumni->kelas_awal	=	$this->input->post('kelas_awal', true);
            $alumni->tgl_lahir_ayah	=	$this->input->post('tgl_lahir_ayah', true);
            $alumni->tgl_lahir_ibu	=	$this->input->post('tgl_lahir_ibu', true);
            $alumni->tgl_lahir_wali	=	$this->input->post('tgl_lahir_wali', true);
            $alumni->sekolah_asal	=	$this->input->post('sekolah_asal', true);
            $alumni->foto	=	'uploads/foto_siswa/'.$nis.'.jpg';
            */
            $input = [
                'nisn' => $this->input->post('nisn', true),
                'nis' => $this->input->post('nis', true),
                'nama' => $this->input->post('nama', true),
                'jenis_kelamin' => $this->input->post('jenis_kelamin', true),
                'tempat_lahir' => $this->input->post('tempat_lahir', true),
                'tanggal_lahir' => $this->input->post('tanggal_lahir', true),
                'agama' => $this->input->post('agama', true),
                'status_keluarga' => $this->input->post('status_keluarga', true),
                'anak_ke' => $this->input->post('anak_ke', true),
                'alamat' => $this->input->post('alamat', true),
                'rt' => $this->input->post('rt', true),
                'rw' => $this->input->post('rw', true),
                'kelurahan' => $this->input->post('kelurahan', true),
                'kecamatan' => $this->input->post('kecamatan', true),
                'kabupaten' => $this->input->post('kabupaten', true),
                'provinsi' => $this->input->post('provinsi', true),
                'kode_pos' => $this->input->post('kode_pos', true),
                'hp' => $this->input->post('hp', true),
                'nama_ayah' => $this->input->post('nama_ayah', true),
                'nohp_ayah' => $this->input->post('nohp_ayah', true),
                'pendidikan_ayah' => $this->input->post('pendidikan_ayah', true),
                'pekerjaan_ayah' => $this->input->post('pekerjaan_ayah', true),
                'alamat_ayah' => $this->input->post('alamat_ayah', true),
                'nama_ibu' => $this->input->post('nama_ibu', true),
                'nohp_ibu' => $this->input->post('nohp_ibu', true),
                'pendidikan_ibu' => $this->input->post('pendidikan_ibu', true),
                'pekerjaan_ibu' => $this->input->post('pekerjaan_ibu', true),
                'alamat_ibu' => $this->input->post('alamat_ibu', true),
                'nama_wali' => $this->input->post('nama_wali', true),
                'pendidikan_wali' => $this->input->post('pendidikan_wali', true),
                'pekerjaan_wali' => $this->input->post('pekerjaan_wali', true),
                'nohp_wali' => $this->input->post('nohp_wali', true),
                'alamat_wali' => $this->input->post('alamat_wali', true),
                'tahun_masuk' => $this->input->post('tahun_masuk', true),
                'kelas_awal' => $this->input->post('kelas_awal', true),
                'tgl_lahir_ayah' => $this->input->post('tgl_lahir_ayah', true),
                'tgl_lahir_ibu' => $this->input->post('tgl_lahir_ibu', true),
                'tgl_lahir_wali' => $this->input->post('tgl_lahir_wali', true),
                'sekolah_asal' => $this->input->post('sekolah_asal', true),
                'foto' => 'uploads/foto_siswa/' . $nis . '.jpg'
            ];

            $action = $this->master->update('master_siswa', $input, 'id_siswa', $id_siswa);
            $data['insert'] = $input;
            $data['text'] = 'Alumni berhasil diperbaharui';
        }

        $this->output_json($data);
    }

    function uploadFile($id_siswa)
    {
        $alumni = $this->master->getAlumniById($id_siswa);
        if (isset($_FILES["foto"]["name"])) {

            $config['upload_path'] = './uploads/foto_siswa/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|JPEG|JPG|PNG|GIF';
            $config['overwrite'] = true;
            //$config['encrypt_name'] = TRUE;
            $config['file_name'] = $alumni->nis;

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('foto')) {
                $data['status'] = false;
                $data['src'] = $this->upload->display_errors();
            } else {
                $result = $this->upload->data();
                $data['src'] = base_url() . 'uploads/foto_siswa/' . $result['file_name'];
                $data['filename'] = pathinfo($result['file_name'], PATHINFO_FILENAME);
                $data['status'] = true;

                $this->db->set('foto', 'uploads/foto_siswa/' . $result['file_name']);
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

    function deleteFoto()
    {
        $src = $this->input->post('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        unlink($file_name);
        echo 'File Delete Successfully';
    }

    public function delete()
    {
        $id = $this->input->post('id', true);
        if (!$id) {
            $this->output_json(['status' => false, 'message' => 'Tidak ada siswa yang dipilih']);
        } else {
            if ($this->master->delete('master_siswa', $id, 'id_siswa')) {
                // ==> hapus rapor
                // rapor_catatan_wali
                $this->master->delete('rapor_catatan_wali', $id, 'id_siswa');
                // rapor_fisik
                $this->master->delete('rapor_fisik', $id, 'id_siswa');
                // rapor_naik
                $this->master->delete('rapor_naik', $id, 'id_siswa');
                // rapor_nilai_akhir
                $this->master->delete('rapor_nilai_akhir', $id, 'id_siswa');
                // rapor_nilai_ekstra
                $this->master->delete('rapor_nilai_ekstra', $id, 'id_siswa');
                // rapor_nilai_harian
                $this->master->delete('rapor_nilai_harian', $id, 'id_siswa');
                // rapor_nilai_pts
                $this->master->delete('rapor_nilai_pts', $id, 'id_siswa');
                // rapor_nilai_sikap
                $this->master->delete('rapor_nilai_sikap', $id, 'id_siswa');
                // rapor_prestasi
                $this->master->delete('rapor_prestasi', $id, 'id_siswa');

                // ==> hapus BUKU INDUK
                $status = $this->master->delete('buku_induk', $id, 'id_siswa');
                $this->output_json(['status' => $status, 'message' => 'Data alumni berhasil dihapus']);
            } else {
                $this->output_json(['status' => false, 'message' => 'Data alumni gagal dihapus']);
            }
        }
    }

    public function do_import()
    {
        $input = json_decode($this->input->post('alumni', true));
        $this->db->trans_start();
        foreach ($input as $key1 => $val1) {
            $data = [];
            foreach (((array) $input)[$key1] as $key => $val) {
                $data[$key] = $val;
            }
            $data['foto'] = 'uploads/foto_siswa/' . $data['nis'] . '.jpg';
            $save = $this->db->insert('master_siswa', $data);
        }
        $this->db->trans_complete();
        $this->output->set_content_type('application/json')->set_output($save);
    }

    public function editKelulusan()
    {
        $id_siswa = $this->input->post('id_siswa', true);
        $thn = $this->input->post('tahun_lulus', true);
        $no_ijazah = $this->input->post('no_ijazah', true);
        $kelas_akhir = $this->input->post('kelas_akhir', true);

        $this->db->set('kelas_akhir', $kelas_akhir);
        $this->db->set('tahun_lulus', $thn);
        $this->db->set('no_ijazah', $no_ijazah);
        $this->db->where('id_siswa', $id_siswa);
        $status = $this->db->update('buku_induk');

        $data['status'] = $status;
        $this->output_json($data);
    }

}
