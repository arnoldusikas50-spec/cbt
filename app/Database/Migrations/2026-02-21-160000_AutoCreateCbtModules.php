<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Auto Create All New CBT Module Tables
 * 
 * This migration automatically creates all tables required for:
 * - Audit Trail System
 * - Offline Capability  
 * - Enhanced Security
 * - Query Optimization
 * 
 * @version 1.0.0
 */
class AutoCreateCbtModules extends Migration
{
    public function up()
    {
        // 1. Create cbt_nilai_audit table
        $this->createAuditNilaiTable();
        
        // 2. Create cbt_jawaban_audit table
        $this->createAuditJawabanTable();
        
        // 3. Create cbt_sync_queue table
        $this->createSyncQueueTable();
        
        // 4. Create cbt_cache_metadata table (for cache management)
        $this->createCacheMetadataTable();
        
        // 5. Create cbt_security_logs table
        $this->createSecurityLogsTable();
        
        // 6. Add columns to existing tables if not exist
        $this->addColumnsToExistingTables();
        
        // 7. Create indexes for performance
        $this->createIndexes();
        
        echo "✅ All CBT module tables created successfully!\n";
    }

    public function down()
    {
        // Drop tables in reverse order
        $this->forge->dropTable('cbt_security_logs', true);
        $this->forge->dropTable('cbt_cache_metadata', true);
        $this->forge->dropTable('cbt_sync_queue', true);
        $this->forge->dropTable('cbt_jawaban_audit', true);
        $this->forge->dropTable('cbt_nilai_audit', true);
        
        // Note: We don't drop columns from existing tables in down()
        // to prevent data loss
        
        echo "⚠️ All CBT module tables dropped!\n";
    }

    // ==========================================
    // TABLE 1: cbt_nilai_audit
    // ==========================================
    private function createAuditNilaiTable()
    {
        if ($this->db->tableExists('cbt_nilai_audit')) {
            echo "ℹ️ Table cbt_nilai_audit already exists, skipping...\n";
            return;
        }

        $this->forge->addField([
            'id_audit' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_jadwal' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'INSERT, UPDATE, DELETE, RECALCULATE, CORRECT, MARK_CORRECTED',
            ],
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'changed_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'changed_by_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Admin, Guru, Panitia, Siswa, System',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id_audit', true);
        $this->forge->addKey(['id_siswa', 'id_jadwal']);
        $this->forge->addKey('action');
        $this->forge->addKey('created_at');
        
        $this->forge->createTable('cbt_nilai_audit', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        echo "✅ Created table: cbt_nilai_audit\n";
    }

    // ==========================================
    // TABLE 2: cbt_jawaban_audit
    // ==========================================
    private function createAuditJawabanTable()
    {
        if ($this->db->tableExists('cbt_jawaban_audit')) {
            echo "ℹ️ Table cbt_jawaban_audit already exists, skipping...\n";
            return;
        }

        $this->forge->addField([
            'id_audit' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_jadwal' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_soal' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'old_jawaban' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'new_jawaban' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'jawaban_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'changed_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'changed_by_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id_audit', true);
        $this->forge->addKey(['id_siswa', 'id_jadwal', 'id_soal']);
        $this->forge->addKey('action');
        $this->forge->addKey('created_at');
        
        $this->forge->createTable('cbt_jawaban_audit', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        echo "✅ Created table: cbt_jawaban_audit\n";
    }

    // ==========================================
    // TABLE 3: cbt_sync_queue
    // ==========================================
    private function createSyncQueueTable()
    {
        if ($this->db->tableExists('cbt_sync_queue')) {
            echo "ℹ️ Table cbt_sync_queue already exists, skipping...\n";
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'siswa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'jadwal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'soal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'jawaban_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'synced', 'failed', 'retrying'],
                'default' => 'pending',
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 3,
                'unsigned' => true,
                'default' => 0,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'client_timestamp' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Timestamp from client device',
            ],
            'server_timestamp' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => true,
                'comment' => 'Timestamp when received by server',
            ],
            'synced_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['siswa_id', 'jadwal_id']);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        
        $this->forge->createTable('cbt_sync_queue', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        echo "✅ Created table: cbt_sync_queue\n";
    }

    // ==========================================
    // TABLE 4: cbt_cache_metadata
    // ==========================================
    private function createCacheMetadataTable()
    {
        if ($this->db->tableExists('cbt_cache_metadata')) {
            echo "ℹ️ Table cbt_cache_metadata already exists, skipping...\n";
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'cache_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'cache_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'soal, jawaban, nilai, jadwal, etc',
            ],
            'entity_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tags' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'hit_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('cache_key', false, true); // Unique
        $this->forge->addKey('cache_type');
        $this->forge->addKey('expires_at');
        
        $this->forge->createTable('cbt_cache_metadata', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        echo "✅ Created table: cbt_cache_metadata\n";
    }

    // ==========================================
    // TABLE 5: cbt_security_logs
    // ==========================================
    private function createSecurityLogsTable()
    {
        if ($this->db->tableExists('cbt_security_logs')) {
            echo "ℹ️ Table cbt_security_logs already exists, skipping...\n";
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'XSS_ATTEMPT, RACE_CONDITION, INVALID_ACCESS, etc',
            ],
            'severity' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default' => 'low',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'user_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
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
            'request_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'request_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resolved' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'resolved_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('event_type');
        $this->forge->addKey('severity');
        $this->forge->addKey('created_at');
        
        $this->forge->createTable('cbt_security_logs', true, [
            'ENGINE' => 'InnoDB',
            'CHARACTER SET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        echo "✅ Created table: cbt_security_logs\n";
    }

    // ==========================================
    // Add columns to existing tables
    // ==========================================
    private function addColumnsToExistingTables()
    {
        // Add columns to cbt_jawaban if not exist
        $this->addColumnIfNotExists('cbt_jawaban', 'created_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'ragu'
        ]);

        $this->addColumnIfNotExists('cbt_jawaban', 'updated_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'created_at'
        ]);

        // Add columns to cbt_nilai if not exist
        $this->addColumnIfNotExists('cbt_nilai', 'nilai_asli', [
            'type' => 'DECIMAL',
            'constraint' => '5,2',
            'null' => true,
            'comment' => 'Nilai sebelum katrol',
            'after' => 'pg_nilai'
        ]);

        $this->addColumnIfNotExists('cbt_nilai', 'detail_json', [
            'type' => 'JSON',
            'null' => true,
            'after' => 'catatan'
        ]);

        echo "✅ Added columns to existing tables\n";
    }

    // ==========================================
    // Create indexes for performance
    // ==========================================
    private function createIndexes()
    {
        // Add composite index on cbt_jadwal for common queries
        $this->addIndexIfNotExists('cbt_jadwal', 'idx_jadwal_status', ['id_tp', 'id_smt', 'status_ujian']);
        
        // Add index on cbt_siswa for performance
        $this->addIndexIfNotExists('cbt_siswa', 'idx_siswa_jadwal', ['id_siswa', 'id_jadwal']);
        
        // Add index on cbt_jawaban for performance
        $this->addIndexIfNotExists('cbt_jawaban', 'idx_jawaban_lookup', ['id_siswa', 'id_jadwal', 'id_soal']);
        
        // Add index on cbt_soal for performance
        $this->addIndexIfNotExists('cbt_soal', 'idx_soal_bank', ['bank_id', 'tampilkan']);

        echo "✅ Created performance indexes\n";
    }

    // ==========================================
    // Helper methods
    // ==========================================
    private function addColumnIfNotExists($table, $column, $definition)
    {
        $fields = $this->db->getFieldNames($table);
        
        if (!in_array($column, $fields)) {
            $this->forge->addColumn($table, [$column => $definition]);
            echo "✅ Added column {$column} to {$table}\n";
        } else {
            echo "ℹ️ Column {$column} already exists in {$table}\n";
        }
    }

    private function addIndexIfNotExists($table, $indexName, $columns)
    {
        // Check if index exists
        $dbName = $this->db->getDatabase();
        $query = $this->db->query(
            "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = '{$dbName}' 
             AND TABLE_NAME = '{$table}' 
             AND INDEX_NAME = '{$indexName}'"
        );
        
        $result = $query ? $query->getRow() : null;
        
        if ($result && $result->count == 0) {
            $this->db->query("CREATE INDEX {$indexName} ON {$table} (" . implode(',', $columns) . ")");
            echo "✅ Created index {$indexName} on {$table}\n";
        } else {
            echo "ℹ️ Index {$indexName} already exists on {$table}\n";
        }
    }
}
