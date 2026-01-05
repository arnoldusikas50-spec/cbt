<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-pencil-square"></i>
                    Input Nilai Rapor - Kelas
                    <?= esc($kelas->nama_kelas) ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('rapor') ?>" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= site_url('rapor/save_nilai') ?>" method="post" id="formNilai">
                    <input type="hidden" name="id_kelas" value="<?= $kelas->id_kelas ?>">

                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="50">No.</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th width="150">Nilai Akhir</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($siswas)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada siswa</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1;
                                foreach ($siswas as $s): ?>
                                    <tr>
                                        <td>
                                            <?= $no++ ?>
                                        </td>
                                        <td>
                                            <?= esc($s->nis) ?>
                                        </td>
                                        <td>
                                            <?= esc($s->nama) ?>
                                        </td>
                                        <td>
                                            <input type="number" name="nilai[<?= $s->id_siswa ?>]"
                                                class="form-control form-control-sm" value="0">
                                        </td>
                                        <td>
                                            <input type="text" name="ket[<?= $s->id_siswa ?>]"
                                                class="form-control form-control-sm" placeholder="-">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>