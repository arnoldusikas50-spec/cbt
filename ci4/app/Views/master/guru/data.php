<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-main">
    <div class="container-fluid">
        <div class="card card-default my-shadow">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-person-badge me-2"></i>Daftar Guru</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus me-1"></i>Tambah Guru
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="tableData" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Guru</th>
                            <th>NIP</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Guru</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_guru" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">NIP</label>
                                <input type="text" name="nip" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">No HP</label>
                                <input type="text" name="no_hp" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var table;
    $(document).ready(function () {
        table = $('#tableData').DataTable({
            ajax: '<?= site_url('dataguru/data') ?>',
            columns: [
                { data: 'no' },
                { data: 'nama_guru' },
                { data: 'nip' },
                { data: 'email' },
                { data: 'no_hp' },
                {
                    data: 'id_guru',
                    render: d => '<button class="btn btn-warning btn-sm" onclick="editData(' + d + ')"><i class="bi bi-pencil"></i></button> ' +
                        '<button class="btn btn-danger btn-sm" onclick="hapus(' + d + ')"><i class="bi bi-trash"></i></button>'
                }
            ]
        });
    });

    $('#formTambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('dataguru/save') ?>', type: 'POST',
            data: $(this).serialize() + '&' + CSRF_NAME + '=' + CSRF_HASH, dataType: 'json',
            success: function (res) {
                res.status ? (showToast('success', res.message), $('#modalTambah').modal('hide'), $('#formTambah')[0].reset(), table.ajax.reload()) : showToast('error', res.message);
            }
        });
    });

    function hapus(id) { confirmDelete('<?= site_url('dataguru/delete') ?>', { id_guru: id }, function () { table.ajax.reload(); }); }
</script>
<?= $this->endSection() ?>