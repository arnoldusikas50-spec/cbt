<style>
    .update-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .update-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .version-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .version-current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .version-new {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .update-available-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .update-available-card .icon-big {
        font-size: 4rem;
        opacity: 0.3;
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .backup-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
    }

    .backup-item:hover {
        background: #e9ecef;
    }

    .changelog-content {
        max-height: 300px;
        overflow-y: auto;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        white-space: pre-wrap;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.875rem;
    }

    .step-indicator {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .step-indicator .step {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 0.75rem;
    }

    .step-indicator .step.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .step-indicator .step.done {
        background: #28a745;
        color: white;
    }
</style>

<main class="app-main pt-4 bg-light">
    <!-- Header -->
    <div class="content-header mb-4 illustration-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="m-0 font-weight-bold text-dark mb-1">
                        <i class="bi bi-cloud-download text-primary me-2"></i>Pembaruan Aplikasi
                    </h4>
                    <small class="text-muted">Kelola update dan backup sistem</small>
                </div>
                <a href="<?= base_url('sync') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Current Version Card -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card update-card shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-box-seam text-primary" style="font-size: 3rem;"></i>
                            <h6 class="text-muted mt-3 mb-2">Versi Saat Ini</h6>
                            <span class="version-badge version-current">
                                <i class="bi bi-tag-fill me-2"></i>v
                                <?= $current_version ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card update-card shadow-sm h-100" id="updateStatusCard">
                        <div class="card-body py-4">
                            <div id="checkingUpdate" class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p class="text-muted mb-0">Memeriksa pembaruan...</p>
                            </div>

                            <div id="noUpdate" style="display: none;" class="text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 font-weight-bold text-success">Aplikasi Sudah Terbaru!</h5>
                                <p class="text-muted mb-3">Tidak ada pembaruan yang tersedia.</p>
                                <button class="btn btn-outline-primary btn-sm" onclick="checkForUpdates()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Periksa Ulang
                                </button>
                            </div>

                            <div id="updateAvailable" style="display: none;">
                                <div class="d-flex align-items-start">
                                    <div class="me-4">
                                        <i class="bi bi-arrow-up-circle-fill text-success" style="font-size: 3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="font-weight-bold mb-1">
                                            Pembaruan Tersedia!
                                            <span id="criticalBadge" class="badge bg-danger ms-2"
                                                style="display: none;">Kritis</span>
                                        </h5>
                                        <p class="mb-2">
                                            <span class="version-badge version-new">
                                                <i class="bi bi-tag-fill me-1"></i>v<span id="newVersion"></span>
                                            </span>
                                        </p>
                                        <h6 id="updateTitle" class="font-weight-bold mt-3"></h6>
                                        <div id="updateDescription" class="changelog-content mt-2"></div>
                                        <div class="mt-3">
                                            <button class="btn btn-success btn-lg" id="btnInstallUpdate"
                                                onclick="installUpdate()">
                                                <i class="bi bi-download me-2"></i> Download & Install
                                            </button>
                                            <span class="text-muted ms-3">
                                                <i class="bi bi-hdd me-1"></i> <span id="fileSize"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="updateError" style="display: none;" class="text-center">
                                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 font-weight-bold">Gagal Memeriksa Update</h5>
                                <p class="text-muted mb-3" id="errorMessage"></p>
                                <button class="btn btn-outline-primary btn-sm" onclick="checkForUpdates()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Coba Lagi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup Management -->
            <div class="row">
                <div class="col-12">
                    <div class="card update-card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="font-weight-bold mb-0">
                                <i class="bi bi-archive text-primary me-2"></i>Backup & Rollback
                            </h5>
                            <button class="btn btn-primary" onclick="createBackup()">
                                <i class="bi bi-plus-circle me-1"></i> Buat Backup Sekarang
                            </button>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                Backup otomatis dibuat sebelum setiap update. Anda bisa rollback ke versi sebelumnya
                                jika terjadi masalah.
                            </p>

                            <div id="backupList">
                                <?php if (empty($backups)): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-3">Belum ada backup tersedia</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($backups as $backup): ?>
                                        <div class="backup-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="font-weight-bold mb-1">
                                                    <i class="bi bi-file-earmark-zip text-warning me-2"></i>
                                                    <?= $backup['name'] ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?= $backup['date'] ?>
                                                    <span class="mx-2">|</span>
                                                    <i class="bi bi-hdd me-1"></i>
                                                    <?= number_format($backup['size'] / 1024, 1) ?>
                                                    KB
                                                </small>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-warning"
                                                    onclick="rollbackTo('<?= $backup['path'] ?>')">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Rollback
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<!-- Modal Progress -->
<div class="modal fade" id="modalProgress" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="step-indicator justify-content-center mb-4">
                    <div class="step" id="step1">1</div>
                    <div class="flex-grow-1 mx-2" style="height: 2px; background: #e9ecef;"></div>
                    <div class="step" id="step2">2</div>
                    <div class="flex-grow-1 mx-2" style="height: 2px; background: #e9ecef;"></div>
                    <div class="step" id="step3">3</div>
                </div>
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                <h5 class="font-weight-bold" id="progressText">Memproses...</h5>
                <p class="text-muted small mb-0" id="progressSubtext">Mohon jangan tutup halaman ini.</p>
            </div>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?= base_url() ?>';
    const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
    const csrfHash = '<?= $this->security->get_csrf_hash() ?>';
    let updateData = null;

    // Setup CSRF for all AJAX POST requests
    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            if (settings.type === 'POST' && settings.data) {
                if (typeof settings.data === 'string') {
                    settings.data += '&' + csrfName + '=' + csrfHash;
                } else if (settings.data instanceof FormData) {
                    settings.data.append(csrfName, csrfHash);
                } else {
                    settings.data[csrfName] = csrfHash;
                }
            } else if (settings.type === 'POST') {
                settings.data = {};
                settings.data[csrfName] = csrfHash;
            }
        }
    });

    $(document).ready(function () {
        checkForUpdates();
    });

    function checkForUpdates() {
        $('#checkingUpdate').show();
        $('#noUpdate, #updateAvailable, #updateError').hide();

        $.ajax({
            url: BASE_URL + 'sync/check_update',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                $('#checkingUpdate').hide();

                if (res.status === 'success' && res.data) {
                    if (res.data.update_available) {
                        updateData = res.data;
                        showUpdateAvailable(res.data);
                    } else {
                        $('#noUpdate').show();
                    }
                } else {
                    showError(res.message || 'Tidak dapat memeriksa update');
                }
            },
            error: function (xhr) {
                $('#checkingUpdate').hide();
                showError('Koneksi gagal: ' + xhr.statusText);
            }
        });
    }

    function showUpdateAvailable(data) {
        $('#newVersion').text(data.latest_version);
        $('#updateTitle').text(data.title || 'Update ' + data.latest_version);
        $('#updateDescription').text(data.description || 'Tidak ada catatan rilis.');
        $('#fileSize').text(formatBytes(data.file_size));

        if (data.is_critical) {
            $('#criticalBadge').show();
        }

        if (!data.can_update) {
            $('#btnInstallUpdate').prop('disabled', true).text('Tidak Bisa Update');
        }

        $('#updateAvailable').show();
    }

    function showError(message) {
        $('#errorMessage').text(message);
        $('#updateError').show();
    }

    function installUpdate() {
        if (!updateData || !updateData.update_id) {
            Swal.fire('Error', 'Data update tidak valid', 'error');
            return;
        }

        Swal.fire({
            title: 'Install Update?',
            html: `
                <p>Anda akan menginstall versi <strong>${updateData.latest_version}</strong></p>
                <p class="text-muted small">Backup akan dibuat otomatis sebelum update.</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Install!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                doInstallUpdate();
            }
        });
    }

    function doInstallUpdate() {
        $('#modalProgress').modal('show');
        setProgress(1, 'Membuat backup...');

        setTimeout(function () {
            setProgress(2, 'Mengunduh update...');
        }, 1000);

        $.ajax({
            url: BASE_URL + 'sync/install_update',
            type: 'POST',
            data: {
                update_id: updateData.update_id,
                new_version: updateData.latest_version,
                [csrfName]: csrfHash
            },
            dataType: 'json',
            timeout: 600000, // 10 minutes
            success: function (res) {
                setProgress(3, 'Menginstall...');

                setTimeout(function () {
                    $('#modalProgress').modal('hide');

                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'Update Berhasil!',
                            html: `Aplikasi berhasil diupdate ke versi <strong>${res.new_version}</strong>.<br>Halaman akan direload.`,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        let msg = res.message;
                        if (res.rolled_back) {
                            msg += '<br><br><small class="text-muted">Aplikasi telah di-rollback ke versi sebelumnya.</small>';
                        }
                        Swal.fire('Gagal', msg, 'error');
                    }
                }, 1000);
            },
            error: function (xhr) {
                $('#modalProgress').modal('hide');
                Swal.fire('Error', 'Koneksi gagal: ' + xhr.statusText, 'error');
            }
        });
    }

    function setProgress(step, text) {
        for (let i = 1; i <= 3; i++) {
            const el = $('#step' + i);
            el.removeClass('active done');
            if (i < step) {
                el.addClass('done').html('<i class="bi bi-check"></i>');
            } else if (i === step) {
                el.addClass('active').text(i);
            } else {
                el.text(i);
            }
        }
        $('#progressText').text(text);
    }

    function createBackup() {
        Swal.fire({
            title: 'Membuat Backup...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: BASE_URL + 'sync/create_backup',
            type: 'POST',
            data: { [csrfName]: csrfHash },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire('Berhasil', 'Backup berhasil dibuat: ' + res.backup_name, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Gagal membuat backup', 'error');
            }
        });
    }

    function rollbackTo(backupPath) {
        Swal.fire({
            title: 'Rollback ke Backup Ini?',
            text: 'File aplikasi akan dikembalikan ke kondisi saat backup dibuat.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Rollback!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Melakukan Rollback...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: BASE_URL + 'sync/rollback_update',
                    type: 'POST',
                    data: { backup_file: backupPath, [csrfName]: csrfHash },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire('Berhasil', 'Rollback berhasil. Halaman akan direload.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal melakukan rollback', 'error');
                    }
                });
            }
        });
    }

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
</script>