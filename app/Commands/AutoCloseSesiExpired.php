<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Command: AutoCloseSesiExpired
 *
 * Menutup otomatis sesi ujian yang waktunya sudah habis namun belum
 * di-submit (misalnya browser crash, koneksi putus, atau tab ditutup).
 *
 * Logika:
 *   1. Ambil semua cbt_siswa yang mulai IS NOT NULL, selesai IS NULL
 *   2. Join cbt_jadwal untuk mendapat durasi_ujian dan reset_waktu
 *   3. Jika (NOW - mulai) > (durasi_ujian + reset_waktu + toleransi), tandai selesai
 *   4. Trigger perhitungan nilai async (via HitungNilai command)
 *
 * Dijadwalkan via cron setiap 5 menit:
 *   * /5 * * * * php /path/to/spark cbt:auto_close_expired >> /dev/null 2>&1
 *
 * Atau dipanggil via HTTP endpoint Cron controller (untuk shared hosting).
 *
 * Usage:
 *   php spark cbt:auto_close_expired [--dry-run] [--toleransi=120]
 *
 * Options:
 *   --dry-run     Tampilkan sesi yang akan ditutup tanpa mengubah DB
 *   --toleransi   Toleransi tambahan dalam detik (default: 120)
 */
class AutoCloseSesiExpired extends BaseCommand
{
    protected $group       = 'CBT';
    protected $name        = 'cbt:auto_close_expired';
    protected $description = 'Tutup otomatis sesi ujian yang waktunya sudah habis';
    protected $usage       = 'cbt:auto_close_expired [--dry-run] [--toleransi=<detik>]';
    protected $arguments   = [];
    protected $options     = [
        '--dry-run'    => 'Tampilkan sesi yang akan ditutup tanpa mengubah DB',
        '--toleransi'  => 'Toleransi waktu tambahan dalam detik (default: 120)',
    ];

    public function run(array $params): void
    {
        $dryRun    = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');
        $toleransi = (int) (CLI::getOption('toleransi') ?? $params['toleransi'] ?? 120);

        CLI::write('🔍 CBT Auto Close Expired Sessions', 'cyan');
        CLI::write('Toleransi: ' . $toleransi . ' detik' . ($dryRun ? ' [DRY-RUN]' : ''), 'yellow');
        CLI::newLine();

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Ambil semua sesi aktif yang belum selesai beserta data durasi jadwal
        // reset_waktu adalah menit tambahan yang diberikan pengawas
        $sesiAktif = $db->query(
            "SELECT
                cs.id_cbt_siswa,
                cs.id_siswa,
                cs.id_jadwal,
                cs.mulai,
                cs.reset_waktu,
                j.durasi_ujian
             FROM cbt_siswa cs
             JOIN cbt_jadwal j ON j.id_jadwal = cs.id_jadwal
             WHERE cs.mulai    IS NOT NULL
               AND cs.selesai  IS NULL
               AND cs.status  != '9'
             ORDER BY cs.mulai ASC"
        )->getResult();

        if (empty($sesiAktif)) {
            CLI::write('✓ Tidak ada sesi aktif yang perlu diperiksa.', 'green');
            return;
        }

        CLI::write('Memeriksa ' . count($sesiAktif) . ' sesi aktif...', 'white');

        $nowTs    = time();
        $ditutup  = 0;
        $skipped  = 0;

        foreach ($sesiAktif as $sesi) {
            $durasiDetik  = (int) $sesi->durasi_ujian * 60;
            $extraDetik   = (int) ($sesi->reset_waktu ?? 0) * 60;
            $batasDetik   = $durasiDetik + $extraDetik + $toleransi;

            $mulaiTs      = strtotime($sesi->mulai);
            $elapsedDetik = $nowTs - $mulaiTs;

            if ($elapsedDetik <= $batasDetik) {
                $skipped++;
                continue;
            }

            // Sesi ini sudah expired
            $lebihDetik = $elapsedDetik - $batasDetik;
            CLI::write(
                sprintf(
                    '  → Sesi #%d (siswa=%d, jadwal=%d) expired sejak %d detik lalu',
                    $sesi->id_cbt_siswa, $sesi->id_siswa, $sesi->id_jadwal, $lebihDetik
                ),
                'yellow'
            );

            if ($dryRun) {
                $ditutup++;
                continue;
            }

            // Hitung lama_ujian
            $lamaUjian = gmdate('H:i:s', min($elapsedDetik, $durasiDetik + $extraDetik));

            // Tandai selesai
            $db->table('cbt_siswa')
                ->where('id_cbt_siswa', $sesi->id_cbt_siswa)
                ->update([
                    'selesai'    => $now,
                    'lama_ujian' => $lamaUjian,
                    'status'     => '2',
                ]);

            // Trigger hitung nilai async
            $this->triggerHitungNilai((int) $sesi->id_siswa, (int) $sesi->id_jadwal);

            $ditutup++;
        }

        CLI::newLine();
        CLI::write(
            sprintf(
                '%s Selesai: %d sesi ditutup, %d sesi masih aktif.',
                $dryRun ? '[DRY-RUN]' : '✓',
                $ditutup,
                $skipped
            ),
            $ditutup > 0 ? 'green' : 'white'
        );

        log_message(
            'info',
            sprintf(
                '[AutoCloseSesiExpired] %s ditutup=%d, aktif=%d, toleransi=%ds',
                $dryRun ? 'DRY-RUN' : 'RUN',
                $ditutup,
                $skipped,
                $toleransi
            )
        );
    }

    /**
     * Enqueue scoring (id_siswa, id_jadwal) ke cbt_scoring_queue.
     * Menggantikan pola exec('cbt:hitung_nilai ...') yang me-spawn 1 proses
     * PHP CLI per siswa — bahaya saat ratusan sesi ditutup sekaligus.
     * Konsumen cron (cbt:proses_scoring_queue) memproses batch-nya.
     * Fallback ke hitung sinkron bila enqueue gagal.
     */
    private function triggerHitungNilai(int $idSiswa, int $idJadwal): void
    {
        try {
            $service = new \App\Services\CbtService();
            if (!$service->enqueueScoring($idSiswa, $idJadwal)) {
                $service->calculateScore($idSiswa, $idJadwal);
            }
        } catch (\Throwable $e) {
            log_message('error', "[AutoClose] enqueue hitung nilai gagal: " . $e->getMessage());
        }
    }
}
