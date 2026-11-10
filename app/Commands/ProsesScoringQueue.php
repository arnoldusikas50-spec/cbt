<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\CbtService;

/**
 * Command: ProsesScoringQueue
 *
 * Konsumen (worker) untuk tabel cbt_scoring_queue. Dipanggil lewat cron
 * rutin (misal tiap 1 menit) dan memproses batch kecil sehingga 1000 siswa
 * yang submit bersamaan tidak memicu 1000 proses PHP CLI sekaligus.
 *
 * Usage:
 *   php spark cbt:proses_scoring_queue                # default: 50 item, 3 attempts
 *   php spark cbt:proses_scoring_queue --limit=100 --max-attempts=5
 */
class ProsesScoringQueue extends BaseCommand
{
    protected $group       = 'CBT';
    protected $name        = 'cbt:proses_scoring_queue';
    protected $description = 'Proses antrian scoring CBT (konsumen cron, batch kecil)';
    protected $usage       = 'cbt:proses_scoring_queue [--limit=50] [--max-attempts=3]';
    protected $options     = [
        '--limit'        => 'Jumlah item maks per run (default 50)',
        '--max-attempts' => 'Maksimal percobaan sebelum gagal permanen (default 3)',
    ];

    public function run(array $params): void
    {
        $limit       = max(1, min((int) ($params['limit'] ?? 50), 500));
        $maxAttempts = max(1, (int) ($params['max-attempts'] ?? 3));

        $service = new CbtService();

        $result = $service->processScoringQueue($limit, $maxAttempts);

        CLI::write(sprintf(
            'Proses antrian selesai: %d ok, %d gagal, %d skip (limit=%d, max-attempts=%d).',
            $result['ok'],
            $result['failed'],
            $result['skipped'],
            $limit,
            $maxAttempts
        ), $result['failed'] > 0 ? 'yellow' : 'green');
    }
}