<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-shield-lock-fill text-primary me-2"></i>
                    Manajemen Panitia
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">User Panitia</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary"><i class="bi bi-people me-2"></i>Daftar Panitia</h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-1">
                    <a href="<?= site_url('cbtcetak/kartupengawas') ?>" class="btn btn-info btn-sm shadow-sm text-white">
                        <i class="bi bi-person-badge-fill me-1"></i> Cetak Kartu Pengawas
                    </a>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#generatePanitiaModal"
                        class="btn btn-warning btn-sm shadow-sm">
                        <i class="bi bi-magic me-1"></i> Auto-Generate
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createPanitiaModal"
                        class="btn btn-primary btn-sm shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Baru
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small">Show
                                <select id="users_length" class="form-select form-select-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </label>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex gap-2 align-items-end">
                                <input id="input-search" type="search" class="form-control form-control-sm"
                                    placeholder="Cari panitia...">
                                <button id="btn-search" type="button" class="btn btn-sm btn-primary"
                                    onclick="applySearch()" disabled>
                                    <i class="bi bi-search"></i>
                                </button>
                                <button id="btn-clear" type="button" class="btn btn-sm btn-secondary" disabled>
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-2 d-flex gap-2 align-items-center">
                    <span class="text-muted small fw-bold me-2"><i class="bi bi-lightning-charge-fill me-1"></i> Bulk
                        Action:</span>
                    <button type="button" class="btn btn-mass-action btn-success btn-sm shadow-sm px-3" data-action="aktifkan">
                        <i class="bi bi-shield-check me-1"></i> Aktifkan Semua
                    </button>
                    <button type="button" class="btn btn-mass-action btn-danger btn-sm shadow-sm px-3"
                        data-action="nonaktifkan">
                        <i class="bi bi-shield-slash me-1"></i> Nonaktifkan Semua
                    </button>
                </div>

                <?= form_open('', array('id' => 'bulk')); ?>
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table id="users" class="w-100 table table-striped table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th width="40" class="align-middle text-center p-0">No.</th>
                                <th class="align-middle">Nama</th>
                                <th class="align-middle">Username</th>
                                <th class="align-middle">Password</th>
                                <th class="text-center align-middle">Jabatan</th>
                                <th class="text-center align-middle">Reset Login</th>
                                <th class="align-middle text-center">Status/Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                        </tbody>
                    </table>
                </div>
                <?= form_close() ?>

                <div class="mt-3">
                    <div class="float-start" id="total-entri"></div>
                    <nav aria-label="Page navigation" class="float-end">
                        <ul class="pagination mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'pager')); ?>
<input type="hidden" id="pager-page" name="page" value="1">
<input type="hidden" id="pager-limit" name="limit" value="10">
<?= form_close() ?>

<style>
    .fade-in-up {
        animation: fadeInUp 0.5s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- Generate Modal -->
<?= form_open('userpanitia/generate', array('id' => 'generate')) ?>
<div class="modal fade" id="generatePanitiaModal" tabindex="-1" role="dialog"
    aria-labelledby="generatePanitiaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generatePanitiaModalLabel">
                    <i class="bi bi-magic me-2"></i> Auto-Generate User Panitia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-0 mb-3">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>
                            <p class="mb-1 small fw-bold">Petunjuk Pembuatan Akun:</p>
                            <small class="d-block mb-1">Fitur ini akan membuat akun Panitia dengan jabatan
                                <b>Pengawas</b> untuk guru yang dipilih.</small>
                            <div class="bg-white p-2 rounded border small">
                                <b>Username:</b> <code>p_[NIP]</code><br>
                                <b>Password:</b> <code>123456</code>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Guru (Dapat dipilih lebih dari satu)</label>
                    <select id="select-gurus" class="select2 form-control" multiple="multiple" name="gurus[]"
                        data-placeholder="Pilih Guru dari daftar..." style="width: 100%;" required>
                        <?php foreach ($gurus as $id => $nama): ?>
                            <?php if ($id != '0'): ?>
                                <option value="<?= $id ?>"><?= $nama ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-magic me-2"></i> Generate
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<!-- Modal Create -->
<?= form_open('userpanitia/create', array('id' => 'create')) ?>
<div class="modal fade" id="createPanitiaModal" tabindex="-1" role="dialog" aria-labelledby="createPanitiaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPanitiaModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i> Tambah Panitia Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    <?= form_label('Nama Panitia:', 'nama_panitia', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'nama_panitia', 'class' => 'form-control', 'required' => 'required']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('NIP:', 'nip', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'nip', 'class' => 'form-control', 'type' => 'number']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Jabatan:', 'jabatan', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_dropdown('jabatan', ['' => 'Pilih Jabatan', 'Panitia' => 'Panitia', 'Pengawas' => 'Pengawas'], '', ['class' => 'form-control', 'id' => 'create-jabatan']) ?>
                    </div>
                </div>
                <div class="mb-3 row d-none" id="create-ruang-group">
                    <?= form_label('Ruang:', 'ruang', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?php
                        $ruang_options = ['' => 'Pilih Ruang'] + (isset($ruangs) ? (array) $ruangs : []);
                        echo form_dropdown('ruang', $ruang_options, '', ['class' => 'form-control']);
                        ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Username:', 'username', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'username', 'class' => 'form-control', 'required' => 'required', 'minlength' => '4', 'maxlength' => '50']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Password:', 'password', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <div class="input-group">
                            <?= form_password(['name' => 'password', 'id' => 'create-password', 'class' => 'form-control', 'required' => 'required', 'minlength' => '5', 'maxlength' => '50']) ?>
                            <button class="btn btn-outline-primary btn-generate-password" type="button"
                                data-target="#create-password" title="Generate Password">
                                <i class="bi bi-magic"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Email:', 'email', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'email', 'class' => 'form-control', 'type' => 'email']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('No HP:', 'no_hp', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'no_hp', 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Instansi:', 'instansi', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'instansi', 'class' => 'form-control', 'placeholder' => 'Asal Sekolah/Instansi']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Foto Profil:', 'foto', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maks: 2MB</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="reset" class="btn btn-outline-warning">
                    <i class="bi bi-arrow-repeat me-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<!-- Modal Edit -->
<?= form_open('userpanitia/edit', array('id' => 'edit')) ?>
<div class="modal fade" id="editPanitiaModal" tabindex="-1" role="dialog" aria-labelledby="editPanitiaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPanitiaModalLabel">
                    <i class="bi bi-pencil-square me-2"></i> Edit Data Panitia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_panitia">
                <div class="mb-3 row">
                    <?= form_label('Nama Panitia:', 'nama_panitia', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'nama_panitia', 'class' => 'form-control', 'required' => 'required']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('NIP:', 'nip', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'nip', 'class' => 'form-control', 'type' => 'number']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Jabatan:', 'jabatan', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_dropdown('jabatan', ['' => 'Pilih Jabatan', 'Panitia' => 'Panitia', 'Pengawas' => 'Pengawas'], '', ['class' => 'form-control', 'id' => 'edit-jabatan']) ?>
                    </div>
                </div>
                <div class="mb-3 row d-none" id="edit-ruang-group">
                    <?= form_label('Ruang:', 'ruang', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?php
                        echo form_dropdown('ruang', $ruang_options, '', ['class' => 'form-control']);
                        ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Username:', 'username', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'username', 'class' => 'form-control', 'required' => 'required', 'minlength' => '4', 'maxlength' => '50']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Password:', 'password', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <div class="input-group">
                            <?= form_password(['name' => 'password', 'id' => 'edit-password', 'class' => 'form-control', 'placeholder' => 'Kosongkan jika tidak ingin mengubah password']) ?>
                            <button class="btn btn-outline-primary btn-generate-password" type="button"
                                data-target="#edit-password" title="Generate Password">
                                <i class="bi bi-magic"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Email:', 'email', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'email', 'class' => 'form-control', 'type' => 'email']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('No HP:', 'no_hp', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'no_hp', 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Instansi:', 'instansi', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <?= form_input(['name' => 'instansi', 'class' => 'form-control', 'placeholder' => 'Asal Sekolah/Instansi']) ?>
                    </div>
                </div>
                <div class="mb-3 row">
                    <?= form_label('Foto Profil:', 'foto', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2">
                            <img id="edit-foto-preview" src="<?= base_url('assets/img/user.png') ?>" alt="Preview"
                                class="rounded-circle border shadow-sm me-3"
                                style="width: 60px; height: 60px; object-fit: cover;">
                            <div id="edit-foto-action" class="d-none">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hapus_foto" value="1"
                                        id="hapus_foto_check">
                                    <label class="form-check-label text-danger small" for="hapus_foto_check">
                                        Hapus Foto
                                    </label>
                                </div>
                            </div>
                        </div>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maks: 2MB</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

<?= form_open('', ['id' => 'up']) ?>
<?= form_close(); ?>

<script src="<?= base_url() ?>assets/app/js/jquery.twbsPagination.js" type="text/javascript"></script>
<script src="<?= base_url() ?>assets/app/js/ajax_request.js" type="text/javascript"></script>
<script>
    const user_id = '<?= $user->id ?>';
    let currentPage = 1;
    let perPage = 10;
    let $pagination, defaultOpts, query;

    $(document).ready(function () {
        $('.select2').each(function () {
            var $this = $(this);
            $this.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $this.data('placeholder') || 'Pilih...',
                dropdownParent: $this.closest('.modal')
            });
        });

        $(document).on('shown.bs.modal', '.modal', function () {
            $(this).find('.select2').each(function () {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Pilih...',
                    dropdownParent: $(this).closest('.modal')
                });
            });
        });

        $pagination = $('#pagination');
        defaultOpts = {
            visiblePages: 5,
            initiateStartPageClick: false,
            onPageClick: function (event, page) {
                currentPage = page;
                loadPanitia();
            }
        };
        $pagination.twbsPagination(defaultOpts);

        $('#users_length').change(function () {
            $('#pager-limit').val($(this).val());
            perPage = $(this).val();
            currentPage = 1;
            loadPanitia();
        });

        $('#input-search').on('change keyup', function () {
            var val = $(this).val();
            query = val === "" ? null : val;
            $('#btn-clear').attr('disabled', query == null);
            $('#btn-search').attr('disabled', query == null);
        });

        $('#btn-clear').on('click', function () {
            query = null;
            currentPage = 1;
            $(this).attr('disabled', query == null);
            $('#btn-search').attr('disabled', query == null);
            loadPanitia();
        });

        $('#edit').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData($(this)[0]);
            const fileInput = $(this).find('input[name="foto"]')[0];
            if (fileInput.files.length > 0) {
                formData.append('foto', fileInput.files[0]);
            }

            ajaxPost(base_url + 'userpanitia/edit', formData, function (error, response) {
                $('#editPanitiaModal').modal('hide');
                if (error) {
                    Swal.fire('Error', error, 'error');
                } else {
                    if (response.status) {
                        Swal.fire('Sukses', response.msg, 'success').then(() => {
                            loadPanitia();
                        });
                    } else {
                        let html = '<ul>';
                        if (response.errors) {
                            Object.keys(response.errors).forEach(function (key) {
                                if (response.errors[key]) {
                                    html += '<li>' + response.errors[key] + '</li>';
                                }
                            });
                        }
                        html += '</ul>';
                        Swal.fire('ERROR', html || response.msg, 'error');
                    }
                }
            });
        });

        $('#create-jabatan').change(function () {
            if ($(this).val() === 'Pengawas') {
                $('#create-ruang-group').removeClass('d-none');
            } else {
                $('#create-ruang-group').addClass('d-none');
            }
        });

        $('#edit-jabatan').change(function () {
            if ($(this).val() === 'Pengawas') {
                $('#edit-ruang-group').removeClass('d-none');
            } else {
                $('#edit-ruang-group').addClass('d-none');
            }
        });

        $('#create').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            let formData = new FormData($(this)[0]);
            const fileInput = $(this).find('input[name="foto"]')[0];
            if (fileInput.files.length > 0) {
                formData.append('foto', fileInput.files[0]);
            }

            ajaxPost(base_url + 'userpanitia/create', formData, function (error, response) {
                $('#createPanitiaModal').modal('hide').data('bs.modal', null);
                $('#createPanitiaModal').on('hidden', function () {
                    $(this).data('modal', null);
                });

                if (error) {
                    Swal.fire('Error', error, 'error');
                } else {
                    if (response.status) {
                        Swal.fire('Sukses', response.msg || 'Panitia berhasil ditambahkan', 'success').then(() => {
                            loadPanitia();
                        });
                    } else {
                        let html = '<ul>';
                        if (response.errors) {
                            Object.keys(response.errors).forEach(function (key) {
                                if (response.errors[key]) {
                                    html += '<li>' + response.errors[key] + '</li>';
                                }
                            });
                        }
                        html += '</ul>';
                        Swal.fire('ERROR', html || response.msg || 'Terjadi kesalahan', 'error');
                    }
                }
            });
        });

        $('#generate').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData($(this)[0]);
            $('#loading').removeClass('d-none');
            ajaxPost(base_url + 'userpanitia/generate', formData, function (error, response) {
                $('#loading').addClass('d-none');
                $('#generatePanitiaModal').modal('hide');
                if (error) {
                    Swal.fire('Error', error.message, 'error');
                } else {
                    Swal.fire(response.status ? 'Sukses' : 'Info', response.msg, response.status ? 'success' : 'info').then(() => {
                        loadPanitia();
                    });
                }
            });
        });

        $("#users").on("click", ".btn-aktif", function () {
            let id = $(this).data("id");
            let id_user = $(this).data("user-id");
            let nama = encodeURIComponent($(this).data("nama"));
            $('#loading').removeClass('d-none');
            let formActivate = new FormData($('#pager')[0]);
            formActivate.append('is_active', 'aktifkan');
            formActivate.append('id_panitia', id);
            formActivate.append('id', id_user);
            formActivate.append('nama', nama);
            ajaxPost('userpanitia/activate', formActivate, function (error, data) {
                $('#loading').addClass('d-none');
                if (data.status) {
                    Swal.fire({ title: "Sukses", text: data.msg, icon: "success" }).then(() => {
                        loadPanitia();
                    });
                } else {
                    Swal.fire({ title: "Error", text: data.msg, icon: "error" });
                }
            });
        });

        $("#users").on("click", ".btn-nonaktif", function () {
            let id = $(this).data("id");
            let id_user = $(this).data("user-id");
            let username = $(this).data("username");
            let nama = encodeURIComponent($(this).data("nama"));
            $('#loading').removeClass('d-none');
            let formActivate = new FormData($('#pager')[0]);
            formActivate.append('is_active', 'nonaktifkan');
            formActivate.append('id_panitia', id);
            formActivate.append('id', id_user);
            formActivate.append('username', username);
            formActivate.append('nama', nama);
            ajaxPost('userpanitia/activate', formActivate, function (error, data) {
                $('#loading').addClass('d-none');
                if (data.status) {
                    Swal.fire({ title: "Sukses", html: data.msg, icon: "success" }).then(() => {
                        loadPanitia();
                    });
                } else {
                    Swal.fire({ title: "Error", text: data.msg, icon: "error" });
                }
            });
        });

        $("#users").on("click", ".btn-reset", function () {
            let username = $(this).data("username");
            let id = $(this).data("id");
            let nama = encodeURIComponent($(this).data("nama"));
            $('#loading').removeClass('d-none');
            let formActivate = new FormData($('#pager')[0]);
            formActivate.append('id_panitia', id);
            formActivate.append('username', username);
            formActivate.append('nama', nama);
            ajaxPost('userpanitia/reset_login', formActivate, function (error, response) {
                $('#loading').addClass('d-none');
                if (response.status) {
                    Swal.fire({ title: "Sukses", html: "<b>" + decodeURIComponent(response.msg) + "<b>", icon: "success" }).then(() => {
                        loadPanitia();
                    });
                } else {
                    Swal.fire({ title: "Error", html: "<b>" + decodeURIComponent(response.msg) + "<b>", icon: "error" });
                }
            });
        });

        $("#users").on("click", ".btn-edit", function () {
            let id = $(this).data("id");
            let nama = $(this).data("nama");
            let nip = $(this).data("nip");
            let jabatan = $(this).data("jabatan");
            let username = $(this).data("username");
            let email = $(this).data("email");
            let no_hp = $(this).data("no_hp");
            let instansi = $(this).data("instansi");
            let ruang = $(this).data("ruang");
            $('#editPanitiaModal').find('input[name="id_panitia"]').val(id);
            $('#editPanitiaModal').find('input[name="nama_panitia"]').val(nama);
            $('#editPanitiaModal').find('input[name="nip"]').val(nip);
            $('#editPanitiaModal').find('select[name="jabatan"]').val(jabatan).trigger('change');
            $('#editPanitiaModal').find('input[name="username"]').val(username);
            $('#editPanitiaModal').find('input[name="email"]').val(email);
            $('#editPanitiaModal').find('input[name="no_hp"]').val(no_hp);
            $('#editPanitiaModal').find('input[name="instansi"]').val(instansi);
            $('#editPanitiaModal').find('select[name="ruang"]').val(ruang);

            // Foto preview logic
            const foto = $(this).data("foto");
            if (foto && !foto.includes('assets/img/user.png')) {
                $('#edit-foto-preview').attr('src', foto);
                $('#edit-foto-action').removeClass('d-none');
            } else {
                $('#edit-foto-preview').attr('src', base_url + 'assets/img/user.png');
                $('#edit-foto-action').addClass('d-none');
            }
            $('#hapus_foto_check').prop('checked', false);

            if (jabatan === 'Pengawas') {
                $('#edit-ruang-group').removeClass('d-none');
            } else {
                $('#edit-ruang-group').addClass('d-none');
            }
            $('#editPanitiaModal').modal('show');
        });

        $("#users").on("click", ".btn-delete", function () {
            let id = $(this).data("id");
            let nama = $(this).data("nama");
            Swal.fire({
                title: 'Hapus Panitia?',
                text: 'Panitia "' + nama + '" akan dihapus permanen. Lanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loading').removeClass('d-none');
                    let formData = new FormData($('#pager')[0]);
                    formData.append('id_panitia', id);
                    ajaxPost('userpanitia/delete', formData, function (error, response) {
                        $('#loading').addClass('d-none');
                        if (error) {
                            console.error('Delete error:', error);
                            return; // ajax_request.js already shows Swal on error
                        }
                        if (response && response.status) {
                            Swal.fire('Sukses', response.msg, 'success').then(() => {
                                loadPanitia();
                            });
                        } else {
                            Swal.fire('Error', response ? response.msg : 'Gagal menghapus panitia', 'error');
                        }
                    });
                }
            });
        });

        $("#users").on("click", ".btn-generate-row", function () {
            let id = $(this).data("id");
            let nama = $(this).data("nama");
            let password = generateRandomPassword(8);

            Swal.fire({
                title: 'Generate Password Baru?',
                html: 'Password baru untuk <b>' + nama + '</b> adalah: <br><br><h3 class="text-primary fw-bold">' + password + '</h3><br>Simpan perubahan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loading').removeClass('d-none');
                    let formData = new FormData($('#pager')[0]);
                    formData.append('id_panitia', id);
                    formData.append('password', password);
                    ajaxPost('userpanitia/generate_password', formData, function (error, response) {
                        $('#loading').addClass('d-none');
                        if (response && response.status) {
                            Swal.fire({
                                title: 'Sukses',
                                html: 'Password berhasil diperbarui: <br><br><h2 class="text-success fw-bold">' + password + '</h2>',
                                icon: 'success'
                            }).then(() => {
                                loadPanitia();
                            });
                        } else {
                            Swal.fire('Error', response ? response.msg : 'Gagal memperbarui password', 'error');
                        }
                    });
                }
            });
        });

        $(".btn-mass-action").on("click", function () {
            let action = $(this).data("action");
            Swal.fire({
                title: action === 'aktifkan' ? "Aktifkan semua panitia" : "Nonaktifkan semua panitia",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Lanjutkan"
            }).then(result => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    Swal.fire({
                        title: action === 'aktifkan' ? "Mengaktifkan semua panitia" : "Menonaktifkan semua panitia",
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    let formActivate = new FormData($('#pager')[0]);
                    formActivate.append('is_active', action);
                    ajaxPost("userpanitia/aktifkansemua", formActivate, function (error, response) {
                        $('#loading').addClass('d-none');
                        if (error) {
                            console.error('Batch action error:', error);
                            return;
                        }
                        if (response && response.status) {
                            Swal.fire({
                                title: "Sukses",
                                html: response.msg,
                                icon: "success"
                            }).then(() => {
                                loadPanitia();
                            });
                        } else {
                            Swal.fire('Error', response ? response.msg : 'Gagal memproses permintaan', 'error');
                        }
                    });
                }
            });
        });

        loadPanitia();
    });

    function loadPanitia() {
        $('#pager-page').val(currentPage);
        $('#loading').removeClass('d-none');
        let dataPost = new FormData($('#pager')[0]);
        if (query != null) dataPost.append('search', query);

        ajaxPost('userpanitia/list', dataPost, function (error, data) {
            console.log('Response from server:', data);

            if (error) {
                console.error('Error loading panitia:', error);
                $('#loading').addClass('d-none');
                return;
            }

            if (!data || typeof data !== 'object') {
                console.error('Invalid response format:', data);
                Swal.fire('Error', 'Format data tidak valid', 'error');
                $('#loading').addClass('d-none');
                return;
            }

            if (data.pages > 0) {
                $pagination.removeClass('d-none');
                $pagination.twbsPagination('destroy');
                $pagination.twbsPagination($.extend({}, defaultOpts, {
                    startPage: currentPage,
                    totalPages: data.pages,
                }));
            }
            previewData(data);
            $('#loading').addClass('d-none');
            $('#input-search').val(data.search || '');
        });
    }

    function previewData(data) {
        $('#input-search').val(data.search);
        var html = '';
        if (data.lists.length > 0) {
            $.each(data.lists, function (idx, panitia) {
                html += `<tr>
                <td class="text-center align-middle">
                    <input type="hidden" name="ids[]" value="${panitia.id_panitia}">
                    ${Number((perPage * (currentPage - 1)) + (idx + 1))}</td>
            <td>
                <div class="d-flex align-items-center h-100">
                    <img class="avatar rounded-circle justify-content-center align-self-center shadow-sm" src="${panitia.foto || base_url + 'assets/img/user.png'}" width="40" height="40" alt="User Image" onerror="this.src=base_url+'assets/img/user.png'">
                        <div class="ms-2 flex-grow-1 justify-content-center align-self-center fw-bold text-dark">${panitia.nama_panitia}</div>
                </div>
            </td>
            <td class="align-middle">${panitia.username}</td>
            <td class="align-middle text-break">${panitia.password}</td>
            <td class="align-middle text-center"><span class="badge bg-secondary">${panitia.jabatan || '-'}</span></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm ${panitia.reset == '0' ? 'btn-secondary' : 'btn-danger'} shadow-sm btn-reset" ${panitia.reset == '0' ? 'disabled' : ''} data-id="${panitia.id_panitia}" data-username="${panitia.username}" data-nama="${panitia.nama_panitia}" title="Reset Login">
                    <i class="bi bi-arrow-repeat text-white"></i>
                </button>
            </td>`;
                html += `<td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-1 align-items-center">`;
                if (panitia.active === "1") {
                    html += `<span class="badge bg-success">Aktif</span>
                             <button type="button" class="btn btn-nonaktif btn-warning btn-sm shadow-sm" data-id="${panitia.id_panitia}" data-user-id="${panitia.id}" data-username="${panitia.username}" data-nama="${panitia.nama_panitia}" title="Nonaktifkan">
                                 <i class="bi bi-slash-circle text-white"></i>
                             </button>`;
                } else {
                    html += `<span class="badge bg-danger">Nonaktif</span>
                             <button type="button" class="btn btn-aktif btn-success btn-sm shadow-sm" data-id="${panitia.id_panitia}" data-user-id="${panitia.id}" data-nama="${panitia.nama_panitia}" title="Aktifkan">
                                 <i class="bi bi-person-plus-fill text-white"></i>
                             </button>`;
                }
                html += `<button type="button" class="btn btn-generate-row btn-primary btn-sm shadow-sm" data-id="${panitia.id_panitia}" data-nama="${panitia.nama_panitia}" title="Generate Password">
                        <i class="bi bi-magic text-white"></i>
                    </button>
                    <button type="button" class="btn btn-edit btn-info btn-sm shadow-sm" data-id="${panitia.id_panitia}" data-nama="${panitia.nama_panitia}" data-nip="${panitia.nip || ''}" data-jabatan="${panitia.jabatan || ''}" data-username="${panitia.username}" data-email="${panitia.email || ''}" data-no_hp="${panitia.no_hp || ''}" data-instansi="${panitia.instansi || ''}" data-ruang="${panitia.ruang || ''}" data-foto="${panitia.foto || ''}" title="Edit">
                        <i class="bi bi-pencil text-white"></i>
                    </button>
                    <button type="button" class="btn btn-delete btn-danger btn-sm shadow-sm" data-id="${panitia.id_panitia}" data-nama="${panitia.nama_panitia}" title="Hapus">
                        <i class="bi bi-trash text-white"></i>
                    </button>`;
                html += `</div></td></tr>`;
            });
            const loaded = Number(perPage * (currentPage - 1)) + 1;
            const to = loaded + data.lists.length;
            $('#total-entri').html(`${loaded} ~ ${to - 1} dari ${data.total} entri`);
        } else {
            $('#total-entri').html(`0 ~ 0 dari 0 entri`);
            html += `<tr><td colspan="7" class="text-center align-middle">Tidak ada data panitia</td><tr>`;
        }
        $('#table-body').html(html);
    }

    function applySearch() {
        query = $('#input-search').val();
        currentPage = 1;
        loadPanitia();
    }

    $(document).on('click', '.btn-generate-password', function () {
        const target = $(this).data('target');
        const password = generateRandomPassword(8);
        $(target).val(password).attr('type', 'text');

        // Change icon and type back after a few seconds or when focused? 
        // For now, just show it so the user can see what was generated.

        Swal.fire({
            title: 'Password Generated',
            text: 'Password baru: ' + password,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });

    function generateRandomPassword(length = 8) {
        const uppercase = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
        const numbers = "0123456789";
        const symbols = "@#*";
        const pool = uppercase + numbers;

        // Pilih 1 simbol untuk posisi paling depan
        const symbol = symbols[Math.floor(Math.random() * symbols.length)];
        
        let rest = "";
        // Pastikan ada minimal 1 angka
        rest += numbers[Math.floor(Math.random() * numbers.length)];
        // Pastikan ada minimal 1 huruf besar
        rest += uppercase[Math.floor(Math.random() * uppercase.length)];

        // Sisanya (5 karakter lagi untuk total 7 karakter + 1 simbol)
        for (let i = rest.length; i < length - 1; i++) {
            rest += pool[Math.floor(Math.random() * pool.length)];
        }

        // Acak urutan 7 karakter sisa agar tidak ketebak polanya (angka/huruf di awal)
        const shuffledRest = rest.split('').sort(() => 0.5 - Math.random()).join('');

        // Kembalikan simbol + 7 karakter yang sudah diacak
        return symbol + shuffledRest;
    }

</script>
<?= $this->endSection() ?>