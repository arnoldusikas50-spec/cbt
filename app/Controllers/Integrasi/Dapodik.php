<?php

namespace App\Controllers\Integrasi;

use App\Controllers\BaseController;
use App\Libraries\DapodikClient;
use App\Models\DashboardModel;
use App\Services\DapodikSyncService;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Controller Dapodik — Pengelolaan Integrasi, Setting API, Direct Sync, & Offline Import Dapodik.
 */
class Dapodik extends BaseController
{
    protected DapodikSyncService $syncService;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->syncService    = new DapodikSyncService();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Halaman Utama Integrasi Dapodik (Dashboard & Tabs)
     */
    public function index()
    {
        $db = \Config\Database::connect();

        $settings = null;
        if ($db->tableExists('dapodik_settings')) {
            $settings = $db->table('dapodik_settings')->get()->getRowArray();
        }

        if (!$settings && $db->tableExists('dapodik_settings')) {
            $db->table('dapodik_settings')->insert([
                'server_url'   => 'http://localhost:5774',
                'npsn'         => '',
                'secret_token' => '',
                'semester_id'  => '20251',
                'auto_sync'    => 0,
                'is_active'    => 0,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            $settings = $db->table('dapodik_settings')->get()->getRowArray();
        }

        // App/School settings for layouts/main.php compatibility
        $appSetting = $this->dashboardModel->getSetting();

        // Stats per perbandingan data terhubung dapo_id
        $stats = [
            'total_guru'  => $db->tableExists('master_guru') ? $db->table('master_guru')->countAllResults() : 0,
            'guru_dapo'   => ($db->tableExists('master_guru') && $db->fieldExists('dapo_id', 'master_guru')) ? $db->table('master_guru')->where('dapo_id IS NOT NULL', null, false)->where('dapo_id !=', '')->countAllResults() : 0,
            'total_siswa' => $db->tableExists('master_siswa') ? $db->table('master_siswa')->countAllResults() : 0,
            'siswa_dapo'  => ($db->tableExists('master_siswa') && $db->fieldExists('dapo_id', 'master_siswa')) ? $db->table('master_siswa')->where('dapo_id IS NOT NULL', null, false)->where('dapo_id !=', '')->countAllResults() : 0,
            'total_kelas' => $db->tableExists('master_kelas') ? $db->table('master_kelas')->countAllResults() : 0,
            'kelas_dapo'  => ($db->tableExists('master_kelas') && $db->fieldExists('dapo_id', 'master_kelas')) ? $db->table('master_kelas')->where('dapo_id IS NOT NULL', null, false)->where('dapo_id !=', '')->countAllResults() : 0,
            'total_mapel' => $db->tableExists('master_mapel') ? $db->table('master_mapel')->countAllResults() : 0,
            'mapel_dapo'  => ($db->tableExists('master_mapel') && $db->fieldExists('dapo_id', 'master_mapel')) ? $db->table('master_mapel')->where('dapo_id IS NOT NULL', null, false)->where('dapo_id !=', '')->countAllResults() : 0,
        ];

        // Logs
        $logs = [];
        if ($db->tableExists('dapodik_log')) {
            $logs = $db->table('dapodik_log')
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get()->getResultArray();
        }

        $data = [
            'title'      => 'Integrasi Data Dapodik',
            'setting'    => $appSetting,
            'settings'   => $settings,
            'stats'      => $stats,
            'logs'       => $logs,
            'tp_active'  => $this->dashboardModel->getTahunActive(),
            'smt_active' => $this->dashboardModel->getSemesterActive(),
        ];

        return view('integrasi/dapodik/index', $data);
    }

    /**
     * Simpan Konfigurasi Server API Dapodik
     */
    public function saveSettings()
    {
        $db = \Config\Database::connect();
        $serverUrl   = rtrim($this->request->getPost('server_url') ?? '', '/');
        $npsn        = trim($this->request->getPost('npsn') ?? '');
        $secretToken = trim($this->request->getPost('secret_token') ?? '');
        $semesterId  = trim($this->request->getPost('semester_id') ?? '') ?: '20251';
        $autoSync    = $this->request->getPost('auto_sync') ? 1 : 0;
        $isActive    = $this->request->getPost('is_active') ? 1 : 0;

        if (empty($serverUrl) || !filter_var($serverUrl, FILTER_VALIDATE_URL)) {
            return redirect()->back()->with('error', 'URL Server Dapodik tidak valid. Harap masukkan URL yang benar (contoh: http://localhost:5774).');
        }

        if (empty($npsn)) {
            return redirect()->back()->with('error', 'NPSN Sekolah tidak boleh kosong.');
        }

        if (empty($secretToken)) {
            return redirect()->back()->with('error', 'Token Rahasia Web Service tidak boleh kosong.');
        }

        if ($db->tableExists('dapodik_settings')) {
            $db->table('dapodik_settings')->where('id >', 0)->update([
                'server_url'   => $serverUrl,
                'npsn'         => $npsn,
                'secret_token' => $secretToken,
                'semester_id'  => $semesterId,
                'auto_sync'    => $autoSync,
                'is_active'    => $isActive,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to(base_url('integrasi/dapodik'))->with('success', 'Konfigurasi Web Service Dapodik berhasil disimpan.');
    }

    /**
     * AJAX — Tes Koneksi HTTP ke API Server Dapodik
     */
    public function testConnection(): ResponseInterface
    {
        $serverUrl   = $this->request->getPost('server_url');
        $npsn        = $this->request->getPost('npsn');
        $secretToken = $this->request->getPost('secret_token');

        $client = new DapodikClient($serverUrl, $npsn, $secretToken);
        $result = $client->testConnection();

        return $this->response->setJSON($result);
    }

    /**
     * AJAX/POST — Jalankan Direct Sync via API Dapodik
     */
    public function syncApi(): ResponseInterface
    {
        $entitas = $this->request->getPost('entitas') ?? 'all';
        $client  = new DapodikClient();
        $userId  = session()->get('id_user') ?? session()->get('user_id');

        try {
            switch ($entitas) {
                case 'gtk':
                    $res = $this->syncService->syncGtkFromApi($client, $userId);
                    break;
                case 'siswa':
                    $res = $this->syncService->syncSiswaFromApi($client, $userId);
                    break;
                case 'rombel':
                    $res = $this->syncService->syncRombelFromApi($client, $userId);
                    break;
                case 'mapel':
                    $res = $this->syncService->syncMapelFromApi($client, $userId);
                    break;
                case 'sekolah':
                    $res = $this->syncService->syncSekolahFromApi($client, $userId);
                    break;
                case 'pembelajaran':
                    $res = $this->syncService->syncPembelajaranFromApi($client, $userId);
                    break;
                case 'ruang':
                    $res = $this->syncService->syncRuangFromApi($client, $userId);
                    break;
                case 'all':
                default:
                    $res = $this->syncService->syncAllFromApi($client, $userId);
                    break;
            }

            return $this->response->setJSON($res);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem saat sinkronisasi: ' . $e->getMessage(),
            ]);
        }
    }



    /**
     * Hapus Riwayat Log Sinkronisasi
     */
    public function clearLogs()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('dapodik_log')) {
            $db->table('dapodik_log')->truncate();
        }
        return redirect()->to(base_url('integrasi/dapodik'))->with('success', 'Riwayat log sinkronisasi Dapodik telah dibersihkan.');
    }
}
