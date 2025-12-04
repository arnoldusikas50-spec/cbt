<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_libraries extends CI_Migration {

	public function up() {
		$this->dbforge->add_field(array(
			'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
			),
			'url' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'null' => FALSE,

			),
            'siteName' => array(
                'type' => 'VARCHAR',
                'constraint' => 200,
            ),
			'title' => array(
                'type' => 'VARCHAR',
                'constraint' => 200,
				'null' => FALSE
			),
			'description' => array(
                'type' => 'VARCHAR',
                'constraint' => 200
			),
            'image' => array(
                'type' => 'TEXT'
            )
        ));
		$this->dbforge->add_key("id",true);
		$this->dbforge->create_table("kelas_libraries", TRUE);
	 }

	public function down()	{
		$this->dbforge->drop_table("kelas_libraries", TRUE);
	}
}