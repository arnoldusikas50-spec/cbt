<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    :root {
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;
        --color-neutral-50: #f8fafc;
        --color-neutral-100: #f1f5f9;
        --color-neutral-200: #e2e8f0;
        --color-neutral-500: #64748b;
        --color-neutral-800: #1e293b;
        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.25rem;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.05);
        --transition-base: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .my-shadow {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
    }

    .text-primary-gradient {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        padding: 1.25rem;
        border-radius: var(--radius-xl);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-sm);
        transition: var(--transition-base);
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--color-primary-200);
    }

    .pas-card {
        background: white;
        border-radius: var(--radius-xl);
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

    /* Filter System */
    .premium-input-group {
        display: flex;
        align-items: center;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.25rem 0.75rem;
        transition: all 0.2s;
        width: 100%;
    }
    .premium-input-group:focus-within {
        border-color: var(--color-primary-600);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }
    .p-group-icon {
        color: #64748b;
        font-size: 1.1rem;
        margin-right: 8px;
    }
    .premium-input-group .form-control, 
    .premium-input-group .form-select {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0.5rem 0.4rem !important;
    }

    .pas-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.1rem;
        border-radius: var(--radius-lg);
        font-weight: 600;
        font-size: 0.875rem;
        transition: var(--transition-base);
        border: 1px solid transparent;
        cursor: pointer;
    }
    .pas-btn--primary {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    .pas-btn--primary:hover {
        color: white;
        opacity: 0.95;
    }
    .pas-btn--danger-soft {
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
    }
    .pas-btn--danger-soft:hover {
        background: #e11d48;
        color: white;
    }

    /* Avatar Log */
    .avatar-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        border: 2px solid white;
    }

    .table-premium thead th {
        background: var(--color-neutral-50);
        color: var(--color-neutral-500);
        font-size: 0.75rem;
        text-uppercase: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--color-neutral-200);
    }
    .table-premium tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
    }
</style>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    Log Aktivitas
                </h3>
                <p class="text-muted small mb-0">Memantau riwayat tindakan dan aktivitas pengguna sistem</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Log Aktivitas') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="app-content pt-3">
    <div class="container-fluid">

        <div class="pas-card">
            <div class="pas-card__header">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                        <i class="bi bi-list-columns-reverse fs-5"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Riwayat Aktivitas</h5>
                        <p class="text-muted small mb-0">Menampilkan data log real-time</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($is_admin): ?>
                        <button type="button" onclick="hapusLogAktivitas()" class="pas-btn pas-btn--danger-soft">
                            <i class="bi bi-trash3-fill"></i> Hapus Semua Log
                        </button>
                    <?php endif; ?>
                    <button type="button" onclick="window.location.reload()" class="pas-btn border-neutral-200 bg-white shadow-sm">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Filters -->
                <div class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Filter Berdasarkan User</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-person-badge"></i></span>
                            <select name="filterby" id="filterby" class="form-select border-0 shadow-none" multiple="multiple">
                                <?php if ($is_admin): ?>
                                    <option value="1">Admin</option>
                                <?php endif; ?>
                                <option value="2">Guru</option>
                                <option value="3">Siswa</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Pencarian Aktivitas</label>
                        <div class="premium-input-group">
                            <span class="p-group-icon"><i class="bi bi-search"></i></span>
                            <input class="form-control border-0 shadow-none" type="text" id="query" placeholder="Cari aktivitas, modul, atau nama pengguna...">
                            <button class="pas-btn pas-btn--primary py-1 px-3" id="searchby">Cari</button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px" class="text-center">Profil</th>
                                <th>Pengguna</th>
                                <th>Aktivitas & Waktu</th>
                                <?php if ($is_admin): ?>
                                    <th class="text-center" style="width: 90px;">Aksi</th>
                                    <th class="text-center" style="width: 100px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <input class="form-check-input shadow-none mt-0 me-1" type="checkbox" id="check-all" style="cursor:pointer; width:1.1rem; height:1.1rem;" title="Pilih Semua">
                                            <button class="btn btn-sm btn-outline-danger border-0 p-1" id="del-selected" title="Hapus Terpilih">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="log-list"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var colorBg = ['success', 'success', 'secondary', 'primary', 'warning', 'danger',
        'primary', "warning", "primary", "success", "primary", "success", "warning"
    ];
    const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;
    let limit = 10;
    let filter = isAdmin ? ['1', '2', '3'] : ['2', '3'];
    let loaded = 0;
    let total = 0;
    let search = '';

    $(document).ready(function () {
        load_log();

        var filterBy = $("#filterby");
        if (typeof $.fn.select2 !== 'undefined') {
            filterBy.select2({
                width: '100%',
                placeholder: "Pilih Tipe User",
                allowClear: true,
                theme: "bootstrap-5",
                closeOnSelect: false
            });

            filterBy.on("select2:clear", function (evt) {
                $(this).on("select2:opening.cancelOpen", function (evt) {
                    evt.preventDefault();
                    $(this).off("select2:opening.cancelOpen");
                });
            });

            filterBy.change(function () {
                filter = $(this).val();
                loaded = 0;
                load_log();
            });
        }

        $('#query').on('input', function (e) {
            search = $(this).val();
            if (search.trim().length === 0) {
                loaded = 0;
                load_log();
            }
        });

        $('#query').on('keypress', function (e) {
            if (e.which === 13) {
                $('#searchby').click();
            }
        });

        $('#searchby').on('click', function () {
            search = $('#query').val();
            loaded = 0;
            load_log();
        });

        $('#check-all').on('change', function () {
            $('.my-checkbox').prop('checked', $(this).is(':checked'));
        });

        $('#del-selected').on('click', function () {
            const checked = $("#log-list tr:visible .my-checkbox:checked");
            if (checked.length === 0) {
                Swal.fire({
                    title: "Peringatan",
                    text: "Tidak ada data yang dipilih",
                    icon: "warning",
                    confirmButtonColor: "#f39c12"
                });
                return;
            }

            Swal.fire({
                title: "Hapus Log Terpilih?",
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then(result => {
                if (result.value) {
                    const ids = [];
                    checked.each(function () {
                        ids.push($(this).val());
                    });

                    $.ajax({
                        url: base_url + "datalogs/hapuslog/selected",
                        type: "GET",
                        data: { ids: ids },
                        success: function (data) {
                            if (data) {
                                Swal.fire({
                                    title: "Terhapus!",
                                    text: "Log aktivitas telah dihapus.",
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    loaded -= ids.length;
                                    $('#check-all').prop('checked', false);
                                    checked.each(function () {
                                        const tr = $(this).closest('tr');
                                        tr.addClass('bg-danger bg-opacity-10')
                                            .children('td')
                                            .wrapInner('<div style="display:block;" />')
                                            .children()
                                            .slideUp(300, function () {
                                                tr.remove();
                                            });
                                    });
                                });
                            } else {
                                Swal.fire("Gagal", "Terjadi kesalahan saat menghapus data.", "error");
                            }
                        }, error: function (xhr) {
                            console.log("error", xhr.responseText);
                            Swal.fire("Error", "Gagal menghubungi server.", "error");
                        }
                    });
                }
            });
        });
    });

    function formatTanggal(date) {
        if (!date) return '-';
        var dateArray = date.split(' '),
            year = dateArray[0].split('-')[0],
            month = dateArray[0].split('-')[1],
            day = dateArray[0].split('-')[2],
            hour = dateArray[1] ? dateArray[1].split(':')[0] : '00',
            minutes = dateArray[1] ? dateArray[1].split(':')[1] : '00';

        var hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

        var d = new Date(month + "/" + day + "/" + year);
        var curr_day = d.getDay();
        var curr_date = d.getDate();
        var curr_month = d.getMonth();
        var curr_year = d.getFullYear();

        return `${hari[curr_day]}, ${curr_date} ${bulan[curr_month]} ${curr_year} <span class="badge bg-secondary bg-opacity-10 text-secondary border ms-1">${hour}:${minutes}</span>`;
    }

    function load_log(page = 1) {
        const colCount = isAdmin ? 5 : 3;
        if (page > 1 && loaded > 0) {
            $('#next-page').html(`<td class="align-middle text-center py-4 text-muted border-0" colspan="${colCount}">` +
                '<div class="d-flex justify-content-center align-items-center gap-2">' +
                '<span class="spinner-border spinner-border-sm text-primary"></span>' +
                '<span class="small fw-medium">Memuat data selanjutnya...</span></div></td>');
        } else {
            $('#log-list').html(`<tr><td class="align-middle text-center py-5 text-muted border-0" colspan="${colCount}">` +
                '<div class="d-flex flex-column align-items-center py-4">' +
                '<div class="spinner-grow text-primary mb-3" role="status"></div>' +
                '<span class="fw-medium">Menyusun riwayat aktivitas...</span></div></td></tr>');
        }

        setTimeout(function () {
            $.ajax({
                url: base_url + "datalogs/getlog",
                data: { 'page': page, 'limit': limit, 'filter': filter, 'search': search },
                method: "GET",
                success: function (data) {
                    addList(page, data);
                },
                error: function () {
                    $('#log-list').html(`<tr><td class="text-center text-danger py-5 border-0" colspan="${colCount}">` +
                        '<i class="bi bi-cloud-slash display-6 mb-3 d-block"></i>' +
                        '<div class="fw-bold">Gagal terhubung ke server</div>' +
                        '<button onclick="load_log(1)" class="btn btn-sm btn-link text-decoration-none mt-2">Coba Lagi</button></td></tr>');
                }
            });
        }, 300);
    }

    function addList(page, data) {
        if (!data.logs) return;
        const len = data.logs.length;
        loaded += len;
        const colCount = isAdmin ? 5 : 3;

        if (len > 0) {
            let body = '';
            $.each(data.logs, function (key, value) {
                body += `<tr data-log="${value.id_log}" class="fade-in-up">`;

                const nama_lengkap = value?.first_name === value?.last_name ? value?.first_name : value?.first_name + ' ' + value?.last_name;
                var nama = value.id_group === '1' ? value.first_name : nama_lengkap;
                var clr = colorBg[value.log_type] || 'primary';

                var tgl = formatTanggal(value.log_time);

                const guru = data.guru ? data.guru.find(g => g.id === value.id_user) : null;
                const siswa = data.siswa ? data.siswa.find(s => s.id === value.id_user) : null;
                const admin = data.admins ? data.admins.find(a => a.id === value.id_user) : null;

                const fotoUser = guru ? guru.foto :
                    siswa ? siswa.foto :
                        admin ? admin.foto : null;

                const initial = nama ? nama.charAt(0).toUpperCase() : '?';
                const hasFoto = fotoUser && fotoUser.length > 3 && fotoUser.indexOf('.') > -1;

                // Avatar Column
                body += `<td class="text-center">
                        <div class="avatar-circle bg-${clr} mx-auto">`;

                if (hasFoto) {
                    body += `<img style="width:100%; height:100%; object-fit:cover; border-radius:50%;" src="${base_url + fotoUser}" alt="${initial}" onError="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                             <span style="display:none;">${initial}</span>`;
                } else {
                    body += `<span>${initial}</span>`;
                }
                body += `</div></td>`;

                // User Column
                const groupLabels = { '1': 'ADMIN', '2': 'GURU', '3': 'SISWA' };
                const groupColors = { '1': 'warning', '2': 'success', '3': 'info' };
                const label = groupLabels[value.id_group] || 'USER';
                const labelColor = groupColors[value.id_group] || 'secondary';
                
                body += `<td>
                            <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                ${nama}
                                <span class="badge bg-${labelColor} bg-opacity-10 text-${labelColor} border border-${labelColor} border-opacity-25" style="font-size:0.65rem; letter-spacing:0.02em;">${label}</span>
                            </div>
                        </td>`;

                // Activity Column
                body += `<td>
                        <div class="d-flex flex-column">
                            <span class="text-dark mb-1 fw-medium" style="font-size: 0.925rem; line-height: 1.4;">${value.log_desc}</span>
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <i class="bi bi-calendar3" style="font-size:0.75rem"></i>
                                <span>${tgl}</span>
                            </div>
                        </div>
                     </td>`;

                // Action Columns
                if (isAdmin) {
                    body += `<td class="text-center">
                                <button class="btn btn-sm btn-icon btn-light text-danger border shadow-sm rounded-3" onclick="hapusLog(this)" title="Hapus Permanen">
                                    <i class="bi bi-trash"></i>
                                </button>
                             </td>`;
                    body += `<td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input my-checkbox shadow-none" type="checkbox" name="check[]" value="${value.id_log}" style="cursor:pointer; width:1.15rem; height:1.15rem;">
                                </div>
                             </td>`;
                }
                body += '</tr>';
            });

            total = data.total;

            // Load More Row
            body += `<tr id="next-page" class="bg-light border-0">
                    <td class="align-middle text-center py-4 border-0" colspan="${colCount}">
                    <div class="d-flex flex-column align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2 py-1 px-3 bg-white border rounded-pill shadow-sm">
                            <span class="text-muted small fw-bold">TERMUAT <span class="text-primary mx-1">${loaded}</span> DARI <span class="text-dark mx-1">${total}</span> DATA</span>
                        </div>`;

            if (loaded < total) {
                body += `<button id="load-page" class="pas-btn pas-btn--primary px-5 shadow">
                        <i class="bi bi-chevron-down me-1"></i> Tampilkan Lebih Banyak
                        </button>`;
            } else {
                body += `<div class="d-flex align-items-center gap-2 text-success fw-bold small mt-1">
                            <i class="bi bi-check2-all fs-5"></i>
                            <span>SELURUH RIWAYAT TELAH DITAMPILKAN</span>
                         </div>`;
            }
            body += `</div></td><tr>`;

            if (page > 1) {
                $('#next-page').remove();
                $('#log-list').append(body);
            } else {
                $('#log-list').html(body);
            }

            $('#load-page').off('click').on('click', function () {
                const nextPage = page + 1;
                load_log(nextPage);
            });
        } else {
            $('#log-list').html(`<tr><td class="align-middle text-center py-5 text-muted border-0" colspan="${colCount}">` +
                '<div class="d-flex flex-column align-items-center py-5 opacity-75">' +
                '<i class="bi bi-inbox-fill display-1 mb-3 text-neutral-200"></i>' +
                '<span class="fs-5 fw-bold text-neutral-500">Data Tidak Ditemukan</span>' +
                '<p class="small">Belum ada aktivitas yang tercatat untuk filter ini.</p></div></td></tr>');
        }
    }

    function hapusLog(e) {
        const tr = $(e).closest('tr');
        const id = tr.data('log');

        Swal.fire({
            title: "Hapus Item Log?",
            text: "Informasi aktivitas ini akan dihapus permanen.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e11d48",
            cancelButtonColor: "#64748b",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: 'rounded-3',
                cancelButton: 'rounded-3'
            }
        }).then(result => {
            if (result.value) {
                $.ajax({
                    url: base_url + `datalogs/hapuslog/${id}`,
                    type: "GET",
                    success: function (data) {
                        if (data) {
                            loaded -= 1;
                            tr.fadeOut(400, function() { $(this).remove(); });
                        } else {
                            Swal.fire("Gagal", "Tidak dapat menghapus data.", "error");
                        }
                    }, error: function () {
                        Swal.fire("Error", "Server Error.", "error");
                    }
                });
            }
        });
    }

    function hapusLogAktivitas() {
        Swal.fire({
            title: "KOSONGKAN DATA LOG?",
            html: "Anda akan menghapus <b>SELURUH</b> riwayat aktivitas. <br><span class='text-danger small mt-2 d-block'><i class='bi bi-info-circle me-1'></i>Tindakan ini tidak dapat dibatalkan!</span>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e11d48",
            cancelButtonColor: "#64748b",
            confirmButtonText: "Kosongkan Sekarang",
            cancelButtonText: "Batalkan",
            reverseButtons: true
        }).then(result => {
            if (result.value) {
                $.ajax({
                    url: base_url + "datalogs/hapuslog/all",
                    type: "GET",
                    success: function (data) {
                        if (data) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: "Tabel log aktivitas telah dikosongkan.",
                                icon: "success",
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload(true);
                            });
                        } else {
                            Swal.fire("Gagal", "Terjadi kesalahan sistem.", "error");
                        }
                    }, error: function () {
                        Swal.fire("Error", "Server Error.", "error");
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>