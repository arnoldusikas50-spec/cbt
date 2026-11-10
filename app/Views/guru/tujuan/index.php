<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Content Header (Page Header) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-bullseye text-primary me-2"></i>
                    Tujuan Pembelajaran (TP)
                </h3>
                <p class="text-muted small mb-0 mt-1">
                    <i class="bi bi-calendar3 me-1"></i><?= esc($tp_active->tahun ?? '-') ?> — Semester <?= esc($smt_active->id_smt ?? '-') ?>
                </p>
            </div>
            <div class="col-sm-6">
                <div class="d-flex align-items-center justify-content-sm-end gap-2 flex-wrap">
                    <?php if ($is_admin || $is_panitia): ?>
                        <div class="input-group input-group-sm" style="max-width: 240px;">
                            <span class="input-group-text bg-light text-primary"><i class="bi bi-person-workspace"></i></span>
                            <select class="form-select shadow-none" id="id_guru_monitor" aria-label="Pantau guru" onchange="changeGuru(this.value)">
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($gurus as $id_g => $nama_g): ?>
                                    <option value="<?= $id_g ?>" <?= (isset($id_guru_selected) && $id_guru_selected == $id_g) ? 'selected' : '' ?>>
                                        <?= $nama_g ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <ol class="breadcrumb mb-0 bg-transparent">
                        <li class="breadcrumb-item">
                            <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                                <i class="bi bi-house-door me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Tujuan Pembelajaran</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Main Card Container -->
        <div class="card card-default my-shadow mb-4 border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="bi bi-journal-plus fs-5"></i>
                            </span>
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Data Tujuan Pembelajaran</h5>
                                <small class="text-muted">Rumuskan TP yang terukur sesuai pembelajaran mendalam</small>
                            </div>
                        </div>

                        <!-- Bulk Actions Toolbar (Muncul saat ada checkbox terpilih) -->
                        <div id="bulk-actions" class="d-none ms-sm-3 d-flex align-items-center gap-2 p-1 bg-light border rounded-pill px-3 animate__animated animate__fadeIn">
                            <span class="small fw-semibold text-muted me-1">
                                <span id="count-checked" class="text-primary fw-bold">0</span> dipilih:
                            </span>
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap" onclick="bulkToggleStatus('aktif')" title="Aktifkan TP Terpilih">
                                <i class="bi bi-check-circle me-1"></i>Aktifkan
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-2.5 py-1 text-nowrap" onclick="bulkToggleStatus('nonaktif')" title="Nonaktifkan TP Terpilih">
                                <i class="bi bi-x-circle me-1"></i>Nonaktifkan
                            </button>
                            <button id="btn-bulk-delete" type="button" class="btn btn-sm btn-danger rounded-pill px-2.5 py-1 text-nowrap" onclick="bulkDelete()" title="Hapus TP Terpilih">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none px-1 text-nowrap" onclick="clearTpSelection()" title="Batalkan pilihan">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-2 align-items-center ms-auto">
                        <?php
                            $exportParams = [];
                            if ($id_guru_selected) $exportParams['id_guru'] = $id_guru_selected;
                            if ($mapel_selected) $exportParams['mapel'] = $mapel_selected;
                            if (!empty($tingkat_selected)) $exportParams['tingkat'] = $tingkat_selected;
                            $exportQs = $exportParams ? '?' . http_build_query($exportParams) : '';
                        ?>

                        <!-- Dropdown Ekspor & Impor -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 dropdown-toggle d-inline-flex align-items-center gap-1 shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-arrow-down-up me-1"></i>Ekspor / Impor
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-2" style="font-size: 0.85rem;">
                                <li><h6 class="dropdown-header text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Ekspor Data</h6></li>
                                <li>
                                    <a class="dropdown-item py-1.5" href="<?= base_url('guru/tujuan/export' . $exportQs) ?>" target="_blank">
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>Ekspor Dokumen PDF
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-1.5" href="<?= base_url('guru/tujuan/export_excel' . $exportQs) ?>" target="_blank">
                                        <i class="bi bi-file-earmark-excel text-success me-2"></i>Ekspor File Excel
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><h6 class="dropdown-header text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Impor &amp; Template</h6></li>
                                <li>
                                    <a class="dropdown-item py-1.5" href="javascript:void(0)" onclick="openImportModal()">
                                        <i class="bi bi-upload text-primary me-2"></i>Impor TP dari Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-1.5" href="<?= base_url('guru/tujuan/download_template') ?>">
                                        <i class="bi bi-download text-muted me-2"></i>Unduh Format Template Excel
                                    </a>
                                </li>
                            </ul>
                        </div>


                        <!-- Salin TP -->
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 d-inline-flex align-items-center shadow-xs" onclick="openCopyModal()" title="Salin TP dari Semester Lain">
                            <i class="bi bi-copy me-1.5"></i>Salin TP
                        </button>

                        <!-- Salin TP dari Guru Lain (hanya admin/panitia) -->
                        <?php if ($is_admin || $is_panitia): ?>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 d-inline-flex align-items-center shadow-xs ms-2" onclick="openCopyGuruModal()" title="Salin TP dari Guru Lain (Mapel Sama)">
                            <i class="bi bi-person-badge me-1.5"></i>Dari Guru Lain
                        </button>
                        <?php endif; ?>

                        <!-- Tambah TP Utama -->
                        <button type="button" class="btn btn-sm btn-primary fw-bold rounded-pill px-3.5 shadow-sm d-inline-flex align-items-center" onclick="modalAdd()" id="btn-tambah-tp">
                            <i class="bi bi-plus-lg me-1.5"></i>Tambah TP
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Toolbar & Live Search -->
            <div class="border-bottom bg-light bg-opacity-75 px-3 py-2.5">
                <form action="" method="get" id="filter-form" class="row g-2 align-items-center">
                    <?php if ($is_admin || $is_panitia): ?>
                    <div class="col-auto">
                        <select name="id_guru" id="filter-guru" aria-label="Filter guru" class="form-select form-select-sm border-primary-subtle shadow-none rounded-3" onchange="this.form.submit()" style="min-width:180px;">
                            <option value="">— Semua Guru —</option>
                            <?php foreach ($gurus as $id => $nama): ?>
                                <option value="<?= $id ?>" <?= ($id_guru_selected == $id) ? 'selected' : '' ?>><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="col-auto">
                        <select name="mapel" id="filter-mapel" aria-label="Filter mata pelajaran" class="form-select form-select-sm shadow-none rounded-3" onchange="this.form.submit()" style="min-width:160px;">
                            <option value="">— Semua Mapel —</option>
                            <?php foreach ($mapel_list as $id => $nama): ?>
                                <option value="<?= $id ?>" <?= $mapel_selected == $id ? 'selected' : '' ?>><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-auto">
                        <select name="tingkat" id="filter-tingkat" aria-label="Filter kelas" class="form-select form-select-sm shadow-none rounded-3" onchange="this.form.submit()" style="min-width:110px;">
                            <option value="">— Semua Kelas —</option>
                            <option value="10" <?= ($tingkat_selected ?? '') === '10' ? 'selected' : '' ?>>Kls 10</option>
                            <option value="11" <?= ($tingkat_selected ?? '') === '11' ? 'selected' : '' ?>>Kls 11</option>
                            <option value="12" <?= ($tingkat_selected ?? '') === '12' ? 'selected' : '' ?>>Kls 12</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <select name="semester" id="filter-semester" aria-label="Filter semester" class="form-select form-select-sm shadow-none rounded-3" onchange="this.form.submit()" style="min-width:125px;">
                            <option value="">— Semua Semester —</option>
                            <option value="1" <?= ($semester_selected ?? '') == '1' ? 'selected' : '' ?>>1 (Ganjil)</option>
                            <option value="2" <?= ($semester_selected ?? '') == '2' ? 'selected' : '' ?>>2 (Genap)</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <select name="status" id="filter-status" aria-label="Filter status" class="form-select form-select-sm shadow-none rounded-3" onchange="this.form.submit()" style="min-width:110px;">
                            <option value="">— Status —</option>
                            <option value="aktif" <?= ($status_selected ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= ($status_selected ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>

                    <!-- Live Search Input -->
                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="live-search-tp" aria-label="Cari tujuan pembelajaran" class="form-control form-control-sm border-start-0 border-end-0 shadow-none" placeholder="Cari TP, kode, mapel... ( / )" oninput="onLiveSearchInput()" style="min-width:180px;" autocomplete="off">
                            <button class="btn btn-outline-secondary bg-white border-start-0 text-muted rounded-end-3 d-none" type="button" id="btn-clear-search" onclick="clearLiveSearch()" title="Hapus pencarian (Esc)">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>

                    <?php
                        $hasActiveFilter = (!empty($mapel_selected) || !empty($tingkat_selected) || ($semester_selected !== '' && $semester_selected !== null) || !empty($status_selected) || (!empty($id_guru_selected) && ($is_admin || $is_panitia)));
                    ?>
                    <?php if ($hasActiveFilter): ?>
                    <div class="col-auto">
                        <a href="<?= site_url('guru/tujuan') ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 d-inline-flex align-items-center" title="Reset Semua Filter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-auto ms-auto d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.8rem;" id="filterActiveBadge">
                            <i class="bi bi-filter-circle-fill me-1"></i><span id="tp-count-badge"><?= count($tujuan_list ?? []) ?></span> TP Ditemukan
                        </span>
                    </div>
                </form>
            </div>

            <?php if (!empty($stats)): ?>
            <!-- Strip ringkasan: sebaran TP per mapel + filter cepat -->
            <div class="px-3 py-2 border-bottom bg-white d-flex align-items-center gap-2 overflow-auto" id="tp-stats-strip">
                <small class="text-muted fw-semibold text-nowrap"><i class="bi bi-pie-chart me-1"></i>Ringkasan:</small>
                <button type="button" class="tp-chip <?= empty($mapel_selected) ? 'active' : '' ?>" onclick="filterByMapel('')" title="Tampilkan semua mapel">
                    Semua <span class="tp-chip-n"><?= count($tujuan_list ?? []) ?></span>
                </button>
                <?php foreach ($stats as $mid => $s): ?>
                    <button type="button" class="tp-chip <?= ((string)$mapel_selected === (string)$mid) ? 'active' : '' ?>" onclick="filterByMapel('<?= $mid ?>')" title="Tampilkan TP <?= esc($s['nama']) ?>">
                        <?= esc($s['nama']) ?> <span class="tp-chip-n"><?= $s['count'] ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="card-body p-0">
                <div class="table-responsive mb-0 p-0 border-0">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;" id="tpTable">
                    <thead>
                        <tr class="table-light text-nowrap" style="border-bottom: 2px solid #e2e8f0;">
                            <th width="40" class="text-center py-3">
                                <input type="checkbox" class="form-check-input" id="check-all" title="Pilih semua di halaman ini">
                            </th>
                            <th width="5%" class="text-center fw-bold text-secondary">No</th>
                            <th width="13%" class="fw-bold text-secondary th-sort" onclick="toggleTpSort('nama')" title="Klik untuk mengurutkan berdasar mapel">Mata Pelajaran<span class="sort-ind" id="sort-ind-nama"></span></th>
                            <th width="9%" class="fw-bold text-secondary">Kelas</th>
                            <th width="8%" class="fw-bold text-secondary th-sort" onclick="toggleTpSort('kode')" title="Klik untuk mengurutkan berdasar kode">Kode TP<span class="sort-ind" id="sort-ind-kode"></span></th>
                            <th class="fw-bold text-secondary">Rumusan Tujuan Pembelajaran</th>
                            <th width="8%" class="text-center fw-bold text-secondary">Status</th>
                            <th width="11%" class="text-center fw-bold text-secondary">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tpTableBody">
                        <?php if (empty($tujuan_list)): ?>
                            <tr id="tp-empty-initial">
                                <td colspan="8" class="text-center text-muted py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px; background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                            <i class="bi bi-clipboard-x text-primary" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Belum ada data Tujuan Pembelajaran</h6>
                                        <p class="text-muted small mb-3 mx-auto" style="max-width: 380px;">
                                            Rumuskan TP yang terukur: diawali kata kerja operasional + konten/materi yang jelas, sesuai pembelajaran mendalam (berkesadaran, bermakna, menggembirakan).
                                        </p>
                                        <div class="d-flex gap-2">
                                            <button onclick="modalAdd()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                                <i class="bi bi-plus-lg me-1"></i>Tambah Manual
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tujuan_list as $i => $tp): ?>
                                <?php
                                $isActive = $tp->is_active ?? 1;
                                $isUsed = (($tp->used_in_kartu ?? 0) > 0 || ($tp->used_in_soal ?? 0) > 0);
                                ?>
                                <tr class="tp-row <?= $isUsed ? 'bg-light bg-opacity-25' : '' ?>" data-mapel="<?= $tp->id_mapel ?>" data-tingkat="<?= $tp->tingkat ?>" data-status="<?= $isActive ? 'aktif' : 'nonaktif' ?>" data-nama="<?= esc(mb_strtolower($tp->nama_mapel ?? '')) ?>" data-kode="<?= esc(mb_strtolower($tp->kode ?? '')) ?>" data-search="<?= esc(mb_strtolower($tp->kode . ' ' . $tp->tujuan . ' ' . ($tp->materi_pokok ?? '') . ' ' . ($tp->nama_mapel ?? ''))) ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input check-item" value="<?= $tp->id_tujuan ?>" <?= $isUsed ? 'data-used="1" title="Sudah digunakan di Kartu Soal / Bank Soal"' : '' ?>>
                                    </td>
                                    <td class="text-center fw-semibold text-muted font-monospace tp-num"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= esc($tp->nama_mapel) ?></div>
                                        <?php if (!empty($tp->materi_pokok)): ?>
                                            <small class="text-muted d-block text-truncate" style="max-width: 170px;" title="<?= esc($tp->materi_pokok) ?>">
                                                <i class="bi bi-tag me-1"></i><?= esc($tp->materi_pokok) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($tp->tingkat > 0): ?>
                                            <span class="badge bg-light text-dark border rounded-pill" style="font-size: 0.7rem;">Kls <?= $tp->tingkat ?></span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.75rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.75rem;"><?= esc($tp->kode) ?: '-' ?></span>
                                    </td>
                                    <td class="small">
                                        <div class="fw-medium text-dark tp-text" style="line-height: 1.45;" onclick="this.closest('tr').classList.toggle('expanded');syncMoreLabel(this)" title="Klik untuk membentangkan/menciutkan">
                                            <?php if ($isUsed): ?>
                                                <i class="bi bi-link-45deg text-info me-1" title="Terdapat di Kartu Soal / Bank Soal (masih bisa diedit)"></i>
                                            <?php endif; ?>
                                            <?= esc($tp->tujuan) ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <button type="button" class="tp-more d-none" onclick="this.closest('tr').classList.toggle('expanded');syncMoreLabel(this.closest('tr').querySelector('.tp-text'))">Selengkapnya</button>
                                            <button type="button" class="tp-copy" onclick="copyTpText(this)" title="Salin rumusan TP" aria-label="Salin rumusan TP"><i class="bi bi-clipboard"></i></button>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isUsed && $isActive): ?>
                                            <button type="button" class="tp-switch is-locked" aria-checked="true" aria-disabled="true" title="Terkunci: sudah digunakan di Kartu Soal / Bank Soal" onclick="Swal.fire('Informasi', 'TP yang sudah digunakan di Kartu Soal / Bank Soal tidak dapat dinonaktifkan.', 'info')">
                                                <span class="track"></span><span class="lbl">Aktif</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="tp-switch" role="switch" aria-checked="<?= $isActive ? 'true' : 'false' ?>" aria-label="Ubah status TP <?= esc($tp->kode ?: $tp->id_tujuan) ?>" title="<?= $isActive ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan' ?>" onclick="toggleStatusSwitch(this, <?= $tp->id_tujuan ?>)">
                                                <span class="track"></span><span class="lbl"><?= $isActive ? 'Aktif' : 'Nonaktif' ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-action-group justify-content-center">
                                            <button type="button" onclick="duplicateTp(<?= $tp->id_tujuan ?>)" class="btn btn-action btn-action-magic" title="Duplikat TP" aria-label="Duplikat TP <?= esc($tp->kode ?: $tp->id_tujuan) ?>">
                                                <i class="bi bi-files"></i>
                                            </button>

                                            <button type="button" onclick='editTp(<?= json_encode($tp) ?>)' class="btn btn-action btn-action-edit" title="Edit TP" aria-label="Edit TP <?= esc($tp->kode ?: $tp->id_tujuan) ?>">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>

                                            <?php if ($isUsed): ?>
                                                <button type="button" class="btn btn-action btn-action-delete opacity-50" style="cursor: not-allowed;" onclick="Swal.fire('Informasi', 'Tujuan Pembelajaran yang sudah digunakan di Kartu Soal / Bank Soal tidak dapat dihapus.', 'info')" title="Terkunci (Sudah Digunakan)" aria-label="Hapus dikunci">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" onclick="hapus(<?= $tp->id_tujuan ?>)" class="btn btn-action btn-action-delete" title="Hapus TP" aria-label="Hapus TP <?= esc($tp->kode ?: $tp->id_tujuan) ?>">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Empty state row untuk live search -->
                        <tr id="tp-empty-search" style="display: none;">
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-search text-muted mb-2" style="font-size: 2.2rem; opacity: 0.5;"></i>
                                    <h6 class="fw-bold text-dark mb-1">Tidak ada Tujuan Pembelajaran yang cocok</h6>
                                    <p class="text-muted small mb-2">Coba kata kunci pencarian lain atau bersihkan pencarian.</p>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="clearLiveSearch()">
                                        <i class="bi bi-x-circle me-1"></i>Bersihkan pencarian
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Card Footer: Pagination Controls & Information -->
            <div class="card-footer bg-white border-top py-3 px-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3" id="tp-pagination-container">
                    <!-- Showing info + Rows per page -->
                    <div class="d-flex align-items-center flex-wrap gap-2 small text-muted">
                        <div>
                            Menampilkan <span id="pagination-from" class="fw-bold text-dark">0</span> – <span id="pagination-to" class="fw-bold text-dark">0</span> dari <span id="pagination-total" class="fw-bold text-dark"><?= count($tujuan_list ?? []) ?></span> data TP
                        </div>
                        <div class="d-flex align-items-center ms-sm-2">
                            <span>Tampilkan</span>
                            <select id="tp-per-page" aria-label="Jumlah baris per halaman" class="form-select form-select-sm d-inline-block w-auto mx-1 shadow-none rounded-2 border-secondary-subtle" onchange="changeTpPageSize(this.value)">
                                <option value="10">10</option>
                                <option value="15" selected>15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="-1">Semua</option>
                            </select>
                            <span>baris</span>
                        </div>
                    </div>

                    <!-- Pagination Navigation Buttons -->
                    <nav aria-label="Navigasi Halaman Tujuan Pembelajaran">
                        <ul class="pagination pagination-sm mb-0 gap-1" id="tp-pagination-nav">
                            <!-- Populated dynamically via JS -->
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="bg-light bg-opacity-50 border-top p-3 rounded-bottom-3">
                <small class="text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>
                        Rumuskan TP yang <strong>terukur</strong> (KKO + materi jelas) sesuai <strong>pembelajaran mendalam</strong>: berkesadaran, bermakna, menggembirakan.
                    </span>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Manual Form -->
<div class="modal fade" id="modalTp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formTp" action="<?= base_url('guru/tujuan/create') ?>" method="post">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah Tujuan Pembelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_tujuan" id="id_tujuan">
                    <input type="hidden" name="id_guru" value="<?= $id_guru_selected ?>">

                    <!-- Informasi Dasar -->
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 rounded-3 mb-3 py-2 px-3">
                        <small><i class="bi bi-lightbulb me-1"></i><strong>TP terukur + pembelajaran mendalam:</strong> awali dengan kata kerja operasional (mis. <em>menganalisis, menjelaskan, menerapkan</em>), tulis konten/materi yang jelas, dan pastikan bermakna bagi peserta didik — <strong>berkesadaran, bermakna, menggembirakan</strong>.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="id_mapel" id="id_mapel" class="form-select rounded-3" required>
                                <option value="">— Pilih Mapel —</option>
                                <?php foreach ($mapel_list as $id => $nama): ?>
                                    <option value="<?= $id ?>"><?= $nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Kelas</label>
                            <select name="tingkat" id="tingkat" class="form-select rounded-3" onchange="autoGenerateNextKode()">
                                <option value="0">— Umum —</option>
                                <option value="10" selected>Kelas 10</option>
                                <option value="11">Kelas 11</option>
                                <option value="12">Kelas 12</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Semester</label>
                            <select name="id_smt" id="id_smt" class="form-select rounded-3">
                                <option value="">— Pilih —</option>
                                <option value="1">1 (Ganjil)</option>
                                <option value="2">2 (Genap)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-muted d-flex justify-content-between align-items-center">
                                Kode TP
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size:0.72rem;" onclick="autoGenerateNextKode()"><i class="bi bi-magic me-1"></i>Auto Kode</button>
                            </label>
                            <input type="text" name="kode" id="kode" class="form-control rounded-3" placeholder="Contoh: 10.1">
                        </div>
                    </div>

                    <!-- Inti: Rumusan TP -->
                    <div class="mb-1">
                        <label class="form-label small fw-semibold text-dark d-flex justify-content-between align-items-center" for="tujuan">
                            <span>Rumusan Tujuan Pembelajaran (TP) <span class="text-danger">*</span></span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size:0.72rem;" onclick="fillContohTp()" title="Isi dengan contoh rumusan">Lihat contoh</button>
                        </label>
                        <textarea name="tujuan" id="tujuan" class="form-control rounded-3" rows="3"
                            placeholder="Contoh: Menganalisis struktur teks eksplanasi dan menentukan hubungan kausalitas antar paragraf melalui diskusi kelompok" required oninput="hintKkoTp()"></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="form-text mb-0" id="tujuan-hint">Tips: KKO + materi jelas + dapat diamati/diukur.</div>
                            <small class="text-muted"><span id="tujuan-count">0</span> karakter</small>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                            <small class="text-muted me-1">Awalan cepat:</small>
                            <button type="button" class="btn btn-outline-primary rounded-pill kko-pick" onclick="pickKko('Menganalisis')">Menganalisis</button>
                            <button type="button" class="btn btn-outline-primary rounded-pill kko-pick" onclick="pickKko('Menjelaskan')">Menjelaskan</button>
                            <button type="button" class="btn btn-outline-primary rounded-pill kko-pick" onclick="pickKko('Menerapkan')">Menerapkan</button>
                            <button type="button" class="btn btn-outline-primary rounded-pill kko-pick" onclick="pickKko('Mengidentifikasi')">Mengidentifikasi</button>
                            <button type="button" class="btn btn-outline-primary rounded-pill kko-pick" onclick="pickKko('Menilai')">Menilai</button>
                            <button type="button" class="btn btn-outline-primary rounded-pill kko-pick" onclick="pickKko('Merancang')">Merancang</button>
                        </div>
                    </div>

                    <!-- Opsi lanjutan (disembunyikan agar form tetap sederhana) -->
                    <details class="mt-3 border rounded-3 px-3 py-2 bg-light bg-opacity-50">
                        <summary class="small fw-semibold text-muted" style="cursor:pointer;">Opsi lanjutan (Fase, CP, materi, KKTP — opsional)</summary>
                        <div class="row g-3 py-3">
                            <input type="hidden" name="fase" id="fase" value="E">
                            <input type="hidden" name="level_kognitif" id="level_kognitif" value="">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Kode CP Acuan</label>
                                <input type="text" name="kode_cp" id="kode_cp" class="form-control rounded-3" placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Elemen CP</label>
                                <input type="text" name="elemen_cp" id="elemen_cp" class="form-control rounded-3" placeholder="Opsional">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">Materi Pokok</label>
                                <input type="text" name="materi_pokok" id="materi_pokok" class="form-control rounded-3" placeholder="Opsional: topik/materi yang diajarkan">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">TP Sikap (opsional)</label>
                                <textarea name="tp_sikap" id="tp_sikap" class="form-control rounded-3" rows="1" placeholder="Opsional"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">TP Keterampilan (opsional)</label>
                                <textarea name="tp_keterampilan" id="tp_keterampilan" class="form-control rounded-3" rows="1" placeholder="Opsional"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">KKTP (opsional)</label>
                                <textarea name="kriteria_ketercapaian" id="kriteria_ketercapaian" class="form-control rounded-3" rows="2" placeholder="Opsional"></textarea>
                            </div>
                        </div>
                    </details>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between align-items-center">
                    <small class="text-muted d-none d-sm-block"><kbd>Ctrl</kbd> + <kbd>Enter</kbd> untuk simpan</small>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4" id="btn-save-tp">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formImport" enctype="multipart/form-data">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel text-success me-2"></i>Import TP dari Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 mb-3 p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark" style="font-size:0.85rem;">Template Excel Resmi</h6>
                                <small class="text-muted">Gunakan format template agar import berjalan lancar</small>
                            </div>
                            <a href="<?= base_url('guru/tujuan/download_template') ?>" class="btn btn-sm btn-outline-success rounded-pill">
                                <i class="bi bi-download me-1"></i>Unduh Template
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mata Pelajaran Target <span class="text-danger">*</span></label>
                        <select name="id_mapel" id="import-id-mapel" class="form-select rounded-3" required>
                            <option value="">— Pilih Mapel —</option>
                            <?php foreach ($mapel_list as $id => $nama): ?>
                                <option value="<?= $id ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih File Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                        <input type="file" name="file_excel" id="file_excel" class="form-control rounded-3" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4" id="btn-submit-import">
                        <i class="bi bi-upload me-1"></i>Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Salin TP dari Semester Lain -->
<div class="modal fade" id="modalCopy" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formCopy">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-copy text-info me-2"></i>Salin TP dari Semester Lain</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 rounded-3 mb-3 p-3">
                        <small><i class="bi bi-info-circle me-1"></i>Fitur ini akan menyalin seluruh TP pada Mapel & Semester asal ke <strong>Semester <?= $smt_active->id_smt ?? 1 ?></strong> aktif Anda.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="id_mapel" id="copy-id-mapel" class="form-select rounded-3" required>
                            <option value="">— Pilih Mapel —</option>
                            <?php foreach ($mapel_list as $id => $nama): ?>
                                <option value="<?= $id ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Salin Dari Semester <span class="text-danger">*</span></label>
                        <select name="id_smt_source" id="copy-id-smt-source" class="form-select rounded-3" required>
                            <option value="1" <?= ($smt_active->id_smt ?? 1) == 2 ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                            <option value="2" <?= ($smt_active->id_smt ?? 1) == 1 ? 'selected' : '' ?>>Semester 2 (Genap)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white btn-sm rounded-pill px-4" id="btn-submit-copy">
                        <i class="bi bi-copy me-1"></i>Proses Salin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Salin TP dari Guru Lain (Mapel Sama) -->
<?php if ($is_admin || $is_panitia): ?>
<div class="modal fade" id="modalCopyGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="formCopyGuru">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-badge text-success me-2"></i>Salin TP dari Guru Lain</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-success bg-opacity-10 border-success border-opacity-25 rounded-3 mb-3 p-3">
                        <small><i class="bi bi-info-circle me-1"></i>Menyalin seluruh TP pada <strong>Mapel yang sama</strong> dari guru lain ke <strong>Semester <?= $smt_active->id_smt ?? 1 ?></strong> aktif Anda.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Guru Asal <span class="text-danger">*</span></label>
                        <select name="id_guru_source" id="copyguru-id-guru-source" class="form-select rounded-3" required>
                            <option value="">— Pilih Guru —</option>
                            <?php foreach ($gurus as $id => $nama): ?>
                                <option value="<?= $id ?>" <?= ($id_guru_selected == $id) ? 'disabled' : '' ?>><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Guru yang sedang Anda pilih tidak bisa dipilih sebagai sumber.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Mata Pelajaran <span class="text-danger">*</span></label>
                        <select name="id_mapel" id="copyguru-id-mapel" class="form-select rounded-3" required>
                            <option value="">— Pilih Mapel —</option>
                            <?php foreach ($mapel_list as $id => $nama): ?>
                                <option value="<?= $id ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Salin Dari Semester <span class="text-danger">*</span></label>
                        <select name="id_smt_source" id="copyguru-id-smt-source" class="form-select rounded-3" required>
                            <option value="1" <?= ($smt_active->id_smt ?? 1) == 2 ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                            <option value="2" <?= ($smt_active->id_smt ?? 1) == 1 ? 'selected' : '' ?>>Semester 2 (Genap)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success text-white btn-sm rounded-pill px-4" id="btn-submit-copy-guru">
                        <i class="bi bi-person-badge me-1"></i>Proses Salin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Sticky thead: konteks kolom tetap terlihat saat scroll daftar panjang */
#tpTable thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #f8fafc;
    box-shadow: inset 0 -2px 0 #e2e8f0;
}
/* Batasi tinggi tabel agar header sticky bekerja pada daftar panjang */
.card-body .table-responsive {
    max-height: 62vh;
    overflow-y: auto;
}

/* Status switch tunggal: satu kontrol jelas ganti badge+ikon ganda */
.tp-switch {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    cursor: pointer;
    user-select: none;
    border: 0;
    background: transparent;
    padding: 0.25rem 0.2rem;
    border-radius: 999px;
}
.tp-switch:focus-visible {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
}
.tp-switch .track {
    position: relative;
    width: 44px;
    height: 24px;
    border-radius: 999px;
    background: #cbd5e1;
    transition: background 0.2s ease;
    flex: none;
}
.tp-switch .track::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    transition: transform 0.2s ease;
}
.tp-switch[aria-checked="true"] .track { background: linear-gradient(135deg, #22c55e, #16a34a); }
.tp-switch[aria-checked="true"] .track::after { transform: translateX(20px); }
.tp-switch .lbl { font-size: 0.75rem; font-weight: 600; color: #475569; min-width: 58px; text-align: left; }
.tp-switch[aria-checked="true"] .lbl { color: #15803d; }
.tp-switch.is-locked { cursor: not-allowed; opacity: 0.65; }
.tp-switch.is-busy { pointer-events: none; opacity: 0.6; }

/* Rumusan panjang: clamp 3 baris, klik untuk bentang */
.tp-text {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    cursor: pointer;
}
.tp-row.expanded .tp-text {
    -webkit-line-clamp: unset;
    display: block;
}
.tp-more {
    font-size: 0.72rem;
    font-weight: 600;
    color: #4f46e5;
    background: none;
    border: 0;
    padding: 0;
    margin-top: 2px;
}
.tp-more:hover { text-decoration: underline; }

/* Baris terpilih: highlight jelas */
.tp-row.row-selected { background-color: #eef2ff !important; }
.tp-row.row-selected:hover { background-color: #e0e7ff !important; }

/* Tombol aksi: area sentuh 34px, fokus terlihat */
.btn-action-group .btn-action {
    min-width: 34px;
    min-height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-action-group .btn-action:focus-visible {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
}

/* Strip ringkasan per mapel */
#tp-stats-strip { scrollbar-width: thin; }
.tp-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    white-space: nowrap;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 0.25rem 0.7rem;
    transition: all 0.15s ease;
}
.tp-chip:hover { background: #e0e7ff; border-color: #c7d2fe; color: #3730a3; }
.tp-chip.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }
.tp-chip-n {
    font-size: 0.7rem;
    font-weight: 700;
    background: rgba(0,0,0,0.08);
    border-radius: 999px;
    padding: 0 0.45rem;
    line-height: 1.5;
}
.tp-chip.active .tp-chip-n { background: rgba(255,255,255,0.25); }

/* Header sortir */
.th-sort { cursor: pointer; user-select: none; }
.th-sort:hover { color: #4f46e5 !important; }
.sort-ind { font-size: 0.7rem; color: #4f46e5; margin-left: 2px; }

/* Salin rumusan: muncul saat hover baris (selalu tampil di layar sentuh) */
.tp-copy {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    background: none;
    border: 0;
    padding: 0 2px;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.tp-row:hover .tp-copy, .tp-row:focus-within .tp-copy { opacity: 1; }
.tp-copy:hover { color: #4f46e5; }
@media (hover: none) { .tp-copy { opacity: 1; } }

/* Pilihan KKO cepat di modal */
.kko-pick { font-size: 0.72rem; padding: 0.15rem 0.7rem; }

/* Pagination Custom Styles */
#tp-pagination-nav .page-link {
    border-radius: 8px !important;
    padding: 0.3rem 0.65rem;
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
#tp-pagination-nav .page-link:hover:not(.disabled) {
    background-color: #eef2ff;
    border-color: #c7d2fe;
    color: #3730a3;
}
#tp-pagination-nav .page-item.active .page-link {
    background-color: #4f46e5 !important;
    border-color: #4f46e5 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
}
#tp-pagination-nav .page-item.disabled .page-link {
    color: #94a3b8;
    background-color: #f8fafc;
    border-color: #e2e8f0;
    pointer-events: none;
}

/* Table Hover & Row Polish */
#tpTable th {
    font-size: 0.78rem;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.tp-row:hover {
    background-color: #f8fafc !important;
}
</style>

<script>
// State Management untuk Pagination dan Search
let currentTpPage = 1;
let tpPageSize = parseInt(localStorage.getItem('tp_page_size')) || 15;
let filteredTpRows = [];
let tpSortKey = '';
let tpSortDir = 1;

/**
 * Sortir klik-header: Mapel (nama) atau Kode TP. Klik ke-3 mengembalikan urutan awal.
 */
function toggleTpSort(key) {
    if (tpSortKey !== key) {
        tpSortKey = key;
        tpSortDir = 1;
    } else if (tpSortDir === 1) {
        tpSortDir = -1;
    } else {
        tpSortKey = '';
        tpSortDir = 1;
    }
    updateSortIndicators();
    renderTpTable();
}

function updateSortIndicators() {
    const map = { nama: 'sort-ind-nama', kode: 'sort-ind-kode' };
    Object.keys(map).forEach(function(k) {
        const el = document.getElementById(map[k]);
        if (!el) return;
        el.textContent = (tpSortKey === k) ? (tpSortDir === 1 ? ' ▲' : ' ▼') : '';
    });
}

/**
 * Filter cepat dari strip ringkasan: set filter mapel lalu submit.
 */
function filterByMapel(mid) {
    const sel = document.getElementById('filter-mapel');
    if (sel) {
        sel.value = mid;
        sel.form.submit();
    } else {
        const url = new URL(window.location.href);
        if (mid) url.searchParams.set('mapel', mid);
        else url.searchParams.delete('mapel');
        window.location.href = url.toString();
    }
}

function showModalById(id) {
    const el = document.getElementById(id);
    if (!el) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (typeof $.fn.modal !== 'undefined') {
        $(el).modal('show');
    }
}

function getFaseFromTingkat(tingkat) {
    const t = parseInt(tingkat);
    if (t === 1 || t === 2) return 'A';
    if (t === 3 || t === 4) return 'B';
    if (t === 5 || t === 6) return 'C';
    if (t === 7 || t === 8 || t === 9) return 'D';
    if (t === 10) return 'E';
    if (t === 11 || t === 12) return 'F';
    return '';
}

$(document).ready(function() {
    // Inisialisasi Select Page Size
    $('#tp-per-page').val(tpPageSize.toString());

    // Inisialisasi Pagination pertama kali
    renderTpTable();

    // Trigger perubahan tingkat -> fase
    $(document).on('change', '#tingkat', function() {
        const val = $(this).val();
        const fase = getFaseFromTingkat(val);
        if (fase) {
            $('#fase').val(fase);
        }
    });

    // Mapel dipilih & kode masih kosong -> isi kode otomatis tanpa toast
    $(document).on('change', '#id_mapel', function() {
        if (!$('#kode').val()) {
            const mapel = $(this).val();
            const tingkat = $('#tingkat').val();
            if (!mapel) return;
            $.get('<?= base_url('guru/tujuan/get_next_kode') ?>', { id_mapel: mapel, tingkat: tingkat }, function(res) {
                if (res.status && res.kode && !$('#kode').val()) {
                    $('#kode').val(res.kode);
                }
            });
        }
    });

    // Autofocus cerdas: tambah -> mapel, edit -> rumusan TP
    const modalTpEl = document.getElementById('modalTp');
    if (modalTpEl) {
        modalTpEl.addEventListener('shown.bs.modal', function() {
            const isEdit = !!$('#id_tujuan').val();
            setTimeout(function() {
                if (isEdit) {
                    $('#tujuan').trigger('focus');
                } else if (!$('#id_mapel').val()) {
                    $('#id_mapel').trigger('focus');
                } else {
                    $('#tujuan').trigger('focus');
                }
            }, 60);
        });
    }

    // Ctrl+Enter di textarea rumusan -> simpan
    $(document).on('keydown', '#tujuan', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            $('#formTp').trigger('submit');
        }
    });

    // Shortcut global: "/" fokus ke pencarian, Esc bersihkan (di luar modal/input lain)
    $(document).on('keydown', function(e) {
        const tag = (e.target.tagName || '').toLowerCase();
        const typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;
        if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            $('#live-search-tp').trigger('focus');
        } else if (e.key === 'Escape' && document.activeElement && document.activeElement.id === 'live-search-tp') {
            clearLiveSearch();
            $('#live-search-tp').trigger('blur');
        }
    });

    // Cegah double-submit: kunci tombol simpan saat memproses
    $('#formTp').submit(function(e) {
        const tujuanTxt = ($('#tujuan').val() || '').trim();
        if (tujuanTxt.length < 10) return; // validasi utama yang menolak
        $('#btn-save-tp').prop('disabled', true);
        setTimeout(function() { $('#btn-save-tp').prop('disabled', false); }, 4000);
    });

    // Form Tambah / Edit TP (disederhanakan: fokus rumusan TP terukur)
    $('#formTp').submit(function(e) {
        e.preventDefault();
        const tujuanTxt = ($('#tujuan').val() || '').trim();
        if (tujuanTxt.length < 10) {
            Swal.fire('Belum lengkap', 'Tulis rumusan TP minimal 10 karakter: diawali kata kerja operasional + materi yang jelas.', 'warning');
            return;
        }
        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.post($(this).attr('action'), $(this).serialize(), function(res) {
            if (res.status) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }).fail(() => Swal.fire('Error', 'Gagal menghubungi server', 'error'));
    });

    // Check All Checkbox Handler (pilih semua pada halaman aktif)
    $(document).on('change', '#check-all', function() {
        const isChecked = this.checked;
        $('.tp-row:visible .check-item').prop('checked', isChecked);
        updateBulkState();
    });

    $(document).on('change', '.check-item', function() {
        updateBulkState();
    });

    // Handler Form Import Excel
    $('#formImport').submit(function(e) {
        e.preventDefault();
        const btn = $('#btn-submit-import');
        const origText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mengimpor...');

        const formData = new FormData(this);
        formData.append('id_guru', '<?= $id_guru_selected ?>');
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        $.ajax({
            url: '<?= base_url('guru/tujuan/import_excel') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html(origText);
                if (res.status) {
                    Swal.fire({ icon: 'success', title: 'Import Berhasil!', text: res.message, timer: 1800, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal Import', res.message, 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(origText);
                Swal.fire('Error', 'Gagal menghubungi server saat import.', 'error');
            }
        });
    });

    // Handler Form Salin TP
    $('#formCopy').submit(function(e) {
        e.preventDefault();
        const btn = $('#btn-submit-copy');
        const origText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyalin...');

        $.post('<?= base_url('guru/tujuan/copy_from') ?>', {
            id_mapel: $('#copy-id-mapel').val(),
            id_smt_source: $('#copy-id-smt-source').val(),
            id_guru: '<?= $id_guru_selected ?>',
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }, function(res) {
            btn.prop('disabled', false).html(origText);
            if (res.status) {
                Swal.fire({ icon: 'success', title: 'Berhasil Menyalin!', text: res.message, timer: 1800, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Gagal Menyalin', res.message, 'error');
            }
        }).fail(function() {
            btn.prop('disabled', false).html(origText);
            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
        });
    });

    // Handler Form Salin TP dari Guru Lain
    $('#formCopyGuru').submit(function(e) {
        e.preventDefault();
        const btn = $('#btn-submit-copy-guru');
        const origText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyalin...');

        $.post('<?= base_url('guru/tujuan/copy_from_guru') ?>', {
            id_guru: '<?= $id_guru_selected ?>',
            id_guru_source: $('#copyguru-id-guru-source').val(),
            id_mapel: $('#copyguru-id-mapel').val(),
            id_smt_source: $('#copyguru-id-smt-source').val(),
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }, function(res) {
            btn.prop('disabled', false).html(origText);
            if (res.status) {
                Swal.fire({ icon: 'success', title: 'Berhasil Menyalin!', text: res.message, timer: 1800, showConfirmButton: false })
                    .then(() => location.reload());
            } else {
                Swal.fire('Gagal Menyalin', res.message, 'error');
            }
        }).fail(function() {
            btn.prop('disabled', false).html(origText);
            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
        });
    });
});

/**
 * Live Search Input Handler (debounced agar render tidak berkedut saat mengetik cepat)
 */
let tpSearchTimer = null;
function onLiveSearchInput() {
    clearTimeout(tpSearchTimer);
    tpSearchTimer = setTimeout(function() {
        const q = ($('#live-search-tp').val() || '').trim();
        if (q.length > 0) {
            $('#btn-clear-search').removeClass('d-none');
        } else {
            $('#btn-clear-search').addClass('d-none');
        }
        currentTpPage = 1;
        renderTpTable();
    }, 180);
}

/**
 * Clear Live Search
 */
function clearLiveSearch() {
    $('#live-search-tp').val('');
    $('#btn-clear-search').addClass('d-none');
    currentTpPage = 1;
    renderTpTable();
}

/**
 * Render Table Rows with Client-side Pagination & Live Filtering
 */
function renderTpTable() {
    const q = ($('#live-search-tp').val() || '').toLowerCase().trim();
    const allRows = $('.tp-row');
    
    if (allRows.length === 0) {
        $('#tp-pagination-container').hide();
        return;
    }

    filteredTpRows = [];
    allRows.each(function() {
        const searchText = $(this).attr('data-search') || '';
        if (q === '' || searchText.indexOf(q) !== -1) {
            filteredTpRows.push(this);
        }
    });

    // Terapkan sortir klik-header (natural sort agar 10.2 < 10.10)
    if (tpSortKey) {
        const attr = tpSortKey === 'nama' ? 'nama' : 'kode';
        filteredTpRows.sort(function(a, b) {
            const va = ($(a).data(attr) || '').toString();
            const vb = ($(b).data(attr) || '').toString();
            return va.localeCompare(vb, 'id', { numeric: true, sensitivity: 'base' }) * tpSortDir;
        });
    }

    const totalMatches = filteredTpRows.length;
    $('#tp-count-badge').text(totalMatches);

    // Empty state handling
    if (totalMatches === 0) {
        allRows.hide();
        $('#tp-empty-search').show();
        $('#pagination-from').text('0');
        $('#pagination-to').text('0');
        $('#pagination-total').text('0');
        $('#tp-pagination-nav').html('');
        $('#tp-pagination-container').hide();
        updateBulkState();
        return;
    } else {
        $('#tp-empty-search').hide();
        $('#tp-pagination-container').show();
    }

    // Pagination calculations
    let effectivePageSize = tpPageSize;
    if (effectivePageSize === -1) {
        effectivePageSize = totalMatches;
    }

    const totalPages = Math.ceil(totalMatches / effectivePageSize) || 1;
    if (currentTpPage > totalPages) currentTpPage = totalPages;
    if (currentTpPage < 1) currentTpPage = 1;

    const startIndex = (currentTpPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalMatches);

    // Hide all rows first
    allRows.hide();

    // Show and re-number matched rows for the current page
    for (let i = startIndex; i < endIndex; i++) {
        const row = filteredTpRows[i];
        $(row).show();
        $(row).find('.tp-num').text(i + 1);
        syncMoreLabel($(row).find('.tp-text')[0]);
    }

    // Update Pagination Info
    $('#pagination-from').text(startIndex + 1);
    $('#pagination-to').text(endIndex);
    $('#pagination-total').text(totalMatches);

    // Build Pagination Navigation
    buildPaginationNav(totalPages, currentTpPage);
    updateBulkState();
}

/**
 * Build Pagination Navigation Links
 */
function buildPaginationNav(totalPages, page) {
    const $nav = $('#tp-pagination-nav');
    $nav.empty();

    if (totalPages <= 1) {
        return;
    }

    // Tombol First & Prev
    const prevDisabled = page <= 1 ? 'disabled' : '';
    $nav.append(`
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="javascript:void(0)" onclick="goToTpPage(1)" title="Halaman Pertama"><i class="bi bi-chevron-double-left"></i></a>
        </li>
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="javascript:void(0)" onclick="goToTpPage(${page - 1})" title="Halaman Sebelumnya"><i class="bi bi-chevron-left"></i></a>
        </li>
    `);

    // Window halaman (misal: page - 2 s/d page + 2)
    let startPage = Math.max(1, page - 2);
    let endPage = Math.min(totalPages, page + 2);

    if (startPage > 1) {
        $nav.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToTpPage(1)">1</a></li>`);
        if (startPage > 2) {
            $nav.append(`<li class="page-item disabled"><span class="page-link border-0">…</span></li>`);
        }
    }

    for (let p = startPage; p <= endPage; p++) {
        const activeClass = p === page ? 'active' : '';
        $nav.append(`
            <li class="page-item ${activeClass}">
                <a class="page-link" href="javascript:void(0)" onclick="goToTpPage(${p})">${p}</a>
            </li>
        `);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            $nav.append(`<li class="page-item disabled"><span class="page-link border-0">…</span></li>`);
        }
        $nav.append(`<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToTpPage(${totalPages})">${totalPages}</a></li>`);
    }

    // Tombol Next & Last
    const nextDisabled = page >= totalPages ? 'disabled' : '';
    $nav.append(`
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="javascript:void(0)" onclick="goToTpPage(${page + 1})" title="Halaman Selanjutnya"><i class="bi bi-chevron-right"></i></a>
        </li>
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="javascript:void(0)" onclick="goToTpPage(${totalPages})" title="Halaman Terakhir"><i class="bi bi-chevron-double-right"></i></a>
        </li>
    `);
}

/**
 * Navigate to Specific Page
 */
function goToTpPage(page) {
    currentTpPage = page;
    renderTpTable();

    // Smooth scroll to top of table if scrolled past
    const tableEl = document.getElementById('tpTable');
    if (tableEl) {
        const rect = tableEl.getBoundingClientRect();
        if (rect.top < 0) {
            tableEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

/**
 * Change Page Size Handler
 */
function changeTpPageSize(size) {
    tpPageSize = parseInt(size);
    localStorage.setItem('tp_page_size', size);
    currentTpPage = 1;
    renderTpTable();
}

/**
 * Update Bulk Checkbox and Action Toolbar State
 */
function updateBulkState() {
    const checkedItems = $('.check-item:checked');
    const count = checkedItems.length;
    const totalVisible = $('.tp-row:visible .check-item').length;

    $('#count-checked').text(count);

    const $bulkActions = $('#bulk-actions');
    if (count > 0) {
        $bulkActions.removeClass('d-none');
    } else {
        $bulkActions.addClass('d-none');
    }

    // Highlight baris terpilih agar kentara
    $('.tp-row').removeClass('row-selected');
    checkedItems.each(function() {
        $(this).closest('tr').addClass('row-selected');
    });

    const checkedVisibleCount = $('.tp-row:visible .check-item:checked').length;
    if (totalVisible > 0 && checkedVisibleCount === totalVisible) {
        $('#check-all').prop('checked', true).prop('indeterminate', false);
    } else if (checkedVisibleCount > 0) {
        $('#check-all').prop('checked', false).prop('indeterminate', true);
    } else {
        $('#check-all').prop('checked', false).prop('indeterminate', false);
    }
}

/**
 * Petunjuk terukur saat mengetik rumusan TP:
 * cek KKO di awal kalimat + panjang rumusan yang memadai.
 */
function hintKkoTp() {
    const txt = ($('#tujuan').val() || '').trim();
    $('#tujuan-count').text(txt.length);
    const kko = ['menyebutkan','mengidentifikasi','menjelaskan','membandingkan','mengklasifikasikan','merangkum','menghitung','menerapkan','menggunakan','melaksanakan','menganalisis','membedakan','menghubungkan','menilai','menyimpulkan','membuktikan','mengevaluasi','merancang','merumuskan','menciptakan','menyusun','membuat','mendeskripsikan','menguraikan','memecahkan'];
    const lower = txt.toLowerCase();
    const found = kko.find(k => lower.startsWith(k) || lower.includes(' ' + k + ' '));
    const hint = $('#tujuan-hint');
    if (!txt) {
        hint.text('Tips: KKO + materi jelas + dapat diamati/diukur.');
    } else if (!found) {
        hint.html('<span class="text-warning">Belum terdeteksi kata kerja operasional di awal kalimat. Contoh: <em>Menganalisis ..., Menjelaskan ..., Menerapkan ...</em></span>');
    } else if (txt.length < 20) {
        hint.html('<span class="text-warning">Tambahkan konten/materi yang jelas agar terukur. Contoh: <em>"... struktur teks eksplanasi melalui diskusi kelompok"</em></span>');
    } else {
        hint.html('<span class="text-success">Bagus — TP diawali KKO (' + found + ') dan cukup spesifik.</span>');
    }
}

/**
 * Auto generate next Kode TP
 */
function autoGenerateNextKode() {
    const mapel = $('#id_mapel').val();
    const tingkat = $('#tingkat').val();

    if (!mapel) {
        Swal.fire('Pemberitahuan', 'Pilih Mata Pelajaran terlebih dahulu.', 'info');
        return;
    }

    $.get('<?= base_url('guru/tujuan/get_next_kode') ?>', { id_mapel: mapel, tingkat: tingkat }, function(res) {
        if (res.status && res.kode) {
            $('#kode').val(res.kode);
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Kode disarankan: ' + res.kode, timer: 1500, showConfirmButton: false });
        }
    });
}

/**
 * 1-Click Duplicate TP
 */
function duplicateTp(id) {
    Swal.fire({
        title: 'Duplikat TP Ini?',
        text: 'Salinan TP baru akan dibuat secara otomatis.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Duplikat!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4f46e5'
    }).then((res) => {
        if (res.isConfirmed) {
            Swal.fire({ title: 'Menduplikasi...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.post('<?= base_url('guru/tujuan/duplicate') ?>', {
                id: id,
                id_guru: '<?= $id_guru_selected ?>',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function(resp) {
                if (resp.status) {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: resp.message, timer: 1200, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', resp.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Error', 'Gagal menduplikasi TP.', 'error');
            });
        }
    });
}

function modalAdd() {
    $('#formTp')[0].reset();
    $('#id_tujuan').val('');
    $('#tingkat').val('10').trigger('change');
    $('#id_smt').val('<?= $smt_active->id_smt ?>');
    $('#fase').val(getFaseFromTingkat('10'));
    $('#modalTitle').text('Tambah Tujuan Pembelajaran');
    hintKkoTp();
    showModalById('modalTp');
}

function editTp(data) {
    $('#formTp')[0].reset();
    $('#id_tujuan').val(data.id_tujuan || '');
    $('#id_mapel').val(data.id_mapel || '');
    $('#tingkat').val(data.tingkat || '0');
    $('#id_smt').val(data.id_smt || '');
    $('#fase').val(data.fase || getFaseFromTingkat(data.tingkat || '10'));
    $('#kode').val(data.kode || '');
    $('#kode_cp').val(data.kode_cp || '');
    $('#elemen_cp').val(data.elemen_cp || '');
    $('#level_kognitif').val(data.level_kognitif || '');
    $('#materi_pokok').val(data.materi_pokok || '');
    $('#tujuan').val(data.tujuan || '');
    $('#tp_sikap').val(data.tp_sikap || '');
    $('#tp_keterampilan').val(data.tp_keterampilan || '');
    $('#kriteria_ketercapaian').val(data.kriteria_ketercapaian || '');
    $('#modalTitle').text('Edit Tujuan Pembelajaran');
    hintKkoTp();
    showModalById('modalTp');
}

function hapus(id) {
    Swal.fire({
        title: 'Hapus Tujuan Pembelajaran?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('<?= base_url('guru/tujuan/delete') ?>', {
                id: id,
                id_guru: '<?= $id_guru_selected ?>',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function(resp) {
                if (resp.status) {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: resp.message, timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', resp.message, 'error');
                }
            });
        }
    });
}

function bulkDelete() {
    let selected = [];
    let usedItems = 0;
    $('.check-item:checked').each(function() {
        if ($(this).data('used')) {
            usedItems++;
        } else {
            selected.push($(this).val());
        }
    });

    if (selected.length === 0) {
        if (usedItems > 0) {
            Swal.fire('Informasi', 'Semua TP yang dipilih sedang digunakan dan tidak dapat dihapus.', 'info');
        } else {
            Swal.fire('Peringatan', 'Silakan pilih data yang akan dihapus.', 'warning');
        }
        return;
    }

    let warningText = `Anda akan menghapus ${selected.length} data Tujuan Pembelajaran.`;
    if (usedItems > 0) {
        warningText += ` (${usedItems} data lainnya diabaikan karena sedang digunakan)`;
    }
    warningText += " Tindakan ini tidak dapat dikembalikan!";

    Swal.fire({
        title: 'Hapus Terpilih?',
        text: warningText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('<?= base_url('guru/tujuan/delete') ?>', {
                ids: selected,
                id_guru: '<?= $id_guru_selected ?>',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function(resp) {
                if (resp.status) {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: resp.message, timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', resp.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Gagal', 'Terjadi kesalahan sistem saat menghapus data.', 'error');
            });
        }
    });
}

/**
 * Toggle status aktif/nonaktif TP via switch tunggal (optimistic UI + rollback).
 */
function toggleStatusSwitch(el, id) {
    const btn = $(el);
    const tr = btn.closest('tr');
    const currentlyOn = btn.attr('aria-checked') === 'true';
    const newStatus = currentlyOn ? 'nonaktif' : 'aktif';
    const willBeOn = !currentlyOn;

    // Optimistic update
    btn.attr('aria-checked', willBeOn ? 'true' : 'false');
    btn.find('.lbl').text(willBeOn ? 'Aktif' : 'Nonaktif');
    btn.attr('title', willBeOn ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan');
    btn.addClass('is-busy');
    tr.attr('data-status', newStatus);

    function rollback() {
        btn.attr('aria-checked', currentlyOn ? 'true' : 'false');
        btn.find('.lbl').text(currentlyOn ? 'Aktif' : 'Nonaktif');
        btn.attr('title', currentlyOn ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan');
        tr.attr('data-status', currentlyOn ? 'aktif' : 'nonaktif');
    }

    $.post('<?= base_url('guru/tujuan/toggleStatus') ?>', {
        id: id,
        status: newStatus,
        id_guru: '<?= $id_guru_selected ?>',
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
    }, function(resp) {
        btn.removeClass('is-busy');
        if (resp.status) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: resp.message,
                timer: 1200,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            rollback();
            Swal.fire('Gagal', resp.message || 'Gagal mengubah status', 'error');
        }
    }).fail(function() {
        btn.removeClass('is-busy');
        rollback();
        Swal.fire('Error', 'Gagal mengubah status', 'error');
    });
}

/**
 * Batalkan semua pilihan checkbox.
 */
function clearTpSelection() {
    $('.check-item').prop('checked', false);
    updateBulkState();
}

/**
 * Tampilkan tombol "Selengkapnya" hanya bila teks terpotong (clamp aktif).
 */
function syncMoreLabel(textEl) {
    if (!textEl) return;
    const moreBtn = textEl.parentElement.querySelector('.tp-more');
    if (!moreBtn) return;
    const expanded = textEl.closest('tr').classList.contains('expanded');
    const clamped = textEl.scrollHeight > textEl.clientHeight + 2;
    if (expanded) {
        moreBtn.classList.remove('d-none');
        moreBtn.textContent = 'Ciutkan';
    } else if (clamped) {
        moreBtn.classList.remove('d-none');
        moreBtn.textContent = 'Selengkapnya';
    } else {
        moreBtn.classList.add('d-none');
    }
}

/**
 * Isi contoh rumusan TP terukur (membantu guru yang baru mulai).
 */
function fillContohTp() {
    const contoh = 'Menganalisis struktur teks eksplanasi dan menentukan hubungan kausalitas antar paragraf melalui diskusi kelompok';
    const cur = ($('#tujuan').val() || '').trim();
    if (cur) {
        Swal.fire({
            title: 'Ganti dengan contoh?',
            text: 'Isi rumusan saat ini akan diganti dengan contoh.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, ganti',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                $('#tujuan').val(contoh);
                hintKkoTp();
                $('#tujuan').trigger('focus');
            }
        });
    } else {
        $('#tujuan').val(contoh);
        hintKkoTp();
    }
}

/**
 * Awalan KKO cepat: isi saat kosong, atau ganti kata kerja pertama bila sudah berupa KKO.
 */
function pickKko(word) {
    const ta = $('#tujuan');
    const cur = (ta.val() || '').trim();
    const known = ['menyebutkan','mengidentifikasi','mendaftar','mengenali','memilih','menjelaskan','membandingkan','mengklasifikasikan','merangkum','menguraikan','mendeskripsikan','menafsirkan','menghitung','menerapkan','menggunakan','melaksanakan','melakukan','memecahkan','mengoperasikan','menganalisis','membedakan','mengorganisasikan','menghubungkan','mengkategorikan','menilai','mengkritisi','menyimpulkan','membuktikan','mempertahankan','mengevaluasi','mengkritik','merancang','menghasilkan','merumuskan','menciptakan','membangun','mengembangkan','menyusun','membuat'];
    if (!cur) {
        ta.val(word + ' ');
    } else {
        const parts = cur.split(/\s+/);
        if (known.includes(parts[0].toLowerCase())) {
            parts[0] = word;
            ta.val(parts.join(' '));
        } else {
            ta.val(word + ' ' + cur.charAt(0).toLowerCase() + cur.slice(1));
        }
    }
    hintKkoTp();
    ta.trigger('focus');
}

/**
 * Salin rumusan TP satu baris ke clipboard.
 */
function copyTpText(btn) {
    const textEl = btn.closest('tr').querySelector('.tp-text');
    const txt = (textEl ? textEl.innerText : '').trim();
    if (!txt) return;
    function done() {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = 'bi bi-check-lg text-success';
            setTimeout(function() { icon.className = 'bi bi-clipboard'; }, 1200);
        }
        Swal.fire({ icon: 'success', title: 'Disalin', text: 'Rumusan TP disalin ke clipboard.', timer: 1000, showConfirmButton: false, toast: true, position: 'top-end' });
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(txt).then(done, function() { fallbackCopyTp(txt); done(); });
    } else {
        fallbackCopyTp(txt);
        done();
    }
}

function fallbackCopyTp(txt) {
    const tmp = document.createElement('textarea');
    tmp.value = txt;
    tmp.style.position = 'fixed';
    tmp.style.opacity = '0';
    document.body.appendChild(tmp);
    tmp.select();
    try { document.execCommand('copy'); } catch (e) { /* abaikan */ }
    document.body.removeChild(tmp);
}

/**
 * Bulk toggle status untuk TP terpilih
 */
function bulkToggleStatus(status) {
    const statusText = status === 'aktif' ? 'mengaktifkan' : 'menonaktifkan';
    const statusLabel = status === 'aktif' ? 'Aktifkan' : 'Nonaktifkan';

    let selected = [];
    let usedItems = 0;
    $('.check-item:checked').each(function() {
        if (status === 'nonaktif' && $(this).data('used')) {
            usedItems++;
        } else {
            selected.push($(this).val());
        }
    });

    if (selected.length === 0) {
        if (usedItems > 0) {
            Swal.fire('Informasi', 'Semua TP yang dipilih sedang digunakan dan tidak dapat dinonaktifkan.', 'info');
        } else {
            Swal.fire('Peringatan', 'Silakan pilih data yang akan diubah statusnya.', 'warning');
        }
        return;
    }

    let warningText = `Anda akan ${statusText} ${selected.length} data Tujuan Pembelajaran.`;
    if (status === 'nonaktif' && usedItems > 0) {
        warningText += ` (${usedItems} data lainnya diabaikan karena sedang digunakan)`;
    }

    Swal.fire({
        title: `${statusLabel} Terpilih?`,
        text: warningText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'aktif' ? '#22c55e' : '#64748b',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Ya, ${statusLabel}!`,
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((res) => {
        if (res.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: `Sedang ${statusText} ${selected.length} TP`,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post('<?= base_url('guru/tujuan/bulkToggleStatus') ?>', {
                ids: selected,
                status: status,
                id_guru: '<?= $id_guru_selected ?>',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            }, function(resp) {
                Swal.close();
                if (resp.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', resp.message, 'error');
                }
            }).fail(function() {
                Swal.fire('Gagal', 'Terjadi kesalahan sistem saat mengubah status.', 'error');
            });
        }
    });
}

function openImportModal() {
    $('#formImport')[0].reset();
    showModalById('modalImport');
}

function openCopyModal() {
    $('#formCopy')[0].reset();
    showModalById('modalCopy');
}

function openCopyGuruModal() {
    $('#formCopyGuru')[0].reset();
    showModalById('modalCopyGuru');
}

function changeGuru(idGuru) {
    const url = new URL(window.location.href);
    if (idGuru) {
        url.searchParams.set('id_guru', idGuru);
    } else {
        url.searchParams.delete('id_guru');
    }
    window.location.href = url.toString();
}
</script>
<?= $this->endSection(); ?>
