<?php

namespace App\Models;

use CodeIgniter\Model;

class CbtKartuSoalItemModel extends Model
{
    protected $table = 'cbt_kartu_soal_item';
    protected $primaryKey = 'id_item';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'kartu_id',
        'nomor',
        'bank_id',
        'soal_id',
        'jenis_soal',
        'id_tujuan',
        'indikator_pencapaian',
        'tingkat_kesulitan',
        'topik',
        'bentuk_soal',
        'cp',
        'materi',
        'tujuan',
        'indikator_soal',
        'level_kognitif',
        'estimasi_waktu',
        'catatan',
        'butir_soal',
        'opsi_json',
        'jawaban_benar_json',
        'pembahasan',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        if (service('cache')->get('cbt_kartu_soal_item_schema_checked') === true) {
            return;
        }

        $db = \Config\Database::connect();
        if ($db->tableExists('cbt_kartu_soal_item')) {
            // Only migrate when required columns are missing; legacy columns may remain safely.
            $fields = $db->getFieldNames('cbt_kartu_soal_item');
            $addColumns = [];
            if (
                !in_array('id_tujuan', $fields, true)
                || !in_array('jenis_soal', $fields, true)
                || !in_array('indikator_soal', $fields, true)
                || !in_array('bank_id', $fields, true)
                || !in_array('soal_id', $fields, true)
                || !in_array('indikator_pencapaian', $fields, true)
                || !in_array('tingkat_kesulitan', $fields, true)
                || !in_array('topik', $fields, true)
                || !in_array('butir_soal', $fields, true)
                || !in_array('opsi_json', $fields, true)
                || !in_array('jawaban_benar_json', $fields, true)
                || !in_array('pembahasan', $fields, true)
                || !in_array('updated_at', $fields, true)
            ) {
                $this->migrateTable();
            }

            // Always ensure bank_id and soal_id allow NULL (legacy schema fix)
            $this->fixNullableColumns($db);
            // Ensure performance indexes exist (safe, no-op if already present)
            $this->ensurePerformanceIndexes();

            service('cache')->save('cbt_kartu_soal_item_schema_checked', true, 86400 * 30);
            return;
        }

        $forge = \Config\Database::forge();
        $forge->addField([
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
            'nomor' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
            ],
            'bank_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Sumber bank soal',
            ],
            'soal_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Sumber id_soal di bank',
            ],
            'jenis_soal' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=PG, 2=Kompleks, 3=Jodohkan, 4=Isian, 5=Essay, 6=Urutan, 7=Cloze',
            ],
            'id_tujuan' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Relasi ke e_tujuan_pembelajaran.id_tujuan',
            ],
            'bentuk_soal' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Bentuk penulisan soal spesifik',
            ],
            'cp' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Capaian Pembelajaran',
            ],
            'materi' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Materi/Bahan Kajian',
            ],
            'tujuan' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Tujuan Pembelajaran',
            ],
            'indikator_soal' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Indikator Soal (kemampuan yang diukur)',
            ],
            'indikator_pencapaian' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Indikator Pencapaian Kompetensi (IPK)',
            ],
            'tingkat_kesulitan' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'mudah/sedang/sulit',
            ],
            'topik' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Topik materi soal',
            ],
            'level_kognitif' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'C1-C6 atau Taksonomi Bloom',
            ],
            'estimasi_waktu' => [
                'type' => 'INT',
                'constraint' => 5,
                'null' => true,
                'comment' => 'Dalam menit',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'butir_soal' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'Konten utama butir soal (HTML)',
            ],
            'opsi_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON opsi jawaban sesuai tipe butir',
            ],
            'jawaban_benar_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => 'JSON jawaban benar',
            ],
            'pembahasan' => [
                'type' => 'LONGTEXT',
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
        ]);
        $forge->addKey('id_item', true);
        $forge->addKey(['kartu_id', 'nomor']);
        $forge->addForeignKey('kartu_id', 'cbt_kartu_soal', 'id_kartu', 'CASCADE', 'CASCADE');
        $forge->createTable('cbt_kartu_soal_item', true);
        // Ensure composite index exists after creation
        $this->ensurePerformanceIndexes();
    }

    /**
     * Ensure performance-critical indexes exist.
     * Safe to call on every boot since it checks before adding.
     */
    private function ensurePerformanceIndexes(): void
    {
        if (strtolower((string) ($this->db->DBDriver ?? '')) === 'sqlite3') {
            return; // SQLite doesn't support ALTER TABLE ADD INDEX syntax
        }

        $indexes = [
            'idx_item_kartu_nomor' => 'ALTER TABLE cbt_kartu_soal_item ADD INDEX idx_item_kartu_nomor (kartu_id, nomor)',
            'idx_item_kartu_jenis' => 'ALTER TABLE cbt_kartu_soal_item ADD INDEX idx_item_kartu_jenis (kartu_id, jenis_soal)',
            'idx_item_tujuan'      => 'ALTER TABLE cbt_kartu_soal_item ADD INDEX idx_item_tujuan (id_tujuan)',
        ];

        foreach ($indexes as $name => $sql) {
            $query = $this->db->table('information_schema.STATISTICS')
                ->select('INDEX_NAME')
                ->where('TABLE_SCHEMA', $this->db->getDatabase())
                ->where('TABLE_NAME', 'cbt_kartu_soal_item')
                ->where('INDEX_NAME', $name)
                ->limit(1)
                ->get();
            $exists = $query ? $query->getRow() : null;

            if (!$exists) {
                try {
                    $this->db->query($sql);
                } catch (\Throwable $e) {
                    // Silently skip if environment doesn't allow ALTER
                }
            }
        }

        // Ensure Foreign Keys exist
        if (strtolower((string) ($this->db->DBDriver ?? '')) !== 'sqlite3') {
            // FK: kartu_id → cbt_kartu_soal.id_kartu
            try {
                $query = $this->db->table('information_schema.KEY_COLUMN_USAGE')
                    ->select('CONSTRAINT_NAME')
                    ->where('TABLE_SCHEMA', $this->db->getDatabase())
                    ->where('TABLE_NAME', 'cbt_kartu_soal_item')
                    ->where('COLUMN_NAME', 'kartu_id')
                    ->where('REFERENCED_TABLE_NAME IS NOT NULL')
                    ->limit(1)
                    ->get();
                $fkExists = $query ? $query->getRow() : null;

                if (!$fkExists) {
                    $this->db->query("DELETE FROM cbt_kartu_soal_item WHERE kartu_id NOT IN (SELECT id_kartu FROM cbt_kartu_soal)");
                    $this->db->query("ALTER TABLE cbt_kartu_soal_item ADD CONSTRAINT fk_item_kartu_soal FOREIGN KEY (kartu_id) REFERENCES cbt_kartu_soal(id_kartu) ON DELETE CASCADE ON UPDATE CASCADE");
                }
            } catch (\Throwable $e) {
                // Silently skip if environment doesn't allow ALTER
            }

            // FK: id_tujuan → e_tujuan_pembelajaran.id_tujuan
            try {
                $query = $this->db->table('information_schema.KEY_COLUMN_USAGE')
                    ->select('CONSTRAINT_NAME')
                    ->where('TABLE_SCHEMA', $this->db->getDatabase())
                    ->where('TABLE_NAME', 'cbt_kartu_soal_item')
                    ->where('COLUMN_NAME', 'id_tujuan')
                    ->where('REFERENCED_TABLE_NAME IS NOT NULL')
                    ->limit(1)
                    ->get();
                $fkTujuanExists = $query ? $query->getRow() : null;

                if (!$fkTujuanExists && $this->db->tableExists('e_tujuan_pembelajaran')) {
                    // Clean up orphan id_tujuan references before adding constraint
                    $this->db->query("UPDATE cbt_kartu_soal_item SET id_tujuan = NULL WHERE id_tujuan IS NOT NULL AND id_tujuan NOT IN (SELECT id_tujuan FROM e_tujuan_pembelajaran)");
                    $this->db->query("ALTER TABLE cbt_kartu_soal_item ADD CONSTRAINT fk_item_tujuan FOREIGN KEY (id_tujuan) REFERENCES e_tujuan_pembelajaran(id_tujuan) ON DELETE SET NULL ON UPDATE CASCADE");
                }
            } catch (\Throwable $e) {
                // Silently skip if environment doesn't allow ALTER
            }
        }
        
        service('cache')->save('cbt_kartu_soal_item_schema_checked', true, 86400 * 30);
    }

    private function migrateTable(): void
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();
        $fields = $db->getFieldNames('cbt_kartu_soal_item');
        $addColumns = [];

        if (!in_array('bank_id', $fields, true)) {
            $addColumns['bank_id'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'nomor',
            ];
        }
        if (!in_array('soal_id', $fields, true)) {
            $addColumns['soal_id'] = [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'after' => 'bank_id',
            ];
        }

        if (!in_array('jenis_soal', $fields, true)) {
            $addColumns['jenis_soal'] = [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'after' => 'nomor',
            ];
        }
        if (!in_array('id_tujuan', $fields, true)) {
            $addColumns['id_tujuan'] = [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'after' => 'jenis_soal',
            ];
        }
        if (!in_array('bentuk_soal', $fields, true)) {
            $addColumns['bentuk_soal'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'id_tujuan',
            ];
        }
        if (!in_array('cp', $fields, true)) {
            $addColumns['cp'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'bentuk_soal',
            ];
        }
        if (!in_array('materi', $fields, true)) {
            $addColumns['materi'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'cp',
            ];
        }
        if (!in_array('tujuan', $fields, true)) {
            $addColumns['tujuan'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'materi',
            ];
        }
        if (!in_array('indikator_soal', $fields, true)) {
            $addColumns['indikator_soal'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'tujuan',
            ];
        }
        if (!in_array('indikator_pencapaian', $fields, true)) {
            $addColumns['indikator_pencapaian'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'indikator_soal',
            ];
        }
        if (!in_array('tingkat_kesulitan', $fields, true)) {
            $addColumns['tingkat_kesulitan'] = [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'after' => 'indikator_pencapaian',
            ];
        }
        if (!in_array('topik', $fields, true)) {
            $addColumns['topik'] = [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'tingkat_kesulitan',
            ];
        }
        if (!in_array('level_kognitif', $fields, true)) {
            $addColumns['level_kognitif'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'topik',
            ];
        }
        if (!in_array('estimasi_waktu', $fields, true)) {
            $addColumns['estimasi_waktu'] = [
                'type' => 'INT',
                'constraint' => 5,
                'null' => true,
                'after' => 'level_kognitif',
            ];
        }
        if (!in_array('catatan', $fields, true)) {
            $addColumns['catatan'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'estimasi_waktu',
            ];
        }
        if (!in_array('butir_soal', $fields, true)) {
            $addColumns['butir_soal'] = [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'catatan',
            ];
        }
        if (!in_array('opsi_json', $fields, true)) {
            $addColumns['opsi_json'] = [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'butir_soal',
            ];
        }
        if (!in_array('jawaban_benar_json', $fields, true)) {
            $addColumns['jawaban_benar_json'] = [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'opsi_json',
            ];
        }
        if (!in_array('pembahasan', $fields, true)) {
            $addColumns['pembahasan'] = [
                'type' => 'LONGTEXT',
                'null' => true,
                'after' => 'jawaban_benar_json',
            ];
        }
        if (!in_array('updated_at', $fields, true)) {
            $addColumns['updated_at'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'created_at',
            ];
        }

        if (!empty($addColumns)) {
            $forge->addColumn('cbt_kartu_soal_item', $addColumns);
        }

        // Fix: Ensure bank_id and soal_id allow NULL (they were NOT NULL in legacy schema)
        // These columns should be nullable because AI-generated items don't have a bank/soal yet
        try {
            $db->query("ALTER TABLE `cbt_kartu_soal_item` MODIFY COLUMN `bank_id` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'Sumber bank soal'");
        } catch (\Throwable $e) {
            // Column may not exist or already correct
        }
        try {
            $db->query("ALTER TABLE `cbt_kartu_soal_item` MODIFY COLUMN `soal_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'Sumber id_soal di bank'");
        } catch (\Throwable $e) {
            // Column may not exist or already correct
        }
    }

    /**
     * Fix legacy NOT NULL constraint on bank_id and soal_id.
     * These columns must allow NULL because AI-generated items don't have a bank/soal yet.
     */
    private function fixNullableColumns($db): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        try {
            // Check if bank_id allows NULL already
            $query = $db->query("SHOW COLUMNS FROM `cbt_kartu_soal_item` WHERE Field = 'bank_id'");
            $result = $query ? $query->getRow() : null;
            if ($result && strtoupper($result->Null ?? '') !== 'YES') {
                // Fix: Column exists but is NOT NULL — make it nullable
                $db->query("UPDATE `cbt_kartu_soal_item` SET `bank_id` = NULL WHERE `bank_id` = 0");
                $db->query("ALTER TABLE `cbt_kartu_soal_item` MODIFY COLUMN `bank_id` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'Sumber bank soal'");
                log_message('info', '[CbtKartuSoalItemModel] Fixed bank_id to allow NULL');
            }
        } catch (\Throwable $e) {
            log_message('debug', '[CbtKartuSoalItemModel::fixNullableColumns] bank_id: ' . $e->getMessage());
        }

        try {
            $query = $db->query("SHOW COLUMNS FROM `cbt_kartu_soal_item` WHERE Field = 'soal_id'");
            $result = $query ? $query->getRow() : null;
            if ($result && strtoupper($result->Null ?? '') !== 'YES') {
                $db->query("UPDATE `cbt_kartu_soal_item` SET `soal_id` = NULL WHERE `soal_id` = 0");
                $db->query("ALTER TABLE `cbt_kartu_soal_item` MODIFY COLUMN `soal_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'Sumber id_soal di bank'");
                log_message('info', '[CbtKartuSoalItemModel] Fixed soal_id to allow NULL');
            }
        } catch (\Throwable $e) {
            log_message('debug', '[CbtKartuSoalItemModel::fixNullableColumns] soal_id: ' . $e->getMessage());
        }
    }

    private function dropColumnSafely($forge, string $table, string $column): void
    {
        try {
            $forge->dropColumn($table, $column);
        } catch (\Throwable $e) {
            // Some legacy schemas keep stale index metadata; skip hard-fail and continue migration.
            log_message('debug', '[CbtKartuSoalItemModel::dropColumnSafely] ' . $column . ': ' . $e->getMessage());
        }
    }

    public function getItems(int $kartuId): array
    {
        return $this->where('kartu_id', $kartuId)->orderBy('nomor', 'ASC')->findAll();
    }

    public function getItemsPaginated(int $kartuId, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $total = $this->where('kartu_id', $kartuId)->countAllResults();
        $items = $this->where('kartu_id', $kartuId)
            ->orderBy('nomor', 'ASC')
            ->findAll($perPage, $offset);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function nextNomor(int $kartuId): int
    {
        $row = $this->selectMax('nomor')->where('kartu_id', $kartuId)->get()->getRow();
        $max = (int) ($row->nomor ?? 0);
        return $max + 1;
    }

    public function getItemsCount(int $kartuId): int
    {
        return $this->where('kartu_id', $kartuId)->countAllResults();
    }

    public function getLinkedTujuanCount(int $kartuId): int
    {
        return $this->where('kartu_id', $kartuId)
            ->where('id_tujuan IS NOT NULL', null, false)
            ->countAllResults();
    }

    /**
     * Count distinct Tujuan Pembelajaran linked in this kartu.
     * More accurate than getLinkedTujuanCount() which counts rows, not unique TPs.
     */
    public function getLinkedTujuanUniqueCount(int $kartuId): int
    {
        $row = $this->db->table('cbt_kartu_soal_item')
            ->select('COUNT(DISTINCT id_tujuan) as c', false)
            ->where('kartu_id', $kartuId)
            ->where('id_tujuan IS NOT NULL', null, false)
            ->get()
            ->getRow();
        return (int) ($row->c ?? 0);
    }

    /**
     * Get sum of estimasi_waktu for all items in a kartu.
     */
    public function getTotalEstimasi(int $kartuId): int
    {
        $row = $this->db->table('cbt_kartu_soal_item')
            ->selectSum('estimasi_waktu', 'total')
            ->where('kartu_id', $kartuId)
            ->get()
            ->getRow();
        return (int) ($row->total ?? 0);
    }

    /**
     * Batch insert multiple items at once (for AI-generated batches).
     * Returns number of rows inserted, or 0 on failure.
     */
    public function batchInsertItems(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }
        // Filter to only allowed fields
        $allowed = $this->allowedFields;
        $clean = [];
        foreach ($rows as $row) {
            $r = [];
            foreach ($allowed as $field) {
                if (array_key_exists($field, $row)) {
                    $r[$field] = $row[$field];
                }
            }
            if (!empty($r)) {
                $clean[] = $r;
            }
        }
        if (empty($clean)) {
            return 0;
        }
        return (int) $this->db->table($this->table)->insertBatch($clean);
    }

    /**
     * Base query butir + JOIN TP & bank soal, dengan filter opsional di level SQL.
     * $filters: ['tujuan_id' => int, 'jenis_soal' => int, 'q' => string]
     */
    private function baseItemsWithTujuanQuery(int $kartuId, array $filters = [], bool $countOnly = false)
    {
        $builder = $this->db->table('cbt_kartu_soal_item');
        if ($countOnly) {
            // COUNT atas primary key agar JOIN yang memperbanyak baris tidak dihitung ganda
            $builder->select('COUNT(DISTINCT cbt_kartu_soal_item.id_item) as c', false);
        } else {
            $builder->select('
            cbt_kartu_soal_item.*,
            cbt_soal.soal as bank_soal_teks,
            cbt_soal.opsi_a as bank_opsi_a,
            cbt_soal.opsi_b as bank_opsi_b,
            cbt_soal.opsi_c as bank_opsi_c,
            cbt_soal.opsi_d as bank_opsi_d,
            cbt_soal.opsi_e as bank_opsi_e,
            cbt_soal.jawaban as bank_soal_jawaban,
            cbt_soal.deskripsi as bank_soal_pembahasan,
            cbt_soal.nomor_soal as bank_soal_nomor,
            cbt_soal.jenis as bank_soal_jenis,
            e_tujuan_pembelajaran.kode as tujuan_kode,
            e_tujuan_pembelajaran.tujuan as tujuan_teks,
            e_tujuan_pembelajaran.fase,
            e_tujuan_pembelajaran.kode_cp as tujuan_kode_cp,
            e_tujuan_pembelajaran.elemen_cp as tujuan_elemen_cp,
            e_tujuan_pembelajaran.materi_pokok as tujuan_materi_pokok,
            e_tujuan_pembelajaran.tp_sikap as tujuan_tp_sikap,
            e_tujuan_pembelajaran.tp_keterampilan as tujuan_tp_keterampilan,
            e_tujuan_pembelajaran.kriteria_ketercapaian as tujuan_kktp,
            e_tujuan_pembelajaran.level_kognitif as tujuan_level_kognitif,
            e_tujuan_pembelajaran.tingkat as tujuan_tingkat
        ');
        }
        $builder->join('cbt_soal', '(cbt_soal.id_soal = cbt_kartu_soal_item.soal_id OR (cbt_kartu_soal_item.soal_id IS NULL AND cbt_soal.id_soal = (SELECT MIN(s2.id_soal) FROM cbt_soal s2 WHERE s2.nomor_soal = cbt_kartu_soal_item.nomor AND s2.jenis = cbt_kartu_soal_item.jenis_soal AND s2.bank_id = COALESCE(cbt_kartu_soal_item.bank_id, (SELECT MIN(b.id_bank) FROM cbt_bank_soal b WHERE b.kartu_soal_id = cbt_kartu_soal_item.kartu_id)))))', 'left');
        $builder->join('e_tujuan_pembelajaran', 'e_tujuan_pembelajaran.id_tujuan = cbt_kartu_soal_item.id_tujuan', 'left');
        $builder->where('cbt_kartu_soal_item.kartu_id', $kartuId);

        $tujuanId = (int) ($filters['tujuan_id'] ?? 0);
        if ($tujuanId > 0) {
            $builder->where('cbt_kartu_soal_item.id_tujuan', $tujuanId);
        }
        $jenisSoal = (int) ($filters['jenis_soal'] ?? 0);
        if ($jenisSoal > 0) {
            $builder->where('cbt_kartu_soal_item.jenis_soal', $jenisSoal);
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $builder->groupStart()
                ->like('cbt_kartu_soal_item.materi', $q)
                ->orLike('cbt_kartu_soal_item.indikator_soal', $q)
                ->orLike('cbt_kartu_soal_item.cp', $q)
                ->orLike('cbt_kartu_soal_item.tujuan', $q)
                ->orLike('e_tujuan_pembelajaran.tujuan', $q);
            if (ctype_digit($q)) {
                $builder->orWhere('cbt_kartu_soal_item.nomor', (int) $q);
            }
            $builder->groupEnd();
        }

        return $builder;
    }

    public function getItemsWithTujuan(int $kartuId, array $filters = [], ?int $limit = null, ?int $offset = null): array
    {
        $builder = $this->baseItemsWithTujuanQuery($kartuId, $filters);
        $builder->orderBy('cbt_kartu_soal_item.nomor', 'ASC');
        if ($limit !== null && $limit > 0) {
            $builder->limit($limit, max(0, (int) ($offset ?? 0)));
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Hitung total baris untuk filter yang sama (pasangan pagination dari getItemsWithTujuan).
     */
    public function countItemsWithTujuan(int $kartuId, array $filters = []): int
    {
        $builder = $this->baseItemsWithTujuanQuery($kartuId, $filters, true);
        $row = $builder->get()->getRow();
        return (int) ($row->c ?? 0);
    }

    public function getItemsGroupedByTujuan(int $kartuId): array
    {
        $items = $this->getItemsWithTujuan($kartuId);
        $grouped = [];

        foreach ($items as $item) {
            $tujuanId = $item['id_tujuan'] ?? 0;
            if (!isset($grouped[$tujuanId])) {
                $grouped[$tujuanId] = [
                    'tujuan_kode' => $item['tujuan_kode'] ?? 'Tidak Terkait',
                    'tujuan_teks' => $item['tujuan_teks'] ?? 'Tidak ada tujuan pembelajaran',
                    'items' => [],
                ];
            }
            $grouped[$tujuanId]['items'][] = $item;
        }

        return $grouped;
    }

    public function getKartuSummary(int $kartuId): array
    {
        $builder = $this->db->table($this->table)
            ->select('jenis_soal, count(*) as jml_soal')
            ->where('kartu_id', $kartuId)
            ->groupBy('jenis_soal');
        
        $result = $builder->get()->getResult();
        $summary = [];
        foreach ($result as $row) {
            $summary[$row->jenis_soal] = $row->jml_soal;
        }
        return $summary;
    }

    /**
     * Check if a kartu soal item (butir soal) is being used in bank soal.
     * An item is considered "used" if:
     * 1. It has bank_id and soal_id pointing to an existing cbt_soal record
     * 2. That cbt_soal record exists and belongs to a valid bank
     * 
     * @param int $itemId The kartu soal item ID
     * @return array Returns ['used' => bool, 'bank_info' => array|null]
     */
    public function isUsedInBankSoal(int $itemId): array
    {
        if ($itemId <= 0) {
            return ['used' => false, 'bank_info' => null];
        }

        $item = $this->find($itemId);
        if (!$item) {
            return ['used' => false, 'bank_info' => null];
        }

        // If no bank_id or soal_id, it's not linked to any bank soal
        if (empty($item->bank_id) || empty($item->soal_id)) {
            return ['used' => false, 'bank_info' => null];
        }

        $bankId = (int) $item->bank_id;
        $soalId = (int) $item->soal_id;

        // Check if the soal exists in cbt_soal table
        if (!$this->db->tableExists('cbt_soal')) {
            return ['used' => false, 'bank_info' => null];
        }

        $soal = $this->db->table('cbt_soal')
            ->where('id_soal', $soalId)
            ->where('bank_id', $bankId)
            ->get()
            ->getRow();

        if (!$soal) {
            return ['used' => false, 'bank_info' => null];
        }

        // Get bank info
        $bankInfo = null;
        if ($this->db->tableExists('cbt_bank_soal')) {
            $bank = $this->db->table('cbt_bank_soal')
                ->select('id_bank, bank_kode, bank_nama')
                ->where('id_bank', $bankId)
                ->where('status', 1)
                ->get()
                ->getRow();
            if ($bank) {
                $bankInfo = [
                    'id_bank' => $bank->id_bank,
                    'kode' => $bank->bank_kode ?? '-',
                    'nama' => $bank->bank_nama ?? '-',
                ];
            }
        }

        // Also check cbt_bank table
        if (!$bankInfo && $this->db->tableExists('cbt_bank')) {
            $bank = $this->db->table('cbt_bank')
                ->select('id_bank, bank_kode, bank_nama')
                ->where('id_bank', $bankId)
                ->where('status', 1)
                ->get()
                ->getRow();
            if ($bank) {
                $bankInfo = [
                    'id_bank' => $bank->id_bank,
                    'kode' => $bank->bank_kode ?? '-',
                    'nama' => $bank->bank_nama ?? '-',
                ];
            }
        }

        return [
            'used' => true,
            'bank_info' => $bankInfo,
            'soal_id' => $soalId,
            'bank_id' => $bankId,
        ];
    }

    /**
     * Get usage status for multiple items at once (batch query, no N+1).
     * Returns array keyed by item ID.
     */
    public function getBulkUsageStatus(array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        $itemIds = array_map('intval', $itemIds);
        $result = [];
        foreach ($itemIds as $id) {
            $result[$id] = ['used' => false, 'bank_info' => null];
        }

        // 1. Fetch all items with bank_id & soal_id in one query
        $items = $this->db->table($this->table)
            ->select('id_item, bank_id, soal_id')
            ->whereIn('id_item', $itemIds)
            ->where('bank_id IS NOT NULL')
            ->where('soal_id IS NOT NULL')
            ->get()
            ->getResult();

        if (empty($items)) {
            return $result;
        }

        $soalIds = array_map(fn($i) => (int) $i->soal_id, $items);
        $bankIds = array_map(fn($i) => (int) $i->bank_id, $items);

        // 2. Check which soal_ids actually exist in cbt_soal in one query
        $existingSoalIds = [];
        if ($this->db->tableExists('cbt_soal')) {
            $existingSoalIds = array_map(
                fn($r) => (int) $r->id_soal,
                $this->db->table('cbt_soal')
                    ->select('id_soal')
                    ->whereIn('id_soal', $soalIds)
                    ->whereIn('bank_id', $bankIds)
                    ->get()
                    ->getResult()
            );
        }

        // 3. Get bank info for all banks in one query
        $bankInfoMap = [];
        $bankTable = null;
        if ($this->db->tableExists('cbt_bank_soal')) {
            $bankTable = 'cbt_bank_soal';
        } elseif ($this->db->tableExists('cbt_bank')) {
            $bankTable = 'cbt_bank';
        }

        if ($bankTable) {
            $banks = $this->db->table($bankTable)
                ->select('id_bank, bank_kode, bank_nama')
                ->whereIn('id_bank', $bankIds)
                ->where('status', 1)
                ->get()
                ->getResult();
            foreach ($banks as $b) {
                $bankInfoMap[(int) $b->id_bank] = [
                    'id_bank' => (int) $b->id_bank,
                    'kode' => $b->bank_kode ?? '-',
                    'nama' => $b->bank_nama ?? '-',
                ];
            }
        }

        // 4. Build result
        foreach ($items as $item) {
            $idItem = (int) $item->id_item;
            $soalId = (int) $item->soal_id;
            $bankId = (int) $item->bank_id;

            if (in_array($soalId, $existingSoalIds, true)) {
                $result[$idItem] = [
                    'used' => true,
                    'bank_info' => $bankInfoMap[$bankId] ?? null,
                    'soal_id' => $soalId,
                    'bank_id' => $bankId,
                ];
            }
        }

        return $result;
    }
}