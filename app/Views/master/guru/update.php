<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;
        
        --color-success-50: #f0fdf4;
        --color-success-100: #dcfce7;
        --color-success-600: #16a34a;

        --color-info-50: #f0f9ff;
        --color-info-100: #e0f2fe;
        --color-info-600: #0284c7;

        --color-neutral-50: #f9fafb;
        --color-neutral-100: #f3f4f6;
        --color-neutral-200: #e5e7eb;
        --color-neutral-300: #d1d5db;
        --color-neutral-400: #9ca3af;
        --color-neutral-500: #6b7280;
        --color-neutral-600: #4b5563;
        --color-neutral-700: #374151;

        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
        --radius-full: 9999px;

        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --transition-base: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc !important; }

    /* ============================================
       TYPOGRAPHY & HEADERS
       ============================================ */
    .text-primary-gradient {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    /* ============================================
       PREMIUM COMPONENTS
       ============================================ */
    .pas-card {
        background: white;
        border-radius: var(--radius-2xl);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: var(--transition-base);
    }
    .pas-card:hover { box-shadow: var(--shadow-md); border-color: var(--color-neutral-300); }

    .pas-card__header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--color-neutral-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--color-neutral-800);
    }

    .pas-card__title-icon {
        width: 40px; height: 40px;
        background: var(--color-primary-50);
        color: var(--color-primary-600);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }

    /* ============================================
       BUTTONS
       ============================================ */
    .pas-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 0.5rem; padding: 0.625rem 1.25rem;
        font-weight: 600; font-size: var(--font-size-sm);
        border-radius: var(--radius-lg); transition: var(--transition-base);
        cursor: pointer; border: 1px solid transparent; text-decoration: none !important;
    }

    .pas-btn--primary { background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
    .pas-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35); color: white; }

    .pas-btn--success { background: var(--color-success-600); color: white; }
    .pas-btn--success:hover { background: #15803d; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25); }

    .pas-btn--info { background: var(--color-info-600); color: white; }
    .pas-btn--info:hover { background: #0369a1; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25); }

    .pas-btn--white { background: white; color: var(--color-neutral-700); border-color: var(--color-neutral-200); }
    .pas-btn--white:hover { background: var(--color-neutral-50); color: var(--color-neutral-900); }

    .alert-premium-info {
        background: var(--color-info-50); border: 1px solid var(--color-info-100);
        color: var(--color-info-600); border-radius: var(--radius-lg);
        padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem;
    }

    .premium-file-input {
        padding: 1rem;
        border: 2px dashed var(--color-neutral-300);
        border-radius: var(--radius-xl);
        background: var(--color-neutral-50);
        width: 100%;
        cursor: pointer;
        transition: var(--transition-base);
    }
    .premium-file-input:hover { border-color: var(--color-primary-400); background: white; }

    .preview-table-container {
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        overflow: hidden;
        background: white;
    }

    .floating-icon { animation: float 4s ease-in-out infinite; }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
</style>

<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark" style="font-family: 'Inter', sans-serif; letter-spacing: -1.5px;">
                    <span class="text-primary-gradient"><i class="bi bi-arrow-repeat me-2"></i></span>Sinkronisasi Data
                </h3>
                <p class="text-muted mb-0">Bulk update informasi profil dan sinkronisasi foto massal</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('dataguru') ?>" class="text-decoration-none">Guru</a></li>
                    <li class="breadcrumb-item active">Update Massal</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12">
                <div class="alert-premium-info mb-4 shadow-sm border-start border-4 border-info">
                    <i class="bi bi-info-circle-fill fs-4"></i>
                    <div class="smaller">
                        <strong class="text-dark d-block mb-1">Panduan Kerja Efisien:</strong>
                        Cukup unduh data yang ada, lakukan perubahan pada file Excel atau Word secara luring, kemudian unggah kembali ke panel yang sesuai untuk sinkronisasi instan.
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Profile Data Sync -->
                <div class="pas-card border-0 mb-4 h-auto shadow-sm">
                    <div class="pas-card__header py-3 px-4 border-bottom-0">
                        <div class="pas-card__title">
                            <div class="pas-card__title-icon bg-success-soft text-success">
                                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Update Massal (Excel)</h6>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">1. Unduh Data Profil</label>
                            <?php if (!empty($gurus)): ?>
                                <button onclick="download()" class="pas-btn pas-btn--success w-100 py-3 shadow-sm rounded-pill">
                                    <i class="bi bi-download"></i> Download Dataset
                                </button>
                            <?php else: ?>
                                <div class="bg-light p-3 rounded text-center smaller text-muted italic border">Belum ada data guru</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">2. Unggah File Dataset</label>
                            <input type="file" id="input-file-excel" class="premium-file-input smaller" accept=".xlsx" />
                            <div class="smaller text-muted mt-2 opacity-75">Gunakan format <b>.xlsx</b> yang diunduh.</div>
                        </div>
                    </div>
                </div>

                <!-- Photo Sync -->
                <div class="pas-card border-0 mb-4 h-auto shadow-sm">
                    <div class="pas-card__header py-3 px-4 border-bottom-0">
                        <div class="pas-card__title">
                            <div class="pas-card__title-icon bg-info-soft text-info">
                                <i class="bi bi-image-fill"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Sinkron Foto (Word)</h6>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">1. Unduh Template Word</label>
                            <?php if (!empty($gurus)): ?>
                                <button onclick="downloadWord()" class="pas-btn pas-btn--info w-100 py-3 shadow-sm rounded-pill">
                                    <i class="bi bi-file-earmark-word-fill"></i> Unduh Tabel Foto
                                </button>
                            <?php else: ?>
                                <div class="bg-light p-3 rounded text-center smaller text-muted italic border">Belum ada data guru</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">2. Unggah Berkas Word</label>
                            <input type="file" id="input-file-word" class="premium-file-input smaller" accept=".docx" />
                            <div class="smaller text-muted mt-2 opacity-75">Lampirkan foto pada kolom yang ditentukan.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Action Status -->
                <div class="pas-card mb-4 border-0 shadow-sm" style="min-height: 120px; display: flex; align-items: center;">
                    <div class="card-body px-4 py-2">
                        <div id="status-container" class="text-center py-4 text-muted opacity-50">
                            <div class="floating-icon mb-2">
                                <i class="bi bi-cloud-arrow-up display-5"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Siap Menerima Berkas</h6>
                            <p class="smaller m-0">Pilih file di panel kiri untuk memulai pratinjau dataset</p>
                        </div>
                        
                        <div id="action-container" class="d-none animate-fade-in py-3">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-soft text-primary rounded-circle p-3 me-3">
                                        <i class="bi bi-database-check fs-3"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0"><span id="data-count">0</span> Entitas Terdeteksi</h5>
                                        <p class="text-muted smaller mb-0">Struktur valid. Siap untuk sinkronisasi massal.</p>
                                    </div>
                                </div>
                                <button id="btn-upload" type="button" class="pas-btn pas-btn--primary px-5 py-3 shadow-lg rounded-pill border-0">
                                    <i class="bi bi-check2-circle fs-5 me-2"></i> Konfirmasi Sinkronisasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Grid -->
                <div class="pas-card border-0 shadow-sm">
                    <div class="pas-card__header border-bottom py-3 px-4">
                        <div class="pas-card__title">
                            <h6 class="mb-0 fw-bold px-1"><i class="bi bi-table text-primary me-2"></i>Integritas Dataset</h6>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="file-preview" class="table-responsive" style="max-height: 450px; background: #fafbfc;">
                            <table class="table table-hover align-middle mb-0 d-none" id="tbl-preview">
                                <thead class="bg-white sticky-top shadow-xs">
                                    <tr id="preview-header"></tr>
                                </thead>
                                <tbody id="preview-body" class="smaller"></tbody>
                            </table>
                            <div id="preview-empty" class="text-center py-5 opacity-25">
                                <i class="bi bi-intersect display-1 d-block mb-3"></i>
                                <h5 class="fw-bold">Belum Ada Data Terpindai</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Helper for Export -->
        <div id="download-tbl" class="d-none">
            <style>
                table { border-collapse: collapse; width: 100%; font-family: sans-serif; }
                th, td { border: 1px solid #000; padding: 5px; }
                th { background-color: #f2f2f2; font-weight: bold; }
            </style>
            <h2>Dataset Profil Guru</h2>
            <ul>
                <li>Dilarang merubah Header atau kolom ID</li>
                <li>Hanya ganti data pada kolom isian</li>
                <li>Untuk dokumen Word, ganti gambar pada kolom FOTO</li>
            </ul>
            <table id="tbl-export">
                <thead><tr id="export-header"></tr></thead>
                <tbody id="export-body"></tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/excel/exceljs.js') ?>"></script>
<script src="<?= base_url('assets/app/js/FileSaver.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/mammoth.browser.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/html-docx.js') ?>"></script>

<script>
    let dataGuru = [];
    let isUploadType;

    $(document).ready(function () {
        $('#input-file-excel').change(async function (e) {
            const file = e.target.files[0];
            if (!file) return;

            showLoadingPreview('Menganalisis file Excel...');
            try {
                const data = await getDataFromExcel(file);
                dataGuru = data[data.sheets[0]].rows;
                isUploadType = 'excel';
                displayExcelPreview(data[data.sheets[0]]);
                showAction(dataGuru.length);
            } catch (err) {
                Swal.fire({icon: 'error', title: 'Error', text: 'Gagal memproses file Excel'});
                resetPreview();
            }
        });

        $('#input-file-word').change(function (e) {
            const file = e.target.files[0];
            if (!file) return;

            showLoadingPreview('Memindai Dokumen Word...');
            parseWordDocxFile(file);
        });

        $('#btn-upload').click(function () {
            if (dataGuru.length === 0) return;
            uploadData();
        });
    });

    function showLoadingPreview(text) {
        $('#status-container').html(`<div class="spinner-border text-primary mb-3"></div><p class="smaller fw-bold text-muted">${text}</p>`).removeClass('d-none');
        $('#preview-empty').addClass('d-none');
        $('#action-container').addClass('d-none');
        $('#tbl-preview').addClass('d-none');
    }

    function showAction(count) {
        $('#status-container').addClass('d-none');
        $('#action-container').removeClass('d-none').hide().fadeIn();
        $('#data-count').text(count);
    }

    function resetPreview() {
        $('#status-container').removeClass('d-none').html(`<i class="bi bi-info-circle display-4 d-block mb-3"></i><p>Pilih file isian untuk pratinjau</p>`);
        $('#preview-empty').removeClass('d-none');
        $('#action-container').addClass('d-none');
        $('#tbl-preview').addClass('d-none');
        dataGuru = [];
    }

    async function getDataFromExcel(file) {
        const wb = new ExcelJS.Workbook();
        const reader = new FileReader();
        return new Promise((resolve) => {
            reader.onload = () => {
                wb.xlsx.load(reader.result).then(workbook => {
                    let dataFiles = { sheets: [] };
                    workbook.eachSheet((sheet) => {
                        dataFiles.sheets.push(sheet.name);
                        let cols = { name: sheet.name, header: [], rows: [] };
                        sheet.eachRow({ includeEmpty: false }, (row, rowIndex) => {
                            if (rowIndex === 1) {
                                row.eachCell((cell, colNumber) => { cols.header.push({ label: cell.text, value: colNumber }); });
                            } else if (rowIndex > 1) {
                                let obj = {};
                                for (let i = 1; i <= row.cellCount; i++) { obj[i] = row.getCell(i).text; }
                                cols.rows.push(obj);
                            }
                        });
                        dataFiles[sheet.name] = cols;
                    });
                    resolve(dataFiles);
                });
            };
            reader.readAsArrayBuffer(file);
        });
    }

    function displayExcelPreview(sheet) {
        let headerHtml = '';
        sheet.header.forEach(h => headerHtml += `<th class="smaller text-muted py-3 px-3 border-bottom text-uppercase tracking-wider fw-bold">${h.label}</th>`);
        $('#preview-header').html(headerHtml);

        let bodyHtml = '';
        sheet.rows.forEach(row => {
            bodyHtml += '<tr>';
            sheet.header.forEach(h => { bodyHtml += `<td class="px-3 border-bottom">${row[h.value] || '-'}</td>`; });
            bodyHtml += '</tr>';
        });

        $('#preview-body').html(bodyHtml);
        $('#tbl-preview').removeClass('d-none');
    }

    function parseWordDocxFile(file) {
        let reader = new FileReader();
        reader.onload = function (e) {
            let arrayBuffer = e.target.result;
            mammoth.convertToHtml({ arrayBuffer: arrayBuffer }).then(function (result) {
                const $wrap = $('<div>').append(result.value);
                const $table = $wrap.find('table');

                if ($table.length) {
                    dataGuru = [];
                    let headerHtml = '';
                    $table.find('tr').first().find('td,th').each(function () {
                        headerHtml += `<th class="smaller text-muted py-3 px-3 border-bottom text-uppercase tracking-wider fw-bold">${$(this).text().trim()}</th>`;
                    });
                    $('#preview-header').html(headerHtml);

                    let bodyHtml = '';
                    $table.find('tr').each(function (i, tr) {
                        if (i === 0) return;
                        const $tds = $(tr).find('td');
                        bodyHtml += '<tr>';
                        $tds.each(function () { bodyHtml += `<td class="px-3 border-bottom">${$(this).html()}</td>`; });
                        bodyHtml += '</tr>';

                        const fotoImg = $(tr).find('img');
                        const fotoSrc = fotoImg.attr('src');
                        dataGuru.push({
                            no: $tds.eq(0).text().trim(),
                            id_guru: $tds.eq(1).text().trim(),
                            nip: $tds.eq(2).text().trim(),
                            nama: $tds.eq(3).text().trim(),
                            jenis_kelamin: $tds.eq(4).text().trim(),
                            foto: fotoSrc ? fotoSrc.split(',')[1] : null,
                            ext: fotoSrc ? (fotoSrc.substring("data:image/".length, fotoSrc.indexOf(";base64")) || 'jpg') : 'jpg'
                        });
                    });

                    $('#preview-body').html(bodyHtml);
                    $('#tbl-preview').removeClass('d-none');
                    isUploadType = 'word';
                    showAction(dataGuru.length);
                } else {
                    Swal.fire({icon: 'error', title: 'Invalid File', text: 'Tabel data tidak ditemukan dalam file Word!'});
                    resetPreview();
                }
            });
        };
        reader.readAsArrayBuffer(file);
    }

    function uploadData() {
        Swal.fire({
            title: '<h4 class="fw-bold m-0">Inisiasi Sinkronisasi?</h4>',
            html: `Seluruh perubahan pada berkas akan segera diterapkan pada <b>${dataGuru.length}</b> entitas guru.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Jalankan Update',
            cancelButtonText: 'Tinjau Kembali',
            customClass: { popup: 'rounded-4', confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Sinkronisasi Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                const mapping = [
                    { key: 1, value: "no" }, { key: 2, value: "id_guru" }, { key: 3, value: "nip" },
                    { key: 4, value: "nama_guru" }, { key: 5, value: "jenis_kelamin" },
                    { key: 6, value: "username" }, { key: 7, value: "password" }
                ];

                let payload = [];
                let ids = [];

                if (isUploadType === 'excel') {
                    dataGuru.forEach(row => {
                        let guru = {};
                        mapping.forEach(m => { if (row[m.key]) guru[m.value] = row[m.key]; });
                        if (guru.id_guru) { payload.push(guru); ids.push(guru.id_guru); }
                    });
                } else { payload = dataGuru; }

                const url = isUploadType === 'excel' ? '<?= site_url("dataguru/update_all") ?>' : '<?= site_url("dataguru/update_foto") ?>';

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { guru: payload, ids: JSON.stringify(ids), <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    success: (res) => {
                        Swal.close();
                        if (res.status) {
                            Swal.fire({icon: 'success', title: 'Sinkron Tuntas', text: `Daftar data berhasil diperbarui.`, customClass: { popup: 'rounded-4' }}).then(() => {
                                window.location.href = '<?= site_url("dataguru") ?>';
                            });
                        } else {
                            Swal.fire({icon: 'error', title: 'Gagal', text: res.message || 'Terjadi hambatan saat update'});
                        }
                    },
                    error: () => { Swal.close(); Swal.fire({icon: 'error', title: 'Fatal Error', text: 'Koneksi ke server terputus'}); }
                });
            }
        });
    }

    function download() {
        Swal.fire({ title: 'Mengekstraksi Dataset...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        $.get('<?= site_url("dataguru/downloadData") ?>', function (res) {
            Swal.close();
            if (res.status) {
                const wb = new ExcelJS.Workbook();
                const sheet = wb.addWorksheet('Update Guru');
                sheet.addRow(['NO', 'ID', 'NIP', 'NAMA GURU', 'L/P', 'USERNAME', 'PASSWORD']);
                res.guru.forEach(g => { sheet.addRow([g.no, g.id_guru, g.nip, g.nama_guru, g.jenis_kelamin, g.username, g.password]); });
                sheet.getRow(1).font = { bold: true };
                sheet.columns.forEach(col => col.width = 18);
                wb.xlsx.writeBuffer().then(buffer => { saveAs(new Blob([buffer]), 'Dataset_Update_Guru.xlsx'); });
            }
        });
    }

    async function downloadWord() {
        Swal.fire({ title: 'Menyiapkan Tempat Foto...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        $.get('<?= site_url("dataguru/downloadData") ?>', function (res) {
            if (res.status) {
                $('#export-header').html('<th>NO</th><th>ID</th><th>NIP</th><th>NAMA GURU</th><th>L/P</th><th>FOTO</th>');
                let body = '';
                res.guru.forEach(g => {
                    body += `<tr><td align="center">${g.no}</td><td align="center">${g.id_guru}</td><td>${g.nip || ''}</td><td>${g.nama_guru}</td><td align="center">${g.jenis_kelamin}</td><td align="center"><img src="${g.foto}" width="70" /></td></tr>`;
                });
                $('#export-body').html(body);
                setTimeout(() => {
                    const content = document.getElementById('download-tbl').innerHTML;
                    const converted = htmlDocx.asBlob(`<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>${content}</body></html>`);
                    saveAs(converted, 'Template_Foto_Guru.docx');
                    Swal.close();
                }, 800);
            }
        });
    }
</script>
<?= $this->endSection() ?>