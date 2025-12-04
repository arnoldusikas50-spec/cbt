<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_pengawas extends CI_Migration {

	public function up() {
        $fields = [
            'id_pengawas' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ],
        ];
        $this->dbforge->modify_column('cbt_pengawas', $fields);
	 }
}