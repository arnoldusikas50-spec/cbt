<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Optimasi Index & UNIQUE constraint tabel CBT ujian siswa
 * 
 * Masalah yang diperbaiki:
 * 1. cbt_jawaban tidak punya UNIQUE key → duplikat jawaban saat concurrent save
 * 2. Index (id_siswa, id_jadwal) tidak cover id_soal → full scan tiap getSoal
 * 3. cbt_siswa tidak ada index pada (id_siswa, id_jadwal) → lambat saat cek status
 * 4. cbt_soal tidak ada index pada (bank_id, tampilkan) → getSoalCached lambat
 */
class OptimizeCbtJawabanIndexes extends Migration
{
    public function up(): void
    {
        // ─── cbt_jawaban ────────────────────────────────────────────────────────
        // Tambah UNIQUE KEY: mencegah duplikat + memungkinkan ON DUPLICATE KEY UPDATE
        $this->addIndexSafe(
            'cbt_jawaban',
            'uk_jawaban_siswa_jadwal_soal',
            'ALTER TABLE `cbt_jawaban` ADD UNIQUE KEY `uk_jawaban_siswa_jadwal_soal` (id_siswa, id_jadwal, id_soal)'
        );

        // Index covering untuk query "semua jawaban siswa dalam satu jadwal"
        $this->addIndexSafe(
            'cbt_jawaban',
            'idx_jawaban_jadwal_siswa',
            'ALTER TABLE `cbt_jawaban` ADD INDEX `idx_jawaban_jadwal_siswa` (id_jadwal, id_siswa)'
        );

        // ─── cbt_siswa ──────────────────────────────────────────────────────────
        // UNIQUE untuk cegah double session siswa-jadwal
        $this->addIndexSafe(
            'cbt_siswa',
            'uk_cbtsiswa_siswa_jadwal',
            'ALTER TABLE `cbt_siswa` ADD UNIQUE KEY `uk_cbtsiswa_siswa_jadwal` (id_siswa, id_jadwal)'
        );

        // Index untuk monitoring dashboard: cari semua siswa yang sedang ujian di jadwal X
        $this->addIndexSafe(
            'cbt_siswa',
            'idx_cbtsiswa_jadwal_status',
            'ALTER TABLE `cbt_siswa` ADD INDEX `idx_cbtsiswa_jadwal_status` (id_jadwal, status, selesai)'
        );

        // ─── cbt_soal ───────────────────────────────────────────────────────────
        // Covering index untuk getSoalCached: WHERE bank_id=X AND tampilkan=1 ORDER BY nomor_soal
        $this->addIndexSafe(
            'cbt_soal',
            'idx_soal_bank_tampil_nomor',
            'ALTER TABLE `cbt_soal` ADD INDEX `idx_soal_bank_tampil_nomor` (bank_id, tampilkan, nomor_soal)'
        );

        // ─── cbt_nilai ──────────────────────────────────────────────────────────
        // UNIQUE key untuk ON DUPLICATE KEY UPDATE di hitungNilai
        $this->addIndexSafe(
            'cbt_nilai',
            'uk_nilai_siswa_jadwal',
            'ALTER TABLE `cbt_nilai` ADD UNIQUE KEY `uk_nilai_siswa_jadwal` (id_siswa, id_jadwal)'
        );
    }

    public function down(): void
    {
        $drops = [
            'cbt_jawaban'  => ['uk_jawaban_siswa_jadwal_soal', 'idx_jawaban_jadwal_siswa'],
            'cbt_siswa'    => ['uk_cbtsiswa_siswa_jadwal', 'idx_cbtsiswa_jadwal_status'],
            'cbt_soal'     => ['idx_soal_bank_tampil_nomor'],
            'cbt_nilai'    => ['uk_nilai_siswa_jadwal'],
        ];

        foreach ($drops as $table => $indexes) {
            foreach ($indexes as $index) {
                try {
                    $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
                } catch (\Throwable $e) {
                    // Abaikan jika index tidak ada
                }
            }
        }
    }

    /**
     * Tambahkan index hanya jika belum ada (idempotent)
     */
    private function addIndexSafe(string $table, string $indexName, string $sql): void
    {
        try {
            // Cek apakah index sudah ada di information_schema
            $exists = $this->db->query(
                "SELECT COUNT(*) as cnt 
                 FROM information_schema.STATISTICS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = ? 
                   AND INDEX_NAME = ?",
                [$table, $indexName]
            )->getRow();

            if (!$exists || (int) $exists->cnt === 0) {
                $this->db->query($sql);
                log_message('info', "Migration: Index {$indexName} ditambahkan ke {$table}");
            } else {
                log_message('debug', "Migration: Index {$indexName} sudah ada di {$table}, dilewati");
            }
        } catch (\Throwable $e) {
            // Log tapi jangan gagalkan migration — bisa jadi duplicate key conflict
            log_message('warning', "Migration addIndexSafe [{$table}.{$indexName}]: " . $e->getMessage());
        }
    }
}
