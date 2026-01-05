<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dataguru extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin()) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }
        $this->load->library(['datatables', 'form_validation']); // Load Library Ignited-Datatables
        $this->form_validation->set_error_delimiters('', '');
    }

    public function index()
    {
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $mode = $this->input->get('mode', true);
        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Guru',
            'subjudul' => 'Data Guru',
            'profile' => null,
            'setting' => $setting
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['mode'] = $mode == null ? '1' : '2';
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $mapels = $this->master->getAllMapel();
        $ret = [];
        if ($mapels) {
            foreach ($mapels as $key => $row) {
                $ret[$row->id_mapel] = $row;
            }
        }
        $data['mapels'] = $ret;
        $data['extras'] = $this->dropdown->getAllKodeEkskul();

        $data['kelass'] = $this->master->getAllKelas($tp->id_tp, $smt->id_smt);
        $gurus = $this->master->getAllDataGuru($tp->id_tp, $smt->id_smt);
        foreach ($gurus as $guru) {
            $guru->mapel_kelas = $this->maybe_unserialize($guru->mapel_kelas ?? '[]');
            $guru->ekstra_kelas = $this->maybe_unserialize($guru->ekstra_kelas ?? '[]');
        }
        $data['gurus'] = $gurus;

        $this->render_view(1, 'master/guru/data', $data);
    }

    public function data()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $this->output_json($this->master->getDataGuru($tp->id_tp, $smt->id_smt), false);
    }

    public function edit($id)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();
        $guru = $this->master->getGuruById($id, $tp->id_tp, $smt->id_smt);
        $id_guru = $id;

        $data = [
            'user' => $user,
            'judul' => 'Edit Guru',
            'subjudul' => 'Edit Data Guru',
            'mapel' => $this->master->getAllMapel(),
            'guru' => $guru,
            'profile' => null,
            'setting' => $setting
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;
        $data['id_active'] = $id;

        $inputsProfile = [
            [
                'label' => 'Nama Lengkap',
                'name' => 'nama_guru',
                'value' => $guru->nama_guru,
                'icon' => 'bi bi-person',
                'type' => 'text'
            ],
            [
                'label' => 'Email',
                'name' => 'email',
                'value' => $guru->email,
                'icon' => 'bi bi-envelope',
                'type' => 'text'
            ],
            [
                'label' => 'NIP / NUPTK',
                'name' => 'nip',
                'value' => $guru->nip,
                'icon' => 'bi bi-person-badge',
                'type' => 'text'
            ],
            [
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'value' => $guru->jenis_kelamin,
                'icon' => 'bi bi-gender-ambiguous',
                'type' => 'text'
            ],
            [
                'label' => 'No. Handphone',
                'name' => 'no_hp',
                'value' => $guru->no_hp,
                'icon' => 'bi bi-telephone',
                'type' => 'number'
            ],
            [
                'label' => 'Agama',
                'name' => 'agama',
                'value' => $guru->agama,
                'icon' => 'bi bi-person',
                'type' => 'text'
            ],
            [
                'label' => 'Google API Key',
                'name' => 'api_key',
                'value' => $guru->api_key ?? '',
                'icon' => 'bi bi-key',
                'type' => 'text'
            ],
        ];
        $inputsAlamat = [
            [
                'label' => 'NIK',
                'name' => 'no_ktp',
                'value' => $guru->no_ktp,
                'icon' => 'bi bi-person-badge',
                'type' => 'number'
            ],
            [
                'label' => 'Tempat Lahir',
                'name' => 'tempat_lahir',
                'value' => $guru->tempat_lahir,
                'icon' => 'bi bi-geo-alt',
                'type' => 'text'
            ],
            [
                'label' => 'Tgl. Lahir',
                'name' => 'tgl_lahir',
                'value' => $guru->tgl_lahir,
                'icon' => 'bi bi-calendar-date',
                'type' => 'text'
            ],
            [
                'label' => 'Alamat',
                'name' => 'alamat_jalan',
                'value' => $guru->alamat_jalan,
                'icon' => 'bi bi-geo-alt',
                'type' => 'text'
            ],
            [
                'label' => 'Kecamatan',
                'name' => 'kecamatan',
                'value' => $guru->kecamatan,
                'icon' => 'bi bi-geo-alt',
                'type' => 'text'
            ],
            [
                'label' => 'Kota/Kab.',
                'name' => 'kabupaten',
                'value' => $guru->kabupaten,
                'icon' => 'bi bi-geo-alt',
                'type' => 'text'
            ],
            [
                'label' => 'Provinsi',
                'name' => 'provinsi',
                'value' => $guru->provinsi,
                'icon' => 'bi bi-geo-alt',
                'type' => 'text'
            ],
            [
                'label' => 'Kode Pos',
                'name' => 'kode_pos',
                'value' => $guru->kode_pos,
                'icon' => 'bi bi-envelope',
                'type' => 'number'
            ],
        ];
        $inputsDokumen = [
            [
                'label' => 'KTP',
                'id' => 'ktp_id',
                'name' => 'ktp_id',
                'value' => '',
                'type' => 'file'
            ],
            [
                'label' => 'KK',
                'id' => 'kk_id',
                'name' => 'kk_id',
                'value' => '',
                'type' => 'file'
            ],
            [
                'label' => 'Sertifikat',
                'id' => 'sertifikat',
                'name' => 'sertifikat[]',
                'value' => '',
                'type' => 'file'
            ]
        ];

        $data['id_guru'] = $id_guru;
        $data['input_profile'] = json_decode(json_encode($inputsProfile), FALSE);
        $data['input_alamat'] = json_decode(json_encode($inputsAlamat), FALSE);
        $data['input_dokumen'] = json_decode(json_encode($inputsDokumen), FALSE);
        $this->render_view(1, 'master/guru/edit', $data);
    }

    public function create()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $nip = $this->input->post('nip', true);
        $nama_guru = $this->input->post('nama_guru', true);
        $username = $this->input->post('username', true);
        $password = $this->input->post('password', true);

        $u_nip = 'is_unique[master_guru.nip]';
        $u_username = '|is_unique[master_guru.username]';

        $this->form_validation->set_rules('nip', 'NIP', 'numeric|trim|' . $u_nip);
        $this->form_validation->set_rules('nama_guru', 'Nama Guru', 'required|trim|min_length[2]');
        $this->form_validation->set_rules('username', 'Username', 'required|trim' . $u_username);
        $this->form_validation->set_rules('password', 'Password', 'required');

        if (!$this->form_validation->run()) {
            $data = [
                'status' => false,
                'errors' => [
                    'nip' => form_error('nip'),
                    'nama_guru' => form_error('nama_guru'),
                    'username' => form_error('username'),
                    'password' => form_error('password'),
                ]
            ];
            $this->output_json($data);
        } else {
            $nama = explode(' ', $nama_guru ?? '');
            $first_name = $nama[0];
            $last_name = end($nama);
            $user = [
                'username' => trim($username ?? ''),
                'password' => password_hash(trim($password ?? ''), PASSWORD_BCRYPT),
                'first_name' => $first_name,
                'last_name' => $last_name
            ];
            $id_user = $this->auth->register($user, '2');
            $input = [
                'id_user' => $id_user,
                'nip' => trim($nip ?? ''),
                'nama_guru' => trim($nama_guru ?? ''),
                'username' => trim($username ?? ''),
                'password' => trim($password ?? ''),
                'foto' => 'uploads/profile_guru/' . trim($nip ?? '00') . '.jpg',
                'api_key' => trim($this->input->post('api_key', true)),
                'sync_school_uuid' => $this->session->userdata('school_uuid')
            ];

            $action = $this->master->create('master_guru', $input);
            $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menambah guru: ' . $nama_guru);

            if ($action) {
                $this->output_json(['status' => true]);
            } else {
                $this->output_json(['status' => false]);
            }
        }
    }

    public function save()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $method = $this->input->post('method', true);
        $id_guru = $this->input->post('id_guru', true);
        $nip = $this->input->post('nip', true);
        $nama_guru = $this->input->post('nama_guru', true);
        $email = $this->input->post('email', true);
        $password = $this->input->post('password', true);
        if ($method == 'add') {
            $u_nip = '|is_unique[guru.nip]';
            $u_email = '|is_unique[guru.email]';
        } else {
            $dbdata = $this->master->getGuruById($id_guru);
            $u_nip = $dbdata->nip == $nip ? "" : "|is_unique[guru.nip]";
            $u_email = $dbdata->email == $email ? "" : "|is_unique[guru.email]";
        }
        $this->form_validation->set_rules('nip', 'NIP', 'trim|min_length[5]' . $u_nip);
        $this->form_validation->set_rules('nama_guru', 'Nama Guru', 'required|trim|min_length[3]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email' . $u_email);
        $this->form_validation->set_rules('password', 'Password', 'required');

        if (!$this->form_validation->run()) {
            $data = [
                'status' => false,
                'errors' => [
                    'nip' => form_error('nip'),
                    'nama_guru' => form_error('nama_guru'),
                    'email' => form_error('email'),
                    'mapel' => form_error('mapel'),
                ]
            ];
            $this->output_json($data);
        } else {
            $input = [
                'nip' => $nip,
                'nama_guru' => $nama_guru,
                'email' => $email,
                'api_key' => $this->input->post('api_key', true),
                //'mapel_id' 	    => $mapel
            ];
            $action = true;
            $action = true;
            if ($method == 'add') {
                $input['sync_school_uuid'] = $this->session->userdata('school_uuid');
                $action = $this->master->create('master_guru', $input);
                $this->logging->saveLog($this->LOG_TYPE->ADD, 'Menambah guru: ' . $nama_guru);
            } else if ($method == 'edit') {
                $action = $this->master->update('master_guru', $input, 'id_guru', $id_guru);
                $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengedit profile guru: ' . $nama_guru);
            }

            if ($action) {
                $this->output_json(['status' => true]);
            } else {
                $this->output_json(['status' => false]);
            }
        }
    }

    public function deleteGuru()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');

        $chk = $this->input->post('id_guru', true);
        $messages = [];
        $tables = [];
        $tabless = $this->db->list_tables();
        foreach ($tabless as $table) {
            $fields = $this->db->field_data($table);
            foreach ($fields as $field) {
                if ($field->name == 'id_guru' || $field->name == 'guru_id')
                    $tables[] = $table;
            }
        }

        foreach ($tables as $table) {
            if ($table != 'master_guru') {
                if ($table == 'master_kelas') {
                    $this->db->where('guru_id', $chk);
                } else {
                    $this->db->where('id_guru', $chk);
                }
                $num = $this->db->count_all_results($table);
                if ($num > 0)
                    $messages[] = $table;
            }
        }

        if (count($messages) > 0) {
            $this->output_json([
                'count' => count($messages),
                'status' => false,
                'message' => 'Data guru digunakan di ' . count($messages) . ' tabel:<br>' . implode('<br>', $messages)
            ]);
        } else {
            $guru = $this->auth->userGuru($chk);
            $deleted = $this->master->delete('master_guru', $chk, 'id_guru');
            if ($deleted) {
                $deleted = $this->auth->delete_user($guru->id);
                $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Menghapus guru: ' . $guru->nama_guru);
            }
            $data['status'] = $deleted;
            $this->output_json($data);
        }
    }

    public function detail($id_guru)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Detail Guru',
            'subjudul' => 'Info Jabatan Guru',
            'mapel' => $this->master->getAllMapel(),
            'profile' => null,
            'setting' => $setting
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['kelas'] = $this->master->getAllKelas();

        $data['id_guru'] = $id_guru;
        $jabatan = $this->master->getDetailJabatanGuru($id_guru);
        foreach ($jabatan as $tps) {
            foreach ($tps as $guru) {
                $guru->mapel_kelas = $this->maybe_unserialize($guru->mapel_kelas ?? '[]');
                $guru->ekstra_kelas = $this->maybe_unserialize($guru->ekstra_kelas ?? '[]');
            }
        }

        $data['guru'] = [
            'detail' => $this->master->getGuruByArrId([$id_guru])[0],
            'jabatan' => $jabatan,
            'materi' => $this->db->get_where('kelas_materi', 'id_guru=' . $id_guru)->num_rows(),
            'catatan_mapel' => $this->db->get_where('kelas_catatan_mapel', 'id_guru=' . $id_guru)->num_rows(),
            'bank_soal' => $this->db->get_where('cbt_bank_soal', 'bank_guru_id=' . $id_guru)->num_rows(),
            'pengawas' => $this->db->get_where('cbt_pengawas', 'id_guru LIKE "%' . $id_guru . '%"')->num_rows(),
            'posts' => $this->db->get_where('post', 'dari=' . $id_guru)->num_rows(),
            'comments' => $this->db->get_where('post_comments', 'dari=' . $id_guru)->num_rows(),
            'replies' => $this->db->get_where('post_reply', 'dari=' . $id_guru)->num_rows()
        ];

        $this->render_view(1, 'master/guru/detail', $data);
    }

    public function delete()
    {
        $this->load->model('Log_model', 'logging');
        $this->load->model('Master_model', 'master');
        $chk = $this->input->post('checked', true);
        if (!$chk) {
            $this->output_json(['status' => false]);
        } else {
            if ($this->master->delete('master_guru', $chk, 'id_guru')) {
                $this->output_json(['status' => true, 'total' => count($chk)]);
                $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Menghapus guru: ' . implode(', ', $chk));
            }
        }
    }

    public function forceDelete()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Log_model', 'logging');
        $id_guru = $this->input->post('id_guru', true);
        $guru = $this->db->select('id_guru,nama_guru')->where('id_guru', $id_guru)->get('master_guru')->row();
        $this->logging->saveLog($this->LOG_TYPE->DELETE, 'Menghapus guru secara paksa: ' . $guru->nama_guru);

        $data['status'] = ($this->master->delete('master_guru', $id_guru, 'id_guru'));
        $data['guru'] = $guru->nama_guru;
        $this->output_json($data);
    }

    public function import($import_data = null)
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');

        $user = $this->auth->user();
        $setting = $this->dashboard->getSetting();
        $data = [
            'user' => $user,
            'judul' => 'Guru',
            'subjudul' => 'Tambah Data Guru',
            'mapel' => $this->master->getAllMapel(),
            'profile' => null,
            'setting' => $setting
        ];
        if ($import_data != null)
            $data['import'] = $import_data;

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        $this->render_view(1, 'master/guru/add', $data);
    }

    public function do_import()
    {
        $this->load->model('Log_model', 'logging');
        $this->load->model('Master_model', 'master');
        $input = $this->input->post('guru', true);
        $errors = [];
        foreach ($input as $guru) {
            $this->form_validation->set_data($guru);
            $this->form_validation->set_rules('2', 'Nama Guru', 'trim|min_length[1]|max_length[50]');
            $this->form_validation->set_rules('3', 'NIP', 'trim|min_length[6]|max_length[30]|is_unique[master_guru.nip]');
            $this->form_validation->set_rules('5', 'Username', 'required|trim|min_length[5]|max_length[30]|is_unique[master_guru.username]');
            $this->form_validation->set_rules('6', 'Password', 'required|trim|min_length[5]|max_length[30]');

            if (!$this->form_validation->run()) {
                $errors[] = [
                    'nama' => form_error('2'),
                    'nip' => form_error('3'),
                    'username' => form_error('5'),
                    'password' => form_error('6'),
                ];
            }
        }

        if (count($errors) > 0) {
            $data = [
                'status' => false,
                'errors' => $errors,
            ];
        } else {
            $data_insert = [];
            foreach ($input as $guru) {
                $foto = 'uploads/profile_guru/' . trim($guru['3'] ?? '00') . '.jpg';
                if (isset($guru['7'])) {
                    $base64_image_string = $guru['7'];
                    $extension = $guru['8'];
                    if ($extension == 'jpeg')
                        $extension = 'jpg';
                    $output_file = trim($guru['3'] ?? '00') . '.' . $extension;
                    file_put_contents('./uploads/profile_guru/' . $output_file, base64_decode($base64_image_string));
                    $foto = 'uploads/profile_guru/' . $output_file;
                }
                $nama_guru = trim($guru['2'] ?? '');
                $username = trim($guru['5'] ?? '');
                $password = trim($guru['6'] ?? '');

                $nama = explode(' ', $nama_guru ?? '');
                $first_name = $nama[0];
                $last_name = end($nama);
                $user = [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
                $id_user = $this->auth->register($user, '2');

                $data_insert[] = [
                    'id_user' => $id_user,
                    'nama_guru' => $nama_guru,
                    'nip' => trim($guru['3'] ?? ''),
                    'kode_guru' => trim($guru['4'] ?? ''),
                    'username' => $username,
                    'password' => $password,
                    'foto' => $foto
                ];
            }

            $save = $this->master->create('master_guru', $data_insert, true);
            if ($save) {
                $this->logging->saveLog($this->LOG_TYPE->ADD, 'Mengimpor data guru sebanyak: ' . count($data_insert));
            }
            $data = [
                'status' => true,
                'data' => $save,
                'insert' => $data_insert
            ];
        }
        $this->output_json($data);
    }

    public function editJabatan($id)
    {
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->master->getJabatanGuru($id, $tp->id_tp, $smt->id_smt);
        $guru->mapel_kelas = $this->maybe_unserialize($guru->mapel_kelas ?? '[]');
        $guru->ekstra_kelas = $this->maybe_unserialize($guru->ekstra_kelas ?? '[]');
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Jabatan Guru',
            'subjudul' => 'Edit Jabatan Guru',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];
        $data['guru'] = $guru;
        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $tp;
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $smt;

        if ($this->auth->is_admin()) {
            $data['groups'] = $this->auth->groups()->result();
        }
        //for dropdown
        $data['kelass'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['mapels'] = $this->dropdown->getAllMapel();
        $data['levels'] = $this->dropdown->getAllLevelGuru();
        $data['ekskul'] = $this->dropdown->getAllEkskul();
        $data['kur'] = $smt;

        $smt2 = $smt->id_smt == '1' ? '2' : '1';
        $tp2 = $smt->id_smt == '1' ? ($tp->id_tp) - 1 : $tp->id_tp;
        $guru_before = $this->master->getJabatanGuru($id, $tp2, $smt2);
        $guru_before->mapel_kelas = json_decode(json_encode($this->maybe_unserialize($guru_before->mapel_kelas ?? '')));
        $guru_before->ekstra_kelas = json_decode(json_encode($this->maybe_unserialize($guru_before->ekstra_kelas ?? '')));
        $data['before'] = [
            'kelass' => $this->dropdown->getAllKelas($tp2, $smt2),
            'guru' => $guru_before
        ];

        $this->render_view(1, 'master/guru/editmapel', $data);
    }

    public function saveJabatan()
    {
        $this->load->model('Dropdown_model', 'dropdown');
        $this->load->model('Master_model', 'master');
        $this->load->model('Kelas_model', 'kelas');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Log_model', 'logging');

        $id_guru = $this->input->post('id_guru', true);
        $id_level = $this->input->post('level', true);
        $wali = $this->input->post('kelas_wali', true);
        $copy = $this->input->post('copy', true) != null;

        $tp = $this->master->getTahunActive();
        $smt = $this->master->getSemesterActive();

        $smt_before = $smt->id_smt == '1' ? '2' : '1';
        $tp_before = $smt->id_smt == '1' ? ($tp->id_tp) - 1 : $tp->id_tp;

        $kelas_now = $this->kelas->getNamaKelasByNama($tp->id_tp, $smt->id_smt);
        $normalized_1 = [];
        foreach ($kelas_now as $kode => $value) {
            $normalized_1[$this->normalize($kode)] = $value;
        }
        $kelas_bfr = $this->dropdown->getAllKodeKelas($tp_before, $smt_before);

        if ($copy && isset($kelas_bfr[$wali])) {
            $tmp_wali = $kelas_bfr[$wali];
            $kelas_wali = $kelas_now[$tmp_wali] ?? 0;
        } else {
            $kelas_wali = $wali;
        }

        $mapels = [];
        $check_mapel = $this->input->post('mapel', true);
        if ($check_mapel) {
            $row_mapels = count($this->input->post('mapel', true));
            for ($i = 0; $i <= $row_mapels; $i++) {
                $mapel = $this->input->post('mapel[' . $i . ']', true);
                $nama_mapel = $this->input->post('nama_mapel' . $mapel, true);

                $check = $this->input->post('kelasmapel' . $mapel, true);
                if ($check) {
                    $row_kelas = count($this->input->post('kelasmapel' . $mapel, true));
                    $kelas = [];
                    for ($j = 0; $j <= $row_kelas; $j++) {
                        $kelasmapel = $this->input->post('kelasmapel' . $mapel . '[' . $j . ']', true);
                        if ($copy) {
                            if (isset($kelas_bfr[$kelasmapel])) {
                                $tmp_kode = $this->normalize($kelas_bfr[$kelasmapel]);
                                if (isset($normalized_1[$tmp_kode])) {
                                    $kelas[] = [
                                        'kelas' => $normalized_1[$tmp_kode]
                                    ];
                                }
                            }
                        } else {
                            $kelas[] = [
                                'kelas' => $kelasmapel
                            ];
                        }
                    }

                    $mapels[] = [
                        'id_mapel' => $mapel,
                        'nama_mapel' => $nama_mapel,
                        'kelas_mapel' => $kelas
                    ];
                }
            }
        }
        $kelas_mapel_guru = json_encode($mapels);

        $ekstras = [];
        $check_ekstra = $this->input->post('ekstra', true);
        if ($check_ekstra) {
            $row_ekstras = count($this->input->post('ekstra', true));
            for ($i = 0; $i <= $row_ekstras; $i++) {
                $ekstra = $this->input->post('ekstra[' . $i . ']', true);
                $nama_ekstra = $this->input->post('nama_ekstra' . $ekstra, true);

                $check = $this->input->post('kelasekstra' . $ekstra, true);
                if ($check) {
                    $row_kelas = count($this->input->post('kelasekstra' . $ekstra, true));
                    $kelas = [];
                    for ($j = 0; $j <= $row_kelas; $j++) {
                        $kelasekstra = $this->input->post('kelasekstra' . $ekstra . '[' . $j . ']', true);
                        if ($copy) {
                            if (isset($kelas_bfr[$kelasekstra])) {
                                $tmp_kode2 = $this->normalize($kelas_bfr[$kelasekstra]);
                                if (isset($normalized_1[$tmp_kode2])) {
                                    $kelas[] = [
                                        'kelas' => $normalized_1[$tmp_kode2]
                                    ];
                                }
                            }
                        } else {
                            $kelas[] = [
                                'kelas' => $kelasekstra
                            ];
                        }
                    }

                    $ekstras[] = [
                        'id_ekstra' => $ekstra,
                        'nama_ekstra' => $nama_ekstra,
                        'kelas_ekstra' => $kelas
                    ];
                }
            }
        }
        $kelas_ekstra_guru = json_encode($ekstras);

        $data = [
            'id_jabatan_guru' => $id_guru . $tp->id_tp . $smt->id_smt,
            'id_guru' => $id_guru,
            'id_jabatan' => $id_level ?? '',
            'id_kelas' => $kelas_wali == null ? 0 : $kelas_wali,
            'mapel_kelas' => $kelas_mapel_guru,
            'ekstra_kelas' => $kelas_ekstra_guru,
            'id_tp' => $tp->id_tp,
            'id_smt' => $smt->id_smt
        ];

        if ($this->input->post()) {
            $update = $this->db->replace('jabatan_guru', $data);
            $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengedit jabatan guru: ' . $id_guru . ' pada tahun ajaran: ' . $tp->id_tp . ' semester: ' . $smt->id_smt);
            $res['status'] = $update;
            $res['msg'] = $update ? 'Data berhasil disimpan' : 'Gagal menyimpan data';
        } else {
            $res['status'] = FALSE;
            $res['msg'] = 'Error post data';
        }
        $res['test'] = $normalized_1;
        $this->output_json($res);
    }

    private function normalize($str)
    {
        return strtolower(preg_replace('/[\s.\-_]/', '', $str ?? ''));
    }

    public function getDataKelas()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Users_model', 'users');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $jabatans = $this->master->getGuruMapel($tp->id_tp, $smt->id_smt);
        $mapel_terisi = [];
        $ekstra_terisi = [];
        $jbtn = [];
        foreach ($jabatans as $jabatan) {
            $mpl_kls = $jabatan->mapel_kelas = $this->maybe_unserialize($jabatan->mapel_kelas ?? '[]');
            $eks_kls = $jabatan->ekstra_kelas = $this->maybe_unserialize($jabatan->ekstra_kelas ?? '[]');
            foreach ($mpl_kls as $mpls) {
                $klss = [];
                foreach ($mpls->kelas_mapel as $mpl) {
                    $klss[] = $mpl->kelas;
                }
                $mapel_terisi[$mpls->id_mapel][$jabatan->id_guru] = [
                    'id_guru' => $jabatan->id_guru,
                    'guru' => $jabatan->nama_guru,
                    'kelas' => $klss
                ];
            }
            foreach ($eks_kls as $eks) {
                $klse = [];
                foreach ($eks->kelas_ekstra as $ek) {
                    $klse[] = $ek->kelas;
                }
                $ekstra_terisi[$eks->id_ekstra][$jabatan->id_guru] = [
                    'id_guru' => $jabatan->id_guru,
                    'guru' => $jabatan->nama_guru,
                    'kelas' => $klse
                ];
            }
            $jbtn[$jabatan->id_jabatan][$jabatan->id_kelas] = ['nama' => $jabatan->nama_guru, 'id' => $jabatan->id_guru];
        }
        $data['jabatan'] = $jbtn;
        $data['mpl_terisi'] = $mapel_terisi;
        $data['eks_terisi'] = $ekstra_terisi;
        $data['kelas'] = $this->users->getKelas($tp->id_tp, $smt->id_smt);
        $this->output_json($data);
    }

    public function addjabatan()
    {
        $this->load->model('Log_model', 'logging');
        $mode = $this->input->post('mode', true);
        $id = $this->input->post('id_level', true);
        $s_mode = $mode == '1' ? 'menyimpan' : 'menghapus';
        if ($mode == '1') {
            $insert = [
                'id_level' => $id,
                'level' => $this->input->post('level', true)
            ];
            $replaced = $this->db->replace('level_guru', $insert);
        } else {
            $replaced = $this->db->delete('level_guru', 'id_level=' . $id);
        }
        $this->logging->saveLog($this->LOG_TYPE->EDIT, $s_mode . ' jabatan guru dengan id: ' . $id);
        $data = [
            'success' => $replaced,
            'msg' => $replaced ? 'Sukses ' . $s_mode . ' jabatan' : 'Gagal ' . $s_mode . ' jabatan'
        ];
        $this->output_json($data);
    }

    public function update()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Update Data Guru',
            'subjudul' => 'Update Data Guru',
            'setting' => $this->dashboard->getSetting()
        ];

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data['tp_active'] = $tp;
        $data['smt_active'] = $smt;

        $data['tp'] = $this->dashboard->getTahun();
        $data['smt'] = $this->dashboard->getSemester();

        $data['tipe'] = 'update';
        $data['gurus'] = $this->dropdown->getAllGuru();

        $this->render_view(1, 'master/guru/update', $data);
    }

    public function downloadData()
    {
        $this->load->model('Master_model', 'master');

        $this->db->select('id_guru,nama_guru,jenis_kelamin,foto,username,password,nip')->from('master_guru');
        $result = $this->db->get()->result();

        foreach ($result as $ind => $guru) {
            $guru->no = ($ind + 1);
            $guru->foto = $guru->foto != null && $guru->foto != "" && file_exists($guru->foto) ? base_url($guru->foto) : base_url('assets/img/guru_xs.png');
            $guru->jenis_kelamin = $guru->jenis_kelamin ?? '';
        }
        $this->output_json(['status' => true, 'guru' => $result]);
    }

    public function update_all()
    {
        $this->load->model('Log_model', 'logging');

        $ids = $this->input->post('ids', true);
        $update_guru = $this->input->post('guru', true);
        $this->db->update_batch('master_guru', $update_guru, 'id_guru');

        $update_user = [];
        $users = $this->auth->usersGuru(json_decode($ids));
        foreach ($users as $guru) {
            $update_user[] = [
                'id' => $guru->id,
                'username' => $guru->username,
                'password' => password_hash($guru->password, PASSWORD_BCRYPT)
            ];
        }
        $this->db->update_batch('users', $update_user, 'id');
        $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengupdate data ' . count($update_guru) . ' guru');

        $data = [
            'status' => true,
            'user' => count($update_guru),
        ];
        $this->output_json($data);
    }

    public function update_foto()
    {
        $this->load->model('Log_model', 'logging');

        $input = $this->input->post('guru', true);
        $errors = [];
        $duplikat = [];

        foreach ($input as $value) {
            $this->form_validation->set_data($value);
            $this->form_validation->set_rules('nip', 'NIP', 'numeric|trim|min_length[6]|max_length[30]');
            if ($this->form_validation->run() == FALSE) {
                $duplikat[] = $value;
                $errors[$value['nama_guru']] = [
                    'nip' => form_error('nip'),
                ];
            }
        }

        if (count($errors) > 0) {
            $data = [
                'status' => false,
                'errors' => $errors,
                'duplikat' => $duplikat
            ];
        } else {
            $siswas = [];
            foreach ($input as $value) {
                $foto = 'uploads/foto_siswa/' . trim($value['nip'] ?? '00') . '.jpg';
                if (isset($value['foto'])) {
                    $base64_image_string = $value['foto'];
                    $extension = $value['ext'];
                    if ($extension == 'jpeg')
                        $extension = 'jpg';
                    $output_file = trim($value['nip'] ?? '00') . '.' . $extension;
                    file_put_contents('./uploads/profile_guru/' . $output_file, base64_decode($base64_image_string));
                    $foto = 'uploads/profile_guru/' . $output_file;
                }

                $siswas[] = [
                    "id_guru" => $value['id_guru'],
                    "nip" => $value["nip"] ?? '',
                    "nama_guru" => $value["nama"] ?? '',
                    "jenis_kelamin" => $value["jenis_kelamin"] ?? '',
                    "foto" => $foto
                ];
            }
            $this->db->update_batch('master_guru', $siswas, 'id_guru');
            $this->logging->saveLog($this->LOG_TYPE->EDIT, 'Mengupdate data ' . count($siswas) . ' guru');

            $data = [
                'status' => true,
                'user' => count($siswas),
            ];
        }
        $this->output_json($data);
    }

}
