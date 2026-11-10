<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\MasterModel;
use App\Models\AuthModel;

class Cbttoken extends BaseController
{
    protected $cbtModel;
    protected $dashboardModel;
    protected $masterModel;
    protected AuthModel $authModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel(); // Assuming MasterModel exists, otherwise use DashboardModel
        $this->authModel = new AuthModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        return redirect()->to('cbtjadwal')->with('info', 'Token sekarang diatur per-jadwal di menu Jadwal Penilaian.');
    }

    public function generate()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);
        }

        try {
            $inputData = $this->request->getPost('data');
            $settings = json_decode($inputData);

            // Generate random token (6 chars uppercase)
            $newToken = strtoupper(substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", 5)), 0, 6));
            $data = [
                'token' => $newToken,
                'auto' => $settings->auto ?? '0',
                'jarak' => $settings->jarak ?? '1',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Add virtual status for frontend response
            $responseData = $data;
            $responseData['status'] = '1';
            $responseData['now'] = $data['updated_at']; // Sync time

            // Simpan token; MySQL sesekali mengembalikan deadlock (1213) saat banyak
            // klien me-generate token bersamaan. Coba ulang singkat dulu sebelum
            // menyerah, supaya tidak ikut terlempar ke log ERROR.
            $saved = false;
            $lastError = null;
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    $saved = (bool) $this->cbtModel->saveToken($data);
                    $lastError = null;
                    break;
                } catch (\Throwable $e) {
                    $lastError = $e;
                    if (stripos($e->getMessage(), 'Deadlock found') === false && stripos($e->getMessage(), 'Lock wait timeout') === false) {
                        break; // bukan masalah locking, biarkan ditangani catch di bawah
                    }
                    usleep(150000 * $attempt); // 150ms, 300ms
                }
            }

            if ($saved === false && $lastError !== null) {
                throw $lastError;
            }

            if ($saved) {
                // Clear token cache so students get the new token immediately
                $cache = \Config\Services::cache();
                $cache->delete('cbt_current_token');

                // FETCH FRESH TOKEN to get the DB timestamp for correct timer sync
                $freshToken = $this->cbtModel->getToken();
                if ($freshToken) {
                    $responseData['updated'] = $freshToken->updated ?? $freshToken->updated_at ?? $responseData['now'];
                } else {
                    $responseData['updated'] = $responseData['now'];
                }

                // Override updated to equal now safely right after generation
                // to prevent any negative JS timeout bugs on immediate generation
                $responseData['updated'] = $responseData['now'];

                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Token berhasil digenerate',
                    'data' => $responseData
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Gagal menyimpan token ke database'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
            ]);
        }
    }

    public function toggle()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Session expired']);
        }

        $status = $this->request->getPost('status');
        $currentToken = $this->cbtModel->getToken();

        if ($currentToken) {
            // Since 'status' column doesn't exist, we use 'updated' to control validity.
            // Enable: updated = NOW
            // Disable: updated = OLD DATE (e.g. 2000-01-01)

            if ($status === '1') {
                // Re-enable token - just update auto flag if needed
                $updateData = ['auto' => $currentToken->auto ?? '0', 'updated_at' => date('Y-m-d H:i:s')];
                $message = 'Token diaktifkan';
            } else {
                // Disable token - set auto to 0
                $updateData = ['auto' => '0', 'updated_at' => date('Y-m-d H:i:s')];
                $message = 'Token dinonaktifkan';
            }

            // saveToken handles the update logic based on existing token
            if ($this->cbtModel->saveToken($updateData)) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => $message
                ]);
            }
        }

        return $this->response->setJSON(['status' => false, 'message' => 'Gagal update status']);
    }

    // Used by Admin Dashboard to load token via AJAX
    public function loadToken()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => '0', 'error' => 'Unauthorized']);
        }

        try {
            $token = $this->cbtModel->getToken();

            // If no token exists, return default structure
            if (!$token) {
                $token = new \stdClass();
                $token->token = '';
                $token->auto = '0';
                $token->jarak = '1';
                $token->elapsed = '00:00:00';
                $token->status = '0';
            } else {
                // Token exists, set status based on token value
                $token->status = !empty($token->token) ? '1' : '0';

                // FIX: Ensure 'updated' property exists for JS timer
                if (!isset($token->updated)) {
                    $token->updated = $token->updated_at ?? date('Y-m-d H:i:s');
                }
            }

            // Add server time for sync
            $token->now = date('Y-m-d H:i:s');

            return $this->response->setJSON($token);
        } catch (\Throwable $e) {
            // Return default token on error instead of 500
            $defaultToken = new \stdClass();
            $defaultToken->token = '';
            $defaultToken->auto = '0';
            $defaultToken->jarak = '1';
            $defaultToken->elapsed = '00:00:00';
            $defaultToken->status = '0';
            $defaultToken->now = date('Y-m-d H:i:s');
            $defaultToken->error = $e->getMessage();

            return $this->response->setJSON($defaultToken);
        }
    }
}
