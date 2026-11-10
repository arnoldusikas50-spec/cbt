<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsuites@5/dist/jsuites.min.css" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@5/dist/jspreadsheet.min.css" type="text/css" />
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --glass-bg: rgba(255, 255, 255, 0.7);
    }

    body {
        font-family: 'Outfit', sans-serif !important;
        background-color: #f8fafc;
    }

    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        margin-bottom: 2rem;
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }

    .card-title h6 {
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        border-radius: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
    }

    .sticky-save {
        position: sticky;
        bottom: 2rem;
        z-index: 100;
        filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.1));
    }

    .bg-soft-danger { background-color: #fee2e2; color: #991b1b; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="content">
    <div class="container-fluid">
        <div class="card my-shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h6><i class="bi bi-star me-2 text-primary"></i>Nilai Ekstrakurikuler: <b><?= $ekstra['nama_ekstra'] ?></b></h6>
                </div>
                <button onclick="window.history.back();" type="button" class="btn btn-sm btn-outline-danger px-3 rounded-pill">
                    <i class="bi bi-arrow-left-short"></i> Kembali
                </button>
            </div>
            <div class="card-body">
                <?php if ($kkm == null): ?>
                    <div class="alert bg-soft-danger border-0 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-3"></i>
                        <div>Download template tidak tersedia, silakan isi KKM terlebih dahulu di menu <b>DATA RAPOR > KKM DAN BOBOT</b></div>
                    </div>
                <?php else: ?>
                    <div class="row align-items-center mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <button type="button" class="btn btn-primary w-100 py-2 shadow-sm"
                                onclick="processDownload(<?= $ekstra['id_ekstra'] ?>, <?= $kelas['id_kelas'] ?>)">
                                <i class="bi bi-cloud-download me-2"></i> Download Template (.xlsx)
                            </button>
                        </div>

                        <div class="col-md-8">
                            <form id="uploadfile" enctype="multipart/form-data" class="d-flex gap-2">
                                <?= csrf_field() ?>
                                <div class="flex-grow-1">
                                    <input type="file" name="upload_file" class="form-control" id="custom-file">
                                </div>
                                <button id="upload" type="submit" class="btn btn-success px-4 bg-gradient border-0">
                                    <i class="bi bi-cloud-upload me-2"></i> Upload
                                </button>
                            </form>
                        </div>
                    </div>

                    <div id="t-siswa" class="w-100 mb-4 jspreadsheet-modern"></div>
                    
                    <form id="uploadnilai">
                        <?= csrf_field() ?>
                        <div class="sticky-save text-center mt-5">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-lg bg-gradient rounded-pill">
                                <i class="bi bi-check-all me-2"></i> Simpan Nilai Ekstrakurikuler
                            </button>
                        </div>
                        <div id="for-upload" class="d-none row"></div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<form id="uploadekstra">
    <?= csrf_field() ?>
    <input type="hidden" name="id_ekstra" class="form-control" value="<?= $ekstra['id_ekstra'] ?>">
    <input type="hidden" name="id_kelas" class="form-control" value="<?= $kelas['id_kelas'] ?>">
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/exceljs.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/js-excel-template.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jsuites@5/dist/jsuites.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@5/dist/index.min.js"></script>
<script>
    var arrSiswa = <?= json_encode($siswa) ?>;
    var arrNilai = <?= json_encode($nilai) ?>;
    var kkm = <?= json_encode($kkm) ?>;
    var idMapel = '<?= $ekstra['id_ekstra'] ?>';
    var idKelas = '<?= $kelas['id_kelas'] ?>';
    var namaMapel = '<?= $ekstra['nama_ekstra'] ?>';
    var namaKelas = '<?= $kelas['nama_kelas'] ?>';
    var tpActive = '<?= $tp_active->id_tp ?>';
    var smtActive = '<?= $smt_active->id_smt ?>';

    var isi = parseInt(kkm ? kkm.kkm : 0);
    var pre_d = 0;
    var pre_dsd = isi - 1;
    var pre_c = isi;
    var pre_csd = Math.floor(isi + (100 - isi) / 3);
    var pre_b = pre_csd + 1;
    var pre_bsd = Math.floor(pre_b + (100 - pre_b) / 2);
    var pre_a = pre_bsd + 1;
    var pre_asd = 100;

    $(function () {
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }
    });

    function calcPredikat(val) {
        if (val === '' || val === null || val === undefined) return '';
        var n = Number(val);
        if (isNaN(n)) return '';
        if (n > pre_bsd) return 'A';
        if (n > pre_csd) return 'B';
        if (n > pre_dsd) return 'C';
        if (n < pre_c) return 'D';
        return '';
    }

    function calcDeskripsi(pred) {
        if (!pred) return '';
        if (pred === 'A') return 'Sangat Baik';
        if (pred === 'B') return 'Baik';
        if (pred === 'C') return 'Cukup';
        if (pred === 'D') return 'Kurang';
        return '';
    }

    $(document).ready(function () {
        var dataSiswa = [];
        var row = 1;
        $.each(arrSiswa, function (i, v) {
            var noInduk = v.nisn == null || v.nisn == '' ? v.nis : v.nisn;
            var nilai = arrNilai[v.id_siswa] || {};
            var n = nilai.nilai || '';
            var p = calcPredikat(n);
            var d = calcDeskripsi(p);
            dataSiswa.push([
                noInduk, v.nama, n, p, d, v.id_siswa
            ]);
            row++;
        });

        var tableSiswa = $('#t-siswa');
        var arrCol = [];
        var char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for (let i = 0; i < 6; i++) {
            var item = {};
            // Default readOnly
            item['readOnly'] = true;

            if (i === 0) {
                item['title'] = 'NIS/NISN\n' + char.charAt(i);
                item['width'] = 100;
            } else if (i === 1) {
                item['title'] = 'NAMA SISWA\n' + char.charAt(i);
                item['width'] = 250;
            } else if (i === 2) {
                item['title'] = 'NILAI\n' + char.charAt(i);
                item['width'] = 100;
                item['readOnly'] = false; // Editable
            } else if (i === 3) {
                item['title'] = 'PREDIKAT\n' + char.charAt(i);
                item['width'] = 100;
            } else if (i === 4) {
                item['title'] = 'DESKRIPSI\n' + char.charAt(i);
                item['width'] = 150;
            } else if (i === 5) {
                item['title'] = 'ID';
                item['width'] = 1;
            }
            arrCol.push(item);
        }

        var container = document.getElementById('t-siswa');
        if (container) {
            container.innerHTML = '';
            var spreadsheet = jspreadsheet(container, {
                worksheets: [{
                    data: dataSiswa,
                    minDimensions: [5, 5],
                    search: true,
                    freezeColumns: 2,
                    columnResize: false,
                    columns: arrCol.map(c => ({ ...c, type: 'text' })),
                    onchange: function (instance, cell, col, row, value) {
                        col = parseInt(col);
                        row = parseInt(row);
                        // Recalculate saat NILAI (col 2) diubah
                        if (col === 2) {
                            var ws = instance;
                            if (spreadsheet && spreadsheet.worksheets) {
                                ws = spreadsheet.worksheets[0];
                            } else if (Array.isArray(spreadsheet)) {
                                ws = spreadsheet[0];
                            } else if (container.jspreadsheet) {
                                ws = container.jspreadsheet[0];
                            }
                            var p = calcPredikat(value);
                            var d = calcDeskripsi(p);
                            ws.setValueFromCoords(3, row, p, true);
                            ws.setValueFromCoords(4, row, d, true);
                        }
                    },
                    updateTable: function (instance, cell, col, row, val, label, cellName) {
                        // Identitas (NIS, NAMA)
                        if (col === 0 || col === 1) {
                            cell.style.backgroundColor = '#e8eaf6';
                            cell.style.color = '#283593';
                            cell.style.fontWeight = '500';
                            cell.classList.add('readonly');
                        }
                        if (col === 0) cell.style.textAlign = 'center';

                        // NILAI (editable)
                        if (col === 2) {
                            cell.style.backgroundColor = '#fff9c4';
                            cell.style.textAlign = 'center';
                            cell.style.fontWeight = '600';
                        }

                        // PREDIKAT - Dynamic Color
                        if (col === 3) {
                            cell.style.textAlign = 'center';
                            cell.style.fontWeight = '700';
                            cell.style.borderRadius = '4px';
                            cell.classList.add('readonly');
                            var v = String(val).trim().toUpperCase();
                            if (v === 'A') {
                                cell.style.backgroundColor = '#c8e6c9';
                                cell.style.color = '#2e7d32';
                            } else if (v === 'B') {
                                cell.style.backgroundColor = '#bbdefb';
                                cell.style.color = '#1565c0';
                            } else if (v === 'C') {
                                cell.style.backgroundColor = '#ffe0b2';
                                cell.style.color = '#e65100';
                            } else if (v === 'D') {
                                cell.style.backgroundColor = '#ffcdd2';
                                cell.style.color = '#c62828';
                            } else {
                                cell.style.backgroundColor = '#eceff1';
                                cell.style.color = '#90a4ae';
                            }
                        }

                        // DESKRIPSI (readonly, auto-update)
                        if (col === 4) {
                            cell.style.backgroundColor = '#fff8e1';
                            cell.style.color = '#5d4037';
                            cell.style.fontSize = '12px';
                            cell.classList.add('readonly');
                        }

                        // ID (hidden, readonly)
                        if (col === 5) {
                            cell.style.backgroundColor = '#fafafa';
                            cell.classList.add('readonly');
                        }
                    },

        $('#uploadnilai').on('submit', function (e) {
            e.preventDefault();
            var data;
            if (Array.isArray(spreadsheet)) {
                data = spreadsheet[0].getData();
            } else if (spreadsheet && spreadsheet.worksheets) {
                data = spreadsheet.worksheets[0].getData();
            } else if (spreadsheet && typeof spreadsheet.getData === 'function') {
                data = spreadsheet.getData();
            } else {
                data = container.jspreadsheet[0].getData();
            }

            var siswa = [];
            $.each(data, function (idx, s) {
                var idSiswa = s[5];
                siswa.push({
                    id_nilai_ekstra: idMapel + idKelas + idSiswa + tpActive + smtActive,
                    id_siswa: idSiswa,
                    id_tp: tpActive,
                    id_smt: smtActive,
                    id_ekstra: idMapel,
                    id_kelas: idKelas,
                    nilai: s[2],
                    predikat: s[3],
                    deskripsi: s[4]
                });
            });

            $.ajax({
                type: "POST",
                url: base_url + 'rapor/importekstra',
                data: { siswa: siswa },
                success: function (data) {
                    swal.fire({
                        title: "Sukses",
                        text: "Nilai ekstrakurikuler berhasil disimpan",
                        icon: "success",
                        showCancelButton: false,
                    }).then(result => {
                        location.reload();
                    });
                },
                error: function (e) {
                    swal.fire("Gagal", "Gagal menyimpan", "error");
                }
            });
        });
    });

    async function processDownload(idMapel, idKelas) {
        // Implementation
    }
</script>
<?= $this->endSection() ?>