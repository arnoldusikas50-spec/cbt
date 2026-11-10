<?php

namespace App\Models;

use App\Traits\SiswaAktifTrait;
use CodeIgniter\Model;

/**
 * CbtAnalysisModel - Model untuk analisis hasil ujian berbasis Kurikulum Merdeka
 * Mengelola analisis ketercapaian Tujuan Pembelajaran (TP), statistik deskriptif,
 * analisis butir soal (kesukaran, daya beda, validitas, reliabilitas), dan prediksi capaian.
 */
class CbtAnalysisModel extends Model
{
    use SiswaAktifTrait;

    protected $table = 'cbt_jawaban';
    protected $primaryKey = 'id_jawaban';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Ambil data hasil ujian dengan mapping ke Tujuan Pembelajaran (TP)
     * Menggunakan fallback joinkartu soal item jika tujuan_id pada cbt_soal belum diisi
     */
    public function getHasilUjianWithTP(int $idJadwal, ?int $idKelas = null): array
    {
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            js.id_siswa,
            s.nama as nama_siswa,
            s.nis,
            js.id_soal,
            cs.nomor_soal,
            cs.soal,
            cs.jenis,
            COALESCE(cs.tujuan_id, ksi.id_tujuan) as tujuan_id,
            etp.kode as tujuan_kode,
            etp.tujuan as tujuan_teks,
            etp.materi_pokok,
            js.jawaban,
            cs.jawaban as jawaban_benar,
            js.nilai,
            (js.nilai > 0) as iscorrect,
            (js.nilai > 0) as is_correct,
            mp.nama_mapel,
            cs.kesulitan,
            COALESCE(cn.nilai_katrol, cn.nilai_asli, js.nilai) as nilai_siswa
        ');
        $builder->join('master_siswa s', 's.id_siswa = js.id_siswa');
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
        $this->onlySiswaAktif($builder, 'js');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->join('cbt_kartu_soal_item ksi', 'ksi.soal_id = cs.id_soal', 'left');
        $builder->join('cbt_bank_soal cb', 'cb.id_bank = cs.bank_id', 'left');
        $builder->join('e_tujuan_pembelajaran etp', 'etp.id_tujuan = COALESCE(cs.tujuan_id, ksi.id_tujuan)', 'left');
        $builder->join('master_mapel mp', 'mp.id_mapel = cb.bank_mapel_id', 'left');
        $builder->join('cbt_nilai cn', 'cn.id_jadwal = js.id_jadwal AND cn.id_siswa = js.id_siswa', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        
        $this->applyKelasFilter($builder, $idKelas);
        
        $builder->orderBy('s.nama', 'ASC');
        $builder->orderBy('cs.nomor_soal', 'ASC');
        
        $query = $builder->get();
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Analisis kesalahan per Tujuan Pembelajaran
     */
    public function analyzeTopikBermasalah(int $idJadwal, ?int $idKelas = null, int $limit = 5): array
    {
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            COALESCE(cs.tujuan_id, ksi.id_tujuan) as tujuan_id,
            etp.kode as tujuan_kode,
            etp.tujuan as tujuan_teks,
            etp.materi_pokok,
            COUNT(*) as total_jawaban,
            SUM(CASE WHEN js.nilai > 0 THEN 0 ELSE 1 END) as jumlah_salah,
            ROUND((SUM(CASE WHEN js.nilai > 0 THEN 0 ELSE 1 END) / COUNT(*)) * 100, 2) as persentase_kesalahan,
            COUNT(DISTINCT js.id_siswa) as jumlah_siswa,
            AVG(js.nilai) as rata_rata_nilai
        ');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->join('cbt_kartu_soal_item ksi', 'ksi.soal_id = cs.id_soal', 'left');
        $builder->join('e_tujuan_pembelajaran etp', 'etp.id_tujuan = COALESCE(cs.tujuan_id, ksi.id_tujuan)', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        
        $this->applyKelasFilter($builder, $idKelas);
        
        $builder->where('COALESCE(cs.tujuan_id, ksi.id_tujuan) IS NOT NULL');
        $builder->groupBy('COALESCE(cs.tujuan_id, ksi.id_tujuan), etp.kode, etp.tujuan, etp.materi_pokok');
        $builder->having('total_jawaban >=', 1);
        $builder->orderBy('persentase_kesalahan', 'DESC');
        $builder->limit($limit);
        
        $query = $builder->get();
        return $query ? $query->getResultArray() : [];
    }
    
    /**
     * Statistik nilai per mata pelajaran
     */
    public function getStatistikNilaiPerMapel(int $idJadwal, ?int $idKelas = null): array
    {
        // 1. Ambil data nilai per siswa
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            mp.id_mapel,
            mp.nama_mapel,
            COALESCE(NULLIF(cb.kkm, 0), 75) as kkm,
            js.id_siswa,
            COALESCE(cn.nilai_katrol, cn.nilai_asli, AVG(js.nilai)) as nilai_siswa
        ');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->join('cbt_bank_soal cb', 'cb.id_bank = cs.bank_id');
        $builder->join('master_mapel mp', 'mp.id_mapel = cb.bank_mapel_id');
        $builder->join('cbt_nilai cn', 'cn.id_jadwal = js.id_jadwal AND cn.id_siswa = js.id_siswa', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        
        $this->applyKelasFilter($builder, $idKelas);
        
        $builder->groupBy('mp.id_mapel, mp.nama_mapel, cb.kkm, js.id_siswa, cn.nilai_katrol, cn.nilai_asli');
        
        $query = $builder->get();
        $rows = $query ? $query->getResultArray() : [];
        
        if (empty($rows)) {
            return [];
        }

        // Agregasikan per mapel
        $mapelStats = [];
        foreach ($rows as $row) {
            $mapelId = $row['id_mapel'];
            if (!isset($mapelStats[$mapelId])) {
                $mapelStats[$mapelId] = [
                    'id_mapel' => $mapelId,
                    'nama_mapel' => $row['nama_mapel'],
                    'kkm' => (float) $row['kkm'],
                    'scores' => []
                ];
            }
            $mapelStats[$mapelId]['scores'][] = (float) $row['nilai_siswa'];
        }

        $result = [];
        foreach ($mapelStats as $ms) {
            $scores = $ms['scores'];
            $n = count($scores);
            $kkm = $ms['kkm'];
            $mean = array_sum($scores) / $n;
            $min = min($scores);
            $max = max($scores);
            $stdDev = $this->variance($scores) > 0 ? sqrt($this->variance($scores)) : 0;
            
            $tuntasCount = count(array_filter($scores, fn($v) => $v >= $kkm));
            
            $result[] = [
                'id_mapel' => $ms['id_mapel'],
                'nama_mapel' => $ms['nama_mapel'],
                'kkm' => $kkm,
                'jumlah_siswa' => $n,
                'rata_rata' => round($mean, 2),
                'nilai_min' => round($min, 2),
                'nilai_max' => round($max, 2),
                'simpangan_baku' => round($stdDev, 2),
                'jumlah_tuntas' => $tuntasCount,
                'persentase_tuntas' => round(($tuntasCount / $n) * 100, 2)
            ];
        }

        return $result;
    }
    
    /**
     * Kategorikan siswa berdasarkan pencapaian standar Rapor Pendidikan Kemdikbud
     */
    public function kategorikanSiswa(int $idJadwal, ?int $idKelas = null): array
    {
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            js.id_siswa,
            s.nama as nama_siswa,
            s.nis,
            COALESCE(cn.nilai_katrol, cn.nilai_asli, AVG(js.nilai)) as nilai_akhir,
            COALESCE(NULLIF(cb.kkm, 0), 75) as kkm
        ');
        $builder->join('master_siswa s', 's.id_siswa = js.id_siswa');
        // Hanya siswa berstatus AKTIF (buku_induk.status = 1).
        $this->onlySiswaAktif($builder, 'js');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->join('cbt_bank_soal cb', 'cb.id_bank = cs.bank_id', 'left');
        $builder->join('cbt_nilai cn', 'cn.id_jadwal = js.id_jadwal AND cn.id_siswa = js.id_siswa', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        
        $this->applyKelasFilter($builder, $idKelas);
        
        $builder->groupBy('js.id_siswa, s.nama, s.nis, cb.kkm, cn.nilai_katrol, cn.nilai_asli');
        
        $query = $builder->get();
        $siswaData = $query ? $query->getResultArray() : [];
        
        $kategori = [
            'mahir' => [],
            'cakap' => [],
            'dasar' => [],
            'intervensi' => []
        ];
        
        foreach ($siswaData as $siswa) {
            $nilai = (float) $siswa['nilai_akhir'];
            
            if ($nilai >= 85) {
                $siswa['kategori'] = 'Mahir';
                $siswa['kategori_class'] = 'success';
                $kategori['mahir'][] = $siswa;
            } elseif ($nilai >= 70) {
                $siswa['kategori'] = 'Cakap';
                $siswa['kategori_class'] = 'primary';
                $kategori['cakap'][] = $siswa;
            } elseif ($nilai >= 50) {
                $siswa['kategori'] = 'Dasar';
                $siswa['kategori_class'] = 'warning';
                $kategori['dasar'][] = $siswa;
            } else {
                $siswa['kategori'] = 'Perlu Intervensi Khusus';
                $siswa['kategori_class'] = 'danger';
                $kategori['intervensi'][] = $siswa;
            }
        }
        
        return $kategori;
    }
    
    /**
     * Statistik deskriptif lengkap berbasis skor siswa
     */
    public function getStatistikDeskriptif(int $idJadwal, ?int $idKelas = null): array
    {
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('js.id_siswa, COALESCE(cn.nilai_katrol, cn.nilai_asli, AVG(js.nilai)) as skor');
        $builder->join('cbt_nilai cn', 'cn.id_jadwal = js.id_jadwal AND cn.id_siswa = js.id_siswa', 'left');
        $builder->where('js.id_jadwal', $idJadwal);

        $this->applyKelasFilter($builder, $idKelas);
        $builder->groupBy('js.id_siswa, cn.nilai_katrol, cn.nilai_asli');

        $query = $builder->get();
        $rows = $query ? $query->getResultArray() : [];
        $scores = array_map(static fn($r) => (float) $r['skor'], $rows);
        $n = count($scores);

        if ($n === 0) {
            return [
                'n' => 0,
                'mean' => 0,
                'median' => 0,
                'modus' => 0,
                'min' => 0,
                'max' => 0,
                'range' => 0,
                'q1' => 0,
                'q2' => 0,
                'q3' => 0,
                'iqr' => 0,
                'std_dev' => 0,
                'varians' => 0,
                'skewness' => 0,
                'kurtosis' => 0,
                'std_error' => 0,
                'normality' => ['level' => 'Tidak diketahui', 'label' => 'Data kosong', 'interpretasi' => 'Belum ada data siswa.']
            ];
        }

        sort($scores);
        $mean = array_sum($scores) / $n;

        $median = $this->quantile($scores, 0.5);
        $q1 = $this->quantile($scores, 0.25);
        $q3 = $this->quantile($scores, 0.75);
        $iqr = $q3 - $q1;

        $modus = $this->modusScore($scores);

        $min = $scores[0];
        $max = $scores[$n - 1];
        $range = $max - $min;

        $sumSq = 0.0;
        foreach ($scores as $v) {
            $sumSq += ($v - $mean) * ($v - $mean);
        }
        $varians = $n > 1 ? $sumSq / ($n - 1) : 0;
        $stdDev = sqrt($varians);
        $stdError = $n > 0 ? $stdDev / sqrt($n) : 0;

        $skewness = 0;
        $kurtosis = 0;
        if ($n > 2 && $stdDev > 0) {
            $m3 = 0.0;
            $m4 = 0.0;
            foreach ($scores as $v) {
                $z = ($v - $mean) / $stdDev;
                $m3 += $z * $z * $z;
                $m4 += $z * $z * $z * $z;
            }
            $skewness = $m3 / $n;
            $kurtosis = ($m4 / $n) - 3;
        }

        $normality = $this->interpretNormality($skewness, $n);

        return [
            'n' => $n,
            'mean' => round($mean, 2),
            'median' => round($median, 2),
            'modus' => round($modus, 2),
            'min' => round($min, 2),
            'max' => round($max, 2),
            'range' => round($range, 2),
            'q1' => round($q1, 2),
            'q2' => round($median, 2),
            'q3' => round($q3, 2),
            'iqr' => round($iqr, 2),
            'std_dev' => round($stdDev, 2),
            'varians' => round($varians, 2),
            'skewness' => round($skewness, 2),
            'kurtosis' => round($kurtosis, 2),
            'std_error' => round($stdError, 2),
            'normality' => $normality
        ];
    }

    private function quantile(array $sorted, float $p): float
    {
        $n = count($sorted);
        if ($n === 0) return 0;
        if ($n === 1) return (float) $sorted[0];

        $pos = ($n - 1) * $p;
        $base = (int) floor($pos);
        $rest = $pos - $base;

        if (isset($sorted[$base + 1])) {
            return (float) ($sorted[$base] + $rest * ($sorted[$base + 1] - $sorted[$base]));
        }
        return (float) $sorted[$base];
    }

    private function modusScore(array $scores): float
    {
        $bins = [];
        foreach ($scores as $v) {
            $bin = (int) floor($v / 10) * 10;
            $bins[$bin] = ($bins[$bin] ?? 0) + 1;
        }
        if (empty($bins)) return 0;
        $maxCount = max($bins);
        $modeBins = array_keys($bins, $maxCount);
        $firstBin = (float) $modeBins[0];
        return $firstBin + 5;
    }

    private function interpretNormality(float $skewness, int $n): array
    {
        $abs = abs($skewness);
        if ($abs < 0.5) {
            $label = 'Normal / Simetris';
            $level = 'good';
            $interpretasi = 'Distribusi skor berbentuk simetris, mendekati normal.';
        } elseif ($abs < 1) {
            $label = $skewness > 0 ? 'Miring Kanan (Positif)' : 'Miring Kiri (Negatif)';
            $level = 'warning';
            $interpretasi = $skewness > 0
                ? 'Distribusi miring ke kanan: banyak siswa berada di skor rendah, sedikit yang sangat tinggi.'
                : 'Distribusi miring ke kiri: banyak siswa di skor tinggi, sedikit yang sangat rendah.';
        } else {
            $label = $skewness > 0 ? 'Sangat Miring Kanan' : 'Sangat Miring Kiri';
            $level = 'danger';
            $interpretasi = 'Distribusi sangat tidak normal (asimetris ekstrem). Pertimbangkan transformasi data.';
        }
        return [
            'level' => $level,
            'label' => $label,
            'interpretasi' => $interpretasi
        ];
    }

    /**
     * Ringkasan kualitas butir soal & rekomendasi perbaikan tes
     */
    public function getRingkasanKualitas(int $idJadwal, ?int $idKelas = null): array
    {
        $butir = $this->analyzeItemStatistics($idJadwal, $idKelas);
        $items = $butir['items'] ?? [];
        $n = count($items);
        if ($n === 0) {
            return ['n' => 0, 'baik' => 0, 'perlu_revisi' => 0, 'persen_baik' => 0, 'reliability' => $butir['reliability'] ?? []];
        }

        $baik = 0;
        $perluRevisi = 0;
        $avgP = 0;
        $avgD = 0;
        foreach ($items as $it) {
            $d = (float) ($it['daya_beda'] ?? 0);
            $v = (float) ($it['validitas_item'] ?? 0);
            $avgP += (float) ($it['tingkat_kesukaran'] ?? 0);
            $avgD += $d;
            if ($d >= 0.20 && $v >= 0.20) {
                $baik++;
            } else {
                $perluRevisi++;
            }
        }

        return [
            'n' => $n,
            'baik' => $baik,
            'perlu_revisi' => $perluRevisi,
            'persen_baik' => round(($baik / $n) * 100, 1),
            'avg_kesukaran' => round($avgP / $n, 3),
            'avg_daya_beda' => round($avgD / $n, 3),
            'reliability' => $butir['reliability'] ?? []
        ];
    }

    /**
     * Histogram distribusi skor (10 bin 0-100)
     */
    public function getHistogramSkor(int $idJadwal, ?int $idKelas = null, int $bins = 10): array
    {
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('js.id_siswa, COALESCE(cn.nilai_katrol, cn.nilai_asli, AVG(js.nilai)) as skor');
        $builder->join('cbt_nilai cn', 'cn.id_jadwal = js.id_jadwal AND cn.id_siswa = js.id_siswa', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        $this->applyKelasFilter($builder, $idKelas);
        $builder->groupBy('js.id_siswa, cn.nilai_katrol, cn.nilai_asli');

        $query = $builder->get();
        $rows = $query ? $query->getResultArray() : [];
        $labels = [];
        $data = [];
        $width = 100 / $bins;
        for ($i = 0; $i < $bins; $i++) {
            $low = round($i * $width);
            $high = round(($i + 1) * $width);
            $labels[] = $low . '-' . $high;
            $data[] = 0;
        }
        foreach ($rows as $r) {
            $v = (float) $r['skor'];
            $idx = min($bins - 1, (int) floor($v / $width));
            if ($idx < 0) $idx = 0;
            $data[$idx]++;
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Analisis korelasi antara penguasaan TP dengan nilai akhir
     */
    public function analisisKorelasiTP(int $idJadwal, ?int $idKelas = null): array
    {
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            js.id_siswa,
            COALESCE(cs.tujuan_id, ksi.id_tujuan) as tujuan_id,
            etp.kode as tujuan_kode,
            AVG(js.nilai) as rata_rata_tp,
            COUNT(*) as jumlah_soal_tp,
            SUM(CASE WHEN js.nilai > 0 THEN 1 ELSE 0 END) as benar_tp,
            ROUND((SUM(CASE WHEN js.nilai > 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as penguasaan_tp
        ');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->join('cbt_kartu_soal_item ksi', 'ksi.soal_id = cs.id_soal', 'left');
        $builder->join('e_tujuan_pembelajaran etp', 'etp.id_tujuan = COALESCE(cs.tujuan_id, ksi.id_tujuan)', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        $builder->where('COALESCE(cs.tujuan_id, ksi.id_tujuan) IS NOT NULL');
        
        $this->applyKelasFilter($builder, $idKelas);
        
        $builder->groupBy('js.id_siswa, COALESCE(cs.tujuan_id, ksi.id_tujuan), etp.kode');
        
        $query = $builder->get();
        $penguasaanTP = $query ? $query->getResultArray() : [];
        
        $builder2 = $this->db->table('cbt_jawaban js');
        $builder2->select('
            js.id_siswa,
            COALESCE(cn.nilai_katrol, cn.nilai_asli, AVG(js.nilai)) as nilai_akhir
        ');
        $builder2->join('cbt_nilai cn', 'cn.id_jadwal = js.id_jadwal AND cn.id_siswa = js.id_siswa', 'left');
        $builder2->where('js.id_jadwal', $idJadwal);
        
        $this->applyKelasFilter($builder2, $idKelas);
        
        $builder2->groupBy('js.id_siswa, cn.nilai_katrol, cn.nilai_asli');
        
        $query2 = $builder2->get();
        $nilaiAkhir = $query2 ? $query2->getResultArray() : [];
        
        $siswaData = [];
        foreach ($nilaiAkhir as $na) {
            $siswaData[$na['id_siswa']] = [
                'nilai_akhir' => $na['nilai_akhir'],
                'penguasaan_tp' => []
            ];
        }
        
        foreach ($penguasaanTP as $ptp) {
            if (isset($siswaData[$ptp['id_siswa']])) {
                $siswaData[$ptp['id_siswa']]['penguasaan_tp'][$ptp['tujuan_id']] = $ptp['penguasaan_tp'];
            }
        }
        
        $korelasi = [];
        $tps = array_unique(array_column($penguasaanTP, 'tujuan_id'));
        
        foreach ($tps as $tpId) {
            $x = [];
            $y = [];
            
            foreach ($siswaData as $data) {
                if (isset($data['penguasaan_tp'][$tpId])) {
                    $x[] = $data['penguasaan_tp'][$tpId];
                    $y[] = $data['nilai_akhir'];
                }
            }
            
            if (count($x) >= 2) {
                $korelasi[$tpId] = $this->hitungKorelasi($x, $y);
            }
        }
        
        return [
            'korelasi_per_tp' => $korelasi,
            'data_siswa' => $siswaData,
            'jumlah_siswa' => count($siswaData)
        ];
    }
    
    private function hitungKorelasi(array $x, array $y): array
    {
        $n = count($x);
        if ($n < 2) return ['r' => 0, 'interpretasi' => 'Tidak cukup data'];
        
        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;
        
        $numerator = 0;
        $denominatorX = 0;
        $denominatorY = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $meanX;
            $dy = $y[$i] - $meanY;
            
            $numerator += $dx * $dy;
            $denominatorX += $dx * $dx;
            $denominatorY += $dy * $dy;
        }
        
        if ($denominatorX == 0 || $denominatorY == 0) {
            return ['r' => 0, 'interpretasi' => 'Tidak ada variasi'];
        }
        
        $r = $numerator / sqrt($denominatorX * $denominatorY);
        
        $absR = abs($r);
        if ($absR >= 0.7) {
            $interpretasi = 'Kuat';
        } elseif ($absR >= 0.4) {
            $interpretasi = 'Sedang';
        } elseif ($absR >= 0.2) {
            $interpretasi = 'Lemah';
        } else {
            $interpretasi = 'Sangat lemah/tidak ada';
        }
        
        return [
            'r' => round($r, 3),
            'interpretasi' => $interpretasi,
            'arah' => $r > 0 ? 'Positif' : 'Negatif'
        ];
    }
    
    /**
     * Prediksi capaian siswa berdasarkan penguasaan TP saat ini
     */
    public function prediksiCapaian(int $idJadwal, int $idSiswa): array
    {
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            COALESCE(cs.tujuan_id, ksi.id_tujuan) as tujuan_id,
            etp.kode as tujuan_kode,
            AVG(js.nilai) as rata_rata_tp,
            COUNT(*) as jumlah_soal_tp,
            ROUND((SUM(CASE WHEN js.nilai > 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as penguasaan_tp
        ');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->join('cbt_kartu_soal_item ksi', 'ksi.soal_id = cs.id_soal', 'left');
        $builder->join('e_tujuan_pembelajaran etp', 'etp.id_tujuan = COALESCE(cs.tujuan_id, ksi.id_tujuan)', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        $builder->where('js.id_siswa', $idSiswa);
        $builder->where('COALESCE(cs.tujuan_id, ksi.id_tujuan) IS NOT NULL');
        $builder->groupBy('COALESCE(cs.tujuan_id, ksi.id_tujuan), etp.kode');
        
        $query = $builder->get();
        $penguasaanTP = $query ? $query->getResultArray() : [];
        
        $totalPenguasaan = 0;
        $jumlahTP = 0;
        
        foreach ($penguasaanTP as $tp) {
            $totalPenguasaan += $tp['penguasaan_tp'];
            $jumlahTP++;
        }
        
        $nilaiPrediksi = $jumlahTP > 0 ? ($totalPenguasaan / $jumlahTP) : 0;
        
        if ($nilaiPrediksi >= 85) {
            $kategori = 'Mahir';
            $rekomendasi = 'Siswa mampu mengaplikasikan pengetahuan pada situasi kompleks. Rekomendasikan program pengayaan.';
        } elseif ($nilaiPrediksi >= 70) {
            $kategori = 'Cakap';
            $rekomendasi = 'Siswa dapat mengaplikasikan pengetahuan pada masalah rutin. Fokuskan pada peningkatan kedalaman konsep.';
        } elseif ($nilaiPrediksi >= 50) {
            $kategori = 'Dasar';
            $rekomendasi = 'Siswa memiliki konsep dasar tapi terbatas. Perlu pendekatan eksploratif yang terarah dan latihan soal bervariasi.';
        } else {
            $kategori = 'Perlu Intervensi Khusus';
            $rekomendasi = 'Siswa belum memahami konsep dasar fundamental. Wajib mendapat pendampingan personal dan remedial intensif (Tutorial Sebaya).';
        }
        
        return [
            'nilai_prediksi' => round($nilaiPrediksi, 2),
            'kategori' => $kategori,
            'rekomendasi' => $rekomendasi,
            'detail_tp' => $penguasaanTP,
            'jumlah_tp_dikuasai' => count(array_filter($penguasaanTP, function($tp) {
                return $tp['penguasaan_tp'] >= 75;
            }))
        ];
    }
    
    /**
     * Prediksi capaian untuk multiple siswa (batch version)
     */
    public function prediksiCapaianBatch(int $idJadwal, array $idSiswaList): array
    {
        if (empty($idSiswaList)) {
            return [];
        }
        
        $idSiswaList = array_slice($idSiswaList, 0, 50);
        
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            js.id_siswa,
            COALESCE(cs.tujuan_id, ksi.id_tujuan) as tujuan_id,
            etp.kode as tujuan_kode,
            AVG(js.nilai) as rata_rata_tp,
            COUNT(*) as jumlah_soal_tp,
            ROUND((SUM(CASE WHEN js.nilai > 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as penguasaan_tp
        ');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->join('cbt_kartu_soal_item ksi', 'ksi.soal_id = cs.id_soal', 'left');
        $builder->join('e_tujuan_pembelajaran etp', 'etp.id_tujuan = COALESCE(cs.tujuan_id, ksi.id_tujuan)', 'left');
        $builder->where('js.id_jadwal', $idJadwal);
        $builder->whereIn('js.id_siswa', $idSiswaList);
        $builder->where('COALESCE(cs.tujuan_id, ksi.id_tujuan) IS NOT NULL');
        $builder->groupBy('js.id_siswa, COALESCE(cs.tujuan_id, ksi.id_tujuan), etp.kode');
        
        $query = $builder->get();
        $results = $query ? $query->getResultArray() : [];
        
        $siswaData = [];
        foreach ($results as $row) {
            $idSiswa = $row['id_siswa'];
            if (!isset($siswaData[$idSiswa])) {
                $siswaData[$idSiswa] = [
                    'detail_tp' => [],
                    'total_penguasaan' => 0,
                    'jumlah_tp' => 0
                ];
            }
            $siswaData[$idSiswa]['detail_tp'][] = $row;
            $siswaData[$idSiswa]['total_penguasaan'] += $row['penguasaan_tp'];
            $siswaData[$idSiswa]['jumlah_tp']++;
        }
        
        $predictions = [];
        foreach ($siswaData as $idSiswa => $data) {
            $jumlahTP = $data['jumlah_tp'];
            $nilaiPrediksi = $jumlahTP > 0 ? ($data['total_penguasaan'] / $jumlahTP) : 0;
            
            if ($nilaiPrediksi >= 85) {
                $kategori = 'Mahir';
                $rekomendasi = 'Siswa mampu mengaplikasikan pengetahuan pada situasi kompleks. Rekomendasikan program pengayaan.';
            } elseif ($nilaiPrediksi >= 70) {
                $kategori = 'Cakap';
                $rekomendasi = 'Siswa dapat mengaplikasikan pengetahuan pada masalah rutin. Fokuskan pada peningkatan kedalaman konsep.';
            } elseif ($nilaiPrediksi >= 50) {
                $kategori = 'Dasar';
                $rekomendasi = 'Siswa memiliki konsep dasar tapi terbatas. Perlu pendekatan eksploratif yang terarah dan latihan soal bervariasi.';
            } else {
                $kategori = 'Perlu Intervensi Khusus';
                $rekomendasi = 'Siswa belum memahami konsep dasar fundamental. Wajib mendapat pendampingan personal dan remedial intensif (Tutorial Sebaya).';
            }
            
            $predictions[$idSiswa] = [
                'nilai_prediksi' => round($nilaiPrediksi, 2),
                'kategori' => $kategori,
                'rekomendasi' => $rekomendasi,
                'detail_tp' => $data['detail_tp'],
                'jumlah_tp_dikuasai' => count(array_filter($data['detail_tp'], fn($tp) => $tp['penguasaan_tp'] >= 75))
            ];
        }
        
        return $predictions;
    }

    /**
     * Generate rencana remedial untuk topik bermasalah
     */
    public function generateRencanaRemedial(array $topikBermasalah): array
    {
        $rencana = [];
        
        foreach ($topikBermasalah as $topik) {
            if ($topik['persentase_kesalahan'] > 30) {
                $rencana[] = [
                    'tujuan_id' => $topik['tujuan_id'],
                    'tujuan_kode' => $topik['tujuan_kode'],
                    'tujuan_teks' => $topik['tujuan_teks'],
                    'materi_pokok' => $topik['materi_pokok'] ?? '-',
                    'persentase_kesalahan' => $topik['persentase_kesalahan'],
                    'jenis_remediasi' => $this->tentukanJenisRemedial($topik['persentase_kesalahan']),
                    'estimasi_waktu' => $this->tentukanWaktuRemedial($topik['persentase_kesalahan']),
                    'strategi' => $this->tentukanStrategiRemedial($topik['persentase_kesalahan']),
                    'indikator_keberhasilan' => 'Penguasaan TP meningkat menjadi >= 75%'
                ];
            }
        }
        
        return $rencana;
    }
    
    private function tentukanJenisRemedial(float $persentaseKesalahan): string
    {
        if ($persentaseKesalahan >= 60) {
            return 'Remedial Intensif';
        } elseif ($persentaseKesalahan >= 45) {
            return 'Remedial Terbimbing';
        } else {
            return 'Penguatan Konsep';
        }
    }
    
    private function tentukanWaktuRemedial(float $persentaseKesalahan): int
    {
        if ($persentaseKesalahan >= 60) {
            return 120;
        } elseif ($persentaseKesalahan >= 45) {
            return 90;
        } else {
            return 60;
        }
    }
    
    private function tentukanStrategiRemedial(float $persentaseKesalahan): string
    {
        if ($persentaseKesalahan >= 60) {
            return 'Pendekatan multi-sensori, visualisasi konsep, latihan bertahap dengan scaffolding, evaluasi formatif berkala';
        } elseif ($persentaseKesalahan >= 45) {
            return 'Penjelasan ulang dengan pendekatan berbeda, contoh soal yang bervariasi, diskusi kelompok kecil';
        } else {
            return 'Penguatan melalui ringkasan konsep, latihan soal terfokus, peer tutoring';
        }
    }

    /**
     * Analisis butir soal dengan optimasi SQL (fast, low-memory)
     */
    public function analyzeItemStatisticsFast(int $idJadwal, ?int $idKelas = null): array
    {
        $countBuilder = $this->db->table('cbt_jawaban js');
        $countBuilder->select('COUNT(DISTINCT js.id_siswa) as total_siswa');
        $countBuilder->where('js.id_jadwal', $idJadwal);
        $this->applyKelasFilter($countBuilder, $idKelas);
        $totalSiswa = (int) ($countBuilder->get()->getRow()->total_siswa ?? 0);
        
        if ($totalSiswa === 0) {
            return [
                'items' => [],
                'reliability' => ['alpha_cronbach' => 0, 'label' => 'Data tidak cukup'],
                'summary' => ['total_items' => 0, 'total_students' => 0, 'kesukaran' => ['mudah' => 0, 'sedang' => 0, 'sulit' => 0], 'daya_beda' => ['baik' => 0, 'cukup' => 0, 'jelek' => 0]]
            ];
        }
        
        $groupSize = max(1, (int) ceil($totalSiswa * 0.27));
        
        $totalBuilder = $this->db->table('cbt_jawaban js');
        $totalBuilder->select('js.id_siswa, SUM(js.nilai) as total_nilai');
        $totalBuilder->where('js.id_jadwal', $idJadwal);
        $this->applyKelasFilter($totalBuilder, $idKelas);
        $totalBuilder->groupBy('js.id_siswa');
        $totalBuilder->orderBy('total_nilai', 'DESC');
        $studentTotals = $totalBuilder->get()->getResultArray();
        
        $upperGroup = array_slice(array_column($studentTotals, 'id_siswa'), 0, $groupSize);
        $lowerGroup = array_slice(array_column($studentTotals, 'id_siswa'), -$groupSize);
        
        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            cs.id_soal,
            cs.nomor_soal,
            cs.jenis,
            SUBSTRING(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(cs.soal, "<[^>]+>", " "), "\\s+", " ")), 1, 120) as preview_soal,
            COUNT(*) as total_jawaban,
            AVG(js.nilai) as avg_nilai,
            MAX(js.nilai) as max_nilai
        ');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->where('js.id_jadwal', $idJadwal);
        $this->applyKelasFilter($builder, $idKelas);
        $builder->groupBy('cs.id_soal, cs.nomor_soal, cs.jenis, cs.soal');
        $builder->orderBy('cs.nomor_soal', 'ASC');
        
        $items = $builder->get()->getResultArray();
        $processedItems = [];
        $difficultyBuckets = ['mudah' => 0, 'sedang' => 0, 'sulit' => 0];
        $discriminationBuckets = ['baik' => 0, 'cukup' => 0, 'jelek' => 0];
        
        foreach ($items as $item) {
            $idSoal = (int) $item['id_soal'];
            $maxNilai = (float) ($item['max_nilai'] ?? 100);
            $avgNilai = (float) ($item['avg_nilai'] ?? 0);
            
            $p = $maxNilai > 0 ? $avgNilai / $maxNilai : 0;
            if ($p > 1) $p = 1;
            
            $upperMean = $this->getGroupMeanForItem($idJadwal, $idSoal, $upperGroup);
            $lowerMean = $this->getGroupMeanForItem($idJadwal, $idSoal, $lowerGroup);
            
            $d = ($upperMean - $lowerMean) / ($maxNilai > 0 ? $maxNilai : 1);
            
            $diffLabel = $this->difficultyLabel($p);
            $discLabel = $this->discriminationLabel($d);
            
            if ($diffLabel === 'Mudah') $difficultyBuckets['mudah']++;
            elseif ($diffLabel === 'Sulit') $difficultyBuckets['sulit']++;
            else $difficultyBuckets['sedang']++;
            
            if ($d >= 0.40) $discriminationBuckets['baik']++;
            elseif ($d < 0.20) $discriminationBuckets['jelek']++;
            else $discriminationBuckets['cukup']++;
            
            $processedItems[] = [
                'id_soal' => $idSoal,
                'nomor_soal' => (int) $item['nomor_soal'],
                'jenis' => (int) $item['jenis'],
                'preview_soal' => $item['preview_soal'] ?? '-',
                'tingkat_kesukaran' => round($p, 3),
                'kategori_kesukaran' => $diffLabel,
                'daya_beda' => round($d, 3),
                'kategori_daya_beda' => $discLabel,
                'validitas_item' => 0
            ];
        }
        
        $alpha = $this->estimateCronbachAlpha(count($processedItems), 0.15);
        
        return [
            'items' => $processedItems,
            'reliability' => [
                'alpha_cronbach' => round($alpha, 3),
                'label' => $this->reliabilityLabel($alpha)
            ],
            'summary' => [
                'total_items' => count($processedItems),
                'total_students' => $totalSiswa,
                'kesukaran' => $difficultyBuckets,
                'daya_beda' => $discriminationBuckets
            ]
        ];
    }
    
    private function applyKelasFilter($builder, ?int $idKelas): void
    {
        if ($idKelas === null) {
            return;
        }
        $builder->join('cbt_jadwal jd', 'jd.id_jadwal = js.id_jadwal');
        $builder->join('kelas_siswa ks', 'ks.id_siswa = js.id_siswa AND ks.id_tp = CAST(jd.id_tp AS UNSIGNED) AND ks.id_smt = CAST(jd.id_smt AS UNSIGNED)');
        $builder->where('ks.id_kelas', $idKelas);
    }

    private function getGroupMeanForItem(int $idJadwal, int $idSoal, array $studentIds): float
    {
        if (empty($studentIds)) return 0;
        
        $builder = $this->db->table('cbt_jawaban');
        $builder->select('AVG(nilai) as mean_nilai');
        $builder->where('id_jadwal', $idJadwal);
        $builder->where('id_soal', $idSoal);
        $builder->whereIn('id_siswa', $studentIds);
        
        $result = $builder->get()->getRow();
        return (float) ($result->mean_nilai ?? 0);
    }
    
    private function estimateCronbachAlpha(int $nItems, float $avgItemVariance): float
    {
        if ($nItems < 2) return 0;
        $alpha = ($nItems * 0.3) / (1 + ($nItems - 1) * 0.3);
        return min(0.95, max(0, $alpha));
    }

    /**
     * Analisis butir soal: kesukaran, daya beda, validitas item, reliabilitas tes
     */
    public function analyzeItemStatistics(int $idJadwal, ?int $idKelas = null): array
    {
        $countBuilder = $this->db->table('cbt_jawaban js');
        $countBuilder->where('js.id_jadwal', $idJadwal);
        $this->applyKelasFilter($countBuilder, $idKelas);
        $count = $countBuilder->countAllResults();
        
        if ($count > 5000) {
            return $this->analyzeItemStatisticsFast($idJadwal, $idKelas);
        }

        $builder = $this->db->table('cbt_jawaban js');
        $builder->select('
            js.id_siswa,
            js.id_soal,
            js.nilai,
            cs.nomor_soal,
            cs.jenis,
            cs.soal
        ');
        $builder->join('cbt_soal cs', 'cs.id_soal = js.id_soal');
        $builder->where('js.id_jadwal', $idJadwal);

        $this->applyKelasFilter($builder, $idKelas);

        $query = $builder->orderBy('cs.nomor_soal', 'ASC')->get();
        $rows = $query ? $query->getResultArray() : [];
        if (empty($rows)) {
            return [
                'items' => [],
                'reliability' => ['alpha_cronbach' => 0, 'label' => 'Data tidak cukup'],
                'summary' => ['total_items' => 0, 'total_students' => 0]
            ];
        }

        $maxNilaiBySoal = [];
        foreach ($rows as $row) {
            $idSoal = (int) ($row['id_soal'] ?? 0);
            $nilai = (float) ($row['nilai'] ?? 0);
            if (!isset($maxNilaiBySoal[$idSoal]) || $nilai > $maxNilaiBySoal[$idSoal]) {
                $maxNilaiBySoal[$idSoal] = $nilai;
            }
        }

        $studentMatrix = [];
        $soalMeta = [];
        foreach ($rows as $row) {
            $idSiswa = (int) $row['id_siswa'];
            $idSoal = (int) $row['id_soal'];
            $score = $this->normalizeItemScore($row, $maxNilaiBySoal[$idSoal] ?? 0);
            $studentMatrix[$idSiswa][$idSoal] = $score;

            if (!isset($soalMeta[$idSoal])) {
                $soalText = strip_tags((string) ($row['soal'] ?? ''));
                $soalText = preg_replace('/\s+/', ' ', $soalText ?? '');
                $soalMeta[$idSoal] = [
                    'id_soal' => $idSoal,
                    'nomor_soal' => (int) ($row['nomor_soal'] ?? 0),
                    'jenis' => (int) ($row['jenis'] ?? 0),
                    'preview_soal' => substr(trim((string) $soalText), 0, 120),
                ];
            }
        }

        $allSoalIds = array_keys($soalMeta);
        sort($allSoalIds);
        foreach ($studentMatrix as $idSiswa => $scores) {
            foreach ($allSoalIds as $idSoal) {
                if (!isset($scores[$idSoal])) {
                    $studentMatrix[$idSiswa][$idSoal] = 0.0;
                }
            }
        }

        $totalByStudent = [];
        foreach ($studentMatrix as $idSiswa => $scores) {
            $totalByStudent[$idSiswa] = array_sum($scores);
        }

        $sortedTotals = $totalByStudent;
        arsort($sortedTotals);
        $studentIds = array_keys($sortedTotals);
        $nStudents = count($studentIds);
        $groupSize = max(1, (int) ceil($nStudents * 0.27));
        $upper = array_slice($studentIds, 0, $groupSize);
        $lower = array_slice($studentIds, -$groupSize);

        $items = [];
        $difficultyBuckets = ['mudah' => 0, 'sedang' => 0, 'sulit' => 0];
        $discriminationBuckets = ['baik' => 0, 'cukup' => 0, 'jelek' => 0];

        foreach ($allSoalIds as $idSoal) {
            $itemScores = [];
            foreach ($studentMatrix as $idSiswa => $scores) {
                $itemScores[$idSiswa] = (float) ($scores[$idSoal] ?? 0);
            }

            $p = $nStudents > 0 ? array_sum($itemScores) / $nStudents : 0;
            $meanUpper = $this->meanByStudents($itemScores, $upper);
            $meanLower = $this->meanByStudents($itemScores, $lower);
            $d = $meanUpper - $meanLower;

            $difficultyLabel = $this->difficultyLabel($p);
            $discriminationLabel = $this->discriminationLabel($d);
            $validity = $this->pearson(array_values($itemScores), array_values($totalByStudent));

            if ($difficultyLabel === 'Mudah') $difficultyBuckets['mudah']++;
            elseif ($difficultyLabel === 'Sulit') $difficultyBuckets['sulit']++;
            else $difficultyBuckets['sedang']++;

            if ($d >= 0.40) $discriminationBuckets['baik']++;
            elseif ($d < 0.20) $discriminationBuckets['jelek']++;
            else $discriminationBuckets['cukup']++;

            $items[] = array_merge($soalMeta[$idSoal], [
                'tingkat_kesukaran' => round($p, 3),
                'kategori_kesukaran' => $difficultyLabel,
                'daya_beda' => round($d, 3),
                'kategori_daya_beda' => $discriminationLabel,
                'validitas_item' => round($validity, 3),
            ]);
        }

        usort($items, static function ($a, $b) {
            return ($a['nomor_soal'] ?? 0) <=> ($b['nomor_soal'] ?? 0);
        });

        $alpha = $this->cronbachAlpha($studentMatrix, $allSoalIds);

        return [
            'items' => $items,
            'reliability' => [
                'alpha_cronbach' => round($alpha, 3),
                'label' => $this->reliabilityLabel($alpha)
            ],
            'summary' => [
                'total_items' => count($allSoalIds),
                'total_students' => $nStudents,
                'kesukaran' => $difficultyBuckets,
                'daya_beda' => $discriminationBuckets
            ]
        ];
    }

    private function normalizeItemScore(array $row, float $maxNilai): float
    {
        $nilai = (float) ($row['nilai'] ?? 0);
        if ($maxNilai <= 0) {
            return $nilai > 0 ? 1.0 : 0.0;
        }
        $normalized = $nilai / $maxNilai;
        if ($normalized < 0) return 0.0;
        if ($normalized > 1) return 1.0;
        return $normalized;
    }

    private function meanByStudents(array $itemScores, array $studentIds): float
    {
        if (empty($studentIds)) {
            return 0;
        }
        $sum = 0;
        $count = 0;
        foreach ($studentIds as $idSiswa) {
            if (array_key_exists($idSiswa, $itemScores)) {
                $sum += (float) $itemScores[$idSiswa];
                $count++;
            }
        }
        return $count > 0 ? $sum / $count : 0;
    }

    private function variance(array $values): float
    {
        $n = count($values);
        if ($n < 2) {
            return 0;
        }
        $mean = array_sum($values) / $n;
        $sum = 0;
        foreach ($values as $v) {
            $sum += ($v - $mean) * ($v - $mean);
        }
        return $sum / ($n - 1);
    }

    private function cronbachAlpha(array $matrix, array $itemIds): float
    {
        $k = count($itemIds);
        if ($k < 2 || empty($matrix)) {
            return 0;
        }

        $totalScores = [];
        foreach ($matrix as $scores) {
            $total = 0;
            foreach ($itemIds as $itemId) {
                $total += (float) ($scores[$itemId] ?? 0);
            }
            $totalScores[] = $total;
        }

        $varTotal = $this->variance($totalScores);
        if ($varTotal <= 0) {
            return 0;
        }

        $sumVarItems = 0;
        foreach ($itemIds as $itemId) {
            $vals = [];
            foreach ($matrix as $scores) {
                $vals[] = (float) ($scores[$itemId] ?? 0);
            }
            $sumVarItems += $this->variance($vals);
        }

        $alpha = ($k / ($k - 1)) * (1 - ($sumVarItems / $varTotal));
        if (!is_finite($alpha)) {
            return 0;
        }
        return max(-1, min(1, $alpha));
    }

    private function pearson(array $x, array $y): float
    {
        $n = count($x);
        if ($n < 2 || $n !== count($y)) {
            return 0;
        }
        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;
        $sumXY = 0;
        $sumXX = 0;
        $sumYY = 0;
        for ($i = 0; $i < $n; $i++) {
            $dx = $x[$i] - $meanX;
            $dy = $y[$i] - $meanY;
            $sumXY += $dx * $dy;
            $sumXX += $dx * $dx;
            $sumYY += $dy * $dy;
        }
        if ($sumXX <= 0 || $sumYY <= 0) {
            return 0;
        }
        return $sumXY / sqrt($sumXX * $sumYY);
    }

    private function difficultyLabel(float $p): string
    {
        if ($p >= 0.70) {
            return 'Mudah';
        }
        if ($p < 0.30) {
            return 'Sulit';
        }
        return 'Sedang';
    }

    private function discriminationLabel(float $d): string
    {
        if ($d >= 0.70) {
            return 'Sangat Baik';
        }
        if ($d >= 0.40) {
            return 'Baik';
        }
        if ($d < 0.20) {
            return 'Jelek';
        }
        return 'Cukup';
    }

    private function reliabilityLabel(float $alpha): string
    {
        if ($alpha >= 0.90) {
            return 'Sangat Tinggi';
        }
        if ($alpha >= 0.80) {
            return 'Tinggi';
        }
        if ($alpha >= 0.70) {
            return 'Cukup';
        }
        if ($alpha >= 0.60) {
            return 'Rendah';
        }
        return 'Sangat Rendah';
    }
}
