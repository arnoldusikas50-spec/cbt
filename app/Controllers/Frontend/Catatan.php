<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class Catatan extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Catatan index
     */
    public function index()
    {
        // Check if logged in
        if (!$this->isLoggedIn()) {
            return redirect()->to('login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Check if siswa
        $role = $this->getUserRole();
        if ($role !== '3' && $role !== 'siswa') {
            return redirect()->to('auth')->with('error', 'Akses ditolak');
        }

        $userId = $this->getUserId();
        $user = $this->authModel->user($userId);

        if (!$user) {
            return redirect()->to('login')->with('error', 'Sesi tidak valid');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return view('errors/disable_login', [
                'setting' => $this->dashboardModel->getSetting(),
                'message' => 'Tahun pelajaran atau semester belum diatur'
            ]);
        }

        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return view('errors/disable_login', [
                'setting' => $this->dashboardModel->getSetting(),
                'message' => 'Data siswa tidak ditemukan'
            ]);
        }

        $data = [
            'siswa' => $siswa,
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'judul' => 'Catatan',
            'subjudul' => 'Catatan Siswa'
        ];

        // Use EXISTING view
        return view('members/siswa/catatan/data', $data);
    }
}
