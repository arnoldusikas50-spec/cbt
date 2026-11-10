<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>

<style>
    :root {
        --cbt-primary: #6366f1;
        --cbt-primary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
        --cbt-secondary: #64748b;
        --cbt-success: #10b981;
        --cbt-info: #0ea5e9;
        --cbt-info-light: #f0f9ff;
        --cbt-warning: #f59e0b;
        --cbt-danger: #ef4444;
        --cbt-border: #e2e8f0;
        --cbt-bg-light: #f8fafc;
    }

    /* ── Tombol tema (terang/gelap) ─────────────────────────────────── */
    .btn-theme-toggle {
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        border-radius: 999px;
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        transition: all .2s;
    }
    .btn-theme-toggle:hover {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4338ca;
    }
    [data-bs-theme="dark"] .btn-theme-toggle {
        background: #1f2937;
        border-color: #334155;
        color: #fbbf24;
    }
    [data-bs-theme="dark"] .btn-theme-toggle:hover {
        background: #312e81;
        border-color: #4338ca;
        color: #fde68a;
    }

    /* ── Token: tombol pill compact di header card ───────────────────── */
    .btn-token-cbt {
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #4338ca;
        border-radius: 999px;
        font-weight: 700;
        font-size: .78rem;
        padding: 6px 14px;
        height: auto;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all .2s;
        white-space: nowrap;
    }
    .btn-token-cbt:hover {
        background: #e0e7ff;
        border-color: #a5b4fc;
        color: #3730a3;
        transform: none;
    }
    .btn-token-cbt code#btn-token-value {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: .92rem;
        font-weight: 800;
        letter-spacing: .14em;
        color: #4338ca;
        background: transparent;
        padding: 0;
    }
    .btn-token-cbt #btn-token-count {
        background: #4338ca;
        color: #fff;
        font-size: .62rem;
        font-weight: 700;
    }

    /* ── Modal token ─────────────────────────────────────────────────── */
    .min-w-0 { min-width: 0; }
    #tokenModal .modal-content {
        border-radius: 16px;
        overflow: hidden;
        border: 0;
    }
    /* Header gradasi (pindah dari inline style agar mudah dipelihara) */
    .token-modal-head {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: 0;
        padding: 18px 20px 16px;
        align-items: center;
    }
    .token-modal-ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 13px;
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .28);
        color: #fff;
        font-size: 1.15rem;
        flex: 0 0 auto;
    }
    .token-modal-title {
        color: #fff;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.2;
        margin: 0;
    }
    .token-modal-sub {
        color: rgba(255, 255, 255, .78);
        font-size: .74rem;
        font-weight: 500;
        margin: 2px 0 0;
    }

    .token-modal-body {
        background: #f8fafc;
        padding: 18px 20px;
    }
    .token-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 2px 6px -4px rgba(15, 23, 42, .12);
    }
    .token-modal-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #64748b;
    }
    .token-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding-bottom: 12px;
        margin-bottom: 14px;
        border-bottom: 1px dashed #e2e8f0;
    }

    /* Nilai token: kotak digit per karakter */
    .token-modal-value {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 6px;
        background: #eef2ff;
        border: 1px dashed #a5b4fc;
        border-radius: 12px;
        padding: 14px 10px;
    }
    .tok-digit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: clamp(32px, 8vw, 44px);
        height: clamp(42px, 10vw, 54px);
        border-radius: 10px;
        background: #fff;
        border: 1px solid #c7d2fe;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: clamp(1.15rem, 4vw, 1.65rem);
        font-weight: 800;
        color: #312e81;
    }
    .tok-plain {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: clamp(1rem, 4vw, 1.35rem);
        font-weight: 800;
        letter-spacing: .18em;
        color: #312e81;
        word-break: break-all;
        text-align: center;
    }
    .tok-muted { color: #94a3b8; }

    /* Tombol salin token */
    #copy-token-btn {
        border: 0;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        border-radius: 999px;
        font-weight: 700;
        font-size: .8rem;
        padding: 9px 20px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: .04em;
        transition: all .2s;
    }
    #copy-token-btn:hover {
        box-shadow: 0 4px 12px rgba(99, 102, 241, .4);
        transform: none;
        color: #fff;
    }
    .token-single-foot {
        text-align: center;
        margin-top: 14px;
    }
    .token-single-foot .token-hint {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .72rem;
        color: #94a3b8;
        justify-content: center;
    }

    /* Daftar banyak token: area scroll + tombol salin semua */
    .btn-token-copyall {
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #4338ca;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s;
        white-space: nowrap;
    }
    .btn-token-copyall:hover {
        background: #e0e7ff;
        border-color: #a5b4fc;
        color: #3730a3;
    }
    .token-list-scroll {
        max-height: min(46vh, 420px);
        overflow: auto;
        padding: 2px;
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
    }
    .token-list-scroll::-webkit-scrollbar { width: 8px; }
    .token-list-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 20px;
    }
    .token-chip-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    .token-chip {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 9px 14px;
        box-shadow: 0 2px 6px -4px rgba(15, 23, 42, .18);
        transition: all .2s;
    }
    .token-chip:hover { border-color: #a5b4fc; }
    .token-chip .chip-ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        background: #eef2ff;
        color: #4338ca;
        font-size: .85rem;
        flex: 0 0 auto;
    }
    .token-chip .chip-kode {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
        white-space: nowrap;
    }
    .token-chip .chip-val {
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: .12em;
        color: #312e81;
    }
    .token-chip .btn-token-copy {
        border: 0;
        background: #eef2ff;
        color: #4338ca;
        width: 28px;
        height: 28px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all .2s;
        flex: 0 0 auto;
    }
    .token-chip .btn-token-copy:hover { background: #c7d2fe; }
    .token-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-align: center;
        color: #94a3b8;
        font-size: .85rem;
        padding: 26px 0 18px;
    }
    .token-empty .bi { font-size: 1.6rem; opacity: .7; }

    /* Footer modal token */
    .token-modal-footer {
        background: #f8fafc;
        border: 0;
        padding: 0 20px 18px;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .token-modal-footer .token-hint {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .72rem;
        color: #94a3b8;
        min-width: 0;
    }
    .token-modal-footer .btn {
        border-radius: 999px;
        font-weight: 600;
    }

    @media (max-width: 575.98px) {
        .btn-token-cbt { padding: 6px 10px; font-size: .74rem; }
        .btn-token-cbt .token-btn-suffix { display: none; }
        .token-modal-body { padding: 14px; }
        .token-panel { padding: 12px; }
        .token-chip { padding: 7px 10px; gap: 8px; }
        .token-chip .chip-val { font-size: .95rem; letter-spacing: .08em; }
        .token-modal-footer .token-hint { flex: 1 1 100%; }
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
    }

    /* ── Filter bar: satu baris ringkas agar tabel dapat lebih tinggi ── */
    .cbt-filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px 14px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .cbt-filter-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        flex: 1 1 170px;
        min-width: 0;
    }
    .cbt-filter-item.by-kelas { flex: 1 1 220px; }
    .cbt-filter-item.by-ruang { flex: 1 1 170px; }
    .cbt-filter-toggle {
        flex: 0 1 190px;
    }

    .filter-label {
        font-size: 10px;
        font-weight: 700;
        color: var(--cbt-secondary);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: 0;
        display: block;
    }

    .form-select-cbt, .form-control-cbt {
        border-radius: 10px;
        border: 1px solid var(--cbt-border);
        padding: 8px 14px;
        font-size: 0.85rem;
        background-color: var(--cbt-bg-light);
        transition: all 0.2s;
        height: 38px;
    }

    .form-select-cbt:focus, .form-control-cbt:focus {
        border-color: var(--cbt-primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        background-color: white;
    }

    /* Premium Toggle */
    .switch-cbt {
        display: inline-flex;
        align-items: center;
        background: var(--cbt-bg-light);
        padding: 4px;
        border-radius: 10px;
        gap: 10px;
        cursor: pointer;
        width: 100%;
        height: 38px;
        justify-content: center;
    }

    .switch-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--cbt-secondary);
        transition: color 0.2s;
    }

    .switch-item {
        position: relative;
        width: 44px;
        height: 22px;
    }

    .switch-item input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .4s;
        border-radius: 20px;
    }

    .switch-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .switch-slider { background-color: var(--cbt-primary); }
    input:checked + .switch-slider:before { transform: translateX(22px); }

    .switch-active { color: var(--cbt-primary); }

    /* Panduan: dibuat collapsible agar tabel mendapat ruang lebih luas */
    .guide-box {
        background-color: var(--cbt-info-light);
        border-left: 4px solid var(--cbt-info);
        border-radius: 12px;
        padding: 0;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .guide-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: transparent;
        border: 0;
        padding: 9px 14px;
        text-align: left;
    }
    .guide-toggle:hover { background: rgba(14, 165, 233, .06); }
    .guide-chevron {
        color: var(--cbt-info);
        transition: transform .25s ease;
        font-size: .85rem;
    }
    .guide-toggle[aria-expanded="true"] .guide-chevron { transform: rotate(180deg); }

    .guide-title {
        color: var(--cbt-info);
        font-weight: 700;
        font-size: .85rem;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .guide-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px 26px;
        list-style: none;
        padding: 2px 16px 14px 32px;
        margin: 0;
    }

    .guide-item {
        font-size: 0.85rem;
        color: #475569;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .guide-item::before {
        content: "•";
        color: var(--cbt-secondary);
        font-weight: bold;
    }

    .guide-item b {
        color: #1e293b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .action-bar-left {
        display: flex;
        gap: 8px;
        align-items: center;
        flex: 1 1 340px;
        max-width: 480px;
        min-width: 0;
    }

    .action-bar-right {
        display: flex;
        gap: 10px;
        align-items: center;
        flex: 1 1 320px;
        min-width: 0;
    }

    .action-bar-right .search-container { min-width: 0; }

    /* Select2 di dalam action-bar harus boleh menyusut agar tidak meluber */
    .action-bar-left .select2-container { flex: 1 1 auto; min-width: 0; max-width: 100%; }
    .action-bar-left .select2-selection { min-width: 0; }

    .btn-cbt-status {
        border-radius: 10px;
        font-weight: 600;
        padding: 8px 16px;
        height: 38px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-cbt-primary { background: var(--cbt-primary-gradient); color: white; border: none; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.2); }
    .btn-cbt-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(99,102,241,0.3); color: white; }

    .btn-cbt-success { background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2); }
    .btn-cbt-success:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(16,185,129,0.3); color: white; }

    .btn-refresh-cbt {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #6366f1;
        color: white;
        border: none;
        flex: 0 0 auto;
        transition: background .2s, box-shadow .2s, opacity .2s, transform .12s;
    }
    .btn-refresh-cbt:hover:not(:disabled) {
        background: #4f46e5;
        box-shadow: 0 4px 12px rgba(99, 102, 241, .35);
    }
    .btn-refresh-cbt:active:not(:disabled) { transform: scale(.94); }
    .btn-refresh-cbt:disabled { opacity: .75; cursor: progress; }
    /* Ikon berputar saat sedang memuat data */
    .btn-refresh-cbt.is-loading > i { animation: cbt-spin .7s linear infinite; }
    @keyframes cbt-spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }

    .search-container {
        position: relative;
        flex: 1;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--cbt-secondary);
    }

    .search-input-cbt {
        padding-left: 45px !important;
        border-radius: 50px !important;
        background: white !important;
        border: 1px solid var(--cbt-border) !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
    }

    /* ── Card tabel: flat modern, tanpa bayangan gelap ───────────────── */
    .cbt-card-flat {
        box-shadow: none !important;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
    }
    /* Padding card dirapatkan → area tabel lebih luas */
    .cbt-card-body {
        padding: 12px 14px;
    }

    /* ── Tabel: desain modern (tanpa garis vertikal, pemisah tipis) ───── */
    .table-status-cbt {
        border: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 900px;
        table-layout: auto;
        margin-bottom: 0;
        color: #334155;
    }

    /* Header: label kecil uppercase, garis pemisah tipis */
    .table-status-cbt thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: .68rem;
        letter-spacing: .06em;
        white-space: nowrap;
        vertical-align: middle;
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        border-top: 0;
        position: sticky;
        z-index: 12;
    }
    /* Baris header ke-1 menempel di atas, baris ke-2 menempel di bawahnya */
    .table-status-cbt thead tr:first-child th {
        top: 0;
        z-index: 13;
        border-bottom-color: transparent;
    }
    .table-status-cbt thead tr:nth-child(2) th { top: var(--cbt-head-h, 44px); }
    /* Sel group header (JADWAL UJIAN / STATUS SISWA / AKSI) harus setinggi
       kotak baris header-1, jika tidak akan ada celah saat tabel digeser
       vertikal (sel rowspan membuat tinggi baris tidak sinkron) */
    .table-status-cbt thead tr:first-child th:not([rowspan]) {
        height: var(--cbt-head-h, 44px);
    }
    /* Sel ber-rowspan (NO. / DATA SISWA) tetap memakai garis pemisah */
    .table-status-cbt thead tr:first-child th[rowspan] { border-bottom-color: #e2e8f0; }

    /* Isi tabel: padding rapat agar lebih banyak siswa terlihat */
    .table-status-cbt tbody td {
        padding: 7px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        background-color: inherit;
    }
    .table-status-cbt tbody tr:last-child td { border-bottom: 0; }

    /* Warna latar baris (pakai class agar ikut tema light/dark) */
    .cbt-row-odd    { background-color: #ffffff; }
    .cbt-row-even   { background-color: #f3f5fa; }
    .cbt-row-blocked { background-color: #fdf2f2; }

    /* Warna sel mengikuti baris (status/stripe) agar kolom lengket tetap opaque */
    .table-status-cbt tbody tr > td { background-color: inherit; }
    /* Hover: overlay halus agar warna status baris tetap terlihat */
    .table-status-cbt tbody tr:hover > td {
        box-shadow: inset 0 0 0 9999px rgba(99, 102, 241, .06);
    }
    /* Netralkan hover bawaan bootstrap di tabel ini */
    .table-status-cbt.table-hover > tbody > tr:hover > * {
        --bs-table-bg: transparent;
        --bs-table-hover-bg: transparent;
        --bs-table-accent-bg: transparent;
        background-color: inherit;
        color: inherit;
    }

    /* Nomor baris: chip persegi membulat */
    .cbt-row-no {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
    }

    /* ── Kolom lengket (sticky): NO. + DATA SISWA ────────────────────── */
    .table-status-cbt .col-sticky-1 {
        width: 58px;
        min-width: 58px;
        position: sticky;
        left: 0;
        z-index: 8;
        /* pemisah tipis antara kolom NO. dan DATA SISWA */
        border-right: 1px solid #eef2f7;
    }
    .table-status-cbt .col-sticky-2 {
        left: 58px;
        width: 210px;
        min-width: 210px;
        max-width: 260px;
        position: sticky;
        z-index: 8;
        /* Pemisah tegas antara identitas siswa (nama) dan kolom mapel/soal:
           garis ikut menempel pada kolom lengket sehingga selalu terlihat
           saat tabel digeser ke samping. */
        border-right: 2px solid #cbd5e1;
    }
    .table-status-cbt thead .col-sticky-1,
    .table-status-cbt thead .col-sticky-2 { z-index: 16; }
    /* Batasi lebar isi kolom DATA SISWA agar kolom tidak melebar sendiri
       (max-width pada <td> diabaikan browser saat table-layout: auto) */
    .table-status-cbt .col-sticky-2 > .cbt-siswa-cell { max-width: 236px; }
    .table-status-cbt .col-sticky-2 > .cbt-siswa-cell span {
        overflow-wrap: anywhere;
        max-width: 100%;
    }

    /* Pemisah kelompok kolom AKSI agar tidak tertukar dengan STATUS SISWA */
    .table-status-cbt .col-reset-waktu { border-left: 1px solid #eef2f7; }

    /* Kolom identitas siswa (NO. + DATA SISWA) diberi blok warna sangat
       halus agar jelas terpisah dari kolom mapel/status (stripe tetap terlihat) */
    .table-status-cbt tbody td.col-sticky-1,
    .table-status-cbt tbody td.col-sticky-2 {
        background-image: linear-gradient(rgba(99, 102, 241, .04), rgba(99, 102, 241, .04));
    }

    /* ── Badge siswa: pill bernuansa lembut (bukan warna solid) ──────── */
    /* Catatan: theme (.bg-success/.bg-danger/.bg-primary) memakai
       `color:#fff !important`, jadi warna teks badge WAJIB memakai !important
       agar tidak menjadi putih di atas background terang. */
    .table-status-cbt .col-sticky-2 .badge {
        border-radius: 999px;
        font-weight: 600;
        font-size: .66rem;
        letter-spacing: .01em;
        padding: 4px 9px;
    }
    .table-status-cbt .col-sticky-2 .badge.bg-success {
        background: #ecfdf5 !important;
        color: #047857 !important;
    }
    .table-status-cbt .col-sticky-2 .badge.bg-danger {
        background: #fef2f2 !important;
        color: #b91c1c !important;
    }
    .table-status-cbt .col-sticky-2 .badge.bg-warning {
        background: #fffbeb !important;
        color: #b45309 !important;
    }
    .table-status-cbt .col-sticky-2 .badge.bg-primary {
        background: #eef2ff !important;
        color: #4338ca !important;
    }

    /* Badge "Siswa belum ujian" */
    .cbt-badge-info {
        border-radius: 999px;
        font-weight: 600;
        font-size: .7rem;
        padding: 5px 12px;
        background: #fffbeb !important;
        color: #b45309 !important;
    }

    /* ── Tombol aksi & checkbox: tampilan modern ─────────────────────── */
    .table-status-cbt tbody .btn {
        border-radius: 9px;
        font-size: .78rem;
        line-height: 1;
        padding: 6px 9px;
        border-width: 1px;
    }
    .table-status-cbt .col-fouls .btn {
        border-radius: 999px;
        font-weight: 700;
        padding: 5px 11px;
    }
    .table-status-cbt .col-fouls .btn.btn-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .table-status-cbt .col-fouls .btn.btn-danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }
    .table-status-cbt .col-fouls .btn.btn-outline-secondary {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #94a3b8;
    }
    /* Tombol reset waktu / blokir: warna lembut */
    .table-status-cbt .col-reset-waktu .btn-warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #b45309;
    }
    .table-status-cbt .col-reset-waktu .btn-warning:hover {
        background: #fef3c7;
        border-color: #fcd34d;
    }
    .table-status-cbt .col-reset-waktu .btn-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }
    .table-status-cbt .col-reset-waktu .btn-danger:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }
    .table-status-cbt .col-reset-waktu .btn-success {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
    }
    .table-status-cbt .col-reset-waktu .btn-success:hover {
        background: #d1fae5;
        border-color: #6ee7b7;
    }
    .table-status-cbt input[type="checkbox"].check {
        accent-color: var(--cbt-primary);
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    /* ── Chip identitas siswa: lembut & konsisten (pengganti badge solid) ── */
    .cbt-chip {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .02em;
        line-height: 1.5;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .cbt-chip-agama { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
    .cbt-chip-kelas { background: #eef2ff; color: #4338ca; border-color: #e0e7ff; }
    .cbt-chip-ruang { background: #fffbeb; color: #b45309; border-color: #fde68a; }
    .cbt-chip-sesi  { background: #f0f9ff; color: #0369a1; border-color: #bae6fd; }

    /* Warna jam mulai saat siswa sedang mengerjakan */
    .cbt-mulai-run { color: #4338ca; }

    /* ── Sel MAPEL: nama mapel di atas, badge kode + ketersediaan di bawah ── */
    .cbt-mapel-cell {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        max-width: 140px;
    }
    .cbt-mapel {
        font-weight: 700;
        color: #334155;
        font-size: .85rem;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }
    .cbt-mapel-sub {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .cbt-kode-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 6px;
        padding: 2px 7px;
        font-size: .64rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .cbt-avail {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        font-size: .65rem;
    }
    .cbt-avail.ok  { background: #ecfdf5; color: #047857; }
    .cbt-avail.end { background: #f1f5f9; color: #94a3b8; }

    /* ── Pill status/durasi ujian ────────────────────────────────────── */
    .cbt-time-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: .7rem;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid transparent;
    }
    .cbt-pill-run    { background: #eef2ff; color: #4338ca; border-color: #e0e7ff; }
    .cbt-pill-done   { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .cbt-pill-none   { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; }
    .cbt-pill-blokir { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

    /* ── Empty state tabel ───────────────────────────────────────────── */
    .cbt-empty-cell { padding: 44px 16px !important; }
    .cbt-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .cbt-empty-state .bi { font-size: 2.2rem; color: #cbd5e1; }
    .cbt-empty-title { font-weight: 700; color: #334155; font-size: .95rem; }
    .cbt-empty-hint  { font-size: .8rem; color: #94a3b8; }

    /* ── Header berwarna: tint lembut per kelompok kolom ────────────── */
    /* NB: premium_adminlte.css memaksa thead th jadi abu-abu dengan
       !important, maka semua rule warna header WAJIB !important agar
       tidak ditimpa tema. Warna sengaja dibuat soft (pastel) agar
       tabel tidak "berteriak" dan data siswa tetap jadi fokus. */
    /* NO. & DATA SISWA: netral slate (lengket, wajib opaque) */
    .table-status-cbt thead tr:first-child th.col-sticky-1,
    .table-status-cbt thead tr:first-child th.col-sticky-2 {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    .table-status-cbt thead tr:first-child th.col-sticky-2 {
        border-right: 2px solid #cbd5e1 !important;
    }
    /* MAPEL: indigo lembut */
    .table-status-cbt thead tr:first-child th.th-group-jadwal {
        background: #eef2ff !important;
        color: #4338ca !important;
        border-bottom: 2px solid #c7d2fe !important;
    }
    /* STATUS UJIAN: emerald lembut */
    .table-status-cbt thead tr:first-child th.th-group-status {
        background: #ecfdf5 !important;
        color: #047857 !important;
        border-bottom: 2px solid #a7f3d0 !important;
    }
    /* AKSI: rose lembut */
    .table-status-cbt thead tr:first-child th.th-group-aksi {
        background: #fff1f2 !important;
        color: #be123c !important;
        border-bottom: 2px solid #fecdd3 !important;
    }
    /* Pemisah vertikal antar kelompok kolom */
    .table-status-cbt thead tr:first-child th.th-group-status,
    .table-status-cbt thead tr:first-child th.th-group-aksi {
        border-left: 1px solid #e2e8f0;
    }
    .table-status-cbt thead th i {
        margin-right: 3px;
        opacity: .8;
    }

    /* Baris header ke-2: netral, warna teks mengikuti grupnya */
    .table-status-cbt thead tr:nth-child(2) th {
        background: #fafbfc !important;
        color: #64748b !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .table-status-cbt thead tr:nth-child(2) th.col-mulai,
    .table-status-cbt thead tr:nth-child(2) th.col-durasi,
    .table-status-cbt thead tr:nth-child(2) th.col-fouls {
        background: #f4fbf7 !important;
        color: #059669 !important;
        border-bottom: 1px solid #d1fae5 !important;
    }
    .table-status-cbt thead tr:nth-child(2) th.col-reset-waktu,
    .table-status-cbt thead tr:nth-child(2) th.col-reset-izin,
    .table-status-cbt thead tr:nth-child(2) th.col-force,
    .table-status-cbt thead tr:nth-child(2) th.col-ulang {
        background: #fff7f8 !important;
        color: #e11d48 !important;
        border-bottom: 1px solid #ffe4e6 !important;
    }
    /* Pemisah vertikal antar sub-kolom */
    .table-status-cbt thead tr:nth-child(2) th.col-fouls,
    .table-status-cbt thead tr:nth-child(2) th.col-reset-waktu,
    .table-status-cbt thead tr:nth-child(2) th.col-reset-izin,
    .table-status-cbt thead tr:nth-child(2) th.col-force,
    .table-status-cbt thead tr:nth-child(2) th.col-ulang {
        border-left: 1px solid #f1f5f9;
    }

    /* ── Tombol refresh otomatis ─────────────────────────────────────── */
    .btn-auto-cbt {
        height: 38px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        font-weight: 600;
        font-size: .78rem;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 12px;
        transition: all .2s;
        flex: 0 0 auto;
    }
    .btn-auto-cbt .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #cbd5e1;
        transition: background .2s;
    }
    .btn-auto-cbt:hover { border-color: #c7d2fe; color: #4338ca; }
    .btn-auto-cbt.active { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    .btn-auto-cbt.active .dot {
        background: #4338ca;
        animation: ls-blink 1.4s ease-in-out infinite;
    }

    @media (max-width: 575.98px) {
        .btn-auto-cbt { padding: 0 10px; font-size: .75rem; }
    }

    /* ── Shell tabel: area scroll horizontal terkontrol ──────────────── */
    .cbt-table-shell {
        position: relative;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }
    .cbt-table-body { position: relative; }
    .cbt-table-scroll {
        overflow: auto;
        /* Tinggi tabel mengikuti viewport → monitoring lebih maximal */
        max-height: max(340px, calc(100vh - 300px));
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
    }
    .cbt-table-scroll:focus-visible {
        outline: 2px solid var(--cbt-primary);
        outline-offset: -2px;
    }
    .cbt-table-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
    .cbt-table-scroll::-webkit-scrollbar-track { background: transparent; }
    .cbt-table-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 20px;
        border: 3px solid #fff;
    }
    .cbt-table-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

    /* Indikator tepi kanan: pudar putih halus (bukan bayangan gelap) */
    .cbt-scroll-fade {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 26px;
        pointer-events: none;
        opacity: 0;
        transition: opacity .22s ease;
        z-index: 18;
    }
    .cbt-scroll-fade.show { opacity: 1; }
    .cbt-scroll-fade-right {
        right: 0;
        background: linear-gradient(to left, rgba(255, 255, 255, .95), rgba(255, 255, 255, 0));
    }

    /* Toolbar di atas tabel (petunjuk + tombol geser) */
    .cbt-table-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        font-size: .78rem;
        color: #64748b;
    }
    .cbt-scroll-hint {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 5px 12px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-weight: 600;
        min-width: 0;
    }
    .cbt-scroll-hint i { animation: cbt-nudge 1.6s ease-in-out infinite; }
    @keyframes cbt-nudge {
        0%, 100% { transform: translateX(0); }
        50%      { transform: translateX(4px); }
    }
    .cbt-scroll-nav { display: flex; gap: 6px; flex: 0 0 auto; }
    .btn-scroll-nav {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all .2s;
    }
    .btn-scroll-nav:hover:not(:disabled) {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4f46e5;
    }
    .btn-scroll-nav:disabled { opacity: .45; cursor: not-allowed; }

    /* ── Mode layar penuh: tabel monitor memenuhi layar ─────────────── */
    /* Ancestor memakai overflow-x: clip → dinonaktifkan saat mode ini
       agar shell fixed benar-benar menempel penuh ke viewport. */
    body.cbt-full-on .app-content,
    body.cbt-full-on .app-content-header { overflow: visible; }
    .cbt-table-shell.cbt-mode-full {
        position: fixed;
        inset: 0;
        z-index: 1055;
        border: 0;
        border-radius: 0;
        box-shadow: 0 0 0 100vmax rgba(15, 23, 42, .45);
    }
    .cbt-table-shell.cbt-mode-full .cbt-table-scroll {
        max-height: calc(100vh - 122px);
    }
    .cbt-table-shell.cbt-mode-full .cbt-table-toolbar {
        padding: 12px 16px;
        background: #fff;
    }
    .btn-scroll-nav.active {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4338ca;
    }

    /* Remedial Badge */
    .badge-remedial {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 20px;
        letter-spacing: 0.04em;
        animation: pulse-remedial 1.8s infinite;
        margin-top: 3px;
        white-space: nowrap;
    }
    @keyframes pulse-remedial {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5); }
        50%       { box-shadow: 0 0 0 5px rgba(245, 158, 11, 0); }
    }

    /* Live Stats — minimal, no external dependency */
    .ls-modal-body { background: #f8fafc; }
    .ls-modal-footer { background: #f8fafc; }
    #live-stats-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px 8px;
        margin-bottom: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    #live-stats-inner { overflow-x: auto; }
    .ls-dot {
        display: inline-block;
        width: 7px; height: 7px;
        background: #10b981;
        border-radius: 50%;
        animation: ls-blink 1.4s ease-in-out infinite;
        vertical-align: middle;
    }
    @keyframes ls-blink {
        0%,100%{ opacity:1; transform:scale(1); }
        50%     { opacity:.3; transform:scale(.7); }
    }

    /* ═══════════════════════════════════════════════════════════════════
       DARK MODE
       Diaktifkan dengan atribut data-bs-theme="dark" pada elemen <html>
       (Bootstrap 5.3 otomatis menyesuaikan komponen inti: card, form, tabel)
       Blok ini hanya menimpa warna hard-coded milik halaman ini.
       ═══════════════════════════════════════════════════════════════════ */
    [data-bs-theme="dark"] {
        --cbt-secondary: #94a3b8;
        --cbt-border: #334155;
        --cbt-bg-light: #1e293b;
    }

    /* ── Shell aplikasi & komponen global (mengatasi warna hard-coded) ── */
    [data-bs-theme="dark"] body,
    [data-bs-theme="dark"] .app-wrapper,
    [data-bs-theme="dark"] .app-main { background-color: #111827 !important; color: #e2e8f0; }
    [data-bs-theme="dark"] .app-content,
    [data-bs-theme="dark"] .app-content-header { background-color: transparent; }
    [data-bs-theme="dark"] .app-header {
        background-color: rgba(17, 24, 39, .92) !important;
        border-bottom-color: rgba(255, 255, 255, .08) !important;
    }
    [data-bs-theme="dark"] .app-header .nav-link { color: #cbd5e1 !important; }
    [data-bs-theme="dark"] .app-header .nav-link:hover { color: #a5b4fc !important; }
    [data-bs-theme="dark"] .app-footer {
        background-color: #111827 !important;
        border-top-color: #334155 !important;
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .app-footer strong { color: #cbd5e1 !important; }

    /* .bg-light memakai !important di Bootstrap → harus ditimpa */
    [data-bs-theme="dark"] .bg-light {
        background-color: #1f2937 !important;
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .bg-white {
        background-color: #1f2937 !important;
        color: #e2e8f0 !important;
    }

    [data-bs-theme="dark"] .card { background: #1f2937; color: #e2e8f0; }
    [data-bs-theme="dark"] .card-header { border-bottom-color: #334155; }
    [data-bs-theme="dark"] .card-title { color: #e2e8f0; }
    [data-bs-theme="dark"] .card-footer { border-top-color: #334155; background: transparent; }
    [data-bs-theme="dark"] .text-dark { color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .border-bottom,
    [data-bs-theme="dark"] .border-top { border-color: #334155 !important; }

    /* Tabel global */
    [data-bs-theme="dark"] .table {
        --bs-table-bg: transparent;
        --bs-table-color: #e2e8f0;
        --bs-table-striped-bg: #1a2333;
        --bs-table-hover-bg: #1f2937;
    }
    [data-bs-theme="dark"] .table td { color: #cbd5e1; }
    [data-bs-theme="dark"] .table thead th {
        background-color: #1e293b;
        color: #94a3b8;
        border-bottom-color: #334155;
    }

    /* Form global */
    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select,
    [data-bs-theme="dark"] .select2-container--bootstrap4 .select2-selection {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .form-control:focus,
    [data-bs-theme="dark"] .form-select:focus,
    [data-bs-theme="dark"] .select2-container--bootstrap4.select2-container--focus .select2-selection {
        background-color: #0f172a !important;
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .form-control::placeholder { color: #64748b !important; }
    [data-bs-theme="dark"] .input-group-text {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .input-group > .input-group-text.bg-light {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }

    /* Dropdown, list-group, pagination, breadcrumb */
    [data-bs-theme="dark"] .dropdown-menu { background-color: #1f2937; border-color: #334155; }
    [data-bs-theme="dark"] .dropdown-item { color: #cbd5e1; }
    [data-bs-theme="dark"] .dropdown-item:hover,
    [data-bs-theme="dark"] .dropdown-item:focus { background-color: #263244; color: #fff; }
    [data-bs-theme="dark"] .dropdown-divider { border-color: #334155; }
    [data-bs-theme="dark"] .list-group-item { background-color: #1f2937; border-color: #334155; color: #e2e8f0; }
    [data-bs-theme="dark"] .page-link { background-color: #1f2937; border-color: #334155; color: #cbd5e1; }
    [data-bs-theme="dark"] .page-item.active .page-link { background-color: #4f46e5; border-color: #4f46e5; }
    [data-bs-theme="dark"] .breadcrumb-item,
    [data-bs-theme="dark"] .breadcrumb-item a { color: #94a3b8; }
    [data-bs-theme="dark"] .breadcrumb-item.active { color: #e2e8f0; }
    [data-bs-theme="dark"] .breadcrumb-item + .breadcrumb-item::before { color: #64748b; }

    /* Notifikasi (toast & sweetalert) */
    [data-bs-theme="dark"] .jq-toast-wrap .jq-toast { background: #1f2937; color: #e2e8f0; }
    [data-bs-theme="dark"] .swal2-popup { background: #1f2937; color: #e2e8f0; }
    [data-bs-theme="dark"] .swal2-title,
    [data-bs-theme="dark"] .swal2-html-container { color: #e2e8f0; }

    /* Permukaan (kartu, panel, tabel) */
    [data-bs-theme="dark"] .cbt-card-flat { background: #1f2937; border-color: #334155; }
    [data-bs-theme="dark"] .cbt-filter-bar { background: #1f2937; border-color: #334155; }
    [data-bs-theme="dark"] .cbt-table-shell { background: #1f2937; border-color: #334155; }
    [data-bs-theme="dark"] .cbt-table-toolbar { background: #1f2937; border-bottom-color: #334155; color: #94a3b8; }
    [data-bs-theme="dark"] .cbt-table-shell.cbt-mode-full .cbt-table-toolbar { background: #1f2937; }

    /* Form & kontrol */
    [data-bs-theme="dark"] .form-select-cbt,
    [data-bs-theme="dark"] .form-control-cbt {
        background-color: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
    }
    [data-bs-theme="dark"] .form-select-cbt:focus,
    [data-bs-theme="dark"] .form-control-cbt:focus { background-color: #0f172a; color: #e2e8f0; }
    [data-bs-theme="dark"] .search-input-cbt {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #e2e8f0;
    }
    [data-bs-theme="dark"] .search-input-cbt::placeholder { color: #64748b; }
    [data-bs-theme="dark"] .filter-label { color: #94a3b8; }
    [data-bs-theme="dark"] .switch-cbt { background: #1e293b; }
    [data-bs-theme="dark"] .switch-slider { background-color: #475569; }
    [data-bs-theme="dark"] .switch-label { color: #94a3b8; }
    [data-bs-theme="dark"] .switch-active { color: #a5b4fc; }

    /* ── Select2 ─────────────────────────────────────────────────────── */
    /* Catatan: theme global memakai `color:#212529 !important` pada
       .select2-selection__rendered → harus ditimpa agar teks select
       (Hari Pelaksanaan / Ruang / Sesi) tetap terbaca. Selain itu
       dropdown Select2 di-append ke <body>, jadi tidak bisa di-target
       lewat .select2-container. */
    [data-bs-theme="dark"] .select2-container .select2-selection--single,
    [data-bs-theme="dark"] .select2-container .select2-selection--multiple {
        color: #e2e8f0 !important;
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .select2-container .select2-selection--single .select2-selection__rendered,
    [data-bs-theme="dark"] .select2-container .select2-selection--multiple .select2-selection__rendered {
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .select2-container .select2-selection__placeholder { color: #94a3b8 !important; }
    [data-bs-theme="dark"] .select2-container .select2-selection__clear { color: #94a3b8 !important; }
    /* State fokus/terbuka: theme global memaksa latar putih + teks gelap */
    [data-bs-theme="dark"] .select2-container--bootstrap4.select2-container--focus .select2-selection,
    [data-bs-theme="dark"] .select2-container--bootstrap4.select2-container--open .select2-selection {
        background-color: #1e293b !important;
        color: #e2e8f0 !important;
        border-color: #6366f1 !important;
    }
    [data-bs-theme="dark"] .select2-container .select2-selection--single .select2-selection__arrow b {
        border-color: #94a3b8 transparent transparent transparent;
    }

    /* Dropdown Select2 (berada di <body>) */
    [data-bs-theme="dark"] .select2-dropdown {
        background-color: #1f2937 !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .select2-dropdown .select2-results__option { color: #cbd5e1 !important; }
    [data-bs-theme="dark"] .select2-dropdown .select2-results__option--highlighted[aria-selected],
    [data-bs-theme="dark"] .select2-dropdown .select2-results__option[aria-selected="true"] {
        background-color: #334155 !important;
        color: #ffffff !important;
    }
    [data-bs-theme="dark"] .select2-dropdown .select2-results__option[aria-disabled="true"] { color: #64748b !important; }
    [data-bs-theme="dark"] .select2-dropdown .select2-results__group { color: #94a3b8 !important; }
    [data-bs-theme="dark"] .select2-dropdown .select2-search__field {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }
    [data-bs-theme="dark"] .select2-dropdown .select2-search__field::placeholder { color: #64748b !important; }

    /* Panduan */
    [data-bs-theme="dark"] .guide-box { background-color: rgba(14, 165, 233, .12); border-left-color: #0ea5e9; }
    [data-bs-theme="dark"] .guide-title { color: #7dd3fc; }
    [data-bs-theme="dark"] .guide-toggle:hover { background: rgba(14, 165, 233, .12); }
    [data-bs-theme="dark"] .guide-item { color: #cbd5e1; }
    [data-bs-theme="dark"] .guide-item b { color: #f1f5f9; }
    [data-bs-theme="dark"] .guide-item::before { color: #7dd3fc; }

    /* Action bar */
    [data-bs-theme="dark"] .btn-auto-cbt { background: #1f2937; border-color: #334155; color: #94a3b8; }
    [data-bs-theme="dark"] .btn-auto-cbt:hover { border-color: #4338ca; color: #c7d2fe; }
    [data-bs-theme="dark"] .btn-auto-cbt.active { background: #312e81; border-color: #4338ca; color: #e0e7ff; }
    [data-bs-theme="dark"] .btn-auto-cbt .dot { background: #64748b; }
    [data-bs-theme="dark"] .btn-auto-cbt.active .dot { background: #c7d2fe; }

    /* Tabel: teks & garis */
    [data-bs-theme="dark"] .table-status-cbt { color: #e2e8f0; }
    [data-bs-theme="dark"] .table-status-cbt tbody td { border-bottom-color: #263244; }
    [data-bs-theme="dark"] .table-status-cbt tbody tr:hover > td { box-shadow: inset 0 0 0 9999px rgba(99, 102, 241, .14); }
    [data-bs-theme="dark"] .cbt-row-odd     { background-color: #1a2333; }
    [data-bs-theme="dark"] .cbt-row-even    { background-color: #16202e; }
    [data-bs-theme="dark"] .cbt-row-blocked { background-color: #3b1418; }
    [data-bs-theme="dark"] .table-status-cbt tbody td.col-sticky-1,
    [data-bs-theme="dark"] .table-status-cbt tbody td.col-sticky-2 {
        background-image: linear-gradient(rgba(99, 102, 241, .10), rgba(99, 102, 241, .10));
    }
    [data-bs-theme="dark"] .table-status-cbt .col-sticky-2 { border-right-color: #475569; }
    [data-bs-theme="dark"] .table-status-cbt .col-reset-waktu { border-left-color: #334155; }

    /* Header tabel: tint gelap per kelompok kolom */
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.col-sticky-1,
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.col-sticky-2 {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-bottom: 2px solid #334155 !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.col-sticky-2 {
        border-right: 2px solid #475569 !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.th-group-jadwal {
        background: #1e1b4b !important;
        color: #a5b4fc !important;
        border-bottom: 2px solid #4338ca !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.th-group-status {
        background: #052e16 !important;
        color: #6ee7b7 !important;
        border-bottom: 2px solid #059669 !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.th-group-aksi {
        background: #4c0519 !important;
        color: #fda4af !important;
        border-bottom: 2px solid #e11d48 !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.th-group-status,
    [data-bs-theme="dark"] .table-status-cbt thead tr:first-child th.th-group-aksi { border-left-color: #334155; }

    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th {
        background: #172033 !important;
        color: #94a3b8 !important;
        border-bottom: 1px solid #334155 !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-mulai,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-durasi,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-fouls {
        background: #0b2b20 !important;
        color: #34d399 !important;
        border-bottom: 1px solid #065f46 !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-reset-waktu,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-reset-izin,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-force,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-ulang {
        background: #2b0f18 !important;
        color: #fb7185 !important;
        border-bottom: 1px solid #9f1239 !important;
    }
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-fouls,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-reset-waktu,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-reset-izin,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-force,
    [data-bs-theme="dark"] .table-status-cbt thead tr:nth-child(2) th.col-ulang { border-left-color: #334155; }

    /* Elemen sel (chip, pill, badge, tombol) */
    [data-bs-theme="dark"] .cbt-row-no { background: #334155; color: #cbd5e1; }
    [data-bs-theme="dark"] .cbt-mapel { color: #e2e8f0; }
    [data-bs-theme="dark"] .cbt-mulai-run { color: #a5b4fc; }
    [data-bs-theme="dark"] .cbt-kode-badge { background: #1e293b; color: #cbd5e1; border-color: #334155; }
    [data-bs-theme="dark"] .cbt-avail.ok  { background: #052e16; color: #34d399; }
    [data-bs-theme="dark"] .cbt-avail.end { background: #1e293b; color: #64748b; }
    [data-bs-theme="dark"] .cbt-pill-run    { background: #1e1b4b; color: #a5b4fc; border-color: #312e81; }
    [data-bs-theme="dark"] .cbt-pill-done   { background: #052e16; color: #34d399; border-color: #065f46; }
    [data-bs-theme="dark"] .cbt-pill-none   { background: #1e293b; color: #94a3b8; border-color: #334155; }
    [data-bs-theme="dark"] .cbt-pill-blokir { background: #450a0a; color: #fca5a5; border-color: #7f1d1d; }
    [data-bs-theme="dark"] .cbt-chip-agama { background: #1e293b; color: #cbd5e1; border-color: #334155; }
    [data-bs-theme="dark"] .cbt-chip-kelas { background: #1e1b4b; color: #a5b4fc; border-color: #312e81; }
    [data-bs-theme="dark"] .cbt-chip-ruang { background: #3b2f08; color: #fcd34d; border-color: #78350f; }
    [data-bs-theme="dark"] .cbt-chip-sesi  { background: #0c4a6e; color: #7dd3fc; border-color: #075985; }
    [data-bs-theme="dark"] .cbt-badge-info { background: #3b2f08 !important; color: #fcd34d !important; }
    [data-bs-theme="dark"] .table-status-cbt .col-sticky-2 .badge.bg-success { background: #052e16 !important; color: #4ade80 !important; }
    [data-bs-theme="dark"] .table-status-cbt .col-sticky-2 .badge.bg-danger  { background: #450a0a !important; color: #fca5a5 !important; }
    [data-bs-theme="dark"] .table-status-cbt .col-sticky-2 .badge.bg-warning { background: #3b2f08 !important; color: #fcd34d !important; }
    [data-bs-theme="dark"] .table-status-cbt .col-sticky-2 .badge.bg-primary { background: #1e1b4b !important; color: #a5b4fc !important; }
    [data-bs-theme="dark"] .table-status-cbt .col-fouls .btn.btn-danger { background: #450a0a; border-color: #7f1d1d; color: #f87171; }
    [data-bs-theme="dark"] .table-status-cbt .col-fouls .btn.btn-danger:hover { background: #4c0f0f; border-color: #991b1b; }
    [data-bs-theme="dark"] .table-status-cbt .col-fouls .btn.btn-outline-secondary { background: #1e293b; border-color: #334155; color: #94a3b8; }
    [data-bs-theme="dark"] .table-status-cbt .col-reset-waktu .btn-warning { background: #3b2f08; border-color: #78350f; color: #fcd34d; }
    [data-bs-theme="dark"] .table-status-cbt .col-reset-waktu .btn-warning:hover { background: #422006; border-color: #92400e; }
    [data-bs-theme="dark"] .table-status-cbt .col-reset-waktu .btn-danger { background: #450a0a; border-color: #7f1d1d; color: #f87171; }
    [data-bs-theme="dark"] .table-status-cbt .col-reset-waktu .btn-danger:hover { background: #4c0f0f; border-color: #991b1b; }
    [data-bs-theme="dark"] .table-status-cbt .col-reset-waktu .btn-success { background: #052e16; border-color: #065f46; color: #4ade80; }
    [data-bs-theme="dark"] .table-status-cbt .col-reset-waktu .btn-success:hover { background: #064e3b; border-color: #047857; }

    /* Empty state */
    [data-bs-theme="dark"] .cbt-empty-state .bi { color: #64748b; }
    [data-bs-theme="dark"] .cbt-empty-title { color: #e2e8f0; }
    [data-bs-theme="dark"] .cbt-empty-hint { color: #94a3b8; }

    /* Scroll & navigasi tabel */
    [data-bs-theme="dark"] .cbt-scroll-hint { background: #1e1b4b; color: #a5b4fc; }
    [data-bs-theme="dark"] .btn-scroll-nav { background: #1f2937; border-color: #334155; color: #94a3b8; }
    [data-bs-theme="dark"] .btn-scroll-nav:hover:not(:disabled) { background: #1e1b4b; border-color: #4338ca; color: #a5b4fc; }
    [data-bs-theme="dark"] .btn-scroll-nav.active { background: #1e1b4b; border-color: #4338ca; color: #a5b4fc; }
    [data-bs-theme="dark"] .cbt-scroll-fade-right { background: linear-gradient(to left, rgba(31, 41, 55, .95), rgba(31, 41, 55, 0)); }
    [data-bs-theme="dark"] .cbt-table-scroll::-webkit-scrollbar-thumb { background: #334155; border-color: #1f2937; }
    [data-bs-theme="dark"] .cbt-table-scroll::-webkit-scrollbar-thumb:hover { background: #475569; }

    /* Tombol token (header) */
    [data-bs-theme="dark"] .btn-token-cbt { background: #1e1b4b; border-color: #4338ca; color: #a5b4fc; }
    [data-bs-theme="dark"] .btn-token-cbt:hover { background: #312e81; border-color: #6366f1; color: #e0e7ff; }
    [data-bs-theme="dark"] .btn-token-cbt code#btn-token-value { color: #c7d2fe; }
    [data-bs-theme="dark"] .btn-token-cbt #btn-token-count { background: #6366f1; color: #e0e7ff; }

    /* Modal token */
    [data-bs-theme="dark"] .token-modal-body,
    [data-bs-theme="dark"] .token-modal-footer { background: #111827; }
    [data-bs-theme="dark"] .token-panel { background: #1f2937; border-color: #334155; box-shadow: none; }
    [data-bs-theme="dark"] .token-modal-label { color: #94a3b8; }
    [data-bs-theme="dark"] .token-panel-head { border-bottom-color: #334155; }
    [data-bs-theme="dark"] .token-modal-value { background: #1e1b4b; border-color: #4338ca; }
    [data-bs-theme="dark"] .tok-digit { background: #0f172a; border-color: #4338ca; color: #c7d2fe; }
    [data-bs-theme="dark"] .tok-plain { color: #c7d2fe; }
    [data-bs-theme="dark"] .tok-muted { color: #64748b; }
    [data-bs-theme="dark"] .token-chip { background: #1f2937; border-color: #334155; }
    [data-bs-theme="dark"] .token-chip:hover { border-color: #4338ca; }
    [data-bs-theme="dark"] .token-chip .chip-ico { background: #1e1b4b; color: #a5b4fc; }
    [data-bs-theme="dark"] .token-chip .chip-kode { color: #94a3b8; }
    [data-bs-theme="dark"] .token-chip .chip-val { color: #c7d2fe; }
    [data-bs-theme="dark"] .token-chip .btn-token-copy { background: #1e1b4b; color: #a5b4fc; }
    [data-bs-theme="dark"] .token-chip .btn-token-copy:hover { background: #312e81; }
    [data-bs-theme="dark"] .btn-token-copyall { background: #1e1b4b; border-color: #4338ca; color: #a5b4fc; }
    [data-bs-theme="dark"] .btn-token-copyall:hover { background: #312e81; border-color: #6366f1; color: #e0e7ff; }
    [data-bs-theme="dark"] .token-list-scroll::-webkit-scrollbar-thumb { background: #334155; }
    [data-bs-theme="dark"] .token-empty { color: #64748b; }

    /* Modal Live Stats */
    [data-bs-theme="dark"] .ls-modal-body,
    [data-bs-theme="dark"] .ls-modal-footer { background: #111827; }
    [data-bs-theme="dark"] .ls-modal-body .table,
    [data-bs-theme="dark"] .ls-modal-body .card { --bs-card-bg: #1f2937; }

    /* Modal info error & pelanggaran (Bootstrap sudah dark, rapikan tabel) */
    [data-bs-theme="dark"] .modal-content { background-color: #1f2937; border-color: #334155; }
    [data-bs-theme="dark"] .modal-header {
        background-color: #1f2937;
        border-color: #334155;
    }
    [data-bs-theme="dark"] .modal-footer {
        background-color: #1f2937;
        border-color: #334155;
    }
    [data-bs-theme="dark"] .modal-body .table { --bs-table-bg: transparent; --bs-table-color: #e2e8f0; }

    /* ── KONTRAS TEKS: pastikan semua tulisan terbaca di latar gelap ── */
    /* Utility warna Bootstrap tetap gelap di mode gelap → diganti versi terang */
    [data-bs-theme="dark"] .text-success   { color: #4ade80 !important; }
    [data-bs-theme="dark"] .text-danger    { color: #f87171 !important; }
    [data-bs-theme="dark"] .text-warning   { color: #fbbf24 !important; }
    [data-bs-theme="dark"] .text-info      { color: #38bdf8 !important; }
    [data-bs-theme="dark"] .text-primary   { color: #a5b4fc !important; }
    [data-bs-theme="dark"] .text-secondary { color: #94a3b8 !important; }
    [data-bs-theme="dark"] .text-muted     { color: #94a3b8 !important; }
    [data-bs-theme="dark"] .text-body      { color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .text-body-secondary { color: #94a3b8 !important; }

    /* Badge: tema memaksa teks putih → buat jadi tint lembut + teks terang */
    [data-bs-theme="dark"] .badge.bg-success   { background-color: #052e16 !important; color: #4ade80 !important; }
    [data-bs-theme="dark"] .badge.bg-danger    { background-color: #450a0a !important; color: #fca5a5 !important; }
    [data-bs-theme="dark"] .badge.bg-warning   { background-color: #3b2f08 !important; color: #fcd34d !important; }
    [data-bs-theme="dark"] .badge.bg-info      { background-color: #0c4a6e !important; color: #7dd3fc !important; }
    [data-bs-theme="dark"] .badge.bg-primary   { background-color: #1e1b4b !important; color: #a5b4fc !important; }
    [data-bs-theme="dark"] .badge.bg-secondary { background-color: #334155 !important; color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .badge.bg-light     { background-color: #334155 !important; color: #e2e8f0 !important; }
    [data-bs-theme="dark"] .badge.bg-light.border {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }

    /* Badge remedial: putih di atas amber terlalu kontras rendah */
    [data-bs-theme="dark"] .badge-remedial {
        background: linear-gradient(135deg, #a16207, #854d0e);
        color: #fef3c7;
    }

    /* Tombol terang (Tutup, dst) disoftkan agar tidak menyilaukan */
    [data-bs-theme="dark"] .btn-light {
        background-color: #1f2937;
        border-color: #334155;
        color: #e2e8f0;
    }
    [data-bs-theme="dark"] .btn-light:hover,
    [data-bs-theme="dark"] .btn-light:focus {
        background-color: #263244;
        border-color: #475569;
        color: #fff;
    }
    [data-bs-theme="dark"] .btn-outline-secondary {
        background-color: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }
    [data-bs-theme="dark"] .btn-outline-secondary:hover {
        background-color: #334155;
        border-color: #475569;
        color: #fff;
    }

    /* Teks pada modal & panel */
    [data-bs-theme="dark"] .modal-content { color: #e2e8f0; }
    [data-bs-theme="dark"] .modal-title { color: #e2e8f0; }
    [data-bs-theme="dark"] .form-label { color: #cbd5e1; }
    [data-bs-theme="dark"] .guide-chevron { color: #7dd3fc; }
    [data-bs-theme="dark"] .table-bordered > :not(caption) > * > * { border-color: #334155; }
    [data-bs-theme="dark"] .cbt-card-flat,
    [data-bs-theme="dark"] .cbt-table-shell,
    [data-bs-theme="dark"] .cbt-filter-bar { color: #e2e8f0; }
    [data-bs-theme="dark"] .cbt-empty-hint,
    [data-bs-theme="dark"] .cbt-scroll-hint { color: #94a3b8; }
    [data-bs-theme="dark"] .cbt-mapel-sub { color: inherit; }
    [data-bs-theme="dark"] .cbt-siswa-cell { color: #e2e8f0; }

    /* Toast jquery (selector yang benar) */
    [data-bs-theme="dark"] .jq-toast-wrap .jq-toast-single { background-color: #1f2937; color: #e2e8f0; }
    [data-bs-theme="dark"] .jq-toast-wrap .jq-toast-single h2 { color: #e2e8f0; }

    /* Navbar: badge putih + text-dark → akan jadi putih di atas putih */
    [data-bs-theme="dark"] .badge.bg-white {
        background-color: #1f2937 !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .user-footer .bg-teal {
        background-color: #0f766e !important;
        color: #fff !important;
    }
    [data-bs-theme="dark"] .user-footer .bg-red {
        background-color: #b91c1c !important;
        color: #fff !important;
    }
    [data-bs-theme="dark"] .user-footer .text-dark { color: #fcd34d !important; }
    [data-bs-theme="dark"] .user-header.bg-dark { background-color: #111827 !important; }

    /* Header gradasi modal token harus tetap gradasi di mode gelap */
    [data-bs-theme="dark"] .token-modal-head { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    [data-bs-theme="dark"] .token-modal-body,
    [data-bs-theme="dark"] .token-modal-footer,
    [data-bs-theme="dark"] .ls-modal-body,
    [data-bs-theme="dark"] .ls-modal-footer { background: #111827; }

    /* ── Header mode layar penuh (hanya tampil saat fullscreen) ───────── */
    .cbt-fs-header {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 12px 16px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }
    .cbt-table-shell.cbt-mode-full .cbt-fs-header { display: flex; }
    .cbt-fs-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .cbt-fs-ico {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        color: #fff;
        font-size: 1.1rem;
        flex: 0 0 auto;
        box-shadow: 0 6px 14px -6px rgba(79, 70, 229, .6);
    }
    .cbt-fs-heading {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        line-height: 1.2;
        color: #1e293b;
    }
    .cbt-fs-sub { display: block; font-size: .72rem; color: #94a3b8; }
    .cbt-fs-tools { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .cbt-fs-label {
        font-size: .68rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin: 0;
    }
    .cbt-fs-select { width: auto; min-width: 170px; }
    .cbt-fs-search { width: 230px; flex: 0 1 230px; }

    [data-bs-theme="dark"] .cbt-fs-header { background: #1f2937; border-bottom-color: #334155; }
    [data-bs-theme="dark"] .cbt-fs-heading { color: #e2e8f0; }
    [data-bs-theme="dark"] .cbt-fs-label { color: #94a3b8; }
    /* Overlay loading AdminLTE memakai bg putih → soften di mode gelap */
    [data-bs-theme="dark"] .overlay { background: rgba(17, 24, 39, .65); }

    /* ── Cegah halaman ikut ter-scroll horizontal ─────────────────────── */
    .app-content, .app-content-header { overflow-x: clip; }

    /* ── Tampilan responsif (tablet & ponsel) ────────────────────────── */
    @media (max-width: 991.98px) {
        .cbt-table-scroll { max-height: max(320px, calc(100vh - 250px)); }
    }

    @media (max-width: 767.98px) {
        .guide-list { grid-template-columns: 1fr; }
        .action-bar-left,
        .action-bar-right {
            flex: 1 1 100%;
            max-width: 100%;
        }
        .action-bar-right { flex-wrap: wrap; }
        .action-bar-right .search-container { flex: 1 1 100%; }
        .action-bar-right .btn-cbt-status {
            flex: 1 1 100%;
            justify-content: center;
        }
        .action-bar-left > .form-select-cbt { min-width: 0; }
        .cbt-table-toolbar { font-size: .72rem; padding: 8px 10px; }
        .btn-scroll-nav { width: 28px; height: 28px; }
        /* Header layar penuh: kontrol menumpuk rapi di layar kecil */
        .cbt-fs-header { padding: 10px 12px; gap: 10px; }
        .cbt-fs-tools { width: 100%; }
        .cbt-fs-select { flex: 1 1 130px; min-width: 0; }
        .cbt-fs-search { flex: 1 1 140px; width: auto; }
    }

    @media (max-width: 575.98px) {
        .cbt-table-scroll { max-height: max(300px, calc(100vh - 230px)); }
        .cbt-scroll-hint span.cbt-hint-long { display: none; }
        /* Kolom lengket dibuat lebih ramping di layar ponsel */
        .table-status-cbt .col-sticky-1 { width: 44px; min-width: 44px; }
        .table-status-cbt .col-sticky-2 {
            left: 44px;
            width: 128px;
            min-width: 128px;
        }
        .table-status-cbt .col-sticky-2 > .cbt-siswa-cell { max-width: 118px; }
    }
</style>

<script>
    /* Terapkan tema tersimpan SEBELUM body dirender → tidak ada kedipan tema.
       Default: ikuti preferensi sistem operating device. */
    (function () {
        try {
            var saved = localStorage.getItem('cbt-theme');
            var prefersDark = window.matchMedia
                && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        } catch (e) { /* localStorage diblokir → pakai tema bawaan */ }
    })();
</script>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-display text-primary me-2"></i>
                    <?= esc($judul ?? 'Status Ujian Siswa') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Status Siswa') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-default cbt-card-flat mb-4">
            <div class="card-header">
                <h6 class="card-title"><?= $subjudul ?? 'Status Siswa' ?></h6>
                <div class="card-tools d-flex align-items-center gap-2 ms-auto">
                    <button type="button" id="btn-theme-toggle" class="btn btn-theme-toggle btn-sm"
                        title="Ganti tema terang/gelap" aria-label="Ganti tema terang/gelap">
                        <i class="bi bi-moon-stars-fill" id="icon-theme-toggle"></i>
                    </button>
                    <button type="button" id="btn-token-cbt" class="btn btn-token-cbt btn-sm" data-bs-toggle="modal"
                        data-bs-target="#tokenModal" title="Lihat token ujian">
                        <i class="bi bi-key-fill"></i>
                        <span class="token-btn-suffix">Token Ujian</span>
                        <code id="btn-token-value" class="d-none">------</code>
                        <span class="badge rounded-pill d-none" id="btn-token-count">0</span>
                    </button>
                    <button class="btn btn-danger btn-sm btn-toggle" data-bs-toggle="modal"
                        data-bs-target="#infoModal"><i class="bi bi-info-circle me-1"></i> Info Error
                    </button>
                </div>
            </div>
            <div class="card-body cbt-card-body">

                <!-- Filter ringkas satu baris → hemat tinggi untuk tabel monitoring -->
                <div class="cbt-filter-bar">
                    <div class="cbt-filter-item">
                        <span class="filter-label">Hari Pelaksanaan</span>
                        <select name="tgl" id="tgl" class="form-select-cbt w-100"></select>
                    </div>

                    <!-- ── LIVE STATS PANEL ─────────────────────────────────────────── -->
                    <!-- Dipindah ke modal — tombol ada di action-bar -->
                    <!-- ── END LIVE STATS PANEL ──────────────────────────────────────── -->

                    <div class="cbt-filter-item cbt-filter-toggle">
                        <span class="filter-label">Filter Berdasarkan</span>
                        <label class="switch-cbt" for="printby">
                            <span class="switch-label" id="label-ruang">Ruang</span>
                            <div class="switch-item">
                                <input type="checkbox" id="printby">
                                <span class="switch-slider"></span>
                            </div>
                            <span class="switch-label" id="label-kelas">Kelas</span>
                        </label>
                    </div>

                    <div class="cbt-filter-item by-ruang">
                        <span class="filter-label">Pilih Ruang</span>
                        <select name="ruang" id="ruang" class="form-select-cbt w-100">
                            <option value='' selected='selected'>Pilih Ruang</option>
                        </select>
                    </div>

                    <div class="cbt-filter-item by-ruang">
                        <span class="filter-label">Pilih Sesi</span>
                        <select name="sesi" id="sesi" class="form-select-cbt w-100">
                            <option value='' selected='selected'>Pilih Sesi</option>
                        </select>
                    </div>

                    <div class="cbt-filter-item by-kelas d-none" id="by-kelas">
                        <span class="filter-label">Pilih Kelas</span>
                        <select name="kelas" id="kelas" class="form-select-cbt w-100">
                            <option value='' selected='selected'>Pilih Kelas</option>
                        </select>
                    </div>
                </div>

                <div class="guide-box d-none" id="info">
                    <button type="button" class="guide-toggle" data-bs-toggle="collapse" data-bs-target="#guideList"
                        aria-expanded="false" aria-controls="guideList">
                        <span class="guide-title">
                            <i class="bi bi-info-circle-fill"></i> Panduan Monitoring Ujian
                        </span>
                        <i class="bi bi-chevron-down guide-chevron"></i>
                    </button>
                    <div class="collapse" id="guideList">
                        <ul class="guide-list">
                        <li class="guide-item">
                            <span><b>REFRESH:</b> Gunakan tombol <span class="badge bg-primary"><i class="bi bi-arrow-repeat me-1"></i> Refresh</span> untuk memperbarui data realtime.</span>
                        </li>
                        <li class="guide-item">
                            <span><b>PAKSA SELESAI:</b> Klik untuk menyudahi ujian siswa secara manual.</span>
                        </li>
                        <li class="guide-item">
                            <span><b>RESET WAKTU:</b> Dilakukan jika siswa logout sebelum selesai dan sisa waktu masih ada.</span>
                        </li>
                        <li class="guide-item">
                            <span><b>ULANG:</b> Menghapus semua progres dan memulai ujian dari awal.</span>
                        </li>
                        <li class="guide-item">
                            <span><b>RESET IZIN:</b> Memungkinkan siswa login kembali di perangkat yang berbeda.</span>
                        </li>
                        <li class="guide-item">
                            <span><b>TERAPKAN AKSI:</b> Tombol <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Terapkan Aksi</span> untuk memproses pilihan massal.</span>
                        </li>
                        <li class="guide-item">
                            <span><b>REMEDIAL:</b> Badge <span class="badge-remedial"><i class="bi bi-arrow-repeat"></i> Remedial</span> muncul jika siswa sedang dalam sesi remedial.</span>
                        </li>
                        </ul>
                    </div>
                </div>

                <div class="action-bar d-none" id="action-controls">
                    <div class="action-bar-left">
                        <select name="jadwal-by" id="jadwal-by" class="form-select-cbt flex-grow-1"></select>
                        <button type="button" class="btn-refresh-cbt" id="btn-refresh-main" onclick="refreshStatus()" title="Refresh">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                        <button type="button" id="btn-live-stats"
                                class="btn-refresh-cbt position-relative"
                                data-bs-toggle="modal" data-bs-target="#liveStatsModal"
                                title="Live Progress"
                                style="background:#10b981;">
                            <i class="bi bi-activity"></i>
                            <span id="ls-badge-dot"
                                  style="position:absolute;top:6px;right:6px;width:7px;height:7px;
                                         border-radius:50%;background:#fff;display:none;
                                         animation:ls-blink 1.4s ease-in-out infinite;"></span>
                        </button>
                        <button type="button" id="btn-auto-refresh"
                                class="btn-auto-cbt"
                                title="Refresh otomatis setiap 30 detik">
                            <span class="dot"></span> Auto 30s
                        </button>
                    </div>

                    <div class="action-bar-right">
                        <div class="search-container">
                            <i class="bi bi-search search-icon"></i>
                            <input type="search" id="cari-status-siswa" class="form-control form-control-cbt search-input-cbt w-100" placeholder="Cari Siswa...">
                        </div>
                        <button type="button" class="btn btn-cbt-status btn-cbt-success" onclick="terapkanAksi()">
                            <i class="bi bi-check-circle"></i> Terapkan Aksi
                        </button>
                    </div>
                </div>

                <!-- Tabel status: area scroll horizontal terkontrol (sticky header + 2 kolom lengket) -->
                <div class="cbt-table-shell">
                    <!-- Header khusus mode layar penuh: judul + pilih jadwal + refresh + cari siswa -->
                    <div class="cbt-fs-header" id="cbt-fs-header">
                        <div class="cbt-fs-title">
                            <span class="cbt-fs-ico" aria-hidden="true"><i class="bi bi-display"></i></span>
                            <div>
                                <h5 class="cbt-fs-heading">Monitoring Status Ujian</h5>
                                <span class="cbt-fs-sub" id="cbt-fs-sub">Status ujian real-time</span>
                            </div>
                        </div>
                        <div class="cbt-fs-tools">
                            <label class="cbt-fs-label" for="jadwal-by-fs">Jadwal</label>
                            <select id="jadwal-by-fs" class="form-select-cbt cbt-fs-select"></select>
                            <button type="button" class="btn-refresh-cbt" id="cbt-fs-refresh" title="Refresh" aria-label="Refresh data">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button type="button" class="btn btn-cbt-status btn-cbt-success" id="cbt-fs-apply"
                                title="Terapkan aksi yang dipilih">
                                <i class="bi bi-check-circle"></i> Terapkan Aksi
                            </button>
                            <div class="search-container cbt-fs-search">
                                <i class="bi bi-search search-icon"></i>
                                <input type="search" id="cari-fs" class="form-control form-control-cbt search-input-cbt w-100"
                                    placeholder="Cari Siswa...">
                            </div>
                        </div>
                    </div>
                    <div class="cbt-table-toolbar">
                        <div class="cbt-scroll-hint" id="cbt-scroll-hint">
                            <i class="bi bi-arrow-left-right"></i>
                            <span>Geser tabel ke samping <span class="cbt-hint-long">untuk melihat semua kolom</span></span>
                        </div>
                        <div class="cbt-scroll-nav">
                            <button type="button" class="btn-scroll-nav" id="cbt-scroll-left" title="Geser ke kiri" aria-label="Geser tabel ke kiri">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="btn-scroll-nav" id="cbt-scroll-right" title="Geser ke kanan" aria-label="Geser tabel ke kanan">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            <button type="button" class="btn-scroll-nav" id="cbt-fullscreen" title="Layar penuh" aria-label="Tabel layar penuh">
                                <i class="bi bi-arrows-fullscreen"></i>
                            </button>
                        </div>
                    </div>
                    <div class="cbt-table-body">
                        <div class="cbt-table-scroll" id="cbt-table-scroll" tabindex="0"
                             role="region" aria-label="Tabel status siswa, gunakan panah kiri dan kanan untuk menggeser">
                            <table class="table table-sm table-hover table-status-cbt" id="table-status">
                            </table>
                        </div>
                        <div class="cbt-scroll-fade cbt-scroll-fade-right" id="cbt-fade-right"></div>
                    </div>
                </div>
            </div>
            <div class="overlay d-none" id="loading">
                <button class="btn btn-primary" type="button" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                    Loading...
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Modal -->
<form id="reset">
    <?= csrf_field() ?>
    <div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xs" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetLabel">Reset Waktu Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" id="id_cbt_siswa" name="id_cbt_siswa" value="">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reset" id="reset1" value="1">
                            <label class="form-check-label" for="reset1">Reset Waktu dari awal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="reset" id="reset2" value="2">
                            <label class="form-check-label" for="reset2">Lanjutkan sisa waktu</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">OK</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">BATAL</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xs" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoLabel">Kode error siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table w-100">
                    <tr>
                        <td class="text-bold">001</td>
                        <td>:</td>
                        <td>Token salah atau token belum digenerate</td>
                    </tr>
                    <tr>
                        <td class="text-bold">002</td>
                        <td>:</td>
                        <td>Harus reset izin</td>
                    </tr>
                    <tr>
                        <td class="text-bold">003</td>
                        <td>:</td>
                        <td>Harus reset waktu</td>
                    </tr>
                    <tr>
                        <td class="text-bold">004</td>
                        <td>:</td>
                        <td>Soal belum dibuat atau belum dipilih</td>
                    </tr>
                    <tr>
                        <td class="text-bold">005</td>
                        <td>:</td>
                        <td>Siswa menggunakan browser yang tidak mendukung</td>
                    </tr>
                    <tr>
                        <td class="text-bold">006</td>
                        <td>:</td>
                        <td>Internet down atau error dari database/server</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Pelanggaran Modal -->
<div class="modal fade" id="pelanggaranModal" tabindex="-1" role="dialog" aria-labelledby="pelanggaranLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xs" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pelanggaranLabel">Pelanggaran Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table w-100 table-bordered">
                    <tr>
                        <th class="alert alert-primary">Jenis Pelanggaran</th>
                        <th class="alert alert-success">Jumlah</th>
                    </tr>
                    <tr>
                        <td class="alert alert-warning">Pelanggaran Ringan<ul>
                                <li>Memulai ujian dengan tampilan tidak fullscreen</li>
                            </ul>
                        </td>
                        <td class="text-bold align-middle text-center text-danger" id="jml-ringan"></td>
                    </tr>
                    <tr>
                        <td class="alert alert-danger">Pelanggaran Sedang<ul>
                                <li>Mencoba keluar dari fullscreen</li>
                            </ul>
                        </td>
                        <td class="text-bold align-middle text-center text-danger" id="jml-sedang"></td>
                    </tr>
                    <tr>
                        <td class="alert alert-danger">Pelanggaran Berat<ul>
                                <li>Mencoba membuka tab baru</li>
                                <li>Keluar dari aplikasi sebelum selesai</li>
                                <li>Me-minimize browser</li>
                            </ul>
                        </td>
                        <td class="text-bold align-middle text-center text-danger" id="jml-berat"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<form id="post_data"><?= csrf_field() ?></form>

<!-- Modal Token Ujian -->
<div class="modal fade" id="tokenModal" tabindex="-1" role="dialog" aria-labelledby="tokenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header token-modal-head">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="token-modal-ico" aria-hidden="true"><i class="bi bi-key-fill"></i></span>
                    <div class="min-w-0">
                        <h5 class="token-modal-title" id="tokenModalLabel">Token Ujian</h5>
                        <p class="token-modal-sub text-truncate" id="token-modal-sub">Token aktif hari ini</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto ms-md-0"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body token-modal-body">
                <!-- Tampilan token jadwal terpilih -->
                <div id="token-modal-single" class="d-none">
                    <div class="token-panel">
                        <div class="token-panel-head">
                            <span class="token-modal-label">
                                <i class="bi bi-book-fill"></i><span id="token-modal-mapel">TOKEN</span>
                            </span>
                            <span class="token-modal-label">
                                <i class="bi bi-calendar-check"></i>HARI INI
                            </span>
                        </div>
                        <div class="token-modal-value" id="token-display-wrap" aria-live="polite"></div>
                        <div class="token-single-foot">
                            <button type="button" id="copy-token-btn" class="btn d-none" onclick="copyMonitoringToken()">
                                <i class="bi bi-clipboard"></i> SALIN TOKEN
                            </button>
                            <div class="token-hint" id="token-single-hint">
                                <i class="bi bi-info-circle"></i>Bagikan token ini kepada siswa untuk memulai ujian.
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Daftar semua token (mode Semua Jadwal) -->
                <div id="token-modal-list" class="d-none">
                    <div class="token-panel">
                        <div class="token-panel-head">
                            <span class="token-modal-label">
                                <i class="bi bi-collection"></i>DAFTAR TOKEN UJIAN
                            </span>
                            <button type="button" id="btn-token-copyall" class="btn-token-copyall d-none"
                                    onclick="copyAllTokens()" title="Salin semua token">
                                <i class="bi bi-files"></i> SALIN SEMUA
                            </button>
                        </div>
                        <div id="token-list-all" class="token-list-scroll"><!-- Dinamis lewat JS --></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer token-modal-footer">
                <span class="token-hint">
                    <i class="bi bi-lightbulb"></i>Token aktif juga tampil di tombol header.
                </span>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn text-white border-0 btn-refresh-token-modal"
                            style="background:linear-gradient(135deg,#6366f1,#4f46e5);"
                            onclick="updateTokenBanner();">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Live Stats -->
<div class="modal fade" id="liveStatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0 px-4 pt-4"
                 style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-1">
                        <i class="bi bi-activity me-2"></i>Live Progress Ujian
                    </h5>
                    <p class="text-white opacity-75 mb-0" style="font-size:.8rem;">
                        Auto-refresh setiap 10 detik
                    </p>
                </div>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <span id="ls-modal-time"
                          style="font-size:.72rem;font-weight:600;color:rgba(255,255,255,.8);
                                 display:inline-flex;align-items:center;gap:6px;">
                        <span class="ls-dot" style="background:#fff;"></span> —
                    </span>
                    <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4 ls-modal-body">
                <div id="live-stats-inner">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                        <div style="font-size:.85rem;">Memuat data...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-3 ls-modal-footer">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold border"
                        data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn rounded-pill px-4 fw-semibold text-white border-0"
                        style="background:linear-gradient(135deg,#6366f1,#4f46e5);"
                        onclick="liveStatsEtag=null;fetchLiveStats();">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/findMyData.js"></script>
<script>
    var is_admin = "<?= isset($is_admin) && $is_admin ? '1' : '0' ?>";
    var is_pengawas = "<?= isset($is_pengawas) && $is_pengawas ? '1' : '0' ?>";
    var assigned_ruangs = <?= json_encode($assigned_ruangs ?? []) ?>;
    var assigned_sesis = <?= json_encode($assigned_sesis ?? []) ?>;
    var dnone = is_pengawas === '1' ? 'd-none' : '';
    var printBy = '1';
    var url = '';

    var kelas;
    var tglJadwal, ruang, sesi;
    let responseJadwal;
    let jadwalSelected = 'all';
    let allDataJadwal = {};

    /* Palet aksen baris tabel (nomor siswa) — versi gelap lebih terang */
    const SISWA_ACCENTS = ['#6366f1', '#10b981', '#f59e0b', '#0ea5e9', '#8b5cf6', '#ef4444'];
    const SISWA_ACCENTS_DARK = ['#a5b4fc', '#6ee7b7', '#fcd34d', '#7dd3fc', '#c4b5fd', '#fca5a5'];

    /* ── Tema terang / gelap ────────────────────────────────────────── */
    function currentTheme() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    }

    /** Sinkronkan ikon & judul tombol dengan tema aktif. */
    function syncThemeIcon(theme) {
        var isDark = theme === 'dark';
        $('#icon-theme-toggle').attr('class', isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill');
        $('#btn-theme-toggle')
            .attr('title', isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap')
            .attr('aria-label', isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
    }

    /** Terapkan tema + simpan pilihan user. */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        try { localStorage.setItem('cbt-theme', theme); } catch (e) { /* abaikan */ }
        syncThemeIcon(theme);
        // Render ulang Live Stats agar palet warna ikut tema
        if (lastLiveStats) renderLiveStats(lastLiveStats);
        // Samakan warna nomor baris tabel dengan tema baru
        refreshTableTheme();
    }

    /** Warnai ulang nomor baris tabel mengikuti tema aktif. */
    function refreshTableTheme() {
        const isDark = currentTheme() === 'dark';
        const palette = isDark ? SISWA_ACCENTS_DARK : SISWA_ACCENTS;
        const alpha = isDark ? '26' : '1A';

        $('#table-status tbody .cbt-row-no').each(function () {
            const $no = $(this);
            const idx = (parseInt($no.text(), 10) || 1) - 1;
            const accent = palette[idx % palette.length];
            $no.attr('style', 'background:' + accent + alpha + ';color:' + accent + ';');
        });
    }

    $(document).ready(function () {
        ajaxcsrf();

        // Terapkan tema sesuai yang sudah dipasang di <head>
        syncThemeIcon(currentTheme());
        $('#btn-theme-toggle').on('click', function () {
            applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });

        var opsiPrintBy = $("#printby");

        opsiPrintBy.change(function () {
            printBy = $(this).prop('checked') ? '2' : '1';
            if (printBy === '1') {
                $('#by-kelas').addClass('d-none');
                $('.by-ruang').removeClass('d-none');
                $('#label-ruang').addClass('switch-active');
                $('#label-kelas').removeClass('switch-active');
                if (ruang && sesi && tglJadwal) loadSiswaRuang(ruang, sesi, tglJadwal)
            } else {
                $('#by-kelas').removeClass('d-none');
                $('.by-ruang').addClass('d-none');
                $('#label-ruang').removeClass('switch-active');
                $('#label-kelas').addClass('switch-active');
                if (kelas && tglJadwal) loadSiswaKelas(kelas, tglJadwal)
            }
        });
        $('#label-ruang').addClass('switch-active');


        var idSiswa = '';
        var idCbtSiswa = '';
        $('#resetModal').on('show.bs.modal', function (e) {
            idSiswa = $(e.relatedTarget).data('siswa');
            idCbtSiswa = $(e.relatedTarget).data('cbt');
            $('#id_cbt_siswa').val(idCbtSiswa);
        });

        // Token modal: render ulang isi setiap kali dibuka
        $('#tokenModal').on('show.bs.modal', function () {
            updateTokenBanner();
        });

        $('#reset').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $('#resetModal').modal('hide');

            let formData = new FormData($('#reset')[0]);

            $.ajax({
                url: base_url + "cbtstatus/resettimer",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status) {
                        refreshStatus();
                        showSuccessToast('Berhasil mereset waktu ujian');
                    } else showDangerToast('tidak bisa mereset waktu');
                }, error: function (xhr, status, error) {
                    console.log('error');
                }
            });
        });

        $('#tgl').select2({ theme: 'bootstrap4', width: '100%' });
        $('#ruang').select2({ theme: 'bootstrap4', width: '100%' });
        $('#sesi').select2({ theme: 'bootstrap4', width: '100%' });
        $('#kelas').select2({ theme: 'bootstrap4', width: '100%' });

        var opsiJadwal = $("#jadwal-by");
        opsiJadwal.select2({ width: '100%', theme: 'bootstrap4' });
        opsiJadwal.change(function () {
            jadwalSelected = $(this).val();
            updateTokenBanner();
            syncFullscreenControls();
            updateFullscreenSubtitle();
            const jadwals = Array.isArray(responseJadwal?.jadwals) ? responseJadwal.jadwals : [];
            if (jadwalSelected === 'all') {
                createPreview({ ...responseJadwal, jadwals: jadwals }, true);
                return;
            }
            const filter = jadwals.filter(jad => jad.id_jadwal === jadwalSelected);
            const newData = { ...responseJadwal, jadwals: filter.length > 0 ? filter : [] };
            createPreview(newData, true)
        })

        $('#pelanggaranModal').on('show.bs.modal', function (e) {
            const ringan = $(e.relatedTarget).data('ringan');
            const sedang = $(e.relatedTarget).data('sedang');
            const berat = $(e.relatedTarget).data('berat');

            $(e.currentTarget).find('#jml-ringan').text(ringan + ' x');
            $(e.currentTarget).find('#jml-sedang').text(sedang + ' x');
            $(e.currentTarget).find('#jml-berat').text(berat + ' x');
        });

        $('#cari-status-siswa').on("input", function () {
            const searchTerm = $(this).val().toLowerCase();
            if (!responseJadwal) return;

            const result = simpleDataSearch(responseJadwal, {
                arrayKeys: ['jadwals', 'sesisiswa'],
                keys: ['siswa.nama', 'siswa.agama', 'peserta.nomor_peserta',
                    'ruang.kode_ruang', 'kelas.kode_kelas', 'sesi.kode_sesi',
                    'bank.bank_kode', 'bank.mapel.kode'],
            });
            let resName = result.search(searchTerm);
            if (resName.jadwals.length > 0 && resName.sesisiswa.length > 0) {
                const newData = { ...responseJadwal, jadwals: resName.jadwals, sesisiswa: resName.sesisiswa };
                createPreview(newData);
                return;
            }

            if (resName.sesisiswa.length > 0) {
                const newData = { ...responseJadwal, sesisiswa: resName.sesisiswa, jadwals: responseJadwal.jadwals };
                createPreview(newData);
                return;
            }

            if (resName.jadwals.length > 0) {
                const newData = { ...responseJadwal, jadwals: resName.jadwals, sesisiswa: responseJadwal.sesisiswa };
                createPreview(newData);
                return;
            }

            const newData = { ...responseJadwal, jadwals: [], sesisiswa: [] };
            createPreview(newData);
        });

        getAllJadwal();
        initTableScroll();

        // Placeholder tabel sebelum tanggal/ruang/sesi dipilih
        showTablePlaceholder(
            'Belum Ada Data',
            'Pilih tanggal, ruang & sesi di atas untuk menampilkan status siswa.',
            'bi-inboxes'
        );

        // ── Refresh otomatis tabel setiap 30 detik ──
        var autoTimer = null;
        var AUTO_INTERVAL = 30000;
        $('#btn-auto-refresh').on('click', function () {
            var $btn = $(this);
            if (autoTimer) {
                clearInterval(autoTimer);
                autoTimer = null;
                $btn.removeClass('active');
                return;
            }
            if (!url) {
                showWarningToast('Pilih tanggal, ruang & sesi terlebih dulu');
                return;
            }
            // Bukan via refresh manual: teks pencarian dipertahankan
            // Mode senyap → overlay tidak menutupi tabel saat monitoring
            autoTimer = setInterval(function () { refreshStatus(false, true); }, AUTO_INTERVAL);
            $btn.addClass('active');
            showSuccessToast('Refresh otomatis diaktifkan (setiap 30 detik)');
        });
    });

    /** Set subjudul modal token. */
    function setTokenLabel(text) {
        $('#token-modal-sub').text(text);
    }

    /** Escape karakter berbahaya untuk interpolasi HTML. */
    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, m =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])
        );
    }

    /** Salin nilai ke clipboard + notifikasi singkat. */
    function copyTokenValue(val, title) {
        navigator.clipboard.writeText(val);
        const single = /^[A-Za-z0-9]{4,8}$/.test(val);
        Swal.fire({
            icon: 'success',
            title: title || 'Tersalin',
            text: single ? 'Token: ' + val : '',
            html: single ? null : '<span style="font-size:.9rem;">Semua token hari ini berhasil disalin</span>',
            timer: 1200,
            showConfirmButton: false
        });
    }

    /** Token yang sedang tampil pada tampilan tunggal (untuk tombol salin). */
    let currentMonitoringToken = '';

    /** Render nilai token: kotak digit per karakter utk token 4–8 karakter, teks biasa selain itu. */
    function renderTokenValue(val) {
        const isToken = /^[A-Za-z0-9]{4,8}$/.test(val);
        currentMonitoringToken = isToken ? val : '';
        if (isToken) {
            $('#token-display-wrap').html(
                val.split('').map(ch => `<span class="tok-digit">${escHtml(ch)}</span>`).join('')
            );
        } else {
            $('#token-display-wrap').html(
                `<span class="tok-plain tok-muted">${escHtml(val)}</span>`
            );
        }
    }

    /** Set teks petunjuk di bawah token (single view). */
    function setTokenHint(mode) {
        const hints = {
            aktif: ['bi bi-info-circle', 'Bagikan token ini kepada siswa untuk memulai ujian.'],
            tanpa: ['bi bi-slash-circle', 'Jadwal ini tidak menggunakan token.'],
            kosong: ['bi bi-key-off', 'Token belum digenerate. Cek pengaturan jadwal ujian.']
        };
        const h = hints[mode] || hints.kosong;
        $('#token-single-hint').html(`<i class="bi ${h[0]}"></i>${h[1]}`);
    }

    function updateTokenBanner() {
        const selectedVal = $('#jadwal-by').val() || 'all';
        const $btnVal = $('#btn-token-value');
        const $btnCount = $('#btn-token-count');

        if (!responseJadwal || !responseJadwal.jadwals || responseJadwal.jadwals.length === 0) {
            setTokenLabel('TIDAK ADA JADWAL AKTIF HARI INI');
            $('#token-modal-single').addClass('d-none');
            $('#token-modal-list').removeClass('d-none');
            $('#token-list-all').html(
                '<div class="token-empty"><i class="bi bi-calendar-x"></i><span>Tidak ada jadwal aktif hari ini</span></div>'
            );
            $btnVal.addClass('d-none');
            $btnCount.addClass('d-none');
            return;
        }

        const jadwalsBerToken = responseJadwal.jadwals.filter(j => j && j.token == '1' && j.token_value);

        if (selectedVal === 'all') {
            setTokenLabel('DAFTAR TOKEN UJIAN HARI INI');

            let htmlList = '<div class="token-chip-grid">';
            jadwalsBerToken.forEach(jad => {
                htmlList += `
                    <div class="token-chip">
                        <span class="chip-ico"><i class="bi bi-key-fill"></i></span>
                        <span class="chip-kode">${escHtml(jad.bank?.bank_kode ?? '-')}</span>
                        <code class="chip-val">${escHtml(jad.token_value)}</code>
                        <button class="btn-token-copy" data-token="${escHtml(jad.token_value)}" title="Salin Token">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>`;
            });
            htmlList += '</div>';

            $('#token-modal-single').addClass('d-none');
            $('#token-modal-list').removeClass('d-none');
            $('#token-list-all').html(htmlList);

            // Tampilkan tombol "Salin Semua" hanya jika ada token
            $('#btn-token-copyall').toggleClass('d-none', jadwalsBerToken.length === 0);
            if (jadwalsBerToken.length === 0) {
                $('#token-list-all').html(
                    '<div class="token-empty"><i class="bi bi-key-off"></i><span>Tidak ada token aktif hari ini</span></div>'
                );
            }

            // Tombol header: tampilkan jumlah token aktif
            $btnVal.addClass('d-none');
            $btnCount.removeClass('d-none').text(jadwalsBerToken.length);
        } else {
            const jad = responseJadwal.jadwals.find(j => j.id_jadwal?.toString() === selectedVal?.toString());
            $('#token-modal-list').addClass('d-none');
            $('#token-modal-single').removeClass('d-none');
            const kodeMapel = jad?.bank?.bank_kode ?? '';
            $('#token-modal-mapel').text(kodeMapel ? 'MAPEL: ' + kodeMapel : 'TOKEN UJIAN');

            setTokenLabel(`TOKEN UJIAN: ${kodeMapel}`);
            if (jad && jad.token == '1' && jad.token_value) {
                renderTokenValue(jad.token_value);
                $('#copy-token-btn').removeClass('d-none');
                setTokenHint('aktif');
                // Token aktif langsung terlihat sekilas di tombol header
                $btnVal.removeClass('d-none').text(jad.token_value);
                $btnCount.addClass('d-none');
            } else {
                if (jad && jad.token == '0') {
                    renderTokenValue('TANPA TOKEN');
                    setTokenHint('tanpa');
                    $btnVal.removeClass('d-none').text('TANPA TOKEN');
                } else {
                    renderTokenValue('------');
                    setTokenHint('kosong');
                    $btnVal.removeClass('d-none').text('------');
                }
                $('#copy-token-btn').addClass('d-none');
                $btnCount.addClass('d-none');
            }
        }
    }

    /** Salin token yang sedang tampil di modal (tampilan tunggal). */
    function copyMonitoringToken() {
        if (currentMonitoringToken) copyTokenValue(currentMonitoringToken);
    }

    /** Salin semua token hari ini (format: KODE MAPEL: TOKEN). */
    function copyAllTokens() {
        const lines = [];
        if (Array.isArray(responseJadwal?.jadwals)) {
            responseJadwal.jadwals.forEach(j => {
                if (j && j.token == '1' && j.token_value) {
                    lines.push(`${j.bank?.bank_kode ?? '-'}: ${j.token_value}`);
                }
            });
        }
        if (lines.length === 0) {
            showWarningToast('Tidak ada token untuk disalin');
            return;
        }
        copyTokenValue(lines.join('\n'), 'Semua Token Tersalin');
    }

    // Salin per-chip: delegasi agar aman utk konten dinamis
    $(document).on('click', '.btn-token-copy', function () {
        copyTokenValue($(this).data('token'));
    });


    $(document).on('click', '.toggle-block', function (e) {
        e.preventDefault();
        const me = $(this);
        const action = me.data('action');
        const idSiswa = me.data('siswa');
        const nama = me.data('nama');
        const idCbt = me.data('cbt');
        const idJadwal = me.data('jadwal');

        let itemJadwal = {
            reset: [],
            force: [],
            ulang: [],
            blokir: [],
            unblokir: []
        };

        let payload = {
            id_jadwal: idJadwal,
            id_cbt_siswa: [idCbt]
        };

        let confirmText = '';
        if (action === 'blokir') {
            itemJadwal.blokir.push(payload);
            confirmText = 'Siswa ' + nama + ' akan diblokir dan tidak bisa melanjutkan ujian.';
        } else {
            itemJadwal.unblokir.push(payload);
            confirmText = 'Akses ujian siswa ' + nama + ' akan dibuka kembali.';
        }

        let formData = new FormData($('#post_data')[0]);
        formData.append("data", JSON.stringify(itemJadwal));

        Swal.fire({
            title: action === 'blokir' ? "Blokir Siswa?" : "Buka Blokir?",
            text: confirmText,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: action === 'blokir' ? "#d33" : "#3085d6",
            cancelButtonColor: "#757575",
            confirmButtonText: "Ya, Lanjutkan"
        }).then(result => {
            if (result.value) {
                $.ajax({
                    url: base_url + "cbtstatus/applyaction",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        showSuccessToast(action === 'blokir' ? 'Siswa berhasil diblokir' : 'Blokir siswa dibuka');
                        refreshStatus();
                    }, error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        showDangerToast('Gagal mengubah status blokir');
                    }
                });
            }
        });
    });

    function terapkanAksi() {
        const $rows = $('#table-status').find('tr:visible'), headers = $rows.splice(0, 2);
        let itemJadwal = {
            reset: [],
            force: [],
            ulang: [],
            blokir: [],
            unblokir: []
        }
        let jmlSiswaReset = [];
        let jmlSiswaForce = [];
        let jmlSiswaUlang = [];

        $rows.each((i, row) => {
            const $colReset = $(row).find('.input-reset');
            const $colForce = $(row).find('.input-force');
            const $colUlang = $(row).find('.input-ulang');

            if ($colReset.prop("checked") === true) {
                const jadwal_id = $colReset.parent().data('jadwal')
                const cbt_id = $colReset.parent().data('cbt')
                const siswa_id = $colForce.parent().data('siswa')
                if (!jmlSiswaReset.includes(siswa_id)) jmlSiswaReset.push(siswa_id)

                const item = itemJadwal.reset.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                if (item) {
                    item.id_cbt_siswa.push(cbt_id)
                } else {
                    let reset = {
                        id_jadwal: jadwal_id,
                        id_cbt_siswa: [cbt_id],
                    }
                    itemJadwal.reset.push(reset)
                }
            }
            if ($colForce.prop("checked") === true) {
                const cbt_id = $colForce.parent().data('cbt')
                const jadwal_id = $colForce.parent().data('jadwal')
                const siswa_id = $colForce.parent().data('siswa')
                if (!jmlSiswaForce.includes(siswa_id)) jmlSiswaForce.push(siswa_id)

                const item = itemJadwal.force.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                if (item) {
                    item.id_cbt_siswa.push(cbt_id)
                    item.id_siswa.push(siswa_id)
                } else {
                    let force = {
                        id_jadwal: jadwal_id,
                        id_cbt_siswa: [cbt_id],
                        id_siswa: [siswa_id]
                    }
                    itemJadwal.force.push(force)
                }
            }
            if ($colUlang.prop("checked") === true) {
                const jadwal_id = $colUlang.parent().data('jadwal')
                const cbt_id = $colUlang.parent().data('cbt')
                const siswa_id = $colUlang.parent().data('siswa')
                if (!jmlSiswaUlang.includes(siswa_id)) jmlSiswaUlang.push(siswa_id)

                const item = itemJadwal.ulang.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                if (item) {
                    item.id_cbt_siswa.push(cbt_id)
                    item.id_siswa.push(siswa_id)
                } else {
                    let ulang = {
                        id_jadwal: jadwal_id,
                        id_cbt_siswa: [cbt_id],
                        id_siswa: [siswa_id]
                    }
                    itemJadwal.ulang.push(ulang)
                }
            }
        })

        var jmlReset = jmlSiswaReset.length === 0 ? '' : '<b>' + jmlSiswaReset.length + '</b> siswa akan direset<br>';
        var jmlForce = jmlSiswaForce.length === 0 ? '' : '<b>' + jmlSiswaForce.length + '</b> siswa akan dipaksa selesai<br>';
        var jmlUlang = jmlSiswaUlang.length === 0 ? '' : '<b>' + jmlSiswaUlang.length + '</b> siswa akan mengulang ujian<br>';

        var resReset = jmlSiswaReset.length === 0 ? '' : '<b>' + jmlSiswaReset.length + '</b> siswa berhasil direset<br>';
        var resForce = jmlSiswaForce.length === 0 ? '' : '<b>' + jmlSiswaForce.length + '</b> siswa berhasil dipaksa selesai<br>';
        var resUlang = jmlSiswaUlang.length === 0 ? '' : '<b>' + jmlSiswaUlang.length + '</b> siswa harus mengulang ujian<br>';

        if (itemJadwal.reset.length === 0 && itemJadwal.force.length === 0 && itemJadwal.ulang.length === 0) {
            showWarningToast('Silahkan pilih siswa terlebih dulu');
            return;
        }

        let formData = new FormData($('#post_data')[0]);
        formData.append("data", JSON.stringify(itemJadwal));
        Swal.fire({
            title: "Terapkan Aksi",
            html: jmlReset + jmlForce + jmlUlang,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Apply"
        }).then(result => {
            if (result.value) {
                $.ajax({
                    url: base_url + "cbtstatus/applyaction",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        Swal.fire({
                            title: "Sukses",
                            html: resForce + resReset + resUlang,
                            icon: "success"
                        }).then(result => {
                            refreshStatus();
                        });
                    }, error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        Swal.fire({
                            "title": "Error",
                            "html": xhr.responseText,
                            "icon": "error"
                        });
                    }
                });
            }
        });
    }

    /** Set state memuat pada tombol refresh (ikon berputar + tombol nonaktif). */
    function setRefreshLoading(isLoading) {
        $('#btn-refresh-main, #cbt-fs-refresh')
            .toggleClass('is-loading', !!isLoading)
            .prop('disabled', !!isLoading);
    }

    function refreshStatus(clearSearch, silent) {
        if (clearSearch === undefined) clearSearch = true;
        if (clearSearch) {
            $('#cari-status-siswa').val('');
            $('#cari-fs').val('');
        }

        // Belum ada filter aktif → jangan pernah request ke URL kosong
        // (sebelumnya membuat browser memuat ulang halaman ini)
        if (!url) {
            showWarningToast('Pilih tanggal, ruang & sesi terlebih dahulu');
            return;
        }

        // Mode senyap: hanya ikon berputar, overlay tidak menutupi tabel
        if (!silent) $('#loading').removeClass('d-none');
        // Reset ETag agar live stats ikut refresh setelah aksi manual
        liveStatsEtag = null;
        setRefreshLoading(true);

        setTimeout(function () {
            $.ajax({
                type: "GET",
                url: url,
                dataType: "json",
                success: function (response) {
                    responseJadwal = response;
                    optionJadwal();
                },
                error: function (xhr, status, error) {
                    console.error('[cbtstatus] Refresh gagal:', xhr.responseText);
                    showDangerToast('Gagal memuat data. Periksa koneksi Anda.');
                },
                complete: function () {
                    $('#loading').addClass('d-none');
                    setRefreshLoading(false);
                }
            });
        }, 150);
    }

    function optionJadwal() {
        var opsiJadwal = $("#jadwal-by");
        opsiJadwal.html('')
        opsiJadwal.append(`<option value="all" ${jadwalSelected === 'all' ? 'selected' : ''}>Semua Jadwal</option>`);
        if (responseJadwal && responseJadwal.jadwals) {
            for (const jadwal of responseJadwal.jadwals) {
                if (jadwal) {
                    opsiJadwal.append(`<option value="${jadwal.id_jadwal}" ${jadwalSelected === jadwal.id_jadwal ? 'selected' : ''}>${jadwal.bank?.bank_kode}</option>`);
                }
            }
        }
        opsiJadwal.trigger('change');
        updateTokenBanner();
        // Samakan dengan kontrol header layar penuh
        syncFullscreenControls();
        updateFullscreenSubtitle();
    }

    /**
     * Bangun bagian thead tabel status.
     * @param {boolean} isEmpty true ketika tidak ada baris siswa (checkbox select-all disabled)
     */
    function buildTheadHead(isEmpty) {
        const disabled = isEmpty ? 'disabled' : '';

        // Sembunyikan checkbox select-all massal untuk pengawas
        const chkReset = is_pengawas === '1' ? '' : '<br><input id="input-reset-all" class="check" type="checkbox" ' + disabled + ' title="Pilih semua siswa">';
        const chkForce = is_pengawas === '1' ? '' : '<br><input id="input-force-all" class="check" type="checkbox" ' + disabled + ' title="Pilih semua siswa">';
        const chkUlang = is_pengawas === '1' ? '' : '<br><input id="input-ulang-all" class="check" type="checkbox" ' + disabled + ' title="Pilih semua siswa">';

        return '<thead>' +
            '<tr>' +
            '<th rowspan="2" class="text-center align-middle col-sticky-1">NO.</th>' +
            '<th rowspan="2" class="text-center align-middle col-sticky-2"><i class="bi bi-people-fill"></i>DATA SISWA</th>' +
            '<th rowspan="2" class="text-center align-middle th-group-jadwal col-mapel" ' +
            'title="Kode mapel, badge kode bank soal, dan indikator ketersediaan jadwal"><i class="bi bi-book-fill"></i>MAPEL</th>' +
            '<th colspan="3" class="text-center align-middle th-group-status"><i class="bi bi-activity"></i>STATUS UJIAN</th>' +
            '<th colspan="4" class="text-center align-middle th-group-aksi"><i class="bi bi-lightning-charge-fill"></i>AKSI</th>' +
            '</tr>' +
            '<tr>' +
            '<th class="text-center align-middle p-1 col-mulai">MULAI</th>' +
            '<th class="text-center align-middle col-durasi">DURASI</th>' +
            '<th class="text-center align-middle col-fouls" title="Jumlah pelanggaran selama ujian">PELANGGARAN</th>' +
            '<th class="text-center align-middle col-reset-waktu"><i class="bi bi-arrow-clockwise"></i>RESET<br/>WAKTU/BLOKIR</th>' +
            '<th class="text-center align-middle col-reset-izin">RESET<br/>IZIN' + chkReset + '</th>' +
            '<th class="text-center align-middle col-force">PAKSA<br/>SELESAI' + chkForce + '</th>' +
            '<th class="text-center align-middle col-ulang">ULANG<br/>UJIAN' + chkUlang + '</th>' +
            '</tr></thead><tbody>';
    }

    /** Baris kosong tabel dengan ikon + judul + petunjuk. */
    function buildEmptyRow(title, hint, icon) {
        return '<tr><td colspan="10" class="cbt-empty-cell">' +
            '<div class="cbt-empty-state">' +
            '<i class="bi ' + icon + '"></i>' +
            '<span class="cbt-empty-title">' + title + '</span>' +
            '<span class="cbt-empty-hint">' + hint + '</span>' +
            '</div></td></tr>';
    }

    /** Tampilkan placeholder tabel sebelum ada data (belum pilih tanggal/ruang/sesi). */
    function showTablePlaceholder(title, hint, icon) {
        $('#table-status').html(buildTheadHead(true) + buildEmptyRow(title, hint, icon) + '</tbody>');
        $('#loading').addClass('d-none');
        measureStickyHeader();
        updateScrollState();
    }

    function createPreview(data, isInit) {
        let tbody = buildTheadHead(data.is_empty);

        // Warna latar baris sesuai status ujian (satu baris = satu mapel)
        // Dipakai sebagai class agar otomatis menyesuaikan tema light/dark
        const STATUS_BG = {
            blokir: 'cbt-row-blocked' // diblokir — merah muda (status lain cukup lewat pill)
        };

        // Palet aksen berputar per siswa (blok baris tiap siswa diberi warna berbeda)
        // Versi gelap memakai aksen lebih terang agar tetap terbaca
        const isDarkTheme = currentTheme() === 'dark';
        const accentPalette = isDarkTheme ? SISWA_ACCENTS_DARK : SISWA_ACCENTS;

        const all_jadwals = (Array.isArray(data.jadwals) ? data.jadwals : []);
        if (Array.isArray(data.sesisiswa) && data.sesisiswa.length > 0) {
            // Urutkan berdasarkan NIS (fallback nama) — nomor peserta tidak lagi ditampilkan
            data.sesisiswa.sort((a, b) => {
                const na = a?.siswa?.nis || '', nb = b?.siswa?.nis || '';
                if (na && nb && na !== nb) return na.localeCompare(nb, undefined, { numeric: true });
                return (a?.siswa?.nama || '').localeCompare(b?.siswa?.nama || '');
            })
            let ind = 1;
            for (const sesiSiswa of data.sesisiswa) {
                // Blok warna per siswa: dasar berselang putih/biru muda + strip aksen berputar
                const accent = accentPalette[(ind - 1) % accentPalette.length];
                const rowCls = ind % 2 === 1 ? 'cbt-row-odd' : 'cbt-row-even';
                const accentAlpha = isDarkTheme ? '26' : '1A';

                const idSiswa = sesiSiswa?.siswa_id ?? 'N/A';
                var sesiLabel = sesiSiswa?.sesi?.kode_sesi ?? 'N/A';
                var ruangLabel = sesiSiswa?.ruang?.kode_ruang ?? 'N/A';
                var kelasLabel = sesiSiswa?.kelas?.kode_kelas ?? 'N/A';
                const nama = sesiSiswa?.siswa?.nama ?? '';
                const agama = sesiSiswa?.siswa?.agama ?? '';

                const bankSoals = all_jadwals.filter(jad =>
                    !(jad?.bank?.soal_agama != '-' && jad?.bank?.soal_agama != '0' && jad?.bank?.soal_agama != agama) &&
                    jad?.bank?.bank_kelas?.some(k => k?.kelas_id?.toString() == sesiSiswa?.kelas_id?.toString())
                );

                let rowsMapel = bankSoals.length || "1";
                if (bankSoals.length > 0) {
                    let indJadwal = 0;
                    bankSoals.sort((a, b) => a.jam_ke - b.jam_ke)
                    for (const bank of bankSoals) {
                        const akhirUjian = ((bank.tgl_selesai).split(' ')[0]).replaceAll('-', '')
                        const now = ((data.today).split(' ')[0]).replaceAll('-', '')
                        const tglBerkahir = now > akhirUjian
                        const tersediaTitle = tglBerkahir ? 'Sudah berakhir' : 'Masih tersedia';
                        const tersediaCls   = tglBerkahir ? 'end' : 'ok';
                        const tersediaIcon  = tglBerkahir ? 'bi-x-lg' : 'bi-check-lg';
                        const mapelNama = bank.bank?.mapel?.nama_mapel || bank.bank?.mapel?.kode || '-';
                        const bankKode  = bank.bank?.bank_kode || '';
                        let rowJadwal = '<td class="align-middle text-center col-mapel">' +
                            '<div class="cbt-mapel-cell">' +
                            '<span class="cbt-mapel" title="' + mapelNama + '">' + mapelNama + '</span>' +
                            '<div class="cbt-mapel-sub">' +
                            (bankKode ? '<span class="cbt-kode-badge" title="Kode bank soal: ' + bankKode + '">' + bankKode + '</span>' : '') +
                            '<span class="cbt-avail ' + tersediaCls + '" title="' + tersediaTitle + '" aria-label="' + tersediaTitle + '">' +
                            '<i class="bi ' + tersediaIcon + '"></i></span>' +
                            '</div></div></td>';

                        // Check if student has cbtsiswa data
                        const cbtSiswaArr = sesiSiswa?.cbtsiswa || [];
                        const cbtSiswa = Array.isArray(cbtSiswaArr)
                            ? cbtSiswaArr.find(cbt => cbt?.id_jadwal === bank?.id_jadwal)
                            : null;

                        // Warna baris: mengikuti warna blok siswa; hanya siswa diblokir
                        // yang tetap pakai warna status agar tidak disalahartikan
                        var statusCls = null;
                        if (cbtSiswa && cbtSiswa.status === '9') {
                            statusCls = STATUS_BG.blokir;
                        }

                        if (cbtSiswa) {
                            const hitungDurasi = kalkulasiWaktu(cbtSiswa)
                            const durasi = hitungDurasi.durasi || '0m';
                            var sudahSelesai = cbtSiswa.selesai || false;

                            // Deteksi mode remedial: remedial_attempt > 0 dan belum selesai
                            var remedialAttempt = parseInt(cbtSiswa.remedial_attempt || 0);
                            var sedangRemedial = remedialAttempt > 0 && !sudahSelesai;
                            var remedialBadge = sedangRemedial
                                ? '<span class="badge-remedial"><i class="bi bi-arrow-repeat"></i> Remedial ke-' + remedialAttempt + '</span>'
                                : '';

                            var disabledResetWaktu = !tglBerkahir && !sudahSelesai && cbtSiswa.reset_waktu === '0' ? '' : 'disabled';
                            var disabledResetIzin = !sudahSelesai && cbtSiswa.reset_izin != null && cbtSiswa.reset_izin === '0' ? '' : 'disabled';
                            var disabledSelesai = !sudahSelesai ? '' : 'disabled';
                            var disabledUlang = sudahSelesai ? '' : 'disabled';
                            var isBlocked = cbtSiswa.status === '9';
                            var belumMulai = !cbtSiswa.mulai;
                            var disabledBlokir = sudahSelesai || belumMulai ? 'disabled' : '';

                            // Pill status ujian: diblokir / selesai / belum mulai / sedang berjalan
                            var pillCls   = isBlocked ? 'cbt-pill-blokir' : sudahSelesai ? 'cbt-pill-done' : belumMulai ? 'cbt-pill-none' : 'cbt-pill-run';
                            var pillIcon  = isBlocked ? 'bi-slash-circle' : sudahSelesai ? 'bi-check-circle-fill' : belumMulai ? 'bi-moon-stars' : 'bi-hourglass-split';
                            var pillLabel = isBlocked ? 'Diblokir' : sudahSelesai ? (durasi || 'Selesai') : belumMulai ? 'Belum mulai' : (durasi || 'Berjalan');
                            var pillTitle = isBlocked ? 'Siswa diblokir pengawas' : sudahSelesai ? 'Sudah selesai mengerjakan' : belumMulai ? 'Sudah terdaftar tapi belum mulai ujian' : 'Sedang mengerjakan ujian';

                            var btnBlokir = isBlocked
                                ? '<button type="button" class="btn btn-success btn-sm toggle-block" data-action="unblokir" ' +
                                'data-siswa="' + idSiswa + '" data-nama="' + nama + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '" ' + disabledBlokir + ' data-toggle="tooltip" title="Buka Blokir"><i class="bi bi-unlock"></i></button>'
                                : '<button type="button" class="btn btn-danger btn-sm toggle-block" data-action="blokir" ' +
                                'data-siswa="' + idSiswa + '" data-nama="' + nama + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '" ' + disabledBlokir + ' data-toggle="tooltip" title="Blokir"><i class="bi bi-slash-circle"></i></button>';

                            const mulai = () => {
                                if (!bank?.tgl_mulai || !cbtSiswa?.mulai) return '-';
                                const siswaMulai = new Date(cbtSiswa?.mulai)
                                return `${zeroPad(siswaMulai.getHours(), 2)}:${zeroPad(siswaMulai.getMinutes(), 2)}`;
                            };

                            const pelanggaran = cbtSiswa.pelanggaran || {};
                            const totalPelanggaran = function () {
                                if (bank?.pelanggaran != '1') {
                                    return 0;
                                } else {
                                    return (pelanggaran?.ringan || 0) + (pelanggaran?.sedang || 0) + (pelanggaran?.berat || 0);
                                }
                            }
                            const btn = `<button class="btn ${bank?.pelanggaran != '1' ? 'btn-outline-secondary' : 'btn-danger'} btn-sm text-bold"
                                        data-bs-toggle="modal" data-bs-target="#pelanggaranModal"
                                        data-ringan="${pelanggaran?.ringan || 0}"
                                        data-sedang="${pelanggaran?.sedang || 0}"
                                        data-berat="${pelanggaran?.berat || 0}"
                                        title="Rincian pelanggaran siswa"
                                        ${bank?.pelanggaran != '1' ? 'disabled' : ''}>${totalPelanggaran()} x</button>`
                            // Kolom MULAI: tampilkan jam + badge remedial jika aktif
                            var mulaiColorCls = isBlocked ? 'text-danger'
                                : sudahSelesai ? 'text-success'
                                : belumMulai ? 'text-muted'
                                : 'cbt-mulai-run';
                            var mulaiCell = '<div class="d-flex flex-column align-items-center">' +
                                '<span class="fw-bold ' + mulaiColorCls + '">' + mulai() + '</span>' +
                                remedialBadge +
                                '</div>';
                            rowJadwal += '<td class="text-center align-middle col-mulai">' + mulaiCell + '</td>' +
                                '<td class="text-center align-middle col-durasi">' +
                                '<span class="cbt-time-pill ' + pillCls + '" title="' + pillTitle + '">' +
                                '<i class="bi ' + pillIcon + '"></i>' + pillLabel + '</span>' +
                                '</td>' +
                                '<td class="text-center align-middle col-fouls">' + btn + '</td>' +
                                '<td class="text-center align-middle col-reset-waktu">' +
                                '<div class="d-flex justify-content-center gap-1">' +
                                '<button type="button" class="btn btn-warning btn-sm" ' +
                                'data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" ' +
                                'data-bs-toggle="modal" data-bs-target="#resetModal" ' + disabledResetWaktu + ' data-toggle="tooltip" title="Reset Waktu"><i class="bi bi-arrow-clockwise"></i></button>' +
                                btnBlokir +
                                '</div>' +
                                '</td>' +
                                '<td class="text-center text-success align-middle col-reset-izin" data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '">' +
                                '<input class="check input-reset" type="checkbox" ' + disabledResetIzin + '>' +
                                '</td>' +
                                '<td class="text-center text-danger align-middle col-force" data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '">' +
                                '<input class="check input-force" type="checkbox" ' + disabledSelesai + '>' +
                                '</td>' +
                                '<td class="text-center text-danger align-middle col-ulang" data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '">' +
                                '<input class="check input-ulang" type="checkbox" ' + disabledUlang + '>' +
                                '</td>';
                        } else {
                            rowJadwal += '<td colspan="8" class="align-middle text-center"><span class="badge cbt-badge-info"><i class="bi bi-hourglass-split me-1"></i>Belum ujian</span></td>'
                        }

                        if (indJadwal === 0) {
                            tbody += '<tr class="' + (statusCls || rowCls) + '" data-siswa="' + idSiswa + '">' +
                                '<td rowspan="' + rowsMapel + '" class="text-center align-middle col-sticky-1" ' +
                                'style="box-shadow: inset 4px 0 0 0 ' + accent + ';">' +
                                '<span class="cbt-row-no" style="background:' + accent + accentAlpha + ';color:' + accent + ';">' + ind + '</span></td>' +
                                '<td rowspan="' + rowsMapel + '" class="align-middle col-sticky-2">' +
                                '<div class="d-flex flex-column cbt-siswa-cell">' +
                                '<span class="fw-bold">' + nama + '</span>' +
                                '<div class="d-flex flex-wrap mt-1 gap-1">';
                            if (agama) {
                                tbody += '<span class="cbt-chip cbt-chip-agama">' + agama + '</span>';
                            }
                            tbody += '<span class="cbt-chip cbt-chip-kelas" title="Kelas">' + kelasLabel + '</span>' +
                                '<span class="cbt-chip cbt-chip-ruang" title="Ruang">' + ruangLabel + '</span>' +
                                '<span class="cbt-chip cbt-chip-sesi" title="Sesi">' + sesiLabel + '</span>' +
                                '</div></div>' +
                                '</td>';
                            tbody += rowJadwal + '</tr>';
                            ind++
                        } else {
                            tbody += '<tr class="' + (statusCls || rowCls) + '">' + rowJadwal + '</tr>';
                        }
                        indJadwal++;
                    }
                }
            }
        } else {
            tbody += isInit
                ? buildEmptyRow('Tidak Ada Siswa Terdaftar', 'Belum ada siswa pada ruang/sesi ini untuk tanggal yang dipilih.', 'bi-person-x')
                : buildEmptyRow('Tidak Ada Hasil', 'Coba kata kunci pencarian yang lain.', 'bi-search');
        }

        tbody += '</tbody>';

        // Pertahankan posisi scroll horizontal saat tabel di-refresh
        const scrollEl = document.getElementById('cbt-table-scroll');
        const keepScroll = scrollEl ? scrollEl.scrollLeft : 0;

        $('#table-status').html(tbody);
        $('#info').removeClass('d-none');
        $('#action-controls').removeClass('d-none');
        $('#loading').addClass('d-none');

        // Ketinggian baris header pertama dipakai untuk sticky baris header kedua
        measureStickyHeader();
        if (scrollEl) scrollEl.scrollLeft = keepScroll;
        updateScrollState();


        // Checkbox select all handlers
        $("#input-reset-all").on("click", function () {
            $(".input-reset").each(function () {
                if (!$(this).prop('disabled')) {
                    this.checked = $("#input-reset-all").prop("checked");
                }
            });
        });

        $("#input-force-all").on("click", function () {
            $(".input-force").each(function () {
                if (!$(this).prop('disabled')) {
                    this.checked = $("#input-force-all").prop("checked");
                }
            });
        });

        $("#input-ulang-all").on("click", function () {
            $(".input-ulang").each(function () {
                if (!$(this).prop('disabled')) {
                    this.checked = $("#input-ulang-all").prop("checked");
                }
            });
        });
    }

    // ── SCROLL HORIZONTAL TABEL ────────────────────────────────────────────
    // Tabel monitoring punya 10 kolom sehingga membutuhkan area scroll
    // horizontal. Agar tetap nyaman:
    //   1. Header tabel menempel di atas (sticky) saat tabel digeser vertikal
    //   2. Kolom NO. & DATA SISWA menempel di kiri (sticky) saat digeser horizontal
    //   3. Indikator fade kiri/kanan + tombol geser sebagai petunjuk affordance

    function initTableScroll() {
        const $scroll = $('#cbt-table-scroll');
        if (!$scroll.length) return;

        $scroll.on('scroll', updateScrollState);

        $('#cbt-scroll-left').on('click', function () {
            $scroll[0].scrollBy({ left: -260, behavior: 'smooth' });
        });
        $('#cbt-scroll-right').on('click', function () {
            $scroll[0].scrollBy({ left: 260, behavior: 'smooth' });
        });

        // ── Mode layar penuh: tabel memenuhi layar untuk monitoring maksimal ──
        $('#cbt-fullscreen').on('click', function () {
            toggleFullscreenTable();
        });
        $(document).on('keydown', function (e) {
            // Esc keluar dari layar penuh — kecuali sedang ada modal terbuka
            if (e.key === 'Escape'
                && $('.cbt-table-shell').hasClass('cbt-mode-full')
                && !document.querySelector('.modal.show')) {
                toggleFullscreenTable(false);
            }
        });

        // ── Kontrol header layar penuh (sinkron dengan kontrol utama) ──
        $('#jadwal-by-fs').on('change', function () {
            $('#jadwal-by').val($(this).val()).trigger('change');
        });
        $('#cari-fs').on('input', function () {
            const v = $(this).val();
            if ($('#cari-status-siswa').val() !== v) {
                $('#cari-status-siswa').val(v).trigger('input');
            }
        });
        $('#cari-status-siswa').on('input', function () {
            const v = $(this).val();
            if ($('#cari-fs').val() !== v) $('#cari-fs').val(v);
        });
        $('#cbt-fs-refresh').on('click', function () {
            refreshStatus();
        });
        $('#cbt-fs-apply').on('click', function () {
            terapkanAksi();
        });

        let resizeTimer;
        $(window).on('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                measureStickyHeader();
                updateScrollState();
            }, 150);
        });

        updateScrollState();
    }

    /** Aktifkan/nonaktifkan mode layar penuh pada shell tabel. */
    function toggleFullscreenTable(force) {
        const $shell = $('.cbt-table-shell');
        const $btn = $('#cbt-fullscreen');
        const isFull = force === undefined ? !$shell.hasClass('cbt-mode-full') : force;

        $shell.toggleClass('cbt-mode-full', isFull);
        $btn.toggleClass('active', isFull);
        $btn.attr('title', isFull ? 'Keluar layar penuh' : 'Layar penuh');
        $btn.find('i').attr('class', isFull ? 'bi bi-arrows-angle-contract' : 'bi bi-arrows-fullscreen');

        if (isFull) {
            // Kunci scroll halaman agar tabel jadi satu-satunya area yang bergeser
            $('body').addClass('cbt-full-on').css('overflow', 'hidden');
            syncFullscreenControls();
        } else {
            $('body').removeClass('cbt-full-on').css('overflow', '');
        }

        setTimeout(function () {
            measureStickyHeader();
            updateScrollState();
        }, 60);
    }

    /** Salin daftar jadwal & nilai pencarian ke kontrol header layar penuh. */
    function syncFullscreenControls() {
        const $main = $('#jadwal-by');
        const $fs = $('#jadwal-by-fs');
        if ($fs.length) {
            $fs.html($main.html() || '');
            $fs.val($main.val() || 'all');
        }
        const q = $('#cari-status-siswa').val() || '';
        if ($('#cari-fs').val() !== q) $('#cari-fs').val(q);
    }

    /** Tampilkan ringkasan jadwal aktif pada subjudul header layar penuh. */
    function updateFullscreenSubtitle() {
        const $sub = $('#cbt-fs-sub');
        if (!$sub.length) return;
        const val = $('#jadwal-by').val() || 'all';
        const opt = $('#jadwal-by option:selected').text() || '';
        $sub.text(val === 'all' ? 'Semua jadwal · status ujian real-time' : 'Jadwal ' + opt);
    }

    /** Hitung tinggi baris header pertama → dipakai sebagai offset sticky baris kedua. */
    function measureStickyHeader() {
        const $scroll = $('#cbt-table-scroll');
        const $row1 = $('#table-status thead tr:first-child th').first();
        const $row2 = $('#table-status thead tr:nth-child(2) th').first();
        if (!$scroll.length || !$row1.length || !$row2.length) return;

        const r1 = $row1[0].getBoundingClientRect();
        const r2 = $row2[0].getBoundingClientRect();
        const head = Math.max(0, Math.round(r2.top - r1.top));
        $scroll[0].style.setProperty('--cbt-head-h', head + 'px');
    }

    /** Perbarui indikator fade, status tombol geser, dan petunjuk. */
    function updateScrollState() {
        const el = document.getElementById('cbt-table-scroll');
        if (!el) return;

        const max = el.scrollWidth - el.clientWidth;
        const pos = el.scrollLeft;

        $('#cbt-fade-right').toggleClass('show', max > 4 && pos < max - 4);
        $('#cbt-scroll-left').prop('disabled', pos <= 4);
        $('#cbt-scroll-right').prop('disabled', max <= 4 || pos >= max - 4);
        $('#cbt-scroll-hint').toggleClass('d-none', max <= 4);
    }

    // ── LIVE STATS POLLING ─────────────────────────────────────────────────
    // Polling ringan setiap 10 detik ke /cbtstatus/live_stats
    // Menggunakan ETag sehingga jika data tidak berubah, browser terima 304
    // tanpa body → hemat bandwidth saat semua siswa sudah selesai.

    let liveStatsEtag   = null;
    let liveStatsTimer  = null;
    let liveStatsTgl    = null;
    let lastLiveStats   = null;

    function startLiveStats(tgl) {
        liveStatsTgl = tgl || liveStatsTgl;
        if (liveStatsTimer) clearInterval(liveStatsTimer);
        fetchLiveStats();
        liveStatsTimer = setInterval(fetchLiveStats, 10000);
    }

    function stopLiveStats() {
        if (liveStatsTimer) clearInterval(liveStatsTimer);
        liveStatsTimer = null;
        $('#ls-badge-dot').hide();
    }

    function fetchLiveStats() {
        if (!liveStatsTgl) return;
        const headers = {};
        if (liveStatsEtag) headers['If-None-Match'] = liveStatsEtag;

        $.ajax({
            url:     base_url + 'cbtstatus/live_stats?tgl=' + liveStatsTgl,
            type:    'GET',
            headers: headers,
            statusCode: {
                304: function () { /* data tidak berubah, tidak perlu update UI */ },
                200: function (data, _, xhr) {
                    liveStatsEtag = xhr.getResponseHeader('ETag');
                    lastLiveStats = data;
                    renderLiveStats(data);
                }
            },
            error: function () {
                // Gagal fetch — hentikan polling agar tidak spam
                console.warn('[LiveStats] Fetch failed, stopping poll.');
                stopLiveStats();
            }
        });
    }

    function renderLiveStats(data) {
        if (!data || !data.jadwals || data.jadwals.length === 0) {
            $('#live-stats-inner').html(
                '<div class="text-center text-muted py-5" style="font-size:.85rem;">' +
                '<i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;opacity:.3;"></i>' +
                'Tidak ada jadwal untuk tanggal ini.</div>'
            );
            $('#ls-badge-dot').hide();
            return;
        }

        const P = lsPalette();
        const now   = new Date();
        const hhmm  = now.getHours().toString().padStart(2,'0') + ':' +
                      now.getMinutes().toString().padStart(2,'0') + ':' +
                      now.getSeconds().toString().padStart(2,'0');

        // Helper: stat box
        const statBox = (num, lbl, bg, numColor) =>
            `<div style="flex:1;display:flex;flex-direction:column;align-items:center;
                         padding:8px 4px;border-radius:10px;background:${bg};min-width:50px;">
                <span style="font-size:1.4rem;font-weight:800;line-height:1;color:${numColor};">${num}</span>
                <span style="font-size:0.6rem;text-transform:uppercase;letter-spacing:.5px;
                             color:${P.muted};margin-top:3px;font-weight:600;">${lbl}</span>
             </div>`;

        // Helper: small badge
        const badge = (text, bg, color, border) =>
            `<span style="font-size:.6rem;font-weight:600;padding:2px 7px;border-radius:5px;
                          background:${bg};color:${color};border:1px solid ${border};
                          white-space:nowrap;">${text}</span>`;

        let rows = '';

        data.jadwals.forEach(function(j, idx) {
            const pct      = j.pct_selesai || 0;
            const opacity  = j.expired ? '.45' : '1';
            const borderT  = idx === 0 ? 'none' : P.border;

            // Badges
            const bToken   = j.use_token
                ? badge('<i class="bi bi-key-fill"></i> Token', P.tokenBg, P.tokenFg, P.tokenBorder)
                : '';
            const bExp     = j.expired
                ? badge('Expired', P.expBg, P.expFg, P.expBorder)
                : '';

            // Sisa waktu
            let sisaHtml = '';
            if (!j.expired && j.sedang > 0 && j.sisa_waktu_avg_detik !== null) {
                const m   = Math.floor(j.sisa_waktu_avg_detik / 60);
                const h   = Math.floor(m / 60);
                const txt = h > 0 ? `${h}j ${m % 60}m` : `${m}m`;
                sisaHtml = `<div style="flex:0 0 72px;text-align:center;padding-left:12px;">
                    <div style="font-size:.85rem;font-weight:700;color:${P.amber};">
                        <i class="bi bi-hourglass-split" style="font-size:.75rem;"></i> ${txt}
                    </div>
                    <div style="font-size:.58rem;color:${P.muted};text-transform:uppercase;letter-spacing:.5px;">sisa</div>
                </div>`;
            }

            // Blokir
            const bBlokir  = j.diblokir > 0
                ? statBox(j.diblokir, 'Blokir', P.blokirBg, P.blokirFg)
                : '';

            // Last done
            const lastDone = j.selesai_terakhir
                ? j.selesai_terakhir.substring(11, 16)
                : '—';

            rows += `
            <div style="display:flex;align-items:center;padding:12px 0;
                        border-top:${borderT};opacity:${opacity};gap:0;flex-wrap:wrap;">

                <!-- Nama Mapel (kiri) -->
                <div style="flex:0 0 195px;min-width:0;padding-right:16px;">
                    <div style="font-size:.88rem;font-weight:700;color:${P.mapelText};
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                         title="${j.nama_mapel}">${j.nama_mapel}</div>
                    <div style="display:flex;align-items:center;gap:4px;margin-top:4px;flex-wrap:wrap;">
                        ${badge(j.bank_kode,   P.chipBg, P.chipFg, P.chipBorder)}
                        ${badge('Jam '+(j.jam_ke||1), P.chipBg, P.chipFg, P.chipBorder)}
                        ${badge(j.durasi_ujian+'m', P.chipBg, P.chipFg, P.chipBorder)}
                        ${bToken}${bExp}
                    </div>
                </div>

                <!-- Stat boxes -->
                <div style="flex:1;display:flex;align-items:center;gap:4px;">
                    ${statBox(j.total,   'Total',   P.totalBg,   P.totalFg)}
                    ${statBox(j.sedang,  'Aktif',   P.aktifBg,  P.aktifFg)}
                    ${statBox(j.selesai, 'Selesai', P.selesaiBg, P.selesaiFg)}
                    ${statBox(j.belum,   'Belum',   P.belumBg,  P.belumFg)}
                    ${bBlokir}
                </div>

                <!-- Sisa waktu -->
                ${sisaHtml}

                <!-- Progress bar (kanan) -->
                <div style="flex:0 0 130px;padding-left:14px;">
                    <div style="height:7px;background:${P.track};border-radius:99px;overflow:hidden;">
                        <div style="height:100%;width:${pct}%;border-radius:99px;
                                    background:linear-gradient(90deg,#10b981,#059669);
                                    transition:width .7s ease;"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:5px;">
                        <span style="font-size:.7rem;font-weight:700;color:${P.green};">${pct}%</span>
                        <span style="font-size:.68rem;color:${P.muted};">${lastDone}</span>
                    </div>
                </div>
            </div>`;
        });

        const html = rows || '<div class="text-center text-muted py-4" style="font-size:.85rem;">Tidak ada jadwal hari ini.</div>';

        $('#live-stats-inner').html(html);

        // Update timestamp di header modal
        $('#ls-modal-time').html(`<span class="ls-dot" style="background:#fff;"></span> ${hhmm}`);

        // Tampilkan dot di tombol saat data sudah ada
        $('#ls-badge-dot').show();

        if (responseJadwal) updateTokenBanner();
    }
    /** Palet warna Live Stats yang menyesuaikan tema terang/gelap. */
    function lsPalette() {
        const dark = currentTheme() === 'dark';
        return dark ? {
            border: '1px solid #334155', mapelText: '#e2e8f0',
            chipBg: '#1e293b', chipFg: '#94a3b8', chipBorder: '#334155',
            tokenBg: '#1e1b4b', tokenFg: '#a5b4fc', tokenBorder: '#4338ca',
            expBg: '#450a0a', expFg: '#f87171', expBorder: '#7f1d1d',
            totalBg: '#1e293b', totalFg: '#cbd5e1',
            aktifBg: '#3b2f08', aktifFg: '#fbbf24',
            selesaiBg: '#052e16', selesaiFg: '#4ade80',
            belumBg: '#1e293b', belumFg: '#94a3b8',
            blokirBg: '#450a0a', blokirFg: '#f87171',
            track: '#334155', muted: '#94a3b8', amber: '#fbbf24', green: '#4ade80'
        } : {
            border: '1px solid #f1f5f9', mapelText: '#1e293b',
            chipBg: '#f1f5f9', chipFg: '#64748b', chipBorder: '#e2e8f0',
            tokenBg: '#eff6ff', tokenFg: '#3b82f6', tokenBorder: '#bfdbfe',
            expBg: '#fef2f2', expFg: '#ef4444', expBorder: '#fecaca',
            totalBg: '#f8fafc', totalFg: '#334155',
            aktifBg: '#fffbeb', aktifFg: '#f59e0b',
            selesaiBg: '#f0fdf4', selesaiFg: '#10b981',
            belumBg: '#f8fafc', belumFg: '#94a3b8',
            blokirBg: '#fef2f2', blokirFg: '#ef4444',
            track: '#e2e8f0', muted: '#94a3b8', amber: '#f59e0b', green: '#10b981'
        };
    }

    // ── END LIVE STATS POLLING ─────────────────────────────────────────────

    function getAllJadwal() {
        $.ajax({
            type: "GET",
            url: base_url + "cbtstatus/gettgljadwalujian",
            cache: false,
            success: function (response) {
                setOptionJadwal(response)
            }
        });
    }

    function setOptionJadwal(data) {
        allDataJadwal = data;
        allDataJadwal = data;

        var opsiTgl = $("#tgl");
        var opsiRuang = $("#ruang");
        var opsiSesi = $("#sesi");
        var opsiKelas = $("#kelas");
        opsiTgl.html('');
        opsiTgl.append('<option value="">Pilih Tanggal</option>');
        if (!data.jadwal || Object.keys(data.jadwal).length === 0) {
            opsiTgl.append('<option value="">Belum ada jadwal</option>');
            return
        }
        for (const id in data.jadwal) {
            opsiTgl.append('<option value="' + id + '">' + data.jadwal[id] + '</option>');
        }

        opsiTgl.change(function () {
            const selectedDate = $(this).val();

            // Mulai atau restart live stats polling saat tanggal dipilih
            if (selectedDate) {
                startLiveStats(selectedDate);
            } else {
                stopLiveStats();
            }

            // Populate Kelas
            opsiKelas.html('');
            opsiKelas.append('<option value="">Pilih Kelas</option>');
            const allKelas = data.kelas?.[selectedDate] || {};
            for (const idKls in allKelas) {
                opsiKelas.append('<option value="' + idKls + '">' + allKelas[idKls] + '</option>');
            }

            // Populate Ruang
            opsiRuang.html('');
            opsiRuang.append('<option value="">Pilih Ruang</option>');
            const allRuang = data.ruang?.[selectedDate] || {};
            for (const idr in allRuang) {
                // Filter for supervisor assignments
                if (is_pengawas === '1' && assigned_ruangs.length > 0) {
                    if (assigned_ruangs.includes(idr)) {
                        opsiRuang.append('<option value="' + idr + '">' + allRuang[idr] + '</option>');
                    }
                } else {
                    opsiRuang.append('<option value="' + idr + '">' + allRuang[idr] + '</option>');
                }
            }

            // Populate Sesi
            opsiSesi.html('');
            opsiSesi.append('<option value="">Pilih Sesi</option>');
            const allSesi = data.sesi?.[selectedDate] || {};
            for (const ids in allSesi) {
                // Filter for supervisor assignments
                if (is_pengawas === '1' && assigned_sesis.length > 0) {
                    if (assigned_sesis.includes(ids)) {
                        opsiSesi.append('<option value="' + ids + '">' + allSesi[ids] + '</option>');
                    }
                } else {
                    opsiSesi.append('<option value="' + ids + '">' + allSesi[ids] + '</option>');
                }
            }

            // Auto-select if only one option (especially for pengawas)
            if (is_pengawas === '1') {
                if (opsiRuang.find('option').length === 2) {
                    opsiRuang.val(opsiRuang.find('option:last').val()).trigger('change');
                }
                if (opsiSesi.find('option').length === 2) {
                    opsiSesi.val(opsiSesi.find('option:last').val()).trigger('change');
                }
            }
        });
        opsiTgl.removeAttr('disabled')

        opsiKelas.change(function () {
            kelas = $(this).val();
            tglJadwal = opsiTgl.val();
            loadSiswaKelas(kelas, tglJadwal)
        });

        opsiRuang.change(function () {
            sesi = opsiSesi.val();
            ruang = $(this).val();
            tglJadwal = opsiTgl.val();
            loadSiswaRuang($(this).val(), sesi, tglJadwal)
        })

        opsiSesi.change(function () {
            sesi = $(this).val();
            ruang = opsiRuang.val();
            tglJadwal = opsiTgl.val();
            loadSiswaRuang(ruang, $(this).val(), tglJadwal)
        })
    }

    function loadSiswaRuang(ruang, sesi, tglJadwal) {
        var empty = ruang === '' || sesi === '' || tglJadwal === '';
        if (!empty) {
            url = base_url + "cbtstatus/getstatussiswa?ruang=" + ruang + '&sesi=' + sesi + '&tgl=' + tglJadwal;
            refreshStatus();
        } else {
            console.log('empty')
        }
    }

    function loadSiswaKelas(kelas, tglJadwal) {
        var empty = kelas === '' || tglJadwal === '';
        if (!empty) {
            url = base_url + "cbtstatus/getstatussiswa?kelas=" + kelas + '&tgl=' + tglJadwal;
            refreshStatus();
        } else {
            console.log('empty')
        }
    }

    function kalkulasiWaktu(durasi) {
        if (!durasi || !durasi.mulai) return durasi;

        const mulai = new Date(durasi.mulai).getTime();
        let lamanya = durasi.lama_ujian;

        if (!lamanya || !lamanya.includes(':')) {
            if (!durasi.selesai) return durasi;

            const selesai = new Date(durasi.selesai).getTime();
            if (isNaN(mulai) || isNaN(selesai) || selesai < mulai) return durasi;

            const lama = (selesai - mulai) / 1000;
            const jam = Math.floor(lama / 3600).toString().padStart(2, '0');
            const menit = Math.floor((lama % 3600) / 60).toString().padStart(2, '0');
            const detik = Math.floor(lama % 60).toString().padStart(2, '0');

            lamanya = `${jam}:${menit}:${detik}`;
        }

        const [h, m, s] = lamanya.split(':').map(Number);
        const ej = h ? `${h}j ` : '';
        const em = m ? `${m}m ` : '';
        const ed = s ? `${s}d` : '';

        return {
            ...durasi,
            lama_ujian: lamanya,
            durasi: ej + em + ed || '0d'
        };
    }
</script>
<?= $this->endSection() ?>