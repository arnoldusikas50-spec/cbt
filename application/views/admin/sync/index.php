<style>
    /* Dashboard Modern Styles */
    .sync-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        color: #fff;
        overflow: hidden;
        position: relative;
    }

    .sync-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .sync-card .card-body {
        padding: 1.5rem;
        z-index: 2;
        position: relative;
    }

    .sync-card .icon-bg {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        opacity: 0.15;
        z-index: 1;
        transform: rotate(-15deg);
    }

    .sync-card-primary {
        background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
    }

    .sync-card-warning {
        background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
    }

    .sync-card-info {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .sync-card-accent {
        background: linear-gradient(135deg, #8360c3 0%, #2ebf91 100%);
    }

    .action-btn {
        border: none;
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #eee;
        transition: all 0.2s;
        cursor: pointer;
        width: 100%;
    }

    .action-btn:hover {
        background: #f8f9fa;
        border-color: #ddd;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .action-btn i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .action-badge {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
        font-weight: 600;
    }

    .card-clean {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: none;
        border-radius: 10px;
        margin-bottom: 1.5rem;
    }

    .card-clean .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, .05);
        padding: 1rem 1.5rem;
    }

    .spin {
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    .badge-soft-primary {
        background-color: #e6f2ff;
        color: #007bff;
        border: 1px solid #b3d7ff;
    }

    .badge-soft-success {
        background-color: #dbf2e3;
        color: #1e7e34;
        border: 1px solid #c3e6cb;
    }

    .badge-soft-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Standard Bootstrap Alert-Info colors for high readability */
    .badge-soft-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .badge-soft-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .table td {
        vertical-align: middle;
    }
</style>

<main class="app-main pt-4 bg-light">
    <!-- Header with Gradient Border -->
    <div class="content-header mb-4 illustration-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="m-0 font-weight-bold text-dark mb-1">
                        <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Sinkronisasi
                    </h4>
                    <small class="text-muted">Pantau status koneksi dan antrian data.</small>
                </div>
                <span class="badge bg-primary px-3 py-2 rounded-pill">
                    <i class="bi bi-tag-fill me-1"></i>v<?= isset($app_version) ? $app_version : '1.0.0' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Update Notification Banner (for local server only) -->
    <?php if (!$is_pusat): ?>
        <div id="updateBanner" class="alert alert-info border-0 shadow-sm mx-3 mb-3"
            style="display: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px;">
            <div class="d-flex align-items-center text-white">
                <i class="bi bi-arrow-up-circle-fill me-3" style="font-size: 2rem;"></i>
                <div class="flex-grow-1">
                    <h6 class="font-weight-bold mb-0">
                        <span id="updateBannerTitle">Update Tersedia!</span>
                        <span id="updateBannerVersion" class="badge bg-light text-dark ms-2"></span>
                        <span id="updateBannerCritical" class="badge bg-danger ms-1" style="display: none;">Kritis</span>
                    </h6>
                    <small id="updateBannerDesc" class="opacity-75"></small>
                </div>
                <a href="<?= base_url('sync/updates') ?>" class="btn btn-light btn-sm font-weight-bold px-4 rounded-pill">
                    <i class="bi bi-download me-1"></i> Lihat Update
                </a>
            </div>
        </div>
    <?php endif; ?>

    <section class="content">
        <div class="container-fluid">
            <!-- Server Info Banner -->
            <?php if (!$config): ?>
                <div class="alert alert-warning border-0 shadow-sm rounded-lg mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fa-2x me-3"></i>
                        <div>
                            <h5 class="alert-heading font-weight-bold mb-1">Konfigurasi Dibutuhkan</h5>
                            <p class="mb-0">Silakan lakukan konfigurasi awal untuk memulai sinkronisasi.</p>
                        </div>
                        <a href="<?= base_url('sync/config') ?>"
                            class="btn btn-warning btn-sm font-weight-bold ms-auto px-4 rounded-pill">Setup Sekarang</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert bg-white border-0 shadow-sm rounded-lg mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="bg-light p-2 rounded-circle me-3">
                            <i class="bi bi-<?= ($is_pusat) ? 'cloud-check' : 'hdd-network' ?> text-primary"
                                style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <span class="text-uppercase text-muted small font-weight-bold letter-spacing-1">Mode
                                Server</span>
                            <h5 class="mb-0 font-weight-bold text-dark">
                                <?= ($is_pusat) ? 'Server Pusat (Cloud)' : 'Server Lokal' ?>
                            </h5>
                        </div>
                        <?php if (!$is_pusat): ?>
                            <div class="ms-auto d-none d-md-block text-end">
                                <span class="text-muted small">URL Pusat:</span>
                                <div class="font-weight-medium text-primary">
                                    <?= (isset($config) && is_object($config) && isset($config->pusat_url)) ? $config->pusat_url : '-' ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Row -->
            <div class="row mb-5">
                <!-- Status Koneksi -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="sync-card sync-card-primary">
                        <div class="card-body d-flex flex-column justify-content-between h-100">
                            <div>
                                <h3 id="connectionStatus" class="font-weight-bold mb-1"><i
                                        class="bi bi-arrow-repeat spin"></i></h3>
                                <div class="action-badge">Status Koneksi</div>
                            </div>

                            <div id="connectedSchoolInfo" class="mt-3 pt-3 border-top border-white-50"
                                style="display:none;">
                                <div id="schoolName" class="font-weight-bold text-truncate" style="font-size: 0.95rem;">
                                </div>
                                <div class="small opacity-75">NPSN: <span id="schoolNpsn"></span></div>
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <button onclick="testConnection()"
                                    class="btn btn-sm btn-primary text-white font-weight-bold rounded-pill px-3 shadow-sm border-0 flex-grow-1">
                                    <i class="bi bi-funnel"></i> Test Koneksi
                                </button>
                                <button onclick="resetSync()"
                                    class="btn btn-sm btn-outline-light text-white font-weight-bold rounded-pill px-3 shadow-sm border-0"
                                    title="Download ulang semua data">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                            </div>
                            <div class="mt-2 text-center">
                                <a href="javascript:void(0)" onclick="undoSync()"
                                    class="text-white text-xs text-decoration-underline opacity-75 small">
                                    <i class="bi bi-trash"></i> Hapus data 30 menit terakhir (Undo)
                                </a>
                            </div>
                        </div>
                        <i class="bi bi-wifi icon-bg"></i>
                    </div>
                </div>

                <!-- Pending -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="sync-card sync-card-warning">
                        <div class="card-body">
                            <h3 class="font-weight-bold mb-1"><?= number_format($pending_records) ?></h3>
                            <div class="action-badge">Data Pending Sync</div>
                            <div class="mt-4 pt-2">
                                <button onclick="refreshStatus()"
                                    class="btn btn-sm btn-outline-light text-white rounded-pill px-3">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <i class="bi bi-hourglass-split icon-bg"></i>
                    </div>
                </div>

                <!-- Antrian -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="sync-card sync-card-accent">
                        <div class="card-body">
                            <h3 class="font-weight-bold mb-1"><?= number_format($queue_count) ?></h3>
                            <div class="action-badge">Antrian Proses</div>
                            <div class="mt-4 pt-2 text-white-50 small">
                                Otomatis diproses background
                            </div>
                        </div>
                        <i class="bi bi-list-check icon-bg"></i>
                    </div>
                </div>

                <!-- Last Sync -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="sync-card sync-card-info">
                        <div class="card-body">
                            <h5 class="font-weight-bold mb-1" style="font-size: 1.25rem;">
                                <?php if ($last_sync): ?>
                                    <?= date('d/m H:i', strtotime($last_sync->created_at)) ?>
                                <?php else: ?>
                                    Belum Pernah
                                <?php endif; ?>
                            </h5>
                            <div class="action-badge">Sinkronisasi Terakhir</div>
                            <a href="#syncLogSection"
                                class="text-white small mt-4 d-inline-block text-decoration-none border-bottom border-white pb-1">Lihat
                                Riwayat <i class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                        <i class="bi bi-calendar-check icon-bg"></i>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Bar (Bottom) -->
            <?php if (!$is_pusat): ?>
                <!-- Server Lokal Action Buttons -->
                <div class="bg-white rounded-lg shadow-sm p-3 mb-4">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="text-muted me-2 font-weight-bold small text-uppercase">Aksi:</span>

                        <button class="btn btn-warning btn-sm px-3 shadow-sm" onclick="previewSync()">
                            <i class="bi bi-search me-1"></i> Cek Data Server
                        </button>

                        <button class="btn btn-info btn-sm px-3 shadow-sm text-white"
                            onclick="openActionSelect('download')">
                            <i class="bi bi-cloud-download me-1"></i> Download
                        </button>

                        <button class="btn btn-success btn-sm px-3 shadow-sm" onclick="openActionSelect('upload')">
                            <i class="bi bi-cloud-upload me-1"></i> Upload
                        </button>

                        <div class="vr mx-2 d-none d-md-block"></div>

                        <a href="<?= base_url('sync/updates') ?>" class="btn btn-outline-primary btn-sm px-3">
                            <i class="bi bi-box-arrow-down me-1"></i> Patch Update
                        </a>

                        <a href="<?= base_url('sync/conflicts') ?>"
                            class="btn btn-outline-warning btn-sm px-3 position-relative">
                            <i class="bi bi-exclamation-triangle me-1"></i> Cek Konflik
                            <span id="conflictBadge"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="display: none;">0</span>
                        </a>

                        <a href="<?= base_url('sync/config') ?>" class="btn btn-outline-secondary btn-sm px-3 ms-auto">
                            <i class="bi bi-gear me-1"></i> Konfigurasi
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Server Pusat Action Buttons -->
                <div class="bg-white rounded-lg shadow-sm p-3 mb-4">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="text-muted me-2 font-weight-bold small text-uppercase">Menu Pusat:</span>

                        <a href="<?= base_url('sync/schools') ?>" class="btn btn-info btn-sm px-3 shadow-sm text-white">
                            <i class="bi bi-building me-1"></i> Kelola Sekolah
                        </a>

                        <button class="btn btn-success btn-sm px-3 shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalAddSchool">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Sekolah
                        </button>

                        <a href="<?= base_url('sync/manage_updates') ?>" class="btn btn-primary btn-sm px-3 shadow-sm">
                            <i class="bi bi-box-seam me-1"></i> Kelola Update
                        </a>

                        <a href="<?= base_url('sync/config') ?>" class="btn btn-outline-secondary btn-sm px-3 ms-auto">
                            <i class="bi bi-gear me-1"></i> Konfigurasi
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Sync Overview & Logs -->
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="card card-clean bg-white h-100">
                        <div class="card-header d-flex align-items-center">
                            <h6 class="font-weight-bold m-0"><i class="bi bi-table text-primary me-2"></i>Status Tabel
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 text-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Tabel</th>
                                        <th>Data Lokal</th>
                                        <th>Arah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sync_tables as $table => $config): ?>
                                        <tr>
                                            <td class="font-weight-bold text-dark"><?= $table ?></td>
                                            <td><?= number_format($config['total_records']) ?></td>
                                            <td>
                                                <?php if ($config['direction'] === 'download'): ?>
                                                    <span class="badge badge-soft-info px-3 py-2 rounded-pill"><i
                                                            class="bi bi-arrow-down me-1"></i> Download</span>
                                                <?php elseif ($config['direction'] === 'upload'): ?>
                                                    <span class="badge badge-soft-success px-3 py-2 rounded-pill"><i
                                                            class="bi bi-arrow-up me-1"></i> Upload</span>
                                                <?php else: ?>
                                                    <span class="badge badge-soft-primary px-3 py-2 rounded-pill"><i
                                                            class="bi bi-arrow-left-right me-1"></i> Dua Arah</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4" id="syncLogSection">
                    <div class="card card-clean bg-white h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold m-0"><i
                                    class="bi bi-clock-history text-success me-2"></i>Riwayat Sync</h6>
                            <button class="btn btn-sm btn-warning shadow-sm ms-auto" onclick="refreshLog()"
                                title="Refresh Log"><i class="bi bi-arrow-repeat me-2"></i> Reload</button>
                        </div>
                        <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">
                            <!-- Added w-100 to force full width -->
                            <table class="table table-hover mb-0 text-sm w-100" id="syncLogTable">
                                <thead class="bg-light sticky-top" style="z-index: 1;">
                                    <tr>
                                        <!-- Adjusted widths for better proportion -->
                                        <th style="width: 25%;">Waktu</th>
                                        <th style="width: 50%;">Tipe</th>
                                        <th class="text-right" style="width: 25%;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sync_log)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Belum ada riwayat</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sync_log as $log): ?>
                                            <tr>
                                                <td class="text-muted"><?= date('d/m H:i', strtotime($log->created_at)) ?></td>
                                                <td>
                                                    <?php if ($log->sync_type === 'download'): ?>
                                                        <span class="badge badge-soft-info"><i class="bi bi-arrow-down"></i>
                                                            Down</span>
                                                    <?php elseif ($log->sync_type === 'upload'): ?>
                                                        <span class="badge badge-soft-success"><i class="bi bi-arrow-up"></i>
                                                            Up</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-soft-primary"><i class="bi bi-arrow-repeat"></i>
                                                            Sync</span>
                                                    <?php endif; ?>
                                                    <div class="small text-muted mt-1 font-weight-bold"><?= $log->table_name ?>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <?php if ($log->status === 'success'): ?>
                                                        <span class="badge badge-soft-success"><?= $log->records_success ?>
                                                            OK</span>
                                                    <?php elseif ($log->status === 'failed'): ?>
                                                        <span class="badge badge-soft-danger">Gagal</span>
                                                        <i class="bi bi-info-circle text-danger cursor-pointer ms-1"
                                                            onclick='showErrorDetail(<?= json_encode($log->error_message) ?>)'></i>
                                                    <?php else: ?>
                                                        <span
                                                            class="badge badge-soft-warning"><?= $log->records_success ?>/<?= $log->records_total ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>


<!-- ================= MODALS ================= -->

<!-- Modal Add School -->
<div class="modal fade" id="modalAddSchool" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrasi Sekolah</h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <form id="formAddSchool">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Sekolah</label>
                        <input type="text" name="school_name" class="form-control" required
                            placeholder="Contoh: SMA Negeri 1 ...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal Action Select (New robust replacement for dropdowns) -->
<div class="modal fade" id="modalActionSelect" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title font-weight-bold" id="actionSelectTitle">Pilih Opsi</h6>
                <button type="button" class="close" data-bs-dismiss="modal">×</button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3" id="actionSelectDesc">Pilih jenis data:</p>

                <button class="btn btn-light w-100 mb-3 text-start py-3 shadow-sm border" id="btnActionDb"
                    style="display:flex; align-items:center;">
                    <i class="bi bi-database fa-lg me-3"></i>
                    <div style="line-height:1.2;">
                        <span class="d-block font-weight-bold">Database</span>
                        <small class="text-muted">Data soal, nilai, siswa</small>
                    </div>
                </button>
                <button class="btn btn-light w-100 text-start py-3 shadow-sm border" id="btnActionMedia"
                    style="display:flex; align-items:center;">
                    <i class="bi bi-images fa-lg me-3"></i>
                    <div style="line-height:1.2;">
                        <span class="d-block font-weight-bold">File Media</span>
                        <small class="text-muted">Gambar & Audio</small>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal API Keys -->
<div class="modal fade" id="modalApiKeys" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Sekolah Terdaftar!</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Silakan berikan kredensial berikut kepada admin sekolah:</p>
                <div class="form-group">
                    <label>API Key</label>
                    <div class="input-group">
                        <input type="text" id="apiKeyDisplay" class="form-control" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="copyToClipboard('apiKeyDisplay')"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>API Secret <span class="text-danger font-weight-bold">(RAHASIA)</span></label>
                    <div class="input-group">
                        <input type="text" id="apiSecretDisplay" class="form-control" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="copyToClipboard('apiSecretDisplay')"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Progress Sync -->
<!-- Modal Progress Sync -->
<div class="modal fade" id="modalSyncProgress" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white py-2">
                <h5 class="modal-title font-weight-light"><i class="bi bi-arrow-repeat mr-2"></i>Sync Progress Status
                </h5>
            </div>
            <div class="modal-body p-0" style="max-height: 60vh; overflow-y: auto;">
                <div id="syncProgressList" class="list-group list-group-flush">
                    <!-- Dynamic Content -->
                </div>
            </div>
            <div class="modal-footer bg-light py-1 justify-content-center">
                <small class="text-muted">Mohon jangan tutup halaman ini selama proses berlangsung.</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Error Detail -->
<div class="modal fade" id="modalErrorDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Detail Error</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <pre id="errorDetailContent" class="bg-light p-3 m-0 text-danger"
                    style="white-space: pre-wrap; word-break: break-all; max-height: 500px; overflow-y: scroll;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview -->
<div class="modal fade" id="modalPreview" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title font-weight-bold"><i class="bi bi-search text-primary"></i> Preview Sinkronisasi
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div id="previewLoading" class="text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Membandingkan data...</p>
                </div>
                <div id="previewContent" style="display:none;">
                    <table class="table table-striped mb-0">
                        <thead class="bg-light text-muted text-uppercase text-xs font-weight-bold">
                            <tr>
                                <th class="ps-4">Tabel / File</th>
                                <th class="text-center">Lokal</th>
                                <th class="text-center">Pusat</th>
                                <th class="text-center">Selisih</th>
                                <th class="text-center">Saran</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody">
                            <!-- Rows populated by JS -->
                        </tbody>
                    </table>
                    <div class="p-3 bg-light text-end">
                        <small class="text-muted">*Data selisih positif (+) berarti lokal lebih banyak (perlu
                            upload).</small><br>
                        <small class="text-muted">*Data selisih negatif (-) berarti pusat lebih banyak (perlu
                            download).</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <div class="btn-group">
                    <button type="button" class="btn btn-info"
                        onclick="$('#modalPreview').modal('hide'); executeSync('download_data')">
                        <i class="bi bi-cloud-download-fill"></i> Download
                    </button>
                    <button type="button" class="btn btn-success"
                        onclick="$('#modalPreview').modal('hide'); executeSync('upload_data')">
                        <i class="bi bi-cloud-upload-fill"></i> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?= base_url() ?>';
    const IS_PUSAT = <?= $is_pusat ? 'true' : 'false' ?>;

    $(document).ready(function () {
        testConnection();

        // Check for updates (local server only)
        if (!IS_PUSAT) {
            checkForUpdates();
            checkConflicts();
        }

        // Form submit handler
        $('#formAddSchool').on('submit', function (e) {
            e.preventDefault();
            registerSchool();
        });
    });

    // Check for pending conflicts (silent)
    function checkConflicts() {
        $.ajax({
            url: BASE_URL + 'sync/get_conflict_stats',
            type: 'GET',
            dataType: 'json',
            timeout: 5000,
            success: function (res) {
                if (res.status === 'success' && res.data && res.data.pending > 0) {
                    $('#conflictBadge').text(res.data.pending).show();
                }
            }
        });
    }

    // Check if updates are available (silent check)
    function checkForUpdates() {
        $.ajax({
            url: BASE_URL + 'sync/check_update',
            type: 'GET',
            dataType: 'json',
            timeout: 10000,
            success: function (res) {
                if (res.status === 'success' && res.data && res.data.update_available) {
                    showUpdateBanner(res.data);
                }
            },
            error: function () {
                // Silent fail - don't show error for background check
            }
        });
    }

    function showUpdateBanner(data) {
        $('#updateBannerVersion').text('v' + data.latest_version);
        $('#updateBannerDesc').text(data.title || 'Versi terbaru tersedia');

        if (data.is_critical) {
            $('#updateBannerCritical').show();
        }

        $('#updateBanner').slideDown(300);
    }

    function openActionSelect(type) {
        let title, desc;
        const $btnDb = $('#btnActionDb');
        const $btnMedia = $('#btnActionMedia');
        const $iconDb = $btnDb.find('i');
        const $iconMedia = $btnMedia.find('i');

        // Reset classes
        $btnDb.removeClass('btn-outline-primary btn-outline-success');
        $btnMedia.removeClass('btn-outline-primary btn-outline-success');
        $iconDb.removeClass('text-primary text-success');
        $iconMedia.removeClass('text-primary text-success');

        if (type === 'download') {
            title = 'Download Opsi';
            desc = 'Apa yang ingin Anda download dari Server Pusat?';

            $btnDb.attr('onclick', "executeSync('download_data'); $('#modalActionSelect').modal('hide');");
            $iconDb.addClass('text-info');

            $btnMedia.attr('onclick', "executeSync('download_media'); $('#modalActionSelect').modal('hide');");
            $iconMedia.addClass('text-info');
        } else {
            title = 'Upload Opsi';
            desc = 'Apa yang ingin Anda upload ke Server Pusat?';

            $btnDb.attr('onclick', "executeSync('upload_data'); $('#modalActionSelect').modal('hide');");
            $iconDb.addClass('text-success');

            $btnMedia.attr('onclick', "executeSync('upload_media'); $('#modalActionSelect').modal('hide');");
            $iconMedia.addClass('text-success');
        }

        $('#actionSelectTitle').text(title);
        $('#actionSelectDesc').text(desc);
        $('#modalActionSelect').modal('show');
    }

    // Preview & Compare Data
    async function previewSync() {
        $('#modalPreview').modal('show');
        $('#previewLoading').show();
        $('#previewContent').hide();

        try {
            const res = await $.ajax({
                url: BASE_URL + 'sync/get_comparison',
                type: 'POST',
                dataType: 'json'
            });

            if (res.status === 'success') {
                let html = '';
                const data = res.data;

                for (const table in data) {
                    const local = data[table].local || 0;
                    const pusat = data[table].pusat || 0;
                    const diff = local - pusat;

                    let statusBadge = '';
                    let diffBadge = '';

                    if (diff === 0) {
                        statusBadge = '<span class="badge badge-soft-success font-weight-bold px-3 py-1"><i class="bi bi-check-circle-fill mr-1"></i> Sinkron</span>';
                        diffBadge = '<span class="text-muted font-weight-bold">0</span>';
                    } else if (diff > 0) {
                        statusBadge = '<span class="badge badge-soft-success font-weight-bold px-3 py-1"><i class="bi bi-arrow-up mr-1"></i> Upload</span>';
                        diffBadge = '<span class="text-success font-weight-bold">+' + diff + '</span>';
                    } else {
                        statusBadge = '<span class="badge badge-soft-info font-weight-bold px-3 py-1"><i class="bi bi-arrow-down mr-1"></i> Download</span>';
                        diffBadge = '<span class="text-danger font-weight-bold">' + diff + '</span>';
                    }

                    let tableName = table;
                    if (table === 'media_files') {
                        tableName = '<i class="bi bi-images text-info mr-2"></i> File Media';
                    } else {
                        tableName = `<span class="font-weight-bold text-dark">${table}</span>`;
                    }

                    html += `<tr>
                        <td class="pl-4">${tableName}</td>
                        <td class="text-center">${local}</td>
                        <td class="text-center">${pusat}</td>
                        <td class="text-center">${diffBadge}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>`;
                }

                $('#previewTableBody').html(html);
                $('#previewLoading').hide();
                $('#previewContent').show();
            } else {
                Swal.fire('Error', res.message || 'Gagal mengambil data perbandingan', 'error');
                $('#modalPreview').modal('hide');
            }
        } catch (error) {
            console.error('Preview Error:', error);
            Swal.fire('Error', 'Gagal terhubung ke server', 'error');
            $('#modalPreview').modal('hide');
        }
    }

    function testConnection() {
        $('#connectionStatus').html('<i class="bi bi-arrow-repeat spin"></i>');

        $.ajax({
            url: BASE_URL + 'sync/test_connection',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#connectionStatus').html('<i class="bi bi-check-circle-fill text-white"></i>');
                    if (res.data && res.data.school) {
                        $('#schoolName').text(res.data.school);
                        $('#schoolNpsn').text(res.data.npsn || '-');
                        $('#connectedSchoolInfo').slideDown();
                    }
                } else {
                    $('#connectionStatus').html('<i class="bi bi-x-circle-fill text-white"></i>');
                }
            },
            error: function () {
                $('#connectionStatus').html('<i class="bi bi-wifi-off text-white"></i>');
            }
        });
    }

    async function executeSync(type) {
        let title = '';
        let text = '';

        switch (type) {
            case 'download_data':
                title = 'Download Database';
                text = 'Ambil data terbaru dari server pusat?';
                break;
            case 'upload_data':
                title = 'Upload Database';
                text = 'Kirim data database ke server pusat?';
                break;
            case 'upload_media':
                title = 'Upload Media';
                text = 'Kirim file gambar/media ke server pusat?';
                break;
            case 'download_media':
                title = 'Download Media';
                text = 'Ambil file gambar/media dari server pusat?';
                break;
        }

        const result = await Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#007bff'
        });

        if (result.isConfirmed) {
            $('#modalSyncProgress').modal('show');
            $('#syncProgressText').text('Menyiapkan...');
            $('.progress-bar').css('width', '5%');

            try {
                if (type === 'upload_media') {
                    await executeUploadMediaWithProgress();
                } else if (type === 'upload_data') {
                    await executeUploadData();
                } else if (type === 'download_data') {
                    await executeDownloadWithProgress();
                } else if (type === 'download_media') {
                    await executeDownloadMediaWithProgress();
                }
            } catch (e) {
                console.error(e);
                $('#modalSyncProgress').modal('hide');
                Swal.fire('Error', 'Terjadi kesalahan: ' + (e.message || JSON.stringify(e)), 'error');
            }
        }
    }

    // Reuse existing extensive JS logic for uploads/downloads/media...
    // (Pasting the complex functions here for completeness)

    async function executeDownloadMediaWithProgress() {
        const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
        const csrfHash = '<?= $this->security->get_csrf_hash() ?>';
        $('#syncProgressText').text('Memeriksa file media...');

        try {
            const [pusatRes, localRes] = await Promise.all([
                $.ajax({ url: BASE_URL + 'sync/get_media_list_pusat', type: 'GET', dataType: 'json' }),
                $.ajax({ url: BASE_URL + 'sync/get_local_media_list', type: 'GET', dataType: 'json' })
            ]);

            if (!pusatRes.status || pusatRes.status !== 'success') throw new Error(pusatRes.message || 'Gagal akses pusat');
            const pusatFiles = pusatRes.data.files || [];
            const localFiles = localRes.data || [];

            const localMap = new Map();
            localFiles.forEach(f => localMap.set(f.name, parseInt(f.size)));

            const pending = pusatFiles.filter(p => {
                const lSize = localMap.get(p.name);
                return lSize === undefined || lSize !== parseInt(p.size);
            });

            if (pending.length === 0) {
                $('#modalSyncProgress').modal('hide');
                Swal.fire('Info', 'Media sudah sinkron.', 'success');
                return;
            }

            // UI Setup
            let html = `
            <div class="list-group-item p-3 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 30px;">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 font-weight-bold text-dark">Download Media</h6>
                            <small class="text-primary font-weight-bold" id="dl-media-status">0/${pending.length}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div id="dl-media-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block" id="dl-media-msg">Menyiapkan...</small>
                    </div>
                </div>
            </div>`;
            $('#syncProgressList').html(html);
            $('#modalSyncProgress').modal('show');

            let success = 0, fail = 0;
            for (let i = 0; i < pending.length; i++) {
                const f = pending[i];
                const pct = Math.round(((i + 1) / pending.length) * 100);

                $('#dl-media-status').text(`${i + 1}/${pending.length}`);
                $('#dl-media-bar').css('width', pct + '%');
                $('#dl-media-msg').text(`Mendownload: ${f.name}`);

                try {
                    await $.ajax({
                        url: BASE_URL + 'sync/download_media_file',
                        type: 'POST',
                        data: { url: f.url, filename: f.name, [csrfName]: csrfHash },
                        dataType: 'json'
                    });
                    success++;
                } catch (e) { fail++; }
            }
            handleSyncSuccess({ status: 'success', message: `Selesai. Sukses: ${success}, Gagal: ${fail}` });
        } catch (e) {
            $('#modalSyncProgress').modal('hide');
            Swal.fire('Error', e.message, 'error');
        }
    }

    async function executeUploadMediaWithProgress() {
        $('#syncProgressText').text('Memeriksa file media...');
        try {
            const [pusatRes, localRes] = await Promise.all([
                $.ajax({ url: BASE_URL + 'sync/get_media_list_pusat', type: 'GET', dataType: 'json' }),
                $.ajax({ url: BASE_URL + 'sync/get_local_media_list', type: 'GET', dataType: 'json' })
            ]);

            const pusatFiles = pusatRes.data.files || [];
            const localFiles = localRes.data || [];
            const pusatMap = new Map();
            pusatFiles.forEach(f => pusatMap.set(f.name, parseInt(f.size)));

            const pending = localFiles.filter(l => {
                const pSize = pusatMap.get(l.name);
                return pSize === undefined || pSize !== parseInt(l.size);
            });

            if (pending.length === 0) {
                $('#modalSyncProgress').modal('hide');
                Swal.fire('Info', 'Media sudah sinkron.', 'success');
                return;
            }

            let success = 0, fail = 0;

            // UI Setup
            let html = `
            <div class="list-group-item p-3 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 30px;">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 font-weight-bold text-dark">Upload Media</h6>
                            <small class="text-primary font-weight-bold" id="media-status-text">0/${pending.length}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div id="media-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block" id="media-msg-text">Menyiapkan...</small>
                    </div>
                </div>
            </div>`;
            $('#syncProgressList').html(html);
            $('#modalSyncProgress').modal('show');

            for (let i = 0; i < pending.length; i++) {
                const f = pending[i];
                const pct = Math.round(((i + 1) / pending.length) * 100);

                $('#media-status-text').text(`${i + 1}/${pending.length}`);
                $('#media-progress-bar').css('width', pct + '%');
                $('#media-msg-text').text(`Mengupload: ${f.name}`);
                try {
                    await $.ajax({
                        url: BASE_URL + 'sync/upload_single_media',
                        type: 'POST',
                        data: { path: 'uploads/bank_soal/' + f.name },
                        dataType: 'json'
                    });
                    success++;
                } catch (e) { fail++; }
            }
            handleSyncSuccess({ status: 'success', message: `Selesai. Sukses: ${success}, Gagal: ${fail}` });
        } catch (e) {
            $('#modalSyncProgress').modal('hide');
            Swal.fire('Error', e.message, 'error');
        }
    }

    async function executeUploadData() {
        // UI Setup
        let html = `
        <div class="list-group-item p-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="mr-3 text-center" style="width: 30px;">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 font-weight-bold text-dark">Upload Data</h6>
                        <small class="text-primary font-weight-bold">Proses...</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">Sedang mengupload data ke server pusat...</small>
                </div>
            </div>
        </div>`;
        $('#syncProgressList').html(html);
        $('#modalSyncProgress').modal('show');

        // Execute
        const res = await $.ajax({
            url: BASE_URL + 'sync/upload',
            type: 'POST',
            data: { skip_media: true },
            dataType: 'json'
        });
        handleSyncSuccess(res);
    }

    async function executeDownloadWithProgress() {
        const tables = <?= json_encode(array_keys(array_filter($sync_tables ?? [], function ($t) {
            return $t['direction'] !== 'upload';
        }))) ?>;

        // 1. Initialize List
        let listHtml = '';
        tables.forEach((t, index) => {
            let label = t.replace(/_/g, ' ').toUpperCase();
            listHtml += `
            <div class="list-group-item p-3 border-bottom" id="sync-item-${index}">
                <div class="d-flex align-items-center">
                    <div class="mr-3 text-center" style="width: 30px;" id="sync-icon-${index}">
                        <i class="bi bi-hourglass text-muted" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 0.9rem;">${label}</h6>
                            <small class="text-secondary font-weight-bold" id="sync-status-${index}">Menunggu</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div id="sync-bar-${index}" class="progress-bar bg-secondary" style="width: 0%"></div>
                        </div>
                        <small class="text-muted mt-1 d-block" id="sync-msg-${index}"></small>
                    </div>
                </div>
            </div>`;
        });

        $('#syncProgressList').html(listHtml);
        // Ensure progress bar selector in executeSync didn't mess things up

        let success = 0, failed = 0;

        for (let i = 0; i < tables.length; i++) {
            const t = tables[i];
            const idx = i;

            // 2. Set Active State
            $(`#sync-item-${idx}`).addClass('bg-soft-primary'); // Optional highlighting
            $(`#sync-icon-${idx}`).html('<div class="spinner-border spinner-border-sm text-primary"></div>');
            $(`#sync-status-${idx}`).text('Proses...').removeClass('text-secondary').addClass('text-primary');
            $(`#sync-bar-${idx}`).removeClass('bg-secondary').addClass('progress-bar-striped progress-bar-animated bg-primary').css('width', '100%');
            $(`#sync-msg-${idx}`).text('Sedang mendownload data...');

            // Auto-scroll
            const itemEl = document.getElementById(`sync-item-${idx}`);
            if (itemEl) itemEl.scrollIntoView({ behavior: 'smooth', block: 'center' });

            try {
                const res = await $.ajax({
                    url: BASE_URL + 'sync/download_table',
                    type: 'POST',
                    data: { table: t },
                    dataType: 'json'
                });

                // 3. Set Done State
                if (res.status === 'success') {
                    success += (res.success || 0);
                    $(`#sync-icon-${idx}`).html('<i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>');
                    $(`#sync-status-${idx}`).text('Selesai').removeClass('text-primary').addClass('text-success');
                    $(`#sync-bar-${idx}`).removeClass('progress-bar-striped progress-bar-animated bg-primary').addClass('bg-success').css('width', '100%');
                    $(`#sync-msg-${idx}`).html(`Sukses: <b>${res.success}</b>, Gagal: ${res.failed || 0}`);
                } else {
                    failed += (res.failed || 0);
                    $(`#sync-icon-${idx}`).html('<i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 1.5rem;"></i>');
                    $(`#sync-status-${idx}`).text('Gagal').removeClass('text-primary').addClass('text-danger');
                    $(`#sync-bar-${idx}`).removeClass('progress-bar-striped progress-bar-animated bg-primary').addClass('bg-danger').css('width', '100%');
                    $(`#sync-msg-${idx}`).text(res.message || 'Error tidak diketahui');
                }
            } catch (err) {
                failed++;
                $(`#sync-icon-${idx}`).html('<i class="bi bi-x-circle-fill text-danger" style="font-size: 1.5rem;"></i>');
                $(`#sync-status-${idx}`).text('Error').removeClass('text-primary').addClass('text-danger');
                $(`#sync-bar-${idx}`).removeClass('progress-bar-striped progress-bar-animated bg-primary').addClass('bg-danger').css('width', '100%');
                $(`#sync-msg-${idx}`).text('Koneksi Gagal');
            }

            $(`#sync-item-${idx}`).removeClass('bg-soft-primary');
        }

        await new Promise(r => setTimeout(r, 800)); // Short pause to let user see final state

        $('#modalSyncProgress').modal('hide');
        Swal.fire({
            title: 'Download Selesai',
            text: `${success} record sukses, ${failed} gagal`,
            icon: (failed > 0 && success === 0) ? 'error' : 'success'
        }).then(() => location.reload());
    }

    function handleSyncSuccess(res) {
        $('#modalSyncProgress').modal('hide');
        if (res.status === 'success') {
            Swal.fire({ title: 'Berhasil', text: res.message, icon: 'success' }).then(() => location.reload());
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    }

    function refreshLog() {
        $('#syncLogTable tbody').html('<tr><td colspan="3" class="text-center"><div class="spinner-border spinner-border-sm"></div></td></tr>');
        location.reload();
    }

    function refreshStatus() { location.reload(); }

    function showErrorDetail(msg) {
        $('#errorDetailContent').text(typeof msg === 'object' ? JSON.stringify(msg, null, 2) : msg);
        $('#modalErrorDetail').modal('show');
    }

    function registerSchool() {
        let formData = $('#formAddSchool').serialize();
        $.ajax({
            url: BASE_URL + 'sync/register_school',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    $('#modalAddSchool').modal('hide');
                    $('#apiSchoolUuid').val(res.data.school_uuid);
                    $('#apiKeyDisplay').val(res.data.api_key);
                    $('#apiSecretDisplay').val(res.data.api_secret);
                    $('#modalApiKeys').modal('show');
                    $('#formAddSchool')[0].reset();
                } else Swal.fire('Error', res.message, 'error');
            }
        });
    }

    function copyToClipboard(elementId) {
        let element = document.getElementById(elementId);
        element.select();
        document.execCommand('copy');
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Disalin', timer: 1500, showConfirmButton: false });
    }


    function resetSync() {
        const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
        const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

        Swal.fire({
            title: 'Reset Sinkronisasi?',
            text: "Ini akan menghapus riwayat waktu sinkronisasi, sehingga semua data akan didownload ulang. Proses ini mungkin memakan waktu.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + 'sync/reset_sync',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        [csrfName]: csrfHash
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire('Error', 'Gagal menghubungi server (' + xhr.status + ')', 'error');
                    }
                });
            }
        });
    }

    function undoSync() {
        if (typeof BASE_URL === 'undefined') {
            alert("System Error: BASE_URL undefined");
            return;
        }

        const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
        const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

        // Step 1: Check stats
        $.ajax({
            url: BASE_URL + 'sync/get_recent_sync_stats',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    if (res.total === 0) {
                        Swal.fire('Info', 'Tidak ada data sinkronisasi baru dalam 30 menit terakhir.', 'info');
                        return;
                    }

                    let msg = `Ditemukan <b>${res.total}</b> record baru yang masuk dalam 30 menit terakhir.<br><br>`;
                    msg += `<div class="text-left small mb-3" style="max-height:150px;overflow-y:auto;">`;
                    for (let t in res.data) {
                        msg += `- ${t}: ${res.data[t]} record<br>`;
                    }
                    msg += `</div>`;
                    msg += `Apakah Anda yakin ingin MENGHAPUS data ini? Gunakan fitur ini jika terjadi Duplikasi Data setelah sinkronisasi.`;

                    Swal.fire({
                        title: 'Validasi Undo Duplikasi',
                        html: msg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Ya, Hapus Data Ini!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Step 2: Execute
                            $.ajax({
                                url: BASE_URL + 'sync/undo_sync',
                                type: 'POST',
                                data: { [csrfName]: csrfHash },
                                dataType: 'json',
                                success: function (delRes) {
                                    if (delRes.status === 'success') {
                                        Swal.fire('Selesai', delRes.message, 'success').then(() => location.reload());
                                    } else {
                                        Swal.fire('Error', delRes.message, 'error');
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire('Error', 'Gagal saat menghapus data (' + xhr.status + ')', 'error');
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire('Error', 'Gagal mengambil data stats: ' + (res.message || 'Unknown'), 'error');
                }
            },
            error: function (xhr, status, error) {
                let errMsg = 'Gagal request stats (' + xhr.status + '). ';
                if (status === 'parsererror') {
                    errMsg += 'Respon bukan JSON: ' + xhr.responseText.substring(0, 50) + '...';
                }
                Swal.fire('Error', errMsg, 'error');
            }
        });
    }

    $(document).ready(function () {
        $(document).on('click', '[data-dismiss="modal"]', function (e) {
            e.preventDefault();
            $(this).closest('.modal').modal('hide');
        });
    });
</script>