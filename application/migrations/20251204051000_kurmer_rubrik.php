<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Kurmer_rubrik extends CI_Migration {

    public function up() {
        $this->create_table_kurmer_penilaian_setting();
        $this->create_table_kurmer_rubrik_level();
        $this->create_table_kurmer_penilaian_eviden();
    }

    public function down() {
        $this->dbforge->drop_table('kurmer_penilaian_eviden', true);
        $this->dbforge->drop_table('kurmer_rubrik_level', true);
        $this->dbforge->drop_table('kurmer_penilaian_setting', true);
    }

    private function create_table_kurmer_penilaian_setting() {
        if ($this->db->table_exists('kurmer_penilaian_setting')) {
            return;
        }

        $fields = [
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_tp' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'id_smt' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'id_mapel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'id_kelas' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'bobot_formatif' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 60
            ],
            'bobot_sumatif' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 40
            ],
            'min_level_tuntas' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'B'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key(['id_mapel', 'id_kelas']);
        $this->dbforge->create_table('kurmer_penilaian_setting', true);
    }

    private function create_table_kurmer_rubrik_level() {
        if ($this->db->table_exists('kurmer_rubrik_level')) {
            return;
        }

        $fields = [
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_mapel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'level_kode' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'level_nama' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'warna_label' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],
            'urutan' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key(['id_mapel', 'level_kode']);
        $this->dbforge->create_table('kurmer_rubrik_level', true);
    }

    private function create_table_kurmer_penilaian_eviden() {
        if ($this->db->table_exists('kurmer_penilaian_eviden')) {
            return;
        }

        $fields = [
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'id_penilaian' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true
            ],
            'jenis_bukti' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'tautan_bukti' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'tanggal_bukti' => [
                'type' => 'DATE',
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ];

        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('id_penilaian');
        $this->dbforge->create_table('kurmer_penilaian_eviden', true);
    }
}
