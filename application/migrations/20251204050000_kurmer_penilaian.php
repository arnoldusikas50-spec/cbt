<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Kurmer_penilaian extends CI_Migration {

    public function up() {
        $this->create_table_kurmer_tp();
        $this->create_table_kurmer_penilaian();
        $this->create_table_kurmer_penilaian_detail();
        $this->create_table_kurmer_proyek_p5();
    }

    public function down() {
        $this->dbforge->drop_table('kurmer_proyek_p5', true);
        $this->dbforge->drop_table('kurmer_penilaian_detail', true);
        $this->dbforge->drop_table('kurmer_penilaian', true);
        $this->dbforge->drop_table('kurmer_tujuan_pembelajaran', true);
    }

    private function create_table_kurmer_tp() {
        if ($this->db->table_exists('kurmer_tujuan_pembelajaran')) {
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
                'unsigned' => true,
                'null' => true
            ],
            'fase' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true
            ],
            'kode_tp' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'judul_tp' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'uraian' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'kriteria_ketuntasan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->dbforge->add_key(['id_mapel', 'id_kelas']);
        $this->dbforge->add_key(['id_tp', 'id_smt']);
        $this->dbforge->create_table('kurmer_tujuan_pembelajaran', true);
    }

    private function create_table_kurmer_penilaian() {
        if ($this->db->table_exists('kurmer_penilaian')) {
            return;
        }

        $fields = [
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
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
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'id_tujuan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'jenis_asesmen' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'formatif'
            ],
            'tanggal_penilaian' => [
                'type' => 'DATE',
                'null' => true
            ],
            'skor' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true
            ],
            'level_kelulusan' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],
            'capaian' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'bukti_penilaian' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'is_summative' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->dbforge->add_key(['id_kelas', 'id_siswa']);
        $this->dbforge->add_key('id_tujuan');
        $this->dbforge->create_table('kurmer_penilaian', true);
    }

    private function create_table_kurmer_penilaian_detail() {
        if ($this->db->table_exists('kurmer_penilaian_detail')) {
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
            'indikator' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'uraian_eviden' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'skor_indikator' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true
            ],
            'level_indikator' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],
            'catatan_tindak_lanjut' => [
                'type' => 'TEXT',
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
        $this->dbforge->create_table('kurmer_penilaian_detail', true);
    }

    private function create_table_kurmer_proyek_p5() {
        if ($this->db->table_exists('kurmer_proyek_p5')) {
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
            'id_kelas' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'tema' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true
            ],
            'dimensi' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'elemen' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'sub_elemen' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true
            ],
            'predikat' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'tanggal_proyek' => [
                'type' => 'DATE',
                'null' => true
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->dbforge->add_key(['id_kelas', 'id_siswa']);
        $this->dbforge->create_table('kurmer_proyek_p5', true);
    }
}
