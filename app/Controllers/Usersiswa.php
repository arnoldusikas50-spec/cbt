<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\DashboardModel;
use App\Models\AuthModel;
use App\Models\MasterModel;

class Usersiswa extends BaseController
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
        if (!$this->isAdmin() && !$this->isPanitia()) {
            return redirect()->to('dashboard');
        }

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'User Management',
            'subjudul' => 'Data User Siswa',
            'profile' => null,
            'setting' => $this->dashboard->getSetting(),
            'is_admin' => $this->isAdmin(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        // Load role specific logic if panitia
        $is_pengawas = false;
        if ($this->isPanitia()) {
            $tp_active = $data['tp_active'];
            $smt_active = $data['smt_active'];
            $panitia = $this->dashboard->getDataPanitiaByUserId($user->id, $tp_active->id_tp, $smt_active->id_smt);
            $data['panitia'] = $panitia;

            if ($panitia) {
                $jabatan = strtolower($panitia->jabatan ?? '');
                $namaPanitia = strtolower($panitia->nama_panitia ?? '');
                
                // Get assignments to determine if really a supervisor
                $db = \Config\Database::connect();
                $assignments = $db->table('cbt_pengawas')
                    ->where('id_tp', $tp_active->id_tp)
                    ->where('id_smt', $smt_active->id_smt)
                    ->where('id_guru', 'P' . $panitia->id_panitia)
                    ->get()->getResult();

                if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas' || !empty($assignments)) {
                    $is_pengawas = true;
                }
            }
        }
        $data['is_pengawas'] = $is_pengawas;

        return view('users/siswa/data', $data);
    }

    // JSON Data endpoint for Datatables
    public function data()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        return $this->response->setJSON($this->users->getUserSiswa($tp->id_tp, $smt->id_smt));
    }

    // Manual pagination endpoint
    public function list()
    {
        $page = $this->request->getPost('page') ?? 1;
        $limit = $this->request->getPost('limit') ?? 10;
        $search = $this->request->getPost('search');
        $reset = $this->request->getPost('reset') === '1';

        $offset = ($page - 1) * $limit;
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $count_siswa = $this->users->getUserSiswaTotalPage($search, $reset);
        $lists = $this->users->getUserSiswaPage($tp->id_tp, $smt->id_smt, $offset, $limit, $search, null, null, $reset);

        foreach ($lists as $list) {
            $list->foto = ($list->foto != null && $list->foto != "" && file_exists($list->foto)) ? base_url($list->foto) : base_url('assets/img/siswa_sm.png');
        }

        $data = [
            'lists' => $lists,
            'total' => $count_siswa,
            'pages' => ceil($count_siswa / $limit),
            'search' => $search,
            'perpage' => $limit
        ];

        return $this->response->setJSON($data);
    }

    public function activate()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Silahkan login terlebih dahulu']);
        }
        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Akses ditolak']);
        }

        $id_siswa = $this->request->getPost('id_siswa');
        $is_active = $this->request->getPost('is_active');
        $namaParam = $this->request->getPost('nama');

        $siswa = $this->master->getSiswa($id_siswa);
        if (!$siswa) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data siswa tidak ditemukan']);
        }

        $namaSiswa = !empty($siswa->nama) ? $siswa->nama : urldecode($namaParam ?? '');
        $username = trim($siswa->username ?? '');

        if (empty($username)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Username siswa kosong, tidak dapat diaktifkan']);
        }

        $user = $this->auth->isExist($username);
        $active = false;

        if ($user) {
            $status = ($is_active == 'aktifkan') ? 1 : 0;
            $active = $this->master->updateData('users', ['active' => $status], 'id', $user->id);
            $msg = ($is_active == 'aktifkan') ? ' diaktifkan' : ' dinonaktifkan';
        } else {
            if ($is_active == 'aktifkan') {
                $user_data = [
                    'username'   => $username,
                    'password'   => hashPasswordSafe(trim($siswa->password ?? '123456')),
                    'first_name' => $namaSiswa,
                    'email'      => $username . '@siswa.sekolah',
                    'active'     => 1
                ];
                $id_user = $this->auth->register($user_data, '3'); // 3 for group siswa
                $active = (bool) $id_user;
                $msg = ' diaktifkan';
            } else {
                $active = true;
                $msg = ' dinonaktifkan';
            }
        }

        return $this->response->setJSON([
            'status' => (bool) $active,
            'msg'    => $active ? 'Siswa ' . $namaSiswa . $msg : 'Gagal memproses status siswa ' . $namaSiswa
        ]);
    }

    public function reset_login()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Silahkan login terlebih dahulu']);
        }
        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Akses ditolak']);
        }

        $username = $this->request->getPost('username');
        $nama = $this->request->getPost('nama');
        $reset = $this->auth->resetErrorLogin($username);

        $namaUser = !empty($nama) ? urldecode($nama) : $username;
        return $this->response->setJSON([
            'status' => (bool) $reset,
            'msg'    => $reset ? 'User ' . $namaUser . ' berhasil direset' : 'User ' . $namaUser . ' gagal direset (Mungkin tidak ada error login)'
        ]);
    }

    public function aktifkan_all()
    {
        return $this->aktifkansemua();
    }

    public function aktifkansemua()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Silahkan login terlebih dahulu']);
        }
        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Akses ditolak']);
        }

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

            $siswas = $this->users->getUserSiswa($tp->id_tp, $smt->id_smt);
            $successCount = 0;
            $failCount = 0;

            if ($is_active == 'aktifkan') {
                foreach ($siswas as $siswa) {
                    $username = trim($siswa->username ?? '');
                    if (empty($username)) continue;

                    $user = $this->auth->isExist($username);
                    if ($user) {
                        $this->master->updateData('users', ['active' => 1], 'id', $user->id);
                        $successCount++;
                    } else {
                        $user_data = [
                            'username'   => $username,
                            'password'   => hashPasswordSafe(trim($siswa->password ?? '123456')),
                            'first_name' => trim($siswa->nama ?? 'Siswa'),
                            'email'      => $username . '@siswa.sekolah',
                            'active'     => 1
                        ];
                        try {
                            $id_user = $this->auth->register($user_data, '3');
                            if ($id_user) {
                                $successCount++;
                            } else {
                                $failCount++;
                            }
                        } catch (\Exception $e) {
                            $failCount++;
                            log_message('error', 'Error registering student user: ' . $username . ' - ' . $e->getMessage());
                        }
                    }
                }
                $msg = "Berhasil mengaktifkan {$successCount} user siswa.";
            } else {
                $db = \Config\Database::connect();
                $db->query("UPDATE users JOIN users_groups ON users_groups.user_id = users.id SET users.active = 0 WHERE users_groups.group_id = 3");
                $msg = "Berhasil menonaktifkan semua user siswa.";
                $successCount = count($siswas);
            }

            return $this->response->setJSON([
                'status'  => true,
                'msg'     => $msg,
                'success' => $successCount,
                'fail'    => $failCount
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in Usersiswa::aktifkansemua: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}
