<?php

class Database {

    function create_database($data) {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], '');

        if ($mysqli->connect_errno) {
            error_log("Failed to connect to MySQL: " . $mysqli->connect_error);
            return false;
        }

        $database = $mysqli->real_escape_string($data['database']);
        if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            error_log("Database creation failed: " . $mysqli->error);
            $mysqli->close();
            return false;
        }

        $mysqli->close();
        return true;
    }

    function create_tables($data) {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], $data['database']);

        if ($mysqli->connect_errno) {
            error_log("Failed to connect to MySQL: " . $mysqli->connect_error);
            return false;
        }

        $query = $mysqli->query("SHOW TABLES LIKE 'users'");

        if ($query === false) {
            error_log("Error executing query: " . $mysqli->error);
            $mysqli->close();
            return false;
        }

        if ($query->num_rows <= 0) {
            $sql = file_get_contents('../assets/app/db/master.sql');
            if ($sql === false) {
                error_log("Failed to open SQL file");
                $mysqli->close();
                return false;
            }

            if (!$mysqli->multi_query($sql)) {
                error_log("Error executing multi_query: " . $mysqli->error);
                $mysqli->close();
                return false;
            }

            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                } else {
                    if ($mysqli->errno) {
                        error_log("Error in multi_query loop: " . $mysqli->error);
                        $mysqli->close();
                        return false;
                    }
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
        }

        $mysqli->close();
        return true;
    }
}
