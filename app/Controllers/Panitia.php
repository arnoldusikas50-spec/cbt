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

        if (!$this->isPanitia()) {
            return redirect()->to('dashboard');
        }

        $db = \Config\Database::connect();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $userId = session()->get('user_id');

        // Helper to check TTD column existence (CI3 logic)
        if (!$db->fieldExists('ttd', 'master_panitia')) {
            $db->query("ALTER TABLE master_panitia ADD COLUMN ttd VARCHAR(255) DEFAULT NULL AFTER foto");
        }

        $panitia = $this->dashboardModel->getDataPanitiaByUserId($userId, $tp->id_tp, $smt->id_smt);

        if (!$panitia) {
            // Create a temporary object if record is missing in master_panitia
            $user = $this->getUser();
            $panitia = (object) [
                'id_panitia' => '',
                'nama_panitia' => $user->first_name,
                'email' => $user->email,
                'nip' => '',
                'no_hp' => '',
                'instansi' => '',
                'jabatan' => '',
                'ruang' => '',
                'foto' => $user->foto,
                'ttd' => '',
                'jenis_kelamin' => '',
                'agama' => '',
                'api_key' => ''
            ];
        }
        
        $cbtModel = new \App\Models\CbtModel();
        
        // Robust is_pengawas detection
        $is_pengawas = false;
        if (!empty($panitia->id_panitia)) {
            $jabatan = strtolower($panitia->jabatan ?? '');
            $namaPanitiaStr = strtolower($panitia->nama_panitia ?? '');
            
            // Get assignments to determine if really a supervisor
            $db_conn = \Config\Database::connect();
            $assignments = $db_conn->table('cbt_pengawas')
                ->where('id_tp', $tp->id_tp)
                ->where('id_smt', $smt->id_smt)
                ->where('id_guru', 'P' . $panitia->id_panitia)
                ->get()->getResult();

            if ($jabatan === 'pengawas' || $namaPanitiaStr === 'pengawas' || !empty($assignments)) {
                $is_pengawas = true;
            }
        }

        $data = [
            'judul' => 'Profil Panitia',
            'subjudul' => 'Edit Profil',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'panitia' => $panitia,
            'user' => $this->getUser(),
            'ruangs' => $cbtModel->getAllRuang(),
            'is_pengawas' => $is_pengawas
        ];

        // Format ruangs for dropdown
        $data['ruang_options'] = [];
        foreach ($data['ruangs'] as $r) {
            $data['ruang_options'][$r->id_ruang] = $r->nama_ruang;
        }

        return view('members/panitia/profil', $data);
    }

    public function save()
    {
        if (!$this->isPanitia()) {
            return $this->respond(false, 'Akses ditolak');
        }

        $id_panitia = $this->request->getPost('id_panitia');
        $nama = $this->request->getPost('nama_panitia');
        $nip = $this->request->getPost('nip');
        $email = $this->request->getPost('email');
        $no_hp = $this->request->getPost('no_hp');
        $instansi = $this->request->getPost('instansi');
        $jabatan = $this->request->getPost('jabatan');
        $ruang = $this->request->getPost('ruang');
        $jenis_kelamin = $this->request->getPost('jenis_kelamin');
        $agama = $this->request->getPost('agama');
        $api_key = $this->request->getPost('api_key');
        $password = $this->request->getPost('password');

        if (empty($nama)) {
            return $this->respond(false, 'Nama Panitia harus diisi');
        }

        $db = \Config\Database::connect();

        // Auto create missing columns if needed (CI3 logic)
        if (!$db->fieldExists('jenis_kelamin', 'master_panitia')) {
            $db->query("ALTER TABLE master_panitia ADD COLUMN jenis_kelamin VARCHAR(2) DEFAULT NULL");
        }
        if (!$db->fieldExists('agama', 'master_panitia')) {
            $db->query("ALTER TABLE master_panitia ADD COLUMN agama VARCHAR(50) DEFAULT NULL");
        }
        if (!$db->fieldExists('api_key', 'master_panitia')) {
            $db->query("ALTER TABLE master_panitia ADD COLUMN api_key TEXT DEFAULT NULL");
        }

        $data_update = [
            'nama_panitia' => $nama,
            'nip' => $nip,
            'email' => $email,
            'no_hp' => $no_hp,
            'instansi' => $instansi,
            'jabatan' => $jabatan,
            'ruang' => $ruang,
            'jenis_kelamin' => $jenis_kelamin,
            'agama' => $agama,
            'api_key' => $api_key
        ];

        // Insert or update logic
        if (empty($id_panitia)) {
            // Create new record
            $userId = session()->get('user_id');
            $data_update['id_user'] = $userId;
            $data_update['username'] = session()->get('username') ?? $this->getUser()->username;
            $data_update['status'] = 1;
            $db->table('master_panitia')->insert($data_update);
            $id_panitia = $db->insertID();
        } else {
            $db->table('master_panitia')
                ->where('id_panitia', $id_panitia)
                ->update($data_update);
        }

        // Update User info (first_name/password) if changed
        $panitia = $db->table('master_panitia')->where('id_panitia', $id_panitia)->get()->getRow();
        $user_update = [
            'first_name' => $nama
        ];

        if (!empty($password)) {
            $user_update['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($panitia && $panitia->id_user) {
            $db->table('users')
                ->where('id', $panitia->id_user)
                ->update($user_update);

            // Update session if it's the current user
            if (session()->get('user_id') == $panitia->id_user) {
                session()->set('first_name', $nama);
            }
        }

        return $this->respond(true, 'Profil berhasil diperbarui');
    }

    public function uploadFoto()
    {
        if (!$this->isPanitia()) {
            return $this->respond(false, 'Akses ditolak');
        }

        $id_panitia = $this->request->getPost('id_panitia');
        $field = $this->request->getPost('field'); // 'foto' or 'ttd'

        if (!in_array($field, ['foto', 'ttd'])) {
            return $this->respond(false, 'Field tidak valid');
        }

        $file = $this->request->getFile('file');

        if (!$file->isValid()) {
            return $this->respond(false, $file->getErrorString());
        }

        $uploadPath = FCPATH . 'uploads/foto_panitia/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $newName = $file->getRandomName();
        if ($file->move($uploadPath, $newName)) {
            $filePath = 'uploads/foto_panitia/' . $newName;

            $db = \Config\Database::connect();
            $panitia = $db->table('master_panitia')->where('id_panitia', $id_panitia)->get()->getRow();

            // Delete old file if exists
            if ($panitia && !empty($panitia->$field) && file_exists(FCPATH . $panitia->$field)) {
                unlink(FCPATH . $panitia->$field);
            }

            $db->table('master_panitia')
                ->where('id_panitia', $id_panitia)
                ->update([$field => $filePath]);

            // Sync foto to users table if field is foto
            if ($field === 'foto' && $panitia->id_user) {
                $db->table('users')
                    ->where('id', $panitia->id_user)
                    ->update(['foto' => $filePath]);
            }

            return $this->respond(true, 'Upload berhasil', ['src' => base_url($filePath)]);
        }

        return $this->respond(false, 'Gagal memindahkan file');
    }

    public function saveSignature()
    {
        if (!$this->isPanitia()) {
            return $this->respond(false, 'Akses ditolak');
        }

        $id_panitia = $this->request->getPost('id_panitia');
        $image = $this->request->getPost('image');

        if (empty($image) || empty($id_panitia)) {
            return $this->respond(false, 'Data tidak lengkap');
        }

        $image = preg_replace('#^data:image/\w+;base64,#i', '', $image);
        $image = str_replace(' ', '+', $image);
        $data = base64_decode($image);

        $fileName = 'ttd_' . $id_panitia . '_' . time() . '.png';
        $filePath = 'uploads/foto_panitia/' . $fileName;

        if (!is_dir(FCPATH . 'uploads/foto_panitia/')) {
            mkdir(FCPATH . 'uploads/foto_panitia/', 0777, true);
        }

        if (file_put_contents(FCPATH . $filePath, $data)) {
            $db = \Config\Database::connect();
            $panitia = $db->table('master_panitia')->where('id_panitia', $id_panitia)->get()->getRow();

            if ($panitia && !empty($panitia->ttd) && file_exists(FCPATH . $panitia->ttd)) {
                unlink(FCPATH . $panitia->ttd);
            }

            $db->table('master_panitia')
                ->where('id_panitia', $id_panitia)
                ->update(['ttd' => $filePath]);

            return $this->respond(true, 'Tanda tangan berhasil disimpan', ['src' => base_url($filePath)]);
        }

        return $this->respond(false, 'Gagal menyimpan file');
    }

    public function deleteTtd()
    {
        if (!$this->isPanitia()) {
            return $this->respond(false, 'Akses ditolak');
        }

        $id_panitia = $this->request->getPost('id_panitia');
        $db = \Config\Database::connect();
        $panitia = $db->table('master_panitia')->where('id_panitia', $id_panitia)->get()->getRow();

        if ($panitia && !empty($panitia->ttd)) {
            if (file_exists(FCPATH . $panitia->ttd)) {
                unlink(FCPATH . $panitia->ttd);
            }
            $db->table('master_panitia')
                ->where('id_panitia', $id_panitia)
                ->update(['ttd' => null]);

            return $this->respond(true, 'Tanda tangan berhasil dihapus');
        }

        return $this->respond(false, 'Tanda tangan tidak ditemukan');
    }
}
