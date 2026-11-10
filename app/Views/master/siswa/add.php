<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-0 fw-bold text-dark" style="letter-spacing: -1px;">
                    <span class="text-primary-gradient"><i class="bi bi-cloud-arrow-up-fill me-2"></i></span>Import Siswa Massal
                </h3>
                <p class="text-muted smaller mb-0">Impor data peserta didik dalam jumlah besar melalui template Excel</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('datasiswa') ?>" class="text-decoration-none">Data Siswa</a></li>
                    <li class="breadcrumb-item active">Import</li>
                </ol>
            </nav>
        </div>
    </div>
</div>


<div class="app-content pt-4">
    <div class="container-fluid">
        <!-- Minimalist Action Bar -->
        <div class="pas-card mb-4 border-0 shadow-sm bg-white">
            <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-soft text-primary rounded-circle p-2 me-3">
                        <i class="bi bi-file-earmark-excel fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Import Data Siswa</h6>
                        <p class="smaller text-muted mb-0">Unggah file Excel untuk memproses data massal</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <?php $url = 'uploads/import/format/format_siswa.xlsx'; ?>
                    <a href="<?= base_url($url) ?>" class="pas-btn pas-btn--white py-2 px-3 border-0 bg-light">
                        <i class="bi bi-download"></i> <span class="d-none d-md-inline">Unduh Format</span>
                    </a>
                    
                    <div class="vr mx-2 text-neutral-200"></div>

                    <label for="input-file-excel" class="mb-0 cursor-pointer">
                        <div class="pas-btn pas-btn--white py-2 px-3 border-primary-soft text-primary bg-primary-soft">
                            <i class="bi bi-search me-1"></i> <span id="btn-text-file">Pilih File</span>
                        </div>
                        <input type="file" id="input-file-excel" class="d-none" 
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    </label>

                    <button id="submit-excel" type="button" class="pas-btn pas-btn--primary py-2 px-4 shadow-sm" disabled>
                        <i class="bi bi-cloud-upload-fill me-1"></i> Import
                    </button>
                </div>
            </div>
            <div id="file-name-display" class="px-3 pb-3 smaller text-primary fw-bold d-none text-end"></div>
        </div>

        <?php if (isset($kelas)): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center p-2 rounded-4 bg-white">
                <i class="bi bi-check-circle-fill text-success me-2 ms-2"></i>
                <div class="smaller text-dark">Data otomatis dikaitkan ke <strong>Kelas: <?= $kelas->nama_kelas ?></strong>.</div>
            </div>
        <?php endif; ?>

        <div id="error-excel" class="alert alert-danger-modern mb-4 d-none p-3 border-0 shadow-sm rounded-4"></div>

        <!-- Preview Section -->
        <div class="pas-card border-0 shadow-sm">
            <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between bg-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-eye text-muted"></i>
                    <h6 class="mb-0 fw-bold text-dark">Pratinjau Data</h6>
                </div>
                <div id="preview-count" class="smaller fw-bold text-muted">Belum ada file</div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                    <table id="tableprev" class="table table-hover align-middle mb-0">
                        <thead class="bg-light sticky-top">
                            <!-- JS Generated -->
                        </thead>
                        <tbody class="border-top-0">
                            <!-- JS Generated -->
                        </tbody>
                    </table>

                    <div id="file-preview-placeholder" class="text-center py-5">
                        <div class="py-5 opacity-25">
                            <i class="bi bi-table display-1 d-block mb-3"></i>
                            <h6 class="fw-bold mb-0">Menunggu Input File...</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end">
                <a href="<?= site_url('datasiswa') ?>" class="pas-btn pas-btn--white py-2 px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
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

    .pas-card:hover { transform: translateY(-2px); }

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

    .pas-btn--success {
        background: linear-gradient(135deg, var(--color-success-600) 0%, var(--color-success-700) 100%);
        color: white;
    }

    .pas-btn--white {
        background: white;
        color: var(--color-neutral-700);
        border: 1px solid var(--color-neutral-200);
    }

    .pas-btn--white:hover {
        background: var(--color-neutral-50);
        border-color: var(--color-neutral-300);
    }

    /* ============================================
       STEP BADGE
       ============================================ */
    .step-badge {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: var(--color-primary-50);
        color: var(--color-primary-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        margin-bottom: 1rem;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }

    .icon-box-lg {
        font-size: 3rem;
        margin-bottom: 1.5rem;
    }

    /* ============================================
       FORM CONTROLS
       ============================================ */
    .form-control {
        border-radius: var(--radius-lg);
        padding: 0.75rem 1.25rem;
        font-size: var(--font-size-sm);
        border: 2px dashed var(--color-primary-200);
        background-color: var(--color-primary-50);
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
    .table thead th {
        background: var(--color-neutral-100);
        font-size: var(--font-size-xs);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 2px solid var(--color-neutral-200);
        color: var(--color-neutral-600);
    }

    .table td { padding: 0.75rem 1rem; vertical-align: middle; }

    /* Custom Scrollbar */
    .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
    .table-responsive::-webkit-scrollbar-track { background: var(--color-neutral-100); }
    .table-responsive::-webkit-scrollbar-thumb { background: var(--color-neutral-300); border-radius: 10px; }

    .smaller { font-size: var(--font-size-xs); }
    .text-primary-gradient { color: var(--color-primary-600); }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script>
    const idKelasAktif = '<?= isset($kelas) ? $kelas->id_kelas : 0 ?>';
    let dataSiswa = [];

    $(document).ready(function () {
        $('#input-file-excel').on('change', async function (e) {
            const file = e.target.files[0];
            if (!file) {
                $('#file-name-display').addClass('d-none').text('');
                return;
            }

            $('#file-name-display').removeClass('d-none').html(`<i class="bi bi-file-earmark-check me-1"></i> ${file.name}`);
            
            $('#tableprev thead').html('');
            $('#tableprev tbody').html('');
            $('#file-preview-placeholder').addClass('d-none');

            Swal.fire({
                title: "Mempelajari File",
                text: "Memproses buffer data Excel...",
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const workbook = new ExcelJS.Workbook();
                const buffer = await file.arrayBuffer();
                await workbook.xlsx.load(buffer);

                const sheet = workbook.worksheets[0];
                let header = [];
                let rows = [];

                sheet.eachRow((row, rowIndex) => {
                    const cleanRow = [];
                    row.eachCell({ includeEmpty: true }, function (cell, colNumber) {
                        let val = cell.value;
                        if (typeof val === 'object' && val !== null) {
                            if (val.text) val = val.text;
                            else if (val.result) val = val.result;
                            else if (val.richText) val = val.richText.map(t => t.text).join('');
                        }
                        cleanRow[colNumber - 1] = val;
                    });

                    for (let i = 0; i < cleanRow.length; i++) {
                        if (cleanRow[i] === undefined) cleanRow[i] = "";
                    }

                    if (rowIndex === 1) {
                        header = cleanRow;
                    } else {
                        rows.push(cleanRow);
                    }
                });

                if (rows.length === 0) {
                    Swal.fire({ icon: "warning", title: "Data Hampa", text: "File Excel tampak kosong atau baris data tidak terdeteksi.", customClass: { popup: 'rounded-4' } });
                    $('#submit-excel').prop('disabled', true);
                    return;
                }

                renderPreview(header, rows);
                remapAndValidate(rows);

                Swal.close();
                $('#submit-excel').prop('disabled', dataSiswa.length === 0);
            } catch (err) {
                console.error(err);
                Swal.fire({ icon: "error", title: "Kegagalan Buffer", text: "Gagal membaca file Excel. Pastikan format valid (.xlsx)", customClass: { popup: 'rounded-4' } });
            }
        });

        $('#submit-excel').on('click', function () {
            uploadData();
        });
    });

    function renderPreview(header, rows) {
        let hHtml = '<tr>';
        for (let i = 0; i < header.length; i++) {
            hHtml += `<th class="text-nowrap px-3 py-3">${header[i] || `Col ${i + 1}`}</th>`;
        }
        hHtml += '</tr>';
        $('#tableprev thead').html(hHtml);

        let bHtml = '';
        const limit = 20;
        rows.slice(0, limit).forEach((row, idx) => {
            bHtml += '<tr>';
            for (let i = 0; i < header.length; i++) {
                bHtml += `<td class="text-nowrap smaller px-3 border-light">${row[i] || ''}</td>`;
            }
            bHtml += '</tr>';
        });

        if (rows.length > limit) {
            bHtml += `<tr><td colspan="${header.length}" class="text-center py-4 text-muted fst-italic bg-light smaller">... dan ${rows.length - limit} baris lainnya disembunyikan dalam pratinjau ...</td></tr>`;
        }

        $('#tableprev tbody').html(bHtml);
        $('#preview-count').html(`<i class="bi bi-intersect me-2 text-primary"></i>${rows.length} Baris Terdeteksi`);
        $('#btn-text-file').text('Ganti File');
    }

    function remapAndValidate(rows) {
        dataSiswa = [];
        let errors = 0;

        rows.forEach((row, idx) => {
            const nisn = row[1];
            const nis = row[2];
            const nama = row[3];

            if (nama && (nis || nisn)) {
                dataSiswa.push({
                    nisn: row[1] || '',
                    nis: row[2] || '',
                    nama: row[3] || '',
                    jenis_kelamin: row[4] || 'L',
                    username: row[5] || row[2], 
                    password: row[6] || row[2], 
                    kelas_awal: row[7] || '',
                    tahun_masuk: row[8] || '',
                    sekolah_asal: row[9] || '',
                    tempat_lahir: row[10] || '',
                    tanggal_lahir: row[11] || '',
                    agama: row[12] || '',
                    hp: row[13] || '',
                    email: row[14] || '',
                    anak_ke: row[15] || '',
                    status_keluarga: row[16] || '',
                    alamat: row[17] || '',
                });
            } else {
                if(row.some(c => String(c).trim() !== '')) errors++;
            }
        });

        if (errors > 0 && dataSiswa.length > 0) {
            $('#error-excel').html(`<i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> <strong>Anomali Terdeteksi:</strong> Terdapat ${errors} baris yang diabaikan karena kolom krusial (Nama/NIS) tidak lengkap.`).removeClass('d-none');
        } else if (dataSiswa.length === 0) {
            $('#error-excel').html(`<i class="bi bi-x-circle-fill me-2 fs-5"></i> <strong>Format Tidak Valid:</strong> Tidak ada baris data yang layak diimport. Pastikan minimal Nama dan NIS/NISN terisi.`).removeClass('d-none');
        } else {
            $('#error-excel').addClass('d-none');
        }
    }

    async function uploadData() {
        if (dataSiswa.length === 0) return;

        Swal.fire({
            title: "Menginisialisasi Import",
            html: `<div class="p-3">Sedang merekam <b class="text-primary">${dataSiswa.length}</b> record ke database server.<br><small class="text-muted">Proses ini mungkin membutuhkan waktu beberapa saat sesuai ukuran data.</small></div>`,
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData();
        formData.append('id_kelas', idKelasAktif);
        formData.append(CSRF_NAME, CSRF_HASH);

        dataSiswa.forEach((siswa, idx) => {
            for (let key in siswa) {
                formData.append(`siswa[${idx}][${key}]`, siswa[key]);
            }
        });

        try {
            const response = await fetch('<?= site_url('datasiswa/do_import') ?>', {
                method: 'POST',
                body: formData
            });

            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                const result = await response.json();
                if (result.status) {
                    Swal.fire({
                        title: "Sinkronisasi Berhasil!",
                        text: result.message || `Sebanyak ${result.total || dataSiswa.length} data siswa telah berhasil direlasikan.`,
                        icon: "success",
                        confirmButtonText: "Lihat Data Sekarang",
                        confirmButtonColor: "#0d6efd",
                        customClass: { popup: 'rounded-5' }
                    }).then(() => {
                        window.location.href = '<?= site_url('datasiswa') ?>';
                    });
                } else {
                    Swal.fire({ icon: "error", title: "Operasi Ditolak", text: result.message || "Server menolak penyimpanan data.", customClass: { popup: 'rounded-4' } });
                }
            } else {
                const text = await response.text();
                console.error("Server Error Response:", text);
                Swal.fire({ icon: "error", title: "Server Hangup (500)", text: "Terjadi kesalahan internal pada server. Periksa alokasi memory atau batasan input PHP.", customClass: { popup: 'rounded-4' } });
            }

        } catch (err) {
            console.error(err);
            Swal.fire({ icon: "error", title: "Connection Lost", text: "Terputus dari server saat transmisi data. Cek konektivitas Anda.", customClass: { popup: 'rounded-4' } });
        }
    }
</script>
<?= $this->endSection() ?>