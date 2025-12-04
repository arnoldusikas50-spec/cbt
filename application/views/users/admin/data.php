<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-header with-border">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools">
                        <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button type="button" data-toggle="modal" data-target="#createAdminModal"
                                class="btn btn-sm btn-primary"><i
                                    class="fas fa-plus"></i><span
                                    class="d-none d-sm-inline-block ml-1">Tambah Admin</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="users" class="w-100 table table-striped table-bordered table-hover">
                            <thead class="alert-default-info">
                            <tr>
                                <th>No.</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Username</th>
                                <th>Created On</th>
                                <th>Level</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($admins as $num=>$admin) :
                                ?>
                                <tr>
                                    <td><?= $num +1 ?></td>
                                    <td><?= $admin->first_name ?></td>
                                    <td><?= $admin->last_name ?></td>
                                    <td><?= $admin->username ?></td>
                                    <td><span class="badge"><?= $admin->created_on ?></span></td>
                                    <td><?= $admin->level ?></td>
                                    <td>
                                        <?php if ($admin->active == 1) : ?>
                                            <span class="badge bg-green">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-red">Not Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($admin->id === $user->id) : ?>
                                            <div class="text-center">
                                                <a class="btn btn-xs bg-primary" href="<?=base_url('useradmin/edit/'. $admin->id)?>">
                                                    <i class="fa fa-cog fa-spin"></i>
                                                </a>
                                            </div>
                                        <?php else:
                                            if ($user->id == "1") : ?>
                                                <div class="text-center">
                                                    <a type="button" class="btn btn-xs btn-warning" href="<?=base_url('useradmin/edit/'.$admin->id)?>">
                                                        <i class="fa fa-pencil-alt text-white"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-xs btn-danger" onclick="hapus(<?=$admin->id?>)">
                                                        <i class="fa fa-trash text-white"></i>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center"><i class="fa fa-ban text-danger"></i></div>
                                            <?php endif; endif;?>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                echo '<div class="form-group row">';
                echo form_label('First name:', 'first_name', array('class' => 'col-md-4 col-form-label'));
                echo form_error('first_name');
                echo form_input(array('name' => 'first_name', 'class' => 'col-md-7 form-control'),
                    set_value('first_name'), 'required');
                echo '</div>';
                echo '<div class="form-group row">';
                echo form_label('Last name:', 'last_name', array('class' => 'col-md-4 col-form-label'));
                echo form_error('last_name');
                echo form_input(array('name' => 'last_name', 'class' => 'col-md-7 form-control'),
                    set_value('last_name'), 'required');
                echo '</div>';
                /*
                echo '<div class="form-group row">';
                echo form_label('Email:', 'email', array('class' => 'col-md-4 col-form-label'));
                echo form_error('email');
                echo form_input(array('name' => 'email', 'class' => 'col-md-7 form-control'),
                    set_value('email'), 'required');
                echo '</div>';
                */
                echo '<div class="form-group row">';
                echo form_label('Username:', 'username', array('class' => 'col-md-4 col-form-label'));
                echo form_error('username');
                echo form_input(array('name' => 'username', 'class' => 'col-md-7 form-control'),
                    set_value('username'), 'required');
                echo '</div>';
                echo '<div class="form-group row">';
                echo form_label('Password:', 'password', array('class' => 'col-md-4 col-form-label'));
                echo form_error('password');
                echo form_password(array('name' => 'password', 'class' => 'col-md-7 form-control'), '', 'required');
                echo '</div>';
                echo '<div class="form-group row">';
                echo form_label('Confirm password:', 'confirm_password', array('class' => 'col-md-4 col-form-label'));
                echo form_error('confirm_password');
                echo form_password(array('name' => 'confirm_password', 'class' => 'col-md-7 form-control'), '', 'required');
                echo '</div>';
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<script src="<?=base_url()?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
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
                    swal.fire({
                        title: "Error",
                        text: error,
                        icon: "error"
                    });
                } else {
                    console.log('response', response);
                    if (response.status) {
                        swal.fire({
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
                        swal.fire({
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
        swal.fire({
            title: "Anda yakin?",
            text: "Data akan dihapus.",
            type: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
        }).then(async result => {
            if (result.value) {
                ajaxGet(base_url + "useradmin/delete/" + id, function(error, response) {
                    if (error) {
                        console.log('error', error);
                        swal.fire({
                            title: "Gagal",
                            text: "User gagal dihapus",
                            type: "error"
                        });
                    } else {
                        console.log('response', response);
                        swal.fire({
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