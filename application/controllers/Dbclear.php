<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dbclear extends MY_Controller
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
        $this->load->dbforge();
		$this->load->model('Settings_model', 'settings');
		$this->load->model('Dashboard_model', 'dashboard');
		$this->load->helper('directory');
	}

	public function index(){
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
        $excludes = ['buku_induk', 'bulan', 'hari', 'setting',
            'cbt_jenis', 'cbt_ruang', 'cbt_sesi', 'cbt_token',
            'level_guru', 'level_kelas',
            'master_tp', 'master_smt', 'master_hari_efektif',
            'users', 'groups', 'users_groups', 'login_attempts',
            'rapor_admin_setting', 'running_text', 'migrations', 'ci_sessions'];

        $data['group_cbt'] = [
            "cbt" => [
                "cbt_bank_soal",
                "cbt_soal",
                ],
            "jadwal" => [
                "cbt_jadwal",
                "cbt_pengawas",
                "cbt_siswa",
                "cbt_sesi_siswa",
                "cbt_nomor_peserta",
                "log_ujian",
            ],
            "administrasi" => [
                "cbt_kop_absensi",
                "cbt_kop_berita",
                "cbt_kop_kartu",
            ],
            "rekap" => [
                "cbt_rekap_jadwal",
                "cbt_rekap_siswa",
            ]
        ];

        $data['group_elearning'] = [
            "jadwal" => [
                "kelas_jadwal_kbm",
                "kelas_jadwal_mapel",
                "kelas_jadwal_materi",
                "kelas_materi",
                "log_materi",
            ],
            "walikelas" => [
                "kelas_catatan_mapel",
                "kelas_catatan_wali",
            ]
        ];

        $data['group_master'] = [
            "mapel" => [
                "master_mapel",
                "master_ekstra"
            ],
            "kelas" => [
                "master_jurusan",
                "master_kelas",
                "kelas_struktur",
                "kelas_ekstra",
            ],
            "guru" => [
                "master_guru",
                "jabatan_guru",
            ],
            "siswa" => [
                "master_siswa",
                "kelas_siswa",
            ]
        ];

        $data['group_post'] = [
            "running_text",
            "log",
            "post",
            "post_comments",
            "post_reply",
        ];

        $data['group_rapor'] = [
            "rapor_kkm",
            "rapor_kikd",
            "rapor_catatan_wali",
            "rapor_data_catatan",
            "rapor_data_fisik",
            "rapor_data_sikap",
            "rapor_fisik",
            "rapor_naik",
            "rapor_nilai_akhir",
            "rapor_nilai_ekstra",
            "rapor_nilai_harian",
            "rapor_nilai_pts",
            "rapor_nilai_sikap",
            "rapor_prestasi",
        ];

        $group_removed = [
            'cbt_durasi_siswa',
            'cbt_nilai',
            'cbt_rekap',
            'cbt_rekap_nilai',
            'cbt_soal_siswa',
            'users_profile'
        ];

        $data_tables = [];
        $table_removed = [];
        $tables = $this->db->list_tables();
        foreach ($tables as $table) {
            if (in_array($table, $group_removed)) {
                $table_removed[] = $table;
            }

            if (!in_array($table, $excludes)) {
                $name = str_replace("_", " ", $table ?? '');

                $table_info = [
                    'ket' => $this->keterangan()[$table] ?? "",
                    'size' => $this->settings->rowSize($table),
                    'table' => $table,
                    'name' => ucwords($name)
                ];
                $data_tables[$table_info['ket']][] = $table_info;
            }
        }

        $data['tables'] = $data_tables;
        $data['removed'] = $table_removed;

        $this->render_view(1, 'setting/manage', $data);
    }

    public function hapusTable() {
        $table = $this->input->post('table', true);
        $this->load->dbutil();

        $prefs = [
            'tables' => array($table),
            'ignore' => array(),           // List of tables to omit from the backup
            'format' => 'txt',             // gzip, zip, txt
            'filename' => $table.'.sql',    // File name - NEEDED ONLY WITH ZIP FILES
            'add_drop' => TRUE,              // Whether to add DROP TABLE statements to backup file
            'add_insert' => TRUE,              // Whether to add INSERT data to backup file
            'newline' => "\n"               // Newline character used in backup file
        ];

        $backup = $this->dbutil->backup(array($prefs));
        $this->load->helper('file');
        write_file('./backups/backup_' . $table . '_' . date("Y_m_d_H_i_s") . '.sql', $backup);
        $this->db->truncate($table);
        $this->output_json(['type' => 'database', 'message' => 'Database berhasil dihapus']);
    }

	public function truncate()
	{
		$tables = $this->db->list_tables();
		$this->settings->truncate($tables);

		$this->output_json(['status' => true]);
	}

	private function keterangan() {
	    // 0 = pre-app
	    // 1 = master
        // 2 = deletable
        // 3 = dropable
	    $data = [
            'buku_induk' => '1',
            'bulan' => '0',
            'cbt_bank_soal' => '2',
            'cbt_durasi_siswa' => '3',
            'cbt_jadwal' => '2',
            'cbt_jadwal_ujian' => '2',
            'cbt_jenis' => '0',
            'cbt_kop_absensi' => '1',
            'cbt_kop_berita' => '1',
            'cbt_kop_kartu' => '1',
            'cbt_nilai' => '3',
            'cbt_nomor_peserta' => '2',
            'cbt_pengawas' => '2',
            'cbt_rekap' => '3',
            'cbt_rekap_nilai' => '3',
            'cbt_rekap_jadwal' => '2',
            'cbt_rekap_siswa' => '2',
            'cbt_ruang' => '1',
            'cbt_sesi' => '1',
            'cbt_sesi_siswa' => '2',
            'cbt_soal' => '2',
            'cbt_siswa' => '2',
            'cbt_soal_siswa' => '3',
            'cbt_token' => '1',
            'groups' => '0',
            'hari' => '0',
            'jabatan_guru' => '1',
            'kelas_catatan_mapel' => '2',
            'kelas_catatan_wali' => '2',
            'kelas_ekstra' => '1',
            'kelas_jadwal_kbm' => '2',
            'kelas_jadwal_mapel' => '2',
            'kelas_jadwal_materi' => '2',
            'kelas_jadwal_tugas' => '2',
            'kelas_materi' => '2',
            'kelas_siswa' => '2',
            'kelas_struktur' => '2',
            'kelas_tugas' => '2',
            "kelas_libraries" => "2",
            'level_guru' => '0',
            'level_kelas' => '0',
            'log' => '2',
            'login_attempts' => '0',
            'log_materi' => '2',
            'log_tugas' => '2',
            'log_ujian' => '2',
            'master_ekstra' => '1',
            'master_guru' => '1',
            'master_hari_efektif' => '1',
            'master_jurusan' => '1',
            'master_kelas' => '1',
            'master_kelompok_mapel' => '1',
            'master_mapel' => '1',
            'master_siswa' => '1',
            'master_smt' => '0',
            'master_tp' => '0',
            'post' => '2',
            'post_comments' => '2',
            'post_reply' => '2',
            'rapor_admin_setting' => '1',
            'rapor_catatan_wali' => '1',
            'rapor_data_catatan' => '1',
            'rapor_data_fisik' => '1',
            'rapor_data_sikap' => '1',
            'rapor_fisik' => '1',
            'rapor_kikd' => '1',
            'rapor_kkm' => '1',
            'rapor_naik' => '1',
            'rapor_nilai_akhir' => '1',
            'rapor_nilai_ekstra' => '1',
            'rapor_nilai_harian' => '1',
            'rapor_nilai_pts' => '1',
            'rapor_nilai_sikap' => '1',
            'rapor_prestasi' => '1',
            'running_text' => '1',
            'setting' => '1',
            'users' => '0',
            'users_groups' => '0'
        ];
	    return $data;
    }
}
