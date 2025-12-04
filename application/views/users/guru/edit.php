<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <?php if ($is_admin) : ?>
                    <div class="col-6">
                        <a href="<?= base_url('userguru') ?>" type="button" class="btn btn-sm btn-danger float-right">
                            <i class="fas fa-arrow-circle-left"></i><span
                                    class="d-none d-sm-inline-block ml-1">Kembali</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <?php if ($user->username === $guru->username or $is_admin) : ?>
                    <div class="col-md-6">
                        <div class="card my-shadow mb-4">
                            <div class="card-header">
                                <?php if (is_null($users)) : ?>
                                    <h6 class="card-title"><?= $guru->nama_guru ?> belum aktif</h6>
                                    <button type="submit" id="btn-aktif" class="card-tools btn btn-success btn-sm">
                                        Aktifkan
                                    </button>
                                <?php else: ?>
                                    <h6 class="card-title">Edit Login <?= $guru->nama_guru ?></h6>
                                    <?php if ($is_admin) : ?>
                                        <button type="submit" id="btn-nonaktif"
                                                class="card-tools btn btn-danger btn-sm">
                                            Nonaktifkan
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?= form_open('userguru/editlogin', array('id' => 'change_password'), array('id' => $guru->id)) ?>
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Nama Depan</span>
                                    </div>
                                    <input type="text" name="first_name" class="form-control"
                                           value="<?= is_null($users) ? '' : $users->first_name ?>"
                                           placeholder="Nama Depan"
                                        <?= is_null($users) ? 'disabled' : '' ?> required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Nama Belakang</span>
                                    </div>
                                    <input type="text" name="last_name" class="form-control"
                                           value="<?= is_null($users) ? '' : $users->last_name ?>"
                                           placeholder="Nama Belakang"
                                        <?= is_null($users) ? 'disabled' : '' ?> required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Email</span>
                                    </div>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= is_null($users) ? '' : $guru->email ?>"
                                           placeholder="Email" required <?= is_null($users) ? 'disabled' : '' ?>>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Username</span>
                                    </div>
                                    <input type="text" class="form-control" value="<?= $guru->username ?>"
                                           placeholder="Username" disabled>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Password</span>
                                    </div>
                                    <input type="password" class="form-control" value="<?= $guru->password ?>"
                                           placeholder="Username" disabled>
                                </div>
                                <button type="submit" id="btn-level"
                                        class="btn btn-primary float-right" <?= is_null($users) ? 'disabled' : '' ?>>
                                    Simpan
                                </button>
                            </div>
                            <?= form_close(); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-warning my-shadow">
                            <div class="card-header with-border">
                                <h3 class="card-title">Ubah Password & Username</h3>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Username</span>
                                    </div>
                                    <input type="text" class="form-control" name="username"
                                           value="<?= is_null($users) ? '' : $users->username ?>" placeholder="Username"
                                        <?= is_null($users) ? 'disabled' : '' ?> required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Password Lama</span>
                                    </div>
                                    <input type="password" name="old" class="form-control" placeholder="Password Lama"
                                           required <?= is_null($users) ? 'disabled' : '' ?>>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Password Baru</span>
                                    </div>
                                    <input type="password" name="new" class="form-control" placeholder="Password Baru"
                                           required <?= is_null($users) ? 'disabled' : '' ?>>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Konfirmasi Password</span>
                                    </div>
                                    <input type="password" name="new_confirm" class="form-control"
                                           placeholder="Konfirmasi Password Baru"
                                           required <?= is_null($users) ? 'disabled' : '' ?>>
                                </div>
                                <button type="submit" id="btn-pass" class="btn btn-warning float-right"
                                    <?= is_null($users) ? 'disabled' : '' ?>>Ganti Password
                                </button>
                                <button type="reset"
                                        class="btn btn-default float-right mr-2" <?= is_null($users) ? 'disabled' : '' ?>>
                                    <i class="fa fa-rotate-left"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    var guru_id = '<?=$guru->id_guru?>';

    function submitajax(url, data, msg, btn) {
        $.ajax({
            url: url,
            data: data,
            type: 'POST',
            success: function (response) {
                if (response.status) {
                    swal.fire({
                        "title": "Berhasil",
                        "text": msg,
                        "type": "success"
                    });
                } else {
                    if (response.errors) {
                        $.each(response.errors, function (key, val) {
                            $('[name="' + key + '"]').closest('.form-group').addClass('has-error');
                            $('[name="' + key + '"]').nextAll('.help-block').eq(0).text(val);
                            if (val === '') {
                                $('[name="' + key + '"]').closest('.form-group').removeClass('has-error');
                                $('[name="' + key + '"]').nextAll('.help-block').eq(0).text('');
                            }
                        });
                    }
                    if (response.msg) {
                        swal.fire({
                            "title": "Gagal",
                            "text": "Password lama tidak benar",
                            "type": "error"
                        });
                    }
                }
                btn.removeAttr('disabled').text('Ganti Password');
            }
        });
    }

    $(document).ready(function () {
        var kelas;

        $('form input, form select').on('change', function () {
            $(this).closest('.form-group').removeClass('has-error');
            $(this).nextAll('.help-block').eq(0).text('');
        });

        $('form#editjabatan').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let btn = $('#btn-jabatan');
            btn.attr('disabled', 'disabled').text('Process...');

            $.ajax({
                url: $(this).attr('action'),
                data: $(this).serialize(),
                type: 'POST',
                success: function (response) {
                    swal.fire({
                        "title": response.status ? "Berhasil" : "Gagal",
                        "text": response.msg,
                        "icon": response.status ? "success" : "error"
                    });
                    btn.removeAttr('disabled').text('Simpan');
                }
            });
        });

        $('form#user_level').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let btn = $('#btn-level');
            btn.attr('disabled', 'disabled').text('Process...');

            url = $(this).attr('action');
            data = $(this).serialize();
            msg = "Level user berhasil diupdate";
            submitajax(url, data, msg, btn);
        });

        $('form#user_status').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let btn = $('#btn-status');
            btn.attr('disabled', 'disabled').text('Process...');

            url = $(this).attr('action');
            data = $(this).serialize();
            msg = "Status user berhasil diupdate";
            submitajax(url, data, msg, btn);
        });

        var mapelObj = [];
        $('#mapel').on('select2:select', function (e) {
            var data = e.params.data;
            mapelObj.push(data);
            var inputgroup = $('#input-mapel');

            var div1 = $('<div>',  {class : "input-group mb-3 addmapel", id: data.id});
            var div2 = $('<div>',  {class : "input-group-prepend"});
            var spangroup = $('<div>',  {class : "input-group-text", text: data.text});
            var sel = $('<select>',  {name : "kelasmapel"+data.id+"[]", class : "select2 form-control selectmapel",
                multiple:'multiple', required:'required'});

            div2.append(spangroup);
            div1.append(div2);
            div1.append(sel);
            div1.appendTo(inputgroup);

            $.each(kelas, function (key, entry) {
                sel.append($('<option></option>').attr('value', entry.id_kelas).text(entry.nama_kelas));
            });

            $(".selectmapel").select2({tags: true});

            if ($(".addmapel").length) {
                $('#keterangan').removeClass('d-none');
            }
        });

        $('#mapel').on('select2:unselect', function (e) {
            var data = e.params.data;
            //console.log(data);
            for (var i = 0; i < mapelObj.length; i++)
                if (mapelObj[i].id && mapelObj[i].id === data.id) {
                    //mapelObj.splice(i, 1);
                    $("div").remove("#"+ data.id +"");
                    break;
                }
            //console.log("mapelObj", mapelObj);
            //jsonKelasMapel();

            if (!$(".addmapel").length) {
                $('#keterangan').addClass('d-none');
            }
        });

        $('#kelas-wali').select2({
            dropdownAutoWidth : true,
            width: 'auto'
        });

        $('#level').on('select2:select', function (e) {
            var data = e.params.data;
            console.log(data);

            if (data.id === "4") {
                var inputgroup = $('#input-jabatan');

                var div1 = $('<div>',  {class : "input-group mb-3 addkelas", id: "input-group-walikelas"});
                var div2 = $('<div>',  {class : "input-group-prepend"});
                var spangroup = $('<div>',  {class : "input-group-text", text: "Kelas"});
                var sel = $('<select>',  {name : "kelas_wali", class : "select2 form-control selectkelas", required:'required'});
                sel.append($('<option value="" selected="selected" disabled>Pilih Kelas</option>'));
                div2.append(spangroup);
                div1.append(div2);
                div1.append(sel);
                div1.appendTo(inputgroup);

                $.each(kelas, function (key, entry) {
                    if (entry.guru_id === guru_id) {
                        console.log(guru_id);
                        sel.append($('<option value="'+entry.id_kelas+'" selected="selected">'+entry.nama_kelas+'</option>'));
                    } else {
                        sel.append($('<option value="'+entry.id_kelas+'">'+entry.nama_kelas+'</option>'));
                    }
                    //sel.append($('<option>', {value: entry.id_kelas, text: entry.nama_kelas}));
                });

                $(".selectkelas").select2({tags: true});
            } else {
                $("div").remove("#input-group-walikelas");
            }
        });

        /*
        $('form#change_password').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let btn = $('#btn-pass');
            btn.attr('disabled', 'disabled').text('Process...');

            url = $(this).attr('action');
            data = $(this).serialize();
            msg = "Password anda berhasil diganti";
            submitajax(url, data, msg, btn);
        });
         */
    });
</script>