<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureGuruNilaiSchema extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();

        if (!$this->db->tableExists('nilai_proses')) {
            $forge->addField([
                'id_nilai_proses' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11],
                'id_tp' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
                'id_guru' => ['type' => 'INT', 'constraint' => 11],
                'id_tahun' => ['type' => 'INT', 'constraint' => 11],
                'id_smt' => ['type' => 'INT', 'constraint' => 11],
                'tipe' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'sumatif_lm'],
                'komponen_penilaian' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pengetahuan'],
                'kompetensi_dasar' => ['type' => 'TEXT', 'null' => true],
                'capaian_pembelajaran' => ['type' => 'TEXT', 'null' => true],
                'indikator_pencapaian' => ['type' => 'TEXT', 'null' => true],
                'nilai' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'nilai_skala_4' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0],
                'sync_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
                'last_sync_at' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_nilai_proses', true);
            $forge->addKey(['id_siswa', 'id_mapel', 'id_tp', 'tipe'], false, false, 'idx_nilai_proses_scope');
            $forge->addKey(['id_mapel', 'id_tahun', 'id_smt'], false, false, 'idx_nilai_proses_period');
            $forge->createTable('nilai_proses', true);
        }

        if (!$this->db->tableExists('nilai_akhir_kurmer')) {
            $forge->addField([
                'id_nilai_akhir' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'id_siswa' => ['type' => 'INT', 'constraint' => 11],
                'id_mapel' => ['type' => 'INT', 'constraint' => 11],
                'id_guru' => ['type' => 'INT', 'constraint' => 11],
                'id_tahun' => ['type' => 'INT', 'constraint' => 11],
                'id_smt' => ['type' => 'INT', 'constraint' => 11],
                'nilai_sas' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'nilai_rapor' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'bobot_proses' => ['type' => 'INT', 'constraint' => 3, 'default' => 50],
                'bobot_sas' => ['type' => 'INT', 'constraint' => 3, 'default' => 50],
                'nilai_pengetahuan' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'nilai_keterampilan' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'nilai_sikap' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
                'nilai_rapor_skala_4' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0],
                'predikat_skala_4' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
                'kkm' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 75],
                'sync_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
                'last_sync_at' => ['type' => 'DATETIME', 'null' => true],
                'deskripsi_tinggi' => ['type' => 'TEXT', 'null' => true],
                'deskripsi_rendah' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_nilai_akhir', true);
            $forge->addKey(['id_siswa', 'id_mapel', 'id_tahun', 'id_smt'], false, true, 'uq_nilai_akhir_scope');
            $forge->addKey(['id_mapel', 'id_tahun', 'id_smt'], false, false, 'idx_nilai_akhir_period');
            $forge->createTable('nilai_akhir_kurmer', true);
        }

        $this->addColumnIfMissing($forge, 'nilai_proses', 'komponen_penilaian', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pengetahuan']);
        $this->addColumnIfMissing($forge, 'nilai_proses', 'kompetensi_dasar', ['type' => 'TEXT', 'null' => true]);
        $this->addColumnIfMissing($forge, 'nilai_proses', 'capaian_pembelajaran', ['type' => 'TEXT', 'null' => true]);
        $this->addColumnIfMissing($forge, 'nilai_proses', 'indikator_pencapaian', ['type' => 'TEXT', 'null' => true]);
        $this->addColumnIfMissing($forge, 'nilai_proses', 'nilai_skala_4', ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0]);
        $this->addColumnIfMissing($forge, 'nilai_proses', 'sync_status', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending']);
        $this->addColumnIfMissing($forge, 'nilai_proses', 'last_sync_at', ['type' => 'DATETIME', 'null' => true]);

        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_pengetahuan', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0]);
        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_keterampilan', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0]);
        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_sikap', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0]);
        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'nilai_rapor_skala_4', ['type' => 'DECIMAL', 'constraint' => '4,2', 'default' => 0]);
        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'predikat_skala_4', ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true]);
        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'kkm', ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 75]);
        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'sync_status', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending']);
        $this->addColumnIfMissing($forge, 'nilai_akhir_kurmer', 'last_sync_at', ['type' => 'DATETIME', 'null' => true]);

        if (!$this->db->tableExists('nilai_sync_log')) {
            $forge->addField([
                'id_sync' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'entity' => ['type' => 'VARCHAR', 'constraint' => 30],
                'entity_id' => ['type' => 'VARCHAR', 'constraint' => 80],
                'mode' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'realtime'],
                'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'queued'],
                'payload' => ['type' => 'LONGTEXT', 'null' => true],
                'response' => ['type' => 'LONGTEXT', 'null' => true],
                'attempts' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $forge->addKey('id_sync', true);
            $forge->addKey(['entity', 'status'], false, false, 'idx_nilai_sync_entity_status');
            $forge->createTable('nilai_sync_log', true);
        }
    }

    public function down()
    {
        // Keep schema/data on rollback to avoid destructive downgrade on production.
    }

    private function addColumnIfMissing(\CodeIgniter\Database\Forge $forge, string $table, string $column, array $definition): void
    {
        if (!$this->db->tableExists($table)) {
            return;
        }
        if ($this->db->fieldExists($column, $table)) {
            return;
        }
        $forge->addColumn($table, [$column => $definition]);
    }
}

