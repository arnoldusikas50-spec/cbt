<?php


function sortir($a, $b)
{
    return strcmp($a->tgl_mulai, $b->tgl_mulai) * -1;
}

function my_array_unique($array, $keep_key_assoc = false)
{
    $duplicate_keys = array();
    $tmp = array();

    foreach ($array as $key => $val) {
        // convert objects to arrays, in_array() does not support objects
        if (is_object($val))
            $val = (array) $val;

        if (!in_array($val['id_jadwal'], $tmp))
            $tmp[] = $val['id_jadwal'];
        else
            $duplicate_keys[] = $key;
    }

    foreach ($duplicate_keys as $key)
        unset($array[$key]);

    return $keep_key_assoc ? $array : array_values($array);
}

?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $judul ?? 'Rekap' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Rekap</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title text-bold"><i class="bi bi-database-check me-2"></i><?= $subjudul ?></h3>
                    <div class="card-tools ms-auto">
                        <button type="button" id="reload" class="btn btn-sm btn-warning shadow-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-default-info border-info my-shadow-sm mb-4" role="alert">
                        <h5 class="alert-heading fw-bold"><i class="bi bi-info-circle me-2"></i> Informasi Rekap</h5>
                        <p class="mb-2">Rekap hanya menyalin data ujian sesuai status ujian sudah/belum koreksi. Rekap
                            bertujuan agar:</p>
                        <ul class="mb-2 ps-4">
                            <li>Hasil ujian siswa bisa dihapus jika diperlukan</li>
                            <li>Jadwal ujian bisa dihapus jika diperlukan</li>
                            <li>Bank soal bisa dihapus jika diperlukan</li>
                        </ul>
                        <p class="mb-0 small">Klik tombol <span class="badge bg-warning text-dark"><i
                                    class="bi bi-arrow-repeat me-1"></i> Reload</span> untuk mendapatkan status terbaru
                        </p>
                    </div>
                    <div class="row">
                        <?php if (!isset($tp_active) || !isset($smt_active)): ?>
                            <div class="col-12">
                                <div class="alert alert-default-warning border-warning" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i> Tahun Pelajaran atau Semester belum di
                                    set
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-4 mb-3">
                                <label for="filterby" class="form-label fw-bold">Filter By</label>
                                <select name="filterby" id="filterby" class="form-select shadow-sm">
                                    <option value="all" selected="selected">Semua</option>
                                    <option value="0">Jadwal belum rekap</option>
                                    <option value="1">Jadwal sudah rekap</option>
                                    <option value="2">Rekap tanpa jadwal</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <button id="hapusterpilih" onclick="bulk_delete()" type="button"
                                    class="btn btn-danger shadow-sm me-2" data-bs-toggle="tooltip" title="Hapus Terpilih"
                                    disabled="disabled">
                                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                                </button>
                                <button id="rekapterpilih" onclick="bulk_rekap()" type="button"
                                    class="btn btn-success shadow-sm me-2" data-bs-toggle="tooltip" title="Rekap Terpilih"
                                    disabled="disabled">
                                    <i class="bi bi-database me-1"></i> Rekap Terpilih
                                </button>
                            </div>

                            <div class="table-responsive" id="konten"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2 fw-bold text-primary">Memproses Data...</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= form_open('', array('id' => 'bulk')) ?>
<?= form_close(); ?>

<script>
    const infoRekap = <?= json_encode($rekaps) ?>;
    const infoKelas = <?= json_encode($kelas_guru ?? []) ?>;
    let lastUser;
    const currentUser = '<?= $session_id ?>'

    const limitAntrian = 1000
    const limitPost = 20
    let totalSoalSiswa = 0;
    let pagePos = 1;
    let idsRekap = {};

    let db;
    let request = indexedDB.open("GarudaCbtRekap", 2);
    const table = {
        cbtPengawas: 'cbt_pengawas',
        masterKelas: 'master_kelas',
        cbtJadwal: 'cbt_jadwal',
        rekapJadwal: 'cbt_rekap_jadwal',
    }

    request.onerror = function (event) {
        console.log("Gagal membuka database", event);
    };

    request.onsuccess = function (event) {
        db = event.target.result;
        //console.log("Database berhasil dibuka");
    };

    request.onupgradeneeded = function (event) {
        db = event.target.result;
        if (!db.objectStoreNames.contains(table.cbtPengawas)) {
            const soalStore = db.createObjectStore(table.cbtPengawas, { keyPath: "id_pengawas" });
        }
        if (!db.objectStoreNames.contains(table.masterKelas)) {
            const soalStore = db.createObjectStore(table.masterKelas, { keyPath: "id_kelas" });
        }
        if (!db.objectStoreNames.contains(table.cbtJadwal)) {
            const soalStore = db.createObjectStore(table.cbtJadwal, { keyPath: "id_jadwal" });
            soalStore.createIndex("id_jadwal", "id_jadwal", { unique: false });
        }
        if (!db.objectStoreNames.contains(table.rekapJadwal)) {
            const soalStore = db.createObjectStore(table.rekapJadwal, { keyPath: "id_rekap" });
            soalStore.createIndex("id_jadwal", "id_jadwal", { unique: false });
        }
        console.log("Object store berhasil dibuat");
    };

    let jadwals;
    let filterBy = 'all';
    $(document).ready(async function () {
        ajaxcsrf();

        $("#flashdata").fadeTo(8000, 500).slideUp(500, function () {
            $("#flashdata").slideUp(500);
        });

        const opsiFilterBy = $("#filterby");
        opsiFilterBy.change(function () {
            filterBy = $(this).val();
            $('#loading').removeClass('d-none');
            populateTableJadwal()
        });

        $("#reload").on("click", function () {
            reloadPage(true)
        })

        lastUser = localStorage.getItem('garudaCBT.sessionId')
        if (!lastUser) {
            localStorage.setItem('garudaCBT.sessionId', currentUser);
        }

        setTimeout(async function () {
            const last = new Date(localStorage.getItem('garudaCBT.rekap'))
            if (lastUser !== currentUser) {
                localStorage.setItem('garudaCBT.sessionId', currentUser);
                reloadPage(true)
            } else {
                reloadPage(last === null)
            }
        }, 500)
    })

    function reloadPage(force, showDialog = true) {
        if (force) {
            pagePos = 1;
            getJadwal(showDialog)
        } else {
            const last = new Date(localStorage.getItem('garudaCBT.rekap'))
            const now = new Date();
            const cekMenit = minutesDiff(now, last)
            console.log('diff', cekMenit)
            if (cekMenit > 15) {
                pagePos = 1;
                getJadwal(showDialog)
            } else {
                Swal.fire({
                    text: "Memuat jadwal ujian....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                populateTableJadwal()
            }
        }
    }

    async function getJadwal(showDialog, url = 'cbtrekap/get_jadwal') {
        if (showDialog) {
            Swal.fire({
                text: "Memuat jadwal ujian....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        localStorage.setItem('garudaCBT.rekap', new Date())

        const response = await ajaxGet(url);
        await saveJadwal(response)
    }

    let tempcbtJadwal = [];
    let tempcbtPengawas = [];
    let tempcbtKelas = [];
    async function saveJadwal(response) {
        if (pagePos === 1) {
            tempcbtJadwal = [];
            tempcbtPengawas = [];
            tempcbtKelas = [];
        }
        console.log("res", response);

        for (const jadwal of response.jadwal) {
            for (const sesisiswa of jadwal.sesisiswa) {
                sesisiswa.cbtsiswa = response.cbtsiswa.find(cbt => cbt.id_jadwal === jadwal.id_jadwal && cbt.id_siswa === sesisiswa.siswa_id);
            }
        }

        tempcbtJadwal.push(...response.jadwal);
        tempcbtPengawas.push(...response.pengawas);

        const map = new Map();
        [...tempcbtKelas, ...response.kelases].forEach(obj => {
            map.set(obj.id_kelas, obj);
        });
        tempcbtKelas = Array.from(map.values());

        if (tempcbtJadwal.length < response.total) {
            await getJadwal(false, `cbtrekap/get_jadwal/${++pagePos}`);
        } else {
            await addItems(table.cbtJadwal, tempcbtJadwal);
            await addItems(table.cbtPengawas, tempcbtPengawas);

            pagePos = 1;
            await getRekap(`cbtrekap/get_rekap`)
        }
    }

    async function getRekap(url) {
        const response = await ajaxGet(url);
        await saveRekap(response)
    }

    let temprekapJadwal = [];
    async function saveRekap(response) {
        if (pagePos === 1) {
            temprekapJadwal = [];
        }
        console.log("res", response);
        temprekapJadwal.push(...response.rekap);

        const map = new Map();
        [...tempcbtKelas, ...response.kelases].forEach(obj => {
            map.set(obj.id_kelas, obj);
        });
        tempcbtKelas = Array.from(map.values());

        if (temprekapJadwal.length < response.total) {
            await getRekap(`cbtrekap/get_rekap/${++pagePos}`);
        } else {
            await addItems(table.rekapJadwal, temprekapJadwal);
            await addItems(table.masterKelas, tempcbtKelas);
            await populateTableJadwal();
        }
    }

    async function populateTableJadwal() {
        const cbtjadwals = await getItems(table.cbtJadwal) || [];
        const arrIdJadwal = cbtjadwals.map(item => item.id_jadwal);

        const pengawas = await getItems(table.cbtPengawas || [])
        const rekaps = await getItems(table.rekapJadwal) || [];
        const idJadwalRekap = rekaps.map(item => item.id_jadwal);
        idsRekap = new Map(rekaps.map(item => [item.id_jadwal, item.id_rekap]));
        const kelases = await getItems(table.masterKelas) || [];
        const kelasMap = Object.fromEntries(kelases.map(k => [k.id_kelas, k]));

        const jadwalNotRekap = cbtjadwals.filter(jadwal => !idJadwalRekap.includes(jadwal.id_jadwal))
        const jadwalWithRekap = cbtjadwals.filter(jadwal => idJadwalRekap.includes(jadwal.id_jadwal))
        const rekapNonJadwal = rekaps.filter(jadwal => !arrIdJadwal.includes(jadwal.id_jadwal))

        switch (filterBy) {
            case '0':
                jadwals = [
                    ...jadwalNotRekap.map(jadwal => ({
                        ...jadwal,
                        pengawas: pengawas.filter(p => p.id_jadwal === jadwal.id_jadwal),
                        rekap: 0,
                    })),
                ]
                break;
            case '1':
                jadwals = [
                    ...jadwalWithRekap.map(jadwal => ({
                        ...jadwal,
                        pengawas: pengawas.filter(p => p.id_jadwal === jadwal.id_jadwal),
                        rekap: 1,
                    })),
                ]
                break;
            case '2':
                jadwals = [
                    ...rekapNonJadwal.map(jadwal => ({
                        ...jadwal,
                        rekap: 2,
                    }))
                ]
                break;
            case 'all':
                jadwals = [
                    ...jadwalWithRekap.map(jadwal => ({
                        ...jadwal,
                        pengawas: pengawas.filter(p => p.id_jadwal === jadwal.id_jadwal),
                        rekap: 1,
                    })),
                    ...jadwalNotRekap.map(jadwal => ({
                        ...jadwal,
                        pengawas: pengawas.filter(p => p.id_jadwal === jadwal.id_jadwal),
                        rekap: 0,
                    })),
                    ...rekapNonJadwal.map(jadwal => ({
                        ...jadwal,
                        rekap: 2,
                    })),
                ]
                break;
        }

        jadwals.sort((a, b) => {
            const dateDiff = new Date(b.tgl_mulai) - new Date(a.tgl_mulai);
            return dateDiff !== 0 ? dateDiff : b.id_jadwal - a.id_jadwal;
        });

        const konten = document.getElementById("konten");
        let tableHtml = ``;

        if (jadwals.length > 0) {
            tableHtml = `<table id="data-table" class="w-100 table table-bordered table-sm">
                                <thead>
                                <tr class="bg-light">
                                    <th style="min-width: 40px" class="align-middle">
                                        <div class="text-center">
                                            <input id="check-all" class="check-all" type="checkbox">
                                        </div>
                                    </th>
                                    <th class="text-center align-middle p-0" style="min-width: 40px; height: 50px">No.</th>
                                    <th class="align-middle">Bank Soal</th>
                                    <th class="align-middle">Mapel</th>
                                    <th class="align-middle">Pelaksanaan</th>
                                    <th class="text-center align-middle p-0"><span>Rekap</span></th>
                                    <th class="text-center align-middle">Kelas</th>
                                    <th class="text-center align-middle p-0" style="min-width: 160px"><span>Aksi</span></th>
                                </tr>
                                </thead>
                                <tbody id="table-body">`;
            jadwals.forEach((item, no) => {
                const genap = (no + 1) % 2 === 0;
                const bgRow = genap ? '#e9ecef' : '#fff'
                const idJadwal = item.id_jadwal;

                const bankKelasIds = item.bank_kelas
                    .map(bk => bk.kelas_id)
                    .filter(id => id !== null);

                const hanyaPg = item.bank?.tampil_esai == 0 &&
                    item.bank?.tampil_isian == 0 &&
                    item.bank?.tampil_jodohkan == 0 &&
                    item.bank?.tampil_kompleks == 0 &&
                    item.bank?.tampil_pg > 0;

                let allSiswa = [];
                let all_mengerjakan = [];
                let all_dikoreksi = [];

                let all_kelas_siswa = {};
                let all_kelas_mengerjakan = {};
                let all_kelas_dikoreksi = {};
                if (item.rekap === 2) {
                    allSiswa = item.rekapsiswa;
                    all_mengerjakan = allSiswa?.filter(s => s.mulai && !(item.bank?.soal_agama !== '-' && item.bank?.soal_agama !== '0' && item.bank?.soal_agama !== s.siswa?.agama));
                    all_dikoreksi = hanyaPg ? all_mengerjakan : all_mengerjakan?.filter(s => s.nilai_input?.dikoreksi === '1');
                    bankKelasIds.forEach(kelasId => {
                        all_kelas_siswa[kelasId] = allSiswa?.filter(s => s.kelas_id === kelasId);
                        all_kelas_mengerjakan[kelasId] = all_mengerjakan?.filter(s => s.kelas_id === kelasId && s.mulai != null);
                        all_kelas_dikoreksi[kelasId] = all_mengerjakan?.filter(s => s.kelas_id === kelasId && s.nilai_input?.dikoreksi === '1');
                    });
                } else {
                    allSiswa = item.sesisiswa;
                    all_mengerjakan = allSiswa?.filter(s => s.cbtsiswa?.mulai && !(item.bank?.soal_agama !== '-' && item.bank?.soal_agama !== '0' && item.bank?.soal_agama !== s.siswa?.agama));
                    all_dikoreksi = all_mengerjakan?.filter(s => s.cbtsiswa?.nilai_input?.dikoreksi === '1');
                    bankKelasIds.forEach(kelasId => {
                        all_kelas_siswa[kelasId] = allSiswa?.filter(s => s.kelas_id === kelasId);
                        all_kelas_mengerjakan[kelasId] = all_mengerjakan?.filter(s => s.kelas_id === kelasId && s.cbtsiswa?.mulai != null);
                        all_kelas_dikoreksi[kelasId] = all_mengerjakan?.filter(s => s.kelas_id === kelasId && s.cbtsiswa?.nilai_input?.dikoreksi === '1');
                    });
                }
                let agm = item.bank?.soal_agama !== '-' && item.bank?.soal_agama !== '0' ? item.bank?.soal_agama : 'Umum';
                all_mengerjakan?.sort((a, b) => a.id_siswa - b.id_siswa);

                const jumlahKelas = item.bank_kelas
                let kelasbank = [];
                if (jumlahKelas && Array.isArray(jumlahKelas) && jumlahKelas.length > 0) {
                    jumlahKelas.forEach(j => {
                        const k = kelasMap[j.kelas_id];
                        if (!k) return;
                        kelasbank.push({
                            id: k.id_kelas,
                            nama: k.nama_kelas,
                            siswa: all_kelas_siswa[k.id_kelas],
                            mengerjakan: all_kelas_mengerjakan[k.id_kelas],
                            dikoreksi: all_kelas_dikoreksi[k.id_kelas]
                        });
                    });
                }

                const mulai = new Date(item.tgl_mulai).toLocaleDateString('id-ID', {
                    year: "numeric",
                    month: "short",
                    day: "2-digit",
                })
                let selesai = mulai
                if (item.tgl_selesai) {
                    selesai = new Date(item.tgl_selesai).toLocaleDateString('id-ID', {
                        year: "numeric",
                        month: "short",
                        day: "2-digit",
                    })
                }
                const pelaksanaan = mulai === selesai
                    ? `<span class="badge" style="font-size: 90%;font-weight: 500;">${mulai}</span>`
                    : `<span class="badge" style="font-size: 90%;font-weight: 500;">${mulai}</span> ~ <span class="badge" style="font-size: 90%;font-weight: 500;">${selesai}</span>`

                const rows = kelasbank.length;
                for (let i = 0; i < rows; i++) {
                    const filterSiswaKelas = kelasbank[i]?.siswa || [];
                    const mengerjakan = kelasbank[i]?.mengerjakan || [];
                    const dikoreksi = kelasbank[i]?.dikoreksi || [];

                    let rekap = ``;
                    if (item.rekap === 1) {
                        rekap = `<div class="btn-group" data-jadwal="${item.id_jadwal}">
                                <button class="btn btn-primary btn-xs" onclick="backup(${idJadwal})">Ulangi</button>
                                <button class="btn btn-success btn-xs btn-aksi" data-type="a">Analisis</button>
                                <button class="btn btn-danger btn-xs btn-aksi" data-type="h">Hapus</button></div>`;
                    } else if (item.rekap === 2) {
                        rekap = `<div class="btn-group" data-jadwal="${item.id_jadwal}">
                                <button class="btn btn-success btn-xs btn-aksi" data-type="a">Analisis</button>
                                <button class="btn btn-danger btn-xs btn-aksi" data-type="h">Hapus</button>
                            </div>`;
                    } else {
                        rekap = `<div class="btn-group">
                                <button class="btn btn-primary btn-xs" onclick="backup(${idJadwal})">Rekap</button>
                                <button class="btn btn-danger btn-xs btn-aksi" data-type="h">Hapus</button>
                            </div>`;
                    }

                    let badgeJenis = hanyaPg ? 'bg-success' : 'bg-danger';
                    let btnKoreksi = hanyaPg ? 'btn-success' : 'btn-warning';
                    //const klsDikoreksi = hanyaPg ? all_mengerjakan.length : all_dikoreksi.length;
                    if (!hanyaPg) {
                        btnKoreksi = mengerjakan.length === dikoreksi.length
                            ? 'btn-success'
                            : 'btn-warning';

                        badgeJenis = mengerjakan.length === dikoreksi.length
                            ? 'bg-success'
                            : 'bg-danger';

                        rekap += '<br>';
                        rekap += `<span class="badge ${badgeJenis}">Dikoreksi ${all_dikoreksi?.length} dari ${all_mengerjakan?.length}</span>`;
                        rekap += '<br>';
                        rekap += `<span class="badge ${badgeJenis}">Terdapat soal Essay</span>`;
                    }

                    const emptySiswa = mengerjakan.length === 0;
                    let enableEkspor = item.rekap > 0;
                    const enableBtnKoreksi = enableEkspor && !emptySiswa ? '' : 'disabled'
                    const enableBtnEkspor = enableEkspor && !emptySiswa ? '' : 'disabled'

                    if (i === 0) {
                        tableHtml += `<tr style="background: ${bgRow}">
            <td rowspan="${rows}">
                 <div class="text-center">
                      <input class="check" value="${item.id_jadwal}" type="checkbox">
                 </div>
            </td>
            <td rowspan="${rows}" class="text-center">${no + 1}</td>
            <td rowspan="${rows}">${item.kode_jenis || item.jenis.kode_jenis} <br /> <span class="text-xs text-bold">${item.bank?.bank_kode}</span></td>
            <td rowspan="${rows}">${item.nama_mapel || item.bank.mapel.nama_mapel}<br /><span class="badge bg-info">${agm || 'Umum'}</span></td>
            <td rowspan="${rows}">${pelaksanaan}</td>
            <td rowspan="${rows}" class="text-center">${rekap}</td>
            <td class="text-center"><span class="badge" style="font-size: 90%;font-weight: 500;">${kelasbank[i]?.nama}</span>
                <br/><span class="badge bg-info">${mengerjakan.length} siswa</span>
            </td>
            <td class="text-center align-middle" data-jadwal="${item.id_jadwal}" data-kelas="${kelasbank[i]?.id}">
                <button class="btn ${btnKoreksi} btn-xs me-1 btn-aksi" data-type="k" ${enableBtnKoreksi}>Koreksi</button>
                <button class="btn btn-info btn-xs me-1 btn-aksi" data-type="e" ${enableBtnEkspor}>Ekspor</button></td>
        </tr>`;
                    } else {
                        tableHtml += `<tr style="background: ${bgRow}">
                                    <td class="text-center"><span class="badge" style="font-size: 90%;font-weight: 500;">${kelasbank[i]?.nama}</span>
                                        <br/><span class="badge bg-info">${mengerjakan.length} siswa</span>
                                    </td>
                                    <td class="text-center align-middle" data-jadwal="${item.id_jadwal}" data-kelas="${kelasbank[i]?.id}">
                                        <button class="btn ${btnKoreksi} btn-xs me-1 btn-aksi" data-type="k" ${enableBtnKoreksi}>Koreksi</button>
                                        <button class="btn btn-info btn-xs me-1 btn-aksi" data-type="e" ${enableBtnEkspor}>Ekspor</button></td>
                                 </tr>`;
                    }
                }
            });
            tableHtml += '</tbody></table>';
        } else {
            tableHtml = `<div class="alert alert-default-warning shadow align-content-center" role="alert">
                                Belum ada jadwal penilaian untuk Tahun Pelajaran <b><?= $tp_active->tahun ?></b>
                                Semester: <b><?= $smt_active->smt ?></b>
                            </div>`;
        }
        konten.innerHTML = tableHtml;

        $('.btn-aksi').click(function () {
            const idJadwal = $(this).parent().data('jadwal')
            const idKelas = $(this).parent().data('kelas')
            const type = $(this).data('type')

            const linkAnalisa = `${base_url}cbtanalisis/analisis?id_jadwal=${idJadwal}`
            const linkKoreksi = `${base_url}cbtrekap/view_koreksi?id_jadwal=${idJadwal}&id_kelas=${idKelas}`
            const linkEkspor = `${base_url}cbtrekap/ekspor_rekap?id_jadwal=${idJadwal}&id_kelas=${idKelas}`
            if (type === 'k') {
                window.location.href = linkKoreksi;
            } else if (type === 'a') {
                window.location.href = linkAnalisa;
            } else if (type === 'e') {
                window.location.href = linkEkspor;
            } else if (type === 'h') {
                var jsonId = [`${idJadwal}`];
                delete_rekap(jsonId)
            } else {
                // nothing
            }
            //console.log('click', type, idJadwal, idKelas)
        });

        $("#check-all").on("click", function () {
            if (this.checked) {
                $(".check").each(function () {
                    this.checked = true;
                    $("#check-all").prop("checked", true);
                    $("#hapusterpilih").removeAttr("disabled");
                    $("#rekapterpilih").removeAttr("disabled");
                });
            } else {
                $(".check").each(function () {
                    this.checked = false;
                    $("#check-all").prop("checked", false);
                    $("#hapusterpilih").attr("disabled", "disabled");
                    $("#rekapterpilih").attr("disabled", "disabled");
                });
            }
        });

        $("#data-table tbody").on("click", "tr .check", function () {
            var check = $("#data-table tbody tr .check").length;
            var checked = $("#data-table tbody tr .check:checked").length;
            if (check === checked) {
                $("#check-all").prop("checked", true);
            } else {
                $("#check-all").prop("checked", false);
            }

            if (checked === 0) {
                $("#hapusterpilih").attr("disabled", "disabled");
                $("#rekapterpilih").attr("disabled", "disabled");
            } else {
                $("#hapusterpilih").removeAttr("disabled");
                $("#rekapterpilih").removeAttr("disabled");
            }
        });

        $('#loading').addClass('d-none');
        swal.close()
    }

    function bulk_rekap() {
        const $rows = $('#data-table').find('tr'), headers = $rows.splice(0, 1); // header rows
        const jsonId = [];
        $rows.each((i, row) => {
            const $selected = $(row).find('.check:checked');
            if ($selected.val() != null) {
                jsonId.push($selected.val());
            }
        });

        if ($("#data-table tbody tr .check:checked").length === 0) {
            Swal.fire({
                title: "Gagal",
                text: "Tidak ada data yang dipilih",
                icon: "error"
            });
        } else {
            backup(jsonId)
        }
    }

    async function backup(ids) {
        Swal.fire({
            title: "Merekap Nilai Siswa",
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        let arrId = ids;
        if (!Array.isArray(ids)) {
            arrId = [`${ids}`];
        }

        let arrTp = []
        let arrSmt = []
        let success = false
        let response;
        try {
            const jadwal = jadwals.filter(j => arrId.includes(j.id_jadwal.toString()))
            const pages = Math.ceil(jadwal.length / 50)
            if (pages === 0) return

            for (let i = 0; i < pages; i++) {
                let pagePost = i + 1;
                const jadwalPost = await paginate(pagePost, 50, jadwal)

                let formData = new FormData($('#bulk')[0]);
                let arrIdBank = [];
                jadwalPost.forEach((item, index) => {
                    console.log('loop', item)
                    const idRekap = idsRekap.get(item.id_jadwal) || '';
                    arrIdBank.push(item.id_bank)
                    if (!arrTp.includes(item.id_tp)) arrTp.push(item.id_tp)
                    if (!arrSmt.includes(item.id_smt)) arrSmt.push(item.id_smt)
                    const propsjadwal = {
                        "id_jadwal": item.id_jadwal,
                        "id_bank": item.id_bank,
                        "id_jenis": item.id_jadwal,
                        "tgl_mulai": item.tgl_mulai,
                        "tgl_selesai": item.tgl_selesai,
                        "jam_ke": item.jam_ke,
                        "durasi_ujian": item.durasi_ujian,
                        "acak_soal": item.acak_soal,
                        "acak_opsi": item.acak_opsi,
                        "kode_jenis": item.kode_jenis,
                        "pelanggaran": item.pelanggaran
                    }

                    const ids = {
                        'id_rekap': idRekap,
                        'id_tp': item.id_tp,
                        'tp': item.tp || item.tp.tahun,
                        'id_smt': item.id_smt,
                        'smt': item.smt || item.smt.nama_smt,
                        'id_jadwal': item.id_jadwal,
                        'id_bank': item.id_bank,
                        'kode_bank': item.bank.kode_bank,
                        'id_jenis': item.id_jenis,
                        'tgl_mulai': item.tgl_mulai,
                        'tgl_selesai': item.tgl_selesai,
                    }
                    formData.append(`rekap[${index}][ids]`, JSON.stringify(ids));
                    formData.append(`rekap[${index}][jadwal]`, JSON.stringify(propsjadwal));
                    formData.append(`rekap[${index}][pengawas]`, JSON.stringify(item.pengawas));
                });

                formData.append(`ids_bank`, JSON.stringify(arrIdBank));
                formData.append(`ids_jadwal`, JSON.stringify(arrId));

                // rekap jadwal dan mengambil data siswa
                response = await ajaxPost('cbtrekap/rekap', formData);
                console.log('rekap', response)

                if (response.success) {
                    if (pagePost >= pages) {
                        // menyimpan data siswa
                        await backupNilaiSiswa(arrId)
                    }
                } else {
                    Swal.fire({
                        title: "Gagal",
                        text: "Gagal merekap jadwal",
                        icon: "error"
                    });
                }
            }
        } catch (error) {
            console.error('Error in postBackup:', error);
            Swal.fire({
                title: "Gagal",
                text: error,
                icon: "error"
            });
        }
    }

    async function paginate(pageNumber, itemsPerPage, arrayData) {
        const startIndex = (pageNumber - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        return arrayData.slice(startIndex, endIndex);
    }

    async function backupNilaiSiswa(idJadwal) {
        let formData = new FormData($('#bulk')[0]);
        formData.append(`idjadwal`, JSON.stringify(idJadwal))
        const response = await ajaxPost('cbtrekap/rekap_nilai_siswa', formData)
        console.log('backupNilai', response)

        if (response.success) {
            if (response.cbt > 0 || response.rekap_siswa > 0) {
                await reloadPage(true, false);
            } else {
                await populateTableJadwal()
            }
        } else {
            Swal.fire({
                title: "Rekap nilai siswa",
                text: "Rekap nilai siswa gagal",
                icon: "error",
            });
        }
    }

    function bulk_delete() {
        const $rows = $('#data-table').find('tr'), headers = $rows.splice(0, 1); // header rows
        var jsonId = [];
        $rows.each((i, row) => {
            const $selected = $(row).find('.check:checked');
            if ($selected.val() != null) jsonId.push($selected.val());
        });

        if ($("#data-table tbody tr .check:checked").length === 0) {
            Swal.fire({
                title: "Gagal",
                text: "Tidak ada data yang dipilih",
                icon: "error"
            });
        } else {
            delete_rekap(jsonId)
        }
    }

    function delete_rekap(jsonId) {
        Swal.fire({
            title: "Hapus Rekap jadwal terpilih!",
            html: "Jika jadwal CBT sudah dihapus maka anda tidak bisa lagi melakukan rekap<br><span class='text-danger'><b>Pastikan anda telah mendownload semua nilai siswa</b></span>",
            //text: "Semua nilai siswa akan dihapus!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "YA, Hapus!"
        }).then(result => {
            if (result.value) {
                Swal.fire({
                    title: "Menghapus rekap Nilai Siswa",
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: base_url + 'cbtrekap/hapusrekap',
                    data: $('#bulk').serialize() + '&ids=' + JSON.stringify(jsonId),
                    type: "POST",
                    success: async function (respon) {
                        if (respon.success) {
                            await deleteItems(table.rekapJadwal, 'id_jadwal', jsonId)
                            await populateTableJadwal()
                            swal.close()
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                text: "Tidak ada data yang dihapus",
                                icon: "error"
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: "Gagal",
                            text: "Ada data yang sedang digunakan",
                            icon: "error"
                        });
                    }
                });
            }
        });
    }

    async function getItems(table) {
        if (!db) {
            console.warn("Database belum siap, menunggu...");
            await waitForDbReady();
        }
        return new Promise((resolve, reject) => {
            const transaction = db.transaction([table], "readonly");
            const objectStore = transaction.objectStore(table);
            const request = objectStore.getAll(); // Mengambil semua data

            request.onsuccess = function (event) {
                const allItems = event.target.result;
                resolve(allItems);
            };

            request.onerror = function (event) {
                reject("Gagal membaca data: " + event.target.errorCode);
            };
        })
    }

    async function addItems(table, items) {
        if (!db) {
            console.warn("Database belum siap, menunggu...");
            await waitForDbReady();
        }
        await clearItems(table)

        const transaction = db.transaction([table], "readwrite");
        const objectStore = transaction.objectStore(table);

        items.forEach(item => {
            const request = objectStore.add(item);

            request.onsuccess = function (event) { };

            request.onerror = function (event) {
                console.log(`Gagal menambahkan data: ${item}`, event);
            };
        });

        transaction.oncomplete = function () { };

        transaction.onerror = function (event) {
            console.log("Terjadi kesalahan pada transaksi", event);
        };
    }

    async function clearItems(table) {
        if (!db) {
            console.warn("Database belum siap, menunggu...");
            await waitForDbReady();
        }
        const transaction = db.transaction([table], "readwrite");
        const objectStore = transaction.objectStore(table);
        const request = objectStore.clear(); // Menghapus semua data

        request.onsuccess = function (event) { };

        request.onerror = function (event) {
            console.log("Gagal menghapus semua data", event);
        };
    }

    let countFetch = 0;

    async function saveItems(table, items, keyField) {
        if (!db) {
            console.warn("Database belum siap, menunggu...");
            await waitForDbReady();
        }

        //countFetch++;
        const transaction = db.transaction([table], "readwrite");
        const objectStore = transaction.objectStore(table);

        items.forEach(item => {
            // Ambil nilai kunci dari setiap item
            const key = item[keyField];

            // Cek apakah item sudah ada berdasarkan kunci
            const checkRequest = objectStore.get(key);

            checkRequest.onsuccess = function (event) {
                if (checkRequest.result) {
                    // Data ditemukan, lakukan update
                    const updateRequest = objectStore.put(item);
                    updateRequest.onsuccess = function () {
                        //console.log(`Data dengan key ${key} berhasil diperbarui.`);
                    };
                    updateRequest.onerror = function (event) {
                        console.log(`Gagal memperbarui data dengan key ${key}:`, event);
                    };
                } else {
                    // Data tidak ditemukan, lakukan insert
                    const insertRequest = objectStore.add(item);
                    insertRequest.onsuccess = function () {
                        //console.log(`Data dengan key ${key} berhasil ditambahkan.`);
                    };
                    insertRequest.onerror = function (event) {
                        console.log(`Gagal menambahkan data dengan key ${key}:`, event);
                    };
                }
            };

            checkRequest.onerror = function (event) {
                console.log(`Gagal memeriksa data dengan key ${key}:`, event);
            };
        });

        transaction.oncomplete = () => {
            //if (countFetch === 2) {
            //    countFetch = 0;
            //    dbEvents.dispatchEvent(new Event("dbUpdated"));
            //    channel.postMessage("dbUpdated");
            //}
        };
    }

    async function deleteItems(table, indexName, ids) {
        if (!db) {
            console.warn("Database belum siap, menunggu...");
            await waitForDbReady();
        }

        const transaction = db.transaction([table], "readwrite");
        const objectStore = transaction.objectStore(table);

        ids.forEach((indexValue) => {
            const index = objectStore.index(indexName); // Mendapatkan index berdasarkan nama
            const request = index.getAllKeys(indexValue); // Mencari semua key path berdasarkan nilai index

            request.onsuccess = function (event) {
                const keys = event.target.result;

                if (keys.length === 0) {
                    console.log(`Tidak ada item ditemukan untuk index '${indexName}' dengan nilai '${indexValue}'.`);
                    return;
                }

                keys.forEach((key) => {
                    const deleteRequest = objectStore.delete(key);

                    deleteRequest.onsuccess = function () {
                        //console.log(`Item dengan key '${key}' berhasil dihapus.`);
                    };

                    deleteRequest.onerror = function (event) {
                        console.error(`Gagal menghapus item dengan key '${key}':`, event.target.errorCode);
                    };
                });
            };

            request.onerror = function (event) {
                console.error(`Gagal mengambil key berdasarkan index '${indexName}':`, event.target.errorCode);
            };
        });

        transaction.oncomplete = function () {
            console.log("Semua operasi penghapusan selesai.");
        };

        transaction.onerror = function (event) {
            console.error("Terjadi kesalahan pada transaksi:", event.target.errorCode);
        };
    }

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
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 100)
        });
    }

    async function ajaxPost(url, formData) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    method: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (respon) {
                        if (respon.success) {
                            resolve(respon); // Resolusi Promise ketika sukses
                        } else {
                            reject(respon.message); // Rejeksi Promise ketika ada error
                        }
                    },
                    error: function (xhr, status, error) {
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 200)
        });
    }

    function minutesDiff(now, last) {
        let differenceValue = (now.getTime() - last.getTime()) / 1000;
        differenceValue /= 60;
        return Math.abs(Math.round(differenceValue));
    }

    function hitungWaktu(durasi) {
        if (durasi.mulai && durasi.selesai) {
            let lamanya = durasi.lama_ujian;
            if (!lamanya || !lamanya.includes(':')) {
                const mulai = new Date(durasi.mulai).getTime();
                const selesai = new Date(durasi.selesai).getTime();
                const lama = selesai - mulai;

                durasi.lama_ujian = new Date(lama).toISOString().substring(11, 19); // Format H:i:s
            }
        }
        return durasi;
    }

    function waitForDbReady() {
        return new Promise((resolve) => {
            const interval = setInterval(() => {
                if (db) {
                    clearInterval(interval);
                    resolve();
                }
            }, 200);
        });
    }

</script>