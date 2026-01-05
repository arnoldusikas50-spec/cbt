<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Sync Controller
 * Controller untuk menangani request API dari server lokal
 * Digunakan HANYA di server pusat
 */
class Api_sync extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Load database with error handling
        if (!isset($this->db) || $this->db === null) {
            $this->load->database();
        }

        // Verify database is loaded
        if (!isset($this->db) || $this->db === null) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
            exit;
        }

        $this->load->model('Sync_model');

        // Set header JSON
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: X-Api-Key, Content-Type');

        // Handle preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    /**
     * Get input data (supports JSON body and POST fields)
     */
    private function _get_input($key = null, $default = null)
    {
        // Try JSON body first (if content-type is json)
        $content_type = $this->input->get_request_header('Content-Type');
        $raw_input = $this->input->raw_input_stream;

        if (stripos($content_type, 'application/json') !== false || ($raw_input && $raw_input[0] === '{')) {
            $json = json_decode($raw_input, true);
            if ($json !== null) {
                if ($key === null)
                    return $json;
                return isset($json[$key]) ? $json[$key] : $default;
            }
        }

        // Fallback to CI input
        $val = $this->input->post($key);
        return $val !== null ? $val : $default;
    }

    /**
     * Configuration: Enforce HMAC Signature
     * Set to TRUE for production, FALSE for development/debugging
     */
    private $enforce_hmac = false; // TODO: Set TRUE for production!

    /**
     * Verify API key and HMAC Signature
     */
    private function verify_request()
    {
        $headers = $this->input->request_headers();
        $api_key = isset($headers['X-Api-Key']) ? $headers['X-Api-Key'] :
            (isset($headers['x-api-key']) ? $headers['x-api-key'] : null);

        if (!$api_key) {
            $this->_response_error('API Key tidak ditemukan', 401);
        }

        $school = $this->Sync_model->get_school_by_api_key($api_key);
        if (!$school) {
            $this->_response_error('API Key tidak valid', 401);
        }

        if (!$school->is_active) {
            $this->_response_error('Sekolah dinonaktifkan', 403);
        }

        // HMAC Signature Verification
        $signature = isset($headers['X-Api-Signature']) ? $headers['X-Api-Signature'] :
            (isset($headers['x-api-signature']) ? $headers['x-api-signature'] : null);

        $raw_body = $this->input->raw_input_stream;

        // If signature is provided, always validate it
        if ($signature && $raw_body) {
            $expected_signature = hash_hmac('sha256', $raw_body, $school->api_secret);
            if (!hash_equals($expected_signature, $signature)) {
                $this->_log_security_event($school->id, 'HMAC_MISMATCH', [
                    'ip' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent()
                ]);
                $this->_response_error('Invalid Signature: Integritas data terganggu. Kemungkinan data dimodifikasi.', 403);
            }
        }
        // If enforcement is ON and no signature provided, reject
        elseif ($this->enforce_hmac && $raw_body) {
            $this->_response_error('HMAC Signature diperlukan. Pastikan client mengirim header X-Api-Signature.', 401);
        }

        return $school;
    }

    /**
     * Log security events for audit trail
     */
    private function _log_security_event($school_id, $event_type, $details = [])
    {
        // Check if activity_log table exists
        if (!$this->db->table_exists('activity_log')) {
            return; // Silently skip if table doesn't exist yet
        }

        $this->db->insert('activity_log', [
            'user_id' => $school_id,
            'user_type' => 'school',
            'action' => 'SECURITY_' . $event_type,
            'target_table' => 'api_sync',
            'target_id' => null,
            'old_value' => null,
            'new_value' => json_encode($details),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Send error response
     */
    private function _response_error($message, $code = 400)
    {
        // Ensure no output has been sent
        if (ob_get_length())
            ob_clean();

        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    /**
     * Send success response
     */
    private function _response_success($data = [], $message = 'Success')
    {
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // ==================== PUBLIC ENDPOINTS ====================

    /**
     * Ping - Check server status and validate API key
     * GET /api_sync/ping
     */
    public function ping()
    {
        try {
            $school = $this->verify_request();

            // Update last connection time
            $this->Sync_model->update_school_last_sync(
                $school->school_uuid,
                $this->input->ip_address()
            );

            $this->_response_success([
                'school' => $school->nama_sekolah,
                'npsn' => $school->npsn,
                'server_time' => date('Y-m-d H:i:s'),
                'timezone' => date_default_timezone_get()
            ], 'Server pusat online');

        } catch (Exception $e) {
            $this->_response_error('Server Error: ' . $e->getMessage(), 500);
        } catch (Throwable $e) { // PHP 7+
            $this->_response_error('Server Fatal Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get server info
     * GET /api_sync/info
     */
    public function info()
    {
        $school = $this->verify_request();

        $this->_response_success([
            'school_uuid' => $school->school_uuid,
            'nama_sekolah' => $school->nama_sekolah,
            'last_sync' => $school->last_sync,
            'server_version' => '1.0.0',
            'sync_tables' => array_keys($this->Sync_model->get_sync_tables())
        ]);
    }

    /**
     * Download data from pusat server
     * POST /api_sync/download
     * 
     * Body params:
     * - table: nama tabel yang akan di-download
     * - last_sync: timestamp sync terakhir (optional)
     * - filters: filter tambahan dalam format JSON (optional)
     */
    public function download()
    {
        $school = $this->verify_request();

        $table = $this->_get_input('table');
        $last_sync = $this->_get_input('last_sync');
        $filters = $this->_get_input('filters');
        $limit = $this->_get_input('limit');
        $offset = $this->_get_input('offset');

        if (empty($table)) {
            $this->_response_error('Parameter table diperlukan');
        }

        // Validate table is allowed
        $sync_tables = $this->Sync_model->get_sync_tables();
        if (!isset($sync_tables[$table])) {
            $this->_response_error('Tabel tidak diizinkan untuk sync: ' . $table);
        }

        // Check direction - table must be downloadable
        if ($sync_tables[$table]['direction'] === 'upload') {
            $this->_response_error('Tabel ini hanya untuk upload, bukan download');
        }

        // Parse filters if provided
        $filter_array = [];
        if ($filters) {
            $filter_array = json_decode($filters, true) ?: [];
        }

        // Get data
        $data = $this->Sync_model->get_data_for_download($table, $last_sync, $school->school_uuid, $filter_array, $limit, $offset);

        // Get total count (optional, adds overhead)
        //$total = $this->db->count_all_results($table); 

        // Log the sync
        $this->Sync_model->create_log([
            'school_uuid' => $school->school_uuid,
            'sync_type' => 'download',
            'sync_direction' => 'pusat_to_lokal',
            'table_name' => $table,
            'records_total' => count($data),
            'records_success' => count($data),
            'status' => 'success'
        ]);

        // Get IDs of deleted records for delta sync
        $deleted_ids = $this->Sync_model->get_deleted_records($table, $last_sync, $school->school_uuid);

        $this->_response_success([
            'table' => $table,
            'count' => count($data),
            'records' => $data,
            'deleted_ids' => $deleted_ids,
            'has_deleted' => !empty($deleted_ids)
        ], 'Data berhasil diambil');
    }

    /**
     * Download multiple tables at once
     * POST /api_sync/download_batch
     * 
     * Body params:
     * - tables: array nama tabel ["table1", "table2"]
     * - last_sync: timestamp sync terakhir (optional)
     */
    public function download_batch()
    {
        try {
            $school = $this->verify_request();

            $tables = $this->_get_input('tables');
            $last_sync = $this->_get_input('last_sync');

            if (empty($tables)) {
                $this->_response_error('Parameter tables diperlukan');
            }

            if (is_string($tables)) {
                $tables = json_decode($tables, true);
            }

            if (!is_array($tables)) {
                $this->_response_error('Parameter tables harus berupa array');
            }

            $sync_tables = $this->Sync_model->get_sync_tables();
            $result = [];
            $total_records = 0;

            foreach ($tables as $table) {
                if (!isset($sync_tables[$table])) {
                    continue;
                }

                if ($sync_tables[$table]['direction'] === 'upload') {
                    continue;
                }

                $data = $this->Sync_model->get_data_for_download($table, $last_sync, $school->school_uuid);
                $result[$table] = $data;
                $total_records += count($data);
            }

            // Log batch sync
            $this->Sync_model->create_log([
                'school_uuid' => $school->school_uuid,
                'sync_type' => 'download',
                'sync_direction' => 'pusat_to_lokal',
                'table_name' => implode(',', array_keys($result)),
                'records_total' => $total_records,
                'records_success' => $total_records,
                'status' => 'success'
            ]);

            $this->_response_success([
                'tables' => array_keys($result),
                'total_records' => $total_records,
                'data' => $result
            ], 'Data batch berhasil diambil');

        } catch (Exception $e) {
            $this->_response_error('Download Error: ' . $e->getMessage(), 500);
        } catch (Throwable $e) {
            $this->_response_error('Download Fatal Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Upload data from lokal to pusat server
     * POST /api_sync/upload
     * 
     * Body params:
     * - table: nama tabel
     * - records: data dalam format JSON array
     */
    public function upload()
    {
        $school = $this->verify_request();

        $table = $this->_get_input('table');
        $records = $this->_get_input('records');

        if (empty($table)) {
            $this->_response_error('Parameter table diperlukan');
        }

        if (empty($records)) {
            $this->_response_error('Parameter records diperlukan');
        }

        // Validate table
        $sync_tables = $this->Sync_model->get_sync_tables();
        if (!isset($sync_tables[$table])) {
            $this->_response_error('Tabel tidak diizinkan untuk sync: ' . $table);
        }

        // Check direction
        if ($sync_tables[$table]['direction'] === 'download') {
            $this->_response_error('Tabel ini hanya untuk download, bukan upload');
        }

        // Parse records
        if (is_string($records)) {
            $records = json_decode($records, true);
        }

        if (!is_array($records)) {
            $this->_response_error('Format records tidak valid');
        }

        // Add school_uuid to each record for tracking
        foreach ($records as &$record) {
            $record['sync_school_uuid'] = $school->school_uuid;
        }

        // Create log entry
        $log_id = $this->Sync_model->create_log([
            'school_uuid' => $school->school_uuid,
            'sync_type' => 'upload',
            'sync_direction' => 'lokal_to_pusat',
            'table_name' => $table,
            'records_total' => count($records),
            'status' => 'running'
        ]);

        // Import data
        $result = $this->Sync_model->import_data($table, $records, $school->school_uuid);

        // Update log
        $status = 'success';
        if ($result['failed'] > 0 && $result['success'] === 0) {
            $status = 'failed';
        } elseif ($result['failed'] > 0) {
            $status = 'partial';
        }

        $this->Sync_model->update_log($log_id, [
            'records_success' => $result['success'],
            'records_failed' => $result['failed'],
            'status' => $status,
            'message' => json_encode($result['errors'])
        ]);

        $this->_response_success([
            'table' => $table,
            'success' => $result['success'],
            'failed' => $result['failed']
        ], $result['failed'] > 0 ? 'Upload selesai dengan beberapa error' : 'Upload berhasil');
    }

    /**
     * Upload multiple tables at once
     * POST /api_sync/upload_batch
     * 
     * Body params:
     * - data: object dengan key = nama tabel, value = array records
     *   {"cbt_siswa": [...], "cbt_nilai": [...]}
     */
    public function upload_batch()
    {
        $school = $this->verify_request();

        $data = $this->_get_input('data');

        if (empty($data)) {
            $this->_response_error('Parameter data diperlukan');
        }

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!is_array($data)) {
            $this->_response_error('Format data tidak valid');
        }

        $sync_tables = $this->Sync_model->get_sync_tables();
        $results = [];
        $total_success = 0;
        $total_failed = 0;

        foreach ($data as $table => $records) {
            if (!isset($sync_tables[$table])) {
                continue;
            }

            if ($sync_tables[$table]['direction'] === 'download') {
                continue;
            }

            // Add school_uuid
            foreach ($records as &$record) {
                $record['sync_school_uuid'] = $school->school_uuid;
            }

            $result = $this->Sync_model->import_data($table, $records, $school->school_uuid);
            $results[$table] = $result;
            $total_success += $result['success'];
            $total_failed += $result['failed'];
        }

        // Log batch sync
        $status = 'success';
        if ($total_failed > 0 && $total_success === 0) {
            $status = 'failed';
        } elseif ($total_failed > 0) {
            $status = 'partial';
        }

        $this->Sync_model->create_log([
            'school_uuid' => $school->school_uuid,
            'sync_type' => 'upload',
            'sync_direction' => 'lokal_to_pusat',
            'table_name' => implode(',', array_keys($results)),
            'records_total' => $total_success + $total_failed,
            'records_success' => $total_success,
            'records_failed' => $total_failed,
            'status' => $status
        ]);

        $this->_response_success([
            'tables' => array_keys($results),
            'total_success' => $total_success,
            'total_failed' => $total_failed,
            'details' => $results
        ], 'Batch upload selesai');
    }

    /**
     * Full sync - download then upload
     * POST /api_sync/full_sync
     */
    public function full_sync()
    {
        $school = $this->verify_request();

        $last_sync = $this->_get_input('last_sync');
        $upload_data = $this->_get_input('upload_data');

        $sync_tables = $this->Sync_model->get_sync_tables();

        // Prepare download data
        $download_result = [];
        foreach ($sync_tables as $table => $config) {
            if ($config['direction'] !== 'upload') {
                $data = $this->Sync_model->get_data_for_download($table, $last_sync, $school->school_uuid);
                if (!empty($data)) {
                    $download_result[$table] = $data;
                }
            }
        }

        // Process upload data
        $upload_result = [];
        if ($upload_data) {
            if (is_string($upload_data)) {
                $upload_data = json_decode($upload_data, true);
            }

            foreach ($upload_data as $table => $records) {
                if (!isset($sync_tables[$table]))
                    continue;
                if ($sync_tables[$table]['direction'] === 'download')
                    continue;

                foreach ($records as &$record) {
                    $record['sync_school_uuid'] = $school->school_uuid;
                }

                $upload_result[$table] = $this->Sync_model->import_data($table, $records, $school->school_uuid);
            }
        }

        // Log full sync
        $this->Sync_model->create_log([
            'school_uuid' => $school->school_uuid,
            'sync_type' => 'full',
            'sync_direction' => 'pusat_to_lokal',
            'table_name' => 'full_sync',
            'records_total' => array_sum(array_map('count', $download_result)),
            'status' => 'success'
        ]);

        $this->_response_success([
            'download' => [
                'tables' => array_keys($download_result),
                'data' => $download_result
            ],
            'upload' => $upload_result,
            'sync_timestamp' => date('Y-m-d H:i:s')
        ], 'Full sync berhasil');
    }

    /**
     * Get sync history for a school
     * GET /api_sync/history
     */
    public function history()
    {
        $school = $this->verify_request();

        $limit = $this->input->get('limit') ?: 50;

        $logs = $this->db->where('school_uuid', $school->school_uuid)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('sync_log')
            ->result();

        $this->_response_success([
            'logs' => $logs
        ]);
    }

    /**
     * Register new school (called once during initial setup)
     * POST /api_sync/register
     * 
     * Note: This should be protected or called internally
     */
    public function register()
    {
        // This endpoint should be protected by admin authentication
        // For now, we'll use a master key
        $master_key = $this->input->post('master_key');
        $expected_key = $this->config->item('sync_master_key') ?: 'AKM_MASTER_KEY_CHANGE_ME';

        if ($master_key !== $expected_key) {
            $this->_response_error('Unauthorized', 401);
        }

        $data = [
            'npsn' => $this->input->post('npsn'),
            'nama_sekolah' => $this->input->post('nama_sekolah'),
            'alamat' => $this->input->post('alamat'),
            'kota' => $this->input->post('kota'),
            'provinsi' => $this->input->post('provinsi')
        ];

        if (empty($data['nama_sekolah'])) {
            $this->_response_error('Nama sekolah diperlukan');
        }

        $result = $this->Sync_model->register_school($data);

        if ($result) {
            $school = $this->db->where('id', $result)->get('sync_schools')->row();

            $this->_response_success([
                'school_uuid' => $school->school_uuid,
                'api_key' => $school->api_key,
                'api_secret' => $school->api_secret
            ], 'Sekolah berhasil didaftarkan. Simpan API Key dan Secret dengan aman!');
        }

        $this->_response_error('Gagal mendaftarkan sekolah');
    }
    /**
     * Upload media file (images/audio)
     * POST /api_sync/upload_media
     */
    public function upload_media()
    {
        $school = $this->verify_request();

        $path = $this->input->post('rel_path');

        if (empty($path)) {
            $this->_response_error('Relative path diperlukan');
        }

        // Security Validation
        if (strpos($path, '..') !== false) {
            $this->_response_error('Invalid path security');
        }

        // Normalize slashes
        $path = str_replace('\\', '/', $path);

        // Remove 'uploads/' prefix if present
        $clean_path = preg_replace('/^uploads\//', '', $path);

        // Final destination: FCPATH/uploads/clean_path
        $target_root = FCPATH . 'uploads/';
        $target_file = $target_root . $clean_path;
        $target_dir = dirname($target_file);

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        if (!isset($_FILES['file'])) {
            $this->_response_error('File tidak ditemukan');
        }

        $config['upload_path'] = $target_dir;
        $config['allowed_types'] = 'gif|jpg|jpeg|png|mp3|mp4|doc|docx|pdf|xls|xlsx|ppt|pptx';
        $config['file_name'] = basename($target_file);
        $config['overwrite'] = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            $this->_response_error($this->upload->display_errors('', ''));
        }

        $this->_response_success(['path' => $clean_path], 'File berhasil diupload');
    }

    /**
     * Get record counts for comparison
     */
    public function get_counts()
    {
        try {
            $school = $this->verify_request();
            $tables = $this->_get_input('tables');

            if (empty($tables)) {
                $this->_response_error('Parameter tables diperlukan');
            }

            if (is_string($tables)) {
                $tables = json_decode($tables, true);
            }

            $sync_tables = $this->Sync_model->get_sync_tables();
            $counts = [];

            foreach ($tables as $table) {
                if (!isset($sync_tables[$table])) {
                    $counts[$table] = 0;
                    continue;
                }

                if (!$this->db->table_exists($table)) {
                    $counts[$table] = 0;
                    continue;
                }

                // Count records for this school OR global records (NULL)
                $this->db->from($table);

                if ($this->db->field_exists('sync_school_uuid', $table)) {
                    $this->db->group_start();
                    $this->db->where('sync_school_uuid', $school->school_uuid);
                    $this->db->or_where('sync_school_uuid', NULL);
                    $this->db->or_where('sync_school_uuid', '');
                    $this->db->group_end();
                } elseif ($this->db->field_exists('school_uuid', $table)) {
                    $this->db->group_start();
                    $this->db->where('school_uuid', $school->school_uuid);
                    $this->db->or_where('school_uuid', NULL);
                    $this->db->or_where('school_uuid', '');
                    $this->db->group_end();
                } elseif ($this->db->field_exists('server_id', $table)) {
                    $this->db->group_start();
                    $this->db->where('server_id', $school->school_uuid);
                    $this->db->or_where('server_id', NULL);
                    $this->db->or_where('server_id', '');
                    $this->db->group_end();
                }

                $counts[$table] = $this->db->count_all_results();
            }

            // Count Media Files if requested
            if ($this->_get_input('check_media')) {
                $media_path = FCPATH . 'uploads/bank_soal/';
                $media_count = 0;
                if (is_dir($media_path)) {
                    $files = glob($media_path . '*.*'); // Check files only
                    if ($files) {
                        $media_count = count($files);
                    }
                }
                $counts['media_files'] = $media_count;
            }

            $this->_response_success($counts);

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        } catch (Throwable $e) {
            $this->_response_error($e->getMessage());
        }
    }
    public function get_media_list()
    {
        try {
            $this->verify_request();
            $media_path = FCPATH . 'uploads/bank_soal/';
            $file_list = [];

            // Optional: filter files modified after a certain date
            $since = $this->_get_input('since'); // timestamp

            if (is_dir($media_path)) {
                $files = scandir($media_path);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $full_path = $media_path . $file;
                        if (is_file($full_path)) {
                            $mtime = filemtime($full_path);

                            // If since filter provided, skip older files
                            if ($since && $mtime <= $since) {
                                continue;
                            }

                            $file_list[] = [
                                'name' => $file,
                                'size' => filesize($full_path),
                                'md5' => md5_file($full_path),
                                'modified' => $mtime,
                                'url' => base_url('uploads/bank_soal/' . $file)
                            ];
                        }
                    }
                }
            }

            $this->_response_success([
                'files' => $file_list,
                'total' => count($file_list),
                'path' => 'uploads/bank_soal/'
            ]);

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        }
    }

    /**
     * Download a single media file with checksum header
     * POST /api_sync/download_media_file
     * 
     * Body params:
     * - file_name: Name of the file to download
     */
    public function download_media_file()
    {
        try {
            $school = $this->verify_request();

            $file_name = $this->_get_input('file_name');
            if (!$file_name) {
                $this->_response_error('file_name diperlukan');
            }

            // Security: prevent directory traversal
            $file_name = basename($file_name);

            $file_path = FCPATH . 'uploads/bank_soal/' . $file_name;

            if (!file_exists($file_path)) {
                $this->_response_error('File tidak ditemukan: ' . $file_name, 404);
            }

            // Calculate MD5 hash
            $md5_hash = md5_file($file_path);
            $file_size = filesize($file_path);
            $mime_type = mime_content_type($file_path) ?: 'application/octet-stream';

            // Set headers with checksum info
            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . $file_size);
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('X-File-MD5: ' . $md5_hash);
            header('X-File-Size: ' . $file_size);
            header('X-File-Name: ' . $file_name);

            // Stream the file
            readfile($file_path);
            exit;

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        }
    }

    /**
     * Verify file integrity (check if client has correct file)
     * POST /api_sync/verify_media_file
     */
    public function verify_media_file()
    {
        try {
            $this->verify_request();

            $file_name = $this->_get_input('file_name');
            $client_md5 = $this->_get_input('md5');

            if (!$file_name) {
                $this->_response_error('file_name diperlukan');
            }

            $file_name = basename($file_name);
            $file_path = FCPATH . 'uploads/bank_soal/' . $file_name;

            if (!file_exists($file_path)) {
                $this->_response_success([
                    'exists' => false,
                    'file_name' => $file_name
                ]);
                return;
            }

            $server_md5 = md5_file($file_path);
            $is_valid = ($client_md5 && $client_md5 === $server_md5);

            $this->_response_success([
                'exists' => true,
                'file_name' => $file_name,
                'server_md5' => $server_md5,
                'client_md5' => $client_md5,
                'is_valid' => $is_valid,
                'size' => filesize($file_path)
            ]);

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        }
    }

    // ==================== AUTO-UPDATE ENDPOINTS ====================

    /**
     * Check for available updates
     * POST /api_sync/check_update
     * 
     * Body params:
     * - current_version: Client's current version string (e.g., "2.0.0")
     * - current_version_code: Client's numeric version code (e.g., 20000)
     */
    public function check_update()
    {
        try {
            $school = $this->verify_request();

            $current_version = $this->_get_input('current_version');
            $current_version_code = (int) $this->_get_input('current_version_code', 0);

            if (!$current_version_code) {
                $this->_response_error('current_version_code diperlukan');
            }

            // Check if system_updates table exists
            if (!$this->db->table_exists('system_updates')) {
                $this->_response_success([
                    'update_available' => false,
                    'message' => 'Sistem update belum dikonfigurasi'
                ]);
                return;
            }

            // Get latest active update that is newer than client version
            $this->db->where('is_active', 1);
            $this->db->where('version_code >', $current_version_code);
            $this->db->order_by('version_code', 'DESC');
            $this->db->limit(1);
            $latest = $this->db->get('system_updates')->row();

            if (!$latest) {
                $this->_response_success([
                    'update_available' => false,
                    'current_version' => $current_version,
                    'message' => 'Aplikasi sudah versi terbaru'
                ]);
                return;
            }

            // Check minimum version requirement
            if ($latest->min_version) {
                $min_code = $this->_version_to_code($latest->min_version);
                if ($current_version_code < $min_code) {
                    $this->_response_success([
                        'update_available' => true,
                        'can_update' => false,
                        'message' => 'Update memerlukan minimal versi ' . $latest->min_version . '. Silakan update bertahap.',
                        'latest_version' => $latest->version
                    ]);
                    return;
                }
            }

            $this->_response_success([
                'update_available' => true,
                'can_update' => true,
                'latest_version' => $latest->version,
                'latest_version_code' => $latest->version_code,
                'title' => $latest->title,
                'description' => $latest->description,
                'file_size' => $latest->file_size,
                'file_checksum' => $latest->file_checksum,
                'is_critical' => (bool) $latest->is_critical,
                'update_id' => $latest->id,
                'current_version' => $current_version
            ]);

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        }
    }

    /**
     * Get detailed info about a specific update
     * POST /api_sync/get_update_info
     */
    public function get_update_info()
    {
        try {
            $school = $this->verify_request();

            $update_id = $this->_get_input('update_id');
            $version = $this->_get_input('version');

            if (!$update_id && !$version) {
                $this->_response_error('update_id atau version diperlukan');
            }

            if (!$this->db->table_exists('system_updates')) {
                $this->_response_error('Sistem update belum dikonfigurasi');
            }

            if ($update_id) {
                $this->db->where('id', $update_id);
            } else {
                $this->db->where('version', $version);
            }
            $this->db->where('is_active', 1);
            $update = $this->db->get('system_updates')->row();

            if (!$update) {
                $this->_response_error('Update tidak ditemukan', 404);
            }

            $this->_response_success([
                'id' => $update->id,
                'version' => $update->version,
                'version_code' => $update->version_code,
                'title' => $update->title,
                'description' => $update->description,
                'file_name' => $update->file_name,
                'file_size' => $update->file_size,
                'file_checksum' => $update->file_checksum,
                'min_version' => $update->min_version,
                'is_critical' => (bool) $update->is_critical,
                'created_at' => $update->created_at
            ]);

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        }
    }

    /**
     * Download update package file (streams the ZIP)
     * POST /api_sync/download_update
     */
    public function download_update()
    {
        try {
            $school = $this->verify_request();

            $update_id = $this->_get_input('update_id');
            if (!$update_id) {
                $this->_response_error('update_id diperlukan');
            }

            if (!$this->db->table_exists('system_updates')) {
                $this->_response_error('Sistem update belum dikonfigurasi');
            }

            $this->db->where('id', $update_id);
            $this->db->where('is_active', 1);
            $update = $this->db->get('system_updates')->row();

            if (!$update) {
                $this->_response_error('Update tidak ditemukan', 404);
            }

            $file_path = $update->file_path;
            if (!file_exists($file_path)) {
                $this->_response_error('File update tidak ditemukan di server', 404);
            }

            // Increment download count
            $this->db->where('id', $update_id);
            $this->db->set('download_count', 'download_count + 1', FALSE);
            $this->db->update('system_updates');

            // Log the download
            if ($this->db->table_exists('system_update_log')) {
                $this->db->insert('system_update_log', [
                    'school_uuid' => $school->school_uuid,
                    'update_id' => $update_id,
                    'to_version' => $update->version,
                    'status' => 'downloaded',
                    'ip_address' => $this->input->ip_address(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Stream the file
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $update->file_name . '"');
            header('Content-Length: ' . filesize($file_path));
            header('X-Update-Version: ' . $update->version);
            header('X-Update-Checksum: ' . $update->file_checksum);

            readfile($file_path);
            exit;

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        }
    }

    /**
     * Report update installation status
     * POST /api_sync/report_update_status
     */
    public function report_update_status()
    {
        try {
            $school = $this->verify_request();

            $update_id = $this->_get_input('update_id');
            $from_version = $this->_get_input('from_version');
            $to_version = $this->_get_input('to_version');
            $status = $this->_get_input('status'); // installed, failed, rolled_back
            $error_message = $this->_get_input('error_message');

            if (!$update_id || !$status) {
                $this->_response_error('update_id dan status diperlukan');
            }

            if (!in_array($status, ['installed', 'failed', 'rolled_back'])) {
                $this->_response_error('Status tidak valid');
            }

            if ($this->db->table_exists('system_update_log')) {
                $this->db->insert('system_update_log', [
                    'school_uuid' => $school->school_uuid,
                    'update_id' => $update_id,
                    'from_version' => $from_version,
                    'to_version' => $to_version,
                    'status' => $status,
                    'error_message' => $error_message,
                    'ip_address' => $this->input->ip_address(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->_response_success(['logged' => true]);

        } catch (Exception $e) {
            $this->_response_error($e->getMessage());
        }
    }

    /**
     * Helper: Convert version string to numeric code
     */
    private function _version_to_code($version)
    {
        $parts = explode('.', $version);
        $major = isset($parts[0]) ? (int) $parts[0] : 0;
        $minor = isset($parts[1]) ? (int) $parts[1] : 0;
        $patch = isset($parts[2]) ? (int) $parts[2] : 0;

        return ($major * 10000) + ($minor * 100) + $patch;
    }
}
