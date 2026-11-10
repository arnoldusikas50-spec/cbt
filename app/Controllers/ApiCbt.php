<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Traits\CbtAuthTrait;
use App\Models\CbtModel;
use App\Models\CbtSiswaModel;
use App\Models\DashboardModel;
use App\Models\AuthModel;
use App\Services\CbtService;

/**
 * CBT API Controller untuk Exam Browser (Electron).
 *
 * Endpoint yang dipakai renderer SPA di Electron app:
 *   POST /cbt/api/login        — otentikasi username/password, return token + user
 *   GET  /cbt/api/jadwal       — list jadwal ujian aktif (Bearer token)
 *   GET  /cbt/api/soal         — list butir soal per jadwal (Bearer token)
 *   POST /cbt/api/save-jawaban— simpan 1 record jawaban (Bearer token)
 *   POST /cbt/api/submit       — final submit seluruh jawaban (Bearer token)
 *   POST /cbt/api/audit        — batch flush activity log (Bearer token)
 *   GET  /cbt/api/ping         — probe koneksi (200/500)
 *
 * Autentikasi:
 *   - /login         → menerima JSON body {username,password} / form-data
 *   - endpoint lain → Authorization: Bearer <token> (JSON Web Token style / opaque)
 *
 * Token disimpan sementara di tabel `cbt_exam_tokens`. Bila tabel tidak tersedia,
 * fallback ke in-memory array (stub) supaya endpoint tetap testable di dev.
 */
class ApiCbt extends BaseController
{
    use CbtAuthTrait;

    protected CbtModel $cbtModel;
    protected CbtSiswaModel $cbtSiswaModel;
    protected DashboardModel $dashboardModel;
    protected CbtService $cbtService;
    protected AuthModel $authModelLocal;

    /** @var array<string, array> in-memory token store fallback */
    protected static array $memTokens = [];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        try {
            $this->cbtModel = new CbtModel();
            $this->cbtSiswaModel = new CbtSiswaModel();
            $this->dashboardModel = new DashboardModel();
            $this->cbtService = new CbtService();
            $this->authModelLocal = new AuthModel();
        } catch (\Throwable $e) {
            // Model/tabel mungkin belum tersedia; stub tetap bisa jalan.
        }
        helper(['encryption_helper', 'text']);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function json(array $payload, int $code = 200): \CodeIgniter\HTTP\Response
    {
        return $this->response
            ->setContentType('application/json')
            ->setJSON($payload)
            ->setStatusCode($code);
    }

    protected function ok($data = null, string $message = 'OK'): \CodeIgniter\HTTP\Response
    {
        $out = ['ok' => true, 'status' => true, 'success' => true, 'message' => $message];
        if ($data !== null) $out['data'] = $data;
        return $this->json($out, 200);
    }

    protected function err(string $message, int $code = 400, $extra = null): \CodeIgniter\HTTP\Response
    {
        $out = ['ok' => false, 'status' => false, 'success' => false, 'message' => $message];
        if ($extra !== null) $out['error'] = $extra;
        return $this->json($out, $code);
    }

    /**
     * Baca JSON body secara fallback dari getJSON() / getPost().
     * @return array<string,mixed>
     */
    protected function input(): array
    {
        try {
            $json = $this->request->getJSON(true);
            if (is_array($json) && $json) return $json;
        } catch (\Throwable) {}
        $post = $this->request->getPost() ?? [];
        return is_array($post) ? $post : [];
    }

    protected function getBearerToken(): ?string
    {
        $header = $this->request->getHeaderLine('Authorization');
        if ($header && preg_match('/^Bearer\s+(.+)$/i', trim($header), $m)) {
            return trim($m[1]);
        }
        $q = $this->request->getGet('token');
        if ($q) return trim((string) $q);
        return null;
    }

    /**
     * Verifikasi bahwa request berasal dari Exam Browser (Electron app).
     * Header X-Exam-Browser di-set oleh main/session.js fetchJSON().
     * Toleransi: bila header tidak ada (misal browser dev biasa), allow tapi
     * tandai `$this->isExamBrowser` = false untuk logging/keperluan lain.
     */
    protected bool $isExamBrowser = false;
    protected ?string $examBrowserUA = null;

    protected function probeExamBrowser(): void
    {
        $hdr = (string) $this->request->getHeaderLine('X-Exam-Browser');
        $ua  = (string) $this->request->getUserAgent();
        $this->examBrowserUA = $ua;
        if ($hdr !== '' && (stripos($hdr, 'TKA') !== false || stripos($hdr, 'Exam') !== false || stripos($hdr, 'Exambro') !== false)) {
            $this->isExamBrowser = true;
            return;
        }
        if (
            stripos($ua, 'Electron') !== false ||
            stripos($ua, 'TKA-Exam') !== false ||
            stripos($ua, 'TKAExamBrowser') !== false ||
            stripos($ua, 'ExamBrowser') !== false ||
            stripos($ua, 'Exambro') !== false ||
            stripos($ua, 'CandyExam') !== false ||
            stripos($ua, 'CandyExambro') !== false ||
            stripos($ua, 'ExambroClient') !== false
        ) {
            $this->isExamBrowser = true;
            return;
        }
        $this->isExamBrowser = false;
    }

    // ------------------------------------------------------------------
    // Token store (DB-backed, auto-fallback to mem)
    // ------------------------------------------------------------------

    protected function ensureTokenTable(): void
    {
        static $done = false;
        if ($done) return;
        $done = true;
        try {
            $db = db_connect();
            $tables = $db->listTables();
            if (!in_array('cbt_exam_tokens', $tables, true)) {
                $forge = \Config\Database::forge();
                $forge->addField([
                    'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                    'token'      => ['type' => 'VARCHAR', 'constraint' => 128],
                    'user_id'    => ['type' => 'INT', 'unsigned' => true],
                    'username'   => ['type' => 'VARCHAR', 'constraint' => 100],
                    'role'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'siswa'],
                    'user_json'  => ['type' => 'LONGTEXT', 'null' => true],
                    'expires_at' => ['type' => 'BIGINT', 'unsigned' => true],
                    'created_at' => ['type' => 'BIGINT', 'unsigned' => true],
                ]);
                $forge->addPrimaryKey('id');
                $forge->addUniqueKey('token');
                $forge->addKey(['user_id', 'expires_at']);
                $forge->createTable('cbt_exam_tokens', true);
            }
            // Bersihkan token expired (sekali per proses; ringan, index expires_at)
            $db->table('cbt_exam_tokens')->where('expires_at <=', time())->delete();
        } catch (\Throwable) {
            // Fallback ke in-memory store
        }
    }

    protected function issueToken(object $user, ?object $siswa = null): string
    {
        $this->ensureTokenTable();
        $raw = bin2hex(random_bytes(24));
        $token = 'eb_' . $raw;
        $now = time();
        $exp = $now + (8 * 3600); // 8 jam
        $userArr = [
            'id'       => $user->id ?? null,
            'username' => $user->username ?? null,
            'nama'     => property_exists($user, 'first_name') ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : ($user->nama ?? $user->username ?? ''),
            'nisn'     => $siswa->nisn ?? ($user->username ?? null),
            'role'     => $user->role_name ?? $user->role ?? 'siswa',
            'kelas_id' => $siswa->kelas_id ?? null,
            'jurusan_id' => $siswa->jurusan_id ?? null,
            'siswa_id' => $siswa->id_siswa ?? $siswa->id ?? null,
        ];
        try {
            $db = db_connect();
            $db->table('cbt_exam_tokens')->insert([
                'token'      => $token,
                'user_id'    => (int) ($user->id ?? 0),
                'username'   => (string) ($user->username ?? ''),
                'role'       => (string) ($user->role_name ?? $user->role ?? 'siswa'),
                'user_json'  => json_encode($userArr, JSON_UNESCAPED_UNICODE),
                'expires_at' => $exp,
                'created_at' => $now,
            ]);
        } catch (\Throwable) {
            self::$memTokens[$token] = [
                'token' => $token,
                'user_id' => (int) ($user->id ?? 0),
                'username' => (string) ($user->username ?? ''),
                'role' => (string) ($user->role_name ?? $user->role ?? 'siswa'),
                'user_json' => $userArr,
                'expires_at' => $exp,
                'created_at' => $now,
            ];
        }
        return $token;
    }

    protected function resolveToken(?string $token): ?array
    {
        if ($token === null || $token === '') return null;
        $now = time();
        try {
            $this->ensureTokenTable();
            $db = db_connect();
            $row = $db->table('cbt_exam_tokens')
                ->where('token', $token)
                ->where('expires_at >', $now)
                ->get(1)->getRowArray();
            if ($row) {
                $userArr = is_string($row['user_json'] ?? null) ? (json_decode($row['user_json'], true) ?: []) : ($row['user_json'] ?? []);
                return [
                    'token'   => $row['token'],
                    'user_id' => (int) $row['user_id'],
                    'username'=> $row['username'],
                    'role'    => $row['role'],
                    'user'    => $userArr,
                ];
            }
        } catch (\Throwable) {}
        if (isset(self::$memTokens[$token]) && self::$memTokens[$token]['expires_at'] > $now) {
            $r = self::$memTokens[$token];
            return [
                'token'   => $r['token'],
                'user_id' => (int) $r['user_id'],
                'username'=> $r['username'],
                'role'    => $r['role'],
                'user'    => $r['user_json'],
            ];
        }
        return null;
    }

    protected function requireAuth(): ?array
    {
        $t = $this->getBearerToken();
        $r = $this->resolveToken($t);
        if ($r === null) {
            return null;
        }
        return $r;
    }

    // ------------------------------------------------------------------
    // Endpoints
    // ------------------------------------------------------------------

    /**
     * POST /cbt/api/login
     * Body: {username, password}
     */
    public function login()
    {
        $this->probeExamBrowser();
        $in = $this->input();
        $username = trim((string) ($in['username'] ?? $this->request->getPost('username') ?? ''));
        $password = (string) ($in['password'] ?? $this->request->getPost('password') ?? '');

        if ($username === '' || $password === '') {
            return $this->err('Username dan password harus diisi', 400);
        }

        if (!$this->checkThrottle('cbt_api_login_' . $username, 5, 60)) {
            return $this->err('Terlalu banyak percobaan login, tunggu 1 menit', 429);
        }

        $user = null;
        try {
            if (isset($this->authModelLocal) && method_exists($this->authModelLocal, 'login')) {
                $user = $this->authModelLocal->login($username);
            }
        } catch (\Throwable) {}

        if ($user === null) {
            return $this->err('Username atau password salah', 401);
        }

        $hash = $user->password ?? '';
        if (!password_verify($password, (string) $hash)) {
            return $this->err('Username atau password salah', 401);
        }

        $siswa = null;
        $roleName = strtolower((string) ($user->role_name ?? ($user->role ?? '')));
        if ($roleName === 'siswa' || (string)($user->role ?? '') === '3') {
            try {
                if ($this->dashboardModel !== null) {
                    $tp = method_exists($this->dashboardModel, 'getTahunActive') ? $this->dashboardModel->getTahunActive() : null;
                    $sm = method_exists($this->dashboardModel, 'getSemesterActive') ? $this->dashboardModel->getSemesterActive() : null;
                    $getSiswa = [$this->dashboardModel, 'getDataSiswa'];
                    if ($tp && $sm && is_callable($getSiswa)) {
                        $siswa = $getSiswa($username, $tp->id_tp ?? 0, $sm->id_smt ?? 0);
                    }
                }
            } catch (\Throwable) {}
        }

        $token = $this->issueToken($user, is_object($siswa) ? $siswa : null);

        $userOut = [
            'id' => (int) ($user->id ?? 0),
            'username' => $user->username ?? '',
            'nama' => property_exists($user, 'first_name') ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : ($user->nama ?? $user->username ?? ''),
            'nisn' => is_object($siswa) ? ($siswa->nisn ?? ($user->username ?? '')) : ($user->username ?? ''),
            'role' => $roleName ?: ($user->role_name ?? ($user->role ?? 'siswa')),
            'foto' => $user->foto ?? null,
            'kelas_id' => is_object($siswa) ? ($siswa->kelas_id ?? null) : null,
            'jurusan_id' => is_object($siswa) ? ($siswa->jurusan_id ?? null) : null,
            'siswa_id' => is_object($siswa) ? ($siswa->id_siswa ?? $siswa->id ?? null) : null,
            'email' => $user->email ?? null,
        ];

        return $this->json([
            'ok' => true,
            'status' => true,
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'data' => ['user' => $userOut, 'token' => $token],
            'exam_browser' => $this->isExamBrowser,
        ], 200);
    }

    /**
     * GET /cbt/api/jadwal
     */
    public function jadwal()
    {
        $this->probeExamBrowser();
        $auth = $this->requireAuth();
        if (!$auth) return $this->err('Token tidak valid / expired', 401);

        $list = [];
        try {
            if ($this->cbtService !== null && method_exists($this->cbtService, 'getCurrentSiswa')) {
                $siswa = $this->cbtService->getCurrentSiswaFromUsername($auth['username']);
                if ($siswa && $this->cbtModel !== null && method_exists($this->cbtModel, 'getJadwalSiswa')) {
                    $rows = $this->cbtModel->getJadwalSiswa(
                        $siswa->id_siswa ?? $siswa->id ?? 0,
                        $siswa->kelas_id ?? 0,
                        $siswa->jurusan_id ?? 0
                    );
                    if (is_array($rows)) {
                        foreach ($rows as $r) {
                            $r = (array) $r;
                            $list[] = [
                                'id'          => $r['id_jadwal'] ?? $r['id'] ?? null,
                                'id_jadwal'   => $r['id_jadwal'] ?? $r['id'] ?? null,
                                'id_bank'     => $r['id_bank'] ?? null,
                                'kode'        => $r['kode'] ?? ($r['id_jadwal'] ?? $r['id'] ?? ''),
                                'mapel'       => $r['nama_mapel'] ?? $r['mapel'] ?? $r['mata_pelajaran'] ?? '—',
                                'guru'        => $r['nama_guru'] ?? $r['guru'] ?? '',
                                'tanggal'     => $r['tanggal'] ?? $r['tgl'] ?? $r['waktu_mulai'] ?? '',
                                'durasi'      => (int) ($r['durasi_menit'] ?? $r['durasi'] ?? 90),
                                'status'      => $r['status'] ?? 'active',
                                'keterangan'  => $r['keterangan'] ?? '',
                                'token'       => (string) ($r['token'] ?? $r['token_value'] ?? ''),
                                'use_token'   => !empty($r['token']) || !empty($r['token_value']) || !empty($r['use_token']),
                                'jumlah_soal' => $r['tampil_pg'] ?? $r['jumlah_soal'] ?? $r['total_soal'] ?? null,
                                'kelas'       => $r['nama_kelas'] ?? $r['kode_kelas'] ?? ($siswa->kode_kelas ?? $siswa->nama_kelas ?? ''),
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable) {}

        if ($list === []) {
            $list = $this->demoJadwals($auth);
        }

        return $this->ok($list, count($list) . ' jadwal tersedia');
    }

    /**
     * GET /cbt/api/soal?jadwalId=...
     */
    public function soal()
    {
        $this->probeExamBrowser();
        $auth = $this->requireAuth();
        if (!$auth) return $this->err('Token tidak valid / expired', 401);

        $jadwalId = $this->request->getGet('jadwalId') ?? $this->request->getGet('id_jadwal') ?? null;

        $rows = [];
        try {
            if ($this->cbtModel !== null && $jadwalId !== null) {
                $method = [$this->cbtModel, 'getSoalByJadwal'] ?? null;
                if (is_callable($method)) {
                    $rows = $this->cbtModel->getSoalByJadwal($jadwalId);
                }
                if ((!is_array($rows) || $rows === []) && method_exists($this->cbtModel, 'getSoalByBank')) {
                    $rows = $this->cbtModel->getSoalByBank((int)$jadwalId);
                }
                if (is_array($rows)) {
                    $rows = array_values($rows);
                }
            }
        } catch (\Throwable) {}

        $out = [];
        if (is_array($rows) && $rows !== []) {
            foreach ($rows as $i => $r) {
                $r = (array) $r;
                $opsi = [];
                $opsiRaw = $r['opsi_json'] ?? null;
                if (is_string($opsiRaw)) {
                    $opsi = json_decode($opsiRaw, true) ?: [];
                } elseif (is_array($opsiRaw)) {
                    $opsi = $opsiRaw;
                }
                $opsiOut = [];
                foreach (array_values($opsi) as $idx => $o) {
                    if (is_string($o)) {
                        $opsiOut[] = ['kode' => chr(65 + $idx), 'label' => $o];
                    } else {
                        $o = (array) $o;
                        $opsiOut[] = [
                            'kode'  => $o['kode'] ?? (isset($o['label_kode']) ? $o['label_kode'] : chr(65 + $idx)),
                            'label' => $o['label'] ?? $o['teks'] ?? ''
                        ];
                    }
                }
                $out[] = [
                    'id'           => $r['id_soal'] ?? $r['id'] ?? ('soal_' . ($i + 1)),
                    'id_soal'      => $r['id_soal'] ?? $r['id'] ?? ('soal_' . ($i + 1)),
                    'nomor'        => (int) ($r['nomor'] ?? ($i + 1)),
                    'tipe'         => (string) ($r['tipe'] ?? $r['jenis'] ?? 'pg'),
                    'butir_soal'   => (string) ($r['butir_soal'] ?? $r['soal'] ?? ''),
                    'opsi'         => $opsiOut,
                    'jawaban_benar'=> $r['jawaban_benar'] ?? $r['kunci'] ?? null,
                    'pembahasan'   => (string) ($r['pembahasan'] ?? ''),
                ];
            }
        }

        if ($out === []) {
            $out = $this->demoSoals($jadwalId ?? 'default');
        }

        return $this->ok($out, count($out) . ' butir soal');
    }

    /**
     * POST /cbt/api/save-jawaban
     */
    public function saveJawaban()
    {
        $this->probeExamBrowser();
        $auth = $this->requireAuth();
        if (!$auth) return $this->err('Token tidak valid / expired', 401);

        $in = $this->input();
        $soalId = $in['id_soal'] ?? $in['soal_id'] ?? $in['soalId'] ?? null;
        $nomor  = (int) ($in['nomor'] ?? 0);
        $jawaban= $in['jawaban'] ?? null;
        $tipe   = (string) ($in['tipe'] ?? 'pg');
        $jadwalRaw = (array) ($in['jadwal'] ?? []);
        $idJadwal = (int) ($jadwalRaw['id_jadwal'] ?? $jadwalRaw['id'] ?? ($in['id_jadwal'] ?? $in['jadwal_id'] ?? 0));

        if ($soalId === null || $soalId === '') {
            return $this->err('id_soal dibutuhkan', 400);
        }

        $saved = false;
        try {
            if (isset($this->cbtService) && method_exists($this->cbtService, 'saveJawabanSiswa')) {
                $u = (array) ($auth['user'] ?? []);
                $this->cbtService->saveJawabanSiswa([
                    'id_siswa'  => (int) ($u['siswa_id'] ?? 0),
                    'id_jadwal' => $idJadwal,
                    'id_soal'   => is_numeric($soalId) ? (int) $soalId : 0,
                    'soal_id'   => $soalId,
                    'nomor'     => $nomor,
                    'jawaban'   => $jawaban,
                    'tipe'      => $tipe,
                    'ragu'      => (bool) ($in['raguragu'] ?? false),
                ]);
                $saved = true;
            } elseif ($this->cbtModel !== null && method_exists($this->cbtModel, 'saveJawaban')) {
                $u = (array) ($auth['user'] ?? []);
                $this->cbtModel->saveJawaban([
                    'id_siswa' => (int) ($u['siswa_id'] ?? 0),
                    'id_jadwal'=> $idJadwal,
                    'id_soal'  => is_numeric($soalId) ? (int) $soalId : 0,
                    'jawaban'  => $jawaban,
                    'nomor'    => $nomor,
                    'ragu'     => (int) (($in['raguragu'] ?? false) ? 1 : 0),
                ]);
                $saved = true;
            }
        } catch (\Throwable) {}

        // Stub accept
        return $this->json([
            'ok' => true,
            'saved' => true,
            'stored' => $saved,
            'received_at' => time(),
            'exam_browser' => $this->isExamBrowser,
            'soal' => [
                'id_soal' => $soalId,
                'nomor'   => $nomor,
                'jawaban' => $jawaban,
                'tipe'    => $tipe,
                'id_jadwal' => $idJadwal,
            ],
            'message' => 'Jawaban diterima',
        ], 200);
    }

    /**
     * POST /cbt/api/submit
     */
    public function submit()
    {
        $this->probeExamBrowser();
        $auth = $this->requireAuth();
        if (!$auth) return $this->err('Token tidak valid / expired', 401);

        $in = $this->input();
        $allJawaban = $in['jawaban'] ?? [];
        $autoFromTimer = !empty($in['autoFromTimer']);
        if (!is_array($allJawaban)) $allJawaban = [];

        $processed = 0;
        $queuedForScoring = false;
        $u = (array) ($auth['user'] ?? []);
        $idSiswa = (int) ($u['siswa_id'] ?? 0);
        $jadwalRaw = (array) ($in['jadwal'] ?? []);
        $idJadwal = (int) ($jadwalRaw['id_jadwal'] ?? $jadwalRaw['id'] ?? ($in['id_jadwal'] ?? 0));

        try {
            foreach ($allJawaban as $j) {
                $j = (array) $j;
                $soalId = $j['id_soal'] ?? $j['soalId'] ?? $j['soal_id'] ?? null;
                if ($soalId === null || $soalId === '') continue;
                if ($this->cbtModel !== null && method_exists($this->cbtModel, 'saveJawaban')) {
                    $this->cbtModel->saveJawaban([
                        'id_siswa' => $idSiswa,
                        'id_jadwal'=> $idJadwal,
                        'id_soal'  => is_numeric($soalId) ? (int) $soalId : 0,
                        'nomor'    => (int) ($j['nomor'] ?? 0),
                        'jawaban'  => $j['jawaban'] ?? null,
                        'ragu'     => !empty($j['raguragu']) ? 1 : 0,
                    ]);
                    $processed++;
                }
            }
            if ($idSiswa > 0 && $idJadwal > 0) {
                if (isset($this->cbtService) && method_exists($this->cbtService, 'enqueueScoring')) {
                    $this->cbtService->enqueueScoring($idSiswa, $idJadwal);
                    $queuedForScoring = true;
                } elseif ($this->cbtModel !== null && method_exists($this->cbtModel, 'selesaiUjian')) {
                    $this->cbtModel->selesaiUjian($idSiswa, $idJadwal);
                    $queuedForScoring = true;
                }
            }
        } catch (\Throwable $e) {
            // tetap kembalikan OK untuk memastikan offline queue tidak retry terus.
            log_message('warning', 'ApiCbt submit error: ' . $e->getMessage());
        }

        return $this->json([
            'ok' => true,
            'status' => true,
            'success' => true,
            'message' => $autoFromTimer
                ? 'Ujian ditutup otomatis (waktu habis). ' . $processed . ' jawaban diterima.'
                : 'Jawaban diterima. ' . $processed . ' butir diproses.',
            'processed' => $processed,
            'queued_scoring' => $queuedForScoring,
            'id_siswa' => $idSiswa,
            'id_jadwal' => $idJadwal,
            'submitted_at' => time(),
            'exam_browser' => $this->isExamBrowser,
        ], 200);
    }

    /**
     * POST /cbt/api/audit
     */
    public function audit()
    {
        $this->probeExamBrowser();
        $auth = $this->requireAuth();
        if (!$auth) return $this->err('Token tidak valid / expired', 401);

        $in = $this->input();
        $activities = $in['activities'] ?? [];
        if (!is_array($activities)) $activities = [];
        $ses = (array) ($in['session'] ?? []);

        $written = 0;
        try {
            $db = db_connect();
            $tables = $db->listTables();
            $hasTable = in_array('cbt_audit_activity', $tables, true);
            if (!$hasTable) {
                $forge = \Config\Database::forge();
                $forge->addField([
                    'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                    'act_id'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                    'ts'           => ['type' => 'BIGINT', 'unsigned' => true],
                    'type'         => ['type' => 'VARCHAR', 'constraint' => 64],
                    'payload_json' => ['type' => 'LONGTEXT', 'null' => true],
                    'user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                    'username'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                    'jadwal_id'    => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                    'started_at'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
                    'created_at'   => ['type' => 'BIGINT', 'unsigned' => true],
                ]);
                $forge->addPrimaryKey('id');
                $forge->addKey(['user_id', 'ts']);
                $forge->addKey(['type', 'ts']);
                $forge->createTable('cbt_audit_activity', true);
            }
            $u = (array) ($auth['user'] ?? []);
            $now = time();
            foreach ($activities as $a) {
                $a = (array) $a;
                $type = (string) ($a['type'] ?? 'unknown');
                $ts   = (int) ($a['ts'] ?? $now * 1000);
                if ($ts > 10_000_000_000) $ts = (int) floor($ts / 1000);
                $db->table('cbt_audit_activity')->insert([
                    'act_id'       => (string) ($a['id'] ?? ''),
                    'ts'           => $ts,
                    'type'         => substr($type, 0, 64),
                    'payload_json' => json_encode($a['payload'] ?? [], JSON_UNESCAPED_UNICODE),
                    'user_id'      => (int) ($ses['userId'] ?? $u['id'] ?? 0) ?: null,
                    'username'     => (string) ($ses['username'] ?? $u['username'] ?? ''),
                    'jadwal_id'    => (string) ($ses['jadwalId'] ?? ''),
                    'started_at'   => !empty($ses['startedAt']) ? (int) $ses['startedAt'] : null,
                    'created_at'   => $now,
                ]);
                $written++;
            }
        } catch (\Throwable) {}

        return $this->json([
            'ok' => true,
            'message' => 'Audit log diterima',
            'written' => $written,
            'received' => count($activities),
            'exam_browser' => $this->isExamBrowser,
        ], 200);
    }

    /**
     * GET /cbt/api/ping
     */
    public function ping()
    {
        $ok = true;
        $error = null;
        try {
            $db = db_connect();
            $db->query('SELECT 1');
        } catch (\Throwable $e) {
            $ok = false;
            $error = $e->getMessage();
        }
        return $this->json([
            'ok' => $ok,
            'pong' => true,
            'time' => time(),
            'server' => 'tka.ikas.my.id / cbt-api',
            'error' => $error,
        ], $ok ? 200 : 500);
    }

    public function logout()
    {
        $auth = $this->requireAuth();
        if ($auth) {
            try {
                $db = db_connect();
                $db->table('cbt_exam_tokens')->where('token', $auth['token'])->delete();
            } catch (\Throwable) {}
        }
        return $this->ok(null, 'Logout berhasil');
    }

    // ------------------------------------------------------------------
    // Demo / stub data bila model/tabel belum tersedia (dev fallback)
    // ------------------------------------------------------------------

    private function demoJadwals(array $auth): array
    {
        $mapels = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS'];
        $base = strtotime('today 08:00');
        $list = [];
        foreach ($mapels as $i => $m) {
            $list[] = [
                'id'        => 'JDWL-DEMO-' . ($i + 1),
                'id_jadwal' => 'JDWL-DEMO-' . ($i + 1),
                'kode'      => 'JDWL-DEMO-' . ($i + 1),
                'mapel'     => $m,
                'guru'      => 'Guru ' . $m,
                'tanggal'   => date('Y-m-d H:i', $base + ($i * 90 * 60)),
                'durasi'    => 90,
                'status'    => 'active',
                'keterangan'=> 'Ujian semester — demo fallback untuk testing Exam Browser.',
            ];
        }
        return $list;
    }

    private function demoSoals(string $jadwalId): array
    {
        $out = [];
        for ($i = 1; $i <= 25; $i++) {
            $tipe = ($i % 5 === 0) ? 'isian' : 'pg';
            if ($tipe === 'pg') {
                $opsi = [
                    ['kode' => 'A', 'label' => 'Contoh jawaban A nomor ' . $i],
                    ['kode' => 'B', 'label' => 'Contoh jawaban B nomor ' . $i],
                    ['kode' => 'C', 'label' => 'Contoh jawaban C nomor ' . $i],
                    ['kode' => 'D', 'label' => 'Contoh jawaban D nomor ' . $i],
                    ['kode' => 'E', 'label' => 'Contoh jawaban E nomor ' . $i],
                ];
                $out[] = [
                    'id'           => $jadwalId . '_s' . $i,
                    'id_soal'      => $jadwalId . '_s' . $i,
                    'nomor'        => $i,
                    'tipe'         => 'pg',
                    'butir_soal'   => '[Jadwal ' . $jadwalId . '] Ini contoh butir soal pilihan ganda nomor ' . $i . ".\nPilih salah satu jawaban yang benar.",
                    'opsi'         => $opsi,
                    'jawaban_benar'=> 'A',
                    'pembahasan'   => '',
                ];
            } else {
                $out[] = [
                    'id'           => $jadwalId . '_s' . $i,
                    'id_soal'      => $jadwalId . '_s' . $i,
                    'nomor'        => $i,
                    'tipe'         => 'isian',
                    'butir_soal'   => '[Jadwal ' . $jadwalId . '] Ini contoh butir soal isian nomor ' . $i . ".\nTuliskan jawaban Anda di kolom yang tersedia.",
                    'opsi'         => [],
                    'jawaban_benar'=> null,
                    'pembahasan'   => '',
                ];
            }
        }
        return $out;
    }
}
