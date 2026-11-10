<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/jquery-datetimepicker/jquery.datetimepicker.min.css') ?>">
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clock text-primary me-2"></i>
                    <?= esc($judul ?? 'Sesi Ujian') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Sesi Ujian') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>


<!-- Main content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary"><i class="bi bi-clock-history me-2"></i>Daftar Sesi Ujian</h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-1">
                    <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createSesiModal"
                        class="btn btn-sm btn-primary shadow-sm">
                        <i class="bi bi-plus me-1"></i> Tambah Sesi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="bulk">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table id="sesi" class="w-100 table table-striped table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th width="40" class="text-center">
                                        <input class="form-check-input select_all" type="checkbox">
                                    </th>
                                    <th width="50" class="text-center">No.</th>
                                    <th>Nama Sesi</th>
                                    <th width="100">Kode</th>
                                    <th width="200">Waktu</th>
                                    <th width="120" class="text-center">Istirahat</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light">
                <button id="hapusterpilih" onclick="bulk_delete()" type="button" class="btn btn-sm btn-danger shadow-sm" disabled>
                    <i class="bi bi-trash-fill me-1"></i> Hapus Terpilih
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createSesiModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createModalLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Sesi Ujian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_sesi" class="form-label fw-bold">Nama Sesi <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_sesi" name="nama_sesi"
                            placeholder="Contoh: Sesi 1" required>
                    </div>
                    <div class="mb-3">
                        <label for="kode_sesi" class="form-label fw-bold">Kode Sesi <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode_sesi" name="kode_sesi" placeholder="Contoh: S1"
                            required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="waktu_mulai" class="form-label fw-bold">Jam Mulai <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="waktu_mulai" name="waktu_mulai" class="form-control jam-picker"
                                placeholder="07:30" required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="waktu_akhir" class="form-label fw-bold">Jam Berakhir <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="waktu_akhir" name="waktu_akhir" class="form-control jam-picker"
                                placeholder="09:00" required autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="istirahat" class="form-label fw-bold">Istirahat (Menit)</label>
                        <input type="number" id="istirahat" name="istirahat" class="form-control"
                            placeholder="Contoh: 15" min="0">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Fitur istirahat hanya sebagai informasi untuk cetak Daftar Hadir dan Berita Acara.
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
<div class="modal fade" id="editSesiModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Sesi Ujian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="editIdSesi" name="id_sesi">
                    <div class="mb-3">
                        <label for="namaEdit" class="form-label fw-bold">Nama Sesi <span
                                class="text-danger">*</span></label>
                        <input type="text" id="namaEdit" name="nama_sesi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="kodeEdit" class="form-label fw-bold">Kode Sesi <span
                                class="text-danger">*</span></label>
                        <input type="text" id="kodeEdit" name="kode_sesi" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jamAwalEdit" class="form-label fw-bold">Jam Mulai <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="jamAwalEdit" name="waktu_mulai" class="form-control jam-picker"
                                required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jamAkhirEdit" class="form-label fw-bold">Jam Berakhir <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="jamAkhirEdit" name="waktu_akhir" class="form-control jam-picker"
                                required autocomplete="off">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="istirahatEdit" class="form-label fw-bold">Istirahat (Menit)</label>
                        <input type="number" id="istirahatEdit" name="istirahat" class="form-control" min="0">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Fitur istirahat hanya sebagai informasi untuk cetak Daftar Hadir dan Berita Acara.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class="bi bi-save me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/jquery-datetimepicker/jquery.datetimepicker.full.min.js') ?>"></script>
<script>
    var table;

    $(document).ready(function () {
        ajaxcsrf();

        // Init Timepicker 24 Jam
        $('.jam-picker').datetimepicker({
            datepicker: false,
            format: 'H:i',
            step: 15,
            formatTime: 'H:i',
            scrollInput: false
        });

        table = $("#sesi").DataTable({
            initComplete: function () {
                var api = this.api();
                $("#sesi_filter input").off(".DT").on("keyup.DT", function (e) {
                    api.search(this.value).draw();
                });
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari sesi...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Belum ada data sesi"
            },
            processing: false,
            serverSide: false,
            ajax: {
                url: base_url + "cbtsesi/data",
                type: "POST"
            },
            columns: [
                {
                    data: "id_sesi",
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    render: function (data) {
                        return `<input name="checked[]" class="form-check-input check" value="${data}" type="checkbox">`;
                    }
                },
                {
                    data: "no",
                    className: "text-center"
                },
                {
                    data: "nama_sesi",
                    render: function (data) {
                        return `<strong>${data}</strong>`;
                    }
                },
                {
                    data: "kode_sesi",
                    render: function (data) {
                        return `<span class="badge bg-secondary">${data}</span>`;
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        return `<span class="badge bg-success"><i class="bi bi-clock me-1"></i>${data.waktu_mulai}</span> 
                                <i class="bi bi-arrow-right mx-1"></i> 
                                <span class="badge bg-danger"><i class="bi bi-clock me-1"></i>${data.waktu_akhir}</span>`;
                    }
                },
                {
                    data: "istirahat",
                    className: "text-center",
                    render: function (data) {
                        return data ? `<span class="badge bg-info">${data} Menit</span>` :
                            '<span class="text-muted fst-italic">-</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    render: function (data) {
                        return `<div class="btn-action-group">
                            <button type="button" class="btn btn-action btn-action-edit btn-edit" 
                                data-id="${data.id_sesi}" 
                                data-nama="${data.nama_sesi}" 
                                data-kode="${data.kode_sesi}"
                                data-awal="${data.waktu_mulai}" 
                                data-akhir="${data.waktu_akhir}" 
                                data-istirahat="${data.istirahat ?? ''}" 
                                title="Edit Sesi">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-action-delete btn-delete" 
                                data-id="${data.id_sesi}" 
                                data-nama="${data.nama_sesi}" 
                                title="Hapus Sesi">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>`;
                    }
                }
            ],
            order: [[2, "asc"]],
            pageLength: 25,
            responsive: true
        });

        // Select all checkbox
        $(".select_all").on("click", function () {
            $(".check").prop("checked", this.checked);
            updateHapusBtn();
        });

        // Individual checkbox
        $("#sesi tbody").on("click", ".check", function () {
            var total = $(".check").length;
            var checked = $(".check:checked").length;
            $(".select_all").prop("checked", total === checked);
            updateHapusBtn();
        });

        function updateHapusBtn() {
            $('#hapusterpilih').prop('disabled', $(".check:checked").length === 0);
        }

        // Delete individual
        $('#sesi').on('click', '.btn-delete', function () {
            var id = $(this).data('id');
            var nama = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Sesi?',
                html: `Anda yakin ingin menghapus sesi <strong>${nama}</strong>?<br><small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: base_url + "cbtsesi/delete",
                        data: 'id_sesi=' + id + '&<?= csrf_token() ?>=' + '<?= csrf_hash() ?>',
                        type: 'POST',
                        dataType: "JSON",
                        success: function (res) {
                            if (res.status) {
                                showSuccessToast(res.message || 'Sesi berhasil dihapus');
                                table.ajax.reload(null, false);
                            } else {
                                showDangerToast(res.message || 'Gagal menghapus sesi');
                            }
                        },
                        error: function (xhr) {
                            showDangerToast("Error: Data sedang digunakan atau terjadi kesalahan");
                        }
                    });
                }
            });
        });

        // Create
        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "cbtsesi/add",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (res) {
                    if (res.status) {
                        $('#createSesiModal').modal('hide');
                        $('#createForm')[0].reset();
                        showSuccessToast(res.message || 'Sesi berhasil ditambahkan');
                        table.ajax.reload(null, false);
                    } else {
                        showDangerToast(res.message || 'Gagal menambahkan sesi');
                    }
                },
                error: function (xhr) {
                    showDangerToast("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                }
            });
        });

        // Edit button click
        $('#sesi').on('click', '.btn-edit', function () {
            var data = $(this).data();
            $('#editIdSesi').val(data.id);
            $('#namaEdit').val(data.nama);
            $('#kodeEdit').val(data.kode);
            $('#jamAwalEdit').val(data.awal);
            $('#jamAkhirEdit').val(data.akhir);
            $('#istirahatEdit').val(data.istirahat);
            $('#editSesiModal').modal('show');
        });

        // Update
        $('#updateForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "cbtsesi/update",
                data: $(this).serialize(),
                method: 'POST',
                dataType: "JSON",
                success: function (res) {
                    if (res.status) {
                        $('#editSesiModal').modal('hide');
                        showSuccessToast(res.message || 'Sesi berhasil diupdate');
                        table.ajax.reload(null, false);
                    } else {
                        showDangerToast(res.message || 'Gagal mengupdate sesi');
                    }
                },
                error: function (xhr) {
                    showDangerToast("Error: " + (xhr.responseJSON?.message || xhr.responseText));
                }
            });
        });

        // Bulk delete
        $("#bulk").on("submit", function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "cbtsesi/delete",
                data: $(this).serialize(),
                type: "POST",
                dataType: "JSON",
                success: function (res) {
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: (res.total || 0) + " data berhasil dihapus",
                            timer: 2000
                        });
                        reload_ajax();
                    } else {
                        Swal.fire("Gagal", res.message || "Tidak ada data yang dipilih", "error");
                    }
                },
                error: function () {
                    Swal.fire("Gagal", "Ada data yang sedang digunakan", "error");
                }
            });
        });
    });

    function bulk_delete() {
        if ($(".check:checked").length == 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Tidak ada data yang dipilih'
            });
        } else {
            Swal.fire({
                title: "Hapus Data Terpilih?",
                html: `Anda akan menghapus <strong>${$(".check:checked").length}</strong> sesi.<br><small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</small>`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    $("#bulk").submit();
                }
            });
        }
    }

    function reload_ajax() {
        table.ajax.reload(null, false);
        $(".select_all").prop("checked", false);
        $('#hapusterpilih').prop('disabled', true);
    }
</script>
<?= $this->endSection() ?>