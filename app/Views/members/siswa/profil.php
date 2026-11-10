<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-xl-10">

        <!-- Header -->
        <div class="page-header mb-4">
            <div class="page-title-box">
                <h2><i class="bi bi-person-circle text-primary me-2"></i>Profil Saya</h2>
                <p>Kelola informasi akun Anda</p>
            </div>
            <div>
                <a href="<?= base_url('dashboard') ?>" class="btn btn-primary btn-rounded shadow-sm">
                    <i class="bi bi-house-door-fill me-2"></i>Beranda
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Profile Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <!-- Header Gradient -->
                    <div class="text-center py-5"
                        style="background: linear-gradient(135deg, #1877F2 0%, #00238f 100%);">
                        <?php
                        $foto = (isset($siswa->foto) && !empty($siswa->foto) && file_exists(FCPATH . $siswa->foto))
                            ? base_url($siswa->foto)
                            : base_url('assets/img/logo.png');
                        $defaultFoto = base_url('assets/img/logo.png');
                        ?>
                        <img src="<?= $foto ?>" class="rounded-4 border border-4 border-white shadow"
                            style="width: 250px; height: 250px; object-fit: cover;"
                            onerror="this.src='<?= $defaultFoto ?>'"
                            alt="Foto Profil">
                    </div>

                    <div class="card-body text-center pt-3">
                        <h4 class="fw-bold mb-1"><?= esc($siswa->nama ?? 'Siswa') ?></h4>
                        <p class="text-muted mb-3"><?= esc($siswa->nis ?? '-') ?></p>

                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                <i class="bi bi-mortarboard me-1"></i><?= esc($siswa->nama_kelas ?? '-') ?>
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                <i class="bi bi-calendar3 me-1"></i><?= esc($tp_active->tahun ?? '-') ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-4 py-3">
                                <span class="text-muted"><i class="bi bi-card-text me-2"></i>NISN</span>
                                <span class="fw-bold"><?= esc($siswa->nisn ?? '-') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-4 py-3">
                                <span class="text-muted"><i class="bi bi-gender-ambiguous me-2"></i>Jenis Kelamin</span>
                                <span
                                    class="fw-bold"><?= ($siswa->jenis_kelamin ?? 'L') == 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-4 py-3">
                                <span class="text-muted"><i class="bi bi-calendar-date me-2"></i>Tanggal Lahir</span>
                                <span
                                    class="fw-bold"><?= !empty($siswa->tanggal_lahir) ? date('d M Y', strtotime($siswa->tanggal_lahir)) : '-' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-4 py-3">
                                <span class="text-muted"><i class="bi bi-telephone me-2"></i>No. HP</span>
                                <span class="fw-bold"><?= esc($siswa->hp ?? '-') ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-shield-lock text-primary me-2"></i>Ganti Password
                        </h5>
                    </div>
                    <form id="formPassword">
                        <?= csrf_field() ?>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Password Lama</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input type="password" name="old_password" class="form-control border-start-0"
                                            placeholder="Masukkan password lama" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" name="new_password" class="form-control border-start-0"
                                            placeholder="Masukkan password baru" required>
                                    </div>
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>
                                        <input type="password" name="confirm_password"
                                            class="form-control border-start-0" placeholder="Ulangi password baru"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-4 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Tips keamanan:</strong> Gunakan kombinasi huruf, angka, dan simbol untuk
                                password yang kuat.
                            </div>
                        </div>
                        <div class="card-footer bg-light border-0 p-4">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm rounded-pill">
                                <i class="bi bi-save me-2"></i>Simpan Password Baru
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Additional Info Card -->
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-info-circle text-primary me-2"></i>Informasi Lainnya
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                        <i class="bi bi-building text-primary"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Sekolah</small>
                                        <span class="fw-bold"><?= esc($setting->nama_sekolah ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                        <i class="bi bi-calendar-check text-success"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Semester</small>
                                        <span class="fw-bold"><?= esc($smt_active->nama_smt ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
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
    $('#formPassword').on('submit', function (e) {
        e.preventDefault();

        const newPass = $('[name="new_password"]').val();
        const confirmPass = $('[name="confirm_password"]').val();

        if (newPass.length < 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Password Terlalu Pendek',
                text: 'Password minimal 6 karakter'
            });
            return;
        }

        if (newPass !== confirmPass) {
            Swal.fire({
                icon: 'error',
                title: 'Password Tidak Cocok',
                text: 'Konfirmasi password tidak sesuai dengan password baru'
            });
            return;
        }

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '<?= base_url('siswa/gantiPassword') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message || 'Password berhasil diubah',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#formPassword')[0].reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.message || 'Terjadi kesalahan saat mengubah password'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menghubungi server'
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>