<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class Home extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        // Check if user is already logged in
        if ($this->isLoggedIn()) {
            $role = $this->getUserRole();

            // Redirect based on role
            if ($role === '3' || $role === 'siswa') {
                // Siswa - redirect to siswa dashboard
                return redirect()->to('dashboard');
            } else {
                // Admin/Guru/Panitia - redirect to admin
                return redirect()->to('admin/dashboard');
            }
        }

        // Not logged in - redirect to login page
        return redirect()->to('login');

        /* OLD CODE - Show landing page
        // Get app settings
        $data['setting'] = $this->dashboardModel->getSetting();
        $data['title'] = 'Beranda - Aplikasi Asesmen';

        // Get latest announcements for landing page
        $data['announcements'] = $this->dashboardModel->getRunningText();

        return view('frontend/home', $data);
        */
    }

    /**
     * About page
     */
    public function about()
    {
        $data['setting'] = $this->dashboardModel->getSetting();
        $data['title'] = 'Tentang - Aplikasi Asesmen';

        return view('frontend/about', $data);
    }

    /**
     * Help page
     */
    public function help()
    {
        $data['setting'] = $this->dashboardModel->getSetting();
        $data['title'] = 'Bantuan - Aplikasi Asesmen';

        return view('frontend/help', $data);
    }
}
