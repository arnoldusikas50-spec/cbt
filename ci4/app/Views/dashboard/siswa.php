<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dashboard Siswa - <?= esc($setting->nama_sekolah ?? 'CBT AKM') ?></title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-icon/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/app/css/siswa-dashboard.css') ?>">

    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>
</head>

<body style="background-color: #f4f6f9;">

    <main style="min-height: 100vh; display: flex; flex-direction: column; margin: 0; padding: 0;">

        <div class="container-fluid main-content main-content-overlap" style="flex: 1;">
            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11">

                    <!-- Welcome Banner -->
                    <div class="welcome-banner shadow-lg mb-5">
                        <div class="row justify-content-center align-items-center">
                            <div class="col-md-8">
                                <h2 class="font-weight-bold">Halo,
                                    <?= isset($siswa->nama) ? explode(' ', $siswa->nama)[0] : 'Siswa' ?>! 👋
                                </h2>
                                <p class="mb-0">Selamat datang kembali di Aplikasi Asesmen.</p>
                            </div>
                            <div class="col-md-4 text-center d-none d-md-block">
                                <i class="bi bi-rocket-takeoff-fill"
                                    style="font-size: 4rem; color: rgba(24, 119, 242, 0.15)"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Cards -->
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="card shadow-lg border-0" style="border-radius: 15px;">
                                <div class="card-body p-4">
                                    <h4 class="mb-4 text-secondary font-weight-bold">Menu Utama</h4>
                                    <div class="menu-grid">
                                        <!-- Absensi Ujian -->
                                        <a href="<?= base_url('cbtsiswa/absensi') ?>" class="menu-item bg-absensi">
                                            <div class="icon-box">
                                                <img src="<?= base_url('assets/app/img/absensi.png') ?>" alt="Absensi"
                                                    onerror="this.src='https://cdn-icons-png.flaticon.com/512/2921/2921222.png'">
                                            </div>
                                            <span>Absensi</span>
                                        </a>

                                        <!-- Asesmen (CBT) -->
                                        <a href="<?= base_url('cbtsiswa/cbt') ?>" class="menu-item bg-cbt">
                                            <div class="icon-box">
                                                <img src="<?= base_url('assets/app/img/exam.png') ?>" alt="CBT"
                                                    onerror="this.src='https://cdn-icons-png.flaticon.com/512/4301/4301550.png'">
                                            </div>
                                            <span>Asesmen (CBT)</span>
                                        </a>

                                        <!-- Nilai -->
                                        <a href="<?= base_url('kbm/hasil') ?>" class="menu-item bg-nilai">
                                            <div class="icon-box">
                                                <img src="<?= base_url('assets/app/img/nilai.png') ?>" alt="Nilai"
                                                    onerror="this.src='https://cdn-icons-png.flaticon.com/512/2921/2921239.png'">
                                            </div>
                                            <span>Nilai</span>
                                        </a>

                                        <!-- Catatan -->
                                        <a href="<?= base_url('siswa/catatan') ?>" class="menu-item bg-catatan">
                                            <div class="icon-box">
                                                <img src="<?= base_url('assets/app/img/catatan.png') ?>" alt="Catatan"
                                                    onerror="this.src='https://cdn-icons-png.flaticon.com/512/2921/2921244.png'">
                                            </div>
                                            <span>Catatan</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Running Text (Marquee) -->
                    <?php if (!empty($running_text)): ?>
                        <div class="row mb-5">
                            <div class="col-12">
                                <div class="glass-card px-4 py-3 d-flex align-items-center">
                                    <div class="badge bg-primary me-3 p-2 shadow-sm">
                                        <i class="bi bi-megaphone-fill me-1"></i> INFO
                                    </div>
                                    <div class="w-100 overflow-hidden text-nowrap position-relative" style="height: 24px;">
                                        <marquee onmouseover="this.stop();" onmouseout="this.start();" scrollamount="6">
                                            <?php
                                            $texts = [];
                                            foreach ($running_text as $rt) {
                                                $texts[] = '<span class="me-5 text-dark font-weight-bold" style="font-size: 1.1em">' . $rt->text . '</span>';
                                            }
                                            echo implode('<i class="bi bi-patch-check-fill mx-3 text-secondary text-xs"></i>', $texts);
                                            ?>
                                        </marquee>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Footer / Quick Actions -->
                    <div class="row mb-5 justify-content-center">
                        <div class="col-md-6 col-12 text-center">
                            <div class="glass-card p-4">
                                <button onclick="logout()" class="btn-logout-modern w-100">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar Aplikasi
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="text-center text-muted small pb-5 mt-auto">
            COPYRIGHT &copy; <?= date('Y'); ?> | <?= htmlspecialchars($setting->nama_sekolah ?? 'CBT AKM'); ?>
        </div>
    </main>

    <script>
        function logout() {
            Swal.fire({
                title: 'Keluar Aplikasi?',
                text: "Anda akan segera keluar dari sesi belajar hari ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00238f',
                cancelButtonColor: '#adb5bd',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-4 shadow-lg'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= base_url("auth/logout") ?>';
                }
            })
        }
    </script>
</body>

</html>