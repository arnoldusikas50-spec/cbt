<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.profile-avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 1rem;
}
.profile-avatar-wrapper img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border: 4px solid #ffffff;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transition: transform 0.3s ease;
}
.profile-avatar-wrapper:hover img {
    transform: scale(1.02);
}
.btn-camera-overlay {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #1a237e;
    color: white;
    border: 3px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    transition: all 0.2s ease;
    cursor: pointer;
}
.btn-camera-overlay:hover {
    background: #0d47a1;
    transform: scale(1.1);
    color: white;
}
.modern-nav-pills {
    background: #f8fafc;
    border-radius: 12px;
    padding: 0.35rem;
    border: 1px solid #e2e8f0;
}
.modern-nav-pills .nav-link {
    color: #64748b;
    font-weight: 600;
    border-radius: 8px;
    padding: 0.65rem 1rem;
    transition: all 0.2s ease;
    border: none;
    font-size: 0.875rem;
}
.modern-nav-pills .nav-link.active {
    background: #1a237e !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(26, 35, 126, 0.25);
}
.modern-nav-pills .nav-link:hover:not(.active) {
    background-color: #e2e8f0;
    color: #1a237e;
}
.premium-input-group {
    position: relative;
    width: 100%;
}
.p-group-icon {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 1.1rem;
    z-index: 5;
    pointer-events: none;
    transition: color 0.2s ease;
}
.premium-input-group:focus-within .p-group-icon {
    color: #1a237e;
}
.premium-input-group .form-control,
.premium-input-group .form-select {
    padding-left: 2.75rem !important;
    height: 2.75rem;
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}
.premium-input-group .form-control:focus,
.premium-input-group .form-select:focus {
    border-color: #1a237e;
    box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.12);
}
.crop-drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 2.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s ease;
    background: #f8fafc;
}
.crop-drop-zone:hover {
    border-color: #1a237e;
    background: #f1f5f9;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$fotoSiswa = $siswa->foto;
$defaultFoto = ($siswa->jenis_kelamin == 'P') ? 'assets/img/siswa-p.png' : 'assets/img/siswa-l.png';
$fallbackFoto = 'assets/img/siswa_sm.png';

$displayFoto = (!empty($fotoSiswa) && file_exists(FCPATH . $fotoSiswa))
    ? base_url($fotoSiswa)
    : (file_exists(FCPATH . $defaultFoto) ? base_url($defaultFoto) : base_url($fallbackFoto));
?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-bounding-box text-primary me-2"></i>
                    Edit Profil Siswa
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('datasiswa') ?>" class="text-decoration-none">Data Siswa</a>
                    </li>
                    <li class="breadcrumb-item active">Edit Profil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Left Sidebar Profile -->
            <div class="col-xl-4 col-lg-5">
                <div class="card card-primary card-outline shadow-sm text-center h-100">
                    <div class="card-body p-4 d-flex flex-column h-100">
                        <div class="profile-header mb-4 mt-2">
                            <div class="profile-avatar-wrapper">
                                <img src="<?= $displayFoto ?>" 
                                    class="rounded-circle" 
                                    alt="Foto Siswa"
                                    onerror="this.onerror=null; this.src='<?= base_url($defaultFoto) ?>';">
                                <button type="button" 
                                    class="btn-camera-overlay"
                                    data-bs-toggle="modal" data-bs-target="#editFotoModal"
                                    title="Ganti Foto Profil">
                                    <i class="bi bi-camera-fill"></i>
                                </button>
                            </div>
                            <h4 class="mt-3 fw-bold text-dark mb-1" style="font-size: 1.25rem;"><?= esc($siswa->nama) ?></h4>
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-3 flex-wrap">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 rounded-pill font-monospace" style="font-size: 0.75rem;">
                                    NIS: <?= esc($siswa->nis) ?>
                                </span>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5 rounded-pill font-monospace" style="font-size: 0.75rem;">
                                    NISN: <?= esc($siswa->nisn ?: '-') ?>
                                </span>
                            </div>
                        </div>

                        <div class="d-grid gap-2.5 mb-4">
                            <button type="button" class="btn btn-warning text-dark rounded-pill py-2.5 px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#editLoginModal">
                                <i class="bi bi-shield-lock-fill me-1.5"></i> Password Login
                            </button>
                            <button type="button" class="btn btn-outline-danger rounded-pill py-2 px-3" onclick="deleteImage(true)">
                                <i class="bi bi-trash3-fill me-1.5"></i> Hapus Foto Profil
                            </button>
                        </div>
                        
                        <div class="mt-auto text-start p-3 bg-light rounded-3 border">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-2" style="font-size: 0.68rem; letter-spacing: 0.5px;">Informasi Akademik</label>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-2" style="width: 10px; height: 10px; padding: 0; border-radius: 50%;"></span>
                                    <span class="fw-bold text-dark small">Status: Aktif</span>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.75rem;">
                                    TP <?= esc($tp_active->tahun ?? '-') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content Form -->
            <div class="col-xl-8 col-lg-7">
                <form id="form-siswa" action="<?= site_url('datasiswa/updatedata') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_siswa" value="<?= $siswa->id_siswa ?>">
                    <input type="hidden" name="method" value="edit">

                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header border-bottom p-2 bg-white">
                            <ul class="nav nav-pills nav-fill modern-nav-pills" id="editTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="main-tab" data-bs-toggle="tab" href="#datasiswa" role="tab">
                                        <i class="bi bi-person-fill me-1.5"></i> Bibliografi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="detail-tab" data-bs-toggle="tab" href="#biosiswa" role="tab">
                                        <i class="bi bi-journal-text me-1.5"></i> Detail Bio
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="family-tab" data-bs-toggle="tab" href="#ortusiswa" role="tab">
                                        <i class="bi bi-people-fill me-1.5"></i> Keluarga
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="wali-tab" data-bs-toggle="tab" href="#walisiswa" role="tab">
                                        <i class="bi bi-person-fill-check me-1.5"></i> Wali / Kontak
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content">
                                <!-- Tab 1: Bibliografi -->
                                <div class="tab-pane fade show active" id="datasiswa" role="tabpanel">
                                    <div class="row g-3">
                                        <?php foreach ($input_data as $data): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;"><?= esc($data->label) ?></label>
                                                <div class="premium-input-group">
                                                    <span class="p-group-icon"><i class="<?= esc($data->icon) ?>"></i></span>
                                                    <?php if ($data->name == 'jenis_kelamin'): ?>
                                                        <select class="form-select" name="jenis_kelamin" required>
                                                            <option value="L" <?= $data->value == 'L' ? 'selected' : '' ?>>Laki-laki (Ikhwan)</option>
                                                            <option value="P" <?= $data->value == 'P' ? 'selected' : '' ?>>Perempuan (Akhwat)</option>
                                                        </select>
                                                    <?php elseif ($data->name == 'status'): ?>
                                                        <select class="form-select" name="status" required>
                                                            <option value="1" <?= $data->value == '1' ? 'selected' : '' ?>>AKTIF</option>
                                                            <option value="2" <?= $data->value == '2' ? 'selected' : '' ?>>LULUS</option>
                                                            <option value="3" <?= $data->value == '3' ? 'selected' : '' ?>>PINDAH</option>
                                                            <option value="4" <?= $data->value == '4' ? 'selected' : '' ?>>KELUAR</option>
                                                        </select>
                                                    <?php elseif ($data->name == 'kelas_awal'): ?>
                                                        <select class="form-select" name="kelas_awal" required>
                                                            <?php 
                                                            $opsis = ($setting->jenjang == 1) ? [1,2,3,4,5,6] : (($setting->jenjang == 2) ? [7,8,9] : [10,11,12]);
                                                            foreach ($opsis as $kls): ?>
                                                                <option value="<?= $kls ?>" <?= $data->value == $kls ? 'selected' : '' ?>>Tingkat <?= $kls ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <input type="<?= esc($data->type) ?>" name="<?= esc($data->name) ?>" value="<?= esc($data->value) ?>" 
                                                            class="form-control" placeholder="Masukkan <?= esc($data->label) ?>" 
                                                            <?= ($data->name == 'nama' || $data->name == 'nis') ? 'required' : '' ?>>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Tab 2: Detail Bio -->
                                <div class="tab-pane fade" id="biosiswa" role="tabpanel">
                                    <div class="row g-3">
                                        <?php foreach ($input_bio as $bio): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;"><?= esc($bio->label) ?></label>
                                                <div class="premium-input-group">
                                                    <span class="p-group-icon"><i class="<?= esc($bio->icon) ?>"></i></span>
                                                    <?php if ($bio->name == 'agama'): ?>
                                                        <?php $arrAgama = ["Islam", "Kristen", "Katolik", "Hindu", "Buddha", "Khonghucu", "Kepercayaan thd Tuhan YME", "Lainnya"]; ?>
                                                        <select class="form-select" name="agama">
                                                            <option value="">Pilih Agama</option>
                                                            <?php foreach ($arrAgama as $agm): ?>
                                                                <option value="<?= $agm ?>" <?= $bio->value == $agm ? 'selected' : '' ?>><?= $agm ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <input type="<?= esc($bio->type) ?>" name="<?= esc($bio->name) ?>" value="<?= esc($bio->value) ?>" 
                                                            class="form-control" placeholder="Masukkan <?= esc($bio->label) ?>">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Tab 3: Keluarga -->
                                <div class="tab-pane fade" id="ortusiswa" role="tabpanel">
                                    <div class="row g-3">
                                        <?php foreach ($input_ortu as $ortu): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;"><?= esc($ortu->label) ?></label>
                                                <div class="premium-input-group">
                                                    <span class="p-group-icon"><i class="<?= esc($ortu->icon) ?>"></i></span>
                                                    <?php if ($ortu->name == 'status_keluarga'): ?>
                                                        <select class="form-select" name="status_keluarga">
                                                            <option value="1" <?= $ortu->value == '1' ? 'selected' : '' ?>>Anak Kandung</option>
                                                            <option value="2" <?= $ortu->value == '2' ? 'selected' : '' ?>>Anak Tiri</option>
                                                            <option value="3" <?= $ortu->value == '3' ? 'selected' : '' ?>>Anak Angkat</option>
                                                        </select>
                                                    <?php else: ?>
                                                        <input type="<?= esc($ortu->type) ?>" name="<?= esc($ortu->name) ?>" value="<?= esc($ortu->value) ?>" 
                                                            class="form-control" placeholder="Masukkan <?= esc($ortu->label) ?>">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Tab 4: Wali -->
                                <div class="tab-pane fade" id="walisiswa" role="tabpanel">
                                    <div class="row g-3">
                                        <?php foreach ($input_wali as $wali): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;"><?= esc($wali->label) ?></label>
                                                <div class="premium-input-group">
                                                    <span class="p-group-icon"><i class="<?= esc($wali->icon) ?>"></i></span>
                                                    <input type="<?= esc($wali->type) ?>" name="<?= esc($wali->name) ?>" value="<?= esc($wali->value) ?>" 
                                                        class="form-control" placeholder="Masukkan <?= esc($wali->label) ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light border-top py-3 px-4 d-flex justify-content-end gap-2">
                            <button onclick="window.history.back();" type="button" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </button>
                            <button type="reset" class="btn btn-outline-warning text-dark rounded-pill px-4">
                                <i class="bi bi-arrow-repeat me-1"></i> Reset
                            </button>
                            <button type="submit" id="btn-save" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="bi bi-save-fill me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Foto Crop -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
<div class="modal fade" id="editFotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 px-4 py-3 bg-primary text-white">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-crop me-2"></i>Crop Foto Profil</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="crop-step-pick">
                    <div class="crop-drop-zone" id="crop-drop-zone">
                        <i class="bi bi-cloud-arrow-up display-4 text-primary d-block mb-2"></i>
                        <div class="fw-bold text-dark fs-6">Klik atau seret gambar ke sini</div>
                        <div class="text-muted small mt-1">Format: JPG, PNG &bull; Maksimal: 5 MB</div>
                        <input type="file" id="crop-file-input" accept="image/jpeg,image/png,image/jpg" class="d-none">
                    </div>
                </div>
                <div id="crop-step-edit" class="d-none">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div style="max-height: 400px; overflow: hidden; background: #1e293b; border-radius: 12px;">
                                <img id="crop-image" src="" alt="Crop Preview" style="display: block; max-width: 100%;">
                            </div>
                            <div class="d-flex align-items-center justify-content-center gap-2 p-2.5 bg-light rounded-bottom border border-top-0">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="crop-rotate-left" title="Putar Kiri"><i class="bi bi-arrow-counterclockwise"></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="crop-rotate-right" title="Putar Kanan"><i class="bi bi-arrow-clockwise"></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="crop-zoom-in" title="Perbesar"><i class="bi bi-zoom-in"></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="crop-zoom-out" title="Perkecil"><i class="bi bi-zoom-out"></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="crop-reset" title="Reset"><i class="bi bi-arrow-repeat"></i></button>
                                <div class="vr mx-1"></div>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-3" id="crop-change-file" title="Ganti File"><i class="bi bi-folder2-open"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="text-muted small fw-bold text-uppercase mb-2" style="letter-spacing:.05em;">Preview Foto</div>
                            <div id="crop-preview-circle" style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; border: 3px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 0 auto 0.5rem;"></div>
                            <span class="text-muted small d-block mb-3">Tampilan Profil</span>
                            <div id="crop-preview-square" style="width: 80px; height: 80px; border-radius: 12px; overflow: hidden; border: 2px solid #e2e8f0; margin: 0 auto 0.25rem;"></div>
                            <span class="text-muted small">Thumbnail</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-none" id="btn-save-crop">
                    <i class="bi bi-check-lg me-1"></i>Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Login Credentials -->
<div class="modal fade" id="editLoginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="form-login">
                <div class="modal-header border-0 px-4 py-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold mb-0">
                        <i class="bi bi-shield-lock-fill me-2"></i> Update Password Siswa
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm small mb-4 rounded-3 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-5 me-3 text-info"></i>
                        <span>Username bersifat statis. Perubahan password akan langsung membatalkan sesi aktif siswa.</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Username Terdaftar</label>
                        <div class="premium-input-group opacity-75">
                            <span class="p-group-icon"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" value="<?= esc($siswa->username) ?>" readonly disabled>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Password Baru</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan minimal 6 karakter">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#form-siswa').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btn-save');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        Swal.fire({
            title: "Memproses Data",
            text: "Sinkronisasi profil dengan database server....",
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: $(this).attr('action'),
            data: $(this).serialize(),
            type: 'POST',
            success: function(data) {
                if (data.insert) {
                    Swal.fire({ icon: 'success', title: 'Sinkronisasi Berhasil', text: data.text, customClass: { popup: 'rounded-4' } }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Proses Ditolak', text: data.text || "Terjadi anomali saat menyimpan", customClass: { popup: 'rounded-4' } });
                    btn.prop('disabled', false).html('<i class="bi bi-save-fill me-1"></i> Simpan Perubahan');
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Connection Lost', text: "Gagal menghubungi API server", customClass: { popup: 'rounded-4' } });
                btn.prop('disabled', false).html('<i class="bi bi-save-fill me-1"></i> Simpan Perubahan');
            }
        });
    });

    // ══════════════ CROP PHOTO LOGIC ══════════════
    (function() {
        const cropScript = document.createElement('script');
        cropScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js';
        cropScript.onload = function() { initCropFeature(); };
        document.head.appendChild(cropScript);
    })();

    function initCropFeature() {
        let cropper = null;
        const $cropImage = $('#crop-image');
        const $stepPick = $('#crop-step-pick');
        const $stepEdit = $('#crop-step-edit');
        const $btnSaveCrop = $('#btn-save-crop');
        const $cropFileInput = $('#crop-file-input');
        const $dropZone = $('#crop-drop-zone');

        $cropFileInput.on('click', (e) => e.stopPropagation());
        $dropZone.on('click', () => $cropFileInput[0].click());
        $dropZone.on('dragover', function(e) { e.preventDefault(); $(this).css('border-color', '#1a237e').css('background', '#f1f5f9'); });
        $dropZone.on('dragleave drop', function(e) { e.preventDefault(); $(this).css('border-color', '#cbd5e1').css('background', '#f8fafc'); if (e.type === 'drop' && e.originalEvent.dataTransfer.files.length) loadImage(e.originalEvent.dataTransfer.files[0]); });
        $cropFileInput.on('change', function() { if (this.files && this.files[0]) loadImage(this.files[0]); });

        function loadImage(file) {
            if (!file.type.match(/image\/(jpeg|png|jpg)/)) { Swal.fire('Format Salah', 'Hanya file JPG/PNG yang diperbolehkan.', 'warning'); return; }
            if (file.size > 5 * 1024 * 1024) { Swal.fire('File Terlalu Besar', 'Ukuran maksimal file adalah 5 MB.', 'warning'); return; }
            const reader = new FileReader();
            reader.onload = function(e) {
                $cropImage.attr('src', e.target.result);
                $stepPick.addClass('d-none');
                $stepEdit.removeClass('d-none');
                $btnSaveCrop.removeClass('d-none');
                if (cropper) cropper.destroy();
                cropper = new Cropper($cropImage[0], {
                    aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 0.85,
                    responsive: true, guides: true, center: true, highlight: true,
                    cropBoxMovable: true, cropBoxResizable: true,
                    preview: '#crop-preview-circle, #crop-preview-square',
                });
            };
            reader.readAsDataURL(file);
        }

        $('#crop-rotate-left').click(() => cropper && cropper.rotate(-90));
        $('#crop-rotate-right').click(() => cropper && cropper.rotate(90));
        $('#crop-zoom-in').click(() => cropper && cropper.zoom(0.1));
        $('#crop-zoom-out').click(() => cropper && cropper.zoom(-0.1));
        $('#crop-reset').click(() => cropper && cropper.reset());
        $('#crop-change-file').click(() => { $cropFileInput.val(''); $cropFileInput[0].click(); });

        $('#editFotoModal').on('hidden.bs.modal', function() {
            if (cropper) { cropper.destroy(); cropper = null; }
            $stepPick.removeClass('d-none'); $stepEdit.addClass('d-none'); $btnSaveCrop.addClass('d-none'); $cropFileInput.val('');
        });

        $btnSaveCrop.click(function() {
            if (!cropper) return;
            const canvas = cropper.getCroppedCanvas({ width: 500, height: 500, imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('foto', blob, 'profile_crop.jpg');
                formData.append(CSRF_NAME, CSRF_HASH);

                Swal.fire({ title: 'Menyimpan...', html: 'Mengompres dan mengunggah foto profil', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: '<?= site_url('datasiswa/uploadFile/' . $siswa->id_siswa) ?>',
                    type: 'POST', data: formData, processData: false, contentType: false,
                    success: function(data) {
                        if (data.status) {
                            $('#editFotoModal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Foto Diperbarui!', text: 'Foto profil berhasil di-crop dan disimpan.', timer: 2500, showConfirmButton: false, customClass: { popup: 'rounded-4' } }).then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Upload Gagal', text: data.message || 'File storage menolak file ini.', customClass: { popup: 'rounded-4' } });
                        }
                    },
                    error: () => Swal.fire({ icon: 'error', title: 'API Error', text: 'Terjadi kesalahan saat mengupload foto.', customClass: { popup: 'rounded-4' } })
                });
            }, 'image/jpeg', 0.92);
        });
    }

    $('#form-login').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({ title: "Mengupdate Password", allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        
        $.ajax({
            url: '<?= site_url('datasiswa/updatedata') ?>',
            data: $(this).serialize() + '&id_siswa=<?= $siswa->id_siswa ?>&method=edit&' + CSRF_NAME + '=' + CSRF_HASH,
            type: 'POST',
            success: function(res) {
                if (res.insert) {
                    Swal.fire({ icon: 'success', title: 'Akses Diperbarui', text: res.text, customClass: { popup: 'rounded-4' } }).then(() => {
                        $('#editLoginModal').modal('hide');
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal Update', text: res.text, customClass: { popup: 'rounded-4' } });
                }
            }
        });
    });
});

function deleteImage(confirm) {
    if (confirm) {
        Swal.fire({
            title: "Hapus Foto Profil?",
            text: "Avatar akan dikembalikan ke settingan gender default. Aksi ini permanen.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ef4444",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            customClass: { popup: 'rounded-4', confirmButton: 'btn btn-danger rounded-pill px-4 fw-bold', cancelButton: 'btn btn-light rounded-pill px-4 fw-bold border' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('datasiswa/updatedata') ?>',
                    data: {
                        id_siswa: '<?= $siswa->id_siswa ?>',
                        foto: '',
                        method: 'edit',
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    type: 'POST',
                    success: function(res) {
                        window.location.reload();
                    }
                });
            }
        });
    }
}
</script>
<?= $this->endSection() ?>
