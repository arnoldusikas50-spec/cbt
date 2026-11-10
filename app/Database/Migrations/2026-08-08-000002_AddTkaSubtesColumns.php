<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTkaSubtesColumns extends Migration
{
    public function up()
    {
        // 1. Add TKA subtes fields to cbt_bank_soal if missing
        if (!$this->db->fieldExists('subtes_code', 'cbt_bank_soal')) {
            $this->forge->addColumn('cbt_bank_soal', [
                'subtes_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => 'GENERAL',
                    'comment'    => 'Kode Sub-tes TKA (VERBAL, KUANTITATIF, LOGIS, LITERASI, NUMERASI)'
                ],
                'subtes_nama' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                    'comment'    => 'Nama Sub-tes TKA (Penalaran Verbal, Kuantitatif, dll)'
                ]
            ]);
        }

        // 2. Add TKA subtes fields and IRT weight to cbt_soal if missing
        if (!$this->db->fieldExists('subtes_code', 'cbt_soal')) {
            $this->forge->addColumn('cbt_soal', [
                'subtes_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => 'GENERAL'
                ],
                'bobot_irt' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,3',
                    'default'    => 1.000,
                    'comment'    => 'Bobot kesukaran/IRT item soal TKA'
                ]
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('subtes_code', 'cbt_bank_soal')) {
            $this->forge->dropColumn('cbt_bank_soal', ['subtes_code', 'subtes_nama']);
        }
        if ($this->db->fieldExists('subtes_code', 'cbt_soal')) {
            $this->forge->dropColumn('cbt_soal', ['subtes_code', 'bobot_irt']);
        }
    }
}
