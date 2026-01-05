<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Ruang Ujian' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Ruang</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title text-bold"><i class="bi bi-door-open me-2"></i>Daftar Ruang Ujian</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambah">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Ruang
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableData" class="table table-bordered table-striped table-hover table-sm w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50" class="text-center py-2">No</th>
                                    <th class="py-2">Nama Ruang</th>
                                    <th class="py-2 text-center">Kapasitas</th>
                                    <th width="100" class="text-center py-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah Ruang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Ruang</label>
                        <input type="text" name="nama_ruang" class="form-control"
                            placeholder="Hubungkan nama ruang, misal: Ruang 01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kapasitas</label>
                        <div class="input-group">
                            <input type="number" name="kapasitas" class="form-control" value="30">
                            <span class="input-group-text">Peserta</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm">
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
        table = $('#tableData').DataTable({
            processing: true,
            ajax: '<?= site_url('cbtruang/data') ?>',
            columns: [
                { data: 'no', class: 'text-center' },
                { data: 'nama_ruang' },
                {
                    data: 'kapasitas',
                    class: 'text-center',
                    render: d => '<span class="badge bg-info-subtle text-info border border-info-subtle px-2">' + d + ' peserta</span>'
                },
                {
                    data: 'id_ruang',
                    class: 'text-center',
                    render: d => '<button class="btn btn-danger btn-xs shadow-sm" onclick="hapus(' + d + ')" title="Hapus"><i class="bi bi-trash"></i></button>'
                }
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });
    });

    $('#formTambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('cbtruang/save') ?>',
            type: 'POST',
            data: $(this).serialize() + '&' + CSRF_NAME + '=' + CSRF_HASH,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    showSuccessToast(res.message);
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    table.ajax.reload();
                } else {
                    showDangerToast(res.message);
                }
            }
        });
    });

    function hapus(id) {
        confirmDelete('<?= site_url('cbtruang/delete') ?>', { id_ruang: id }, function () {
            table.ajax.reload();
        });
    }
</script>