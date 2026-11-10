<?php
// Dashboard Penilaian Kurikulum Merdeka
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fw-bold text-primary"><i class="bi bi-award me-2"></i>Penilaian Kurikulum Merdeka</h4>
                            <p class="text-muted mb-0">Sesuai Permendikbudristek No. 53/2023 tentang Standar Penilaian Pendidikan</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= site_url('kurikulum-merdeka/audit-keamanan') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-shield-check me-1"></i>Audit Keamanan
                            </a>
                            <a href="<?= site_url('kurikulum-merdeka/laporan-p5') ?>" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-1"></i>Laporan P5
                            </a>
                            <a href="<?= site_url('kurikulum-merdeka/input-penilaian') ?>" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>Input Penilaian
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold"><?= $statistik['jumlah_penilaian'] ?? 0 ?></div>
                    <div class="small">Total Penilaian</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold"><?= $statistik['jumlah_portofolio'] ?? 0 ?></div>
                    <div class="small">Portofolio Digital</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold"><?= $statistik['jumlah_p5'] ?? 0 ?></div>
                    <div class="small">Proyek P5</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="display-4 fw-bold"><?= $statistik['jumlah_nilai_akhir'] ?? 0 ?></div>
                    <div class="small">Nilai Akhir</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Komponen Penilaian -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Komponen Penilaian Sesuai Permendikbudristek No. 53/2023</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php 
                        $pengetahuan = array_filter($komponen_penilaian, function($k) { return $k->jenis_penilaian == 'Pengetahuan'; });
                        $keterampilan = array_filter($komponen_penilaian, function($k) { return $k->jenis_penilaian == 'Keterampilan'; });
                        $sikap = array_filter($komponen_penilaian, function($k) { return $k->jenis_penilaian == 'Sikap'; });
                        ?>
                        
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary bg-opacity-10">
                                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-book me-2"></i>Pengetahuan</h6>
                                </div>
                                <div class="card-body p-2">
                                    <?php foreach ($pengetahuan as $k): ?>
                                        <div class="alert alert-primary alert-sm mb-2">
                                            <strong><?= $k->kode_komponen ?></strong>: <?= $k->nama_komponen ?>
                                            <br><small class="text-muted">Bobot: <?= $k->bobot_minimal ?>% - <?= $k->bobot_maksimal ?>%</small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-success bg-opacity-10">
                                    <h6 class="mb-0 fw-bold text-success"><i class="bi bi-tools me-2"></i>Keterampilan</h6>
                                </div>
                                <div class="card-body p-2">
                                    <?php foreach ($keterampilan as $k): ?>
                                        <div class="alert alert-success alert-sm mb-2">
                                            <strong><?= $k->kode_komponen ?></strong>: <?= $k->nama_komponen ?>
                                            <br><small class="text-muted">Bobot: <?= $k->bobot_minimal ?>% - <?= $k->bobot_maksimal ?>%</small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning bg-opacity-10">
                                    <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-heart me-2"></i>Sikap</h6>
                                </div>
                                <div class="card-body p-2">
                                    <?php foreach ($sikap as $k): ?>
                                        <div class="alert alert-warning alert-sm mb-2">
                                            <strong><?= $k->kode_komponen ?></strong>: <?= $k->nama_komponen ?>
                                            <br><small class="text-muted">Bobot: <?= $k->bobot_minimal ?>% - <?= $k->bobot_maksimal ?>%</small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="bi bi-upload text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">Input Penilaian Otentik</h6>
                            <p class="text-muted small mb-0">Penilaian sikap, keterampilan, dan pengetahuan</p>
                        </div>
                    </div>
                    <a href="<?= site_url('kurikulum-merdeka/input-penilaian') ?>" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg me-1"></i>Mulai Input Penilaian
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="bi bi-folder2-open text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold">Portofolio Digital</h6>
                            <p class="text-muted small mb-0">P5 - Profil Pelajar Pancasila</p>
                        </div>
                    </div>
                    <a href="<?= site_url('kurikulum-merdeka/portofolio-digital') ?>" class="btn btn-success w-100">
                        <i class="bi bi-folder-plus me-1"></i>Kelola Portofolio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Panduan Singkat -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Panduan Singkat</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="fw-bold text-primary"><i class="bi bi-1-circle me-1"></i>Input Penilaian</h6>
                            <ul class="small">
                                <li>Pilih kelas dan mata pelajaran</li>
                                <li>Pilih siswa yang akan dinilai</li>
                                <li>Pilih komponen penilaian (Pengetahuan/Keterampilan/Sikap)</li>
                                <li>Input nilai dan deskripsi capaian</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold text-success"><i class="bi bi-2-circle me-1"></i>Portofolio Digital</h6>
                            <ul class="small">
                                <li>Unggah bukti pembelajaran (foto, video, dokumen)</li>
                                <li>Isi refleksi siswa atas kegiatan</li>
                                <li>Berikan feedback dan penilaian</li>
                                <li>Validasi portofolio</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold text-warning"><i class="bi bi-3-circle me-1"></i>Laporan P5</h6>
                            <ul class="small">
                                <li>Monitor proyek P5 siswa</li>
                                <li>Sync data ke Dapodik dan EMIS</li>
                                <li>Generate laporan kemajuan</li>
                                <li>Download laporan untuk rapor</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
