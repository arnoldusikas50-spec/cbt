<div class="content-wrapper bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools">
                        <button type="button" data-toggle="modal" data-target="#createSiswaModal"
                                class="btn btn-sm btn-primary"><i
                                    class="fas fa-plus"></i><span
                                    class="d-none d-sm-inline-block ml-1">Tambah Siswa</span>
                        </button>
                        <a href="<?= base_url('datasiswa/add') ?>" class="btn btn-sm bg-gradient-success"><i
                                    class="fas fa-upload"></i><span class="d-none d-sm-inline-block ml-1">Import</span></a>
                        <a href="<?= base_url('datasiswa/update') ?>" class="btn btn-sm bg-gradient-success"><i
                                    class="fas fa-database"></i><span
                                    class="d-none d-sm-inline-block ml-1">Update Data</span></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Manajemen Data Siswa</h5>
                                <div class="text-muted">Kelola data siswa, import massal, dan aksi bulk</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row align-items-sm-center">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Siswa: <span id="badge-total-siswa">-</span></span>
                                <span class="badge badge-info px-3 py-2 text-sm">Tahun Aktif: <?= $tp_active->tahun ?? '-' ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengelolaan</h5>
                        <ul class="pl-3 mb-0">
                            <li>Gunakan tombol <span class="badge badge-primary">Tambah Siswa</span> atau <span class="badge badge-success">Import</span> untuk menambah data.</li>
                            <li>Aksi bulk (Pindah, Keluar, Hapus) tersedia setelah mencentang data siswa.</li>
                            <li>Filter berdasarkan status atau kelas dan manfaatkan pencarian untuk mempercepat penelusuran.</li>
                        </ul>
                    </div>

                    <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-6 d-flex flex-wrap align-items-center mb-2 mb-lg-0">
                                <div class="d-flex align-items-center mr-3">
                                    <label class="mb-0 mr-2">Tampilkan</label>
                                    <select id="users_length" aria-controls="users" class="custom-select custom-select-sm form-control form-control-sm">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="dropdown">
                                    <button id="dropdown-btn" class="btn btn-sm btn-danger dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false" disabled="disabled">
                                        Aksi Terpilih
                                    </button>
                                    <div id="dropdown-action" class="dropdown-menu">
                                        <a class="dropdown-item" id="pindah" href="#">Set sebagai PINDAH</a>
                                        <a class="dropdown-item" id="keluar" href="#">Set sebagai KELUAR</a>
                                        <a class="dropdown-item" id="hapus" href="#">HAPUS</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 d-flex flex-wrap justify-content-lg-end align-items-center">
                                <div class="d-flex align-items-center mr-3 mb-2 mb-lg-0">
                                    <span>Filter</span>
                                    <select id="users-filter" class="ml-2 form-control form-control-sm">
                                        <option value="0">Semua</option>
                                        <option value="1">Aktif</option>
                                        <option value="2">Nonaktif</option>
                                        <option value="3">Kelas</option>
                                    </select>
                                    <div id="kelas-filter-container" class="d-none ml-2">
                                        <?php
                                        echo form_dropdown('filter-kelas', $kelass, null, 'id="kelas-filter" class="form-control form-control-sm"'); ?>
                                    </div>
                                </div>
                                <div class="dataTables_filter d-flex align-items-center">
                                    <button id="btn-clear" type="button" class="btn btn-sm btn-light mr-1" data-toggle="tooltip" title="Hapus pencarian" disabled="disabled">
                                        <i class="fa fa-times"></i>
                                    </button>
                                    <input id="input-search" type="search" class="form-control form-control-sm mr-1" placeholder="Cari siswa" aria-controls="users">
                                    <button id="btn-search" type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="Cari" onclick="applySearch()" disabled="disabled">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <?= form_open('datasiswa/delete', array('id' => 'bulk')); ?>
                                <div class="table-responsive">
                                    <table id="table-siswa" class="w-100 table table-md table-striped table-bordered table-hover">
                                        <thead class="alert alert-default-primary">
                                        <tr>
                                            <th height="50" class="align-middle text-center p-0">
                                                <input class="select_all" type="checkbox">
                                            </th>
                                            <th class="align-middle text-center p-0">No.</th>
                                            <th class="align-middle">NAMA & KELAS</th>
                                            <th class="align-middle">NIS & NISN</th>
                                            <th class="align-middle text-center p-0">Aksi</th>
                                        </tr>
                                        </thead>
                                        <tbody id="table-body">
                                        </tbody>
                                    </table>
                                </div>
                                <?= form_close() ?>
                            </div>
                            <div class="col-12 d-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
                                <div class="mb-2 mb-md-0" id="total-entri"></div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination mb-0" id="pagination"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overlay" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= form_open('', array('id' => 'formsiswa')); ?>
<div class="modal fade" id="createSiswaModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Tambah Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="nama_siswa">Nama Siswa :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input id="nama_siswa" type="text" class="form-control" name="nama_siswa"
                                   placeholder="Nama Siswa" required>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="nis">NIS :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            </div>
                            <input type="number" id="nis" class="form-control" name="nis" placeholder="NIS" required>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="nisn">NISN :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            </div>
                            <input type="number" id="nisn" class="form-control" name="nisn" placeholder="NISN" required>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="jenis_kelamin" class="control-label">Jenis Kelamin :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                            </div>
                            <select class="form-control" id="jenis_kelamin" data-placeholder="Jenis Kelamin"
                                    name="jenis_kelamin">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="jenis_kelamin" class="control-label">Agama :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-user"></i></span>
                            </div>
                            <?php
                            $arrAgama = ["Islam","Katolik", "Protestan", "Hindu", "Budha", "Konghucu"];
                            ?>
                            <select class="form-control" id="agama" data-placeholder="Pilih Agama yang dianut" name="agama">
                                <option value="Pilih Agama yang dianut">Pilih Agama yang dianut</option>
                                <?php foreach ($arrAgama as $agama) : ?>
                                    <option value="<?= $agama ?>"><?= $agama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="kelas_awal">Kelas Awal :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                            </div>
                            <?php
                            if ($setting->jenjang == 1) {
                                $opsis ['1'] = '1';
                                $opsis ['2'] = '2';
                                $opsis ['3'] = '3';
                                $opsis ['4'] = '4';
                                $opsis ['5'] = '5';
                                $opsis ['6'] = '6';
                            } elseif ($setting->jenjang == 2) {
                                $opsis ['7'] = '7';
                                $opsis ['8'] = '8';
                                $opsis ['9'] = '9';
                            } else {
                                $opsis ['10'] = '10';
                                $opsis ['11'] = '11';
                                $opsis ['12'] = '12';
                            };
                            ?>
                            <select class="form-control" id="kelas_awal" data-placeholder="Pilih Kelas"
                                    name="kelas_awal">
                                <option value="">Pilih Kelas Awal</option>
                                <?php foreach ($opsis as $kelas) : ?>
                                    <option value="<?= $kelas ?>"><?= $kelas ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="tahun_masuk">Tanggal Diterima :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
								<span class="input-group-text">
									<i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" name="tahun_masuk" id="tahunmasuk" class="form-control"
                                   autocomplete="off" placeholder="Tgl/Tahun Masuk" required>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="username">Username :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input id="username" type="text" class="form-control" name="username" placeholder="Username"
                                   required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-offset-4">
                        <label for="password">Password :</label>
                    </div>
                    <div class="col-md-8 col-sm-offset-8">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            </div>
                            <input id="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
                <button type="reset" class="btn bg-warning text-white">
                    <i class="fa fa-sync mr-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= form_open('', array('id' => 'pager')); ?>
<input type="hidden" id="pager-page" name="page" value="1">
<input type="hidden" id="pager-limit" name="limit" value="10">
<?= form_close() ?>

<script src="<?=base_url()?>/assets/app/js/jquery.twbsPagination.js" type="text/javascript"></script>
<script>
    let currentPage = 1;
    let perPage = 10;
    let $pagination, defaultOpts, query, actionBulk;

    $(document).ready(function () {
        ajaxcsrf();
        $pagination = $('#pagination');
        defaultOpts = {
            visiblePages: 5,
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

        $('#users-filter').change(function () {
            if ($(this).val() === "3") {
                $('#kelas-filter-container').removeClass('d-none');
            } else {
                $('#kelas-filter-container').addClass('d-none');
                currentPage = 1;
                loadSiswa();
            }
        });

        $('#kelas-filter').prepend('<option value="" selected>Kelas</option>');
        $('#kelas-filter').change(function () {
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

        $(".select_all").on("click", function () {
            if (this.checked) {
                $(".check").each(function () {
                    this.checked = true;
                    $(".select_all").prop("checked", true);
                    $('#hapusterpilih').removeAttr('disabled');
                    $('#dropdown-btn').removeAttr('disabled');
                });
            } else {
                $(".check").each(function () {
                    this.checked = false;
                    $(".select_all").prop("checked", false);
                    $('#hapusterpilih').attr('disabled', 'disabled');
                    $('#dropdown-btn').attr('disabled', 'disabled');
                });
            }
        });

        $("#table-siswa tbody").on("click", "tr .check", function () {
            var check = $("#table-siswa tbody tr .check").length;
            var checked = $("#table-siswa tbody tr .check:checked").length;
            if (check === checked) {
                $(".select_all").prop("checked", true);
            } else {
                $(".select_all").prop("checked", false);
            }

            if (checked === 0) {
                $('#hapusterpilih').attr('disabled', 'disabled');
                $('#dropdown-btn').attr('disabled', 'disabled');
            } else {
                $('#hapusterpilih').removeAttr('disabled');
                $('#dropdown-btn').removeAttr('disabled');
            }
        });

        $("#bulk").on("submit", function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            //console.log($(this).serialize() + '&aksi=' +actionBulk)
            $.ajax({
                url: $(this).attr("action"),
                data: $(this).serialize() + '&aksi=' +actionBulk,
                type: "POST",
                success: function (respon) {
                    console.log('res', respon)
                    if (respon.status) {
                        $(".select_all").prop("checked", false);
                        $('#hapusterpilih').attr('disabled', 'disabled');
                        $('#dropdown-btn').attr('disabled', 'disabled');
                        swal.fire({
                            title: "Berhasil",
                            text: respon.total + " " + respon.message,
                            icon: "success"
                        });
                        loadSiswa();
                    } else {
                        swal.fire({
                            title: "Gagal",
                            html: respon.message,
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    swal.fire({
                        title: "Gagal",
                        text: "Ada data yang sedang digunakan",
                        icon: "error"
                    });
                }
            });
        });

        $('#tahunmasuk').datetimepicker({
            icons:
                {
                    next: 'fa fa-angle-right',
                    previous: 'fa fa-angle-left'
                },
            timepicker: false,
            format: 'Y-m-d',
            //disabledWeekDays: [0],
            widgetPositioning: {
                horizontal: 'left',
                vertical: 'bottom'
            }
        });

        $('#formsiswa').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            console.log($(this).serialize());
            $.ajax({
                url: base_url + "datasiswa/create",
                data: $(this).serialize(),
                dataType: "JSON",
                type: 'POST',
                success: function (response) {
                    console.log("result", response);
                    $('#createSiswaModal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });
                    $('#createSiswaModal').modal('hide').data('bs.modal', null);

                    if (response.insert) {
                        showSuccessToast(response.text);
                        loadSiswa();
                    } else {
                        const err = [];
                        for (const error of data.errors) {
                            console.log(error);
                            /*
                            if (error.nisn !== "") arrNisnDup.push("<br />" + idx + ": " +o.nisn);
                            if (error.nis !== "") arrNisDup.push("<br />" + idx + ": " +o.nis);
                            if (o.username !== "") arrUserDup.push("<br />" + idx + ": " +o.username);
                             */
                        }
                        showDangerToast(response.text);
                    }
                },
                error: function (xhr, status, error) {
                    $('#createSiswaModal').modal('hide').data('bs.modal', null);
                    $('#createSiswaModal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });
                    showDangerToast("Gagal disimpan");
                    console.log(xhr.responseText);
                }
            })
        });

        $("#dropdown-action a ").click(function () {
            let x = $(this).attr('id');
            //alert(x);
            actionBulk = x;
            if (x === "pindah") {
                bulk_pindah()
            } else if (x === "keluar") {
                bulk_keluar()
            } else if (x === "hapus") {
                bulk_delete()
            }
        });

        loadSiswa();
    });

    function loadSiswa() {
        $(".select_all").prop("checked", false);
        $('#pager-page').val(currentPage);
        $('#loading').removeClass('d-none');
        var cari = query != null ? '&search=' + query : ''
        var filter = '&filter=' + $('#users-filter').val();
        var kelas = '&kelas=' + $('#kelas-filter').val();
        var dataPost = $('#pager').serialize() + cari + filter + kelas;
        console.log('post', dataPost);
        $.ajax({
            url: base_url + 'datasiswa/list',
            data: dataPost,
            type: 'POST',
            success: function (data) {
                $('#input-search').val(data.search);
                if (data.pages > 0) {
                    $pagination.removeClass('d-none');
                    $pagination.twbsPagination('destroy');
                    $pagination.twbsPagination($.extend({}, defaultOpts, {
                        startPage: currentPage,
                        totalPages: data.pages,
                    }));
                } else {
                    $pagination.addClass('d-none');
                }
                console.log(data);
                previewData(data);
            }, error: function (xhr, status, error) {
                $('#loading').addClass('d-none')
                console.log("error", xhr.responseText);
                swal.fire({
                    title: "ERROR",
                    text: "Ada kesalahan",
                    icon: "error"
                });
            }
        });
    }

    async function previewData(data) {
        $('#input-search').val(data?.search ?? '');
        let html = '';

        if (Array.isArray(data?.lists) && data.lists.length > 0) {
            //data.lists.sort((a, b) => (a?.id_kelas ?? 0) - (b?.id_kelas ?? 0));
            //data.lists.sort((a, b) => a?.nama?.localeCompare(b?.nama ?? '', undefined, { numeric: true }));

            for (let idx = 0; idx < data.lists.length; idx++) {
                const siswa = data.lists[idx];
                const namaKelas = siswa?.nama_kelas ?? '';
                const kls = namaKelas !== '' ? '<span class="badge badge-pill badge-info">' + namaKelas + '</span>' : '';
                const statusSiswa = {
                    '1': {status: 'Aktif', color: 'badge-success'},
                    '2': {status: 'Lulus', color: 'badge-primary'},
                    '3': {status: 'Pindah', color: 'badge-warning'},
                    '4': {status: 'Keluar', color: 'badge-danger'}
                }
                const status = siswa?.active == "0" && siswa?.status == "1"
                    ? '<span class="badge badge-pill badge-secondary">Nonaktif</span>'
                    : `<span class="badge badge-pill ${statusSiswa[siswa.status]?.color}">${statusSiswa[siswa.status]?.status}</span>`;
                const agama = siswa?.agama ? `<span class="badge badge-pill badge-info ml-1">${siswa.agama}</span>` : '';
                const gender = siswa?.jenis_kelamin ?? '-';

                const fotoUrl = siswa?.foto

                html += '<tr>' +
                    '   <td class="text-center align-middle">' +
                    '       <input name="checked[]" class="check" value="' + (siswa?.id_siswa ?? '') + '" type="checkbox">' +
                    '   </td>' +
                    '   <td class="text-center align-middle">' + Number((perPage * (currentPage - 1)) + (idx + 1)) + '</td>' +
                    '   <td>' +
                    '       <div class="media d-flex h-100">' +
                    '           <img class="avatar img-circle justify-content-center align-self-center"' +
                    '                src="' + fotoUrl + '" width="50" height="50" alt="User Image"> ' +
                    '           <div class="media-body ml-2 justify-content-center align-self-center">' +
                    (siswa?.nama ?? '') + '<br>' + kls + agama +
                    '               <span class="badge badge-info mr-1">' + gender + '</span>' + status +
                    '           </div>' +
                    '       </div>' +
                    '   </td>' +
                    '   <td class=" align-middle">' +
                    '       <span class="badge badge-light">' + (siswa?.nis ?? '-') + '</span><br>' +
                    '       <span class="badge badge-light">' + (siswa?.nisn ?? '-') + '</span>' +
                    '   </td>' +
                    '   <td class=" align-middle">' +
                    '       <div class="text-center">' +
                    '           <a class="btn btn-xs btn-warning" href="' + base_url + 'datasiswa/edit/' + (siswa?.id_siswa ?? '') + '">' +
                    '               <i class="fa fa-pencil-alt"></i> Edit' +
                    '           </a>' +
                    '       </div>' +
                    '   </td>' +
                    '</tr>';
            }

            const loaded = Number(perPage * (currentPage - 1)) +1;
            const to = loaded + data.lists.length;
            $('#total-entri').html(`${loaded} ~ ${to-1} dari ${data.total} entri`);
        } else {
            $('#total-entri').html(`0 ~ 0 dari 0 entri`);
            html += '<tr><td colspan="5" class="text-center align-middle">Tidak ada data siswa</td><tr>';
        }

        $('#table-body').html(html);

        $('#loading').addClass('d-none');
    }

    function cekGambar(url) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(url);
            img.onerror = () => resolve(base_url + 'assets/img/siswa.png');
            img.src = url;
        });
    }

    function applySearch() {
        query = $('#input-search').val();
        currentPage = 1;
        loadSiswa();
    }

    function bulk_delete() {
        if ($("#table-siswa tbody tr .check:checked").length == 0) {
            swal.fire({
                title: "Gagal",
                text: "Tidak ada data yang dipilih",
                icon: "error"
            });
        } else {
            swal.fire({
                title: "Anda yakin?",
                text: "Data terpilih akan dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(result => {
                if (result.value) {
                    $("#bulk").submit();
                }
            });
        }
    }

    function bulk_pindah() {
        if ($("#table-siswa tbody tr .check:checked").length == 0) {
            swal.fire({
                title: "Gagal",
                text: "Tidak ada data yang dipilih",
                icon: "error"
            });
        } else {
            swal.fire({
                title: "Anda yakin?",
                text: "Data terpilih akan diset sebagai siswa PINDAH",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "YA!"
            }).then(result => {
                if (result.value) {
                    $("#bulk").submit();
                }
            });
        }
    }

    function bulk_keluar() {
        if ($("#table-siswa tbody tr .check:checked").length == 0) {
            swal.fire({
                title: "Gagal",
                text: "Tidak ada data yang dipilih",
                icon: "error"
            });
        } else {
            swal.fire({
                title: "Anda yakin?",
                text: "Data terpilih akan diset sebagai siswa KELUAR",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "YA!"
            }).then(result => {
                if (result.value) {
                    $("#bulk").submit();
                }
            });
        }
    }

</script>
