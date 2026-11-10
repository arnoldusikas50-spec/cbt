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

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>


<style>
    :root {
        /* Primary Colors */
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-200: #c7d2fe;
        --color-primary-300: #a5b4fc;
        --color-primary-400: #818cf8;
        --color-primary-500: #6366f1;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;
        --color-primary-800: #3730a3;
        --color-primary-900: #312e81;

        /* Success Colors */
        --color-success-50: #f0fdf4;
        --color-success-100: #dcfce7;
        --color-success-500: #22c55e;
        --color-success-600: #16a34a;
        --color-success-700: #15803d;

        /* Danger/Warning/Info */
        --color-danger-50: #fef2f2;
        --color-danger-100: #fee2e2;
        --color-danger-500: #ef4444;
        --color-danger-600: #dc2626;
        --color-danger-700: #b91c1c;

        --color-warning-50: #fffbeb;
        --color-warning-100: #fef3c7;
        --color-warning-500: #f59e0b;
        --color-warning-600: #d97706;

        --color-info-50: #eff6ff;
        --color-info-100: #dbeafe;
        --color-info-500: #3b82f6;
        --color-info-600: #2563eb;

        /* Neutral Colors */
        --color-neutral-50: #f8fafc;
        --color-neutral-100: #f1f5f9;
        --color-neutral-200: #e2e8f0;
        --color-neutral-300: #cbd5e1;
        --color-neutral-400: #94a3b8;
        --color-neutral-500: #64748b;
        --color-neutral-600: #475569;
        --color-neutral-700: #334155;
        --color-neutral-800: #1e293b;
        --color-neutral-900: #0f172a;

        /* Typography */
        --font-family-base: 'Inter', 'Outfit', -apple-system, sans-serif;
        --font-size-xs: 0.72rem;
        --font-size-sm: 0.875rem;
        --font-size-base: 1rem;
        --font-size-lg: 1.125rem;
        --font-weight-semibold: 600;
        --font-weight-bold: 700;

        /* Spacing & Radius */
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-full: 9999px;
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: var(--font-family-base);
        background-color: var(--color-neutral-50) !important;
    }

    .pas-card {
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .pas-card:hover {
        transform: translateY(-2px);
    }

    .pas-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem 2rem;
        background: #ffffff;
        border-bottom: 1px solid var(--color-neutral-100);
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-bold);
        margin: 0;
        color: var(--color-neutral-800) !important;
    }

    .pas-card__title-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        background: var(--color-primary-50);
        color: var(--color-primary-600);
        border-radius: var(--radius-lg);
        font-size: 1.25rem;
    }

    .pas-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        font-size: var(--font-size-sm);
        font-weight: 500;
        border-radius: var(--radius-lg);
        border: none;
        cursor: pointer;
        transition: all var(--transition-base);
        white-space: nowrap;
    }

    .pas-btn--primary {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: white;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
    }

    .pas-btn--primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.35);
        color: white;
    }

    .pas-btn--danger { background: var(--color-danger-500); color: white; }
    .pas-btn--danger:hover { background: var(--color-danger-600); }

    .pas-btn--success { background: var(--color-success-500); color: white; }
    .pas-btn--success:hover { background: var(--color-success-600); }

    .pas-btn--info { background: var(--color-info-500); color: white; }
    .pas-btn--info:hover { background: var(--color-info-600); }

    .pas-btn--warning { background: var(--color-warning-500); color: white; }
    .pas-btn--warning:hover { background: var(--color-warning-600); }


    .pas-btn--white {
        background: white;
        color: var(--color-neutral-700);
        border: 1px solid var(--color-neutral-200);
    }

    .pas-btn--white:hover {
        background: var(--color-neutral-50);
        border-color: var(--color-neutral-300);
    }

    .pas-badge {
        padding: 0.25rem 0.75rem;
        font-size: var(--font-size-xs);
        font-weight: 600;
        border-radius: var(--radius-full);
        display: inline-flex;
        align-items: center;
    }

    .pas-badge--primary { background: var(--color-primary-50); color: var(--color-primary-700); }
    .pas-badge--success { background: var(--color-success-50); color: var(--color-success-700); }
    .pas-badge--info { background: var(--color-info-50); color: var(--color-info-700); }
    .pas-badge--warning { background: var(--color-warning-50); color: var(--color-warning-700); }
    .pas-badge--danger { background: var(--color-danger-50); color: var(--color-danger-700); }
    .pas-badge--neutral { background: var(--color-neutral-100); color: var(--color-neutral-600); }

    .pas-form-select {
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-neutral-300);
        padding: 0.5rem 1rem;
        font-size: var(--font-size-sm);
        background-color: white;
        transition: all 0.2s;
    }

    .pas-form-select:focus {
        border-color: var(--color-primary-400);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .table-rekap {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--color-neutral-200) !important;
        background: white;
    }

    .table-rekap thead th {
        background-color: var(--color-neutral-50) !important;
        color: var(--color-neutral-500) !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 12px 10px !important;
        border-color: var(--color-neutral-200) !important;
        text-align: center;
        vertical-align: middle !important;
    }

    .table-rekap tbody tr {
        transition: all 0.2s ease;
    }

    .table-rekap tbody tr:hover {
        background-color: var(--color-primary-50) !important;
    }

    .table-rekap tbody td {
        padding: 12px 10px !important;
        vertical-align: middle !important;
        border-color: var(--color-neutral-200) !important;
        color: var(--color-neutral-700);
        font-size: 0.9rem;
    }

    .card-rekap-info {
        background: white;
        border-radius: 12px;
        padding: 10px;
        border: 1px solid var(--color-neutral-200);
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .rekap-title { font-weight: 700; color: var(--color-neutral-800); font-size: 0.9rem; }
    .rekap-subtitle { font-size: 0.75rem; color: var(--color-neutral-500); font-weight: 600; }
    
    .check-custom { width: 18px; height: 18px; cursor: pointer; }
</style>

<!-- Content Header -->
<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark fs-4" style="font-family: 'Inter', sans-serif; letter-spacing: -0.5px;">
                    <span class="text-primary-gradient"><i class="bi bi-calendar-check-fill me-2"></i></span><?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="pas-card mb-4">
                <div class="pas-card__header">
                    <div class="pas-card__title">
                        <div class="pas-card__title-icon">
                            <i class="bi bi-database-check"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold"><?= $subjudul ?></h5>
                            <div style="font-size: 0.75rem; opacity: 0.8; font-weight: normal;">Manajemen rekaman hasil dan penjadwalan</div>
                        </div>
                    </div>
                    <div class="pas-card__actions">
                        <button type="button" id="reload" class="pas-btn pas-btn--white py-2 shadow-sm">
                            <i class="bi bi-arrow-clockwise"></i> Reload
                        </button>
                    </div>
                </div>
                <div class="pas-card__body p-4">

                    <div class="alert alert-default-info border-info shadow-sm mb-4 rounded-3" role="alert">
                        <div class="d-flex">
                            <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                            <div>
                                <h5 class="alert-heading fw-bold mb-2">Informasi Rekap</h5>
                                <p class="mb-2">Rekap hanya menyalin data ujian sesuai status ujian sudah/belum koreksi. Rekap bertujuan agar:</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-check2-circle text-success me-2"></i>
                                            <span class="small fw-600">Hasil ujian siswa bisa dihapus aman</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-check2-circle text-success me-2"></i>
                                            <span class="small fw-600">Jadwal ujian bisa dikelola ulang</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-check2-circle text-success me-2"></i>
                                            <span class="small fw-600">Data bank soal tetap terjaga</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="mb-0 mt-2 small text-muted">
                                    <i class="bi bi-lightbulb me-1"></i> Klik tombol <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i> Reload</span> untuk mendapatkan status terbaru.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row align-items-end">
                        <?php if (!isset($tp_active) || !isset($smt_active)): ?>
                            <div class="col-12">
                                <div class="alert alert-default-warning border-warning" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i> Tahun Pelajaran atau Semester belum di set
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-md-3 mb-3">
                                <label for="filterby" class="form-label small fw-bold text-secondary text-uppercase">Filter Status</label>
                                <select name="filterby" id="filterby" class="pas-form-select shadow-sm" style="border-radius: 10px; width: 100%;">
                                    <option value="all" selected="selected">Semua Jadwal</option>
                                    <option value="0">Belum Direkap</option>
                                    <option value="1">Sudah Direkap</option>
                                    <option value="2">Hanya Rekap (Tanpa Jadwal)</option>
                                </select>
                            </div>
                            <div class="col-md-9 mb-3 text-md-end">
                                <button id="hapusterpilih" onclick="bulk_delete()" type="button"
                                    class="pas-btn pas-btn--danger me-2" data-bs-toggle="tooltip" title="Hapus Terpilih"
                                    disabled="disabled">
                                    <i class="bi bi-trash-fill"></i> Hapus Terpilih
                                </button>
                                <button id="rekapterpilih" onclick="bulk_rekap()" type="button"
                                    class="pas-btn pas-btn--success" data-bs-toggle="tooltip" title="Rekap Terpilih"
                                    disabled="disabled">
                                    <i class="bi bi-database-fill-add"></i> Rekap Terpilih
                                </button>
                            </div>

                            <div class="col-12">
                                <div class="table-responsive" id="konten"></div>
                            </div>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

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

        // Delegated event listener for action buttons
        $(document).on('click', '.btn-aksi', function (e) {
            e.preventDefault();
            console.log('Button clicked!'); // Debug log
            const idJadwal = $(this).closest('[data-jadwal]').data('jadwal');
            const idKelas = $(this).closest('[data-kelas]').data('kelas');
            const type = $(this).data('type');
            console.log('Type:', type, 'ID Jadwal:', idJadwal, 'ID Kelas:', idKelas); // Debug log

            const linkAnalisa = `${base_url}cbtanalisis/analisis?id_jadwal=${idJadwal}`;
            const linkKoreksi = `${base_url}cbtrekap/view_koreksi?id_jadwal=${idJadwal}&id_kelas=${idKelas}`;
            const linkEkspor = `${base_url}cbtrekap/ekspor_rekap?id_jadwal=${idJadwal}&id_kelas=${idKelas}`;
            
            if (type === 'k') {
                window.location.href = linkKoreksi;
            } else if (type === 'a') {
                window.location.href = linkAnalisa;
            } else if (type === 'e') {
                window.location.href = linkEkspor;
            } else if (type === 'h') {
                var jsonId = [`${idJadwal}`];
                delete_rekap(jsonId);
            }
        });

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


        for (const jadwal of response.jadwal) {
            for (const sesisiswa of jadwal.sesisiswa) {
                // Use == for loose comparison to handle string vs int type differences
                sesisiswa.cbtsiswa = response.cbtsiswa.find(cbt => cbt.id_jadwal == jadwal.id_jadwal && cbt.id_siswa == sesisiswa.siswa_id);
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

        temprekapJadwal.push(...response.rekap);

        const map = new Map();
        [...tempcbtKelas, ...(response.kelases || [])].forEach(obj => {
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
        const arrIdJadwal = cbtjadwals.map(item => String(item.id_jadwal));

        const pengawas = await getItems(table.cbtPengawas || [])
        const rekaps = await getItems(table.rekapJadwal) || [];
        const idJadwalRekap = rekaps.map(item => String(item.id_jadwal));
        idsRekap = new Map(rekaps.map(item => [String(item.id_jadwal), item.id_rekap]));
        const kelases = await getItems(table.masterKelas) || [];
        const kelasMap = Object.fromEntries(kelases.map(k => [k.id_kelas, k]));

        const jadwalNotRekap = cbtjadwals.filter(jadwal => !idJadwalRekap.includes(String(jadwal.id_jadwal)))
        const jadwalWithRekap = cbtjadwals.filter(jadwal => idJadwalRekap.includes(String(jadwal.id_jadwal)))
        const rekapNonJadwal = rekaps.filter(jadwal => !arrIdJadwal.includes(String(jadwal.id_jadwal)))

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
            tableHtml = `<table id="data-table" class="table-rekap">
                                <thead>
                                <tr>
                                    <th style="width: 50px">
                                        <div class="text-center">
                                            <input id="check-all" class="form-check-input check-custom" type="checkbox">
                                        </div>
                                    </th>
                                    <th style="width: 50px">No.</th>
                                    <th>Bank Soal & Mapel</th>
                                    <th style="width: 180px">Pelaksanaan</th>
                                    <th style="width: 200px">Status Rekap</th>
                                    <th style="width: 180px">Kelas & Peserta</th>
                                    <th style="width: 130px">Aksi</th>
                                </tr>
                                </thead>
                                <tbody id="table-body">`;
            jadwals.forEach((item, no) => {
                const idJadwal = item.id_jadwal;

                const bankKelasIds = (Array.isArray(item.bank_kelas) ? item.bank_kelas : [])
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
                    const validAgama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Kepercayaan thd Tuhan YME', 'Lainnya'];
                    const isAgamaFilter = validAgama.includes(item.bank?.soal_agama);

                    all_mengerjakan = allSiswa?.filter(s => s.mulai && (!isAgamaFilter || item.bank?.soal_agama == s.siswa?.agama));
                    all_dikoreksi = hanyaPg ? all_mengerjakan : all_mengerjakan?.filter(s => s.nilai_input?.dikoreksi == '1');

                    bankKelasIds.forEach(kelasId => {
                        all_kelas_siswa[kelasId] = allSiswa?.filter(s => s.kelas_id == kelasId);
                        all_kelas_mengerjakan[kelasId] = all_mengerjakan?.filter(s => s.kelas_id == kelasId && s.mulai != null);
                        all_kelas_dikoreksi[kelasId] = all_mengerjakan?.filter(s => s.kelas_id == kelasId && s.nilai_input?.dikoreksi == '1');
                    });
                } else {
                    allSiswa = item.sesisiswa;
                    const validAgama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Kepercayaan thd Tuhan YME', 'Lainnya'];
                    const isAgamaFilter = validAgama.includes(item.bank?.soal_agama);

                    all_mengerjakan = allSiswa?.filter(s => s.cbtsiswa?.mulai && (!isAgamaFilter || item.bank?.soal_agama == s.siswa?.agama));
                    all_dikoreksi = all_mengerjakan?.filter(s => s.cbtsiswa?.nilai_input?.dikoreksi == '1');

                    bankKelasIds.forEach(kelasId => {
                        all_kelas_siswa[kelasId] = allSiswa?.filter(s => s.kelas_id == kelasId);
                        all_kelas_mengerjakan[kelasId] = all_mengerjakan?.filter(s => s.kelas_id == kelasId && s.cbtsiswa?.mulai != null);
                        all_kelas_dikoreksi[kelasId] = all_mengerjakan?.filter(s => s.kelas_id == kelasId && s.cbtsiswa?.nilai_input?.dikoreksi == '1');
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
                            nama: k.nama_kelas || k.kode_kelas || ('Kelas ' + k.id_kelas),
                            siswa: all_kelas_siswa[k.id_kelas],
                            mengerjakan: all_kelas_mengerjakan[k.id_kelas],
                            dikoreksi: all_kelas_dikoreksi[k.id_kelas]
                        });
                    });
                }

                if (kelasbank.length === 0) {
                    kelasbank.push({
                        id: 0,
                        nama: '<span class="text-danger fst-italic">Mapel/Kelas tidak ditemukan</span>',
                        siswa: [],
                        mengerjakan: [],
                        dikoreksi: []
                    });
                }

                const formatDate = (dateStr) => {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    if (isNaN(d.getTime())) return '-';
                    return d.toLocaleDateString('id-ID', {
                        year: "numeric",
                        month: "short",
                        day: "2-digit",
                    });
                };

                const mulai = formatDate(item.tgl_mulai);
                const selesai = item.tgl_selesai ? formatDate(item.tgl_selesai) : mulai;

                const pelaksanaan = mulai === selesai
                    ? `<div class="pas-badge pas-badge--success w-100 mb-1">${mulai}</div>`
                    : `<div class="pas-badge pas-badge--success w-100 mb-1">${mulai}</div><div class="pas-badge pas-badge--success w-100">${selesai}</div>`

                const rows = kelasbank.length;
                
                // Calculate total metrics for the ENTIRE schedule, not just the single class
                let totalMengerjakan = 0;
                let totalDikoreksi = 0;
                for (let c = 0; c < rows; c++) {
                    totalMengerjakan += kelasbank[c]?.mengerjakan?.length || 0;
                    totalDikoreksi += kelasbank[c]?.dikoreksi?.length || 0;
                }

                let rekapHtml = ``;
                if (item.rekap === 1) {
                    rekapHtml = `<div class="d-flex flex-column gap-2 align-items-center" data-jadwal="${item.id_jadwal}">
                            <div class="d-flex gap-1 w-100">
                                <button class="pas-btn pas-btn--primary flex-fill" onclick="backup(${idJadwal})" title="Ulangi Rekap"><i class="bi bi-arrow-repeat"></i> Update</button>
                                <button class="pas-btn pas-btn--danger btn-aksi" data-type="h" title="Hapus Rekap"><i class="bi bi-trash"></i></button>
                            </div>
                            <button class="pas-btn pas-btn--success btn-aksi w-100" data-type="a"><i class="bi bi-graph-up me-1"></i> Analisis Butir Soal</button>`;
                } else if (item.rekap === 2) {
                    rekapHtml = `<div class="d-flex flex-column gap-2 align-items-center" data-jadwal="${item.id_jadwal}">
                            <div class="d-flex gap-1 w-100">
                                <button class="pas-btn pas-btn--success btn-aksi flex-fill" data-type="a"><i class="bi bi-graph-up me-1"></i> Analisis Data</button>
                                <button class="pas-btn pas-btn--danger btn-aksi" data-type="h" title="Hapus Rekap"><i class="bi bi-trash"></i></button>
                            </div>`;
                } else {
                    rekapHtml = `<div class="d-flex flex-column gap-2 align-items-center" data-jadwal="${item.id_jadwal}">
                            <button class="pas-btn pas-btn--primary w-100" onclick="backup(${idJadwal})"><i class="bi bi-database-add me-1"></i> Rekap Nilai</button>`;
                }

                if (!hanyaPg) {
                    const statusClass = totalMengerjakan > 0 && totalMengerjakan === totalDikoreksi ? 'pas-badge--success' : 'pas-badge--warning';
                    const essayWarning = totalMengerjakan > totalDikoreksi ? `<div class="pas-badge pas-badge--danger mt-1 w-100"><i class="bi bi-pencil-square me-1"></i> ${totalMengerjakan - totalDikoreksi} Belum Dikoreksi</div>` : '';
                    
                    rekapHtml += `<div class="pas-badge ${statusClass} w-100 text-center mt-2">
                                    <i class="bi bi-check-all me-1"></i> Selesai Dikoreksi: ${totalDikoreksi}/${totalMengerjakan}
                                  </div>${essayWarning}`;
                }
                rekapHtml += `</div>`;

                for (let i = 0; i < rows; i++) {
                    const mengerjakanCount = kelasbank[i]?.mengerjakan?.length || 0;
                    const dikoreksiCount = kelasbank[i]?.dikoreksi?.length || 0;
                    const emptySiswa = mengerjakanCount === 0;
                    let enableEkspor = item.rekap > 0;
                    const enableBtnKoreksi = enableEkspor && !emptySiswa ? '' : 'disabled'
                    const enableBtnEkspor = enableEkspor && !emptySiswa ? '' : 'disabled'

                    const btnKoreksiClass = (hanyaPg || mengerjakanCount === dikoreksiCount) ? 'pas-btn--success' : 'pas-btn--warning';

                    if (i === 0) {
                        tableHtml += `<tr>
            <td rowspan="${rows}">
                 <div class="text-center">
                      <input class="form-check-input check check-custom" value="${item.id_jadwal}" type="checkbox">
                 </div>
            </td>
            <td rowspan="${rows}" class="text-center fw-bold text-secondary">${no + 1}</td>
            <td rowspan="${rows}">
                <div class="card-rekap-info">
                    <div class="rekap-title">${item.nama_mapel || item.bank?.mapel?.nama_mapel || 'Mapel'}</div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="pas-badge pas-badge--success" style="font-size: 10px">${item.kode_jenis || item.jenis?.kode_jenis || ''}</span>
                        <span class="rekap-subtitle">${item.bank?.bank_kode}</span>
                    </div>
                    <div class="mt-1"><span class="pas-badge pas-badge--info" style="font-size: 10px"><i class="bi bi-tag"></i> ${agm || 'Umum'}</span></div>
                </div>
            </td>
            <td rowspan="${rows}" class="text-center">${pelaksanaan}</td>
            <td rowspan="${rows}" class="text-center">${rekapHtml}</td>
            <td class="text-center">
                <div class="d-flex flex-column align-items-center p-2" style="background: #f8fafc; border-radius: 10px; border: 1px solid #f1f5f9">
                    <span class="fw-bold text-dark mb-1" style="font-size: 13px">${kelasbank[i]?.nama}</span>
                    <span class="pas-badge pas-badge--info" style="font-size: 10px">${mengerjakanCount} Siswa</span>
                </div>
            </td>
            <td class="text-center align-middle" data-jadwal="${item.id_jadwal}" data-kelas="${kelasbank[i]?.id}">
                <div class="d-flex flex-column gap-2">
                    <button class="pas-btn ${btnKoreksiClass} btn-aksi" data-type="k" ${enableBtnKoreksi}><i class="bi bi-pencil-square"></i> Koreksi</button>
                    <button class="pas-btn pas-btn--info btn-aksi" data-type="e" ${enableBtnEkspor}><i class="bi bi-file-earmark-excel"></i> Ekspor</button>
                </div>
            </td>
        </tr>`;
                    } else {
                        tableHtml += `<tr>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center p-2" style="background: #f8fafc; border-radius: 10px; border: 1px solid #f1f5f9">
                                            <span class="fw-bold text-dark mb-1" style="font-size: 13px">${kelasbank[i]?.nama}</span>
                                            <span class="pas-badge pas-badge--info" style="font-size: 10px">${mengerjakanCount} Siswa</span>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle" data-jadwal="${item.id_jadwal}" data-kelas="${kelasbank[i]?.id}">
                                        <div class="d-flex flex-column gap-2">
                                            <button class="pas-btn ${btnKoreksiClass} btn-aksi" data-type="k" ${enableBtnKoreksi}><i class="bi bi-pencil-square"></i> Koreksi</button>
                                            <button class="pas-btn pas-btn--info btn-aksi" data-type="e" ${enableBtnEkspor}><i class="bi bi-file-earmark-excel"></i> Ekspor</button>
                                        </div>
                                    </td>
                                 </tr>`;
                    }
                }
            });
            tableHtml += '</tbody></table>';
        } else {
            tableHtml = `<div class="alert alert-default-warning shadow-sm border-warning rounded-3 p-4" role="alert">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                                    <div>
                                        <h5 class="fw-bold mb-1">Belum Ada Data</h5>
                                        <p class="mb-0">Belum ada jadwal penilaian untuk Tahun Pelajaran <b><?= $tp_active->tahun ?></b> Semester: <b><?= $smt_active->smt ?></b></p>
                                    </div>
                                </div>
                            </div>`;
        }
        konten.innerHTML = tableHtml;

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
                        'tp': (item.tp && item.tp.tahun) ? item.tp.tahun : (item.tp || tp_active),
                        'id_smt': item.id_smt,
                        'smt': (item.smt && item.smt.nama_smt) ? item.smt.nama_smt : (item.smt || smt_active),
                        'id_jadwal': item.id_jadwal,
                        'id_bank': item.id_bank,
                        'kode_bank': item.bank?.bank_kode || item.bank?.kode_bank || '',
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


        if (response.success) {
            await reloadPage(true, false);
            Swal.fire({
                title: "Berhasil",
                text: response.message,
                icon: "success",
                timer: 2000,
                showConfirmButton: false
            });
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
                            // await deleteItems(table.rekapJadwal, 'id_jadwal', jsonId)
                            await reloadPage(true, false);
                            Swal.fire({
                                title: "Berhasil",
                                text: respon.message,
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            });
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
<?= $this->endSection() ?>