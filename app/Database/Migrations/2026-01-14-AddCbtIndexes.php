<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add indexes for CBT tables to improve performance
 * Critical for 1000+ concurrent users
 */
class AddCbtIndexes extends Migration
{
    public function up()
    {
        // Index for cbt_jawaban - Most critical table
        $this->forge->addKey(['id_siswa', 'id_jadwal'], false, false, 'idx_jawaban_siswa_jadwal');
        $this->forge->addKey(['id_siswa', 'id_jadwal', 'id_soal'], false, false, 'idx_jawaban_lookup');

        // Run raw SQL for existing table
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_jawaban_siswa_jadwal ON cbt_jawaban (id_siswa, id_jadwal)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_jawaban_lookup ON cbt_jawaban (id_siswa, id_jadwal, id_soal)');

        // Index for cbt_siswa
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_cbtsiswa_siswa_jadwal ON cbt_siswa (id_siswa, id_jadwal)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_cbtsiswa_status ON cbt_siswa (status)');

        // Index for cbt_nilai
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_nilai_siswa_jadwal ON cbt_nilai (id_siswa, id_jadwal)');

        // Index for cbt_durasi_siswa
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_durasi_siswa_jadwal ON cbt_durasi_siswa (id_siswa, id_jadwal)');

        // Index for cbt_jadwal
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_jadwal_tp_smt ON cbt_jadwal (id_tp, id_smt)');
        $this->db->query('CREATE INDEX IF NOT EXISTS idx_jadwal_status ON cbt_jadwal (status_ujian)');
    }

    public function down()
    {
        // Drop indexes
        $this->db->query('DROP INDEX IF EXISTS idx_jawaban_siswa_jadwal ON cbt_jawaban');
        $this->db->query('DROP INDEX IF EXISTS idx_jawaban_lookup ON cbt_jawaban');
        $this->db->query('DROP INDEX IF EXISTS idx_cbtsiswa_siswa_jadwal ON cbt_siswa');
        $this->db->query('DROP INDEX IF EXISTS idx_cbtsiswa_status ON cbt_siswa');
        $this->db->query('DROP INDEX IF EXISTS idx_nilai_siswa_jadwal ON cbt_nilai');
        $this->db->query('DROP INDEX IF EXISTS idx_durasi_siswa_jadwal ON cbt_durasi_siswa');
        $this->db->query('DROP INDEX IF EXISTS idx_jadwal_tp_smt ON cbt_jadwal');
        $this->db->query('DROP INDEX IF EXISTS idx_jadwal_status ON cbt_jadwal');
    }
}
