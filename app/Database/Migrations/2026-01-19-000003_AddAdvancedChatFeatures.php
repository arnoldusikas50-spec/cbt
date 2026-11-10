<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdvancedChatFeatures extends Migration
{
    public function up()
    {
        // Create chat_reactions table
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'message_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'user_type' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'guru', 'panitia', 'siswa', 'ai', 'system'],
                'default' => 'guru',
            ],
            'emoji' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['message_id', 'user_id', 'user_type'], false);
        $this->forge->addForeignKey('message_id', 'chat_messages', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('chat_reactions', true);

        // Create pinned_messages table
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'room_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'message_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],
            'pinned_by' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'pinned_by_type' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'guru', 'panitia', 'siswa', 'ai', 'system'],
                'default' => 'guru',
            ],
            'pinned_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['room_id', 'message_id'], true);
        $this->forge->addForeignKey('room_id', 'chat_rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('message_id', 'chat_messages', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pinned_messages', true);

        // Add thread support to chat_messages
        $this->forge->addColumn('chat_messages', [
            'parent_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'id',
            ],
            'is_thread' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'parent_id',
            ],
            'thread_count' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'is_thread',
            ],
        ]);

        // Add index for thread support
        $this->db->query('CREATE INDEX idx_chat_messages_parent_id ON chat_messages(parent_id)');
        $this->db->query('CREATE INDEX idx_chat_messages_is_thread ON chat_messages(is_thread)');
        
        // Add foreign key for thread support
        $this->db->query('ALTER TABLE chat_messages ADD CONSTRAINT fk_chat_messages_parent_id FOREIGN KEY (parent_id) REFERENCES chat_messages(id) ON DELETE CASCADE');
    }

    public function down()
    {
        // Drop foreign key first
        $this->db->query('ALTER TABLE chat_messages DROP FOREIGN KEY fk_chat_messages_parent_id');
        
        // Drop indexes
        $this->db->query('DROP INDEX idx_chat_messages_parent_id ON chat_messages');
        $this->db->query('DROP INDEX idx_chat_messages_is_thread ON chat_messages');
        
        // Drop thread support columns
        $this->forge->dropColumn('chat_messages', ['parent_id', 'is_thread', 'thread_count']);
        
        // Drop tables
        $this->forge->dropTable('pinned_messages', true);
        $this->forge->dropTable('chat_reactions', true);
    }
}
