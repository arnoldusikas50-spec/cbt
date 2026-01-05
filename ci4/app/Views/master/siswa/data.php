<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<main class="app-main bg-white pt-4">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Data Siswa' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Data Siswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-people me-2"></i>Daftar Siswa</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambah">
                            <i class="bi bi-plus me-1"></i>Tambah Siswa
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableSiswa" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama</th>
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Kelas</th>
                                    <th>L/P</th>
                                    <th width="100">Aksi</th>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Siswa</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>NISN</label>
                                <input type="text" name="nisn" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Agama</label>
                                <select name="agama" class="form-control">
                                    <option value="1">Islam</option>
                                    <option value="2">Kristen</option>
                                    <option value="3">Katolik</option>
                                    <option value="4">Hindu</option>
                                    <option value="5">Buddha</option>
                                    <option value="6">Konghucu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control"
                                    placeholder="Kosongkan untuk menggunakan NIS">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Siswa</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEdit">
                <input type="hidden" name="id_siswa" id="editId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama" id="editNama" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>NIS</label>
                                <input type="text" name="nis" id="editNis" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>NISN</label>
                                <input type="text" name="nisn" id="editNisn" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="editJk" class="form-control">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" id="editTempat" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" id="editTgl" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" id="editAlamat" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->

<?= $this->endSection() ?>

<script>
    var table;

    $(document).ready(function () {
        table = $('#tableSiswa').DataTable({
            processing: true,
            serverSide: true,
            ajax: '<?= site_url('datasiswa/data') ?>',
            columns: [
                { data: 'no' },
                { data: 'nama' },
                { data: 'nis' },
                { data: 'nisn' },
                { data: 'kelas' },
                { data: 'jk' },
                {
                    data: 'id_siswa',
                    render: function (data, type, row) {
                        return '<button class="btn btn-warning btn-sm" onclick="editSiswa(' + data + ')"><i class="bi bi-pencil"></i></button> ' +
                            '<button class="btn btn-danger btn-sm" onclick="hapusSiswa(' + data + ')"><i class="bi bi-trash"></i></button>';
                    }
                }
            ]
        });
    });

    // Form Tambah
    $('#formTambah').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('datasiswa/save') ?>',
            type: 'POST',
            data: $(this).serialize() + '&' + CSRF_NAME + '=' + CSRF_HASH,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    showToast('success', res.message);
                    $('#modalTambah').modal('hide');
                    $('#formTambah')[0].reset();
                    table.ajax.reload();
                } else {
                    showToast('error', res.message);
                }
            }
        });
    });

    // Edit
    function editSiswa(id) {
        var row = table.rows().data().toArray().find(r => r.id_siswa == id);
        if (row) {
            $('#editId').val(row.id_siswa);
            $('#editNama').val(row.nama);
            $('#editNis').val(row.nis);
            $('#editNisn').val(row.nisn);
            $('#modalEdit').modal('show');
        }
    }

    $('#formEdit').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: '<?= site_url('datasiswa/update') ?>',
            type: 'POST',
            data: $(this).serialize() + '&' + CSRF_NAME + '=' + CSRF_HASH,
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    showToast('success', res.message);
                    $('#modalEdit').modal('hide');
                    table.ajax.reload();
                } else {
                    showToast('error', res.message);
                }
            }
        });
    });

    // Hapus
    function hapusSiswa(id) {
        confirmDelete('<?= site_url('datasiswa/delete') ?>', { id_siswa: id }, function () {
            table.ajax.reload();
        });
    }
</script>