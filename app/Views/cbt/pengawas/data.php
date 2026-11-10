<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    <?= esc($judul ?? 'Atur Pengawas') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">CBT</li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Atur Pengawas') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary">
                    <i class="bi bi-person-badge me-2"></i><?= esc($subjudul ?? 'Atur Pengawas Ujian') ?>
                </h3>
                <div class="card-tools ms-auto">
                    <button type="button" id="btn-reload" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" id="btn-auto" class="btn btn-sm btn-outline-primary ms-1" disabled>
                        <i class="bi bi-magic me-1"></i> Generate Otomatis
                    </button>
                    <button type="button" id="btn-reset-pengawas" class="btn btn-sm btn-outline-danger ms-1" disabled
                        title="Kosongkan pengawas yang terpilih">
                        <i class="bi bi-eraser me-1"></i> Kosongkan
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex">
                        <i class="bi bi-info-circle me-3 mt-1 fs-5"></i>
                        <div>
                            <strong>Panduan Pengaturan:</strong>
                            <ol class="mb-0 mt-1">
                                <li>Pilih <b>Jenis Penilaian</b> dan <b>Tanggal Ujian</b> terlebih dahulu.</li>
                                <li>Alokasi pengawas dihitung per <b>ruang</b>, <b>sesi</b>, dan <b>jam ke</b>.</li>
                                <li><b>Generate Otomatis</b> mengisi pengawas dari Guru Pengampu Mapel dan Panitia Ruang.</li>
                                <li>Slot yang dikosongkan akan <b>dihapus</b> dari daftar penugasan ketika disimpan.</li>
                                <li>Perubahan baru tersimpan setelah menekan tombol <b>Simpan</b>.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="row g-3 align-items-end mb-4">
                    <div class="col-12 col-md-3">
                        <label for="jenis" class="form-label fw-bold">Jenis Penilaian</label>
                        <?= form_dropdown('jenis', $jenis, null, 'id="jenis" class="form-select form-select-sm"'); ?>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="tanggal" class="form-label fw-bold">Tanggal Ujian</label>
                        <select name="tanggal" id="tanggal" class="form-select form-select-sm">
                            <option value="">-- Pilih Tanggal Ujian --</option>
                        </select>
                        <div id="tanggal-spinner" class="spinner-border spinner-border-sm text-primary mt-2 d-none" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="search-pengawas" class="form-label fw-bold">Cari Ruang / Mapel</label>
                        <input type="text" id="search-pengawas" class="form-control form-control-sm"
                            placeholder="Ketik nama ruang/mapel..." disabled>
                    </div>
                    <div class="col-12 col-md-2">
                        <span class="badge bg-secondary" id="table-row-count">0 Alokasi</span>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="alert alert-secondary border-0 shadow-sm text-center py-5" role="alert">
                    <i class="bi bi-calendar2-range fs-1 d-block mb-2 text-secondary"></i>
                    <h5 class="fw-bold mb-1">Pilih Jenis Penilaian &amp; Tanggal Ujian</h5>
                    <p class="text-muted small mb-0">
                        Silakan tentukan Jenis Penilaian dan Tanggal Ujian pada panel filter di atas untuk mulai
                        mengonfigurasi pengawas ujian.
                    </p>
                </div>

                <div class="table-responsive" id="table-container"></div>
            </div>

            <div class="card-footer bg-light">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="text-muted small" id="status-pengawas">
                        <i class="bi bi-shield-check text-success me-1"></i>Belum ada alokasi pengawas
                    </div>
                    <?= form_open('', array('id' => 'savepengawas', 'class' => 'm-0')) ?>
                    <button type="submit" id="btn-submit-save" class="btn btn-primary shadow-sm px-4" disabled>
                        <i class="bi bi-save me-2"></i> Simpan
                    </button>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/cbtpengawas.js?v=<?= time() ?>"></script>
<?= $this->endSection() ?>
