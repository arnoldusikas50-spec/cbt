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
                                class="d-none d-sm-inline-block text-nowrap">Tambah User Siswa</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Filter Kelas</label>
                            <select id="filterKelas" class="form-select">
                                <option value="">-- Semua Kelas --</option>
                                <?php if (isset($kelases) && is_array($kelases)): ?>
                                    <?php foreach ($kelases as $k): ?>
                                        <option value="<?= $k->id_kelas ?>">
                                            <?= esc($k->nama_kelas ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="button" id="btnResetAll" class="btn btn-warning shadow-sm w-100">
                                <i class="bi bi-key-fill me-1"></i> Reset Semua ke NIS
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="tableUser" class="table table-sm table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center align-middle" width="50">No.</th>
                                    <th class="text-center align-middle">Nama</th>
                                    <th class="text-center align-middle">NIS</th>
                                    <th class="text-center align-middle">Kelas</th>
                                    <th class="text-center align-middle">Username</th>
                                    <th class="text-center align-middle">Password</th>
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
                    <h5 class="modal-title" id="createModalLabel">Tambah User Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Siswa*</label>
                        <select id="createSiswa" name="id_siswa" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php if (isset($siswas) && is_array($siswas)): ?>
                                <?php foreach ($siswas as $s): ?>
                                    <option value="<?= $s->id_siswa ?>">
                                        <?= esc($s->nama ?? '') ?> (<?= esc($s->nis ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username*</label>
                        <input type="text" id="createUsername" name="username" class="form-control" required
                            placeholder="siswa001">
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
            ajax: {
                url: '<?= site_url('usersiswa/data') ?>',
                data: function (d) {
                    d.id_kelas = $('#filterKelas').val();
                }
            },
            columns: [
                { data: 'no', className: 'text-center' },
                { data: 'nama' },
                { data: 'nis', className: 'text-center' },
                { data: 'kelas', className: 'text-center' },
                { data: 'username', className: 'text-center' },
                {
                    data: 'has_password',
                    className: 'text-center',
                    render: function (data) {
                        return data ? '<span class="badge bg-success">Ada</span>' : '<span class="badge bg-secondary">Belum</span>';
                    }
                },
                {
                    data: 'id_siswa',
                    className: 'text-center',
                    render: function (data) {
                        return `
                            <button type="button" class="btn btn-sm btn-info text-white btn-reset shadow-sm" data-id="${data}">
                                <i class="bi bi-key"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });

        $('#createUserModal').on('show.bs.modal', function (e) {
            $('#createForm')[0].reset();
            $('#createModalLabel').text('Tambah User Siswa');
        });

        $('#createUserModal').on('hidden.bs.modal', function (e) {
            $('#createForm')[0].reset();
        });

        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            
            $.post('<?= site_url('usersiswa/save') ?>', $(this).serialize(), function (res) {
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

        $('#filterKelas').on('change', function () {
            table.ajax.reload();
        });

        $('#tableUser').on('click', '.btn-reset', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Reset Password',
                input: 'password',
                inputLabel: 'Password Baru',
                showCancelButton: true,
                confirmButtonText: 'Reset'
            }).then((result) => {
                if (result.value) {
                    $.post('<?= site_url('usersiswa/resetPassword') ?>', { id_siswa: id, password: result.value, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.message, res.status ? 'success' : 'error')
                            .then(() => { if (res.status) table.ajax.reload(); });
                    });
                }
            });
        });

        $('#btnResetAll').on('click', function () {
            var kelas = $('#filterKelas').val();
            if (!kelas) {
                Swal.fire('Perhatian', 'Pilih kelas terlebih dahulu', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Reset Semua Password?',
                text: 'Password semua siswa di kelas ini akan direset ke NIS mereka',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reset',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= site_url('usersiswa/resetAll') ?>', { id_kelas: kelas, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.message, res.status ? 'success' : 'error')
                            .then(() => { if (res.status) table.ajax.reload(); });
                    });
                }
            });
        });
    });
</script>