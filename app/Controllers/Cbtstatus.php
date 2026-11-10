<?php

namespace App\Controllers;

use App\Models\CbtModel;
use App\Models\CbtSiswaModel;
use App\Models\DashboardModel;

/**
 * Cbtstatus Controller - Migrated from CI3 Cbtstatus
 * CBT monitoring for admin/guru/panitia
 */
class Cbtstatus extends BaseController
{
    protected CbtModel $cbtModel;
    protected CbtSiswaModel $cbtSiswaModel;
    protected DashboardModel $dashboardModel;

    public function __construct()
    {
        $this->cbtModel = new CbtModel();
        $this->cbtSiswaModel = new CbtSiswaModel();
        $this->dashboardModel = new DashboardModel();
    }

    /**
     * Monitoring page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        // Only admin, guru, panitia can access
        if ($this->inGroup('siswa')) {
            return redirect()->to('cbtsiswa/cbt');
        }

        $setting = $this->dashboardModel->getSetting();
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $data = [
            'judul' => 'Monitoring Ujian',
            'subjudul' => 'Status Ujian Real-time',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'ruangs' => $this->cbtModel->getAllRuang(),
            'sesis' => $this->cbtModel->getAllSesi(),
            'jadwals' => $tp && $smt ? $this->cbtModel->getActiveJadwal($tp->id_tp, $smt->id_smt) : [],
            'user' => (object)[
                'first_name' => session()->get('first_name'),
                'username' => session()->get('username'),
            ]
        ];

        $is_admin = $this->isAdmin();
        $is_panitia = $this->isPanitia();
        $data['is_admin'] = $is_admin || $is_panitia; // For general panitia visibility

        // Check if user is pengawas (supervisor)
        $data['is_pengawas'] = false;
        $data['assigned_ruangs'] = [];
        $data['assigned_sesis'] = [];

        if ($is_panitia && !$is_admin) {
            $tp_id = $tp->id_tp ?? 0;
            $smt_id = $smt->id_smt ?? 0;
            $user_id = session()->get('user_id');

            $db = \Config\Database::connect();
            $panitiaData = $db->table('master_panitia')
                ->where('id_user', $user_id)
                ->get()
                ->getRow();

            if ($panitiaData) {
                $jabatan = strtolower($panitiaData->jabatan ?? '');
                $namaPanitia = strtolower($panitiaData->nama_panitia ?? '');
                
                // Get assignments
                $assignments = $db->table('cbt_pengawas')
                    ->where('id_tp', $tp_id)
                    ->where('id_smt', $smt_id)
                    ->where('id_guru', 'P' . $panitiaData->id_panitia)
                    ->get()->getResult();

                if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas' || !empty($assignments)) {
                    $data['is_pengawas'] = true;
                    
                    // Collect assigned room and session IDs
                    foreach ($assignments as $as) {
                        $assignedRuangs = (array) $data['assigned_ruangs'];
                        $assignedSesis  = (array) $data['assigned_sesis'];
                        if ($as->id_ruang && !in_array($as->id_ruang, $assignedRuangs)) {
                            $data['assigned_ruangs'][] = $as->id_ruang;
                        }
                        if ($as->id_sesi && !in_array($as->id_sesi, $assignedSesis)) {
                            $data['assigned_sesis'][] = $as->id_sesi;
                        }
                    }
                }
            }
        }

        return view('cbtstatus/index', $data);
    }

    /**
     * Get siswa currently taking exam + statistik.
     * OPTIMASI: getStatistikJadwal sudah 1-query CASE; data siswa + kelas
     * diambil dengan JOIN bukan lewat model loop.
     */
    public function getSiswaUjian()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $idJadwal = (int) $this->request->getGet('id_jadwal');
            if (!$idJadwal) {
                return $this->respond(false, 'ID Jadwal diperlukan');
            }

            $jadwal = $this->cbtModel->getJadwal($idJadwal);
            if (!$jadwal) {
                return $this->respond(false, 'Jadwal tidak ditemukan');
            }

            // Statistik — sudah 1 query berkat optimasi getStatistikJadwal
            $stats = $this->cbtSiswaModel->getStatistikJadwal($idJadwal);

            $db = \Config\Database::connect();

            // Siswa sedang ujian dan sudah selesai — 2 query dengan JOIN
            // (menggantikan JOIN + loop model yang sebelumnya berat)
            $baseSelect = 'a.id_cbt_siswa, a.id_siswa, a.id_jadwal, a.mulai, a.selesai,
                           a.status, a.lama_ujian, a.reset_izin, a.reset_waktu,
                           b.nama, b.nis, b.nisn,
                           c.nama_kelas';
            // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dimonitor.
            $baseJoin = fn($builder) => $this->onlySiswaAktif(
                $builder
                    ->join('master_siswa b',  'a.id_siswa = b.id_siswa', 'left')
                    ->join('kelas_siswa ks',  'ks.id_siswa = a.id_siswa', 'left')
                    ->join('master_kelas c',  'c.id_kelas = ks.id_kelas', 'left')
                    ->where('a.id_jadwal', $idJadwal),
                'a'
            );

            $sedangBuilder = $db->table('cbt_siswa a')->select($baseSelect);
            $sedangBuilder = $baseJoin($sedangBuilder);
            $sedangUjian = $sedangBuilder
                ->where('a.mulai IS NOT NULL')
                ->where('a.selesai', null)
                ->get()->getResult();

            $selesaiBuilder = $db->table('cbt_siswa a')->select($baseSelect);
            $selesaiBuilder = $baseJoin($selesaiBuilder);
            $selesai = $selesaiBuilder
                ->where('a.selesai IS NOT NULL')
                ->get()->getResult();

            return $this->response->setJSON([
                'status'       => true,
                'jadwal'       => $jadwal,
                'stats'        => $stats,
                'sedang_ujian' => $sedangUjian,
                'selesai'      => $selesai,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get siswa belum ujian.
     * OPTIMASI: eliminasi N+1 (getCbtSiswa per siswa) → 1 LEFT JOIN query.
     */
    public function getSiswaBelumUjian()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $idJadwal = (int) $this->request->getGet('id_jadwal');
            if (!$idJadwal) {
                return $this->respond(false, 'ID Jadwal diperlukan');
            }

            $jadwal = $this->cbtModel->getJadwal($idJadwal);
            if (!$jadwal) {
                return $this->respond(false, 'Jadwal tidak ditemukan');
            }

            $tp  = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            $bankKelas = @unserialize($jadwal->bank_kelas ?? '');
            if ($bankKelas === false) {
                $bankKelas = json_decode($jadwal->bank_kelas ?? '[]', true) ?? [];
            }
            $kelasIds = is_array($bankKelas) ? array_column($bankKelas, 'kelas_id') : [];

            if (empty($kelasIds)) {
                return $this->response->setJSON(['status' => true, 'belum_ujian' => []]);
            }

            $db = \Config\Database::connect();

            // ── OPTIMASI: 1 LEFT JOIN menggantikan loop getCbtSiswa per siswa ──
            // Sebelum: N siswa × 1 query = N round-trips
            // Sesudah: 1 query dengan LEFT JOIN, WHERE cs.id_jadwal IS NULL
            $belumUjian = $db->query(
                "SELECT ms.id_siswa, ms.nama, ms.nis, ms.nisn, mk.nama_kelas
                 FROM kelas_siswa ks
                 JOIN master_siswa ms  ON ms.id_siswa  = ks.id_siswa
                 JOIN buku_induk  bi  ON bi.id_siswa  = ks.id_siswa AND bi.status = '1'
                 JOIN master_kelas mk  ON mk.id_kelas  = ks.id_kelas
                 LEFT JOIN cbt_siswa cs
                        ON cs.id_siswa  = ks.id_siswa
                       AND cs.id_jadwal = ?
                       AND cs.mulai     IS NOT NULL
                 WHERE ks.id_tp    = ?
                   AND ks.id_smt   = ?
                   AND ks.id_kelas IN (" . implode(',', array_map('intval', $kelasIds)) . ")
                   AND cs.id_cbt_siswa IS NULL
                 ORDER BY ms.nama ASC",
                [$idJadwal, $tp->id_tp, $smt->id_smt]
            )->getResult();

            return $this->response->setJSON([
                'status'      => true,
                'belum_ujian' => $belumUjian,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Reset login izin for siswa
     */
    public function resetLogin()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            // Only admin and panitia can reset
            if (!$this->isAdmin() && !$this->isPanitia()) {
                return $this->respond(false, 'Akses ditolak');
            }

            $idCbtSiswa = (int)$this->request->getPost('id_cbt_siswa');

            if (!$idCbtSiswa) {
                return $this->respond(false, 'ID tidak valid');
            }

            $result = $this->cbtSiswaModel->resetLogin($idCbtSiswa);

            if ($result) {
                return $this->respond(true, 'Reset login berhasil');
            }

            return $this->respond(false, 'Gagal reset login');
        }
        catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    /**
     * Reset waktu for siswa
     */
    public function resetWaktu()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            // Only admin can reset time
            if (!$this->isAdmin()) {
                return $this->respond(false, 'Akses ditolak');
            }

            $idCbtSiswa = (int)$this->request->getPost('id_cbt_siswa');
            $tambahMenit = (int)($this->request->getPost('tambah_menit') ?? 15);

            if (!$idCbtSiswa) {
                return $this->respond(false, 'ID tidak valid');
            }

            $result = $this->cbtSiswaModel->resetWaktu($idCbtSiswa, $tambahMenit);

            if ($result) {
                return $this->respond(true, "Waktu ditambah {$tambahMenit} menit");
            }

            return $this->respond(false, 'Gagal reset waktu');
        }
        catch (\Exception $e) {
            return $this->respond(false, $e->getMessage());
        }
    }

    public function getjadwalujianbyjadwal()
    {
        $idJadwal = $this->request->getGet('id_jadwal');
        if (!$idJadwal)
            return $this->response->setJSON([]);

        $jadwal = $this->cbtModel->getJadwal($idJadwal);
        if (!$jadwal)
            return $this->response->setJSON([]);

        $bankKelas = $this->cbtModel->maybeUnserialize($jadwal->bank_kelas);
        if (!is_array($bankKelas))
            $bankKelas = [];

        $kelasIds = [];
        foreach ($bankKelas as $bk) {
            $kelasIds[] = is_array($bk) ? ($bk['kelas_id'] ?? 0) : (is_object($bk) ? ($bk->kelas_id ?? 0) : $bk);
        }

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $db = \Config\Database::connect();
        if (empty($kelasIds))
            return $this->response->setJSON([]);

        $kelas = $db->table('master_kelas')
            ->whereIn('id_kelas', $kelasIds)
            //->where('id_tp', $tp->id_tp) // Optional if classes are TP specific
            //->where('id_smt', $smt->id_smt) // Optional
            ->orderBy('nama_kelas', 'ASC')
            ->get()->getResult();

        $data = [];
        foreach ($kelas as $k) {
            $data[$k->id_kelas] = $k->nama_kelas;
        }
        return $this->response->setJSON($data);
    }

    public function getjadwalujianbykelas()
    {
        $idKelas = $this->request->getGet('id_kelas');
        if (!$idKelas)
            return $this->response->setJSON([]);

        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        $jadwals = $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt);

        // Filter by guru if the logged-in user is a guru
        if ($this->inGroup('guru')) {
            $guru = $this->dashboardModel->getDataGuruByUserId(session()->get('user_id'), $tp->id_tp, $smt->id_smt);
            $id_guru = $guru->id_guru ?? null;
            $jadwals = array_filter($jadwals, function ($j) use ($id_guru) {
                return $j->bank_guru_id == $id_guru;
            });
        }

        $data = [];
        foreach ($jadwals as $j) {
            $bankKelas = $this->cbtModel->maybeUnserialize($j->bank_kelas);
            $hasKelas = false;
            if (is_array($bankKelas)) {
                foreach ($bankKelas as $bk) {
                    $kid = is_array($bk) ? ($bk['kelas_id'] ?? 0) : (is_object($bk) ? ($bk->kelas_id ?? 0) : $bk);
                    if ($kid == $idKelas) {
                        $hasKelas = true;
                        break;
                    }
                }
            }
            if ($hasKelas) {
                // Determine Mapel Name
                $nama = $j->bank_kode;
                if (isset($j->nama_mapel))
                    $nama .= " ({$j->nama_mapel})";
                $data[$j->id_jadwal] = $nama;
            }
        }
        return $this->response->setJSON($data);
    }
    public function getTglJadwalUjian()
    {
        $tp = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return $this->response->setJSON(['jadwal' => [], 'ruang' => [], 'sesi' => [], 'kelas' => []]);
        }

        // Get all jadwals for this TP/SMT
        $jadwals = $this->cbtModel->getAllJadwal($tp->id_tp, $smt->id_smt);

        $tgl_jadwal = [];
        $ruangs = [];
        $sesis = [];
        $kelases = [];
        $allJadwal = [];

        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        // Get master ruang/sesi as fallback
        $masterRuangs = $this->cbtModel->getAllRuang();
        $masterSesis = $this->cbtModel->getAllSesi();

        foreach ($jadwals as $jad) {
            // Parse bank_kelas
            $bankKelas = @unserialize($jad->bank_kelas ?? '');
            if ($bankKelas === false) {
                $bankKelas = json_decode($jad->bank_kelas ?? '[]', true) ?? [];
            }

            $arrKls = [];
            if (is_array($bankKelas)) {
                foreach ($bankKelas as $kl) {
                    $kId = is_array($kl) ? ($kl['kelas_id'] ?? 0) : (is_object($kl) ? ($kl->kelas_id ?? 0) : $kl);
                    if ($kId && !in_array($kId, $arrKls)) {
                        $arrKls[] = $kId;
                    }
                }
            }

            // Format date
            $tglMulai = $jad->tgl_mulai ?? '';
            $d = date('Y-m-d', strtotime($tglMulai));
            $ts = strtotime($tglMulai);
            $dayIdx = date('w', $ts);
            $monthIdx = date('n', $ts);

            $dayName = $hari[$dayIdx] ?? '';
            $monthName = $bulan[$monthIdx] ?? '';
            $formatted = $dayName . ', ' . date('j', $ts) . ' ' . $monthName . ' ' . date('Y', $ts);

            $tgl_jadwal[$d] = $formatted;

            // Get distinct ruang and sesi for the classes in this jadwal
            $ruangSesi = $this->cbtModel->getDistinctRuangSesi($tp->id_tp, $smt->id_smt, $arrKls);

            // Merge ruang per date
            if (!isset($ruangs[$d]))
                $ruangs[$d] = [];
            if (!empty($ruangSesi['ruang'])) {
                foreach ($ruangSesi['ruang'] as $id => $nama) {
                    $ruangs[$d][$id] = $nama;
                }
            }

            // Merge sesi per date
            if (!isset($sesis[$d]))
                $sesis[$d] = [];
            if (!empty($ruangSesi['sesi'])) {
                foreach ($ruangSesi['sesi'] as $id => $nama) {
                    $sesis[$d][$id] = $nama;
                }
            }

            // Get kelas names
            if (!empty($arrKls)) {
                $db = \Config\Database::connect();
                $kelasData = $db->table('master_kelas')
                    ->select('id_kelas, nama_kelas')
                    ->whereIn('id_kelas', $arrKls)
                    ->get()
                    ->getResult();

                if (!isset($kelases[$d]))
                    $kelases[$d] = [];
                foreach ($kelasData as $k) {
                    $kelases[$d][$k->id_kelas] = $k->nama_kelas;
                }
            }

            // All jadwal per date
            if (!isset($allJadwal[$d]))
                $allJadwal[$d] = [];
            $allJadwal[$d][$jad->id_jadwal] = $jad->bank_kode;
        }

        // Fallback: if ruang/sesi per date is empty, use master data
        foreach ($tgl_jadwal as $date => $formattedDate) {
            if (empty($ruangs[$date])) {
                foreach ($masterRuangs as $r) {
                    $ruangs[$date][$r->id_ruang] = $r->nama_ruang;
                }
            }
            if (empty($sesis[$date])) {
                foreach ($masterSesis as $s) {
                    $sesis[$date][$s->id_sesi] = $s->nama_sesi;
                }
            }
        }

        return $this->response->setJSON([
            'jadwal' => $tgl_jadwal,
            'ruang' => $ruangs,
            'sesi' => $sesis,
            'kelas' => $kelases,
            'all_jadwal' => $allJadwal
        ]);
    }
    public function getJadwalKelas()
    {
        $tgl = $this->request->getGet('tgl');
        if (!$tgl)
            return $this->response->setJSON([]);

        $jadwals = $this->cbtModel->getDataJadwalByTgl($tgl);
        $kelasIds = [];
        foreach ($jadwals as $j) {
            $bankKelas = @unserialize($j->bank_kelas ?? '');
            if ($bankKelas === false) {
                $bankKelas = json_decode($j->bank_kelas ?? '[]', true) ?? [];
            }
            if (!is_array($bankKelas))
                continue;

            foreach ($bankKelas as $bk) {
                $kId = is_array($bk) ? ($bk['kelas_id'] ?? 0) : (is_object($bk) ? ($bk->kelas_id ?? 0) : $bk);
                if ($kId)
                    $kelasIds[] = $kId;
            }
        }

        $kelasIds = array_unique($kelasIds);
        if (empty($kelasIds))
            return $this->response->setJSON([]);

        $db = \Config\Database::connect();
        $kelas = $db->table('master_kelas')
            ->whereIn('id_kelas', $kelasIds)
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON($kelas);
    }

    public function getStatusSiswa()
    {
        try {
            $tgl        = $this->request->getGet('tgl');
            $ruangFilter = $this->request->getGet('ruang');
            $sesiFilter  = $this->request->getGet('sesi');
            $kelasFilter = $this->request->getGet('kelas');

            if (!$tgl) {
                return $this->response->setJSON(['jadwals' => [], 'sesisiswa' => [], 'is_empty' => true, 'today' => date('Y-m-d H:i:s')]);
            }

            $db  = \Config\Database::connect();
            $tp  = $this->dashboardModel->getTahunActive();
            $smt = $this->dashboardModel->getSemesterActive();

            if (!$tp || !$smt) {
                return $this->response->setJSON(['jadwals' => [], 'sesisiswa' => [], 'is_empty' => true, 'today' => date('Y-m-d H:i:s')]);
            }

            // ── 1. Jadwal hari ini ───────────────────────────────────────────
            $jadwals = $db->table('cbt_jadwal a')
                ->select('a.*, b.bank_kode, b.bank_nama, b.bank_kelas, b.soal_agama, c.nama_mapel, c.kode as kode_mapel')
                ->join('cbt_bank_soal b', 'a.id_bank = b.id_bank', 'left')
                ->join('master_mapel c', 'b.bank_mapel_id = c.id_mapel', 'left')
                ->where('a.id_tp', $tp->id_tp)
                ->where('a.id_smt', $smt->id_smt)
                ->where('DATE(a.tgl_mulai)', $tgl)
                ->orderBy('a.jam_ke', 'ASC')
                ->get()->getResult();

            if (empty($jadwals)) {
                return $this->response->setJSON(['jadwals' => [], 'sesisiswa' => [], 'is_empty' => true, 'today' => date('Y-m-d H:i:s')]);
            }

            // Parse bank_kelas dan kumpulkan semua jadwal/kelas ID
            $allKelasIds     = [];
            $jadwalIds       = [];
            $formattedJadwals = [];

            foreach ($jadwals as $jadwal) {
                $jadwalIds[] = $jadwal->id_jadwal;

                $bankKelas = @unserialize($jadwal->bank_kelas ?? '');
                if ($bankKelas === false) {
                    $bankKelas = json_decode($jadwal->bank_kelas ?? '[]');
                }

                $kelasIdsForJadwal = [];
                if (is_array($bankKelas)) {
                    foreach ($bankKelas as $bk) {
                        $kId = is_array($bk) ? ($bk['kelas_id'] ?? 0) : (is_object($bk) ? ($bk->kelas_id ?? 0) : $bk);
                        if ($kId) {
                            $kelasIdsForJadwal[] = $kId;
                            if (!in_array($kId, $allKelasIds)) {
                                $allKelasIds[] = $kId;
                            }
                        }
                    }
                }

                $formattedJadwals[] = [
                    'id_jadwal'    => $jadwal->id_jadwal,
                    'tgl_mulai'    => $jadwal->tgl_mulai,
                    'tgl_selesai'  => $jadwal->tgl_selesai,
                    'durasi_ujian' => $jadwal->durasi_ujian ?? 0,
                    'jam_ke'       => $jadwal->jam_ke ?? 0,
                    'pelanggaran'  => $jadwal->pelanggaran ?? '0',
                    'token'        => $jadwal->token ?? '0',
                    'token_value'  => $jadwal->token_value ?? '',
                    'bank'         => [
                        'bank_kode'  => $jadwal->bank_kode,
                        'bank_kelas' => $bankKelas,
                        'soal_agama' => $jadwal->soal_agama ?? '-',
                        'mapel'      => ['kode' => $jadwal->kode_mapel ?? '', 'nama_mapel' => $jadwal->nama_mapel ?? ''],
                    ],
                ];
            }

            if (empty($allKelasIds)) {
                return $this->response->setJSON(['jadwals' => $formattedJadwals, 'sesisiswa' => [], 'is_empty' => true, 'today' => date('Y-m-d H:i:s')]);
            }

            // ── 2. OPTIMASI: 6 query terpisah → 1 JOIN besar ────────────────
            // Sebelum: sesi_siswa + siswa + kelas + ruang + sesi + peserta = 6 query
            // Sesudah: 1 LEFT JOIN query mengambil semua sekaligus
            $builder = $db->table('cbt_sesi_siswa ss')
                ->select('
                    ss.id_sesi_siswa, ss.siswa_id, ss.kelas_id, ss.ruang_id, ss.sesi_id,
                    ms.nama, ms.nis, ms.nisn, ms.agama,
                    mk.kode_kelas, mk.nama_kelas,
                    cr.kode_ruang, cr.nama_ruang,
                    cs2.kode_sesi, cs2.nama_sesi,
                    np.nomor_peserta
                ')
                ->join('master_siswa ms',     'ms.id_siswa = ss.siswa_id', 'left')
                ->join('master_kelas mk',     'mk.id_kelas = ss.kelas_id', 'left')
                ->join('cbt_ruang cr',        'cr.id_ruang = ss.ruang_id', 'left')
                ->join('cbt_sesi cs2',        'cs2.id_sesi = ss.sesi_id',  'left')
                ->join('cbt_nomor_peserta np',
                    'np.id_siswa = ss.siswa_id AND np.id_tp = ' . (int)$tp->id_tp . ' AND np.id_smt = ' . (int)$smt->id_smt,
                    'left')
                ->where('ss.tp_id',  $tp->id_tp)
                ->where('ss.smt_id', $smt->id_smt);

            // Hanya siswa berstatus AKTIF (buku_induk.status = 1) yang dimonitor.
            $this->onlySiswaAktif($builder, 'ss', 'bi', 'siswa_id');

            if ($kelasFilter) {
                $builder->where('ss.kelas_id', $kelasFilter);
            } else {
                $builder->whereIn('ss.kelas_id', $allKelasIds);
            }
            if ($ruangFilter) $builder->where('ss.ruang_id', $ruangFilter);
            if ($sesiFilter)  $builder->where('ss.sesi_id',  $sesiFilter);

            $sesiRows = $builder->get()->getResult();

            if (empty($sesiRows)) {
                return $this->response->setJSON(['jadwals' => $formattedJadwals, 'sesisiswa' => [], 'is_empty' => true, 'today' => date('Y-m-d H:i:s')]);
            }

            $studentIds = array_unique(array_column($sesiRows, 'siswa_id'));

            // ── 3. Status ujian (cbt_siswa) — 1 bulk query ──────────────────
            $cbtMap = [];
            if (!empty($jadwalIds) && !empty($studentIds)) {
                // Kolom opsional: cek sekali pakai cache static
                static $hasPelanggaran = null;
                static $hasRemedial    = null;
                if ($hasPelanggaran === null) {
                    $cols = $db->getFieldNames('cbt_siswa');
                    $hasPelanggaran = in_array('pelanggaran',      $cols);
                    $hasRemedial    = in_array('remedial_attempt', $cols);
                }

                $selectFields = 'id_cbt_siswa, id_jadwal, id_siswa, lama_ujian, mulai, reset_izin, reset_waktu, selesai, status';
                if ($hasPelanggaran) $selectFields .= ', pelanggaran';
                if ($hasRemedial)    $selectFields .= ', remedial_attempt';

                $placeholders = implode(',', array_fill(0, count($jadwalIds), '?'));
                $params       = array_merge($jadwalIds, $studentIds);
                $cbtData      = $db->query(
                    "SELECT {$selectFields}
                     FROM cbt_siswa
                     WHERE id_jadwal IN ({$placeholders})
                       AND id_siswa  IN (" . implode(',', array_fill(0, count($studentIds), '?')) . ")",
                    $params
                )->getResult();

                foreach ($cbtData as $cbt) {
                    if (isset($cbt->pelanggaran)) {
                        $pel = @unserialize($cbt->pelanggaran ?? '');
                        if ($pel === false) {
                            $pel = json_decode($cbt->pelanggaran ?? '{}');
                        }
                        $cbt->pelanggaran = $pel ?: (object)['ringan' => 0, 'sedang' => 0, 'berat' => 0];
                    } else {
                        $cbt->pelanggaran = (object)['ringan' => 0, 'sedang' => 0, 'berat' => 0];
                    }
                    $cbtMap[$cbt->id_siswa][] = $cbt;
                }
            }

            // ── 4. Bangun response ───────────────────────────────────────────
            $sesisiswa = [];
            foreach ($sesiRows as $ss) {
                if ($kelasFilter && $ss->kelas_id != $kelasFilter) continue;
                if (!$kelasFilter && !in_array($ss->kelas_id, $allKelasIds)) continue;

                $sesisiswa[] = [
                    'siswa_id' => $ss->siswa_id,
                    'kelas_id' => $ss->kelas_id,
                    'ruang_id' => $ss->ruang_id,
                    'sesi_id'  => $ss->sesi_id,
                    'siswa'    => (object)['id_siswa' => $ss->siswa_id, 'nama' => $ss->nama, 'nis' => $ss->nis, 'nisn' => $ss->nisn, 'agama' => $ss->agama],
                    'kelas'    => (object)['id_kelas' => $ss->kelas_id, 'kode_kelas' => $ss->kode_kelas, 'nama_kelas' => $ss->nama_kelas],
                    'ruang'    => (object)['id_ruang' => $ss->ruang_id, 'kode_ruang' => $ss->kode_ruang, 'nama_ruang' => $ss->nama_ruang],
                    'sesi'     => (object)['id_sesi' => $ss->sesi_id, 'kode_sesi' => $ss->kode_sesi, 'nama_sesi' => $ss->nama_sesi],
                    'peserta'  => $ss->nomor_peserta ? (object)['nomor_peserta' => $ss->nomor_peserta] : null,
                    'cbtsiswa' => $cbtMap[$ss->siswa_id] ?? [],
                ];
            }

            // ── 5. HTTP ETag — hindari transfer data identik ke browser ──────
            $payload   = json_encode(['jadwals' => $formattedJadwals, 'sesisiswa' => $sesisiswa, 'is_empty' => empty($sesisiswa), 'today' => date('Y-m-d H:i:s')]);
            $etag      = '"' . md5($payload) . '"';
            $ifNoneMatch = $this->request->getHeaderLine('If-None-Match');

            if ($ifNoneMatch && $ifNoneMatch === $etag) {
                return $this->response
                    ->setStatusCode(304)
                    ->setHeader('ETag', $etag);
            }

            return $this->response
                ->setHeader('ETag', $etag)
                ->setHeader('Cache-Control', 'no-cache')
                ->setJSON(json_decode($payload));

        } catch (\Exception $e) {
            log_message('error', 'getStatusSiswa error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Terapkan aksi massal: reset izin, paksa selesai, ulang, blokir, unblokir
     * Port dari CI3 Cbtstatus::applyAction dengan penyesuaian ke Query Builder CI4
     */
    public function applyAction()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthorized');
        }

        $raw = $this->request->getPost('data');
        if (!$raw) {
            return $this->respond(false, 'Data tidak boleh kosong');
        }

        $datas = json_decode($raw);
        if (!$datas) {
            return $this->respond(false, 'Data tidak valid');
        }

        $db = \Config\Database::connect();

        $reset = $datas->reset ?? [];
        $force = $datas->force ?? [];
        $ulang = $datas->ulang ?? [];
        $blokir = $datas->blokir ?? [];
        $unblokir = $datas->unblokir ?? [];

        $resultFlags = [
            'update_reset' => false,
            'update_selesai' => false,
            'update_ulangi' => false,
        ];

        // Reset izin
        if (!empty($reset)) {
            $resultFlags['update_reset'] = true;
            foreach ($reset as $rSiswa) {
                if (empty($rSiswa->id_cbt_siswa)) {
                    continue;
                }
                $ids = (array)$rSiswa->id_cbt_siswa;
                $db->table('cbt_siswa')
                    ->whereIn('id_cbt_siswa', $ids)
                    ->update(['reset_izin' => 1]);
            }
        }

        // Paksa selesai
        if (!empty($force)) {
            $resultFlags['update_selesai'] = true;
            foreach ($force as $fSiswa) {
                $idJadwal    = (int) ($fSiswa->id_jadwal ?? 0);
                $idSiswaArr  = isset($fSiswa->id_siswa)     ? (array) $fSiswa->id_siswa     : [];
                $idCbtArr    = isset($fSiswa->id_cbt_siswa) ? (array) $fSiswa->id_cbt_siswa : [];

                // ── OPTIMASI: batch log dalam 1 query ───────────────────────
                if ($idJadwal && !empty($idSiswaArr)) {
                    $logRows = [];
                    $now     = date('Y-m-d H:i:s');
                    foreach ($idSiswaArr as $idSiswa) {
                        $logRows[] = [
                            'id_siswa'  => (int) $idSiswa,
                            'id_jadwal' => $idJadwal,
                            'log_type'  => 2,
                            'address'   => $this->request->getIPAddress(),
                            'log_time'  => $now,
                        ];
                    }
                    if (!empty($logRows)) {
                        $db->table('log_ujian')->insertBatch($logRows);
                    }
                }

                if (!empty($idCbtArr)) {
                    $db->table('cbt_siswa')
                        ->whereIn('id_cbt_siswa', $idCbtArr)
                        ->update([
                            'selesai'     => date('Y-m-d H:i:s'),
                            'status'      => 2,
                            'reset_waktu' => 3,
                        ]);
                }
            }
        }

        // Ulang ujian: hapus log_ujian, cbt_siswa, cbt_rekap_siswa
        if (!empty($ulang)) {
            $resultFlags['update_ulangi'] = true;
            foreach ($ulang as $uSiswa) {
                $idJadwal = (int)($uSiswa->id_jadwal ?? 0);
                $idSiswaArr = isset($uSiswa->id_siswa) ? (array)$uSiswa->id_siswa : [];
                $idCbtArr = isset($uSiswa->id_cbt_siswa) ? (array)$uSiswa->id_cbt_siswa : [];

                if ($idJadwal && !empty($idSiswaArr)) {
                    // Hapus log ujian
                    $db->table('log_ujian')
                        ->where('id_jadwal', $idJadwal)
                        ->whereIn('id_siswa', $idSiswaArr)
                        ->delete();

                    // Hapus cbt_siswa
                    if (!empty($idCbtArr)) {
                        $db->table('cbt_siswa')
                            ->where('id_jadwal', $idJadwal)
                            ->whereIn('id_cbt_siswa', $idCbtArr)
                            ->delete();
                    }

                    // Hapus rekap nilai siswa
                    $db->table('cbt_rekap_siswa')
                        ->where('id_jadwal', $idJadwal)
                        ->whereIn('id_siswa', $idSiswaArr)
                        ->delete();

                    // Hapus nilai siswa
                    $db->table('cbt_nilai')
                        ->where('id_jadwal', $idJadwal)
                        ->whereIn('id_siswa', $idSiswaArr)
                        ->delete();

                    // Hapus jawaban siswa (PENTING AGAR MULAI DARI NOL)
                    $db->table('cbt_jawaban')
                        ->where('id_jadwal', $idJadwal)
                        ->whereIn('id_siswa', $idSiswaArr)
                        ->delete();
                }
            }
        }

        // Blokir
        if (!empty($blokir)) {
            foreach ($blokir as $bSiswa) {
                if (empty($bSiswa->id_cbt_siswa)) {
                    continue;
                }
                $ids = (array)$bSiswa->id_cbt_siswa;
                $db->table('cbt_siswa')
                    ->whereIn('id_cbt_siswa', $ids)
                    ->update(['status' => 9]);
            }
        }

        // Unblokir
        if (!empty($unblokir)) {
            foreach ($unblokir as $ubSiswa) {
                if (empty($ubSiswa->id_cbt_siswa)) {
                    continue;
                }
                $ids = (array)$ubSiswa->id_cbt_siswa;
                $db->table('cbt_siswa')
                    ->whereIn('id_cbt_siswa', $ids)
                    ->update(['status' => 1]);
            }
        }

        return $this->respond(true, 'Aksi berhasil diterapkan', $resultFlags);
    }

    /**
     * Reset timer siswa (mode lama: reset_waktu 1/2/3)
     * Dipanggil dari modal Reset Waktu di tampilan status CBT lama
     */
    public function resetTimer()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Unauthorized');
            }

            $idCbtSiswa = (int)$this->request->getPost('id_cbt_siswa');
            $resetMode = (string)$this->request->getPost('reset');

            if (!$idCbtSiswa || $resetMode === '') {
                return $this->respond(false, 'Data tidak valid');
            }

            $update = [
                'reset_waktu' => $resetMode,
            ];

            // Seperti CI3: jika reset = 1 atau 3, lama_ujian di-reset ke 00:00:00
            if ($resetMode === '1' || $resetMode === '3') {
                $update['lama_ujian'] = null;
                $update['mulai'] = null;
            }

            $db = \Config\Database::connect();
            $ok = $db->table('cbt_siswa')
                ->where('id_cbt_siswa', $idCbtSiswa)
                ->update($update);

            if (!$ok) {
                // Check for database errors if any (though CI4 usually throws exceptions)
                $error = $db->error();
                if ($error['code']) {
                    throw new \Exception("Database error: " . $error['message']);
                }
            }

            return $this->respond((bool)$ok, $ok ? 'Reset waktu berhasil' : 'Reset waktu gagal');

        }
        catch (\Exception $e) {
            log_message('error', '[ResetTimer] Error: ' . $e->getMessage());
            return $this->respond(false, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Live stats polling — endpoint ringan untuk auto-refresh dashboard monitoring.
     *
     * Hanya mengembalikan angka agregat per jadwal (bukan baris siswa) sehingga
     * payload kecil dan cocok untuk polling tiap 10 detik tanpa membebani server.
     *
     * Response:
     *   { status, today, jadwals: [ { id_jadwal, nama_mapel, durasi_ujian,
     *       total, sedang, selesai, belum, diblokir, pct_selesai, expired, ... } ] }
     *
     * ETag digunakan agar 304 dikirim jika data tidak berubah → hemat bandwidth.
     */
    public function getLiveStats()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setStatusCode(401)->setJSON(['status' => false]);
        }

        $tgl = $this->request->getGet('tgl') ?: date('Y-m-d');

        $db  = \Config\Database::connect();
        $tp  = $this->dashboardModel->getTahunActive();
        $smt = $this->dashboardModel->getSemesterActive();

        if (!$tp || !$smt) {
            return $this->response->setJSON(['status' => false, 'message' => 'Periode aktif tidak ditemukan']);
        }

        // Jadwal hari ini sesuai tanggal filter
        $jadwals = $db->table('cbt_jadwal j')
            ->select('j.id_jadwal, j.durasi_ujian, j.tgl_mulai, j.tgl_selesai, j.jam_ke, j.token,
                      m.nama_mapel, b.bank_kode')
            ->join('cbt_bank_soal b', 'b.id_bank = j.id_bank', 'left')
            ->join('master_mapel m',  'm.id_mapel = b.bank_mapel_id', 'left')
            ->where('j.id_tp', $tp->id_tp)
            ->where('j.id_smt', $smt->id_smt)
            ->where('DATE(j.tgl_mulai)', $tgl)
            ->orderBy('j.jam_ke', 'ASC')
            ->get()->getResult();

        if (empty($jadwals)) {
            return $this->response->setJSON(['status' => true, 'today' => date('Y-m-d H:i:s'), 'jadwals' => []]);
        }

        $jadwalIds = array_column($jadwals, 'id_jadwal');
        $now       = time();

        // Satu query agregat untuk semua jadwal sekaligus — jauh lebih efisien
        // dari memanggil getStatistikJadwal() satu per satu
        $placeholders = implode(',', array_fill(0, count($jadwalIds), '?'));
        $agg = $db->query(
            "SELECT
                id_jadwal,
                COUNT(*)                                                                    AS total,
                SUM(CASE WHEN mulai IS NOT NULL AND selesai IS NULL  THEN 1 ELSE 0 END)     AS sedang,
                SUM(CASE WHEN selesai IS NOT NULL                    THEN 1 ELSE 0 END)     AS selesai,
                SUM(CASE WHEN mulai  IS NULL                         THEN 1 ELSE 0 END)     AS belum,
                SUM(CASE WHEN status = '9'                           THEN 1 ELSE 0 END)     AS diblokir,
                MIN(CASE WHEN mulai IS NOT NULL AND selesai IS NULL THEN mulai ELSE NULL END) AS mulai_tercepat,
                MAX(CASE WHEN selesai IS NOT NULL THEN selesai ELSE NULL END)                AS selesai_terakhir
             FROM cbt_siswa
             WHERE id_jadwal IN ({$placeholders})
             GROUP BY id_jadwal",
            $jadwalIds
        )->getResult();

        // Index by id_jadwal untuk O(1) lookup
        $aggMap = [];
        foreach ($agg as $row) {
            $aggMap[(int) $row->id_jadwal] = $row;
        }

        $result = [];
        foreach ($jadwals as $j) {
            $stat    = $aggMap[(int) $j->id_jadwal] ?? null;
            $expired = strtotime($j->tgl_selesai) < $now;

            $total    = (int) ($stat->total    ?? 0);
            $selesai  = (int) ($stat->selesai  ?? 0);
            $sedang   = (int) ($stat->sedang   ?? 0);
            $belum    = (int) ($stat->belum    ?? 0);
            $diblokir = (int) ($stat->diblokir ?? 0);
            $pct      = $total > 0 ? round(($selesai / $total) * 100) : 0;

            // Estimasi sisa waktu rata-rata bagi siswa yang masih mengerjakan
            $sisa_avg = null;
            if ($sedang > 0 && !empty($stat->mulai_tercepat)) {
                $elapsed_max  = $now - strtotime($stat->mulai_tercepat);
                $durasi_detik = (int) $j->durasi_ujian * 60;
                $sisa_avg     = max(0, $durasi_detik - $elapsed_max);
            }

            $result[] = [
                'id_jadwal'            => (int) $j->id_jadwal,
                'nama_mapel'           => $j->nama_mapel ?? $j->bank_kode,
                'bank_kode'            => $j->bank_kode,
                'durasi_ujian'         => (int) $j->durasi_ujian,
                'jam_ke'               => $j->jam_ke,
                'tgl_mulai'            => $j->tgl_mulai,
                'tgl_selesai'          => $j->tgl_selesai,
                'expired'              => $expired,
                'use_token'            => $j->token == '1',
                'total'                => $total,
                'sedang'               => $sedang,
                'selesai'              => $selesai,
                'belum'                => $belum,
                'diblokir'             => $diblokir,
                'pct_selesai'          => $pct,
                'sisa_waktu_avg_detik' => $sisa_avg,
                'selesai_terakhir'     => $stat->selesai_terakhir ?? null,
            ];
        }

        $payload     = ['status' => true, 'today' => date('Y-m-d H:i:s'), 'jadwals' => $result];
        $payloadJson = json_encode($payload);
        $etag        = '"' . md5($payloadJson) . '"';
        $ifNoneMatch = $this->request->getHeaderLine('If-None-Match');

        if ($ifNoneMatch && $ifNoneMatch === $etag) {
            return $this->response->setStatusCode(304)->setHeader('ETag', $etag);
        }

        return $this->response
            ->setHeader('ETag', $etag)
            ->setHeader('Cache-Control', 'no-cache')
            ->setJSON($payload);
    }
}