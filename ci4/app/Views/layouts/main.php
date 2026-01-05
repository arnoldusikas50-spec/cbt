<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>
        <?= esc($judul ?? 'Dashboard') ?> -
        <?= esc($setting->nama_sekolah ?? 'CBT AKM') ?>
    </title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (replace Font Awesome) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --success: #48bb78;
            --danger: #f56565;
            --warning: #ed8936;
            --info: #4299e1;
            --dark: #1a202c;
            --light: #f7fafc;
            --sidebar-width: 260px;
            --header-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--dark) 0%, #2d3748 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }

        .sidebar-logo i {
            font-size: 1.8rem;
            color: white;
        }

        .sidebar-title {
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .sidebar-subtitle {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-label {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border-left-color: var(--primary);
        }

        .menu-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Header */
        .top-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .page-title p {
            font-size: 0.85rem;
            color: #718096;
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .user-menu:hover {
            background: #f7fafc;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark);
        }

        .user-role {
            font-size: 0.75rem;
            color: #718096;
        }

        /* Content Area */
        .content-wrapper {
            padding: 30px;
        }

        /* New app-main alias for updated views */
        .app-main {
            padding: 30px;
        }

        /* Info Boxes */
        .info-box {
            background: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .info-icon.blue {
            background: linear-gradient(135deg, #4299e1 0%, #667eea 100%);
        }

        .info-icon.cyan {
            background: linear-gradient(135deg, #0bc5ea 0%, #00b5d8 100%);
        }

        .info-icon.teal {
            background: linear-gradient(135deg, #38b2ac 0%, #319795 100%);
        }

        .info-icon.fuchsia {
            background: linear-gradient(135deg, #d53f8c 0%, #b83280 100%);
        }

        .info-icon.success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        }

        .info-icon.indigo {
            background: linear-gradient(135deg, #667eea 0%, #5a67d8 100%);
        }

        .info-icon.maroon {
            background: linear-gradient(135deg, #c53030 0%, #9b2c2c 100%);
        }

        .info-icon.green {
            background: linear-gradient(135deg, #48bb78 0%, #2f855a 100%);
        }

        .info-icon.info {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        }

        .info-details h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .info-details p {
            font-size: 0.85rem;
            color: #718096;
            margin: 0;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px;
        }

        .card-header h5 {
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Section Title */
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary);
        }

        /* Version Badge */
        .version-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            z-index: 999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="sidebar-title">
                <?= esc($setting->nama_sekolah ?? 'CBT AKM') ?>
            </div>
            <div class="sidebar-subtitle">E-Learning & CBT</div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-label">Menu Utama</div>
            <a href="<?= site_url('dashboard') ?>" class="menu-item active">
                <i class="bi bi-house"></i>
                <span>Dashboard</span>
            </a>

            <?php if (session()->get('role') === '1'): ?>
                <div class="menu-label">Master Data</div>
                <a href="<?= site_url('datasiswa') ?>" class="menu-item">
                    <i class="bi bi-people"></i>
                    <span>Data Siswa</span>
                </a>
                <a href="<?= site_url('dataguru') ?>" class="menu-item">
                    <i class="bi bi-person-badge"></i>
                    <span>Data Guru</span>
                </a>
                <a href="<?= site_url('datakelas') ?>" class="menu-item">
                    <i class="bi bi-door-open"></i>
                    <span>Data Kelas</span>
                </a>
                <a href="<?= site_url('datamapel') ?>" class="menu-item">
                    <i class="bi bi-book"></i>
                    <span>Data Mapel</span>
                </a>
            <?php endif; ?>

            <div class="menu-label">CBT</div>
            <a href="<?= site_url('cbtbanksoal') ?>" class="menu-item">
                <i class="bi bi-database"></i>
                <span>Bank Soal</span>
            </a>
            <a href="<?= site_url('cbtjadwal') ?>" class="menu-item">
                <i class="bi bi-calendar-event"></i>
                <span>Jadwal Ujian</span>
            </a>
            <a href="<?= site_url('cbtstatus') ?>" class="menu-item">
                <i class="bi bi-monitor"></i>
                <span>Monitoring</span>
            </a>

            <div class="menu-label">Pengaturan</div>
            <a href="<?= site_url('settings') ?>" class="menu-item">
                <i class="bi bi-gear"></i>
                <span>Pengaturan</span>
            </a>
            <a href="<?= site_url('auth/logout') ?>" class="menu-item">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="top-header">
            <div class="page-title">
                <h1>
                    <?= esc($judul ?? 'Dashboard') ?>
                </h1>
                <p>
                    <?= esc($subjudul ?? 'Halaman Utama') ?>
                </p>
            </div>

            <div class="header-actions">
                <div class="dropdown">
                    <div class="user-menu" data-bs-toggle="dropdown">
                        <div class="user-info">
                            <div class="user-name">
                                <?= esc($user->first_name ?? 'User') ?>
                            </div>
                            <div class="user-role">
                                <?= esc(ucfirst(session()->get('role_name') ?? 'Guest')) ?>
                            </div>
                        </div>
                        <div class="user-avatar">
                            <?= strtoupper(substr($user->first_name ?? 'U', 0, 1)) ?>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= site_url('profile') ?>"><i
                                    class="bi bi-person me-2"></i>Profil</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="<?= site_url('auth/logout') ?>"><i
                                    class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="app-main">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <!-- Version Badge -->
    <div class="version-badge">
        <i class="bi bi-code-slash me-1"></i>
        CI4 v
        <?= \CodeIgniter\CodeIgniter::CI_VERSION ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const base_url = '<?= base_url() ?>/';
        let globalToken;
        let adaJadwalUjian;

        function getToken(func) {
            $.ajax({
                url: base_url + "dashboard/checktokenjadwal",
                type: "GET",
                success: function (response) {
                    globalToken = response.token;
                    adaJadwalUjian = response.ada_ujian;
                    if (func && (typeof func == "function")) {
                        func(response);
                    }
                }
            });
        }

        function zeroPad(no) {
            return no < 10 ? '0' + no : no;
        }

        function formatTanggal(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            return date.toLocaleDateString('id-ID', options);
        }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>