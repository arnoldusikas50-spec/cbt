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
                <?php
                $page = uri_string();
                $userRole = session()->get('role');
                $isSchoolAdmin = session()->get('school_uuid') ? true : false;
                $isWali = (isset($guru) && (($guru->id_jabatan ?? '') == '4' || !empty($guru->wali_kelas)));
                $userId = session()->get('user_id');

                // Badge pada menu "Kartu Soal" — jumlah kartu yang perlu
                // ditindaklanjuti:
                //   Guru    -> kartu miliknya yang berstatus "Perlu Revisi"
                //   Admin/Panitia -> kartu yang menunggu review ("submitted")
                $kartuBadge = null;
                if ($userId) {
                    try {
                        $db = \Config\Database::connect();
                        if ($db->tableExists('cbt_kartu_soal')) {
                            $badgeBuilder = $db->table('cbt_kartu_soal')->where('deleted_at', null);

                            if ($userRole === '2') {
                                $badgeBuilder->where('workflow_status', 'revisi')->where('created_by', (int) $userId);
                            } else {
                                $badgeBuilder->where('workflow_status', 'submitted');
                            }

                            $badgeCount = (int) $badgeBuilder->countAllResults();
                            if ($badgeCount > 0) {
                                $kartuBadge = [
                                    'text' => (string) $badgeCount,
                                    'class' => $userRole === '2' ? 'bg-warning text-dark' : 'bg-info text-dark',
                                    'title' => $userRole === '2'
                                        ? 'Kartu soal yang perlu diperbaiki'
                                        : 'Kartu soal menunggu review',
                                ];
                            }
                        }
                    } catch (\Throwable $e) {
                        log_message('error', 'Sidebar badge kartu soal: ' . $e->getMessage());
                    }
                }

                // Define Menus
                $menus = [];

                if ($userRole == '1') { // Admin
                    $menus = [
                        [
                            'header' => 'HOME',
                            'menu' => [
                                ['name' => 'Dashboard', 'link' => 'dashboard', 'icon' => 'bi bi-house-heart-fill'],
                                [
                                    'name' => 'Data Umum',
                                    'icon' => 'bi bi-hdd-network',
                                    'submenu' => [
                                        ['name' => "Tahun Pelajaran", 'link' => "datatahun", 'icon' => 'bi bi-calendar-check'],
                                        ['name' => "Mata Pelajaran", 'link' => "datamapel", 'icon' => 'bi bi-book'],
                                        ['name' => "Jurusan", 'link' => "datajurusan", 'icon' => 'bi bi-diagram-3'],
                                        ['name' => "Kelas / Rombel", 'link' => "datakelas", 'icon' => 'bi bi-building'],
                                        ['name' => "Siswa", 'link' => "datasiswa", 'icon' => 'bi bi-people-fill'],
                                        ['name' => "Guru", 'link' => "dataguru", 'icon' => 'bi bi-person-video3']
                                    ]
                                ],
                                [
                                    'name' => 'Data Ujian',
                                    'icon' => 'bi bi-mortarboard-fill',
                                    'submenu' => [
                                        ['name' => "Jenis Ujian", 'link' => "cbtjenis", 'icon' => 'bi bi-diagram-2'],
                                        ['name' => "Sesi", 'link' => "cbtsesi", 'icon' => 'bi bi-clock'],
                                        ['name' => "Ruang", 'link' => "cbtruang", 'icon' => 'bi bi-door-open-fill'],
                                        ['name' => "Nomor Peserta", 'link' => "cbtnomorpeserta", 'icon' => 'bi bi-card-heading'],
                                        ['name' => "Ruang/Sesi", 'link' => "cbtsesisiswa", 'icon' => 'bi bi-person-workspace'],
                                        ['name' => "Tujuan Pembelajaran", 'link' => "guru/tujuan", 'icon' => 'bi bi-journal-plus'],  
['name' => "Kartu Soal", 'link' => "cbtkartusoal", 'icon' => 'bi bi-collection', 'badge' => $kartuBadge],
                                        ['name' => "Bank Soal", 'link' => "cbtbanksoal", 'icon' => 'bi bi-folder2-open'],
                                        ['name' => "Pengelolaan Nilai", 'link' => "guru/nilai", 'icon' => 'bi bi-clipboard2-check']
                                    ]
                                ],
                                ['name' => 'Pengumuman', 'link' => 'pengumuman', 'icon' => 'bi bi-megaphone-fill'],
                                ['name' => 'Log Aktivitas', 'link' => 'datalogs', 'icon' => 'bi bi-clock-history'],
                            ]
                        ],


                        [
                            'header' => 'PELAKSANAAN',
                            'menu' => [
                                [
                                    'name' => 'Manajemen Ujian',
                                    'icon' => 'bi bi-mortarboard',
                                    'submenu' => [
                                        ['name' => "Jadwal Ujian", 'link' => "cbtjadwal", 'icon' => 'bi bi-calendar3'],
                                        ['name' => "Ujian Praktik", 'link' => "cbtpraktik", 'icon' => 'bi bi-laptop'],
                                        ['name' => "Template Rubrik", 'link' => "cbtpraktik/templates", 'icon' => 'bi bi-list-check'],
                                        ['name' => "Alokasi Waktu", 'link' => "cbtalokasi", 'icon' => 'bi bi-clock'],
                                        ['name' => "Pengawas", 'link' => "cbtpengawas", 'icon' => 'bi bi-briefcase-fill'],                                       
                                        ['name' => 'Cetak Adm', 'link' => "cbtcetak", 'icon' => 'bi bi-printer-fill'],
                                        ['name' => 'Aktivasi Peserta', 'link' => "cbtactivate", 'icon' => 'bi bi-person-gear'],
                                        ['name' => 'Status Siswa', 'link' => "cbtstatus", 'icon' => 'bi bi-person-check'],
                                        ['name' => 'Hasil Ujian', 'link' => "cbtnilai", 'icon' => 'bi bi-file-earmark-text'],
                                        ['name' => 'Rekap Nilai', 'link' => "cbtrekap", 'icon' => 'bi bi-trophy-fill'],
                                        ['name' => 'Analisis TP', 'link' => "cbtanalysis", 'icon' => 'bi bi-graph-up-arrow'],
                                        ['name' => 'Arsip Laporan', 'link' => "laporan", 'icon' => 'bi bi-file-earmark-pdf-fill'],
                                    ]
                                ],
                            ]
                        ],



                        [
                            'header' => 'PENGATURAN',
                            'menu' => [
                                ['name' => 'Profile Sekolah', 'link' => $isSchoolAdmin ? 'profilsekolah' : 'settings', 'icon' => 'bi bi-bank'],
                                [
                                    'name' => 'Data User',
                                    'icon' => 'bi bi-person-gear',
                                    'submenu' => [
                                        ['name' => 'Administrator', 'link' => "useradmin", 'icon' => 'bi bi-gear-fill', 'exclude_school' => true],
                                        ['name' => 'Guru', 'link' => "userguru", 'icon' => 'bi bi-person-video3'],
                                        ['name' => 'Panitia', 'link' => "userpanitia", 'icon' => 'bi bi-shield-lock-fill'],
                                        ['name' => 'Siswa', 'link' => "usersiswa", 'icon' => 'bi bi-people-fill']
                                    ]
                                ],
                                [
                                    'name' => 'Database',
                                    'icon' => 'bi bi-database-gear',
                                    'exclude_school' => true,
                                    'submenu' => [
                                        ['name' => 'Backup', 'link' => "dbmanager", 'icon' => 'bi bi-database'],
                                        ['name' => 'Data Manager', 'link' => "dbclear", 'icon' => 'bi bi-database-dash'],
                                        ['name' => 'Image Converter', 'link' => "dbconvert", 'icon' => 'bi bi-images'],
                                        ['name' => 'Cleanup Images', 'link' => "cbtbanksoal/cleanup_images", 'icon' => 'bi bi-trash'],
                                    ]
                                ],

                                ['name' => 'Update System', 'link' => 'update', 'icon' => 'bi bi-cloud-arrow-down-fill', 'exclude_school' => true],
                                ['name' => 'Integrasi Dapodik', 'link' => 'integrasi/dapodik', 'icon' => 'bi bi-cloud-arrow-down-fill', 'exclude_school' => true],
                            ]
                        ],
                        ['name' => 'LOGOUT', 'link' => 'logout', 'icon' => 'bi bi-box-arrow-right'],
                    ];
                } elseif ($userRole == '2') { // Guru
                    // Build Guru Menu
                    $menus = [
                        [
                            'header' => 'MENU UTAMA',
                            'menu' => [
                                ['name' => 'Beranda', 'link' => 'dashboard', 'icon' => 'bi bi-pc-display'],
                                ['name' => 'Profile Saya', 'link' => 'guru', 'icon' => 'bi bi-person-circle'],
                                ['name' => 'Pengumuman', 'link' => 'pengumuman', 'icon' => 'bi bi-megaphone-fill'],
                            ]
                        ],
                        [
                            'header' => 'MANAJEMEN UJIAN',
                            'menu' => [
                                [
                                    'name' => 'CBT / Ujian',
                                    'icon' => 'bi bi-mortarboard-fill',
                                    'submenu' => [
                                        ['name' => "Bank Soal", 'link' => "cbtbanksoal", 'icon' => 'bi bi-folder2-open'],
                                        ['name' => "Kartu Soal", 'link' => "cbtkartusoal", 'icon' => 'bi bi-collection', 'badge' => $kartuBadge],
                                        ['name' => "Ujian Praktik", 'link' => "cbtpraktik", 'icon' => 'bi bi-laptop'],
                                        ['name' => "Template Rubrik", 'link' => "cbtpraktik/templates", 'icon' => 'bi bi-list-check'],
                                        ['name' => "Jadwal", 'link' => "cbtjadwal", 'icon' => 'bi bi-calendar3'],
                                        ['name' => 'Status Siswa', 'link' => "cbtstatus", 'icon' => 'bi bi-person-workspace'],
                                        ['name' => 'Hasil Ujian', 'link' => "cbtnilai", 'icon' => 'bi bi-file-earmark-text'],
                                        ['name' => 'Rekap Nilai', 'link' => "cbtrekap", 'icon' => 'bi bi-trophy-fill'],
                                    ]
                                ],
                                ['name' => 'Log Aktivitas', 'link' => 'datalogs', 'icon' => 'bi bi-clock-history'],
                            ]
                        ],
                        [
                            'header' => 'PEMBELAJARAN',
                            'menu' => [
                                ['name' => 'Tujuan Pembelajaran', 'link' => 'guru/tujuan', 'icon' => 'bi bi-journal-plus'],
                                ['name' => 'Pengelolaan Nilai', 'link' => 'guru/nilai', 'icon' => 'bi bi-calculator'],
                            ]
                        ]
                    ];

                    if ($isWali) {
                        $menus[] = [
                            'header' => 'TUGAS WALI KELAS',
                            'menu' => [
                                [
                                    'name' => 'Informasi Kelas',
                                    'icon' => 'bi bi-house-heart-fill',
                                    'submenu' => [
                                        ['name' => 'Data Siswa', 'link' => 'walisiswa', 'icon' => 'bi bi-people-fill'],
                                        ['name' => 'Struktur Kelas', 'link' => 'walistruktur', 'icon' => 'bi bi-diagram-3'],
                                        ['name' => 'Catatan Wali', 'link' => 'walicatatan', 'icon' => 'bi bi-pencil-square'],
                                    ]
                                ],

                                [
                                    'name' => 'Daftar Nilai (Kelas)',
                                    'icon' => 'bi bi-printer-fill',
                                    'submenu' => [
                                        ['name' => 'Ledger Kelas', 'link' => 'guru/nilai/cetakleger', 'icon' => 'bi bi-circle'],
                                        ['name' => 'DKN (Nilai)', 'link' => 'guru/nilai/dkn', 'icon' => 'bi bi-circle'],
                                    ]
                                ],

                            ]
                        ];
                    }

                    $menus[] = ['name' => 'LOGOUT', 'link' => '', 'icon' => 'bi bi-box-arrow-right'];

                } elseif ($userRole == '4') { // Panitia
                    // Get panitia data to check jabatan
                    $isPanitiaPengawas = false;
                    $userId = session()->get('user_id');
                    
                    if ($userId) {
                        $db = \Config\Database::connect();
                        $queryPanitia = $db->table('master_panitia')->where('id_user', $userId)->get();
                        $panitiaData = $queryPanitia ? $queryPanitia->getRow() : null;
                        
                        if ($panitiaData) {
                            $jabatan = strtolower($panitiaData->jabatan ?? '');
                            $namaPanitia = strtolower($panitiaData->nama_panitia ?? '');
                            
                            // 1. Check profile jabatan/nama
                            if ($jabatan === 'pengawas' || $namaPanitia === 'pengawas') {
                                $isPanitiaPengawas = true;
                            } else {
                                // 2. Robust check: assigned in cbt_pengawas for active period
                                $queryTp = $db->table('master_tp')->where('active', 1)->get();
                                $tp_active = $queryTp ? $queryTp->getRow() : null;
                                
                                $querySmt = $db->table('master_smt')->where('active', 1)->get();
                                $smt_active = $querySmt ? $querySmt->getRow() : null;
                                
                                if ($tp_active && $smt_active) {
                                    $queryAssign = $db->table('cbt_pengawas')
                                        ->where('id_tp', $tp_active->id_tp)
                                        ->where('id_smt', $smt_active->id_smt)
                                        ->where('id_guru', 'P' . ($panitiaData->id_panitia ?? ''))
                                        ->get();
                                    
                                    if ($queryAssign && $queryAssign->getRow()) {
                                        $isPanitiaPengawas = true;
                                    }
                                }
                            }
                        }
                    }

                    $menus = [
                        [
                            'header' => 'MENU UTAMA',
                            'menu' => [
                                ['name' => 'Beranda', 'link' => 'dashboard', 'icon' => 'bi bi-pc-display'],
                                ['name' => 'Profile', 'link' => 'panitia', 'icon' => 'bi bi-person-circle'],
                                ['name' => 'Pengumuman', 'link' => 'pengumuman', 'icon' => 'bi bi-megaphone-fill'],
                                $isPanitiaPengawas ? ['name' => 'Panduan Pengawas', 'link' => 'panduan/pengawas', 'icon' => 'bi bi-journal-check text-warning'] : null,
                            ]
                        ],
                        [
                            'header' => 'PERSIAPAN UJIAN',
                            'menu' => [
                                $isPanitiaPengawas ? null : ['name' => 'Tujuan Pembelajaran', 'link' => 'guru/tujuan', 'icon' => 'bi bi-journal-plus'],
                                $isPanitiaPengawas ? null : ['name' => 'Bank Soal', 'link' => 'cbtbanksoal', 'icon' => 'bi bi-folder2-open'],
                                $isPanitiaPengawas ? null : ['name' => 'Kartu Soal', 'link' => 'cbtkartusoal', 'icon' => 'bi bi-collection', 'badge' => $kartuBadge],
                                $isPanitiaPengawas ? null : ['name' => 'Ujian Praktik', 'link' => 'cbtpraktik', 'icon' => 'bi bi-laptop'],
                                $isPanitiaPengawas ? null : ['name' => 'Template Rubrik', 'link' => 'cbtpraktik/templates', 'icon' => 'bi bi-list-check'],
                                $isPanitiaPengawas ? null : ['name' => 'Mapping Pengawas', 'link' => 'cbtpengawas', 'icon' => 'bi bi-briefcase-fill'],
                                $isPanitiaPengawas ? null : ['name' => 'Ruang/Sesi', 'link' => 'cbtsesisiswa', 'icon' => 'bi bi-person-workspace'],
                                $isPanitiaPengawas ? null : ['name' => 'Jadwal Ujian', 'link' => 'cbtjadwal', 'icon' => 'bi bi-calendar3'],
                                $isPanitiaPengawas ? null : ['name' => 'Alokasi Waktu', 'link' => 'cbtalokasi', 'icon' => 'bi bi-clock'],
                            ]
                        ],
                        [
                            'header' => 'PELAKSANAAN UJIAN',
                            'menu' => [
                                ['name' => 'Monitoring Ujian', 'link' => 'cbtstatus', 'icon' => 'bi bi-person-workspace'],
                                $isPanitiaPengawas ? null : ['name' => 'Administrasi Ujian', 'link' => 'cbtcetak', 'icon' => 'bi bi-file-earmark-text'],
                                ['name' => 'Data Siswa (Reset Login)', 'link' => 'usersiswa', 'icon' => 'bi bi-people-fill'],
                                $isPanitiaPengawas ? null : ['name' => 'Data Panitia', 'link' => 'userpanitia', 'icon' => 'bi bi-shield-lock-fill'],
                            ]
                        ],
                        [
                            'header' => 'HASIL & EVALUASI',
                            'menu' => [
                                $isPanitiaPengawas ? null : ['name' => 'Nilai Ujian', 'link' => 'cbtnilai', 'icon' => 'bi bi-bar-chart-fill'],
                                $isPanitiaPengawas ? null : ['name' => 'Monitoring Nilai (Kurmer)', 'link' => 'guru/nilai', 'icon' => 'bi bi-clipboard2-check'],
                                $isPanitiaPengawas ? null : ['name' => 'Analisis TP', 'link' => 'cbtanalysis', 'icon' => 'bi bi-graph-up-arrow'],
                                ['name' => 'Arsip Laporan', 'link' => 'laporan', 'icon' => 'bi bi-file-earmark-pdf-fill'],
                            ]
                        ]
                    ];

                    $menus[] = ['name' => 'LOGOUT', 'link' => '', 'icon' => 'bi bi-box-arrow-right'];

                }

                // Render Menus
                foreach ($menus as $header) {
                    if (isset($header['header'])) {
                        // Group with header
                        $hasVisibleItems = false;
                        $itemsHTML = '';

                        foreach ($header['menu'] as $menu) {
                            // Skip null menu items (used to hide menu for pengawas)
                            if ($menu === null)
                                continue;

                            if ($isSchoolAdmin && !empty($menu['exclude_school']))
                                continue;

                            if (isset($menu['submenu'])) {
                                $visibleSubmenus = [];
                                foreach ($menu['submenu'] as $s) {
                                    if (!($isSchoolAdmin && !empty($s['exclude_school']))) {
                                        $visibleSubmenus[] = $s;
                                    }
                                }

                                if (count($visibleSubmenus) > 0) {
                                    $hasVisibleItems = true;
                                    $subLinks = array_column($visibleSubmenus, 'link');
                                    // Check active state properly
                                    $isActive = false;
                                    foreach ($subLinks as $sl) {
                                        if ($sl === $page || ($sl !== '' && strpos($page, $sl) === 0))
                                            $isActive = true;
                                    }

                                    $itemsHTML .= '<li class="nav-item has-treeview ' . ($isActive ? 'menu-open' : '') . '">';
                                    $itemsHTML .= '<a href="#" class="nav-link ' . ($isActive ? 'active' : '') . '">';
                                    $itemsHTML .= '<i class="nav-icon ' . $menu['icon'] . '"></i>';
                                    $itemsHTML .= '<p>' . $menu['name'] . '<i class="bi bi-chevron-left right"></i></p>';
                                    $itemsHTML .= '</a><ul class="nav nav-treeview">';

foreach ($visibleSubmenus as $sub) {
                                        $subActive = ($page === $sub['link'] || ($sub['link'] !== '' && strpos($page, $sub['link']) === 0)) ? 'active' : '';
                                        $subBadge = !empty($sub['badge']) && is_array($sub['badge'])
                                            ? '<span class="badge ' . esc($sub['badge']['class'] ?? 'bg-secondary') . ' rounded-pill sidebar-badge"'
                                                . ' style="float:right;margin-top:2px;font-size:.62rem;font-weight:700;"'
                                                . (isset($sub['badge']['title']) ? ' title="' . esc($sub['badge']['title']) . '"' : '') . '>'
                                                . esc($sub['badge']['text'] ?? '') . '</span>'
                                            : '';
                                        $itemsHTML .= '<li class="nav-item">';
                                        $itemsHTML .= '<a href="' . base_url($sub['link']) . '" class="nav-link ' . $subActive . '">';
                                        $itemsHTML .= '<i class="' . $sub['icon'] . ' nav-icon"></i>';
                                        $itemsHTML .= '<p>' . $sub['name'] . $subBadge . '</p></a></li>';
                                    }
                                    $itemsHTML .= '</ul></li>';
                                }
                            } else {
                                $hasVisibleItems = true;
                                if ($menu['link'] === '' || $menu['link'] === 'logout') {
                                    $itemsHTML .= '<hr/><li class="nav-item"><a href="#" onclick="logout()" class="nav-link">';
                                    $itemsHTML .= '<i class="nav-icon ' . $menu['icon'] . '"></i>';
                                    $itemsHTML .= '<p>' . $menu['name'] . '</p></a></li>';
                                } else {
                                    $act = ($page === $menu['link']) ? 'active' : '';
                                    $itemsHTML .= '<li class="nav-item"><a href="' . base_url($menu['link']) . '" class="nav-link ' . $act . '">';
                                    $itemsHTML .= '<i class="nav-icon ' . $menu['icon'] . '"></i>';
                                    $itemsHTML .= '<p>' . $menu['name'] . '</p></a></li>';
                                }
                            }
                        }

                        if ($hasVisibleItems) {
                            echo '<li class="nav-header">' . $header['header'] . '</li>';
                            echo $itemsHTML;
                        }

                    } else {
                        // Single Item without header (flat structure or fallback)
                        $menu = $header;
                        if ($menu['link'] === '' || $menu['link'] === 'logout') {
                            echo '<hr/><li class="nav-item"><a href="#" onclick="logout()" class="nav-link">';
                            echo '<i class="nav-icon ' . $menu['icon'] . '"></i>';
                            echo '<p>' . $menu['name'] . '</p></a></li>';
                        } else {
                            $act = ($page === $menu['link']) ? 'active' : '';
                            echo '<li class="nav-item"><a href="' . base_url($menu['link']) . '" class="nav-link ' . $act . '">';
                            echo '<i class="nav-icon ' . $menu['icon'] . '"></i>';
                            echo '<p>' . $menu['name'] . '</p></a></li>';
                        }
                    }
                }
                ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

