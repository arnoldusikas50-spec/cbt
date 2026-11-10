<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 container-p-x main-content-overlap pb-5 mb-5 pb-lg-4 mb-lg-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Main Card -->
            <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Header Decoration -->
                <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 0;">
                    <div style="
                                    position: absolute;
                                    top: -10%;
                                    right: -5%;
                                    width: 400px;
                                    height: 400px;
                                    background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, rgba(255,255,255,0) 70%);
                                    border-radius: 50%;
                                "></div>
                </div>

                <div class="card-body p-3 p-md-4 position-relative z-1 ">
                    <!-- Watermark -->
                    <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background-color: #e0e7ff;
                                    -webkit-mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    -webkit-mask-size: 50%;
                                    mask-size: 50%;
                                    -webkit-mask-repeat: no-repeat;
                                    mask-repeat: no-repeat;
                                    -webkit-mask-position: center;
                                    mask-position: center;
                                    opacity: 0.05;
                                    z-index: -1;
                                    pointer-events: none;
                                "></div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-4 me-3 d-flex align-items-center justify-content-center border border-primary border-opacity-10" style="width: 48px; height: 48px;">
                                <i class="bi bi-clipboard-data-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0 fs-5 fs-md-4"><?= esc($judul ?? 'Absensi Ujian') ?></h5>
                                <div class="text-muted small d-none d-sm-block mt-1"><?= esc($subjudul ?? 'Rekap kehadiran ujian') ?></div>
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

                    <?php if (empty($jadwals)): ?>
                        <div class="text-center py-5">
                            <div class="d-inline-flex flex-column align-items-center text-muted opacity-50">
                                <i class="bi bi-inbox fs-1 mb-2"></i>
                                <span>Belum ada data absensi</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-lg-block">
                            <table class="table table-hover align-middle custom-table mb-0" id="table-absensi">
                                <thead class="bg-light bg-opacity-50">
                                    <tr>
                                        <th class="text-center py-3 rounded-start-3" style="width: 60px;">No</th>
                                        <th class="py-3">Tanggal & Waktu</th>
                                        <th class="py-3">Mata Pelajaran</th>
                                        <th class="text-center py-3">Jenis Ujian</th>
                                        <th class="text-center py-3">Status</th>
                                        <th class="text-center py-3 rounded-end-3">Tanda Tangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($jadwals as $jadwal): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-secondary"><?= $no++ ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-box-sm bg-light text-secondary rounded-circle me-3"
                                                        style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-calendar4"></i>
                                                    </div>
                                                    <div>
                                                        <span class="d-block fw-bold text-dark"><?= buat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai))) ?></span>
                                                        <small class="text-muted d-flex align-items-center mt-1">
                                                            <i class="bi bi-clock me-1"></i> <?= date('H:i', strtotime($jadwal->tgl_mulai)) ?> WIB
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark mb-1 text-wrap line-clamp-2" style="max-width: 250px;">
                                                    <?= esc($jadwal->nama_mapel ?? '') ?>
                                                </div>
                                                <span class="badge bg-light text-secondary border px-2 py-1">
                                                    <i class="bi bi-bookmark-fill text-muted me-1"></i> <?= esc($jadwal->bank_kode ?? '') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-medium">
                                                    <?= esc($jadwal->nama_jenis ?? '') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($jadwal->hadir ?? false): ?>
                                                    <?php if ($jadwal->dikerjakan ?? false): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-medium border border-success border-opacity-25">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-medium border border-warning border-opacity-25">
                                                            <i class="bi bi-hourglass-split me-1"></i> Mengerjakan
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-medium border border-danger border-opacity-25">
                                                        <i class="bi bi-x-circle-fill me-1"></i> Absen
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($jadwal->signature)): ?>
                                                    <div class="p-1 border shadow-sm rounded-3 bg-white d-inline-block hover-scale">
                                                        <img src="<?= $jadwal->signature ?>" style="height: 40px; width: 80px; object-fit: contain;" alt="TTD">
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border px-3 py-2">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile/Tablet Card View -->
                        <div class="d-block d-lg-none">
                            <div class="row g-3">
                                <?php foreach ($jadwals as $jadwal): ?>
                                    <div class="col-12 col-md-6">
                                        <div class="card bg-white border rounded-4 shadow-sm h-100 hover-scale-sm transition-all">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex flex-column gap-1">
                                                        <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1 align-self-start fw-medium" style="font-size: 0.75rem;">
                                                            <?= esc($jadwal->nama_jenis ?? '') ?>
                                                        </span>
                                                        <span class="badge bg-light text-secondary border px-2 py-1 align-self-start mt-1" style="font-size: 0.7rem;">
                                                            <i class="bi bi-bookmark-fill text-muted me-1"></i> <?= esc($jadwal->bank_kode ?? '') ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <?php if ($jadwal->hadir ?? false): ?>
                                                            <?php if ($jadwal->dikerjakan ?? false): ?>
                                                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill fw-medium border border-success border-opacity-25" style="font-size: 0.75rem;">
                                                                    <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill fw-medium border border-warning border-opacity-25" style="font-size: 0.75rem;">
                                                                    <i class="bi bi-hourglass-split me-1"></i> Mengerjakan
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill fw-medium border border-danger border-opacity-25" style="font-size: 0.75rem;">
                                                                <i class="bi bi-x-circle-fill me-1"></i> Absen
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <h6 class="fw-bold text-dark mb-2 line-clamp-2"><?= esc($jadwal->nama_mapel ?? '') ?></h6>
                                                
                                                <div class="d-flex align-items-center text-muted small bg-light rounded-3 p-2 mb-3">
                                                    <div class="bg-white rounded-circle d-flex justify-content-center align-items-center shadow-sm me-2" style="width: 28px; height: 28px;">
                                                        <i class="bi bi-calendar4 text-primary" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-medium text-dark" style="font-size: 0.8rem;"><?= buat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai))) ?></span>
                                                        <span style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($jadwal->tgl_mulai)) ?> WIB</span>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-auto">
                                                    <span class="small text-muted fw-medium">Tanda Tangan:</span>
                                                    <?php if (!empty($jadwal->signature)): ?>
                                                        <div class="p-1 border shadow-sm rounded-2 bg-white d-inline-block">
                                                            <img src="<?= $jadwal->signature ?>" style="height: 35px; width: 70px; object-fit: contain;" alt="TTD">
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border px-3 py-1">-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- ══ MOBILE BOTTOM NAV ══ -->
<nav class="position-fixed bottom-0 start-0 w-100 d-flex d-lg-none bg-white border-top shadow-lg" style="padding-bottom: env(safe-area-inset-bottom); z-index: 1040;">
    <div class="d-flex justify-content-around align-items-center w-100 py-2 px-1">
        <a href="<?= base_url('dashboard') ?>" id="bn-home" class="text-decoration-none d-flex flex-column align-items-center text-primary bg-primary bg-opacity-10 py-1 px-3 rounded-pill transition-all" style="min-width: 60px;">
            <i class="bi bi-house-door-fill fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Home</small>
        </a>

        <button onclick="logout()" class="btn btn-link text-decoration-none d-flex flex-column align-items-center text-secondary py-1 px-3 rounded-pill transition-all" style="min-width: 60px; box-shadow: none;">
            <i class="bi bi-box-arrow-right fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Keluar</small>
        </button>
    </div>
</nav>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Custom Styles specific to this page but adhering to global variables */
    .custom-table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }

    .custom-table tbody td {
        border-bottom: 1px dashed #e2e8f0;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
        font-size: 0.95rem;
        background-color: transparent;
        transition: background-color 0.2s ease;
    }

    .custom-table tbody tr:hover td {
        background-color: rgba(248, 250, 252, 0.8);
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    .hover-scale {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-scale:hover {
        transform: translateY(-2px);
    }
    
    .hover-scale-sm {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease;
    }

    .hover-scale-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
    }

    .text-shadow {
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    
    .transition-all {
        transition: all 0.3s ease;
    }
</style>
<?= $this->endSection() ?>