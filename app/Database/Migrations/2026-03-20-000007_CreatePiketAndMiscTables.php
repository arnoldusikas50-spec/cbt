<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePiketAndMiscTables extends Migration
{
    public function up()
    {
        // 1. Table: piket_absensi
        $this->forge->addField([
            'id'       => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'tanggal'  => ['type' => 'DATE'],
            'id_kelas' => ['type' => 'INT', 'constraint' => 11],
            'id_siswa' => ['type' => 'INT', 'constraint' => 11],
            'status'   => ['type' => 'VARCHAR', 'constraint' => 5, 'comment' => 'H, S, I, A, T'],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tanggal', 'id_siswa']);
        $this->forge->createTable('piket_absensi', true);

        // 2. Table: piket_jadwal
        $this->forge->addField([
            'id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'id_tp'    => ['type' => 'INT', 'constraint' => 11],
            'id_smt'   => ['type' => 'INT', 'constraint' => 11],
            'hari'     => ['type' => 'VARCHAR', 'constraint' => 20, 'comment' => 'Senin, Selasa, etc.'],
            'id_guru'  => ['type' => 'INT', 'constraint' => 11],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_tp', 'id_smt', 'hari']);
        $this->forge->createTable('piket_jadwal', true);

        // 3. Table: piket_tamu
        $this->forge->addField([
            'id'       => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'tanggal'  => ['type' => 'DATE'],
            'jam_masuk' => ['type' => 'TIME', 'null' => true],
            'jam_keluar' => ['type' => 'TIME', 'null' => true],
            'nama'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'keperluan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('tanggal');
        $this->forge->createTable('piket_tamu', true);

        // 4. Table: running_text
        $this->forge->addField([
            'id_text' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'text'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id_text', true);
        $this->forge->createTable('running_text', true);
    }

    public function down()
    {
        $this->forge->dropTable('running_text', true);
        $this->forge->dropTable('piket_tamu', true);
        $this->forge->dropTable('piket_jadwal', true);
        $this->forge->dropTable('piket_absensi', true);
    }
}
