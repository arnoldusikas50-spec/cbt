<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6 text-end">
                    <a href="<?= base_url('userpanitia') ?>" class="btn btn-sm btn-danger">
                        <i class="bi bi-arrow-left-square"></i>
                        <span class="d-none d-sm-inline-block ms-1">Kembali</span>
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
                            <div class="row g-3">
                                <div class="mb-3 col-md-6">
                                    <label for="nama_panitia">Nama Panitia *</label>
                                    <input type="text" class="form-control" id="nama_panitia" name="nama_panitia"
                                        required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="nip">NIP</label>
                                    <input type="number" class="form-control" id="nip" name="nip">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="mb-3 col-md-6">
                                    <label for="jabatan">Jabatan</label>
                                    <select class="form-control" id="jabatan" name="jabatan">
                                        <option value="">Pilih Jabatan</option>
                                        <option value="Panitia">Panitia</option>
                                        <option value="Pengawas">Pengawas</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="username">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required
                                        minlength="4" maxlength="50">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="mb-3 col-md-6">
                                    <label for="password">Password *</label>
                                    <input type="text" class="form-control" id="password" name="password" required
                                        minlength="5" maxlength="50">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="mb-3 col-md-6">
                                    <label for="no_hp">No HP</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary float-end">
                                <i class="bi bi-save"></i>
                                <span class="d-none d-sm-inline-block ms-1">Simpan</span>
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
                btn.attr('disabled', 'disabled').html('<i class="bi bi-spinner fa-spin"></i> Simpan');

                $.ajax({
                    url: base_url + 'userpanitia/create',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        btn.removeAttr('disabled').html('<i class="bi bi-save"></i><span class="d-none d-sm-inline-block ms-1">Simpan</span>');
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
                        btn.removeAttr('disabled').html('<i class="bi bi-save"></i><span class="d-none d-sm-inline-block ms-1">Simpan</span>');
                        Swal.fire('Gagal', xhr.responseText || 'Terjadi kesalahan server', 'error');
                    }
                });
            });
        });
    </script>