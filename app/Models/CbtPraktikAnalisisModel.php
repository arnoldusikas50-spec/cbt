<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk Analisis Hasil dan Rekomendasi Pembelajaran
 * Menyediakan analisis otomatis dan rekomendasi peningkatan pembelajaran
 */
class CbtPraktikAnalisisModel extends Model
{
    protected $table = 'cbt_praktik_analisis';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'praktik_id', 'siswa_id', 'hasil_id', 'tipe_analisis',
        'skor_ketercapaian', 'indikator_lemah', 'rekomendasi_belajar',
        'sumber_data', 'created_by', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Tipe analisis
    const TIPE_REMEDIAL = 'remedial';
    const TIPE_PENINGKATAN = 'peningkatan';
    const TIPE_PENCAPAIAN = 'pencapaian';

    /**
     * Generate analisis otomatis berdasarkan hasil ujian
     */
    public function generateAnalisis(
        int $hasilId,
        int $praktikId,
        int $siswaId,
        ?int $createdBy = null
    ): bool {
        $hasilModel = new CbtPraktikHasilModel();
        $rubrikModel = new CbtPraktikRubrikModel();
        $praktikModel = new CbtPraktikModel();

        $hasil = $hasilModel->getHasilDetail($hasilId);
        $praktik = $praktikModel->find($praktikId);
        $rubriks = $rubrikModel->getRubrikPraktik($praktikId, false);

        if (!$hasil || !$praktik) {
            return false;
        }

        $kkm = $praktik->kkm ?? 70;
        $nilaiAkhir = $hasil->nilai_akhir ?? 0;

        // Tentukan tipe analisis
        $tipeAnalisis = $nilaiAkhir < $kkm ? self::TIPE_REMEDIAL : ($nilaiAkhir >= 90 ? self::TIPE_PENCAPAIAN : self::TIPE_PENINGKATAN);

        // Hitung skor ketercapaian
        $skorKetercapaian = round(($nilaiAkhir / 100) * 100, 2);

        // Analisis indikator lemah
        $indikatorLemah = $this->analisisIndikatorLemah($hasil, $rubriks, $kkm);

        // Generate rekomendasi
        $rekomendasi = $this->generateRekomendasi($tipeAnalisis, $indikatorLemah, $rubriks, $hasil);

        // Simpan analisis
        $data = [
            'praktik_id' => $praktikId,
            'siswa_id' => $siswaId,
            'hasil_id' => $hasilId,
            'tipe_analisis' => $tipeAnalisis,
            'skor_ketercapaian' => $skorKetercapaian,
            'indikator_lemah' => json_encode($indikatorLemah),
            'rekomendasi_belajar' => json_encode($rekomendasi),
            'sumber_data' => 'auto',
            'created_by' => $createdBy,
        ];

        // Hapus analisis lama jika ada
        $this->where(['hasil_id' => $hasilId, 'tipe_analisis' => $tipeAnalisis])->delete();

        return (bool) $this->insert($data);
    }

    /**
     * Analisis indikator yang lemah berdasarkan rincian nilai
     */
    private function analisisIndikatorLemah(object $hasil, array $rubriks, float $kkm): array
    {
        $indikatorLemah = [];
        $rincian = json_decode($hasil->rincian_nilai_rubrik ?? '{}', true);

        if (empty($rincian)) {
            return $indikatorLemah;
        }

        foreach ($rincian as $rubrikId => $nilaiData) {
            $nilai = is_array($nilaiData) ? ($nilaiData['nilai'] ?? 0) : (float)$nilaiData;

            // Cari rubrik detail
            $rubrik = null;
            foreach ($rubriks as $r) {
                if ($r->id == $rubrikId) {
                    $rubrik = $r;
                    break;
                }
            }

            if (!$rubrik) continue;

            // Hitung threshold berdasarkan bobot
            $threshold = ($rubrik->bobot_persen / 100) * $kkm;

            if ($nilai < $threshold) {
                $indikatorLemah[] = [
                    'rubrik_id' => $rubrikId,
                    'kode' => $rubrik->kode_kriteria,
                    'nama' => $rubrik->nama_kriteria,
                    'nilai' => $nilai,
                    'bobot' => $rubrik->bobot_persen,
                    'threshold' => round($threshold, 2),
                    'gap' => round($threshold - $nilai, 2),
                    'deskripsi' => $rubrik->deskripsi,
                ];
            }
        }

        // Sort by gap (terbesar dulu)
        usort($indikatorLemah, fn($a, $b) => $b['gap'] <=> $a['gap']);

        return $indikatorLemah;
    }

    /**
     * Generate rekomendasi pembelajaran
     */
    private function generateRekomendasi(string $tipe, array $indikatorLemah, array $rubriks, object $hasil): array
    {
        $rekomendasi = [];

        switch ($tipe) {
            case self::TIPE_REMEDIAL:
                $rekomendasi = $this->generateRekomendasiRemedial($indikatorLemah, $rubriks);
                break;

            case self::TIPE_PENCAPAIAN:
                $rekomendasi = $this->generateRekomendasiPencapaian($hasil, $rubriks);
                break;

            case self::TIPE_PENINGKATAN:
            default:
                $rekomendasi = $this->generateRekomendasiPeningkatan($indikatorLemah, $rubriks);
                break;
        }

        return $rekomendasi;
    }

    /**
     * Generate rekomendasi untuk siswa remedial
     */
    private function generateRekomendasiRemedial(array $indikatorLemah, array $rubriks): array
    {
        $rekomendasi = [
            [
                'kategori' => 'pemulihan',
                'prioritas' => 'tinggi',
                'judul' => 'Program Pemulihan Kompetensi',
                'deskripsi' => 'Ikuti program pemulihan untuk meningkatkan pemahaman pada kompetensi dasar yang belum tercapai.',
                'tindakan' => [
                    'Review materi pokok terkait ' . ($indikatorLemah[0]['nama'] ?? 'kompetensi'),
                    'Praktik tambahan dengan pendampingan guru',
                    'Diskusi kelompok kecil dengan teman yang sudah menguasai',
                    'Latihan soal/praktik berulang dengan feedback instan',
                ],
                'target_waktu' => '2 minggu',
            ],
            [
                'kategori' => 'penguatan',
                'prioritas' => 'sedang',
                'judul' => 'Penguatan Indikator Spesifik',
                'deskripsi' => 'Fokus pada peningkatan indikator dengan gap terbesar.',
                'tindakan' => array_map(fn($i) => "Penguatan {$i['nama']} (gap: {$i['gap']})", array_slice($indikatorLemah, 0, 2)),
                'target_waktu' => '1 minggu',
            ],
            [
                'kategori' => 'sumber_belajar',
                'prioritas' => 'sedang',
                'judul' => 'Sumber Belajar Tambahan',
                'deskripsi' => 'Gunakan sumber belajar alternatif untuk pemahaman lebih baik.',
                'tindakan' => [
                    'Video tutorial dari channel edukasi terpercaya',
                    'Modul pembelajaran interaktif online',
                    'Buku referensi dengan pembahasan detail',
                    'Simulasi/praktik virtual jika tersedia',
                ],
                'target_waktu' => 'ongoing',
            ],
        ];

        return $rekomendasi;
    }

    /**
     * Generate rekomendasi untuk peningkatan
     */
    private function generateRekomendasiPeningkatan(array $indikatorLemah, array $rubriks): array
    {
        $rekomendasi = [
            [
                'kategori' => 'pengayaan',
                'prioritas' => 'sedang',
                'judul' => 'Proyek Pengayaan',
                'deskripsi' => 'Tantang diri dengan proyek pengayaan untuk meningkatkan keahlian.',
                'tindakan' => [
                    'Tantangan praktik dengan tingkat kesulitan lebih tinggi',
                    'Proyek kolaboratif dengan teman',
                    'Eksplorasi aplikasi praktis di dunia nyata',
                    'Berbagi pengetahuan dengan mendampingi teman',
                ],
                'target_waktu' => '2-3 minggu',
            ],
        ];

        if (!empty($indikatorLemah)) {
            $rekomendasi[] = [
                'kategori' => 'perbaikan',
                'prioritas' => 'sedang',
                'judul' => 'Perbaikan Area Tertentu',
                'deskripsi' => 'Fokus pada aspek yang masih perlu perbaikan meskipun sudah lulus.',
                'tindakan' => array_map(fn($i) => "Latihan tambahan: {$i['nama']}", array_slice($indikatorLemah, 0, 2)),
                'target_waktu' => '1 minggu',
            ];
        }

        return $rekomendasi;
    }

    /**
     * Generate rekomendasi untuk pencapaian tinggi
     */
    private function generateRekomendasiPencapaian(object $hasil, array $rubriks): array
    {
        return [
            [
                'kategori' => 'pengembangan_diri',
                'prioritas' => 'sedang',
                'judul' => 'Pengembangan Diri Lanjut',
                'deskripsi' => 'Kamu sudah menguasai kompetensi dengan baik. Saatnya mengembangkan potensi lebih!',
                'tindakan' => [
                    'Mentoring teman yang membutuhkan bantuan',
                    'Mengikuti kompetisi/olimpiade terkait materi',
                    'Membuat portofolio proyek kreatif',
                    'Eksplorasi advanced topics di bidang ini',
                ],
                'target_waktu' => 'ongoing',
            ],
            [
                'kategori' => 'kolaborasi',
                'prioritas' => 'rendah',
                'judul' => 'Kontribusi Kolaboratif',
                'deskripsi' => 'Bagikan keahlianmu dengan kontribusi positif.',
                'tindakan' => [
                    'Membuat tutorial/video penjelasan untuk teman',
                    'Membantu guru sebagai asisten praktik',
                    'Membentuk study group sebagai fasilitator',
                ],
                'target_waktu' => 'ongoing',
            ],
        ];
    }

    /**
     * Get analisis untuk sebuah hasil
     */
    public function getAnalisisByHasil(int $hasilId, ?string $tipe = null): ?object
    {
        $builder = $this->where('hasil_id', $hasilId);

        if ($tipe) {
            $builder->where('tipe_analisis', $tipe);
        }

        $result = $builder->first();

        if ($result) {
            $result->indikator_lemah = $result->indikator_lemah ? json_decode($result->indikator_lemah, true) : [];
            $result->rekomendasi_belajar = $result->rekomendasi_belajar ? json_decode($result->rekomendasi_belajar, true) : [];
        }

        return $result;
    }

    /**
     * Get analisis untuk praktik (semua siswa)
     */
    public function getAnalisisPraktik(int $praktikId): array
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('a.*, s.nama as nama_siswa, s.nis');
        $builder->join('master_siswa s', 'a.siswa_id = s.id_siswa', 'left');
        $builder->where('a.praktik_id', $praktikId);
        $builder->orderBy('a.skor_ketercapaian', 'ASC');

        $query = $builder->get();
        $results = $query ? $query->getResult() : [];

        foreach ($results as &$result) {
            $result->indikator_lemah = $result->indikator_lemah ? json_decode($result->indikator_lemah, true) : [];
            $result->rekomendasi_belajar = $result->rekomendasi_belajar ? json_decode($result->rekomendasi_belajar, true) : [];
        }

        return $results;
    }

    /**
     * Get daftar siswa yang perlu remedial
     */
    public function getDaftarRemedial(int $praktikId): array
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('a.*, s.nama as nama_siswa, s.nis, k.nama_kelas, h.nilai_akhir');
        $builder->join('master_siswa s', 'a.siswa_id = s.id_siswa', 'left');
        $builder->join('cbt_praktik_hasil h', 'a.hasil_id = h.id', 'left');
        $builder->join('master_kelas k', 'h.kelas_id = k.id_kelas', 'left');
        $builder->where('a.praktik_id', $praktikId);
        $builder->where('a.tipe_analisis', self::TIPE_REMEDIAL);
        $builder->orderBy('h.nilai_akhir', 'ASC');

        $query = $builder->get();
        $results = $query ? $query->getResult() : [];

        foreach ($results as &$result) {
            $result->indikator_lemah = $result->indikator_lemah ? json_decode($result->indikator_lemah, true) : [];
            $result->rekomendasi_belajar = $result->rekomendasi_belajar ? json_decode($result->rekomendasi_belajar, true) : [];
        }

        return $results;
    }

    /**
     * Get statistik analisis untuk praktik
     */
    public function getStatistikAnalisis(int $praktikId): object
    {
        $sql = "
            SELECT 
                COUNT(*) as total_dianalisis,
                COUNT(CASE WHEN tipe_analisis = 'remedial' THEN 1 END) as total_remedial,
                COUNT(CASE WHEN tipe_analisis = 'peningkatan' THEN 1 END) as total_peningkatan,
                COUNT(CASE WHEN tipe_analisis = 'pencapaian' THEN 1 END) as total_pencapaian,
                AVG(skor_ketercapaian) as rata_skor,
                MIN(skor_ketercapaian) as skor_terendah,
                MAX(skor_ketercapaian) as skor_tertinggi
            FROM {$this->table}
            WHERE praktik_id = ?
        ";

        $query = $this->db->query($sql, [$praktikId]);
        $result = $query ? $query->getRow() : null;

        return $result ?: (object)[
            'total_dianalisis' => 0,
            'total_remedial' => 0,
            'total_peningkatan' => 0,
            'total_pencapaian' => 0,
            'rata_skor' => 0,
            'skor_terendah' => 0,
            'skor_tertinggi' => 0,
        ];
    }

    /**
     * Batch generate analisis untuk semua siswa dalam praktik
     */
    public function batchGenerateAnalisis(int $praktikId, ?int $createdBy = null): array
    {
        $hasilModel = new CbtPraktikHasilModel();
        $hasilList = $hasilModel->getHasilByPraktik($praktikId, false);

        $hasil = [
            'total' => count($hasilList),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($hasilList as $h) {
            if ($this->generateAnalisis($h->id, $praktikId, $h->siswa_id, $createdBy)) {
                $hasil['success']++;
                $hasil['details'][] = ['siswa_id' => $h->siswa_id, 'status' => 'success'];
            } else {
                $hasil['failed']++;
                $hasil['details'][] = ['siswa_id' => $h->siswa_id, 'status' => 'failed'];
            }
        }

        return $hasil;
    }

    /**
     * Get rekomendasi umum untuk kelas/praktik
     */
    public function getRekomendasiUmum(int $praktikId): array
    {
        $statistik = $this->getStatistikAnalisis($praktikId);
        $rubrikModel = new CbtPraktikRubrikModel();
        $rubriks = $rubrikModel->getRubrikPraktik($praktikId, false);

        $rekomendasi = [];

        // Analisis berdasarkan statistik
        if ($statistik->total_remedial > ($statistik->total_dianalisis * 0.3)) {
            $rekomendasi[] = [
                'kategori' => 'strategi_kelas',
                'urgensi' => 'tinggi',
                'judul' => 'Review Strategi Pembelajaran',
                'deskripsi' => ">30% siswa perlu remedial. Pertimbangkan untuk mengulang materi dengan pendekatan berbeda.",
                'saran_tindakan' => [
                    'Review metode pembelajaran yang digunakan',
                    'Identifikasi miskonsepsi umum siswa',
                    'Tambahkan variasi aktivitas pembelajaran',
                    'Perhatikan kesiapan belajar siswa sebelum praktik',
                ],
            ];
        }

        if ($statistik->rata_skor < 75) {
            $rekomendasi[] = [
                'kategori' => 'pembelajaran_ulang',
                'urgensi' => 'sedang',
                'judul' => 'Pembelajaran Ulang Terpimpin',
                'deskripsi' => 'Rata-rata skor kelas di bawah ekspektasi. Lakukan remedial terstruktur.',
                'saran_tindakan' => [
                    'Buat jadwal remedial terstruktur',
                    'Gunakan peer tutoring',
                    'Sediakan modul pembelajaran mandiri',
                    'Lakukan evaluasi formatif berkala',
                ],
            ];
        }

        // Rekomendasi berdasarkan rubrik yang paling lemah
        $allIndikatorLemah = [];
        $analisisList = $this->getAnalisisPraktik($praktikId);

        foreach ($analisisList as $a) {
            if (!empty($a->indikator_lemah)) {
                foreach ($a->indikator_lemah as $indikator) {
                    $key = $indikator['rubrik_id'];
                    if (!isset($allIndikatorLemah[$key])) {
                        $allIndikatorLemah[$key] = $indikator;
                        $allIndikatorLemah[$key]['count'] = 0;
                    }
                    $allIndikatorLemah[$key]['count']++;
                }
            }
        }

        // Sort by frequency
        uasort($allIndikatorLemah, fn($a, $b) => $b['count'] <=> $a['count']);

        $topIndikator = array_slice($allIndikatorLemah, 0, 3, true);

        if (!empty($topIndikator)) {
            $indikatorNames = array_map(fn($i) => $i['nama'], $topIndikator);
            $rekomendasi[] = [
                'kategori' => 'fokus_indikator',
                'urgensi' => 'tinggi',
                'judul' => 'Penguatan Indikator Prioritas',
                'deskripsi' => 'Fokus pada indikator yang paling banyak menjadi kendala: ' . implode(', ', $indikatorNames),
                'saran_tindakan' => array_map(fn($i) => "Penguatan khusus untuk: {$i['nama']} ({$i['count']} siswa)", $topIndikator),
            ];
        }

        return $rekomendasi;
    }
}
