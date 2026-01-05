<?php
$satuan = ["1" => ["SD", "MI"], "2" => ["SMP", "MTS"], "3" => ["SMA", "MA", "SMK"]];

$settingData = isset($setting) && is_object($setting) ? $setting : (object) [];

$esc = static function ($value) {
    return html_escape($value ?? '');
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

$requiredSummaryFields = [
    'nama_aplikasi' => $settingData->nama_aplikasi ?? '',
    'nama_sekolah' => $settingData->sekolah ?? '',
    'jenjang' => $settingData->jenjang ?? '',
    'satuan_pendidikan' => $settingData->satuan_pendidikan ?? '',
    'alamat' => $settingData->alamat ?? '',
    'desa' => $settingData->desa ?? '',
    'kecamatan' => $settingData->kecamatan ?? '',
    'kota' => $settingData->kota ?? '',
    'provinsi' => $settingData->provinsi ?? '',
    'kepsek' => $settingData->kepsek ?? '',
    'proktor' => $settingData->proktor ?? ''
];

$totalRequiredFields = count($requiredSummaryFields);
$filledRequiredFields = count(array_filter($requiredSummaryFields, static fn($value) => trim((string) $value) !== ''));

$mediaFields = [
    'logo_kanan' => $settingData->logo_kanan ?? '',
    'logo_kiri' => $settingData->logo_kiri ?? '',
    'tanda_tangan' => $settingData->tanda_tangan ?? '',
    'ttdproktor' => $settingData->ttdproktor ?? '',
    'stampel' => $settingData->stampel ?? '',
    'kodescan' => $settingData->kodescan ?? '',
    'kopsekolah' => $settingData->kopsekolah ?? '',
    'banner1' => $settingData->banner1 ?? '',
    'banner2' => $settingData->banner2 ?? '',
    'banner3' => $settingData->banner3 ?? ''
];

$totalMediaSlots = count($mediaFields);
$uploadedMediaCount = count(array_filter($mediaFields, static fn($value) => trim((string) $value) !== ''));

$bankSoalStatus = $bankSoalOptions[$settingData->ba_aktif ?? null] ?? 'Belum Diatur';
$tokenStatus = $tokenOptions[$settingData->tkn_siswa ?? null] ?? 'Belum Diatur';
$modeStatus = $modeOptions[$settingData->mode_app ?? null] ?? 'Belum Diatur';

$satuanOptions = $satuan[$settingData->jenjang ?? '1'] ?? [];

$bankSoalDateTime = '';
if (!empty($settingData->ba_waktu) && $settingData->ba_waktu !== '0000-00-00 00:00:00') {
    $bankSoalDateObj = DateTime::createFromFormat('Y-m-d H:i:s', (string) $settingData->ba_waktu);
    if ($bankSoalDateObj instanceof DateTime) {
        $bankSoalDateTime = $bankSoalDateObj->format('Y-m-d\TH:i');
    }
}
?>
<style>
    .dropify-wrapper .dropify-message p {
        font-size: 0.85rem;
        line-height: 1.3;
    }

    .dropify-wrapper .dropify-message span.file-icon {
        font-size: 2.5rem;
    }
</style>
<main class="app-main bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4 border-0">
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="mb-1 font-weight-bold">Pengaturan Profil Sekolah</h5>
                        <div class="text-muted">Kelola identitas sekolah, kontak, serta pengaturan ujian dalam satu
                            tempat.</div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Panduan Pengisian
                            Profil</h5>
                        <ul class="ps-3 mb-0">
                            <li>Isi seluruh kolom bertanda bintang (<span class="text-danger">*</span>) untuk memastikan
                                data profil lengkap.</li>
                            <li>Pastikan jenjang dan satuan pendidikan sesuai agar tampilan rapor dan fitur CBT
                                menyesuaikan otomatis.</li>
                            <li>Unggah logo dan dokumen pendukung dalam format JPG/PNG maksimal 2MB agar tampil tajam
                                pada laporan.</li>
                            <li>Aktifkan token siswa hanya saat dibutuhkan untuk sesi ujian.</li>
                        </ul>
                    </div>

                    <?= form_open('', ['id' => 'savesetting']) ?>
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header"
                                    style="background: linear-gradient(135deg, #f6f9ff 0%, #dbe7ff 100%); color: #111;">
                                    <h6 class="mb-0">Identitas Sekolah</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Nama Aplikasi <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nama_aplikasi" class="form-control required"
                                            value="<?= $esc($settingData->nama_aplikasi ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Nama Sekolah <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nama_sekolah" class="form-control required"
                                            value="<?= $esc($settingData->sekolah ?? '') ?>" required>
                                    </div>
                                    <div class="row g-3">
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">NSS/NSM</label>
                                            <input type="text" name="nss" class="form-control"
                                                value="<?= $esc($settingData->nss ?? '') ?>" pattern="[0-9]{10,12}"
                                                title="NSS/NSM harus berupa angka 10-12 digit">
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">NPSN</label>
                                            <input type="text" name="npsn" class="form-control"
                                                value="<?= $esc($settingData->npsn ?? '') ?>" pattern="[0-9]{8}"
                                                title="NPSN harus berupa 8 digit angka">
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Jenjang <span
                                                    class="text-danger">*</span></label>
                                            <select id="jenjang" class="form-control required" name="jenjang" required>
                                                <option value="" disabled <?= empty($settingData->jenjang) ? 'selected' : '' ?>>Pilih Jenjang</option>
                                                <?php foreach ($jenjangOptions as $key => $label): ?>
                                                        <option value="<?= $key ?>" <?= ((string) ($settingData->jenjang ?? '')) === (string) $key ? 'selected' : '' ?>><?= html_escape($label) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Satuan Pendidikan <span
                                                    class="text-danger">*</span></label>
                                            <select id="satuan-pend" class="form-control required"
                                                name="satuan_pendidikan" required>
                                                <option value="" disabled <?= empty($settingData->satuan_pendidikan) ? 'selected' : '' ?>>Pilih Satuan Pendidikan</option>
                                                <?php $satuanOptionList = array_values($satuanOptions); ?>
                                                <?php foreach ($satuanOptionList as $idx => $label): ?>
                                                        <?php $optionValue = $idx + 1; ?>
                                                        <option value="<?= $optionValue ?>"
                                                            <?= ((string) ($settingData->satuan_pendidikan ?? '')) === (string) $optionValue ? 'selected' : '' ?>>
                                                            <?= html_escape($label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header"
                                    style="background: linear-gradient(135deg, #eef1ff 0%, #d8dcff 100%); color: #111;">
                                    <h6 class="mb-0">Alamat & Wilayah</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Alamat <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control required" name="alamat" rows="4"
                                            placeholder="Masukkan alamat lengkap"
                                            required><?= $esc($settingData->alamat ?? '') ?></textarea>
                                    </div>
                                    <div class="row g-3">
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Desa/Kelurahan <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="desa" class="form-control required"
                                                value="<?= $esc($settingData->desa ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Kecamatan <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="kec" class="form-control required"
                                                value="<?= $esc($settingData->kecamatan ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Kabupaten/Kota <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="kota" class="form-control required"
                                                value="<?= $esc($settingData->kota ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3 col-md-3">
                                            <label class="font-weight-semibold">Kode Pos</label>
                                            <input type="text" name="kode_pos" class="form-control"
                                                value="<?= $esc($settingData->kode_pos ?? '') ?>" pattern="[0-9]{5}"
                                                title="Kode pos terdiri dari 5 digit angka">
                                        </div>
                                        <div class="mb-3 col-md-3">
                                            <label class="font-weight-semibold">Provinsi <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="provinsi" class="form-control required"
                                                value="<?= $esc($settingData->provinsi ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header"
                                    style="background: linear-gradient(135deg, #eefbf3 0%, #d6f2e2 100%); color: #111;">
                                    <h6 class="mb-0">Kontak Sekolah</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Website</label>
                                        <input type="url" name="web" class="form-control"
                                            value="<?= $esc($settingData->web ?? '') ?>"
                                            placeholder="https://contoh-sekolah.sch.id">
                                    </div>
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="<?= $esc($settingData->email ?? '') ?>"
                                            placeholder="kontak@sekolah.sch.id">
                                    </div>
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Nomor Telepon</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">+62</span>
                                            </div>
                                            <input type="tel" name="tlp" class="form-control"
                                                value="<?= $esc($settingData->telp ?? '') ?>" pattern="[0-9]{6,15}"
                                                title="Masukkan nomor telepon tanpa spasi atau tanda hubung">
                                        </div>
                                    </div>
                                    <div class="mb-3 mb-0">
                                        <label class="font-weight-semibold">Faksimili</label>
                                        <input type="text" name="fax" class="form-control"
                                            value="<?= $esc($settingData->fax ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header"
                                    style="background: linear-gradient(135deg, #fff4e4 0%, #ffe3c3 100%); color: #111;">
                                    <h6 class="mb-0">Penanggung Jawab & Pengaturan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Kepala Sekolah <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="kepsek" class="form-control required"
                                                value="<?= $esc($settingData->kepsek ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">NIP Kepala Sekolah</label>
                                            <input type="text" name="nip" class="form-control"
                                                value="<?= $esc($settingData->nip ?? '') ?>" pattern="[0-9]{8,20}"
                                                title="Masukkan 8-20 digit angka">
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Proktor <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="proktor" class="form-control required"
                                                value="<?= $esc($settingData->proktor ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">NIP Proktor</label>
                                            <input type="text" name="nip_proktor" class="form-control"
                                                value="<?= $esc($settingData->nip_proktor ?? '') ?>"
                                                pattern="[0-9]{8,20}" title="Masukkan 8-20 digit angka">
                                        </div>
                                    </div>
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Status Bank Soal</label>
                                        <select id="ba_aktif" class="form-control required" name="ba_aktif" required>
                                            <option value="" disabled <?= empty($settingData->ba_aktif) ? 'selected' : '' ?>>Pilih Status</option>
                                            <?php foreach ($bankSoalOptions as $key => $label): ?>
                                                    <option value="<?= $key ?>" <?= ((string) ($settingData->ba_aktif ?? '')) === (string) $key ? 'selected' : '' ?>><?= html_escape($label) ?>
                                                    </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">Waktu Bank Soal</label>
                                        <input type="datetime-local" name="ba_waktu" class="form-control"
                                            value="<?= $esc($bankSoalDateTime) ?>">
                                    </div>
                                    <div class="row g-3">
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Tampilkan Token di Siswa</label>
                                            <select id="tkn_siswa" class="form-control required" name="tkn_siswa"
                                                required>
                                                <option value="" disabled <?= empty($settingData->tkn_siswa) ? 'selected' : '' ?>>Pilih Status</option>
                                                <?php foreach ($tokenOptions as $key => $label): ?>
                                                        <option value="<?= $key ?>" <?= ((string) ($settingData->tkn_siswa ?? '')) === (string) $key ? 'selected' : '' ?>><?= html_escape($label) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="font-weight-semibold">Mode Aplikasi</label>
                                            <select id="mode_app" class="form-control required" name="mode_app"
                                                required>
                                                <option value="" disabled <?= empty($settingData->mode_app) ? 'selected' : '' ?>>Pilih Mode</option>
                                                <?php foreach ($modeOptions as $key => $label): ?>
                                                        <option value="<?= $key ?>" <?= ((string) ($settingData->mode_app ?? '')) === (string) $key ? 'selected' : '' ?>><?= html_escape($label) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 mb-3">
                                        <label class="font-weight-semibold">API Key (OpenAI / Gemini)</label>
                                        <input type="text" name="api_key" class="form-control"
                                            value="<?= $esc($settingData->api_key ?? '') ?>"
                                            placeholder="Paste API Key disini...">
                                        <small class="text-muted">Kunci API diperlukan untuk fitur generate soal
                                            otomatis menggunakan AI.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary btn-sm mt-2" onclick="submitSetting()"><i
                                    class="bi bi-save me-1"></i>Simpan Pengaturan</button>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>

            <div class="card my-shadow border-0">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background: linear-gradient(135deg, #f6f9ff 0%, #e6f2ff 100%); color: #111;">
                    <h6 class="mb-0">Identitas Visual & Dokumen Pendukung</h6>
                    <span class="text-muted small">Format file JPG/PNG maksimal 2MB</span>
                </div>
                <div class="card-body">
                    <div class="callout callout-warning shadow-sm border border-warning mb-4">
                        <h6 class="font-weight-bold mb-2"><i class="bi bi-images me-2"></i>Panduan Upload</h6>
                        <ul class="ps-3 mb-0">
                            <li>Gunakan resolusi tinggi agar logo dan banner tampil tajam pada laporan.</li>
                            <li>Banner disarankan berukuran 1020x250 piksel dengan rasio 4:1.</li>
                            <li>Format transparan (PNG) ideal untuk tanda tangan digital dan cap sekolah.</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-stampel']) ?>
                            <label class="font-weight-semibold" for="stampel">Cap / Stampel Sekolah</label>
                            <input type="file" id="stampel" name="logo" class="dropify" data-max-file-size-preview="2M"
                                data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->stampel) ? base_url() . $esc($settingData->stampel) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-tandatangan']) ?>
                            <label class="font-weight-semibold" for="tanda-tangan">TTD Kepala Sekolah</label>
                            <input type="file" id="tanda-tangan" name="logo" class="dropify"
                                data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->tanda_tangan) ? base_url() . $esc($settingData->tanda_tangan) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-ttdproktor']) ?>
                            <label class="font-weight-semibold" for="ttdproktor">TTD Proktor</label>
                            <input type="file" id="ttdproktor" name="logo" class="dropify"
                                data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->ttdproktor) ? base_url() . $esc($settingData->ttdproktor) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-logo-kiri']) ?>
                            <label class="font-weight-semibold" for="logo-kiri">Logo Kiri / Aplikasi</label>
                            <input type="file" id="logo-kiri" name="logo" class="dropify"
                                data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->logo_kiri) ? base_url() . $esc($settingData->logo_kiri) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-logo-kanan']) ?>
                            <label class="font-weight-semibold" for="logo-kanan">Logo Kanan</label>
                            <input type="file" id="logo-kanan" name="logo" class="dropify"
                                data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->logo_kanan) ? base_url() . $esc($settingData->logo_kanan) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-kodescan']) ?>
                            <label class="font-weight-semibold" for="kodescan">QR Code</label>
                            <input type="file" id="kodescan" name="logo" class="dropify" data-max-file-size-preview="2M"
                                data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->kodescan) ? base_url() . $esc($settingData->kodescan) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-kopsekolah']) ?>
                            <label class="font-weight-semibold" for="kopsekolah">Kop Sekolah</label>
                            <input type="file" id="kopsekolah" name="logo" class="dropify"
                                data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->kopsekolah) ? base_url() . $esc($settingData->kopsekolah) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-banner1']) ?>
                            <label class="font-weight-semibold" for="banner1">Banner 1 (1020×250px)</label>
                            <input type="file" id="banner1" name="logo" class="dropify" data-max-file-size-preview="2M"
                                data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->banner1) ? base_url() . $esc($settingData->banner1) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-banner2']) ?>
                            <label class="font-weight-semibold" for="banner2">Banner 2 (1020×250px)</label>
                            <input type="file" id="banner2" name="logo" class="dropify" data-max-file-size-preview="2M"
                                data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->banner2) ? base_url() . $esc($settingData->banner2) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                            <?= form_open_multipart('', ['id' => 'set-banner3']) ?>
                            <label class="font-weight-semibold" for="banner3">Banner 3 (1020×250px)</label>
                            <input type="file" id="banner3" name="logo" class="dropify" data-max-file-size-preview="2M"
                                data-allowed-file-extensions="jpg jpeg png"
                                data-default-file="<?= !empty($settingData->banner3) ? base_url() . $esc($settingData->banner3) : '' ?>" />
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    // ... mediaMappings ...
    const mediaMappings = {
        'logo-kanan': 'logo_kanan',
        'logo-kiri': 'logo_kiri',
        'stampel': 'stampel',
        'tanda-tangan': 'tanda_tangan',
        'ttdproktor': 'ttdproktor',
        'kodescan': 'kodescan',
        'kopsekolah': 'kopsekolah',
        'banner1': 'banner1',
        'banner2': 'banner2',
        'banner3': 'banner3'
    };

    const baseUrl = typeof base_url !== 'undefined' ? base_url : '<?= base_url() ?>';
    const baseUrlNormalized = baseUrl.replace(/\/+$/, '');
    const escapeRegex = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    let csrf = {
        name: '<?= $this->security->get_csrf_token_name() ?>',
        hash: '<?= $this->security->get_csrf_hash() ?>'
    };

    // ... syncAjaxCsrf, updateCsrfFromResponse, appendCsrfToFormData code block (STANDARD)
    const syncAjaxCsrf = () => {
        const payload = {};
        payload[csrf.name] = csrf.hash;
        $.ajaxSetup({ data: payload });
    };

    const updateCsrfFromResponse = (response) => {
        if (response && response.csrfName && response.csrfHash) {
            csrf = {
                name: response.csrfName,
                hash: response.csrfHash
            };
            syncAjaxCsrf();
        }
    };

    const appendCsrfToFormData = (formData) => {
        if (formData instanceof FormData && csrf?.name && csrf?.hash) {
            formData.append(csrf.name, csrf.hash);
        }
    };

    syncAjaxCsrf();

    const toRelativePath = (url) => {
        if (!url) {
            return '';
        }
        const normalizedUrl = url.replace(/\/+$/, '');
        const regex = new RegExp('^' + escapeRegex(baseUrlNormalized) + '/?', 'i');
        return normalizedUrl.replace(regex, '').replace(/^\/+/, '');
    };

    let skipDropifyClear = false;

    const logos = {
        logo_kanan: '<?= $esc($settingData->logo_kanan ?? '') ?>',
        logo_kiri: '<?= $esc($settingData->logo_kiri ?? '') ?>',
        kodescan: '<?= $esc($settingData->kodescan ?? '') ?>',
        kopsekolah: '<?= $esc($settingData->kopsekolah ?? '') ?>',
        stampel: '<?= $esc($settingData->stampel ?? '') ?>',
        ttdproktor: '<?= $esc($settingData->ttdproktor ?? '') ?>',
        tanda_tangan: '<?= $esc($settingData->tanda_tangan ?? '') ?>',
        banner1: '<?= $esc($settingData->banner1 ?? '') ?>',
        banner2: '<?= $esc($settingData->banner2 ?? '') ?>',
        banner3: '<?= $esc($settingData->banner3 ?? '') ?>'
    };

    const satuanPend = <?= json_encode($satuan) ?>;

    function submitSetting() {
        $('#savesetting').submit();
    }

    $(document).ready(function () {
        ajaxcsrf(); // Existing system helper

        const drEvent = $('.dropify').dropify({
            messages: {
                default: 'Seret logo ke sini atau klik',
                replace: 'Seret atau klik untuk mengganti logo',
                remove: 'Hapus',
                error: 'Oops, ada kesalahan!'
            },
            error: {
                fileSize: 'Ukuran file terlalu besar (maks {{ value }}).',
                imageFormat: 'Format gambar tidak diizinkan (hanya {{ value }}).'
            }
        });

        drEvent.on('dropify.afterClear', function (event, element) {
            if (skipDropifyClear) {
                skipDropifyClear = false;
                return;
            }
            const inputId = element.element.id;
            const fieldKey = mediaMappings[inputId] || null;
            const $input = $(event.currentTarget);
            const defaultFile = $input.attr('data-default-file') || '';

            if (fieldKey) {
                logos[fieldKey] = '';
                if (defaultFile) {
                    deleteImage(defaultFile, fieldKey);
                }
            }

            $input.attr('data-default-file', '');
        });

        drEvent.on('dropify.errors', function () {
            showWarningToast('File tidak valid atau terlalu besar');
        });

        $('#jenjang').on('change', function () {
            const jenjangVal = $(this).val();
            const options = ['<option value="" disabled selected>Pilih Satuan Pendidikan</option>'];

            if (satuanPend[jenjangVal]) {
                satuanPend[jenjangVal].forEach(function (label, index) {
                    options.push(`<option value="${index + 1}">${label}</option>`);
                });
            }

            $('#satuan-pend').html(options.join(''));
        });

        $('#savesetting').on('submit', function (e) {
            e.preventDefault();

            const hasInput = $('.required').toArray().every(function (input) {
                return $(input).val().trim() !== '';
            });

            if (!hasInput) {
                Swal.fire({
                    title: 'Error',
                    text: 'Isi semua kolom yang bertanda bintang (*)',
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                text: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: base_url + 'profilsekolah/saveSetting', // MODIFIED URL
                type: 'POST',
                data: $(this).serialize() + '&' + $.param(logos),
                success: function (res) {
                    Swal.fire({
                        title: 'Sukses',
                        text: 'Pengaturan berhasil disimpan',
                        icon: 'success',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        window.location.href = base_url + 'profilsekolah'; // MODIFIED REDIRECT
                    });
                },
                error: function (xhr) {
                    let errorMsg = 'Gagal menyimpan pengaturan';
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errorMsg = err.Message || errorMsg;
                    } catch (error) {
                        console.error('Error parsing response:', xhr.responseText);
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error'
                    });
                }
            });
        });

        $('.dropify').on('change', function () {
            const input = this;
            const inputId = input.id;
            const fieldKey = mediaMappings[inputId] || null;

            if (!fieldKey || !(input.files && input.files[0])) {
                return;
            }

            if (input.files[0].size > 2 * 1024 * 1024) {
                showDangerToast('Ukuran file maksimum adalah 2MB');
                $(input).val('');
                return;
            }

            const form = new FormData();
            form.append('logo', input.files[0], input.files[0].name);
            appendCsrfToFormData(form);
            // MODIFIED UPLOAD URL
            uploadAttach(base_url + `profilsekolah/uploadFile/${fieldKey}`, form, fieldKey, inputId);
        });

        function uploadAttach(action, data, fieldKey, inputId) {
            $.ajax({
                type: 'POST',
                enctype: 'multipart/form-data',
                url: action,
                dataType: 'json',
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (response) {
                    updateCsrfFromResponse(response);
                    if (response && response.status) {
                        const fileUrl = response.src || '';
                        const relativePath = response.relative_path || toRelativePath(fileUrl);

                        logos[fieldKey] = relativePath;

                        const $input = $(`#${inputId}`);
                        if (fileUrl) {
                            $input.attr('data-default-file', fileUrl);
                        }
                        const dropifyInstance = $input.data('dropify');
                        if (dropifyInstance) {
                            skipDropifyClear = true;
                            dropifyInstance.resetPreview();
                            dropifyInstance.clearElement();
                            dropifyInstance.settings.defaultFile = fileUrl;
                            dropifyInstance.destroy();
                            dropifyInstance.init();
                            skipDropifyClear = false;
                        } else {
                            $input.dropify();
                            const newInstance = $input.data('dropify');
                            if (newInstance) {
                                skipDropifyClear = true;
                                newInstance.settings.defaultFile = fileUrl;
                                newInstance.init();
                                skipDropifyClear = false;
                            }
                        }

                        showSuccessToast('File berhasil diunggah');
                    } else {
                        const message = response?.src || 'Gagal mengunggah file';
                        showDangerToast(message);
                    }
                },
                error: function (xhr) {
                    const resp = xhr.responseJSON || {};
                    const errMessage = resp?.src || resp?.message || 'Gagal mengunggah file';
                    showDangerToast(errMessage);
                }
            });
        }

        function deleteImage(src, fieldKey) {
            if (!fieldKey) {
                return;
            }
            if (!src) {
                return;
            }

            const payload = { src: src, field: fieldKey };
            payload[csrf.name] = csrf.hash;

            $.ajax({
                type: 'POST',
                // MODIFIED DELETE URL
                url: base_url + 'profilsekolah/deleteFile',
                data: payload,
                cache: false,
                success: function (response) {
                    updateCsrfFromResponse(response);
                    console.log('File deleted:', response);
                },
                error: function (xhr) {
                    console.error('Delete error:', xhr.responseText);
                }
            });
        }
    });
</script>
