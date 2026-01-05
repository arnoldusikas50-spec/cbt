<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people text-primary me-2"></i>Daftar Siswa</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus me-1"></i>Tambah Siswa
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableSiswa" class="table table-hover table-striped" style="width:100%">
                <thead class="table-light">
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">NISN</label>
                            <input type="text" name="nisn" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Agama</label>
                            <select name="agama" class="form-select">
                                <option value="1">Islam</option>
                                <option value="2">Kristen</option>
                                <option value="3">Katolik</option>
                                <option value="4">Hindu</option>
                                <option value="5">Buddha</option>
                                <option value="6">Konghucu</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control"
                                placeholder="Kosongkan untuk auto dari NIS">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                <input type="hidden" name="id_siswa" id="editId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" id="editNama" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">NIS</label>
                            <input type="text" name="nis" id="editNis" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">NISN</label>
                            <input type="text" name="nisn" id="editNisn" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="editJk" class="form-select">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="editTempat" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="editTanggal" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" id="editAlamat" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
    const csrfToken = '<?= csrf_hash() ?>';
    let table;

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
                            return `
                            <button class="btn btn-sm btn-warning" onclick="editSiswa(${data})">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSiswa(${data})">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                }
            }
        ],
            language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        }
        });
    });

    // Tambah
    $('#formTambah').on('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('<?= csrf_token() ?>', csrfToken);

        try {
            const res = await fetch('<?= site_url('datasiswa/save') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status) {
                Swal.fire('Berhasil', data.message, 'success');
                $('#modalTambah').modal('hide');
                this.reset();
                table.ajax.reload();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan', 'error');
        }
    });

    // Edit
    function editSiswa(id) {
        // Ideally fetch data from server, for now use table data
        const rowData = table.rows().data().toArray().find(r => r.id_siswa == id);
        if (rowData) {
            $('#editId').val(rowData.id_siswa);
            $('#editNama').val(rowData.nama);
            $('#editNis').val(rowData.nis);
            $('#editNisn').val(rowData.nisn);
            $('#modalEdit').modal('show');
        }
    }

    $('#formEdit').on('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('<?= csrf_token() ?>', csrfToken);

        try {
            const res = await fetch('<?= site_url('datasiswa/update') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status) {
                Swal.fire('Berhasil', data.message, 'success');
                $('#modalEdit').modal('hide');
                table.ajax.reload();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan', 'error');
        }
    });

    // Delete
    async function deleteSiswa(id) {
        const result = await Swal.fire({
            title: 'Hapus Siswa?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        });

        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('id_siswa', id);
        formData.append('<?= csrf_token() ?>', csrfToken);

        try {
            const res = await fetch('<?= site_url('datasiswa/delete') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status) {
                Swal.fire('Berhasil', data.message, 'success');
                table.ajax.reload();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Terjadi kesalahan', 'error');
        }
    }
</script>
<?= $this->endSection() ?>