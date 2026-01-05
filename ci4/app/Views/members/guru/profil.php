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
                            <?= esc($guru->nama_guru ?? 'Guru') ?>
                        </h3>
                        <p class="text-muted">
                            <?= esc($guru->nip ?? '-') ?>
                        </p>
                        <ul class="list-group list-group-unbordered mb-3 mt-3">
                            <li class="list-group-item d-flex justify-content-between">
                                <b>Email</b> <span><?= esc($guru->email ?? '-') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between border-bottom-0">
                                <b>No HP</b> <span><?= esc($guru->no_hp ?? '-') ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card my-shadow">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-pencil-square me-2"></i>Edit Profil</h3>
                    </div>
                    <form id="formProfil">
                        <input type="hidden" name="id_guru" value="<?= $guru->id_guru ?? '' ?>">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_guru" class="form-control"
                                    value="<?= esc($guru->nama_guru ?? '') ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control"
                                        value="<?= esc($guru->nip ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No HP</label>
                                    <input type="text" name="no_hp" class="form-control"
                                        value="<?= esc($guru->no_hp ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= esc($guru->email ?? '') ?>">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control"
                                    rows="2"><?= esc($guru->alamat ?? '') ?></textarea>
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
            url: '<?= site_url('guru/updateProfil') ?>',
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