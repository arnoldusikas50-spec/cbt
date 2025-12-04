<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_col_jadwal extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('cbt_jadwal');
        $fields['pelanggaran'] = ['type' => 'VARCHAR', 'constraint' => '2'];
        if (!in_array('pelanggaran', $fieldExist)) {
            $this->dbforge->add_column('cbt_jadwal', $fields);
        } else {
            $this->dbforge->modify_column('cbt_jadwal', $fields);
        }
	 }
}