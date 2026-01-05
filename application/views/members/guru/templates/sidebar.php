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
    const jabatan = '<?= isset($guru->id_jabatan) ? $guru->id_jabatan : "0" ?>';
    const pageact = '<?= $this->uri->segment(2); ?>';
    const menus = [
        {
            'header': 'HOME', 'cbt': '1',
            'menu': [
                { 'name': 'Beranda', 'link': 'dashboard', 'icon': 'bi bi-pc-display', 'cbt': '1' },
                { 'name': 'Profile', 'link': 'guru', 'icon': 'bi bi-person-circle', 'cbt': '1' },
                { 'name': 'Pengumuman', 'link': 'pengumuman', 'icon': 'bi bi-megaphone-fill', 'cbt': '1' },
                {
                    'name': 'Wali Kelas', 'icon': 'bi bi-pie-chart-fill', 'cbt': '1', 'wali': true,
                    'submenu': [
                        { 'name': 'Siswa', 'link': "walisiswa", 'icon': 'bi bi-people-fill' },
                        { 'name': 'Struktur', 'link': "walistruktur", 'icon': 'bi bi-circle' },
                        { 'name': 'Catatan', 'link': "walicatatan", 'icon': 'bi bi-pencil' }
                    ]
                },

                {
                    'name': 'Manajemen Ujian', 'icon': 'bi bi-mortarboard-fill', 'cbt': '1',
                    'submenu': [
                        { 'name': "Nomor Peserta", 'link': "cbtnomorpeserta", 'icon': 'bi bi-card-heading' },
                        { 'name': "Atur Ruang/Sesi", 'link': "cbtsesisiswa", 'icon': 'bi bi-person-workspace' },
                        { 'name': "Bank Soal", 'link': "cbtbanksoal", 'icon': 'bi bi-folder2-open' },
                        { 'name': "Jadwal", 'link': "cbtjadwal", 'icon': 'bi bi-calendar3' },
                        { 'name': "Alokasi Waktu", 'link': "cbtalokasi", 'icon': 'bi bi-clock' },
                        { 'name': 'Cetak', 'link': "cbtcetak", 'icon': 'bi bi-printer-fill' },
                        { 'name': 'Status Siswa', 'link': "cbtstatus", 'icon': 'bi bi-person-workspace' },
                        { 'name': 'Hasil Ujian', 'link': "cbtnilai", 'icon': 'bi bi-file-earmark-text' },
                        { 'name': 'Rekap Nilai', 'link': "cbtrekap", 'icon': 'bi bi-trophy-fill' }, // sementara dihilangkan
                    ]
                },
                {
                    'name': 'Log Aktivitas', 'link': 'datalogs', 'icon': 'bi bi-clock-history', 'cbt': '1'
                },
                {
                    'name': 'Generate RPM (AI)', 'link': 'rpm', 'icon': 'bi bi-magic', 'cbt': '1'
                },
            ]
        },
        {
            'header': 'PENILAIAN', 'cbt': '1',
            'menu': [
                {
                    'name': 'Data Rapor', 'icon': 'bi bi-pie-chart-fill', 'cbt': '1',
                    'submenu': [
                        { 'name': 'KKM dan Bobot', 'link': 'rapor/raporkkm', 'icon': 'bi bi-percent', 'cbt': '1' },
                        { 'name': 'Indikator Nilai', 'link': 'rapor/raporkikd', 'icon': 'bi bi-book-fill', 'cbt': '1' },
                    ]
                },
                { 'name': 'Input Nilai', 'link': 'rapor/rapornilai', 'icon': 'bi bi-people-fill', 'cbt': '1' },
                { 'name': 'Periksa Nilai', 'link': 'rapor/rapornilaiguru', 'icon': 'bi bi-people-fill', 'cbt': '1', 'wali': true },
                {
                    'name': 'Input Wali Kelas', 'icon': 'bi bi-pie-chart-fill', 'cbt': '0', 'wali': true,
                    'submenu': [
                        { 'name': 'Sikap Spiritual', 'link': 'rapor/raporspiritual', 'icon': 'bi bi-book-fill', 'cbt': '1' },
                        { 'name': 'Sikap Sosial', 'link': 'rapor/raporsosial', 'icon': 'bi bi-book-fill', 'cbt': '1' },
                        { 'name': 'Prestasi', 'link': 'rapor/raporprestasi', 'icon': 'bi bi-people-fill', 'cbt': '1' },
                        { 'name': 'Kehadiran', 'link': 'rapor/raporcatatan', 'icon': 'bi bi-people-fill', 'cbt': '1' },
                        { 'name': 'Kenaikan', 'link': 'rapor/rapornaik', 'icon': 'bi bi-people-fill', 'cbt': '1' },
                    ]
                },
            ]
        },
        {
            'header': 'CETAK', 'cbt': '1', 'wali': true,
            'menu': [
                { 'name': 'Rapor PTS', 'link': 'rapor/cetakpts', 'icon': 'bi bi-book-fill', 'cbt': '1' },
                { 'name': 'Rapor Akhir', 'link': 'rapor/cetakakhir', 'icon': 'bi bi-book-fill', 'cbt': '1' },
                { 'name': 'Ledger', 'link': 'rapor/cetakleger', 'icon': 'bi bi-people-fill', 'cbt': '1' },
                { 'name': 'DKN', 'link': 'rapor/dkn', 'icon': 'bi bi-people-fill', 'cbt': '1' },
            ]
        },
        {
            'header': 'ARSIP', 'cbt': '1', 'wali': true,
            'menu': [
                { 'name': 'Arsip Rapor', 'link': 'bukurapor', 'icon': 'bi bi-bank', 'cbt': '1', },
            ]
        },
        { 'name': 'LOGOUT', 'link': '', 'icon': 'bi bi-box-arrow-right', 'cbt': '1' },
    ];

    let htmlMenu = '';
    menus.forEach(function (header) {
        if (jabatan !== '4' && header.wali) {
            return
        }
        if (header.header) {
            htmlMenu += `<li class="nav-header">${header.header}</li>`;
            header.menu.forEach(function (menu) {
                if (jabatan !== '4' && menu.wali) {
                    return
                }
                if (menu.submenu) {
                    var subs = menu.submenu.map(function (item) {
                        if (item['link'].includes('/')) {
                            return item['link'].split('/')[1]
                        } else return item['link'];
                    });
                    htmlMenu += `<li class="nav-item has-treeview ${subs.includes(pageact) || subs.includes(page) ? "menu-open" : ""}">
                    <a href="#" class="nav-link ${subs.includes(pageact) || subs.includes(page) ? "active" : ""}">
                        <i class="nav-icon ${menu.icon}"></i>
                        <p>${menu.name}<i class="bi bi-chevron-left right"></i></p>
                    </a><ul class="nav nav-treeview">`;
                    menu.submenu.forEach(function (sub) {
                        htmlMenu += `<li class="nav-item">
                            <a href="${base_url + sub.link}"
                               class="nav-link ${page + '/' + pageact === sub.link || page === sub.link ? "active" : ""}">
                                <i class="${sub.icon} nav-icon"></i>
                                <p>${sub.name}</p>
                            </a>
                        </li>`;
                    })
                    htmlMenu += `</ul></li>`;
                } else {
                    htmlMenu += `<li class="nav-item"><a href="${base_url + menu.link}"
                       class="nav-link ${page === menu.link ? "active" : ""}">
                        <i class="nav-icon ${menu.icon}"></i>
                        <p>${menu.name}</p>
                    </a></li>`
                }
            })
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