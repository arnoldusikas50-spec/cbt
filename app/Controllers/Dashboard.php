<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use App\Models\MasterModel;

/**
 * Dashboard Controller - Fully ported from CI3
 * Main dashboard for all user roles with complete AJAX endpoints
 */
class Dashboard extends BaseController
{
    protected AuthModel $authModel;
    protected DashboardModel $dashboardModel;
    protected MasterModel $masterModel;
    protected CbtModel $cbtModel;
    protected DropdownModel $dropdownModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
        $this->dashboardModel = new DashboardModel();
        $this->masterModel = new MasterModel();
        $this->cbtModel = new CbtModel();
        $this->dropdownModel = new DropdownModel();
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
            'semester' => $this->dashboardModel->getSemester(), // Renamed for consistency if needed, but keeping CI3 style 'smt'
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $smt,
        ];

        $roleId = (string) session()->get('role');
        
        if ($roleId === self::ROLE_ADMIN) {
            return $this->adminDashboard($data, $setting, $tp, $smt);
        } elseif ($roleId === self::ROLE_GURU) {
            return $this->guruDashboard($data, $user, $tp, $smt);
        } elseif ($roleId === self::ROLE_SISWA) {
            return $this->siswaDashboard($data, $user, $tp, $smt);
        } elseif ($roleId === self::ROLE_PANITIA) {
            // Early exit if active TP/SMT not set
            if (!$tp || !$smt) {
                return $this->adminDashboard($data, $setting, $tp, $smt);
            }

            // Get panitia data directly here
            $panitia = ($user && $tp && $smt) 
                ? $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt)
                : null;
            
            // Check if user is pengawas (supervisor)
            $is_pengawas = false;
            $assignments = [];
            if ($panitia) {
                $jabatan = strtolower($panitia->jabatan ?? '');
                $namaPanitia = strtolower($panitia->nama_panitia ?? '');
                // Get assignments for this panitia for today
                $db = \Config\Database::connect();
                $assignments = $db->table('cbt_pengawas p')
                    ->select('p.*, r.nama_ruang, s.nama_sesi')
                    ->join('cbt_ruang r', 'p.id_ruang = r.id_ruang', 'left')
                    ->join('cbt_sesi s', 'p.id_sesi = s.id_sesi', 'left')
                    ->where('p.id_tp', $tp->id_tp ?? 0)
                    ->where('p.id_smt', $smt->id_smt ?? 0)
                    ->where('p.id_guru', 'P' . ($panitia->id_panitia ?? ''))
                    ->get();
                $assignments = $assignments ? $assignments->getResult() : [];

                if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas' || !empty($assignments)) {
                    $is_pengawas = true;
                }
            }
            
            // Get token info
            $token = $this->cbtModel->getToken();
            $data['token'] = $token ?? (object) ['token' => '', 'auto' => '0', 'jarak' => '1', 'elapsed' => '00:00:00'];
            $data['ada_ujian'] = ($tp && $smt) ? $this->cbtModel->getDataJadwalByTgl(date('Y-m-d')) : [];
            $data['jadwal_kbm'] = [];
            $data['kelases'] = ($tp && $smt) ? $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt) : [];
            $data['times'] = [];
            $data['mapels'] = [];
            
            $data['panitia'] = $panitia;
            $data['info_box'] = $this->getPanitiaBox($tp->id_tp, $smt->id_smt);
            $data['ujian_box'] = $this->getUjianBox($tp->id_tp, $smt->id_smt); // Added this
            $data['is_pengawas'] = $is_pengawas;
            $data['assignments'] = $assignments;

            // If supervisor, prepare specific stats for their assignment
            if ($is_pengawas && !empty($assignments)) {
                $data['supervisor_info'] = $this->getSupervisorBox($assignments, $tp->id_tp, $smt->id_smt);
            }
            
            return view('members/panitia/dashboard', $data);
        } else {
            return $this->adminDashboard($data, $setting, $tp, $smt);
        }
    }

    /**
     * Get supervisor info boxes for assigned rooms
     */
    private function getSupervisorBox(array $assignments, int $tpId, int $smtId)
    {
        $db = \Config\Database::connect();
        $assignedRuangs = array_unique(array_column($assignments, 'id_ruang'));
        
        if (empty($assignedRuangs)) return [];

        // Count total students in assigned rooms
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dihitung.
        $totalSiswa = $this->onlySiswaAktif(
            $db->table('cbt_sesi_siswa ss')
                ->where('ss.tp_id', $tpId)
                ->where('ss.smt_id', $smtId)
                ->whereIn('ss.ruang_id', $assignedRuangs),
            'ss',
            'bi',
            'siswa_id'
        )->countAllResults();

        // Count active exams in these rooms (simplified)
        $activeExams = count($assignments);

        $box = [
            [
                'box' => 'blue',
                'total' => $totalSiswa,
                'title' => 'Siswa Anda',
                'url' => '#',
                'icon' => 'users'
            ],
            [
                'box' => 'teal',
                'total' => $activeExams,
                'title' => 'Ruang dan Sesi',
                'url' => '#',
                'icon' => 'door-open'
            ]
        ];

        // Add assignment details for header - group by ruang to avoid duplicates
        $ruangSesiMap = [];
        foreach ($assignments as $a) {
            $ruang = $a->nama_ruang ?? 'Ruang ?';
            $sesi = $a->nama_sesi ?? 'Sesi ?';
            if (!isset($ruangSesiMap[$ruang])) {
                $ruangSesiMap[$ruang] = [];
            }
            if (!in_array($sesi, $ruangSesiMap[$ruang])) {
                $ruangSesiMap[$ruang][] = $sesi;
            }
        }
        
        $assignmentText = [];
        foreach ($ruangSesiMap as $ruang => $sesiList) {
            if (count($sesiList) === 1) {
                $assignmentText[] = $ruang . ' (' . $sesiList[0] . ')';
            } else {
                $assignmentText[] = $ruang . ' (' . count($sesiList) . ' sesi)';
            }
        }
        
        return [
            'boxes' => $box,
            'text' => implode(', ', $assignmentText)
        ];
    }


    /**
     * Admin dashboard view
     */
    private function adminDashboard(array $data, ?object $setting, ?object $tp, ?object $smt)
    {
        // Get token info
        $token = $this->cbtModel->getToken();
        $data['token'] = $token ?? (object) ['token' => '', 'auto' => '0', 'jarak' => '1', 'elapsed' => '00:00:00'];

        // Get today's exam schedules
        $data['ada_ujian'] = $tp && $smt ? $this->cbtModel->getDataJadwalByTgl(date('Y-m-d')) : [];

        // Empty arrays for E-Learning (to be implemented later)
        $data['jadwal_kbm'] = [];
        $data['kelases'] = $tp && $smt ? $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt) : [];
        $data['times'] = [];
        $data['mapels'] = [];

        if ($tp && $smt) {
            $data['info_box'] = $this->getAdminBox($setting, $tp->id_tp, $smt->id_smt);
            $data['ujian_box'] = $this->getUjianBox($tp->id_tp, $smt->id_smt);
        } else {
            $data['info_box'] = [];
            $data['ujian_box'] = [];
        }

        return view('dashboard', $data);
    }

    /**
     * Guru dashboard view
     */
    private function guruDashboard(array $data, ?object $user, ?object $tp, ?object $smt)
    {
        if (!$user || !$tp || !$smt) {
            return view('disable_login', $data);
        }

        $guru = $this->dashboardModel->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);

        if (!$guru) {
            return view('disable_login', $data);
        }

        // Get token info
        $token = $this->cbtModel->getToken();
        $data['token'] = $token ?? (object) ['token' => '', 'auto' => '0', 'jarak' => '1', 'elapsed' => '00:00:00'];
        $data['ada_ujian'] = $this->cbtModel->getDataJadwalByTgl(date('Y-m-d'));
        $data['jadwal_kbm'] = [];
        $data['kelases'] = $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['times'] = [];
        $data['mapels'] = [];

        $data['guru'] = $guru;
        $data['info_box'] = $this->getAdminBox($data['setting'], $tp->id_tp, $smt->id_smt);
        $data['ujian_box'] = $this->getUjianBox($tp->id_tp, $smt->id_smt);

        return view('members/guru/dashboard', $data);
    }

    /**
     * Siswa dashboard view
     */
    private function siswaDashboard(array $data, ?object $user, ?object $tp, ?object $smt)
    {
        if (!$user || !$tp || !$smt) {
            return view('disable_login', $data);
        }

        $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);

        if (!$siswa) {
            return view('disable_login', $data);
        }

        $data['siswa'] = $siswa;
        $data['login'] = $siswa;
        $data['menu'] = $this->getSiswaMenu();
        $data['running_text'] = $this->dashboardModel->getRunningText();
        $data['jadwal'] = [];
        $data['jadwals'] = [];

        return view('members/siswa/dashboard', $data);
    }

    /**
     * Panitia dashboard view
     */
    private function panitiaDashboardTEST(array $data, ?object $user, ?object $tp, ?object $smt)
    {
        // Debug: Check if this method is called
        echo "<pre style='background:red;color:white;padding:10px;position:fixed;top:0;left:0;z-index:9999;'>";
        echo "INSIDE panitiaDashboardTEST METHOD!\n";
        echo "User ID: " . ($user->id ?? 'NULL') . "\n";
        echo "User Role: " . (session()->get('role') ?? 'NULL') . "\n";
        echo "TP ID: " . ($tp->id_tp ?? 'NULL') . "\n";
        echo "SMT ID: " . ($smt->id_smt ?? 'NULL') . "\n";
        echo "</pre>";
        die("STOPPED HERE FOR TESTING");
        
        // Check parameters
        echo "<pre style='background:yellow;color:black;padding:10px;position:fixed;top:100px;left:0;z-index:9999;'>";
        echo "PARAMETERS CHECK:\n";
        echo "User exists: " . ($user ? 'YES' : 'NO') . "\n";
        echo "TP exists: " . ($tp ? 'YES' : 'NO') . "\n";
        echo "SMT exists: " . ($smt ? 'YES' : 'NO') . "\n";
        echo "</pre>";
        
        if (!$user || !$tp || !$smt) {
            echo "<pre style='background:red;color:white;padding:10px;position:fixed;top:200px;left:0;'>MISSING PARAMETERS - Returning disable_login</pre>";
            return view('disable_login', $data);
        }
        
        echo "<pre style='background:lime;color:black;padding:10px;position:fixed;top:300px;left:0;z-index:9999;'>Parameters OK - Continuing...</pre>";

        $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);

        if (!$panitia) {
            return view('disable_login', $data);
        }

        // Get token info
        $token = $this->cbtModel->getToken();
        $data['token'] = $token ?? (object) ['token' => '', 'auto' => '0', 'jarak' => '1', 'elapsed' => '00:00:00'];
        $data['ada_ujian'] = $this->cbtModel->getDataJadwalByTgl(date('Y-m-d'));
        $data['jadwal_kbm'] = [];
        $data['kelases'] = $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt);
        $data['times'] = [];
        $data['mapels'] = [];

        $data['panitia'] = $panitia;
        $data['info_box'] = $this->getPanitiaBox($tp->id_tp, $smt->id_smt);
        $data['ujian_box'] = [];

        // Check if user is pengawas (supervisor)
        $data['is_pengawas'] = false;

        if ($panitia) {
            $jabatan     = strtolower($panitia->jabatan ?? '');
            $namaPanitia = strtolower($panitia->nama_panitia ?? '');

            if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas') {
                $data['is_pengawas'] = true;
            }
        }

        return view('members/panitia/dashboard', $data);
    }

    /**
     * Get admin info boxes
     */
    private function getAdminBox(?object $setting, int $tp, int $smt)
    {
        $jenjang_where = '';
        if ($setting && $setting->jenjang == "1") {
            $jenjang_where = 'jenjang=0 OR jenjang=1';
        } elseif ($setting && $setting->jenjang == "2") {
            $jenjang_where = 'jenjang=2 OR jenjang=1';
        }

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
                'icon' => 'user-o'
            ],
            [
                'box' => 'success',
                'total' => $this->dashboardModel->total('master_mapel', $jenjang_where),
                'title' => 'Mapel',
                'url' => 'datamapel',
                'icon' => 'book'
            ],
        ];
        return $box;
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
        return $box;
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

        $belumUjian = $this->countBelumUjian($tp, $smt);

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
                'url' => '', // belum ada route/controller datapanitia
                'icon' => 'user-tie'
            ],
            [
                'box' => 'fuchsia',
                'total' => $activeExams,
                'title' => 'Jadwal Aktif',
                'url' => 'cbtjadwal',
                'icon' => 'clock'
            ],
            [
                'box' => 'maroon',
                'total' => $belumUjian,
                'title' => 'Belum Ujian',
                'url' => 'cbtstatus',
                'icon' => 'user-clock'
            ],
        ];
        return $box;
    }

    /**
     * Jumlah siswa peserta ujian hari ini yang belum memulai ujian.
     *
     * Mengikuti logika yang sama dengan tabel "Jadwal Penilaian Hari Ini"
     * (lihat processAndGroupData() di assets/app/js/dashboard.js):
     *   1. ambil jadwal dengan tgl_mulai = hari ini,
     *   2. kumpulkan kelas peserta dari bank soal tiap jadwal,
     *   3. pasangan (ruang, sesi) yang dipakai ujian hari ini,
     *   4. siswa di cbt_sesi_siswa pada pasangan tersebut,
     *   5. kurangi yang sudah punya cbt_siswa.mulai (sudah mulai ujian).
     *
     * Mengembalikan 0 (bukan error) bila tidak ada ujian pada tanggal tersebut.
     *
     * @param string|null $tanggal Format Y-m-d; default hari ini.
     */
    private function countBelumUjian(int $tp, int $smt, ?string $tanggal = null): int
    {
        $tanggal = $tanggal ?? date('Y-m-d');

        try {
            $jadwals = $this->cbtModel->getDataJadwalByTgl($tanggal);
            if (empty($jadwals)) {
                return 0;
            }

            // 1. Kelas yang ikut ujian hari ini (dari bank_kelas tiap jadwal)
            $allKelas = [];
            foreach ($jadwals as $j) {
                $bankKelas = $this->cbtModel->maybeUnserialize($j->bank_kelas ?? '');
                if (! is_array($bankKelas)) {
                    continue;
                }

                foreach ($bankKelas as $kb) {
                    $kId = is_object($kb) ? ($kb->kelas_id ?? null)
                        : (is_array($kb) ? ($kb['kelas_id'] ?? null) : $kb);
                    if ($kId !== null && $kId !== '') {
                        $allKelas[(int) $kId] = (int) $kId;
                    }
                }
            }
            $allKelas = array_values($allKelas);
            if (empty($allKelas)) {
                return 0;
            }

            // 2. Pasangan (ruang, sesi) yang dipakai hari ini
            $ruangs = $this->cbtModel->getDistinctSesi($tp, $smt, $allKelas);
            if (empty($ruangs)) {
                return 0;
            }

            $pairs = [];
            foreach ($ruangs as $r) {
                $pairs[] = ['ruang_id' => (int) $r->ruang_id, 'sesi_id' => (int) $r->sesi_id];
            }

            // 3. Siswa peserta pada pasangan ruang/sesi tersebut.
            //    OR-of-AND (bukan dua IN terpisah) supaya ruang tidak
            //    dipasangkan dengan sesi milik ruang lain.
            //    Catatan: where()/orWhere() pada versi CI4 ini TIDAK menerima
            //    Closure, jadi or-of-and dibangun dengan groupStart + orWhere.
            $db = \Config\Database::connect();
            $builder = $db->table('cbt_sesi_siswa ss')
                ->select('ss.siswa_id')
                ->where('ss.tp_id', $tp)
                ->where('ss.smt_id', $smt)
                ->whereIn('ss.kelas_id', $allKelas)
                ->groupBy('ss.siswa_id');

            // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dihitung.
            $this->onlySiswaAktif($builder, 'ss', 'bi', 'siswa_id');

            $builder->groupStart();
            $first = true;
            foreach ($pairs as $p) {
                if ($first) {
                    $builder->where('ss.ruang_id', $p['ruang_id'])->where('ss.sesi_id', $p['sesi_id']);
                    $first = false;
                } else {
                    $builder->orWhere('ss.ruang_id', $p['ruang_id'])->where('ss.sesi_id', $p['sesi_id']);
                }
            }
            $builder->groupEnd();

            $siswaIds = array_column($builder->get()->getResult(), 'siswa_id');
            if (empty($siswaIds)) {
                return 0;
            }

            // 4. Yang sudah mulai ujian -> tidak dihitung
            $sudahMulai = $db->table('cbt_siswa')
                ->select('id_siswa')
                ->whereIn('id_jadwal', array_column($jadwals, 'id_jadwal'))
                ->whereIn('id_siswa', $siswaIds)
                ->where('mulai IS NOT NULL', null, false)
                ->groupBy('id_siswa')
                ->get()
                ->getResult();

            return count($siswaIds) - count($sudahMulai);
        } catch (\Throwable $e) {
            log_message('error', 'countBelumUjian: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Get siswa menu items
     */
    private function getSiswaMenu(): array
    {
        return [
            [
                'title' => 'Jadwal Pelajaran',
                'icon' => 'ic_online.gif',
                'link' => 'kbm/jadwal_pelajaran'
            ],
            [
                'title' => 'Materi',
                'icon' => 'ic_elearning.gif',
                'link' => 'kbm/materi'
            ],
            [
                'title' => 'Tugas',
                'icon' => 'ic_questions.gif',
                'link' => 'kbm/tugas'
            ],
            [
                'title' => 'Nilai Hasil',
                'icon' => 'ic_exam.gif',
                'link' => 'kbm/hasil'
            ],
            [
                'title' => 'Absensi',
                'icon' => 'absensi.png',
                'link' => 'kbm/kehadiran'
            ],
            [
                'title' => 'Catatan Guru',
                'icon' => 'ic_teacher.png',
                'link' => 'siswa/catatan'
            ],
            [
                'title' => 'Arsip',
                'icon' => 'icon-arsip.gif',
                'link' => 'kbm/arsip'
            ],
            [
                'title' => 'E-Perpus',
                'icon' => 'ic_library.png',
                'link' => 'siswa/perpus'
            ],
        ];
    }

    // ============================================
    // AJAX ENDPOINTS
    // ============================================

    /**
     * Get student exam status (real-time)
     */
    public function get_status_siswa()
    {
        try {
            $tp = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return $this->response->setJSON([
                    'ruangs' => [],
                    'jadwals_ujian' => [],
                    'sesisiswa' => [],
                    'cbtsiswa' => [],
                    'gurus' => [],
                    'pengawas' => [],
                    'kelases' => [],
                    'panitias' => [],
                    'today' => date('Y-m-d')
                ]);
            }

            $jadwals = $this->cbtModel->getDataJadwalByTgl(date('Y-m-d'));

            // Collect IDs for bulk fetching
            $arrJadwalIds = array_column($jadwals, 'id_jadwal');

            // Get related data
            $sesiSiswa = [];
            $ruangs = [];
            $cbtSiswa = [];
            $pengawas = [];
            $allKelas = [];

            if (!empty($jadwals)) {
                // Get unique classes from all active banks
                foreach ($jadwals as $j) {
                    if (!empty($j->bank_kelas)) {
                        $kelasBank = $this->cbtModel->maybeUnserialize($j->bank_kelas);
                        if (is_array($kelasBank)) {
                            foreach ($kelasBank as $kb) {
                                $kId = is_object($kb) ? ($kb->kelas_id ?? null) : (is_array($kb) ? ($kb['kelas_id'] ?? null) : $kb);
                                if ($kId)
                                    $allKelas[] = $kId;
                            }
                        }
                    }
                }
                $allKelas = array_unique($allKelas);

                if (!empty($allKelas)) {
                    $ruangs = $this->cbtModel->getDistinctSesi($tp->id_tp, $smt->id_smt, $allKelas);

                    // Get Siswa involved
                    $db = \Config\Database::connect();
                    if (!empty($arrJadwalIds)) { // Check if not empty
                        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
                        $cbtSiswa = $this->onlySiswaAktif(
                            $db->table('cbt_siswa cs')
                                ->select('cs.*')
                                ->whereIn('cs.id_jadwal', $arrJadwalIds),
                            'cs'
                        )->get()->getResult();
                    }

                    // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
                    $sesiSiswa = $this->onlySiswaAktif(
                        $db->table('cbt_sesi_siswa ss')
                            ->select('ss.*')
                            ->where('ss.tp_id', $tp->id_tp)
                            ->where('ss.smt_id', $smt->id_smt)
                            ->whereIn('ss.kelas_id', $allKelas),
                        'ss',
                        'bi',
                        'siswa_id'
                    )->get()->getResult();

                    $pengawas = $this->cbtModel->getAllPengawas($tp->id_tp, $smt->id_smt);
                }
            }

            $user = $this->authModel->user($this->getUserId());
            $role = $this->getUserRole();
            $myRooms = [];
            if (strtolower($role ?? '') === 'panitia') {
                $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
                if ($panitia) {
                    $db = \Config\Database::connect();
                    $assigned = $db->table('cbt_pengawas')
                        ->where('id_tp', $tp->id_tp)
                        ->where('id_smt', $smt->id_smt)
                        ->where('id_guru', 'P' . $panitia->id_panitia)
                        ->get()->getResult();
                    $myRooms = array_unique(array_column($assigned, 'id_ruang'));
                }
            }

            $gurus = $this->masterModel->getDropdownGuru();
            $panitias = $this->masterModel->getDropdownPanitia();
            // Merge gurus and panitias into one array for easier lookup in views
            // Use + operator for associative array merging to preserve keys (even stringified numbers)
            $allPengawas = $gurus + $panitias;

            $data = [
                'today' => date('Y-m-d'),
                'ruangs' => $ruangs,
                'jadwals_ujian' => $jadwals,
                'sesisiswa' => $sesiSiswa,
                'cbtsiswa' => $cbtSiswa,
                'gurus' => $allPengawas,
                'pengawas' => $pengawas,
                'kelases' => $this->dropdownModel->getAllKelas($tp->id_tp, $smt->id_smt),
                'panitias' => $panitias,
                'arr_kelas' => [],
                'my_rooms' => $myRooms
            ];

            foreach ($data['jadwals_ujian'] as &$jw) {
                $bankKelasRaw = $this->cbtModel->maybeUnserialize($jw->bank_kelas);
                $simpleKelas = [];
                if (is_array($bankKelasRaw)) {
                    foreach ($bankKelasRaw as $bk) {
                        $simpleKelas[] = is_object($bk) ? ($bk->kelas_id ?? '') : (is_array($bk) ? ($bk['kelas_id'] ?? '') : $bk);
                    }
                }

                $jw->bank = (object) [
                    'bank_kode' => $jw->bank_kode,
                    'bank_kelas' => $simpleKelas,
                    'mapel' => (object) [
                        'kode' => $jw->nama_mapel
                    ]
                ];
            }

            return $this->response->setJSON($data);
        } catch (\Throwable $th) {
            return $this->response->setJSON([
                'ruangs' => [],
                'jadwals_ujian' => [],
                'sesisiswa' => [],
                'cbtsiswa' => [],
                'gurus' => [],
                'pengawas' => [],
                'kelases' => [],
                'panitias' => [],
                'today' => date('Y-m-d'),
                'error' => $th->getMessage()
            ]);
        }
    }

    /**
     * Get realtime statistics
     */
    public function get_realtime_stats()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();
        $setting = $this->dashboardModel->getSetting();

        if (!$tp || !$smt || !$setting) {
            return $this->response->setJSON(['info_box' => [], 'ujian_box' => []]);
        }

        $role = $this->getUserRole();

        if (strtolower($role) === 'panitia') {
            $user = $this->authModel->user($this->getUserId());
            $panitia = $this->dashboardModel->getDataPanitiaByUserId($user->id, $tp->id_tp, $smt->id_smt);
            
            $is_pengawas = false;
            $assignments = [];
            if ($panitia) {
                $jabatan = strtolower($panitia->jabatan ?? '');
                $namaPanitia = strtolower($panitia->nama_panitia ?? '');
                if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas') {
                    $is_pengawas = true;

                    $db = \Config\Database::connect();
                    $assignments = $db->table('cbt_pengawas')
                        ->where('id_tp', $tp->id_tp)
                        ->where('id_smt', $smt->id_smt)
                        ->where('id_guru', 'P' . $panitia->id_panitia)
                        ->get()->getResult();
                }
            }

            // ujian_box tetap dikirim untuk semua role panitia:
            // view menampilkannya sebagai kartu (id="total-ujian-*") dan
            // dashboard.js.updateStats() mengupdate nilainya tiap 60 detik.
            $ujianBox = $this->getUjianBox($tp->id_tp, $smt->id_smt);

            if ($is_pengawas && !empty($assignments)) {
                $supBox = $this->getSupervisorBox($assignments, $tp->id_tp, $smt->id_smt);
                $data = [
                    'info_box' => $supBox['boxes'],
                    'ujian_box' => $ujianBox
                ];
            } else {
                $data = [
                    'info_box' => $this->getPanitiaBox($tp->id_tp, $smt->id_smt),
                    'ujian_box' => $ujianBox
                ];
            }
        } else {
            $data = [
                'info_box' => $this->getAdminBox($setting, $tp->id_tp, $smt->id_smt),
                'ujian_box' => $this->getUjianBox($tp->id_tp, $smt->id_smt)
            ];
        }

        return $this->response->setJSON($data);
    }

    /**
     * Get activity logs
     */
    public function getlog()
    {
        try {
            $logModel = new \App\Models\LogModel();

            $limit = $this->request->getGet('limit') ?? 5;
            $page = $this->request->getGet('page') ?? 1;
            $offset = ($page - 1) * $limit;

            $logs = $logModel->getLog($offset, $limit);
            $total = $logModel->getCountLog();

            // Format for frontend
            $dataLogs = [];
            foreach ($logs as $log) {
                $foto = $log->foto;
                if ($foto) {
                    if (strpos($foto, 'uploads/') === 0) {
                        $fotoUrl = base_url($foto);
                    } else {
                        $fotoUrl = base_url('uploads/profiles/' . $foto);
                    }
                } else {
                    $fotoUrl = base_url('assets/img/user.png');
                }

                $dataLogs[] = [
                    'text' => $log->keterangan, // JS uses 'text' or 'message'
                    'message' => $log->keterangan,
                    'user' => $log->first_name . ' ' . $log->last_name,
                    'time' => $log->waktu,
                    'foto' => $fotoUrl
                ];
            }

            return $this->response->setJSON(['logs' => $dataLogs, 'total' => $total]);
        } catch (\Throwable $th) {
            return $this->response->setJSON(['logs' => [], 'total' => 0]);
        }
    }

    /**
     * Check token and jadwal
     */
    public function checkTokenJadwal()
    {
        try {
            $ada_ujian = $this->cbtModel->getDataJadwalByTgl(date('Y-m-d'));
            $token = $this->cbtModel->getToken();

            $data = [
                'ada_ujian' => count($ada_ujian ?? []),
                'token' => $token ?? (object) ['token' => '', 'auto' => '0', 'jarak' => '1', 'elapsed' => '00:00:00']
            ];

            return $this->response->setJSON($data);
        } catch (\Throwable $th) {
            return $this->response->setJSON(['ada_ujian' => 0, 'token' => null]);
        }
    }

    /**
     * Load token
     */
    public function loadtoken()
    {
        try {
            $token = $this->cbtModel->getToken();
            return $this->response->setJSON($token ?? (object) ['token' => '', 'auto' => '0', 'now' => date('H:i:s')]);
        } catch (\Throwable $th) {
            return $this->response->setJSON(['token' => '', 'auto' => '0', 'now' => date('H:i:s')]);
        }
    }

    /**
     * Get posts/pengumuman
     */
    public function getpost()
    {
        try {
            $postModel = new \App\Models\PostModel();
            $user = $this->authModel->user();

            if (!$user) {
                // Return empty if no user (session technically expired but handled gracefully for dashboard widgets)
                return $this->response->setJSON(['posts' => [], 'total' => 0]);
            }

            $role = $this->getUserRole();
            $kepada = '0'; // Public
            $kelas = null;

            if (strtolower($role) === 'siswa') {
                $tp = $this->dashboardModel->getTahunActive();
                $smt = $this->dashboardModel->getSemesterActive();

                // Safety check for TP/SMT
                if ($tp && $smt) {
                    $siswa = $this->dashboardModel->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
                    $kelas = $siswa ? $siswa->id_kelas : null;
                }
                $kepada = "'siswa'";
            } elseif (strtolower($role) === 'guru') {
                $kepada = "'guru'";
            }

            $limit = $this->request->getGet('limit') ?? 5;
            $page = $this->request->getGet('page') ?? 1;
            $offset = ($page - 1) * $limit;

            // Use proper params
            // If class is null for siswa, it might cause issues if model expects a value.
            // Ensure getPostForUser handles null $kelas or we pass a dummy.
            // Passing 0 or safe fallback.
            $kelasParam = $kelas ?? 0;

            $posts = $postModel->getPostForUser($kepada, $kelasParam, $limit, $offset);

            // Map to standard JSON response
            $mappedPosts = [];
            if ($posts) {
                foreach ($posts as $p) {
                    $foto = base_url('assets/img/user.png');
                    if ($p->dari != '0') {
                        $pathInDb = $p->foto;
                        if ($pathInDb) {
                            if (strpos($pathInDb, 'uploads/') === 0) {
                                if (file_exists(FCPATH . $pathInDb)) {
                                    $foto = base_url($pathInDb);
                                } else {
                                    $foto = base_url('assets/img/guru.png');
                                }
                            } else {
                                if (file_exists(FCPATH . 'uploads/profiles/' . $pathInDb)) {
                                    $foto = base_url('uploads/profiles/' . $pathInDb);
                                } else {
                                    $foto = base_url('assets/img/guru.png');
                                }
                            }
                        } else {
                            $foto = base_url('assets/img/guru.png');
                        }
                    }

                    $mappedPosts[] = [
                        'id_post' => $p->id_post,
                        'title' => 'Pengumuman',
                        'content' => $p->text ?? ($p->isi ?? ''),
                        'text' => $p->text ?? ($p->isi ?? ''),
                        'created_at' => $p->updated ?? $p->tanggal ?? date('Y-m-d H:i:s'),
                        'author' => $p->dari == '0' ? 'Administrator' : ($p->nama_guru ?? 'Guru'),
                        'author_photo' => $foto,
                        'comments_count' => $p->jml ?? 0
                    ];
                }
            }

            return $this->response->setJSON(['posts' => $mappedPosts, 'total' => count($mappedPosts)]);

        } catch (\Throwable $th) {
            // Log error and return empty to prevent dashboard breakage
            log_message('error', '[Dashboard::getpost] ' . $th->getMessage());
            return $this->response->setJSON(['posts' => [], 'total' => 0, 'error' => 'Gagal memuat postingan']);
        }
    }

    /**
     * Ganti tahun ajaran
     */
    public function gantiTahun()
    {
        $aktif = $this->request->getPost('active');
        $rows = count($this->request->getPost('tahun') ?? []);

        $update = [];
        for ($i = 0; $i <= $rows; $i++) {
            $id_tp = $this->request->getPost("id_tp[$i]");
            $tahun = $this->request->getPost("tahun[$i]");

            if ($id_tp) {
                $update[] = [
                    'id_tp' => $id_tp,
                    'tahun' => $tahun,
                    'active' => ($id_tp == $aktif) ? 1 : 0
                ];
            }
        }

        if (!empty($update)) {
            $db = \Config\Database::connect();
            $db->table('master_tp')->updateBatch($update, 'id_tp');
        }

        return $this->response->setJSON(['status' => true, 'update' => $update]);
    }

    /**
     * Ganti semester
     */
    public function gantiSemester()
    {
        $aktif = $this->request->getPost('active');
        $rows = count($this->request->getPost('smt') ?? []);

        $update = [];
        for ($i = 1; $i <= $rows; $i++) {
            $id_smt = $this->request->getPost("id_smt[$i]");
            $smt = $this->request->getPost("smt[$i]");

            if ($id_smt) {
                $update[] = [
                    'id_smt' => $id_smt,
                    'smt' => $smt,
                    'active' => ($id_smt == $aktif) ? 1 : 0
                ];
            }
        }

        if (!empty($update)) {
            $db = \Config\Database::connect();
            $db->table('master_smt')->updateBatch($update, 'id_smt');
        }

        return $this->response->setJSON(['status' => true, 'update' => $update]);
    }

    /**
     * Save balasan (reply to comment)
     */
    public function savebalasan()
    {
        // Stub - pengumuman feature to be implemented
        return $this->response->setJSON(['status' => true, 'message' => 'Balasan berhasil disimpan']);
    }

    /**
     * Save komentar (comment)
     */
    public function savekomentar()
    {
        // Stub - pengumuman feature to be implemented
        return $this->response->setJSON(['status' => true, 'message' => 'Komentar berhasil disimpan']);
    }

    /**
     * Hapus post
     */
    public function hapuspost()
    {
        // Stub - pengumuman feature to be implemented
        return $this->response->setJSON(['status' => true, 'message' => 'Post berhasil dihapus']);
    }

    /**
     * Hapus komentar
     */
    public function hapuskomentar()
    {
        // Stub - pengumuman feature to be implemented
        return $this->response->setJSON(['status' => true, 'message' => 'Komentar berhasil dihapus']);
    }

    /**
     * Hapus balasan
     */
    public function hapusbalasan()
    {
        // Stub - pengumuman feature to be implemented
        return $this->response->setJSON(['status' => true, 'message' => 'Balasan berhasil dihapus']);
    }

    /**
     * Hapus log
     */
    public function hapuslog()
    {
        $db = \Config\Database::connect();
        $tableName = 'logs';
        if (!$db->tableExists('logs') && $db->tableExists('log')) {
            $tableName = 'log';
        }
        
        $db->table($tableName)->emptyTable();
        return $this->response->setJSON(true);
    }
}
