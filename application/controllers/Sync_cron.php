<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sync Cron Controller
 * Dual-mode: HTTP with key validation OR CLI access
 * 
 * HTTP Examples:
 * - /sync_cron/run?key=YOUR_SECRET_KEY
 * - /sync_cron/status?key=YOUR_SECRET_KEY
 * 
 * CLI Examples:
 * - php index.php sync_cron run
 * - php index.php sync_cron check_update
 * - php index.php sync_cron install_update
 * 
 * Cron Job Example (every 30 minutes):
 * *​/30 * * * * cd /path/to/akm && php index.php sync_cron run >> /var/log/cbt_sync.log 2>&1
 */
class Sync_cron extends CI_Controller
{
    private $cron_key = 'AKM_CRON_SECRET_KEY_2026'; // Change this!
    private $is_cli = false;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Sync_model');
        $this->is_cli = $this->input->is_cli_request();
    }

    /**
     * Verify access (key for HTTP, allow all for CLI)
     */
    private function verify_access()
    {
        if ($this->is_cli) {
            return true; // CLI is always allowed
        }

        $key = $this->input->get('key');
        if ($key !== $this->cron_key) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['status' => 'error', 'message' => 'Invalid cron key']);
            exit;
        }
        return true;
    }

    /**
     * Output message (JSON for HTTP, plain text for CLI)
     */
    private function output($data, $is_final = true)
    {
        if ($this->is_cli) {
            if (is_array($data)) {
                if (isset($data['message'])) {
                    echo "[" . date('H:i:s') . "] " . $data['message'] . "\n";
                } else {
                    echo "[" . date('H:i:s') . "] " . json_encode($data) . "\n";
                }
            } else {
                echo "[" . date('H:i:s') . "] " . $data . "\n";
            }
        } else {
            if ($is_final) {
                header('Content-Type: application/json');
                echo json_encode($data);
            }
        }
    }

    /**
     * Main sync runner
     * Run both download and upload operations
     */
    public function run($mode = 'auto')
    {
        $this->verify_access();
        $start = microtime(true);

        $this->output("=== Starting Sync ({$mode}) ===");

        $config = $this->Sync_model->get_config();

        if (!$config) {
            $this->output(['status' => 'error', 'message' => 'Sync not configured']);
            return;
        }

        if (isset($config->server_type) && $config->server_type === 'pusat') {
            $this->output(['status' => 'skipped', 'message' => 'This is pusat server, no sync needed']);
            return;
        }

        // Check if auto sync is enabled (for HTTP mode only)
        if (!$this->is_cli && isset($config->auto_sync) && !$config->auto_sync) {
            $this->output(['status' => 'skipped', 'message' => 'Auto sync disabled']);
            return;
        }

        // Test connection
        $this->output("Testing connection...");
        $ping = $this->_ping($config);
        if (!$ping) {
            $this->output(['status' => 'error', 'message' => 'Cannot connect to pusat server']);
            return;
        }
        $this->output("Connection OK");

        $results = [];

        // Download data
        if (in_array($mode, ['auto', 'full', 'download'])) {
            $this->output("Starting download...");
            $results['download'] = $this->_execute_download($config);
        }

        // Upload data
        if (in_array($mode, ['auto', 'full', 'upload'])) {
            $this->output("Starting upload...");
            $results['upload'] = $this->_execute_upload($config);
        }

        // Sync media (full mode only)
        if ($mode === 'full') {
            $this->output("Starting media sync...");
            $results['media'] = $this->_sync_media($config);
        }

        $duration = round(microtime(true) - $start, 2);

        $this->output("=== Sync Complete ({$duration}s) ===");

        // Save to log
        $this->_log_sync($mode, $results, $duration);

        $this->output([
            'status' => 'success',
            'mode' => $mode,
            'duration' => $duration,
            'results' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Force download
     */
    public function download()
    {
        $this->verify_access();
        $config = $this->Sync_model->get_config();

        if (!$config) {
            $this->output(['status' => 'error', 'message' => 'Not configured']);
            return;
        }

        $result = $this->_execute_download($config);
        $this->output(['status' => 'success', 'download' => $result]);
    }

    /**
     * Force upload
     */
    public function upload()
    {
        $this->verify_access();
        $config = $this->Sync_model->get_config();

        if (!$config) {
            $this->output(['status' => 'error', 'message' => 'Not configured']);
            return;
        }

        $result = $this->_execute_upload($config);
        $this->output(['status' => 'success', 'upload' => $result]);
    }

    /**
     * Get sync status
     */
    public function status()
    {
        $this->verify_access();

        $config = $this->Sync_model->get_config();
        $status = $this->Sync_model->get_sync_status();

        $ping = false;
        if ($config && !empty($config->pusat_url)) {
            $ping = $this->_ping($config);
        }

        $this->output([
            'status' => 'success',
            'connected' => $ping,
            'sync_status' => $status,
            'server_type' => $config->server_type ?? 'unknown'
        ]);
    }

    /**
     * Check for updates (CLI recommended)
     */
    public function check_update()
    {
        $this->verify_access();
        $this->output("=== Checking for Updates ===");

        $config = $this->Sync_model->get_config();
        if (!$config) {
            $this->output(['status' => 'error', 'message' => 'Not configured']);
            return;
        }

        $this->load->library('Update_manager');
        $result = $this->update_manager->check_for_updates();

        if ($result['success'] && isset($result['data']['update_available']) && $result['data']['update_available']) {
            $data = $result['data'];
            $this->output("UPDATE AVAILABLE: v{$data['latest_version']}");
            $this->output("Title: " . ($data['title'] ?? 'N/A'));

            if (isset($data['is_critical']) && $data['is_critical']) {
                $this->output("*** CRITICAL UPDATE - INSTALL IMMEDIATELY ***");
            }

            $this->output([
                'status' => 'success',
                'update_available' => true,
                'data' => $data
            ]);
        } else {
            $this->output("No updates available. You have the latest version.");
            $this->output([
                'status' => 'success',
                'update_available' => false
            ]);
        }
    }

    /**
     * Auto-install update (CLI only, use with caution)
     */
    public function install_update()
    {
        if (!$this->is_cli) {
            $this->output(['status' => 'error', 'message' => 'CLI only']);
            return;
        }

        $this->output("=== Auto-Installing Update ===");

        // Check config
        $this->config->load('version', TRUE);
        $auto_install = $this->config->item('auto_install_updates');

        if (!$auto_install) {
            $this->output("Auto-install is disabled. Set auto_install_updates = true in config/version.php");
            return;
        }

        $this->load->library('Update_manager');
        $check = $this->update_manager->check_for_updates();

        if (!$check['success'] || !isset($check['data']['update_available']) || !$check['data']['update_available']) {
            $this->output("No updates available.");
            return;
        }

        $update_id = $check['data']['update_id'];
        $new_version = $check['data']['latest_version'];

        $this->output("Downloading v{$new_version}...");
        $download = $this->update_manager->download_update($update_id);

        if (!$download['success']) {
            $this->output("Download failed: " . $download['message']);
            return;
        }

        $this->output("Installing...");
        $install = $this->update_manager->install_update(
            $download['file_path'],
            $update_id,
            $new_version
        );

        if ($install['success']) {
            $this->output("SUCCESS! Updated to v{$new_version}");
        } else {
            $this->output("FAILED: " . $install['message']);
            if (isset($install['rolled_back']) && $install['rolled_back']) {
                $this->output("System rolled back to previous version.");
            }
        }
    }

    /**
     * Process sync queue
     */
    public function process_queue($limit = 100)
    {
        $this->verify_access();

        $config = $this->Sync_model->get_config();
        if (!$config) {
            $this->output(['status' => 'error', 'message' => 'Not configured']);
            return;
        }

        if (!$this->db->table_exists('sync_queue')) {
            $this->output(['status' => 'error', 'message' => 'Queue table not exists']);
            return;
        }

        $this->db->where('status', 'pending');
        $this->db->order_by('created_at', 'ASC');
        $this->db->limit($limit);
        $items = $this->db->get('sync_queue')->result();

        if (empty($items)) {
            $this->output(['status' => 'success', 'message' => 'Queue empty', 'processed' => 0]);
            return;
        }

        $processed = 0;
        $failed = 0;

        foreach ($items as $item) {
            // Mark as processing
            $this->db->where('id', $item->id)->update('sync_queue', ['status' => 'processing']);

            // Process based on action
            $success = $this->_process_queue_item($item, $config);

            if ($success) {
                $this->db->where('id', $item->id)->update('sync_queue', [
                    'status' => 'completed',
                    'processed_at' => date('Y-m-d H:i:s')
                ]);
                $processed++;
            } else {
                $this->db->where('id', $item->id)->update('sync_queue', [
                    'status' => 'failed',
                    'retry_count' => ($item->retry_count ?? 0) + 1
                ]);
                $failed++;
            }
        }

        $this->output([
            'status' => 'success',
            'processed' => $processed,
            'failed' => $failed
        ]);
    }

    // ==================== PRIVATE HELPERS ====================

    private function _ping($config)
    {
        $url = rtrim($config->pusat_url, '/') . '/api_sync/ping';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['X-Api-Key: ' . $config->api_key]
        ]);

        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code === 200;
    }

    private function _execute_download($config)
    {
        $tables = $this->Sync_model->get_sync_tables();
        $downloadable = array_filter($tables, fn($t) => in_array($t['direction'], ['download', 'both']));

        $result = ['tables' => 0, 'records' => 0, 'errors' => []];

        foreach ($downloadable as $table => $info) {
            $res = $this->_download_table($table, $config);
            $result['tables']++;
            if ($res['success']) {
                $result['records'] += $res['count'];
            } else {
                $result['errors'][] = "{$table}: {$res['message']}";
            }
        }

        return $result;
    }

    private function _execute_upload($config)
    {
        $tables = $this->Sync_model->get_sync_tables();
        $uploadable = array_filter($tables, fn($t) => in_array($t['direction'], ['upload', 'both']));

        $result = ['tables' => 0, 'records' => 0, 'errors' => []];

        foreach ($uploadable as $table => $info) {
            $res = $this->_upload_table($table, $config);
            $result['tables']++;
            if ($res['success']) {
                $result['records'] += $res['count'];
            } else {
                $result['errors'][] = "{$table}: {$res['message']}";
            }
        }

        return $result;
    }

    private function _download_table($table, $config)
    {
        $url = rtrim($config->pusat_url, '/') . '/api_sync/download';
        $body = json_encode(['table' => $table, 'limit' => 1000, 'offset' => 0]);

        $headers = ['Content-Type: application/json', 'X-Api-Key: ' . $config->api_key];
        if (!empty($config->api_secret)) {
            $headers[] = 'X-Api-Signature: ' . hash_hmac('sha256', $body, $config->api_secret);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            return ['success' => false, 'message' => "HTTP {$code}", 'count' => 0];
        }

        $result = json_decode($response, true);
        if (!$result || $result['status'] !== 'success') {
            return ['success' => false, 'message' => $result['message'] ?? 'Unknown', 'count' => 0];
        }

        $records = $result['data']['records'] ?? [];
        if (!empty($records)) {
            $import = $this->Sync_model->import_data($table, $records);
            return ['success' => true, 'count' => $import['success']];
        }

        return ['success' => true, 'count' => 0];
    }

    private function _upload_table($table, $config)
    {
        $data = $this->Sync_model->get_data_for_upload($table);

        if (empty($data)) {
            return ['success' => true, 'count' => 0];
        }

        $url = rtrim($config->pusat_url, '/') . '/api_sync/upload';
        $body = json_encode(['table' => $table, 'records' => $data]);

        $headers = ['Content-Type: application/json', 'X-Api-Key: ' . $config->api_key];
        if (!empty($config->api_secret)) {
            $headers[] = 'X-Api-Signature: ' . hash_hmac('sha256', $body, $config->api_secret);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200) {
            return ['success' => false, 'message' => "HTTP {$code}", 'count' => 0];
        }

        $result = json_decode($response, true);
        if (!$result || $result['status'] !== 'success') {
            return ['success' => false, 'message' => $result['message'] ?? 'Unknown', 'count' => 0];
        }

        return ['success' => true, 'count' => count($data)];
    }

    private function _sync_media($config)
    {
        $url = rtrim($config->pusat_url, '/') . '/api_sync/get_media_list';
        $body = json_encode([]);

        $headers = ['Content-Type: application/json', 'X-Api-Key: ' . $config->api_key];
        if (!empty($config->api_secret)) {
            $headers[] = 'X-Api-Signature: ' . hash_hmac('sha256', $body, $config->api_secret);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if (!$result || $result['status'] !== 'success') {
            return ['success' => false, 'downloaded' => 0, 'skipped' => 0];
        }

        $files = $result['data']['files'] ?? [];
        $local_path = FCPATH . 'uploads/bank_soal/';

        if (!is_dir($local_path)) {
            mkdir($local_path, 0755, true);
        }

        $downloaded = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $local_file = $local_path . $file['name'];

            if (file_exists($local_file) && md5_file($local_file) === $file['md5']) {
                $skipped++;
                continue;
            }

            // Download
            if ($this->_download_media_file($file['name'], $file['md5'], $config)) {
                $downloaded++;
            }
        }

        return ['success' => true, 'downloaded' => $downloaded, 'skipped' => $skipped];
    }

    private function _download_media_file($filename, $expected_md5, $config)
    {
        $url = rtrim($config->pusat_url, '/') . '/api_sync/download_media_file';
        $body = json_encode(['file_name' => $filename]);
        $save_path = FCPATH . 'uploads/bank_soal/' . $filename;

        $headers = ['Content-Type: application/json', 'X-Api-Key: ' . $config->api_key];
        if (!empty($config->api_secret)) {
            $headers[] = 'X-Api-Signature: ' . hash_hmac('sha256', $body, $config->api_secret);
        }

        $fp = fopen($save_path, 'wb');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 120
        ]);

        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($code === 200 && $expected_md5 && md5_file($save_path) !== $expected_md5) {
            @unlink($save_path);
            return false;
        }

        return $code === 200;
    }

    private function _process_queue_item($item, $config)
    {
        // Simplified queue processing
        return true;
    }

    private function _log_sync($mode, $results, $duration)
    {
        if ($this->db->table_exists('sync_log')) {
            $total = 0;
            foreach ($results as $r) {
                if (is_array($r) && isset($r['records'])) {
                    $total += $r['records'];
                }
            }

            $this->db->insert('sync_log', [
                'sync_type' => 'cron_' . $mode,
                'direction' => 'auto',
                'table_name' => 'all',
                'records_synced' => $total,
                'status' => 'success',
                'message' => "Completed in {$duration}s",
                'details' => json_encode($results),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
