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
        --color-success-600: #16a34a;

        --color-info-50: #f0f9ff;
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
    }

    .pas-card__header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--color-neutral-100);
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
       PREMIUM INPUT GROUPS
       ============================================ */
    .premium-input-group {
        position: relative;
        width: 100%;
    }

    .p-group-icon {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3.25rem;
        display: flex; align-items: center; justify-content: center;
        color: var(--color-neutral-400); 
        font-size: 1.2rem;
        z-index: 5;
        pointer-events: none;
        transition: var(--transition-base);
    }

    .premium-input-group:focus-within .p-group-icon {
        color: var(--color-primary-600);
    }

    .premium-input-group .form-control {
        padding-left: 3.25rem !important;
        height: 3.5rem;
        background-color: var(--color-neutral-50);
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-lg);
        font-size: var(--font-size-sm);
        transition: var(--transition-base);
    }

    .premium-input-group .form-control:focus {
        border-color: var(--color-primary-400);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background-color: white;
        outline: none;
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
    .pas-btn--success:hover { background: #15803d; }

    .pas-btn--white { background: white; color: var(--color-neutral-700); border-color: var(--color-neutral-200); }
    .pas-btn--white:hover { background: var(--color-neutral-50); color: var(--color-neutral-900); }

    /* ============================================
       TABS PREMIUM
       ============================================ */
    .nav-tabs-premium { border-bottom: 2px solid var(--color-neutral-100); gap: 1rem; }
    .nav-tabs-premium .nav-link {
        border: none; padding: 1rem 1.5rem; font-weight: 600; color: var(--color-neutral-500);
        position: relative; transition: var(--transition-base);
    }
    .nav-tabs-premium .nav-link.active {
        color: var(--color-primary-600); background: transparent;
    }
    .nav-tabs-premium .nav-link.active::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0;
        height: 2px; background: var(--color-primary-600); border-radius: 2px;
    }

    .alert-premium {
        background: var(--color-info-50); border: 1px solid rgba(14, 165, 233, 0.2);
        color: var(--color-info-600); border-radius: var(--radius-lg);
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;
    }

    .preview-table-container {
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        overflow: hidden;
        background: white;
    }
</style>

<!-- Content Header (Page Header) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-plus-fill text-primary me-2"></i>
                    Registrasi &amp; Import Data Guru
                </h3>
                <p class="text-muted small mb-0 mt-1">Tambahkan data guru secara manual atau unggah berkas Excel</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dataguru') ?>" class="text-decoration-none">Data Guru</a>
                    </li>
                    <li class="breadcrumb-item active">Tambah Guru</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card pas-card border-0 shadow-sm mb-4">
            <div class="pas-card__header p-0">
                <ul class="nav nav-tabs nav-tabs-premium px-4" id="guruTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button">
                            <i class="bi bi-pencil-square me-2"></i>Tambah Manual
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button">
                            <i class="bi bi-file-earmark-excel me-2"></i>Import Excel
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="guruTabsContent">
                    <!-- Tab Manual -->
                    <div class="tab-pane fade show active" id="manual" role="tabpanel">
                        <form id="formManual" autocomplete="off">
                            <?= csrf_field() ?>
                            <div class="alert-premium mb-4">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div class="smaller">Data guru yang ditambahkan akan otomatis mendapatkan akun login dengan level <b>Guru</b>. Username dan Password bersifat sensitif.</div>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Nama Lengkap <span class="text-danger">*</span></label>
                                    <div class="premium-input-group">
                                        <span class="p-group-icon"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" class="form-control" name="nama_guru" required placeholder="Contoh: Randy Ikas, S.Pd">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">NIP / NUPTK</label>
                                    <div class="premium-input-group">
                                        <span class="p-group-icon"><i class="bi bi-hash"></i></span>
                                        <input type="number" class="form-control" name="nip" placeholder="Nomor Induk Pegawai (Opsional)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Username Akses <span class="text-danger">*</span></label>
                                    <div class="premium-input-group">
                                        <span class="p-group-icon"><i class="bi bi-person-circle"></i></span>
                                        <input type="text" class="form-control" name="username" required minlength="5" placeholder="Minimal 5 karakter">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted text-uppercase tracking-wider mb-2">Kata Sandi Default <span class="text-danger">*</span></label>
                                    <div class="premium-input-group">
                                        <span class="p-group-icon"><i class="bi bi-key-fill"></i></span>
                                        <input type="text" class="form-control" name="password" required minlength="5" placeholder="Minimal 5 karakter">
                                    </div>
                                </div>
                            </div>

                            <div class="border-top mt-5 pt-4 d-flex justify-content-end">
                                <button type="submit" class="pas-btn pas-btn--primary px-5 py-3">
                                    <i class="bi bi-save-fill me-2"></i> Simpan Data Guru
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Import -->
                    <div class="tab-pane fade" id="import" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="pas-card bg-light border-0 p-4 h-100">
                                    <h6 class="fw-bold text-dark mb-3">Langkah Unggah Berkas</h6>
                                    <ol class="smaller text-muted ps-3 mb-4">
                                        <li class="mb-2">Unduh format template Excel yang telah disediakan.</li>
                                        <li class="mb-2">Isi data guru sesuai dengan kolom yang ada.</li>
                                        <li class="mb-2">Simpan file dan unggah ke panel di bawah ini.</li>
                                    </ol>
                                    
                                    <a href="<?= base_url('assets/tpl/format_guru.xlsx') ?>" class="pas-btn pas-btn--success w-100 mb-4">
                                        <i class="bi bi-file-earmark-arrow-down-fill me-2"></i> Unduh Template Excel
                                    </a>

                                    <form id="formUpload">
                                        <input type="file" id="input-file-events" name="upload_file" class="dropify" data-height="180" data-allowed-file-extensions="xlsx" />
                                    </form>

                                    <div class="mt-4 d-grid gap-2">
                                        <button type="button" id="btn-import" class="pas-btn pas-btn--primary w-100 d-none">
                                            <i class="bi bi-cloud-upload-fill me-2"></i> Mulai Proses Import
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div id="preview-container" class="preview-table-container h-100" style="min-height: 400px; display: flex; flex-direction: column;">
                                    <div id="import-placeholder" class="m-auto text-center text-muted opacity-50 py-5">
                                        <i class="bi bi-file-earmark-excel display-1 d-block mb-3"></i>
                                        <p class="fw-bold mb-0">Preview Berkas</p>
                                        <span class="smaller">Pilih file Excel untuk memvalidasi data sebelum import</span>
                                    </div>
                                    <div id="file-preview" class="table-responsive p-0" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/dropify/css/dropify.min.css') ?>" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="<?= base_url('assets/plugins/dropify/js/dropify.min.js') ?>"></script>

<script>
    let dataGuru = [];

    $(document).ready(function () {
        // Form Manual Submit
        $('#formManual').submit(function (e) {
            e.preventDefault();
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: '<?= site_url("dataguru/create") ?>',
                type: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    Swal.close();
                    if (res.status) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data Guru berhasil terdaftar', customClass: { popup: 'rounded-4' } }).then(() => {
                            window.location.href = '<?= site_url("dataguru") ?>';
                        });
                    } else {
                        let msg = res.message || 'Gagal menyimpan data';
                        if (res.errors) msg = Object.values(res.errors).join('<br>');
                        Swal.fire({ icon: 'error', title: 'Registrasi Gagal', html: msg, customClass: { popup: 'rounded-4' } });
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'System Error', text: 'Gagal menghubungi server', customClass: { popup: 'rounded-4' } });
                }
            });
        });

        const dropify = $('.dropify').dropify();

        dropify.on('dropify.afterClear', function () {
            $('#file-preview').empty().hide();
            $('#import-placeholder').show();
            $('#btn-import').addClass('d-none').hide();
            dataGuru = [];
        });

        $('#input-file-events').on('change', function () {
            const file = this.files[0];
            if (!file) return;

            $('#import-placeholder').hide();
            $('#file-preview').show().html('<div class="text-center py-5 m-auto"><div class="spinner-border text-primary" role="status"></div><p class="mt-3 smaller text-muted fw-bold">Memvalidasi struktur file...</p></div>');

            getDataFromExcel(file).then(data => {
                const sheet = data[data.sheets[0]];
                displayPreview(sheet);
            }).catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Format Salah', text: 'Gagal membaca file Excel', customClass: { popup: 'rounded-4' } });
                dropify.clear();
            });
        });

        function getDataFromExcel(file) {
            return new Promise((resolve, reject) => {
                const wb = new ExcelJS.Workbook();
                const reader = new FileReader();
                reader.onload = () => {
                    const buffer = reader.result;
                    wb.xlsx.load(buffer).then(workbook => {
                        let dataFiles = { sheets: [] };
                        workbook.eachSheet((sheet) => {
                            dataFiles.sheets.push(sheet.name);
                            let cols = { name: sheet.name, header: [], rows: [] };
                            sheet.eachRow({ includeEmpty: false }, (row, rowIndex) => {
                                if (rowIndex === 1) {
                                    row.eachCell((cell, colNumber) => { cols.header.push({ label: cell.text, value: colNumber }); });
                                } else {
                                    let obj = {};
                                    row.eachCell({ includeEmpty: true }, (cell, colNumber) => { obj[colNumber] = cell.text; });
                                    cols.rows.push(obj);
                                }
                            });
                            dataFiles[sheet.name] = cols;
                        });
                        resolve(dataFiles);
                    });
                };
                reader.onerror = reject;
                reader.readAsArrayBuffer(file);
            });
        }

        function displayPreview(sheet) {
            let html = '<table class="table table-hover align-middle mb-0"><thead class="bg-light"><tr>';
            sheet.header.forEach(h => html += `<th class="smaller tracking-wider text-uppercase py-3 px-3 text-muted">${h.label}</th>`);
            html += '</tr></thead><tbody>';

            dataGuru = sheet.rows;
            sheet.rows.slice(0, 20).forEach(row => {
                html += '<tr>';
                sheet.header.forEach(h => {
                    const val = row[h.value] || '-';
                    html += `<td class="smaller px-3 py-2">${val}</td>`;
                });
                html += '</tr>';
            });

            if (sheet.rows.length > 20) {
                html += `<tr><td colspan="${sheet.header.length}" class="text-center p-3 smaller text-muted bg-light">Berikutnya ${sheet.rows.length - 20} data lainnya tersembunyi...</td></tr>`;
            }

            html += '</tbody></table>';
            $('#file-preview').html(html);
            $('#btn-import').removeClass('d-none').fadeIn();
        }

        $('#btn-import').click(function () {
            if (dataGuru.length === 0) return;

            Swal.fire({
                title: '<h4 class="fw-bold m-0">Inisialisasi Import?</h4>',
                html: `Sistem akan memproses <b>${dataGuru.length}</b> data guru baru.<br><small class="text-muted mt-2 d-block">Mohon jangan menutup tab ini selama proses berlangsung.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Ya, Mulai Import',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-4', confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Mengimport Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                    $.ajax({
                        url: '<?= site_url("dataguru/do_import") ?>',
                        type: 'POST',
                        data: { guru: dataGuru, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                        success: (res) => {
                            Swal.close();
                            if (res.status) {
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: `Tuntas mengimport ${res.total} data guru`, customClass: { popup: 'rounded-4' } }).then(() => {
                                    window.location.href = '<?= site_url("dataguru") ?>';
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan internal saat import', customClass: { popup: 'rounded-4' } });
                            }
                        },
                        error: () => {
                            Swal.close();
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Kegagalan komunikasi server', customClass: { popup: 'rounded-4' } });
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>