<?php

namespace App\Traits;

/**
 * KKTP (Kriteria Ketercapaian Tujuan Pembelajaran) Trait
 * Standar Kurikulum Merdeka untuk penilaian praktik
 */
trait KktpTrait
{
    /**
     * Interval KKTP sesuai Kurikulum Merdeka
     */
    protected static array $kktpInterval = [
        ['nama' => 'Baru Berkembang',      'singkatan' => 'BB', 'min' => 0,   'max' => 40,  'warna' => '#ef4444'],
        ['nama' => 'Layak',                'singkatan' => 'L',  'min' => 41,  'max' => 65,  'warna' => '#f59e0b'],
        ['nama' => 'Cakap',                'singkatan' => 'C',  'min' => 66,  'max' => 85,  'warna' => '#3b82f6'],
        ['nama' => 'Mahir',                'singkatan' => 'M',  'min' => 86,  'max' => 100, 'warna' => '#22c55e'],
    ];

    /**
     * Dimensi Profil Pelajar Pancasila
     */
    protected static array $dimensiPpp = [
        'beriman'       => 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia',
        'berkebinekaan' => 'Berkebinekaan Global',
        'gotong_royong' => 'Bergotong Royong',
        'mandiri'       => 'Mandiri',
        'bernalar_kritis' => 'Bernalar Kritis',
        'kreatif'       => 'Kreatif',
    ];

    /**
     * Dapatkan predikat KKTP dari nilai
     */
    public function getKktp(?float $nilai): array
    {
        $nilai = $nilai ?? 0;
        foreach (static::$kktpInterval as $interval) {
            if ($nilai >= $interval['min'] && $nilai <= $interval['max']) {
                return [
                    'predikat'   => $interval['nama'],
                    'singkatan'  => $interval['singkatan'],
                    'warna'      => $interval['warna'],
                    'nilai'      => round($nilai, 2),
                ];
            }
        }
        return [
            'predikat'  => 'Tidak Teridentifikasi',
            'singkatan' => '-',
            'warna'     => '#6b7280',
            'nilai'     => round($nilai, 2),
        ];
    }

    /**
     * Dapatkan deskripsi KKTP untuk satu kriteria
     */
    public function generateDeskripsiKriteria(string $namaKriteria, float $nilai, float $bobot): string
    {
        $kktp = $this->getKktp($nilai);
        $persen = $bobot > 0 ? round(($nilai / $bobot) * 100, 2) : 0;

        $template = [
            'Baru Berkembang' => "Siswa masih perlu bimbingan intensif dalam aspek {$namaKriteria}. " .
                "Pencapaian baru {$persen}% dari bobot yang diharapkan. Disarankan mengulang latihan dasar.",
            'Layak' => "Siswa cukup mampu dalam aspek {$namaKriteria} dengan pencapaian {$persen}% " .
                "dari bobot. Perlu penguatan pada beberapa sub-kompetensi.",
            'Cakap' => "Siswa sudah baik dalam aspek {$namaKriteria} dengan pencapaian {$persen}% " .
                "dari bobot. Dapat ditingkatkan ke level mahir dengan latihan lanjutan.",
            'Mahir' => "Siswa sangat menguasai aspek {$namaKriteria} dengan pencapaian {$persen}% " .
                "dari bobot. Dapat menjadi tutor sebaya atau mengikuti pengayaan.",
        ];

        return $template[$kktp['predikat']] ?? "Siswa memperoleh nilai {$nilai} untuk aspek {$namaKriteria}.";
    }

    /**
     * Generate deskripsi rapor naratif lengkap
     */
    public function generateDeskripsiRapor(array $rincianNilai, array $rubrik, ?float $nilaiAkhir): string
    {
        if (empty($rincianNilai) || empty($rubrik)) {
            return 'Belum ada data penilaian.';
        }

        $parts = [];
        $kktpAkhir = $this->getKktp($nilaiAkhir);

        $parts[] = "Secara umum, siswa mencapai predikat **{$kktpAkhir['predikat']}** " .
            "dengan nilai akhir {$kktpAkhir['nilai']}.";

        $lemah = [];
        $kuat = [];

        foreach ($rubrik as $r) {
            $nilai = $rincianNilai[$r->id] ?? $rincianNilai[$r->kode_kriteria] ?? null;
            if ($nilai === null) continue;

            $bobot = (float)($r->bobot_persen ?? 0);
            $persen = $bobot > 0 ? ($nilai / $bobot) * 100 : 0;

            $kktp = $this->getKktp($persen * 100);
            $deskripsi = "- **{$r->nama_kriteria}**: {$kktp['predikat']} ({$kktp['singkatan']})";

            if ($persen < 0.66) {
                $lemah[] = $deskripsi;
            } else {
                $kuat[] = $deskripsi;
            }
        }

        if (!empty($kuat)) {
            $parts[] = "Kekuatan:";
            $parts = array_merge($parts, $kuat);
        }

        if (!empty($lemah)) {
            $parts[] = "Area yang perlu ditingkatkan:";
            $parts = array_merge($parts, $lemah);
        }

        if (!empty($lemah)) {
            $parts[] = "Rekomendasi: Lakukan remedial pada aspek yang masih kurang dan konsultasi dengan guru.";
        } else {
            $parts[] = "Rekomendasi: Pertahankan prestasi dan ikuti kegiatan pengayaan.";
        }

        return implode("\n", $parts);
    }

    /**
     * Cek apakah nilai mencapai KKTP (minimal Cakap).
     * Bila praktik memakai kktp_mode='kkm' dengan nilai kkm terisi, ambang memakai KKM tsb.
     */
    public function isTuntas(?float $nilai, $kkm = null): bool
    {
        return ($nilai ?? 0) >= ($kkm ?? 66);
    }

    /**
     * Ambang tuntas sebuah praktik (KKM jika mode kkm, default KKTP Cakap 66).
     */
    public function getKkm(?object $praktik): float
    {
        if ($praktik && ($praktik->kktp_mode ?? null) === 'kkm' && $praktik->kkm !== null && $praktik->kkm !== '') {
            return (float)$praktik->kkm;
        }
        return 66.0;
    }

    /**
     * Dapatkan semua interval KKTP
     */
    public function getKktpInterval(): array
    {
        return static::$kktpInterval;
    }

    /**
     * Dapatkan daftar Dimensi PPP
     */
    public function getDimensiPpp(): array
    {
        return static::$dimensiPpp;
    }
}
