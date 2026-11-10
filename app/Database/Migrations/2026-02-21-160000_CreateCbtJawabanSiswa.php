<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCbtJawabanSiswaTable extends Migration
{
    public function up()
    {
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

    public function down()
    {
        $this->forge->dropTable('cbt_jawaban_siswa', true);
    }
}
