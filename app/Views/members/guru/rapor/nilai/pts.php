<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsuites@5/dist/jsuites.min.css" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@5/dist/jspreadsheet.min.css" type="text/css" />
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    /* ============================================
       DESIGN TOKENS - CSS Variables
       ============================================ */
    :root {
        /* Colors - Primary (Facebook Blue) */
        --color-primary-50: #eff6ff;
        --color-primary-100: #dbeafe;
        --color-primary-200: #bfdbfe;
        --color-primary-300: #93c5fd;
        --color-primary-400: #60a5fa;
        --color-primary-500: #3b82f6;
        --color-primary-600: #2563eb;
        --color-primary-700: #1d4ed8;
        --color-primary-800: #1e40af;
        --color-primary-900: #1e3a8a;
        
        /* Colors - Neutral */
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
        
        /* Colors - Semantic */
        --color-success-50: #f0fdf4;
        --color-success-100: #dcfce7;
        --color-success-500: #22c55e;
        --color-success-600: #16a34a;
        --color-success-700: #15803d;
        
        --color-warning-50: #fffbeb;
        --color-warning-100: #fef3c7;
        --color-warning-500: #f59e0b;
        --color-warning-600: #d97706;
        --color-warning-700: #b45309;
        
        --color-danger-50: #fef2f2;
        --color-danger-100: #fee2e2;
        --color-danger-500: #ef4444;
        --color-danger-600: #dc2626;
        --color-danger-700: #b91c1c;
        
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
        --shadow-primary: 0 4px 12px rgba(24, 119, 242, 0.3);
        --shadow-primary-lg: 0 6px 16px rgba(24, 119, 242, 0.4);
        
        /* Transitions */
        --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ============================================
       BASE STYLES
       ============================================ */
    .pts-container {
        font-family: var(--font-family-base);
        color: var(--color-neutral-800);
        line-height: var(--line-height-normal);
    }

    /* ============================================
       CARD COMPONENT
       ============================================ */
    .pts-card {
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        transition: box-shadow var(--transition-base);
    }

    .pts-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .pts-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--space-6);
        border-bottom: 1px solid var(--color-neutral-200);
        background: linear-gradient(135deg, var(--color-neutral-50) 0%, #ffffff 100%);
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    .pts-card__title {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--color-neutral-800);
        margin: 0;
    }

    .pts-card__title-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        background: var(--color-primary-100);
        color: var(--color-primary-600);
        border-radius: var(--radius-lg);
    }

    .pts-card__body {
        padding: var(--space-6);
    }

    /* ============================================
       BUTTON COMPONENTS
       ============================================ */
    .pts-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        padding: var(--space-3) var(--space-5);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        line-height: var(--line-height-tight);
        border-radius: var(--radius-lg);
        border: 1px solid transparent;
        cursor: pointer;
        transition: all var(--transition-base);
        text-decoration: none;
        white-space: nowrap;
    }

    .pts-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.3);
    }

    .pts-btn:focus-visible {
        outline: 2px solid var(--color-primary-500);
        outline-offset: 2px;
    }

    /* Primary Button */
    .pts-btn--primary {
        background: linear-gradient(135deg, #1877F2 0%, #166FE5 50%, #0D5EDB 100%);
        color: #ffffff;
        box-shadow: var(--shadow-primary);
    }

    .pts-btn--primary:hover {
        background: linear-gradient(135deg, #166FE5 0%, #0D5EDB 50%, #0A51C7 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-primary-lg);
    }

    .pts-btn--primary:active {
        transform: translateY(0);
    }

    .pts-btn--primary:disabled {
        background: linear-gradient(135deg, #94B8E8 0%, #8AADE0 100%);
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    /* Secondary Button */
    .pts-btn--secondary {
        background: var(--color-neutral-100);
        color: var(--color-neutral-700);
        border-color: var(--color-neutral-300);
    }

    .pts-btn--secondary:hover {
        background: var(--color-neutral-200);
        color: var(--color-neutral-800);
    }

    /* Success Button */
    .pts-btn--success {
        background: var(--color-success-600);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .pts-btn--success:hover {
        background: var(--color-success-700);
        transform: translateY(-1px);
    }

    /* Danger Button */
    .pts-btn--danger {
        background: var(--color-danger-600);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .pts-btn--danger:hover {
        background: var(--color-danger-700);
        transform: translateY(-1px);
    }

    /* Button Sizes */
    .pts-btn--sm {
        padding: var(--space-2) var(--space-4);
        font-size: var(--font-size-xs);
    }

    .pts-btn--lg {
        padding: var(--space-4) var(--space-6);
        font-size: var(--font-size-base);
    }

    /* Button with Loading State */
    .pts-btn--loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }

    .pts-btn--loading::after {
        content: '';
        position: absolute;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: pts-spin 0.8s linear infinite;
    }

    @keyframes pts-spin {
        to { transform: rotate(360deg); }
    }

    /* ============================================
       FORM COMPONENTS
       ============================================ */
    .pts-form-group {
        margin-bottom: var(--space-4);
    }

    .pts-form-label {
        display: block;
        margin-bottom: var(--space-2);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--color-neutral-700);
    }

    .pts-form-control {
        display: block;
        width: 100%;
        padding: var(--space-3) var(--space-4);
        font-size: var(--font-size-sm);
        line-height: var(--line-height-normal);
        color: var(--color-neutral-800);
        background-color: #ffffff;
        border: 1px solid var(--color-neutral-300);
        border-radius: var(--radius-lg);
        transition: all var(--transition-fast);
    }

    .pts-form-control:focus {
        outline: none;
        border-color: var(--color-primary-500);
        box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.15);
    }

    .pts-form-control:hover {
        border-color: var(--color-neutral-400);
    }

    .pts-form-control:disabled {
        background-color: var(--color-neutral-100);
        color: var(--color-neutral-500);
        cursor: not-allowed;
    }

    .pts-form-control::placeholder {
        color: var(--color-neutral-400);
    }

    /* File Input Styling */
    .pts-file-input {
        position: relative;
    }

    .pts-file-input input[type="file"] {
        padding: var(--space-3);
        cursor: pointer;
    }

    /* ============================================
       ALERT COMPONENTS
       ============================================ */
    .pts-alert {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        padding: var(--space-4) var(--space-5);
        border-radius: var(--radius-lg);
        border: 1px solid transparent;
    }

    .pts-alert__icon {
        flex-shrink: 0;
        font-size: var(--font-size-xl);
    }

    .pts-alert__content {
        flex: 1;
        font-size: var(--font-size-sm);
        line-height: var(--line-height-relaxed);
    }

    .pts-alert--danger {
        background-color: var(--color-danger-50);
        border-color: var(--color-danger-200);
        color: var(--color-danger-700);
    }

    .pts-alert--warning {
        background-color: var(--color-warning-50);
        border-color: var(--color-warning-200);
        color: var(--color-warning-700);
    }

    .pts-alert--success {
        background-color: var(--color-success-50);
        border-color: var(--color-success-200);
        color: var(--color-success-700);
    }

    .pts-alert--info {
        background-color: var(--color-primary-50);
        border-color: var(--color-primary-200);
        color: var(--color-primary-700);
    }

    /* ============================================
       STATS CARDS
       ============================================ */
    .pts-stats-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }

    @media (min-width: 768px) {
        .pts-stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .pts-stat-card {
        display: flex;
        flex-direction: column;
        padding: var(--space-5);
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-lg);
        transition: all var(--transition-base);
    }

    .pts-stat-card:hover {
        border-color: var(--color-neutral-300);
        box-shadow: var(--shadow-md);
    }

    .pts-stat-card__label {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-neutral-500);
        margin-bottom: var(--space-2);
    }

    .pts-stat-card__value {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--color-neutral-800);
        line-height: var(--line-height-tight);
    }

    .pts-stat-card__meta {
        font-size: var(--font-size-xs);
        color: var(--color-neutral-500);
        margin-top: var(--space-2);
    }

    .pts-stat-card--primary .pts-stat-card__value {
        color: var(--color-primary-600);
    }

    .pts-stat-card--success .pts-stat-card__value {
        color: var(--color-success-600);
    }

    /* Predikat Badges */
    .pts-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: var(--space-2) var(--space-3);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        border-radius: var(--radius-full);
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .pts-badge--danger {
        background-color: var(--color-danger-100);
        color: var(--color-danger-700);
    }

    .pts-badge--warning {
        background-color: var(--color-warning-100);
        color: var(--color-warning-700);
    }

    .pts-badge--info {
        background-color: var(--color-primary-100);
        color: var(--color-primary-700);
    }

    .pts-badge--success {
        background-color: var(--color-success-100);
        color: var(--color-success-700);
    }

    /* ============================================
       ACTION BAR
       ============================================ */
    .pts-action-bar {
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
        padding: var(--space-5);
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-6);
    }

    @media (min-width: 768px) {
        .pts-action-bar {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .pts-action-group {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
        flex: 1;
    }

    @media (min-width: 768px) {
        .pts-action-group {
            flex-direction: row;
            align-items: center;
        }
    }

    /* ============================================
       LOADING SKELETON
       ============================================ */
    .pts-skeleton {
        background: linear-gradient(90deg, var(--color-neutral-200) 25%, var(--color-neutral-100) 50%, var(--color-neutral-200) 75%);
        background-size: 200% 100%;
        animation: pts-skeleton-loading 1.5s ease-in-out infinite;
        border-radius: var(--radius-md);
    }

    @keyframes pts-skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .pts-skeleton--text {
        height: 1rem;
        margin-bottom: var(--space-2);
    }

    .pts-skeleton--title {
        height: 1.5rem;
        width: 60%;
        margin-bottom: var(--space-4);
    }

    .pts-skeleton--card {
        height: 8rem;
        margin-bottom: var(--space-4);
    }

    /* ============================================
       EMPTY STATE
       ============================================ */
    .pts-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: var(--space-12) var(--space-6);
        text-align: center;
    }

    .pts-empty-state__icon {
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

    .pts-empty-state__title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--color-neutral-700);
        margin-bottom: var(--space-2);
    }

    .pts-empty-state__description {
        font-size: var(--font-size-sm);
        color: var(--color-neutral-500);
        max-width: 24rem;
        margin-bottom: var(--space-6);
    }

    /* ============================================
       STICKY SAVE BUTTON
       ============================================ */
    .pts-sticky-save {
        position: sticky;
        bottom: var(--space-6);
        z-index: 1020;
        text-align: center;
        margin-top: var(--space-8);
    }

    .pts-sticky-save__btn {
        display: inline-flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-4) var(--space-8);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        background: linear-gradient(135deg, #1877F2 0%, #166FE5 50%, #0D5EDB 100%);
        color: #ffffff;
        border: none;
        border-radius: var(--radius-full);
        box-shadow: var(--shadow-xl);
        cursor: pointer;
        transition: all var(--transition-base);
    }

    .pts-sticky-save__btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 30px -5px rgb(0 0 0 / 0.15), 0 10px 15px -5px rgb(0 0 0 / 0.1);
    }

    .pts-sticky-save__btn:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(24, 119, 242, 0.3), var(--shadow-xl);
    }

    .pts-sticky-save__btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    /* ============================================
       SPREADSHEET OVERRIDES
       ============================================ */
    .jexcel_container {
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-neutral-200);
        overflow: hidden;
    }

    .jexcel_content {
        background: #ffffff;
    }

    /* Responsive adjustments for spreadsheet */
    @media (max-width: 767px) {
        .jexcel_content table {
            font-size: var(--font-size-xs);
        }
        
        .jexcel_content td,
        .jexcel_content th {
            padding: var(--space-2);
        }
    }

    /* ============================================
       ACCESSIBILITY IMPROVEMENTS
       ============================================ */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* Focus visible for keyboard navigation */
    .pts-btn:focus-visible,
    .pts-form-control:focus-visible,
    .pts-sticky-save__btn:focus-visible {
        outline: 2px solid var(--color-primary-500);
        outline-offset: 2px;
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .pts-btn--primary {
            border: 2px solid #ffffff;
        }
        
        .pts-card {
            border: 2px solid var(--color-neutral-800);
        }
    }

    /* Legacy compatibility */
    .bg-soft-info { background-color: var(--color-primary-100); color: var(--color-primary-700); }
    .bg-soft-success { background-color: var(--color-success-100); color: var(--color-success-700); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="content pts-container">
    <div class="container-fluid">
        <!-- Loading Skeleton (shown while data is loading) -->
        <div id="loadingSkeleton" class="pts-card mb-4" style="display: none;">
            <div class="pts-card__header">
                <div class="pts-skeleton pts-skeleton--title" style="width: 300px;"></div>
            </div>
            <div class="pts-card__body">
                <div class="pts-stats-grid">
                    <div class="pts-skeleton pts-skeleton--card"></div>
                    <div class="pts-skeleton pts-skeleton--card"></div>
                    <div class="pts-skeleton pts-skeleton--card"></div>
                </div>
                <div class="pts-skeleton pts-skeleton--card" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="pts-card mb-4" id="mainContent">
            <div class="pts-card__header">
                <h1 class="pts-card__title">
                    <span class="pts-card__title-icon" aria-hidden="true">
                        <i class="bi bi-journal-check"></i>
                    </span>
                    <span>Input Nilai PTS: <strong><?= esc($mapel['nama_mapel']) ?></strong></span>
                </h1>
                <div class="d-flex gap-2">
                    <button type="button" 
                            id="download-excel" 
                            class="pts-btn pts-btn--success pts-btn--sm"
                            aria-label="Ekspor data ke format Excel">
                        <i class="bi bi-file-excel" aria-hidden="true"></i>
                        <span>Ekspor Excel</span>
                    </button>
                    <button type="button" 
                            onclick="window.history.back();" 
                            class="pts-btn pts-btn--danger pts-btn--sm"
                            aria-label="Kembali ke halaman sebelumnya">
                        <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
                        <span>Kembali</span>
                    </button>
                </div>
            </div>
            
            <div class="pts-card__body">
                <?php if ($kkm == null): ?>
                    <!-- Empty State for KKM not set -->
                    <div class="pts-empty-state" role="alert">
                        <div class="pts-empty-state__icon">
                            <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                        </div>
                        <h2 class="pts-empty-state__title">KKM Belum Diatur</h2>
                        <p class="pts-empty-state__description">
                            Download template tidak tersedia. Silakan isi KKM terlebih dahulu di menu 
                            <strong>DATA RAPOR > KKM DAN BOBOT</strong> untuk melanjutkan.
                        </p>
                        <a href="<?= base_url('rapor/kkm') ?>" class="pts-btn pts-btn--primary">
                            <i class="bi bi-gear me-2"></i> Atur KKM
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Action Bar: Download & Upload -->
                    <div class="pts-action-bar">
                        <button type="button" 
                                class="pts-btn pts-btn--primary pts-btn--lg" 
                                onclick="processDownload(<?= (int)$mapel['id_mapel'] ?>, <?= (int)$kelas['id_kelas'] ?>)"
                                aria-label="Download template Excel untuk input nilai">
                            <i class="bi bi-cloud-download" aria-hidden="true"></i>
                            <span>Download Template (.xlsx)</span>
                        </button>
                        
                        <form id="uploadfile" 
                              enctype="multipart/form-data" 
                              class="pts-action-group"
                              aria-label="Form upload file nilai">
                            <?= csrf_field() ?>
                            <div class="pts-form-group mb-0 flex-grow-1">
                                <label for="custom-file" class="visually-hidden">Pilih file Excel</label>
                                <input type="file" 
                                       name="upload_file" 
                                       class="pts-form-control" 
                                       id="custom-file" 
                                       accept=".xlsx,.xls"
                                       aria-describedby="fileHelp">
                            </div>
                            <button type="button" 
                                    id="upload" 
                                    class="pts-btn pts-btn--success"
                                    aria-label="Upload file nilai">
                                <i class="bi bi-cloud-upload" aria-hidden="true"></i>
                                <span>Upload</span>
                            </button>
                        </form>
                    </div>
                    
                    <?php
                    $multi = $setting_rapor->kkm_tunggal == "0" ? "MULTI" : "TUNGGAL";
                    $isi = (int)$kkm->kkm;
                    $dsd = $isi - 1;
                    $c = $isi;
                    $csd = floor($isi + (100 - $isi) / 3);
                    $b = $csd + 1;
                    $bsd = floor($b + (100 - $b) / 2);
                    $a = $bsd + 1;
                    ?>
                    
                    <!-- Stats Grid: KKM & Predikat Info -->
                    <div class="pts-stats-grid" role="region" aria-label="Informasi KKM dan Predikat">
                        <!-- KKM Card -->
                        <div class="pts-stat-card pts-stat-card--primary">
                            <span class="pts-stat-card__label">KKM Utama</span>
                            <span class="pts-stat-card__value"><?= $kkm->kkm ?></span>
                            <span class="pts-stat-card__meta">Jenis: <?= $multi ?></span>
                        </div>
                        
                        <!-- Predikat Interval Card -->
                        <div class="pts-stat-card">
                            <span class="pts-stat-card__label">Interval Predikat</span>
                            <div class="d-flex justify-content-center gap-2 flex-wrap mt-2">
                                <span class="pts-badge pts-badge--danger" title="Nilai 0-<?= $dsd ?>">D (0-<?= $dsd ?>)</span>
                                <span class="pts-badge pts-badge--warning" title="Nilai <?= $c ?>-<?= $csd ?>">C (<?= $c ?>-<?= $csd ?>)</span>
                                <span class="pts-badge pts-badge--info" title="Nilai <?= $b ?>-<?= $bsd ?>">B (<?= $b ?>-<?= $bsd ?>)</span>
                                <span class="pts-badge pts-badge--success" title="Nilai <?= $a ?>-100">A (<?= $a ?>-100)</span>
                            </div>
                        </div>
                        
                        <!-- Bobot PTS Card -->
                        <div class="pts-stat-card pts-stat-card--success">
                            <span class="pts-stat-card__label">Bobot PTS</span>
                            <span class="pts-stat-card__value"><?= $kkm->bobot_pts ?>%</span>
                            <span class="pts-stat-card__meta">Kontribusi Nilai Rapor</span>
                        </div>
                    </div>

                    <!-- Error State (Hidden by default) -->
                    <div id="errorState" class="pts-alert pts-alert--danger mb-4" role="alert" style="display: none;">
                        <span class="pts-alert__icon" aria-hidden="true">
                            <i class="bi bi-x-circle-fill"></i>
                        </span>
                        <div class="pts-alert__content">
                            <strong>Error:</strong> <span id="errorMessage">Terjadi kesalahan saat memuat data.</span>
                        </div>
                    </div>

                    <!-- Spreadsheet Container -->
                    <div id="t-siswa" class="w-100 mb-4 jspreadsheet-modern" role="region" aria-label="Tabel input nilai siswa"></div>
                    
                    <!-- Empty State for No Students -->
                    <div id="emptyState" class="pts-empty-state" style="display: none;">
                        <div class="pts-empty-state__icon">
                            <i class="bi bi-inbox fs-1"></i>
                        </div>
                        <h2 class="pts-empty-state__title">Tidak Ada Data Siswa</h2>
                        <p class="pts-empty-state__description">
                            Tidak ditemukan data siswa untuk kelas ini. 
                            Pastikan data siswa sudah diinput dengan benar.
                        </p>
                    </div>
                    
                    <!-- Save Form -->
                    <form id="uploadnilai" aria-label="Form simpan nilai PTS">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_kelas" value="<?= (int)$kelas['id_kelas'] ?>">
                        <input type="hidden" name="id_mapel" value="<?= (int)$mapel['id_mapel'] ?>">
                        
                        <div class="pts-sticky-save">
                            <button type="submit" 
                                    id="btnSave"
                                    class="pts-sticky-save__btn"
                                    aria-label="Simpan nilai PTS">
                                <i class="bi bi-check-all" aria-hidden="true"></i>
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

<!-- Hidden Upload Form -->
<form id="uploadpts" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="id_mapel" value="<?= (int)$mapel['id_mapel'] ?>">
    <input type="hidden" name="id_kelas" value="<?= (int)$kelas['id_kelas'] ?>">
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
    var kkm = <?= json_encode($kkm) ?>;
    var idMapel = '<?= $mapel['id_mapel'] ?>';
    var idKelas = '<?= $kelas['id_kelas'] ?>';
    var tpActive = '<?= $tp_active->id_tp ?>';
    var smtActive = '<?= $smt_active->id_smt ?>';
    var namaMapel = '<?= $mapel['nama_mapel'] ?>';
    var namaKelas = '<?= $kelas['nama_kelas'] ?>';

    var isi = parseInt(kkm ? kkm.kkm : 0);
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
        // Show loading skeleton initially
        showLoadingState();
        
        // Check if there are students
        if (!arrSiswa || arrSiswa.length === 0) {
            hideLoadingState();
            showEmptyState();
            return;
        }
        
        var dataSiswa = [];
        var row = 1;
        $.each(arrSiswa, function (i, v) {
            var noInduk = v.nisn == null || v.nisn == '' ? v.nis : v.nisn;
            var nilai = arrNilai[v.id_siswa] || {};
            var nilaiPts = nilai.nilai || '';
            dataSiswa.push([
                noInduk, v.nama, nilaiPts,
                calcPredikat(nilaiPts),
                v.id_siswa
            ]);
            row++;
        });

        // Remove redundant jQuery reference
        var arrCol = [];
        var char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for (let i = 0; i < 5; i++) {
            var item = {};
            // Default readOnly
            item['readOnly'] = true;

            if (i === 0) {
                item['title'] = 'NIS/NISN\n' + char.charAt(i);
                item['width'] = 160;
            } else if (i === 1) {
                item['title'] = 'NAMA SISWA\n' + char.charAt(i);
                item['width'] = 250;
            } else if (i === 2) {
                item['title'] = 'NILAI PTS\n' + char.charAt(i);
                item['width'] = 100;
                item['readOnly'] = false; // Editable
            } else if (i === 3) {
                item['title'] = 'PREDIKAT\n' + char.charAt(i);
                item['width'] = 100;
            } else if (i === 4) {
                item['title'] = 'ID';
                item['width'] = 1;
            }
            arrCol.push(item);
        }

        var container = document.getElementById('t-siswa');
        if (container) {
            container.innerHTML = '';
            try {
                var spreadsheet = jspreadsheet(container, {
                    worksheets: [{
                        data: dataSiswa,
                        minDimensions: [5, 5],
                        search: true,
                        freezeColumns: 2,
                        columnResize: false,
                        columns: arrCol.map(c => ({ ...c, type: 'text' })),
                        onchange: function (instance, cell, col, row, value) {
                            col = parseInt(col);
                            row = parseInt(row);
                            // Recalculate predikat saat nilai PTS (col 2) diubah
                            if (col === 2) {
                                var ws = instance;
                                if (spreadsheet && spreadsheet.worksheets) {
                                    ws = spreadsheet.worksheets[0];
                                } else if (Array.isArray(spreadsheet)) {
                                    ws = spreadsheet[0];
                                } else if (container.jspreadsheet) {
                                    ws = container.jspreadsheet[0];
                                }
                                ws.setValueFromCoords(3, row, calcPredikat(value), true);
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
                            // Input nilai PTS (editable)
                            if (col === 2) {
                                cell.style.backgroundColor = '#fff9c4';
                                cell.style.textAlign = 'center';
                                cell.style.fontWeight = '600';
                            }
                            // PREDIKAT - warna dinamis
                            if (col === 3) {
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
                            // Hidden ID column
                            if (col === 4) {
                                cell.style.backgroundColor = '#fafafa';
                                cell.classList.add('readonly');
                            }
                        },
                    }]
                });
                
                // Hide loading state on success
                hideLoadingState();
            } catch (error) {
                hideLoadingState();
                showErrorState('Gagal memuat tabel spreadsheet. Silakan refresh halaman.');
                console.error('Spreadsheet initialization error:', error);
            }
        }

        // Download Excel button handler
        $('#download-excel').on('click', function () {
            var btn = $(this);
            btn.addClass('pts-btn--loading');
            btn.prop('disabled', true);
            
            var inst = null;
            if (typeof spreadsheet !== 'undefined' && spreadsheet) {
                if (Array.isArray(spreadsheet)) inst = spreadsheet[0];
                else inst = spreadsheet;
            } else if (container.jspreadsheet) {
                inst = container.jspreadsheet[0];
            }
            if (inst) {
                try {
                    inst.download();
                } catch (e) {
                    showErrorState('Gagal mengunduh file. Silakan coba lagi.');
                }
            }
            
            setTimeout(function() {
                btn.removeClass('pts-btn--loading');
                btn.prop('disabled', false);
            }, 1000);
        });

        // Form submit handler with loading state
        $('#uploadnilai').on('submit', function (e) {
            e.preventDefault();
            
            var btnSave = $('#btnSave');
            btnSave.prop('disabled', true);
            btnSave.addClass('pts-btn--loading');
            
            var data;
            try {
                if (Array.isArray(spreadsheet)) {
                    data = spreadsheet[0].getData();
                } else if (spreadsheet && spreadsheet.worksheets) {
                    data = spreadsheet.worksheets[0].getData();
                } else if (spreadsheet && typeof spreadsheet.getData === 'function') {
                    data = spreadsheet.getData();
                } else {
                    data = container.jspreadsheet[0].getData();
                }
            } catch (err) {
                showErrorState('Gagal membaca data dari tabel.');
                btnSave.prop('disabled', false);
                btnSave.removeClass('pts-btn--loading');
                return;
            }
            
            var id_mapel = $(this).find('input[name="id_mapel"]').val();
            var id_kelas = $(this).find('input[name="id_kelas"]').val();

            var siswa = [];
            $.each(data, function (idx, s) {
                var idSiswa = s[4];
                var nilaiValue = s[2];
                
                // Validasi nilai
                if (nilaiValue !== '' && nilaiValue !== null) {
                    var nilaiNum = Number(nilaiValue);
                    if (isNaN(nilaiNum) || nilaiNum < 0 || nilaiNum > 100) {
                        return; // Skip invalid values
                    }
                }
                
                siswa.push({
                    id_nilai_pts: id_mapel + id_kelas + idSiswa + tpActive + smtActive,
                    id_siswa: idSiswa,
                    id_tp: tpActive,
                    id_smt: smtActive,
                    id_mapel: id_mapel,
                    id_kelas: id_kelas,
                    nilai: s[2],
                    predikat: s[3]
                });
            });
            
            if (siswa.length === 0) {
                showErrorState('Tidak ada data siswa yang valid untuk disimpan.');
                btnSave.prop('disabled', false);
                btnSave.removeClass('pts-btn--loading');
                return;
            }

            $.ajax({
                type: "POST",
                url: base_url + 'rapor/importpts',
                data: { siswa: siswa },
                beforeSend: function() {
                    // Already showing loading state
                },
                success: function (data) {
                    btnSave.removeClass('pts-btn--loading');
                    btnSave.prop('disabled', false);
                    
                    swal.fire({
                        title: "Sukses",
                        text: "Nilai PTS berhasil disimpan",
                        icon: "success",
                        showCancelButton: false,
                    }).then(result => {
                        location.reload();
                    });
                },
                error: function (e) {
                    btnSave.removeClass('pts-btn--loading');
                    btnSave.prop('disabled', false);
                    
                    var errorMsg = 'Gagal menyimpan data. Silakan coba lagi.';
                    if (e.responseJSON && e.responseJSON.message) {
                        errorMsg = e.responseJSON.message;
                    }
                    showErrorState(errorMsg);
                    
                    swal.fire("Gagal", errorMsg, "error");
                }
            });
        });
        
        // Upload button handler
        $('#upload').on('click', function() {
            var fileInput = $('#custom-file')[0];
            if (!fileInput.files || fileInput.files.length === 0) {
                showErrorState('Silakan pilih file terlebih dahulu.');
                return;
            }
            
            var btn = $(this);
            btn.addClass('pts-btn--loading');
            btn.prop('disabled', true);
            
            // Simulate upload process (actual implementation depends on backend)
            setTimeout(function() {
                btn.removeClass('pts-btn--loading');
                btn.prop('disabled', false);
            }, 2000);
        });
    });

    // UI State Management Functions
    function showLoadingState() {
        $('#loadingSkeleton').show();
        $('#mainContent').hide();
        $('#errorState').hide();
        $('#emptyState').hide();
    }

    function hideLoadingState() {
        $('#loadingSkeleton').hide();
        $('#mainContent').show();
    }

    function showEmptyState() {
        $('#loadingSkeleton').hide();
        $('#mainContent').hide();
        $('#errorState').hide();
        $('#emptyState').show();
    }

    function showErrorState(message) {
        $('#errorMessage').text(message);
        $('#errorState').show();
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('#errorState').fadeOut();
        }, 5000);
    }

    function hideErrorState() {
        $('#errorState').hide();
    }

    // Keyboard navigation support
    $(document).on('keydown', function(e) {
        // Ctrl+S to save
        if ((e.ctrlKey || e.metaKey) && e.which === 83) {
            e.preventDefault();
            $('#uploadnilai').submit();
        }
    });

    async function processDownload(idMapel, idKelas) {
        try {
            window.location.href = base_url + 'rapor/downloadtemplatepts/' + idMapel + '/' + idKelas;
        } catch (error) {
            showErrorState('Gagal mengunduh template. Silakan coba lagi.');
        }
    }
</script>
<?= $this->endSection() ?>