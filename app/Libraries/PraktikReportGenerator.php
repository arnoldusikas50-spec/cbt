<?php

namespace App\Libraries;

use App\Models\CbtPraktikModel;
use App\Models\CbtPraktikHasilModel;
use App\Models\CbtPraktikRubrikModel;
use App\Models\CbtPraktikAnalisisModel;
use App\Models\CbtPraktikMultiAssessorModel;
use App\Models\MasterModel;
use App\Traits\KktpTrait;

/**
 * Library untuk generate laporan hasil ujian praktik
 * Berbasis KKTP Kurikulum Merdeka dengan deskripsi naratif
 * Mendukung format PDF, Excel/CSV, dan HTML
 */
class PraktikReportGenerator
{
    use KktpTrait;
    protected $praktikModel;
    protected $hasilModel;
    protected $rubrikModel;
    protected $analisisModel;
    protected $assessorModel;
    protected $masterModel;

    public function __construct()
    {
        $this->praktikModel = new CbtPraktikModel();
        $this->hasilModel = new CbtPraktikHasilModel();
        $this->rubrikModel = new CbtPraktikRubrikModel();
        $this->analisisModel = new CbtPraktikAnalisisModel();
        $this->assessorModel = new CbtPraktikMultiAssessorModel();
        $this->masterModel = new MasterModel();
    }

    /**
     * Generate laporan lengkap dalam format array
     */
    public function generateLaporanLengkap(int $praktikId, ?string $format = 'array'): array
    {
        $praktik = $this->praktikModel->getDetailPraktik($praktikId);
        $hasil = $this->hasilModel->getHasilByPraktik($praktikId, true);
        $rubriks = $this->rubrikModel->getRubrikPraktik($praktikId, false);
        $analisis = $this->analisisModel->getStatistikAnalisis($praktikId);
        $rekomendasiUmum = $this->analisisModel->getRekomendasiUmum($praktikId);

        // Statistik nilai
        $nilaiList = array_filter(array_map(fn($h) => $h->nilai_akhir, $hasil));
        $statistikNilai = $this->hitungStatistikNilai($nilaiList);

        // Tentukan KKTMODE
        $kktpMode = $praktik->kktp_mode ?? 'kkm';
        $kkm = $praktik->kkm ?? 70;

        // Distribusi nilai (KKTP-aware)
        $distribusi = $this->hitungDistribusiNilai($nilaiList, $kkm);

        // Data per siswa
        $dataSiswa = [];
        foreach ($hasil as $h) {
            $analisisSiswa = $this->analisisModel->getAnalisisByHasil($h->id);
            $kktpInfo = $this->getKktp($h->nilai_akhir);
            $deskripsiNaratif = $this->generateDeskripsiSiswa($h, $analisisSiswa);

            $dataSiswa[] = [
                'siswa_id' => $h->siswa_id,
                'nis' => $h->nis,
                'nama' => $h->nama_siswa,
                'kelas' => $h->nama_kelas,
                'nilai' => $h->nilai_akhir,
                'nilai_otomatis' => $h->nilai_otomatis,
                'nilai_manual' => $h->nilai_manual,
                'status' => $h->status_pengerjaan,
                'status_penilaian' => $h->status_penilaian,
                'lulus' => $this->isTuntas($h->nilai_akhir, $kkm),
                'kktp' => $kktpInfo,
                'predikat' => $kktpInfo['predikat'],
                'singkatan' => $kktpInfo['singkatan'],
                'deskripsi' => $deskripsiNaratif,
                'rincian_nilai' => $h->rincian_nilai ?? [],
                'analisis' => $analisisSiswa,
                'feedback' => $h->feedback_guru,
            ];
        }

        // Sort by nilai (tertinggi dulu)
        usort($dataSiswa, fn($a, $b) => $b['nilai'] <=> $a['nilai']);

        return [
            'info_praktik' => [
                'id' => $praktik->id,
                'judul' => $praktik->judul,
                'mapel' => $praktik->nama_mapel,
                'guru' => $praktik->nama_guru,
                'nip_guru' => $praktik->nip_guru,
                'jenis' => $praktik->jenis_praktik,
                'kkm' => $kkm,
                'kktp_mode' => $kktpMode,
                'kktp_interval' => $this->getKktpThresholds(),
                'tanggal' => $praktik->tanggal_mulai,
            ],
            'statistik' => [
                'nilai' => $statistikNilai,
                'distribusi' => $distribusi,
                'analisis' => $analisis,
            ],
            'rubriks' => $rubriks,
            'data_siswa' => $dataSiswa,
            'rekomendasi_umum' => $rekomendasiUmum,
        ];
    }

    /**
     * Hitung statistik nilai
     */
    protected function hitungStatistikNilai(array $nilaiList): array
    {
        if (empty($nilaiList)) {
            return [
                'rata_rata' => 0,
                'median' => 0,
                'modus' => 0,
                'tertinggi' => 0,
                'terendah' => 0,
                'standar_deviasi' => 0,
                'varians' => 0,
            ];
        }

        sort($nilaiList);
        $count = count($nilaiList);
        $sum = array_sum($nilaiList);

        // Median
        $median = ($count % 2)
            ? $nilaiList[floor($count / 2)]
            : ($nilaiList[$count / 2 - 1] + $nilaiList[$count / 2]) / 2;

        // Modus
        $frequency = array_count_values(array_map(fn($n) => round($n), $nilaiList));
        arsort($frequency);
        $modus = key($frequency);

        // Standar deviasi
        $mean = $sum / $count;
        $variance = array_sum(array_map(fn($n) => pow($n - $mean, 2), $nilaiList)) / $count;
        $stdDev = sqrt($variance);

        return [
            'rata_rata' => round($mean, 2),
            'median' => round($median, 2),
            'modus' => $modus,
            'tertinggi' => max($nilaiList),
            'terendah' => min($nilaiList),
            'standar_deviasi' => round($stdDev, 2),
            'varians' => round($variance, 2),
        ];
    }

    /**
     * Hitung distribusi nilai
     */
    protected function hitungDistribusiNilai(array $nilaiList, float $kkm): array
    {
        if (empty($nilaiList)) {
            return [
                'lulus' => 0,
                'tidak_lulus' => 0,
                'persen_lulus' => 0,
                'predikat_mahir' => 0,
                'predikat_cakap' => 0,
                'predikat_layak' => 0,
                'predikat_bb' => 0,
                'label_predikat' => [],
            ];
        }

        $lulus = 0;
        $predikatMahir = 0;
        $predikatCakap = 0;
        $predikatLayak = 0;
        $predikatBB = 0;
        $labelPredikat = [];

        foreach ($nilaiList as $nilai) {
            $kktp = $this->getKktp($nilai);

            if ($this->isTuntas($nilai, $kkm)) {
                $lulus++;
            }

            switch ($kktp['predikat']) {
                case 'Mahir': $predikatMahir++; break;
                case 'Cakap': $predikatCakap++; break;
                case 'Layak': $predikatLayak++; break;
                default: $predikatBB++; break;
            }

            $labelPredikat[] = $kktp['singkatan'];
        }

        $total = count($nilaiList);

        return [
            'lulus' => $lulus,
            'tidak_lulus' => $total - $lulus,
            'persen_lulus' => round(($lulus / $total) * 100, 2),
            'predikat_mahir' => $predikatMahir,
            'predikat_cakap' => $predikatCakap,
            'predikat_layak' => $predikatLayak,
            'predikat_bb' => $predikatBB,
            'label_predikat' => $labelPredikat,
        ];
    }

    /**
     * Generate deskripsi naratif per siswa berdasarkan KKTP dan analisis
     */
    protected function generateDeskripsiSiswa(object $hasil, $analisis): string
    {
        $template = $this->generateDeskripsiRapor(
            json_decode($hasil->rincian_nilai_rubrik ?? '{}', true) ?: [],
            $this->rubrikModel->getRubrikPraktik($hasil->praktik_id, false),
            $hasil->nilai_akhir
        );

        // Tambahkan deskripsi dari analisis jika ada
        if ($analisis && is_object($analisis) && property_exists($analisis, 'deskripsi_kualitatif') && $analisis->deskripsi_kualitatif) {
            $template .= "\n\nCatatan Guru: {$analisis->deskripsi_kualitatif}";
        }

        // Tambah rekomendasi
        if ($analisis && is_object($analisis) && property_exists($analisis, 'rekomendasi') && $analisis->rekomendasi) {
            $template .= "\n\nRekomendasi: {$analisis->rekomendasi}";
        }

        return $template;
    }

    /**
     * Get KKTP threshold labels for display
     */
    public function getKktpThresholds(): array
    {
        return [
            ['predikat' => 'Mahir', 'label' => 'M', 'min' => 86, 'max' => 100, 'deskripsi' => 'Siswa mampu menguasai seluruh kompetensi secara mandiri dan konsisten'],
            ['predikat' => 'Cakap', 'label' => 'C', 'min' => 66, 'max' => 85, 'deskripsi' => 'Siswa mampu menguasai sebagian besar kompetensi dengan baik'],
            ['predikat' => 'Layak', 'label' => 'L', 'min' => 41, 'max' => 65, 'deskripsi' => 'Siswa mampu menguasai kompetensi minimal yang dipersyaratkan'],
            ['predikat' => 'Baru Berkembang', 'label' => 'BB', 'min' => 0, 'max' => 40, 'deskripsi' => 'Siswa perlu bimbingan intensif untuk mencapai kompetensi yang diharapkan'],
        ];
    }

    /**
     * Generate narasi rapor Kurikulum Merdeka lengkap
     */
    public function generateNarasiRapor(int $praktikId, int $siswaId): array
    {
        $praktik = $this->praktikModel->getDetailPraktik($praktikId);
        if (!$praktik) {
            return [];
        }

        $hasil = $this->hasilModel->getHasilSiswa($praktikId, $siswaId);
        if (!$hasil) {
            return [];
        }

        // Ambil detail lengkap (termasuk data siswa: nama_siswa, nis, nama_kelas)
        $hasil = $this->hasilModel->getHasilDetail($hasil->id);
        if (!$hasil) {
            return [];
        }

        $rubriks = $this->rubrikModel->getRubrikPraktik($praktikId, false);
        $analisis = $this->analisisModel->getAnalisisByHasil($hasil->id);

        $kktp = $this->getKktp($hasil->nilai_akhir);

        // Deskripsi per rubrik
        $deskripsiRubrik = [];
        if ($hasil->rincian_nilai_rubrik) {
            $rincian = json_decode($hasil->rincian_nilai_rubrik, true) ?: [];
            foreach ($rubriks as $rubrik) {
                $nilaiRubrik = $rincian[$rubrik->id]['nilai'] ?? 0;
                $kktpRubrik = $this->getKktp($nilaiRubrik);
                $deskripsiRubrik[] = [
                    'kriteria' => $rubrik->nama_kriteria,
                    'nilai' => $nilaiRubrik,
                    'predikat' => $kktpRubrik['predikat'],
                    'singkatan' => $kktpRubrik['singkatan'],
                    'dimensi' => $rubrik->dimensi_penilaian ?? 'umum',
                    'dimensi_ppp' => $rubrik->dimensi_ppp ?? null,
                ];
            }
        }

        // Dimensi PPP teridentifikasi
        $dimensiPpp = array_unique(array_filter(array_map(fn($r) => $r['dimensi_ppp'] ?? null, $deskripsiRubrik)));

        return [
            'identitas_siswa' => [
                'nama' => $hasil->nama_siswa,
                'nis' => $hasil->nis,
                'kelas' => $hasil->nama_kelas,
            ],
            'informasi_praktik' => [
                'judul' => $praktik->judul,
                'mapel' => $praktik->nama_mapel,
                'guru' => $praktik->nama_guru,
                'tanggal' => $praktik->tanggal_mulai,
            ],
            'capaian_pembelajaran' => $praktik->capaian_pembelajaran ?? null,
            'alur_tujuan_pembelajaran' => $praktik->alur_tujuan_pembelajaran ?? null,
            'kktp' => $kktp,
            'nilai_akhir' => $hasil->nilai_akhir,
            'deskripsi_naratif' => $this->generateDeskripsiSiswa($hasil, $analisis),
            'rincian_per_kriteria' => $deskripsiRubrik,
            'dimensi_ppp_teridentifikasi' => array_values($dimensiPpp),
            'rekomendasi' => $analisis->rekomendasi ?? null,
        ];
    }

    /**
     * Export ke CSV
     */
    public function exportCSV(int $praktikId): string
    {
        $laporan = $this->generateLaporanLengkap($praktikId);

        $filename = 'Laporan_Praktik_' . preg_replace('/[^a-zA-Z0-9]/', '_', $laporan['info_praktik']['judul']) . '_' . date('Ymd_His') . '.csv';
        $filepath = WRITEPATH . 'exports/' . $filename;

        // Pastikan direktori ada
        if (!is_dir(WRITEPATH . 'exports')) {
            mkdir(WRITEPATH . 'exports', 0777, true);
        }

        $output = fopen($filepath, 'w');

        // BOM untuk UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header info
        fputcsv($output, ['LAPORAN HASIL UJIAN PRAKTIK']);
        fputcsv($output, ['Judul', $laporan['info_praktik']['judul']]);
        fputcsv($output, ['Mata Pelajaran', $laporan['info_praktik']['mapel']]);
        fputcsv($output, ['Guru', $laporan['info_praktik']['guru']]);
        fputcsv($output, ['Jenis', $laporan['info_praktik']['jenis']]);
        fputcsv($output, ['Tanggal', $laporan['info_praktik']['tanggal_mulai'] . ' s/d ' . $laporan['info_praktik']['tanggal_selesai']]);
        fputcsv($output, ['KKM', $laporan['info_praktik']['kkm']]);
        fputcsv($output, []);

        // Statistik
        fputcsv($output, ['STATISTIK NILAI']);
        fputcsv($output, ['Rata-rata', $laporan['statistik']['nilai']['rata_rata']]);
        fputcsv($output, ['Median', $laporan['statistik']['nilai']['median']]);
        fputcsv($output, ['Modus', $laporan['statistik']['nilai']['modus']]);
        fputcsv($output, ['Nilai Tertinggi', $laporan['statistik']['nilai']['tertinggi']]);
        fputcsv($output, ['Nilai Terendah', $laporan['statistik']['nilai']['terendah']]);
        fputcsv($output, ['Standar Deviasi', $laporan['statistik']['nilai']['standar_deviasi']]);
        fputcsv($output, []);

        // Distribusi
        fputcsv($output, ['DISTRIBUSI NILAI']);
        fputcsv($output, ['Lulus', $laporan['statistik']['distribusi']['lulus']]);
        fputcsv($output, ['Tidak Lulus', $laporan['statistik']['distribusi']['tidak_lulus']]);
        fputcsv($output, ['% Lulus', $laporan['statistik']['distribusi']['persen_lulus']]);
        fputcsv($output, []);

        // Data siswa
        fputcsv($output, ['DATA SISWA']);
        fputcsv($output, ['No', 'NIS', 'Nama', 'Kelas', 'Nilai Akhir', 'Status', 'Lulus/Tidak', 'Catatan']);

        $no = 1;
        foreach ($laporan['data_siswa'] as $s) {
            fputcsv($output, [
                $no++,
                $s['nis'],
                $s['nama'],
                $s['kelas'],
                $s['nilai'],
                $s['status_penilaian'],
                $s['lulus'] ? 'Lulus' : 'Remedial',
                $s['feedback'] ?? '-'
            ]);
        }

        fclose($output);

        return $filepath;
    }

    /**
     * Generate HTML untuk PDF
     */
    public function generateHTMLForPDF(int $praktikId): string
    {
        $laporan = $this->generateLaporanLengkap($praktikId);
        $setting = $this->masterModel->getSetting();

        $html = view('cbt/praktik/reports/pdf_template', [
            'laporan' => $laporan,
            'setting' => $setting,
            'tanggal_cetak' => date('d F Y H:i:s'),
        ]);

        return $html;
    }

    /**
     * Generate PDF menggunakan Dompdf
     */
    public function exportPDF(int $praktikId): string
    {
        $html = $this->generateHTMLForPDF($praktikId);

        $filename = 'Laporan_Praktik_' . $praktikId . '_' . date('Ymd_His') . '.pdf';
        $filepath = WRITEPATH . 'exports/' . $filename;

        if (!is_dir(WRITEPATH . 'exports')) {
            mkdir(WRITEPATH . 'exports', 0777, true);
        }

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        file_put_contents($filepath, $dompdf->output());

        return $filepath;
    }

    /**
     * Generate laporan individual per siswa
     */
    public function generateLaporanIndividual(int $hasilId): ?array
    {
        $hasil = $this->hasilModel->getHasilDetail($hasilId);
        if (!$hasil) {
            return null;
        }

        $praktik = $this->praktikModel->getDetailPraktik($hasil->praktik_id);
        $rubriks = $this->rubrikModel->getRubrikPraktik($hasil->praktik_id, false);
        $analisis = $this->analisisModel->getAnalisisByHasil($hasilId);

        // Parse rincian nilai
        $rincianNilai = [];
        if ($hasil->rincian_nilai_rubrik) {
            $decoded = json_decode($hasil->rincian_nilai_rubrik, true);
            foreach ($decoded as $rubrikId => $nilai) {
                $rincianNilai[$rubrikId] = is_array($nilai) ? $nilai : ['nilai' => $nilai, 'catatan' => ''];
            }
        }

        $kktp = $this->getKktp($hasil->nilai_akhir);
        $deskripsiNaratif = $this->generateDeskripsiSiswa($hasil, $analisis);

        return [
            'siswa' => [
                'id' => $hasil->siswa_id,
                'nis' => $hasil->nis,
                'nama' => $hasil->nama_siswa,
                'kelas' => $hasil->nama_kelas,
                'email' => $hasil->email ?? null,
            ],
            'praktik' => [
                'id' => $praktik->id,
                'judul' => $praktik->judul,
                'mapel' => $praktik->nama_mapel,
                'guru' => $praktik->nama_guru,
                'nip_guru' => $praktik->nip_guru,
                'jenis' => $praktik->jenis_praktik,
                'instruksi' => $praktik->instruksi,
            ],
            'hasil' => [
                'nilai_akhir' => $hasil->nilai_akhir,
                'nilai_otomatis' => $hasil->nilai_otomatis,
                'nilai_manual' => $hasil->nilai_manual,
                'kkm' => $praktik->kkm ?? 70,
                'kktp' => $kktp,
                'predikat' => $kktp['predikat'],
                'deskripsi' => $deskripsiNaratif,
                'lulus' => $this->isTuntas($hasil->nilai_akhir, $this->getKkm($praktik)),
                'durasi_pengerjaan' => $hasil->durasi_pengerjaan_detik,
                'waktu_submit' => $hasil->waktu_submit,
                'feedback_guru' => $hasil->feedback_guru,
                'catatan_siswa' => $hasil->catatan_siswa,
            ],
            'rubriks' => $rubriks,
            'rincian_nilai' => $rincianNilai,
            'analisis' => $analisis,
        ];
    }

    /**
     * Export laporan individual ke PDF
     */
    public function exportLaporanIndividualPDF(int $hasilId): ?string
    {
        $laporan = $this->generateLaporanIndividual($hasilId);
        if (!$laporan) {
            return null;
        }

        $filename = 'Laporan_Individual_' . preg_replace('/[^a-zA-Z0-9]/', '_', $laporan['siswa']['nama']) . '_' . date('Ymd') . '.pdf';
        $filepath = WRITEPATH . 'exports/' . $filename;

        if (!is_dir(WRITEPATH . 'exports')) {
            mkdir(WRITEPATH . 'exports', 0777, true);
        }

        $html = view('cbt/praktik/reports/pdf_individual', [
            'laporan' => $laporan,
            'tanggal_cetak' => date('d F Y'),
        ]);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($filepath, $dompdf->output());

        return $filepath;
    }

    /**
     * Get daftar file yang sudah diexport
     */
    public function getExportedFiles(): array
    {
        $exportPath = WRITEPATH . 'exports/';
        if (!is_dir($exportPath)) {
            return [];
        }

        $files = [];
        $iterator = new \DirectoryIterator($exportPath);

        foreach ($iterator as $file) {
            if ($file->isFile() && strpos($file->getFilename(), 'Laporan_Praktik_') === 0) {
                $files[] = [
                    'filename' => $file->getFilename(),
                    'filepath' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'created' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort by created desc
        usort($files, fn($a, $b) => strtotime($b['created']) <=> strtotime($a['created']));

        return $files;
    }
}
