<?php

namespace App\Controllers;

use App\Models\CbtKartuSoalAuditModel;
use App\Models\CbtKartuSoalItemModel;
use App\Models\CbtKartuSoalModel;
use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\LogModel;
use App\Models\MasterModel;
use App\Models\TujuanPembelajaranModel;
use App\Services\AIKisiKisiSekolahService;
use App\Services\AIService;
use App\Services\CbtService;
use Dompdf\Dompdf;
use Dompdf\Options;

class Cbtkartusoal extends BaseController
{
    protected DashboardModel $dashboard;
    protected LogModel $logging;
    protected MasterModel $master;
    protected CbtModel $cbt;
    protected CbtKartuSoalModel $kartu;
    protected CbtKartuSoalItemModel $items;
    protected CbtKartuSoalAuditModel $audit;
    protected TujuanPembelajaranModel $tujuan;
    protected AIService $ai;
    protected CbtService $cbtService;
    protected AIKisiKisiSekolahService $aiKisiKisiSekolah;

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
        $this->logging = new LogModel();
        $this->master = new MasterModel();
        $this->cbt = new CbtModel();
        $this->kartu = new CbtKartuSoalModel();
        $this->items = new CbtKartuSoalItemModel();
        $this->audit = new CbtKartuSoalAuditModel();
        $this->tujuan = new TujuanPembelajaranModel();
        $this->tujuan->initialize();
        $this->ai = new AIService();
        $this->cbtService = new CbtService();
        $this->aiKisiKisiSekolah = new AIKisiKisiSekolahService();
    }

    private function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        $hex = bin2hex($data);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    private function canManage(?object $kartu = null): bool
    {
        if ($this->isAdmin() || $this->isPanitia()) {
            return true;
        }
        if ($this->isGuru()) {
            if ($kartu === null) {
                return true;
            }
            return (int) ($kartu->created_by ?? 0) === (int) $this->getUserId();
        }
        return false;
    }

    private function isApprovalRole(): bool
    {
        return $this->isAdmin() || $this->isPanitia();
    }

    private function getWorkflowStatusValue(?object $kartu): string
    {
        $status = strtolower(trim((string) ($kartu->workflow_status ?? '')));
        if ($status === '') {
            return 'draft';
        }
        if (!in_array($status, ['draft', 'submitted', 'approved', 'revisi'], true)) {
            return 'draft';
        }
        return $status;
    }

    private function canEditKartuContent(?object $kartu): bool
    {
        if (!$kartu) {
            return false;
        }
        return in_array($this->getWorkflowStatusValue($kartu), ['draft', 'revisi'], true);
    }

    private function ensureEditableKartu(?object $kartu)
    {
        if ($this->canEditKartuContent($kartu)) {
            return null;
        }
        return $this->respond(
            false,
            'Kartu Soal sudah disubmit/approved. Kembalikan ke revisi terlebih dahulu sebelum mengubah isi.',
            ['workflow_status' => $this->getWorkflowStatusValue($kartu)],
            null,
            422
        );
    }

    private function ensureCanManage(?object $kartu = null)
    {
        if (!$this->canManage($kartu)) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }
        return null;
    }

    private function decodeJsonPayload($payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }
        if (!is_string($payload) || trim($payload) === '') {
            return [];
        }
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeHtml(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }
        $plain = trim(strip_tags($html));
        return $plain === '' ? '' : $html;
    }

    /**
     * Validasi isi kisi-kisi (blueprint) butir soal.
     * Standar kisi-kisi (BSNK / Permendikbudristek): setiap butir wajib memiliki
     * Indikator Soal (ber-KKO Bloom), Materi/Bahan Kajian, dan Level Kognitif C1-C6.
     */
    private function validateItemAnswerPayload(
        int $jenisSoal,
        string $butirSoal,
        array $opsiRaw,
        array $jawabanRaw,
        ?string $indikator = null,
        ?string $materi = null,
        ?string $levelKognitif = null
    ): array {
        $errors = [];

        $indikator = trim(strip_tags((string) ($indikator ?? '')));
        $materi    = trim((string) ($materi ?? ''));
        $level     = strtoupper(trim((string) ($levelKognitif ?? '')));

        if ($indikator === '') {
            $errors['indikator_soal'] = 'Indikator Soal wajib diisi (merujuk KKO Taksonomi Bloom).';
        } elseif (mb_strlen($indikator) < 10) {
            $errors['indikator_soal'] = 'Indikator Soal terlalu pendek, jelaskan kemampuan yang diukur.';
        }

        if ($materi === '') {
            $errors['materi'] = 'Materi / Bahan Kajian wajib diisi.';
        }

        if ($level === '') {
            $errors['level_kognitif'] = 'Level Kognitif (C1-C6) wajib dipilih.';
        } elseif (!preg_match('/^C[1-6]$/', $level)) {
            $errors['level_kognitif'] = 'Level Kognitif harus dalam rentang C1-C6.';
        }

        return [
            'ok'                  => empty($errors),
            'errors'              => $errors,
            'butir_soal'          => $butirSoal !== '' ? $butirSoal : null,
            'opsi_json'           => !empty($opsiRaw) ? json_encode($opsiRaw, JSON_UNESCAPED_UNICODE) : null,
            'jawaban_benar_json'  => !empty($jawabanRaw) ? json_encode($jawabanRaw, JSON_UNESCAPED_UNICODE) : null,
        ];
    }

    /**
     * Default Level Kognitif berdasarkan jenis soal (best-practice Taksonomi Bloom).
     */
    private function defaultLevelKognitif(int $jenisSoal): string
    {
        return [
            1 => 'C3', // Pilihan Ganda -> Menerapkan
            2 => 'C4', // PG Kompleks -> Menganalisis
            3 => 'C2', // Menjodohkan -> Memahami
            4 => 'C3', // Isian Singkat -> Menerapkan
            5 => 'C4', // Essay -> Menganalisis
            6 => 'C3', // Urutan -> Menerapkan
            7 => 'C3', // Cloze -> Menerapkan
            8 => 'C2', // True / False -> Memahami
        ][$jenisSoal] ?? 'C3';
    }

    /**
     * Normalisasi level kognitif agar hanya menyimpan nilai standar C1–C6
     * (fallback ke default sesuai jenis soal bila nilai invalid / kosong).
     */
    private function normalizeLevelKognitif(string $raw, int $jenisSoal): string
    {
        $val = strtoupper(trim($raw));
        if (!preg_match('/^C[1-6]$/', $val)) {
            return $this->defaultLevelKognitif($jenisSoal);
        }
        return $val;
    }

    /**
     * Default estimasi waktu (menit) berdasarkan jenis soal.
     */
    private function defaultEstimasiWaktu(int $jenisSoal): int
    {
        return [
            1 => 2,
            2 => 3,
            3 => 3,
            4 => 3,
            5 => 10,
            6 => 3,
            7 => 3,
            8 => 1,
        ][$jenisSoal] ?? 5;
    }

    private function getTujuanOptionsForKartu(object $kartu): array
    {
        $mapelId = (int) ($kartu->mapel_id ?? 0);
        $tpId = (int) ($kartu->tp_id ?? 0);
        $smtId = (int) ($kartu->smt_id ?? 0);

        $builder = $this->tujuan->where('id_mapel', $mapelId)
            ->where('is_active', 1);

        if ($tpId > 0) {
            $builder->where('id_tp', $tpId);
        }
        if ($smtId > 0) {
            $builder->where('id_smt', $smtId);
        }

        if ($this->isGuru()) {
            $guru = $this->getCurrentGuruProfile();
            if (!$guru) return [];
            $builder->where('id_guru', (int)$guru->id_guru);
        }

        return $builder->orderBy('tingkat', 'ASC')
            ->orderBy('kode', 'ASC')
            ->findAll();
    }

    /**
     * Periksa apakah nomor soal sudah dipakai di kartu yang sama (proteksi server-side).
     */
    private function isNomorTaken(int $kartuId, int $nomor, ?int $exceptItemId = null): bool
    {
        if ($nomor <= 0 || $kartuId <= 0) {
            return false;
        }
        $builder = $this->items
            ->where('kartu_id', $kartuId)
            ->where('nomor', $nomor);
        if ($exceptItemId !== null && $exceptItemId > 0) {
            $builder->where('id_item !=', $exceptItemId);
        }
        return $builder->countAllResults() > 0;
    }

    private function buildAiPromptForKartu(object $kartu, string $mapelNama, string $kelasNama, array $params, array $tujuanOptions): string
    {
        $jumlah    = (int) ($params['jumlah']    ?? 1);
        $kesulitan = strtoupper(trim((string) ($params['kesulitan'] ?? 'C4')));
        $jenis     = $params['jenis'] ?? ['1'];
        $instruksiTambahan = trim((string) ($params['instruksi'] ?? ''));

        $isMixed  = ($kesulitan === 'MIXED');
        $jenisLabel = implode(', ', array_map(static function ($v) {
            $map = [
                '1' => 'Pilihan Ganda',
                '2' => 'Pilihan Ganda Kompleks',
                '3' => 'Menjodohkan',
                '4' => 'Isian Singkat',
                '5' => 'Uraian / Essay',
                '6' => 'Urutan',
                '7' => 'Isian Rumpang',
            ];
            return $map[$v] ?? $v;
        }, $jenis));

        // Kartu dipakai bank sub-tes (bukan GENERAL) → suntik konteks sub-tes ke prompt
        $subtesKonteks = '';
        try {
            $bankRow = $this->cbt->db->table('cbt_bank_soal')
                ->select('subtes_code, subtes_nama')
                ->where('kartu_soal_id', (int) ($kartu->id_kartu ?? 0))
                ->where('subtes_code IS NOT NULL', null, false)
                ->where('subtes_code !=', 'GENERAL')
                ->where('deleted_at IS NULL', null, false)
                ->limit(1)
                ->get()
                ->getRow();
            if ($bankRow) {
                $subtesKode = strtoupper(trim((string) ($bankRow->subtes_code ?? '')));
                if ($subtesKode !== '' && $subtesKode !== 'GENERAL') {
                    $subtesNama = trim((string) ($bankRow->subtes_nama ?? ''));
                    $subtesKonteks = $subtesKode . ($subtesNama !== '' && $subtesNama !== $subtesKode ? ' — ' . $subtesNama : '');
                }
            }
        } catch (\Throwable $th) {
            log_message('warning', '[Cbtkartusoal::buildAiPromptForKartu] Gagal deteksi sub-tes: ' . $th->getMessage());
        }

        // Build TP list — rich context including fase, materi_pokok, KKTP
        $tujuanListText = '';
        foreach ($tujuanOptions as $tp) {
            $idTujuan = (int) ($tp->id_tujuan ?? 0);
            $kode     = (string) ($tp->kode     ?? '-');
            $fase     = (string) ($tp->fase     ?? '');
            $tujuanTeks = trim((string) ($tp->tujuan ?? ''));
            $kodeCp   = trim((string) ($tp->kode_cp ?? ''));
            $elemenCp = trim((string) ($tp->elemen_cp ?? ''));
            $materi   = trim((string) ($tp->materi_pokok ?? ''));
            $kktp     = trim((string) ($tp->kriteria_ketercapaian ?? ''));
            $levelKog = trim((string) ($tp->level_kognitif ?? ''));

            $line  = "- id_tujuan: {$idTujuan}";
            $line .= "; kode: {$kode}";
            if ($fase !== '')     $line .= "; fase: {$fase}";
            if ($kodeCp !== '')   $line .= "; kode_cp: {$kodeCp}";
            if ($elemenCp !== '') $line .= "; elemen_cp: {$elemenCp}";
            if ($materi !== '')   $line .= "; materi_pokok: {$materi}";
            if ($tujuanTeks !== '') $line .= "; tujuan_pengetahuan: {$tujuanTeks}";
            if (!empty($tp->tp_sikap)) {
                $line .= '; tujuan_sikap: ' . mb_substr(trim((string) $tp->tp_sikap), 0, 100);
            }
            if (!empty($tp->tp_keterampilan)) {
                $line .= '; tujuan_keterampilan: ' . mb_substr(trim((string) $tp->tp_keterampilan), 0, 100);
            }
            if ($kktp !== '') {
                $line .= '; kktp: ' . mb_substr($kktp, 0, 150);
            }
            if ($levelKog !== '') $line .= "; level_kognitif_rekomendasi: {$levelKog}";
            $tujuanListText .= $line . "\n";
        }

        $prompt  = "Anda adalah AI Assessment Specialist Kurikulum Nasional Indonesia.\n";
        $prompt .= "Versi: 2025 — Sesuai Permendikdasmen No. 13 Tahun 2025\n\n";
        $prompt .= "TUGAS: Buat {$jumlah} KISI-KISI SOAL (blueprint/kartu soal) berkualitas tinggi yang:\n";
        $prompt .= "1. Selaras dengan Capaian Pembelajaran (CP) dan Tujuan Pembelajaran (TP)\n";
        $prompt .= "2. Sesuai konteks kelas dan fase pembelajaran\n";
        $prompt .= "3. Menggunakan Kata Kerja Operasional (KKO) Taksonomi Bloom Revisi\n";
        $prompt .= "4. Setiap materi dan indikator UNIK dan tidak identik satu sama lain\n\n";

        $prompt .= "KONTEKS KARTU SOAL:\n";
        $prompt .= "- Mata Pelajaran : {$mapelNama}\n";
        $prompt .= "- Kelas / Tingkat: {$kelasNama}\n";
        $prompt .= "- Judul Ujian    : " . (string) ($kartu->judul ?? '-') . "\n";
        if ($isMixed) {
            $prompt .= "- Level Kognitif : Mixed — distribusikan secara variatif antara C2–C5\n";
        } else {
            $prompt .= "- Level Kognitif : Dominan {$kesulitan}, boleh 1 tingkat di atas/bawah\n";
        }
        $prompt .= "- Bentuk Soal    : {$jenisLabel}\n\n";

        $isEnglish = stripos($mapelNama, 'english') !== false || stripos($mapelNama, 'bahasa inggris') !== false;

        if ($isEnglish) {
            $prompt .= "LANGUAGE RULE: PENTING! MATAPELAJARAN BAHASA INGGRIS. SELURUH HASIL GENERASI (BAIK KISI-KISI, INDIKATOR, MAUPUN MATERI) WAJIB MENGGUNAKAN BAHASA INGGRIS BAKU.\n";
            $prompt .= "- Gunakan Bahasa Inggris formal sesuai standar nasional Kurikulum Merdeka Indonesia.\n";
            $prompt .= "- SEMUA KOMPONEN HARUS 100% DALAM BAHASA INGGRIS YANG BENAR DAN BAKU.\n";
            $prompt .= "- Gunakan British English atau American English yang konsisten, jangan dicampur.\n";
            $prompt .= "- Vocabulary dan grammar sesuai tingkat kelas {$kelasNama}.\n\n";
        }

        // Konteks sub-tes — disuntikkan bila kartu soal dipakai bank sub-tes
        if ($subtesKonteks !== '') {
            $prompt .= "KONTEKS SUB-TES:\n";
            $prompt .= "- Kartu soal ini digunakan untuk sub-tes: {$subtesKonteks}.\n";
            $prompt .= "- Kisi-kisi harus selaras dengan kompetensi akademik sub-tes tersebut (bukan sekedar ulangan biasa).\n";
            $prompt .= "- Materi/indikator mengarah ke stimulus kaya konteks (kasus/data/teks/grafik) dan pertanyaan analitis & penerapan, sesuai standar Asesmen Kompetensi.\n";
            $prompt .= "- Pastikan cakupan materi sesuai kompetensi yang diukur sub-tes terkait.\n\n";
        }

        $prompt .= "TUJUAN PEMBELAJARAN YANG HARUS DICAKUP (pilih id_tujuan yang paling sesuai untuk tiap soal):\n";
        $prompt .= $tujuanListText !== '' ? $tujuanListText : "- Tidak ada TP tersedia. Tetap generate tapi id_tujuan set ke 0.\n";

        if ($instruksiTambahan !== '') {
            $prompt .= "\nINSTRUKSI KHUSUS DARI GURU:\n{$instruksiTambahan}\n";
        }

        $prompt .= "\nFORMAT OUTPUT: JSON Array of Objects MURNI (tanpa markdown, tanpa penjelasan). Schema tiap object:\n";
        $prompt .= '[' . "\n";
        $prompt .= '  {' . "\n";
        $prompt .= '    "jenis_soal": ' . (count($jenis) === 1 ? $jenis[0] : implode(' atau ', $jenis)) . ',' . " // integer 1-7 yang merepresentasikan: {$jenisLabel}\n";
        $prompt .= '    "id_tujuan": 0,' . " // integer, WAJIB dari daftar TP di atas\n";
        $prompt .= '    "kode_cp": "",' . " // kode CP dari kolom kode_cp TP, kosongkan jika tidak ada\n";
        $prompt .= '    "elemen_cp": "",' . " // elemen CP dari TP, kosongkan jika tidak ada\n";
        $prompt .= '    "level_kognitif": "C4",' . " // C1-C6 Taksonomi Bloom Revisi\n";
        $prompt .= '    "materi": "Topik/Materi spesifik sesuai materi_pokok TP",' . "\n";
        $prompt .= '    "indikator_soal": "[KKO Bloom] + [konteks spesifik + kelas]. Harus operasional dan terukur.",' . "\n";
        $prompt .= '    "estimasi_waktu": 2' . " // menit per soal, sesuaikan dengan kompleksitas\n";
        $prompt .= '  }' . "\n";
        $prompt .= ']' . "\n\n";
        $prompt .= "ATURAN MUTLAK:\n";
        $prompt .= "- jenis_soal HARUS MENGGUNAKAN SALAH SATU ANGKA INI SAJA: " . implode(', ', $jenis) . " (Tidak boleh angka lain)\n";
        $prompt .= "- id_tujuan HARUS dari daftar di atas (jangan mengarang id baru)\n";
        $prompt .= "- Output berisi TEPAT {$jumlah} object (tidak lebih, tidak kurang)\n";
        $prompt .= "- Indikator soal HARUS diawali KKO Bloom (bukan 'Peserta didik dapat' saja)\n";
        $prompt .= "- materi dan indikator tiap soal HARUS berbeda-beda\n";
        $prompt .= "- Sesuaikan kompleksitas dengan tingkat kelas {$kelasNama}";
        $prompt .= "\n\nSTANDAR AKM & ASESMEN NASIONAL (WAJIB):\n";
        $prompt .= "- Indikator dirumuskan operasional & terukur, mengikuti proses kognitif AKM: L1 Menemukan/Memahami, L2 Menginterpretasi/Menerapkan, L3 Menalar/Mengevaluasi.\n";
        $prompt .= "- Sebisa mungkin gunakan stimulus kontekstual AKM: konteks personal, sosial-budaya (budaya Indonesia/daerah), atau saintifik.\n";
        $prompt .= "- Pilihan bentuk soal (jenis_soal) disesuaikan dengan kompetensi yang diukur — hindari memaksa semua butir ke soal hafalan.\n";
        $prompt .= "- Setiap butir dinyatakan dalam satu indikator tunggal (satu kompetensi per butir), bukan indikator ganda.";

        return $prompt;
    }

    private function checkGeneratedItemQuality(array $row): array
    {
        $errors = [];
        $plainMateri = trim(strip_tags((string)($row['materi'] ?? '')));
        if (mb_strlen($plainMateri) < 3) {
            $errors[] = 'Topik materi sangat kurang jelas.';
        }

        $plainIndikator = trim(strip_tags((string)($row['indikator_soal'] ?? '')));
        if (mb_strlen($plainIndikator) < 10) {
            $errors[] = 'Indikator soal terlalu singkat dan tidak deskriptif.';
        }

        return $errors;
    }

    private function normalizeGeneratedAiItems($parsed, array $allowedTujuanIds, int $defaultTujuanId, array $allowedJenis = []): array
    {
        $rows = [];
        if (is_array($parsed) && isset($parsed['items']) && is_array($parsed['items'])) {
            $rows = $parsed['items'];
        } elseif (is_array($parsed)) {
            $rows = $parsed;
        }

        $result = ['valid' => [], 'rejected' => []];
        $seen = [];
        foreach ($rows as $idx => $row) {
            if (!is_array($row)) {
                $result['rejected'][] = ['index' => $idx, 'reason' => 'Format item bukan objek'];
                continue;
            }

            $jenisRaw = strtolower(trim((string) ($row['jenis_soal'] ?? '1')));
            $jenisSoal = (int) $jenisRaw;
            if ($jenisSoal < 1 || $jenisSoal > 7) {
                $jenisSoal = 1;
            }
            if (!empty($allowedJenis) && !in_array((string)$jenisSoal, $allowedJenis, true)) {
                $jenisSoal = (int) $allowedJenis[0];
            }

            $idTujuan = (int) ($row['id_tujuan'] ?? 0);
            if ($idTujuan <= 0 || !in_array($idTujuan, $allowedTujuanIds, true)) {
                $idTujuan = $defaultTujuanId;
            }

            $estimasi = max(1, min(20, (int) ($row['estimasi_waktu'] ?? 2)));

            $qualityErrors = $this->checkGeneratedItemQuality($row);
            if (!empty($qualityErrors)) {
                $result['rejected'][] = ['index' => $idx, 'reason' => implode('; ', $qualityErrors)];
                continue;
            }

            $fingerprint = mb_strtolower(trim(strip_tags((string) ($row['indikator_soal'] ?? ''))));
            if ($fingerprint !== '' && isset($seen[$fingerprint])) {
                $result['rejected'][] = ['index' => $idx, 'reason' => 'Duplikat indikator soal terdeteksi'];
                continue;
            }
            $seen[$fingerprint] = true;

            // Sanitize & carry-forward field baru Kurikulum Merdeka dari AI output
            $kodeCp    = trim((string) ($row['kode_cp'] ?? ''));
            $elemenCp  = trim((string) ($row['elemen_cp'] ?? ''));
            // Validasi: kode_cp dan elemen_cp hanya diterima jika non-empty
            if (!preg_match('/^[A-Za-z0-9\-_\.]+$/', $kodeCp)) {
                $kodeCp = '';
            }

            $result['valid'][] = [
                'jenis_soal'         => $jenisSoal,
                'id_tujuan'          => $idTujuan,
                'estimasi_waktu'     => $estimasi,
                'materi'             => (string) ($row['materi'] ?? ''),
                'indikator_soal'     => (string) ($row['indikator_soal'] ?? ''),
                'level_kognitif'     => $this->normalizeLevelKognitif((string) ($row['level_kognitif'] ?? ''), $jenisSoal),
                'kode_cp'            => $kodeCp,         // Baru: dari AI
                'elemen_cp'          => $elemenCp,       // Baru: dari AI
                'butir_soal'         => '',
                'opsi_json'          => [],
                'jawaban_benar_json' => [],
                'pembahasan'         => '',
            ];
        }

        return $result;
    }

    private function getMapelForCurrentUser(?int $tpId = null, ?int $smtId = null): array
    {
        if ($tpId === null || $smtId === null) {
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $tpId = (int) ($tp->id_tp ?? 0);
            $smtId = (int) ($smt->id_smt ?? 0);
        }

        // Kode (singkatan) lookup dari master_mapel untuk judul otomatis
        $kodeLookup = [];
        foreach ($this->master->getAllMapel() as $mr) {
            $kodeLookup[(int) ($mr->id_mapel ?? 0)] = (string) ($mr->kode ?? '');
        }

        if ($this->isAdmin()) {
            $arrMapel = [];
            foreach ($this->master->getAllMapel() as $m) {
                $idMapel = (int) ($m->id_mapel ?? 0);
                $arrMapel[] = (object) [
                    'id_mapel' => $idMapel,
                    'nama_mapel' => (string) ($m->nama_mapel ?? ''),
                    'kode'     => $kodeLookup[$idMapel] ?? '',
                ];
            }
            return $arrMapel;
        }

        if ($tpId <= 0 || $smtId <= 0) {
            return [];
        }

        $user = $this->authModel->user();
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tpId, $smtId);
        if (!$guru) {
            return [];
        }

        $mapelGuru = $this->master->getGuruMapelKelas((int) $guru->id_guru, $tpId, $smtId);
        if (!$mapelGuru) {
            return [];
        }

        $mapel = $this->decodeMapelKelasPayload($mapelGuru->mapel_kelas ?? '[]');
        $arrMapel = [];
        if (is_array($mapel)) {
            foreach ($mapel as $m) {
                $idMapel = (int) $this->pickMixed($m, ['id_mapel'], 0);
                if ($idMapel <= 0) {
                    continue;
                }
                $arrMapel[] = (object) [
                    'id_mapel' => $idMapel,
                    'nama_mapel' => (string) $this->pickMixed($m, ['nama_mapel'], ''),
                    'kode'     => $kodeLookup[$idMapel] ?? '',
                ];
            }
        }
        return $arrMapel;
    }

    private function getKelasForCurrentUser(?int $tpId = null, ?int $smtId = null): array
    {
        if ($tpId === null || $smtId === null) {
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $tpId = (int) ($tp->id_tp ?? 0);
            $smtId = (int) ($smt->id_smt ?? 0);
        }

        if ($tpId <= 0 || $smtId <= 0) {
            return [];
        }

        if ($this->isGuru()) {
            $guru = $this->getCurrentGuruProfile($tpId, $smtId);
            if (!$guru) {
                return [];
            }
            $mapelGuru = $this->master->getGuruMapelKelas((int) $guru->id_guru, $tpId, $smtId);
            if (!$mapelGuru) {
                return [];
            }
            $mapelList = $this->decodeMapelKelasPayload($mapelGuru->mapel_kelas ?? '[]');
            $kelasIds = [];
            foreach ($mapelList as $mp) {
                $kelasMapel = $this->pickMixed($mp, ['kelas_mapel'], []);
                if (!is_array($kelasMapel)) {
                    continue;
                }
                foreach ($kelasMapel as $km) {
                    $kelasId = (int) $this->pickMixed($km, ['kelas', 'id_kelas'], 0);
                    if ($kelasId <= 0 && is_scalar($km) && is_numeric((string) $km)) {
                        $kelasId = (int) $km;
                    }
                    if ($kelasId > 0) {
                        $kelasIds[] = $kelasId;
                    }
                }
            }
            $kelasIds = array_values(array_unique($kelasIds));
            if (empty($kelasIds)) {
                return [];
            }
            return $this->master->getAllKelasByArrayId($tpId, $smtId, $kelasIds);
        }

        return $this->master->getAllKelas($tpId, $smtId);
    }

    private function getJenisUjianOptions(): array
    {
        // Tampilkan semua jenis ujian (aktif maupun nonaktif)
        return $this->cbt->getAllJenisUjian();
    }

    private function isValidJenisUjian(int $jenisId): bool
    {
        if ($jenisId <= 0) {
            return false;
        }

        $row = $this->cbt->getJenisUjianById($jenisId);
        return $row && (string) ($row->status ?? '1') === '1';
    }

    private function isValidTpSmt(int $tpId, int $smtId): bool
    {
        if ($tpId <= 0 || $smtId <= 0) {
            return false;
        }

        $tpValid = false;
        foreach ($this->dashboard->getTahun() as $tp) {
            if ((int) ($tp->id_tp ?? 0) === $tpId) {
                $tpValid = true;
                break;
            }
        }

        if (!$tpValid) {
            return false;
        }

        foreach ($this->dashboard->getSemester() as $smt) {
            if ((int) ($smt->id_smt ?? 0) === $smtId) {
                return true;
            }
        }

        return false;
    }

    private function isValidMapelKelasCombinationForUser(int $mapelId, int $kelasId, int $tpId, int $smtId): bool
    {
        return $this->cbtService->isValidMapelKelas(
            (int) $this->getUserId(),
            ($this->isAdmin() || $this->isPanitia()),
            $mapelId,
            $kelasId,
            $tpId,
            $smtId
        );
    }

    /**
     * Validasi lock tingkat kelas untuk kartu soal.
     * Hanya memperbolehkan level X (10), XI (11) dan XII (12).
     * Mengembalikan daftar kelas yang di luar tingkat yang diizinkan.
     */
    private function validateKelasTingkatLock(array $kelasIdArr, int $tpId, int $smtId): array
    {
        $allowedLevels = [10, 11, 12]; // X, XI, XII
        $invalid = [];
        if (empty($kelasIdArr)) {
            return $invalid;
        }
        $rows = $this->master->getAllKelasByArrayId($tpId, $smtId, $kelasIdArr);
        $foundIds = [];
        foreach ($rows as $r) {
            $idKelas = (int) ($r->id_kelas ?? 0);
            $foundIds[] = $idKelas;
            $level = (int) ($r->level_id ?? 0);
            if (!in_array($level, $allowedLevels, true)) {
                $invalid[] = [
                    'id_kelas'   => $idKelas,
                    'nama_kelas' => (string) ($r->nama_kelas ?? '-'),
                    'level'      => $level,
                ];
            }
        }
        // Kelas ID yang tidak ditemukan untuk periode ini juga ditolak (tidak valid).
        foreach ($kelasIdArr as $idKelas) {
            if (!in_array($idKelas, $foundIds, true)) {
                $invalid[] = [
                    'id_kelas'   => $idKelas,
                    'nama_kelas' => '-',
                    'level'      => 0,
                ];
            }
        }
        return $invalid;
    }

    private function getCurrentGuruProfile(?int $tpId = null, ?int $smtId = null): ?object
    {
        if (!$this->isGuru()) return null;
        if ($tpId === null || $smtId === null) {
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $tpId = (int) ($tp->id_tp ?? 0);
            $smtId = (int) ($smt->id_smt ?? 0);
        }
        return $this->cbtService->getGuruProfile((int) $this->getUserId(), $tpId, $smtId);
    }

    private function getTujuanByMapelForCurrentGuru(int $mapelId, ?int $tpId = null, ?int $smtId = null): array
    {
        if (!$this->isGuru() || $mapelId <= 0) return [];
        if ($tpId === null || $smtId === null) {
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();
            $tpId = (int) ($tp->id_tp ?? 0);
            $smtId = (int) ($smt->id_smt ?? 0);
        }
        $guru = $this->getCurrentGuruProfile($tpId, $smtId);
        if (!$guru) return [];
        return $this->cbtService->getTujuanByMapel((int) $guru->id_guru, $tpId, $smtId, $mapelId);
    }

    private function hasTujuanForMapel(int $mapelId, ?int $tpId = null, ?int $smtId = null): bool
    {
        return count($this->getTujuanByMapelForCurrentGuru($mapelId, $tpId, $smtId)) > 0;
    }

    /**
     * AJAX: Return kelas list for a given mapel (filtered by guru assignment).
     * GET cbtkartusoal/api/kelas-by-mapel?mapel_id=N
     */
    public function apiKelasByMapel(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
        }

        $mapelId = (int) ($this->request->getGet('mapel_id') ?? 0);
        $tp  = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tpId  = (int) ($tp->id_tp ?? 0);
        $smtId = (int) ($smt->id_smt ?? 0);

        if ($this->isAdmin()) {
            // Admin: return ALL kelas
            $allKelas = $this->master->getAllKelas($tpId, $smtId);
            return $this->response->setJSON(['status' => true, 'data' => $allKelas]);
        }

        if (!$this->isGuru() || $mapelId <= 0) {
            return $this->response->setJSON(['status' => true, 'data' => []]);
        }

        // Guru: return only kelas that are assigned to this guru for the given mapel
        $guru = $this->getCurrentGuruProfile($tpId, $smtId);
        if (!$guru) {
            return $this->response->setJSON(['status' => true, 'data' => []]);
        }

        $mapelGuru = $this->master->getGuruMapelKelas((int) $guru->id_guru, $tpId, $smtId);
        if (!$mapelGuru) {
            return $this->response->setJSON(['status' => true, 'data' => []]);
        }

        $mapelList = $this->decodeMapelKelasPayload($mapelGuru->mapel_kelas ?? '[]');
        $kelasIds  = [];

        foreach ($mapelList as $mp) {
            $assignedMapelId = (int) $this->pickMixed($mp, ['id_mapel', 'mapel', 'id'], 0);
            if ($assignedMapelId !== $mapelId) {
                continue;
            }
            $kelasMapel = $this->pickMixed($mp, ['kelas_mapel'], []);
            if (!is_array($kelasMapel)) {
                continue;
            }
            foreach ($kelasMapel as $km) {
                $kid = (int) $this->pickMixed($km, ['kelas', 'id_kelas'], 0);
                if ($kid <= 0 && is_scalar($km) && is_numeric((string) $km)) {
                    $kid = (int) $km;
                }
                if ($kid > 0) {
                    $kelasIds[] = $kid;
                }
            }
        }

        $kelasIds = array_values(array_unique($kelasIds));
        if (empty($kelasIds)) {
            return $this->response->setJSON(['status' => true, 'data' => []]);
        }

        $kelasList = $this->master->getAllKelasByArrayId($tpId, $smtId, $kelasIds);
        return $this->response->setJSON(['status' => true, 'data' => $kelasList]);
    }

    /**
     * Decode assignment payload from JSON or serialized value.
     */
    private function decodeMapelKelasPayload($raw): array
    {
        $decoded = $this->maybe_unserialize($raw);
        if (!is_array($decoded)) {
            return [];
        }
        return $decoded;
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $view = 'list'; // Forced to list view as per user request
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $mapelFilter = (int) ($this->request->getGet('mapel_id') ?? 0);
        $jenisFilter = (int) ($this->request->getGet('id_jenis') ?? 0);
        $tpFilter = (int) ($this->request->getGet('tp_id') ?? 0);
        $smtFilter = (int) ($this->request->getGet('smt_id') ?? 0);
        $statusFilter = strtolower(trim((string) ($this->request->getGet('status') ?? '')));
        $perPage = (int) ($this->request->getGet('per_page') ?? 15);
        if (!in_array($perPage, [10, 15, 20, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $builder = $this->kartu->select('cbt_kartu_soal.*')
            ->select('m.nama_mapel, k.nama_kelas, g.nama_guru, j.nama_jenis, t.tahun, s.nama_smt')
            ->select('(SELECT COUNT(*) FROM cbt_kartu_soal_item WHERE kartu_id = cbt_kartu_soal.id_kartu) as total_item')
            ->select('(SELECT COALESCE(SUM(estimasi_waktu), 0) FROM cbt_kartu_soal_item WHERE kartu_id = cbt_kartu_soal.id_kartu) as total_estimasi')
            ->select('(SELECT COUNT(*) FROM cbt_bank_soal WHERE kartu_soal_id = cbt_kartu_soal.id_kartu AND status = 1) as total_bank_dipakai')
            ->join('master_mapel m', 'cbt_kartu_soal.mapel_id = m.id_mapel', 'left')
            ->join('master_kelas k', 'cbt_kartu_soal.kelas_id = k.id_kelas', 'left')
            ->join('cbt_jenis j', 'cbt_kartu_soal.id_jenis = j.id_jenis', 'left')
            ->join('master_tp t', 'cbt_kartu_soal.tp_id = t.id_tp', 'left')
            ->join('master_smt s', 'cbt_kartu_soal.smt_id = s.id_smt', 'left')
            ->join('users u', 'cbt_kartu_soal.created_by = u.id', 'left')
            ->join('master_guru g', 'u.username = g.username', 'left');

        if ($this->isGuru()) {
            $builder = $builder->where('cbt_kartu_soal.created_by', (int) $this->getUserId());
        }
        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('cbt_kartu_soal.judul', $q)
                ->orLike('cbt_kartu_soal.deskripsi', $q)
                ->orLike('m.nama_mapel', $q)
                ->orLike('j.nama_jenis', $q)
                ->orLike('t.tahun', $q)
                ->orLike('s.nama_smt', $q)
                ->orLike('g.nama_guru', $q)
                ->groupEnd();
        }
        if ($mapelFilter > 0) {
            $builder = $builder->where('cbt_kartu_soal.mapel_id', $mapelFilter);
        }
        if ($jenisFilter > 0) {
            $builder = $builder->where('cbt_kartu_soal.id_jenis', $jenisFilter);
        }
        if ($tpFilter > 0) {
            $builder = $builder->where('cbt_kartu_soal.tp_id', $tpFilter);
        }
        if ($smtFilter > 0) {
            $builder = $builder->where('cbt_kartu_soal.smt_id', $smtFilter);
        }
        if ($statusFilter !== '') {
            if ($statusFilter === 'draft') {
                $builder = $builder->groupStart()
                    ->where('cbt_kartu_soal.workflow_status', 'draft')
                    ->orWhere('cbt_kartu_soal.workflow_status IS NULL')
                    ->groupEnd();
            } else {
                $builder = $builder->where('cbt_kartu_soal.workflow_status', $statusFilter);
            }
        }

        $cards = $builder->orderBy('cbt_kartu_soal.updated_at', 'DESC')->paginate($perPage);

        $data = [
            'user' => $user,
            'judul' => 'Kartu Soal',
            'subjudul' => 'Kartu Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'view' => $view,
            'q' => $q,
            'mapels' => $this->getMapelForCurrentUser(),
            'jenis_ujian' => $this->getJenisUjianOptions(),
            'tp_options' => $this->dashboard->getTahun(),
            'smt_options' => $this->dashboard->getSemester(),
            'filter_mapel_id' => $mapelFilter,
            'filter_jenis_id' => $jenisFilter,
            'filter_tp_id' => $tpFilter,
            'filter_smt_id' => $smtFilter,
            'filter_status' => $statusFilter,
            'per_page' => $perPage,
            'cards' => $cards,
            'pager' => $this->kartu->pager,
            'can_manage' => $this->canManage(),
        ];

        return view('cbt/kartusoal/index', $data);
    }

    /**
     * Kirim notifikasi Telegram (kalau dikonfigurasi) untuk perubahan status kartu.
     * Sengaja dibungkus try/catch: notifikasi tidak boleh menggagalkan aksi utama.
     */
    private function notifyTelegram(string $event, $kartu, string $catatan = ''): void
    {
        try {
            $telegram = new \App\Services\TelegramService();
            if (! $telegram->isEnabled()) {
                return;
            }

            $db = \Config\Database::connect();
            $mapel = $db->table('master_mapel')->select('nama_mapel')->where('id_mapel', (int) ($kartu->mapel_id ?? 0))->get()->getRow();
            $kelas = $db->table('master_kelas')->select('nama_kelas')->where('id_kelas', (int) ($kartu->kelas_id ?? 0))->get()->getRow();
            $guru  = $db->table('users')->select('first_name, last_name')->where('id', (int) ($kartu->created_by ?? 0))->get()->getRow();

            $namaGuru = $guru ? trim($guru->first_name . ' ' . $guru->last_name) : '-';
            $judul    = (string) ($kartu->judul ?? '-');
            $link     = base_url('cbtkartusoal/detail/' . ($kartu->uuid ?? ''));
            $waktu    = date('d/m/Y H:i');

            $header = [
                'submit'    => "📤 <b>Kartu Soal Diajukan</b>\nMenunggu review kurikulum/admin.",
                'approve'   => "✅ <b>Kartu Soal Disetujui</b>\nSiap dipakai membuat paket bank soal.",
                'revisi'    => "⚠️ <b>Kartu SoalPerlu Revisi</b>\nDikembalikan untuk diperbaiki oleh guru.",
                'cancel'    => "↩️ <b>Persetujuan Dibatalkan</b>\nKartu dikembalikan ke status Draft.",
            ][$event] ?? ('ℹ️ <b>Kartu Soal</b> — Status: ' . $event);

            $lines = [
                $header,
                '',
                '<b>Judul:</b> ' . htmlspecialchars($judul, ENT_QUOTES),
                '<b>Mapel:</b> ' . htmlspecialchars((string) ($mapel->nama_mapel ?? '-'), ENT_QUOTES),
                '<b>Kelas:</b> ' . htmlspecialchars((string) ($kelas->nama_kelas ?? '-'), ENT_QUOTES),
                '<b>Guru:</b> ' . htmlspecialchars($namaGuru, ENT_QUOTES),
            ];

            if ($catatan !== '') {
                $lines[] = '';
                $lines[] = '<b>Catatan:</b>';
                $lines[] = '<blockquote>' . htmlspecialchars($catatan, ENT_QUOTES) . '</blockquote>';
            }

            $lines[] = '';
            $lines[] = '<i>' . $waktu . '</i>';
            $lines[] = '<a href="' . $link . '">Buka Kartu Soal</a>';

            $res = $telegram->send(implode("\n", $lines));

            if (! $res['ok']) {
                log_message('warning', '[Telegram] Notifikasi kartu gagal: ' . $res['message']);
            }
        } catch (\Throwable $e) {
            log_message('error', '[Telegram] Exception notifikasi kartu: ' . $e->getMessage());
        }
    }

    /**
     * Catatan revisi terakhir sebuah kartu: isi catatan, nama reviewer, dan
     * waktunya.
     *
     * Saat kartu dikembalikan, kolom approval_note/approved_by di cbt_kartu_soal
     * sengaja dikosongkan (kartu memang belum "disetujui"), sehingga sumber
     * riwayat penolakan ada di cbt_kartu_soal_audit. Kolom approval_note tetap
     * dipakai sebagai fallback untuk data lama.
     */
    private function getLatestRevisionInfo(int $kartuId): array
    {
        $empty = ['note' => '', 'by' => '', 'at' => ''];
        if ($kartuId <= 0) {
            return $empty;
        }

        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('cbt_kartu_soal_audit')) {
                return $empty;
            }

            $row = $db->table('cbt_kartu_soal_audit a')
                ->select('a.detail, a.waktu, u.first_name, u.last_name')
                ->join('users u', 'a.user_id = u.id', 'left')
                ->where('a.kartu_id', $kartuId)
                ->where('a.aksi', 'return_revision')
                ->orderBy('a.id_audit', 'DESC')
                ->limit(1)
                ->get()
                ->getRow();
        } catch (\Throwable $e) {
            log_message('error', 'getLatestRevisionInfo: ' . $e->getMessage());

            return $empty;
        }

        if (! $row) {
            return $empty;
        }

        $detail = json_decode((string) ($row->detail ?? ''), true);

        return [
            'note' => trim((string) (is_array($detail) ? ($detail['note'] ?? '') : '')),
            'by'   => trim(((string) ($row->first_name ?? '')) . ' ' . ((string) ($row->last_name ?? ''))),
            'at'   => (string) ($row->waktu ?? ''),
        ];
    }

    /**
     * Daftar paket/bank soal yang dibuat dari kartu ini (kartu_soal_id).
     * Dipakai utk modal "Lihat Soal semua paket" & tab paket di preview soa.
     */
    private function getPaketBanksForKartu(int $kartuId): array
    {
        if ($kartuId <= 0) {
            return [];
        }
        $db = \Config\Database::connect();
        if (!$db->tableExists('cbt_bank_soal')) {
            return [];
        }
        $rows = $db->table('cbt_bank_soal b')
            ->select('b.id_bank, b.bank_kode, b.bank_nama, b.subtes_code, b.subtes_nama,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE bank_id = b.id_bank) AS total_soal,
                (SELECT COUNT(id_soal) FROM cbt_soal WHERE bank_id = b.id_bank AND tampilkan = 1) AS total_tampil')
            ->where('b.kartu_soal_id', $kartuId)
            ->where('b.status', 1)
            ->orderBy('b.bank_kode', 'ASC')
            ->get()
            ->getResult();

        $banks       = [];
        $usedLetters = [];
        $alphaSeq    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Pass 1: collect candidate letters from bank_kode / bank_nama patterns
        foreach ($rows as $r) {
            $kode   = strtoupper(trim((string) ($r->bank_kode ?? '')));
            $nama   = (string) ($r->bank_nama ?? '');
            $letter = null;

            // Pattern 1: kode ends with -A … -Z  (e.g. "EKONOMI-STS-XI-A")
            if (preg_match('/-([A-Z])$/i', $kode, $m)) {
                $letter = strtoupper($m[1]);
            }
            // Pattern 2: kode contains "PAKET-1", "PAKET-2", "PAKET_A", "PAKET_B", …
            elseif (preg_match('/PAKET[-_]?(\d+|[A-Z])$/i', $kode, $m)) {
                $v = strtoupper($m[1]);
                $letter = is_numeric($v) ? (isset($alphaSeq[$v - 1]) ? $alphaSeq[$v - 1] : null) : $v;
            }
            // Pattern 3: bank_nama contains "Paket A", "Paket B", "Paket Soal A", …
            elseif (preg_match('/paket(?:\s+soal)?\s+([A-Z])/i', $nama, $m)) {
                $letter = strtoupper($m[1]);
            }

            $r->_letter_hint = $letter;
        }

        // Pass 2: finalise letters — keep explicit ones if unique, otherwise assign sequentially
        $seqIdx = 0;
        foreach ($rows as $r) {
            $idBank = (int) ($r->id_bank ?? 0);
            $letter = $r->_letter_hint;

            if ($letter === null || in_array($letter, $usedLetters, true)) {
                // Find next available sequential letter
                while ($seqIdx < strlen($alphaSeq) && in_array($alphaSeq[$seqIdx], $usedLetters, true)) {
                    $seqIdx++;
                }
                $letter = $seqIdx < strlen($alphaSeq) ? $alphaSeq[$seqIdx] : ('P' . ($seqIdx + 1));
                $seqIdx++;
            }
            $usedLetters[] = $letter;

            // Strip leading "Paket Soal A - " / "Paket A - " prefix from nama for a cleaner subtitle
            $bankNama   = trim((string) ($r->bank_nama ?? ''));
            $cleanNama  = preg_replace('/^paket\s+soal\s+[A-Z]\s*[-–]\s*/iu', '', $bankNama);
            $cleanNama  = preg_replace('/^paket\s+[A-Z]\s*[-–]\s*/iu', '', $cleanNama);

            $banks[] = [
                'id_bank'      => $idBank,
                'bank_kode'    => (string) ($r->bank_kode ?? ''),
                'bank_nama'    => $bankNama ?: (string) ($r->bank_kode ?? ''),
                'bank_label'   => $cleanNama ?: (string) ($r->bank_kode ?? ''),
                'letter'       => $letter,
                'subtes_code'  => (string) ($r->subtes_code ?? 'GENERAL'),
                'subtes_nama'  => (string) ($r->subtes_nama ?? ''),
                'total_soal'   => (int) ($r->total_soal ?? 0),
                'total_tampil' => (int) ($r->total_tampil ?? 0),
                'url_detail'   => site_url('cbtbanksoal/detail/' . $idBank),
            ];
        }
        return $banks;
    }

    public function detail(string $uuid)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return redirect()->to('cbtkartusoal')->with('error', 'Akses ditolak');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        // Load mapel and kelas names
        $mapel = $this->master->getMapelById((int) ($kartu->mapel_id ?? 0));
        $kelas = $this->master->getKelasById((int) ($kartu->kelas_id ?? 0));
        $jenisUjian = $this->cbt->getJenisUjianById((int) ($kartu->id_jenis ?? 0));
        $itemCount         = $this->items->getItemsCount((int) $kartu->id_kartu);
        $linkedTujuanCount = $this->items->getLinkedTujuanUniqueCount((int) $kartu->id_kartu);
        $totalEstimasi     = $this->items->getTotalEstimasi((int) $kartu->id_kartu);

        // Cek orphan items: satu query (hindari N+1) — ada id_tujuan yg tidak ada di TP aktif?
        $hasOrphanItems = false;
        $tujuanIds = array_unique(array_filter(array_map(
            static fn($it) => (int) ($it->id_tujuan ?? 0),
            $this->items->select('id_tujuan')->where('kartu_id', (int) $kartu->id_kartu)->findAll()
        )));
        if (!empty($tujuanIds)) {
            $activeCount = $this->tujuan->whereIn('id_tujuan', $tujuanIds)->where('is_active', 1)->countAllResults();
            $hasOrphanItems = $activeCount < count($tujuanIds);
        }
        $tpRow = null;
        foreach ($this->dashboard->getTahun() as $tpItem) {
            if ((int) ($tpItem->id_tp ?? 0) === (int) ($kartu->tp_id ?? 0)) {
                $tpRow = $tpItem;
                break;
            }
        }
        $smtRow = null;
        foreach ($this->dashboard->getSemester() as $smtItem) {
            if ((int) ($smtItem->id_smt ?? 0) === (int) ($kartu->smt_id ?? 0)) {
                $smtRow = $smtItem;
                break;
            }
        }

        // Paket/bank soal yang pernah dibuat dari kartu ini
        $paketBanks = $this->getPaketBanksForKartu((int) $kartu->id_kartu);

        $data = [
            'user' => $user,
            'judul' => 'Detail Kartu Soal',
            'subjudul' => 'Kartu Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'kartu' => $kartu,
            'mapel' => $mapel,
            'kelas' => $kelas,
            'jenis_ujian' => $jenisUjian,
            'tp_row' => $tpRow,
            'smt_row' => $smtRow,
            'item_count' => $itemCount,
            'linkedTujuanCount' => $linkedTujuanCount,
            'total_estimasi' => $totalEstimasi,
            'summary_jenis' => $this->items->getKartuSummary((int) $kartu->id_kartu),
            'can_manage' => $this->canManage($kartu),
            'is_approval_role' => $this->isApprovalRole(),
            'has_orphan_items' => $hasOrphanItems,
            'tujuan_options' => $this->getTujuanOptionsForKartu($kartu),
            'jenis_soal_labels' => [
                1 => 'Pilihan Ganda',
                2 => 'Pilihan Ganda Kompleks',
                3 => 'Menjodohkan',
                4 => 'Isian Singkat',
                5 => 'Essay',
                6 => 'Urutan',
                7 => 'Cloze',
                8 => 'True / False',
            ],
            'paket_banks' => $paketBanks,
            'revisi_info' => $this->getLatestRevisionInfo((int) $kartu->id_kartu),
        ];

        return view('cbt/kartusoal/detail', $data);
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        if (!$this->canManage()) {
            return redirect()->to('cbtkartusoal')->with('error', 'Akses ditolak');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $selectedTpId = (int) ($tp->id_tp ?? 0);
        $selectedSmtId = (int) ($smt->id_smt ?? 0);
        $allMapel = $this->getMapelForCurrentUser($selectedTpId, $selectedSmtId);

        if ($this->isGuru()) {
            $guru = $this->getCurrentGuruProfile();
            $hasAnyTujuan = false;
            if ($guru && $tp && $smt) {
                $hasAnyTujuan = $this->tujuan->where('id_guru', (int) $guru->id_guru)
                    ->where('id_tp', (int) $tp->id_tp)
                    ->where('id_smt', (int) $smt->id_smt)
                    ->countAllResults() > 0;
            }

            if (!$hasAnyTujuan) {
                return redirect()->to('guru/tujuan')->with('error', 'Lengkapi Tujuan Pembelajaran terlebih dahulu sebelum membuat Kartu Soal.');
            }
        }

        $data = [
            'user' => $user,
            'judul' => 'Buat Kartu Soal',
            'subjudul' => 'Kartu Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'kartu' => null,
            'item_count' => 0,
            'mapels' => $allMapel,
            'kelases' => $this->getKelasForCurrentUser($selectedTpId, $selectedSmtId),
            'jenis_ujian' => $this->getJenisUjianOptions(),
            'tp_options' => $this->dashboard->getTahun(),
            'smt_options' => $this->dashboard->getSemester(),
            'selected_smt_id' => $selectedSmtId,
            'tujuan_options' => [],
            'gurus' => ($this->isAdmin() || $this->isPanitia()) ? $this->master->getAllGuruWithUserId() : [],
        ];

        return view('cbt/kartusoal/form', $data);
    }

    public function edit(string $uuid)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }
        if (!$this->canManage($kartu)) {
            return redirect()->to('cbtkartusoal')->with('error', 'Akses ditolak');
        }

        $user = $this->authModel->user();
        $setting = $this->dashboard->getSetting();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $selectedTpId = (int) ($kartu->tp_id ?? ($tp->id_tp ?? 0));
        $selectedSmtId = (int) ($kartu->smt_id ?? ($smt->id_smt ?? 0));

        $data = [
            'user' => $user,
            'judul' => 'Edit Kartu Soal',
            'subjudul' => 'Kartu Soal',
            'setting' => $setting,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'kartu' => $kartu,
            'item_count' => $this->items->getItemsCount((int) $kartu->id_kartu),
            'mapels' => $this->getMapelForCurrentUser($selectedTpId, $selectedSmtId),
            'kelases' => $this->getKelasForCurrentUser($selectedTpId, $selectedSmtId),
            'jenis_ujian' => $this->getJenisUjianOptions(),
            'tp_options' => $this->dashboard->getTahun(),
            'smt_options' => $this->dashboard->getSemester(),
            'selected_tp_id' => $selectedTpId,
            'selected_smt_id' => $selectedSmtId,
            'tujuan_options' => $this->getTujuanByMapelForCurrentGuru(
                (int) ($kartu->mapel_id ?? 0),
                (int) ($kartu->tp_id ?? 0),
                (int) ($kartu->smt_id ?? 0)
            ),
            'jenis_soal_labels' => [
                1 => 'Pilihan Ganda',
                2 => 'Pilihan Kompleks',
                3 => 'Menjodohkan',
                4 => 'Isian Singkat',
                5 => 'Essay',
                6 => 'Urutan',
                7 => 'Cloze',
                8 => 'True / False',
            ],
        ];
        $data['gurus'] = ($this->isAdmin() || $this->isPanitia()) ? $this->master->getAllGuruWithUserId() : [];

        return view('cbt/kartusoal/form', $data);
    }

    public function save()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        $guard = $this->ensureCanManage();
        if ($guard) {
            return $guard;
        }

        $uuid      = trim((string) ($this->request->getPost('uuid') ?? ''));
        $judul     = trim((string) ($this->request->getPost('judul') ?? ''));
        $deskripsi = trim((string) ($this->request->getPost('deskripsi') ?? ''));
        $mapelId   = (int) ($this->request->getPost('mapel_id') ?? 0);
        $jenisId   = (int) ($this->request->getPost('id_jenis') ?? 0);
        $tpId      = (int) ($this->request->getPost('tp_id') ?? 0);
        $smtId     = (int) ($this->request->getPost('smt_id') ?? 0);

        // Multi-kelas: accept kelas_ids[] (new) or kelas_id (legacy)
        $rawKelasIds = $this->request->getPost('kelas_ids') ?? [];
        if (!is_array($rawKelasIds) || empty($rawKelasIds)) {
            // Fallback to single kelas_id for legacy compatibility
            $single = (int) ($this->request->getPost('kelas_id') ?? 0);
            $rawKelasIds = $single > 0 ? [$single] : [];
        }
        $kelasIdArr = array_values(array_unique(array_filter(array_map('intval', $rawKelasIds))));
        $kelasId    = $kelasIdArr[0] ?? 0; // First kelas for backward compatibility

        if (empty($kelasIdArr)) {
            return $this->respond(false, 'Validasi gagal', null, ['kelas_id' => 'Pilih minimal satu kelas'], 422);
        }

        $rules = [
            'judul'    => 'required|min_length[3]|max_length[150]',
            'deskripsi'=> 'permit_empty|max_length[5000]',
            'mapel_id' => 'required|is_natural_no_zero',
            'id_jenis' => 'required|is_natural_no_zero',
            'tp_id'    => 'required|is_natural_no_zero',
            'smt_id'   => 'required|is_natural_no_zero',
        ];
        $validation = service('validation');
        $validation->setRules($rules);
        if (!$validation->run([
            'judul'    => $judul,
            'deskripsi'=> $deskripsi,
            'mapel_id' => $mapelId,
            'id_jenis' => $jenisId,
            'tp_id'    => $tpId,
            'smt_id'   => $smtId,
        ])) {
            return $this->respond(false, 'Validasi gagal', null, $validation->getErrors(), 422);
        }

        if (!$this->isValidTpSmt($tpId, $smtId)) {
            return $this->respond(false, 'Tahun pelajaran atau semester tidak valid.', null, ['tp_id' => 'Periode tidak valid'], 422);
        }
        if (!$this->isValidJenisUjian($jenisId)) {
            return $this->respond(false, 'Jenis ujian tidak valid.', null, ['id_jenis' => 'Jenis ujian tidak ditemukan/aktif'], 422);
        }

        /**
         * Logika bisnis pembuatan kartu soal:
         * - Jangan lock pada kelas (mapel-kelas assignment diabaikan, bebas pilih kelas).
         * - Lock pada tingkat: hanya kelas level X (10), XI (11) dan XII (12) yang diperbolehkan.
         */
        $invalidTingkat = $this->validateKelasTingkatLock($kelasIdArr, $tpId, $smtId);
        if (!empty($invalidTingkat)) {
            $names = array_map(
                fn($r) => $r['nama_kelas'] . ' (Tingkat ' . $r['level'] . ')',
                $invalidTingkat
            );
            return $this->respond(
                false,
                'Kelas yang dipilih harus berbasis tingkat X, XI, atau XII. Terdeteksi di luar tingkat tersebut: ' . implode(', ', $names),
                ['invalid_tingkat' => $invalidTingkat],
                ['kelas_id' => 'Pilih kelas dengan tingkat X, XI, atau XII saja.'],
                422
            );
        }

        if ($this->isGuru() && !$this->hasTujuanForMapel($mapelId, $tpId, $smtId)) {
            return $this->respond(
                false,
                'Mapel ini belum memiliki Tujuan Pembelajaran. Lengkapi dulu di menu Guru > Tujuan Pembelajaran.',
                ['workflow' => 'missing_tujuan'],
                null,
                422
            );
        }

        $kelasIdsJson = json_encode($kelasIdArr, JSON_UNESCAPED_UNICODE);
        $userId = (int) $this->getUserId();
        
        // Admin/Panitia can override the creator
        $createdBy = $userId;
        if ($this->isAdmin() || $this->isPanitia()) {
            $postedCreator = (int) ($this->request->getPost('created_by') ?? 0);
            if ($postedCreator > 0) {
                $createdBy = $postedCreator;
            }
        }

        if ($uuid !== '') {
            $existing = $this->kartu->findByUuid($uuid);
            if (!$existing) {
                return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
            }
            if (!$this->canManage($existing)) {
                return $this->respond(false, 'Akses ditolak', null, null, 403);
            }
            $editableGuard = $this->ensureEditableKartu($existing);
            if ($editableGuard) {
                return $editableGuard;
            }

            // If mapel changes, nullify item TP references that don't match the new mapel
            if ((int) ($existing->mapel_id ?? 0) !== $mapelId && $mapelId > 0) {
                $db = \Config\Database::connect();
                $db->table('cbt_kartu_soal_item ksi')
                    ->join('e_tujuan_pembelajaran tp', 'ksi.id_tujuan = tp.id_tujuan', 'left')
                    ->where('ksi.kartu_id', (int) $existing->id_kartu)
                    ->where(function ($q) use ($mapelId) {
                        $q->where('tp.id_mapel IS NULL')
                          ->orWhere('tp.id_mapel !=', $mapelId)
                          ->orWhere('tp.is_active', 0);
                    })
                    ->set(['ksi.id_tujuan' => null, 'ksi.updated_at' => date('Y-m-d H:i:s')])
                    ->update();
            }

            $ok = $this->kartu->update((int) $existing->id_kartu, [
                'judul'      => $judul,
                'deskripsi'  => $deskripsi !== '' ? $deskripsi : null,
                'mapel_id'   => $mapelId,
                'kelas_id'   => $kelasId,
                'kelas_ids'  => $kelasIdsJson,
                'id_jenis'   => $jenisId,
                'tp_id'      => $tpId,
                'smt_id'     => $smtId,
                'updated_by' => $userId,
                'created_by' => $createdBy, // Allow admin to change creator
            ]);
            if (!$ok) {
                return $this->respond(false, 'Gagal menyimpan', null, $this->kartu->errors(), 500);
            }
            $this->audit->add((int) $existing->id_kartu, 'update', json_encode(['judul' => $judul], JSON_UNESCAPED_UNICODE));
            $this->logging->saveLog(5, 'Update kartu soal: ' . $judul);
            return $this->respond(true, 'Berhasil disimpan', ['uuid' => $uuid, 'is_create' => false]);
        }

        $uuid = $this->generateUuidV4();
        $id   = $this->kartu->insert([
            'uuid'           => $uuid,
            'judul'          => $judul,
            'deskripsi'      => $deskripsi !== '' ? $deskripsi : null,
            'mapel_id'       => $mapelId,
            'kelas_id'       => $kelasId,
            'kelas_ids'      => $kelasIdsJson,
            'id_jenis'       => $jenisId,
            'tp_id'          => $tpId,
            'smt_id'         => $smtId,
            'status'         => 1,
            'workflow_status'=> 'draft',
            'submitted_at'   => null,
            'approved_at'    => null,
            'approved_by'    => null,
            'approval_note'  => null,
            'created_by'     => $createdBy,
            'updated_by'     => $userId,
        ], true);
        if (!$id) {
            return $this->respond(false, 'Gagal membuat kartu', null, $this->kartu->errors(), 500);
        }
        $this->audit->add((int) $id, 'create', json_encode(['judul' => $judul], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(3, 'Buat kartu soal: ' . $judul);

        return $this->respond(true, 'Kartu dibuat', ['uuid' => $uuid, 'is_create' => true]);
    }

    public function delete()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        // Admin/panitia boleh menghapus kartu approved (cleanup);
        // guru tetap wajib draft/revisi.
        if (!$this->isApprovalRole()) {
            $editableGuard = $this->ensureEditableKartu($kartu);
            if ($editableGuard) {
                return $editableGuard;
            }
        }

        // Check if kartu soal is being used in bank soal
        $bankUsageCount = $this->kartu->isUsedInBankSoal((int) $kartu->id_kartu);
        $force = (string) ($this->request->getPost('force') ?? '0') === '1';
        $canForce = $this->isApprovalRole();
        if ($bankUsageCount > 0 && !$force) {
            $bankList = $this->kartu->getBankSoalUsage((int) $kartu->id_kartu);
            $bankNames = array_map(fn($b) => $b['kode'] . ' - ' . $b['nama'], $bankList);

            return $this->respond(false, 'Kartu soal tidak dapat dihapus karena sedang digunakan di ' . $bankUsageCount . ' bank soal: ' . implode(', ', $bankNames) . '. Hapus atau ubah bank soal terlebih dahulu.' . ($canForce ? ' Atau ulangi dengan hapus paksa untuk memutus relasi.' : ''), [
                'bank_count' => $bankUsageCount,
                'banks' => $bankList,
                'can_force' => $canForce,
            ], null, 422);
        }
        if ($bankUsageCount > 0 && $force && !$canForce) {
            return $this->respond(false, 'Hapus paksa hanya untuk Administrator/Panitia', null, null, 403);
        }

        if ($bankUsageCount > 0 && $force) {
            // Putuskan relasi agar provenance bank tidak menggantung (FK juga SET NULL)
            \Config\Database::connect()->table('cbt_bank_soal')
                ->where('kartu_soal_id', (int) $kartu->id_kartu)
                ->update(['kartu_soal_id' => null]);
        }

        $this->items->where('kartu_id', (int) $kartu->id_kartu)->delete();
        $ok = $this->kartu->delete((int) $kartu->id_kartu);
        if (!$ok) {
            return $this->respond(false, 'Gagal menghapus', null, $this->kartu->errors(), 500);
        }
        $this->audit->add((int) $kartu->id_kartu, $force && $bankUsageCount > 0 ? 'delete_force' : 'delete', json_encode(['judul' => $kartu->judul, 'bank_unlinked' => $bankUsageCount], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(6, 'Hapus kartu soal: ' . $kartu->judul);
        return $this->respond(true, 'Berhasil dihapus');
    }

    /**
     * Bulk delete kartu soal (maksimal 100 per request).
     * Kartu yang sedang dipakai bank soal aktif / tidak boleh diedit / tidak ada
     * akses akan dilewati dan dilaporkan, tidak menggagalkan yang lain.
     */
    public function bulkDelete()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        $guard = $this->ensureCanManage();
        if ($guard) {
            return $guard;
        }

        $uuids = $this->request->getPost('uuids');
        if (is_string($uuids)) {
            $decoded = json_decode($uuids, true);
            $uuids = is_array($decoded) ? $decoded : [$uuids];
        }
        if (!is_array($uuids)) {
            return $this->respond(false, 'Pilih minimal satu kartu soal', null, null, 422);
        }
        $uuids = array_values(array_unique(array_filter(array_map(
            static fn($v) => trim((string) $v),
            $uuids
        ))));
        if (empty($uuids)) {
            return $this->respond(false, 'Pilih minimal satu kartu soal', null, null, 422);
        }
        if (count($uuids) > 100) {
            return $this->respond(false, 'Maksimal 100 kartu per hapus massal', null, null, 422);
        }

        $force = (string) ($this->request->getPost('force') ?? '0') === '1';
        $canForce = $this->isApprovalRole();
        if ($force && !$canForce) {
            return $this->respond(false, 'Hapus paksa hanya untuk Administrator/Panitia', null, null, 403);
        }

        $deleted = [];
        $skippedLocked = [];
        $skippedForbidden = [];
        $skippedNotFound = [];

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($uuids as $uuid) {
            $kartu = $this->kartu->findByUuid($uuid);
            if (!$kartu) {
                $skippedNotFound[] = $uuid;
                continue;
            }
            if (!$this->canManage($kartu)) {
                $skippedForbidden[] = ['uuid' => $uuid, 'judul' => $kartu->judul ?? '-'];
                continue;
            }
            // Admin/panitia boleh hapus kartu approved; guru tetap draft/revisi saja
            if (!$canForce && !in_array($this->getWorkflowStatusValue($kartu), ['draft', 'revisi'], true)) {
                $skippedForbidden[] = ['uuid' => $uuid, 'judul' => $kartu->judul ?? '-', 'alasan' => 'Sudah disubmit/approved'];
                continue;
            }
            $bankUsageCount = $this->kartu->isUsedInBankSoal((int) $kartu->id_kartu);
            if ($bankUsageCount > 0 && !$force) {
                $skippedLocked[] = [
                    'uuid' => $uuid,
                    'judul' => $kartu->judul ?? '-',
                    'bank_count' => $bankUsageCount,
                    'banks' => $this->kartu->getBankSoalUsage((int) $kartu->id_kartu),
                ];
                continue;
            }
            if ($bankUsageCount > 0 && $force) {
                $db->table('cbt_bank_soal')
                    ->where('kartu_soal_id', (int) $kartu->id_kartu)
                    ->update(['kartu_soal_id' => null]);
            }

            $this->items->where('kartu_id', (int) $kartu->id_kartu)->delete();
            $ok = $this->kartu->delete((int) $kartu->id_kartu);
            if ($ok) {
                $deleted[] = ['uuid' => $uuid, 'judul' => $kartu->judul ?? '-'];
                $this->audit->add((int) $kartu->id_kartu, 'bulk_delete', json_encode(['judul' => $kartu->judul ?? ''], JSON_UNESCAPED_UNICODE));
            } else {
                $skippedForbidden[] = ['uuid' => $uuid, 'judul' => $kartu->judul ?? '-', 'alasan' => 'Gagal menghapus'];
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->respond(false, 'Transaksi hapus massal gagal, tidak ada data yang dihapus', null, null, 500);
        }

        $this->logging->saveLog(6, 'Bulk hapus kartu soal: ' . count($deleted) . ' berhasil, ' . (count($skippedLocked) + count($skippedForbidden) + count($skippedNotFound)) . ' dilewati');

        $msg = count($deleted) . ' kartu berhasil dihapus';
        $skippedTotal = count($skippedLocked) + count($skippedForbidden) + count($skippedNotFound);
        if ($skippedTotal > 0) {
            $msg .= ', ' . $skippedTotal . ' dilewati (terkunci/akses ditolak/tidak ditemukan)';
        }

        return $this->respond(true, $msg, [
            'deleted' => $deleted,
            'skipped_locked' => $skippedLocked,
            'skipped_forbidden' => $skippedForbidden,
            'skipped_notfound' => $skippedNotFound,
        ]);
    }

    public function addItem()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        $jenisSoal = (int) ($this->request->getPost('jenis_soal') ?? 1);
        $nomorInput = (int) ($this->request->getPost('nomor') ?? 0);
        $idTujuan = (int) ($this->request->getPost('id_tujuan') ?? 0);
        $cp = trim((string) ($this->request->getPost('cp') ?? ''));
        $materi = trim((string) ($this->request->getPost('materi') ?? ''));
        $indikatorSoal = trim((string) ($this->request->getPost('indikator_soal') ?? ''));
        // Auto-default level kognitif & estimasi waktu dari jenis soal bila kosong
        $levelKognitif = trim((string) ($this->request->getPost('level_kognitif') ?? ''));
        if ($levelKognitif === '') {
            $levelKognitif = $this->defaultLevelKognitif($jenisSoal);
        }
        $estimasiWaktu = (int) ($this->request->getPost('estimasi_waktu') ?? 0);
        if ($estimasiWaktu <= 0) {
            $estimasiWaktu = $this->defaultEstimasiWaktu($jenisSoal);
        }
        $catatan = trim((string) ($this->request->getPost('catatan') ?? ''));
        $butirSoal = (string) ($this->request->getPost('butir_soal') ?? '');
        $opsiRaw = $this->decodeJsonPayload($this->request->getPost('opsi_json'));
        $jawabanRaw = $this->decodeJsonPayload($this->request->getPost('jawaban_benar_json'));
        $pembahasan = $this->normalizeHtml((string) ($this->request->getPost('pembahasan') ?? ''));

        $rules = [
            'jenis_soal' => 'required|in_list[1,2,3,4,5,6,7,8]',
            'id_tujuan' => 'required|is_natural_no_zero',
            'cp' => 'permit_empty|max_length[255]',
            'materi' => 'permit_empty|max_length[255]',
        ];
        $validation = service('validation');
        $validation->setRules($rules);
        if (!$validation->run([
            'jenis_soal' => $jenisSoal,
            'id_tujuan' => $idTujuan,
            'cp' => $cp,
            'materi' => $materi,
        ])) {
            return $this->respond(false, 'Validasi gagal', null, $validation->getErrors(), 422);
        }

        $answerValidation = $this->validateItemAnswerPayload($jenisSoal, $butirSoal, $opsiRaw, $jawabanRaw, $indikatorSoal, $materi, $levelKognitif);
        if (!$answerValidation['ok']) {
            return $this->respond(false, 'Validasi butir soal gagal', null, $answerValidation['errors'], 422);
        }

        $tujuanRow = null;
        if ($this->isGuru()) {
            $tujuanList = $this->getTujuanByMapelForCurrentGuru(
                (int) ($kartu->mapel_id ?? 0),
                (int) ($kartu->tp_id ?? 0),
                (int) ($kartu->smt_id ?? 0)
            );
            foreach ($tujuanList as $tpItem) {
                if ((int) ($tpItem->id_tujuan ?? 0) === $idTujuan) {
                    $tujuanRow = $tpItem;
                    break;
                }
            }
        } else {
            // Admin/panitia: validasi mapel & is_active
            $tujuanRow = $this->tujuan
                ->where('id_tujuan', $idTujuan)
                ->where('id_mapel', (int) ($kartu->mapel_id ?? 0))
                ->where('is_active', 1)
                ->first();
        }
        if (!$tujuanRow) {
            return $this->respond(false, 'Tujuan Pembelajaran tidak valid untuk mapel kartu ini.', null, null, 422);
        }

        $nomor = $nomorInput > 0 ? $nomorInput : $this->items->nextNomor((int) $kartu->id_kartu);
        if ($this->isNomorTaken((int) $kartu->id_kartu, $nomor)) {
            return $this->respond(false, 'Nomor soal ' . $nomor . ' sudah digunakan oleh butir lain di kartu ini.', null, ['nomor' => 'No. soal duplikat'], 422);
        }
        $idItem = $this->items->insert([
            'kartu_id' => (int) $kartu->id_kartu,
            'nomor' => $nomor,
            'jenis_soal' => $jenisSoal,
            'id_tujuan' => $idTujuan,
            'cp' => $cp !== '' ? $cp : (trim((string) ($tujuanRow->kode_cp ?? '')) !== '' ? (string) $tujuanRow->kode_cp : null),
            'materi' => $materi !== '' ? $materi : null,
            'tujuan' => trim((string) ($tujuanRow->tujuan ?? '')) !== '' ? (string) $tujuanRow->tujuan : null,
            'indikator_soal' => $indikatorSoal !== '' ? $indikatorSoal : null,
            'level_kognitif' => $levelKognitif !== '' ? $levelKognitif : null,
            'estimasi_waktu' => $estimasiWaktu > 0 ? $estimasiWaktu : null,
            'catatan' => $catatan !== '' ? $catatan : null,
            'butir_soal' => $answerValidation['butir_soal'],
            'opsi_json' => $answerValidation['opsi_json'],
            'jawaban_benar_json' => $answerValidation['jawaban_benar_json'],
            'pembahasan' => $pembahasan !== '' ? $pembahasan : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], true);

        if (!$idItem) {
            return $this->respond(false, 'Gagal menambah item', null, $this->items->errors(), 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'add_item', json_encode(['nomor' => $nomor, 'jenis' => $jenisSoal], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Tambah item kartu soal: ' . $kartu->judul);
        return $this->respond(true, 'Item ditambahkan', ['id_item' => (int) $idItem, 'nomor' => $nomor]);
    }

    public function updateItem()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $itemId = (int) ($this->request->getPost('id_item') ?? 0);
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        $item = $this->items->find($itemId);
        if (!$item || (int) $item->kartu_id !== (int) $kartu->id_kartu) {
            return $this->respond(false, 'Item tidak ditemukan', null, null, 404);
        }

        $jenisSoal = (int) ($this->request->getPost('jenis_soal') ?? 1);
        $nomorInput = (int) ($this->request->getPost('nomor') ?? 0);
        $idTujuan = (int) ($this->request->getPost('id_tujuan') ?? 0);
        $cp = trim((string) ($this->request->getPost('cp') ?? ''));
        $materi = trim((string) ($this->request->getPost('materi') ?? ''));
        $indikatorSoal = trim((string) ($this->request->getPost('indikator_soal') ?? ''));
        // Auto-default level kognitif & estimasi waktu dari jenis soal bila kosong
        $levelKognitif = trim((string) ($this->request->getPost('level_kognitif') ?? ''));
        if ($levelKognitif === '') {
            $levelKognitif = $this->defaultLevelKognitif($jenisSoal);
        }
        $estimasiWaktu = (int) ($this->request->getPost('estimasi_waktu') ?? 0);
        if ($estimasiWaktu <= 0) {
            $estimasiWaktu = $this->defaultEstimasiWaktu($jenisSoal);
        }
        $catatan = trim((string) ($this->request->getPost('catatan') ?? ''));
        $butirSoal = (string) ($this->request->getPost('butir_soal') ?? '');
        $opsiRaw = $this->decodeJsonPayload($this->request->getPost('opsi_json'));
        $jawabanRaw = $this->decodeJsonPayload($this->request->getPost('jawaban_benar_json'));
        $pembahasan = $this->normalizeHtml((string) ($this->request->getPost('pembahasan') ?? ''));

        $rules = [
            'jenis_soal' => 'required|in_list[1,2,3,4,5,6,7,8]',
            'id_tujuan' => 'required|is_natural_no_zero',
            'cp' => 'permit_empty|max_length[255]',
            'materi' => 'permit_empty|max_length[255]',
        ];
        $validation = service('validation');
        $validation->setRules($rules);
        if (!$validation->run([
            'jenis_soal' => $jenisSoal,
            'id_tujuan' => $idTujuan,
            'cp' => $cp,
            'materi' => $materi,
        ])) {
            return $this->respond(false, 'Validasi gagal', null, $validation->getErrors(), 422);
        }

        $answerValidation = $this->validateItemAnswerPayload($jenisSoal, $butirSoal, $opsiRaw, $jawabanRaw, $indikatorSoal, $materi, $levelKognitif);
        if (!$answerValidation['ok']) {
            return $this->respond(false, 'Validasi butir soal gagal', null, $answerValidation['errors'], 422);
        }

        $tujuanRow = null;
        if ($this->isGuru()) {
            $tujuanList = $this->getTujuanByMapelForCurrentGuru(
                (int) ($kartu->mapel_id ?? 0),
                (int) ($kartu->tp_id ?? 0),
                (int) ($kartu->smt_id ?? 0)
            );
            foreach ($tujuanList as $tpItem) {
                if ((int) ($tpItem->id_tujuan ?? 0) === $idTujuan) {
                    $tujuanRow = $tpItem;
                    break;
                }
            }
        } else {
            // Admin/panitia: validasi mapel & is_active
            $tujuanRow = $this->tujuan
                ->where('id_tujuan', $idTujuan)
                ->where('id_mapel', (int) ($kartu->mapel_id ?? 0))
                ->where('is_active', 1)
                ->first();
        }
        if (!$tujuanRow) {
            return $this->respond(false, 'Tujuan Pembelajaran tidak valid untuk mapel kartu ini.', null, null, 422);
        }

        $updateData = [
            'jenis_soal' => $jenisSoal,
            'id_tujuan' => $idTujuan,
            'bentuk_soal' => null,
            'cp' => $cp !== '' ? $cp : (trim((string) ($tujuanRow->kode_cp ?? '')) !== '' ? (string) $tujuanRow->kode_cp : null),
            'materi' => $materi !== '' ? $materi : null,
            'tujuan' => trim((string) ($tujuanRow->tujuan ?? '')) !== '' ? (string) $tujuanRow->tujuan : null,
            'indikator_soal' => $indikatorSoal !== '' ? $indikatorSoal : null,
            'level_kognitif' => $levelKognitif !== '' ? $levelKognitif : null,
            'estimasi_waktu' => $estimasiWaktu > 0 ? $estimasiWaktu : null,
            'catatan' => $catatan !== '' ? $catatan : null,
            'butir_soal' => $answerValidation['butir_soal'],
            'opsi_json' => $answerValidation['opsi_json'],
            'jawaban_benar_json' => $answerValidation['jawaban_benar_json'],
            'pembahasan' => $pembahasan !== '' ? $pembahasan : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Update nomor if provided
        if ($nomorInput > 0) {
            if ($this->isNomorTaken((int) $kartu->id_kartu, $nomorInput, (int) $itemId)) {
                return $this->respond(false, 'Nomor soal ' . $nomorInput . ' sudah digunakan oleh butir lain di kartu ini.', null, ['nomor' => 'No. soal duplikat'], 422);
            }
            $updateData['nomor'] = $nomorInput;
        }

        $ok = $this->items->update($itemId, $updateData);

        if (!$ok) {
            return $this->respond(false, 'Gagal mengupdate item', null, $this->items->errors(), 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'update_item', json_encode(['id_item' => $itemId], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Update item kartu soal: ' . $kartu->judul);
        return $this->respond(true, 'Item diupdate');
    }

    public function cloneItem()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $itemId = (int) ($this->request->getPost('id_item') ?? 0);
        $asTemplate = $this->request->getPost('as_template') === '1';

        if ($itemId <= 0) {
            return $this->respond(false, 'Item tidak valid', null, null, 422);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        $source = $this->items->find($itemId);
        if (!$source || (int) $source->kartu_id !== (int) $kartu->id_kartu) {
            return $this->respond(false, 'Item tidak ditemukan di kartu ini', null, null, 404);
        }

        $nomor = $this->items->nextNomor((int) $kartu->id_kartu);

        $cloneData = [
            'kartu_id' => (int) $kartu->id_kartu,
            'nomor' => $nomor,
            'jenis_soal' => $source->jenis_soal,
            'id_tujuan' => $source->id_tujuan,
            'cp' => $source->cp,
            'materi' => $source->materi,
            'tujuan' => $source->tujuan,
            'indikator_soal' => $source->indikator_soal,
            'level_kognitif' => $source->level_kognitif,
            'estimasi_waktu' => $source->estimasi_waktu,
            'catatan' => $source->catatan,
            'butir_soal' => $source->butir_soal,
            'opsi_json' => $source->opsi_json,
            'jawaban_benar_json' => $source->jawaban_benar_json,
            'pembahasan' => $source->pembahasan,
        ];

        if ($asTemplate) {
            $cloneData['bank_id'] = null;
            $cloneData['soal_id'] = null;
        } else {
            $cloneData['bank_id'] = $source->bank_id;
            $cloneData['soal_id'] = $source->soal_id;
        }

        $cloneData['created_at'] = date('Y-m-d H:i:s');
        $cloneData['updated_at'] = date('Y-m-d H:i:s');

        $newId = $this->items->insert($cloneData, true);

        if (!$newId) {
            return $this->respond(false, 'Gagal menduplikasi item', null, $this->items->errors(), 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'clone_item', json_encode(['source_id' => $itemId, 'new_id' => $newId], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Duplikasi item kartu soal: ' . $kartu->judul . ' (id_item: ' . $newId . ')');

        $newItem = $this->items->getItemsWithTujuan((int) $kartu->id_kartu);
        return $this->respond(true, $asTemplate ? 'Template berhasil dibuat' : 'Item berhasil diduplikasi', $newItem ?: $cloneData);
    }

    public function removeItem()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $itemId = (int) ($this->request->getPost('id_item') ?? 0);
        $itemIds = $this->request->getPost('ids'); // Bulk Support
        
        // Handle case where jQuery sends array as ids[] instead of ids
        if (empty($itemIds)) {
            $itemIds = $this->request->getPost('ids[]');
        }
        // Also try getVar which handles both cases
        if (empty($itemIds)) {
            $itemIds = $this->request->getVar('ids');
        }
        // Try with index notation
        if (empty($itemIds)) {
            $allPost = $this->request->getPost();
            if (isset($allPost['ids']) && is_array($allPost['ids'])) {
                $itemIds = $allPost['ids'];
            } elseif (isset($allPost['ids[]']) && is_array($allPost['ids[]'])) {
                $itemIds = $allPost['ids[]'];
            }
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        if ($itemIds && is_array($itemIds)) {
            // Bulk delete logic using Query Builder directly (more reliable than Model with whereIn)
            $db = \Config\Database::connect();
            
            // Check if any items are being used in bank soal before deleting
            $usageCheck = $this->items->getBulkUsageStatus(array_map('intval', $itemIds));
            $usedItems = [];
            foreach ($usageCheck as $id => $status) {
                if ($status['used']) {
                    $usedItems[] = ['id' => $id, 'bank_info' => $status['bank_info']];
                }
            }
            
            if (!empty($usedItems)) {
                $usedCount = count($usedItems);
                $bankNames = array_map(fn($item) => $item['bank_info']['kode'] ?? '-', $usedItems);
                return $this->respond(false, 'Tidak dapat menghapus ' . $usedCount . ' butir soal yang sudah digunakan di bank soal: ' . implode(', ', $bankNames), [
                    'used_items' => $usedItems,
                ], null, 422);
            }
            
            $validIds = $db->table('cbt_kartu_soal_item')
                ->where('kartu_id', $kartu->id_kartu)
                ->whereIn('id_item', $itemIds)
                ->select('id_item')
                ->get()
                ->getResultArray();
            
            $validIdsArr = array_column($validIds, 'id_item');
            if (empty($validIdsArr)) {
                return $this->respond(false, 'Data tidak ditemukan', null, null, 404);
            }

            // Use Query Builder delete directly instead of Model delete with whereIn
            $ok = $db->table('cbt_kartu_soal_item')
                ->whereIn('id_item', $validIdsArr)
                ->delete();
                
            if (!$ok) {
                return $this->respond(false, 'Gagal menghapus beberapa item', null, null, 500);
            }

            $this->audit->add((int) $kartu->id_kartu, 'remove_items_bulk', json_encode(['ids' => $validIdsArr], JSON_UNESCAPED_UNICODE));
            $this->logging->saveLog(5, 'Hapus massal soal (' . count($validIdsArr) . ' butir) dari kartu: ' . $kartu->judul);
            return $this->respond(true, count($validIdsArr) . ' butir soal dihapus');
        } elseif ($itemId) {
            // Original single delete logic
            $item = $this->items->find($itemId);
            if (!$item || (int) $item->kartu_id !== (int) $kartu->id_kartu) {
                return $this->respond(false, 'Item tidak ditemukan', null, null, 404);
            }

            // Check if item is being used in bank soal
            $usageStatus = $this->items->isUsedInBankSoal($itemId);
            if ($usageStatus['used']) {
                $bankName = $usageStatus['bank_info']['kode'] . ' - ' . $usageStatus['bank_info']['nama'];
                return $this->respond(false, 'Butir soal tidak dapat dihapus karena sudah digunakan di bank soal: ' . $bankName . '. Hapus soal dari bank soal terlebih dahulu.', [
                    'bank_info' => $usageStatus['bank_info'],
                ], null, 422);
            }

            $ok = $this->items->delete($itemId);
            if (!$ok) {
                return $this->respond(false, 'Gagal menghapus item', null, $this->items->errors(), 500);
            }

            $this->audit->add((int) $kartu->id_kartu, 'remove_item', json_encode(['id_item' => $itemId], JSON_UNESCAPED_UNICODE));
            $this->logging->saveLog(5, 'Hapus soal dari kartu: ' . $kartu->judul);
            return $this->respond(true, 'Item dihapus');
        }

        return $this->respond(false, 'Tidak ada data dipilih');
    }

    public function reorder()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $order = $this->request->getPost('order');
        if (!is_array($order)) {
            return $this->respond(false, 'Payload tidak valid', null, null, 422);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $i = 1;
        foreach ($order as $idItem) {
            $idItem = (int) $idItem;
            if ($idItem <= 0) {
                continue;
            }
            $db->table('cbt_kartu_soal_item')
                ->where('id_item', $idItem)
                ->where('kartu_id', (int) $kartu->id_kartu)
                ->update(['nomor' => $i]);
            $i++;
        }
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->respond(false, 'Gagal menyimpan urutan', null, null, 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'reorder', json_encode(['count' => count($order)], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Ubah urutan kartu soal: ' . $kartu->judul);
        return $this->respond(true, 'Urutan diperbarui');
    }


    public function autoReorder()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        // Get all items and sort by jenis_soal first, then by nomor/id_item
        $items = $this->items->where('kartu_id', (int) $kartu->id_kartu)
            ->orderBy('jenis_soal', 'ASC')
            ->orderBy('nomor', 'ASC')
            ->orderBy('id_item', 'ASC')
            ->findAll();
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        $i = 1;
        $jenisSummary = [];
        foreach ($items as $item) {
            $db->table('cbt_kartu_soal_item')
                ->where('id_item', (int) $item->id_item)
                ->update(['nomor' => $i]);
            
            // Track count per jenis_soal for summary
            $jenis = (int) $item->jenis_soal;
            if (!isset($jenisSummary[$jenis])) {
                $jenisSummary[$jenis] = 0;
            }
            $jenisSummary[$jenis]++;
            
            $i++;
        }
        
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->respond(false, 'Gagal mengurutkan nomor soal otomatis', null, null, 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'auto_reorder', json_encode([
            'count' => count($items),
            'by_jenis' => $jenisSummary,
        ], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Auto urutkan kartu soal (by jenis): ' . $kartu->judul);
        
        // Build summary message
        $jenisLabels = [
            1 => 'Pilihan Ganda',
            2 => 'PG Kompleks',
            3 => 'Menjodohkan',
            4 => 'Isian Singkat',
            5 => 'Essay',
            6 => 'Urutan',
            7 => 'Cloze',
        ];
        
        $summaryText = '';
        foreach ($jenisSummary as $jenisId => $count) {
            $label = $jenisLabels[$jenisId] ?? 'Jenis ' . $jenisId;
            $summaryText .= ($summaryText ? ', ' : '') . $label . ': ' . $count;
        }
        
        return $this->respond(true, 'Nomor soal berhasil diurutkan berdasarkan jenis soal. (' . $summaryText . ')');
    }

    public function generateAiItems()

    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        $jumlah = max(1, min(30, (int) ($this->request->getPost('jumlah_soal') ?? 5)));
        $kesulitan = strtoupper(trim((string) ($this->request->getPost('tingkat_kesulitan') ?? 'C4')));
        if (!in_array($kesulitan, ['C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'MIXED'], true)) {
            $kesulitan = 'C4';
        }
        $jenisRaw = $this->request->getPost('jenis_soal');
        if (!is_array($jenisRaw)) {
            $jenisRaw = array_filter(array_map('trim', explode(',', (string) $jenisRaw)));
        }
        $jenis = array_values(array_unique(array_filter(array_map(static fn($j) => strtolower((string) $j), $jenisRaw), static fn($j) => in_array($j, ['1', '2', '3', '4', '5', '6', '7'], true))));
        if (empty($jenis)) {
            $jenis = ['1'];
        }
        $instruksi = trim((string) ($this->request->getPost('instruksi_tambahan') ?? ''));
        $aiModel   = trim((string) ($this->request->getPost('ai_model') ?? ''));

        // Optional: user-selected TP IDs filter
        $selectedTpIds = $this->request->getPost('selected_tujuan_ids');
        if (!is_array($selectedTpIds)) {
            $selectedTpIds = array_filter(array_map('intval', explode(',', (string) ($selectedTpIds ?? ''))));
        } else {
            $selectedTpIds = array_filter(array_map('intval', $selectedTpIds));
        }
        $selectedTpIds = array_values(array_unique($selectedTpIds));
        $allTujuanOptions = $this->getTujuanOptionsForKartu($kartu);
        if (empty($allTujuanOptions)) {
            return $this->respond(false, 'Tujuan Pembelajaran belum tersedia untuk kartu ini. Buat TP di menu Guru > Tujuan Pembelajaran.', null, null, 422);
        }

        // Filter ke TP yang dipilih user (jika ada seleksi)
        if (!empty($selectedTpIds)) {
            $tujuanOptions = array_values(array_filter($allTujuanOptions, static fn($tp) => in_array((int) ($tp->id_tujuan ?? 0), $selectedTpIds, true)));
            // Fallback ke semua jika filter menghasilkan kosong
            if (empty($tujuanOptions)) {
                $tujuanOptions = $allTujuanOptions;
            }
        } else {
            $tujuanOptions = $allTujuanOptions;
        }
        error_log('DEBUG selectedTpIds=' . json_encode($selectedTpIds) . ' all=' . count($allTujuanOptions) . ' filtered=' . count($tujuanOptions) . ' fallback=' . ((!empty($selectedTpIds) && count($tujuanOptions) === count($allTujuanOptions)) ? 'YES' : 'NO'));

        $apiKey = $this->ai->getApiKey((int) $this->getUserId(), $this->isGuru());
        if ($apiKey === '') {
            return $this->respond(false, 'API Key AI belum tersedia. Atur API key di profil atau setting sistem.', null, null, 422);
        }

        $mapel  = $this->master->getMapelById((int) ($kartu->mapel_id ?? 0));
        $kelas  = $this->master->getKelasById((int) ($kartu->kelas_id ?? 0));
        $mapelNama = (string) ($mapel->nama_mapel ?? '-');
        $kelasNama = (string) ($kelas->nama_kelas ?? '-');

        $prompt = $this->buildAiPromptForKartu($kartu, $mapelNama, $kelasNama, [
            'jumlah'    => $jumlah,
            'kesulitan' => $kesulitan,
            'jenis'     => $jenis,
            'instruksi' => $instruksi,
        ], $tujuanOptions);

        $aiResult = $this->ai->generateContent($apiKey, $prompt, $aiModel);
        if (!$aiResult['success']) {
            return $this->respond(false, $aiResult['message'], ['wait_seconds' => $aiResult['wait_seconds'] ?? 0], null, 422);
        }

        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        if (!$parseResult['success']) {
            return $this->respond(false, $parseResult['message'], null, null, 422);
        }

        $parsed = $parseResult['data'];

        $allowedTujuanIds = array_map(static fn($tp) => (int) ($tp->id_tujuan ?? 0), $tujuanOptions);
        $allowedTujuanIds = array_values(array_filter($allowedTujuanIds, static fn($v) => $v > 0));
        $defaultTujuanId  = (int) ($allowedTujuanIds[0] ?? 0);
        if ($defaultTujuanId <= 0) {
            return $this->respond(false, 'Tujuan Pembelajaran tidak valid.', null, null, 422);
        }

        $normalized = $this->normalizeGeneratedAiItems($parsed, $allowedTujuanIds, $defaultTujuanId, $jenis);
        if (empty($normalized['valid'])) {
            return $this->respond(false, 'AI tidak menghasilkan soal yang lolos validasi kualitas.', [
                'rejected' => $normalized['rejected'],
            ], null, 422);
        }

        return $this->respond(true, 'Kisi-kisi AI berhasil digenerate. Silakan review sebelum simpan.', [
            'items' => $normalized['valid'],
            'meta'  => [
                'generated_count' => (is_array($parsed) && isset($parsed['items']) && is_array($parsed['items']))
                    ? count($parsed['items'])
                    : (is_array($parsed) ? count($parsed) : count($normalized['valid'])),
                'valid_count'    => count($normalized['valid']),
                'rejected_count' => count($normalized['rejected']),
                'tp_used'        => count($tujuanOptions),
                'tp_total'       => count($allTujuanOptions),
                'ai_model'       => $aiResult['model'] ?? 'unknown',
                'ai_elapsed'     => $aiResult['elapsed'] ?? 0,
            ],
            'rejected' => $normalized['rejected'],
        ]);
    }

    public function generateItemSoalContent()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $itemId = (int) ($this->request->getPost('id_item') ?? 0);
        $save = $this->request->getPost('save') === '1';
        $aiModel = $this->request->getPost('ai_model') ?: 'gemini-3.5-flash-lite';

        if ($itemId <= 0) {
            return $this->respond(false, 'Item tidak valid', null, null, 422);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }

        $item = $this->items->find($itemId);
        if (!$item || (int) $item->kartu_id !== (int) $kartu->id_kartu) {
            return $this->respond(false, 'Item tidak ditemukan di kartu ini', null, null, 404);
        }

        $indikator = trim((string) ($item->indikator_soal ?? ''));
        $materi = trim((string) ($item->materi ?? ''));
        $level = trim((string) ($item->level_kognitif ?? 'C4'));
        $jenisSoal = (int) ($item->jenis_soal ?? 0);
        $cp = trim((string) ($item->cp ?? ''));
        $tujuan = trim((string) ($item->tujuan ?? ''));

        if (empty($indikator) || empty($materi)) {
            return $this->respond(false, 'Indikator soal dan materi harus diisi terlebih dahulu sebelum generate konten AI.', null, null, 422);
        }

        if (!in_array($jenisSoal, [1, 2, 3, 4, 5, 6, 7], true)) {
            return $this->respond(false, 'Jenis soal tidak valid.', null, null, 422);
        }

        $apiKey = $this->ai->getApiKey((int) $this->getUserId(), $this->inGroup('guru'));
        if (empty($apiKey)) {
            return $this->respond(false, 'API Key tidak tersedia. Silakan atur API Key terlebih dahulu.', null, null, 400);
        }

        $jenisLabelMap = [1 => 'Pilihan Ganda', 2 => 'PG Kompleks', 3 => 'Menjodohkan', 4 => 'Isian Singkat', 5 => 'Esai/Uraian', 6 => 'Urutan', 7 => 'Isian Rumpang'];
        $jenisLabel = $jenisLabelMap[$jenisSoal] ?? 'Soal';

        $mapelNama = '';
        if (!empty($kartu->mapel_id)) {
            $mapel = $this->master->find($kartu->mapel_id);
            $mapelNama = $mapel->nama_mapel ?? '';
        }

        $isEnglish = stripos($mapelNama, 'english') !== false || stripos($mapelNama, 'bahasa inggris') !== false;

        $prompt = "Anda adalah AI Assessment Specialist Kurikulum Indonesia.\n";
        $prompt .= "TUGAS: Buat SATU butir soal {$jenisLabel} berkualitas tinggi berdasarkan blueprint berikut.\n\n";
        $prompt .= "BLUEPRINT BUTIR SOAL:\n";
        $prompt .= "- Capaian Pembelajaran: {$cp}\n";
        $prompt .= "- Tujuan Pembelajaran: {$tujuan}\n";
        $prompt .= "- Materi: {$materi}\n";
        $prompt .= "- Indikator Soal: {$indikator}\n";
        $prompt .= "- Level Kognitif: {$level} (Taksonomi Bloom Revisi)\n";
        $prompt .= "- Bentuk Soal: {$jenisLabel}\n";
        if ($isEnglish) {
            $prompt .= "- BAHASA: Inggris (mata pelajaran Bahasa Inggris)\n";
        }
        $prompt .= "\nOUTPUT: JSON object dengan field-field berikut:\n";
        $prompt .= '{
  "soal": "Teks soal lengkap dengan stimulus bila diperlukan. WAJIB menggunakan bahasa Indonesia baku' . ($isEnglish ? ' Inggris' : '') . '.",
  "opsi": {';

        if ($jenisSoal === 1) {
            $prompt .= '"a": "Opsi A", "b": "Opsi B", "c": "Opsi C", "d": "Opsi D"';
        } elseif ($jenisSoal === 2) {
            $prompt .= '"a": "Opsi A", "b": "Opsi B", "c": "Opsi C", "d": "Opsi D", "e": "Opsi E"';
        } elseif ($jenisSoal === 4) {
            $prompt .= '"jawaban": "kata/kalimat jawaban singkat"';
        } elseif ($jenisSoal === 5) {
            $prompt .= '"pedoman_penskoran": "Rubrik/kriteria penilaian uraian jawaban"';
        } elseif ($jenisSoal === 6) {
            $prompt .= '"items": ["Langkah 1", "Langkah 2", "Langkah 3", "Langkah 4"]';
        } elseif ($jenisSoal === 3) {
            $prompt .= '"left": ["Pernyataan A", "Pernyataan B", "Pernyataan C"], "right": ["Jawaban A", "Jawaban B", "Jawaban C"]';
        } elseif ($jenisSoal === 7) {
            $prompt .= '"teks": "Teks rumpang dengan {{blank1}}, {{blank2}} sebagai penanda", "jawaban": {"blank1": "jawaban1", "blank2": "jawaban2"}';
        }

        $prompt .= '},
  "jawaban": ';

        if ($jenisSoal === 1) {
            $prompt .= '"a" // huruf kunci jawaban (a/b/c/d)';
        } elseif ($jenisSoal === 2) {
            $prompt .= '["a", "c"] // array huruf jawaban benar';
        } elseif ($jenisSoal === 3) {
            $prompt .= '{"0": "right_0", "1": "right_1", "2": "right_2"} // mapping indeks left ke right';
        } elseif ($jenisSoal === 4) {
            $prompt .= '"kata kunci jawaban"';
        } elseif ($jenisSoal === 5) {
            $prompt .= '"Pedoman/kriteria jawaban essay"';
        } elseif ($jenisSoal === 6) {
            $prompt .= '["1", "2", "3", "4"] // urutan yang benar (array indeks terurut)';
        } elseif ($jenisSoal === 7) {
            $prompt .= '{"blank1": "jawaban1", "blank2": "jawaban2", ...}';
        }

        $prompt .= ',
  "pembahasan": "Penjelasan mengapa jawaban tersebut benar, termasuk analisis opsi pengecoh bila ada."
}' . "\n\n";
        $prompt .= "ATURAN:\n";
        $prompt .= "- Output HANYA JSON murni, tanpa markdown, tanpa teks lain.\n";
        $prompt .= "- Sesuaikan tingkat kesulitan dengan level kognitif {$level}.\n";
        $prompt .= "- Soal harus kontekstual dan relevan dengan materi {$materi}.\n";
        $prompt .= "- Gunakan KKO Bloom sesuai level {$level}.\n";
        $prompt .= "- Untuk PG, sertakan minimal 1 pengecoh yang plausible.\n";
        $prompt .= "\nSTANDAR AKM & ASESMEN NASIONAL:\n";
        $prompt .= "1. Butir wajib mengikuti Standar Asesmen Kompetensi Minimum (AKM): berstimulus kontekstual (kasus/data/teks/grafik) yang memagari jawaban (siswa tidak dapat menjawab tanpa stimulus).\n";
        $prompt .= "2. Semua opsi pengecoh homogen secara gramatikal, panjang serupa, dan mewakili miskonsepsi umum — hindari pengecoh asal-asalan.\n";
        $prompt .= "3. Kunci jawaban unik, pasti benar terhadap stimulus, dan untuk PG tersebar bervariasi antar opsi (jangan selalu C).\n";
        $prompt .= "4. Kalimat per stimulus dan per butir pendek, jelas, tidak ambigu, sesuai perkembangan bahasa peserta didik.\n";
        $prompt .= "5. Untuk esai/uraian sertakan pedoman penskoran dengan kriteria/kata kunci yang jelas.\n";

        $aiResult = $this->ai->generateContent($apiKey, $prompt, $aiModel);
        if (!$aiResult['success']) {
            return $this->respond(false, 'AI gagal: ' . $aiResult['message'], null, null, 500);
        }

        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        if (!$parseResult['success']) {
            return $this->respond(false, 'Format respons AI tidak valid: ' . $parseResult['message'], null, null, 500);
        }

        $data = $parseResult['data'];

        if ($save) {
            $opsiJson = '{}';
            $jawabanJson = '{}';

            if ($jenisSoal === 1) {
                $opsi = $data['opsi'] ?? [];
                $opsiJson = json_encode($opsi, JSON_UNESCAPED_UNICODE);
                $jawabanJson = json_encode($data['jawaban'] ?? '', JSON_UNESCAPED_UNICODE);
            } elseif ($jenisSoal === 2) {
                $opsi = $data['opsi'] ?? [];
                $opsiJson = json_encode($opsi, JSON_UNESCAPED_UNICODE);
                $jawabanJson = json_encode($data['jawaban'] ?? [], JSON_UNESCAPED_UNICODE);
            } elseif (in_array($jenisSoal, [3, 4, 5, 6, 7], true)) {
                $opsiJson = json_encode($data['opsi'] ?? [], JSON_UNESCAPED_UNICODE);
                $jawabanJson = json_encode($data['jawaban'] ?? '', JSON_UNESCAPED_UNICODE);
            }

            $updateData = [
                'butir_soal' => $data['soal'] ?? '',
                'opsi_json' => $opsiJson,
                'jawaban_benar_json' => $jawabanJson,
                'pembahasan' => $data['pembahasan'] ?? '',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $ok = $this->items->update($itemId, $updateData);
            if (!$ok) {
                return $this->respond(false, 'Gagal menyimpan konten soal', null, $this->items->errors(), 500);
            }

            $this->audit->add((int) $kartu->id_kartu, 'generate_soal_content', json_encode(['id_item' => $itemId], JSON_UNESCAPED_UNICODE));
            $this->logging->saveLog(5, 'Generate konten AI butir kartu soal: ' . $kartu->judul . ' (id_item: ' . $itemId . ')');

            $updatedItem = $this->items->getItemsWithTujuan((int) $kartu->id_kartu);
            return $this->respond(true, 'Konten soal berhasil di-generate dan disimpan', $updatedItem ?: $updateData);
        }

        return $this->respond(true, 'Konten soal berhasil di-generate', $data);
    }

    public function saveGeneratedItems()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        try {
            $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
            
            // Fallback: read from JSON body if uuid is empty
            if ($uuid === '') {
                try {
                    $jsonBody = $this->request->getJSON(true);
                    if (is_array($jsonBody)) {
                        $uuid = trim((string) ($jsonBody['kartu_uuid'] ?? $jsonBody['uuid'] ?? ''));
                    }
                } catch (\Throwable $th) {
                    // Not a JSON body - ignore
                }
            }
            
            $kartu = $this->kartu->findByUuid($uuid);
            if (!$kartu) {
                return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
            }
            $guard = $this->ensureCanManage($kartu);
            if ($guard) {
                return $guard;
            }

            // Check editable - return HTTP 200 with status:false so JS .done() handles it
            if (!$this->canEditKartuContent($kartu)) {
                $wfStatus = $this->getWorkflowStatusValue($kartu);
                return $this->respond(false, 'Kartu Soal sedang dalam status "' . ucfirst($wfStatus) . '". Kembalikan ke Draft/Revisi untuk menambah butir soal.', [
                    'workflow_status' => $wfStatus,
                ]);
            }

            // Decode items from POST (jQuery $.post sends form-encoded JSON string)
            $rawItems = $this->request->getPost('items');
            $itemsPayload = $this->decodeJsonPayload($rawItems);

            // Fallback: try reading raw JSON body
            if (empty($itemsPayload)) {
                try {
                    $jsonBody = $this->request->getJSON(true);
                    if (is_array($jsonBody) && isset($jsonBody['items']) && is_array($jsonBody['items'])) {
                        $itemsPayload = $jsonBody['items'];
                    }
                } catch (\Throwable $th) {
                    // Not a JSON body - ignore
                }
            }

            if (!is_array($itemsPayload) || empty($itemsPayload)) {
                log_message('warning', '[Cbtkartusoal::saveGeneratedItems] Empty items payload. Raw type=' . gettype($rawItems) . ' val=' . mb_substr((string) $rawItems, 0, 200));
                return $this->respond(false, 'Data soal hasil AI kosong. Pastikan preview sudah di-generate sebelum simpan.');
            }
            if (count($itemsPayload) > 100) {
                return $this->respond(false, 'Maksimal simpan 100 butir sekali proses.');
            }

            $tujuanOptions = $this->getTujuanOptionsForKartu($kartu);
            $allowedTujuanIds = array_map(static fn($tp) => (int) ($tp->id_tujuan ?? 0), $tujuanOptions);
            $allowedTujuanIds = array_values(array_filter($allowedTujuanIds, static fn($v) => $v > 0));
            $defaultTujuanId = (int) ($allowedTujuanIds[0] ?? 0);

            if ($defaultTujuanId <= 0) {
                return $this->respond(false, 'Tidak ada Tujuan Pembelajaran yang valid untuk kartu ini. Pastikan TP sudah dibuat untuk mapel terkait.');
            }

            $nomor = $this->items->nextNomor((int) $kartu->id_kartu);
            $savedCount = 0;
            $errors = [];

            foreach ($itemsPayload as $idx => $item) {
                if (!is_array($item)) {
                    $errors[] = 'Item #' . ($idx + 1) . ' tidak valid.';
                    continue;
                }

                $jenisSoal = (int) ($item['jenis_soal'] ?? 1);
                if ($jenisSoal < 1 || $jenisSoal > 7) {
                    $jenisSoal = 1;
                }
                $idTujuan = (int) ($item['id_tujuan'] ?? 0);
                if ($idTujuan <= 0 || !in_array($idTujuan, $allowedTujuanIds, true)) {
                    $idTujuan = $defaultTujuanId;
                }

                $estimasiWaktu = max(1, min(20, (int) ($item['estimasi_waktu'] ?? 2)));

                // Normalize materi and indikator
                $materi = trim((string) ($item['materi'] ?? ''));
                $indikator = trim((string) ($item['indikator_soal'] ?? ''));

                // Auto-fill if one is missing
                if ($materi === '' && $indikator !== '') {
                    $materi = mb_substr(strip_tags($indikator), 0, 120);
                }
                if ($indikator === '' && $materi !== '') {
                    $indikator = 'Peserta didik mampu menjelaskan materi: ' . $materi;
                }
                // Auto-generate minimal content if both are empty
                if ($materi === '') {
                    $materi = 'Materi soal #' . ($idx + 1);
                }
                if ($indikator === '') {
                    $indikator = 'Peserta didik dapat menjawab soal terkait ' . $materi;
                }

                $tujuanRow = null;
                foreach ($tujuanOptions as $tp) {
                    if ((int) ($tp->id_tujuan ?? 0) === $idTujuan) {
                        $tujuanRow = $tp;
                        break;
                    }
                }
                if (!$tujuanRow) {
                    $errors[] = 'Item #' . ($idx + 1) . ': tujuan pembelajaran tidak ditemukan.';
                    continue;
                }

                try {
                    $idItem = $this->items->insert([
                        'kartu_id'          => (int) $kartu->id_kartu,
                        'nomor'             => $nomor,
                        'bank_id'           => null,
                        'soal_id'           => null,
                        'jenis_soal'        => $jenisSoal,
                        'id_tujuan'         => $idTujuan,
                        'cp'                => ($item['kode_cp'] ?? '') !== '' ? (string) $item['kode_cp'] : (trim((string) ($tujuanRow->kode_cp ?? '')) !== '' ? (string) $tujuanRow->kode_cp : null),
                        'tujuan'            => trim((string) ($tujuanRow->tujuan ?? '')) !== '' ? (string) $tujuanRow->tujuan : null,
                        'estimasi_waktu'    => $estimasiWaktu,
                        'materi'            => $materi,
                        'indikator_soal'    => $indikator,
                        'level_kognitif'    => $this->normalizeLevelKognitif((string) ($item['level_kognitif'] ?? ''), $jenisSoal),
                        'butir_soal'        => '',
                        'opsi_json'         => '{}',
                        'jawaban_benar_json' => '{}',
                        'pembahasan'        => '',
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ], true);

                    if ($idItem) {
                        $savedCount++;
                        $nomor++;
                    } else {
                        $modelErrors = $this->items->errors();
                        $errMsg = 'Item #' . ($idx + 1) . ': gagal insert. ' . ($modelErrors ? json_encode($modelErrors) : '');
                        $errors[] = $errMsg;
                        log_message('error', '[Cbtkartusoal::saveGeneratedItems] ' . $errMsg);
                    }
                } catch (\Throwable $insertErr) {
                    $errors[] = 'Item #' . ($idx + 1) . ': ' . $insertErr->getMessage();
                    log_message('error', '[Cbtkartusoal::saveGeneratedItems] Insert error: ' . $insertErr->getMessage());
                }
            }

            if ($savedCount <= 0) {
                log_message('error', '[Cbtkartusoal::saveGeneratedItems] No items saved. Errors: ' . implode(' | ', array_slice($errors, 0, 5)));
                return $this->respond(false, 'Tidak ada butir soal yang berhasil disimpan.', [
                    'errors' => $errors,
                    'items_received' => count($itemsPayload),
                ]);
            }

            $this->audit->add((int) $kartu->id_kartu, 'save_generated_ai', json_encode(['saved' => $savedCount], JSON_UNESCAPED_UNICODE));
            $this->logging->saveLog(5, 'Simpan item kartu soal: ' . $kartu->judul . ' (' . $savedCount . ' item)');

            return $this->respond(true, $savedCount . ' butir soal AI berhasil disimpan.', [
                'saved_count' => $savedCount,
                'errors' => $errors,
            ]);
        } catch (\Throwable $th) {
            log_message('error', '[Cbtkartusoal::saveGeneratedItems] Unhandled: ' . $th->getMessage() . "\n" . $th->getTraceAsString());
            return $this->respond(false, 'Terjadi kesalahan sistem: ' . $th->getMessage());
        }
    }

    public function workflowCheck()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $mapelId = (int) ($this->request->getGet('mapel_id') ?? 0);
        $tpId = (int) ($this->request->getGet('tp_id') ?? 0);
        $smtId = (int) ($this->request->getGet('smt_id') ?? 0);
        if ($mapelId <= 0) {
            return $this->respond(false, 'Mapel belum dipilih', null, null, 422);
        }

        if (!$this->isGuru()) {
            return $this->respond(true, 'OK', ['tujuan_count' => 1, 'ready' => true]);
        }

        $tujuanList = $this->getTujuanByMapelForCurrentGuru($mapelId, $tpId > 0 ? $tpId : null, $smtId > 0 ? $smtId : null);
        $tujuanCount = count($tujuanList);
        $ready = $tujuanCount > 0;
        $message = $ready
            ? 'Siap. Tujuan Pembelajaran untuk mapel ini sudah tersedia.'
            : 'Mapel ini belum memiliki Tujuan Pembelajaran. Lengkapi dulu di menu Guru > Tujuan Pembelajaran.';

        return $this->respond($ready, $message, [
            'tujuan_count' => $tujuanCount,
            'ready' => $ready,
        ], null, $ready ? 200 : 422);
    }

    public function tujuanByMapel()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $mapelId = (int) ($this->request->getGet('mapel_id') ?? 0);
        $tpId = (int) ($this->request->getGet('tp_id') ?? 0);
        $smtId = (int) ($this->request->getGet('smt_id') ?? 0);
        if ($mapelId <= 0) {
            return $this->respond(false, 'Mapel belum dipilih', null, null, 422);
        }

        $tujuanList = $this->isGuru()
            ? $this->getTujuanByMapelForCurrentGuru($mapelId, $tpId > 0 ? $tpId : null, $smtId > 0 ? $smtId : null)
            : [];
        $data = [];
        foreach ($tujuanList as $tp) {
            $data[] = [
                'id_tujuan' => (int) ($tp->id_tujuan ?? 0),
                'kode' => (string) ($tp->kode ?? ''),
                'tujuan' => (string) ($tp->tujuan ?? ''),
                'fase' => (string) ($tp->fase ?? ''),
                'kode_cp' => (string) ($tp->kode_cp ?? ''),
                'elemen_cp' => (string) ($tp->elemen_cp ?? ''),
                'materi_pokok' => (string) ($tp->materi_pokok ?? ''),
                'tp_sikap' => (string) ($tp->tp_sikap ?? ''),
                'tp_keterampilan' => (string) ($tp->tp_keterampilan ?? ''),
                'kriteria_ketercapaian' => (string) ($tp->kriteria_ketercapaian ?? ''),
                'level_kognitif' => (string) ($tp->level_kognitif ?? ''),
                'tingkat' => (string) ($tp->tingkat ?? ''),
            ];
        }

        return $this->respond(true, 'OK', $data);
    }

    public function optionsByPeriode()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $tpId = (int) ($this->request->getGet('tp_id') ?? 0);
        $smtId = (int) ($this->request->getGet('smt_id') ?? 0);
        if (!$this->isValidTpSmt($tpId, $smtId)) {
            return $this->respond(false, 'Periode tidak valid', null, null, 422);
        }

        $mapel = $this->getMapelForCurrentUser($tpId, $smtId);
        $kelas = $this->getKelasForCurrentUser($tpId, $smtId);
        $result = [
            'mapel' => array_map(static function ($row) {
                return [
                    'id' => (int) ($row->id_mapel ?? 0),
                    'nama' => (string) ($row->nama_mapel ?? '-'),
                ];
            }, $mapel),
            'kelas' => array_map(static function ($row) {
                return [
                    'id' => (int) ($row->id_kelas ?? 0),
                    'nama' => (string) ($row->nama_kelas ?? '-'),
                    'level_id' => (string) ($row->level_id ?? ''),
                ];
            }, $kelas),
        ];

        return $this->respond(true, 'OK', $result);
    }

    public function submitForApproval()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }

        if (!$this->canEditKartuContent($kartu)) {
            return $this->respond(false, 'Kartu sudah disubmit/approved.', ['workflow_status' => $this->getWorkflowStatusValue($kartu)], null, 422);
        }

        $totalItems = $this->items->where('kartu_id', (int) $kartu->id_kartu)->countAllResults();
        if ($totalItems <= 0) {
            return $this->respond(false, 'Kartu belum memiliki item soal.', null, ['items' => 'Minimal 1 item'], 422);
        }

        $ok = $this->kartu->update((int) $kartu->id_kartu, [
            'workflow_status' => 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
            'approved_at' => null,
            'approved_by' => null,
            // Catatan revisi TIDAK dikosongkan di sini: guru perlu tahu apa
            // yang tadi diperbaiki, dan reviewer perlu konteks sebelum menyetujui.
            // Catatan akan ditimpa saat kartu disetujui atau direvisi lagi.
            'status' => 2,
            'updated_by' => (int) $this->getUserId(),
        ]);
        if (!$ok) {
            return $this->respond(false, 'Gagal submit kartu soal', null, $this->kartu->errors(), 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'submit_approval', json_encode(['total_items' => $totalItems], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Submit approval kartu soal: ' . ($kartu->judul ?? '-'));
        $this->notifyTelegram('submit', $kartu);
        return $this->respond(true, 'Kartu soal berhasil disubmit untuk approval.');
    }

    public function approve()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        if (!$this->isApprovalRole()) {
            return $this->respond(false, 'Hanya admin/panitia yang dapat approve.', null, null, 403);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $note = trim((string) ($this->request->getPost('note') ?? ''));
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        $workflow = $this->getWorkflowStatusValue($kartu);
        if (!in_array($workflow, ['submitted', 'revisi', 'draft'], true)) {
            return $this->respond(false, 'Status kartu tidak valid untuk approval.', ['workflow_status' => $workflow], null, 422);
        }

        $ok = $this->kartu->update((int) $kartu->id_kartu, [
            'workflow_status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => (int) $this->getUserId(),
            'approval_note' => $note !== '' ? $note : null,
            'status' => 2,
            'updated_by' => (int) $this->getUserId(),
        ]);
        if (!$ok) {
            return $this->respond(false, 'Gagal approve kartu soal', null, $this->kartu->errors(), 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'approve', json_encode(['note' => $note], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Approve kartu soal: ' . ($kartu->judul ?? '-'));
        $this->notifyTelegram('approve', $kartu, $note);
        return $this->respond(true, 'Kartu soal berhasil diapprove.');
    }

    public function returnForRevision()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        if (!$this->isApprovalRole()) {
            return $this->respond(false, 'Hanya admin/panitia yang dapat mengembalikan revisi.', null, null, 403);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $note = trim((string) ($this->request->getPost('note') ?? ''));
        if ($note === '') {
            return $this->respond(false, 'Catatan revisi wajib diisi.', null, ['note' => 'Catatan revisi wajib'], 422);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        $ok = $this->kartu->update((int) $kartu->id_kartu, [
            'workflow_status' => 'revisi',
            'approved_at' => null,
            'approved_by' => null,
            'approval_note' => $note,
            'status' => 1,
            'updated_by' => (int) $this->getUserId(),
        ]);
        if (!$ok) {
            return $this->respond(false, 'Gagal mengembalikan kartu untuk revisi', null, $this->kartu->errors(), 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'return_revision', json_encode(['note' => $note], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Return revisi kartu soal: ' . ($kartu->judul ?? '-'));
        $this->notifyTelegram('revisi', $kartu, $note);
        return $this->respond(true, 'Kartu soal dikembalikan untuk revisi.');
    }

    /**
     * Batalkan persetujuan (revoke approval) — hanya untuk admin/panitia.
     * Mengembalikan kartu dari status "approved" ke "draft" agar bisa diedit kembali.
     */
    public function cancelApproval()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        if (!$this->isApprovalRole()) {
            return $this->respond(false, 'Hanya admin/panitia yang dapat membatalkan persetujuan.', null, null, 403);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $note = trim((string) ($this->request->getPost('note') ?? ''));
        if ($note === '') {
            return $this->respond(false, 'Alasan pembatalan wajib diisi.', null, ['note' => 'Alasan wajib diisi'], 422);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        $workflow = $this->getWorkflowStatusValue($kartu);
        if ($workflow !== 'approved') {
            return $this->respond(false, 'Hanya kartu yang sudah disetujui yang dapat dibatalkan persetujuannya.', [
                'workflow_status' => $workflow,
            ], null, 422);
        }

        $ok = $this->kartu->update((int) $kartu->id_kartu, [
            'workflow_status' => 'draft',
            'approved_at'     => null,
            'approved_by'     => null,
            'approval_note'   => 'DIBATALKAN: ' . $note,
            'status'          => 1,
            'updated_by'      => (int) $this->getUserId(),
        ]);
        if (!$ok) {
            return $this->respond(false, 'Gagal membatalkan persetujuan', null, $this->kartu->errors(), 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'cancel_approval', json_encode([
            'note'        => $note,
            'revoked_by'  => (int) $this->getUserId(),
            'prev_status' => 'approved',
        ], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, 'Batalkan approval kartu soal: ' . ($kartu->judul ?? '-') . ' — Alasan: ' . $note);
        $this->notifyTelegram('cancel', $kartu, $note);

        return $this->respond(true, 'Persetujuan berhasil dibatalkan. Kartu dikembalikan ke status Draft.');
    }

    public function apiGetItems(string $uuid)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }

        $idsOnly = (int) ($this->request->getGet('ids_only') ?? 0) === 1;
        if ($idsOnly) {
            $rows = $this->items->select('id_item')->where('kartu_id', (int) $kartu->id_kartu)->orderBy('nomor', 'ASC')->findAll();
            $ids = array_map(static fn($row) => (int) ($row->id_item ?? 0), $rows);
            return $this->respond(true, 'OK', ['order' => array_values(array_filter($ids))]);
        }

        // Lightweight endpoint: return all nomor+id_item for duplicate detection across pages
        $allNomors = (int) ($this->request->getGet('all_nomors') ?? 0) === 1;
        if ($allNomors) {
            $rows = $this->items
                ->select('id_item, nomor')
                ->where('kartu_id', (int) $kartu->id_kartu)
                ->orderBy('nomor', 'ASC')
                ->findAll();
            $nomors = array_map(static fn($row) => [
                'id_item' => (int) ($row->id_item ?? 0),
                'nomor'   => (int) ($row->nomor ?? 0),
            ], $rows);
            return $this->respond(true, 'OK', [
                'nomors'     => array_values($nomors),
                'next_nomor' => empty($nomors) ? 1 : (max(array_column($nomors, 'nomor')) + 1),
            ]);
        }

        // Support: check duplicate by nomor values (comma-separated)
        $checkDuplicates = trim((string) ($this->request->getGet('check_duplicates') ?? ''));
        if ($checkDuplicates !== '') {
            $nomorToCheck = array_values(array_unique(array_filter(array_map('intval', explode(',', $checkDuplicates)))));
            if (empty($nomorToCheck)) {
                return $this->respond(true, 'OK', ['duplicates' => []]);
            }
            $existingNomors = $this->items
                ->select('nomor')
                ->where('kartu_id', (int) $kartu->id_kartu)
                ->whereIn('nomor', $nomorToCheck)
                ->findAll();
            $dupeNomors = array_map(static fn($r) => (int) ($r->nomor ?? 0), $existingNomors);
            return $this->respond(true, 'OK', ['duplicates' => array_values($dupeNomors)]);
        }

        $page    = (int) ($this->request->getGet('page')     ?? 0);
        $perPage = (int) ($this->request->getGet('per_page') ?? 0);

        // Optional server filters
        $filterTujuanId = (int) ($this->request->getGet('tujuan_id')  ?? 0);
        $filterJenis    = (int) ($this->request->getGet('jenis_soal') ?? 0);
        $search         = trim((string) ($this->request->getGet('q') ?? ''));

        // Filter + pagination dikerjakan di SQL (tidak lagi muat semua baris ke PHP)
        $filters = [
            'tujuan_id'  => $filterTujuanId,
            'jenis_soal' => $filterJenis,
            'q'          => $search,
        ];
        $kartuId = (int) $kartu->id_kartu;

        $total        = $this->items->countItemsWithTujuan($kartuId, $filters);
        $filterActive = $filterTujuanId > 0 || $filterJenis > 0 || $search !== '';

        $items = [];
        $meta  = null;
        if ($page > 0 && $perPage > 0) {
            $offset = ($page - 1) * $perPage;
            $allItems = $this->items->getItemsWithTujuan($kartuId, $filters, $perPage, $offset);
            $items  = $allItems;
            $meta   = [
                'total'              => $total,
                'page'               => $page,
                'per_page'           => $perPage,
                'total_pages'        => (int) ceil($total / max(1, $perPage)),
                'filter_active'      => $filterActive,
                'filter_tujuan_id'   => $filterTujuanId,
                'filter_jenis'       => $filterJenis,
                'search'             => $search,
                'summary_jenis'      => $this->items->getKartuSummary($kartuId),
                // Ringkasan global kartu (akurat lintas halaman, dipakai header saat tanpa filter)
                'total_all'          => $this->items->getItemsCount($kartuId),
                'total_estimasi_all' => $this->items->getTotalEstimasi($kartuId),
                'tujuan_unique_all'  => $this->items->getLinkedTujuanUniqueCount($kartuId),
            ];
        } else {
            $items = $this->items->getItemsWithTujuan($kartuId, $filters);
        }

        $jenisLabels = [
            1 => 'Pilihan Ganda',
            2 => 'Pilihan Ganda Kompleks',
            3 => 'Menjodohkan',
            4 => 'Isian Singkat',
            5 => 'Uraian / Essay',
            6 => 'Urutan',
            7 => 'Isian Rumpang',
        ];

        $mappedItems = [];
        foreach ($items as $item) {
            $butirSoal = trim((string) ($item['butir_soal'] ?? ''));
            if ($butirSoal === '' && !empty($item['bank_soal_teks'])) {
                $butirSoal = (string) $item['bank_soal_teks'];
            }

            $opsi = json_decode((string) ($item['opsi_json'] ?? ''), true);
            if (!is_array($opsi) || empty($opsi)) {
                $bankOpsi = [];
                foreach (['a' => 'bank_opsi_a', 'b' => 'bank_opsi_b', 'c' => 'bank_opsi_c', 'd' => 'bank_opsi_d', 'e' => 'bank_opsi_e'] as $k => $col) {
                    $v = trim((string) ($item[$col] ?? ''));
                    if ($v !== '') {
                        $bankOpsi[$k] = $v;
                    }
                }
                if (!empty($bankOpsi)) {
                    $opsi = $bankOpsi;
                }
            }

            $jawaban = json_decode((string) ($item['jawaban_benar_json'] ?? ''), true);
            if (($jawaban === null || $jawaban === '' || (is_array($jawaban) && empty($jawaban))) && !empty($item['bank_soal_jawaban'])) {
                $jawaban = trim((string) $item['bank_soal_jawaban']);
            }

            $pembahasan = trim((string) ($item['pembahasan'] ?? ''));
            if ($pembahasan === '' && !empty($item['bank_soal_pembahasan'])) {
                $pembahasan = (string) $item['bank_soal_pembahasan'];
            }

            $jenisId = (int) ($item['jenis_soal'] ?? $item['bank_soal_jenis'] ?? 1);
            if ($jenisId <= 0) $jenisId = 1;

            $mappedItems[] = [
                'id_item'                => (int) $item['id_item'],
                'nomor'                  => (int) $item['nomor'],
                'jenis_soal'             => $jenisId,
                'soal_id'                => (int) ($item['soal_id'] ?? 0),
                'id_tujuan'              => (int) ($item['id_tujuan'] ?? 0),
                'jenis_label'            => $jenisLabels[$jenisId] ?? 'Pilihan Ganda',
                'cp'                     => (string) ($item['cp'] ?? ''),
                'materi'                 => (string) ($item['materi'] ?? ''),
                'tujuan'                 => (string) ($item['tujuan'] ?? $item['tujuan_teks'] ?? ''),
                'tujuan_kode'            => (string) ($item['tujuan_kode'] ?? ''),
                'fase'                   => (string) ($item['fase'] ?? ''),
                'indikator_soal'         => (string) ($item['indikator_soal'] ?? ''),
                'level_kognitif'         => (string) ($item['level_kognitif'] ?? ''),
                'estimasi_waktu'         => (int) ($item['estimasi_waktu'] ?? 0),
                'catatan'                => (string) ($item['catatan'] ?? ''),
                'butir_soal'             => $butirSoal,
                'opsi_json'              => is_array($opsi) ? $opsi : [],
                'jawaban_benar_json'     => $jawaban,
                'pembahasan'             => $pembahasan,
                'bank_soal_nomor'        => (int) ($item['bank_soal_nomor'] ?? 0),
                // Full TP metadata from JOIN
                'tujuan_kode_cp'         => (string) ($item['tujuan_kode_cp'] ?? ''),
                'tujuan_elemen_cp'       => (string) ($item['tujuan_elemen_cp'] ?? ''),
                'tujuan_materi_pokok'    => (string) ($item['tujuan_materi_pokok'] ?? ''),
                'tujuan_tp_sikap'        => (string) ($item['tujuan_tp_sikap'] ?? ''),
                'tujuan_tp_keterampilan' => (string) ($item['tujuan_tp_keterampilan'] ?? ''),
                'tujuan_kktp'            => (string) ($item['tujuan_kktp'] ?? ''),
                'tujuan_level_kognitif'  => (string) ($item['tujuan_level_kognitif'] ?? ''),
            ];
        }

        if ($meta !== null) {
            return $this->respond(true, 'OK', [
                'items' => $mappedItems,
                'meta'  => $meta,
            ]);
        }

        return $this->respond(true, 'OK', $mappedItems);
    }

    /**
     * ARSIP: tidak di-routing (lihat Routes.php) & tanpa konsumen UI per audit.
     * Disimpan agar mudah diaktifkan kembali bila dibutuhkan.
     */
    public function apiGetKartu(string $uuid)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }

        $mapel = $this->master->getMapelById((int) ($kartu->mapel_id ?? 0));
        $kelas = $this->master->getKelasById((int) ($kartu->kelas_id ?? 0));
        $jenis = $this->cbt->getJenisUjianById((int) ($kartu->id_jenis ?? 0));

        return $this->respond(true, 'OK', [
            'id_kartu' => (int) $kartu->id_kartu,
            'uuid' => $kartu->uuid,
            'judul' => $kartu->judul,
            'deskripsi' => (string) ($kartu->deskripsi ?? ''),
            'mapel_id' => (int) ($kartu->mapel_id ?? 0),
            'mapel_nama' => $mapel->nama_mapel ?? '-',
            'kelas_id' => (int) ($kartu->kelas_id ?? 0),
            'kelas_nama' => $kelas->nama_kelas ?? '-',
            'id_jenis' => (int) ($kartu->id_jenis ?? 0),
            'jenis_nama' => (string) ($jenis->nama_jenis ?? '-'),
            'tp_id' => (int) ($kartu->tp_id ?? 0),
            'smt_id' => (int) ($kartu->smt_id ?? 0),
            'status' => (int) ($kartu->status ?? 1),
            'workflow_status' => $this->getWorkflowStatusValue($kartu),
            'submitted_at' => (string) ($kartu->submitted_at ?? ''),
            'approved_at' => (string) ($kartu->approved_at ?? ''),
            'approved_by' => (int) ($kartu->approved_by ?? 0),
            'approval_note' => (string) ($kartu->approval_note ?? ''),
        ]);
    }

    public function apiGetTujuan(string $uuid)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }

        // Ambil data TP berdasarkan kartu soal
        $tujuanOptions = $this->getTujuanOptionsForKartu($kartu);

        // Format ulang data untuk response agar aman dan komprehensif
        $data = array_map(function ($tp) {
            return [
                'id_tujuan' => (int) ($tp->id_tujuan ?? 0),
                'kode' => (string) ($tp->kode ?? ''),
                'fase' => (string) ($tp->fase ?? ''),
                'tujuan' => trim((string) ($tp->tujuan ?? '')),
                'kode_cp' => (string) ($tp->kode_cp ?? ''),
                'elemen_cp' => (string) ($tp->elemen_cp ?? ''),
                'materi_pokok' => (string) ($tp->materi_pokok ?? ''),
                'kriteria_ketercapaian' => (string) ($tp->kriteria_ketercapaian ?? ''),
                'tp_sikap' => (string) ($tp->tp_sikap ?? ''),
                'tp_keterampilan' => (string) ($tp->tp_keterampilan ?? '')
            ];
        }, $tujuanOptions);

        return $this->respond(true, 'OK', $data);
    }

    /**
     * ARSIP: tidak di-routing (lihat Routes.php) & tanpa konsumen UI per audit.
     * Disimpan agar mudah diaktifkan kembali bila dibutuhkan.
     */
    public function apiBanks()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $mapelId = (int) ($this->request->getGet('mapel_id') ?? 0);
        $tpId = (int) ($this->request->getGet('tp_id') ?? 0);
        $smtId = (int) ($this->request->getGet('smt_id') ?? 0);

        $builder = db_connect()->table('cbt_bank_soal b')
            ->select('b.id_bank, b.bank_kode, b.bank_nama, b.bank_mapel_id, b.id_tp, b.id_smt, b.bank_guru_id, b.kartu_soal_id')
            ->where('b.status', 1);

        if ($mapelId > 0) {
            $builder->where('b.bank_mapel_id', $mapelId);
        }
        if ($tpId > 0) {
            $builder->where('b.id_tp', $tpId);
        }
        if ($smtId > 0) {
            $builder->where('b.id_smt', $smtId);
        }
        if ($this->isGuru()) {
            $guruProfile = $this->getCurrentGuruProfile();
            if ($guruProfile && !empty($guruProfile->id_guru)) {
                $builder->where('b.bank_guru_id', (int) $guruProfile->id_guru);
            } else {
                return $this->respond(false, 'Data guru tidak ditemukan', null, null, 403);
            }
        }

        $rows = $builder->orderBy('b.id_bank', 'DESC')->get()->getResult() ?? [];
        $data = array_map(static function ($row) {
            return [
                'id_bank' => (int) ($row->id_bank ?? 0),
                'kode' => (string) ($row->bank_kode ?? ''),
                'nama' => !empty($row->bank_nama) ? (string) $row->bank_nama : (string) ($row->bank_kode ?? ''),
                'mapel_id' => (int) ($row->bank_mapel_id ?? 0),
                'tp_id' => (int) ($row->id_tp ?? 0),
                'smt_id' => (int) ($row->id_smt ?? 0),
                'kartu_soal_id' => (int) ($row->kartu_soal_id ?? 0),
            ];
        }, $rows);

        return $this->respond(true, 'OK', $data);
    }

    /**
     * Ringkasan ringan soal per bank. Guard kepemilikan disamakan dengan
     * apiSoalFull/apiSoalByNomor agar teks soal tidak bisa diintip lintas pemilik.
     * ARSIP: saat ini tidak di-routing (lihat Routes.php).
     */
    public function apiSoalByBank(int $bankId)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        if ($bankId <= 0) {
            return $this->respond(false, 'Bank tidak valid', null, null, 422);
        }

        if (!$this->resolveBankForKartuAccess($bankId)) {
            return $this->respond(false, 'Paket/bank tidak ditemukan atau akses ditolak', null, null, 404);
        }

        $rows = db_connect()->table('cbt_soal')
            ->select('id_soal, bank_id, nomor_soal, jenis, soal')
            ->where('bank_id', $bankId)
            ->orderBy('nomor_soal', 'ASC')
            ->get()
            ->getResult() ?? [];

        $data = array_map(static function ($row) {
            return [
                'id_soal' => (int) ($row->id_soal ?? 0),
                'bank_id' => (int) ($row->bank_id ?? 0),
                'nomor_soal' => (int) ($row->nomor_soal ?? 0),
                'jenis' => (int) ($row->jenis ?? 1),
                'soal' => (string) ($row->soal ?? ''),
            ];
        }, $rows);

        return $this->respond(true, 'OK', $data);
    }

    /**
     * Dashboard pemilik kartu hanya boleh mengakses bank/paket yang lahir dari
     * kartu miliknya (guru). Admin/panitia diizinkan untuk semua.
     */
    private function resolveBankForKartuAccess(int $bankId): ?object
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('cbt_bank_soal') || $bankId <= 0) {
            return null;
        }
        $bank = $db->table('cbt_bank_soal')->where('id_bank', $bankId)->get()->getRow();
        if (!$bank) {
            return null;
        }
        if ($this->isAdmin() || $this->isPanitia()) {
            return $bank;
        }
        if (!$this->isGuru()) {
            return null;
        }
        $kartuId = (int) ($bank->kartu_soal_id ?? 0);
        if ($kartuId <= 0) {
            return null;
        }
        $kartu = $this->kartu->find($kartuId);
        if (!$kartu) {
            return null;
        }
        return (int) ($kartu->created_by ?? 0) === (int) $this->getUserId() ? $bank : null;
    }

    private function mapBankSoalForPreview($soal): array
    {
        $jenis = (int) ($soal->jenis ?? 1);
        if ($jenis <= 0) {
            $jenis = 1;
        }

        $opsi = [];
        foreach (['a' => 'opsi_a', 'b' => 'opsi_b', 'c' => 'opsi_c', 'd' => 'opsi_d', 'e' => 'opsi_e'] as $k => $col) {
            $v = $soal->{$col} ?? null;
            if (is_string($v)) {
                $v = trim($v);
                if ($v !== '') {
                    $opsi[$k] = $this->cbt->maybeUnserialize($v);
                }
            } elseif ($v !== null && $v !== '') {
                $opsi[$k] = $v;
            }
        }

        return [
            'id_soal'     => (int) ($soal->id_soal ?? 0),
            'bank_id'     => (int) ($soal->bank_id ?? 0),
            'nomor_soal'  => (int) ($soal->nomor_soal ?? 0),
            'jenis'       => $jenis,
            'tampilkan'   => (int) ($soal->tampilkan ?? 1),
            'soal'        => (string) ($soal->soal ?? ''),
            'opsi'        => $opsi,
            'jawaban'     => $this->cbt->maybeUnserialize($soal->jawaban ?? null),
            'deskripsi'   => (string) ($soal->deskripsi ?? ''),
            'mapel_id'    => (int) ($soal->mapel_id ?? 0),
        ];
    }

    public function apiPaketBanks(string $uuid)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }
        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }
        return $this->respond(true, 'OK', $this->getPaketBanksForKartu((int) $kartu->id_kartu));
    }

    public function apiSoalFull(int $bankId)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        $bank = $this->resolveBankForKartuAccess($bankId);
        if (!$bank) {
            return $this->respond(false, 'Paket/bank tidak ditemukan atau akses ditolak', null, null, 404);
        }
        $soals = $this->cbt->getAllSoalByBank($bankId);
        $mapped = [];
        foreach ($soals as $s) {
            $mapped[] = $this->mapBankSoalForPreview($s);
        }

        return $this->respond(true, 'OK', [
            'bank' => [
                'id_bank'   => $bankId,
                'bank_kode' => (string) ($bank->bank_kode ?? ''),
                'bank_nama' => !empty($bank->bank_nama) ? (string) $bank->bank_nama : (string) ($bank->bank_kode ?? ''),
            ],
            'soals' => $mapped,
        ]);
    }

    public function apiSoalByNomor(int $bankId, int $nomor)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        $bank = $this->resolveBankForKartuAccess($bankId);
        if (!$bank) {
            return $this->respond(false, 'Paket/bank tidak ditemukan atau akses ditolak', null, null, 404);
        }
        // nomor_soal bank bersifat per-jenis (PG 1..N, Essex 1..M, ...), jadi kunci
        // pencarian perlu jenis agar deterministik saat kartu punya beragam jenis soal.
        $jenis = (int) ($this->request->getGet('jenis') ?? 0);
        $builder = \Config\Database::connect()->table('cbt_soal')
            ->where('bank_id', $bankId)
            ->where('nomor_soal', $nomor);
        if ($jenis > 0) {
            $builder->where('jenis', $jenis);
        }
        // Deterministik: tanpa filter jenis bisa ada beberapa baris — ambil yang pertama.
        $soal = $builder->orderBy('id_soal', 'ASC')->limit(1)->get()->getRow();
        if (!$soal) {
            return $this->respond(false, 'Soal nomor ' . $nomor . ' tidak tersedia di paket ini', null, null, 404);
        }
        return $this->respond(true, 'OK', $this->mapBankSoalForPreview($soal));
    }

    public function apiSearchBankSoal(string $uuid)
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu soal tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }

        $tujuanId = max(0, (int) $this->request->getGet('tujuan_id'));
        $jenis = (string) $this->request->getGet('jenis');
        if ($jenis !== '' && !in_array($jenis, ['1', '2', '3', '4', '5', '6', '7'], true)) {
            $jenis = '';
        }
        $kesulitan = (string) $this->request->getGet('kesulitan');
        $search = (string) $this->request->getGet('search');

        $builder = db_connect()->table('cbt_soal s')
            ->select('s.id_soal, s.jenis, s.soal, s.kesulitan, s.tujuan_id, b.bank_kode')
            ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id')
            ->where('b.bank_mapel_id', $kartu->mapel_id)
            ->where('b.id_tp', $kartu->tp_id)
            ->where('b.id_smt', $kartu->smt_id)
            ->where('b.status', 1)
            ->limit(300)
            ->orderBy('s.id_soal', 'DESC');

        if ($this->isGuru()) {
            $guruProfile = $this->getCurrentGuruProfile();
            if ($guruProfile && !empty($guruProfile->id_guru)) {
                $builder->where('b.bank_guru_id', (int) $guruProfile->id_guru);
            } else {
                return $this->respond(false, 'Data guru tidak ditemukan', null, null, 403);
            }
        }

        if ($tujuanId > 0) {
            $builder->where('s.tujuan_id', $tujuanId);
        }
        if ($jenis !== '') {
            $builder->where('s.jenis', $jenis);
        }
        if ($kesulitan !== '') {
            $builder->where('s.kesulitan', $kesulitan);
        }
        if ($search !== '') {
            $builder->like('s.soal', $search);
        }

        $rows = $builder->get()->getResult() ?? [];
        $data = array_map(static function ($row) {
            return [
                'id_soal' => (int) ($row->id_soal ?? 0),
                'jenis' => (string) ($row->jenis ?? ''),
                'soal' => (string) ($row->soal ?? ''),
                'kesulitan' => (string) ($row->kesulitan ?? ''),
                'tujuan_id' => (int) ($row->tujuan_id ?? 0),
                'bank_kode' => (string) ($row->bank_kode ?? ''),
            ];
        }, $rows);

        return $this->respond(true, 'OK', $data);
    }

    public function importFromBank()
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }
        if (!$this->isAjax()) {
            return $this->respond(false, 'Invalid request', null, null, 400);
        }

        $uuid = trim((string) $this->request->getPost('uuid'));
        $soalIds = $this->request->getPost('soal_ids');
        if (!is_array($soalIds)) {
            return $this->respond(false, 'Data tidak lengkap', null, ['soal_ids' => 'List soal wajib berupa array'], 422);
        }

        $soalIds = array_values(array_unique(array_filter(array_map(static fn($v) => (int) $v, $soalIds), static fn($v) => $v > 0)));
        if ($uuid === '' || empty($soalIds)) {
            return $this->respond(false, 'Data tidak lengkap', null, ['uuid' => 'Kartu dan daftar soal wajib diisi'], 422);
        }
        if (count($soalIds) > 200) {
            return $this->respond(false, 'Maksimal import 200 soal per proses.', null, null, 422);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu soal tidak ditemukan', null, null, 404);
        }
        $guard = $this->ensureCanManage($kartu);
        if ($guard) {
            return $guard;
        }
        $editableGuard = $this->ensureEditableKartu($kartu);
        if ($editableGuard) {
            return $editableGuard;
        }

        $kartuId = (int) $kartu->id_kartu;
        $db = db_connect();

        $builder = $db->table('cbt_soal s')
            ->select('s.id_soal, s.bank_id, s.jenis, s.soal, s.kesulitan, s.tujuan_id, s.opsi_a, s.opsi_b, s.opsi_c, s.opsi_d, s.opsi_e, s.jawaban')
            ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id', 'inner')
            ->whereIn('s.id_soal', $soalIds)
            ->where('b.bank_mapel_id', (int) ($kartu->mapel_id ?? 0))
            ->where('b.id_tp', (int) ($kartu->tp_id ?? 0))
            ->where('b.id_smt', (int) ($kartu->smt_id ?? 0))
            ->where('b.status', 1);

        if ($this->isGuru()) {
            $guruProfile = $this->getCurrentGuruProfile();
            if ($guruProfile && !empty($guruProfile->id_guru)) {
                $builder->where('b.bank_guru_id', (int) $guruProfile->id_guru);
            } else {
                return $this->respond(false, 'Data guru tidak ditemukan', null, null, 403);
            }
        }

        $soalRows = $builder->get()->getResult() ?? [];
        if (empty($soalRows)) {
            return $this->respond(false, 'Tidak ada soal valid yang bisa diimport untuk scope kartu ini.', null, null, 422);
        }

        // Validasi TP: kumpulkan semua tujuan_id, cek apakah exist & aktif & sesuai mapel
        $tujuanIds = array_unique(array_filter(array_map(fn($s) => (int) ($s->tujuan_id ?? 0), $soalRows)));
        $validTujuanIds = [];
        $tujuanMateri = [];
        if (!empty($tujuanIds)) {
            $validTps = $this->tujuan
                ->select('id_tujuan, materi_pokok')
                ->whereIn('id_tujuan', $tujuanIds)
                ->where('id_mapel', (int) ($kartu->mapel_id ?? 0))
                ->where('is_active', 1)
                ->findAll();
            foreach ($validTps as $vtp) {
                $validTujuanIds[(int) $vtp->id_tujuan] = true;
                $tujuanMateri[(int) $vtp->id_tujuan] = (string) ($vtp->materi_pokok ?? '');
            }
        }

        $successCount = 0;
        $skippedCount = 0;
        $db->transStart();

        foreach ($soalRows as $soal) {
            $exists = $this->items
                ->where('kartu_id', $kartuId)
                ->where('soal_id', (int) ($soal->id_soal ?? 0))
                ->first();
            if ($exists) {
                $skippedCount++;
                continue;
            }

            $mapJenis = [
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                '5' => 5,
                '6' => 6,
                '7' => 7,
            ];
            $jenisItem = $mapJenis[(string) ($soal->jenis ?? '')] ?? 1;

            $opsi = null;
            // Salin opsi apa adanya untuk semua jenis (bank selalu punya kolom opsi_a..e);
            // format item native: {"a":..,"b":..,...} agar seragam dengan addItem/AI-generate.
            $opsi = [
                'a' => (string) ($soal->opsi_a ?? ''),
                'b' => (string) ($soal->opsi_b ?? ''),
                'c' => (string) ($soal->opsi_c ?? ''),
                'd' => (string) ($soal->opsi_d ?? ''),
                'e' => (string) ($soal->opsi_e ?? ''),
            ];
            if (trim(implode('', $opsi)) === '') {
                $opsi = null;
            }

            // Kanonik kesulitan bank (int) -> label kartu; invers dari peta generate.
            $kslRaw = strtolower(trim((string) ($soal->kesulitan ?? '')));
            $kslLabel = ['1' => 'mudah', 'mudah' => 'mudah', '2' => 'sedang', 'sedang' => 'sedang', '3' => 'sulit', 'sulit' => 'sulit'][$kslRaw] ?? 'sedang';
            $levelMap = ['mudah' => 'C2', 'sedang' => 'C3', 'sulit' => 'C4'];
            $level = $levelMap[$kslLabel] ?? null;

            // Format jawaban native item (sama seperti AI-generate/save):
            // PG (1) = JSON string "b", kompleks (2) = JSON array ["a","c"], lainnya = JSON string.
            $jawaban = trim((string) ($soal->jawaban ?? ''));
            if ($jenisItem === 2) {
                $decoded = json_decode($jawaban, true);
                $jawabanJson = is_array($decoded)
                    ? json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE)
                    : ($jawaban !== '' ? json_encode([$jawaban], JSON_UNESCAPED_UNICODE) : null);
            } else {
                $jawabanJson = $jawaban !== '' ? json_encode($jawaban, JSON_UNESCAPED_UNICODE) : null;
            }

            $itemData = [
                'kartu_id' => $kartuId,
                'nomor' => $this->items->nextNomor($kartuId),
                'bank_id' => (int) ($soal->bank_id ?? 0),
                'soal_id' => (int) ($soal->id_soal ?? 0),
                'jenis_soal' => $jenisItem,
                'id_tujuan' => isset($validTujuanIds[(int) ($soal->tujuan_id ?? 0)]) ? (int) $soal->tujuan_id : null,
                'butir_soal' => (string) ($soal->soal ?? ''),
                'level_kognitif' => $level,
                'indikator_soal' => '',
                'materi' => $tujuanMateri[(int) ($soal->tujuan_id ?? 0)] ?? '',
                'tingkat_kesulitan' => $kslLabel,
                'opsi_json' => $opsi ? json_encode($opsi, JSON_UNESCAPED_UNICODE) : null,
                'jawaban_benar_json' => $jawabanJson,
                'estimasi_waktu' => 5,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->items->insert($itemData, true)) {
                $successCount++;
            }
        }
        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->respond(false, 'Gagal import soal dari bank.', null, null, 500);
        }

        if ($successCount > 0) {
            $this->audit->add((int) $kartu->id_kartu, 'import_bank', json_encode([
                'imported' => $successCount,
                'skipped' => $skippedCount,
            ], JSON_UNESCAPED_UNICODE));
            $this->logging->saveLog(5, 'Import bank soal ke kartu: ' . ($kartu->judul ?? '-') . ' (' . $successCount . ')');
            return $this->respond(true, "$successCount soal berhasil diimport", [
                'imported' => $successCount,
                'skipped' => $skippedCount,
            ]);
        }

        return $this->respond(false, 'Tidak ada soal baru yang diimport. Kemungkinan semua soal sudah ada di kartu.', [
            'imported' => 0,
            'skipped' => $skippedCount,
        ], null, 422);
    }
    /**
     * Print Kartu Soal to PDF
     * 
     * @param string $uuid Kartu Soal UUID
     * @return Response PDF stream or redirect
     */
    public function printPdf(string $uuid)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return redirect()->to('cbtkartusoal')->with('error', 'Akses ditolak');
        }

        $items = $this->items->getItemsWithTujuan((int) $kartu->id_kartu);
        // Convert items ke object untuk konsistensi template
        $itemsObj = array_map(fn($i) => (object) $i, $items);

        // Load mapel and kelas names
        $mapel    = $this->master->getMapelById((int) ($kartu->mapel_id ?? 0));
        $kelas    = $this->master->getKelasById((int) ($kartu->kelas_id ?? 0));
        $jenisUjian = $this->cbt->getJenisUjianById((int) ($kartu->id_jenis ?? 0));

        // Get guru info (nama & NIP)
        $db   = \Config\Database::connect();
        $user = $db->table('users u')
            ->select('u.id, g.nama_guru, g.nip')
            ->join('master_guru g', "g.username = u.username", 'left')
            ->where('u.id', (int) ($kartu->created_by ?? 0))
            ->get()->getRow();
        $kartu->nama_guru = $user->nama_guru ?? 'Administrator';
        $kartu->nip_guru  = $user->nip ?? '-';
        
        $tpRow = null;
        foreach ($this->dashboard->getTahun() as $tpItem) {
            if ((int) ($tpItem->id_tp ?? 0) === (int) ($kartu->tp_id ?? 0)) {
                $tpRow = $tpItem;
                break;
            }
        }
        $smtRow = null;
        foreach ($this->dashboard->getSemester() as $smtItem) {
            if ((int) ($smtItem->id_smt ?? 0) === (int) ($kartu->smt_id ?? 0)) {
                $smtRow = $smtItem;
                break;
            }
        }

        // Build tujuanMap: id_tujuan => TP object (dengan 3 ranah)
        $tujuanIds = array_unique(array_filter(array_map(fn($i) => (int) ($i->id_tujuan ?? 0), $itemsObj)));
        $tujuanMap = [];
        if (!empty($tujuanIds)) {
            $tujuanRows = $this->tujuan->whereIn('id_tujuan', $tujuanIds)->findAll();
            foreach ($tujuanRows as $tp) {
                $tujuanMap[(int) $tp->id_tujuan] = $tp;
            }
        }

        $bankSoalsQuery = $db->table('cbt_soal s')
            ->select('s.*, b.bank_kode')
            ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id')
            ->where('b.kartu_soal_id', $kartu->id_kartu)
            ->orderBy('b.id_bank ASC, s.nomor_soal ASC')
            ->get();
        $bankSoals = $bankSoalsQuery ? $bankSoalsQuery->getResult() : [];

        $data = [
            'kartu'      => $kartu,
            'items'      => $itemsObj,
            'bank_soals' => $bankSoals,
            'tujuanMap'  => $tujuanMap,
            'mapel'      => $mapel,
            'kelas'      => $kelas,
            'jenis_ujian' => $jenisUjian,
            'tp_row'     => $tpRow,
            'smt_row'    => $smtRow,
            'jenis_soal_labels' => [
                1 => 'Pilihan Ganda',
                2 => 'Pilihan Ganda Kompleks',
                3 => 'Menjodohkan',
                4 => 'Isian Singkat',
                5 => 'Uraian / Essay',
                6 => 'Urutan',
                7 => 'Isian Rumpang',
            ],
            'level_kognitif_labels' => [
                'C1' => ['label' => 'Mengingat',   'color' => '#6c757d'],
                'C2' => ['label' => 'Memahami',    'color' => '#17a2b8'],
                'C3' => ['label' => 'Menerapkan',  'color' => '#007bff'],
                'C4' => ['label' => 'Menganalisis','color' => '#ffc107'],
                'C5' => ['label' => 'Mengevaluasi','color' => '#dc3545'],
                'C6' => ['label' => 'Mencipta',    'color' => '#28a745'],
            ],
            'setting' => $this->dashboard->getSetting(),
        ];

        // Generate HTML from view & convert all local images to base64 data URIs for offline Dompdf rendering
        $html = view('cbt/kartusoal/print_pdf', $data);
        $html = $this->prepareHtmlForDompdf($html);

        // Configure Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', [FCPATH, ROOTPATH]);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate filename
        $filename = 'Kartu_Soal_' . preg_replace('/[^a-zA-Z0-9]/', '_', $kartu->judul ?? 'Untitled') . '.pdf';

        // Return PDF as CodeIgniter Response
        $pdfOutput = $dompdf->output();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdfOutput);
    }

    /**
     * Cetak PDF per Paket Soal (bank soal spesifik yang dibuat dari kartu ini).
     * GET /cbtkartusoal/printPdfPaket/{uuid}/{bankId}
     */
    public function printPdfPaket(string $uuid, int $bankId)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->response->setStatusCode(404)->setBody('Kartu Soal tidak ditemukan.');
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return redirect()->to('cbtkartusoal')->with('error', 'Akses ditolak');
        }

        $db = \Config\Database::connect();

        // Load bank soal target
        $bank = $db->table('cbt_bank_soal')->where('id_bank', $bankId)->where('kartu_soal_id', $kartu->id_kartu)->get()->getRow();
        if (!$bank) {
            return $this->response->setStatusCode(404)->setBody('Paket Soal tidak ditemukan atau bukan bagian dari kartu ini.');
        }

        // --- Determine paket letter ---
        $allBanks   = $this->getPaketBanksForKartu((int) $kartu->id_kartu);
        $paketInfo  = null;
        foreach ($allBanks as $pb) {
            if ((int) $pb['id_bank'] === $bankId) {
                $paketInfo = $pb;
                break;
            }
        }
        $paketLetter = $paketInfo['letter'] ?? 'A';
        $paketLabel  = $paketInfo['bank_label'] ?? ($paketInfo['bank_nama'] ?? ($bank->bank_kode ?? 'Paket'));

        // --- Load soal dari bank ini ---
        $soals = $db->table('cbt_soal')
            ->where('bank_id', $bankId)
            ->where('tampilkan', 1)
            ->orderBy('nomor_soal', 'ASC')
            ->get()->getResult();

        // --- Load kisi-kisi items untuk metadata (cross-reference by nomor & jenis) ---
        $kisiItems = $this->items->getItemsWithTujuan((int) $kartu->id_kartu);
        $kisiItems = array_map(fn($i) => (object) $i, $kisiItems);
        $kisiMap   = [];  // nomor_soal → item (bisa multiple per jenis)
        foreach ($kisiItems as $ki) {
            $no = (int) ($ki->nomor ?? 0);
            $jn = (int) ($ki->jenis_soal ?? 1);
            $kisiMap[$no][$jn] = $ki;
        }

        // --- Shared metadata ---
        $mapel      = $this->master->getMapelById((int) ($kartu->mapel_id ?? 0));
        $kelas      = $this->master->getKelasById((int) ($kartu->kelas_id ?? 0));
        $jenisUjian = $this->cbt->getJenisUjianById((int) ($kartu->id_jenis ?? 0));

        $user = $db->table('users u')
            ->select('u.id, g.nama_guru, g.nip')
            ->join('master_guru g', "g.username = u.username", 'left')
            ->where('u.id', (int) ($kartu->created_by ?? 0))
            ->get()->getRow();
        $kartu->nama_guru = $user->nama_guru ?? 'Administrator';
        $kartu->nip_guru  = $user->nip ?? '-';

        $tpRow = null;
        foreach ($this->dashboard->getTahun() as $tpItem) {
            if ((int) ($tpItem->id_tp ?? 0) === (int) ($kartu->tp_id ?? 0)) { $tpRow = $tpItem; break; }
        }
        $smtRow = null;
        foreach ($this->dashboard->getSemester() as $smtItem) {
            if ((int) ($smtItem->id_smt ?? 0) === (int) ($kartu->smt_id ?? 0)) { $smtRow = $smtItem; break; }
        }

        // Build tujuanMap for kisi-kisi items
        $tujuanIds = array_unique(array_filter(array_map(fn($i) => (int) ($i->id_tujuan ?? 0), (array) $kisiItems)));
        $tujuanMap = [];
        if (!empty($tujuanIds)) {
            foreach ($this->tujuan->whereIn('id_tujuan', $tujuanIds)->findAll() as $tp) {
                $tujuanMap[(int) $tp->id_tujuan] = $tp;
            }
        }

        $data = [
            'kartu'         => $kartu,
            'bank'          => $bank,
            'paket_letter'  => $paketLetter,
            'paket_label'   => $paketLabel,
            'soals'         => $soals,
            'kisi_map'      => $kisiMap,
            'tujuan_map'    => $tujuanMap,
            'mapel'         => $mapel,
            'kelas'         => $kelas,
            'jenis_ujian'   => $jenisUjian,
            'tp_row'        => $tpRow,
            'smt_row'       => $smtRow,
            'setting'       => $this->dashboard->getSetting(),
            'jenis_soal_labels' => [
                1 => 'Pilihan Ganda',
                2 => 'Pilihan Ganda Kompleks',
                3 => 'Menjodohkan',
                4 => 'Isian Singkat',
                5 => 'Uraian / Essay',
                6 => 'Urutan',
                7 => 'Isian Rumpang',
            ],
        ];

        // Generate HTML from view & convert all local images to base64 data URIs
        $html = view('cbt/kartusoal/print_pdf_paket', $data);
        $html = $this->prepareHtmlForDompdf($html);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', [FCPATH, ROOTPATH]);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeName = preg_replace('/[^a-zA-Z0-9]/', '_', $kartu->judul ?? 'Untitled');
        $filename  = "Paket_{$paketLetter}_{$safeName}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Konversi semua path gambar (relatif, URL lokal, path uploads) di dalam HTML menjadi
     * base64 Data URI agar Dompdf dapat merender gambar secara pasti dan offline tanpa gagal jaringan.
     */
    private function prepareHtmlForDompdf(string $html): string
    {
        if (empty($html)) {
            return $html;
        }

        return preg_replace_callback('/<img\s+([^>]*?)src=["\']([^"\']+)["\']([^>]*?)>/i', function ($matches) {
            $preAttributes  = $matches[1];
            $src            = trim($matches[2]);
            $postAttributes = $matches[3];

            // Jika sudah base64 data URI, jangan diubah
            if (str_starts_with($src, 'data:image/')) {
                return $matches[0];
            }

            // Normalisasi URL lokal atau path relatif
            $cleanSrc = $src;
            $baseUrl  = rtrim(base_url(), '/');
            $siteUrl  = rtrim(site_url(), '/');

            if (str_starts_with($cleanSrc, $baseUrl)) {
                $cleanSrc = substr($cleanSrc, strlen($baseUrl));
            } elseif (str_starts_with($cleanSrc, $siteUrl)) {
                $cleanSrc = substr($cleanSrc, strlen($siteUrl));
            } elseif (preg_match('#^https?://[^/]+/(.*)$#i', $cleanSrc, $m)) {
                $cleanSrc = '/' . $m[1];
            }

            // Hilangkan query string atau hash jika ada
            $cleanSrc = preg_replace('/[?#].*$/', '', $cleanSrc);
            $cleanSrc = ltrim($cleanSrc, '/');

            // Cari lokasi fisik file di server
            $candidates = [
                FCPATH . $cleanSrc,
                ROOTPATH . 'public/' . $cleanSrc,
                ROOTPATH . $cleanSrc,
            ];

            $filePath = null;
            foreach ($candidates as $cand) {
                if (file_exists($cand) && is_file($cand)) {
                    $filePath = $cand;
                    break;
                }
            }

            if ($filePath) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeType = match ($ext) {
                    'png'         => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif'         => 'image/gif',
                    'svg'         => 'image/svg+xml',
                    'webp'        => 'image/webp',
                    default       => 'image/png',
                };
                $imageData = @file_get_contents($filePath);
                if ($imageData !== false && !empty($imageData)) {
                    $base64 = base64_encode($imageData);
                    return "<img {$preAttributes}src=\"data:{$mimeType};base64,{$base64}\"{$postAttributes}>";
                }
            }

            return $matches[0];
        }, $html);
    }

    /**
     * API: Generate Kisi-Kisi Ujian Sekolah dengan distribusi ketat
     * POST /cbtkartusoal/api/generate-kisi-sekolah
     */
    public function apiGenerateKisiSekolah(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
        }

        $mapelId = (int) ($this->request->getPost('mapel_id') ?? 0);
        $jumlahSoal = (int) ($this->request->getPost('jumlah_soal') ?? 50);
        $instruksi = trim((string) ($this->request->getPost('instruksi_tambahan') ?? ''));
        $aiModel = trim((string) ($this->request->getPost('ai_model') ?? ''));
        
        // Decode jenis_soal dari JSON
        $jenisSoalRaw = $this->request->getPost('jenis_soal');
        $jenisSoal = [];
        if ($jenisSoalRaw) {
            $decoded = json_decode((string) $jenisSoalRaw, true);
            if (is_array($decoded)) {
                $jenisSoal = array_map('intval', $decoded);
            }
        }
        if (empty($jenisSoal)) {
            $jenisSoal = [1, 2, 3, 4, 5, 6, 7]; // Default semua
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tpId = (int) ($tp->id_tp ?? 0);
        $smtId = (int) ($smt->id_smt ?? 0);

        // Validasi input
        if ($mapelId <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mata pelajaran wajib dipilih'])->setStatusCode(422);
        }

        if ($jumlahSoal < 10 || $jumlahSoal > 50) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jumlah soal harus antara 10-50'])->setStatusCode(422);
        }

        // Ambil nama mapel
        $mapel = $this->master->getMapelById($mapelId);
        $mapelNama = $mapel->nama_mapel ?? 'Unknown';

        // Ambil API key
        $apiKey = $this->ai->getApiKey((int) $this->getUserId(), $this->isGuru());
        if ($apiKey === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'API Key AI belum tersedia'])->setStatusCode(422);
        }

        // Generate kisi-kisi sekolah
        $result = $this->aiKisiKisiSekolah->generateKisiKisiSekolah([
            'jumlah_soal' => $jumlahSoal,
            'mapel_id' => $mapelId,
            'mapel_nama' => $mapelNama,
            'tp_id' => $tpId,
            'smt_id' => $smtId,
            'api_key' => $apiKey,
            'ai_model' => $aiModel,
            'instruksi_tambahan' => $instruksi,
            'jenis_soal' => $jenisSoal,
        ]);

        if (!$result['success']) {
            return $this->response->setJSON(['status' => false, 'message' => $result['message']])->setStatusCode(422);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => $result['message'],
            'data' => $result['data']
        ]);
    }

    /**
     * API: Get distribusi target untuk kisi-kisi sekolah
     * GET /cbtkartusoal/api/distribusi-target
     */
    public function apiDistribusiTarget(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
        }

        $jumlahSoal = (int) ($this->request->getGet('jumlah_soal') ?? 50);

        if ($jumlahSoal < 10 || $jumlahSoal > 100) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jumlah soal harus antara 10-100'])->setStatusCode(422);
        }

        $distribusiKelas = $this->aiKisiKisiSekolah->hitungDistribusiKelas($jumlahSoal);
        $distribusiBloom = $this->aiKisiKisiSekolah->hitungDistribusiBloom($jumlahSoal);

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'distribusi_kelas' => $distribusiKelas,
                'distribusi_bloom' => $distribusiBloom,
            ]
        ]);
    }

    /**
     * API: Get dashboard monitoring untuk kisi-kisi sekolah
     * GET /cbtkartusoal/api/dashboard-kisi-sekolah
     */
    public function apiDashboardKisiSekolah(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
        }

        $mapelId = (int) ($this->request->getGet('mapel_id') ?? 0);
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        $dashboard = $this->aiKisiKisiSekolah->getDashboardMonitoring([
            'mapel_id' => $mapelId,
            'tp_id' => (int) ($tp->id_tp ?? 0),
            'smt_id' => (int) ($smt->id_smt ?? 0),
        ]);

        return $this->response->setJSON([
            'status' => true,
            'data' => $dashboard
        ]);
    }

    /**
     * API: Validasi silang kurikulum untuk kisi-kisi yang sudah digenerate
     * POST /cbtkartusoal/api/validasi-kurikulum
     */
    public function apiValidasiKurikulum(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
        }

        $items = $this->request->getJSON(true)['items'] ?? [];
        $mapelId = (int) ($this->request->getPost('mapel_id') ?? 0);

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        if (empty($items)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data items kosong'])->setStatusCode(422);
        }

        $validasi = $this->aiKisiKisiSekolah->validasiSilangKurikulum($items, $mapelId, (int) ($tp->id_tp ?? 0), (int) ($smt->id_smt ?? 0));

        return $this->response->setJSON([
            'status' => $validasi['valid'],
            'message' => $validasi['valid'] ? 'Validasi kurikulum berhasil' : 'Terdapat masalah dalam validasi kurikulum',
            'data' => $validasi
        ]);
    }

    /**
     * API: Generate Kisi-Kisi per Kelas (step by step)
     * POST /cbtkartusoal/api/generate-per-kelas
     */
    public function apiGeneratePerKelas(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthenticated'])->setStatusCode(401);
        }

        $kelas = trim((string) ($this->request->getPost('kelas') ?? ''));
        $mapelId = (int) ($this->request->getPost('mapel_id') ?? 0);
        $jumlahSoal = (int) ($this->request->getPost('jumlah_soal') ?? 20);
        $instruksi = trim((string) ($this->request->getPost('instruksi_tambahan') ?? ''));
        $aiModel = trim((string) ($this->request->getPost('ai_model') ?? ''));
        $kartuId = (int) ($this->request->getPost('kartu_id') ?? 0);
        $tpId = (int) ($this->request->getPost('tp_id') ?? 0);
        $smtId = (int) ($this->request->getPost('smt_id') ?? 0);

        // Ambil konteks dari Kartu Soal jika ada
        if ($kartuId > 0) {
            $kartu = $this->kartu->find($kartuId);
            if ($kartu) {
                if ($tpId <= 0) $tpId = (int) ($kartu->tp_id ?? 0);
                if ($smtId <= 0) $smtId = (int) ($kartu->smt_id ?? 0);
                if ($mapelId <= 0) $mapelId = (int) ($kartu->mapel_id ?? 0);
            }
        }

        if ($tpId <= 0) {
            $tp = $this->dashboard->getTahunActive();
            $tpId = (int) ($tp->id_tp ?? 0);
        }
        if ($smtId <= 0) {
            $smt = $this->dashboard->getSemesterActive();
            $smtId = (int) ($smt->id_smt ?? 0);
        }

        // Decode jenis_soal dari JSON
        $jenisSoalRaw = $this->request->getPost('jenis_soal');
        $jenisSoal = [];
        if ($jenisSoalRaw) {
            $decoded = json_decode((string) $jenisSoalRaw, true);
            if (is_array($decoded)) {
                $jenisSoal = array_map('intval', $decoded);
            }
        }
        if (empty($jenisSoal)) {
            $jenisSoal = [1, 2, 3, 4, 5, 6, 7]; // Default semua
        }

        // Validasi kelas fleksibel (SD, SMP, SMA/SMK)
        $validKelasList = ['1','2','3','4','5','6','7','8','9','10','11','12','X','XI','XII','VII','VIII','IX','A','B','C','D','E','F'];
        if (!in_array(strtoupper($kelas), $validKelasList, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Kelas/Tingkat tidak valid'])->setStatusCode(422);
        }

        if ($mapelId <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mata pelajaran wajib dipilih'])->setStatusCode(422);
        }

        if ($jumlahSoal < 5 || $jumlahSoal > 50) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jumlah soal harus antara 5-50'])->setStatusCode(422);
        }

        // Ambil nama mapel
        $mapel = $this->master->getMapelById($mapelId);
        $mapelNama = $mapel->nama_mapel ?? 'Unknown';

        // Ambil API key
        $apiKey = $this->ai->getApiKey((int) $this->getUserId(), $this->isGuru());
        if ($apiKey === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'API Key AI belum tersedia'])->setStatusCode(422);
        }

        // Generate per kelas
        $result = $this->aiKisiKisiSekolah->generateKisiKisiPerKelas([
            'jumlah_soal' => $jumlahSoal,
            'mapel_id' => $mapelId,
            'mapel_nama' => $mapelNama,
            'tp_id' => $tpId,
            'smt_id' => $smtId,
            'api_key' => $apiKey,
            'ai_model' => $aiModel,
            'instruksi_tambahan' => $instruksi,
            'jenis_soal' => $jenisSoal,
        ], strtoupper($kelas));

        if (!$result['success']) {
            return $this->response->setJSON(['status' => false, 'message' => $result['message']])->setStatusCode(422);
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => $result['message'],
            'data' => $result['data']
        ]);
    }

    // ================================================================
    // SPRINT 1-3: KUALITAS KISI-KISI
    // ================================================================

    /**
     * GET /cbtkartusoal/api/analisis-distribusi/{uuid}
     *
     * Validasi distribusi Taksonomi Bloom pada satu kartu soal.
     * Membandingkan distribusi aktual vs target berdasarkan tipe ujian.
     */
    public function apiAnalisisDistribusi(string $uuid): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }

        $tipeUjian = trim((string) ($this->request->getGet('tipe') ?? 'uts'));

        $bloomService = new \App\Services\BloomValidatorService();
        $hasil        = $bloomService->validasiByKartu((int) $kartu->id_kartu, $tipeUjian);

        // Tambahkan metadata kartu
        $hasil['kartu_uuid']  = $uuid;
        $hasil['kartu_judul'] = $kartu->judul ?? '-';

        return $this->respond(true, 'OK', $hasil);
    }

    /**
     * GET /cbtkartusoal/api/validasi-materi/{uuid}
     *
     * Validasi proporsionalitas materi pokok dan ketercakupan TP.
     */
    public function apiValidasiMateri(string $uuid): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        if ($this->isGuru() && (int) ($kartu->created_by ?? 0) !== (int) $this->getUserId()) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }

        $db    = \Config\Database::connect();
        $total = $this->items->where('kartu_id', $kartu->id_kartu)->countAllResults();

        if ($total === 0) {
            return $this->respond(false, 'Belum ada item kisi-kisi untuk divalidasi.');
        }

        // --- Distribusi per materi pokok ---
        $perMateri = $db->table('cbt_kartu_soal_item')
            ->select('materi, COUNT(*) as jumlah')
            ->where('kartu_id', $kartu->id_kartu)
            ->where('materi IS NOT NULL')
            ->where('materi !=', '')
            ->groupBy('materi')
            ->orderBy('jumlah', 'DESC')
            ->get()->getResult();

        // --- Ketercakupan TP ---
        // Gunakan raw query sederhana untuk COUNT DISTINCT — lebih aman daripada chaining
        $tpTercoverRow = $db->query(
            'SELECT COUNT(DISTINCT id_tujuan) AS jml
             FROM cbt_kartu_soal_item
             WHERE kartu_id = ? AND id_tujuan IS NOT NULL',
            [(int) $kartu->id_kartu]
        );
        $tpTercover = $tpTercoverRow ? (int) ($tpTercoverRow->getRow()->jml ?? 0) : 0;

        $tpTotal = 0;
        try {
            $tpTotal = $db->table('e_tujuan_pembelajaran')
                ->where('id_mapel', $kartu->mapel_id)
                ->where('is_active', 1)
                ->countAllResults();
        } catch (\Throwable $e) {
            // tabel TP mungkin kosong — tidak kritis
        }

        $pctTpCover = $tpTotal > 0 ? round($tpTercover / $tpTotal * 100, 1) : 0;

        // --- Distribusi per jenis soal ---
        $perJenis = $db->table('cbt_kartu_soal_item')
            ->select('jenis_soal, COUNT(*) as jumlah')
            ->where('kartu_id', $kartu->id_kartu)
            ->groupBy('jenis_soal')
            ->orderBy('jenis_soal', 'ASC')
            ->get()->getResult();

        // --- Bangun daftar peringatan ---
        $warnings = [];
        foreach ($perMateri as $baris) {
            $pct = round($baris->jumlah / $total * 100, 1);
            if ($pct > 40) {
                $warnings[] = [
                    'level' => 'warning',
                    'pesan' => "Materi '{$baris->materi}' mendominasi {$pct}% soal (>40%). "
                               . "Pertimbangkan meratakan ke materi lain.",
                ];
            }
        }

        if ($pctTpCover < 70 && $tpTotal > 0) {
            $warnings[] = [
                'level' => 'warning',
                'pesan' => "Hanya {$pctTpCover}% Tujuan Pembelajaran tercakup "
                           . "({$tpTercover} dari {$tpTotal} TP aktif). "
                           . "Standar minimal adalah 70%.",
            ];
        }

        if ($total < 5) {
            $warnings[] = [
                'level' => 'info',
                'pesan' => "Jumlah item kisi-kisi ({$total}) masih sedikit. "
                           . "Tambahkan lebih banyak butir untuk ujian yang representatif.",
            ];
        }

        return $this->respond(true, 'OK', [
            'kartu_uuid'    => $uuid,
            'kartu_judul'   => $kartu->judul ?? '-',
            'total_soal'    => $total,
            'per_materi'    => $perMateri,
            'per_jenis'     => $perJenis,
            'tp_tercover'   => (int) $tpTercover,
            'tp_total'      => $tpTotal,
            'pct_tp_cover'  => $pctTpCover,
            'warnings'      => $warnings,
            'valid'         => empty($warnings),
        ]);
    }

    /**
     * POST /cbtkartusoal/duplicate
     *
     * Duplikasi kartu soal ke periode (tp_id / smt_id) lain.
     * Semua item kisi-kisi disalin; soal_id & bank_id dikosongkan
     * agar guru bisa mengisi soal baru tanpa terkait bank lama.
     */
    public function duplicate(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        $uuid     = trim((string) ($this->request->getPost('uuid')    ?? ''));
        $newTpId  = (int) ($this->request->getPost('tp_id')           ?? 0);
        $newSmtId = (int) ($this->request->getPost('smt_id')          ?? 0);

        if ($uuid === '' || $newTpId <= 0 || $newSmtId <= 0) {
            return $this->respond(false, 'Parameter uuid, tp_id, dan smt_id wajib diisi.', null,
                ['uuid' => 'Wajib', 'tp_id' => 'Wajib', 'smt_id' => 'Wajib'], 422);
        }

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        // Hanya pemilik atau admin/panitia yang boleh menduplikasi
        if (!$this->canManage($kartu)) {
            return $this->respond(false, 'Akses ditolak', null, null, 403);
        }

        $userId  = (int) $this->getUserId();
        $newUuid = $this->generateUuidV4();
        $now     = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->transStart();

        // --- Salin header kartu soal ---
        $kartuData = (array) $kartu;
        unset($kartuData['id_kartu'], $kartuData['deleted_at']);
        $kartuData['uuid']            = $newUuid;
        $kartuData['tp_id']           = $newTpId;
        $kartuData['smt_id']          = $newSmtId;
        $kartuData['workflow_status'] = 'draft';
        $kartuData['submitted_at']    = null;
        $kartuData['approved_at']     = null;
        $kartuData['approved_by']     = null;
        $kartuData['approval_note']   = null;
        // Kolom sprint 3
        $kartuData['kepsek_approved_at'] = null;
        $kartuData['kepsek_approved_by'] = null;
        $kartuData['kepsek_note']        = null;
        $kartuData['created_by']         = $userId;
        $kartuData['updated_by']         = $userId;
        $kartuData['created_at']         = $now;
        $kartuData['updated_at']         = $now;

        // Hapus kolom yang tidak ada di allowedFields model
        $allowed = $this->kartu->getAllowedFields();
        $allowed[] = 'uuid'; // uuid tidak di allowedFields tapi wajib ada
        foreach (array_keys($kartuData) as $col) {
            if (!in_array($col, $allowed, true)) {
                unset($kartuData[$col]);
            }
        }

        $newKartuId = $this->kartu->insert($kartuData, true);
        if (!$newKartuId) {
            $db->transRollback();
            return $this->respond(false, 'Gagal menduplikasi kartu soal.',
                null, $this->kartu->errors(), 500);
        }

        // --- Salin semua item kisi-kisi (tanpa soal_id & bank_id) ---
        $sourceItems = $this->items->where('kartu_id', (int) $kartu->id_kartu)
                                   ->orderBy('nomor', 'ASC')
                                   ->findAll();

        // Validasi TP: cari mana id_tujuan yang valid di target period
        $validTpIds = [];
        $allTpIds = array_unique(array_filter(array_map(fn($i) => (int) ($i->id_tujuan ?? 0), $sourceItems)));
        if (!empty($allTpIds)) {
            $validTps = $this->tujuan
                ->select('id_tujuan')
                ->whereIn('id_tujuan', $allTpIds)
                ->where('id_mapel', (int) ($kartu->mapel_id ?? 0))
                ->where('id_tp', $newTpId)
                ->where('id_smt', $newSmtId)
                ->where('is_active', 1)
                ->findAll();
            foreach ($validTps as $vtp) {
                $validTpIds[(int) $vtp->id_tujuan] = true;
            }
        }

        $insertBatch = [];
        foreach ($sourceItems as $item) {
            $row = (array) $item;
            unset($row['id_item']);
            $row['kartu_id']   = $newKartuId;
            $row['soal_id']    = null;   // fresh — belum ada soal
            $row['bank_id']    = null;   // fresh — belum terikat bank
            // Null-kan id_tujuan jika TP tidak valid di target period
            if (!empty($row['id_tujuan']) && !isset($validTpIds[(int) $row['id_tujuan']])) {
                $row['id_tujuan'] = null;
            }
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            $insertBatch[]     = $row;
        }

        if (!empty($insertBatch)) {
            $this->items->batchInsertItems($insertBatch);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->respond(false, 'Transaksi database gagal.', null, null, 500);
        }

        $this->audit->add($newKartuId, 'duplicate', json_encode([
            'source_uuid' => $uuid,
            'tp_id'       => $newTpId,
            'smt_id'      => $newSmtId,
            'item_count'  => count($insertBatch),
        ], JSON_UNESCAPED_UNICODE));

        $this->logging->saveLog(3, "Duplikasi kartu soal '{$kartu->judul}' ke periode baru (TP:{$newTpId}/SMT:{$newSmtId})");

        return $this->respond(true, 'Kartu soal berhasil diduplikasi.', [
            'uuid'       => $newUuid,
            'item_count' => count($insertBatch),
        ]);
    }

    /**
     * POST /cbtkartusoal/approve-kepsek
     *
     * Persetujuan final oleh Kepala Sekolah (approval level 2).
     * Hanya tersedia jika approval_level = 2 dan workflow_status = 'approved'.
     */
    public function approveKepsek(): \CodeIgniter\HTTP\Response
    {
        if (!$this->isLoggedIn()) {
            return $this->respond(false, 'Sesi habis', null, null, 401);
        }

        // Hanya admin (yang mewakili kepsek) atau panitia level tertentu
        if (!$this->isAdmin() && !$this->isPanitia()) {
            return $this->respond(false, 'Akses ditolak. Fitur ini untuk admin/kepsek.', null, null, 403);
        }

        $uuid = trim((string) ($this->request->getPost('uuid') ?? ''));
        $note = trim((string) ($this->request->getPost('note') ?? ''));

        $kartu = $this->kartu->findByUuid($uuid);
        if (!$kartu) {
            return $this->respond(false, 'Kartu tidak ditemukan', null, null, 404);
        }

        $wfStatus = $this->getWorkflowStatusValue($kartu);

        // Hanya kartu yang sudah diapprove panitia yang bisa disetujui kepsek
        if ($wfStatus !== 'approved') {
            return $this->respond(false,
                "Kartu harus berstatus 'approved' (panitia) sebelum disetujui kepsek. "
                . "Status saat ini: {$wfStatus}.",
                ['workflow_status' => $wfStatus], null, 422);
        }

        $db  = \Config\Database::connect();
        $ok  = $db->table('cbt_kartu_soal')
            ->where('id_kartu', (int) $kartu->id_kartu)
            ->update([
                'kepsek_approved_at' => date('Y-m-d H:i:s'),
                'kepsek_approved_by' => (int) $this->getUserId(),
                'kepsek_note'        => $note !== '' ? $note : null,
                'updated_by'         => (int) $this->getUserId(),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

        if (!$ok) {
            return $this->respond(false, 'Gagal menyimpan persetujuan kepsek.', null, null, 500);
        }

        $this->audit->add((int) $kartu->id_kartu, 'approve_kepsek',
            json_encode(['note' => $note], JSON_UNESCAPED_UNICODE));
        $this->logging->saveLog(5, "Approve kepsek kartu soal: {$kartu->judul}");

        return $this->respond(true, 'Persetujuan Kepala Sekolah berhasil disimpan.');
    }

    /**
     * Re-sync Kartu Soal Items from Master Tujuan Pembelajaran (TP)
     */
    public function resyncItemsFromTp($id_kartu = 0)
    {
        try {
            $id_kartu = (int) $id_kartu;
            if ($id_kartu <= 0) {
                return $this->respond(false, 'ID Kartu tidak valid');
            }

            $kartu = $this->kartu->find($id_kartu);
            if (!$kartu) {
                return $this->respond(false, 'Kartu soal tidak ditemukan');
            }

            if (!$this->canManage($kartu) || !$this->canEditKartuContent($kartu)) {
                return $this->respond(false, 'Akses ditolak');
            }

            $db = \Config\Database::connect();
            $items = $db->table('cbt_kartu_soal_item')->where('kartu_id', $id_kartu)->get()->getResult();

            if (empty($items)) {
                return $this->respond(false, 'Kartu soal belum memiliki butir kisi-kisi');
            }

            $updated = 0;
            foreach ($items as $it) {
                if (empty($it->id_tujuan)) continue;

                $tp = $db->table('e_tujuan_pembelajaran tp')
                    ->select('tp.*, cp.elemen as elemen_cp, cp.deskripsi_cp')
                    ->join('e_capaian_pembelajaran cp', 'tp.kode_cp = cp.kode_cp', 'left')
                    ->where('tp.id_tujuan', $it->id_tujuan)
                    ->get()->getRow();

                if ($tp) {
                    $updateData = [
                        'cp'         => !empty($tp->kode_cp) ? $tp->kode_cp : $it->cp,
                        'tujuan'     => !empty($tp->tujuan) ? $tp->tujuan : $it->tujuan,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    if (empty($it->materi) && !empty($tp->materi_pokok)) {
                        $updateData['materi'] = $tp->materi_pokok;
                    }
                    if (empty($it->level_kognitif) && !empty($tp->level_kognitif)) {
                        $updateData['level_kognitif'] = $tp->level_kognitif;
                    }

                    $db->table('cbt_kartu_soal_item')->where('id_item', $it->id_item)->update($updateData);
                    $updated++;
                }
            }

            return $this->respond(true, "Berhasil menyelaraskan {$updated} butir kisi-kisi dari master Tujuan Pembelajaran.");
        } catch (\Exception $e) {
            return $this->respond(false, 'Gagal menyelaraskan data: ' . $e->getMessage());
        }
    }

    /**
     * Generate new Bank Soal (Paket Soal) from an existing Kartu Soal.
     * Supports mode: 'all', 'ganjil', 'genap' and optional option shuffling ('acak_opsi').
     */
    public function generateBankFromKartu()
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->respond(false, 'Sesi habis. Silakan login kembali.', null, null, 401);
            }

            @set_time_limit(0);

            $idKartu = (int) ($this->request->getPost('kartu_id') ?? 0);
            $bankKode = trim((string) ($this->request->getPost('bank_kode') ?? ''));
            $bankNama = trim((string) ($this->request->getPost('bank_nama') ?? ''));
            $modeSoal = trim((string) ($this->request->getPost('mode_soal') ?? 'all')); // 'all', 'ganjil', 'genap'
            $acakOpsi = (bool) ($this->request->getPost('acak_opsi') ?? false);
            // Satu kartu = satu paket per generate (varian paket A/B via mode_soal ganjil/genap).
            $jumlahPaket = 1;
            $subtesCode = strtoupper(trim((string) ($this->request->getPost('subtes_code') ?? 'GENERAL')));
            $subtesMap = [
                'GENERAL'   => 'General / Reguler',
                'BIN'       => 'Bahasa Indonesia',
                'MTK'       => 'Matematika',
                'BING'      => 'Bahasa Inggris',
                'FIS'       => 'Fisika',
                'KIM'       => 'Kimia',
                'BIO'       => 'Biologi',
                'GEO'       => 'Geografi',
                'EKO'       => 'Ekonomi',
                'SEJ'       => 'Sejarah',
                'SOS'       => 'Sosiologi',
                'PPN'       => 'Pendidikan Pancasila',
                'INF'       => 'Informatika',
                'KEAHLIAN'  => 'Mapel Keahlian SMK',
                'VERBAL'    => 'Penalaran Verbal',
                'KUANTITATIF' => 'Penalaran Kuantitatif',
                'LOGIS'     => 'Penalaran Logis & Spasial',
                'LITERASI'  => 'Literasi Membaca',
                'NUMERASI'  => 'Literasi Numerasi & Data'
            ];
            if (!isset($subtesMap[$subtesCode])) {
                $subtesCode = 'GENERAL';
            }
            $subtesNama = $subtesMap[$subtesCode];

            if ($idKartu <= 0) {
                return $this->respond(false, 'Kartu Soal tidak valid', null, null, 422);
            }
            if (empty($bankKode)) {
                return $this->respond(false, 'Kode Bank Soal wajib diisi', null, null, 422);
            }

            $kartu = $this->kartu->find($idKartu);
            if (!$kartu) {
                return $this->respond(false, 'Kartu Soal tidak ditemukan', null, null, 404);
            }

            // Guard kepemilikan: guru hanya boleh generate dari kartunya sendiri
            $manageGuard = $this->ensureCanManage($kartu);
            if ($manageGuard) {
                return $manageGuard;
            }

            // Guard workflow: kartu harus sudah approved (kecuali admin/panitia)
            if (!$this->isAdmin() && !$this->isPanitia()) {
                if ($this->getWorkflowStatusValue($kartu) !== 'approved') {
                    return $this->respond(
                        false,
                        'Kartu Soal harus berstatus Disetujui (approved) sebelum dapat di-generate menjadi Bank Soal.',
                        ['workflow_status' => $this->getWorkflowStatusValue($kartu)],
                        null,
                        422
                    );
                }
            }

            $db = \Config\Database::connect();

            // Check if bank_kode already exists
            $existBank = $db->table('cbt_bank_soal')->where('bank_kode', $bankKode)->get()->getRow();
            if ($existBank) {
                return $this->respond(false, "Kode Bank Soal '{$bankKode}' sudah digunakan. Gunakan kode lain.", null, null, 400);
            }

            // Get items from cbt_kartu_soal_item
            $builderItems = $db->table('cbt_kartu_soal_item')
                ->where('kartu_id', $idKartu)
                ->orderBy('nomor', 'ASC');

            if ($modeSoal === 'ganjil') {
                $builderItems->where('MOD(nomor, 2) =', 1);
            } elseif ($modeSoal === 'genap') {
                $builderItems->where('MOD(nomor, 2) =', 0);
            }

            $items = $builderItems->get()->getResult();
            if (empty($items)) {
                return $this->respond(false, 'Tidak ada butir soal pada Kartu Soal yang sesuai kriteria.', null, null, 400);
            }

            // Calculate question type counts
            $counts = [
                1 => 0, // PG
                2 => 0, // PG Kompleks
                3 => 0, // Menjodohkan
                4 => 0, // Isian Singkat
                5 => 0, // Uraian/Essay
                6 => 0, // Urutan
                7 => 0, // Cloze
            ];

            foreach ($items as $it) {
                $j = (int) ($it->jenis_soal ?? 1);
                if (isset($counts[$j])) {
                    $counts[$j]++;
                } else {
                    $counts[1]++;
                }
            }

            // Default weights: equal weighting for active types
            $bobotPg = $counts[1] > 0 ? 100 : 0;
            $bobotEsai = $counts[5] > 0 ? 100 : 0;
            $bobotKompleks = $counts[2] > 0 ? 100 : 0;
            $bobotJodohkan = $counts[3] > 0 ? 100 : 0;
            $bobotIsian = $counts[4] > 0 ? 100 : 0;

            // Build bank_kelas JSON (support multi-kelas kartu via kelas_ids)
            $bankKelasJson = '[]';
            $kartuKelasIds = [];
            if (!empty($kartu->kelas_ids)) {
                $decodedKelas = json_decode((string) $kartu->kelas_ids, true);
                if (is_array($decodedKelas)) {
                    $kartuKelasIds = array_values(array_filter(array_map('intval', $decodedKelas)));
                }
            }
            if (empty($kartuKelasIds) && !empty($kartu->kelas_id)) {
                $kartuKelasIds = [(int) $kartu->kelas_id];
            }
            if (!empty($kartuKelasIds)) {
                $bankKelasJson = json_encode(array_map(fn($kId) => ['kelas_id' => (string) $kId], $kartuKelasIds));
            }

            // Resolve master_guru.id_guru for bank_guru_id
            $bankGuruId = 0;
            $tp = $this->dashboard->getTahunActive();
            $smt = $this->dashboard->getSemesterActive();

            // Try from current logged in user
            $user = $this->authModel->user();
            if ($user && $tp && $smt) {
                $guruObj = $this->dashboard->getDataGuruByUserId($user->id, (int)$tp->id_tp, (int)$smt->id_smt);
                if ($guruObj && !empty($guruObj->id_guru)) {
                    $bankGuruId = (int)$guruObj->id_guru;
                }
            }

            // Fallback from kartu->created_by (which is user_id in cbt_kartu_soal)
            if ($bankGuruId === 0 && !empty($kartu->created_by) && $tp && $smt) {
                $guruObj = $this->dashboard->getDataGuruByUserId((int)$kartu->created_by, (int)$tp->id_tp, (int)$smt->id_smt);
                if ($guruObj && !empty($guruObj->id_guru)) {
                    $bankGuruId = (int)$guruObj->id_guru;
                }
            }

            $bankData = [
                'bank_kode'       => $bankKode,
                'bank_nama'       => !empty($bankNama) ? $bankNama : $bankKode,
                'bank_mapel_id'   => $kartu->mapel_id ? (int)$kartu->mapel_id : 0,
                'bank_jenis_id'   => $kartu->id_jenis ? (int)$kartu->id_jenis : 1,
                'bank_jurusan_id' => 0,
                'subtes_code'     => $subtesCode,
                'subtes_nama'     => $subtesNama,
                'bank_level'      => $kartu->kelas_id ? (string)$kartu->kelas_id : '10',
                'bank_kelas'      => $bankKelasJson,
                'bank_guru_id'    => $bankGuruId,
                'kartu_soal_id'   => (int)$kartu->id_kartu,
                'id_tp'           => $kartu->tp_id ? (int)$kartu->tp_id : 1,
                'id_smt'          => $kartu->smt_id ? (int)$kartu->smt_id : 1,
                'status'          => 1,
                'status_soal'     => 1,
                'opsi'            => 5,
                'jml_soal'        => $counts[1],
                'tampil_pg'       => $counts[1],
                'bobot_pg'        => $bobotPg,
                'jml_esai'        => $counts[5],
                'tampil_esai'     => $counts[5],
                'bobot_esai'      => $bobotEsai,
                'jml_kompleks'    => $counts[2],
                'tampil_kompleks' => $counts[2],
                'bobot_kompleks'   => $bobotKompleks,
                'jml_jodohkan'    => $counts[3],
                'tampil_jodohkan' => $counts[3],
                'bobot_jodohkan'  => $bobotJodohkan,
                'jml_isian'       => $counts[4],
                'tampil_isian'    => $counts[4],
                'bobot_isian'     => $bobotIsian,
                'tampil_urutan'   => $counts[6],
                'bobot_urutan'    => 0,
                'tampil_cloze'    => $counts[7],
                'bobot_cloze'     => 0,
                'date'            => date('Y-m-d H:i:s')
            ];

            $db->transStart();
            $db->table('cbt_bank_soal')->insert($bankData);
            $newBankId = $db->insertID();

            if (!$newBankId) {
                $db->transRollback();
                return $this->respond(false, 'Gagal membuat Paket Bank Soal baru di database', null, null, 500);
            }

            // Insert questions into cbt_soal
            // Konvensi numbering: nomor_soal PER JENIS (1..N per jenis), konsisten dengan
            // editor bank soal (btn-jenis-nomor, getSoalByNomor(bank, nomor, jenis),
            // getNomorSoalTerbesar, reorder/delete yang juga per-jenis).
            $noPerJenis = [];
            foreach ($items as $it) {
                $jenis = (int) ($it->jenis_soal ?? 1);
                $soalText = trim((string) ($it->butir_soal ?? ''));
                
                // Extract options from opsi_json if available
                $opsiA = null; $opsiB = null; $opsiC = null; $opsiD = null; $opsiE = null;
                if (!empty($it->opsi_json)) {
                    $opsiArr = json_decode($it->opsi_json, true);
                    if (is_array($opsiArr)) {
                        $opsiA = $opsiArr[0]['text'] ?? $opsiArr['a'] ?? $opsiArr['A'] ?? null;
                        $opsiB = $opsiArr[1]['text'] ?? $opsiArr['b'] ?? $opsiArr['B'] ?? null;
                        $opsiC = $opsiArr[2]['text'] ?? $opsiArr['c'] ?? $opsiArr['C'] ?? null;
                        $opsiD = $opsiArr[3]['text'] ?? $opsiArr['d'] ?? $opsiArr['D'] ?? null;
                        $opsiE = $opsiArr[4]['text'] ?? $opsiArr['e'] ?? $opsiArr['E'] ?? null;
                    }
                }

                // Extract correct answer -> format kolom bank native:
                // PG (1) = huruf polos, kompleks (2) = JSON array. Format item native:
                // PG = JSON string "b", kompleks = JSON array; envelope lawas {"raw":X} di-unwrap.
                $jawaban = null;
                if (!empty($it->jawaban_benar_json)) {
                    $jwbDecoded = json_decode($it->jawaban_benar_json, true);
                    if (is_array($jwbDecoded) && isset($jwbDecoded['raw']) && count($jwbDecoded) === 1) {
                        $jawaban = (string) $jwbDecoded['raw']; // legacy envelope
                    } elseif (is_string($jwbDecoded)) {
                        $jawaban = $jwbDecoded; // JSON string "b" -> b
                    } elseif (is_array($jwbDecoded)) {
                        if ($jenis === 1 && count($jwbDecoded) === 1) {
                            $jawaban = (string) reset($jwbDecoded);
                        } else {
                            $jawaban = json_encode(array_values($jwbDecoded), JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        $jawaban = (string) $it->jawaban_benar_json;
                    }
                }

                // Fallback from linked original cbt_soal if item fields are empty
                if (!empty($it->soal_id)) {
                    $origSoal = $db->table('cbt_soal')->where('id_soal', (int) $it->soal_id)->get()->getRow();
                    if ($origSoal) {
                        if (empty($soalText)) $soalText = $origSoal->soal ?? '';
                        if ($opsiA === null)  $opsiA = $origSoal->opsi_a ?? null;
                        if ($opsiB === null)  $opsiB = $origSoal->opsi_b ?? null;
                        if ($opsiC === null)  $opsiC = $origSoal->opsi_c ?? null;
                        if ($opsiD === null)  $opsiD = $origSoal->opsi_d ?? null;
                        if ($opsiE === null)  $opsiE = $origSoal->opsi_e ?? null;
                        if (empty($jawaban) || $jawaban === '{}' || $jawaban === '[]') {
                            $jawaban = $origSoal->jawaban ?? null;
                        }
                    }
                }

                // Smart Option Shuffling for Pilihan Ganda (jenis == 1) with answer key remapping
                if ($acakOpsi && $jenis === 1 && !empty($jawaban)) {
                    $origOpsiMap = [];
                    if ($opsiA !== null) $origOpsiMap['a'] = $opsiA;
                    if ($opsiB !== null) $origOpsiMap['b'] = $opsiB;
                    if ($opsiC !== null) $origOpsiMap['c'] = $opsiC;
                    if ($opsiD !== null) $origOpsiMap['d'] = $opsiD;
                    if ($opsiE !== null) $origOpsiMap['e'] = $opsiE;

                    $correctKey = strtolower(trim((string)$jawaban));
                    if (isset($origOpsiMap[$correctKey]) && count($origOpsiMap) > 1) {
                        $correctText = $origOpsiMap[$correctKey];
                        $pairs = [];
                        foreach ($origOpsiMap as $k => $txt) {
                            $pairs[] = ['key' => $k, 'text' => $txt];
                        }
                        shuffle($pairs);

                        $keys = ['a', 'b', 'c', 'd', 'e'];
                        $opsiA = null; $opsiB = null; $opsiC = null; $opsiD = null; $opsiE = null;
                        $newJawaban = $jawaban;

                        foreach ($pairs as $idx => $p) {
                            $posKey = $keys[$idx] ?? 'a';
                            if ($idx === 0) $opsiA = $p['text'];
                            if ($idx === 1) $opsiB = $p['text'];
                            if ($idx === 2) $opsiC = $p['text'];
                            if ($idx === 3) $opsiD = $p['text'];
                            if ($idx === 4) $opsiE = $p['text'];

                            if ($p['text'] === $correctText) {
                                $newJawaban = $posKey;
                            }
                        }
                        $jawaban = $newJawaban;
                    }
                }

                // Difficulty integer mapping (invers dari peta import: mudah=1, sedang=2, sulit=3).
                // Terima label maupun digit lawas '1'/'2'/'3' agar item hasil import lama tetap akurat.
                $kesulitanStr = strtolower(trim((string)($it->tingkat_kesulitan ?? 'sedang')));
                $kesulitanInt = 2;
                if (in_array($kesulitanStr, ['mudah', 'c1', 'c2', '1'], true)) {
                    $kesulitanInt = 1;
                } elseif (in_array($kesulitanStr, ['sulit', 'c5', 'c6', '3'], true)) {
                    $kesulitanInt = 3;
                }

                $soalData = [
                    'bank_id'     => $newBankId,
                    'mapel_id'    => $kartu->mapel_id ? (int)$kartu->mapel_id : 0,
                    'tujuan_id'   => !empty($it->id_tujuan) ? (int)$it->id_tujuan : null,
                    'jenis'       => $jenis,
                    'subtes_code' => $subtesCode,
                    'bobot_irt'   => 1.0,
                    'nomor_soal'  => $noPerJenis[$jenis] = ($noPerJenis[$jenis] ?? 0) + 1,
                    'soal'        => $soalText,
                    'opsi_a'      => $opsiA,
                    'opsi_b'      => $opsiB,
                    'opsi_c'      => $opsiC,
                    'opsi_d'      => $opsiD,
                    'opsi_e'      => $opsiE,
                    'jawaban'     => $jawaban,
                    'tampilkan'   => 1,
                    'deskripsi'   => $it->indikator_soal ?? '',
                    'kesulitan'   => $kesulitanInt,
                    'timer'       => 0,
                    'timer_menit' => 0,
                    'created_on'  => time(),
                ];

                $db->table('cbt_soal')->insert($soalData);
                $newSoalId = (int) $db->insertID();

                // Tautkan item kisi-kisi ke butir soal bank ini (relasi utama kartu ↔ bank).
                // Jangan menimpa link yang sudah ada ke soal bank lain yang masih hidup —
                // link pertama (bank asli) tetap dijaga agar tidak membuat orphan soal.
                $alreadyLinked = false;
                if (!empty($it->bank_id) && !empty($it->soal_id)) {
                    $alreadyLinked = $db->table('cbt_soal')
                        ->where('id_soal', (int) $it->soal_id)
                        ->countAllResults() > 0;
                }
                if (!$alreadyLinked) {
                    $db->table('cbt_kartu_soal_item')
                        ->where('id_item', (int) $it->id_item)
                        ->update([
                            'bank_id'    => $newBankId,
                            'soal_id'    => $newSoalId,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->respond(false, 'Gagal mengkloning butir soal ke Bank Soal baru.', null, null, 500);
            }

            // Audit log
            if (isset($this->audit) && method_exists($this->audit, 'add')) {
                $this->audit->add((int) $kartu->id_kartu, 'generate_bank', json_encode([
                    'id_bank' => $newBankId,
                    'bank_kode' => $bankKode,
                    'total_items' => count($items),
                    'jumlah_paket' => $jumlahPaket,
                ]));
            }

            $message = "Berhasil membuat Paket Soal baru '{$bankKode}' dengan " . count($items) . " butir soal dari Kartu ini.";

            return $this->respond(true, $message, [
                'id_bank' => $newBankId,
                'bank_kode' => $bankKode,
                'total_soal' => count($items),
                'jumlah_paket' => $jumlahPaket,
                'url_detail' => site_url('cbtbanksoal/detail/' . $newBankId)
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[generateBankFromKartu Error] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return $this->respond(false, 'Terjadi kesalahan sistem: ' . $e->getMessage(), null, null, 500);
        }
    }
}
