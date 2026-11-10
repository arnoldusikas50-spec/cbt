<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\CbtModel;
use App\Models\CbtSiswaModel;
use App\Models\DashboardModel;
use App\Services\CbtService;
use App\Traits\CbtAuthTrait;

/**
 * Frontend Cbt Controller
 * Handles CBT exams for siswa
 * 
 * Optimized with:
 * - CbtService for business logic
 * - CbtAuthTrait for auth helpers
 * - Caching for frequently accessed data
 */
class Cbt extends BaseController
{
    use CbtAuthTrait;

    protected $cbtModel;
    protected $cbtSiswaModel;
    protected $dashboardModel;
    protected $cbtService;

    public function __construct()
    {
        // Models
        $this->cbtModel = new CbtModel();
        $this->cbtSiswaModel = new CbtSiswaModel();
        $this->dashboardModel = new DashboardModel();

        // Service layer
        $this->cbtService = new CbtService();

        helper('encryption_helper');

        // OPTIMASI: ensureJawabanTableExists hanya dipanggil sekali per proses,
        // bukan setiap request. Gunakan static flag agar tidak cek DB tiap kali.
        $this->maybeEnsureJawabanTable();
    }

    /**
     * Cek tabel cbt_jawaban hanya sekali per PHP process (shared-nothing model),
     * bukan setiap request. Static flag memastikan tableExists() tidak
     * dipanggil berkali-kali saat banyak concurrent request.
     */
    private function maybeEnsureJawabanTable(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        $this->ensureJawabanTableExists();
    }

    /**
     * Check if user is logged in as siswa
     */
    private function checkSiswaAuth()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('login')->with('error', 'Silakan login terlebih dahulu');
        }

        $role = $this->getUserRole();
        if ($role !== '3' && $role !== 'siswa') {
            return redirect()->to('admin/dashboard')->with('error', 'Akses ditolak');
        }

        return null;
    }

    /**
     * Index - CBT exam list
     */
    public function index()
    {
        $authCheck = $this->checkSiswaAuth();
        if ($authCheck)
            return $authCheck;

        // Use service for cached data retrieval
        $siswa = $this->cbtService->getCurrentSiswa();

        if (!$siswa) {
            return view('errors/disable_login', [
                'setting' => $this->dashboardModel->getSetting(),
                'message' => 'Data siswa tidak ditemukan'
            ]);
        }

        $period = $this->cbtService->getActivePeriod();

        if (!$period['tp'] || !$period['smt']) {
            return view('errors/disable_login', [
                'setting' => $this->dashboardModel->getSetting(),
                'message' => 'Tahun pelajaran atau semester belum diatur'
            ]);
        }

        $data = [
            'siswa' => $siswa,
            'setting' => $this->dashboardModel->getSetting(),
            'tp' => $this->dashboardModel->getTahun(),
            'tp_active' => $period['tp'],
            'smt' => $this->dashboardModel->getSemester(),
            'smt_active' => $period['smt'],
            'judul' => 'Penilaian',
            'subjudul' => 'Daftar Ujian',
            'running_text' => $this->dashboardModel->getRunningText()
        ];

        return view('members/siswa/cbt/data_jadwal', $data);
    }

    /**
     * Get current token (cached via service)
     */
    public function load_token()
    {
        $token = $this->cbtService->getCurrentToken();
        if (!$token) {
            $token = (object) ['token' => 'INVALID', 'auto' => 0];
        }
        return $this->response->setJSON($token);
    }

    /**
     * Get available exams for student via AJAX
     * Optimized with service for cached data
     */
    public function getCbtSiswa()
    {
        if (!$this->isLoggedIn()) {
            return $this->jsonError('Unauthorized', 401);
        }

        try {
            // Always bind to active session user to prevent data exposure by query tampering.
            $currentSiswa = $this->cbtService->getCurrentSiswa();
            if (!$currentSiswa) {
                return $this->jsonError('Session expired');
            }
            $id_siswa = (int) $currentSiswa->id_siswa;

            // Use service for cached period data
            $period = $this->cbtService->getActivePeriod();
            $tp = $period['tp'];
            $smt = $period['smt'];

            if (!$tp || !$smt) {
                return $this->jsonError('Session expired');
            }

            $db = \Config\Database::connect();

            // ── OPTIMASI: 5 query terpisah → 1 JOIN query ──────────────────────
            // Sebelum: master_siswa + cbt_sesi_siswa + master_kelas + cbt_ruang
            //          + cbt_sesi + cbt_nomor_peserta = 6 round-trips ke DB
            // Sesudah: 1 query dengan LEFT JOIN, hemat ~5 round-trips per pageload
            $siswaRow = $db->query(
                "SELECT
                    s.id_siswa, s.nama, s.nis, s.nisn, s.agama, s.foto,
                    ss.kelas_id, ss.ruang_id, ss.sesi_id, ss.tp_id, ss.smt_id,
                    k.id_kelas, k.kode_kelas, k.nama_kelas, k.level_id,
                    r.id_ruang, r.kode_ruang, r.nama_ruang,
                    se.id_sesi, se.kode_sesi, se.nama_sesi, se.waktu_mulai, se.waktu_akhir,
                    np.nomor_peserta
                 FROM master_siswa s
                 LEFT JOIN cbt_sesi_siswa ss
                        ON ss.siswa_id = s.id_siswa
                       AND ss.tp_id   = ?
                       AND ss.smt_id  = ?
                 LEFT JOIN master_kelas k  ON k.id_kelas = ss.kelas_id
                 LEFT JOIN cbt_ruang    r  ON r.id_ruang = ss.ruang_id
                 LEFT JOIN cbt_sesi     se ON se.id_sesi = ss.sesi_id
                 LEFT JOIN cbt_nomor_peserta np
                        ON np.id_siswa = s.id_siswa
                       AND np.id_tp    = ?
                       AND np.id_smt   = ?
                 WHERE s.id_siswa = ?
                 LIMIT 1",
                [$tp->id_tp, $smt->id_smt, $tp->id_tp, $smt->id_smt, $id_siswa]
            )->getRow();

            if (!$siswaRow) {
                return $this->jsonError('Data siswa tidak ditemukan');
            }

            // Susun struktur yang kompatibel dengan JS view
            $siswa = (object) [
                'id_siswa' => $siswaRow->id_siswa,
                'nama'     => $siswaRow->nama,
                'nis'      => $siswaRow->nis,
                'nisn'     => $siswaRow->nisn,
                'agama'    => $siswaRow->agama,
                'foto'     => $siswaRow->foto,
            ];

            $sesisiswa = null;
            if ($siswaRow->kelas_id) {
                $sesisiswa = (object) [
                    'siswa_id' => $id_siswa,
                    'kelas_id' => $siswaRow->kelas_id,
                    'ruang_id' => $siswaRow->ruang_id,
                    'sesi_id'  => $siswaRow->sesi_id,
                    'tp_id'    => $siswaRow->tp_id,
                    'smt_id'   => $siswaRow->smt_id,
                    'kelas'    => (object) [
                        'id_kelas'   => $siswaRow->id_kelas,
                        'kode_kelas' => $siswaRow->kode_kelas,
                        'nama_kelas' => $siswaRow->nama_kelas,
                        'level_id'   => $siswaRow->level_id,
                    ],
                    'ruang'    => $siswaRow->ruang_id ? (object) [
                        'id_ruang'   => $siswaRow->id_ruang,
                        'kode_ruang' => $siswaRow->kode_ruang,
                        'nama_ruang' => $siswaRow->nama_ruang,
                    ] : null,
                    'sesi'     => $siswaRow->sesi_id ? (object) [
                        'id_sesi'     => $siswaRow->id_sesi,
                        'kode_sesi'   => $siswaRow->kode_sesi,
                        'nama_sesi'   => $siswaRow->nama_sesi,
                        'waktu_mulai' => $siswaRow->waktu_mulai,
                        'waktu_akhir' => $siswaRow->waktu_akhir,
                    ] : null,
                    'peserta'  => $siswaRow->nomor_peserta
                        ? (object) ['nomor_peserta' => $siswaRow->nomor_peserta]
                        : null,
                ];
            }

            $siswa->sesisiswa = $sesisiswa;

            // Status ujian siswa: hanya jadwal yang relevan (ada di cbt_siswa)
            $cbtsiswa = $db->table('cbt_siswa')
                ->select('id_jadwal, status, lama_ujian, mulai, selesai, reset_waktu, reset_izin')
                ->where('id_siswa', $id_siswa)
                ->get()->getResult();

            $siswa->cbtsiswa = $cbtsiswa ?: [];

            // Get banks + jadwal in a single query (hindari N+1: sebelumnya 1 query
            // per bank untuk mengambil jadwal). Semua bank level siswa di-join langsung
            // dengan cbt_jadwal sehingga hanya 1 query ke DB.
            $jadwal_today = [];
            $jadwal_before = [];

            if ($sesisiswa && $sesisiswa->kelas && $sesisiswa->kelas->level_id) {
                $banks = $db->table('cbt_bank_soal b')
                    ->select('b.*, mp.kode, mp.nama_mapel, mg.nama_guru, mg.kode_guru,
                              j.id_jadwal, j.tgl_mulai, j.tgl_selesai, j.durasi_ujian, j.jam_ke, j.status as jadwal_status,
                              j.token, j.token_value,
                              jn.nama_jenis')
                    ->join('master_mapel mp', 'mp.id_mapel = b.bank_mapel_id', 'left')
                    ->join('master_guru mg', 'mg.id_guru = b.bank_guru_id', 'left')
                    ->join('cbt_jenis jn', 'jn.id_jenis = b.bank_jenis_id', 'left')
                    ->join('cbt_jadwal j', 'j.id_bank = b.id_bank AND j.status = \'1\'', 'left')
                    ->where('b.id_tp', $tp->id_tp)
                    ->where('b.id_smt', $smt->id_smt)
                    ->where('b.bank_level', $sesisiswa->kelas->level_id)
                    ->where('b.status_soal', '1')
                    ->get()->getResult();

                $todayDate = date('Y-m-d');
                $today = strtotime($todayDate);

                foreach ($banks as $bank) {
                    // Parse bank_kelas (JSON atau PHP-serialize)
                    $bankKelas = $this->parseBankKelas($bank->bank_kelas ?? '[]');
                    $bank->bank_kelas = $bankKelas;

                    $arrKelasCbt = array_column($bankKelas, 'kelas_id');

                    // Bond bank dengan jadwal (jika ada jadwal aktif untuk bank ini)
                    if (!empty($bank->id_jadwal) && in_array($sesisiswa->kelas_id, $arrKelasCbt)) {
                        $jadwal = (object) [
                            'id_jadwal'    => $bank->id_jadwal,
                            'tgl_mulai'    => $bank->tgl_mulai,
                            'tgl_selesai'  => $bank->tgl_selesai,
                            'durasi_ujian' => $bank->durasi_ujian,
                            'jam_ke'       => $bank->jam_ke,
                            'status'       => $bank->jadwal_status,
                            'token'        => $bank->token,
                            'token_value'  => $bank->token_value ?? '',
                            'nama_jenis'   => $bank->nama_jenis,
                        ];

                        $jadwal->token_url = encrypt_id_token((int) $jadwal->id_jadwal);
                        $jadwal->jenis = (object) ['nama_jenis' => $bank->nama_jenis];
                        $bank->jadwal = $jadwal;
                        $bank->mapel = (object) ['kode' => $bank->kode, 'nama_mapel' => $bank->nama_mapel];
                        $bank->guru = (object) ['kode_guru' => $bank->kode_guru, 'nama_guru' => $bank->nama_guru];

                        // Filter agama
                        if ($bank->soal_agama != '-' && $bank->soal_agama != '0' && $bank->soal_agama != $siswa->agama) {
                            continue;
                        }

                        $mulaiDate = date('Y-m-d', strtotime($bank->tgl_mulai ?? ''));
                        $selesaiDate = date('Y-m-d', strtotime($bank->tgl_selesai ?? ''));
                        $mulai = strtotime($mulaiDate);
                        $selesai = strtotime($selesaiDate);

                        if ($mulaiDate == $todayDate) {
                            $jadwal_today[$bank->tgl_mulai][] = $bank;
                        } else if ($today > $mulai && $today <= $selesai) {
                            $jadwal_before[$bank->tgl_mulai][] = $bank;
                        } else if ($today > $selesai) {
                            $jadwal_before[$bank->tgl_mulai][] = $bank;
                        }
                        // Future exams (mulai > today) are not shown
                    }
                }
            }

            return $this->response->setJSON([
                'status' => true,
                'tp' => $this->dashboardModel->getTahun(),
                'smt' => $this->dashboardModel->getSemester(),
                'tp_active' => $tp,
                'smt_active' => $smt,
                'siswa' => $siswa,
                'jadwal_today' => $jadwal_today,
                'jadwal_before' => $jadwal_before,
                'today' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }

    /**
     * Confirm and enter exam - Optimized
     */
    public function konfirmasi($token)
    {
        try {
            if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
                return $this->jsonError('Unauthorized', 401);
            }

            $idJadwal = decrypt_id_token($token);
            if (!$idJadwal && ctype_digit((string) $token)) {
                $idJadwal = (int) $token;
            }
            if (!$idJadwal) {
                return $this->jsonError('Link ujian tidak valid');
            }

            // Use service for cached data
            $siswa = $this->cbtService->getCurrentSiswa();
            if (!$siswa) {
                // Debug if siswa null
                log_message('error', 'Konfirmasi: Siswa not found for user ' . $this->getUserId());
                return $this->jsonError('Data siswa tidak ditemukan (Session expired?)');
            }

            // Use service for jadwal (cached)
            $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
            if (!$jadwal) {
                return $this->jsonError('Jadwal tidak ditemukan');
            }

            $period = $this->cbtService->getActivePeriod();

            // Get bank details
            $bank = $this->cbtModel->getBankSoalById($jadwal->id_bank);
            
            // SECURITY CHECK: Agama Validation
            if ($bank && !empty($bank->soal_agama) && $bank->soal_agama !== '-' && $bank->soal_agama !== '0') {
                if (empty($siswa->agama) || strtoupper(trim($bank->soal_agama)) !== strtoupper(trim($siswa->agama))) {
                    return redirect()->to('cbt')->with('error', 'Akses ditolak: Anda tidak memiliki akses ke mata pelajaran agama ini.');
                }
            }

            // CHECK IF ALREADY FINISHED
            $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
            if ($cbtSiswa && !empty($cbtSiswa->selesai)) {
                return redirect()->to('cbt')->with('error', 'Anda sudah menyelesaikan ujian ini.');
            }

            // CHECK IF BLOCKED (status = 9)
            if (($cbtSiswa->status ?? '0') == '9') {
                $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
                $this->response->setHeader('Pragma', 'no-cache');
                $this->response->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
                return view('members/siswa/cbt/blocked', [
                    'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
                ]);
            }

            // Fix for View Compatibility: Attach bank object to jadwal
            // The view expects $jadwal->bank->id_bank access
            if ($bank) {
                $jadwal->bank = $bank;
            }

            // Normalize jadwal token property
            if (!isset($jadwal->token)) {
                $jadwal->token = $jadwal->token ?? $jadwal->status_token ?? '0';
            }

            $data = [
                'siswa' => $siswa,
                'jadwal' => $jadwal,
                'bank' => $bank,
                'setting' => $this->dashboardModel->getSetting(),
                'tp_active' => $period['tp'],
                'smt_active' => $period['smt'],
                'running_text' => $this->dashboardModel->getRunningText(),
                'support' => true,
                'valid' => true,
                'error' => ''
            ];

            // Prevent caching to fix browser Back button bug
            $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

            return view('members/siswa/cbt/konfirmasi', $data);
        } catch (\Throwable $e) {
            log_message('error', '[Cbt::konfirmasi] Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->to('cbt')->with('error', 'Terjadi kesalahan sistem saat membuka ujian');
        }
    }

    /**
     * Validate and start exam - Optimized
     */
    public function validasiJadwal()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        try {
            $idJadwal = (int) $this->request->getPost('jadwal');
            $inputToken = $this->request->getPost('token');

            // Use service for cached jadwal
            $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
            if (!$jadwal) {
                return $this->jsonError('Jadwal tidak ditemukan');
            }

            // Check Token if required (per-jadwal)
            if (($jadwal->token ?? $jadwal->status_token ?? '0') == '1') {
                $storedToken = strtoupper(trim($jadwal->token_value ?? ''));
                if (empty($storedToken)) {
                    return $this->jsonError('Token belum di-generate. Hubungi pengawas.');
                }
                if ($storedToken !== strtoupper(trim($inputToken))) {
                    return $this->jsonError("Token Salah! Input: {$inputToken}");
                }
            }

            // Use service for cached student data
            $siswa = $this->cbtService->getCurrentSiswa();
            if (!$siswa) {
                return $this->jsonError('Session expired');
            }

            // SECURITY CHECK: Agama Validation
            if (!empty($jadwal->soal_agama) && $jadwal->soal_agama !== '-' && $jadwal->soal_agama !== '0') {
                if (empty($siswa->agama) || strtoupper(trim($jadwal->soal_agama)) !== strtoupper(trim($siswa->agama))) {
                    return $this->jsonError('Akses ditolak: Mapel agama ini tidak sesuai dengan agama Anda.');
                }
            }

            // Get system info
            $agent = $this->request->getUserAgent();
            $ip = $this->request->getIPAddress();

            // Check existing session
            $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);

            // Check if student is blocked (status = 9)
            if ($cbtSiswa && ($cbtSiswa->status ?? '0') == '9') {
                return $this->response->setJSON([
                    'approved' => false,
                    'error' => 'blocked',
                    'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
                ]);
            }

            // Check if student is forced finished (status = 2 and selesai is set)
            if ($cbtSiswa && ($cbtSiswa->status ?? '0') == '2' && !empty($cbtSiswa->selesai)) {
                return $this->response->setJSON([
                    'approved' => false,
                    'error' => 'finished',
                    'message' => 'Ujian Anda telah diselesaikan oleh pengawas.'
                ]);
            }

            $updateData = [
                'id_siswa' => $siswa->id_siswa,
                'id_jadwal' => $idJadwal,
                'ip_address' => $ip,
                'agent' => $agent->getAgentString(),
                'mulai' => date('Y-m-d H:i:s')
            ];

            if ($cbtSiswa) {
                // Already started - update IP/Agent but NOT mulai time
                $updateData['id_cbt_siswa'] = $cbtSiswa->id_cbt_siswa;
                unset($updateData['mulai']);
                $this->cbtSiswaModel->save($updateData);
            } else {
                // First time starting
                $updateData['status'] = '1';
                $this->cbtSiswaModel->save($updateData);
            }

            return $this->jsonSuccess(['id_jadwal' => $idJadwal], 'Berhasil');

        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
        }
    }
    /**
     * Exam Interface - Optimized
     */
    public function penilaian($token)
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return redirect()->to('login');
        }

        $idJadwal = decrypt_id_token($token);
        if (!$idJadwal && ctype_digit((string) $token)) {
            $idJadwal = (int) $token;
        }
        if (!$idJadwal) {
            return redirect()->to('cbt')->with('error', 'Link ujian tidak valid.');
        }

        // Use service for cached data
        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return redirect()->to('cbt')->with('error', 'Sesi siswa tidak valid');
        }

        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if (!$cbtSiswa || $cbtSiswa->selesai) {
            return redirect()->to('cbt');
        }

        // Check if student is blocked (status = 9) - realtime block
        if (($cbtSiswa->status ?? '0') == '9') {
            $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
            return view('members/siswa/cbt/blocked', [
                'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
            ]);
        }

        // PREVENT MULTIPLE SIMULTANEOUS EXAMS
        $activeExam = $this->cbtSiswaModel->getActiveExam((int)$siswa->id_siswa);
        if ($activeExam && (int)$activeExam->id_jadwal !== (int)$idJadwal) {
            $otherJadwal = $this->cbtService->getJadwalComplete((int)$activeExam->id_jadwal);
            $otherMapel = $otherJadwal->nama_mapel ?? 'Ujian Lain';
            return redirect()->to('cbt')->with('error', "Anda sedang dalam sesi ujian: $otherMapel. Selesaikan dahulu sebelum pindah ke mapel lain.");
        }

        // Use service for cached jadwal
        $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
        if (!$jadwal) {
            return redirect()->to('cbt');
        }

        // SECURITY CHECK: Agama Validation
        if (!empty($jadwal->soal_agama) && $jadwal->soal_agama !== '-' && $jadwal->soal_agama !== '0') {
            if (empty($siswa->agama) || strtoupper(trim($jadwal->soal_agama)) !== strtoupper(trim($siswa->agama))) {
                return redirect()->to('cbt')->with('error', 'Akses ditolak: Mapel agama tidak sesuai dengan agama Anda.');
            }
        }

        $period = $this->cbtService->getActivePeriod();

        // Questions - using cached service (avoids expensive RAND() on every request)
        $acakSoal = $jadwal->acak_soal ?? '0';
        $questions = $this->cbtService->getSoalCached($jadwal->id_bank, $siswa->id_siswa, $idJadwal, $acakSoal);

        // Answers - using cached service (reduces per-request DB calls)
        $answers = $this->cbtService->getJawabanCached($siswa->id_siswa, $idJadwal);

        // Calculate timer
        $durasiDetik = ($jadwal->durasi_ujian ?? 60) * 60;
        $mulaiDb = $cbtSiswa->mulai ?? date('Y-m-d H:i:s');
        if (strpos($mulaiDb, '0000') !== false) {
            $mulaiDb = date('Y-m-d H:i:s');
        }
        $mulai = strtotime($mulaiDb);
        $elapsed = strtotime(date('Y-m-d H:i:s')) - $mulai;
        $sisaWaktu = $durasiDetik - $elapsed;

        // Get mapel name from jadwal (already joined via service)
        $nama_mapel = $jadwal->nama_mapel ?? 'Mata Pelajaran';

        $data = [
            'judul' => 'Ujian Berlangsung',
            'subjudul' => $nama_mapel,
            'user' => (object) ['first_name' => session()->get('first_name')],
            'siswa' => $siswa,
            'setting' => $this->dashboardModel->getSetting(),
            'jadwal' => $jadwal,
            'questions' => $questions,
            'answers' => $answers,
            'sisa_waktu' => $sisaWaktu,
            'elapsed_waktu' => $elapsed,
            'tp_active' => $period['tp'],
            'smt_active' => $period['smt'],
            'nama_mapel' => $nama_mapel,
            'pelanggaran' => $jadwal->pelanggaran ?? '0',
            'reset_login' => $jadwal->reset_login ?? '0'
        ];

        // Prevent caching to fix browser Back button bug allowing access after finish
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $this->response->setHeader('Pragma', 'no-cache');
        $this->response->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

        return view('members/siswa/cbt/ujian', $data);
    }

    /**
     * Save answer - Optimized with service & trait
     */
    public function saveJawaban()
    {
        // Use trait helpers for validation
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        // ── RATE LIMIT: maks 60 simpan jawaban per 10 detik per siswa ────────
        $userId = $this->getUserId() ?? 'anon';
        if (!$this->checkThrottle("cbt_save_{$userId}", 60, 10)) {
            log_message('warning', "[saveJawaban] Rate limit hit: user={$userId}");
            return $this->jsonError('Terlalu banyak request. Coba lagi sebentar.', 429);
        }

        try {
            // Handle both direct params and data[] wrapper format from JS
            $data = $this->request->getPost('data');

            if ($data && is_array($data)) {
                $idJadwal = (int) ($data['jadwal'] ?? $this->request->getPost('jadwal') ?? 0);
                $idSoal = (int) ($data['id_soal'] ?? $this->request->getPost('id_soal') ?? 0);
                $jawaban = $data['jawaban_siswa'] ?? $data['jawaban'] ?? $this->request->getPost('jawaban') ?? null;
                $raguData = $data['ragu'] ?? $this->request->getPost('ragu') ?? null;
                $ragu = ($raguData !== null) ? ($raguData === '1' || $raguData === 'true' || $raguData === true) : null;
            } else {
                $idJadwal = (int) $this->request->getPost('jadwal');
                $idSoal = (int) $this->request->getPost('id_soal');
                $jawaban = $this->request->getPost('jawaban');
                $raguPost = $this->request->getPost('ragu');
                $ragu = ($raguPost !== null) ? ($raguPost === '1' || $raguPost === 'true' || $raguPost === true) : null;
            }

            // Handle JSON string for complex types
            if (is_string($jawaban) && !empty($jawaban) && (strpos($jawaban, '[') === 0 || strpos($jawaban, '{') === 0)) {
                // Keep as is - already JSON
            } elseif (is_array($jawaban)) {
                $jawaban = json_encode($jawaban);
            }

            // Use service for cached student data
            $siswa = $this->cbtService->getCurrentSiswa();
            if (!$siswa) {
                return $this->jsonError('Session expired');
            }

            // Validate exam session
            $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
            if (!$cbtSiswa) {
                // Try to auto-create session if jadwal is valid and time window is open
                $jadwal = $this->cbtModel->getJadwal($idJadwal);
                if ($jadwal) {
                    $now = time();
                    $mulai = strtotime($jadwal->tgl_mulai);
                    $selesai = strtotime($jadwal->tgl_selesai);

                    if ($now >= $mulai && $now <= $selesai) {
                        // Auto-start exam session
                        $this->cbtSiswaModel->startExam($siswa->id_siswa, $idJadwal, $jadwal->id_bank);
                        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
                    }
                }

                // If still no session, return error with debug info
                if (!$cbtSiswa) {
                    $debugInfo = "Jadwal: $idJadwal, Siswa: " . $siswa->id_siswa;
                    if (isset($jadwal)) {
                        $debugInfo .= ", Now: " . date('Y-m-d H:i:s') . ", Start: " . $jadwal->tgl_mulai . ", End: " . $jadwal->tgl_selesai;
                    } else {
                        $debugInfo .= ", Jadwal tidak ditemukan di DB";
                    }
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Sesi ujian tidak ditemukan. ' . $debugInfo,
                        'expired' => true
                    ]);
                }
            }

            // Check if student is blocked
            if (($cbtSiswa->status ?? '0') == '9') {
                return $this->response->setJSON([
                    'status' => false,
                    'error' => 'blocked',
                    'message' => 'Anda telah diblokir oleh pengawas! Jawaban tidak dapat disimpan. Silakan hubungi pengawas.',
                ]);
            }

            // If marked as finished, block all save attempts
            if (!empty($cbtSiswa->selesai)) {
                // SECURITY: Re-validate if this is forced finish (reset_waktu == 3)
                if (($cbtSiswa->reset_waktu ?? '0') == '3') {
                    return $this->response->setJSON([
                        'status' => false,
                        'error' => 'finished',
                        'message' => 'Akses ditolak: Ujian Anda telah dipaksa selesai oleh pengawas.',
                        'expired' => true
                    ]);
                }

                // If not forced, it might be a normal finish. Still block saving.
                return $this->response->setJSON([
                    'status' => false,
                    'error' => 'finished',
                    'message' => 'Waktu ujian sudah habis atau ujian telah diselesaikan.',
                    'expired' => true
                ]);
            }

            // REMEDIAL PROTECTION: If in remedial mode, block saving for correct answers
            if (session()->get('remedial_mode') && session()->get('remedial_jadwal') == $idJadwal) {
                $originalStatus = session()->get('remedial_soal_status');
                if ($originalStatus && isset($originalStatus[$idSoal]) && $originalStatus[$idSoal]['is_benar']) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'Soal ini sudah dijawab dengan benar dan terkunci selama remedial.'
                    ]);
                }
            }


            // ── SERVER-SIDE TIMER VALIDATION ─────────────────────────────────
            // Cegah siswa yang memanipulasi timer di sisi client.
            // Toleransi 60 detik untuk latensi jaringan dan clock skew.
            $jadwalForTimer = $this->cbtService->getJadwalComplete($idJadwal);
            if ($jadwalForTimer && !empty($cbtSiswa->mulai)) {
                $durasiDetik = (int) ($jadwalForTimer->durasi_ujian ?? 60) * 60;
                $extraDetik  = (int) ($cbtSiswa->reset_waktu ?? 0) * 60;
                $totalDetik  = $durasiDetik + $extraDetik + 60; // +60 detik toleransi

                $elapsed = time() - strtotime($cbtSiswa->mulai);
                if ($elapsed > $totalDetik) {
                    // Paksa selesai jika waktu benar-benar habis di server
                    $this->cbtSiswaModel->update($cbtSiswa->id_cbt_siswa, [
                        'selesai' => date('Y-m-d H:i:s'),
                        'status'  => '2',
                    ]);
                    return $this->response->setJSON([
                        'status'  => false,
                        'error'   => 'finished',
                        'message' => 'Waktu ujian telah habis.',
                        'expired' => true,
                    ]);
                }
            }

            if ($idJadwal <= 0 || $idSoal <= 0) {
                // Request tidak valid = bug/kebalahan di sisi client (mis. soal
                // belum termuat sehingga id_soal terkirim "undefined"), bukan
                // kegagalan server → dicatat sebagai warning, bukan error, dan
                // POST body tidak dibuang seluruhnya ke log.
                log_message(
                    'warning',
                    '[saveJawaban] 422 request tidak valid: idJadwal=' . $idJadwal
                    . ', idSoal=' . $idSoal
                    . ', siswa=' . ($siswa->id_siswa ?? '-')
                );
                return $this->jsonError('Parameter soal/jadwal tidak valid', 422);
            }

            // ── SECURITY: Validasi kepemilikan soal ─────────────────────────
            // Pastikan id_soal yang dikirim memang bagian dari bank soal jadwal ini.
            // Mencegah siswa menyimpan jawaban ke soal dari jadwal/bank yang berbeda.
            $jadwalForSoal = $jadwalForTimer ?? $this->cbtService->getJadwalComplete($idJadwal);
            if ($jadwalForSoal) {
                $soalMilikJadwal = \Config\Database::connect()
                    ->table('cbt_soal')
                    ->where('id_soal', $idSoal)
                    ->where('bank_id', (int) $jadwalForSoal->id_bank)
                    ->where('tampilkan', '1')
                    ->countAllResults();

                if ($soalMilikJadwal === 0) {
                    log_message('warning', "[saveJawaban] SECURITY: siswa={$siswa->id_siswa} mencoba simpan soal={$idSoal} yang bukan milik jadwal={$idJadwal}");
                    return $this->jsonError('Soal tidak valid untuk jadwal ini', 403);
                }
            }

            // OPTIMASI SKALA BESAR:
            // - Upsert tunggal (INSERT ... ON DUPLICATE KEY UPDATE) menggantikan
            //   SELECT FOR UPDATE + insert/update terpisah. Mengurangi 2 query -> 1 query
            //   dan menghilangkan row-lock yang menjadi bottleneck saat ribuan siswa
            //   menyimpan jawaban bersamaan.
            // - Unique key (id_siswa, id_jadwal, id_soal) menjamin tidak ada duplikat.
            $db = \Config\Database::connect();
            $now = date('Y-m-d H:i:s');
            $raguVal = $ragu !== null ? ($ragu ? '1' : '0') : '0';

            // AUDIT JAWABAN (overhead minimal): baca nilai lama via index
            // unik (1 point-read), catat hanya bila jawaban benar-benar berubah.
            // Insert pertama tidak diaudit agar tulis tetap ringan saat 1000
            // siswa submit bersamaan.
            $oldRow = $db->table('cbt_jawaban')
                ->select('jawaban')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('id_jadwal', $idJadwal)
                ->where('id_soal', $idSoal)
                ->get()->getRow();
            $oldVal = $oldRow->jawaban ?? null;

            $db->query(
                "INSERT INTO cbt_jawaban (id_siswa, id_jadwal, id_soal, jawaban, ragu, waktu)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    jawaban = VALUES(jawaban),
                    ragu    = VALUES(ragu),
                    waktu   = VALUES(waktu)",
                [$siswa->id_siswa, $idJadwal, $idSoal, $jawaban, $raguVal, $now]
            );

            if ($oldRow !== null && (string) $oldVal !== (string) $jawaban) {
                $this->logJawabanChange(
                    $siswa->id_siswa, $idJadwal, $idSoal,
                    'UPDATE', $oldVal, (string) $jawaban
                );
            }

            // Invalidate jawaban cache so next getSoal/modal gets fresh data
            $this->cbtService->invalidateJawabanCache($siswa->id_siswa, $idJadwal);

            // Log aktivitas: simpan jawaban
            $this->cbtService->logAktivitas(
                $siswa->id_siswa, $idJadwal, 'simpan_jawaban', $idSoal,
                $jawaban !== null ? 'len=' . strlen((string)$jawaban) : null
            );

            // UPDATE DURATION: ringan, tanpa transaksi. Dilakukan dengan
            // increment waktu dari sisi client bila tersedia untuk menghindari
            // query tambahan ke cbt_siswa pada setiap klik soal.
            $lamaUjian = $this->request->getPost('lama_ujian');
            if ($lamaUjian) {
                $db->table('cbt_siswa')
                    ->where('id_cbt_siswa', $cbtSiswa->id_cbt_siswa)
                    ->update(['lama_ujian' => $lamaUjian]);
            }

            // Skip expensive count query - just return success, client already tracks count
            // The next getSoal call will return accurate count anyway
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Jawaban tersimpan'
            ]);
        } catch (\Exception $e) {
            log_message('error', '[saveJawaban] Exception: ' . $e->getMessage());
            log_message('error', '[saveJawaban] Stack: ' . $e->getTraceAsString());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle ragu-ragu - Optimized
     */
    public function toggleRagu()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        $idJadwal = (int) $this->request->getPost('jadwal');
        $idSoal = (int) $this->request->getPost('id_soal') ?: (int) $this->request->getPost('soal');
        $raguPost = $this->request->getPost('ragu');
        $ragu = $raguPost === '1' || $raguPost === 'true' || $raguPost === true;

        // Use service for cached student data
        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->jsonError('Session expired');
        }

        // Check if finished
        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if ($cbtSiswa && !empty($cbtSiswa->selesai)) {
            return $this->jsonError('Ujian sudah selesai', 403);
        }

        if ($cbtSiswa && ($cbtSiswa->status ?? '0') == '9') {
            return $this->response->setJSON([
                'status'  => false,
                'error'   => 'blocked',
                'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
            ]);
        }

        // ── SECURITY: Validasi kepemilikan soal ─────────────────────────────
        if ($idJadwal > 0 && $idSoal > 0) {
            $jadwalForRagu = $this->cbtService->getJadwalComplete($idJadwal);
            if ($jadwalForRagu) {
                $soalValid = \Config\Database::connect()
                    ->table('cbt_soal')
                    ->where('id_soal', $idSoal)
                    ->where('bank_id', (int) $jadwalForRagu->id_bank)
                    ->where('tampilkan', '1')
                    ->countAllResults();
                if ($soalValid === 0) {
                    log_message('warning', "[toggleRagu] SECURITY: siswa={$siswa->id_siswa} mencoba ragu soal={$idSoal} bukan milik jadwal={$idJadwal}");
                    return $this->jsonError('Soal tidak valid', 403);
                }
            }
        }


        $db  = \Config\Database::connect();

        // ── OPTIMASI: eliminasi SELECT + conditional INSERT/UPDATE ───────────
        // Sebelum: SELECT → cek existing → INSERT atau UPDATE = 2 queries
        // Sesudah: 1 upsert (ON DUPLICATE KEY UPDATE), butuh UNIQUE key
        //          uk_jawaban_siswa_jadwal_soal yang sudah ditambahkan di migration
        $db->query(
            "INSERT INTO cbt_jawaban (id_siswa, id_jadwal, id_soal, ragu, waktu)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE ragu = VALUES(ragu), waktu = VALUES(waktu)",
            [$siswa->id_siswa, $idJadwal, $idSoal, $ragu ? '1' : '0']
        );

        return $this->jsonSuccess(null, 'Status ragu diubah');
    }

    /**
     * Get Question Content (One by One)
     */
    public function getSoal()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['approved' => false, 'message' => 'Invalid request']);
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->response->setJSON(['approved' => false, 'message' => 'Unauthorized']);
        }

        // ── RATE LIMIT: maks 120 navigasi soal per 10 detik per siswa ────────
        $userId = $this->getUserId() ?? 'anon';
        if (!$this->checkThrottle("cbt_soal_{$userId}", 120, 10)) {
            log_message('warning', "[getSoal] Rate limit hit: user={$userId}");
            return $this->response->setJSON(['approved' => false, 'message' => 'Terlalu banyak request.']);
        }

        $nomor = (int) $this->request->getPost('nomor');
        $idJadwal = (int) $this->request->getPost('jadwal');
        $idBank = (int) $this->request->getPost('bank');

        // Use service for cached student data
        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->jsonError('Session expired');
        }

        // Penggerak mandiri antrian scoring: kuras kecil-kecil saat ada
        // antrian menunggu (di-capacity 0 bila kosong). Tanpa ketergantungan cron.
        $this->cbtService->drainScoringQueueIfNeeded(5);

        // Use cached cbtSiswa (avoid repeated queries during exam navigation)
        // Use cached cbtSiswa (avoid repeated queries during exam navigation)
        $cbtSiswa = $this->cbtService->getCbtSiswaCached($siswa->id_siswa, $idJadwal);
        if (!$cbtSiswa || !empty($cbtSiswa->selesai)) {
            $msg = 'Ujian selesai';
            if (($cbtSiswa->reset_waktu ?? '0') == '3') {
                $msg = 'Ujian Anda telah dipaksa selesai oleh pengawas.';
            }
            return $this->response->setJSON(['approved' => false, 'error' => 'finished', 'message' => $msg]);
        }


        // Check if student is blocked (status = 9) - realtime block during exam
        if (($cbtSiswa->status ?? '0') == '9') {
            return $this->response->setJSON([
                'approved' => false,
                'error' => 'blocked',
                'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
            ]);
        }

        // Use service for cached jadwal
        $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
        if (!$jadwal) {
            return $this->jsonError('Jadwal tidak ditemukan');
        }

        // Check Violation/Blocked
        if (isset($cbtSiswa->pelanggaran) && !empty($cbtSiswa->pelanggaran)) {
            // Logic to check stored violation (simplified)
        }

        // Use cached questions (avoids expensive RAND() query each navigation)
        $acakSoal = $jadwal->acak_soal ?? '0';
        $acakOpsi = $jadwal->acak_opsi ?? '0';
        $questions = $this->cbtService->getSoalCached($idBank, $siswa->id_siswa, $idJadwal, $acakSoal);
        $totalSoal = count($questions);

        if ($nomor > $totalSoal || $nomor < 1) {
            return $this->response->setJSON(['approved' => false, 'message' => 'Nomor soal tidak valid']);
        }

        $soal = $questions[$nomor - 1];

        // Get existing answer from cached data (avoids per-soal query)
        $allJawaban = $this->cbtService->getJawabanCached($siswa->id_siswa, $idJadwal);
        $jawaban = $allJawaban[$soal->id_soal] ?? null;

        // Format Opsi based on question type
        $jenisSoal = $soal->jenis ?? '1';
        $opsi = [];

        if ($jenisSoal == '3') {
            // MATCHING QUESTION: Data is in 'jawaban' column, not 'opsi'
            $rawJawaban = $soal->jawaban ?? '';
            $jawabanData = null;

            if (is_string($rawJawaban) && !empty(trim($rawJawaban))) {
                $trimmed = trim($rawJawaban);
                if ($trimmed[0] === '{' || $trimmed[0] === '[') {
                    $decoded = json_decode($rawJawaban, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $jawabanData = $decoded;
                    }
                }

                if ($jawabanData === null) {
                    $unserialized = @unserialize($rawJawaban);
                    if ($unserialized !== false) {
                        $jawabanData = json_decode(json_encode($unserialized), true);
                    }
                }
            }

            if (is_array($jawabanData) && !empty($jawabanData)) {
                $model = $jawabanData['model'] ?? '2';
                $type = $jawabanData['type'] ?? '2';
                $tabelRaw = $jawabanData['jawaban'] ?? $jawabanData['tabel'] ?? [];

                if (is_object($tabelRaw)) {
                    $tabelRaw = json_decode(json_encode($tabelRaw), true);
                }

                if (is_array($tabelRaw) && !empty($tabelRaw)) {
                    $tabel = [];
                    $leftItems = [];
                    $rightItems = [];

                    $firstKey = array_key_first($tabelRaw);
                    if (is_numeric($firstKey) && is_array($tabelRaw[$firstKey] ?? null)) {
                        $tabel = $tabelRaw;
                        for ($i = 1; $i < count($tabel); $i++) {
                            if (isset($tabel[$i][0]) && !empty($tabel[$i][0])) {
                                $leftItems[] = ['id' => 'L' . $i, 'text' => $tabel[$i][0], 'key' => (string) $i];
                            }
                        }
                        if (isset($tabel[0]) && is_array($tabel[0])) {
                            $alphabet = range('A', 'Z');
                            for ($i = 1; $i < count($tabel[0]); $i++) {
                                $rightItems[] = [
                                    'id' => 'R' . ($i - 1),
                                    'text' => $tabel[0][$i],
                                    'key' => $alphabet[$i - 1] ?? (string) ($i - 1)
                                ];
                            }
                        }
                    } else {
                        $tabel = array_values($tabelRaw);
                        if (isset($jawabanData['kiri']) && is_array($jawabanData['kiri'])) {
                            $idx = 1;
                            foreach ($jawabanData['kiri'] as $text) {
                                $leftItems[] = ['id' => 'L' . $idx, 'text' => $text, 'key' => (string) $idx];
                                $idx++;
                            }
                        }
                        if (isset($jawabanData['kanan']) && is_array($jawabanData['kanan'])) {
                            $alphabet = range('A', 'Z');
                            $idx = 0;
                            foreach ($jawabanData['kanan'] as $text) {
                                $rightItems[] = ['id' => 'R' . $idx, 'text' => $text, 'key' => $alphabet[$idx] ?? (string) $idx];
                                $idx++;
                            }
                        }
                    }

                    // Deterministic Shuffle for Matching Options
                    if ($acakOpsi == '1') {
                        $seed = (int)$siswa->id_siswa + (int)$idJadwal + (int)$soal->id_soal;
                        mt_srand($seed);
                        if (!empty($leftItems)) shuffle($leftItems);
                        if (!empty($rightItems)) shuffle($rightItems);
                        mt_srand();
                    }

                    $opsi = [
                        'model' => $model,
                        'type' => $type,
                        'tabel' => $tabel,
                        'baris' => [],
                        'leftItems' => $leftItems,
                        'rightItems' => $rightItems
                    ];
                } else {
                    $opsi = ['model' => $model, 'type' => $type, 'tabel' => [], 'baris' => [], 'leftItems' => [], 'rightItems' => []];
                }
            } else {
                $opsi = ['debug_status' => 'matching_parse_failed', 'raw_jawaban' => $rawJawaban];
            }

            // Always add full debug for matching
            $opsi['_debug'] = [
                'raw_jawaban_string' => substr($rawJawaban ?? '', 0, 500),
                'jawabanData_type' => is_array($jawabanData) ? 'array' : gettype($jawabanData),
                'jawabanData_keys' => is_array($jawabanData) ? array_keys($jawabanData) : null
            ];
        } elseif ($jenisSoal == '1') {
            // PILIHAN GANDA (single answer)
            $opsi = [];
            foreach (['a', 'b', 'c', 'd', 'e'] as $letter) {
                $col = 'opsi_' . $letter;
                if (isset($soal->$col) && $soal->$col !== null && $soal->$col !== '') {
                    $opsi[] = ['valAlias' => strtoupper($letter), 'value' => $letter, 'opsi' => $soal->$col];
                }
            }

            // Shuffle Pilihan Ganda Options
            if ($acakOpsi == '1' && !empty($opsi)) {
                $seed = (int)$siswa->id_siswa + (int)$idJadwal + (int)$soal->id_soal;
                mt_srand($seed);
                shuffle($opsi);
                mt_srand();

                $alphabet = range('A', 'Z');
                foreach ($opsi as $i => &$item) {
                    $item['valAlias'] = $alphabet[$i];
                }
            }
        } elseif ($jenisSoal == '2') {
            // MULTIPLE CHOICE (multiple answers)
            $rawOpsi = $soal->opsi_a ?? $soal->opsi ?? '';
            $opsiRaw = [];

            if (is_string($rawOpsi) && !empty(trim($rawOpsi))) {
                $trimmed = trim($rawOpsi);
                if ($trimmed[0] === '{' || $trimmed[0] === '[') {
                    $opsiRaw = json_decode($rawOpsi, true) ?: [];
                }
                if (empty($opsiRaw)) {
                    $unserialized = @unserialize($rawOpsi);
                    if ($unserialized !== false) {
                        $opsiRaw = is_object($unserialized) ? json_decode(json_encode($unserialized), true) : $unserialized;
                    }
                }
            }

            $opsi = [];
            if (is_array($opsiRaw)) {
                foreach ($opsiRaw as $letter => $text) {
                    $opsi[] = ['valAlias' => strtoupper($letter), 'value' => $letter, 'opsi' => $text];
                }
            }

            // Shuffle Multiple Choice Options
            if ($acakOpsi == '1' && !empty($opsi)) {
                $seed = (int)$siswa->id_siswa + (int)$idJadwal + (int)$soal->id_soal;
                mt_srand($seed);
                shuffle($opsi);
                mt_srand();

                $alphabet = range('A', 'Z');
                foreach ($opsi as $i => &$item) {
                    $item['valAlias'] = $alphabet[$i];
                }
            }
        } else {
            // OTHER TYPES (Isian, Essay, Urutan, etc.)
            $rawOpsi = $soal->opsi ?? $soal->opsi_a ?? '';
            $opsi = [];

            if (is_string($rawOpsi) && !empty(trim($rawOpsi))) {
                $trimmed = trim($rawOpsi);
                if ($trimmed[0] === '{' || $trimmed[0] === '[') {
                    $opsi = json_decode($rawOpsi, true) ?: [];
                }
                if (empty($opsi)) {
                    $unserialized = @unserialize($rawOpsi);
                    if ($unserialized !== false) {
                        $opsi = is_object($unserialized) ? json_decode(json_encode($unserialized), true) : $unserialized;
                    }
                }
            }

            if (empty($opsi) && ($jenisSoal == '6' || $jenisSoal == '7')) {
                $rawOpsi = $soal->opsi_a ?? '';
                if (is_string($rawOpsi) && !empty(trim($rawOpsi))) {
                    $opsi = json_decode($rawOpsi, true) ?: [];
                }
            }

            // Shuffle Ordering items (jenis 6/7)
            if (($jenisSoal == '6' || $jenisSoal == '7') && $acakOpsi == '1' && is_array($opsi) && !empty($opsi)) {
                $seed = (int)$siswa->id_siswa + (int)$idJadwal + (int)$soal->id_soal;
                mt_srand($seed);
                shuffle($opsi);
                mt_srand();
            }
        }

        if (empty($opsi)) {
            $opsi = [];
        }
        // If model returns raw, we might need to parse. Assuming getSoalByBank does heavy lifting (it usually does in CI3 port)

        // ── Hitung timer dari server (authoritative) ─────────────────────────
        // JS menerima nilai ini dan meng-update countdown-nya, mencegah drift
        // akibat tab sleep / DevTools manipulation.
        $timerState    = $this->cbtService->getTimerState($cbtSiswa, $jadwal);
        $remaining     = $timerState['remaining'];
        $elapsedSeconds = $timerState['elapsed'];

        // Jika server menyatakan waktu habis, blokir akses soal
        if ($timerState['expired'] && ($cbtSiswa->reset_waktu ?? '0') !== '3') {
            // Auto-finish jika belum ditandai selesai
            if (empty($cbtSiswa->selesai)) {
                $this->cbtSiswaModel->update($cbtSiswa->id_cbt_siswa, [
                    'selesai' => date('Y-m-d H:i:s'),
                    'status'  => '2',
                ]);
                $this->cbtService->calculateScore($siswa->id_siswa, $idJadwal);
            }
            return $this->response->setJSON([
                'approved' => false,
                'error'    => 'finished',
                'message'  => 'Waktu ujian telah habis. Jawaban Anda telah disimpan.',
            ]);
        }

        $hours   = floor($elapsedSeconds / 3600);
        $minutes = floor(($elapsedSeconds % 3600) / 60);
        $seconds = $elapsedSeconds % 60;

        // PERFORMANCE: Skip server-side modal generation
        // JS already has soalCache with all answers - let it build modal client-side
        // This removes expensive loop over all questions on every request

        $answeredCount = 0;
        foreach ($allJawaban as $jwb) {
            if (!empty($jwb->jawaban ?? '')) {
                $answeredCount++;
            }
        }

        // Calculate time elapsed
        $mulaiDb = $cbtSiswa->mulai ?? date('Y-m-d H:i:s');
        if (strpos($mulaiDb, '0000') !== false) {
            $mulaiDb = date('Y-m-d H:i:s');
        }
        $elapsedSeconds = time() - strtotime($mulaiDb);
        $hours = floor($elapsedSeconds / 3600);
        $minutes = floor(($elapsedSeconds % 3600) / 60);
        $seconds = $elapsedSeconds % 60;

        // Minimal response - JS handles modal rendering from its cache
        $data = [
            'approved' => true,
            'soal_id' => $soal->id_soal,
            'soal_siswa_id' => $cbtSiswa->id_cbt_siswa,
            'soal_nomor' => $nomor,
            'soal_jenis' => $soal->jenis,
            'tipe_soal' => $soal->tipe_soal ?? 'pg',
            'soal_soal' => $soal->soal,
            'soal_opsi' => $opsi,
            'soal_ragu' => ($jawaban && ($jawaban->ragu ?? '0') == '1'),
            'soal_jawaban_siswa' => $jawaban ? $jawaban->jawaban : null,
            'soal_jawaban_alias' => $jawaban ? ($jawaban->alias ?? null) : null,
            'soal_terjawab' => $answeredCount,
            'soal_total' => $totalSoal,
            'jadwal' => [
                'durasi_ujian' => $jadwal->durasi_ujian ?? 60,
            ],
            'durasi' => [
                'reset_waktu' => $cbtSiswa->reset_waktu ?? '0',
                'lama_ujian'  => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
                // ── sisa_waktu: authoritative dari server, JS wajib sync timer ke nilai ini
                'sisa_waktu'  => $remaining,
            ],
            'interval' => [
                'jam'   => $hours,
                'h'     => $hours,
                'menit' => $minutes,
                'i'     => $minutes,
                'detik' => $seconds,
                's'     => $seconds,
            ],
            'jarak'       => $jadwal->jarak ?? 0,
            'max_jawaban' => $this->getMaxJawabanValue($soal, $opsi),
            'media'       => [],
        ];

        return $this->response->setJSON($data);
    }
    /**
     * Selesai Ujian
     * Optimasi: marking selesai sinkronus, hitung nilai async (background)
     * sehingga response ke siswa tidak terblokir proses skoring.
     */
    public function selesaiUjian()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        try {
            $idJadwal = (int) $this->request->getPost('jadwal');

            $siswa = $this->cbtService->getCurrentSiswa();
            if (!$siswa) {
                return $this->jsonError('Session expired');
            }

            $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
            if (!$cbtSiswa) {
                return $this->jsonError('Data ujian tidak ditemukan');
            }

            if (($cbtSiswa->status ?? '0') == '9') {
                return $this->response->setJSON([
                    'status'  => false,
                    'error'   => 'blocked',
                    'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
                ]);
            }

            // Validasi jarak (durasi minimal) server-side untuk mencegah submit terlalu dini
            $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
            $jarakMenit = (int) ($jadwal->jarak ?? 0);
            if ($jarakMenit > 0 && !empty($cbtSiswa->mulai) && strpos($cbtSiswa->mulai, '0000') === false) {
                $elapsedSec = time() - strtotime($cbtSiswa->mulai);
                $jarakSec   = $jarakMenit * 60;
                if ($elapsedSec < $jarakSec) {
                    $sisaSec = $jarakSec - $elapsedSec;
                    $sisaMnt = ceil($sisaSec / 60);
                    return $this->jsonError("Belum bisa submit. Tunggu {$sisaMnt} menit lagi.");
                }
            }

            // Tandai selesai terlebih dahulu → respons ke siswa secepat mungkin
            $this->cbtSiswaModel->update($cbtSiswa->id_cbt_siswa, [
                'selesai' => date('Y-m-d H:i:s'),
                'status'  => '2',
            ]);

            // Log aktivitas: selesai ujian
            $this->cbtService->logAktivitas($siswa->id_siswa, $idJadwal, 'selesai');

            // ── Hitung nilai: enqueue ke antrian (skala besar aman) ──────────
            // Sebelumnya spawn 1 proses PHP CLI (exec) per siswa — fatal saat
            // 1000 siswa submit bersamaan. Kini hanya INSERT ringan ke
            // cbt_scoring_queue; konsumen cron memprosesnya.
            // Fallback ke hitung sinkron hanya bila enqueue gagal.
            $enqueued = $this->cbtService->enqueueScoring((int) $siswa->id_siswa, $idJadwal);
            if (!$enqueued) {
                try {
                    $this->cbtService->calculateScore((int) $siswa->id_siswa, $idJadwal);
                } catch (\Throwable $e) {
                    log_message('error', "[selesaiUjian] fallback hitung nilai gagal: " . $e->getMessage());
                }
            }

            return $this->jsonSuccess(
                ['redirect' => site_url('cbt/hasil/' . encrypt_id_token($idJadwal))],
                'Ujian selesai'
            );
        } catch (\Exception $e) {
            log_message('error', '[selesaiUjian] ' . $e->getMessage());
            return $this->jsonError('Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Validasi Token - Lightweight AJAX endpoint to verify per-jadwal token
     * before advancing to the signature step on the confirmation page.
     */
    public function validasiToken()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Invalid request']);
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Unauthorized']);
        }

        $idJadwal   = (int) $this->request->getPost('jadwal');
        $inputToken = strtoupper(trim($this->request->getPost('token') ?? ''));

        if (!$idJadwal || $inputToken === '') {
            return $this->response->setJSON(['valid' => false, 'message' => 'Data tidak lengkap']);
        }

        $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
        if (!$jadwal) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Jadwal tidak ditemukan']);
        }

        // Jadwal does not require a token — treat as valid
        $requireToken = ($jadwal->token ?? $jadwal->status_token ?? '0') == '1';
        if (!$requireToken) {
            return $this->response->setJSON(['valid' => true]);
        }

        $storedToken = strtoupper(trim($jadwal->token_value ?? ''));
        if ($storedToken === '') {
            return $this->response->setJSON(['valid' => false, 'message' => 'Token ujian belum diset. Hubungi pengawas.']);
        }

        if ($inputToken !== $storedToken) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Token salah. Periksa kembali.']);
        }

        // Store validated token in session so validasiSiswa() can skip re-checking
        session()->set('token_' . $idJadwal, $inputToken);

        return $this->response->setJSON(['valid' => true]);
    }

    /**
     * Validasi Siswa - Validate student before exam - Optimized
     */
    public function validasiSiswa()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['approved' => false, 'token' => false, 'support' => false, 'izinkan' => false, 'message' => 'Invalid request']);
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->response->setJSON(['approved' => false, 'token' => false, 'support' => false, 'izinkan' => false, 'message' => 'Unauthorized']);
        }

        try {
            $idJadwal = (int) $this->request->getPost('jadwal');
            $siswa = $this->cbtService->getCurrentSiswa();
            if (!$siswa) {
                return $this->response->setJSON(['approved' => false, 'token' => false, 'support' => false, 'izinkan' => false, 'message' => 'Session expired']);
            }
            $idSiswa = (int) $siswa->id_siswa;
            $tokenSiswa = $this->request->getPost('token');

            // Use service for cached data
            $period = $this->cbtService->getActivePeriod();
            $tp = $period['tp'];
            $smt = $period['smt'];

            $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
            if (!$jadwal) {
                return $this->response->setJSON(['approved' => false, 'token' => false, 'support' => false, 'izinkan' => false, 'message' => 'Jadwal tidak ditemukan']);
            }

            // PREVENT MULTIPLE SIMULTANEOUS EXAMS
            $activeExam = $this->cbtSiswaModel->getActiveExam($idSiswa);
            if ($activeExam && (int)$activeExam->id_jadwal !== $idJadwal) {
                $otherJadwal = $this->cbtService->getJadwalComplete((int)$activeExam->id_jadwal);
                $otherMapel = $otherJadwal->nama_mapel ?? 'Ujian Lain';
                return $this->response->setJSON([
                    'approved' => false, 
                    'token' => false, 
                    'support' => false, 
                    'izinkan' => false, 
                    'message' => "Anda masih memiliki sesi aktif di mapel: <b>$otherMapel</b>. Gunakan satu tab browser saja dan selesaikan ujian tersebut terlebih dahulu."
                ]);
            }

            // Check token if required
            $tokenValid = true;
            $tokenMsg = '';
            // Handle null/string variations
            $jadwalToken = $jadwal->token ?? $jadwal->status_token ?? '0';

            // ── OPTIMASI: eliminasi double query cbt_siswa ──────────────────────
            // Sebelum: query existingSession lalu query cbtSiswa = 2 round-trips
            // Sesudah: 1 query, gunakan hasil yang sama untuk kedua keperluan
            $db = \Config\Database::connect();
            $cbtSiswa = $db->table('cbt_siswa')
                ->where('id_jadwal', $idJadwal)
                ->where('id_siswa', $idSiswa)
                ->get()->getRow();

            if ($cbtSiswa && ($cbtSiswa->status ?? '0') == '9') {
                return $this->response->setJSON([
                    'approved' => false,
                    'token' => false,
                    'support' => false,
                    'izinkan' => false,
                    'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
                ]);
            }

            // Jika sesi sudah ada dan sudah mulai, skip validasi token
            $alreadyStarted = $cbtSiswa
                && !empty($cbtSiswa->mulai)
                && strpos($cbtSiswa->mulai, '0000') === false;

            if (!$alreadyStarted && $jadwalToken == '1') {
                // If token was already pre-validated via validasiToken() endpoint, trust the session
                $sessionToken = session()->get('token_' . $idJadwal);
                if ($sessionToken && strtoupper(trim($sessionToken)) === strtoupper(trim($tokenSiswa))) {
                    // Token pre-validated — skip DB re-check
                } else {
                    $tokenValue = $jadwal->token_value ?? '';
                    if (empty($tokenValue)) {
                        $tokenValid = false;
                        $tokenMsg = 'Token ujian belum diset oleh admin';
                    } elseif (empty($tokenSiswa)) {
                        $tokenValid = false;
                        $tokenMsg = 'Token tidak dikirim. Silakan kembali ke halaman konfirmasi.';
                    } elseif (strtoupper(trim($tokenValue)) !== strtoupper(trim($tokenSiswa))) {
                        $tokenValid = false;
                        $tokenMsg = 'Token salah';
                    } else {
                        session()->set('token_' . $idJadwal, $tokenSiswa);
                    }
                }
            }

            if (!$tokenValid) {
                return $this->response->setJSON(['approved' => false, 'token' => false, 'token_msg' => $tokenMsg, 'support' => true, 'izinkan' => false, 'message' => $tokenMsg]);
            }

            // Check device/browser support
            $agent   = $this->request->getUserAgent();
            $support = $agent->getBrowser() !== '';

            if (!$support) {
                return $this->response->setJSON(['approved' => false, 'token' => true, 'support' => false, 'izinkan' => false, 'message' => 'Browser tidak didukung']);
            }

            // ── Inisialisasi sesi ujian (gunakan $cbtSiswa yang sudah ada) ───────
            if (!$cbtSiswa) {
                $db->table('cbt_siswa')->insert([
                    'id_jadwal' => $idJadwal,
                    'id_siswa'  => $idSiswa,
                    'mulai'     => date('Y-m-d H:i:s'),
                ]);
                $cbtSiswa = $db->table('cbt_siswa')
                    ->where('id_jadwal', $idJadwal)
                    ->where('id_siswa', $idSiswa)
                    ->get()->getRow();
            } elseif (empty($cbtSiswa->mulai) || strpos($cbtSiswa->mulai, '0000') !== false) {
                $db->table('cbt_siswa')
                    ->where('id_cbt_siswa', $cbtSiswa->id_cbt_siswa)
                    ->update(['mulai' => date('Y-m-d H:i:s')]);
                $cbtSiswa->mulai = date('Y-m-d H:i:s');
            }

            // ── Cek sisa waktu via service (authoritative) ──────────────────
            $ada_waktu = true;
            if ($cbtSiswa && !empty($cbtSiswa->mulai)) {
                $timerState = $this->cbtService->getTimerState($cbtSiswa, $jadwal);
                $ada_waktu  = !$timerState['expired'];
            }

            // ── Hitung jumlah soal via cache (hindari query ulang) ───────────
            $acakSoal = $jadwal->acak_soal ?? '0';
            $questions = $this->cbtService->getSoalCached((int) $jadwal->id_bank, $idSiswa, $idJadwal, $acakSoal);
            $jml_soal  = count($questions);



            log_message('info', "Validasi Siswa: Jadwal=$idJadwal, Siswa=$idSiswa, Soal=$jml_soal");

            // Log aktivitas: siswa mulai ujian
            $this->cbtService->logAktivitas($idSiswa, $idJadwal, 'mulai', null, "soal=$jml_soal");

            // Hapus token_value dari respons agar tidak bocor ke client
            unset($jadwal->token_value);

            return $this->response->setJSON([
                'approved' => true, // For JS compatibility
                'token' => true,
                'support' => true,
                'izinkan' => true,
                'ada_waktu' => $ada_waktu,
                'jadwal' => $jadwal,
                'jml_soal' => $jml_soal,
                'token_url' => encrypt_id_token($idJadwal)
            ]);

        } catch (\Throwable $e) {
            log_message('critical', 'Validasi Siswa Error: ' . $e->getMessage());
            // Return 200 so we can see the JSON body
            return $this->response->setJSON([
                'approved' => false, // For JS compatibility
                'token' => false,
                'support' => false,
                'izinkan' => false,
                'message' => 'SysError: ' . $e->getMessage(),
                'debug_trace' => $e->getTraceAsString()
            ]);
        }
    }



    /**
     * Hitung Pelanggaran - catat pelanggaran siswa secara atomik
     * Optimasi: gunakan UPDATE langsung dengan JSON_SET untuk menghindari
     * race condition SELECT → UPDATE yang terjadi saat banyak tab switch
     * dilaporkan bersamaan.
     */
    public function hitungPelanggaran()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        $idSiswa  = (int) $this->request->getPost('siswa');
        $idJadwal = (int) $this->request->getPost('jadwal');
        $jenis    = $this->request->getPost('jenis'); // ringan | sedang | berat

        // Whitelist jenis pelanggaran — cegah SQL injection lewat kolom nama
        if (!in_array($jenis, ['ringan', 'sedang', 'berat'], true)) {
            return $this->jsonError('Jenis pelanggaran tidak valid');
        }

        $db = \Config\Database::connect();

        // Ambil record sekali untuk baca & update sekaligus
        $cbtSiswa = $db->table('cbt_siswa')
            ->select('id_cbt_siswa, pelanggaran')
            ->where('id_siswa', $idSiswa)
            ->where('id_jadwal', $idJadwal)
            ->get()->getRow();

        if (!$cbtSiswa) {
            return $this->jsonError('Data tidak ditemukan');
        }

        // Decode JSON pelanggaran yang ada
        $pelanggaranRaw = $cbtSiswa->pelanggaran ?? '{}';
        $pelanggaran    = json_decode($pelanggaranRaw, true);
        if (!is_array($pelanggaran)) {
            $pelanggaran = ['ringan' => 0, 'sedang' => 0, 'berat' => 0];
        }
        $pelanggaran[$jenis] = (int) ($pelanggaran[$jenis] ?? 0) + 1;
        $newJson = json_encode($pelanggaran);

        // Single UPDATE — tidak perlu SELECT terpisah lagi
        $db->table('cbt_siswa')
            ->where('id_cbt_siswa', $cbtSiswa->id_cbt_siswa)
            ->update(['pelanggaran' => $newJson]);

        // Log aktivitas: pelanggaran
        $this->cbtService->logAktivitas($idSiswa, $idJadwal, 'tab_switch', null, "jenis=$jenis");

        return $this->jsonSuccess(['pelanggaran' => $pelanggaran], 'Pelanggaran dicatat');
    }

    /**
     * Log aktivitas client-side (blur, fullscreen_exit, copy_paste).
     * Dipanggil dari JS ujian saat event browser terdeteksi.
     * Ringan: hanya INSERT, tidak ada DB read.
     */
    public function logAktivitasSiswa()
    {
        if (!$this->request->isAJAX() || !$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->response->setStatusCode(204)->setBody('');
        }

        $aksiAllowed = ['blur', 'fullscreen_exit', 'copy_paste', 'buka_soal', 'ragu'];
        $aksi     = $this->request->getPost('aksi') ?? '';
        $idJadwal = (int) $this->request->getPost('jadwal');
        $idSoal   = (int) ($this->request->getPost('soal') ?? 0) ?: null;
        $detail   = $this->request->getPost('detail') ?? null;

        if (!in_array($aksi, $aksiAllowed, true) || $idJadwal <= 0) {
            return $this->response->setStatusCode(204)->setBody('');
        }

        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->response->setStatusCode(204)->setBody('');
        }

        $this->cbtService->logAktivitas($siswa->id_siswa, $idJadwal, $aksi, $idSoal, $detail);

        // 204 No Content — JS tidak perlu menangani response
        return $this->response->setStatusCode(204)->setBody('');
    }

    /**
     * Leave CBT - Force logout from exam
     */
    public function leavecbt()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('login');
        }

        // Use service for cached student data
        $siswa = $this->cbtService->getCurrentSiswa();
        $idJadwal = $this->request->getPost('id_jadwal');

        if ($idJadwal && $siswa) {
            // Log leave action if needed
            // $this->cbtModel->saveLog($siswa->id_siswa, (int) $idJadwal, 3, 'Keluar dari ujian');
        }

        return $this->jsonSuccess(['redirect' => site_url('cbt')], 'OK');
    }

    /**
     * Save signature image - Optimized
     */
    public function saveSignature()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        if (!defined('FCPATH')) {
            return $this->jsonError('System configuration error (FCPATH)');
        }

        $dataImg = $this->request->getPost('signature_data');
        $idSiswa = $this->request->getPost('siswa');
        $idJadwal = $this->request->getPost('jadwal');
        $idBank = $this->request->getPost('bank');

        // Fallback: Get id_bank from jadwal if not provided
        if (empty($idBank) && !empty($idJadwal)) {
            $jadwal = $this->cbtService->getJadwalComplete((int) $idJadwal);
            $idBank = $jadwal->id_bank ?? '';
        }

        if (empty($dataImg)) {
            return $this->jsonError('Data tanda tangan kosong');
        }

        try {
            $path = FCPATH . 'uploads/ttd_siswa/';

            if (!is_dir($path)) {
                if (!mkdir($path, 0777, true)) {
                    log_message('error', 'CBT: Failed to create dir ' . $path);
                    return $this->jsonError('Gagal membuat direktori');
                }
            }

            // Base64 decode
            $dataImg = str_replace('data:image/png;base64,', '', $dataImg);
            $dataImg = str_replace(' ', '+', $dataImg);
            $image_base64 = base64_decode($dataImg);

            if ($image_base64 === false) {
                return $this->jsonError('Gagal decode gambar');
            }

            $fileName = $idSiswa . '_' . $idBank . '_' . $idJadwal . '.png';
            $fullPath = $path . $fileName;

            if (file_put_contents($fullPath, $image_base64) !== false) {
                return $this->jsonSuccess(null, 'Tanda tangan tersimpan');
            } else {
                log_message('error', 'CBT: Failed to write ' . $fullPath);
                return $this->jsonError('Gagal menyimpan file');
            }

        } catch (\Throwable $e) {
            log_message('critical', 'CBT saveSignature Error: ' . $e->getMessage());
            return $this->jsonError('Error: ' . $e->getMessage());
        }
    }

    /**
     * Absensi - Attendance page - Optimized
     */
    public function absensi()
    {
        $authCheck = $this->checkSiswaAuth();
        if ($authCheck)
            return $authCheck;

        // Use service for cached data
        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return redirect()->to('login');
        }

        $period = $this->cbtService->getActivePeriod();
        $tp = $period['tp'];
        $smt = $period['smt'];

        $db = \Config\Database::connect();

        // Efficient Query: Get Attendance Records directly from cbt_siswa
        $jadwals = $db->table('cbt_siswa s')
            ->select('j.*, j.id_bank, b.bank_kode, b.bank_kelas, c.nama_jenis, d.kode as kode_mapel, d.nama_mapel, s.mulai, s.selesai')
            ->join('cbt_jadwal j', 's.id_jadwal = j.id_jadwal')
            ->join('cbt_bank_soal b', 'j.id_bank = b.id_bank', 'left')
            ->join('cbt_jenis c', 'j.id_jenis = c.id_jenis', 'left')
            ->join('master_mapel d', 'b.bank_mapel_id = d.id_mapel', 'left')
            ->where('s.id_siswa', $siswa->id_siswa)
            ->where('j.id_tp', $tp->id_tp)
            ->where('j.id_smt', $smt->id_smt)
            ->orderBy('j.tgl_mulai', 'DESC')
            ->get()->getResult();

        $my_jadwals = [];
        $path = FCPATH . 'uploads/ttd_siswa/';

        foreach ($jadwals as $jadwal) {
            $jadwal->hadir = true;
            $jadwal->dikerjakan = ($jadwal->selesai != null);

            // Check signature (both formats)
            $fileNameNormal = $siswa->id_siswa . '_' . $jadwal->id_bank . '_' . $jadwal->id_jadwal . '.png';
            $fileNameBug = $siswa->id_siswa . '__' . $jadwal->id_jadwal . '.png';

            if (file_exists($path . $fileNameNormal)) {
                $jadwal->signature = base_url('uploads/ttd_siswa/' . $fileNameNormal);
            } elseif (file_exists($path . $fileNameBug)) {
                $jadwal->signature = base_url('uploads/ttd_siswa/' . $fileNameBug);
            } else {
                $jadwal->signature = null;
            }

            $my_jadwals[] = $jadwal;
        }

        $data = [
            'siswa' => $siswa,
            'judul' => 'Absensi Ujian',
            'subjudul' => 'Daftar Kehadiran Ujian',
            'setting' => $this->dashboardModel->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'jadwals' => $my_jadwals,
            'running_text' => $this->dashboardModel->getRunningText()
        ];

        return view('members/siswa/cbt/absensi', $data);
    }

    /**
     * Ensure cbt_jawaban table exists (auto-create if missing)
     */
    private function ensureJawabanTableExists()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('cbt_jawaban')) {
            $forge = \Config\Database::forge();
            $fields = [
                'id_jawaban' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true, 'unsigned' => true],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11],
                'id_jadwal' => ['type' => 'INT', 'constraint' => 11],
                'id_soal' => ['type' => 'INT', 'constraint' => 11],
                'id_bank' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'jawaban' => ['type' => 'TEXT', 'null' => true],
                'jenis' => ['type' => 'INT', 'constraint' => 1, 'null' => true],
                'nilai' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
                'ragu' => ['type' => 'INT', 'constraint' => 1, 'default' => 0],
                'waktu' => ['type' => 'DATETIME', 'null' => true],
                'alias' => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => true]
            ];
            $forge->addField($fields);
            $forge->addKey('id_jawaban', true);
            $forge->addKey(['id_siswa', 'id_jadwal']); // Composite index for query speed
            $forge->createTable('cbt_jawaban', true);
        }
    }

    /**
     * Calculate max allowed answers for complex multiple choice (type 2)
     * Based on number of correct answers in 'jawaban' column
     */
    private function getMaxJawabanValue($soal, $opsi): array
    {
        // For type 2 (Pilihan Ganda Kompleks), count correct answers from 'jawaban' column
        if ($soal->jenis == '2') {
            // Get correct answers from 'jawaban' column
            $rawJawaban = $soal->jawaban ?? '';
            $correctAnswers = [];

            if (!empty($rawJawaban)) {
                // Try JSON decode first
                if (is_string($rawJawaban)) {
                    $trimmed = trim($rawJawaban);
                    if ($trimmed[0] === '[' || $trimmed[0] === '{') {
                        $decoded = json_decode($rawJawaban, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $correctAnswers = $decoded;
                        }
                    }

                    // Try unserialize if JSON failed
                    if (empty($correctAnswers)) {
                        $unserialized = @unserialize($rawJawaban);
                        if ($unserialized !== false) {
                            $correctAnswers = is_array($unserialized) ? $unserialized : [$unserialized];
                        }
                    }

                    // Try comma-separated string (e.g., "A,B,C")
                    if (empty($correctAnswers) && strpos($rawJawaban, ',') !== false) {
                        $correctAnswers = array_map('trim', explode(',', $rawJawaban));
                    }
                } elseif (is_array($rawJawaban)) {
                    $correctAnswers = $rawJawaban;
                }
            }

            // Count correct answers
            $count = is_array($correctAnswers) ? count($correctAnswers) : 0;

            // If still 0, fallback to pg_kompleks or count of options
            if ($count <= 0) {
                if (isset($soal->pg_kompleks) && $soal->pg_kompleks > 0) {
                    return [(int) $soal->pg_kompleks];
                }
                // Last fallback: count of options
                $count = is_array($opsi) ? count($opsi) : 4;
            }

            return [$count > 0 ? $count : 4];
        }

        // For other types, return existing max_jawaban or default
        if (isset($soal->max_jawaban)) {
            return is_array($soal->max_jawaban) ? $soal->max_jawaban : [$soal->max_jawaban];
        }

        return [0];
    }

    /**
     * Parse kolom bank_kelas (mendukung JSON maupun PHP-serialize)
     * mengembalikan array of kelas_id.
     */
    private function parseBankKelas($bankKelas): array
    {
        if (empty($bankKelas)) {
            return [];
        }
        if (is_array($bankKelas)) {
            return $bankKelas;
        }
        $decoded = json_decode($bankKelas, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        $unserialized = @unserialize($bankKelas);
        if ($unserialized !== false && is_array($unserialized)) {
            return $unserialized;
        }
        return [];
    }

    /**
     * Log perubahan jawaban ke audit trail (tabel cbt_jawaban_audit).
     * Dipanggil hanya saat nilai berubah (bukan insert pertama) agar
     * overhead tulis minimal pada lonjakan 1000 siswa.
     *
     * @param int $siswaId
     * @param int $jadwalId
     * @param int $soalId
     * @param string $action INSERT atau UPDATE
     * @param string|null $oldJawaban
     * @param string|null $newJawaban
     */
    private function logJawabanChange(int $siswaId, int $jadwalId, int $soalId, string $action, ?string $oldJawaban, ?string $newJawaban): void
    {
        try {
            (new \App\Models\CbtAuditModel())->logJawabanChange(
                $siswaId, $jadwalId, $soalId, $action, $oldJawaban, $newJawaban
            );
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan proses utama
            log_message('error', '[logJawabanChange] Failed: ' . $e->getMessage());
        }
    }

    /**
     * Halaman hasil ujian dengan tombol remedial.
     * OPTIMASI: cekEligibleRemedial sekarang menerima idBank sehingga
     * tidak ada query jadwal tambahan; getSoalDenganStatus hanya dipanggil
     * sekali karena cekEligibleRemedial sudah memanggil getSoalDenganStatus internally.
     */
    public function hasilUjian($token = null)
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return redirect()->to('login');
        }

        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return redirect()->to('login');
        }

        $idJadwal = 0;
        if ($token) {
            try {
                $idJadwal = (int) decrypt_id_token($token);
            } catch (\Exception $e) {
                return redirect()->to('cbt')->with('error', 'Token tidak valid');
            }
        }

        $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
        if (!$jadwal) {
            return redirect()->to('cbt')->with('error', 'Jadwal tidak ditemukan');
        }

        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if (!$cbtSiswa) {
            return redirect()->to('cbt')->with('error', 'Anda belum mengikuti ujian ini');
        }

        $remedialEnabled = $jadwal->enable_remedial ?? '1';
        $kkm             = (float) ($jadwal->kkm ?? 70.0);
        $maxRemedial     = (int) ($jadwal->max_remedial ?? 2);
        $remedialAttempt = (int) ($cbtSiswa->remedial_attempt ?? 0);
        // sisa percobaan: 0 = unlimited
        $sisaRemedial = $maxRemedial > 0 ? max(0, $maxRemedial - $remedialAttempt) : null;
        // habis jika dibatasi (maxRemedial>0) dan attempt sudah mencapai batas
        $remedialExhausted = $maxRemedial > 0 && $remedialAttempt >= $maxRemedial;

        // ── Satu panggilan: cekEligibleRemedial kini memakai getSoalDenganStatus
        //    (1 JOIN query) dan menerima idBank untuk skip extra query ─────────
        $hasil = $this->cbtSiswaModel->cekEligibleRemedial(
            $siswa->id_siswa, $idJadwal, $kkm, (int) $jadwal->id_bank
        );

        // soalDenganStatus sudah dihitung di dalam cekEligibleRemedial via
        // getSoalDenganStatus — tidak perlu panggil lagi
        $soalDenganStatus = [];
        if ($hasil['eligible'] && $remedialEnabled === '1') {
            // Susun dari data yang sudah ada di hasil
            $soalDenganStatus = array_merge($hasil['soal_salah'], $hasil['soal_benar']);
            // Sort by id_soal untuk urutan konsisten
            usort($soalDenganStatus, fn($a, $b) => (int)$a['id_soal'] <=> (int)$b['id_soal']);
        }

        $data = [
            'judul'            => 'Hasil Ujian',
            'subjudul'         => ($jadwal->nama_jenis ?? '') . ' - ' . ($jadwal->nama_mapel ?? ''),
            'siswa'            => $siswa,
            'jadwal'           => $jadwal,
            'cbt_siswa'        => $cbtSiswa,
            'hasil'            => $hasil,
            'remedial_enabled' => $remedialEnabled === '1',
            'remedial_exhausted' => $remedialExhausted,
            'sisa_remedial'    => $sisaRemedial,
            'max_remedial'     => $maxRemedial,
            'soal_status'      => $soalDenganStatus,
            'token'            => $token,
            'setting'          => $this->dashboardModel->getSetting(),
        ];

        return view('members/siswa/cbt/hasil', $data);
    }

    // ==========================================
    // REMEDIAL FUNCTIONS
    // ==========================================

    /**
     * Cek apakah siswa eligible untuk remedial
     */
    public function cekRemedial()
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->jsonError('Session expired');
        }

        $idJadwal = (int) $this->request->getPost('jadwal');
        if (!$idJadwal) {
            return $this->jsonError('Jadwal tidak valid');
        }

        // Get jadwal untuk KKM
        $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
        $kkm = (float) ($jadwal->kkm ?? 70.0);

        // Info batas remedial
        $maxRemedial = (int) ($jadwal->max_remedial ?? 2);
        $cbtSiswaChk = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        $attempt     = (int) ($cbtSiswaChk->remedial_attempt ?? 0);
        $sisa        = $maxRemedial > 0 ? max(0, $maxRemedial - $attempt) : null;
        $exhausted   = $maxRemedial > 0 && $attempt >= $maxRemedial;

        // Cek eligible remedial
        $hasil = $this->cbtSiswaModel->cekEligibleRemedial($siswa->id_siswa, $idJadwal, $kkm);

        return $this->response->setJSON([
            'status' => true,
            'eligible' => $hasil['eligible'],
            'nilai' => $hasil['nilai'],
            'kkm' => $hasil['kkm'],
            'jumlah_salah' => $hasil['jumlah_salah'],
            'jumlah_benar' => $hasil['jumlah_benar'],
            'total_soal' => $hasil['total_soal'],
            'max_remedial' => $maxRemedial,
            'remedial_attempt' => $attempt,
            'sisa_remedial' => $sisa,
            'remedial_exhausted' => $exhausted,
            'message' => $hasil['eligible'] 
                ? 'Anda dapat mengikuti remedial untuk memperbaiki jawaban yang salah' 
                : 'Nilai Anda sudah memenuhi KKM (' . $kkm . ')'
        ]);
    }

    /**
     * Mulai sesi remedial
     */
    public function mulaiRemedial()
    {
        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->jsonError('Session expired');
        }

        $idJadwal = (int) $this->request->getPost('jadwal');
        if (!$idJadwal) {
            return $this->jsonError('Jadwal tidak valid');
        }

        // Get cbt_siswa record
        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if (!$cbtSiswa) {
            return $this->jsonError('Data ujian tidak ditemukan');
        }

        // Get jadwal untuk KKM
        $jadwal = $this->cbtService->getJadwalComplete($idJadwal);
        $kkm = (float) ($jadwal->kkm ?? 70.0);

        // Cek batas percobaan remedial
        $maxRemedial = (int) ($jadwal->max_remedial ?? 2);
        $attempt     = (int) ($cbtSiswa->remedial_attempt ?? 0);
        if ($maxRemedial > 0 && $attempt >= $maxRemedial) {
            return $this->jsonError(
                "Batas maksimal remedial ({$maxRemedial}x) telah tercapai. Anda tidak dapat mengikuti remedial lagi."
            );
        }

        // Cek eligible dulu
        $hasil = $this->cbtSiswaModel->cekEligibleRemedial($siswa->id_siswa, $idJadwal, $kkm);
        if (!$hasil['eligible']) {
            return $this->jsonError('Anda tidak eligible untuk remedial (nilai sudah ≥ KKM)');
        }

        try {
            // Start remedial session
            $this->cbtSiswaModel->startRemedial($cbtSiswa->id_cbt_siswa);

            // Set session flag
            session()->set('remedial_mode', true);
            session()->set('remedial_jadwal', $idJadwal);

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Sesi remedial dimulai',
                'max_remedial' => $maxRemedial,
                'remedial_attempt' => $attempt + 1,
                'sisa_remedial' => ($maxRemedial > 0 ? max(0, $maxRemedial - ($attempt + 1)) : null),
                'redirect' => site_url('cbt/penilaian/' . encrypt_id_token($idJadwal) . '?remedial=1')
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'CBT: Gagal mulai remedial. ' . $e->getMessage());
            return $this->jsonError('Gagal memulai remedial: ' . $e->getMessage());
        }
    }

    /**
     * Get soal dengan status benar/salah untuk remedial (AJAX)
     */
    public function getSoalRemedial()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->jsonError('Session expired');
        }

        $idJadwal = (int) $this->request->getPost('jadwal');
        $idBank = (int) $this->request->getPost('bank');

        if (!$idJadwal || !$idBank) {
            return $this->jsonError('Parameter tidak lengkap');
        }

        // Get soal dengan status
        $soalDenganStatus = $this->cbtSiswaModel->getSoalDenganStatus(
            $siswa->id_siswa, 
            $idJadwal, 
            $idBank
        );

        // Store original status in session for consistent validation during remedial
        $originalStatus = [];
        foreach ($soalDenganStatus as $s) {
            $originalStatus[$s['id_soal']] = [
                'is_benar' => $s['is_benar'],
                'is_salah' => $s['is_salah']
            ];
        }
        session()->set('remedial_soal_status', $originalStatus);

        return $this->response->setJSON([
            'status' => true,
            'soal' => $soalDenganStatus,
            'total' => count($soalDenganStatus),
            'total_salah' => count(array_filter($soalDenganStatus, fn($s) => $s['is_salah'])),
            'total_benar' => count(array_filter($soalDenganStatus, fn($s) => $s['is_benar']))
        ]);
    }

    /**
     * Simpan jawaban remedial — hanya update soal yang salah.
     * OPTIMASI: hapus debug log + ganti SELECT+INSERT/UPDATE dengan upsert.
     */
    public function saveJawabanRemedial()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->jsonError('Session expired');
        }

        // Terima parameter baik langsung maupun dalam data[]
        $postData = $this->request->getPost('data');
        if ($postData && is_array($postData)) {
            $idJadwal = (int) ($postData['jadwal']         ?? $this->request->getPost('jadwal')   ?? 0);
            $idSoal   = (int) ($postData['id_soal']        ?? $this->request->getPost('id_soal')  ?? 0);
            $jawaban  = $postData['jawaban_siswa'] ?? $postData['jawaban'] ?? $this->request->getPost('jawaban') ?? null;
        } else {
            $idJadwal = (int) ($this->request->getPost('jadwal')   ?? 0);
            $idSoal   = (int) ($this->request->getPost('id_soal')  ?? 0);
            $jawaban  = $this->request->getPost('jawaban');
        }

        if (!$idJadwal || !$idSoal) {
            return $this->jsonError('Parameter tidak valid');
        }

        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if ($cbtSiswa && ($cbtSiswa->status ?? '0') == '9') {
            return $this->response->setJSON([
                'status'  => false,
                'error'   => 'blocked',
                'message' => 'Anda telah diblokir oleh pengawas! Anda tidak dapat melanjutkan ujian sampai blokir dibuka. Silakan hubungi pengawas.'
            ]);
        }

        // Hanya boleh simpan saat sesi remedial AKTIF untuk jadwal ini.
        // Mencegah endpoint ini disalahgunakan di luar sesi remedial
        // (mis. mengubah jawaban setelah ujian selesai).
        if (!session()->get('remedial_mode') || (int) session()->get('remedial_jadwal') !== $idJadwal) {
            return $this->jsonError('Sesi remedial tidak aktif untuk jadwal ini');
        }

        // Tolak jika ujian/remedial sudah selesai (selesai tidak kosong).
        if ($cbtSiswa && !empty($cbtSiswa->selesai)) {
            return $this->response->setJSON([
                'status'  => false,
                'error'   => 'finished',
                'message' => 'Ujian/remedial sudah selesai. Jawaban tidak dapat disimpan.'
            ]);
        }

        // ── SERVER-SIDE TIMER VALIDATION (paritas dengan saveJawaban) ──
        // Cegah siswa yang memanipulasi timer di sisi client. Waktu dihitung
        // sejak mulai remedial (field mulai di-reset oleh startRemedial).
        $jadwalTimer = $this->cbtService->getJadwalComplete($idJadwal);
        if ($jadwalTimer && $cbtSiswa && !empty($cbtSiswa->mulai)) {
            $durasiDetik = (int) ($jadwalTimer->durasi_ujian ?? 60) * 60;
            $extraDetik  = (int) ($cbtSiswa->reset_waktu ?? 0) * 60;
            $totalDetik  = $durasiDetik + $extraDetik + 60; // +60 toleransi
            $elapsed     = time() - strtotime($cbtSiswa->mulai);

            if ($elapsed > $totalDetik) {
                $this->cbtSiswaModel->update($cbtSiswa->id_cbt_siswa, [
                    'selesai' => date('Y-m-d H:i:s'),
                    'status'  => '2',
                ]);
                return $this->response->setJSON([
                    'status'  => false,
                    'error'   => 'finished',
                    'message' => 'Waktu remedial telah habis.',
                    'expired' => true,
                ]);
            }
        }

        // Normalkan jawaban array → JSON
        if (is_array($jawaban)) {
            $jawaban = json_encode($jawaban);
        }

        // ── Validasi: soal ini boleh diedit (tidak boleh yang sudah benar) ──
        $originalStatus = session()->get('remedial_soal_status');

        if ($originalStatus && isset($originalStatus[$idSoal])) {
            if ($originalStatus[$idSoal]['is_benar']) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Soal ini sudah dijawab dengan benar dan tidak dapat diubah.',
                ]);
            }
        } else {
            // Fallback: cek DB langsung (tanpa session snapshot)
            $jadwalFb = $this->cbtService->getJadwalComplete($idJadwal);
            if (!$jadwalFb) {
                return $this->jsonError('Jadwal tidak ditemukan');
            }

            $soalStatus = $this->cbtSiswaModel->getSoalDenganStatus(
                $siswa->id_siswa, $idJadwal, (int) $jadwalFb->id_bank
            );

            $target = null;
            foreach ($soalStatus as $s) {
                if ((int) $s['id_soal'] === $idSoal) {
                    $target = $s;
                    break;
                }
            }

            if (!$target) {
                return $this->jsonError('Soal tidak ditemukan');
            }

            if ($target['is_benar']) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Soal ini sudah dijawab dengan benar dan tidak dapat diubah.',
                ]);
            }
        }

        // ── Simpan jawaban dengan upsert — 1 query, atomik ──────────────────
        $db = \Config\Database::connect();
        $db->query(
            "INSERT INTO cbt_jawaban (id_siswa, id_jadwal, id_soal, jawaban, waktu)
             VALUES (?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE jawaban = VALUES(jawaban), waktu = VALUES(waktu)",
            [$siswa->id_siswa, $idJadwal, $idSoal, $jawaban]
        );

        // Invalidasi cache jawaban
        $this->cbtService->invalidateJawabanCache($siswa->id_siswa, $idJadwal);

        return $this->response->setJSON([
            'status'      => true,
            'message'     => 'Jawaban tersimpan',
            'is_remedial' => true,
            'soal_status' => 'salah_diubah',
        ]);
    }

    /**
     * Selesai remedial — hitung nilai akhir.
     * OPTIMASI: scoring async + hapus cekEligibleRemedial yang tidak perlu.
     */
    public function selesaiRemedial()
    {
        if (!$this->request->isAJAX()) {
            return $this->jsonError('Invalid request');
        }

        if (!$this->isLoggedIn() || !$this->inGroup('siswa')) {
            return $this->jsonError('Unauthorized', 401);
        }

        $siswa = $this->cbtService->getCurrentSiswa();
        if (!$siswa) {
            return $this->jsonError('Session expired');
        }

        $idJadwal = (int) $this->request->getPost('jadwal');
        if (!$idJadwal) {
            return $this->jsonError('Jadwal tidak valid');
        }

        $cbtSiswa = $this->cbtSiswaModel->getCbtSiswa($siswa->id_siswa, $idJadwal);
        if (!$cbtSiswa) {
            return $this->jsonError('Data ujian tidak ditemukan');
        }

        // Tandai selesai
        $this->cbtSiswaModel->update($cbtSiswa->id_cbt_siswa, [
            'selesai' => date('Y-m-d H:i:s'),
            'status'  => '2',
        ]);

        // Hitung nilai: enqueue ke antrian (skala besar aman).
        // Fallback ke hitung sinkron bila enqueue gagal.
        $enqueued = $this->cbtService->enqueueScoring((int) $siswa->id_siswa, $idJadwal);
        if (!$enqueued) {
            try {
                $this->cbtService->calculateScore((int) $siswa->id_siswa, $idJadwal);
            } catch (\Throwable $e) {
                log_message('error', "[selesaiRemedial] fallback hitung nilai gagal: " . $e->getMessage());
            }
        }

        // Hitung ulang ringkasan hasil untuk respon (konsisten dgn tampilan hasil)
        $jadwalResp = $this->cbtService->getJadwalComplete($idJadwal);
        $kkmResp    = (float) ($jadwalResp->kkm ?? 70.0);
        $hasilResp  = $this->cbtSiswaModel->cekEligibleRemedial(
            $siswa->id_siswa, $idJadwal, $kkmResp, (int) ($jadwalResp->id_bank ?? 0)
        );

        // Bersihkan session remedial
        session()->remove('remedial_mode');
        session()->remove('remedial_jadwal');
        session()->remove('remedial_soal_status');

        // ── Tidak perlu query cekEligibleRemedial di sini ──
        // Nilai dihitung async; client cukup redirect ke halaman hasil
        return $this->response->setJSON([
            'status'        => true,
            'message'       => 'Remedial selesai. Nilai sedang dihitung.',
            'nilai_akhir'   => $hasilResp['nilai'] ?? 0,
            'jumlah_benar'  => $hasilResp['jumlah_benar'] ?? 0,
            'total_soal'    => $hasilResp['total_soal'] ?? 0,
            'redirect'      => site_url('cbt/hasil/' . encrypt_id_token($idJadwal)),
        ]);
    }
}
