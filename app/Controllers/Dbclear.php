<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use App\Models\LogModel;
use App\Models\SettingsModel;

class Dbclear extends BaseController
{
    protected $dashboard;
    protected $settings;

    /**
     * Tabel yang TIDAK BOLEH dikosongkan (sistem, autentikasi, master data,
     * kurikulum, kartu soal, antrian scoring, TKA, rapor, KBM).
     * Ditegakkan di sisi server pada hapusTable(), bukan hanya disembunyikan di UI.
     */
    protected $protectedTables = [
        // Autentikasi & sistem
        'users', 'groups', 'users_groups', 'users_profile', 'login_attempts',
        'ci_sessions', 'migrations', 'setting', 'running_text',
        // Master data
        'buku_induk', 'bulan', 'hari', 'level_guru', 'level_kelas', 'jabatan_guru',
        'master_guru', 'master_siswa', 'master_kelas', 'master_mapel',
        'master_jurusan', 'master_panitia', 'master_kelompok_mapel', 'master_ekstra',
        'master_tp', 'master_smt', 'master_hari_efektif',
        // Konfigurasi CBT
        'cbt_jenis', 'cbt_ruang', 'cbt_sesi', 'cbt_token', 'cbt_kelas_ruang',
        // Kurikulum & kartu soal
        'e_capaian_pembelajaran', 'e_tujuan_pembelajaran',
        'cbt_kartu_soal', 'cbt_kartu_soal_item', 'cbt_kartu_soal_audit',
        // Scoring & AI grading (antrian scoring krusial: jangan dihapus)
        'cbt_scoring_queue', 'cbt_ai_grading_history', 'cbt_nilai',
        // Modul TKA
        'tka_paket', 'tka_subtes', 'tka_sesi_siswa', 'tka_subtes_sesi', 'tka_nilai',
        // Rapor & nilai kurikulum
        'rapor_admin_setting', 'rapor_catatan_wali', 'rapor_data_catatan',
        'rapor_data_fisik', 'rapor_data_sikap', 'rapor_fisik', 'rapor_kikd',
        'rapor_kkm', 'rapor_naik', 'rapor_nilai_akhir', 'rapor_nilai_ekstra',
        'rapor_nilai_harian', 'rapor_nilai_pts', 'rapor_nilai_sikap', 'rapor_prestasi',
        'nilai_akhir_kurmer', 'nilai_proses', 'nilai_sts', 'nilai_sync_log',
        // KBM aktif
        'guru_jurnal', 'log_materi', 'kelas_catatan_mapel', 'kelas_catatan_wali',
        'kelas_ekstra', 'kelas_jadwal_kbm', 'kelas_jadwal_mapel', 'kelas_jadwal_materi',
        'kelas_libraries', 'kelas_materi', 'kelas_siswa', 'kelas_struktur',
        // Praktik: konfigurasi & asesmen (bukan data transaksional hasil)
        'cbt_praktik', 'cbt_praktik_analisis', 'cbt_praktik_multi_assessor',
        'cbt_praktik_p5', 'cbt_praktik_p5_nilai', 'cbt_praktik_penilaian_berkelanjutan',
        'cbt_praktik_penilaian_otomatis', 'cbt_praktik_portofolio',
        'cbt_praktik_presentasi', 'cbt_praktik_remedial', 'cbt_praktik_rubrik',
        'cbt_praktik_rubrik_level', 'cbt_praktik_self_assessment',
        'cbt_praktik_template_rubrik', 'cbt_praktik_template_rubrik_item',
        // Sinkronisasi & API
        'sync_config', 'sync_servers', 'sync_log', 'api_setting', 'api_token',
        'dapodik_settings',
    ];

    /**
     * Tabel yang BOLEH dikosongkan, dikelompokkan.
     * Hanya tabel dalam daftar ini yang bisa di-truncate oleh hapusTable().
     */
    protected $deletableGroups = [
        'hasil' => [
            'cbt_jawaban', 'cbt_durasi_siswa', 'cbt_sesi_siswa', 'cbt_soal_siswa',
            'cbt_rekap', 'cbt_rekap_jadwal', 'cbt_rekap_siswa', 'cbt_siswa',
            'log_ujian', 'logs', 'activity_log', 'dapodik_log',
            'cbt_praktik_log', 'cbt_praktik_hasil', 'cbt_praktik_observasi',
            'cbt_praktik_aktivitas_siswa', 'cbt_praktik_umpan_balik',
            'cbt_praktik_nilai_audit', 'cbt_praktik_file',
        ],
        'ujian' => [
            'cbt_jadwal', 'cbt_pengawas', 'cbt_berita_acara', 'cbt_pakta_integritas',
            'cbt_nomor_peserta', 'cbt_kop_absensi', 'cbt_kop_berita', 'cbt_kop_kartu',
        ],
        'soal' => [
            'cbt_bank_soal', 'cbt_soal',
        ],
        'lainnya' => [
            'post', 'post_comments', 'post_likes', 'post_reply',
            'system_update_log', 'system_updates', 'cbt_laporan',
        ],
    ];

    public function __construct()
    {
        $this->dashboard = new DashboardModel();
        $this->settings = new SettingsModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn())
            return redirect()->to('auth');
        if (!$this->isAdmin())
            return redirect()->to('dashboard');

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'Bersihkan Data',
            'subjudul' => 'Hapus Data',
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive()
        ];

        $db = \Config\Database::connect();
        $actualTables = array_flip($db->listTables());

        $data_tables = [];
        foreach ($this->deletableGroups as $ktg => $list) {
            foreach ($list as $table) {
                if (!isset($actualTables[$table]))
                    continue;
                $data_tables[$ktg][] = [
                    'size' => $this->settings->rowSize($table),
                    'table' => $table,
                    'name' => ucwords(str_replace("_", " ", $table))
                ];
            }
        }

        $protected = [];
        foreach ($this->protectedTables as $table) {
            if (!isset($actualTables[$table]))
                continue;
            $protected[] = [
                'size' => $this->settings->rowSize($table),
                'table' => $table,
                'name' => ucwords(str_replace("_", " ", $table))
            ];
        }
        // Urutkan alfabetis agar mudah dipindai
        usort($protected, fn($a, $b) => strcmp($a['table'], $b['table']));

        $data['tables'] = $data_tables;
        $data['protected'] = $protected;
        $data['removed'] = [];

        return view('setting/manage', $data);
    }

    public function hapusTable()
    {
        if (!$this->isAdmin())
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak']);

        $table = trim($this->request->getPost('table') ?? '');
        if (empty($table))
            return $this->response->setJSON(['status' => false, 'message' => 'Nama tabel wajib diisi']);

        $allowed = [];
        foreach ($this->deletableGroups as $list)
            $allowed = array_merge($allowed, $list);
        $allowed = array_flip($allowed);

        // Pintu pengaman utama: hanya tabel whitelist yang boleh dikosongkan
        if (!isset($allowed[$table])) {
            log_message('warning', '[Dbclear::hapusTable] Percobaan truncate tabel terlarang: ' . $table);
            return $this->response->setJSON(['status' => false, 'message' => 'Tabel tidak diizinkan untuk dikosongkan']);
        }

        $db = \Config\Database::connect();
        if (!in_array($table, $db->listTables()))
            return $this->response->setJSON(['status' => false, 'message' => 'Tabel tidak ditemukan']);

        try {
            $db->query('SET FOREIGN_KEY_CHECKS=0');
            $db->table($table)->truncate();
            $db->query('SET FOREIGN_KEY_CHECKS=1');

            (new LogModel())->saveLog(3, 'Mengosongkan tabel: ' . $table);

            return $this->response->setJSON(['type' => 'database', 'status' => true, 'message' => 'Tabel berhasil dikosongkan']);
        } catch (\Throwable $e) {
            $db->query('SET FOREIGN_KEY_CHECKS=1');
            log_message('error', '[Dbclear::hapusTable] Gagal truncate ' . $table . ': ' . $e->getMessage());
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal mengosongkan tabel: ' . $e->getMessage()]);
        }
    }
}
