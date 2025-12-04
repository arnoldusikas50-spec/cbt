<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_jadwal extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('kelas_jadwal_mapel');
        $fields = [];
        if (!in_array('dari', $fieldExist)) {
            $fields['dari'] = ['type' => 'VARCHAR', 'constraint' => '10'];
        }
        if (!in_array('sampai', $fieldExist)) {
            $fields['sampai'] = ['type' => 'VARCHAR', 'constraint' => '10'];
        }

        if (count($fields) > 0) {
            $this->dbforge->add_column('kelas_jadwal_mapel', $fields);
        }
	 }
}