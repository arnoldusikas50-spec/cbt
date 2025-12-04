<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_rekapsiswa extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('cbt_rekap_siswa');
        $fields = [];
        if (!in_array('siswa', $fieldExist)) {
            $fields['siswa'] = ['type' => 'LONGTEXT', 'null' => TRUE];
        }

        if (!in_array('agama', $fieldExist)) {
            $fields['agama'] = ['type' => 'VARCHAR', 'constraint' => '50'];
        }
        if (count($fields) > 0) {
            $this->dbforge->add_column('cbt_rekap_siswa', $fields);
        }
	 }
}