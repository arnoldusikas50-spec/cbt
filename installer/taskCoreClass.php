<?php
error_reporting(E_ALL);
$envs = ['development', 'testing', 'production'];
define('ENVIRONMENT', $_SERVER['CI_ENV'] ?? $envs[0]);
$application_folder = 'application';
define('BASEPATH', str_replace("\\", "/", $application_folder) . '/');

class Core {

    function checkFile() {
        return file_exists('../application/config/database.php');
    }

    function checkEmpty($data) {
        return !empty($data['hostname']) && !empty($data['username']) && !empty($data['database']);
    }

    function show_message($condition, $type, $message, $data = null) {
        return json_encode([
            'cond' => $condition,
            'type' => $type,
            'message' => $message,
            'data' => $data
        ]);
    }

    function dbExist()
    {
        $ret = ['status' => false];

        if (!file_exists('../application/config/database.php')) {
            return $ret;
        }

        include '../application/config/database.php';
        $data = $db['default'];

        if (empty($data['hostname']) || empty($data['username']) || empty($data['database'])) {
            return $ret;
        }

        try {
            $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], '');
            if ($mysqli->connect_error) {
                return $ret;
            }

            $dbname = $mysqli->real_escape_string($data['database']);
            $db_exists = mysqli_fetch_array(mysqli_query($mysqli, "SHOW DATABASES LIKE '$dbname'"));
            $ret['status'] = !empty($db_exists);
            $ret['data'] = [
                'hostname' => $data['hostname'],
                'username' => $data['username'],
                'password' => $data['password'],
                'database' => $dbname
            ];
        } catch (mysqli_sql_exception $e) {
            $ret['status'] = false;
            $ret['data'] = 'Gagal koneksi DB: ' . $e->getMessage();
        }

        return $ret;
    }

    function tableExist()
    {
        $ret = ['status' => false];

        if (!file_exists('../application/config/database.php')) {
            return $ret;
        }

        include '../application/config/database.php';
        $data = $db['default'];

        if (empty($data['hostname']) || empty($data['username']) || empty($data['database'])) {
            return $ret;
        }

        try {
            $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], '');
            $dbname = $mysqli->real_escape_string($data['database']);
            $mysqli->select_db($dbname);
            $check_tables = mysqli_query($mysqli, "SHOW TABLES");
            $count = mysqli_num_rows($check_tables);

            $ret['status'] = $count > 0;
            $ret['data'] = [
                'hostname' => $data['hostname'],
                'username' => $data['username'],
                'password' => $data['password'],
                'database' => $dbname
            ];
        } catch (mysqli_sql_exception $e) {
            $ret['status'] = false;
            $ret['data'] = 'Gagal koneksi DB: ' . $e->getMessage();
        }

        return $ret;
    }

    function getAllData($data) {
        return $data;
    }

    function write_db_config($data) {
        $template_path = '../assets/app/db/database.php';
        $output_path = '../application/config/database.php';
        if (!file_exists($template_path)) {
            error_log("Template konfigurasi database tidak ditemukan.");
            return false;
        }

        $database_file = file_get_contents($template_path);
        if ($database_file === false) {
            error_log("Gagal membaca template konfigurasi.");
            return false;
        }

        $new = str_replace(
            ["%HOSTNAME%", "%USERNAME%", "%PASSWORD%", "%DATABASE%"],
            [
                htmlspecialchars($data['hostname'], ENT_QUOTES),
                htmlspecialchars($data['username'], ENT_QUOTES),
                htmlspecialchars($data['password'], ENT_QUOTES),
                htmlspecialchars($data['database'], ENT_QUOTES)
            ],
            $database_file
        );

        @chmod($output_path, 0777);
        if (is_writable(dirname($output_path))) {
            $handle = fopen($output_path, 'w+');
            if ($handle === false) {
                error_log("Gagal membuka file konfigurasi untuk ditulis.");
                return false;
            }

            if (fwrite($handle, $new) === false) {
                fclose($handle);
                error_log("Gagal menulis konfigurasi ke file.");
                return false;
            }

            fclose($handle);
            return true;
        } else {
            error_log("Direktori tidak dapat ditulisi: " . dirname($output_path));
            return false;
        }
    }
}
