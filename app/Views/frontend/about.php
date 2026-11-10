<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>

<div class="about-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="text-center mb-5">Tentang Aplikasi</h1>

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h3 class="mb-3">Aplikasi Asesmen Digital</h3>
                        <p>
                            Sistem manajemen asesmen berbasis komputer yang dirancang untuk memfasilitasi
                            proses penilaian dan evaluasi pembelajaran secara digital.
                        </p>
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