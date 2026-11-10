<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Drop the redundant cbt_jawaban_siswa table.
 * 
 * This table was a write-only copy of evaluation results that duplicated
 * data already available in cbt_jawaban + cbt_nilai. No module ever read
 * from it, so it only added unnecessary DB load during scoring.
 * 
 * Data equivalence:
 * - jawaban       → already in cbt_jawaban.jawaban
 * - jawaban_benar → derivable from cbt_soal.jawaban (kunci)
 * - nilai/iscorrect → already computed and stored in cbt_nilai
 */
class DropCbtJawabanSiswa extends Migration
{
    public function up()
    {
        $this->forge->dropTable('cbt_jawaban_siswa', true);
    }

    public function down()
    {
        // Recreate the table if rollback is needed
        $this->forge->addField([
            'id_jawaban' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_jadwal' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_soal' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jawaban' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'jawaban_benar' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'nilai' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
            ],
            'iscorrect' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addKey('id_jawaban', true);
        $this->forge->addKey(['id_siswa', 'id_jadwal']);
        $this->forge->addKey('id_soal');
        $this->forge->createTable('cbt_jawaban_siswa', true);
    }
}
