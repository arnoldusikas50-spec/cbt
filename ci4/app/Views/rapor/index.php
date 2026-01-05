<?= $this->extend('dashboard') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-printer"></i>
                    Pilih Kelas
                </h3>
            </div>
            <div class="card-body">
                <div class="callout callout-info">
                    <p>Silakan pilih kelas untuk memproses nilai rapor.</p>
                </div>

                <table id="tableKelas" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="30">No.</th>
                            <th>Nama Kelas</th>
                            <th>Wali Kelas</th>
                            <th>Jumlah Siswa</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kelas)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Data kelas tidak ditemukan</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1;
                            foreach ($kelas as $k): ?>
                                <tr>
                                    <td>
                                        <?= $no++ ?>
                                    </td>
                                    <td>
                                        <?= esc($k->nama_kelas) ?>
                                    </td>
                                    <td>
                                        <?= esc($k->wali ?? '-') ?>
                                    </td>
                                    <td>-</td>
                                    <td class="text-center">
                                        <a href="<?= site_url('rapor/nilai?kelas=' . $k->id_kelas) ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i> Proses
                                        </a>
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

<script>
    $(document).ready(function () {
        $('#tableKelas').DataTable();
    });
</script>
<?= $this->endSection() ?>