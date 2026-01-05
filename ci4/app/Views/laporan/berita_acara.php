<?php
echo view('_templates/dashboard/_header', get_defined_vars());
echo view('_templates/dashboard/navbar', get_defined_vars());
echo view('_templates/dashboard/sidebar', get_defined_vars());
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Berita Acara' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('laporan') ?>">Laporan</a></li>
                        <li class="breadcrumb-item active">Berita Acara</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Daftar Berita Acara</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#modalTambah">
                            <i class="fas fa-plus mr-1"></i>Buat Berita Acara
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($berita_acaras ?? [] as $i => $ba): ?>
                                <tr>
                                    <td>
                                        <?= $i + 1 ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($ba->tgl_ujian ?? 'now')) ?>
                                    </td>
                                    <td>
                                        <?= esc($ba->waktu_mulai ?? '-') ?> -
                                        <?= esc($ba->waktu_selesai ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= esc($ba->catatan ?? '-') ?>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('laporan/cetakBeritaAcara/' . $ba->id_berita_acara) ?>"
                                            target="_blank" class="btn btn-info btn-xs"><i class="fas fa-print"></i>
                                            Print</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($berita_acaras)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Buat Berita Acara</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Jadwal Ujian</label>
                                <select name="id_jadwal" class="form-control select2" style="width:100%">
                                    <option value="">-- Pilih Jadwal --</option>
                                    <?php foreach ($jadwals ?? [] as $j): ?>
                                        <option value="<?= $j->id_jadwal ?>">
                                            <?= esc($j->nama_mapel ?? $j->bank_nama ?? '-') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Ujian</label>
                                <input type="date" name="tgl_ujian" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Waktu Mulai</label>
                                <input type="time" name="waktu_mulai" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Waktu Selesai</label>
                                <input type="time" name="waktu_selesai" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="main-footer">
    <strong>&copy;
        <?= date('Y') ?>
        <?= esc($setting?->nama_sekolah ?? 'CBT AKM') ?>.
    </strong>
</footer>

<?php echo view('_templates/dashboard/_footer', get_defined_vars()); ?>

<script>
    $(document).ready(function () {
        $('.select2').select2();
    });

    $('#formTambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('laporan/saveBeritaAcara') ?>', type: 'POST',
            data: $(this).serialize() + '&' + CSRF_NAME + '=' + CSRF_HASH, dataType: 'json',
            success: function (res) {
                res.status ? (showToast('success', res.message), location.reload()) : showToast('error', res.message);
            }
        });
    });
</script>