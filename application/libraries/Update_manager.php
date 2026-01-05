<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Update Manager Library
 * 
 * Handles checking, downloading, and installing application updates
 * from the central server.
 */
class Update_manager
{
    protected $CI;
    protected $config;
    protected $current_version;
    protected $current_version_code;
    protected $backup_path;
    protected $temp_path;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Sync_model');
        $this->CI->config->load('version', TRUE);

        $version_config = $this->CI->config->item('version');
        $this->current_version = isset($version_config['app_version']) ? $version_config['app_version'] : '1.0.0';
        $this->current_version_code = isset($version_config['app_version_code']) ? $version_config['app_version_code'] : 10000;

        $this->backup_path = FCPATH . 'backups/updates/';
        $this->temp_path = FCPATH . 'temp/updates/';

        // Ensure directories exist
        $this->_ensure_directory($this->backup_path);
        $this->_ensure_directory($this->temp_path);
    }

    /**
     * Get current application version
     */
    public function get_current_version()
    {
        return [
            'version' => $this->current_version,
            'version_code' => $this->current_version_code
        ];
    }

    /**
     * Check for available updates from server pusat
     */
    public function check_for_updates()
    {
        $sync_config = $this->CI->Sync_model->get_config();

        if (!$sync_config || empty($sync_config->pusat_url) || empty($sync_config->api_key)) {
            return [
                'success' => false,
                'message' => 'Konfigurasi sync belum lengkap'
            ];
        }

        $url = rtrim($sync_config->pusat_url, '/') . '/api_sync/check_update';
        $body = json_encode([
            'current_version' => $this->current_version,
            'current_version_code' => $this->current_version_code
        ]);

        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $sync_config->api_key
        ];

        // Add HMAC signature if secret is configured
        if (!empty($sync_config->api_secret)) {
            $signature = hash_hmac('sha256', $body, $sync_config->api_secret);
            $headers[] = 'X-Api-Signature: ' . $signature;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung: ' . $error
            ];
        }

        if ($http_code !== 200) {
            return [
                'success' => false,
                'message' => 'Server error: HTTP ' . $http_code
            ];
        }

        $result = json_decode($response, true);
        if (!$result || $result['status'] !== 'success') {
            return [
                'success' => false,
                'message' => isset($result['message']) ? $result['message'] : 'Unknown error'
            ];
        }

        return [
            'success' => true,
            'data' => $result['data']
        ];
    }

    /**
     * Download update package
     */
    public function download_update($update_id)
    {
        $sync_config = $this->CI->Sync_model->get_config();

        if (!$sync_config) {
            return ['success' => false, 'message' => 'Konfigurasi tidak ditemukan'];
        }

        $url = rtrim($sync_config->pusat_url, '/') . '/api_sync/download_update';
        $body = json_encode(['update_id' => $update_id]);

        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $sync_config->api_key
        ];

        if (!empty($sync_config->api_secret)) {
            $signature = hash_hmac('sha256', $body, $sync_config->api_secret);
            $headers[] = 'X-Api-Signature: ' . $signature;
        }

        // Clean temp directory
        $this->_clean_directory($this->temp_path);

        $temp_file = $this->temp_path . 'update_' . $update_id . '.zip';

        $fp = fopen($temp_file, 'w+');
        if (!$fp) {
            return ['success' => false, 'message' => 'Gagal membuat file temporary'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 600, // 10 minutes for large files
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$response_headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2)
                    return $len;
                $response_headers[strtolower(trim($header[0]))] = trim($header[1]);
                return $len;
            }
        ]);

        $response_headers = [];
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if ($error || $http_code !== 200) {
            @unlink($temp_file);
            return [
                'success' => false,
                'message' => $error ?: 'HTTP Error: ' . $http_code
            ];
        }

        // Get expected checksum from response headers
        $expected_checksum = isset($response_headers['x-update-checksum'])
            ? $response_headers['x-update-checksum'] : null;
        $version = isset($response_headers['x-update-version'])
            ? $response_headers['x-update-version'] : null;

        // Verify checksum
        if ($expected_checksum) {
            $actual_checksum = hash_file('sha256', $temp_file);
            if ($actual_checksum !== $expected_checksum) {
                @unlink($temp_file);
                return [
                    'success' => false,
                    'message' => 'Checksum tidak cocok. File mungkin rusak atau dimodifikasi.'
                ];
            }
        }

        return [
            'success' => true,
            'file_path' => $temp_file,
            'version' => $version,
            'checksum' => $expected_checksum
        ];
    }

    /**
     * Install downloaded update
     */
    public function install_update($zip_path, $update_id = null, $new_version = null)
    {
        if (!file_exists($zip_path)) {
            return ['success' => false, 'message' => 'File update tidak ditemukan'];
        }

        // 1. Create backup first
        $backup_result = $this->create_backup();
        if (!$backup_result['success']) {
            return [
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $backup_result['message']
            ];
        }
        $backup_file = $backup_result['backup_file'];

        // 2. Extract to temp directory
        $extract_path = $this->temp_path . 'extracted_' . time() . '/';
        $this->_ensure_directory($extract_path);

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== TRUE) {
            return ['success' => false, 'message' => 'Gagal membuka file ZIP'];
        }

        $zip->extractTo($extract_path);
        $zip->close();

        // 3. Verify extracted contents (should have application/ folder)
        $source_path = $extract_path;
        if (is_dir($extract_path . 'application/')) {
            $source_path = $extract_path;
        } elseif (is_dir($extract_path . 'akm/application/')) {
            $source_path = $extract_path . 'akm/';
        } else {
            // Check for single root folder
            $dirs = glob($extract_path . '*', GLOB_ONLYDIR);
            if (count($dirs) === 1 && is_dir($dirs[0] . '/application/')) {
                $source_path = $dirs[0] . '/';
            }
        }

        // 4. Copy files to application
        try {
            $this->_copy_directory($source_path . 'application/', APPPATH);

            // Copy assets if exists
            if (is_dir($source_path . 'assets/')) {
                $this->_copy_directory($source_path . 'assets/', FCPATH . 'assets/');
            }

            // 5. Update version file if new version provided
            if ($new_version) {
                $this->_update_version_file($new_version);
            }

            // 6. Clear cache
            $this->_clear_cache();

            // 7. Clean up temp files
            $this->_delete_directory($extract_path);
            @unlink($zip_path);

            // 8. Report success to pusat
            if ($update_id) {
                $this->_report_update_status($update_id, $this->current_version, $new_version, 'installed');
            }

            return [
                'success' => true,
                'message' => 'Update berhasil diinstall',
                'backup_file' => $backup_file,
                'new_version' => $new_version
            ];

        } catch (Exception $e) {
            // Rollback on failure
            $this->rollback($backup_file);

            if ($update_id) {
                $this->_report_update_status($update_id, $this->current_version, $new_version, 'failed', $e->getMessage());
            }

            return [
                'success' => false,
                'message' => 'Gagal menginstall: ' . $e->getMessage(),
                'rolled_back' => true
            ];
        }
    }

    /**
     * Create backup of current application
     */
    public function create_backup()
    {
        $backup_name = 'backup_v' . $this->current_version . '_' . date('Ymd_His') . '.zip';
        $backup_file = $this->backup_path . $backup_name;

        $zip = new ZipArchive();
        if ($zip->open($backup_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return ['success' => false, 'message' => 'Gagal membuat file backup'];
        }

        // Backup application folder (essential files only)
        $folders_to_backup = [
            'controllers',
            'models',
            'views',
            'libraries',
            'helpers',
            'config'
        ];

        foreach ($folders_to_backup as $folder) {
            $folder_path = APPPATH . $folder;
            if (is_dir($folder_path)) {
                $this->_add_folder_to_zip($zip, $folder_path, 'application/' . $folder);
            }
        }

        $zip->close();

        // Clean old backups
        $this->_clean_old_backups();

        return [
            'success' => true,
            'backup_file' => $backup_file,
            'backup_name' => $backup_name
        ];
    }

    /**
     * Rollback to backup
     */
    public function rollback($backup_file)
    {
        if (!file_exists($backup_file)) {
            return ['success' => false, 'message' => 'File backup tidak ditemukan'];
        }

        $extract_path = $this->temp_path . 'rollback_' . time() . '/';
        $this->_ensure_directory($extract_path);

        $zip = new ZipArchive();
        if ($zip->open($backup_file) !== TRUE) {
            return ['success' => false, 'message' => 'Gagal membuka file backup'];
        }

        $zip->extractTo($extract_path);
        $zip->close();

        // Restore files
        $this->_copy_directory($extract_path . 'application/', APPPATH);

        // Clean up
        $this->_delete_directory($extract_path);
        $this->_clear_cache();

        return [
            'success' => true,
            'message' => 'Rollback berhasil'
        ];
    }

    /**
     * Get list of available backups
     */
    public function get_backups()
    {
        $backups = [];
        $files = glob($this->backup_path . '*.zip');

        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        // Sort by date descending
        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    // ==================== PRIVATE HELPER METHODS ====================

    private function _ensure_directory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function _clean_directory($path)
    {
        if (is_dir($path)) {
            $files = glob($path . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                } elseif (is_dir($file)) {
                    $this->_delete_directory($file);
                }
            }
        }
    }

    private function _delete_directory($path)
    {
        if (!is_dir($path))
            return;

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $full_path = $path . '/' . $file;
            is_dir($full_path) ? $this->_delete_directory($full_path) : unlink($full_path);
        }
        rmdir($path);
    }

    private function _copy_directory($source, $dest)
    {
        if (!is_dir($source))
            return;

        $this->_ensure_directory($dest);

        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $src_path = $source . '/' . $file;
            $dst_path = $dest . '/' . $file;

            if (is_dir($src_path)) {
                $this->_copy_directory($src_path, $dst_path);
            } else {
                copy($src_path, $dst_path);
            }
        }
    }

    private function _add_folder_to_zip($zip, $folder, $zip_path)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_path = $zip_path . '/' . substr($file_path, strlen($folder) + 1);
                $zip->addFile($file_path, $relative_path);
            }
        }
    }

    private function _update_version_file($new_version)
    {
        $version_file = APPPATH . 'config/version.php';
        $parts = explode('.', $new_version);
        $major = isset($parts[0]) ? (int) $parts[0] : 0;
        $minor = isset($parts[1]) ? (int) $parts[1] : 0;
        $patch = isset($parts[2]) ? (int) $parts[2] : 0;
        $version_code = ($major * 10000) + ($minor * 100) + $patch;

        $content = "<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n\n";
        $content .= "\$config['app_version'] = '$new_version';\n";
        $content .= "\$config['app_version_code'] = $version_code;\n";
        $content .= "\$config['app_version_date'] = '" . date('Y-m-d') . "';\n";
        $content .= "\$config['app_name'] = 'Ikasmedia CBT';\n";
        $content .= "\$config['update_server'] = '';\n";
        $content .= "\$config['auto_check_updates'] = true;\n";
        $content .= "\$config['auto_install_updates'] = false;\n";
        $content .= "\$config['backup_before_update'] = true;\n";
        $content .= "\$config['backup_max_age_days'] = 30;\n";

        file_put_contents($version_file, $content);
    }

    private function _clear_cache()
    {
        // Clear CI cache if exists
        $cache_path = APPPATH . 'cache/';
        if (is_dir($cache_path)) {
            $files = glob($cache_path . '*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== 'index.html') {
                    @unlink($file);
                }
            }
        }

        // Clear OPcache if enabled
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    private function _clean_old_backups()
    {
        $this->CI->config->load('version', TRUE);
        $max_age = $this->CI->config->item('backup_max_age_days') ?: 30;
        $cutoff = time() - ($max_age * 86400);

        $files = glob($this->backup_path . '*.zip');
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    private function _report_update_status($update_id, $from_version, $to_version, $status, $error = null)
    {
        $sync_config = $this->CI->Sync_model->get_config();
        if (!$sync_config)
            return;

        $url = rtrim($sync_config->pusat_url, '/') . '/api_sync/report_update_status';
        $body = json_encode([
            'update_id' => $update_id,
            'from_version' => $from_version,
            'to_version' => $to_version,
            'status' => $status,
            'error_message' => $error
        ]);

        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $sync_config->api_key
        ];

        if (!empty($sync_config->api_secret)) {
            $signature = hash_hmac('sha256', $body, $sync_config->api_secret);
            $headers[] = 'X-Api-Signature: ' . $signature;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
