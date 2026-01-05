-- =====================================================
-- ACTIVITY LOG TABLE FOR AUDIT TRAIL
-- Run this SQL to create the table
-- =====================================================

CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT NULL COMMENT 'ID of user performing action (null for system actions)',
    `user_type` VARCHAR(50) NULL COMMENT 'admin, guru, siswa, panitia, school, system',
    `action` VARCHAR(100) NOT NULL COMMENT 'Action: LOGIN, LOGOUT, CREATE, UPDATE, DELETE, SYNC, SECURITY_*',
    `target_table` VARCHAR(100) NULL COMMENT 'Table affected by this action',
    `target_id` VARCHAR(50) NULL COMMENT 'Primary key of affected record',
    `old_value` TEXT NULL COMMENT 'JSON: Previous value before change',
    `new_value` TEXT NULL COMMENT 'JSON: New value after change',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_user_type` (`user_type`),
    INDEX `idx_action` (`action`),
    INDEX `idx_target_table` (`target_table`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit trail for all user and system activities';

-- =====================================================
-- SOFT DELETE COLUMNS FOR DELTA SYNC SUPPORT
-- These allow tracking deleted records for sync purposes
-- =====================================================

-- Add deleted_at column to key tables for soft delete support
-- Run these only if columns don't exist

ALTER TABLE `cbt_bank_soal` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `cbt_soal` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `cbt_jadwal` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `master_siswa` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `master_guru` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `master_kelas` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `master_mapel` ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL DEFAULT NULL;

-- Add indexes for soft delete queries
ALTER TABLE `cbt_bank_soal` ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`);
ALTER TABLE `cbt_soal` ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`);
ALTER TABLE `cbt_jadwal` ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`);
ALTER TABLE `master_siswa` ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`);
ALTER TABLE `master_guru` ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`);
ALTER TABLE `master_kelas` ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`);
ALTER TABLE `master_mapel` ADD INDEX IF NOT EXISTS `idx_deleted_at` (`deleted_at`);

-- =====================================================
-- MD5 CHECKSUM COLUMN FOR MEDIA INTEGRITY
-- =====================================================

-- Add checksum column to track file integrity
-- ALTER TABLE `cbt_soal` ADD COLUMN IF NOT EXISTS `file_checksum` VARCHAR(32) NULL COMMENT 'MD5 hash of media files';
