<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
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
        <div class="row">
            <?php if ($is_admin): ?>
                <div class="col-md-6">
                    <div class="card card-info my-shadow">
                        <div class="card-header">
                            <h3 class="card-title">
                                <?= $subjudul ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if ($user->id === $users->id || $is_admin): ?>
                                    <div class="col-5">
                                        <?= form_open_multipart('', array('id' => 'set-foto'), ['id_user' => $users->id]) ?>
                                        <div class="mb-3 pb-2">
                                            <label for="logo-kanan">Foto</label>
                                            <input type="file" id="foto" name="foto" class="dropify"
                                                data-max-file-size-preview="2M" data-allowed-file-extensions="jpg jpeg png gif"
                                                data-default-file="<?= ($users->foto != null && file_exists($users->foto)) ? base_url() . $users->foto : base_url('assets/img/siswa_sm.png') ?>" />
                                        </div>
                                        <?= form_close() ?>
                                    </div>
                                <?php endif; ?>
                                <div class="col-7">
                                    <?= form_open('useradmin/edit_info', array('id' => 'user_info'), array('id' => $users->id)) ?>
                                    <div class="mb-3">
                                        <label for="first_name">First Name</label>
                                        <input type="text" name="first_name" class="form-control"
                                            value="<?= $users->first_name ?>">
                                        <small class="help-block"></small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" name="last_name" class="form-control"
                                            value="<?= $users->last_name ?>">
                                        <small class="help-block"></small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= $users->email ?>">
                                        <small class="help-block"></small>
                                    </div>
                                    <div class="mb-3">
                                        <label>Jabatan</label>
                                        <input type="text" name="jabatan" placeholder="Jabatan" class="form-control"
                                            value="<?= $users->jabatan ?>">
                                    </div>
                                    <div class="float-end">
                                        <button type="reset" class="btn btn-warning ms-2"><i
                                                class="bi bi-arrow-counterclockwise"></i> Reset</button>
                                        <button type="submit" id="btn-info" class="btn btn-primary ms-2"><i
                                                class="bi bi-save me-1"></i> Simpan</button>
                                    </div>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($user->id === $users->id || $is_admin): ?>
                <div class="col-md-6">
                    <?= form_open('useradmin/change_password', array('id' => 'change_password'), array('id' => $users->id, 'username' => $users->username)) ?>
                    <div class="card card-warning my-shadow">
                        <div class="card-header">
                            <h3 class="card-title">Edit Data Login</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="username">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= $users->username ?>">
                                <small class="help-block"></small>
                            </div>
                            <div class="mb-3">
                                <label for="old">Password Lama</label>
                                <input type="password" placeholder="Password Lama" name="old" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="new">Password Baru</label>
                                <input type="password" placeholder="Password Baru" name="new" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="new_confirm">Konfirmasi Password</label>
                                <input type="password" placeholder="Konfirmasi Password Baru" name="new_confirm"
                                    class="form-control">
                            </div>
                            <div class="float-end">
                                <button type="reset" class="btn btn-warning ms-2"><i
                                        class="bi bi-arrow-counterclockwise"></i>
                                    Reset</button>
                                <button type="submit" id="btn-pass" class="btn btn-primary ms-2"><i
                                        class="bi bi-key me-1"></i> Ganti Password</button>
                            </div>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
<script src="<?= base_url('assets/plugins/dropify/js/dropify.min.js') ?>"></script>

<script>
    const idUser = '<?= $users->id ?>';
    const curUser = '<?= $user->id ?>';
    let fprofil = '<?= $users->foto != null ? base_url() . $users->foto : "" ?>';

    $(document).ready(function () {
        // Initialise dropify only when the plugin is actually loaded, so the
        // handlers below (foto upload, edit info, change password) keep working
        // even if the asset ever fails to load.
        var drEvent = $.fn.dropify ? $('.dropify').dropify({
            messages: {
                'default': 'Seret logo kesini atau klik',
                'replace': 'Seret atau klik<br>untuk mengganti logo',
                'remove': 'Hapus',
                'error': 'Ooops, ada kesalahan!!.'
            },
            error: {
                'fileSize': 'The file size is too big ({{ value }} max).',
                'minWidth': 'The image width is too small ({{ value }}}px min).',
                'maxWidth': 'The image width is too big ({{ value }}}px max).',
                'minHeight': 'The image height is too small ({{ value }}}px min).',
                'maxHeight': 'The image height is too big ({{ value }}px max).',
                'imageFormat': 'The image format is not allowed ({{ value }} only).'
            }
        }) : $();

        drEvent.on('dropify.beforeClear', function (event, element) {
            //return confirm("Hapus logo \"" + element.file.name + "\" ?");
        });

        drEvent.on('dropify.afterClear', function (event, element) {
            deleteImage();
        });

        drEvent.on('dropify.errors', function (event, element) {
            console.log('Has Errors');
            $.toast({
                heading: "Error",
                text: "file rusak",
                icon: 'warning',
                showHideTransition: 'fade',
                allowToastClose: true,
                hideAfter: 5000,
                position: 'top-right'
            });
        });

        $("#foto").change(function () {
            var input = $(this)[0];
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#prev-logo-kanan').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);

                var form = new FormData($('#set-foto')[0]);
                uploadAttach(base_url + 'useradmin/uploadfile/' + idUser, form);
            }
        });

        function uploadAttach(action, data) {
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: action,
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (data) {
                    console.log(data.src);
                    fprofil = data.src;
                    $('#foto').attr('data-default-file', data.src)
                },
                error: function (xhr, status, error) {
                    const err = JSON.parse(xhr.responseText)
                    Swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        }

        function deleteImage() {
            const src = $('#foto').attr('data-default-file')
            console.log(src);
            var form = new FormData($('#set-foto')[0]);
            form.append('src', src)
            $.ajax({
                data: form,
                type: "POST",
                url: base_url + "useradmin/deletefile",
                processData: false,
                contentType: false,
                cache: false,
                success: function (response) {
                    console.log(response);
                    fprofil = '';
                }
            });
        }

        $('form#change_password').on('submit', async function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let btn = $('#btn-pass');
            btn.attr('disabled', 'disabled').text('Process...');

            url = $(this).attr('action'); //useradmin/change_password
            data = $(this).serialize();
            let formData = new FormData($(this)[0])

            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            await ajaxPost(url, formData, function (error, response) {
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
                            text: response.msg,
                            icon: "success"
                        }).then(result => {
                            if (result.value) {
                                if (idUser === curUser) {
                                    window.location.href = base_url + 'logout';
                                } else {
                                    window.location.reload();
                                }
                            }
                        });
                    } else {
                        var html = '<ul style="list-style: none">';
                        if (response.errors.username != null && response.errors.username !== "") {
                            html += '<li>' + response.errors.username + '</li>';
                        }
                        if (response.errors.old != null && response.errors.old !== "") {
                            html += '<li>' + response.errors.old + '</li>';
                        }
                        if (response.errors.new != null && response.errors.new !== "") {
                            html += '<li>' + response.errors.new + '</li>';
                        }
                        if (response.errors.new_confirm != null && response.errors.new_confirm !== "") {
                            html += '<li>' + response.errors.new_confirm + '</li>';
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

            btn.removeAttr('disabled').text('Ganti Password');
        });

        $('form input, form select').on('change', function () {
            $(this).closest('.mb-3').removeClass('has-error');
            $(this).nextAll('.help-block').eq(0).text('');
        });

        $('form#user_info').on('submit', async function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let btn = $('#btn-info');
            btn.attr('disabled', 'disabled').text('Process...');

            url = $(this).attr('action'); //useradmin/edit_info
            data = $(this).serialize();
            let formData = new FormData($(this)[0])

            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            await ajaxPost(url, formData, function (error, response) {
                btn.removeAttr('disabled').text('Simpan');
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
                            text: "Data Admin berhasil diupdate",
                            icon: "success"
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    } else {
                        var html = '';
                        if (response.errors.first_name != null && response.errors.first_name !== "") {
                            html += response.errors.first_name;
                        }
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

    // simpanProfile function was unused in original PHP (it was defined but not called?), button type is submit which calls ajaxPost.
    // I kept it just in case.
    function simpanProfile() {
        var form = new FormData($('#set-foto')[0]);
        form.append('foto', fprofil);
        form.append('nama_lengkap', $('#nama-lengkap').val());
        form.append('jabatan', $('#jabatan').val());

        Swal.fire({
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        $.ajax({
            data: form,
            type: "POST",
            processData: false,
            contentType: false,
            cache: false,
            url: base_url + "useradmin/saveprofile",
            success: function (response) {
                //console.log(response);
                Swal.fire({
                    title: "Sukses",
                    text: "Profile berhasil disimpan",
                    icon: "success",
                });
            }, error: function (xhr, status, error) {
                const err = JSON.parse(xhr.responseText)
                Swal.fire({
                    title: "Error",
                    text: err.Message,
                    icon: "error"
                });
            }
        });
    }

</script>
<?= $this->endSection() ?>