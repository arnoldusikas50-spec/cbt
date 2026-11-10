<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    <?= esc($judul ?? 'User Siswa') ?>
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
                <h3 class="card-title fw-bold text-primary"><i class="bi bi-people me-2"></i><?= $subjudul ?></h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-1">
                    <?php if ($is_admin): ?>
                        <button type="button" class="btn btn-mass-action btn-success btn-sm shadow-sm"
                            data-action="aktifkan">
                            <i class="bi bi-people me-1"></i> Aktifkan Semua
                        </button>
                        <button type="button" class="btn btn-mass-action btn-danger btn-sm shadow-sm"
                            data-action="nonaktifkan">
                            <i class="bi bi-slash-circle me-1"></i> Nonaktifkan Semua
                        </button>
                    <?php endif; ?>
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
                            <div class="d-flex gap-2 align-items-end">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="check-reset">
                                    <label class="form-check-label small" for="check-reset">Hanya yang perlu
                                        reset</label>
                                </div>
                                <input id="input-search" type="search" class="form-control form-control-sm"
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
                <?= form_open('', array('id' => 'bulk')); ?>
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table id="users" class="w-100 table table-striped table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th width="40" class="align-middle text-center p-0">No.</th>
                                <th class="align-middle">NIS</th>
                                <th class="align-middle">Nama</th>
                                <th class="text-center align-middle">Kelas</th>
                                <th class="align-middle">Username</th>
                                <th class="align-middle">Password</th>
                                <th class="text-center align-middle">Reset Login</th>
                                <?php if ($is_admin): ?>
                                    <th class="align-middle text-center">Status/Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                        </tbody>
                    </table>
                </div>
                <?= form_close() ?>
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
<?= form_open('', array('id' => 'pager')); ?>
<input type="hidden" id="pager-page" name="page" value="1">
<input type="hidden" id="pager-limit" name="limit" value="10">
<?= form_close() ?>

<script src="<?= base_url() ?>assets/app/js/jquery.twbsPagination.js" type="text/javascript"></script>
<script type="text/javascript">
    let currentPage = 1;
    let perPage = 10;
    let isAdmin = "<?= $is_admin ? '1' : '0' ?>";
    let $pagination, defaultOpts, query;

    $(document).ready(async function () {
        // ajaxcsrf();

        $pagination = $('#pagination');
        defaultOpts = {
            visiblePages: 5, // CI4 pagination usually handles HTML, but here using JS plugin
            initiateStartPageClick: false,
            onPageClick: function (event, page) {
                console.info(page + ' (from options)');
                currentPage = page;
                loadSiswa();
            }
        };
        $pagination.twbsPagination(defaultOpts);

        $('#users_length').change(function () {
            $('#pager-limit').val($(this).val());
            perPage = $(this).val();
            currentPage = 1;
            loadSiswa();
        });

        $('#check-reset').on('change', function () {
            currentPage = 1;
            loadSiswa();
        });

        $('#input-search').on('change keyup', function () {
            var val = $(this).val();
            query = val === "" ? null : val;
            $('#btn-clear').attr('disabled', query == null)
            $('#btn-search').attr('disabled', query == null)
        });

        $('#btn-clear').on('click', function () {
            query = null;
            currentPage = 1;
            $(this).attr('disabled', query == null);
            $('#btn-search').attr('disabled', query == null);
            loadSiswa();
        });

        $("#users").on("click", ".btn-aktif", async function () {
            let id = $(this).data("id");
            let nama = encodeURIComponent($(this).data("nama"));
            $('#loading').removeClass('d-none');

            let formActivate = new FormData($('#pager')[0])
            formActivate.append('is_active', 'aktifkan')
            formActivate.append('id_siswa', id)
            formActivate.append('nama', nama)
            const data = await ajaxPost('usersiswa/activate', formActivate)
            console.log("response:", data);
            if (data.status) {
                Swal.fire({
                    title: "Sukses",
                    text: data.msg,
                    icon: "success"
                }).then(result => {
                    loadSiswa();
                });
            } else {
                Swal.fire({
                    title: "Error",
                    text: data.msg,
                    icon: "error"
                });
            }
        });

        $("#users").on("click", ".btn-nonaktif", async function () {
            let id = $(this).data("id");
            let username = $(this).data("username");
            let nama = encodeURIComponent($(this).data("nama"));
            $('#loading').removeClass('d-none');

            let formActivate = new FormData($('#pager')[0])
            formActivate.append('is_active', 'nonaktifkan')
            formActivate.append('id_siswa', id)
            formActivate.append('username', username)
            formActivate.append('nama', nama)
            const data = await ajaxPost('usersiswa/activate', formActivate)
            console.log("response:", data);
            if (data.status) {
                Swal.fire({
                    title: "Sukses",
                    html: data.msg,
                    icon: "success"
                }).then(result => {
                    loadSiswa();
                });
            } else {
                Swal.fire({
                    title: "Error",
                    text: data.msg,
                    icon: "error"
                });
            }
        });

        $("#users").on("click", ".btn-reset", async function () {
            let username = $(this).data("username");
            let id = $(this).data("id");
            let nama = encodeURIComponent($(this).data("nama"));
            $('#loading').removeClass('d-none');

            let formActivate = new FormData($('#pager')[0])
            formActivate.append('id_siswa', id)
            formActivate.append('username', username)
            formActivate.append('nama', nama)
            const response = await ajaxPost('usersiswa/reset_login', formActivate)
            if (response.status) {
                Swal.fire({
                    title: "Sukses",
                    html: "<b>" + decodeURIComponent(response.msg) + "<b>",
                    icon: "success"
                }).then(result => {
                    loadSiswa();
                });
            } else {
                Swal.fire({
                    title: "Error",
                    html: "<b>" + decodeURIComponent(response.msg) + "<b>",
                    icon: "error"
                });
            }
            $('#loading').addClass('d-none');
        });

        $(".btn-mass-action").on("click", function () {
            let action = $(this).data("action");

            Swal.fire({
                title: action === 'aktifkan' ? "Aktifan semua siswa" : "Nonaktifkan semua siswa",
                text: "",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Lanjutkan"
            }).then(async result => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    Swal.fire({
                        title: action === 'aktifkan' ? "Mengaktifkan semua siswa" : "Menonaktifkan semua siswa",
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    let formActivate = new FormData($('#pager')[0])
                    formActivate.append('is_active', action)

                    const response = await ajaxPost("usersiswa/aktifkansemua", formActivate)
                    console.log("result", response);
                    if (response) {
                        Swal.fire({
                            title: response.status ? "Sukses" : "Gagal",
                            html: response.msg || (response.status ? "Berhasil mengupdate status siswa" : "Gagal mengupdate status siswa"),
                            icon: response.status ? "success" : "error"
                        }).then(result => {
                            if (response.status) loadSiswa();
                        });
                    }
                    $('#loading').addClass('d-none');
                }
            });
        });

        await loadSiswa();

    });

    async function loadSiswa() {
        $('#pager-page').val(currentPage);
        $('#loading').removeClass('d-none');

        let dataPost = new FormData($('#pager')[0])
        if (query != null) dataPost.append('search', query)
        const reset = $('#check-reset').is(':checked') ? '1' : '0';
        dataPost.append('reset', reset);

        const data = await ajaxPost('usersiswa/list', dataPost)
        if (data.pages > 0) {
            $pagination.removeClass('d-none');
            $pagination.twbsPagination('destroy');
            $pagination.twbsPagination($.extend({}, defaultOpts, {
                startPage: currentPage,
                totalPages: data.pages,
            }));
        }
        previewData(data);

        $('#loading').addClass('d-none');
        $('#input-search').val(data.search);
    }

    function previewData(data) {
        console.log(data);
        $('#input-search').val(data.search);
        var html = '';
        if (data.lists.length > 0) {
            $.each(data.lists, function (idx, siswa) {
                const kls = siswa.nama_kelas != null ? siswa.nama_kelas : '';
                html += `<tr>
                <td class="text-center align-middle">
                    <input type="hidden" name="ids[]" value="${siswa.id_siswa}">
                    ${Number((perPage * (currentPage - 1)) + (idx + 1))}</td>
            <td class="align-middle">${siswa.nis}</td>
            <td>
                <div class="d-flex align-items-center h-100">
                    <img class="avatar rounded-circle justify-content-center align-self-center shadow-sm" src="${siswa.foto}" width="40" height="40" alt="User Image">
                        <div class="ms-2 flex-grow-1 justify-content-center align-self-center fw-bold text-dark">${siswa.nama}</div>
                </div>
            </td>
            <td class="align-middle text-center"><span class="badge bg-secondary">${kls}</span></td>
            <td class="align-middle">${siswa.username}</td>
            <td class="align-middle text-break">${siswa.password}</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm ${siswa.reset == '0' ? 'btn-secondary' : 'btn-danger'} shadow-sm btn-reset" ${siswa.reset == '0' ? 'disabled' : ''} data-id="${siswa.id_siswa}" data-username="${siswa.username}" data-nama="${siswa.nama}" title="Reset Login"> 
                    <i class="bi bi-arrow-repeat text-white"></i>
                </button>
            </td>`;
                if (isAdmin === '1') {
                    html += `<td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-1 align-items-center">`;
                    if (siswa.active === "1") {
                        html += `<span class="badge bg-success">Aktif</span>
                                 <button type="button" class="btn btn-nonaktif btn-warning btn-sm shadow-sm" data-id="${siswa.id_siswa}" data-username="${siswa.username}" data-nama="${siswa.nama}" title="Nonaktifkan">
                                     <i class="bi bi-slash-circle text-white"></i>
                                 </button>`;
                    } else {
                        html += `<span class="badge bg-danger">Nonaktif</span>
                                 <button type="button" class="btn btn-aktif btn-success btn-sm shadow-sm" data-id="${siswa.id_siswa}" data-nama="${siswa.nama}" title="Aktifkan">
                                     <i class="bi bi-person-plus-fill text-white"></i>
                                 </button>`;
                    }
                    html += `</div></td>`;
                }
                html += `</tr>`;
            });

            const loaded = Number(perPage * (currentPage - 1)) + 1;
            const to = loaded + data.lists.length;
            $('#total-entri').html(`${loaded} ~ ${to - 1} dari ${data.total} entri`);
        } else {
            $('#total-entri').html(`0 ~ 0 dari 0 entri`);
            let colCount = isAdmin === '1' ? 8 : 7;
            html += `<tr><td colspan="${colCount}" class="text-center align-middle">Tidak ada data siswa</td><tr>`;
        }

        $('#table-body').html(html);
    }

    function applySearch() {
        query = $('#input-search').val();
        currentPage = 1;
        loadSiswa();
    }

    async function ajaxPost(url, formData) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    method: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (respon) {
                        resolve(respon);
                    },
                    error: function (xhr, status, error) {
                        $('#loading').addClass('d-none');
                        console.log(xhr);
                        Swal.fire({
                            title: "Gagal",
                            html: xhr.responseText,
                            icon: "error"
                        });
                        reject(error);
                    }
                });
            }, 500)
        });
    }
</script>
<?= $this->endSection() ?>