<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKbmAndAcademyTables extends Migration
{
    public function up()
    {
        // 1. Table: kelas_siswa
        $this->forge->addField([
            'id_kelas_siswa' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_tp'          => ['type' => 'INT', 'constraint' => 11],
            'id_smt'         => ['type' => 'INT', 'constraint' => 11],
            'id_siswa'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_kelas'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $this->forge->addKey('id_kelas_siswa', true);
        $this->forge->addKey(['id_siswa', 'id_kelas']);
        $this->forge->createTable('kelas_siswa', true);

        // 2. Table: kelas_materi
        $this->forge->addField([
            'id_materi'     => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_tp'         => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'id_smt'        => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'id_guru'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'materi_kelas'  => ['type' => 'MEDIUMTEXT'],
            'id_mapel'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'judul_materi'  => ['type' => 'MEDIUMTEXT'],
            'isi_materi'    => ['type' => 'LONGTEXT'],
            'file'          => ['type' => 'LONGTEXT', 'null' => true],
            'tgl_mulai'     => ['type' => 'DATETIME', 'null' => true],
            'created_on'    => ['type' => 'DATETIME', 'null' => true],
            'updated_on'    => ['type' => 'DATETIME', 'null' => true],
            'status'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'jenis'         => ['type' => 'INT', 'constraint' => 11, 'default' => 1, 'comment' => '1=materi, 2=tugas'],
        ]);
        $this->forge->addKey('id_materi', true);
        $this->forge->addKey('id_guru');
        $this->forge->createTable('kelas_materi', true);

        // 3. Table: kelas_jadwal_mapel
        $this->forge->addField([
            'id_jadwal' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_tp'     => ['type' => 'INT', 'constraint' => 11],
            'id_smt'    => ['type' => 'INT', 'constraint' => 11],
            'id_kelas'  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_hari'   => ['type' => 'INT', 'constraint' => 11],
            'jam_ke'    => ['type' => 'INT', 'constraint' => 11],
            'id_mapel'  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $this->forge->addKey('id_jadwal', true);
        $this->forge->addKey(['id_tp', 'id_smt', 'id_kelas']);
        $this->forge->createTable('kelas_jadwal_mapel', true);

        // 4. Table: kelas_catatan_mapel
        $this->forge->addField([
            'id_catatan' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_tp'      => ['type' => 'INT', 'constraint' => 11],
            'id_smt'     => ['type' => 'INT', 'constraint' => 11],
            'id_siswa'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_mapel'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_kelas'   => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'id_guru'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'tgl'        => ['type' => 'DATETIME'],
            'text'       => ['type' => 'MEDIUMTEXT'],
        ]);
        $this->forge->addKey('id_catatan', true);
        $this->forge->addKey(['id_tp', 'id_smt', 'id_siswa']);
        $this->forge->createTable('kelas_catatan_mapel', true);

        // 5. Table: guru_jurnal
        $this->forge->addField([
            'id_jurnal'    => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'id_guru'      => ['type' => 'INT', 'constraint' => 11],
            'id_kelas'     => ['type' => 'INT', 'constraint' => 11],
            'id_mapel'     => ['type' => 'INT', 'constraint' => 11],
            'id_tp'        => ['type' => 'INT', 'constraint' => 11],
            'id_smt'       => ['type' => 'INT', 'constraint' => 11],
            'tanggal'      => ['type' => 'DATE'],
            'jam_ke'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'materi_pokok' => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_jurnal', true);
        $this->forge->addKey(['id_guru', 'id_kelas', 'tanggal']);
        $this->forge->createTable('guru_jurnal', true);
    }

    public function down()
    {
        $this->forge->dropTable('guru_jurnal', true);
        $this->forge->dropTable('kelas_catatan_mapel', true);
        $this->forge->dropTable('kelas_jadwal_mapel', true);
        $this->forge->dropTable('kelas_materi', true);
        $this->forge->dropTable('kelas_siswa', true);
    }
}
