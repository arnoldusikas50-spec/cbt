<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sprint 1 & 2 — Fondasi Item Analysis & Kualitas Bank Soal
 *
 * Migrasi ini menambahkan:
 * 1. Tabel `e_capaian_pembelajaran` — master CP BSKAP (kode CP tervalidasi)
 * 2. Kolom analisis empiris di `cbt_soal`  (stat_p, stat_d, soal_hash, tag_kesulitan)
 * 3. Kolom approval kepsek di `cbt_kartu_soal`
 */
class AddItemAnalysisToSystem extends Migration
{
    public function up(): void
    {
        $this->buatTabelCapaianPembelajaran();
        $this->tambahKolomCbtSoal();
        $this->tambahKolomKartuSoal();
    }

    // ------------------------------------------------------------------
    // 1. Tabel e_capaian_pembelajaran
    // ------------------------------------------------------------------
    private function buatTabelCapaianPembelajaran(): void
    {
        if ($this->db->tableExists('e_capaian_pembelajaran')) {
            return;
        }

        $this->forge->addField([
            'id_cp' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode_cp' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'comment'    => 'Kode unik CP, contoh: B.IND.D.1',
            ],
            'mata_pelajaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'fase' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'comment'    => 'A=SD1-2, B=SD3-4, C=SD5-6, D=SMP, E=SMA-X, F=SMA-XI-XII',
            ],
            'elemen' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Domain/elemen CP, misal: Membaca, Geometri, Teks',
            ],
            'deskripsi_cp' => [
                'type' => 'TEXT',
            ],
            'jenjang' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 2,
                'comment'    => '1=SD 2=SMP 3=SMA 4=SMK',
            ],
            'kelompok_mapel' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Umum/Kejuruan/Pilihan',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_cp', true);
        $this->forge->addUniqueKey('kode_cp');
        $this->forge->addKey(['fase', 'jenjang']);
        $this->forge->addKey('mata_pelajaran');
        $this->forge->createTable('e_capaian_pembelajaran', true);
    }

    // ------------------------------------------------------------------
    // 2. Kolom baru di cbt_soal
    // ------------------------------------------------------------------
    private function tambahKolomCbtSoal(): void
    {
        if (!$this->db->tableExists('cbt_soal')) {
            return;
        }

        $existing = $this->db->getFieldNames('cbt_soal');

        $kolom = [
            'soal_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
                'after'      => 'jawaban',
                'comment'    => 'MD5 bag-of-words untuk deteksi duplikat lintas bank',
            ],
            'stat_p' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,3',
                'null'       => true,
                'after'      => 'soal_hash',
                'comment'    => 'Indeks kesulitan empiris (0.000-1.000)',
            ],
            'stat_d' => [
                'type'       => 'DECIMAL',
                'constraint' => '4,3',
                'null'       => true,
                'after'      => 'stat_p',
                'comment'    => 'Daya beda empiris (-1.000-1.000)',
            ],
            'stat_used_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'stat_d',
                'comment'    => 'Jumlah ujian yang memakai soal ini',
            ],
            'stat_last_calc' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'stat_used_count',
                'comment' => 'Timestamp terakhir item analysis dihitung',
            ],
            'tag_kesulitan' => [
                'type'       => 'ENUM',
                'constraint' => ['mudah', 'sedang', 'sulit'],
                'null'       => true,
                'after'      => 'stat_last_calc',
                'comment'    => 'Tag otomatis dari data empiris ujian',
            ],
        ];

        $toAdd = [];
        foreach ($kolom as $nama => $def) {
            if (!in_array($nama, $existing, true)) {
                $toAdd[$nama] = $def;
            }
        }

        if (!empty($toAdd)) {
            $this->forge->addColumn('cbt_soal', $toAdd);
        }

        // Tambah index jika belum ada
        $this->addIndexSafe('cbt_soal', 'idx_soal_hash',    'ALTER TABLE cbt_soal ADD INDEX idx_soal_hash (soal_hash)');
        $this->addIndexSafe('cbt_soal', 'idx_soal_stat',    'ALTER TABLE cbt_soal ADD INDEX idx_soal_stat (stat_p, stat_d)');
        $this->addIndexSafe('cbt_soal', 'idx_soal_tag',     'ALTER TABLE cbt_soal ADD INDEX idx_soal_tag (tag_kesulitan)');
    }

    // ------------------------------------------------------------------
    // 3. Kolom baru di cbt_kartu_soal (approval kepsek)
    // ------------------------------------------------------------------
    private function tambahKolomKartuSoal(): void
    {
        if (!$this->db->tableExists('cbt_kartu_soal')) {
            return;
        }

        $existing = $this->db->getFieldNames('cbt_kartu_soal');

        $kolom = [
            'approval_level' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'approval_note',
                'comment'    => '1=panitia cukup, 2=wajib persetujuan kepsek',
            ],
            'kepsek_approved_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'approval_level',
            ],
            'kepsek_approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'kepsek_approved_at',
            ],
            'kepsek_note' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'kepsek_approved_by',
            ],
        ];

        $toAdd = [];
        foreach ($kolom as $nama => $def) {
            if (!in_array($nama, $existing, true)) {
                $toAdd[$nama] = $def;
            }
        }

        if (!empty($toAdd)) {
            $this->forge->addColumn('cbt_kartu_soal', $toAdd);
        }
    }

    // ------------------------------------------------------------------
    // Helper: tambah index hanya jika belum ada
    // ------------------------------------------------------------------
    private function addIndexSafe(string $table, string $indexName, string $sql): void
    {
        try {
            $row = $this->db->table('information_schema.STATISTICS')
                ->select('INDEX_NAME')
                ->where('TABLE_SCHEMA', $this->db->getDatabase())
                ->where('TABLE_NAME', $table)
                ->where('INDEX_NAME', $indexName)
                ->limit(1)
                ->get()
                ->getRow();

            if (!$row) {
                $this->db->query($sql);
            }
        } catch (\Throwable $e) {
            // SQLite atau tidak ada izin ALTER — lewati
            log_message('debug', "[Migration:AddItemAnalysis] index {$indexName}: " . $e->getMessage());
        }
    }

    public function down(): void
    {
        // Hapus tabel CP (aman karena data bisa di-seed ulang)
        $this->forge->dropTable('e_capaian_pembelajaran', true);

        // Hapus kolom-kolom tambahan di cbt_soal
        if ($this->db->tableExists('cbt_soal')) {
            $existing = $this->db->getFieldNames('cbt_soal');
            $drop = ['soal_hash', 'stat_p', 'stat_d', 'stat_used_count', 'stat_last_calc', 'tag_kesulitan'];
            foreach ($drop as $col) {
                if (in_array($col, $existing, true)) {
                    $this->forge->dropColumn('cbt_soal', $col);
                }
            }
        }

        // Hapus kolom approval kepsek di cbt_kartu_soal
        if ($this->db->tableExists('cbt_kartu_soal')) {
            $existing = $this->db->getFieldNames('cbt_kartu_soal');
            $drop = ['approval_level', 'kepsek_approved_at', 'kepsek_approved_by', 'kepsek_note'];
            foreach ($drop as $col) {
                if (in_array($col, $existing, true)) {
                    $this->forge->dropColumn('cbt_kartu_soal', $col);
                }
            }
        }
    }
}
