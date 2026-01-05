<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\DashboardModel;

/**
 * Useradmin Controller - Migrated from CI3
 */
class Useradmin extends BaseController
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
            'judul' => 'User Admin',
            'subjudul' => 'Kelola Akun Administrator',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('users/admin/data', $data)
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized', 'data' => []]);
        }

        try {
            $db = \Config\Database::connect();
            $users = $db->table('users')
                ->where('role', '1')
                ->get()
                ->getResult();

            $data = [];
            foreach ($users as $i => $u) {
                $data[] = [
                    'no' => $i + 1,
                    'id' => $u->id,
                    'username' => $u->username,
                    'first_name' => $u->first_name ?? '-',
                    'email' => $u->email ?? '-',
                    'last_login' => $u->last_login ?? '-',
                    'active' => $u->active ?? '1',
                ];
            }
            return $this->response->setJSON(['data' => $data]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function save()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (!$username || !$password) {
            return $this->response->setJSON(['status' => false, 'message' => 'Username dan password wajib diisi']);
        }

        $db = \Config\Database::connect();

        // Check if username exists
        $exists = $db->table('users')->where('username', $username)->countAllResults();
        if ($exists > 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Username sudah digunakan']);
        }

        $data = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $this->request->getPost('first_name'),
            'email' => $this->request->getPost('email'),
            'role' => '1',
            'active' => '1',
            'created_on' => time(),
        ];

        $result = $db->table('users')->insert($data);

        return $result
            ? $this->response->setJSON(['status' => true, 'message' => 'Admin berhasil ditambahkan'])
            : $this->response->setJSON(['status' => false, 'message' => 'Gagal menambahkan admin']);
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id');

        // Prevent deleting self
        if ($id == session()->get('user_id')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak dapat menghapus akun sendiri']);
        }

        $db = \Config\Database::connect();
        $result = $db->table('users')->where('id', $id)->delete();

        return $result
            ? $this->response->setJSON(['status' => true, 'message' => 'Admin berhasil dihapus'])
            : $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus admin']);
    }
}
