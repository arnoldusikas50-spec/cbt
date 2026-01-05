<?php

namespace App\Controllers;

use App\Models\SettingsModel;
use App\Models\DashboardModel;

/**
 * Settings Controller - Migrated from CI3
 * Application settings management
 */
class Settings extends BaseController
{
    protected SettingsModel $settingsModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Settings page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        if (!$this->isAdmin()) {
            return redirect()->to('dashboard')->with('error', 'Akses hanya untuk admin');
        }

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Pengaturan',
            'subjudul' => 'Konfigurasi Aplikasi',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp_list' => $this->dashboardModel->getTahun(),
            'smt_list' => $this->dashboardModel->getSemester(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar', $data)
            . view('_templates/dashboard/sidebar', $data)
            . view('settings/index', $data)
            . view('_templates/dashboard/_footer');
    }

    /**
     * Update settings
     */
    public function update()
    {
        if (!$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $type = $this->request->getPost('type');

        switch ($type) {
            case 'sekolah':
                return $this->updateSekolah();
            case 'tp':
                return $this->updateTp();
            case 'smt':
                return $this->updateSmt();
            case 'cbt':
                return $this->updateCbt();
            default:
                return $this->response->setJSON(['status' => false, 'message' => 'Tipe tidak dikenali']);
        }
    }

    /**
     * Update school settings
     */
    private function updateSekolah()
    {
        $data = [
            'nama_sekolah' => $this->request->getPost('nama_sekolah'),
            'npsn' => $this->request->getPost('npsn'),
            'alamat' => $this->request->getPost('alamat'),
            'kota' => $this->request->getPost('kota'),
            'provinsi' => $this->request->getPost('provinsi'),
            'telepon' => $this->request->getPost('telepon'),
            'email' => $this->request->getPost('email'),
            'website' => $this->request->getPost('website'),
            'kepala_sekolah' => $this->request->getPost('kepala_sekolah'),
            'nip_kepsek' => $this->request->getPost('nip_kepsek'),
        ];

        // Handle logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = $logo->getRandomName();
            $logo->move(ROOTPATH . '../uploads/logo', $newName);
            $data['logo'] = $newName;
        }

        $result = $this->settingsModel->updateSetting($data);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Pengaturan sekolah berhasil disimpan' : 'Gagal menyimpan pengaturan'
        ]);
    }

    /**
     * Update tahun pelajaran
     */
    private function updateTp()
    {
        $activeId = $this->request->getPost('tp_aktif');
        $db = \Config\Database::connect();

        // Set all to inactive
        $db->table('master_tp')->update(['active' => 0]);

        // Set selected as active
        $db->table('master_tp')
            ->where('id_tp', $activeId)
            ->update(['active' => 1]);

        // Update setting table
        $this->settingsModel->updateSetting(['tp_aktif' => $activeId]);

        return $this->response->setJSON(['status' => true, 'message' => 'Tahun pelajaran berhasil diubah']);
    }

    /**
     * Update semester
     */
    private function updateSmt()
    {
        $activeId = $this->request->getPost('smt_aktif');
        $db = \Config\Database::connect();

        // Set all to inactive
        $db->table('master_smt')->update(['active' => 0]);

        // Set selected as active
        $db->table('master_smt')
            ->where('id_smt', $activeId)
            ->update(['active' => 1]);

        // Update setting table
        $this->settingsModel->updateSetting(['smt_aktif' => $activeId]);

        return $this->response->setJSON(['status' => true, 'message' => 'Semester berhasil diubah']);
    }

    /**
     * Update CBT settings
     */
    private function updateCbt()
    {
        $data = [
            'durasi_default' => $this->request->getPost('durasi_default'),
            'acak_soal' => $this->request->getPost('acak_soal') ? '1' : '0',
            'acak_jawaban' => $this->request->getPost('acak_jawaban') ? '1' : '0',
            'tampil_nilai' => $this->request->getPost('tampil_nilai') ? '1' : '0',
            'tampil_pembahasan' => $this->request->getPost('tampil_pembahasan') ? '1' : '0',
        ];

        $result = $this->settingsModel->updateSetting($data);

        return $this->response->setJSON([
            'status' => $result,
            'message' => $result ? 'Pengaturan CBT berhasil disimpan' : 'Gagal menyimpan pengaturan'
        ]);
    }
}
