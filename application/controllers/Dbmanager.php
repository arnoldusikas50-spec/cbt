<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dbmanager extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->auth->logged_in()) {
            redirect('auth');
        } else if (!$this->auth->is_admin()) {
            show_error('Hanya Admin yang boleh mengakses halaman ini', 403, 'Akses dilarang');
        }
        $this->load->library('upload');
        $this->load->model('Settings_model', 'settings');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->helper('directory');
    }

    public function index()
    {
        // Auto delete old backups (older than 30 days)
        $this->_auto_delete_old_backups();

        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Backup',
            'subjudul' => 'Backup Database',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        $list = directory_map('./backups/');
        $arrFile = [];
        foreach ($list as $key => $value) {
            // Skip if value is an array (subdirectory) or not a string
            if (!is_string($value)) {
                continue;
            }

            if ($value == 'index.html' || $value == '.htaccess')
                continue;

            $nfile = explode('.', $value);
            $nama = $nfile[0] ?? '';
            $type = $nfile[1] ?? '';
            $tgl = filemtime('./backups/' . $value);
            $size = $this->formatSizeUnits(filesize('./backups/' . $value));

            if ($type !== 'html' && $type !== '') {
                $arrFile[$key] = ['type' => $type, 'nama' => $nama, 'tgl' => $tgl, 'size' => $size, 'src' => $value];
            }
            //array_push([$key=>$file]);
        }

        $data['list'] = $arrFile;
        $data['tables'] = $this->db->list_tables();

        $this->render_view(1, 'setting/db', $data);
    }

    public function manage()
    {
        $user = $this->auth->user();
        $data = [
            'user' => $user,
            'judul' => 'Bersihkan Data',
            'subjudul' => 'Hapus Data',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        $data_tables = [];
        $tables = $this->db->list_tables();
        foreach ($tables as $table) {
            $data_tables[$table] = $this->settings->toJSON($table);
        }
        $data['tables'] = $data_tables;
        $this->render_view(1, 'setting/manage', $data);
    }

    public function truncate()
    {
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }
        $tables = $this->db->list_tables();
        $this->settings->truncate($tables);

        $this->output_json(['status' => true, 'csrf_hash' => $this->security->get_csrf_hash()]);
    }

    public function backupDb()
    {
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }

        $this->load->dbutil();
        $this->dbutil->optimize_database();

        $prefs = [
            'tables' => $this->db->list_tables(),
            'ignore' => array(),
            'format' => 'txt',             // Changed to txt to handle manual zipping
            'filename' => 'backup.sql',
            'add_drop' => TRUE,
            'add_insert' => TRUE,
            'newline' => "\n"
        ];

        $sql_content = $this->dbutil->backup($prefs);
        $filename = 'backup-db-' . date("Y-m-d-H-i-s") . '.sql.zip';
        $save_path = './backups/' . $filename;

        $zip = new ZipArchive;
        if ($zip->open($save_path, ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('backup.sql', $sql_content);

            // Apply Encryption
            $password = $this->config->item('encryption_key');
            if (!empty($password)) {
                // Try AES-256, fallback to PKWARE if not available
                $encryption = defined('ZipArchive::EM_AES_256') ? ZipArchive::EM_AES_256 : ZipArchive::EM_TRAD_PKWARE;
                $zip->setEncryptionName('backup.sql', $encryption, $password);
            }

            $zip->close();

            $this->output_json([
                'type' => 'database',
                'message' => 'Database berhasil dibackup (Encrypted)',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        } else {
            $this->output_json([
                'type' => 'database',
                'message' => 'Gagal membuat file backup zip',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        }
    }

    public function backupData()
    {
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }

        $filename = 'backup-file-' . date("Y-m-d-H-i-s") . '.zip';
        $save_path = './backups/' . $filename;
        $source_path = './uploads/';

        $zip = new ZipArchive;
        if ($zip->open($save_path, ZipArchive::CREATE) === TRUE) {
            $password = $this->config->item('encryption_key');
            $this->_zip_folder($source_path, $zip, '', $password);
            $zip->close();

            $this->output_json([
                'type' => 'file',
                'message' => 'File data berhasil dibackup (Encrypted)',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        } else {
            $this->output_json([
                'type' => 'file',
                'message' => 'Gagal membuat file backup zip',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        }
    }

    // Helper for recursive encrypt zip
    private function _zip_folder($folder, &$zipFile, $folderInZip = '', $password = '')
    {
        $handle = opendir($folder);
        if ($handle) {
            while (false !== ($f = readdir($handle))) {
                if ($f != '.' && $f != '..') {
                    $filePath = "$folder/$f";
                    $localPath = "$folderInZip$f";
                    if (is_file($filePath)) {
                        $zipFile->addFile($filePath, $localPath);
                        if (!empty($password)) {
                            $encryption = defined('ZipArchive::EM_AES_256') ? ZipArchive::EM_AES_256 : ZipArchive::EM_TRAD_PKWARE;
                            $zipFile->setEncryptionName($localPath, $encryption, $password);
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

    public function hapusBackup($src = null)
    {
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }

        if ($src === null) {
            $src = $this->input->post('src', true);
        }

        $src = basename($src);

        if (unlink('./backups/' . $src)) {
            $this->output_json([
                'status' => true,
                'message' => 'Backup berhasil dihapus',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal menghapus backup',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        }
    }

    public function del_backup()
    {
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }
        $list = directory_map('./backups/');
        foreach ($list as $value) {
            if ($value != 'index.html' && $value != '.htaccess') {
                unlink('./backups/' . $value);
            }
        }
        $this->output_json([
            'status' => true,
            'list' => $list,
            'csrf_hash' => $this->security->get_csrf_hash()
        ]);
    }

    public function restore($src = null)
    {
        if ($this->input->method() !== 'post') {
            show_error('Method not allowed', 405);
        }

        $this->load->helper('file');

        if ($src === null) {
            $src = $this->input->post('src', true);
        }

        $src = basename($src);

        $file_path = './backups/' . $src;
        if (!file_exists($file_path)) {
            $this->output_json([
                'status' => false,
                'message' => 'File tidak ditemukan',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
            return;
        }

        $is_db = strpos($src, 'backup-db') !== false;
        $is_data = strpos($src, 'backup-file') !== false;

        if ($is_db) {
            $this->restore_db($file_path);
        } elseif ($is_data) {
            $this->restore_data($file_path);
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Tipe backup tidak dikenali',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        }
    }

    private function restore_db($file_path)
    {
        $zip = new ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            $password = $this->config->item('encryption_key');
            if (!empty($password)) {
                $zip->setPassword($password);
            }

            // Assume the first file in zip is the sql file
            $stat = $zip->statIndex(0);
            $sql_content = $zip->getFromIndex(0);

            // If encrypted + wrong password, getFromIndex might return false or garbage.
            // ZipArchive doesn't always strictly fail open if password is wrong, 
            // but reading logic will fail.

            if (!$sql_content) {
                // Try again explicitly with index name if possible, but index 0 is safest
                $zip->close();
                $this->output_json([
                    'status' => false,
                    'message' => 'Gagal membaca isi backup (Mungkin Password Salah/File Corrupt)',
                    'csrf_hash' => $this->security->get_csrf_hash()
                ]);
                return;
            }

            $zip->close();

            $mysqli = $this->db->conn_id;
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

            if ($mysqli->multi_query($sql_content)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            } else {
                $this->output_json([
                    'status' => false,
                    'message' => 'Gagal mengeksekusi restore: ' . $mysqli->error,
                    'csrf_hash' => $this->security->get_csrf_hash()
                ]);
                return;
            }

            $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

            $this->output_json([
                'status' => true,
                'message' => 'Database berhasil direstore.',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);

        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal membuka file backup',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        }
    }

    private function restore_data($file_path)
    {
        $zip = new ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            $password = $this->config->item('encryption_key');
            if (!empty($password)) {
                $zip->setPassword($password);
            }

            if ($zip->extractTo('./')) {
                $zip->close();
                $this->output_json([
                    'status' => true,
                    'message' => 'File Uploads berhasil direstore',
                    'csrf_hash' => $this->security->get_csrf_hash()
                ]);
            } else {
                $zip->close();
                $this->output_json([
                    'status' => false,
                    'message' => 'Gagal mengekstrak file (Password Salah?)',
                    'csrf_hash' => $this->security->get_csrf_hash()
                ]);
            }
        } else {
            $this->output_json([
                'status' => false,
                'message' => 'Gagal membuka file backup',
                'csrf_hash' => $this->security->get_csrf_hash()
            ]);
        }
    }

    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    private function _auto_delete_old_backups($days = 30)
    {
        $path = './backups/';
        if (is_dir($path)) {
            if ($handle = opendir($path)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != 'index.html' && $file != '.htaccess') {
                        $file_path = $path . $file;
                        if (filemtime($file_path) < (time() - ($days * 24 * 60 * 60))) {
                            @unlink($file_path);
                        }
                    }
                }
                closedir($handle);
            }
        }
    }

    public function download($src)
    {
        if (!$this->auth->is_admin()) {
            show_error('Akses dilarang', 403);
        }

        $src = basename($src);
        $file = './backups/' . $src;

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            show_404();
        }
    }
}
