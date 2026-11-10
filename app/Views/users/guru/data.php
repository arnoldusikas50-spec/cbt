<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-video3 text-primary me-2"></i>
                    <?= esc($judul ?? 'User Guru') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary"><i class="bi bi-person-video me-2"></i><?= $subjudul ?></h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-1">
                    <button type="button" onclick="getDataGuru()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" class="btn btn-mass-action btn-success btn-sm shadow-sm"
                        data-action="aktifkan">
                        <i class="bi bi-people me-1"></i> Aktifkan Semua
                    </button>
                    <button type="button" class="btn btn-mass-action btn-danger btn-sm shadow-sm"
                        data-action="nonaktifkan">
                        <i class="bi bi-slash-circle me-1"></i> Nonaktifkan Semua
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small">Show
                                <select id="users_length" class="form-select form-select-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </label>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex gap-2 align-items-end justify-content-md-end">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="check-reset">
                                    <label class="form-check-label small" for="check-reset">Hanya yang perlu
                                        reset</label>
                                </div>
                                <input id="input-search" type="search" class="form-control form-control-sm" style="max-width: 200px;"
                                    placeholder="Cari...">
                                <button id="btn-search" type="button" class="btn btn-sm btn-primary"
                                    onclick="applySearch()" disabled>
                                    <i class="bi bi-search"></i>
                                </button>
                                <button id="btn-clear" type="button" class="btn btn-sm btn-secondary" disabled>
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="users" class="w-100 table table-striped table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th width="40" class="align-middle text-center p-0">No.</th>
                                <th class="align-middle">Nama Guru</th>
                                <th class="align-middle">Username</th>
                                <th class="align-middle text-break">Password</th>
                                <th class="text-center align-middle">Jabatan</th>
                                <th class="text-center align-middle">Reset Login</th>
                                <th class="text-center align-middle">Status/Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <div class="float-start" id="total-entri"></div>
                    <nav aria-label="Page navigation" class="float-end">
                        <ul class="pagination mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<?= form_open('', array('id' => 'up')) ?>
<?= form_close(); ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>assets/app/js/jquery.twbsPagination.js" type="text/javascript"></script>
<script src="<?= base_url() ?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
<script type="text/javascript">
    const user_id = '<?= $user->id ?>';
    let allData = [];
    let filteredData = [];
    let currentPage = 1;
    let perPage = 10;
    let query = null;
    let $pagination, defaultOpts;

    $(document).ready(function () {
        // ajaxcsrf();

        $("#users").on("click", ".btn-reset", function () {
            $('#loading').removeClass('d-none');
            let username = $(this).data("username");
            let nama = $(this).data("nama");
            $.ajax({
                url: base_url + "userguru/reset_login",
                type: "POST", // Changed to POST as controller expects POST
                data: { username: username, nama: nama },
                success: function (response) {
                    $('#loading').addClass('d-none');
                    if (response.msg) {
                        if (response.status) {
                            Swal.fire({
                                title: "Sukses",
                                html: "<b>" + nama + response.msg + "<b>",
                                icon: "success"
                            });
                            getDataGuru()
                        } else {
                            Swal.fire({
                                title: "Error",
                                html: "<b>" + nama + response.msg + "<b>",
                                icon: "error"
                            });
                        }
                    }
                },
                error: function (xhr, status, error) {
                    $('#loading').addClass('d-none');
                    console.log(xhr);
                    Swal.fire({
                        title: "Gagal",
                        html: xhr.responseText,
                        icon: "error"
                    });
                }
            });
        });

        $(".btn-mass-action").on("click", function () {
            console.log('click');
            let action = $(this).data("action");
            Swal.fire({
                title: action === 'aktifkan' ? "Aktifkan Semua Guru" : "Nonaktifkan Semua Guru",
                text: "",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Lanjutkan"
            }).then(async result => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    let formActivate = new FormData($('#up')[0])
                    formActivate.append('is_active', action)
                    // Updated to match controller method name if different, usually plural
                    await ajaxPost(base_url + "userguru/aktifkan_all", formActivate, function (error, response) {
                        $('#loading').addClass('d-none');
                        if (error) {
                            console.log('error', error.message);
                        } else {
                            console.log('response', response);
                            if (response.status) {
                                updateUser()
                            }
                        }
                    });
                }
            });

        });

        $pagination = $('#pagination');
        defaultOpts = {
            visiblePages: 5,
            initiateStartPageClick: false,
            onPageClick: function (event, page) {
                currentPage = page;
                renderPage();
            }
        };
        $pagination.twbsPagination(defaultOpts);

        $('#users_length').change(function () {
            perPage = parseInt($(this).val());
            currentPage = 1;
            filterData();
        });

        $('#check-reset').on('change', function () {
            currentPage = 1;
            filterData();
        });

        $('#input-search').on('change keyup', function () {
            var val = $(this).val();
            query = val === "" ? null : val.toLowerCase();
            $('#btn-clear').attr('disabled', query == null)
            $('#btn-search').attr('disabled', query == null)
            if (event.type === 'keyup') { filterData(); }
        });

        $('#btn-clear').on('click', function () {
            query = null;
            $('#input-search').val('');
            currentPage = 1;
            $(this).attr('disabled', true);
            $('#btn-search').attr('disabled', true);
            filterData();
        });

        getDataGuru()
    });

    function applySearch() {
        query = $('#input-search').val().toLowerCase();
        currentPage = 1;
        filterData();
    }

    async function updateUser() {
        let formActivate = new FormData($('#up')[0])
        await ajaxPost(base_url + "userguru/update_iduser", formActivate, function (error, response) {
            if (error) {
                console.log('error', error.message);
            } else {
                console.log('response', response);
                if (response.status) {
                    Swal.fire({
                        title: response.status ? "Sukses" : "Gagal",
                        html: response.msg,
                        icon: response.status ? "success" : "error"
                    }).then(result => {
                        getDataGuru()
                    });
                }
            }
        });
    }

    function getDataGuru() {
        $('#loading').removeClass('d-none');
        ajaxGet(base_url + 'userguru/data', function (error, response) {
            $('#loading').addClass('d-none');
            if (error) {
                console.log('error', error.message);
            } else {
                allData = response;
                filterData();
            }
        });
    }

    function filterData() {
        const needReset = $('#check-reset').is(':checked');
        
        filteredData = allData.filter(guru => {
            let match = true;
            if (needReset && guru.reset === '0') match = false;
            
            if (match && query) {
                const searchStr = `${guru.nama_guru} ${guru.username} ${guru.level}`.toLowerCase();
                if (!searchStr.includes(query)) match = false;
            }
            return match;
        });

        const totalPages = Math.ceil(filteredData.length / perPage) || 1;
        
        if (currentPage > totalPages) currentPage = totalPages;

        $pagination.removeClass('d-none');
        $pagination.twbsPagination('destroy');
        if (filteredData.length > 0) {
            $pagination.twbsPagination($.extend({}, defaultOpts, {
                startPage: currentPage,
                totalPages: totalPages,
            }));
        } else {
            $pagination.addClass('d-none');
        }

        renderPage();
    }

    function renderPage() {
        var html = '';
        
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;
        const pageData = filteredData.slice(start, end);

        if (pageData.length > 0) {
            $.each(pageData, function (idx, guru) {
                html += `<tr>
                <td class="text-center align-middle">${start + idx + 1}</td>
                <td>
                    <div class="d-flex align-items-center h-100">
                        <img class="avatar rounded-circle justify-content-center align-self-center shadow-sm" src="${guru.foto}" width="40" height="40" alt="User Image">
                            <div class="ms-2 flex-grow-1 justify-content-center align-self-center fw-bold text-dark">${guru.nama_guru}</div>
                    </div>
                </td>
                <td class="align-middle">${guru.username}</td>
                <td class="align-middle text-break">${guru.password}</td>
                <td class="align-middle text-center"><span class="badge bg-secondary">${guru.level}</span></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm ${guru.reset == '0' ? 'btn-secondary' : 'btn-danger'} shadow-sm btn-reset" ${guru.reset == '0' ? 'disabled' : ''} data-username="${guru.username}" data-nama="${guru.nama_guru}" title="Reset Login"> 
                        <i class="bi bi-arrow-repeat text-white"></i>
                    </button>
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center gap-1 align-items-center">`;
                
                if (guru.active === "1") {
                    html += `<span class="badge bg-success">Aktif</span>
                             <button type="button" class="btn btn-sm btn-warning shadow-sm" onclick="aktifkan('${guru.id}', '${guru.id_guru}', '${guru.nama_guru}', 'nonaktifkan')" title="Nonaktifkan">
                                 <i class="bi bi-slash-circle text-white"></i>
                             </button>`;
                } else {
                    html += `<span class="badge bg-danger">Nonaktif</span>
                             <button type="button" class="btn btn-sm btn-success shadow-sm" onclick="aktifkan('${guru.id}', '${guru.id_guru}', '${guru.nama_guru}', 'aktifkan')" title="Aktifkan">
                                 <i class="bi bi-person-plus-fill text-white"></i>
                             </button>`;
                }
                html += `</div></td></tr>`;
            });

            const loaded = start + 1;
            const to = start + pageData.length;
            $('#total-entri').html(`${loaded} ~ ${to} dari ${filteredData.length} entri`);
        } else {
            $('#total-entri').html(`0 ~ 0 dari 0 entri`);
            html += `<tr><td colspan="7" class="text-center align-middle">Tidak ada data guru</td><tr>`;
        }

        $('#table-body').html(html);
    }

    function aktifkan(id, id_guru, nama_guru, isActive) {
        console.log(id, id_guru, nama_guru, isActive)
        $('#loading').removeClass('d-none');
        let formActivate = new FormData($('#up')[0])
        formActivate.append('is_active', isActive)
        if (id) formActivate.append('id', id)
        formActivate.append('id_guru', id_guru)
        formActivate.append('nama', nama_guru)
        ajaxPost(base_url + 'userguru/activate', formActivate, function (error, response) {
            $('#loading').addClass('d-none');
            if (error) {
                Swal.fire({
                    title: "Error",
                    html: error.message,
                    icon: "error"
                });
            } else {
                console.log('response_e', response);
                if (response.status) {
                    Swal.fire({
                        title: "Sukses",
                        text: response.msg,
                        icon: "success"
                    }).then(result => {
                        getDataGuru()
                    });
                } else {
                    let errs = response.msg;
                    if (response.users) {
                        for (const user of response.users) {
                            errs += `<br><b>- ${user.nama_guru}</b>`;
                        }
                    }

                    Swal.fire({
                        title: "Error",
                        html: errs,
                        icon: "error"
                    });
                }
            }
        })
    }
</script>
<?= $this->endSection() ?>