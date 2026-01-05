<?php

namespace App\Controllers;

use App\Models\AuthModel;
use App\Models\DashboardModel;
use App\Models\SettingsModel;

/**
 * Install Controller - Migrated from CI3
 * Handles initial application setup and installation
 */
class Install extends BaseController
{
    protected DashboardModel $dashboardModel;
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Main installation page
     */
    public function index()
    {
        $res = $this->checkInstaller();

        if ($res === 0) {
            return redirect()->to('dashboard');
        }

        $data = $this->getSaved();
        $data->error = $res;
        $data->msg = $this->getErrorMessage($res);

        return view('install/index', ['data' => $data]);
    }

    /**
     * Check installation status
     * @return int 0=complete, 1=no db, 2=tables missing, 3=no admin, 4=no setting
     */
    private function checkInstaller(): int
    {
        helper('init');

        // Check database connection
        if (!function_exists('database_connected') || !database_connected()) {
            return 1;
        }

        // Check if database is empty
        if (is_database_empty()) {
            return 2;
        }

        // Check admin user
        $db = \Config\Database::connect();
        if ($db->tableExists('users')) {
            $admin = $db->table('users')
                ->join('users_groups', 'users.id = users_groups.user_id')
                ->where('users_groups.group_id', 1)
                ->get()->getRow();
            if (!$admin) {
                return 3;
            }
        } else {
            return 2;
        }

        // Check settings
        if (is_setting_empty()) {
            return 4;
        }

        return 0;
    }

    /**
     * Get error message by code
     */
    private function getErrorMessage(int $code): string
    {
        $messages = [
            1 => 'Koneksi database gagal',
            2 => 'Sebagian tabel belum dibuat',
            3 => 'Belum ada administrator',
            4 => 'Belum ada data sekolah'
        ];
        return $messages[$code] ?? 'Unknown error';
    }

    /**
     * Get saved installation data
     */
    private function getSaved(): object
    {
        $data = [
            'hostname' => env('database.default.hostname', 'localhost'),
            'username' => env('database.default.username', 'root'),
            'password' => env('database.default.password', ''),
            'database' => env('database.default.database', ''),
            'nama_admin' => '',
            'user_admin' => '',
            'pass_admin' => '',
            'aplikasi' => '',
            'sekolah' => '',
            'jenjang' => '',
            'satuan' => '',
            'kepsek' => '',
            'alamat' => '',
            'desa' => '',
            'kec' => '',
            'kota' => '',
            'prov' => '',
            'current_page' => 1
        ];

        $db = \Config\Database::connect();

        try {
            if ($db->tableExists('users')) {
                $admin = $db->table('users')->get()->getRow();
                if ($admin) {
                    $data['nama_admin'] = ($admin->first_name ?? '') . ' ' . ($admin->last_name ?? '');
                    $data['user_admin'] = $admin->username ?? '';
                    $data['pass_admin'] = $admin->password ?? '';
                    $data['current_page'] = 2;
                }

                $setting = $this->settingsModel->getSetting();
                if ($setting) {
                    $data['aplikasi'] = $setting->nama_aplikasi ?? '';
                    $data['sekolah'] = $setting->sekolah ?? '';
                    $data['jenjang'] = $setting->jenjang ?? '';
                    $data['satuan'] = $setting->satuan_pendidikan ?? '';
                    $data['kepsek'] = $setting->kepsek ?? '';
                    $data['alamat'] = $setting->alamat ?? '';
                    $data['desa'] = $setting->desa ?? '';
                    $data['kec'] = $setting->kecamatan ?? '';
                    $data['kota'] = $setting->kota ?? '';
                    $data['prov'] = $setting->provinsi ?? '';
                    $data['current_page'] = $admin ? 3 : 2;
                }
            }
        } catch (\Exception $e) {
            // Database not ready
        }

        return (object) $data;
    }

    /**
     * Steps page
     */
    public function steps()
    {
        $data = $this->getSaved();
        return view('install/index', ['data' => $data]);
    }

    /**
     * Check and create database connection
     */
    public function checkDatabase()
    {
        $hostname = $this->request->getPost('hostname');
        $hostuser = $this->request->getPost('hostuser');
        $hostpass = $this->request->getPost('hostpass');
        $database = $this->request->getPost('database');

        if (!$this->validateHost($hostname, $hostuser, $database)) {
            return $this->respond(false, 'Tidak boleh ada yang kosong');
        }

        // Update .env file
        $envUpdated = $this->updateEnvFile($hostname, $hostuser, $hostpass, $database);

        if (!$envUpdated) {
            return $this->respond(false, 'Tidak ada akses untuk update konfigurasi database');
        }

        // Create database if not exists
        $dbCreated = $this->createDatabase($hostname, $hostuser, $hostpass, $database);

        // Create tables
        $tablesCreated = $this->createTables($hostname, $hostuser, $hostpass, $database);

        return $this->respond(true, 'Database berhasil dikonfigurasi', [
            'host' => true,
            'database' => $dbCreated,
            'table' => $tablesCreated
        ]);
    }

    /**
     * Create database tables
     */
    public function createDb()
    {
        $page = $this->request->getPost('page');

        if ($page !== '0') {
            return $this->respond(false, 'Step salah');
        }

        $hostname = $this->request->getPost('hostname');
        $hostuser = $this->request->getPost('hostuser');
        $hostpass = $this->request->getPost('hostpass');
        $database = $this->request->getPost('database');

        $tableCreated = $this->createTables($hostname, $hostuser, $hostpass, $database);

        return $this->respond($tableCreated, $tableCreated ? 'Tabel berhasil dibuat' : 'Gagal membuat tabel', [
            'table' => $tableCreated,
            'database' => true
        ]);
    }

    /**
     * Create admin user
     */
    public function createAdmin()
    {
        $nama = $this->request->getPost('nama_lengkap');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($nama) || empty($username) || empty($password)) {
            return $this->respond(false, 'Semua field harus diisi');
        }

        $authModel = new AuthModel();

        $namaAdmin = explode(' ', $nama);
        $first_name = $namaAdmin[0];
        $last_name = count($namaAdmin) > 1 ? end($namaAdmin) : '';
        $email = strtolower(str_replace(' ', '', $nama)) . '@admin.com';

        $user = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        ];

        try {
            $created = $authModel->register($user, '1');
            return $this->respond(true, 'Admin berhasil dibuat', ['admin' => $created]);
        } catch (\Exception $e) {
            return $this->respond(false, 'Gagal membuat admin: ' . $e->getMessage());
        }
    }

    /**
     * Create school settings
     */
    public function createSetting()
    {
        $insert = [
            'id_setting' => 1,
            'sekolah' => $this->request->getPost('nama_sekolah'),
            'jenjang' => $this->request->getPost('jenjang'),
            'satuan_pendidikan' => $this->request->getPost('satuan_pendidikan'),
            'alamat' => $this->request->getPost('alamat'),
            'desa' => $this->request->getPost('desa'),
            'kota' => $this->request->getPost('kota'),
            'kecamatan' => $this->request->getPost('kec'),
            'telp' => $this->request->getPost('tlp'),
            'kepsek' => $this->request->getPost('kepsek'),
            'nama_aplikasi' => $this->request->getPost('nama_aplikasi'),
        ];

        $db = \Config\Database::connect();

        try {
            $db->table('setting')->insert($insert);
            return $this->respond(true, 'Setting berhasil disimpan', ['saved' => $this->getSaved()]);
        } catch (\Exception $e) {
            return $this->respond(false, 'Gagal menyimpan setting: ' . $e->getMessage());
        }
    }

    /**
     * Create app (combined admin + setting)
     */
    public function createApp()
    {
        // Create admin
        $nama = $this->request->getPost('nama_lengkap');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $authModel = new AuthModel();

        $namaAdmin = explode(' ', $nama ?? '');
        $first_name = $namaAdmin[0] ?? '';
        $last_name = count($namaAdmin) > 1 ? end($namaAdmin) : '';
        $email = strtolower(str_replace(' ', '', $nama ?? '')) . '@admin.com';

        $user = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        ];

        $adminCreated = $authModel->register($user, '1');

        // Create setting
        $insert = [
            'id_setting' => 1,
            'sekolah' => $this->request->getPost('nama_sekolah'),
            'jenjang' => $this->request->getPost('jenjang'),
            'satuan_pendidikan' => $this->request->getPost('satuan'),
            'alamat' => $this->request->getPost('alamat'),
            'desa' => $this->request->getPost('desa'),
            'kota' => $this->request->getPost('kota'),
            'kecamatan' => $this->request->getPost('kec'),
            'provinsi' => $this->request->getPost('prov'),
            'kepsek' => $this->request->getPost('kepsek'),
            'nama_aplikasi' => $this->request->getPost('nama_aplikasi'),
        ];

        $db = \Config\Database::connect();
        $settingInserted = $db->table('setting')->insert($insert);

        return $this->respond(
            $adminCreated && $settingInserted,
            ($adminCreated && $settingInserted) ? 'Aplikasi berhasil dikonfigurasi' : 'Terjadi kesalahan',
            ['admin' => $adminCreated, 'insert' => $settingInserted]
        );
    }

    /**
     * Validate host parameters
     */
    private function validateHost(?string $host, ?string $usr, ?string $db): bool
    {
        return !empty($host) && !empty($usr) && !empty($db);
    }

    /**
     * Update .env file with database config
     */
    private function updateEnvFile(string $hostname, string $username, string $password, string $database): bool
    {
        $envPath = ROOTPATH . '.env';

        if (!is_writable($envPath)) {
            return false;
        }

        try {
            $content = file_get_contents($envPath);

            // Update database settings
            $content = preg_replace('/database\.default\.hostname\s*=.*/', "database.default.hostname = $hostname", $content);
            $content = preg_replace('/database\.default\.database\s*=.*/', "database.default.database = $database", $content);
            $content = preg_replace('/database\.default\.username\s*=.*/', "database.default.username = $username", $content);
            $content = preg_replace('/database\.default\.password\s*=.*/', "database.default.password = '$password'", $content);

            file_put_contents($envPath, $content);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create database if not exists
     */
    private function createDatabase(string $hostname, string $hostuser, string $hostpass, string $database): bool
    {
        try {
            $mysqli = new \mysqli($hostname, $hostuser, $hostpass, '');
            if ($mysqli->connect_errno) {
                return false;
            }
            $mysqli->query("CREATE DATABASE IF NOT EXISTS `$database`");
            $mysqli->close();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create database tables from SQL file
     */
    private function createTables(string $hostname, string $hostuser, string $hostpass, string $database): bool
    {
        try {
            $mysqli = new \mysqli($hostname, $hostuser, $hostpass, $database);
            if ($mysqli->connect_errno) {
                return false;
            }

            $sqlPath = FCPATH . 'assets/app/db/master.sql';
            if (!file_exists($sqlPath)) {
                return false;
            }

            $query = file_get_contents($sqlPath);
            $mysqli->multi_query($query);

            // Flush results
            while ($mysqli->next_result()) {
                if ($res = $mysqli->store_result()) {
                    $res->free();
                }
            }

            $mysqli->close();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
