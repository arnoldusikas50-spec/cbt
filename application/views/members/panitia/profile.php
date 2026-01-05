<?php
/**
 * View Profile Panitia
 * Display edit form for Nama, Username, Password, Email, No HP
 * And Upload for Foto & TTD
 */
?>

<style>
    /* Force correct input group helper */
    .input-group {
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: stretch !important;
        width: 100% !important;
    }

    /* Icon container box */
    .input-group-text {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0.375rem 0.75rem !important;
        margin-bottom: 0 !important;
        font-size: 1rem !important;
        font-weight: 400 !important;
        line-height: 1.5 !important;
        color: #495057 !important;
        text-align: center !important;
        white-space: nowrap !important;
        background-color: #f8f9fa !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem 0 0 0.25rem !important;
        border-right: 0 !important;
        width: 45px !important;
        /* Fixed width for consistency */
    }

    /* Input fields */
    .input-group>.form-control,
    .input-group>.form-select {
        position: relative !important;
        flex: 1 1 auto !important;
        width: 1% !important;
        min-width: 0 !important;
        margin-bottom: 0 !important;
        border-radius: 0 0.25rem 0.25rem 0 !important;
        border-left: 0 !important;
        padding-left: 10px !important;
    }

    /* Password toggle button special case */
    #toggle-password {
        border-radius: 0 0.25rem 0.25rem 0 !important;
        border-left: 0 !important;
        z-index: 5 !important;
    }

    /* Password input special case (middle item) */
    .input-group>#password-input {
        border-radius: 0 !important;
        border-right: 0 !important;
    }

    /* Signature canvas */
    #sig-canvas {
        border: 2px dotted #CCCCCC;
        border-radius: 15px;
        cursor: crosshair;
        background-color: #ffffff;
    }
</style>

<main class="app-main bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <form id="form-foto" enctype="multipart/form-data">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                                        value="<?= $this->security->get_csrf_hash() ?>" />
                                    <input type="hidden" name="id_panitia" value="<?= $panitia->id_panitia ?>">
                                    <input type="hidden" name="field" value="foto">
                                    <div class="position-relative d-inline-block">
                                        <img class="profile-user-img img-fluid img-circle"
                                            src="<?= !empty($panitia->foto) ? base_url($panitia->foto) : base_url('assets/img/avatar-default.png') ?>"
                                            alt="User profile picture" id="img-foto-preview"
                                            style="width: 150px; height: 150px; object-fit: cover;">
                                        <label for="input-foto" class="position-absolute"
                                            style="bottom: 0; right: 0; background: rgba(0,0,0,0.5); border-radius: 50%; padding: 8px; cursor: pointer;">
                                            <i class="bi bi-camera text-white"></i>
                                            <input type="file" name="file" id="input-foto" class="d-none"
                                                accept="image/*">
                                        </label>
                                    </div>
                                </form>
                            </div>

                            <h3 class="profile-username text-center">
                                <?= $panitia->nama_lengkap ?? $panitia->nama_panitia ?>
                            </h3>
                            <p class="text-muted text-center"><?= $panitia->jabatan ?? 'Panitia Ujian' ?></p>

                            <hr>
                            <strong><i class="bi bi-signature me-1"></i> Tanda Tangan Digital</strong>
                            <div class="text-center mt-2">
                                <form id="form-ttd" enctype="multipart/form-data">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                                        value="<?= $this->security->get_csrf_hash() ?>" />
                                    <input type="hidden" name="id_panitia" value="<?= $panitia->id_panitia ?>">
                                    <input type="hidden" name="field" value="ttd">
                                    <div class="border rounded p-2" style="background: #f8f9fa;">
                                        <div id="ttd-placeholder"
                                            class="d-flex align-items-center justify-content-center text-muted"
                                            style="height: 100px; border: 2px dashed #ccc; <?= !empty($panitia->ttd) ? 'display:none !important;' : '' ?>">
                                            <small>Belum ada TTD</small>
                                        </div>
                                        <img src="<?= !empty($panitia->ttd) ? base_url($panitia->ttd) : '' ?>" alt="TTD"
                                            id="img-ttd-preview"
                                            class="img-fluid <?= empty($panitia->ttd) ? 'd-none' : '' ?>"
                                            style="max-height: 100px;">
                                        <div class="mt-2">
                                            <label class="btn btn-sm btn-primary mb-0" title="Upload File Gambar">
                                                <i class="bi bi-upload"></i> Upload
                                                <input type="file" name="file" id="input-ttd" class="d-none"
                                                    accept="image/*">
                                            </label>
                                            <button type="button" class="btn btn-sm btn-success mb-0 ms-1"
                                                id="btn-create-ttd" title="Buat TTD Baru">
                                                <i class="bi bi-pen-nib"></i> Buat
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-danger mb-0 ms-1 <?= empty($panitia->ttd) ? 'd-none' : '' ?>"
                                                id="btn-delete-ttd" title="Hapus TTD">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-teal">
                        <div class="card-header">
                            <h3 class="card-title text-blue">Edit Data Akun</h3>
                        </div>
                        <form id="form-profile" class="form-horizontal">
                            <div class="card-body">
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                                    value="<?= $this->security->get_csrf_hash() ?>" />
                                <input type="hidden" name="id_panitia" value="<?= $panitia->id_panitia ?>">

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-person-fill text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0"
                                                name="nama_panitia" value="<?= $panitia->nama_panitia ?>"
                                                placeholder="Ester Snaes T, S.Pd">
                                        </div>
                                        <span class="text-danger error-text nama_panitia-error"></span>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-envelope-fill text-muted"></i>
                                            </span>
                                            <input type="email" class="form-control border-start-0 ps-0" name="email"
                                                value="<?= $panitia->email ?>" placeholder="Email">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">NIP / NUPTK</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-card-text text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0" name="nip"
                                                value="<?= $panitia->nip ?>" placeholder="1746765666130182">
                                        </div>
                                        <span class="text-danger error-text nip-error"></span>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Jenis Kelamin</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-gender-ambiguous text-muted"></i>
                                            </span>
                                            <select class="form-select border-start-0 ps-0" name="jenis_kelamin">
                                                <option value="">-- Pilih Jenis Kelamin --</option>
                                                <option value="L" <?= (isset($panitia->jenis_kelamin) && $panitia->jenis_kelamin == 'L') ? 'selected' : '' ?>>Laki-laki
                                                </option>
                                                <option value="P" <?= (isset($panitia->jenis_kelamin) && $panitia->jenis_kelamin == 'P') ? 'selected' : '' ?>>Perempuan
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">No. Handphone</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-telephone-fill text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0" name="no_hp"
                                                value="<?= $panitia->no_hp ?>" placeholder="No. Handphone">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Agama</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-person-fill text-muted"></i>
                                            </span>
                                            <select class="form-select border-start-0 ps-0" name="agama">
                                                <option value="">-- Pilih Agama --</option>
                                                <option value="Islam" <?= (isset($panitia->agama) && $panitia->agama == 'Islam') ? 'selected' : '' ?>>Islam</option>
                                                <option value="Kristen" <?= (isset($panitia->agama) && $panitia->agama == 'Kristen') ? 'selected' : '' ?>>Kristen</option>
                                                <option value="Katolik" <?= (isset($panitia->agama) && $panitia->agama == 'Katolik') ? 'selected' : '' ?>>Katolik</option>
                                                <option value="Hindu" <?= (isset($panitia->agama) && $panitia->agama == 'Hindu') ? 'selected' : '' ?>>Hindu</option>
                                                <option value="Buddha" <?= (isset($panitia->agama) && $panitia->agama == 'Buddha') ? 'selected' : '' ?>>Buddha</option>
                                                <option value="Konghucu" <?= (isset($panitia->agama) && $panitia->agama == 'Konghucu') ? 'selected' : '' ?>>Konghucu</option>
                                                <option value="Protestan" <?= (isset($panitia->agama) && $panitia->agama == 'Protestan') ? 'selected' : '' ?>>Protestan
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Google API Key</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-key-fill text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0" name="api_key"
                                                value="<?= isset($panitia->api_key) ? $panitia->api_key : '' ?>"
                                                placeholder="Google API Key">
                                        </div>
                                    </div>
                                </div>

                                <?php if (isset($panitia->jabatan) && $panitia->jabatan === 'Pengawas'): ?>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Pengawas Ruang</label>
                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="bi bi-door-open-fill text-muted"></i>
                                                </span>
                                                <?php
                                                $options = ['' => 'Pilih Ruang'] + $ruangs;
                                                echo form_dropdown('ruang', $options, $panitia->ruang, ['class' => 'form-select border-start-0 ps-0']);
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Instansi/Sekolah Asal</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-building-fill text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0" name="instansi"
                                                value="<?= isset($panitia->instansi) ? $panitia->instansi : '' ?>"
                                                placeholder="Asal Sekolah/Instansi">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Username</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-person-badge-fill text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 ps-0" name="username"
                                                value="<?= $panitia->username ?>" disabled>
                                        </div>
                                        <span class="text-danger error-text username-error"></span>
                                    </div>
                                </div>

                                <div class="mb-3 row">
                                    <label class="col-sm-3 col-form-label">Password Baru</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="bi bi-lock-fill text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-0 ps-0" name="password"
                                                id="password-input"
                                                placeholder="Kosongkan jika tidak ingin mengganti password">
                                            <button class="btn bg-light border-start-0" type="button"
                                                id="toggle-password"
                                                style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                                                <i class="bi bi-eye-fill text-muted" id="eye-icon"></i>
                                            </button>
                                        </div>
                                        <span class="help-block text-muted small"><i
                                                class="bi bi-info-circle me-1"></i>Minimal 6 karakter</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary float-end">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <!-- Modal Signature -->
    <div class="modal fade" id="modal-signature" tabindex="-1" role="dialog" aria-labelledby="modalSigLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSigLabel">Buat Tanda Tangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-sm text-muted">Gunakan mouse atau layar sentuh untuk tanda tangan di kotak di bawah
                        ini.
                    </p>
                    <canvas id="sig-canvas" width="400" height="200" style="touch-action: none;"></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="sig-clear">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Ulangi
                    </button>
                    <button type="button" class="btn btn-primary" id="sig-submit">
                        <i class="bi bi-check-circle me-2"></i>Simpan Tanda Tangan
                    </button>
                </div>
            </div>
        </div>
    </div>



    <script>
        var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash() ?>';

        $(document).ready(function () {
            // --- Bootstrap Toast Helper ---
            // --- Bootstrap Alert Helper ---
            function showBootstrapAlert(type, message) {
                const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show fixed-top m-3 shadow" role="alert" style="z-index: 9999; max-width: 500px; left: 50%; transform: translateX(-50%);">
                        <i class="bi ${icon} me-2"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                $('body').append(alertHtml);
                setTimeout(() => {
                    $('.alert-dismissible').alert('close');
                }, 3000);
            }
            // --- Toggle Password Visibility ---
            $('#toggle-password').click(function () {
                const passwordInput = $('#password-input');
                const eyeIcon = $('#eye-icon');

                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    eyeIcon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
                } else {
                    passwordInput.attr('type', 'password');
                    eyeIcon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
                }
            });

            // --- Signature Pad Logic ---
            var canvas = document.getElementById("sig-canvas");
            var ctx = canvas.getContext("2d");
            var drawing = false;
            var mousePos = { x: 0, y: 0 };
            var lastPos = mousePos;

            // Resize canvas to fit modal if needed (optional)

            function getMousePos(canvasDom, mouseEvent) {
                var rect = canvasDom.getBoundingClientRect();
                return {
                    x: (mouseEvent.clientX || mouseEvent.touches[0].clientX) - rect.left,
                    y: (mouseEvent.clientY || mouseEvent.touches[0].clientY) - rect.top
                };
            }

            function renderCanvas() {
                if (drawing) {
                    ctx.beginPath();
                    ctx.moveTo(lastPos.x, lastPos.y);
                    ctx.lineTo(mousePos.x, mousePos.y);
                    ctx.lineWidth = 3;
                    ctx.lineCap = 'round';
                    ctx.strokeStyle = '#000000';
                    ctx.stroke();
                    lastPos = mousePos;
                }
            }

            // Mouse Events
            canvas.addEventListener("mousedown", function (e) {
                drawing = true;
                lastPos = getMousePos(canvas, e);
            }, false);
            canvas.addEventListener("mouseup", function (e) {
                drawing = false;
            }, false);
            canvas.addEventListener("mousemove", function (e) {
                mousePos = getMousePos(canvas, e);
                renderCanvas();
            }, false);

            // Touch Events
            canvas.addEventListener("touchstart", function (e) {
                mousePos = getMousePos(canvas, e);
                var touch = e.touches[0];
                var mouseEvent = new MouseEvent("mousedown", {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                canvas.dispatchEvent(mouseEvent);
                e.preventDefault(); // Prevent scrolling
            }, false);
            canvas.addEventListener("touchend", function (e) {
                var mouseEvent = new MouseEvent("mouseup", {});
                canvas.dispatchEvent(mouseEvent);
            }, false);
            canvas.addEventListener("touchmove", function (e) {
                var touch = e.touches[0];
                var mouseEvent = new MouseEvent("mousemove", {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                canvas.dispatchEvent(mouseEvent);
                e.preventDefault();
            }, false);

            // Modal Events
            $('#btn-create-ttd').click(function () {
                $('#modal-signature').modal('show');
                // Clear canvas on open? Maybe not, keep history unless cleared.
            });

            $('#modal-signature').on('shown.bs.modal', function () {
                // Fix for canvas size/rendering context if needed
                // Just clear it for new signature
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });

            $('#sig-clear').click(function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });

            $('#sig-submit').click(function () {
                // Validation: Check if canvas is empty? (Simple check: getImageData)
                // Convert to dataURL
                var dataUrl = canvas.toDataURL("image/png");
                var id_panitia = $('input[name="id_panitia"]').val();

                var data = {
                    id_panitia: id_panitia,
                    image: dataUrl
                };
                data[csrfName] = csrfHash;

                $.ajax({
                    url: base_url + 'panitia/save_signature',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    beforeSend: function () {
                        $('#sig-submit').attr('disabled', true).text('Menyimpan...');
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#img-ttd-preview').attr('src', response.src).removeClass('d-none');
                            $('#ttd-placeholder').addClass('d-none');
                            $('#img-ttd-preview').attr('src', response.src).removeClass('d-none');
                            $('#ttd-placeholder').addClass('d-none');
                            $('#btn-delete-ttd').removeClass('d-none');
                            showBootstrapAlert('success', response.message);
                            $('#modal-signature').modal('hide');
                        } else {
                            showBootstrapAlert('danger', response.message || 'Gagal menyimpan tanda tangan');
                        }
                    },
                    error: function () {
                        showBootstrapAlert('danger', 'Gagal menyimpan signature');
                    },
                    complete: function () {
                        $('#sig-submit').attr('disabled', false).text('Simpan Tanda Tangan');
                    }
                });
            });

            // Delete TTD
            $('#btn-delete-ttd').click(function () {
                if (!confirm('Hapus Tanda Tangan?\n\nFile tanda tangan akan dihapus permanen!')) {
                    return;
                }

                var id_panitia = $('input[name="id_panitia"]').val();
                var data = { id_panitia: id_panitia };
                data[csrfName] = csrfHash;

                $.ajax({
                    url: base_url + 'panitia/delete_ttd',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            $('#img-ttd-preview').addClass('d-none').attr('src', '');
                            $('#ttd-placeholder').removeClass('d-none');
                            $('#btn-delete-ttd').addClass('d-none');
                            showBootstrapAlert('success', response.message);
                        } else {
                            showBootstrapAlert('danger', response.message || 'Gagal menghapus tanda tangan');
                        }
                    },
                    error: function () {
                        showToast('Gagal menghapus tanda tangan', 'error');
                    }
                });
            });

            // --- End Signature Pad Logic ---


            // Upload Foto
            $('#input-foto').change(function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#img-foto-preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);

                    var formData = new FormData($('#form-foto')[0]);
                    //formData.append(csrfName, csrfHash);
                    uploadFile(formData, 'foto');
                }
            });

            // Upload TTD (Manual File)
            $('#input-ttd').change(function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#img-ttd-preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);

                    var formData = new FormData($('#form-ttd')[0]);
                    //formData.append(csrfName, csrfHash);
                    uploadFile(formData, 'ttd');
                }
            });

            function uploadFile(formData, type) {
                $.ajax({
                    url: base_url + 'panitia/upload_foto',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            if (type === 'ttd') {
                                $('#img-ttd-preview').removeClass('d-none');
                                $('#ttd-placeholder').addClass('d-none');
                                $('#btn-delete-ttd').removeClass('d-none');
                            }
                            showBootstrapAlert('success', response.message);
                        } else {
                            showBootstrapAlert('danger', response.message || 'Gagal upload file');
                        }
                    },
                    error: function () {
                        showBootstrapAlert('danger', 'Gagal mengupload file');
                    }
                });
            }

            // Save Profile
            $('#form-profile').submit(function (e) {
                e.preventDefault();
                $('.error-text').text('');

                var data = $(this).serialize();

                $.ajax({
                    url: base_url + 'panitia/save',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            showBootstrapAlert('success', response.message);
                            setTimeout(function () {
                                window.location.reload();
                            }, 1500);
                        } else {
                            if (response.errors) {
                                $.each(response.errors, function (key, val) {
                                    $('.' + key + '-error').text(val);
                                });
                            }
                            showBootstrapAlert('danger', 'Gagal menyimpan profil');
                        }
                    },
                    error: function () {
                        showBootstrapAlert('danger', 'Terjadi kesalahan server');
                    }
                });
            });
        });
    </script>