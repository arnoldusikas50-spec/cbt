<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Application Version Configuration
 * 
 * This file defines the current version of the application.
 * Update this file when releasing new versions.
 */

// Current application version (Semantic Versioning)
$config['app_version'] = '2.1.0';

// Numeric version code for easy comparison
// Format: MAJOR * 10000 + MINOR * 100 + PATCH
// Example: 2.1.0 = 2*10000 + 1*100 + 0 = 20100
$config['app_version_code'] = 20100;

// Version release date
$config['app_version_date'] = '2026-01-04';

// Application name
$config['app_name'] = 'Ikasmedia CBT';

// Update server URL (can be same as pusat_url or different CDN)
$config['update_server'] = ''; // Leave empty to use sync pusat_url

// Auto-check for updates (true/false)
$config['auto_check_updates'] = true;

// Allow auto-install of non-critical updates
$config['auto_install_updates'] = false;

// Backup before update
$config['backup_before_update'] = true;

// Maximum backup age in days (older backups will be deleted)
$config['backup_max_age_days'] = 30;
