<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <div class="card-title"><?= $subjudul ?></div>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </button>
                        <button type="button" data-from="add" data-bs-toggle="modal" data-bs-target="#createTahunModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i><span
                                class="d-none d-sm-inline-block text-nowrap">Tambah Tahun Pelajaran</span>
                        </button>
                        <button type="button" class="btn btn-tool ms-1" data-card-widget="collapse" title="Collapse">
                            <i class="bi bi-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php $totalTp = count($tp); ?>
                    <div class="mb-4">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Pengaturan Tahun & Semester</h5>
                                <div class="text-muted">Kelola tahun pelajaran aktif beserta semesternya</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-primary px-3 py-2 text-sm">Total Tahun: <?= $totalTp ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Panduan Pengaturan</h5>
                        <ul class="ps-3 mb-0">
                            <li>Aktifkan tahun pelajaran yang sedang berjalan untuk memastikan data sinkron.</li>
                            <li>Gunakan tombol <span class="badge bg-primary">Tambah Tahun Pelajaran</span> untuk
                                menambah periode baru.</li>
                            <li>Pengaturan semester akan otomatis terkait dengan tahun pelajaran aktif.</li>
                            <li>Tahun atau semester yang sedang aktif tidak dapat dihapus untuk menjaga konsistensi
                                data.</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-4 table-responsive">
                            <label>Tahun Pelajaran</label>
                            <?= form_open('', array('id' => 'edittp')) ?>
                            <table id="tahun" class="table table-sm table-striped table-bordered table-hover dataTable">
                                <thead>
                                    <tr>
                                        <th class="d-none">id</th>
                                        <th height="50" class="text-center align-middle border-0">No.</th>
                                        <th class="text-center align-middle border-0">Tahun Pelajaran</th>
                                        <th class="text-center align-middle border-0">Status</th>
                                        <th class="text-center align-middle border-0">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tp as $key => $value): ?>
                                        <tr>
                                            <td class="d-none">
                                                <?= $value->id_tp ?>
                                            </td>
                                            <td class="text-center">
                                                <?= ($key + 1) ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?= $value->tahun ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php if ($value->active): ?>
                                                    <span class="badge bg-success shadow-sm px-3"><i
                                                            class="bi bi-check me-1"></i> AKTIF</span>
                                                <?php else: ?>
                                                    <button type="button" data-id="<?= $value->id_tp ?>"
                                                        class="btn btn-sm btn-primary btn-aktif w-100">AKTIFKAN
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" data-id="<?= $value->id_tp ?>"
                                                        data-tahun="<?= $value->tahun ?>" data-from="edit"
                                                        data-bs-toggle="modal" data-bs-target="#createTahunModal"
                                                        class="btn btn-sm btn-info text-white btn-edit shadow-sm <?= $value->active ? 'd-none' : '' ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" data-id="<?= $value->id_tp ?>"
                                                        class="btn btn-sm btn-danger btn-hapus shadow-sm <?= $value->active ? 'd-none' : '' ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?= form_close() ?>
                        </div>
                        <div class="col-md-5">
                            <label>Semester</label>
                            <?= form_open('', array('id' => 'editsmt')) ?>
                            <table id="semester"
                                class="table table-sm table-striped table-bordered table-hover dataTable">
                                <thead>
                                    <tr>
                                        <th class="d-none">id</th>
                                        <th height="50" class="text-center align-middle border-0">No.</th>
                                        <th class="text-center align-middle border-0">Semester</th>
                                        <th class="text-center align-middle border-0">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($smt as $key => $value): ?>
                                        <tr>
                                            <td class="d-none">
                                                <?= $value->id_smt ?>
                                            </td>
                                            <td class="text-center">
                                                <?= ($key + 1) ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $value->smt ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php if ($value->active): ?>
                                                    <span class="badge bg-success shadow-sm px-3"><i
                                                            class="bi bi-check me-1"></i> AKTIF</span>
                                                <?php else: ?>
                                                    <button type="button" data-id="<?= $value->id_smt ?>"
                                                        class="btn btn-sm btn-warning btn-aktif w-100">AKTIFKAN
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <?= form_open('', array('id' => 'create')) ?>
    <div class="modal fade" id="createTahunModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Tahun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-2 col-form-label">Tahun*</label>
                        <div class="col-md-10">
                            <input type="text" id="createtahun" name="tahun" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdTahun" class="form-control">
                    <input type="hidden" id="method" name="method" class="form-control">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="submit-tp">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <script type="text/javascript"
        src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson-cell.js"></script>
    <script type="text/javascript"
        src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson-row.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson.js"></script>
    <script>
        $(document).ready(function () {
            $("#tahun").on("click", ".btn-aktif", function () {
                let id = $(this).data("id");
                var dataTahun = JSON.stringify($('#tahun').tableToJSON());
                var replaced = dataTahun.replace(/Tahun Pelajaran/g, "tp");

                //console.log($('#edittp').serialize() + "&active=" + id + "&tahun=" + replaced);
                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
                            }
                });
                $.ajax({
                    url: base_url + "datatahun/gantitahun",
                    data: $('#edittp').serialize() + "&active=" + id + "&tahun=" + replaced,
                    type: "POST",
                    success: function (response) {
                        var title = response.status ? "Berhasil" : "Gagal";
                        var type = response.status ? "success" : "error";

                        Swal.fire({
                            title: title,
                            text: response.msg,
                            icon: type
                        }).then((result) => {
                            if (result.value) {
                                if (response.status) {
                                    window.location.href = base_url + 'datatahun';
                                }
                            }
                        });
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        Swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            });

            $('#createTahunModal').on('show.bs.modal', function (e) {
                var method = $(e.relatedTarget).data('from');
                $(e.currentTarget).find('input[id="method"]').val(method);

                if (method === 'edit') {
                    $('#createModalLabel').text('Edit Tahun');
                    var id = $(e.relatedTarget).data('id');
                    var tahun = $(e.relatedTarget).data('tahun');

                    $(e.currentTarget).find('input[id="editIdTahun"]').val(id);
                    $(e.currentTarget).find('input[id="createtahun"]').val(tahun);

                    var attrId = document.getElementById("editIdTahun");
                    attrId.setAttribute("name", "id_tahun");
                } else {
                    $('#createModalLabel').text('Tambah Tahun');
                    $(e.currentTarget).find('input[id="editIdTahun"]').val('');
                    $(e.currentTarget).find('input[id="createtahun"]').val('');
                }
            });

            $("#tahun").on("click", ".btn-hapus", function () {
                let id = $(this).data("id");
                Swal.fire({
                    title: 'Hapus Tahun',
                    text: 'Anda yakin akan menghapus Tahun Pelajaran? tindakan ini akan membuat data yang berhubungan tidak aktif',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Hapus"
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: base_url + "datatahun/hapustahun",
                            data: $('#edittp').serialize() + "&hapus=" + id,
                            type: "POST",
                            success: function (response) {
                                var title = response.status ? "Berhasil" : "Gagal";
                                var type = response.status ? "success" : "error";

                                Swal.fire({
                                    title: title,
                                    text: response.msg,
                                    icon: type
                                }).then((result) => {
                                    if (result.value) {
                                        if (response.status) {
                                            window.location.href = base_url + 'datatahun';
                                        }
                                    }
                                });
                            }, error: function (xhr, status, error) {
                                console.log("error", xhr.responseText);
                                const err = JSON.parse(xhr.responseText)
                                Swal.fire({
                                    title: "Error",
                                    text: err.Message,
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });

            $("#semester").on("click", ".btn-aktif", function () {
                let id = $(this).data("id");
                var dataSmt = JSON.stringify($('#semester').tableToJSON());
                //console.log($('#edittp').serialize() + "&active=" + id + "&tahun=" + replaced);
                $.ajax({
                    url: base_url + "datatahun/gantisemester",
                    data: $('#edittp').serialize() + "&active=" + id + "&semester=" + dataSmt,
                    type: "POST",
                    success: function (response) {
                        var title = response.status ? "Berhasil" : "Gagal";
                        var type = response.status ? "success" : "error";

                        Swal.fire({
                            title: title,
                            text: response.msg,
                            icon: type
                        }).then((result) => {
                            if (result.value) {
                                if (response.status) {
                                    window.location.href = base_url + 'datatahun';
                                }
                            }
                        });
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        Swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            });

            $('#create').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var form = new FormData($('#create')[0])
                console.log("data:", Object.fromEntries(form));

                $.ajax({
                    url: base_url + "datatahun/add",
                    type: "POST",
                    data: form,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function (data) {
                        location.href = base_url + 'datatahun';
                    }, error: function (xhr, status, error) {
                        $('#createTahunModal').modal('hide').data('bs.modal', null);
                        $('#createTahunModal').on('hidden', function () {
                            $(this).data('modal', null);  // destroys modal
                        });
                        showDangerToast('Gagal menambah tahun pelajaran');
                    }
                });
                return false;
            });

            /*
            $('#hariefektif').submit('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                e.stopImmediatePropagation();
                console.log("data:", $(this).serialize());
    
                $.ajax({
                    url: base_url + "datatahun/savehariefektif",
                    type: "POST",
                    dataType: "JSON",
                    data: $(this).serialize(),
                    success: function (data) {
                        if (data.status) {
                            Swal.fire({
                                title: "Sukses",
                                text: "Jumlah Hari Efektif berhasi disimpan",
                                icon: "success"
                            }).then((result) => {
                                if (result.value) {
                                    if (data.status) {
                                        window.location.href = base_url + 'datatahun';
                                    }
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                text: 'Gagal menyimpan jumlah hari efektif',
                                icon: "error",
                            });
                        }
                    }, error: function (xhr, status, error) {
                        const err = JSON.parse(xhr.responseText)
                        Swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            });
             */
        })
    </script>