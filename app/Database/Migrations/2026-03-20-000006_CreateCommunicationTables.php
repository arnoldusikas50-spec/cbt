<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommunicationTables extends Migration
{
    public function up()
    {
        // 1. Table: post
        $this->forge->addField([
            'id_post'    => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'dari'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'dari_group' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'kepada'     => ['type' => 'VARCHAR', 'constraint' => 50, 'comment' => 'target group'],
            'text'       => ['type' => 'LONGTEXT'],
            'tanggal'    => ['type' => 'DATETIME'],
            'updated'    => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id_post', true);
        $this->forge->createTable('post', true);

        // 2. Table: post_comments
        $this->forge->addField([
            'id_comment' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_post'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'dari'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'dari_group' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'text'       => ['type' => 'LONGTEXT'],
            'tanggal'    => ['type' => 'DATETIME'],
            'updated'    => ['type' => 'DATETIME'],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 1, 'default' => '1', 'comment' => '1:pengumuman, 2:materi, 3:tugas'],
        ]);
        $this->forge->addKey('id_comment', true);
        $this->forge->addKey('id_post');
        $this->forge->createTable('post_comments', true);

        // 3. Table: post_likes
        $this->forge->addField([
            'id_like'    => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_post'    => ['type' => 'INT', 'constraint' => 11],
            'id_comment' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'dari'       => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'dari_group' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id_like', true);
        $this->forge->addUniqueKey(['id_post', 'id_comment', 'dari', 'dari_group'], 'unique_like_post');
        $this->forge->createTable('post_likes', true);

        // 4. Table: post_reply
        $this->forge->addField([
            'id_reply'   => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_comment' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'dari'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'dari_group' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'text'       => ['type' => 'LONGTEXT'],
            'tanggal'    => ['type' => 'DATETIME'],
            'updated'    => ['type' => 'DATETIME'],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 1, 'default' => '1'],
        ]);
        $this->forge->addKey('id_reply', true);
        $this->forge->addKey('id_comment');
        $this->forge->createTable('post_reply', true);
    }

    public function down()
    {
        $this->forge->dropTable('post_reply', true);
        $this->forge->dropTable('post_likes', true);
        $this->forge->dropTable('post_comments', true);
        $this->forge->dropTable('post', true);
    }
}
