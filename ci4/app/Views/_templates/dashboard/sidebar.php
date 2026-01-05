<!-- Main Sidebar Container -->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Brand Logo -->
    <div class="sidebar-brand">
        <a href="<?= site_url('dashboard'); ?>" class="brand-link">
            <?php
            $logo_app = ($setting->logo_kiri == null) ? base_url('assets/img/favicon.png') : base_url('' . $setting->logo_kiri);
            ?>
            <img src="<?= $logo_app ?>" alt="App Logo" class="brand-image opacity-75 shadow" style="max-height: 40px;">
            <span class="brand-text fw-light"></span>
        </a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar-wrapper">
        <div class="header border-0 mt-4 mb-3 text-center">
            <a class="d-block text-decoration-none" style="line-height: 1; font-size: 14px; color: #cbd5e1;">
                <?= esc($setting->nama_sekolah ?? 'CBT AKM') ?><br>
                <small class="text-muted">Assesmen Application</small>
            </a>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2 mb-5">
            <ul class="nav nav-sidebar sidebar-menu flex-column" id="tree-menus" data-lte-toggle="treeview" role="menu"
                data-accordion="false">
                <!-- Dynamic Content by JS -->
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<script>

    const page = '<?= uri_string() ?>';
    const isSchoolAdmin = <?= session()->get('school_uuid') ? 'true' : 'false' ?>;
    const userRole = '<?= session()->get('role') ?>';

    // Simplified logic to filter by role in JS if needed, but original used 'cbt' etc.
    // We will stick to the exact menus from original
    const menus = [
        {
            'header': 'HOME', 'cbt': '1',
            'menu': [
                { 'name': 'Dashboard', 'link': 'dashboard', 'icon': 'bi bi-house-heart-fill', 'cbt': '1' },
                {
                    'name': 'Data Umum', 'icon': 'bi bi-hdd-network', 'cbt': '1',
                    'submenu': [
                        { 'name': "Tahun Pelajaran", 'link': "datatahun", 'icon': 'bi bi-calendar-check' },
                        { 'name': "Mata Pelajaran", 'link': "datamapel", 'icon': 'bi bi-book' },
                        { 'name': "Jurusan", 'link': "datajurusan", 'icon': 'bi bi-diagram-3' },
                        { 'name': "Kelas / Rombel", 'link': "datakelas", 'icon': 'bi bi-building' },
                        { 'name': "Ekstrakurikuler", 'link': "dataekstra", 'icon': 'bi bi-controller' },
                        { 'name': "Siswa", 'link': "datasiswa", 'icon': 'bi bi-people-fill' },
                        { 'name': "Guru", 'link': "dataguru", 'icon': 'bi bi-person-video3' }
                    ]
                },
                {
                    'name': 'Data Ujian', 'icon': 'bi bi-mortarboard-fill', 'cbt': '1',
                    'submenu': [
                        { 'name': "Jenis Ujian", 'link': "cbtjenis", 'icon': 'bi bi-diagram-2' },
                        { 'name': "Sesi", 'link': "cbtsesi", 'icon': 'bi bi-clock' },
                        { 'name': "Ruang", 'link': "cbtruang", 'icon': 'bi bi-door-open-fill' },
                        { 'name': "Nomor Peserta", 'link': "cbtnomorpeserta", 'icon': 'bi bi-card-heading' },
                        { 'name': "Atur Ruang/Sesi", 'link': "cbtsesisiswa", 'icon': 'bi bi-person-workspace' },
                        { 'name': "Bank Soal", 'link': "cbtbanksoal", 'icon': 'bi bi-folder2-open' },
                        { 'name': "Jadwal", 'link': "cbtjadwal", 'icon': 'bi bi-calendar3' },
                        { 'name': "Alokasi Waktu", 'link': "cbtalokasi", 'icon': 'bi bi-clock' },
                        { 'name': "Pengawas", 'link': "cbtpengawas", 'icon': 'bi bi-briefcase-fill' },
                        { 'name': "Token", 'link': "cbttoken", 'icon': 'bi bi-key-fill' }
                    ]
                },
                { 'name': 'Pengumuman', 'link': 'pengumuman', 'icon': 'bi bi-megaphone-fill', 'cbt': '1' },
                { 'name': 'Log Aktivitas', 'link': 'datalogs', 'icon': 'bi bi-clock-history', 'cbt': '1' },
            ]
        },
        {
            'header': 'PELAKSANAAN', 'cbt': '1',
            'menu': [
                {
                    'name': 'Manajemen Ujian', 'icon': 'bi bi-mortarboard', 'cbt': '1',
                    'submenu': [
                        { 'name': 'Cetak', 'link': "cbtcetak", 'icon': 'bi bi-printer-fill' },
                        { 'name': 'Aktivasi Peserta', 'link': "cbtactivate", 'icon': 'bi bi-person-gear' },
                        { 'name': 'Status Siswa', 'link': "cbtstatus", 'icon': 'bi bi-person-check' },
                        { 'name': 'Hasil Ujian', 'link': "cbtnilai", 'icon': 'bi bi-file-earmark-text' },
                        { 'name': 'Rekap Nilai', 'link': "cbtrekap", 'icon': 'bi bi-trophy-fill' },
                        { 'name': 'Arsip Laporan', 'link': "laporan", 'icon': 'bi bi-file-earmark-pdf-fill' },
                    ]
                }
            ]
        },
        {
            'header': 'RAPOR', 'cbt': '1',
            'menu': [
                { 'name': 'Setting Rapor', 'link': 'rapor', 'icon': 'bi bi-book-fill', 'cbt': '1' },
            ]
        },
        {
            'header': 'PENGATURAN', 'cbt': '1',
            'menu': [
                { 'name': 'Profile Sekolah', 'link': isSchoolAdmin ? 'profilsekolah' : 'settings', 'icon': 'bi bi-bank', 'cbt': '1', },
                {
                    'name': 'Data User', 'icon': 'bi bi-person-gear', 'cbt': '1',
                    'submenu': [
                        { 'name': 'Administrator', 'link': "useradmin", 'icon': 'bi bi-gear-fill', 'exclude_school': true },
                        { 'name': 'Guru', 'link': "userguru", 'icon': 'bi bi-person-video3' },
                        { 'name': 'Panitia', 'link': "userpanitia", 'icon': 'bi bi-shield-lock-fill' },
                        { 'name': 'Siswa', 'link': "usersiswa", 'icon': 'bi bi-people-fill' }
                    ]
                }
            ]
        }
    ];

    let htmlMenu = '';
    menus.forEach(function (header) {
        if (header.header) {
            let hasVisibleItems = false;
            let itemsHTML = '';

            header.menu.forEach(function (menu) {
                if (isSchoolAdmin && menu.exclude_school) return;

                // Simplified Role Check for CI4 Migration logic
                // In production, this should be more robust
                if (userRole === '2' && menu.name === 'Data Umum') return; // Hide for teacher

                if (menu.submenu) {
                    let visibleSubmenus = menu.submenu.filter(item => !(isSchoolAdmin && item.exclude_school));
                    if (visibleSubmenus.length > 0) {
                        hasVisibleItems = true;
                        let subs = visibleSubmenus.map(item => item.link);
                        let isActive = subs.includes(page);

                        itemsHTML += `<li class="nav-item has-treeview ${isActive ? "menu-open" : ""}">
                        <a href="#" class="nav-link ${isActive ? "active" : ""}">
                            <i class="nav-icon ${menu.icon}"></i>
                            <p>${menu.name}<i class="bi bi-chevron-left right"></i></p>
                        </a><ul class="nav nav-treeview">`;

                        visibleSubmenus.forEach(function (sub) {
                            itemsHTML += `<li class="nav-item">
                                <a href="${base_url + sub.link}" class="nav-link ${page === sub.link ? "active" : ""}">
                                    <i class="${sub.icon} nav-icon"></i>
                                    <p>${sub.name}</p>
                                </a>
                            </li>`;
                        });
                        itemsHTML += `</ul></li>`;
                    }
                } else {
                    hasVisibleItems = true;
                    itemsHTML += `<li class="nav-item"><a href="${base_url + menu.link}" class="nav-link ${page === menu.link ? "active" : ""}">
                        <i class="nav-icon ${menu.icon}"></i>
                        <p>${menu.name}</p>
                    </a></li>`;
                }
            });

            if (hasVisibleItems) {
                htmlMenu += `<li class="nav-header">${header.header}</li>` + itemsHTML;
            }
        }
    });
    $(document).ready(function () {
        $('#tree-menus').html(htmlMenu);
    });
</script>