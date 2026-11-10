<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\ItemAnalysisService;
use App\Services\DuplicateDetectorService;

/**
 * Command: QualityMaintenance
 *
 * Tugas pemeliharaan kualitas bank soal yang dijalankan via cron job.
 * Direkomendasikan dijadwalkan harian di luar jam ujian (misalnya 01:00 WIB).
 *
 * Usage:
 *   php spark cbt:quality_maintenance            — jalankan semua tugas
 *   php spark cbt:quality_maintenance --rehash   — hanya rehash fingerprint
 *   php spark cbt:quality_maintenance --report   — hanya cetak laporan duplikasi
 *
 * Crontab (contoh):
 *   0 1 * * * cd /path/to/app && php spark cbt:quality_maintenance >> /var/log/cbt_quality.log 2>&1
 */
class QualityMaintenance extends BaseCommand
{
    protected $group       = 'CBT';
    protected $name        = 'cbt:quality_maintenance';
    protected $description = 'Pemeliharaan kualitas bank soal: rehash fingerprint & laporan duplikasi';
    protected $usage       = 'cbt:quality_maintenance [--rehash] [--report] [--limit=N]';
    protected $options     = [
        '--rehash' => 'Hanya jalankan rehash fingerprint soal yang belum punya hash',
        '--report' => 'Hanya cetak laporan duplikasi ke log',
        '--limit'  => 'Batas soal yang di-rehash per batch (default: 1000)',
    ];

    public function run(array $params): void
    {
        $doRehash = array_key_exists('rehash', $params);
        $doReport = array_key_exists('report', $params);
        $limit    = (int) ($params['limit'] ?? 1000);

        // Jika tidak ada flag spesifik, jalankan semua
        $runAll = !$doRehash && !$doReport;

        CLI::write('=== CBT Quality Maintenance ===', 'cyan');
        CLI::write('Waktu: ' . date('Y-m-d H:i:s'), 'white');
        CLI::newLine();

        if ($runAll || $doRehash) {
            $this->doRehash($limit);
        }

        if ($runAll || $doReport) {
            $this->doReport();
        }

        CLI::write('=== Selesai ===', 'cyan');
    }

    // ------------------------------------------------------------------

    private function doRehash(int $limit): void
    {
        CLI::write('► Rehash fingerprint soal…', 'yellow');
        $start = microtime(true);

        try {
            $service = new DuplicateDetectorService();
            $count   = $service->rehashMissing($limit);
            $elapsed = round(microtime(true) - $start, 2);

            if ($count > 0) {
                CLI::write("  ✓ {$count} soal diperbarui hashnya ({$elapsed}s)", 'green');
                log_message('info', "[QualityMaintenance] Rehash: {$count} soal, {$elapsed}s");
            } else {
                CLI::write('  ✓ Semua soal sudah punya hash', 'green');
            }
        } catch (\Throwable $e) {
            CLI::error('  ✗ Error rehash: ' . $e->getMessage());
            log_message('error', '[QualityMaintenance] Rehash error: ' . $e->getMessage());
        }

        CLI::newLine();
    }

    private function doReport(): void
    {
        CLI::write('► Cek duplikasi soal lintas bank…', 'yellow');

        try {
            $db  = \Config\Database::connect();
            $tp  = $db->table('master_tp')->where('active', 1)->get()->getRow();
            $smt = $db->table('master_smt')->where('active', 1)->get()->getRow();

            if (!$tp || !$smt) {
                CLI::write('  Tidak ada periode aktif.', 'white');
                return;
            }

            $service  = new DuplicateDetectorService();
            $laporan  = $service->laporanDuplikasi((int) $tp->id_tp, (int) $smt->id_smt);
            $jumlah   = count($laporan);

            if ($jumlah === 0) {
                CLI::write('  ✓ Tidak ada duplikasi soal.', 'green');
            } else {
                CLI::write("  ⚠ {$jumlah} kelompok duplikasi ditemukan:", 'yellow');
                foreach (array_slice($laporan, 0, 10) as $row) {
                    CLI::write('    • ' . ($row->lokasi ?? '-') . " ({$row->jml_bank} bank)", 'white');
                }
                if ($jumlah > 10) {
                    CLI::write('    … dan ' . ($jumlah - 10) . ' duplikasi lainnya (lihat log)', 'white');
                }
                log_message('warning', "[QualityMaintenance] {$jumlah} duplikasi soal ditemukan di periode {$tp->tahun}.");
            }

            // Cek soal tanpa hash
            $tanpaHash = $db->table('cbt_soal s')
                ->join('cbt_bank_soal b', 'b.id_bank = s.bank_id')
                ->where('b.id_tp', $tp->id_tp)
                ->where('b.id_smt', $smt->id_smt)
                ->where('s.soal_hash IS NULL')
                ->countAllResults();

            if ($tanpaHash > 0) {
                CLI::write("  ⚠ {$tanpaHash} soal belum punya fingerprint hash.", 'yellow');
                CLI::write('    Jalankan: php spark cbt:quality_maintenance --rehash', 'white');
            }

        } catch (\Throwable $e) {
            CLI::error('  ✗ Error report: ' . $e->getMessage());
            log_message('error', '[QualityMaintenance] Report error: ' . $e->getMessage());
        }

        CLI::newLine();
    }
}
