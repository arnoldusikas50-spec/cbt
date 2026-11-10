<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/jquery-datetimepicker/jquery.datetimepicker.min.css') ?>">
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <?= $judul ?? 'Nomor Peserta' ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Nomor Peserta</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- Main content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-card-heading me-2"></i>
                    <?= $subjudul ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label fw-bold">Pilih Kelas Penilaian</label>
                        <select id="kelas" name="kelas[]" class="form-select" multiple="multiple"
                            data-placeholder="Pilih kelas penilaian">
                            <?php foreach ($kelas as $id => $nama): ?>
                                <option value="<?= $id ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-2 text-info small">
                            <i class="bi bi-info-circle me-1"></i> Pilih semua kelas yang akan melaksanakan penilaian
                        </div>
                    </div>
                    <div class="col-md-6 d-none" id="generate">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#nomorModal">
                                <i class="bi bi-gear me-1"></i> Generate Nomor
                            </button>
                            <button type="button" class="btn btn-danger" onclick="resetNumber()">
                                <i class="bi bi-trash me-1"></i> Hapus Nomor
                            </button>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-12 d-none" id="for-save">
                        <div class="text-end mb-3">
                            <?= form_open('setNomor', array('id' => 'setNomor')); ?>
                            <button type="button" class="btn btn-sm btn-warning" onclick="undoNumber()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Batalkan perubahan
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary ms-2">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <?= form_close() ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="table-nomor">
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div class="overlay d-none" id="loading">
                <button class="btn btn-primary" type="button" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading...
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Generate Nomor -->
<div class="modal fade" id="nomorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Nomor Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <!-- Separator -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <input id="check1" class="form-check-input" type="checkbox" checked />
                        </div>
                        <div class="input-group input-group-sm flex-grow-1">
                            <span class="input-group-text" style="width: 180px">Separator</span>
                            <input id="input1" class="form-control fw-bold" value="-" type="text" />
                        </div>
                    </div>
                    <div class="mb-3 ms-5">
                        <small class="text-muted">Pemisah antara tiap kode, seperti: <b>- _ /</b> dan lainnya</small>
                    </div>

                    <?php for ($i = 2; $i <= 11; $i++): ?>
                        <!-- Option <?= $i ?> -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <input id="check<?= $i ?>" class="form-check-input" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm flex-grow-1">
                                <select id="opt<?= $i ?>" class="form-select for-option" style="width: 180px">
                                </select>
                                <input id="input<?= $i ?>" class="form-control fw-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-3 ms-5">
                            <small class="text-muted" id="info<?= $i ?>"></small>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="alert alert-info">
                    <strong>Catatan:</strong>
                    <ul class="mb-0">
                        <li>Silahkan checklist kode yang akan digunakan, lalu pilih opsi KODE.</li>
                        <li>Jika ingin menambahkan kode khusus seperti <b>ASAT-</b>XX-XX, bisa memilih opsi apa saja
                            <b>selain</b> Kode Tahun, Nomor Urut dan Nomor Unik.
                        </li>
                        <li>Pilihan Kode yang <b>tidak di-checklist</b> maka tidak akan digenerate.</li>
                        <li>Input Kode kosong tidak akan digenerate.</li>
                        <li>Nomor peserta ujian akan dibuat sesuai urutan siswa pada kelas yang dipilih.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary" onclick="generateNomorUjian()">
                    <i class="bi bi-gear me-1"></i> Generate Nomor
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/cbtnomorpeserta.js') ?>"></script>
<script>
    var arrKelas = <?= json_encode($kelas) ?>;
    var tahun = '<?= $tp_active->tahun ?>';
    var smt = '<?= $smt_active->id_smt ?>';
</script>
<?= $this->endSection() ?>