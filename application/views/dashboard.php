<!-- Content Wrapper. Contains page content -->
<main class="app-main bg-light pt-4">
    <section class="content-header p-0 d-flex align-items-end fade-in-up">
        <div class="container-fluid ps-0 pe-0 pb-0 pt-4" style="background-color: rgba(255,255,255,0.7)">
            <?php if ($this->session->userdata('school_uuid')): ?>
                <div class="row m-0 mb-3">
                    <div class="col-12">
                        <div
                            class="alert alert-warning border-2 border-warning shadow d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                <span class="fs-5">Mode Admin Sekolah:
                                    <b><?= $this->session->userdata('school_name') ?></b></span>
                            </div>
                            <a href="<?= base_url('sync/stop_impersonate') ?>" class="btn btn-dark fw-bold px-4">
                                <i class="bi bi-arrow-left-circle-fill me-2"></i> KEMBALI
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row m-0">
                <?php foreach ($info_box as $info): ?>
                    <div class="col-md-2 col-4"> <!-- ikasmedia -->
                        <a href="<?= base_url() . $info->url ?>" class="text-decoration-none">
                            <div class="shadow small-box grad-<?= $info->box ?>">
                                <div class="inner">
                                    <h5 class="mb-0 text-white"
                                        id="total-<?= strtolower(str_replace(' ', '-', $info->title)) ?>">
                                        <b><?= $info->total; ?></b>
                                    </h5>
                                    <span class="text-white"><?= $info->title; ?></span>
                                </div>
                                <div class="icon">
                                    <?php
                                    $iconMap = [
                                        'users' => 'people-fill',
                                        'chalkboard-teacher' => 'person-video3',
                                        'book' => 'book',
                                        'user' => 'person-fill',
                                        'graduation-cap' => 'mortarboard-fill',
                                        'university' => 'bank',
                                        'tags' => 'tags-fill',
                                        'list-alt' => 'list-task',
                                        'tasks' => 'list-check',
                                        'envelope' => 'envelope-fill',
                                        'comments' => 'chat-dots-fill',
                                        'clipboard-list' => 'clipboard-data',
                                        'cogs' => 'gears',
                                        'cog' => 'gear-fill',
                                        'desktop' => 'pc-display',
                                        'server' => 'hdd-network',
                                        'bullhorn' => 'megaphone-fill',
                                        'pie-chart' => 'pie-chart-fill',
                                        'home' => 'house-door-fill',
                                        'print' => 'printer-fill'
                                    ];
                                    $biIcon = isset($iconMap[$info->icon]) ? $iconMap[$info->icon] : $info->icon;
                                    ?>
                                    <i class="bi bi-<?= $biIcon ?>"></i>
                                </div>
                                <div class="small-box-footer" style="background: rgba(0,0,0,0.1)">
                                    Detail <i class="bi bi-arrow-right-circle-fill"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Main content -->
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-danger my-shadow">
                        <div class="card-header">
                            <h4 class="card-title">Penilaian</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($ujian_box as $info): ?>
                                    <div class="col-4 col-md-3 col-lg-2" style="min-height: 60px">
                                        <a href="<?= base_url() . $info->url ?>" class="text-decoration-none">
                                            <div class="info-box bg-white border border-<?= $info->box ?> p-1 shadow-sm"
                                                style="min-height: 60px; border-width: 2px !important">
                                                <div class="info-box-content p-1 text-dark">
                                                    <span class="info-box-text"
                                                        style="font-size: 0.8rem"><?= $info->title; ?></span>
                                                    <h5 class="info-box-number m-0"
                                                        id="total-ujian-<?= strtolower(str_replace(' ', '-', $info->title)) ?>">
                                                        <?= $info->total; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-6 col-md-3 col-lg-2" style="min-height: 60px">
                                    <a href="<?= base_url('cbttoken') ?>" class="text-decoration-none">
                                        <div class="info-box bg-white border border-indigo p-1 shadow-sm"
                                            style="min-height: 60px; border-width: 2px !important">
                                            <div class="info-box-content p-1 text-dark">
                                                <span class="info-box-text" style="font-size: 0.8rem">Token
                                                    <small class="float-end d-none" id="interval">-- : -- :
                                                        --</small></span>
                                                <h5 class="info-box-number m-0 font-monospace" id="token-view">
                                                    <?= $token->token != null ? $token->token : '- - - - - -' ?>
                                                </h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-center grad-info text-white p-2 rounded shadow-sm"><b>JADWAL
                                            PENILAIAN HARI INI</b>
                                    </h6> <!-- ikasmedia -->
                                    <div class="col-12 table-responsive" id="table-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ELEARNING -->
                <div class="col-md-4">
                    <!-- <div class="card card-primary my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                ELEARNING HARI INI
                            </div>
                            <div class="card-tools ms-auto">
                                <a href="<?= base_url('elearning/jadwal') ?>" type="button" onclick=""
                                    class="btn btn-sm btn-light">
                                    <i class="bi bi-arrow-right-circle-fill"></i>
                                </a>
                            </div>
                        </div>
                        <?php if (count($jadwal_kbm) > 0): ?>
                            <div class="card-body p-2">
                                <div class="row">
                                    <span class="ms-3 text-bold">Kelas</span>
                                </div>
                                <hr class="m-0" />
                                <div class="row">
                                    <div class="col-5 col-sm-3">
                                        <div class="nav flex-column nav-tabs h-100">
                                            <?php
                                            $no = 1;
                                            foreach ($kelases as $ky => $kelas):
                                                $active = $no == 1 ? 'active' : '';
                                                ?>
                                                <a class="nav-link <?= $active ?>" href="#tab_<?= $ky ?>"
                                                    data-bs-toggle="tab"><?= $kelas ?></a>
                                                <?php $no++; endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-7 col-sm-9">
                                        <div class="tab-content">
                                            <div class="tab-content">
                                                <?php
                                                $no = 1;
                                                foreach ($kelases as $ky => $kelas):
                                                    $active = $no == 1 ? 'active' : '';
                                                    ?>
                                                    <div class="tab-pane fade show <?= $active ?>" id="tab_<?= $ky ?>">
                                                        <div class="table-responsive">
                                                            <?php
                                                            $jadwalKbm = $jadwal_kbm[$ky] ?? [];
                                                            $table = "<table class='my-timetable w-100 border-bottom' id='tbl'>";
                                                            $tbody = "";
                                                            $idRow = 0;
                                                            $jadwalRows = 0;
                                                            $arrStart = [];

                                                            foreach ($times as $jam) {
                                                                $startTime = isset($jam['start']) ? $jam['start'] : '';
                                                                $arrStart[] = $startTime;
                                                                $rowSpanJam = isset($jam['span']) ? $jam['span'] : 12;

                                                                if ($idRow === 0) {
                                                                    $firstRow = "<tr class='row$idRow'>";

                                                                    $td0 = "<td class='column-time text-center align-top text-xs text-bold times p-0 frozen-column' rowspan='$rowSpanJam'>$startTime</td>";
                                                                    $td1 = "<td class='frozen-column-2' style='width: 30px; height: 10px;'></td>";
                                                                    $td2 = "<td class='row0'></td>";

                                                                    $firstRow .= $td0 . $td1 . $td2;
                                                                    $firstRow .= "</tr>";
                                                                    $tbody .= $firstRow;
                                                                } else {
                                                                    $minute = explode(':', $startTime)[1];
                                                                    $row = "<tr class='row$idRow'>";

                                                                    if ($idRow % 2 !== 0) {
                                                                        $row = "<tr class='row$idRow' data-time='$startTime'>";
                                                                    }

                                                                    if ($minute % 30 === 0) {
                                                                        $td0 = "<td class='column-time text-center align-top text-xs text-bold times p-0 frozen-column' rowspan='$rowSpanJam'>$startTime</td>";
                                                                        $row .= $td0;
                                                                    }

                                                                    $td1 = "<td class='frozen-column-2' style='width: 30px; height: 6px;'></td>";
                                                                    $row .= $td1;

                                                                    $tbody .= $row . "</tr>";
                                                                }

                                                                $scheduleRow = "<tr class='row" . ($idRow + 1) . "'>";

                                                                if ($idRow % 2 !== 0) {
                                                                    $scheduleRow = "<tr class='row" . ($idRow + 1) . "' data-time='$startTime'>";
                                                                }

                                                                $td1 = "<td class='frozen-column-3' style='width: 30px; height: 6px;'></td>";
                                                                $scheduleRow .= $td1;

                                                                $arr = isset($jadwalKbm->detail[$startTime]) ? $jadwalKbm->detail[$startTime] : null;
                                                                $rowSpan = $arr ? $arr['rows'] * 2 : 2;
                                                                $jadwalRows = $rowSpan;

                                                                $td = "<td class='align-middle row" . ($idRow + 1) . " text-xs' rowspan='$rowSpan'>";

                                                                $div = "<div class='h-100 pb-1' style='padding: 0 .1rem;'>";

                                                                if ($arr) {
                                                                    $flex = $rowSpan > 6 ? 'flex-column justify-content-center' : 'flex-row justify-content-between';
                                                                    $alertDivClass = 'alert alert-default-' . (isset($arr['id_mapel']) && $arr['id_mapel'] === '-1'
                                                                        ? 'warning' : (isset($arr['id_mapel']) && $arr['id_mapel'] === '-2' ? 'success'
                                                                            : (isset($arr['kode']) && $arr['kode'] ? 'info' : 'secondary'))) . " p-1 h-100 m-0 d-flex $flex";
                                                                    $alertDiv = "<div class='$alertDivClass'>";

                                                                    if (isset($arr['id_mapel']) && $arr['id_mapel'] === '-1') {
                                                                        $alertDiv .= "<div class='text-xs ms-1'>Istirahat</div>";
                                                                        //"<div class='text-xs mx-1'>" . ($arr['rows'] * 5) . "m</div>";
                                                                        $alertDiv .= "<div class='d-inline-block mx-1 text-nowrap'>{$arr['dari']} - {$arr['sampai']}</div>";
                                                                    } elseif (isset($arr['id_mapel']) && $arr['id_mapel'] === '-2') {
                                                                        $alertDiv .= "<div class='text-xs ms-1'>Upacara Bendera</div>";
                                                                        $alertDiv .= "<div class='d-inline-block mx-1 text-nowrap'>{$arr['dari']} - {$arr['sampai']}</div>";
                                                                    } else {
                                                                        if (isset($arr['kode']) && $arr['kode']) {
                                                                            $alertDiv .= "<div class='text-bold ms-1'>{$arr['kode']}</div>";
                                                                            $alertDiv .= "<div class='d-inline-block mx-1 text-nowrap'>{$arr['dari']} - {$arr['sampai']}</div>";
                                                                        }
                                                                    }
                                                                    $alertDiv .= "</div>";
                                                                    $div .= $alertDiv;
                                                                    $div .= "</div>";
                                                                    $td .= $div;
                                                                }
                                                                $scheduleRow .= $td;
                                                                $scheduleRow .= "</tr>";
                                                                $tbody .= $scheduleRow;
                                                                $jadwalRows -= 2;
                                                                $idRow += 2;
                                                            }
                                                            $table .= $tbody . "</table>";
                                                            echo $table;
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <?php $no++; endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card-body">
                                Tidak ada jadwal hari ini
                            </div>
                        <?php endif; ?>
                    </div> -->
                    <div class="card card-success my-shadow">
                        <div class="card-header">
                            <div class="card-title">Aktifitas</div>
                            <div class="card-tools ms-auto">
                                <a href="<?= base_url('datalogs') ?>" type="button" onclick=""
                                    class="btn btn-sm btn-light">
                                    <i class="bi bi-arrow-right-circle-fill"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="log-list">
                                <p>Tidak ada aktifitas</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ELEARNING -->
                <div class="col-md-8">
                    <div class="card card-danger my-shadow mb-3">
                        <div class="card-header">
                            <div class="card-title"> <!-- ikasmedia -->
                                INFO/PENGUMUMAN
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="konten-pengumuman">
                                <div id="pengumuman">
                                </div>
                                <div id="loading-post" class="text-center d-none">
                                    <br />
                                    <div class="spinner-border spinner-border-sm" role="status"></div> Loading....
                                </div>
                                <div id="loadmore-post" onclick="getPosts()" class="text-center mt-4 loadmore d-none">
                                    <div class="btn btn-warning">Muat Pengumuman lainnya ...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
    </div>

    <div class="modal fade" id="komentarModal" tabindex="-1" role="dialog" aria-labelledby="komentarLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="komentarLabel">Tulis Komentar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body d-flex align-items-start">
                    <img class="img-fluid img-circle rounded-circle flex-shrink-0"
                        src="<?= base_url('assets/img/user.png') ?>" alt="Alt Text"
                        style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="w-100 ms-3">
                        <?= form_open('create', array('id' => 'komentar')) ?>
                        <input type="hidden" id="id-post" name="id_post" value="">
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" name="text" placeholder="Tulis komentar ..." class="form-control"
                                required>
                            <button type="submit" class="btn btn-success shadow-sm px-3"><i
                                    class="bi bi-send-fill"></i></button>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="balasanModal" tabindex="-1" role="dialog" aria-labelledby="balasanLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="balasanLabel">Tulis Balasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body d-flex align-items-start">
                    <img class="img-fluid img-circle rounded-circle flex-shrink-0"
                        src="<?= base_url('assets/img/siswa.png') ?>" alt="Alt Text"
                        style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="w-100 ms-3">
                        <?= form_open('create', array('id' => 'balasan')) ?>
                        <input type="hidden" id="id-comment" name="id_comment" value="">
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" name="text" placeholder="Tulis balasan ...." class="form-control"
                                required>
                            <button type="submit" class="btn btn-success shadow-sm px-3"><i
                                    class="bi bi-send-fill"></i></button>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                </div>
            </div>
        </div>
    </div>

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
                container.innerHTML = `<table class="w-100 table-bordered"><tr><td class=" alert alert-default-warning text-center">Tidak ada jadwal penilaian hari ini</td></tr></table>`;
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
        <thead class="table-light">
            <tr>
                <th class="text-center align-middle" rowspan="2">RUANG</th>
                <th class="text-center align-middle" rowspan="2">SESI</th>
                <th class="text-center align-middle" rowspan="2">JAM KE</th>
                <th class="text-center align-middle" rowspan="2">MAPEL<br>(BANK SOAL)</th>
                <th class="text-center align-middle" rowspan="2">KELAS</th>
                <th class="text-center align-middle" rowspan="2">PENGAWAS</th>
                <th class="text-center align-middle" rowspan="2">JML SISWA</th>
                <th class="text-center align-middle" colspan="3">STATUS SISWA</th>
            </tr>
            <tr>
                <th class="text-center align-middle">BLM<br/>LOGIN</th>
                <th class="text-center align-middle">SDG<br/>LOGIN</th>
                <th class="text-center align-middle">SELESAI</th>
            </tr>
        </thead>`;

            let bodyHtml = '<tbody>';
            const usedRuangIds = new Set();
            const usedSesiKeys = new Set();
            let currentRow = 0;
            let curSesi = 0;
            let bgRow = '';
            let bold = false;

            groupedSchedules.forEach(group => {
                const { ruang_sesi, jam_ke, jadwal, pengawas, peserta, blm_login, sdg_login, selesai } = group;
                let infoMapel = ``;
                for (const jad of jadwal) {
                    infoMapel += `${jad.bank.mapel.kode} (<span class="badge bg-secondary text-white">${jad.bank.bank_kode}</span>)<br/>`;
                }
                const bank_kelas = getUniqueClassNames(ruang_sesi, jadwal).join(', ');
                //const bank_kode = jadwal.map(j => `<span class="badge bg-light">${j.bank.bank_kode}</span>`).join('<br>');
                const idsGuru = jadwal.map(j => j.bank.bank_guru_id).join(',');

                bodyHtml += `<tr>`;
                // Ruang column with rowspan
                const ruangId = ruang_sesi.ruang_id;
                if (!usedRuangIds.has(ruangId)) {
                    const rowspanValue = rowCounts.get(String(ruangId)) || 1;
                    bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle border-bottom-bold">${ruang_sesi.nama_ruang}</td>`;
                    usedRuangIds.add(ruangId);
                    bold = true
                } else {
                    bold = false
                }

                var genap = curSesi % 2 === 0;
                const bBold = bold ? 'border-top-bold' : '';
                // Sesi column with rowspan
                const sesiKey = `${ruang_sesi.ruang_id}-${ruang_sesi.sesi_id}`;
                if (!usedSesiKeys.has(sesiKey)) {
                    bgRow = genap ? 'bg-light' : '';
                    const rowspanValue = sesiRowCounts.get(sesiKey) || 1;
                    bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-sesi ${bgRow} ${bBold}">${ruang_sesi.nama_sesi}</td>`;
                    usedSesiKeys.add(sesiKey);
                    curSesi++;
                }

                bodyHtml += `
            <td class="text-center align-middle td-jam ${bgRow} ${bBold}">${jam_ke}</td>
            <td class="align-middle td-mapel jadwal ${bgRow} ${bBold}">${infoMapel}</td>
            <td class="text-center align-middle ${bgRow} ${bBold}">${bank_kelas}</td>
            <td class="text-center align-middle td-pengawas ${bgRow} ${bBold}" data-guru="${idsGuru}">${pengawas.join('<br>')}</td>
            <td class="text-center align-middle text-bold ${bgRow} ${bBold}">${peserta}</td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="badge bg-danger">${blm_login}</span></td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="badge bg-warning text-dark">${sdg_login}</span></td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="badge bg-success">${selesai}</span></td>
        </tr>`;

                currentRow++;
            });

            bodyHtml += '</tbody>';
            return `<table class="table table-bordered table-sm w-100">${headerHtml}${bodyHtml}</table>`;
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
                        '<div class="btn-circle-sm btn-success pt-1 me-2" style="width: 43px; height: 40px">A</div>'
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
                            '<div class="btn-circle-sm btn-success pt-1 me-2" style="width: 37px">A</div>';
                    } else {
                        if (v.dari_group == '2') {
                            dari = v.nama_guru;
                            foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/guru.png';
                            avatar = '<img class="rounded-circle border" src="' + foto + '" alt="Img" width="35px" height="35px">';
                        } else {
                            dari = v.nama_siswa;
                            foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                            avatar = '<img class="rounded-circle border" src="' + foto + '" alt="Img" width="35px" height="35px">';
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
                        avatar = v.foto != null ? '<img class="img-circle border" src="' + v.foto + '" alt="Img" width="50px" height="50px">' :
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

                    card += '<div class="post-item border-bottom pb-3 mb-3" id="parent' + v.id_post + '">' +
                        '    <div class="d-flex align-items-center mb-2">' +
                        avatar +
                        '        <div class="ms-3">' +
                        '            <h6 class="fw-bold fs-6 mb-0 text-dark">' + dari + '</h6>' +
                        '            <small class="text-muted text-xs">' + createTime(v.tanggal) + '</small>' +
                        '        </div>' +
                        '    </div>' +
                        '    <div class="mb-3 text-secondary text-sm post-content" style="line-height: 1.6;">' + v.text + '</div>' +
                        '    <div class="d-flex align-items-center flex-wrap gap-2">' +
                        '            <button type="button" class="btn btn-light btn-sm rounded-pill px-3 text-muted btn-toggle shadow-sm border-0"' +
                        '                    data-id="' + v.id_post + '" data-bs-toggle="modal"' +
                        '                    data-bs-target="#komentarModal"><i class="bi bi-reply-fill me-1 text-primary"></i> Tulis Komentar' +
                        '            </button>' +
                        '            <button type="button" id="trigger' + v.id_post + '" class="btn btn-light btn-sm rounded-pill px-3 text-muted action-collapse shadow-sm border-0"' +
                        '                    data-bs-toggle="collapse" aria-expanded="true"' +
                        '                    aria-controls="collapse-' + v.id_post + '"' +
                        '                    href="#collapse-' + v.id_post + '">' +
                        '                <i class="bi bi-chat-dots me-1 text-success"></i> ' + v.jml + ' Komentar' +
                        '            </button>' +
                        '            <button type="button" class="btn btn-light btn-sm rounded-pill px-3 text-muted shadow-sm border-0 ms-auto" onclick="hapusPost(' + v.id_post + ')" data-id="' + v.id_post + '">' +
                        '                <i class="bi bi-trash text-danger"></i>' +
                        '            </button>' +
                        '    </div>' +
                        '    <div id="collapse-' + v.id_post + '" class="p-2 collapse toggle-comment"' +
                        '         data-id="' + v.id_post + '" data-bs-parent="#parent' + v.id_post + '">' +
                        '        <div id="konten' + v.id_post + '" class="p-3 bg-light rounded mt-3">' +
                        '        </div>' +
                        '        <div id="loading' + v.id_post + '" class="text-center d-none">' +
                        '            <div class="spinner-grow spinner-grow-sm text-primary"></div>' +
                        '        </div>';
                    if (v.jml == '0') {
                        card += '<div class="text-center text-muted small mt-2" id="empty-comment">Belum ada komentar</div>';
                    } else {
                        card += '<div id="loadmore' + v.id_post + '"' +
                            '     onclick="getComments(' + v.id_post + ')"' +
                            '     class="text-center mt-3 loadmore">' +
                            '    <div class="btn btn-link btn-sm text-decoration-none text-muted">Muat komentar lainnya ...</div>' +
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
                    },
                    error: function (xhr, status, error) {
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

            var colorBg = ['info', 'success', 'secondary', 'info', 'warning', 'danger',
                'primary', "secondary", "info", "success", "primary", "success", "indigo"
            ];

            function load_log() {
                $.ajax({
                    url: base_url + "dashboard/getlog",
                    data: { page: 1, limit: 10, filter: ['1', '2', '3'], search: '' },
                    method: "GET",
                    success: function (data) {
                        if (data.logs.length > 0) {
                            var timelineHtml = '<div class="activity-timeline w-100">';

                            $.each(data.logs, function (key, value) {
                                const nama_lengkap = value?.first_name === value?.last_name ? value?.first_name : value?.first_name + ' ' + value?.last_name
                                var nama = value.id_group === '1' ? value.first_name : nama_lengkap;
                                var clr = colorBg[value.log_type] || 'secondary';

                                var tgl = formatTanggal(value.log_time);
                                var timeAgo = jQuery.timeago(value.log_time);

                                // Determine icon based on log type
                                var icon = 'circle-fill';
                                switch (parseInt(value.log_type)) {
                                    case 1: icon = 'box-arrow-in-right'; break; // Login
                                    case 2: icon = 'box-arrow-right'; break; // Logout
                                    case 3: icon = 'plus-lg'; break; // Add
                                    case 4: icon = 'pencil'; break; // Edit
                                    case 5: icon = 'trash'; break; // Delete
                                    case 6: icon = 'book'; break; // Membuka Materi
                                    case 7: icon = 'book'; break; // Menutup Materi
                                    case 8: icon = 'pencil'; break; // Mengerjakan Tugas
                                    case 9: icon = 'send'; break; // Mengirimkan Tugas
                                    case 10: icon = 'file-earmark-text'; break; // Mengerjakan Ujian
                                    case 11: icon = 'check-all'; break; // Menyelesaikan Ujian
                                    case 12: icon = 'eye'; break; // Melihat Nilai
                                }

                                const isLast = key === data.logs.length - 1;
                                timelineHtml += `
                            <div class="d-flex pb-0 position-relative" style="min-height: 50px;">
                                <div class="d-flex flex-column align-items-center me-3" style="width: 40px;">
                                    <div class="rounded-circle bg-${clr} text-white d-flex align-items-center justify-content-center shadow-sm" 
                                         style="width: 36px; height: 36px; z-index: 2;">
                                        <i class="bi bi-${icon} text-sm"></i>
                                    </div>
                                    ${!isLast ? '<div class="flex-grow-1 bg-light" style="width: 2px; margin-top: -5px; margin-bottom: -5px;"></div>' : ''}
                                </div>
                                <div class="flex-grow-1 ps-1 pb-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                             <h6 class="fw-bold text-dark mb-0 text-sm text-truncate">${nama}</h6>
                                             <p class="text-secondary small mb-0 lh-sm mt-1">${value.log_desc}</p>
                                        </div>
                                        <small class="text-muted text-xs ms-2 text-nowrap mt-1"><i class="bi bi-clock me-1"></i>${timeAgo}</small>
                                    </div>
                                </div>
                            </div>`;
                            });

                            timelineHtml += '</div>';

                            $('#log-list').html(timelineHtml);
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
        })

    </script>