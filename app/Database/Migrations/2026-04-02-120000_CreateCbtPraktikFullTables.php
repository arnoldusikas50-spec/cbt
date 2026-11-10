<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCbtPraktikFullTables extends Migration
{
    public function up()
    {
        // 1. Tabel Utama Ujian Praktik (Enhanced)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tp_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'smt_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'mapel_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'kelas_id' => ['type' => 'TEXT', 'null' => true], // JSON array of class IDs
            'judul' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'instruksi' => ['type' => 'LONGTEXT', 'null' => true],
            'tujuan_pembelajaran' => ['type' => 'TEXT', 'null' => true],
            'jenis_praktik' => ['type' => 'ENUM', 'constraint' => ['coding', 'simulasi', 'hands_on', 'upload_file', 'mixed'], 'default' => 'hands_on'],
            'format_pengumpulan' => ['type' => 'ENUM', 'constraint' => ['file', 'url', 'teks', 'kode', 'screenshot', 'mixed'], 'default' => 'file'],
            'batasan_file' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true], // json: extensions, max_size
            'tanggal_mulai' => ['type' => 'DATETIME', 'null' => true],
            'tanggal_selesai' => ['type' => 'DATETIME', 'null' => true],
            'durasi_menit' => ['type' => 'INT', 'constraint' => 11, 'default' => 0], // 0 = tidak dibatasi
            'bobot' => ['type' => 'INT', 'constraint' => 11, 'default' => 100],
            'kkm' => ['type' => 'INT', 'constraint' => 11, 'default' => 70],
            'aktifkan_penilaian_otomatis' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'aktifkan_anti_cheat' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'batas_tab_browser' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1], // Blokir tab lain
            'batas_copy_paste' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'batas_screenshot' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0], // Deteksi screenshot
            'wajib_webcam' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'wajib_fullscreen' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'aktif', 'sedang_berlangsung', 'selesai', 'arsip'], 'default' => 'draft'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tp_id', 'smt_id', 'mapel_id']);
        $this->forge->addKey(['status', 'tanggal_mulai']);
        $this->forge->createTable('cbt_praktik', true);

        // 2. Tabel Rubrik Penilaian (Kriteria Utama)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kode_kriteria' => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'nama_kriteria' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'bobot_persen' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0], // Persentase bobot
            'tipe_penilaian' => ['type' => 'ENUM', 'constraint' => ['manual', 'otomatis', 'hybrid'], 'default' => 'manual'],
            'urutan' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('praktik_id');
        $this->forge->createTable('cbt_praktik_rubrik', true);

        // 3. Tabel Detail Level Rubrik (Skala Penilaian)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rubrik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'level' => ['type' => 'INT', 'constraint' => 11, 'default' => 1], // 1-4 atau 1-5
            'nama_level' => ['type' => 'VARCHAR', 'constraint' => '50'], // Kurang, Cukup, Baik, Sangat Baik
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'nilai_min' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'nilai_max' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'indikator_otomatis' => ['type' => 'TEXT', 'null' => true], // JSON: pattern coding, dll
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('rubrik_id');
        $this->forge->createTable('cbt_praktik_rubrik_level', true);

        // 4. Tabel Hasil Ujian Praktik (Enhanced)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'kelas_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'file_path' => ['type' => 'TEXT', 'null' => true], // JSON array jika multiple file
            'jawaban_teks' => ['type' => 'LONGTEXT', 'null' => true],
            'url_simulasi' => ['type' => 'VARCHAR', 'constraint' => '500', 'null' => true],
            'kode_jawaban' => ['type' => 'LONGTEXT', 'null' => true], // Untuk coding
            'bahasa_pemrograman' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true], // python, java, dll
            'waktu_mulai' => ['type' => 'DATETIME', 'null' => true],
            'waktu_submit' => ['type' => 'DATETIME', 'null' => true],
            'durasi_pengerjaan_detik' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status_pengerjaan' => ['type' => 'ENUM', 'constraint' => ['belum_mulai', 'sedang_mengerjakan', 'selesai', 'terlambat', 'dibatalkan'], 'default' => 'belum_mulai'],
            'status_penilaian' => ['type' => 'ENUM', 'constraint' => ['belum_dinilai', 'sedang_dinilai', 'sudah_dinilai', 'diajukan_revisi'], 'default' => 'belum_dinilai'],
            'nilai_otomatis' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'nilai_manual' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'nilai_akhir' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'rincian_nilai_rubrik' => ['type' => 'LONGTEXT', 'null' => true], // JSON: {rubrik_id: {level: 3, nilai: 80, catatan: "..."}}
            'feedback_guru' => ['type' => 'TEXT', 'null' => true],
            'catatan_siswa' => ['type' => 'TEXT', 'null' => true],
            'status_plagiasi' => ['type' => 'ENUM', 'constraint' => ['aman', 'dicurigai', 'terindikasi'], 'null' => true],
            'persentase_plagiasi' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => '45', 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['praktik_id', 'siswa_id']);
        $this->forge->addKey(['siswa_id', 'status_pengerjaan']);
        $this->forge->createTable('cbt_praktik_hasil', true);

        // 5. Tabel Log Aktivitas (Anti-Cheating)
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'tipe_aktivitas' => ['type' => 'ENUM', 'constraint' => [
                'mulai_ujian', 'submit_ujian', 'keluar_fullscreen', 
                'buka_tab_baru', 'copy_paste', 'screenshot_detected',
                'navigasi_jawaban', 'ubah_jawaban', 'simpan_draft',
                'koneksi_putus', 'koneksi_kembali', 'pelanggaran_kecurangan',
                'webcam_tidak_aktif', 'waktu_hampir_habis'
            ]],
            'detail' => ['type' => 'TEXT', 'null' => true], // JSON detail aktivitas
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => '45', 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['praktik_id', 'siswa_id']);
        $this->forge->addKey(['tipe_aktivitas', 'created_at']);
        $this->forge->createTable('cbt_praktik_log', true);

        // 6. Tabel Template Rubrik (Reusable)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_template' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'jenis_praktik' => ['type' => 'ENUM', 'constraint' => ['coding', 'simulasi', 'hands_on', 'upload_file', 'mixed', 'semua'], 'default' => 'semua'],
            'mapel_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true], // Null = untuk semua mapel
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'is_public' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cbt_praktik_template_rubrik', true);

        // 7. Tabel Item Template Rubrik
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'template_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kode_kriteria' => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'nama_kriteria' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'bobot_persen' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'tipe_penilaian' => ['type' => 'ENUM', 'constraint' => ['manual', 'otomatis', 'hybrid'], 'default' => 'manual'],
            'urutan' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'level_detail' => ['type' => 'LONGTEXT', 'null' => true], // JSON array level
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('template_id');
        $this->forge->createTable('cbt_praktik_template_rubrik_item', true);

        // 8. Tabel Konfigurasi Penilaian Otomatis
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rubrik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tipe_penilaian' => ['type' => 'ENUM', 'constraint' => ['pattern_match', 'test_case', 'syntax_check', 'similarity', 'custom_script'], 'default' => 'pattern_match'],
            'config' => ['type' => 'LONGTEXT', 'null' => true], // JSON konfigurasi spesifik
            'test_cases' => ['type' => 'LONGTEXT', 'null' => true], // JSON test cases untuk coding
            'bobot_otomatis' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 100], // % dari nilai rubrik yang diotomatisasi
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['praktik_id', 'rubrik_id']);
        $this->forge->createTable('cbt_praktik_penilaian_otomatis', true);

        // 9. Tabel File Attachment (Multiple files per submission)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'nama_file' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'nama_file_asli' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'tipe_file' => ['type' => 'VARCHAR', 'constraint' => '50'],
            'ukuran_file' => ['type' => 'INT', 'constraint' => 11, 'default' => 0], // bytes
            'path_file' => ['type' => 'TEXT'],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['hasil_id', 'praktik_id']);
        $this->forge->createTable('cbt_praktik_file', true);

        // 10. Tabel Riwayat Revisi (Jika siswa mengajukan revisi)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'hasil_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'praktik_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false],
            'versi' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'file_path' => ['type' => 'TEXT', 'null' => true],
            'jawaban_teks' => ['type' => 'LONGTEXT', 'null' => true],
            'catatan_revisi' => ['type' => 'TEXT', 'null' => true],
            'status_revisi' => ['type' => 'ENUM', 'constraint' => ['pending', 'diterima', 'ditolak'], 'default' => 'pending'],
            'dinilai_oleh' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['hasil_id', 'versi']);
        $this->forge->createTable('cbt_praktik_revisi', true);
    }

    public function down()
    {
        $this->forge->dropTable('cbt_praktik_revisi', true);
        $this->forge->dropTable('cbt_praktik_file', true);
        $this->forge->dropTable('cbt_praktik_penilaian_otomatis', true);
        $this->forge->dropTable('cbt_praktik_template_rubrik_item', true);
        $this->forge->dropTable('cbt_praktik_template_rubrik', true);
        $this->forge->dropTable('cbt_praktik_log', true);
        $this->forge->dropTable('cbt_praktik_hasil', true);
        $this->forge->dropTable('cbt_praktik_rubrik_level', true);
        $this->forge->dropTable('cbt_praktik_rubrik', true);
        // Keep main table as it exists from earlier migration
    }
}
