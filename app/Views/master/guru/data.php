<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header (Page Header) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-video3 text-primary me-2"></i>
                    Direktori Data Guru &amp; GTK
                </h3>
                <p class="text-muted small mb-0 mt-1">Kelola profil pendidik, kepegawaian, penugasan, &amp; sinkronisasi Dapodik</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Data Guru</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Main Card Container -->
        <div class="card pas-card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-3">
                <div class="row align-items-center g-2">
                    <div class="col-md-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-person-lines-fill text-primary me-2"></i>Daftar Tenaga Pendidik
                        </h6>
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap flex-md-nowrap">
                            <div class="input-group input-group-sm" style="max-width: 190px;">
                                <span class="input-group-text bg-white text-muted"><i class="bi bi-filter me-1"></i>Dapodik:</span>
                                <select id="dapodik-filter-guru" class="form-select">
                                    <option value="all">Semua</option>
                                    <option value="synced">Terhubung</option>
                                    <option value="local">Lokal</option>
                                </select>
                            </div>
                            <div class="input-group input-group-sm flex-grow-1" style="max-width: 250px;">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="guruSearch" class="form-control border-start-0" placeholder="Cari nama atau NIP...">
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm fw-bold text-nowrap btn-sync-dapodik" data-entitas="gtk">
                                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Sync Dapodik
                            </button>
                            <a href="<?= site_url('dataguru/add') ?>" class="btn btn-primary btn-sm fw-bold shadow-sm text-nowrap">
                                <i class="bi bi-person-plus-fill me-1"></i> Tambah / Import
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div id="viewContainer">
                    <!-- Table View -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableGuru">
                            <thead class="bg-light">
                                <tr class="text-uppercase small text-muted">
                                    <th class="ps-4 py-3" style="width: 70px;">No</th>
                                    <th class="py-3">Informasi Personal &amp; NIP</th>
                                    <th class="py-3">Mata Pelajaran Diampu</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="pe-4 py-3 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="guruTableBody">
                                <!-- AJAX Loaded -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div class="d-flex flex-wrap align-items-center justify-content-between p-3 border-top bg-light">
                    <div class="small fw-bold text-muted" id="total-entri">Menampilkan 0 - 0 dari 0 entri</div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0 shadow-sm" id="pagination">
                            <!-- Handled by TwbsPagination -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Detail Guru -->
<div class="modal fade" id="modalDetailGuru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark mb-0">
                    <i class="bi bi-person-badge-fill me-2 text-primary"></i>Detail Profil &amp; Data Dapodik Guru
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-img {
        width: 44px;
        height: 44px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/jquery.twbsPagination.js') ?>"></script>

<script>
    let mode = '<?= $mode ?>';
    let currentPage = 1;
    let totalPages = 1;
    let guruData = [];
    const siteUrl = '<?= site_url() ?>';
    let paginationInitialized = false;

    $(document).ready(function () {
        fetchGuruData(1);

        $('#guruSearch').on('keyup', function () {
            paginationInitialized = false;
            fetchGuruData(1);
        });

        $('#dapodik-filter-guru').on('change', function () {
            paginationInitialized = false;
            fetchGuruData(1);
        });

        // Sync Dapodik Trigger
        $('.btn-sync-dapodik').on('click', function() {
            const entitas = $(this).data('entitas') || 'gtk';
            Swal.fire({
                title: 'Sinkronisasi Dapodik',
                text: 'Proses ini akan mengunduh data GTK terbaru dari server Dapodik lokal.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#1877F2',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Sinkronkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Sinkronisasi...',
                        text: 'Mohon tunggu beberapa saat.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    
                    $.ajax({
                        url: siteUrl + 'integrasi/dapodik/sync/' + entitas,
                        type: 'POST',
                        dataType: 'json',
                        success: function(res) {
                            if (res.status) {
                                Swal.fire('Berhasil!', res.message || 'Sinkronisasi data GTK berhasil.', 'success').then(() => {
                                    fetchGuruData(currentPage);
                                });
                            } else {
                                Swal.fire('Gagal!', res.message || 'Gagal sinkronisasi Dapodik.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem atau koneksi server.', 'error');
                        }
                    });
                }
            });
        });
    });

    function fetchGuruData(page) {
        currentPage = page;
        const search = $('#guruSearch').val();
        const dapodikFilter = $('#dapodik-filter-guru').val();

        $.ajax({
            url: '<?= site_url('dataguru/list') ?>',
            type: 'GET',
            data: {
                page: page,
                search: search,
                dapodik: dapodikFilter
            },
            dataType: 'json',
            success: function (response) {
                if (response.status) {
                    guruData = response.data.gurus;
                    totalPages = response.data.pagination.total_pages;

                    renderData(response.data.pagination.offset);
                    updatePagination(response.data.pagination.total);
                    $('#total-entri').text(`Menampilkan ${response.data.pagination.from} - ${response.data.pagination.to} dari ${response.data.pagination.total} entri`);
                }
            }
        });
    }

    function renderData(offset) {
        let listHtml = '';

        if (guruData.length > 0) {
            guruData.forEach((guru, idx) => {
                const num = offset + idx + 1;
                const status = guru.active === '1' ? 'Aktif' : 'Nonaktif';
                const badge = guru.active === '1' ? 'success' : 'secondary';

                let mapelDisplay = '';
                if (guru.mapels && guru.mapels.length > 0) {
                    mapelDisplay = guru.mapels.map(m => {
                        let kelasBadges = '';
                        if (m.kelas && m.kelas.length > 0) {
                            kelasBadges = m.kelas.map(k => `<span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal" style="font-size: 0.7rem; padding: 0.2em 0.5em;">${k}</span>`).join('');
                        }
                        return `
                            <div class="mb-2">
                                <div class="fw-semibold text-dark small mb-1">
                                    <i class="bi bi-book-fill text-primary me-1"></i>${m.nama_mapel}
                                </div>
                                ${kelasBadges ? `<div class="d-flex flex-wrap gap-1">${kelasBadges}</div>` : ''}
                            </div>
                        `;
                    }).join('');
                } else {
                    mapelDisplay = '<span class="text-muted small">—</span>';
                }

                listHtml += `
                    <tr>
                        <td class="ps-4 fw-semibold text-muted font-monospace">#${String(num).padStart(2, '0')}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="position-relative me-3">
                                    <img src="${guru.foto}" class="avatar-img" alt="${guru.nama_guru}">
                                    ${guru.active === '1' ? '<span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1" style="width: 10px; height: 10px;"></span>' : ''}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0">${guru.nama_guru}</div>
                                    <div class="small text-muted"><i class="bi bi-person-badge me-1"></i>NIP: ${guru.nip || '-'}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        ${(guru.dapo_id || guru.ptk_id) ? '<span class="badge bg-success bg-opacity-10 text-success border"><i class="bi bi-cloud-check-fill me-1"></i>Dapodik</span>' : ''}
                                        ${guru.level ? `<span class="badge bg-primary bg-opacity-10 text-primary border">${guru.level}</span>` : ''}
                                        ${guru.nama_kelas ? `<span class="badge bg-info bg-opacity-10 text-info border">Wali ${guru.nama_kelas}</span>` : ''}
                                    </div>                                        
                                </div>
                            </div>
                        </td>

                        <td>
                            <div style="min-width: 200px; max-width: 300px;">
                                ${mapelDisplay}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge-action-status ${guru.active === '1' ? '' : 'inactive'}">${status}</span>
                        </td>
                        <td class="pe-4 text-center">
                            <div class="btn-action-group">
                                <button type="button" onclick="showDetailGuru(${idx})" class="btn btn-action btn-action-magic" data-bs-toggle="tooltip" title="Detail Profil">
                                    <i class="bi bi-magic"></i>
                                </button>
                                <a class="btn btn-action btn-action-edit" href="${siteUrl}dataguru/edit/${guru.id_guru}" data-bs-toggle="tooltip" title="Edit Profil">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a class="btn btn-action btn-action-warning" href="${siteUrl}dataguru/editjabatan/${guru.id_guru}" data-bs-toggle="tooltip" title="Penugasan Mapel & Kelas">
                                    <i class="bi bi-sliders"></i>
                                </a>
                                <button onclick="hapusGuru('${guru.id_guru}', '${guru.nama_guru}')" class="btn btn-action btn-action-delete" data-bs-toggle="tooltip" title="Hapus">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        } else {
            listHtml = `
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="bi bi-person-workspace text-muted display-4 d-block mb-3"></i>
                        <h6 class="fw-bold text-dark mb-1">Data Guru Kosong</h6>
                        <p class="small text-muted mb-0">Belum ada data guru yang tersimpan di sistem.</p>
                    </td>
                </tr>
            `;
        }

        $('#guruTableBody').html(listHtml);

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    function updatePagination(totalRecords) {
        const $pagination = $('#pagination');

        if (typeof $.fn.twbsPagination !== 'undefined') {
            if (paginationInitialized) {
                $pagination.twbsPagination('destroy');
                paginationInitialized = false;
            }

            if (totalPages > 1) {
                $pagination.twbsPagination({
                    totalPages: totalPages,
                    visiblePages: 5,
                    startPage: currentPage,
                    initiateStartPageClick: false,
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    prev: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>',
                    onPageClick: function (event, page) {
                        fetchGuruData(page);
                    }
                });
                paginationInitialized = true;
            }
        }
    }

    function showDetailGuru(idx) {
        const guru = guruData[idx];
        if (!guru) return;

        let mapelsHtml = (guru.mapels && guru.mapels.length > 0)
            ? guru.mapels.map(m => `<span class="badge bg-primary bg-opacity-10 text-primary border me-1 mb-1">${m.nama_mapel}</span>`).join('')
            : '<span class="text-muted small">—</span>';

        let html = `
            <div class="row g-3">
                <div class="col-md-4 text-center">
                    <img src="${guru.foto}" class="img-thumbnail rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    <h6 class="fw-bold mb-0 text-dark">${guru.nama_guru}</h6>
                    <p class="text-muted small mb-2">${guru.nip ? 'NIP: ' + guru.nip : 'NIP: -'}</p>
                    <span class="badge bg-${guru.active === '1' ? 'success' : 'secondary'} px-3 py-1.5">${guru.active === '1' ? 'Status: Aktif' : 'Status: Nonaktif'}</span>
                </div>
                <div class="col-md-8">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted small fw-bold" style="width: 130px;">Email / Username:</td>
                            <td class="small fw-semibold">${guru.email || guru.username || '-'}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-bold">NUPTK:</td>
                            <td class="small fw-semibold">${guru.nuptk || '-'}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-bold">NIK / KTP:</td>
                            <td class="small fw-semibold">${guru.no_ktp || '-'}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-bold">No. HP / WA:</td>
                            <td class="small fw-semibold">${guru.no_hp || '-'}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-bold">Penugasan Wali:</td>
                            <td class="small fw-semibold">${guru.nama_kelas ? 'Wali Kelas ' + guru.nama_kelas : '-'}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-bold">Status Dapodik:</td>
                            <td class="small fw-semibold">${(guru.dapo_id || guru.ptk_id) ? '<span class="text-success fw-bold"><i class="bi bi-cloud-check-fill me-1"></i>Terhubung Dapodik</span>' : '<span class="text-muted">Hanya Data Lokal</span>'}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-bold">Mapel Diampu:</td>
                            <td>${mapelsHtml}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;

        $('#modalDetailBody').html(html);
        new bootstrap.Modal(document.getElementById('modalDetailGuru')).show();
    }

    function hapusGuru(id, nama) {
        Swal.fire({
            title: 'Hapus Data Guru?',
            text: `Apakah Anda yakin ingin menghapus data guru "${nama}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("${siteUrl}dataguru/delete", { id_guru: id }, function (res) {
                    if (res.status) {
                        Swal.fire('Berhasil!', res.message || 'Data guru berhasil dihapus.', 'success').then(() => {
                            fetchGuruData(currentPage);
                        });
                    } else {
                        Swal.fire('Gagal!', res.message || 'Gagal menghapus data guru.', 'error');
                    }
                }, 'json').fail(function () {
                    Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>