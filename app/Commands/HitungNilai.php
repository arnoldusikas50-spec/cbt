<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CbtService;

/**
 * Command: HitungNilai
 *
 * Utilitas manual: hitung nilai satu siswa via CLI. Alur utama selesaiUjian()
 * TIDAK lagi memanggil command ini via exec() — ia pakai CbtService::
 * enqueueScoring() + konsumen antrian (cbt:proses_scoring_queue). Command ini
 * hanya untuk debugging/re-run manual.
 *
 * Usage:
 *   php spark cbt:hitung_nilai <id_siswa> <id_jadwal>
 */
class HitungNilai extends BaseCommand
{
    protected $group       = 'CBT';
    protected $name        = 'cbt:hitung_nilai';
    protected $description = 'Hitung nilai CBT siswa secara background (async)';
    protected $usage       = 'cbt:hitung_nilai <id_siswa> <id_jadwal>';
    protected $arguments   = [
        'id_siswa'  => 'ID siswa',
        'id_jadwal' => 'ID jadwal ujian',
    ];

    public function run(array $params): void
    {
        $idSiswa  = (int) ($params[0] ?? 0);
        $idJadwal = (int) ($params[1] ?? 0);

        if ($idSiswa <= 0 || $idJadwal <= 0) {
            CLI::error('Usage: php spark cbt:hitung_nilai <id_siswa> <id_jadwal>');
            return;
        }

        CLI::write("Menghitung nilai: siswa={$idSiswa}, jadwal={$idJadwal}", 'yellow');

        try {
            $service = new CbtService();
            $result  = $service->calculateScore($idSiswa, $idJadwal);

            if ($result) {
                CLI::write("✓ Nilai berhasil dihitung.", 'green');
                log_message('info', "HitungNilai command: sukses siswa={$idSiswa}, jadwal={$idJadwal}");
            } else {
                CLI::error("✗ Gagal menghitung nilai.");
                log_message('error', "HitungNilai command: gagal siswa={$idSiswa}, jadwal={$idJadwal}");
            }
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage());
            log_message('error', "HitungNilai command error: " . $e->getMessage());
        }
    }
}
