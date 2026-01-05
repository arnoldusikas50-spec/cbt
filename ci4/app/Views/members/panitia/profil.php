<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline my-shadow">
                    <div class="card-body box-profile text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="<?= base_url('assets/adminlte/dist/img/user2-160x160.jpg') ?>" alt="User profile">
                        <h3 class="profile-username mt-3">
                            <?= esc($panitia->nama_panitia ?? 'Panitia') ?>
                        </h3>
                        <p class="text-muted">
                            <?= esc($panitia->instansi ?? '-') ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card my-shadow">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-pencil-square me-2"></i>Edit Profil</h3>
                    </div>
                    <form id="formProfil">
                        <input type="hidden" name="id_panitia" value="<?= $panitia->id_panitia ?? '' ?>">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_panitia" class="form-control"
                                    value="<?= esc($panitia->nama_panitia ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instansi/Sekolah</label>
                                <input type="text" name="instansi" class="form-control"
                                    value="<?= esc($panitia->instansi ?? '') ?>">
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $('#formProfil').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('panitia/updateProfil') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message
                    });
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>