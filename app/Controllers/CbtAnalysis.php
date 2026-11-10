<?php

namespace App\Controllers;

use App\Models\CbtAnalysisModel;
use App\Models\CbtModel;
use App\Models\DashboardModel;
use App\Models\DropdownModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * CbtAnalysisController - Controller untuk analisis hasil ujian berbasis Kurikulum Merdeka
 */
class CbtAnalysis extends BaseController
{
    protected $analysis;
    protected $cbt;
    protected $dashboard;
    protected $dropdown;
    protected $cache;
    
    // Cache TTL settings
    protected const CACHE_TTL_ANALYSIS = 600; // 10 minutes for analysis data
    protected const CACHE_TTL_SHORT = 300;    // 5 minutes for frequently changing data
    
    public function __construct()
    {
        $this->analysis = new CbtAnalysisModel();
        $this->cbt = new CbtModel();
        $this->dashboard = new DashboardModel();
        $this->dropdown = new DropdownModel();
        $this->cache = \Config\Services::cache();
    }
    
    /**
     * Dashboard analisis hasil ujian
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        
        $user = $this->getUser();
        
        // Only allow admin, guru, or panitia with jabatan 'panitia' (not 'pengawas')
        if ($this->isPanitia() && !$this->hasPanitiaJabatan()) {
            return $this->showError('Akses ditolak. Hanya Administrator atau Panitia yang dapat mengakses Analisis TP.', 403);
        }
        
        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
            return $this->showError('Hanya Administrator, Guru, dan Panitia yang diberi hak untuk mengakses halaman ini', 403);
        }
        
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        
        $idJadwal = $this->request->getGet('id_jadwal');
        $idKelas = $this->request->getGet('id_kelas');
        
        // Ambil data jadwal yang tersedia
        $jadwals = $this->cbt->getAllJadwal($tp->id_tp, $smt->id_smt);
        
        $data = [
            'user' => $user,
            'judul' => 'Analisis Hasil Ujian',
            'subjudul' => 'Analisis Berbasis Kurikulum Merdeka',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $smt,
            'jadwals' => $jadwals,
            'id_jadwal_selected' => $idJadwal,
            'id_kelas_selected' => $idKelas
        ];
        
        // Jika jadwal dipilih, lakukan analisis dengan caching
        if ($idJadwal) {
            $idJadwalInt = (int)$idJadwal;
            $idKelasInt = $idKelas ? (int)$idKelas : null;
            
            // Generate cache key (using underscore instead of colon to avoid reserved chars)
            $cacheKey = "cbt_analysis_{$idJadwalInt}_" . ($idKelasInt ?? 'all');
            
            // Try to get cached analysis data
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                $data['analisis'] = $cached;
                $data['chart_data'] = $this->prepareChartData($cached);
                
                // Ambil data kelas untuk filter
                if ($this->isAdmin() || $this->isPanitia()) {
                    $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
                } else {
                    $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
                }
                
                return $this->renderView('cbt/analisis/dashboard', $data);
            }
            
            $startTime = microtime(true);

            // 1-6. Run analysis in parallel where possible
            $hasilUjian = $this->analysis->getHasilUjianWithTP($idJadwalInt, $idKelasInt);
            $topikBermasalah = $this->analysis->analyzeTopikBermasalah($idJadwalInt, $idKelasInt);
            $statistikMapel = $this->analysis->getStatistikNilaiPerMapel($idJadwalInt, $idKelasInt);
            $kategoriSiswa = $this->analysis->kategorikanSiswa($idJadwalInt, $idKelasInt);
            $analisisKorelasi = $this->analysis->analisisKorelasiTP($idJadwalInt, $idKelasInt);
            $rencanaRemedial = $this->analysis->generateRencanaRemedial($topikBermasalah);

            // 6b. Analisis butir soal - limit to prevent memory issues
            $analisisButir = $this->analysis->analyzeItemStatistics($idJadwalInt, $idKelasInt);

            // 6c. Statistik deskriptif & kualitas tes
            $statistikDeskriptif = $this->analysis->getStatistikDeskriptif($idJadwalInt, $idKelasInt);
            $ringkasanKualitas = $this->analysis->getRingkasanKualitas($idJadwalInt, $idKelasInt);
            $histogramSkor = $this->analysis->getHistogramSkor($idJadwalInt, $idKelasInt);
            
            // 7. Prediksi capaian - batch prediction instead of N+1
            $prediksiSiswa = [];
            $siswaPerluPeningkatan = array_merge($kategoriSiswa['intervensi'] ?? [], $kategoriSiswa['dasar'] ?? []);
            
            // Limit to top 20 students to prevent resource exhaustion
            $siswaPerluPeningkatan = array_slice($siswaPerluPeningkatan, 0, 20);
            
            if (!empty($siswaPerluPeningkatan)) {
                // Get all predictions in a single batch query
                $prediksiBatch = $this->analysis->prediksiCapaianBatch($idJadwalInt, array_column($siswaPerluPeningkatan, 'id_siswa'));
                
                foreach ($siswaPerluPeningkatan as $siswa) {
                    $prediksi = $prediksiBatch[$siswa['id_siswa']] ?? [
                        'nilai_prediksi' => 0,
                        'kategori' => 'Tidak Ada Data',
                        'rekomendasi' => '-',
                        'detail_tp' => [],
                        'jumlah_tp_dikuasai' => 0
                    ];
                    $prediksi['nama_siswa'] = $siswa['nama_siswa'] ?? 'Siswa';
                    $prediksi['nis'] = $siswa['nis'] ?? '-';
                    $prediksiSiswa[] = $prediksi;
                }
            }
            
            $executionTime = microtime(true) - $startTime;
            
            $analisisData = [
                'hasil_ujian' => $hasilUjian,
                'topik_bermasalah' => $topikBermasalah,
                'statistik_mapel' => $statistikMapel,
                'kategori_siswa' => $kategoriSiswa,
                'analisis_korelasi' => $analisisKorelasi,
                'rencana_remedial' => $rencanaRemedial,
                'analisis_butir' => $analisisButir,
                'prediksi_siswa' => $prediksiSiswa,
                'statistik_deskriptif' => $statistikDeskriptif,
                'ringkasan_kualitas' => $ringkasanKualitas,
                'histogram_skor' => $histogramSkor,
                'execution_time' => round($executionTime, 3),
                'jumlah_siswa' => count($hasilUjian) > 0 ? count(array_unique(array_column($hasilUjian, 'id_siswa'))) : 0,
                'jumlah_soal' => count($hasilUjian) > 0 ? count(array_unique(array_column($hasilUjian, 'id_soal'))) : 0
            ];
            
            // Cache the analysis data
            $this->cache->save($cacheKey, $analisisData, self::CACHE_TTL_ANALYSIS);
            
            $data['analisis'] = $analisisData;
            
            // Siapkan data untuk visualisasi
            $data['chart_data'] = $this->prepareChartData($data['analisis']);
        }
        
        // Ambil data kelas untuk filter
        if ($this->isAdmin() || $this->isPanitia()) {
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        } else {
            // Untuk guru, ambil kelas yang diajar (menggunakan getAllKelas karena getKelasByGuru tidak ada)
            $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
            $data['kelas'] = $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt);
        }
        
        return $this->renderView('cbt/analisis/dashboard', $data);
    }
    
    /**
     * Siapkan data untuk visualisasi chart
     */
    private function prepareChartData(array $analisis): array
    {
        $chartData = [];
        
        // Chart 1: Topik Bermasalah
        if (!empty($analisis['topik_bermasalah'])) {
            $chartData['topik_bermasalah'] = [
                'labels' => array_column($analisis['topik_bermasalah'], 'tujuan_kode'),
                'data' => array_column($analisis['topik_bermasalah'], 'persentase_kesalahan'),
                'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            ];
        }

        // Chart 1b: Histogram distribusi skor
        if (!empty($analisis['histogram_skor']['labels'])) {
            $chartData['histogram_skor'] = [
                'labels' => $analisis['histogram_skor']['labels'],
                'data' => $analisis['histogram_skor']['data']
            ];
        }

        // Chart 1c: Distribusi kualitas daya beda butir
        if (!empty($analisis['analisis_butir']['summary'])) {
            $dayaBeda = $analisis['analisis_butir']['summary']['daya_beda'] ?? [];
            $chartData['daya_beda_butir'] = [
                'labels' => ['Baik (D>=0.40)', 'Cukup (0.20-0.39)', 'Jelek (<0.20)'],
                'data' => [
                    (int) ($dayaBeda['baik'] ?? 0),
                    (int) ($dayaBeda['cukup'] ?? 0),
                    (int) ($dayaBeda['jelek'] ?? 0)
                ]
            ];
        }

        // Chart 2: Kategori Siswa (Pie Chart) Rapor Pendidikan
        if (!empty($analisis['kategori_siswa'])) {
            $kategori = $analisis['kategori_siswa'];
            $chartData['kategori_siswa'] = [
                'labels' => ['Mahir', 'Cakap', 'Dasar', 'Intervensi'],
                'data' => [
                    count($kategori['mahir'] ?? []),
                    count($kategori['cakap'] ?? []),
                    count($kategori['dasar'] ?? []),
                    count($kategori['intervensi'] ?? [])
                ],
                'backgroundColor' => ['#198754', '#0d6efd', '#ffc107', '#dc3545']
            ];
        }
        
        // Chart 3: Statistik Mapel
        if (!empty($analisis['statistik_mapel'])) {
            $chartData['statistik_mapel'] = [
                'labels' => array_column($analisis['statistik_mapel'], 'nama_mapel'),
                'rata_rata' => array_column($analisis['statistik_mapel'], 'rata_rata'),
                'kkm' => array_column($analisis['statistik_mapel'], 'kkm'),
                'persentase_tuntas' => array_column($analisis['statistik_mapel'], 'persentase_tuntas')
            ];
        }

        // Chart 5: Distribusi kesukaran butir
        if (!empty($analisis['analisis_butir']['summary']['kesukaran'])) {
            $kesukaran = $analisis['analisis_butir']['summary']['kesukaran'];
            $chartData['kesukaran_butir'] = [
                'labels' => ['Mudah', 'Sedang', 'Sulit'],
                'data' => [
                    (int) ($kesukaran['mudah'] ?? 0),
                    (int) ($kesukaran['sedang'] ?? 0),
                    (int) ($kesukaran['sulit'] ?? 0),
                ]
            ];
        }
        
        // Chart 4: Distribusi Nilai
        if (!empty($analisis['hasil_ujian'])) {
            $nilai = array_column($analisis['hasil_ujian'], 'nilai');
            $distribusi = $this->hitungDistribusiNilai($nilai);
            $chartData['distribusi_nilai'] = [
                'labels' => array_keys($distribusi),
                'data' => array_values($distribusi)
            ];
        }
        
        return $chartData;
    }
    
    /**
     * Hitung distribusi nilai
     */
    private function hitungDistribusiNilai(array $nilai): array
    {
        $distribusi = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0
        ];
        
        foreach ($nilai as $n) {
            $n = (float) $n;
            if ($n <= 20) $distribusi['0-20']++;
            elseif ($n <= 40) $distribusi['21-40']++;
            elseif ($n <= 60) $distribusi['41-60']++;
            elseif ($n <= 80) $distribusi['61-80']++;
            else $distribusi['81-100']++;
        }
        
        return $distribusi;
    }
    
    /**
     * Export data ke Excel
     */
    public function exportExcel()
    {
        $idJadwal = $this->request->getGet('id_jadwal');
        $idKelas = $this->request->getGet('id_kelas');
        $filterJenis = $this->request->getGet('filter_jenis');
        $filterKesukaran = $this->request->getGet('filter_kesukaran');
        $filterKualitasD = $this->request->getGet('filter_kualitas_d');
        $filterPreset = $this->request->getGet('filter_preset');
        $filterBermasalah = (string) $this->request->getGet('filter_bermasalah') === '1';
        
        if (!$idJadwal) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak valid']);
        }
        
        // Ambil data analisis
        $hasilUjian = $this->analysis->getHasilUjianWithTP($idJadwal, $idKelas);
        $topikBermasalah = $this->analysis->analyzeTopikBermasalah($idJadwal, $idKelas);
        $kategoriSiswa = $this->analysis->kategorikanSiswa($idJadwal, $idKelas);
        $analisisButir = $this->analysis->analyzeItemStatistics($idJadwal, $idKelas);
        if (!empty($analisisButir['items'])) {
            $analisisButir['items'] = $this->applyButirExportFilter(
                $analisisButir['items'],
                $filterJenis,
                $filterKesukaran,
                $filterKualitasD,
                $filterPreset,
                $filterBermasalah
            );
        }
        
        // Siapkan data untuk Excel
        $data = [
            'hasil_ujian' => $hasilUjian,
            'topik_bermasalah' => $topikBermasalah,
            'kategori_siswa' => $kategoriSiswa,
            'analisis_butir' => $analisisButir,
            'filter' => [
                'jenis' => $filterJenis,
                'kesukaran' => $filterKesukaran,
                'kualitas_d' => $filterKualitasD,
                'preset' => $filterPreset,
                'bermasalah' => $filterBermasalah,
            ]
        ];
        
        // Generate Excel (implementasi sederhana)
        $filename = 'analisis_ujian_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Set header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Sederhana: output sebagai CSV dulu
        $this->generateCSV($data);
        exit;
    }
    
    /**
     * Generate CSV sederhana
     */
    private function generateCSV(array $data): void
    {
        // Output buffer
        $output = fopen('php://output', 'w');
        
        // Header
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        // Sheet 1: Hasil Ujian
        fputcsv($output, ['HASIL UJIAN PER SISWA']);
        fputcsv($output, ['No', 'NIS', 'Nama Siswa', 'Tujuan Pembelajaran', 'Nilai', 'Benar/Salah']);
        
        $no = 1;
        foreach ($data['hasil_ujian'] as $row) {
            fputcsv($output, [
                $no++,
                $row['nis'],
                $row['nama_siswa'],
                $row['tujuan_kode'] . ' - ' . $row['tujuan_teks'],
                $row['nilai'],
                $row['iscorrect'] ? 'Benar' : 'Salah'
            ]);
        }
        
        // Sheet 2: Topik Bermasalah
        fputcsv($output, []);
        fputcsv($output, ['TOPIK BERMASALAH']);
        fputcsv($output, ['No', 'Kode TP', 'Tujuan Pembelajaran', 'Persentase Kesalahan', 'Rata-rata Nilai']);
        
        $no = 1;
        foreach ($data['topik_bermasalah'] as $row) {
            fputcsv($output, [
                $no++,
                $row['tujuan_kode'],
                $row['tujuan_teks'],
                $row['persentase_kesalahan'] . '%',
                round($row['rata_rata_nilai'], 2)
            ]);
        }

        // Sheet 3: Analisis Butir
        fputcsv($output, []);
        fputcsv($output, ['ANALISIS BUTIR SOAL']);
        $rel = $data['analisis_butir']['reliability'] ?? [];
        fputcsv($output, [
            'Reliabilitas Alpha Cronbach',
            $rel['alpha_cronbach'] ?? 0,
            $rel['label'] ?? '-'
        ]);
        fputcsv($output, [
            'Filter Export',
            'Jenis=' . ($data['filter']['jenis'] ?: 'Semua') .
            '; Kesukaran=' . ($data['filter']['kesukaran'] ?: 'Semua') .
            '; D=' . ($data['filter']['kualitas_d'] ?: 'Semua') .
            '; Preset=' . ($data['filter']['preset'] ?: 'Semua') .
            '; Bermasalah=' . ($data['filter']['bermasalah'] ? 'Ya' : 'Tidak')
        ]);
        fputcsv($output, ['No', 'No Soal', 'Jenis', 'Kesukaran (P)', 'Kategori Kesukaran', 'Daya Beda (D)', 'Kategori Daya Beda', 'Validitas']);
        $no = 1;
        foreach (($data['analisis_butir']['items'] ?? []) as $item) {
            fputcsv($output, [
                $no++,
                $item['nomor_soal'] ?? 0,
                $item['jenis'] ?? 0,
                $item['tingkat_kesukaran'] ?? 0,
                $item['kategori_kesukaran'] ?? '-',
                $item['daya_beda'] ?? 0,
                $item['kategori_daya_beda'] ?? '-',
                $item['validitas_item'] ?? 0,
            ]);
        }
        
        fclose($output);
    }

    private function applyButirExportFilter(
        array $items,
        ?string $jenis,
        ?string $kesukaran,
        ?string $kualitasD,
        ?string $preset,
        bool $bermasalahOnly
    ): array {
        return array_values(array_filter($items, function ($item) use ($jenis, $kesukaran, $kualitasD, $preset, $bermasalahOnly) {
            $rowJenis = (string) ($item['jenis'] ?? '');
            $rowKesukaran = (string) ($item['kategori_kesukaran'] ?? '');
            $d = (float) ($item['daya_beda'] ?? 0);
            $p = (float) ($item['tingkat_kesukaran'] ?? 0);
            $validitas = (float) ($item['validitas_item'] ?? 0);

            if (!empty($jenis) && $rowJenis !== (string) $jenis) {
                return false;
            }
            if (!empty($kesukaran) && $rowKesukaran !== (string) $kesukaran) {
                return false;
            }
            if (!empty($kualitasD)) {
                $rowKualitasD = $d >= 0.40 ? 'good' : ($d < 0.20 ? 'poor' : 'fair');
                if ($rowKualitasD !== $kualitasD) {
                    return false;
                }
            }
            if (!empty($preset)) {
                if ($preset === 'too_easy' && !($p >= 0.70)) {
                    return false;
                }
                if ($preset === 'too_hard' && !($p < 0.30)) {
                    return false;
                }
                if ($preset === 'low_discrimination' && !($d < 0.20)) {
                    return false;
                }
                if ($preset === 'low_validity' && !($validitas < 0.30)) {
                    return false;
                }
                if ($preset === 'high_validity' && !($validitas >= 0.70)) {
                    return false;
                }
            }
            if ($bermasalahOnly) {
                if (!($d < 0.20 || $validitas < 0.30)) {
                    return false;
                }
            }
            return true;
        }));
    }

    public function apiSummary()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi habis']);
        }
        
        // Only allow admin, guru, or panitia with jabatan 'panitia' (not 'pengawas')
        if ($this->isPanitia() && !$this->hasPanitiaJabatan()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }
        
        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);
        }

        $idJadwal = (int) ($this->request->getGet('id_jadwal') ?? 0);
        $idKelas = (int) ($this->request->getGet('id_kelas') ?? 0);
        if ($idJadwal <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'id_jadwal wajib diisi']);
        }

        $hasil = $this->analysis->getHasilUjianWithTP($idJadwal, $idKelas > 0 ? $idKelas : null);
        $topik = $this->analysis->analyzeTopikBermasalah($idJadwal, $idKelas > 0 ? $idKelas : null, 10);
        $kategori = $this->analysis->kategorikanSiswa($idJadwal, $idKelas > 0 ? $idKelas : null);
        $butir = $this->analysis->analyzeItemStatistics($idJadwal, $idKelas > 0 ? $idKelas : null);
        $sd = $this->analysis->getStatistikDeskriptif($idJadwal, $idKelas > 0 ? $idKelas : null);
        $rk = $this->analysis->getRingkasanKualitas($idJadwal, $idKelas > 0 ? $idKelas : null);

        $uniqueSiswa = count(array_unique(array_column($hasil, 'id_siswa')));
        $nilai = array_map(static fn($r) => (float) ($r['nilai'] ?? 0), $hasil);
        $avg = count($nilai) > 0 ? array_sum($nilai) / count($nilai) : 0.0;
        $min = count($nilai) > 0 ? min($nilai) : 0.0;
        $max = count($nilai) > 0 ? max($nilai) : 0.0;

        $rekomendasi = [];
        if (!empty($topik)) {
            $rekomendasi[] = 'Fokus remedial pada TP: ' . implode(', ', array_column(array_slice($topik, 0, 3), 'tujuan_kode'));
        }
        $jumlahIntervensi = count($kategori['intervensi'] ?? []);
        if ($jumlahIntervensi > 0) {
            $rekomendasi[] = 'Jalankan pendampingan intensif untuk ' . $jumlahIntervensi . ' siswa kategori Perlu Intervensi Khusus.';
        }
        $alpha = (float) ($butir['reliability']['alpha_cronbach'] ?? 0);
        if ($alpha < 0.70) {
            $rekomendasi[] = 'Reliabilitas tes rendah (alpha ' . round($alpha, 3) . '), lakukan revisi butir soal.';
        }
        if (empty($rekomendasi)) {
            $rekomendasi[] = 'Hasil relatif stabil, lanjutkan pengayaan pada TP dengan capaian tinggi.';
        }

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'deskriptif' => [
                    'jumlah_siswa' => $uniqueSiswa,
                    'jumlah_respon' => count($hasil),
                    'rata_rata' => $sd['mean'] ?? round($avg, 2),
                    'median' => $sd['median'] ?? 0,
                    'modus' => $sd['modus'] ?? 0,
                    'std_dev' => $sd['std_dev'] ?? 0,
                    'min' => $sd['min'] ?? round($min, 2),
                    'max' => $sd['max'] ?? round($max, 2),
                    'q1' => $sd['q1'] ?? 0,
                    'q3' => $sd['q3'] ?? 0,
                    'iqr' => $sd['iqr'] ?? 0,
                    'skewness' => $sd['skewness'] ?? 0,
                    'normality' => $sd['normality'] ?? [],
                ],
                'kategori_siswa' => [
                    'mahir' => count($kategori['mahir'] ?? []),
                    'cakap' => count($kategori['cakap'] ?? []),
                    'dasar' => count($kategori['dasar'] ?? []),
                    'intervensi' => count($kategori['intervensi'] ?? []),
                ],
                'topik_bermasalah' => $topik,
                'reliabilitas' => $butir['reliability'] ?? [],
                'kualitas_butir' => [
                    'baik' => $rk['baik'] ?? 0,
                    'perlu_revisi' => $rk['perlu_revisi'] ?? 0,
                    'persen_baik' => $rk['persen_baik'] ?? 0,
                ],
                'rekomendasi' => $rekomendasi,
            ]
        ]);
    }

    public function exportPdf()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        
        // Only allow admin, guru, or panitia with jabatan 'panitia' (not 'pengawas')
        if ($this->isPanitia() && !$this->hasPanitiaJabatan()) {
            return $this->showError('Akses ditolak', 403);
        }
        
        if (!$this->isAdmin() && !$this->inGroup('guru') && !$this->isPanitia()) {
            return $this->showError('Akses ditolak', 403);
        }

        $idJadwal = (int) ($this->request->getGet('id_jadwal') ?? 0);
        $idKelas = (int) ($this->request->getGet('id_kelas') ?? 0);
        if ($idJadwal <= 0) {
            return $this->response->setStatusCode(422)->setBody('id_jadwal wajib diisi');
        }

        $hasil = $this->analysis->getHasilUjianWithTP($idJadwal, $idKelas > 0 ? $idKelas : null);
        $topik = $this->analysis->analyzeTopikBermasalah($idJadwal, $idKelas > 0 ? $idKelas : null, 10);
        $kategori = $this->analysis->kategorikanSiswa($idJadwal, $idKelas > 0 ? $idKelas : null);
        $butir = $this->analysis->analyzeItemStatistics($idJadwal, $idKelas > 0 ? $idKelas : null);
        $sd = $this->analysis->getStatistikDeskriptif($idJadwal, $idKelas > 0 ? $idKelas : null);
        $rk = $this->analysis->getRingkasanKualitas($idJadwal, $idKelas > 0 ? $idKelas : null);

        $nilai = array_map(static fn($r) => (float) ($r['nilai'] ?? 0), $hasil);
        $avg = count($nilai) > 0 ? array_sum($nilai) / count($nilai) : 0.0;

        $rowsTopik = '';
        foreach ($topik as $i => $t) {
            $rowsTopik .= '<tr><td>' . ($i + 1) . '</td><td>' . esc((string) ($t['tujuan_kode'] ?? '-')) . '</td><td>' . esc((string) ($t['tujuan_teks'] ?? '-')) . '</td><td>' . esc((string) ($t['persentase_kesalahan'] ?? '0')) . '%</td></tr>';
        }
        if ($rowsTopik === '') {
            $rowsTopik = '<tr><td colspan="4">Tidak ada data topik bermasalah.</td></tr>';
        }

        $alpha = (float) ($butir['reliability']['alpha_cronbach'] ?? 0);
        $norm = $sd['normality'] ?? [];
        $html = '<html><head><meta charset="utf-8"><style>
            body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#111}
            h1{font-size:18px;margin:0 0 8px}
            h2{font-size:14px;margin:14px 0 6px}
            table{width:100%;border-collapse:collapse;margin-top:6px}
            th,td{border:1px solid #ccc;padding:6px 8px;vertical-align:top}
            th{background:#f3f4f6;text-align:left}
            .meta{margin-bottom:10px}
            .badge{display:inline-block;padding:2px 8px;border-radius:10px;background:#eef2ff}
        </style></head><body>
            <h1>Laporan Analisis Hasil Ujian</h1>
            <div class="meta">Jadwal: <b>#' . $idJadwal . '</b> | Kelas: <b>' . ($idKelas > 0 ? $idKelas : 'Semua') . '</b> | Tanggal cetak: ' . date('d-m-Y H:i') . '</div>
            <h2>Ringkasan Deskriptif</h2>
            <table>
                <tr><th>Jumlah Siswa</th><td>' . ($sd['n'] ?? count($hasil)) . '</td><th>Rata-rata (Mean)</th><td>' . ($sd['mean'] ?? round($avg, 2)) . '</td></tr>
                <tr><th>Median</th><td>' . ($sd['median'] ?? '0') . '</td><th>Modus</th><td>' . ($sd['modus'] ?? '0') . '</td></tr>
                <tr><th>Std. Deviasi</th><td>' . ($sd['std_dev'] ?? '0') . '</td><th>Varians</th><td>' . ($sd['varians'] ?? '0') . '</td></tr>
                <tr><th>Nilai Min</th><td>' . ($sd['min'] ?? '0') . '</td><th>Nilai Max</th><td>' . ($sd['max'] ?? '0') . '</td></tr>
                <tr><th>Kuartil Q1</th><td>' . ($sd['q1'] ?? '0') . '</td><th>Kuartil Q3 (IQR)</th><td>' . ($sd['q3'] ?? '0') . ' (' . ($sd['iqr'] ?? '0') . ')</td></tr>
                <tr><th>Skewness</th><td>' . ($sd['skewness'] ?? '0') . '</td><th>Bentuk Distribusi</th><td>' . esc((string) ($norm['label'] ?? '-')) . '</td></tr>
                <tr><th>Mahir</th><td>' . count($kategori['mahir'] ?? []) . '</td><th>Cakap</th><td>' . count($kategori['cakap'] ?? []) . '</td></tr>
                <tr><th>Dasar</th><td>' . count($kategori['dasar'] ?? []) . '</td><th>Perlu Intervensi</th><td>' . count($kategori['intervensi'] ?? []) . '</td></tr>
                <tr><th>Butir Layak / Revisi</th><td>' . ($rk['baik'] ?? 0) . ' / ' . ($rk['perlu_revisi'] ?? 0) . '</td><th>Reliabilitas (α)</th><td>' . round($alpha, 3) . ' (' . esc((string) ($butir['reliability']['label'] ?? '-')) . ')</td></tr>
            </table>

            <h2>Topik Bermasalah</h2>
            <table>
                <thead><tr><th>No</th><th>Kode TP</th><th>Tujuan Pembelajaran</th><th>% Kesalahan</th></tr></thead>
                <tbody>' . $rowsTopik . '</tbody>
            </table>
        </body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

            return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="analisis_hasil_ujian_' . date('Ymd_His') . '.pdf"')
            ->setBody($dompdf->output());
    }
    
    /**
     * Clear analysis cache for specific jadwal
     * Call this when exam data changes
     */
    public function clearAnalysisCache()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }
        
        $idJadwal = $this->request->getPost('id_jadwal');
        $idKelas = $this->request->getPost('id_kelas');
        
        if ($idJadwal) {
            $kelasStr = $idKelas ?? 'all';
            $cacheKey = "cbt_analysis_{$idJadwal}_{$kelasStr}";
            $this->cache->delete($cacheKey);
            
            // Also clear variants
            $this->cache->delete("cbt_analysis_{$idJadwal}_all");
            $this->cache->delete("cbt_analysis_{$idJadwal}_");
        }
        
        return $this->response->setJSON(['status' => true, 'message' => 'Cache cleared']);
    }

    /**
     * Analisis Butir Soal (Item Analysis) - Psychometric
     * Menghitung Tingkat Kesukaran (p-value), Daya Beda (d-value), dan Reliabilitas KR-20
     */
    public function getItemAnalysis()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);
        }

        $idJadwal = (int) ($this->request->getGet('id_jadwal') ?? 0);
        if ($idJadwal <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'id_jadwal wajib diisi']);
        }

        $db = \Config\Database::connect();

        // Ambil semua soal pada jadwal ini melalui bank soal
        $jadwal = $db->table('cbt_jadwal j')
            ->select('j.id_bank, j.id_jadwal')
            ->where('j.id_jadwal', $idJadwal)
            ->get()->getRow();

        if (!$jadwal) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan']);
        }

        // Ambil seluruh soal di bank ini
        $soalList = $db->table('cbt_soal')
            ->select('id_soal, butir_soal, tujuan_id, bobot_irt')
            ->where('bank_id', $jadwal->id_bank)
            ->where('tampilkan', 1)
            ->orderBy('id_soal')
            ->get()->getResultArray();

        if (empty($soalList)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada soal pada jadwal ini']);
        }

        $soalIds = array_column($soalList, 'id_soal');

        // Ambil semua jawaban siswa pada jadwal ini
        $jawabanRows = $db->table('cbt_jawaban jw')
            ->select('jw.id_siswa, jw.id_soal, jw.skor')
            ->whereIn('jw.id_soal', $soalIds)
            ->where('jw.id_jadwal', $idJadwal)
            ->get()->getResultArray();

        if (empty($jawabanRows)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Belum ada data jawaban siswa']);
        }

        // Susun matrix [siswa][soal] => skor (0/1)
        $matrix = [];
        $totalPerSiswa = [];
        foreach ($jawabanRows as $row) {
            $sid = $row['id_siswa'];
            $qid = $row['id_soal'];
            $skor = (float) $row['skor'];
            $matrix[$sid][$qid] = $skor > 0 ? 1 : 0;
            $totalPerSiswa[$sid] = ($totalPerSiswa[$sid] ?? 0) + ($skor > 0 ? 1 : 0);
        }

        $N = count($matrix); // jumlah peserta
        $k = count($soalIds); // jumlah soal

        if ($N < 2) {
            return $this->response->setJSON(['status' => false, 'message' => 'Minimal 2 peserta diperlukan untuk analisis butir']);
        }

        // Urutkan siswa berdasarkan total skor (untuk d-value upper/lower 27%)
        arsort($totalPerSiswa);
        $siswaOrdered = array_keys($totalPerSiswa);
        $n27 = max(1, (int) round($N * 0.27));
        $upperGroup = array_slice($siswaOrdered, 0, $n27);
        $lowerGroup = array_slice($siswaOrdered, -$n27);

        // Hitung KR-20 (Kuder-Richardson Reliability)
        $varTotal = 0;
        $totalScores = array_values($totalPerSiswa);
        $meanTotal = array_sum($totalScores) / $N;
        foreach ($totalScores as $ts) {
            $varTotal += pow($ts - $meanTotal, 2);
        }
        $varTotal /= $N;

        $sumPQ = 0;
        $items = [];

        foreach ($soalList as $soal) {
            $qid = $soal['id_soal'];

            // p-value: proporsi siswa menjawab benar
            $benar = 0;
            foreach ($matrix as $sid => $answers) {
                $benar += ($answers[$qid] ?? 0);
            }
            $p = $benar / $N;
            $q = 1 - $p;
            $sumPQ += $p * $q;

            // d-value: daya beda (upper 27% - lower 27%) / n27
            $upperBenar = 0;
            $lowerBenar = 0;
            foreach ($upperGroup as $sid) {
                $upperBenar += ($matrix[$sid][$qid] ?? 0);
            }
            foreach ($lowerGroup as $sid) {
                $lowerBenar += ($matrix[$sid][$qid] ?? 0);
            }
            $d = ($upperBenar - $lowerBenar) / $n27;

            // Kategorikan tingkat kesukaran
            $pLabel = $p < 0.3 ? 'Sukar' : ($p <= 0.7 ? 'Sedang' : 'Mudah');
            $pBadge = $p < 0.3 ? 'danger' : ($p <= 0.7 ? 'warning' : 'success');

            // Kategorikan daya beda
            $dLabel = $d < 0.2 ? 'Jelek' : ($d < 0.4 ? 'Cukup' : ($d < 0.7 ? 'Baik' : 'Sangat Baik'));
            $dBadge = $d < 0.2 ? 'danger' : ($d < 0.4 ? 'warning' : 'success');

            $items[] = [
                'id_soal'   => $qid,
                'no'        => count($items) + 1,
                'p_value'   => round($p, 3),
                'p_label'   => $pLabel,
                'p_badge'   => $pBadge,
                'd_value'   => round($d, 3),
                'd_label'   => $dLabel,
                'd_badge'   => $dBadge,
                'benar'     => $benar,
                'peserta'   => $N,
                'bobot_irt' => (float) ($soal['bobot_irt'] ?? 1.0),
            ];
        }

        // KR-20 = (k/(k-1)) * (1 - sumPQ/varTotal)
        $kr20 = $k > 1 && $varTotal > 0 ? round(($k / ($k - 1)) * (1 - ($sumPQ / $varTotal)), 3) : null;
        $kr20Label = $kr20 === null ? 'N/A' : (
            $kr20 >= 0.9 ? 'Sangat Tinggi' : ($kr20 >= 0.7 ? 'Tinggi' : ($kr20 >= 0.5 ? 'Cukup' : 'Rendah'))
        );
        $kr20Badge = $kr20 === null ? 'secondary' : (
            $kr20 >= 0.9 ? 'success' : ($kr20 >= 0.7 ? 'info' : ($kr20 >= 0.5 ? 'warning' : 'danger'))
        );

        return $this->response->setJSON([
            'status' => true,
            'data' => [
                'items'      => $items,
                'n_peserta'  => $N,
                'n_soal'     => $k,
                'kr20'       => $kr20,
                'kr20_label' => $kr20Label,
                'kr20_badge' => $kr20Badge,
            ],
        ]);
    }
}
