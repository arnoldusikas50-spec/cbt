<?php
/** @var array $unused */
/** @var int $total_size */
?>

<main class="app-main bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools ms-auto">
                        <a href="<?= base_url('cbtbanksoal') ?>" class="btn btn-sm btn-default">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">


                    <div class="alert alert-warning">
                        <i class="icon bi bi-exclamation-triangle-fill"></i> <b>Perhatian!</b><br>
                        Fitur ini akan menghapus permanen file gambar di folder <b>uploads/bank_soal</b> yang tidak
                        terdeteksi digunakan dalam database (Soal, Jawaban, Opsi).<br>
                        Pastikan Anda sudah membackup folder uploads jika ragu.
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <h5>Total File Unused: <b><?= count($unused) ?></b></h5>
                            <h5>Total Size: <b><?= byte_format($total_size) ?></b></h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if (count($unused) > 0): ?>
                                <?= form_open('cbtbanksoal/cleanup_images', array('id' => 'cleanup-form')) ?>
                                <input type="hidden" name="delete" value="true">
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus semua file ini? Tindakan ini tidak dapat dibatalkan.')">
                                    <i class="bi bi-trash"></i> Hapus Semua File Tak Terpakai
                                </button>
                                <?= form_close() ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-cleanup">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Tgl Modifikasi</th>
                                    <th>Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($unused as $row): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= $row['name'] ?></td>
                                        <td><?= byte_format($row['size']) ?></td>
                                        <td><?= $row['date'] ?></td>
                                        <td class="text-center">
                                            <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $row['name'])): ?>
                                                <a href="<?= base_url('uploads/bank_soal/' . $row['name']) ?>" target="_blank">
                                                    <img src="<?= base_url('uploads/bank_soal/' . $row['name']) ?>"
                                                        style="max-height: 50px; border: 1px solid #ddd; padding: 2px;">
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url('uploads/bank_soal/' . $row['name']) ?>" target="_blank"
                                                    class="btn btn-xs btn-default">
                                                    Download
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
    </section>
    </div>

    <script>
        $(document).ready(function () {
            $('#table-cleanup').DataTable();
        });
    </script>