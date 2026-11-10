<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header dengan Stats Boxes -->
<section class="content-header p-0 d-flex align-items-end fade-in-up">
    <div class="container-fluid"
        style="padding: 0 !important; margin: 0 !important; background-color: rgba(255,255,255,0.7);">

        <!-- Info Boxes -->
        <div class="row m-0">
            <?php foreach ($info_box as $info): ?>
                <div class="col-md-2 col-4">
                    <?php
                    // Hanya jadi link bila URL-nya benar-benar ada route-nya
                    // (getAdminBox() bisa mengirim url kosong / route yang tidak ada).
                    $boxUrl = !empty($info['url']) ? base_url($info['url']) : '';
                    $iconMap = [
                        'users' => 'people-fill',
                        'chalkboard-teacher' => 'person-video3',
                        'book' => 'book',
                        'user' => 'person-fill',
                        'user-o' => 'person',
                        'user-circle' => 'person-circle',
                        'user-tie' => 'person-badge',
                        'user-clock' => 'person-fill-exclamation',
                        'bell' => 'bell-fill',
                        'school' => 'building',
                        'clock' => 'clock-fill',
                        'folder' => 'folder-fill',
                        'universal-access' => 'person-arms-up'
                    ];
                    $biIcon = $iconMap[$info['icon']] ?? $info['icon'];
                    ob_start(); ?>
                        <div class="shadow small-box grad-<?= esc($info['box']) ?>">
                            <div class="inner">
                                <h5 class="mb-0 text-white" id="total-<?= esc(strtolower(str_replace(' ', '-', $info['title']))) ?>">
                                    <b><?= esc($info['total']) ?></b>
                                </h5>
                                <span class="text-white"><?= esc($info['title']) ?></span>
                            </div>
                            <div class="icon">
                                <i class="bi bi-<?= esc($biIcon) ?>"></i>
                            </div>
                            <div class="small-box-footer" style="background: rgba(0,0,0,0.1)">
                                Detail <i class="bi bi-arrow-right-circle-fill"></i>
                            </div>
                        </div>
                    <?php
                    $boxInner = ob_get_clean();
                    if ($boxUrl !== '') { ?>
                        <a href="<?= esc($boxUrl) ?>" class="text-decoration-none d-block"
                            aria-label="<?= esc($info['title']) ?>: <?= esc($info['total']) ?>">
                            <?= $boxInner ?>
                        </a>
                    <?php } else { ?>
                        <div class="d-block"><?= $boxInner ?></div>
                    <?php } ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="content mt-4">
    <div class="container-fluid" style="padding-left: 1rem !important; padding-right: 1rem !important;">
        <div class="row">
            <!-- Kolom Kiri: Penilaian (CBT) -->
            <div class="col-12">
                <div class="card card-danger my-shadow">
                    <div class="card-header">
                        <h4 class="card-title">Penilaian</h4>
                    </div>
                    <div class="card-body">
                        <!-- Ujian Boxes -->
                        <div class="row">
                            <?php foreach ($ujian_box as $info): ?>
                                <div class="col-4 col-md-3 col-lg-2" style="min-height: 60px">
                                    <a href="<?= base_url($info['url']) ?>" class="text-decoration-none">
                                        <div class="info-box bg-white border border-<?= $info['box'] ?> p-1 shadow-sm"
                                            style="min-height: 60px; border-width: 2px !important">
                                            <div class="info-box-content p-1 text-dark">
                                                <span class="info-box-text"
                                                    style="font-size: 0.8rem"><?= esc($info['title']) ?></span>
                                                <h5 class="info-box-number m-0"
                                                    id="total-ujian-<?= strtolower(str_replace(' ', '-', $info['title'])) ?>">
                                                    <?= esc($info['total']) ?>
                                                </h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>

                            <!-- Token Box -->
                            <div class="col-6 col-md-3 col-lg-2" style="min-height: 60px">
                                <a href="<?= base_url('cbttoken') ?>" class="text-decoration-none">
                                    <div class="info-box bg-white border border-indigo p-1 shadow-sm"
                                        style="min-height: 60px; border-width: 2px !important">
                                        <div class="info-box-content p-1 text-dark">
                                            <span class="info-box-text" style="font-size: 0.8rem">Token
                                                <small class="float-end d-none" id="interval">-- : -- : --</small>
                                            </span>
                                            <h5 class="info-box-number m-0 font-monospace" id="token-view">
                                                <?= esc($token->token ?? '- - - - - -') ?>
                                            </h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <hr>

                        <!-- Jadwal Penilaian Hari Ini -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-center grad-info text-white p-2 rounded shadow-sm">
                                    <b>JADWAL PENILAIAN HARI INI</b>
                                </h6>
                                <div class="col-12 table-responsive" id="table-container">
                                    <div class="text-center py-4" role="status">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                        <div class="text-muted mt-2 small">Memuat jadwal ujian...</div>
                                    </div>
                                </div>
                                <div id="table-loading" class="d-none text-center py-2">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <span class="ms-2 text-muted small">Memuat ulang jadwal...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Aktivitas & Pengumuman -->
            <div class="col-md-4">
                <!-- Aktivitas Log -->
                <div class="card card-success my-shadow">
                    <div class="card-header">
                        <div class="card-title">Aktivitas</div>
                        <div class="card-tools ms-auto">
                            <a href="<?= base_url('datalogs') ?>" class="btn btn-sm btn-light" title="Lihat semua log"
                                aria-label="Lihat semua log">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="log-list">
                            <div class="text-center py-4" role="status">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                <div class="text-muted mt-2 small">Memuat aktivitas...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Pengumuman/Info -->
                <div class="card card-danger my-shadow mb-2">
                    <div class="card-header">
                        <div class="card-title">INFO/PENGUMUMAN</div>
                    </div>
                    <div class="card-body">
                        <div class="konten-pengumuman">
                            <div id="pengumuman"></div>
                            <div id="loading-post" class="text-center d-none">
                                <div class="spinner-border spinner-border-sm" role="status"></div> Memuat...
                            </div>
                            <div id="pengumuman-empty" class="d-none text-center py-4">
                                <i class="bi bi-inbox text-muted fs-2"></i>
                                <p class="text-muted mt-2 mb-0 small">Belum ada pengumuman</p>
                            </div>
                            <div id="loadmore-post" class="text-center mt-1 loadmore d-none">
                                <button type="button" class="btn btn-warning">Muat Pengumuman lainnya ...</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Komentar (Modern Style) -->
<div class="modal fade" id="komentarModal" tabindex="-1" aria-labelledby="komentarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="komentarLabel">
                    <i class="bi bi-chat-dots me-2"></i> Tulis Komentar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="komentar">
                <div class="modal-body p-4">
                    <input type="hidden" id="id-post" name="id_post" value="">
                    <div class="d-flex align-items-start">
                        <img class="rounded-circle border shadow-sm flex-shrink-0 me-3"
                            src="<?= base_url('assets/img/logo.png') ?>" alt="Avatar"
                            style="width: 48px; height: 48px; object-fit: cover;" onerror="this.src='<?= base_url('assets/img/logo.png') ?>'">
                        <div class="flex-grow-1">
                            <textarea name="text" class="form-control border-0 bg-light rounded-3 py-2 mention-input"
                                placeholder="Tulis komentar..." rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-send me-1"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Balasan (Modern Style) -->
<div class="modal fade" id="balasanModal" tabindex="-1" aria-labelledby="balasanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-info text-white py-3">
                <h5 class="modal-title fw-bold" id="balasanLabel">
                    <i class="bi bi-reply me-2"></i> Tulis Balasan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="balasan">
                <div class="modal-body p-4">
                    <input type="hidden" id="id-comment" name="id_comment" value="">
                    <div class="d-flex align-items-start">
                        <img class="rounded-circle border shadow-sm flex-shrink-0 me-3"
                            src="<?= base_url('assets/img/logo.png') ?>" alt="Avatar"
                            style="width: 48px; height: 48px; object-fit: cover;" onerror="this.src='<?= base_url('assets/img/logo.png') ?>'">
                        <div class="flex-grow-1">
                            <textarea name="text" class="form-control border-0 bg-light rounded-3 py-2 mention-input"
                                placeholder="Tulis balasan..." rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-info rounded-pill px-4 text-white">
                        <i class="bi bi-reply me-1"></i> Balas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/dashboard.js') ?>"></script>
<script>
    // getPosts() di dalam IIFE dashboard.js, jadi tidak bisa dipanggil lewat
    // onclick inline. delegated handler ke window.loadMorePosts.
    $(function () {
        $('#loadmore-post').on('click', function () {
            if (typeof window.loadMorePosts === 'function') {
                window.loadMorePosts();
            }
        });
    });
</script>
<?= $this->endSection() ?>