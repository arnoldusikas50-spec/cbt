<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\DashboardModel;
use App\Models\SettingsModel;

/**
 * Dashboard Controller - Migrated from CI3 Dashboard controller
 * Main dashboard for all user roles
 */
class Dashboard extends BaseController
{
    protected AuthModel $authModel;
    protected DashboardModel $dashboardModel;
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->dashboardModel = new DashboardModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Main dashboard index
     */
    public function index()
    {
        // Check if logged in
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->authModel->user($this->getUserId());
        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'user' => $user,
            'judul' => 'Beranda',
            'subjudul' => 'Halaman Utama',
            'setting' => $setting,
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
        ];

        $role = $this->getUserRole();

        // Route based on role
        switch (strtolower($role ?? '')) {
            case 'admin':
                return $this->adminDashboard($data, $setting, $tp, $smt);

            case 'guru':
                return $this->guruDashboard($data, $user, $tp, $smt);

            case 'siswa':
                return $this->siswaDashboard($data, $user, $tp, $smt);

            case 'panitia':
                return $this->panitiaDashboard($data, $user, $tp, $smt);

            default:
                return $this->adminDashboard($data, $setting, $tp, $smt);
        }
    }

    /**
     * Admin dashboard view
     */
    private function adminDashboard(array $data, ?object $setting, ?object $tp, ?object $smt)
    {
        // Dummy data for CI3 view compatibility to prevent undefined variable errors
        $data['token'] = (object) ['token' => 'TOKEN', 'auto' => '0', 'jarak' => '0', 'elapsed' => '00:00:00'];
        $data['ada_ujian'] = [];
        $data['jadwal_kbm'] = [];
        $data['kelases'] = [];
        $data['times'] = [];

        if ($tp && $smt) {
            $data['info_box'] = $this->getAdminBox($setting, $tp->id_tp, $smt->id_smt);
            $data['ujian_box'] = $this->getUjianBox($tp->id_tp, $smt->id_smt);
        } else {
            $data['info_box'] = [];
            $data['ujian_box'] = [];
        }

        return view('dashboard/admin', $data);
    }

    /**
     * Guru dashboard view
     */
    private function guruDashboard(array $data, ?object $user, ?object $tp, ?object $smt)
    {
        if (!$user || !$tp || !$smt) {
            return view('errors/disable_login', $data);
        }

        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        if (!$guru) {
            return view('errors/disable_login', $data);
        }

        // Dummy data for CI3 view compatibility
        $data['token'] = (object) ['token' => 'TOKEN', 'auto' => '0', 'jarak' => '0', 'elapsed' => '00:00:00'];
        $data['ada_ujian'] = [];
        $data['jadwal_kbm'] = [];  // Required for E-Learning widget in admin/guru view
        $data['kelases'] = [];
        $data['times'] = [];

        $data['guru'] = $guru;
        $data['info_box'] = $this->getAdminBox($data['setting'], $tp->id_tp, $smt->id_smt);
        $data['ujian_box'] = $this->getUjianBox($tp->id_tp, $smt->id_smt);

        return view('dashboard/guru', $data);
    }

    /**
     * Siswa dashboard view
     */
    private function siswaDashboard(array $data, ?object $user, ?object $tp, ?object $smt)
    {
        if (!$user || !$tp || !$smt) {
            return view('errors/disable_login', $data);
        }

        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return view('errors/disable_login', $data);
        }

        $data['siswa'] = $siswa;
        $data['menu'] = $this->getSiswaMenu();
        $data['running_text'] = $this->dashboardModel->getRunningText();
        // $data['jadwal'] is used in some versions, adding fallback
        $data['jadwal'] = [];

        return view('dashboard/siswa', $data);
    }

    /**
     * Panitia dashboard view
     */
    private function panitiaDashboard(array $data, ?object $user, ?object $tp, ?object $smt)
    {
        if (!$user || !$tp || !$smt) {
            return view('errors/disable_login', $data);
        }

        $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);

        if (!$panitia) {
            return view('errors/disable_login', $data);
        }

        // Dummy data for CI3 view compatibility
        $data['token'] = (object) ['token' => 'TOKEN', 'auto' => '0', 'jarak' => '0', 'elapsed' => '00:00:00'];
        $data['ada_ujian'] = [];
        $data['jadwal_kbm'] = [];
        $data['kelases'] = [];
        $data['times'] = [];

        $data['panitia'] = $panitia;
        $data['info_box'] = $this->getPanitiaBox($tp->id_tp, $smt->id_smt);
        $data['ujian_box'] = [];

        return view('dashboard/panitia', $data);
    }

    /**
     * Get admin info boxes
     */
    private function getAdminBox(?object $setting, int $tp, int $smt)
    {
        $box = [
            [
                'box' => 'blue',
                'total' => $this->dashboardModel->getTotalSiswa($tp, $smt),
                'title' => 'Siswa',
                'url' => 'datasiswa',
                'icon' => 'users'
            ],
            [
                'box' => 'cyan',
                'total' => $this->dashboardModel->total('master_kelas', "id_tp={$tp} AND id_smt={$smt}"),
                'title' => 'Rombel',
                'url' => 'datakelas',
                'icon' => 'bell'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboardModel->total('master_guru'),
                'title' => 'Guru',
                'url' => 'dataguru',
                'icon' => 'user-circle'
            ],
            [
                'box' => 'fuchsia',
                'total' => $this->dashboardModel->totalWaliKelas($tp, $smt),
                'title' => 'Wali Kelas',
                'url' => 'dataguru',
                'icon' => 'user'
            ],
            [
                'box' => 'success',
                'total' => $this->dashboardModel->total('master_mapel'),
                'title' => 'Mapel',
                'url' => 'datamapel',
                'icon' => 'book'
            ],
        ];
        return json_decode(json_encode($box));
    }

    /**
     * Get ujian info boxes
     */
    private function getUjianBox(int $tp, int $smt)
    {
        $box = [
            [
                'box' => 'indigo',
                'total' => $this->dashboardModel->total('cbt_ruang'),
                'title' => 'Ruang',
                'url' => 'cbtruang',
                'icon' => 'school'
            ],
            [
                'box' => 'maroon',
                'total' => $this->dashboardModel->total('cbt_sesi'),
                'title' => 'Sesi',
                'url' => 'cbtsesi',
                'icon' => 'clock'
            ],
            [
                'box' => 'green',
                'total' => $this->dashboardModel->total('cbt_bank_soal'),
                'title' => 'Bank Soal',
                'url' => 'cbtbanksoal',
                'icon' => 'folder'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboardModel->totalJadwal($tp, $smt),
                'title' => 'Jadwal',
                'url' => 'cbtjadwal',
                'icon' => 'clock'
            ],
        ];
        return json_decode(json_encode($box));
    }

    /**
     * Get panitia info boxes
     */
    private function getPanitiaBox(int $tp, int $smt)
    {
        $db = \Config\Database::connect();

        // Active exams count
        $now = date('Y-m-d H:i:s');
        $activeExams = $db->table('cbt_jadwal')
            ->where('tgl_mulai <=', $now)
            ->where('tgl_selesai >=', $now)
            ->countAllResults();

        $box = [
            [
                'box' => 'blue',
                'total' => $this->dashboardModel->total('master_guru'),
                'title' => 'Guru',
                'url' => 'dataguru',
                'icon' => 'chalkboard-teacher'
            ],
            [
                'box' => 'cyan',
                'total' => $this->dashboardModel->getTotalSiswa($tp, $smt),
                'title' => 'Siswa',
                'url' => 'datasiswa',
                'icon' => 'users'
            ],
            [
                'box' => 'teal',
                'total' => $this->dashboardModel->total('master_panitia'),
                'title' => 'Pengawas',
                'url' => 'datapanitia',
                'icon' => 'user-tie'
            ],
            [
                'box' => 'fuchsia',
                'total' => $activeExams,
                'title' => 'Jadwal Aktif',
                'url' => 'cbtjadwal',
                'icon' => 'clock'
            ],
        ];
        return json_decode(json_encode($box));
    }

    /**
     * Get siswa menu items
     */
    private function getSiswaMenu(): array
    {
        return [
            [
                'title' => 'Jadwal Pelajaran',
                'icon' => 'calendar',
                'link' => 'kbm/jadwal_pelajaran'
            ],
            [
                'title' => 'Materi',
                'icon' => 'book-open',
                'link' => 'kbm/materi'
            ],
            [
                'title' => 'Tugas Siswa',
                'icon' => 'clipboard-list',
                'link' => 'kbm/tugas'
            ],
            [
                'title' => 'Hasil Nilai',
                'icon' => 'poll',
                'link' => 'kbm/hasil'
            ],
            [
                'title' => 'Kehadiran',
                'icon' => 'user-check',
                'link' => 'kbm/absensi'
            ],
            [
                'title' => 'Catatan',
                'icon' => 'sticky-note',
                'link' => 'siswa/catatan'
            ],
        ];
    }

    // AJAX Endpoint Stubs for CI3 View Compatibility
    public function get_status_siswa()
    {
        $data = [
            'ruangs' => [],
            'jadwals_ujian' => [],
            'sesisiswa' => [],
            'cbtsiswa' => [],
            'gurus' => [],
            'pengawas' => [],
            'kelases' => [],
            'panitias' => []
        ];
        return $this->response->setJSON($data);
    }

    public function getlog()
    {
        return $this->response->setJSON(['logs' => []]);
    }

    public function get_realtime_stats()
    {
        return $this->response->setJSON(['info_box' => [], 'ujian_box' => []]);
    }

    public function getpost()
    {
        return $this->response->setJSON([]);
    }
    public function hapuslog()
    {
        return $this->response->setJSON(true);
    }

    // Additional Dummy Endpoints
    public function loadtoken()
    {
        return $this->response->setJSON(json_decode(json_encode(['token' => 'TOKEN', 'auto' => '0', 'now' => date('H:i:s')])));
    }
    public function savebalasan()
    {
        return $this->response->setJSON(['status' => true]);
    }
    public function savekomentar()
    {
        return $this->response->setJSON(['status' => true]);
    }
    public function hapuspost()
    {
        return $this->response->setJSON(true);
    }
    public function hapuskomentar()
    {
        return $this->response->setJSON(true);
    }
    public function hapusbalasan()
    {
        return $this->response->setJSON(true);
    }
}
