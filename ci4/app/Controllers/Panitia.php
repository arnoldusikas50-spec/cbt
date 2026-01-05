<?php

namespace App\Controllers;

use App\Models\DashboardModel;

/**
 * Panitia Controller - Migrated from CI3
 * Panitia profile and functions
 */
class Panitia extends BaseController
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

        if (!$this->inGroup('panitia')) {
            return redirect()->to('dashboard');
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $userId = session()->get('user_id');

        $panitia = $this->dashboardModel->getDataPanitiaByUserId($userId, $tp->id_tp, $smt->id_smt);

        $data = [
            'judul' => 'Profil Panitia',
            'subjudul' => 'Data Profil Saya',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'panitia' => $panitia,
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('members/panitia/profil', $data);
    }

    public function updateProfil()
    {
        if (!$this->inGroup('panitia')) {
            return $this->respond(false, 'Akses ditolak');
        }

        $idPanitia = (int) $this->request->getPost('id_panitia');

        $db = \Config\Database::connect();

        $data = [
            'nama_panitia' => $this->request->getPost('nama_panitia'),
            'instansi' => $this->request->getPost('instansi'),
        ];

        $result = $db->table('master_panitia')
            ->where('id_panitia', $idPanitia)
            ->update($data);

        return $result
            ? $this->respond(true, 'Profil berhasil diupdate')
            : $this->respond(false, 'Gagal update profil');
    }
}
