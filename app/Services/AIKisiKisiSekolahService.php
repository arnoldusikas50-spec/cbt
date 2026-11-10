<?php

namespace App\Services;

use App\Models\DashboardModel;
use App\Models\MasterModel;
use App\Models\TujuanPembelajaranModel;

/**
 * AI Kisi-Kisi Sekolah Service
 * 
 * Implementasi fitur AI Auto Create Kisi-Kisi Ujian Sekolah berbasis Kurikulum Merdeka
 * dengan spesifikasi distribusi ketat dan validasi kurikulum.
 */
class AIKisiKisiSekolahService
{
    private DashboardModel $dashboard;
    private MasterModel $master;
    private TujuanPembelajaranModel $tujuan;
    private AIService $ai;

    // Distribusi kelas: X (20%), XI (30%), XII (50%)
    private const DISTRIBUSI_KELAS = [
        'X' => 0.20,
        'XI' => 0.30,
        'XII' => 0.50
    ];

    // Toleransi deviasi: ±2%
    private const TOLERANSI_DEVIASI = 0.02;

    // Distribusi level kognitif Bloom's Taxonomy
    private const DISTRIBUSI_BLOOM = [
        'C1' => ['min' => 0.15, 'max' => 0.25, 'target' => 0.20], // Remembering
        'C2' => ['min' => 0.20, 'max' => 0.30, 'target' => 0.25], // Understanding
        'C3' => ['min' => 0.25, 'max' => 0.35, 'target' => 0.30], // Applying
        'C4' => ['min' => 0.15, 'max' => 0.25, 'target' => 0.20], // Analyzing
        'C5' => ['min' => 0.10, 'max' => 0.20, 'target' => 0.15], // Evaluating
    ];

    // 7 bentuk soal yang didukung
    private const BENTUK_SOAL = [
        1 => ['nama' => 'Pilihan Ganda', 'kode' => 'PG', 'cocok' => ['C1', 'C2', 'C3']],
        2 => ['nama' => 'Pilihan Ganda Kompleks', 'kode' => 'PGK', 'cocok' => ['C2', 'C3', 'C4']],
        3 => ['nama' => 'Menjodohkan', 'kode' => 'MJ', 'cocok' => ['C1', 'C2', 'C3']],
        4 => ['nama' => 'Isian Singkat', 'kode' => 'IS', 'cocok' => ['C1', 'C2', 'C3']],
        5 => ['nama' => 'Uraian/Essay', 'kode' => 'UR', 'cocok' => ['C3', 'C4', 'C5']],
        6 => ['nama' => 'Urutan', 'kode' => 'URT', 'cocok' => ['C2', 'C3', 'C4']],
        7 => ['nama' => 'Isian Rumpang/Cloze', 'kode' => 'IR', 'cocok' => ['C2', 'C3', 'C4']],
    ];

    // Mapping fase ke kelas - Kurikulum Merdeka (SD, SMP, SMA/SMK)
    private const FASE_KELAS = [
        'A' => ['1', '2', 'I', 'II'],
        'B' => ['3', '4', 'III', 'IV'],
        'C' => ['5', '6', 'V', 'VI'],
        'D' => ['7', '8', '9', 'VII', 'VIII', 'IX'],
        'E' => ['10', 'X'],
        'F' => ['11', '12', 'XI', 'XII'],
    ];

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
        $this->master = new MasterModel();
        $this->tujuan = new TujuanPembelajaranModel();
        $this->tujuan->initialize();
        $this->ai = new AIService();
    }

    /**
     * Generate kisi-kisi sekolah dengan distribusi ketat
     * 
     * @param array $params Parameter generate
     * @return array Hasil generate dengan validasi distribusi
     */
    public function generateKisiKisiSekolah(array $params): array
    {
        $jumlahSoal = (int) ($params['jumlah_soal'] ?? 50);
        $mapelId = (int) ($params['mapel_id'] ?? 0);
        $tpId = (int) ($params['tp_id'] ?? 0);
        $smtId = (int) ($params['smt_id'] ?? 0);
        $apiKey = (string) ($params['api_key'] ?? '');

        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'API Key AI belum tersedia',
                'data' => null
            ];
        }

        // 1. Hitung distribusi soal per kelas
        $distribusiKelas = $this->hitungDistribusiKelas($jumlahSoal);

        // 2. Hitung distribusi level kognitif
        $distribusiBloom = $this->hitungDistribusiBloom($jumlahSoal);

        // 3. Get allowed jenis soal from params
        $jenisSoalAllowed = $params['jenis_soal'] ?? [1, 2, 3, 4, 5, 6, 7];

        // 4. Ambil data CP & ATP dari database
        $cpAtpData = $this->ambilCpAtpData($mapelId, $tpId, $smtId);

        // 5. Bangun prompt AI dengan konteks lengkap
        $prompt = $this->buildPromptKisiKisiSekolah($params, $distribusiKelas, $distribusiBloom, $cpAtpData, $jenisSoalAllowed);

        // 5. Generate dengan AI
        $aiResult = $this->ai->generateContent($apiKey, $prompt, $params['ai_model'] ?? '');

        if (!$aiResult['success']) {
            return [
                'success' => false,
                'message' => $aiResult['message'],
                'data' => null,
                'wait_seconds' => $aiResult['wait_seconds'] ?? 0
            ];
        }

        // 6. Parse dan validasi hasil
        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        
        // Jika parsing gagal, coba recovery dengan pendekatan lebih agresif
        if (!$parseResult['success']) {
            $raw = $aiResult['data'] ?? '';
            
            // Recovery: coba ekstrak JSON array dengan regex yang lebih toleran
            if (preg_match('/\[.*\{.*\}.*\]/s', $raw, $matches)) {
                $candidate = $matches[0];
                // Bersihkan karakter problematik
                $candidate = preg_replace('/,\s*\}/', '}', $candidate); // Hapus trailing comma
                $candidate = preg_replace('/,\s*\]/', ']', $candidate); // Hapus trailing comma di array
                $candidate = str_replace(["\r", "\n", "\t"], " ", $candidate);
                $candidate = preg_replace('/[\x00-\x1F\x7F]/', '', $candidate);
                
                $parsed = json_decode($candidate, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    $parseResult = [
                        'success' => true,
                        'data' => $parsed,
                        'message' => 'Recovered'
                    ];
                }
            }
        }
        
        if (!$parseResult['success']) {
            // Log raw response for debugging
            log_message('error', 'AI Kisi-Kisi JSON Parse Error: ' . $parseResult['message']);
            log_message('error', 'AI Raw Response (first 1000 chars): ' . substr($aiResult['data'] ?? '', 0, 1000));

            return [
                'success' => false,
                'message' => 'AI tidak mengembalikan format JSON yang valid. Coba gunakan jumlah soal lebih kecil (10-30) atau pilih model Flash 2.5. Error: ' . $parseResult['message'],
                'data' => null
            ];
        }

        // 7. Normalisasi dan validasi hasil
        $normalized = $this->normalizeKisiKisiItems($parseResult['data'], $distribusiKelas, $distribusiBloom);

        // 8. Validasi distribusi
        $validasiDistribusi = $this->validasiDistribusi($normalized['valid'], $distribusiKelas, $distribusiBloom);

        // 9. Jika ada deviasi, lakukan optimasi
        if (!$validasiDistribusi['valid']) {
            $normalized = $this->optimasiDistribusi($normalized, $distribusiKelas, $distribusiBloom, $apiKey, $params);
        }

        return [
            'success' => true,
            'message' => 'Kisi-kisi sekolah berhasil digenerate',
            'data' => [
                'items' => $normalized['valid'],
                'meta' => [
                    'total_soal' => $jumlahSoal,
                    'distribusi_kelas' => $distribusiKelas,
                    'distribusi_bloom' => $distribusiBloom,
                    'validasi_distribusi' => $validasiDistribusi,
                    'ai_model' => $aiResult['model'] ?? 'unknown',
                    'ai_elapsed' => $aiResult['elapsed'] ?? 0,
                    'valid_count' => count($normalized['valid']),
                    'rejected_count' => count($normalized['rejected']),
                ]
            ]
        ];
    }

    /**
     * Ambil data CP & ATP dari database - SEMUA SEMESTER (1 dan 2)
     * Menggabungkan data dari semester 1 dan 2 untuk coverage yang menyeluruh
     */
    /**
     * Ambil data CP & ATP dari database - SEMUA SEMESTER (1 dan 2)
     * Menggabungkan data dari semester 1 dan 2 untuk coverage yang menyeluruh
     */
    private function ambilCpAtpDataAllSemester(int $mapelId, int $tpId): array
    {
        // 1. Ambil data dengan filter tp_id jika diberikan
        $dataSemester1 = [];
        $dataSemester2 = [];

        if ($tpId > 0) {
            $dataSemester1 = $this->tujuan
                ->where('id_mapel', $mapelId)
                ->where('id_tp', $tpId)
                ->where('id_smt', 1)
                ->where('is_active', 1)
                ->orderBy('kode', 'ASC')
                ->findAll();

            $dataSemester2 = $this->tujuan
                ->where('id_mapel', $mapelId)
                ->where('id_tp', $tpId)
                ->where('id_smt', 2)
                ->where('is_active', 1)
                ->orderBy('kode', 'ASC')
                ->findAll();
        }

        // Fallback 1: Jika kosong atau $tpId = 0, ambil tanpa filter id_tp yang kaku
        if (empty($dataSemester1) && empty($dataSemester2)) {
            $dataSemester1 = $this->tujuan
                ->where('id_mapel', $mapelId)
                ->where('id_smt', 1)
                ->where('is_active', 1)
                ->orderBy('kode', 'ASC')
                ->findAll();

            $dataSemester2 = $this->tujuan
                ->where('id_mapel', $mapelId)
                ->where('id_smt', 2)
                ->where('is_active', 1)
                ->orderBy('kode', 'ASC')
                ->findAll();
        }

        // Fallback 2: Jika masih kosong, ambil seluruh TP aktif untuk mapel ini
        if (empty($dataSemester1) && empty($dataSemester2)) {
            $allActive = $this->tujuan
                ->where('id_mapel', $mapelId)
                ->where('is_active', 1)
                ->orderBy('kode', 'ASC')
                ->findAll();

            foreach ($allActive as $item) {
                if ((int) ($item->id_smt ?? 1) === 2) {
                    $dataSemester2[] = $item;
                } else {
                    $dataSemester1[] = $item;
                }
            }
        }

        // Gabungkan data dengan tandai semester
        $allData = [];
        foreach ($dataSemester1 as $item) {
            $item->semester = 1;
            $allData[] = $item;
        }
        foreach ($dataSemester2 as $item) {
            $item->semester = 2;
            $allData[] = $item;
        }

        $result = [
            'cp_list' => [],
            'atp_list' => [],
            'fase_kelas_map' => [],
            'semester_stats' => [
                'semester_1' => count($dataSemester1),
                'semester_2' => count($dataSemester2),
                'total' => count($allData),
            ],
            'kelas_distribution' => [],
        ];

        foreach ($allData as $item) {
            $fase = strtoupper(trim((string) ($item->fase ?? '')));
            $tingkat = (int) ($item->tingkat ?? 0);
            $kodeCp = (string) ($item->kode_cp ?? '');
            $semester = (int) ($item->semester ?? 1);
            
            // Mapping fase/tingkat ke list kelas yang cocok
            $kelasList = [];
            if ($tingkat > 0) {
                $kelasList[] = (string) $tingkat;
                if ($tingkat === 10) $kelasList[] = 'X';
                if ($tingkat === 11) $kelasList[] = 'XI';
                if ($tingkat === 12) $kelasList[] = 'XII';
                if ($tingkat === 7) $kelasList[] = 'VII';
                if ($tingkat === 8) $kelasList[] = 'VIII';
                if ($tingkat === 9) $kelasList[] = 'IX';
            }
            if (isset(self::FASE_KELAS[$fase])) {
                $kelasList = array_unique(array_merge($kelasList, self::FASE_KELAS[$fase]));
            }
            if (empty($kelasList)) {
                $kelasList = ['1','2','3','4','5','6','7','8','9','10','11','12','X','XI','XII','VII','VIII','IX'];
            }
            
            $atpItem = [
                'id_tujuan' => (int) ($item->id_tujuan ?? 0),
                'kode' => (string) ($item->kode ?? ''),
                'fase' => $fase,
                'tingkat' => $tingkat,
                'kelas_list' => $kelasList,
                'tujuan' => trim((string) ($item->tujuan ?? '')),
                'kode_cp' => $kodeCp,
                'elemen_cp' => trim((string) ($item->elemen_cp ?? '')),
                'materi_pokok' => trim((string) ($item->materi_pokok ?? '')),
                'kriteria_ketercapaian' => trim((string) ($item->kriteria_ketercapaian ?? '')),
                'level_kognitif_rekomendasi' => (string) ($item->level_kognitif ?? ''),
                'tp_sikap' => (string) ($item->tp_sikap ?? ''),
                'tp_keterampilan' => (string) ($item->tp_keterampilan ?? ''),
                'semester' => $semester,
            ];

            $result['atp_list'][] = $atpItem;

            // Group by CP
            if ($kodeCp !== '') {
                if (!isset($result['cp_list'][$kodeCp])) {
                    $result['cp_list'][$kodeCp] = [
                        'kode' => $kodeCp,
                        'elemen' => $atpItem['elemen_cp'],
                        'atp_count' => 0,
                        'atp_count_smt1' => 0,
                        'atp_count_smt2' => 0,
                        'atp_ids' => [],
                    ];
                }
                $result['cp_list'][$kodeCp]['atp_count']++;
                if ($semester === 1) {
                    $result['cp_list'][$kodeCp]['atp_count_smt1']++;
                } else {
                    $result['cp_list'][$kodeCp]['atp_count_smt2']++;
                }
                $result['cp_list'][$kodeCp]['atp_ids'][] = $atpItem['id_tujuan'];
            }

            // Mapping fase ke kelas untuk distribusi + statistik per semester
            foreach ($kelasList as $kelas) {
                if (!isset($result['fase_kelas_map'][$kelas])) {
                    $result['fase_kelas_map'][$kelas] = [];
                }
                $result['fase_kelas_map'][$kelas][] = $atpItem;
                
                // Update distribusi statistik
                if (!isset($result['kelas_distribution'][$kelas])) {
                    $result['kelas_distribution'][$kelas] = ['total' => 0, 'semester_1' => 0, 'semester_2' => 0];
                }
                $result['kelas_distribution'][$kelas]['total']++;
                if ($semester === 1) {
                    $result['kelas_distribution'][$kelas]['semester_1']++;
                } else {
                    $result['kelas_distribution'][$kelas]['semester_2']++;
                }
            }
        }

        return $result;
    }

    /**
     * Hitung distribusi soal per kelas dengan proporsi ketat
     * Kelas X: 20%, XI: 30%, XII: 50%
     */
    public function hitungDistribusiKelas(int $totalSoal): array
    {
        $distribusi = [];
        $totalAssigned = 0;

        // Hitung jumlah soal per kelas
        foreach (self::DISTRIBUSI_KELAS as $kelas => $proporsi) {
            $jumlah = (int) round($totalSoal * $proporsi);
            $distribusi[$kelas] = [
                'proporsi' => $proporsi,
                'target' => $jumlah,
                'min' => (int) ceil($totalSoal * ($proporsi - self::TOLERANSI_DEVIASI)),
                'max' => (int) floor($totalSoal * ($proporsi + self::TOLERANSI_DEVIASI)),
                'actual' => 0,
            ];
            $totalAssigned += $jumlah;
        }

        // Adjust jika ada rounding error
        $diff = $totalSoal - $totalAssigned;
        if ($diff !== 0) {
            // Tambahkan/kurangi dari kelas dengan proporsi terbesar (XII)
            $distribusi['XII']['target'] += $diff;
        }

        return $distribusi;
    }

    /**
     * Hitung distribusi level kognitif Bloom
     */
    public function hitungDistribusiBloom(int $totalSoal): array
    {
        $distribusi = [];
        $totalAssigned = 0;

        foreach (self::DISTRIBUSI_BLOOM as $level => $config) {
            $target = (int) round($totalSoal * $config['target']);
            $distribusi[$level] = [
                'target' => $target,
                'min' => (int) ceil($totalSoal * $config['min']),
                'max' => (int) floor($totalSoal * $config['max']),
                'actual' => 0,
            ];
            $totalAssigned += $target;
        }

        // Adjust jika ada rounding error
        $diff = $totalSoal - $totalAssigned;
        if ($diff !== 0) {
            // Tambahkan/kurangi dari level dengan target terbesar (C3)
            $distribusi['C3']['target'] += $diff;
        }

        return $distribusi;
    }

    /**
     * Ambil data CP & ATP dari database
     */
    private function ambilCpAtpData(int $mapelId, int $tpId, int $smtId): array
    {
        $data = $this->tujuan
            ->where('id_mapel', $mapelId)
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('is_active', 1)
            ->orderBy('kode', 'ASC')
            ->findAll();

        $result = [
            'cp_list' => [],
            'atp_list' => [],
            'fase_kelas_map' => [],
        ];

        foreach ($data as $item) {
            $fase = (string) ($item->fase ?? '');
            $kodeCp = (string) ($item->kode_cp ?? '');
            
            // Mapping fase ke kelas
            $kelasList = self::FASE_KELAS[$fase] ?? ['X', 'XI', 'XII'];
            
            $atpItem = [
                'id_tujuan' => (int) ($item->id_tujuan ?? 0),
                'kode' => (string) ($item->kode ?? ''),
                'fase' => $fase,
                'kelas_list' => $kelasList,
                'tujuan' => trim((string) ($item->tujuan ?? '')),
                'kode_cp' => $kodeCp,
                'elemen_cp' => trim((string) ($item->elemen_cp ?? '')),
                'materi_pokok' => trim((string) ($item->materi_pokok ?? '')),
                'kriteria_ketercapaian' => trim((string) ($item->kriteria_ketercapaian ?? '')),
                'level_kognitif_rekomendasi' => (string) ($item->level_kognitif ?? ''),
                'tp_sikap' => (string) ($item->tp_sikap ?? ''),
                'tp_keterampilan' => (string) ($item->tp_keterampilan ?? ''),
            ];

            $result['atp_list'][] = $atpItem;

            // Group by CP
            if ($kodeCp !== '') {
                if (!isset($result['cp_list'][$kodeCp])) {
                    $result['cp_list'][$kodeCp] = [
                        'kode' => $kodeCp,
                        'elemen' => $atpItem['elemen_cp'],
                        'atp_count' => 0,
                        'atp_ids' => [],
                    ];
                }
                $result['cp_list'][$kodeCp]['atp_count']++;
                $result['cp_list'][$kodeCp]['atp_ids'][] = $atpItem['id_tujuan'];
            }

            // Mapping fase ke kelas untuk distribusi
            foreach ($kelasList as $kelas) {
                if (!isset($result['fase_kelas_map'][$kelas])) {
                    $result['fase_kelas_map'][$kelas] = [];
                }
                $result['fase_kelas_map'][$kelas][] = $atpItem;
            }
        }

        return $result;
    }

    /**
     * Build prompt AI untuk kisi-kisi sekolah
     */
    private function buildPromptKisiKisiSekolah(
        array $params,
        array $distribusiKelas,
        array $distribusiBloom,
        array $cpAtpData,
        array $jenisSoalAllowed = [1, 2, 3, 4, 5, 6, 7]
    ): string {
        $mapelNama = (string) ($params['mapel_nama'] ?? '');
        $jumlahSoal = (int) ($params['jumlah_soal'] ?? 50);

        $prompt = "Anda adalah AI Assessment Specialist untuk Ujian Sekolah Kurikulum Merdeka Indonesia.\n\n";
        $prompt .= "TUGAS: Buat {$jumlahSoal} KISI-KISI SOAL dengan distribusi ketat berikut:\n\n";

        // 1. Distribusi Kelas
        $prompt .= "=== DISTRIBUSI SOAL PER KELAS (WAJIB ±2% TOLERANSI) ===\n";
        foreach ($distribusiKelas as $kelas => $config) {
            $proporsiPercent = round($config['proporsi'] * 100, 1);
            $prompt .= "- Kelas {$kelas}: {$config['target']} soal (target {$proporsiPercent}%)\n";
        }
        $prompt .= "\n";

        // 2. Distribusi Level Kognitif
        $prompt .= "=== DISTRIBUSI LEVEL KOGNITIF BLOOM (WAJIB) ===\n";
        $bloomLabels = [
            'C1' => 'Mengingat (Remembering)',
            'C2' => 'Memahami (Understanding)',
            'C3' => 'Menerapkan (Applying)',
            'C4' => 'Menganalisis (Analyzing)',
            'C5' => 'Mengevaluasi (Evaluating)',
        ];
        foreach ($distribusiBloom as $level => $config) {
            $label = $bloomLabels[$level] ?? $level;
            $prompt .= "- {$level} ({$label}): {$config['target']} soal (rentang {$config['min']}-{$config['max']})\n";
        }
        $prompt .= "\n";

        // 3. Filter bentuk soal yang diizinkan
        $bentukSoalFiltered = [];
        foreach (self::BENTUK_SOAL as $kode => $bentuk) {
            if (in_array((int) $kode, $jenisSoalAllowed, true)) {
                $bentukSoalFiltered[$kode] = $bentuk;
            }
        }
        
        if (empty($bentukSoalFiltered)) {
            $bentukSoalFiltered = self::BENTUK_SOAL; // Fallback ke semua jika kosong
        }

        $prompt .= "=== BENTUK SOAL YANG DIPILIH (HANYA GUNAKAN YANG BERIKUT) ===\n";
        foreach ($bentukSoalFiltered as $kode => $bentuk) {
            $cocok = implode(', ', $bentuk['cocok']);
            $prompt .= "{$kode}. {$bentuk['nama']} ({$bentuk['kode']}) - Cocok untuk level: {$cocok}\n";
        }
        $allowedJenisStr = implode(', ', array_keys($bentukSoalFiltered));
        $prompt .= "\nPENTING: Gunakan HANYA jenis soal di atas (kode: {$allowedJenisStr}).\n\n";

        // 4. Data CP & ATP
        $prompt .= "=== CAPAIAN PEMBELAJARAN (CP) DAN TUJUAN PEMBELAJARAN (ATP) ===\n";
        $prompt .= "Mata Pelajaran: {$mapelNama}\n\n";
        
        foreach ($cpAtpData['cp_list'] as $cp) {
            $prompt .= "CP {$cp['kode']}: {$cp['elemen']} ({$cp['atp_count']} ATP)\n";
        }
        $prompt .= "\n";

        $prompt .= "Daftar Lengkap ATP:\n";
        foreach ($cpAtpData['atp_list'] as $atp) {
            $prompt .= "- [ID:{$atp['id_tujuan']}] Kode:{$atp['kode']} | Fase:{$atp['fase']} | Kelas:" . implode(',', $atp['kelas_list']) . " | CP:{$atp['kode_cp']}\n";
            $prompt .= "  Materi: {$atp['materi_pokok']}\n";
            $prompt .= "  Tujuan: {$atp['tujuan']}\n";
            if ($atp['level_kognitif_rekomendasi']) {
                $prompt .= "  Level Rekomendasi: {$atp['level_kognitif_rekomendasi']}\n";
            }
            $prompt .= "\n";
        }

        // 5. Instruksi Pemilihan
        $prompt .= "=== ATURAN PEMILIHAN SOAL ===\n";
        $prompt .= "1. SETIAP soal HARUS memiliki:\n";
        $prompt .= "   - kelas: X, XI, atau XII (sesuai distribusi target)\n";
        $prompt .= "   - level_kognitif: C1, C2, C3, C4, atau C5 (sesuai distribusi target)\n";
        $prompt .= "   - jenis_soal: HANYA " . $allowedJenisStr . " (pilih yang sesuai dengan level kognitif)\n";
        $prompt .= "   - id_tujuan: ID ATP dari daftar di atas\n";
        $prompt .= "   - kode_cp: kode CP yang sesuai\n";
        $prompt .= "   - indikator_soal: diawali KKO Bloom yang sesuai level\n";
        $prompt .= "\n";
        $prompt .= "2. Prioritas pemilihan ATP:\n";
        $prompt .= "   - Setiap CP harus memiliki minimal 1 soal\n";
        $prompt .= "   - Distribusikan soal merata antar ATP dalam satu CP\n";
        $prompt .= "   - Untuk kelas X: prioritaskan ATP dari Fase A\n";
        $prompt .= "   - Untuk kelas XI: prioritaskan ATP dari Fase B\n";
        $prompt .= "   - Untuk kelas XII: prioritaskan ATP dari Fase C\n";
        $prompt .= "\n";
        $prompt .= "3. Mapping level kognitif ke bentuk soal:\n";
        
        // Build mapping berdasarkan jenis soal yang diizinkan
        $mappingBloom = [
            'C1' => [],
            'C2' => [],
            'C3' => [],
            'C4' => [],
            'C5' => [],
        ];
        foreach ($bentukSoalFiltered as $kode => $bentuk) {
            foreach ($bentuk['cocok'] as $level) {
                if (isset($mappingBloom[$level])) {
                    $mappingBloom[$level][] = $kode;
                }
            }
        }
        
        foreach ($mappingBloom as $level => $jenisList) {
            if (!empty($jenisList)) {
                $jenisStr = implode(', ', $jenisList);
                $prompt .= "   - {$level}: {$jenisStr}\n";
            }
        }
        $prompt .= "\n";

        // 6. Format Output
        $prompt .= "=== FORMAT OUTPUT (JSON Array) ===\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= '    "kelas": "X", // X, XI, atau XII' . "\n";
        $prompt .= '    "level_kognitif": "C3", // C1, C2, C3, C4, C5' . "\n";
        $prompt .= '    "jenis_soal": 1, // HANYA ' . $allowedJenisStr . "\n";
        $prompt .= '    "id_tujuan": 123, // ID ATP dari daftar' . "\n";
        $prompt .= '    "kode_cp": "CP-KD-01", // Kode CP' . "\n";
        $prompt .= '    "elemen_cp": "Elemen capaian...", // Elemen CP' . "\n";
        $prompt .= '    "materi": "Materi pokok spesifik", // Materi dari ATP' . "\n";
        $prompt .= '    "indikator_soal": "Menganalisis... [KKO] + konteks", // Indikator dengan KKO' . "\n";
        $prompt .= '    "estimasi_waktu": 1 // Menit per soal' . "\n";
        $prompt .= "  },\n";
        $prompt .= "  ...\n";
        $prompt .= "]\n\n";

        // 7. Aturan Mutlak
        $prompt .= "=== ATURAN MUTLAK ===\n";
        $prompt .= "- Output HARUS tepat {$jumlahSoal} objek\n";
        $prompt .= "- Distribusi kelas HARUS sesuai target (±2% toleransi)\n";
        $prompt .= "- Distribusi level kognitif HARUS dalam rentang yang ditentukan\n";
        $prompt .= "- HANYA gunakan jenis_soal: {$allowedJenisStr} (jangan gunakan jenis lain)\n";
        $prompt .= "- Setiap CP harus memiliki minimal 1 soal\n";
        $prompt .= "- Materi dan indikator tiap soal HARUS berbeda (unik)\n";
        $prompt .= "- Indikator HARUS diawali KKO Bloom yang sesuai levelnya\n";
        $prompt .= "- Gunakan hanya ID tujuan yang ada di daftar ATP\n";

        if (!empty($params['instruksi_tambahan'])) {
            $prompt .= "\n=== INSTRUKSI TAMBAHAN DARI GURU ===\n";
            $prompt .= $params['instruksi_tambahan'] . "\n";
        }

        return $prompt;
    }

    /**
     * Normalisasi hasil kisi-kisi dari AI
     */
    private function normalizeKisiKisiItems($parsed, array $distribusiKelas, array $distribusiBloom): array
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
                $result['rejected'][] = ['index' => $idx, 'reason' => 'Format bukan objek'];
                continue;
            }

            // Validasi kelas
            $kelas = strtoupper(trim((string) ($row['kelas'] ?? '')));
            if (!in_array($kelas, ['X', 'XI', 'XII'], true)) {
                $result['rejected'][] = ['index' => $idx, 'reason' => "Kelas '{$kelas}' tidak valid (harus X, XI, atau XII)"];
                continue;
            }

            // Validasi level kognitif
            $levelKognitif = strtoupper(trim((string) ($row['level_kognitif'] ?? '')));
            if (!in_array($levelKognitif, ['C1', 'C2', 'C3', 'C4', 'C5'], true)) {
                $result['rejected'][] = ['index' => $idx, 'reason' => "Level kognitif '{$levelKognitif}' tidak valid"];
                continue;
            }

            // Validasi jenis soal
            $jenisSoal = (int) ($row['jenis_soal'] ?? 1);
            if ($jenisSoal < 1 || $jenisSoal > 7) {
                $jenisSoal = 1;
            }

            // Validasi kualitas
            $qualityErrors = $this->checkKisiKisiQuality($row);
            if (!empty($qualityErrors)) {
                $result['rejected'][] = ['index' => $idx, 'reason' => implode('; ', $qualityErrors)];
                continue;
            }

            // Cek duplikat
            $fingerprint = mb_strtolower(trim(strip_tags((string) ($row['indikator_soal'] ?? ''))));
            if ($fingerprint !== '' && isset($seen[$fingerprint])) {
                $result['rejected'][] = ['index' => $idx, 'reason' => 'Duplikat indikator terdeteksi'];
                continue;
            }
            $seen[$fingerprint] = true;

            // Sanitasi data
            $result['valid'][] = [
                'kelas' => $kelas,
                'level_kognitif' => $levelKognitif,
                'jenis_soal' => $jenisSoal,
                'id_tujuan' => (int) ($row['id_tujuan'] ?? 0),
                'kode_cp' => trim((string) ($row['kode_cp'] ?? '')),
                'elemen_cp' => trim((string) ($row['elemen_cp'] ?? '')),
                'materi' => trim((string) ($row['materi'] ?? '')),
                'indikator_soal' => trim((string) ($row['indikator_soal'] ?? '')),
                'estimasi_waktu' => max(1, min(20, (int) ($row['estimasi_waktu'] ?? 1))),
            ];

            // Update actual count
            $distribusiKelas[$kelas]['actual']++;
            $distribusiBloom[$levelKognitif]['actual']++;
        }

        return $result;
    }

    /**
     * Cek kualitas item kisi-kisi
     */
    private function checkKisiKisiQuality(array $row): array
    {
        $errors = [];

        $plainMateri = trim(strip_tags((string) ($row['materi'] ?? '')));
        if (mb_strlen($plainMateri) < 3) {
            $errors[] = 'Materi terlalu singkat';
        }

        $plainIndikator = trim(strip_tags((string) ($row['indikator_soal'] ?? '')));
        if (mb_strlen($plainIndikator) < 10) {
            $errors[] = 'Indikator terlalu singkat';
        }

        // Cek apakah indikator diawali dengan KKO Bloom
        $kkoValid = false;
        $kkoList = [
            'mengingat', 'menyebutkan', 'mengidentifikasi', 'menyatakan', // C1
            'memahami', 'menjelaskan', 'menguraikan', 'menginterpretasi', 'mengklasifikasikan', 'meringkas', // C2
            'menerapkan', 'menggunakan', 'melakukan', 'menyelesaikan', 'menghitung', // C3
            'menganalisis', 'membedakan', 'membandingkan', 'menyelidiki', 'mengategorikan', // C4
            'mengevaluasi', 'menilai', 'mempertahankan', 'mengkritik', 'membuktikan', // C5
        ];
        foreach ($kkoList as $kko) {
            if (stripos($plainIndikator, $kko) === 0) {
                $kkoValid = true;
                break;
            }
        }
        if (!$kkoValid) {
            $errors[] = 'Indikator tidak diawali dengan KKO Bloom yang valid';
        }

        return $errors;
    }

    /**
     * Validasi distribusi hasil generate
     */
    public function validasiDistribusi(array $items, array $distribusiKelas, array $distribusiBloom): array
    {
        $result = [
            'valid' => true,
            'kelas' => [],
            'bloom' => [],
            'rekomendasi' => [],
        ];

        // Hitung aktual distribusi
        $actualKelas = ['X' => 0, 'XI' => 0, 'XII' => 0];
        $actualBloom = ['C1' => 0, 'C2' => 0, 'C3' => 0, 'C4' => 0, 'C5' => 0];

        foreach ($items as $item) {
            $actualKelas[$item['kelas']]++;
            $actualBloom[$item['level_kognitif']]++;
        }

        $total = count($items);

        // Validasi kelas
        foreach ($distribusiKelas as $kelas => $config) {
            $actual = $actualKelas[$kelas];
            $proporsiActual = $total > 0 ? $actual / $total : 0;
            $proporsiMin = $config['proporsi'] - self::TOLERANSI_DEVIASI;
            $proporsiMax = $config['proporsi'] + self::TOLERANSI_DEVIASI;

            $isValid = $proporsiActual >= $proporsiMin && $proporsiActual <= $proporsiMax;
            
            $result['kelas'][$kelas] = [
                'target' => $config['target'],
                'actual' => $actual,
                'proporsi' => round($proporsiActual * 100, 2) . '%',
                'valid' => $isValid,
            ];

            if (!$isValid) {
                $result['valid'] = false;
                if ($actual < $config['target']) {
                    $result['rekomendasi'][] = "Tambahkan soal kelas {$kelas}: kurang " . ($config['target'] - $actual);
                } else {
                    $result['rekomendasi'][] = "Kurangi soal kelas {$kelas}: kelebihan " . ($actual - $config['target']);
                }
            }
        }

        // Validasi Bloom
        foreach ($distribusiBloom as $level => $config) {
            $actual = $actualBloom[$level];
            
            $isValid = $actual >= $config['min'] && $actual <= $config['max'];
            
            $result['bloom'][$level] = [
                'target' => $config['target'],
                'min' => $config['min'],
                'max' => $config['max'],
                'actual' => $actual,
                'valid' => $isValid,
            ];

            if (!$isValid) {
                $result['valid'] = false;
                if ($actual < $config['min']) {
                    $result['rekomendasi'][] = "Tambahkan soal level {$level}: kurang " . ($config['min'] - $actual);
                } else {
                    $result['rekomendasi'][] = "Kurangi soal level {$level}: kelebihan " . ($actual - $config['max']);
                }
            }
        }

        return $result;
    }

    /**
     * Optimasi distribusi dengan re-generate jika diperlukan
     */
    private function optimasiDistribusi(
        array $normalized,
        array $distribusiKelas,
        array $distribusiBloom,
        string $apiKey,
        array $params
    ): array {
        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $validasi = $this->validasiDistribusi($normalized['valid'], $distribusiKelas, $distribusiBloom);
            
            if ($validasi['valid']) {
                break;
            }

            $attempt++;
            
            // Build prompt korektif
            $promptKorektif = $this->buildPromptKorektif($validasi, $distribusiKelas, $distribusiBloom, $params);
            
            $aiResult = $this->ai->generateContent($apiKey, $promptKorektif, $params['ai_model'] ?? '');
            
            if ($aiResult['success']) {
                $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
                if ($parseResult['success']) {
                    $newItems = $this->normalizeKisiKisiItems($parseResult['data'], $distribusiKelas, $distribusiBloom);
                    $normalized = $newItems;
                }
            }
        }

        return $normalized;
    }

    /**
     * Build prompt korektif untuk optimasi
     */
    private function buildPromptKorektif(array $validasi, array $distribusiKelas, array $distribusiBloom, array $params): string
    {
        $jumlahSoal = (int) ($params['jumlah_soal'] ?? 50);
        
        $prompt = "KOREKSI DISTRIBUSI KISI-KISI SOAL\n\n";
        $prompt .= "Hasil sebelumnya memiliki deviasi dari target. Silakan generate ulang dengan perbaikan:\n\n";

        $prompt .= "Masalah yang terdeteksi:\n";
        foreach ($validasi['rekomendasi'] as $rekomendasi) {
            $prompt .= "- {$rekomendasi}\n";
        }
        $prompt .= "\n";

        $prompt .= "Target akhir yang harus dicapai:\n";
        foreach ($distribusiKelas as $kelas => $config) {
            $prompt .= "- Kelas {$kelas}: {$config['target']} soal\n";
        }
        $prompt .= "\n";

        $prompt .= "Format output tetap sama: JSON Array dengan {$jumlahSoal} objek.\n";
        $prompt .= "Pastikan setiap objek memiliki field: kelas, level_kognitif, jenis_soal, id_tujuan, kode_cp, elemen_cp, materi, indikator_soal, estimasi_waktu.\n";

        return $prompt;
    }

    /**
     * Get dashboard monitoring data
     */
    public function getDashboardMonitoring(array $filters = []): array
    {
        $mapelId = (int) ($filters['mapel_id'] ?? 0);
        $tpId = (int) ($filters['tp_id'] ?? 0);
        $smtId = (int) ($filters['smt_id'] ?? 0);

        // CP Coverage Analysis
        $cpAtpData = $this->ambilCpAtpData($mapelId, $tpId, $smtId);
        
        $cpCoverage = [];
        foreach ($cpAtpData['cp_list'] as $kode => $cp) {
            $cpCoverage[$kode] = [
                'kode' => $kode,
                'elemen' => $cp['elemen'],
                'total_atp' => $cp['atp_count'],
                'soal_tercover' => 0, // Akan dihitung dari data kisi-kisi
                'coverage_percent' => 0,
            ];
        }

        return [
            'cp_coverage' => $cpCoverage,
            'total_cp' => count($cpAtpData['cp_list']),
            'total_atp' => count($cpAtpData['atp_list']),
            'fase_distribution' => [
                'A' => count(array_filter($cpAtpData['atp_list'], fn($a) => $a['fase'] === 'A')),
                'B' => count(array_filter($cpAtpData['atp_list'], fn($a) => $a['fase'] === 'B')),
                'C' => count(array_filter($cpAtpData['atp_list'], fn($a) => $a['fase'] === 'C')),
            ],
        ];
    }

    /**
     * Validasi silang kurikulum
     */
    public function validasiSilangKurikulum(array $items, int $mapelId, int $tpId, int $smtId): array
    {
        $cpAtpData = $this->ambilCpAtpData($mapelId, $tpId, $smtId);
        
        $validCpIds = array_keys($cpAtpData['cp_list']);
        $validAtpIds = array_map(fn($a) => $a['id_tujuan'], $cpAtpData['atp_list']);

        $result = [
            'valid' => true,
            'issues' => [],
            'stats' => [
                'cp_tercover' => [],
                'atp_tercover' => [],
                'level_distribution' => ['C1' => 0, 'C2' => 0, 'C3' => 0, 'C4' => 0, 'C5' => 0],
            ],
        ];

        foreach ($items as $item) {
            // Validasi CP
            $kodeCp = $item['kode_cp'] ?? '';
            if ($kodeCp !== '' && !in_array($kodeCp, $validCpIds, true)) {
                $result['issues'][] = "CP '{$kodeCp}' tidak valid untuk mapel ini";
                $result['valid'] = false;
            }

            // Validasi ATP
            $idTujuan = (int) ($item['id_tujuan'] ?? 0);
            if ($idTujuan > 0 && !in_array($idTujuan, $validAtpIds, true)) {
                $result['issues'][] = "ATP ID {$idTujuan} tidak valid";
                $result['valid'] = false;
            }

            // Track coverage
            if ($kodeCp !== '') {
                $result['stats']['cp_tercover'][$kodeCp] = true;
            }
            if ($idTujuan > 0) {
                $result['stats']['atp_tercover'][$idTujuan] = true;
            }

            // Track level distribution
            $level = $item['level_kognitif'] ?? '';
            if (isset($result['stats']['level_distribution'][$level])) {
                $result['stats']['level_distribution'][$level]++;
            }
        }

        // Cek CP yang belum tercover
        foreach ($validCpIds as $cpId) {
            if (!isset($result['stats']['cp_tercover'][$cpId])) {
                $result['issues'][] = "CP '{$cpId}' belum memiliki soal";
            }
        }

        return $result;
    }

    /**
     * Generate kisi-kisi sekolah per kelas (bertahap) - dengan data SEMUA SEMESTER
     * Menghasilkan response yang lebih kecil dan reliable
     * Menggunakan data TP dari semester 1 dan 2 dengan proporsi ketat
     */
    public function generateKisiKisiPerKelas(array $params, string $kelas): array
    {
        $jumlahSoalTotal = (int) ($params['jumlah_soal'] ?? 50);
        $mapelId = (int) ($params['mapel_id'] ?? 0);
        $tpId = (int) ($params['tp_id'] ?? 0);
        $apiKey = (string) ($params['api_key'] ?? '');

        if ($apiKey === '') {
            return [
                'success' => false,
                'message' => 'API Key AI belum tersedia',
                'data' => null
            ];
        }

        // Hitung jumlah soal untuk kelas ini berdasarkan proporsi KETAT
        $proporsiKelas = self::DISTRIBUSI_KELAS[$kelas] ?? 0.3;
        $jumlahSoalKelas = (int) round($jumlahSoalTotal * $proporsiKelas);
        
        // Minimal 3 soal per kelas
        if ($jumlahSoalKelas < 3) {
            $jumlahSoalKelas = 3;
        }

        // Ambil data CP & ATP dari database - SEMUA SEMESTER (1 dan 2)
        $cpAtpData = $this->ambilCpAtpDataAllSemester($mapelId, $tpId);

        // Filter ATP untuk kelas ini (dengan pencarian alias seperti 10 <-> X, 7 <-> VII, dll)
        $kelasAliases = [$kelas];
        if ($kelas === 'X' || $kelas === '10') $kelasAliases = ['X', '10'];
        if ($kelas === 'XI' || $kelas === '11') $kelasAliases = ['XI', '11'];
        if ($kelas === 'XII' || $kelas === '12') $kelasAliases = ['XII', '12'];
        if ($kelas === 'VII' || $kelas === '7') $kelasAliases = ['VII', '7'];
        if ($kelas === 'VIII' || $kelas === '8') $kelasAliases = ['VIII', '8'];
        if ($kelas === 'IX' || $kelas === '9') $kelasAliases = ['IX', '9'];

        $atpKelasMap = [];
        foreach ($kelasAliases as $alias) {
            if (!empty($cpAtpData['fase_kelas_map'][$alias])) {
                foreach ($cpAtpData['fase_kelas_map'][$alias] as $atpItem) {
                    $atpKelasMap[$atpItem['id_tujuan']] = $atpItem;
                }
            }
        }
        $atpKelas = array_values($atpKelasMap);

        // Fallback: Jika belum ada ATP spesifik kelas, gunakan seluruh ATP aktif mapel ini
        if (empty($atpKelas) && !empty($cpAtpData['atp_list'])) {
            $atpKelas = $cpAtpData['atp_list'];
        }
        
        if (empty($atpKelas)) {
            return [
                'success' => false,
                'message' => "Tidak ada Tujuan Pembelajaran (ATP) aktif yang ditemukan untuk Mata Pelajaran ini.",
                'data' => null
            ];
        }

        // Hitung proporsi soal per semester berdasarkan jumlah TP
        $kelasStats = $cpAtpData['kelas_distribution'][$kelas] ?? ['semester_1' => count($atpKelas), 'semester_2' => 0];
        $totalTpKelas = ($kelasStats['semester_1'] ?? 0) + ($kelasStats['semester_2'] ?? 0);
        
        $proporsiSemester = [
            1 => $totalTpKelas > 0 ? $kelasStats['semester_1'] / $totalTpKelas : 0.5,
            2 => $totalTpKelas > 0 ? $kelasStats['semester_2'] / $totalTpKelas : 0.5,
        ];

        // Build prompt khusus untuk kelas ini dengan informasi semester
        $prompt = $this->buildPromptPerKelas($params, $kelas, $jumlahSoalKelas, $atpKelas, $cpAtpData, $proporsiSemester);

        // Generate dengan AI
        $aiResult = $this->ai->generateContent($apiKey, $prompt, $params['ai_model'] ?? '');

        if (!$aiResult['success']) {
            return [
                'success' => false,
                'message' => $aiResult['message'],
                'data' => null,
                'wait_seconds' => $aiResult['wait_seconds'] ?? 0
            ];
        }

        // Parse dan normalisasi
        $parseResult = $this->ai->parseJsonResponse($aiResult['data']);
        
        // Fallback parsing jika parseJsonResponse gagal
        if (!$parseResult['success']) {
            $raw = $aiResult['data'] ?? '';
            
            // Extract JSON array from Gemini response text field
            if (preg_match('/"text":\s*"(\[[^"]*\])"/s', $raw, $matches)) {
                $candidate = $matches[1];
                // Unescape JSON string
                $candidate = str_replace(['\\n', '\\t', '\\r', '\\"'], ["\n", "\t", "\r", '"'], $candidate);
                $parsed = json_decode($candidate, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    $parseResult = ['success' => true, 'data' => $parsed, 'message' => 'Recovered from text field'];
                }
            }
            
            // Try extracting raw JSON array if still failed
            if (!$parseResult['success'] && preg_match('/\[.*\{.*\}.*\]/s', $raw, $matches)) {
                $candidate = $matches[0];
                $candidate = preg_replace('/,\s*\}/', '}', $candidate);
                $candidate = preg_replace('/,\s*\]/', ']', $candidate);
                $candidate = str_replace(["\r\n", "\r", "\n", "\t"], " ", $candidate);
                $candidate = preg_replace('/[\x00-\x1F\x7F]/', '', $candidate);
                $candidate = preg_replace('/\s+/', ' ', $candidate);
                
                $parsed = json_decode($candidate, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    $parseResult = ['success' => true, 'data' => $parsed, 'message' => 'Recovered from raw'];
                }
            }
        }

        if (!$parseResult['success']) {
            log_message('error', 'AI Per Kelas JSON Parse Error: ' . $parseResult['message']);
            return [
                'success' => false,
                'message' => 'AI tidak mengembalikan format JSON yang valid. Error: ' . $parseResult['message'],
                'data' => null
            ];
        }

        // Normalisasi hasil
        $normalized = $this->normalizeItemsPerKelas($parseResult['data'], $kelas);

        return [
            'success' => true,
            'message' => "Kisi-kisi Kelas {$kelas} berhasil digenerate",
            'data' => [
                'items' => $normalized['valid'],
                'kelas' => $kelas,
                'total_generated' => count($normalized['valid']),
                'ai_model' => $aiResult['model'] ?? 'unknown',
                'semester_distribution' => $proporsiSemester,
                'tp_stats' => $kelasStats,
            ]
        ];
    }

    /**
     * Build prompt untuk generate per kelas - dengan data SEMUA SEMESTER
     */
    private function buildPromptPerKelas(array $params, string $kelas, int $jumlahSoal, array $atpKelas, array $cpAtpData, array $proporsiSemester = []): string
    {
        $mapelNama = (string) ($params['mapel_nama'] ?? '');
        $jenisSoalAllowed = $params['jenis_soal'] ?? [1, 2, 3, 4, 5, 6, 7];

        // Filter bentuk soal
        $bentukSoalFiltered = [];
        foreach (self::BENTUK_SOAL as $kode => $bentuk) {
            if (in_array((int) $kode, $jenisSoalAllowed, true)) {
                $bentukSoalFiltered[$kode] = $bentuk;
            }
        }
        if (empty($bentukSoalFiltered)) {
            $bentukSoalFiltered = self::BENTUK_SOAL;
        }
        $allowedJenisStr = implode(', ', array_keys($bentukSoalFiltered));

        // Format proporsi semester untuk tampilan
        $proporsiSmt1 = isset($proporsiSemester[1]) ? round($proporsiSemester[1] * 100, 1) : 50;
        $proporsiSmt2 = isset($proporsiSemester[2]) ? round($proporsiSemester[2] * 100, 1) : 50;

        // Ambil statistik data
        $stats = $cpAtpData['semester_stats'] ?? ['semester_1' => 0, 'semester_2' => 0];
        $kelasDistribusi = $cpAtpData['kelas_distribution'][$kelas] ?? ['semester_1' => 0, 'semester_2' => 0, 'total' => 0];

        $prompt = "Anda adalah AI Assessment Specialist untuk Ujian Sekolah Kurikulum Merdeka Indonesia.\n\n";
        $prompt .= "TUGAS: Buat {$jumlahSoal} KISI-KISI SOAL untuk KELAS {$kelas} saja.\n\n";

        // Informasi Data Semester
        $prompt .= "=== DATA TUJUAN PEMBELAJARAN (TP/ATP) ===\n";
        $prompt .= "Sumber data: SEMESTER 1 dan SEMESTER 2 (gabungan lengkap)\n";
        $prompt .= "Statistik data tersedia:\n";
        $prompt .= "- Semester 1: {$stats['semester_1']} TP ({$proporsiSmt1}% proporsi soal)\n";
        $prompt .= "- Semester 2: {$stats['semester_2']} TP ({$proporsiSmt2}% proporsi soal)\n";
        $prompt .= "- Total untuk Kelas {$kelas}: {$kelasDistribusi['total']} TP\n";
        $prompt .= "  (SMT1: {$kelasDistribusi['semester_1']}, SMT2: {$kelasDistribusi['semester_2']})\n\n";

        // Distribusi Bloom untuk kelas ini
        $prompt .= "=== DISTRIBUSI LEVEL KOGNITIF BLOOM (WAJIB) ===\n";
        $prompt .= "Distribusi soal berdasarkan level kognitif:\n";
        $prompt .= "- C1 (Mengingat): ~20% (sekitar " . round($jumlahSoal * 0.2) . " soal)\n";
        $prompt .= "- C2 (Memahami): ~25% (sekitar " . round($jumlahSoal * 0.25) . " soal)\n";
        $prompt .= "- C3 (Menerapkan): ~30% (sekitar " . round($jumlahSoal * 0.3) . " soal)\n";
        $prompt .= "- C4 (Menganalisis): ~20% (sekitar " . round($jumlahSoal * 0.2) . " soal)\n";
        $prompt .= "- C5 (Mengevaluasi): ~5% (sekitar " . round($jumlahSoal * 0.05) . " soal)\n\n";

        // Bentuk Soal yang dipilih
        $prompt .= "=== BENTUK SOAL YANG DIPILIH (HANYA GUNAKAN YANG BERIKUT) ===\n";
        foreach ($bentukSoalFiltered as $kode => $bentuk) {
            $cocok = implode(', ', $bentuk['cocok']);
            $prompt .= "{$kode}. {$bentuk['nama']} - Cocok untuk level: {$cocok}\n";
        }
        $prompt .= "\nPENTING: Gunakan HANYA jenis soal di atas (kode: {$allowedJenisStr}).\n\n";

        // Data CP & ATP untuk kelas ini - group by semester
        $prompt .= "=== CAPAIAN PEMBELAJARAN DAN TUJUAN UNTUK KELAS {$kelas} ===\n";
        $prompt .= "Mata Pelajaran: {$mapelNama}\n\n";

        // Group ATP by CP dan semester
        $atpByCpSemester = [];
        foreach ($atpKelas as $atp) {
            $kodeCp = $atp['kode_cp'] ?? 'Lainnya';
            $semester = $atp['semester'] ?? 1;
            if (!isset($atpByCpSemester[$kodeCp])) {
                $atpByCpSemester[$kodeCp] = ['smt1' => [], 'smt2' => []];
            }
            if ($semester === 1) {
                $atpByCpSemester[$kodeCp]['smt1'][] = $atp;
            } else {
                $atpByCpSemester[$kodeCp]['smt2'][] = $atp;
            }
        }

        foreach ($atpByCpSemester as $kodeCp => $atps) {
            $cpInfo = $cpAtpData['cp_list'][$kodeCp] ?? null;
            $elemen = $cpInfo ? $cpInfo['elemen'] : 'Elemen tidak tersedia';
            $prompt .= "CP {$kodeCp}: {$elemen}\n";
            
            // Semester 1
            if (!empty($atps['smt1'])) {
                $prompt .= "  [Semester 1]:\n";
                foreach ($atps['smt1'] as $atp) {
                    $prompt .= "    - [ID:{$atp['id_tujuan']}] {$atp['kode']}: {$atp['tujuan']}\n";
                    if ($atp['materi_pokok']) {
                        $prompt .= "      Materi: {$atp['materi_pokok']}\n";
                    }
                }
            }
            
            // Semester 2
            if (!empty($atps['smt2'])) {
                $prompt .= "  [Semester 2]:\n";
                foreach ($atps['smt2'] as $atp) {
                    $prompt .= "    - [ID:{$atp['id_tujuan']}] {$atp['kode']}: {$atp['tujuan']}\n";
                    if ($atp['materi_pokok']) {
                        $prompt .= "      Materi: {$atp['materi_pokok']}\n";
                    }
                }
            }
            $prompt .= "\n";
        }

        $prompt .= "=== ATURAN PEMBUATAN SOAL (WAJIB) ===\n";
        $prompt .= "1. Setiap soal HARUS memiliki:\n";
        $prompt .= '   - "kelas": "' . $kelas . '" (KELAS WAJIB TETAP)' . "\n";
        $prompt .= '   - "level_kognitif": Pilih dari C1, C2, C3, C4, C5 sesuai distribusi di atas (JANGAN SEMUA C4!)' . "\n";
        $prompt .= '   - "jenis_soal": Pilih dari kode ' . $allowedJenisStr . " yang sesuai dengan level kognitif\n";
        $prompt .= '   - "id_tujuan": ID ATP dari daftar di atas (HARUS VALID)' . "\n";
        $prompt .= '   - "kode_cp": Kode CP yang sesuai dengan ATP' . "\n";
        $prompt .= '   - "elemen_cp": Elemen CP lengkap' . "\n";
        $prompt .= '   - "materi": Materi pokok dari ATP (spesifik dan berbeda tiap soal)' . "\n";
        $prompt .= '   - "indikator_soal": Indikator dengan KKO Bloom yang sesuai level (misal: C2=Memahami, C3=Menerapkan)' . "\n";
        $prompt .= '   - "estimasi_waktu": Menit (1-5, default: 1)' . "\n\n";
        
        $prompt .= "2. PANDUAN PEMILIHAN LEVEL KOGNITIF & JENIS SOAL:\n";
        foreach (self::BENTUK_SOAL as $kode => $bentuk) {
            if (in_array((int) $kode, $jenisSoalAllowed, true)) {
                $cocokLevels = implode(', ', $bentuk['cocok']);
                $prompt .= "   - Jenis {$kode} ({$bentuk['nama']}): Cocok untuk level {$cocokLevels}\n";
            }
        }
        $prompt .= "\n";        $prompt .= "3. PROPORSI SOAL PER SEMESTER (WAJIB DIJAGA):\n";
        $targetSmt1 = round($jumlahSoal * ($proporsiSmt1 / 100));
        $targetSmt2 = $jumlahSoal - $targetSmt1;
        $prompt .= "   - Semester 1: ~{$targetSmt1} soal ({$proporsiSmt1}% dari {$jumlahSoal})\n";
        $prompt .= "   - Semester 2: ~{$targetSmt2} soal ({$proporsiSmt2}% dari {$jumlahSoal})\n";
        $prompt .= "   AI harus mengambil ATP dari kedua semester sesuai proporsi ini!\n\n";
        
        $prompt .= "3. Prioritas pemilihan ATP:\n";
        $prompt .= "   - Prioritaskan ATP yang berasal dari Semester 1 untuk soal-soal awal\n";
        $prompt .= "   - Lanjutkan dengan ATP dari Semester 2 untuk soal-soal berikutnya\n";
        $prompt .= "   - Setiap CP harus memiliki minimal 1 soal (jika ada ATP-nya)\n";
        $prompt .= "   - Distribusikan soal merata antar ATP dalam satu CP\n\n";
        
        $prompt .= "4. Indikator dan Materi:\n";
        $prompt .= "   - Indikator HARUS diawali dengan KKO Bloom yang valid\n";
        $prompt .= "   - Materi tiap soal harus berbeda (unik, tidak duplikat)\n";
        $prompt .= "   - Gunakan HANYA jenis soal yang tercantum di atas\n";
        $prompt .= "   - Estimasi waktu harus realistis sesuai kompleksitas soal\n\n";

        $prompt .= "=== FORMAT OUTPUT (JSON Array - PASTIKAN VALID) ===\n";
        $prompt .= "[\n";
        $prompt .= '  {"kelas": "' . $kelas . '", "level_kognitif": "C3", "jenis_soal": 1, "id_tujuan": 123, "kode_cp": "CP-01", "elemen_cp": "...", "materi": "...", "indikator_soal": "...", "estimasi_waktu": 2},' . "\n";
        $prompt .= "  ...\n";
        $prompt .= "]\n\n";

        $prompt .= "=== ATURAN MUTLAK ===\n";
        $prompt .= "- Output HARUS tepat {$jumlahSoal} objek (tidak lebih, tidak kurang)\n";
        $prompt .= "- Semua ID tujuan HARUS valid dan berasal dari daftar ATP di atas\n";
        $prompt .= "- Proporsi semester ({$proporsiSmt1}% SMT1, {$proporsiSmt2}% SMT2) WAJIB dijaga\n";
        $prompt .= "- Setiap materi dan indikator HARUS unik (tidak boleh sama antar soal)\n";
        $prompt .= "- Gunakan HANYA jenis_soal: {$allowedJenisStr} (JANGAN gunakan yang lain)\n";
        $prompt .= "- Level kognitif harus sesuai dengan KKO yang digunakan dalam indikator\n\n";

        if (!empty($params['instruksi_tambahan'])) {
            $prompt .= "=== INSTRUKSI TAMBAHAN DARI GURU ===\n";
            $prompt .= $params['instruksi_tambahan'] . "\n";
        }

        return $prompt;
    }

    /**
     * Normalisasi hasil per kelas
     */
    private function normalizeItemsPerKelas($parsed, string $kelasTarget): array
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
                $result['rejected'][] = ['index' => $idx, 'reason' => 'Format bukan objek'];
                continue;
            }

            // Force kelas sesuai target
            $row['kelas'] = $kelasTarget;

            // Validasi level kognitif
            $levelKognitif = strtoupper(trim((string) ($row['level_kognitif'] ?? '')));
            if (!in_array($levelKognitif, ['C1', 'C2', 'C3', 'C4', 'C5'], true)) {
                $levelKognitif = 'C3'; // Default
            }

            // Validasi jenis soal
            $jenisSoal = (int) ($row['jenis_soal'] ?? 1);
            if ($jenisSoal < 1 || $jenisSoal > 7) {
                $jenisSoal = 1;
            }

            // Cek kualitas minimal
            $plainIndikator = trim(strip_tags((string) ($row['indikator_soal'] ?? '')));
            if (mb_strlen($plainIndikator) < 5) {
                $result['rejected'][] = ['index' => $idx, 'reason' => 'Indikator terlalu singkat'];
                continue;
            }

            // Cek duplikat
            $fingerprint = mb_strtolower($plainIndikator);
            if (isset($seen[$fingerprint])) {
                continue; // Skip duplikat silently
            }
            $seen[$fingerprint] = true;

            $result['valid'][] = [
                'kelas' => $kelasTarget,
                'level_kognitif' => $levelKognitif,
                'jenis_soal' => $jenisSoal,
                'id_tujuan' => (int) ($row['id_tujuan'] ?? 0),
                'kode_cp' => trim((string) ($row['kode_cp'] ?? '')),
                'elemen_cp' => trim((string) ($row['elemen_cp'] ?? '')),
                'materi' => trim((string) ($row['materi'] ?? '')),
                'indikator_soal' => trim((string) ($row['indikator_soal'] ?? '')),
                'estimasi_waktu' => max(1, min(20, (int) ($row['estimasi_waktu'] ?? 1))),
            ];
        }

        return $result;
    }
}
