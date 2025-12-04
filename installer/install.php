<?php
// Aktifkan semua laporan kesalahan selama pengembangan, ubah ke 0 dalam produksi
error_reporting(E_ALL);
require_once('taskCoreClass.php');
require_once('databaseLibrary.php');

$core = new Core();
$database = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = $_POST;
    $cond = $postData['cond'];
    $is_first = $cond == 0;
    $db_exist = $cond == 2;
    $table_exist = $cond == 3;

    if ($is_first) {
        // cek apakah database sudah ada
        $db_is_exist = $core->dbExist();
        if ($db_is_exist['status']) {
            // cek apakah tabel sudah ada
            $table_is_exist = $core->tableExist();
            if ($table_is_exist['status']) {
                $message = $core->show_message(3, 'info', "Database sudah terinstall sebelumnya...", $table_is_exist['data']);
            } else {
                $message = $core->show_message(2, 'info', "Anda akan menggunakan database yang sudah ada, status tabel pada database kosong.", $db_is_exist['data']);
            }
        }
        else {
            $message = $core->show_message(1, 'info', 'Silahkan isi semua parameter yang sesuai.');
        }
    } else {
        // Cek input wajib
        if (!$db_exist && !$table_exist && !$core->checkEmpty($postData)) {
            $message = $core->show_message(4, 'error', "Gagal membuat database, pastikan semua parameter diisi dengan benar.");
        } // Pastikan file ada
        elseif (!$db_exist && !$table_exist && !$core->checkFile()) { // cek apakah file database.php sudah ada, skip jika update
            $message = $core->show_message(5, 'error', "File application/config/database.php tidak ditemukan setelah penulisan.");
        } // Buat database
        elseif (!$db_exist && !$table_exist && !$database->create_database($postData)) { // skip jika update
            $message = $core->show_message(6, 'error', "Gagal membuat database, pastikan semua parameter diisi dengan benar.");
        } // Tulis konfigurasi ke database.php
        elseif (!$db_exist && !$table_exist && !$core->write_db_config($postData)) { // skip jika update
            $message = $core->show_message(7, 'error', "Tidak bisa membuat file konfigurasi database. Coba ubah permission file menjadi 777.");
        } // Buat tabel dari master.sql
        elseif (!$table_exist && !$database->create_tables($postData)) { // skip jika tabel sudah ada
            $message = $core->show_message(8, 'error', "Gagal membuat tabel, pastikan file SQL valid dan struktur sudah sesuai.");
        } // Jika semua sukses
        else {
            $message = $core->show_message(9, 'success', 'Aplikasi siap digunakan...');
        }
    }

    echo $message;
}