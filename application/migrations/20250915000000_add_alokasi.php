<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_alokasi extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('cbt_pengawas');
        $fields = [];
        if (!in_array('jam_ke', $fieldExist)) {
            $fields['jam_ke'] = ['type' => 'VARCHAR', 'constraint' => 2];
        }

        if (count($fields) > 0) {
            $this->dbforge->add_column('cbt_pengawas', $fields);
        }
	 }
}