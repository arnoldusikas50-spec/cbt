<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration: Create Activity Log Table
 * 
 * This table stores all important user and system activities for audit purposes.
 * It follows Write-Only principle - records should never be updated or deleted.
 */
class Migration_Create_activity_log extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'user_id' => [
                'type' => 'INT',
                'null' => TRUE,
                'comment' => 'ID of user performing action (null for system actions)'
            ],
            'user_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
                'comment' => 'admin, guru, siswa, panitia, school, system'
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE,
                'comment' => 'Action performed: LOGIN, LOGOUT, CREATE, UPDATE, DELETE, SYNC, SECURITY_*'
            ],
            'target_table' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE,
                'comment' => 'Table affected by this action'
            ],
            'target_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE,
                'comment' => 'Primary key of affected record'
            ],
            'old_value' => [
                'type' => 'TEXT',
                'null' => TRUE,
                'comment' => 'JSON: Previous value before change'
            ],
            'new_value' => [
                'type' => 'TEXT',
                'null' => TRUE,
                'comment' => 'JSON: New value after change'
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => TRUE
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => TRUE
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => FALSE
            ]
        ]);

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->add_key('user_type');
        $this->dbforge->add_key('action');
        $this->dbforge->add_key('target_table');
        $this->dbforge->add_key('created_at');

        $this->dbforge->create_table('activity_log', TRUE, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Audit trail for all user and system activities'
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_table('activity_log', TRUE);
    }
}
