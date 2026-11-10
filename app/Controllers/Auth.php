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
     * Default login page (staff) - DEPRECATED
     * Now using Backend\Auth::login
     */
    /*
    public function index()
    {
        return $this->renderLoginPage('staff');
    }
    */

    /**
     * Student login page - REDIRECT to new frontend login
     */
    public function siswa()
    {
        // Redirect to new frontend login page
        return redirect()->to('login');
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
            $redirectUrl = $this->determineRedirect($user);
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
            'login_title' => $variant === 'siswa' ? 'LOGIN SISWA' : 'LOGIN ADMIN',
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

                    if ($user->active == '1') {
                        // Verify password
                        if (password_verify($password, $user->password)) {
                            $role = $user->role;

                            // Set session data
                            $this->session->set([
                                'user_id' => $user->id,
                                'username' => $user->username,
                                'nama' => $user->first_name . ($user->last_name ? ' ' . $user->last_name : ''),
                                'email' => $user->email,
                                'role' => (string) $user->group_id, // Ensure string for consistency
                                'role_name' => strtolower($user->role_name ?? ''),
                                'foto' => $user->foto,
                                'school_uuid' => $user->company ?? null,
                                'is_login' => true,
                                'login_variant' => $loginVariant
                            ]);

                            // Regenerate session ID for security and to ensure fresh session state
                            $this->session->regenerate();


                            $this->authModel->resetErrorLogin($username);
                            $message = 'Login berhasil';
                            $success = true;
                            $redirectUrl = $this->determineRedirect($user);

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
    private function determineRedirect(?object $user): string
    {
        $default = site_url('dashboard');

        if (!$user) {
            return $default;
        }

        $roleName = strtolower($user->role_name ?? '');

        if ($roleName === 'siswa') {
            return site_url('dashboard');
        }

        // Admin, Guru, Panitia redirect to their dashboard
        if (in_array($roleName, ['admin', 'guru', 'panitia'])) {
            return site_url('dashboard');
        }

        // Admin Sekolah redirect
        if ($roleName === 'admin_sekolah' || !empty($user->company)) {
            return site_url('sekolahpanel');
        }

        return $default;
    }

    /**
     * Handle logout - REDIRECT to new login pages
     */
    public function logout()
    {
        $role = $this->session->get('role_name');

        $this->logModel->saveLog(2, 'Logout');
        $this->session->destroy();

        // Redirect to new login pages
        if (strtolower($role ?? '') === 'siswa') {
            return redirect()->to('login'); // New frontend login
        }

        return redirect()->to('auth'); // New backend login
    }
}
