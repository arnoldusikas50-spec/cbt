<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Index tambahan untuk skala besar (pelaksanaan ujian bersamaan ribuan siswa).
 *
 * - cbt_siswa: index komposit status + id_jadwal untuk query realtime pengawas
 *   (siswa "sedang ujian" / "selesai") tanpa full table scan.
 * - cbt_siswa: index (id_siswa, id_jadwal, status) untuk cek sesi aktif siswa.
 * - cbt_jawaban: index (id_jadwal, id_siswa, id_soal) sebagai covering untuk upsert.
 * - cbt_jadwal: index (id_tp, id_smt, status, bank_level) untuk daftar ujian siswa.
 * - cbt_bank_soal: index (id_tp, id_smt, bank_level, status_soal) untuk pencarian bank.
 */
class AddCbtScaleIndexes extends Migration
{
    protected function indexExists(string $table, string $index): bool
    {
        $sql = "SELECT COUNT(1) AS idx FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?";
        $query = $this->db->query($sql, [$table, $index]);
        $row = $query ? $query->getRow() : null;
        return ($row->idx ?? 0) > 0;
    }

    public function up()
    {
        $indexes = [
            'cbt_siswa' => [
                'idx_siswa_status_jadwal' => ['status', 'id_jadwal'],
                'idx_siswa_aktif' => ['id_siswa', 'id_jadwal', 'status'],
            ],
            'cbt_jawaban' => [
                'idx_jawaban_cover' => ['id_jadwal', 'id_siswa', 'id_soal'],
            ],
            'cbt_jadwal' => [
                'idx_jadwal_tpsmt_status_level' => ['id_tp', 'id_smt', 'status', 'bank_level'],
            ],
            'cbt_bank_soal' => [
                'idx_bank_tpsmt_level_status' => ['id_tp', 'id_smt', 'bank_level', 'status_soal'],
            ],
        ];

        foreach ($indexes as $table => $defs) {
            foreach ($defs as $idxName => $columns) {
                if (!$this->indexExists($table, $idxName)) {
                    $colsSql = '`' . implode('`,`', $columns) . '`';
                    $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$idxName}` ({$colsSql})");
                }
            }
        }
    }

    public function down()
    {
        $indexes = [
            'cbt_siswa' => ['idx_siswa_status_jadwal', 'idx_siswa_aktif'],
            'cbt_jawaban' => ['idx_jawaban_cover'],
            'cbt_jadwal' => ['idx_jadwal_tpsmt_status_level'],
            'cbt_bank_soal' => ['idx_bank_tpsmt_level_status'],
        ];

        foreach ($indexes as $table => $idxList) {
            foreach ($idxList as $idxName) {
                if ($this->indexExists($table, $idxName)) {
                    $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$idxName}`");
                }
            }
        }
    }
}
