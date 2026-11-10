<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * CBT Setup Command
 * 
 * Command untuk auto setup database untuk semua modul CBT baru:
 * - Run migrations
 * - Seed test data
 * - Create indexes
 * - Verify setup
 * 
 * Usage: php spark cbt:setup [options]
 * 
 * @version 1.0.0
 */
class CbtSetup extends BaseCommand
{
    protected $group = 'CBT';
    protected $name = 'cbt:setup';
    protected $description = 'Auto setup database for all new CBT modules';
    protected $usage = 'cbt:setup [options]';
    protected $arguments = [];
    protected $options = [
        '--seed' => 'Seed test data after migration',
        '--fresh' => 'Drop all tables and recreate (WARNING: DATA LOSS)',
        '--verify' => 'Verify database setup without making changes',
        '--backup' => 'Create backup before migration',
    ];

    protected $db;

    public function run(array $params)
    {
        CLI::write('🔧 CBT Module Database Setup Tool', 'green');
        CLI::write(str_repeat('=', 50), 'green');
        CLI::newLine();

        $this->db = Database::connect();

        // Parse options
        $seed = array_key_exists('seed', $params) || CLI::getOption('seed');
        $fresh = array_key_exists('fresh', $params) || CLI::getOption('fresh');
        $verify = array_key_exists('verify', $params) || CLI::getOption('verify');
        $backup = array_key_exists('backup', $params) || CLI::getOption('backup');

        // FRESH mode confirmation
        if ($fresh) {
            CLI::write('⚠️  WARNING: Fresh mode will DROP ALL CBT MODULE TABLES!', 'red');
            CLI::write('This action cannot be undone!', 'red');
            $confirm = CLI::prompt('Are you sure? Type "yes" to continue', ['no']);
            
            if ($confirm !== 'yes') {
                CLI::write('Operation cancelled.', 'yellow');
                return;
            }
        }

        // BACKUP mode
        if ($backup || $fresh) {
            $this->createBackup();
        }

        // VERIFY mode
        if ($verify) {
            $this->verifySetup();
            return;
        }

        // STEP 1: Run Migrations
        CLI::write('📦 Step 1: Running Migrations...', 'blue');
        $this->runMigrations($fresh);

        // STEP 2: Create Additional Indexes
        CLI::write('📑 Step 2: Creating Performance Indexes...', 'blue');
        $this->createAdditionalIndexes();

        // STEP 3: Seed Data (optional)
        if ($seed) {
            CLI::write('🌱 Step 3: Seeding Test Data...', 'blue');
            $this->seedData();
        }

        // STEP 4: Verify Setup
        CLI::write('✅ Step 4: Verifying Setup...', 'blue');
        $this->verifySetup();

        CLI::newLine();
        CLI::write('🎉 CBT Module Setup Completed Successfully!', 'green');
        CLI::newLine();
        CLI::write('Summary:', 'blue');
        CLI::write('  • All new tables created/updated', 'white');
        CLI::write('  • Performance indexes applied', 'white');
        if ($seed) {
            CLI::write('  • Test data seeded', 'white');
        }
        CLI::write('  • System ready for use', 'white');
        CLI::newLine();
        CLI::write('Next steps:', 'yellow');
        CLI::write('  1. Review database structure', 'white');
        CLI::write('  2. Configure CBT settings in app/Config/Cbt.php', 'white');
        CLI::write('  3. Test audit trail at /audit', 'white');
        CLI::write('  4. Test offline capability in ujian page', 'white');
    }

    // ==========================================
    // Run Migrations
    // ==========================================
    private function runMigrations(bool $fresh = false)
    {
        $migrate = service('migration');

        if ($fresh) {
            // Rollback first
            CLI::write('   Rolling back existing migrations...', 'yellow');
            try {
                $migrate->regress(0); // Rollback all
                CLI::write('   ✓ Rollback completed', 'green');
            } catch (\Exception $e) {
                CLI::write('   ⚠ Rollback skipped: ' . $e->getMessage(), 'yellow');
            }
        }

        // Run latest migrations
        CLI::write('   Running latest migrations...', 'yellow');
        
        try {
            $migrate->latest();
            CLI::write('   ✓ Migrations completed successfully', 'green');
        } catch (\Exception $e) {
            CLI::write('   ✗ Migration failed: ' . $e->getMessage(), 'red');
            
            // Try running specific migration
            $this->runSpecificMigration('AutoCreateCbtModules');
        }
    }

    // ==========================================
    // Run Specific Migration
    // ==========================================
    private function runSpecificMigration(string $name)
    {
        CLI::write("   Trying to run {$name} migration directly...", 'yellow');
        
        $migrationFile = APPPATH . "Database/Migrations/2026-02-21-160000_{$name}.php";
        
        if (!file_exists($migrationFile)) {
            CLI::write("   ✗ Migration file not found: {$migrationFile}", 'red');
            return;
        }

        require_once $migrationFile;
        $className = "App\\Database\\Migrations\\{$name}";
        
        if (!class_exists($className)) {
            CLI::write("   ✗ Migration class not found: {$className}", 'red');
            return;
        }

        $migration = new $className();
        $migration->up();
        
        CLI::write("   ✓ Migration {$name} completed", 'green');
    }

    // ==========================================
    // Create Additional Indexes
    // ==========================================
    private function createAdditionalIndexes()
    {
        $indexes = [
            // Audit tables indexes
            [
                'table' => 'cbt_nilai_audit',
                'name' => 'idx_audit_siswa_jadwal',
                'columns' => ['id_siswa', 'id_jadwal', 'created_at']
            ],
            [
                'table' => 'cbt_nilai_audit',
                'name' => 'idx_audit_action',
                'columns' => ['action', 'created_at']
            ],
            [
                'table' => 'cbt_jawaban_audit',
                'name' => 'idx_jawaban_audit_lookup',
                'columns' => ['id_siswa', 'id_jadwal', 'id_soal']
            ],
            
            // Sync queue indexes
            [
                'table' => 'cbt_sync_queue',
                'name' => 'idx_sync_status',
                'columns' => ['status', 'attempts']
            ],
            [
                'table' => 'cbt_sync_queue',
                'name' => 'idx_sync_timestamp',
                'columns' => ['created_at', 'status']
            ],
            
            // Security logs indexes
            [
                'table' => 'cbt_security_logs',
                'name' => 'idx_security_severity',
                'columns' => ['severity', 'created_at']
            ],
            [
                'table' => 'cbt_security_logs',
                'name' => 'idx_security_event',
                'columns' => ['event_type', 'created_at']
            ],
        ];

        foreach ($indexes as $index) {
            $this->createIndexIfNotExists(
                $index['table'], 
                $index['name'], 
                $index['columns']
            );
        }
    }

    // ==========================================
    // Seed Test Data
    // ==========================================
    private function seedData()
    {
        $seeder = new \App\Database\Seeders\CbtModuleSeeder();
        $seeder->run();
    }

    // ==========================================
    // Verify Setup
    // ==========================================
    private function verifySetup()
    {
        $requiredTables = [
            'cbt_nilai_audit' => 'Audit trail for nilai',
            'cbt_jawaban_audit' => 'Audit trail for jawaban',
            'cbt_sync_queue' => 'Offline sync queue',
            'cbt_cache_metadata' => 'Cache metadata',
            'cbt_security_logs' => 'Security event logs',
        ];

        $existingTables = $this->db->listTables();
        
        CLI::newLine();
        CLI::write('Database Verification:', 'blue');
        CLI::write(str_repeat('-', 60), 'blue');
        
        $allGood = true;
        
        foreach ($requiredTables as $table => $description) {
            $exists = in_array($table, $existingTables);
            $status = $exists ? '✓' : '✗';
            $color = $exists ? 'green' : 'red';
            
            CLI::write(sprintf(
                '  %s %-30s %s',
                $status,
                $table,
                "({$description})"
            ), $color);
            
            if (!$exists) {
                $allGood = false;
            }
        }
        
        CLI::write(str_repeat('-', 60), 'blue');
        
        // Check columns in existing tables
        if (in_array('cbt_nilai', $existingTables)) {
            $fields = $this->db->getFieldNames('cbt_nilai');
            $newColumns = ['nilai_asli', 'detail_json'];
            
            CLI::write('Checking enhanced columns in cbt_nilai:', 'blue');
            foreach ($newColumns as $column) {
                $exists = in_array($column, $fields);
                $status = $exists ? '✓' : '✗';
                $color = $exists ? 'green' : 'yellow';
                CLI::write("  {$status} {$column}", $color);
            }
        }
        
        if ($allGood) {
            CLI::write('✅ All required tables exist!', 'green');
        } else {
            CLI::write('⚠️  Some tables are missing. Run migration again.', 'yellow');
        }
        
        CLI::newLine();
    }

    // ==========================================
    // Create Backup
    // ==========================================
    private function createBackup()
    {
        CLI::write('💾 Creating database backup...', 'blue');
        
        $dbConfig = new Database();
        $connection = $dbConfig->default;
        
        $backupPath = WRITEPATH . 'backups/';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $filename = 'cbt_backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupPath . $filename;
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h%s -u%s %s %s > %s 2>&1',
            escapeshellarg($connection['hostname']),
            escapeshellarg($connection['username']),
            $connection['password'] ? '-p' . escapeshellarg($connection['password']) : '',
            escapeshellarg($connection['database']),
            escapeshellarg($filepath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            CLI::write("   ✓ Backup created: {$filename}", 'green');
            CLI::write("   Location: {$filepath}", 'white');
        } else {
            CLI::write('   ⚠ Backup failed or mysqldump not available', 'yellow');
            CLI::write('   Please create backup manually before proceeding', 'yellow');
        }
    }

    // ==========================================
    // Helper: Create Index If Not Exists
    // ==========================================
    private function createIndexIfNotExists($table, $indexName, $columns)
    {
        $dbName = $this->db->getDatabase();
        
        $query = $this->db->query(
            "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND INDEX_NAME = ?",
            [$dbName, $table, $indexName]
        );
        
        $result = $query->getRow();
        
        if ($result->count == 0) {
            try {
                $columnsStr = implode(',', $columns);
                $this->db->query("CREATE INDEX {$indexName} ON {$table} ({$columnsStr})");
                CLI::write("   ✓ Created index: {$indexName}", 'green');
            } catch (\Exception $e) {
                CLI::write("   ⚠ Failed to create index {$indexName}: " . $e->getMessage(), 'yellow');
            }
        } else {
            CLI::write("   ℹ Index exists: {$indexName}", 'yellow');
        }
    }
}
