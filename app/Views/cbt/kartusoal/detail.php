<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$itemCount = (int) ($item_count ?? 0);
$totalEstimasi = (int) ($total_estimasi ?? 0);
$linkedTujuanCount = (int) ($linkedTujuanCount ?? 0);
$wf = strtolower($kartu->workflow_status ?? 'draft');
$isEditable = in_array($wf, ['draft', 'revisi'], true);
$wfProps = [
    'approved' => ['Disetujui', 'success', 'check-circle-fill'],
    'submitted' => ['Menunggu Validasi', 'info', 'hourglass-split'],
    'revisi' => ['Perlu Revisi', 'warning', 'exclamation-triangle-fill'],
    'draft' => ['Draft', 'secondary', 'pencil-fill']
][$wf] ?? ['Draft', 'secondary', 'pencil-fill'];

// Catatan revisi terakhir (dari audit log; fallback ke kolom approval_note).
$revisiInfo = is_array($revisi_info ?? null) ? $revisi_info : ['note' => '', 'by' => '', 'at' => ''];
if (trim((string) $revisiInfo['note']) === '') {
    $revisiInfo['note'] = trim((string) ($kartu->approval_note ?? ''));
}
$revisiNote = trim((string) $revisiInfo['note']);
$revisiMeta = [];
if ($revisiInfo['by'] !== '') {
    $revisiMeta[] = 'oleh ' . $revisiInfo['by'];
}
if ($revisiInfo['at'] !== '') {
    $revisiMeta[] = $revisiInfo['at'];
}
?>

<style>
/* === Kartu Soal Detail — simplified theme === */
.ks-head-title { font-size: 1.05rem; }
.ks-meta-badge { font-size: .75rem; font-weight: 500; }
.ks-stat { min-width: 86px; }
.ks-stat .num { font-size: 1.05rem; font-weight: 700; line-height: 1; }
.ks-stat .lbl { font-size: .68rem; color: #64748b; }
/* Pagination */
.pagination-container .page-link {
    border-radius: 8px !important;
    padding: 0.35rem 0.7rem;
    font-size: 0.825rem;
    font-weight: 600;
    color: #4f46e5;
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    text-decoration: none;
}
.pagination-container .page-link:hover:not(.disabled) { background-color: #eef2ff; border-color: #c7d2fe; }
.pagination-container .page-item.active .page-link { background-color: #4f46e5 !important; border-color: #4f46e5 !important; color: #fff !important; }
.pagination-container .page-item.disabled .page-link { color: #94a3b8; background-color: #f8fafc; pointer-events: none; }
/* Row actions — satu gaya, ukuran konsisten */
.btn-action-group { display: inline-flex; gap: 4px; }
.btn-action-group .btn-action {
    width: 30px; height: 30px; padding: 0; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.78rem; border: 1px solid transparent;
}
.btn-action-info { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
.btn-action-info:hover { background: #0284c7; color: #fff; }
.btn-action-magic { background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; }
.btn-action-magic:hover { background: #7c3aed; color: #fff; }
.btn-action-edit { background: #fef9c3; color: #a16207; border-color: #fef08a; }
.btn-action-edit:hover { background: #eab308; color: #fff; }
.btn-action-delete { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.btn-action-delete:hover { background: #dc2626; color: #fff; }
/* Tabel: rapat + mudah dipindai */
#itemsTable th { font-size: 0.72rem; letter-spacing: .3px; text-transform: uppercase; white-space: nowrap; }
#itemsTable td { font-size: .83rem; vertical-align: top; }
#itemsTable tr:hover { background-color: #f8fafc !important; }
.ks-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.ks-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
/* Section label di modal butir */
.ks-sec { font-size: .72rem; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: #6366f1; }
/* Tab modal AI di atas header gelap */
#aiGeneratorTabs .nav-link:not(.active) { color: rgba(255,255,255,.7); }
#aiGeneratorTabs .nav-link:not(.active):hover { color: #fff; border-color: transparent; }
#aiGeneratorTabs .nav-link.active { font-weight: 600; }
</style>

<!-- Header halaman (ringkas: judul + meta + breadcrumb) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center g-2">
            <div class="col-md-8">
                <h3 class="mb-1 fw-bold text-dark ks-head-title">
                    <i class="bi bi-list-task text-primary me-2"></i>Kelola Butir Soal &amp; Kisi-kisi
                </h3>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border ks-meta-badge">
                        <i class="bi bi-book me-1"></i><?= esc($mapel->nama_mapel ?? '-') ?>
                    </span>
                    <?php if (!empty($kelas->nama_kelas)): ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis border ks-meta-badge">
                            Kelas <?= esc($kelas->nama_kelas) ?>
                        </span>
                    <?php endif; ?>
                    <span class="badge bg-light text-secondary border ks-meta-badge">
                        <?= esc($tp_row->tahun ?? '-') ?> &middot; Smt <?= esc($smt_row->nama_smt ?? '-') ?>
                    </span>
                    <span class="badge bg-<?= $wfProps[1] ?>-subtle text-<?= $wfProps[1] ?> border ks-meta-badge">
                        <i class="bi bi-<?= $wfProps[2] ?> me-1"></i><?= $wfProps[0] ?>
                    </span>
                    <?php if (!empty($has_orphan_items)): ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis border ks-meta-badge" title="Ada butir menunjuk ke TP yang sudah nonaktif">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>TP tidak aktif
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <ol class="breadcrumb float-md-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('cbtkartusoal') ?>" class="text-decoration-none">Kartu Soal</a>
                    </li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Info kartu + statistik (satu kartu, bukan dua bar terpisah) -->
        <div class="card border-0 rounded-3 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-start gap-2">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold text-dark text-truncate" title="<?= esc($kartu->judul ?? '') ?>">
                                    <i class="bi bi-journal-text me-1 text-primary"></i><?= esc($kartu->judul ?? '-') ?>
                                </div>
                                <?php if (!empty($kartu->deskripsi)): ?>
                                    <div class="text-muted small ks-clamp-2 mt-1" title="<?= esc(strip_tags($kartu->deskripsi)) ?>">
                                        <?= esc(mb_strimwidth(strip_tags($kartu->deskripsi), 0, 160, '…')) ?>
                                    </div>
                                <?php endif; ?>
                                <a href="<?= site_url('cbtkartusoal/edit/' . $kartu->uuid) ?>" class="small text-decoration-none">
                                    <i class="bi bi-pencil-square me-1"></i>Edit info kartu
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex align-items-center justify-content-lg-end gap-3 flex-wrap">
                            <div class="text-center ks-stat">
                                <div class="num text-primary" id="summaryItemCount"><?= $itemCount ?></div>
                                <div class="lbl">Butir</div>
                            </div>
                            <div class="vr opacity-25 d-none d-sm-block"></div>
                            <div class="text-center ks-stat">
                                <div class="num text-success" id="summaryWaktu"><?= $totalEstimasi ?></div>
                                <div class="lbl">Menit</div>
                            </div>
                            <div class="vr opacity-25 d-none d-sm-block"></div>
                            <div class="text-center ks-stat">
                                <div class="num text-info" id="summaryTujuan"><?= $linkedTujuanCount ?></div>
                                <div class="lbl">TP terkait</div>
                            </div>
                            <div id="summaryJenisWrapper" class="d-flex flex-wrap align-items-center gap-1">
                                <?php if (!empty($summary_jenis)): ?>
                                    <?php foreach ($summary_jenis as $jenisId => $count):
                                        $label = $jenis_soal_labels[$jenisId] ?? 'Lainnya';
                                    ?>
                                        <span class="badge bg-light text-secondary border rounded-pill fw-medium summary-jenis-badge"
                                              id="summaryJenis_<?= $jenisId ?>"
                                              style="font-size: 0.7rem;" title="<?= esc($label) ?>">
                                            <?= esc(str_replace(['Pilihan Ganda Kompleks', 'Pilihan Ganda'], ['PG Kmplx', 'PG'], $label)) ?>: <strong class="text-dark" id="summaryJenisCount_<?= $jenisId ?>"><?= $count ?></strong>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan revisi: muncul saat kartu berstatus "Perlu Revisi" -->
        <?php if ($wf === 'revisi' || ($wf === 'submitted' && $revisiNote !== '')): ?>
            <div class="alert <?= $wf === 'revisi' ? 'alert-warning' : 'alert-info' ?> border-0 rounded-3 shadow-sm mb-3 ks-revisi-note"
                role="alert">
                <div class="d-flex flex-column flex-md-row gap-3">
                    <div class="flex-grow-1">
                        <div class="fw-bold d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-<?= $wf === 'revisi' ? 'exclamation-triangle-fill' : 'info-circle-fill' ?>"></i>
                            <?= $wf === 'revisi' ? 'Kartu Soal perlu direvisi' : 'Kartu ini pernah dikembalikan untuk revisi' ?>
                        </div>
                        <?php if ($revisiNote !== ''): ?>
                            <div class="ks-revisi-text" style="white-space: pre-wrap; font-size: .9rem;"><?= esc($revisiNote) ?></div>
                        <?php else: ?>
                            <div class="small text-muted fst-italic">
                                Reviewer tidak menuliskan catatan. Periksa kembali butir soal, tujuan pembelajaran, dan jenjang LEVEL butir.
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($revisiMeta)): ?>
                            <div class="small text-muted mt-2">
                                <i class="bi bi-clock-history me-1"></i><?= esc(implode(' • ', $revisiMeta)) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($wf === 'revisi' && $isEditable): ?>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-pencil-square me-1"></i>Kartu dapat diedit. Setelah selesai, klik
                                <strong>Ajukan</strong> untuk dikirim kembali ke reviewer.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($wf === 'revisi' && $isEditable): ?>
                        <div class="d-flex align-items-start">
                            <button type="button" class="btn btn-warning btn-sm fw-semibold rounded-pill px-3 shadow-sm"
                                id="btnGoRevisiItems">
                                <i class="bi bi-arrow-down me-1"></i>Perbaiki Butir Soal
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <script>
            // tombol "Perbaiki Butir Soal" pada catatan revisi
            document.addEventListener('DOMContentLoaded', function () {
                var btn = document.getElementById('btnGoRevisiItems');
                if (!btn) return;
                btn.addEventListener('click', function () {
                    var target = document.getElementById('itemsTable');
                    if (target && target.scrollIntoView) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } else {
                        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                    }
                });
            });
        </script>

        <!-- Main Card: Items Management -->
        <div class="card card-default my-shadow border-0 rounded-3 mb-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-grid-3x3-gap-fill text-primary me-1.5"></i>Daftar Butir Soal
                            </h6>
                            <button id="btnBulkDelete" class="btn btn-sm btn-danger transition-all rounded-pill px-3 d-none shadow-sm">
                                <i class="bi bi-trash-fill me-1"></i>Hapus Terpilih (<span id="countChecked">0</span>)
                            </button>
                        </div>
                        <small class="text-muted" id="listHint">Memuat daftar butir soal...</small>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <?php if (!empty($can_manage) && $isEditable): ?>
                            <!-- Aksi utama: Tambah -->
                            <button type="button" class="btn btn-sm btn-primary fw-semibold rounded-pill px-3 shadow-sm" id="btnAddItem" title="Tambah butir soal (Alt+N)" <?= !empty($tujuan_options) ? '' : 'disabled title="Tujuan pembelajaran belum tersedia"' ?>>
                                <i class="bi bi-plus-lg me-1"></i>Tambah Butir
                            </button>
                        <?php endif; ?>

                        <?php if (!empty($can_manage) && $isEditable && (!isset($setting->enable_ai) || $setting->enable_ai == 1)): ?>
                            <!-- AI: satu tombol membuka satu modal bertab -->
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 shadow-none" id="btnOpenAiGenerate" <?= !empty($tujuan_options) ? '' : 'disabled' ?> title="Generate kisi-kisi dengan AI (Per TP / Ujian Sekolah)">
                                <i class="bi bi-stars me-1"></i>AI Generator
                            </button>
                        <?php endif; ?>

                        <!-- Workflow: hanya satu tombol kontekstual -->
                        <?php if (!empty($can_manage) && empty($is_approval_role) && $isEditable): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 shadow-none" id="btnSubmitApproval" title="Ajukan validasi ke kurikulum/admin">
                                <i class="bi bi-send-check-fill me-1"></i>Ajukan
                            </button>
                        <?php endif; ?>
                        <?php if (!empty($is_approval_role) && in_array($wf, ['submitted', 'revisi', 'draft'])): ?>
                            <div class="btn-group shadow-none">
                                <button type="button" class="btn btn-sm btn-success rounded-start-3" id="btnApproveKartu" title="Setujui Kartu Soal">
                                    <i class="bi bi-check-circle-fill me-1"></i>Setujui
                                </button>
                                <?php if ($wf !== 'revisi'): ?>
                                    <button type="button" class="btn btn-sm btn-warning rounded-end-3" id="btnRejectKartu" title="Kembalikan untuk revisi">
                                        <i class="bi bi-arrow-return-left me-1"></i>Revisi
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($is_approval_role) && $wf === 'approved'): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 shadow-none" id="btnCancelApproval" title="Batalkan persetujuan dan kembalikan kartu ke status Draft">
                                <i class="bi bi-shield-x me-1"></i>Batalkan
                            </button>
                        <?php endif; ?>

                        <!-- Lainnya: cetak, paket, sinkron, urutkan, kembali -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary rounded-3 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Aksi lainnya">
                                <i class="bi bi-three-dots me-1"></i>Lainnya
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:250px;">
                                <li><h6 class="dropdown-header">Dokumen &amp; Paket</h6></li>
                                <li><a class="dropdown-item" href="<?= site_url('cbtkartusoal/printPdf/' . $kartu->uuid) ?>" target="_blank">
                                    <i class="bi bi-printer-fill me-2 text-danger"></i>Cetak Kisi-kisi (PDF)
                                </a></li>
                                <?php foreach (($paket_banks ?? []) as $pb): ?>
                                <li><a class="dropdown-item" href="<?= site_url('cbtkartusoal/printPdfPaket/' . $kartu->uuid . '/' . (int)$pb['id_bank']) ?>" target="_blank">
                                    <i class="bi bi-box-seam me-2 text-success"></i>Cetak Paket <?= esc($pb['letter']) ?>
                                    <span class="text-muted small">(<?= (int)($pb['total_tampil'] ?? $pb['total_soal'] ?? 0) ?> soal)</span>
                                </a></li>
                                <?php endforeach; ?>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="openGenerateBankModal()">
                                    <i class="bi bi-plus-circle me-2 text-success"></i>Buat Paket Bank Soal
                                </a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" id="btnLihatPaket" style="display:none;">
                                    <i class="bi bi-collection-play me-2 text-info"></i>Lihat Soal Paket (<span id="btnLihatPaketCount">0</span>)
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">Perawatan data</h6></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="resyncItemsFromTp(<?= $kartu->id_kartu ?>)">
                                    <i class="bi bi-arrow-repeat me-2 text-primary"></i>Sinkronkan Ulang TP
                                </a></li>
                                <?php if (!empty($can_manage) && $isEditable): ?>
                                <li><a class="dropdown-item" href="javascript:void(0)" id="btnAutoReorder">
                                    <i class="bi bi-sort-numeric-down me-2 text-warning"></i>Urutkan Ulang Nomor
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= site_url('cbtkartusoal') ?>">
                                    <i class="bi bi-arrow-left me-2 text-secondary"></i>Kembali ke Daftar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter: satu baris (cari + TP + bentuk + reset) -->
            <div class="border-bottom bg-light bg-opacity-50 px-3 py-2">
                <div class="d-flex gap-2 align-items-center flex-wrap flex-lg-nowrap">
                    <div class="input-group input-group-sm flex-grow-1" style="min-width:200px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control form-control-sm border-start-0 border-end-0 shadow-none" id="filterSearch" placeholder="Cari materi, indikator, No…" autocomplete="off">
                        <button class="btn btn-outline-secondary border-start-0 bg-white d-none" type="button" id="btnClearSearch" title="Bersihkan pencarian"><i class="bi bi-x text-muted"></i></button>
                    </div>
                    <select class="form-select form-select-sm shadow-none" style="max-width:260px;" id="filterTujuanId" title="Filter berdasarkan Tujuan Pembelajaran">
                        <option value="">Semua TP</option>
                        <?php
                        $filterLastGroup = null;
                        foreach (($tujuan_options ?? []) as $tp):
                            $tpObj = is_object($tp) ? $tp : (object)$tp;
                            $groupKey = trim(($tpObj->kode_cp ?? '') . '|' . ($tpObj->elemen_cp ?? ''));
                            $groupLabel = $tpObj->kode_cp ?? '' ? esc($tpObj->kode_cp) . ' — ' . esc(mb_strimwidth($tpObj->elemen_cp ?? '', 0, 40, '...')) : '(Tanpa CP)';
                            if ($groupKey !== $filterLastGroup):
                                if ($filterLastGroup !== null): ?></optgroup><?php endif;
                                $filterLastGroup = $groupKey; ?>
                            <optgroup label="<?= $groupLabel ?>">
                            <?php endif; ?>
                            <option value="<?= (int) ($tpObj->id_tujuan ?? 0) ?>">[<?= esc($tpObj->level_kognitif ?? '?') ?>] <?= esc($tpObj->kode ?? '') ?> — <?= esc(mb_strimwidth($tpObj->tujuan ?? '', 0, 40, '...')) ?></option>
                        <?php endforeach;
                        if ($filterLastGroup !== null): ?></optgroup><?php endif; ?>
                    </select>
                    <select class="form-select form-select-sm shadow-none" style="max-width:190px;" id="filterJenisSoal" title="Filter berdasarkan bentuk soal">
                        <option value="">Semua Bentuk</option>
                        <option value="1">Pilihan Ganda</option>
                        <option value="2">PG Kompleks</option>
                        <option value="3">Menjodohkan</option>
                        <option value="4">Isian Singkat</option>
                        <option value="5">Uraian / Essay</option>
                        <option value="6">Urutan</option>
                        <option value="7">Isian Rumpang</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-secondary shadow-none flex-shrink-0" id="btnResetFilter" title="Reset semua filter">
                        <i class="bi bi-arrow-counterclockwise"></i><span class="d-none d-xl-inline ms-1">Reset</span>
                    </button>
                    <span class="badge bg-primary-subtle text-primary border rounded-pill small d-none text-nowrap" id="filterActiveBadge">
                        <i class="bi bi-funnel-fill me-1"></i>Filter aktif
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div id="listLoading" class="p-5 text-center text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Memuat butir soal...
                </div>
                <div class="table-responsive d-none" id="itemsTableWrap">
                    <table class="table table-hover mb-0" id="itemsTable">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th class="text-center ps-3" style="width:36px;">
                                    <input type="checkbox" class="form-check-input" id="checkAll" title="Pilih semua">
                                </th>
                                <th class="text-center" style="width:46px;">No</th>
                                <th style="min-width:180px;max-width:260px;">Acuan TP</th>
                                <th style="min-width:150px;max-width:230px;">Materi</th>
                                <th style="min-width:170px;">Indikator</th>
                                <th class="text-center" style="width:64px;">Lvl</th>
                                <th style="width:110px;">Bentuk</th>
                                <th class="text-center" style="width:60px;" title="Nomor soal di bank/paket">Paket</th>
                                <th class="text-center d-none d-lg-table-cell" style="width:56px;" title="Estimasi waktu (menit)">
                                    <i class="bi bi-stopwatch"></i>
                                </th>
                                <?php if (!empty($can_manage)): ?>
                                    <th class="text-center pe-3" style="width:150px;">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
                <div id="emptyState" class="d-none p-5 text-center">
                    <div class="mb-3 text-muted">
                        <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Belum Ada Butir Soal</h6>
                    <p class="text-muted small mb-3">Tambahkan butir pertama atau gunakan AI Generator untuk mulai menyusun kartu soal.</p>
                    <?php if (!empty($can_manage) && $isEditable): ?>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" onclick="$('#btnAddItem').click()">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Butir Sekarang
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card Footer: Pagination Controls & Information -->
            <div class="card-footer bg-white border-top py-3 px-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pagination-container" id="paginationWrap">
                    <div class="d-flex align-items-center flex-wrap gap-2 small text-muted">
                        <span id="paginationInfo"></span>
                        <div class="d-flex align-items-center ms-sm-2">
                            <span>Tampilkan</span>
                            <select id="detailPerPage" class="form-select form-select-sm d-inline-block w-auto mx-1 shadow-none rounded-2 border-secondary-subtle">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                            <span>butir</span>
                        </div>
                    </div>
                    <nav aria-label="Navigasi Halaman Butir Soal">
                        <ul class="pagination pagination-sm mb-0 gap-1" id="paginationButtons"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Undo Delete Toast -->
<div id="undoDeleteToast" class="d-none" style="
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 1090;
    min-width: 320px;
    max-width: 420px;
    background: #1e293b;
    color: #f1f5f9;
    border-radius: 12px;
    padding: 0.85rem 1rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.35);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: opacity 0.3s;
">
    <i class="bi bi-trash text-danger fs-5 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <div class="fw-semibold small" id="undoToastMsg">Butir soal akan dihapus.</div>
        <div class="text-secondary" style="font-size:0.72rem;">
            Dihapus otomatis dalam <span id="undoCountdown" class="text-warning fw-bold">5</span> detik
        </div>
    </div>
    <button id="btnUndoDelete" class="btn btn-sm btn-outline-light border-0 fw-semibold px-3" style="white-space:nowrap;">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Batalkan
    </button>
</div>


<?php if (!empty($can_manage)): ?>
<!-- Modal AI Generator (gabungan: Per TP + Ujian Sekolah dalam tab) -->
<div class="modal fade" id="aiGeneratorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow overflow-hidden">
            <div class="modal-header bg-gradient pb-0" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;">
                <div>
                    <h5 class="modal-title text-white mb-2"><i class="bi bi-stars me-2"></i>AI Generator Kisi-kisi</h5>
                    <ul class="nav nav-tabs border-0 gap-1" id="aiGeneratorTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active border-0 rounded-top-2 px-3" id="tab-btn-pertp" data-bs-toggle="tab" data-bs-target="#tabAiPerTp" type="button" role="tab" aria-controls="tabAiPerTp" aria-selected="true">
                                <i class="bi bi-list-check me-1"></i>Per TP
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link border-0 rounded-top-2 px-3 text-white-50" id="tab-btn-sekolah" data-bs-toggle="tab" data-bs-target="#tabAiSekolah" type="button" role="tab" aria-controls="tabAiSekolah" aria-selected="false">
                                <i class="bi bi-buildings me-1"></i>Ujian Sekolah
                            </button>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="tab-content" id="aiGeneratorTabsContent">
                    <div class="tab-pane fade show active" id="tabAiPerTp" role="tabpanel" aria-labelledby="tab-btn-pertp">
                        <!-- Context Banner -->
                        <div class="alert alert-info d-flex align-items-start gap-2 py-2" style="font-size:0.82rem;">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <div>
                                <strong>Konteks Kartu:</strong>
                                <span id="aiContextMapel" class="ms-1 badge bg-primary-subtle text-primary border"><?= esc($kartu->judul ?? '-') ?></span>
                                <span class="text-muted mx-1">&middot;</span>
                                <span id="aiContextKelas" class="badge bg-secondary-subtle text-secondary border">
                                    Mapel: <?= esc($mapel->nama_mapel ?? '-') ?>
                                </span>
                                <?php if (!empty($kelas)): ?>
                                <span class="ms-1 badge bg-success-subtle text-success border">
                                    Kelas: <?= esc($kelas->nama_kelas ?? '-') ?>
                                </span>
                                <?php endif; ?>
                                <span class="ms-1 badge bg-warning-subtle text-warning border">
                                    <?= count($tujuan_options) ?> Tujuan Pembelajaran tersedia
                                </span>
                            </div>
                        </div>

                        <div class="row g-3">
                    <!-- Left: Parameters -->
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 h-100" style="background:#f8f9ff;">
                            <h6 class="fw-bold mb-3"><i class="bi bi-sliders me-1 text-primary"></i>Parameter Generate</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Jumlah Kisi-kisi Soal</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="number" class="form-control form-control-sm" id="aiJumlahSoal" min="1" max="30" value="5" style="max-width:90px;">
                                    <small class="text-muted">maks. 30 per proses</small>
                                </div>
                            </div>

                            <!-- TP Selection -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-flex justify-content-between">
                                    <span>Pilih Tujuan Pembelajaran</span>
                                    <span>
                                        <a href="#" id="btnSelectAllTp" class="small text-primary text-decoration-none me-2">Semua</a>
                                        <a href="#" id="btnClearTp" class="small text-secondary text-decoration-none">Reset</a>
                                    </span>
                                </label>
                                
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <select class="form-select form-select-sm" id="aiFilterKelas" title="Filter Kelas">
                                            <option value="">Semua Kelas</option>
                                            <option value="7">Kelas VII (7)</option>
                                            <option value="8">Kelas VIII (8)</option>
                                            <option value="9">Kelas IX (9)</option>
                                            <option value="10">Kelas X (10)</option>
                                            <option value="11">Kelas XI (11)</option>
                                            <option value="12">Kelas XII (12)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select class="form-select form-select-sm" id="aiFilterSemester" title="Filter Semester">
                                            <option value="">Semua Semester</option>
                                            <option value="1">Ganjil (1)</option>
                                            <option value="2">Genap (2)</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="aiTpCheckboxList" class="border rounded p-2 bg-white" style="max-height:160px;overflow-y:auto;">
                                    <?php if (empty($tujuan_options)): ?>
                                        <small class="text-danger">Tidak ada TP tersedia untuk mapel/guru ini.</small>
                                    <?php else: ?>
                                        <?php foreach ($tujuan_options as $tp): ?>
                                        <div class="form-check mb-1 ai-tp-item" data-kelas="<?= esc($tp->tingkat ?? '') ?>" data-smt="<?= esc($tp->id_smt ?? '') ?>">
                                            <input class="form-check-input aiTpCheck" type="checkbox"
                                                value="<?= (int) ($tp->id_tujuan ?? 0) ?>"
                                                id="aiTp<?= (int) ($tp->id_tujuan ?? 0) ?>"
                                                checked
                                                data-kode="<?= esc($tp->kode ?? '') ?>"
                                                data-tujuan="<?= esc($tp->tujuan ?? '') ?>"
                                                data-cp="<?= esc($tp->kode_cp ?? '') ?>"
                                                data-materi="<?= esc($tp->materi_pokok ?? '') ?>"
                                                data-fase="<?= esc($tp->fase ?? '') ?>"
                                                data-tingkat="<?= esc($tp->tingkat ?? '') ?>"
                                                data-level="<?= esc($tp->level_kognitif ?? '') ?>">
                                            <label class="form-check-label small d-flex" for="aiTp<?= (int) ($tp->id_tujuan ?? 0) ?>" title="<?= esc($tp->tujuan ?? '') ?>">
                                                <div style="flex:1;">
                                                    <span class="badge bg-info-subtle text-info border-0 me-1" style="font-size:0.65rem;"><?= esc($tp->kode ?? '-') ?></span>
                                                    <?= esc(mb_strimwidth($tp->tujuan ?? '-', 0, 55, '...')) ?>
                                                </div>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- TP Detail Panel -->
                            <div id="aiSelectedTpDetail" class="mb-3 d-none">
                                <label class="form-label small fw-semibold text-secondary d-flex justify-content-between align-items-center w-100 mb-1">
                                    <span><i class="bi bi-journal-text me-1"></i>Detail TP Terpilih</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill" id="aiTpBadgeCount">0</span>
                                </label>
                                <div id="aiTpDetailContent" class="border rounded p-2 bg-white small" style="max-height:100px;overflow-y:auto;font-size:0.75rem;line-height:1.4;color:#374151;"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Level Kognitif Dominan</label>
                                <select class="form-select form-select-sm" id="aiKesulitan">
                                    <option value="C1">C1 - Mengingat</option>
                                    <option value="C2">C2 - Memahami</option>
                                    <option value="C3">C3 - Menerapkan</option>
                                    <option value="C4">C4 - Menganalisis (HOTS)</option>
                                    <option value="C5">C5 - Mengevaluasi</option>
                                    <option value="C6">C6 - Mencipta</option>
                                    <option value="mixed" selected>Mixed (distribusi variatif)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Jenis Soal</label>
                                <div class="d-flex flex-column gap-1" style="font-size:0.82rem;">
                                    <label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input aiJenis" value="1" checked> Pilihan Ganda</label>
                                    <label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input aiJenis" value="2"> Pilihan Ganda Kompleks</label>
                                    <label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input aiJenis" value="3"> Menjodohkan</label>
                                    <label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input aiJenis" value="4"> Isian Singkat</label>
                                    <label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input aiJenis" value="5"> Uraian / Essay</label>
                                    <label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input aiJenis" value="6"> Urutan</label>
                                    <label class="d-flex align-items-center gap-2"><input type="checkbox" class="form-check-input aiJenis" value="7"> Isian Rumpang</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                    <i class="bi bi-cpu text-primary"></i> Mode AI (Pilih salah satu)
                                </label>
                                <div class="d-flex flex-wrap gap-2" id="aiModelWrapper">
                                    <input type="hidden" id="aiModel" value="">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 ai-model-btn active" data-model="">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Auto 
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ai-model-btn" data-model="gemini-3.5-flash-lite">
                                        Flash 3.5 Lite
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ai-model-btn" data-model="gemini-3.6-flash">
                                        Flash 3.6
                                    </button>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary w-100" id="btnGenerateAi">
                                <span class="spinner-border spinner-border-sm d-none" id="aiGenerateSpinner"></span>
                                <span id="aiGenerateText"><i class="bi bi-stars me-1"></i>Generate Kisi-kisi</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Preview -->
                    <div class="col-lg-8">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-table me-1 text-success"></i>Preview Kisi-kisi (Editable)</h6>
                            <small class="text-muted" id="aiPreviewMeta">Belum ada hasil.</small>
                        </div>
                        <div id="aiPreviewEmpty" class="text-center text-muted border rounded-3 p-5 bg-light">
                            <i class="bi bi-stars fs-2 d-block mb-2 text-muted opacity-50"></i>
                            Pilih Tujuan Pembelajaran, atur parameter, lalu klik <strong>Generate Kisi-kisi</strong>.
                        </div>
                        <div id="aiPreviewList" class="d-none overflow-auto" style="max-height:65vh;"></div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top">
                    <div class="me-auto text-danger small" id="aiQualityMessage"></div>
                    <button type="button" class="btn btn-success d-none" id="btnSaveAiGenerated">
                        <span class="spinner-border spinner-border-sm d-none" id="aiSaveSpinner"></span>
                        <span id="aiSaveText"><i class="bi bi-floppy me-1"></i>Simpan ke Kartu Soal</span>
                    </button>
                </div>
                    </div><!-- /#tabAiPerTp -->
                    <div class="tab-pane fade" id="tabAiSekolah" role="tabpanel" aria-labelledby="tab-btn-sekolah">
                        <div class="alert alert-success d-flex align-items-start gap-2 py-2" style="font-size:0.82rem;">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <div>
                                <strong>Mode Ujian Sekolah:</strong> AI akan menghasilkan kisi-kisi dengan proporsi ketat antar kelas.
                                <span class="ms-1 badge bg-success-subtle text-success border">X: 20%</span>
                                <span class="ms-1 badge bg-warning-subtle text-warning border">XI: 30%</span>
                                <span class="ms-1 badge bg-primary-subtle text-primary border">XII: 50%</span>
                            </div>
                        </div>
                        <div class="row g-3">
                    <!-- Left: Parameters -->
                    <div class="col-lg-4">
                        <div class="border rounded-3 p-3 h-100" style="background:#f0fdf4;">
                            <h6 class="fw-bold mb-3"><i class="bi bi-sliders me-1 text-success"></i>Parameter Generate</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Jumlah Soal Total</label>
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="number" class="form-control form-control-sm" id="aiSekolahJumlahSoal" min="10" max="50" value="50" style="max-width:90px;">
                                    <small class="text-muted">10-50 (rekomendasi: 20)</small>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-info-circle me-1"></i>Jumlah besar (50+) bisa menyebabkan error parsing. Generate dalam batch jika perlu.
                                </small>
                            </div>

                            <!-- Distribusi Preview -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Preview Distribusi Kelas</label>
                                <div class="border rounded p-2 bg-white">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small">Kelas X (20%)</span>
                                        <span class="badge bg-success" id="previewKelasX">10</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small">Kelas XI (30%)</span>
                                        <span class="badge bg-warning text-dark" id="previewKelasXI">15</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small">Kelas XII (50%)</span>
                                        <span class="badge bg-primary" id="previewKelasXII">25</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold">Total</span>
                                        <span class="badge bg-dark" id="previewTotal">50</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Distribusi Bloom Preview -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Target Distribusi Bloom</label>
                                <div class="border rounded p-2 bg-white small">
                                    <div class="d-flex justify-content-between mb-1"><span>C1 (15-25%)</span><span class="text-muted" id="previewBloomC1">~10</span></div>
                                    <div class="d-flex justify-content-between mb-1"><span>C2 (20-30%)</span><span class="text-muted" id="previewBloomC2">~12</span></div>
                                    <div class="d-flex justify-content-between mb-1"><span>C3 (25-35%)</span><span class="text-muted" id="previewBloomC3">~15</span></div>
                                    <div class="d-flex justify-content-between mb-1"><span>C4 (15-25%)</span><span class="text-muted" id="previewBloomC4">~8</span></div>
                                    <div class="d-flex justify-content-between"><span>C5 (10-20%)</span><span class="text-muted" id="previewBloomC5">~5</span></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Instruksi Tambahan (Opsional)</label>
                                <textarea class="form-control form-control-sm" id="aiSekolahInstruksi" rows="2" placeholder="Contoh: Fokus pada materi semester genap, prioritaskan bab 3-5..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-list-check text-success me-1"></i>Pilihan Bentuk Soal</span>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="btnSelectAllJenis">Pilih Semua</button>
                                </label>
                                <div class="border rounded-3 p-2 bg-white" id="jenisSoalContainer">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="1" id="jenisSoal1" checked>
                                                <label class="form-check-label small" for="jenisSoal1">1. Pilihan Ganda</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="2" id="jenisSoal2" checked>
                                                <label class="form-check-label small" for="jenisSoal2">2. Pilihan Ganda Kompleks</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="3" id="jenisSoal3" checked>
                                                <label class="form-check-label small" for="jenisSoal3">3. Menjodohkan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="4" id="jenisSoal4" checked>
                                                <label class="form-check-label small" for="jenisSoal4">4. Isian Singkat</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="5" id="jenisSoal5" checked>
                                                <label class="form-check-label small" for="jenisSoal5">5. Uraian / Essay</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="6" id="jenisSoal6" checked>
                                                <label class="form-check-label small" for="jenisSoal6">6. Urutan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="7" id="jenisSoal7" checked>
                                                <label class="form-check-label small" for="jenisSoal7">7. Isian Rumpang</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    <i class="bi bi-info-circle me-1"></i>Centang bentuk soal yang ingin digunakan. AI akan mendistribusikan sesuai level kognitif.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-flex align-items-center gap-2">
                                    <i class="bi bi-cpu text-success"></i> Mode AI
                                </label>
                                <div class="d-flex flex-wrap gap-2" id="aiSekolahModelWrapper">
                                    <input type="hidden" id="aiSekolahModel" value="">
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 ai-sekolah-model-btn active" data-model="">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Auto
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ai-sekolah-model-btn" data-model="gemini-3.5-flash-lite">
                                        <i class="bi bi-cpu me-1"></i> Flash 3.5 Lite
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ai-sekolah-model-btn" data-model="gemini-3.6-flash">
                                        <i class="bi bi-cpu me-1"></i> Flash 3.6
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Generate Per Kelas / Tingkatan:</label>
                                <div class="d-flex flex-wrap gap-2" id="kelasButtonsContainer">
                                    <?php
                                        $targetTingkat = (int) ($kelas->tingkat ?? $kartu->tingkat ?? 10);
                                        if ($targetTingkat >= 1 && $targetTingkat <= 6) {
                                            $kelasBtns = [
                                                '1' => ['label' => 'Kelas 1', 'cls' => 'outline-success'],
                                                '2' => ['label' => 'Kelas 2', 'cls' => 'outline-info'],
                                                '3' => ['label' => 'Kelas 3', 'cls' => 'outline-warning'],
                                                '4' => ['label' => 'Kelas 4', 'cls' => 'outline-primary'],
                                                '5' => ['label' => 'Kelas 5', 'cls' => 'outline-secondary'],
                                                '6' => ['label' => 'Kelas 6', 'cls' => 'outline-danger'],
                                            ];
                                        } elseif ($targetTingkat >= 7 && $targetTingkat <= 9) {
                                            $kelasBtns = [
                                                '7' => ['label' => 'Kelas 7 (VII)', 'cls' => 'outline-success'],
                                                '8' => ['label' => 'Kelas 8 (VIII)', 'cls' => 'outline-warning'],
                                                '9' => ['label' => 'Kelas 9 (IX)', 'cls' => 'outline-primary'],
                                            ];
                                        } else {
                                            $kelasBtns = [
                                                'X' => ['label' => 'Kelas X', 'cls' => 'outline-success'],
                                                'XI' => ['label' => 'Kelas XI', 'cls' => 'outline-warning'],
                                                'XII' => ['label' => 'Kelas XII', 'cls' => 'outline-primary'],
                                            ];
                                        }
                                    ?>
                                    <?php foreach ($kelasBtns as $kVal => $kConf): ?>
                                        <button type="button" class="btn btn-<?= $kConf['cls'] ?> btn-sm flex-fill btn-kelas-generate" id="btnGenerateKelas<?= $kVal ?>" data-kelas="<?= $kVal ?>">
                                            <span class="spinner-border spinner-border-sm d-none" id="spinner<?= $kVal ?>"></span>
                                            <span id="text<?= $kVal ?>"><i class="bi bi-mortarboard me-1"></i><?= $kConf['label'] ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-info-circle me-1"></i>Generate bertahap per tingkatan kelas, hasilnya akan digabungkan.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Preview & Dashboard -->
                    <div class="col-lg-8">
                        <!-- Dashboard Monitoring -->
                        <div id="aiSekolahDashboard" class="d-none mb-3">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-2 text-center">
                                            <small class="text-muted d-block">CP Tercakup</small>
                                            <span class="fw-bold text-success" id="dashboardCpCount">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-2 text-center">
                                            <small class="text-muted d-block">Validasi</small>
                                            <span class="badge bg-success" id="dashboardValidasi">Valid</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-2 text-center">
                                            <small class="text-muted d-block">Iterasi</small>
                                            <span class="fw-bold text-primary" id="dashboardIterasi">1</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-2 text-center">
                                            <small class="text-muted d-block">Deviasi</small>
                                            <span class="fw-bold text-info" id="dashboardDeviasi">0%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-table me-1 text-success"></i>Preview Kisi-Kisi (Editable)</h6>
                            <small class="text-muted" id="aiSekolahPreviewMeta">Belum ada hasil.</small>
                        </div>
                        <div id="aiSekolahPreviewEmpty" class="text-center text-muted border rounded-3 p-5 bg-light">
                            <i class="bi bi-buildings fs-2 d-block mb-2 text-muted opacity-50"></i>
                            Pilih jumlah soal dan bentuk soal, lalu klik <strong>Generate</strong> per kelas.
                            <br><small class="text-muted">Sistem akan otomatis mendistribusikan soal dengan proporsi ketat.</small>
                        </div>
                        <div id="aiSekolahPreviewList" class="d-none overflow-auto" style="max-height:55vh;"></div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top">
                    <div class="me-auto text-danger small" id="aiSekolahQualityMessage"></div>
                    <button type="button" class="btn btn-success d-none" id="btnSaveAiSekolahGenerated">
                        <span class="spinner-border spinner-border-sm d-none" id="aiSekolahSaveSpinner"></span>
                        <span id="aiSekolahSaveText"><i class="bi bi-floppy me-1"></i>Simpan ke Kartu Soal</span>
                    </button>
                </div>
                    </div><!-- /#tabAiSekolah -->
                </div><!-- /#aiGeneratorTabsContent -->
            </div><!-- /.modal-body -->

            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>

            <!-- Loader: generate Per TP -->
            <div id="generateLoadingOverlay" class="d-none flex-column align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.85); z-index: 9999; backdrop-filter: blur(3px); border-radius: inherit;">
                <div class="text-center">
                    <div class="mb-4">
                        <div class="spinner-border text-primary" role="status" style="width: 4.5rem; height: 4.5rem; border-width: 0.35rem;"></div>
                    </div>
                    <h4 class="fw-bold text-primary mb-2">Sedang menyusun kisi-kisi soal...</h4>
                    <p class="text-muted text-center px-4" style="max-width: 400px;">Mohon tunggu sebentar, AI sedang menganalisis Tujuan Pembelajaran dan menyelaraskan dengan parameter Anda.</p>
                </div>
            </div>

            <!-- Loader: generate Ujian Sekolah -->
            <div id="generateSekolahLoadingOverlay" class="d-none flex-column align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); z-index: 9999; backdrop-filter: blur(3px); border-radius: inherit;">
                <div class="text-center">
                    <div class="mb-4">
                        <div class="spinner-border text-success" role="status" style="width: 4.5rem; height: 4.5rem; border-width: 0.35rem;"></div>
                    </div>
                    <h4 class="fw-bold text-success mb-2">Menyusun Kisi-Kisi Ujian Sekolah...</h4>
                    <p class="text-muted text-center px-4" style="max-width: 400px;">AI sedang mengoptimalkan distribusi soal untuk Kelas X, XI, dan XII sesuai proporsi Kurikulum Merdeka.</p>
                    <div class="mt-3">
                        <div class="progress" style="width: 300px; height: 6px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalTitle">Tambah butir soal</h5>
                <div class="d-flex align-items-center gap-2 ms-auto me-3">
                    <span class="badge bg-light text-dark border" id="autoSaveState">Manual Save</span>
                    <span class="spinner-border spinner-border-sm text-primary d-none" id="autoSaveSpinner"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="itemId">
                <!-- Seksi 1: Acuan kurikulum (TP → auto-isi field lain) -->
                <div class="ks-sec mb-2"><i class="bi bi-journal-bookmark me-1"></i>1 &middot; Acuan Kurikulum</div>
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <label class="form-label small fw-semibold mb-0">Tujuan Pembelajaran (TP) <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-link py-0 fw-semibold text-decoration-none" style="font-size:0.7rem;" id="btnSyncTp" title="Muat ulang data TP dari server">
                                <i class="bi bi-arrow-clockwise me-1"></i>Sync TP
                            </button>
                        </div>
                        <select class="form-select" id="itemTujuan">
                            <option value="">Pilih TP...</option>
                            <?php
                            $lastGroup = null;
                            foreach ($tujuan_options as $tp):
                                $groupKey = trim(($tp->kode_cp ?? '') . '|' . ($tp->elemen_cp ?? ''));
                                $groupLabel = '';
                                if ($tp->kode_cp ?? '') {
                                    $groupLabel = esc($tp->kode_cp) . ' — ' . esc(mb_strimwidth($tp->elemen_cp ?? '', 0, 50, '...'));
                                } else {
                                    $groupLabel = '(Tanpa CP)';
                                }
                                if ($groupKey !== $lastGroup):
                                    if ($lastGroup !== null): ?></optgroup><?php endif;
                                    $lastGroup = $groupKey; ?>
                                <optgroup label="<?= $groupLabel ?>">
                                <?php endif; ?>
                                <option value="<?= (int) ($tp->id_tujuan ?? 0) ?>"
                                    data-fase="<?= esc($tp->fase ?? '') ?>"
                                    data-kode-cp="<?= esc($tp->kode_cp ?? '') ?>"
                                    data-elemen-cp="<?= esc($tp->elemen_cp ?? '') ?>"
                                    data-materi-pokok="<?= esc($tp->materi_pokok ?? '') ?>"
                                    data-tujuan="<?= esc($tp->tujuan ?? '') ?>"
                                    data-tp-sikap="<?= esc($tp->tp_sikap ?? '') ?>"
                                    data-tp-keterampilan="<?= esc($tp->tp_keterampilan ?? '') ?>"
                                    data-kktp="<?= esc($tp->kriteria_ketercapaian ?? '') ?>"
                                    data-level="<?= esc($tp->level_kognitif ?? '') ?>"
                                    data-tingkat="<?= esc($tp->tingkat ?? '') ?>"
                                >[<?= esc($tp->level_kognitif ?? '?') ?>] <?= esc($tp->kode ?? '') ?> — <?= esc(mb_strimwidth($tp->tujuan ?? '', 0, 50, '...')) ?> <?= !empty($tp->kriteria_ketercapaian) ? '(KKTP: ' . esc(mb_strimwidth($tp->kriteria_ketercapaian, 0, 35, '...')) . ')' : '' ?></option>
                            <?php endforeach;
                            if ($lastGroup !== null): ?></optgroup><?php endif; ?>
                        </select>
                        <div class="form-text mt-1" id="tpAutoFillHint" style="font-size:0.7rem;">
                            <i class="bi bi-magic me-1 text-primary"></i>Memilih TP otomatis mengisi CP, Fase, Materi, Level, dan draf Indikator.
                        </div>
                        <div id="tpDetailPreview" class="d-none mt-2 p-2 border rounded" style="background-color:#f8fafc; font-size:0.75rem;">
                            <!-- Konten preview TP akan diisi oleh JS -->
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">CP</label>
                        <input type="text" class="form-control" id="itemCp" placeholder="Otomatis dari TP">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Fase</label>
                        <input type="text" class="form-control" id="itemFase" placeholder="Otomatis" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">No Soal <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control" id="itemNomor" placeholder="Otomatis">
                        <div id="nextNomorHint" class="form-text text-primary small mt-1" style="font-size:0.72rem;"></div>
                    </div>
                </div>
                <!-- Seksi 2: Isi kisi-kisi -->
                <div class="ks-sec mb-2"><i class="bi bi-card-text me-1"></i>2 &middot; Isi Kisi-kisi</div>
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Materi / Topik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="itemMateri" placeholder="Contoh: Sistem persamaan linear dua variabel">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Indikator Soal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="itemIndikator" placeholder="Contoh: Peserta didik mampu menganalisis …">
                        <div class="form-text" style="font-size:0.7rem;">Awali dengan KKO Bloom (menyebutkan, menjelaskan, menerapkan, …).</div>
                    </div>
                </div>
                <!-- Seksi 3: Atribut soal -->
                <div class="ks-sec mb-2"><i class="bi bi-sliders me-1"></i>3 &middot; Atribut Soal</div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="itemLevel">
                            <option value="">Pilih...</option>
                            <option value="C1">C1 - Mengingat</option>
                            <option value="C2">C2 - Memahami</option>
                            <option value="C3">C3 - Menerapkan</option>
                            <option value="C4">C4 - Menganalisis</option>
                            <option value="C5">C5 - Mengevaluasi</option>
                            <option value="C6">C6 - Mencipta</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Bentuk Soal <span class="text-danger">*</span></label>
                        <select class="form-select" id="itemJenis">
                            <option value="1">Pilihan Ganda</option>
                            <option value="2">Pilihan Ganda Kompleks</option>
                            <option value="3">Menjodohkan</option>
                            <option value="4">Isian Singkat</option>
                            <option value="5">Uraian / Essay</option>
                            <option value="6">Urutan</option>
                            <option value="7">Isian Rumpang</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Waktu (mnt)</label>
                        <input type="number" min="1" max="60" class="form-control" id="itemWaktu" placeholder="5" value="5">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="me-auto text-danger small" id="validationGlobal"></div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveItem">
                    <span class="spinner-border spinner-border-sm d-none" id="itemSaveSpinner"></span>
                    <span id="itemSaveText">Simpan Butir</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ================================================================
     WIDGET ANALISIS KUALITAS KISI-KISI (Sprint 1-3)
     Menampilkan distribusi Bloom dan proporsionalitas materi.
     Muncul di bawah konten utama, collapsed by default.
================================================================ -->
<div class="container-fluid mt-2 mb-4" id="widget-kualitas-kartu">
  <div class="accordion" id="accordionKualitas">

    <!-- Panel Distribusi Bloom -->
    <div class="accordion-item border-0 shadow-sm mb-2" style="border-radius:12px;overflow:hidden">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed fw-semibold" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseBloom"
                style="border-radius:12px">
          <i class="bi bi-bar-chart-fill me-2 text-primary"></i>
          Analisis Distribusi Taksonomi Bloom
          <span id="bloom-badge" class="ms-2 badge bg-secondary">—</span>
        </button>
      </h2>
      <div id="collapseBloom" class="accordion-collapse collapse">
        <div class="accordion-body">
          <div class="row g-3 align-items-start">

            <!-- Bars distribusi -->
            <div class="col-md-6">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-semibold text-muted">Level Kognitif</span>
                <select id="bloom-tipe-ujian" class="form-select form-select-sm" style="width:160px">
                  <option value="ulangan_harian">Ulangan Harian</option>
                  <option value="uts" selected>UTS / PTS</option>
                  <option value="uas">UAS / PAS</option>
                  <option value="us">Ujian Sekolah</option>
                  <option value="anbk">ANBK</option>
                </select>
              </div>
              <?php foreach (['C1'=>['secondary','Mengingat'],'C2'=>['info','Memahami'],'C3'=>['primary','Menerapkan'],'C4'=>['warning','Menganalisis'],'C5'=>['danger','Mengevaluasi'],'C6'=>['success','Mencipta']] as $lv => [$color, $label]): ?>
              <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                  <span><span class="badge bg-<?= $color ?> me-1"><?= $lv ?></span><?= $label ?></span>
                  <span>
                    <span id="bloom-n-<?= $lv ?>" class="text-muted">—</span>
                    <span class="text-muted mx-1">|</span>
                    <span id="bloom-pct-<?= $lv ?>" class="fw-semibold">—%</span>
                    <span class="text-muted small ms-1" id="bloom-target-<?= $lv ?>"></span>
                  </span>
                </div>
                <div class="progress" style="height:10px;border-radius:5px">
                  <div id="bloom-bar-<?= $lv ?>"
                       class="progress-bar bg-<?= $color ?> progress-bar-striped"
                       style="width:0%;transition:width .5s ease" role="progressbar"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <!-- Ringkasan & rekomendasi -->
            <div class="col-md-6">
              <div class="row g-2 mb-3">
                <div class="col-4 text-center">
                  <div class="fw-bold fs-5 text-secondary" id="bloom-sum-lots">—%</div>
                  <div class="small text-muted">LOTS (C1–C2)</div>
                </div>
                <div class="col-4 text-center">
                  <div class="fw-bold fs-5 text-primary" id="bloom-sum-mots">—%</div>
                  <div class="small text-muted">MOTS (C3)</div>
                </div>
                <div class="col-4 text-center">
                  <div class="fw-bold fs-5" id="bloom-sum-hots">—%</div>
                  <div class="small text-muted">HOTS (C4–C6)</div>
                </div>
              </div>
              <div id="bloom-rekomendasi">
                <div class="text-muted small text-center py-2">
                  <i class="bi bi-arrow-repeat me-1"></i>Klik "Muat Analisis" untuk memulai.
                </div>
              </div>
              <div class="mt-3 text-end">
                <button class="btn btn-sm btn-outline-primary" id="btn-muat-bloom">
                  <span class="spinner-border spinner-border-sm d-none me-1" id="spin-bloom"></span>
                  <i class="bi bi-arrow-clockwise me-1"></i>Muat Analisis
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel Validasi Materi -->
    <div class="accordion-item border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed fw-semibold" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseMateri"
                style="border-radius:12px">
          <i class="bi bi-book me-2 text-success"></i>
          Ketercakupan Materi & Tujuan Pembelajaran
          <span id="materi-badge" class="ms-2 badge bg-secondary">—</span>
        </button>
      </h2>
      <div id="collapseMateri" class="accordion-collapse collapse">
        <div class="accordion-body">
          <div id="materi-content">
            <div class="text-muted small text-center py-2">
              <i class="bi bi-arrow-repeat me-1"></i>Klik "Muat Analisis" untuk memeriksa ketercakupan materi.
            </div>
          </div>
          <div class="mt-2 text-end">
            <button class="btn btn-sm btn-outline-success" id="btn-muat-materi">
              <span class="spinner-border spinner-border-sm d-none me-1" id="spin-materi"></span>
              <i class="bi bi-search me-1"></i>Muat Analisis
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.KARTU_SOAL_DETAIL = {
        uuid          : <?= json_encode($kartu->uuid) ?>,
        kelasLevel    : <?= json_encode($kelas->level_id ?? '') ?>,
        canManage     : <?= !empty($can_manage) ? 'true' : 'false' ?>,
        isEditable    : <?= $isEditable ? 'true' : 'false' ?>,
        hasTujuan     : <?= !empty($tujuan_options) ? 'true' : 'false' ?>,
        workflowStatus: <?= json_encode($wf) ?>,
        labels: {
            1: 'Pilihan Ganda',
            2: 'Pilihan Ganda Kompleks',
            3: 'Menjodohkan',
            4: 'Isian Singkat',
            5: 'Uraian / Essay',
            6: 'Urutan',
            7: 'Isian Rumpang'
        },
        summaryJenis: <?= json_encode($summary_jenis ?? []) ?>,
        kartuId     : <?= (int) ($kartu->id_kartu ?? 0) ?>,
        mapelId     : <?= (int) ($kartu->mapel_id ?? 0) ?>,
        tpId        : <?= (int) ($kartu->tp_id ?? 0) ?>,
        smtId       : <?= (int) ($kartu->smt_id ?? 0) ?>,
        judul       : <?= json_encode($kartu->judul ?? '') ?>,
        urlGeneratePerKelas: <?= json_encode(site_url('cbtkartusoal/api/generate-per-kelas')) ?>,
        urlSaveGenerated   : <?= json_encode(site_url('cbtkartusoal/save-generated')) ?>,
        urlResyncTp        : <?= json_encode(site_url('cbtkartusoal/resyncItemsFromTp/')) ?>,
        urlGenerateBank    : <?= json_encode(site_url('cbtkartusoal/generateBankFromKartu')) ?>,
        paketBanks: <?= json_encode($paket_banks ?? []) ?>
    };
</script>
<script src="<?= base_url('assets/app/js/cbt/kartusoal/detail-manager.js') ?>?v=<?= time() ?>"></script>
<script src="<?= base_url('assets/app/js/cbt/kartusoal/detail-preview.js') ?>?v=<?= time() ?>"></script>
<script src="<?= base_url('assets/app/js/cbt/kartusoal/detail-ai-sekolah.js') ?>?v=<?= time() ?>"></script>

<style>
    .ai-model-btn {
        transition: all 0.2s ease;
        border-color: #dee2e6;
        background-color: #f8f9fa;
        color: #495057;
    }
    .ai-model-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        background-color: #fff;
        border-color: #8b5cf6;
        color: #8b5cf6;
    }
    .ai-model-btn.active {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
        border-color: transparent !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
    }
    .ai-model-btn small, .ai-model-btn span {
        transition: opacity 0.2s;
    }
    
    .ai-sekolah-model-btn {
        transition: all 0.2s ease;
        border-color: #dee2e6;
        background-color: #f8f9fa;
        color: #495057;
    }
    .ai-sekolah-model-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        background-color: #fff;
        border-color: #10b981;
        color: #10b981;
    }
    .ai-sekolah-model-btn.active {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
        border-color: transparent !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
    }
</style>

<!-- ================================================================
     SCRIPT WIDGET KUALITAS KISI-KISI (Sprint 1-3)
================================================================ -->
<script src="<?= base_url('assets/app/js/cbt/kartusoal/detail-analysis.js') ?>?v=<?= time() ?>"></script>
<script src="<?= base_url('assets/app/js/cbt/kartusoal/detail-bank.js') ?>?v=<?= time() ?>"></script>

<!-- Modal Generate Bank Soal (Paket Soal Baru) dari Kartu Soal -->
<div class="modal fade" id="modalGenerateBankFromKartu" tabindex="-1" aria-labelledby="modalGenBankTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white py-3 rounded-top-4">
                <h5 class="modal-title fw-bold fs-6" id="modalGenBankTitle">
                    <i class="bi bi-box-seam me-2"></i>Buat Paket Soal / Bank Soal Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formGenBank" onsubmit="submitGenerateBankForm(event)">
                <input type="hidden" name="kartu_id" value="<?= (int)$kartu->id_kartu ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Fitur ini akan membuat <strong>Paket Soal Baru (Bank Soal)</strong> dan secara otomatis mengkloning butir soal dari Kisi-kisi Kartu Soal <strong><?= esc($kartu->judul ?? '') ?></strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Kode Bank Soal / Paket <span class="text-danger">*</span></label>
                        <input type="text" name="bank_kode" id="gen_bank_kode" class="form-control form-control-sm" placeholder="Contoh: PAS-MAT11-A" required>
                        <small class="text-muted">Kode unik paket soal ujian.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Nama Paket Soal</label>
                        <input type="text" name="bank_nama" id="gen_bank_nama" class="form-control form-control-sm" placeholder="Contoh: Paket Soal A - PAS Matematika Kelas 11">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Jenis Sub-Tes</label>
                        <select name="subtes_code" id="gen_subtes_code" class="form-select form-select-sm">
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
                        <select name="mode_soal" id="gen_mode_soal" class="form-select form-select-sm">
                            <option value="all">🟢 Semua Soal (100% Butir Kisi-kisi)</option>
                            <option value="ganjil">🔵 Nomor Ganjil Sahaja (Paket A)</option>
                            <option value="genap">🟠 Nomor Genap Sahaja (Paket B)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="acak_opsi" id="gen_acak_opsi" value="1">
                        <label class="form-check-label small fw-semibold text-dark" for="gen_acak_opsi">
                            🔀 Acak Urutan Pilihan Jawaban (A-E)
                        </label>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label fw-bold small text-dark">Jumlah Paket Soal</label>
                        <select name="jumlah_paket" id="gen_jumlah_paket" class="form-select form-select-sm">
                            <option value="1">1 paket (kloning kartu saja)</option>
                        </select>
                        <small class="text-muted">Paket A = kloning butir kartu (tanpa AI).</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success px-4 fw-bold" id="btnSubmitGenBank">
                        <i class="bi bi-box-seam me-1"></i>Buat Paket Soal Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Overlay Progres Generate Paket dari Kartu -->
<div class="modal fade" id="modalGenBankProgress" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
            <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status"></div>
            <h6 class="mt-3 mb-1 fw-bold">Membuat paket soal...</h6>
            <p class="text-muted small mb-2" id="gen_bank_progress_txt">Menyiapkan paket...</p>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 100%"></div>
            </div>
            <p class="text-muted small mt-3 mb-0">
                Bisa memakan waktu beberapa menit. <strong>Jangan tutup halaman</strong> selama proses berjalan.
            </p>
        </div>
    </div>
</div>

<!-- Modal Preview Contoh Soal & Detail Indikator (Tampilan UI Presisi) -->
<div class="modal fade" id="modalPreviewContohSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem;">
            <!-- Header Modal -->
            <div class="modal-header border-0 text-white p-3 px-4" style="background: linear-gradient(135deg, #3b82f6 0%, #4f46e5 100%); border-top-left-radius: 1.25rem; border-top-right-radius: 1.25rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-primary shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-search fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Preview Soal</h5>
                        <span class="smaller opacity-75 d-block">Detail Indikator &amp; Preview Soal</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body Modal -->
            <div class="modal-body p-0 bg-light" style="border-radius: 0 0 1.25rem 1.25rem;">
                <!-- Paket tab selector (terlihat jika ada paket dibuat dari kartu ini) -->
                <div class="d-none border-bottom bg-white px-3 py-2" id="prevPaketTabs" style="gap:6px;display:flex;flex-wrap:wrap;align-items:center;"></div>

                <div class="p-4">
                <div id="prevKartuSection">
                <!-- 1. Hierarki Indikator Card -->
                <div class="card border-0 mb-4 shadow-sm" style="border-radius: 0.85rem; background: #eff6ff;">
                    <div class="card-body p-3.5">
                        <div class="d-flex align-items-center gap-2 mb-3 text-primary fw-bold">
                            <i class="bi bi-layers-fill fs-5"></i>
                            <span class="fs-6">Hierarki Indikator</span>
                        </div>
                        <div class="row g-2 text-dark" style="font-size: 0.875rem;">
                            <div class="col-sm-3 fw-bold text-secondary">Elemen:</div>
                            <div class="col-sm-9 fw-semibold text-dark" id="prev_elemen">-</div>

                            <div class="col-sm-3 fw-bold text-secondary">Subelemen:</div>
                            <div class="col-sm-9 text-dark" id="prev_subelemen">-</div>

                            <div class="col-sm-3 fw-bold text-secondary">Kompetensi:</div>
                            <div class="col-sm-9 text-dark" id="prev_kompetensi">-</div>

                            <div class="col-sm-3 fw-bold text-secondary">Indikator:</div>
                            <div class="col-sm-9 fw-semibold text-primary" id="prev_indikator">-</div>
                        </div>
                    </div>
                </div>

                <!-- 2. Detail Soal Card -->
                <div class="card shadow-sm" style="border: 1.5px solid #c084fc; border-radius: 0.85rem;">
                    <div class="card-body p-4">
                        <!-- Metadata Header -->
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <span class="font-monospace small text-muted fw-bold" id="prev_kode_soal">SOAL-00000</span>
                            <span class="badge px-3 py-1.5 rounded-pill border fw-semibold" style="background: #ffedd5; color: #c2410c; border-color: #fed7aa !important;" id="prev_bentuk_soal">
                                Bentuk Soal : Pilihan Ganda
                            </span>
                        </div>

                        <!-- Stimulus & Pertanyaan -->
                        <div class="soal-content text-dark mb-4" id="prev_butir_soal" style="line-height: 1.6; font-size: 0.95rem;">
                            <!-- Teks Soal / Stimulus -->
                        </div>

                        <!-- Pilihan Jawaban -->
                        <div class="d-flex flex-column gap-2" id="prev_options_container">
                            <!-- Option cards -->
                        </div>
                    </div>
                </div>
                </div>

                <!-- 3. Soal dari paket/bank (per nomor yang sama di tiap paket) -->
                <div class="d-none" id="prevPaketSection">
                    <div class="card shadow-sm" style="border: 1.5px solid #60a5fa; border-radius: 0.85rem;">
                        <div class="card-body p-4" id="prevPaketBody"></div>
                    </div>
                </div>
                <input type="hidden" id="prevPaketNomor" value="">
                <input type="hidden" id="prevPaketJenis" value="">
                </div><!-- /.p-4 -->
            </div><!-- /.modal-body -->

            <!-- Footer Modal -->
            <div class="modal-footer border-0 bg-white px-4 py-3" style="border-bottom-left-radius: 1.25rem; border-bottom-right-radius: 1.25rem;">
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-dismiss="modal" style="background: #3b82f6;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lihat Soal Semua Paket (bandingkan soal antar paket dari kartu yang sama) -->
<div class="modal fade" id="modalLihatPaketSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem; max-height: 88vh;">
            <div class="modal-header border-0 text-white p-3 px-4" style="background: linear-gradient(135deg, #3b82f6 0%, #4f46e5 100%); border-top-left-radius: 1.25rem; border-top-right-radius: 1.25rem;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-primary shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-collection-play fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Lihat Soal Semua Paket</h5>
                        <span class="smaller opacity-75 d-block"><?= esc($kartu->judul ?? 'Kartu Soal') ?> — bandingkan / periksa soal antar paket</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="border-bottom px-3 py-2 bg-light d-flex flex-wrap align-items-center gap-2" id="lapaketTabs"></div>
            <div class="overflow-auto" style="max-height: calc(88vh - 150px);">
                <div class="modal-body p-3 bg-light" id="lapaketBody">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                        Pilih paket untuk melihat soal.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-white px-4 py-3" style="border-bottom-left-radius: 1.25rem; border-bottom-right-radius: 1.25rem;">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

