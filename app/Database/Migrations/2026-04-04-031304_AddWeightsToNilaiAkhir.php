<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWeightsToNilaiAkhir extends Migration
{
    public function up()
    {
        $this->forge->addColumn('nilai_akhir_kurmer', [
            'bobot_sts' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
                'after' => 'bobot_sas'
            ],
            'bobot_praktik' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
                'after' => 'bobot_sts'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('nilai_akhir_kurmer', ['bobot_sts', 'bobot_praktik']);
    }
}
