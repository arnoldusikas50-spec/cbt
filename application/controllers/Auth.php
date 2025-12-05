<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('Auth_model', 'auth');
        $this->load->model('Log_model', 'logging');
    }

    protected function respond($status, $message = '', $data = null, $errors = null, $http_code = 200)
    {
        if (!method_exists($this, 'respond_json')) {
            $response = [
                'status' => (bool) $status,
                'message' => $message,
                'errors' => $errors,
                'data' => $data
            ];
            if (ob_get_length()) ob_end_clean();
            header_remove();
            $this->output
                ->set_status_header($http_code)
                ->set_content_type('application/json')
                ->set_output(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
            return;
        }

        $this->respond_json($status, $message, $data, $errors, $http_code);
    }

    public function index() {
        $this->render_login_page('staff');
    }

    public function siswa()
    {
        $this->render_login_page('siswa');
    }

    private function render_login_page($variant = 'staff')
    {
        $this->load->helper('init');

        if (!database_connected() || is_database_empty() || is_setting_empty()) {
            redirect('install');
        }

        if ($this->session->userdata('is_login')) {
            redirect('dashboard');
            return;
        }

        $this->load->model('Users_model', 'users');
        $this->load->model('Settings_model', 'settings');

        $setting = $this->settings->getSetting();
        $data['setting'] = $setting;
        $identityField = [
            'name' => 'username',
            'id' => 'identity',
            'type' => 'text',
            'placeholder' => 'Username',
            'autofocus' => 'autofocus',
            'class' => 'form-control',
            'autocomplete' => 'off'
        ];
        $passwordField = [
            'name' => 'password',
            'id' => 'password',
            'type' => 'password',
            'placeholder' => 'Password',
            'class' => 'form-control',
        ];
        $data['identity'] = $identityField;
        $data['username'] = $identityField;
        $data['password'] = $passwordField;
        $data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
        $data['login_variant'] = $variant;
        $data['show_mode_selector'] = $variant !== 'siswa';
        $data['cbt_only_default'] = $variant === 'siswa' ? '1' : '0';
        $data['login_title'] = $variant === 'siswa' ? 'LOGIN SISWA CBT' : 'E-LEARNING & CBT';

        $view = $variant === 'siswa' ? 'auth/login_siswa' : 'auth/login_admin';

        $this->load->view('_templates/auth/_header', $data);
        $this->load->view($view, $data);
        $this->load->view('_templates/auth/_footer');
    }

    public function login() {
        $success = false;
        $message = '';
        $role = '0';
        $data_user = null;
        $cbtOnly = $this->input->post('cbt-only', true) === '1';
        $loginVariant = $this->input->post('login_variant', true);
        $redirectUrl = site_url('dashboard');
        if ($this->input->post()) {
            // cek post
            $this->load->library('form_validation');

            $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[5]|max_length[50]');
            $this->form_validation->set_rules('password', 'Password', 'required|trim|min_length[5]|max_length[50]');
            $this->form_validation->set_rules('login_variant', 'Halaman Login', 'required|in_list[siswa,staff]');
            if ($this->form_validation->run() == TRUE) {
                // lanjutkan jika lolos validasi
                $username = htmlspecialchars($this->input->post('username'), ENT_QUOTES, 'UTF-8');
                $password = htmlspecialchars($this->input->post('password'), ENT_QUOTES, 'UTF-8');

                // Cek percobaan login (error login)
                $attempts = $this->auth->cekErrorLogin($username);
                if (count($attempts) >= 3) {
                    // Cek selisih waktu dari percobaan login terakhir
                    // Gunakan `end()` untuk mengambil waktu terakhir
                    $timeDifference = time() - end($attempts)->time;
                    if ($timeDifference < 300) {
                        // Jika belum 5 menit
                        $message = 'Anda sudah 3x melakukan percobaan login. Silakan tunggu 5 menit atau hubungi Administrator.';
                        $this->respond(false, $message);
                        return;
                    } else {
                        // Jika lebih dari 5 menit, reset percobaan login
                        $this->auth->resetErrorLogin($username);
                    }
                }

                // Ambil data user berdasarkan username
                $user = $this->auth->login($username);
                if ($user) {
                    $roleName = strtolower($user->role_name ?? '');
                    $is_siswa_variant = $loginVariant === 'siswa';
                    $is_staff_variant = $loginVariant === 'staff';
                    $isUserSiswa = $roleName === 'siswa';
                    if ($is_siswa_variant && !$isUserSiswa) {
                        $this->respond(false, 'Gunakan halaman login Admin/Guru untuk akun ini.');
                        return;
                    }
                    if ($is_staff_variant && $isUserSiswa) {
                        $this->respond(false, 'Gunakan halaman login siswa untuk akun ini.');
                        return;
                    }
                    // cek apakah siswa dan status nonaktif
                    $data_user = $this->auth->isAktif($username);
                    $is_siswa = $data_user != null;
                    if ($is_siswa && $data_user->status != '1') {
                        $message = 'Akun Anda dinonaktifkan!';
                        $this->respond(false, $message);
                        return;
                    }

                    if ($user->active == '1') {
                        // Lanjutkan proses login jika belum 3x salah atau waktu blokir sudah habis
                        if (password_verify($password, $user->password)) {
                            $role = $user->role;
                            // cek apakah akun nonaktif
                            // Jika login berhasil
                            $this->session->set_userdata([
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'role' => $user->group_id,
                                'role_name' => $user->role_name,
                                'is_login' => true
                            ]);
                            $this->auth->resetErrorLogin($username);
                            $message = 'Login berhasil';
                            $success = true;
                            $redirectUrl = $this->determine_redirect($user, $cbtOnly);
                            session_write_close();
                            $this->logging->saveLog(1, 'Login');
                        } else {
                            // Jika password salah
                            $error = [
                                'ip_address' => $this->input->ip_address(),
                                'login' => $username,
                                'time' => time()
                            ];
                            $this->auth->errorLogin($error); // Catat kesalahan login
                            $message = 'Username atau Password salah!';
                        }
                    } else {
                        $message = 'Akun Anda dinonaktifkan!';
                    }
                } else {
                    // Jika user tidak ditemukan
                    $message = 'Akun tidak terdaftar!';
                }
            } else {
                $message = 'Username atau Password salah!';
                $invalid = [
                    'username' => form_error('username'),
                    'password' => form_error('password')
                ];
            }
        }

        $resp_data = [
            'role' => $role,
            'user' => $data_user,
            'url' => 'dashboard',
            'redirect' => $redirectUrl
        ];
        $errors = $invalid ?? [];
        $this->respond($success, $message, $resp_data, $errors);
    }

    private function determine_redirect($user, $cbtOnly)
    {
        $default = site_url('dashboard');
        if (!$user) {
            return $default;
        }

        $roleName = strtolower($user->role_name ?? '');
        if ($cbtOnly && $roleName === 'siswa') {
            return site_url('cbtsiswa/cbt');
        }

        if ($roleName === 'siswa') {
            return site_url('cbtsiswa');
        }

        if ($roleName === 'guru') {
            return site_url('cbtguru');
        }

        return $default;
    }

    public function register() {
        if ($this->input->post()) {
            $group = $this->input->post('role');
            $data = [
                'username' => $this->input->post('username'),
                'password' => password_hash($this->input->post('password'), PASSWORD_BCRYPT),
                'first_name' => '',
                'last_name' => ''
            ];
            $this->auth->register($data, $group);
            redirect('auth');
        } else {
            $this->load->view('register');
        }
    }

    public function logout() {
        $this->logging->saveLog(2, 'Logout');
        $this->session->sess_destroy();
        redirect('auth');
    }

    public function qrcode_login() {
        $qr_data = $this->input->post('qr_data', true);
        
        // Debug logging
        error_log('QR Login Attempt - Data: ' . $qr_data);
        
        if (!$qr_data) {
            $this->respond(false, 'QR code data is required');
            return;
        }

        // QR code should contain student username or nomor_peserta
        // Try username first, then nomor_peserta
        $user = $this->auth->login($qr_data);
        
        error_log('User found by username: ' . ($user ? 'Yes' : 'No'));
        
        // If not found by username, try to find by nomor_peserta
        if (!$user) {
            $user = $this->db->select('a.*, b.user_id, b.group_id, c.id as role, c.name as role_name, c.description')
                ->from('users a')
                ->where(['a.nomor_peserta' => $qr_data])
                ->join('users_groups b', 'a.id=b.user_id')
                ->join('groups c', 'b.group_id=c.id')
                ->get()->row();
                
            error_log('User found by nomor_peserta: ' . ($user ? 'Yes' : 'No'));
        }
        
        if (!$user) {
            error_log('User not found for QR data: ' . $qr_data);
            $this->respond(false, 'QR code tidak valid atau siswa tidak ditemukan');
            return;
        }
        
        if (strtolower($user->role_name ?? '') !== 'siswa') {
            error_log('User role is not siswa: ' . ($user->role_name ?? 'unknown'));
            $this->respond(false, 'QR code tidak valid atau siswa tidak ditemukan');
            return;
        }

        // Set session data
        $session_data = [
            'is_login' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'nama' => $user->nama,
            'email' => $user->email,
            'role' => $user->role_name,
            'foto' => $user->foto,
            'login_variant' => 'siswa'
        ];
        
        $this->session->set_userdata($session_data);
        
        // Log the login
        $this->logging->saveLog(1, 'QR Code Login: ' . $qr_data);
        
        error_log('QR Login successful for: ' . $user->username);
        
        $this->respond(true, 'Login berhasil dengan QR code', [
            'redirect' => site_url('cbtsiswa/cbt'),
            'url' => 'siswa/cbt',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama' => $user->nama,
                'role' => $user->role_name
            ]
        ]);
    }
}