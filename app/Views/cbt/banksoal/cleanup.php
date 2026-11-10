<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php helper('number'); ?>

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark"><?= esc($subjudul ?? 'Cleanup File') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('cbtbanksoal') ?>" class="text-decoration-none">Bank Soal</a></li>
                    <li class="breadcrumb-item active">Cleanup</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-default my-shadow mb-4">
            <div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-trash text-primary me-2"></i><?= esc($subjudul ?? 'Cleanup Unused Files') ?>
                </h6>
                <div class="card-tools ms-auto">
                    <a href="<?= base_url('cbtbanksoal') ?>" class="btn btn-sm btn-outline-secondary fw-bold shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">Perhatian!</h5>
                        <p class="mb-0">
                            Fitur ini akan menghapus permanen file gambar di folder <b>uploads/bank_soal</b> yang tidak terdeteksi digunakan dalam database (Soal, Jawaban, Opsi).<br>
                            <span class="fw-bold">Pastikan Anda sudah membackup folder uploads jika ragu.</span>
                        </p>
                    </div>
                </div>

                <div class="row mb-4 align-items-center g-3">
                    <div class="col-md-6">
                        <div class="d-flex gap-4">
                            <div class="border-start border-4 border-warning ps-3">
                                <small class="text-muted text-uppercase fw-bold">Total File Unused</small>
                                <h4 class="mb-0 fw-bold"><?= count($unused) ?></h4>
                            </div>
                            <div class="border-start border-4 border-info ps-3">
                                <small class="text-muted text-uppercase fw-bold">Total Size</small>
                                <h4 class="mb-0 fw-bold"><?= number_to_size($total_size) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <?php if (count($unused) > 0): ?>
                            <?= form_open('cbtbanksoal/cleanup_images', array('id' => 'cleanup-form')) ?>
                            <input type="hidden" name="delete" value="true">
                            <button type="submit" class="btn btn-danger fw-bold shadow-sm">
                                <i class="bi bi-trash me-1"></i> Hapus Semua File Tak Terpakai
                            </button>
                            <?= form_close() ?>
                        <?php else: ?>
                            <button class="btn btn-secondary fw-bold shadow-sm" disabled>
                                <i class="bi bi-check-circle me-1"></i> Tidak ada file sampah
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle w-100 mb-0" id="table-cleanup">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Nama File</th>
                                <th>Ukuran</th>
                                <th>Tgl Modifikasi</th>
                                <th class="text-center">Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($unused as $row): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="font-monospace text-wrap"><?= $row['name'] ?></td>
                                    <td><?= number_to_size($row['size']) ?></td>
                                    <td><?= $row['date'] ?></td>
                                    <td class="text-center">
                                        <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $row['name'])): ?>
                                            <a href="<?= base_url('uploads/bank_soal/' . $row['name']) ?>" target="_blank" class="d-inline-block p-1 border rounded bg-white">
                                                <img src="<?= base_url('uploads/bank_soal/' . $row['name']) ?>"
                                                    style="max-height: 40px;" alt="Preview">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('uploads/bank_soal/' . $row['name']) ?>" target="_blank"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download me-1"></i> Download
                                            </a>
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
</div>

<script>
    $(document).ready(function () {
        $('#table-cleanup').DataTable({
            responsive: true,
            language: dt_lang_id // Gunakan language global dari layout main
        });

        $('#cleanup-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Semua File?',
                    text: 'Tindakan ini akan menghapus semua file sampah secara permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Ya, Hapus Semua',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus semua file ini? Tindakan ini tidak dapat dibatalkan.')) {
                    form.submit();
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>