<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi inti sistem Tryout TKA Kemendikdasmen
 *
 * Tabel yang dibuat:
 * 1. tka_paket       — Paket tryout TKA (1 paket = 5 sub-tes)
 * 2. tka_subtes      — Konfigurasi sub-tes per paket (link ke bank soal)
 * 3. tka_sesi_siswa  — Sesi ujian siswa per paket
 * 4. tka_subtes_sesi — Status pengerjaan per sub-tes per siswa
 * 5. tka_nilai       — Nilai final TKA per siswa per paket (mentah + terstandar)
 */
class CreateTkaCoreTables extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────────
        // 1. tka_paket — header paket tryout TKA
        // ─────────────────────────────────────────────────────────────────
        if (!$this->db->tableExists('tka_paket')) {
            $this->forge->addField([
                'id_paket' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'kode_paket' => ['type' => 'VARCHAR', 'constraint' => 50, 'comment' => 'Kode unik paket, mis. TKA-2026-01'],
                'nama_paket' => ['type' => 'VARCHAR', 'constraint' => 255],
                'deskripsi'  => ['type' => 'TEXT', 'null' => true],
                'tahun_ajaran' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'comment' => 'Mis. 2026/2027'],
                'id_tp'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'id_smt'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'tgl_mulai'  => ['type' => 'DATETIME', 'null' => true],
                'tgl_selesai'=> ['type' => 'DATETIME', 'null' => true],
                // Konfigurasi ujian
                'total_durasi_menit' => ['type' => 'INT', 'constraint' => 11, 'default' => 195, 'comment' => 'Total durasi semua sub-tes (menit)'],
                'istirahat_antar_subtes' => ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'comment' => 'Jeda (menit) antara sub-tes'],
                'acak_soal'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'acak_opsi'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'gunakan_token' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'token_value'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'tampil_hasil'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'comment' => '1=langsung tampil setelah selesai'],
                // Target kelas
                'level'         => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'comment' => 'Level kelas sasaran, mis. XII'],
                'kelas_ids'     => ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON array id_kelas yang bisa ikut'],
                // Status
                'status'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'comment' => '0=draft, 1=aktif, 2=selesai'],
                'mode_penilaian'=> ['type' => 'ENUM', 'constraint' => ['raw', 'scaled', 'irt'], 'default' => 'scaled',
                                    'comment' => 'raw=persentase, scaled=Z-score 200-800, irt=IRT'],
                'created_by'    => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'created_at'    => ['type' => 'DATETIME', 'null' => true],
                'updated_at'    => ['type' => 'DATETIME', 'null' => true],
                'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id_paket', true);
            $this->forge->addUniqueKey('kode_paket');
            $this->forge->addKey(['id_tp', 'id_smt']);
            $this->forge->addKey('status');
            $this->forge->createTable('tka_paket', true);
        }

        // ─────────────────────────────────────────────────────────────────
        // 2. tka_subtes — konfigurasi sub-tes dalam satu paket
        // ─────────────────────────────────────────────────────────────────
        if (!$this->db->tableExists('tka_subtes')) {
            $this->forge->addField([
                'id_subtes'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_paket'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'urutan'      => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 1,
                                  'comment' => 'Urutan pengerjaan: 1=pertama, 5=terakhir'],
                'kode_subtes' => ['type' => 'ENUM',
                                  'constraint' => ['BIN', 'MTK', 'BING', 'FIS', 'KIM', 'BIO', 'GEO', 'EKO', 'SEJ', 'SOS', 'PPN', 'INF', 'KEAHLIAN', 'GENERAL', 'VERBAL', 'KUANTITATIF', 'LOGIS', 'LITERASI', 'NUMERASI'],
                                  'default' => 'GENERAL'],
                'nama_subtes' => ['type' => 'VARCHAR', 'constraint' => 150, 'comment' => 'Mis. Penalaran Verbal'],
                'deskripsi'   => ['type' => 'TEXT', 'null' => true],
                'id_bank'     => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'comment' => 'FK → cbt_bank_soal'],
                'jumlah_soal' => ['type' => 'INT', 'constraint' => 11, 'default' => 0, 'comment' => 'Jumlah soal tampil'],
                'durasi_menit'=> ['type' => 'INT', 'constraint' => 11, 'default' => 30, 'comment' => 'Durasi sub-tes (menit)'],
                'bobot_persen'=> ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 20.00,
                                  'comment' => 'Bobot sub-tes thd nilai komposit (total 100)'],
                'skor_min_tampil' => ['type' => 'INT', 'constraint' => 11, 'default' => 200],
                'skor_max_tampil' => ['type' => 'INT', 'constraint' => 11, 'default' => 800],
            ]);
            $this->forge->addKey('id_subtes', true);
            $this->forge->addKey('id_paket');
            $this->forge->addKey(['id_paket', 'urutan']);
            $this->forge->createTable('tka_subtes', true);
        }

        // ─────────────────────────────────────────────────────────────────
        // 3. tka_sesi_siswa — header sesi ujian per siswa
        // ─────────────────────────────────────────────────────────────────
        if (!$this->db->tableExists('tka_sesi_siswa')) {
            $this->forge->addField([
                'id_sesi'    => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'id_paket'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_siswa'   => ['type' => 'INT', 'constraint' => 11],
                'mulai'      => ['type' => 'DATETIME', 'null' => true],
                'selesai'    => ['type' => 'DATETIME', 'null' => true],
                'lama_ujian' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'status'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0,
                                 'comment' => '0=belum mulai, 1=berjalan, 2=selesai, 9=dibatalkan'],
                'subtes_aktif' => ['type' => 'INT', 'constraint' => 11, 'null' => true,
                                   'comment' => 'id_subtes yang sedang dikerjakan'],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
                'user_agent' => ['type' => 'TEXT', 'null' => true],
                'pelanggaran'=> ['type' => 'TEXT', 'null' => true, 'comment' => 'JSON {ringan,sedang,berat}'],
            ]);
            $this->forge->addKey('id_sesi', true);
            $this->forge->addUniqueKey(['id_paket', 'id_siswa'], 'uq_tka_sesi_paket_siswa');
            $this->forge->addKey('id_siswa');
            $this->forge->addKey(['status', 'id_paket']);
            $this->forge->createTable('tka_sesi_siswa', true);
        }

        // ─────────────────────────────────────────────────────────────────
        // 4. tka_subtes_sesi — progress per sub-tes per siswa
        // ─────────────────────────────────────────────────────────────────
        if (!$this->db->tableExists('tka_subtes_sesi')) {
            $this->forge->addField([
                'id'           => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
                'id_sesi'      => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
                'id_subtes'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_paket'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_siswa'     => ['type' => 'INT', 'constraint' => 11],
                'mulai_subtes' => ['type' => 'DATETIME', 'null' => true],
                'selesai_subtes' => ['type' => 'DATETIME', 'null' => true],
                'status'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0,
                                   'comment' => '0=belum, 1=berjalan, 2=selesai'],
                'soal_order'   => ['type' => 'TEXT', 'null' => true,
                                   'comment' => 'JSON array id_soal terurut (acak per siswa, fixed setelah mulai)'],
                'jawaban_count'=> ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                // Timer server-side
                'sisa_detik'   => ['type' => 'INT', 'constraint' => 11, 'null' => true,
                                   'comment' => 'Sisa waktu terakhir (diupdate saat pindah sub-tes)'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['id_sesi', 'id_subtes'], 'uq_tka_subtes_sesi');
            $this->forge->addKey(['id_siswa', 'id_paket']);
            $this->forge->addKey('id_sesi');
            $this->forge->createTable('tka_subtes_sesi', true);
        }

        // ─────────────────────────────────────────────────────────────────
        // 5. tka_nilai — nilai final TKA per siswa per paket
        // ─────────────────────────────────────────────────────────────────
        if (!$this->db->tableExists('tka_nilai')) {
            $this->forge->addField([
                'id_nilai'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'id_paket'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'id_siswa'     => ['type' => 'INT', 'constraint' => 11],
                // Skor mentah per sub-tes (jumlah benar / total soal × 100)
                'verbal_raw'       => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'kuantitatif_raw'  => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'logis_raw'        => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'literasi_raw'     => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'numerasi_raw'     => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                // Skor terstandar per sub-tes (skala 200-800, mean=500 SD=100)
                'verbal_scaled'      => ['type' => 'DECIMAL', 'constraint' => '6,1', 'default' => 0],
                'kuantitatif_scaled' => ['type' => 'DECIMAL', 'constraint' => '6,1', 'default' => 0],
                'logis_scaled'       => ['type' => 'DECIMAL', 'constraint' => '6,1', 'default' => 0],
                'literasi_scaled'    => ['type' => 'DECIMAL', 'constraint' => '6,1', 'default' => 0],
                'numerasi_scaled'    => ['type' => 'DECIMAL', 'constraint' => '6,1', 'default' => 0],
                // Nilai komposit
                'skor_komposit'    => ['type' => 'DECIMAL', 'constraint' => '6,1', 'default' => 0,
                                       'comment' => 'Weighted average skor terstandar'],
                'skor_komposit_raw'=> ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0,
                                       'comment' => 'Weighted average skor mentah'],
                // Statistik populasi (diisi ulang saat semua peserta selesai)
                'percentile'    => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true],
                'stanine'       => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
                't_score'       => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true],
                'rank_nasional' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                // Detail JSON lengkap
                'detail_json'   => ['type' => 'LONGTEXT', 'null' => true,
                                    'comment' => 'JSON lengkap: benar/salah/kosong per sub-tes'],
                'calculated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id_nilai', true);
            $this->forge->addUniqueKey(['id_paket', 'id_siswa'], 'uq_tka_nilai_paket_siswa');
            $this->forge->addKey('id_siswa');
            $this->forge->addKey('skor_komposit');
            $this->forge->createTable('tka_nilai', true);
        }
    }

    public function down(): void
    {
        foreach (['tka_nilai', 'tka_subtes_sesi', 'tka_sesi_siswa', 'tka_subtes', 'tka_paket'] as $tbl) {
            $this->forge->dropTable($tbl, true);
        }
    }
}
