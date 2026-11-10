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
    .harian-container {
        font-family: var(--font-family-base);
        color: var(--color-neutral-800);
        line-height: var(--line-height-normal);
    }

    /* ============================================
       CARD COMPONENT
       ============================================ */
    .harian-card {
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        transition: box-shadow var(--transition-base);
        margin-bottom: var(--space-6);
    }

    .harian-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .harian-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--space-6);
        border-bottom: 1px solid var(--color-neutral-200);
        background: linear-gradient(135deg, var(--color-neutral-50) 0%, #ffffff 100%);
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    .harian-card__title {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--color-neutral-800);
        margin: 0;
    }

    .harian-card__title-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        background: var(--color-primary-100);
        color: var(--color-primary-600);
        border-radius: var(--radius-lg);
    }

    .harian-card__body {
        padding: var(--space-6);
    }

    /* ============================================
       BUTTON COMPONENTS
       ============================================ */
    .harian-btn {
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

    .harian-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.3);
    }

    .harian-btn:focus-visible {
        outline: 2px solid var(--color-primary-500);
        outline-offset: 2px;
    }

    /* Primary Button */
    .harian-btn--primary {
        background: linear-gradient(135deg, #1877F2 0%, #166FE5 50%, #0D5EDB 100%);
        color: #ffffff;
        box-shadow: var(--shadow-primary);
    }

    .harian-btn--primary:hover {
        background: linear-gradient(135deg, #166FE5 0%, #0D5EDB 50%, #0A51C7 100%);
        transform: translateY(-1px);
        box-shadow: var(--shadow-primary-lg);
    }

    .harian-btn--primary:active {
        transform: translateY(0);
    }

    .harian-btn--primary:disabled {
        background: linear-gradient(135deg, #94B8E8 0%, #8AADE0 100%);
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    /* Secondary Button */
    .harian-btn--secondary {
        background: var(--color-neutral-100);
        color: var(--color-neutral-700);
        border-color: var(--color-neutral-300);
    }

    .harian-btn--secondary:hover {
        background: var(--color-neutral-200);
        color: var(--color-neutral-800);
    }

    /* Success Button */
    .harian-btn--success {
        background: var(--color-success-600);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .harian-btn--success:hover {
        background: var(--color-success-700);
        transform: translateY(-1px);
    }

    /* Danger Button */
    .harian-btn--danger {
        background: var(--color-danger-600);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .harian-btn--danger:hover {
        background: var(--color-danger-700);
        transform: translateY(-1px);
    }

    /* Button Sizes */
    .harian-btn--sm {
        padding: var(--space-2) var(--space-4);
        font-size: var(--font-size-xs);
    }

    .harian-btn--lg {
        padding: var(--space-4) var(--space-6);
        font-size: var(--font-size-base);
    }

    /* Button with Loading State */
    .harian-btn--loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }

    .harian-btn--loading::after {
        content: '';
        position: absolute;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: harian-spin 0.8s linear infinite;
    }

    @keyframes harian-spin {
        to { transform: rotate(360deg); }
    }

    /* ============================================
       FORM COMPONENTS
       ============================================ */
    .harian-form-group {
        margin-bottom: var(--space-4);
    }

    .harian-form-label {
        display: block;
        margin-bottom: var(--space-2);
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--color-neutral-700);
    }

    .harian-form-control {
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

    .harian-form-control:focus {
        outline: none;
        border-color: var(--color-primary-500);
        box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.15);
    }

    .harian-form-control:hover {
        border-color: var(--color-neutral-400);
    }

    /* TP Input Group */
    .harian-tp-group {
        border: 1px solid var(--color-neutral-300);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-2);
        overflow: hidden;
        transition: all var(--transition-fast);
        background: #ffffff;
    }

    .harian-tp-group:focus-within {
        border-color: var(--color-primary-500);
        box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.1);
    }

    .harian-tp-label {
        background: var(--color-neutral-100);
        color: var(--color-neutral-600);
        padding: var(--space-2) var(--space-3);
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-xs);
        border-right: 1px solid var(--color-neutral-300);
        min-width: 45px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
    }

    .harian-tp-input {
        border: none;
        padding: var(--space-2) var(--space-4);
        font-size: var(--font-size-sm);
        width: 100%;
        outline: none;
        color: var(--color-neutral-700);
        background: transparent;
    }

    .harian-tp-input::placeholder {
        color: var(--color-neutral-400);
    }

    /* ============================================
       ALERT COMPONENTS
       ============================================ */
    .harian-alert {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        padding: var(--space-4) var(--space-5);
        border-radius: var(--radius-lg);
        border: 1px solid transparent;
    }

    .harian-alert__icon {
        flex-shrink: 0;
        font-size: var(--font-size-xl);
    }

    .harian-alert__content {
        flex: 1;
        font-size: var(--font-size-sm);
        line-height: var(--line-height-relaxed);
    }

    .harian-alert--danger {
        background-color: var(--color-danger-50);
        border-color: var(--color-danger-200);
        color: var(--color-danger-700);
    }

    .harian-alert--warning {
        background-color: var(--color-warning-50);
        border-color: var(--color-warning-200);
        color: var(--color-warning-700);
    }

    .harian-alert--success {
        background-color: var(--color-success-50);
        border-color: var(--color-success-200);
        color: var(--color-success-700);
    }

    .harian-alert--info {
        background-color: var(--color-primary-50);
        border-color: var(--color-primary-200);
        color: var(--color-primary-700);
    }

    /* ============================================
       STATS CARDS
       ============================================ */
    .harian-stats-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: var(--space-4);
        margin-bottom: var(--space-6);
    }

    @media (min-width: 768px) {
        .harian-stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .harian-stat-card {
        display: flex;
        flex-direction: column;
        padding: var(--space-5);
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-lg);
        transition: all var(--transition-base);
    }

    .harian-stat-card:hover {
        border-color: var(--color-neutral-300);
        box-shadow: var(--shadow-md);
    }

    .harian-stat-card__label {
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-neutral-500);
        margin-bottom: var(--space-2);
    }

    .harian-stat-card__value {
        font-size: var(--font-size-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--color-neutral-800);
        line-height: var(--line-height-tight);
    }

    .harian-stat-card__meta {
        font-size: var(--font-size-xs);
        color: var(--color-neutral-500);
        margin-top: var(--space-2);
    }

    .harian-stat-card--primary .harian-stat-card__value {
        color: var(--color-primary-600);
    }

    .harian-stat-card--success .harian-stat-card__value {
        color: var(--color-success-600);
    }

    /* Indicator Pills */
    .harian-indicator {
        display: inline-flex;
        align-items: center;
        padding: var(--space-1) var(--space-3);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 0.025em;
        border-radius: var(--radius-full);
        margin-bottom: var(--space-3);
    }

    .harian-indicator--p {
        background-color: var(--color-success-100);
        color: var(--color-success-700);
    }

    .harian-indicator--k {
        background-color: var(--color-primary-100);
        color: var(--color-primary-700);
    }

    /* Predikat Badges */
    .harian-badge {
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

    .harian-badge--danger {
        background-color: var(--color-danger-100);
        color: var(--color-danger-700);
    }

    .harian-badge--warning {
        background-color: var(--color-warning-100);
        color: var(--color-warning-700);
    }

    .harian-badge--info {
        background-color: var(--color-primary-100);
        color: var(--color-primary-700);
    }

    .harian-badge--success {
        background-color: var(--color-success-100);
        color: var(--color-success-700);
    }

    /* ============================================
       ACTION BAR
       ============================================ */
    .harian-action-bar {
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
        .harian-action-bar {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .harian-action-group {
        display: flex;
        flex-direction: column;
        gap: var(--space-3);
        flex: 1;
    }

    @media (min-width: 768px) {
        .harian-action-group {
            flex-direction: row;
            align-items: center;
        }
    }

    /* ============================================
       LOADING SKELETON
       ============================================ */
    .harian-skeleton {
        background: linear-gradient(90deg, var(--color-neutral-200) 25%, var(--color-neutral-100) 50%, var(--color-neutral-200) 75%);
        background-size: 200% 100%;
        animation: harian-skeleton-loading 1.5s ease-in-out infinite;
        border-radius: var(--radius-md);
    }

    @keyframes harian-skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .harian-skeleton--text {
        height: 1rem;
        margin-bottom: var(--space-2);
    }

    .harian-skeleton--title {
        height: 1.5rem;
        width: 60%;
        margin-bottom: var(--space-4);
    }

    .harian-skeleton--card {
        height: 8rem;
        margin-bottom: var(--space-4);
    }

    /* ============================================
       EMPTY STATE
       ============================================ */
    .harian-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: var(--space-12) var(--space-6);
        text-align: center;
    }

    .harian-empty-state__icon {
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

    .harian-empty-state__title {
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        color: var(--color-neutral-700);
        margin-bottom: var(--space-2);
    }

    .harian-empty-state__description {
        font-size: var(--font-size-sm);
        color: var(--color-neutral-500);
        max-width: 24rem;
        margin-bottom: var(--space-6);
    }

    /* ============================================
       STICKY SAVE BUTTON
       ============================================ */
    .harian-sticky-save {
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

    .harian-sticky-save__btn {
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

    .harian-sticky-save__btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 30px -5px rgb(0 0 0 / 0.15), 0 10px 15px -5px rgb(0 0 0 / 0.1);
    }

    .harian-sticky-save__btn:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgba(24, 119, 242, 0.3), var(--shadow-xl);
    }

    .harian-sticky-save__btn:disabled {
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

    .jexcel > thead > tr > td {
        background-color: var(--color-neutral-100) !important;
        color: var(--color-neutral-700) !important;
        font-weight: var(--font-weight-semibold) !important;
        padding: var(--space-3) !important;
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
    .harian-btn:focus-visible,
    .harian-form-control:focus-visible,
    .harian-tp-input:focus-visible,
    .harian-sticky-save__btn:focus-visible {
        outline: 2px solid var(--color-primary-500);
        outline-offset: 2px;
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .harian-btn--primary {
            border: 2px solid #ffffff;
        }
        
        .harian-card {
            border: 2px solid var(--color-neutral-800);
        }
    }

    /* Legacy compatibility */
    .bg-soft-info { background-color: var(--color-primary-100); color: var(--color-primary-700); }
    .bg-soft-success { background-color: var(--color-success-100); color: var(--color-success-700); }
    .bg-soft-warning { background-color: var(--color-warning-100); color: var(--color-warning-700); }

        .harian-skeleton--card {
            height: 8rem;
            margin-bottom: var(--space-4);
        }

        /* ============================================
           EMPTY STATE
           ============================================ */
        .harian-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-12) var(--space-6);
            text-align: center;
        }

        .harian-empty-state__icon {
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

        .harian-empty-state__title {
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--color-neutral-700);
            margin-bottom: var(--space-2);
        }

        .harian-empty-state__description {
            font-size: var(--font-size-sm);

    .harian-kkm-card__type {
        font-size: var(--font-size-xs);
        color: var(--color-neutral-500);
    }

    .harian-kkm-card__badges {
        display: flex;
        justify-content: center;
        gap: var(--space-2);
        flex-wrap: wrap;
        margin-top: var(--space-2);
    }

    /* ============================================
       PREDIKAT BADGES
       ============================================ */
    .harian-badge {
        display: inline-flex;
        align-items: center;
        padding: var(--space-2) var(--space-3);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        border-radius: var(--radius-full);
        white-space: nowrap;
    }

    .harian-badge--d {
        background-color: var(--color-danger-100);
        color: var(--color-danger-700);
    }

    .harian-badge--c {
        background-color: var(--color-warning-100);
        color: var(--color-warning-800);
    }

    .harian-badge--b {
        background-color: var(--color-info-100);
        color: var(--color-info-700);
    }
        .jexcel_container {
            border-radius: var(--radius-lg);
            border: 1px solid var(--color-neutral-200);
            overflow: hidden;
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
        .harian-btn:focus-visible,
        .harian-form-control:focus-visible,
        .harian-tp-input:focus-visible,
        .harian-sticky-save__btn:focus-visible {
            outline: 2px solid var(--color-primary-500);
            outline-offset: 2px;
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .harian-btn--primary {
                border: 2px solid #ffffff;
            }
            
            .harian-card {
                border: 2px solid var(--color-neutral-800);
            }
        }

        /* Legacy compatibility */
        .bg-soft-info { background-color: var(--color-primary-100); color: var(--color-primary-700); }
        .bg-soft-success { background-color: var(--color-success-100); color: var(--color-success-700); }
        .bg-soft-warning { background-color: var(--color-warning-100); color: var(--color-warning-700); }
        .bg-soft-danger { background-color: var(--color-danger-100); color: var(--color-danger-700); }
    </style>
    <?= $this->endSection() ?>

    <?= $this->section('content') ?>
    <section class="content">
        <div class="container-fluid">
            <div class="harian-card" id="mainContent">
                <div class="harian-card__header">
                    <h1 class="harian-card__title">
                        <span class="harian-card__title-icon" aria-hidden="true">
                            <i class="bi bi-journal-text"></i>
                        </span>
                        <span>Input Nilai Harian: <strong><?= esc($mapel['nama_mapel']) ?></strong></span>
                    </h1>
                    <div class="d-flex gap-2">
                        <button type="button" id="download-excel" class="harian-btn harian-btn--success harian-btn--sm"
                            data-bs-toggle="tooltip" title="Download Excel Nilai Harian"
                            aria-label="Ekspor data ke format Excel">
                            <i class="bi bi-file-excel" aria-hidden="true"></i>
                            <span>Ekspor Excel</span>
                        </button>
                        <button onclick="window.history.back();" type="button" class="harian-btn harian-btn--danger harian-btn--sm"
                            aria-label="Kembali ke halaman sebelumnya">
                            <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
                            <span>Kembali</span>
                        </button>
                    </div>
                </div>
                <div class="harian-card__body">
                    <!-- Loading Skeleton -->
                    <div id="loadingSkeletonCard1" class="d-none">
                        <div class="harian-skeleton harian-skeleton--title"></div>
                        <div class="harian-skeleton harian-skeleton--text" style="width: 80%;"></div>
                        <div class="harian-skeleton harian-skeleton--text" style="width: 60%;"></div>
                    </div>

                    <!-- Error State -->
                    <div id="errorStateCard1" class="harian-alert harian-alert--danger d-none" role="alert" aria-live="polite">
                        <i class="bi bi-exclamation-triangle-fill harian-alert__icon" aria-hidden="true"></i>
                        <div class="harian-alert__content">
                            <strong>Error!</strong> <span id="errorMessageCard1">Terjadi kesalahan saat memuat data.</span>
                        </div>
                    </div>

                    <div class="harian-action-bar" id="actionBarContent">
                        <?php if ($kkm == null): ?>
                            <div class="harian-alert harian-alert--danger w-100" role="alert">
                                <i class="bi bi-exclamation-triangle-fill harian-alert__icon" aria-hidden="true"></i>
                                <div class="harian-alert__content">
                                    Download template tidak tersedia, silakan isi <strong>KKM</strong> terlebih dahulu di menu 
                                    <b>DATA RAPOR > KKM DAN BOBOT</b>
                                </div>
                            </div>
                        <?php else: ?>
                            <button type="button" class="harian-btn harian-btn--primary" 
                                onclick="processDownload(<?= $mapel['id_mapel'] ?>, <?= $kelas['id_kelas'] ?>)"
                                aria-label="Download template Excel untuk input nilai">
                                <i class="bi bi-cloud-download" aria-hidden="true"></i>
                                <span>Download Template (.xlsx)</span>
                            </button>
                            
                            <form id="uploadfile" enctype="multipart/form-data" class="harian-action-group mb-0">
                                <?= csrf_field() ?>
                                <div class="flex-grow-1">
                                    <input type="file" name="upload_file" class="harian-form-control" id="custom-file"
                                        aria-label="Pilih file Excel untuk upload nilai"
                                        accept=".xlsx,.xls">
                                </div>
                                <button id="upload" class="harian-btn harian-btn--success" type="submit"
                                    aria-label="Upload file nilai harian">
                                    <i class="bi bi-cloud-upload" aria-hidden="true"></i>
                                    <span>Upload</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <form id="editkikd" class="harian-card">
            <?= csrf_field() ?>
            <div class="harian-card__header">
                <h2 class="harian-card__title">
                    <span class="harian-card__title-icon" aria-hidden="true">
                        <i class="bi bi-pencil-square"></i>
                    </span>
                    <span>Indikator Tujuan Pembelajaran</span>
                </h2>
            </div>
            
            <div class="harian-card__body">
                <!-- Loading Skeleton for TP section -->
                <div id="loadingSkeletonTP" class="d-none">
                    <div class="harian-skeleton harian-skeleton--title"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="harian-skeleton harian-skeleton--card"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="harian-skeleton harian-skeleton--card"></div>
                        </div>
                    </div>
                </div>

                <div id="tpContent">
                    <div class="harian-alert harian-alert--info" role="alert">
                        <i class="bi bi-info-circle-fill harian-alert__icon" aria-hidden="true"></i>
                        <div class="harian-alert__content">
                            Tuliskan <strong>Tujuan Pembelajaran (TP)</strong> yang dinilai pada setiap pertemuan. 
                            Narasi ini akan otomatis menjadi dasar deskripsi capaian kompetensi di Rapor.
                            Klik dan edit teks di bawah, lalu klik <strong>Simpan</strong>.
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Aspek Pengetahuan -->
                        <div class="col-md-6 mb-4">
                            <span class="harian-indicator harian-indicator--p">Aspek Pengetahuan (Kognitif)</span>
                            <?php for ($i = 0; $i < 8; $i++): 
                                $id_mapel_kelas = $mapel['id_mapel'] . $kelas['id_kelas'];
                                $id_kikd = $id_mapel_kelas . '1' . ($i + 1);
                                $materi = property_exists($kikd[1][$id_kikd] ?? new stdClass(), 'materi_kikd') ? $kikd[1][$id_kikd]->materi_kikd : ''; ?>
                                <div class="harian-tp-group d-flex">
                                    <div class="harian-tp-label" aria-hidden="true">P<?= $i+1 ?></div>
                                    <input type="text" class="harian-tp-input" 
                                        name="materi[1][<?= $id_mapel_kelas ?>][<?= $id_kikd ?>]" 
                                        value="<?= esc($materi) ?>" 
                                        placeholder="Input Tujuan Pembelajaran <?= $i+1 ?>..."
                                        aria-label="Tujuan Pembelajaran Pengetahuan <?= $i+1 ?>">
                                </div>
                            <?php endfor; ?>
                        </div>
                        
                        <!-- Aspek Keterampilan -->
                        <div class="col-md-6 mb-4">
                            <span class="harian-indicator harian-indicator--k">Aspek Keterampilan (Psikomotor)</span>
                            <?php for ($i = 0; $i < 8; $i++): 
                                $id_mapel_kelas = $mapel['id_mapel'] . $kelas['id_kelas'];
                                $id_kikd = $id_mapel_kelas . '2' . ($i + 1);
                                $materi = property_exists($kikd[2][$id_kikd] ?? new stdClass(), 'materi_kikd') ? $kikd[2][$id_kikd]->materi_kikd : ''; ?>
                                <div class="harian-tp-group d-flex">
                                    <div class="harian-tp-label" aria-hidden="true">K<?= $i+1 ?></div>
                                    <input type="text" class="harian-tp-input" 
                                        name="materi[2][<?= $id_mapel_kelas ?>][<?= $id_kikd ?>]" 
                                        value="<?= esc($materi) ?>" 
                                        placeholder="Input Tujuan Pembelajaran <?= $i+1 ?>..."
                                        aria-label="Tujuan Pembelajaran Keterampilan <?= $i+1 ?>">
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="harian-btn harian-btn--primary harian-btn--lg"
                            aria-label="Simpan indikator penilaian dan update deskripsi rapor">
                            <i class="bi bi-save2" aria-hidden="true"></i>
                            <span>Simpan Indikator & Update Deskripsi</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <?php $dnone = $kkm == null ? 'd-none' : ''; ?>
        <div class="harian-card <?= $dnone ?>" id="gradesCard">
            <div class="harian-card__header">
                <h2 class="harian-card__title">
                    <span class="harian-card__title-icon" aria-hidden="true">
                        <i class="bi bi-grid-3x3"></i>
                    </span>
                    <span>Pengisian Nilai Siswa</span>
                </h2>
            </div>
            
            <div class="harian-card__body">
                <!-- Loading Skeleton -->
                <div id="loadingSkeletonGrades" class="d-none">
                    <div class="harian-skeleton harian-skeleton--title"></div>
                    <div class="row mb-4">
                        <div class="col-md-3"><div class="harian-skeleton harian-skeleton--card"></div></div>
                        <div class="col-md-6"><div class="harian-skeleton harian-skeleton--card"></div></div>
                        <div class="col-md-3"><div class="harian-skeleton harian-skeleton--card"></div></div>
                    </div>
                    <div class="harian-skeleton harian-skeleton--card" style="height: 20rem;"></div>
                </div>

                <!-- Error State -->
                <div id="errorStateGrades" class="harian-alert harian-alert--danger d-none" role="alert" aria-live="polite">
                    <i class="bi bi-exclamation-triangle-fill harian-alert__icon" aria-hidden="true"></i>
                    <div class="harian-alert__content">
                        <strong>Error!</strong> <span id="errorMessageGrades">Terjadi kesalahan saat memuat data nilai.</span>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyStateTable" class="harian-empty-state d-none" role="status" aria-live="polite">
                    <div class="harian-empty-state__icon" aria-hidden="true">
                        <i class="bi bi-inbox fs-1"></i>
                    </div>
                    <h3 class="harian-empty-state__title">Belum Ada Data Siswa</h3>
                    <p class="harian-empty-state__description">
                        Tidak ada data siswa untuk ditampilkan. Silakan tambahkan siswa ke kelas ini terlebih dahulu.
                    </p>
                </div>

                <div id="gradesContent">
                    <?php if ($kkm != null): 
                        $multi = $setting_rapor->kkm_tunggal == "0" ? "Multi KKM" : "KKM Tunggal";
                        $isi = $kkm->kkm;
                        $dsd = $isi - 1;
                        $c = $isi;
                        $csd = floor($isi + (100 - $isi) / 3);
                        $b = $csd + 1;
                        $bsd = floor($b + (100 - $b) / 2);
                        $a = $bsd + 1;
                    ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="harian-kkm-card">
                                <div class="harian-kkm-card__label">KKM Utama</div>
                                <div class="harian-kkm-card__value harian-kkm-card__value--primary"><?= $kkm->kkm ?></div>
                                <div class="harian-kkm-card__type">Jenis: <?= $multi ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="harian-kkm-card">
                                <div class="harian-kkm-card__label">Interval Predikat Berdasarkan KKM</div>
                                <div class="harian-kkm-card__badges">
                                    <span class="harian-badge harian-badge--d">0 - <?= $dsd ?> (D)</span>
                                            <span class="harian-badge harian-badge--c"><?= $c ?> - <?= $csd ?> (C)</span>
                                            <span class="harian-badge harian-badge--b"><?= $b ?> - <?= $bsd ?> (B)</span>
                                            <span class="harian-badge harian-badge--a"><?= $a ?> - 100 (A)</span>
                                        </div>
                                    </div>
                        </div>
                        <div class="col-md-3">
                            <div class="harian-kkm-card">
                                <div class="harian-kkm-card__label">Bobot Harian</div>
                                <div class="harian-kkm-card__value harian-kkm-card__value--success"><?= $kkm->bobot_ph ?>%</div>
                                <div class="harian-kkm-card__type">Digunakan untuk Nilai Rapor</div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div id="t-siswa" class="w-100 jspreadsheet-modern" role="region" aria-label="Tabel pengisian nilai siswa"></div>
                    
                    <!-- Sticky Save Button -->
                    <form id="uploadnilai">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_mapel" value="<?= $mapel['id_mapel'] ?>">
                        <input type="hidden" name="id_kelas" value="<?= $kelas['id_kelas'] ?>">
                        <div class="harian-sticky-save">
                            <button type="submit" class="harian-sticky-save__btn"
                                aria-label="Simpan semua nilai siswa">
                                <i class="bi bi-save" aria-hidden="true"></i>
                                <span>SIMPAN NILAI</span>
                            </button>
                        </div>
                        <div id="for-upload" class="d-none row"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<form id="uploadharian">
    <?= csrf_field() ?>
    <input type="hidden" name="id_mapel" class="form-control" value="<?= $mapel['id_mapel'] ?>">
    <input type="hidden" name="id_kelas" class="form-control" value="<?= $kelas['id_kelas'] ?>">
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

    var tpActive = '<?= $tp_active->id_tp ?>';
    var smtActive = '<?= $smt_active->id_smt ?>';
    var arrSiswa = <?= json_encode($siswa) ?>;
    var arrNilai = <?= json_encode($nilai) ?>;
    var arrKiKd = <?= json_encode($kikd) ?>;
    var kkm = <?= json_encode($kkm) ?>;
    var idMapel = '<?= $mapel['id_mapel'] ?>';
    var idKelas = '<?= $kelas['id_kelas'] ?>';
    var namaMapel = '<?= $mapel['nama_mapel'] ?>';
    var namaKelas = '<?= $kelas['nama_kelas'] ?>';

    var isi = 0;
    if (kkm != null) {
        isi = parseInt(kkm.kkm);
    }
    var pre_d = 0;
    var pre_dsd = isi - 1;
    var pre_c = isi;
    var pre_csd = Math.floor(isi + (100 - isi) / 3);
    var pre_b = pre_csd + 1;
    var pre_bsd = Math.floor(pre_b + (100 - pre_b) / 2);
    var pre_a = pre_bsd + 1;
    var pre_asd = 100;

    var arrIndic1 = [];
    for (let i = 0; i < 8; i++) {
        var asp1 = arrKiKd[1];
        var idP = 'P' + (i + 1);
        var key = idMapel + idKelas + '1' + (i + 1);
        if (asp1[key] != null && asp1[key].materi_kikd !== '' && asp1[key].materi_kikd !== undefined) {
            idP = asp1[key].materi_kikd;
        }
        arrIndic1.push(idP);
    }
    var arrIndic2 = [];
    for (let i = 0; i < 8; i++) {
        var asp2 = arrKiKd[2];
        var idK = 'K' + (i + 1);
        var key = idMapel + idKelas + '2' + (i + 1);
        if (asp2[key] != null && asp2[key].materi_kikd !== '' && asp2[key].materi_kikd !== undefined) {
            idK = asp2[key].materi_kikd;
        }
        arrIndic2.push(idK);
    }

    $(function () {
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }
    });

    function inRange(n, start, end) {
        return n >= start && n <= end;
    }

    function sortByNilai(arr, key) {
        return arr.sort(function (a, b) {
            var x = a[key];
            var y = b[key];
            return ((x > y) ? -1 : ((x < y) ? 1 : 0));
        });
    }

    function preCalcKetr(n1, n2, n3, pos, posdesk) {
        var res = '';
        if (n2 != null) {
            if (inRange(n2, pre_a, pre_asd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_a, pre_asd)) {
                        if (inRange(n3, pre_a, pre_asd)) {
                            res += ', ' + arrIndic2[posdesk];
                        } else {
                            res += ' dan ' + arrIndic2[posdesk];
                        }
                    } else {
                        res += ', sangat baik kemampuan dalam ' + arrIndic2[posdesk];
                    }
                } else {
                    res += ' sangat baik dalam ' + arrIndic2[posdesk];
                }
            } else if (inRange(n2, pre_b, pre_bsd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_b, pre_bsd)) {
                        if (inRange(n3, pre_b, pre_bsd)) {
                            res += ', ' + arrIndic2[posdesk];
                        } else {
                            res += ' dan ' + arrIndic2[posdesk];
                        }
                    } else {
                        res += ', baik kemampuan dalam ' + arrIndic2[posdesk];
                    }
                } else {
                    res += ' baik dalam ' + arrIndic2[posdesk];
                }
            } else if (inRange(n2, pre_c, pre_csd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_c, pre_csd)) {
                        if (inRange(n3, pre_c, pre_csd)) {
                            res += ', ' + arrIndic2[posdesk];
                        } else {
                            res += ' dan ' + arrIndic2[posdesk];
                        }
                    } else {
                        res += ', cukup kemampuan dalam ' + arrIndic2[posdesk];
                    }
                } else {
                    res += ' cukup dalam ' + arrIndic2[posdesk];
                }
            } else if (inRange(n2, pre_d, pre_dsd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_d, pre_dsd)) {
                        if (inRange(n3, pre_d, pre_dsd)) {
                            res += ', ' + arrIndic2[posdesk];
                        } else {
                            res += ' dan ' + arrIndic2[posdesk];
                        }
                    } else {
                        res += ', kurang kemampuan dalam ' + arrIndic2[posdesk];
                    }
                } else {
                    res += ' kurang dalam ' + arrIndic2[posdesk];
                }
            }
        }

        if (n3 == null) {
            res += '.';
        }
        return res;
    }

    function preCalcPeng(n1, n2, n3, pos, posdesk) {
        var res = '';
        if (n2 != null) {
            if (inRange(n2, pre_a, pre_asd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_a, pre_asd)) {
                        if (inRange(n3, pre_a, pre_asd)) {
                            res += ', ' + arrIndic1[posdesk];
                        } else {
                            res += ' dan ' + arrIndic1[posdesk];
                        }
                    } else {
                        res += ', sangat baik kemampuan dalam ' + arrIndic1[posdesk];
                    }
                } else {
                    res += ' sangat baik dalam ' + arrIndic1[posdesk];
                }
            } else if (inRange(n2, pre_b, pre_bsd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_b, pre_bsd)) {
                        if (inRange(n3, pre_b, pre_bsd)) {
                            res += ', ' + arrIndic1[posdesk];
                        } else {
                            res += ' dan ' + arrIndic1[posdesk];
                        }
                    } else {
                        res += ', baik kemampuan dalam ' + arrIndic1[posdesk];
                    }
                } else {
                    res += ' baik dalam ' + arrIndic1[posdesk];
                }
            } else if (inRange(n2, pre_c, pre_csd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_c, pre_csd)) {
                        if (inRange(n3, pre_c, pre_csd)) {
                            res += ', ' + arrIndic1[posdesk];
                        } else {
                            res += ' dan ' + arrIndic1[posdesk];
                        }
                    } else {
                        res += ', cukup kemampuan dalam ' + arrIndic1[posdesk];
                    }
                } else {
                    res += ' cukup dalam ' + arrIndic1[posdesk];
                }
            } else if (inRange(n2, pre_d, pre_dsd)) {
                if (pos > 0) {
                    if (inRange(n1, pre_d, pre_dsd)) {
                        if (inRange(n3, pre_d, pre_dsd)) {
                            res += ', ' + arrIndic1[posdesk];
                        } else {
                            res += ' dan ' + arrIndic1[posdesk];
                        }
                    } else {
                        res += ', kurang kemampuan dalam ' + arrIndic1[posdesk];
                    }
                } else {
                    res += ' kurang dalam ' + arrIndic1[posdesk];
                }
            }
        }

        if (n3 == null) {
            res += '.';
        }
        return res;
    }

    function setDeskPengetahuan(desk) {
        var cols = [];
        for (let j = 0; j < desk.length; j++) {
            var item = {};
            item['pos'] = j;
            item['nilai'] = desk[j];
            item['desk'] = arrIndic1[j];
            cols.push(item)
        }
        cols = sortByNilai(cols, 'nilai');
        cols = $.grep(cols, function (n, i) {
            return (n != null && n.nilai !== '');
        });

        var result = cols.length > 1 ? 'Memiliki kemampuan' : '';
        if (cols.length > 0) {
            var count = cols.length > 2 ? 2 : cols.length;
            for (let i = 0; i < count; i++) {
                if (cols[i].nilai != null && cols[i].nilai !== '' && cols[i].nilai > 0) {
                    if (i === 0) {
                        if (cols[i + 1] != null) {
                            result += preCalcPeng(null, cols[i].nilai, cols[i + 1].nilai, i, cols[i].pos);
                        } else {
                            result += preCalcPeng(null, cols[i].nilai, null, i, cols[i].pos);
                        }
                    } else if (i === cols.length - 1) {
                        if (cols[i - 1] != null) {
                            result += preCalcPeng(cols[i - 1].nilai, cols[i].nilai, null, i, cols[i].pos);
                        }
                    } else {
                        if (cols[i - 1] != null && cols[i + 1] != null) {
                            result += preCalcPeng(cols[i - 1].nilai, cols[i].nilai, cols[i + 1].nilai, i, cols[i].pos);
                        }
                    }
                }
            }
        }
        return result;
    }

    function setDeskKeterampilan(desk) {
        var cols = [];
        for (let j = 0; j < desk.length; j++) {
            var item = {};
            item['pos'] = j;
            item['nilai'] = desk[j];
            item['desk'] = arrIndic2[j];
            cols.push(item)
        }
        cols = sortByNilai(cols, 'nilai');
        cols = $.grep(cols, function (n, i) {
            return (n != null && n.nilai !== '');
        });

        var result = cols.length > 1 ? 'Memiliki keterampilan' : '';
        if (cols.length > 0) {
            var count = cols.length > 2 ? 2 : cols.length;
            for (let i = 0; i < count; i++) {
                if (cols[i].nilai != null && cols[i].nilai !== '' && cols[i].nilai > 0) {
                    if (i === 0) {
                        if (cols[i + 1] != null) {
                            result += preCalcKetr(null, cols[i].nilai, cols[i + 1].nilai, i, cols[i].pos);
                        } else {
                            result += preCalcKetr(null, cols[i].nilai, null, i, cols[i].pos);
                        }
                    } else if (i === cols.length - 1) {
                        if (cols[i - 1] != null) {
                            result += preCalcKetr(cols[i - 1].nilai, cols[i].nilai, null, i, cols[i].pos);
                        }
                    } else {
                        if (cols[i - 1] != null && cols[i + 1] != null) {
                            result += preCalcKetr(cols[i - 1].nilai, cols[i].nilai, cols[i + 1].nilai, i, cols[i].pos);
                        }
                    }
                }
            }
        }
        return result;
    }

    // Helper: hitung nilai dari array, abaikan string kosong
    function calcValues(arr) {
        var nums = [];
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] !== '' && arr[i] !== null && arr[i] !== undefined) {
                var n = Number(arr[i]);
                if (!isNaN(n)) nums.push(n);
            }
        }
        return nums;
    }

    function countNonEmpty(arr) {
        var c = 0;
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] !== '' && arr[i] !== null && arr[i] !== undefined) c++;
        }
        return c;
    }

    function calcRata(arr) {
        var nums = calcValues(arr);
        if (nums.length < 2) return '';
        var sum = 0;
        for (var i = 0; i < nums.length; i++) sum += nums[i];
        return Math.round(sum / nums.length);
    }

    function calcPredikat(rata) {
        if (rata === '' || rata === null || rata === undefined) return '';
        var n = Number(rata);
        if (isNaN(n)) return '';
        if (n > pre_bsd) return 'A';
        if (n > pre_csd) return 'B';
        if (n > pre_dsd) return 'C';
        if (n < pre_c) return 'D';
        return '';
    }

    function calcSum(arr) {
        var nums = calcValues(arr);
        var sum = 0;
        for (var i = 0; i < nums.length; i++) sum += nums[i];
        return sum;
    }

    $(document).ready(function () {
        var dataSiswa = [];
        var row = 1;
        $.each(arrSiswa, function (i, v) {
            var noInduk = v.nisn == null || v.nisn == '' ? v.nis : v.nisn;
            var nilai = arrNilai[v.id_siswa] || {};
            var p1 = nilai.p1 == '0' ? '' : (nilai.p1 || '');
            var p2 = nilai.p2 == '0' ? '' : (nilai.p2 || '');
            var p3 = nilai.p3 == '0' ? '' : (nilai.p3 || '');
            var p4 = nilai.p4 == '0' ? '' : (nilai.p4 || '');
            var p5 = nilai.p5 == '0' ? '' : (nilai.p5 || '');
            var p6 = nilai.p6 == '0' ? '' : (nilai.p6 || '');
            var p7 = nilai.p7 == '0' ? '' : (nilai.p7 || '');
            var p8 = nilai.p8 == '0' ? '' : (nilai.p8 || '');

            var k1 = nilai.k1 == '0' ? '' : (nilai.k1 || '');
            var k2 = nilai.k2 == '0' ? '' : (nilai.k2 || '');
            var k3 = nilai.k3 == '0' ? '' : (nilai.k3 || '');
            var k4 = nilai.k4 == '0' ? '' : (nilai.k4 || '');
            var k5 = nilai.k5 == '0' ? '' : (nilai.k5 || '');
            var k6 = nilai.k6 == '0' ? '' : (nilai.k6 || '');
            var k7 = nilai.k7 == '0' ? '' : (nilai.k7 || '');
            var k8 = nilai.k8 == '0' ? '' : (nilai.k8 || '');

            var pArr = [p1, p2, p3, p4, p5, p6, p7, p8];
            var kArr = [k1, k2, k3, k4, k5, k6, k7, k8];

            // Hitung kolom formula secara JS
            var pJml = countNonEmpty(pArr);
            var pRata = calcRata(pArr);
            var pPred = calcPredikat(pRata);
            var pSum = calcSum(pArr);

            var kJml = countNonEmpty(kArr);
            var kRata = calcRata(kArr);
            var kPred = calcPredikat(kRata);
            var kSum = calcSum(kArr);

            var totalSum = pSum + kSum;

            dataSiswa.push(
                [
                    noInduk, v.nama, p1, p2, p3, p4, p5, p6, p7, p8,
                    pJml,
                    pRata,
                    pPred,
                    setDeskPengetahuan(pArr),
                    k1, k2, k3, k4, k5, k6, k7, k8,
                    kJml,
                    kRata,
                    kPred,
                    setDeskKeterampilan(kArr),
                    v.id_siswa,
                    pSum,
                    kSum,
                    totalSum
                ]
            );
            row++;
        });

        var arrCol = [];
        var char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var pno = 1;
        var kno = 1;

        for (let i = 0; i < 30; i++) {
            var item = {};
            // Default readOnly
            item['readOnly'] = true;

            if (i === 0) {
                item['title'] = 'NIS/NISN';
                item['width'] = 100;
            } else if (i === 1) {
                item['title'] = 'NAMA SISWA';
                item['width'] = 250;
            } else if (i >= 2 && i <= 9) {
                item['title'] = 'P-' + pno;
                item['width'] = 45;
                item['readOnly'] = false; // Editable
                pno++;
            } else if (i === 10) {
                item['title'] = 'JML';
                item['width'] = 45;
            } else if (i === 11) {
                item['title'] = 'RATA';
                item['width'] = 50;
            } else if (i === 12) {
                item['title'] = 'PRED';
                item['width'] = 50;
            } else if (i === 13) {
                item['title'] = 'DESKRIPSI';
                item['width'] = 200;
                item['wordWrap'] = true;
            } else if (i >= 14 && i <= 21) {
                item['title'] = 'K-' + kno;
                item['width'] = 45;
                item['readOnly'] = false; // Editable
                kno++;
            } else if (i === 22) {
                item['title'] = 'JML';
                item['width'] = 45;
            } else if (i === 23) {
                item['title'] = 'RATA';
                item['width'] = 50;
            } else if (i === 24) {
                item['title'] = 'PRED';
                item['width'] = 50;
            } else if (i === 25) {
                item['title'] = 'DESKRIPSI';
                item['width'] = 200;
                item['wordWrap'] = true;
            } else if (i === 26) {
                item['title'] = 'ID';
                item['width'] = 1;
            } else if (i > 26) {
                item['width'] = 1;
            }
            arrCol.push(item);
        }

        // Fungsi recalculate baris saat nilai P/K diubah di spreadsheet
        function recalcRow(ws, rowIdx) {
            var rowData = ws.getRowData(rowIdx);
            var pArr = rowData.slice(2, 10);  // P1-P8 (col 2-9)
            var kArr = rowData.slice(14, 22); // K1-K8 (col 14-21)

            var pJml = countNonEmpty(pArr);
            var pRata = calcRata(pArr);
            var pPred = calcPredikat(pRata);
            var pSum = calcSum(pArr);
            var pDesk = setDeskPengetahuan(pArr);

            var kJml = countNonEmpty(kArr);
            var kRata = calcRata(kArr);
            var kPred = calcPredikat(kRata);
            var kSum = calcSum(kArr);
            var kDesk = setDeskKeterampilan(kArr);

            var totalSum = pSum + kSum;

            // Update kolom-kolom kalkulasi tanpa trigger onchange lagi
            ws.setValueFromCoords(10, rowIdx, pJml, true);
            ws.setValueFromCoords(11, rowIdx, pRata, true);
            ws.setValueFromCoords(12, rowIdx, pPred, true);
            ws.setValueFromCoords(13, rowIdx, pDesk, true);
            ws.setValueFromCoords(22, rowIdx, kJml, true);
            ws.setValueFromCoords(23, rowIdx, kRata, true);
            ws.setValueFromCoords(24, rowIdx, kPred, true);
            ws.setValueFromCoords(25, rowIdx, kDesk, true);
            ws.setValueFromCoords(27, rowIdx, pSum, true);
            ws.setValueFromCoords(28, rowIdx, kSum, true);
            ws.setValueFromCoords(29, rowIdx, totalSum, true);
        }

        var container = document.getElementById('t-siswa');
        if (container) {
            container.innerHTML = '';
            var spreadsheet = jspreadsheet(container, {
                worksheets: [{
                    data: dataSiswa,
                    minDimensions: [30, 10],
                    search: true,
                    freezeColumns: 2,
                    columnResize: false,
                    columns: arrCol.map(c => ({ ...c, type: 'text' })),
                    nestedHeaders: [
                        [
                            {
                                title: 'IDENTITAS SISWA',
                                colspan: 2,
                            },
                            {
                                title: 'NILAI PENGETAHUAN',
                                colspan: 12,
                            },
                            {
                                title: 'NILAI KETERAMPILAN',
                                colspan: 12,
                            },
                            {
                                title: 'SYSTEM',
                                colspan: 4,
                            },
                        ],
                    ],
                    onchange: function (instance, cell, col, row, value) {
                        col = parseInt(col);
                        row = parseInt(row);
                        // Recalculate jika P1-P8 (col 2-9) atau K1-K8 (col 14-21) diubah
                        if ((col >= 2 && col <= 9) || (col >= 14 && col <= 21)) {
                            var ws = instance;
                            if (spreadsheet && spreadsheet.worksheets) {
                                ws = spreadsheet.worksheets[0];
                            } else if (Array.isArray(spreadsheet)) {
                                ws = spreadsheet[0];
                            } else if (container.jspreadsheet) {
                                ws = container.jspreadsheet[0];
                            }
                            recalcRow(ws, row);
                        }
                    },
                    updateTable: function (instance, cell, col, row, val, label, cellName) {
                        // Identitas siswa (NIS, Nama)
                        if (col === 0) {
                            cell.style.backgroundColor = '#e8eaf6';
                            cell.style.textAlign = 'center';
                            cell.style.color = '#283593';
                            cell.style.fontWeight = '500';
                            cell.classList.add('readonly');
                        }
                        if (col === 1) {
                            cell.style.backgroundColor = '#e8eaf6';
                            cell.style.textAlign = 'left';
                            cell.style.color = '#283593';
                            cell.style.fontWeight = '500';
                            cell.classList.add('readonly');
                        }
                        // Input nilai P1-P8 dan K1-K8 (editable)
                        if (col >= 2 && col <= 9 || col >= 14 && col <= 21) {
                            cell.style.backgroundColor = '#fff9c4';
                            cell.style.textAlign = 'center';
                        }
                        // JML kolom (col 10 Pengetahuan, col 22 Keterampilan)
                        if (col === 10 || col === 22) {
                            cell.style.backgroundColor = '#eceff1';
                            cell.style.color = '#546e7a';
                            cell.style.textAlign = 'center';
                            cell.classList.add('readonly');
                        }
                        // RATA kolom (col 11 Pengetahuan, col 23 Keterampilan)
                        if (col === 11 || col === 23) {
                            cell.style.backgroundColor = '#e3f2fd';
                            cell.style.color = '#1565c0';
                            cell.style.fontWeight = '700';
                            cell.style.textAlign = 'center';
                            cell.classList.add('readonly');
                        }
                        // PREDIKAT kolom (col 12 Pengetahuan, col 24 Keterampilan) - warna dinamis
                        if (col === 12 || col === 24) {
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
                        // DESKRIPSI kolom (col 13 Pengetahuan, col 25 Keterampilan)
                        if (col === 13 || col === 25) {
                            cell.style.backgroundColor = '#fff8e1';
                            cell.style.fontSize = '11px';
                            cell.style.textAlign = 'left';
                            cell.style.color = '#5d4037';
                            cell.classList.add('readonly');
                        }
                        // Hidden columns (ID, SUM)
                        if (col === 26 || col === 27 || col === 28 || col === 29) {
                            cell.style.backgroundColor = '#fafafa';
                            cell.classList.add('readonly');
                        }
                    }
                }]
            });
        }

        $('#editkikd').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "rapor/savekikd",
                type: 'POST',
                data: $(this).serialize(),
                success: function (data) {
                    swal.fire({
                        title: "Sukses",
                        text: "Indikator Penilaian berhasil disimpan",
                        icon: "success",
                        showCancelButton: false,
                    }).then(result => {
                        location.reload();
                    });
                },
                error: function (xhr, error, status) {
                    console.log(xhr.responseText);
                    swal.fire("Gagal", "Simpan Indikator Penilaian gagal", "error");
                }
            });
        });

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
            } else if (spreadsheet && spreadsheet.worksheets) {
                data = spreadsheet.worksheets[0].getData();
            } else if (spreadsheet && typeof spreadsheet.getData === 'function') {
                data = spreadsheet.getData();
            } else {
                data = container.jspreadsheet[0].getData();
            }
            var mapel = $(this).find('input[name="id_mapel"]').val();
            var kelas = $(this).find('input[name="id_kelas"]').val();

            var siswa = [];
            $.each(data, function (i, v) {
                if (v[26] != '') {
                    siswa.push({
                        id: v[26],
                        p1: v[2], p2: v[3], p3: v[4], p4: v[5], p5: v[6], p6: v[7], p7: v[8], p8: v[9],
                        p_rata_rata: v[11], p_predikat: v[12], p_deskripsi: v[13],
                        k1: v[14], k2: v[15], k3: v[16], k4: v[17], k5: v[18], k6: v[19], k7: v[20], k8: v[21],
                        k_rata_rata: v[23], k_predikat: v[24], k_deskripsi: v[25]
                    });
                }
            });

            $.ajax({
                url: base_url + "rapor/uploadnilaiharian",
                type: 'POST',
                data: { siswa: siswa, id_mapel: mapel, id_kelas: kelas },
                success: function (data) {
                    swal.fire({
                        title: "Sukses",
                        text: "Nilai berhasil disimpan",
                        icon: "success",
                        showCancelButton: false,
                    }).then(result => {
                        location.reload();
                    });
                },
                error: function (xhr, error, status) {
                    console.log(xhr.responseText);
                    swal.fire("Gagal", "Simpan nilai gagal", "error");
                }
            });
        });
    });

    async function processDownload(idMapel, idKelas) {
        window.location.href = base_url + 'rapor/downloadtemplate_harian/' + idMapel + '/' + idKelas;
    }

    // ============================================
    // UI STATE MANAGEMENT FUNCTIONS
    // ============================================

    /**
     * Show loading skeleton for a specific section
     * @param {string} skeletonId - ID of the skeleton element to show
     * @param {string} contentId - ID of the content element to hide (optional)
     * @param {string} errorId - ID of the error element to hide (optional)
     */
    function showLoadingState(skeletonId, contentId, errorId) {
        $('#' + skeletonId).removeClass('d-none').attr('aria-busy', 'true');
        if (contentId) $('#' + contentId).addClass('d-none');
        if (errorId) $('#' + errorId).addClass('d-none');

        // Announce to screen readers
        announceToScreenReader('Memuat data, mohon tunggu...');
    }

    /**
     * Hide loading skeleton and show content
     * @param {string} skeletonId - ID of the skeleton element to hide
     * @param {string} contentId - ID of the content element to show
     */
    function hideLoadingState(skeletonId, contentId) {
        $('#' + skeletonId).addClass('d-none').attr('aria-busy', 'false');
        if (contentId) $('#' + contentId).removeClass('d-none');
    }

    /**
     * Show error state with custom message
     * @param {string} errorId - ID of the error element to show
     * @param {string} messageId - ID of the message span element
     * @param {string} message - Error message to display
     * @param {number} autoHide - Auto hide delay in ms (0 for no auto-hide)
     */
    function showErrorState(errorId, messageId, message, autoHide = 5000) {
        if (messageId) $('#' + messageId).text(message || 'Terjadi kesalahan saat memuat data.');
        $('#' + errorId).removeClass('d-none').attr('aria-live', 'assertive');

        // Announce to screen readers
        announceToScreenReader('Error: ' + (message || 'Terjadi kesalahan saat memuat data.'));

        // Auto hide after specified delay
        if (autoHide > 0) {
            setTimeout(() => {
                $('#' + errorId).fadeOut(300, function() {
                    $(this).addClass('d-none').show();
                });
            }, autoHide);
        }
    }

    /**
     * Hide error state
     * @param {string} errorId - ID of the error element to hide
     */
    function hideErrorState(errorId) {
        $('#' + errorId).addClass('d-none').attr('aria-live', 'polite');
    }

    /**
     * Show empty state for empty data
     * @param {string} emptyId - ID of the empty state element
     * @param {string} contentId - ID of the content element to hide
     */
    function showEmptyState(emptyId, contentId) {
        $('#' + emptyId).removeClass('d-none');
        if (contentId) $('#' + contentId).addClass('d-none');

        announceToScreenReader('Tidak ada data untuk ditampilkan');
    }

    /**
     * Hide empty state and show content
     * @param {string} emptyId - ID of the empty state element
     * @param {string} contentId - ID of the content element to show
     */
    function hideEmptyState(emptyId, contentId) {
        $('#' + emptyId).addClass('d-none');
        if (contentId) $('#' + contentId).removeClass('d-none');
    }

    /**
     * Announce message to screen readers
     * @param {string} message - Message to announce
     */
    function announceToScreenReader(message) {
        // Create or update live region for screen reader announcements
        let liveRegion = document.getElementById('sr-announcements');
        if (!liveRegion) {
            liveRegion = document.createElement('div');
            liveRegion.id = 'sr-announcements';
            liveRegion.setAttribute('role', 'status');
            liveRegion.setAttribute('aria-live', 'polite');
            liveRegion.setAttribute('aria-atomic', 'true');
            liveRegion.className = 'sr-only';
            liveRegion.style.cssText = 'position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;';
            document.body.appendChild(liveRegion);
        }
        liveRegion.textContent = message;

        // Clear after announcement
        setTimeout(() => {
            liveRegion.textContent = '';
        }, 1000);
    }

    /**
     * Validate form before submission
     * @param {string} formSelector - jQuery selector for the form
     * @returns {boolean} - True if valid, false otherwise
     */
    function validateForm(formSelector) {
        const form = $(formSelector);
        let isValid = true;
        let firstInvalid = null;

        // Check required inputs
        form.find('input[required], textarea[required]').each(function() {
            const input = $(this);
            if (!input.val().trim()) {
                isValid = false;
                input.addClass('is-invalid');
                if (!firstInvalid) firstInvalid = input;
            } else {
                input.removeClass('is-invalid');
            }
        });

        // Focus first invalid input
        if (firstInvalid) {
            firstInvalid.focus();
            announceToScreenReader('Form tidak valid. Mohon periksa field yang ditandai.');
        }

        return isValid;
    }

    /**
     * Set button loading state
     * @param {string} buttonSelector - jQuery selector for the button
     * @param {boolean} isLoading - True to set loading state, false to restore
     * @param {string} originalText - Original button text
     */
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

        // Show error in appropriate section
        showErrorState('errorStateCard1', 'errorMessageCard1', errorMessage);
        showErrorState('errorStateGrades', 'errorMessageGrades', errorMessage);
    });

    // Initialize page with loading states
    $(document).ready(function() {
        // Show initial loading state for grades section if data is being loaded
        if (arrSiswa && arrSiswa.length === 0) {
            showEmptyState('emptyStateTable', 'gradesContent');
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
                    showErrorState('errorStateCard1', 'errorMessageCard1', 'File harus berformat .xlsx atau .xls');
                    this.value = '';
                } else {
                    $(this).removeClass('is-invalid');
                    hideErrorState('errorStateCard1');
                }
            }
        });

        // Upload form validation
        $('#uploadfile').on('submit', function(e) {
            const fileInput = $('#custom-file')[0];
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                showErrorState('errorStateCard1', 'errorMessageCard1', 'Silakan pilih file terlebih dahulu');
                fileInput.focus();
                return false;
            }
        });
    });

</script>
<?= $this->endSection() ?>