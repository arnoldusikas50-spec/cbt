-- =====================================================
-- SYSTEM UPDATES TABLE FOR AUTO-PATCHING
-- =====================================================

CREATE TABLE IF NOT EXISTS `system_updates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `version` VARCHAR(20) NOT NULL COMMENT 'Semantic version: 1.0.0, 1.0.1, etc',
    `version_code` INT UNSIGNED NOT NULL COMMENT 'Numeric version for comparison: 10001, 10002',
    `title` VARCHAR(255) NOT NULL COMMENT 'Update title',
    `description` TEXT NULL COMMENT 'Changelog / release notes',
    `file_name` VARCHAR(255) NOT NULL COMMENT 'ZIP file name',
    `file_path` VARCHAR(500) NOT NULL COMMENT 'Path to ZIP file on server',
    `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `file_checksum` VARCHAR(64) NOT NULL COMMENT 'SHA256 hash of the file',
    `min_version` VARCHAR(20) NULL COMMENT 'Minimum version required to update',
    `is_critical` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Critical security update flag',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Whether this update is available',
    `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_by` INT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_version` (`version`),
    INDEX `idx_version_code` (`version_code`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System update packages for auto-patching';

-- Track update history per school
CREATE TABLE IF NOT EXISTS `system_update_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `school_uuid` VARCHAR(50) NULL COMMENT 'NULL for local server updates',
    `update_id` INT UNSIGNED NOT NULL,
    `from_version` VARCHAR(20) NULL,
    `to_version` VARCHAR(20) NOT NULL,
    `status` ENUM('downloaded', 'installed', 'failed', 'rolled_back') NOT NULL,
    `error_message` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_school` (`school_uuid`),
    INDEX `idx_update` (`update_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log of update installations';
