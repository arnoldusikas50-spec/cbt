<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sessions extends CI_Migration {

	public function up() {
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'VARCHAR',
				'constraint' => 128,
				'null' => FALSE,

			),
			'ip_address' => array(
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => FALSE,

			),
			'timestamp' => array(
				'type' => 'INT UNSIGNED',
				'null' => FALSE,
				'default' => '0',

			),
			'data' => array(
				'type' => 'BLOB',
				'null' => FALSE,

			),
		));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("ci_sessions", TRUE);
	 }

	public function down()	{
		$this->dbforge->drop_table("ci_sessions", TRUE);
	}
}