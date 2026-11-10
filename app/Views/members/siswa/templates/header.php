<!DOCTYPE html>
<html lang="id">

<head>
    <!-- Meta Tag -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        <?= $judul ?? 'Dashboard' ?> - CBT by Ikasmedia
    </title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- Favicon -->
    <?php
    $logo_app = ($setting->logo_kiri ?? null) ? base_url($setting->logo_kiri) : base_url('assets/img/favicon.png');
    ?>
    <link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">

    <!-- CSS Plugins -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-icon/bootstrap-icons.css') ?>">
    <link rel="stylesheet"
        href="<?= base_url('assets/plugins/datatables-responsive/css/responsive.bootstrap5.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>">
    <link rel="stylesheet"
        href="<?= base_url('assets/plugins/select2-bootstrap5-theme/select2-bootstrap-5-theme.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/multiselect/css/multi-select.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-lite.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/quill/quill.snow.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/app/css/show.toast.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/dropify/css/dropify.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/katex/katex.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/app/css/premium_adminlte.css') ?>">

    <!-- Local Tailwind CSS -->
    <!-- Suppress Tailwind Production Warning -->
    <script>
        if (window.console) {
            const originalWarn = console.warn;
            console.warn = function() {
                if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com')) return;
                originalWarn.apply(console, arguments);
            };
        }
    </script>
    <script src="<?= base_url('assets/app/vendor/tailwind.js') ?>"></script>
    
    <style>
        @font-face {
            font-family: 'Inter';
            src: url('<?= base_url('assets/fonts/google/Inter-Regular.ttf') ?>') format('truetype');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Inter';
            src: url('<?= base_url('assets/fonts/google/Inter-SemiBold.ttf') ?>') format('truetype');
            font-weight: 600;
            font-style: normal;
        }
        @font-face {
            font-family: 'Inter';
            src: url('<?= base_url('assets/fonts/google/Inter-Bold.ttf') ?>') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['monospace'],
                    },
                    colors: {
                        brand: {
                            blue: '#0047cc',
                            dark: '#003399',
                            bg: '#f8fafc'
                        }
                    }
                }
            }
        }
    </style>
    <style>
        body {
            background-color: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(0, 71, 204, 0.03) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 71, 204, 0.03) 0px, transparent 50%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            padding-bottom: 100px; 
        }
        
        @media (min-width: 640px) {
            body {
                padding-bottom: 0;
            }
        }

        .header-bg {
            background: url('<?= base_url('assets/img/header-bg.png') ?>') left center / auto 100% no-repeat, 
                        #00238f;
            position: relative;
            overflow: hidden;
            padding-bottom: 72px; 
        }

        @media (max-width: 767px) {
            .header-bg {
                padding-bottom: 56px;
            }
        }
        @media (max-width: 479px) {
            .header-bg {
                padding-bottom: 48px;
            }
        }

        .menu-glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 24px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .menu-glass-card:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px -10px rgba(0, 71, 204, 0.15);
        }

        .card-absensi:hover { border-color: rgba(59, 130, 246, 0.5); }
        .card-asesmen:hover { border-color: rgba(236, 72, 153, 0.5); }
        .card-nilai:hover { border-color: rgba(20, 184, 166, 0.5); }
        .card-catatan:hover { border-color: rgba(234, 179, 8, 0.5); }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
        .menu-glass-card:hover img {
            animation: float 2s ease-in-out infinite;
        }

        .clock-glow {
            text-shadow: 0 0 20px rgba(0, 71, 204, 0.1);
        }
    </style>
    <!-- AdminLTE -->
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/adminlte.min.css') ?>">

    <!-- Siswa Dashboard CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/app/css/siswa-dashboard.css?v=' . time()) ?>">

    <!-- Scripts -->
    <script src="<?= base_url('assets/plugins/signature/signature_pad.min.js?v=08') ?>"></script>
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
    <script defer src="<?= base_url('assets/plugins/katex/contrib/auto-render.min.js') ?>"
        onload="renderMathInElement(document.body);"></script>

    <style>
        .linker-list p {
            margin-bottom: .5rem;
            margin-top: .5rem;
        }

        .no-close .ui-dialog-titlebar-close {
            display: none;
        }

        .fadeInDown {
            animation: fadeInDown 0.5s ease-in-out;
        }

        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<script type="text/javascript">
    let base_url = '<?= base_url() ?>';
</script>

<body class="layout-top-nav bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Header Siswa (Modern Design) -->
        <?php if (!(isset($hide_header) && $hide_header)): ?>
        <header class="header-bg text-white shadow-lg z-0">
            <div class="max-w-10xl mx-auto px-6 py-6 flex justify-between items-center relative z-10">
                <div class="flex items-center">
                    <div class="flex items-center justify-center overflow-hidden">
                        <img src="<?= base_url('assets/img/logo_cbt.png') ?>" alt="Logo" class="max-h-16 lg:max-h-20 w-auto object-contain" onerror="this.src='<?= base_url('assets/img/favicon.png') ?>'; this.onerror=null;">
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="text-right hidden sm:block">
                        <h2 class="font-bold text-sm tracking-wide uppercase"><?= esc($siswa->nama ?? 'Siswa') ?></h2>
                        <p class="text-xs text-blue-100">NISN: <?= esc($siswa->nisn ?? '-') ?> • Kelas: <?= esc($siswa->kode_kelas ?? $siswa->nama_kelas ?? '-') ?></p>
                    </div>
                    <div class="h-14 w-[1px] bg-white/20"></div>
                    <?php 
                    $full_name = $siswa->nama ?? 'Siswa';
                    $first_name = explode(' ', trim($full_name))[0];
                    ?>
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center">
                            <div class="bg-blue-400/30 w-12 h-12 lg:w-14 lg:h-14 rounded-xl flex items-center justify-center border border-white/20 overflow-hidden shadow-sm">
                                <?php
                                $foto_siswa = $siswa->foto ?? null;
                                $foto_exists = $foto_siswa && file_exists(FCPATH . $foto_siswa);
                                if ($foto_exists && !empty($foto_siswa)): ?>
                                    <img src="<?= base_url($foto_siswa) ?>" alt="Student" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="bi bi-person-fill text-2xl lg:text-3xl"></i>
                                <?php endif; ?>
                            </div>
                            <span class="text-[9px] font-bold mt-1 text-white opacity-90 sm:hidden uppercase tracking-wider"><?= esc($first_name) ?></span>
                        </div>
                        <button onclick="<?= (uri_string() == 'cbt/penilaian' || uri_string() == 'cbtsiswa/penilaian' || strpos(uri_string(), 'penilaian') !== false) ? 'BlokirKeluar()' : 'logout()' ?>" class="bg-white text-[#00238f] text-xs font-bold px-4 py-2 rounded-lg hover:bg-gray-100 transition-all shadow-sm hidden sm:block">
                            Logout
                        </button>

                    </div>
                </div>
            </div>
        </header>
        <?php endif; ?>