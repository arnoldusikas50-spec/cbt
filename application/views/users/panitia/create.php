<div class="content-wrapper bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6 text-right">
                    <a href="<?= base_url('userpanitia') ?>" class="btn btn-sm btn-danger">
                        <i class="fas fa-arrow-circle-left"></i>
                        <span class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-primary my-shadow">
                        <div class="card-header">
                            <h3 class="card-title">Tambah User Panitia</h3>
                        </div>
                        <?= form_open('', ['id' => 'formPanitia']) ?>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nama_panitia">Nama Panitia *</label>
                                    <input type="text" class="form-control" id="nama_panitia" name="nama_panitia" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="jabatan">Jabatan</label>
                                    <input type="text" class="form-control" id="jabatan" name="jabatan">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="username">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required minlength="4" maxlength="50">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="password">Password *</label>
                                    <input type="text" class="form-control" id="password" name="password" required minlength="5" maxlength="50">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="no_hp">No HP</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary float-right">
                                <i class="fas fa-save"></i>
                                <span class="d-none d-sm-inline-block ml-1">Simpan</span>
                            </button>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(function () {
        ajaxcsrf();
        $('#formPanitia').on('submit', function (e) {
            e.preventDefault();
            const btn = $(this).find('button[type=submit]');
            btn.attr('disabled', 'disabled').html('<i class="fa fa-spinner fa-spin"></i> Simpan');

            $.ajax({
                url: base_url + 'userpanitia/create',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    btn.removeAttr('disabled').html('<i class="fas fa-save"></i><span class="d-none d-sm-inline-block ml-1">Simpan</span>');
                    if (response.status) {
                        Swal.fire('Sukses', response.msg, 'success').then(() => {
                            window.location.href = base_url + 'userpanitia';
                        });
                    } else if (response.errors) {
                        let errorText = '';
                        Object.keys(response.errors).forEach(function (key) {
                            if (response.errors[key]) {
                                errorText += `${response.errors[key]}\n`;
                            }
                        });
                        Swal.fire('Validasi Gagal', errorText || response.msg, 'warning');
                    } else {
                        Swal.fire('Gagal', response.msg || 'Terjadi kesalahan', 'error');
                    }
                },
                error: function (xhr) {
                    btn.removeAttr('disabled').html('<i class="fas fa-save"></i><span class="d-none d-sm-inline-block ml-1">Simpan</span>');
                    Swal.fire('Gagal', xhr.responseText || 'Terjadi kesalahan server', 'error');
                }
            });
        });
    });
</script>
