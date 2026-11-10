<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>

<div class="help-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="text-center mb-5">Bantuan</h1>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="mb-3">Panduan Login</h3>
                        <ol>
                            <li>Klik tombol "Login Siswa" di halaman utama</li>
                            <li>Masukkan username (NIS/NISN) dan password Anda</li>
                            <li>Klik tombol "Masuk"</li>
                        </ol>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="mb-3">Mengikuti Ujian</h3>
                        <ol>
                            <li>Login ke sistem</li>
                            <li>Pilih menu "Asesmen (CBT)"</li>
                            <li>Pilih ujian yang ingin diikuti</li>
                            <li>Klik "Mulai Ujian"</li>
                        </ol>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="<?= base_url('/') ?>" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>