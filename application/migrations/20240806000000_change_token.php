<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_token extends CI_Migration {

	public function up() {
        $fields = [
            'updated' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => ''
            ]
        ];

        $this->dbforge->modify_column('cbt_token', $fields);
	 }
}