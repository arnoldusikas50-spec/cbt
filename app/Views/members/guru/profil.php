<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/dropify/css/dropify.min.css') ?>" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
<style>
    #sig-canvas {
        border: 2px dotted #CCCCCC;
        border-radius: 15px;
        cursor: crosshair;
        background-color: #ffffff;
        width: 100%;
        max-width: 400px;
        height: 200px;
        touch-action: none;
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
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.04);
    }
    .crop-drop-zone.dragover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.08);
    }
</style>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-circle text-primary me-2"></i>
                    <?= esc($judul ?? 'Profil Guru') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data Profil Saya') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-4 col-md-4">
                    <div class="card shadow-sm border-0 card-primary card-outline">
                        <div class="card-body text-center">
                            <?php
                            $foto = $guru->foto && file_exists(FCPATH . $guru->foto)
                                ? base_url($guru->foto)
                                : base_url('assets/img/logo.png');
                            $defaultFoto = base_url('assets/img/logo.png');
                            ?>
                            <img id="foto-guru" src="<?= $foto ?>" class="rounded-circle img-thumbnail mb-3"
                                alt="User avatar" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='<?= $defaultFoto ?>'">
                            <h4 class="fw-bold mb-1">
                                <?= $guru->nama_guru ?>
                            </h4>
                            <p class="text-muted mb-4">
                                <?= ($guru->level ?? '') . ' ' . ($guru->nama_kelas ?? '') ?>
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#editFotoModal"
                                        class="btn btn-sm btn-primary w-100"><i class="bi bi-image me-1"></i>
                                        Ganti Foto
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-danger btn-sm w-100"
                                        onclick="deleteImage(true)"><i class="bi bi-trash me-1"></i> Hapus Foto
                                    </button>
                                </div>
                            </div>

                            <button type="button" class="btn btn-warning btn-sm w-100 mb-4" data-bs-toggle="modal"
                                data-bs-target="#editLoginModal"><i class="bi bi-pencil me-1"></i> Edit Username /
                                Password
                            </button>

                            <div class="text-start">
                                <h6 class="text-muted small fw-bold text-uppercase mb-2">Tanda Tangan</h6>
                                <div class="border rounded p-3 bg-light">
                                    <div id="ttd-placeholder"
                                        class="d-flex align-items-center justify-content-center text-muted <?= !empty($guru->ttd) ? 'd-none' : '' ?>"
                                        style="height: 100px; border: 2px dashed #ccc;">
                                        <small>Belum ada TTD</small>
                                    </div>
                                    <img src="<?= !empty($guru->ttd) ? base_url($guru->ttd) : '' ?>" alt="TTD"
                                        id="img-ttd-preview"
                                        class="img-fluid mx-auto d-block <?= empty($guru->ttd) ? 'd-none' : '' ?>"
                                        style="max-height: 100px;">
                                    <div class="mt-2 text-center">
                                        <button type="button" class="btn btn-sm btn-success" id="btn-create-ttd"
                                            title="Buat TTD Baru">
                                            <i class="bi bi-pen-nib me-1"></i> Buat
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm btn-danger <?= empty($guru->ttd) ? 'd-none' : '' ?>"
                                            id="btn-delete-ttd" title="Hapus TTD">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-md-8">
                    <div class="card shadow-sm border-0 card-primary card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="guruEditTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#profile-pane" type="button" role="tab">Profile</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="address-tab" data-bs-toggle="tab"
                                        data-bs-target="#address-pane" type="button" role="tab">Data Lengkap</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="docs-tab" data-bs-toggle="tab"
                                        data-bs-target="#docs-pane" type="button" role="tab">Dokumen</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="ai-tab" data-bs-toggle="tab"
                                        data-bs-target="#ai-pane" type="button" role="tab">
                                        <i class="bi bi-stars me-1"></i>Pengaturan AI
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <form id="formguru" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_guru" value="<?= $id_active ?>">
                            <div class="card-body">
                                <div class="tab-content" id="guruEditTabsContent">
                                    <!-- Profile Tab -->
                                    <div class="tab-pane fade show active pt-3" id="profile-pane" role="tabpanel">
                                        <?php foreach ($input_profile as $input): ?>
                                            <div class="mb-3 row">
                                                <label class="col-sm-3 col-form-label">
                                                    <i class="<?= $input->icon ?> me-2 text-secondary"></i> <?= $input->label ?>
                                                </label>
                                                <div class="col-sm-9">
                                                    <?php if ($input->name == 'jenis_kelamin'): ?>
                                                        <select class="form-select" name="jenis_kelamin" required>
                                                            <option value="" disabled <?= empty($input->value) ? 'selected' : '' ?>>Pilih Jenis Kelamin</option>
                                                            <option value="L" <?= $input->value == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                            <option value="P" <?= $input->value == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                        </select>
                                                    <?php elseif ($input->name == 'agama'): ?>
                                                        <select class="form-select" name="agama">
                                                            <?php $agamas = ["Islam", "Kristen", "Katolik", "Hindu", "Buddha", "Khonghucu", "Kepercayaan thd Tuhan YME", "Lainnya"]; ?>
                                                            <?php foreach ($agamas as $agama): ?>
                                                                <option value="<?= $agama ?>" <?= $input->value == $agama ? 'selected' : '' ?>>
                                                                    <?= $agama ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <input type="<?= $input->type ?>" class="form-control"
                                                            name="<?= $input->name ?>" value="<?= $input->value ?>"
                                                            placeholder="<?= $input->label ?>"
                                                            <?= ($input->name == 'api_key') ? 'readonly' : '' ?>>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Address Tab -->
                                    <div class="tab-pane fade pt-3" id="address-pane" role="tabpanel">
                                        <?php foreach ($input_alamat as $input): ?>
                                            <div class="mb-3 row">
                                                <label class="col-sm-3 col-form-label">
                                                    <i class="<?= $input->icon ?> me-2 text-secondary"></i> <?= $input->label ?>
                                                </label>
                                                <div class="col-sm-9">
                                                    <input type="<?= $input->type ?>" class="form-control"
                                                        name="<?= $input->name ?>" value="<?= $input->value ?>"
                                                        placeholder="<?= $input->label ?>"
                                                        id="<?= $input->name ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Documents Tab -->
                                    <div class="tab-pane fade pt-3" id="docs-pane" role="tabpanel">
                                        <div class="row">
                                            <?php foreach ($input_dokumen as $doc): ?>
                                                <div class="col-md-6 mb-4">
                                                    <label class="form-label fw-bold">
                                                        <?= $doc->label ?>
                                                    </label>
                                                    <input type="file" class="form-control mb-2" id="<?= $doc->id ?>"
                                                        name="<?= $doc->name ?>" <?= $doc->id == 'sertifikat' ? 'multiple' : '' ?>>
                                                    <div id="preview-<?= $doc->id ?>" class="d-flex flex-wrap gap-2 mt-2">
                                                        <!-- Thumbnails will be rendered here -->
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- AI Settings Tab -->
                                    <div class="tab-pane fade pt-3" id="ai-pane" role="tabpanel">
                                        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px;">
                                            <div class="card-body text-white">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                                                        <i class="bi bi-robot fs-3"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-1 fw-bold">Google Gemini API Key</h5>
                                                        <p class="mb-0 small opacity-75">Masukkan API Key pribadi Anda untuk menggunakan fitur AI (Generate Soal, Parafrase, dll.) dengan kuota sendiri.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card border shadow-sm mb-3" style="border-radius: 10px;">
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">
                                                        <i class="bi bi-key-fill text-warning me-1"></i> Google API Key
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                                                        <input type="password" class="form-control" id="input-api-key"
                                                            placeholder="AIza... (masukkan API key Anda)"
                                                            value="<?= esc($api_key ?? '') ?>">
                                                        <button class="btn btn-outline-secondary" type="button" id="btn-toggle-key" title="Tampilkan/Sembunyikan">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div class="form-text">
                                                        <?php if (!empty($api_key)): ?>
                                                            <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>API Key tersimpan</span>
                                                        <?php else: ?>
                                                            <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Belum ada API Key. Menggunakan API Key sistem (jika ada).</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-primary" id="btn-save-apikey">
                                                        <i class="bi bi-save me-1"></i> Simpan API Key
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" id="btn-remove-apikey">
                                                        <i class="bi bi-trash me-1"></i> Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Instructions Card -->
                                        <div class="card border-0 bg-light" style="border-radius: 10px;">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-3"><i class="bi bi-question-circle text-primary me-1"></i> Cara Mendapatkan API Key</h6>
                                                <ol class="mb-3 small">
                                                    <li>Kunjungi <a href="https://aistudio.google.com/apikey" target="_blank" class="fw-bold text-decoration-none">Google AI Studio <i class="bi bi-box-arrow-up-right"></i></a></li>
                                                    <li>Login dengan akun Google Anda</li>
                                                    <li>Klik <strong>"Create API Key"</strong> atau <strong>"Get API Key"</strong></li>
                                                    <li>Pilih project (atau buat baru)</li>
                                                    <li>Copy API Key yang dihasilkan dan paste di kolom di atas</li>
                                                </ol>
                                                <div class="alert alert-info py-2 mb-0 small">
                                                    <i class="bi bi-lightbulb me-1"></i>
                                                    <strong>Gratis!</strong> Google Gemini API menyediakan kuota gratis yang cukup untuk penggunaan harian.
                                                    Dengan API Key pribadi, kuota Anda terpisah dari guru lain sehingga tidak saling mengganggu.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top d-flex align-items-center">
                                <small class="text-danger"><i>* Semua kolom profil wajib diisi</i></small>
                                <button type="button" id="submit-profil" class="btn btn-primary ms-auto">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Photo Crop Modal -->
<div class="modal fade" id="editFotoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem; overflow: hidden;">
            <div class="modal-header border-0 bg-white px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="bi bi-crop text-primary me-2"></i>Crop Foto Profil</h5>
                    <small class="text-muted">Pilih area wajah yang ingin dijadikan foto profil</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-2">
                <!-- Step 1: File Picker -->
                <div id="crop-step-pick">
                    <div class="crop-drop-zone" id="crop-drop-zone">
                        <i class="bi bi-cloud-arrow-up" style="font-size: 2.5rem; color: #6366f1;"></i>
                        <div class="fw-semibold mt-2" style="color: #334155;">Klik atau seret gambar ke sini</div>
                        <div class="text-muted small mt-1">Format: JPG, PNG &bull; Maks: 5 MB</div>
                        <input type="file" id="crop-file-input" accept="image/jpeg,image/png,image/jpg" class="d-none">
                    </div>
                </div>
                <!-- Step 2: Cropper -->
                <div id="crop-step-edit" class="d-none">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="crop-container">
                                <img id="crop-image" src="" alt="Crop Preview">
                            </div>
                            <div class="crop-toolbar">
                                <button type="button" class="btn btn-outline-secondary" id="crop-rotate-left" title="Putar Kiri">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-rotate-right" title="Putar Kanan">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-zoom-in" title="Perbesar">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-zoom-out" title="Perkecil">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="crop-reset" title="Reset">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <div class="vr mx-1"></div>
                                <button type="button" class="btn btn-outline-primary" id="crop-change-file" title="Ganti File">
                                    <i class="bi bi-folder2-open"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="crop-preview-section">
                                <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.05em;">Preview</div>
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
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius:.75rem;">Batal</button>
                <button type="button" class="btn btn-primary px-4 d-none" id="btn-save-crop" style="border-radius:.75rem;">
                    <i class="bi bi-check-lg me-1"></i>Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="editLoginModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="login-form">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Akun Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="<?= $user->username ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control" name="new"
                            placeholder="Kosongkan jika tidak ingin ganti">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="new_confirm"
                            placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update Login</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Signature Modal -->
<div class="modal fade" id="modal-signature" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Tanda Tangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <canvas id="sig-canvas" style="touch-action: none; border: 1px solid #ccc; width: 100%; height: 200px; cursor: crosshair;"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="sig-clear">Bersihkan</button>
                <button type="button" class="btn btn-primary" id="sig-submit">Simpan TTD</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/dropify/js/dropify.min.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
    const idGuru = '<?= $id_active ?>';
    let dokumen = <?= json_encode($guru->dokumen ?? new stdClass()) ?>;

    function deleteImage(confirm) {
         Swal.fire({
            title: 'Hapus Foto?',
            text: "Foto profil akan dihapus dan kembali ke default",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url("guru/deleteFoto") ?>',
                    type: 'POST',
                    success: function (res) {
                         if (res.status) {
                            $('#foto-guru').attr('src', '<?= base_url("assets/img/guru.png") ?>');
                            Swal.fire('Terhapus!', 'Foto profil berhasil dihapus.', 'success');
                         } else {
                            Swal.fire('Gagal', 'Gagal menghapus foto.', 'error');
                         }
                    },
                    error: function() {
                         Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                    }
                });
            }
        });
    }

    function renderDocs() {
        ['ktp_id', 'kk_id', 'sertifikat'].forEach(id => {
            const container = $(`#preview-${id}`);
            container.empty();
            let key = id === 'sertifikat' ? 'sertifikat' : (id === 'ktp_id' ? 'ktp_id' : 'kk_id');
            // Mapping key names from controller definition to actual db json key might differ?
            // Controller defined names: ktp_id, kk_id, serti[]
            // But documents saved in DB with keys: ktp_id, kk_id, serti
            
            // Correction based on Controller implementation:
            if (id === 'sertifikat') key = 'serti';
            
            if (dokumen && dokumen[key]) {
                const docList = Array.isArray(dokumen[key]) ? dokumen[key] : [dokumen[key]];
                docList.forEach(src => {
                    const fullSrc = base_url + src;
                    container.append(`
                        <div class="position-relative">
                            <img src="${fullSrc}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="deleteDoc('${src}')">
                                <i class="bi bi-x"></i>
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
            icon: 'warning',
            showCancelButton: true
        }).then(res => {
            if (res.value) {
                $.ajax({
                    url: '<?= site_url("guru/deleteDokumen") ?>',
                    data: { src: src },
                    type: 'POST',
                    success: (res) => {
                        if (res.status) {
                            dokumen = res.dokumen;
                            renderDocs();
                        }
                    }
                });
            }
        });
    }

    $(document).ready(function () {
        renderDocs();

        $('#tgl_lahir').datetimepicker({
            format: 'Y-m-d',
            timepicker: false
        });

        $('.dropify').dropify();

        // ══════════════ CROP PHOTO LOGIC ══════════════
        let cropper = null;
        const $cropImage = $('#crop-image');
        const $stepPick = $('#crop-step-pick');
        const $stepEdit = $('#crop-step-edit');
        const $btnSaveCrop = $('#btn-save-crop');
        const $cropFileInput = $('#crop-file-input');
        const $dropZone = $('#crop-drop-zone');

        // Drop zone click — use native .click() and stopPropagation to prevent infinite loop
        $cropFileInput.on('click', (e) => e.stopPropagation());
        $dropZone.on('click', () => $cropFileInput[0].click());

        // Drag & Drop
        $dropZone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        }).on('dragleave drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            if (e.type === 'drop') {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length) loadImageForCrop(files[0]);
            }
        });

        // File input change
        $cropFileInput.on('change', function() {
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
            reader.onload = function(e) {
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

        // Toolbar actions
        $('#crop-rotate-left').click(() => cropper && cropper.rotate(-90));
        $('#crop-rotate-right').click(() => cropper && cropper.rotate(90));
        $('#crop-zoom-in').click(() => cropper && cropper.zoom(0.1));
        $('#crop-zoom-out').click(() => cropper && cropper.zoom(-0.1));
        $('#crop-reset').click(() => cropper && cropper.reset());
        $('#crop-change-file').click(() => {
            $cropFileInput.val('');
            $cropFileInput[0].click();
        });

        // Reset modal on close
        $('#editFotoModal').on('hidden.bs.modal', function() {
            if (cropper) { cropper.destroy(); cropper = null; }
            $stepPick.removeClass('d-none');
            $stepEdit.addClass('d-none');
            $btnSaveCrop.addClass('d-none');
            $cropFileInput.val('');
        });

        // Save cropped photo
        $btnSaveCrop.click(function() {
            if (!cropper) return;

            const canvas = cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('foto', blob, 'profile_crop.jpg');

                Swal.fire({
                    title: 'Menyimpan...',
                    html: 'Mengompres dan mengunggah foto profil',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '<?= site_url("guru/uploadFoto") ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (res) => {
                        if (res.status) {
                            $('#foto-guru').attr('src', res.src + '?t=' + Date.now());
                            $('#editFotoModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Foto Diperbarui!',
                                text: 'Foto profil berhasil di-crop dan disimpan.',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.error || 'Upload gagal', 'error');
                        }
                    },
                    error: () => {
                        Swal.fire('Error', 'Koneksi ke server gagal.', 'error');
                    }
                });
            }, 'image/jpeg', 0.92);
        });

        // Submit Profile
        $('#submit-profil').click(function () {
            let formData = new FormData($('#formguru')[0]);
            $.ajax({
                url: '<?= site_url("guru/updateProfil") ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (res) => {
                    if (res.status) {
                        Swal.fire('Berhasil', 'Data profile berhasil disimpan', 'success');
                    } else {
                        Swal.fire('Gagal', res.message || 'Periksa kembali inputan Anda', 'error');
                    }
                }
            });
        });

        // Login Update
        $('#login-form').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: '<?= site_url("guru/updateLogin") ?>',
                type: 'POST',
                data: $(this).serialize(),
                success: (res) => {
                    if (res.status) {
                        $('#editLoginModal').modal('hide');
                        Swal.fire('Berhasil', res.text, 'success');
                    } else {
                        let msg = res.errors ? Object.values(res.errors).join('<br>') : res.message;
                        Swal.fire('Gagal', msg, 'error');
                    }
                }
            });
        });

        // Signature Logic
        const canvas = document.getElementById("sig-canvas");
        const ctx = canvas.getContext("2d");
        let drawing = false;
        let prevX, prevY, prevT;

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);
        }

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const p = e.touches ? e.touches[0] : e;
            return { x: p.clientX - rect.left, y: p.clientY - rect.top };
        }

        function startDraw(e) {
            e.preventDefault();
            drawing = true;
            const pos = getPos(e);
            prevX = pos.x;
            prevY = pos.y;
            prevT = Date.now();
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function moveDraw(e) {
            e.preventDefault();
            if (!drawing) return;
            const pos = getPos(e);
            const now = Date.now();
            const dt = now - prevT || 1;
            const dx = pos.x - prevX;
            const dy = pos.y - prevY;
            const dist = Math.sqrt(dx * dx + dy * dy);
            const speed = dist / dt;
            const lineWidth = Math.max(1, Math.min(4, 6 - speed * 30));
            ctx.lineCap = 'butt';
            ctx.lineJoin = 'miter';
            ctx.strokeStyle = '#000';
            ctx.lineWidth = lineWidth;
            ctx.beginPath();
            ctx.moveTo(prevX, prevY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            prevX = pos.x;
            prevY = pos.y;
            prevT = now;
        }

        function endDraw(e) {
            e.preventDefault();
            drawing = false;
        }

        resizeCanvas();
        window.addEventListener("resize", resizeCanvas);

        canvas.addEventListener("mousedown", startDraw);
        canvas.addEventListener("mousemove", moveDraw);
        canvas.addEventListener("mouseup", endDraw);
        canvas.addEventListener("mouseleave", endDraw);
        canvas.addEventListener("touchstart", startDraw, { passive: false });
        canvas.addEventListener("touchmove", moveDraw, { passive: false });
        canvas.addEventListener("touchend", endDraw);

        $('#modal-signature').on('shown.bs.modal', resizeCanvas);

        $('#btn-create-ttd').click(() => $('#modal-signature').modal('show'));

        $('#sig-clear').click(() => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.beginPath();
        });

        $('#sig-submit').click(() => {
            const dataUrl = canvas.toDataURL("image/png");
            $.ajax({
                url: '<?= site_url("guru/saveSignature") ?>',
                type: 'POST',
                data: { image: dataUrl },
                success: (res) => {
                    if (res.status) {
                        $('#img-ttd-preview').attr('src', res.src).removeClass('d-none');
                        $('#ttd-placeholder').addClass('d-none');
                        $('#btn-delete-ttd').removeClass('d-none');
                        $('#modal-signature').modal('hide');
                        Swal.fire('Berhasil', res.message, 'success');
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                }
            });
        });

        $('#btn-delete-ttd').click(() => {
            $.ajax({
                url: '<?= site_url("guru/deleteTtd") ?>',
                type: 'POST',
                success: (res) => {
                    if (res.status) {
                        $('#img-ttd-preview').addClass('d-none');
                        $('#ttd-placeholder').removeClass('d-none');
                        $('#btn-delete-ttd').addClass('d-none');
                    }
                }
            });
        });

        // ========== AI API Key Logic ==========
        // Toggle password visibility
        $('#btn-toggle-key').click(function() {
            const input = $('#input-api-key');
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // Save API Key
        $('#btn-save-apikey').click(function() {
            const apiKey = $('#input-api-key').val().trim();
            const btn = $(this);
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memverifikasi...');

            $.ajax({
                url: '<?= site_url("guru/saveApiKey") ?>',
                type: 'POST',
                data: { api_key: apiKey },
                success: (res) => {
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 3000,
                            showConfirmButton: true
                        });
                        // Update status text
                        const statusDiv = $('#input-api-key').closest('.mb-3').find('.form-text');
                        if (apiKey) {
                            statusDiv.html('<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>API Key tersimpan dan terverifikasi</span>');
                        } else {
                            statusDiv.html('<span class="text-muted"><i class="bi bi-info-circle me-1"></i>Belum ada API Key. Menggunakan API Key sistem (jika ada).</span>');
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: () => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                },
                complete: () => {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Remove API Key
        $('#btn-remove-apikey').click(function() {
            Swal.fire({
                title: 'Hapus API Key?',
                text: 'API Key Anda akan dihapus. Fitur AI akan menggunakan API Key sistem (jika tersedia).',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#input-api-key').val('');
                    $('#btn-save-apikey').click();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>