<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCbtCoreTables extends Migration
{
    public function up()
    {
        // 1. Table: cbt_bank_soal
        $this->forge->addField([
            'id_bank'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'bank_jenis_id'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'bank_kode'       => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => '0'],
            'bank_level'      => ['type' => 'VARCHAR', 'constraint' => 225],
            'bank_kelas'      => ['type' => 'LONGTEXT'],
            'bank_mapel_id'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'bank_guru_id'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'bank_nama'       => ['type' => 'VARCHAR', 'constraint' => 250],
            'jml_soal'        => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'tampil_pg'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'bobot_pg'        => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'opsi'            => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status'          => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'id_tp'           => ['type' => 'INT', 'constraint' => 11],
            'id_smt'          => ['type' => 'INT', 'constraint' => 11],
            'sync_uuid'       => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'sync_school_uuid' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_bank', true);
        $this->forge->addUniqueKey('bank_kode');
        $this->forge->createTable('cbt_bank_soal', true);

        // 2. Table: cbt_soal
        $this->forge->addField([
            'id_soal'      => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'bank_id'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'mapel_id'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'jenis'        => ['type' => 'INT', 'constraint' => 11, 'comment' => '1=ganda, 2=kompleks, 3=jodohkan, 4=isian, 5=uraian'],
            'nomor_soal'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'soal'         => ['type' => 'LONGTEXT', 'null' => true],
            'opsi_a'       => ['type' => 'LONGTEXT', 'null' => true],
            'opsi_b'       => ['type' => 'LONGTEXT', 'null' => true],
            'opsi_c'       => ['type' => 'LONGTEXT', 'null' => true],
            'opsi_d'       => ['type' => 'LONGTEXT', 'null' => true],
            'opsi_e'       => ['type' => 'LONGTEXT', 'null' => true],
            'jawaban'      => ['type' => 'LONGTEXT', 'null' => true],
            'tampilkan'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'sync_uuid'    => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_soal', true);
        $this->forge->addKey('bank_id');
        $this->forge->createTable('cbt_soal', true);

        // 3. Table: cbt_jadwal
        $this->forge->addField([
            'id_jadwal'      => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_tp'          => ['type' => 'CHAR', 'constraint' => 2],
            'id_smt'         => ['type' => 'CHAR', 'constraint' => 2],
            'id_bank'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_jenis'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'tgl_mulai'      => ['type' => 'VARCHAR', 'constraint' => 20],
            'tgl_selesai'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'durasi_ujian'   => ['type' => 'INT', 'constraint' => 11],
            'acak_soal'      => ['type' => 'INT', 'constraint' => 11],
            'acak_opsi'      => ['type' => 'INT', 'constraint' => 11],
            'hasil_tampil'   => ['type' => 'INT', 'constraint' => 11],
            'token'          => ['type' => 'INT', 'constraint' => 11],
            'status'         => ['type' => 'INT', 'constraint' => 11],
            'sync_uuid'      => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_jadwal', true);
        $this->forge->addKey(['id_tp', 'id_smt']);
        $this->forge->createTable('cbt_jadwal', true);

        // 4. Table: cbt_jawaban
        $this->forge->addField([
            'id_jawaban' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'   => ['type' => 'INT', 'constraint' => 11],
            'id_jadwal'  => ['type' => 'INT', 'constraint' => 11],
            'id_soal'    => ['type' => 'INT', 'constraint' => 11],
            'jawaban'    => ['type' => 'TEXT', 'null' => true],
            'nilai'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'ragu'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'waktu'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_jawaban', true);
        $this->forge->addKey(['id_siswa', 'id_jadwal', 'id_soal']);
        $this->forge->createTable('cbt_jawaban', true);

        // 5. Table: cbt_nilai
        $this->forge->addField([
            'id_nilai' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_jadwal' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'pg_benar' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'pg_nilai' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '0'],
            'nilai_asli' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'sync_uuid' => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id_nilai', true);
        $this->forge->addKey(['id_siswa', 'id_jadwal']);
        $this->forge->createTable('cbt_nilai', true);

        // 6. Table: cbt_siswa (Log Ujian/Session)
        $this->forge->addField([
            'id_cbt_siswa' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_siswa'     => ['type' => 'INT', 'constraint' => 11],
            'id_jadwal'    => ['type' => 'INT', 'constraint' => 11],
            'id_bank'      => ['type' => 'INT', 'constraint' => 11],
            'status'       => ['type' => 'INT', 'constraint' => 11],
            'mulai'        => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true],
            'selesai'      => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true],
            'lama_ujian'   => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'sync_uuid'    => ['type' => 'VARCHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id_cbt_siswa', true);
        $this->forge->addKey(['id_siswa', 'id_jadwal']);
        $this->forge->createTable('cbt_siswa', true);
    }

    public function down()
    {
        $this->forge->dropTable('cbt_siswa', true);
        $this->forge->dropTable('cbt_nilai', true);
        $this->forge->dropTable('cbt_jawaban', true);
        $this->forge->dropTable('cbt_jadwal', true);
        $this->forge->dropTable('cbt_soal', true);
        $this->forge->dropTable('cbt_bank_soal', true);
    }
}
