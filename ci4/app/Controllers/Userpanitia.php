<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\DashboardModel;

/**
 * Userpanitia Controller - Migrated from CI3
 */
class Userpanitia extends BaseController
{
    protected AuthModel $authModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('dashboard');
        }

        $data = [
            'judul' => 'User Panitia',
            'subjudul' => 'Kelola Akun Panitia/Pengawas',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('users/panitia/data', $data);
    }

    public function data()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized', 'data' => []]);
        }

        $db = \Config\Database::connect();
        $users = $db->table('users a')
            ->select('a.*, b.nama_panitia, b.instansi')
            ->join('master_panitia b', 'a.id = b.id_user', 'left')
            ->where('a.role', '4')
            ->get()
            ->getResult();

        $data = [];
        foreach ($users as $i => $u) {
            $data[] = [
                'no' => $i + 1,
                'id' => $u->id,
                'username' => $u->username,
                'nama_panitia' => $u->nama_panitia ?? $u->first_name ?? '-',
                'instansi' => $u->instansi ?? '-',
                'last_login' => $u->last_login ?? '-',
                'active' => $u->active ?? '1',
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    public function save()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $nama = $this->request->getPost('nama_panitia');

        if (!$username || !$password) {
            return $this->response->setJSON(['status' => false, 'message' => 'Username dan password wajib diisi']);
        }

        $db = \Config\Database::connect();

        // Check if username exists
        $exists = $db->table('users')->where('username', $username)->countAllResults();
        if ($exists > 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Username sudah digunakan']);
        }

        // Create user
        $userData = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $nama,
            'role' => '4',
            'active' => '1',
            'created_on' => time(),
        ];

        $db->table('users')->insert($userData);
        $userId = $db->insertID();

        // Create panitia record
        $panitiaData = [
            'id_user' => $userId,
            'nama_panitia' => $nama,
            'instansi' => $this->request->getPost('instansi'),
        ];

        $db->table('master_panitia')->insert($panitiaData);

        return $this->response->setJSON(['status' => true, 'message' => 'Panitia berhasil ditambahkan']);
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id');
        $db = \Config\Database::connect();

        // Delete panitia record
        $db->table('master_panitia')->where('id_user', $id)->delete();

        // Delete user
        $result = $db->table('users')->where('id', $id)->delete();

        return $result
            ? $this->response->setJSON(['status' => true, 'message' => 'Panitia berhasil dihapus'])
            : $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus panitia']);
    }
}
