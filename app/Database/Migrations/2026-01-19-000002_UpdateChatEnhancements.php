<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateChatEnhancements extends Migration
{
    public function up()
    {
        // Add snapshot columns to chat_participants
        $this->forge->addColumn('chat_participants', [
            'snapshot_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'joined_at'
            ],
            'snapshot_avatar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'snapshot_name'
            ],
        ]);

        // Add delivery status columns to chat_messages
        $this->forge->addColumn('chat_messages', [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['sent', 'delivered', 'read'],
                'default' => 'sent',
                'after' => 'attachment_name'
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status'
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'delivered_at'
            ],
        ]);

        // Create typing status table for per-room typing indicator
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'room_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_type' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'guru', 'panitia', 'siswa', 'ai', 'system'],
                'default' => 'guru',
            ],
            'is_typing' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['room_id', 'user_id', 'user_type']);
        $this->forge->createTable('chat_typing_status', true);
    }

    public function down()
    {
        // Remove typing status table
        $this->forge->dropTable('chat_typing_status', true);

        // Remove delivery columns
        $this->forge->dropColumn('chat_messages', ['status', 'delivered_at', 'read_at']);

        // Remove snapshot columns
        $this->forge->dropColumn('chat_participants', ['snapshot_name', 'snapshot_avatar']);
    }
}
