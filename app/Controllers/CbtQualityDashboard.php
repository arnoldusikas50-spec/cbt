<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\MasterModel;
use App\Models\CbtModel;
use App\Models\CapaianPembelajaranModel;
use App\Services\ItemAnalysisService;
use App\Services\BloomValidatorService;
use App\Services\DuplicateDetectorService;

/**
 * CbtQualityDashboard
 *
 * Dashboard monitoring kualitas bank soal — Sprint 3.
 * Hanya dapat diakses oleh admin dan panitia.
 *
 * Fitur:
 *  - Ringkasan agregat kualitas bank soal sekolah
 *  - Analisis item (p, D, tag) per bank soal
 *  - Laporan duplikasi soal lintas bank
 *  - Manajemen master Capaian Pembelajaran (CP) BSKAP
 */
class CbtQualityDashboard extends BaseController
{
    protected DashboardModel       $dashboard;
    protected MasterModel          $master;
    protected CbtModel             $cbt;
    protected CapaianPembelajaranModel $cpModel;
    protected ItemAnalysisService      $itemAnalysis;
    protected BloomValidatorService    $bloom;
    protected DuplicateDetectorService $duplikat;

    public function __construct()
    {
        $this->dashboard    = new DashboardModel();
        $this->master       = new MasterModel();
        $this->cbt          = new CbtModel();
        $this->cpModel      = new CapaianPembelajaranModel();
        $this->itemAnalysis = new ItemAnalysisService();
        $this->bloom        = new BloomValidatorService();
        $this->duplikat     = new DuplicateDetectorService();
    }

    // ================================================================
    // HALAMAN UTAMA
    // ================================================================

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $data = [
            'user'       => $this->getUser(),
            'judul'      => 'Dashboard Kualitas Bank Soal',
            'subjudul'   => 'Monitoring & Analisis',
            'setting'    => $this->dashboard->getSetting(),
            'tp_active'  => $tp,
            'smt_active' => $smt,
            'tipe_ujian' => $this->bloom->getTipeUjian(),
        ];

        return view('cbt/quality/dashboard', $data);
    }

    // ================================================================
    // HALAMAN ITEM ANALYSIS (VIEW)
    // ================================================================

    /**
     * GET /cbt-quality/item-analysis-view/{id_bank}
     * Halaman full view item analysis satu bank soal.
     */
    public function itemAnalysisView(int $bankId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $bank = $this->cbt->getBankSoalById($bankId);
        if (!$bank) {
            return redirect()->to('cbt-quality')
                ->with('error', 'Bank soal tidak ditemukan.');
        }

        $data = [
            'user'      => $this->getUser(),
            'judul'     => 'Item Analysis',
            'subjudul'  => 'Bank Soal: ' . ($bank->bank_kode ?? '-'),
            'setting'   => $this->dashboard->getSetting(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt_active'=> $this->dashboard->getSemesterActive(),
            'bank'      => $bank,
        ];

        return view('cbt/quality/item_analysis', $data);
    }

    // ================================================================
    // HALAMAN LAPORAN DUPLIKASI (VIEW)
    // ================================================================

    /**
     * GET /cbt-quality/duplikasi
     * Halaman laporan duplikasi soal lintas bank.
     */
    public function duplikasi()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Ambil semua bank soal periode aktif untuk dropdown rehash
        $db    = \Config\Database::connect();
        $banks = $db->table('cbt_bank_soal b')
            ->select('b.id_bank, b.bank_kode, m.nama_mapel')
            ->join('master_mapel m', 'm.id_mapel = b.bank_mapel_id', 'left')
            ->where('b.id_tp', $tp->id_tp)
            ->where('b.id_smt', $smt->id_smt)
            ->orderBy('m.nama_mapel', 'ASC')
            ->get()->getResult();

        // Hitung soal yang belum punya hash
        $tanpaHash = $db->table('cbt_soal s')
            ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id')
            ->where('b.id_tp', $tp->id_tp)
            ->where('b.id_smt', $smt->id_smt)
            ->where('s.soal_hash IS NULL')
            ->countAllResults();

        $data = [
            'user'        => $this->getUser(),
            'judul'       => 'Laporan Duplikasi Soal',
            'subjudul'    => 'Deteksi soal identik lintas bank',
            'setting'     => $this->dashboard->getSetting(),
            'tp_active'   => $tp,
            'smt_active'  => $smt,
            'banks'       => $banks,
            'tanpa_hash'  => $tanpaHash,
        ];

        return view('cbt/quality/duplikasi', $data);
    }

    // ================================================================
    // API: RINGKASAN AGREGAT
    // ================================================================

    /**
     * GET /cbt-quality/summary
     * Ringkasan kualitas seluruh bank soal periode aktif.
     */
    public function summary()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $db  = \Config\Database::connect();

        // Guard: periode wajib ada
        if (!$tp || !$smt) {
            return $this->respond(false, 'Tahun/Semester aktif belum diatur.');
        }

        // ----------------------------------------------------------
        // [1] Statistik bank soal
        // ----------------------------------------------------------
        $banks = null;
        try {
            $q = $db->table('cbt_bank_soal b')
                ->select('
                    COUNT(*) as total_bank,
                    SUM(CASE WHEN b.status = 1 THEN 1 ELSE 0 END) as bank_siap,
                    SUM(CASE WHEN b.kartu_soal_id IS NOT NULL THEN 1 ELSE 0 END) as bank_berkisi
                ')
                ->where('b.id_tp', $tp->id_tp)
                ->where('b.id_smt', $smt->id_smt)
                ->get();
            $banks = $q ? $q->getRow() : null;
        } catch (\Throwable $e) {
            log_message('error', '[CbtQuality::summary] banks: ' . $e->getMessage());
        }

        // ----------------------------------------------------------
        // [2] Statistik kartu soal
        // ----------------------------------------------------------
        $kartus = null;
        try {
            $q = $db->table('cbt_kartu_soal k')
                ->select('
                    COUNT(*) as total_kartu,
                    SUM(CASE WHEN k.workflow_status = "approved" THEN 1 ELSE 0 END) as kartu_approved,
                    SUM(CASE WHEN k.workflow_status = "submitted" THEN 1 ELSE 0 END) as kartu_pending,
                    SUM(CASE WHEN k.workflow_status IN ("draft","revisi") THEN 1 ELSE 0 END) as kartu_draft
                ')
                ->where('k.tp_id', $tp->id_tp)
                ->where('k.smt_id', $smt->id_smt)
                ->where('k.deleted_at', null)
                ->get();
            $kartus = $q ? $q->getRow() : null;
        } catch (\Throwable $e) {
            log_message('error', '[CbtQuality::summary] kartus: ' . $e->getMessage());
        }

        // ----------------------------------------------------------
        // [3] Distribusi Bloom agregat
        // ----------------------------------------------------------
        $bloomDist = [];
        $bloomMap  = [];
        $hotsPct   = 0;
        $totalItem = 0;
        try {
            $q = $db->table('cbt_kartu_soal_item i')
                ->select('i.level_kognitif, COUNT(*) as jumlah')
                ->join('cbt_kartu_soal k', 'k.id_kartu = i.kartu_id')
                ->where('k.tp_id', $tp->id_tp)
                ->where('k.smt_id', $smt->id_smt)
                ->where('k.deleted_at', null)
                ->where('i.level_kognitif IS NOT NULL')
                ->groupBy('i.level_kognitif')
                ->orderBy('i.level_kognitif', 'ASC')
                ->get();
            $bloomDist = $q ? $q->getResult() : [];

            $totalItem = array_sum(array_column($bloomDist, 'jumlah'));
            foreach ($bloomDist as $b) {
                $bloomMap[$b->level_kognitif] = (int) $b->jumlah;
            }
            if ($totalItem > 0) {
                $hotsCount = ($bloomMap['C4'] ?? 0) + ($bloomMap['C5'] ?? 0) + ($bloomMap['C6'] ?? 0);
                $hotsPct   = round($hotsCount / $totalItem * 100, 1);
            }
        } catch (\Throwable $e) {
            log_message('error', '[CbtQuality::summary] bloom: ' . $e->getMessage());
        }

        // ----------------------------------------------------------
        // [4] Guru yang belum input TP
        // ----------------------------------------------------------
        $guruBelumTp = [];
        try {
            $guruQuery = $db->query("
                SELECT DISTINCT g.id_guru, g.nama_guru
                FROM guru_mapel_kelas gmk
                JOIN master_guru g ON g.id_guru = gmk.id_guru
                WHERE gmk.id_tp = ? AND gmk.id_smt = ?
                  AND g.id_guru NOT IN (
                      SELECT DISTINCT id_guru FROM e_tujuan_pembelajaran
                      WHERE id_tp = ? AND id_smt = ?
                  )
                ORDER BY g.nama_guru ASC
                LIMIT 30
            ", [$tp->id_tp, $smt->id_smt, $tp->id_tp, $smt->id_smt]);
            $guruBelumTp = $guruQuery ? $guruQuery->getResult() : [];
        } catch (\Throwable $e) {
            log_message('error', '[CbtQuality::summary] guru_tp: ' . $e->getMessage());
        }

        // ----------------------------------------------------------
        // [5] Soal kualitas rendah — hanya jika kolom stat_d sudah ada
        // ----------------------------------------------------------
        $soalPerluRevisi = [];
        try {
            if ($db->fieldExists('stat_d', 'cbt_soal')) {
                $q = $db->table('cbt_soal s')
                    ->select('s.id_soal, s.nomor_soal, s.stat_p, s.stat_d,
                              s.tag_kesulitan, b.bank_kode, b.id_bank, m.nama_mapel')
                    ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id')
                    ->join('master_mapel m',  'm.id_mapel = b.bank_mapel_id', 'left')
                    ->where('b.id_tp', $tp->id_tp)
                    ->where('b.id_smt', $smt->id_smt)
                    ->where('s.stat_d IS NOT NULL')
                    ->where('s.stat_d <', 0.20)
                    ->orderBy('s.stat_d', 'ASC')
                    ->limit(20)
                    ->get();
                $soalPerluRevisi = $q ? $q->getResult() : [];
            }
        } catch (\Throwable $e) {
            log_message('error', '[CbtQuality::summary] soal_revisi: ' . $e->getMessage());
        }

        // ----------------------------------------------------------
        // [6] Total CP master
        // ----------------------------------------------------------
        $totalCp = 0;
        try {
            if ($db->tableExists('e_capaian_pembelajaran')) {
                $totalCp = $this->cpModel->countAllResults();
            }
        } catch (\Throwable $e) {
            // tabel belum ada — bukan error kritis
        }

        return $this->respond(true, 'OK', [
            'periode'          => [
                'tp'  => $tp->tahun   ?? '-',
                'smt' => $smt->nama_smt ?? '-',
            ],
            'banks'            => $banks,
            'kartus'           => $kartus,
            'bloom_distribusi' => $bloomDist,
            'bloom_map'        => $bloomMap,
            'total_item_kisi'  => $totalItem,
            'hots_pct'         => $hotsPct,
            'guru_belum_tp'    => $guruBelumTp,
            'soal_perlu_revisi'=> $soalPerluRevisi,
            'total_cp_master'  => $totalCp,
        ]);
    }

    // ================================================================
    // API: ITEM ANALYSIS PER BANK
    // ================================================================

    /**
     * GET /cbt-quality/item-analysis/{id_bank}
     * Laporan item analysis untuk satu bank soal.
     */
    public function itemAnalysisBank(int $bankId)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $bank = $this->cbt->getBankSoalById($bankId);
        if (!$bank) {
            return $this->respond(false, 'Bank soal tidak ditemukan', null, null, 404);
        }

        $db = \Config\Database::connect();

        // Kolom stat_* mungkin belum ada sebelum migrasi dijalankan
        $statKolomAda = $db->fieldExists('stat_d', 'cbt_soal');

        $laporan   = $statKolomAda ? $this->itemAnalysis->getLaporanBank($bankId)   : [];
        $ringkasan = $statKolomAda ? $this->itemAnalysis->getRingkasanBank($bankId) : (object) [];

        return $this->respond(true, 'OK', [
            'bank'           => $bank,
            'ringkasan'      => $ringkasan,
            'soals'          => $laporan,
            'stat_available' => $statKolomAda,
        ]);
    }

    /**
     * POST /cbt-quality/run-analysis/{id_bank}
     * Jalankan item analysis untuk bank soal tertentu (dari jadwal terakhirnya).
     */
    public function runAnalysis(int $bankId)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $db      = \Config\Database::connect();
        $jadwal  = $db->table('cbt_jadwal')
            ->select('id_jadwal')
            ->where('id_bank', $bankId)
            ->orderBy('id_jadwal', 'DESC')
            ->limit(1)
            ->get()->getRow();

        if (!$jadwal) {
            return $this->respond(false, 'Belum ada jadwal ujian yang menggunakan bank soal ini.');
        }

        $result = $this->itemAnalysis->hitungMassal((int) $jadwal->id_jadwal);

        return $this->respond(
            !isset($result['error']),
            $result['error'] ?? "Analisis selesai: {$result['processed']} soal diproses.",
            $result
        );
    }

    // ================================================================
    // API: DUPLIKASI
    // ================================================================

    /**
     * GET /cbt-quality/laporan-duplikasi
     */
    public function laporanDuplikasi()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $laporan = $this->duplikat->laporanDuplikasi(
            (int) $tp->id_tp,
            (int) $smt->id_smt
        );

        return $this->respond(true, 'OK', [
            'total_duplikat' => count($laporan),
            'laporan'        => $laporan,
        ]);
    }

    /**
     * POST /cbt-quality/rehash-bank/{id_bank}
     * Isi ulang soal_hash untuk satu bank soal.
     */
    public function rehashBank(int $bankId)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $count = $this->duplikat->rehashBank($bankId);
        return $this->respond(true, "{$count} soal diperbarui hashnya.", ['updated' => $count]);
    }

    /**
     * POST /cbt-quality/rehash-missing
     * Isi hash untuk semua soal yang belum punya hash.
     */
    public function rehashMissing()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $limit = (int) ($this->request->getPost('limit') ?? 500);
        $count = $this->duplikat->rehashMissing($limit);
        return $this->respond(true, "{$count} soal diperbarui hashnya.", ['updated' => $count]);
    }

    // ================================================================
    // MANAJEMEN CAPAIAN PEMBELAJARAN (CP MASTER)
    // ================================================================

    /**
     * GET /cbt-quality/capaian-pembelajaran
     * Halaman manajemen master CP.
     */
    public function capaianPembelajaran()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $db = \Config\Database::connect();

        // Auto-migrate: buat tabel CP jika belum ada agar halaman tidak crash
        if (!$db->tableExists('e_capaian_pembelajaran')) {
            try {
                \Config\Services::migrations()->run();
            } catch (\Throwable $e) {
                log_message('warning', '[CbtQuality] Auto-migrate gagal: ' . $e->getMessage());
            }
        }

        // Jika tabel masih belum ada setelah migrate, tampilkan halaman kosong dgn pesan
        $tabelAda = $db->tableExists('e_capaian_pembelajaran');

        $q     = trim((string) ($this->request->getGet('q')      ?? ''));
        $fase  = trim((string) ($this->request->getGet('fase')   ?? ''));
        $mapel = trim((string) ($this->request->getGet('mapel')  ?? ''));

        $cpList  = [];
        $pager   = null;
        $stats   = [];

        if ($tabelAda) {
            $builder = $this->cpModel->where('is_active >=', 0); // semua

            if ($q !== '') {
                $builder->groupStart()
                    ->like('kode_cp', $q)
                    ->orLike('mata_pelajaran', $q)
                    ->orLike('elemen', $q)
                    ->orLike('deskripsi_cp', $q)
                    ->groupEnd();
            }
            if ($fase !== '') {
                $builder->where('fase', strtoupper($fase));
            }
            if ($mapel !== '') {
                $builder->like('mata_pelajaran', $mapel);
            }

            $perPage = 30;
            try {
                $cpList = $builder->orderBy('fase', 'ASC')
                                  ->orderBy('mata_pelajaran', 'ASC')
                                  ->orderBy('elemen', 'ASC')
                                  ->paginate($perPage);
                $pager  = $this->cpModel->pager;
                $stats  = $this->cpModel->getStats();
            } catch (\Throwable $e) {
                log_message('error', '[CbtQuality::capaianPembelajaran] ' . $e->getMessage());
            }
        }

        $data = [
            'user'        => $this->getUser(),
            'judul'       => 'Master Capaian Pembelajaran',
            'subjudul'    => 'BSKAP — Kurikulum Merdeka',
            'setting'     => $this->dashboard->getSetting(),
            'tp_active'   => $this->dashboard->getTahunActive(),
            'smt_active'  => $this->dashboard->getSemesterActive(),
            'cp_list'     => $cpList,
            'pager'       => $pager,
            'stats'       => $stats,
            'tabel_ada'   => $tabelAda,
            'q'           => $q,
            'fase'        => $fase,
            'mapel'       => $mapel,
            'fase_options'=> ['D' => 'D (SMP)', 'E' => 'E (SMA X)', 'F' => 'F (SMA XI-XII)'],
        ];

        return view('cbt/quality/capaian_pembelajaran', $data);
    }

    /**
     * POST /cbt-quality/capaian-pembelajaran/save
     * Tambah atau edit satu data CP.
     */
    public function saveCapaian()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $rules = [
            'kode_cp'        => 'required|max_length[30]',
            'mata_pelajaran' => 'required|max_length[100]',
            'fase'           => 'required|in_list[A,B,C,D,E,F]',
            'elemen'         => 'required|max_length[150]',
            'deskripsi_cp'   => 'required',
            'jenjang'        => 'required|in_list[1,2,3,4]',
        ];

        if (!$this->validate($rules)) {
            return $this->respond(false, 'Validasi gagal', null, $this->validator->getErrors(), 422);
        }

        $id     = (int) ($this->request->getPost('id_cp') ?? 0);
        $kode   = trim((string) $this->request->getPost('kode_cp'));

        // Cek duplikat kode (kecuali saat edit)
        $existing = $this->cpModel->where('kode_cp', $kode)->first();
        if ($existing && (int) $existing->id_cp !== $id) {
            return $this->respond(false, "Kode CP '{$kode}' sudah ada.", null,
                ['kode_cp' => 'Kode CP sudah terdaftar'], 422);
        }

        $payload = [
            'kode_cp'        => $kode,
            'mata_pelajaran' => trim((string) $this->request->getPost('mata_pelajaran')),
            'fase'           => strtoupper(trim((string) $this->request->getPost('fase'))),
            'elemen'         => trim((string) $this->request->getPost('elemen')),
            'deskripsi_cp'   => trim((string) $this->request->getPost('deskripsi_cp')),
            'jenjang'        => (int) $this->request->getPost('jenjang'),
            'kelompok_mapel' => trim((string) ($this->request->getPost('kelompok_mapel') ?? '')),
            'is_active'      => (int) ($this->request->getPost('is_active') ?? 1),
        ];

        if ($id > 0) {
            $ok = $this->cpModel->update($id, $payload);
            $pesan = 'Data CP berhasil diperbarui.';
        } else {
            $ok = (bool) $this->cpModel->insert($payload);
            $pesan = 'Data CP berhasil ditambahkan.';
        }

        return $this->respond($ok, $ok ? $pesan : 'Gagal menyimpan data CP.');
    }

    /**
     * POST /cbt-quality/capaian-pembelajaran/delete
     */
    public function deleteCapaian()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Unauthenticated', null, null, 401);
        }

        $id = (int) ($this->request->getPost('id_cp') ?? 0);
        if ($id <= 0) {
            return $this->respond(false, 'ID tidak valid.', null, null, 422);
        }

        // Cek apakah CP digunakan di TP
        $used = \Config\Database::connect()->table('e_tujuan_pembelajaran')
            ->join('e_capaian_pembelajaran', 'e_capaian_pembelajaran.kode_cp = e_tujuan_pembelajaran.kode_cp')
            ->where('e_capaian_pembelajaran.id_cp', $id)
            ->countAllResults();

        if ($used > 0) {
            return $this->respond(false,
                "CP ini digunakan oleh {$used} Tujuan Pembelajaran dan tidak dapat dihapus. " .
                "Nonaktifkan saja jika tidak lagi diperlukan.", null, null, 422);
        }

        $ok = $this->cpModel->delete($id);
        return $this->respond((bool) $ok, $ok ? 'CP berhasil dihapus.' : 'Gagal menghapus CP.');
    }

    /**
     * POST /cbt-quality/capaian-pembelajaran/seed
     * Isi data sampel CP BSKAP jika tabel masih kosong.
     */
    public function seedCapaian()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return $this->respond(false, 'Akses ditolak.', null, null, 403);
        }

        $count = $this->cpModel->seedDefault();

        if ($count === 0) {
            return $this->respond(false,
                'Tabel sudah berisi data CP. Seed hanya berjalan pada tabel kosong.');
        }

        return $this->respond(true,
            "{$count} data CP sampel berhasil ditambahkan. " .
            "Silakan import data lengkap dari dokumen resmi BSKAP.",
            ['seeded' => $count]);
    }
}
