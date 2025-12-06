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
        $user = $this->auth->user();
        $data = [
            'user' 			=> $user,
			'judul' => 'Backup',
			'subjudul' => 'Backup Database',
            'profile'		=> null,
            'setting'		=> $this->dashboard->getSetting()
		];

		$data['tp'] = $this->dashboard->getTahun();
		$data['tp_active'] = $this->dashboard->getTahunActive();
		$data['smt'] = $this->dashboard->getSemester();
		$data['smt_active'] = $this->dashboard->getSemesterActive();

		$list = directory_map('./backups/');
		$arrFile = [];
		foreach ($list as $key => $value) {
            $nfile = explode('.', $value ?? '');
            $nama = $nfile[0];
            $type = $nfile[1];
            $tgl = filemtime('./backups/'.$value);
            $size = $this->formatSizeUnits(filesize('./backups/'.$value));

            if ($type !== 'html') {
                $arrFile[$key] = ['type' => $type, 'nama' => $nama, 'tgl' => $tgl, 'size' => $size, 'src'=>$value];
            }
			//array_push([$key=>$file]);
		}

		$data['list'] = $arrFile;
        $data['tables'] = $this->db->list_tables();

		$this->render_view(1, 'setting/db', $data);
	}

	public function manage() {
        $user = $this->auth->user();
        $data = [
            'user' 			=> $user,
            'judul' => 'Bersihkan Data',
            'subjudul' => 'Hapus Data',
            'profile'		=> null,
            'setting'		=> $this->dashboard->getSetting()
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
		$tables = $this->db->list_tables();
		$this->settings->truncate($tables);

		$this->output_json(['status' => true]);
	}

	public function backupDb()
	{
		$this->load->dbutil();
        $this->dbutil->optimize_database();

        $prefs = [
            'tables' => $this->db->list_tables(),
            'ignore' => array(),           // List of tables to omit from the backup
            'format' => 'zip',             // gzip, zip, txt
            'filename' => 'backup.sql',    // File name - NEEDED ONLY WITH ZIP FILES
            'add_drop' => TRUE,              // Whether to add DROP TABLE statements to backup file
            'add_insert' => TRUE,              // Whether to add INSERT data to backup file
            'newline' => "\n"               // Newline character used in backup file
        ];

        $backup = $this->dbutil->backup($prefs);
        $this->load->helper('file');
        write_file('./backups/backup-db-' . date("Y-m-d-H-i-s") . '.sql.zip', $backup);
		$this->output_json(['type' => 'database', 'message' => 'Database berhasil dibackup']);

	}

	public function backupData()
	{
		$this->load->library('zip');
		$this->zip->read_dir('uploads');
		//$this->zip->download('my_backup.zip');
		$this->zip->archive('./backups/backup-file-' . date("Y-m-d-H-i-s") . '.zip');

		$this->output_json(['type' => 'file', 'message' => 'File data berhasil dibackup']);
	}

	public function hapusBackup($src)
	{
		if (unlink('./backups/' . $src)) {
			$this->output_json(['status' => true, 'message' => 'Backup berhasil dihapus']);
		} else {
			$this->output_json(['status' => false, 'message' => 'Gagal menghapus backup']);
		}
	}

    public function del_backup()
    {
        $list = directory_map('./backups/');
        foreach ($list as $value) {
            if (unlink('./backups/' . $value)) {
                $this->output_json(['status' => true, 'message' => 'Backup berhasil dihapus']);
            } else {
                $this->output_json(['status' => false, 'message' => 'Gagal menghapus backup']);
            }
        }
        $this->output_json(['status' => true, 'list'=>$list]);
    }

    public function restore($src)
    {
        $this->load->helper('file');
        
        $file_path = './backups/' . $src;
        if (!file_exists($file_path)) {
            $this->output_json(['status' => false, 'message' => 'File tidak ditemukan']);
            return;
        }

        // Detect type based on filename prefix defined in backup methods
        // backupDb uses: backup-db-...
        // backupData uses: backup-file-...
        $is_db = strpos($src, 'backup-db') !== false;
        $is_data = strpos($src, 'backup-file') !== false;

        if ($is_db) {
            $this->restore_db($file_path);
        } elseif ($is_data) {
            $this->restore_data($file_path);
        } else {
             $this->output_json(['status' => false, 'message' => 'Tipe backup tidak dikenali']);
        }
    }

    private function restore_db($file_path) {
        $zip = new ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            // Assume the first file in zip is the sql file
            $sql_content = $zip->getFromIndex(0);
            $zip->close();
            
            if (!$sql_content) {
                $this->output_json(['status' => false, 'message' => 'Gagal membaca isi backup database']);
                return;
            }
            
            // Execute SQL using mysqli multi_query for better performance and robust parsing
            $mysqli = $this->db->conn_id;
            
            // Disable FK checks to prevent errors during drop/create
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
            
            if ($mysqli->multi_query($sql_content)) {
                do {
                    // Consume all results to clear the stack
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            } else {
                 $this->output_json(['status' => false, 'message' => 'Gagal mengeksekusi restore: ' . $mysqli->error]);
                 return;
            }
            
            $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
             
            $this->output_json(['status' => true, 'message' => 'Database berhasil direstore. Silakan relogin jika session terputus.']);

        } else {
             $this->output_json(['status' => false, 'message' => 'Gagal membuka file backup']);
        }
    }

    private function restore_data($file_path) {
        $zip = new ZipArchive;
        if ($zip->open($file_path) === TRUE) {
            // Extract to root directory (because zip was created with 'uploads/' structure)
            if ($zip->extractTo('./')) {
                $zip->close();
                $this->output_json(['status' => true, 'message' => 'File Uploads berhasil direstore']);
            } else {
                 $zip->close();
                 $this->output_json(['status' => false, 'message' => 'Gagal mengekstrak file']);
            }
        } else {
            $this->output_json(['status' => false, 'message' => 'Gagal membuka file backup']);
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
}
