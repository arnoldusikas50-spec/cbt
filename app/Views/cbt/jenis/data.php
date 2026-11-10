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
                <h6 class="card-title"><i class="bi bi-file-earmark me-2"></i><?= esc($judul) ?></h6>
                <div class="card-tools ms-auto">
                    <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createJenisModal"
                        class="btn btn-sm btn-primary shadow-sm ms-1"><i class="bi bi-plus me-1"></i> Tambah Jenis
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkDeleteForm">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table id="jenis" class="w-100 table table-striped table-bordered table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="40" class="align-middle text-center p-0">
                                        <input class="select_all" type="checkbox">
                                    </th>
                                    <th width="10" class="align-middle text-center p-0">No.</th>
                                    <th>Jenis Ujian</th>
                                    <th>Kode Jenis</th>
                                    <th width="100" class="align-middle text-center p-0">Status</th>
                                    <th width="20" class="align-middle text-center p-0">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createJenisModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Jenis Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jenis Ujian*</label>
                        <input type="text" class="form-control" name="nama_jenis" placeholder="Contoh: Penilaian Harian"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Jenis*</label>
                        <input type="text" class="form-control" name="kode_jenis" placeholder="Contoh: PH" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editJenisModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="updateForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Jenis Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jenis Ujian*</label>
                        <input type="text" id="namaEdit" name="nama_jenis" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Jenis*</label>
                        <input type="text" id="kodeEdit" name="kode_jenis" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdJenis" name="id_jenis">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
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
        table = $("#jenis").DataTable({
            initComplete: function () {
                var api = this.api();
                $('#jenis_filter input').off('.DT').on('keyup.DT', function (e) {
                    api.search(this.value).draw();
                });
            },
            oLanguage: { sProcessing: "loading..." },
            processing: true,
            serverSide: false,
            ajax: { "url": base_url + "cbtjenis/data", "type": "POST" },
            columns: [
                {
                    data: "id_jenis",
                    render: function (data, type, row, meta) {
                        return '<div class="text-center"><input name="checked[]" class="check" value="' + data + '" type="checkbox"></div>';
                    },
                    orderable: false,
                    searchable: false
                },
                { data: "no", class: "text-center" },
                { data: "nama_jenis" },
                { data: "kode_jenis" },
                {
                    data: "status",
                    render: function (data, type, row, meta) {
                        var badge = data == '1' ? 'bg-success' : 'bg-danger';
                        var text = data == '1' ? 'AKTIF' : 'NON AKTIF';
                        return '<div class="text-center"><button class="btn btn-sm ' + badge + ' btn-status" data-id="' + row.id_jenis + '" data-status="' + data + '">' + text + '</button></div>';
                    }
                },
                {
                    data: "id_jenis",
                    render: function (data, type, row, meta) {
                        return '<div class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-warning btn-edit me-1" data-id="' + data + '" data-nama="' + row.nama_jenis + '" data-kode="' + row.kode_jenis + '"><i class="bi bi-pencil"></i></button>' +
                            '</div>';
                    }
                }
            ],
            order: [[1, 'asc']]
        });

        $(".select_all").on("click", function () {
            if (this.checked) {
                $(".check").each(function () {
                    this.checked = true;
                });
            } else {
                $(".check").each(function () {
                    this.checked = false;
                });
            }
        });

        $("#jenis").on("click", ".check", function () {
            if ($(".check:checked").length == $(".check").length) {
                $(".select_all").prop("checked", true);
            } else {
                $(".select_all").prop("checked", false);
            }
        });

        $('#jenis').on('click', '.btn-status', function () {
            var id = $(this).data('id');
            var status = $(this).data('status');
            $.post(base_url + "cbtjenis/togglestatus", { id_jenis: id, status: status, [CSRF_NAME]: CSRF_HASH }, function (res) {
                if (res.success) {
                    showSuccessToast(res.message);
                    table.ajax.reload();
                } else {
                    showDangerToast(res.message);
                }
            });
        });

        $('#jenis').on('click', '.btn-edit', function () {
            $('#editIdJenis').val($(this).data('id'));
            $('#namaEdit').val($(this).data('nama'));
            $('#kodeEdit').val($(this).data('kode'));
            $('#editJenisModal').modal('show');
        });

        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            $.post(base_url + "cbtjenis/add", $(this).serialize(), function (res) {
                if (res.status) {
                    $('#createJenisModal').modal('hide');
                    $('#createForm')[0].reset();
                    table.ajax.reload();
                    showToast('success', res.message);
                } else {
                    showToast('error', res.message);
                }
            });
        });

        $('#updateForm').on('submit', function (e) {
            e.preventDefault();
            $.post(base_url + "cbtjenis/update", $(this).serialize(), function (res) {
                if (res.status) {
                    $('#editJenisModal').modal('hide');
                    table.ajax.reload();
                    showToast('success', res.message);
                } else {
                    showToast('error', res.message);
                }
            });
        });

        $('#jenis').on('click', '.btn-delete', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Jenis Ujian?',
                text: 'Data akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(base_url + 'cbtjenis/delete', { checked: [id], [CSRF_NAME]: CSRF_HASH }, function (res) {
                        if (res.status) {
                            showToast('success', res.message);
                            table.ajax.reload();
                        } else {
                            showToast('error', res.message);
                        }
                    });
                }
            });
        });
    });

    function reload_ajax() {
        table.ajax.reload();
    }
</script>

<?= $this->endSection() ?>