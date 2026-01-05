<style>
    .update-package-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white;
    }

    .update-package-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .version-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .version-tag-active {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .version-tag-inactive {
        background: #e9ecef;
        color: #6c757d;
    }

    .version-tag-critical {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        color: white;
    }

    .upload-zone {
        border: 2px dashed #dee2e6;
        border-radius: 16px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s;
        background: #f8f9fa;
    }

    .upload-zone:hover,
    .upload-zone.dragover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }

    .upload-zone i {
        font-size: 3rem;
        color: #667eea;
    }

    .stats-mini {
        display: flex;
        gap: 1rem;
        font-size: 0.875rem;
    }

    .stats-mini .stat-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .changelog-preview {
        max-height: 100px;
        overflow-y: auto;
        font-size: 0.875rem;
        color: #6c757d;
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 8px;
        white-space: pre-wrap;
    }
</style>

<main class="app-main pt-4 bg-light">
    <!-- Header -->
    <div class="content-header mb-4 illustration-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="m-0 font-weight-bold text-dark mb-1">
                        <i class="bi bi-box-seam text-primary me-2"></i>Kelola Paket Update
                    </h4>
                    <small class="text-muted">Upload dan kelola paket update untuk distribusi ke sekolah</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary px-3 py-2">
                        <i class="bi bi-tag me-1"></i>Server: v
                        <?= $server_version ?>
                    </span>
                    <a href="<?= base_url('sync') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Upload New Update -->
            <div class="card update-package-card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="font-weight-bold mb-0">
                        <i class="bi bi-cloud-upload text-success me-2"></i>Upload Paket Update Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formUploadUpdate" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- File Upload Zone -->
                                <div class="upload-zone mb-3" id="uploadZone">
                                    <input type="file" id="updateFile" name="update_file" accept=".zip"
                                        style="display: none;">
                                    <i class="bi bi-file-earmark-zip mb-3"></i>
                                    <h6 class="font-weight-bold">Drag & drop file ZIP atau klik untuk memilih</h6>
                                    <small class="text-muted">File harus berformat .zip berisi folder
                                        application/</small>
                                    <div id="selectedFileName" class="mt-2 text-primary font-weight-bold"
                                        style="display:none;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Form Fields -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nomor Versi <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="version" id="inputVersion"
                                        placeholder="Contoh: 2.2.0" required pattern="\d+\.\d+\.\d+">
                                    <small class="text-muted">Format: major.minor.patch (x.y.z)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Judul Update <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" id="inputTitle"
                                        placeholder="Contoh: Perbaikan Bug Login" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Changelog / Deskripsi</label>
                                    <textarea class="form-control" name="description" id="inputDescription" rows="3"
                                        placeholder="Tuliskan perubahan yang dilakukan..."></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Min. Versi (Opsional)</label>
                                        <input type="text" class="form-control" name="min_version"
                                            placeholder="Contoh: 2.0.0">
                                        <small class="text-muted">Versi minimum untuk update ini</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_critical"
                                                id="isCritical">
                                            <label class="form-check-label fw-bold text-danger" for="isCritical">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Update Kritis (Security)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg w-100" id="btnUpload">
                                    <i class="bi bi-upload me-2"></i>Upload Paket Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List of Updates -->
            <div class="card update-package-card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold mb-0">
                        <i class="bi bi-collection text-primary me-2"></i>Daftar Paket Update
                    </h5>
                    <span class="badge bg-secondary">
                        <?= count($updates) ?> paket
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($updates)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">Belum ada paket update</h5>
                            <p class="text-muted">Upload paket update pertama Anda menggunakan form di atas.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Versi</th>
                                        <th>Judul</th>
                                        <th>Ukuran</th>
                                        <th class="text-center">Download</th>
                                        <th>Tanggal</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($updates as $upd): ?>
                                        <tr id="row-<?= $upd->id ?>">
                                            <td>
                                                <span
                                                    class="version-tag <?= $upd->is_critical ? 'version-tag-critical' : ($upd->is_active ? 'version-tag-active' : 'version-tag-inactive') ?>">
                                                    v
                                                    <?= $upd->version ?>
                                                    <?php if ($upd->is_critical): ?>
                                                        <i class="bi bi-exclamation-triangle-fill ms-1"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($upd->title) ?>
                                                </strong>
                                                <?php if ($upd->min_version): ?>
                                                    <br><small class="text-muted">Min: v
                                                        <?= $upd->min_version ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= number_format($upd->file_size / 1024 / 1024, 2) ?> MB
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    <?= $upd->download_count ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= date('d M Y', strtotime($upd->created_at)) ?>
                                                </small><br>
                                                <small class="text-muted">
                                                    <?= date('H:i', strtotime($upd->created_at)) ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($upd->is_active): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="viewDetails(<?= $upd->id ?>)"
                                                        title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-warning"
                                                        onclick="toggleUpdate(<?= $upd->id ?>)" title="Toggle Status">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger"
                                                        onclick="deleteUpdate(<?= $upd->id ?>, 'v<?= $upd->version ?>')"
                                                        title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>
</main>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detail Paket Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?= base_url() ?>';
    const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
    const csrfHash = '<?= $this->security->get_csrf_hash() ?>';
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('updateFile');

    // Setup CSRF for all AJAX POST requests (except FormData which we handle manually)
    $.ajaxSetup({
        beforeSend: function (xhr, settings) {
            if (settings.type === 'POST') {
                if (typeof settings.data === 'string') {
                    settings.data += '&' + csrfName + '=' + csrfHash;
                } else if (settings.data && !(settings.data instanceof FormData)) {
                    settings.data[csrfName] = csrfHash;
                } else if (!settings.data) {
                    settings.data = {};
                    settings.data[csrfName] = csrfHash;
                }
            }
        }
    });

    // Drag & Drop handlers
    uploadZone.addEventListener('click', () => fileInput.click());

    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            showSelectedFile(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            showSelectedFile(e.target.files[0]);
        }
    });

    function showSelectedFile(file) {
        const display = document.getElementById('selectedFileName');
        display.innerHTML = '<i class="bi bi-file-earmark-zip me-2"></i>' + file.name +
            ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        display.style.display = 'block';
    }

    // Form submit
    document.getElementById('formUploadUpdate').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        // Add CSRF token to FormData
        formData.append(csrfName, csrfHash);

        const btn = document.getElementById('btnUpload');
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...';

        $.ajax({
            url: BASE_URL + 'sync/upload_update',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        confirmButtonText: 'OK'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function (xhr) {
                Swal.fire('Error', 'Gagal mengupload: ' + xhr.statusText, 'error');
            },
            complete: function () {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });

    function toggleUpdate(id) {
        $.ajax({
            url: BASE_URL + 'sync/toggle_update/' + id,
            type: 'POST',
            data: { [csrfName]: csrfHash },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    }

    function deleteUpdate(id, version) {
        Swal.fire({
            title: 'Hapus Update ' + version + '?',
            text: 'File ZIP dan data akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + 'sync/delete_update/' + id,
                    type: 'POST',
                    data: { [csrfName]: csrfHash },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            $('#row-' + id).fadeOut(300, function () {
                                $(this).remove();
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }
                });
            }
        });
    }

    function viewDetails(id) {
        $('#modalDetail').modal('show');
        $('#modalDetailContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');

        $.ajax({
            url: BASE_URL + 'sync/update_stats/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    let html = '<h6 class="font-weight-bold mb-3">Riwayat Download</h6>';

                    if (res.data.length === 0) {
                        html += '<p class="text-muted text-center">Belum ada sekolah yang download update ini.</p>';
                    } else {
                        html += '<div class="table-responsive"><table class="table table-sm"><thead><tr>';
                        html += '<th>Sekolah</th><th>Status</th><th>Waktu</th></tr></thead><tbody>';
                        res.data.forEach(function (log) {
                            let statusBadge = log.status === 'installed' ? 'success' :
                                (log.status === 'downloaded' ? 'info' : 'danger');
                            html += '<tr>';
                            html += '<td>' + (log.nama_sekolah || log.school_uuid || '-') + '</td>';
                            html += '<td><span class="badge bg-' + statusBadge + '">' + log.status + '</span></td>';
                            html += '<td>' + log.created_at + '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';
                    }

                    $('#modalDetailContent').html(html);
                }
            }
        });
    }
</script>