<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\ItemAnalysisService;
use App\Services\DuplicateDetectorService;

/**
 * Command: ItemAnalysis
 *
 * Menjalankan item analysis (p, D, tag kesulitan) untuk satu jadwal
 * atau untuk semua bank soal yang belum punya hash duplikasi.
 *
 * Usage:
 *   php spark cbt:item_analysis <id_jadwal>      — analisis satu jadwal
 *   php spark cbt:item_analysis --rehash          — rehash soal tanpa hash
 *   php spark cbt:item_analysis --rehash-bank=5   — rehash bank soal ID=5
 */
class ItemAnalysis extends BaseCommand
{
    protected $group       = 'CBT';
    protected $name        = 'cbt:item_analysis';
    protected $description = 'Hitung item analysis (indeks kesulitan & daya beda) untuk soal CBT';
    protected $usage       = 'cbt:item_analysis [id_jadwal] [--rehash] [--rehash-bank=N]';
    protected $arguments   = [
        'id_jadwal' => '(Opsional) ID jadwal ujian yang akan dianalisis',
    ];
    protected $options = [
        '--rehash'       => 'Isi fingerprint (soal_hash) untuk semua soal yang belum punya hash',
        '--rehash-bank'  => 'Isi fingerprint untuk satu bank soal tertentu (berikan ID bank)',
        '--limit'        => 'Batas soal yang di-rehash sekaligus (default: 500)',
    ];

    public function run(array $params): void
    {
        $idJadwal   = (int) ($params[0] ?? 0);
        $doRehash   = array_key_exists('rehash', $params);
        $rehashBank = isset($params['rehash-bank']) ? (int) $params['rehash-bank'] : 0;
        $limit      = (int) ($params['limit'] ?? 500);

        // --- Mode: rehash satu bank ---
        if ($rehashBank > 0) {
            $this->rehashBank($rehashBank);
            return;
        }

        // --- Mode: rehash semua yang kosong ---
        if ($doRehash) {
            $this->rehashMissing($limit);
            return;
        }

        // --- Mode: analisis satu jadwal ---
        if ($idJadwal > 0) {
            $this->analisisJadwal($idJadwal);
            return;
        }

        CLI::error('Tidak ada parameter yang diberikan.');
        CLI::write($this->usage);
    }

    // ------------------------------------------------------------------

    private function analisisJadwal(int $idJadwal): void
    {
        CLI::write("Memulai item analysis untuk jadwal ID: {$idJadwal}", 'yellow');
        $start = microtime(true);

        try {
            $service = new ItemAnalysisService();
            $result  = $service->hitungMassal($idJadwal);

            if (isset($result['error'])) {
                CLI::error('Error: ' . $result['error']);
                return;
            }

            $elapsed = round(microtime(true) - $start, 2);

            CLI::write("✓ Selesai dalam {$elapsed} detik.", 'green');
            CLI::write("  Diproses : {$result['processed']} soal", 'white');
            CLI::write("  Dilewati : {$result['skipped']} soal (data tidak cukup)", 'white');

            if (!empty($result['results'])) {
                CLI::newLine();
                CLI::write('Ringkasan kualitas:', 'cyan');

                $perlu_revisi = array_filter(
                    $result['results'],
                    fn($r) => $r['d'] < 0.20
                );
                CLI::write('  Soal perlu revisi (D < 0.20): ' . count($perlu_revisi), 'yellow');

                $bagus = array_filter(
                    $result['results'],
                    fn($r) => $r['d'] >= 0.40 && $r['p'] >= 0.30 && $r['p'] <= 0.70
                );
                CLI::write('  Soal berkualitas baik       : ' . count($bagus), 'green');
            }

            log_message('info', "[ItemAnalysis] Jadwal {$idJadwal}: {$result['processed']} diproses.");
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage());
            log_message('error', "[ItemAnalysis] Jadwal {$idJadwal}: " . $e->getMessage());
        }
    }

    private function rehashBank(int $bankId): void
    {
        CLI::write("Rehash soal_hash untuk bank ID: {$bankId}", 'yellow');
        try {
            $service = new DuplicateDetectorService();
            $count   = $service->rehashBank($bankId);
            CLI::write("✓ {$count} soal diperbarui hashnya.", 'green');
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage());
        }
    }

    private function rehashMissing(int $limit): void
    {
        CLI::write("Rehash soal_hash untuk soal yang belum punya hash (limit: {$limit})...", 'yellow');
        try {
            $service = new DuplicateDetectorService();
            $count   = $service->rehashMissing($limit);
            CLI::write("✓ {$count} soal diperbarui hashnya.", 'green');
        } catch (\Throwable $e) {
            CLI::error('Error: ' . $e->getMessage());
        }
    }
}
