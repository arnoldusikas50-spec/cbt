<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel audit trail CBT yang selama ini dipakai CbtAuditModel / Audit
 * controller / Cbtnilai tetapi tidak pernah dibuat di DB (audit nilai
 * gagal diam-diam via try/catch). Dua tabel:
 *  - cbt_nilai_audit  : perubahan nilai (INSERT/UPDATE/DELETE/RECALCULATE/CORRECT/MARK_CORRECTED)
 *  - cbt_jawaban_audit: perubahan jawaban siswa per butir (INSERT/UPDATE)
 */
class CreateAuditTrailTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_audit' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'id_jadwal' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'action' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'old_values' => ['type' => 'TEXT', 'null' => true],
            'new_values' => ['type' => 'TEXT', 'null' => true],
            'changed_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'changed_by_role' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id_audit', true);
        $this->forge->addKey(['id_jadwal', 'id_siswa']);
        $this->forge->createTable('cbt_nilai_audit', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'id_jadwal' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'id_soal' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'action' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'old_jawaban' => ['type' => 'TEXT', 'null' => true],
            'new_jawaban' => ['type' => 'TEXT', 'null' => true],
            'sync_method' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'changed_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_jadwal', 'id_siswa']);
        $this->forge->createTable('cbt_jawaban_audit', true);
    }

    public function down()
    {
        $this->forge->dropTable('cbt_jawaban_audit', true);
        $this->forge->dropTable('cbt_nilai_audit', true);
    }
}
