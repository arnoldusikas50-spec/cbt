<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\SettingsModel;

/**
 * ProfilSekolah Controller - Migrated from CI3
 * Handles school profile management
 */
class Profilsekolah extends BaseController
{
    protected DashboardModel $dashboardModel;
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Main profile page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        // Ensure only school admins access
        $school_uuid = $this->session->get('school_uuid');
        if (!$school_uuid && !$this->isAdmin()) {
            return $this->showError('Hanya Admin Sekolah yang dapat mengakses halaman ini.', 403);
        }

        $user = $this->getUser();
        $setting = $this->dashboardModel->getSetting();

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Profile Sekolah',
            'subjudul' => 'Pengaturan Data Sekolah',
            'profile' => null,
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('members/sekolah/profil', $data)
            . view('_templates/dashboard/_footer');
    }

    /**
     * Upload file/logo
     */
    public function uploadFile($logo = 'logo')
    {
        $file = $this->request->getFile('logo');

        if (!$file || !$file->isValid()) {
            return $this->respond(false, 'File tidak valid');
        }

        $allowedTypes = ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->respond(false, 'Tipe file tidak diizinkan');
        }

        $school_uuid = $this->session->get('school_uuid') ?? 'default';
        $newName = $logo . '_' . $school_uuid . '.' . $file->getExtension();

        $uploadPath = FCPATH . 'uploads/settings/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if ($file->move($uploadPath, $newName, true)) {
            return $this->respond(true, 'File berhasil diupload', [
                'src' => base_url('uploads/settings/' . $newName),
                'filename' => pathinfo($newName, PATHINFO_FILENAME),
                'relative_path' => 'uploads/settings/' . $newName,
                'type' => $file->getMimeType(),
                'size' => $file->getSize()
            ]);
        }

        return $this->respond(false, 'Gagal mengupload file');
    }

    /**
     * Delete file
     */
    public function deleteFile()
    {
        $src = $this->request->getPost('src');
        $file_name = str_replace(base_url(), '', $src ?? '');
        $file_path = FCPATH . ltrim($file_name, '/');

        if (file_exists($file_path) && unlink($file_path)) {
            return $this->respond(true, 'File berhasil dihapus');
        }

        return $this->respond(false, 'File tidak ditemukan atau gagal menghapus');
    }

    /**
     * Normalize media path
     */
    private function normalizeMediaPath(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        $normalized = str_replace(base_url(), '', $path);
        return ltrim($normalized, '/');
    }

    /**
     * Save school settings
     */
    public function saveSetting()
    {
        $school_uuid = $this->session->get('school_uuid');

        // Allow admin without school_uuid
        if (!$school_uuid && !$this->isAdmin()) {
            return $this->respond(false, 'Akses Sekolah tidak valid');
        }

        $db = \Config\Database::connect();

        // Collect all form data
        $insert = [
            'nama_aplikasi' => $this->request->getPost('nama_aplikasi'),
            'logo_kanan' => $this->normalizeMediaPath($this->request->getPost('logo_kanan')),
            'logo_kiri' => $this->normalizeMediaPath($this->request->getPost('logo_kiri')),
            'stampel' => $this->normalizeMediaPath($this->request->getPost('stampel')),
            'tanda_tangan' => $this->normalizeMediaPath($this->request->getPost('tanda_tangan')),
            'ttdproktor' => $this->normalizeMediaPath($this->request->getPost('ttdproktor')),
            'kodescan' => $this->normalizeMediaPath($this->request->getPost('kodescan')),
            'kopsekolah' => $this->normalizeMediaPath($this->request->getPost('kopsekolah')),
            'banner1' => $this->normalizeMediaPath($this->request->getPost('banner1')),
            'banner2' => $this->normalizeMediaPath($this->request->getPost('banner2')),
            'banner3' => $this->normalizeMediaPath($this->request->getPost('banner3')),
            'web' => $this->request->getPost('web'),
            'fax' => $this->request->getPost('fax'),
            'email' => $this->request->getPost('email'),
            'telp' => preg_replace('/[^0-9]/', '', $this->request->getPost('tlp') ?? ''),
            'kepsek' => $this->request->getPost('kepsek'),
            'nip' => $this->request->getPost('nip'),
            'proktor' => $this->request->getPost('proktor'),
            'nip_proktor' => $this->request->getPost('nip_proktor'),
            'tkn_siswa' => $this->request->getPost('tkn_siswa'),
            'mode_app' => $this->request->getPost('mode_app'),
            'ba_aktif' => $this->request->getPost('ba_aktif'),
            'jenjang' => $this->request->getPost('jenjang'),
            'satuan_pendidikan' => $this->request->getPost('satuan_pendidikan'),
            'sekolah' => $this->request->getPost('nama_sekolah'),
            'nss' => $this->request->getPost('nss'),
            'npsn' => $this->request->getPost('npsn'),
            'alamat' => $this->request->getPost('alamat'),
            'desa' => $this->request->getPost('desa'),
            'kecamatan' => $this->request->getPost('kec'),
            'kota' => $this->request->getPost('kota'),
            'provinsi' => $this->request->getPost('provinsi'),
            'kode_pos' => preg_replace('/[^0-9]/', '', $this->request->getPost('kode_pos') ?? ''),
            'api_key' => $this->request->getPost('api_key'),
        ];

        // Handle ba_waktu datetime
        $ba_waktu_input = $this->request->getPost('ba_waktu');
        if (!empty($ba_waktu_input)) {
            $dt = \DateTime::createFromFormat('Y-m-d\TH:i', $ba_waktu_input);
            if ($dt instanceof \DateTime) {
                $insert['ba_waktu'] = $dt->format('Y-m-d H:i:s');
            }
        }

        $updated = false;

        try {
            // Check if setting exists
            $existing = $db->table('setting')->limit(1)->get()->getRow();

            if ($existing) {
                // Update
                $updated = $db->table('setting')
                    ->where('id_setting', $existing->id_setting ?? 1)
                    ->update($insert);
            } else {
                // Insert
                $insert['id_setting'] = 1;
                if ($school_uuid) {
                    $insert['sync_school_uuid'] = $school_uuid;
                }
                $updated = $db->table('setting')->insert($insert);
            }
        } catch (\Exception $e) {
            return $this->respond(false, 'Error: ' . $e->getMessage());
        }

        return $this->respond((bool) $updated, $updated ? 'Pengaturan berhasil disimpan' : 'Tidak ada perubahan');
    }
}
