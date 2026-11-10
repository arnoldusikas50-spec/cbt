<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration to add comprehensive audit trail and multi-assessor support tables
 * for CBT Praktik module
 */
class AddCbtPraktikAuditTrailAndMultiAssessor extends Migration
{
    public function up()
    {
        // 1. Tabel Audit Trail untuk perubahan nilai
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'praktik_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'hasil_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'siswa_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'assessor_id' => [
                'type' => 'INT',
                'null' => true,
                'comment' => 'ID guru yang melakukan penilaian',
            ],
            'tipe_aksi' => [
                'type' => 'ENUM',
                'constraint' => ['create', 'update', 'delete', 'verify', 'approve', 'reject'],
                'default' => 'update',
            ],
            'field_yang_diubah' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'nilai_lama' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'nilai_baru' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'rincian_lama' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON rincian nilai rubrik lama',
            ],
            'rincian_baru' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON rincian nilai rubrik baru',
            ],
            'alasan_perubahan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['praktik_id', 'hasil_id', 'siswa_id']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('cbt_praktik_nilai_audit');

        // 2. Tabel Multi-Assessor Penilaian
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'hasil_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'praktik_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'siswa_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'assessor_id' => [
                'type' => 'INT',
                'null' => false,
                'comment' => 'ID guru yang menilai',
            ],
            'nilai_manual' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'rincian_nilai_rubrik' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON {rubrik_id: {nilai: x, catatan: y}}',
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'bobot_penilai' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 100.00,
                'comment' => 'Bobot kontribusi penilai dalam %',
            ],
            'status_penilaian' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'submitted', 'verified', 'rejected'],
                'default' => 'draft',
            ],
            'dinilai_oleh' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'waktu_penilaian' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['hasil_id', 'assessor_id'], false, true); // Unique
        $this->forge->addKey('praktik_id');
        $this->forge->createTable('cbt_praktik_multi_assessor');

        // 3. Tabel Analisis dan Rekomendasi
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'praktik_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'siswa_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'hasil_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'tipe_analisis' => [
                'type' => 'ENUM',
                'constraint' => ['remedial', 'peningkatan', 'pencapaian'],
                'default' => 'remedial',
            ],
            'skor_ketercapaian' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'indikator_lemah' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array indikator yang perlu ditingkatkan',
            ],
            'rekomendasi_belajar' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON array rekomendasi pembelajaran',
            ],
            'sumber_data' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'auto',
                'comment' => 'auto/manual (AI generated atau manual)',
            ],
            'created_by' => [
                'type' => 'INT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['hasil_id', 'tipe_analisis'], false, true); // Unique per type
        $this->forge->addKey('praktik_id');
        $this->forge->createTable('cbt_praktik_analisis');

        // 4. Tabel Riwayat Aktivitas Siswa (Enhanced Log)
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'praktik_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
            ],
            'siswa_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'hasil_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'tipe_aktivitas' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'metadata' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON additional data',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['praktik_id', 'siswa_id']);
        $this->forge->addKey('created_at');
        $this->forge->createTable('cbt_praktik_aktivitas_siswa');

        // Add foreign key constraints if needed (optional, for data integrity)
        // Note: Uncomment if your database supports FK and you want strict referential integrity
        /*
        $this->db->query('ALTER TABLE cbt_praktik_nilai_audit ADD CONSTRAINT fk_audit_praktik 
            FOREIGN KEY (praktik_id) REFERENCES cbt_praktik(id) ON DELETE CASCADE');
        $this->db->query('ALTER TABLE cbt_praktik_multi_assessor ADD CONSTRAINT fk_multi_assessor_hasil 
            FOREIGN KEY (hasil_id) REFERENCES cbt_praktik_hasil(id) ON DELETE CASCADE');
        $this->db->query('ALTER TABLE cbt_praktik_analisis ADD CONSTRAINT fk_analisis_hasil 
            FOREIGN KEY (hasil_id) REFERENCES cbt_praktik_hasil(id) ON DELETE CASCADE');
        */
    }

    public function down()
    {
        $this->forge->dropTable('cbt_praktik_aktivitas_siswa', true);
        $this->forge->dropTable('cbt_praktik_analisis', true);
        $this->forge->dropTable('cbt_praktik_multi_assessor', true);
        $this->forge->dropTable('cbt_praktik_nilai_audit', true);
    }
}
