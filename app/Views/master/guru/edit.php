<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/dropify/css/dropify.min.css') ?>" />

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

        --color-neutral-50: #f8fafc;
        --color-neutral-100: #f1f5f9;
        --color-neutral-200: #e2e8f0;
        --color-neutral-300: #cbd5e1;
        --color-neutral-400: #94a3b8;
        --color-neutral-500: #64748b;
        --color-neutral-600: #475569;
        --color-neutral-700: #334155;
        --color-neutral-800: #1e293b;

        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.25rem;
        --radius-full: 9999px;

        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 12px -2px rgba(0, 0, 0, 0.08);
        
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --transition-base: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc !important; }


    .text-primary-gradient {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    .pas-card {
        background: white;
        border-radius: var(--radius-xl);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .pas-card__header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--color-neutral-100);
        background: white;
    }

    .premium-input-group {
        position: relative;
        width: 100%;
    }

    .p-group-icon {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3rem;
        display: flex; align-items: center; justify-content: center;
        color: var(--color-neutral-400); 
        font-size: 1.1rem;
        z-index: 5;
        pointer-events: none;
        transition: var(--transition-base);
    }

    .premium-input-group:focus-within .p-group-icon {
        color: var(--color-primary-600);
    }

    .premium-input-group .form-control,
    .premium-input-group .form-select {
        padding-left: 3rem !important;
        height: 3.25rem;
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

    .pas-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 0.5rem; padding: 0.625rem 1.25rem;
        font-weight: 600; font-size: var(--font-size-sm);
        border-radius: var(--radius-lg); transition: var(--transition-base);
        cursor: pointer; border: 1px solid transparent; text-decoration: none !important;
    }

    .pas-btn--primary {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .pas-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3); color: white; }

    .pas-btn--white { background: white; color: var(--color-neutral-700); border-color: var(--color-neutral-200); }
    .pas-btn--white:hover { background: var(--color-neutral-50); color: var(--color-neutral-800); }

    .pas-btn--danger-soft { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
    .pas-btn--danger-soft:hover { background: #e11d48; color: white; }

    .nav-tabs-premium { border-bottom: 2px solid var(--color-neutral-100); gap: 0.5rem; }
    .nav-tabs-premium .nav-link {
        border: none; padding: 1rem 1.25rem; font-weight: 600; color: var(--color-neutral-500);
        position: relative; transition: var(--transition-base); background: transparent;
    }
    .nav-tabs-premium .nav-link:hover { color: var(--color-primary-600); }
    .nav-tabs-premium .nav-link.active {
        color: var(--color-primary-600); background: transparent;
    }
    .nav-tabs-premium .nav-link.active::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0;
        height: 2.5px; background: var(--color-primary-600); border-radius: 2px;
    }

    #sig-canvas {
        border: 2px dashed var(--color-neutral-300);
        border-radius: var(--radius-xl);
        cursor: crosshair; background-color: #ffffff;
        width: 100%; height: 200px; touch-action: none;
    }

    .avatar-upload-wrapper {
        position: relative; display: inline-block;
    }
    .avatar-preview {
        width: 140px; height: 140px; border-radius: var(--radius-full);
        border: 4px solid white; box-shadow: var(--shadow-md); object-fit: cover;
    }
</style>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-gear text-primary me-2"></i>Edit Profil Guru
                </h3>
                <p class="text-muted small mb-0">Kelola informasi personal, data biodata, dokumen, dan akses akun</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent me-2">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('dataguru') ?>" class="text-decoration-none">Data Guru</a></li>
                    <li class="breadcrumb-item active">Edit Profil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-3">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Sidebar Profile -->
            <div class="col-xl-4 col-md-5">
                <div class="pas-card text-center p-4">
                    <div class="avatar-upload-wrapper mb-3">
                        <?php
                        $foto = $guru->foto && file_exists(FCPATH . $guru->foto)
                            ? base_url($guru->foto)
                            : base_url('assets/img/guru.png');
                        ?>
                        <img id="foto-guru" src="<?= $foto ?>" class="avatar-preview" alt="Avatar">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#editFotoModal" 
                                class="pas-btn pas-btn--primary p-2 rounded-circle position-absolute bottom-0 end-0 shadow-sm" 
                                style="width: 38px; height: 38px; border: 3px solid white !important;" title="Ganti Foto">
                            <i class="bi bi-camera-fill fs-6"></i>
                        </button>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= esc($guru->nama_guru) ?></h5>
                    <div class="mb-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 rounded-pill mb-2">
                            <?= esc($guru->level ?? 'Pendidik') ?>
                        </span>
                        <div class="text-muted small">
                            NIP: <span class="fw-semibold text-dark"><?= esc($guru->nip ?: 'N/A') ?></span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="button" class="pas-btn pas-btn--white w-100" data-bs-toggle="modal" data-bs-target="#editLoginModal">
                            <i class="bi bi-shield-lock me-2 text-warning fs-6"></i> Keamanan Akun
                        </button>
                        <button type="button" class="pas-btn pas-btn--danger-soft w-100" onclick="deleteImage(true)">
                            <i class="bi bi-trash me-2"></i> Reset Foto Profil
                        </button>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="text-start">
                        <h6 class="text-muted small fw-bold text-uppercase tracking-wider mb-3">
                            <i class="bi bi-vector-pen me-1 text-primary"></i>Tanda Tangan Digital
                        </h6>
                        <div class="pas-card bg-light border-0 p-3 text-center" style="min-height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div id="ttd-placeholder" class="text-muted <?= !empty($guru->ttd) ? 'd-none' : '' ?>">
                                <i class="bi bi-pen d-block fs-2 mb-1 opacity-25"></i>
                                <span class="small">Belum ada tanda tangan</span>
                            </div>
                            <img src="<?= !empty($guru->ttd) ? base_url($guru->ttd) : '' ?>" alt="TTD" id="img-ttd-preview"
                                 class="img-fluid <?= empty($guru->ttd) ? 'd-none' : '' ?>" style="max-height: 100px; filter: contrast(1.2);">
                            
                            <div class="mt-3 d-flex gap-2">
                                <button type="button" class="pas-btn pas-btn--primary py-1 px-3 btn-sm" id="btn-create-ttd">
                                    <i class="bi bi-pencil-square me-1"></i> <?= !empty($guru->ttd) ? 'Update' : 'Buat' ?>
                                </button>
                                <button type="button" class="pas-btn pas-btn--danger-soft py-1 px-3 btn-sm <?= empty($guru->ttd) ? 'd-none' : '' ?>" id="btn-delete-ttd">
                                    <i class="bi bi-trash me-1"></i> Hapus
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
                        <ul class="nav nav-tabs nav-tabs-premium px-4" id="guruEditTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-pane" type="button" role="tab">
                                    <i class="bi bi-person-badge me-1"></i>Informasi Dasar
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address-pane" type="button" role="tab">
                                    <i class="bi bi-card-heading me-1"></i>Biodata Lengkap
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="docs-tab" data-bs-toggle="tab" data-bs-target="#docs-pane" type="button" role="tab">
                                    <i class="bi bi-file-earmark-arrow-up me-1"></i>Dokumen
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <form id="formguru" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_guru" value="<?= $id_active ?>">
                        <div class="card-body p-4">
                            <div class="tab-content" id="guruEditTabsContent">
                                <!-- Profile Tab -->
                                <div class="tab-pane fade show active" id="profile-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <?php foreach ($input_profile as $input): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2"><?= $input->label ?></label>
                                                <div class="premium-input-group">
                                                    <span class="p-group-icon"><i class="<?= $input->icon ?>"></i></span>
                                                    <?php if ($input->name == 'jenis_kelamin'): ?>
                                                        <select class="form-select" name="jenis_kelamin" required>
                                                            <option value="L" <?= $input->value == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                            <option value="P" <?= $input->value == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                        </select>
                                                    <?php elseif ($input->name == 'agama'): ?>
                                                        <select class="form-select" name="agama">
                                                            <?php foreach (["Islam", "Kristen", "Katolik", "Hindu", "Buddha", "Khonghucu", "Kepercayaan thd Tuhan YME", "Lainnya"] as $agama): ?>
                                                                <option value="<?= $agama ?>" <?= $input->value == $agama ? 'selected' : '' ?>><?= $agama ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <input type="<?= $input->type ?>" class="form-control" name="<?= $input->name ?>" value="<?= esc($input->value) ?>" placeholder="<?= $input->label ?>">
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Address Tab -->
                                <div class="tab-pane fade" id="address-pane" role="tabpanel">
                                    <div class="row g-3">
                                        <?php foreach ($input_alamat as $input): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2"><?= $input->label ?></label>
                                                <div class="premium-input-group">
                                                    <span class="p-group-icon"><i class="<?= $input->icon ?>"></i></span>
                                                    <input type="<?= $input->type ?>" class="form-control" name="<?= $input->name ?>" 
                                                           value="<?= esc($input->value) ?>" placeholder="<?= $input->label ?>" id="<?= $input->name ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Documents Tab -->
                                <div class="tab-pane fade" id="docs-pane" role="tabpanel">
                                    <div class="row g-4">
                                        <?php foreach ($input_dokumen as $doc): ?>
                                            <div class="col-md-12">
                                                <div class="pas-card bg-light border-0 p-4 mb-0">
                                                    <label class="form-label fw-bold text-dark mb-3">
                                                        <i class="bi bi-file-earmark-arrow-up me-2 text-primary"></i>Upload <?= $doc->label ?>
                                                    </label>
                                                    <input type="file" class="form-control shadow-xs" id="<?= $doc->id ?>" 
                                                           name="<?= $doc->name ?>" <?= $doc->id == 'sertifikat' ? 'multiple' : '' ?>>
                                                    <div id="preview-<?= $doc->id ?>" class="d-flex flex-wrap gap-3 mt-3">
                                                        <!-- Thumbnails -->
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pas-card__header border-top d-flex align-items-center justify-content-between p-4">
                            <a href="<?= site_url('dataguru') ?>" class="pas-btn pas-btn--white">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="button" id="submit-profil" class="pas-btn pas-btn--primary px-4 py-2">
                                <i class="bi bi-cloud-check-fill me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div class="modal fade" id="editFotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
            <div class="modal-header bg-white border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-camera me-2 text-primary"></i>Perbarui Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="foto-form">
                    <input type="file" id="foto-input" name="foto" class="dropify" data-height="220"
                        data-allowed-file-extensions="jpg jpeg png" data-default-file="<?= $foto ?>">
                </form>
            </div>
            <div class="modal-footer bg-light border-0 p-3">
                <button type="button" class="pas-btn pas-btn--white" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="pas-btn pas-btn--primary px-4" id="btn-save-foto">Simpan Foto</button>
            </div>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="editLoginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form id="login-form">
            <?= csrf_field() ?>
            <input type="hidden" name="id_guru" value="<?= $id_active ?>">
            <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
                <div class="modal-header bg-white border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2 text-warning"></i>Keamanan & Akses Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Username Baru</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-person-circle"></i></span>
                            <input type="text" class="form-control" name="username" value="<?= esc($guru->username) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Password Baru</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" name="new" placeholder="Isi hanya jika ingin mengganti">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Konfirmasi Password</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-check-circle-fill"></i></span>
                            <input type="password" class="form-control" name="new_confirm" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-3">
                    <button type="button" class="pas-btn pas-btn--white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="pas-btn pas-btn--primary px-4">Update Akses</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Signature Modal -->
<div class="modal fade" id="modal-signature" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg rounded-4">
            <div class="modal-header bg-white border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-vector-pen me-2 text-primary"></i>Gambar Tanda Tangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <canvas id="sig-canvas"></canvas>
                <p class="small text-muted mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Gunakan mouse atau layar sentuh untuk menggambar tanda tangan Anda.</p>
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
<script>
    const idGuru = '<?= $id_active ?>';
    let dokumen = <?= $guru->dokumen ?? '{}' ?>;

    function renderDocs() {
        ['ktp_id', 'kk_id', 'sertifikat'].forEach(id => {
            const container = $(`#preview-${id}`);
            container.empty();
            let key = id === 'sertifikat' ? 'sertifikat' : (id === 'ktp_id' ? 'ktp' : 'kk');

            if (dokumen[key]) {
                const docList = Array.isArray(dokumen[key]) ? dokumen[key] : [dokumen[key]];
                docList.forEach(src => {
                    const fullSrc = base_url + '/' + src;
                    container.append(`
                        <div class="position-relative">
                            <img src="${fullSrc}" class="rounded-3 border shadow-sm" style="width: 110px; height: 110px; object-fit: cover; border: 3px solid white !important;">
                            <button type="button" class="pas-btn pas-btn--danger-soft p-1 rounded-circle position-absolute" 
                                    style="top: -8px; right: -8px; width:24px; height:24px;" onclick="deleteDoc('${src}')">
                                <i class="bi bi-x" style="font-size: 1rem;"></i>
                            </button>
                        </div>
                    `);
                });
            }
        });
    }

    function deleteDoc(src) {
        Swal.fire({
            title: 'Hapus Dokumen?',
            text: "Dokumen ini akan dihapus secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4', confirmButton: 'rounded-3 px-4', cancelButton: 'rounded-3 px-4' }
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({
                    url: '<?= site_url("dataguru/delete_dokumen/") ?>' + idGuru,
                    data: { src: src },
                    success: (res) => {
                        if (res.status) {
                            dokumen = res.dokumen;
                            renderDocs();
                            Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Dokumen berhasil dihapus', customClass: { popup: 'rounded-4' } });
                        }
                    }
                });
            }
        });
    }

    function deleteImage(confirm) {
        Swal.fire({
            title: 'Reset Foto Profil?',
            text: "Kembali menggunakan foto default sistem.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4', confirmButton: 'rounded-3 px-4', cancelButton: 'rounded-3 px-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url("dataguru/delete_foto/") ?>' + idGuru,
                    type: 'POST',
                    data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    success: function (res) {
                         if (res.status) {
                            $('#foto-guru').attr('src', '<?= base_url("assets/img/guru.png") ?>');
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Foto kembali ke default', customClass: { popup: 'rounded-4' } });
                         }
                    }
                });
            }
        });
    }

    $(document).ready(function () {
        renderDocs();
        if (typeof $.fn.dropify !== 'undefined') {
            $('.dropify').dropify();
        }

        if (typeof $.fn.datetimepicker !== 'undefined') {
            $('#tgl_lahir').datetimepicker({ format: 'Y-m-d', timepicker: false });
        }

        // Photo Upload
        $('#btn-save-foto').click(function () {
            let formData = new FormData($('#foto-form')[0]);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            Swal.fire({ title: 'Mengunggah...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            
            $.ajax({
                url: '<?= site_url("dataguru/uploadFile/") ?>' + idGuru,
                type: 'POST', data: formData, processData: false, contentType: false,
                success: (res) => {
                    Swal.close();
                    if (res.status) {
                        $('#foto-guru').attr('src', res.src);
                        $('#editFotoModal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Foto profil diperbarui', customClass: { popup: 'rounded-4' } });
                    }
                }
            });
        });

        // Submit Profile
        $('#submit-profil').click(function () {
            let formData = new FormData($('#formguru')[0]);
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            
            $.ajax({
                url: '<?= site_url("dataguru/save") ?>',
                type: 'POST', data: formData, processData: false, contentType: false,
                success: (res) => {
                    Swal.close();
                    if (res.status) {
                        Swal.fire({ icon: 'success', title: 'Tersimpan', text: 'Data profil berhasil diperbarui', customClass: { popup: 'rounded-4' } });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Periksa kembali formulir Anda', customClass: { popup: 'rounded-4' } });
                    }
                }
            });
        });

        // Login Update
        $('#login-form').submit(function (e) {
            e.preventDefault();
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            
            $.ajax({
                url: '<?= site_url("dataguru/editlogin") ?>',
                type: 'POST', data: $(this).serialize(),
                success: (res) => {
                    Swal.close();
                    if (res.status) {
                        $('#editLoginModal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.text, customClass: { popup: 'rounded-4' } });
                    } else {
                        let msg = isObject(res.errors) ? Object.values(res.errors).join('<br>') : (res.message || 'Validasi gagal');
                        Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: msg, customClass: { popup: 'rounded-4' } });
                    }
                }
            });
        });

        function isObject(val) {
            return val !== null && typeof val === 'object';
        }

        // Signature Logic
        const canvas = document.getElementById("sig-canvas");
        if (canvas) {
            const ctx = canvas.getContext("2d");
            let drawing = false;
            let prevX, prevY;

            $('#btn-create-ttd').click(() => $('#modal-signature').modal('show'));

            function getMousePos(canvasDom, mouseEvent) {
                var rect = canvasDom.getBoundingClientRect();
                return {
                    x: (mouseEvent.clientX || (mouseEvent.touches && mouseEvent.touches[0] ? mouseEvent.touches[0].clientX : 0)) - rect.left,
                    y: (mouseEvent.clientY || (mouseEvent.touches && mouseEvent.touches[0] ? mouseEvent.touches[0].clientY : 0)) - rect.top
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
            canvas.addEventListener("touchstart", (e) => { e.preventDefault(); drawing = true; const pos = getMousePos(canvas, e); [prevX, prevY] = [pos.x, pos.y]; }, false);
            canvas.addEventListener("touchmove", (e) => { if (!drawing) return; const pos = getMousePos(canvas, e); ctx.beginPath(); ctx.moveTo(prevX, prevY); ctx.lineTo(pos.x, pos.y); ctx.stroke(); [prevX, prevY] = [pos.x, pos.y]; }, false);
            canvas.addEventListener("touchend", () => drawing = false, false);

            $('#sig-clear').click(() => ctx.clearRect(0, 0, canvas.width, canvas.height));

            $('#sig-submit').click(() => {
                const dataUrl = canvas.toDataURL("image/png");
                Swal.fire({ title: 'Menyimpan TTD...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                
                $.ajax({
                    url: '<?= site_url("dataguru/save_signature") ?>',
                    type: 'POST',
                    data: { id_guru: idGuru, image: dataUrl, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    success: (res) => {
                        Swal.close();
                        if (res.status) {
                            $('#img-ttd-preview').attr('src', res.src).removeClass('d-none');
                            $('#ttd-placeholder').addClass('d-none');
                            $('#btn-delete-ttd').removeClass('d-none');
                            $('#modal-signature').modal('hide');
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Tanda tangan disimpan', customClass: { popup: 'rounded-4' } });
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
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    customClass: { popup: 'rounded-4', confirmButton: 'rounded-3 px-4', cancelButton: 'rounded-3 px-4' }
                }).then(res => {
                    if (res.isConfirmed) {
                        $.ajax({
                            url: '<?= site_url("dataguru/delete_ttd") ?>',
                            type: 'POST',
                            data: { id_guru: idGuru, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                            success: (res) => {
                                if (res.status) {
                                    $('#img-ttd-preview').addClass('d-none');
                                    $('#ttd-placeholder').removeClass('d-none');
                                    $('#btn-delete-ttd').addClass('d-none');
                                    Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Tanda tangan dihapus', customClass: { popup: 'rounded-4' } });
                                }
                            }
                        });
                    }
                });
            });
        }
    });
</script>
<?= $this->endSection() ?>