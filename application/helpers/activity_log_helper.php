<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Activity Logger Helper
 * 
 * Helper functions for logging user and system activities.
 * Use these functions throughout the application to maintain audit trail.
 * 
 * Usage:
 *   $this->load->helper('activity_log');
 *   log_activity('CREATE', 'cbt_soal', $soal_id, null, $new_data);
 */

if (!function_exists('log_activity')) {
    /**
     * Log an activity to the activity_log table
     * 
     * @param string $action      Action type: LOGIN, LOGOUT, CREATE, UPDATE, DELETE, SYNC, VIEW, EXPORT, etc.
     * @param string $table       Target table name (optional)
     * @param mixed  $target_id   Primary key of affected record (optional)
     * @param mixed  $old_value   Previous value - will be JSON encoded (optional)
     * @param mixed  $new_value   New value - will be JSON encoded (optional)
     * @param int    $user_id     User ID (auto-detected from session if not provided)
     * @param string $user_type   User type: admin, guru, siswa, panitia (auto-detected if not provided)
     * @return bool
     */
    function log_activity($action, $table = null, $target_id = null, $old_value = null, $new_value = null, $user_id = null, $user_type = null)
    {
        $CI =& get_instance();

        // Check if table exists
        if (!$CI->db->table_exists('activity_log')) {
            return false;
        }

        // Auto-detect user from session if not provided
        if ($user_id === null || $user_type === null) {
            $session_data = detect_user_from_session();
            $user_id = $user_id ?? $session_data['user_id'];
            $user_type = $user_type ?? $session_data['user_type'];
        }

        // Prepare data
        $data = [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'action' => strtoupper($action),
            'target_table' => $table,
            'target_id' => $target_id,
            'old_value' => $old_value ? json_encode($old_value) : null,
            'new_value' => $new_value ? json_encode($new_value) : null,
            'ip_address' => $CI->input->ip_address(),
            'user_agent' => substr($CI->input->user_agent(), 0, 500),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $CI->db->insert('activity_log', $data);
    }
}

if (!function_exists('detect_user_from_session')) {
    /**
     * Detect current user from session
     * 
     * @return array ['user_id' => int|null, 'user_type' => string|null]
     */
    function detect_user_from_session()
    {
        $CI =& get_instance();

        // Check various session keys used in the application
        $user_id = null;
        $user_type = null;

        // Admin
        if ($CI->session->userdata('id_user')) {
            $user_id = $CI->session->userdata('id_user');
            $user_type = 'admin';
        }
        // Guru
        elseif ($CI->session->userdata('id_guru')) {
            $user_id = $CI->session->userdata('id_guru');
            $user_type = 'guru';
        }
        // Siswa
        elseif ($CI->session->userdata('id_siswa')) {
            $user_id = $CI->session->userdata('id_siswa');
            $user_type = 'siswa';
        }
        // Panitia
        elseif ($CI->session->userdata('id_panitia')) {
            $user_id = $CI->session->userdata('id_panitia');
            $user_type = 'panitia';
        }

        return [
            'user_id' => $user_id,
            'user_type' => $user_type
        ];
    }
}

if (!function_exists('log_login')) {
    /**
     * Shortcut for logging login events
     */
    function log_login($user_id, $user_type, $success = true)
    {
        $action = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
        return log_activity($action, 'users', $user_id, null, ['login_time' => date('Y-m-d H:i:s')], $user_id, $user_type);
    }
}

if (!function_exists('log_logout')) {
    /**
     * Shortcut for logging logout events
     */
    function log_logout($user_id = null, $user_type = null)
    {
        return log_activity('LOGOUT', 'users', $user_id, null, ['logout_time' => date('Y-m-d H:i:s')], $user_id, $user_type);
    }
}

if (!function_exists('log_sync')) {
    /**
     * Shortcut for logging sync events
     */
    function log_sync($sync_type, $table, $record_count, $status = 'success', $details = null)
    {
        $action = 'SYNC_' . strtoupper($sync_type); // SYNC_DOWNLOAD, SYNC_UPLOAD
        $new_value = [
            'table' => $table,
            'records' => $record_count,
            'status' => $status,
            'details' => $details
        ];
        return log_activity($action, $table, null, null, $new_value, null, 'system');
    }
}

if (!function_exists('log_exam_event')) {
    /**
     * Log exam-related events (start, submit, violation)
     */
    function log_exam_event($event, $jadwal_id, $siswa_id, $details = null)
    {
        $action = 'EXAM_' . strtoupper($event); // EXAM_START, EXAM_SUBMIT, EXAM_VIOLATION, EXAM_TIMEOUT
        return log_activity($action, 'cbt_jadwal', $jadwal_id, null, $details, $siswa_id, 'siswa');
    }
}
