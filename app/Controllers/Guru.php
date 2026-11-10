<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\MasterModel; // Added for Data Manipulation
use App\Models\LogModel; // For Logging

/**
 * Guru Controller - Migrated from CI3
 * Teacher profile and functions
 */
class Guru extends BaseController
{
    protected DashboardModel $dashboardModel;
    protected MasterModel $masterModel;
    protected LogModel $logModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel();
        $this->logModel = new LogModel();
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
        $user = $this->getUser();

        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        
        if (!$guru) {
            return redirect()->to('dashboard')->with('error', 'Data guru tidak ditemukan');
        }
        
        // Enrich guru data like Dataguru::edit
        $guru = $this->masterModel->getGuruById($guru->id_guru, $tp->id_tp, $smt->id_smt);
        
        // Decode dokumen JSON
        if (!empty($guru->dokumen) && is_string($guru->dokumen)) {
            $guru->dokumen = json_decode($guru->dokumen, true);
        }

        $data = [
            'judul' => 'Profil Guru',
            'subjudul' => 'Data Profil Saya',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'guru' => $guru,
            'user' => $user,
            'id_active' => $guru->id_guru // For view compatibility
        ];

        // Prepare Inputs like CI3 / Dataguru::edit
        $data['input_profile'] = [
            (object) ['label' => 'Nama Lengkap', 'name' => 'nama_guru', 'value' => $guru->nama_guru, 'icon' => 'bi bi-person', 'type' => 'text'],
            (object) ['label' => 'Email', 'name' => 'email', 'value' => $guru->email, 'icon' => 'bi bi-envelope', 'type' => 'text'],
            (object) ['label' => 'NIP / NUPTK', 'name' => 'nip', 'value' => $guru->nip, 'icon' => 'bi bi-person-badge', 'type' => 'text'],
            (object) ['label' => 'Jenis Kelamin', 'name' => 'jenis_kelamin', 'value' => $guru->jenis_kelamin, 'icon' => 'bi bi-gender-ambiguous', 'type' => 'text'],
            (object) ['label' => 'No. Handphone', 'name' => 'no_hp', 'value' => $guru->no_hp, 'icon' => 'bi bi-telephone', 'type' => 'number'],
            (object) ['label' => 'Agama', 'name' => 'agama', 'value' => $guru->agama, 'icon' => 'bi bi-person', 'type' => 'text'],
        ];

        $data['input_alamat'] = [
            (object) ['label' => 'NIK', 'name' => 'no_ktp', 'value' => $guru->no_ktp, 'icon' => 'bi bi-person-badge', 'type' => 'number'],
            (object) ['label' => 'Tempat Lahir', 'name' => 'tempat_lahir', 'value' => $guru->tempat_lahir, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Tgl. Lahir', 'name' => 'tgl_lahir', 'value' => $guru->tgl_lahir, 'icon' => 'bi bi-calendar-date', 'type' => 'text'],
            (object) ['label' => 'Alamat', 'name' => 'alamat_jalan', 'value' => $guru->alamat_jalan, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Kecamatan', 'name' => 'kecamatan', 'value' => $guru->kecamatan, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Kota/Kab.', 'name' => 'kabupaten', 'value' => $guru->kabupaten, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Provinsi', 'name' => 'provinsi', 'value' => $guru->provinsi, 'icon' => 'bi bi-geo-alt', 'type' => 'text'],
            (object) ['label' => 'Kode Pos', 'name' => 'kode_pos', 'value' => $guru->kode_pos, 'icon' => 'bi bi-envelope', 'type' => 'number'],
        ];

        $data['input_dokumen'] = [
            (object) ['label' => 'KTP', 'id' => 'ktp_id', 'name' => 'ktp_id', 'type' => 'file'],
            (object) ['label' => 'KK', 'id' => 'kk_id', 'name' => 'kk_id', 'type' => 'file'],
            (object) ['label' => 'Sertifikat', 'id' => 'sertifikat', 'name' => 'serti[]', 'type' => 'file'],
        ];

        // AI Settings data
        $data['api_key'] = $guru->api_key ?? '';

        return view('members/guru/profil', $data);
    }
    
    /**
     * Save/Update Profile
     */
    public function updateProfil()
    {
        if (!$this->inGroup('guru')) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }
        
        $user = $this->getUser();
        $existingGuru = $this->dashboardModel->getDataGuruByUserId($user->id, null, null);
        
        if (!$existingGuru) {
             return $this->response->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }
        
        $id_guru = $existingGuru->id_guru; // Force use ID from session context
        
        $data = $this->request->getPost();
        
        // Remove prohibited fields
        unset($data['id_guru'], $data['method'], $data[csrf_token()]);
        
        // Handle Document Uploads
        $files = $this->request->getFiles();
        if ($files) {
            $currentDokumen = json_decode($existingGuru->dokumen ?? '{}', true);
            if (!is_array($currentDokumen)) $currentDokumen = [];
            
            // Define upload path
            $uploadPath = FCPATH . 'uploads/users/' . $user->id . '/documents/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            foreach ($files as $key => $fileInput) {
                if (is_array($fileInput)) {
                    // Multiple files (e.g., serti[])
                    foreach ($fileInput as $idx => $file) {
                        if ($file->isValid() && !$file->hasMoved()) {
                            $newName = $file->getRandomName();
                            $file->move($uploadPath, $newName);
                            $relativePath = 'uploads/users/' . $user->id . '/documents/' . $newName;
                            
                            // Initialize array if not exists
                            if (!isset($currentDokumen[$key]) || !is_array($currentDokumen[$key])) {
                                $currentDokumen[$key] = [];
                            }
                            $currentDokumen[$key][] = $relativePath;
                        }
                    }
                } else {
                    // Single file
                    if ($fileInput->isValid() && !$fileInput->hasMoved()) {
                        $newName = $fileInput->getRandomName();
                        $fileInput->move($uploadPath, $newName);
                        $relativePath = 'uploads/users/' . $user->id . '/documents/' . $newName;
                        
                        $currentDokumen[$key] = $relativePath;
                    }
                }
            }
            
            // Only update dokumen field if files were processed
            // But we must merge with existing data
            $data['dokumen'] = json_encode($currentDokumen);
        }

        // Update Data Guru
        $success = $this->masterModel->updateData('master_guru', $data, 'id_guru', $id_guru);
        
        if ($success) {
            // Update User Table (Email, Name)
            $userUpdate = [];
            if (isset($data['email'])) $userUpdate['email'] = $data['email'];
            if (isset($data['nama_guru'])) {
                $nama = explode(' ', $data['nama_guru']);
                $userUpdate['first_name'] = $nama[0];
                $userUpdate['last_name'] = count($nama) > 1 ? end($nama) : '';
            }
            
            if (!empty($userUpdate)) {
                $db = \Config\Database::connect();
                $db->table('users')->where('id', $user->id)->update($userUpdate);
            }
            
            $this->logModel->saveLog(2, 'Guru mengupdate profilnya sendiri. ID: ' . $id_guru);
            return $this->response->setJSON(['status' => true, 'message' => 'Profil berhasil diupdate']);
        }
        
        return $this->response->setJSON(['status' => false, 'message' => 'Gagal update profil']);
    }

    /**
     * Update Login Credentials (Username/Password)
     */
    public function updateLogin()
    {
        if (!$this->inGroup('guru') || !$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }
        
        $user = $this->getUser();
        $username = $this->request->getPost('username');
        $newPass = $this->request->getPost('new');
        $confirmPass = $this->request->getPost('new_confirm');
        
        $updateData = [];
        $rules = [
            'username' => 'required|min_length[3]|is_unique[users.username,id,' . $user->id . ']'
        ];
        
        $updateData['username'] = $username;
        
        if (!empty($newPass)) {
            $rules['new'] = 'required|min_length[6]';
            $rules['new_confirm'] = 'required|matches[new]';
            // Password hashing is handled by Myth/Auth entities usually, assumes manual update here matches auth config
            // Better to use Auth Model/Entity if possible. 
            // For now, assuming standard Myth/Auth PasswordHasher
             $updateData['password_hash'] = \Myth\Auth\Password::hash($newPass);
        }
        
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['status' => false, 'errors' => $this->validator->getErrors()]);
        }
        
        $db = \Config\Database::connect();
        $db->table('users')->where('id', $user->id)->update($updateData);
        
        // Update guru table username also if redundant column exists? 
        // Usually relational, but let's check master_guru
        // master_guru doesn't have username/password column usually as it links to users.
        
        $this->logModel->saveLog(2, 'Guru update login info. UserID: ' . $user->id);
        
        return $this->response->setJSON(['status' => true, 'text' => 'Login info berhasil diupdate']);
    }

    /**
     * Upload Foto Profil
     */
    public function uploadFoto()
    {
        if (!$this->inGroup('guru')) return $this->response->setJSON(['status' => false]);

        $user = $this->getUser();
        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, null, null);
        if (!$guru) return $this->response->setJSON(['status' => false]);
        $id_guru = $guru->id_guru;

        $validationRule = [
            'foto' => [
                'label' => 'Image File',
                'rules' => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/gif,image/png]|max_size[foto,2048]',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->response->setJSON(['status' => false, 'error' => $this->validator->getErrors()]);
        }

        $img = $this->request->getFile('foto');
        if (!$img->hasMoved()) {
            // Delete old photo
            if ($guru->foto && file_exists(FCPATH . $guru->foto)) {
                if(strpos($guru->foto, 'default') === false) @unlink(FCPATH . $guru->foto);
            }

            $newName = $img->getRandomName();
            $path = 'uploads/users/' . $user->id . '/profile/';
            
            if (!is_dir(FCPATH . $path)) {
                mkdir(FCPATH . $path, 0777, true);
            }
            
            $img->move(FCPATH . $path, $newName);
            
            $dbPath = $path . $newName;
            $this->masterModel->updateData('master_guru', ['foto' => $dbPath], 'id_guru', $id_guru);

            // SYNC TO USERS TABLE (For Attendance App Consistency)
            $db = \Config\Database::connect();
            if ($db->fieldExists('foto', 'users')) {
                $db->table('users')->where('id', $user->id)->update(['foto' => $dbPath]);
            }

            return $this->response->setJSON(['status' => true, 'src' => base_url($dbPath)]);
        }

        return $this->response->setJSON(['status' => false]);
    }

    /**
     * Delete Foto Profil
     */
    public function deleteFoto()
    {
        if (!$this->inGroup('guru')) return $this->response->setJSON(['status' => false]);

        $user = $this->getUser();
        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, null, null);
        if (!$guru) return $this->response->setJSON(['status' => false]);

        if ($guru->foto && file_exists(FCPATH . $guru->foto)) {
             @unlink(FCPATH . $guru->foto);
        }
        
        $this->masterModel->updateData('master_guru', ['foto' => null], 'id_guru', $guru->id_guru);
        
        // SYNC TO USERS TABLE
        $db = \Config\Database::connect();
        if ($db->fieldExists('foto', 'users')) {
            $db->table('users')->where('id', $user->id)->update(['foto' => null]);
        }
        
        return $this->response->setJSON(['status' => true]);
    }

    /**
     * Save Signature
     */
    public function saveSignature()
    {
        if (!$this->inGroup('guru')) return $this->response->setJSON(['status' => false]);

        $user = $this->getUser();
        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, null, null);
        if (!$guru) return $this->response->setJSON(['status' => false]);

        $image = $this->request->getPost('image');
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $data = base64_decode($image);

        $fileName = 'ttd_' . $guru->nip . '_' . time() . '.png';
        $path = 'uploads/ttd/';
        
        if (!is_dir(FCPATH . $path)) {
            mkdir(FCPATH . $path, 0777, true);
        }

        // Delete old ttd
        if ($guru->ttd && file_exists(FCPATH . $guru->ttd)) {
            @unlink(FCPATH . $guru->ttd);
        }

        if (file_put_contents(FCPATH . $path . $fileName, $data)) {
            $dbPath = $path . $fileName;
            $this->masterModel->updateData('master_guru', ['ttd' => $dbPath], 'id_guru', $guru->id_guru);
            return $this->response->setJSON(['status' => true, 'src' => base_url($dbPath), 'message' => 'Tanda tangan berhasil disimpan']);
        }
        
        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan file']);
    }

    /**
     * Delete Signature
     */
    public function deleteTtd()
    {
        if (!$this->inGroup('guru')) return $this->response->setJSON(['status' => false]);

        $user = $this->getUser();
        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, null, null);
        
        if ($guru->ttd && file_exists(FCPATH . $guru->ttd)) {
            @unlink(FCPATH . $guru->ttd);
        }
        
        $this->masterModel->updateData('master_guru', ['ttd' => null], 'id_guru', $guru->id_guru);
        return $this->response->setJSON(['status' => true]);
    }
    
    /**
     * Delete Document
     */
    public function deleteDokumen()
    {
        if (!$this->inGroup('guru')) return $this->response->setJSON(['status' => false]);
        
        $src = $this->request->getGet('src'); // Can be passed via GET or POST
        if (!$src) $src = $this->request->getPost('src');
        
        if(!$src) return $this->response->setJSON(['status' => false]);

        $user = $this->getUser();
        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, null, null);
        
        // Enrich to get dokumen field
        $guruFull = $this->masterModel->getGuruById($guru->id_guru);
        
        $dokumen = json_decode($guruFull->dokumen ?? '{}', true);
        if (!$dokumen) $dokumen = [];
        
        // Remove file
        if (file_exists(FCPATH . $src)) {
            @unlink(FCPATH . $src);
        }
        
        // Update DB JSON
        $newDokumen = [];
        foreach($dokumen as $key => $val) {
             if (is_array($val)) {
                 $newVals = [];
                 foreach($val as $v) {
                     if ($v !== $src) $newVals[] = $v;
                 }
                 if (!empty($newVals)) $newDokumen[$key] = $newVals;
             } else {
                 if ($val !== $src) $newDokumen[$key] = $val;
             }
        }
        
        $this->masterModel->updateData('master_guru', ['dokumen' => json_encode($newDokumen)], 'id_guru', $guru->id_guru);
        
        return $this->response->setJSON(['status' => true, 'dokumen' => $newDokumen]);
    }

    /**
     * Save Google API Key for AI features
     */
    public function saveApiKey()
    {
        if (!$this->inGroup('guru') || !$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $user = $this->getUser();
        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, null, null);

        if (!$guru) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data guru tidak ditemukan']);
        }

        $apiKey = trim($this->request->getPost('api_key') ?? '');

        // Auto-create api_key column if not exists
        $db = \Config\Database::connect();
        if (!$db->fieldExists('api_key', 'master_guru')) {
            $db->query("ALTER TABLE master_guru ADD COLUMN api_key TEXT DEFAULT NULL");
        }

        // Validate API key format (optional, basic check)
        if (!empty($apiKey) && strlen($apiKey) < 10) {
            return $this->response->setJSON(['status' => false, 'message' => 'Format API Key tidak valid. Minimal 10 karakter.']);
        }

        // Test the API key if provided
        if (!empty($apiKey)) {
            $testResult = $this->testApiKey($apiKey);
            if (!$testResult['valid']) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'API Key tidak valid: ' . $testResult['error']
                ]);
            }
        }

        // Save to database
        $db->table('master_guru')
           ->where('id_guru', $guru->id_guru)
           ->update(['api_key' => $apiKey ?: null]);

        $this->logModel->saveLog(2, 'Guru update API Key. ID: ' . $guru->id_guru);

        return $this->response->setJSON([
            'status' => true,
            'message' => empty($apiKey) ? 'API Key berhasil dihapus. Akan menggunakan API Key sistem.' : 'API Key berhasil disimpan dan terverifikasi!'
        ]);
    }

    /**
     * Test if a Google Gemini API key is valid
     */
    private function testApiKey(string $apiKey): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['valid' => false, 'error' => 'Tidak dapat menghubungi server Google: ' . $error];
        }

        if ($httpCode === 200) {
            return ['valid' => true, 'error' => ''];
        }

        $data = json_decode($response, true);
        $errMsg = $data['error']['message'] ?? 'HTTP ' . $httpCode;

        return ['valid' => false, 'error' => $errMsg];
    }
}
