<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CbtModel;

/**
 * Command: RecalculateJadwal
 *
 * Menghitung ulang nilai seluruh siswa dalam satu jadwal via CLI.
 * Awalnya dipanggil Cbtnilai::recalculateAll() via exec(), kini controller
 * pakai CbtService::enqueueScoring(force=true) + konsumen antrian. Command ini
 * tetap ada untuk re-run manual via CLI.
 *
 * Usage:
 *   php spark cbt:recalculate_jadwal <id_jadwal>
 */
class RecalculateJadwal extends BaseCommand
{
    protected $group       = 'CBT';
    protected $name        = 'cbt:recalculate_jadwal';
    protected $description = 'Hitung ulang nilai semua siswa dalam satu jadwal (background)';
    protected $usage       = 'cbt:recalculate_jadwal <id_jadwal>';
    protected $arguments   = [
        'id_jadwal' => 'ID jadwal ujian',
    ];

    public function run(array $params): void
    {
        $idJadwal = (int) ($params[0] ?? 0);

        if ($idJadwal <= 0) {
            CLI::error('Usage: php spark cbt:recalculate_jadwal <id_jadwal>');
            return;
        }

        CLI::write("Recalculate jadwal {$idJadwal}...", 'yellow');

        $db = \Config\Database::connect();

        $students = $db->table('cbt_siswa')
            ->select('id_siswa')
            ->where('id_jadwal', $idJadwal)
            ->where('status', '2')
            ->get()->getResultArray();

        if (empty($students)) {
            CLI::write('Tidak ada siswa yang selesai ujian.', 'red');
            return;
        }

        $studentIds   = array_column($students, 'id_siswa');
        $totalSiswa   = count($studentIds);
        $cbtModel     = new CbtModel();
        $successCount = 0;
        $failCount    = 0;
        $chunkSize    = 200;

        CLI::write("Total siswa: {$totalSiswa}. Memproses per chunk {$chunkSize}...", 'cyan');

        foreach (array_chunk($studentIds, $chunkSize) as $i => $chunk) {
            $res           = $cbtModel->batchHitungNilai($chunk, $idJadwal);
            $successCount += $res['success'];
            $failCount    += $res['failed'];
            CLI::write(sprintf(
                '  Chunk %d: ✓ %d sukses, ✗ %d gagal',
                $i + 1, $res['success'], $res['failed']
            ), $res['failed'] > 0 ? 'yellow' : 'green');
        }

        $status = $failCount === 0 ? 'green' : 'yellow';
        CLI::write("Selesai: {$successCount} sukses, {$failCount} gagal dari {$totalSiswa} siswa.", $status);
        log_message('info', "RecalculateJadwal: jadwal={$idJadwal}, sukses={$successCount}, gagal={$failCount}");
    }
}
