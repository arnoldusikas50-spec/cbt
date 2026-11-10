<?php

namespace App\Controllers\Backend;

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
     * Show login page for admin/guru/panitia
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $role = $this->getUserRole();

            // Admin/Guru/Panitia stay in admin
            if (in_array($role, ['1', '2', '4', 'admin', 'guru', 'panitia'])) {
                return redirect()->to('admin/dashboard');
            } else {
                // Siswa go to frontend
                return redirect()->to('dashboard');
            }
        }

        $data['setting'] = $this->dashboardModel->getSetting();
        $data['title'] = 'Login Admin';

        return view('backend/login', $data);
    }

    /**
     * Process login for admin/guru/panitia
     * DEPRECATED: Now handled by App\Controllers\Auth::login
     */
    /*
    public function doLogin()
    {
       // ... Logic merged to Auth::login ...
    }
    */

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->destroy();

        return redirect()->to('auth')->with('success', 'Anda telah keluar');
    }
}
