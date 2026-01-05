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
    const jabatan = '4'; // Panitia role
    const pageact = '<?= $this->uri->segment(2); ?>';
    const menus = [
        {
            'header': 'MAIN MENU',
            'menu': [
                { 'name': 'Beranda', 'link': 'dashboard', 'icon': 'bi bi-pc-display' },
                { 'name': 'Profile', 'link': 'panitia', 'icon': 'bi bi-person-circle' },
                { 'name': 'Data Siswa (Reset Login)', 'link': 'usersiswa', 'icon': 'bi bi-people-fill' },
                { 'name': 'Pengumuman', 'link': 'pengumuman', 'icon': 'bi bi-megaphone-fill' },
                { 'name': 'Bank Soal', 'link': 'cbtbanksoal', 'icon': 'bi bi-folder2-open' },
                { 'name': 'Jadwal', 'link': 'cbtjadwal', 'icon': 'bi bi-calendar3' },
                { 'name': 'Monitoring Ujian', 'link': 'cbtstatus', 'icon': 'bi bi-person-workspace' },
                { 'name': 'Administrasi Ujian', 'link': 'cbtcetak', 'icon': 'bi bi-file-earmark-text' },
                { 'name': 'Nilai Ujian', 'link': 'cbtnilai', 'icon': 'bi bi-bar-chart-fill' },
                { 'name': 'Arsip Laporan', 'link': 'laporan', 'icon': 'bi bi-file-earmark-pdf-fill' },
            ]
        },
        { 'name': 'LOGOUT', 'link': '', 'icon': 'bi bi-box-arrow-right' },
    ];

    let htmlMenu = '';
    menus.forEach(function (header) {
        if (header.header) {
            htmlMenu += `<li class="nav-header">${header.header}</li>`;
            header.menu.forEach(function (menu) {
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

    // Initialize TreeView for sidebar menu
    // $('#tree-menus').Treeview('init')
</script>