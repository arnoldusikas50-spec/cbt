<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_kbm extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('kelas_jadwal_kbm');
        $fields = [];
        if (!in_array('kbm_jam_selesai', $fieldExist)) {
            $fields['kbm_jam_selesai'] = ['type' => 'VARCHAR', 'constraint' => '5', 'after' => 'kbm_jam_mulai'];
        }
        if (!in_array('libur', $fieldExist)) {
            $fields[] = 'libur INT NOT NULL DEFAULT 0';
        }
        if (count($fields) > 0) {
            $this->dbforge->add_column('kelas_jadwal_kbm', $fields);
        }
	 }
}