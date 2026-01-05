<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_sync_error_log extends CI_Migration
{

    public function up()
    {
        // Add error_message column to sync_log
        if ($this->db->table_exists('sync_log') && !$this->db->field_exists('error_message', 'sync_log')) {
            $fields = array(
                'error_message' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'after' => 'status'
                )
            );
            $this->dbforge->add_column('sync_log', $fields);
        }
    }

    public function down()
    {
        if ($this->db->table_exists('sync_log') && $this->db->field_exists('error_message', 'sync_log')) {
            $this->dbforge->drop_column('sync_log', 'error_message');
        }
    }
}
