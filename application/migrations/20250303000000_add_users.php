<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_users extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('users');
        $fields = [];
        if (!in_array('foto', $fieldExist)) {
            $fields['foto'] = ['type' => 'VARCHAR', 'constraint' => '250'];
        }
        if (!in_array('jabatan', $fieldExist)) {
            $fields['jabatan'] = ['type' => 'VARCHAR', 'constraint' => '50'];
        }

        if (count($fields) > 0) {
            $this->dbforge->add_column('users', $fields);
        }
	 }
}