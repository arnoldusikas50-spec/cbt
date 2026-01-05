<?php
/**
 * View Status Siswa Belum Ujian
 * for Panitia - Enhanced Version
 */
?>
<style>
    .stat-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-card .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
    }

    .urgency-critical {
        background-color: #fee2e2 !important;
    }

    .urgency-warning {
        background-color: #fef3c7 !important;
    }

    .urgency-normal {
        background-color: transparent;
    }

    .auto-refresh-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.5;
            transform: scale(1.2);
        }
    }

    .progress-ring {
        width: 120px;
        height: 120px;
    }
</style>

<main class="app-main bg-light">
    <!-- Page Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i><?= $judul ?>
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>"><i
                                    class="bi bi-house-door-fill"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
                        <li class="breadcrumb-item active"><?= $judul ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Statistics Cards -->
            <div class="row mb-4" id="stats-container">
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Total Peserta</p>
                                    <h3 class="stat-number mb-0 text-dark" id="stat-total">-</h3>
                                </div>
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Belum Mulai</p>
                                    <h3 class="stat-number mb-0 text-danger" id="stat-belum">-</h3>
                                </div>
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-hourglass-top"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Sedang Mengerjakan</p>
                                    <h3 class="stat-number mb-0 text-warning" id="stat-sedang">-</h3>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="card stat-card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Selesai</p>
                                    <h3 class="stat-number mb-0 text-success" id="stat-selesai">-</h3>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold">Progress Ujian</span>
                        <span class="badge bg-primary" id="progress-percent">0%</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar bg-success" id="progress-bar" role="progressbar" style="width: 0%">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title fw-bold mb-1">
                                <i class="bi bi-list-check text-primary me-2"></i>Daftar Siswa Belum Ujian
                            </h5>
                            <div class="d-flex align-items-center gap-3">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>Update terakhir: <span id="last-update">-</span>
                                </small>
                                <div class="auto-refresh-indicator bg-success bg-opacity-10 text-success"
                                    id="auto-refresh-status">
                                    <span class="pulse-dot"></span>
                                    <span>Auto-refresh aktif</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" id="btn-toggle-refresh" class="btn btn-outline-secondary btn-sm"
                                title="Toggle Auto-refresh">
                                <i class="bi bi-pause-circle me-1"></i> <span id="toggle-text">Pause</span>
                            </button>
                            <button type="button" id="btn-refresh" class="btn btn-outline-primary btn-sm"
                                title="Refresh Manual">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-success btn-sm" id="btn-export-excel"
                                    title="Export Excel">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="btn-print" title="Cetak">
                                    <i class="bi bi-printer me-1"></i> Cetak
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body border-bottom bg-light">
                    <form id="filter-form" class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label for="filter_kelas" class="form-label fw-semibold small text-muted">
                                <i class="bi bi-building me-1"></i>Filter Kelas
                            </label>
                            <select id="filter_kelas" class="form-select select2">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($kelas as $id_kelas => $nama_kelas): ?>
                                    <option value="<?= $id_kelas ?>">
                                        <?= $nama_kelas ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label for="filter_mapel" class="form-label fw-semibold small text-muted">
                                <i class="bi bi-book me-1"></i>Filter Mata Pelajaran
                            </label>
                            <select id="filter_mapel" class="form-select select2">
                                <option value="">Semua Mapel</option>
                                <?php foreach ($mapel as $id_mapel => $nama_mapel): ?>
                                    <option value="<?= $id_mapel ?>">
                                        <?= $nama_mapel ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label for="filter_ruang" class="form-label fw-semibold small text-muted">
                                <i class="bi bi-door-open me-1"></i>Filter Ruang
                            </label>
                            <select id="filter_ruang" class="form-select select2">
                                <option value="">Semua Ruang</option>
                                <?php foreach ($ruang as $id_ruang => $nama_ruang): ?>
                                    <option value="<?= $id_ruang ?>">
                                        <?= $nama_ruang ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button type="button" id="btn-filter" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="table-belum-ujian" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center">Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th class="text-center">Sesi</th>
                                    <th class="text-center">Ruang</th>
                                    <th class="text-center">Sisa Waktu</th>
                                    <th>Pengawas</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle text-info me-2"></i>
                            <small class="text-muted">Data diperbarui otomatis setiap 30 detik. Klik "Pause" untuk
                                menghentikan.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-danger">Kritis (&lt;30 menit)</span>
                            <span class="badge bg-warning text-dark">Perhatian (&lt;60 menit)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function () {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        let autoRefreshInterval = null;
        let isAutoRefreshActive = true;
        const REFRESH_INTERVAL = 30000; // 30 seconds

        const table = $('#table-belum-ujian').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: base_url + 'cbtstatus/get_siswa_belum_ujian',
                type: 'POST',
                data: function (d) {
                    d.kelas = $('#filter_kelas').val();
                    d.mapel = $('#filter_mapel').val();
                    d.ruang = $('#filter_ruang').val();
                    d['<?= $this->security->get_csrf_token_name() ?>'] = '<?= $this->security->get_csrf_hash() ?>';
                },
                dataSrc: function (json) {
                    // Update statistics
                    if (json.stats) {
                        updateStats(json.stats);
                    }
                    if (json.last_update) {
                        $('#last-update').text(json.last_update);
                    }
                    return json.data || [];
                }
            },
            columns: [
                {
                    data: null,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'nis',
                    className: 'font-monospace small'
                },
                {
                    data: 'nama_siswa',
                    className: 'fw-bold text-dark'
                },
                {
                    data: 'kelas',
                    className: 'text-center',
                    render: function (data) {
                        return '<span class="badge bg-info text-dark">' + data + '</span>';
                    }
                },
                {
                    data: 'mapel',
                    render: function (data) {
                        return '<span class="fw-medium text-primary">' + data + '</span>';
                    }
                },
                {
                    data: 'sesi',
                    className: 'text-center',
                    render: function (data) {
                        if (data === '-' || !data) return '<span class="text-muted">-</span>';
                        return '<span class="badge bg-secondary">' + data + '</span>';
                    }
                },
                {
                    data: 'ruang',
                    className: 'text-center',
                    render: function (data) {
                        if (data === '-' || !data) return '<span class="text-muted">-</span>';
                        return '<span class="badge bg-light text-dark border">' + data + '</span>';
                    }
                },
                {
                    data: 'time_remaining',
                    className: 'text-center',
                    render: function (data, type, row) {
                        if (!data && data !== 0) return '-';
                        let hours = Math.floor(data / 60);
                        let mins = data % 60;
                        let timeStr = hours > 0 ? hours + 'j ' + mins + 'm' : mins + ' menit';

                        let badgeClass = 'bg-light text-dark';
                        if (row.urgency === 'critical') {
                            badgeClass = 'bg-danger text-white';
                        } else if (row.urgency === 'warning') {
                            badgeClass = 'bg-warning text-dark';
                        }
                        return '<span class="badge ' + badgeClass + '">' + timeStr + '</span>';
                    }
                },
                {
                    data: 'pengawas',
                    render: function (data) {
                        if (data === '-' || !data) return '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Belum ada</span>';
                        return '<i class="bi bi-person-check-fill me-1 text-success"></i>' + data;
                    }
                }
            ],
            createdRow: function (row, data, dataIndex) {
                if (data.urgency === 'critical') {
                    $(row).addClass('urgency-critical');
                } else if (data.urgency === 'warning') {
                    $(row).addClass('urgency-warning');
                }
            },
            order: [[7, 'asc']], // Sort by time remaining ascending
            language: {
                "lengthMenu": "Tampilkan _MENU_ entri",
                "zeroRecords": "<div class='text-center py-4'><i class='bi bi-check-circle-fill text-success fs-1'></i><br><span class='text-muted'>Semua siswa sudah memulai ujian! 🎉</span></div>",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa belum ujian",
                "infoEmpty": "Tidak ada siswa yang belum ujian",
                "infoFiltered": "(dari total _MAX_ entri)",
                "search": "<i class='bi bi-search'></i>",
                "searchPlaceholder": "Cari siswa...",
                "paginate": {
                    "first": "<i class='bi bi-chevron-double-left'></i>",
                    "last": "<i class='bi bi-chevron-double-right'></i>",
                    "next": "<i class='bi bi-chevron-right'></i>",
                    "previous": "<i class='bi bi-chevron-left'></i>"
                },
                "processing": "<div class='spinner-border spinner-border-sm text-primary' role='status'></div> Memuat data..."
            },
            dom: '<"row px-3 pt-3"<"col-md-6"l><"col-md-6"f>>rt<"row px-3 pb-3"<"col-md-6"i><"col-md-6"p>>',
        });

        function updateStats(stats) {
            $('#stat-total').text(stats.total_siswa || 0);
            $('#stat-belum').text(stats.belum_mulai || 0);
            $('#stat-sedang').text(stats.sedang_mengerjakan || 0);
            $('#stat-selesai').text(stats.selesai || 0);

            // Update progress bar
            const total = stats.total_siswa || 0;
            const selesai = stats.selesai || 0;
            const percent = total > 0 ? Math.round((selesai / total) * 100) : 0;
            $('#progress-bar').css('width', percent + '%');
            $('#progress-percent').text(percent + '%');
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = setInterval(function () {
                table.ajax.reload(null, false);
            }, REFRESH_INTERVAL);
            isAutoRefreshActive = true;
            $('#auto-refresh-status').removeClass('bg-secondary bg-opacity-10 text-secondary').addClass('bg-success bg-opacity-10 text-success');
            $('#auto-refresh-status .pulse-dot').css('background-color', '#10b981');
            $('#auto-refresh-status span:last-child').text('Auto-refresh aktif');
            $('#toggle-text').text('Pause');
            $('#btn-toggle-refresh i').removeClass('bi-play-circle').addClass('bi-pause-circle');
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
            isAutoRefreshActive = false;
            $('#auto-refresh-status').removeClass('bg-success bg-opacity-10 text-success').addClass('bg-secondary bg-opacity-10 text-secondary');
            $('#auto-refresh-status .pulse-dot').css('background-color', '#6b7280');
            $('#auto-refresh-status span:last-child').text('Auto-refresh dijeda');
            $('#toggle-text').text('Resume');
            $('#btn-toggle-refresh i').removeClass('bi-pause-circle').addClass('bi-play-circle');
        }

        // Start auto-refresh on load
        startAutoRefresh();

        // Toggle auto-refresh
        $('#btn-toggle-refresh').on('click', function () {
            if (isAutoRefreshActive) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });

        // Manual refresh
        $('#btn-filter, #btn-refresh').on('click', function () {
            table.ajax.reload();
        });

        // Export Excel
        $('#btn-export-excel').on('click', function () {
            let data = table.rows().data().toArray();
            if (data.length === 0) {
                alert('Tidak ada data untuk diexport');
                return;
            }

            let csv = 'No,NIS,Nama Siswa,Kelas,Mata Pelajaran,Sesi,Ruang,Sisa Waktu (menit),Pengawas\n';
            data.forEach((row, idx) => {
                csv += `${idx + 1},"${row.nis}","${row.nama_siswa}","${row.kelas}","${row.mapel}","${row.sesi}","${row.ruang}",${row.time_remaining},"${row.pengawas}"\n`;
            });

            let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'siswa_belum_ujian_' + new Date().toISOString().slice(0, 10) + '.csv';
            link.click();
        });

        // Print
        $('#btn-print').on('click', function () {
            let data = table.rows().data().toArray();
            if (data.length === 0) {
                alert('Tidak ada data untuk dicetak');
                return;
            }

            let printContent = `
                <html>
                <head>
                    <title>Daftar Siswa Belum Ujian</title>
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 12px; }
                        h2 { text-align: center; margin-bottom: 5px; }
                        p { text-align: center; margin-top: 0; color: #666; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
                        th { background-color: #333; color: white; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        .critical { background-color: #fee2e2 !important; }
                        .warning { background-color: #fef3c7 !important; }
                        .footer { margin-top: 30px; font-size: 10px; color: #666; }
                    </style>
                </head>
                <body>
                    <h2>DAFTAR SISWA BELUM UJIAN</h2>
                    <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Sesi</th>
                                <th>Ruang</th>
                                <th>Sisa Waktu</th>
                                <th>Pengawas</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.forEach((row, idx) => {
                let rowClass = row.urgency === 'critical' ? 'critical' : (row.urgency === 'warning' ? 'warning' : '');
                let timeStr = row.time_remaining > 60 ? Math.floor(row.time_remaining / 60) + 'j ' + (row.time_remaining % 60) + 'm' : row.time_remaining + ' menit';
                printContent += `
                    <tr class="${rowClass}">
                        <td>${idx + 1}</td>
                        <td>${row.nis}</td>
                        <td>${row.nama_siswa}</td>
                        <td>${row.kelas}</td>
                        <td>${row.mapel}</td>
                        <td>${row.sesi}</td>
                        <td>${row.ruang}</td>
                        <td>${timeStr}</td>
                        <td>${row.pengawas}</td>
                    </tr>
                `;
            });

            printContent += `
                        </tbody>
                    </table>
                    <div class="footer">
                        <p>Total siswa belum ujian: ${data.length}</p>
                    </div>
                </body>
                </html>
            `;

            let printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        });
    });
</script>