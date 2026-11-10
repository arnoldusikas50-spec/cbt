<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMaintenanceSetting extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('setting')) {
            return;
        }

        $fields = [
            'maintenance_aktif' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => true,
                'after' => 'mikrotik_pass',
            ],
            'maintenance_pesan' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'maintenance_aktif',
            ],
            'maintenance_sampai' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'maintenance_pesan',
            ],
        ];

        foreach ($fields as $columnName => $definition) {
            if (! $this->db->fieldExists($columnName, 'setting')) {
                $this->forge->addColumn('setting', [$columnName => $definition]);
            }
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('setting')) {
            return;
        }

        foreach (['maintenance_sampai', 'maintenance_pesan', 'maintenance_aktif'] as $columnName) {
            if ($this->db->fieldExists($columnName, 'setting')) {
                $this->forge->dropColumn('setting', $columnName);
            }
        }
    }
}