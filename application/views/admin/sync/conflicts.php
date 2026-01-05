<style>
    .conflict-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white;
    }

    .conflict-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .conflict-item {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border-left: 4px solid #ffc107;
    }

    .conflict-item.resolved {
        border-left-color: #28a745;
        opacity: 0.7;
    }

    .data-preview {
        background: #1e1e1e;
        color: #d4d4d4;
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 0.75rem;
        max-height: 200px;
        overflow-y: auto;
    }

    .data-preview .key {
        color: #9cdcfe;
    }

    .data-preview .value {
        color: #ce9178;
    }

    .strategy-btn {
        border-radius: 50px;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        transition: all 0.2s;
    }

    .strategy-btn:hover {
        transform: translateY(-2px);
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
    }

    .timestamp {
        font-size: 0.75rem;
        color: #6c757d;
    }
</style>

<main class="app-main pt-4 bg-light">
    <!-- Header -->
    <div class="content-header mb-4 illustration-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="m-0 font-weight-bold text-dark mb-1">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>Resolusi Konflik
                    </h4>
                    <small class="text-muted">Kelola data yang berkonflik antara server lokal dan pusat</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="stat-badge bg-warning text-dark">
                        <i class="bi bi-clock me-1"></i>Pending:
                        <?= $conflict_stats['pending'] ?>
                    </span>
                    <span class="stat-badge bg-success text-white">
                        <i class="bi bi-check-circle me-1"></i>Resolved:
                        <?= $conflict_stats['resolved'] ?>
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

            <?php if (empty($conflicts)): ?>
                <!-- No Conflicts -->
                <div class="card conflict-card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-4 font-weight-bold text-success">Tidak Ada Konflik</h4>
                        <p class="text-muted mb-0">Semua data tersinkronisasi dengan baik antara server lokal dan pusat.</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Bulk Actions -->
                <div class="card conflict-card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h6 class="font-weight-bold mb-1">
                                    <i class="bi bi-lightning-charge text-warning me-2"></i>Aksi Massal
                                </h6>
                                <p class="text-muted small mb-0">Selesaikan semua konflik dengan satu strategi</p>
                            </div>
                            <div class="btn-group">
                                <button class="strategy-btn btn btn-outline-primary" onclick="resolveAll('server_wins')">
                                    <i class="bi bi-cloud me-1"></i> Server Menang
                                </button>
                                <button class="strategy-btn btn btn-outline-success" onclick="resolveAll('local_wins')">
                                    <i class="bi bi-hdd me-1"></i> Lokal Menang
                                </button>
                                <button class="strategy-btn btn btn-outline-info" onclick="resolveAll('newest_wins')">
                                    <i class="bi bi-clock-history me-1"></i> Terbaru Menang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conflict List -->
                <div class="card conflict-card shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold mb-0">
                            <i class="bi bi-list-ul text-primary me-2"></i>Daftar Konflik
                        </h5>
                        <span class="badge bg-warning text-dark">
                            <?= count($conflicts) ?> konflik
                        </span>
                    </div>
                    <div class="card-body">
                        <?php foreach ($conflicts as $conflict): ?>
                            <?php
                            $local_data = json_decode($conflict->local_data, true);
                            $remote_data = json_decode($conflict->remote_data, true);
                            ?>
                            <div class="conflict-item" id="conflict-<?= $conflict->id ?>">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="font-weight-bold mb-1">
                                            <span class="badge bg-primary me-2">
                                                <?= $conflict->table_name ?>
                                            </span>
                                            <small class="text-muted">UUID:
                                                <?= substr($conflict->sync_uuid, 0, 8) ?>...
                                            </small>
                                        </h6>
                                        <div class="timestamp">
                                            <i class="bi bi-clock me-1"></i>Terdeteksi:
                                            <?= $conflict->created_at ?>
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success" onclick="resolveConflict(<?= $conflict->id ?>, 'local')"
                                            title="Pilih Data Lokal">
                                            <i class="bi bi-hdd me-1"></i> Lokal
                                        </button>
                                        <button class="btn btn-primary"
                                            onclick="resolveConflict(<?= $conflict->id ?>, 'remote')" title="Pilih Data Server">
                                            <i class="bi bi-cloud me-1"></i> Server
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-success me-2">DATA LOKAL</span>
                                            <span class="timestamp">
                                                <?= $conflict->local_updated ?>
                                            </span>
                                        </div>
                                        <div class="data-preview">
                                            <?php if ($local_data): ?>
                                                <?php foreach (array_slice($local_data, 0, 8) as $key => $val): ?>
                                                    <div><span class="key">
                                                            <?= $key ?>:
                                                        </span> <span class="value">
                                                            <?= is_array($val) ? json_encode($val) : htmlspecialchars(substr((string) $val, 0, 50)) ?>
                                                        </span></div>
                                                <?php endforeach; ?>
                                                <?php if (count($local_data) > 8): ?>
                                                    <div class="text-muted">... +
                                                        <?= count($local_data) - 8 ?> fields
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-primary me-2">DATA SERVER</span>
                                            <span class="timestamp">
                                                <?= $conflict->remote_updated ?>
                                            </span>
                                        </div>
                                        <div class="data-preview">
                                            <?php if ($remote_data): ?>
                                                <?php foreach (array_slice($remote_data, 0, 8) as $key => $val): ?>
                                                    <div><span class="key">
                                                            <?= $key ?>:
                                                        </span> <span class="value">
                                                            <?= is_array($val) ? json_encode($val) : htmlspecialchars(substr((string) $val, 0, 50)) ?>
                                                        </span></div>
                                                <?php endforeach; ?>
                                                <?php if (count($remote_data) > 8): ?>
                                                    <div class="text-muted">... +
                                                        <?= count($remote_data) - 8 ?> fields
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<script>
    const BASE_URL = '<?= base_url() ?>';
    const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
    const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

    function resolveConflict(id, choice) {
        Swal.fire({
            title: 'Konfirmasi',
            text: `Anda akan menggunakan data ${choice === 'local' ? 'LOKAL' : 'SERVER'}. Lanjutkan?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Pilih!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + 'sync/resolve_conflict',
                    type: 'POST',
                    data: {
                        conflict_id: id,
                        choice: choice,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            $('#conflict-' + id).addClass('resolved').fadeOut(500, function () {
                                $(this).remove();
                                updateCount();
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal menghubungi server', 'error');
                    }
                });
            }
        });
    }

    function resolveAll(strategy) {
        let strategyLabel = {
            'server_wins': 'Data Server akan menang',
            'local_wins': 'Data Lokal akan menang',
            'newest_wins': 'Data terbaru akan menang'
        };

        Swal.fire({
            title: 'Selesaikan Semua Konflik?',
            html: `<p>Strategi: <strong>${strategyLabel[strategy]}</strong></p><p class="text-muted small">Semua konflik akan diselesaikan dengan strategi ini.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: BASE_URL + 'sync/resolve_all_conflicts',
                    type: 'POST',
                    data: {
                        strategy: strategy,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Selesai!',
                                text: res.message
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal menghubungi server', 'error');
                    }
                });
            }
        });
    }

    function updateCount() {
        let remaining = $('.conflict-item:visible').length;
        if (remaining === 0) {
            location.reload();
        }
    }
</script>