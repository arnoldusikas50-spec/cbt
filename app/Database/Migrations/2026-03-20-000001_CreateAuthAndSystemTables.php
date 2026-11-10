<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthAndSystemTables extends Migration
{
    public function up()
    {
        // 1. Table: users (IonAuth style)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 254,
                'null'       => true,
            ],
            'activation_selector' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'activation_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'forgotten_password_selector' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'forgotten_password_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'forgotten_password_time' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'remember_selector' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'remember_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_on' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'last_login' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'null'       => true,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 250,
                'null'       => true,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users', true);

        // 2. Table: groups
        $this->forge->addField([
            'id' => [
                'type'           => 'MEDIUMINT',
                'constraint'     => 8,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('groups', true);

        // 3. Table: users_groups
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'group_id' => [
                'type'       => 'MEDIUMINT',
                'constraint' => 8,
                'unsigned'   => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'group_id']);
        $this->forge->createTable('users_groups', true);

        // 4. Table: login_attempts
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
            ],
            'login' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'time' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('login_attempts', true);

        // 5. Table: users_profile
        $this->forge->addField([
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'nama_lengkap' => [
                'type' => 'TEXT',
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'jabatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'level_access' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'foto' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ttd' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ruang' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Ruang 1 (R1)',
            ],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->createTable('users_profile', true);

        // 6. Table: activity_log
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'user_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'target_table' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'target_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'old_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'new_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('activity_log', true);

        // 7. Table: logs
        $this->forge->addField([
            'id_log' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'aktivitas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'waktu' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_log', true);
        $this->forge->addKey('id_user');
        $this->forge->addKey('waktu');
        $this->forge->createTable('logs', true);

        // 8. Table: ci_sessions
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
            ],
            'timestamp' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'data' => [
                'type' => 'BLOB',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('timestamp');
        $this->forge->createTable('ci_sessions', true);
    }

    public function down()
    {
        $this->forge->dropTable('ci_sessions', true);
        $this->forge->dropTable('logs', true);
        $this->forge->dropTable('activity_log', true);
        $this->forge->dropTable('users_profile', true);
        $this->forge->dropTable('login_attempts', true);
        $this->forge->dropTable('users_groups', true);
        $this->forge->dropTable('groups', true);
        $this->forge->dropTable('users', true);
    }
}
