<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRaporAndAssessmentTables extends Migration
{
    public function up()
    {
        // 1. Table: rapor_admin_setting
        $this->forge->addField([
            'id_setting'   => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_tp'        => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'id_smt'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'kkm_tunggal'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'kkm'          => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'bobot_ph'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'bobot_pts'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'bobot_pas'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'tgl_rapor_akhir' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $this->forge->addKey('id_setting', true);
        $this->forge->createTable('rapor_admin_setting', true);

        // 2. Table: rapor_nilai_harian (Legacy Assessment)
        $this->forge->addField([
            'id_nilai_harian' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_siswa'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_mapel'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_kelas'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_tp'      => ['type' => 'INT', 'constraint' => 11],
            'id_smt'     => ['type' => 'INT', 'constraint' => 11],
            'p1'         => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => true],
            'p2'         => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => true],
            'p3'         => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => true],
            'p_rata_rata' => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'p_predikat'  => ['type' => 'VARCHAR', 'constraint' => 1, 'null' => true],
        ]);
        $this->forge->addKey('id_nilai_harian', true);
        $this->forge->addKey(['id_siswa', 'id_mapel', 'id_tp', 'id_smt']);
        $this->forge->createTable('rapor_nilai_harian', true);

        // 3. Table: nilai_akhir_kurmer (Kurikulum Merdeka)
        $this->forge->addField([
            'id_nilai_akhir' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'       => ['type' => 'INT', 'constraint' => 11],
            'id_mapel'       => ['type' => 'INT', 'constraint' => 11],
            'id_guru'        => ['type' => 'INT', 'constraint' => 11],
            'id_tahun'       => ['type' => 'INT', 'constraint' => 11],
            'id_smt'         => ['type' => 'INT', 'constraint' => 11],
            'nilai_sas'      => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'nilai_rapor'    => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'sync_status'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_nilai_akhir', true);
        $this->forge->addKey(['id_siswa', 'id_mapel', 'id_tahun', 'id_smt']);
        $this->forge->createTable('nilai_akhir_kurmer', true);

        // 4. Table: nilai_proses
        $this->forge->addField([
            'id_nilai_proses' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'        => ['type' => 'INT', 'constraint' => 11],
            'id_mapel'        => ['type' => 'INT', 'constraint' => 11],
            'id_tp'           => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'id_guru'         => ['type' => 'INT', 'constraint' => 11],
            'id_tahun'        => ['type' => 'INT', 'constraint' => 11],
            'id_smt'          => ['type' => 'INT', 'constraint' => 11],
            'tipe'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'sumatif_lm'],
            'nilai'           => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_nilai_proses', true);
        $this->forge->addKey(['id_siswa', 'id_mapel', 'id_tp', 'tipe']);
        $this->forge->createTable('nilai_proses', true);
    }

    public function down()
    {
        $this->forge->dropTable('nilai_proses', true);
        $this->forge->dropTable('nilai_akhir_kurmer', true);
        $this->forge->dropTable('rapor_nilai_harian', true);
        $this->forge->dropTable('rapor_admin_setting', true);
    }
}
