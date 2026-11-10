<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/dropify/css/dropify.min.css') ?>" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />

<style>
    :root {
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-200: #c7d2fe;
        --color-primary-300: #a5b4fc;
        --color-primary-400: #818cf8;
        --color-primary-500: #6366f1;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;

        --color-success-50: #f0fdf4;
        --color-success-600: #16a34a;
        --color-warning-50: #fffbeb;
        --color-warning-600: #d97706;
        --color-danger-50: #fef2f2;
        --color-danger-600: #dc2626;

        --color-neutral-50: #f9fafb;
        --color-neutral-100: #f3f4f6;
        --color-neutral-200: #e5e7eb;
        --color-neutral-300: #d1d5db;
        --color-neutral-400: #9ca3af;
        --color-neutral-500: #6b7280;
        --color-neutral-600: #4b5563;
        --color-neutral-700: #374151;

        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
        --radius-full: 9999px;

        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);

        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --transition-base: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: 'Inter', sans-serif !important;
        background-color: #f8fafc !important;
    }

    /* ============================================
       TYPOGRAPHY & HEADERS
       ============================================ */
    .text-primary-gradient {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    /* ============================================
       PREMIUM COMPONENTS
       ============================================ */
    .pas-card {
        background: white;
        border-radius: var(--radius-2xl);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .pas-card__header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--color-neutral-100);
        background: white;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--color-neutral-800);
    }

    .pas-card__title-icon {
        width: 40px;
        height: 40px;
        background: var(--color-primary-50);
        color: var(--color-primary-600);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    /* ============================================
       PREMIUM INPUT GROUPS
       ============================================ */
    .premium-input-group {
        position: relative;
        width: 100%;
    }

    .p-group-icon {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-neutral-400);
        font-size: 1.2rem;
        z-index: 5;
        pointer-events: none;
        transition: var(--transition-base);
    }

    .premium-input-group:focus-within .p-group-icon {
        color: var(--color-primary-600);
    }

    .premium-input-group .form-control,
    .premium-input-group .form-select {
        padding-left: 3.25rem !important;
        height: 3.5rem;
        background-color: var(--color-neutral-50);
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-lg);
        font-size: var(--font-size-sm);
        transition: var(--transition-base);
    }

    .premium-input-group .form-control:focus,
    .premium-input-group .form-select:focus {
        border-color: var(--color-primary-400);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background-color: white;
        outline: none;
    }

    /* ============================================
       BUTTONS
       ============================================ */
    .pas-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        font-weight: 600;
        font-size: var(--font-size-sm);
        border-radius: var(--radius-lg);
        transition: var(--transition-base);
        cursor: pointer;
        border: 1px solid transparent;
        text-decoration: none !important;
    }

    .pas-btn--primary {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    .pas-btn--primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
        color: white;
    }

    .pas-btn--white {
        background: white;
        color: var(--color-neutral-700);
        border-color: var(--color-neutral-200);
    }

    .pas-btn--white:hover {
        background: var(--color-neutral-50);
        color: var(--color-neutral-900);
    }

    .pas-btn--danger-soft {
        background: var(--color-danger-50);
        color: var(--color-danger-600);
    }

    .pas-btn--danger-soft:hover {
        background: var(--color-danger-600);
        color: white;
    }

    /* ============================================
       TABS MODAL
       ============================================ */
    .nav-tabs-premium {
        border-bottom: 2px solid var(--color-neutral-100);
        gap: 1rem;
    }

    .nav-tabs-premium .nav-link {
        border: none;
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: var(--color-neutral-500);
        position: relative;
        transition: var(--transition-base);
    }

    .nav-tabs-premium .nav-link.active {
        color: var(--color-primary-600);
        background: transparent;
    }

    .nav-tabs-premium .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--color-primary-600);
        border-radius: 2px;
    }

    #sig-canvas {
        border: 2px dashed var(--color-neutral-300);
        border-radius: var(--radius-xl);
        cursor: crosshair;
        background-color: #ffffff;
        width: 100%;
        height: 200px;
        touch-action: none;
    }

    .avatar-upload-wrapper {
        position: relative;
        display: inline-block;
    }

    .avatar-preview {
        width: 160px;
        height: 160px;
        border-radius: var(--radius-full);
        border: 4px solid white;
        box-shadow: var(--shadow-md);
        object-fit: cover;
    }

    /* ── Crop Modal Styles ── */
    .crop-container {
        max-height: 400px;
        overflow: hidden;
        background: #1e293b;
        border-radius: 12px;
    }

    .crop-container img {
        display: block;
        max-width: 100%;
    }

    .crop-preview-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .crop-preview-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .crop-preview-square {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e2e8f0;
    }

    .crop-toolbar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
        border-top: 1px solid #e2e8f0;
    }

    .crop-toolbar .btn {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 0;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .crop-toolbar .btn:hover {
        transform: scale(1.1);
    }

    .crop-drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 2.5rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s;
        background: #f8fafc;
    }

    .crop-drop-zone:hover {
        border-color: var(--color-primary-500);
        background: rgba(99, 102, 241, 0.04);
    }

    .crop-drop-zone.dragover {
        border-color: var(--color-primary-500);
        background: rgba(99, 102, 241, 0.08);
    }
</style>

<!-- Content Header -->
<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark" style="font-family: 'Inter', sans-serif; letter-spacing: -1.5px;">
                    <span class="text-primary-gradient"><i
                            class="bi bi-person-badge me-2"></i></span><?= esc($judul ?? 'Profil Panitia') ?>
                </h3>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"
                            class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Edit Profil') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Sidebar Profile -->
            <div class="col-xl-4 col-md-5">
                <div class="pas-card text-center p-4">
                    <div class="avatar-upload-wrapper mb-4">
                        <?php
                        $foto = !empty($panitia->foto) && file_exists(FCPATH . $panitia->foto)
                            ? base_url($panitia->foto)
                            : base_url('assets/img/logo.png');
                        ?>
                        <img id="img-foto-preview" src="<?= $foto ?>" class="avatar-preview" alt="Avatar">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#editFotoModal"
                            class="pas-btn pas-btn--primary p-2 rounded-circle position-absolute bottom-0 end-0 border-white border-2"
                            style="width: 42px; height: 42px; border: 4px solid white !important;">
                            <i class="bi bi-camera-fill"></i>
                        </button>
                    </div>

                    <h4 class="fw-bold text-dark mb-1"><?= esc($panitia->nama_panitia ?? 'Panitia') ?></h4>
                    <p class="text-muted smaller mb-4">
                        <span
                            class="pas-badge pas-badge--primary mb-2"><?= esc(ucwords($panitia->jabatan ?? 'Panitia')) ?></span>
                        <br>
                        <?= esc($panitia->instansi ?? '-') ?>
                    </p>

                    <div class="d-grid gap-2 mb-4">
                        <button type="button" class="pas-btn pas-btn--white w-100" data-bs-toggle="modal"
                            data-bs-target="#editLoginModal">
                            <i class="bi bi-shield-lock me-2 text-warning"></i> Keamanan Akun
                        </button>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="text-start">
                        <h6 class="text-muted smaller fw-bold text-uppercase tracking-wider mb-3">Tanda Tangan Digital
                        </h6>
                        <div class="pas-card bg-light border-0 p-3 text-center"
                            style="min-height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <?php $hasTtd = !empty($panitia->ttd) && file_exists(FCPATH . $panitia->ttd); ?>
                            <div id="ttd-placeholder" class="text-muted <?= $hasTtd ? 'd-none' : '' ?>">
                                <i class="bi bi-pen d-block fs-2 mb-2 opacity-25"></i>
                                <span class="smaller">Belum ada tanda tangan</span>
                            </div>
                            <img src="<?= $hasTtd ? base_url($panitia->ttd) : '' ?>" alt="TTD" id="img-ttd-preview"
                                class="img-fluid <?= !$hasTtd ? 'd-none' : '' ?>"
                                style="max-height: 100px; filter: contrast(1.2);">

                            <div class="mt-3 d-flex gap-2">
                                <button type="button" class="pas-btn pas-btn--primary pas-btn--sm px-3"
                                    id="btn-create-ttd">
                                    <i class="bi bi-plus-lg"></i> <?= $hasTtd ? 'Update' : 'Buat Baru' ?>
                                </button>
                                <button type="button"
                                    class="pas-btn pas-btn--danger-soft pas-btn--sm px-3 <?= !$hasTtd ? 'd-none' : '' ?>"
                                    id="btn-delete-ttd">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Content -->
            <div class="col-xl-8 col-md-7">
                <div class="pas-card">
                    <div class="pas-card__header p-0">
                        <ul class="nav nav-tabs nav-tabs-premium px-4" id="panitiaEditTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab"
                                    data-bs-target="#profile-pane" type="button" role="tab">Informasi Dasar</button>
                            </li>
                        </ul>
                    </div>

                    <form id="form-profile" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_panitia" id="id_panitia"
                            value="<?= $panitia->id_panitia ?? '' ?>">
                        <div class="card-body p-4">
                            <div class="tab-content" id="panitiaEditTabsContent">
                                <!-- Profile Tab -->
                                <div class="tab-pane fade show active" id="profile-pane" role="tabpanel">
                                    <div class="row g-4">

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Nama
                                                Lengkap</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control" name="nama_panitia"
                                                    value="<?= esc($panitia->nama_panitia ?? '') ?>"
                                                    placeholder="Nama Lengkap">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Email
                                                Address</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-envelope"></i></span>
                                                <input type="email" class="form-control" name="email"
                                                    value="<?= esc($panitia->email ?? '') ?>"
                                                    placeholder="email@example.com">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">NIP
                                                / NUPTK</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-card-list"></i></span>
                                                <input type="text" class="form-control" name="nip"
                                                    value="<?= esc($panitia->nip ?? '') ?>" placeholder="Masukkan NIP">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">No.
                                                Handphone</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-whatsapp"></i></span>
                                                <input type="text" class="form-control" name="no_hp"
                                                    value="<?= esc($panitia->no_hp ?? '') ?>" placeholder="08xxxx">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Jenis
                                                Kelamin</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-gender-ambiguous"></i></span>
                                                <select class="form-select" name="jenis_kelamin">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="L" <?= ($panitia->jenis_kelamin ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                    <option value="P" <?= ($panitia->jenis_kelamin ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Agama</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-moon-stars"></i></span>
                                                 <select class="form-select" name="agama">
                                                     <option value="">-- Pilih --</option>
                                                     <?php $agamas = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Kepercayaan thd Tuhan YME', 'Lainnya'];
                                                     foreach ($agamas as $ag): ?>
                                                        <option value="<?= $ag ?>" <?= ($panitia->agama ?? '') == $ag ? 'selected' : '' ?>><?= $ag ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Instansi
                                                / Sekolah Asal</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-building"></i></span>
                                                <input type="text" class="form-control" name="instansi"
                                                    value="<?= esc($panitia->instansi ?? '') ?>"
                                                    placeholder="Nama Sekolah">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Jabatan
                                                Aktif</label>
                                            <?php
                                            $isPengawas = isset($is_pengawas) ? $is_pengawas : (isset($panitia) && isset($panitia->jabatan) && strtolower($panitia->jabatan) === 'pengawas');
                                            $currentJabatan = strtolower($panitia->jabatan ?? '');
                                            if ($isPengawas && empty($currentJabatan))
                                                $currentJabatan = 'pengawas';
                                            ?>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-person-workspace"></i></span>
                                                <select class="form-select" name="jabatan" <?= $isPengawas ? 'disabled' : '' ?>>
                                                    <option value="">-- Pilih Jabatan --</option>
                                                    <option value="panitia" <?= $currentJabatan === 'panitia' ? 'selected' : '' ?>>Panitia</option>
                                                    <option value="pengawas" <?= $isPengawas ? 'selected' : '' ?>>
                                                        Pengawas</option>
                                                </select>
                                            </div>
                                            <?php if ($isPengawas): ?>
                                                <input type="hidden" name="jabatan" value="pengawas">
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-12">
                                            <label
                                                class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2 text-primary">Ruang
                                                / Lokasi Tugas Saat Ini</label>
                                            <div class="premium-input-group">
                                                <span class="p-group-icon"><i class="bi bi-geo-alt"></i></span>
                                                <select class="form-select" name="ruang">
                                                    <option value="">-- Pilih Ruang --</option>
                                                    <?php foreach ($ruang_options as $id => $name): ?>
                                                        <option value="<?= $id ?>" <?= ($panitia->ruang ?? '') == $id ? 'selected' : '' ?>><?= $name ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pas-card__header border-top d-flex align-items-center justify-content-between p-4">
                            <div class="smaller text-muted">
                                <i class="bi bi-info-circle me-1 text-primary"></i> <span class="fw-bold">Tips:</span>
                                Periksa kembali data sebelum menyimpan.
                            </div>
                            <button type="submit" class="pas-btn pas-btn--primary px-5 py-3">
                                <i class="bi bi-cloud-check-fill me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Crop Modal -->
<div class="modal fade" id="editFotoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem; overflow: hidden;">
            <div class="modal-header border-0 bg-white px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="bi bi-crop text-primary me-2"></i>Crop Foto Profil
                    </h5>
                    <small class="text-muted">Pilih area wajah yang ingin dijadikan foto profil</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-2">
                <div id="crop-step-pick">
                    <div class="crop-drop-zone" id="crop-drop-zone">
                        <i class="bi bi-cloud-arrow-up" style="font-size: 2.5rem; color: var(--color-primary-500);"></i>
                        <div class="fw-semibold mt-2" style="color: #334155;">Klik atau seret gambar ke sini</div>
                        <div class="text-muted small mt-1">Format: JPG, PNG &bull; Maks: 5 MB</div>
                        <input type="file" id="crop-file-input" accept="image/jpeg,image/png,image/jpg" class="d-none">
                    </div>
                </div>
                <div id="crop-step-edit" class="d-none">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="crop-container">
                                <img id="crop-image" src="" alt="Crop Preview">
                            </div>
                            <div class="crop-toolbar">
                                <button type="button" class="btn btn-outline-secondary" id="crop-rotate-left"
                                    title="Putar Kiri"><i class="bi bi-arrow-counterclockwise"></i></button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-rotate-right"
                                    title="Putar Kanan"><i class="bi bi-arrow-clockwise"></i></button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-zoom-in"
                                    title="Perbesar"><i class="bi bi-zoom-in"></i></button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-zoom-out"
                                    title="Perkecil"><i class="bi bi-zoom-out"></i></button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-reset" title="Reset"><i
                                        class="bi bi-arrow-repeat"></i></button>
                                <div class="vr mx-1"></div>
                                <button type="button" class="btn btn-outline-primary" id="crop-change-file"
                                    title="Ganti File"><i class="bi bi-folder2-open"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="crop-preview-section">
                                <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.05em;">
                                    Preview</div>
                                <div class="crop-preview-circle" id="crop-preview-circle"></div>
                                <span class="text-muted" style="font-size:.7rem;">Tampilan profil</span>
                                <div class="crop-preview-square" id="crop-preview-square"></div>
                                <span class="text-muted" style="font-size:.7rem;">Thumbnail</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white px-4 pb-4 pt-2">
                <button type="button" class="pas-btn pas-btn--white px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="pas-btn pas-btn--primary px-4 d-none" id="btn-save-crop">
                    <i class="bi bi-check-lg me-1"></i>Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="editLoginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="login-form">
            <?= csrf_field() ?>
            <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
                <div class="modal-header bg-white border-0 p-4">
                    <h5 class="modal-title fw-bold">Keamanan & Akses Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <p class="small text-muted mb-4 opacity-75">Username Anda:
                        <strong><?= esc($panitia->username ?? '') ?></strong>
                    </p>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Ganti Kata
                            Sandi</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" name="password" id="password_input"
                                placeholder="Gunakan minimal 6 karakter">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Google
                            Gemini API Key (Fitur AI)</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-robot"></i></span>
                            <input type="password" class="form-control" name="api_key" id="api_key_input"
                                value="<?= esc($panitia->api_key ?? '') ?>" placeholder="Paste your API key here...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="pas-btn pas-btn--white" data-bs-dismiss="modal">Batal</button>
                    <!-- Apply security changes by submitting to form-profile, or handled uniquely, since we submit auth data too -->
                    <!-- We can just trigger main form save -->
                    <button type="button" id="btn-save-security" class="pas-btn pas-btn--primary px-4">Terapkan
                        Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Signature Modal -->
<div class="modal fade" id="modal-signature" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
            <div class="modal-header bg-white border-0 p-4">
                <h5 class="modal-title fw-bold">Gambar Tanda Tangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <canvas id="sig-canvas"></canvas>
                <p class="smaller text-muted mt-2"><i class="bi bi-info-circle me-1"></i>Gunakan mouse atau layar sentuh
                    untuk menggambar.</p>
            </div>
            <div class="modal-footer bg-light border-0 p-3">
                <button type="button" class="pas-btn pas-btn--white" id="sig-clear">Ulangi</button>
                <button type="button" class="pas-btn pas-btn--primary px-4" id="sig-submit">Simpan TTD</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/dropify/js/dropify.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
    $(document).ready(function () {
        $('.dropify').dropify();

        // Form Submit
        $('#form-profile').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            const oldText = btn.html();

            // Append data from login modal manually
            let data = $(this).serialize();
            let pw = $('#password_input').val();
            let api = $('#api_key_input').val();

            if (pw) data += '&password=' + encodeURIComponent(pw);
            if (api) data += '&api_key=' + encodeURIComponent(api);

            $.ajax({
                url: '<?= site_url('panitia/save') ?>',
                type: 'POST',
                data: data,
                dataType: 'json',
                beforeSend: function () {
                    btn.attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                },
                success: function (res) {
                    Swal.close();
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-4' }
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, customClass: { popup: 'rounded-4' } });
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem', customClass: { popup: 'rounded-4' } });
                },
                complete: function () {
                    btn.attr('disabled', false).html(oldText);
                }
            });
        });

        // Security Save handler
        $('#btn-save-security').click(function () {
            $('#editLoginModal').modal('hide');
            $('#form-profile').submit();
        });

        // ══════════════ CROP PHOTO LOGIC ══════════════
        let cropper = null;
        const $cropImage = $('#crop-image');
        const $stepPick = $('#crop-step-pick');
        const $stepEdit = $('#crop-step-edit');
        const $btnSaveCrop = $('#btn-save-crop');
        const $cropFileInput = $('#crop-file-input');
        const $dropZone = $('#crop-drop-zone');

        // Prevent infinite recursion by using native click and stopPropagation
        $cropFileInput.on('click', (e) => e.stopPropagation());
        $dropZone.on('click', () => $cropFileInput[0].click());

        $dropZone.on('dragover', function (e) {
            e.preventDefault();
            $(this).addClass('dragover');
        }).on('dragleave drop', function (e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            if (e.type === 'drop') {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length) loadImageForCrop(files[0]);
            }
        });

        $cropFileInput.on('change', function () {
            if (this.files && this.files[0]) loadImageForCrop(this.files[0]);
        });

        function loadImageForCrop(file) {
            if (!file.type.match(/image\/(jpeg|png|jpg)/)) {
                Swal.fire('Format Salah', 'Hanya file JPG/PNG yang didukung.', 'warning');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('File Terlalu Besar', 'Ukuran maksimal 5 MB.', 'warning');
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                $cropImage.attr('src', e.target.result);
                $stepPick.addClass('d-none');
                $stepEdit.removeClass('d-none');
                $btnSaveCrop.removeClass('d-none');
                initCropper();
            };
            reader.readAsDataURL(file);
        }

        function initCropper() {
            if (cropper) cropper.destroy();
            cropper = new Cropper($cropImage[0], {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.85,
                responsive: true,
                restore: false,
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
                preview: '#crop-preview-circle, #crop-preview-square',
            });
        }

        $('#crop-rotate-left').click(() => cropper && cropper.rotate(-90));
        $('#crop-rotate-right').click(() => cropper && cropper.rotate(90));
        $('#crop-zoom-in').click(() => cropper && cropper.zoom(0.1));
        $('#crop-zoom-out').click(() => cropper && cropper.zoom(-0.1));
        $('#crop-reset').click(() => cropper && cropper.reset());
        $('#crop-change-file').click(() => { $cropFileInput.val(''); $cropFileInput[0].click(); });

        $('#editFotoModal').on('hidden.bs.modal', function () {
            if (cropper) { cropper.destroy(); cropper = null; }
            $stepPick.removeClass('d-none');
            $stepEdit.addClass('d-none');
            $btnSaveCrop.addClass('d-none');
            $cropFileInput.val('');
        });

        $btnSaveCrop.click(function () {
            if (!cropper) return;
            const canvas = cropper.getCroppedCanvas({ width: 500, height: 500, imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
            canvas.toBlob(function (blob) {
                const formData = new FormData();
                formData.append('file', blob, 'profile_crop.jpg');
                formData.append('id_panitia', '<?= $panitia->id_panitia ?? '' ?>');
                formData.append('field', 'foto');
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                Swal.fire({ title: 'Menyimpan...', html: 'Mengompres dan mengunggah foto profil', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                $.ajax({
                    url: '<?= site_url("panitia/uploadFoto") ?>',
                    type: 'POST', data: formData, processData: false, contentType: false,
                    success: (res) => {
                        if (res.status) {
                            $('#img-foto-preview').attr('src', (res.data?.src || res.src) + '?t=' + Date.now());
                            $('#editFotoModal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Foto Diperbarui!', text: 'Foto profil berhasil di-crop dan disimpan.', timer: 2500, showConfirmButton: false, customClass: { popup: 'rounded-4' } });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Upload gagal', customClass: { popup: 'rounded-4' } });
                        }
                    },
                    error: () => Swal.fire('Error', 'Koneksi ke server gagal.', 'error')
                });
            }, 'image/jpeg', 0.92);
        });

        // Signature Logic
        const canvas = document.getElementById("sig-canvas");
        const ctx = canvas.getContext("2d");
        let drawing = false;
        let prevX, prevY;

        $('#modal-signature').on('shown.bs.modal', function () {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
        });

        $('#btn-create-ttd').click(() => $('#modal-signature').modal('show'));

        function getMousePos(canvasDom, mouseEvent) {
            var rect = canvasDom.getBoundingClientRect();
            return {
                x: (mouseEvent.clientX || mouseEvent.touches[0].clientX) - rect.left,
                y: (mouseEvent.clientY || mouseEvent.touches[0].clientY) - rect.top
            };
        }

        canvas.addEventListener("mousedown", (e) => {
            drawing = true;
            const pos = getMousePos(canvas, e);
            [prevX, prevY] = [pos.x, pos.y];
        });

        canvas.addEventListener("mousemove", (e) => {
            if (!drawing) return;
            const pos = getMousePos(canvas, e);
            ctx.beginPath(); ctx.lineWidth = 2; ctx.lineCap = "round"; ctx.strokeStyle = "#333";
            ctx.moveTo(prevX, prevY); ctx.lineTo(pos.x, pos.y); ctx.stroke();
            [prevX, prevY] = [pos.x, pos.y];
        });

        canvas.addEventListener("mouseup", () => drawing = false);
        canvas.addEventListener("touchstart", (e) => { e.preventDefault(); drawing = true; const pos = getMousePos(canvas, e);[prevX, prevY] = [pos.x, pos.y]; }, { passive: false });
        canvas.addEventListener("touchmove", (e) => { if (!drawing) return; const pos = getMousePos(canvas, e); ctx.beginPath(); ctx.moveTo(prevX, prevY); ctx.lineTo(pos.x, pos.y); ctx.stroke();[prevX, prevY] = [pos.x, pos.y]; }, { passive: false });
        canvas.addEventListener("touchend", () => drawing = false, false);

        $('#sig-clear').click(() => ctx.clearRect(0, 0, canvas.width, canvas.height));

        $('#sig-submit').click(() => {
            const dataUrl = canvas.toDataURL("image/png");
            Swal.fire({ title: 'Menyimpan TTD...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: '<?= site_url("panitia/saveSignature") ?>',
                type: 'POST',
                data: { id_panitia: $('#id_panitia').val(), image: dataUrl, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                success: (res) => {
                    Swal.close();
                    if (res.status) {
                        $('#img-ttd-preview').attr('src', res.data.src).removeClass('d-none');
                        $('#ttd-placeholder').addClass('d-none');
                        $('#btn-delete-ttd').removeClass('d-none');
                        $('#btn-create-ttd').html('<i class="bi bi-plus-lg"></i> Update');
                        $('#modal-signature').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, customClass: { popup: 'rounded-4' } });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, customClass: { popup: 'rounded-4' } });
                    }
                }
            });
        });

        $('#btn-delete-ttd').click(() => {
            Swal.fire({
                title: 'Hapus Tanda Tangan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                customClass: { popup: 'rounded-4', confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
            }).then(res => {
                if (res.isConfirmed) {
                    $.ajax({
                        url: '<?= site_url("panitia/deleteTtd") ?>',
                        type: 'POST',
                        data: { id_panitia: $('#id_panitia').val(), <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                        success: (res) => {
                            if (res.status) {
                                $('#img-ttd-preview').addClass('d-none');
                                $('#ttd-placeholder').removeClass('d-none');
                                $('#btn-delete-ttd').addClass('d-none');
                                $('#btn-create-ttd').html('<i class="bi bi-plus-lg"></i> Buat Baru');
                                Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, customClass: { popup: 'rounded-4' } });
                            }
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>