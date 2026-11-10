<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$satuan = ["1" => ["SD", "MI"], "2" => ["SMP", "MTS"], "3" => ["SMA", "MA", "SMK"]];

$settingData = isset($setting) && is_object($setting) ? $setting : (object) [];

$esc = static function ($value) {
    return esc($value ?? '');
};

$jenjangOptions = [
    1 => 'SD/MI',
    2 => 'SMP/MTS',
    3 => 'SMA/MA/SMK'
];

$bankSoalOptions = [
    1 => 'Aktif',
    2 => 'Non-Aktif'
];

$tokenOptions = [
    1 => 'Ya',
    2 => 'Tidak'
];

$modeOptions = [
    1 => 'CBT',
    2 => 'Elearning'
];

$bankSoalDateTime = '';
if (!empty($settingData->ba_waktu) && $settingData->ba_waktu !== '0000-00-00 00:00:00') {
    $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', (string) ($settingData->ba_waktu ?? ''));
    if ($dateObj instanceof DateTime) {
        $bankSoalDateTime = $dateObj->format('Y-m-d\TH:i');
    }
}

$maintenanceDateTime = '';
if (!empty($settingData->maintenance_sampai) && $settingData->maintenance_sampai !== '0000-00-00 00:00:00') {
    $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', (string) ($settingData->maintenance_sampai ?? ''));
    if ($dateObj instanceof DateTime) {
        $maintenanceDateTime = $dateObj->format('Y-m-d\TH:i');
    }
}

$maintenanceAktif = ((string) ($settingData->maintenance_aktif ?? '0')) === '1';
?>

<!-- Content Header (Page Header) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-gear text-primary me-2"></i>
                    <?= esc($judul ?? 'Profile Sekolah') ?>
                </h3>
                <p class="text-muted small mb-0 mt-1">Konfigurasi profile sekolah, identitas visual, dan preferensi operasional aplikasi</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">Pengaturan</li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Umum') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Flash Information Banner -->
        <div class="alert alert-info alert-dismissible fade show my-shadow border-0 rounded-3 mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            Perubahan konfigurasi akan secara otomatis memperbarui header, kop surat, dan laporan cetak aplikasi.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Main Settings Card with Tabs -->
        <div class="card card-default my-shadow mb-4">
            <div class="card-header p-2 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <ul class="nav nav-pills" id="settingsTabs" role="tablist">
                    <li class="nav-item me-1">
                        <button class="nav-link active fw-bold py-2 px-3" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="bi bi-building me-1"></i> Identitas & Alamat
                        </button>
                    </li>
                    <li class="nav-item me-1">
                        <button class="nav-link fw-bold py-2 px-3" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                            <i class="bi bi-person-lines-fill me-1"></i> Pengelola & Kontak
                        </button>
                    </li>
                    <li class="nav-item me-1">
                        <button class="nav-link fw-bold py-2 px-3" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                            <i class="bi bi-cpu me-1"></i> Sistem & AI
                        </button>
                    </li>
                    <li class="nav-item me-1">
                        <button class="nav-link fw-bold py-2 px-3" id="notif-tab" data-bs-toggle="tab" data-bs-target="#notifikasi" type="button" role="tab">
                            <i class="bi bi-send me-1"></i> Notifikasi
                        </button>
                    </li>
                    <li class="nav-item me-1">
                        <button class="nav-link fw-bold py-2 px-3" id="visual-tab" data-bs-toggle="tab" data-bs-target="#visual" type="button" role="tab">
                            <i class="bi bi-palette me-1"></i> Logo Sekolah
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold py-2 px-3" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                            <i class="bi bi-cone-striped me-1"></i> Maintenance
                        </button>
                    </li>
                </ul>

                <div class="card-tools ms-auto">
                    <button type="button" class="btn btn-primary fw-bold shadow-sm px-4" onclick="submitSetting()">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                <?= form_open('', ['id' => 'savesetting']) ?>
                <div class="tab-content" id="settingsTabsContent">

                    <!-- TAB 1: IDENTITAS & ALAMAT -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-info-square text-primary me-2"></i>Informasi Dasar Sekolah
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Nama Aplikasi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-window text-primary"></i></span>
                                    <input type="text" name="nama_aplikasi" class="form-control required" value="<?= $esc($settingData->nama_aplikasi ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Nama Sekolah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-building text-primary"></i></span>
                                    <input type="text" name="nama_sekolah" class="form-control required" value="<?= $esc($settingData->sekolah ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">NSS / NSM</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-hash text-primary"></i></span>
                                    <input type="text" name="nss" class="form-control" value="<?= $esc($settingData->nss ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">NPSN Sekolah</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-qr-code text-primary"></i></span>
                                    <input type="text" name="npsn" class="form-control" value="<?= $esc($settingData->npsn ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Jenjang Pendidikan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-layers text-primary"></i></span>
                                    <select id="jenjang" class="form-select" name="jenjang">
                                        <option value="" disabled <?= empty($settingData->jenjang) ? 'selected' : '' ?>>Pilih Jenjang</option>
                                        <?php foreach ($jenjangOptions as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ((string) ($settingData->jenjang ?? '')) === (string) $key ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Satuan Pendidikan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-award text-primary"></i></span>
                                    <select id="satuan-pend" class="form-select" name="satuan_pendidikan">
                                        <option value="" disabled <?= empty($settingData->satuan_pendidikan) ? 'selected' : '' ?>>Pilih Satuan</option>
                                        <?php
                                        $satuanOptionList = $satuan[$settingData->jenjang ?? '1'] ?? [];
                                        foreach ($satuanOptionList as $idx => $label):
                                            $optionValue = $idx + 1;
                                            ?>
                                            <option value="<?= $optionValue ?>" <?= ((string) ($settingData->satuan_pendidikan ?? '')) === (string) $optionValue ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-geo-alt text-primary me-2"></i>Lokasi & Alamat Sekolah
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Alamat Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-geo-alt text-primary"></i></span>
                                    <input type="text" name="alamat" class="form-control required" value="<?= $esc($settingData->alamat ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Desa / Kelurahan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-map text-primary"></i></span>
                                    <input type="text" name="desa" class="form-control required" value="<?= $esc($settingData->desa ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Kecamatan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-map text-primary"></i></span>
                                    <input type="text" name="kec" class="form-control required" value="<?= $esc($settingData->kecamatan ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Kota / Kabupaten <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-pin-map text-primary"></i></span>
                                    <input type="text" name="kota" class="form-control required" value="<?= $esc($settingData->kota ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Provinsi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-globe text-primary"></i></span>
                                    <input type="text" name="provinsi" class="form-control required" value="<?= $esc($settingData->provinsi ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: PENGELOLA & KONTAK -->
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-telephone-inbound text-primary me-2"></i>Kontak Resmi Sekolah
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Email Sekolah</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope-at text-primary"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?= $esc($settingData->email ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Website Resmi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-browser-chrome text-primary"></i></span>
                                    <input type="url" name="web" class="form-control" value="<?= $esc($settingData->web ?? '') ?>" placeholder="https://">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Telepon Sekolah</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone text-primary"></i></span>
                                    <input type="text" name="tlp" class="form-control" value="<?= $esc($settingData->telp ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-person-badge text-primary me-2"></i>Penanggung Jawab (Kepsek & Proktor)
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Nama Kepala Sekolah <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-workspace text-primary"></i></span>
                                    <input type="text" name="kepsek" class="form-control required" value="<?= $esc($settingData->kepsek ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">NIP Kepala Sekolah</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key text-primary"></i></span>
                                    <input type="text" name="nip" class="form-control" value="<?= $esc($settingData->nip ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Nama Proktor Utama <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-gear text-primary"></i></span>
                                    <input type="text" name="proktor" class="form-control required" value="<?= $esc($settingData->proktor ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">NIP Proktor</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key-fill text-primary"></i></span>
                                    <input type="text" name="nip_proktor" class="form-control" value="<?= $esc($settingData->nip_proktor ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: SISTEM & AI -->
                    <div class="tab-pane fade" id="system" role="tabpanel">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-sliders text-primary me-2"></i>Operasional Ujian & Data
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Mode Aplikasi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-display text-primary"></i></span>
                                    <select name="mode_app" class="form-select">
                                        <?php foreach ($modeOptions as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ((string) ($settingData->mode_app ?? '')) === (string) $key ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Tampilkan Token di Siswa</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-shield-lock text-primary"></i></span>
                                    <select name="tkn_siswa" class="form-select">
                                        <?php foreach ($tokenOptions as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ((string) ($settingData->tkn_siswa ?? '')) === (string) $key ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Status Bank Soal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-database-check text-primary"></i></span>
                                    <select name="ba_aktif" class="form-select">
                                        <?php foreach ($bankSoalOptions as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= ((string) ($settingData->ba_aktif ?? '')) === (string) $key ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Waktu Aktif Bank Soal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar-event text-primary"></i></span>
                                    <input type="datetime-local" name="ba_waktu" class="form-control" value="<?= $esc($bankSoalDateTime) ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-robot text-primary me-2"></i>Integrasi AI (Artificial Intelligence)
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Fitur AI (Auto-Generate)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-robot text-primary"></i></span>
                                    <select name="enable_ai" class="form-select">
                                        <option value="1" <?= ((string) ($settingData->enable_ai ?? '1')) === '1' ? 'selected' : '' ?>>Aktifkan Fitur AI</option>
                                        <option value="0" <?= ((string) ($settingData->enable_ai ?? '1')) === '0' ? 'selected' : '' ?>>Nonaktifkan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">API Key (OpenAI / Gemini)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key-fill text-primary"></i></span>
                                    <input type="text" name="api_key" class="form-control" value="<?= $esc($settingData->api_key ?? '') ?>" placeholder="Masukkan API Key disini...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: NOTIFIKASI (TELEGRAM) -->
                    <div class="tab-pane fade" id="notifikasi" role="tabpanel">
                        <div class="alert alert-primary alert-dismissible fade show my-shadow border-0 rounded-3 mb-4" role="alert">
                            <i class="bi bi-telegram me-2"></i>
                            Notifikasi <b>alur Kartu Soal</b> (diajukan, disetujui, perlu revisi, pembatalan persetujuan)
                            dikirim otomatis ke satu grup/channel Telegram.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-1-circle text-primary me-2"></i>Cara Mendapatkan Token &amp; Chat ID
                        </h6>
                        <ol class="mb-4" style="font-size:.85rem;">
                            <li>Di Telegram, buka chat <b>@BotFather</b> → kirim <code>/newbot</code> → salin token yang diberikan.</li>
                            <li>Tambahkan bot tersebut ke grup sekolah, lalu kirim satu pesan di dalam grup itu.</li>
                            <li>Buka <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code>, cari objek
                                <code>message</code>, lalu ambil nilai <code>chat.id</code> (untuk grup supergrup diawali <code>-100</code>).</li>
                        </ol>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 mt-4">
                            <i class="bi bi-key text-primary me-2"></i>Kredensial Bot
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Bot Token</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-robot text-primary"></i></span>
                                    <input type="password" name="telegram_bot_token" id="tgBotToken"
                                        class="form-control font-monospace" autocomplete="off"
                                        placeholder="1234567890:AAExxxxxxxxxxxxxxxxxxxxxxxxx"
                                        value="<?= $esc($settingData->telegram_bot_token ?? '') ?>">
                                    <button class="btn btn-outline-secondary" type="button" id="tgToggleToken"
                                        title="Tampilkan / sembunyikan token" aria-label="Tampilkan atau sembunyikan token">
                                        <i class="bi bi-eye-slash" id="tgToggleIcon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Disimpan di database aplikasi. Kosongkan bila tidak memakai notifikasi Telegram.</div>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Chat ID Grup/Channel</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-people text-primary"></i></span>
                                    <input type="text" name="telegram_chat_id" class="form-control font-monospace"
                                        autocomplete="off" placeholder="-1001234567890"
                                        value="<?= $esc($settingData->telegram_chat_id ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="button" class="btn btn-outline-primary shadow-sm fw-bold" id="btnTestTelegram">
                                <i class="bi bi-send me-1"></i> Kirim Pesan Tes
                            </button>
                            <span class="text-muted align-self-center" style="font-size:.8rem;">
                                Simpan pengaturan terlebih dahulu, lalu klik Kirim Pesan Tes untuk memastikan bot aktif di grup.
                            </span>
                        </div>
                    </div>

                    <!-- TAB 5: IDENTITAS VISUAL -->
                    <div class="tab-pane fade" id="visual" role="tabpanel">
                        <div class="alert alert-primary alert-dismissible fade show my-shadow border-0 rounded-3 mb-4" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Pengaturan logo ini digunakan untuk Kop Surat, Kartu Ujian, dan Laporan Hasil Evaluasi. Format yang didukung: PNG/JPG (Maks. 2MB).
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <div class="row g-3">
                            <?php
                            $uploadInputs = [
                                ['id' => 'set-logo-kiri', 'input_id' => 'logo-kiri', 'label' => 'Logo Utama (Kiri)', 'field' => 'logo_kiri'],
                                ['id' => 'set-logo-kanan', 'input_id' => 'logo-kanan', 'label' => 'Logo Pendukung (Kanan)', 'field' => 'logo_kanan'],
                                ['id' => 'set-stampel', 'input_id' => 'stampel', 'label' => 'Cap / Stempel Sekolah', 'field' => 'stampel'],
                                ['id' => 'set-tandatangan', 'input_id' => 'tanda-tangan', 'label' => 'Tanda Tangan Kepsek', 'field' => 'tanda_tangan'],
                                ['id' => 'set-kopsekolah', 'input_id' => 'kopsekolah', 'label' => 'Header Banner Kop Sekolah', 'field' => 'kopsekolah'],
                            ];

                            foreach ($uploadInputs as $item):
                                $val = !empty($settingData->{$item['field']}) ? base_url($settingData->{$item['field']}) : '';
                                ?>
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="card border my-shadow p-3 h-100 rounded-3">
                                        <label class="form-label small fw-bold text-dark text-uppercase mb-2">
                                            <i class="bi bi-image text-primary me-1"></i><?= $item['label'] ?>
                                        </label>
                                        <input type="file" id="<?= $item['input_id'] ?>" name="logo" class="dropify" data-height="160" data-default-file="<?= $val ?>" />
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- TAB 6: MAINTENANCE -->
                    <div class="tab-pane fade" id="maintenance" role="tabpanel">
                        <div class="alert alert-warning alert-dismissible fade show my-shadow border-0 rounded-3 mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Saat maintenance aktif, <b>siswa dan pengunjung tanpa login</b> akan melihat halaman maintenance.
                            Admin, Guru, dan Panitia tetap bisa mengakses aplikasi seperti biasa.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-cone-striped text-primary me-2"></i>Status Maintenance
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Maintenance</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-power text-primary"></i></span>
                                    <select name="maintenance_aktif" id="maintenanceAktif" class="form-select">
                                        <option value="0" <?= ((string) ($settingData->maintenance_aktif ?? '0')) === '0' ? 'selected' : '' ?>>
                                            Nonaktif - Aplikasi dapat diakses semua user
                                        </option>
                                        <option value="1" <?= ((string) ($settingData->maintenance_aktif ?? '0')) === '1' ? 'selected' : '' ?>>
                                            Aktif - Tampilkan halaman maintenance ke siswa
                                        </option>
                                    </select>
                                </div>
                                <div class="form-text" id="maintenanceStatusHint">
                                    <span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Aplikasi online normal.</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-1">Sampai Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-clock-history text-primary"></i></span>
                                    <input type="datetime-local" name="maintenance_sampai" id="maintenanceSampai"
                                        class="form-control" value="<?= $maintenanceDateTime ?>">
                                </div>
                                <div class="form-text">Kosongkan untuk maintenance tanpa batas waktu.</div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-chat-left-text text-primary me-2"></i>Pesan yang Ditampilkan
                        </h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Pesan Maintenance</label>
                            <textarea name="maintenance_pesan" id="maintenancePesan" class="form-control" rows="4"
                                placeholder="Contoh: Aplikasi sedang dalam pemeliharaan. Mohon tunggu hingga selesai."><?= esc($settingData->maintenance_pesan ?? '') ?></textarea>
                            <div class="form-text">Kosongkan untuk memakai pesan bawaan aplikasi.</div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <button type="button" class="btn btn-outline-danger fw-bold shadow-sm" id="btnToggleMaintenance">
                                <i class="bi bi-lightning-charge me-1"></i>
                                <span id="btnToggleMaintenanceLabel"><?= ((string) ($settingData->maintenance_aktif ?? '0')) === '1' ? 'Matikan Maintenance' : 'Nyalakan Maintenance Sekarang' ?></span>
                            </button>
                            <span class="text-muted align-self-center" style="font-size:.8rem;">
                                Tombol cepat: tidak perlu menyimpan form. Pengaturan di atas tetap memakai tombol Simpan.
                            </span>
                        </div>
                    </div>

                </div>
                <?= form_close() ?>
            </div>

            <!-- Footer Card Action -->
            <div class="card-footer bg-light p-3 border-top text-end">
                <button type="button" class="btn btn-primary fw-bold shadow-sm px-4" onclick="submitSetting()">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/dropify/js/dropify.min.js') ?>"></script>
<script>
    const mediaMappings = {
        'logo-kanan': 'logo_kanan', 'logo-kiri': 'logo_kiri', 'stampel': 'stampel',
        'tanda-tangan': 'tanda_tangan', 'ttdproktor': 'ttdproktor', 'kodescan': 'kodescan',
        'kopsekolah': 'kopsekolah', 'banner1': 'banner1', 'banner2': 'banner2', 'banner3': 'banner3'
    };

    const baseUrl = '<?= base_url() ?>'.replace(/\/+$/, '');
    let csrf = { name: '<?= csrf_token() ?>', hash: '<?= csrf_hash() ?>' };

    const updateCsrf = (res) => {
        if (res && res.csrfName && res.csrfHash) {
            csrf = { name: res.csrfName, hash: res.csrfHash };
            $.ajaxSetup({ data: { [csrf.name]: csrf.hash } });
        }
    };
    $.ajaxSetup({ data: { [csrf.name]: csrf.hash } });

    const appendCsrf = (fd) => { if (fd instanceof FormData) fd.append(csrf.name, csrf.hash); };

    let skipDropifyClear = false;

    const logos = <?= json_encode([
        'logo_kanan' => $settingData->logo_kanan ?? '',
        'logo_kiri' => $settingData->logo_kiri ?? '',
        'kodescan' => $settingData->kodescan ?? '',
        'kopsekolah' => $settingData->kopsekolah ?? '',
        'stampel' => $settingData->stampel ?? '',
        'ttdproktor' => $settingData->ttdproktor ?? '',
        'tanda_tangan' => $settingData->tanda_tangan ?? '',
        'banner1' => $settingData->banner1 ?? '',
        'banner2' => $settingData->banner2 ?? '',
        'banner3' => $settingData->banner3 ?? '',
    ]) ?>;

    const satuanPend = <?= json_encode($satuan) ?>;

    function submitSetting() { $('#savesetting').submit(); }

    // Uji kirim notifikasi Telegram (pakai nilai dari form, tidak perlu disimpan dulu)
    $(document).on('click', '#tgToggleToken', function () {
        const $input = $('#tgBotToken');
        const show = $input.attr('type') === 'password';
        $input.attr('type', show ? 'text' : 'password');
        $('#tgToggleIcon').attr('class', show ? 'bi bi-eye-slash' : 'bi bi-eye');
    });

    $(document).on('click', '#btnTestTelegram', function () {
        const token = ($('[name="telegram_bot_token"]').val() || '').trim();
        const chatId = ($('[name="telegram_chat_id"]').val() || '').trim();

        // Belum diisi = bukan error, cukup arahan.
        if (!token || !chatId) {
            Swal.fire({
                icon: 'info',
                title: 'Belum bisa diuji',
                html: 'Isi <b>Bot Token</b> dan <b>Chat ID</b> terlebih dahulu.'
                    + (token ? '' : '<br>• Bot Token masih kosong')
                    + (chatId ? '' : '<br>• Chat ID Grup masih kosong'),
                confirmButtonText: 'Mengerti'
            });
            bootstrap.Tab.getOrCreateInstance(document.getElementById('notif-tab')).show();
            (!token ? $('#tgBotToken') : $('[name="telegram_chat_id"]')).trigger('focus');
            return;
        }

        const $btn = $(this);
        const $icon = $btn.find('i');
        const html = $btn.html();

        $btn.prop('disabled', true);
        $icon.addClass('spinner-border spinner-border-sm');

        $.ajax({
            url: baseUrl + '/settings/test-telegram',
            type: 'POST',
            data: { telegram_bot_token: token, telegram_chat_id: chatId },
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    if (typeof showSuccessToast === 'function') showSuccessToast(res.message);
                    else Swal.fire('Berhasil', res.message, 'success');
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal mengirim',
                        html: escHtml(res.message || 'Pesan ditolak Telegram.')
                            + '<br><small class="text-muted">Cek token dari @BotFather, pastikan bot sudah ditambahkan ke grup,'
                            + ' dan chat id benar (grup supergrup diawali -100).</small>',
                        confirmButtonText: 'Mengerti'
                    });
                }
            },
            error: function (xhr) {
                let msg = 'Tidak dapat menghubungi server.';
                if (xhr.status === 403 || xhr.status === 401) {
                    msg = 'Sesi login habis atau token ditolak. Muat ulang halaman lalu coba lagi.';
                } else if (xhr.status === 0) {
                    msg = 'Koneksi ke server terputus. Periksa jaringan Anda.';
                } else if (xhr.responseText) {
                    msg = 'Server membalas HTTP ' + xhr.status + '.';
                }
                Swal.fire('Tidak dapat mengirim', msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html(html);
            }
        });
    });

    function escHtml(t) {
        return String(t).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    // =========================================================
    // MAINTENANCE
    // =========================================================
    const maintenanceOn = <?= $maintenanceAktif ? 'true' : 'false' ?>;

    function paintMaintenanceState(aktif) {
        const $hint = $('#maintenanceStatusHint');
        const $label = $('#btnToggleMaintenanceLabel');
        const $icon = $('#btnToggleMaintenance').find('i');

        if (aktif) {
            $hint.html('<span class="text-danger fw-bold"><i class="bi bi-cone-striped me-1"></i>Maintenance aktif - siswa melihat halaman maintenance.</span>');
            $label.text('Matikan Maintenance');
            $icon.removeClass('bi-lightning-charge').addClass('bi-power');
        } else {
            $hint.html('<span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Aplikasi online normal.</span>');
            $label.text('Nyalakan Maintenance Sekarang');
            $icon.removeClass('bi-power').addClass('bi-lightning-charge');
        }
    }

    $(document).on('change', '#maintenanceAktif', function () {
        paintMaintenanceState($(this).val() === '1');
    });

    paintMaintenanceState($('#maintenanceAktif').val() === '1');

    $(document).on('click', '#btnToggleMaintenance', function () {
        const next = maintenanceOn ? 0 : 1;
        const $btn = $(this).prop('disabled', true);
        const html = $btn.html();

        const apply = () => {
            maintenanceOn = next === 1;
            $('#maintenanceAktif').val(next === 1 ? '1' : '0');
            paintMaintenanceState(maintenanceOn);
            $('#maintenanceSampai').prop('disabled', !maintenanceOn);
        };

        const doToggle = () => {
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

            $.ajax({
                url: baseUrl + '/settings/maintenance/toggle',
                type: 'POST',
                data: { aktif: next },
                success: function (res) {
                    updateCsrf(res);
                    if (res.status) {
                        apply();
                        if (typeof showSuccessToast === 'function') {
                            showSuccessToast(res.message);
                        } else {
                            Swal.fire('Berhasil', res.message, 'success');
                        }
                    } else if (typeof showDangerToast === 'function') {
                        showDangerToast(res.message);
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Gagal menghubungi server.';
                    if (typeof showDangerToast === 'function') {
                        showDangerToast(msg);
                    } else {
                        Swal.fire('Gagal', msg, 'error');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).html(html);
                }
            });
        };

        Swal.fire({
            icon: next === 1 ? 'warning' : 'question',
            title: next === 1 ? 'Aktifkan Maintenance?' : 'Matikan Maintenance?',
            text: next === 1
                ? 'Siswa dan pengunjung tanpa login akan melihat halaman maintenance. Admin, Guru, dan Panitia tetap bisa mengakses aplikasi.'
                : 'Aplikasi akan kembali dapat diakses oleh semua user, termasuk siswa.',
            showCancelButton: true,
            confirmButtonText: next === 1 ? 'Ya, Aktifkan' : 'Ya, Matikan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (result) {
            if (result.isConfirmed) doToggle();
        });
    });

    // Inline Validation
    function validateForm() {
        let valid = true;
        $('.required').each(function () {
            const $el = $(this);
            const $group = $el.closest('.input-group');
            $group.find('.invalid-feedback').remove();
            $el.removeClass('is-invalid');
            if (!$el.val().trim()) {
                valid = false;
                $el.addClass('is-invalid');
                $group.after('<div class="invalid-feedback d-block small mt-1">Kolom ini wajib diisi</div>');
            }
        });
        return valid;
    }

    $(document).ready(function () {
        // Clear validation error on input
        $(document).on('input change', '.required', function () {
            $(this).removeClass('is-invalid');
            $(this).closest('.input-group').next('.invalid-feedback').remove();
        });

        // Submit handler
        $('#savesetting').on('submit', function (e) {
            e.preventDefault();
            if (!validateForm()) {
                if (typeof showDangerToast === 'function') {
                    showDangerToast('Mohon lengkapi semua kolom yang bertanda bintang (*)');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire('Form Belum Lengkap', 'Mohon lengkapi semua kolom yang bertanda bintang (*)', 'warning');
                } else {
                    alert('Mohon lengkapi semua kolom yang bertanda bintang (*)');
                }
                return;
            }

            const $btn = $('.btn-primary');
            const origHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

            $.ajax({
                url: baseUrl + '/settings/save',
                type: 'POST',
                data: $(this).serialize() + '&' + $.param(logos),
                success: function (response) {
                    if (response.status) {
                        if (typeof showSuccessToast === 'function') {
                            showSuccessToast(response.message || 'Pengaturan berhasil disimpan');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire('Berhasil', response.message || 'Pengaturan berhasil disimpan', 'success');
                        } else {
                            alert(response.message || 'Pengaturan berhasil disimpan');
                        }
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        if (response.errors) {
                            Object.keys(response.errors).forEach(function (field) {
                                const $input = $(`[name="${field}"]`);
                                $input.addClass('is-invalid');
                                const $group = $input.closest('.input-group');
                                $group.find('.invalid-feedback').remove();
                                $group.after(`<div class="invalid-feedback d-block small mt-1">${response.errors[field]}</div>`);
                            });
                        }
                        if (typeof showDangerToast === 'function') {
                            showDangerToast(response.message || 'Gagal menyimpan pengaturan');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal', response.message || 'Gagal menyimpan pengaturan', 'error');
                        } else {
                            alert(response.message || 'Gagal menyimpan pengaturan');
                        }
                    }
                },
                error: function (xhr) {
                    const resp = xhr.responseJSON || {};
                    const msg = resp.message || 'Gagal menyimpan pengaturan';
                    if (typeof showDangerToast === 'function') {
                        showDangerToast(msg);
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).html(origHtml);
                }
            });
        });

        // Jenjang cascade
        $('#jenjang').on('change', function () {
            const val = $(this).val();
            const opts = ['<option value="" disabled selected>Pilih Satuan Pendidikan</option>'];
            if (satuanPend[val]) {
                satuanPend[val].forEach(function (label, idx) {
                    opts.push(`<option value="${idx + 1}">${label}</option>`);
                });
            }
            $('#satuan-pend').html(opts.join(''));
        });

        // Dropify initializer
        if ($.fn.dropify) {
            const drEvent = $('.dropify').dropify({
                messages: {
                    default: 'Seret atau klik untuk mengunggah logo',
                    replace: 'Seret atau klik untuk mengganti logo',
                    remove: 'Hapus',
                    error: 'Terjadi kesalahan pada file'
                },
                error: {
                    fileSize: 'Ukuran maksimal {{ value }}.',
                    imageFormat: 'Hanya {{ value }} yang diizinkan.'
                }
            });

            drEvent.on('dropify.afterClear', function (event, element) {
                if (skipDropifyClear) { skipDropifyClear = false; return; }
                const fieldKey = mediaMappings[element.element.id];
                const $input = $(event.currentTarget);
                const defaultFile = $input.attr('data-default-file') || '';
                if (fieldKey) {
                    logos[fieldKey] = '';
                    if (defaultFile) deleteImage(defaultFile, fieldKey);
                }
                $input.attr('data-default-file', '');
            });

            $('.dropify').on('change', function () {
                const fieldKey = mediaMappings[this.id];
                if (!fieldKey || !(this.files && this.files[0])) return;

                if (this.files[0].size > 2 * 1024 * 1024) {
                    if (typeof showDangerToast === 'function') {
                        showDangerToast('Ukuran file maksimum 2MB');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('File Terlalu Besar', 'Ukuran file maksimum 2MB', 'warning');
                    }
                    $(this).val('');
                    return;
                }

                const fd = new FormData();
                fd.append('logo', this.files[0], this.files[0].name);
                appendCsrf(fd);
                uploadAttach(baseUrl + `/settings/uploadFile/${fieldKey}`, fd, fieldKey, this.id);
            });
        }
    });

    function uploadAttach(url, data, fieldKey, inputId) {
        const $input = $('#' + inputId);

        $.ajax({
            type: 'POST',
            enctype: 'multipart/form-data',
            url: url,
            dataType: 'json',
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (response) {
                updateCsrf(response);
                if (response && response.status) {
                    const fileUrl = response.data?.src || '';
                    const relPath = response.data?.relative_path || '';
                    logos[fieldKey] = relPath;
                    if (fileUrl) $input.attr('data-default-file', fileUrl);
                    const dropify = $input.data('dropify');
                    if (dropify) {
                        skipDropifyClear = true;
                        dropify.settings.defaultFile = fileUrl;
                        dropify.destroy();
                        dropify.init();
                        skipDropifyClear = false;
                    }
                    if (typeof showSuccessToast === 'function') {
                        showSuccessToast('File berhasil diunggah');
                    }
                } else {
                    if (typeof showDangerToast === 'function') {
                        showDangerToast(response.message || 'Gagal mengunggah');
                    }
                }
            },
            error: function (xhr) {
                const resp = xhr.responseJSON || {};
                if (typeof showDangerToast === 'function') {
                    showDangerToast(resp.message || 'Gagal mengunggah file');
                }
            }
        });
    }

    function deleteImage(src, fieldKey) {
        if (!fieldKey || !src) return;
        $.ajax({
            type: 'POST',
            url: baseUrl + '/settings/deleteFile',
            data: { src: src, field: fieldKey, [csrf.name]: csrf.hash },
            cache: false,
            success: function (response) { updateCsrf(response); },
            error: function (xhr) { console.error('Delete error:', xhr.responseText); }
        });
    }
</script>
<?= $this->endSection() ?>