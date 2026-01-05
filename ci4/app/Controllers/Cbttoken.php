<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\DashboardModel;

/**
 * Cbttoken Controller - Migrated from CI3
 * Token management for CBT
 */
class Cbttoken extends BaseController
{
    protected CbtModel $cbtModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Index page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Token Ujian',
            'subjudul' => 'Kelola Token Masuk CBT',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'token' => $this->cbtModel->getToken(),
            'user' => (object) ['first_name' => session()->get('first_name')]
        ];

        return view('_templates/dashboard/_header', $data)
            . view('_templates/dashboard/navbar')
            . view('_templates/dashboard/sidebar')
            . view('cbt/token/data')
            . view('_templates/dashboard/_footer');
    }

    /**
     * Generate new token
     */
    public function generate()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            // Generate random token
            $token = strtoupper(substr(md5(time()), 0, 6));

            $data = [
                'token' => $token,
                'created_by' => session()->get('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
                'status' => '1'
            ];

            $result = $this->cbtModel->saveToken($data);

            return $result
                ? $this->respond(true, 'Token berhasil dibuat', ['token' => $token])
                : $this->respond(false, 'Gagal membuat token');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Toggle token status
     */
    public function toggle()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $status = $this->request->getPost('status');

            $result = $this->cbtModel->saveToken(['status' => $status]);

            return $result
                ? $this->respond(true, 'Status token berhasil diubah')
                : $this->respond(false, 'Gagal mengubah status');
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Get current token via AJAX
     */
    public function getToken()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $token = $this->cbtModel->getToken();

            return $this->respond(true, '', ['token' => $token]);
        } catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }


}
