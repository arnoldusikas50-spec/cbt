<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * CLI utility untuk menerapkan migrasi & memverifikasi integritas relasi
 * modul soal. Jalankan:
 *   php public/index.php cbt:dbfix
 *   php public/index.php cbt:dbfix verify
 */
class CbtDbFix extends Controller
{
    public function index()
    {
        if (!is_cli()) {
            return redirect()->to('/');
        }

        echo ">> Menjalankan migrasi (latest)...\n";
        $migrate = \Config\Services::migrations();
        try {
            $migrate->latest();
            echo "   Migrasi selesai.\n";
        } catch (\Throwable $e) {
            echo "   GAGAL migrasi: " . $e->getMessage() . "\n";
            echo "   " . $e->getFile() . ':' . $e->getLine() . "\n";
            while ($e = $e->getPrevious()) {
                echo "   prev: " . $e->getMessage() . "\n";
            }
            return;
        }

        $this->verify();
    }

    public function verify()
    {
        if (!is_cli()) {
            return redirect()->to('/');
        }

        echo ">> Memverifikasi FOREIGN KEY modul soal...\n";
        $db = \Config\Database::connect();
        $checks = [
            ['cbt_soal', 'bank_id', 'cbt_bank_soal', 'id_bank'],
            ['cbt_kartu_soal_item', 'kartu_id', 'cbt_kartu_soal', 'id_kartu'],
            ['cbt_kartu_soal_item', 'soal_id', 'cbt_soal', 'id_soal'],
            ['cbt_jawaban', 'id_soal', 'cbt_soal', 'id_soal'],
            ['cbt_jawaban', 'id_jadwal', 'cbt_jadwal', 'id_jadwal'],
            ['cbt_bank_soal', 'bank_jenis_id', 'cbt_jenis', 'id_jenis'],
            ['cbt_jadwal', 'id_jenis', 'cbt_jenis', 'id_jenis'],
        ];

        foreach ($checks as [$tbl, $col, $ref, $refCol]) {
            if (!$db->tableExists($tbl) || !$db->tableExists($ref)) {
                echo "   LEWAT: tabel $tbl / $ref tidak ada\n";
                continue;
            }
            // Cek orphan
            $orphan = $db->query(
                "SELECT COUNT(*) AS c FROM `{$tbl}` t
                 LEFT JOIN `{$ref}` r ON t.`{$col}` = r.`{$refCol}`
                 WHERE t.`{$col}` IS NOT NULL AND r.`{$refCol}` IS NULL"
            )->getRow()->c;

            // Cek FK ada
            $fk = $db->table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $db->getDatabase())
                ->where('TABLE_NAME', $tbl)
                ->where('COLUMN_NAME', $col)
                ->where('REFERENCED_TABLE_NAME IS NOT NULL')
                ->countAllResults();

            $status = ($orphan == 0) ? 'OK' : "ORPHAN=$orphan";
            $fkStatus = $fk > 0 ? 'FK=Ada' : 'FK=Tidak';
            echo "   $tbl.$col -> $ref.$refCol : $status | $fkStatus\n";
        }
        echo ">> Selesai.\n";
    }
}
