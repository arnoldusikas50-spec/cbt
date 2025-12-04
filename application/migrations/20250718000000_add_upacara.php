<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_upacara extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('kelas_jadwal_kbm');
        $fields = [];
        if (!in_array('upacara', $fieldExist)) {
            $fields['upacara'] = ['type' => 'TEXT', 'after' => 'istirahat'];
        }

        if (count($fields) > 0) {
            $this->dbforge->add_column('kelas_jadwal_kbm', $fields);
        }
	 }
}