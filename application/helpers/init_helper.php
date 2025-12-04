<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('database_connected')) {
    function database_connected()
    {
        $CI =& get_instance();
        $CI->load->database();

        $db_name = $CI->db->database;
        if (empty($db_name)) return false;

        // Membuat koneksi manual ke MySQL server (tanpa nama database)
        $hostname = $CI->db->hostname;
        $username = $CI->db->username;
        $password = $CI->db->password;

        $conn = @mysqli_connect($hostname, $username, $password);
        if (!$conn) return false;

        // Cek apakah database benar-benar ada di server
        $result = mysqli_query($conn, "SHOW DATABASES LIKE '" . mysqli_real_escape_string($conn, $db_name) . "'");
        $exists = mysqli_num_rows($result) > 0;

        mysqli_close($conn);
        return $exists;
    }
}

if (!function_exists('is_database_empty')) {
    function is_database_empty()
    {
        $CI =& get_instance();
        $CI->load->database();
        return count($CI->db->list_tables()) === 0;
    }
}

if (!function_exists('is_setting_empty')) {
    function is_setting_empty()
    {
        $CI =& get_instance();
        $CI->load->database();
        if (!$CI->db->table_exists('setting')) return true;

        $setting = $CI->db->get('setting')->result();
        return count($setting) === 0;
    }
}

if (!function_exists('get_current_migration_version')) {
    function get_current_migration_version()
    {
        $CI =& get_instance();
        $CI->load->database();
        $CI->config->load('migration');

        $current_version = 0;

        if ($CI->db->table_exists('migrations') && $CI->db->field_exists('version', 'migrations')) {
            $migrations = $CI->db->get('migrations');
            if ($migrations->num_rows() > 0) {
                $curr_version = $migrations->first_row();
                $current_version = isset($curr_version->version) ? (int) $curr_version->version : 0;
            }
        }

        return $current_version;
    }
}

if (!function_exists('get_latest_migration_version')) {
    function get_latest_migration_version()
    {
        $CI =& get_instance();
        $CI->config->load('migration');
        return (int) $CI->config->item('migration_version');
    }
}

if (!function_exists('is_migration_table_empty')) {
    function is_migration_table_empty()
    {
        $CI =& get_instance();
        $CI->load->database();

        if (!$CI->db->table_exists('migrations')) return true;

        $migrations = $CI->db->get('migrations');
        return $migrations->num_rows() === 0;
    }
}
