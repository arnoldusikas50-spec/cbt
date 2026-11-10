<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$currentPage = $pager ? $pager->getCurrentPage() : 1;
$totalItems = $pager ? $pager->getTotal() : count($cards ?? []);
$pageCount = $pager ? $pager->getPageCount() : 1;
$noStart = max(1, ($currentPage - 1) * ($per_page ?? 15) + 1);
$noEnd = min($totalItems, ($currentPage - 1) * ($per_page ?? 15) + count($cards ?? []));

$hasFilter = (!empty($q) || !empty($filter_mapel_id) || !empty($filter_jenis_id) || !empty($filter_tp_id) || !empty($filter_smt_id) || !empty($filter_status));
?>

<!-- Content Header (Page Header) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-collection-play text-primary me-2"></i>
                    <?= esc($judul ?? 'Kartu Soal') ?>
                </h3>
                <p class="text-muted small mb-0 mt-1">
                    <i class="bi bi-calendar3 me-1"></i><?= esc($tp_active->tahun ?? '-') ?> — Semester <?= esc($smt_active->id_smt ?? '-') ?> &bull; Kelola Kisi-kisi Kartu Soal dan Paket Butir Ujian
                </p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Kartu Soal') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<style>
/* Pagination Custom Styles */
.pagination-container .page-link {
    border-radius: 8px !important;
    padding: 0.35rem 0.7rem;
    font-size: 0.825rem;
    font-weight: 600;
    color: #4f46e5;
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    text-decoration: none;
}
.pagination-container .page-link:hover:not(.disabled) {
    background-color: #eef2ff;
    border-color: #c7d2fe;
    color: #3730a3;
}
.pagination-container .page-item.active .page-link {
    background-color: #4f46e5 !important;
    border-color: #4f46e5 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
}
.pagination-container .page-item.disabled .page-link {
    color: #94a3b8;
    background-color: #f8fafc;
    border-color: #e2e8f0;
    pointer-events: none;
}

/* Table Hover & Row Polish */
#tableKartu th {
    font-size: 0.78rem;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
#tableKartu tr:hover {
    background-color: #f8fafc !important;
}
</style>

<div class="app-content">
    <div class="container-fluid">

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Bar Card -->
        <div class="card card-default my-shadow mb-4 border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="card-title fw-bold mb-0 text-dark">
                        <i class="bi bi-funnel text-primary me-2"></i>Filter Data Kartu Soal
                    </h6>
                    <?php if ($hasFilter): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small">
                            <i class="bi bi-filter me-1"></i>Filter Aktif
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body bg-light bg-opacity-50 p-3">
                <form method="get" action="<?= site_url('cbtkartusoal') ?>" id="filterForm">
                    <input type="hidden" name="per_page" value="<?= (int)($per_page ?? 15) ?>">
                    <div class="row g-2 align-items-center">
                        <div class="col-xl-3 col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                                <input class="form-control form-control-sm border-start-0 shadow-none rounded-end-3" name="q" id="inputSearch" value="<?= esc($q ?? '') ?>" placeholder="Cari judul, materi, atau guru...">
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <select class="form-select form-select-sm shadow-none rounded-3" name="mapel_id" onchange="this.form.submit()">
                                <option value="">— Semua Mata Pelajaran —</option>
                                <?php foreach (($mapels ?? []) as $m): ?>
                                    <option value="<?= (int) ($m->id_mapel ?? 0) ?>" <?= (int) ($filter_mapel_id ?? 0) === (int) ($m->id_mapel ?? 0) ? 'selected' : '' ?>>
                                        <?= esc(mb_strimwidth($m->nama_mapel ?? '-', 0, 32, '...')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-4">
                            <select class="form-select form-select-sm shadow-none rounded-3" name="id_jenis" onchange="this.form.submit()">
                                <option value="">— Semua Jenis Ujian —</option>
                                <?php foreach (($jenis_ujian ?? []) as $j): ?>
                                    <option value="<?= (int) ($j->id_jenis ?? 0) ?>" <?= (int) ($filter_jenis_id ?? 0) === (int) ($j->id_jenis ?? 0) ? 'selected' : '' ?>>
                                        <?= esc($j->nama_jenis ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-4">
                            <select class="form-select form-select-sm shadow-none rounded-3" name="status" onchange="this.form.submit()">
                                <option value="">— Semua Status —</option>
                                <option value="draft" <?= ($filter_status === 'draft') ? 'selected' : '' ?>>Draft</option>
                                <option value="submitted" <?= ($filter_status === 'submitted') ? 'selected' : '' ?>>Menunggu Review</option>
                                <option value="approved" <?= ($filter_status === 'approved') ? 'selected' : '' ?>>Disetujui</option>
                                <option value="revisi" <?= ($filter_status === 'revisi') ? 'selected' : '' ?>>Perlu Revisi</option>
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-4 d-flex gap-1">
                            <select class="form-select form-select-sm shadow-none rounded-3" name="tp_id" onchange="this.form.submit()" title="Tahun Pelajaran">
                                <option value="">— Thn —</option>
                                <?php foreach (($tp_options ?? []) as $tp): ?>
                                    <option value="<?= (int) ($tp->id_tp ?? 0) ?>" <?= (int) ($filter_tp_id ?? 0) === (int) ($tp->id_tp ?? 0) ? 'selected' : '' ?>>
                                        <?= esc($tp->tahun ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select class="form-select form-select-sm shadow-none rounded-3" name="smt_id" onchange="this.form.submit()" style="max-width: 85px;" title="Semester">
                                <option value="">— Smt —</option>
                                <?php foreach (($smt_options ?? []) as $smt): ?>
                                    <option value="<?= (int) ($smt->id_smt ?? 0) ?>" <?= (int) ($filter_smt_id ?? 0) === (int) ($smt->id_smt ?? 0) ? 'selected' : '' ?>>
                                        <?= esc($smt->nama_smt ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <div class="small text-muted">
                                Ditemukan <strong class="text-dark"><?= $totalItems ?></strong> kartu soal
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($hasFilter): ?>
                                    <a href="<?= site_url('cbtkartusoal') ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-inline-flex align-items-center shadow-xs" title="Hapus Filter">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-primary rounded-pill px-3.5 shadow-sm fw-bold d-inline-flex align-items-center" type="submit">
                                    <i class="bi bi-funnel-fill me-1"></i>Terapkan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Card Data List -->
        <div class="card card-default my-shadow mb-4 border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-list-columns-reverse fs-5"></i>
                    </span>
                    <div>
                        <h6 class="card-title fw-bold mb-0 text-dark">Daftar Kisi-kisi Kartu Soal</h6>
                        <small class="text-muted">Kelola butir soal dan kloning ke paket bank soal CBT</small>
                    </div>
                </div>

                <div class="card-tools ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?= site_url('guru/tujuan') ?>" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs" title="Buka modul Tujuan Pembelajaran">
                        <i class="bi bi-bullseye me-1"></i>Tujuan Pembelajaran
                    </a>
                    <a href="<?= site_url('cbtbanksoal') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" title="Buka modul Bank Soal CBT">
                        <i class="bi bi-box-seam me-1"></i>Bank Soal
                    </a>
                    <?php if (!empty($can_manage)): ?>
                        <a href="<?= site_url('cbtkartusoal/create') ?>" class="btn btn-primary btn-sm fw-bold rounded-pill px-3.5 shadow-sm d-inline-flex align-items-center">
                            <i class="bi bi-plus-lg me-1.5"></i> Buat Kartu Soal Baru
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body p-0">
                <?php if (!empty($can_manage)): ?>
                <div id="bulkBarKartu" class="d-none align-items-center justify-content-between gap-2 px-3 py-2 bg-warning bg-opacity-10 border-bottom">
                    <div class="small fw-bold text-dark">
                        <span id="bulkCountKartu">0</span> kartu dipilih
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btnClearBulkKartu">Batalkan</button>
                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm" id="btnBulkDeleteKartu">
                            <i class="bi bi-trash me-1"></i>Hapus Terpilih
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableKartu" style="font-size: 0.875rem;">
                        <thead>
                            <tr class="table-light text-nowrap" style="border-bottom: 2px solid #e2e8f0;">
                                <?php if (!empty($can_manage)): ?>
                                <th class="ps-3 py-3 text-center" style="width: 44px;">
                                    <input type="checkbox" class="form-check-input" id="checkAllKartu" title="Pilih semua">
                                </th>
                                <?php endif; ?>
                                <th class="ps-4 py-3 text-center text-secondary fw-bold" style="width: 60px;">No</th>
                                <th class="py-3 text-secondary fw-bold">Informasi Kartu Soal</th>
                                <th class="py-3 text-secondary fw-bold">Mata Pelajaran &amp; Kelas</th>
                                <th class="py-3 text-secondary fw-bold">Jenis &amp; Periode</th>
                                <th class="py-3 text-secondary fw-bold">Penyusun</th>
                                <th class="py-3 text-center text-secondary fw-bold">Estimasi &amp; Butir</th>
                                <th class="pe-4 py-3 text-center text-secondary fw-bold" style="min-width: 190px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cards)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px; background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                                <i class="bi bi-inbox text-primary" style="font-size: 2rem;"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">Belum ada data Kartu Soal</h6>
                                            <p class="text-muted small mb-3 mx-auto" style="max-width: 380px;">
                                                Silakan buat master kartu soal baru untuk mulai menyusun butir kisi-kisi dan paket ujian.
                                            </p>
                                            <?php if (!empty($can_manage)): ?>
                                                <a href="<?= site_url('cbtkartusoal/create') ?>" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm">
                                                    <i class="bi bi-plus-lg me-1"></i>Buat Kartu Soal
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $no = $noStart;
                                foreach ($cards as $c): 
                                    $wf = strtolower($c->workflow_status ?? 'draft');
                                    if ($wf === 'approved') {
                                        $wfText = 'Disetujui'; $wfBadge = 'bg-success'; $wfIcon = 'check-circle-fill';
                                    } elseif ($wf === 'submitted') {
                                        $wfText = 'Menunggu Validasi'; $wfBadge = 'bg-info'; $wfIcon = 'hourglass-split';
                                    } elseif ($wf === 'revisi') {
                                        $wfText = 'Perlu Revisi'; $wfBadge = 'bg-warning'; $wfIcon = 'exclamation-triangle-fill';
                                    } else {
                                        $wfText = 'Draft'; $wfBadge = 'bg-secondary'; $wfIcon = 'pencil-fill';
                                    }
                                    $bankUsage = (int) ($c->total_bank_dipakai ?? 0);
                                    // Alasan penolakan (approval_note) — hanya untuk kartu
                                    // yang berstatus "Perlu Revisi" supaya guru langsung
                                    // tahu apa yang perlu diperbaiki tanpa membuka detail.
                                    $revisiNote = $wf === 'revisi' ? trim((string) ($c->approval_note ?? '')) : '';
                                    $revisiNoteShort = $revisiNote !== ''
                                        ? mb_strimwidth($revisiNote, 0, 110, '…')
                                        : '';
                                ?>
                                    <tr>
                                        <?php if (!empty($can_manage)): ?>
                                        <td class="ps-3 text-center">
                                            <input type="checkbox" class="form-check-input kartu-check" value="<?= esc($c->uuid) ?>" data-locked="<?= $bankUsage > 0 ? '1' : '0' ?>" title="<?= $bankUsage > 0 ? 'Dipakai ' . $bankUsage . ' bank aktif — hapus biasa dilewati, hapus paksa memutus relasi' : 'Pilih untuk hapus massal' ?>">
                                        </td>
                                        <?php endif; ?>
                                        <td class="ps-4 text-center text-muted fw-semibold font-monospace">#<?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <a href="<?= site_url('cbtkartusoal/detail/' . $c->uuid) ?>" class="fw-bold text-dark text-decoration-none mb-1 text-truncate hover-primary" style="max-width: 300px;" title="<?= esc($c->judul ?? '') ?>">
                                                    <?= esc($c->judul ?? '') ?>
                                                </a>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <span class="badge <?= $wfBadge ?> bg-opacity-10 text-<?= str_replace('bg-', '', $wfBadge) ?> border px-2.5 py-1 rounded-pill" style="font-size: 0.7rem;">
                                                        <i class="bi bi-<?= $wfIcon ?> me-1"></i><?= $wfText ?>
                                                    </span>
                                                    <?php if ($bankUsage > 0): ?>
                                                        <span class="badge bg-purple bg-opacity-10 text-purple border px-2.5 py-1 rounded-pill" style="font-size: 0.7rem; background-color: #f3e8ff; color: #7e22ce; border-color: #e9d5ff;" title="Telah dibuat menjadi <?= $bankUsage ?> paket bank soal">
                                                            <i class="bi bi-box-seam me-1"></i>Paket Bank (<?= $bankUsage ?>)
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($revisiNoteShort !== ''): ?>
                                                    <div class="small text-warning-emphasis d-flex align-items-start gap-1 ks-revisi-hint"
                                                        title="<?= esc($revisiNote) ?>">
                                                        <i class="bi bi-chat-left-quote-fill mt-1 flex-shrink-0"></i>
                                                        <span class="fw-semibold text-truncate" style="max-width: 300px;">
                                                            <?= esc($revisiNoteShort) ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1 rounded-pill align-self-start fw-semibold" style="font-size: 0.75rem;">
                                                    <i class="bi bi-book me-1"></i><?= esc($c->nama_mapel ?? '-') ?>
                                                </span>
                                                <?php if (!empty($c->nama_kelas)): ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-0.5 rounded-pill align-self-start" style="font-size: 0.7rem;">
                                                        <i class="bi bi-building me-1"></i>Kelas <?= esc($c->nama_kelas) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-light text-dark border px-2 py-0.5 rounded-pill align-self-start" style="font-size: 0.72rem;">
                                                    <?= esc($c->nama_jenis ?? '-') ?>
                                                </span>
                                                <small class="text-muted" style="font-size: 0.75rem;">
                                                    <i class="bi bi-calendar-event me-1"></i><?= esc($c->tahun ?? '-') ?> (Smt <?= esc($c->nama_smt ?? '-') ?>)
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-light border text-secondary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                    <?= strtoupper(substr($c->nama_guru ?? 'A', 0, 2)) ?>
                                                </div>
                                                <div style="line-height: 1.25;">
                                                    <div class="fw-semibold text-dark small"><?= esc($c->nama_guru ?? 'Admin') ?></div>
                                                    <small class="text-muted" style="font-size: 0.7rem;">Penyusun</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex flex-column align-items-center">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2.5 py-1 mb-1 fw-bold" style="font-size: 0.75rem;">
                                                    <?= (int) ($c->total_item ?? 0) ?> Soal
                                                </span>
                                                <span class="small text-muted" style="font-size: 0.7rem;">
                                                    <i class="bi bi-clock me-1"></i><?= (int) ($c->total_estimasi ?? 0) ?> menit
                                                </span>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="btn-action-group justify-content-center">
                                                <a href="<?= site_url('cbtkartusoal/printPdf/' . $c->uuid) ?>" class="btn btn-action btn-action-status" target="_blank" data-bs-toggle="tooltip" title="Cetak PDF Kartu Soal">
                                                    <i class="bi bi-printer-fill"></i>
                                                </a>
                                                <a href="<?= site_url('cbtkartusoal/detail/' . $c->uuid) ?>" class="btn btn-action btn-action-magic" data-bs-toggle="tooltip" title="Kelola / Detail Butir Soal">
                                                    <i class="bi bi-magic"></i>
                                                </a>
                                                <button type="button" class="btn btn-action btn-action-warning btnGenBankIndex" 
                                                    data-id="<?= (int)$c->id_kartu ?>" 
                                                    data-clean="<?= esc(preg_replace('/[^a-zA-Z0-9]/', '', $c->judul ?? 'SOAL')) ?>" 
                                                    data-judul="<?= esc($c->judul ?? '') ?>" 
                                                    data-bs-toggle="tooltip" title="Generate Paket Bank Soal Baru">
                                                    <i class="bi bi-box-seam-fill"></i>
                                                </button>
                                                <?php if (!empty($can_manage)): ?>
                                                    <a href="<?= site_url('cbtkartusoal/edit/' . $c->uuid) ?>" class="btn btn-action btn-action-edit" data-bs-toggle="tooltip" title="Edit Master Kartu">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-action btn-action-delete btnDeleteKartu"
                                                        data-uuid="<?= esc($c->uuid) ?>" data-judul="<?= esc($c->judul ?? '') ?>"
                                                        data-bs-toggle="tooltip" title="<?= $bankUsage > 0 ? 'Hapus (dipakai ' . $bankUsage . ' bank aktif)' : 'Hapus Kartu Soal' ?>">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card Footer: Pagination Controls & Information -->
            <div class="card-footer bg-white border-top py-3 px-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pagination-container">
                    <!-- Left info & Per-page selector -->
                    <div class="d-flex align-items-center flex-wrap gap-2 small text-muted">
                        <div>
                            Menampilkan <span class="fw-bold text-dark"><?= !empty($cards) ? $noStart : 0 ?></span> – <span class="fw-bold text-dark"><?= !empty($cards) ? $noEnd : 0 ?></span> dari <span class="fw-bold text-dark"><?= $totalItems ?></span> data Kartu Soal
                        </div>
                        <div class="d-flex align-items-center ms-sm-2">
                            <span>Tampilkan</span>
                            <select id="selectPerPage" class="form-select form-select-sm d-inline-block w-auto mx-1 shadow-none rounded-2 border-secondary-subtle" onchange="changePerPage(this.value)">
                                <option value="10" <?= ($per_page ?? 15) == 10 ? 'selected' : '' ?>>10</option>
                                <option value="15" <?= ($per_page ?? 15) == 15 ? 'selected' : '' ?>>15</option>
                                <option value="20" <?= ($per_page ?? 15) == 20 ? 'selected' : '' ?>>20</option>
                                <option value="25" <?= ($per_page ?? 15) == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= ($per_page ?? 15) == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= ($per_page ?? 15) == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <span>baris</span>
                        </div>
                    </div>

                    <!-- Right Pagination Navigation -->
                    <?php if ($pager && $pageCount > 1): ?>
                        <?php
                        $hasPrev = $currentPage > 1;
                        $hasNext = $currentPage < $pageCount;
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($pageCount, $currentPage + 2);
                        ?>
                        <nav aria-label="Navigasi Halaman Kartu Soal">
                            <ul class="pagination pagination-sm mb-0 gap-1">
                                <!-- Tombol First & Prev -->
                                <?php if ($hasPrev): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $pager->getPageURI(1) ?>" title="Halaman Pertama"><i class="bi bi-chevron-double-left"></i></a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $pager->getPageURI($currentPage - 1) ?>" title="Halaman Sebelumnya"><i class="bi bi-chevron-left"></i></a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="bi bi-chevron-double-left"></i></span>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="bi bi-chevron-left"></i></span>
                                    </li>
                                <?php endif; ?>

                                <!-- Smart Window Page Links -->
                                <?php if ($startPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?= $pager->getPageURI(1) ?>">1</a></li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link border-0">…</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($p = $startPage; $p <= $endPage; $p++):
                                    $active = ($p == $currentPage) ? 'active' : '';
                                ?>
                                    <li class="page-item <?= $active ?>">
                                        <a class="page-link" href="<?= $pager->getPageURI($p) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($endPage < $pageCount): ?>
                                    <?php if ($endPage < $pageCount - 1): ?>
                                        <li class="page-item disabled"><span class="page-link border-0">…</span></li>
                                    <?php endif; ?>
                                    <li class="page-item"><a class="page-link" href="<?= $pager->getPageURI($pageCount) ?>"><?= $pageCount ?></a></li>
                                <?php endif; ?>

                                <!-- Tombol Next & Last -->
                                <?php if ($hasNext): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $pager->getPageURI($currentPage + 1) ?>" title="Halaman Selanjutnya"><i class="bi bi-chevron-right"></i></a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= $pager->getPageURI($pageCount) ?>" title="Halaman Terakhir"><i class="bi bi-chevron-double-right"></i></a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="bi bi-chevron-right"></i></span>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="bi bi-chevron-double-right"></i></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-light bg-opacity-50 border-top p-3 rounded-bottom-3">
                <small class="text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>
                        Kartu Soal adalah kisi-kisi master yang dapat di-generate menjadi <strong>Paket Bank Soal CBT</strong> secara otomatis atau diekspor ke format dokumen cetak resmi.
                    </span>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Generate Bank Soal dari Index Kartu Soal -->
<div class="modal fade" id="modalIndexGenBank" tabindex="-1" aria-labelledby="modalIdxGenTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 my-shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title fw-bold fs-6" id="modalIdxGenTitle">
                    <i class="bi bi-box-seam me-2"></i>Buat Paket Soal / Bank Soal Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formIndexGenBank" onsubmit="submitIndexGenBank(event)">
                <input type="hidden" name="kartu_id" id="idx_kartu_id" value="">
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Fitur ini akan membuat <strong>Paket Soal Baru (Bank Soal)</strong> dan secara otomatis mengkloning butir soal dari Kisi-kisi Kartu Soal <strong id="idx_target_judul"></strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Kode Bank Soal / Paket <span class="text-danger">*</span></label>
                        <input type="text" name="bank_kode" id="idx_bank_kode" class="form-control rounded-3" placeholder="Contoh: PAS-MAT11-A" required>
                        <small class="text-muted">Kode unik paket soal ujian.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Nama Paket Soal</label>
                        <input type="text" name="bank_nama" id="idx_bank_nama" class="form-control rounded-3" placeholder="Contoh: Paket Soal A - PAS Matematika Kelas 11">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Jenis Sub-Tes</label>
                        <select name="subtes_code" id="idx_subtes_code" class="form-select rounded-3">
                            <option value="GENERAL">📘 General / Reguler (CBT biasa)</option>
                            <option value="BIN">🇮🇩 Bahasa Indonesia</option>
                            <option value="MTK">➗ Matematika</option>
                            <option value="BING">🇬🇧 Bahasa Inggris</option>
                            <option value="FIS">⚛️ Fisika</option>
                            <option value="KIM">🧪 Kimia</option>
                            <option value="BIO">🧬 Biologi</option>
                            <option value="GEO">🌍 Geografi</option>
                            <option value="EKO">📈 Ekonomi</option>
                            <option value="SEJ">🏛️ Sejarah</option>
                            <option value="SOS">👥 Sosiologi</option>
                            <option value="PPN">🛡️ Pendidikan Pancasila</option>
                            <option value="INF">💻 Informatika</option>
                            <option value="KEAHLIAN">🛠️ Mapel Keahlian SMK</option>
                            <option value="VERBAL">🧠 Penalaran Verbal</option>
                            <option value="KUANTITATIF">🧮 Penalaran Kuantitatif</option>
                            <option value="LOGIS">🔗 Penalaran Logis &amp; Spasial</option>
                            <option value="LITERASI">📖 Literasi Membaca</option>
                            <option value="NUMERASI">📊 Literasi Numerasi &amp; Data</option>
                        </select>
                        <small class="text-muted">Pilih sub-tes untuk pengelompokan bank soal.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Pilihan Butir Soal yang Di-generate</label>
                        <select name="mode_soal" id="idx_mode_soal" class="form-select rounded-3">
                            <option value="all">🟢 Semua Soal (100% Butir Kisi-kisi)</option>
                            <option value="ganjil">🔵 Nomor Ganjil Sahaja (Paket A)</option>
                            <option value="genap">🟠 Nomor Genap Sahaja (Paket B)</option>
                        </select>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3 rounded-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="acak_opsi" id="idx_acak_opsi" value="1">
                            <label class="form-check-label small fw-bold text-dark ms-1" for="idx_acak_opsi">
                                Acak Urutan Pilihan Jawaban (A-E)
                            </label>
                        </div>
                    </div>

                    <div class="mb-0">
                        <input type="hidden" name="jumlah_paket" value="1">
                        <small class="text-muted">Paket A = kloning butir kartu. Untuk Paket B, generate lagi dengan mode Nomor Genap.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnSubmitIdxGenBank">
                        <i class="bi bi-box-seam me-1"></i>Buat Paket Soal Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Overlay Progres Generate Paket dari Kartu (Index) -->
<div class="modal fade" id="modalIdxGenBankProgress" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
            <h6 class="mt-3 mb-1 fw-bold">Membuat paket soal...</h6>
            <p class="text-muted small mb-2" id="idx_gen_bank_progress_txt">Menyiapkan paket...</p>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
            </div>
            <p class="text-muted small mt-3 mb-0">
                Bisa memakan waktu beberapa menit. <strong>Jangan tutup halaman</strong> selama proses berjalan.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Handle rows per page change
    function changePerPage(val) {
        const form = document.getElementById('filterForm');
        if (form) {
            form.querySelector('input[name="per_page"]').value = val;
            form.submit();
        } else {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
    }

    $(function () {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        const deleteUrl = <?= json_encode(site_url('cbtkartusoal/delete')) ?>;
        const bulkDeleteUrl = <?= json_encode(site_url('cbtkartusoal/bulk-delete')) ?>;
        const csrfName = <?= json_encode(csrf_token()) ?>;
        let csrfHash = <?= json_encode(csrf_hash()) ?>;

        function refreshCsrf(res) {
            if (res && res.csrfHash) csrfHash = res.csrfHash;
            if (res && res.token) csrfHash = res.token;
        }

        function updateBulkBar() {
            const checked = $('.kartu-check:checked:not(:disabled)');
            const n = checked.length;
            $('#bulkCountKartu').text(n);
            const bar = $('#bulkBarKartu');
            if (n > 0) {
                bar.removeClass('d-none').addClass('d-flex');
            } else {
                bar.addClass('d-none').removeClass('d-flex');
            }
            const total = $('.kartu-check:not(:disabled)').length;
            $('#checkAllKartu').prop('checked', total > 0 && n === total);
        }

        $(document).on('change', '#checkAllKartu', function () {
            $('.kartu-check:not(:disabled)').prop('checked', $(this).is(':checked'));
            updateBulkBar();
        });
        $(document).on('change', '.kartu-check', updateBulkBar);
        $(document).on('click', '#btnClearBulkKartu', function () {
            $('.kartu-check').prop('checked', false);
            updateBulkBar();
        });

        $(document).on('click', '#btnBulkDeleteKartu', function () {
            const uuids = $('.kartu-check:checked:not(:disabled)').map(function () {
                return $(this).val();
            }).get();
            if (uuids.length === 0) {
                showError('Pilih minimal satu kartu soal.');
                return;
            }
            const proceed = (force) => {
                $.ajax({
                    url: bulkDeleteUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { uuids: uuids, force: force ? 1 : 0, [csrfName]: csrfHash },
                    success: function (res) {
                        refreshCsrf(res);
                        if (res && res.status) {
                            let detail = res.message || 'Bulk delete selesai.';
                            if (res.data) {
                                const skipLocked = (res.data.skipped_locked || []).length;
                                const skipForbid = (res.data.skipped_forbidden || []).length;
                                if (skipLocked > 0 || skipForbid > 0) {
                                    detail += '<br><small class="text-muted">' + skipLocked + ' terkunci bank soal, ' + skipForbid + ' ditolak.</small>';
                                }
                                if (skipLocked > 0 && !force && typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Sebagian Terkunci',
                                        html: detail + '<br><br>Hapus paksa ' + skipLocked + ' kartu terkunci? Relasi kartu-bank akan diputus.',
                                        showCancelButton: true,
                                        confirmButtonColor: '#ef4444',
                                        confirmButtonText: 'Ya, Hapus Paksa',
                                        cancelButtonText: 'Tidak',
                                        reverseButtons: true
                                    }).then((r) => {
                                        if (r.isConfirmed) {
                                            proceed(true);
                                        } else {
                                            window.location.reload();
                                        }
                                    });
                                    return;
                                }
                            }
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'success', title: 'Selesai', html: detail, timer: 2500, showConfirmButton: false }).then(() => window.location.reload());
                            } else {
                                window.location.reload();
                            }
                        } else {
                            showError((res && res.message) ? res.message : 'Gagal menghapus massal.');
                        }
                    },
                    error: function (xhr) {
                        refreshCsrf(xhr && xhr.responseJSON);
                        const resp = xhr && xhr.responseJSON;
                        showError((resp && resp.message) ? resp.message : 'Gagal menghapus massal.');
                    }
                });
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus ' + uuids.length + ' Kartu Soal?',
                    text: 'Kartu yang dipakai bank soal aktif akan dilewati otomatis.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => { if (result.isConfirmed) proceed(); });
            } else if (confirm('Hapus ' + uuids.length + ' kartu soal?')) {
                proceed();
            }
        });

        function showError(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Gagal', message || 'Terjadi kesalahan sistem.', 'error');
            } else {
                alert(message || 'Terjadi kesalahan sistem.');
            }
        }

        $(document).on('click', '.btnDeleteKartu', function () {
            const btn = $(this);
            const uuid = String(btn.data('uuid') || '');
            const judul = String(btn.data('judul') || '');
            if (!uuid) {
                showError('UUID kartu tidak valid.');
                return;
            }

            const proceed = (force) => {
                $.ajax({
                    url: deleteUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        uuid: uuid,
                        force: force ? 1 : 0,
                        [csrfName]: csrfHash
                    },
                    success: function (res) {
                        refreshCsrf(res);
                        if (res && res.status) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message || 'Kartu soal berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => window.location.reload());
                            } else {
                                window.location.reload();
                            }
                        } else {
                            showError((res && res.message) ? res.message : 'Gagal menghapus kartu soal.');
                        }
                    },
                    error: function (xhr) {
                        refreshCsrf(xhr && xhr.responseJSON);
                        const resp = xhr && xhr.responseJSON;
                        if (xhr && xhr.status === 422 && resp && resp.data && resp.data.can_force) {
                            const n = resp.data.bank_count || 0;
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Dipakai ' + n + ' Bank Aktif',
                                    html: (resp.message || '') + '<br><br>Hapus paksa akan <b>memutus relasi</b> kartu-bank (bank tetap ada, kolom kartu_soal_id dikosongkan). Lanjutkan?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#64748b',
                                    confirmButtonText: 'Ya, Hapus Paksa',
                                    cancelButtonText: 'Batal',
                                    reverseButtons: true
                                }).then((r) => { if (r.isConfirmed) proceed(true); });
                            } else if (confirm('Hapus paksa? Relasi ke bank akan diputus.')) {
                                proceed(true);
                            }
                        } else {
                            showError((resp && resp.message) ? resp.message : 'Gagal menghapus kartu soal.');
                        }
                    }
                });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Kartu Soal?',
                    text: judul ? ('Kartu "' + judul + '" akan dihapus permanen.') : 'Data kartu soal akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        proceed();
                    }
                });
            } else if (confirm('Hapus kartu soal ini?')) {
                proceed();
            }
        });

        $(document).on('click', '.btnGenBankIndex', function () {
            const btn = $(this);
            const idKartu = btn.data('id');
            const cleanJudul = String(btn.data('clean') || 'SOAL');
            const rawJudul = String(btn.data('judul') || 'Kartu Soal');

            $('#idx_kartu_id').val(idKartu);
            $('#idx_target_judul').text(rawJudul);
            $('#idx_bank_kode').val(cleanJudul + '-A');
            $('#idx_bank_nama').val('Paket Soal A - ' + rawJudul);
            $('#idx_mode_soal').val('all');
            $('#idx_acak_opsi').prop('checked', false);

            var m = new bootstrap.Modal(document.getElementById('modalIndexGenBank'));
            m.show();
        });
    });

    function submitIndexGenBank(e) {
        e.preventDefault();

        const kode = String($('#idx_bank_kode').val() || '').trim();
        if (!kode) {
            Swal.fire('Kode Belum Diisi', 'Isi kode bank soal / paket terlebih dahulu.', 'warning');
            return;
        }

        const run = function() {
            const btn = $('#btnSubmitIdxGenBank');
            btn.prop('disabled', true);
            showIdxGenBankProgress('Menyiapkan paket kloning...');

            $.ajax({
                url: '<?= site_url("cbtkartusoal/generateBankFromKartu") ?>',
                type: 'POST',
                data: $('#formIndexGenBank').serialize(),
                dataType: 'json',
                timeout: 600000,
                success: function(resp) {
                    hideIdxGenBankProgress();
                    btn.prop('disabled', false);
                    if (resp.status) {
                        bootstrap.Modal.getInstance(document.getElementById('modalIndexGenBank')).hide();
                        let htmlDetail = resp.message || 'Paket soal berhasil dibuat.';
                        Swal.fire({
                            icon: 'success',
                            title: 'Paket Soal Terbuat!',
                            html: htmlDetail,
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-folder-symlink me-1"></i>Buka Bank Soal',
                            cancelButtonText: 'Tetap di Sini',
                            confirmButtonColor: '#4f46e5'
                        }).then((result) => {
                            if (result.isConfirmed && resp.data && resp.data.url_detail) {
                                window.location.href = resp.data.url_detail;
                            }
                        });
                    } else {
                        Swal.fire('Gagal Membuat Paket', resp.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(xhr, status) {
                    hideIdxGenBankProgress();
                    btn.prop('disabled', false);
                    const msg = xhr?.responseJSON?.message || (status === 'timeout' ? 'Proses terlalu lama. Coba jumlah paket lebih sedikit atau periksa API Key.' : 'Gagal menghubungi server.');
                    Swal.fire('Error', msg, 'error');
                }
            });
        };

        run();
    }

    function showIdxGenBankProgress(txt) {
        $('#idx_gen_bank_progress_txt').text(txt || 'Menyiapkan paket...');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalIdxGenBankProgress')).show();
    }

    function hideIdxGenBankProgress() {
        const m = bootstrap.Modal.getInstance(document.getElementById('modalIdxGenBankProgress'));
        if (m) m.hide();
    }
</script>
<?= $this->endSection() ?>
