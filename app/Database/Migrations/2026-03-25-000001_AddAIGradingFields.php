<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAIGradingFields extends Migration
{
    public function up()
    {
        // 1. Add AI columns to cbt_jawaban_siswa (for per-question AI result)
        $fields = [
            'ai_score' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'after'      => 'nilai'
            ],
            'ai_feedback' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'ai_score'
            ],
            'ai_status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0, // 0: No AI, 1: Pending, 2: Success, 3: Failed
                'after'      => 'ai_feedback'
            ],
        ];
        $this->forge->addColumn('cbt_jawaban_siswa', $fields);

        // 2. Create AI Grading History table
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
                'null'       => true, // Null means graded whole schedule
            ],
            'graded_by' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20, // 'PENDING', 'SUCCESS', 'FAILED'
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

    public function down()
    {
        $this->forge->dropTable('cbt_ai_grading_history');
        $this->forge->dropColumn('cbt_jawaban_siswa', ['ai_score', 'ai_feedback', 'ai_status']);
    }
}
