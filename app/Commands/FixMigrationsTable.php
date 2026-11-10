<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Memperbaiki tabel `migrations` berskema lama (hanya version/namespace/id/batch)
 * agar kompatibel dengan MigrationRunner CodeIgniter 4.7, yang membutuhkan
 * kolom `class`, `group`, dan `time`.
 *
 * Tanpa perbaikan ini `php spark migrate` gagal dengan
 * "Undefined property: stdClass::$class".
 */
class FixMigrationsTable extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'migrations:fix';
    protected $description = 'Upgrade tabel migrations skema lama ke format CodeIgniter 4 terbaru.';
    protected $usage       = 'migrations:fix [--baseline]';
    protected $arguments   = [];
    protected $options     = [
        '--baseline' => 'Catat semua file migration yang ada sebagai sudah dijalankan (batch 1). Pakai hanya bila skema database sudah sesuai.',
    ];

    public function run(array $params)
    {
        $db    = Database::connect();
        $table = $db->prefixTable('migrations');

        if (! $db->tableExists($table)) {
            CLI::write('Tabel migrations belum ada, dibuat ulang dengan skema CodeIgniter 4.');
            $this->createTable($db, $table);

            return EXIT_SUCCESS;
        }

        $this->upgradeSchema($db, $table);
        $files = $this->scanMigrations();
        $this->backfill($db, $table, $files);

        if (array_key_exists('baseline', $params) || CLI::getOption('baseline')) {
            $this->baseline($db, $table, $files);
        }

        CLI::newLine();
        CLI::write('Tabel migrations siap dipakai. Lanjutkan dengan:');
        CLI::write('  php spark migrate:status');
        CLI::write('  php spark migrate');

        return EXIT_SUCCESS;
    }

    private function createTable($db, string $table): void
    {
        $db->query(
            "CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `version` VARCHAR(255) NOT NULL,
                `class` VARCHAR(255) NOT NULL,
                `group` VARCHAR(255) NOT NULL,
                `namespace` VARCHAR(255) NOT NULL,
                `time` INT(11) NOT NULL,
                `batch` INT(11) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    private function upgradeSchema($db, string $table): void
    {
        $added = [];

        if (! $db->fieldExists('class', $table)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `class` VARCHAR(255) NOT NULL DEFAULT '' AFTER `version`");
            $added[] = 'class';
        }
        if (! $db->fieldExists('group', $table)) {
            $after = $db->fieldExists('class', $table) ? 'AFTER `class`' : 'AFTER `version`';
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `group` VARCHAR(255) NOT NULL DEFAULT 'default' {$after}");
            $added[] = 'group';
        }
        if (! $db->fieldExists('time', $table)) {
            $after = $db->fieldExists('namespace', $table) ? 'AFTER `namespace`' : '';
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `time` INT(11) NOT NULL DEFAULT 0 {$after}");
            $added[] = 'time';
        }

        // Normalisasi kolom lama agar tidak menolak nilai CI4.
        $db->query("ALTER TABLE `{$table}` MODIFY `version` VARCHAR(255) NOT NULL");

        if ($db->fieldExists('namespace', $table)) {
            $db->query("ALTER TABLE `{$table}` MODIFY `namespace` VARCHAR(255) NOT NULL DEFAULT 'App'");
        }
        if ($db->fieldExists('batch', $table)) {
            $db->query("ALTER TABLE `{$table}` MODIFY `batch` INT(11) UNSIGNED NOT NULL DEFAULT 0");
        }

        CLI::write($added === []
            ? 'Skema tabel migrations sudah lengkap (class, group, time tersedia).'
            : 'Kolom ditambahkan: ' . implode(', ', $added));
    }

    /**
     * Membaca seluruh file migration di app/Database/Migrations.
     *
     * @return array<string, array{version: string, class: string, short: string, digits: string, time: int}>
     *         di-key "<versi numerik>|<nama class>" agar file dengan nomor sama tetap terpisah.
     */
    private function scanMigrations(): array
    {
        $path  = APPPATH . 'Database/Migrations/';
        $regex = '/\A(\d{4}[_-]?\d{2}[_-]?\d{2}[_-]?\d{6})_(\w+)\z/';
        $files = glob($path . '*.php');
        sort($files);

        $map = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (preg_match($regex, $name, $m) !== 1) {
                continue;
            }

            $class = $m[2];
            $code  = (string) file_get_contents($file);
            if (preg_match('/class\s+(\w+)\s+extends\s+Migration/', $code, $c) === 1) {
                $class = $c[1];
            }

            $digits = preg_replace('/\D/', '', $m[1]);
            $map[$digits . '|' . $class] = [
                'version' => $m[1],
                'class'   => 'App\\Database\\Migrations\\' . $class,
                'short'   => $class,
                'digits'  => $digits,
                'time'    => (int) (filemtime($file) ?: time()),
            ];
        }

        return $map;
    }

    /**
     * Menyesuaikan baris lama: versi dinormalkan ke format file, class/group/time diisi.
     */
    private function backfill($db, string $table, array $files): void
    {
        $rows    = $db->table($table)->get()->getResultArray();
        $updated = 0;

        $byDigits = [];
        foreach ($files as $key => $file) {
            $byDigits[$file['digits']][] = $key;
        }

        foreach ($rows as $row) {
            $digits = preg_replace('/\D/', '', (string) $row['version']);
            $short  = (string) ($row['class'] ?? '');
            $short  = ($pos = strrpos($short, '\\')) !== false ? substr($short, $pos + 1) : $short;

            $key = $digits . '|' . $short;
            if (! isset($files[$key])) {
                // Baris lama tanpa class: pakai satu-satunya file dengan nomor tersebut.
                $candidates = $byDigits[$digits] ?? [];
                if (count($candidates) !== 1) {
                    continue;
                }
                $key = $candidates[0];
            }

            $target = $files[$key];
            $change = [];

            if ((string) $row['version'] !== $target['version']) {
                $change['version'] = $target['version'];
            }
            if (($row['class'] ?? '') !== $target['class']) {
                $change['class'] = $target['class'];
            }
            if (($row['group'] ?? '') === '') {
                $change['group'] = 'default';
            }
            if (($row['namespace'] ?? '') === '') {
                $change['namespace'] = 'App';
            }
            if ((int) ($row['time'] ?? 0) === 0) {
                $change['time'] = $target['time'];
            }

            if ($change === []) {
                continue;
            }

            $db->table($table)->where('id', (int) $row['id'])->update($change);
            $updated++;
        }

        CLI::write('Baris history lama disesuaikan: ' . $updated);
    }

    /**
     * Menandai semua migration yang ada sebagai sudah dijalankan (batch 1)
     * bila belum tercatat. Berguna untuk database lama yang skemanya dibuat
     * manual dari dump SQL, sehingga `migrate` tidak menjalankan ulang semuanya.
     */
    private function baseline($db, string $table, array $files): void
    {
        $existing = [];
        foreach ($db->table($table)->get()->getResultArray() as $row) {
            $digits = preg_replace('/\D/', '', (string) $row['version']);
            $short  = (string) ($row['class'] ?? '');
            $short  = ($pos = strrpos($short, '\\')) !== false ? substr($short, $pos + 1) : $short;
            $existing[$digits . '|' . $short] = true;
        }

        $inserted = 0;
        foreach ($files as $key => $file) {
            if (isset($existing[$key])) {
                continue;
            }

            $db->table($table)->insert([
                'version'   => $file['version'],
                'class'     => $file['class'],
                'group'     => 'default',
                'namespace' => 'App',
                'time'      => $file['time'],
                'batch'     => 1,
            ]);
            $inserted++;
        }

        CLI::write('Baseline: ' . $inserted . ' migration ditandai sudah dijalankan.');
    }
}
