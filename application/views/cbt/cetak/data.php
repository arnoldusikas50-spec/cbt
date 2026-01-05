<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h5 class="mb-0 fw-bold text-dark fs-5">Pusat Cetak Dokumen</h5>
                </div>
                <button onclick="window.history.back();" type="button"
                    class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </button>
            </div>

            <?php
            $isAdmin = $is_admin;
            if ($isAdmin || (isset($guru) && isset($ids_pengawas) && in_array($guru->id_guru, $ids_pengawas))):
                ?>
                <!-- Grid Menu Minimalist -->
                <div class="row g-3 pb-4">
                    <?php
                    $is_pengawas = isset($panitia) && isset($panitia->jabatan) && $panitia->jabatan === 'Pengawas';
                    ?>

                    <?php if (!$is_pengawas): ?>
                        <!-- Item: Kartu Peserta -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a href="<?= base_url('cbtcetak/kartupeserta') ?>" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-scale rounded-3">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="icon-box bg-light-primary text-primary rounded-3 me-3">
                                            <i class="bi bi-person-vcard fs-4"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold mb-1 fs-6">Kartu Peserta</h6>
                                            <p class="text-muted mb-0 text-xs text-truncate">Login & QR Code</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Kartu Meja -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a href="<?= base_url('cbtcetak/kartumeja') ?>" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-scale rounded-3">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="icon-box bg-light-purple text-purple rounded-3 me-3">
                                            <i class="bi bi-person-workspace fs-4"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold mb-1 fs-6">Kartu Meja</h6>
                                            <p class="text-muted mb-0 text-xs text-truncate">Label Meja Peserta</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Daftar Hadir -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a href="<?= base_url('cbtcetak/absenpeserta') ?>" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-scale rounded-3">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="icon-box bg-light-success text-success rounded-3 me-3">
                                            <i class="bi bi-clipboard-check fs-4"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold mb-1 fs-6">Daftar Hadir</h6>
                                            <p class="text-muted mb-0 text-xs text-truncate">Presensi Harian</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Jadwal Pengawas -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a href="<?= base_url('cbtcetak/pengawas') ?>" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-scale rounded-3">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="icon-box bg-light-warning text-warning rounded-3 me-3">
                                            <i class="bi bi-calendar-check fs-4"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold mb-1 fs-6">Jadwal Pengawas</h6>
                                            <p class="text-muted mb-0 text-xs text-truncate">Tugas Pengawas</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Peserta Ujian -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <a href="<?= base_url('cbtcetak/pesertaujian') ?>" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 shadow-sm hover-scale rounded-3">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="icon-box bg-light-info text-info rounded-3 me-3">
                                            <i class="bi bi-people-fill fs-4"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bold mb-1 fs-6">Data Peserta</h6>
                                            <p class="text-muted mb-0 text-xs text-truncate">Daftar Siswa Ujian</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- End Not Pengawas -->
                    <?php endif; ?>

                    <!-- All Can See: Berita Acara -->
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                        <a href="<?= base_url('cbtcetak/beritaacara') ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 border-0 shadow-sm hover-scale rounded-3">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="icon-box bg-light-danger text-danger rounded-3 me-3">
                                        <i class="bi bi-file-earmark-check-fill fs-4"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold mb-1 fs-6">Berita Acara</h6>
                                        <p class="text-muted mb-0 text-xs text-truncate">Dokumen Pelaksanaan</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Pakta Integritas -->
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                        <a href="<?= base_url('cbtcetak/paktaintegritas') ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 border-0 shadow-sm hover-scale rounded-3">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="icon-box bg-light-indigo text-indigo rounded-3 me-3">
                                        <i class="bi bi-shield-check fs-4"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold mb-1 fs-6">Pakta Integritas</h6>
                                        <p class="text-muted mb-0 text-xs text-truncate">Komitmen Pengawas</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Access Denied -->
                <div class="alert alert-light border-danger shadow-sm d-flex align-items-center p-3 rounded-3" role="alert">
                    <div class="bg-danger p-2 rounded-circle me-3 text-white d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-danger mb-0">Akses Dibatasi</h6>
                        <small class="text-secondary">Hanya Administrator dan Pengawas Ujian.</small>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>
<style>
    .hover-scale {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .hover-scale:hover {
        transform: scale(1.02);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05) !important;
        cursor: pointer;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        min-width: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-light-primary {
        background-color: rgba(0, 123, 255, 0.1);
    }

    .bg-light-success {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .bg-light-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .bg-light-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-light-info {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .bg-light-purple {
        background-color: rgba(111, 66, 193, 0.1);
    }

    .bg-light-indigo {
        background-color: rgba(102, 16, 242, 0.1);
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    .text-indigo {
        color: #6610f2 !important;
    }

    .text-xs {
        font-size: 0.75rem;
    }

    .fs-6 {
        font-size: 1rem;
    }
</style>