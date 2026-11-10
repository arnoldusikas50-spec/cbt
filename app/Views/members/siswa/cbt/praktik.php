<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 container-p-x main-content-overlap fade-in-up pb-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 mt-4">

            <!-- Header Section -->
            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-info bg-opacity-10 text-info rounded-4 d-flex align-items-center justify-content-center border border-info border-opacity-25 shadow-sm" style="width: 64px; height: 64px;">
                                <i class="bi bi-laptop fs-2"></i>
                            </div>
                            <div>
                                <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;"><?= esc($judul) ?></h3>
                                <p class="mb-0 text-muted small text-uppercase letter-spacing-1 fw-bold"><?= esc($subjudul) ?></p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('dashboard') ?>"
                                class="btn btn-primary rounded-pill px-3 px-md-4 py-2 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                <i class="bi bi-arrow-left me-0 me-md-2"></i> 
                                <span class="d-none d-md-inline">Kembali</span>
                            </a>
                    </div>
                </div>
            </div>

            <?php if(empty($praktik)): ?>
                <!-- Empty State -->
                <div class="card bg-white border-0 shadow-sm rounded-4 p-5 text-center">
                    <div class="py-5">
                        <div class="icon-box bg-light bg-opacity-75 text-muted rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-inbox fs-1"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Tidak Ada Ujian Praktik</h4>
                        <p class="text-muted mb-0">Belum ada ujian praktik yang aktif untuk kelas Anda saat ini.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                <?php foreach($praktik as $index => $p): ?>
                    <?php
                        $statusConfig = match(true) {
                            $p->status_pengerjaan === 'selesai' => [
                                'class' => 'success',
                                'bg' => 'bg-success',
                                'text' => 'text-success',
                                'badge' => 'Selesai',
                                'btnClass' => 'btn-success',
                                'btnText' => 'Lihat Hasil',
                                'icon' => 'bi-check-circle'
                            ],
                            $p->status_pengerjaan === 'sedang_mengerjakan' => [
                                'class' => 'warning',
                                'bg' => 'bg-warning',
                                'text' => 'text-warning',
                                'badge' => 'Sedang Mengerjakan',
                                'btnClass' => 'btn-warning',
                                'btnText' => 'Lanjutkan',
                                'icon' => 'bi-pause-circle'
                            ],
                            $p->nilai_akhir !== null => [
                                'class' => 'info',
                                'bg' => 'bg-info',
                                'text' => 'text-info',
                                'badge' => 'Sudah Dinilai',
                                'btnClass' => 'btn-info',
                                'btnText' => 'Lihat Nilai',
                                'icon' => 'bi-award'
                            ],
                            default => [
                                'class' => 'primary',
                                'bg' => 'bg-primary',
                                'text' => 'text-primary',
                                'badge' => 'Belum Mulai',
                                'btnClass' => 'btn-primary',
                                'btnText' => 'Mulai Ujian',
                                'icon' => 'bi-play-circle'
                            ]
                        };

                        $now = time();
                        $mulai = strtotime($p->tanggal_mulai);
                        $selesai = strtotime($p->tanggal_selesai);
                        $btnDisabled = '';

                        if ($now < $mulai) {
                            $btnDisabled = 'disabled';
                            $statusConfig['badge'] = 'Belum Dimulai';
                            $statusConfig['class'] = 'secondary';
                            $statusConfig['bg'] = 'bg-secondary';
                            $statusConfig['text'] = 'text-secondary';
                        } elseif ($now > $selesai && $p->status_pengerjaan !== 'selesai') {
                            $btnDisabled = 'disabled';
                            $statusConfig['badge'] = 'Sudah Berakhir';
                            $statusConfig['class'] = 'danger';
                            $statusConfig['bg'] = 'bg-danger';
                            $statusConfig['text'] = 'text-danger';
                        }

                        $jenisIcon = match($p->jenis_praktik) {
                            'coding' => 'bi-code-slash',
                            'simulasi' => 'bi-display',
                            'hands_on' => 'bi-tools',
                            'upload_file' => 'bi-upload',
                            default => 'bi-laptop'
                        };

                        $jenisLabel = match($p->jenis_praktik) {
                            'coding' => 'Coding',
                            'simulasi' => 'Simulasi',
                            'hands_on' => 'Hands On',
                            'upload_file' => 'Upload File',
                            default => 'Praktik'
                        };

                        $delay = ($index + 1) * 0.1;
                    ?>
                    <div class="col-md-6 fade-in-up" style="animation-delay: <?= $delay ?>s">
                        <div class="card bg-white border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-scale-sm transition-all position-relative">
                            <!-- Status Header -->
                            <div class="card-header border-0 py-3 px-4 <?= $statusConfig['bg'] ?> bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-journal-text <?= $statusConfig['text'] ?>"></i>
                                        <span class="fw-bold small <?= $statusConfig['text'] ?> text-uppercase letter-spacing-1"><?= esc($p->nama_mapel) ?></span>
                                    </div>
                                    <span class="badge bg-white <?= $statusConfig['text'] ?> border border-opacity-25 rounded-pill px-3 py-2 small fw-bold shadow-sm">
                                        <i class="bi <?= $jenisIcon ?> me-1"></i> <?= $jenisLabel ?>
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <!-- Title -->
                                <h4 class="fw-bold text-dark mb-3" style="letter-spacing: -0.5px;"><?= esc($p->judul) ?></h4>

                                <!-- Status Badge -->
                                <div class="mb-4">
                                    <span class="badge <?= $statusConfig['bg'] ?> bg-opacity-25 <?= $statusConfig['text'] ?> rounded-pill px-3 py-2 small fw-bold">
                                        <i class="bi <?= $statusConfig['icon'] ?> me-1"></i> <?= $statusConfig['badge'] ?>
                                    </span>
                                    <?php if ($p->nilai_akhir !== null): 
                                        $lulus = $p->nilai_akhir >= 66;
                                        $predikat = $p->nilai_akhir >= 86 ? 'M' : ($p->nilai_akhir >= 66 ? 'C' : ($p->nilai_akhir >= 41 ? 'L' : 'BB'));
                                    ?>
                                        <span class="badge bg-white text-<?= $lulus ? 'success' : 'danger' ?> border border-opacity-25 rounded-pill px-3 py-2 small fw-bold ms-2 shadow-sm">
                                            <i class="bi bi-award me-1"></i> Nilai: <?= number_format($p->nilai_akhir, 1) ?>
                                        </span>
                                        <span class="badge bg-<?= $lulus ? 'success' : 'danger' ?> text-white rounded-pill px-2 py-1 small fw-bold ms-1"><?= $predikat ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Info Grid -->
                                <div class="bg-light bg-opacity-50 rounded-3 p-3 mb-4">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="icon-box bg-white text-muted rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-calendar-event small"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Mulai</small>
                                                    <div class="fw-semibold text-dark small"><?= date('d M Y', strtotime($p->tanggal_mulai)) ?></div>
                                                    <div class="text-muted" style="font-size: 11px;"><?= date('H:i', strtotime($p->tanggal_mulai)) ?> WIB</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="icon-box bg-white text-muted rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-calendar-check small"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Selesai</small>
                                                    <div class="fw-semibold text-dark small"><?= date('d M Y', strtotime($p->tanggal_selesai)) ?></div>
                                                    <div class="text-muted" style="font-size: 11px;"><?= date('H:i', strtotime($p->tanggal_selesai)) ?> WIB</div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($p->durasi_menit > 0): ?>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="icon-box bg-white text-muted rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-clock small"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Durasi</small>
                                                    <div class="fw-semibold text-dark small"><?= $p->durasi_menit ?> Menit</div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($p->wajib_fullscreen): ?>
                                        <div class="col-6">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="icon-box bg-white text-muted rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-fullscreen small"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Mode</small>
                                                    <div class="fw-semibold text-dark small">Fullscreen Wajib</div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <?php
                                $btnUrl = base_url('cbtsiswa/praktik/konfirmasi/' . $p->id);
                                if ($p->status_pengerjaan === 'selesai' || $p->nilai_akhir !== null) {
                                    $btnUrl = base_url('cbtsiswa/praktik/hasil/' . $p->id);
                                }
                                ?>
                                <a href="<?= $btnUrl ?>"
                                   class="btn <?= $statusConfig['btnClass'] ?> rounded-pill px-4 fw-bold w-100 py-3 shadow-sm hover-scale-sm transition-all <?= $btnDisabled ?>">
                                    <i class="bi <?= $statusConfig['icon'] ?> me-2"></i> <?= $statusConfig['btnText'] ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .fade-in-up {
        animation: fadeInUp 0.5s ease-out both;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hover-scale-sm {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }
    .hover-scale-sm:hover {
        transform: translateY(-4px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.12)!important;
    }

    .letter-spacing-1 {
        letter-spacing: 1px;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bg-success.bg-opacity-10 { background: rgba(16, 185, 129, 0.1) !important; }
    .bg-warning.bg-opacity-10 { background: rgba(245, 158, 11, 0.1) !important; }
    .bg-info.bg-opacity-10 { background: rgba(14, 165, 233, 0.1) !important; }
    .bg-primary.bg-opacity-10 { background: rgba(13, 110, 253, 0.1) !important; }
    .bg-danger.bg-opacity-10 { background: rgba(239, 68, 68, 0.1) !important; }
    .bg-secondary.bg-opacity-10 { background: rgba(100, 116, 139, 0.1) !important; }

    .transition-all {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
<?= $this->endSection() ?>
