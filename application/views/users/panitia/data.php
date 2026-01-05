<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div
                    class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title">Master <?= $subjudul ?></h3>
                    <div class="card-tools mt-3 mt-md-0 ms-auto">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createPanitiaModal"
                            class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i><span
                                class="d-none d-sm-inline-block ms-1">Tambah</span>
                        </button>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#generatePanitiaModal"
                            class="btn btn-sm btn-info"><i class="bi bi-magic"></i><span
                                class="d-none d-sm-inline-block ms-1">Generate dari Guru</span>
                        </button>
                        <button type="button" onclick="getDataPanitia()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </button>
                        <button type="button" class="btn btn-action btn-success btn-sm" data-action="aktifkan"
                            data-bs-toggle="tooltip" title="Aktifkan">
                            <i class="bi bi-people m-1"></i><span class="d-none d-sm-inline-block ms-1">Aktifkan
                                Semua</span>
                        </button>
                        <button type="button" class="btn btn-action btn-danger btn-sm" data-action="nonaktifkan"
                            data-bs-toggle="tooltip" title="Nonaktifkan">
                            <i class="bi bi-slash-circle m-1"></i><span
                                class="d-none d-sm-inline-block ms-1">Nonaktifkan
                                Semua</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan User Panitia</h5>
                                <div class="text-muted">Kelola akun panitia penyelenggara</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge bg-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total: <span
                                        id="badge-total-panitia">0</span></span>
                                <span class="badge bg-success px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Aktif: <span
                                        id="badge-total-aktif">0</span></span>
                                <span class="badge bg-warning px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Nonaktif: <span
                                        id="badge-total-nonaktif">0</span></span>
                                <span class="badge bg-info px-3 py-2 text-sm">Perlu Reset Login: <span
                                        id="badge-total-reset">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle-fill me-2"></i>Panduan</h5>
                        <ul class="ps-3 mb-0">
                            <li>Pastikan data panitia terhubung dengan akun pengguna sebelum mengaktifkan.</li>
                            <li>Periksa duplikasi username sebelum melakukan aktivasi massal.</li>
                            <li>Gunakan fitur reset login jika panitia terblokir karena percobaan login salah.</li>
                            <li>Fitur <b>Generate dari Guru</b> akan membuat akun panitia baru dengan jabatan Pengawas
                                untuk guru yang dipilih.</li>
                        </ul>
                    </div>

                    <div class="table-responsive" id="table-panitia"></div>
                </div>
                <div class="overlay d-none" id="loading">
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                    </button>
                </div>
            </div>
        </div>
    </section>
    </div>

    <!-- Generate Modal -->
    <?= form_open('generate', array('id' => 'generate')) ?>
    <div class="modal fade" id="generatePanitiaModal" tabindex="-1" role="dialog"
        aria-labelledby="generatePanitiaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generatePanitiaModalLabel">Generate User Panitia dari Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Fitur ini akan membuat akun Panitia (Jabatan: Pengawas) untuk guru yang dipilih.<br>
                        Username: p_[NIP]<br>
                        Password: 123456
                    </div>
                    <div class="mb-3">
                        <label>Pilih Guru</label>
                        <select class="select2 form-control" multiple="multiple" name="gurus[]"
                            data-placeholder="Pilih Guru" style="width: 100%;" required>
                            <?php foreach ($gurus as $id => $nama): ?>
                                <?php if ($id != '0'): ?>
                                    <option value="<?= $id ?>"><?= $nama ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-magic me-1"></i> Generate
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <?= form_open('create', array('id' => 'create')) ?>
    <div class="modal fade" id="createPanitiaModal" tabindex="-1" role="dialog"
        aria-labelledby="createPanitiaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPanitiaModalLabel">Tambah Panitia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <?= form_label('Nama Panitia:', 'nama_panitia', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('nama_panitia') ?>
                            <?= form_input(['name' => 'nama_panitia', 'class' => 'form-control', 'required' => 'required']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('NIP:', 'nip', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('nip') ?>
                            <?= form_input(['name' => 'nip', 'class' => 'form-control', 'type' => 'number']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Jabatan:', 'jabatan', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('jabatan') ?>
                            <?= form_dropdown('jabatan', ['' => 'Pilih Jabatan', 'Panitia' => 'Panitia', 'Pengawas' => 'Pengawas'], '', ['class' => 'form-control', 'id' => 'create-jabatan']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row d-none" id="create-ruang-group">
                        <?= form_label('Ruang:', 'ruang', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?php
                            $ruang_options = ['' => 'Pilih Ruang'] + $ruangs;
                            echo form_dropdown('ruang', $ruang_options, '', ['class' => 'form-control']);
                            ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Username:', 'username', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('username') ?>
                            <?= form_input(['name' => 'username', 'class' => 'form-control', 'required' => 'required', 'minlength' => '4', 'maxlength' => '50']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Password:', 'password', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('password') ?>
                            <?= form_password(['name' => 'password', 'class' => 'form-control', 'required' => 'required', 'minlength' => '5', 'maxlength' => '50']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Email:', 'email', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('email') ?>
                            <?= form_input(['name' => 'email', 'class' => 'form-control', 'type' => 'email']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('No HP:', 'no_hp', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('no_hp') ?>
                            <?= form_input(['name' => 'no_hp', 'class' => 'form-control']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Instansi/Sekolah Asal:', 'instansi', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('instansi') ?>
                            <?= form_input(['name' => 'instansi', 'class' => 'form-control', 'placeholder' => 'Asal Sekolah/Instansi']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Foto Profil:', 'foto', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('foto') ?>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maks: 2MB</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-floppy me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <!-- Edit Modal -->
    <?= form_open('edit', array('id' => 'edit')) ?>
    <div class="modal fade" id="editPanitiaModal" tabindex="-1" role="dialog" aria-labelledby="editPanitiaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPanitiaModalLabel">Edit Panitia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_panitia">
                    <div class="mb-3 row">
                        <?= form_label('Nama Panitia:', 'nama_panitia', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('nama_panitia') ?>
                            <?= form_input(['name' => 'nama_panitia', 'class' => 'form-control', 'required' => 'required']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('NIP:', 'nip', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('nip') ?>
                            <?= form_input(['name' => 'nip', 'class' => 'form-control', 'type' => 'number']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Jabatan:', 'jabatan', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('jabatan') ?>
                            <?= form_dropdown('jabatan', ['' => 'Pilih Jabatan', 'Panitia' => 'Panitia', 'Pengawas' => 'Pengawas'], '', ['class' => 'form-control', 'id' => 'edit-jabatan']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row d-none" id="edit-ruang-group">
                        <?= form_label('Ruang:', 'ruang', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?php
                            // Reuse options from create
                            $ruang_options = isset($ruang_options) ? $ruang_options : (['' => 'Pilih Ruang'] + $ruangs);
                            echo form_dropdown('ruang', $ruang_options, '', ['class' => 'form-control']);
                            ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Username:', 'username', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('username') ?>
                            <?= form_input(['name' => 'username', 'class' => 'form-control', 'required' => 'required', 'minlength' => '4', 'maxlength' => '50']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Password:', 'password', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('password') ?>
                            <?= form_password(['name' => 'password', 'class' => 'form-control', 'placeholder' => 'Kosongkan jika tidak ingin mengubah password']) ?>
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Email:', 'email', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('email') ?>
                            <?= form_input(['name' => 'email', 'class' => 'form-control', 'type' => 'email']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('No HP:', 'no_hp', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('no_hp') ?>
                            <?= form_input(['name' => 'no_hp', 'class' => 'form-control']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Instansi/Sekolah Asal:', 'instansi', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('instansi') ?>
                            <?= form_input(['name' => 'instansi', 'class' => 'form-control', 'placeholder' => 'Asal Sekolah/Instansi']) ?>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <?= form_label('Foto Profil:', 'foto', ['class' => 'col-md-4 col-form-label']) ?>
                        <div class="col-md-7">
                            <?= form_error('foto') ?>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maks: 2MB</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white px-4">
                        <i class="bi bi-floppy me-1"></i> Update
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>
    <?= form_open('', ['id' => 'up']) ?>
    <?= form_close(); ?>

    <script src="<?= base_url() ?>assets/app/js/ajax_request.js" type="text/javascript"></script>
    <script>
        const user_id = '<?= $user->id ?>';

        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
            ajaxcsrf();

            $('#edit').on('submit', async function (e) {
                e.preventDefault();
                let formData = new FormData($(this)[0]);

                // Handle file upload
                const fileInput = $(this).find('input[name="foto"]')[0];
                if (fileInput.files.length > 0) {
                    formData.append('foto', fileInput.files[0]);
                }

                await ajaxPost(base_url + 'userpanitia/edit', formData, function (error, response) {
                    $('#editPanitiaModal').modal('hide');
                    if (error) {
                        Swal.fire('Error', error, 'error');
                    } else {
                        if (response.status) {
                            Swal.fire('Sukses', response.msg, 'success').then(() => {
                                getDataPanitia();
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

            // Toggle Ruang inputs
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

            $('#create').on('submit', async function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let formData = new FormData($(this)[0]);

                // Handle file upload
                const fileInput = $(this).find('input[name="foto"]')[0];
                if (fileInput.files.length > 0) {
                    formData.append('foto', fileInput.files[0]);
                }

                await ajaxPost(base_url + 'userpanitia/create', formData, function (error, response) {
                    $('#createPanitiaModal').modal('hide').data('bs.modal', null);
                    $('#createPanitiaModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });

                    if (error) {
                        Swal.fire('Error', error, 'error');
                    } else {
                        if (response.status) {
                            Swal.fire('Sukses', response.msg || 'Panitia berhasil ditambahkan', 'success').then(() => {
                                getDataPanitia();
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

            $('#generate').on('submit', async function (e) {
                e.preventDefault();
                let formData = new FormData($(this)[0]);

                $('#loading').removeClass('d-none');
                await ajaxPost(base_url + 'userpanitia/generate', formData, function (error, response) {
                    $('#loading').addClass('d-none');
                    $('#generatePanitiaModal').modal('hide');
                    if (error) {
                        Swal.fire('Error', error.message, 'error');
                    } else {
                        Swal.fire(response.status ? 'Sukses' : 'Info', response.msg, response.status ? 'success' : 'info').then(() => {
                            getDataPanitia();
                        });
                    }
                });
            });

            $(".btn-action").on("click", function () {
                const action = $(this).data("action");
                Swal.fire({
                    title: action === 'aktifkan' ? "Aktifkan Semua Panitia" : "Nonaktifkan Semua Panitia",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Lanjutkan"
                }).then(async result => {
                    if (result.value) {
                        $('#loading').removeClass('d-none');
                        let formActivate = new FormData($('#up')[0]);
                        formActivate.append('is_active', action);
                        await ajaxPost(base_url + "userpanitia/aktifkansemua", formActivate, function (error, response) {
                            $('#loading').addClass('d-none');
                            if (error) {
                                Swal.fire('Error', error.message, 'error');
                            } else {
                                Swal.fire(response.status ? 'Sukses' : 'Error', response.msg, response.status ? 'success' : 'error');
                                if (response.status) {
                                    getDataPanitia();
                                }
                            }
                        });
                    }
                });
            });

            $("#table-panitia").on("click", ".btn-reset", function () {
                const username = $(this).data("username");
                const nama = $(this).data("nama");
                $('#loading').removeClass('d-none');
                let form = new FormData($('#up')[0]);
                form.append('username', username);
                form.append('nama', nama);
                ajaxPost('userpanitia/reset_login', form, function (error, response) {
                    $('#loading').addClass('d-none');
                    if (error) {
                        Swal.fire('Error', error.message, 'error');
                    } else {
                        Swal.fire(response.status ? 'Sukses' : 'Error', response.msg, response.status ? 'success' : 'error');
                        if (response.status) {
                            getDataPanitia();
                        }
                    }
                });
            });

            getDataPanitia();
        });

        function getDataPanitia() {
            $('#loading').removeClass('d-none');
            ajaxGet(base_url + 'userpanitia/data', function (error, response) {
                $('#loading').addClass('d-none');
                if (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Terjadi kesalahan saat mengambil data', 'error');
                } else {
                    populateTable(response);
                }
            });
        }

        function populateTable(data) {
            const tableContainer = document.getElementById("table-panitia");
            tableContainer.innerHTML = '';

            if (!data || data.length === 0) {
                tableContainer.innerHTML = '<div class="alert alert-warning">Tidak ada data panitia</div>';
                $('#loading').addClass('d-none');
                return;
            }

            const table = document.createElement("table");
            table.id = "users";
            table.className = "w-100 table table-sm table-striped table-bordered table-hover dataTable";

            const headers = ["No.", "Nama", "Username", "Password", "Jabatan", "Reset Login", "Status", "Aksi"];
            const thead = document.createElement("thead");
            thead.className = "";
            const headerRow = document.createElement("tr");
            headers.forEach(text => {
                const th = document.createElement("th");
                th.className = "text-center align-middle border-0 py-3";
                th.textContent = text;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            const tbody = document.createElement("tbody");

            let total = data.length;
            let totalAktif = 0;
            let totalReset = 0;

            data.forEach((panitia, index) => {
                if (panitia.active === "1") totalAktif++;
                if (panitia.reset !== null && panitia.reset !== "0") totalReset++;

                const row = document.createElement("tr");

                row.appendChild(createCell(index + 1, 'text-center align-middle'));

                const cellName = document.createElement("td");
                cellName.className = "align-middle";
                cellName.innerHTML = `<div class="d-flex align-items-center">
                <img class="avatar rounded-circle border shadow-sm me-3" 
                     src="${panitia.foto || base_url + 'assets/img/user.png'}" 
                     style="width: 45px; height: 45px; object-fit: cover;" 
                     alt="Panitia" 
                     onerror="this.src=base_url+'assets/img/user.png'">
                <div class="overflow-hidden">
                    <div class="fw-bold text-dark text-truncate" style="max-width: 200px;">${panitia.nama_panitia ?? '-'}</div>
                    <small class="text-muted font-monospace">${panitia.nip ?? '-'}</small>
                </div>
            </div>`;
                row.appendChild(cellName);

                row.appendChild(createCell(panitia.username ?? '-', 'text-center align-middle'));
                row.appendChild(createCell(panitia.password ?? '-', 'text-center align-middle text-monospace'));
                row.appendChild(createCell(panitia.jabatan ?? '-', 'text-center align-middle'));

                // Reset Login UI
                const cellReset = document.createElement("td");
                cellReset.className = "text-center align-middle";
                const btnReset = document.createElement("button");
                const needReset = panitia.reset !== null && panitia.reset !== "0";
                btnReset.type = 'button';
                btnReset.className = `btn btn-sm ${needReset ? 'btn-danger shadow-sm' : 'btn-primary border text-muted'}`;
                btnReset.dataset.username = panitia.username;
                btnReset.dataset.nama = panitia.nama_panitia;
                btnReset.setAttribute('title', 'Reset Login');
                btnReset.innerHTML = '<i class="bi bi-arrow-repeat"></i>' + (needReset ? '<span class="ms-1 d-none d-sm-inline">Reset</span>' : '');
                if (!needReset) {
                    btnReset.setAttribute('disabled', 'disabled');
                }
                cellReset.appendChild(btnReset);
                row.appendChild(cellReset);

                // Status Badge
                const isActive = panitia.active === "1";
                const cellStatus = document.createElement("td");
                cellStatus.className = "text-center align-middle";
                cellStatus.innerHTML = `<span class="badge ${isActive ? 'bg-success' : 'bg-danger'}">${isActive ? 'Aktif' : 'Nonaktif'}</span>`;
                row.appendChild(cellStatus);

                // Action UI
                const cellAction = document.createElement("td");
                cellAction.className = 'text-center align-middle';

                const actionGroup = document.createElement('div');
                actionGroup.className = 'd-flex justify-content-center gap-1';

                // Edit
                const btnEdit = document.createElement('button');
                btnEdit.type = 'button';
                btnEdit.className = 'btn btn-sm btn-info shadow-sm';
                btnEdit.innerHTML = '<i class="bi bi-pencil"></i>';
                btnEdit.onclick = () => editPanitia(panitia);
                actionGroup.appendChild(btnEdit);

                // Toggle Active
                const btnAction = document.createElement('button');
                btnAction.type = 'button';
                btnAction.className = `btn btn-sm ${isActive ? 'btn-warning' : 'btn-success'} shadow-sm`;
                btnAction.innerHTML = `<i class="bi ${isActive ? 'bi-slash-circle' : 'bi-person-plus-fill'}"></i>`;
                btnAction.title = isActive ? 'Nonaktifkan' : 'Aktifkan';
                btnAction.onclick = () => toggleActive(panitia, isActive ? 'nonaktifkan' : 'aktifkan');
                actionGroup.appendChild(btnAction);

                // Delete
                const btnDelete = document.createElement('button');
                btnDelete.type = 'button';
                btnDelete.className = 'btn btn-sm btn-danger shadow-sm';
                btnDelete.innerHTML = '<i class="bi bi-trash"></i>';
                btnDelete.onclick = () => deletePanitia(panitia);
                actionGroup.appendChild(btnDelete);

                cellAction.appendChild(actionGroup);
                row.appendChild(cellAction);

                tbody.appendChild(row);
            });

            table.appendChild(tbody);
            tableContainer.appendChild(table);
            $("#users").DataTable();
            $('#loading').addClass('d-none');

            document.getElementById('badge-total-panitia').innerText = total;
            document.getElementById('badge-total-aktif').innerText = totalAktif;
            document.getElementById('badge-total-nonaktif').innerText = total - totalAktif;
            document.getElementById('badge-total-reset').innerText = totalReset;
        }

        function toggleActive(panitia, action) {
            $('#loading').removeClass('d-none');
            let form = new FormData($('#up')[0]);
            if (panitia.id) form.append('id', panitia.id);
            form.append('id_panitia', panitia.id_panitia);
            form.append('nama', panitia.nama_panitia);
            form.append('is_active', action);
            ajaxPost('userpanitia/activate', form, function (error, response) {
                $('#loading').addClass('d-none');
                if (error) {
                    Swal.fire('Error', error.message, 'error');
                } else {
                    Swal.fire(response.status ? 'Sukses' : 'Error', response.msg, response.status ? 'success' : 'error');
                    if (response.status) {
                        getDataPanitia();
                    }
                }
            });
        }

        function deletePanitia(panitia) {
            Swal.fire({
                title: 'Anda yakin?',
                text: `Data panitia "${panitia.nama_panitia}" akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!'
            }).then(async (result) => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    let formData = new FormData($('#up')[0]);
                    formData.append('id_panitia', panitia.id_panitia);
                    formData.append('nama', panitia.nama_panitia);

                    await ajaxPost(base_url + 'userpanitia/delete', formData, function (error, response) {
                        $('#loading').addClass('d-none');
                        if (error) {
                            Swal.fire('Error', error.message, 'error');
                        } else {
                            Swal.fire(response.status ? 'Sukses' : 'Error', response.msg, response.status ? 'success' : 'error');
                            if (response.status) {
                                getDataPanitia();
                            }
                        }
                    });
                }
            });
        }

        function editPanitia(panitia) {
            // Populate modal fields with existing data
            $('#editPanitiaModal input[name="nama_panitia"]').val(panitia.nama_panitia || '');
            $('#editPanitiaModal input[name="nip"]').val(panitia.nip || '');
            $('#editPanitiaModal select[name="jabatan"]').val(panitia.jabatan || '');
            $('#editPanitiaModal input[name="username"]').val(panitia.username || '');
            $('#editPanitiaModal input[name="email"]').val(panitia.email || '');
            $('#editPanitiaModal input[name="no_hp"]').val(panitia.no_hp || '');
            $('#editPanitiaModal input[name="no_hp"]').val(panitia.no_hp || '');
            $('#editPanitiaModal input[name="instansi"]').val(panitia.instansi || '');
            $('#editPanitiaModal input[name="id_panitia"]').val(panitia.id_panitia || '');
            $('#editPanitiaModal select[name="ruang"]').val(panitia.ruang || '');

            // Trigger change to update Ruang visibility
            $('#edit-jabatan').trigger('change');

            // Show modal
            $('#editPanitiaModal').modal('show');
        }

        function createCell(text, classes = '') {
            const td = document.createElement('td');
            td.className = classes;
            td.innerHTML = text;
            return td;
        }
    </script>