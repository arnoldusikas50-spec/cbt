<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sync_tables extends CI_Migration
{

    public function up()
    {
        // Tabel konfigurasi server sync
        if (!$this->db->table_exists('sync_config')) {
            $this->db->query("
                CREATE TABLE `sync_config` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `server_type` ENUM('pusat','lokal') NOT NULL DEFAULT 'lokal',
                    `server_id` VARCHAR(36) DEFAULT NULL COMMENT 'UUID sekolah untuk identifikasi',
                    `pusat_url` VARCHAR(255) DEFAULT NULL,
                    `api_key` VARCHAR(64) DEFAULT NULL,
                    `api_secret` VARCHAR(128) DEFAULT NULL,
                    `last_sync_download` DATETIME DEFAULT NULL,
                    `last_sync_upload` DATETIME DEFAULT NULL,
                    `auto_sync` TINYINT(1) NOT NULL DEFAULT 0,
                    `sync_interval` INT(11) NOT NULL DEFAULT 60 COMMENT 'Interval dalam menit',
                    `sync_on_startup` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Tabel registrasi sekolah (untuk server pusat)
        if (!$this->db->table_exists('sync_schools')) {
            $this->db->query("
                CREATE TABLE `sync_schools` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `school_uuid` VARCHAR(36) NOT NULL,
                    `npsn` VARCHAR(20) DEFAULT NULL,
                    `nama_sekolah` VARCHAR(255) NOT NULL,
                    `alamat` TEXT,
                    `kota` VARCHAR(100) DEFAULT NULL,
                    `provinsi` VARCHAR(100) DEFAULT NULL,
                    `api_key` VARCHAR(64) NOT NULL,
                    `api_secret` VARCHAR(128) NOT NULL,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `last_sync` DATETIME DEFAULT NULL,
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_school_uuid` (`school_uuid`),
                    UNIQUE KEY `uk_api_key` (`api_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Tabel log sinkronisasi
        if (!$this->db->table_exists('sync_log')) {
            $this->db->query("
                CREATE TABLE `sync_log` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `school_uuid` VARCHAR(36) DEFAULT NULL,
                    `sync_type` ENUM('download','upload','full') NOT NULL,
                    `sync_direction` ENUM('pusat_to_lokal','lokal_to_pusat') NOT NULL,
                    `table_name` VARCHAR(50) NOT NULL,
                    `records_total` INT(11) NOT NULL DEFAULT 0,
                    `records_success` INT(11) NOT NULL DEFAULT 0,
                    `records_failed` INT(11) NOT NULL DEFAULT 0,
                    `status` ENUM('pending','running','success','failed','partial') NOT NULL DEFAULT 'pending',
                    `message` TEXT,
                    `started_at` DATETIME DEFAULT NULL,
                    `completed_at` DATETIME DEFAULT NULL,
                    `duration_seconds` INT(11) DEFAULT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_school_uuid` (`school_uuid`),
                    KEY `idx_sync_type` (`sync_type`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Tabel antrian sinkronisasi
        if (!$this->db->table_exists('sync_queue')) {
            $this->db->query("
                CREATE TABLE `sync_queue` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `school_uuid` VARCHAR(36) DEFAULT NULL,
                    `table_name` VARCHAR(50) NOT NULL,
                    `record_id` INT(11) NOT NULL,
                    `action` ENUM('insert','update','delete') NOT NULL,
                    `data_json` LONGTEXT,
                    `priority` TINYINT(3) NOT NULL DEFAULT 5 COMMENT '1=highest, 10=lowest',
                    `status` ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
                    `retry_count` TINYINT(3) NOT NULL DEFAULT 0,
                    `max_retries` TINYINT(3) NOT NULL DEFAULT 3,
                    `error_message` TEXT,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `processed_at` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_status_priority` (`status`, `priority`),
                    KEY `idx_table_record` (`table_name`, `record_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Tabel untuk menyimpan mapping ID antara server pusat dan lokal
        if (!$this->db->table_exists('sync_id_mapping')) {
            $this->db->query("
                CREATE TABLE `sync_id_mapping` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `table_name` VARCHAR(50) NOT NULL,
                    `local_id` INT(11) NOT NULL,
                    `pusat_id` INT(11) NOT NULL,
                    `uuid` VARCHAR(36) DEFAULT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_table_local` (`table_name`, `local_id`),
                    UNIQUE KEY `uk_table_pusat` (`table_name`, `pusat_id`),
                    KEY `idx_uuid` (`uuid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Tambahkan kolom sync pada tabel-tabel utama CBT
        $tables_to_modify = [
            'cbt_bank_soal' => 'id_bank',
            'cbt_soal' => 'id_soal',
            'cbt_jadwal' => 'id_jadwal',
            'cbt_siswa' => 'id',
            'cbt_durasi' => 'id_durasi',
            'cbt_nilai' => 'id_nilai',
            'cbt_log' => 'id_log',
            'master_siswa' => 'id_siswa',
            'master_guru' => 'id_guru',
            'master_kelas' => 'id_kelas',
            'master_mapel' => 'id_mapel',
            'ref_jurusan' => 'id_jurusan',
            'cbt_jenis' => 'id_jenis',
            'cbt_sesi' => 'id_sesi',
            'cbt_ruang' => 'id_ruang'
        ];

        foreach ($tables_to_modify as $table => $pk) {
            if ($this->db->table_exists($table)) {
                // Add UUID column
                if (!$this->db->field_exists('sync_uuid', $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `sync_uuid` VARCHAR(36) DEFAULT NULL");
                    $this->db->query("CREATE INDEX `idx_sync_uuid` ON `{$table}` (`sync_uuid`)");
                }

                // Add sync status column
                if (!$this->db->field_exists('sync_status', $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `sync_status` ENUM('synced','pending','conflict') DEFAULT 'pending'");
                }

                // Add last sync timestamp
                if (!$this->db->field_exists('sync_at', $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `sync_at` DATETIME DEFAULT NULL");
                }

                // Add server origin
                if (!$this->db->field_exists('sync_origin', $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `sync_origin` ENUM('pusat','lokal') DEFAULT 'lokal'");
                }
            }
        }

        // Generate UUID for existing records yang belum punya
        foreach ($tables_to_modify as $table => $pk) {
            if ($this->db->table_exists($table) && $this->db->field_exists('sync_uuid', $table)) {
                $this->db->query("UPDATE `{$table}` SET `sync_uuid` = UUID() WHERE `sync_uuid` IS NULL");
            }
        }
    }

    public function down()
    {
        // Drop sync tables
        $this->db->query("DROP TABLE IF EXISTS `sync_id_mapping`");
        $this->db->query("DROP TABLE IF EXISTS `sync_queue`");
        $this->db->query("DROP TABLE IF EXISTS `sync_log`");
        $this->db->query("DROP TABLE IF EXISTS `sync_schools`");
        $this->db->query("DROP TABLE IF EXISTS `sync_config`");

        // Note: Tidak menghapus kolom sync dari tabel lain untuk keamanan data
    }
}
