<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $judul ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-header with-border">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </button>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#createAdminModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i> Tambah Admin
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="users" class="w-100 table table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="align-middle">No.</th>
                                    <th class="align-middle">First Name</th>
                                    <th class="align-middle">Last Name</th>
                                    <th class="align-middle">Username</th>
                                    <th class="align-middle">Created On</th>
                                    <th class="align-middle">Level</th>
                                    <th class="text-center align-middle">Status</th>
                                    <th class="text-center align-middle">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($admins as $num => $admin):
                                    ?>
                                    <tr>
                                        <td><?= $num + 1 ?></td>
                                        <td><?= $admin->first_name ?></td>
                                        <td><?= $admin->last_name ?></td>
                                        <td><?= $admin->username ?></td>
                                        <td><span class="badge bg-secondary"><?= $admin->created_on ?></span></td>
                                        <td><?= $admin->level ?></td>
                                        <td>
                                            <?php if ($admin->active == 1): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($admin->id === $user->id): ?>
                                                <div class="d-flex justify-content-center">
                                                    <a class="btn btn-sm btn-primary shadow-sm"
                                                        href="<?= base_url('useradmin/edit/' . $admin->id) ?>"
                                                        title="Edit Profile">
                                                        <i class="bi bi-gear-fill"></i>
                                                    </a>
                                                </div>
                                            <?php else:
                                                if ($user->id == "1"): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a type="button" class="btn btn-sm btn-info shadow-sm"
                                                            href="<?= base_url('useradmin/edit/' . $admin->id) ?>" title="Edit">
                                                            <i class="bi bi-pencil text-white"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger shadow-sm"
                                                            onclick="hapus(<?= $admin->id ?>)" title="Hapus">
                                                            <i class="bi bi-trash text-white"></i>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center"><i class="bi bi-ban text-danger"></i></div>
                                                <?php endif; endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <?= form_open('create', array('id' => 'create')) ?>
    <div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <?php
                    echo '<div class="mb-3 row">';
                    echo form_label('First name:', 'first_name', array('class' => 'col-md-4 col-form-label'));
                    echo form_error('first_name');
                    echo form_input(
                        array('name' => 'first_name', 'class' => 'col-md-7 form-control'),
                        set_value('first_name'),
                        'required'
                    );
                    echo '</div>';
                    echo '<div class="mb-3 row">';
                    echo form_label('Last name:', 'last_name', array('class' => 'col-md-4 col-form-label'));
                    echo form_error('last_name');
                    echo form_input(
                        array('name' => 'last_name', 'class' => 'col-md-7 form-control'),
                        set_value('last_name'),
                        'required'
                    );
                    echo '</div>';
                    /*
                    echo '<div class="mb-3 row">';
                    echo form_label('Email:', 'email', array('class' => 'col-md-4 col-form-label'));
                    echo form_error('email');
                    echo form_input(array('name' => 'email', 'class' => 'col-md-7 form-control'),
                        set_value('email'), 'required');
                    echo '</div>';
                    */
                    echo '<div class="mb-3 row">';
                    echo form_label('Username:', 'username', array('class' => 'col-md-4 col-form-label'));
                    echo form_error('username');
                    echo form_input(
                        array('name' => 'username', 'class' => 'col-md-7 form-control'),
                        set_value('username'),
                        'required'
                    );
                    echo '</div>';
                    echo '<div class="mb-3 row">';
                    echo form_label('Password:', 'password', array('class' => 'col-md-4 col-form-label'));
                    echo form_error('password');
                    echo form_password(array('name' => 'password', 'class' => 'col-md-7 form-control'), '', 'required');
                    echo '</div>';
                    echo '<div class="mb-3 row">';
                    echo form_label('Confirm password:', 'confirm_password', array('class' => 'col-md-4 col-form-label'));
                    echo form_error('confirm_password');
                    echo form_password(array('name' => 'confirm_password', 'class' => 'col-md-7 form-control'), '', 'required');
                    echo '</div>';
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <script src="<?= base_url() ?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            ajaxcsrf();
            $("#users").DataTable()

            $('#create').on('submit', async function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                console.log("data:", $(this).serialize());

                let formData = new FormData($(this)[0])
                await ajaxPost(base_url + "useradmin/create", formData, function (error, response) {
                    $('#createAdminModal').modal('hide').data('bs.modal', null);
                    $('#createAdminModal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });
                    //showSuccessToast('Data berhasil disimpan.');
                    //window.location.reload()

                    if (error) {
                        console.log('error', error);
                        Swal.fire({
                            title: "Error",
                            text: error,
                            icon: "error"
                        });
                    } else {
                        console.log('response', response);
                        if (response.status) {
                            Swal.fire({
                                title: "Sukses",
                                text: response.text,
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            var html = '<ul>';
                            if (response.errors.username != null && response.errors.username !== "") {
                                html += '<li>' + response.errors.username + '</li>';
                            }
                            if (response.errors.first_name != null && response.errors.first_name !== "") {
                                html += '<li>' + response.errors.first_name + '</li>';
                            }
                            if (response.errors.password != null && response.errors.password !== "") {
                                html += '<li>' + response.errors.password + '</li>';
                            }
                            if (response.errors.confirm_password != null && response.errors.confirm_password !== "") {
                                html += '<li>' + response.errors.confirm_password + '</li>';
                            }
                            html += '</ul>';
                            Swal.fire({
                                title: "ERROR",
                                html: html,
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    }
                });
            });
        });

        function hapus(id) {
            Swal.fire({
                title: "Anda yakin?",
                text: "Data akan dihapus.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(async result => {
                if (result.value) {
                    ajaxGet(base_url + "useradmin/delete/" + id, function (error, response) {
                        if (error) {
                            console.log('error', error);
                            Swal.fire({
                                title: "Gagal",
                                text: "User gagal dihapus",
                                icon: "error"
                            });
                        } else {
                            console.log('response', response);
                            Swal.fire({
                                title: response.status ? "Berhasil" : "Gagal",
                                text: response.status ? "User berhasil dihapus" : "User gagal dihapus",
                                type: response.status ? "success" : "error"
                            }).then(result => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        }
                    });
                }
            });
        }
    </script>