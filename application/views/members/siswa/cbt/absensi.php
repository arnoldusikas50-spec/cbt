<div class="container-fluid py-4 container-p-x main-content-overlap">
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

                <div class="card-body p-4 position-relative z-1 ">
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
                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-4 me-3">
                                <i class="bi bi-clipboard-data-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><?= $judul ?></h5>
                                <div class="text-muted small"><?= $subjudul ?></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('dashboard') ?>"
                                class="btn btn-primary border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                <i class="bi bi-arrow-left-circle-fill me-2 text-primary"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle custom-table" id="table-absensi">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center py-3 rounded-start-3" style="width: 50px;">No
                                    </th>
                                    <th class="py-3">Tanggal & Waktu</th>
                                    <th class="py-3">Mata Pelajaran</th>
                                    <th class="text-center py-3">Jenis Ujian</th>
                                    <th class="text-center py-3">Status</th>
                                    <th class="text-center py-3 rounded-end-3">Tanda Tangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jadwals)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center text-muted opacity-50">
                                                <i class="bi bi-inbox fs-1 mb-2"></i>
                                                <span>Belum ada data absensi</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1;
                                    foreach ($jadwals as $jadwal): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-secondary"><?= $no++ ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-box-sm bg-light text-secondary rounded-circle me-2 p-1"
                                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-calendar4"></i>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="d-block fw-bold text-dark"><?= buat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai))) ?></span>
                                                        <small
                                                            class="text-muted"><?= date('H:i', strtotime($jadwal->tgl_mulai)) ?>
                                                            WIB</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark mb-1 text-wrap">
                                                    <?= $jadwal->nama_mapel ?>
                                                </div>
                                                <span
                                                    class="badge bg-light text-secondary border"><?= $jadwal->bank_kode ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill"><?= $jadwal->nama_jenis ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($jadwal->hadir): ?>
                                                    <?php if ($jadwal->dikewrjakan): ?>
                                                        <span
                                                            class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                                        </span>
                                                    <?php else: ?>
                                                        <span
                                                            class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                                            <i class="bi bi-hourglass-split me-1"></i> Mengerjakan
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                                        <i class="bi bi-x-circle me-1"></i> Absen
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($jadwal->signature): ?>
                                                    <div class="p-1 border rounded bg-white d-inline-block">
                                                        <img src="<?= $jadwal->signature ?>"
                                                            style="height: 40px; object-fit: contain;" alt="TTD">
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small fst-italic">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Custom Styles specific to this page but adhering to global variables */
    .custom-table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: none;
    }

    .custom-table tbody td {
        border-bottom: 1px solid #f1f5f9;
        padding-top: 1rem;
        padding-bottom: 1rem;
        font-size: 0.95rem;
    }

    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }

    .hover-scale {
        transition: transform 0.2s;
    }

    .hover-scale:hover {
        transform: scale(1.02);
    }

    .text-shadow {
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>