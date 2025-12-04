<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6">
                    <a href="<?= base_url('useradmin') ?>" type="button" class="btn btn-sm btn-danger float-right">
                        <i class="fas fa-arrow-circle-left"></i><span
                                class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <?php if ($is_admin) : ?>
                    <div class="col-md-6">
                        <div class="card card-info my-shadow">
                            <div class="card-header">
                                <h3 class="card-title"><?= $subjudul ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if ($user->id === $users->id || $is_admin) : ?>
                                    <div class="col-5">
                                        <?= form_open_multipart('', array('id' => 'set-foto'), ['id_user'=>$users->id]) ?>
                                        <div class="form-group pb-2">
                                            <label for="logo-kanan">Foto</label>
                                            <input type="file" id="foto" name="foto" class="dropify"
                                                   data-max-file-size-preview="2M"
                                                   data-allowed-file-extensions="jpg jpeg png gif"
                                                   data-default-file="<?= base_url() . $users->foto ?>"/>
                                        </div>
                                        <?= form_close() ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-7">
                                        <?= form_open('useradmin/edit_info', array('id' => 'user_info'), array('id' => $users->id)) ?>
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" name="first_name" class="form-control"
                                                   value="<?= $users->first_name ?>">
                                            <small class="help-block"></small>
                                        </div>
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" name="last_name" class="form-control"
                                                   value="<?= $users->last_name ?>">
                                            <small class="help-block"></small>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?= $users->email ?>">
                                            <small class="help-block"></small>
                                        </div>
                                        <div class="form-group">
                                            <label>Jabatan</label>
                                            <input type="text" name="jabatan" placeholder="Jabatan" class="form-control"
                                                   value="<?= $users->jabatan ?>">
                                        </div>
                                        <div class="float-right">
                                            <button type="reset" class="btn btn-warning ml-3"><i class="fa fa-rotate-left"></i> Reload</button>
                                            <button type="submit" id="btn-info" class="btn btn-info">Simpan</button>
                                        </div>
                                        <?= form_close() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($user->id === $users->id || $is_admin) : ?>
                    <div class="col-md-6">
                        <?= form_open('useradmin/change_password', array('id' => 'change_password'), array('id' => $users->id, 'username'=>$users->username)) ?>
                        <div class="card card-warning my-shadow">
                            <div class="card-header">
                                <h3 class="card-title">Edit Data Login</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" class="form-control"
                                           value="<?= $users->username ?>">
                                    <small class="help-block"></small>
                                </div>
                                <div class="form-group">
                                    <label for="old">Password Lama</label>
                                    <input type="password" placeholder="Password Lama" name="old" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="new">Password Baru</label>
                                    <input type="password" placeholder="Password Baru" name="new" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="new_confirm">Konfirmasi Password</label>
                                    <input type="password" placeholder="Konfirmasi Password Baru" name="new_confirm"
                                           class="form-control">
                                </div>
                                <div class="float-right">
                                    <button type="reset" class="btn btn-warning ml-3"><i class="fa fa-rotate-left"></i> Reload</button>
                                    <button type="submit" id="btn-pass" class="btn btn-info ml-3">Ganti Password</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>

<script src="<?=base_url()?>/assets/app/js/ajax_request.js" type="text/javascript"></script>

<script>
    const idUser = '<?=$users->id?>';
    const curUser = '<?=$user->id?>';
    let fprofil = '<?= base_url() . $user->foto ?>';

    $(document).ready(function () {
        var drEvent = $('.dropify').dropify({
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
        });

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
                    swal.fire({
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
            //msg = "Password anda berhasil diganti";
            //submitajax(url, data, msg, 1);

            let formData = new FormData($(this)[0])

            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });

            await ajaxPost(url, formData, function (error, response) {
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
                        swal.fire({
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
            $(this).closest('.form-group').removeClass('has-error');
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

            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });

            await ajaxPost(url, formData, function (error, response) {
                btn.removeAttr('disabled').text('Simpan');
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

    function simpanProfile() {
        var form = new FormData($('#set-foto')[0]);
        form.append('foto', fprofil);
        form.append('nama_lengkap', $('#nama-lengkap').val());
        form.append('jabatan', $('#jabatan').val());

        swal.fire({
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
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
                swal.fire({
                    title: "Sukses",
                    text: "Profile berhasil disimpan",
                    icon: "success",
                });
            }, error: function (xhr, status, error) {
                const err = JSON.parse(xhr.responseText)
                swal.fire({
                    title: "Error",
                    text: err.Message,
                    icon: "error"
                });
            }
        });
    }

</script>