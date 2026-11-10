<?php

namespace App\Services;

/**
 * BloomValidatorService
 *
 * Memvalidasi distribusi Taksonomi Bloom Revisi (C1-C6) pada kisi-kisi soal.
 * Membandingkan distribusi aktual dengan target berdasarkan tipe ujian.
 *
 * Standar target mengacu pada:
 *  - Panduan BSKAP 2023 untuk Asesmen Nasional
 *  - Panduan Pengembangan Soal Kemendikbudristek (2022)
 *  - Anderson & Krathwohl (2001) Taxonomy Revised
 */
class BloomValidatorService
{
    /**
     * Target distribusi (%) per level Bloom untuk setiap tipe ujian.
     * Format: ['tipe' => ['C1' => %, 'C2' => %, ..., 'C6' => %]]
     */
    private array $targets = [
        'ulangan_harian' => ['C1' => 10, 'C2' => 20, 'C3' => 30, 'C4' => 25, 'C5' => 10, 'C6' =>  5],
        'uts'            => ['C1' =>  5, 'C2' => 15, 'C3' => 25, 'C4' => 30, 'C5' => 15, 'C6' => 10],
        'uas'            => ['C1' =>  5, 'C2' => 10, 'C3' => 20, 'C4' => 35, 'C5' => 20, 'C6' => 10],
        'us'             => ['C1' =>  0, 'C2' => 10, 'C3' => 20, 'C4' => 35, 'C5' => 25, 'C6' => 10],
        'anbk'           => ['C1' =>  0, 'C2' =>  5, 'C3' => 15, 'C4' => 35, 'C5' => 30, 'C6' => 15],
    ];

    /**
     * Label tipe ujian untuk tampilan.
     */
    private array $tipeLabel = [
        'ulangan_harian' => 'Ulangan Harian',
        'uts'            => 'UTS / PTS',
        'uas'            => 'UAS / PAS',
        'us'             => 'Ujian Sekolah',
        'anbk'           => 'ANBK / Asesmen Nasional',
    ];

    /** Toleransi deviasi dari target dalam poin persentase */
    private int $toleransi = 10;

    // ================================================================
    // API PUBLIK
    // ================================================================

    /**
     * Validasi distribusi Bloom dari daftar item kisi-kisi.
     *
     * @param  array  $items      Array objek/array dengan kolom `level_kognitif`
     * @param  string $tipeUjian  Salah satu key dari $this->targets
     * @return array  Hasil validasi lengkap
     */
    public function validasi(array $items, string $tipeUjian = 'uts'): array
    {
        $target = $this->targets[$tipeUjian] ?? $this->targets['uts'];
        $total  = count($items);

        if ($total === 0) {
            return [
                'valid'         => false,
                'tipe_ujian'    => $tipeUjian,
                'total_item'    => 0,
                'distribusi'    => [],
                'target'        => $target,
                'skor_hots'     => 0,
                'target_hots'   => $target['C4'] + $target['C5'] + $target['C6'],
                'rekomendasi'   => [['level' => 'error', 'pesan' => 'Tidak ada item untuk divalidasi']],
            ];
        }

        // Hitung distribusi aktual
        $aktual = ['C1' => 0, 'C2' => 0, 'C3' => 0, 'C4' => 0, 'C5' => 0, 'C6' => 0];
        foreach ($items as $item) {
            $level = strtoupper(trim(
                is_array($item) ? ($item['level_kognitif'] ?? 'C3')
                                : ($item->level_kognitif ?? 'C3')
            ));
            // Normalkan: kalau ada nilai yang bukan C1-C6 anggap C3
            if (!isset($aktual[$level])) {
                $level = 'C3';
            }
            $aktual[$level]++;
        }

        // Konversi ke persentase
        $persen  = [];
        $deviasi = [];
        foreach ($aktual as $level => $jml) {
            $persen[$level]  = round(($jml / $total) * 100, 1);
            $deviasi[$level] = round($persen[$level] - $target[$level], 1);
        }

        // Bangun daftar rekomendasi
        $rekomendasi = $this->bangunRekomendasi($persen, $target, $aktual, $total);

        // Hitung HOTS
        $hotsPct    = $persen['C4'] + $persen['C5'] + $persen['C6'];
        $hotsTarget = $target['C4'] + $target['C5'] + $target['C6'];

        if ($hotsPct < $hotsTarget - $this->toleransi) {
            $rekomendasi[] = [
                'level'    => 'warning',
                'kategori' => 'HOTS',
                'aktual'   => $hotsPct,
                'target'   => $hotsTarget,
                'pesan'    => sprintf(
                    'Proporsi HOTS (C4–C6) hanya %.1f%%, target minimal %.1f%% untuk %s.',
                    $hotsPct, $hotsTarget, $this->tipeLabel[$tipeUjian] ?? $tipeUjian
                ),
            ];
        }

        return [
            'valid'          => empty($rekomendasi),
            'tipe_ujian'     => $tipeUjian,
            'tipe_label'     => $this->tipeLabel[$tipeUjian] ?? $tipeUjian,
            'total_item'     => $total,
            'distribusi'     => $aktual,
            'persentase'     => $persen,
            'target'         => $target,
            'deviasi'        => $deviasi,
            'skor_hots'      => round($hotsPct, 1),
            'target_hots'    => $hotsTarget,
            'rekomendasi'    => $rekomendasi,
            'ringkasan'      => $this->ringkasan($persen, $hotsPct),
        ];
    }

    /**
     * Validasi distribusi langsung dari ID kartu soal.
     */
    public function validasiByKartu(int $kartuId, string $tipeUjian = 'uts'): array
    {
        $db    = \Config\Database::connect();
        $items = $db->table('cbt_kartu_soal_item')
            ->select('level_kognitif')
            ->where('kartu_id', $kartuId)
            ->get()->getResult();

        return $this->validasi($items, $tipeUjian);
    }

    /**
     * Validasi agregat semua kartu soal aktif dalam satu periode (sekolah-wide).
     */
    public function validasiPeriode(int $tpId, int $smtId, string $tipeUjian = 'uts'): array
    {
        $db    = \Config\Database::connect();
        $items = $db->table('cbt_kartu_soal_item i')
            ->select('i.level_kognitif')
            ->join('cbt_kartu_soal k', 'k.id_kartu = i.kartu_id')
            ->where('k.tp_id', $tpId)
            ->where('k.smt_id', $smtId)
            ->where('k.deleted_at', null)
            ->where('i.level_kognitif IS NOT NULL')
            ->get()->getResult();

        $result             = $this->validasi($items, $tipeUjian);
        $result['scope']    = 'periode';
        $result['tp_id']    = $tpId;
        $result['smt_id']   = $smtId;
        return $result;
    }

    /**
     * Kembalikan semua tipe ujian yang tersedia dengan labelnya.
     */
    public function getTipeUjian(): array
    {
        $out = [];
        foreach ($this->tipeLabel as $key => $label) {
            $out[] = ['value' => $key, 'label' => $label];
        }
        return $out;
    }

    /**
     * Kembalikan target distribusi untuk tipe ujian tertentu.
     */
    public function getTarget(string $tipeUjian = 'uts'): array
    {
        return $this->targets[$tipeUjian] ?? $this->targets['uts'];
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    private function bangunRekomendasi(
        array $persen, array $target, array $aktual, int $total
    ): array {
        $rekomendasi = [];

        foreach ($persen as $level => $pct) {
            $tgt = $target[$level];
            $dev = $pct - $tgt;

            if (abs($dev) <= $this->toleransi) {
                continue;
            }

            $arah     = $dev > 0 ? 'kelebihan' : 'kekurangan';
            $saran    = $dev > 0
                ? "Kurangi soal {$level} atau tambah soal level lain."
                : "Tambahkan " . abs((int) round(($tgt - $pct) * $total / 100)) .
                  " soal {$level} lagi untuk memenuhi target.";

            $rekomendasi[] = [
                'level'    => 'warning',
                'kategori' => $level,
                'aktual'   => $pct,
                'target'   => $tgt,
                'selisih'  => round($dev, 1),
                'status'   => $arah,
                'pesan'    => sprintf(
                    '%s: %s (%.1f%% vs target %.1f%%). %s',
                    $level, ucfirst($arah), $pct, $tgt, $saran
                ),
            ];
        }

        return $rekomendasi;
    }

    private function ringkasan(array $persen, float $hotsPct): string
    {
        $lots = ($persen['C1'] ?? 0) + ($persen['C2'] ?? 0);
        $mots = ($persen['C3'] ?? 0);

        return sprintf(
            'LOTS %.1f%% | MOTS %.1f%% | HOTS %.1f%%',
            $lots, $mots, $hotsPct
        );
    }
}
