<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerangkatSebaranMateri extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('perangkat_sebaran_materi')) {
            // Tabel sudah ada — tambahkan UNIQUE constraint pada kode_sebaran jika belum ada
            if (!$this->db->indexExists('perangkat_sebaran_materi', 'uq_kode_sebaran')) {
                $this->db->query("ALTER TABLE `perangkat_sebaran_materi` ADD UNIQUE KEY `uq_kode_sebaran` (`kode_sebaran`)");
            }
            return;
        }

        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kode_sebaran'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'unique' => true],
            'guru_id'               => ['type' => 'INT', 'default' => 0],
            'mapel_id'              => ['type' => 'INT', 'null' => false],
            'kelas_id'              => ['type' => 'INT', 'null' => true],
            'id_tp'                 => ['type' => 'INT', 'null' => false],
            'id_smt'                => ['type' => 'INT', 'null' => false],
            'fase'                  => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'E'],
            'tingkat'               => ['type' => 'INT', 'default' => 10],
            'nama_dokumen'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'jp_per_minggu'         => ['type' => 'INT', 'default' => 4],
            'total_minggu_semester' => ['type' => 'INT', 'default' => 18],
            'minggu_efektif'        => ['type' => 'INT', 'default' => 16],
            'minggu_tidak_efektif'  => ['type' => 'INT', 'default' => 2],
            'jp_total'              => ['type' => 'INT', 'default' => 72],
            'jp_tidak_efektif'      => ['type' => 'INT', 'default' => 8],
            'jp_efektif'            => ['type' => 'INT', 'default' => 64],
            'buffer_percent'        => ['type' => 'INT', 'default' => 10],
            'buffer_jp'             => ['type' => 'INT', 'default' => 6],
            'rpe_data'              => ['type' => 'LONGTEXT', 'null' => true],
            'materi_units'          => ['type' => 'LONGTEXT', 'null' => true],
            'alokasi_summary'       => ['type' => 'LONGTEXT', 'null' => true],
            'catatan_kurikulum'     => ['type' => 'TEXT', 'null' => true],
            'status'                => ['type' => "ENUM('draft','final','published')", 'default' => "'draft'"],
            'ai_generated'          => ['type' => 'TINYINT', 'default' => 0, 'constraint' => 1],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_sebaran', false, true); // UNIQUE
        $this->forge->addKey(['guru_id', 'id_tp', 'id_smt'], false, false, 'idx_guru_tp_smt');
        $this->forge->addKey(['mapel_id', 'tingkat'], false, false, 'idx_mapel_tingkat');
        $this->forge->createTable('perangkat_sebaran_materi');
    }

    public function down()
    {
        $this->forge->dropTable('perangkat_sebaran_materi', true);
    }
}
