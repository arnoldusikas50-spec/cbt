<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerangkatPekanEfektif extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_tp' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'id_smt' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'nama_kalender' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'total_minggu_semester' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 18,
            ],
            'minggu_efektif' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 16,
            ],
            'minggu_tidak_efektif' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 2,
            ],
            'rpe_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_tp', 'id_smt']);
        $this->forge->createTable('perangkat_pekan_efektif', true);
    }

    public function down()
    {
        $this->forge->dropTable('perangkat_pekan_efektif', true);
    }
}
