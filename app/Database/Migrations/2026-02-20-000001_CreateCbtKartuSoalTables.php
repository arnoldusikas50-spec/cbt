<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCbtKartuSoalTables extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('cbt_kartu_soal')) {
            $this->forge->addField([
                'id_kartu' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'uuid' => [
                    'type' => 'CHAR',
                    'constraint' => 36,
                ],
                'judul' => [
                    'type' => 'VARCHAR',
                    'constraint' => 150,
                ],
                'deskripsi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_kartu', true);
            $this->forge->addKey('uuid', false, true);
            $this->forge->addKey(['created_by', 'created_at']);
            $this->forge->addKey('deleted_at');
            $this->forge->createTable('cbt_kartu_soal', true);
        }

        if (!$this->db->tableExists('cbt_kartu_soal_item')) {
            $this->forge->addField([
                'id_item' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'kartu_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'bank_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'soal_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'nomor' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'default' => 1,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_item', true);
            $this->forge->addKey(['kartu_id', 'nomor']);
            $this->forge->addKey(['kartu_id', 'bank_id', 'soal_id'], false, true);
            $this->forge->createTable('cbt_kartu_soal_item', true);
        }

        if (!$this->db->tableExists('cbt_kartu_soal_audit')) {
            $this->forge->addField([
                'id_audit' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'kartu_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'aksi' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'detail' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => true,
                ],
                'user_agent' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'waktu' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_audit', true);
            $this->forge->addKey(['kartu_id', 'waktu']);
            $this->forge->addKey(['user_id', 'waktu']);
            $this->forge->createTable('cbt_kartu_soal_audit', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('cbt_kartu_soal_audit', true);
        $this->forge->dropTable('cbt_kartu_soal_item', true);
        $this->forge->dropTable('cbt_kartu_soal', true);
    }
}

