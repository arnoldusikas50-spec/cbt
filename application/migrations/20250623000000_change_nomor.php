<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_nomor extends CI_Migration {

	public function up() {
        $fields = [
            'nomor_peserta' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
        ];
        $this->dbforge->modify_column('cbt_nomor_peserta', $fields);
	 }
}