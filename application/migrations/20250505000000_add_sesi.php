<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_sesi extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('cbt_sesi');
        $fields = [];
        if (!in_array('istirahat', $fieldExist)) {
            $fields['istirahat'] = ['type' => 'INT', 'constraint' => '2', 'after' => 'waktu_akhir'];
        }

        if (count($fields) > 0) {
            $this->dbforge->add_column('cbt_sesi', $fields);
        }
	 }
}