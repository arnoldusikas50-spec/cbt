<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: CreateCbtLogAktivitas
 *
 * Mencatat aktivitas siswa selama ujian berlangsung untuk keperluan:
 * - Audit kecurangan (pindah tab, copy-paste, keluar fullscreen)
 * - Analisis soal (waktu per soal)
 * - Forensik jika terjadi masalah teknis
 *
 * Didesain ringan: INSERT-only, tidak ada UPDATE/DELETE.
 * Data lama dapat di-archive/truncate setelah rekap selesai.
 */
class CreateCbtLogAktivitas extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'   => ['type' => 'INT',    'constraint' => 11],
            'id_jadwal'  => ['type' => 'INT',    'constraint' => 11],
            'id_soal'    => ['type' => 'INT',    'constraint' => 11, 'null' => true, 'default' => null],
            // aksi: buka_soal | simpan_jawaban | ragu | tab_switch | blur |
            //        fullscreen_exit | copy_paste | selesai | paksa_selesai | mulai
            'aksi'       => ['type' => 'VARCHAR', 'constraint' => 32],
            'detail'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45,  'null' => true, 'default' => null],
            'waktu'      => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);
        // Index utama: cari log per siswa+jadwal dalam waktu ujian
        $this->forge->addKey(['id_siswa', 'id_jadwal', 'waktu']);
        // Index untuk analisis per aksi (audit pelanggaran)
        $this->forge->addKey(['id_jadwal', 'aksi', 'waktu']);

        $this->forge->createTable('cbt_log_aktivitas', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('cbt_log_aktivitas', true);
    }
}
