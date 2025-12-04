<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_guru extends CI_Migration {

	public function up() {
        $fields = [
            'dokumen' => [
                'type' => 'LONGTEXT',
                'null' => TRUE
            ],
        ];
        $this->dbforge->add_column('master_guru', $fields);
	 }
}