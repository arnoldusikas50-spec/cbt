<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header mb-3">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient text-white rounded-3 p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cloud-arrow-down fs-3"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-dark"><?= esc($title ?? 'Integrasi Web Service Dapodik') ?></h3>
                        <p class="text-muted small mb-0">Penyelarasan Data Sekolah, GTK, Siswa, Rombel, Mapel, Pembelajaran & Ruang dari Dapodik Lokal</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-5">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-house-door me-1"></i>Dashboard</a>
                    </li>
                    <li class="breadcrumb-item text-muted">Integrasi</li>
                    <li class="breadcrumb-item active fw-semibold text-primary">Dapodik</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                    <div><?= session()->getFlashdata('success') ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                    <div><?= session()->getFlashdata('error') ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Overall Status & Connection Banner Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-body p-3.5">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8 col-12">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 d-none d-sm-flex align-items-center justify-content-center" style="width: 52px; height: 52px; min-width: 52px;">
                                <i class="bi bi-router-fill fs-2"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <span class="fw-bold text-dark fs-6">Status Web Service Dapodik:</span>
                                    <span id="connectionStatusBadge" class="badge bg-secondary rounded-pill px-3 py-1.5 fw-semibold">
                                        <i class="bi bi-dash-circle me-1"></i>Belum Dites
                                    </span>
                                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 fw-normal">
                                        <i class="bi bi-clock-history me-1 text-primary"></i>Sync Terakhir: 
                                        <strong><?= !empty($settings['last_sync_at']) ? date('d M Y H:i', strtotime($settings['last_sync_at'])) : 'Belum Pernah' ?></strong>
                                    </span>
                                </div>
                                <div class="small text-muted d-flex align-items-center gap-3 flex-wrap">
                                    <span><i class="bi bi-globe me-1 text-primary"></i>Server: <code class="bg-light px-2 py-0.5 rounded text-primary border"><?= esc($settings['server_url'] ?? 'http://localhost:5774') ?></code></span>
                                    <span><i class="bi bi-building me-1 text-primary"></i>NPSN: <code class="bg-light px-2 py-0.5 rounded text-dark border"><?= esc($settings['npsn'] ?? 'N/A') ?></code></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12 text-lg-end">
                        <button type="button" class="btn btn-primary bg-gradient rounded-3 px-4 py-2 fw-bold shadow-sm" id="btnTestConnection">
                            <i class="bi bi-plug-fill me-1.5"></i> Tes Koneksi API
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stat Overview Cards Row (4 Columns) -->
        <div class="row g-3 mb-4">
            <!-- Guru Card -->
            <div class="col-xl-3 col-md-6 col-12">
                <div class="card border-0 shadow-sm bg-white p-3 h-100 rounded-3 border-start border-4 border-primary hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-person-badge fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-uppercase text-muted fw-bold smaller d-block mb-1">Guru / GTK</span>
                            <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['guru_dapo']) ?> <small class="text-muted fs-6">/ <?= number_format($stats['total_guru']) ?></small></h4>
                            <?php $percGuru = $stats['total_guru'] > 0 ? round(($stats['guru_dapo'] / $stats['total_guru']) * 100, 1) : 0; ?>
                            <div class="progress mt-2 rounded-pill" style="height: 5px;">
                                <div class="progress-bar bg-primary rounded-pill" style="width: <?= $percGuru ?>%"></div>
                            </div>
                            <span class="text-primary fw-bold smaller mt-1 d-block"><i class="bi bi-check-circle-fill me-1"></i><?= $percGuru ?>% Terhubung</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Siswa Card -->
            <div class="col-xl-3 col-md-6 col-12">
                <div class="card border-0 shadow-sm bg-white p-3 h-100 rounded-3 border-start border-4 border-success hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-people-fill fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-uppercase text-muted fw-bold smaller d-block mb-1">Peserta Didik (Siswa)</span>
                            <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['siswa_dapo']) ?> <small class="text-muted fs-6">/ <?= number_format($stats['total_siswa']) ?></small></h4>
                            <?php $percSiswa = $stats['total_siswa'] > 0 ? round(($stats['siswa_dapo'] / $stats['total_siswa']) * 100, 1) : 0; ?>
                            <div class="progress mt-2 rounded-pill" style="height: 5px;">
                                <div class="progress-bar bg-success rounded-pill" style="width: <?= $percSiswa ?>%"></div>
                            </div>
                            <span class="text-success fw-bold smaller mt-1 d-block"><i class="bi bi-check-circle-fill me-1"></i><?= $percSiswa ?>% Terhubung</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kelas Card -->
            <div class="col-xl-3 col-md-6 col-12">
                <div class="card border-0 shadow-sm bg-white p-3 h-100 rounded-3 border-start border-4 border-info hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-building fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-uppercase text-muted fw-bold smaller d-block mb-1">Rombel / Kelas</span>
                            <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['kelas_dapo']) ?> <small class="text-muted fs-6">/ <?= number_format($stats['total_kelas']) ?></small></h4>
                            <?php $percKelas = $stats['total_kelas'] > 0 ? round(($stats['kelas_dapo'] / $stats['total_kelas']) * 100, 1) : 0; ?>
                            <div class="progress mt-2 rounded-pill" style="height: 5px;">
                                <div class="progress-bar bg-info rounded-pill" style="width: <?= $percKelas ?>%"></div>
                            </div>
                            <span class="text-info fw-bold smaller mt-1 d-block"><i class="bi bi-check-circle-fill me-1"></i><?= $percKelas ?>% Terhubung</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapel Card -->
            <div class="col-xl-3 col-md-6 col-12">
                <div class="card border-0 shadow-sm bg-white p-3 h-100 rounded-3 border-start border-4 border-warning hover-lift">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-book fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <span class="text-uppercase text-muted fw-bold smaller d-block mb-1">Mata Pelajaran</span>
                            <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['mapel_dapo']) ?> <small class="text-muted fs-6">/ <?= number_format($stats['total_mapel']) ?></small></h4>
                            <?php $percMapel = $stats['total_mapel'] > 0 ? round(($stats['mapel_dapo'] / $stats['total_mapel']) * 100, 1) : 0; ?>
                            <div class="progress mt-2 rounded-pill" style="height: 5px;">
                                <div class="progress-bar bg-warning rounded-pill" style="width: <?= $percMapel ?>%"></div>
                            </div>
                            <span class="text-warning fw-bold smaller mt-1 d-block"><i class="bi bi-check-circle-fill me-1"></i><?= $percMapel ?>% Terhubung</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card with Navigation Tabs -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom p-2">
                <ul class="nav nav-pills custom-pills-bar gap-1" id="dapodikTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold px-3 py-2" id="live-tab" data-bs-toggle="tab" data-bs-target="#live-pane" type="button" role="tab">
                            <i class="bi bi-arrow-repeat me-1.5"></i> Live Direct Sync (API)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold px-3 py-2" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button" role="tab">
                            <i class="bi bi-gear-fill me-1.5"></i> Pengaturan Web Service
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold px-3 py-2" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" type="button" role="tab">
                            <i class="bi bi-clock-history me-1.5"></i> Riwayat & Log
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="dapodikTabContent">

                    <!-- TAB 1: LIVE DIRECT SYNC (API) -->
                    <div class="tab-pane fade show active" id="live-pane" role="tabpanel">
                        <div class="mb-4">
                            <h5 class="fw-bold text-dark mb-1">
                                <i class="bi bi-arrow-repeat text-primary me-2"></i>Sinkronisasi Langsung via Web Service API
                            </h5>
                            <p class="text-muted small mb-0">Pilih entitas data yang ingin ditarik secara real-time dari Web Service Dapodik lokal ke database sekolah.</p>
                        </div>

                        <!-- Entity Sync Cards Grid (3 Columns x 2 Rows) -->
                        <div class="row g-3 mb-4">
                            <!-- 1. GURU -->
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card border border-light-subtle shadow-xs h-100 p-3.5 rounded-3 sync-card hover-lift bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                            <i class="bi bi-person-badge fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-bold mb-0 text-dark">Guru / GTK</h6>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 smaller">
                                                    <?= number_format($stats['guru_dapo']) ?> / <?= number_format($stats['total_guru']) ?>
                                                </span>
                                            </div>
                                            <p class="text-muted smaller mb-0 mt-1">NUPTK, NIP, NIK, biodata, & kepegawaian.</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-soft-primary btn-sm fw-semibold w-100 rounded-3 py-2 btn-sync mt-auto" data-entitas="gtk">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync Guru
                                    </button>
                                </div>
                            </div>

                            <!-- 2. SISWA -->
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card border border-light-subtle shadow-xs h-100 p-3.5 rounded-3 sync-card hover-lift bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                            <i class="bi bi-people-fill fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-bold mb-0 text-dark">Peserta Didik (Siswa)</h6>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 smaller">
                                                    <?= number_format($stats['siswa_dapo']) ?> / <?= number_format($stats['total_siswa']) ?>
                                                </span>
                                            </div>
                                            <p class="text-muted smaller mb-0 mt-1">NISN, NIS, NIK, biodata ortu & alamat.</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-soft-success btn-sm fw-semibold w-100 rounded-3 py-2 btn-sync mt-auto" data-entitas="siswa">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync Siswa
                                    </button>
                                </div>
                            </div>

                            <!-- 3. ROMBEL / KELAS -->
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card border border-light-subtle shadow-xs h-100 p-3.5 rounded-3 sync-card hover-lift bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 bg-info bg-opacity-10 text-info p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                            <i class="bi bi-building fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-bold mb-0 text-dark">Rombel / Kelas</h6>
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1 smaller">
                                                    <?= number_format($stats['kelas_dapo']) ?> / <?= number_format($stats['total_kelas']) ?>
                                                </span>
                                            </div>
                                            <p class="text-muted smaller mb-0 mt-1">Nama kelas, tingkat, & wali kelas.</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-soft-info btn-sm fw-semibold w-100 rounded-3 py-2 btn-sync mt-auto" data-entitas="rombel">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync Rombel
                                    </button>
                                </div>
                            </div>

                            <!-- 4. MAPEL -->
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card border border-light-subtle shadow-xs h-100 p-3.5 rounded-3 sync-card hover-lift bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                            <i class="bi bi-book fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-bold mb-0 text-dark">Mata Pelajaran</h6>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1 smaller">
                                                    <?= number_format($stats['mapel_dapo']) ?> / <?= number_format($stats['total_mapel']) ?>
                                                </span>
                                            </div>
                                            <p class="text-muted smaller mb-0 mt-1">Direktori mapel & kelompok mapel.</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-soft-warning btn-sm fw-semibold w-100 rounded-3 py-2 btn-sync mt-auto" data-entitas="mapel">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync Mapel
                                    </button>
                                </div>
                            </div>

                            <!-- 5. PEMBELAJARAN -->
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card border border-light-subtle shadow-xs h-100 p-3.5 rounded-3 sync-card hover-lift bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 bg-secondary bg-opacity-10 text-secondary p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                            <i class="bi bi-journal-bookmark-fill fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-bold mb-0 text-dark">Pembelajaran</h6>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-1 smaller">Jadwal</span>
                                            </div>
                                            <p class="text-muted smaller mb-0 mt-1">Penugasan mapel & jadwal mengajar guru.</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-soft-secondary btn-sm fw-semibold w-100 rounded-3 py-2 btn-sync mt-auto" data-entitas="pembelajaran">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync Pembelajaran
                                    </button>
                                </div>
                            </div>

                            <!-- 6. RUANG -->
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="card border border-light-subtle shadow-xs h-100 p-3.5 rounded-3 sync-card hover-lift bg-white">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                            <i class="bi bi-door-open-fill fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-bold mb-0 text-dark">Ruang / Sarpras</h6>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 smaller">Sarpras</span>
                                            </div>
                                            <p class="text-muted smaller mb-0 mt-1">Gedung, ruang kelas, & lab CBT.</p>
                                        </div>
                                    </div>
                                    <button class="btn btn-soft-danger btn-sm fw-semibold w-100 rounded-3 py-2 btn-sync mt-auto" data-entitas="ruang">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync Ruang
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sync All Banner Box -->
                        <div class="p-4 rounded-3 text-center border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-left: 4px solid #f59e0b !important;">
                            <div class="text-white py-1">
                                <h5 class="fw-bold mb-1.5 text-white"><i class="bi bi-rocket-takeoff-fill text-warning me-2 fs-4"></i>Sinkronisasi Seluruh Data Sekaligus</h5>
                                <p class="text-white-50 small mb-3 mx-auto" style="max-width: 650px;">
                                    Tarik dan perbarui secara berurutan data Sekolah, GTK, Siswa, Rombel, Mapel, Pembelajaran & Ruang dalam 1 proses otomatis.
                                </p>
                                <button type="button" class="btn btn-warning bg-gradient px-4 py-2.5 fw-bold rounded-3 shadow-sm text-dark btn-sync" data-entitas="all">
                                    <i class="bi bi-rocket-takeoff-fill me-1.5"></i> Jalankan SINKRONISASI LENGKAP (SYNC ALL)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: PENGATURAN API -->
                    <div class="tab-pane fade" id="settings-pane" role="tabpanel">
                        <form action="<?= base_url('integrasi/dapodik/saveSettings') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="row g-4 mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">URL Server Web Service Dapodik <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-globe text-primary"></i></span>
                                            <input type="text" name="server_url" class="form-control" value="<?= esc($settings['server_url'] ?? 'http://localhost:5774') ?>" placeholder="http://localhost:5774" required>
                                        </div>
                                        <small class="text-muted d-block mt-1">Default Web Service Dapodik port <code>5774</code> (HTTP) atau URL reverse proxy server sekolah.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">NPSN Sekolah <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-building text-primary"></i></span>
                                            <input type="text" name="npsn" class="form-control" value="<?= esc($settings['npsn'] ?? '') ?>" placeholder="NPSN Sekolah" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark">Token Rahasia Web Service (Bearer Token) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill text-primary"></i></span>
                                            <input type="password" id="inputSecretToken" name="secret_token" class="form-control" value="<?= esc($settings['secret_token'] ?? '') ?>" placeholder="Masukkan token rahasia dari Dapodik" required>
                                            <button type="button" class="btn btn-outline-secondary bg-light" id="btnToggleToken" title="Tampilkan/Sembunyikan Token">
                                                <i class="bi bi-eye" id="iconToggleToken"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1">Token rahasia didapatkan dari menu <code>Pengaturan -> Web Service</code> pada aplikasi Dapodik.</small>
                                    </div>

                                    <div class="card bg-light border-0 rounded-3 p-3 mb-3">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveSwitch" <?= !empty($settings['is_active']) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold text-dark ms-1" for="isActiveSwitch">Aktifkan Integrasi Web Service Dapodik</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-top pt-3 text-end">
                                <button type="submit" class="btn btn-primary bg-gradient rounded-3 px-4 fw-bold shadow-sm">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 3: LOGS & RIWAYAT -->
                    <div class="tab-pane fade" id="logs-pane" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Aktivitas Sinkronisasi</h5>
                            <?php if (!empty($logs)): ?>
                                <a href="<?= base_url('integrasi/dapodik/clearLogs') ?>" class="btn btn-outline-danger btn-sm rounded-2 fw-bold" onclick="return confirm('Bersihkan seluruh riwayat log sinkronisasi?');">
                                    <i class="bi bi-trash me-1"></i> Bersihkan Log
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Entitas</th>
                                        <th>Sumber</th>
                                        <th>Status</th>
                                        <th class="text-center">Total Diproses</th>
                                        <th class="text-center">Baru</th>
                                        <th class="text-center">Diperbarui</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td class="small text-nowrap"><?= !empty($log['created_at']) ? date('d M Y H:i:s', strtotime($log['created_at'])) : '-' ?></td>
                                                <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-uppercase px-2.5 py-1"><?= esc($log['entitas'] ?? '-') ?></span></td>
                                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border text-uppercase px-2.5 py-1"><?= esc($log['metode'] ?? $log['sumber'] ?? 'api') ?></span></td>
                                                <td>
                                                    <?php if (($log['status'] ?? '') === 'sukses'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1"><i class="bi bi-check-circle me-1"></i>Sukses</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1"><i class="bi bi-x-circle me-1"></i>Gagal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center fw-bold"><?= number_format($log['total_diperoses'] ?? $log['total_proses'] ?? 0) ?></td>
                                                <td class="text-center text-success fw-bold">+<?= number_format($log['total_ditambah'] ?? $log['total_tambah'] ?? 0) ?></td>
                                                <td class="text-center text-info fw-bold"><?= number_format($log['total_diperbarui'] ?? $log['total_ubah'] ?? 0) ?></td>
                                                <td class="small text-muted"><?= esc($log['keterangan'] ?? $log['pesan'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                <i class="bi bi-inbox fs-1 d-block text-secondary opacity-50 mb-2"></i>
                                                Belum ada riwayat aktivitas sinkronisasi.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Web Cron Helper Card -->
        <?php $cronUrl = site_url('cron/dapodik_sync?key=' . (env('CRON_SECRET_KEY') ?? 'z7kQmP2xLvN8rT5wY6uH3aS5bD1cF9eJ0')); ?>
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body p-3.5">
                <div class="row align-items-center g-3">
                    <div class="col-lg-6 col-12">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                                <i class="bi bi-clock-history fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5">Web-Cron Auto Sync Dapodik (Server Background Job)</h6>
                                <p class="text-muted small mb-0">Atur skedul cron server Anda untuk menyinkronkan Dapodik secara berkala melalui HTTP Web-Cron.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <div class="input-group">
                            <input type="text" id="cronUrlInput" class="form-control form-control-sm font-monospace bg-light" value="<?= esc($cronUrl) ?>" readonly>
                            <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3" id="btnCopyCronUrl">
                                <i class="bi bi-clipboard me-1"></i> Salin URL
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.4rem 0.8rem rgba(0, 0, 0, 0.06) !important;
}
.custom-pills-bar .nav-link {
    color: #64748b;
    background-color: transparent;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}
.custom-pills-bar .nav-link:hover {
    color: #1e293b;
    background-color: #f8fafc;
}
.custom-pills-bar .nav-link.active {
    color: #2563eb !important;
    background-color: #eff6ff !important;
    border: 1px solid #bfdbfe;
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
}
.border-dashed {
    border-style: dashed !important;
}
/* Minimalist Soft Tint Buttons */
.btn-soft-primary { background-color: rgba(37, 99, 235, 0.08); color: #2563eb; border: 0; }
.btn-soft-primary:hover, .btn-soft-primary:focus { background-color: rgba(37, 99, 235, 0.18); color: #1d4ed8; }

.btn-soft-success { background-color: rgba(22, 163, 74, 0.08); color: #16a34a; border: 0; }
.btn-soft-success:hover, .btn-soft-success:focus { background-color: rgba(22, 163, 74, 0.18); color: #15803d; }

.btn-soft-info { background-color: rgba(8, 145, 178, 0.08); color: #0891b2; border: 0; }
.btn-soft-info:hover, .btn-soft-info:focus { background-color: rgba(8, 145, 178, 0.18); color: #0e7490; }

.btn-soft-warning { background-color: rgba(217, 119, 6, 0.08); color: #d97706; border: 0; }
.btn-soft-warning:hover, .btn-soft-warning:focus { background-color: rgba(217, 119, 6, 0.18); color: #b45309; }

.btn-soft-secondary { background-color: rgba(71, 85, 105, 0.08); color: #475569; border: 0; }
.btn-soft-secondary:hover, .btn-soft-secondary:focus { background-color: rgba(71, 85, 105, 0.18); color: #334155; }

.btn-soft-danger { background-color: rgba(220, 38, 38, 0.08); color: #dc2626; border: 0; }
.btn-soft-danger:hover, .btn-soft-danger:focus { background-color: rgba(220, 38, 38, 0.18); color: #b91c1c; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Copy Cron URL button
        $('#btnCopyCronUrl').on('click', function() {
            var cronUrl = $('#cronUrlInput').val();
            navigator.clipboard.writeText(cronUrl).then(function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'URL Web-Cron disalin!',
                    showConfirmButton: false,
                    timer: 2500
                });
            }).catch(function() {
                $('#cronUrlInput').select();
                document.execCommand('copy');
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'URL Web-Cron disalin!',
                    showConfirmButton: false,
                    timer: 2500
                });
            });
        });

        // Toggle secret token visibility
        $('#btnToggleToken').on('click', function() {
            var $input = $('#inputSecretToken');
            var $icon = $('#iconToggleToken');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // Test connection button
        $('#btnTestConnection').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menguji...');
            $('#connectionStatusBadge').removeClass('bg-success bg-danger').addClass('bg-secondary').html('<i class="bi bi-hourglass-split me-1"></i>Menguji...');

            $.post('<?= base_url('integrasi/dapodik/testConnection') ?>', { [CSRF_NAME]: CSRF_HASH }, function(res) {
                $btn.prop('disabled', false).html('<i class="bi bi-plug-fill me-1.5"></i> Tes Koneksi API');
                if (res.status) {
                    $('#connectionStatusBadge').removeClass('bg-secondary bg-danger').addClass('bg-success').html('<i class="bi bi-check-circle-fill me-1"></i>Terhubung (200 OK)');
                    Swal.fire({
                        title: 'Koneksi Berhasil',
                        text: res.message,
                        icon: 'success',
                        confirmButtonColor: '#2563eb'
                    });
                } else {
                    $('#connectionStatusBadge').removeClass('bg-secondary bg-success').addClass('bg-danger').html('<i class="bi bi-x-circle-fill me-1"></i>Koneksi Gagal');
                    Swal.fire({
                        title: 'Koneksi Gagal',
                        text: res.message,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }).fail(function() {
                $btn.prop('disabled', false).html('<i class="bi bi-plug-fill me-1.5"></i> Tes Koneksi API');
                $('#connectionStatusBadge').removeClass('bg-success bg-danger').addClass('bg-secondary').html('<i class="bi bi-exclamation-triangle me-1"></i>Error Server');
                Swal.fire('Error', 'Gagal menghubungi server aplikasi', 'error');
            });
        });

        // Entity direct sync button
        $('.btn-sync').on('click', function() {
            var $btn = $(this);
            var entitas = $btn.data('entitas');
            var titleText = entitas === 'all' ? 'Sinkronkan SELURUH Data Dapodik?' : 'Sinkronkan Data ' + entitas.toUpperCase() + '?';

            Swal.fire({
                title: titleText,
                text: "Proses ini akan menarik data terbaru dari Web Service Dapodik lokal ke database.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Jalankan Sync',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Memproses...');

                    Swal.fire({
                        title: 'Memproses Sinkronisasi...',
                        text: 'Mohon tunggu, sistem sedang berkomunikasi dengan Web Service Dapodik.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.post('<?= base_url('integrasi/dapodik/syncApi') ?>', { entitas: entitas, [CSRF_NAME]: CSRF_HASH }, function(res) {
                        $btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1.5"></i> ' + $btn.text().replace('Memproses...', '').trim());

                        if (res.status) {
                            Swal.fire({
                                title: 'Sinkronisasi Berhasil!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#2563eb'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal Sinkronisasi', res.message || 'Terjadi kesalahan saat sinkronisasi', 'error');
                        }
                    }).fail(function() {
                        $btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1.5"></i> ' + $btn.text().replace('Memproses...', '').trim());
                        Swal.fire('Error', 'Terjadi kesalahan server saat sinkronisasi.', 'error');
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>
