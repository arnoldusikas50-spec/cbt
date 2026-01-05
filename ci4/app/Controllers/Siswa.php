<?php

namespace App\Controllers;

use App\Models\DashboardModel;

/**
 * Siswa Controller - Migrated from CI3
 * Student profile and functions
 */
class Siswa extends BaseController
{
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->inGroup('siswa')) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $username = session()->get('username');

        $siswa = $this->dashboardModel->getDataSiswa($username, $tp->id_tp, $smt->id_smt);

        $data = [
            'judul' => 'Profil Siswa',
            'subjudul' => 'Data Profil Saya',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'siswa' => $siswa,
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('members/siswa/profil', $data);
    }

    public function gantiPassword()
    {
        if (!$this->inGroup('siswa')) {
            return $this->respond(false, 'Akses ditolak');
        }

        $oldPassword = $this->request->getPost('old_password');
        $newPassword = $this->request->getPost('new_password');
        $username = session()->get('username');

        $db = \Config\Database::connect();

        // Get current siswa
        $siswa = $db->table('master_siswa')
            ->where('username', $username)
            ->get()
            ->getRow();

        if (!$siswa) {
            return $this->respond(false, 'Siswa tidak ditemukan');
        }

        // Verify old password
        if (!password_verify($oldPassword, $siswa->password ?? '')) {
            return $this->respond(false, 'Password lama salah');
        }

        // Update password
        $result = $db->table('master_siswa')
            ->where('id_siswa', $siswa->id_siswa)
            ->update(['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);

        return $result
            ? $this->respond(true, 'Password berhasil diubah')
            : $this->respond(false, 'Gagal mengubah password');
    }
}
