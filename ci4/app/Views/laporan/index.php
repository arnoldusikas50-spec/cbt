<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-text-fill fa-4x text-primary mb-3" style="font-size:48px;color:var(--bs-primary)"></i>
                        <h5>Berita Acara</h5>
                        <p class="text-muted">Laporan berita acara ujian</p>
                        <a href="<?= site_url('laporan/beritaAcara') ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>Buka
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-success card-outline">
                    <div class="card-body text-center">
                        <i class="bi bi-file-earmark-text fa-4x text-success mb-3" style="font-size:48px;color:var(--bs-success)"></i>
                        <h5>Pakta Integritas</h5>
                        <p class="text-muted">Dokumen pakta integritas</p>
                        <a href="<?= site_url('laporan/paktaIntegritas') ?>" class="btn btn-success btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>Buka
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-warning card-outline">
                    <div class="card-body text-center">
                        <i class="bi bi-pie-chart-fill fa-4x text-warning mb-3" style="font-size:48px;color:var(--bs-warning)"></i>
                        <h5>Rekap Nilai</h5>
                        <p class="text-muted">Rekap nilai per kelas/mapel</p>
                        <a href="<?= site_url('cbtnilai') ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>Buka
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>