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
        --color-success-700: #15803d;

        --color-info-50: #f0f9ff;
        --color-info-500: #0ea5e9;
        --color-info-600: #0284c7;

        --color-warning-50: #fffbeb;
        --color-warning-500: #f59e0b;
        --color-warning-600: #d97706;

        --color-danger-50: #fef2f2;
        --color-danger-500: #ef4444;
        --color-danger-600: #dc2626;

        --color-neutral-50: #f9fafb;
        --color-neutral-100: #f3f4f6;
        --color-neutral-200: #e5e7eb;
        --color-neutral-300: #d1d5db;
        --color-neutral-400: #9ca3af;
        --color-neutral-500: #6b7280;
        --color-neutral-600: #4b5563;
        --color-neutral-700: #374151;
        --color-neutral-800: #1f2937;
        --color-neutral-900: #111827;

        --radius-sm: 0.375rem;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
        --radius-full: 9999px;

        --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --font-size-base: 1rem;
        --font-size-lg: 1.125rem;
        --font-size-xl: 1.25rem;
        
        --transition-base: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: 'Inter', sans-serif !important;
        background-color: #f8fafc !important;
    }

    .text-primary-gradient {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    .pas-card {
        background: white;
        border-radius: var(--radius-2xl);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .pas-card__header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--color-neutral-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--color-neutral-800);
    }

    .pas-card__title-icon {
        width: 40px;
        height: 40px;
        background: var(--color-primary-50);
        color: var(--color-primary-600);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .pas-card__actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .pas-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.55rem 1.15rem;
        font-weight: 600;
        font-size: var(--font-size-sm);
        border-radius: var(--radius-lg);
        transition: var(--transition-base);
        cursor: pointer;
        border: 1px solid transparent;
        white-space: nowrap;
        text-decoration: none !important;
    }

    .pas-btn--sm {
        padding: 0.35rem 0.85rem;
        font-size: var(--font-size-xs);
    }

    .pas-btn--primary {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }
    .pas-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35); color: white; }

    .pas-btn--white {
        background: white;
        color: var(--color-neutral-700);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-xs);
    }
    .pas-btn--white:hover { background: var(--color-neutral-50); border-color: var(--color-neutral-300); color: var(--color-neutral-900); }

    .pas-btn--outline-info { border: 1px solid var(--color-info-500); color: var(--color-info-600); background: transparent; }
    .pas-btn--outline-info:hover { background: var(--color-info-50); }

    .pas-btn--outline-success { border: 1px solid var(--color-success-500); color: var(--color-success-600); background: transparent; }
    .pas-btn--outline-success:hover { background: var(--color-success-50); }

    .pas-btn--danger { background: var(--color-danger-500); color: white; }
    .pas-btn--danger:hover { background: var(--color-danger-600); }

    .pas-badge {
        padding: 0.3rem 0.75rem;
        font-size: var(--font-size-xs);
        font-weight: 600;
        border-radius: var(--radius-full);
        display: inline-flex;
        align-items: center;
        line-height: 1.25;
        border: 1px solid transparent;
    }

    .pas-badge--primary { background: var(--color-primary-50); color: var(--color-primary-700); }
    .pas-badge--success { background: var(--color-success-50); color: var(--color-success-700); }
    .pas-badge--info { background: var(--color-info-50); color: var(--color-info-700); }
    .pas-badge--warning { background: var(--color-warning-50); color: var(--color-warning-700); }
    .pas-badge--danger { background: var(--color-danger-50); color: var(--color-danger-700); }
    .pas-badge--neutral { background: var(--color-neutral-100); color: var(--color-neutral-600); }

    .search-group {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-full);
        padding: 0.25rem 0.5rem;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    .search-input {
        border: none;
        padding: 0.35rem 0.85rem;
        font-size: var(--font-size-sm);
        width: 100%;
        outline: none;
        background: transparent;
    }
    .pas-form-select {
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-md);
        font-size: var(--font-size-sm);
        padding: 0.375rem 0.75rem;
        background-color: white;
    }

    .hover-row { transition: var(--transition-base); }
    .hover-row:hover { background-color: var(--color-neutral-50); }
    .smaller { font-size: 0.75rem; }
    .tracking-wider { letter-spacing: 0.05em; }
</style>

<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark fs-4" style="font-family: 'Inter', sans-serif; letter-spacing: -0.5px;">
                    <span class="text-primary-gradient"><i class="bi bi-people-fill me-2"></i></span>Manajemen Siswa
                </h3>
                <p class="text-muted mb-0">Kelola basis data peserta didik, status akademik, dan sinkronisasi Dapodik</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Data Siswa</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid">

        <!-- Main Data Container -->
        <div class="pas-card">
            <div class="pas-card__header">
                <div class="pas-card__title">
                    <div class="pas-card__title-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Direktori Data Siswa</h5>
                        <div style="font-size: 0.75rem; opacity: 0.8; font-weight: normal;">Kelola individu, impor massal, atau perbarui atribut data</div>
                    </div>
                </div>
                <div class="pas-card__actions">
                    <button type="button" class="pas-btn pas-btn--white" onclick="loadSiswa()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                    <button type="button" class="pas-btn pas-btn--white btn-sync-dapodik text-primary" data-entitas="siswa">
                        <i class="bi bi-cloud-arrow-down-fill text-primary"></i> Sync Dapodik
                    </button>
                    <button type="button" class="pas-btn pas-btn--primary" data-bs-toggle="modal" data-bs-target="#createSiswaModal">
                        <i class="bi bi-plus-lg"></i> Tambah Siswa
                    </button>
                </div>
            </div>

            <div class="pas-card__body p-4">
                <!-- Action Bar (Consolidated Filter & Bulk) -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="dropdown">
                            <button id="dropdown-btn" class="pas-btn pas-btn--danger pas-btn--sm dropdown-toggle" type="button" data-bs-toggle="dropdown" disabled>
                                Aksi Massal
                            </button>
                            <ul class="dropdown-menu border-0 shadow-lg rounded-3 mt-2" id="dropdown-action">
                                <li><a class="dropdown-item py-2 px-3" id="pindah" href="#"><i class="bi bi-box-arrow-right me-2 text-warning"></i>Set sebagai PINDAH</a></li>
                                <li><a class="dropdown-item py-2 px-3" id="keluar" href="#"><i class="bi bi-person-x me-2 text-danger"></i>Set sebagai KELUAR</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 px-3 text-danger fw-bold" id="hapus" href="#"><i class="bi bi-trash3-fill me-2"></i>HAPUS DATA</a></li>
                            </ul>
                        </div>
                        <div class="d-flex align-items-center">
                            <label class="smaller fw-bold text-muted text-uppercase me-2 mb-0">Status:</label>
                            <select id="users-filter" class="pas-form-select py-1">
                                <option value="0">Semua Status</option>
                                <option value="1">Siswa Aktif</option>
                                <option value="2">Lulus / Pindah / Keluar</option>
                                <option value="3">Per Kelas</option>
                            </select>
                            <div id="kelas-filter-container" class="d-none ms-2">
                                <select id="kelas-filter" class="pas-form-select py-1">
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelass as $id => $name): ?>
                                        <option value="<?= $id ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <label class="smaller fw-bold text-muted text-uppercase me-2 mb-0">Dapodik:</label>
                            <select id="dapodik-filter" class="pas-form-select py-1">
                                <option value="all">Semua Data</option>
                                <option value="synced">Terhubung Dapodik</option>
                                <option value="local">Hanya Data Lokal</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= site_url('datasiswa/add') ?>" class="pas-btn pas-btn--outline-info pas-btn--sm">
                            <i class="bi bi-cloud-arrow-up"></i> Import
                        </a>
                        <a href="<?= site_url('datasiswa/update') ?>" class="pas-btn pas-btn--outline-success pas-btn--sm">
                            <i class="bi bi-database-fill-gear"></i> Update Bulk
                        </a>
                        <div class="search-group ms-2" style="width: 250px;">
                            <input id="input-search" type="text" class="search-input" placeholder="Cari nama/NIS...">
                            <button id="btn-search" class="pas-btn pas-btn--primary p-2 rounded-circle" style="width: 32px; height: 32px; min-width: 32px;" onclick="applySearch()">
                                <i class="bi bi-search" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="position-relative">
                    <form id="bulk">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="table-siswa">
                                <thead>
                                    <tr>
                                        <th class="ps-4 py-3" style="width: 50px;">
                                            <input class="select_all form-check-input shadow-none" type="checkbox">
                                        </th>
                                        <th class="py-3 text-muted smaller tracking-wider text-uppercase" style="width: 70px;">Rank</th>
                                        <th class="py-3 text-muted smaller tracking-wider text-uppercase">Profil Peserta Didik</th>
                                        <th class="py-3 text-muted smaller tracking-wider text-uppercase">Informasi Identitas</th>
                                        <th class="py-3 text-muted smaller tracking-wider text-uppercase text-center">Aksi Pintas</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body" class="border-top-0">
                                    <!-- AJAX Loaded -->
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Loading State -->
                    <div id="loading" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 10;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Pagination & Info Footer -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top">
                    <div class="smaller text-muted fw-semibold" id="total-entri">
                        Menampilkan 0 dari 0 entri
                    </div>
                    <form id="pager" class="d-none">
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="limit" value="10">
                    </form>
                    <ul id="pagination" class="pagination pagination-sm mb-0"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Siswa -->
<div class="modal fade" id="createSiswaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 my-shadow rounded-4">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah Peserta Didik Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createSiswaForm">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_siswa" class="form-control" required placeholder="Nama Siswa">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control" required placeholder="NIS">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">NISN <span class="text-danger">*</span></label>
                            <input type="text" name="nisn" class="form-control" required placeholder="NISN">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required placeholder="Username Login">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Agama</label>
                            <select name="agama" class="form-select">
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Khonghucu">Khonghucu</option>
                                <option value="Kepercayaan thd Tuhan YME">Kepercayaan thd Tuhan YME</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-save me-1"></i> Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Profil & Dapodik Siswa -->
<div class="modal fade" id="modalDetailSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 my-shadow rounded-4">
            <div class="modal-header border-bottom bg-light">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-person-badge-fill me-2"></i>Detail Profil & Data Dapodik Siswa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detailSiswaBody">
                <!-- Dynamically Populated -->
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/jquery.twbsPagination.js') ?>"></script>
<script>
    let currentPage = 1;
    let perPage = 10;
    let query = null;
    let $pagination, defaultOpts;
    let globalSiswaList = [];

    $(document).ready(function () {
        $pagination = $('#pagination');
        defaultOpts = {
            visiblePages: 5,
            initiateStartPageClick: false,
            first: '<i class="bi bi-chevron-double-left"></i>',
            prev: '<i class="bi bi-chevron-left"></i>',
            next: '<i class="bi bi-chevron-right"></i>',
            last: '<i class="bi bi-chevron-double-right"></i>',
            onPageClick: function (event, page) {
                currentPage = page;
                loadSiswa();
            }
        };
        if (typeof $.fn.twbsPagination !== 'undefined' && $pagination.length) {
            $pagination.twbsPagination(defaultOpts);
        }

        // Filter Handlers
        $('#users-filter').on('change', function () {
            if ($(this).val() == "3") {
                $('#kelas-filter-container').removeClass('d-none');
            } else {
                $('#kelas-filter-container').addClass('d-none');
                currentPage = 1;
                loadSiswa();
            }
        });

        $('#kelas-filter, #dapodik-filter').on('change', function () {
            currentPage = 1;
            loadSiswa();
        });

        // Search Input
        let searchTimer;
        $('#input-search').on('input', function () {
            clearTimeout(searchTimer);
            query = $(this).val();
            searchTimer = setTimeout(function () {
                currentPage = 1;
                loadSiswa();
            }, 400);
        });

        loadSiswa();
    });

    function applySearch() {
        query = $('#input-search').val();
        currentPage = 1;
        loadSiswa();
    }

    function loadSiswa() {
        $('#loading').removeClass('d-none');

        var cari = query != null ? '&search=' + query : '';
        var filter = '&filter=' + $('#users-filter').val();
        var kelas = '&kelas=' + $('#kelas-filter').val();
        var dataPost = $('#pager').serialize() + cari + filter + kelas + '&<?= csrf_token() ?>=' + '<?= csrf_hash() ?>';

        $.ajax({
            url: '<?= site_url('datasiswa/list') ?>',
            data: dataPost,
            type: 'POST',
            success: function (data) {
                globalSiswaList = data.lists || [];

                // Client-side Dapodik filter if selected
                const dapoFilter = $('#dapodik-filter').val();
                if (dapoFilter === 'synced') {
                    globalSiswaList = globalSiswaList.filter(s => s.dapo_id && s.dapo_id !== '');
                } else if (dapoFilter === 'local') {
                    globalSiswaList = globalSiswaList.filter(s => !s.dapo_id || s.dapo_id === '');
                }

                if (data.pages > 0) {
                    $pagination.removeClass('d-none');
                    if (typeof $.fn.twbsPagination !== 'undefined') {
                        $pagination.twbsPagination('destroy');
                        $pagination.twbsPagination($.extend({}, defaultOpts, {
                            startPage: currentPage,
                            totalPages: data.pages,
                        }));
                    }
                } else {
                    $pagination.addClass('d-none');
                }

                previewData({ lists: globalSiswaList, total: data.total });
            },
            error: function () {
                $('#loading').addClass('d-none');
                showToast('error', 'Gagal memuat data siswa');
            }
        });
    }

    function previewData(data) {
        let html = '';
        if (data.lists && data.lists.length > 0) {
            data.lists.forEach((siswa, idx) => {
                const num = (perPage * (currentPage - 1)) + (idx + 1);
                const kls = siswa.nama_kelas ? `<span class="pas-badge pas-badge--info me-1">${siswa.nama_kelas}</span>` : '';
                const agama = siswa.agama ? `<span class="pas-badge pas-badge--neutral me-1">${siswa.agama}</span>` : '';

                const statusSiswa = {
                    '1': { status: 'Aktif', color: 'success' },
                    '2': { status: 'Lulus', color: 'primary' },
                    '3': { status: 'Pindah', color: 'warning' },
                    '4': { status: 'Keluar', color: 'danger' }
                };

                const status = (siswa.active == "0" && siswa.status == "1")
                    ? '<span class="pas-badge pas-badge--neutral">Nonaktif</span>'
                    : `<span class="pas-badge pas-badge--${statusSiswa[siswa.status]?.color || 'neutral'}">${statusSiswa[siswa.status]?.status || 'Unknown'}</span>`;

                const dapoBadge = siswa.dapo_id
                    ? `<span class="pas-badge pas-badge--success me-1" title="ID Dapodik: ${siswa.dapo_id}"><i class="bi bi-cloud-check-fill me-1"></i>Dapodik</span>`
                    : '<span class="pas-badge pas-badge--neutral me-1" style="opacity:0.6;"><i class="bi bi-cloud-slash me-1"></i>Lokal</span>';

                html += `<tr class="transition-all hover-row">
                    <td class="text-center align-middle ps-4">
                        <input name="checked[]" class="check form-check-input shadow-none" value="${siswa.id_siswa}" type="checkbox">
                    </td>
                    <td>
                        <span class="pas-badge pas-badge--neutral">#${String(num).padStart(2, '0')}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img class="rounded-circle border shadow-sm me-3" src="${siswa.foto}" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid white !important;" onerror="this.onerror=null; this.src='<?= base_url('assets/img/siswa_sm.png') ?>';">
                            <div>
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">${siswa.nama}</div>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    ${kls} ${agama} ${status} ${dapoBadge}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="smaller text-muted fw-bold mb-1" style="letter-spacing: 0.05em;"><i class="bi bi-hash me-1 text-primary"></i>IDENTITAS SISWA</div>
                        <div class="fw-bold text-neutral-700" style="font-size: 0.88rem;">NIS: ${siswa.nis || '-'} <span class="text-muted fw-normal mx-1">|</span> NISN: ${siswa.nisn || '-'}</div>
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group justify-content-center">
                            <button type="button" class="btn btn-action btn-action-magic btn-detail-siswa" onclick="showDetailSiswa(${idx})" title="Detail Profil & Dapodik">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                            <a class="btn btn-action btn-action-edit" href="<?= site_url('datasiswa/edit/') ?>${siswa.id_siswa}" title="Edit Profil Siswa">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                        </div>
                    </td>
                </tr>`;
            });
        } else {
            html = '<tr><td colspan="5" class="text-center py-5"><div class="opacity-25 my-4"><i class="bi bi-person-x display-1 d-block mb-3"></i><h5 class="fw-bold">Database Kosong</h5><p class="small">Tidak ada record siswa yang sesuai dengan kriteria filter.</p></div></td></tr>';
        }
        $('#table-body').html(html);

        const from = (perPage * (currentPage - 1)) + 1;
        const to = from + (data.lists ? data.lists.length : 0) - 1;
        $('#total-entri').text(data.total > 0 ? `Menampilkan ${from} - ${to} dari ${data.total} entri` : '0 entri ditemukan');

        $('#loading').addClass('d-none');
    }

    // Modal Detail Siswa & Status Dapodik
    function showDetailSiswa(idx) {
        const s = globalSiswaList[idx];
        if (!s) return;

        const dapoStatusHtml = s.dapo_id
            ? `<div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-4 p-3 rounded-3">
                 <i class="bi bi-cloud-check-fill text-success fs-2 me-3"></i>
                 <div>
                     <strong class="text-success d-block">Terhubung dengan Web Service Dapodik</strong>
                     <span class="small text-muted">ID Peserta Didik (Dapodik): <code class="bg-white px-2 py-1 rounded border text-dark">${s.dapo_id}</code></span>
                 </div>
               </div>`
            : `<div class="alert alert-secondary border-0 bg-secondary bg-opacity-10 d-flex align-items-center mb-4 p-3 rounded-3">
                 <i class="bi bi-cloud-slash text-secondary fs-2 me-3"></i>
                 <div>
                     <strong class="text-secondary d-block">Belum Terhubung Dapodik (Record Lokal)</strong>
                     <span class="small text-muted">Jalankan tombol "Sync Dapodik" untuk menyelaraskan siswa ini dengan data Dapodik sekolah.</span>
                 </div>
               </div>`;

        const html = `
            ${dapoStatusHtml}
            <div class="row g-4">
                <div class="col-md-4 text-center border-end">
                    <img src="${s.foto}" class="rounded-circle shadow border p-1 mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    <h5 class="fw-bold mb-1 text-dark">${s.nama}</h5>
                    <span class="badge bg-primary px-3 py-2 rounded-pill">${s.nama_kelas || 'Tanpa Kelas'}</span>
                </div>
                <div class="col-md-8">
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-person-lines-fill me-1"></i> Identitas Peserta Didik</h6>
                    <div class="row g-2 small">
                        <div class="col-6"><strong class="text-muted">NIS:</strong> ${s.nis || '-'}</div>
                        <div class="col-6"><strong class="text-muted">NISN:</strong> ${s.nisn || '-'}</div>
                        <div class="col-6"><strong class="text-muted">NIK:</strong> ${s.nik || '-'}</div>
                        <div class="col-6"><strong class="text-muted">Jenis Kelamin:</strong> ${s.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</div>
                        <div class="col-6"><strong class="text-muted">Tempat Lahir:</strong> ${s.tempat_lahir || '-'}</div>
                        <div class="col-6"><strong class="text-muted">Tgl Lahir:</strong> ${s.tanggal_lahir || '-'}</div>
                        <div class="col-6"><strong class="text-muted">Agama:</strong> ${s.agama || '-'}</div>
                        <div class="col-6"><strong class="text-muted">No. HP:</strong> ${s.hp || '-'}</div>
                    </div>

                    <h6 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-3"><i class="bi bi-people me-1"></i> Data Orang Tua & Alamat</h6>
                    <div class="row g-2 small">
                        <div class="col-6"><strong class="text-muted">Nama Ayah:</strong> ${s.nama_ayah || '-'}</div>
                        <div class="col-6"><strong class="text-muted">Nama Ibu:</strong> ${s.nama_ibu || '-'}</div>
                        <div class="col-12"><strong class="text-muted">Alamat Jalan:</strong> ${s.alamat || '-'}</div>
                    </div>
                </div>
            </div>
        `;

        $('#detailSiswaBody').html(html);
        $('#modalDetailSiswa').modal('show');
    }

    // Single Create Form AJAX
    $('#createSiswaForm').on('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        try {
            const res = await fetch('<?= site_url('datasiswa/create') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.insert || data.status) {
                Swal.fire('Berhasil', 'Data siswa berhasil ditambahkan', 'success');
                $('#createSiswaModal').modal('hide');
                this.reset();
                loadSiswa();
            } else {
                Swal.fire('Gagal', data.text || data.message || 'Gagal menyimpan siswa', 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
        }
    });

    function showToast(type, msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: msg,
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(msg);
        }
    }
</script>
<?= $this->endSection() ?>