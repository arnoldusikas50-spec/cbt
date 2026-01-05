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
                            <?= esc($siswa->nama ?? 'Siswa') ?>
                        </h3>
                        <p class="text-muted">
                            <?= esc($siswa->nis ?? '-') ?>
                        </p>
                        <ul class="list-group list-group-unbordered mb-3 mt-3">
                            <li class="list-group-item d-flex justify-content-between">
                                <b>NISN</b> <span><?= esc($siswa->nisn ?? '-') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <b>Kelas</b> <span><?= esc($siswa->nama_kelas ?? '-') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between border-bottom-0">
                                <b>Jenis Kelamin</b>
                                <span><?= ($siswa->jenis_kelamin ?? 'L') == 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card my-shadow">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-key me-2"></i>Ganti Password</h3>
                    </div>
                    <form id="formPassword">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Ganti Password
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
    $('#formPassword').on('submit', function (e) {
        e.preventDefault();
        var newPass = $('[name="new_password"]').val();
        var confirmPass = $('[name="confirm_password"]').val();
        if (newPass !== confirmPass) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Konfirmasi password tidak sesuai'
            });
            return;
        }
        $.ajax({
            url: '<?= site_url('siswa/gantiPassword') ?>',
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
                    $('#formPassword')[0].reset();
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