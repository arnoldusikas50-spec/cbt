<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <?= esc($subjudul) ?>
                    </div>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <button type="button" onclick="location.reload()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </button>
                        <button type="button" data-from="add" data-bs-toggle="modal" data-bs-target="#createJurusanModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i><span
                                class="d-none d-sm-inline-block text-nowrap">Tambah Jurusan</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Kelola Data Jurusan/Program</h5>
                                <div class="text-muted">Atur daftar jurusan atau program yang tersedia di sekolah</div>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Panduan Pengaturan</h5>
                        <ul class="ps-3 mb-0">
                            <li>Gunakan tombol <span class="badge bg-primary">Tambah Jurusan</span> untuk menambah jurusan baru.</li>
                            <li>Setiap jurusan harus memiliki nama yang unik dan deskriptif.</li>
                            <li>Singkatan membantu mengidentifikasi jurusan dengan singkat dan cepat.</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table id="tableJurusan" class="table table-sm table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center align-middle" width="50">No.</th>
                                    <th class="text-center align-middle">Nama Jurusan</th>
                                    <th class="text-center align-middle">Singkatan</th>
                                    <th class="text-center align-middle" width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="modal fade" id="createJurusanModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Nama Jurusan*</label>
                        <div class="col-md-9">
                            <input type="text" id="createNama" name="nama_jurusan" class="form-control" required
                                placeholder="Contoh: Teknik Informatika">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-3 col-form-label">Singkatan</label>
                        <div class="col-md-9">
                            <input type="text" id="createSingkatan" name="singkatan" class="form-control"
                                placeholder="Contoh: TI">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="editIdJurusan" name="id_jurusan">
                    <input type="hidden" id="method" name="method">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="submit-jurusan">
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
        table = $('#tableJurusan').DataTable({
            ajax: '<?= site_url('datajurusan/data') ?>',
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'nama_jurusan' },
                { data: 'singkatan', className: 'text-center' },
                {
                    data: 'id_jurusan',
                    className: 'text-center',
                    render: function (data) {
                        return `
                            <button type="button" class="btn btn-sm btn-info text-white btn-edit shadow-sm" 
                                data-id="${data}" data-bs-toggle="modal" data-bs-target="#createJurusanModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-hapus shadow-sm" data-id="${data}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });

        $('#tableJurusan').on('click', '.btn-edit', function (e) {
            let id = $(this).data('id');
            let row = table.row((idx, data) => data.id_jurusan === id).data();
            
            if (row) {
                $('#createModalLabel').text('Edit Jurusan');
                $('#editIdJurusan').val(row.id_jurusan);
                $('#createNama').val(row.nama_jurusan);
                $('#createSingkatan').val(row.singkatan);
                $('#submit-jurusan').html('<i class="bi bi-save me-1"></i> Update');
                $('#method').val('edit');
            }
        });

        $('#createJurusanModal').on('show.bs.modal', function (e) {
            // Reset form for new entry if not clicked from edit button
            if (!$('#editIdJurusan').val()) {
                $('#createModalLabel').text('Tambah Jurusan');
                $('#createForm')[0].reset();
                $('#submit-jurusan').html('<i class="bi bi-save me-1"></i> Simpan');
                $('#method').val('add');
            }
        });

        $('#createJurusanModal').on('hidden.bs.modal', function (e) {
            $('#createForm')[0].reset();
            $('#editIdJurusan').val('');
            $('#method').val('');
        });

        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            let url = $('#editIdJurusan').val() ? '<?= site_url('datajurusan/update') ?>' : '<?= site_url('datajurusan/save') ?>';
            
            $.post(url, $(this).serialize(), function (res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message || res.msg, 'success').then(() => {
                        $('#createJurusanModal').modal('hide');
                        table.ajax.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message || res.msg, 'error');
                }
            });
        });

        $('#tableJurusan').on('click', '.btn-hapus', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Jurusan?',
                text: 'Data yang berhubungan akan ikut terpengaruh.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= site_url('datajurusan/delete') ?>', { id_jurusan: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.message || res.msg, res.status ? 'success' : 'error')
                            .then(() => { if (res.status) table.ajax.reload(); });
                    });
                }
            });
        });
    });
</script>