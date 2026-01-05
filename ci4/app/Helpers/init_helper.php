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

if (!function_exists('get_setting')) {
    /**
     * Get application settings
     */
    function get_setting(): ?object
    {
        try {
            $db = \Config\Database::connect();
            return $db->table('setting')->get()->getRow();
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('tp_aktif')) {
    /**
     * Get active tahun pelajaran
     */
    function tp_aktif(): ?int
    {
        $setting = get_setting();
        return $setting ? (int) $setting->tp_aktif : null;
    }
}

if (!function_exists('smt_aktif')) {
    /**
     * Get active semester
     */
    function smt_aktif(): ?int
    {
        $setting = get_setting();
        return $setting ? (int) $setting->smt_aktif : null;
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
