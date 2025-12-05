<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-teal my-shadow">
	<!-- Brand Logo -->
	<a href="<?= base_url();?>" class="brand-link bg-dark border-0">
        <?php $logo_app = $setting->logo_kiri == null ? base_url().'assets/img/favicon.png' : base_url().$setting->logo_kiri; ?>
		<center><img src="<?= $logo_app ?>" alt="App Logo" class="logo" height="40px"></center>
	</a>

    <!-- Sidebar -->
	<div class="sidebar">
		<div class="header border-0 mt-4 mb-3 text-center">
			<a href="#" class="d-block" style="line-height: 1"><?=$setting->nama_aplikasi?><br>
			<small class="text-muted">
                Assesmen Application
            </small></a>
		</div>

        <!-- Sidebar Menu -->
        <nav class="mt-2 mb-5">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent"
                id="tree-menus" data-widget="treeview" role="menu" data-accordion="false">
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<script>
    const page = '<?= $this->uri->segment(1)?>';
    const jabatan = '4'; // Panitia role
    const pageact = '<?= $this->uri->segment(2); ?>';
    const menus = [
        {
            'header': 'MAIN MENU', 'cbt': '1',
            'menu': [
                {'name': 'Beranda', 'link': 'dashboard', 'icon': 'fas fa-desktop', 'cbt': '1'},
                {'name': 'Profile', 'link': 'panitia', 'icon': 'fas fa-user', 'cbt': '1'},
                {'name': 'Data Siswa', 'link': 'usersiswa', 'icon': 'fas fa-users', 'cbt': '1'},
                {'name': 'Pengumuman', 'link': 'pengumuman', 'icon': 'fas fa-bullhorn', 'cbt': '1'},
                {
                    'name': 'Manajemen Ujian', 'icon': 'fas fa-edit', 'cbt': '1',
                    'submenu': [
                        {'name': 'Bank Soal', 'link': 'cbtbanksoal', 'icon': 'fas fa-folder-open'},
                        {'name': 'Jadwal', 'link': 'cbtjadwal', 'icon': 'fas fa-calendar-alt'},
                        {'name': 'Status Peserta', 'link': 'cbtstatus', 'icon': 'fas fa-user-clock'},
                    ]
                },
                {
                    'name': 'Administrasi', 'icon': 'fas fa-file-alt', 'cbt': '1',
                    'submenu': [
                        {'name': 'Berita Acara', 'link': 'cbtcetak/beritaAcara', 'icon': 'fas fa-file-signature'},
                        {'name': 'Pakta Integritas', 'link': 'cbtcetak/paktaIntegritas', 'icon': 'fas fa-file-contract'},
                        {'name': 'Daftar Hadir', 'link': 'cbtcetak/absenPeserta', 'icon': 'fas fa-clipboard-list'},
                        {'name': 'Kartu Peserta', 'link': 'cbtcetak/kartuPeserta', 'icon': 'fas fa-id-card'},
                        {'name': 'Kartu Meja', 'link': 'cbtcetak/kartuMeja', 'icon': 'fas fa-print'},
                    ]
                },
                {'name': 'Nilai Ujian', 'link': 'cbtnilai', 'icon': 'fas fa-chart-bar', 'cbt': '1'},
                {'name': 'Analisis', 'link': 'cbtanalisis', 'icon': 'fas fa-chart-line', 'cbt': '1'},
                {'name': 'Arsip Laporan', 'link': 'laporan', 'icon': 'fas fa-file-pdf', 'cbt': '1'},
            ]
        },
        {'name': 'LOGOUT', 'link': '', 'icon': 'fas fa-sign-out-alt', 'cbt': '1'},
    ];

    const isLogin = localStorage.getItem('garudaCBT.login')
    const isCbtMode = isLogin ? isLogin === '1' : false
    let htmlMenu = '';
    menus.forEach(function (header) {
        if (isCbtMode && header.cbt === '0') {
            return
        }
        if (header.header) {
            htmlMenu += `<li class="nav-header">${header.header}</li>`;
            header.menu.forEach(function (menu) {
                if (isCbtMode && menu.cbt === '0') {
                    return
                }
                if (menu.submenu) {
                    var subs = menu.submenu.map(function(item) {
                        if (item['link'].includes('/')) {
                            return item['link'].split('/')[1]
                        } else return item['link'];
                    });
                    htmlMenu += `<li class="nav-item has-treeview ${subs.includes(pageact) || subs.includes(page) ? "menu-open" : ""}">
                    <a href="#" class="nav-link ${subs.includes(pageact) || subs.includes(page) ? "active" : ""}">
                        <i class="nav-icon ${menu.icon}"></i>
                        <p>${menu.name}<i class="fas fa-angle-left right"></i></p>
                    </a><ul class="nav nav-treeview">`;
                    menu.submenu.forEach(function (sub) {
                        htmlMenu += `<li class="nav-item">
                            <a href="${base_url + sub.link}"
                               class="nav-link ${page+'/'+pageact === sub.link || page === sub.link ? "active" : ""}">
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