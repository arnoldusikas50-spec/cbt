<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<?= view('partials/dashboard_theme') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>


<!-- Guru Dashboard Content -->
<!-- Content Header -->
<div class="app-content-header py-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-person-workspace text-primary"></i>
                    <span><?= esc($judul ?? 'Beranda Guru') ?></span>
                </h4>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <ol class="breadcrumb float-sm-end bg-transparent p-0 mb-0" style="font-size: 0.8rem;">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Beranda') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid mt-2">
        <!-- Metric Overview Cards -->
        <div class="row g-2 mb-3">
            <?php
            $metricMap = [
                'Siswa'          => ['card' => 'card-metric-siswa', 'icon' => 'people-fill'],
                'Rombel'         => ['card' => 'card-metric-rombel', 'icon' => 'journal-bookmark-fill'],
                'Guru'           => ['card' => 'card-metric-guru', 'icon' => 'person-fill'],
                'Wali Kelas'     => ['card' => 'card-metric-wali', 'icon' => 'person-badge-fill'],
                'Mapel'          => ['card' => 'card-metric-mapel', 'icon' => 'book-fill'],
                'Ekstrakurikuler'=> ['card' => 'card-metric-ekstra', 'icon' => 'trophy-fill'],
            ];

            foreach ($info_box as $info):
                $cfg = $metricMap[$info['title']] ?? ['card' => 'card-metric-siswa', 'icon' => 'info-circle-fill'];
                ?>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border-0 shadow-sm card-metric <?= $cfg['card'] ?> h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="metric-title fw-bold text-uppercase d-block" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                    <?= esc($info['title']) ?>
                                </span>
                                <h4 class="metric-value fw-bold mb-0 mt-1">
                                    <?= esc($info['total']) ?>
                                </h4>
                            </div>
                            <div class="guru-icon-box">
                                <i class="bi bi-<?= $cfg['icon'] ?> fs-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3">
            <!-- 2. Penilaian & Jadwal Ujian Card -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="guru-icon-box icon-success">
                                <i class="bi bi-journal-check fs-6"></i>
                            </div>
                            <h6 class="card-title fw-bold mb-0 text-dark">Penilaian CBT &amp; Ujian</h6>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <!-- Ujian Boxes -->
                        <div class="row g-2 mb-3">
                            <?php foreach ($ujian_box as $info): ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="p-2 border rounded-2 bg-light bg-opacity-40 text-center h-100">
                                        <span class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.65rem;">
                                            <?= esc($info['title']) ?>
                                        </span>
                                        <h5 class="fw-bold text-dark mb-0 font-monospace">
                                            <?= esc($info['total']) ?>
                                        </h5>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Token Box (Clean, High-Contrast Style) -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="p-2 token-card-box text-center h-100 d-flex flex-column justify-content-center shadow-xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1 px-1">
                                        <span class="fw-bold text-uppercase" style="font-size: 0.65rem; color: #475569;">Token Ujian</span>
                                        <small class="font-monospace text-primary fw-semibold d-none" style="font-size: 0.65rem;" id="interval">--:--:--</small>
                                    </div>
                                    <h5 class="mb-0 font-monospace token-val-text" id="token-view">
                                        <?= esc($token->token ?? '- - - - - -') ?>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <!-- Jadwal Penilaian -->
                        <div class="border rounded-2 p-2 bg-light bg-opacity-25">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-calendar-event text-primary fs-6"></i>
                                <span class="fw-bold text-dark" style="font-size: 0.75rem;">JADWAL PENILAIAN HARI INI</span>
                            </div>
                            <div class="table-responsive" id="table-container"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Pengumuman Card -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center gap-2">
                        <div class="guru-icon-box icon-danger">
                            <i class="bi bi-megaphone fs-6"></i>
                        </div>
                        <h6 class="card-title fw-bold mb-0 text-dark">Info &amp; Pengumuman Sekolah</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="konten-pengumuman">
                            <div id="pengumuman"></div>
                            <div id="loading-post" class="text-center d-none py-2">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div> 
                                <span class="ms-2 small text-muted">Memuat pengumuman...</span>
                            </div>
                            <div id="loadmore-post" onclick="getPosts()" class="text-center mt-2 loadmore d-none">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-3" style="font-size: 0.75rem;">
                                    <i class="bi bi-arrow-down-circle me-1"></i> Muat Pengumuman Lainnya
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?= view('partials/comment_modals') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/dashboard.js') ?>"></script>
<?= $this->endSection() ?>

