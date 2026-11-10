<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-0 fw-bold text-dark" style="letter-spacing: -1px;">
                    <span class="text-primary-gradient"><i class="bi bi-people-fill me-2"></i></span>Update Data Siswa
                </h3>
                <p class="text-muted smaller mb-0">Sinkronisasi massal data profil dan foto siswa via dokumen</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('datasiswa') ?>" class="text-decoration-none">Data Siswa</a></li>
                    <li class="breadcrumb-item active">Bulk Update</li>
                </ol>
            </nav>
        </div>
    </div>
</div>


<div class="app-content pt-4">
    <div class="container-fluid">
        <!-- Notification Banner -->
        <!-- Notification Banner -->
        <div class="alert alert-info border-0 shadow-sm rounded-4 p-3 d-flex align-items-center mb-4">
            <div class="bg-white rounded-circle p-2 me-3 shadow-sm text-info">
                <i class="bi bi-lightbulb-fill fs-5"></i>
            </div>
            <div class="smaller">
                <strong class="text-dark">Prosedur Efisien:</strong> Unduh data per kelas, lakukan perubahan pada file, lalu unggah kembali untuk sinkronisasi otomatis.
            </div>
        </div>


        <div class="row g-4">
            <!-- Action Sidebar -->
            <div class="col-xl-4 col-lg-5">
                <div class="pas-card h-100">
                    <div class="p-4">

                        <!-- Section: Data Profil -->
                        <div class="action-section mb-5">
                            <label class="fw-bold d-block mb-3 text-dark d-flex align-items-center">
                                <span class="bg-success-soft text-success rounded-3 p-2 me-2"><i class="bi bi-file-earmark-excel"></i></span>
                                Download Data Profil
                            </label>
                            <div class="row g-2">
                                <?php if (isset($kelas) && count($kelas) > 0): ?>
                                    <?php foreach ($kelas as $id => $kls): ?>
                                        <div class="col-6">
                                            <button onclick="downloadExcel('<?= $id ?>', '<?= $kls ?>')"
                                                class="pas-btn pas-btn--outline-success w-100 justify-content-center py-2">
                                                <?= $kls ?>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <div class="col-12 p-3 text-center bg-light rounded-3 text-muted small">Belum ada data kelas</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Section: Update Foto -->
                        <div class="action-section mb-5">
                            <label class="fw-bold d-block mb-3 text-dark d-flex align-items-center">
                                <span class="bg-info-soft text-info rounded-3 p-2 me-2"><i class="bi bi-image"></i></span>
                                Update Foto (Batch Word)
                            </label>
                            <div class="row g-2">
                                <?php if (isset($kelas) && count($kelas) > 0): ?>
                                    <?php foreach ($kelas as $id => $kls): ?>
                                        <div class="col-4">
                                            <button onclick="downloadWord('<?= $id ?>', '<?= $kls ?>')"
                                                class="pas-btn pas-btn--outline-info w-100 justify-content-center py-2">
                                                <?= $kls ?>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <div class="col-12 p-3 text-center bg-light rounded-3 text-muted small">Belum ada data kelas</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-4 opacity-5">

                        <!-- Section: Upload Area -->
                        <div class="action-section">
                            <label class="fw-bold mb-4 d-block text-dark"><i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i>Unggah Revisi Data</label>
                            
                            <div class="upload-item mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted fw-bold text-uppercase">Data Profil (.xlsx)</small>
                                </div>
                                <input type="file" id="input-excel" class="form-control premium-file-input-sm" accept=".xlsx">
                            </div>

                            <div class="upload-item">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted fw-bold text-uppercase">Arsip Foto (.docx)</small>
                                </div>
                                <input type="file" id="input-word" class="form-control premium-file-input-sm" accept=".docx">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Content -->
            <div class="col-xl-8 col-lg-7">
                <div class="pas-card h-100">
                    <div class="pas-card__header">
                        <div class="pas-card__title">
                            <i class="bi bi-eye-fill"></i>
                            <h6 class="mb-0 fw-bold">Pratinjau Sinkronisasi</h6>
                        </div>
                        <button id="btn-submit-update" class="pas-btn pas-btn--primary" disabled>
                            <i class="bi bi-cloud-check-fill"></i> Terapkan
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div id="preview-container" class="table-responsive" style="height: 600px; background: #fafbfc;">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted opacity-50">
                                <div class="floating-icon-wrapper mb-3">
                                    <i class="bi bi-file-earmark-arrow-up display-1"></i>
                                </div>
                                <h5 class="fw-bold">Menunggu Input File</h5>
                                <p class="small">Data Anda akan dipindai secara lokal sebelum diunggah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ============================================
       DESIGN TOKENS - UNIFIED SYSTEM
       ============================================ */
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

    /* ============================================
       REFINED CARD SYSTEM
       ============================================ */
    .pas-card {
        background: #ffffff;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .pas-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: #ffffff;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: var(--font-size-lg);
        font-weight: var(--font-weight-semibold);
        margin: 0;
        color: #ffffff !important;
    }

    /* ============================================
       PREMIUM BUTTONS
       ============================================ */
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

    .pas-btn--primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(79, 70, 229, 0.35);
        color: white;
    }

    .pas-btn--primary:disabled { opacity: 0.6; cursor: not-allowed; }

    .pas-btn--white {
        background: white;
        color: var(--color-neutral-700);
        border: 1px solid var(--color-neutral-200);
    }

    .pas-btn--white:hover {
        background: var(--color-neutral-50);
        border-color: var(--color-neutral-300);
    }

    .pas-btn--outline-success { border: 1px solid var(--color-success-500); color: var(--color-success-600); background: transparent; }
    .pas-btn--outline-success:hover { background: var(--color-success-50); }

    .pas-btn--outline-info { border: 1px solid var(--color-info-500); color: var(--color-info-600); background: transparent; }
    .pas-btn--outline-info:hover { background: var(--color-info-50); }

    /* ============================================
       FORM CONTROLS
       ============================================ */
    .form-control {
        border-radius: var(--radius-lg);
        padding: 0.625rem 1rem;
        font-size: var(--font-size-sm);
        border: 2px dashed var(--color-neutral-300);
        background-color: var(--color-neutral-50);
        transition: all 0.2s;
    }

    .form-control:focus {
        border-color: var(--color-primary-400);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background-color: white;
    }

    /* ============================================
       TABLES
       ============================================ */
    #preview-container thead th {
        background: var(--color-neutral-100);
        font-size: var(--font-size-xs);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 2px solid var(--color-neutral-200);
        color: var(--color-neutral-600);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    #preview-container tbody td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--color-neutral-100);
        font-size: var(--font-size-sm);
        color: var(--color-neutral-700);
    }

    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
    .table-responsive::-webkit-scrollbar-track { background: var(--color-neutral-100); }
    .table-responsive::-webkit-scrollbar-thumb { background: var(--color-neutral-300); border-radius: 10px; }

    .smaller { font-size: var(--font-size-xs); }
    .text-primary-gradient { color: var(--color-primary-600); }
    .bg-success-soft { background-color: var(--color-success-50) !important; color: var(--color-success-700) !important; }
    .bg-info-soft { background-color: var(--color-info-50) !important; color: var(--color-info-700) !important; }
    
    .floating-icon-wrapper { animation: float 4s ease-in-out infinite; opacity: 0.5; }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
</style>

<div id="hidden-word-content" class="d-none"></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/excel/exceljs.js') ?>"></script>
<script src="<?= base_url('assets/app/js/excel/js-excel-template.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/mammoth.browser.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/FileSaver.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/html-docx.js') ?>"></script>
<script src="<?= base_url('assets/app/js/convert-area.js') ?>"></script>

<script>
    let dataSiswa = [];
    let uploadType = null;

    $(document).ready(function () {
        $('#input-excel').on('change', async function (e) {
            const file = e.target.files[0];
            if (!file) return;

            Swal.fire({ title: 'Membaca Excel...', didOpen: () => Swal.showLoading() });

            try {
                const workbook = new ExcelJS.Workbook();
                await workbook.xlsx.load(await file.arrayBuffer());
                const sheet = workbook.worksheets[0];
                let header = [];
                let rows = [];

                sheet.eachRow((row, idx) => {
                    const values = row.values.slice(1);
                    if (idx === 1) header = values;
                    else rows.push(values);
                });

                renderPreview(header, rows);
                processExcelData(rows);
                uploadType = 'excel';
                $('#btn-submit-update').prop('disabled', dataSiswa.length === 0);
                Swal.close();
            } catch (err) {
                console.error(err);
                Swal.fire("Error", "Gagal membaca Excel", "error");
            }
        });

        $('#input-word').on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            Swal.fire({ title: 'Membaca Word...', didOpen: () => Swal.showLoading() });

            const reader = new FileReader();
            reader.onload = function (event) {
                mammoth.convertToHtml({ arrayBuffer: event.target.result })
                    .then(result => {
                        const html = result.value;
                        $('#preview-container').html(html).find('table').addClass('table table-sm table-bordered');
                        extractWordData();
                        uploadType = 'word';
                        $('#btn-submit-update').prop('disabled', dataSiswa.length === 0);
                        Swal.close();
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire("Error", "Gagal membaca Word", "error");
                    });
            };
            reader.readAsArrayBuffer(file);
        });

        $('#btn-submit-update').click(function () {
            submitUpdate();
        });
    });

    function renderPreview(header, rows) {
        let html = '<table class="table table-sm table-striped table-bordered mb-0"><thead><tr class="table-dark">';
        header.forEach(h => html += `<th class="text-nowrap">${h || ''}</th>`);
        html += '</tr></thead><tbody>';

        rows.slice(0, 50).forEach(row => {
            html += '<tr>';
            row.forEach(cell => html += `<td class="text-nowrap">${cell || ''}</td>`);
            html += '</tr>';
        });

        if (rows.length > 50) html += `<tr><td colspan="${header.length}" class="text-center text-muted">... ${rows.length - 50} baris lainnya ...</td></tr>`;

        html += '</tbody></table>';
        $('#preview-container').html(html);
    }

    function processExcelData(rows) {
        dataSiswa = [];
        // Assumes format: ID (43), NISN (1), NIS (2), Nama (3), ...
        rows.forEach(row => {
            if (row[43]) { // ID exists
                dataSiswa.push({
                    id_siswa: row[43],
                    nisn: row[1] || '',
                    nis: row[2] || '',
                    nama: row[3] || '',
                    // and other fields if needed for bulk update
                });
            }
        });
    }

    function extractWordData() {
        dataSiswa = [];
        $('#preview-container table tr').each(function (idx) {
            if (idx === 0) return; // Skip header
            const tds = $(this).find('td');
            const id = $(tds[1]).text().trim();
            const nis = $(tds[2]).text().trim();
            const img = $(tds[5]).find('img');

            if (id && img.length > 0) {
                const src = img.attr('src');
                if (src.startsWith('data:image/')) {
                    const ext = src.substring("data:image/".length, src.indexOf(";base64"));
                    dataSiswa.push({
                        id_siswa: id,
                        nis: nis,
                        foto: src.split(';base64,')[1],
                        ext: ext
                    });
                }
            }
        });
    }

    async function downloadExcel(idKelas, namaKelas) {
        Swal.fire({ title: 'Menyiapkan data...', didOpen: () => Swal.showLoading() });

        try {
            const response = await fetch(`<?= site_url('datasiswa/downloadData') ?>/${idKelas}`);
            const result = await response.json();

            const templateResponse = await fetch('<?= base_url('uploads/import/format/format_siswa.xlsx') ?>');
            const templateBuffer = await templateResponse.arrayBuffer();
            const excelTemplate = await JsExcelTemplate.fromArrayBuffer(templateBuffer);

            // Note: Template might need specific keys, matching CI3
            excelTemplate.set("siswa", result.siswa);

            const blob = await excelTemplate.toBlob();
            saveAs(blob, `Update_Siswa_${namaKelas}.xlsx`);
            Swal.close();
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Gagal download data", "error");
        }
    }

    async function downloadWord(idKelas, namaKelas) {
        Swal.fire({ title: 'Menyiapkan Foto...', didOpen: () => Swal.showLoading() });

        try {
            const response = await fetch(`<?= site_url('datasiswa/downloadData') ?>/${idKelas}`);
            const result = await response.json();

            let html = `<h3>Daftar Foto Siswa - ${namaKelas}</h3><table border="1" style="border-collapse: collapse; width: 100%;">`;
            html += `<tr style="background: #f0f0f0;"><th>No</th><th>ID</th><th>NIS</th><th>NAMA</th><th>JK</th><th>FOTO</th></tr>`;

            result.siswa.forEach((s, i) => {
                html += `<tr>
                    <td align="center">${i + 1}</td>
                    <td align="center">${s.id_siswa}</td>
                    <td align="center">${s.nis}</td>
                    <td>${s.nama}</td>
                    <td align="center">${s.jenis_kelamin}</td>
                    <td align="center"><img src="${s.foto}" width="60" height="auto"></td>
                </tr>`;
            });
            html += '</table>';

            // Mammoth/html-docx magic
            const converted = htmlDocx.asBlob(html, { orientation: 'portrait', margins: { top: 720 } });
            saveAs(converted, `Update_Foto_${namaKelas}.docx`);
            Swal.close();
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Gagal menyiapkan file Word", "error");
        }
    }

    async function submitUpdate() {
        Swal.fire({
            title: "Memperbarui Data",
            text: `Sedang mengirim ${dataSiswa.length} data...`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const url = uploadType === 'excel' ? '<?= site_url('datasiswa/updatedata') ?>' : '<?= site_url('datasiswa/update_foto') ?>';

        const formData = new FormData();
        formData.append(CSRF_NAME, CSRF_HASH);
        formData.append('method', 'bulk');

        dataSiswa.forEach((s, i) => {
            for (let key in s) {
                formData.append(`siswa[${i}][${key}]`, s[key]);
            }
        });

        try {
            const response = await fetch(url, { method: 'POST', body: formData });
            const result = await response.json();

            if (result.status) {
                Swal.fire("Berhasil", result.message || "Data berhasil diperbarui", "success").then(() => {
                    window.location.href = '<?= site_url('datasiswa') ?>';
                });
            } else {
                Swal.fire("Gagal", result.message || "Gagal update data", "error");
            }
        } catch (err) {
            console.error(err);
            Swal.fire("Error", "Koneksi terputus", "error");
        }
    }
</script>
<?= $this->endSection() ?>