<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rekap_jadwal extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id_rekap' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'id_tp' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'tp' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
            ),
            'id_smt' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'smt' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
            ),
            'id_jadwal' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'jadwal' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'id_bank' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'bank_soal' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'kode_bank' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'kelas_bank' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'level_bank' => array(
                'type' => 'INT',
                'constraint' => 3,
            ),
            'id_jenis' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'kode_jenis' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'id_mapel' => array(
                'type' => 'INT',
                'constraint' => 11,
            ),
            'kode_mapel' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
            ),
            'nama_mapel' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
            ),
            'tgl_mulai' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
            ),
            'tgl_selesai' => array(
                'type' => 'VARCHAR',
                'constraint' => 30,
            ),
            'soal_pg' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'soal_kompleks' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'soal_jodohkan' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'soal_isian' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'soal_esai' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
            'id_guru' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'nama_guru' => array(
                'type' => 'VARCHAR',
                'constraint' => 250,
                'null' => TRUE,
            ),
            'pengawas' => array(
                'type' => 'LONGTEXT',
                'null' => TRUE,
            ),
        ));
        $this->dbforge->add_key('id_rekap', TRUE);
        $this->dbforge->add_key(array('id_jadwal', 'id_bank'));
        $this->dbforge->create_table('cbt_rekap_jadwal', TRUE);
    }

    public function down()
    {
    }
}