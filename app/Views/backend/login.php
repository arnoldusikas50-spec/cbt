<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Guru & Pengawas | CBT by Ikasmedia</title>
    
    <?php $logo_app = $setting->logo_kiri == null ? base_url() . 'assets/img/favicon.png' : base_url() . $setting->logo_kiri; ?>
    <link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="<?= base_url('assets/plugins/bootstrap-icon/bootstrap-icons.css') ?>" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3f37c9;
            --secondary: #7209b7;
            --accent: #4cc9f0;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-600: #475569;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --radius-2xl: 2rem;
            --radius-xl: 1.5rem;
            --radius-lg: 1rem;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --font-main: 'Outfit', sans-serif;
        }

        * { font-smoothing: antialiased; -webkit-font-smoothing: antialiased; }

        body {
            font-family: var(--font-main);
            background-color: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            margin: 0;
            padding: 1.5rem;
        }

        /* Ambient Animated Background */
        .ambient-orb {
            position: fixed;
            width: 60vmax;
            height: 60vmax;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            z-index: -1;
            animation: moveOrb 25s infinite alternate ease-in-out;
            pointer-events: none;
        }
        .orb-1 { top: -20%; left: -20%; background: var(--primary); }
        .orb-2 { bottom: -20%; right: -20%; background: var(--secondary); animation-delay: -5s; }
        .orb-3 { top: 30%; right: 10%; background: var(--accent); width: 40vmax; height: 40vmax; animation-delay: -10s; }

        @keyframes moveOrb {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(10%, 15%) scale(1.1); }
            100% { transform: translate(-5%, -10%) scale(0.95); }
        }

        /* Glass Login Container */
        .glass-container {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: var(--radius-2xl);
            box-shadow: 0 40px 100px -20px rgba(15, 23, 42, 0.15);
            width: 100%;
            max-width: 960px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            overflow: hidden;
            animation: containerAppear 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes containerAppear {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Left Branding Section */
        .brand-section {
            background: linear-gradient(145deg, var(--slate-900) 0%, #1e1b4b 100%);
            padding: 3.5rem;
            color: white;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .brand-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url('<?= base_url() ?>/assets/img/side-bg.png') center/cover no-repeat;
            opacity: 0.45;
            z-index: 1;
        }

        .brand-mesh {
            position: absolute;
            top: -20%;
            right: -20%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(67, 97, 238, 0.4) 0%, transparent 70%);
            filter: blur(50px);
            z-index: 2;
        }

        .brand-content { position: relative; z-index: 3; }

        .brand-logo-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2.5rem;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
        }

        .brand-logo-circle img { max-width: 50px; height: auto; }

        .brand-title {
            font-weight: 800;
            font-size: 2.5rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.04em;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-sub {
            font-size: 1.1rem;
            color: var(--slate-400);
            line-height: 1.6;
            font-weight: 400;
        }

        .brand-footer {
            position: relative;
            z-index: 2;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Right Form Section */
        .form-section {
            padding: 4rem 3.5rem;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .welcome-head { margin-bottom: 2.5rem; }
        .welcome-head h2 { 
            font-weight: 800; 
            color: var(--slate-900); 
            letter-spacing: -0.02em; 
            margin-bottom: 0.5rem;
        }
        .welcome-head p { color: var(--slate-600); }

        /* Input Controls */
        .input-group-modern {
            position: relative;
            margin-bottom: 1.75rem;
        }

        .input-group-modern label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--slate-400);
            margin-bottom: 0.6rem;
            margin-left: 0.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: var(--slate-50);
            border: 2px solid var(--slate-100);
            border-radius: 1.25rem;
            padding: 0.35rem 1rem;
            transition: var(--transition);
        }

        .input-wrapper:focus-within {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 10px 20px -5px rgba(67, 97, 238, 0.1);
            transform: translateY(-1px);
        }

        .input-wrapper i {
            font-size: 1.2rem;
            color: var(--slate-400);
            margin-right: 0.75rem;
            transition: var(--transition);
        }

        .input-wrapper:focus-within i { color: var(--primary); }

        .input-wrapper .form-control {
            border: none;
            background: transparent;
            box-shadow: none !important;
            padding: 0.6rem 0;
            font-weight: 500;
            font-size: 1rem;
            color: var(--slate-900);
        }

        .input-wrapper .form-control::placeholder { color: var(--slate-400); font-weight: 400; }

        .pass-toggle {
            background: none;
            border: none;
            color: var(--slate-400);
            padding: 0.5rem;
            transition: var(--transition);
        }
        .pass-toggle:hover { color: var(--primary); }

        /* Actions */
        .form-actions {
            margin-top: 2rem;
        }

        .btn-auth {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            width: 100%;
            padding: 1.1rem;
            border-radius: 1.25rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.02em;
            transition: var(--transition);
            box-shadow: 0 15px 30px -10px rgba(67, 97, 238, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-auth:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -12px rgba(67, 97, 238, 0.5);
            filter: brightness(1.1);
            color: white;
        }

        .btn-auth:active { transform: translateY(-1px); }

        /* Alerts */
        .alert-premium {
            padding: 1rem 1.25rem;
            border-radius: 1.1rem;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            border: 1px solid transparent;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }

        .alert-premium.info { background: #eef2ff; border-color: #e0e7ff; color: #4338ca; }
        .alert-premium.error { background: #fff1f2; border-color: #ffe4e6; color: #be123c; }
        .alert-premium.success { background: #f0fdf4; border-color: #dcfce7; color: #15803d; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }
        .anim-shake { animation: shake 0.4s ease-in-out; }

        /* Responsive */
        @media (max-width: 991px) {
            .glass-container { grid-template-columns: 1fr; max-width: 480px; }
            .brand-section { display: none; }
            .form-section { padding: 3.5rem 2.5rem; }
        }

        @media (max-width: 480px) {
            body { padding: 1rem; }
            .form-section { padding: 2.5rem 1.5rem; }
            .brand-title { font-size: 2rem; }
        }

        /* Modern Checkbox */
        .custom-check {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            font-size: 0.85rem;
            color: var(--slate-600);
            font-weight: 500;
        }
        .custom-check input { display: none; }
        .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid var(--slate-200);
            border-radius: 6px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .custom-check:hover .checkmark { border-color: var(--primary-light); }
        .custom-check input:checked + .checkmark {
            background: var(--primary);
            border-color: var(--primary);
        }
        .custom-check input:checked + .checkmark::after {
            content: "\F26E";
            font-family: "bootstrap-icons";
            color: white;
            font-size: 0.7rem;
        }

        .hero-footer-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        .hero-footer-link:hover { color: white; opacity: 1; }
    </style>
</head>

<body>

    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>
    <div class="ambient-orb orb-3"></div>

    <div class="glass-container anim-fade">
        <!-- Brand Side -->
        <div class="brand-section">
            <div class="brand-mesh"></div>
            <div class="brand-content">
                <div class="brand-logo-circle">
                    <?php if (isset($setting->logo_kiri) && !empty($setting->logo_kiri)): ?>
                        <img src="<?= base_url() . $setting->logo_kiri ?>" alt="Logo">
                    <?php else: ?>
                        <i class="bi bi-mortarboard-fill text-white fs-1"></i>
                    <?php endif; ?>
                </div>
                <h1 class="brand-title">CBT Panel Management</h1>
                <p class="brand-sub">Platform asesmen digital berbasis Komputer dan Android untuk penilaian proses belajar siswa.</p>
            </div>
            
            <div class="brand-footer">
                <span class="opacity-50">&copy; <?= date('Y') ?> <?= $setting->sekolah ?? 'IKASMEDIA' ?></span>
                <span class="opacity-25">|</span>
                <a href="<?= base_url() ?>" class="hero-footer-link">Versi 4.0 </a>
            </div>
        </div>

        <!-- Form Side -->
        <div class="form-section">
            <div class="welcome-head text-center text-lg-start">
                <h2>Selamat Datang</h2>
                <p>Masuk menggunakan akun yang Anda miliki.</p>
            </div>

            <div id="infoMessage"></div>

            <form action="<?= base_url('auth/login') ?>" method="POST" id="loginForm">
                <?= csrf_field() ?>
                <input type="hidden" name="login_variant" value="staff">

                <div class="input-group-modern">
                    <label for="identity">Username</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-circle"></i>
                        <input type="text" class="form-control" id="identity" name="username" 
                               placeholder="Masukkan username" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="input-group-modern">
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <label for="password">Password</label>   
                    </div>
                    <div class="input-wrapper">
                        <i class="bi bi-shield-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Masukkan password" required autocomplete="current-password">
                        <button type="button" class="pass-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div> 
                <div class="form-actions mt-1">
                    <button type="submit" id="btnSubmit" class="btn-auth">
                        <span id="btnText">L O G I N</span>
                        <i class="bi bi-arrow-right-short fs-4"></i>
                    </button>
                </div>
            </form>

            <!-- Footer -->

            <div class="text-center mt-5 d-lg-none">
                <p class="text-slate-400 small">&copy; <?= date('Y') ?> <?= $setting->sekolah ?? 'IKASMEDIA' ?></p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }

        $(document).ready(function() {
            const base_url = '<?= base_url(); ?>';
            const $infoMessage = $('#infoMessage');

            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                $infoMessage.html('<div class="alert-premium info"><span class="spinner-border spinner-border-sm"></span> Autentikasi...</div>');
                
                const $btn = $('#btnSubmit');
                const $btnText = $('#btnText');
                const originalText = $btnText.text();
                
                $btn.prop('disabled', true).css('opacity', '0.8');
                $btnText.text('MOHON TUNGGU');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'JSON',
                    success: function(data) {
                        if (data.status) {
                            $infoMessage.html(`<div class="alert-premium success"><i class="bi bi-check2-circle fs-5"></i> ${data.message || 'Login berhasil!'}</div>`);
                            
                            setTimeout(() => {
                                const respData = data.data || {};
                                let go = respData.redirect ? respData.redirect : base_url + (respData.url || 'dashboard');
                                window.location.href = go;
                            }, 800);
                        } else {
                            $btn.prop('disabled', false).css('opacity', '1');
                            $btnText.text(originalText);
                            $infoMessage.html(`<div class="alert-premium error"><i class="bi bi-exclamation-octagon fs-5"></i> ${data.message || 'Username atau password salah.'}</div>`);
                            
                            // Shake animation
                            $('.glass-container').addClass('anim-shake');
                            setTimeout(() => $('.glass-container').removeClass('anim-shake'), 400);
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).css('opacity', '1');
                        $btnText.text(originalText);
                        let msg = xhr.status === 403 ? 'Sesi kedaluwarsa, silakan muat ulang.' : 'Gagal menghubungi server.';
                        $infoMessage.html(`<div class="alert-premium error"><i class="bi bi-wifi-off fs-5"></i> ${msg}</div>`);
                    }
                });
            });
        });
    </script>
</body>

</html>
