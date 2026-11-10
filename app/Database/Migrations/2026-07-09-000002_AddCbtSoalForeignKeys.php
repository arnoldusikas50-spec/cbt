<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tambah FOREIGN KEY constraint pada relasi inti modul soal untuk menjamin
 * integritas data di level database (sebelumnya tidak ada FK satupun):
 *  - cbt_soal.bank_id            -> cbt_bank_soal.id_bank        (CASCADE)
 *  - cbt_kartu_soal_item.kartu_id -> cbt_kartu_soal.id_kartu     (CASCADE) [jika belum]
 *  - cbt_jawaban.id_soal         -> cbt_soal.id_soal             (CASCADE)
 *  - cbt_kartu_soal_item.soal_id -> cbt_soal.id_soal             (SET NULL)
 *
 * CATATAN TKA: FK `cbt_jawaban.id_jadwal -> cbt_jadwal.id_jadwal` sengaja
 * TIDAK dibuat karena Tryout TKA memakai id_jadwal VIRTUAL (id_paket + 1_000_000)
 * yang tidak ada di tabel cbt_jadwal. Menambahkan FK itu akan menolak insert
 * jawaban TKA & cascade-delete data TKA saat jadwal reguler dihapus.
 *
 * Migration idempoten: melewati constraint yang sudah ada.
 */
class AddCbtSoalForeignKeys extends Migration
{
    public function up()
    {
        $db = $this->db;
        $dbName = $db->getDatabase();

        // Pastikan tabel engine InnoDB (FK butuh InnoDB)
        $tables = ['cbt_soal', 'cbt_bank_soal', 'cbt_kartu_soal_item', 'cbt_kartu_soal', 'cbt_jawaban', 'cbt_jadwal'];
        foreach ($tables as $t) {
            if ($db->tableExists($t)) {
                $row = $db->query("SHOW TABLE STATUS WHERE Name = ?", [$t])->getRow();
                if ($row && strtolower($row->Engine ?? '') !== 'innodb') {
                    $db->query("ALTER TABLE `{$t}` ENGINE=InnoDB");
                }
            }
        }

        $fks = [
            ['cbt_soal', 'fk_soal_bank', 'bank_id', 'cbt_bank_soal', 'id_bank', 'CASCADE', 'CASCADE'],
            ['cbt_kartu_soal_item', 'fk_item_kartu_soal', 'kartu_id', 'cbt_kartu_soal', 'id_kartu', 'CASCADE', 'CASCADE'],
            ['cbt_kartu_soal_item', 'fk_item_soal', 'soal_id', 'cbt_soal', 'id_soal', 'SET NULL', 'SET NULL'],
            ['cbt_jawaban', 'fk_jawaban_soal', 'id_soal', 'cbt_soal', 'id_soal', 'CASCADE', 'CASCADE'],
            ['cbt_bank_soal', 'fk_bank_jenis', 'bank_jenis_id', 'cbt_jenis', 'id_jenis', 'SET NULL', 'CASCADE'],
            ['cbt_jadwal', 'fk_jadwal_jenis', 'id_jenis', 'cbt_jenis', 'id_jenis', 'SET NULL', 'CASCADE'],
        ];

        foreach ($fks as [$table, $name, $col, $refTable, $refCol, $onDel, $onUpd]) {
            if (!$db->tableExists($table) || !$db->tableExists($refTable)) {
                continue;
            }
            if ($this->fkExists($dbName, $table, $name)) {
                continue;
            }
            // Bersihkan orphan & nilai 0 sebelum menambah constraint.
            // PERHATIAN TKA: jangan sentuh baris cbt_jawaban ber-id_jadwal virtual TKA (>= 1_000_000)
            if ($table === 'cbt_jawaban' && $col === 'id_jadwal') {
                continue;
            }
            $db->query(
                "UPDATE `{$table}` t LEFT JOIN `{$refTable}` r ON t.`{$col}` = r.`{$refCol}`
                 SET t.`{$col}` = NULL WHERE t.`{$col}` IS NOT NULL AND (r.`{$refCol}` IS NULL OR t.`{$col}` = 0)"
            );
            // Ubah NOT NULL DEFAULT 0 jadi NULL agar FK bisa diterima
            if (!str_starts_with($name, 'fk_jawaban') && !str_starts_with($name, 'fk_soal_bank')) {
                $db->query("ALTER TABLE `{$table}` MODIFY COLUMN `{$col}` int(11) DEFAULT NULL");
            }
            try {
                $db->query(
                    "ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}`
                     FOREIGN KEY (`{$col}`) REFERENCES `{$refTable}`(`{$refCol}`)
                     ON DELETE {$onDel} ON UPDATE {$onUpd}"
                );
            } catch (\Throwable $e) {
                log_message('error', "[AddCbtSoalForeignKeys] gagal menambah {$name}: " . $e->getMessage());
            }
        }
    }

    public function down()
    {
        $fks = [
            ['cbt_soal', 'fk_soal_bank'],
            ['cbt_kartu_soal_item', 'fk_item_kartu_soal'],
            ['cbt_kartu_soal_item', 'fk_item_soal'],
            ['cbt_jawaban', 'fk_jawaban_soal'],
            ['cbt_bank_soal', 'fk_bank_jenis'],
            ['cbt_jadwal', 'fk_jadwal_jenis'],
        ];
        foreach ($fks as [$table, $name]) {
            try {
                $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
            } catch (\Throwable $e) {
                // abaikan jika tidak ada
            }
        }
    }

    private function fkExists(string $dbName, string $table, string $name): bool
    {
        $row = $this->db->table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', $dbName)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $name)
            ->get()->getRow();
        return $row !== null;
    }
}
