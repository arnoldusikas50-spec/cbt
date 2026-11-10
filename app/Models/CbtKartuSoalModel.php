<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtKartuSoalModel extends Model
{
    protected $table = 'cbt_kartu_soal';
    protected $primaryKey = 'id_kartu';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'uuid',
        'judul',
        'deskripsi',
        'mapel_id',
        'kelas_id',
        'kelas_ids',   // JSON array of kelas IDs (multi-kelas support)
        'id_jenis',
        'tp_id',
        'smt_id',
        'status',
        'workflow_status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'approval_note',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        if (service('cache')->get('cbt_kartu_soal_schema_checked') === true) {
            return;
        }

        $db = \Config\Database::connect();
        if ($db->tableExists('cbt_kartu_soal')) {
            $this->migrateTable();
            $this->ensureIndexesAndConstraints();
            service('cache')->save('cbt_kartu_soal_schema_checked', true, 86400 * 30);
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
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
            'mapel_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'kelas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'id_jenis' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'tp_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'smt_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=draft, 2=final',
            ],
            'workflow_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'draft',
                'comment' => 'draft,submitted,approved,revisi',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'approval_note' => [
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
        $forge->addKey('id_kartu', true);
        $forge->addKey('uuid', false, true);
        $forge->addKey(['mapel_id', 'id_jenis', 'tp_id', 'smt_id']);
        $forge->addKey(['created_by', 'created_at']);
        $forge->addKey(['tp_id', 'smt_id', 'workflow_status']);
        $forge->addKey('deleted_at');
        $forge->createTable('cbt_kartu_soal', true);
        
        $this->ensureIndexesAndConstraints();

        service('cache')->save('cbt_kartu_soal_schema_checked', true, 86400 * 30);
    }

    private function migrateTable(): void
    {
        $forge = \Config\Database::forge();

        $columns = [
            'mapel_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'deskripsi',
            ],
            'kelas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'mapel_id',
            ],
            'id_jenis' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'kelas_id',
            ],
            'tp_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'id_jenis',
            ],
            'smt_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'tp_id',
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'smt_id',
            ],
            'workflow_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'draft',
                'after' => 'status',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'workflow_status',
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'submitted_at',
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'approved_at',
            ],
            'approval_note' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'approved_by',
            ],
            'kelas_ids' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'kelas_id',
                'comment' => 'JSON array of id_kelas for multi-class assignment',
            ],
        ];

        $existing = $this->db->getFieldNames('cbt_kartu_soal');
        foreach ($columns as $name => $definition) {
            if (in_array($name, $existing, true)) {
                continue;
            }
            $forge->addColumn('cbt_kartu_soal', [$name => $definition]);
        }
    }

    private function ensureIndexesAndConstraints(): void
    {
        // SQLite (used in tests) has different metadata tables and limited ALTER TABLE support.
        // Skip MySQL-specific index/FK synchronization to keep schema bootstrap portable.
        if ($this->isSQLite()) {
            return;
        }

        if (!$this->indexExists('cbt_kartu_soal', 'idx_kartu_scope')) {
            try {
                $this->db->query('ALTER TABLE cbt_kartu_soal ADD INDEX idx_kartu_scope (mapel_id, id_jenis, tp_id, smt_id, kelas_id)');
            } catch (\Throwable $th) {
                // Skip when schema cannot be altered on this environment.
            }
        }

        $this->alignForeignKeyColumns();

        $this->ensureForeignKey(
            'fk_kartu_mapel',
            'mapel_id',
            'master_mapel',
            'id_mapel'
        );
        $this->ensureForeignKey(
            'fk_kartu_kelas',
            'kelas_id',
            'master_kelas',
            'id_kelas'
        );
        $this->ensureForeignKey(
            'fk_kartu_jenis',
            'id_jenis',
            'cbt_jenis',
            'id_jenis'
        );
        $this->ensureForeignKey(
            'fk_kartu_tp',
            'tp_id',
            'master_tp',
            'id_tp'
        );
        $this->ensureForeignKey(
            'fk_kartu_smt',
            'smt_id',
            'master_smt',
            'id_smt'
        );

        // Ensure cbt_bank_soal has kartu_soal_id column + FK
        $this->ensureBankSoalKartuLinkage();
    }

    /**
     * Ensure cbt_bank_soal table has kartu_soal_id column with FK to cbt_kartu_soal.
     * This enforces referential integrity at the database level.
     */
    private function ensureBankSoalKartuLinkage(): void
    {
        if (!$this->tableExists('cbt_bank_soal')) {
            return;
        }

        // Step 1: Ensure kartu_soal_id column exists
        if (!$this->columnExists('cbt_bank_soal', 'kartu_soal_id')) {
            try {
                $forge = \Config\Database::forge();
                $forge->addColumn('cbt_bank_soal', [
                    'kartu_soal_id' => [
                        'type' => 'INT',
                        'constraint' => 11,
                        'unsigned' => true,
                        'null' => true,
                        'after' => 'bank_guru_id',
                        'comment' => 'FK to cbt_kartu_soal.id_kartu',
                    ],
                ]);
            } catch (\Throwable $th) {
                log_message('debug', '[CbtKartuSoalModel::ensureBankSoalKartuLinkage] column add: ' . $th->getMessage());
                return;
            }
        }

        // Step 2: Add index for faster lookups
        if (!$this->indexExists('cbt_bank_soal', 'idx_bank_kartu')) {
            try {
                $this->db->query('ALTER TABLE cbt_bank_soal ADD INDEX idx_bank_kartu (kartu_soal_id)');
            } catch (\Throwable $th) {
                // Skip
            }
        }

        // Step 3: Align column type with cbt_kartu_soal.id_kartu
        $this->syncColumnTypeWithReference('cbt_bank_soal', 'kartu_soal_id', 'cbt_kartu_soal', 'id_kartu');

        // Step 4: Add foreign key constraint
        if (!$this->isInnoDbTable('cbt_bank_soal') || !$this->isInnoDbTable('cbt_kartu_soal')) {
            return;
        }

        if (!$this->hasCompatibleColumnType('cbt_bank_soal', 'kartu_soal_id', 'cbt_kartu_soal', 'id_kartu')) {
            return;
        }

        $exists = $this->db->table('information_schema.TABLE_CONSTRAINTS')
            ->select('CONSTRAINT_NAME')
            ->where('CONSTRAINT_SCHEMA', $this->db->getDatabase())
            ->where('TABLE_NAME', 'cbt_bank_soal')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->where('CONSTRAINT_NAME', 'fk_bank_kartu_soal')
            ->get()
            ->getRow();

        if ($exists) {
            return;
        }

        try {
            // Clean orphan references first
            $this->db->query(
                "UPDATE cbt_bank_soal b " .
                "LEFT JOIN cbt_kartu_soal k ON b.kartu_soal_id = k.id_kartu " .
                "SET b.kartu_soal_id = NULL " .
                "WHERE b.kartu_soal_id IS NOT NULL AND (b.kartu_soal_id = 0 OR k.id_kartu IS NULL)"
            );
            $this->db->query(
                "ALTER TABLE cbt_bank_soal ADD CONSTRAINT fk_bank_kartu_soal " .
                "FOREIGN KEY (kartu_soal_id) REFERENCES cbt_kartu_soal(id_kartu) " .
                "ON UPDATE CASCADE ON DELETE SET NULL"
            );
        } catch (\Throwable $th) {
            log_message('debug', '[CbtKartuSoalModel::ensureBankSoalKartuLinkage] FK: ' . $th->getMessage());
        }
    }

    private function ensureForeignKey(string $name, string $column, string $refTable, string $refColumn): void
    {
        if (!$this->tableExists($refTable)) {
            return;
        }

        if (!$this->columnExists('cbt_kartu_soal', $column) || !$this->columnExists($refTable, $refColumn)) {
            return;
        }

        if (!$this->isInnoDbTable('cbt_kartu_soal') || !$this->isInnoDbTable($refTable)) {
            return;
        }

        if (!$this->hasCompatibleColumnType('cbt_kartu_soal', $column, $refTable, $refColumn)) {
            return;
        }

        $exists = $this->db->table('information_schema.TABLE_CONSTRAINTS')
            ->select('CONSTRAINT_NAME')
            ->where('CONSTRAINT_SCHEMA', $this->db->getDatabase())
            ->where('TABLE_NAME', 'cbt_kartu_soal')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->where('CONSTRAINT_NAME', $name)
            ->get()
            ->getRow();

        if ($exists) {
            return;
        }

        try {
            $this->db->query(
                "UPDATE cbt_kartu_soal k " .
                "LEFT JOIN {$refTable} r ON k.{$column} = r.{$refColumn} " .
                "SET k.{$column} = NULL " .
                "WHERE k.{$column} IS NOT NULL AND (k.{$column} = 0 OR r.{$refColumn} IS NULL)"
            );
            $this->db->query(
                "ALTER TABLE cbt_kartu_soal ADD CONSTRAINT {$name} " .
                "FOREIGN KEY ({$column}) REFERENCES {$refTable}({$refColumn}) " .
                "ON UPDATE CASCADE ON DELETE SET NULL"
            );
        } catch (\Throwable $th) {
            // Skip when existing data still violates FK.
        }
    }

    private function alignForeignKeyColumns(): void
    {
        $pairs = [
            ['mapel_id', 'master_mapel', 'id_mapel'],
            ['kelas_id', 'master_kelas', 'id_kelas'],
            ['id_jenis', 'cbt_jenis', 'id_jenis'],
            ['tp_id', 'master_tp', 'id_tp'],
            ['smt_id', 'master_smt', 'id_smt'],
        ];

        foreach ($pairs as $pair) {
            [$localCol, $refTable, $refCol] = $pair;
            $this->syncColumnTypeWithReference('cbt_kartu_soal', $localCol, $refTable, $refCol);
        }
    }

    private function syncColumnTypeWithReference(string $table, string $column, string $refTable, string $refColumn): void
    {
        if (
            !$this->tableExists($table) ||
            !$this->tableExists($refTable) ||
            !$this->columnExists($table, $column) ||
            !$this->columnExists($refTable, $refColumn)
        ) {
            return;
        }

        $local = $this->getColumnMeta($table, $column);
        $ref = $this->getColumnMeta($refTable, $refColumn);
        if (!$local || !$ref) {
            return;
        }

        $localType = strtolower((string) ($local->COLUMN_TYPE ?? ''));
        $refType = strtolower((string) ($ref->COLUMN_TYPE ?? ''));
        if ($localType === $refType) {
            return;
        }

        $targetType = (string) ($ref->COLUMN_TYPE ?? '');
        if ($targetType === '') {
            return;
        }

        try {
            $this->db->query("ALTER TABLE {$table} MODIFY COLUMN {$column} {$targetType} NULL");
        } catch (\Throwable $th) {
            // Skip if this environment does not allow ALTER or data is incompatible.
        }
    }

    private function getColumnMeta(string $table, string $column): ?object
    {
        return $this->db->table('information_schema.COLUMNS')
            ->select('COLUMN_TYPE, DATA_TYPE')
            ->where('TABLE_SCHEMA', $this->db->getDatabase())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->get()
            ->getRow();
    }

    private function hasCompatibleColumnType(string $table, string $column, string $refTable, string $refColumn): bool
    {
        $local = $this->getColumnMeta($table, $column);
        $ref = $this->getColumnMeta($refTable, $refColumn);
        if (!$local || !$ref) {
            return false;
        }

        $localType = strtolower((string) ($local->COLUMN_TYPE ?? ''));
        $refType = strtolower((string) ($ref->COLUMN_TYPE ?? ''));
        if ($localType !== $refType) {
            return false;
        }

        $localDataType = strtolower((string) ($local->DATA_TYPE ?? ''));
        $refDataType = strtolower((string) ($ref->DATA_TYPE ?? ''));
        return $localDataType === $refDataType;
    }

    private function tableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return in_array($column, $this->db->getFieldNames($table), true);
    }

    private function isInnoDbTable(string $table): bool
    {
        $row = $this->db->table('information_schema.TABLES')
            ->select('ENGINE')
            ->where('TABLE_SCHEMA', $this->db->getDatabase())
            ->where('TABLE_NAME', $table)
            ->get()
            ->getRow();
        if (!$row) {
            return false;
        }
        return strtolower((string) ($row->ENGINE ?? '')) === 'innodb';
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = $this->db->table('information_schema.STATISTICS')
            ->select('INDEX_NAME')
            ->where('TABLE_SCHEMA', $this->db->getDatabase())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->limit(1)
            ->get()
            ->getRow();
        return (bool) $row;
    }

    private function isSQLite(): bool
    {
        return strtolower((string) ($this->db->DBDriver ?? '')) === 'sqlite3';
    }

    public function findByUuid(string $uuid): ?object
    {
        return $this->where('uuid', $uuid)->first();
    }

    public function getById(int $id): ?object
    {
        return $this->find($id);
    }

    public function getKartuByMapelKelas(int $mapelId, int $kelasId, ?int $tpId = null, ?int $smtId = null, ?int $jenisId = null): array
    {
        $builder = $this->where('mapel_id', $mapelId)
                        ->where('kelas_id', $kelasId)
                        ->where('status', 1);
        
        if ($tpId !== null) {
            $builder = $builder->where('tp_id', $tpId);
        }
        if ($smtId !== null) {
            $builder = $builder->where('smt_id', $smtId);
        }
        if ($jenisId !== null) {
            $builder = $builder->where('id_jenis', $jenisId);
        }

        return $builder->findAll();
    }

    /**
     * Get approved kartu soal for a specific mapel in the active period.
     */
    public function getApprovedKartuByMapel(int $mapelId, int $tpId, int $smtId, ?int $userId = null): array
    {
        $builder = $this->where('mapel_id', $mapelId)
            ->where('tp_id', $tpId)
            ->where('smt_id', $smtId)
            ->where('workflow_status', 'approved')
            ->where('deleted_at', null);

        if ($userId !== null) {
            $builder->where('created_by', $userId);
        }

        return $builder->orderBy('updated_at', 'DESC')->findAll();
    }

    /**
     * Check if kartu soal is being used in any bank soal.
     * Returns count of bank soal that use this kartu soal.
     */
    public function isUsedInBankSoal(int $kartuId): int
    {
        if ($kartuId <= 0) {
            return 0;
        }

        // Check cbt_bank_soal table for kartu_soal_id
        if ($this->db->tableExists('cbt_bank_soal')) {
            $count = $this->db->table('cbt_bank_soal')
                ->where('kartu_soal_id', $kartuId)
                ->where('status', 1)
                ->countAllResults();
            
            if ($count > 0) {
                return $count;
            }
        }

        // Also check cbt_bank table (alternative table name)
        if ($this->db->tableExists('cbt_bank')) {
            $count = $this->db->table('cbt_bank')
                ->where('kartu_soal_id', $kartuId)
                ->where('status', 1)
                ->countAllResults();
            
            if ($count > 0) {
                return $count;
            }
        }

        return 0;
    }

    /**
     * Get list of bank soal that use this kartu soal.
     */
    public function getBankSoalUsage(int $kartuId): array
    {
        if ($kartuId <= 0) {
            return [];
        }

        $result = [];

        // Check cbt_bank_soal table
        if ($this->db->tableExists('cbt_bank_soal')) {
            $banks = $this->db->table('cbt_bank_soal')
                ->select('id_bank, bank_kode, bank_nama, bank_mapel_id')
                ->where('kartu_soal_id', $kartuId)
                ->where('status', 1)
                ->get()
                ->getResultArray();
            
            foreach ($banks as $bank) {
                $result[] = [
                    'id_bank' => $bank['id_bank'],
                    'kode' => $bank['bank_kode'] ?? '-',
                    'nama' => !empty($bank['bank_nama']) ? $bank['bank_nama'] : ($bank['bank_kode'] ?? '-'),
                    'id_mapel' => $bank['bank_mapel_id'] ?? null,
                ];
            }
        }

        // Also check cbt_bank table
        if ($this->db->tableExists('cbt_bank')) {
            $banks = $this->db->table('cbt_bank')
                ->select('id_bank, bank_kode, bank_nama, id_mapel')
                ->where('kartu_soal_id', $kartuId)
                ->where('status', 1)
                ->get()
                ->getResultArray();
            
            foreach ($banks as $bank) {
                // Avoid duplicates
                $exists = false;
                foreach ($result as $existing) {
                    if ($existing['id_bank'] === $bank['id_bank']) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $result[] = [
                        'id_bank' => $bank['id_bank'],
                        'kode' => $bank['bank_kode'] ?? '-',
                        'nama' => $bank['bank_nama'] ?? '-',
                        'id_mapel' => $bank['id_mapel'],
                    ];
                }
            }
        }

        return $result;
    }
}
