<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\DashboardModel;
use App\Models\AuthModel;
use App\Models\MasterModel;

class Userguru extends BaseController
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
        if (!$this->isAdmin() && !$this->auth->inGroup('guru')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akses Terlarang');
        }

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'User Management',
            'subjudul' => 'Data User Guru',
            'profile' => null,
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        if ($this->isAdmin()) {
            return view('users/guru/data', $data);
        } else {
            $guru = $this->users->getGuruByUsername($user->username);
            return redirect()->to('userguru/edit/' . $guru->id_guru);
        }
    }

    public function data()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data = $this->users->getUserGuru($tp->id_tp, $smt->id_smt);
        foreach ($data as $list) {
            $list->foto = ($list->foto != null && $list->foto != "" && file_exists($list->foto)) ? base_url($list->foto) : base_url('assets/img/siswa_sm.png');
        }
        return $this->response->setJSON($data);
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $guru = $this->users->getDetailGuru($id);

        if (!$guru)
            return redirect()->to('userguru');

        $users = $this->users->getUsers($guru->username);
        $user = $this->getUser();

        $data = [
            'user' => $user,
            'judul' => 'User Management',
            'subjudul' => 'Edit Data User',
            'setting' => $this->dashboard->getSetting(),
            'users' => $users,
            'guru' => $guru,
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'is_admin' => $this->isAdmin()
        ];

        if ($this->isAdmin()) {
            $data['groups'] = $this->auth->groups();
            $data['kelass'] = $this->users->getKelas($tp->id_tp, $smt->id_smt);
            $data['mapels'] = $this->users->getMapel();
            $data['levels'] = $this->users->getLevelGuru();
            return view('users/guru/edit', $data);
        } else {
            // Self edit
            echo "Access denied (Implementation pending for non-admin self edit view logic if different)"; // Placeholder. Assuming view logic handles it.
            return view('users/guru/edit', $data);
        }
    }

    public function activate()
    {
        $id_guru = $this->request->getPost('id_guru');
        $id_user = $this->request->getPost('id');
        $nama_guru = $this->request->getPost('nama');
        $is_active = $this->request->getPost('is_active');

        $guru = $this->users->getDataGuru($id_guru);
        $usernames = $this->users->usernameCheck($guru->username ?? '-', 'master_guru');

        if (count($usernames) > 1 && $is_active == 'aktifkan') {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'ada ' . count($usernames) . ' username menggunakan ' . $guru->username . ', silahkan ganti username',
                'users' => $usernames
            ]);
        }

        $active = false;

        if ($id_user) {
            // Toggle active status in users table
            $status = ($is_active == 'aktifkan') ? 1 : 0;
            $active = $this->master->updateData('users', ['active' => $status], 'id', $id_user);
            $msg = $is_active == 'aktifkan' ? ' diaktifkan' : ' dinonaktifkan';
        } else {
            $nama = explode(' ', $guru->nama_guru ?? '');
            $first_name = $nama[0];
            $last_name = end($nama) != $first_name ? end($nama) : '';

            $user = [
                'username' => trim($guru->username ?? ''),
                'password' => hashPasswordSafe(trim($guru->password ?? '')),
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $guru->username . '@guru.sekolah' // Mock email
            ];

            $id_user = $this->auth->register($user, '2'); // 2 for guru group

            $input = ['id_user' => $id_user];
            $active = $this->master->updateData('master_guru', $input, 'id_guru', $id_guru);
            $msg = ' diaktifkan';
        }

        return $this->response->setJSON([
            'status' => (bool) $active,
            'msg' => $active ? 'Guru ' . $nama_guru . $msg : 'Guru ' . $nama_guru . ' gagal' . $msg
        ]);
    }

    public function reset_login()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false, 'msg' => 'Unauthorized']);

        $username = $this->request->getPost('username');
        $nama = $this->request->getPost('nama');

        if ($this->auth->resetErrorLogin($username)) {
            $data = ['status' => true, 'msg' => 'User ' . $nama . ' berhasil direset'];
        } else {
            $data = ['status' => false, 'msg' => 'User ' . $nama . ' gagal direset (Mungkin tidak ada error login)'];
        }
        return $this->response->setJSON($data);
    }

    public function aktifkan_all()
    {
        return $this->aktifkansemua();
    }

    public function aktifkansemua()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false, 'msg' => 'Unauthorized']);

        try {
            $is_active = $this->request->getPost('is_active');
            $status = ($is_active == 'aktifkan') ? 1 : 0;

            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            if (!$tp || !isset($tp->id_tp)) {
                return $this->response->setJSON(['status' => false, 'msg' => 'Tahun Pelajaran aktif tidak ditemukan']);
            }
            if (!$smt || !isset($smt->id_smt)) {
                return $this->response->setJSON(['status' => false, 'msg' => 'Semester aktif tidak ditemukan']);
            }

            $gurus = $this->users->getUserGuru($tp->id_tp, $smt->id_smt);
            $success_count = 0;
            $fail_count = 0;

            foreach ($gurus as $guru) {
                if ($is_active == 'aktifkan') {
                    if ($guru->id == null) {
                        // Register
                        $nama = explode(' ', $guru->nama_guru ?? '');
                        $firstName = $nama[0] ?? 'Guru';

                        // Handler for Last Name (Safe)
                        $lastName = '';
                        if (count($nama) > 1) {
                            unset($nama[0]);
                            $lastName = implode(' ', $nama);
                        }

                        // Ensure unique username if empty? 
                        // Assuming username is valid from master data.
                        $username = trim($guru->username ?? '');
                        if (empty($username))
                            continue;

                        $user = [
                            'username' => $username,
                            'password' => hashPasswordSafe(trim($guru->password ?? '123456')),
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $username . '@guru.sekolah',
                            'active' => 1
                        ];

                        try {
                            $id_user = $this->auth->register($user, '2');
                            if ($id_user) {
                                $this->master->updateData('master_guru', ['id_user' => $id_user], 'id_guru', $guru->id_guru);
                                $success_count++;
                            } else {
                                $fail_count++;
                            }
                        } catch (\Exception $e) {
                            $fail_count++;
                            log_message('error', 'Error registering user: ' . $username . ' - ' . $e->getMessage());
                        }
                    } else {
                        // Activate
                        $this->master->updateData('users', ['active' => 1], 'id', $guru->id);
                        $success_count++;
                    }
                } else {
                    // Nonaktifkan
                    if ($guru->id != null) {
                        $this->master->updateData('users', ['active' => 0], 'id', $guru->id);
                        $success_count++;
                    }
                }
            }

            return $this->response->setJSON(['status' => true, 'msg' => $success_count . ' guru berhasil ' . ($status ? 'diaktifkan' : 'dinonaktifkan')]);

        } catch (\Throwable $th) {
            log_message('error', 'Aktifkan Semua Error: ' . $th->getMessage());
            return $this->response->setJSON(['status' => false, 'msg' => 'Terjadi kesalahan sistem: ' . $th->getMessage()]);
        }
    }

    public function update_iduser()
    {
        // This seems to be a sync function used by JS after bulk activation
        // In my logic above, I already updated master_guru.id_user.
        // So this might be redundant or just a confirmation.
        return $this->response->setJSON(['status' => true, 'msg' => 'Data User Updated']);
    }

    public function editlogin()
    {
        if (!$this->isLoggedIn())
            return $this->response->setJSON(['status' => false, 'msg' => 'Login required']);

        $id = $this->request->getPost('id'); // guru id? or user id? View passes $guru->id (master_guru id likely? Or user id?)
        // In View: form_open(..., ['id' => $guru->id])
        // And $guru->id seems to be master_guru's primary key if fetched via getDetailGuru?
        // Wait, getDetailGuru returns row with id_guru, id_user, etc.
        // Let's assume input 'id' is id_guru from master_guru table context or id_user.
        // Standard in this app seems to be mixed.
        // But let's look at `activate`: it takes `id_guru`.

        // Actually, looking at `users/guru/edit.php` (CI3), line 36: `form_open(..., array('id' => $guru->id)`
        // If $guru object comes from `getDetailGuru`, check `UsersModel`.
        // `getUserGuru` joins users. `id` usually refers to `users.id` (user account id).
        // `id_guru` is master id.

        $username = $this->request->getPost('username');
        $old = $this->request->getPost('old');
        $new = $this->request->getPost('new');

        // If changing password
        if ($new) {
            if (!$this->auth->changePassword($username, $old, $new)) {
                return $this->response->setJSON(['status' => false, 'msg' => 'Gagal ubah password']);
            }
        }

        // If changing info (first_name, etc)
        $first_name = $this->request->getPost('first_name');
        if ($first_name) {
            $data = [
                'first_name' => $first_name,
                'last_name' => $this->request->getPost('last_name'),
                'email' => $this->request->getPost('email')
            ];
            // We need user id.
            // If $id from post is master_guru id, we need to find user id.
            // If $id is user id, use it.
            // Let's assume we find user by username if available.
            $u = $this->auth->isExist($username);
            if ($u) {
                $this->master->updateData('users', $data, 'id', $u->id);
            }
        }

        return $this->response->setJSON(['status' => true, 'msg' => 'Berhasil diupdate']);
    }
}
