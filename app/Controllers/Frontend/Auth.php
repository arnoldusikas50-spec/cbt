<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class Auth extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Show login page for siswa
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $role = $this->getUserRole();

            if ($role === '3' || $role === 'siswa') {
                return redirect()->to('dashboard');
            } else {
                return redirect()->to('admin/dashboard');
            }
        }

        // Use existing auth/index view for siswa login
        $data['setting'] = $this->dashboardModel->getSetting();
        $data['title'] = 'Login Siswa';
        $data['message'] = ''; // Required by auth/index view

        return view('auth/index', $data);
    }

    /**
     * Process login for siswa
     */
    public function doLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Use existing auth model
        $user = $this->authModel->login($username);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Username tidak ditemukan');
        }

        // Verify password
        if (!password_verify($password, $user->password)) {
            return redirect()->back()->withInput()->with('error', 'Password salah');
        }

        // Check if user is siswa
        $roleName = strtolower($user->role_name ?? '');
        if ($roleName !== 'siswa') {
            return redirect()->to('admin/login')->with('error', 'Silakan login di halaman admin');
        }

        // Set session
        $this->session->set([
            'user_id' => $user->id,
            'username' => $user->username,
            'nama' => $user->first_name . ($user->last_name ? ' ' . $user->last_name : ''),
            'role' => (string) $user->group_id,
            'role_name' => 'siswa',
            'is_login' => true,
            'login_variant' => 'siswa'
        ]);

        $this->session->regenerate();

        // Optional: Log activity if you have Log model
        // $this->logModel->insert([...]);

        return redirect()->to('dashboard')->with('success', 'Selamat datang, ' . $user->username);
    }

    /**
     * Logout
     */
    public function logout()
    {
        // Optional: Log activity before destroying session
        // if ($this->logModel) {
        //     $this->logModel->insert([...]);
        // }

        $this->session->destroy();

        return redirect()->to('/')->with('success', 'Anda telah keluar');
    }
}
