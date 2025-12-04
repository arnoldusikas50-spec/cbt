<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_logmateri extends CI_Migration {

	public function up() {
        $fieldExist = $this->db->list_fields('log_materi');
        $fields = [];
        if (!in_array('ulang', $fieldExist)) {
            $fields['ulang'] = [
                'type' => 'VARCHAR',
                'constraint' => 1,
                'null' => FALSE,
                'default' => '0',
                'after' => 'catatan'];
        }

        if (count($fields) > 0) {
            $this->dbforge->add_column('log_materi', $fields);
        }
	 }
}