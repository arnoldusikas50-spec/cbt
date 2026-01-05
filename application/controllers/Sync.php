<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sync Controller
 * Controller untuk mengelola sinkronisasi data di server lokal
 */
class Sync extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Sync_model');
        $this->load->model('Master_model', 'master');

        // Check authentication
        if (!$this->auth->logged_in()) {
            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                exit;
            }
            redirect('auth');
        }

        // Only admin can access sync
        if (!$this->auth->is_admin()) {
            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
                exit;
            }
            redirect('dashboard');
        }
    }

    /**
     * Get common data for views
     */
    private function get_common_data()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $setting = $this->dashboard->getSetting();
        $user = $this->auth->user();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();

        return [
            'setting' => $setting,
            'user' => $user,
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp_sekarang' => $tp->tahun,
            'smt_sekarang' => $smt->id_smt
        ];
    }

    /**
     * Main sync dashboard
     */
    public function index()
    {
        $data = $this->get_common_data();
        $data['title'] = 'Sinkronisasi Data';
        $data['judul'] = 'Sinkronisasi';
        $data['subjudul'] = 'Dashboard';

        // Load Configuration
        $config = $this->Sync_model->get_config();
        $data['config'] = $config;
        $data['is_pusat'] = ($config && isset($config->server_type) && $config->server_type === 'pusat');

        $data['sync_status'] = $this->Sync_model->get_sync_status();
        $data['sync_log'] = $this->Sync_model->get_sync_log(20);
        $data['sync_tables'] = $this->Sync_model->get_sync_tables_with_stats();
        $data['sync_stats_7days'] = $this->Sync_model->get_sync_stats_last_7_days();

        // Additional Dashboard Stats
        $data['queue_count'] = $this->db->table_exists('sync_queue') ? $this->db->count_all('sync_queue') : 0;
        $data['last_sync'] = $this->db->order_by('created_at', 'DESC')->get('sync_log', 1)->row();

        // Calculate Pending Records (Simplified)
        $data['pending_records'] = 0;
        if (isset($data['sync_tables'])) {
            // Logic to sum pending if available in stats, otherwise default 0
            // For now we rely on what get_sync_tables_with_stats provides
        }

        // Load app version
        $this->config->load('version', TRUE);
        $data['app_version'] = $this->config->item('app_version') ?: '1.0.0';

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('admin/sync/index', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    /**
     * Configuration page
     */
    public function config()
    {
        $data = $this->get_common_data();
        $data['title'] = 'Konfigurasi Sync';
        $data['judul'] = 'Sinkronisasi';
        $data['subjudul'] = 'Konfigurasi';
        $data['config'] = $this->Sync_model->get_config();

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('admin/sync/config', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    /**
     * Save configuration
     */
    public function save_config()
    {
        header('Content-Type: application/json');

        $data = [
            'server_type' => $this->input->post('server_type'),
            'pusat_url' => rtrim($this->input->post('pusat_url'), '/'),
            'api_key' => $this->input->post('api_key'),
            'api_secret' => $this->input->post('api_secret'),
            'auto_sync' => $this->input->post('auto_sync') ? 1 : 0,
            'sync_interval' => $this->input->post('sync_interval') ?: 60,
            'sync_on_startup' => $this->input->post('sync_on_startup') ? 1 : 0
        ];

        if ($this->Sync_model->save_config($data)) {
            echo json_encode(['status' => 'success', 'message' => 'Konfigurasi berhasil disimpan']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan konfigurasi']);
        }
    }

    /**
     * Test connection to pusat server
     */
    public function test_connection()
    {
        header('Content-Type: application/json');

        $result = $this->Sync_model->test_connection();
        echo json_encode($result);
    }

    /**
     * AJAX: Reset sync time (Force Full Sync)
     */
    public function reset_sync()
    {
        header('Content-Type: application/json');

        if ($this->Sync_model->reset_sync_time()) {
            echo json_encode(['status' => 'success', 'message' => 'Waktu sinkronisasi berhasil direset. Silakan download ulang data.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mereset waktu sinkronisasi']);
        }
    }

    /**
     * AJAX: Get stats of recently synced data (candidates for undo)
     */
    public function get_recent_sync_stats()
    {
        // Ensure clean JSON output
        // if (ob_get_length())
        //    ob_clean();
        header('Content-Type: application/json');

        try {
            $minutes = 30; // Check data in last 30 mins
            $time_threshold = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
            $stats = [];
            $total_records = 0;

            if (!isset($this->Sync_model) || !isset($this->Sync_model->sync_tables)) {
                throw new Exception("Sync Model or sync_tables not loaded");
            }

            foreach ($this->Sync_model->sync_tables as $table => $conf) {
                if (!$this->db->table_exists($table))
                    continue;

                // Check fields
                if (!$this->db->field_exists('sync_at', $table))
                    continue;

                $count = $this->db->where('sync_at >=', $time_threshold)->count_all_results($table);
                if ($count > 0) {
                    $stats[$table] = $count;
                    $total_records += $count;
                }
            }

            echo json_encode(['status' => 'success', 'data' => $stats, 'total' => $total_records]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
        }
        exit;
    }

    /**
     * AJAX: Execute Undo (Delete recently synced records)
     */
    public function undo_sync()
    {
        // Ensure clean JSON output
        if (ob_get_length())
            ob_clean();
        header('Content-Type: application/json');

        try {
            $minutes = 30;
            $time_threshold = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));
            $deleted_total = 0;

            // Disable FK checks to allow deletion in any order
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($this->Sync_model->sync_tables as $table => $conf) {
                if (!$this->db->table_exists($table))
                    continue;
                if (!$this->db->field_exists('sync_at', $table))
                    continue;

                $this->db->where('sync_at >=', $time_threshold);
                $this->db->delete($table);
                $deleted_total += $this->db->affected_rows();
            }

            // Re-enable FK checks
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

            // Also reset sync config to be safe
            $this->Sync_model->reset_sync_time();

            echo json_encode(['status' => 'success', 'message' => "Berhasil menghapus $deleted_total data duplikat yang baru masuk."]);
        } catch (Throwable $e) {
            // Ensure FK checks specific re-enable in case of error
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Helper to send signed request to server pusat
     */
    private function _send_request($endpoint, $data = [], $method = 'POST')
    {
        $config = $this->Sync_model->get_config();

        if (!$config || empty($config->pusat_url) || empty($config->api_key)) {
            return ['status' => 'error', 'message' => 'Konfigurasi belum lengkap'];
        }

        $url = rtrim($config->pusat_url, '/') . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $config->api_key
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);

        if ($method === 'POST') {
            $payload = json_encode($data);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // HMAC Signature
            if (!empty($config->api_secret)) {
                $signature = hash_hmac('sha256', $payload, $config->api_secret);
                $headers[] = 'X-Api-Signature: ' . $signature;
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Debug: Log to file
        error_log("SYNC DEBUG: URL=$url, HTTP=$http_code, Error=$error, Response=" . substr($response, 0, 500));

        if ($error) {
            return ['status' => 'error', 'message' => 'Koneksi Gagal: ' . $error];
        }

        $result = json_decode($response, true);
        if (!$result) {
            // Try to extract error from raw response
            $error_msg = 'Server Error (' . $http_code . ')';
            if ($response) {
                $error_msg .= ': ' . substr($response, 0, 200);
            }
            return ['status' => 'error', 'message' => $error_msg];
        }

        // Propagate error from server
        if ($result['status'] === 'error') {
            return $result;
        }

        return $result;
    }

    /**
     * Execute sync download from pusat server
     */
    public function download()
    {
        header('Content-Type: application/json');

        try {
            $config = $this->Sync_model->get_config();

            if (!$config) {
                echo json_encode(['status' => 'error', 'message' => 'Konfigurasi sync belum diatur']);
                return;
            }

            $tables = $this->input->post('tables');
            if (empty($tables)) {
                $sync_tables = $this->Sync_model->get_sync_tables();
                $tables = [];
                foreach ($sync_tables as $table => $tconfig) {
                    if ($tconfig['direction'] !== 'upload') {
                        $tables[] = $table;
                    }
                }
            } elseif (is_string($tables)) {
                $tables = json_decode($tables, true);
            }

            // Send Request
            $result = $this->_send_request('/api_sync/download_batch', [
                'tables' => $tables,
                'last_sync' => isset($config->last_sync_download) ? $config->last_sync_download : null
            ]);

            if ($result['status'] !== 'success') {
                echo json_encode($result);
                return;
            }

            // Import data
            $import_results = [];
            $total_success = 0;
            $total_failed = 0;

            if (!empty($result['data'])) {
                $data_tables = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];

                foreach ($data_tables as $table => $records) {
                    if (is_array($records)) {
                        $import_result = $this->Sync_model->import_data($table, $records);
                        $import_results[$table] = $import_result;
                        $total_success += $import_result['success'];
                        $total_failed += $import_result['failed'];
                    }
                }
            }

            $this->Sync_model->save_config(['last_sync_download' => date('Y-m-d H:i:s')]);

            $this->Sync_model->create_log([
                'sync_type' => 'download',
                'sync_direction' => 'pusat_to_lokal',
                'table_name' => implode(',', $tables),
                'records_total' => $total_success + $total_failed,
                'records_success' => $total_success,
                'records_failed' => $total_failed,
                'status' => $total_failed > 0 ? ($total_success > 0 ? 'partial' : 'failed') : 'success'
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => "Download selesai: {$total_success} masuk, {$total_failed} gagal",
                'details' => $import_results
            ]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Download Error: ' . $e->getMessage()]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Download Fatal: ' . $e->getMessage()]);
        }
    }

    /**
     * Download single table (for per-table progress)
     */
    public function download_table()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        header('Content-Type: application/json');

        try {
            $table = $this->input->post('table');
            if (empty($table)) {
                echo json_encode(['status' => 'error', 'message' => 'Table name required']);
                return;
            }

            $config = $this->Sync_model->get_config();
            if (!$config) {
                echo json_encode(['status' => 'error', 'message' => 'Config not found']);
                return;
            }

            // Create Log
            $log_id = $this->Sync_model->create_log([
                'sync_type' => 'download',
                'table_name' => $table,
                'status' => 'running',
                'records_total' => 0
            ]);

            $last_sync = isset($config->last_sync_download) ? $config->last_sync_download : null;

            // Loop for chunking
            $limit = 1000;
            $offset = 0;
            $total_success = 0;
            $total_failed = 0;
            $total_deleted = 0;
            $all_errors = [];

            while (true) {
                // Download from server pusat chunked
                $result = $this->_send_request('/api_sync/download', [
                    'table' => $table,
                    'last_sync' => $last_sync,
                    'limit' => $limit,
                    'offset' => $offset
                ]);



                if ($result['status'] !== 'success') {
                    $this->Sync_model->update_log($log_id, [
                        'status' => 'failed',
                        'error_message' => $result['message']
                    ]);
                    echo json_encode($result);
                    return;
                }

                $records = [];
                // Handle different response structures
                if (isset($result['data']['records'])) {
                    $records = $result['data']['records'];
                } elseif (isset($result['data']) && is_array($result['data']) && isset($result['data'][0])) {
                    // Direct array
                    $records = $result['data'];
                }

                $chunk_size = count($records);

                if ($chunk_size > 0) {
                    $import_result = $this->Sync_model->import_data($table, $records);
                    $total_success += $import_result['success'];
                    $total_failed += $import_result['failed'];
                    if (!empty($import_result['errors'])) {
                        $all_errors = array_merge($all_errors, $import_result['errors']);
                    }
                }

                // Handle deleted records (Delta Sync)
                if (isset($result['data']['deleted_ids']) && !empty($result['data']['deleted_ids'])) {
                    $deleted_ids = $result['data']['deleted_ids'];
                    $deleted_count = $this->Sync_model->delete_by_ids($table, $deleted_ids);
                    $total_deleted += $deleted_count;
                }

                // Break if last chunk
                if ($chunk_size < $limit) {
                    break;
                }
                $offset += $limit;

                // Optional small delay to prevent server overload
                // usleep(100000); 
            }

            // Update Log
            $status = 'success';
            if ($total_failed > 0) {
                $status = ($total_success > 0) ? 'partial' : 'failed';
            }

            $this->Sync_model->update_log($log_id, [
                'status' => $status,
                'records_total' => $total_success + $total_failed,
                'records_success' => $total_success,
                'records_failed' => $total_failed,
                'error_message' => (!empty($all_errors)) ? $all_errors : null
            ]);

            echo json_encode([
                'status' => 'success',
                'table' => $table,
                'success' => $total_success,
                'failed' => $total_failed,
                'deleted' => $total_deleted,
                'errors' => $all_errors
            ]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Execute sync upload to pusat server
     */
    public function upload()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        header('Content-Type: application/json');

        $pending_data = $this->Sync_model->get_pending_upload();

        $skip_media = $this->input->post('skip_media');

        if (empty($pending_data)) {
            echo json_encode(['status' => 'success', 'message' => 'Tidak ada data yang perlu di-upload']);
            return;
        }

        $media_count = 0;
        // Process media files if not skipped (BACKWARD COMPATIBILITY)
        if (!$skip_media || $skip_media === 'false') {
            $media_count = $this->Sync_model->process_media_files($pending_data);
        }

        $sync_tables = $this->Sync_model->get_sync_tables();
        $total_success = 0;
        $total_failed = 0;
        $batch_errors = [];

        // CHUNKED UPLOAD STRATEGY
        $CHUNK_SIZE = 200; // Kirim per 200 baris agar ringan

        foreach ($pending_data as $table => $rows) {
            if (!isset($sync_tables[$table]))
                continue;

            $pk = $sync_tables[$table]['pk'] ?? 'id';
            $chunks = array_chunk($rows, $CHUNK_SIZE);

            foreach ($chunks as $chunk) {
                // Kirim Chunk ke Server Pusat
                $result = $this->_send_request('/api_sync/upload', [
                    'table' => $table,
                    'records' => $chunk
                ]);

                // Cek hasil
                if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === 'partial')) {
                    // Tandai sudah sync di lokal
                    $ids = array_column($chunk, $pk);
                    if (!empty($ids)) {
                        $this->Sync_model->mark_as_synced($table, $ids);
                    }

                    $res_success = isset($result['data']['success']) ? $result['data']['success'] : (isset($result['success']) ? $result['success'] : 0);
                    $res_failed = isset($result['data']['failed']) ? $result['data']['failed'] : (isset($result['failed']) ? $result['failed'] : 0);

                    $total_success += $res_success;
                    $total_failed += $res_failed;
                } else {
                    // Chunk gagal total
                    $total_failed += count($chunk);
                    $msg = isset($result['message']) ? $result['message'] : 'Unknown Error';
                    $batch_errors[] = "Error $table: $msg";
                }
            }
        }

        $this->Sync_model->save_config(['last_sync_upload' => date('Y-m-d H:i:s')]);

        // Logging
        $status = ($total_failed > 0) ? (($total_success > 0) ? 'partial' : 'failed') : 'success';

        $log_data = [
            'sync_type' => 'upload',
            'sync_direction' => 'lokal_to_pusat',
            'table_name' => implode(',', array_keys($pending_data)),
            'records_total' => $total_success + $total_failed,
            'records_success' => $total_success,
            'records_failed' => $total_failed,
            'status' => $status
        ];

        if (!empty($batch_errors)) {
            $log_data['message'] = json_encode($batch_errors);
        }

        $this->Sync_model->create_log($log_data);

        if ($status === 'success') {
            $msg = 'Upload data selesai';
        } elseif ($status === 'partial') {
            $msg = "Upload selesai sebagian ($total_failed gagal)";
        } else {
            $msg = "Upload gagal ($total_failed data gagal)";
        }

        $response = [
            'status' => $status,
            'message' => $msg,
            'total_success' => $total_success,
            'total_failed' => $total_failed
        ];

        if (isset($media_count) && $media_count > 0) {
            $response['message'] .= " ($media_count file media diproses)";
        }

        echo json_encode($response);
    }

    /**
     * Full sync - download first, then upload
     */
    public function full_sync()
    {
        header('Content-Type: application/json');

        $config = $this->Sync_model->get_config();
        $upload_data = $this->Sync_model->get_pending_upload();

        // Process media files first
        $media_count = $this->Sync_model->process_media_files($upload_data);

        $result = $this->_send_request('/api_sync/full_sync', [
            'last_sync' => $config->last_sync_download,
            'upload_data' => $upload_data
        ]);

        if ($result['status'] !== 'success') {
            echo json_encode($result);
            return;
        }

        // Import downloaded data
        $import_results = [];
        $total_success = 0;

        $downloads = $result['data']['download']['data'] ?? [];
        foreach ($downloads as $table => $records) {
            if (is_array($records)) {
                $import_result = $this->Sync_model->import_data($table, $records);
                $import_results[$table] = $import_result;
                $total_success += $import_result['success'];
            }
        }

        // Mark uploaded
        if (!empty($upload_data)) {
            $sync_tables = $this->Sync_model->get_sync_tables();
            foreach ($upload_data as $table => $records) {
                $pk = $sync_tables[$table]['pk'] ?? 'id';
                $ids = array_column($records, $pk);
                $this->Sync_model->mark_as_synced($table, $ids);
            }
        }

        $this->Sync_model->save_config([
            'last_sync_download' => date('Y-m-d H:i:s'),
            'last_sync_upload' => date('Y-m-d H:i:s')
        ]);

        $this->Sync_model->create_log([
            'sync_type' => 'full',
            'sync_direction' => 'pusat_to_lokal',
            'table_name' => 'full_sync',
            'records_total' => $total_success,
            'records_success' => $total_success,
            'status' => 'success'
        ]);

        $msg = 'Full sync berhasil';
        if (isset($media_count) && $media_count > 0) {
            $msg .= " (" . $media_count . " file media terupload)";
        }

        echo json_encode([
            'status' => 'success',
            'message' => $msg,
            'download_results' => $import_results,
            'sync_timestamp' => $result['data']['sync_timestamp'] ?? date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get sync status for AJAX polling
     */
    public function status()
    {
        header('Content-Type: application/json');
        echo json_encode($this->Sync_model->get_sync_status());
    }

    /**
     * Get sync log for AJAX
     */
    public function log()
    {
        header('Content-Type: application/json');
        $limit = $this->input->get('limit') ?: 50;
        echo json_encode($this->Sync_model->get_sync_log($limit));
    }





    /**
     * School management (server pusat only)
     */
    public function schools()
    {
        if (!$this->Sync_model->is_pusat_server()) {
            redirect('sync');
        }

        // Ensure tables have isolation columns
        $this->Sync_model->optimize_tables();

        $data = $this->get_common_data();
        $data['title'] = 'Manajemen Sekolah';
        $data['judul'] = 'Sinkronisasi';
        $data['subjudul'] = 'Kelola Sekolah';
        $data['schools'] = $this->Sync_model->get_all_schools();

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('admin/sync/schools', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    /**
     * Register new school (server pusat only)
     */
    public function register_school()
    {
        header('Content-Type: application/json');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya tersedia di server pusat']);
            return;
        }

        $data = [
            'npsn' => $this->input->post('npsn'),
            'nama_sekolah' => $this->input->post('nama_sekolah'),
            'alamat' => $this->input->post('alamat'),
            'kota' => $this->input->post('kota'),
            'provinsi' => $this->input->post('provinsi')
        ];

        if (empty($data['nama_sekolah'])) {
            echo json_encode(['status' => 'error', 'message' => 'Nama sekolah diperlukan']);
            return;
        }

        $result = $this->Sync_model->register_school($data);

        if ($result) {
            $school = $this->db->where('id', $result)->get('sync_schools')->row();
            echo json_encode([
                'status' => 'success',
                'message' => 'Sekolah berhasil didaftarkan',
                'data' => [
                    'school_uuid' => $school->school_uuid,
                    'api_key' => $school->api_key,
                    'api_secret' => $school->api_secret
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftarkan sekolah']);
        }
    }

    /**
     * Toggle school active status
     */
    public function toggle_school($id)
    {
        header('Content-Type: application/json');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya tersedia di server pusat']);
            return;
        }

        $school = $this->db->where('id', $id)->get('sync_schools')->row();
        if (!$school) {
            echo json_encode(['status' => 'error', 'message' => 'Sekolah tidak ditemukan']);
            return;
        }

        $new_status = $school->is_active ? 0 : 1;
        $this->db->where('id', $id)->update('sync_schools', ['is_active' => $new_status]);

        echo json_encode([
            'status' => 'success',
            'message' => $new_status ? 'Sekolah diaktifkan' : 'Sekolah dinonaktifkan',
            'is_active' => $new_status
        ]);
    }

    /**
     * Regenerate API key for school
     */
    public function regenerate_key($id)
    {
        header('Content-Type: application/json');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya tersedia di server pusat']);
            return;
        }

        $new_key = $this->Sync_model->generate_api_key();
        $new_secret = $this->Sync_model->generate_api_secret();

        $this->db->where('id', $id)->update('sync_schools', [
            'api_key' => $new_key,
            'api_secret' => $new_secret
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'API Key berhasil di-regenerate',
            'api_key' => $new_key,
            'api_secret' => $new_secret
        ]);
    }
    public function get_admin_detail($school_id)
    {
        header('Content-Type: application/json');
        if (!$this->Sync_model->is_pusat_server())
            return;

        $user = $this->Sync_model->get_admin_by_school_id($school_id);
        if ($user) {
            unset($user->password, $user->salt, $user->ip_address, $user->activation_code, $user->forgotten_password_code);
            echo json_encode(['status' => 'success', 'data' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }
    }

    public function save_school_admin()
    {
        header('Content-Type: application/json');

        $this->load->model('Auth_model');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya tersedia di server pusat']);
            return;
        }

        $school_id = $this->input->post('school_id');
        $user_id = $this->input->post('user_id'); // Hidden ID if editing
        $username = $this->input->post('username', true);
        $password = $this->input->post('password');
        $email = $this->input->post('email', true);

        if (empty($school_id) || empty($username) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Field wajib harus diisi']);
            return;
        }

        $school = $this->db->where('id', $school_id)->get('sync_schools')->row();
        if (!$school) {
            echo json_encode(['status' => 'error', 'message' => 'Sekolah tidak ditemukan']);
            return;
        }

        if (!empty($user_id)) {
            // UPDATE EXISTING USER
            $currUser = $this->db->where('id', $user_id)->get('users')->row();
            if (!$currUser) {
                echo json_encode(['status' => 'error', 'message' => 'User ID tidak valid']);
                return;
            }

            // Check username conflict (pass user_id to ignore self)
            if ($this->Auth_model->username_check($username, $user_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan user lain']);
                return;
            }

            $data = [
                'username' => $username,
                'email' => $email
            ];

            if (!empty($password)) {
                if (strlen($password) < 8) {
                    echo json_encode(['status' => 'error', 'message' => 'Password minimal 8 karakter']);
                    return;
                }
                $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            if ($this->Auth_model->changeUser($user_id, $data)) {
                echo json_encode(['status' => 'success', 'message' => 'Akun Admin ' . $school->nama_sekolah . ' diperbarui']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui user']);
            }

        } else {
            // CREATE NEW USER
            if (empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'Password wajib diisi']);
                return;
            }

            // Check if username exists
            if ($this->Auth_model->isExist($username)) {
                echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan']);
                return;
            }

            $data = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'email' => $email,
                'first_name' => 'Admin',
                'last_name' => $school->nama_sekolah,
                'company' => $school->school_uuid,
                'phone' => '00000000',
                'active' => 1
            ];

            // Register as Admin (Group 1)
            $id = $this->Auth_model->register($data, 1);

            if ($id) {
                echo json_encode(['status' => 'success', 'message' => 'Akun Admin untuk sekolah ' . $school->nama_sekolah . ' berhasil dibuat.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data user ke database.']);
            }
        }
    }

    /**
     * Impersonate school (Login as school admin)
     */
    public function impersonate($uuid)
    {
        if (!$this->Sync_model->is_pusat_server()) {
            show_error('Akses ditolak', 403);
            return;
        }

        // Try UUID first
        $this->db->where('school_uuid', $uuid);
        $school = $this->db->get('sync_schools')->row();

        // Fallback ID (if old link)
        if (!$school && is_numeric($uuid)) {
            $school = $this->db->where('id', $uuid)->get('sync_schools')->row();
        }

        if ($school) {
            $this->session->set_userdata('school_uuid', $school->school_uuid);
            $this->session->set_userdata('school_name', $school->nama_sekolah);
            $this->session->set_flashdata('message', 'Anda sekarang login sebagai: ' . $school->nama_sekolah);
            redirect('dashboard');
        } else {
            $this->session->set_flashdata('error', 'Sekolah tidak ditemukan');
            redirect('sync/schools');
        }
    }

    /**
     * Stop impersonating
     */
    public function stop_impersonate()
    {
        $this->session->unset_userdata('school_uuid');
        $this->session->unset_userdata('school_name');
        $this->session->set_flashdata('message', 'Kembali ke mode Admin Pusat');
        redirect('sync');
    }

    // ==================== UPDATE PACKAGE MANAGEMENT (SERVER PUSAT) ====================

    /**
     * Manage update packages page (server pusat only)
     */
    public function manage_updates()
    {
        if (!$this->Sync_model->is_pusat_server()) {
            redirect('sync');
        }

        $data = $this->get_common_data();
        $data['title'] = 'Kelola Update';
        $data['judul'] = 'Sinkronisasi';
        $data['subjudul'] = 'Kelola Paket Update';

        // Get all updates
        $data['updates'] = [];
        if ($this->db->table_exists('system_updates')) {
            $this->db->order_by('version_code', 'DESC');
            $data['updates'] = $this->db->get('system_updates')->result();
        }

        // Load version config from this server
        $this->config->load('version', TRUE);
        $data['server_version'] = $this->config->item('app_version') ?: '1.0.0';

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('admin/sync/manage_updates', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    /**
     * Upload new update package
     */
    public function upload_update()
    {
        header('Content-Type: application/json');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya untuk server pusat']);
            return;
        }

        // Validate required fields
        $version = $this->input->post('version');
        $title = $this->input->post('title');
        $description = $this->input->post('description');
        $is_critical = $this->input->post('is_critical') ? 1 : 0;
        $min_version = $this->input->post('min_version');

        if (!$version || !$title) {
            echo json_encode(['status' => 'error', 'message' => 'Versi dan judul diperlukan']);
            return;
        }

        // Validate version format (x.y.z)
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            echo json_encode(['status' => 'error', 'message' => 'Format versi harus x.y.z (contoh: 2.1.0)']);
            return;
        }

        // Calculate version code
        $parts = explode('.', $version);
        $version_code = ((int) $parts[0] * 10000) + ((int) $parts[1] * 100) + (int) $parts[2];

        // Check if version already exists
        if ($this->db->table_exists('system_updates')) {
            $existing = $this->db->where('version', $version)->get('system_updates')->row();
            if ($existing) {
                echo json_encode(['status' => 'error', 'message' => 'Versi ' . $version . ' sudah ada']);
                return;
            }
        }

        // Handle file upload
        if (!isset($_FILES['update_file']) || $_FILES['update_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'File ZIP diperlukan']);
            return;
        }

        // Validate file type
        $file = $_FILES['update_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            echo json_encode(['status' => 'error', 'message' => 'Hanya file ZIP yang diperbolehkan']);
            return;
        }

        // Create updates directory
        $upload_dir = FCPATH . 'uploads/system_updates/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate filename
        $file_name = 'update_v' . $version . '_' . date('Ymd_His') . '.zip';
        $file_path = $upload_dir . $file_name;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file']);
            return;
        }

        // Calculate checksum
        $file_checksum = hash_file('sha256', $file_path);
        $file_size = filesize($file_path);

        // Ensure table exists
        if (!$this->db->table_exists('system_updates')) {
            echo json_encode(['status' => 'error', 'message' => 'Tabel system_updates belum dibuat. Silakan jalankan SQL migration.']);
            @unlink($file_path);
            return;
        }

        // Insert to database
        $insert_data = [
            'version' => $version,
            'version_code' => $version_code,
            'title' => $title,
            'description' => $description,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'file_size' => $file_size,
            'file_checksum' => $file_checksum,
            'min_version' => $min_version ?: null,
            'is_critical' => $is_critical,
            'is_active' => 1,
            'download_count' => 0,
            'created_by' => $this->session->userdata('id_user'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('system_updates', $insert_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Update v' . $version . ' berhasil diupload',
                'data' => [
                    'id' => $insert_id,
                    'version' => $version,
                    'file_size' => $file_size,
                    'checksum' => $file_checksum
                ]
            ]);
        } else {
            @unlink($file_path);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database']);
        }
    }

    /**
     * Toggle update active status
     */
    public function toggle_update($id)
    {
        header('Content-Type: application/json');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya untuk server pusat']);
            return;
        }

        if (!$this->db->table_exists('system_updates')) {
            echo json_encode(['status' => 'error', 'message' => 'Tabel tidak ditemukan']);
            return;
        }

        $update = $this->db->where('id', $id)->get('system_updates')->row();
        if (!$update) {
            echo json_encode(['status' => 'error', 'message' => 'Update tidak ditemukan']);
            return;
        }

        $new_status = $update->is_active ? 0 : 1;
        $this->db->where('id', $id)->update('system_updates', [
            'is_active' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Status update berhasil diubah',
            'is_active' => $new_status
        ]);
    }

    /**
     * Delete update package
     */
    public function delete_update($id)
    {
        header('Content-Type: application/json');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya untuk server pusat']);
            return;
        }

        if (!$this->db->table_exists('system_updates')) {
            echo json_encode(['status' => 'error', 'message' => 'Tabel tidak ditemukan']);
            return;
        }

        $update = $this->db->where('id', $id)->get('system_updates')->row();
        if (!$update) {
            echo json_encode(['status' => 'error', 'message' => 'Update tidak ditemukan']);
            return;
        }

        // Delete file if exists
        if (!empty($update->file_path) && file_exists($update->file_path)) {
            @unlink($update->file_path);
        }

        // Delete from database
        $this->db->where('id', $id)->delete('system_updates');

        echo json_encode([
            'status' => 'success',
            'message' => 'Update v' . $update->version . ' berhasil dihapus'
        ]);
    }

    /**
     * Get update download statistics
     */
    public function update_stats($id = null)
    {
        header('Content-Type: application/json');

        if (!$this->Sync_model->is_pusat_server()) {
            echo json_encode(['status' => 'error', 'message' => 'Hanya untuk server pusat']);
            return;
        }

        if (!$this->db->table_exists('system_update_log')) {
            echo json_encode(['status' => 'success', 'data' => []]);
            return;
        }

        $this->db->select('system_update_log.*, sync_schools.nama_sekolah');
        $this->db->join('sync_schools', 'sync_schools.school_uuid = system_update_log.school_uuid', 'left');

        if ($id) {
            $this->db->where('update_id', $id);
        }

        $this->db->order_by('system_update_log.created_at', 'DESC');
        $this->db->limit(100);
        $logs = $this->db->get('system_update_log')->result();

        echo json_encode(['status' => 'success', 'data' => $logs]);
    }

    /**
     * Get list of media files to upload (for frontend progress)
     */
    public function get_pending_media()
    {
        $pending_data = $this->Sync_model->get_pending_upload();
        $files = $this->Sync_model->get_media_files_list($pending_data);
        echo json_encode(['status' => 'success', 'files' => $files, 'count' => count($files)]);
    }

    /**
     * Upload single media file
     */
    public function upload_single_media()
    {
        $path = $this->input->post('path');
        if (empty($path)) {
            echo json_encode(['status' => 'error', 'message' => 'Path empty']);
            return;
        }

        $full_path = FCPATH . $path;
        if (!file_exists($full_path)) {
            echo json_encode(['status' => 'error', 'message' => 'File not found']);
            return;
        }

        $result = $this->Sync_model->upload_file_to_pusat($full_path, $path);

        // Decode response from pusat
        $res = json_decode($result, true);
        echo json_encode(['status' => ($res['status'] ?? false) ? 'success' : 'error']);
    }

    /**
     * Get data comparison between local and pusat server
     */
    public function get_comparison()
    {
        header('Content-Type: application/json');

        try {
            // Get local counts
            $sync_tables = $this->Sync_model->get_sync_tables();
            $local_counts = [];

            foreach ($sync_tables as $table => $config) {
                if ($this->db->table_exists($table)) {
                    $local_counts[$table] = $this->db->count_all($table);
                } else {
                    $local_counts[$table] = 0;
                }
            }

            // Get pusat counts
            $result = $this->_send_request('/api_sync/get_counts', [
                'tables' => array_keys($sync_tables),
                'check_media' => true
            ]);

            $pusat_counts = [];
            if ($result['status'] === 'success' && isset($result['data'])) {
                $pusat_counts = $result['data'];
            }

            // Combine results
            $comparison = [];
            foreach ($sync_tables as $table => $config) {
                $comparison[$table] = [
                    'local' => $local_counts[$table] ?? 0,
                    'pusat' => $pusat_counts[$table] ?? 0,
                    'direction' => $config['direction']
                ];
            }

            // Media Comparison
            $local_media_count = 0;
            $media_path = FCPATH . 'uploads/bank_soal/';
            if (is_dir($media_path)) {
                $files = glob($media_path . '*.*');
                if ($files)
                    $local_media_count = count($files);
            }

            $comparison['media_files'] = [
                'local' => $local_media_count,
                'pusat' => $pusat_counts['media_files'] ?? 0,
                'direction' => 'upload'
            ];

            echo json_encode([
                'status' => 'success',
                'data' => $comparison
            ]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    /**
     * Get sync logs for ajax refresh
     */
    public function get_logs()
    {
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 20;
        $logs = $this->Sync_model->get_sync_log($limit);

        // Decode error messages
        foreach ($logs as &$log) {
            if (!empty($log->error_message)) {
                $decoded = json_decode($log->error_message, true);
                $log->error_message = ($decoded) ? $decoded : $log->error_message;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $logs]);
    }
    public function get_media_list_pusat()
    {
        $result = $this->_send_request('/api_sync/get_media_list', []);
        echo json_encode($result);
    }

    /**
     * Download single media file with MD5 checksum validation
     */
    public function download_media_file()
    {
        header('Content-Type: application/json');

        $filename = $this->input->post('filename');
        $expected_md5 = $this->input->post('md5'); // MD5 from server's file list
        $url = $this->input->post('url'); // Direct URL (fallback)
        $max_retries = 3;

        if (!$filename) {
            echo json_encode(['status' => 'error', 'message' => 'filename diperlukan']);
            return;
        }

        // Security: prevent directory traversal
        if (preg_match('/\.\./', $filename) || preg_match('/[\/\\\\]/', $filename)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid filename (security)']);
            return;
        }

        $save_path = FCPATH . 'uploads/bank_soal/' . $filename;

        // Ensure directory exists
        if (!is_dir(dirname($save_path))) {
            @mkdir(dirname($save_path), 0777, true);
        }

        $config = $this->Sync_model->get_config();

        // Method 1: Use API endpoint with proper authentication (preferred)
        if ($config && !empty($config->pusat_url) && !empty($config->api_key)) {
            $result = $this->_download_media_via_api($filename, $save_path, $expected_md5, $config, $max_retries);
        }
        // Method 2: Direct URL download (fallback for backwards compatibility)
        elseif ($url) {
            $result = $this->_download_media_direct($url, $save_path, $expected_md5, $max_retries);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No download method available']);
            return;
        }

        echo json_encode($result);
    }

    /**
     * Download media file via API with MD5 validation
     */
    private function _download_media_via_api($filename, $save_path, $expected_md5, $config, $max_retries = 3)
    {
        $url = rtrim($config->pusat_url, '/') . '/api_sync/download_media_file';
        $body = json_encode(['file_name' => $filename]);

        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $config->api_key
        ];

        // Add HMAC signature
        if (!empty($config->api_secret)) {
            $signature = hash_hmac('sha256', $body, $config->api_secret);
            $headers[] = 'X-Api-Signature: ' . $signature;
        }

        $retry = 0;
        $last_error = '';
        $response_headers = [];

        while ($retry < $max_retries) {
            $fp = fopen($save_path, 'wb');
            if (!$fp) {
                return ['status' => 'error', 'message' => 'Cannot write to file'];
            }

            $response_headers = [];

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FILE => $fp,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$response_headers) {
                    $len = strlen($header);
                    $parts = explode(':', $header, 2);
                    if (count($parts) == 2) {
                        $response_headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                    }
                    return $len;
                }
            ]);

            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            fclose($fp);

            if ($error || $http_code !== 200) {
                @unlink($save_path);
                $last_error = $error ?: "HTTP $http_code";
                $retry++;
                usleep(500000); // Wait 500ms before retry
                continue;
            }

            // Get expected MD5 from response header or use provided one
            $server_md5 = isset($response_headers['x-file-md5'])
                ? $response_headers['x-file-md5']
                : $expected_md5;

            // Validate MD5 checksum
            if ($server_md5) {
                $local_md5 = md5_file($save_path);

                if ($local_md5 !== $server_md5) {
                    @unlink($save_path);
                    $last_error = "Checksum mismatch: expected $server_md5, got $local_md5";
                    $retry++;
                    usleep(500000);
                    continue;
                }
            }

            // Success!
            return [
                'status' => 'success',
                'filename' => $filename,
                'size' => filesize($save_path),
                'md5' => $server_md5 ?? md5_file($save_path),
                'verified' => !empty($server_md5)
            ];
        }

        return [
            'status' => 'error',
            'message' => "Failed after $max_retries retries: $last_error",
            'filename' => $filename
        ];
    }

    /**
     * Download media file directly from URL with MD5 validation
     */
    private function _download_media_direct($url, $save_path, $expected_md5, $max_retries = 3)
    {
        $retry = 0;
        $last_error = '';

        while ($retry < $max_retries) {
            $fp = fopen($save_path, 'wb');
            if (!$fp) {
                return ['status' => 'error', 'message' => 'Cannot write to file'];
            }

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_HEADER => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $success = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            fclose($fp);

            if (!$success || $http_code !== 200) {
                @unlink($save_path);
                $last_error = $error ?: "HTTP $http_code";
                $retry++;
                usleep(500000);
                continue;
            }

            // Validate MD5 if provided
            if ($expected_md5) {
                $local_md5 = md5_file($save_path);

                if ($local_md5 !== $expected_md5) {
                    @unlink($save_path);
                    $last_error = "Checksum mismatch: expected $expected_md5, got $local_md5";
                    $retry++;
                    usleep(500000);
                    continue;
                }
            }

            // Success!
            return [
                'status' => 'success',
                'filename' => basename($save_path),
                'size' => filesize($save_path),
                'md5' => $expected_md5 ?? md5_file($save_path),
                'verified' => !empty($expected_md5)
            ];
        }

        return [
            'status' => 'error',
            'message' => "Failed after $max_retries retries: $last_error",
            'filename' => basename($save_path)
        ];
    }

    public function get_local_media_list()
    {
        $path = FCPATH . 'uploads/bank_soal/';
        $files = [];
        if (is_dir($path)) {
            $scanned = scandir($path);
            foreach ($scanned as $file) {
                if ($file === '.' || $file === '..')
                    continue;
                $full = $path . $file;
                if (is_file($full)) {
                    $files[] = [
                        'name' => $file,
                        'size' => filesize($full)
                    ];
                }
            }
        }
        echo json_encode(['status' => 'success', 'data' => $files]);
    }

    // ==================== CONFLICT MANAGEMENT ====================

    /**
     * Conflicts page - View and resolve sync conflicts
     */
    public function conflicts()
    {
        $data = $this->get_common_data();
        $data['title'] = 'Konflik Sinkronisasi';
        $data['judul'] = 'Sinkronisasi';
        $data['subjudul'] = 'Resolusi Konflik';

        // Get pending conflicts
        $data['conflicts'] = $this->Sync_model->get_pending_conflicts();
        $data['conflict_stats'] = $this->Sync_model->get_conflict_stats();

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('admin/sync/conflicts', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    /**
     * AJAX: Get conflict list
     */
    public function get_conflicts()
    {
        header('Content-Type: application/json');

        $table = $this->input->get('table');
        $limit = $this->input->get('limit') ?: 50;

        $conflicts = $this->Sync_model->get_pending_conflicts($table, $limit);
        $stats = $this->Sync_model->get_conflict_stats();

        echo json_encode([
            'status' => 'success',
            'data' => $conflicts,
            'stats' => $stats
        ]);
    }

    /**
     * AJAX: Resolve a specific conflict
     */
    public function resolve_conflict()
    {
        header('Content-Type: application/json');

        $conflict_id = $this->input->post('conflict_id');
        $choice = $this->input->post('choice'); // 'local' or 'remote'

        if (!$conflict_id || !in_array($choice, ['local', 'remote'])) {
            echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']);
            return;
        }

        $result = $this->Sync_model->resolve_conflict_by_id($conflict_id, $choice);

        if ($result['success']) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Konflik berhasil diselesaikan dengan memilih data ' . ($choice === 'local' ? 'Lokal' : 'Server')
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => $result['message']
            ]);
        }
    }

    /**
     * AJAX: Get conflict statistics
     */
    public function get_conflict_stats()
    {
        header('Content-Type: application/json');

        $stats = $this->Sync_model->get_conflict_stats();
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }

    /**
     * AJAX: Resolve all conflicts with a strategy
     */
    public function resolve_all_conflicts()
    {
        header('Content-Type: application/json');

        $strategy = $this->input->post('strategy'); // 'server_wins', 'local_wins', 'newest_wins'

        if (!in_array($strategy, ['server_wins', 'local_wins', 'newest_wins'])) {
            echo json_encode(['status' => 'error', 'message' => 'Strategi tidak valid']);
            return;
        }

        $conflicts = $this->Sync_model->get_pending_conflicts(null, 500);
        $resolved = 0;
        $failed = 0;

        foreach ($conflicts as $conflict_row) {
            $local_data = json_decode($conflict_row->local_data, true);
            $remote_data = json_decode($conflict_row->remote_data, true);

            $conflict = [
                'table' => $conflict_row->table_name,
                'sync_uuid' => $conflict_row->sync_uuid,
                'local_id' => $conflict_row->local_id,
                'local_record' => $local_data,
                'remote_record' => $remote_data,
                'local_updated' => $conflict_row->local_updated,
                'remote_updated' => $conflict_row->remote_updated
            ];

            $result = $this->Sync_model->resolve_conflict($conflict, $strategy);

            if ($result['resolved']) {
                $this->db->where('id', $conflict_row->id)->update('sync_conflicts', [
                    'status' => 'resolved',
                    'resolution_strategy' => $strategy,
                    'winner' => $result['winner'],
                    'resolved_at' => date('Y-m-d H:i:s')
                ]);
                $resolved++;
            } else {
                $failed++;
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => "{$resolved} konflik diselesaikan" . ($failed > 0 ? ", {$failed} gagal" : ""),
            'resolved' => $resolved,
            'failed' => $failed
        ]);
    }

    // ==================== UPDATE MANAGEMENT ====================

    /**
     * Updates page - Show available updates and management UI
     */
    public function updates()
    {
        $data = $this->get_common_data();
        $data['title'] = 'Update Aplikasi';
        $data['judul'] = 'Sinkronisasi';
        $data['subjudul'] = 'Pembaruan Aplikasi';

        // Load version config
        $this->config->load('version', TRUE);
        $data['current_version'] = $this->config->item('app_version') ?: '1.0.0';
        $data['current_version_code'] = $this->config->item('app_version_code') ?: 10000;

        // Load Update Manager
        $this->load->library('Update_manager');
        $data['backups'] = $this->update_manager->get_backups();

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('admin/sync/updates', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    /**
     * AJAX: Check for updates
     */
    public function check_update()
    {
        header('Content-Type: application/json');

        try {
            $this->load->library('Update_manager');
            $result = $this->update_manager->check_for_updates();

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $result['data']
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Download and install update
     */
    public function install_update()
    {
        header('Content-Type: application/json');
        set_time_limit(0);

        try {
            $update_id = $this->input->post('update_id');
            $new_version = $this->input->post('new_version');

            if (!$update_id) {
                echo json_encode(['status' => 'error', 'message' => 'update_id diperlukan']);
                return;
            }

            $this->load->library('Update_manager');

            // Step 1: Download
            $download = $this->update_manager->download_update($update_id);
            if (!$download['success']) {
                echo json_encode([
                    'status' => 'error',
                    'step' => 'download',
                    'message' => $download['message']
                ]);
                return;
            }

            // Step 2: Install
            $install = $this->update_manager->install_update(
                $download['file_path'],
                $update_id,
                $new_version ?: $download['version']
            );

            if ($install['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Update berhasil diinstall ke versi ' . ($new_version ?: $download['version']),
                    'new_version' => $new_version ?: $download['version'],
                    'backup_file' => $install['backup_file']
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'step' => 'install',
                    'message' => $install['message'],
                    'rolled_back' => isset($install['rolled_back']) ? $install['rolled_back'] : false
                ]);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Get backup list
     */
    public function get_backups()
    {
        header('Content-Type: application/json');

        $this->load->library('Update_manager');
        $backups = $this->update_manager->get_backups();

        echo json_encode([
            'status' => 'success',
            'data' => $backups
        ]);
    }

    /**
     * AJAX: Rollback to specific backup
     */
    public function rollback_update()
    {
        header('Content-Type: application/json');

        try {
            $backup_file = $this->input->post('backup_file');

            if (!$backup_file || !file_exists($backup_file)) {
                echo json_encode(['status' => 'error', 'message' => 'File backup tidak ditemukan']);
                return;
            }

            $this->load->library('Update_manager');
            $result = $this->update_manager->rollback($backup_file);

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Rollback berhasil'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message']
                ]);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Create manual backup
     */
    public function create_backup()
    {
        header('Content-Type: application/json');

        try {
            $this->load->library('Update_manager');
            $result = $this->update_manager->create_backup();

            if ($result['success']) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Backup berhasil dibuat',
                    'backup_name' => $result['backup_name']
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message']
                ]);
            }

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

}
