<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pindahkan kolom AI grading dari cbt_jawaban_siswa (sudah di-drop oleh
 * DropCbtJawabanSiswa) ke tabel aktif cbt_jawaban, dan buat ulang tabel
 * cbt_ai_grading_history agar AIAutoGradeService konsisten dengan skema.
 */
class MoveAIGradingToCbtJawaban extends Migration
{
    public function up()
    {
        // 1. Kolom AI di cbt_jawaban (menggantikan lokasi lama di cbt_jawaban_siswa)
        if (!$this->db->fieldExists('ai_score', 'cbt_jawaban')) {
            $this->forge->addColumn('cbt_jawaban', [
                'ai_score' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,2',
                    'null'       => true,
                    'after'      => 'nilai',
                ],
                'ai_feedback' => [
                    'type'       => 'TEXT',
                    'null'       => true,
                    'after'      => 'ai_score',
                ],
                'ai_status' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'ai_feedback',
                ],
            ]);
        }

        // 2. Tabel riwayat grading AI
        if (!$this->db->tableExists('cbt_ai_grading_history')) {
            $this->forge->addField([
                'id_ai_history' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'id_jadwal' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'id_siswa' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'graded_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'total_soal' => [
                    'type'       => 'INT',
                    'constraint' => 5,
                    'default'    => 0,
                ],
                'success_count' => [
                    'type'       => 'INT',
                    'constraint' => 5,
                    'default'    => 0,
                ],
                'log' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_ai_history', true);
            $this->forge->addKey(['id_jadwal', 'id_siswa']);
            $this->forge->createTable('cbt_ai_grading_history');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('cbt_ai_grading_history')) {
            $this->forge->dropTable('cbt_ai_grading_history');
        }
        if ($this->db->fieldExists('ai_score', 'cbt_jawaban')) {
            $this->forge->dropColumn('cbt_jawaban', ['ai_score', 'ai_feedback', 'ai_status']);
        }
    }
}
