<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi untuk Standar Penilaian Kurikulum Merdeka sesuai Permendikbudristek No. 53/2023
 * Memastikan keterpaduan format penilaian dengan regulasi nasional
 */
class KurikulumMerdekaAssessmentStandard extends Migration
{
    public function up()
    {
        // 1. Tabel Master Komponen Penilaian sesuai Permendikbudristek 53/2023
        if (!$this->db->tableExists('e_komponen_penilaian')) {
            $this->forge->addField([
                'id_komponen' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'kode_komponen' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'unique' => true,
                ],
                'nama_komponen' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'jenis_penilaian' => [ // Pengetahuan, Keterampilan, Sikap
                    'type' => 'ENUM',
                    'constraint' => ['Pengetahuan', 'Keterampilan', 'Sikap'],
                ],
                'bobot_minimal' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'default' => 0.00,
                ],
                'bobot_maksimal' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'default' => 100.00,
                ],
                'keterangan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'is_aktif' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
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
            $this->forge->addKey('id_komponen', true);
            $this->forge->addKey('kode_komponen');
            $this->forge->addKey('jenis_penilaian');
            $this->forge->createTable('e_komponen_penilaian', true);
            
            // Insert data default sesuai Permendikbudristek 53/2023
            $this->sebaranDefaultKomponenPenilaian();
        }

        // 2. Tabel Bobot Penilaian per Mapel dan Tingkat
        if (!$this->db->tableExists('e_bobot_penilaian')) {
            $this->forge->addField([
                'id_bobot' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'mapel_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'tingkat_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'komponen_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'bobot' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'default' => 0.00,
                ],
                'jenis_bobot' => [ // Harian, Tengah Semester, Akhir Semester
                    'type' => 'ENUM',
                    'constraint' => ['Harian', 'Tengah_Semester', 'Akhir_Semester'],
                ],
                'semester' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'tahun_pelajaran' => [
                    'type' => 'VARCHAR',
                    'constraint' => 9, // Format: 2023/2024
                ],
                'is_aktif' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
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
            $this->forge->addKey('id_bobot', true);
            $this->forge->addKey(['mapel_id', 'tingkat_id', 'komponen_id', 'jenis_bobot', 'semester', 'tahun_pelajaran'], false, true);
            $this->forge->createTable('e_bobot_penilaian', true);
        }

        // 3. Tabel Portofolio Digital dengan P5 (Pancasila Student Profile)
        if (!$this->db->tableExists('e_portofolio_digital')) {
            $this->forge->addField([
                'id_portofolio' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'siswa_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'elemen_p5' => [ // Dimensi P5
                    'type' => 'ENUM',
                    'constraint' => [
                        'Beriman dan Bertaqwa kepada Tuhan YME',
                        'Berkebinekaan Global',
                        'Bergotong Royong',
                        'Kreatif',
                        'Mandiri',
                        'Bernalar Kritis'
                    ],
                ],
                'sub_elemen' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'capaian_pembelajaran' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'bukti_pembelajaran' => [ // JSON untuk multiple evidence
                    'type' => 'JSON',
                    'null' => true,
                ],
                'jenis_bukti' => [ // Video, Dokumen, Gambar, Audio
                    'type' => 'ENUM',
                    'constraint' => ['Video', 'Dokumen', 'Gambar', 'Audio'],
                ],
                'tanggal_kegiatan' => [
                    'type' => 'DATE',
                ],
                'refleksi_siswa' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'feedback_guru' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'nilai_otentik' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'rubrik_penilaian' => [ // JSON untuk rubrik
                    'type' => 'JSON',
                    'null' => true,
                ],
                'status_validasi' => [
                    'type' => 'ENUM',
                    'constraint' => ['Draft', 'Menunggu_Validasi', 'Tervalidasi', 'Perlu_Revisi'],
                    'default' => 'Draft',
                ],
                'validator_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'tanggal_validasi' => [
                    'type' => 'DATETIME',
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
            $this->forge->addKey('id_portofolio', true);
            $this->forge->addKey('siswa_id');
            $this->forge->addKey('elemen_p5');
            $this->forge->addKey('status_validasi');
            $this->forge->addKey('tanggal_kegiatan');
            $this->forge->createTable('e_portofolio_digital', true);
        }

        // 4. Tabel Nilai Komponen untuk Penilaian Otentik
        if (!$this->db->tableExists('e_nilai_komponen')) {
            $this->forge->addField([
                'id_nilai' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'siswa_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'mapel_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'komponen_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'nilai' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'predikat' => [ // A, B, C, D untuk Pengetahuan; SB, B, C, K untuk Keterampilan; A, B, C, D untuk Sikap
                    'type' => 'ENUM',
                    'constraint' => ['A', 'B', 'C', 'D', 'SB', 'K'],
                    'null' => true,
                ],
                'deskripsi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'jenis_penilaian' => [ // Harian, Tengah_Semester, Akhir_Semester, Ujian_Sekolah
                    'type' => 'ENUM',
                    'constraint' => ['Harian', 'Tengah_Semester', 'Akhir_Semester', 'Ujian_Sekolah'],
                ],
                'tanggal_penilaian' => [
                    'type' => 'DATE',
                ],
                'guru_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'semester' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'tahun_pelajaran' => [
                    'type' => 'VARCHAR',
                    'constraint' => 9,
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
            $this->forge->addKey('id_nilai', true);
            $this->forge->addKey(['siswa_id', 'mapel_id', 'komponen_id', 'jenis_penilaian', 'semester', 'tahun_pelajaran'], false, true);
            $this->forge->addKey('tanggal_penilaian');
            $this->forge->createTable('e_nilai_komponen', true);
        }

        // 5. Tabel Nilai Akhir dengan Perhitungan Otomatis
        if (!$this->db->tableExists('e_nilai_akhir')) {
            $this->forge->addField([
                'id_nilai_akhir' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'siswa_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'mapel_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'nilai_pengetahuan' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'predikat_pengetahuan' => [
                    'type' => 'ENUM',
                    'constraint' => ['A', 'B', 'C', 'D'],
                    'null' => true,
                ],
                'deskripsi_pengetahuan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'nilai_keterampilan' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'predikat_keterampilan' => [
                    'type' => 'ENUM',
                    'constraint' => ['SB', 'B', 'C', 'K'],
                    'null' => true,
                ],
                'deskripsi_keterampilan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'nilai_sikap' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'predikat_sikap' => [
                    'type' => 'ENUM',
                    'constraint' => ['A', 'B', 'C', 'D'],
                    'null' => true,
                ],
                'deskripsi_sikap' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'nilai_akhir' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'predikat_akhir' => [
                    'type' => 'ENUM',
                    'constraint' => ['A', 'B', 'C', 'D'],
                    'null' => true,
                ],
                'deskripsi_akhir' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'semester' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'tahun_pelajaran' => [
                    'type' => 'VARCHAR',
                    'constraint' => 9,
                ],
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['Draft', 'Menunggu_Validasi', 'Tervalidasi'],
                    'default' => 'Draft',
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
            $this->forge->addKey('id_nilai_akhir', true);
            $this->forge->addKey(['siswa_id', 'mapel_id', 'semester', 'tahun_pelajaran'], false, true);
            $this->forge->createTable('e_nilai_akhir', true);
        }

        // 6. Tabel Pelaporan P5 ke Dapodik dan EMIS
        if (!$this->db->tableExists('e_p5_report')) {
            $this->forge->addField([
                'id_report' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'siswa_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'elemen_p5' => [
                    'type' => 'ENUM',
                    'constraint' => [
                        'Beriman dan Bertaqwa kepada Tuhan YME',
                        'Berkebinekaan Global',
                        'Bergotong Royong',
                        'Kreatif',
                        'Mandiri',
                        'Bernalar Kritis'
                    ],
                ],
                'proyek' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'durasi_proyek' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'comment' => 'Durasi dalam jam',
                ],
                'tanggal_mulai' => [
                    'type' => 'DATE',
                ],
                'tanggal_selesai' => [
                    'type' => 'DATE',
                ],
                'tingkat_ketercapaian' => [
                    'type' => 'ENUM',
                    'constraint' => ['Belum', 'Mulai', 'Sedang', 'Hampir', 'Tuntas'],
                    'default' => 'Belum',
                ],
                'nilai_akhir_p5' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'null' => true,
                ],
                'predikat_p5' => [
                    'type' => 'ENUM',
                    'constraint' => ['A', 'B', 'C', 'D'],
                    'null' => true,
                ],
                'refleksi_siswa' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'catatan_guru' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'dokumentasi' => [ // JSON untuk multiple files
                    'type' => 'JSON',
                    'null' => true,
                ],
                'link_dapodik' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'link_emis' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'sync_status' => [
                    'type' => 'ENUM',
                    'constraint' => ['Belum_Sync', 'Sync_Progress', 'Sudah_Sync', 'Sync_Failed'],
                    'default' => 'Belum_Sync',
                ],
                'last_sync' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'semester' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ],
                'tahun_pelajaran' => [
                    'type' => 'VARCHAR',
                    'constraint' => 9,
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
            $this->forge->addKey('id_report', true);
            $this->forge->addKey('siswa_id');
            $this->forge->addKey('elemen_p5');
            $this->forge->addKey('sync_status');
            $this->forge->addKey('last_sync');
            $this->forge->createTable('e_p5_report', true);
        }

        // 7. Tabel Audit Keamanan Data (GDPR-like & SNIST)
        if (!$this->db->tableExists('e_data_audit')) {
            $this->forge->addField([
                'id_audit' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'tabel_terkait' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ],
                'record_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'jenis_aksi' => [
                    'type' => 'ENUM',
                    'constraint' => ['CREATE', 'READ', 'UPDATE', 'DELETE', 'EXPORT', 'IMPORT'],
                ],
                'data_sebelum' => [ // JSON untuk data lama
                    'type' => 'JSON',
                    'null' => true,
                ],
                'data_sesudah' => [ // JSON untuk data baru
                    'type' => 'JSON',
                    'null' => true,
                ],
                'alasan_aksi' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                ],
                'user_agent' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'session_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'waktu_aksi' => [
                    'type' => 'DATETIME',
                ],
                'status_kepatuhan' => [
                    'type' => 'ENUM',
                    'constraint' => ['Valid', 'Warning', 'Violation'],
                    'default' => 'Valid',
                ],
                'catatan_kepatuhan' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id_audit', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('tabel_terkait');
            $this->forge->addKey('jenis_aksi');
            $this->forge->addKey('waktu_aksi');
            $this->forge->addKey('status_kepatuhan');
            $this->forge->createTable('e_data_audit', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('e_data_audit', true);
        $this->forge->dropTable('e_p5_report', true);
        $this->forge->dropTable('e_nilai_akhir', true);
        $this->forge->dropTable('e_nilai_komponen', true);
        $this->forge->dropTable('e_portofolio_digital', true);
        $this->forge->dropTable('e_bobot_penilaian', true);
        $this->forge->dropTable('e_komponen_penilaian', true);
    }

    /**
     * Sebaran default komponen penilaian sesuai Permendikbudristek 53/2023
     */
    private function sebaranDefaultKomponenPenilaian()
    {
        $data = [
            [
                'kode_komponen' => 'P1',
                'nama_komponen' => 'Pengetahuan - Tes Tulis',
                'jenis_penilaian' => 'Pengetahuan',
                'bobot_minimal' => 30.00,
                'bobot_maksimal' => 40.00,
                'keterangan' => 'Evaluasi hasil belajar melalui tes tertulis',
                'is_aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_komponen' => 'P2',
                'nama_komponen' => 'Pengetahuan - Ujian Praktik',
                'jenis_penilaian' => 'Pengetahuan',
                'bobot_minimal' => 20.00,
                'bobot_maksimal' => 30.00,
                'keterangan' => 'Evaluasi melalui ujian praktik atau demonstrasi',
                'is_aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_komponen' => 'K1',
                'nama_komponen' => 'Keterampilan - Proyek',
                'jenis_penilaian' => 'Keterampilan',
                'bobot_minimal' => 25.00,
                'bobot_maksimal' => 35.00,
                'keterangan' => 'Penilaian melalui proyek atau portofolio',
                'is_aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_komponen' => 'K2',
                'nama_komponen' => 'Keterampilan - Praktik',
                'jenis_penilaian' => 'Keterampilan',
                'bobot_minimal' => 20.00,
                'bobot_maksimal' => 30.00,
                'keterangan' => 'Penilaian keterampilan praktik langsung',
                'is_aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_komponen' => 'S1',
                'nama_komponen' => 'Sikap Spiritual dan Sosial',
                'jenis_penilaian' => 'Sikap',
                'bobot_minimal' => 10.00,
                'bobot_maksimal' => 20.00,
                'keterangan' => 'Penilaian sikap melalui observasi dan penilaian diri',
                'is_aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'kode_komponen' => 'P3',
                'nama_komponen' => 'Pengetahuan - Portofolio',
                'jenis_penilaian' => 'Pengetahuan',
                'bobot_minimal' => 15.00,
                'bobot_maksimal' => 25.00,
                'keterangan' => 'Penilaian melalui koleksi hasil kerja siswa',
                'is_aktif' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        
        $this->db->table('e_komponen_penilaian')->insertBatch($data);
    }
}
