<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<style>
    /* Hero Welcome Card */
    .hero-welcome-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 10px 30px -5px rgba(0, 71, 204, 0.05) !important;
    }

    /* Pulse Indicator */
    .pulse-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #2563eb;
        animation: pulseLive 1.8s infinite ease-in-out;
    }
    @keyframes pulseLive {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(0.6); opacity: 0.35; }
    }

    .live-dot-green {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #10b981;
    }
    .live-dot-amber {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #f59e0b;
    }

    @keyframes wave { 0%,100%{transform:rotate(0)} 25%{transform:rotate(-15deg)} 75%{transform:rotate(15deg)} }
    .animate-wave { animation: wave 2.5s ease-in-out infinite; }
    
    .fade-in-up { animation: fadeInUp 0.4s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* ══ MENU CARD COMPONENT ══
       Style kartu menu (grid, watermark, hover, arrow) berada di
       assets/app/css/siswa-dashboard.css → class .siswa-dashboard__menu-*
    */

    @media (prefers-reduced-motion: reduce) {
        .fade-in-up, .animate-wave, .pulse-indicator {
            animation: none !important;
            opacity: 1 !important;
        }
    }
</style>

<div class="container-fluid py-4 container-p-x main-content-overlap fade-in-up pb-5 mb-5 pb-lg-4 mb-lg-0">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">

            <!-- ══ WELCOME HERO CARD ══ -->
            <div class="card hero-welcome-card border-0 shadow-sm rounded-4 overflow-hidden mb-4 position-relative">
                <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row align-items-center justify-content-between gap-4 position-relative z-1">
                    <!-- Left: Profile & Greeting -->
                    <div class="flex-grow-1 text-center text-md-start">
                        <div class="d-inline-flex align-items-center gap-2 bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill mb-3 border border-primary border-opacity-25 shadow-xs">
                            <span class="pulse-indicator"></span>
                            <span class="fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.8px;">Siswa Aktif</span>
                            <?php if (!empty($siswa->nama_kelas)): ?>
                            <span class="text-muted opacity-50">&bull;</span>
                            <span class="fw-bold" style="font-size: 0.72rem;"><?= esc($siswa->nama_kelas) ?></span>
                            <?php endif; ?>
                        </div>
                        <h2 class="fw-bolder text-dark mb-2 display-6" style="letter-spacing: -1px; font-weight: 800;">
                            Halo, <span class="text-primary"><?= esc(isset($siswa->nama) && !empty(trim($siswa->nama)) ? explode(' ', trim($siswa->nama))[0] : 'Siswa') ?>!</span>
                            <span class="d-inline-block animate-wave fs-3 ms-1" style="transform-origin: 70% 70%;">👋</span>
                        </h2>
                        <p class="text-secondary fw-normal mb-0" style="font-size: 1.05rem;">
                            Selamat datang di Portal Ujian. Utamakan kejujuran & raih prestasi terbaik!
                        </p>
                        <?php if (!empty($siswa->nis) || !empty($siswa->username)): ?>
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3 mt-3 text-muted small">
                            <?php if (!empty($siswa->nis)): ?>
                            <span><i class="bi bi-person-vcard me-1 text-primary"></i>NIS: <b><?= esc($siswa->nis) ?></b></span>
                            <?php endif; ?>
                            <span><i class="bi bi-person-badge me-1 text-primary"></i>ID: <b><?= esc($siswa->username ?? '-') ?></b></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Digital Clock & Date Widget -->
                    <div class="ms-md-auto w-100 clock-widget-container" style="max-width: 270px;">
                        <div class="bg-white border p-3.5 p-md-4 rounded-4 shadow-sm text-center d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex align-items-center gap-1 text-muted text-uppercase fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 1px;">
                                <span class="live-dot-green"></span>
                                <span id="current-date">-- --- ----</span>
                            </div>
                            <div class="display-6 fw-black text-primary font-monospace my-1" id="digital-clock" style="font-family: 'JetBrains Mono', 'Inter', monospace; letter-spacing: -1px; font-weight: 800;">
                                00:00:00
                            </div>
                            <div class="text-muted small" style="font-size: 0.7rem;">Waktu Server </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ RUNNING TEXT / PENGUMUMAN (IF ANY) ══ -->
            <?php if (!empty($running_text) && !empty($running_text->text)): ?>
            <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 py-2 px-3 mb-4 bg-white border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-center rounded-circle bg-info bg-opacity-10 text-info flex-shrink-0" style="width: 36px; height: 36px;">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden" style="font-size: 0.88rem;">
                    <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();" class="text-dark fw-medium mb-0">
                        <?= esc($running_text->text) ?>
                    </marquee>
                </div>
            </div>
            <?php endif; ?>

            <!-- ══ EXAM BROWSER STATUS & DOWNLOAD BANNER ══ -->
            <div id="bannerExamBrowserInfo" class="alert alert-info border-info-subtle rounded-4 p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3 bg-info-subtle shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; flex-shrink: 0;">
                        <i class="bi bi-display fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">Gunakan Aplikasi Resmi CBT Exam Browser</div>
                        <div class="small text-muted">Pelaksanaan ujian lebih stabil, bebas gangguan notifikasi, dan terproteksi aman dalam mode Kiosk.</div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= base_url('downloads/TKA-Exam-Browser-Setup-2.0.1-x64.exe') ?>"
                       class="btn btn-primary rounded-pill px-3.5 btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-download"></i> Installer (.exe)
                    </a>
                    <a href="<?= base_url('downloads/TKA-Exam-Browser-Portable-2.0.1-win-x64.zip') ?>"
                       class="btn btn-outline-primary rounded-pill px-3.5 btn-sm fw-bold shadow-sm d-inline-flex align-items-center gap-2 bg-white">
                        <i class="bi bi-file-earmark-zip"></i> Portable (.zip)
                    </a>
                </div>
            </div>

            <!-- Badge Status Terkunci jika berada di Exam Browser -->
            <div id="badgeExamBrowserActive" class="d-none alert alert-success border-success-subtle rounded-4 p-2 px-3 mb-4 d-flex align-items-center justify-content-between bg-success-subtle shadow-sm">
                <div class="d-flex align-items-center gap-2 text-success fw-bold small">
                    <i class="bi bi-shield-fill-check fs-5"></i>
                    <span>Lingkungan Ujian Terproteksi: <strong>CBT Exam Browser Kiosk Aktif</strong></span>
                </div>
                <span class="badge bg-success rounded-pill px-2 py-1" style="font-size: .68rem;">Terverifikasi</span>
            </div>

            <script>
                (function() {
                    const isEb = !!(
                        window.isExamBrowser ||
                        window.TKA_EXAM_BROWSER ||
                        window.EXAM_BROWSER ||
                        window.exambrowser ||
                        window.Exambro ||
                        window.Android ||
                        (typeof navigator !== 'undefined' && navigator.userAgent && (
                            navigator.userAgent.indexOf('TKAExamBrowser') !== -1 ||
                            navigator.userAgent.indexOf('TKA-Exam') !== -1 ||
                            navigator.userAgent.indexOf('ExamBrowser') !== -1 ||
                            navigator.userAgent.indexOf('Exambro') !== -1 ||
                            navigator.userAgent.indexOf('CandyExam') !== -1 ||
                            navigator.userAgent.indexOf('Electron') !== -1
                        ))
                    );
                    if (isEb) {
                        document.getElementById('bannerExamBrowserInfo')?.classList.add('d-none');
                        document.getElementById('badgeExamBrowserActive')?.classList.remove('d-none');
                    }
                })();
            </script>

            <!-- ══ NAVIGATION MENU HEADER ══ -->
            <div class="d-flex align-items-center justify-content-between mb-4 mt-4 px-1">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary" style="width: 4px; height: 22px; border-radius: 4px;"></div>
                        <h4 class="fw-bold text-dark mb-0 text-uppercase" style="letter-spacing: 0.5px; font-size: 1.15rem;">Menu Navigasi Utama</h4>
                    </div>
                    <small class="text-muted ms-3">Pilih modul pembelajaran & ujian yang ingin Anda akses</small>
                </div>
            </div>

            <?php
            // Label jenis ujian aktif (fallback bila belum ada data).
            $jenisUjianLabel = (isset($jenis_ujian_aktif->nama_jenis) && trim((string) $jenis_ujian_aktif->nama_jenis) !== '')
                ? $jenis_ujian_aktif->nama_jenis
                : 'Ujian Berbasis Komputer';

            // Konfigurasi kartu menu (tanpa footer card).
            $menuCards = [
                [
                    'url'    => base_url('cbt/absensi'),
                    'icon'   => 'assets/app/img/absen.png',
                    'title'  => 'ABSENSI',
                    'desc'   => 'Kehadiran harian, izin, dan rekap presensi siswa.',
                    'accent' => 'blue',
                ],
                [
                    'url'    => base_url('cbt'),
                    'icon'   => 'assets/app/img/asesmen.png',
                    'title'  => 'ASESMEN CBT',
                    'desc'   => $jenisUjianLabel,
                    'accent' => 'pink',
                ],
                [
                    'url'    => base_url('kbm/hasil'),
                    'icon'   => 'assets/app/img/hasil.png',
                    'title'  => 'DAFTAR NILAI',
                    'desc'   => 'Laporan nilai, riwayat ujian, dan evaluasi belajar.',
                    'accent' => 'teal',
                ],
                [
                    'url'    => base_url('cbtsiswa/praktik'),
                    'icon'   => 'assets/app/img/praktik.png',
                    'title'  => 'PRAKTIKUM',
                    'desc'   => 'Laboratorium digital dan materi praktik-terapan.',
                    'accent' => 'amber',
                ],
            ];
            ?>

            <!-- ══ 4-MENU GRID (2 KOLOM MOBILE · 4 KOLOM DESKTOP) ══ -->
            <div class="siswa-dashboard__menu-grid mb-5">
                <?php foreach ($menuCards as $idx => $card): ?>
                    <a href="<?= esc($card['url']) ?>"
                       class="siswa-dashboard__menu-card siswa-dashboard__menu-card--<?= esc($card['accent']) ?> menu-col-enter"
                       style="animation-delay: <?= number_format($idx * 0.07, 2, '.', '') ?>s;"
                       aria-label="Buka menu <?= esc($card['title']) ?>">
                        <div class="siswa-dashboard__menu-head">
                            <span class="siswa-dashboard__menu-icon">
                                <img src="<?= base_url($card['icon']) ?>" alt="" class="siswa-dashboard__menu-icon-img" loading="lazy" width="32" height="32">
                            </span>
                            <span class="siswa-dashboard__menu-arrow" aria-hidden="true">
                                <i class="bi bi-arrow-up-right"></i>
                            </span>
                        </div>
                        <div class="siswa-dashboard__menu-content">
                            <h3 class="siswa-dashboard__menu-title"><?= esc($card['title']) ?></h3>
                            <p class="siswa-dashboard__menu-description"><?= esc($card['desc']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>

<!-- ══ DESKTOP FOOTER ══ -->
<footer class="d-none d-lg-block mt-4 mb-4">
    <div class="container-fluid py-3">
        <div class="text-center">
            <p class="text-muted small fw-medium mb-0">
                &copy; <?= date('Y') ?> <b>CBT</b> by IKASMEDIA &bull; All Rights Reserved.
            </p>
        </div>
    </div>
</footer>

<!-- ══ MOBILE BOTTOM NAV ══ -->
<nav class="position-fixed bottom-0 start-0 w-100 d-flex d-lg-none bg-white border-top shadow-lg" style="padding-bottom: env(safe-area-inset-bottom); z-index: 1040;">
    <div class="d-flex justify-content-around align-items-center w-100 py-2 px-1">
        <a href="<?= base_url('dashboard') ?>" id="bn-home" class="text-decoration-none d-flex flex-column align-items-center text-primary bg-primary bg-opacity-10 py-1.5 px-3 rounded-pill transition-all" style="min-width: 60px;">
            <i class="bi bi-house-door-fill fs-5 mb-0.5"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Home</small>
        </a>

        <a href="<?= base_url('cbt') ?>" id="bn-cbt" class="text-decoration-none d-flex flex-column align-items-center text-secondary py-1.5 px-3 rounded-pill transition-all" style="min-width: 60px;">
            <i class="bi bi-pencil-square fs-5 mb-0.5"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Ujian</small>
        </a>

        <button onclick="logout()" class="btn btn-link text-decoration-none d-flex flex-column align-items-center text-danger py-1.5 px-3 rounded-pill transition-all" style="min-width: 60px; box-shadow: none;">
            <i class="bi bi-box-arrow-right fs-5 mb-0.5"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Keluar</small>
        </button>
    </div>
</nav>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Live Digital Clock
    !function tick(){
        const n = new Date();
        const c = document.getElementById('digital-clock');
        const d = document.getElementById('current-date');
        if (c) c.textContent = n.toLocaleTimeString('id-ID', {hour12:false, hour:'2-digit', minute:'2-digit', second:'2-digit'}).replace(/\./g, ':');
        if (d) d.textContent = n.toLocaleDateString('id-ID', {weekday:'short', day:'2-digit', month:'short', year:'numeric'}).toUpperCase();
        setTimeout(tick, 1000);
    }();

    // Mobile Bottom Nav Active state
    !function(){
        const p = location.pathname;
        const m = { home: /dashboard/, cbt: /\/cbt/ };
        Object.entries(m).forEach(([k, r]) => {
            const e = document.getElementById('bn-' + k);
            if (e && r.test(p)) {
                document.querySelectorAll('[id^=bn-]').forEach(x => {
                    x.classList.remove('text-primary', 'bg-primary', 'bg-opacity-10');
                    x.classList.add('text-secondary');
                });
                e.classList.remove('text-secondary');
                e.classList.add('text-primary', 'bg-primary', 'bg-opacity-10');
            }
        });
    }();
</script>
<?= $this->endSection() ?>
