<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_api_key extends CI_Migration
{

    public function up()
    {
        $fieldExist = $this->db->list_fields('setting');
        $fields = [];
        if (!in_array('api_key', $fieldExist)) {
            $fields['api_key'] = ['type' => 'TEXT', 'null' => TRUE, 'after' => 'mode_app'];
        }
        if (count($fields) > 0) {
            $this->dbforge->add_column('setting', $fields);
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('setting', 'api_key');
    }
}
