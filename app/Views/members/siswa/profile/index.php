<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 container-p-x main-content-overlap">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Header -->
            <div class="card card-modern border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1">👤 Profil Siswa</h4>
                            <p class="text-muted mb-0">Kelola informasi profil kamu</p>
                        </div>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-light border rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Profile Card -->
                <div class="col-md-4">
                    <div class="card card-modern border-0">
                        <div class="card-body p-4 text-center">
                            <?php
                            $foto = $siswa->foto ?? null;
                            $foto_path = $foto ? base_url($foto) : base_url('assets/img/siswa.png');
                            ?>
                            <div class="position-relative d-inline-block mb-3">
                                <img src="<?= $foto_path ?>" class="rounded-circle border border-3 border-primary"
                                    width="150" height="150" alt="Foto Profil"
                                    onerror="this.src='<?= base_url('assets/img/siswa.png') ?>'">
                                <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0"
                                    style="width: 40px; height: 40px;" title="Ubah Foto">
                                    <i class="bi bi-camera"></i>
                                </button>
                            </div>
                            <h5 class="fw-bold mb-1">
                                <?= $siswa->nama ?? 'Nama Siswa' ?>
                            </h5>
                            <p class="text-muted mb-3">
                                <?= $siswa->nis ?? '-' ?>
                            </p>

                            <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-start">
                                <small class="text-muted d-block">Kelas</small>
                                <strong>
                                    <?= $siswa->nama_kelas ?? '-' ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="col-md-8">
                    <!-- Data Pribadi -->
                    <div class="card card-modern border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">📋 Data Pribadi</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Nama Lengkap</label>
                                    <input type="text" class="form-control" value="<?= $siswa->nama ?? '-' ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">NIS / NISN</label>
                                    <input type="text" class="form-control"
                                        value="<?= $siswa->nisn ?? $siswa->nis ?? '-' ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Jenis Kelamin</label>
                                    <input type="text" class="form-control" value="<?= $siswa->jenis_kelamin ?? '-' ?>"
                                        readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small">Tempat, Tanggal Lahir</label>
                                    <input type="text" class="form-control"
                                        value="<?= ($siswa->tempat_lahir ?? '-') . ', ' . ($siswa->tanggal_lahir ?? '-') ?>"
                                        readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Alamat</label>
                                    <textarea class="form-control" rows="2"
                                        readonly><?= $siswa->alamat ?? '-' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Kontak (Editable) -->
                    <div class="card card-modern border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">📞 Kontak & Akun</h5>

                            <form id="form-kontak">
                                <?= csrf_field() ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email"
                                            value="<?= $user->email ?? '' ?>" placeholder="Email kamu">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">No. Telepon</label>
                                        <input type="text" class="form-control" name="telepon"
                                            value="<?= $siswa->no_telp ?? '' ?>" placeholder="No. HP/WA">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Ganti Password -->
                    <div class="card card-modern border-0">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">🔒 Keamanan Akun</h5>

                            <form id="form-password">
                                <?= csrf_field() ?>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Password Lama <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password Baru <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="new_password" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Konfirmasi Password <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-warning rounded-pill px-4">
                                            <i class="bi bi-shield-lock me-2"></i>Ganti Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        // Update kontak
        $('#form-kontak').submit(function (e) {
            e.preventDefault();

            const btn = $(this).find('button[type=submit]');
            const originalText = btn.html();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

            $.ajax({
                url: base_url + 'profile/update',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    btn.prop('disabled', false).html(originalText);

                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Kontak berhasil diperbarui',
                            confirmButtonColor: '#00238f'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan'
                        });
                    }
                },
                error: function () {
                    btn.prop('disabled', false).html(originalText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan server'
                    });
                }
            });
        });

        // Change password
        $('#form-password').submit(function (e) {
            e.preventDefault();

            const newPass = $('input[name=new_password]').val();
            const confirmPass = $('input[name=confirm_password]').val();

            if (newPass !== confirmPass) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Konfirmasi password tidak cocok!'
                });
                return;
            }

            if (newPass.length < 6) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Password minimal 6 karakter!'
                });
                return;
            }

            const btn = $(this).find('button[type=submit]');
            const originalText = btn.html();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Mengganti...');

            $.ajax({
                url: base_url + 'profile/password',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    btn.prop('disabled', false).html(originalText);

                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Password berhasil diubah',
                            confirmButtonColor: '#00238f'
                        }).then(() => {
                            $('#form-password')[0].reset();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan'
                        });
                    }
                },
                error: function () {
                    btn.prop('disabled', false).html(originalText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan server'
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>