<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Guru & Pengawas | CBT by Ikasmedia</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?php $logo_app = $setting->logo_kiri == null ? base_url() . 'assets/img/favicon.png' : base_url() . $setting->logo_kiri; ?>
    <link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">
    <meta name="description"
        content="Aplikasi Asesmen Berbasis Komputer membantu sekolah dan siswa dalam pelaksanaan penilaian di sekolah.">
    <meta name="keyword"
        content="ANBK, Ujian, Ujian Nasional, Ulangan Harian, Ulangan Semester, Mid Semester, Asesmen Nasional, Ujian Sekolah">
    <meta name="google" content="nositelinkssearchbox" />
    <meta name="robots" content="index, follow">
    <!-- Bootstrap 5 CSS -->
    <link href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="<?= base_url('assets/plugins/bootstrap-icon/bootstrap-icons.css') ?>" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4F46E5;
            --primary-hover: #4338ca;
            --accent-color: #818cf8;
            --bg-gradient-start: #e0e7ff;
            --bg-gradient-end: #f3f4f6;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            padding: 0;
        }

        /* Safe Centering Mechanism */
        .container {
            /* margin: auto; No longer needed with flex centering */
            max-height: 100vh;
            /* Prevent container from forcing scroll */
        }

        /* Blob Background (Static) */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            z-index: -1;
            /* animation: float 10s infinite ease-in-out; */
        }

        .blob-1 {
            top: -10%;
            left: -5%;
            width: 500px;
            height: 500px;
            background: #c7d2fe;
            animation-delay: 0s;
        }

        .blob-2 {
            bottom: -10%;
            right: -5%;
            width: 400px;
            height: 400px;
            background: #fbcfe8;
            animation-delay: 2s;
        }

        .blob-3 {
            bottom: 20%;
            left: 20%;
            width: 300px;
            height: 300px;
            background: #e9d5ff;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -30px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        /* Card Styling */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 1.25rem;
            /* Even smaller radius */
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 750px;
            /* Reduced from 850px */
            min-height: 450px;
            /* Reduced from 500px */
            transition: transform 0.3s ease;
        }

        /* Hero Image Area */
        .hero-section {
            position: relative;
            background-color: #1e1b4b;
            min-height: 450px;
            /* Match card height */
            overflow: hidden;
        }

        .hero-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
            transition: opacity 0.5s ease-in-out;
            mix-blend-mode: overlay;
        }

        .hero-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(16, 12, 66, 0.9), transparent);
            padding: 1.5rem;
            /* Reduced padding */
            /* Reduced padding */
            color: #fff;
            z-index: 2;
        }

        /* Form Area */
        .form-section {
            padding: 2rem;
            /* Reduced from 2.5rem */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            /* Reduced gap */
            margin-bottom: 1.25rem;
        }

        .logo-icon {
            width: 40px;
            /* Reduced from 48px */
            height: 40px;
            /* Reduced from 48px */
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            /* Reduced from 12px */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            /* Reduced font size */
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        /* Form Controls */
        .form-floating>.form-control {
            border-radius: 0.8rem;
            border: 1px solid #e5e7eb;
            background-color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            /* Smaller font */
        }

        .form-floating>.form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
            background-color: #fff;
        }

        .form-floating>label {
            color: #6b7280;
        }

        .btn-custom-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem;
            /* Reduced padding */
            border-radius: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            width: 100%;
            color: white;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);
            font-size: 0.95rem;
        }

        .btn-custom-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
            color: white;
        }

        .btn-custom-primary:disabled {
            background: #9ca3af;
            transform: none;
        }

        /* Nav Pills */
        .nav-pills {
            background: #f3f4f6;
            padding: 0.25rem;
            border-radius: 0.8rem;
            margin-bottom: 1.5rem;
            /* Reduced */
        }

        .nav-pills .nav-link {
            border-radius: 0.6rem;
            color: #6b7280;
            font-weight: 600;
            transition: all 0.2s;
            padding: 0.5rem 0.8rem;
            /* Reduced padding */
            font-size: 0.9rem;
        }

        .nav-pills .nav-link.active {
            background: #fff;
            color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            z-index: 10;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            /* Fix alignment */
        }

        .input-group-text {
            border-radius: 0.8rem 0 0 0.8rem !important;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-right: none;
            color: #6b7280;
            padding: 0.6rem 1rem;
        }

        .input-with-icon .form-control {
            border-left: none;
            border-radius: 0 0.8rem 0.8rem 0 !important;
            padding-left: 0;
            padding: 0.6rem 1rem;
            /* Specific padding control */
            height: auto;
        }

        .input-with-icon:focus-within .input-group-text {
            border-color: var(--primary-color);
            background-color: #fff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .glass-card {
                min-height: auto;
                margin: 1rem;
                width: calc(100% - 2rem);
                /* Ensure proper width with margin */
            }

            .form-section {
                padding: 2rem 1.5rem;
            }

            body {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
        }
    </style>
</head>

<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="container d-flex justify-content-center">
        <div class="row g-0 glass-card">

            <!-- Hero Image Section -->
            <div class="col-lg-6 d-none d-lg-block hero-section">
                <img id="hero-image" src="<?= base_url() ?>/assets/img/side-bg.png" class="hero-img" alt="Hero">
                <div class="hero-overlay">
                    <div class="mb-3">
                        <span class="badge bg-white bg-opacity-25 backdrop-blur p-2 rounded-3 text-white mb-3">
                            <i id="hero-icon" class="bi bi-easel fs-5"></i>
                        </span>
                        <h2 class="fw-bold mb-2" id="hero-title">Manajemen Kelas</h2>
                        <p class="text-white text-opacity-75 lead fs-6" id="hero-desc">Platform terintegrasi untuk
                            mengelola kegiatan belajar mengajar dengan efisien.</p>
                    </div>
                </div>
            </div>

            <!-- Login Form Section -->
            <div class="col-lg-6 form-section">
                <div class="text-center mb-4">
                    <div class="logo-container">
                        <?php if (isset($setting->logo_kiri) && !empty($setting->logo_kiri)): ?>
                            <img src="<?= base_url() . $setting->logo_kiri ?>" alt="Logo"
                                style="height: 50px; width: auto;">
                        <?php else: ?>
                            <div class="logo-icon">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="infoMessage" class="text-start"></div>

                    <ul class="nav nav-pills nav-fill" id="roleTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="guru-tab" data-bs-toggle="pill"
                                onclick="switchRole('guru')">
                                <i class="bi bi-briefcase me-2"></i>Guru
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="pengawas-tab" data-bs-toggle="pill"
                                onclick="switchRole('pengawas')">
                                <i class="bi bi-shield-check me-2"></i>Pengawas
                            </button>
                        </li>
                    </ul>

                    <h3 class="fw-bold text-dark mb-1" id="form-title">Selamat Datang! 👋</h3>
                    <p class="text-muted" id="form-desc">Silakan masuk ke akun Guru anda.</p>
                </div>

                <form action="<?= base_url('auth/login') ?>" method="POST" id="login">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>"
                        value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="login_variant" value="staff">

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold ms-1" id="label-identity">USERNAME /
                            NIP</label>
                        <div class="input-group input-with-icon">
                            <span class="input-group-text bg-transparent"><i class="bi bi-person-badge fs-5"></i></span>
                            <input type="text" class="form-control form-control-lg fs-6" id="identity" name="username"
                                placeholder="Masukkan ID Pengguna" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-muted small fw-bold ms-1">PASSWORD</label>
                            <a href="#" class="text-decoration-none small text-primary fw-bold">Lupa Password?</a>
                        </div>
                        <div class="input-group input-with-icon position-relative">
                            <span class="input-group-text bg-transparent"><i class="bi bi-lock fs-5"></i></span>
                            <input type="password" class="form-control form-control-lg fs-6" id="password"
                                name="password" placeholder="••••••••" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>


                    <button type="submit" id="submit" class="btn btn-custom-primary">
                        <span id="btnText">L O G I N</span> <i class="bi bi-arrow-right ms-2 transition-icon"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- Footer Credit -->
    <div class="fixed-bottom text-center pb-4">
        <small class="text-muted text-opacity-50">&copy; <?= date('Y') ?> <span
                class="fw-bold"><?= isset($setting->sekolah) ? $setting->sekolah : 'IKASMEDIA' ?></span></small>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <script>
        const heroImage = document.getElementById('hero-image');
        const heroTitle = document.getElementById('hero-title');
        const heroDesc = document.getElementById('hero-desc');
        const heroIcon = document.getElementById('hero-icon');
        const formTitle = document.getElementById('form-title');
        const formDesc = document.getElementById('form-desc');
        const labelIdentity = document.getElementById('label-identity');
        const btnText = document.getElementById('btnText');

        function switchRole(role) {
            heroImage.style.opacity = '0'; // Fade out

            setTimeout(() => {
                if (role === 'guru') {
                    labelIdentity.textContent = "USERNAME / NIP";
                    formTitle.textContent = "Selamat Datang! 👋";
                    formDesc.textContent = "Silakan masuk ke akun Guru anda.";

                    heroImage.src = "<?= base_url('/assets/img/side1.png') ?>";
                    heroTitle.textContent = "Manajemen Kelas";
                    heroDesc.textContent = "Platform terintegrasi untuk mengelola kegiatan belajar mengajar dengan efisien.";
                    heroIcon.className = 'bi bi-easel fs-5';
                } else {
                    labelIdentity.textContent = "USERNAME / ID PENGAWAS";
                    formTitle.textContent = "Halo, Pengawas! 🛡️";
                    formDesc.textContent = "Masuk untuk memantau ujian.";

                    heroImage.src = "<?= base_url('/assets/img/side2.png') ?>";
                    heroTitle.textContent = "Monitoring Ujian";
                    heroDesc.textContent = "Pantau statistik, laporan kinerja, dan evaluasi pelaksanaan ujian secara real-time.";
                    heroIcon.className = 'bi bi-graph-up-arrow fs-5';
                }
                heroImage.style.opacity = '0.8'; // Fade in
            }, 300);
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        $(document).ready(function () {
            let base_url = '<?= base_url(); ?>';
            const $infoMessage = $('#infoMessage');

            $('form#login').on('submit', function (e) {
                e.preventDefault();

                $infoMessage.html('<div class="alert alert-info border-0 shadow-sm"><i class="bi bi-arrow-repeat me-2 spinner-border spinner-border-sm"></i> Memproses...</div>');

                var btnsubmit = $('#submit');
                var originalBtnText = $('#btnText').html();
                var originalBtnIcon = btnsubmit.find('i');

                btnsubmit.attr('disabled', 'disabled');
                $('#btnText').text('LOADING...');
                originalBtnIcon.addClass('d-none');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'JSON',
                    success: function (data) {
                        btnsubmit.removeAttr('disabled');
                        $('#btnText').html(originalBtnText);
                        originalBtnIcon.removeClass('d-none');

                        if (data.status) {
                            $infoMessage.html(`<div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> ${data.message || 'Login Sukses!'}</div>`);

                            setTimeout(() => {
                                const respData = data.data || {};
                                let go = respData.redirect ? respData.redirect : base_url + (respData.url || 'dashboard');
                                if (!respData.redirect && data.role === 'siswa') {
                                    go = 'siswa/cbt'
                                }
                                window.location.href = go;
                            }, 1000);
                        } else {
                            $infoMessage.html(`<div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i> ${data.message || 'Login Gagal'}</div>`);
                            if (data.errors) {
                                $.each(data.errors, function (key, val) {
                                    $('[name="' + key + '"]').addClass('is-invalid');
                                });
                            }
                        }
                    },
                    error: function () {
                        btnsubmit.removeAttr('disabled');
                        $('#btnText').html(originalBtnText);
                        originalBtnIcon.removeClass('d-none');
                        $infoMessage.html('<div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-server me-2"></i> Terjadi kesalahan koneksi.</div>');
                    }
                });
            });

            $('input').on('focus input', function () {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
</body>

</html>