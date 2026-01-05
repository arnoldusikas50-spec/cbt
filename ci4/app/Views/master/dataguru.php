<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-badge text-primary me-2"></i>Daftar Guru</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus me-1"></i>Tambah Guru
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableGuru" class="table table-hover table-striped" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Guru</th>
                        <th>NIP</th>
                        <th>NUPTK</th>
                        <th>Email</th>
                        <th>No HP</th>
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
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Guru <span class="text-danger">*</span></label>
                            <input type="text" name="nama_guru" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">NUPTK</label>
                            <input type="text" name="nuptk" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No HP</label>
                            <input type="text" name="no_hp" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"></textarea>
                        </div>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    const csrfToken = '<?= csrf_hash() ?>';
    let table;

    $(document).ready(function () {
        table = $('#tableGuru').DataTable({
            processing: true,
            serverSide: false,
            ajax: '<?= site_url('dataguru/data') ?>',
            columns: [
            { data: 'no' },
            { data: 'nama_guru' },
            { data: 'nip' },
            { data: 'nuptk' },
            { data: 'email' },
            { data: 'no_hp' },
            {
                data: 'id_guru',
                render: function (data) {
                    return `
                            <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteGuru(${data})"><i class="fas fa-trash"></i></button>
                        `;
                }
            }
        ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });
    });

    $('#formTambah').on('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('<?= csrf_token() ?>', csrfToken);

        const res = await fetch('<?= site_url('dataguru/save') ?>', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status) {
            Swal.fire('Berhasil', data.message, 'success');
            $('#modalTambah').modal('hide');
            this.reset();
            table.ajax.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    });

    async function deleteGuru(id) {
        const result = await Swal.fire({
            title: 'Hapus Guru?', icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
        });
        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('id_guru', id);
        formData.append('<?= csrf_token() ?>', csrfToken);

        const res = await fetch('<?= site_url('dataguru/delete') ?>', { method: 'POST', body: formData });
        const data = await res.json();

        data.status ? Swal.fire('Berhasil', data.message, 'success') : Swal.fire('Gagal', data.message, 'error');
        table.ajax.reload();
    }
</script>
<?= $this->endSection() ?>