<?php

namespace App\Controllers;

use App\Models\SettingsModel;
use App\Models\DashboardModel;
use App\Services\MaintenanceService;
use CodeIgniter\Files\File;

class Settings extends BaseController
{
    protected $settings;
    protected $dashboard;

    public function __construct()
    {
        $this->settings = new SettingsModel();
        $this->dashboard = new DashboardModel();
        helper(['form', 'filesystem', 'text', 'number']);
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('auth');
        }
        if (!$this->isAdmin()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $user = $this->getUser();
        $data = [
            'user' => $user,
            'judul' => 'Profile Sekolah',
            'subjudul' => 'Pengaturan Umum',
            'profile' => null,
            'setting' => $this->dashboard->getSetting()
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();

        return view('setting/data', $data);
    }

    public function dbManager()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return redirect()->to('auth');
        }

        $data = [
            'user' => $this->getUser(),
            'judul' => 'Backup dan Restore',
            'subjudul' => 'Backup dan Restore',
        ];

        $data['tp'] = $this->dashboard->getTahun();
        $data['tp_active'] = $this->dashboard->getTahunActive();
        $data['smt'] = $this->dashboard->getSemester();
        $data['smt_active'] = $this->dashboard->getSemesterActive();
        $data['setting'] = $this->settings->getSetting();
        // Assuming backups are stored in writable/backups or public/backups. 
        // CI3 used './backups/'. I'll map to ROOTPATH . 'backups/' or keep it aligned if folder exists.
        // For now, let's use a safe path if directory_map is allowed.
        // If directory doesn't exist, directory_map returns empty array.
        $backupPath = ROOTPATH . 'public/backups/';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0777, true);
        }
        $data['list'] = directory_map($backupPath);

        return view('setting/db', $data);
    }

    public function uploadFile($logo)
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Forbidden', null, null, 403);
        }

        $allowedFields = [
            'logo_kanan', 'logo_kiri', 'stampel', 'tanda_tangan',
            'ttdproktor', 'kodescan', 'kopsekolah',
            'banner1', 'banner2', 'banner3'
        ];

        if (!in_array($logo, $allowedFields, true)) {
            return $this->respond(false, 'Invalid field', null, ['field' => 'Unknown field'], 400);
        }

        $file = $this->request->getFile('logo');
        $validationRule = [
            'logo' => [
                'label' => 'Image File',
                'rules' => 'uploaded[logo]|is_image[logo]|mime_in[logo,image/jpg,image/jpeg,image/gif,image/png,image/webp]|max_size[logo,2048]'
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->respond(false, 'Upload failed', null, $this->validator->getErrors(), 422);
        }

        if (!$file->isValid() || $file->hasMoved()) {
            return $this->respond(false, 'File is not valid or already moved', null, null, 500);
        }

        $uploadPath = ROOTPATH . 'public/uploads/settings/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $logo . '.' . $file->getExtension();
        $filePath = $uploadPath . $fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $file->move($uploadPath, $fileName);

        $relativePath = 'uploads/settings/' . $fileName;
        $this->settings->updateSetting([$logo => $relativePath]);
        $this->dashboard->clearCache();

        $data = [
            'src' => base_url($relativePath),
            'relative_path' => $relativePath,
            'filename' => $fileName,
            'type' => $file->getClientMimeType(),
            'size' => $file->getSize()
        ];

        return $this->respond(true, 'File berhasil diunggah', $data);
    }

    public function deleteFile()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Forbidden', null, null, 403);
        }

        $src = $this->request->getPost('src');
        $field = $this->request->getPost('field');

        $allowedFields = [
            'logo_kanan', 'logo_kiri', 'stampel', 'tanda_tangan',
            'ttdproktor', 'kodescan', 'kopsekolah',
            'banner1', 'banner2', 'banner3'
        ];

        if (empty($field) || !in_array($field, $allowedFields, true)) {
            return $this->respond(false, 'Invalid field', null, null, 400);
        }

        $baseUrl = base_url();
        $fileName = str_replace($baseUrl, '', $src ?? '');
        $fileName = ltrim($fileName, '/');
        $fileName = str_replace('\\', '/', $fileName);

        $filePath = ROOTPATH . 'public/' . $fileName;

        if ($fileName !== '' && is_file($filePath)) {
            unlink($filePath);
        }

        $this->settings->updateSetting([$field => '']);
        $this->dashboard->clearCache();

        return $this->respond(true, 'File berhasil dihapus');
    }

    private function normalizeMediaPath($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        $normalized = str_replace(base_url(), '', $path);
        return ltrim($normalized, '/');
    }

    /**
     * Objek setting yang dipakai khusus untuk tombol "Kirim Pesan Tes":
     * nilai dari form (jika diisi) menang atas nilai yang tersimpan, supaya
     * token baru bisa diuji sebelum disimpan.
     */
    private function getTelegramSettingFromPost(): object
    {
        $setting = clone (object) $this->dashboard->getSetting();
        $posted  = $this->request->getPost();

        if (array_key_exists('telegram_bot_token', $posted)) {
            $setting->telegram_bot_token = trim((string) $posted['telegram_bot_token']);
        }
        if (array_key_exists('telegram_chat_id', $posted)) {
            $setting->telegram_chat_id = trim((string) $posted['telegram_chat_id']);
        }

        return $setting;
    }

    /**
     * Uji koneksi + kirim pesan contoh ke grup Telegram.
     */
    public function testTelegram()
    {
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return $this->respond(false, 'Forbidden', null, null, 403);
        }
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Forbidden', null, null, 403);
        }

        $telegram = new \App\Services\TelegramService($this->getTelegramSettingFromPost());

        if (!$telegram->isEnabled()) {
            return $this->respond(false, 'Isi Bot Token dan Chat ID lebih dulu, lalu simpan pengaturan.');
        }

        $appConfig = $this->dashboard->getSetting();
        $sekolah = (string) ($appConfig->nama_aplikasi ?? $appConfig->sekolah ?? 'Aplikasi CBT');
        $waktu = date('d/m/Y H:i:s');

        $res = $telegram->send(
            "<b>✅ Tes Notifikasi Telegram</b>\n"
            . "Aplikasi: " . htmlspecialchars($sekolah, ENT_QUOTES) . "\n"
            . 'Waktu: ' . $waktu . "\n"
            . 'Notifikasi alur Kartu Soal aktif (diajukan / disetujui / perlu revisi).'
        );

        return $this->respond($res['ok'], $res['ok'] ? 'Pesan tes berhasil dikirim ke grup.' : $res['message']);
    }

    public function saveSetting()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Forbidden', null, null, 403);
        }

        $input = $this->request->getPost();
        // Bacalah nilai setting langsung dari DB (bukan dari cache DashboardModel),
        // supaya nilai lama tidak tertimpa cache saat form lain disimpan.
        $currentTg = \Config\Database::connect()->table('setting')
            ->select('telegram_bot_token, telegram_chat_id, jenjang, satuan_pendidikan, api_key, enable_ai, ba_aktif, ba_waktu')
            ->get()->getRow();

        // Validate presence of critical fields (excluding selects with disabled default)
        $critical = ['nama_aplikasi', 'nama_sekolah', 'alamat', 'desa', 'kec', 'kota', 'provinsi', 'kepsek', 'proktor'];
        $errors = [];
        foreach ($critical as $field) {
            if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
                $errors[$field] = 'Field ini wajib diisi';
            }
        }
        if (!empty($errors)) {
            return $this->respond(false, 'Lengkapi semua field yang wajib diisi', null, $errors, 422);
        }

        // Validate email if provided
        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->respond(false, 'Format email tidak valid', null, ['email' => 'Email tidak valid'], 422);
        }

        // Validate URL if provided
        if (!empty($input['web']) && !str_starts_with($input['web'], 'http://') && !str_starts_with($input['web'], 'https://')) {
            // Try prepending https:// for validation
            $url = 'https://' . $input['web'];
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return $this->respond(false, 'Format URL tidak valid (harus diawali http:// atau https://)', null, ['web' => 'URL tidak valid'], 422);
            }
        } elseif (!empty($input['web']) && !filter_var($input['web'], FILTER_VALIDATE_URL)) {
            return $this->respond(false, 'Format URL tidak valid', null, ['web' => 'URL tidak valid'], 422);
        }

        $ba_waktu = null;
        if (!empty($input['ba_waktu'])) {
            try {
                $dt = new \DateTime($input['ba_waktu']);
                $ba_waktu = $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // keep null
            }
        }

        $telp = isset($input['tlp']) ? preg_replace('/[^0-9]/', '', $input['tlp']) : null;
        $kodepos = isset($input['kode_pos']) ? preg_replace('/[^0-9]/', '', $input['kode_pos']) : null;

        $updateData = [
            'nama_aplikasi' => $input['nama_aplikasi'] ?? '',
            'logo_kanan' => $this->normalizeMediaPath($input['logo_kanan'] ?? ''),
            'logo_kiri' => $this->normalizeMediaPath($input['logo_kiri'] ?? ''),
            'stampel' => $this->normalizeMediaPath($input['stampel'] ?? ''),
            'tanda_tangan' => $this->normalizeMediaPath($input['tanda_tangan'] ?? ''),
            'ttdproktor' => $this->normalizeMediaPath($input['ttdproktor'] ?? ''),
            'kodescan' => $this->normalizeMediaPath($input['kodescan'] ?? ''),
            'kopsekolah' => $this->normalizeMediaPath($input['kopsekolah'] ?? ''),
            'banner1' => $this->normalizeMediaPath($input['banner1'] ?? ''),
            'banner2' => $this->normalizeMediaPath($input['banner2'] ?? ''),
            'banner3' => $this->normalizeMediaPath($input['banner3'] ?? ''),
            'web' => $input['web'] ?? '',
            'fax' => $input['fax'] ?? '',
            'email' => $input['email'] ?? '',
            'telp' => $telp,
            'kepsek' => $input['kepsek'] ?? '',
            'nip' => $input['nip'] ?? '',
            'proktor' => $input['proktor'] ?? '',
            'nip_proktor' => $input['nip_proktor'] ?? '',
            'tkn_siswa' => $input['tkn_siswa'] ?? '',
            'mode_app' => $input['mode_app'] ?? '',
            // Guard: hanya sentuh bila field-nya benar-benar dikirim dari form.
            // Tanpa ini, menyimpan pengaturan lain akan mengosongkan nilai AI / bank soal lama.
            'api_key' => array_key_exists('api_key', $input)
                ? trim((string) $input['api_key'])
                : (string) ($currentTg->api_key ?? ''),
            'enable_ai' => array_key_exists('enable_ai', $input)
                ? (string) $input['enable_ai']
                : (string) ($currentTg->enable_ai ?? '1'),
            'ba_aktif' => array_key_exists('ba_aktif', $input)
                ? (string) $input['ba_aktif']
                : (string) ($currentTg->ba_aktif ?? '1'),
            'ba_waktu' => array_key_exists('ba_waktu', $input) && !empty($input['ba_waktu'])
                ? $ba_waktu
                : ($currentTg->ba_waktu ?? null),
            // Opsi "Pilih Jenjang"/"Pilih Satuan" ber-value kosong dan disabled,
            // jadi browser TIDAK mengirim field-nya. Nilai lama akan ikut tertimpa tanpa guard ini,, nilai lama
            // ikut tertimpa jadi kosong setiap kali pengaturan lain disimpan.
            'jenjang' => array_key_exists('jenjang', $input)
                ? (string) $input['jenjang']
                : (string) ($currentTg->jenjang ?? ''),
            'satuan_pendidikan' => array_key_exists('satuan_pendidikan', $input)
                ? (string) $input['satuan_pendidikan']
                : (string) ($currentTg->satuan_pendidikan ?? ''),
            'sekolah' => $input['nama_sekolah'] ?? '',
            'nss' => $input['nss'] ?? '',
            'npsn' => $input['npsn'] ?? '',
            'alamat' => $input['alamat'] ?? '',
            'desa' => $input['desa'] ?? '',
            'kecamatan' => $input['kec'] ?? '',
            'kota' => $input['kota'] ?? '',
            'provinsi' => $input['provinsi'] ?? '',
            'kode_pos' => $kodepos,
            // Hanya sentuh bila field-nya benar-benar dikirim. Tanpa ini, menyimpan
            // form pengaturan lain akan mengosongkan token/chat id Telegram.
            'telegram_bot_token' => array_key_exists('telegram_bot_token', $input)
                ? trim((string) $input['telegram_bot_token'])
                : (string) ($currentTg->telegram_bot_token ?? ''),
            'telegram_chat_id' => array_key_exists('telegram_chat_id', $input)
                ? trim((string) $input['telegram_chat_id'])
                : (string) ($currentTg->telegram_chat_id ?? ''),
            'wa_gateway_url' => $input['wa_gateway_url'] ?? '',
            'wa_token' => $input['wa_token'] ?? '',
            'wa_target_number' => $input['wa_target_number'] ?? '',
            'mikrotik_host' => $input['mikrotik_host'] ?? '',
            'mikrotik_port' => $input['mikrotik_port'] ?? '8728',
            'mikrotik_user' => $input['mikrotik_user'] ?? '',
            'mikrotik_pass' => $input['mikrotik_pass'] ?? '',
            'maintenance_aktif' => $this->pickMaintenanceAktif($input),
            'maintenance_pesan' => trim((string) ($input['maintenance_pesan'] ?? '')),
            'maintenance_sampai' => $this->parseMaintenanceDate($input['maintenance_sampai'] ?? ''),
        ];

        $status = $this->settings->updateSetting($updateData);

        if ($status) {
            $this->dashboard->clearCache();
            (new MaintenanceService())->clearCache();
        }

        if (!$status) {
            return $this->respond(false, 'Gagal menyimpan pengaturan', null, null, 500);
        }

        return $this->respond(true, 'Pengaturan berhasil disimpan');
    }

    /**
     * Maintenance aktif/tidak dibaca dari checkbox toggle pada form pengaturan.
     * Bila maintenance dinonaktifkan, jadwal selesai juga dibersihkan agar tidak
     * mengaktifkan mode secara tak sengaja di masa depan.
     */
    private function pickMaintenanceAktif(array $input): int
    {
        return ! empty($input['maintenance_aktif']) ? 1 : 0;
    }

    /**
     * Ubah input datetime-local ("Y-m-dTH:i") menjadi format kolom DATETIME.
     */
    private function parseMaintenanceDate($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $time = strtotime(str_replace('T', ' ', $value));

        return $time === false ? null : date('Y-m-d H:i:s', $time);
    }

    /**
     * Tombol cepat: nyalakan / matikan maintenance tanpa menyimpan form penuh.
     */
    public function toggleMaintenance()
    {
        if (!$this->request->isAJAX()) {
            return $this->respond(false, 'Forbidden', null, null, 403);
        }
        if (!$this->isLoggedIn() || !$this->isAdmin()) {
            return $this->respond(false, 'Forbidden', null, null, 403);
        }

        $aktif = $this->request->getPost('aktif') !== null
            ? (int) $this->request->getPost('aktif')
            : 0;
        $aktif = $aktif === 1 ? 1 : 0;

        $current = \Config\Database::connect()
            ->table('setting')
            ->select('maintenance_pesan, maintenance_sampai')
            ->get()
            ->getRow();

        $updateData = ['maintenance_aktif' => $aktif];

        // Saat dimatikan, jadwal selesai ikut dibuang supaya tidak menyala lagi.
        if ($aktif === 0) {
            $updateData['maintenance_sampai'] = null;
        } elseif (($current->maintenance_sampai ?? null) === null) {
            $updateData['maintenance_sampai'] = date('Y-m-d H:i:s', time() + 3600);
        }

        if (!$this->settings->updateSetting($updateData)) {
            return $this->respond(false, 'Gagal mengubah status maintenance', null, null, 500);
        }

        $this->dashboard->clearCache();
        (new MaintenanceService())->clearCache();

        return $this->respond(
            true,
            $aktif === 1 ? 'Maintenance diaktifkan.' : 'Maintenance dimatikan.',
            ['aktif' => $aktif]
        );
    }
}
