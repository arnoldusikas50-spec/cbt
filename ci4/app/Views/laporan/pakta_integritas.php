<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-main">
    <div class="container-fluid">
        <div class="card card-default my-shadow">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-file-earmark-text me-2"></i>Daftar Pakta Integritas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus me-1"></i>Buat Pakta
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Instansi</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paktas ?? [] as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= esc($p->nama ?? '-') ?></td>
                                <td><?= esc($p->jabatan ?? '-') ?></td>
                                <td><?= esc($p->instansi ?? '-') ?></td>
                                <td><?= date('d/m/Y', strtotime($p->created_at ?? 'now')) ?></td>
                                <td>
                                    <a href="<?= site_url('laporan/cetakPaktaIntegritas/' . $p->id_pakta) ?>" target="_blank" class="btn btn-info btn-sm">
                                        <i class="bi bi-printer"></i> Print
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($paktas)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Buat Pakta Integritas</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instansi/Sekolah</label>
                        <input type="text" name="instansi" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('#formTambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('laporan/savePaktaIntegritas') ?>', type: 'POST',
            data: $(this).serialize() + '&' + CSRF_NAME + '=' + CSRF_HASH, dataType: 'json',
            success: function (res) {
                res.status ? (showToast('success', res.message), location.reload()) : showToast('error', res.message);
            }
        });
    });
</script>
<?= $this->endSection() ?>