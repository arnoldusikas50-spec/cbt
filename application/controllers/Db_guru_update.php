<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Db_guru_update extends CI_Controller {
    public function index() {
        if (!$this->db->field_exists('ttd', 'master_guru')) {
            $this->load->dbforge();
            $fields = array(
                'ttd' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'foto'
                ),
            );
            $this->dbforge->add_column('master_guru', $fields);
            echo "Kolom ttd berhasil ditambahkan ke master_guru.";
        } else {
            echo "Kolom ttd sudah ada di master_guru.";
        }
    }
}
