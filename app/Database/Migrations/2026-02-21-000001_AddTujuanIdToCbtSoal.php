<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTujuanIdToCbtSoal extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('tujuan_id', 'cbt_soal')) {
            $this->forge->addColumn('cbt_soal', [
                'tujuan_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'default' => null,
                    'after' => 'mapel_id'
                ]
            ]);
            
            // Add index for performance
            $this->db->query('CREATE INDEX idx_cbtsoal_tujuan ON cbt_soal (tujuan_id)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('tujuan_id', 'cbt_soal')) {
            // Drop index first
            $this->db->query('DROP INDEX idx_cbtsoal_tujuan ON cbt_soal');
            $this->forge->dropColumn('cbt_soal', 'tujuan_id');
        }
    }
}
