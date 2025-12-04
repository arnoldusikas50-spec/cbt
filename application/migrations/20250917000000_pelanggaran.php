<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_pelanggaran extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('cbt_siswa');
        $fields = [];
        if (!in_array('pelanggaran', $fieldExist)) {
            $fields['pelanggaran'] = ['type' => 'TEXT'];
        }
        if (count($fields) > 0) {
            $this->dbforge->add_column('cbt_siswa', $fields);
        }
	 }
}