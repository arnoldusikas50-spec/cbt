<?php

/**
 * Init Helper - Migrated from CI3
 * Helper functions for initialization checks
 */

if (!function_exists('database_connected')) {
    /**
     * Check if database is connected
     */
    function database_connected(): bool
    {
        try {
            $db = \Config\Database::connect();
            return $db->connect(false) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('is_database_empty')) {
    /**
     * Check if database has required tables
     */
    function is_database_empty(): bool
    {
        try {
            $db = \Config\Database::connect();
            $tables = $db->listTables();

            $requiredTables = ['users', 'setting', 'groups'];

            foreach ($requiredTables as $table) {
                if (!in_array($table, $tables)) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return true;
        }
    }
}

if (!function_exists('is_setting_empty')) {
    /**
     * Check if settings table has data
     */
    function is_setting_empty(): bool
    {
        try {
            $db = \Config\Database::connect();
            $result = $db->table('setting')->countAll();
            return $result === 0;
        } catch (\Exception $e) {
            return true;
        }
    }
}

if (!function_exists('singkat_tanggal')) {
    function singkat_tanggal($str)
    {
        $str = str_replace("Jan", "Jan", $str);
        $str = str_replace("Feb", "Feb", $str);
        $str = str_replace("Mar", "Mar", $str);
        $str = str_replace("Apr", "Apr", $str);
        $str = str_replace("May", "Mei", $str);
        $str = str_replace("Jun", "Jun", $str);
        $str = str_replace("Jul", "Jul", $str);
        $str = str_replace("Aug", "Agt", $str);
        $str = str_replace("Sep", "Sep", $str);
        $str = str_replace("Oct", "Okt", $str);
        $str = str_replace("Nov", "Nov", $str);
        $str = str_replace("Dec", "Des", $str);
        $str = str_replace("Mon", "Senin", $str);
        $str = str_replace("Tue", "Selasa", $str);
        $str = str_replace("Wed", "Rabu", $str);
        $str = str_replace("Thu", "Kamis", $str);
        $str = str_replace("Fri", "Jumat", $str);
        $str = str_replace("Sat", "Sabtu", $str);
        $str = str_replace("Sun", "Minggu", $str);
        return $str;
    }
}

if (!function_exists('buat_tanggal')) {
    /**
     * Convert English date format to Indonesian
     * Translates day names (Mon, Tue, etc.) and month names (Jan, Feb, etc.)
     * to their Indonesian equivalents
     */
    function buat_tanggal($str)
    {
        // Translate months
        $str = str_replace("January", "Januari", $str);
        $str = str_replace("February", "Februari", $str);
        $str = str_replace("March", "Maret", $str);
        $str = str_replace("April", "April", $str);
        $str = str_replace("May", "Mei", $str);
        $str = str_replace("June", "Juni", $str);
        $str = str_replace("July", "Juli", $str);
        $str = str_replace("August", "Agustus", $str);
        $str = str_replace("September", "September", $str);
        $str = str_replace("October", "Oktober", $str);
        $str = str_replace("November", "November", $str);
        $str = str_replace("December", "Desember", $str);

        // Translate short months
        $str = str_replace("Jan", "Jan", $str);
        $str = str_replace("Feb", "Feb", $str);
        $str = str_replace("Mar", "Mar", $str);
        $str = str_replace("Apr", "Apr", $str);
        $str = str_replace("Jun", "Jun", $str);
        $str = str_replace("Jul", "Jul", $str);
        $str = str_replace("Aug", "Agt", $str);
        $str = str_replace("Sep", "Sep", $str);
        $str = str_replace("Oct", "Okt", $str);
        $str = str_replace("Nov", "Nov", $str);
        $str = str_replace("Dec", "Des", $str);

        // Translate days
        $str = str_replace("Monday", "Senin", $str);
        $str = str_replace("Tuesday", "Selasa", $str);
        $str = str_replace("Wednesday", "Rabu", $str);
        $str = str_replace("Thursday", "Kamis", $str);
        $str = str_replace("Friday", "Jumat", $str);
        $str = str_replace("Saturday", "Sabtu", $str);
        $str = str_replace("Sunday", "Minggu", $str);

        // Translate short days
        $str = str_replace("Mon", "Sen", $str);
        $str = str_replace("Tue", "Sel", $str);
        $str = str_replace("Wed", "Rab", $str);
        $str = str_replace("Thu", "Kam", $str);
        $str = str_replace("Fri", "Jum", $str);
        $str = str_replace("Sat", "Sab", $str);
        $str = str_replace("Sun", "Min", $str);

        return $str;
    }
}
if (!function_exists('maybe_unserialize')) {
    function maybe_unserialize($original)
    {
        if (is_string($original)) {
            $data = @unserialize($original);
            if ($data !== false || $original === 'b:0;') {
                return $data;
            }

            // check if it's JSON
            $data = json_decode($original);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        return $original;
    }
}
