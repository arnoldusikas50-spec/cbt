<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingsModel;
use App\Models\DashboardModel;
use ZipArchive;

class Dbmanager extends BaseController
{
    protected $settings;
    protected $dashboard;

    public function __construct()
    {
        $this->settings = new SettingsModel();
        $this->dashboard = new DashboardModel();
        helper(['filesystem', 'date', 'number']);
    }

    public function index()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        if (!$this->isAdmin())
            return redirect()->to('dashboard');

        // Auto delete old backups
        $this->_auto_delete_old_backups();

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'Backup',
            'subjudul' => 'Backup Database',
            'profile' => null,
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        $list = directory_map('./backups/');
        $arrFile = [];
        if ($list) {
            foreach ($list as $key => $value) {
                if (!is_string($value))
                    continue;
                if ($value == 'index.html' || $value == '.htaccess')
                    continue;

                $nfile = explode('.', $value);
                $nama = $nfile[0] ?? '';
                $type = $nfile[1] ?? '';
                $filepath = './backups/' . $value;
                if (file_exists($filepath)) {
                    $tgl = filemtime($filepath);
                    $size = number_to_size(filesize($filepath));
                    if ($type !== 'html' && $type !== '') {
                        $arrFile[] = ['type' => $type, 'nama' => $nama, 'tgl' => $tgl, 'size' => $size, 'src' => $value];
                    }
                }
            }
        }

        $db = \Config\Database::connect();
        $data['list'] = $arrFile;
        $data['tables'] = $db->listTables();

        return view('setting/db', $data);
    }

    public function backupDb()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false, 'message' => 'Unauthorized']);

        // Increase limits for backup
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        try {
            $db = \Config\Database::connect();
            $tables = $db->listTables();

            $sql = "SET FOREIGN_KEY_CHECKS=0;\n";

            foreach ($tables as $table) {
                // Validate table name
                $table = trim($table ?? '');
                if (empty($table)) {
                    continue;
                }

                $sql .= "DROP TABLE IF EXISTS `$table`;\n";

                $query = $db->query("SHOW CREATE TABLE `$table`");
                $row = $query ? $query->getRowArray() : null;
                if (isset($row['Create Table'])) {
                    $sql .= $row['Create Table'] . ";\n\n";

                    // Use unbuffered query for large tables to save memory? 
                    // CI4 getResultArray() loads all into memory.
                    // For now, we stick to logic but with higher memory limit.
                    $rows = $db->table($table)->get()->getResultArray();
                    foreach ($rows as $row) {
                        $sql .= "INSERT INTO `$table` VALUES(";
                        $values = [];
                        foreach ($row as $val) {
                            $values[] = $db->escape($val);
                        }
                        $sql .= implode(', ', $values);
                        $sql .= ");\n";
                    }
                } elseif (isset($row['Create View'])) {
                    $sql .= $row['Create View'] . ";\n\n";
                }
                $sql .= "\n";
            }
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            if (!is_dir('./backups/')) {
                mkdir('./backups/', 0777, true);
            }

            if (class_exists('ZipArchive')) {
                $filename = 'backup-db-' . date("Y-m-d-H-i-s") . '.sql.zip';
                $save_path = './backups/' . $filename;

                $zip = new ZipArchive;
                if ($zip->open($save_path, ZipArchive::CREATE) === TRUE) {
                    $zip->addFromString('backup.sql', $sql);

                    $password = getenv('encryption.key') ?: '';
                    if (!empty($password) && defined('ZipArchive::EM_AES_256')) {
                        $zip->setEncryptionName('backup.sql', ZipArchive::EM_AES_256, $password);
                    }

                    $zip->close();

                    return $this->response->setJSON([
                        'type' => 'database',
                        'message' => 'Database berhasil dibackup',
                        'csrf_hash' => csrf_hash()
                    ]);
                } else {
                    return $this->response->setJSON([
                        'type' => 'database',
                        'message' => 'Gagal membuat file backup zip',
                        'csrf_hash' => csrf_hash()
                    ]);
                }
            } else {
                $filename = 'backup-db-' . date("Y-m-d-H-i-s") . '.sql';
                $save_path = './backups/' . $filename;

                if (file_put_contents($save_path, $sql) !== false) {
                    return $this->response->setJSON([
                        'type' => 'database',
                        'message' => 'Database berhasil dibackup (Format SQL, Zip extension missing)',
                        'csrf_hash' => csrf_hash()
                    ]);
                } else {
                    return $this->response->setJSON([
                        'type' => 'database',
                        'message' => 'Gagal membuat file backup SQL',
                        'csrf_hash' => csrf_hash()
                    ]);
                }
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    public function backupData()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false]);

        if (!class_exists('ZipArchive')) {
            return $this->response->setJSON([
                'type' => 'file',
                'message' => 'Gagal: PHP Zip extension tidak tersedia. Tidak dapat membackup folder uploads.',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $filename = 'backup-file-' . date("Y-m-d-H-i-s") . '.zip';
        $save_path = './backups/' . $filename;
        $source_path = './uploads/';

        if (!is_dir('./backups/'))
            mkdir('./backups/', 0777, true);

        $zip = new ZipArchive;
        if ($zip->open($save_path, ZipArchive::CREATE) === TRUE) {
            $password = getenv('encryption.key') ?: '';
            $this->_zip_folder($source_path, $zip, '', $password);
            $zip->close();

            return $this->response->setJSON([
                'type' => 'file',
                'message' => 'File data berhasil dibackup',
                'csrf_hash' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'type' => 'file',
                'message' => 'Gagal membuat file backup',
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    private function _zip_folder($folder, &$zipFile, $folderInZip = '', $password = '')
    {
        if (!is_dir($folder))
            return;
        $handle = opendir($folder);
        if ($handle) {
            while (false !== ($f = readdir($handle))) {
                if ($f != '.' && $f != '..') {
                    $filePath = "$folder/$f";
                    $localPath = "$folderInZip$f";
                    if (is_file($filePath)) {
                        $zipFile->addFile($filePath, $localPath);
                        if (!empty($password) && defined('ZipArchive::EM_AES_256')) {
                            $zipFile->setEncryptionName($localPath, ZipArchive::EM_AES_256, $password);
                        }
                    } elseif (is_dir($filePath)) {
                        $zipFile->addEmptyDir($localPath);
                        $this->_zip_folder($filePath, $zipFile, $localPath . '/', $password);
                    }
                }
            }
            closedir($handle);
        }
    }

    public function hapusBackup()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false]);
        $src = $this->request->getPost('src');
        if ($src) {
            $src = basename($src);
            if (unlink('./backups/' . $src)) {
                return $this->response->setJSON(['status' => true, 'message' => 'Backup berhasil dihapus', 'csrf_hash' => csrf_hash()]);
            }
        }
        return $this->response->setJSON(['status' => false, 'message' => 'Gagal menghapus', 'csrf_hash' => csrf_hash()]);
    }

    public function del_backup()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false]);
        helper('filesystem');
        $list = directory_map('./backups/');
        foreach ($list as $value) {
            if ($value != 'index.html' && $value != '.htaccess' && is_string($value)) {
                unlink('./backups/' . $value);
            }
        }
        return $this->response->setJSON(['status' => true, 'list' => $list, 'csrf_hash' => csrf_hash()]);
    }

    public function restore()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false]);

        $src = $this->request->getPost('src');
        if (!$src)
            return $this->response->setJSON(['status' => false, 'message' => 'File not found']);

        $src = basename($src);
        $file_path = './backups/' . $src;

        if (!file_exists($file_path))
            return $this->response->setJSON(['status' => false, 'message' => 'File not found']);

        $is_db = strpos($src, 'backup-db') !== false;
        $is_data = strpos($src, 'backup-file') !== false;

        if ($is_db) {
            return $this->restore_db($file_path);
        } elseif ($is_data) {
            return $this->restore_data($file_path);
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Unknown backup type']);
        }
    }

    private function restore_db($file_path)
    {
        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $sql_content = '';

        if ($ext === 'zip') {
            if (!class_exists('ZipArchive')) {
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal: PHP Zip extension tidak tersedia', 'csrf_hash' => csrf_hash()]);
            }
            $zip = new ZipArchive;
            if ($zip->open($file_path) === TRUE) {
                $password = getenv('encryption.key') ?: '';
                if (!empty($password))
                    $zip->setPassword($password);

                $sql_content = $zip->getFromName('backup.sql');
                if (!$sql_content)
                    $sql_content = $zip->getFromIndex(0);

                $zip->close();

                if (!$sql_content) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Gagal membaca backup (Password salah?)', 'csrf_hash' => csrf_hash()]);
                }
            } else {
                return $this->response->setJSON(['status' => false, 'message' => 'Gagal membuka zip', 'csrf_hash' => csrf_hash()]);
            }
        } elseif ($ext === 'sql') {
            $sql_content = file_get_contents($file_path);
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Format file tidak didukung', 'csrf_hash' => csrf_hash()]);
        }

        $db = \Config\Database::connect();
        $mysqli = $db->connID;

        // Execute Multi Query
        // Note: CI4 query() doesn't support multi query well for import.
        // We use mysqli directly.

        if ($mysqli->multi_query("SET FOREIGN_KEY_CHECKS = 0; " . $sql_content . " SET FOREIGN_KEY_CHECKS = 1;")) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'DB Error: ' . $mysqli->error, 'csrf_hash' => csrf_hash()]);
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Database berhasil direstore', 'csrf_hash' => csrf_hash()]);
    }

    private function restore_data($file_path)
    {
        if (!class_exists('ZipArchive')) {
            return $this->response->setJSON(['status' => false, 'message' => 'PHP Zip extension not found', 'csrf_hash' => csrf_hash()]);
        }

        $zip = new ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            $password = getenv('encryption.key') ?: '';
            if (!empty($password))
                $zip->setPassword($password);

            if ($zip->extractTo('./')) {
                $zip->close();
                return $this->response->setJSON(['status' => true, 'message' => 'Data berhasil direstore', 'csrf_hash' => csrf_hash()]);
            }
            $zip->close();
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal extract (Password salah?)', 'csrf_hash' => csrf_hash()]);
        }
        return $this->response->setJSON(['status' => false, 'message' => 'Gagal membuka zip', 'csrf_hash' => csrf_hash()]);
    }

    private function _auto_delete_old_backups($days = 30)
    {
        $path = './backups/';
        if (is_dir($path)) {
            $files = directory_map($path);
            if ($files) {
                foreach ($files as $file) {
                    if (!is_string($file) || $file == 'index.html' || $file == '.htaccess')
                        continue;
                    $file_path = $path . $file;
                    if (file_exists($file_path) && filemtime($file_path) < (time() - ($days * 24 * 60 * 60))) {
                        @unlink($file_path);
                    }
                }
            }
        }
    }

    public function download($src)
    {
        if (!$this->isAdmin())
            return redirect()->back();
        $src = basename($src);
        $file = './backups/' . $src;
        if (file_exists($file)) {
            return $this->response->download($file, null);
        }
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
}