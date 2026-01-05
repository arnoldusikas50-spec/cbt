<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-door-open text-primary me-2"></i>Daftar Kelas/Rombel</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus me-1"></i>Tambah Kelas
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tableKelas" class="table table-hover table-striped" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kelas</th>
                        <th>Kode</th>
                        <th>Level</th>
                        <th>Wali Kelas</th>
                        <th>Jml Siswa</th>
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
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kelas" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Kelas</label>
                        <input type="text" name="kode_kelas" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level/Tingkat</label>
                        <select name="level" class="form-select">
                            <option value="10">X (Kelas 10)</option>
                            <option value="11">XI (Kelas 11)</option>
                            <option value="12">XII (Kelas 12)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wali Kelas</label>
                        <select name="wali_kelas" class="form-select">
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($gurus ?? [] as $g): ?>
                                <option value="<?= $g->id_guru ?>">
                                    <?= esc($g->nama_guru) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
        $('#tableKelas').DataTable({
            ajax: '<?= site_url('datakelas/data') ?>',
            columns: [
            { data: 'no' },
            { data: 'nama_kelas' },
            { data: 'kode_kelas' },
            { data: 'level' },
            { data: 'wali_kelas' },
            { data: 'jumlah', render: d => `<span class="badge bg-primary">${d}</span>` },
            {
                data: 'id_kelas',
                render: d => `
                        <button class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteKelas(${d})"><i class="bi bi-trash"></i></button>
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

        const res = await fetch('<?= site_url('datakelas/save') ?>', { method: 'POST', body: formData });
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