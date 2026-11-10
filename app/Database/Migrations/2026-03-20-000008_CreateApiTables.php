<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiTables extends Migration
{
    public function up()
    {
        // 1. Table: api_setting
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'auto_sync' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'edit_profile_siswa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'edit_profile_guru' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('api_setting', true);

        // 2. Table: api_token
        $this->forge->addField([
            'id_api' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'timestamp' => [
                'type'    => 'DATETIME',
            ],
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'address' => [
                'type' => 'MEDIUMTEXT',
            ],
            'agent' => [
                'type' => 'MEDIUMTEXT',
            ],
            'device' => [
                'type' => 'MEDIUMTEXT',
            ],
            'token' => [
                'type' => 'MEDIUMTEXT',
            ],
        ]);
        $this->forge->addKey('id_api', true);
        $this->forge->addKey('id_user');
        $this->forge->createTable('api_token', true);
    }

    public function down()
    {
        $this->forge->dropTable('api_token', true);
        $this->forge->dropTable('api_setting', true);
    }
}
