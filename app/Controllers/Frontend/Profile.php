<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\DashboardModel;

class Profile extends BaseController
{
    protected $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Profile index - show student profile
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
            'user' => $user,
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'judul' => 'Profil',
            'subjudul' => 'Profil Siswa'
        ];

        return view('members/siswa/profile/index', $data);
    }

    /**
     * Update profile
     */
    public function update()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Sesi tidak valid'
            ]);
        }

        // Validation rules
        $rules = [
            'email' => 'permit_empty|valid_email',
            'telepon' => 'permit_empty|max_length[20]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $userId = $this->getUserId();
        $user = $this->authModel->user($userId);

        if (!$user) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }

        // Update user data
        $updateData = [];

        if ($this->request->getPost('email')) {
            $updateData['email'] = $this->request->getPost('email');
        }

        if ($this->request->getPost('telepon')) {
            // Update siswa table if exists
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if ($tp && $smt) {
                $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
                if ($siswa) {
                    // Update master_siswa table
                    $db = \Config\Database::connect();
                    $db->table('master_siswa')
                        ->where('id_siswa', $siswa->id_siswa)
                        ->update(['no_telp' => $this->request->getPost('telepon')]);
                }
            }
        }

        if (!empty($updateData)) {
            $this->authModel->update($userId, $updateData);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Profil berhasil diperbarui'
        ]);
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Sesi tidak valid'
            ]);
        }

        // Validation
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $userId = $this->getUserId();
        $user = $this->authModel->user($userId);

        if (!$user) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }

        // Verify current password
        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user->password)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Password lama tidak sesuai'
            ]);
        }

        // Update password
        $newPassword = $this->request->getPost('new_password');
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->authModel->update($userId, ['password' => $hashedPassword]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }
}
