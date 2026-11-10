<?= $this->extend('layouts/main') ?>

<?php
/**
 * Dashboard Panitia — memakai tema kartu yang sama dengan dashboard Guru
 * (lihat app/Views/partials/dashboard_theme.php).
 *
 * Helper lokal view ini:
 * - $pncTanggal : tanggal Bahasa Indonesia.
 * - $pncSlug    : id elemen yang dicari dashboard.js ("total-<slug>" /
 *                 "total-ujian-<slug>") supaya real-time update tetap jalan.
 * - $pncMissing : field administrasi yang belum diisi (bikin banner pengingat).
 */
$bulan_arr = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$hari_arr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$pncTanggal = $hari_arr[(int) date('w')] . ', ' . (int) date('j') . ' ' . $bulan_arr[(int) date('n')] . ' ' . date('Y');

$pncSlug = static fn ($title) => strtolower(str_replace(' ', '-', (string) $title));

// Pemetaan kartu metrik ke varian kartu & ikon tema Guru.
// `url` di sini hanya fallback: bila controller sudah mengirim `url`, itu yang dipakai.
$pncMetricMap = [
    'Guru'             => ['card' => 'card-metric-guru',   'icon' => 'person-fill',             'url' => 'dataguru'],
    'Siswa'            => ['card' => 'card-metric-siswa',  'icon' => 'people-fill',             'url' => 'datasiswa'],
    'Pengawas'         => ['card' => 'card-metric-mapel',  'icon' => 'person-badge-fill',       'url' => ''],
    'Jadwal Aktif'     => ['card' => 'card-metric-wali',   'icon' => 'clock-fill',               'url' => 'cbtjadwal'],
    'Belum Ujian'      => ['card' => 'card-metric-ekstra', 'icon' => 'person-fill-exclamation', 'url' => 'cbtstatus'],
    'Siswa Anda'       => ['card' => 'card-metric-siswa',  'icon' => 'people-fill',             'url' => 'cbtstatus'],
    'Ruang dan Sesi'   => ['card' => 'card-metric-mapel',  'icon' => 'door-open-fill',          'url' => 'cbtstatus'],
];

$pncSupervisorInfo = $supervisor_info ?? [];

// Pengingat (bukan penghalang) bila data administrasi belum lengkap.
// Field ini dipakai sistem untuk Berita Acara & Pakta Integritas ujian.
$pncMissing = [];
if (empty($panitia->nama_panitia)) {
    $pncMissing[] = 'Nama lengkap';
}
if (empty($panitia->nip)) {
    $pncMissing[] = 'NIP/NUPTK';
}
if (empty($panitia->ttd)) {
    $pncMissing[] = 'Tanda tangan digital';
}
if (! empty($is_pengawas) && empty($panitia->instansi)) {
    $pncMissing[] = 'Instansi/sekolah asal';
}
if (! empty($is_pengawas) && empty($panitia->ruang)) {
    $pncMissing[] = 'Ruang ujian';
}
?>

<?= $this->section('styles') ?>
<?= view('partials/dashboard_theme') ?>
<style>
    /* Semua tambahan khusus panitia di-scope di .pnc-shell supaya tidak
       bocor ke halaman lain (dashboard Guru memakai tema yang sama). */
    .pnc-shell .min-w-0 {
        min-width: 0;
    }

    /* utilitas kecil untuk tombol Segarkan (di luar .pnc-shell) */
    @keyframes pncSpin {
        to {
            transform: rotate(360deg);
        }
    }

    .pnc-spin {
        display: inline-block;
        animation: pncSpin .8s linear infinite;
    }

    @media (prefers-reduced-motion: reduce) {

        .pnc-spin {
            animation: none;
        }
    }

    .pnc-shell .card-metric.is-static:hover {
        transform: none;
    }

    .pnc-shell .card-metric {
        cursor: pointer;
    }

    .pnc-shell .card-metric.is-static {
        cursor: default;
    }

    /* ---------- Tabel jadwal (di-generate dashboard.js) ---------- */
    .pnc-shell .pnc-table-wrap .table {
        margin-bottom: 0;
    }

    .pnc-shell .pnc-table-wrap .table thead th {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        color: #0369a1;
        font-size: .66rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        white-space: nowrap;
        vertical-align: middle;
        border-bottom: 2px solid #7dd3fc;
        padding: .5rem .4rem;
    }

    .pnc-shell .pnc-table-wrap .table thead tr:nth-child(2) th {
        border-top: 1px solid #bae6fd;
    }

    /* Baris header kedua hanya berisi 3 kolom status (BLM/SDG/SELESAI),
       jadi penwarnanya nth-child(1..3), bukan 8..10. */
    .pnc-shell .pnc-table-wrap .table thead tr:nth-child(2) th:nth-child(1) {
        color: #b91c1c;
    }

    .pnc-shell .pnc-table-wrap .table thead tr:nth-child(2) th:nth-child(2) {
        color: #0369a1;
    }

    .pnc-shell .pnc-table-wrap .table thead tr:nth-child(2) th:nth-child(3) {
        color: #047857;
    }

    .pnc-shell .pnc-table-wrap .table tbody td {
        font-size: .82rem;
        vertical-align: middle;
        color: #0f172a;
    }

    /* hover tanpa merusak zebra `bg-light` dari dashboard.js */
    .pnc-shell .pnc-table-wrap .table tbody tr:hover>td {
        box-shadow: inset 0 0 0 999px rgba(14, 165, 233, .07);
    }

    /* badge status: Belum = merah, Sedang = biru, Selesai = hijau */
    .pnc-shell .pnc-table-wrap .table tbody .badge {
        min-width: 34px;
        padding: .35rem .5rem;
        font-weight: 700;
    }

    .pnc-shell .pnc-table-wrap .table tbody .badge.bg-warning {
        background-color: #0ea5e9 !important;
        color: #fff !important;
    }

    /* sel pengawas kosong -> beri petunjuk, jangan dibiarkan blank */
    .pnc-shell .pnc-table-wrap .table tbody td.td-pengawas:empty::after {
        content: 'Belum ditentukan';
        display: inline-block;
        font-size: .68rem;
        font-weight: 600;
        color: #92400e;
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 999px;
        padding: .1rem .5rem;
    }

    /* ruang milik pengawas sendiri (dashboard.js -> tr.table-warning).
       .table-warning + .border-primary dari Bootstrap bikin baris cokelat
       kusam dan garis biru di semua sel; di sini jadi aksen kiri yang lembut. */
    .pnc-shell .pnc-table-wrap .table tbody tr.table-warning {
        border-color: #fde68a !important;
    }

    .pnc-shell .pnc-table-wrap .table tbody tr.table-warning>td {
        background-color: #fffbeb;
        border-color: #fde68a !important;
        box-shadow: none;
    }

    .pnc-shell .pnc-table-wrap .table tbody tr.table-warning>td:first-child {
        box-shadow: inset 4px 0 0 0 #f59e0b;
        color: #92400e !important;
    }

    .pnc-shell .pnc-table-wrap .table tbody tr.table-warning:hover>td {
        box-shadow: inset 0 0 0 999px rgba(245, 158, 11, .12);
    }

    .pnc-shell .pnc-table-wrap .table tbody tr.table-warning:hover>td:first-child {
        box-shadow: inset 4px 0 0 0 #f59e0b, inset 0 0 0 999px rgba(245, 158, 11, .12);
    }

    /* ---------- Kartu pengumuman ---------- */
    /* Ukuran huruf bawaan post terlalu besar sehingga dwarfing dashboard;
      diturunkan di sini, scoped ke .konten-pengumuman. */
    .pnc-shell .konten-pengumuman .post-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }

    .pnc-shell .konten-pengumuman .post-card:hover {
        box-shadow: 0 12px 28px -10px rgba(15, 23, 42, .18);
    }

    .pnc-shell .konten-pengumuman .post-card .card-body {
        padding: 1rem 1.1rem;
    }

    /* .post-content di-nowrap oleh dashboard.js; isinya sudah HTML <p>,
       jadi pre-wrap hanya menambah celah kosong dari indentasi template.
       max-width menjaga panjang baris tetap enak dibaca di kartu selebar layar. */
    .pnc-shell .konten-pengumuman .post-card .post-content {
        white-space: normal;
        font-size: .84rem !important;
        line-height: 1.55;
        max-width: 90ch;
    }

    .pnc-shell .konten-pengumuman .post-card p,
    .pnc-shell .konten-pengumuman .post-card .comment-text {
        font-size: .84rem;
        line-height: 1.55;
        margin-bottom: .65rem;
    }

    .pnc-shell .konten-pengumuman .post-card p:last-child {
        margin-bottom: 0;
    }

    .pnc-shell .konten-pengumuman .post-card img.rounded-circle {
        width: 38px;
        height: 38px;
    }

    .pnc-shell .konten-pengumuman .post-card .btn-link {
        text-decoration: none;
    }

    .pnc-shell .konten-pengumuman .post-card .btn-sm {
        font-size: .78rem;
        padding: .25rem .7rem;
    }

    /* ---------- Feed aktivitas (di-generate dashboard.js) ---------- */
    /* Kartu ini berada di kolom sempit (col-lg-4), jadi grid ini tetap
       satu kolom di layar normal dan otomatis jadi 2 kolom bila lebar. */
    .pnc-shell .activity-feed {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        align-content: start;
        gap: .5rem;
        padding: .75rem;
    }

    .pnc-shell .activity-item {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .55rem .65rem;
        margin: 0 !important;
        transition: background .2s ease, border-color .2s ease;
    }

    .pnc-shell .activity-item:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* garis penghubung timeline tidak relevan lagi di mode grid */
    .pnc-shell .activity-line {
        display: none !important;
    }

    .pnc-shell .activity-icon {
        transition: transform .2s ease;
    }

    .pnc-shell .activity-item:hover .activity-icon {
        transform: scale(1.08);
    }

    @media (max-width: 575.98px) {
        .pnc-shell .pnc-table-wrap .table thead th {
            font-size: .58rem;
            padding: .4rem;
        }

        .pnc-shell .pnc-table-wrap .table tbody td {
            font-size: .74rem;
            padding: .4rem;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header py-2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-speedometer2 text-primary"></i>
                    <span>Halo, <?= esc($panitia->nama_panitia ?? 'Panitia') ?></span>
                </h4>
                <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                    <span class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar3 me-1"></i><?= $pncTanggal ?>
                    </span>
                    <?php if (!empty($is_pengawas)): ?>
                        <span class="badge rounded-pill bg-warning text-dark" style="font-size: 0.62rem;">
                            <i class="bi bi-shield-lock-fill me-1"></i>MODE PENGAWAS
                        </span>
                    <?php else: ?>
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary" style="font-size: 0.62rem;">
                            <i class="bi bi-person-badge-fill me-1"></i>PANITIA UJIAN
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <div class="d-flex align-items-center justify-content-sm-end gap-2">
                    <button type="button" class="btn btn-sm btn-light border text-secondary shadow-sm" id="btn-refresh"
                        title="Muat ulang data dashboard">
                        <i class="bi bi-arrow-clockwise"></i><span class="d-none d-sm-inline ms-1">Segarkan</span>
                    </button>
                    <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Dashboard Panitia</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content pnc-shell">
    <div class="container-fluid mt-2">

        <!-- ============ Pengingat kelengkapan profil ============ -->
        <!-- Banner (bukan modal) supaya tidak menghalangi kerjarutin harian. -->
        <?php if (!empty($pncMissing)): ?>
            <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 p-2 px-3 mb-3 rounded-3 shadow-sm"
                role="alert" id="pnc-profil-alert">
                <div class="guru-icon-box icon-warning">
                    <i class="bi bi-person-exclamation fs-6"></i>
                </div>
                <div class="flex-grow-1" style="font-size: 0.82rem;">
                    <strong>Lengkapi profil Anda:</strong>
                    <span class="text-muted"><?= esc(implode(', ', $pncMissing)) ?></span>
                    <span class="d-block text-muted" style="font-size: 0.75rem;">
                        Data ini dipakai untuk pencetakan Berita Acara &amp; Pakta Integritas.
                    </span>
                </div>
                <a href="<?= site_url('panitia') ?>" class="btn btn-sm btn-warning fw-bold">
                    <i class="bi bi-pencil-square me-1"></i>Lengkapi
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <!-- ============ Kartu metrik ============ -->
        <?php
        $pncCards = [];
        if (!empty($is_pengawas)) {
            // Supervisor: statistik tugas yang sudah dihitung di backend.
            foreach (($pncSupervisorInfo['boxes'] ?? []) as $b) {
                $pncCards[] = $b;
            }
        } else {
            foreach (($info_box ?? []) as $b) {
                $pncCards[] = $b;
            }
        }
        ?>

        <?php if (!empty($pncCards)): ?>
            <div class="row g-2 mb-3">
                <?php foreach ($pncCards as $box):
                    $cfg = $pncMetricMap[$box['title']] ?? ['card' => 'card-metric-siswa', 'icon' => 'info-circle-fill', 'url' => ''];
                    // `url` dari controller menang; peta view hanya fallback.
                    $rawUrl = $box['url'] ?? ($cfg['url'] ?? '');
                    $url = !empty($rawUrl) ? site_url($rawUrl) : '';
                    $slug = $pncSlug($box['title']);

                    $cardInner = '<div class="d-flex align-items-center justify-content-between">'
                        . '<div class="min-w-0">'
                        . '<span class="metric-title fw-bold text-uppercase d-block" style="font-size: 0.68rem; letter-spacing: 0.5px;">'
                        . esc($box['title']) . '</span>'
                        . '<h4 class="metric-value fw-bold mb-0 mt-1" id="total-' . $slug . '">'
                        . esc($box['total']) . '</h4>'
                        . '</div>'
                        . '<div class="guru-icon-box"><i class="bi bi-' . $cfg['icon'] . ' fs-6"></i></div>'
                        . '</div>';
                    ?>
                    <div class="col-6 col-md-4 col-lg">
                        <?php if ($url !== ''): ?>
                            <a href="<?= $url ?>" class="text-decoration-none d-block h-100"
                                aria-label="<?= esc($box['title']) ?>: <?= esc($box['total']) ?>">
                                <div class="card border-0 shadow-sm card-metric <?= $cfg['card'] ?> h-100">
                                    <?= $cardInner ?>
                                </div>
                            </a>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm card-metric is-static <?= $cfg['card'] ?> h-100">
                                <?= $cardInner ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ============ Banner tugas pengawas ============ -->
        <?php if (!empty($is_pengawas) && !empty($assignments)): ?>
            <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 p-2 px-3 mb-3 rounded-3 shadow-sm">
                <div class="guru-icon-box icon-warning">
                    <i class="bi bi-geo-alt-fill fs-6"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Tugas Pengawasan Anda</strong>
                    <span class="d-block text-muted" style="font-size: 0.78rem;">
                        Ruang: <?= esc($pncSupervisorInfo['text'] ?? 'Belum ditentukan') ?> —
                        pastikan kehadiran peserta sesuai daftar di halaman monitoring.
                    </span>
                </div>
                <a href="<?= site_url('cbtstatus') ?>" class="btn btn-sm btn-warning fw-bold px-3">
                    <i class="bi bi-arrow-right me-1"></i>Buka Monitoring
                </a>
            </div>
        <?php elseif (!empty($is_pengawas)): ?>
            <!-- Supervisors tanpa penugasan: beri penjelasan, jangan halaman kosong. -->
            <div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 p-2 px-3 mb-3 rounded-3 shadow-sm">
                <div class="guru-icon-box" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">
                    <i class="bi bi-calendar2-x fs-6"></i>
                </div>
                <div class="flex-grow-1">
                    <strong>Belum ada tugas pengawasan</strong>
                    <span class="d-block text-muted" style="font-size: 0.78rem;">
                        Anda belum ditugaskan mengawasi ruang ujian pada tahun/semester aktif.
                        Sila cek kembali saat ujian berlangsung atau hubungi koordinator ujian.
                    </span>
                </div>
                <a href="<?= site_url('cbtjadwal') ?>" class="btn btn-sm btn-light border text-secondary shadow-sm">
                    <i class="bi bi-calendar3 me-1"></i>Lihat Jadwal
                </a>
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <!-- ============ Penilaian & jadwal ujian ============ -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                    <div
                        class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="guru-icon-box icon-success">
                                <i class="bi bi-journal-check fs-6"></i>
                            </div>
                            <h6 class="card-title fw-bold mb-0 text-dark">Penilaian CBT &amp; Ujian</h6>
                        </div>
                        <a href="<?= site_url('cbtjadwal') ?>" class="btn btn-sm btn-light border text-secondary shadow-sm">
                            <i class="bi bi-calendar3 me-1"></i>Kelola Jadwal
                        </a>
                    </div>
                    <div class="card-body p-3">
                        <!-- Box status ujian + token -->
                        <div class="row g-2 mb-3">
                            <?php foreach (($ujian_box ?? []) as $box): ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="p-2 border rounded-2 bg-light bg-opacity-40 text-center h-100">
                                        <span class="text-muted fw-bold text-uppercase d-block mb-1"
                                            style="font-size: 0.65rem;">
                                            <?= esc($box['title']) ?>
                                        </span>
                                        <h5 class="fw-bold text-dark mb-0 font-monospace"
                                            id="total-ujian-<?= $pncSlug($box['title']) ?>">
                                            <?= esc($box['total']) ?>
                                        </h5>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Token -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="p-2 token-card-box text-center h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center justify-content-between mb-1 px-1">
                                        <span class="fw-bold text-uppercase" style="font-size: 0.65rem; color: #475569;">
                                            Token Ujian
                                        </span>
                                        <small class="font-monospace text-primary fw-semibold d-none" id="interval"
                                            style="font-size: 0.62rem;">--:--:--</small>
                                    </div>
                                    <h5 class="mb-0 font-monospace token-val-text" id="token-view">
                                        <?= esc($token->token ?? '- - - - - -') ?>
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <!-- Jadwal penilaian -->
                        <div class="border rounded-2 p-2 bg-light bg-opacity-25 pnc-table-wrap">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-calendar-event text-primary fs-6"></i>
                                <span class="fw-bold text-dark" style="font-size: 0.75rem;">JADWAL PENILAIAN HARI INI</span>
                            </div>
                            <!-- Placeholder ini satu-satunya indikator loading;
                                 dashboard.js menimpanya dengan tabel / pesan kosong. -->
                            <div class="table-responsive" id="table-container">
                                <div class="text-center py-5" role="status">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <div class="text-muted mt-2 small">Memuat jadwal ujian...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ Pengumuman ============ -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white h-100">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center gap-2">
                        <div class="guru-icon-box icon-danger">
                            <i class="bi bi-megaphone fs-6"></i>
                        </div>
                        <h6 class="card-title fw-bold mb-0 text-dark">Info &amp; Pengumuman</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="konten-pengumuman">
                            <div id="pengumuman"></div>
                            <div id="loading-post" class="text-center d-none py-3" role="status">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                                <span class="ms-2 text-muted" style="font-size: 0.75rem;">Memuat pengumuman...</span>
                            </div>
                            <div id="pengumuman-empty" class="d-none text-center py-5">
                                <i class="bi bi-inbox text-muted fs-2"></i>
                                <p class="text-muted mt-2 mb-0 small">Belum ada pengumuman</p>
                            </div>
                            <div id="loadmore-post" class="text-center mt-3 d-none">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Muat Pengumuman Lainnya
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ Aktivitas terbaru ============ -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white h-100">
                    <div
                        class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="guru-icon-box icon-info">
                                <i class="bi bi-activity fs-6"></i>
                            </div>
                            <h6 class="card-title fw-bold mb-0 text-dark">Aktivitas Terbaru</h6>
                        </div>
                        <a href="<?= site_url('datalogs') ?>"
                            class="btn btn-sm btn-light border text-secondary shadow-sm"
                            title="Lihat semua log" aria-label="Lihat semua log">
                            <i class="bi bi-box-arrow-up-right me-1"></i><span class="d-none d-sm-inline">Log Aktivitas</span>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div id="log-list">
                            <div id="log-loading" class="text-center py-5" role="status">
                                <div class="spinner-border spinner-border-sm text-info"></div>
                                <div class="text-muted mt-2 small">Memuat aktivitas...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('modals') ?>
<?= view('partials/comment_modals') ?>

<div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="guru-icon-box" style="background-color: rgba(255,255,255,.25); color: #fff;">
                        <i class="bi bi-hand-thumbs-up"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="welcomeModalLabel">
                            Selamat Datang, <?= esc($panitia->nama_panitia ?? 'Panitia') ?>
                        </h5>
                        <small class="text-white-75">
                            <?= !empty($is_pengawas) ? 'Pengawas Ujian' : 'Panitia Ujian' ?>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3">
                <?php if (!empty($is_pengawas)): ?>
                    <div class="alert alert-primary bg-opacity-10 border-0 rounded-3 mb-3">
                        <div class="d-flex gap-2">
                            <i class="bi bi-shield-lock-fill text-primary"></i>
                            <div class="small">
                                <strong class="d-block">Anda bertugas sebagai Pengawas Ujian</strong>
                                Ruang: <strong><?= esc($pncSupervisorInfo['text'] ?? 'Belum ditentukan') ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-2" style="font-size: 0.8rem;">
                            <i class="bi bi-lightbulb-fill me-1"></i>Tips Cepat
                        </h6>
                        <ul class="list-unstyled mb-0 small text-secondary">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Periksa jadwal ujian di bawah</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Pastikan token ujian aktif</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Pantau status peserta real-time</li>
                            <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Laporkan kendala ke admin segera</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-2" style="font-size: 0.8rem;">
                            <i class="bi bi-link-45deg me-1"></i>Menu Cepat
                        </h6>
                        <div class="d-grid gap-2">
                            <?php if (!empty($is_pengawas)): ?>
                                <a href="<?= site_url('cbtstatus') ?>" class="btn btn-outline-primary btn-sm text-start px-3">
                                    <i class="bi bi-person-workspace me-2"></i>Monitoring Ujian
                                </a>
                            <?php endif; ?>
                            <a href="<?= site_url('cbtjadwal') ?>" class="btn btn-outline-primary btn-sm text-start px-3">
                                <i class="bi bi-calendar3 me-2"></i>Jadwal Ujian
                            </a>
                            <a href="<?= site_url('cbtstatus') ?>" class="btn btn-outline-primary btn-sm text-start px-3">
                                <i class="bi bi-person-check me-2"></i>Status Siswa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <div class="form-check me-auto">
                    <input class="form-check-input" type="checkbox" id="dontShowAgain">
                    <label class="form-check-label small text-muted" for="dontShowAgain">
                        Jangan tampilkan lagi
                    </label>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3" data-bs-dismiss="modal"
                    id="welcomeConfirm">
                    <i class="bi bi-check-lg me-1"></i>Mengerti, Mulai
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/dashboard.js') ?>"></script>
<script>
    $(function () {
        // --- Tombol "Muat Pengumuman Lainnya" ---
        // getPosts() dibungkus IIFE di dashboard.js, jadi onclick inline tidak jalan.
        $('#loadmore-post').on('click', function () {
            if (typeof window.loadMorePosts === 'function') window.loadMorePosts();
        });

        // --- Tombol Segarkan ---
        $('#btn-refresh').on('click', function () {
            const $btn = $(this).prop('disabled', true);
            const $icon = $btn.find('i');
            $icon.addClass('pnc-spin');
            if (typeof window.refreshDashboard === 'function') {
                window.refreshDashboard();
            }
            setTimeout(() => {
                $btn.prop('disabled', false);
                $icon.removeClass('pnc-spin');
            }, 1500);
        });

        // --- Modal selamat datang ---
        // Tampil maksimal 1x per sesi browser (sessionStorage); "Jangan tampilkan
        // lagi" menguncinya permanen (localStorage). Sebelumnya hanya localStorage
        // yang dicek sehingga modal muncul di setiap refresh selama checkbox tidak
        // dicentang.
        const $welcome = $('#welcomeModal');
        const seenEver = localStorage.getItem('panitiaWelcomeModalShown') === 'true';
        const seenSession = sessionStorage.getItem('panitiaWelcomeSeen') === '1';

        if ($welcome.length && !seenEver && !seenSession) {
            setTimeout(() => bootstrap.Modal.getOrCreateInstance($welcome[0]).show(), 600);
        }
        $welcome.on('hidden.bs.modal', function () {
            sessionStorage.setItem('panitiaWelcomeSeen', '1');
            if ($('#dontShowAgain').is(':checked')) {
                localStorage.setItem('panitiaWelcomeModalShown', 'true');
            }
        });

        // --- Pengingat profil: ingat pilihan "tutup" per perangkat ---
        const profilKey = 'panitia.profilAlertClosed';
        if (localStorage.getItem(profilKey) === '1') {
            $('#pnc-profil-alert').remove();
        } else {
            $('#pnc-profil-alert').on('closed.bs.alert', function () {
                localStorage.setItem(profilKey, '1');
            });
        }
    });
</script>
<?= $this->endSection() ?>