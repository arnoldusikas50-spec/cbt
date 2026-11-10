<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
/** @var object $tp_active */
/** @var object $smt_active */
/** @var object $setting */
$tp_active = (object) ($tp_active ?? []);
$smt_active = (object) ($smt_active ?? []);
$setting = (object) ($setting ?? []);
?>

<style>
    :root {
        --cbt-primary: #4f46e5;
        --cbt-primary-light: #eef2ff;
        --cbt-success: #10b981;
        --cbt-success-light: #ecfdf5;
        --cbt-info: #06b6d4;
        --cbt-info-light: #ecfeff;
        --cbt-warning: #f59e0b;
        --cbt-warning-light: #fffbeb;
        --cbt-danger: #ef4444;
        --cbt-danger-light: #fef2f2;
        --cbt-secondary: #64748b;
        --cbt-gray: #f8fafc;
        --cbt-border: #eef2f6;
        --cbt-text: #1e293b;
        --cbt-text-muted: #64748b;
    }

    .fade-in-up { 
        animation: fadeInUp 0.5s ease-out; 
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .filter-card {
        border: none;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 20px -5px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
    }

    .table-results-container {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--cbt-border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        background: white;
    }

    .table-results {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100%;
        margin-bottom: 0 !important;
    }

    .table-results thead th {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #334155 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.68rem;
        letter-spacing: 0.05em;
        padding: 14px 10px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-right: 1px solid #f1f5f9 !important;
        text-align: center;
        vertical-align: middle !important;
        position: sticky;
        z-index: 12;
    }

    .header-group-indigo { 
        background: rgba(238, 242, 255, 0.95) !important; 
        color: #4338ca !important;
        border-bottom: 2px solid #c7d2fe !important;
    }
    .header-group-cyan { 
        background: rgba(236, 254, 255, 0.95) !important; 
        color: #0891b2 !important;
        border-bottom: 2px solid #a5f3fc !important;
    }
    .header-group-slate { 
        background: rgba(241, 245, 249, 0.95) !important; 
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }

    /* First header row sticks at top */
    .table-results thead tr:first-child th {
        top: 0;
        z-index: 13;
    }

    /* Second header row sticks below first row */
    .table-results thead tr:nth-child(2) th {
        top: 44px;
        z-index: 12;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        background-color: #ffffff !important;
    }

    .table-results tbody tr {
        transition: all 0.2s ease;
    }

    .table-results tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    .table-results tbody td {
        padding: 12px 10px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid var(--cbt-border) !important;
        border-right: 1px solid var(--cbt-border) !important;
        color: var(--cbt-text);
        font-size: 0.875rem;
    }

    .table-results tbody tr:last-child td {
        border-bottom: none !important;
    }

    .student-card {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .student-name {
        font-weight: 700;
        color: var(--cbt-text);
        font-size: 0.9rem;
        line-height: 1.2;
    }

    .student-badge {
        font-family: 'JetBrains Mono', 'Monaco', 'Consolas', monospace;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        background: white;
        color: var(--cbt-text-muted);
        border-radius: 6px;
        width: fit-content;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .pill-session {
        background: var(--cbt-primary-light);
        color: var(--cbt-primary);
        font-weight: 700;
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .pill-room {
        background: var(--cbt-info-light);
        color: var(--cbt-info);
        font-weight: 700;
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 6px;
    }

    .time-badge {
        font-weight: 700;
        color: var(--cbt-text);
        font-size: 0.8rem;
    }

    .score-badge {
        padding: 4px 8px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 0.95rem;
        min-width: 45px;
        display: inline-block;
        text-align: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .score-asli { background: #f8fafc; color: var(--cbt-text); border: 1px solid #e2e8f0; }
    .score-katrol { background: var(--cbt-success-light); color: var(--cbt-success); border: 1px solid #bef264; }
    .score-foul { background: var(--cbt-danger-light); color: var(--cbt-danger); border: 1px solid #fecaca; }

    .btn-cbt {
        border-radius: 8px;
        font-weight: 600;
        font-size: 11px;
        padding: 6px 12px;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-cbt:hover { transform: translateY(-1px); }
    .btn-cbt:active { transform: translateY(0); }

    .btn-cbt-success { background: var(--cbt-success); color: white; }
    .btn-cbt-success:hover { background: #059669; color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }

    .btn-cbt-info { background: var(--cbt-info); color: white; }
    .btn-cbt-info:hover { background: #0891b2; color: white; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2); }

    .btn-cbt-danger { background: var(--cbt-danger); color: white; }
    .btn-cbt-danger:hover { background: #dc2626; color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); }

    .btn-cbt-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        justify-content: center;
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 10px !important;
        border: 1px solid #e2e8f0 !important;
        height: 42px !important;
        padding-top: 6px !important;
    }

    .stats-panel {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px 16px;
        text-align: center;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover { 
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); 
        transform: translateY(-4px); 
        border-color: #cbd5e1;
    }
    .stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1; margin-bottom: 6px; letter-spacing: -0.02em; }
    .stat-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--cbt-text-muted); font-weight: 700; }

    .search-input {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 16px 10px 40px;
        font-size: 0.875rem;
        width: 100%;
        max-width: 320px;
        transition: all 0.2s ease;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 14px center;
    }
    .search-input:focus { 
        outline: none; 
        background: white;
        border-color: var(--cbt-primary); 
        box-shadow: 0 0 0 4px var(--cbt-primary-light); 
    }

    .table-scroll-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 75vh;
        position: relative;
    }
    .table-scroll-wrapper::-webkit-scrollbar { height: 8px; width: 6px; }
    .table-scroll-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .table-scroll-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }

    .empty-state {
        text-align: center;
        padding: 6rem 2rem;
        color: var(--cbt-text-muted);
    }
    .empty-icon {
        font-size: 4.5rem;
        color: #e2e8f0;
        margin-bottom: 1rem;
    }
    .empty-state h4 {
        color: var(--cbt-text);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(4px);
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>


<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-gradient p-2 rounded-3 me-3 shadow-sm">
                        <i class="bi bi-person-check text-white fs-4"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-dark"><?= esc($judul ?? 'Halaman') ?></h3>
                        <span class="text-muted small"><?= esc($subjudul ?? 'Data') ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 text-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none text-muted">Home</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid">
        <div class="card filter-card fade-in-up">
            <div class="card-body p-4">
                <div class="row g-4 align-items-end">
                    <!-- Esai Notification Banner - will be shown dynamically if there are ungraded essays -->
                    <div class="col-12">
                        <div id="esai-notification" class="alert alert-warning border-0 shadow-sm d-none rounded-4 p-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-20 p-2 rounded-3 me-3">
                                    <i class="bi bi-pencil-square text-warning fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Perlu Koreksi Manual!</h6>
                                    <p class="mb-0 small text-dark opacity-75">
                                        Terdapat <strong><span id="esai-ungraded">0</span> siswa</strong> yang belum dikoreksi jawaban esainya.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Pilih Kelas</label>
                        <?= form_dropdown('kelas', $kelas, $kelas_selected, 'id="kelas" class="form-control select2"') ?>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Pilih Jadwal Ujian</label>
                        <?= form_dropdown('jadwal', $jadwal ?? [], $jadwal_selected, 'id="jadwal" class="form-control select2"') ?>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <button type="button" class="btn btn-light rounded-3 px-3 shadow-sm border fw-bold text-muted"
                                onclick="refreshStatus()" data-bs-toggle="tooltip" title="Refresh Data">
                                <i class="bi bi-arrow-repeat me-1"></i> Refresh
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-success rounded-3 px-3 shadow-sm fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4">
                                    <li><button id="download-nilai" class="dropdown-item py-2" type="button"><i class="bi bi-table me-2 text-success"></i>Format Nilai</button></li>
                                    <li><button id="download-detail" class="dropdown-item py-2" type="button"><i class="bi bi-list-check me-2 text-primary"></i>Format Detail</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-top">
                    <div class="row align-items-center">
                        <div class="col-md-6 col-lg-7">
                            <div class="d-flex flex-wrap gap-2" id="group-btn">
                                <button type="button" id="mark-all" class="btn btn-outline-success border-2 rounded-3 fw-bold btn-sm">
                                    <i class="bi bi-check-all me-1"></i> Mark All
                                </button>
                                <button type="button" id="import-essai" class="btn btn-outline-danger border-2 rounded-3 fw-bold btn-sm">
                                    <i class="bi bi-input-cursor-text me-1"></i> Input Nilai
                                </button>
                                <button type="button" class="btn btn-outline-primary border-2 rounded-3 fw-bold btn-sm" id="btn-katrol">
                                    <i class="bi bi-sliders me-1"></i> Katrol
                                </button>
                                <button type="button" class="btn btn-outline-secondary border-2 rounded-3 fw-bold btn-sm" id="btn-stats" onclick="showScoringStats()">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Statistik
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-5 text-md-end mt-3 mt-md-0">
                            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                <button type="button" class="btn btn-danger btn-sm px-3" id="set-fouls" data-bs-toggle="modal" data-bs-target="#penguranganModal">
                                    <i class="bi bi-scissors me-1"></i> Set Fouls
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm px-2" id="reset-fouls" onclick="resetFouls()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm ms-2" id="btn-recalculate" onclick="recalculateAllScores()">
                                <i class="bi bi-calculator me-1"></i> Hitung Ulang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Stats Panel -->
        <div id="stats-panel" class="stats-panel fade-in-up d-none"></div>

        <div class="table-results-container fade-in-up mb-5">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom" id="table-toolbar" style="display:none!important">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-muted small" id="table-count"></span>
                </div>
                <input type="text" class="search-input" id="search-siswa" placeholder="Cari nama siswa...">
            </div>
            <div class="table-scroll-wrapper">
                <div id="table-container">
                    <div class="empty-state">
                        <div class="empty-icon fade-in-up">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                        </div>
                        <h4 class="fade-in-up" style="animation-delay: 0.1s">Belum Ada Data Ditampilkan</h4>
                        <p class="fade-in-up" style="animation-delay: 0.2s">Silakan pilih <b>Kelas</b> dan <b>Jadwal Ujian</b> terlebih dahulu pada panel filter di atas untuk melihat nilai siswa.</p>
                    </div>
                </div>
            </div>
            <div class="overlay d-none" id="loading">
                <div class="d-flex flex-column align-items-center justify-content-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span class="mt-2 fw-bold text-muted">Memproses data...</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="perbaikanModal" tabindex="-1" role="dialog" aria-labelledby="perbaikanModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="perbaikanModalLabel">Perbaikan Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <form id="perbaikan-nilai">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label for="nama_sesi" class="col-md-4 col-form-label">Nilai Tertinggi</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="ya" value="100"
                                placeholder="Nilai tertinggi yang diinginkan" required>
                            <small>diisi nilai puluhan maksimal 100, misal 80 sampai 100</small>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="kode_sesi" class="col-md-4 col-form-label">Nilai Terrendah</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="yb" value="60"
                                placeholder="Nilai terrendah yang diinginkan" required>
                            <small>diisi nilai puluhan dibawah nilai tertinggi, misal 60</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="convert">Katrol <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="penguranganModal" tabindex="-1" role="dialog" aria-labelledby="penguranganLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xs" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penguranganLabel">Atur Pengurangan Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <p>Pengurangan Point akan diterapkan ke seluruh siswa yang melakukan pelanggaran (Pengurangan Point
                    X
                    jumlah pelanggaran).
                    tetapi masih bisa merubahnya di masing-masing siswa</p>
                <table class="table w-100 table-bordered">
                    <tr class="bg-success">
                        <th class="align-middle">Jenis Pelanggaran</th>
                        <th>Pengurangan Point</th>
                    </tr>
                    <tr class="alert alert-default-info">
                        <td class="align-middle">Pelanggaran Ringan</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm" value="0" step="0.5" min="0"
                                id="p-ringan" style="max-width: 80px">
                        </td>
                    </tr>
                    <tr class="alert alert-default-warning">
                        <td class="align-middle">Pelanggaran Sedang</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm" value="0" step="0.5" min="0"
                                id="p-sedang" style="max-width: 80px">
                        </td>
                    </tr>
                    <tr class="alert alert-default-danger">
                        <td class="align-middle">Pelanggaran Berat</td>
                        <td class="align-middle">
                            <input type="number" class="form-control form-control-sm" value="0" step="0.5" min="0"
                                id="p-berat" style="max-width: 80px">
                        </td>
                    </tr>
                </table>
                <p class="alert alert-default-success">
                    Pengaturan ini tidak akan diterapkan ke siswa yang sudah diatur secara manual.
                </p>
                <div class="icheck-cyan">
                    <input type="checkbox" id="reset-all" name="reset-all">
                    <label for="reset-all">Terapkan juga ke siswa yang sudah diatur manual</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary" id="save-min">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pelanggaranModal" tabindex="-1" role="dialog" aria-labelledby="pelanggaranLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xs" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pelanggaranLabel">Pelanggaran Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <table class="table w-100 table-bordered">
                    <tr class="bg-light">
                        <th class="align-middle">Jenis Pelanggaran</th>
                        <th>Jml. Pelanggaran x Pengurangan Nilai</th>
                    </tr>
                    <tr>
                        <td>
                            Pelanggaran Ringan
                            <ul>
                                <li>
                                    Memulai ujian dengan tampilan tidak fullscreen
                                </li>
                            </ul>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-row align-items-center mb-2">
                                <span id="jml-ringan" class="text-nowrap text-bold"></span><span
                                    class="ms-1 text-bold">x</span>
                                <input type="number" class="form-control form-control-sm mx-2" value="" step="0.5"
                                    min="0" id="np-ringan" style="max-width: 80px">
                            </div>
                            <span class="me-1">dikurangi</span>
                            <span id="t-ringan" class="text-danger text-bold">0</span> point
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Pelanggaran Sedang
                            <ul>
                                <li>
                                    Mencoba keluar dari fullscreen
                                </li>
                            </ul>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-row align-items-center mb-2">
                                <span id="jml-sedang" class="text-nowrap text-bold"></span><span
                                    class="ms-1 text-bold">x</span>
                                <input type="number" class="form-control form-control-sm mx-2" value="" step="0.5"
                                    min="0" id="np-sedang" style="max-width: 80px">
                            </div>
                            <span class="me-1">dikurangi</span>
                            <span id="t-sedang" class="text-danger text-bold">0</span> point
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Pelanggaran Berat
                            <ul>
                                <li>
                                    Mencoba membuka tab baru
                                </li>
                                <li>
                                    Keluar dari aplikasi sebelum selesai
                                </li>
                                <li>
                                    Me-minimize browser
                                </li>
                            </ul>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-row align-items-center mb-2">
                                <span id="jml-berat" class="text-nowrap text-bold"></span><span
                                    class="ms-1 text-bold">x</span>
                                <input type="number" class="form-control form-control-sm mx-2" value="" step="0.5"
                                    min="0" id="np-berat" style="max-width: 80px">
                            </div>
                            <span class="me-1">dikurangi</span>
                            <span id="t-berat" class="text-danger text-bold">0</span> point
                        </td>
                    </tr>
                    <tr class="bg-gray-light">
                        <td colspan="2">
                            <div>
                                Nilai Siswa: <span id="min-nilai" class="text-bold"></span>
                            </div>
                            <div>
                                Pengurangan Point:
                                <span class="text-bold text-danger" id="total-pengurangan"></span>
                            </div>
                            <div>
                                Nilai siswa setelah pengurangan:
                                <span class="text-bold text-danger" id="hasil-pengurangan"></span>
                            </div>
                        </td>
                    </tr>
                </table>
                <div>
                    <small> Kode id siswa pada database
                        <a class="alert-default-danger">[
                            <span id="min-idsiswa"></span>
                            ]</a>
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="save-fouls">Simpan</button>
            </div>
        </div>
    </div>
</div>

<form id="post_data">
    <?= csrf_field() ?>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/tableToExcel.js"></script>

<script>
    console.log('Data Nilai JS Loaded v1.2');
    const limitAntrian = 500
    const limitPost = 20
    const isPanitia = "<?= session()->get('role') == '4' ?>" === "1";

    // Default widths: No, Nama, Sesi, Waktu, Durasi, [PG: B,S,Skor], PK, JO, IS, ES, UR, CL, [Eval: Asli, Katrol, Foul, Status]
    var colWidthMin = '5,40,15,12,10,6,6,8,8,8,8,8,8,8,8,8,8,15';
    var colWidth = '5,40,15,12,10,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5'; // for detail format


    var idJadwal = <?= json_encode($jadwal_selected ?? '') ?>;
    var idKelas = <?= json_encode($kelas_selected ?? '') ?>;
    var isSelected = <?= json_encode($kelas_selected ?? 0) ?>;
    const isRekap = <?= isset($is_rekap) ? 1 : 0 ?>;
    const schoolName = <?= json_encode($setting->nama_sekolah ?? '') ?>;
    const academicYear = <?= json_encode($tp_active->tahun ?? '') ?>;
    const semesterName = <?= json_encode($smt_active->smt ?? '') ?>;
    var kodeMapel = '';
    var namaMapel = '';
    var jenisUjian = '';
    let arr_idSiswa = [];
    let arr_pelanggaran = [];
    let isBackFrom = false;

    let nilai_max = 0;//nilai siswa terbesar
    let nilai_min = 100;//nilai siswa terkecil
    let hasil_max = 100;//batas nilai terbesar
    let hasil_min = 60;//batas nilai terkecil
    let arrNilaiSiswa = []
    let isConverted = false
    let stopAutoKatrol = false
    let result;
    let activeNonPgCount = 0; // for excel export colspan

    async function ajaxGet(url) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    type: "GET",
                    success: function (respon) {
                        resolve(respon); // Resolusi Promise ketika sukses
                    },
                    error: function (xhr, status, error) {
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 100)
        });
    }

    async function ajaxPost(url, formData) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    method: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (respon) {
                        if (typeof respon === 'string') {
                            try {
                                respon = JSON.parse(respon);
                            } catch (e) {
                                reject("Invalid JSON response: " + respon);
                                return;
                            }
                        }
                        if (respon && respon.success) {
                            resolve(respon);
                        } else {
                            reject(respon ? respon.message : 'Unknown error from server');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", xhr.responseText);
                        reject(xhr.statusText || error || 'Network Error');
                    }
                });
            }, 500)
        });
    }

    function refreshStatus() {
        const jadwal = isRekap ? idJadwal : $('#jadwal').val();
        const kelas = isRekap ? idKelas : $('#kelas').val()
        console.log('refresh', jadwal, kelas);
        if (jadwal && kelas) {
            let post = {
                'id_jadwal': jadwal,
                'id_kelas': kelas
            }
            getNilai(post)
        }
    }

    function lihatJawaban(idSiswa) {
        let post = {
            'id_jadwal': idJadwal,
            'id_kelas': isRekap ? idKelas : $("#kelas").val()
        }
        localStorage.setItem('garudaCBT.convert', JSON.stringify(post))
        localStorage.setItem('garudaCBT.last_url', 'input_nilai')
        if (isRekap) {
            window.location.href = base_url + 'cbtrekap/koreksi?id_siswa=' + idSiswa + '&id_jadwal=' + idJadwal;
        } else {
            window.location.href = base_url + 'cbtnilai/koreksi?id_siswa=' + idSiswa + '&id_jadwal=' + idJadwal;
        }
    }

    function getDetailJadwal(idJadwal) {
        $.ajax({
            type: "GET",
            url: base_url + "cbtstatus/getjadwalujianbyjadwal?id_jadwal=" + idJadwal,
            cache: false,
            success: function (response) {
                console.log(response);
                var selKelas = $('#kelas');
                selKelas.html('');
                selKelas.append('<option value="">Pilih Kelas</option>');
                $.each(response, function (k, v) {
                    if (v != null) {
                        selKelas.append('<option value="' + k + '">' + v + '</option>');
                    }
                });
            }
        });
    }

    function getKelasJadwal(idKelas) {
        $.ajax({
            type: "GET",
            url: base_url + "cbtstatus/getjadwalujianbykelas?id_kelas=" + idKelas,
            cache: false,
            success: function (response) {
                console.log(response);

                var selJadwal = $('#jadwal');
                selJadwal.html('');
                selJadwal.append('<option value="">Pilih Jadwal</option>');
                $.each(response, function (k, v) {
                    if (v != null) {
                        selJadwal.append('<option value="' + k + '">' + v + '</option>');
                    }
                });
                if (isBackFrom) {
                    selJadwal.val(idJadwal).trigger('change')
                    isBackFrom = false;
                }
            }
        });
    }

    function downloadExcel(detail) {
        $('#no-soal-tile').attr('data-exclude', '' + !detail);
        $('.no-soal').attr('data-exclude', '' + !detail);
        $('.no-soal-siswa').attr('data-exclude', '' + !detail);

        const info = result?.jadwal?.bank;
        const pgCount = info ? (parseInt(info.tampil_pg) || 0) : 0;
        const baseCols = 5 + (pgCount > 0 ? 3 : 1) + activeNonPgCount + 4 + 1;
        const totalExportCols = detail ? (baseCols + pgCount) : baseCols;

        var table = document.querySelector("#table-status");
        if (table != null) {
            // Update colspan for professional header
            const headerCells = table.querySelectorAll('thead tr.d-none th[colspan]');
            headerCells.forEach(th => {
                const currentCls = parseInt(th.getAttribute('colspan'));
                if (currentCls > 1) { // Avoid meta labels which have colspan="2"
                    if (th.getAttribute('data-meta-label') === 'true') {
                        // Skip meta labels
                    } else {
                        th.setAttribute('colspan', totalExportCols);
                    }
                }
            });

            // Adjust specific meta-value colspans
            const metaValues = table.querySelectorAll('thead tr.d-none th.meta-value');
            metaValues.forEach(th => th.setAttribute('colspan', totalExportCols - 2));

            // Trick: Kosongkan No Peserta sementara agar tidak terbaca oleh Excel
            const badges = document.querySelectorAll('.student-badge');
            const originalContents = Array.from(badges).map(b => b.innerHTML);
            badges.forEach(b => b.innerHTML = '');

            var width = $('#table-status').attr('data-cols-width');
            $('#table-status').attr('data-cols-width', detail ? colWidth : colWidthMin)

            const nilaiDetail = detail ? 'Nilai Detail' : 'Nilai';
            TableToExcel.convert(table, {
                name: `${nilaiDetail} ${jenisUjian} ${$("#kelas option:selected").text()} ${namaMapel}.xlsx`,
                sheet: {
                    name: "Sheet 1"
                }
            });

            // Kembalikan No Peserta ke tampilan dashboard
            badges.forEach((b, i) => b.innerHTML = originalContents[i]);
        } else {
            Swal.fire({
                title: "ERROR",
                text: "Isi JADWAL dan KELAS terlebih dulu",
                icon: "error"
            })
        }
    }

    async function getNilai(post) {
        isConverted = false;
        $('#group-btn').addClass('d-none');
        if (!isRekap && (!post || !post.id_jadwal)) {
            $('#loading').addClass('d-none');
            return;
        }
        document.getElementById('table-container').innerHTML = '';
        $('#loading').removeClass('d-none');
        stopAutoKatrol = false;
        let formData = new FormData($('#post_data')[0]);

        if (post) {
            for (const key in post) {
                formData.append(key, post[key]);
            }
        } else {
            formData.append(`id_jadwal`, idJadwal);
            formData.append(`id_kelas`, idKelas);
        }

        try {
            if (isRekap) {
                const response = await ajaxPost('cbtrekap/get_nilai_rekap', formData)
                console.log('responseRekapSiswa', response);

                idKelas = response.kelas_selected
                idJadwal = response.jadwal_selected
                result = {
                    jadwal: {
                        ...response.rekap.jadwal,
                        bank: response.rekap.bank_soal,
                        sesisiswa: response.rekap.rekapsiswa,
                        soal_pg: response.rekap.soal_pg
                    },
                    nama_mapel: response.rekap.nama_mapel,
                    kelas: response.kelas,
                    nama_guru: response.rekap.nama_guru,
                    kode_jenis: response.rekap.kode_jenis,
                    durasi_siswa: response.durasi_siswa
                }
            } else {
                const response = await ajaxPost('cbtnilai/get_nilai_siswa', formData)
                console.log('responseCbtSiswa', response);

                idKelas = response.kelas_selected
                idJadwal = response.jadwal_selected
                result = response
            }
            await populateTable()
        } catch (e) {
            console.error("Error in getNilai:", e);
            $('#loading').addClass('d-none');
            $('#group-btn').removeClass('d-none'); // Show buttons even on error
            let sms = typeof e === 'string' ? e : (e.message ?? "Terjadi kesalahan sistem");
            Swal.fire({
                title: "Gagal Memuat Nilai",
                text: sms,
                icon: "error"
            });
        }
    }

    async function populateTable() {
        if (!result || !result.jadwal) {
            throw new Error("Data Jadwal tidak ditemukan");
        }
        if (!isConverted) {
            nilai_max = -Infinity;
            nilai_min = Infinity;
            if (result.jadwal.katrol_max != null && result.jadwal.katrol_min != null) {
                hasil_max = parseInt(result.jadwal.katrol_max);
                hasil_min = parseInt(result.jadwal.katrol_min);
                if ($('#perbaikanModal').length) {
                    $('#perbaikanModal input[name="ya"]').val(hasil_max);
                    $('#perbaikanModal input[name="yb"]').val(hasil_min);
                }
            }
        }
        $('#set-fouls').attr('disabled', result.jadwal?.pelanggaran != '1');
        $('#reset-fouls').attr('disabled', result.jadwal?.pelanggaran != '1');
        
        const siswas = result.jadwal.sesisiswa;
        const info = result.jadwal.bank;
        const kelas = result.kelas;
        namaMapel = info?.nama_mapel ?? info?.mapel?.nama_mapel ?? result.nama_mapel ?? '-';
        jenisUjian = result.jadwal.jenis?.kode_jenis ?? result.kode_jenis ?? '-';
        arr_pelanggaran = [];
        
        if (Array.isArray(siswas) && siswas.length > 0) {
            siswas.sort((a, b) => (a?.no_peserta ?? a?.peserta?.nomor_peserta ?? '').localeCompare(b?.no_peserta ?? b?.peserta?.nomor_peserta ?? '', undefined, { numeric: true }));

            // All 7 question types - always shown
            const allTypes = [
                {id: 'pg', key: 'skor_pg', label: 'Pilihan Ganda', code: 'PG', tampil: parseInt(info.tampil_pg) || 0, color: '#4f46e5'},
                {id: 'pk', key: 'skor_kompleks', label: 'PG Kompleks', code: 'PK', tampil: parseInt(info.tampil_kompleks) || 0, color: '#7c3aed'},
                {id: 'jo', key: 'skor_jodohkan', label: 'Menjodohkan', code: 'JO', tampil: parseInt(info.tampil_jodohkan) || 0, color: '#06b6d4'},
                {id: 'is', key: 'skor_isian', label: 'Isian Singkat', code: 'IS', tampil: parseInt(info.tampil_isian) || 0, color: '#f59e0b'},
                {id: 'es', key: 'skor_essai', label: 'Esai', code: 'ES', tampil: parseInt(info.tampil_esai) || 0, color: '#ef4444'},
                {id: 'ur', key: 'skor_urutan', label: 'Mengurutkan', code: 'UR', tampil: parseInt(info.tampil_urutan) || 0, color: '#10b981'},
                {id: 'cl', key: 'skor_cloze', label: 'Isian Rumpang', code: 'CL', tampil: parseInt(info.tampil_cloze) || 0, color: '#ec4899'}
            ];
            const nonPgTypes = allTypes.filter(t => t.id !== 'pg');
            const activeNonPgTypes = nonPgTypes.filter(t => t.tampil > 0);
            activeNonPgCount = activeNonPgTypes.length;

            // Compute dynamic column structure so tableToExcel doesn't crash
            // Total visible cols (excl. Aksi which has data-exclude=true)
            const pgMinCols = info.tampil_pg > 0 ? 3 : 1; // B,S,Skor or PG dummy
            const totalExcelCols = 5 + pgMinCols + activeNonPgTypes.length + 4 + 1; // 5 base + PG section + nonPG + 4 eval + Remed
            const _bW = [5, 40, 15, 12, 10]; // No, Peserta, Sesi, Waktu, Durasi
            const _pgMinW = info.tampil_pg > 0 ? [6, 6, 8] : [8]; // B,S,Skor or PG-dummy
            const _pgDetW = info.tampil_pg > 0 ? [...Array(parseInt(info.tampil_pg)).fill(5), 6, 6, 8] : [8];
            const _nonPgW = activeNonPgTypes.map(() => 8);
            const _tailW  = [8, 8, 8, 8, 8]; // Asli, Katrol, Foul, Status, Remed
            colWidthMin = [..._bW, ..._pgMinW, ..._nonPgW, ..._tailW].join(',');
            colWidth    = [..._bW, ..._pgDetW, ..._nonPgW, ..._tailW].join(',');

            const subjectTitle = (namaMapel && namaMapel !== '-') ? namaMapel.toUpperCase() : (result?.nama_mapel?.toUpperCase() || 'MATA PELAJARAN');
            const classTitle = $("#kelas option:selected").text().toUpperCase() || '-';
            const teacherTitle = result?.nama_guru || result?.jadwal?.nama_guru || '-';
            const examTitle = (jenisUjian && jenisUjian !== '-') ? jenisUjian.toUpperCase() : 'UJIAN';

            let html = `
                <table class="table-results" id="table-status" data-f-name="Arial" data-f-sz="10">
                    <thead>
                        <!-- Header Excel Only (Professional layout) -->
                        <tr class="d-none" data-exclude="false">
                            <th colspan="${totalExcelCols}" data-f-bold="true" data-f-sz="16" data-a-h="center" data-a-v="middle">${schoolName.toUpperCase()}</th>
                        </tr>
                        <tr class="d-none" data-exclude="false">
                            <th colspan="${totalExcelCols}" data-f-bold="true" data-f-sz="14" data-a-h="center" data-a-v="middle">LAPORAN HASIL UJIAN (${examTitle})</th>
                        </tr>
                        <tr class="d-none" data-exclude="false">
                            <th colspan="${totalExcelCols}" data-a-h="center" data-a-v="middle">Tahun Pelajaran: ${academicYear} | Semester: ${semesterName}</th>
                        </tr>
                        <tr class="d-none" data-exclude="false"><th></th></tr>
                        <tr class="d-none" data-exclude="false">
                            <th colspan="2" data-f-bold="true" data-a-h="left" data-meta-label="true">MATA PELAJARAN</th>
                            <th colspan="${totalExcelCols - 2}" data-a-h="left" class="meta-value">: ${subjectTitle}</th>
                        </tr>
                        <tr class="d-none" data-exclude="false">
                            <th colspan="2" data-f-bold="true" data-a-h="left" data-meta-label="true">KELAS</th>
                            <th colspan="${totalExcelCols - 2}" data-a-h="left" class="meta-value">: ${classTitle}</th>
                        </tr>
                        <tr class="d-none" data-exclude="false">
                            <th colspan="2" data-f-bold="true" data-a-h="left" data-meta-label="true">GURU PENGAMPU</th>
                            <th colspan="${totalExcelCols - 2}" data-a-h="left" class="meta-value">: ${teacherTitle}</th>
                        </tr>
                        <tr class="d-none" data-exclude="false">
                            <th colspan="2" data-f-bold="true" data-a-h="left" data-meta-label="true">TANGGAL CETAK</th>
                            <th colspan="${totalExcelCols - 2}" data-a-h="left" class="meta-value">: ${new Date().toLocaleString('id-ID')}</th>
                        </tr>
                        <tr class="d-none" data-exclude="false"><th></th></tr>
                        
                        <tr>
                            <th rowspan="2" width="50" data-f-bold="true" data-a-h="center" data-a-v="middle" data-fill-color="4F46E5" data-f-color="FFFFFF" data-b-a-s="thin">No</th>
                            <th rowspan="2" style="text-align: left; min-width: 250px;" data-f-bold="true" data-a-h="left" data-a-v="middle" data-fill-color="4F46E5" data-f-color="FFFFFF" data-b-a-s="thin">Peserta / Identitas</th>
                            <th rowspan="2" width="120" data-f-bold="true" data-a-h="center" data-a-v="middle" data-fill-color="4F46E5" data-f-color="FFFFFF" data-b-a-s="thin">Sesi & Ruang</th>
                            <th rowspan="2" width="100" data-f-bold="true" data-a-h="center" data-a-v="middle" data-fill-color="4F46E5" data-f-color="FFFFFF" data-b-a-s="thin">Waktu</th>
                            <th rowspan="2" width="100" data-f-bold="true" data-a-h="center" data-a-v="middle" data-fill-color="4F46E5" data-f-color="FFFFFF" data-b-a-s="thin">Durasi</th>
                            ${info.tampil_pg > 0 ? `
                                <th id="no-soal-tile" colspan="${info.tampil_pg}" class="d-none" data-fill-color="F8FAF6" data-f-bold="true" data-a-h="center" data-b-a-s="thin">Nomor Soal PG</th>
                                <th colspan="3" class="header-group-indigo" data-fill-color="C7D2FE" data-f-bold="true" data-a-h="center" data-b-a-s="thin">Pilihan Ganda</th>` : `
                                <th rowspan="2" width="60" class="opacity-25" data-f-bold="true" data-a-h="center" data-a-v="middle" data-fill-color="F1F5F9" data-b-a-s="thin" title="Pilihan Ganda (tidak aktif)">PG</th>`}
                            ${activeNonPgTypes.length > 0 ? `<th colspan="${activeNonPgTypes.length}" class="header-group-cyan" data-fill-color="A5F3FC" data-f-bold="true" data-a-h="center" data-b-a-s="thin">Skor per Jenis Soal</th>` : ''}
                            <th colspan="4" class="header-group-slate" data-fill-color="CBD5E1" data-f-bold="true" data-a-h="center" data-b-a-s="thin">Evaluasi Nilai</th>
                            <th rowspan="2" width="80" data-f-bold="true" data-a-h="center" data-a-v="middle" data-fill-color="4F46E5" data-f-color="FFFFFF" data-b-a-s="thin">Remed</th>
                            <th rowspan="2" width="180" data-exclude="true">Aksi</th>
                        </tr>
                        <tr>
                            ${info.tampil_pg > 0 ? `
                                ${[...Array(parseInt(info.tampil_pg))].map((_, i) => `<th class="no-soal d-none" data-fill-color="F8FAF6" data-f-bold="true" data-b-a-s="thin" data-a-h="center" width="40">${i+1}</th>`).join('')}
                                <th width="45" class="text-success" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="F8FAF6">B</th>
                                <th width="45" class="text-danger" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="F8FAF6">S</th>
                                <th width="55" class="text-primary" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="C7D2FE">Skor</th>
                            ` : ''}
                            ${activeNonPgTypes.map(t => `<th width="55" title="${t.label} (${t.tampil} soal)" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="E0F2FE" style="color:${t.color};font-weight:800">${t.code}</th>`).join('')}
                            <th width="65" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="F1F5F9">Asli</th>
                            <th width="65" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="F1F5F9">Katrol</th>
                            <th width="60" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="F1F5F9">Foul</th>
                            <th width="60" data-f-bold="true" data-a-h="center" data-b-a-s="thin" data-fill-color="F1F5F9">Status</th>
                        </tr>
                    </thead>
                    <tbody>`;

            const hanyaPg = info.tampil_pg > 0 && nonPgTypes.every(t => t.tampil === 0);

            arr_idSiswa = [];
            let totalSkor = 0, countHadir = 0, maxSkor = -Infinity, minSkor = Infinity;
            let countKoreksi = 0, countBelumKoreksi = 0;
            let no = 1;
            siswas.forEach((siswa) => {
                let idSiswa = siswa?.siswa?.id_siswa || siswa.id_siswa;
                arr_idSiswa.push({id_siswa: idSiswa, memulai: siswa?.durasi?.mulai ? '1' : '2'});
                let sudah_hadir = siswa?.durasi?.mulai || false;
                let disable = (!sudah_hadir || siswa?.durasi?.mulai_ujian?.includes('-')) ?? true;
                let disableKoreksi = disable || siswa?.durasi?.selesai === '';
                let disabled = disableKoreksi ? 'disabled' : '';
                
                const pel = siswa?.pelanggaran;
                if (pel) {
                    arr_pelanggaran.push({
                        'id_siswa': idSiswa,
                        'id_cbt_siswa': siswa.id_cbt_siswa || '',
                        'id_rekap_siswa': siswa.id_rekap_siswa || '',
                        'pelanggaran': pel
                    });
                }

                const totalMinus = result?.jadwal?.pelanggaran === '1' ? (parseFloat(pel?.t_total) || 0) : 0;
                const skorAsli = parseFloat(siswa?.nilai_akhir?.skor_total) || 0;
                let skorTotal = skorAsli - totalMinus;

                let skorKatrol = siswa?.nilai_akhir?.nilai_katrol ?? 0;
                if (isConverted) {
                    let rangeActual = nilai_max - nilai_min;
                    let rangeTarget = hasil_max - hasil_min;
                    let skorAsliNum = parseFloat(decimalFixed(skorTotal));
                    if (siswa?.durasi?.status_ujian && !isNaN(skorAsliNum)) {
                        skorKatrol = rangeActual <= 0 ? hasil_max : (hasil_min + (((skorAsliNum - nilai_min) / rangeActual) * rangeTarget));
                        // Katrol score should be rounded to integer as requested by user
                        skorKatrol = Math.round(skorKatrol);
                        if (skorKatrol < skorTotal) skorKatrol = Math.round(skorTotal);
                    } else {
                        skorKatrol = isNaN(skorTotal) ? 0 : Math.round(skorTotal);
                    }
                } else {
                    let st = parseFloat(decimalFixed(skorTotal));
                    if (!isNaN(st)) {
                        if (st > nilai_max) nilai_max = st;
                        if (st < nilai_min) nilai_min = st;
                    }
                }

                // Track stats
                if (sudah_hadir) {
                    const currentSkor = isConverted ? parseFloat(skorKatrol) : parseFloat(skorAsli);
                    if (!isNaN(currentSkor)) {
                        totalSkor += currentSkor;
                        countHadir++;
                        if (currentSkor > maxSkor) maxSkor = currentSkor;
                        if (currentSkor < minSkor) minSkor = currentSkor;
                    }
                    if (siswa?.nilai_akhir?.dikoreksi === '1') countKoreksi++;
                    else countBelumKoreksi++;
                }

                const startTimeSt = siswa?.durasi?.mulai ? new Date(siswa.durasi.mulai) : null;
                const isSusulan = startTimeSt && (startTimeSt.toISOString().split('T')[0].replace(/-/g, '') > result?.jadwal?.tgl_mulai?.replace(/-/g, ''));

                let benar_pg = 0;
                let salah_pg = 0;
                let jawaban_pg = siswa?.nilai_akhir?.skor_pg?.detail ?? [];
                
                let pgDetailsHtml = '';
                if (info.tampil_pg > 0) {
                    result?.jadwal?.soal_pg?.forEach((soal) => {
                        const num = soal.nomor_soal;
                        const jwb = jawaban_pg.find(j => j.nomor === num);
                        let bg = '';
                        if (jwb) {
                            if (jwb?.benar) { bg = 'data-fill-color="00E676"'; benar_pg++; }
                            else { bg = 'data-fill-color="FF7043"'; salah_pg++; }
                        }
                        pgDetailsHtml += `<td class="no-soal-siswa d-none" data-a-v="middle" data-a-h="center" data-b-a-s="thin" ${bg}>${jwb?.jawaban_siswa ?? ''}</td>`;
                    });
                }

                const iconKoreksi = hanyaPg || siswa?.nilai_akhir?.dikoreksi === '1' 
                    ? `<i title="Sudah dikoreksi" class="bi bi-check-circle-fill text-success ms-1"></i>` 
                    : `<i title="Belum dikoreksi" class="bi bi-exclamation-circle-fill text-warning ms-1"></i>`;

                // Compute score section colspan for 'Belum Ada Data Nilai' merged cell
                const scoreColSpan = (info.tampil_pg > 0 ? 3 : 1) + activeNonPgTypes.length + 4;

                html += `
                <tr style="${!sudah_hadir ? 'background:rgba(241,245,249,0.5);' : ''}">
                    <td class="text-center font-monospace opacity-50" data-a-h="center" data-b-a-s="thin">${no}</td>
                    <td data-b-a-s="thin">
                        <div class="student-card">
                            <div class="student-name">${siswa?.siswa?.nama ?? siswa.nama_siswa}</div>
                            <div class="student-badge" data-exclude="true">${siswa?.no_peserta ?? siswa?.peserta?.nomor_peserta ?? '-'}</div>
                        </div>
                    </td>
                    <td class="text-center" data-a-h="center" data-b-a-s="thin">
                        <span class="pill-session mb-1 d-inline-block">${siswa?.sesi?.kode_sesi ?? '-'}</span><br>
                        <span class="pill-room">${siswa?.ruang?.kode_ruang ?? '-'}</span>
                    </td>
                    <td class="text-center" data-a-h="center" data-b-a-s="thin">
                        <div class="time-badge">
                            ${startTimeSt ? `${padZero(startTimeSt.getHours())}:${padZero(startTimeSt.getMinutes())}` : '- : -'}
                        </div>
                    </td>
                    <td class="text-center" data-a-h="center" data-b-a-s="thin">
                        ${siswa?.durasi?.lama_ujian
                            ? `<span class="badge bg-secondary rounded-pill" style="font-size:10px;letter-spacing:0.5px">
                                <i class="bi bi-stopwatch me-1" style="font-size:9px"></i>${siswa.durasi.lama_ujian}
                               </span>`
                            : `<span class="text-muted" style="font-size:11px">-</span>`}
                    </td>
                    ${pgDetailsHtml}
                    ${!sudah_hadir
                        ? `<td colspan="${scoreColSpan}"
                               class="text-center fst-italic"
                               data-a-h="center" data-b-a-s="thin" data-fill-color="FEF08A"
                               style="color:#1e293b;background-color:#fef08a;border-left:3px dashed #e2e8f0;font-weight:bold;">
                               <i class="bi bi-hourglass me-2" style="opacity:.6"></i>Belum Ada Data Nilai
                           </td>`
                        : `
                        ${info.tampil_pg > 0 ? `
                            <td class="text-center text-success fw-bold" data-a-h="center" data-b-a-s="thin" data-t="n">${benar_pg}</td>
                            <td class="text-center text-danger fw-bold" data-a-h="center" data-b-a-s="thin" data-t="n">${salah_pg}</td>
                            <td class="text-center text-primary fw-bold" data-a-h="center" data-b-a-s="thin" data-t="n">${decimalFixed(parseFloat(siswa?.nilai_akhir?.skor_pg?.skor_total) || 0)}</td>
                        ` : `<td class="text-center opacity-25" data-a-h="center" data-b-a-s="thin">-</td>`}
                        ${activeNonPgTypes.map(t => {
                            let score = parseFloat(siswa?.nilai_akhir?.[t.key]?.skor_total);
                            if (isNaN(score)) score = 0;
                            return `<td class="text-center fw-bold" data-a-h="center" data-b-a-s="thin" data-t="n" style="color:${t.color}">${decimalFixed(score)}</td>`;
                        }).join('')}
                        <td class="text-center" data-a-h="center" data-b-a-s="thin" data-t="n"><span class="score-badge score-asli">${decimalFixed(skorAsli)}</span></td>
                        <td class="text-center" data-a-h="center" data-b-a-s="thin" data-t="n"><span class="score-badge score-katrol">${decimalFixed(skorKatrol)}</span></td>
                        <td class="text-center" data-a-h="center" data-b-a-s="thin" data-t="n"><span class="score-badge score-foul">${totalMinus > 0 ? `-${totalMinus}` : '0'}</span></td>
                        <td class="text-center" data-a-h="center" data-b-a-s="thin">
                            ${(() => {
                                const st = siswa?.durasi?.status_ujian || '';
                                if (!st) return `<span class="text-muted">-</span>`;
                                const cfg = st === 'Selesai'      ? ['success','check-circle-fill']
                                          : st === 'Mengerjakan'  ? ['primary','arrow-repeat']
                                          : st === 'Reset'        ? ['warning','exclamation-triangle-fill']
                                          : ['secondary','dash-circle'];
                                return `<span class="badge bg-${cfg[0]}-subtle text-${cfg[0]} rounded-pill px-2" style="font-size:10px">
                                            <i class="bi bi-${cfg[1]} me-1"></i>${st}
                                        </span>`;
                            })()} 
                        </td>`
                    }
                    <td class="text-center" data-a-h="center" data-b-a-s="thin" data-fill-color="E0E7FF" data-t="n" data-f-bold="true">
                        ${siswa?.durasi?.remedial_attempt || 0}
                    </td>
                    <td class="text-center" data-exclude="true">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn-cbt btn-cbt-success ${disabled}" onclick="lihatJawaban(${idSiswa})" ${disabled}>
                                <i class="bi bi-pencil-square"></i> Koreksi ${iconKoreksi}
                            </button>
                            <button type="button" class="btn-cbt btn-cbt-info btn-cbt-icon ${disabled}" onclick="showDetailedScore(${idSiswa}, '${(siswa?.siswa?.nama ?? siswa.nama_siswa).replace(/'/g, "\\'")}')" ${disabled} title="Detail Skor">
                                <i class="bi bi-bar-chart-fill"></i>
                            </button>
                            <button type="button" class="btn-cbt btn-cbt-danger btn-cbt-icon" data-bs-toggle="modal" data-nilai="${skorAsli}" data-bs-target="#pelanggaranModal" data-siswa="${idSiswa}" ${result.jadwal?.pelanggaran !== '1' ? 'disabled' : ''} title="Set Fouls Siswa">
                                <i class="bi bi-scissors"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
                no++;
            });

            if (siswas.length === 0) {
                html += `<tr>
                    <td colspan="${totalExcelCols}" class="text-center py-5 text-muted" style="background:rgba(248,250,252,0.6)">
                        <div class="empty-icon opacity-50 mb-3" style="font-size:3rem">
                            <i class="bi bi-people"></i>
                        </div>
                        <h6 class="fw-bold text-slate-600 mb-1">Tidak Ada Peserta Ujian</h6>
                        <p class="small mb-0">Belum ada data siswa untuk kelas dan jadwal yang dipilih.</p>
                    </td>
                </tr>`;
            }

            html += `</tbody></table>`;
            document.getElementById('table-container').innerHTML = html;

            // Update stats panel
            const avgSkor = countHadir > 0 ? (totalSkor / countHadir) : 0;
            if (maxSkor === -Infinity) maxSkor = 0;
            if (minSkor === Infinity) minSkor = 0;
            const statsPanel = document.getElementById('stats-panel');
            statsPanel.innerHTML = `
                <div class="stat-card"><div class="stat-value text-primary">${siswas.length}</div><div class="stat-label">Total Siswa</div></div>
                <div class="stat-card"><div class="stat-value text-success">${countHadir}</div><div class="stat-label">Sudah Ujian</div></div>
                <div class="stat-card"><div class="stat-value" style="color:var(--cbt-info)">${decimalFixed(avgSkor)}</div><div class="stat-label">Rata-rata</div></div>
                <div class="stat-card"><div class="stat-value text-success">${decimalFixed(maxSkor)}</div><div class="stat-label">Tertinggi</div></div>
                <div class="stat-card"><div class="stat-value text-danger">${decimalFixed(minSkor)}</div><div class="stat-label">Terendah</div></div>
                <div class="stat-card"><div class="stat-value text-success">${countKoreksi}</div><div class="stat-label">Dikoreksi</div></div>
                <div class="stat-card"><div class="stat-value text-warning">${countBelumKoreksi}</div><div class="stat-label">Blm Koreksi</div></div>
            `;
            statsPanel.classList.remove('d-none');

            // Show toolbar
            const toolbar = document.getElementById('table-toolbar');
            if (toolbar) {
                toolbar.style.display = '';
                toolbar.removeAttribute('style');
                document.getElementById('table-count').textContent = `${siswas.length} siswa`;
            }

            // Search filter
            const searchInput = document.getElementById('search-siswa');
            if (searchInput) {
                searchInput.value = '';
                searchInput.oninput = function() {
                    const q = this.value.toLowerCase();
                    document.querySelectorAll('#table-status tbody tr').forEach(row => {
                        const name = row.querySelector('.student-name')?.textContent?.toLowerCase() || '';
                        const badge = row.querySelector('.student-badge')?.textContent?.toLowerCase() || '';
                        row.style.display = (name.includes(q) || badge.includes(q)) ? '' : 'none';
                    });
                };
            }
            
            if (isConverted) {
                $('#btn-katrol').html('<i class="bi bi-arrow-counterclockwise me-1"></i> Nilai Asli').removeClass('btn-outline-primary').addClass('btn-primary')
            } else {
                $('#btn-katrol').html('<i class="bi bi-sliders me-1"></i> Katrol').removeClass('btn-primary').addClass('btn-outline-primary')
            }

            if (isPanitia) {
                // ... logic to hide buttons for panitia ...
                $('#mark-all, #import-essai, #btn-katrol, #set-fouls, #reset-fouls, #btn-recalculate').addClass('d-none');
                $('#table-status th[data-exclude="true"], #table-status td[data-exclude="true"]').addClass('d-none');
            }

            // Esai notification logic
            const esaiCount = parseInt(info.tampil_esai) || 0;
            const esaiNotif = document.getElementById('esai-notification');
            if (esaiCount > 0 && esaiNotif) {
                let ungradedCount = 0;
                siswas.forEach(s => { if (s?.durasi?.mulai && s?.nilai_akhir?.dikoreksi !== '1') ungradedCount++; });
                if (ungradedCount > 0) {
                    document.getElementById('esai-ungraded').textContent = ungradedCount;
                    esaiNotif.classList.remove('d-none');
                } else {
                    esaiNotif.classList.add('d-none');
                }
            } else if (esaiNotif) {
                esaiNotif.classList.add('d-none');
            }
        } else {
            document.getElementById('table-container').innerHTML = '<div class="text-center py-5"><i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i><h5 class="text-muted">Tidak ada data siswa untuk kriteria ini</h5><p class="text-muted small">Silakan pilih kelas dan jadwal ujian</p></div>';
            document.getElementById('stats-panel')?.classList.add('d-none');
            const toolbar = document.getElementById('table-toolbar');
            if (toolbar) toolbar.style.display = 'none';
        }
        $('#loading').addClass('d-none');
        $('#group-btn').removeClass('d-none');
    }

    function perbaikiNilai(fromModal) {
        if (!isConverted && !fromModal) {
            // Check if all scores are corrected (especially for exams with essays)
            let ungradedCount = 0;
            if (result && result.jadwal && result.jadwal.sesisiswa) {
                result.jadwal.sesisiswa.forEach(s => {
                    // Only count students who have actually started the exam
                    if (s?.durasi?.mulai && (s?.nilai_akhir?.dikoreksi == '0' || s?.nilai_akhir?.dikoreksi == 0 || !s?.nilai_akhir?.dikoreksi)) {
                        ungradedCount++;
                    }
                });
            }

            if (ungradedCount > 0) {
                Swal.fire({
                    title: "Koreksi Belum Selesai",
                    text: "Terdapat " + ungradedCount + " siswa yang belum selesai dikoreksi. Pastikan semua nilai sudah dikoreksi (termasuk soal esai) sebelum menggunakan fitur Katrol agar hasil perhitungan akurat.",
                    icon: "warning",
                    confirmButtonColor: "#4f46e5"
                });
                return;
            }

            $('#perbaikanModal').modal('show')
            return
        }
        isConverted = !isConverted;
        stopAutoKatrol = !isConverted;
        populateTable()
    }

    function decimalFixed(num) {
        return Math.round(num * 100) / 100
    }

    $(document).ready(function () {
        ajaxcsrf();

        const loading = $('#loading');
        var opsiJadwal = $("#jadwal");
        var opsiKelas = $("#kelas");
        var selected = isSelected === 0 ? "selected='selected'" : "";
        opsiJadwal.prepend("<option value='' " + selected + " disabled>Pilih Jadwal</option>");
        opsiKelas.prepend("<option value='' " + selected + " disabled>Pilih Kelas</option>");

        opsiKelas.change(function () {
            getKelasJadwal($(this).val());
        });

        opsiJadwal.change(function () {
            let post = {
                'id_jadwal': isRekap ? idJadwal : $(this).val(),
                'id_kelas': isRekap ? idKelas : opsiKelas.val()
            }
            getNilai(post)
        });

        $('#btn-katrol').on('click', function (e) {
            perbaikiNilai(false)
        });

        $("#download-nilai").click(function (event) {
            downloadExcel(false)
        });

        $("#download-detail").click(function (event) {
            downloadExcel(true)
        });

        $('#perbaikan-nilai').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var table = document.querySelector("#table-status");
            var $inputs = $('#perbaikanModal :input');
            var values = {};
            $inputs.each(function () {
                if ($(this).val()) values[this.name] = $(this).val();
            });
            hasil_max = Number(values['ya']);
            hasil_min = Number(values['yb']);

            let katrolData = new FormData($('#perbaikan-nilai')[0]);
            katrolData.append('id_jadwal', isRekap ? idJadwal : $("#jadwal").val());
            katrolData.append('id_kelas', isRekap ? idKelas : $("#kelas").val());
            katrolData.append('min', hasil_min);
            katrolData.append('max', hasil_max);

            $.ajax({
                url: base_url + "cbtnilai/save_katrol",
                type: "POST",
                data: katrolData,
                processData: false,
                contentType: false,
                success: function (d) {
                    if (d.success) {
                        if (typeof showToast === 'function') {
                            showToast('success', d.message);
                        } else {
                            Swal.fire("Berhasil", d.message, "success");
                        }
                    } else {
                        Swal.fire("Gagal", d.message, "error");
                    }
                }
            });

            $('#perbaikanModal').modal('hide').data('bs.modal', null);
            $('#perbaikanModal').on('hidden', function () {
                $(this).data('modal', null);  // destroys modal
            });

            perbaikiNilai(true)

        });

        $("#import-essai").on("click", function () {
            var kls = opsiKelas.val();
            var jad = opsiJadwal.val();
            if (!isRekap && (kls = null || jad == null)) {
                return;
            }
            let post = {
                'id_jadwal': isRekap ? idJadwal : $("#jadwal").val(),
                'id_kelas': isRekap ? idKelas : $("#kelas").val()
            }
            localStorage.setItem('garudaCBT.convert', JSON.stringify(post))
            localStorage.setItem('garudaCBT.last_url', 'input_nilai')
            if (isRekap) {
                window.location.href = base_url + 'cbtrekap/inputessai?id_kelas=' + idKelas + '&id_jadwal=' + idJadwal;
            } else {
                window.location.href = base_url + 'cbtnilai/inputessai?id_kelas=' + opsiKelas.val() + '&id_jadwal=' + opsiJadwal.val();
            }
        });

        $('#mark-all').on('click', function () {
            Swal.fire({
                title: "Tandai Semua",
                text: "Semua hasil siswa akan ditandai sudah dikoreksi",
                icon: "info",
                showCancelButton: true,
            }).then(async (result) => {
                if (result.value) {
                    let dataPost = new FormData($('#post_data')[0]);

                    if (post) {
                        for (const key in post) {
                            formData.append(key, post[key]);
                        }
                    } else {
                        formData.append(`id_jadwal`, idJadwal);
                        formData.append(`id_kelas`, idKelas);
                    }

                    try {
                        if (isRekap) {
                            const response = await ajaxPost('cbtrekap/get_nilai_rekap', formData)
                            console.log('responseRekapSiswa', response);

                            idKelas = response.kelas_selected
                            idJadwal = response.jadwal_selected
                            result = {
                                jadwal: {
                                    ...response.rekap.jadwal,
                                    bank: response.rekap.bank_soal,
                                    sesisiswa: response.rekap.rekapsiswa,
                                    soal_pg: response.rekap.soal_pg
                                },
                                nama_mapel: response.rekap.nama_mapel,
                                kelas: response.kelas,
                                nama_guru: response.rekap.nama_guru,
                                kode_jenis: response.rekap.kode_jenis,
                                durasi_siswa: response.durasi_siswa
                            }
                        } else {
                            const response = await ajaxPost('cbtnilai/get_nilai_siswa', formData)
                            console.log('responseCbtSiswa', response);

                            idKelas = response.kelas_selected
                            idJadwal = response.jadwal_selected
                            result = response
                        }
                        await populateTable()
                    } catch (e) {
                        console.error("Error in getNilai:", e);
                        $('#loading').addClass('d-none');
                        $('#group-btn').removeClass('d-none'); // Show buttons even on error
                        let sms = typeof e === 'string' ? e : (e.message ?? "Terjadi kesalahan sistem");
                        Swal.fire({
                            title: "Gagal Memuat Nilai",
                            text: sms,
                            icon: "error"
                        });
                    }
                }
            });
        })

        opsiKelas.select2({ width: '100%', theme: 'bootstrap4' });
        opsiJadwal.select2({ width: '100%', theme: 'bootstrap4' });

        let post;
        const lastUrl = localStorage.getItem('garudaCBT.last_url', '')
        if (lastUrl === 'input_nilai') {
            isBackFrom = true;
            post = localStorage.getItem('garudaCBT.convert', '')
            if (post) {
                localStorage.setItem('garudaCBT.last_url', '')
                post = JSON.parse(post)
                idJadwal = post.id_jadwal;
                idKelas = post.id_kelas;
                isSelected = idKelas;
                if (!isRekap) {
                    opsiKelas.val(idKelas).trigger('change')
                    //opsiJadwal.val(idJadwal)
                } else {
                    getNilai()
                }
            }
        } else {
            getNilai()
        }

        // set all
        $('#penguranganModal').on('show.bs.modal', function (e) {
            let temp = localStorage.getItem('garudaCBT.pelanggaran', null)
            if (temp) {
                temp = JSON.parse(temp);
                $(e.currentTarget).find('#p-ringan').val(temp.p_ringan);
                $(e.currentTarget).find('#p-sedang').val(temp.p_sedang);
                $(e.currentTarget).find('#p-berat').val(temp.p_berat);
            }
        });

        $('#save-min').click(function () {
            const pel = {
                'p_ringan': parseFloat($('#p-ringan').val()) || 0,
                'p_sedang': parseFloat($('#p-sedang').val()) || 0,
                'p_berat': parseFloat($('#p-berat').val()) || 0
            }
            localStorage.setItem('garudaCBT.pelanggaran', JSON.stringify(pel));
            $('#penguranganModal').modal('hide');
            const isReset = $('#reset-all').is(":checked");
            if (!result) {
                Swal.fire({
                    title: "Tidak ada data",
                    text: 'Silahkan pilih jadwal dan kelas terlebih dahulu',
                    icon: "warning"
                });
                return
            }
            savePengurangan(isReset)
        })
        // set per-siswa
        $('#pelanggaranModal').on('show.bs.modal', function (e) {
            const idSiswa = $(e.relatedTarget).data('siswa');
            const nilai = $(e.relatedTarget).data('nilai');
            const siswa = arr_pelanggaran.find(p => p.id_siswa == idSiswa);

            const ringan = siswa?.pelanggaran?.ringan || 0;
            const sedang = siswa?.pelanggaran?.sedang || 0;
            const berat = siswa?.pelanggaran?.berat || 0;

            const p_ringan = siswa?.pelanggaran?.p_ringan || 0;
            const p_sedang = siswa?.pelanggaran?.p_sedang || 0;
            const p_berat = siswa?.pelanggaran?.p_berat || 0;

            let temp = localStorage.getItem('garudaCBT.pelanggaran', null)
            if (temp) {
                temp = JSON.parse(temp);
            }

            const manual = siswa?.pelanggaran?.manual === 'ya';
            $(e.currentTarget).find('#np-ringan').val(manual ? p_ringan : (temp?.p_ringan || 0));
            $(e.currentTarget).find('#np-sedang').val(manual ? p_sedang : (temp?.p_sedang || 0));
            $(e.currentTarget).find('#np-berat').val(manual ? p_berat : (temp?.p_berat || 0));

            $(e.currentTarget).find('#jml-ringan').text(ringan);
            $(e.currentTarget).find('#jml-sedang').text(sedang);
            $(e.currentTarget).find('#jml-berat').text(berat);

            $(e.currentTarget).find('#min-idsiswa').text(idSiswa);
            $(e.currentTarget).find('#min-nilai').text(nilai);

            $('#np-ringan, #np-sedang, #np-berat').trigger('input')
        });

        $('#save-fouls').click(function () {
            const totalRingan = parseFloat($('#t-ringan').text()) || 0;
            const totalSedang = parseFloat($('#t-sedang').text()) || 0;
            const totalBerat = parseFloat($('#t-berat').text()) || 0;
            const totalMinus = totalRingan + totalSedang + totalBerat;

            const pel = {
                'p_ringan': parseFloat($('#np-ringan').val()),
                'p_sedang': parseFloat($('#np-sedang').val()),
                'p_berat': parseFloat($('#np-berat').val()),
                'manual': 'ya',
                't_ringan': totalRingan,
                't_sedang': totalSedang,
                't_berat': totalBerat,
                't_total': totalMinus
            }
            $('#pelanggaranModal').modal('hide');
            savePengurangan(false, $('#min-idsiswa').text(), pel)
        })

        $('#np-ringan, #np-sedang, #np-berat').on('input', hitungTotalPengurangan);
    })

    function resetFouls() {
        const pel = {
            'p_ringan': 0,
            'p_sedang': 0,
            'p_berat': 0
        }

        Swal.fire({
            title: "Reset Pelanggaran",
            text: "Semua Point penguranagan akan dihapus, nilai siswa akan dikembalikan ke nilai awal",
            icon: "info",
            showCancelButton: true,
        }).then(result => {
            if (result.value) {
                localStorage.setItem('garudaCBT.pelanggaran', JSON.stringify(pel));
                savePengurangan(true)
            }
        })
    }

    function savePengurangan(isReset, idSiswa, pel) {
        let arrSiswa = [];
        if (idSiswa) {
            let siswa;
            if (isRekap) {
                siswa = arr_pelanggaran.find(p => p.id_siswa === idSiswa && p.id_rekap_siswa !== "");
            } else {
                siswa = arr_pelanggaran.find(p => p.id_siswa === idSiswa && p.id_cbt_siswa !== "");
            }
            if (siswa) {
                const objBaru = {
                    ...siswa,
                    pelanggaran: {
                        ...pel,
                        berat: siswa.pelanggaran.berat,
                        sedang: siswa.pelanggaran.sedang,
                        ringan: siswa.pelanggaran.ringan
                    }
                };
                arrSiswa.push(objBaru);
            }
        } else {
            let temp = JSON.parse(localStorage.getItem('garudaCBT.pelanggaran', '{}'))

            arrSiswa = arr_pelanggaran.filter(p => (isReset && !p.manual) && p.id_cbt_siswa !== "");
            arrSiswa.forEach(p => {
                const totalRingan = (p.pelanggaran.ringan || 0) * temp.p_ringan;
                const totalSedang = (p.pelanggaran.sedang || 0) * temp.p_sedang;
                const totalBerat = (p.pelanggaran.berat || 0) * temp.p_berat;
                const totalMinus = totalRingan + totalSedang + totalBerat;

                p.pelanggaran['t_ringan'] = totalRingan;
                p.pelanggaran['t_sedang'] = totalSedang;
                p.pelanggaran['t_berat'] = totalBerat;
                p.pelanggaran['t_total'] = totalMinus;
                p.pelanggaran['p_ringan'] = temp.p_ringan;
                p.pelanggaran['p_sedang'] = temp.p_sedang;
                p.pelanggaran['p_berat'] = temp.p_berat;
                p.pelanggaran['manual'] = 'no'
            });
        }

        if (arrSiswa.length === 0) {
            Swal.fire({
                title: "Gagal",
                text: 'Tidak ada siswa yang dipilih',
                icon: "warning"
            });
            return;
        }

        let formData = new FormData($('#post_data')[0]);
        formData.append('arr_point', JSON.stringify(arrSiswa));
        formData.append('id_jadwal', idJadwal)
        formData.append('id_siswa', JSON.stringify(arr_idSiswa))

        $.ajax({
            url: base_url + "cbtnilai/save_pengurangan",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success) {
                    refreshStatus();
                    Swal.fire({
                        title: "Berhasil",
                        text: "Pengurangan nilai berhasil disimpan",
                        icon: "success"
                    });
                } else {
                    Swal.fire({
                        title: "NOT CHANGED",
                        text: 'Nilai yang disimpan sama seperti sebelumnya',
                        icon: "warning"
                    });
                }
            },
            error: function (xhr, status, error) {
                const err = JSON.parse(xhr.responseText)
                Swal.fire({
                    title: "Error",
                    text: err.Message,
                    icon: "error"
                });
            }
        });
    }

    const hitungTotalPengurangan = function () {
        const nilRingan = parseFloat($('#np-ringan').val()) || 0;
        const jmlRingan = parseFloat($('#jml-ringan').text()) || 0;
        const totalRingan = nilRingan * jmlRingan;
        $('#t-ringan').text(totalRingan);

        const nilSedang = parseFloat($('#np-sedang').val()) || 0;
        const jmlSedang = parseFloat($('#jml-sedang').text()) || 0;
        const totalSedang = nilSedang * jmlSedang;
        $('#t-sedang').text(totalSedang);

        const nilBerat = parseFloat($('#np-berat').val()) || 0;
        const jmlBerat = parseFloat($('#jml-berat').text()) || 0;
        const totalBerat = nilBerat * jmlBerat;
        $('#t-berat').text(totalBerat);

        const totalMinus = totalRingan + totalSedang + totalBerat;

        const nilai = parseFloat($('#min-nilai').text()) || 0;
        const totalNilai = nilai - totalMinus;

        $('#total-pengurangan').text(totalMinus);
        $('#hasil-pengurangan').text(totalNilai);
    };

    const padZero = (no) => no < 10 ? '0' + no : no;

    /**
     * Recalculate all scores for the current jadwal
     */
    function recalculateAllScores() {
        const jadwalId = document.getElementById('jadwal')?.value;
        if (!jadwalId) {
            Swal.fire({
                title: "Error",
                text: "Silakan pilih jadwal terlebih dahulu",
                icon: "warning"
            });
            return;
        }

        Swal.fire({
            title: 'Hitung Ulang Nilai?',
            text: 'Ini akan menghitung ulang nilai semua siswa yang sudah selesai ujian. Nilai esai yang sudah diinput manual tidak akan terpengaruh.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hitung Ulang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    html: 'Menghitung ulang nilai semua siswa',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '<?= site_url('cbtnilai/recalculateAll') ?>',
                    type: 'POST',
                    data: {
                        id_jadwal: jadwalId,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                html: `<p>${response.message}</p>
                                    <p class="text-success">Berhasil: ${response.success_count} siswa</p>
                                    ${response.fail_count > 0 ? `<p class="text-danger">Gagal: ${response.fail_count} siswa</p>` : ''}`,
                                icon: 'success'
                            }).then(() => {
                                refreshStatus(); // Reload the table
                            });
                        } else {
                            Swal.fire({
                                title: 'Peringatan',
                                text: response.message,
                                icon: 'warning'
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Gagal menghitung ulang nilai',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    /**
     * Show detailed score breakdown for a student
     */
    function showDetailedScore(idSiswa, namaSiswa) {
        const jadwalId = document.getElementById('jadwal')?.value;
        if (!jadwalId) return;

        Swal.fire({
            title: 'Memuat Detail Skor...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '<?= site_url('cbtnilai/getDetailedScore') ?>',
            type: 'POST',
            data: {
                id_siswa: idSiswa,
                id_jadwal: jadwalId,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    let breakdownHtml = '';
                    const colors = ['#4f46e5','#10b981','#06b6d4','#f59e0b','#ef4444','#8b5cf6','#ec4899'];

                    if (data.breakdown && data.breakdown.length > 0) {
                        breakdownHtml = '<div class="mt-3">';
                        data.breakdown.forEach((item, idx) => {
                            const pct = parseFloat(item.persentase) || 0;
                            const clr = colors[idx % colors.length];
                            breakdownHtml += `
                                <div class="mb-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #eef2f6">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold" style="font-size:0.85rem">${item.label}</span>
                                        <span class="fw-bold" style="color:${clr}">${parseFloat(item.nilai_berbobot).toFixed(1)}</span>
                                    </div>
                                    <div class="progress mb-1" style="height:6px;border-radius:3px;background:#f1f5f9">
                                        <div class="progress-bar" style="width:${pct}%;background:${clr};border-radius:3px;font-size:0.7rem;font-weight:700">${item.jumlah} siswa</div>
                                    </div>
                                    <div class="d-flex justify-content-between" style="font-size:0.7rem;color:#94a3b8">
                                        <span>Benar: ${parseFloat(item.skor_mentah).toFixed(1)} / ${item.total_soal}</span>
                                        <span>${pct}% · Bobot: ${item.bobot}</span>
                                    </div>
                                </div>`;
                        });
                        breakdownHtml += '</div>';
                    } else {
                        breakdownHtml = '<p class="text-muted text-center mt-3"><i class="bi bi-info-circle me-1"></i>Detail skor tidak tersedia</p>';
                    }

                    const nilaiAsli = parseFloat(data.nilai_asli || data.pg_nilai || 0);
                    const nilaiAkhir = parseFloat(data.pg_nilai || 0);

                    Swal.fire({
                        title: false,
                        html: `
                            <div class="text-start">
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;background:linear-gradient(135deg,#4f46e5,#7c3aed);flex-shrink:0">
                                        <i class="bi bi-bar-chart-fill text-white fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">Detail Skor</h5>
                                        <small class="text-muted">${namaSiswa || 'Siswa'}</small>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 text-center" style="background:#f8fafc;border:1px solid #eef2f6">
                                            <div class="fw-bold" style="font-size:1.4rem;color:#1e293b">${nilaiAsli.toFixed(1)}</div>
                                            <small class="text-muted fw-bold" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em">Nilai Asli</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded-3 text-center" style="background:#ecfdf5;border:1px solid #bbf7d0">
                                            <div class="fw-bold text-success" style="font-size:1.4rem">${nilaiAkhir.toFixed(1)}</div>
                                            <small class="text-muted fw-bold" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.05em">Nilai Akhir</small>
                                        </div>
                                    </div>
                                </div>
                                ${breakdownHtml}
                            </div>
                        `,
                        width: '520px',
                        confirmButtonText: '<i class="bi bi-x-lg me-1"></i> Tutup',
                        customClass: {
                            htmlContainer: 'text-start',
                            confirmButton: 'btn btn-sm btn-secondary rounded-3 px-4'
                        },
                        buttonsStyling: false
                    });
                } else {
                    Swal.fire({
                        title: 'Info',
                        text: response.message || 'Data tidak ditemukan',
                        icon: 'info'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: 'Error',
                    text: 'Gagal memuat detail skor',
                    icon: 'error'
                });
            }
        });
    }

    /**
     * Show scoring statistics modal
     */
    function showScoringStats() {
        const jadwalId = document.getElementById('jadwal')?.value;
        if (!jadwalId) {
            Swal.fire({
                title: "Error",
                text: "Silakan pilih jadwal terlebih dahulu",
                icon: "warning"
            });
            return;
        }

        Swal.fire({
            title: 'Memuat Statistik...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '<?= site_url('cbtnilai/getScoringStats') ?>',
            type: 'POST',
            data: {
                id_jadwal: jadwalId,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    const summary = data.summary || {};
                    const distribution = data.distribution || [];
                    const total = parseInt(summary.total_siswa) || 1;
                    const lulusCount = parseInt(summary.lulus) || 0;
                    const tidakLulus = parseInt(summary.tidak_lulus) || 0;
                    const pctLulus = ((lulusCount / total) * 100).toFixed(0);
                    
                    let distHtml = '';
                    if (distribution.length > 0) {
                        distHtml = '<div class="mt-3 pt-3 border-top">';
                        distHtml += '<h6 class="fw-bold mb-3" style="font-size:0.85rem"><i class="bi bi-bar-chart-steps me-1"></i>Distribusi Grade</h6>';
                        const maxJml = Math.max(...distribution.map(d => d.jumlah || 0), 1);
                        distribution.forEach(item => {
                            const w = ((item.jumlah / maxJml) * 100).toFixed(0);
                            distHtml += `
                                <div class="d-flex align-items-center mb-2">
                                    <span class="fw-bold me-2" style="min-width:50px;font-size:0.8rem">${item.grade}</span>
                                    <div class="flex-grow-1 me-2">
                                        <div class="progress" style="height:20px;border-radius:6px;background:#f1f5f9">
                                            <div class="progress-bar" style="width:${w}%;background:linear-gradient(90deg,#4f46e5,#7c3aed);border-radius:6px;font-size:0.7rem;font-weight:700">${item.jumlah} siswa</div>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        distHtml += '</div>';
                    }
                    
                    Swal.fire({
                        title: false,
                        html: `
                            <div class="text-start">
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;background:linear-gradient(135deg,#10b981,#059669);flex-shrink:0">
                                        <i class="bi bi-graph-up-arrow text-white fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0">Statistik Nilai</h5>
                                        <small class="text-muted">${total} siswa · ${pctLulus}% lulus</small>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <div class="p-3 rounded-3 text-center" style="background:#eef2ff;border:1px solid #c7d2fe">
                                            <div class="fw-bold" style="font-size:1.3rem;color:#4f46e5">${parseFloat(summary.rata_rata || 0).toFixed(1)}</div>
                                            <small class="fw-bold text-muted" style="font-size:0.65rem;text-transform:uppercase">Rata-rata</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-3 rounded-3 text-center" style="background:#ecfdf5;border:1px solid #bbf7d0">
                                            <div class="fw-bold text-success" style="font-size:1.3rem">${parseFloat(summary.nilai_tertinggi || 0).toFixed(1)}</div>
                                            <small class="fw-bold text-muted" style="font-size:0.65rem;text-transform:uppercase">Tertinggi</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-3 rounded-3 text-center" style="background:#fef2f2;border:1px solid #fecaca">
                                            <div class="fw-bold text-danger" style="font-size:1.3rem">${parseFloat(summary.nilai_terendah || 0).toFixed(1)}</div>
                                            <small class="fw-bold text-muted" style="font-size:0.65rem;text-transform:uppercase">Terendah</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center p-2 rounded-3" style="background:#ecfdf5">
                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                            <div><span class="fw-bold text-success">${lulusCount}</span> <small class="text-muted">Lulus (≥75)</small></div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center p-2 rounded-3" style="background:#fef2f2">
                                            <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                            <div><span class="fw-bold text-danger">${tidakLulus}</span> <small class="text-muted">Tidak Lulus</small></div>
                                        </div>
                                    </div>
                                </div>
                                ${distHtml}
                            </div>
                        `,
                        width: '520px',
                        confirmButtonText: '<i class="bi bi-x-lg me-1"></i> Tutup',
                        customClass: {
                            htmlContainer: 'text-start',
                            confirmButton: 'btn btn-sm btn-secondary rounded-3 px-4'
                        },
                        buttonsStyling: false
                    });
                } else {
                    Swal.fire({
                        title: 'Info',
                        text: response.message || 'Tidak ada data',
                        icon: 'info'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Gagal memuat statistik',
                    icon: 'error'
                });
            }
        });
    }

    console.log("EOF Data Nilai v2.0 - Professional Grade Management");
    // End of Script v2.0
</script>
<?= $this->endSection() ?>