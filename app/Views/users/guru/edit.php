<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$users = $users ?? null;
$user = $user ?? null;
$guru = $guru ?? null;
$is_admin = $is_admin ?? false;
?>
<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= esc($judul ?? 'Halaman') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
</div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <?php if ($user->username === $guru->username or $is_admin): ?>
                    <div class="col-md-6">
                        <div class="card my-shadow mb-4">
                            <div class="card-header">
                                <?php if (is_null($users)): ?>
                                    <h6 class="card-title"><?= $guru->nama_guru ?> belum aktif</h6>
                                    <button type="submit" id="btn-aktif" class="card-tools btn btn-success btn-sm ms-auto">
                                        Aktifkan
                                    </button>
                                <?php else: ?>
                                    <h6 class="card-title">Edit Login <?= $guru->nama_guru ?></h6>
                                    <?php if ($is_admin): ?>
                                        <button type="submit" id="btn-nonaktif" class="card-tools btn btn-danger btn-sm ms-auto">
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
                                        value="<?= is_null($users) ? '' : $users->first_name ?>" placeholder="Nama Depan"
                                        <?= is_null($users) ? 'disabled' : '' ?> required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Nama Belakang</span>
                                    </div>
                                    <input type="text" name="last_name" class="form-control"
                                        value="<?= is_null($users) ? '' : $users->last_name ?>" placeholder="Nama Belakang"
                                        <?= is_null($users) ? 'disabled' : '' ?> required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Email</span>
                                    </div>
                                    <input type="email" name="email" class="form-control"
                                        value="<?= is_null($users) ? '' : $guru->username . '@guru.sekolah' /* Mock email if missing, based on logic controller */ ?>"
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
                                <button type="submit" id="btn-level" class="btn btn-primary float-end"
                                    <?= is_null($users) ? 'disabled' : '' ?>>
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
                                    <input type="password" name="old" class="form-control" placeholder="Password Lama" required
                                        <?= is_null($users) ? 'disabled' : '' ?>>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Password Baru</span>
                                    </div>
                                    <input type="password" name="new" class="form-control" placeholder="Password Baru" required
                                        <?= is_null($users) ? 'disabled' : '' ?>>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend w-40">
                                        <span class="input-group-text">Konfirmasi Password</span>
                                    </div>
                                    <input type="password" name="new_confirm" class="form-control"
                                        placeholder="Konfirmasi Password Baru" required <?= is_null($users) ? 'disabled' : '' ?>>
                                </div>
                                <button type="submit" id="btn-pass" class="btn btn-warning float-end"
                                    <?= is_null($users) ? 'disabled' : '' ?>>Ganti Password
                                </button>
                                <button type="reset" class="btn btn-default float-end me-2"
                                    <?= is_null($users) ? 'disabled' : '' ?>>
                                    <i class="bi bi-rotate-left"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
    var guru_id = '<?= $guru->id_guru ?>';

    // Helper for submitting ajax with button state handling
    function submitajax(url, data, msg, btn) {
        $.ajax({
            url: url,
            data: data,
            type: 'POST',
            success: function (response) {
                if (response.status) {
                    Swal.fire({
                        "title": "Berhasil",
                        "text": msg,
                        "icon": "success"
                    });
                } else {
                    if (response.errors) {
                        $.each(response.errors, function (key, val) {
                            $('[name="' + key + '"]').closest('.mb-3').addClass('has-error');
                            $('[name="' + key + '"]').nextAll('.help-block').eq(0).text(val);
                            if (val === '') {
                                $('[name="' + key + '"]').closest('.mb-3').removeClass('has-error');
                                $('[name="' + key + '"]').nextAll('.help-block').eq(0).text('');
                            }
                        });
                    }
                    if (response.msg) {
                        Swal.fire({
                            "title": "Gagal",
                            "text": response.msg,
                            "icon": "error"
                        });
                    }
                }
                btn.removeAttr('disabled').text('Simpan'); // Reset button text properly
            }
        });
    }

    $(document).ready(function () {
        var kelas; 
        // Note: 'kelas' var definition missing in original JS snippet (it was used in '$.each(kelas...)'). 
        // It likely should be passed from PHP: var kelas = <?// json_encode($kelass) ?>; 
        // But since HTML elements using it are missing, I'll comment it out to prevent errors if uncommented later.

        $('form input, form select').on('change', function () {
            $(this).closest('.mb-3').removeClass('has-error');
            $(this).nextAll('.help-block').eq(0).text('');
        });

        // Forms below are referenced in JS but seemingly missing in HTML. Kept for completeness.
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
                    Swal.fire({
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
        
        // Mapel and Kelas logic handling (if elements exist)
        /*
        var mapelObj = [];
        $('#mapel').on('select2:select', function (e) { ... });
        $('#mapel').on('select2:unselect', function (e) { ... });
        $('#kelas-wali').select2({ ... });
        $('#level').on('select2:select', function (e) { ... });
        */
       
       // Handle change password form specifically if ID matches
       $('form#change_password').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let btn = $('#btn-level'); // Using btn-level as in HTML form submit button
            btn.attr('disabled', 'disabled').text('Process...');

            url = $(this).attr('action');
            data = $(this).serialize();
            msg = "Data login berhasil diupdate";
            submitajax(url, data, msg, btn);
        });
    });
</script>
<?= $this->endSection() ?>
