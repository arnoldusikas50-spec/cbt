<!-- Main Sidebar Container -->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Brand Logo -->
    <div class="sidebar-brand">
        <a href="<?= base_url(); ?>" class="brand-link">
            <?php $logo_app = $setting->logo_kiri == null ? base_url() . 'assets/img/favicon.png' : base_url() . $setting->logo_kiri; ?>
            <img src="<?= $logo_app ?>" alt="App Logo" class="brand-image opacity-75 shadow" style="max-height: 40px;">
            <span class="brand-text fw-light"></span>
        </a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar-wrapper">
        <div class="header border-0 mt-4 mb-3 text-center">
            <a class="d-block text-decoration-none"
                style="line-height: 1; font-size: 14px; color: #cbd5e1;"><?= $setting->nama_aplikasi ?><br>
                <small class="text-muted">
                    Assesmen Application
                </small></a>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2 mb-5">
            <ul class="nav nav-sidebar sidebar-menu flex-column" id="tree-menus" data-lte-toggle="treeview" role="menu"
                data-accordion="false">
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<script>
    const page = '<?= $this->uri->segment(1) ?>';
    const pageact = '<?= $this->uri->segment(2); ?>';
    const isSchoolAdmin = <?= $this->session->userdata('school_uuid') ? 'true' : 'false' ?>;

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
                {
                    'name': 'Pengumuman', 'link': 'pengumuman', 'icon': 'bi bi-megaphone-fill', 'cbt': '1'
                },
                {
                    'name': 'Log Aktivitas', 'link': 'datalogs', 'icon': 'bi bi-clock-history', 'cbt': '1'
                },
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
                        { 'name': 'Lulus/Remedial', 'link': "cbtrekap/export_all", 'icon': 'bi bi-people-fill' },
                        { 'name': 'Arsip Laporan', 'link': "laporan", 'icon': 'bi bi-file-earmark-pdf-fill' },
                    ]
                },
                {
                    'name': 'Generate RPM (AI)', 'link': 'rpm', 'icon': 'bi bi-magic', 'cbt': '1', 'exclude_school': false
                },
            ]
        },
        {
            'header': 'RAPOR', 'cbt': '1',
            'menu': [
                { 'name': 'Setting Rapor', 'link': 'rapor', 'icon': 'bi bi-book-fill', 'cbt': '1' },
                { 'name': 'Kumpulan Nilai Rapor', 'link': 'bukurapor', 'icon': 'bi bi-book-fill', 'cbt': '1' },
                { 'name': 'Alumni/Drop Out', 'link': 'dataalumni', 'icon': 'bi bi-people-fill', 'cbt': '1' },
            ]
        },
        {
            'header': 'PENGATURAN', 'cbt': '1',
            'menu': [
                { 'name': 'Profile Sekolah', 'link': isSchoolAdmin ? 'profilsekolah' : 'sekolah', 'icon': 'bi bi-bank', 'cbt': '1', },
                {
                    'name': 'Data User', 'icon': 'bi bi-person-gear', 'cbt': '1',
                    'submenu': [
                        { 'name': 'Administrator', 'link': "useradmin", 'icon': 'bi bi-gear-fill', 'exclude_school': true },
                        { 'name': 'Guru', 'link': "userguru", 'icon': 'bi bi-person-video3' },
                        { 'name': 'Panitia', 'link': "userpanitia", 'icon': 'bi bi-shield-lock-fill' },
                        { 'name': 'Siswa', 'link': "usersiswa", 'icon': 'bi bi-people-fill' }
                    ]
                },
                {
                    'name': 'Database', 'icon': 'bi bi-database-gear', 'cbt': '1', 'exclude_school': true,
                    'submenu': [
                        { 'name': 'Backup', 'link': "dbmanager", 'icon': 'bi bi-database' },
                        { 'name': 'Data Manager', 'link': "dbclear", 'icon': 'bi bi-database-dash' },
                        { 'name': 'Image Converter', 'link': "dbconvert", 'icon': 'bi bi-images' },
                        { 'name': 'Cleanup Images', 'link': "cbtbanksoal/cleanup_images", 'icon': 'bi bi-trash' },
                    ]
                },
                {
                    'name': 'Sinkronisasi', 'icon': 'bi bi-cloud-upload', 'cbt': '1', 'exclude_school': true,
                    'submenu': [
                        { 'name': 'Dashboard Sync', 'link': "sync", 'icon': 'bi bi-arrow-repeat' },
                        { 'name': 'Konfigurasi', 'link': "sync/config", 'icon': 'bi bi-gear' },
                        { 'name': 'Kelola Sekolah', 'link': "sync/schools", 'icon': 'bi bi-building' },
                    ]
                },

            ]
        },
        { 'name': 'LOGOUT', 'link': 'pengumuman', 'icon': 'bi bi-box-arrow-right', 'cbt': '1' },
    ];

    let htmlMenu = '';
    menus.forEach(function (header) {
        if (header.header) {
            // Check header level permission (optional)

            let headerHTML = `<li class="nav-header">${header.header}</li>`;
            let itemsHTML = '';
            let hasVisibleItems = false;

            header.menu.forEach(function (menu) {
                // Skip if exclude_school is true and user isSchoolAdmin
                if (isSchoolAdmin && menu.exclude_school) return;

                if (menu.submenu) {
                    // Filter Submenu
                    let visibleSubmenus = menu.submenu.filter(item => !(isSchoolAdmin && item.exclude_school));

                    if (visibleSubmenus.length > 0) {
                        hasVisibleItems = true;
                        var subs = visibleSubmenus.map(function (item) {
                            if (item['link'].includes('/')) {
                                return item['link'].split('/')[1]
                            } else return item['link'];
                        });
                        itemsHTML += `<li class="nav-item has-treeview ${subs.includes(pageact) || subs.includes(page) ? "menu-open" : ""}">
                        <a href="#" class="nav-link ${subs.includes(pageact) || subs.includes(page) ? "active" : ""}">
                            <i class="nav-icon ${menu.icon}"></i>
                            <p>${menu.name}<i class="bi bi-chevron-left right"></i></p>
                        </a><ul class="nav nav-treeview">`;
                        visibleSubmenus.forEach(function (sub) {
                            let curUrl = page + (pageact ? '/' + pageact : '');
                            itemsHTML += `<li class="nav-item">
                                <a href="${base_url + sub.link}"
                                class="nav-link ${curUrl === sub.link ? "active" : ""}">
                                    <i class="${sub.icon} nav-icon"></i>
                                    <p>${sub.name}</p>
                                </a>
                            </li>`;
                        })
                        itemsHTML += `</ul></li>`;
                    }
                } else {
                    hasVisibleItems = true;
                    itemsHTML += `<li class="nav-item"><a href="${base_url + menu.link}"
                    class="nav-link ${page === menu.link ? "active" : ""}">
                        <i class="nav-icon ${menu.icon}"></i>
                        <p>${menu.name}</p>
                    </a></li>`
                }
            })

            if (hasVisibleItems) {
                htmlMenu += headerHTML + itemsHTML;
            }

        } else {
            htmlMenu += `<hr /><li class="nav-item">
                    <a href="#" onclick="logout()" class="nav-link">
                        <i class="${header.icon} nav-icon"></i>
                        <p>${header.name}</p>
                    </a>
                </li>`;
        }
    })
    $('#tree-menus').html(htmlMenu)
</script>