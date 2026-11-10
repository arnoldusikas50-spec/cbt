<?= $this->extend('frontend/layouts/main') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6 text-center text-lg-start">
                <div class="hero-content">
                    <h1 class="hero-title">
                        Selamat Datang di<br>
                        <span class="text-primary">
                            <?= esc($setting->nama_sekolah ?? 'Aplikasi Asesmen') ?>
                        </span>
                    </h1>
                    <p class="hero-subtitle">
                        Platform asesmen digital untuk pembelajaran yang lebih efektif dan interaktif
                    </p>
                    <div class="hero-buttons">
                        <a href="<?= base_url('login') ?>" class="btn btn-primary btn-lg px-5 py-3 me-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login Siswa
                        </a>
                        <a href="<?= base_url('admin/login') ?>" class="btn btn-outline-primary btn-lg px-5 py-3">
                            <i class="bi bi-shield-lock me-2"></i>Login Admin
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <div class="hero-image">
                    <i class="bi bi-mortarboard-fill text-primary" style="font-size: 20rem; opacity: 0.1;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Fitur Unggulan</h2>
            <p class="section-subtitle">Platform lengkap untuk asesmen digital</p>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon bg-primary">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h3 class="feature-title">Asesmen CBT</h3>
                    <p class="feature-desc">
                        Sistem ujian berbasis komputer yang modern, aman, dan mudah digunakan
                    </p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon bg-success">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h3 class="feature-title">Nilai Real-time</h3>
                    <p class="feature-desc">
                        Lihat hasil ujian dan nilai langsung setelah menyelesaikan asesmen
                    </p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon bg-info">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3 class="feature-title">Jadwal Terpadu</h3>
                    <p class="feature-desc">
                        Kelola dan pantau jadwal ujian dengan mudah dalam satu platform
                    </p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon bg-warning">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h3 class="feature-title">Absensi Digital</h3>
                    <p class="feature-desc">
                        Sistem absensi otomatis untuk setiap sesi ujian
                    </p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon bg-danger">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="feature-title">Keamanan Tinggi</h3>
                    <p class="feature-desc">
                        Dilengkapi dengan sistem keamanan dan anti-kecurangan
                    </p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon bg-purple">
                        <i class="bi bi-phone"></i>
                    </div>
                    <h3 class="feature-title">Mobile Friendly</h3>
                    <p class="feature-desc">
                        Akses dari perangkat apapun, kapanpun, dan dimanapun
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Announcements Section -->
<?php if (!empty($announcements)): ?>
    <section class="announcements-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Pengumuman Terbaru</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="announcement-marquee">
                        <marquee behavior="scroll" direction="left" scrollamount="5" onmouseover="this.stop();"
                            onmouseout="this.start();">
                            <?php foreach ($announcements as $announcement): ?>
                                <span class="announcement-item">
                                    <i class="bi bi-megaphone-fill text-primary me-2"></i>
                                    <?= esc($announcement->text) ?>
                                </span>
                                <span class="mx-4">•</span>
                            <?php endforeach; ?>
                        </marquee>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Footer -->
<footer class="footer-section py-4 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">
                    &copy;
                    <?= date('Y') ?>
                    <?= esc($setting->nama_sekolah ?? 'Aplikasi Asesmen') ?>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="<?= base_url('about') ?>" class="text-white text-decoration-none me-3">Tentang</a>
                <a href="<?= base_url('help') ?>" class="text-white text-decoration-none">Bantuan</a>
            </div>
        </div>
    </div>
</footer>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .hero-buttons .btn {
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .hero-buttons .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
    }

    .section-subtitle {
        font-size: 1.125rem;
        color: #718096;
    }

    .feature-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        text-align: center;
        transition: all 0.3s;
        height: 100%;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: white;
    }

    .bg-purple {
        background-color: #9333ea !important;
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #2d3748;
    }

    .feature-desc {
        color: #718096;
        margin-bottom: 0;
    }

    .announcement-marquee {
        background: linear-gradient(135deg, #f6f8fb 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-radius: 15px;
        font-size: 1.125rem;
    }

    .announcement-item {
        color: #2d3748;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1rem;
        }

        .hero-buttons .btn {
            display: block;
            width: 100%;
            margin-bottom: 1rem !important;
        }
    }
</style>
<?= $this->endSection() ?>