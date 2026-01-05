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
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createUserModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i><span
                                class="d-none d-sm-inline-block text-nowrap">Tambah Panitia</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tableUser" class="table table-sm table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center align-middle" width="50">No.</th>
                                    <th class="text-center align-middle">Username</th>
                                    <th class="text-center align-middle">Nama</th>
                                    <th class="text-center align-middle">Instansi</th>
                                    <th class="text-center align-middle">Last Login</th>
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

<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Panitia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Panitia*</label>
                        <input type="text" id="createNama" name="nama_panitia" class="form-control" required
                            placeholder="Nama lengkap panitia">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instansi/Sekolah</label>
                        <input type="text" id="createInstansi" name="instansi" class="form-control"
                            placeholder="Nama instansi/sekolah">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username*</label>
                        <input type="text" id="createUsername" name="username" class="form-control" required
                            placeholder="panitia001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password*</label>
                        <input type="password" id="createPassword" name="password" class="form-control" required
                            placeholder="Minimal 8 karakter">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var table;
    $(document).ready(function () {
        table = $('#tableUser').DataTable({
            ajax: '<?= site_url('userpanitia/data') ?>',
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'username', className: 'text-center' },
                { data: 'nama_panitia' },
                { data: 'instansi' },
                { data: 'last_login', className: 'text-center' },
                {
                    data: 'id',
                    className: 'text-center',
                    render: function (data) {
                        return `
                            <button type="button" class="btn btn-sm btn-danger btn-hapus shadow-sm" data-id="${data}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });

        $('#createUserModal').on('show.bs.modal', function (e) {
            $('#createForm')[0].reset();
            $('#createModalLabel').text('Tambah Panitia');
        });

        $('#createUserModal').on('hidden.bs.modal', function (e) {
            $('#createForm')[0].reset();
        });

        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            
            $.post('<?= site_url('userpanitia/save') ?>', $(this).serialize(), function (res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success').then(() => {
                        $('#createUserModal').modal('hide');
                        table.ajax.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            });
        });

        $('#tableUser').on('click', '.btn-hapus', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Panitia?',
                text: 'Data panitia ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= site_url('userpanitia/delete') ?>', { id: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.message, res.status ? 'success' : 'error')
                            .then(() => { if (res.status) table.ajax.reload(); });
                    });
                }
            });
        });
    });
</script>