<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: AddRemedialLimitToCbtJadwal
 *
 * Menambahkan kolom untuk mengatur fitur remedial pada jadwal ujian:
 * - enable_remedial : aktif/nonaktif fitur remedial (1/0)
 * - max_remedial    : batas maksimal percobaan remedial (INT, 0 = unlimited, default 2)
 *
 * Dibuat idempoten (cek fieldExists) agar aman dijalankan ulang
 * maupun bila kolom sudah dibuat secara dinamis oleh runtime.
 */
class AddRemedialLimitToCbtJadwal extends Migration
{
    public function up(): void
    {
        if (!$this->db->fieldExists('enable_remedial', 'cbt_jadwal')) {
            $this->forge->addColumn('cbt_jadwal', [
                'enable_remedial' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 1,
                    'default'    => '1',
                    'null'       => true,
                ],
            ]);
        }

        if (!$this->db->fieldExists('max_remedial', 'cbt_jadwal')) {
            $this->forge->addColumn('cbt_jadwal', [
                'max_remedial' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 2,
                    'null'       => false,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('max_remedial', 'cbt_jadwal')) {
            $this->forge->dropColumn('cbt_jadwal', 'max_remedial');
        }

        if ($this->db->fieldExists('enable_remedial', 'cbt_jadwal')) {
            $this->forge->dropColumn('cbt_jadwal', 'enable_remedial');
        }
    }
}
