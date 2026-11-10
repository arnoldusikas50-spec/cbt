<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCbtJenisTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('cbt_jenis')) {
            return;
        }

        $this->forge->addField([
            'id_jenis'   => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_jenis' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kode_jenis' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'status'     => ['type' => 'INT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('id_jenis', true);
        $this->forge->createTable('cbt_jenis', true);
    }

    public function down()
    {
        $this->forge->dropTable('cbt_jenis', true);
    }
}