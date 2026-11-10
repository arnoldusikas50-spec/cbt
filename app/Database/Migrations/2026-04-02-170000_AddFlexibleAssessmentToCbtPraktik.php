<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFlexibleAssessmentToCbtPraktik extends Migration
{
    public function up()
    {
        // 1. Tambah kolom untuk assessment dimension pada rubrik
        $this->forge->addColumn('cbt_praktik_rubrik', [
            'dimensi_penilaian' => [
                'type' => 'ENUM',
                'constraint' => ['keterampilan_praktis', 'proses_kerja', 'pemahaman_konsep', 'kreativitas', 'kolaborasi', 'komunikasi', 'hasil_akhir'],
                'default' => 'hasil_akhir',
                'after' => 'tipe_penilaian'
            ],
            'tahap_penilaian' => [
                'type' => 'ENUM',
                'constraint' => ['pra_praktik', 'selama_praktik', 'pasca_praktik', 'keseluruhan'],
                'default' => 'keseluruhan',
                'after' => 'dimensi_penilaian'
            ],
            'izinkan_revisi' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'tahap_penilaian'
            ],
            'maksimum_revisi' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'izinkan_revisi'
            ],
        ]);

        // 2. Tabel Penilaian Berkelanjutan (Continuous Assessment)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'rubrik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'level_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nilai_sementara' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'catatan_penilaian' => ['type' => 'TEXT', 'null' => true],
            'tipe_penilaian' => ['type' => 'ENUM', 'constraint' => ['formatif', 'sumatif', 'diagnostik'], 'default' => 'formatif'],
            'dinilai_oleh' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'waktu_penilaian' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['hasil_id', 'rubrik_id']);
        $this->forge->createTable('cbt_praktik_penilaian_berkelanjutan', true);

        // 3. Tabel Umpan Balik Konstruktif (Real-time Feedback)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'dari_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false], // Guru/Pengawas
            'tipe_umpan_balik' => ['type' => 'ENUM', 'constraint' => ['positif', 'perbaikan', 'pertanyaan', 'saran', 'motivasi'], 'default' => 'saran'],
            'isi_umpan_balik' => ['type' => 'TEXT'],
            'konteks' => ['type' => 'TEXT', 'null' => true], // JSON: bagian mana yang dinilai
            'telah_dibaca' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'dibalas_oleh_siswa' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'balasan_siswa' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['hasil_id', 'telah_dibaca']);
        $this->forge->createTable('cbt_praktik_umpan_balik', true);

        // 4. Tabel Portofolio (Project-Based Collection)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'judul_item' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'deskripsi_item' => ['type' => 'TEXT', 'null' => true],
            'tipe_item' => ['type' => 'ENUM', 'constraint' => ['file', 'url', 'teks', 'gambar', 'video', 'dokumentasi'], 'default' => 'file'],
            'path_item' => ['type' => 'TEXT', 'null' => true],
            'thumbnail_path' => ['type' => 'TEXT', 'null' => true],
            'urutan' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_highlight' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'catatan_siswa' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['hasil_id', 'urutan']);
        $this->forge->createTable('cbt_praktik_portofolio', true);

        // 5. Tabel Presentasi Hasil (Untuk assessment presentasi/demo)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'jadwal_presentasi' => ['type' => 'DATETIME', 'null' => true],
            'status_presentasi' => ['type' => 'ENUM', 'constraint' => ['belum_jadwal', 'terjadwal', 'selesai', 'dibatalkan'], 'default' => 'belum_jadwal'],
            'link_meeting' => ['type' => 'VARCHAR', 'constraint' => '500', 'null' => true],
            'catatan_presentasi' => ['type' => 'TEXT', 'null' => true],
            'nilai_presentasi' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'rubrik_presentasi' => ['type' => 'TEXT', 'null' => true], // JSON
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('hasil_id');
        $this->forge->createTable('cbt_praktik_presentasi', true);

        // 6. Update tabel hasil - tambah kolom untuk workflow revisi
        $this->forge->addColumn('cbt_praktik_hasil', [
            'status_revisi' => [
                'type' => 'ENUM',
                'constraint' => ['tidak_ada', 'bisa_revisi', 'sedang_revisi', 'sudah_revisi', 'revisi_ditolak'],
                'default' => 'tidak_ada',
                'after' => 'status_penilaian'
            ],
            'sisa_kesempatan_revisi' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'status_revisi'
            ],
            'batas_waktu_revisi' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'sisa_kesempatan_revisi'
            ],
            'instruksi_revisi' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'batas_waktu_revisi'
            ],
            'mode_pengumpulan' => [
                'type' => 'ENUM',
                'constraint' => ['single', 'portofolio', 'presentasi', 'proyek'],
                'default' => 'single',
                'after' => 'instruksi_revisi'
            ],
            'nilai_formatif' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'after' => 'nilai_akhir'
            ],
            'nilai_sumatif' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'after' => 'nilai_formatif'
            ],
        ]);

        // 7. Update tabel praktik - tambah kolom untuk instruksi fleksibel
        $this->forge->addColumn('cbt_praktik', [
            'tipe_instruksi' => [
                'type' => 'ENUM',
                'constraint' => ['terstruktur', 'semi_terbuka', 'terbuka', 'inkuiri'],
                'default' => 'terstruktur',
                'after' => 'instruksi'
            ],
            'panduan_kreativitas' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'tipe_instruksi'
            ],
            'contoh_hasil' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'panduan_kreativitas'
            ],
            'sumber_belajar' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'contoh_hasil'
            ],
            'izinkan_kolaborasi' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'sumber_belajar'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('cbt_praktik_presentasi', true);
        $this->forge->dropTable('cbt_praktik_portofolio', true);
        $this->forge->dropTable('cbt_praktik_umpan_balik', true);
        $this->forge->dropTable('cbt_praktik_penilaian_berkelanjutan', true);

        $this->forge->dropColumn('cbt_praktik_rubrik', ['dimensi_penilaian', 'tahap_penilaian', 'izinkan_revisi', 'maksimum_revisi']);
        $this->forge->dropColumn('cbt_praktik_hasil', ['status_revisi', 'sisa_kesempatan_revisi', 'batas_waktu_revisi', 'instruksi_revisi', 'mode_pengumpulan', 'nilai_formatif', 'nilai_sumatif']);
        $this->forge->dropColumn('cbt_praktik', ['tipe_instruksi', 'panduan_kreativitas', 'contoh_hasil', 'sumber_belajar', 'izinkan_kolaborasi']);
    }
}
