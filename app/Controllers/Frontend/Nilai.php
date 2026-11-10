<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\RaporModel;

class Nilai extends BaseController
{
    protected $dashboardModel;
    protected $raporModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->raporModel = new RaporModel();
    }

    /**
     * Nilai index - show student grades
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
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
            'judul' => 'Nilai',
            'subjudul' => 'Rapor & Nilai Siswa',
            'running_text' => $this->dashboardModel->getRunningText()
        ];

        return view('members/siswa/nilai/index', $data);
    }

    /**
     * Detail nilai per mapel
     */
    public function detail($id_mapel = null)
    {
        if (!$id_mapel) {
            return redirect()->to('nilai')->with('error', 'Mapel tidak valid');
        }

        if (!$this->isLoggedIn()) {
            return redirect()->to('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $userId = $this->getUserId();
        $user = $this->authModel->user($userId);

        if (!$user) {
            return redirect()->to('login')->with('error', 'Sesi tidak valid');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return redirect()->to('nilai')->with('error', 'Tahun/semester tidak valid');
        }

        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return redirect()->to('nilai')->with('error', 'Data siswa tidak ditemukan');
        }

        $data = [
            'siswa' => $siswa,
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'id_mapel' => $id_mapel,
            'judul' => 'Detail Nilai',
            'subjudul' => 'Detail Nilai Mata Pelajaran'
        ];

        return view('members/siswa/nilai/detail', $data);
    }
}
