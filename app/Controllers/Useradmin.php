<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\DashboardModel;
use App\Models\AuthModel;
use App\Models\MasterModel;

class Useradmin extends BaseController
{
    protected $users;
    protected $dashboard;
    protected $auth;
    protected $master;

    public function __construct()
    {
        $this->users = new UsersModel();
        $this->dashboard = new DashboardModel();
        $this->auth = new AuthModel();
        $this->master = new MasterModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        if (!$this->isAdmin())
            return redirect()->to('dashboard');

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'Admin Management',
            'subjudul' => 'Data Admin',
            'profile' => null,
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
            'admins' => $this->users->getAllAdmin(),
            'is_admin' => $this->isAdmin()
        ];

        return view('users/admin/data', $data);
    }

    public function data()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        return $this->response->setJSON($this->users->getAllAdmin());
    }

    public function create()
    {
        $rules = [
            'username' => 'required',
            'first_name' => 'required',
            'password' => 'trim|min_length[6]|max_length[20]|required',
            'confirm_password' => 'trim|matches[password]|required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors()
            ]);
        } else {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $first_name = $this->request->getPost('first_name');
            $last_name = $this->request->getPost('last_name');

            if ($this->auth->isExist($username)) {
                return $this->response->setJSON([
                    'status' => false,
                    'msg' => 'Username tidak tersedia (sudah digunakan).'
                ]);
            } else {
                $user = [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $this->request->getPost('email')
                ];
                $this->auth->register($user, '1');
                return $this->response->setJSON([
                    'status' => true,
                    'msg' => 'User berhasil dibuat.'
                ]);
            }
        }
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'Administrator',
            'subjudul' => 'Edit Data Admin',
            'users' => $this->auth->user($id),
            'groups' => $this->auth->groups(),
            'level' => $this->isAdmin() ? '1' : '2',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
            'is_admin' => $this->isAdmin()
        ];

        return view('users/admin/edit', $data);
    }

    public function edit_info()
    {
        if (!$this->validate(['first_name' => 'required'])) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors()
            ]);
        } else {
            $id = $this->request->getPost('id');
            $input = [
                'first_name' => $this->request->getPost('first_name'),
                'last_name' => $this->request->getPost('last_name'),
                'email' => $this->request->getPost('email'),
                'jabatan' => $this->request->getPost('jabatan'),
            ];
            $update = $this->master->updateData('users', $input, 'id', $id);
            return $this->response->setJSON(['status' => (bool) $update]);
        }
    }

    public function edit_status()
    {
        if (!$this->validate(['status' => 'required'])) {
            return $this->response->setJSON(['status' => false, 'errors' => $this->validator->getErrors()]);
        }
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $update = $this->master->updateData('users', ['active' => $status], 'id', $id);
        return $this->response->setJSON(['status' => (bool) $update]);
    }

    public function delete($id)
    {
        $status = $this->auth->deleteUser($id);
        return $this->response->setJSON(['status' => (bool) $status]);
    }

    public function uploadFile($id_user)
    {
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = 'foto_' . $id_user . '.' . $file->getExtension();
            $file->move('uploads/profiles/', $newName, true);
            $src = 'uploads/profiles/' . $newName;

            // Check if user has photo in DB
            $db = \Config\Database::connect();
            $user_exists = $this->auth->user($id_user);
            if ($user_exists) {
                $db->table('users')->where('id', $id_user)->update(['foto' => $src]);
            }

            return $this->response->setJSON([
                'status' => true,
                'src' => base_url($src),
                'filename' => $newName
            ]);
        }
        return $this->response->setJSON(['status' => false, 'src' => 'Upload failed']);
    }

    public function deleteFile()
    {
        $src = $this->request->getPost('src');
        if ($src) {
            $path = str_replace(base_url(), '', $src);
            // handle leading slash
            $path = ltrim($path, '/');
            if (file_exists($path)) {
                unlink($path);
                return $this->response->setJSON(['status' => true, 'msg' => 'File deleted']);
            }
        }
        return $this->response->setJSON(['status' => false, 'msg' => 'File not found']);
    }

    public function change_password()
    {
        $rules = [
            'old' => 'required',
            'new' => 'required|min_length[5]',
            'new_confirm' => 'required|matches[new]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $id = $this->request->getPost('id');
        $old = $this->request->getPost('old');
        $new = $this->request->getPost('new');

        $user = $this->auth->user($id);
        if (!$user)
            return $this->response->setJSON(['status' => false, 'msg' => 'User not found']);

        // AuthModel::changePassword verifies the old password with password_verify
        // and re-hashes the new one (bcrypt) before updating.
        if ($this->auth->changePassword($user->username, $old, $new)) {
            return $this->response->setJSON(['status' => true, 'msg' => 'Password berhasil diubah']);
        }
        return $this->response->setJSON(['status' => false, 'msg' => 'Gagal mengubah password (password lama salah?)']);
    }

    public function saveProfile()
    {
        return $this->response->setJSON(['status' => true]);
    }
}
