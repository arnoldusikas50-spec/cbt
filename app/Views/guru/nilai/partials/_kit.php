<?php
/**
 * Nilai UI Kit — partial bersama untuk semua halaman modul nilai guru.
 * Dipanggil di tiap view dengan: <?= $this->include('guru/nilai/partials/_kit') ?>
 * Spike warna: biru (Bootstrap) — a-baik hijau, b-biru, c-kuning, d-merah.
 */
?>

<style>
    :root {
        --km-blue: #0d6efd;
        --km-indigo: #4f46e5;
        --km-a: #198754;
        --km-b: #0d6efd;
        --km-c: #997404;
        --km-d: #dc3545;
        --km-ink: #212529;
        --km-muted: #6c757d;
        --km-border: #e9ecef;
        --km-radius: 12px;
    }

    /* Toastr notification fixes - ensure visible */
    #toast-container { position: fixed !important; z-index: 999999 !important; top: 70px !important; right: 20px !important; }
    #toast-container > .toast { opacity: 1 !important; box-shadow: 0 4px 20px rgba(0,0,0,0.2) !important; min-width: 280px !important; }
    .toast-success { background-color: #198754 !important; color: #fff !important; }
    .toast-error   { background-color: #dc3545 !important; color: #fff !important; }
    .toast-info    { background-color: #0dcaf0 !important; color: #fff !important; }
    .toast-warning { background-color: #ffc107 !important; color: #212529 !important; }

    .fs-xs { font-size: 0.72rem; }
    .km-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes flashGreen { 0% { background-color: rgba(25,135,84,0.25); } 100% { background-color: transparent; } }
    @keyframes kmFlashSave { 0% { background-color: rgba(25,135,84,0.25); } 100% { background-color: transparent; } }

    /* ===== Kartu tabel universal ===== */
    .km-table-card {
        background: #fff; border-radius: var(--km-radius);
        border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        padding: 1.25rem; margin-bottom: 1.25rem;
    }

    /* ===== Kartu tips / panduan ===== */
    .km-tips-card {
        background: linear-gradient(135deg, #e7f1ff, #d3e3fd);
        border-radius: 14px; padding: 1.1rem 1.5rem; border: 1px solid rgba(13,110,253,0.12);
    }
    .km-tips-icon {
        width: 40px; height: 40px; border-radius: 12px; background: rgba(13,110,253,0.12);
        display: flex; align-items: center; justify-content: center;
        color: var(--km-blue); font-size: 1.1rem; flex-shrink: 0;
    }

    /* ===== Kartu filter + select + tombol ===== */
    .km-select {
        border-radius: 10px; border: 1.5px solid #e0e0e0; padding: 0.55rem 1rem;
        font-weight: 600; color: #455a64; background-color: #fcfcfc;
    }
    .km-select:focus { border-color: var(--km-blue); box-shadow: 0 0 0 3px rgba(13,110,253,0.1); }
    .km-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem;
        padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.8rem; font-weight: 600;
        text-decoration: none; border: none; cursor: pointer; transition: all 0.25s;
    }
    .km-btn-primary { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: #fff; }
    .km-btn-primary:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(13,110,253,0.25); }

    /* ===== Empty state ===== */
    .km-empty-state { text-align: center; padding: 3rem 2rem; }
    .km-empty-icon { font-size: 3rem; color: #bdbdbd; }

    /* ===== Legenda kategori ===== */
    .km-legend {
        display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;
        padding: 0.6rem 1rem; background: #fafafa; border-radius: 10px; border: 1px solid #f0f0f0;
    }
    .km-legend-title { font-weight: 600; font-size: 0.78rem; color: #546e7a; }
    .km-legend-item { font-size: 0.72rem; color: #6c757d; display: flex; align-items: center; gap: 4px; }

    /* ===== Tabel input nilai ===== */
    .km-input-table { border-collapse: separate; border-spacing: 0; }
    .km-input-table thead th {
        position: sticky; top: 0; z-index: 6;
        background: #f8f9fa !important; border-bottom: 2px solid #dee2e6 !important;
        font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;
        color: #495057; padding: 0.75rem 0.5rem !important;
        text-align: center; vertical-align: middle; white-space: nowrap;
    }
    .km-thead-row th { text-align: center; }
    .km-th-no { width: 45px; }
    .km-th-nama { min-width: 230px; text-align: left !important; }
    .km-th-tp { min-width: 90px; cursor: help; }
    .km-th-avg { width: 80px; }
    .km-tp-code { font-weight: 700; color: var(--km-blue); font-size: 0.8rem; }
    .km-tp-desc {
        font-weight: 400; color: var(--km-muted); font-size: 0.6rem;
        max-width: 90px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;
        margin: 0 auto;
    }
    .km-th-tp.km-col-active, .km-th.km-col-active { color: var(--km-blue) !important; background: #e7f1ff !important; }

    .km-data-row { transition: background-color 0.15s ease; }
    .km-data-row:hover { background: rgba(13,110,253,0.03) !important; }
    .km-data-row td { border-bottom: 1px solid #f1f3f5 !important; }
    .km-data-row.km-row-flash > td { animation: kmFlashSave 0.6s ease; }

    /* Kolom siswa & nomor menempel saat scroll horizontal */
    .km-sticky-no, .km-sticky-nama { position: sticky; z-index: 4; background: inherit; }
    .km-sticky-no { left: 0; }
    .km-sticky-nama { left: 45px; box-shadow: 4px 0 6px -4px rgba(0,0,0,0.08); }
    thead .km-sticky-no { z-index: 7; }
    thead .km-sticky-nama { left: 45px; background: #f8f9fa !important; box-shadow: 4px 0 6px -4px rgba(0,0,0,0.08); }

    /* Highlight kolom TP yang aktif */
    .km-td-input.km-col-active { background-color: rgba(13,110,253,0.06) !important; }

    .km-td-no { color: #6c757d; font-size: 0.8rem; font-weight: 600; }
    .km-td-avg { font-size: 0.85rem; }

    .km-avatar {
        width: 32px; height: 32px; border-radius: 8px; background: #e7f1ff; color: #0a58ca;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 0.8rem;
    }
    .km-student-name { font-weight: 600; color: var(--km-ink); font-size: 0.85rem; line-height: 1.2; }
    .km-student-nis { font-size: 0.7rem; color: var(--km-muted); }

    /* ===== Input nilai ===== */
    .km-input-nilai {
        width: 100%; height: 48px;
        border: none; border-right: 1px solid #f1f3f5;
        text-align: center; font-weight: 600; font-size: 0.95rem;
        background: transparent; color: var(--km-ink);
        transition: all 0.2s; outline: none; -moz-appearance: textfield;
    }
    .km-input-nilai::-webkit-inner-spin-button,
    .km-input-nilai::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .km-input-nilai:focus {
        background: #fff !important; box-shadow: inset 0 0 0 2px var(--km-blue) !important;
        z-index: 2; position: relative;
    }
    .km-flash-success { animation: kmFlashSave 0.6s ease; }

    /* Grade colors */
    .km-grade-a { background: rgba(25,135,84,0.08) !important; color: #198754 !important; }
    .km-grade-b { background: rgba(13,110,253,0.08) !important; color: #0b5ed7 !important; }
    .km-grade-c { background: rgba(255,193,7,0.12) !important; color: #997404 !important; }
    .km-grade-d { background: rgba(220,53,69,0.08) !important; color: #dc3545 !important; }
    /* Alias kelas halaman akhir */
    .grade-premium-a { color: #198754 !important; background-color: rgba(25,135,84,0.08) !important; }
    .grade-premium-b { color: #0b5ed7 !important; background-color: rgba(13,110,253,0.08) !important; }
    .grade-premium-c { color: #997404 !important; background-color: rgba(255,193,7,0.12) !important; }
    .grade-premium-d { color: #dc3545 !important; background-color: rgba(220,53,69,0.08) !important; }

    .km-avg-badge { font-weight: 700; font-size: 0.85rem; padding: 0.35rem 0.65rem; border-radius: 8px; }

    /* ===== Indikator baris ===== */
    .save-indicator-small { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
    .save-indicator-small i { font-size: 0.9rem; transition: all 0.3s; }
    .save-indicator-small.saving i { color: #0b5ed7; }
    .save-indicator-small.saved i { color: #198754; }
    .save-indicator-small.error i { color: #dc3545; }

    .row-save-indicator { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s; }
    .row-save-indicator.saving i { color: var(--km-blue); opacity: 1; }
    .row-save-indicator.saved { background: #d1e7dd; }
    .row-save-indicator.saved i { color: #14532d; opacity: 1; }
    .row-save-indicator.error { background: #f8d7da; }
    .row-save-indicator.error i { color: #991b1b; opacity: 1; }

    /* ===== Status simpan (pill) ===== */
    .km-save-status { display: flex; justify-content: flex-end; padding: 0.5rem 0 0 0; }
    .km-save-indicator {
        display: inline-flex; align-items: center; gap: 0.3rem;
        font-size: 0.78rem; color: var(--km-muted);
        padding: 0.3rem 0.75rem; border-radius: 2rem;
        background: #f8f9fa; border: 1px solid var(--km-border);
        transition: all 0.2s ease;
    }
    .km-save-indicator.saving { color: #0b5ed7; background: #e7f1ff; border-color: #b6d4fe; }
    .km-save-indicator.saved  { color: #198754; background: #d1e7dd; border-color: #badbcc; }
    .km-save-indicator.error  { color: #dc3545; background: #f8d7da; border-color: #f5c2c7; }
    .save-status-pill { padding: 0.4rem 0.8rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; background: white; border: 1px solid #e2e8f0; }
    .save-status-pill.saving { color: #0b5ed7; background: #e7f1ff; }
    .save-status-pill.saved { color: #198754; background: #d1e7dd; }
    .save-status-pill.error { color: #dc3545; background: #f8d7da; }

    /* ===== Banner notifikasi ===== */
    .km-notif-banner {
        display: flex; align-items: center; justify-content: space-between;
        padding: 0.85rem 1.25rem; margin: 0.75rem 0 0.25rem 0; border-radius: 10px;
        background: linear-gradient(135deg, #198754, #146c43);
        color: #fff; box-shadow: 0 4px 15px rgba(25,135,84,0.25);
        animation: slideDown 0.3s ease; z-index: 100; position: relative;
    }
    .km-notif-banner.error { background: linear-gradient(135deg, #dc3545, #b02a37); box-shadow: 0 4px 15px rgba(220,53,69,0.25); }
    .km-notif-banner.info { background: linear-gradient(135deg, #0dcaf0, #0aa2c0); box-shadow: 0 4px 15px rgba(13,202,240,0.25); }
    .km-notif-content { display: flex; align-items: center; gap: 0.75rem; font-weight: 600; font-size: 0.95rem; }
    .km-notif-content i { font-size: 1.2rem; }
    .km-notif-close {
        background: rgba(255,255,255,0.2); border: none; color: #fff;
        width: 30px; height: 30px; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.2s;
    }
    .km-notif-close:hover { background: rgba(255,255,255,0.35); }

    /* ===== Komponen halaman nilai akhir ===== */
    .table-modern thead th {
        background: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057;
        font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
        padding: 0.75rem 1rem; letter-spacing: 0.5px;
    }
    .table-modern .student-row:hover { background-color: rgba(13,110,253,0.04); }
    .avg-bubble { padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; display: inline-block; min-width: 38px; }
    .sts-input-premium {
        height: 38px; font-size: 0.9rem; border-radius: 8px; border: 1px solid #e2e8f0;
        transition: all 0.2s; background: #fff;
    }
    .sts-input-premium:focus { border-color: var(--km-blue) !important; box-shadow: 0 0 0 3px rgba(13,110,253,0.1) !important; outline: none; }
    .modern-textarea {
        font-size: 0.75rem !important; border-radius: 0 6px 6px 0; border: 1px solid #e2e8f0;
        transition: all 0.2s; padding: 0.5rem 0.75rem; min-height: 38px; resize: vertical; line-height: 1.35;
    }
    .modern-textarea:focus { border-color: var(--km-blue); box-shadow: none; z-index: 5; }
    .input-group:focus-within .input-group-text { background-color: var(--km-blue) !important; color: white !important; }
    .border-dashed { border-style: dashed !important; }
    .input-weight-wrapper {
        background: transparent; padding: 0; display: inline-flex; align-items: center;
        font-weight: 800; font-size: 0.85rem; color: var(--km-blue);
    }
    .input-weight-wrapper input {
        width: 35px; border: none; background: transparent; text-align: center; font-weight: 800; outline: none; color: inherit;
    }
    .weight-sep { font-weight: 800; }
    .total-weight-badge { padding: 4px 12px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; transition: all 0.3s; }
    .bg-purple { background-color: #8b5cf6 !important; }
    .text-purple { color: #8b5cf6 !important; }
    .border-purple { border-color: #8b5cf6 !important; }
    .cell-praktik { color: #fff !important; background-color: #8b5cf6 !important; border: 1px solid #7c3aed !important; }
    .cell-praktik.opacity-50 { opacity: 0.6 !important; }
    .badge-predikat {
        padding: 0.35rem 0.5rem; border-radius: 6px; font-weight: 800; font-size: 0.8rem;
        display: inline-block; min-width: 28px; color: #fff; transition: all 0.2s ease;
    }
    .badge-predikat.badge-success { background: #198754; }
    .badge-predikat.badge-primary { background: #0d6efd; }
    .badge-predikat.badge-warning { background: #ffc107; }
    .badge-predikat.badge-danger { background: #dc3545; }
    .badge-predikat.badge-secondary { background: #6c757d; }
    .search-icon-absolute { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--km-muted); pointer-events: none; }
    .km-filter-input {
        padding-left: 2.2rem !important; border-radius: 10px; border: 1.5px solid #dee2e6;
        font-size: 0.8rem; height: 38px; transition: all 0.2s;
    }
    .km-filter-input:focus { border-color: var(--km-blue); box-shadow: 0 0 0 3px rgba(13,110,253,0.1); }
</style>

<script>
/**
 * Nilai UI Kit — helper JS bersama (tanpa URL / endpoint; murni logika tampilan).
 */
(function () {
    'use strict';

    // Default toastr biar konsisten di semua halaman modul nilai.
    if (window.toastr) {
        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000,
            extendedTimeOut: 3000,
            showDuration: 300,
            hideDuration: 300,
            preventDuplicates: false,
            newestOnTop: true
        };
    }

    // Warna input sesuai grade 0-100.
    window.kmColorCode = function (input) {
        input = input instanceof jQuery ? input : $(input);
        let raw = input.val();
        let val = parseFloat(raw);
        input.removeClass('km-grade-a km-grade-b km-grade-c km-grade-d');
        if (raw === '' || raw === null || val === undefined || isNaN(val)) return;
        if (val >= 90) input.addClass('km-grade-a');
        else if (val >= 80) input.addClass('km-grade-b');
        else if (val >= 70) input.addClass('km-grade-c');
        else input.addClass('km-grade-d');
    };

    // Predikat A/B/C/D + warna bootstrap.
    window.kmPredikat = function (val) {
        let v = parseFloat(val);
        if (v >= 90) return { huruf: 'A', color: 'success' };
        if (v >= 80) return { huruf: 'B', color: 'primary' };
        if (v >= 70) return { huruf: 'C', color: 'warning' };
        if (!isNaN(v)) return { huruf: 'D', color: 'danger' };
        return { huruf: '-', color: 'secondary' };
    };

    // Batasi input nilai 0..100, kembalikan nilai clamp hasil.
    window.kmClamp = function (input) {
        let raw = input.val();
        if (raw === '' || raw === null) return raw;
        let val = parseFloat(raw);
        if (isNaN(val)) { input.val(''); return ''; }
        if (val > 100) { input.val(100); if (window.toastr) toastr.warning('Nilai maksimal 100'); return 100; }
        if (val < 0) { input.val(0); if (window.toastr) toastr.warning('Nilai minimal 0'); return 0; }
        return val;
    };

    // Auto-grow textarea deskripsi rapor.
    window.kmAutoGrow = function (el) {
        let $el = el instanceof jQuery ? el : $(el);
        $el.css('height', 'auto').css('height', $el[0].scrollHeight + 'px');
    };

    // Filter baris tabel siswa berdasarkan nama/NIS (data-filter-key).
    window.kmFilterRows = function (tbodySel, query, emptyTextSel) {
        let q = (query || '').toLowerCase().trim();
        let visible = 0;
        $(tbodySel + ' tr[row-filter]').each(function () {
            let key = $(this).attr('row-filter') || '';
            let show = q === '' || key.indexOf(q) !== -1;
            $(this).toggle(show);
            if (show) visible++;
        });
        if (emptyTextSel) $(emptyTextSel).toggle(q !== '' && visible === 0);
        return visible;
    };

    $(function () {
        // Auto-grow semua textarea deskripsi.
        $('.modern-textarea').each(function () { kmAutoGrow(this); });
        $(document).on('input', '.modern-textarea', function () { kmAutoGrow(this); });

        // Tooltip bootstrap opsional.
        if (window.bootstrap) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }
    });
})();
</script>
