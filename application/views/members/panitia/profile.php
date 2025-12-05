<?php
/**
 * View Profile Panitia
 * Display edit form for Nama, Username, Password, Email, No HP
 * And Upload for Foto & TTD
 */
?>

<style>
    #sig-canvas {
        border: 2px dotted #CCCCCC;
        border-radius: 15px;
        cursor: crosshair;
        /* Background putih hanya untuk tampilan, hasil simpan tetap transparan */
        background-color: #ffffff;
    }
</style>

<div class="content-wrapper bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <form id="form-foto" enctype="multipart/form-data">
                                    <input type="hidden" name="id_panitia" value="<?=$panitia->id_panitia?>">
                                    <input type="hidden" name="field" value="foto">
                                    <div class="position-relative d-inline-block">
                                        <img class="profile-user-img img-fluid img-circle"
                                             src="<?= !empty($panitia->foto) ? base_url($panitia->foto) : base_url('assets/img/avatar-default.png') ?>"
                                             alt="User profile picture"
                                             id="img-foto-preview"
                                             style="width: 150px; height: 150px; object-fit: cover;">
                                        <label for="input-foto" class="position-absolute" style="bottom: 0; right: 0; background: rgba(0,0,0,0.5); border-radius: 50%; padding: 8px; cursor: pointer;">
                                            <i class="fas fa-camera text-white"></i>
                                            <input type="file" name="file" id="input-foto" class="d-none" accept="image/*">
                                        </label>
                                    </div>
                                </form>
                            </div>

                            <h3 class="profile-username text-center"><?=$panitia->nama_lengkap ?? $panitia->nama_panitia?></h3>
                            <p class="text-muted text-center"><?=$panitia->jabatan ?? 'Panitia Ujian'?></p>

                            <hr>
                            <strong><i class="fas fa-signature mr-1"></i> Tanda Tangan Digital</strong>
                            <div class="text-center mt-2">
                                <form id="form-ttd" enctype="multipart/form-data">
                                    <input type="hidden" name="id_panitia" value="<?=$panitia->id_panitia?>">
                                    <input type="hidden" name="field" value="ttd">
                                    <div class="border rounded p-2" style="background: #f8f9fa;">
                                        <div id="ttd-placeholder" class="d-flex align-items-center justify-content-center text-muted" style="height: 100px; border: 2px dashed #ccc; <?= !empty($panitia->ttd) ? 'display:none !important;' : '' ?>">
                                            <small>Belum ada TTD</small>
                                        </div>
                                        <img src="<?= !empty($panitia->ttd) ? base_url($panitia->ttd) : '' ?>" 
                                             alt="TTD" 
                                             id="img-ttd-preview"
                                             class="img-fluid <?= empty($panitia->ttd) ? 'd-none' : '' ?>" 
                                             style="max-height: 100px;">
                                        <div class="mt-2">
                                            <label class="btn btn-sm btn-outline-primary mb-0" title="Upload File Gambar">
                                                <i class="fas fa-upload"></i> Upload
                                                <input type="file" name="file" id="input-ttd" class="d-none" accept="image/*">
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success mb-0 ml-1" id="btn-create-ttd" title="Buat TTD Baru">
                                                <i class="fas fa-pen-nib"></i> Buat
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger mb-0 ml-1 <?= empty($panitia->ttd) ? 'd-none' : '' ?>" id="btn-delete-ttd" title="Hapus TTD">
                                                <i class="fas fa-trash"></i>
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
                                <input type="hidden" name="id_panitia" value="<?=$panitia->id_panitia?>">
                                
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="nama_panitia" value="<?=$panitia->nama_panitia?>">
                                        <span class="text-danger error-text nama_panitia-error"></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Username</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="username" value="<?=$panitia->username?>">
                                        <span class="text-danger error-text username-error"></span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" name="email" value="<?=$panitia->email?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">No. HP</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="no_hp" value="<?=$panitia->no_hp?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Password Baru</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengganti password">
                                        <span class="help-block text-muted small">Minimal 6 karakter</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary float-right">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Signature -->
<div class="modal fade" id="modal-signature" tabindex="-1" role="dialog" aria-labelledby="modalSigLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSigLabel">Buat Tanda Tangan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="text-sm text-muted">Gunakan mouse atau layar sentuh untuk tanda tangan di kotak di bawah ini.</p>
                <canvas id="sig-canvas" width="400" height="200" style="touch-action: none;"></canvas>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="sig-clear">Ulangi</button>
                <button type="button" class="btn btn-primary" id="sig-submit">Simpan Tanda Tangan</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // --- Signature Pad Logic ---
    var canvas = document.getElementById("sig-canvas");
    var ctx = canvas.getContext("2d");
    var drawing = false;
    var mousePos = { x:0, y:0 };
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
    $('#btn-create-ttd').click(function() {
        $('#modal-signature').modal('show');
        // Clear canvas on open? Maybe not, keep history unless cleared.
    });
    
    $('#modal-signature').on('shown.bs.modal', function () {
        // Fix for canvas size/rendering context if needed
        // Just clear it for new signature
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    $('#sig-clear').click(function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    $('#sig-submit').click(function() {
        // Validation: Check if canvas is empty? (Simple check: getImageData)
        // Convert to dataURL
        var dataUrl = canvas.toDataURL("image/png");
        var id_panitia = $('input[name="id_panitia"]').val();

        $.ajax({
            url: base_url + 'panitia/save_signature',
            type: 'POST',
            data: {
                id_panitia: id_panitia,
                image: dataUrl
            },
            dataType: 'json',
            beforeSend: function() {
                $('#sig-submit').attr('disabled', true).text('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#img-ttd-preview').attr('src', response.src).removeClass('d-none');
                    $('#ttd-placeholder').addClass('d-none');
                    $('#btn-delete-ttd').removeClass('d-none');
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#modal-signature').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menyimpan signature'
                });
            },
            complete: function() {
                $('#sig-submit').attr('disabled', false).text('Simpan Tanda Tangan');
            }
        });
    });

    // Delete TTD
    $('#btn-delete-ttd').click(function() {
        Swal.fire({
            title: 'Hapus Tanda Tangan?',
            text: "File tanda tangan akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.value) {
                var id_panitia = $('input[name="id_panitia"]').val();
                $.ajax({
                    url: base_url + 'panitia/delete_ttd',
                    type: 'POST',
                    data: {id_panitia: id_panitia},
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $('#img-ttd-preview').addClass('d-none').attr('src', '');
                            $('#ttd-placeholder').removeClass('d-none');
                            $('#btn-delete-ttd').addClass('d-none');
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghapus tanda tangan'
                        });
                    }
                });
            }
        });
    });

    // --- End Signature Pad Logic ---


    // Upload Foto
    $('#input-foto').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#img-foto-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
            
            var formData = new FormData($('#form-foto')[0]);
            uploadFile(formData, 'foto');
        }
    });

    // Upload TTD (Manual File)
    $('#input-ttd').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#img-ttd-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);

            var formData = new FormData($('#form-ttd')[0]);
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
            success: function(response) {
                if (response.status) {
                    if (type === 'ttd') {
                        $('#img-ttd-preview').removeClass('d-none');
                        $('#ttd-placeholder').addClass('d-none');
                        $('#btn-delete-ttd').removeClass('d-none');
                    }
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire('Gagal mengupload file', '', 'error');
            }
        });
    }

    // Save Profile
    $('#form-profile').submit(function(e) {
        e.preventDefault();
        $('.error-text').text('');
        
        $.ajax({
            url: base_url + 'panitia/save',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    if (response.errors) {
                        $.each(response.errors, function(key, val) {
                            $('.' + key + '-error').text(val);
                        });
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan profil'
                    });
                }
            },
            error: function() {
                Swal.fire('Terjadi kesalahan server', '', 'error');
            }
        });
    });
});
</script>
