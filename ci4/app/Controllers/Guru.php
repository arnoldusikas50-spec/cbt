<?php

namespace App\Controllers;

use App\Models\DashboardModel;

/**
 * Guru Controller - Migrated from CI3
 * Teacher profile and functions
 */
class Guru extends BaseController
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

        if (!$this->inGroup('guru')) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $user = session()->get('user_id');

        $guru = $this->dashboardModel->getDataGuruByUserId($user, $tp->id_tp, $smt->id_smt);

        $data = [
            'judul' => 'Profil Guru',
            'subjudul' => 'Data Profil Saya',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'guru' => $guru,
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('members/guru/profil', $data);
    }

    public function updateProfil()
    {
        if (!$this->inGroup('guru')) {
            return $this->respond(false, 'Akses ditolak');
        }

        $idGuru = (int) $this->request->getPost('id_guru');

        $db = \Config\Database::connect();

        $data = [
            'nama_guru' => $this->request->getPost('nama_guru'),
            'nip' => $this->request->getPost('nip'),
            'email' => $this->request->getPost('email'),
            'no_hp' => $this->request->getPost('no_hp'),
            'alamat' => $this->request->getPost('alamat'),
        ];

        $result = $db->table('master_guru')
            ->where('id_guru', $idGuru)
            ->update($data);

        return $result
            ? $this->respond(true, 'Profil berhasil diupdate')
            : $this->respond(false, 'Gagal update profil');
    }
}
