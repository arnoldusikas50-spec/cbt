<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel antrian scoring CBT untuk menggantikan pola exec() yang
 * mem-bypass 1 proses PHP CLI per siswa saat ujian selesai.
 *
 * Dengan antrian, 1000 siswa yang submit dalam satu jendela waktu
 * hanya melakukan INSERT ringan ke sini; konsumen cron
 * (php spark cbt:proses_scoring_queue) memproses batch kecil.
 */
class CreateScoringQueue extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'id_jadwal' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => '0=pending, 1=processing, 2=done, 3=failed',
            ],
            'attempts' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_siswa', 'id_jadwal'], 'uq_scoring_siswa_jadwal');
        $this->forge->addKey('status');
        $this->forge->createTable('cbt_scoring_queue', true);
    }

    public function down()
    {
        $this->forge->dropTable('cbt_scoring_queue', true);
    }
}
