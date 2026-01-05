<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sync Model
 * Model untuk menangani semua operasi sinkronisasi data
 * antara server pusat dan server lokal
 */
class Sync_model extends CI_Model
{

    // Daftar tabel yang perlu disinkronkan dengan prioritas
    public $sync_tables = [
        // Master data (download from pusat)
        'ref_jurusan' => ['pk' => 'id_jurusan', 'direction' => 'download', 'priority' => 1],
        'master_mapel' => ['pk' => 'id_mapel', 'direction' => 'download', 'priority' => 1],
        'cbt_jenis' => ['pk' => 'id_jenis', 'direction' => 'download', 'priority' => 1],
        'cbt_sesi' => ['pk' => 'id_sesi', 'direction' => 'download', 'priority' => 1],
        'cbt_ruang' => ['pk' => 'id_ruang', 'direction' => 'download', 'priority' => 1],

        // User data (bidirectional)
        'master_guru' => ['pk' => 'id_guru', 'direction' => 'both', 'priority' => 2],
        'master_kelas' => ['pk' => 'id_kelas', 'direction' => 'both', 'priority' => 2],
        'master_siswa' => ['pk' => 'id_siswa', 'direction' => 'both', 'priority' => 2],

        // CBT data (bidirectional)
        'cbt_bank_soal' => ['pk' => 'id_bank', 'direction' => 'both', 'priority' => 3],
        'cbt_soal' => ['pk' => 'id_soal', 'direction' => 'both', 'priority' => 3],
        'cbt_jadwal' => ['pk' => 'id_jadwal', 'direction' => 'both', 'priority' => 4],

        // Exam results (upload to pusat)
        'cbt_siswa' => ['pk' => 'id', 'direction' => 'upload', 'priority' => 5],
        'cbt_durasi' => ['pk' => 'id_durasi', 'direction' => 'upload', 'priority' => 5],
        'cbt_nilai' => ['pk' => 'id_nilai', 'direction' => 'upload', 'priority' => 5],
        'cbt_log' => ['pk' => 'id_log', 'direction' => 'upload', 'priority' => 6],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // ==================== CONFIG METHODS ====================

    /**
     * Get sync configuration
     */
    public function get_config()
    {
        if (!$this->db->table_exists('sync_config')) {
            return null;
        }
        return $this->db->get('sync_config')->row();
    }

    /**
     * Save sync configuration
     */
    public function save_config($data)
    {
        $config = $this->get_config();

        if ($config) {
            return $this->db->where('id', $config->id)->update('sync_config', $data);
        } else {
            // Generate server_id jika belum ada
            if (empty($data['server_id'])) {
                $data['server_id'] = $this->generate_uuid();
            }
            return $this->db->insert('sync_config', $data);
        }
    }

    /**
     * Reset sync timestamp to force full sync
     */
    public function reset_sync_time()
    {
        $config = $this->get_config();
        if ($config) {
            return $this->db->where('id', $config->id)->update('sync_config', [
                'last_sync_download' => null,
                'last_sync_upload' => null
            ]);
        }
        return false;
    }

    /**
     * Check if this server is pusat or lokal
     */
    public function is_pusat_server()
    {
        $config = $this->get_config();
        return ($config && $config->server_type === 'pusat');
    }

    // ==================== API KEY VALIDATION (SERVER PUSAT) ====================

    /**
     * Validate API key dari server lokal
     */
    public function validate_api_key($api_key)
    {
        $this->db->where([
            'api_key' => $api_key,
            'is_active' => 1
        ]);
        $school = $this->db->get('sync_schools')->row();

        return $school ? true : false;
    }

    /**
     * Get school by API key
     */
    public function get_school_by_api_key($api_key)
    {
        $this->db->where([
            'api_key' => $api_key,
            'is_active' => 1
        ]);
        return $this->db->get('sync_schools')->row();
    }

    /**
     * Register new school (server pusat)
     */
    public function register_school($data)
    {
        $data['school_uuid'] = $this->generate_uuid();
        $data['api_key'] = $this->generate_api_key();
        $data['api_secret'] = $this->generate_api_secret();
        $data['created_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('sync_schools', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    /**
     * Get all registered schools
     */
    public function get_all_schools()
    {
        $this->db->select('s.*, (SELECT COUNT(id) FROM users WHERE company = s.school_uuid) as admin_count');
        return $this->db->order_by('nama_sekolah', 'ASC')
            ->get('sync_schools s')
            ->result();
    }

    /**
     * Get admin user by school ID
     */
    public function get_admin_by_school_id($school_id)
    {
        $this->db->where('id', $school_id);
        $school = $this->db->get('sync_schools')->row();
        if (!$school)
            return null;

        // Get user associated with school_uuid
        return $this->db->where('company', $school->school_uuid)->get('users')->row();
    }

    /**
     * Count registered schools
     */
    public function count_schools($active_only = true)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->count_all_results('sync_schools');
    }

    /**
     * Get school by ID
     */
    public function get_school_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('sync_schools')->row();
    }

    /**
     * Update school last sync
     */
    public function update_school_last_sync($school_uuid, $ip = null)
    {
        $data = ['last_sync' => date('Y-m-d H:i:s')];
        if ($ip) {
            $data['ip_address'] = $ip;
        }
        return $this->db->where('school_uuid', $school_uuid)
            ->update('sync_schools', $data);
    }

    // ==================== SYNC OPERATIONS ====================

    /**
     * Get data untuk download ke server lokal
     */
    /**
     * Get data untuk download ke server lokal
     */
    public function get_data_for_download($table, $last_sync = null, $school_uuid = null, $filters = [], $limit = null, $offset = null)
    {
        if (!isset($this->sync_tables[$table])) {
            return [];
        }

        // Check if table exists
        if (!$this->db->table_exists($table)) {
            return [];
        }

        $this->db->from($table);

        // Filter by School UUID for Data Isolation
        if ($school_uuid) {
            if ($this->db->field_exists('sync_school_uuid', $table)) {
                $this->db->group_start();
                $this->db->where('sync_school_uuid', $school_uuid);
                $this->db->or_where('sync_school_uuid', NULL);
                $this->db->or_where('sync_school_uuid', '');
                $this->db->group_end();
            } elseif ($this->db->field_exists('school_uuid', $table)) {
                $this->db->group_start();
                $this->db->where('school_uuid', $school_uuid);
                $this->db->or_where('school_uuid', NULL);
                $this->db->or_where('school_uuid', '');
                $this->db->group_end();
            } elseif ($this->db->field_exists('server_id', $table)) {
                $this->db->group_start();
                $this->db->where('server_id', $school_uuid);
                $this->db->or_where('server_id', NULL);
                $this->db->or_where('server_id', '');
                $this->db->group_end();
            }
            // If table has no school identification column, it is assumed to be Global/Shared Data (e.g. Reference Tables)
        }

        // Filter berdasarkan waktu sync terakhir
        if ($last_sync) {
            if ($this->db->field_exists('updated_at', $table)) {
                $this->db->where('updated_at >', $last_sync);
            } elseif ($this->db->field_exists('sync_at', $table)) {
                $this->db->where('sync_at >', $last_sync);
            }
        }

        // Apply additional filters
        foreach ($filters as $key => $value) {
            $this->db->where($key, $value);
        }

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Get IDs of soft-deleted records for delta sync
     * 
     * This allows clients to know which records have been deleted on the server
     * so they can remove them from their local database.
     * 
     * @param string $table Table name
     * @param string $last_sync Last sync timestamp
     * @param string $school_uuid School UUID for data isolation
     * @return array Array of deleted record IDs
     */
    public function get_deleted_records($table, $last_sync = null, $school_uuid = null)
    {
        if (!isset($this->sync_tables[$table])) {
            return [];
        }

        // Check if table exists and has deleted_at column
        if (!$this->db->table_exists($table) || !$this->db->field_exists('deleted_at', $table)) {
            return [];
        }

        // Get primary key column name (assumption: 'id' or 'id_<table>')
        $pk = $this->_get_primary_key($table);
        if (!$pk) {
            return [];
        }

        $this->db->select($pk);
        $this->db->from($table);
        $this->db->where('deleted_at IS NOT NULL', null, false);

        // Filter by deletion time if last_sync provided
        if ($last_sync) {
            $this->db->where('deleted_at >', $last_sync);
        }

        // Filter by school for data isolation
        if ($school_uuid) {
            if ($this->db->field_exists('sync_school_uuid', $table)) {
                $this->db->group_start();
                $this->db->where('sync_school_uuid', $school_uuid);
                $this->db->or_where('sync_school_uuid', NULL);
                $this->db->or_where('sync_school_uuid', '');
                $this->db->group_end();
            } elseif ($this->db->field_exists('school_uuid', $table)) {
                $this->db->group_start();
                $this->db->where('school_uuid', $school_uuid);
                $this->db->or_where('school_uuid', NULL);
                $this->db->or_where('school_uuid', '');
                $this->db->group_end();
            }
        }

        $result = $this->db->get()->result_array();

        // Extract just the IDs
        return array_column($result, $pk);
    }

    /**
     * Get primary key column name for a table
     */
    private function _get_primary_key($table)
    {
        // Common primary key naming conventions
        $possible_keys = [
            'id',
            'id_' . $table,
            $table . '_id'
        ];

        foreach ($possible_keys as $key) {
            if ($this->db->field_exists($key, $table)) {
                return $key;
            }
        }

        // Fallback: try to get from DB schema
        $fields = $this->db->field_data($table);
        foreach ($fields as $field) {
            if (isset($field->primary_key) && $field->primary_key) {
                return $field->name;
            }
        }

        return 'id'; // Default assumption
    }

    /**
     * Soft delete a record (set deleted_at instead of actual delete)
     */
    public function soft_delete($table, $id, $pk = 'id')
    {
        if (!$this->db->table_exists($table) || !$this->db->field_exists('deleted_at', $table)) {
            // Fallback to hard delete if soft delete not supported
            return $this->db->where($pk, $id)->delete($table);
        }

        return $this->db->where($pk, $id)->update($table, [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Delete records locally based on IDs from server
     * Used during download to sync deletions
     */
    public function delete_by_ids($table, $ids, $pk = 'id')
    {
        if (empty($ids) || !$this->db->table_exists($table)) {
            return 0;
        }

        $this->db->where_in($pk, $ids);
        $this->db->delete($table);

        return $this->db->affected_rows();
    }


    /**
     * Get pending upload data dari server lokal
     */
    public function get_pending_upload()
    {
        $result = [];

        foreach ($this->sync_tables as $table => $config) {
            if ($config['direction'] === 'upload' || $config['direction'] === 'both') {
                if ($this->db->table_exists($table) && $this->db->field_exists('sync_status', $table)) {
                    $this->db->where('sync_status', 'pending');
                    $data = $this->db->get($table)->result_array();
                    if (!empty($data)) {
                        $result[$table] = $data;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get data for upload from specific table
     * Used for cron-based automatic sync
     * 
     * @param string $table Table name
     * @param int $limit Max records to return
     * @return array Records to upload
     */
    public function get_data_for_upload($table, $limit = 500)
    {
        if (!isset($this->sync_tables[$table])) {
            return [];
        }

        $config = $this->sync_tables[$table];

        // Check direction allows upload
        if (!in_array($config['direction'], ['upload', 'both'])) {
            return [];
        }

        if (!$this->db->table_exists($table)) {
            return [];
        }

        // Get sync config
        $sync_config = $this->get_config();

        // Build query
        $this->db->from($table);

        // Filter by sync_status if field exists
        if ($this->db->field_exists('sync_status', $table)) {
            $this->db->where('sync_status', 'pending');
        }
        // Alternative: filter by updated_at since last sync
        elseif ($this->db->field_exists('updated_at', $table)) {
            $last_upload = isset($sync_config->last_sync_upload) ? $sync_config->last_sync_upload : null;
            if ($last_upload) {
                $this->db->where('updated_at >', $last_upload);
            }
        }
        // Alternative: filter by sync_at
        elseif ($this->db->field_exists('sync_at', $table)) {
            $last_upload = isset($sync_config->last_sync_upload) ? $sync_config->last_sync_upload : null;
            if ($last_upload) {
                $this->db->where('sync_at >', $last_upload);
            }
        }

        // Exclude soft-deleted records
        if ($this->db->field_exists('deleted_at', $table)) {
            $this->db->where('deleted_at IS NULL');
        }

        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    /**
     * Import data dari server pusat ke lokal
     */
    public function import_data($table, $data, $school_uuid = null)
    {
        if (!isset($this->sync_tables[$table]) || empty($data)) {
            return ['success' => 0, 'failed' => 0, 'errors' => []];
        }

        // Check if table exists
        if (!$this->db->table_exists($table)) {
            return ['success' => 0, 'failed' => 0, 'errors' => ["Table $table does not exist"]];
        }

        $pk = $this->sync_tables[$table]['pk'];
        $success = 0;
        $failed = 0;
        $errors = [];

        // Check for school identification column once
        $school_col = null;
        if ($school_uuid) {
            if ($this->db->field_exists('sync_school_uuid', $table))
                $school_col = 'sync_school_uuid';
            elseif ($this->db->field_exists('school_uuid', $table))
                $school_col = 'school_uuid';
            elseif ($this->db->field_exists('server_id', $table))
                $school_col = 'server_id';
        }

        foreach ($data as $row) {
            try {
                // Enforce School Ownership if strictly defined
                if ($school_col && $school_uuid) {
                    $row[$school_col] = $school_uuid;
                }

                // Check if record exists by sync_uuid
                $existing = null;
                if (!empty($row['sync_uuid'])) {
                    $this->db->where('sync_uuid', $row['sync_uuid']);
                    $existing = $this->db->get($table)->row();
                }

                // Fallback: Check based on Primary Key (if sync_uuid check failed)
                // This prevents duplication if local data exists but has no sync_uuid
                if (!$existing && !empty($row[$pk])) {
                    $this->db->where($pk, $row[$pk]);
                    $existing = $this->db->get($table)->row();
                }

                if ($existing) {
                    // Update existing record
                    unset($row[$pk]); // Remove primary key from update data (keep existing PK)
                    $row['sync_status'] = 'synced';
                    $row['sync_at'] = date('Y-m-d H:i:s');

                    // Use PK for update clause to ensure we target the correct record
                    $pk_val = $existing->$pk;

                    if (!$this->db->where($pk, $pk_val)->update($table, $row)) {
                        $db_error = $this->db->error();
                        throw new Exception('Update failed: ' . ($db_error['message'] ?? 'Unknown DB error'));
                    }
                } else {
                    // Insert new record
                    $old_id = $row[$pk] ?? null;

                    // Try to preserve ID if possible (insert with ID)
                    // If conflicting, we might need to unset it, but usually for sync we want same IDs
                    // Checking if ID available
                    if ($old_id) {
                        // Check if ID is taken (should be caught by existing check above, but double check)
                        $this->db->where($pk, $old_id);
                        if ($this->db->count_all_results($table) > 0) {
                            unset($row[$pk]); // ID taken, let auto-increment works
                        }
                    } else {
                        unset($row[$pk]);
                    }

                    $row['sync_status'] = 'synced';
                    $row['sync_at'] = date('Y-m-d H:i:s');
                    $row['sync_origin'] = 'pusat';

                    if (empty($row['sync_uuid'])) {
                        $row['sync_uuid'] = $this->generate_uuid();
                    }

                    if (!$this->db->insert($table, $row)) {
                        $db_error = $this->db->error();
                        throw new Exception('Insert failed: ' . ($db_error['message'] ?? 'Unknown DB error'));
                    }
                    $new_id = $this->db->insert_id();

                    // If we inserted with explicit ID, new_id might be 0 or the ID
                    if (isset($row[$pk])) {
                        $new_id = $row[$pk];
                    }

                    // Save ID mapping
                    if ($old_id && $new_id && $old_id != $new_id) {
                        $this->save_id_mapping($table, $new_id, $old_id, $row['sync_uuid']);
                    }
                }
                $success++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $row,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    // ==================== CONFLICT RESOLUTION ====================

    /**
     * Detect if there's a conflict between local and remote record
     * A conflict occurs when both records have been modified since last sync
     * 
     * @param string $table Table name
     * @param array $remote_record Record from server
     * @param string $pk Primary key column
     * @return array|null Conflict info or null if no conflict
     */
    public function detect_conflict($table, $remote_record, $pk = 'id')
    {
        // Check if record exists locally by sync_uuid
        $sync_uuid = $remote_record['sync_uuid'] ?? null;
        if (!$sync_uuid) {
            return null; // No UUID means new record, no conflict possible
        }

        $local = $this->db->where('sync_uuid', $sync_uuid)->get($table)->row_array();
        if (!$local) {
            return null; // Record doesn't exist locally, no conflict
        }

        // Get timestamps for comparison
        $local_updated = $local['updated_at'] ?? ($local['sync_at'] ?? null);
        $remote_updated = $remote_record['updated_at'] ?? ($remote_record['sync_at'] ?? null);
        $last_sync = $local['sync_at'] ?? null;

        // If local record was modified after last sync, there might be a conflict
        if ($local_updated && $last_sync && strtotime($local_updated) > strtotime($last_sync)) {
            // Both sides modified - this is a conflict
            if ($remote_updated && strtotime($remote_updated) > strtotime($last_sync)) {
                return [
                    'table' => $table,
                    'sync_uuid' => $sync_uuid,
                    'local_id' => $local[$pk] ?? null,
                    'local_record' => $local,
                    'remote_record' => $remote_record,
                    'local_updated' => $local_updated,
                    'remote_updated' => $remote_updated,
                    'last_sync' => $last_sync,
                    'detected_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        return null; // No conflict
    }

    /**
     * Resolve a sync conflict
     * 
     * @param array $conflict Conflict data from detect_conflict
     * @param string $strategy Resolution strategy: 'server_wins', 'local_wins', 'newest_wins', 'manual'
     * @return array Result of resolution
     */
    public function resolve_conflict($conflict, $strategy = 'server_wins')
    {
        $table = $conflict['table'];
        $sync_uuid = $conflict['sync_uuid'];
        $local = $conflict['local_record'];
        $remote = $conflict['remote_record'];

        // Determine winner based on strategy
        $winner = null;
        $winner_source = null;

        switch ($strategy) {
            case 'server_wins':
                $winner = $remote;
                $winner_source = 'server';
                break;

            case 'local_wins':
                $winner = $local;
                $winner_source = 'local';
                break;

            case 'newest_wins':
                $local_time = strtotime($conflict['local_updated'] ?? '1970-01-01');
                $remote_time = strtotime($conflict['remote_updated'] ?? '1970-01-01');

                if ($remote_time >= $local_time) {
                    $winner = $remote;
                    $winner_source = 'server';
                } else {
                    $winner = $local;
                    $winner_source = 'local';
                }
                break;

            case 'manual':
                // Log conflict for manual resolution
                $this->log_conflict($conflict, 'pending');
                return [
                    'resolved' => false,
                    'status' => 'pending_manual',
                    'message' => 'Conflict logged for manual resolution'
                ];

            default:
                $winner = $remote; // Default to server wins
                $winner_source = 'server';
        }

        // Apply the winning record
        if ($winner_source === 'server') {
            // Update local with remote data
            $pk = $this->_get_primary_key($table);
            unset($winner[$pk]);
            $winner['sync_status'] = 'synced';
            $winner['sync_at'] = date('Y-m-d H:i:s');
            $winner['conflict_resolved'] = date('Y-m-d H:i:s');
            $winner['conflict_strategy'] = $strategy;

            $this->db->where('sync_uuid', $sync_uuid)->update($table, $winner);
        }
        // If local wins, no update needed (local already has the data)

        // Log the resolution
        $this->log_conflict($conflict, 'resolved', $strategy, $winner_source);

        return [
            'resolved' => true,
            'status' => 'resolved',
            'winner' => $winner_source,
            'strategy' => $strategy
        ];
    }

    /**
     * Log a conflict for tracking and audit
     * 
     * @param array $conflict Conflict data
     * @param string $status Status: 'pending', 'resolved', 'ignored'
     * @param string $strategy Resolution strategy used
     * @param string $winner Who won: 'server' or 'local'
     */
    public function log_conflict($conflict, $status = 'pending', $strategy = null, $winner = null)
    {
        // Create conflict log table if not exists
        if (!$this->db->table_exists('sync_conflicts')) {
            $this->_create_conflict_table();
        }

        $data = [
            'table_name' => $conflict['table'],
            'sync_uuid' => $conflict['sync_uuid'],
            'local_id' => $conflict['local_id'] ?? null,
            'local_data' => json_encode($conflict['local_record']),
            'remote_data' => json_encode($conflict['remote_record']),
            'local_updated' => $conflict['local_updated'] ?? null,
            'remote_updated' => $conflict['remote_updated'] ?? null,
            'status' => $status,
            'resolution_strategy' => $strategy,
            'winner' => $winner,
            'created_at' => date('Y-m-d H:i:s'),
            'resolved_at' => ($status === 'resolved') ? date('Y-m-d H:i:s') : null
        ];

        $this->db->insert('sync_conflicts', $data);
        return $this->db->insert_id();
    }

    /**
     * Get pending conflicts for manual resolution
     * 
     * @param string $table Optional table filter
     * @param int $limit Max records
     * @return array List of pending conflicts
     */
    public function get_pending_conflicts($table = null, $limit = 50)
    {
        if (!$this->db->table_exists('sync_conflicts')) {
            return [];
        }

        $this->db->where('status', 'pending');

        if ($table) {
            $this->db->where('table_name', $table);
        }

        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get('sync_conflicts')->result();
    }

    /**
     * Resolve a pending conflict by ID
     * 
     * @param int $conflict_id ID from sync_conflicts table
     * @param string $choice 'local' or 'remote'
     * @return array Result
     */
    public function resolve_conflict_by_id($conflict_id, $choice)
    {
        if (!$this->db->table_exists('sync_conflicts')) {
            return ['success' => false, 'message' => 'Conflict table not exists'];
        }

        $conflict_row = $this->db->where('id', $conflict_id)->get('sync_conflicts')->row();
        if (!$conflict_row) {
            return ['success' => false, 'message' => 'Conflict not found'];
        }

        if ($conflict_row->status !== 'pending') {
            return ['success' => false, 'message' => 'Conflict already resolved'];
        }

        $local_data = json_decode($conflict_row->local_data, true);
        $remote_data = json_decode($conflict_row->remote_data, true);

        // Reconstruct conflict array
        $conflict = [
            'table' => $conflict_row->table_name,
            'sync_uuid' => $conflict_row->sync_uuid,
            'local_id' => $conflict_row->local_id,
            'local_record' => $local_data,
            'remote_record' => $remote_data,
            'local_updated' => $conflict_row->local_updated,
            'remote_updated' => $conflict_row->remote_updated
        ];

        // Apply resolution
        $strategy = ($choice === 'local') ? 'local_wins' : 'server_wins';
        $result = $this->resolve_conflict($conflict, $strategy);

        // Update conflict record
        $this->db->where('id', $conflict_id)->update('sync_conflicts', [
            'status' => 'resolved',
            'resolution_strategy' => $strategy,
            'winner' => $choice,
            'resolved_at' => date('Y-m-d H:i:s')
        ]);

        return ['success' => true, 'result' => $result];
    }

    /**
     * Create sync_conflicts table
     */
    private function _create_conflict_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `sync_conflicts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `table_name` VARCHAR(100) NOT NULL,
            `sync_uuid` VARCHAR(36) NOT NULL,
            `local_id` INT NULL,
            `local_data` JSON NULL,
            `remote_data` JSON NULL,
            `local_updated` DATETIME NULL,
            `remote_updated` DATETIME NULL,
            `status` ENUM('pending', 'resolved', 'ignored') DEFAULT 'pending',
            `resolution_strategy` VARCHAR(50) NULL,
            `winner` VARCHAR(20) NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `resolved_at` DATETIME NULL,
            INDEX `idx_status` (`status`),
            INDEX `idx_table` (`table_name`),
            INDEX `idx_uuid` (`sync_uuid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->db->query($sql);
    }

    /**
     * Get conflict statistics
     * 
     * @return array Stats
     */
    public function get_conflict_stats()
    {
        if (!$this->db->table_exists('sync_conflicts')) {
            return ['pending' => 0, 'resolved' => 0, 'total' => 0];
        }

        $pending = $this->db->where('status', 'pending')->count_all_results('sync_conflicts');
        $resolved = $this->db->where('status', 'resolved')->count_all_results('sync_conflicts');

        return [
            'pending' => $pending,
            'resolved' => $resolved,
            'total' => $pending + $resolved
        ];
    }

    /**
     * Export data dari lokal ke pusat
     */
    public function export_data($table, $school_uuid)
    {
        if (!isset($this->sync_tables[$table])) {
            return [];
        }

        $config = $this->sync_tables[$table];

        $this->db->from($table);

        if ($this->db->field_exists('sync_status', $table)) {
            $this->db->where('sync_status', 'pending');
        }

        return $this->db->get()->result_array();
    }

    /**
     * Mark records as synced
     */
    public function mark_as_synced($table, $ids)
    {
        if (empty($ids))
            return true;

        $pk = $this->sync_tables[$table]['pk'] ?? 'id';

        return $this->db->where_in($pk, $ids)
            ->update($table, [
                'sync_status' => 'synced',
                'sync_at' => date('Y-m-d H:i:s')
            ]);
    }

    // ==================== SYNC QUEUE ====================

    /**
     * Add to sync queue
     */
    public function add_to_queue($table, $record_id, $action, $data = null, $priority = 5)
    {
        return $this->db->insert('sync_queue', [
            'table_name' => $table,
            'record_id' => $record_id,
            'action' => $action,
            'data_json' => $data ? json_encode($data) : null,
            'priority' => $priority,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get pending queue items
     */
    public function get_queue_items($limit = 100)
    {
        return $this->db->where('status', 'pending')
            ->where('retry_count <', 3)
            ->order_by('priority', 'ASC')
            ->order_by('created_at', 'ASC')
            ->limit($limit)
            ->get('sync_queue')
            ->result();
    }

    /**
     * Update queue item status
     */
    public function update_queue_status($id, $status, $error = null)
    {
        $data = [
            'status' => $status,
            'processed_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'failed') {
            $data['retry_count'] = $this->db->query("SELECT retry_count FROM sync_queue WHERE id = ?", [$id])->row()->retry_count + 1;
            $data['error_message'] = $error;
        }

        return $this->db->where('id', $id)->update('sync_queue', $data);
    }

    // ==================== SYNC LOG ====================

    /**
     * Create sync log entry
     */
    /**
     * Create sync log entry
     */
    public function create_log($data)
    {
        // Ensure error_message column exists
        if ($this->db->table_exists('sync_log') && !$this->db->field_exists('error_message', 'sync_log')) {
            $this->load->dbforge();
            $fields = array(
                'error_message' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'after' => 'status'
                )
            );
            $this->dbforge->add_column('sync_log', $fields);
        }

        // Skip if table doesn't exist
        if (!$this->db->table_exists('sync_log')) {
            return 0;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['started_at'] = date('Y-m-d H:i:s');
        $data['status'] = isset($data['status']) ? $data['status'] : 'running';

        $this->db->insert('sync_log', $data);
        return $this->db->insert_id();
    }

    /**
     * Update sync log
     */
    public function update_log($id, $data)
    {
        // Skip if table doesn't exist
        if (!$this->db->table_exists('sync_log')) {
            return false;
        }

        if (isset($data['status']) && in_array($data['status'], ['success', 'failed', 'partial'])) {
            $data['completed_at'] = date('Y-m-d H:i:s');

            // Calculate duration
            $this->db->where('id', $id);
            $log = $this->db->get('sync_log')->row();
            if ($log && $log->started_at) {
                $start = strtotime($log->started_at);
                $end = strtotime($data['completed_at']);
                $data['duration_seconds'] = $end - $start;
            }
        }

        // Handle error message array to JSON conversion
        if (isset($data['error_message']) && is_array($data['error_message'])) {
            $data['error_message'] = json_encode($data['error_message']);
        }

        return $this->db->where('id', $id)->update('sync_log', $data);
    }

    /**
     * Get sync log history
     */
    public function get_sync_log($limit = 50)
    {
        if (!$this->db->table_exists('sync_log')) {
            return [];
        }
        return $this->db->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('sync_log')
            ->result();
    }

    /**
     * Get sync status summary
     */
    public function get_sync_status()
    {
        $config = $this->get_config();

        $pending_count = 0;
        foreach ($this->sync_tables as $table => $tconfig) {
            if ($this->db->table_exists($table) && $this->db->field_exists('sync_status', $table)) {
                $pending_count += $this->db->where('sync_status', 'pending')->count_all_results($table);
            }
        }

        $queue_count = 0;
        if ($this->db->table_exists('sync_queue')) {
            $queue_count = $this->db->where('status', 'pending')->count_all_results('sync_queue');
        }

        $last_log = null;
        if ($this->db->table_exists('sync_log')) {
            $last_log = $this->db->order_by('created_at', 'DESC')
                ->limit(1)
                ->get('sync_log')
                ->row();
        }

        return [
            'config' => $config,
            'pending_records' => $pending_count,
            'pending_upload' => $pending_count,
            'queue_count' => $queue_count,
            'last_sync' => $last_log,
            'is_pusat' => $this->is_pusat_server()
        ];
    }

    // ==================== ID MAPPING ====================

    /**
     * Save ID mapping between local and pusat
     */
    public function save_id_mapping($table, $local_id, $pusat_id, $uuid = null)
    {
        return $this->db->replace('sync_id_mapping', [
            'table_name' => $table,
            'local_id' => $local_id,
            'pusat_id' => $pusat_id,
            'uuid' => $uuid,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get local ID from pusat ID
     */
    public function get_local_id($table, $pusat_id)
    {
        $this->db->where([
            'table_name' => $table,
            'pusat_id' => $pusat_id
        ]);
        $mapping = $this->db->get('sync_id_mapping')->row();

        return $mapping ? $mapping->local_id : null;
    }

    /**
     * Get pusat ID from local ID
     */
    public function get_pusat_id($table, $local_id)
    {
        $this->db->where([
            'table_name' => $table,
            'local_id' => $local_id
        ]);
        $mapping = $this->db->get('sync_id_mapping')->row();

        return $mapping ? $mapping->pusat_id : null;
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Generate UUID v4
     */
    public function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate API key
     */
    public function generate_api_key()
    {
        return 'AKM_' . strtoupper(bin2hex(random_bytes(16)));
    }

    /**
     * Generate API secret
     */
    public function generate_api_secret()
    {
        return hash('sha256', random_bytes(32) . microtime(true));
    }

    /**
     * Get sync tables configuration with stats
     */
    public function get_sync_tables()
    {
        return $this->sync_tables;
    }

    /**
     * Get sync tables configuration with local record counts
     */
    public function get_sync_tables_with_stats()
    {
        $tables = $this->sync_tables;
        foreach ($tables as $name => &$config) {
            if ($this->db->table_exists($name)) {
                $config['total_records'] = $this->db->count_all($name);
            } else {
                $config['total_records'] = 0;
            }
        }
        return $tables;
    }

    /**
     * Get sync statistics for last 7 days
     */
    public function get_sync_stats_last_7_days()
    {
        // Get last 7 days dates
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $dates[] = date('Y-m-d', strtotime("-$i days"));
        }

        $stats = [
            'labels' => [],
            'download' => [],
            'upload' => []
        ];

        foreach ($dates as $date) {
            // Count successful downloads
            $down = $this->db->select_sum('records_success')
                ->where('DATE(created_at)', $date)
                ->where('sync_type', 'download')
                ->get('sync_log')->row()->records_success;

            // Count successful uploads
            $up = $this->db->select_sum('records_success')
                ->where('DATE(created_at)', $date)
                ->where('sync_type', 'upload')
                ->get('sync_log')->row()->records_success;

            $stats['labels'][] = date('d/m', strtotime($date));
            $stats['download'][] = (int) $down;
            $stats['upload'][] = (int) $up;
        }

        return $stats;
    }

    /**
     * Test connection to pusat server
     */
    public function test_connection()
    {
        $config = $this->get_config();

        if (!$config || empty($config->pusat_url) || empty($config->api_key)) {
            return [
                'success' => false,
                'message' => 'Konfigurasi server pusat belum lengkap'
            ];
        }

        $url = rtrim($config->pusat_url, '/') . '/api_sync/ping';
        $headers = [
            'X-Api-Key: ' . $config->api_key,
            'Content-Type: application/json'
        ];

        // Add HMAC Signature if api_secret is configured
        $body = json_encode(['timestamp' => time()]);
        if (!empty($config->api_secret)) {
            $signature = hash_hmac('sha256', $body, $config->api_secret);
            $headers[] = 'X-Api-Signature: ' . $signature;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body
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
            $json = json_decode($response, true);
            $msg = ($json && isset($json['message'])) ? $json['message'] : 'Server merespons dengan kode: ' . $http_code;

            return [
                'success' => false,
                'message' => $msg
            ];
        }

        $result = json_decode($response, true);
        return [
            'success' => true,
            'message' => 'Koneksi berhasil',
            'server_time' => $result['timestamp'] ?? null,
            'data' => $result['data'] ?? null
        ];
    }

    /**
     * Ensure all necessary tables have sync_school_uuid column
     */
    public function optimize_tables()
    {
        $tables = [
            'master_jurusan',
            'master_kelas',
            'master_mapel',
            'master_guru',
            'master_siswa',
            'cbt_bank_soal',
            'cbt_jadwal',
            'cbt_ruang',
            'cbt_sesi',
            'cbt_soal',
            'cbt_jawaban'
        ];

        foreach ($tables as $table) {
            if ($this->db->table_exists($table) && !$this->db->field_exists('sync_school_uuid', $table)) {
                $this->db->query("ALTER TABLE {$table} ADD COLUMN sync_school_uuid VARCHAR(50) DEFAULT NULL, ADD INDEX (sync_school_uuid)");
            }
        }
    }
    // ==================== MEDIA SYNC ====================

    /**
     * Process and upload media files referenced in data
     */
    public function get_media_files_list($pending_data)
    {
        if (empty($pending_data))
            return [];
        if (!isset($pending_data['cbt_soal']))
            return [];

        $records = $pending_data['cbt_soal'];
        $files_to_upload = [];
        $processed = [];

        foreach ($records as $row) {
            // Columns that might contain images
            $cols = ['soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];
            foreach ($cols as $col) {
                if (!empty($row[$col])) {
                    preg_match_all('/src="([^"]*)"/i', $row[$col], $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $src) {
                            $path = urldecode($src);
                            $pos = strpos($path, 'uploads/');
                            if ($pos !== false) {
                                $rel_path = substr($path, $pos);
                                if (!isset($processed[$rel_path])) {
                                    $full_path = FCPATH . $rel_path;
                                    if (file_exists($full_path)) {
                                        $files_to_upload[] = $rel_path;
                                        $processed[$rel_path] = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Columns that are direct file paths
            $file_cols = ['file', 'file1'];
            foreach ($file_cols as $col) {
                if (!empty($row[$col])) {
                    $rel_path = 'uploads/bank_soal/' . $row[$col];
                    if (!isset($processed[$rel_path])) {
                        $full_path = FCPATH . $rel_path;
                        if (file_exists($full_path)) {
                            $files_to_upload[] = $rel_path;
                            $processed[$rel_path] = true;
                        }
                    }
                }
            }
        }

        return $files_to_upload;
    }

    public function process_media_files($pending_data)
    {
        $files = $this->get_media_files_list($pending_data);
        $count = 0;
        foreach ($files as $rel_path) {
            $full_path = FCPATH . $rel_path;
            $this->upload_file_to_pusat($full_path, $rel_path);
            $count++;
        }
        return $count;
    }

    private function _handle_file_upload($src_url, &$processed_files)
    {
        // Decode URL
        $path = urldecode($src_url);

        // Check if local file
        // We expect paths like: .../uploads/bank_soal/xyz.jpg OR just uploads/bank_soal/xyz.jpg
        $pos = strpos($path, 'uploads/');
        if ($pos !== false) {
            $rel_path = substr($path, $pos); // "uploads/bank_soal/xyz.jpg"

            // Avoid duplicate uploads in one batch
            if (isset($processed_files[$rel_path]))
                return;

            $full_path = FCPATH . $rel_path;
            if (file_exists($full_path)) {
                $this->upload_file_to_pusat($full_path, $rel_path);
                $processed_files[$rel_path] = true;
            }
        }
    }

    public function upload_file_to_pusat($local_path, $rel_path)
    {
        $config = $this->get_config();
        if (!$config || empty($config->pusat_url) || empty($config->api_key))
            return false;

        $endpoint = '/api_sync/upload_media';
        $url = rtrim($config->pusat_url, '/') . $endpoint;

        $cfile = new CURLFile($local_path, mime_content_type($local_path), basename($local_path));

        $post = [
            'file' => $cfile,
            'rel_path' => $rel_path
        ];

        $headers = [
            'Content-Type: multipart/form-data',
            'X-Api-Key: ' . $config->api_key
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
