<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    <?= esc($judul ?? 'Data Admin') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary"><i class="bi bi-person-badge me-2"></i><?= $subjudul ?></h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-1">
                    <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createAdminModal"
                        class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i> Tambah Admin
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="users" class="w-100 table table-striped table-bordered table-hover dataTable">
                    <thead>
                        <tr>
                            <th width="40" class="align-middle text-center p-0">No.</th>
                            <th class="align-middle">First Name</th>
                            <th class="align-middle">Last Name</th>
                            <th class="align-middle">Username</th>
                            <th class="align-middle">Created On</th>
                            <th class="align-middle">Level</th>
                            <th class="text-center align-middle">Status</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($admins as $num => $admin):
                            ?>
                            <tr>
                                <td class="text-center"><?= $num + 1 ?></td>
                                <td><?= esc($admin->first_name) ?></td>
                                <td><?= esc($admin->last_name) ?></td>
                                <td><?= esc($admin->username) ?></td>
                                <td><span class="badge bg-secondary"><?= esc($admin->created_on) ?></span></td>
                                <td><?= esc($admin->level) ?></td>
                                <td class="text-center">
                                    <?php if ($admin->active == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($admin->id === $user->id): ?>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a class="btn btn-info btn-sm shadow-sm"
                                                href="<?= base_url('useradmin/edit/' . $admin->id) ?>" title="Edit Profile">
                                                <i class="bi bi-gear-fill text-white"></i>
                                            </a>
                                        </div>
                                    <?php else:
                                        if ($user->id == "1"): ?>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a class="btn btn-info btn-sm shadow-sm"
                                                    href="<?= base_url('useradmin/edit/' . $admin->id) ?>" title="Edit">
                                                    <i class="bi bi-pencil text-white"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm shadow-sm"
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

<!-- Modal Create -->
<?= form_open('useradmin/create', array('id' => 'create')) ?>
<div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createModalLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" id="first_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" id="last_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="reset" class="btn btn-warning shadow-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Reset
                </button>
                <button type="submit" class="btn btn-primary shadow-sm px-4">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // ajaxcsrf(); // Assuming handled globally or by csrf token input
        // But if existing js requires it, might need to expose csrf token

        $("#users").DataTable({
            order: [[0, "asc"]],
            language: dt_lang_id,
            pagingType: "full_numbers",
            dom: "<'row mb-2'<'col-sm-12 col-md-6 d-flex align-items-center'l><'col-sm-12 col-md-6 d-flex justify-content-md-end'f>>" +
                 "<'table-responsive'tr>" +
                 "<'d-flex flex-column flex-md-row justify-content-between align-items-center mt-3'ip>"
        });

        function reload_ajax() {
            $("#users").DataTable().ajax.reload();
        }

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
                            text: response.msg, // Changed from response.text to response.msg based on controller
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
                            icon: response.status ? "success" : "error"
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
<?= $this->endSection() ?>