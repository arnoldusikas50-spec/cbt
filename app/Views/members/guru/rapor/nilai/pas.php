<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsuites@5/dist/jsuites.min.css" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@5/dist/jspreadsheet.min.css" type="text/css" />
<style>
    /* ============================================
       DESIGN TOKENS - PAS PAGE
       ============================================ */
    :root {
        /* Primary Colors */
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-200: #c7d2fe;
        --color-primary-300: #a5b4fc;
        --color-primary-400: #818cf8;
        --color-primary-500: #6366f1;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;
        --color-primary-800: #3730a3;
        --color-primary-900: #312e81;

        /* Success Colors */
        --color-success-50: #f0fdf4;
        --color-success-100: #dcfce7;
        --color-success-500: #22c55e;
        --color-success-600: #16a34a;
        --color-success-700: #15803d;

        /* Danger/Warning/Info */
        --color-danger-50: #fef2f2;
        --color-danger-100: #fee2e2;
        --color-danger-500: #ef4444;
        --color-danger-600: #dc2626;
        --color-danger-700: #b91c1c;

        --color-warning-50: #fffbeb;
        --color-warning-100: #fef3c7;
        --color-warning-500: #f59e0b;
        --color-warning-600: #d97706;

        --color-info-50: #eff6ff;
        --color-info-100: #dbeafe;
        --color-info-500: #3b82f6;
        --color-info-600: #2563eb;

        /* Neutral Colors */
        --color-neutral-50: #f8fafc;
        --color-neutral-100: #f1f5f9;
        --color-neutral-200: #e2e8f0;
        --color-neutral-300: #cbd5e1;
        --color-neutral-400: #94a3b8;
        --color-neutral-500: #64748b;
        --color-neutral-600: #475569;
        --color-neutral-700: #334155;
        --color-neutral-800: #1e293b;
        --color-neutral-900: #0f172a;

        /* Typography */
        --font-family-base: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --font-size-base: 1rem;
        --font-size-lg: 1.125rem;
        --font-size-xl: 1.25rem;
        --font-size-2xl: 1.5rem;
        --font-size-3xl: 1.875rem;
        --font-weight-normal: 400;
        --font-weight-medium: 500;
        --font-weight-semibold: 600;
        --font-weight-bold: 700;
        --line-height-tight: 1.25;
        --line-height-normal: 1.5;
        --line-height-relaxed: 1.625;

        /* Spacing */
        --space-1: 0.25rem;
        --space-2: 0.5rem;
        --space-3: 0.75rem;
        --space-4: 1rem;
        --space-5: 1.25rem;
        --space-6: 1.5rem;
        --space-8: 2rem;
        --space-10: 2.5rem;
        --space-12: 3rem;

        /* Border Radius */
        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
        --radius-full: 9999px;

        /* Shadows */
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

        /* Transitions */
        --transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ============================================
       BASE STYLES
       ============================================ */
    .pas-container {
        font-family: var(--font-family-base);
        background-color: var(--color-neutral-50);
        min-height: 100vh;
    }

    /* ============================================
       CARD COMPONENT
       ============================================ */
    .pas-card {
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--space-6);
        overflow: hidden;
    }

    .pas-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--space-5) var(--space-6);
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: #ffffff;
        border-bottom: none;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        margin: 0;
        color: #ffffff;
    }

    .pas-card__title-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-lg);
    }

    .pas-card__actions {
        display: flex;
        gap: var(--space-3);
    }

    .pas-card__body {
        padding: var(--space-6);
    }

    /* ============================================
       BUTTON COMPONENTS
       ============================================ */
    .pas-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-5);
        font-family: var(--font-family-base);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        line-height: var(--line-height-tight);
        border: none;
        border-radius: var(--radius-lg);
        cursor: pointer;
        transition: all var(--transition-base);
        white-space: nowrap;
    }

    .pas-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
    }

    .pas-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .pas-btn--primary {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: #ffffff;
        box-shadow: var(--shadow-md);
    }

    .pas-btn--primary:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--color-primary-700) 0%, var(--color-primary-800) 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
    }

    .pas-btn--success {
        background: linear-gradient(135deg, var(--color-success-600) 0%, var(--color-success-700) 100%);
        color: #ffffff;
        box-shadow: var(--shadow-md);
    }

    .pas-btn--success:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--color-success-700) 0%, var(--color-success-600) 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-lg);
    }

    .pas-btn--danger {
        background: linear-gradient(135deg, var(--color-danger-500) 0%, var(--color-danger-600) 100%);
        color: #ffffff;
    }

    .pas-btn--danger:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--color-danger-600) 0%, var(--color-danger-700) 100%);
        transform: translateY(-1px);
    }

    .pas-btn--sm {
        padding: var(--space-2) var(--space-4);
        font-size: var(--font-size-xs);
    }

    /* ============================================
       ALERT COMPONENTS
       ============================================ */
    .pas-alert {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3);
        padding: var(--space-4);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-4);
    }

    .pas-alert__icon {
        flex-shrink: 0;
        font-size: var(--font-size-xl);
    }

    .pas-alert__content {
        flex: 1;
        font-size: var(--font-size-sm);
        line-height: var(--line-height-normal);
    }

    .pas-alert--danger {
        background-color: var(--color-danger-50);
        border: 1px solid var(--color-danger-100);
        color: var(--color-danger-700);
    }

    .pas-alert--warning {
        background-color: var(--color-warning-50);
        border: 1px solid var(--color-warning-100);
        color: var(--color-warning-600);
    }

    .pas-alert--info {
        background-color: var(--color-info-50);
        border: 1px solid var(--color-info-100);
        color: var(--color-info-700);
    }

    /* ============================================
       FORM COMPONENTS
       ============================================ */
    .pas-form-control {
        display: block;
        width: 100%;
        padding: var(--space-3) var(--space-4);
        font-size: var(--font-size-sm);
        line-height: var(--line-height-normal);
        color: var(--color-neutral-700);
        background-color: #ffffff;
        border: 1px solid var(--color-neutral-300);
        border-radius: var(--radius-lg);
        transition: all var(--transition-base);
    }

    .pas-form-control:focus {
        outline: none;
        border-color: var(--color-primary-500);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .pas-form-control.is-invalid {
        border-color: var(--color-danger-500);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
    }

    /* ============================================
       ACTION BAR
       ============================================ */
    .pas-action-bar {
        display: flex;
        align-items: center;
        gap: var(--space-4);
        padding: var(--space-5);
        background: var(--color-neutral-50);
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        margin-bottom: var(--space-6);
    }

    .pas-action-group {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        flex: 1;
    }

    /* ============================================
       KKM INFO CARDS
       ============================================ */
    .pas-kkm-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }

    @media (min-width: 768px) {
        .pas-kkm-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .pas-kkm-card {
        background: var(--color-neutral-50);
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        padding: var(--space-5);
        text-align: center;
        transition: all var(--transition-base);
    }

    .pas-kkm-card:hover {
        border-color: var(--color-primary-300);
        box-shadow: var(--shadow-md);
    }

    .pas-kkm-card__label {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-neutral-500);
        margin-bottom: var(--space-2);
    }

    .pas-kkm-card__value {
        font-size: var(--font-size-3xl);
        font-weight: var(--font-weight-bold);
        margin-bottom: var(--space-1);
    }

    .pas-kkm-card__value--primary { color: var(--color-primary-600); }
    .pas-kkm-card__value--success { color: var(--color-success-600); }

    .pas-kkm-card__meta {
        font-size: var(--font-size-xs);
        color: var(--color-neutral-500);
    }

    .pas-kkm-card__badges {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: var(--space-2);
        margin-top: var(--space-3);
    }

    /* ============================================
       BADGES
       ============================================ */
    .pas-badge {
        display: inline-flex;
        align-items: center;
        padding: var(--space-2) var(--space-3);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        border-radius: var(--radius-full);
        white-space: nowrap;
    }

    .pas-badge--d { background: var(--color-danger-100); color: var(--color-danger-700); }
    .pas-badge--c { background: var(--color-warning-100); color: var(--color-warning-700); }
    .pas-badge--b { background: var(--color-info-100); color: var(--color-info-700); }
    .pas-badge--a { background: var(--color-success-100); color: var(--color-success-700); }

    /* ============================================
       SKELETON LOADING
       ============================================ */
    .pas-skeleton {
        background: linear-gradient(90deg, var(--color-neutral-200) 25%, var(--color-neutral-300) 50%, var(--color-neutral-200) 75%);
        background-size: 200% 100%;
        border-radius: var(--radius-md);
        animation: pas-skeleton-loading 1.5s ease-in-out infinite;
    }

    @keyframes pas-skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .pas-skeleton--text { height: 1rem; margin-bottom: var(--space-3); }
    .pas-skeleton--title { height: 1.5rem; width: 60%; margin-bottom: var(--space-4); }
    .pas-skeleton--card { height: 6rem; border-radius: var(--radius-xl); }

    /* ============================================
       EMPTY STATE
       ============================================ */
    .pas-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: var(--space-12) var(--space-6);
        text-align: center;
    }

    .pas-empty-state__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 5rem;
        height: 5rem;
        background: var(--color-neutral-100);
        color: var(--color-neutral-400);
        border-radius: 50%;
        margin-bottom: var(--space-6);
    }

    .pas-empty-state__title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--color-neutral-700);
        margin-bottom: var(--space-2);
    }

    .pas-empty-state__description {
        font-size: var(--font-size-sm);
        color: var(--color-neutral-500);
        max-width: 24rem;
    }

    /* ============================================
       STICKY SAVE BUTTON
       ============================================ */
    .pas-sticky-save {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1020;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-top: 1px solid var(--color-neutral-200);
        padding: var(--space-4);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: center;
        gap: var(--space-4);
    }

    .pas-sticky-save__btn {
        display: inline-flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-4) var(--space-8);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: #ffffff;
        border: none;
        border-radius: var(--radius-full);
        box-shadow: var(--shadow-xl);
        cursor: pointer;
        transition: all var(--transition-base);
    }

    .pas-sticky-save__btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 30px -5px rgb(0 0 0 / 0.15);
    }

    .pas-sticky-save__btn:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.3), var(--shadow-xl);
    }

    /* ============================================
       SPREADSHEET OVERRIDES
       ============================================ */
    .jexcel_container {
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-neutral-200);
        overflow: hidden;
        margin-bottom: var(--space-6);
    }

    .jexcel_content {
        background: #ffffff;
    }

    .jexcel > thead > tr > td {
        background-color: var(--color-neutral-100) !important;
        color: var(--color-neutral-700) !important;
        font-weight: var(--font-weight-semibold) !important;
        padding: var(--space-3) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 767px) {
        .pas-action-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .pas-action-group {
            flex-direction: column;
        }

        .pas-card__header {
            flex-direction: column;
            gap: var(--space-3);
            align-items: flex-start;
        }

        .pas-card__actions {
            width: 100%;
            justify-content: flex-end;
        }

        .jexcel_content table {
            font-size: var(--font-size-xs);
        }
    }

    /* ============================================
       ACCESSIBILITY IMPROVEMENTS
       ============================================ */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    .pas-btn:focus-visible,
    .pas-form-control:focus-visible,
    .pas-sticky-save__btn:focus-visible {
        outline: 2px solid var(--color-primary-500);
        outline-offset: 2px;
    }

    @media (prefers-contrast: high) {
        .pas-btn--primary { border: 2px solid #ffffff; }
        .pas-card { border: 2px solid var(--color-neutral-800); }
    }

    /* Screen reader only */
    .sr-only {
        position: absolute;
        left: -10000px;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }

    /* Legacy compatibility */
    .bg-soft-info { background-color: var(--color-info-100); color: var(--color-info-700); }
    .bg-soft-success { background-color: var(--color-success-100); color: var(--color-success-700); }
    <div class="container-fluid">
        <div class="pas-card" id="mainContent">
            <div class="pas-card__header">
                <h1 class="pas-card__title">
                    <span class="pas-card__title-icon" aria-hidden="true">
                        <i class="bi bi-journal-medical"></i>
                    </span>
                    <span>Input Nilai PAS: <strong><?= esc($mapel['nama_mapel']) ?></strong></span>
                </h1>
                <div class="pas-card__actions">
                    <button type="button" id="download-excel" class="pas-btn pas-btn--success pas-btn--sm"
                        aria-label="Ekspor data nilai ke format Excel">
                        <i class="bi bi-file-excel" aria-hidden="true"></i>
                        <span>Ekspor Excel</span>
                    </button>
                    <button onclick="window.history.back();" type="button" class="pas-btn pas-btn--danger pas-btn--sm"
                        aria-label="Kembali ke halaman sebelumnya">
                        <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
                        <span>Kembali</span>
                    </button>
                </div>
            </div>
            
            <div class="pas-card__body">
                <!-- Loading Skeleton -->
                <div id="loadingSkeletonMain" class="d-none">
                    <div class="pas-skeleton pas-skeleton--title"></div>
                    <div class="pas-skeleton pas-skeleton--text" style="width: 80%;"></div>
                    <div class="pas-skeleton pas-skeleton--text" style="width: 60%;"></div>
                </div>

                <!-- Error State -->
                <div id="errorStateMain" class="pas-alert pas-alert--danger d-none" role="alert" aria-live="polite">
                    <i class="bi bi-exclamation-triangle-fill pas-alert__icon" aria-hidden="true"></i>
                    <div class="pas-alert__content">
                        <strong>Error!</strong> <span id="errorMessageMain">Terjadi kesalahan saat memuat data.</span>
                    </div>
                </div>

                <?php if ($kkm == null): ?>
                    <div class="pas-alert pas-alert--danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill pas-alert__icon" aria-hidden="true"></i>
                        <div class="pas-alert__content">
                            Download template tidak tersedia, silakan isi <strong>KKM</strong> terlebih dahulu di menu 
                            <b>DATA RAPOR > KKM DAN BOBOT</b>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="pas-action-bar" id="actionBarContent">
                        <button type="button" class="pas-btn pas-btn--primary" 
                            onclick="processDownload(<?= $mapel['id_mapel'] ?>, <?= $kelas['id_kelas'] ?>)"
                            aria-label="Download template Excel untuk input nilai PAS">
                            <i class="bi bi-cloud-download" aria-hidden="true"></i>
                            <span>Download Template (.xlsx)</span>
                        </button>
                        
                        <form id="uploadfile" enctype="multipart/form-data" class="pas-action-group mb-0">
                            <?= csrf_field() ?>
                            <div class="flex-grow-1">
                                <input type="file" name="upload_file" class="pas-form-control" id="custom-file"
                                    aria-label="Pilih file Excel untuk upload nilai"
                                    accept=".xlsx,.xls">
                            </div>
                            <button id="upload" class="pas-btn pas-btn--success" type="submit"
                                aria-label="Upload file nilai PAS">
                                <i class="bi bi-cloud-upload" aria-hidden="true"></i>
                                <span>Upload</span>
                            </button>
                        </form>
                    </div>
                    
                    <?php
                    $multi = $setting_rapor->kkm_tunggal == "0" ? "Multi KKM" : "KKM Tunggal";
                    $isi = $kkm->kkm;
                    $dsd = $isi - 1;
                    $c = $isi;
                    $csd = floor($isi + (100 - $isi) / 3);
                    $b = $csd + 1;
                    $bsd = floor($b + (100 - $b) / 2);
                    $a = $bsd + 1;
                    ?>
                    
                    <!-- Loading Skeleton for KKM -->
                    <div id="loadingSkeletonKKM" class="d-none">
                        <div class="pas-kkm-grid">
                            <div class="pas-skeleton pas-skeleton--card"></div>
                            <div class="pas-skeleton pas-skeleton--card"></div>
                            <div class="pas-skeleton pas-skeleton--card"></div>
                        </div>
                    </div>

                    <div class="pas-kkm-grid" id="kkmContent">
                        <div class="pas-kkm-card">
                            <div class="pas-kkm-card__label">KKM Utama</div>
                            <div class="pas-kkm-card__value pas-kkm-card__value--primary"><?= $kkm->kkm ?></div>
                            <div class="pas-kkm-card__meta">Jenis: <?= $multi ?></div>
                        </div>
                        <div class="pas-kkm-card">
                            <div class="pas-kkm-card__label">Interval Predikat</div>
                            <div class="pas-kkm-card__badges">
                                <span class="pas-badge pas-badge--d">0-<?= $dsd ?> D</span>
                                <span class="pas-badge pas-badge--c"><?= $c ?>-<?= $csd ?> C</span>
                                <span class="pas-badge pas-badge--b"><?= $b ?>-<?= $bsd ?> B</span>
                                <span class="pas-badge pas-badge--a"><?= $a ?>-100 A</span>
                            </div>
                        </div>
                        <div class="pas-kkm-card">
                            <div class="pas-kkm-card__label">Bobot PAS</div>
                            <div class="pas-kkm-card__value pas-kkm-card__value--success"><?= $kkm->bobot_pas ?>%</div>
                            <div class="pas-kkm-card__meta">Kontribusi Ke Nilai Rapor</div>
                        </div>
                    </div>

                    <!-- Loading Skeleton for Table -->
                    <div id="loadingSkeletonTable" class="d-none">
                        <div class="pas-skeleton pas-skeleton--title"></div>
                        <div class="pas-skeleton pas-skeleton--card" style="height: 20rem;"></div>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyStateTable" class="pas-empty-state d-none" role="status" aria-live="polite">
                        <div class="pas-empty-state__icon" aria-hidden="true">
                            <i class="bi bi-inbox fs-1"></i>
                        </div>
                        <h3 class="pas-empty-state__title">Belum Ada Data Siswa</h3>
                        <p class="pas-empty-state__description">
                            Tidak ada data siswa untuk ditampilkan. Silakan tambahkan siswa ke kelas ini terlebih dahulu.
                        </p>
                    </div>

                    <div id="t-siswa" class="w-100 jspreadsheet-modern" role="region" aria-label="Tabel pengisian nilai PAS siswa"></div>
                    
                    <form id="uploadnilai">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_kelas" value="<?= $kelas['id_kelas'] ?>">
                        <input type="hidden" name="id_mapel" value="<?= $mapel['id_mapel'] ?>">
                        <div class="pas-sticky-save">
                            <button type="submit" class="pas-sticky-save__btn"
                                aria-label="Simpan semua nilai PAS siswa">
                                <i class="bi bi-save" aria-hidden="true"></i>
                                <span>SIMPAN NILAI</span>
                            </button>
                        </div>
                        <div id="for-upload" class="d-none row"></div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<form id="uploadpas">
    <?= csrf_field() ?>
    <input type="hidden" name="id_mapel" value="<?= $mapel['id_mapel'] ?>">
    <input type="hidden" name="id_kelas" value="<?= $kelas['id_kelas'] ?>">
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/exceljs.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/js-excel-template.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jsuites@5/dist/jsuites.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@5/dist/index.min.js"></script>
<script>
    window.guruKepsek = <?= json_encode([
        'nama' => $setting->kepsek ?? '',
        'nip' => $setting->nip ?? ''
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    window.guruMapelAktif = <?= json_encode([
        'nama' => $guru->nama_lengkap ?? $guru->nama_guru ?? '',
        'nip' => $guru->nip ?? ''
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    var arrSiswa = <?= json_encode($siswa) ?>;
    var arrNilai = <?= json_encode($nilai) ?>;
    var kkmGuru = <?= json_encode($kkm) ?>;
    var kkmSetting = <?= json_encode($setting_rapor) ?>;
    var idMapel = '<?= $mapel['id_mapel'] ?>';
    var idKelas = '<?= $kelas['id_kelas'] ?>';
    var namaMapel = '<?= $mapel['nama_mapel'] ?>';
    var namaKelas = '<?= $kelas['nama_kelas'] ?>';
    var tpActive = '<?= $tp_active->id_tp ?>';
    var smtActive = '<?= $smt_active->id_smt ?>';

    var isi;
    var bobotPH, bobotPTS, bobotPAS;
    if (kkmSetting && kkmSetting.kkm_tunggal == '1') {
        isi = parseInt(kkmSetting.kkm || 0);
        bobotPH = parseInt(kkmSetting.bobot_ph || 0);
        bobotPTS = parseInt(kkmSetting.bobot_pts || 0);
        bobotPAS = parseInt(kkmSetting.bobot_pas || 0);
    } else if (kkmGuru) {
        isi = parseInt(kkmGuru.kkm || 0);
        bobotPH = parseInt(kkmGuru.bobot_ph || 0);
        bobotPTS = parseInt(kkmGuru.bobot_pts || 0);
        bobotPAS = parseInt(kkmGuru.bobot_pas || 0);
    } else {
        isi = 0; bobotPH = 0; bobotPTS = 0; bobotPAS = 0;
    }

    var pre_d = 0;
    var pre_dsd = isi - 1;
    var pre_c = isi;
    var pre_csd = Math.floor(isi + (100 - isi) / 3);
    var pre_b = pre_csd + 1;
    var pre_bsd = Math.floor(pre_b + (100 - pre_b) / 2);
    var pre_a = pre_bsd + 1;
    var pre_asd = 100;

    $(function () {
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }
    });

    function calcNilaiAkhir(nhar, npts, npas) {
        var h = Number(nhar), t = Number(npts), a = Number(npas);
        if (isNaN(h) && isNaN(t) && isNaN(a)) return '';
        h = isNaN(h) ? 0 : h;
        t = isNaN(t) ? 0 : t;
        a = isNaN(a) ? 0 : a;
        return Math.round(((h * bobotPH) / 100) + ((t * bobotPTS) / 100) + ((a * bobotPAS) / 100));
    }

    function calcPredikat(val) {
        if (val === '' || val === null || val === undefined) return '';
        var n = Number(val);
        if (isNaN(n)) return '';
        if (n > pre_bsd) return 'A';
        if (n > pre_csd) return 'B';
        if (n > pre_dsd) return 'C';
        if (n < pre_c) return 'D';
        return '';
    }

    $(document).ready(function () {
        var dataSiswa = [];
        var row = 1;
        $.each(arrSiswa, function (i, v) {
            var noInduk = v.nisn == null || v.nisn == '' ? v.nis : v.nisn;
            var nilai = arrNilai[v.id_siswa] || {};
            var nhar = nilai.nhar || '';
            var npts = nilai.npts || '';
            var npas = nilai.npas || '';
            var akhir = calcNilaiAkhir(nhar, npts, npas);
            var pred = calcPredikat(akhir);
            dataSiswa.push([
                noInduk, v.nama, nhar, npts, npas,
                akhir,
                pred,
                nilai.p_deskripsi || '', nilai.k_rata_rata || '', nilai.k_predikat || '', nilai.k_deskripsi || '', v.id_siswa
            ]);
            row++;
        });

        // Remove redundant jQuery reference
        var arrCol = [];
        var char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for (let i = 0; i < 12; i++) {
            var item = {};
            // Default readOnly
            item['readOnly'] = true;

            if (i === 0) {
                item['title'] = 'NIS/NISN';
                item['width'] = 160;
            } else if (i === 1) {
                item['title'] = 'NAMA SISWA';
                item['width'] = 250;
            } else if (i === 2) {
                item['title'] = 'RPH';
                item['width'] = 60;
            } else if (i === 3) {
                item['title'] = 'PTS';
                item['width'] = 60;
            } else if (i === 4) {
                item['title'] = 'PAS';
                item['width'] = 60;
                item['readOnly'] = false; // Editable
            } else if (i === 5) {
                item['title'] = 'AKHIR';
                item['width'] = 70;
            } else if (i === 6) {
                item['title'] = 'PRED';
                item['width'] = 60;
            } else if (i === 7) {
                item['title'] = 'DESKRIPSI';
                item['width'] = 300;
                item['wordWrap'] = true;
            } else if (i === 8) {
                item['title'] = 'NILAI';
                item['width'] = 60;
            } else if (i === 9) {
                item['title'] = 'PRED';
                item['width'] = 60;
            } else if (i === 10) {
                item['title'] = 'DESKRIPSI';
                item['width'] = 300;
                item['wordWrap'] = true;
            } else if (i === 11) {
                item['title'] = 'ID';
                item['width'] = 1;
            }
            arrCol.push(item);
        }

        var container = document.getElementById('t-siswa');
        if (container) {
            container.innerHTML = '';
            var spreadsheet = jspreadsheet(container, {
                worksheets: [{
                    data: dataSiswa,
                    minDimensions: [12, 10],
                    search: true,
                    freezeColumns: 2,
                    columnResize: false,
                    columns: arrCol.map(c => ({ ...c, type: 'text' })),
                    nestedHeaders: [
                        [
                            { title: 'DATA SISWA', colspan: 2 },
                            { title: 'NILAI PENGETAHUAN', colspan: 6 },
                            { title: 'NILAI KETERAMPILAN', colspan: 4 },
                        ],
                    ],
                    onchange: function (instance, cell, col, row, value) {
                        col = parseInt(col);
                        row = parseInt(row);
                        // Recalculate saat PAS (col 4) diubah
                        if (col === 4) {
                            var ws = instance;
                            if (spreadsheet && spreadsheet.worksheets) {
                                ws = spreadsheet.worksheets[0];
                            } else if (Array.isArray(spreadsheet)) {
                                ws = spreadsheet[0];
                            } else if (container.jspreadsheet) {
                                ws = container.jspreadsheet[0];
                            }
                            var rowData = ws.getRowData(row);
                            var akhir = calcNilaiAkhir(rowData[2], rowData[3], value);
                            var pred = calcPredikat(akhir);
                            ws.setValueFromCoords(5, row, akhir, true);
                            ws.setValueFromCoords(6, row, pred, true);
                        }
                    },
                    updateTable: function (instance, cell, col, row, val, label, cellName) {
                        // Identitas siswa (NIS, Nama)
                        if (col === 0 || col === 1) {
                            cell.style.backgroundColor = '#e8eaf6';
                            cell.style.color = '#283593';
                            cell.style.fontWeight = '500';
                            cell.classList.add('readonly');
                        }
                        if (col === 0) cell.style.textAlign = 'center';
                        // RPH dan PTS (readonly, dari data sebelumnya)
                        if (col === 2 || col === 3) {
                            cell.style.backgroundColor = '#e3f2fd';
                            cell.style.color = '#37474f';
                            cell.style.textAlign = 'center';
                            cell.classList.add('readonly');
                        }
                        // PAS (editable)
                        if (col === 4) {
                            cell.style.backgroundColor = '#fff9c4';
                            cell.style.textAlign = 'center';
                            cell.style.fontWeight = '600';
                        }
                        // AKHIR (nilai akhir berbobot)
                        if (col === 5) {
                            cell.style.backgroundColor = '#e3f2fd';
                            cell.style.color = '#1565c0';
                            cell.style.fontWeight = '700';
                            cell.style.textAlign = 'center';
                            cell.classList.add('readonly');
                        }
                        // PREDIKAT Pengetahuan - warna dinamis
                        if (col === 6) {
                            cell.style.textAlign = 'center';
                            cell.style.fontWeight = '700';
                            cell.style.borderRadius = '4px';
                            cell.classList.add('readonly');
                            var v = String(val).trim().toUpperCase();
                            if (v === 'A') {
                                cell.style.backgroundColor = '#c8e6c9';
                                cell.style.color = '#2e7d32';
                            } else if (v === 'B') {
                                cell.style.backgroundColor = '#bbdefb';
                                cell.style.color = '#1565c0';
                            } else if (v === 'C') {
                                cell.style.backgroundColor = '#ffe0b2';
                                cell.style.color = '#e65100';
                            } else if (v === 'D') {
                                cell.style.backgroundColor = '#ffcdd2';
                                cell.style.color = '#c62828';
                            } else {
                                cell.style.backgroundColor = '#eceff1';
                                cell.style.color = '#90a4ae';
                            }
                        }
                        // DESKRIPSI Pengetahuan
                        if (col === 7) {
                            cell.style.backgroundColor = '#fff8e1';
                            cell.style.fontSize = '11px';
                            cell.style.textAlign = 'left';
                            cell.style.color = '#5d4037';
                            cell.classList.add('readonly');
                        }
                        // NILAI Keterampilan
                        if (col === 8) {
                            cell.style.backgroundColor = '#e3f2fd';
                            cell.style.color = '#1565c0';
                            cell.style.fontWeight = '700';
                            cell.style.textAlign = 'center';
                            cell.classList.add('readonly');
                        }
                        // PREDIKAT Keterampilan - warna dinamis
                        if (col === 9) {
                            cell.style.textAlign = 'center';
                            cell.style.fontWeight = '700';
                            cell.style.borderRadius = '4px';
                            cell.classList.add('readonly');
                            var vk = String(val).trim().toUpperCase();
                            if (vk === 'A') {
                                cell.style.backgroundColor = '#c8e6c9';
                                cell.style.color = '#2e7d32';
                            } else if (vk === 'B') {
                                cell.style.backgroundColor = '#bbdefb';
                                cell.style.color = '#1565c0';
                            } else if (vk === 'C') {
                                cell.style.backgroundColor = '#ffe0b2';
                                cell.style.color = '#e65100';
                            } else if (vk === 'D') {
                                cell.style.backgroundColor = '#ffcdd2';
                                cell.style.color = '#c62828';
                            } else {
                                cell.style.backgroundColor = '#eceff1';
                                cell.style.color = '#90a4ae';
                            }
                        }
                        // DESKRIPSI Keterampilan
                        if (col === 10) {
                            cell.style.backgroundColor = '#fff8e1';
                            cell.style.fontSize = '11px';
                            cell.style.textAlign = 'left';
                            cell.style.color = '#5d4037';
                            cell.classList.add('readonly');
                        }
                        // Hidden ID column
                        if (col === 11) {
                            cell.style.backgroundColor = '#fafafa';
                            cell.classList.add('readonly');
                        }
                    }
                }]
            });
        }

        $('#download-excel').on('click', function () {
            var inst = null;
            if (typeof spreadsheet !== 'undefined' && spreadsheet) {
                if (Array.isArray(spreadsheet)) inst = spreadsheet[0];
                else inst = spreadsheet;
            } else if (container.jspreadsheet) {
                inst = container.jspreadsheet[0];
            }
            if (inst) {
                inst.download();
            }
        });

        $('#uploadnilai').on('submit', function (e) {
            e.preventDefault();
            var data;
            if (Array.isArray(spreadsheet)) {
                data = spreadsheet[0].getData();
            } else if (spreadsheet.worksheets) {
                data = spreadsheet.worksheets[0].getData();
            } else if (typeof spreadsheet.getData === 'function') {
                data = spreadsheet.getData();
            } else {
                // Fallback to get from container
                data = container.jspreadsheet[0].getData();
            }
            var id_mapel = $(this).find('input[name="id_mapel"]').val();
            var id_kelas = $(this).find('input[name="id_kelas"]').val();

            var siswa = [];
            $.each(data, function (idx, s) {
                var idSiswa = s[11];
                siswa.push({
                    id_nilai_akhir: id_mapel + id_kelas + idSiswa + tpActive + smtActive,
                    id_siswa: idSiswa,
                    id_tp: tpActive,
                    id_smt: smtActive,
                    id_mapel: id_mapel,
                    id_kelas: id_kelas,
                    nilai: s[5],
                    akhir: s[6],
                    predikat: s[7]
                });
            });

            $.ajax({
                type: "POST",
                url: base_url + 'rapor/importpas',
                data: { siswa: siswa },
                success: function (data) {
                    swal.fire({
                        title: "Sukses",
                        text: "Nilai PAS berhasil disimpan",
                        icon: "success",
                        showCancelButton: false,
                    }).then(result => {
                        location.reload();
                    });
                },
                error: function (e) {
                    swal.fire("Gagal", "Gagal menyimpan", "error");
                }
            });
        });
    });

    async function processDownload(idMapel, idKelas) {
        window.location.href = base_url + 'rapor/downloadtemplate_pas/' + idMapel + '/' + idKelas;
    }

    // ============================================
    // UI STATE MANAGEMENT FUNCTIONS
    // ============================================

    function showLoadingState(skeletonId, contentId, errorId) {
        $('#' + skeletonId).removeClass('d-none').attr('aria-busy', 'true');
        if (contentId) $('#' + contentId).addClass('d-none');
        if (errorId) $('#' + errorId).addClass('d-none');
        announceToScreenReader('Memuat data, mohon tunggu...');
    }

    function hideLoadingState(skeletonId, contentId) {
        $('#' + skeletonId).addClass('d-none').attr('aria-busy', 'false');
        if (contentId) $('#' + contentId).removeClass('d-none');
    }

    function showErrorState(errorId, messageId, message, autoHide = 5000) {
        if (messageId) $('#' + messageId).text(message || 'Terjadi kesalahan saat memuat data.');
        $('#' + errorId).removeClass('d-none').attr('aria-live', 'assertive');
        announceToScreenReader('Error: ' + (message || 'Terjadi kesalahan saat memuat data.'));
        if (autoHide > 0) {
            setTimeout(() => {
                $('#' + errorId).fadeOut(300, function() {
                    $(this).addClass('d-none').show();
                });
            }, autoHide);
        }
    }

    function hideErrorState(errorId) {
        $('#' + errorId).addClass('d-none').attr('aria-live', 'polite');
    }

    function showEmptyState(emptyId, contentId) {
        $('#' + emptyId).removeClass('d-none');
        if (contentId) $('#' + contentId).addClass('d-none');
        announceToScreenReader('Tidak ada data untuk ditampilkan');
    }

    function hideEmptyState(emptyId, contentId) {
        $('#' + emptyId).addClass('d-none');
        if (contentId) $('#' + contentId).removeClass('d-none');
    }

    function announceToScreenReader(message) {
        let liveRegion = document.getElementById('sr-announcements');
        if (!liveRegion) {
            liveRegion = document.createElement('div');
            liveRegion.id = 'sr-announcements';
            liveRegion.setAttribute('role', 'status');
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            liveRegion.className = 'sr-only';
            document.body.appendChild(liveRegion);
        }
        liveRegion.textContent = message;
        setTimeout(() => {
            liveRegion.textContent = '';
        }, 1000);
    }

    function setButtonLoading(buttonSelector, isLoading, originalText) {
        const btn = $(buttonSelector);
        if (isLoading) {
            btn.prop('disabled', true).attr('aria-busy', 'true');
            btn.data('original-text', originalText || btn.html());
            btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...');
        } else {
            btn.prop('disabled', false).attr('aria-busy', 'false');
            btn.html(btn.data('original-text') || originalText);
        }
    }

    // Global AJAX error handler
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('AJAX Error:', error);
        let errorMessage = 'Terjadi kesalahan saat menghubungi server.';
        if (jqXHR.status === 0) {
            errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
        } else if (jqXHR.status === 404) {
            errorMessage = 'Halaman tidak ditemukan.';
        } else if (jqXHR.status === 500) {
            errorMessage = 'Terjadi kesalahan pada server.';
        } else if (error === 'timeout') {
            errorMessage = 'Permintaan timeout. Silakan coba lagi.';
        }
        showErrorState('errorStateMain', 'errorMessageMain', errorMessage);
    });

    // Initialize page
    $(document).ready(function() {
        // Show empty state if no students
        if (arrSiswa && arrSiswa.length === 0) {
            showEmptyState('emptyStateTable', 't-siswa');
        }

        // File upload validation
        $('#custom-file').on('change', function() {
            const file = this.files[0];
            if (file) {
                const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
                const validExtensions = ['.xlsx', '.xls'];
                const fileName = file.name.toLowerCase();
                const hasValidExtension = validExtensions.some(ext => fileName.endsWith(ext));

                if (!validTypes.includes(file.type) && !hasValidExtension) {
                    $(this).addClass('is-invalid');
                    showErrorState('errorStateMain', 'errorMessageMain', 'File harus berformat .xlsx atau .xls');
                    this.value = '';
                } else {
                    $(this).removeClass('is-invalid');
                    hideErrorState('errorStateMain');
                }
            }
        });

        // Upload form validation
        $('#uploadfile').on('submit', function(e) {
            const fileInput = $('#custom-file')[0];
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                showErrorState('errorStateMain', 'errorMessageMain', 'Silakan pilih file terlebih dahulu');
                fileInput.focus();
                return false;
            }
        });
    });
</script>
<?= $this->endSection() ?>