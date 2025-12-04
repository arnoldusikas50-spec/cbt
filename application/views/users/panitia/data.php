<div class="content-wrapper bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-header with-border d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title">Master <?= $subjudul ?></h3>
                    <div class="card-tools mt-3 mt-md-0 ml-auto">
                        <button type="button" data-toggle="modal" data-target="#createPanitiaModal"
                                class="btn btn-sm btn-primary"><i
                                    class="fas fa-plus"></i><span
                                    class="d-none d-sm-inline-block ml-1">Tambah</span>
                        </button>
                        <button type="button" onclick="getDataPanitia()" class="btn btn-sm btn-warning">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button type="button" class="btn btn-action btn-success btn-sm" data-action="aktifkan" data-toggle="tooltip" title="Aktifkan">
                            <i class="fa fa-users m-1"></i><span class="d-none d-sm-inline-block ml-1">Aktifkan Semua</span>
                        </button>
                        <button type="button" class="btn btn-action btn-danger btn-sm" data-action="nonaktifkan" data-toggle="tooltip" title="Nonaktifkan">
                            <i class="fa fa-ban m-1"></i><span class="d-none d-sm-inline-block ml-1">Nonaktifkan Semua</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan User Panitia</h5>
                                <div class="text-muted">Kelola akun panitia penyelenggara</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total: <span id="badge-total-panitia">0</span></span>
                                <span class="badge badge-success px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Aktif: <span id="badge-total-aktif">0</span></span>
                                <span class="badge badge-warning px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Nonaktif: <span id="badge-total-nonaktif">0</span></span>
                                <span class="badge badge-info px-3 py-2 text-sm">Perlu Reset Login: <span id="badge-total-reset">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan</h5>
                        <ul class="pl-3 mb-0">
                            <li>Pastikan data panitia terhubung dengan akun pengguna sebelum mengaktifkan.</li>
                            <li>Periksa duplikasi username sebelum melakukan aktivasi massal.</li>
                            <li>Gunakan fitur reset login jika panitia terblokir karena percobaan login salah.</li>
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
<?= form_open('create', array('id' => 'create')) ?>
<div class="modal fade" id="createPanitiaModal" tabindex="-1" role="dialog" aria-labelledby="createPanitiaModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPanitiaModalLabel">Tambah Panitia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <?= form_label('Nama Panitia:', 'nama_panitia', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('nama_panitia') ?>
                        <?= form_input(['name' => 'nama_panitia', 'class' => 'form-control', 'required' => 'required']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Jabatan:', 'jabatan', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('jabatan') ?>
                        <?= form_input(['name' => 'jabatan', 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Username:', 'username', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('username') ?>
                        <?= form_input(['name' => 'username', 'class' => 'form-control', 'required' => 'required', 'minlength' => '4', 'maxlength' => '50']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Password:', 'password', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('password') ?>
                        <?= form_password(['name' => 'password', 'class' => 'form-control', 'required' => 'required', 'minlength' => '5', 'maxlength' => '50']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Email:', 'email', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('email') ?>
                        <?= form_input(['name' => 'email', 'class' => 'form-control', 'type' => 'email']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('No HP:', 'no_hp', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('no_hp') ?>
                        <?= form_input(['name' => 'no_hp', 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Foto Profil:', 'foto', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('foto') ?>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maks: 2MB</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_panitia">
                <div class="form-group row">
                    <?= form_label('Nama Panitia:', 'nama_panitia', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('nama_panitia') ?>
                        <?= form_input(['name' => 'nama_panitia', 'class' => 'form-control', 'required' => 'required']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Jabatan:', 'jabatan', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('jabatan') ?>
                        <?= form_input(['name' => 'jabatan', 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Username:', 'username', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('username') ?>
                        <?= form_input(['name' => 'username', 'class' => 'form-control', 'required' => 'required', 'minlength' => '4', 'maxlength' => '50']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Password:', 'password', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('password') ?>
                        <?= form_password(['name' => 'password', 'class' => 'form-control', 'placeholder' => 'Kosongkan jika tidak ingin mengubah password']) ?>
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Email:', 'email', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('email') ?>
                        <?= form_input(['name' => 'email', 'class' => 'form-control', 'type' => 'email']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('No HP:', 'no_hp', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('no_hp') ?>
                        <?= form_input(['name' => 'no_hp', 'class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="form-group row">
                    <?= form_label('Foto Profil:', 'foto', ['class' => 'col-md-4 col-form-label']) ?>
                    <div class="col-md-7">
                        <?= form_error('foto') ?>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, JPEG. Maks: 2MB</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
<?= form_open('', ['id' => 'up']) ?>
<?= form_close(); ?>

<script src="<?=base_url()?>assets/app/js/ajax_request.js" type="text/javascript"></script>
<script>
    const user_id = '<?=$user->id?>';

    $(document).ready(function() {
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

        $(".btn-action").on("click", function() {
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
                    await ajaxPost(base_url + "userpanitia/aktifkansemua", formActivate, function(error, response) {
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

        $("#table-panitia").on("click", ".btn-reset", function() {
            const username = $(this).data("username");
            const nama = $(this).data("nama");
            $('#loading').removeClass('d-none');
            let form = new FormData($('#up')[0]);
            form.append('username', username);
            form.append('nama', nama);
            ajaxPost('userpanitia/reset_login', form, function(error, response) {
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
        ajaxGet(base_url + 'userpanitia/data', function(error, response) {
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
        table.className = "w-100 table table-sm table-striped table-bordered table-hover";

        const headers = ["No.", "Nama", "Username", "Password", "Jabatan", "Reset Login", "Status", "Aksi"];
        const thead = document.createElement("thead");
        const headerRow = document.createElement("tr");
        headers.forEach(text => {
            const th = document.createElement("th");
            th.className = "text-center align-middle alert-default-primary";
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
            cellName.innerHTML = `<div class="media d-flex h-100">
                <img class="avatar img-circle" src="${panitia.foto}" width="40" height="40" alt="User Image">
                <div class="media-body ml-2 align-self-center">
                    <div class="font-weight-bold">${panitia.nama_panitia ?? '-'}</div>
                    <small class="text-muted">${panitia.email ?? '-'}</small>
                </div>
            </div>`;
            row.appendChild(cellName);

            row.appendChild(createCell(panitia.username ?? '-', 'text-center align-middle'));
            row.appendChild(createCell(panitia.password ?? '-', 'text-center align-middle text-monospace'));
            row.appendChild(createCell(panitia.jabatan ?? '-', 'text-center align-middle'));

            const cellReset = document.createElement("td");
            cellReset.className = "text-center align-middle";
            const btnReset = document.createElement("button");
            const needReset = panitia.reset !== null && panitia.reset !== "0";
            btnReset.type = 'button';
            btnReset.className = `btn btn-reset btn-default btn-xs text-red ${!needReset ? 'btn-disabled' : ''}`;
            btnReset.dataset.username = panitia.username;
            btnReset.dataset.nama = panitia.nama_panitia;
            btnReset.setAttribute('title', 'Reset Login');
            btnReset.innerHTML = '<i class="fa fa-sync m-1"></i>';
            if (!needReset) {
                btnReset.setAttribute('disabled', 'disabled');
            }
            cellReset.appendChild(btnReset);
            row.appendChild(cellReset);

            const isActive = panitia.active === "1";
            const cellStatus = document.createElement("td");
            cellStatus.className = `text-bold text-center align-middle ${isActive ? 'text-success' : 'text-danger'}`;
            cellStatus.textContent = isActive ? 'Aktif' : 'Nonaktif';
            row.appendChild(cellStatus);

            const cellAction = document.createElement("td");
            cellAction.className = 'text-center align-middle';
            
            // Edit button
            const btnEdit = document.createElement('button');
            btnEdit.type = 'button';
            btnEdit.className = 'btn btn-sm btn-info mr-1';
            btnEdit.innerHTML = '<i class="fa fa-edit"></i>';
            btnEdit.onclick = () => editPanitia(panitia);
            cellAction.appendChild(btnEdit);
            
            // Delete button
            const btnDelete = document.createElement('button');
            btnDelete.type = 'button';
            btnDelete.className = 'btn btn-sm btn-danger mr-1';
            btnDelete.innerHTML = '<i class="fa fa-trash"></i>';
            btnDelete.onclick = () => deletePanitia(panitia);
            cellAction.appendChild(btnDelete);
            
            // Activate/Deactivate button
            const btnAction = document.createElement('button');
            btnAction.type = 'button';
            btnAction.className = `btn btn-sm ${isActive ? 'btn-danger' : 'btn-success'}`;
            btnAction.innerHTML = `<i class="fa ${isActive ? 'fa-ban' : 'fa-user-plus'} mr-1"></i>${isActive ? 'Nonaktifkan' : 'Aktifkan'}`;
            btnAction.onclick = () => toggleActive(panitia, isActive ? 'nonaktifkan' : 'aktifkan');
            cellAction.appendChild(btnAction);
            
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
        ajaxPost('userpanitia/activate', form, function(error, response) {
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
                
                await ajaxPost(base_url + 'userpanitia/delete', formData, function(error, response) {
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
        $('#editPanitiaModal input[name="jabatan"]').val(panitia.jabatan || '');
        $('#editPanitiaModal input[name="username"]').val(panitia.username || '');
        $('#editPanitiaModal input[name="email"]').val(panitia.email || '');
        $('#editPanitiaModal input[name="no_hp"]').val(panitia.no_hp || '');
        $('#editPanitiaModal input[name="id_panitia"]').val(panitia.id_panitia || '');
        
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
