<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk membuat tabel audit trail cbt_nilai
 * Menyimpan riwayat perubahan nilai untuk keperluan audit dan traceability
 */
class CreateCbtNilaiAuditTable extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();
        
        // Tabel Audit Trail untuk cbt_nilai
        $forge->addField([
            'id_audit' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'id_jadwal' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'INSERT, UPDATE, DELETE, RECALCULATE, CORRECT'
            ],
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Nilai lama sebelum perubahan'
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Nilai baru setelah perubahan'
            ],
            'changed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'ID user yang melakukan perubahan'
            ],
            'changed_by_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Role user (admin, guru, panitia)'
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP address pengguna'
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'User agent string'
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Alasan perubahan (untuk koreksi manual)'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')
            ]
        ]);
        
        $forge->addKey('id_audit', true);
        $forge->addKey(['id_siswa', 'id_jadwal']);
        $forge->addKey('action');
        $forge->addKey('changed_by');
        $forge->addKey('created_at');
        
        $forge->createTable('cbt_nilai_audit', true);
        
        // Tabel Audit untuk cbt_jawaban (jawaban siswa)
        $forge->addField([
            'id_jawaban_audit' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'id_jadwal' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'id_soal' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'old_jawaban' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'new_jawaban' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'sync_method' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => 'direct',
                'comment' => 'direct, queued, offline_sync'
            ],
            'changed_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true
            ]
        ]);
        
        $forge->addKey('id_jawaban_audit', true);
        $forge->addKey(['id_siswa', 'id_jadwal', 'id_soal']);
        $forge->addKey('created_at');
        
        $forge->createTable('cbt_jawaban_audit', true);
        
        // Tabel untuk offline sync queue
        $forge->addField([
            'id_sync' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'id_jadwal' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'sync_data' => [
                'type' => 'JSON',
                'null' => false,
                'comment' => 'Data yang perlu di-sync'
            ],
            'sync_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => 'jawaban, status, duration'
            ],
            'attempt_count' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0
            ],
            'last_attempt' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending',
                'comment' => 'pending, processing, failed, completed'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);
        
        $forge->addKey('id_sync', true);
        $forge->addKey(['id_siswa', 'id_jadwal']);
        $forge->addKey('status');
        $forge->addKey('attempt_count');
        
        $forge->createTable('cbt_sync_queue', true);
    }
    
    public function down()
    {
        $forge = \Config\Database::forge();
        
        $forge->dropTable('cbt_nilai_audit', true);
        $forge->dropTable('cbt_jawaban_audit', true);
        $forge->dropTable('cbt_sync_queue', true);
    }
}
