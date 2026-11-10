<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
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
        <div class="card card-default my-shadow mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-door-open me-2"></i><?= $subjudul ?></h6>
                <div class="card-tools ms-auto">
                    <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm ms-1 btn-sync-dapodik" data-entitas="ruang">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> Sync Dapodik
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createRuangModal"
                        class="btn btn-sm btn-primary shadow-sm ms-1"><i class="bi bi-plus me-1"></i> Tambah Ruang
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="bulk">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table id="ruang" class="w-100 table table-striped table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th width="40" class="align-middle text-center p-0">
                                        <input class="select_all" type="checkbox">
                                    </th>
                                    <th width="40" class="align-middle text-center p-0">No.</th>
                                    <th>Ruang Ujian</th>
                                    <th>Kode Ruang</th>
                                    <th class="text-center">Total Sesi</th>
                                    <th class="align-middle text-center" style="width: 100px">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </form>
                <div class="mt-3">
                    <button id="hapusterpilih" onclick="bulk_delete()" type="button" class="btn btn-danger" disabled>
                        <i class="bi bi-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createRuangModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createModalLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Ruang
                        Ujian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label for="nama_ruang" class="col-md-4 col-form-label">Nama Ruang*</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="nama_ruang" placeholder="Contoh: Ruang 1"
                                required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="kode_ruang" class="col-md-4 col-form-label">Kode Ruang*</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="kode_ruang" placeholder="Contoh: R1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="reset" class="btn btn-warning shadow-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editRuangModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="updateForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil me-2"></i>Edit Ruang Ujian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editIdRuang" name="id_ruang">
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Nama Ruang*</label>
                        <div class="col-md-8">
                            <input type="text" id="namaEdit" name="nama_ruang" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Kode Ruang*</label>
                        <div class="col-md-8">
                            <input type="text" id="kodeEdit" name="kode_ruang" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var table;

    $(document).ready(function () {
        ajaxcsrf();

        table = $("#ruang").DataTable({
            initComplete: function () {
                var api = this.api();
                $("#ruang_filter input").off(".DT").on("keyup.DT", function (e) {
                    api.search(this.value).draw();
                });
            },
            oLanguage: { sProcessing: "loading..." },
            processing: true,
            serverSide: false,
            ajax: { url: base_url + "cbtruang/data", type: "POST" },
            columns: [
                {
                    data: "id_ruang",
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return `<div class="text-center"><input name="checked[]" class="check" value="${data}" type="checkbox"></div>`;
                    }
                },
                { data: "no", className: "text-center" },
                { data: "nama_ruang" },
                { data: "kode_ruang" },
                {
                    data: "jum_sesi",
                    className: "text-center",
                    render: function (data) {
                        return `<span class="badge bg-info">${data} sesi</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return `<div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-warning btn-sm shadow-sm btn-edit" 
                                data-id="${data.id_ruang}" data-nama="${data.nama_ruang}" data-kode="${data.kode_ruang}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>`;
                    }
                }
            ],
            order: [[2, "asc"]]
        });

        $(".select_all").on("click", function () {
            $(".check").prop("checked", this.checked);
            updateHapusBtn();
        });

        $("#ruang tbody").on("click", ".check", function () {
            var total = $(".check").length;
            var checked = $(".check:checked").length;
            $(".select_all").prop("checked", total === checked);
            updateHapusBtn();
        });

        function updateHapusBtn() {
            $('#hapusterpilih').prop('disabled', $(".check:checked").length === 0);
        }

        // Create
        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "cbtruang/add",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (res) {
                    if (res.status) {
                        $('#createRuangModal').modal('hide');
                        $('#createForm')[0].reset();
                        showSuccessToast(res.message);
                        table.ajax.reload();
                    } else {
                        showDangerToast(res.message);
                    }
                },
                error: function (xhr) {
                    showDangerToast("Error: " + xhr.responseText);
                }
            });
        });

        // Edit button click
        $('#ruang').on('click', '.btn-edit', function () {
            var data = $(this).data();
            $('#editIdRuang').val(data.id);
            $('#namaEdit').val(data.nama);
            $('#kodeEdit').val(data.kode);
            $('#editRuangModal').modal('show');
        });

        // Update
        $('#updateForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "cbtruang/update",
                data: $(this).serialize(),
                method: 'POST',
                dataType: "JSON",
                success: function (res) {
                    if (res.status) {
                        $('#editRuangModal').modal('hide');
                        showSuccessToast(res.message);
                        table.ajax.reload();
                    } else {
                        showDangerToast(res.message);
                    }
                },
                error: function (xhr) {
                    showDangerToast("Error: " + xhr.responseText);
                }
            });
        });

        // Bulk delete form
        $("#bulk").on("submit", function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "cbtruang/delete",
                data: $(this).serialize(),
                type: "POST",
                success: function (res) {
                    if (res.status) {
                        Swal.fire("Berhasil", res.total + " data berhasil dihapus", "success");
                    } else {
                        Swal.fire("Gagal", res.message || "Tidak ada data yang dipilih", "error");
                    }
                    reload_ajax();
                },
                error: function () {
                    Swal.fire("Gagal", "Ada data yang sedang digunakan", "error");
                }
            });
        });
    });

    function bulk_delete() {
        if ($(".check:checked").length == 0) {
            Swal.fire("Gagal", "Tidak ada data yang dipilih", "error");
        } else {
            Swal.fire({
                title: "Anda yakin?",
                text: "Data akan dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(result => {
                if (result.isConfirmed) {
                    $("#bulk").submit();
                }
            });
        }
    }

    function reload_ajax() {
        table.ajax.reload();
        $(".select_all").prop("checked", false);
        $('#hapusterpilih').prop('disabled', true);
    }
</script>
<?= $this->endSection() ?>