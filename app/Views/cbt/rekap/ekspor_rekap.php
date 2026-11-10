<?php
$XA = isset($convert) ? $convert['xa'] : 0;
$XB = isset($convert) ? $convert['xb'] : 20;
$YA = isset($convert) ? $convert['ya'] : 100;
$YB = isset($convert) ? $convert['yb'] : 60;

$colWidth = '';
function decimalFixed($num)
{
    return round(($num * 100) / 100, 2);
}
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-200: #c7d2fe;
        --color-primary-300: #a5b4fc;
        --color-primary-400: #818cf8;
        --color-primary-500: #6366f1;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;

        --color-success-50: #f0fdf4;
        --color-success-500: #22c55e;
        --color-success-600: #16a34a;

        --color-danger-50: #fef2f2;
        --color-danger-500: #ef4444;

        --color-warning-50: #fffbeb;
        --color-warning-500: #f59e0b;

        --color-neutral-50: #f8fafc;
        --color-neutral-100: #f1f5f9;
        --color-neutral-200: #e2e8f0;
        --color-neutral-800: #1e293b;

        --font-family-base: 'Inter', 'Outfit', -apple-system, sans-serif;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --transition-base: 200ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: var(--font-family-base);
        background-color: var(--color-neutral-50) !important;
    }

    .my-shadow {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
    }

    .pas-card {
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .pas-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        background: #ffffff;
        border-bottom: 1px solid var(--color-neutral-100);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        color: var(--color-neutral-800) !important;
    }

    .pas-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
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
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.15);
    }

    .pas-btn--danger { background: var(--color-danger-500); color: white; }
    .pas-btn--success { background: #198754; color: white; }
    .pas-btn--warning { background: var(--color-warning-500); color: white; }
    .pas-btn--info { background: #0dcaf0; color: #000; }

    .pas-btn:hover {
        transform: translateY(-1px);
        opacity: 0.9;
        color: white;
    }

    .table-export {
        font-size: 0.85rem;
        border-collapse: collapse !important;
    }

    .table-export td, .table-export th {
        border: 1px solid #000 !important;
        vertical-align: middle !important;
    }
</style>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark fs-4">
                    <i class="bi bi-file-earmark-excel-fill text-success me-2"></i><?= esc($judul ?? 'Ekspor Rekap') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('cbtrekap') ?>" class="text-decoration-none">Rekap Nilai</a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Ekspor') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content pt-2">
    <div class="container-fluid">
        <!-- Filter Card -->
        <div class="card card-default my-shadow mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <form method="get" id="form-filter-rekap" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold mb-1">
                            <i class="bi bi-calendar-event me-1 text-primary"></i>Pilih Jadwal Ujian
                        </label>
                        <select name="id_jadwal" id="select-jadwal" class="form-select select2" required>
                            <option value="">-- Pilih Jadwal Ujian --</option>
                            <?php if (!empty($jadwals)): ?>
                                <?php foreach ($jadwals as $j): ?>
                                    <option value="<?= $j->id_jadwal ?>" <?= ($jadwal_selected == $j->id_jadwal) ? 'selected' : '' ?>>
                                        [<?= esc($j->kode_mapel ?? '') ?>] - <?= esc($j->nama_mapel ?? 'Ujian') ?> (<?= esc($j->kode_jenis ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold mb-1">
                            <i class="bi bi-door-open me-1 text-primary"></i>Pilih Kelas
                        </label>
                        <select name="id_kelas" id="select-kelas" class="form-select select2">
                            <option value="">-- Semua Kelas --</option>
                            <?php if (!empty($kelas)): ?>
                                <?php foreach ($kelas as $id_k => $nama_k): ?>
                                    <option value="<?= $id_k ?>" <?= ($kelas_selected == $id_k) ? 'selected' : '' ?>>
                                        <?= esc($nama_k) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-filter me-1"></i> Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rekap Table Card -->
        <div class="pas-card">
            <div class="pas-card__header">
                <h6 class="pas-card__title mb-0">
                    <i class="bi bi-table text-primary me-1"></i>
                    Rekap Hasil Nilai CBT
                </h6>
                <div class="d-flex gap-2 align-items-center" id="group-export"></div>
            </div>
            <div class="card-body p-0 position-relative">
                <div class="overflow-auto p-3">
                    <table class="table table-sm table-export" id="table-status"></table>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="d-flex justify-content-center align-items-center p-5">
                        <div class="spinner-border text-primary me-2" role="status"></div>
                        <span class="fw-semibold text-muted">Memuat Data Rekap...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Perbaikan Nilai (Katrol) -->
<div class="modal fade" id="perbaikanModal" tabindex="-1" role="dialog" aria-labelledby="perbaikanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-xl);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="perbaikanModalLabel">
                    <i class="bi bi-balance-scale me-2 text-warning"></i>Perbaikan Nilai (Katrol)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="perbaikan-nilai">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-1">Target Nilai Tertinggi</label>
                        <input type="number" class="form-control" name="ya" value="<?= $YA ?>" max="100" required>
                        <div class="form-text text-muted">Misal: 100 atau 90</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-1">Target Nilai Terendah</label>
                        <input type="number" class="form-control" name="yb" value="<?= $YB ?>" required>
                        <div class="form-text text-muted">Misal: 60 atau 70</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="pas-btn pas-btn--primary" id="convert">
                        Proses Katrol <i class="bi bi-arrow-right me-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript" src="<?= base_url('assets/app/js/tableToExcel.js') ?>"></script>

<script>
    var colWidthMin = '5,30,35,10,10,10';
    var colWidth = '<?= $colWidth ?>';

    var idJadwal = '<?= $jadwal_selected ?>';
    var idKelas = '<?= $kelas_selected ?>';
    var isSelected = <?= isset($siswa) ? '1' : '0' ?>;
    let namaMapel = '';
    let namaKelas = '';

    let nilai_max = 0;
    let nilai_min = 100;
    let hasil_max = 100;
    let hasil_min = 60;
    let isConverted = false;
    let response;
    let exluded = false;

    function decimalFixed(num) {
        return Math.round(num * 100) / 100;
    }

    function createRow(cells) {
        const row = document.createElement("tr");
        cells.forEach(([colspan, content, isBold = false]) => {
            const cell = document.createElement("td");
            if (colspan) cell.setAttribute("colspan", colspan);
            if (isBold) {
                const boldContent = document.createElement("b");
                boldContent.textContent = content;
                cell.appendChild(boldContent);
            } else {
                cell.textContent = content;
            }
            row.appendChild(cell);
        });
        return row;
    }

    function singkatTanggal(tanggal) {
        if (!tanggal) return '-';
        const options = { day: "2-digit", month: "short", year: "numeric" };
        return new Date(tanggal).toLocaleDateString("id-ID", options);
    }

    function loadSiswaKelas() {
        if (!idJadwal || idJadwal === 'undefined' || idJadwal === '') {
            $('#loading').addClass('d-none');
            $('#table-status').html('<tr><td colspan="20" class="text-center py-5 text-muted"><i class="bi bi-info-circle me-2"></i>Silakan pilih Jadwal Ujian untuk melihat data rekap</td></tr>');
            return;
        }

        var loading = $('#loading');
        loading.removeClass('d-none');
        let formData = new FormData($('#perbaikan-nilai')[0]);
        formData.append('id_kelas', idKelas || '');
        formData.append('id_jadwal', idJadwal);
        
        $.ajax({
            url: base_url + 'cbtrekap/get_nilai_rekap',
            data: formData,
            method: "POST",
            processData: false,
            contentType: false,
            success: function (data) {
                loading.addClass('d-none');
                if (data.success) {
                    response = data;
                    populateTable();
                } else {
                    $('#table-status').html('<tr><td colspan="20" class="text-center py-5 text-muted"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Data rekap belum tersedia untuk jadwal ini</td></tr>');
                    Swal.fire({
                        title: "Informasi",
                        text: data.message || 'Tidak ada data rekap',
                        icon: "warning"
                    });
                }
            },
            error: function (xhr, status, error) {
                loading.addClass('d-none');
                $('#table-status').html('<tr><td colspan="20" class="text-center py-5 text-danger"><i class="bi bi-x-circle me-2"></i>Gagal memuat data rekap</td></tr>');
                Swal.fire({
                    title: "Error",
                    text: "Gagal menghubungkan ke server",
                    icon: "error"
                });
            }
        });
    }

    function populateTable() {
        if (!response || !response.rekap) return;
        const rekap = response.rekap;
        namaMapel = rekap.nama_mapel || 'Mapel';
        const groupExport = document.getElementById("group-export");
        while (groupExport.firstChild) {
            groupExport.removeChild(groupExport.firstChild);
        }
        
        const katrolButton = document.createElement("button");
        katrolButton.type = "button";
        katrolButton.id = 'btn-katrol';
        katrolButton.classList.add("pas-btn", isConverted ? "pas-btn--warning" : "pas-btn--danger", "btn-sm");
        groupExport.appendChild(katrolButton);

        const detailButton = document.createElement("button");
        detailButton.type = "button";
        detailButton.id = "nilai-detail";
        detailButton.classList.add("pas-btn", exluded ? "pas-btn--warning" : "pas-btn--primary", "btn-sm");
        detailButton.setAttribute("title", "Tampil Detail");
        groupExport.appendChild(detailButton);

        const excelButton = document.createElement("button");
        excelButton.type = "button";
        excelButton.id = "download-excel";
        excelButton.classList.add("pas-btn", "pas-btn--success", "btn-sm");
        excelButton.setAttribute("title", "Download Excel");
        groupExport.appendChild(excelButton);

        const rekapJadwal = rekap.bank_soal || {};
        rekapJadwal.tampil_urutan = rekap.soal_urutan ? rekap.soal_urutan.length : 0;
        rekapJadwal.tampil_cloze = rekap.soal_cloze ? rekap.soal_cloze.length : 0;

        const kelas = rekap.kelas_bank || [];
        const getNamaKelas = () => {
            if (!response.kelas_selected) return 'Semua Kelas';
            const kls = kelas.find(k => k.id_kelas == response.kelas_selected);
            return kls?.nama_kelas ?? ('Kelas ' + response.kelas_selected);
        };
        namaKelas = getNamaKelas();

        if (rekapJadwal) {
            let colWidth = '5,15,35';
            for (let s = 0; s < (rekapJadwal.tampil_pg || 0); s++) {
                colWidth += ',5';
            }
            colWidth += ',10,10,10';

            const jml_soal = Number(rekapJadwal.tampil_pg || 0) +
                Number(rekapJadwal.tampil_kompleks || 0) +
                Number(rekapJadwal.tampil_jodohkan || 0) +
                Number(rekapJadwal.tampil_isian || 0) +
                Number(rekapJadwal.tampil_esai || 0) +
                Number(rekapJadwal.tampil_urutan || 0) +
                Number(rekapJadwal.tampil_cloze || 0);

            const cols = [
                rekapJadwal.tampil_pg,
                rekapJadwal.tampil_kompleks,
                rekapJadwal.tampil_jodohkan,
                rekapJadwal.tampil_isian,
                rekapJadwal.tampil_esai,
                rekapJadwal.tampil_urutan,
                rekapJadwal.tampil_cloze
            ].filter(col => Number(col) > 0);

            const rows = (Number(rekapJadwal.tampil_pg) > 0 || cols.length > 1) ? 2 : 1;

            const table = document.getElementById("table-status");
            while (table.firstChild) {
                table.removeChild(table.firstChild);
            }
            table.id = 'table-status';
            table.style.fontSize = '10pt';
            table.style.borderRight = '1px solid #000';
            table.setAttribute("data-cols-width", exluded ? colWidthMin : colWidth);

            // Baris Header Informasi
            table.appendChild(createRow([[2, "Soal"], [1, rekapJadwal.bank_kode ?? '-', true], [10, "Mata Pelajaran"], [15, rekap.nama_mapel ?? '-', true]]));
            table.appendChild(createRow([[2, "Jenis Penilaian"], [1, rekap.kode_jenis ?? '-', true], [10, "Guru Pengampu"], [15, rekap.nama_guru || 'Guru Pengampu', true]]));
            table.appendChild(createRow([[2, "Kelas"], [1, namaKelas, true], [10, "Waktu Pelaksanaan"], [15, `${singkatTanggal(rekap.tgl_mulai)} ~ ${singkatTanggal(rekap.tgl_selesai)}`, true]]));
            table.appendChild(createRow([[2, "Jumlah Soal"], [1, jml_soal, true], [10, "TP / SMT"], [15, `${rekap.tp ?? ''} smt ${rekap.smt ?? ''}`, true]]));
            table.appendChild(createRow([[null, ""]]));

            const siswas = rekap.rekapsiswa || [];
            if (siswas.length > 0) {
                const headerRow1 = document.createElement('tr');
                const headerRow2 = document.createElement('tr');

                const headers = [
                    { text: "No.", rowspan: rows, width: 40 },
                    { text: "No. Peserta", rowspan: rows, width: 220 },
                    { text: "Nama", rowspan: rows, 'class': 'nama-siswa' }
                ];

                const paramExcel = [
                    { "data": "data-fill-color", "value": "d8eafd" },
                    { "data": "data-a-v", "value": "middle" },
                    { "data": "data-b-a-s", "value": "thin" },
                    { "data": "data-f-bold", "value": "true" }
                ];

                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.classList.add("text-center", "align-middle", "bg-gray-light");
                    if (header.class) th.classList.add(header.class);
                    th.rowSpan = header.rowspan;
                    th.textContent = header.text;
                    th.style.border = '1px solid black';
                    for (const param of paramExcel) {
                        th.setAttribute(param.data, param.value);
                    }
                    if (header.width) th.style.minWidth = `${header.width}px`;
                    headerRow1.appendChild(th);
                });

                if (Number(rekapJadwal.tampil_pg) > 0) {
                    const pgHeader = document.createElement('th');
                    pgHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                    pgHeader.setAttribute('data-exclude', exluded);
                    if (exluded) pgHeader.classList.add("d-none");
                    pgHeader.setAttribute('id', 'no-soal-tile');
                    pgHeader.colSpan = rekapJadwal.tampil_pg;
                    pgHeader.textContent = "Nomor Soal";
                    pgHeader.style.border = '1px solid black';
                    headerRow1.appendChild(pgHeader);

                    const pgBenarHeader = document.createElement('th');
                    pgBenarHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                    pgBenarHeader.rowSpan = 2;
                    pgBenarHeader.textContent = "PG Benar";
                    pgBenarHeader.style.border = '1px solid black';
                    headerRow1.appendChild(pgBenarHeader);

                    for (const param of paramExcel) {
                        pgHeader.setAttribute(param.data, param.value);
                        pgBenarHeader.setAttribute(param.data, param.value);
                    }

                    let num_soal = 1;
                    const soalPgList = rekap.soal_pg || [];
                    for (const soalPg of soalPgList) {
                        const th = document.createElement('th');
                        th.classList.add('text-center', 'align-middle', 'bg-gray-light', 'p-1', 'no-soal');
                        if (exluded) th.classList.add("d-none");
                        th.setAttribute('data-exclude', exluded);
                        th.textContent = num_soal;
                        th.style.minWidth = '40px';
                        th.style.border = '1px solid black';
                        for (const param of paramExcel) {
                            th.setAttribute(param.data, param.value);
                        }
                        headerRow2.appendChild(th);
                        num_soal++;
                    }
                }

                const scoreTypes = [
                    { tampil: 'tampil_pg', text: 'PG' },
                    { tampil: 'tampil_kompleks', text: 'PGK' },
                    { tampil: 'tampil_jodohkan', text: 'Jod' },
                    { tampil: 'tampil_isian', text: 'Is' },
                    { tampil: 'tampil_esai', text: 'Es' },
                    { tampil: 'tampil_urutan', text: 'Urt' },
                    { tampil: 'tampil_cloze', text: 'Clz' },
                ];
                
                const activeScoreCount = scoreTypes.filter(t => Number(rekapJadwal[t.tampil]) > 0).length;
                const belumDataColspan = (Number(rekapJadwal.tampil_pg) > 0 ? 1 : 0) + activeScoreCount + 2;

                scoreTypes.forEach(type => {
                    if (Number(rekapJadwal[type.tampil]) > 0) {
                        const th = document.createElement('th');
                        th.classList.add("text-center", "align-middle", "bg-gray-light");
                        th.textContent = type.text;
                        th.style.border = '1px solid black';
                        for (const param of paramExcel) {
                            th.setAttribute(param.data, param.value);
                        }
                        headerRow2.appendChild(th);
                    }
                });

                const skorAsli = document.createElement('th');
                skorAsli.classList.add("text-center", "align-middle", "bg-gray-light");
                skorAsli.textContent = "Asli";
                skorAsli.style.border = '1px solid black';
                headerRow2.appendChild(skorAsli);

                const nilaiKatrol = document.createElement('th');
                nilaiKatrol.classList.add("text-center", "align-middle", "bg-gray-light");
                nilaiKatrol.textContent = "Katrol";
                nilaiKatrol.style.border = '1px solid black';
                headerRow2.appendChild(nilaiKatrol);

                const skorHeader = document.createElement('th');
                skorHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                skorHeader.colSpan = cols.length;
                skorHeader.textContent = "SKOR";
                skorHeader.style.border = '1px solid black';
                for (const param of paramExcel) {
                    skorHeader.setAttribute(param.data, param.value);
                }
                headerRow1.appendChild(skorHeader);

                const nilaiHeader = document.createElement('th');
                nilaiHeader.classList.add("text-center", "align-middle", "bg-gray-light");
                nilaiHeader.colSpan = 2;
                nilaiHeader.textContent = "Nilai";
                nilaiHeader.style.border = '1px solid black';
                for (const param of paramExcel) {
                    nilaiHeader.setAttribute(param.data, param.value);
                }
                headerRow1.appendChild(nilaiHeader);

                for (const param of paramExcel) {
                    skorAsli.setAttribute(param.data, param.value);
                    nilaiKatrol.setAttribute(param.data, param.value);
                }

                table.appendChild(headerRow1);
                table.appendChild(headerRow2);

                let no = 1;
                const paramNumName = [
                    { "data": "data-a-v", "value": "middle" },
                    { "data": "data-b-a-s", "value": "thin" }
                ];
                const paramAll = [
                    { "data": "data-a-v", "value": "middle" },
                    { "data": "data-a-h", "value": "center" },
                    { "data": "data-b-a-s", "value": "thin" }
                ];

                siswas.forEach(siswa => {
                    const sudahUjian = !!(siswa?.durasi?.mulai);
                    const tr = document.createElement('tr');
                    if (!sudahUjian) tr.style.background = 'rgba(241,245,249,0.6)';

                    const fields = [
                        { value: no, align: 'center' },
                        { value: siswa.no_peserta ?? siswa.siswa?.nisn ?? '-', align: 'center' },
                        { value: siswa.nama_siswa ?? siswa.siswa?.nama ?? '-', overflow: 'hidden', whiteSpace: 'nowrap', 'class': 'nama-siswa' }
                    ];

                    fields.forEach(field => {
                        const td = document.createElement('td');
                        if (field.class) {
                            td.classList.add(field.class);
                        } else {
                            td.setAttribute("data-a-h", "center");
                        }
                        td.textContent = field.value;
                        if (field.align) td.style.textAlign = field.align;
                        if (field.overflow) td.style.overflow = field.overflow;
                        if (field.whiteSpace) td.style.whiteSpace = field.whiteSpace;
                        td.style.border = '1px solid grey';
                        for (const param of paramNumName) {
                            td.setAttribute(param.data, param.value);
                        }
                        tr.appendChild(td);
                    });

                    if (!sudahUjian) {
                        const tdBelum = document.createElement('td');
                        tdBelum.colSpan = belumDataColspan;
                        tdBelum.textContent = '⌛ Belum Ada Data Nilai';
                        tdBelum.style.textAlign = 'center';
                        tdBelum.style.fontStyle = 'italic';
                        tdBelum.style.color = '#94a3b8';
                        tdBelum.style.border = '1px dashed #e2e8f0';
                        tdBelum.setAttribute('data-a-h', 'center');
                        tdBelum.setAttribute('data-b-a-s', 'thin');
                        tr.appendChild(tdBelum);
                    } else if (Number(rekapJadwal.tampil_pg) > 0) {
                        let benarPg = 0;
                        const soalPgList = rekap.soal_pg || [];
                        for (const soalPg of soalPgList) {
                            const td = document.createElement('td');
                            td.classList.add('no-soal-siswa');
                            if (exluded) td.classList.add("d-none");
                            td.setAttribute('data-exclude', exluded);

                            const soalSiswa = siswa.nilai_akhir?.skor_pg?.detail?.find(s => s.id_soal == soalPg.id_soal);
                            if (soalSiswa) {
                                td.textContent = soalSiswa.jawaban_siswa;
                                td.style.backgroundColor = soalSiswa.benar ? '#86efac' : '#fca5a5';
                                td.setAttribute("data-fill-color", soalSiswa.benar ? '86efac' : 'fca5a5');
                                if (soalSiswa.benar) benarPg++;
                            } else {
                                td.textContent = '-';
                                td.style.backgroundColor = '#fca5a5';
                                td.setAttribute("data-fill-color", 'fca5a5');
                            }
                            for (const param of paramAll) {
                                td.setAttribute(param.data, param.value);
                            }
                            td.style.border = '1px solid #000';
                            td.style.textAlign = 'center';
                            tr.appendChild(td);
                        }

                        const pgScore = document.createElement('td');
                        pgScore.textContent = benarPg.toString();
                        pgScore.style.textAlign = 'center';
                        pgScore.style.border = '1px solid #000';
                        for (const param of paramAll) {
                            pgScore.setAttribute(param.data, param.value);
                        }
                        tr.appendChild(pgScore);
                    }

                    if (sudahUjian) {
                        const scoreTypes = [
                            { tampil: 'tampil_pg', skor: siswa.nilai_akhir?.skor_pg?.skor_total },
                            { tampil: 'tampil_kompleks', skor: siswa.nilai_akhir?.skor_kompleks?.skor_total },
                            { tampil: 'tampil_jodohkan', skor: siswa.nilai_akhir?.skor_jodohkan?.skor_total },
                            { tampil: 'tampil_isian', skor: siswa.nilai_akhir?.skor_isian?.skor_total },
                            { tampil: 'tampil_esai', skor: siswa.nilai_akhir?.skor_essai?.skor_total },
                            { tampil: 'tampil_urutan', skor: siswa.nilai_akhir?.skor_urutan?.skor_total },
                            { tampil: 'tampil_cloze', skor: siswa.nilai_akhir?.skor_cloze?.skor_total },
                        ];
                        scoreTypes.forEach(type => {
                            if (Number(rekapJadwal[type.tampil]) > 0) {
                                const td = document.createElement('td');
                                let s = parseFloat(type.skor);
                                td.textContent = isNaN(s) ? '0' : s;
                                td.style.textAlign = 'center';
                                td.style.border = '1px solid #000';
                                for (const param of paramAll) {
                                    td.setAttribute(param.data, param.value);
                                }
                                tr.appendChild(td);
                            }
                        });

                        const pel = siswa?.pelanggaran;
                        const totalMinus = rekap?.jadwal?.pelanggaran === '1' ? (parseFloat(pel?.t_total) || 0) : 0;
                        const textColor = rekap?.jadwal?.pelanggaran === '1' && pel?.t_total != null && pel?.t_total > 0 ? 'text-danger' : 'text-black';

                        let skorAsli = parseFloat(siswa.nilai_akhir?.skor_total) || 0;
                        const skorTotal = skorAsli - totalMinus;

                        let skorKatrol = '';
                        if (isConverted) {
                            let sk = (((hasil_max - hasil_min) / 100) * Number(skorTotal)) + hasil_min;
                            skorKatrol = isNaN(sk) ? '0.00' : Number(sk).toFixed(2);
                        }

                        const totalScore = document.createElement('td');
                        totalScore.classList.add(textColor);
                        totalScore.textContent = skorTotal;
                        totalScore.style.textAlign = 'center';
                        totalScore.style.backgroundColor = '#fef08a';
                        totalScore.style.border = '1px solid #000';
                        totalScore.style.minWidth = '50px';

                        const convertScore = document.createElement('td');
                        convertScore.classList.add(textColor);
                        convertScore.innerHTML = skorKatrol;
                        convertScore.style.backgroundColor = '#ffffff';
                        convertScore.style.textAlign = 'center';
                        convertScore.style.border = '1px solid #000';
                        convertScore.style.minWidth = '50px';

                        for (const param of paramAll) {
                            totalScore.setAttribute(param.data, param.value);
                            convertScore.setAttribute(param.data, param.value);
                        }
                        tr.appendChild(totalScore);
                        tr.appendChild(convertScore);
                    }

                    table.appendChild(tr);
                    no++;
                });
            } else {
                const tr = document.createElement('tr');
                const empty = document.createElement('td');
                empty.id = 'empty';
                empty.textContent = 'Belum ada rekap nilai siswa';
                empty.style.textAlign = 'center';
                empty.style.border = '1px solid grey';
                tr.appendChild(empty);
                table.appendChild(tr);
            }

            // Tombol Toolbar State
            if (isConverted) {
                $('#btn-katrol').html('<i class="bi bi-arrow-counterclockwise me-1"></i><span>Nilai Asli</span>');
            } else {
                $('#btn-katrol').html('<i class="bi bi-sliders me-1"></i><span>Katrol</span>');
            }

            $('#nilai-detail').html('<i class="bi bi-eye me-1"></i><span>' + (exluded ? 'Tampil Detail' : 'Sembunyikan Detail') + '</span>');
            $('#download-excel').html('<i class="bi bi-file-earmark-excel me-1"></i><span>Ekspor ke Excel</span>');

            $('#btn-katrol').off('click').on('click', function (e) {
                $(this).toggleClass('pas-btn--danger').toggleClass('pas-btn--warning');
                perbaikiNilai(false);
            });

            $("#nilai-detail").off('click').on('click', function (event) {
                $(this).toggleClass('pas-btn--primary').toggleClass('pas-btn--warning');
                $("#no-soal-tile").toggleClass('d-none');
                $(".no-soal").toggleClass('d-none');
                $(".no-soal-siswa").toggleClass('d-none');

                exluded = !exluded;
                $('#no-soal-tile').attr('data-exclude', '' + exluded);
                $('.no-soal').attr('data-exclude', '' + exluded);
                $('.no-soal-siswa').attr('data-exclude', '' + exluded);
                $('#table-status').attr('data-cols-width', exluded ? colWidthMin : colWidth);
                $('#nilai-detail span').text(exluded ? 'Tampil Detail' : 'Sembunyikan Detail');
            });

            $("#download-excel").off('click').on('click', function (event) {
                var table = document.querySelector("#table-status");
                TableToExcel.convert(table, {
                    name: `Rekap Nilai${exluded ? '' : ' Detail'} ${namaKelas} ${namaMapel}.xlsx`,
                    sheet: { name: "Nilai" }
                });
            });
        }
    }

    function perbaikiNilai(fromModal) {
        if (!isConverted && !fromModal) {
            $('#perbaikanModal').modal('show');
            return;
        }
        isConverted = !isConverted;
        populateTable();
    }

    $(document).ready(function () {
        ajaxcsrf();

        // Initialize Select2 if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        }

        $('#form-filter-rekap').on('submit', function (e) {
            e.preventDefault();
            idJadwal = $('#select-jadwal').val();
            idKelas = $('#select-kelas').val();
            if (idJadwal) {
                const newUrl = window.location.pathname + '?id_jadwal=' + idJadwal + (idKelas ? '&id_kelas=' + idKelas : '');
                window.history.pushState({ path: newUrl }, '', newUrl);
                loadSiswaKelas();
            } else {
                Swal.fire('Perhatian', 'Silakan pilih Jadwal Ujian terlebih dahulu', 'warning');
            }
        });

        $('#select-jadwal, #select-kelas').on('change', function () {
            $('#form-filter-rekap').submit();
        });

        setTimeout(function () {
            if (idJadwal && idJadwal !== 'undefined' && idJadwal !== '') {
                loadSiswaKelas();
            } else {
                $('#loading').addClass('d-none');
                $('#table-status').html('<tr><td colspan="20" class="text-center py-5 text-muted"><i class="bi bi-info-circle me-2"></i>Silakan pilih Jadwal Ujian dan Kelas untuk melihat data rekap</td></tr>');
            }
        }, 200);
    });
</script>
<?= $this->endSection() ?>