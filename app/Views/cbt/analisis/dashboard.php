<?= $this->extend('layouts/main') ?>

<?= $this->section('body_class') ?>sidebar-expand-lg bg-body-tertiary text-sm<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* Simple fixes for scroll stability */
html, body {
    overflow-x: hidden;
    overflow-y: auto !important;
    height: auto !important;
}

.app-wrapper, .app-main {
    overflow: visible !important;
    height: auto !important;
}

/* Disable animations */
.accordion-collapse {
    transition: none !important;
}

.fade-in-up {
    animation: none !important;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Prevent push-menu error -->
<script>
// Ensure sidebar exists before push-menu initializes
if (!document.querySelector('.app-sidebar')) {
    console.warn('Sidebar not found, push-menu may fail');
}
// Disable push-menu auto-initialization errors
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes('getComputedStyle')) {
        console.warn('Push-menu error suppressed');
        e.stopImmediatePropagation();
        e.preventDefault();
    }
});
</script>

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clipboard2-data-fill text-primary me-2"></i>
                    Analisis Hasil Ujian
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Kurikulum Merdeka') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <form method="get" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold"><i class="bi bi-calendar-event me-1"></i>Pilih Jadwal Ujian</label>
                        <select name="id_jadwal" class="form-select select2" required>
                            <option value="">-- Pilih Jadwal --</option>
                            <?php foreach ($jadwals as $jadwal): ?>
                                <option value="<?= $jadwal->id_jadwal ?>" <?= ($id_jadwal_selected == $jadwal->id_jadwal) ? 'selected' : '' ?>>
                                    [<?= $jadwal->kode_mapel ?? '' ?>] - <?= $jadwal->bank_nama ?? ($jadwal->nama_mapel ?? 'Ujian') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold"><i class="bi bi-door-open me-1"></i>Pilih Kelas (Opsional)</label>
                        <select name="id_kelas" class="form-select select2">
                            <option value="">-- Semua Kelas --</option>
                            <?php foreach ($kelas as $id_kelas_opt => $nama_kelas_opt): ?>
                                <option value="<?= $id_kelas_opt ?>" <?= ($id_kelas_selected == $id_kelas_opt) ? 'selected' : '' ?>>
                                    <?= $nama_kelas_opt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-check me-2"></i>PROSES ANALISIS
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($analisis)): ?>
            <!-- Toolbar & Stats Info -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div class="d-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm border">
                    <i class="bi bi-clock-history text-muted me-2"></i>
                    <small class="text-muted">
                        Diproses: <b><?= $analisis['jumlah_siswa'] ?></b> Siswa | 
                        <b><?= $analisis['jumlah_soal'] ?></b> Soal | 
                        <b><?= $analisis['execution_time'] ?></b> dtk
                    </small>
                </div>
                <div class="btn-group shadow-sm">
                    <a href="<?= site_url('cbtanalysis/exportExcel?id_jadwal=' . $id_jadwal_selected . '&id_kelas=' . $id_kelas_selected) ?>" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                    <a href="<?= site_url('cbtanalysis/exportPdf?id_jadwal=' . $id_jadwal_selected . '&id_kelas=' . $id_kelas_selected) ?>" class="btn btn-danger" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <button class="btn btn-info text-white" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Cetak
                    </button>
                </div>
            </div>

            <!-- Dashboard Stats Icons -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="info-box bg-white shadow-sm border-0" style="border-left: 4px solid #198754 !important;">
                        <span class="info-box-icon bg-success bg-gradient text-white elevation-1"><i class="bi bi-star-fill"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-success fw-bold">Siswa Mahir</span>
                            <span class="info-box-number h4 mb-0"><?= count($analisis['kategori_siswa']['mahir'] ?? []) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-white shadow-sm border-0" style="border-left: 4px solid #0d6efd !important;">
                        <span class="info-box-icon bg-primary bg-gradient text-white elevation-1"><i class="bi bi-award-fill"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-primary fw-bold">Siswa Cakap</span>
                            <span class="info-box-number h4 mb-0"><?= count($analisis['kategori_siswa']['cakap'] ?? []) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-white shadow-sm border-0" style="border-left: 4px solid #ffc107 !important;">
                        <span class="info-box-icon bg-warning bg-gradient text-white elevation-1"><i class="bi bi-shield-fill-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-warning fw-bold">Siswa Dasar</span>
                            <span class="info-box-number h4 mb-0"><?= count($analisis['kategori_siswa']['dasar'] ?? []) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-white shadow-sm border-0" style="border-left: 4px solid #dc3545 !important;">
                        <span class="info-box-icon bg-danger bg-gradient text-white elevation-1"><i class="bi bi-exclamation-triangle-fill"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-danger fw-bold" style="font-size: 0.85rem;">Perlu Intervensi</span>
                            <span class="info-box-number h4 mb-0"><?= count($analisis['kategori_siswa']['intervensi'] ?? []) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Deskriptif -->
            <?php
            $sd = $analisis['statistik_deskriptif'] ?? [];
            $rk = $analisis['ringkasan_kualitas'] ?? [];
            $normClass = [
                'good' => 'success',
                'warning' => 'warning',
                'danger' => 'danger'
            ][$sd['normality']['level'] ?? 'good'] ?? 'secondary';
            ?>
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Statistik Deskriptif & Kualitas Tes
                    </h5>
                    <span class="badge bg-<?= $normClass ?> px-3 py-2 rounded-pill">
                        <i class="bi bi-activity me-1"></i>Distribusi: <?= esc($sd['normality']['label'] ?? '-') ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Mean (Rata-rata)</div>
                                <div class="h4 mb-0 fw-bold text-primary"><?= $sd['mean'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Median</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= $sd['median'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Modus</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= $sd['modus'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Std. Deviasi</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= $sd['std_dev'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Min - Max</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= $sd['min'] ?? 0 ?><span class="text-muted">–</span><?= $sd['max'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Q1 – Q3 (IQR)</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= $sd['q1'] ?? 0 ?><span class="text-muted">–</span><?= $sd['q3'] ?? 0 ?></div>
                                <div class="small text-muted">IQR <?= $sd['iqr'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Skewness</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= $sd['skewness'] ?? 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <div class="border rounded-3 p-3 text-center h-100">
                                <div class="small text-muted mb-1">Reliabilitas (α)</div>
                                <div class="h4 mb-0 fw-bold text-dark"><?= $rk['reliability']['alpha_cronbach'] ?? 0 ?></div>
                                <div class="small text-muted"><?= esc($rk['reliability']['label'] ?? '-') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 small text-muted bg-light rounded-3 p-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <b>Interpretasi:</b> <?= esc($sd['normality']['interpretasi'] ?? '-') ?>
                        <?php if (!empty($rk)): ?>
                            &nbsp;•&nbsp; <b>Kualitas butir:</b> <?= $rk['baik'] ?? 0 ?>/<?= $rk['n'] ?? 0 ?> butir layak
                            (<?= $rk['persen_baik'] ?? 0 ?>%), <?= $rk['perlu_revisi'] ?? 0 ?> butir perlu revisi.
                            Rata-rata kesukaran P = <?= $rk['avg_kesukaran'] ?? 0 ?>, daya beda D = <?= $rk['avg_daya_beda'] ?? 0 ?>.
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Topik Bermasalah</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartTopikBermasalah" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-pie-chart-fill text-info me-2"></i>Distribusi Kategori</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartKategoriSiswa" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-graph-up text-success me-2"></i>Distribusi Skor Siswa</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartHistogramSkor" height="300"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-sliders text-warning me-2"></i>Kualitas Daya Beda Butir</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartDayaBedaButir" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analysis Accordion -->
            <div class="accordion mb-4 shadow-sm" id="analysisAccordion">

                
                <!-- Analisis Psikometri Butir (Item Analysis p-value, d-value, KR-20) -->
                <div class="accordion-item border-0 mb-2 overflow-hidden" style="border-radius: 12px !important;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-white text-dark fw-bold" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapsePsikometri"
                            onclick="loadItemAnalysis()">
                            <i class="bi bi-graph-up-arrow text-success me-2"></i>ANALISIS PSIKOMETRI BUTIR SOAL (p-value, d-value, KR-20)
                        </button>
                    </h2>
                    <div id="collapsePsikometri" class="accordion-collapse collapse" data-bs-parent="#analysisAccordion">
                        <div class="accordion-body p-0">
                            <div id="item-analysis-loading" class="p-4 text-center text-muted d-none">
                                <div class="spinner-border spinner-border-sm text-primary me-2"></div> Menghitung analisis butir soal...
                            </div>
                            <div id="item-analysis-result" class="d-none">
                                <!-- Reliability KR-20 Summary -->
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="text-center">
                                            <div class="small text-muted fw-bold">Reliabilitas (KR-20)</div>
                                            <span id="kr20-value" class="fw-bold fs-5">-</span>
                                            <span id="kr20-badge" class="badge ms-1">-</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="small text-muted fw-bold">Jumlah Soal</div>
                                            <span id="ia-n-soal" class="fw-bold fs-5">-</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="small text-muted fw-bold">Jumlah Peserta</div>
                                            <span id="ia-n-peserta" class="fw-bold fs-5">-</span>
                                        </div>
                                    </div>
                                    <div class="small text-muted fst-italic">
                                        <i class="bi bi-info-circle me-1"></i> p = Tingkat Kesukaran &nbsp;|&nbsp; d = Daya Beda (Upper-Lower 27%)
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="table-item-analysis">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="px-3" width="60">No</th>
                                                <th>ID Soal</th>
                                                <th width="120">p-value (Kesukaran)</th>
                                                <th width="120">d-value (Daya Beda)</th>
                                                <th width="80">Benar/N</th>
                                                <th width="100">Bobot IRT</th>
                                            </tr>
                                        </thead>
                                        <tbody id="item-analysis-tbody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="item-analysis-empty" class="p-4 text-center text-muted">
                                <i class="bi bi-bar-chart-steps fs-2 d-block mb-2"></i>
                                Klik header untuk memuat Analisis Psikometri Butir Soal.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Butir Soal -->
                <div class="accordion-item border-0 mb-2 overflow-hidden" style="border-radius: 12px !important;">
                    <h2 class="accordion-header">
                        <button class="accordion-button bg-white text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseButir">
                            <i class="bi bi-list-check text-primary me-2"></i>ANALISIS BUTIR SOAL & VALIDITAS
                        </button>
                    </h2>
                    <div id="collapseButir" class="accordion-collapse collapse show" data-bs-parent="#analysisAccordion">
                        <div class="accordion-body p-0">
                            <!-- Filters for Butir -->
                            <div class="p-3 bg-light border-bottom">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" id="filter-jenis-butir">
                                            <option value="">Semua Jenis Soal</option>
                                            <option value="1">1 - Pilihan Ganda</option>
                                            <option value="2">2 - PG Kompleks</option>
                                            <option value="3">3 - Menjodohkan</option>
                                            <option value="4">4 - Isian Singkat</option>
                                            <option value="5">5 - Essai</option>
                                            <option value="6">6 - Urutan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" id="filter-kesukaran-butir">
                                            <option value="">Semua Kesukaran</option>
                                            <option value="Mudah">Mudah</option>
                                            <option value="Sedang">Sedang</option>
                                            <option value="Sulit">Sulit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100" id="btn-toggle-bermasalah">
                                            <i class="bi bi-bug me-1"></i>Hanya Butir Bermasalah
                                        </button>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <span class="badge bg-secondary px-3 py-2 rounded-pill" id="filter-butir-result-count">0 butir</span>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="table-analisis-butir">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-3" width="60">No</th>
                                            <th>Preview Soal</th>
                                            <th width="120">P (Kesukaran)</th>
                                            <th width="120">D (Daya Beda)</th>
                                            <th width="100">Validitas</th>
                                            <th width="120">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $itemsAnalisis = $analisis['analisis_butir']['items'] ?? [];
                                        foreach ($itemsAnalisis as $item): 
                                            $isBermasalah = ($item['daya_beda'] < 0.20 || $item['validitas_item'] < 0.30);
                                        ?>
                                        <tr 
                                            data-jenis="<?= esc($item['jenis']) ?>"
                                            data-kesukaran="<?= esc($item['kategori_kesukaran']) ?>"
                                            data-daya-beda="<?= esc($item['daya_beda']) ?>"
                                            data-p="<?= esc($item['tingkat_kesukaran']) ?>"
                                            data-validitas="<?= esc($item['validitas_item']) ?>"
                                            class="<?= $isBermasalah ? 'table-danger-light' : '' ?>">
                                            <td class="px-3 text-center fw-bold text-muted"><?= esc($item['nomor_soal']) ?></td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 300px;">
                                                    <small class="text-dark"><?= esc($item['preview_soal'] ?? '-') ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-bold me-2"><?= esc($item['tingkat_kesukaran']) ?></span>
                                                    <span class="badge bg-<?= ($item['kategori_kesukaran'] === 'Mudah') ? 'success' : (($item['kategori_kesukaran'] === 'Sulit') ? 'danger' : 'warning') ?> btn-circle-micro">
                                                        <?= strtoupper(substr($item['kategori_kesukaran'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold"><?= esc($item['daya_beda']) ?></span>
                                                <small class="d-block text-muted" style="font-size: 0.7rem;"><?= esc($item['kategori_daya_beda']) ?></small>
                                            </td>
                                            <td><?= esc($item['validitas_item']) ?></td>
                                            <td>
                                                <?php if ($isBermasalah): ?>
                                                    <span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>REVISI</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>OK</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rencana Remedial -->
                <div class="accordion-item border-0 mb-2 overflow-hidden" style="border-radius: 12px !important;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-white text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRemedial">
                            <i class="bi bi-clipboard-check text-warning me-2"></i>RENCANA PROGRAM REMEDIAL
                        </button>
                    </h2>
                    <div id="collapseRemedial" class="accordion-collapse collapse" data-bs-parent="#analysisAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-3">TP / Tujuan Pembelajaran</th>
                                            <th width="100">% Gagal</th>
                                            <th width="200">Jenis Remedial</th>
                                            <th>Strategi Pembelajaran</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($analisis['rencana_remedial'])): ?>
                                            <?php foreach ($analisis['rencana_remedial'] as $remedial): ?>
                                            <tr>
                                                <td class="px-3">
                                                    <span class="badge bg-light text-primary border mb-1"><?= $remedial['tujuan_kode'] ?></span>
                                                    <div class="small text-dark fw-bold"><?= $remedial['tujuan_teks'] ?></div>
                                                </td>
                                                <td><span class="text-danger fw-bold"><?= $remedial['persentase_kesalahan'] ?>%</span></td>
                                                <td><span class="badge bg-info bg-gradient px-3"><?= $remedial['jenis_remediasi'] ?></span></td>
                                                <td><small class="text-muted"><?= $remedial['strategi'] ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted italic">Tidak ada topik yang memerlukan remedial intensif.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prediksi & Rekomendasi -->
                <div class="accordion-item border-0 mb-2 overflow-hidden" style="border-radius: 12px !important;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-white text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrediksi">
                            <i class="bi bi-lightbulb text-success me-2"></i>PREDIKSI & REKOMENDASI INDIVIDUAL
                        </button>
                    </h2>
                    <div id="collapsePrediksi" class="accordion-collapse collapse" data-bs-parent="#analysisAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-3">Siswa</th>
                                            <th width="120">Prediksi Skor</th>
                                            <th width="120">Kategori</th>
                                            <th>Rekomendasi Tindak Lanjut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($analisis['prediksi_siswa'])): ?>
                                            <?php foreach ($analisis['prediksi_siswa'] as $prediksi): ?>
                                            <tr>
                                                <td class="px-3">
                                                    <div class="fw-bold text-dark"><?= $prediksi['nama_siswa'] ?></div>
                                                    <small class="text-muted">NIS: <?= $prediksi['nis'] ?></small>
                                                </td>
                                                <td><span class="h5 mb-0 fw-bold text-primary"><?= $prediksi['nilai_prediksi'] ?></span></td>
                                                <td>
                                                    <span class="badge bg-<?= ($prediksi['kategori'] == 'Mahir') ? 'success' : (($prediksi['kategori'] == 'Cakap') ? 'primary' : (($prediksi['kategori'] == 'Dasar') ? 'warning text-dark' : 'danger')) ?> rounded-pill">
                                                        <?= $prediksi['kategori'] ?>
                                                    </span>
                                                </td>
                                                <td><small class="text-muted italic"><?= $prediksi['rekomendasi'] ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted italic">Tidak tersedia data prediksi individual.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 20px;">
                <div class="card-body">
                    <img src="<?= base_url('assets/img/analytics.svg') ?>" alt="Analisis" class="mb-4" style="height: 180px; opacity: 0.7;">
                    <h4 class="fw-bold text-dark">Data Analisis Siap Diproses</h4>
                    <p class="text-muted mx-auto mb-4" style="max-width: 450px;">
                        Silakan pilih <b>Jadwal Ujian</b> di atas untuk memulai kalkulasi statistik butir soal, 
                        analisis Tujuan Pembelajaran, dan rekomendasi program remedial.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <div class="badge bg-light text-primary border p-2 px-3 shadow-sm">
                            <i class="bi bi-shield-check me-1"></i> Berbasis Kurikulum Merdeka
                        </div>
                        <div class="badge bg-light text-success border p-2 px-3 shadow-sm">
                            <i class="bi bi-clock-history me-1"></i> Hasil Real-time
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .table-danger-light { background-color: rgba(220, 53, 69, 0.03); }
    .accordion-button:not(.collapsed) {
        box-shadow: none;
        background-color: #f8fbff;
        color: var(--premium-primary);
    }
    .accordion-button:focus { box-shadow: none; }
    .info-box-icon { border-radius: 12px; }
    .btn-circle-micro { width: 18px; height: 18px; padding: 0; line-height: 18px; text-align: center; }
    @media print {
        .app-content-header, .accordion-header, .btn-group, form, .bg-light { display: none !important; }
        .collapse { display: block !important; }
        .accordion-item { border: 1px solid #ddd !important; margin-bottom: 20px; }
        .app-main { margin-left: 0 !important; padding-top: 0 !important; }
    }
</style>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Disable OverlayScrollbars for this page to prevent scroll flickering
$(document).ready(function() {
    // Destroy any existing overlay scrollbars
    if (typeof $.fn.overlayScrollbars !== 'undefined') {
        $('.os-host').overlayScrollbars('destroy');
        $('body').overlayScrollbars('destroy');
    }
    
    // Force native scroll
    $('body').css('overflow', 'auto');
    $('.app-main').css('overflow', 'visible');
    
    // Disable AdminLTE auto scroll behavior
    if (typeof $ !== 'undefined' && $.AdminLTE) {
        $.AdminLTE.options.scrollbarTheme = false;
    }
});

$(document).ready(function() {
    // Disable Chart.js defaults BEFORE creating charts
    if (typeof Chart !== 'undefined') {
        Chart.defaults.animation = false;
        Chart.defaults.transitions = {};
        Chart.defaults.responsive = false; // DISABLE resize observer!
        Chart.defaults.maintainAspectRatio = false;
    }
    
    <?php if (!empty($chart_data) && !empty($analisis)): ?>
    const chartData = <?= json_encode($chart_data) ?>;
    
    // Topik Bermasalah - with fixed dimensions, no resize
    if ($('#chartTopikBermasalah').length && chartData.topik_bermasalah && chartData.topik_bermasalah.labels) {
        $('#chartTopikBermasalah').attr('width', 400).attr('height', 300);
        new Chart($('#chartTopikBermasalah')[0].getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.topik_bermasalah.labels,
                datasets: [{
                    label: '% Kesalahan',
                    data: chartData.topik_bermasalah.data,
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#6366f1'],
                    borderRadius: 8,
                    barThickness: 35
                }]
            },
            options: {
                responsive: false, // FIXED - no resize observer
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    }

    // Kategori Siswa - with fixed dimensions, no resize
    if ($('#chartKategoriSiswa').length && chartData.kategori_siswa && chartData.kategori_siswa.labels) {
        $('#chartKategoriSiswa').attr('width', 400).attr('height', 300);
        new Chart($('#chartKategoriSiswa')[0].getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chartData.kategori_siswa.labels,
                datasets: [{
                    data: chartData.kategori_siswa.data,
                    backgroundColor: chartData.kategori_siswa.backgroundColor,
                    hoverOffset: 0, // Disable hover animation
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false, // FIXED - no resize observer
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' }, cutout: '70%'},
                animation: false // Disable animation
            }
        });
    }

    // Histogram distribusi skor
    if ($('#chartHistogramSkor').length && chartData.histogram_skor && chartData.histogram_skor.labels) {
        $('#chartHistogramSkor').attr('width', 400).attr('height', 300);
        new Chart($('#chartHistogramSkor')[0].getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.histogram_skor.labels,
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: chartData.histogram_skor.data,
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barThickness: 30
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Jumlah Siswa' } },
                    x: { title: { display: true, text: 'Rentang Skor' } }
                }
            }
        });
    }

    // Kualitas daya beda butir
    if ($('#chartDayaBedaButir').length && chartData.daya_beda_butir && chartData.daya_beda_butir.labels) {
        $('#chartDayaBedaButir').attr('width', 400).attr('height', 300);
        new Chart($('#chartDayaBedaButir')[0].getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: chartData.daya_beda_butir.labels,
                datasets: [{
                    data: chartData.daya_beda_butir.data,
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    hoverOffset: 0,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' }, cutout: '65%' },
                animation: false
            }
        });
    }

    // Butir Filter Logic
    const initButirFilter = () => {
        const fJenis = $('#filter-jenis-butir');
        const fKesukaran = $('#filter-kesukaran-butir');
        const btnBermasalah = $('#btn-toggle-bermasalah');
        const rows = $('#table-analisis-butir tbody tr');
        let bermasalahOnly = false;

        const applyFilter = () => {
            let visible = 0;
            const jenis = fJenis.val();
            const kesukaran = fKesukaran.val();

            rows.each(function() {
                const rJenis = $(this).attr('data-jenis');
                const rKesukaran = $(this).attr('data-kesukaran');
                const rD = parseFloat($(this).attr('data-daya-beda'));
                const rV = parseFloat($(this).attr('data-validitas'));

                const matchJenis = !jenis || rJenis === jenis;
                const matchKesukaran = !kesukaran || rKesukaran === kesukaran;
                const matchBermasalah = !bermasalahOnly || (rD < 0.20 || rV < 0.30);

                if (matchJenis && matchKesukaran && matchBermasalah) {
                    $(this).show();
                    visible++;
                } else {
                    $(this).hide();
                }
            });
            $('#filter-butir-result-count').text(visible + ' butir');
        };

        fJenis.on('change', applyFilter);
        fKesukaran.on('change', applyFilter);
        btnBermasalah.on('click', function() {
            bermasalahOnly = !bermasalahOnly;
            $(this).toggleClass('btn-danger btn-outline-danger');
            applyFilter();
        });
        applyFilter();
    };
    initButirFilter();
    <?php endif; ?>

    // Initialize Select2 if exists
    if ($('.select2').length) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
    
    // Disable Chart.js animations to prevent scroll flickering
    if (typeof Chart !== 'undefined') {
        Chart.defaults.animation = false;
        Chart.defaults.transitions = {};
    }

    // ── Analisis Psikometri Butir Soal (p-value, d-value, KR-20) ──────────────
    let itemAnalysisLoaded = false;
    window.loadItemAnalysis = function () {
        if (itemAnalysisLoaded) return;
        const idJadwal = <?= (int) ($id_jadwal_selected ?? 0) ?>;
        if (!idJadwal) {
            $('#item-analysis-empty').text('Pilih jadwal ujian terlebih dahulu.').removeClass('d-none');
            return;
        }
        $('#item-analysis-empty').addClass('d-none');
        $('#item-analysis-loading').removeClass('d-none');

        fetch(`<?= site_url('cbtanalysis/getItemAnalysis') ?>?id_jadwal=${idJadwal}`)
            .then(r => r.json())
            .then(res => {
                $('#item-analysis-loading').addClass('d-none');
                if (!res.status) {
                    $('#item-analysis-empty').text(res.message || 'Data tidak tersedia.').removeClass('d-none');
                    return;
                }
                const d = res.data;

                // KR-20 header
                $('#kr20-value').text(d.kr20 !== null ? d.kr20 : 'N/A');
                $('#kr20-badge').attr('class', `badge bg-${d.kr20_badge} ms-1`).text(d.kr20_label);
                $('#ia-n-soal').text(d.n_soal);
                $('#ia-n-peserta').text(d.n_peserta);

                // Build table rows
                const tbody = $('#item-analysis-tbody').empty();
                d.items.forEach(item => {
                    tbody.append(`<tr>
                        <td class="px-3">${item.no}</td>
                        <td><span class="badge bg-secondary">#${item.id_soal}</span></td>
                        <td>
                            <span class="badge bg-${item.p_badge}">${item.p_label}</span>
                            <small class="text-muted ms-1">${item.p_value}</small>
                        </td>
                        <td>
                            <span class="badge bg-${item.d_badge}">${item.d_label}</span>
                            <small class="text-muted ms-1">${item.d_value}</small>
                        </td>
                        <td>${item.benar}/${item.peserta}</td>
                        <td><span class="badge bg-primary bg-opacity-75">${item.bobot_irt}</span></td>
                    </tr>`);
                });

                $('#item-analysis-result').removeClass('d-none');
                itemAnalysisLoaded = true;
            })
            .catch(() => {
                $('#item-analysis-loading').addClass('d-none');
                $('#item-analysis-empty').text('Gagal memuat data. Coba lagi.').removeClass('d-none');
            });
    };
});
</script>

<?= $this->endSection() ?>
