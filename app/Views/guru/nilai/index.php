<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .btn-action-text {
        width: auto !important;
        padding: 0.35rem 0.75rem !important;
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        white-space: nowrap;
        display: inline-flex !important;
        align-items: center;
    }
</style>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-calculator text-primary me-2"></i>
                    <?= esc($judul ?? 'Pengelolaan Nilai') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Penilaian') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Main Primary Outline Card matching /cbtjadwal -->
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h3 class="card-title fw-bold text-primary mb-0">
                    <i class="bi bi-mortarboard me-2"></i>Penilaian Kurikulum Merdeka
                </h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-2">
                    <?php if ($is_admin || $is_panitia): ?>
                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-light"><i class="bi bi-person-workspace text-primary"></i></span>
                            <select class="form-select" id="id_guru_monitor" onchange="changeGuru(this.value)">
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($gurus as $g): ?>
                                    <option value="<?= $g->id_guru ?>" <?= (isset($id_guru_selected) && $id_guru_selected == $g->id_guru) ? 'selected' : '' ?>>
                                        <?= esc($g->nama_guru) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <a href="javascript:location.reload()" type="button" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-primary active" id="btnModeList" onclick="switchViewMode('list')" title="Tampilan List/Tabel">
                            <i class="bi bi-list"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="btnModeGrid" onclick="switchViewMode('grid')" title="Tampilan Grid/Kartu">
                            <i class="bi bi-grid-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body py-3">
                <!-- Legend status -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="fw-bold small text-secondary me-1"><i class="bi bi-palette me-1"></i>Kode Warna Status:</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1"><i class="bi bi-square-fill me-1"></i>Belum diisi</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1"><i class="bi bi-square-fill me-1"></i>Proses / belum selesai</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1"><i class="bi bi-square-fill me-1"></i>Tuntas (100%)</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-square-fill me-1"></i>Formatif &amp; Sumatif aktif</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar matching /cbtjadwal -->
        <div class="row align-items-center mb-3 g-2">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchMapelInput" class="form-control" placeholder="Cari mata pelajaran, kelas, atau kode..." onkeyup="filterItems()">
                </div>
            </div>
            <div class="col-md-7 d-flex justify-content-md-end align-items-center gap-1 flex-wrap" id="filterPills">
                <span class="badge bg-primary px-3 py-2 text-white style-filter-pill" style="cursor:pointer;" data-filter="all" onclick="setFilterClass('all', this)">
                    <i class="bi bi-grid-fill me-1"></i>Semua Kelas
                </span>
                <?php
                $uniqueClasses = [];
                foreach ($mapel_kelas as $mk) {
                    $cName = trim($mk->nama_kelas);
                    $parts = explode(' ', $cName);
                    $prefix = $parts[0];
                    if (!in_array($prefix, $uniqueClasses)) {
                        $uniqueClasses[] = $prefix;
                    }
                }
                sort($uniqueClasses);
                foreach ($uniqueClasses as $uc):
                ?>
                    <span class="badge bg-secondary px-3 py-2 text-white style-filter-pill" style="cursor:pointer;" data-filter="<?= esc(strtolower($uc)) ?>" onclick="setFilterClass('<?= esc(strtolower($uc)) ?>', this)">
                        Kelas <?= esc($uc) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- List Mode View matching /cbtjadwal -->
        <div id="viewListContainer">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header border-bottom">
                    <h3 class="card-title fw-bold text-dark">
                        <i class="bi bi-list-task text-primary me-2"></i>Daftar Penugasan Penilaian
                    </h3>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($mapel_kelas)): ?>
                        <div class="alert alert-warning m-3" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i> Belum ada penugasan kelas yang diampu pada semester ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0 dataTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" width="40">No.</th>
                                        <th>Mata Pelajaran</th>
                                        <th class="text-center">Kelas</th>
                                        <th class="text-center">Siswa</th>
                                        <th>Progress Harian</th>
                                        <th>Progress Rapor</th>
                                        <th class="text-center" style="min-width: 210px;">Aksi / Input Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($mapel_kelas as $mk): ?>
                                        <?php
                                        $progress = $mk->progress ?? ['persen' => 0, 'total_siswa' => 0, 'total_tp' => 0, 'persen_akhir' => 0, 'sudah_akhir' => 0];
                                        $harianPct = (int)($progress['persen'] ?? 0);
                                        $akhirPct = (int)($progress['persen_akhir'] ?? 0);

                                        $harianSquareColor = $harianPct >= 100 ? 'text-success' : ($harianPct > 0 ? 'text-warning' : 'text-secondary');

                                        $searchKeywords = strtolower($mk->nama_mapel . ' ' . $mk->nama_kelas . ' ' . ($mk->kode_mapel ?? ''));
                                        $parts = explode(' ', trim($mk->nama_kelas));
                                        $classPrefix = strtolower($parts[0]);
                                        ?>
                                        <tr class="mapel-item-row" data-keywords="<?= esc($searchKeywords) ?>" data-class-prefix="<?= esc($classPrefix) ?>">
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-square-fill me-1 <?= $harianSquareColor ?>"></i>
                                                    <span class="badge bg-light text-secondary border font-monospace me-1"><?= esc($mk->kode_mapel ?? '-') ?></span>
                                                    <b><?= esc($mk->nama_mapel) ?></b>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?= esc($mk->nama_kelas) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <b><?= $progress['total_siswa'] ?></b> Siswa
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px; min-width: 80px;">
                                                        <div class="progress-bar <?= $harianPct >= 100 ? 'bg-success' : ($harianPct > 0 ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $harianPct ?>%"></div>
                                                    </div>
                                                    <span class="small fw-bold"><?= $harianPct ?>%</span>
                                                </div>
                                                <small class="text-muted"><i class="bi bi-bullseye me-1"></i><?= $progress['total_tp'] ?> TP</small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 6px; min-width: 80px;">
                                                        <div class="progress-bar <?= $akhirPct >= 100 ? 'bg-success' : ($akhirPct > 0 ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $akhirPct ?>%"></div>
                                                    </div>
                                                    <span class="small fw-bold"><?= $akhirPct ?>%</span>
                                                </div>
                                                <small class="text-muted"><i class="bi bi-check2-circle me-1"></i><?= $progress['sudah_akhir'] ?? 0 ?>/<?= $progress['total_siswa'] ?> Selesai</small>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <div class="btn-action-group justify-content-center">
                                                    <a href="<?= base_url('guru/nilai/proses?mapel=' . $mk->id_mapel . '&kelas=' . $mk->id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>"
                                                        class="btn btn-action btn-action-text btn-action-info" title="Input Nilai Harian / Proses">
                                                        <i class="bi bi-pencil-fill me-1"></i> Harian
                                                    </a>
                                                    <a href="<?= base_url('guru/nilai/sts?mapel=' . $mk->id_mapel . '&kelas=' . $mk->id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>"
                                                        class="btn btn-action btn-action-text btn-action-warning" title="Input Nilai STS">
                                                        <i class="bi bi-calendar-check-fill me-1"></i> STS
                                                    </a>
                                                    <a href="<?= base_url('guru/nilai/akhir?mapel=' . $mk->id_mapel . '&kelas=' . $mk->id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>"
                                                        class="btn btn-action btn-action-text btn-action-primary" title="Input Nilai Akhir & Rapor">
                                                        <i class="bi bi-calculator-fill me-1"></i> Akhir
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Grid Mode View matching /cbtjadwal -->
        <div id="viewGridContainer" class="d-none">
            <div class="row g-3 mb-4">
                <?php foreach ($mapel_kelas as $mk): ?>
                    <?php
                    $progress = $mk->progress ?? ['persen' => 0, 'total_siswa' => 0, 'total_tp' => 0, 'persen_akhir' => 0, 'sudah_akhir' => 0];
                    $harianPct = (int)($progress['persen'] ?? 0);
                    $akhirPct = (int)($progress['persen_akhir'] ?? 0);

                    $harianSquareColor = $harianPct >= 100 ? 'text-success' : ($harianPct > 0 ? 'text-warning' : 'text-secondary');
                    $searchKeywords = strtolower($mk->nama_mapel . ' ' . $mk->nama_kelas . ' ' . ($mk->kode_mapel ?? ''));
                    $parts = explode(' ', trim($mk->nama_kelas));
                    $classPrefix = strtolower($parts[0]);
                    ?>
                    <div class="col-md-6 col-lg-4 mapel-item-grid" data-keywords="<?= esc($searchKeywords) ?>" data-class-prefix="<?= esc($classPrefix) ?>">
                        <div class="small-box bg-white border shadow-sm rounded p-3 h-100 d-flex flex-column justify-content-between">
                            <div class="inner">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate">
                                        <i class="bi bi-square-fill me-1 <?= $harianSquareColor ?>"></i>
                                        <?= esc($mk->nama_mapel) ?>
                                    </h6>
                                    <span class="badge bg-info ms-2"><?= esc($mk->nama_kelas) ?></span>
                                </div>
                                <div class="text-sm text-muted mb-2">
                                    Kode: <b><?= esc($mk->kode_mapel ?? '-') ?></b> | Siswa: <b><?= $progress['total_siswa'] ?></b>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between text-xs mb-1">
                                        <span>Progress Harian</span>
                                        <b><?= $harianPct ?>%</b>
                                    </div>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar <?= $harianPct >= 100 ? 'bg-success' : ($harianPct > 0 ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $harianPct ?>%"></div>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between text-xs mb-1">
                                        <span>Nilai Akhir Rapor</span>
                                        <b><?= $akhirPct ?>%</b>
                                    </div>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar <?= $akhirPct >= 100 ? 'bg-success' : ($akhirPct > 0 ? 'bg-warning' : 'bg-primary') ?>" style="width: <?= $akhirPct ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
                                <span class="small text-muted"><i class="bi bi-bullseye me-1"></i><?= $progress['total_tp'] ?> TP</span>
                                <div class="btn-action-group">
                                    <a href="<?= base_url('guru/nilai/proses?mapel=' . $mk->id_mapel . '&kelas=' . $mk->id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>"
                                        class="btn btn-action btn-action-text btn-action-info" title="Harian">
                                        <i class="bi bi-pencil-fill me-1"></i> Harian
                                    </a>
                                    <a href="<?= base_url('guru/nilai/sts?mapel=' . $mk->id_mapel . '&kelas=' . $mk->id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>"
                                        class="btn btn-action btn-action-text btn-action-warning" title="STS">
                                        <i class="bi bi-calendar-check-fill me-1"></i> STS
                                    </a>
                                    <a href="<?= base_url('guru/nilai/akhir?mapel=' . $mk->id_mapel . '&kelas=' . $mk->id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>"
                                        class="btn btn-action btn-action-text btn-action-primary" title="Akhir">
                                        <i class="bi bi-calculator-fill me-1"></i> Akhir
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<script>
    let activeClassFilter = 'all';
    let activeViewMode = 'list';

    function changeGuru(idGuru) {
        let url = new URL(window.location.href);
        if (idGuru) {
            url.searchParams.set('id_guru', idGuru);
        } else {
            url.searchParams.delete('id_guru');
        }
        window.location.href = url.toString();
    }

    function switchViewMode(mode) {
        activeViewMode = mode;
        const listContainer = document.getElementById('viewListContainer');
        const gridContainer = document.getElementById('viewGridContainer');
        const btnList = document.getElementById('btnModeList');
        const btnGrid = document.getElementById('btnModeGrid');

        if (mode === 'grid') {
            listContainer.classList.add('d-none');
            gridContainer.classList.remove('d-none');
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
        } else {
            gridContainer.classList.add('d-none');
            listContainer.classList.remove('d-none');
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
        }
    }

    function setFilterClass(filter, element) {
        activeClassFilter = filter;
        document.querySelectorAll('#filterPills .style-filter-pill').forEach(el => {
            el.classList.remove('bg-primary');
            el.classList.add('bg-secondary');
        });
        if (element) {
            element.classList.remove('bg-secondary');
            element.classList.add('bg-primary');
        }
        filterItems();
    }

    function filterItems() {
        const searchInput = document.getElementById('searchMapelInput');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

        // Filter Table Rows
        const rows = document.querySelectorAll('.mapel-item-row');
        rows.forEach(row => {
            const keywords = row.getAttribute('data-keywords') || '';
            const classPrefix = row.getAttribute('data-class-prefix') || '';
            const matchesQuery = query === '' || keywords.includes(query);
            const matchesClass = activeClassFilter === 'all' || classPrefix === activeClassFilter;

            if (matchesQuery && matchesClass) {
                row.classList.remove('d-none');
            } else {
                row.classList.add('d-none');
            }
        });

        // Filter Grid Cards
        const cards = document.querySelectorAll('.mapel-item-grid');
        cards.forEach(card => {
            const keywords = card.getAttribute('data-keywords') || '';
            const classPrefix = card.getAttribute('data-class-prefix') || '';
            const matchesQuery = query === '' || keywords.includes(query);
            const matchesClass = activeClassFilter === 'all' || classPrefix === activeClassFilter;

            if (matchesQuery && matchesClass) {
                card.classList.remove('d-none');
            } else {
                card.classList.add('d-none');
            }
        });
    }
</script>

<?= $this->endSection() ?>