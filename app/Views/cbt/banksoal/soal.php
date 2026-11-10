<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Default values
$soal_belum_komplit = $soal_belum_komplit ?? [];
$soal_ada = $soal_ada ?? [];
$bank = $bank ?? (object) [];
$tab_active = $tab_active ?? '1';
$p_no = $p_no ?? '1';
$soal = $soal ?? null;
$setting = (new \App\Models\SettingsModel())->getSetting();
// Penonaktifan AI di setting hanya berlaku untuk guru/panitia; admin tetap bisa pakai AI.
$isAdminAi = isset($is_admin) ? (bool) $is_admin : ((string) (session()->get('role') ?? '') === '1' || strtolower((string) (session()->get('role_name') ?? '')) === 'admin');
$canUseAi = (!isset($setting->enable_ai) || $setting->enable_ai == 1) || $isAdminAi;

$pg_belum_komplit = [];
$pg2_belum_komplit = [];
$jodohkan_belum_komplit = [];
$isian_belum_komplit = [];
$essai_belum_komplit = [];
$urutan_belum_komplit = [];
$cloze_belum_komplit = [];

if (is_array($soal_belum_komplit)) {
    foreach ($soal_belum_komplit as $value) {
        $jenis = is_object($value) ? $value->jenis : ($value['jenis'] ?? '');
        $nomor = is_object($value) ? $value->nomor_soal : ($value['nomor_soal'] ?? '');
        if ($jenis == '1') {
            $pg_belum_komplit[] = $nomor;
        } elseif ($jenis == '2') {
            $pg2_belum_komplit[] = $nomor;
        } elseif ($jenis == '3') {
            $jodohkan_belum_komplit[] = $nomor;
        } elseif ($jenis == '4') {
            $isian_belum_komplit[] = $nomor;
        } elseif ($jenis == '5') {
            $essai_belum_komplit[] = $nomor;
        } elseif ($jenis == '6') {
            $urutan_belum_komplit[] = $nomor;
        } elseif ($jenis == '7') {
            $cloze_belum_komplit[] = $nomor;
        }
    }
}

$soal_pg_ada = [];
$soal_pg2_ada = [];
$soal_jodohkan_ada = [];
$soal_isian_ada = [];
$soal_esai_ada = [];
$soal_urutan_ada = [];
$soal_cloze_ada = [];

if (is_array($soal_ada)) {
    foreach ($soal_ada as $value) {
        $jenis = is_object($value) ? $value->jenis : ($value['jenis'] ?? '');
        $nomor = is_object($value) ? $value->nomor_soal : ($value['nomor_soal'] ?? '');
        if ($jenis == '1') {
            $soal_pg_ada[] = $nomor;
        } elseif ($jenis == '2') {
            $soal_pg2_ada[] = $nomor;
        } elseif ($jenis == '3') {
            $soal_jodohkan_ada[] = $nomor;
        } elseif ($jenis == '4') {
            $soal_isian_ada[] = $nomor;
        } elseif ($jenis == '5') {
            $soal_esai_ada[] = $nomor;
        } elseif ($jenis == '6') {
            $soal_urutan_ada[] = $nomor;
        } elseif ($jenis == '7') {
            $soal_cloze_ada[] = $nomor;
        }
    }
}
?>

<style>
    /* ============================================
       DESIGN TOKENS - CSS Variables
       ============================================ */
    :root {
        /* Primary Colors */
        --qs-primary-50: #eff6ff;
        --qs-primary-100: #dbeafe;
        --qs-primary-200: #bfdbfe;
        --qs-primary-300: #93c5fd;
        --qs-primary-400: #60a5fa;
        --qs-primary-500: #3b82f6;
        --qs-primary-600: #2563eb;
        --qs-primary-700: #1d4ed8;
        --qs-primary-800: #1e40af;
        --qs-primary-900: #1e3a8a;
        
        /* Success Colors */
        --qs-success-50: #f0fdf4;
        --qs-success-100: #dcfce7;
        --qs-success-500: #22c55e;
        --qs-success-600: #16a34a;
        --qs-success-700: #15803d;
        
        /* Warning Colors */
        --qs-warning-50: #fffbeb;
        --qs-warning-100: #fef3c7;
        --qs-warning-500: #f59e0b;
        --qs-warning-600: #d97706;
        
        /* Danger Colors */
        --qs-danger-50: #fef2f2;
        --qs-danger-100: #fee2e2;
        --qs-danger-500: #ef4444;
        --qs-danger-600: #dc2626;
        
        /* Neutral Colors */
        --qs-gray-50: #f8fafc;
        --qs-gray-100: #f1f5f9;
        --qs-gray-200: #e2e8f0;
        --qs-gray-300: #cbd5e1;
        --qs-gray-400: #94a3b8;
        --qs-gray-500: #64748b;
        --qs-gray-600: #475569;
        --qs-gray-700: #334155;
        --qs-gray-800: #1e293b;
        --qs-gray-900: #0f172a;
        
        /* Typography */
        --qs-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        --qs-font-mono: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
        
        /* Font Sizes */
        --qs-text-xs: 0.75rem;
        --qs-text-sm: 0.875rem;
        --qs-text-base: 1rem;
        --qs-text-lg: 1.125rem;
        --qs-text-xl: 1.25rem;
        --qs-text-2xl: 1.5rem;
        
        /* Spacing */
        --qs-space-1: 0.25rem;
        --qs-space-2: 0.5rem;
        --qs-space-3: 0.75rem;
        --qs-space-4: 1rem;
        --qs-space-5: 1.25rem;
        --qs-space-6: 1.5rem;
        --qs-space-8: 2rem;
        --qs-space-10: 2.5rem;
        --qs-space-12: 3rem;
        
        /* Border Radius */
        --qs-radius-sm: 0.375rem;
        --qs-radius-md: 0.5rem;
        --qs-radius-lg: 0.75rem;
        --qs-radius-xl: 1rem;
        --qs-radius-2xl: 1.5rem;
        --qs-radius-full: 9999px;
        
        /* Shadows */
        --qs-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --qs-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --qs-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --qs-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --qs-shadow-focus: 0 0 0 3px rgb(59 130 246 / 0.3);
        
        /* Transitions */
        --qs-transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --qs-transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
        --qs-transition-slow: 300ms cubic-bezier(0.4, 0, 0.2, 1);
        
        /* Z-Index Scale */
        --qs-z-dropdown: 1000;
        --qs-z-sticky: 1020;
        --qs-z-modal: 1050;
        --qs-z-tooltip: 1070;
        --qs-z-toast: 1080;
    }

    /* ============================================
       BASE STYLES
       ============================================ */
    .qs-container {
        font-family: var(--qs-font-sans);
        color: var(--qs-gray-800);
        background: linear-gradient(135deg, var(--qs-gray-50) 0%, #ffffff 100%);
        min-height: 100vh;
    }

    /* ============================================
       HEADER STYLES
       ============================================ */
    .qs-header {
        background: linear-gradient(135deg, var(--qs-primary-600) 0%, var(--qs-primary-700) 100%);
        padding: var(--qs-space-8) 0;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: var(--qs-shadow-lg);
    }

    .qs-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .qs-header .container-fluid {
        position: relative;
        z-index: 1;
    }

    .qs-breadcrumb {
        display: flex;
        align-items: center;
        gap: var(--qs-space-2);
        font-size: var(--qs-text-sm);
        opacity: 0.9;
        margin-bottom: var(--qs-space-2);
    }

    .qs-breadcrumb a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: color var(--qs-transition-fast);
    }

    .qs-breadcrumb a:hover {
        color: white;
    }

    .qs-breadcrumb-separator {
        opacity: 0.5;
    }

    .qs-header-title {
        font-size: var(--qs-text-2xl);
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: var(--qs-space-3);
    }

    .qs-header-title-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--qs-radius-lg);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.24);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
    }

    .qs-header-subtitle {
        margin-top: var(--qs-space-2);
        margin-bottom: 0;
        font-size: var(--qs-text-sm);
        color: rgba(255, 255, 255, 0.86);
        max-width: 560px;
    }

    .qs-header-meta {
        display: flex;
        align-items: center;
        gap: var(--qs-space-4);
        margin-top: var(--qs-space-3);
        flex-wrap: wrap;
    }

    .qs-header-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--qs-space-2);
        padding: var(--qs-space-1) var(--qs-space-3);
        background: rgba(255,255,255,0.15);
        border-radius: var(--qs-radius-full);
        font-size: var(--qs-text-sm);
        backdrop-filter: blur(8px);
    }

    .qs-status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--qs-success-400);
        animation: qs-pulse 2s infinite;
    }

    @keyframes qs-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .qs-header-actions {
        display: flex;
        gap: var(--qs-space-3);
        align-items: center;
        flex-wrap: wrap;
    }

    .qs-header-btn {
        min-height: 46px;
        border-radius: var(--qs-radius-xl);
        padding: 0 var(--qs-space-5);
        font-weight: 600;
    }

    .qs-header-btn-outline {
        background: rgba(255, 255, 255, 0.08);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.35);
    }

    .qs-header-btn-outline:hover {
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.55);
        color: white;
    }

    .qs-header-btn-primary {
        background: white;
        color: var(--qs-primary-700);
        border: 1px solid rgba(255, 255, 255, 0.75);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
    }

    .qs-header-btn-primary:hover {
        background: var(--qs-gray-50);
        color: var(--qs-primary-800);
    }

    .qs-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--qs-space-2);
        padding: var(--qs-space-2) var(--qs-space-4);
        font-size: var(--qs-text-sm);
        font-weight: 500;
        border-radius: var(--qs-radius-lg);
        border: none;
        cursor: pointer;
        transition: all var(--qs-transition-fast);
        text-decoration: none;
    }

    .qs-btn-primary {
        background: white;
        color: var(--qs-primary-600);
    }

    .qs-btn-primary:hover {
        background: var(--qs-gray-50);
        transform: translateY(-1px);
        box-shadow: var(--qs-shadow-md);
    }

    .qs-btn-outline {
        background: transparent;
        color: white;
        border: 1px solid rgba(255,255,255,0.4);
    }

    .qs-btn-outline:hover {
        background: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.6);
    }

    /* ============================================
       TAB NAVIGATION
       ============================================ */
    .qs-tabs-container {
        background: white;
        border-radius: var(--qs-radius-xl);
        box-shadow: var(--qs-shadow-md);
        margin: var(--qs-space-6) 0;
        overflow: hidden;
    }

    .qs-tabs-header {
        display: flex;
        gap: var(--qs-space-1);
        padding: var(--qs-space-4);
        background: var(--qs-gray-50);
        border-bottom: 1px solid var(--qs-gray-200);
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .qs-tabs-header::-webkit-scrollbar {
        height: 4px;
    }

    .qs-tabs-header::-webkit-scrollbar-track {
        background: transparent;
    }

    .qs-tabs-header::-webkit-scrollbar-thumb {
        background: var(--qs-gray-300);
        border-radius: var(--qs-radius-full);
    }

    .qs-tab {
        display: inline-flex;
        align-items: center;
        gap: var(--qs-space-2);
        padding: var(--qs-space-3) var(--qs-space-4);
        font-size: var(--qs-text-sm);
        font-weight: 500;
        color: var(--qs-gray-600);
        background: white;
        border: 1px solid var(--qs-gray-200);
        border-radius: var(--qs-radius-lg);
        cursor: pointer;
        transition: all var(--qs-transition-fast);
        white-space: nowrap;
        text-decoration: none;
    }

    .qs-tab:hover {
        color: var(--qs-primary-600);
        border-color: var(--qs-primary-200);
        background: var(--qs-primary-50);
    }

    .qs-tab.active {
        color: var(--qs-primary-700);
        background: linear-gradient(135deg, var(--qs-primary-50) 0%, var(--qs-primary-100) 100%);
        border-color: var(--qs-primary-300);
        box-shadow: 0 0 0 1px var(--qs-primary-200);
    }

    .qs-tab-icon {
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qs-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 var(--qs-space-1);
        font-size: var(--qs-text-xs);
        font-weight: 600;
        background: var(--qs-gray-100);
        color: var(--qs-gray-600);
        border-radius: var(--qs-radius-full);
    }

    .qs-tab.active .qs-tab-badge {
        background: var(--qs-primary-500);
        color: white;
    }

    /* ============================================
       QUESTION NAVIGATION GRID
       ============================================ */
    .qs-nav-section {
        padding: var(--qs-space-4) var(--qs-space-6);
    }

    .qs-nav-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: var(--qs-space-4);
    }

    .qs-nav-title {
        font-size: var(--qs-text-sm);
        font-weight: 600;
        color: var(--qs-gray-700);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .qs-nav-progress {
        display: flex;
        align-items: center;
        gap: var(--qs-space-3);
    }

    .qs-progress-bar {
        width: 120px;
        height: 6px;
        background: var(--qs-gray-200);
        border-radius: var(--qs-radius-full);
        overflow: hidden;
    }

    .qs-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--qs-success-500), var(--qs-success-400));
        border-radius: var(--qs-radius-full);
        transition: width var(--qs-transition-slow);
    }

    .qs-nav-grid {
        display: flex;
        flex-wrap: wrap;
        gap: var(--qs-space-2);
    }

    .qs-nav-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        font-size: var(--qs-text-sm);
        font-weight: 600;
        border-radius: var(--qs-radius-lg);
        cursor: pointer;
        transition: all var(--qs-transition-fast);
        border: 2px solid transparent;
        position: relative;
    }

    .qs-nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--qs-shadow-md);
    }

    .qs-nav-btn.completed {
        background: linear-gradient(135deg, var(--qs-success-500), var(--qs-success-600));
        color: white;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
    }

    .qs-nav-btn.incomplete {
        background: white;
        color: var(--qs-danger-600);
        border-color: var(--qs-danger-200);
    }

    .qs-nav-btn.incomplete:hover {
        background: var(--qs-danger-50);
        border-color: var(--qs-danger-300);
    }

    .qs-nav-btn.active {
        background: linear-gradient(135deg, var(--qs-primary-500), var(--qs-primary-600));
        color: white;
        box-shadow: 0 0 0 3px var(--qs-primary-200), 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* Active state should override completed/incomplete */
    .qs-nav-btn.completed.active,
    .qs-nav-btn.incomplete.active {
        background: linear-gradient(135deg, var(--qs-primary-500), var(--qs-primary-600));
        color: white;
        box-shadow: 0 0 0 3px var(--qs-primary-200), 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* When active is removed, ensure completed/incomplete styles return */
    .qs-nav-btn.completed:not(.active) {
        background: linear-gradient(135deg, var(--qs-success-500), var(--qs-success-600));
        color: white;
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
    }

    .qs-nav-btn.incomplete:not(.active) {
        background: white;
        color: var(--qs-danger-600);
        border-color: var(--qs-danger-200);
    }

    .qs-nav-btn.linked::after {
        content: '';
        position: absolute;
        top: -4px;
        right: -4px;
        width: 12px;
        height: 12px;
        background: var(--qs-primary-500);
        border: 2px solid white;
        border-radius: 50%;
    }

    .qs-nav-btn.add-new {
        background: var(--qs-gray-100);
        color: var(--qs-gray-500);
        border: 2px dashed var(--qs-gray-300);
    }

    .qs-nav-btn.add-new:hover {
        background: var(--qs-primary-50);
        color: var(--qs-primary-600);
        border-color: var(--qs-primary-300);
    }

    /* ============================================
       CARD COMPONENTS
       ============================================ */
    .qs-card {
        background: white;
        border-radius: var(--qs-radius-xl);
        box-shadow: var(--qs-shadow-md);
        border: 1px solid var(--qs-gray-100);
        overflow: hidden;
    }

    .qs-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--qs-space-4) var(--qs-space-6);
        background: linear-gradient(135deg, var(--qs-gray-50) 0%, white 100%);
        border-bottom: 1px solid var(--qs-gray-100);
    }

    .qs-card-title {
        font-size: var(--qs-text-lg);
        font-weight: 600;
        color: var(--qs-gray-800);
        margin: 0;
    }

    .qs-card-body {
        padding: var(--qs-space-6);
    }

    .qs-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--qs-space-4) var(--qs-space-6);
        background: var(--qs-gray-50);
        border-top: 1px solid var(--qs-gray-100);
    }

    /* ============================================
       METADATA SECTION
       ============================================ */
    .qs-meta-card {
        background: linear-gradient(135deg, var(--qs-primary-50) 0%, white 100%);
        border: 1px solid var(--qs-primary-200);
        border-radius: var(--qs-radius-xl);
        overflow: hidden;
    }

    .qs-meta-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--qs-space-4) var(--qs-space-5);
        background: rgba(255,255,255,0.5);
        border-bottom: 1px solid var(--qs-primary-100);
    }

    .qs-meta-title {
        font-size: var(--qs-text-sm);
        font-weight: 600;
        color: var(--qs-primary-700);
        display: flex;
        align-items: center;
        gap: var(--qs-space-2);
    }

    .qs-meta-actions {
        display: flex;
        gap: var(--qs-space-2);
    }

    .qs-btn-sm {
        padding: var(--qs-space-1) var(--qs-space-3);
        font-size: var(--qs-text-xs);
    }

    .qs-form-group {
        margin-bottom: var(--qs-space-4);
    }

    .qs-form-label {
        display: block;
        font-size: var(--qs-text-sm);
        font-weight: 500;
        color: var(--qs-gray-700);
        margin-bottom: var(--qs-space-2);
    }

    .qs-form-control {
        width: 100%;
        padding: var(--qs-space-3) var(--qs-space-4);
        font-size: var(--qs-text-sm);
        color: var(--qs-gray-800);
        background: white;
        border: 1px solid var(--qs-gray-200);
        border-radius: var(--qs-radius-lg);
        transition: all var(--qs-transition-fast);
    }

    .qs-form-control:focus {
        outline: none;
        border-color: var(--qs-primary-400);
        box-shadow: var(--qs-shadow-focus);
    }

    .qs-form-control.is-invalid {
        border-color: var(--qs-danger-500);
        background: var(--qs-danger-50);
    }

    .qs-form-control.is-valid {
        border-color: var(--qs-success-500);
    }

    .qs-error-message {
        display: flex;
        align-items: center;
        gap: var(--qs-space-2);
        font-size: var(--qs-text-xs);
        color: var(--qs-danger-600);
        margin-top: var(--qs-space-1);
    }

    /* ============================================
       ANSWER OPTIONS - Drag & Drop
       ============================================ */
    .qs-options-container {
        display: flex;
        flex-direction: column;
        gap: var(--qs-space-3);
    }

    .qs-option-card {
        background: white;
        border: 2px solid var(--qs-gray-200);
        border-radius: var(--qs-radius-xl);
        overflow: hidden;
        transition: all var(--qs-transition-fast);
        position: relative;
    }

    .qs-option-card:hover {
        border-color: var(--qs-gray-300);
        box-shadow: var(--qs-shadow-md);
    }

    .qs-option-card.dragging {
        opacity: 0.8;
        transform: rotate(2deg);
        box-shadow: var(--qs-shadow-xl);
        z-index: 1000;
    }

    .qs-option-card.is-correct {
        border-color: var(--qs-success-500);
        background: linear-gradient(135deg, var(--qs-success-50) 0%, white 100%);
    }

    .qs-option-card.is-correct .qs-option-header {
        background: linear-gradient(135deg, var(--qs-success-100) 0%, var(--qs-success-50) 100%);
    }

    .qs-option-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--qs-space-3) var(--qs-space-4);
        background: linear-gradient(135deg, var(--qs-gray-50) 0%, white 100%);
        border-bottom: 1px solid var(--qs-gray-100);
    }

    .qs-option-header-left {
        display: flex;
        align-items: center;
        gap: var(--qs-space-3);
    }

    .qs-option-handle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        color: var(--qs-gray-400);
        cursor: grab;
        border-radius: var(--qs-radius-md);
        transition: all var(--qs-transition-fast);
    }

    .qs-option-handle:hover {
        color: var(--qs-gray-600);
        background: var(--qs-gray-100);
    }

    .qs-option-handle:active {
        cursor: grabbing;
    }

    .qs-option-letter {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        font-weight: 700;
        font-size: var(--qs-text-base);
        color: white;
        background: linear-gradient(135deg, var(--qs-primary-500), var(--qs-primary-600));
        border-radius: var(--qs-radius-lg);
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    .qs-option-card.is-correct .qs-option-letter {
        background: linear-gradient(135deg, var(--qs-success-500), var(--qs-success-600));
        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25);
    }

    .qs-option-title {
        font-weight: 600;
        color: var(--qs-gray-700);
    }

    .qs-option-header-right {
        display: flex;
        align-items: center;
        gap: var(--qs-space-3);
    }

    .qs-option-check {
        display: flex;
        align-items: center;
        gap: var(--qs-space-2);
    }

    .qs-option-check-label {
        font-size: var(--qs-text-xs);
        font-weight: 500;
        color: var(--qs-gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .qs-option-card.is-correct .qs-option-check-label {
        color: var(--qs-success-600);
    }

    .qs-radio-custom {
        appearance: none;
        width: 24px;
        height: 24px;
        border: 2px solid var(--qs-gray-300);
        border-radius: 50%;
        cursor: pointer;
        transition: all var(--qs-transition-fast);
        position: relative;
    }

    .qs-radio-custom:hover {
        border-color: var(--qs-primary-400);
    }

    .qs-radio-custom:checked {
        border-color: var(--qs-success-500);
        background: linear-gradient(135deg, var(--qs-success-500), var(--qs-success-600));
    }

    .qs-radio-custom:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
    }

    .qs-option-body {
        padding: var(--qs-space-4);
    }

    .qs-option-delete {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        color: var(--qs-gray-400);
        background: transparent;
        border: none;
        border-radius: var(--qs-radius-md);
        cursor: pointer;
        transition: all var(--qs-transition-fast);
    }

    .qs-option-delete:hover {
        color: var(--qs-danger-500);
        background: var(--qs-danger-50);
    }

    /* ============================================
       ADD OPTION BUTTON
       ============================================ */
    .qs-add-option {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: var(--qs-space-2);
        width: 100%;
        padding: var(--qs-space-4);
        font-size: var(--qs-text-sm);
        font-weight: 500;
        color: var(--qs-gray-500);
        background: transparent;
        border: 2px dashed var(--qs-gray-300);
        border-radius: var(--qs-radius-xl);
        cursor: pointer;
        transition: all var(--qs-transition-fast);
    }

    .qs-add-option:hover {
        color: var(--qs-primary-600);
        background: var(--qs-primary-50);
        border-color: var(--qs-primary-300);
    }

    /* ============================================
       PREVIEW PANEL
       ============================================ */
    .qs-preview-card {
        background: white;
        border: 1px solid var(--qs-gray-200);
        border-radius: var(--qs-radius-xl);
        overflow: hidden;
    }

    .qs-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--qs-space-3) var(--qs-space-4);
        background: linear-gradient(135deg, var(--qs-gray-50) 0%, white 100%);
        border-bottom: 1px solid var(--qs-gray-100);
    }

    .qs-preview-title {
        font-size: var(--qs-text-sm);
        font-weight: 600;
        color: var(--qs-gray-700);
        display: flex;
        align-items: center;
        gap: var(--qs-space-2);
    }

    .qs-preview-badge {
        font-size: var(--qs-text-xs);
        padding: var(--qs-space-1) var(--qs-space-2);
        background: var(--qs-success-100);
        color: var(--qs-success-700);
        border-radius: var(--qs-radius-md);
    }

    .qs-preview-body {
        padding: var(--qs-space-6);
        background: linear-gradient(135deg, #fafafa 0%, white 100%);
        min-height: 200px;
    }

    .qs-preview-question {
        font-size: var(--qs-text-base);
        line-height: 1.7;
        color: var(--qs-gray-800);
        margin-bottom: var(--qs-space-6);
    }

    .qs-preview-options {
        display: flex;
        flex-direction: column;
        gap: var(--qs-space-3);
    }

    .qs-preview-option {
        display: flex;
        align-items: flex-start;
        gap: var(--qs-space-3);
        padding: var(--qs-space-3) var(--qs-space-4);
        background: white;
        border: 1px solid var(--qs-gray-200);
        border-radius: var(--qs-radius-lg);
    }

    .qs-preview-option-letter {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        font-weight: 600;
        font-size: var(--qs-text-xs);
        color: var(--qs-gray-600);
        background: var(--qs-gray-100);
        border-radius: var(--qs-radius-md);
        flex-shrink: 0;
    }

    /* ============================================
       TEMPLATES MODAL
       ============================================ */
    .qs-templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: var(--qs-space-4);
        padding: var(--qs-space-4);
    }

    .qs-template-card {
        background: white;
        border: 1px solid var(--qs-gray-200);
        border-radius: var(--qs-radius-xl);
        overflow: hidden;
        cursor: pointer;
        transition: all var(--qs-transition-fast);
    }

    .qs-template-card:hover {
        border-color: var(--qs-primary-300);
        box-shadow: var(--qs-shadow-lg);
        transform: translateY(-2px);
    }

    .qs-template-header {
        padding: var(--qs-space-4);
        background: linear-gradient(135deg, var(--qs-primary-50) 0%, white 100%);
        border-bottom: 1px solid var(--qs-gray-100);
    }

    .qs-template-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--qs-primary-100);
        color: var(--qs-primary-600);
        border-radius: var(--qs-radius-lg);
        font-size: var(--qs-text-xl);
        margin-bottom: var(--qs-space-3);
    }

    .qs-template-title {
        font-size: var(--qs-text-base);
        font-weight: 600;
        color: var(--qs-gray-800);
        margin-bottom: var(--qs-space-1);
    }

    .qs-template-desc {
        font-size: var(--qs-text-xs);
        color: var(--qs-gray-500);
    }

    .qs-template-body {
        padding: var(--qs-space-4);
    }

    .qs-template-preview {
        font-size: var(--qs-text-xs);
        color: var(--qs-gray-600);
        background: var(--qs-gray-50);
        padding: var(--qs-space-3);
        border-radius: var(--qs-radius-md);
        max-height: 80px;
        overflow: hidden;
    }

    /* ============================================
       KEYBOARD SHORTCUTS HINT
       ============================================ */
    .qs-shortcuts-hint {
        position: fixed;
        bottom: var(--qs-space-4);
        right: var(--qs-space-4);
        z-index: var(--qs-z-tooltip);
        background: var(--qs-gray-800);
        color: white;
        padding: var(--qs-space-3) var(--qs-space-4);
        border-radius: var(--qs-radius-lg);
        font-size: var(--qs-text-xs);
        box-shadow: var(--qs-shadow-xl);
        opacity: 0;
        transform: translateY(10px);
        transition: all var(--qs-transition-base);
        pointer-events: none;
    }

    .qs-shortcuts-hint.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .qs-kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: var(--qs-space-1) var(--qs-space-2);
        font-family: var(--qs-font-mono);
        font-size: var(--qs-text-xs);
        background: var(--qs-gray-600);
        border-radius: var(--qs-radius-sm);
        border-bottom: 2px solid var(--qs-gray-700);
    }

    /* ============================================
       SPLIT PANEL LAYOUT
       ============================================ */
    .qs-split-container {
        display: grid;
        grid-template-columns: 1fr minmax(420px, 520px);
        gap: var(--qs-space-6);
    }

    @media (max-width: 1200px) {
        .qs-split-container {
            grid-template-columns: 1fr;
        }
    }

    .qs-editor-panel {
        display: flex;
        flex-direction: column;
        gap: var(--qs-space-6);
    }

    .qs-preview-panel {
        position: sticky;
        top: var(--qs-space-4);
        height: auto;
        display: flex;
        flex-direction: column;
        gap: var(--qs-space-4);
        max-height: none;
        overflow: visible;
        padding-right: 0;
    }

    @media (max-width: 1200px) {
        .qs-preview-panel {
            position: static;
            top: auto;
        }
    }

    /* ============================================
       EDITOR SECTION
       ============================================ */
    .qs-editor-section {
        background: white;
        border-radius: var(--qs-radius-xl);
        box-shadow: var(--qs-shadow-md);
        overflow: hidden;
    }

    .qs-editor-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--qs-space-4) var(--qs-space-5);
        background: var(--qs-gray-50);
        border-bottom: 1px solid var(--qs-gray-100);
    }

    .qs-editor-title {
        font-size: var(--qs-text-sm);
        font-weight: 600;
        color: var(--qs-gray-700);
        display: flex;
        align-items: center;
        gap: var(--qs-space-2);
    }

    .qs-editor-tools {
        display: flex;
        gap: var(--qs-space-2);
    }

    .qs-tool-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        color: var(--qs-gray-500);
        background: white;
        border: 1px solid var(--qs-gray-200);
        border-radius: var(--qs-radius-md);
        cursor: pointer;
        transition: all var(--qs-transition-fast);
    }

    .qs-tool-btn:hover {
        color: var(--qs-primary-600);
        border-color: var(--qs-primary-300);
        background: var(--qs-primary-50);
    }

    .qs-editor-body {
        padding: var(--qs-space-5);
    }

    /* ============================================
       ACTION BAR
       ============================================ */
    .qs-action-bar {
        position: sticky;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--qs-space-4) var(--qs-space-6);
        background: white;
        border-top: 1px solid var(--qs-gray-200);
        box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
        z-index: var(--qs-z-sticky);
    }

    .qs-action-group {
        display: flex;
        align-items: center;
        gap: var(--qs-space-3);
    }

    .qs-btn-lg {
        padding: var(--qs-space-3) var(--qs-space-6);
        font-size: var(--qs-text-base);
    }

    .qs-btn-success {
        background: linear-gradient(135deg, var(--qs-success-500), var(--qs-success-600));
        color: white;
    }

    .qs-btn-success:hover {
        background: linear-gradient(135deg, var(--qs-success-600), var(--qs-success-700));
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
    }

    /* ============================================
       RESPONSIVE ADJUSTMENTS
       ============================================ */
    /* ============================================
       SIDEBAR NAVIGATION
       ============================================ */
    .qs-layout-split {
        display: flex;
        flex-direction: column;
        gap: var(--qs-space-4);
        align-items: stretch;
    }

    .qs-sidebar {
        flex: none;
        width: 100%;
        background: white;
        border-radius: var(--qs-radius-lg);
        border: 1px solid var(--qs-gray-200);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        padding: var(--qs-space-4);
    }

    .qs-sidebar .qs-nav-grid {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
    }

    .qs-main-content {
        flex: 1;
        min-width: 0;
    }

    @media (max-width: 768px) {
        .qs-layout-split {
            flex-direction: column;
        }
        .qs-sidebar {
            flex: none;
            position: relative;
            top: 0;
            max-height: none;
            overflow-y: visible;
            width: 100%;
        }
        .qs-sidebar .qs-nav-grid {
            flex-direction: row;
            flex-wrap: wrap;
        }
        .qs-header-title {
            font-size: var(--qs-text-xl);
        }

        .qs-header-title-icon {
            width: 40px;
            height: 40px;
        }

        .qs-header-actions {
            justify-content: flex-start;
        }

        .qs-header-btn {
            min-height: 42px;
            padding: 0 var(--qs-space-4);
        }
        
        .qs-tabs-header {
            padding: var(--qs-space-2);
        }
        
        .qs-tab {
            padding: var(--qs-space-2) var(--qs-space-3);
        }
        
        .qs-nav-btn {
            width: 40px;
            height: 40px;
        }
        
        .qs-action-bar {
            flex-direction: column;
            gap: var(--qs-space-3);
            padding: var(--qs-space-3);
        }
        
        .qs-action-group {
            width: 100%;
            justify-content: center;
        }
    }

    /* ============================================
       ANIMATIONS
       ============================================ */
    .qs-fade-in {
        animation: qs-fade-in 0.3s ease-out;
    }

    @keyframes qs-fade-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .qs-slide-in {
        animation: qs-slide-in 0.4s ease-out;
    }

    @keyframes qs-slide-in {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* ============================================
       TOAST NOTIFICATIONS
       ============================================ */
    .qs-toast-container {
        position: fixed;
        top: var(--qs-space-4);
        right: var(--qs-space-4);
        z-index: var(--qs-z-toast);
        display: flex;
        flex-direction: column;
        gap: var(--qs-space-3);
    }

    .qs-toast {
        display: flex;
        align-items: center;
        gap: var(--qs-space-3);
        padding: var(--qs-space-4);
        background: white;
        border-radius: var(--qs-radius-lg);
        box-shadow: var(--qs-shadow-xl);
        border-left: 4px solid var(--qs-primary-500);
        animation: qs-toast-in 0.3s ease-out;
    }

    .qs-toast.success {
        border-left-color: var(--qs-success-500);
    }

    .qs-toast.error {
        border-left-color: var(--qs-danger-500);
    }

    .qs-toast.warning {
        border-left-color: var(--qs-warning-500);
    }

    @keyframes qs-toast-in {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Legacy compatibility styles */
    .btn-circle-sm {
        width: 38px;
        height: 38px;
        padding: 0;
        border-radius: 50%;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 4px;
    }

    .btn-oval-sm {
        border-radius: 50px;
        padding-left: 15px;
        padding-right: 15px;
    }
</style>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0">Data Soal</h3>
                <div class="text-muted small mt-1">
                    <?= esc($bank->bank_kode ?? '') ?> - <?= esc($bank->nama_mapel ?? '') ?>
                    <?php if (!empty($bank->subtes_code) && $bank->subtes_code !== 'GENERAL'): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size:.75rem;">
                            <i class="bi bi-diagram-2 me-1"></i><?= esc($bank->subtes_nama ?? $bank->subtes_code) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="d-sm-flex justify-content-sm-end">
                    <ol class="breadcrumb mb-2 mb-sm-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('cbtbanksoal') ?>">Bank Soal</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('cbtbanksoal/detail/' . ($bank->id_bank ?? 0)) ?>"><?= esc($bank->bank_kode ?? 'Detail') ?></a></li>
                        <li class="breadcrumb-item active">Buat Soal</li>
                    </ol>
                </div>
                <div class="d-flex justify-content-sm-end gap-2 flex-wrap mt-2">
                    <a href="<?= base_url('cbtbanksoal/detail/' . ($bank->id_bank ?? 0)) ?>" class="btn btn-sm btn-success">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="qs-toast-container" id="toast-container"></div>

<!-- Keyboard Shortcuts Hint -->
<div class="qs-shortcuts-hint" id="shortcuts-hint">
    <span class="qs-kbd">Ctrl</span> + <span class="qs-kbd">S</span> Simpan &nbsp;|&nbsp;
    <span class="qs-kbd">Ctrl</span> + <span class="qs-kbd">P</span> Panel Opsi &nbsp;|&nbsp;
    <span class="qs-kbd">←</span> <span class="qs-kbd">→</span> Navigasi
</div>



<div class="app-content qs-container">
    <div class="container-fluid py-4">
        <?php if (empty($bank->kartu_soal_id)): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4 border-0 shadow-sm qs-fade-in" style="border-radius: 12px; background: linear-gradient(135deg, #fff3cd 0%, #ffecb5 100%);">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-3" style="font-size: 1.5rem; color: #ffc107;"></i>
            <div>
                <h6 class="mb-1 fw-bold" style="color: #664d03;">Bank Soal belum ditautkan ke Kartu Soal (Kisi-kisi)</h6>
                <div class="small" style="color: #856404;">Untuk menggunakan fitur pengisian metadata otomatis dan statistik pemenuhan soal, Anda sebaiknya menautkan bank soal ini dengan Kartu Soal.</div>
            </div>
            <a href="<?= base_url('cbtbanksoal/edit/' . ($bank->id_bank ?? 0)) ?>" class="btn btn-sm btn-warning ms-auto rounded-pill px-3 fw-bold" style="color: #664d03; border: 1px solid #ffc107;">
                <i class="bi bi-link-45deg me-1"></i>Tautkan
            </a>
        </div>
        <?php endif; ?>

        <?= form_open('create', ['id' => 'create', 'class' => 'qs-fade-in']) ?>
        <?php
        // Define visibility variables for different question types
        $pg_none = $tab_active == '1' ? '' : 'd-none';
        $pg2_none = $tab_active == '2' ? '' : 'd-none';
        $jodohkan_none = $tab_active == '3' ? '' : 'd-none';
        $isian_none = $tab_active == '4' ? '' : 'd-none';
        $essai_none = $tab_active == '5' ? '' : 'd-none';
        $urutan_none = $tab_active == '6' ? '' : 'd-none';
        $cloze_none = $tab_active == '7' ? '' : 'd-none';
        
        // Define rangeAbjad for PG options
        $opsi_count = $bank->opsi ?? 5;
        if ($opsi_count == 4) {
            $rangeAbjad = range('a', 'd');
        } elseif ($opsi_count == 5) {
            $rangeAbjad = range('a', 'e');
        } else {
            $rangeAbjad = range('a', 'c');
        }
        ?>
        <input type="hidden" id="jenis-id" name="jenis" value="<?= $tab_active ?>">
        <input type="hidden" id="bank-id" name="bank_id" value="<?= $bank->id_bank ?? 0 ?>">
        <input type="hidden" id="soal-id" name="soal_id" value="<?= is_null($soal) ? '0' : ($soal->id_soal ?? 0) ?>">
        <input type="hidden" id="method" name="method" value="<?= is_null($soal) ? 'add' : 'edit' ?>">

        <div class="qs-tabs-container mb-4">
            <div class="qs-tabs-header">
                <a href="<?= site_url('cbtbanksoal/buatsoal/' . ($bank->id_bank ?? 0) . '?tab=1') ?>" 
                   class="qs-tab <?= $tab_active == '1' ? 'active' : '' ?>">
                    <span class="qs-tab-icon"><i class="bi bi-ui-radios"></i></span>
                    <span>Soal PG</span>
                    <span class="qs-tab-badge"><?= $bank->tampil_pg ?? 0 ?></span>
                </a>
                <a href="<?= site_url('cbtbanksoal/buatsoal/' . ($bank->id_bank ?? 0) . '?tab=2') ?>" 
                   class="qs-tab <?= $tab_active == '2' ? 'active' : '' ?>">
                    <span class="qs-tab-icon"><i class="bi bi-ui-checks"></i></span>
                    <span>PG Kompleks</span>
                    <span class="qs-tab-badge"><?= $bank->tampil_kompleks ?? 0 ?></span>
                </a>
                <a href="<?= site_url('cbtbanksoal/buatsoal/' . ($bank->id_bank ?? 0) . '?tab=3') ?>" 
                   class="qs-tab <?= $tab_active == '3' ? 'active' : '' ?>">
                    <span class="qs-tab-icon"><i class="bi bi-arrow-left-right"></i></span>
                    <span>Menjodohkan</span>
                    <span class="qs-tab-badge"><?= $bank->tampil_jodohkan ?? 0 ?></span>
                </a>
                <a href="<?= site_url('cbtbanksoal/buatsoal/' . ($bank->id_bank ?? 0) . '?tab=4') ?>" 
                   class="qs-tab <?= $tab_active == '4' ? 'active' : '' ?>">
                    <span class="qs-tab-icon"><i class="bi bi-input-cursor"></i></span>
                    <span>Isian Singkat</span>
                    <span class="qs-tab-badge"><?= $bank->tampil_isian ?? 0 ?></span>
                </a>
                <a href="<?= site_url('cbtbanksoal/buatsoal/' . ($bank->id_bank ?? 0) . '?tab=5') ?>" 
                   class="qs-tab <?= $tab_active == '5' ? 'active' : '' ?>">
                    <span class="qs-tab-icon"><i class="bi bi-text-paragraph"></i></span>
                    <span>Essai</span>
                    <span class="qs-tab-badge"><?= $bank->tampil_esai ?? 0 ?></span>
                </a>
                <a href="<?= site_url('cbtbanksoal/buatsoal/' . ($bank->id_bank ?? 0) . '?tab=6') ?>" 
                   class="qs-tab <?= $tab_active == '6' ? 'active' : '' ?>">
                    <span class="qs-tab-icon"><i class="bi bi-sort-numeric-down"></i></span>
                    <span>Urutan</span>
                    <span class="qs-tab-badge"><?= $bank->tampil_urutan ?? 0 ?></span>
                </a>
                <a href="<?= site_url('cbtbanksoal/buatsoal/' . ($bank->id_bank ?? 0) . '?tab=7') ?>" 
                   class="qs-tab <?= $tab_active == '7' ? 'active' : '' ?>">
                    <span class="qs-tab-icon"><i class="bi bi-input-cursor-text"></i></span>
                    <span>Isian Rumpang</span>
                    <span class="qs-tab-badge"><?= $bank->tampil_cloze ?? 0 ?></span>
                </a>
            </div>


        <div class="qs-layout-split">
            <div class="qs-sidebar">
                <div class="qs-nav-section">
                    <div class="qs-nav-header">
                        <span class="qs-nav-title">
                            <?php
                            $type_names = [
                                '1' => 'Pilihan Ganda',
                                '2' => 'Pilihan Ganda Kompleks',
                                '3' => 'Menjodohkan',
                                '4' => 'Isian Singkat',
                                '5' => 'Essai',
                                '6' => 'Urutan',
                                '7' => 'Isian Rumpang'
                            ];
                            echo $type_names[$tab_active] ?? 'Soal';
                            ?>
                        </span>
                        <div class="qs-nav-progress">
                            <?php
                            $total_map = [
                                '1' => $bank->tampil_pg ?? 0,
                                '2' => $bank->tampil_kompleks ?? 0,
                                '3' => $bank->tampil_jodohkan ?? 0,
                                '4' => $bank->tampil_isian ?? 0,
                                '5' => $bank->tampil_esai ?? 0,
                                '6' => $bank->tampil_urutan ?? 0,
                                '7' => $bank->tampil_cloze ?? 0
                            ];
                            $total_needed = $total_map[$tab_active] ?? 0;
                            $completed = ${'jml_' . ($tab_active == '1' ? 'pg' : ($tab_active == '2' ? 'pg2' : ($tab_active == '3' ? 'jodohkan' : ($tab_active == '4' ? 'isian' : ($tab_active == '5' ? 'essai' : ($tab_active == '6' ? 'urutan' : 'cloze'))))))}->nomor_soal ?? 0;
                            $progress = $total_needed > 0 ? round(($completed / $total_needed) * 100) : 0;
                            ?>
                            <span class="text-muted small"><?= $completed ?> / <?= $total_needed ?> soal</span>
                            <div class="qs-progress-bar">
                                <div class="qs-progress-fill" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="qs-nav-grid">
                        <?php
                        // Generate navigation buttons based on active tab
                        $nav_data = [];
                        switch($tab_active) {
                            case '1':
                                $tpg = (!isset($jml_pg) || empty($jml_pg) || !isset($jml_pg->nomor_soal)) ? 0 : $jml_pg->nomor_soal;
                                $no = 1;
                                for ($i = 0; $i < $tpg; $i++) {
                                    $completed = !in_array($no, $pg_belum_komplit);
                                    $active = ($p_no == $no);
                                    $linked = in_array($no, $soal_pg_ada);
                                    $nav_data[] = ['no' => $no, 'completed' => $completed, 'active' => $active, 'linked' => $linked, 'jenis' => '1'];
                                    $no++;
                                }
                                $nav_data[] = ['no' => $no, 'add_new' => true, 'jenis' => '1', 'func' => 'tambahSoalPg'];
                                break;
                            case '2':
                                $tpg2 = (!isset($jml_pg2) || empty($jml_pg2) || !isset($jml_pg2->nomor_soal)) ? 0 : $jml_pg2->nomor_soal;
                                $no2 = 1;
                                for ($i = 0; $i < $tpg2; $i++) {
                                    $completed = !in_array($no2, $pg2_belum_komplit);
                                    $active = ($p_no == $no2);
                                    $linked = in_array($no2, $soal_pg2_ada);
                                    $nav_data[] = ['no' => $no2, 'completed' => $completed, 'active' => $active, 'linked' => $linked, 'jenis' => '2'];
                                    $no2++;
                                }
                                $nav_data[] = ['no' => $no2, 'add_new' => true, 'jenis' => '2', 'func' => 'tambahSoalPg2'];
                                break;
                            case '3':
                                $tej = (!isset($jml_jodohkan) || empty($jml_jodohkan) || !isset($jml_jodohkan->nomor_soal)) ? 0 : $jml_jodohkan->nomor_soal;
                                $noj = 1;
                                for ($i = 0; $i < $tej; $i++) {
                                    $completed = !in_array($noj, $jodohkan_belum_komplit);
                                    $active = ($p_no == $noj);
                                    $linked = in_array($noj, $soal_jodohkan_ada);
                                    $nav_data[] = ['no' => $noj, 'completed' => $completed, 'active' => $active, 'linked' => $linked, 'jenis' => '3'];
                                    $noj++;
                                }
                                $nav_data[] = ['no' => $noj, 'add_new' => true, 'jenis' => '3', 'func' => 'tambahSoalJodohkan'];
                                break;
                            case '4':
                                $tei = (!isset($jml_isian) || empty($jml_isian) || !isset($jml_isian->nomor_soal)) ? 0 : $jml_isian->nomor_soal;
                                $noi = 1;
                                for ($i = 0; $i < $tei; $i++) {
                                    $completed = !in_array($noi, $isian_belum_komplit);
                                    $active = ($p_no == $noi);
                                    $linked = in_array($noi, $soal_isian_ada);
                                    $nav_data[] = ['no' => $noi, 'completed' => $completed, 'active' => $active, 'linked' => $linked, 'jenis' => '4'];
                                    $noi++;
                                }
                                $nav_data[] = ['no' => $noi, 'add_new' => true, 'jenis' => '4', 'func' => 'tambahSoalIsian'];
                                break;
                            case '5':
                                $tes = (!isset($jml_essai) || empty($jml_essai) || !isset($jml_essai->nomor_soal)) ? 0 : $jml_essai->nomor_soal;
                                $noe = 1;
                                for ($i = 0; $i < $tes; $i++) {
                                    $completed = !in_array($noe, $essai_belum_komplit);
                                    $active = ($p_no == $noe);
                                    $linked = in_array($noe, $soal_esai_ada);
                                    $nav_data[] = ['no' => $noe, 'completed' => $completed, 'active' => $active, 'linked' => $linked, 'jenis' => '5'];
                                    $noe++;
                                }
                                $nav_data[] = ['no' => $noe, 'add_new' => true, 'jenis' => '5', 'func' => 'tambahSoalEssai'];
                                break;
                            case '6':
                                $teu = (!isset($jml_urutan) || empty($jml_urutan) || !isset($jml_urutan->nomor_soal)) ? 0 : $jml_urutan->nomor_soal;
                                $nou = 1;
                                for ($i = 0; $i < $teu; $i++) {
                                    $completed = !in_array($nou, $urutan_belum_komplit);
                                    $active = ($p_no == $nou);
                                    $linked = in_array($nou, $soal_urutan_ada);
                                    $nav_data[] = ['no' => $nou, 'completed' => $completed, 'active' => $active, 'linked' => $linked, 'jenis' => '6'];
                                    $nou++;
                                }
                                $nav_data[] = ['no' => $nou, 'add_new' => true, 'jenis' => '6', 'func' => 'tambahSoalUrutan'];
                                break;
                            case '7':
                                $tec = (!isset($jml_cloze) || empty($jml_cloze) || !isset($jml_cloze->nomor_soal)) ? 0 : $jml_cloze->nomor_soal;
                                $noc = 1;
                                for ($i = 0; $i < $tec; $i++) {
                                    $completed = !in_array($noc, $cloze_belum_komplit);
                                    $active = ($p_no == $noc);
                                    $linked = in_array($noc, $soal_cloze_ada);
                                    $nav_data[] = ['no' => $noc, 'completed' => $completed, 'active' => $active, 'linked' => $linked, 'jenis' => '7'];
                                    $noc++;
                                }
                                $nav_data[] = ['no' => $noc, 'add_new' => true, 'jenis' => '7', 'func' => 'tambahSoalCloze'];
                                break;
                        }

                        foreach($nav_data as $nav):
                            if(isset($nav['add_new'])):
                        ?>
                            <a href="javascript:void(0)" 
                               class="qs-nav-btn add-new" 
                               onclick="<?= $nav['func'] ?>(this)"
                               data-mapel="<?= $bank->bank_mapel_id ?? '' ?>"
                               data-bank="<?= $bank->id_bank ?? 0 ?>"
                               data-nomor="<?= $nav['no'] ?>"
                               data-jenis="<?= $nav['jenis'] ?>"
                               id="btn-add-new-<?= $nav['jenis'] == '1' ? 'pg' : ($nav['jenis'] == '2' ? 'pg2' : ($nav['jenis'] == '3' ? 'jodohkan' : ($nav['jenis'] == '4' ? 'isian' : ($nav['jenis'] == '5' ? 'essai' : ($nav['jenis'] == '6' ? 'urutan' : 'cloze'))))) ?>">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        <?php else:
                            $btn_class = $nav['completed'] ? 'completed' : 'incomplete';
                            if($nav['active']) $btn_class .= ' active';
                            if($nav['linked']) $btn_class .= ' linked';
                        ?>
                            <a href="javascript:void(0)"
                               class="qs-nav-btn <?= $btn_class ?>"
                               onclick="getSoalById(<?= $bank->id_bank ?? 0 ?>, <?= $nav['no'] ?>, <?= $nav['jenis'] . $nav['no'] ?>, '<?= $nav['jenis'] ?>')"
                               id="btn-<?= $nav['jenis'] ?><?= $nav['no'] ?>">
                                <?= $nav['no'] ?>
                            </a>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="qs-main-content">
                <!-- Main Editor with Split Panel -->
                <div class="qs-card mb-4" id="not-empty-soal">
                    <div class="qs-card-header">
                        <h5 class="qs-card-title">
                            <i class="bi bi-pencil-square me-2"></i>
                            Editor Soal <span id="editor-nomor">#<?= $p_no ?? 1 ?></span>
                        </h5>
                        <div class="qs-editor-tools">
                            <button type="button" class="qs-tool-btn" onclick="tidyManual()" title="Rapikan teks (hilangkan format)">
                                <i class="bi bi-eraser"></i>
                            </button>
                            <?php if ($canUseAi): ?>
                            <button type="button" class="qs-tool-btn" onclick="paraphraseSoal()" title="Parafrase dengan AI">
                                <i class="bi bi-magic"></i>
                            </button>
                            <button type="button" class="qs-tool-btn" onclick="openAISimilarModal()" title="Buat Soal Mirip (AI) — soal baru setara kompetensi, skenario berbeda">
                                <i class="bi bi-plus-square-dotted"></i>
                            </button>
                            <?php endif; ?>
                            <button type="button" class="qs-tool-btn" onclick="togglePreviewPanel()" title="Toggle panel opsi (Ctrl+P)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="qs-card-body p-0">
                        <!-- Metadata Card -->
                        <div class="qs-meta-card m-4">
                            <div class="qs-meta-header">
                                <div class="qs-meta-title">
                                    <i class="bi bi-link-45deg"></i>
                                    Metadata Kisi-kisi Soal
                                </div>
                                <div class="qs-meta-actions">
                                    <button type="button" class="qs-btn qs-btn-sm btn-outline-primary" onclick="bukaModalKisiKisi()">
                                        <i class="bi bi-list-check me-1"></i>Pilih Kisi-kisi
                                    </button>
                                    <input type="hidden" name="kartu_item_id" id="kartu_item_id" value="">
                                    <input type="hidden" name="unlink_kisi" id="unlink_kisi" value="0">
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="alert alert-success d-none mb-3" id="alert-kisi-tautan">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <span><i class="bi bi-check-circle-fill me-2"></i><span id="text-kisi-tautan">Soal ini ditautkan ke Kisi-kisi.</span></span>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($canUseAi): ?>
                                            <button type="button" class="btn btn-sm px-3 py-1 text-white border-0" 
                                                style="background:linear-gradient(135deg,#3b82f6,#8b5cf6);border-radius:20px;font-size:12px;font-weight:600;box-shadow:0 2px 8px rgba(59,130,246,.3);transition:all .2s;"
                                                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 12px rgba(59,130,246,.4)'"
                                                onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(59,130,246,.3)'"
                                                onclick="openAIGenerateModal()" 
                                                id="btn-ai-from-kisi" title="Generate soal otomatis dari kisi-kisi ini">
                                                <i class="bi bi-robot me-1"></i>Generate Soal AI
                                            </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn-close" onclick="unlinkKisiKisi()"></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="qs-form-group mb-0">
                                            <label class="qs-form-label"><i class="bi bi-journal-text me-1 text-primary"></i>Tujuan Pembelajaran (TP)</label>
                                            <div id="tp-select-wrapper">
                                                <select id="select_tp_id" class="form-select form-select-sm border-primary" onchange="onSelectTpChanged(this)">
                                                    <option value="">-- Pilih Tujuan Pembelajaran (TP) --</option>
                                                </select>
                                            </div>
                                            <div id="tp-display-wrapper" class="qs-form-control bg-light d-none" style="min-height:38px; display:flex; align-items:center;">
                                                <span id="meta-tujuan-text" class="text-muted">-</span>
                                            </div>
                                            <input type="hidden" name="tujuan_id" id="tujuan_id" value="">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="qs-form-group mb-0">
                                            <label class="qs-form-label"><i class="bi bi-tag me-1 text-primary"></i>Topik/Materi</label>
                                            <div class="qs-form-control bg-light" style="min-height:38px; display:flex; align-items:center;">
                                                <span id="meta-topik-text" class="text-muted">-</span>
                                                <input type="hidden" name="topik" id="topik" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="qs-form-group mb-0">
                                            <label class="qs-form-label"><i class="bi bi-bullseye me-1 text-primary"></i>Indikator Pencapaian Kompetensi</label>
                                            <div class="qs-form-control bg-light" style="min-height:38px; display:flex; align-items:center;">
                                                <span id="meta-indikator-text" class="text-muted">-</span>
                                                <input type="hidden" name="indikator" id="indikator" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="qs-form-group mb-0">
                                            <label class="qs-form-label"><i class="bi bi-speedometer2 me-1 text-primary"></i>Tingkat Kesulitan / Bloom</label>
                                            <div class="qs-form-control bg-light" style="min-height:38px; display:flex; align-items:center;">
                                                <span id="meta-kesulitan-text" class="text-muted">-</span>
                                                <input type="hidden" name="kesulitan" id="kesulitan" value="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="qs-form-group mb-0">
                                            <label class="qs-form-label" title="Parameter kesukaran / IRT (0.1 - 3.0)"><i class="bi bi-sliders me-1 text-info"></i>Bobot IRT</label>
                                            <input type="number" step="0.1" min="0.1" max="3.0" class="form-control form-control-sm border" name="bobot_irt" id="bobot_irt" value="1.0" placeholder="1.0" style="height:38px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Split Panel: Editor + Opsi -->
                        <div class="qs-split-container px-4 pb-4">
                            <div class="qs-editor-panel">
                                <!-- Question Editor -->
                                <div class="qs-editor-section">
                                    <div class="qs-editor-header">
                                        <div class="qs-editor-title">
                                            <i class="bi bi-question-circle"></i>
                                            Teks Soal
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-secondary border px-2 py-1 text-uppercase" id="soal-char-count" style="font-size: 0.7rem; letter-spacing: 0.5px; font-weight: 600;">0 karakter</span>
                                        </div>
                                    </div>
                                    <div class="qs-editor-body">
                                        <textarea class="textsoal" id="textsoal" name="soal" placeholder="Tulis soal di sini..."></textarea>
                                        <div class="qs-error-message d-none" id="soal-error">
                                            <i class="bi bi-exclamation-circle"></i>
                                            <span>Teks soal tidak boleh kosong</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Menjodohkan tetap di bawah teks soal -->
                                <div id="root-opsi-jodohkan" class="qs-editor-section <?= $jodohkan_none ?>">
                                    <div class="qs-editor-header">
                                        <div class="qs-editor-title">
                                            <i class="bi bi-arrow-left-right"></i>
                                            Pasangan Menjodohkan
                                        </div>
                                    </div>
                                    <div class="qs-editor-body">
                                        <div id="jawaban-jodohkan"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Side Panel -->
                            <div class="qs-preview-panel" id="preview-panel">
                                <div class="qs-editor-section <?= $pg_none ?>" id="pg-options-section">
                                    <div class="qs-editor-header">
                                        <div class="qs-editor-title">
                                            <i class="bi bi-list-ul"></i>
                                            Opsi Soal
                                        </div>
                                        <span class="badge bg-light text-success border" id="pg-answer-status">
                                            <i class="bi bi-check-circle me-1"></i>Belum memilih jawaban
                                        </span>
                                    </div>
                                    <div class="qs-editor-body">
                                        <div class="qs-options-container" id="sortable-options">
                                            <?php foreach ($rangeAbjad as $abjad): ?>
                                                <div class="qs-option-card" data-opsi="<?= $abjad ?>">
                                                    <div class="qs-option-header">
                                                        <div class="qs-option-header-left">
                                                            <span class="qs-option-handle" title="Drag untuk mengurutkan">
                                                                <i class="bi bi-grip-vertical"></i>
                                                            </span>
                                                            <span class="qs-option-letter"><?= strtoupper($abjad) ?></span>
                                                            <span class="qs-option-title">Opsi <?= strtoupper($abjad) ?></span>
                                                        </div>
                                                        <div class="qs-option-header-right">
                                                            <label class="qs-option-check">
                                                                <span class="qs-option-check-label">Jawaban Benar</span>
                                                                <input class="qs-radio-custom" type="radio" name="pg_jawaban_visual" value="<?= $abjad ?>" onclick="selectPgAnswer('<?= $abjad ?>')">
                                                            </label>
                                                            <button type="button" class="qs-option-delete" onclick="clearOption('<?= $abjad ?>')" title="Hapus opsi">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="qs-option-body">
                                                        <textarea class="textjawaban" id="textjawaban_<?= $abjad ?>" name="jawaban_<?= $abjad ?>" placeholder="Tulis opsi jawaban di sini..."></textarea>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- PG Kompleks -->
                                <div id="root-opsi-pg2" class="qs-editor-section <?= $pg2_none ?>">
                                    <div class="qs-editor-header">
                                        <div class="qs-editor-title">
                                            <i class="bi bi-ui-checks-grid"></i>
                                            Opsi Jawaban PG Kompleks
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light border" id="pg2-count-badge">
                                                Terpilih <span id="pg2-checked-count">0</span>
                                            </span>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Mode PG Kompleks">
                                                <button type="button" class="btn btn-sm btn-primary pg2-mode-btn active" data-pg2-mode="pgk" onclick="setPg2Mode('pgk')">PG Kompleks</button>
                                                <button type="button" class="btn btn-sm btn-outline-primary pg2-mode-btn" data-pg2-mode="pgkk" onclick="setPg2Mode('pgkk')">PGKK Benar/Salah</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="qs-editor-body">
                                        <div class="alert alert-info py-2 px-3 small mb-3 pgkk-help d-none">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Mode <b>PGKK (Benar/Salah)</b>: setiap pernyataan dinilai <b>Benar</b> atau <b>Salah</b> via dropdown B/S. Format soal Benar/Salah per pernyataan.
                                        </div>
                                        <div id="opsi-pg2">
                                            <?php foreach ($rangeAbjad as $abjad_pg2): ?>
                                                <div class="pg-kompleks mb-3">
                                                    <div class="pg2-header">
                                                        <div class="pg2-header-left">
                                                            <span class="pg2-letter-badge abjad-badge"><?= strtoupper($abjad_pg2) ?></span>
                                                            <span class="abjad-pg2"><b>Jawaban <?= strtoupper($abjad_pg2) ?></b></span>
                                                        </div>
                                                        <div class="pg2-header-right">
                                                            <span class="pg2-check-wrap">
                                                                <span class="pg2-check-label">Jawaban Benar</span>
                                                                <input class="pg2-checkbox check-pg2" type="checkbox" name="jawaban_benar_pg2[]" value="<?= $abjad_pg2 ?>">
                                                            </span>
                                                            <span class="pg2-bs-wrap d-none">
                                                                <select class="pg2-bs-select form-select form-select-sm" data-letter="<?= $abjad_pg2 ?>">
                                                                    <option value="">B/S</option>
                                                                    <option value="B">Benar</option>
                                                                    <option value="S">Salah</option>
                                                                </select>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="pg2-editor-wrap">
                                                        <textarea class="textjawaban2" id="textjawaban2_<?= $abjad_pg2 ?>" name="jawaban2_<?= $abjad_pg2 ?>" placeholder="Tulis jawaban di sini..." style="width:100%;"></textarea>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="pg2-add-btn mt-2" id="tambah-jawaban-pg2">
                                            <i class="bi bi-plus-circle me-1"></i>Tambah Opsi
                                        </button>
                                    </div>
                                </div>
                                <style>
                                    #root-opsi-pg2.pgkk-mode .pg2-check-wrap { display: none; }
                                    #root-opsi-pg2.pgkk-mode .pg2-bs-wrap { display: inline-block; }
                                </style>

                        <!-- Isian Singkat -->
                        <div class="qs-editor-section <?= $isian_none ?>" id="root-jawaban-isian">
                            <div class="qs-editor-header">
                                <div class="qs-editor-title">
                                    <i class="bi bi-key"></i>
                                    Kunci Jawaban Isian
                                </div>
                            </div>
                            <div class="qs-editor-body">
                                <div class="qs-form-group mb-0">
                                    <label class="qs-form-label">Jawaban Benar (Kunci)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key-fill text-warning"></i></span>
                                        <input type="text" id="jawaban-isian" name="jawaban_isian" class="qs-form-control" placeholder="Ketik kunci jawaban...">
                                    </div>
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Jawaban tidak case-sensitive</small>
                                </div>
                            </div>
                        </div>

                        <!-- Essai -->
                        <div class="qs-editor-section <?= $essai_none ?>" id="root-jawaban-essai">
                            <div class="qs-editor-header">
                                <div class="qs-editor-title">
                                    <i class="bi bi-text-paragraph"></i>
                                    Rubrik/Kunci Jawaban Essai
                                </div>
                            </div>
                            <div class="qs-editor-body">
                                <div class="qs-form-group">
                                    <textarea class="textjawaban-essai" id="jawaban-essai" name="jawaban_essai" placeholder="Tulis kunci jawaban atau rubrik penilaian..." style="width:100%;"></textarea>
                                </div>

                            </div>
                        </div>

                        <!-- Urutan -->
                        <div id="root-opsi-urutan" class="qs-editor-section <?= $urutan_none ?? 'd-none' ?>">
                            <div class="qs-editor-header">
                                <div class="qs-editor-title">
                                    <i class="bi bi-sort-numeric-down"></i>
                                    Item Urutan
                                </div>
                                <button type="button" class="qs-btn btn-outline-primary btn-sm" id="tambah-item-urutan">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>Tambah Item</span>
                                </button>
                            </div>
                            <div class="qs-editor-body">
                                <div id="urutan-items-container"></div>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle me-1"></i>Susunan item dari atas ke bawah adalah urutan jawaban benar.
                                </small>
                            </div>
                        </div>

                        <!-- Cloze -->
                        <div id="root-opsi-cloze" class="qs-editor-section <?= $cloze_none ?? 'd-none' ?>">
                            <div class="qs-editor-header">
                                <div class="qs-editor-title">
                                    <i class="bi bi-input-cursor-text"></i>
                                    Kunci Isian Rumpang
                                </div>
                                <span class="badge bg-light border" id="cloze-blank-count">0 Rumpang Terdeteksi</span>
                            </div>
                            <div class="qs-editor-body">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <button type="button" class="qs-btn btn-outline-secondary btn-sm" id="btn-insert-blank">
                                        <i class="bi bi-input-cursor-text"></i>
                                        <span>Sisipkan [[rumpang]]</span>
                                    </button>
                                    <button type="button" class="qs-btn btn-outline-primary btn-sm" id="parse-cloze-blanks">
                                        <i class="bi bi-arrow-repeat"></i>
                                        <span>Deteksi Ulang</span>
                                    </button>
                                </div>
                                <div id="cloze-live-preview" class="border rounded p-3 bg-light mb-3">
                                    <span class="text-muted fst-italic small">Preview rumpang akan tampil di sini...</span>
                                </div>
                                <div id="cloze-answers-container"></div>
                            </div>
                        </div>

                        <input type="hidden" id="jawaban" name="jawaban_pg" value="">

                        <div id="preview-question-container" class="d-none">
                            <div class="qs-preview-question" id="preview-soal"></div>
                            <div class="qs-preview-options" id="preview-options"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Action Bar -->
            <div class="qs-action-bar">
                <div class="qs-action-group">
                    <button type="button" class="qs-btn btn-outline-secondary" onclick="clearForm()">
                        <i class="bi bi-x-lg"></i>
                        <span>Bersihkan</span>
                    </button>
                </div>
                <div class="qs-action-group">
                    <span class="text-muted small me-3" id="last-saved">
                        <i class="bi bi-clock me-1"></i>Belum disimpan
                    </span>
                    <button type="submit" class="qs-btn qs-btn-lg qs-btn-success" id="btn-simpan">
                        <i class="bi bi-floppy"></i>
                        <span>Simpan Soal</span>
                    </button>
                </div>
            </div>
        </div>
            </div>
        </div>

        <!-- Empty State -->
        <div class="alert alert-info d-none" id="empty-soal">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                <div>
                    <strong>Belum ada soal</strong>
                    <p class="mb-0">Pilih nomor soal atau klik "Tambah Soal" untuk memulai.</p>
                </div>
            </div>
        </div>

        <?= form_close() ?>
    </div>
</div>
<!-- AI Generate Modal - Kurikulum Merdeka Pembelajaran Mendalam -->
<div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-labelledby="aiGenerateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 text-white py-3" style="background:linear-gradient(135deg,#0d6efd 0%,#6610f2 50%,#6f42c1 100%);">
                <div>
                    <h5 class="modal-title mb-0" id="aiGenerateModalLabel">
                        <i class="bi bi-robot me-2"></i>Soal Generator by ikasmedia
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Step indicator -->
                <div class="d-flex align-items-center justify-content-center py-3 border-bottom" style="background:#f8f9ff;">
                    <div class="d-flex align-items-center" id="step-indicator">
                        <div class="step-item active" data-step="1">
                            <span class="step-num">1</span>
                            <span class="step-label">Konfigurasi</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="2">
                            <span class="step-num">2</span>
                            <span class="step-label">Preview</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" data-step="3">
                            <span class="step-num">3</span>
                            <span class="step-label">Simpan</span>
                        </div>
                    </div>
                </div>

                <!-- STEP 1: Simplified Configuration -->
                <div id="ai-step-1" class="p-4">
                    <form id="aiGenerateForm">
                        <input type="hidden" name="bank_id" value="<?= $bank->id_bank ?? 0 ?>">
                        <input type="hidden" name="kartu_item_id" id="ai-kartu-item-id" value="">
                        <input type="hidden" name="tipe_soal" id="ai-tipe-hidden" value="<?= esc((string) ($tab_active ?? '1')) ?>">
                        <input type="hidden" id="ai-similar-mode" value="0">
                        <input type="hidden" id="ai-similar-source" value="0">
                        <input type="hidden" id="ai-similar-jumlah" value="1">
                        <input type="hidden" name="nomor_target" id="ai-nomor-hidden" value="<?= esc((string) ($p_no ?? '1')) ?>">
                        <input type="hidden" name="jenjang" id="ai-jenjang" value="SMA">
                        <input type="hidden" name="kelas" id="ai-kelas" value="10">
                        <input type="hidden" name="topic" id="ai-topic" value="">
                        <input type="hidden" name="topik" id="ai-topik" value="">
                        <input type="hidden" name="indikator" id="ai-indikator" value="">
                        <input type="hidden" name="fokus_kompetensi" id="ai-fokus-kompetensi" value="literasi_membaca">
                        <input type="hidden" name="ai_model" id="ai-preferred-model" value="">

                        <!-- Mode Soal Mirip: panel konfigurasi muncul saat membuat soal baru setara kompetensi -->
                        <div id="ai-similar-panel" class="d-none mb-4" style="border-radius:14px;overflow:hidden;border:1px solid #99f6e4;">
                            <div class="px-3 py-2" style="background:linear-gradient(135deg,#f0fdfa,#ecfdf5);border-bottom:1px solid #6ee7b7;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-plus-square-dotted text-success fs-5"></i>
                                    <div>
                                        <strong class="text-success">Buat Soal Mirip (Setara Kompetensi)</strong>
                                        <small class="d-block text-muted">Kompetensi &amp; level berpikir sama — skenario benar-benar berbeda</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-success ms-auto" onclick="closeAISimilarMode()">
                                        <i class="bi bi-x-lg"></i> Keluar
                                    </button>
                                </div>
                            </div>
                            <div class="p-3" style="background:#fff;">
                                <!-- Cuplikan soal referensi -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted small mb-1">
                                        <i class="bi bi-eye me-1"></i>Soal Referensi (digunakan AI sebagai acuan kompetensi):
                                    </label>
                                    <div id="ai-similar-source-preview" class="p-2 rounded border bg-light small text-muted" style="max-height:80px;overflow:hidden;font-style:italic;line-height:1.4;">
                                        —
                                    </div>
                                </div>
                                <!-- Jumlah soal mirip -->
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-123 me-1 text-success"></i>Jumlah Opsi Pembanding
                                        </label>
                                        <select id="ai-similar-jumlah-select" class="form-select" onchange="document.getElementById('ai-similar-jumlah').value = this.value;">
                                            <option value="1" selected>1 opsi soal</option>
                                            <option value="2">2 opsi alternatif (pilih 1 terbaik)</option>
                                            <option value="3">3 opsi alternatif (pilih 1 terbaik)</option>
                                        </select>
                                        <small class="text-muted" style="font-size:0.75rem;">Pilih 1 opsi terbaik untuk menggantikan soal aktif (No. <span id="ai-similar-active-no"><?= $p_no ?? 1 ?></span>)</small>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="alert alert-success border-0 small mb-0 py-2" style="background:#f0fdf4;">
                                            <i class="bi bi-lightbulb me-1"></i>
                                            <b>Cara kerja:</b> AI menganalisis kompetensi &amp; level berpikir soal referensi, lalu membuat opsi alternatif dengan <strong>skenario berbeda</strong>. Anda cukup <strong>memilih 1 opsi terbaik</strong> untuk menggantikan soal aktif ini.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kisi-kisi Summary Card -->
                        <div id="ai-kisi-summary" class="mb-4" style="border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;">
                            <div class="px-3 py-2" style="background:linear-gradient(135deg,#f0fdf4,#ecfdf5);border-bottom:1px solid #bbf7d0;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-link-45deg text-success fs-5"></i>
                                    <div>
                                        <strong class="text-success" id="ai-kisi-title">Data dari Kisi-kisi</strong>
                                        <small class="d-block text-muted" id="ai-kisi-subtitle">Konfigurasi diisi otomatis dari metadata soal</small>
                                    </div>
                                    <?php if (!empty($bank->subtes_code) && $bank->subtes_code !== 'GENERAL'): ?>
                                        <span class="badge bg-primary ms-auto" id="ai-subtes-badge" title="Bank ini terhubung sub-tes — AI akan menyusun soal sesuai konteks sub-tes">
                                            <i class="bi bi-diagram-2 me-1"></i><?= esc($bank->subtes_nama ?? $bank->subtes_code) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="p-3" style="background:#fff;">
                                <div class="row g-2">
                                    <!-- Target Soal Info -->
                                    <div class="col-md-3">
                                        <div class="p-2" style="background:linear-gradient(135deg,#fef3c7,#fef9c3);border-radius:8px;border:1px solid #fde68a;">
                                            <small class="text-muted d-block mb-1"><i class="bi bi-hash me-1 text-warning"></i>No. Soal Target</small>
                                            <span id="ai-summary-no" class="fw-bold text-warning-emphasis fs-5"><?= $p_no ?? '-' ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-2" style="background:linear-gradient(135deg,#e0e7ff,#eef2ff);border-radius:8px;border:1px solid #c7d2fe;">
                                            <small class="text-muted d-block mb-1"><i class="bi bi-layers me-1 text-primary"></i>Jenis Soal</small>
                                            <span id="ai-summary-jenis-label" class="fw-bold text-primary fs-5">
                                                <?php
                                                $jenis_labels = ['1'=>'PG','2'=>'PG Kompleks','3'=>'Menjodohkan','4'=>'Isian Singkat','5'=>'Esai','6'=>'Urutan','7'=>'Isian Rumpang'];
                                                echo $jenis_labels[$tab_active ?? '1'] ?? 'PG';
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-2" style="background:#f8fafc;border-radius:8px;">
                                            <small class="text-muted d-block mb-1"><i class="bi bi-tag me-1"></i>Topik/Materi</small>
                                            <span class="fw-semibold small" id="ai-summary-topik">-</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-2" style="background:#f8fafc;border-radius:8px;">
                                            <small class="text-muted d-block mb-1"><i class="bi bi-list-check me-1"></i>Tipe Soal</small>
                                            <span class="fw-semibold small" id="ai-summary-tipe">-</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-2" style="background:#f8fafc;border-radius:8px;">
                                            <small class="text-muted d-block mb-1"><i class="bi bi-bullseye me-1"></i>Indikator/CP</small>
                                            <span class="fw-semibold small" id="ai-summary-indikator">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Kisi-kisi Warning (fallback manual input) -->
                        <div id="ai-no-kisi-warning" class="alert border-0 mb-4 d-none" style="background:linear-gradient(135deg,#fef3c7,#fef9c3);border-radius:12px;">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-5 mt-1"></i>
                                <div>
                                    <strong>Kisi-kisi Belum Dipilih</strong><br>
                                    <small class="text-muted">Pilih kisi-kisi terlebih dahulu di bawah, atau isi topik manual.</small>
                                    <div class="mt-2">
                                        <textarea name="topic_manual" id="ai-topic-manual" class="form-control form-control-sm" rows="2" placeholder="Tulis topik/CP. Contoh: Persamaan Linear Dua Variabel — Peserta didik mampu menyelesaikan SPLDV"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Controls -->
                        <div class="row g-3" id="ai-user-controls">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-bar-chart me-1 text-primary"></i>Level AKM <span class="text-danger">*</span>
                                </label>
                                <select name="level_akm" id="ai-level-akm" required class="form-select" onchange="updateAkmDescription()">
                                    <option value="auto" selected>Otomatis (sesuai C1–C6)</option>
                                    <option value="level_1">Level 1 — Menemukan/Memahami</option>
                                    <option value="level_2">Level 2 — Menginterpretasi/Menerapkan</option>
                                    <option value="level_3">Level 3 — Menalar/Mengevaluasi</option>
                                </select>
                                <small class="text-muted" style="font-size: 0.75rem;">Level kognitif resmi AKM</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-globe me-1 text-primary"></i>Konteks AKM <span class="text-danger">*</span>
                                </label>
                                <select name="konteks_akm" id="ai-konteks-akm" required class="form-select" onchange="updateAkmDescription()">
                                    <option value="auto" selected>Otomatis (campuran)</option>
                                    <option value="personal">Personal</option>
                                    <option value="sosial_budaya">Sosial-Budaya</option>
                                    <option value="saintifik">Saintifik</option>
                                </select>
                                <small class="text-muted" style="font-size: 0.75rem;">Tiga konteks resmi AKM</small>
                            </div>
                            <div class="col-md-3 d-none">
                                <label class="form-label fw-semibold text-muted">
                                    <i class="bi bi-hash me-1"></i>Jumlah <i class="bi bi-lock-fill ms-1 ms-1 text-warning" style="font-size:0.8em"></i>
                                </label>
                                <input type="number" name="jumlah_soal" id="ai-jumlah" value="1" class="form-control bg-light text-muted" readonly style="cursor:not-allowed;">
                                <small class="text-muted" style="font-size: 0.75rem;">1 soal per generate</small>
                            </div>
                            <div class="col-md-3 d-none" id="ai-pg2-mode-col">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-ui-checks me-1 text-primary"></i>Mode PG Kompleks
                                </label>
                                <select name="subtipe_pg2" id="ai-subtipe-pg2" class="form-select" required>
                                    <option value="pgk" selected>PGK — Pilih Beberapa Benar</option>
                                    <option value="pgkk">PGKK — Benar/Salah</option>
                                </select>
                                <small class="text-muted" style="font-size: 0.75rem;">PGKK: tiap pernyataan dinilai B/S</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-speedometer2 me-1 text-primary"></i>Tingkat Kesulitan <span class="text-danger">*</span>
                                </label>
                                <select name="tingkat_kesulitan" id="ai-kesulitan" required class="form-select">
                                    <option value="C1">C1 - Mengingat</option>
                                    <option value="C2">C2 - Memahami</option>
                                    <option value="C3">C3 - Menerapkan</option>
                                    <option value="C4" selected>C4 - Menganalisis</option>
                                    <option value="C5">C5 - Mengevaluasi</option>
                                    <option value="C6">C6 - Mencipta</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-file-earmark-richtext me-1 text-primary"></i>Jenis Stimulus <span class="text-danger">*</span>
                                </label>
                                <select name="jenis_stimulus" id="ai-stimulus-type" required class="form-select">
                                    <option value="kasus_kontekstual" selected>Kasus Kontekstual / Cerita</option>
                                    <option value="data_tabel_infografis">📊 Data Tabel &amp; Statistik (HTML Table)</option>
                                    <option value="grafik_diagram_svg">📈 Grafik &amp; Diagram Visual (Inline SVG)</option>
                                    <option value="teks_info_dialog_grafik">Teks Informasi, Dialog &amp; Narasi</option>
                                    <option value="teks_fiksi_sastra">Teks Fiksi / Sastra</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-bullseye me-1 text-primary"></i>Fokus Kompetensi <span class="text-danger">*</span>
                                </label>
                                <select name="fokus_kompetensi" id="ai-fokus-kompetensi" required class="form-select">
                                    <option value="literasi_membaca" selected>Literasi Membaca</option>
                                    <option value="numerasi">Numerasi</option>
                                    <option value="literasi_sains">Literasi Sains</option>
                                    <option value="survei_karakter">Survei Karakter</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-sliders me-1 text-primary"></i>Mode Soal <span class="text-danger">*</span>
                                </label>
                                <select name="mode_soal" id="ai-mode-soal" required class="form-select" onchange="updateModeDescription(this.value)">
                                    <option value="soal_biasa">Soal Biasa (Sederhana)</option>
                                    <option value="asesmen_nasional" selected>Asesmen Nasional / AKM (Kompleks)</option>
                                </select>
                                <small class="text-muted" id="ai-mode-desc" style="font-size: 0.75rem;">Stimulus kontekstual dengan analisis mendalam</small>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="mt-3 pt-3 border-top">
                            <label class="form-label fw-semibold mb-2">
                                <i class="bi bi-cpu me-1 text-primary"></i>Model AI
                            </label>
                            <div class="ai-model-selector">
                                <div class="ai-model-chip active" data-model="" onclick="selectAIModel(this, '')">
                                    <i class="bi bi-lightning-charge"></i>
                                    <span>Auto <span class="model-speed">(Tercepat)</span></span>
                                </div>
                                <div class="ai-model-chip" data-model="gemini-3.5-flash-lite" onclick="selectAIModel(this, 'gemini-3.5-flash-lite')">
                                    <i class="bi bi-stars"></i>
                                    <span>Flash 3.5 Lite <span class="model-speed">(Terbaru)</span></span>
                                </div>
                                <div class="ai-model-chip" data-model="gemini-3.6-flash" onclick="selectAIModel(this, 'gemini-3.6-flash')">
                                    <i class="bi bi-cpu"></i>
                                    <span>Flash 3.6 <span class="model-speed">(Cepat)</span></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- STEP 2: Preview Results -->
                <div id="ai-step-2" class="d-none">
                    <!-- Loading state with animated orb -->
                    <div id="ai-loading" class="text-center py-5">
                        <div class="ai-loading-orb">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h5 style="background:linear-gradient(135deg,#3b82f6,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:700;">Generating Soal...</h5>
                        <p class="text-muted mb-3" id="ai-loading-msg">AI sedang menyusun soal untuk Anda.</p>
                        <div class="progress mx-auto mb-2" style="max-width:400px;height:6px;border-radius:4px;">
                            <div class="progress-bar progress-bar-animated" style="width:0%;background:linear-gradient(90deg,#3b82f6,#8b5cf6);transition:width 1s;" id="ai-progress-bar"></div>
                        </div>
                        <small class="text-muted" id="ai-elapsed-timer">0 detik</small>
                    </div>
                    <!-- Analytics Bar (shown after generation) -->
                    <div id="ai-analytics" class="ai-analytics-bar d-none">
                        <div class="ai-analytics-item"><i class="bi bi-cpu"></i> <span id="ai-stat-model">-</span></div>
                        <div class="ai-analytics-item"><i class="bi bi-clock-history"></i> <span id="ai-stat-time">-</span></div>
                        <div class="ai-analytics-item"><i class="bi bi-check2-square"></i> <span id="ai-stat-count">-</span> soal</div>
                    </div>
                    <!-- Preview content -->
                    <div id="ai-preview" class="d-none">
                        <div class="px-4 pt-3 pb-2 border-bottom d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#f8fafc,#f0f4ff);">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class="bi bi-eye me-1 text-primary"></i>Preview Soal</h6>
                                <small class="text-muted" id="ai-preview-info"></small>
                            </div>
                        </div>
                        <div id="ai-preview-list" class="p-3" style="max-height:55vh;overflow-y:auto;"></div>
                    </div>
                    <!-- Error state -->
                    <div id="ai-error" class="d-none text-center py-5 px-4">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size:3rem;"></i>
                        <h5 class="mt-3 text-danger">Gagal Generate</h5>
                        <p class="text-muted" id="ai-error-msg"></p>
                        <button type="button" class="btn btn-outline-primary" onclick="goToStep(1)">
                            <i class="bi bi-arrow-left me-1"></i>Kembali & Coba Lagi
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Success -->
                <div id="ai-step-3" class="d-none text-center py-5" style="background:linear-gradient(180deg,#f0fdf4 0%,#fff 100%);">
                    <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#10b981);display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.2rem;box-shadow:0 8px 30px rgba(34,197,94,.3);animation:ai-orb-pulse 2s infinite;">
                        <i class="bi bi-check-lg text-white" style="font-size:2.8rem;"></i>
                    </div>
                    <h4 style="background:linear-gradient(135deg,#16a34a,#059669);-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-weight:800;">Soal Berhasil Disimpan!</h4>
                    <p class="text-muted mb-3" id="ai-success-msg"></p>
                    <div class="d-inline-flex gap-3 flex-wrap justify-content-center" id="ai-success-stats">
                        <!-- Filled dynamically -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 py-3" style="background:#f8f9fa;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="ai-btn-close">Tutup</button>
                <button type="button" class="btn btn-outline-primary d-none" id="ai-btn-back" onclick="goToStep(1)">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </button>
                <button type="button" class="btn btn-primary" id="ai-btn-generate" onclick="submitAIGenerate()">
                    <i class="bi bi-magic me-1"></i>Generate Soal
                </button>
                <button type="button" class="btn btn-success d-none" id="ai-btn-save" onclick="saveAIGenerated()">
                    <i class="bi bi-floppy me-1"></i>Simpan Soal Terpilih
                </button>
                <button type="button" class="btn btn-primary d-none" id="ai-btn-reload" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Halaman
                </button>
            </div>
        </div>
    </div>
</div>


<style>
    /* ============================================
       AI GENERATE MODAL - Premium Design System
       ============================================ */
    .step-item { display:flex; align-items:center; gap:8px; }
    .step-num {
        width:32px; height:32px; border-radius:50%;
        background:linear-gradient(135deg,#e2e8f0,#cbd5e1); color:#64748b;
        display:inline-flex; align-items:center; justify-content:center;
        font-weight:700; font-size:13px; transition:all .4s cubic-bezier(.4,0,.2,1);
        box-shadow:0 1px 3px rgba(0,0,0,.08);
    }
    .step-label { font-size:13px; color:#94a3b8; font-weight:500; transition:all .4s; }
    .step-item.active .step-num {
        background:linear-gradient(135deg,#3b82f6,#8b5cf6); color:#fff;
        box-shadow:0 4px 15px rgba(59,130,246,.35);
        animation:ai-step-pulse 2s infinite;
    }
    .step-item.active .step-label { color:#3b82f6; font-weight:700; }
    .step-item.done .step-num {
        background:linear-gradient(135deg,#22c55e,#10b981); color:#fff;
        box-shadow:0 2px 8px rgba(34,197,94,.3);
    }
    .step-item.done .step-label { color:#22c55e; font-weight:600; }
    .step-line { width:50px; height:3px; background:linear-gradient(90deg,#e2e8f0,#cbd5e1); margin:0 6px; border-radius:4px; transition:background .5s; }
    .step-item.done + .step-line { background:linear-gradient(90deg,#22c55e,#86efac); }
    @keyframes ai-step-pulse {
        0%,100% { box-shadow:0 4px 15px rgba(59,130,246,.35); }
        50% { box-shadow:0 4px 25px rgba(59,130,246,.55); }
    }

    /* AI Generate Modal Header */
    #aiGenerateModal .modal-content { border:none; border-radius:20px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,.15); }
    #aiGenerateModal .modal-header {
        background:linear-gradient(135deg,#1e3a8a 0%,#3b82f6 30%,#8b5cf6 70%,#a855f7 100%);
        position:relative; overflow:hidden;
    }
    #aiGenerateModal .modal-header::before {
        content:''; position:absolute; top:-50%; right:-20%; width:400px; height:400px;
        background:radial-gradient(circle,rgba(255,255,255,.08) 0%,transparent 70%);
        border-radius:50%; pointer-events:none;
    }
    #aiGenerateModal .modal-header::after {
        content:''; position:absolute; bottom:-30%; left:-10%; width:300px; height:300px;
        background:radial-gradient(circle,rgba(255,255,255,.05) 0%,transparent 70%);
        border-radius:50%; pointer-events:none;
    }

    /* AI Analytics Bar */
    .ai-analytics-bar {
        display:flex; align-items:center; gap:16px; flex-wrap:wrap;
        padding:10px 20px;
        background:linear-gradient(135deg,#f0fdf4,#ecfdf5);
        border-bottom:1px solid #bbf7d0;
        font-size:12px; color:#166534;
    }
    .ai-analytics-item {
        display:inline-flex; align-items:center; gap:6px;
        padding:4px 12px; background:rgba(255,255,255,.7);
        border-radius:20px; font-weight:600;
        backdrop-filter:blur(4px);
    }
    .ai-analytics-item i { font-size:14px; }

    /* AI Soal Cards - Premium */
    .ai-soal-card {
        border:1px solid #e2e8f0; border-radius:16px;
        margin-bottom:16px; overflow:hidden; transition:all .3s cubic-bezier(.4,0,.2,1);
        background:#fff; position:relative;
    }
    .ai-soal-card:hover { border-color:#93c5fd; box-shadow:0 8px 30px rgba(59,130,246,.12); transform:translateY(-2px); }
    .ai-soal-card.deselected { opacity:.45; transform:scale(.98); }
    .ai-soal-card .card-header-custom {
        padding:12px 20px; background:linear-gradient(135deg,#f8fafc,#f1f5f9);
        display:flex; align-items:center; gap:12px; border-bottom:1px solid #e2e8f0;
        flex-wrap:wrap;
    }
    .ai-soal-card .card-body-custom .stimulus { background:#f8fafc; padding:14px; border:1px solid #e2e8f0; border-left:4px solid #3b82f6; border-radius:8px; margin-bottom:14px; }
    .ai-soal-card .card-body-custom table, .soal-content table, .note-editable table { width:100%; max-width:100%; border-collapse:collapse; margin:8px 0; font-size:13px; background:#fff; }
    .ai-soal-card .card-body-custom table th, .soal-content table th, .note-editable table th { background:#f1f5f9; font-weight:700; color:#1e293b; padding:6px 10px; border:1px solid #cbd5e1; text-align:center; }
    .ai-soal-card .card-body-custom table td, .soal-content table td, .note-editable table td { padding:6px 10px; border:1px solid #cbd5e1; }
    .ai-soal-card .card-body-custom svg, .soal-content svg, .note-editable svg { max-width:100%; height:auto; display:block; margin:10px auto; border-radius:6px; }
    .ai-soal-card .opsi-item { padding:8px 14px; margin:5px 0; border-radius:8px; background:#f8fafc; font-size:13px; border:1px solid #e2e8f0; transition:all .2s; }
    .ai-soal-card .opsi-item:hover { background:#eff6ff; border-color:#bfdbfe; }
    .ai-soal-card .opsi-item.correct { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-color:#86efac; border-left:3px solid #22c55e; }

    /* Card Action Buttons */
    .ai-card-actions { display:flex; gap:6px; margin-left:auto; }
    .ai-card-action-btn {
        width:30px; height:30px; border-radius:8px; border:1px solid #e2e8f0;
        background:#fff; color:#64748b; cursor:pointer;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:13px; transition:all .2s;
    }
    .ai-card-action-btn:hover { background:#eff6ff; border-color:#93c5fd; color:#3b82f6; }
    .ai-card-action-btn.btn-regen:hover { background:#fef3c7; border-color:#fbbf24; color:#d97706; }

    /* Quality Score Badge */
    .ai-quality-badge {
        display:inline-flex; align-items:center; gap:4px;
        padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
        letter-spacing:.3px;
    }
    .ai-quality-badge.high { background:linear-gradient(135deg,#dcfce7,#bbf7d0); color:#166534; }
    .ai-quality-badge.medium { background:linear-gradient(135deg,#fef3c7,#fde68a); color:#92400e; }
    .ai-quality-badge.low { background:linear-gradient(135deg,#fee2e2,#fecaca); color:#991b1b; }

    /* Editable fields */
    .ai-editable {
        border:1px dashed transparent; border-radius:8px;
        padding:8px 10px; transition:all .25s; min-height:34px;
    }
    .ai-editable:hover { border-color:#bfdbfe; background:#f0f9ff; }
    .ai-editable:focus { border-color:#3b82f6; background:#eff6ff; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
    .ai-editable-inline {
        display:inline-block; min-width:60%;
        border:1px dashed transparent; border-radius:6px;
        padding:2px 6px; transition:all .25s;
    }
    .ai-editable-inline:hover { border-color:#bfdbfe; background:#f0f9ff; }
    .ai-editable-inline:focus { border-color:#3b82f6; background:#eff6ff; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

    /* AI Loading Animation */
    .ai-loading-orb {
        width:80px; height:80px; margin:0 auto 20px;
        border-radius:50%; position:relative;
        background:linear-gradient(135deg,#3b82f6,#8b5cf6);
        animation:ai-orb-pulse 2s ease-in-out infinite;
    }
    .ai-loading-orb::before {
        content:''; position:absolute; inset:-4px;
        border-radius:50%; border:3px solid transparent;
        border-top-color:#3b82f6; border-right-color:#8b5cf6;
        animation:ai-orb-spin 1.2s linear infinite;
    }
    .ai-loading-orb i { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:#fff; font-size:28px; }
    @keyframes ai-orb-pulse { 0%,100% { transform:scale(1); box-shadow:0 0 0 0 rgba(59,130,246,.4); } 50% { transform:scale(1.05); box-shadow:0 0 30px 10px rgba(139,92,246,.2); } }
    @keyframes ai-orb-spin { 100% { transform:rotate(360deg); } }

    /* Model Selector */
    .ai-model-selector { display:flex; gap:8px; flex-wrap:wrap; }
    .ai-model-chip {
        padding:6px 14px; border-radius:20px; border:1.5px solid #e2e8f0;
        background:#fff; font-size:12px; font-weight:600; color:#64748b;
        cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px;
    }
    .ai-model-chip:hover { border-color:#93c5fd; color:#3b82f6; background:#eff6ff; }
    .ai-model-chip.active { border-color:#3b82f6; color:#fff; background:linear-gradient(135deg,#3b82f6,#2563eb); box-shadow:0 2px 8px rgba(59,130,246,.3); }
    .ai-model-chip .model-speed { font-size:10px; opacity:.7; }

    .swal2-container { z-index: 20000 !important; }

    /* PG Biasa styles */
    .pg-opsi-card {
        border:1px solid #e2e8f0;
        border-radius:14px;
        background:#fff;
        overflow:hidden;
        transition:all .3s cubic-bezier(.4,0,.2,1);
        position:relative;
    }
    .pg-opsi-card:hover { border-color:#c8d0da; box-shadow:0 4px 16px rgba(0,0,0,.06); }
    .pg-opsi-card.is-correct { border-color:#0d6efd; background:linear-gradient(135deg,#eff6ff,#e8f4fd); box-shadow:0 2px 12px rgba(13,110,253,.1); }
    .pg-opsi-card .pg-opsi-header {
        display:flex; align-items:center; justify-content:space-between;
        padding:10px 16px;
        background:linear-gradient(135deg,#f8f9fa,#f1f3f5);
        border-bottom:1px solid #e9ecef;
        gap:8px;
    }
    .pg-opsi-card.is-correct .pg-opsi-header { background:linear-gradient(135deg,#dbeafe,#bfdbfe); border-bottom-color:#93c5fd; }
    .pg-letter-badge {
        display:inline-flex; align-items:center; justify-content:center;
        width:36px; height:36px;
        border-radius:10px;
        background:linear-gradient(135deg,#3b82f6,#2563eb);
        color:#fff; font-weight:700; font-size:15px;
        flex-shrink:0;
        box-shadow:0 2px 6px rgba(59,130,246,.25);
        transition:all .3s;
    }
    .pg-opsi-card.is-correct .pg-letter-badge { background:linear-gradient(135deg,#16a34a,#22c55e); box-shadow:0 2px 8px rgba(22,163,74,.3); }
    .pg-opsi-header-left { display:flex; align-items:center; gap:10px; }
    .pg-opsi-header-right { display:flex; align-items:center; gap:12px; }
    .pg-opsi-label b { font-size:14px; color:#374151; }
    .pg-check-label { font-size:12px; font-weight:600; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; }
    .pg-opsi-card.is-correct .pg-check-label { color:#16a34a; }
    .pg-radio {
        appearance:none; -webkit-appearance:none;
        width:28px; height:28px;
        border:2px solid #cbd5e1; border-radius:50%;
        cursor:pointer;
        transition:all .2s;
        position:relative; flex-shrink:0;
        background:#fff;
    }
    .pg-radio:hover { border-color:#3b82f6; box-shadow:0 0 0 4px rgba(59,130,246,.1); }
    .pg-radio:checked {
        background:linear-gradient(135deg,#16a34a,#22c55e);
        border-color:#16a34a;
        box-shadow:0 2px 8px rgba(22,163,74,.3);
    }
    .pg-radio:checked::after {
        content:''; position:absolute; top:50%; left:50%;
        transform:translate(-50%,-50%);
        width:10px; height:10px; border-radius:50%;
        background:#fff;
    }
    .pg-opsi-card .pg-opsi-editor { padding:12px 16px 16px; }
    .pg-opsi-card .note-editor { border-radius:10px !important; overflow:visible; border-color:#e2e8f0 !important; }
    .pg-opsi-card .note-editor .note-editing-area { min-height:60px; }

    /* Urutan / Sequencing styles */
    .urutan-item-card {
        background:#fff; border:1px solid #e2e8f0; border-radius:12px;
        padding:8px 12px; display:flex; align-items:center; gap:12px;
        transition:all .2s; box-shadow:0 1px 2px rgba(0,0,0,.03);
    }
    .urutan-item-card:hover { border-color:#9333ea; box-shadow:0 4px 12px rgba(147,51,234,.08); transform:translateY(-1px); }
    .urutan-handle {
        color:#cbd5e1; cursor:grab; padding:4px; font-size:18px;
    }
    .urutan-handle:hover { color:#64748b; }
    .urutan-num-badge {
        width:28px; height:28px; background:#f3e8ff; color:#9333ea;
        border-radius:8px; font-weight:700; font-size:14px;
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .urutan-input { border:none; background:transparent; font-size:15px; font-weight:500; color:#334155; }
    .urutan-input:focus { box-shadow:none; background:#f8fafc; }
    .urutan-remove {
        width:32px; height:32px; border-radius:8px; background:transparent; border:none;
        color:#94a3b8; display:flex; align-items:center; justify-content:center;
        transition:all .2s;
    }
    .urutan-remove:hover { background:#fef2f2; color:#ef4444; }
    .pg-kompleks {
        border:1px solid #e2e8f0;
        border-radius:14px;
        padding:0;
        background:#fff;
        transition:all .3s cubic-bezier(.4,0,.2,1);
        overflow:hidden;
        position:relative;
    }
    .pg-kompleks:hover { border-color:#c8d0da; box-shadow:0 4px 16px rgba(0,0,0,.06); }
    .pg-kompleks.is-checked { border-color:#198754; background:linear-gradient(135deg,#f0fdf4,#ecfdf5); box-shadow:0 2px 12px rgba(25,135,84,.1); }
    .pg-kompleks .pg2-header {
        display:flex; align-items:center; justify-content:space-between;
        padding:10px 16px;
        background:linear-gradient(135deg,#f8f9fa,#f1f3f5);
        border-bottom:1px solid #e9ecef;
        gap:8px;
    }
    .pg-kompleks.is-checked .pg2-header { background:linear-gradient(135deg,#d1fae5,#dcfce7); border-bottom-color:#bbf7d0; }
    .pg2-letter-badge {
        display:inline-flex; align-items:center; justify-content:center;
        width:36px; height:36px;
        border-radius:10px;
        background:linear-gradient(135deg,#6366f1,#8b5cf6);
        color:#fff; font-weight:700; font-size:15px;
        flex-shrink:0;
        box-shadow:0 2px 6px rgba(99,102,241,.25);
    }
    .pg-kompleks.is-checked .pg2-letter-badge { background:linear-gradient(135deg,#16a34a,#22c55e); box-shadow:0 2px 6px rgba(22,163,74,.3); }
    .pg2-header-left { display:flex; align-items:center; gap:10px; }
    .pg2-header-right { display:flex; align-items:center; gap:12px; }
    .pg2-check-label { font-size:12px; font-weight:600; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; }
    .pg-kompleks.is-checked .pg2-check-label { color:#16a34a; }
    .pg2-checkbox {
        appearance:none; -webkit-appearance:none;
        width:28px; height:28px;
        border:2px solid #cbd5e1; border-radius:8px;
        cursor:pointer;
        transition:all .2s;
        position:relative;
        flex-shrink:0;
        background:#fff;
    }
    .pg2-checkbox:hover { border-color:#6366f1; box-shadow:0 0 0 4px rgba(99,102,241,.1); }
    .pg2-checkbox:checked {
        background:linear-gradient(135deg,#16a34a,#22c55e);
        border-color:#16a34a;
        box-shadow:0 2px 8px rgba(22,163,74,.3);
    }
    .pg2-checkbox:checked::after {
        content:'\2713'; position:absolute; top:50%; left:50%;
        transform:translate(-50%,-50%); color:#fff; font-size:16px; font-weight:700;
    }
    .pg2-delete-btn {
        background:none; border:none; color:#94a3b8; cursor:pointer;
        width:32px; height:32px; border-radius:8px;
        display:inline-flex; align-items:center; justify-content:center;
        transition:all .2s; font-size:14px;
    }
    .pg2-delete-btn:hover { background:#fef2f2; color:#ef4444; }
    .pg-kompleks .pg2-editor-wrap { padding:12px 16px 16px; }
    .pg-kompleks .note-editor { border-radius:10px !important; overflow:visible; border-color:#e2e8f0 !important; }
    .pg-kompleks .note-editor .note-editing-area { min-height:60px; }
    .pg-kompleks .note-editor .note-statusbar,
    .pg-opsi-card .note-editor .note-statusbar,
    .qs-option-card .note-editor .note-statusbar { display:none; }
    .pg-kompleks .note-editor .note-editable,
    .pg-opsi-card .note-editor .note-editable,
    .qs-option-card .note-editor .note-editable {
        min-height:72px !important;
        height:auto !important;
        max-height:none !important;
        overflow-y:visible !important;
    }
    .pg2-add-btn {
        display:flex; align-items:center; justify-content:center;
        width:100%; padding:14px 20px;
        border:2px dashed #cbd5e1; border-radius:14px;
        background:transparent; color:#64748b;
        font-size:14px; font-weight:600;
        cursor:pointer; transition:all .25s;
        margin-top:4px;
    }
    .pg2-add-btn:hover { border-color:#6366f1; color:#6366f1; background:#f5f3ff; }
    .pg2-add-btn:active { transform:scale(.98); }

    /* Cloze / Isian Rumpang styles */
    .cloze-preview-blank:hover { transform:translateY(-1px); box-shadow:0 2px 8px rgba(0,0,0,.1); }
    .cloze-answer-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.08); transform:translateX(2px); }
    .cloze-answer-card .form-control:focus { box-shadow:0 0 0 3px rgba(59,130,246,.15); }
    #cloze-text-input:focus { box-shadow:0 0 0 4px rgba(13,110,253,.1); }
    #cloze-live-preview { transition:all .3s ease; }

    /* Menjodohkan / Matching styles */
    .jodohkan-container { border:1px solid #e9ecef; border-radius:14px; padding:16px; background:linear-gradient(135deg,#fafbfc,#f8f9ff); transition:all .3s; }
    .jodohkan-container:hover { border-color:#c8d0da; box-shadow:0 2px 12px rgba(0,0,0,.04); }
    .jodohkan-container .card { border-radius:12px; border:none; }
    .jodohkan-container .card-body { padding:12px; }

    /* Flexbox layout for linker columns */
    .linker-col { flex-shrink:0; }
    .linker-col-left { width:40%; min-width:180px; }
    .linker-col-right { width:40%; min-width:180px; }
    .linker-col-canvas { width:20%; min-width:80px; display:flex; align-items:flex-start; justify-content:center; }
    .linker-col-canvas canvas { display:block; }

    .linker-box { background:#fff; border:2px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); transition:all .25s cubic-bezier(.4,0,.2,1); font-size:14px; line-height:1.6; min-height:48px; display:flex; align-items:center; flex-wrap:wrap; word-break:break-word; }
    .linker-box:hover { border-color:#94a3b8; box-shadow:0 4px 16px rgba(0,0,0,.08); }
    .linker-box img { max-width:150px; border-radius:6px; }
    .linker-list { align-items:flex-start !important; }
    .linker-list canvas { cursor:crosshair; }

    /* Editor mode styles */
    .editor-list .linker-col-left,
    .editor-list .linker-col-right { width:48%; min-width:200px; }
    .editor-list .note-editor { border-radius:10px !important; overflow:hidden; }

    #jawaban-jodohkan .btn-warning { border-radius:8px; font-size:13px; padding:6px 16px; }
    #jawaban-jodohkan .btn-success { border-radius:8px; font-size:13px; padding:6px 16px; }
    #jawaban-jodohkan .btn-outline-primary { border-radius:8px; }
    #jawaban-jodohkan .table { border-radius:8px; overflow:hidden; }
    #jawaban-jodohkan .table th { background:linear-gradient(135deg,#f0f4ff,#e8f4fd); font-size:13px; font-weight:600; }
    #jawaban-jodohkan .table td { font-size:13px; vertical-align:middle; }
    #jawaban-jodohkan .hapus { border-radius:8px; font-size:12px; color:#6c757d; border:1px dashed #ced4da; background:transparent; transition:all .2s; }
    #jawaban-jodohkan .hapus:hover { border-color:#0d6efd; color:#0d6efd; background:#f0f4ff; }
</style>

<script>
    // Flag global: boleh pakai AI? (admin selalu true walau setting dimatikan)
    window.CAN_USE_AI = <?= $canUseAi ? 'true' : 'false' ?>;
    // AI Generation state
    let aiGeneratedData = [];
    let aiSelectedIndices = new Set();
    let topicMode = 'db'; // 'db' or 'manual'
    let aiTpOptionCache = [];
    let aiPreferredModel = '';
    let aiElapsedInterval = null;
    let aiStartTime = null;
    let aiLastAnalytics = null;
    const BANK_SUBTES_CODE = <?= json_encode($bank->subtes_code ?? 'GENERAL') ?>;
    const BANK_SUBTES_NAMA = <?= json_encode($bank->subtes_nama ?? '') ?>;
    const aiStimulusLabels = {
        kasus_kontekstual: 'Kasus Kontekstual',
        data_tabel_infografis: 'Tabel Data & Statistik (HTML Table)',
        grafik_diagram_svg: 'Grafik & Diagram Visual (Inline SVG)',
        teks_info_dialog_grafik: 'Teks Informasi & Narasi',
        teks_fiksi_sastra: 'Teks Fiksi / Sastra'
    };
    const aiStimulusDescs = {
        kasus_kontekstual: 'Situasi nyata/studi kasus kontekstual yang harus dianalisis siswa.',
        data_tabel_infografis: 'Stimulus menyajikan tabel data statistik/eksperimen HTML rapi yang harus diinterpretasi.',
        grafik_diagram_svg: 'Stimulus menyajikan visual vektor SVG (grafik batang, garis, diagram venn, geometri, atau bagan).',
        teks_info_dialog_grafik: 'Bacaan faktual artikel atau dialog informatif.',
        teks_fiksi_sastra: 'Teks imajinatif atau karya sastra/cerpen.'
    };
    const aiFokusLabels = {
        literasi_membaca: 'Literasi Membaca',
        numerasi: 'Numerasi',
        literasi_sains: 'Literasi Sains',
        survei_karakter: 'Survei Karakter'
    };
    const aiModeDescs = {
        asesmen_nasional: 'Stimulus kontekstual dengan analisis mendalam sesuai standar AKM.',
        soal_biasa: 'Soal sederhana dengan stimulus langsung dan bahasa yang mudah dipahami.'
    };

    function updateModeDescription(mode) {
        const descEl = document.getElementById('ai-mode-desc');
        if (descEl) descEl.textContent = aiModeDescs[mode] || '';
    }

    function updateAkmDescription() {
        const mode = document.getElementById('ai-mode-soal')?.value;
        if (mode === 'soal_biasa') return;
        const levelEl = document.getElementById('ai-level-akm');
        const konteksEl = document.getElementById('ai-konteks-akm');
        if (!levelEl || !konteksEl) return;
        const levelMap = {
            auto: 'Otomatis',
            level_1: 'Level 1 — Menemukan/Memahami',
            level_2: 'Level 2 — Menginterpretasi/Menerapkan',
            level_3: 'Level 3 — Menalar/Mengevaluasi'
        };
        const konteksMap = {
            auto: 'Otomatis',
            personal: 'Personal',
            sosial_budaya: 'Sosial-Budaya',
            saintifik: 'Saintifik'
        };
        const descEl = document.getElementById('ai-mode-desc');
        if (descEl) descEl.textContent = 'Mode AKM: ' + (levelMap[levelEl.value] || '') + ' | Konteks: ' + (konteksMap[konteksEl.value] || '');
    }

    const aiLoadingMessages = [
        'Menyusun stimulus kontekstual...',
        'Merancang pertanyaan HOTS...',
        'Memvalidasi kesesuaian Bloom\'s Taxonomy...',
        'Menyusun opsi jawaban (distractor)...',
        'Memeriksa kualitas soal...',
        'Finalisasi format soal...'
    ];

    function selectAIModel(el, model) {
        document.querySelectorAll('.ai-model-chip').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        aiPreferredModel = model;
        document.getElementById('ai-preferred-model').value = model;
    }

    function startElapsedTimer() {
        aiStartTime = Date.now();
        let msgIdx = 0;
        const timerEl = document.getElementById('ai-elapsed-timer');
        const msgEl = document.getElementById('ai-loading-msg');
        const progressBar = document.getElementById('ai-progress-bar');
        if (aiElapsedInterval) clearInterval(aiElapsedInterval);
        aiElapsedInterval = setInterval(function() {
            const elapsed = Math.floor((Date.now() - aiStartTime) / 1000);
            if (timerEl) timerEl.textContent = elapsed + ' detik';
            // Animate progress bar (fake, up to 90%)
            const pct = Math.min(90, (elapsed / 60) * 90);
            if (progressBar) progressBar.style.width = pct + '%';
            // Rotate loading messages
            if (elapsed > 0 && elapsed % 8 === 0 && msgEl) {
                msgIdx = (msgIdx + 1) % aiLoadingMessages.length;
                msgEl.style.opacity = '0';
                setTimeout(function() {
                    msgEl.textContent = aiLoadingMessages[msgIdx];
                    msgEl.style.opacity = '1';
                }, 300);
            }
        }, 1000);
    }

    document.addEventListener('change', function(e) {
        if(e.target && e.target.id === 'ai-stimulus-type') {
            const desc = aiStimulusDescs[e.target.value] || '';
            const descEl = document.getElementById('stimulus-desc');
            if(descEl) descEl.textContent = desc;
        }
    });

    function stopElapsedTimer() {
        if (aiElapsedInterval) { clearInterval(aiElapsedInterval); aiElapsedInterval = null; }
        const progressBar = document.getElementById('ai-progress-bar');
        if (progressBar) progressBar.style.width = '100%';
    }

    function showAnalytics(analytics) {
        if (!analytics) return;
        aiLastAnalytics = analytics;
        const bar = document.getElementById('ai-analytics');
        if (!bar) return;
        bar.classList.remove('d-none');
        const modelNames = {
            'gemini-3.5-flash-lite': 'Flash 3.5 Lite',
            'gemini-3.6-flash': 'Flash 3.6'
        };
        document.getElementById('ai-stat-model').textContent = modelNames[analytics.model] || analytics.model || 'Auto';
        document.getElementById('ai-stat-time').textContent = analytics.elapsed + ' detik';
        document.getElementById('ai-stat-count').textContent = analytics.count || '0';
    }

    function getQualityScore(item, tipe) {
        let score = 0;
        const soal = String(item.soal || '');
        // Length check (stimulus + question > 100 chars = good)
        if (soal.length > 200) score += 3;
        else if (soal.length > 100) score += 2;
        else score += 1;
        // Has stimulus?
        if (soal.indexOf('stimulus') !== -1 || soal.indexOf('<div') !== -1) score += 2;
        else score += 1;
        // Has options (for PG)?
        if (tipe === '1' && item.opsi_a && item.opsi_b && item.opsi_c) score += 2;
        else if (tipe === '5' && soal.length > 150) score += 2;
        else score += 1;
        // Normalize to high/medium/low
        if (score >= 6) return 'high';
        if (score >= 4) return 'medium';
        return 'low';
    }

    function getQualityLabel(level) {
        if (level === 'high') return '<i class="bi bi-star-fill"></i> Baik';
        if (level === 'medium') return '<i class="bi bi-star-half"></i> Cukup';
        return '<i class="bi bi-star"></i> Perlu Edit';
    }

    function copyAiSoalText(idx) {
        const card = document.getElementById('ai-card-' + idx);
        if (!card) return;
        const soalEl = card.querySelector('[data-field="soal"]');
        if (!soalEl) return;
        const text = soalEl.innerText || soalEl.textContent || '';
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:1500, icon:'success', title:'Teks soal disalin!' });
        }).catch(function() {
            // Fallback
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:1500, icon:'success', title:'Teks soal disalin!' });
        });
    }

    function setAIGenerateButtonLoading(isLoading) {
        const btn = document.getElementById('ai-btn-generate');
        if (!btn) return;
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalHtml || '<i class="bi bi-magic me-1"></i>Generate Soal';
        }
    }

    function prefillAIGenerateFormFromCurrentSoal() {
        const linkedKisiId = String($('#kartu_item_id').val() || '').trim();
        const linkedKisiText = String($('#text-kisi-tautan').text() || '').trim();
        const currentKesulitan = String($('#kesulitan').val() || 'C4');
        const currentTopik = String($('#topik').val() || '').trim();
        const currentIndikator = String($('#indikator').val() || '').trim();

        let currentTujuanText = String($('#meta-tujuan-text').text() || '').trim();
        if (currentTujuanText === '-') {
            currentTujuanText = '';
        } else if (currentTujuanText.includes(' - ')) {
            currentTujuanText = currentTujuanText.split(' - ').slice(1).join(' - ');
        }

        // Update hidden target fields from global JS state
        const targetNo = typeof nomor_soal !== 'undefined' ? nomor_soal : '1';
        const targetJenis = typeof jenis !== 'undefined' ? jenis : '1';
        
        $('#ai-nomor-hidden').val(targetNo);
        $('#ai-tipe-hidden').val(targetJenis);
        $('#ai-summary-no').text(targetNo);

        // Check if there is a linked kisi-kisi
        const hasKisiKisi = !!linkedKisiId;

        const kartuItemInput = document.getElementById('ai-kartu-item-id');
        if (kartuItemInput) kartuItemInput.value = linkedKisiId;
        
        // Toggle visibility of summary vs warning (only in standard generation mode)
        const isSimilarMode = String($('#ai-similar-mode').val() || '0') === '1';
        const summaryCard = document.getElementById('ai-kisi-summary');
        const warningCard = document.getElementById('ai-no-kisi-warning');
        
        if (!isSimilarMode) {
            if (summaryCard) summaryCard.classList.toggle('d-none', !hasKisiKisi);
            if (warningCard) warningCard.classList.toggle('d-none', hasKisiKisi);
        }

        // Update hidden fields and summary values
        if (hasKisiKisi) {
            // Data from kisi-kisi is available
            // Build robust topic text for AI prompt
            let topicParts = [];
            if (currentTujuanText) topicParts.push("Tujuan Pembelajaran: " + currentTujuanText);
            if (currentTopik) topicParts.push("Topik/Materi: " + currentTopik);
            if (currentIndikator) topicParts.push("Indikator: " + currentIndikator);
            
            const topicSource = topicParts.length > 0 ? topicParts.join("\n") : linkedKisiText;
            
            // Set hidden fields
            document.getElementById('ai-topic').value = topicSource;
            document.getElementById('ai-topik').value = currentTopik;
            document.getElementById('ai-indikator').value = currentIndikator;
            
            // Set summary card UI
            document.getElementById('ai-summary-topik').textContent = currentTopik || '-';
            const tbElem = document.getElementById('ai-summary-tujuan');
            if(tbElem) tbElem.textContent = currentTujuanText || '-';
            document.getElementById('ai-summary-indikator').textContent = currentIndikator || '-';
            
            // Set tipe soal UI
            const currentTipe = $('#ai-tipe-hidden').val();
            const tipeText = currentTipe == '1' ? 'Pilihan Ganda' : 
                             currentTipe == '2' ? 'PG Kompleks' :
                             currentTipe == '3' ? 'Menjodohkan' :
                             currentTipe == '4' ? 'Isian Singkat' :
                             currentTipe == '5' ? 'Uraian' :
                             currentTipe == '6' ? 'Urutan' :
                             currentTipe == '7' ? 'Isian Rumpang' : '-';
            document.getElementById('ai-summary-tipe').textContent = tipeText;
            
            // Update the "Jenis Soal" card label
            const jenisBadgeText = currentTipe == '1' ? 'PG' : 
                                  currentTipe == '2' ? 'PG Kompleks' :
                                  currentTipe == '3' ? 'Menjodohkan' :
                                  currentTipe == '4' ? 'Isian Singkat' :
                                  currentTipe == '5' ? 'Esai' :
                                  currentTipe == '6' ? 'Urutan' :
                                  currentTipe == '7' ? 'Isian Rumpang' : '-';
            $('#ai-summary-jenis-label').text(jenisBadgeText);
            
        } else {
            // Set hidden field based on manual input when no kisi-kisi
            const manualTopicBox = document.getElementById('ai-topic-manual');
            if (manualTopicBox) {
                document.getElementById('ai-topic').value = manualTopicBox.value.trim();
                
                // Make changing manual input sync with hidden ai-topic
                $('#ai-topic-manual').off('input').on('input', function() {
                    $('#ai-topic').val(this.value.trim());
                });
            }
        }

        // Pre-fill user configuration inputs
        const levelInput = document.getElementById('ai-kesulitan');
        if (levelInput && currentKesulitan) {
            levelInput.value = currentKesulitan;
        }
    }

    function openAIGenerateModal() {
        if (!window.CAN_USE_AI) {
            Swal.fire('Nonaktif', 'Fitur AI dinonaktifkan oleh administrator.', 'warning');
            return;
        }
        resetAIModal();
        setAISimilarMode(false);
        prefillAIGenerateFormFromCurrentSoal();
        var myModal = new bootstrap.Modal(document.getElementById('aiGenerateModal'));
        myModal.show();
    }

    function openAISimilarModal() {
        if (!window.CAN_USE_AI) {
            Swal.fire('Nonaktif', 'Fitur AI dinonaktifkan oleh administrator.', 'warning');
            return;
        }
        const idSoalAktif = parseInt(String($('#soal-id').val() || '0'), 10) || 0;
        if (idSoalAktif <= 0) {
            Swal.fire('Peringatan', 'Simpan soal ini terlebih dahulu sebelum membuat soal mirip.', 'warning');
            return;
        }
        resetAIModal();
        setAISimilarMode(true);
        document.getElementById('ai-similar-source').value = idSoalAktif;

        // Ambil nomor soal aktif yang sedang terbuka secara dinamis
        let currentNo = typeof nomor_soal !== 'undefined' ? nomor_soal : 0;
        if (!currentNo) {
            const txt = $('#editor-nomor').text().replace(/[^\d]/g, '').trim();
            if (txt) currentNo = parseInt(txt, 10);
        }
        if (!currentNo) {
            currentNo = parseInt($('#ai-nomor-hidden').val() || '1', 10) || 1;
        }

        const activeNoEl = document.getElementById('ai-similar-active-no');
        if (activeNoEl) activeNoEl.textContent = String(currentNo);

        const nomorHidden = document.getElementById('ai-nomor-hidden');
        if (nomorHidden) nomorHidden.value = String(currentNo);

        // Tampilkan cuplikan teks soal referensi dari Summernote atau textarea
        let srcText = '';
        if (typeof getEditorHtml === 'function' && typeof getPlainText === 'function') {
            srcText = getPlainText(getEditorHtml('#textsoal')).trim();
        }
        if (!srcText) {
            const raw = $('#textsoal').val() || '';
            srcText = $('<div>').html(raw).text().replace(/\u00a0/g, ' ').trim();
        }
        if (srcText.length > 250) {
            srcText = srcText.substring(0, 250) + '...';
        }
        const previewEl = document.getElementById('ai-similar-source-preview');
        if (previewEl) previewEl.textContent = srcText || '(Teks soal referensi siap diproses AI)';
        const jmlSelect = document.getElementById('ai-similar-jumlah-select');
        if (jmlSelect) document.getElementById('ai-similar-jumlah').value = jmlSelect.value;
        var myModal = new bootstrap.Modal(document.getElementById('aiGenerateModal'));
        myModal.show();
    }

    function closeAISimilarMode() {
        setAISimilarMode(false);
        document.getElementById('ai-similar-source').value = 0;
        prefillAIGenerateFormFromCurrentSoal();
    }

    function setAISimilarMode(active) {
        document.getElementById('ai-similar-mode').value = active ? '1' : '0';
        const panel   = document.getElementById('ai-similar-panel');
        const kisiSummary = document.getElementById('ai-kisi-summary');
        const noKisi  = document.getElementById('ai-no-kisi-warning');
        const controls = document.getElementById('ai-user-controls');
        if (panel) panel.classList.toggle('d-none', !active);
        if (kisiSummary) kisiSummary.classList.toggle('d-none', active);
        if (noKisi) noKisi.classList.toggle('d-none', active);
        if (controls) controls.classList.toggle('d-none', active);
        const modalTitle = document.getElementById('aiGenerateModalLabel');
        if (modalTitle) {
            modalTitle.innerHTML = active
                ? '<i class="bi bi-plus-square-dotted me-2"></i>Buat Soal Mirip (Setara Kompetensi)'
                : '<i class="bi bi-robot me-2"></i>Soal Generator by ikasmedia';
        }
        const btnGenerate = document.getElementById('ai-btn-generate');
        if (btnGenerate) {
            btnGenerate.innerHTML = active
                ? '<i class="bi bi-plus-square-dotted me-1"></i>Buat Soal Mirip'
                : '<i class="bi bi-magic me-1"></i>Generate Soal';
        }
    }

    function resetAIModal() {
        goToStep(1);
        aiGeneratedData = [];
        aiSelectedIndices = new Set();
        document.getElementById('ai-preview-list').innerHTML = '';
        setAIGenerateButtonLoading(false);

        // Reset user configurable fields to default empty state
        const manualInput = document.getElementById('ai-topic-manual');
        if (manualInput) manualInput.value = '';
        document.getElementById('ai-topic').value = '';
        
        const topikInput = document.getElementById('ai-topik');
        if (topikInput) topikInput.value = '';
        const indikatorInput = document.getElementById('ai-indikator');
        if (indikatorInput) indikatorInput.value = '';

        const stimSelect = document.getElementById('ai-stimulus-type');
        if (stimSelect) {
            stimSelect.value = 'kasus_kontekstual';
            const descEl = document.getElementById('stimulus-desc');
            if(descEl) descEl.textContent = aiStimulusDescs['kasus_kontekstual'];
        }

        // Bank sub-tes → set fokus kompetensi default sesuai sub-tes & tampilkan badge
        const subtesBadge = document.getElementById('ai-subtes-badge');
        if (subtesBadge) {
            const code = String(BANK_SUBTES_CODE || 'GENERAL').toUpperCase();
            if (code && code !== 'GENERAL') {
                subtesBadge.classList.remove('d-none');
                subtesBadge.textContent = '';
                const ic = document.createElement('i');
                ic.className = 'bi bi-diagram-2 me-1';
                subtesBadge.appendChild(ic);
                subtesBadge.appendChild(document.createTextNode(BANK_SUBTES_NAMA || code));
            } else {
                subtesBadge.classList.add('d-none');
            }
        }
        const fokusSelect = document.getElementById('ai-fokus-kompetensi');
        if (fokusSelect) {
            const code = String(BANK_SUBTES_CODE || 'GENERAL').toUpperCase();
            const fokusDefault = { 'MTK':'numerasi', 'BIN':'literasi_membaca', 'BING':'literasi_membaca', 'FIS':'literasi_sains', 'KIM':'literasi_sains', 'BIO':'literasi_sains', 'INF':'literasi_sains' }[code] || 'literasi_membaca';
            fokusSelect.value = fokusDefault;
        }

        prefillAIGenerateFormFromCurrentSoal();
        syncAiJenisSoalWithActiveTab();

        // Reset similar mode state
        const simJmlSelect = document.getElementById('ai-similar-jumlah-select');
        if (simJmlSelect) { simJmlSelect.value = '1'; document.getElementById('ai-similar-jumlah').value = '1'; }
        const simPreview = document.getElementById('ai-similar-source-preview');
        if (simPreview) simPreview.textContent = '—';

        // Reset analytics & timer
        stopElapsedTimer();
        const analyticsBar = document.getElementById('ai-analytics');
        if (analyticsBar) analyticsBar.classList.add('d-none');
        const progressBar = document.getElementById('ai-progress-bar');
        if (progressBar) progressBar.style.width = '0%';
        const timerEl = document.getElementById('ai-elapsed-timer');
        if (timerEl) timerEl.textContent = '0 detik';
    }


    function goToStep(step) {
        document.querySelectorAll('#ai-step-1,#ai-step-2,#ai-step-3').forEach(el => el.classList.add('d-none'));
        const stepEl = document.getElementById('ai-step-'+step);
        if (stepEl) stepEl.classList.remove('d-none');

        document.querySelectorAll('.step-item').forEach(el => {
            const s = parseInt(el.dataset.step);
            el.classList.remove('active','done');
            if(s < step) el.classList.add('done');
            if(s === step) el.classList.add('active');
        });

        // Button visibility (null-safe)
        const btnGen = document.getElementById('ai-btn-generate');
        const btnSave = document.getElementById('ai-btn-save');
        const btnBack = document.getElementById('ai-btn-back');
        const btnReload = document.getElementById('ai-btn-reload');
        const btnClose = document.getElementById('ai-btn-close');

        if (btnGen) btnGen.classList.toggle('d-none', step !== 1);
        if (btnBack) btnBack.classList.toggle('d-none', step !== 2);
        if (btnSave) btnSave.classList.toggle('d-none', step !== 2);
        if (btnReload) btnReload.classList.toggle('d-none', step !== 3);
        if (btnClose) btnClose.classList.toggle('d-none', step === 3);
    }

        function escapeAttr(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function syncTopicValue() {
        const linkedKisiId = String($('#kartu_item_id').val() || '').trim();
        if (!linkedKisiId) {
            const manualInput = document.getElementById('ai-topic-manual');
            const topicHidden = document.getElementById('ai-topic');
            if (manualInput && topicHidden) {
                topicHidden.value = manualInput.value.trim();
            }
        }
    }

    function syncAiJenisSoalWithActiveTab() {
        const activeJenis = String($('#jenis-id').val() || jenis || '1');
        const tipeSelect = document.getElementById('ai-tipe');
        const tipeHidden = document.getElementById('ai-tipe-hidden');
        if (tipeSelect) tipeSelect.value = activeJenis;
        if (tipeHidden) tipeHidden.value = activeJenis;
        const pg2ModeCol = document.getElementById('ai-pg2-mode-col');
        if (pg2ModeCol) {
            pg2ModeCol.classList.toggle('d-none', activeJenis !== '2');
        }
    }

    function submitAIGenerate() {
        if (!window.CAN_USE_AI) {
            Swal.fire('Nonaktif', 'Fitur AI dinonaktifkan oleh administrator.', 'warning');
            return;
        }
        const similarMode = String(document.getElementById('ai-similar-mode').value || '0') === '1';
        syncTopicValue();
        syncAiJenisSoalWithActiveTab();
        const form = document.getElementById('aiGenerateForm');
        const topic = String(document.getElementById('ai-topic').value || '').trim();
        const kartuItemInput = document.getElementById('ai-kartu-item-id');
        const linkedKisiId = String((kartuItemInput ? kartuItemInput.value : '') || $('#kartu_item_id').val() || '').trim();
        const tipeSoal = String((document.getElementById('ai-tipe-hidden') || {}).value || (document.getElementById('ai-tipe') || {}).value || '').trim();
        const stimulusType = String(document.getElementById('ai-stimulus-type').value || '').trim();
        const fokusKompetensi = String(document.getElementById('ai-fokus-kompetensi').value || '').trim();

        // Similar mode bypass topic/stimulus validation
        if (!similarMode && !stimulusType) {
            Swal.fire('Peringatan', 'Jenis stimulus wajib dipilih.', 'warning');
            return;
        }

        if (kartuItemInput) kartuItemInput.value = linkedKisiId;

        // If no topic is provided from metadata or manual input
        if (!similarMode && !topic) {
            Swal.fire('Peringatan', 'Silakan pilih kisi-kisi terlebih dahulu, atau isi topik/capaian pembelajaran manual.', 'warning');
            return;
        }

        if (!tipeSoal) {
            Swal.fire('Peringatan', 'Jenis soal wajib dipilih.', 'warning');
            return;
        }

        setAIGenerateButtonLoading(true);
        goToStep(2);
        document.getElementById('ai-loading').classList.remove('d-none');
        document.getElementById('ai-preview').classList.add('d-none');
        document.getElementById('ai-error').classList.add('d-none');
        document.getElementById('ai-btn-save').classList.add('d-none');
        document.getElementById('ai-btn-back').classList.add('d-none');
        startElapsedTimer();

        const formData = new FormData(form);
        let ajaxUrl = base_url + 'cbtbanksoal/generate_ai';

        if (similarMode) {
            const idSoalAktif = parseInt(String(document.getElementById('ai-similar-source').value || '0'), 10) || 0;
            if (idSoalAktif <= 0) {
                document.getElementById('ai-loading').classList.add('d-none');
                setAIGenerateButtonLoading(false);
                stopElapsedTimer();
                Swal.fire('Peringatan', 'Simpan soal terlebih dahulu sebelum membuat soal mirip.', 'warning');
                goToStep(1);
                return;
            }
            ajaxUrl = base_url + 'cbtbanksoal/generate_similar_ai';
            const jumlah = String(document.getElementById('ai-similar-jumlah').value || '1');
            // Kirim data minimal — bank tetap yang aktif, soal mirip selalu disimpan di bank yg sama
            formData.delete('kartu_item_id');
            formData.delete('topic');
            formData.delete('level_akm');
            formData.delete('konteks_akm');
            formData.delete('jenis_stimulus');
            formData.delete('fokus_kompetensi');
            formData.delete('mode_soal');
            formData.set('id_soal', String(idSoalAktif));
            formData.set('jumlah', jumlah);
            formData.set('bank_id', String(document.querySelector('#aiGenerateForm [name="bank_id"]')?.value || (typeof id_bank !== 'undefined' ? id_bank : '0')));
            formData.set('ai_model', String(document.getElementById('ai-preferred-model')?.value || ''));
        }

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 180000,
            success: function(data) {
                const response = typeof data === 'string' ? JSON.parse(data) : data;
                document.getElementById('ai-loading').classList.add('d-none');
                setAIGenerateButtonLoading(false);
                stopElapsedTimer();

                if(response.success && response.preview) {
                    aiGeneratedData = response.preview;
                    const tipePreview = (response.tipe_soal || formData.get('tipe_soal') || '').toString();
                    const subtipePreview = (response.subtipe_pg2 || formData.get('subtipe_pg2') || 'pgk').toString();
                    // Sinkronkan tipe & subtipe hasil generate agar save_ai_generated memakai format yang benar
                    const tipeHidden = document.getElementById('ai-tipe-hidden');
                    if (tipeHidden && response.tipe_soal) {
                        tipeHidden.value = String(response.tipe_soal);
                    }
                    const pg2Sel = document.getElementById('ai-subtipe-pg2');
                    if (pg2Sel && String(tipePreview) === '2' && response.subtipe_pg2) {
                        pg2Sel.value = String(response.subtipe_pg2);
                    }
                    renderPreview(response.preview, tipePreview, subtipePreview);
                    document.getElementById('ai-preview').classList.remove('d-none');
                    document.getElementById('ai-btn-save').classList.remove('d-none');
                    document.getElementById('ai-btn-back').classList.remove('d-none');
                    // Show analytics
                    if (response.analytics) showAnalytics(response.analytics);
                } else {
                    document.getElementById('ai-error').classList.remove('d-none');
                    document.getElementById('ai-error-msg').textContent = response.message || 'Gagal generate soal. Silakan coba lagi.';
                    document.getElementById('ai-btn-back').classList.remove('d-none');
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('ai-loading').classList.add('d-none');
                setAIGenerateButtonLoading(false);
                stopElapsedTimer();
                document.getElementById('ai-error').classList.remove('d-none');
                let errMsg = 'Terjadi kesalahan jaringan.';
                if(xhr.status === 403) errMsg = 'Session kadaluarsa. Silakan reload halaman.';
                else if(status === 'timeout') errMsg = 'Timeout - AI memerlukan waktu terlalu lama. Coba kurangi jumlah soal.';
                else errMsg += ' (' + error + ')';
                document.getElementById('ai-error-msg').textContent = errMsg;
                document.getElementById('ai-btn-back').classList.remove('d-none');
            }
        });
    }

    function renderPreview(items, tipe, subtipePg2) {
        const container = document.getElementById('ai-preview-list');
        container.innerHTML = '';
        aiSelectedIndices = new Set();
        const tipeValue = String(tipe || '');
        const pg2Mode = String(subtipePg2 || 'pgk') === 'pgkk' ? 'pgkk' : 'pgk';
        const tipeLabels = {'1':'PG','2':'PG Kompleks','3':'Menjodohkan','4':'Isian Singkat','5':'Esai','6':'Urutan','7':'Isian Rumpang'};
        const stimulus = aiStimulusLabels[document.getElementById('ai-stimulus-type').value] || '-';
        const fokus = aiFokusLabels[document.getElementById('ai-fokus-kompetensi').value || ''] || '-';
        const isSimilarMode = String(document.getElementById('ai-similar-mode')?.value || '0') === '1';

        let currentNo = typeof nomor_soal !== 'undefined' ? nomor_soal : 0;
        if (!currentNo) {
            const txt = $('#editor-nomor').text().replace(/[^\d]/g, '').trim();
            if (txt) currentNo = parseInt(txt, 10);
        }
        if (!currentNo) currentNo = parseInt($('#ai-nomor-hidden').val() || '1', 10) || 1;

        if (isSimilarMode) {
            // Mode Mirip: pilih opsi pertama secara default
            aiSelectedIndices = new Set([0]);
            document.getElementById('ai-preview-info').innerHTML = '<span class="badge bg-success text-white me-2" style="font-size:11px;padding:5px 10px;"><i class="bi bi-shuffle me-1"></i>Mode Pembanding</span> Menampilkan <b>' + items.length + ' opsi alternatif</b>. Klik/pilih salah satu opsi terbaik untuk <b>menggantikan Soal No. ' + currentNo + '</b>.';
        } else {
            items.forEach((item, idx) => aiSelectedIndices.add(idx));
            document.getElementById('ai-preview-info').textContent =
                items.length + ' soal ' + (tipeLabels[tipeValue] || '') + ' berhasil di-generate | Stimulus: ' + stimulus + ' | Fokus: ' + fokus + ' | AKM: ' + (document.getElementById('ai-level-akm')?.selectedOptions[0]?.textContent || 'Otomatis') + ' | Konteks: ' + (document.getElementById('ai-konteks-akm')?.selectedOptions[0]?.textContent || 'Otomatis');
        }

        items.forEach(function(item, idx) {
            let opsiHtml = '';

            if (tipeValue === '1') {
                const jawaban = String(item.jawaban || 'a').toLowerCase();
                ['a','b','c','d','e'].forEach(function(k){
                    if (item['opsi_' + k] != null && String(item['opsi_' + k]).trim() !== '') {
                        opsiHtml += '<div class="opsi-item '+(k===jawaban?'correct':'')+'">'
                            + '<strong>'+k.toUpperCase()+'.</strong> '
                            + '<div class="ai-editable-inline" contenteditable="true" data-field="opsi_'+k+'" data-idx="'+idx+'">'+(item['opsi_'+k] || '')+'</div>'
                            + '</div>';
                    }
                });
                opsiHtml += '<div class="mt-2"><label class="form-label small mb-1">Kunci Jawaban</label>'
                    + '<select class="form-select form-select-sm" data-field="jawaban" data-idx="'+idx+'">'
                    + '<option value="a" '+(jawaban==='a'?'selected':'')+'>A</option>'
                    + '<option value="b" '+(jawaban==='b'?'selected':'')+'>B</option>'
                    + '<option value="c" '+(jawaban==='c'?'selected':'')+'>C</option>'
                    + '<option value="d" '+(jawaban==='d'?'selected':'')+'>D</option>'
                    + '<option value="e" '+(jawaban==='e'?'selected':'')+'>E</option>'
                    + '</select></div>';
            } else if (tipeValue === '2') {
                const opsi = item.opsi || {};
                const jawabanMap = (item.jawaban && typeof item.jawaban === 'object' && !Array.isArray(item.jawaban)) ? item.jawaban : {};
                Object.keys(opsi).forEach(function(k){
                    const key = String(k).toLowerCase();
                    let statusMark = '';
                    if (pg2Mode === 'pgkk') {
                        const bs = String(jawabanMap[key] || jawabanMap[k] || 'B').toUpperCase();
                        statusMark = ' [<span class="badge text-bg-' + (bs === 'S' ? 'danger' : 'success') + '">' + bs + '</span>]';
                    } else {
                        const jawabanArr = Array.isArray(item.jawaban) ? item.jawaban.map(j => String(j).toLowerCase()) : [];
                        if (jawabanArr.includes(key)) statusMark = ' [<span class="badge text-bg-success">BENAR</span>]';
                    }
                    opsiHtml += '<div class="opsi-item">'
                        + '<strong>'+String(k).toUpperCase()+'.</strong> '
                        + '<div class="ai-editable-inline" contenteditable="true" data-field="opsi" data-key="'+k+'" data-idx="'+idx+'">'+(opsi[k] || '')+'</div>'
                        + statusMark
                        + '</div>';
                });
                if (pg2Mode === 'pgkk') {
                    opsiHtml += '<div class="mt-2"><label class="form-label small mb-1">Status Benar/Salah per Pernyataan</label></div>';
                    Object.keys(opsi).forEach(function(k){
                        const key = String(k).toLowerCase();
                        const cur = String(jawabanMap[key] || jawabanMap[k] || 'B').toUpperCase() === 'S' ? 'S' : 'B';
                        opsiHtml += '<div class="d-flex align-items-center gap-2 mb-1 pb-1 border-bottom border-light">'
                            + '<strong style="width:1.5rem;">'+String(k).toUpperCase()+'.</strong> '
                            + '<select class="form-select form-select-sm" style="max-width:120px;" data-field="jawaban_bs" data-key="'+k+'" data-idx="'+idx+'">'
                            + '<option value="B" '+(cur==='B'?'selected':'')+'>Benar (B)</option>'
                            + '<option value="S" '+(cur==='S'?'selected':'')+'>Salah (S)</option>'
                            + '</select></div>';
                    });
                } else {
                    const jawabanArr = Array.isArray(item.jawaban) ? item.jawaban.map(j => String(j).toLowerCase()) : [];
                    opsiHtml += '<div class="mt-2"><label class="form-label small mb-1">Kunci Jawaban (pisahkan dengan koma, contoh: a,c,e)</label>'
                        + '<input type="text" class="form-control form-control-sm" data-field="jawaban_multi" data-idx="'+idx+'" value="'+escapeAttr(jawabanArr.join(','))+'"></div>';
                }
            } else if (tipeValue === '3') {
                const pairs = Array.isArray(item.pairs) ? item.pairs : [];
                opsiHtml += '<table class="table table-sm table-bordered mt-2"><thead><tr><th>Premis</th><th>Respons</th></tr></thead><tbody>';
                pairs.forEach(function(p, pIdx){
                    opsiHtml += '<tr data-pair-index="'+pIdx+'">'
                        + '<td><div class="ai-editable" contenteditable="true" data-field="pair_left" data-pair-index="'+pIdx+'" data-idx="'+idx+'">'+(p.kiri || '')+'</div></td>'
                        + '<td><div class="ai-editable" contenteditable="true" data-field="pair_right" data-pair-index="'+pIdx+'" data-idx="'+idx+'">'+(p.kanan || '')+'</div></td>'
                        + '</tr>';
                });
                opsiHtml += '</tbody></table>';
            } else if (tipeValue === '4') {
                opsiHtml = '<div class="mt-2"><label class="form-label small mb-1">Kunci Jawaban Isian</label>'
                    + '<div class="ai-editable" contenteditable="true" data-field="jawaban" data-idx="'+idx+'">'+(item.jawaban || '')+'</div></div>';
            } else if (tipeValue === '5') {
                opsiHtml = '<div class="mt-2"><label class="form-label small mb-1">Rubrik/Kunci Jawaban Esai</label>'
                    + '<div class="ai-editable" contenteditable="true" data-field="jawaban" data-idx="'+idx+'">'+(item.jawaban || '')+'</div></div>';
            } else if (tipeValue === '6') {
                const uItems = Array.isArray(item.items) ? item.items : [];
                opsiHtml += '<ol class="mt-2">';
                uItems.forEach(function(u, uIdx){
                    opsiHtml += '<li><div class="ai-editable" contenteditable="true" data-field="urutan_item" data-item-index="'+uIdx+'" data-idx="'+idx+'">'+u+'</div></li>';
                });
                opsiHtml += '</ol>';
            } else if (tipeValue === '7') {
                opsiHtml = '<div class="alert alert-light border small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Edit teks soal, lalu gunakan format [[jawaban]] untuk isian rumpang.</div>';
            }

            const quality = getQualityScore(item, tipeValue);
            const qualityHtml = '<span class="ai-quality-badge ' + quality + '">' + getQualityLabel(quality) + '</span>';

            let headerSelectorHtml = '';
            let cardClasses = 'ai-soal-card';
            let cardStyle = '';

            if (isSimilarMode) {
                const isSelected = (idx === 0);
                if (!isSelected) cardClasses += ' deselected';
                cardStyle = isSelected 
                    ? 'border: 2px solid #10b981; box-shadow: 0 4px 14px rgba(16,185,129,0.18); transition: all .3s ease;' 
                    : 'border: 1px solid #e2e8f0; transition: all .3s ease;';
                
                headerSelectorHtml = '<div class="form-check d-flex align-items-center gap-2 m-0">'
                    + '<input type="radio" name="ai_similar_pick" class="form-check-input ai-check-radio" id="ai-pick-' + idx + '" ' + (isSelected ? 'checked' : '') + ' onchange="selectSingleSimilarOption(' + idx + ')" style="width:20px;height:20px;cursor:pointer;">'
                    + '<label class="form-check-label fw-bold small text-success cursor-pointer" for="ai-pick-' + idx + '" style="cursor:pointer;">Pilih Opsi #' + (idx + 1) + '</label>'
                    + '</div>'
                    + '<span class="badge" style="background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:12px;padding:5px 12px;">Alternatif #' + (idx + 1) + '</span>';
            } else {
                headerSelectorHtml = '<input type="checkbox" class="form-check-input ai-check" data-idx="' + idx + '" checked onchange="toggleSelect(' + idx + ')" style="width:20px;height:20px;cursor:pointer;">'
                    + '<span class="badge" style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:12px;padding:5px 12px;">Soal ' + (idx + 1) + '</span>';
            }

            const card = '<div class="' + cardClasses + '" id="ai-card-' + idx + '" style="' + cardStyle + '" onclick="' + (isSimilarMode ? 'if(!event.target.closest(\'.ai-editable, .ai-editable-inline, select, input, button\')) selectSingleSimilarOption(' + idx + ');' : '') + '">'
                + '<div class="card-header-custom">'
                + headerSelectorHtml
                + '<span class="badge bg-light text-dark border" style="font-size:11px;">' + (tipeLabels[tipeValue] || '') + '</span>'
                + qualityHtml
                + '<div class="ai-card-actions">'
                + '<button type="button" class="ai-card-action-btn" onclick="copyAiSoalText(' + idx + ')" title="Salin teks soal"><i class="bi bi-clipboard"></i></button>'
                + '</div>'
                + '</div>'
                + '<div class="card-body-custom">'
                + '<label class="form-label small mb-1 text-muted"><i class="bi bi-pencil me-1"></i>Teks Soal <span class="text-primary">(klik untuk edit)</span></label>'
                + '<div class="soal-content ai-editable" contenteditable="true" data-field="soal" data-idx="' + idx + '">' + (item.soal || '') + '</div>'
                + opsiHtml
                + '</div></div>';
            container.innerHTML += card;
        });

        updateSaveButton();
    }

    function selectSingleSimilarOption(idx) {
        aiSelectedIndices = new Set([idx]);
        document.querySelectorAll('.ai-soal-card').forEach(function(c, cIdx) {
            if (cIdx === idx) {
                c.classList.remove('deselected');
                c.style.border = '2px solid #10b981';
                c.style.boxShadow = '0 4px 14px rgba(16,185,129,0.18)';
            } else {
                c.classList.add('deselected');
                c.style.border = '1px solid #e2e8f0';
                c.style.boxShadow = 'none';
            }
        });
        document.querySelectorAll('.ai-check-radio').forEach(function(r, rIdx) {
            r.checked = (rIdx === idx);
        });
        updateSaveButton();
    }

    function toggleSelect(idx) {
        const card = document.getElementById('ai-card-'+idx);
        if(aiSelectedIndices.has(idx)) {
            aiSelectedIndices.delete(idx);
            if (card) card.classList.add('deselected');
        } else {
            aiSelectedIndices.add(idx);
            if (card) card.classList.remove('deselected');
        }
        updateSaveButton();
    }

    function toggleSelectAll() {
        const allChecked = aiSelectedIndices.size === aiGeneratedData.length;
        document.querySelectorAll('.ai-check').forEach(function(cb, idx){
            cb.checked = !allChecked;
            const card = document.getElementById('ai-card-'+idx);
            if(!allChecked) {
                aiSelectedIndices.add(idx);
                if (card) card.classList.remove('deselected');
            } else {
                aiSelectedIndices.delete(idx);
                if (card) card.classList.add('deselected');
            }
        });
        updateSaveButton();
    }

    function updateSaveButton() {
        const btn = document.getElementById('ai-btn-save');
        if (!btn) return;
        const isSimilarMode = String(document.getElementById('ai-similar-mode')?.value || '0') === '1';

        if (isSimilarMode) {
            let currentNo = typeof nomor_soal !== 'undefined' ? nomor_soal : 0;
            if (!currentNo) {
                const txt = $('#editor-nomor').text().replace(/[^\d]/g, '').trim();
                if (txt) currentNo = parseInt(txt, 10);
            }
            if (!currentNo) currentNo = parseInt($('#ai-nomor-hidden').val() || '1', 10) || 1;

            const selIdx = aiSelectedIndices.size > 0 ? (Array.from(aiSelectedIndices)[0] + 1) : null;
            if (selIdx !== null) {
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Gantikan Soal No. ' + currentNo + ' dengan Opsi #' + selIdx;
                btn.disabled = false;
            } else {
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Pilih 1 Opsi untuk Menggantikan';
                btn.disabled = true;
            }
        } else {
            btn.textContent = '';
            btn.innerHTML = '<i class="bi bi-floppy me-1"></i>Simpan ' + aiSelectedIndices.size + ' Soal';
            btn.disabled = aiSelectedIndices.size === 0;
        }
    }

    function collectEditedAiData(tipe) {
        const tipeValue = String(tipe || '');
        const pg2ModeEl = document.getElementById('ai-subtipe-pg2');
        const pg2Mode = pg2ModeEl ? String(pg2ModeEl.value || 'pgk') : 'pgk';
        aiGeneratedData = aiGeneratedData.map(function(item, idx) {
            const card = document.getElementById('ai-card-' + idx);
            if (!card) return item;

            const updated = Object.assign({}, item || {});
            const soalEl = card.querySelector('[data-field="soal"]');
            if (soalEl) {
                updated.soal = soalEl.innerHTML.trim();
            }

            if (tipeValue === '1') {
                ['a', 'b', 'c', 'd', 'e'].forEach(function(k) {
                    const opsiEl = card.querySelector('[data-field="opsi_' + k + '"]');
                    if (opsiEl) {
                        updated['opsi_' + k] = opsiEl.innerHTML.trim();
                    }
                });
                const jawabanEl = card.querySelector('[data-field="jawaban"]');
                if (jawabanEl) {
                    updated.jawaban = String(jawabanEl.value || 'a').toLowerCase();
                }
            } else if (tipeValue === '2') {
                const opsi = {};
                card.querySelectorAll('[data-field="opsi"]').forEach(function(el) {
                    const key = String(el.getAttribute('data-key') || '').toLowerCase();
                    if (key) opsi[key] = el.innerHTML.trim();
                });
                updated.opsi = opsi;
                if (pg2Mode === 'pgkk') {
                    const bsMap = {};
                    Object.keys(opsi).forEach(function(k) {
                        const selEl = card.querySelector('[data-field="jawaban_bs"][data-key="' + k + '"]');
                        bsMap[k] = selEl ? String(selEl.value || 'B').toUpperCase() : 'B';
                    });
                    updated.jawaban = bsMap;
                } else {
                    const jawabanMulti = card.querySelector('[data-field="jawaban_multi"]');
                    if (jawabanMulti) {
                        updated.jawaban = String(jawabanMulti.value || '')
                            .split(',')
                            .map(function(v) { return String(v || '').trim().toLowerCase(); })
                            .filter(function(v) { return !!v; });
                    }
                }
            } else if (tipeValue === '3') {
                const pairs = [];
                card.querySelectorAll('tr[data-pair-index]').forEach(function(row) {
                    const kiriEl = row.querySelector('[data-field="pair_left"]');
                    const kananEl = row.querySelector('[data-field="pair_right"]');
                    pairs.push({
                        kiri: kiriEl ? kiriEl.innerHTML.trim() : '',
                        kanan: kananEl ? kananEl.innerHTML.trim() : ''
                    });
                });
                updated.pairs = pairs;
            } else if (tipeValue === '4') {
                const jawabanEl = card.querySelector('[data-field="jawaban"]');
                if (jawabanEl) updated.jawaban = jawabanEl.textContent.trim();
            } else if (tipeValue === '5') {
                const jawabanEl = card.querySelector('[data-field="jawaban"]');
                if (jawabanEl) updated.jawaban = jawabanEl.innerHTML.trim();
            } else if (tipeValue === '6') {
                const items = [];
                card.querySelectorAll('[data-field="urutan_item"]').forEach(function(el) {
                    items.push(el.innerHTML.trim());
                });
                updated.items = items;
            }

            return updated;
        });
    }

    function saveAIGenerated() {
        if(aiSelectedIndices.size === 0) {
            Swal.fire('Peringatan','Pilih minimal 1 soal untuk disimpan!','warning');
            return;
        }

        syncAiJenisSoalWithActiveTab();
        const tipeSoal = String((document.getElementById('ai-tipe-hidden') || {}).value || (document.getElementById('ai-tipe') || {}).value || '').trim();
        collectEditedAiData(tipeSoal);

        const selected = [];
        aiSelectedIndices.forEach(function(idx){ selected.push(idx); });

        // Check replace active option
        const replaceActiveEl = document.getElementById('ai-replace-active');
        const replaceActive = replaceActiveEl && replaceActiveEl.checked ? 1 : 0;

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Menyimpan ' + selected.length + ' soal ke bank soal...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Ambil data soal yang terpilih saja
        const selectedData = [];
        aiSelectedIndices.forEach(function(idx) {
            if (aiGeneratedData[idx]) {
                selectedData.push(aiGeneratedData[idx]);
            }
        });

        const similarMode = String(document.getElementById('ai-similar-mode').value || '0') === '1';
        const bankIdSimpan = document.querySelector('#aiGenerateForm [name="bank_id"]')?.value || (typeof id_bank !== 'undefined' ? id_bank : '0');
        // Mode mirip: menggantikan nomor soal aktif yang sedang diedit
        let currentNoSimpan = typeof nomor_soal !== 'undefined' ? nomor_soal : 0;
        if (!currentNoSimpan) {
            const txt = $('#editor-nomor').text().replace(/[^\d]/g, '').trim();
            if (txt) currentNoSimpan = parseInt(txt, 10);
        }
        if (!currentNoSimpan) {
            currentNoSimpan = parseInt($('#ai-nomor-hidden').val() || '1', 10) || 1;
        }
        const nomorSimpan = currentNoSimpan;
        // Mode mirip: replace_active=1 agar menggantikan butir soal aktif
        const effectiveReplaceActive = similarMode ? 1 : replaceActive;

        $.ajax({
            url: base_url + 'cbtbanksoal/save_ai_generated',
            method: 'POST',
            data: {
                bank_id: bankIdSimpan,
                tipe_soal: tipeSoal,
                subtipe_pg2: document.getElementById('ai-subtipe-pg2') ? document.getElementById('ai-subtipe-pg2').value : (String(tipeSoal) === '2' ? 'pgk' : 'pg'),
                nomor_soal: nomorSimpan, // Disesuaikan dengan controller
                topik: document.getElementById('ai-topik') ? document.getElementById('ai-topik').value : '',
                indikator: document.getElementById('ai-indikator') ? document.getElementById('ai-indikator').value : '',
                kartu_item_id: document.getElementById('ai-kartu-item-id') ? document.getElementById('ai-kartu-item-id').value : '',
                jenis_stimulus: document.getElementById('ai-stimulus-type').value,
                fokus_kompetensi: document.getElementById('ai-fokus-kompetensi') ? document.getElementById('ai-fokus-kompetensi').value : '',
                generated: JSON.stringify(selectedData), // Disesuaikan dengan controller (generated)
                replace_active: effectiveReplaceActive
            },
            success: function(data) {
                Swal.close();
                const response = typeof data === 'string' ? JSON.parse(data) : data;
                if(response.success) {
                    goToStep(3);
                    document.getElementById('ai-success-msg').textContent = response.message;
                    // Populate success stats
                    const statsEl = document.getElementById('ai-success-stats');
                    if (statsEl && aiLastAnalytics) {
                        const mn = { 'gemini-3.5-flash-lite':'Flash 3.5 Lite','gemini-3.6-flash':'Flash 3.6' };
                        statsEl.innerHTML = '<div class="ai-analytics-item"><i class="bi bi-cpu"></i> ' + (mn[aiLastAnalytics.model] || 'Auto') + '</div>'
                            + '<div class="ai-analytics-item"><i class="bi bi-clock-history"></i> ' + (aiLastAnalytics.elapsed || '-') + ' detik</div>'
                            + '<div class="ai-analytics-item"><i class="bi bi-check2-square"></i> ' + aiSelectedIndices.size + ' soal disimpan</div>';
                    }
                } else {
                    Swal.fire('Error', response.message || 'Gagal menyimpan soal', 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                Swal.fire('Error', 'Gagal menyimpan: ' + error, 'error');
            }
        });
    }

    // Init kelas options on load + topic manual sync
    // Stub untuk mencegah ReferenceError jika fungsi terhapus atau tidak dimuat
    window.filterAiTpOptionsByJenis = window.filterAiTpOptionsByJenis || function() {};

    document.addEventListener('DOMContentLoaded', function(){
        syncAiJenisSoalWithActiveTab();
        // Sync manual textarea on input
        const manualInput = document.getElementById('ai-topic-manual');
        if (manualInput) {
            manualInput.addEventListener('input', function() { syncTopicValue(); });
        }
        const tipeSelect = document.getElementById('ai-tipe');
        if (tipeSelect) {
            tipeSelect.addEventListener('change', function () {
                const tipeHidden = document.getElementById('ai-tipe-hidden');
                if (tipeHidden) {
                    tipeHidden.value = String(tipeSelect.value || '').trim();
                }
                filterAiTpOptionsByJenis();
                onTPSelected();
            });
        }

        const jenisInput = document.getElementById('jenis-id');
        if (jenisInput) {
            jenisInput.addEventListener('change', function() {
                syncAiJenisSoalWithActiveTab();
                filterAiTpOptionsByJenis();
            });
        }

        $(document).on('click', '.qs-nav-btn, .qs-tab', function() {
            setTimeout(function() {
                syncAiJenisSoalWithActiveTab();
                filterAiTpOptionsByJenis();
            }, 700);
        });
    });

    const kisiItemCache = {};

    function mapKisiKesulitan(levelKognitif) {
        const raw = String(levelKognitif || '').toUpperCase();
        if (raw.indexOf('C1') !== -1) return 'C1';
        if (raw.indexOf('C2') !== -1) return 'C2';
        if (raw.indexOf('C3') !== -1) return 'C3';
        if (raw.indexOf('C4') !== -1) return 'C4';
        if (raw.indexOf('C5') !== -1) return 'C5';
        if (raw.indexOf('C6') !== -1) return 'C6';
        return '';
    }

    function applyKisiMetadata(item, options) {
        const opt = options || {};
        const force = opt.force === true;

        if (!item || typeof item !== 'object') return;

        const tujuanVal = $('#tujuan_id').val();
        const topikVal = $('#topik').val();
        const indikatorVal = $('#indikator').val();
        const kesulitanVal = $('#kesulitan').val();

        let targetTujuanId = tujuanVal;
        let targetTopik = topikVal;
        let targetIndikator = indikatorVal;
        let targetKesulitan = kesulitanVal;
        let targetTujuanText = $('#meta-tujuan-text').text();

        if (item.id_tujuan && (force || !tujuanVal)) {
            targetTujuanId = item.id_tujuan;
            targetTujuanText = (item.tujuan_kode ? (item.tujuan_kode + ' - ') : '') + (item.tujuan || '');
        }
        if (item.materi && (force || !String(topikVal || '').trim())) {
            targetTopik = item.materi;
        }
        if (item.indikator_soal && (force || !String(indikatorVal || '').trim())) {
            targetIndikator = item.indikator_soal;
        }

        const mappedKesulitan = mapKisiKesulitan(item.level_kognitif || item.tingkat_kesulitan || '');
        if (mappedKesulitan && (force || !String(kesulitanVal || '').trim())) {
            targetKesulitan = mappedKesulitan;
        }

        // Use the centralized update function to refresh both hidden inputs and text spans
        if (typeof updateMetadataTextDisplay === 'function') {
            updateMetadataTextDisplay(
                targetTujuanId,
                targetTopik,
                targetIndikator,
                targetKesulitan,
                targetTujuanText
            );
        } else {
            // Fallback if updateMetadataTextDisplay is not available
            $('#tujuan_id').val(targetTujuanId).trigger('change');
            $('#topik').val(targetTopik);
            $('#indikator').val(targetIndikator);
            $('#kesulitan').val(targetKesulitan).trigger('change');
            
            $('#meta-tujuan-text').text(targetTujuanText || '-');
            $('#meta-topik-text').text(targetTopik || '-');
            $('#meta-indikator-text').text(targetIndikator || '-');
            $('#meta-kesulitan-text').text(targetKesulitan || 'C4');
        }
    }

    function syncMetadataFromLinkedKisi(kartuItemId, jenisSoal, options) {
        const opt = options || {};
        const idItem = parseInt(kartuItemId || 0, 10);
        const jenisAktif = parseInt(jenisSoal || $('#jenis-id').val() || jenis || 0, 10);
        if (!idItem || !jenisAktif) return;

        const cacheKey = jenisAktif + ':' + idItem;
        if (kisiItemCache[cacheKey]) {
            applyKisiMetadata(kisiItemCache[cacheKey], opt);
            return;
        }

        $.ajax({
            url: base_url + 'cbtbanksoal/api-get-kartu-items',
            type: 'GET',
            data: {
                id_bank: idBank,
                jenis: jenisAktif
            },
            success: function(res) {
                if (!res || !res.status || !Array.isArray(res.data)) return;

                let selectedItem = null;
                res.data.forEach(function(it) {
                    const key = jenisAktif + ':' + String(it.id_item);
                    kisiItemCache[key] = it;
                    if (String(it.id_item) === String(idItem)) {
                        selectedItem = it;
                    }
                });

                if (selectedItem) {
                    applyKisiMetadata(selectedItem, opt);
                    if (!opt.silent) {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false, timer: 2500,
                            icon: 'success', title: 'Metadata diisi otomatis dari Kisi-kisi.'
                        });
                    }
                }
            }
        });
    }

    function bukaModalKisiKisi() {
        $('#kisiListBody').html('<tr><td colspan="5" class="text-center">Sedang memuat data...</td></tr>');
        var kisiModal = new bootstrap.Modal(document.getElementById('modalPilihKisi'));
        kisiModal.show();
        
        $.ajax({
            url: base_url + 'cbtbanksoal/api-get-kartu-items',
            type: 'GET',
            data: {
                id_bank: idBank,
                jenis: jenis
            },
            success: function(res) {
                if (res.status) {
                    let html = '';
                    if (res.data.length === 0) {
                        html = '<tr><td colspan="6" class="text-center">Belum ada kisi-kisi atau tidak ada yang cocok.</td></tr>';
                    } else {
                        res.data.forEach(function(item, index) {
                            let badgeTaut = item.soal_id ? '<span class="badge bg-success">Digunakan</span>' : '<span class="badge bg-secondary">Bebas</span>';
                            let tujuanText = item.tujuan ? (item.tujuan_kode + ' - ' + item.tujuan.substring(0, 30) + '...') : '-';
                            let buatButirBtn = item.soal_id ? '' : `<button class="btn btn-sm btn-outline-success ms-1" onclick="buatButirDariKartu(${item.id_item}, ${item.jenis_soal})" title="Buat butir soal baru dari kisi-kisi ini">
                                        <i class="bi bi-plus-circle"></i> Buat Butir
                                    </button>`;
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td><small class="fw-bold">${item.judul}</small><br><small class="text-muted">C${item.level_kognitif.replace('C','')}</small></td>
                                <td><small>${tujuanText}</small></td>
                                <td>${badgeTaut}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='pilihKisiIni(${JSON.stringify(item).replace(/'/g, "&apos;")})'>
                                        <i class="bi bi-check-circle"></i> Pilih
                                    </button>
                                    ${buatButirBtn}
                                </td>
                            </tr>`;
                        });
                    }
                    $('#kisiListBody').html(html);
                } else {
                    $('#kisiListBody').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat: ' + res.message + '</td></tr>');
                }
            },
            error: function() {
                $('#kisiListBody').html('<tr><td colspan="5" class="text-center text-danger">Kesalahan jaringan.</td></tr>');
            }
        });
    }

    function buatButirDariKartu(itemId, jenisSoal) {
        Swal.fire({
            title: 'Buat Butir Soal',
            text: 'Buat butir soal baru dari kisi-kisi ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat',
            cancelButtonText: 'Batal',
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: base_url + 'cbtbanksoal/generate-soal-from-kartu',
                type: 'POST',
                data: {
                    bank_id: idBank,
                    item_id: itemId,
                    jenis: jenisSoal,
                },
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });
                },
                success: function(res) {
                    if (res.status && res.data) {
                        Swal.fire({
                            icon: 'success', title: 'Berhasil',
                            text: 'Butir soal ' + jenisSoal + ' nomor ' + res.data.nomor_soal + ' berhasil dibuat.',
                            confirmButtonText: 'Edit Sekarang',
                            showCancelButton: true,
                            cancelButtonText: 'Tutup',
                        }).then(function(act) {
                            if (act.isConfirmed) {
                                window.location.href = base_url + 'cbtbanksoal/buatsoal/' + idBank + '?no=' + res.data.nomor_soal + '&jns=' + jenisSoal;
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Kesalahan jaringan.' });
                },
            });
        });
    }

    function pilihKisiIni(item) {
        // Set metadata form from selected kisi-kisi (force sync).
        applyKisiMetadata(item, { force: true });
        $('#kartu_item_id').val(item.id_item);
        $('#unlink_kisi').val('0');

        $('#alert-kisi-tautan').removeClass('d-none');
        $('#text-kisi-tautan').text('Soal ini akan ditautkan ke Kisi-kisi: ' + item.judul);

        const modalEl = document.getElementById('modalPilihKisi');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            document.activeElement?.blur();
            modal.hide();
        }
        Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            icon: 'success', title: 'Metadata Kisi-kisi diisi. Jangan lupa Simpan.'
        });
    }

    function unlinkKisiKisi() {
        $('#kartu_item_id').val('');
        $('#unlink_kisi').val('1');
        $('#alert-kisi-tautan').addClass('d-none');
        $('#tp-select-wrapper').removeClass('d-none');
        $('#tp-display-wrapper').addClass('d-none');
        Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
            icon: 'info', title: 'Tautan dilepas. Tekan Simpan untuk memperbarui.'
        });
    }
</script>

<!-- Modal Pilih Kisi-Kisi -->
<div class="modal fade" id="modalPilihKisi" tabindex="-1" aria-labelledby="modalPilihKisiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalPilihKisiLabel"><i class="bi bi-link-45deg me-2 text-primary"></i>Pilih Kisi-kisi dari Kartu Soal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Kartu Soal</th>
                                <th width="40%">Tujuan Pembelajaran</th>
                                <th width="10%">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kisiListBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><script src="<?= base_url() ?>/assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/mediastyler.js"></script>

<style>
    .swal2-container {
        z-index: 20000 !important;
    }
</style>

<script src="<?= base_url() ?>/assets/app/js/linker-list.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>
<script src="<?= base_url() ?>/assets/plugins/katex/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

<script type="text/javascript">
    var nomor_soal = '<?= $p_no ?>';
    let soal_ada = '<?= empty($soal) ? 'n' : 'y' ?>';
    let idBank = '<?= $bank->id_bank ?? 0 ?>';
    let idSoal = '<?= empty($soal) ? 0 : ($soal->id_soal ?? 0) ?>';
    let jenis = '<?= $tab_active ?>';
    const maxPgOptions = <?= (int) ($bank->opsi ?? 5) ?>;
    const QS_SIDE_PANEL_WIDTH = 'minmax(420px, 520px)';

    var adaPg = JSON.parse('<?= json_encode($soal_pg_ada) ?>');
    var adaPg2 = JSON.parse('<?= json_encode($soal_pg2_ada) ?>');
    var adaJodohkan = JSON.parse('<?= json_encode($soal_jodohkan_ada) ?>');
    var adaIsian = JSON.parse('<?= json_encode($soal_isian_ada) ?>');
    var adaEssai = JSON.parse('<?= json_encode($soal_esai_ada) ?>');
    var adaUrutan = JSON.parse('<?= json_encode($soal_urutan_ada) ?>');
    var adaCloze = JSON.parse('<?= json_encode($soal_cloze_ada) ?>');
    var allSoalsRaw = <?= json_encode($soals ?? []) ?>;
    
    window.BANK_WORKFLOW = {
        kartuPlanByJenis: <?= json_encode($kartu_plan_by_jenis ?? []) ?>,
        kartuQuotaByJenis: <?= json_encode($kartu_quota_by_jenis ?? []) ?>
    };

    $(function () {
        bsCustomFileInput.init();
        
        // Initialize new UX features
        initSaveFlow();
        initKeyboardShortcuts();
        initDragAndDrop();
        initRealTimePreview();
        initFormValidation();
        initShortcutsHint();
        
        allSoalsRaw.forEach(s => {
            if (s.kartu_item_id && s.kartu_item_id != '0' && s.kartu_item_id !== null) {
                let btnId = 'btn-' + s.jenis + s.nomor_soal;
                let btn = $('#' + btnId);
                if(btn.length) {
                    btn.css('position', 'relative');
                    btn.append('<i class="bi bi-link-45deg bg-success rounded-circle text-white shadow" style="position:absolute; top:-5px; right:-5px; font-size:11px; padding:1px 3px;" title="Ditautkan ke Kisi-kisi"></i>');
                }
            }
        });

        // Sync preview/validation state after legacy loader finishes initial rendering.
        setTimeout(function() {
            updatePreview();
            updateCharCount();
            isDirty = false;
            syncSplitLayout();
        }, 800);

        // Keep preview in sync when user navigates antar nomor soal.
        $(document).on('click', '.qs-nav-btn', function() {
            setTimeout(function() {
                updatePreview();
                updateCharCount();
                isDirty = false;
                syncSplitLayout();
            }, 650);
        });
    });

    // ============================================
    // SAVE FLOW (Manual + Autosave)
    // ============================================
    let isDirty = false;
    let isSavingRequest = false;
    let skipDirtyWarningOnce = false;
    let autoSaveTimer = null;
    const AUTOSAVE_INTERVAL = 20000;
    function markFormDirty() {
        isDirty = true;
    }

    function getEditorHtml(selector) {
        const $el = $(selector);
        if (!$el.length) return '';
        if ($el.next('.note-editor').length && typeof $el.summernote === 'function') {
            return $el.summernote('code') || '';
        }
        if (typeof $el.summernote === 'function') {
            try {
                const code = $el.summernote('code');
                if (code && code !== '<p><br></p>') return code;
            } catch(e) {}
        }
        return $el.val() || '';
    }

    function getPlainText(html) {
        return $('<div>').html(html || '').text().replace(/\u00a0/g, ' ').trim();
    }

    function optionHasContent(content) {
        if (!content) return false;
        const str = String(content).trim();
        if (!str || str === '<p><br></p>' || str === '<p></p>' || str === '<br>') return false;
        if (getPlainText(str) !== '') return true;
        if (/<(img|svg|iframe|video|audio|table|math|canvas|picture)\b/i.test(str)) return true;
        return false;
    }

    function isResponseSuccess(response) {
        return !!(
            response &&
            (response.success === true || response.status === true || response.status === 'success')
        );
    }

    function currentNomorSoal() {
        return String(nomor_soal || '').trim() || '1';
    }

    function currentJenisSoal() {
        return String($('#jenis-id').val() || jenis || window.jenisSoal || '1');
    }

    function buildSaveFormData(strictValidation) {
        const formEl = $('#create')[0];
        if (!formEl) {
            return { ok: false, message: 'Form soal tidak ditemukan' };
        }

        const formData = new FormData(formEl);
        const jenisAktif = currentJenisSoal();

        formData.set('jenis', jenisAktif);
        formData.set('bank_id', $('#bank-id').val() || String(idBank || 0));
        formData.set('soal_id', $('#soal-id').val() || String(idSoal || 0));
        formData.set('method', $('#method').val() || 'add');
        formData.set('nomor_soal', currentNomorSoal());
        formData.set('soal', getEditorHtml('#textsoal'));

        // Metadata Kisi-kisi Soal - ensure these are included
        const kartuItemId = $('#kartu_item_id').val() || '';
        const unlinkKisi = $('#unlink_kisi').val() || '0';
        formData.set('kartu_item_id', kartuItemId);
        formData.set('unlink_kisi', unlinkKisi);

        if (jenisAktif === '1') {
            $('.qs-option-card textarea.textjawaban').each(function() {
                const key = $(this).attr('name');
                if (key) formData.set(key, getEditorHtml('#' + this.id));
            });

            const selected = (($('.qs-radio-custom:checked').val() || $('#jawaban').val() || '') + '').toLowerCase();
            formData.set('jawaban_pg', selected);
            formData.set('jawaban', selected);
            formData.set('tipe_soal', 'pg');
        } else if (jenisAktif === '2') {
            $('#opsi-pg2 .textjawaban2').each(function() {
                const key = $(this).attr('name');
                if (key) formData.set(key, getEditorHtml('#' + this.id));
            });
            const pgkkMode = $('#root-opsi-pg2').hasClass('pgkk-mode');
            formData.set('tipe_soal', pgkkMode ? 'pgkk' : 'pgk');
            if (pgkkMode) {
                const bsMap = {};
                $('#opsi-pg2 .pg2-bs-select').each(function() {
                    const letter = $(this).data('letter');
                    const val = $(this).val();
                    if (letter && (val === 'B' || val === 'S')) bsMap[letter] = val;
                });
                formData.delete('jawaban_benar_pg2');
                formData.set('jawaban_pgkk', JSON.stringify(bsMap));
            } else {
                formData.delete('jawaban_pgkk');
            }
        } else if (jenisAktif === '3') {
            if (window.dataJodohkan && typeof window.dataJodohkan === 'object') {
                $.each(window.dataJodohkan, function(key, value) {
                    if (key === 'jawaban' && value && typeof value === 'object') {
                        $.each(value, function(ks, vs) {
                            $.each(vs || {}, function(k, v) {
                                formData.append('jawaban[' + ks + '][' + k + ']', v);
                            });
                        });
                    } else {
                        formData.set(key, value);
                    }
                });
            }
        } else if (jenisAktif === '6' && typeof window.getUrutanData === 'function') {
            const urutanData = window.getUrutanData();
            formData.set('soal_opsi', JSON.stringify(urutanData));
            formData.set('jawaban', JSON.stringify(urutanData.correct_order || []));
        } else if (jenisAktif === '7') {
            const clozeRaw = getEditorHtml('#textsoal');
            const $tmpContent = $('<div>').html(clozeRaw);
            $tmpContent.find('.cloze-tag').each(function() {
                $(this).replaceWith($(this).text());
            });
            const cleanSoal = $tmpContent.html() || '';
            formData.set('soal', cleanSoal);

            if (typeof window.getClozeData === 'function') {
                const clozeData = window.getClozeData();
                formData.set('soal_opsi', JSON.stringify(clozeData));
                const correctAnswers = {};
                Object.keys(clozeData.blanks || {}).forEach(function(k) {
                    correctAnswers[k] = clozeData.blanks[k].answer;
                });
                formData.set('jawaban', JSON.stringify(correctAnswers));
            }
        }

        if (!strictValidation) {
            return { ok: true, formData: formData };
        }

        let isValid = true;
        
        // Validate soal text
        if (!validateSoalField()) {
            isValid = false;
        }
        
        if (jenisAktif === '1' && $('#pg-options-section').is(':visible')) {
            if (!validateOptionFields(true)) {
                isValid = false;
            }
            if (!$('.qs-radio-custom:checked').length) {
                qsShowToast('Pilih jawaban benar untuk soal PG', 'warning');
                isValid = false;
            }
        }

        if (jenisAktif === '2') {
            const pg2OptionCount = $('#opsi-pg2 .textjawaban2').length;
            const pgkkMode = $('#root-opsi-pg2').hasClass('pgkk-mode');
            if (pg2OptionCount < 2) {
                qsShowToast('Soal PG Kompleks minimal memiliki 2 opsi', 'warning');
                isValid = false;
            }
            if (pgkkMode) {
                const bsFilled = $('#opsi-pg2 .pg2-bs-select').filter(function() { return !!$(this).val(); }).length;
                if (bsFilled < pg2OptionCount) {
                    qsShowToast('Semua pernyataan PGKK harus diberi status Benar/Salah', 'warning');
                    isValid = false;
                }
            } else if ($('#opsi-pg2 .check-pg2:checked').length < 1) {
                qsShowToast('Pilih minimal 1 jawaban benar untuk PG Kompleks', 'warning');
                isValid = false;
            }
        }

        if (jenisAktif === '3' && !window.adaJawaban) {
            qsShowToast('Soal menjodohkan harus memiliki pasangan jawaban', 'warning');
            isValid = false;
        }

        if (jenisAktif === '4' && !$('#jawaban-isian').val().trim()) {
            qsShowToast('Jawaban isian tidak boleh kosong', 'warning');
            isValid = false;
        }

        if (jenisAktif === '5' && !optionHasContent(getEditorHtml('#jawaban-essai'))) {
            qsShowToast('Rubrik/kunci jawaban essai tidak boleh kosong', 'warning');
            isValid = false;
        }

        if (jenisAktif === '7') {
            if (typeof window.getClozeData === 'function') {
                const clozeData = window.getClozeData();
                if (!clozeData.blanks || !Object.keys(clozeData.blanks).length) {
                    qsShowToast('Tambahkan minimal 1 jawaban untuk isian rumpang', 'warning');
                    isValid = false;
                }
            }
        }
        
        return { ok: isValid, formData: formData };
    }

    function validateSoalField() {
        const $field = $('#textsoal');
        const $error = $('#soal-error');
        const value = getEditorHtml('#textsoal');
        const $editor = $field.next('.note-editor');
        
        if (!optionHasContent(value)) {
            $field.addClass('is-invalid');
            $editor.addClass('is-invalid');
            $error.removeClass('d-none');
            return false;
        } else {
            $field.removeClass('is-invalid').addClass('is-valid');
            $editor.removeClass('is-invalid');
            $error.addClass('d-none');
            return true;
        }
    }

    function validateOptionFields(showMessage) {
        let hasContent = 0;
        const $cards = $('.qs-option-card:visible textarea.textjawaban').length ? $('.qs-option-card:visible textarea.textjawaban') : $('.textjawaban');
        $cards.each(function() {
            const content = getEditorHtml('#' + this.id);
            if (optionHasContent(content)) {
                hasContent++;
            }
        });
        if (showMessage && hasContent < 2) {
            qsShowToast('Soal PG minimal memiliki 2 opsi jawaban terisi', 'warning');
        }
        return hasContent >= 2;
    }

    function initSaveFlow() {
        $('#create').on('input change', 'input, textarea, select', markFormDirty);
        $(document).on('summernote.change', '#textsoal, .textjawaban, .textjawaban2, .textjawaban-essai', function() {
            markFormDirty();
            updatePreview();
            updateCharCount();
        });

        // Pindah tab jenis soal adalah navigasi internal yang disengaja.
        $(document).on('click', '.qs-tab', function() {
            skipDirtyWarningOnce = true;
            setTimeout(function() {
                skipDirtyWarningOnce = false;
            }, 1500);
        });

        // Override legacy submit handler to avoid double-save requests.
        setTimeout(function() {
            $('#create').off('submit');
            $('#create').on('submit.qs-enhanced', function(e) {
                e.preventDefault();
                performManualSave();
            });
        }, 0);

        // Autosave: cek setiap 20 detik apakah ada perubahan
        if (autoSaveTimer) clearInterval(autoSaveTimer);
        autoSaveTimer = setInterval(function() {
            if (isDirty && !isSavingRequest) {
                performManualSave(true);
            }
        }, AUTOSAVE_INTERVAL);
    }

    function performManualSave(isAuto) {
        if (isSavingRequest) return false;

        const payload = buildSaveFormData(!isAuto);
        if (!payload.ok) {
            if (!isAuto && payload.message && payload.message !== 'Form belum valid') {
                qsShowToast(payload.message, 'warning');
            }
            return false;
        }

        isSavingRequest = true;
        setButtonLoading('#btn-simpan', true);

        $.ajax({
            url: base_url + 'cbtbanksoal/saveSoal',
            type: 'POST',
            data: payload.formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (isResponseSuccess(response)) {
                    isDirty = false;
                    updateLastSavedTime(isAuto);
                    if (!isAuto) {
                        qsShowToast('Soal berhasil disimpan', 'success');
                    }
                    updateNavButtonStatus(currentNomorSoal(), currentJenisSoal(), true);
                } else {
                    if (!isAuto) {
                        qsShowToast('Gagal menyimpan: ' + ((response && response.message) ? response.message : 'respon tidak valid'), 'error');
                    }
                }
            },
            error: function() {
                if (!isAuto) {
                    qsShowToast('Gagal terhubung ke server', 'error');
                }
            },
            complete: function() {
                isSavingRequest = false;
                setButtonLoading('#btn-simpan', false);
            }
        });

        return true;
    }

    function updateLastSavedTime(isAuto) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const label = isAuto ? 'Draft otomatis' : 'Tersimpan';
        $('#last-saved').html('<i class="bi bi-' + (isAuto ? 'cloud-check' : 'clock') + ' me-1"></i>' + label + ' ' + timeStr);
    }

    function updateNavButtonStatus(nomor, jenis, completed) {
        const btnId = '#btn-' + jenis + nomor;
        const $btn = $(btnId);
        if ($btn.length) {
            $btn.removeClass('incomplete completed btn-danger btn-success').addClass(completed ? 'completed' : 'incomplete');
        }
    }

    function isEditableTarget(target) {
        if (!target) return false;
        const $t = $(target);
        if ($t.is('input, textarea, select, [contenteditable="true"], [contenteditable=""], [contenteditable]')) {
            return true;
        }
        if ($t.closest('input, textarea, select, [contenteditable="true"], [contenteditable=""], [contenteditable], .note-editable, .note-editor, .select2-container, .modal, math-field, .ai-editable, .ai-editable-inline').length > 0) {
            return true;
        }
        return false;
    }

    // ============================================
    // KEYBOARD SHORTCUTS
    // ============================================
    function initKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            // Ctrl+S - Save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('#create').trigger('submit');
                return false;
            }
            
            // Ctrl+P - Toggle side panel
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                togglePreviewPanel();
                return false;
            }
            
            // Arrow keys for navigation (only when not editing text)
            if (!isEditableTarget(e.target)) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    navigateQuestion('prev');
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    navigateQuestion('next');
                }
            }
            
            // Escape - Hide shortcuts hint
            if (e.key === 'Escape') {
                hideShortcutsHint();
            }
        });
    }

    function navigateQuestion(direction) {
        const currentBtn = $('.qs-nav-btn.active');
        if (!currentBtn.length) return;
        
        let targetBtn;
        if (direction === 'prev') {
            targetBtn = currentBtn.prev('.qs-nav-btn:not(.add-new)');
        } else {
            targetBtn = currentBtn.next('.qs-nav-btn:not(.add-new)');
        }
        
        if (targetBtn.length) {
            targetBtn.trigger('click');
        }
    }

    function hasVisibleSidePanelSection() {
        // Do not depend on :visible here because preview panel may be hidden first.
        // We only need to know whether any side-section is active for current jenis soal.
        return $('#preview-panel .qs-editor-section').filter(function() {
            return !$(this).hasClass('d-none');
        }).length > 0;
    }

    function syncSplitLayout(forceShow) {
        const $panel = $('#preview-panel');
        const $container = $('.qs-split-container');
        const shouldShow = (typeof forceShow === 'boolean') ? forceShow : hasVisibleSidePanelSection();

        if (!shouldShow) {
            $container.css('grid-template-columns', '1fr');
            $panel.hide();
            return;
        }

        $container.css('grid-template-columns', '1fr ' + QS_SIDE_PANEL_WIDTH);
        $panel.show();
    }

    function togglePreviewPanel() {
        const $panel = $('#preview-panel');
        const $container = $('.qs-split-container');
        if (!hasVisibleSidePanelSection()) {
            $container.css('grid-template-columns', '1fr');
            $panel.hide();
            return;
        }

        if ($panel.is(':visible')) {
            $container.css('grid-template-columns', '1fr');
            $panel.hide();
        } else {
            $container.css('grid-template-columns', '1fr ' + QS_SIDE_PANEL_WIDTH);
            $panel.show();
            updatePreview();
        }
    }

    // ============================================
    // SHORTCUTS HINT
    // ============================================
    function initShortcutsHint() {
        // Show hint on first visit
        if (!localStorage.getItem('qs_shortcuts_seen')) {
            setTimeout(function() {
                showShortcutsHint();
                localStorage.setItem('qs_shortcuts_seen', '1');
            }, 2000);
        }
        
        // Toggle on '?' key only when outside any editable element
        $(document).on('keydown', function(e) {
            if (e.key === '?' && !isEditableTarget(e.target)) {
                e.preventDefault();
                toggleShortcutsHint();
            }
        });
    }

    function showShortcutsHint() {
        $('#shortcuts-hint').addClass('visible');
        setTimeout(function() {
            hideShortcutsHint();
        }, 5000);
    }

    function hideShortcutsHint() {
        $('#shortcuts-hint').removeClass('visible');
    }

    function toggleShortcutsHint() {
        $('#shortcuts-hint').toggleClass('visible');
    }

    // ============================================
    // DRAG AND DROP (SortableJS for options)
    // ============================================
    function initDragAndDrop() {
        // Check if SortableJS is available
        if (typeof Sortable !== 'undefined') {
            const sortableContainer = document.getElementById('sortable-options');
            if (sortableContainer) {
                new Sortable(sortableContainer, {
                    handle: '.qs-option-handle',
                    animation: 150,
                    ghostClass: 'dragging',
                    onEnd: function(evt) {
                        // Update option letters after reorder
                        updateOptionLetters();
                        updatePreview();
                    }
                });
            }
        }
    }

    function updateOptionLetters() {
        $('.qs-option-card').each(function(index) {
            const letter = String.fromCharCode(97 + index); // a, b, c, ...
            const $card = $(this);
            $card.attr('data-opsi', letter);
            $card.find('.qs-option-letter').text(letter.toUpperCase());
            $card.find('.qs-option-title').text('Opsi ' + letter.toUpperCase());
            $card.find('.qs-radio-custom').val(letter).attr('onclick', "selectPgAnswer('" + letter + "')");
            $card.find('.qs-option-delete').attr('onclick', "clearOption('" + letter + "')");

            const $textarea = $card.find('.textjawaban');
            if ($textarea.length) {
                $textarea.attr('id', 'textjawaban_' + letter);
                $textarea.attr('name', 'jawaban_' + letter);
            }
        });
    }

    // ============================================
    // REAL-TIME PREVIEW
    // ============================================
    function initRealTimePreview() {
        // Native inputs
        $('#create').on('input change', '#textsoal, .textjawaban, .qs-radio-custom', function() {
            updatePreview();
            updateCharCount();
        });

        // Summernote editors
        $(document).on('summernote.change', '#textsoal, .textjawaban', function() {
            updatePreview();
            updateCharCount();
        });
    }

    function updatePreview() {
        const soalText = getEditorHtml('#textsoal');
        
        if (optionHasContent(soalText)) {
            $('#preview-soal').html(soalText);
        } else {
            $('#preview-soal').html('<span class="text-muted fst-italic">Teks soal akan muncul di sini...</span>');
        }
        
        // Update options preview
        updateOptionsPreview();
    }

    function updateOptionsPreview() {
        const $previewOptions = $('#preview-options');
        $previewOptions.empty();
        
        $('.qs-option-card').each(function(index) {
            const $card = $(this);
            const letter = $card.find('.qs-option-letter').text();
            const editorId = $card.find('.textjawaban').attr('id');
            const jawabanText = editorId ? getEditorHtml('#' + editorId) : '';
            
            if (optionHasContent(jawabanText)) {
                const isCorrect = $card.find('.qs-radio-custom').is(':checked');
                const previewHtml = `
                    <div class="qs-preview-option ${isCorrect ? 'border-success bg-success-subtle' : ''}">
                        <span class="qs-preview-option-letter">${letter}</span>
                        <div>${jawabanText}</div>
                    </div>
                `;
                $previewOptions.append(previewHtml);
            }
        });
    }

    function updateCharCount() {
        const text = getPlainText(getEditorHtml('#textsoal'));
        const count = text.length;
        $('#soal-char-count').text(count + ' karakter');
    }

    // ============================================
    // FORM VALIDATION
    // ============================================
    function initFormValidation() {
        // Real-time validation
        $('#create').on('blur', '#textsoal', function() {
            validateSoalField();
        });

        $('#create').on('blur', '.textjawaban', function() {
            validateOptionFields(true);
        });

        $(document).on('summernote.blur', '#textsoal', function() {
            validateSoalField();
        });

        $(document).on('summernote.blur', '.textjawaban', function() {
            validateOptionFields(true);
        });
    }

    function validateForm() {
        let isValid = true;
        const jenisAktif = currentJenisSoal();
        
        // Validate soal text
        if (!validateSoalField()) {
            isValid = false;
        }
        
        if (jenisAktif === '1' && $('#pg-options-section').is(':visible')) {
            if (!validateOptionFields(true)) {
                isValid = false;
            }
            if (!$('.qs-radio-custom:checked').length) {
                qsShowToast('Pilih jawaban benar untuk soal PG', 'warning');
                isValid = false;
            }
        }

        if (jenisAktif === '2') {
            const pg2OptionCount = $('#opsi-pg2 .textjawaban2').length;
            const pgkkMode = $('#root-opsi-pg2').hasClass('pgkk-mode');
            if (pg2OptionCount < 2) {
                qsShowToast('Soal PG Kompleks minimal memiliki 2 opsi', 'warning');
                isValid = false;
            }
            if (pgkkMode) {
                const bsFilled = $('#opsi-pg2 .pg2-bs-select').filter(function() { return !!$(this).val(); }).length;
                if (bsFilled < pg2OptionCount) {
                    qsShowToast('Semua pernyataan PGKK harus diberi status Benar/Salah', 'warning');
                    isValid = false;
                }
            } else if ($('#opsi-pg2 .check-pg2:checked').length < 1) {
                qsShowToast('Pilih minimal 1 jawaban benar untuk PG Kompleks', 'warning');
                isValid = false;
            }
        }

        if (jenisAktif === '3' && !window.adaJawaban) {
            qsShowToast('Soal menjodohkan harus memiliki pasangan jawaban', 'warning');
            isValid = false;
        }

        if (jenisAktif === '4' && !$('#jawaban-isian').val().trim()) {
            qsShowToast('Jawaban isian tidak boleh kosong', 'warning');
            isValid = false;
        }

        if (jenisAktif === '5' && !optionHasContent(getEditorHtml('#jawaban-essai'))) {
            qsShowToast('Rubrik/kunci jawaban essai tidak boleh kosong', 'warning');
            isValid = false;
        }

        if (jenisAktif === '6' && typeof window.getUrutanData === 'function') {
            const urutanData = window.getUrutanData();
            if (!urutanData.items || urutanData.items.length < 2) {
                qsShowToast('Soal urutan minimal memiliki 2 item', 'warning');
                isValid = false;
            }
        }

        if (jenisAktif === '7') {
            if (typeof window.getClozeData === 'function') {
                const clozeData = window.getClozeData();
                if (!clozeData.blanks || !Object.keys(clozeData.blanks).length) {
                    qsShowToast('Tambahkan minimal 1 jawaban untuk isian rumpang', 'warning');
                    isValid = false;
                }
            }
        }
        
        return isValid;
    }

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    function qsShowToast(message, type = 'info') {
        if (type === 'success') {
            if (typeof showSuccessToast === 'function') showSuccessToast(message);
        } else if (type === 'error') {
            if (typeof showDangerToast === 'function') showDangerToast(message);
        } else if (type === 'warning') {
            if (typeof showWarningToast === 'function') showWarningToast(message);
        } else {
            if (typeof showInfoToast === 'function') showInfoToast(message);
        }
    }

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    function setButtonLoading(selector, isLoading) {
        const $btn = $(selector);
        if (isLoading) {
            $btn.prop('disabled', true).data('original-html', $btn.html());
            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
        } else {
            $btn.prop('disabled', false).html($btn.data('original-html') || '<i class="bi bi-floppy"></i><span>Simpan Soal</span>');
        }
    }

    function selectPgAnswer(abjad) {
        $('.qs-option-card').removeClass('is-correct');
        $('.qs-option-card[data-opsi="' + abjad + '"]').addClass('is-correct');
        
        $('#pg-answer-status').html('<i class="bi bi-check-circle-fill me-1 text-success"></i>Jawaban: ' + abjad.toUpperCase());
        $('#jawaban').val(abjad).trigger('change');
        
        updateOptionsPreview();
        markFormDirty();
    }

    function addNewOption() {
        const currentCount = $('.qs-option-card').length;
        if (currentCount >= maxPgOptions) {
            qsShowToast('Maksimum ' + maxPgOptions + ' opsi jawaban', 'warning');
            return;
        }
        
        const newLetter = String.fromCharCode(97 + currentCount); // e, f, etc.
        const optionHtml = `
            <div class="qs-option-card qs-fade-in" data-opsi="${newLetter}">
                <div class="qs-option-header">
                    <div class="qs-option-header-left">
                        <span class="qs-option-handle"><i class="bi bi-grip-vertical"></i></span>
                        <span class="qs-option-letter">${newLetter.toUpperCase()}</span>
                        <span class="qs-option-title">Opsi ${newLetter.toUpperCase()}</span>
                    </div>
                    <div class="qs-option-header-right">
                        <label class="qs-option-check">
                            <span class="qs-option-check-label">Jawaban Benar</span>
                            <input class="qs-radio-custom" type="radio" name="pg_jawaban_visual" value="${newLetter}" onclick="selectPgAnswer('${newLetter}')">
                        </label>
                        <button type="button" class="qs-option-delete" onclick="removeOption('${newLetter}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="qs-option-body">
                    <textarea class="textjawaban" id="textjawaban_${newLetter}" name="jawaban_${newLetter}" placeholder="Tulis opsi jawaban di sini..."></textarea>
                </div>
            </div>
        `;
        
        $('#sortable-options').append(optionHtml);
        // Initialize Summernote on newly added option editor.
        if (typeof $.fn.summernote === 'function') {
            $('#textjawaban_' + newLetter).summernote({
                placeholder: 'Tulis Jawaban disini',
                tabsize: 2,
                minHeight: 72,
                maxHeight: null,
                disableResizeEditor: true,
                toolbar: [
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['view', ['fullscreen', 'codeview']],
                    ['insert', ['picture', 'math']]
                ],
                callbacks: {
                    onImageUpload: function(images) {
                        if (typeof myOwnCallBack === 'function' && images && images[0]) {
                            myOwnCallBack(images[0], $(this));
                        }
                    },
                    onMediaDelete: function(target) {
                        if (typeof deleteImage === 'function') {
                            deleteImage(target[0].src);
                        }
                    }
                },
                dialogsInBody: true
            });
        }
        
        qsShowToast('Opsi ' + newLetter.toUpperCase() + ' ditambahkan', 'success');
        markFormDirty();
    }

    function removeOption(abjad) {
        const $card = $('.qs-option-card[data-opsi="' + abjad + '"]');
        $card.fadeOut(200, function() {
            $(this).remove();
            updateOptionLetters();
            updatePreview();
        });
        markFormDirty();
    }

    function clearOption(abjad) {
        const $textarea = $('#textjawaban_' + abjad);
        if ($textarea.next('.note-editor').length && typeof $textarea.summernote === 'function') {
            $textarea.summernote('code', '');
        } else {
            $textarea.val('');
        }
        updatePreview();
        markFormDirty();
    }

    function clearForm() {
        Swal.fire({
            title: 'Bersihkan form?',
            text: 'Semua isian akan dihapus',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, bersihkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#create')[0].reset();
                if ($('#textsoal').next('.note-editor').length && typeof $('#textsoal').summernote === 'function') {
                    $('#textsoal').summernote('code', '');
                }
                $('.textjawaban').each(function() {
                    if ($(this).next('.note-editor').length && typeof $(this).summernote === 'function') {
                        $(this).summernote('code', '');
                    }
                });
                $('.qs-option-card').removeClass('is-correct');
                $('.qs-radio-custom').prop('checked', false);
                updatePreview();
                markFormDirty();
            }
        });
    }

    let cacheTpData = [];

    function loadTpOptionsForBank() {
        const mapelId = '<?= $bank->bank_mapel_id ?? 0 ?>';
        if (!mapelId || mapelId === '0') return;

        $.ajax({
            url: '<?= site_url("cbtbanksoal/apiGetTujuanByMapel") ?>',
            type: 'GET',
            data: { mapel_id: mapelId },
            dataType: 'json',
            success: function(res) {
                if (res.status && Array.isArray(res.data)) {
                    cacheTpData = res.data;
                    const $select = $('#select_tp_id');
                    $select.empty().append('<option value="">-- Pilih Tujuan Pembelajaran (TP) --</option>');
                    
                    let currentCp = '';
                    let $group = null;

                    res.data.forEach(function(tp) {
                        const cpCode = tp.kode_cp || 'Tanpa CP';
                        if (cpCode !== currentCp) {
                            currentCp = cpCode;
                            const groupLabel = tp.elemen_cp ? (cpCode + ' - ' + tp.elemen_cp) : cpCode;
                            $group = $('<optgroup>').attr('label', groupLabel);
                            $select.append($group);
                        }

                        const tpKodeText = (tp.kode ? tp.kode + ': ' : '') + (tp.tujuan || '');
                        const $opt = $('<option>')
                            .val(tp.id_tujuan)
                            .text(tpKodeText);

                        if ($group) {
                            $group.append($opt);
                        } else {
                            $select.append($opt);
                        }
                    });

                    // Sync initial selection if tujuan_id has value
                    const initTujuanId = $('#tujuan_id').val();
                    if (initTujuanId) {
                        $select.val(initTujuanId);
                    }
                }
            }
        });
    }

    function onSelectTpChanged(elem) {
        const tpId = $(elem).val();
        if (!tpId) {
            $('#tujuan_id').val('');
            $('#topik').val('');
            $('#indikator').val('');
            $('#kesulitan').val('');
            $('#meta-tujuan-text').text('-');
            $('#meta-topik-text').text('-');
            $('#meta-indikator-text').text('-');
            $('#meta-kesulitan-text').text('-');
            return;
        }

        const tp = cacheTpData.find(t => String(t.id_tujuan) === String(tpId));
        if (tp) {
            const tpText = (tp.kode ? tp.kode + ' - ' : '') + (tp.tujuan || '');
            const topikText = tp.materi_pokok || tp.elemen_cp || '';
            const indikatorText = tp.tujuan || '';
            const levelKognitif = tp.level_kognitif || 'C4';

            $('#tujuan_id').val(tp.id_tujuan);
            $('#topik').val(topikText);
            $('#indikator').val(indikatorText);
            $('#kesulitan').val(levelKognitif);

            $('#meta-tujuan-text').text(tpText);
            $('#meta-topik-text').text(topikText || '-');
            $('#meta-indikator-text').text(indikatorText || '-');
            $('#meta-kesulitan-text').text(levelKognitif);
        }
    }

    function updateMetadataTextDisplay(targetTujuanId, targetTopik, targetIndikator, targetKesulitan, targetTujuanText) {
        $('#tujuan_id').val(targetTujuanId || '');
        $('#topik').val(targetTopik || '');
        $('#indikator').val(targetIndikator || '');
        $('#kesulitan').val(targetKesulitan || 'C4');

        if (targetTujuanId && targetTujuanId !== '0') {
            $('#select_tp_id').val(targetTujuanId);
        } else {
            $('#select_tp_id').val('');
        }

        const kisiItemId = $('#kartu_item_id').val();
        if (kisiItemId && kisiItemId !== '0') {
            $('#tp-select-wrapper').addClass('d-none');
            $('#tp-display-wrapper').removeClass('d-none');
        } else {
            $('#tp-select-wrapper').removeClass('d-none');
            $('#tp-display-wrapper').addClass('d-none');
        }

        $('#meta-tujuan-text').text(targetTujuanText || '-');
        $('#meta-topik-text').text(targetTopik || '-');
        $('#meta-indikator-text').text(targetIndikator || '-');
        $('#meta-kesulitan-text').text(targetKesulitan || 'C4');
    }

    $(document).ready(function() {
        loadTpOptionsForBank();
    });
</script>
<script src="<?= base_url() ?>/assets/app/js/cbt/banksoal/soal.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>

