<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sync Service Library
 * Library untuk menjalankan background sync dan scheduled sync
 */
class Sync_service
{

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('Sync_model');
    }

    /**
     * Check if sync is needed and execute if necessary
     * Called on startup if sync_on_startup is enabled
     */
    public function check_and_sync()
    {
        $config = $this->CI->Sync_model->get_config();

        if (!$config) {
            return false;
        }

        // Only sync on local servers
        if ($config->server_type === 'pusat') {
            return false;
        }

        // Check if auto sync is enabled
        if (!$config->auto_sync) {
            return false;
        }

        // Check if sync interval has passed
        $last_sync = $config->last_sync_download ?: '1970-01-01 00:00:00';
        $interval = $config->sync_interval ?: 60; // default 60 minutes

        $next_sync = strtotime($last_sync) + ($interval * 60);

        if (time() < $next_sync) {
            return false; // Not time yet
        }

        // Execute background sync
        return $this->execute_download();
    }

    /**
     * Execute download sync from pusat
     */
    public function execute_download($tables = null)
    {
        $config = $this->CI->Sync_model->get_config();

        if (!$config || empty($config->pusat_url) || empty($config->api_key)) {
            return [
                'success' => false,
                'message' => 'Konfigurasi tidak lengkap'
            ];
        }

        // Default tables
        if (empty($tables)) {
            $sync_tables = $this->CI->Sync_model->get_sync_tables();
            $tables = [];
            foreach ($sync_tables as $table => $tconfig) {
                if ($tconfig['direction'] !== 'upload') {
                    $tables[] = $table;
                }
            }
        }

        $ch = curl_init($config->pusat_url . '/api_sync/download_batch');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'tables' => json_encode($tables),
                'last_sync' => $config->last_sync_download
            ],
            CURLOPT_HTTPHEADER => [
                'X-Api-Key: ' . $config->api_key
            ],
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $error
            ];
        }

        $result = json_decode($response, true);

        if (!$result || $result['status'] !== 'success') {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Unknown error'
            ];
        }

        // Import data
        $total_success = 0;
        $total_failed = 0;

        if (!empty($result['data']['data'])) {
            foreach ($result['data']['data'] as $table => $records) {
                $import_result = $this->CI->Sync_model->import_data($table, $records);
                $total_success += $import_result['success'];
                $total_failed += $import_result['failed'];
            }
        }

        // Update last sync
        $this->CI->Sync_model->save_config([
            'last_sync_download' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => "Download selesai: {$total_success} berhasil, {$total_failed} gagal"
        ];
    }

    /**
     * Execute upload sync to pusat
     */
    public function execute_upload()
    {
        $config = $this->CI->Sync_model->get_config();

        if (!$config || empty($config->pusat_url) || empty($config->api_key)) {
            return [
                'success' => false,
                'message' => 'Konfigurasi tidak lengkap'
            ];
        }

        $pending_data = $this->CI->Sync_model->get_pending_upload();

        if (empty($pending_data)) {
            return [
                'success' => true,
                'message' => 'Tidak ada data yang perlu di-upload'
            ];
        }

        $ch = curl_init($config->pusat_url . '/api_sync/upload_batch');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'data' => json_encode($pending_data)
            ],
            CURLOPT_HTTPHEADER => [
                'X-Api-Key: ' . $config->api_key
            ],
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $error
            ];
        }

        $result = json_decode($response, true);

        if (!$result || ($result['status'] !== 'success' && $result['status'] !== 'partial')) {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Unknown error'
            ];
        }

        // Mark as synced
        $sync_tables = $this->CI->Sync_model->get_sync_tables();
        foreach ($pending_data as $table => $records) {
            $pk = $sync_tables[$table]['pk'] ?? 'id';
            $ids = array_column($records, $pk);
            $this->CI->Sync_model->mark_as_synced($table, $ids);
        }

        // Update last sync
        $this->CI->Sync_model->save_config([
            'last_sync_upload' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => $result['message'] ?? 'Upload berhasil'
        ];
    }

    /**
     * Get sync status summary
     */
    public function get_status()
    {
        return $this->CI->Sync_model->get_sync_status();
    }

    /**
     * Check connection to pusat server
     */
    public function ping()
    {
        return $this->CI->Sync_model->test_connection();
    }
}
