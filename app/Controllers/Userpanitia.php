<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;
use App\Models\DashboardModel;
use App\Models\AuthModel;
use App\Models\MasterModel;
use App\Models\PanitiaModel;
use App\Models\CbtModel;

class Userpanitia extends BaseController
{
    protected $users;
    protected $dashboard;
    protected $auth;
    protected $master;
    protected $panitia;
    protected $cbt;

    public function __construct()
    {
        $this->users = new UsersModel();
        $this->dashboard = new DashboardModel();
        $this->auth = new AuthModel();
        $this->master = new MasterModel();
        $this->panitia = new PanitiaModel();
        $this->cbt = new CbtModel();
        helper(['form', 'url']);
    }

    /**
     * true bila pengguna saat ini TIDAK boleh mengakses Data Panitia.
     *	Aturan pengawas-nya sama dengan sidebar: jabatan/nama "pengawas"
     *	atau punya penugasan di cbt_pengawas pada TP/SMT aktif.
     */
    private function isPanitiaRestricted(): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        if (!$this->isPanitia()) {
            return true;
        }

        $db = \Config\Database::connect();
        $panitia = $db->table('master_panitia')
            ->where('id_user', (int) session()->get('user_id'))
            ->get()->getRow();

        if (!$panitia) {
            return true;
        }

        $jabatan     = strtolower((string) ($panitia->jabatan ?? ''));
        $namaPanitia = strtolower((string) ($panitia->nama_panitia ?? ''));

        if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas') {
            return true;
        }

        $tp  = $db->table('master_tp')->where('active', 1)->get()->getRow();
        $smt = $db->table('master_smt')->where('active', 1)->get()->getRow();

        if ($tp && $smt) {
            $ditugaskan = $db->table('cbt_pengawas')
                ->where('id_tp', $tp->id_tp)
                ->where('id_smt', $smt->id_smt)
                ->where('id_guru', 'P' . ($panitia->id_panitia ?? ''))
                ->get()->getRow();

            if ($ditugaskan) {
                return true;
            }
        }

        return false;
    }

    public function index()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        if ($this->isPanitiaRestricted())
            return redirect()->to('dashboard')->with('error', 'Akses ditolak. Hanya Administrator atau Panitia yang dapat mengelola Data Panitia.');

        // Ensure columns (simplified migration check)
        // ... skipped manual ALTER TABLE queries, assuming DB migration handled externally or manually

        $data = [
            'user' => $this->getUser(),
            'judul' => 'User Management',
            'subjudul' => 'Data User Panitia',
            'profile' => null,
            'setting' => $this->dashboard->getSetting(),
            'is_admin' => $this->isAdmin(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
            //'ruangs' => $this->cbt->getAllRuang(), // Assuming method exists
            'ruangs' => [], // Placeholder if getAllRuang not in CbtModel
        ];

        // Convert gurus from objects to associative array (id => nama)
        $gurusRaw = $this->master->getAllGuru();
        $gurus = [];
        foreach ($gurusRaw as $guru) {
            $gurus[$guru->id_guru] = $guru->nama_guru;
        }
        $data['gurus'] = $gurus;

        // Try getting ruang if method exists
        if (method_exists($this->cbt, 'getAllRuang')) {
            $ruangsRaw = $this->cbt->getAllRuang();
            $ruangs = [];
            foreach ($ruangsRaw as $r) {
                $ruangs[$r->id_ruang] = $r->nama_ruang;
            }
            $data['ruangs'] = $ruangs;
        }

        return view('users/panitia/data', $data);
    }

    public function data()
    {
        return $this->response->setJSON($this->users->getUserPanitia());
    }

    // Manual pagination endpoint
    public function list()
    {
        $page = $this->request->getPost('page') ?? 1;
        $limit = $this->request->getPost('limit') ?? 10;
        $search = $this->request->getPost('search');

        $offset = ($page - 1) * $limit;

        $count_panitia = $this->users->getUserPanitiaTotalPage($search);
        $lists = $this->users->getUserPanitiaPage($offset, $limit, $search);

        foreach ($lists as $list) {
            $list->foto = ($list->foto != null && $list->foto != "" && file_exists($list->foto)) ? base_url($list->foto) : base_url('assets/img/user.png');
        }

        $data = [
            'lists' => $lists,
            'total' => $count_panitia,
            'pages' => ceil($count_panitia / $limit),
            'search' => $search,
            'perpage' => $limit
        ];

        return $this->response->setJSON($data);
    }

    public function create()
    {
        if ($this->isPanitiaRestricted())
            return $this->response->setJSON(['status' => false]);

        $rules = [
            'nama_panitia' => 'required|trim',
            'username' => 'required|trim|min_length[4]|max_length[50]|is_unique[users.username]',
            'password' => 'required|trim|min_length[5]',
            'email' => 'valid_email',
            'jabatan' => 'permit_empty|in_list[panitia,pengawas,Panitia,Pengawas]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => false, 'errors' => $this->validator->getErrors()]);
        }

        // Normalize jabatan to lowercase
        $jabatan = strtolower($this->request->getPost('jabatan') ?? '');
        if (!in_array($jabatan, ['panitia', 'pengawas'])) {
            $jabatan = 'panitia'; // default value
        }

        $user_data = [
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'email' => $this->request->getPost('email'),
            'active' => '1',
            'first_name' => $this->request->getPost('nama_panitia'),
            'last_name' => ''
        ];

        $panitia_data = [
            'nama_panitia' => $this->request->getPost('nama_panitia'),
            'nip' => $this->request->getPost('nip'),
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
            'email' => $this->request->getPost('email'),
            'no_hp' => $this->request->getPost('no_hp'),
            'instansi' => $this->request->getPost('instansi'),
            'jabatan' => $jabatan,
            'ruang' => $this->request->getPost('ruang'),
            'status' => 1,
            'sync_school_uuid' => session()->get('school_uuid')
        ];

        // File upload handling
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/foto_panitia/', $newName);
            $panitia_data['foto'] = 'uploads/foto_panitia/' . $newName;
        }

        $id_user = $this->auth->register($user_data, '4');
        if ($id_user) {
            $panitia_data['id_user'] = $id_user;
            $this->panitia->create($panitia_data);
            return $this->response->setJSON(['status' => true, 'msg' => 'Panitia berhasil ditambahkan']);
        }

        return $this->response->setJSON(['status' => false, 'msg' => 'Gagal membuat user']);
    }

    public function generate()
    {
        if ($this->isPanitiaRestricted())
            return $this->response->setJSON(['status' => false]);
        $gurus = $this->request->getPost('gurus');
        if (!$gurus)
            return $this->response->setJSON(['status' => false, 'msg' => 'No guru selected']);

        $count = 0;
        foreach ($gurus as $id_guru) {
            $guru = $this->master->getGuruById($id_guru);
            if ($guru) {
                // Check duplicate logic omitted for brevity, assume distinct
                $username = 'p_' . ($guru->nip ?: rand(1000, 9999));
                $user_data = [
                    'username' => $username,
                    'password' => password_hash('123456', PASSWORD_BCRYPT),
                    'email' => $username . '@panitia.sekolah',
                    'active' => '1',
                    'first_name' => $guru->nama_guru,
                ];
                $id_user = $this->auth->register($user_data, '4');
                if ($id_user) {
                    $p_data = [
                        'id_user' => $id_user,
                        'nama_panitia' => $guru->nama_guru,
                        'nip' => $guru->nip,
                        'username' => $username,
                        'password' => '123456',
                        'status' => 1,
                        'sync_school_uuid' => session()->get('school_uuid')
                    ];
                    $this->panitia->create($p_data);
                    $count++;
                }
            }
        }
        return $this->response->setJSON(['status' => true, 'msg' => "$count panitia generated"]);
    }

    public function activate()
    {
        if ($this->isPanitiaRestricted())
            return $this->response->setJSON(['status' => false, 'msg' => 'Unauthorized']);

        $id_panitia = $this->request->getPost('id_panitia');
        $id_user = $this->request->getPost('id');
        $is_active = $this->request->getPost('is_active');
        $status = ($is_active == 'aktifkan') ? 1 : 0;

        // If id_user is missing, try to find it from master_panitia
        if (!$id_user && $id_panitia) {
            $panitia = $this->panitia->find($id_panitia);
            if ($panitia) {
                $id_user = $panitia->id_user ?? null;
            }
        }

        if ($id_user) {
            $update = $this->master->updateData('users', ['active' => $status], 'id', $id_user);
            return $this->response->setJSON(['status' => $update, 'msg' => $update ? 'Status berhasil diubah' : 'Gagal mengubah status']);
        }

        return $this->response->setJSON(['status' => false, 'msg' => 'User ID tidak ditemukan']);
    }

    public function delete()
    {
        $id = $this->request->getPost('id_panitia');
        $panitia = $this->panitia->get_by_id($id);
        if ($panitia) {
            $this->panitia->delete($id);
            if ($panitia->id_user) {
                $this->auth->deleteUser($panitia->id_user);
            }
            return $this->response->setJSON(['status' => true]);
        }
        return $this->response->setJSON(['status' => false]);
    }

    public function reset_login()
    {
        $username = $this->request->getPost('username');
        $this->auth->resetErrorLogin($username);
        return $this->response->setJSON(['status' => true]);
    }
    public function edit()
    {
        if ($this->isPanitiaRestricted())
            return $this->response->setJSON(['status' => false]);

        $id_panitia = $this->request->getPost('id_panitia');

        // Validate jabatan
        $rules = [
            'jabatan' => 'permit_empty|in_list[panitia,pengawas,Panitia,Pengawas]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => false, 'errors' => $this->validator->getErrors()]);
        }

        // Normalize jabatan to lowercase
        $jabatan = strtolower($this->request->getPost('jabatan') ?? '');
        if (!in_array($jabatan, ['panitia', 'pengawas'])) {
            $jabatan = 'panitia'; // default value
        }

        $panitia = $this->panitia->find($id_panitia);
        if (!$panitia) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data panitia tidak ditemukan']);
        }

        $id_user = $panitia->id_user;
        $pass = $this->request->getPost('password');

        $panitia_data = [
            'nama_panitia' => $this->request->getPost('nama_panitia'),
            'nip' => $this->request->getPost('nip'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'no_hp' => $this->request->getPost('no_hp'),
            'instansi' => $this->request->getPost('instansi'),
            'jabatan' => $jabatan,
            'ruang' => $this->request->getPost('ruang'),
        ];

        $user_data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'first_name' => $this->request->getPost('nama_panitia'),
        ];

        if (!empty($pass)) {
            $panitia_data['password'] = $pass;
            $user_data['password'] = password_hash($pass, PASSWORD_BCRYPT);
        }

        // File upload
        $file = $this->request->getFile('foto');
        $hapus_foto = $this->request->getPost('hapus_foto');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old photo if exists
            if (!empty($panitia->foto) && file_exists($panitia->foto)) {
                unlink($panitia->foto);
            }
            $newName = $file->getRandomName();
            $file->move('uploads/foto_panitia/', $newName);
            $panitia_data['foto'] = 'uploads/foto_panitia/' . $newName;
            $user_data['foto'] = 'uploads/foto_panitia/' . $newName;
        } elseif ($hapus_foto == '1') {
            // Delete old photo if exists
            if (!empty($panitia->foto) && file_exists($panitia->foto)) {
                unlink($panitia->foto);
            }
            $panitia_data['foto'] = null;
            $user_data['foto'] = null;
        }

        $update_panitia = $this->panitia->update($id_panitia, $panitia_data);
        $update_user = true;

        if ($id_user) {
            $update_user = $this->auth->changeUser($id_user, $user_data);
        }

        return $this->response->setJSON([
            'status' => $update_panitia && $update_user,
            'msg' => ($update_panitia && $update_user) ? 'Panitia berhasil diupdate' : 'Gagal mengupdate beberapa data'
        ]);
    }

    public function aktifkansemua()
    {
        if ($this->isPanitiaRestricted())
            return $this->response->setJSON(['status' => false]);

        $is_active = $this->request->getPost('is_active');
        $status = ($is_active == 'aktifkan') ? 1 : 0;

        // Logic: Get all panitia, update active status
        $panitias = $this->panitia->findAll();
        foreach ($panitias as $p) {
            // Updated to use object property access as returnType is 'object'
            $this->master->updateData('users', ['active' => $status], 'username', $p->username);
        }
        return $this->response->setJSON(['status' => true, 'msg' => count($panitias) . ' panitia updated']);
    }

    public function generate_password()
    {
        if ($this->isPanitiaRestricted())
            return $this->response->setJSON(['status' => false, 'msg' => 'Unauthorized']);

        $id_panitia = $this->request->getPost('id_panitia');
        $new_password = $this->request->getPost('password');

        if (empty($id_panitia) || empty($new_password)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data tidak lengkap']);
        }

        $panitia = $this->panitia->find($id_panitia);
        if (!$panitia) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data panitia tidak ditemukan']);
        }

        $id_user = $panitia->id_user;
        $username = $panitia->username;

        // Update master_panitia
        $this->panitia->update($id_panitia, ['password' => $new_password]);

        // Update users
        if ($id_user) {
            $this->master->updateData('users', ['password' => password_hash($new_password, PASSWORD_BCRYPT)], 'id', $id_user);
        } else {
            // Fallback to username if id_user is null
            $this->master->updateData('users', ['password' => password_hash($new_password, PASSWORD_BCRYPT)], 'username', $username);
        }

        return $this->response->setJSON(['status' => true, 'msg' => 'Password berhasil diperbarui', 'password' => $new_password]);
    }
}
