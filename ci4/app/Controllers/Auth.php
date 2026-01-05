<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\LogModel;
use App\Models\SettingsModel;

/**
 * Auth Controller - Migrated from CI3 Auth controller
 * Handles user authentication (login, logout, registration)
 */
class Auth extends BaseController
{
    protected LogModel $logModel;
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->logModel = new LogModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Default login page (staff)
     */
    public function index()
    {
        return $this->renderLoginPage('staff');
    }

    /**
     * Student login page
     */
    public function siswa()
    {
        return $this->renderLoginPage('siswa');
    }

    /**
     * Render login page based on variant
     */
    private function renderLoginPage(string $variant = 'staff')
    {
        helper('init');

        // Check if setup is complete
        if (!function_exists('database_connected') || !database_connected() || is_database_empty() || is_setting_empty()) {
            return redirect()->to('install');
        }

        // Already logged in - redirect
        if ($this->session->get('is_login')) {
            $user = $this->authModel->user($this->session->get('user_id'));
            $redirectUrl = $this->determineRedirect($user, false);
            return redirect()->to($redirectUrl);
        }

        $setting = $this->settingsModel->getSetting();

        $data = [
            'setting' => $setting,
            'identity' => [
                'name' => 'username',
                'id' => 'identity',
                'type' => 'text',
                'placeholder' => 'Username',
                'autofocus' => 'autofocus',
                'class' => 'form-control',
                'autocomplete' => 'off'
            ],
            'username' => [
                'name' => 'username',
                'id' => 'identity',
                'type' => 'text',
                'placeholder' => 'Username',
                'autofocus' => 'autofocus',
                'class' => 'form-control',
                'autocomplete' => 'off'
            ],
            'password' => [
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'placeholder' => 'Password',
                'class' => 'form-control',
            ],
            'message' => session()->getFlashdata('message') ?? '',
            'login_variant' => $variant,
            'show_mode_selector' => $variant !== 'siswa',
            'cbt_only_default' => $variant === 'siswa' ? '1' : '0',
            'login_title' => $variant === 'siswa' ? 'LOGIN SISWA CBT' : 'E-LEARNING & CBT',
        ];

        // Use appropriate view based on variant
        if ($variant !== 'siswa') {
            return view('auth/login', $data);
        }

        return view('auth/index', $data);
    }

    /**
     * Handle login POST request
     */
    public function login()
    {

        try {
            $success = false;
            $message = '';
            $role = '0';
            $dataUser = null;
            $errors = [];

            $cbtOnly = $this->request->getPost('cbt-only') === '1';
            $loginVariant = $this->request->getPost('login_variant');
            $redirectUrl = site_url('dashboard');

            if (strtolower($this->request->getMethod()) === 'post') {
                // Validation
                $validation = \Config\Services::validation();

                $rules = [
                    'username' => 'required|min_length[3]|max_length[50]',
                    'password' => 'required|min_length[4]|max_length[50]',
                    'login_variant' => 'required|in_list[siswa,staff]'
                ];

                if (!$this->validate($rules)) {
                    $message = 'Username atau Password salah!';
                    $errors = [
                        'username' => $validation->getError('username'),
                        'password' => $validation->getError('password')
                    ];

                    return $this->respond(false, $message, null, $errors);
                }

                $username = (string) $this->request->getPost('username');
                $password = (string) $this->request->getPost('password');

                // Check login attempts
                $attempts = $this->authModel->cekErrorLogin($username);
                if (count($attempts) >= 3) {
                    $timeDifference = time() - end($attempts)->time;
                    if ($timeDifference < 300) {
                        $message = 'Anda sudah 3x melakukan percobaan login. Silakan tunggu 5 menit atau hubungi Administrator.';
                        return $this->respond(false, $message);
                    } else {
                        $this->authModel->resetErrorLogin($username);
                    }
                }

                // Get user
                $user = $this->authModel->login($username);

                if ($user) {
                    $roleName = strtolower($user->role_name ?? '');
                    $isSiswaVariant = $loginVariant === 'siswa';
                    $isStaffVariant = $loginVariant === 'staff';
                    $isUserSiswa = $roleName === 'siswa';

                    // Validate login page vs user role
                    if ($isSiswaVariant && !$isUserSiswa) {
                        return $this->respond(false, 'Gunakan halaman login Admin/Guru untuk akun ini.');
                    }
                    if ($isStaffVariant && $isUserSiswa) {
                        return $this->respond(false, 'Gunakan halaman login siswa untuk akun ini.');
                    }

                    // Check if student is active
                    $dataUser = $this->authModel->isAktif($username);
                    $isSiswa = $dataUser !== null;

                    // Only check status for siswa, and treat null status as active
                    if ($isSiswa && isset($dataUser->status) && $dataUser->status !== '1') {
                        return $this->respond(false, 'Akun Anda dinonaktifkan!');
                    }

                    if ($user->active === '1') {
                        // Verify password
                        if (password_verify($password, $user->password)) {
                            $role = $user->role;

                            // Set session data
                            $this->session->set([
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'nama' => $user->first_name . ($user->last_name ? ' ' . $user->last_name : ''),
                                'email' => $user->email,
                                'role' => $user->group_id,
                                'role_name' => $user->role_name,
                                'foto' => $user->foto,
                                'school_uuid' => $user->company ?? null,
                                'is_login' => true,
                                'login_variant' => $loginVariant
                            ]);

                            $this->authModel->resetErrorLogin($username);
                            $message = 'Login berhasil';
                            $success = true;
                            $redirectUrl = $this->determineRedirect($user, $cbtOnly);

                            // Log with explicit userId since session might not be committed yet
                            $this->logModel->saveLog(1, 'Login', (int) $user->id);
                        } else {
                            // Wrong password
                            $this->authModel->errorLogin([
                                'ip_address' => $this->request->getIPAddress(),
                                'login' => $username,
                                'time' => time()
                            ]);
                            $message = 'Username atau Password salah!';
                        }
                    } else {
                        $message = 'Akun Anda dinonaktifkan!';
                    }
                } else {
                    $message = 'Akun tidak terdaftar!';
                }
            }

            $respData = [
                'role' => $role,
                'user' => $dataUser,
                'url' => 'dashboard',
                'redirect' => $redirectUrl
            ];

            return $this->respond($success, $message, $respData, $errors);

        } catch (\Throwable $th) {
            return $this->respond(false, 'Error: ' . $th->getMessage() . ' File: ' . $th->getFile() . ' Line: ' . $th->getLine());
        }
    }

    /**
     * Determine redirect URL based on user role
     */
    private function determineRedirect(?object $user, bool $cbtOnly): string
    {
        $default = site_url('dashboard');

        if (!$user) {
            return $default;
        }

        // Admin Sekolah redirect
        if (!empty($user->company)) {
            return site_url('sekolahpanel');
        }

        $roleName = strtolower($user->role_name ?? '');

        if ($cbtOnly && $roleName === 'siswa') {
            return site_url('cbtsiswa/cbt');
        }

        if ($roleName === 'siswa') {
            return site_url('dashboard');
        }

        return $default;
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        $role = $this->session->get('role_name');

        $this->logModel->saveLog(2, 'Logout');
        $this->session->destroy();

        if (strtolower($role ?? '') === 'siswa') {
            return redirect()->to('auth/siswa');
        }

        return redirect()->to('auth');
    }

    /**
     * Handle QR code login for students
     */
    public function qrcodeLogin()
    {
        $qrData = $this->request->getPost('qr_data');

        if (!$qrData) {
            return $this->respond(false, 'QR code data is required');
        }

        // Try to find user by username first
        $user = $this->authModel->login($qrData);

        // If not found, try by nomor_peserta
        if (!$user) {
            $db = \Config\Database::connect();
            $user = $db->table('users a')
                ->select('a.*, b.user_id, b.group_id, c.id as role, c.name as role_name, c.description')
                ->join('users_groups b', 'a.id = b.user_id')
                ->join('groups c', 'b.group_id = c.id')
                ->where('a.nomor_peserta', $qrData)
                ->get()
                ->getRow();
        }

        if (!$user) {
            return $this->respond(false, 'QR code tidak valid atau siswa tidak ditemukan');
        }

        if (strtolower($user->role_name ?? '') !== 'siswa') {
            return $this->respond(false, 'QR code tidak valid atau siswa tidak ditemukan');
        }

        // Set session data
        $this->session->set([
            'is_login' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'nama' => $user->first_name . ($user->last_name ? ' ' . $user->last_name : ''),
            'email' => $user->email ?? '',
            'role' => $user->group_id,
            'role_name' => $user->role_name,
            'foto' => $user->foto ?? '',
            'login_variant' => 'siswa'
        ]);

        $this->logModel->saveLog(1, 'QR Code Login: ' . $qrData);

        return $this->respond(true, 'Login berhasil dengan QR code', [
            'redirect' => site_url('cbtsiswa/cbt'),
            'url' => 'siswa/cbt',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nama' => $user->nama ?? '',
                'role' => $user->role_name
            ]
        ]);
    }

    /**
     * Handle registration (if needed)
     */
    public function register()
    {
        if ($this->request->getMethod() === 'post') {
            $group = $this->request->getPost('role');
            $data = [
                'username' => $this->request->getPost('username'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
                'first_name' => '',
                'last_name' => ''
            ];

            $this->authModel->register($data, $group);
            return redirect()->to('auth');
        }

        return view('register');
    }

    /**
     * Debug endpoint to test auth system
     * Access via: /auth/test
     */
    public function test()
    {
        $results = [];

        // Test 1: Database connection
        try {
            $db = \Config\Database::connect();
            $results['database'] = [
                'status' => 'OK',
                'driver' => $db->DBDriver,
                'database' => $db->getDatabase(),
            ];
        } catch (\Throwable $e) {
            $results['database'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
        }

        // Test 2: Check users table
        try {
            $db = \Config\Database::connect();
            $count = $db->table('users')->countAll();
            $results['users_table'] = [
                'status' => 'OK',
                'count' => $count
            ];
        } catch (\Throwable $e) {
            $results['users_table'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
        }

        // Test 3: Check a specific user
        try {
            $user = $this->authModel->login('admincbt');
            if ($user) {
                $results['admin_user'] = [
                    'status' => 'OK',
                    'id' => $user->id,
                    'username' => $user->username,
                    'role_name' => $user->role_name ?? 'N/A',
                    'active' => $user->active,
                    'password_length' => strlen($user->password ?? ''),
                    'password_test_admin123' => password_verify('@Admin123', $user->password) ? 'MATCH' : 'NO MATCH',
                    'password_test_12345678' => password_verify('12345678', $user->password) ? 'MATCH' : 'NO MATCH',
                    'password_test_admincbt' => password_verify('admincbt', $user->password) ? 'MATCH' : 'NO MATCH',
                ];
            } else {
                $results['admin_user'] = [
                    'status' => 'FAIL',
                    'error' => 'User not found or no group'
                ];
            }
        } catch (\Throwable $e) {
            $results['admin_user'] = [
                'status' => 'FAIL',
                'error' => $e->getMessage()
            ];
        }

        // Test 4: Session
        try {
            $this->session->set('test_key', 'test_value');
            $val = $this->session->get('test_key');
            $this->session->remove('test_key');
            $results['session'] = ['status' => $val === 'test_value' ? 'OK' : 'FAIL'];
        } catch (\Throwable $e) {
            $results['session'] = ['status' => 'FAIL', 'error' => $e->getMessage()];
        }

        // Test 5: Settings
        try {
            $setting = $this->settingsModel->getSetting();
            $results['settings'] = [
                'status' => $setting ? 'OK' : 'EMPTY',
                'sekolah' => $setting->sekolah ?? 'N/A'
            ];
        } catch (\Throwable $e) {
            $results['settings'] = ['status' => 'FAIL', 'error' => $e->getMessage()];
        }

        return $this->respond(true, 'Auth system test results', $results);
    }

    /**
     * Reset admin password (for development only)
     * Access via: /auth/reset_admin_password
     * WARNING: Remove this in production!
     */
    public function reset_admin_password()
    {
        try {
            $db = \Config\Database::connect();

            // New password: admin123
            $newPassword = password_hash('admin123', PASSWORD_BCRYPT);

            $db->table('users')
                ->where('username', 'admincbt')
                ->update(['password' => $newPassword]);

            if ($db->affectedRows() > 0) {
                return $this->respond(true, 'Password for admincbt has been reset to: admin123');
            } else {
                return $this->respond(false, 'User admincbt not found');
            }
        } catch (\Throwable $e) {
            return $this->respond(false, 'Error: ' . $e->getMessage());
        }
    }
}
