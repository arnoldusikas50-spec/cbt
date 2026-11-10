<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameSchoolsTable extends Migration
{
    public function up()
    {
        // Rename table
        $this->forge->renameTable('sync_schools', 'sync_servers');

        // Rename columns
        $fields = [
            'npsn' => [
                'name' => 'kode_server',
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_sekolah' => [
                'name' => 'nama_server',
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
        ];
        $this->forge->modifyColumn('sync_servers', $fields);
    }

    public function down()
    {
        // Revert columns
        $fields = [
            'kode_server' => [
                'name' => 'npsn',
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_server' => [
                'name' => 'nama_sekolah',
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
        ];
        $this->forge->modifyColumn('sync_servers', $fields);

        // Revert table name
        $this->forge->renameTable('sync_servers', 'sync_schools');
    }
}
