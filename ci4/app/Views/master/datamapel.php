<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-book text-primary me-2"></i>Daftar Mata Pelajaran</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus me-1"></i>Tambah Mapel
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableMapel" class="table table-hover table-striped" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Mapel</th>
                        <th>Kode</th>
                        <th>Kelompok</th>
                        <th>Urut</th>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Mapel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Mapel <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelompok</label>
                        <select name="kelompok" class="form-select">
                            <option value="A">Kelompok A (Wajib)</option>
                            <option value="B">Kelompok B (Wajib)</option>
                            <option value="C">Kelompok C (Peminatan)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="urut" class="form-control" value="0">
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

    $(document).ready(function () {
        $('#tableMapel').DataTable({
            ajax: '<?= site_url('datamapel/data') ?>',
            columns: [
            { data: 'no' },
            { data: 'nama_mapel' },
            { data: 'kode' },
            { data: 'kelompok' },
            { data: 'urut' },
            {
                data: 'id_mapel',
                render: d => `
                        <button class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteMapel(${d})"><i class="bi bi-trash"></i></button>
                    `
            }
        ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });
    });

    $('#formTambah').on('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('<?= csrf_token() ?>', csrfToken);

        const res = await fetch('<?= site_url('datamapel/save') ?>', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.status) {
            Swal.fire('Berhasil', data.message, 'success');
            $('#modalTambah').modal('hide');
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    });
</script>
<?= $this->endSection() ?>