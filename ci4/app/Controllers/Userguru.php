<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\MasterModel;
use App\Models\DashboardModel;

/**
 * Userguru Controller - Migrated from CI3
 */
class Userguru extends BaseController
{
    protected AuthModel $authModel;
    protected MasterModel $masterModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->masterModel = new MasterModel();
        $this->dashboardModel = new DashboardModel();
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('dashboard');
        }

        $data = [
            'judul' => 'User Guru',
            'subjudul' => 'Kelola Akun Guru',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
            'gurus' => $this->masterModel->getAllGuru(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('users/guru/data', $data)
            . view('_templates/dashboard/_footer');
    }

    public function data()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized', 'data' => []]);
        }

        try {
            $db = \Config\Database::connect();
            $users = $db->table('users a')
                ->select('a.*, b.nama_guru, b.nip')
                ->join('master_guru b', 'a.id = b.id_user', 'left')
                ->where('a.role', '2')
                ->get()
                ->getResult();

            $data = [];
            foreach ($users as $i => $u) {
                $data[] = [
                    'no' => $i + 1,
                    'id' => $u->id,
                    'username' => $u->username,
                    'nama_guru' => $u->nama_guru ?? $u->first_name ?? '-',
                    'nip' => $u->nip ?? '-',
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

        $idGuru = $this->request->getPost('id_guru');
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

        // Get guru data
        $guru = $this->masterModel->getGuru((int) $idGuru);

        $data = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $guru ? $guru->nama_guru : $this->request->getPost('first_name'),
            'email' => $this->request->getPost('email'),
            'role' => '2',
            'active' => '1',
            'created_on' => time(),
        ];

        $db->table('users')->insert($data);
        $userId = $db->insertID();

        // Link to master_guru
        if ($guru) {
            $db->table('master_guru')
                ->where('id_guru', $idGuru)
                ->update(['id_user' => $userId]);
        }

        return $this->response->setJSON(['status' => true, 'message' => 'User guru berhasil ditambahkan']);
    }

    public function resetPassword()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id');
        $password = $this->request->getPost('password');

        if (!$password) {
            return $this->response->setJSON(['status' => false, 'message' => 'Password wajib diisi']);
        }

        $db = \Config\Database::connect();
        $result = $db->table('users')
            ->where('id', $id)
            ->update(['password' => password_hash($password, PASSWORD_DEFAULT)]);

        return $result
            ? $this->response->setJSON(['status' => true, 'message' => 'Password berhasil direset'])
            : $this->response->setJSON(['status' => false, 'message' => 'Gagal reset password']);
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $id = (int) $this->request->getPost('id');
        $db = \Config\Database::connect();

        // Unlink from master_guru
        $db->table('master_guru')->where('id_user', $id)->update(['id_user' => null]);

        $result = $db->table('users')->where('id', $id)->delete();

        return $result
            ? $this->response->setJSON(['status' => true, 'message' => 'User berhasil dihapus'])
            : $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus user']);
    }
}
