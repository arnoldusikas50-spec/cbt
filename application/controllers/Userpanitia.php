<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Userpanitia extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } elseif (!$this->auth->is_admin()) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }

        $this->load->library(['form_validation']);
        $this->load->model('Users_model', 'users');
        $this->load->model('Panitia_model', 'panitia');
        $this->load->model('Master_model', 'master');
        $this->load->model('Dashboard_model', 'dashboard');
    }

    public function data()
    {
        $panitias = $this->users->getUserPanitia();
        $this->output_json($panitias);
    }

    public function index()
    {
        $user = $this->auth->user();
        $tp = $this->dashboard->getTahun();
        $tp_active = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemester();
        $smt_active = $this->dashboard->getSemesterActive();
        $data = [
            'user'      => $user,
            'judul'     => 'User Management',
            'subjudul'  => 'Data User Panitia',
            'profile'   => null,
            'setting'   => $this->dashboard->getSetting(),
            'is_admin'  => $this->auth->is_admin(),
            'tp'        => $tp,
            'tp_active' => $tp_active,
            'smt'       => $smt,
            'smt_active'=> $smt_active,
        ];

        $this->render_view(1, 'users/panitia/data', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            if (!$this->auth->is_admin()) {
                $this->output_json([
                    'status' => false,
                    'msg' => 'Unauthorized'
                ]);
                return;
            }

            $this->form_validation->set_rules('nama_panitia', 'Nama Panitia', 'required|trim');
            $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[4]|max_length[50]|is_unique[users.username]');
            $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[5]|max_length[50]');
            $this->form_validation->set_rules('email', 'Email', 'valid_email');
            $this->form_validation->set_rules('no_hp', 'No HP', 'numeric|min_length[10]|max_length[15]');
            $this->form_validation->set_message('is_unique', '{field} sudah digunakan');

            if ($this->form_validation->run() == FALSE) {
                $errors = [
                    'nama_panitia' => form_error('nama_panitia', '<p class="text-danger">', '</p>'),
                    'username' => form_error('username', '<p class="text-danger">', '</p>'),
                    'password' => form_error('password', '<p class="text-danger">', '</p>'),
                    'email' => form_error('email', '<p class="text-danger">', '</p>'),
                    'no_hp' => form_error('no_hp', '<p class="text-danger">', '</p>'),
                ];
                $this->output_json([
                    'status' => false,
                    'errors' => $errors
                ]);
                return;
            }

            $user_data = [
                'username' => $this->input->post('username'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'email' => $this->input->post('email'),
                'active' => 1,
                'first_name' => $this->input->post('nama_panitia'),
                'last_name' => ''
            ];

            $panitia_data = [
                'nama_panitia' => $this->input->post('nama_panitia'),
                'username' => $this->input->post('username'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'email' => $this->input->post('email'),
                'no_hp' => $this->input->post('no_hp'),
                'jabatan' => $this->input->post('jabatan'),
                'status' => 1
            ];

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $config['upload_path'] = './uploads/foto_panitia/';
                $config['allowed_types'] = 'jpg|jpeg|png';
                $config['max_size'] = 2048;
                $config['encrypt_name'] = TRUE;
                
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload('foto')) {
                    $upload_data = $this->upload->data();
                    $panitia_data['foto'] = 'uploads/foto_panitia/' . $upload_data['file_name'];
                }
            }

            $id_user = $this->auth->register($user_data, '4');
            if ($id_user) {
                $panitia_data['id_user'] = $id_user;
                $created = $this->panitia->create($panitia_data);
                $this->output_json(['status' => (bool)$created, 'msg' => $created ? 'Panitia baru berhasil ditambahkan' : 'Gagal menambahkan panitia']);
            } else {
                $this->output_json(['status' => false, 'msg' => 'Gagal membuat user panitia']);
            }
            return;
        }

        $user = $this->auth->user();
        $tp = $this->dashboard->getTahun();
        $tp_active = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemester();
        $smt_active = $this->dashboard->getSemesterActive();
        $data = [
            'user' => $user,
            'judul' => 'User Management',
            'subjudul' => 'Tambah User Panitia',
            'profile' => null,
            'setting' => $this->dashboard->getSetting(),
            'is_admin' => $this->auth->is_admin(),
            'tp' => $tp,
            'tp_active' => $tp_active,
            'smt' => $smt,
            'smt_active' => $smt_active,
        ];

        $this->render_view(1, 'users/panitia/create', $data);
    }

    public function edit()
    {
        if (!$this->auth->is_admin()) {
            $this->output_json([
                'status' => false,
                'msg' => 'Unauthorized'
            ]);
            return;
        }

        $id_panitia = $this->input->post('id_panitia', true);
        
        $this->form_validation->set_rules('nama_panitia', 'Nama Panitia', 'required|trim');
        $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[4]|max_length[50]|callback_check_username_edit['.$id_panitia.']');
        $this->form_validation->set_rules('email', 'Email', 'valid_email');
        $this->form_validation->set_rules('no_hp', 'No HP', 'numeric|min_length[10]|max_length[15]');
        $this->form_validation->set_rules('password', 'Password', 'min_length[5]|max_length[50]');

        if ($this->form_validation->run() == FALSE) {
            $errors = [
                'nama_panitia' => form_error('nama_panitia', '<p class="text-danger">', '</p>'),
                'username' => form_error('username', '<p class="text-danger">', '</p>'),
                'password' => form_error('password', '<p class="text-danger">', '</p>'),
                'email' => form_error('email', '<p class="text-danger">', '</p>'),
                'no_hp' => form_error('no_hp', '<p class="text-danger">', '</p>'),
            ];
            $this->output_json([
                'status' => false,
                'errors' => $errors
            ]);
            return;
        }

        $update_data = [
            'nama_panitia' => $this->input->post('nama_panitia'),
            'username' => $this->input->post('username'),
            'email' => $this->input->post('email'),
            'no_hp' => $this->input->post('no_hp'),
            'jabatan' => $this->input->post('jabatan'),
        ];

        $password = $this->input->post('password');
        if (!empty($password)) {
            $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle photo upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $config['upload_path'] = './uploads/foto_panitia/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048;
            $config['encrypt_name'] = TRUE;
            
            $this->load->library('upload', $config);
            
            if ($this->upload->do_upload('foto')) {
                $upload_data = $this->upload->data();
                $update_data['foto'] = 'uploads/foto_panitia/' . $upload_data['file_name'];
            }
        }

        $updated = $this->panitia->update($id_panitia, $update_data);
        
        if ($updated) {
            // Also update users table if user exists
            $panitia = $this->users->getDataPanitia($id_panitia);
            if ($panitia && $panitia->id_user) {
                $user_update = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'first_name' => $this->input->post('nama_panitia'),
                    'last_name' => ''
                ];
                
                if (!empty($password)) {
                    $user_update['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                if (isset($update_data['foto'])) {
                    $user_update['foto'] = $update_data['foto'];
                }
                
                $this->db->update('users', $user_update, ['id' => $panitia->id_user]);
            }
        }

        $this->output_json([
            'status' => (bool)$updated, 
            'msg' => $updated ? 'Data panitia berhasil diperbarui' : 'Gagal memperbarui data panitia'
        ]);
    }

    public function check_username_edit($username, $id_panitia)
    {
        $this->db->where('username', $username);
        $this->db->where('id_panitia !=', $id_panitia);
        $this->db->from('master_panitia');
        $count = $this->db->count_all_results();
        
        if ($count > 0) {
            $this->form_validation->set_message('check_username_edit', '{field} sudah digunakan');
            return FALSE;
        }
        return TRUE;
    }

    public function delete()
    {
        if (!$this->auth->is_admin()) {
            $this->output_json([
                'status' => false,
                'msg' => 'Unauthorized'
            ]);
            return;
        }

        $id_panitia = $this->input->post('id_panitia', true);
        $nama_panitia = $this->input->post('nama', true);

        // Get panitia data first to check for user relation
        $panitia = $this->users->getDataPanitia($id_panitia);
        if (!$panitia) {
            $this->output_json([
                'status' => false,
                'msg' => 'Data panitia tidak ditemukan'
            ]);
            return;
        }

        // Delete from master_panitia table
        $deleted = $this->panitia->delete($id_panitia);
        
        // Also delete from users table if user exists
        if ($deleted && $panitia->id_user) {
            $this->db->delete('users', ['id' => $panitia->id_user]);
        }

        $this->output_json([
            'status' => (bool)$deleted,
            'msg' => $deleted ? "Panitia {$nama_panitia} berhasil dihapus" : "Gagal menghapus panitia {$nama_panitia}"
        ]);
    }

    public function activate()
    {
        $id_panitia = $this->input->post('id_panitia', true);
        $id_user = $this->input->post('id', true);
        $nama_panitia = $this->input->post('nama', true);
        $is_active = $this->input->post('is_active', true);

        $panitia = $this->users->getDataPanitia($id_panitia);
        if (!$panitia) {
            $this->output_json([
                'status' => false,
                'msg' => 'Data panitia tidak ditemukan'
            ]);
            return;
        }

        $usernames = $this->users->usernameCheck($panitia->username ?? '-', 'master_panitia');
        if (count($usernames) > 1 && $is_active === 'aktifkan') {
            $data = [
                'status' => false,
                'msg' => 'Username duplikat ditemukan, silakan ganti username terlebih dahulu',
                'users' => $usernames
            ];
            $this->output_json($data);
            return;
        }

        if ($id_user) {
            $active = $this->auth->aktifkanPanitia($is_active, $id_panitia);
            $msg = $is_active == 'aktifkan' ? ' diaktifkan' : ' dinonaktifkan';
        } else {
            $nama = explode(' ', $panitia->nama_panitia ?? '');
            $first_name = $nama[0] ?? '';
            $last_name = end($nama) ?? $first_name;

            $user = [
                'username' => trim($panitia->username ?? ''),
                'password' => password_hash(trim($panitia->password ?? ''), PASSWORD_BCRYPT),
                'first_name' => $first_name,
                'last_name' => $last_name
            ];

            $id_user = $this->auth->register($user, '4');

            $input = [
                'id_panitia' => $id_panitia,
                'id_user' => $id_user,
                'status' => 1
            ];

            $active = $this->master->update('master_panitia', $input, 'id_panitia', $id_panitia);
            $msg = ' diaktifkan';
        }

        $data = [
            'status' => (bool)$active,
            'msg' => $active ? 'Panitia ' . $nama_panitia . $msg : 'Panitia ' . $nama_panitia . ' gagal' . $msg
        ];

        $this->output_json($data);
    }

    public function aktifkanSemua()
    {
        $duplikat = $this->users->getDuplicateUsernames('master_panitia');
        if (!empty($duplikat)) {
            $msg = '';
            foreach ($duplikat as $row) {
                $msg .= "<br>Username: {$row->username} duplikat {$row->total} kali";
            }
            $this->output_json([
                'status' => false,
                'msg' => 'Terdapat username duplikat, silakan ganti username terlebih dahulu!' . $msg
            ]);
            return;
        }

        $is_active = $this->input->post('is_active', true);
        $res = $this->auth->aktifkanAllPanitia($is_active);

        $msg = $is_active == 'aktifkan' ? ' panitia diaktifkan.' : ' panitia dinonaktifkan.';
        $data = [
            'status' => true,
            'jumlah' => count($res),
            'msg' => count($res) . $msg
        ];

        $this->output_json($data);
    }

    public function update_iduser()
    {
        $this->db->select('p.id_panitia, u.id');
        $this->db->from('master_panitia p');
        $this->db->join('users u', 'p.username = u.username', 'left');
        $result = $this->db->get()->result();

        $toUpdate = [];
        foreach ($result as $row) {
            $toUpdate[] = [
                'id_panitia' => $row->id_panitia,
                'id_user' => $row->id
            ];
        }

        $updated = !empty($toUpdate) ? $this->db->update_batch('master_panitia', $toUpdate, 'id_panitia') : true;
        $this->output_json(['status' => (bool)$updated]);
    }

    public function reset_login()
    {
        $username = $this->input->post('username', true);
        $nama = $this->input->post('nama', true);

        if ($this->auth->logged_in() || !$this->auth->is_admin()) {
            $data = [
                'status' => false,
                'msg' => 'You must be an administrator to view this page.'
            ];
        } else {
            $this->db->where('login', $username);
            if ($this->db->delete('login_attempts')) {
                $data = [
                    'status' => true,
                    'msg' => 'User ' . $nama . ' berhasil direset'
                ];
            } else {
                $data = [
                    'status' => false,
                    'msg' => 'User ' . $nama . ' gagal direset'
                ];
            }
        }

        $this->output_json($data, true);
    }
}
