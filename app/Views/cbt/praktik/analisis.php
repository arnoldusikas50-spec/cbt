<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-content-header">
    <div class="container-fluid ps-3 ps-lg-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold" style="color: #00695c;">
                    <i class="bi bi-graph-up-arrow me-2"></i>Analisis & Remedial
                </h3>
                <p class="text-muted small mb-0">
                    <span class="badge bg-dark bg-opacity-75 me-1"><?= esc($praktik->judul) ?></span>
                    <span class="badge bg-secondary">KKM: <?= $praktik->kkm ?></span>
                    <span class="badge bg-info">Mode: <?= strtoupper($praktik->kktp_mode ?? 'KKM') ?></span>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end">
                <a href="<?= site_url('cbtpraktik/exportReport/' . $praktik->id) ?>" class="btn btn-success shadow-sm px-3 rounded-pill">
                    <i class="bi bi-file-earmark-excel me-1"></i> Ekspor
                </a>
                <a href="<?= site_url('cbtpraktik') ?>" class="btn btn-outline-secondary shadow-sm px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid px-3 px-lg-4">
        
        <!-- Summary Stats Cards -->
        <div class="row g-3 mb-4 fade-in-up">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Total Siswa</div>
                            <h4 class="fw-extrabold mb-0 text-dark"><?= $stats['total'] ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Tuntas</div>
                            <h4 class="fw-extrabold mb-0 text-dark"><?= $stats['lulus'] ?> <small class="fs-6 fw-normal text-success"><?= $stats['persen_lulus'] ?>%</small></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-person-x-fill"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Remedial</div>
                            <h4 class="fw-extrabold mb-0 text-dark"><?= $stats['remedial'] ?> <small class="fs-6 fw-normal text-danger"><?= 100 - $stats['persen_lulus'] ?>%</small></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-info bg-opacity-10 text-info">
                            <i class="bi bi-calculator-fill"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Rerata Nilai</div>
                            <h4 class="fw-extrabold mb-0 text-dark"><?= number_format($praktik->rata_nilai ?? 0, 1) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KKTP Distribution Chart -->
        <?php $kktpColors = ['M' => '#10b981', 'C' => '#3b82f6', 'L' => '#f59e0b', 'BB' => '#ef4444']; ?>
        <?php $kktpLabels = ['M' => 'Mahir', 'C' => 'Cakap', 'L' => 'Layak', 'BB' => 'Baru Berkembang']; ?>
        <?php $maxDist = max(array_values($kktp_distribution)) ?: 1; ?>
        <div class="row g-3 mb-4 fade-in-up">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 km-table-card p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="km-tips-icon me-3 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <h6 class="fw-extrabold text-dark mb-0">Distribusi Predikat KKTP</h6>
                    </div>
                    <div class="row g-3">
                        <?php foreach (['M', 'C', 'L', 'BB'] as $p): ?>
                        <?php $pct = $kktp_distribution[$p] > 0 ? round($kktp_distribution[$p] / $stats['total'] * 100, 1) : 0; ?>
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: <?= $kktpColors[$p] ?>08; border: 1px solid <?= $kktpColors[$p] ?>20;">
                                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white" style="width: 44px; height: 44px; background: <?= $kktpColors[$p] ?>; font-size: 0.75rem;"><?= $p ?></div>
                                <div>
                                    <div class="fw-extrabold fs-4 mb-0"><?= $kktp_distribution[$p] ?></div>
                                    <div class="text-muted small"><?= $kktpLabels[$p] ?> <span class="fw-bold">(<?= $pct ?>%)</span></div>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 4px; background: #f1f5f9;">
                                <div class="progress-bar" role="progressbar" style="width: <?= $kktp_distribution[$p] / $maxDist * 100 ?>%; background: <?= $kktpColors[$p] ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Detailed Analysis & Recommendations -->
            <div class="col-lg-8 fade-in-up">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="km-tips-icon me-3 bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-lightbulb-fill"></i>
                        </div>
                        <h6 class="fw-extrabold text-dark mb-0">Rekomendasi & Analisis Kriteria</h6>
                    </div>

                    <!-- Recommendations Section -->
                    <div class="mb-4">
                        <?php if (empty($recommendations)): ?>
                            <div class="p-3 bg-success bg-opacity-5 border border-success border-opacity-10 rounded-3 text-success d-flex align-items-center small">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <span>Performa siswa sudah memenuhi standar kriteria.</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recommendations as $rec): ?>
                                <div class="p-2 px-3 bg-light rounded-3 mb-2 border-start border-warning border-4 d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle text-warning small"></i>
                                    <div class="text-dark small fw-medium"><?= $rec ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Statistik Capaian Kriteria</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle km-input-table">
                            <thead>
                                <tr class="km-thead-row">
                                    <th class="text-start ps-3">Kriteria</th>
                                    <th style="width: 100px;">Rerata</th>
                                    <th>Visualisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kriteria_stats as $id => $stat): 
                                    $avg = $stat['count'] > 0 ? ($stat['total_nilai'] / $stat['count']) : 0;
                                    $weight = 100;
                                    foreach ($praktik->rubrik ?? [] as $r) if ($r->id == $id) { $weight = $r->bobot_persen; break; }
                                    $perc = $weight > 0 ? ($avg / $weight * 100) : 0;
                                    $color = $perc >= 75 ? 'success' : ($perc >= 60 ? 'warning' : 'danger');
                                ?>
                                <tr class="km-data-row">
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark small"><?= esc($stat['nama']) ?></div>
                                        <div class="text-muted" style="font-size: 0.65rem;"><?= $stat['count'] ?> Penilaian</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold text-dark"><?= number_format($avg, 1) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; background: #f1f5f9;">
                                                <div class="progress-bar bg-<?= $color ?>" role="progressbar" style="width: <?= $perc ?>%"></div>
                                            </div>
                                            <span class="fw-bold text-<?= $color ?> small" style="min-width: 35px; font-size: 0.7rem;"><?= round($perc) ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Remedial Student List -->
            <div class="col-lg-4 fade-in-up">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="km-tips-icon me-3 bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-person-x-fill"></i>
                            </div>
                            <h6 class="fw-extrabold text-dark mb-0">Daftar Remedial</h6>
                        </div>
                        <span class="badge bg-danger rounded-pill px-2" style="font-size: 0.7rem;"><?= count($remedial_siswa) ?></span>
                    </div>

                    <?php if (empty($remedial_siswa)): ?>
                        <div class="text-center py-4">
                            <div class="text-success mb-2"><i class="bi bi-emoji-smile-fill fs-2"></i></div>
                            <p class="text-muted small">Semua siswa tuntas!</p>
                        </div>
                    <?php else: ?>
                        <div class="list-siswa-remedial">
                            <?php foreach ($remedial_siswa as $siswa): ?>
                                <div class="p-3 bg-light rounded-3 mb-2 border border-white shadow-xs">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold text-dark small"><?= esc($siswa['nama']) ?></div>
                                        <span class="badge bg-danger bg-opacity-10 text-danger small"><?= $siswa['nilai'] ?></span>
                                    </div>
                                    <?php if (!empty($siswa['weak_points'])): ?>
                                        <div class="text-muted" style="font-size: 0.65rem;">
                                            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
                                            <?= implode(', ', array_map('esc', $siswa['weak_points'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    
    .km-table-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .km-tips-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }

    .km-input-table { border-collapse: separate; border-spacing: 0; width: 100%; }
    .km-thead-row th {
        background: #f8fafc !important;
        border-bottom: 2px solid #f1f5f9 !important;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        padding: 0.75rem 0.5rem !important;
        text-align: center;
    }
    .km-data-row:hover { background: #fcfcfc; }
    .km-data-row td { border-bottom: 1px solid #f1f5f9 !important; padding: 0.75rem 0.5rem; }

    .fade-in-up { animation: fadeInUp 0.5s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .fw-extrabold { font-weight: 800; }
</style>
<?= $this->endSection() ?>
