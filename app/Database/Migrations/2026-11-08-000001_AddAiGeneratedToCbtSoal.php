<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAiGeneratedToCbtSoal extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('ai_generated', 'cbt_soal')) {
            $this->forge->addColumn('cbt_soal', [
                'ai_generated' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                    'comment'    => '1 = soal hasil generate AI',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('ai_generated', 'cbt_soal')) {
            $this->forge->dropColumn('cbt_soal', 'ai_generated');
        }
    }
}
