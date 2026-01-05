<?php echo view('_templates/dashboard/_header', get_defined_vars()); ?>
<?php echo view('_templates/dashboard/navbar', get_defined_vars()); ?>
<?php echo view('_templates/dashboard/sidebar', get_defined_vars()); ?>
<!-- Content Wrapper. Contains page content -->
<style>
    /* Dashboard Card Styles */
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Color Variants - Background */
    .variant-blue-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .variant-teal-bg {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
    }

    .variant-purple-bg {
        background: linear-gradient(135deg, #a855f7 0%, #c026d3 100%);
    }

    .variant-green-bg {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .variant-indigo-bg {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .variant-red-bg {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    /* Color Variants - Text */
    .variant-blue-text,
    .variant-teal-text,
    .variant-purple-text,
    .variant-green-text,
    .variant-indigo-text,
    .variant-red-text {
        color: white;
    }
</style>
<main class="app-main bg-light pt-4">
    <!-- Main content -->
    <section class="content mt-4">
        <div class="container-fluid">
            <!-- Info Boxes -->
            <div class="row">
                <?php
                // Cek jabatan
                $is_pengawas = isset($panitia->jabatan) && $panitia->jabatan === 'Pengawas';

                // Cek kelengkapan profil (hanya field yang critical untuk administrasi ujian)
                $missing_fields = [];
                if (empty($panitia->nama_panitia))
                    $missing_fields[] = 'Nama Lengkap';
                if (empty($panitia->nip))
                    $missing_fields[] = 'NIP/NUPTK';
                if (empty($panitia->ttd))
                    $missing_fields[] = 'Tanda Tangan Digital';
                if ($is_pengawas && empty($panitia->instansi))
                    $missing_fields[] = 'Instansi/Sekolah Asal';
                if ($is_pengawas && empty($panitia->ruang))
                    $missing_fields[] = 'Ruang Ujian (Pengawas)';

                $is_profile_incomplete = !empty($missing_fields);

                if ($is_pengawas):
                    ?>
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">Selamat Datang, Bapak/Ibu Pengawas</h3>
                            </div>
                            <div class="card-body">
                                <?php
                                $banners = [];
                                if (isset($setting->banner1) && !empty($setting->banner1))
                                    $banners[] = $setting->banner1;
                                if (isset($setting->banner2) && !empty($setting->banner2))
                                    $banners[] = $setting->banner2;
                                if (isset($setting->banner3) && !empty($setting->banner3))
                                    $banners[] = $setting->banner3;

                                $captions = [
                                    0 => [
                                        'title' => 'Selamat Datang di ' . (isset($setting->sekolah) ? $setting->sekolah : 'Aplikasi CBT'),
                                        'text' => isset($setting->nama_aplikasi) ? $setting->nama_aplikasi : 'Sistem Ujian Berbasis Komputer'
                                    ],
                                    1 => [
                                        'title' => 'Monitoring & Evaluasi',
                                        'text' => 'Pantau pelaksanaan ujian secara real-time dan transparan'
                                    ],
                                    2 => [
                                        'title' => 'Hasil Cepat & Akurat',
                                        'text' => 'Analisis hasil ujian peserta didik dengan mudah dan cepat'
                                    ]
                                ];

                                if (!empty($banners)):
                                    ?>
                                    <div id="carouselPengawas" class="carousel slide mb-4" data-bs-ride="carousel">
                                        <ol class="carousel-indicators">
                                            <?php foreach ($banners as $key => $banner): ?>
                                                <li data-bs-target="#carouselPengawas" data-bs-slide-to="<?= $key ?>"
                                                    class="<?= $key === 0 ? 'active' : '' ?>"></li>
                                            <?php endforeach; ?>
                                        </ol>
                                        <div class="carousel-inner"
                                            style="max-height: 400px; border-radius: 5px; overflow: hidden;">
                                            <?php foreach ($banners as $key => $banner): ?>
                                                <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">
                                                    <img class="d-block w-100" src="<?= base_url($banner) ?>"
                                                        alt="Slide <?= $key + 1 ?>"
                                                        style="object-fit: cover; height: 100%; width: 100%;">
                                                    <div class="carousel-caption d-none d-md-block"
                                                        style="background: rgba(0,0,0,0.5); border-radius: 10px; padding: 10px;">
                                                        <h5><?= isset($captions[$key]['title']) ? $captions[$key]['title'] : '' ?>
                                                        </h5>
                                                        <p><?= isset($captions[$key]['text']) ? $captions[$key]['text'] : '' ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <a class="carousel-control-prev" href="#carouselPengawas" role="button"
                                            data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>

                                        </a>
                                        <a class="carousel-control-next" href="#carouselPengawas" role="button"
                                            data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>

                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php
                    $colors = [
                        'blue' => 'variant-blue',
                        'cyan' => 'variant-teal',
                        'teal' => 'variant-teal',
                        'fuchsia' => 'variant-purple',
                        'success' => 'variant-green',
                        'info' => 'variant-blue',
                        'indigo' => 'variant-indigo',
                        'maroon' => 'variant-red',
                        'green' => 'variant-green'
                    ];
                    foreach ($info_box as $info):
                        $variant = isset($colors[$info->box]) ? $colors[$info->box] : 'variant-blue';
                        ?>
                        <div class="col-lg col-md-4 col-sm-6 col-12 mb-4">
                            <a href="#" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 hover-lift">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="icon-wrapper rounded-circle d-flex align-items-center justify-content-center <?= $variant ?>-bg"
                                                style="width: 48px; height: 48px;">
                                                <?php
                                                $iconMap = [
                                                    // Common Mappings
                                                    'users' => 'people-fill',
                                                    'chalkboard-teacher' => 'person-workspace', // Guru
                                                    'user-tie' => 'person-badge-fill', // Wali Kelas
                                                    'book' => 'book-fill',
                                                    'user' => 'person-fill',
                                                    'graduation-cap' => 'mortarboard-fill',
                                                    'university' => 'bank2',
                                                    'tags' => 'tags-fill',
                                                    'bell' => 'bell-fill',
                                                    'list-alt' => 'card-list',
                                                    'tasks' => 'list-check',
                                                    'envelope' => 'envelope-fill',
                                                    'comments' => 'chat-dots-fill',
                                                    'clipboard-list' => 'clipboard-data-fill',
                                                    'cogs' => 'gear-wide-connected',
                                                    'cog' => 'gear-fill',
                                                    'desktop' => 'pc-display',
                                                    'server' => 'hdd-network',
                                                    'bullhorn' => 'megaphone-fill',
                                                    'pie-chart' => 'pie-chart-fill',
                                                    'home' => 'house-door-fill',
                                                    'print' => 'printer-fill',
                                                    'id-badge' => 'person-vcard-fill',
                                                    'address-card' => 'person-vcard-fill',
                                                    'running' => 'person-walking',
                                                    'child' => 'person-arms-up',
                                                ];
                                                // Fallback logic: check map, then check if icon exists directly, otherwise default to circle
                                                $biIcon = isset($iconMap[$info->icon]) ? $iconMap[$info->icon] : $info->icon;
                                                ?>
                                                <i class="bi bi-<?= $biIcon ?> <?= $variant ?>-text"
                                                    style="font-size: 24px;"></i>
                                            </div>
                                            <h2 class="mb-0 fw-bold text-dark"
                                                id="total-<?= strtolower(str_replace(' ', '-', $info->title)) ?>">
                                                <?= $info->total; ?>
                                            </h2>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark fw-semibold"><?= $info->title; ?></h6>
                                            <small class="text-muted">Data Terkini</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($is_pengawas): ?>
                    <!-- Welcome Modal for Pengawas -->
                    <div class="modal fade" id="modalWelcomePengawas" tabindex="-1" role="dialog"
                        aria-labelledby="modalWelcomeLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title font-weight-bold" id="modalWelcomeLabel">
                                        <i class="bi bi-megaphone-fill me-2"></i>Selamat Datang di Dashboard Pengawas
                                    </h5>
                                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                                        aria-label="Close">

                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-4">
                                        <img src="<?= base_url('assets/img/checklist.png') ?>" alt="Welcome"
                                            style="max-height: 150px; opacity: 0.8;" class="img-fluid mb-3">
                                        <h4>Halo, Bapak/Ibu Pengawas!</h4>
                                        <p class="text-muted">Berikut adalah beberapa langkah awal untuk memulai tugas
                                            pengawasan Anda:</p>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box shadow-none border bg-light h-100">
                                                <span class="info-box-icon text-primary"><i
                                                        class="bi bi-person-gear"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text font-weight-bold">1. Lengkapi Profil</span>
                                                    <span class="info-box-text text-wrap small text-muted">Pastikan data
                                                        diri, kontak, dan foto profil Anda sudah lengkap dan benar.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box shadow-none border bg-light h-100">
                                                <span class="info-box-icon text-info"><i
                                                        class="bi bi-calendar-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text font-weight-bold">2. Cek Jadwal</span>
                                                    <span class="info-box-text text-wrap small text-muted">Periksa jadwal
                                                        pengawasan Anda di menu Jadwal Ujian.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box shadow-none border bg-light h-100">
                                                <span class="info-box-icon text-success"><i
                                                        class="bi bi-pc-display"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text font-weight-bold">3. Monitoring Ujian</span>
                                                    <span class="info-box-text text-wrap small text-muted">Gunakan menu
                                                        Monitoring Ujian untuk memantau status peserta.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 p-3 bg-light border rounded">
                                        <h6 class="font-weight-bold text-danger"><i
                                                class="bi bi-exclamation-triangle-fill me-2"></i>Penting!</h6>
                                        <ul class="mb-0 text-sm">
                                            <li>Jangan berikan username dan password Anda kepada orang lain.</li>
                                            <li>Segera laporkan ke teknisi/proktor jika terjadi kendala teknis.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="dontShowAgain">
                                        <label class="custom-control-label" for="dontShowAgain">Jangan tampilkan
                                            lagi</label>
                                    </div>
                                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Saya
                                        Mengerti</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($is_profile_incomplete): ?>
                    <!-- Modal Profil Tidak Lengkap - Tidak bisa ditutup -->
                    <div class="modal fade" id="modalProfileIncomplete" tabindex="-1" role="dialog"
                        aria-labelledby="modalProfileIncompleteLabel" aria-hidden="true" data-bs-backdrop="static"
                        data-bs-keyboard="false">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title fw-bold" id="modalProfileIncompleteLabel">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Profil Belum Lengkap!
                                    </h5>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-4">
                                        <div class="mb-3">
                                            <i class="bi bi-person-exclamation text-danger" style="font-size: 4rem;"></i>
                                        </div>
                                        <h5 class="fw-bold">Halo, <?= $panitia->nama_panitia ?? 'Bapak/Ibu' ?>!</h5>
                                        <p class="text-muted">Anda harus melengkapi data profil sebelum dapat menggunakan
                                            sistem ini.</p>
                                    </div>

                                    <div class="alert alert-warning border-warning">
                                        <h6 class="fw-bold mb-2"><i class="bi bi-list-check me-2"></i>Data yang belum
                                            lengkap:</h6>
                                        <ul class="mb-0">
                                            <?php foreach ($missing_fields as $field): ?>
                                                <li><i class="bi bi-x-circle text-danger me-1"></i><?= $field ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <div class="bg-light p-3 rounded border">
                                        <p class="small mb-0 text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Data ini diperlukan untuk keperluan administrasi, pencetakan Berita Acara, dan
                                            Pakta Integritas ujian.
                                        </p>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light justify-content-center">
                                    <a href="<?= base_url('panitia') ?>" class="btn btn-light btn-lg px-5">
                                        <i class="bi bi-pencil-square me-2"></i>Lengkapi Profil Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-danger shadow-sm border-0">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="bi bi-info-circle-fill me-2"></i>Informasi
                            </h4>
                            <div class="card-tools d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-tool" data-card-widget="maximize" title="Maximize">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (!$is_pengawas): ?>
                                    <?php foreach ($ujian_box as $info): ?>
                                        <div class="col-4 col-md-3 col-lg-2" style="min-height: 60px">
                                            <div class="info-box border p-1 hover-lift" style="min-height: 60px">
                                                <div class="info-box-content p-1 text-danger">
                                                    <span class="info-box-text"><?= $info->title; ?></span>
                                                    <h5 class="info-box-number m-0"
                                                        id="total-ujian-<?= strtolower(str_replace(' ', '-', $info->title)) ?>">
                                                        <?= $info->total; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <div class="col-6 col-md-3 col-lg-2" style="min-height: 60px">
                                    <div class="info-box border p-1 hover-lift" style="min-height: 60px">
                                        <div class="info-box-content p-1 text-primary">
                                            <span class="info-box-text">
                                                <i class="bi bi-key-fill me-1"></i>Token
                                                <small class="float-end d-none" id="interval">-- : -- :
                                                    --</small></span>
                                            <h5 class="info-box-number m-0" id="token-view">
                                                <?= isset($token->token) ? $token->token : '- - - - - -' ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-center alert alert-default-info m-0 rounded-0">
                                        <i class="bi bi-calendar-check-fill me-2"></i><b>JADWAL
                                            PENGAWASAN HARI
                                            INI</b>
                                    </h6>
                                    <div class="col-12 table-responsive" id="table-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- JS Logic for Table Generation -->
    <script src="<?= base_url() ?>/assets/app/js/rowspanizer.js"></script>
    <script>
        let halaman = 0;
        adaJadwalUjian = '<?= count($ada_ujian) ?>' || '0';
        localStorage.setItem('garudaCBT.ada_jadwal_ujian', adaJadwalUjian);

        async function ajaxGet(url) {
            return new Promise((resolve, reject) => {
                setTimeout(function () {
                    $.ajax({
                        url: base_url + url,
                        type: "GET",
                        success: function (respon) {
                            resolve(respon); // Resolusi Promise ketika sukses
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error:", status, error);
                            reject(error); // Rejeksi Promise ketika ada error
                        }
                    });
                }, 100)
            });
        }

        async function createTableFromJSON(data) {
            const tableContainer = document.getElementById('table-container');
            const allSesi = data.ruangs || [];
            const jsonData = data.jadwals_ujian || [];

            // 1. Validate and handle empty data
            if (!validateData(allSesi, jsonData, tableContainer)) {
                return;
            }

            // 2. Process and group the data
            const groupedSchedules = processAndGroupData(data);

            // 3. Sort the final list for correct rowspans
            groupedSchedules.sort((a, b) => {
                if (a.ruang_sesi.ruang_id !== b.ruang_sesi.ruang_id) return a.ruang_sesi.ruang_id - b.ruang_sesi.ruang_id;
                if (a.ruang_sesi.sesi_id !== b.ruang_sesi.sesi_id) return a.ruang_sesi.sesi_id - b.ruang_sesi.sesi_id;
                return a.jam_ke - b.jam_ke;
            });

            // 4. Calculate rowspans for 'ruang' and 'sesi' columns
            const rowCounts = calculateRowCounts(groupedSchedules, 'ruang_sesi.ruang_id');
            const sesiRowCounts = calculateRowCounts(groupedSchedules, 'ruang_sesi.ruang_id', 'ruang_sesi.sesi_id');

            // 5. Build and render the HTML table
            const tableHtml = buildTable(groupedSchedules, data, rowCounts, sesiRowCounts);
            tableContainer.innerHTML = tableHtml;
        }

        function validateData(allSesi, jsonData, container) {
            if (allSesi.length === 0 || jsonData.length === 0) {
                container.innerHTML = `<div class="alert alert-warning text-center m-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Tidak ada jadwal penilaian hari ini</div>`;
                return false;
            }
            return true;
        }

        function processAndGroupData(data) {
            const allSesi = data.ruangs || [];
            const jsonData = data.jadwals_ujian || [];
            const grouped = {};

            jsonData.forEach(jadwal => {
                jadwal.bank.bank_kelas = jadwal.bank.bank_kelas.map(String)
                const ruangFiltered = allSesi?.filter(r =>
                    r.kelas_id.some(id => jadwal.bank.bank_kelas.includes(id?.toString()))
                );

                ruangFiltered.forEach(ruang => {
                    // siswa dalam sesi/ruang ini
                    const siswaRuang = data.sesisiswa.filter(s =>
                        s.ruang_id === ruang.ruang_id &&
                        s.sesi_id === ruang.sesi_id &&
                        jadwal.bank.bank_kelas.includes(s.kelas_id?.toString())
                    );
                    const jumlahSiswa = siswaRuang.length;

                    let belumLogin = 0, sedangLogin = 0, selesai = 0;
                    siswaRuang.forEach(s => {
                        // cari cbtsiswa untuk siswa ini
                        const cbt = data.cbtsiswa.find(c =>
                            c.id_siswa === s.siswa_id &&
                            c.jadwal.id_jadwal === jadwal.id_jadwal
                        );

                        if (!cbt || cbt.mulai === null) {
                            belumLogin++;
                        } else if (cbt.selesai === null) {
                            sedangLogin++;
                        } else {
                            selesai++;
                        }
                    });
                    const pengawasJadwal = data.pengawas[jadwal.id_jadwal]?.[ruang.ruang_id]?.[ruang.sesi_id] || {};
                    const key = `${ruang.ruang_id}-${ruang.sesi_id}-${pengawasJadwal.jam_ke || '1'}`;
                    if (!grouped[key]) {
                        grouped[key] = {
                            key: key,
                            ruang_sesi: ruang,
                            jam_ke: pengawasJadwal.jam_ke || '1',
                            jadwal: [],
                            pengawas: [],
                            peserta: 0,
                            blm_login: 0,
                            sdg_login: 0,
                            selesai: 0
                        };
                    }
                    grouped[key].jadwal.push(jadwal);
                    const pengawasList = (pengawasJadwal.id_guru || '').split(',').filter(Boolean);
                    for (const string of pengawasList) {
                        if (!grouped[key].pengawas.includes(data.gurus[string] || '')) {
                            grouped[key].pengawas.push(data.gurus[string] || '');
                        }
                    }
                    grouped[key].peserta += jumlahSiswa;
                    grouped[key].blm_login += belumLogin;
                    grouped[key].sdg_login += sedangLogin;
                    grouped[key].selesai += selesai;
                });
            });

            return Object.values(grouped);
        }

        function calculateRowCounts(items, ...keys) {
            const counts = new Map();
            let prevValues = null;
            let currentCount = 0;

            // Use a deep equality check for dynamic keys
            const getValues = (item) => keys.map(key => {
                return key.split('.').reduce((obj, prop) => obj?.[prop], item);
            });

            [...items, null].forEach(item => {
                const currentValues = item ? getValues(item) : null;

                const isSameGroup = prevValues && currentValues &&
                    prevValues.every((val, i) => val === currentValues[i]);

                if (!isSameGroup) {
                    if (prevValues !== null) {
                        const key = prevValues.join('-');
                        counts.set(key, currentCount);
                    }
                    currentCount = 0;
                }

                if (item !== null) {
                    currentCount++;
                }
                prevValues = currentValues;
            });

            return counts;
        }

        function buildTable(groupedSchedules, data, rowCounts, sesiRowCounts) {
            const headerHtml = `
        <thead class="bg-white">
            <tr class="text-uppercase text-xs font-weight-bold text-secondary">
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Ruang</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Sesi</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Jam</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Mata Pelajaran</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Kelas</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Pengawas</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Siswa</th>
                <th class="text-center align-middle" colspan="3" style="background-color: #f8f9fc;">Status Peserta</th>
            </tr>
            <tr class="text-xs font-weight-bold text-secondary">
                <th class="text-center align-middle border-bottom" style="background-color: #f8f9fc; border-top: 1px solid #e3e6f0;">Belum</th>
                <th class="text-center align-middle border-bottom" style="background-color: #f8f9fc; border-top: 1px solid #e3e6f0;">Sedang</th>
                <th class="text-center align-middle border-bottom" style="background-color: #f8f9fc; border-top: 1px solid #e3e6f0;">Selesai</th>
            </tr>
        </thead>`;

            let bodyHtml = '<tbody>';
            const usedRuangIds = new Set();
            const usedSesiKeys = new Set();
            let curSesi = 0;

            groupedSchedules.forEach(group => {
                const { ruang_sesi, jam_ke, jadwal, pengawas, peserta, blm_login, sdg_login, selesai } = group;
                let infoMapel = ``;
                for (const jad of jadwal) {
                    infoMapel += `
                        <div class="mb-1">
                            <span class="fw-bold text-dark">${jad.bank.mapel.kode}</span>
                            <span class="badge bg-light text-dark border border-secondary shadow-sm ms-1">${jad.bank.bank_kode}</span>
                        </div>`;
                }
                const bank_kelas = getUniqueClassNames(ruang_sesi, jadwal).map(k => `<span class="badge bg-purple bg-gradient-purple text-white me-1 mb-1">${k}</span>`).join('');
                const idsGuru = jadwal.map(j => j.bank.bank_guru_id).join(',');

                // Format pengawas list cleaner
                let pengawasListContent = pengawas.filter(p => p && p.trim() !== "");
                let pengawasList = "";

                if (pengawasListContent.length === 0) {
                    pengawasList = `<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Belum ditentukan</span>`;
                } else {
                    pengawasList = pengawasListContent.map(p => `<div class="small mb-1"><i class="bi bi-person-check-fill me-1 text-muted"></i>${p}</div>`).join('');
                }

                bodyHtml += `<tr class="bg-white">`;

                // Ruang column with rowspan
                const ruangId = ruang_sesi.ruang_id;
                if (!usedRuangIds.has(ruangId)) {
                    const rowspanValue = rowCounts.get(String(ruangId)) || 1;
                    bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle border-bottom-bold font-weight-bold text-dark bg-white shadow-sm" style="border-right: 1px solid #dee2e6;">${ruang_sesi.nama_ruang}</td>`;
                    usedRuangIds.add(ruangId);
                }

                // Sesi column with rowspan
                const sesiKey = `${ruang_sesi.ruang_id}-${ruang_sesi.sesi_id}`;
                if (!usedSesiKeys.has(sesiKey)) {
                    const rowspanValue = sesiRowCounts.get(sesiKey) || 1;
                    // bgRow logic if needed, currently using clean white
                    bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle bg-white fw-medium" style="border-right: 1px solid #dee2e6;">${ruang_sesi.nama_sesi}</td>`;
                    usedSesiKeys.add(sesiKey);
                    curSesi++;
                }

                bodyHtml += `
            <td class="text-center align-middle bg-white">${jam_ke}</td>
            <td class="align-middle bg-white" style="font-size: 0.9rem;">${infoMapel}</td>
            <td class="text-center align-middle bg-white">${bank_kelas}</td>
            <td class="text-start align-middle bg-light" data-guru="${idsGuru}">${pengawasList}</td>
            <td class="text-center align-middle bg-white fw-bold" style="font-size: 1.1em;">${peserta > 0 ? peserta : '-'}</td>
            <td class="text-center align-middle bg-white">
                <span class="badge bg-danger shadow-sm" style="min-width: 35px; padding: 6px; font-size: 0.9em;">${blm_login}</span>
            </td>
            <td class="text-center align-middle bg-white">
                <span class="badge bg-info shadow-sm" style="min-width: 35px; padding: 6px; font-size: 0.9em;">${sdg_login}</span>
            </td>
            <td class="text-center align-middle bg-white">
                <span class="badge bg-success shadow-sm" style="min-width: 35px; padding: 6px; font-size: 0.9em;">${selesai}</span>
            </td>
        </tr>`;
            });

            bodyHtml += '</tbody>';
            return `<table class="table table-bordered table-hover w-100 shadow-sm" style="border-collapse: separate; border-spacing: 0; border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6;">${headerHtml}${bodyHtml}</table>`;
        }

        function getUniqueClassNames(ruang_sesi, jadwal) {
            const banks_kelas = [];
            const classIds = ruang_sesi.kelas_id;

            for (const j of jadwal) {
                for (const klsId of classIds) {
                    if (j.bank?.bank_kelas?.includes(klsId)) {
                        banks_kelas.push(ruang_sesi.nama_kelas[klsId] || '');
                    }
                }
            }

            return [...new Set(banks_kelas)];
        }

        function formatTanggal(dateString) {
            const date = new Date(dateString);
            const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
            return date.toLocaleDateString('id-ID', options);
        }

        function createTime(d) {
            var date = new Date(d);

            var jam = date.getHours();
            var menit = date.getMinutes();
            var sJam;
            var sMenit;

            if (jam < 10) sJam = '0' + jam;
            else sJam = '' + jam;

            if (menit < 10) sMenit = '0' + menit;
            else sMenit = '' + menit;

            var hari = daysdifference(d);
            var time;

            if (hari === 0) {
                time = sJam + ':' + sMenit;
            } else if (hari === 1) {
                time = 'kemarin ' + sJam + ':' + sMenit;
            } else {
                time = jQuery.timeago(d) + ', ' + sJam + ':' + sMenit;
            }
            return time;
        }

        function daysdifference(last) {
            var startDay = new Date(last);
            var endDay = new Date();

            var millisBetween = startDay.getTime() - endDay.getTime();
            var days = millisBetween / (1000 * 3600 * 24);

            return Math.round(Math.abs(days));
        }

        function addComments(id, comments, append) {
            var comm = '';
            $.each(comments, function (i, v) {
                var dari, foto, avatar;
                if (v.dari == '0') {
                    dari = 'Admin';
                    avatar = v.foto != null ? '<img class="rounded-circle border" src="' + v.foto + '" alt="Img" width="40px" height="40px">' :
                        '<div class="btn-circle-sm btn-success media-left pt-1" style="width: 43px; height: 40px">A</div>'
                } else {
                    if (v.dari_group == '2') {
                        dari = v.nama_guru;
                        foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/guru.png';
                        avatar = '<img class="rounded-circle border" src="' + foto + '" alt="Img" width="40px" height="40px">';
                    } else {
                        dari = v.nama_siswa;
                        foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                        avatar = '<img class="rounded-circle border" src="' + foto + '" alt="Img" width="40px" height="40px">';
                    }
                }

                comm += '<div class="d-flex mt-1" id="parent-reply' + v.id_comment + '">'
                    + avatar +
                    '    <div class="w-100 ms-2">' +
                    '        <div class="flex-grow-1 border ps-3 bg-light" style="border-radius: 20px">' +
                    '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                    '            <div class="comment-text pb-1">' + v.text + '</div>' +
                    '        </div>' +
                    '        <div class="ms-2">' +
                    '            <span class="btn-sm me-2 text-muted">' + createTime(v.tanggal) + '</span>' +
                    '            <span id="trigger-reply' + v.id_comment + '" class="btn btn-sm me-2 text-muted action-collapse" data-bs-toggle="collapse" aria-expanded="true"' +
                    '                              aria-controls="collapse-reply' + v.id_comment + '"' +
                    '                              href="#collapse-reply' + v.id_comment + '"><b>' + v.jml + ' balasan</b></span>' +
                    '            <span class="btn btn-sm me-2 text-muted btn-toggle-reply"' +
                    '                  data-id="' + v.id_comment + '" data-bs-toggle="modal" data-bs-target="#balasanModal">' +
                    '                <i class="bi bi-reply-fill"></i> <b>Balas</b></span>' +
                    '<span class="btn btn-sm text-muted" onclick="hapusKomentar(' + v.id_comment + ')" data-id="' + v.id_comment + '"><i class="bi bi-trash me-1"></i> Hapus</span>' +
                    '        </div>' +
                    '<div id="collapse-reply' + v.id_comment + '" class="p-2 collapse toggle-reply" data-id="' + v.id_comment + '" data-bs-parent="#parent-reply' + v.id_comment + '">';
                if (v.jml != '0') {
                    comm += '<div id="konten-reply' + v.id_comment + '"></div>' +
                        '<div id="loadmore-reply' + v.id_comment + '" onclick="getReplies(' + v.id_comment + ')" class="text-center mb-3 loadmore-reply">' +
                        '       <div class="btn btn-default">Muat balasan lainnya ...</div>' +
                        '</div>';
                } else {
                    comm += '<div class="text-center" id="empty-comment">Tidak ada komentar</div>';
                }
                comm += '    <div id="loading-reply' + v.id_comment + '" class="text-center d-none">' +
                    '        <div class="spinner-grow"></div>' +
                    '    </div>' +
                    '</div>' +
                    '    </div>' +
                    '</div>';
            });

            if (append) {
                $(`#konten${id}`).append(comm);
            } else {
                $(`#konten${id}`).prepend(comm);
            }

            $('.toggle-reply').on('shown.bs.collapse', function (e) {
                var konten = $(this);
                var id = konten.data('id');
                var list = $(this).find('.media').length;
                if (list === 0) $(`#loadmore-reply${id}`).click();
            });

            $('#empty-comment').remove();
        }

        function addReplies(id, replies, append) {
            //console.log('replies', replies);
            var repl = '';
            $.each(replies, function (i, v) {
                var sudahAda = $(`.media${v.id_reply}`).length;
                if (!sudahAda) {
                    var dari, foto, avatar;
                    if (v.dari == '0') {
                        dari = 'Admin';
                        avatar = v.foto != null ? '<img class="rounded-circle border" src="' + v.foto + '" alt="Img" width="35px" height="35px">' :
                            '<div class="btn-circle-sm btn-success media-left pt-1 me-2" style="width: 37px">A</div>';
                    } else {
                        if (v.dari_group == '2') {
                            dari = v.nama_guru;
                            foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/guru.png';
                            avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="35px" height="35px">';
                        } else {
                            dari = v.nama_siswa;
                            foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                            avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="35px" height="35px">';
                        }
                    }

                    repl +=
                        '<div class="d-flex mt-1 media' + v.id_reply + '">'
                        + avatar +
                        '    <div class="w-100">' +
                        '        <div class="flex-grow-1 border ps-3" style="border-radius: 17px; background-color: #dee2e6">' +
                        '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                        '            <div class="comment-text">' + v.text +
                        '            </div>' +
                        '        </div>' +
                        '        <div class="ms-2">' +
                        '            <small class="btn-sm me-2 text-muted">' + createTime(v.tanggal) + '</small>' +
                        '            <span class="btn btn-sm text-muted" onclick="hapusReply(' + v.id_reply + ')" data-id="' + v.id_reply + '">' +
                        '                <i class="bi bi-trash me-1"></i> Hapus' +
                        '            </span>' +
                        '        </div>' +
                        '    </div>' +
                        '</div>';
                }
            });

            if (append) {
                $(`#konten-reply${id}`).append(repl);
            } else {
                $(`#konten-reply${id}`).prepend(repl);
            }
            //console.log('added', 'reply'+id);
        }

        function getComments(id) {
            $(`#loading${id}`).removeClass('d-none');
            $(`#loadmore${id}`).addClass('d-none');
            var $count = $(`#loadmore${id}`), page = $count.data('count');
            if (!page) page = 0;

            setTimeout(function () {
                $.ajax({
                    url: base_url + "pengumuman/getcomment/" + id + "/" + page,
                    type: "GET",
                    success: function (response) {
                        console.log("result", response);
                        page += 1;
                        currentPage = page;
                        $count.data('count', page);

                        if (response.length === 5) {
                            $(`#loadmore${id}`).removeClass('d-none');
                        }
                        $(`#loading${id}`).addClass('d-none');
                        addComments(id, response, true)
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        $(`#loading${id}`).addClass('d-none');
                        if ($count.data('count') > 0) {
                            $(`#loadmore${id}`).removeClass('d-none');
                        }
                    }
                });
            }, 500);
        }

        function getReplies(id) {
            $(`#loading-reply${id}`).removeClass('d-none');
            $(`#loadmore-reply${id}`).addClass('d-none');
            var $count = $(`#loadmore-reply${id}`), page = $count.data('count');
            if (!page) page = 0;

            setTimeout(function () {
                $.ajax({
                    url: base_url + "pengumuman/getreplies/" + id + "/" + page,
                    type: "GET",
                    success: function (response) {
                        console.log("result", response);
                        page += 1;
                        currentPage = page;
                        $count.data('count', page);

                        //n >= start && n <= end
                        if (response.length === 5) {
                            $(`#loadmore-reply${id}`).removeClass('d-none');
                        }
                        $(`#loading-reply${id}`).addClass('d-none');
                        addReplies(id, response, true)
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        $(`#loading-reply${id}`).addClass('d-none');
                        if ($count.data('count') > 0) {
                            $(`#loadmore-reply${id}`).removeClass('d-none');
                        }
                    }
                });
            }, 500);
        }

        function addPosts(response) {
            var card = '';

            if (response.length > 0) {
                $.each(response, function (i, v) {
                    var dari, foto, avatar;
                    if (v.dari == '0') {
                        dari = 'Admin';
                        avatar = v.foto != null ? '<img class="rounded-circle border" src="' + v.foto + '" alt="Img" width="50px" height="50px">' :
                            '<div class="btn-circle btn-success media-left pt-1 align-middle">A</div>';
                    } else {
                        if (v.dari_group == '2') {
                            dari = v.nama_guru;
                            foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/guru.png';
                            avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="50px" height="50px">';
                        } else {
                            dari = v.nama_siswa;
                            foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                            avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="50px" height="50px">';
                        }
                    }

                    card += '<div class="card shadow-sm border-0">' +
                        '    <div class="card-body" id="parent' + v.id_post + '">' +
                        '        <div class="d-flex">' +
                        avatar +
                        '                <div class="flex-grow-1 ms-3">' +
                        '                    <span class="font-weight-bold"><b>' + dari + '</b></span>' +
                        '                    <br/>' +
                        '                    <span class="text-gray">' + createTime(v.tanggal) + '</span>' +
                        '                </div>' +
                        '        </div>' +
                        '        <div class="mt-2">' + v.text + '</div>' +
                        '        <div class="text-muted">' +
                        '            <button type="button" class="btn btn-light btn-sm me-2 btn-toggle"' +
                        '                    data-id="' + v.id_post + '" data-bs-toggle="modal"' +
                        '                    data-bs-target="#komentarModal"><i class="bi bi-reply-fill me-1"></i> Tulis komentar' +
                        '            </button>' +
                        '            <button type="button" id="trigger' + v.id_post + '" class="btn btn-success btn-sm me-2 action-collapse"' +
                        '                    data-bs-toggle="collapse" aria-expanded="true"' +
                        '                    aria-controls="collapse-' + v.id_post + '"' +
                        '                    href="#collapse-' + v.id_post + '">' +
                        '                <i class="bi bi-chat-text me-1"></i>' + v.jml + ' komentar' +
                        '            </button>' +
                        '            <button type="button" class="btn btn-danger btn-sm" onclick="hapusPost(' + v.id_post + ')" data-id="' + v.id_post + '">' +
                        '                <i class="bi bi-trash me-1"></i> Hapus' +
                        '            </button>' +
                        '        </div>' +
                        '    </div>' +
                        '    <div id="collapse-' + v.id_post + '" class="p-2 collapse toggle-comment"' +
                        '         data-id="' + v.id_post + '" data-bs-parent="#parent' + v.id_post + '">' +
                        '        <hr class="m-0">' +
                        '        <div id="konten' + v.id_post + '" class="p-4">' +
                        '        </div>' +
                        '        <div id="loading' + v.id_post + '" class="text-center d-none">' +
                        '            <div class="spinner-grow"></div>' +
                        '        </div>';
                    if (v.jml == '0') {
                        card += '<div class="text-center" id="empty-comment">Tidak ada komentar</div>';
                    } else {
                        card += '<div id="loadmore' + v.id_post + '"' +
                            '     onclick="getComments(' + v.id_post + ')"' +
                            '     class="text-center mt-4 loadmore">' +
                            '    <div class="btn btn-default">Muat komentar lainnya ...</div>' +
                            '</div>';
                    }
                    card += '</div>' +
                        '</div>';
                });
            } else {
                card = '<div class="card card-default">' +
                    '<div class="card-body text-center">' +
                    ' <p>Tidak ada pengumuman</p>' +
                    '</div></div>';
            }

            $('#pengumuman').html(card);

            $('.toggle-comment').on('shown.bs.collapse', function (e) {
                var konten = $(this);
                var id = konten.data('id');
                var list = $(this).find('.media').length;
                if (list === 0) $(`#loadmore${id}`).click();
            });

            $('#komentarModal').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                $("#id-post").val(id);

                var isVisible = $(`#collapse-${id}`).hasClass('show');
                if (!isVisible) {
                    $(`#trigger${id}`).click();
                }
            });

            $('#balasanModal').on('show.bs.modal', function (e) {
                var id = $(e.relatedTarget).data('id');
                $("#id-comment").val(id);

                var isVisible = $(`#collapse-reply${id}`).hasClass('show');
                if (!isVisible) {
                    $(`#trigger-reply${id}`).click();
                }
            });

            $('#komentar').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                //console.log("data", $(this).serialize());
                var id = $(this).find('input[name=id_post]').val();

                $.ajax({
                    url: base_url + "pengumuman/savekomentar",
                    data: $(this).serialize(),
                    method: 'POST',
                    dataType: "JSON",
                    success: function (response) {
                        console.log("result", response);
                        $('#komentarModal').modal('hide').data('bs.modal', null);
                        $('#komentarModal').on('hidden', function () {
                            $(this).data('modal', null);
                        });
                        addComments(id, response, false)
                        //window.location.href = base_url + 'pengumuman';
                    },
                    error: function (xhr, status, error) {
                        $('#komentarModal').modal('hide').data('bs.modal', null);
                        $('#komentarModal').on('hidden', function () {
                            $(this).data('modal', null);
                        });
                        showDangerToast('Error, komentar tidak terkirim');
                    }
                });
            });

            $('#balasan').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                //console.log("data", $(this).serialize());
                var id = $(this).find('input[name=id_comment]').val();

                $.ajax({
                    url: base_url + "pengumuman/savebalasan",
                    data: $(this).serialize(),
                    method: 'POST',
                    dataType: "JSON",
                    success: function (response) {
                        console.log("result", response);
                        $('#balasanModal').modal('hide').data('bs.modal', null);
                        $('#balasanModal').on('hidden', function () {
                            $(this).data('modal', null);
                        });
                        //window.location.href = base_url + 'pengumuman';
                        addReplies(id, response, false)
                    },
                    error: function (xhr, status, error) {
                        $('#balasanModal').modal('hide').data('bs.modal', null);
                        $('#balasanModal').on('hidden', function () {
                            $(this).data('modal', null);
                        });
                        showDangerToast('Error, balasan tidak terkirim');
                    }
                });
            });

        }

        function getPosts() {
            $(`#loading-post`).removeClass('d-none');
            $(`#loadmore-post`).addClass('d-none');

            setTimeout(function () {
                $.ajax({
                    url: base_url + "pengumuman/getpost/" + halaman,
                    type: "GET",
                    dataType: "JSON",
                    timeout: 10000,
                    success: function (response) {
                        //console.log("result", response);
                        halaman += 1;

                        if (response && response.length === 5) {
                            $(`#loadmore-post`).removeClass('d-none');
                        }
                        $(`#loading-post`).addClass('d-none');
                        if (response) {
                            addPosts(response);
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        $(`#loading-post`).addClass('d-none');
                        if (halaman > 0) {
                            $(`#loadmore-post`).removeClass('d-none');
                        }
                    }
                });
            }, 500);
        }

        function hapusPost(idPost) {
            Swal.fire({
                title: "Hapus Pengumuman",
                text: "Pengumuman ini akan dihapus",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "HAPUS"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "pengumuman/hapuspost/" + idPost,
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Pengumuman dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Pengumuman tidak dihapus",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            Swal.fire({
                                title: "ERROR",
                                text: "Pengumuman tidak dihapus",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            });
        }

        function hapusKomentar(idKomentar) {
            Swal.fire({
                title: "Hapus Komentar",
                text: "Komentar ini akan dihapus",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "HAPUS"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "pengumuman/hapuskomentar/" + idKomentar,
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Komentar dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                        $(`#parent-reply${idKomentar}`).remove();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Komentar tidak dihapus",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            Swal.fire({
                                title: "ERROR",
                                text: "Komentar tidak dihapus",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            });
        }

        function hapusReply(idReply) {
            Swal.fire({
                title: "Hapus Balasan",
                text: "Balasan ini akan dihapus",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "HAPUS"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "pengumuman/hapusbalasan/" + idReply,
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Balasan dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                        $(`.media${idReply}`).remove();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Balasan tidak dihapus",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            Swal.fire({
                                title: "ERROR",
                                text: "Balasan tidak dihapus",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            });
        }

        function hapusLogAktivitas() {
            Swal.fire({
                title: "Hapus Log",
                text: "Semua log aktifitas akan dihapus",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "HAPUS"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "dashboard/hapuslog/",
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Semua log dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                        window.location.reload(true);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Gagal menghapus log",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            Swal.fire({
                                title: "ERROR",
                                text: "Gagal menghapus log",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            });
        }

        /*
         * SEMUA
         * log_type:
         * 1 = login
         * 2 = logout
         * 3 = add
         * 4 = edit
         * 5 = hapus
         *
         * SISWA
         * 6  = membuka materi
         * 7  = menutup materi
         * 8  = mengerjakan tugas
         * 9  = mengirimkan tugas
         * 10 = mengerjakan ujian
         * 11 = menyelesaikan ujian
         * 12 = melihat nilai
         */

        $(document).ready(async function () {
            ajaxcsrf();

            var colorBg = ['success', 'success', 'secondary', 'primary', 'warning', 'danger',
                'primary', "warning", "primary", "success", "primary", "success", "warning"
            ];

            function load_log() {
                $.ajax({
                    url: base_url + "dashboard/getlog",
                    data: { page: 1, limit: 10, filter: ['1', '2', '3'], search: '' },
                    method: "GET",
                    success: function (data) {
                        if (data.logs.length > 0) {
                            var ul = '<ul class="products-list product-list-in-card ps-2 pe-2">';
                            $.each(data.logs, function (key, value) {
                                const nama_lengkap = value?.first_name === value?.last_name ? value?.first_name : value?.first_name + ' ' + value?.last_name
                                var nama = value.id_group === '1' ? value.first_name : nama_lengkap;
                                var clr = colorBg[value.log_type];
                                var tgl = formatTanggal(value.log_time);//new Date('02/12/2018');
                                const guru = data.guru.find(g => g.id === value.id_user);
                                const siswa = data.siswa.find(s => s.id === value.id_user);
                                const admin = data.admins.find(a => a.id === value.id_user);
                                const fotoUser = guru ? guru.foto :
                                    siswa ? siswa.foto :
                                        admin ? admin.foto : null;
                                const foto = base_url + (fotoUser ? fotoUser : '');
                                const initial = nama.charAt(0).toUpperCase();
                                ul += '  <li class="item">' +
                                    '    <div class="d-flex align-items-center" style="line-height: 1.3">' +
                                    '      <div data-siswa="' + initial + '" class="d-flex justify-content-center align-items-center bg-' + clr + ' rounded-circle" style="width: 50px; height: 50px">' +
                                    '        <img class="avatar-log rounded-circle" src="' + foto + '" width="44" height="44"  alt=""/></div>' +
                                    //'<button class="btn btn-circle-sm btn-'+clr+' media-left">' + nama.charAt(0).toUpperCase()+'</button>' +
                                    '      <div class="flex-grow-1 ms-2">' +
                                    '        <span>' + nama + '</span>' +
                                    '        <br />' +
                                    '        <span class="text-' + clr + ' text-sm">' + value.log_desc + '</span>' +
                                    '        <br />' +
                                    '        <span class="text-xs text-muted">' + tgl + '</span>' +
                                    '      </div>' +
                                    '    </div>' +
                                    '  </li>';

                            });
                            ul += '</ul>';
                            $('#log-list').html(ul);

                            $(".avatar-log").each(async function (i, v) {
                                const loaded = () => {
                                    return new Promise((resolve) => {
                                        let obj = { pos: i };

                                        $(v)
                                            .on("error", function () {
                                                obj.error = true;
                                                const div = $(this).parent();
                                                const initial = div.data('siswa')
                                                $(this).remove()
                                                div.html(`<span class="text-lg">${initial}</span>`);
                                                resolve(obj);
                                            })
                                            .on("load", function () {
                                                obj.error = false;

                                                // ==== Resize gambar dengan rasio asli ====
                                                const canvas = document.createElement("canvas");
                                                const ctx = canvas.getContext("2d");

                                                const targetW = 100; // lebar tetap
                                                const ratio = v.naturalHeight / v.naturalWidth;
                                                const targetH = Math.round(targetW * ratio);

                                                canvas.width = targetW;
                                                canvas.height = targetH;

                                                ctx.drawImage(v, 0, 0, targetW, targetH);

                                                // Convert ke PNG
                                                v.src = canvas.toDataURL("image/png");

                                                resolve(obj);
                                            });
                                    });
                                };

                                await loaded();
                            });
                        }
                    }
                });
            }

            load_log();
            getPosts();

            const response = await ajaxGet('dashboard/get_status_siswa');
            await createTableFromJSON(response);

            // Realtime stats
            function loadRealtimeStats() {
                $.ajax({
                    url: base_url + 'dashboard/get_realtime_stats',
                    method: 'GET',
                    dataType: 'JSON',
                    success: function (response) {
                        // Update Info Box
                        if (response.info_box) {
                            response.info_box.forEach(function (item) {
                                let id = 'total-' + item.title.toLowerCase().replace(/ /g, '-');
                                let el = document.getElementById(id);
                                if (el) el.innerHTML = '<b>' + item.total + '</b>';
                            });
                        }

                        // Update Ujian Box
                        if (response.ujian_box) {
                            response.ujian_box.forEach(function (item) {
                                let id = 'total-ujian-' + item.title.toLowerCase().replace(/ /g, '-');
                                let el = document.getElementById(id);
                                if (el) el.innerHTML = item.total;
                            });
                        }
                    }
                });
            }

            loadRealtimeStats();
            setInterval(loadRealtimeStats, 30 * 1000);

            // Profile Incomplete Modal Logic - PRIORITY OVER WELCOME MODAL
            const isProfileIncomplete = <?= (isset($is_profile_incomplete) && $is_profile_incomplete) ? 'true' : 'false' ?>;
            const modalProfileIncomplete = $('#modalProfileIncomplete');

            if (isProfileIncomplete && modalProfileIncomplete.length) {
                // Show immediately - cannot be dismissed
                setTimeout(() => {
                    modalProfileIncomplete.modal('show');
                }, 500);

                // Prevent any way to close the modal
                modalProfileIncomplete.on('hide.bs.modal', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                });

                // If user tries to press Escape or click outside, redirect anyway
                $(document).on('keydown', function (e) {
                    if (e.key === 'Escape' && isProfileIncomplete) {
                        window.location.href = base_url + 'panitia';
                    }
                });
            } else {
                // Welcome Modal Logic for Pengawas (only show if profile is complete)
                const modalWelcome = $('#modalWelcomePengawas');
                if (modalWelcome.length) {
                    const hasSeenWelcome = localStorage.getItem('seenWelcomePengawas');
                    if (!hasSeenWelcome) {
                        setTimeout(() => {
                            modalWelcome.modal('show');
                        }, 1000);
                    }

                    modalWelcome.on('hide.bs.modal', function () {
                        if ($('#dontShowAgain').is(':checked')) {
                            localStorage.setItem('seenWelcomePengawas', 'true');
                        }
                    });
                }
            }
        })

    </script>

<?php echo view('_templates/dashboard/_footer'); ?>

