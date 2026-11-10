<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-patch-check-fill text-primary me-2"></i>Update System
                </h3>
                <p class="text-muted small mb-0">Kelola pembaruan dan integritas infrastruktur sistem</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Update</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid">
        
        <!-- Status & Direct Update -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-3 mb-md-0">
                                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-4">
                                        <i class="bi bi-cloud-arrow-down fs-1"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold mb-1">Pembaruan Langsung</h4>
                                        <p class="text-muted mb-0">Versi Saat Ini: <span class="badge bg-light text-dark border fw-bold"><?= $current_version ?></span></p>
                                        <small class="text-muted">Periksa patch keamanan dan fitur terbaru langsung dari server pusat.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <button id="btnCheckUpdate" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-search me-2"></i> Periksa Update
                                </button>
                            </div>
                        </div>

                        <!-- Result Area -->
                        <div id="updateResult" class="mt-4" style="display:none;">
                            <div class="p-4 rounded border-dashed border-2 bg-light">
                                <!-- Status: Up to Date -->
                                <div id="alertUpToDate" style="display:none;">
                                    <div class="d-flex align-items-center text-success">
                                        <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-0">Sistem Terupdate</h6>
                                            <small class="text-muted">Aplikasi Anda sudah menggunakan versi terbaru (v<?= $current_version ?>).</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status: Update Available -->
                                <div id="alertUpdateAvailable" style="display:none;">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success bg-opacity-10 text-success p-3 rounded me-3">
                                                    <i class="bi bi-stars fs-3"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1 text-dark">Versi Baru Tersedia: <span id="newVersionText" class="text-success">v1.2.0</span></h6>
                                                    <div id="changelogContent" class="small text-muted"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto mt-3 mt-md-0">
                                            <a href="#" id="downloadLink" target="_blank" class="btn btn-success px-4 fw-bold">
                                                <i class="bi bi-download me-2"></i> Download Paket
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div id="alertError" style="display:none;" class="text-danger small fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><span id="errorMsg"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Offline Update -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-hdd-network text-indigo me-2"></i>Instalasi Manual
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-4">Unggah paket pembaruan (.backup) secara manual jika server tidak memiliki akses internet.</p>

                        <form action="<?= site_url('update/upload') ?>" method="post" enctype="multipart/form-data" id="formOfflineUpdate">
                            <?= csrf_field() ?>
                            <div class="manual-upload-area p-4 border border-dashed rounded text-center mb-4 cursor-pointer" id="dropZone">
                                <input type="file" name="file_update" class="file-input-hidden" id="fileUpdate" accept=".backup" required>
                                <div class="upload-placeholder">
                                    <i class="bi bi-cloud-upload text-muted fs-1 mb-2 d-block"></i>
                                    <span class="text-dark small d-block fw-bold">Klik untuk memilih berkas .backup</span>
                                    <span class="text-muted x-small" id="fileNamePlaceholder">Format: .backup | Maks. 50MB</span>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary w-100 py-2 fw-bold" type="submit">
                                <i class="bi bi-upload me-2"></i> Unggah & Instal
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Backup -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-shield-lock text-success me-2"></i>Cadangkan Sistem
                        </h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <p class="text-muted small mb-4">Amankan seluruh file dan data penting sebelum melakukan perubahan besar pada sistem.</p>
                        
                        <div class="alert alert-warning border-0 small mb-auto text-dark bg-opacity-10 bg-warning">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> Backup mencakup seluruh source code dan aset statis yang krusial. Pastikan ruang penyimpanan mencukupi.
                        </div>

                        <div class="d-grid mt-4">
                            <a href="<?= site_url('update/backup') ?>" id="btnDownloadBackup" class="btn btn-success py-2 fw-bold shadow-sm">
                                <i class="bi bi-archive me-2"></i> Buat Backup Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .text-indigo { color: #6610f2; }
    .bg-indigo-soft { background-color: rgba(102, 16, 242, 0.1); }
    .cursor-pointer { cursor: pointer; }
    .x-small { font-size: 0.75rem; }
    .border-dashed { border-style: dashed !important; }

    .manual-upload-area { position: relative; transition: all 0.2s; background-color: #f8f9fa; }
    .manual-upload-area:hover { background-color: #f1f3f5; border-color: #0d6efd !important; }
    .manual-upload-area input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }

    .fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('#btnCheckUpdate').click(function () {
        let btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Menghubungi Server...');

        $('#updateResult').hide();
        $('#alertUpToDate, #alertUpdateAvailable, #alertError').hide();

        $.ajax({
            url: '<?= site_url('update/check') ?>',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                $('#updateResult').fadeIn(400);

                if (res.status) {
                    if (res.has_update) {
                        $('#alertUpdateAvailable').show();
                        $('#newVersionText').text('v' + res.remote_version);
                        $('#changelogContent').html(res.changelog);
                        $('#downloadLink').attr('href', res.download_url);
                    } else {
                        $('#alertUpToDate').show();
                    }
                } else {
                    $('#alertError').show();
                    $('#errorMsg').text(res.message);
                }
            },
            error: function () {
                $('#updateResult').show();
                $('#alertError').show();
                $('#errorMsg').text('Gagal terhubung ke server update.');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-2"></i> Cek Ulang');
            }
        });
    });

    $('#formOfflineUpdate').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        let form = $(this);

        Swal.fire({
            title: 'Memasang Update',
            html: '<div class="text-muted small mb-3">Mohon tunggu, proses ekstraksi sedang berjalan...</div><div class="progress rounded-pill"><div id="p-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div></div>',
            showConfirmButton: false, allowOutsideClick: false,
            didOpen: () => {
                $.ajax({
                    xhr: function () {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", (evt) => {
                            if (evt.lengthComputable) {
                                let pct = Math.round((evt.loaded / evt.total) * 100);
                                $('#p-bar').css('width', pct + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false, contentType: false,
                    success: function (res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success', title: 'Berhasil',
                                text: res.message,
                                confirmButtonColor: '#0d6efd',
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: () => {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan saat update.' });
                    }
                });
            }
        });
    });

    $('#fileUpdate').change(function() {
        const file = this.files[0];
        if (file) {
            $('#fileNamePlaceholder').html('<b class="text-primary">' + file.name + '</b> (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');
        }
    });
</script>
<?= $this->endSection() ?>
