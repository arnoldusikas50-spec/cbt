<style>
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
                <div class="col-xl-4 col-md-4">
                    <div class="card my-shadow card-primary card-outline">
                        <div class="box-info text-center user-profile-2">
                            <div class="user-profile-inner">
                                <?php $foto = file_exists($guru->foto ?? '') ? base_url() . $guru->foto : base_url('assets/img/guru.png'); ?>
                                <img id="foto-guru" src="<?= $foto ?>" class="img-circle profile-avatar mt-2"
                                    alt="User avatar">
                                <h4 class="mt-5"><?= $guru->nama_guru ?></h4>
                                <h5 class="mb-5"><?= $guru->level . ' ' . $guru->nama_kelas ?></h5>
                                <div class="user-button">
                                    <div class="row">
                                        <div class="col-6">
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#editFotoModal"
                                                class="btn btn-sm btn-primary btn-block"><i class="bi bi-image"></i>
                                                Ganti Foto
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-danger btn-sm btn-block"
                                                onclick="deleteImage(true)"><i class="bi bi-trash"></i> Hapus Foto
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <h6 class="text-muted text-start">Tanda Tangan</h6>
                                            <div class="border rounded p-2" style="background: #f8f9fa;">
                                                <div id="ttd-placeholder"
                                                    class="d-flex align-items-center justify-content-center text-muted"
                                                    style="height: 100px; border: 2px dashed #ccc; <?= !empty($guru->ttd) ? 'display:none !important;' : '' ?>">
                                                    <small>Belum ada TTD</small>
                                                </div>
                                                <img src="<?= !empty($guru->ttd) ? base_url($guru->ttd) : '' ?>"
                                                    alt="TTD" id="img-ttd-preview"
                                                    class="img-fluid <?= empty($guru->ttd) ? 'd-none' : '' ?>"
                                                    style="max-height: 100px;">
                                                <div class="mt-2 text-center">
                                                    <button type="button" class="btn btn-sm btn-success mb-0 ms-1"
                                                        id="btn-create-ttd" title="Buat TTD Baru">
                                                        <i class="bi bi-pen-nib"></i> Buat
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger mb-0 ms-1 <?= empty($guru->ttd) ? 'd-none' : '' ?>"
                                                        id="btn-delete-ttd" title="Hapus TTD">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <button type="button" class="btn btn-warning btn-block"
                                                data-bs-toggle="modal" data-bs-target="#editLoginModal"><i
                                                    class="bi bi-pencil"></i> Edit Username / Password
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-md-8">
                    <div class="card card-primary my-shadow card-outline card-outline-tabs">
                        <div class="card-header border-bottom-0 p-0">
                            <div class="card-title">
                                <ul class="nav nav-tabs" id="custom-tabs-for-tab" role="tablist">
                                    <li class="nav-item alert-default-primary">
                                        <a class="nav-link active" id="custom-tabs-for-home-tab" data-bs-toggle="pill"
                                            href="#custom-tabs-for-home" role="tab" aria-controls="custom-tabs-for-home"
                                            aria-selected="true">Profile *</a>
                                    </li>
                                    <li class="nav-item alert-default-success">
                                        <a class="nav-link" id="custom-tabs-for-profile-tab" data-bs-toggle="pill"
                                            href="#custom-tabs-for-profile" role="tab"
                                            aria-controls="custom-tabs-for-profile" aria-selected="false">Data
                                            Lengkap</a>
                                    </li>
                                    <li class="nav-item alert-default-warning">
                                        <a class="nav-link" id="custom-tabs-for-document-tab" data-bs-toggle="pill"
                                            href="#custom-tabs-for-document" role="tab"
                                            aria-controls="custom-tabs-for-document" aria-selected="false">Dokumen</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-tools ms-auto">
                                <button id="submit-profil" class="btn btn-sm bg-primary text-white">
                                    <i class="bi bi-save"></i><span class="d-none d-sm-inline-block ms-1">Simpan</span>
                                </button>
                            </div>
                        </div>
                        <?= form_open_multipart('', array('id' => 'formguru'), array('method' => 'edit', 'id_guru' => $guru->id_guru)); ?>
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-for-tabContent">
                                <div class="tab-pane fade active show" id="custom-tabs-for-home" role="tabpanel"
                                    aria-labelledby="custom-tabs-for-home-tab">
                                    <?php foreach ($input_profile as $input): ?>
                                        <?php if ($input->name == 'jenis_kelamin'): ?>
                                            <div class="mb-3 row">
                                                <div class="col-md-4 col-6 mb-sm-0">
                                                    <label for="<?= $input->name ?>"
                                                        class="control-label"><?= $input->label ?></label>
                                                </div>
                                                <div class="col-md-8 col-6 mb-sm-0">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i
                                                                    class="<?= $input->icon ?>"></i></span>
                                                        </div>
                                                        <select class="form-control" data-placeholder="Jenis Kelamin"
                                                            name="jenis_kelamin" required>
                                                            <option value="" <?= $input->value == null ? 'selected' : '' ?>
                                                                disabled>Pilih Jenis Kelamin
                                                            </option>
                                                            <?php
                                                            $arrJk = ["L" => "Laki-laki", "P" => "Perempuan"];
                                                            foreach ($arrJk as $key => $jk): ?>
                                                                <option value="<?= $key ?>" <?= $key == $input->value ? 'selected' : '' ?>><?= $jk ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($input->name == 'agama'): ?>
                                            <div class="mb-3 row">
                                                <div class="col-md-4 mb-sm-0">
                                                    <label for="<?= $input->name ?>"
                                                        class="control-label"><?= $input->label ?></label>
                                                </div>
                                                <div class="col-md-8 mb-sm-0">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i
                                                                    class="<?= $input->icon ?>"></i></span>
                                                        </div>
                                                        <?php
                                                        $arrAgama = ["Islam", "Katolik", "Protestan", "Hindu", "Budha", "Konghucu"];
                                                        ?>
                                                        <select class="form-control" id="agama"
                                                            data-placeholder="Pilih Agama yang dianut" name="agama">
                                                            <option value="0" disabled>Pilih Agama yang dianut</option>
                                                            <?php foreach ($arrAgama as $agama): ?>
                                                                <option value="<?= $agama ?>" <?= $agama == $input->value ? 'selected' : '' ?>><?= $agama ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-3 row">
                                                <div class="col-md-4 mb-sm-0">
                                                    <label for="<?= $input->name ?>"
                                                        class="control-label"><?= $input->label ?></label>
                                                </div>
                                                <div class="col-md-8 mb-sm-0">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i
                                                                    class="<?= $input->icon ?>"></i></span>
                                                        </div>
                                                        <input value="<?= trim($input->value ?? '') ?>" id="<?= $input->name ?>"
                                                            type="<?= $input->type ?>" class="form-control"
                                                            name="<?= $input->name ?>" placeholder="<?= $input->label ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; endforeach; ?>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-for-profile" role="tabpanel"
                                    aria-labelledby="custom-tabs-for-profile-tab">
                                    <?php foreach ($input_alamat as $alamat): ?>
                                        <div class="mb-3 row">
                                            <div class="col-md-4 mb-sm-0">
                                                <label for="<?= $alamat->name ?>"
                                                    class="control-label"><?= $alamat->label ?></label>
                                            </div>
                                            <div class="col-md-8 mb-sm-0">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="<?= $alamat->icon ?>"></i></span>
                                                    </div>
                                                    <input value="<?= trim($alamat->value ?? '') ?>"
                                                        id="<?= $alamat->name ?>" type="<?= $alamat->type ?>"
                                                        class="form-control" name="<?= $alamat->name ?>"
                                                        placeholder="<?= $alamat->label ?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-for-document" role="tabpanel"
                                    aria-labelledby="custom-tabs-for-document-tab">
                                    <div class="mb-3 row">
                                        <?php
                                        foreach ($input_dokumen as $doc): ?>
                                            <div class="col-12 mb-3">
                                                <span class="text-bold"><?= $doc->label ?></span>
                                                <input type="file" class="d-none" id="<?= $doc->id ?>"
                                                    name="<?= $doc->name ?>" accept="image/*">
                                                <div class="thumbnail-preview" id="thumbnail_<?= $doc->id ?>">
                                                    <div class="add-photo-button" id="add_<?= $doc->id ?>">+</div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                        <div class="card-footer alert-default-danger">
                            <small><b>INFO: </b><i>Semua kolom PROFIL wajib diisi</i></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <div class="modal fade" id="editFotoModal" tabindex="-1" role="dialog" aria-labelledby="editFotoLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Edit Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <?= form_open_multipart('', array('id' => 'set-foto-profile')) ?>
                    <div class="mb-3 pb-2">
                        <label for="foto-profile">Foto Profil</label>
                        <input type="file" id="foto-profile" name="foto" class="dropify" data-max-file-size-preview="2M"
                            data-allowed-file-extensions="jpg jpeg png"
                            data-default-file="<?= base_url() . $guru->foto ?>" />
                    </div>
                    <?= form_close() ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    </div>

    <div class="modal fade" id="modal-signature" tabindex="-1" role="dialog" aria-labelledby="modalSignatureLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSignatureLabel">Buat Tanda Tangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body text-center">
                    <canvas id="sig-canvas" width="400" height="200">
                        Browser Anda tidak mendukung Canvas.
                    </canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="sig-clear">Bersihkan</button>
                    <button type="button" class="btn btn-primary" id="sig-submit">Simpan Tanda Tangan</button>
                </div>
            </div>
        </div>
    </div>

    <?= form_open('', array('id' => 'updatelogin'), array('id_guru' => $guru->id_guru)) ?>
    <div class="modal fade" id="editLoginModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Username / Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend w-40">
                            <span class="input-group-text">Username</span>
                        </div>
                        <input type="text" class="form-control" name="username" value="<?= $guru->username ?>"
                            placeholder="Username">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend w-40">
                            <span class="input-group-text">Password Lama</span>
                        </div>
                        <input class="form-control" name="old" value="<?= $guru->password ?>" placeholder="Username"
                            readonly>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend w-40">
                            <span class="input-group-text">Password Baru</span>
                        </div>
                        <input type="text" name="new" class="form-control" placeholder="Password Baru">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend w-40">
                            <span class="input-group-text">Konfirmasi Password</span>
                        </div>
                        <input type="text" name="new_confirm" class="form-control"
                            placeholder="Konfirmasi Password Baru" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger float-end" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning float-end">Ganti Password</button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <!-- Fullscreen Preview Modal Structure -->
    <div class="fullscreen-overlay" id="fullscreenPreviewOverlay">
        <div class="fullscreen-content">
            <img id="fullscreenImage" src="" alt="Full-size preview">
            <div class="fullscreen-button-group">
                <button class="btn fullscreen-btn-close-button" id="fullscreenCloseButton"><i
                        class="bi bi-x-lg"></i></button>
                <a href="#" id="fullscreenDownloadButton" class="fullscreen-btn-close-button btn" download><i
                        class="bi bi-download"></i></a>
                <button class="btn fullscreen-btn-close-button" id="fullscreenDeleteButton"><i
                        class="bi bi-trash"></i></button>
            </div>
        </div>
    </div>

    <script>
        var fotoProfile = '';
        var idGuru = '<?= $guru->id_guru ?>';
        var src = '<?= $guru->foto ?>';
        var dokumen = JSON.parse('<?= json_encode(json_decode($guru->dokumen ?? '{}')) ?>');
        $(document).ready(function () {

            function showBootstrapAlert(type, message) {
                const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show fixed-top m-3 shadow" role="alert" style="z-index: 9999; max-width: 500px; left: 50%; transform: translateX(-50%);">
                        <i class="bi ${icon} me-2"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                $('body').append(alertHtml);
                // Auto close after 3 seconds
                setTimeout(() => {
                    $('.alert-dismissible').alert('close');
                }, 3000);
            }

            // --- Signature Pad Logic ---
            var canvas = document.getElementById("sig-canvas");
            var ctx = canvas.getContext("2d");
            var drawing = false;
            var mousePos = { x: 0, y: 0 };
            var lastPos = mousePos;

            $('#btn-create-ttd').click(function () {
                $('#modal-signature').modal('show');
            });

            $('#modal-signature').on('shown.bs.modal', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear on open
            });

            canvas.addEventListener("mousedown", function (e) {
                drawing = true;
                lastPos = getMousePos(canvas, e);
            }, false);

            canvas.addEventListener("mouseup", function () {
                drawing = false;
            }, false);

            canvas.addEventListener("mousemove", function (e) {
                mousePos = getMousePos(canvas, e);
                renderCanvas();
            }, false);

            // Touch support for mobile
            canvas.addEventListener("touchstart", function (e) {
                mousePos = getTouchPos(canvas, e);
                var touch = e.touches[0];
                var mouseEvent = new MouseEvent("mousedown", {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
                canvas.dispatchEvent(mouseEvent);
                e.preventDefault(); // Prevent scrolling
            }, false);

            canvas.addEventListener("touchend", function () {
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
                e.preventDefault(); // Prevent scrolling
            }, false);

            function getMousePos(canvasDom, mouseEvent) {
                var rect = canvasDom.getBoundingClientRect();
                return {
                    x: mouseEvent.clientX - rect.left,
                    y: mouseEvent.clientY - rect.top
                };
            }

            function getTouchPos(canvasDom, touchEvent) {
                var rect = canvasDom.getBoundingClientRect();
                return {
                    x: touchEvent.touches[0].clientX - rect.left,
                    y: touchEvent.touches[0].clientY - rect.top
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

            $('#sig-clear').click(function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });

            $('#sig-submit').click(function () {
                var dataUrl = canvas.toDataURL("image/png");

                $.ajax({
                    url: base_url + 'guru/save_signature',
                    type: 'POST',
                    data: {
                        id_guru: idGuru,
                        image: dataUrl
                    },
                    dataType: 'json',
                    beforeSend: function () {
                        $('#sig-submit').attr('disabled', true).text('Menyimpan...');
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#img-ttd-preview').attr('src', response.src).removeClass('d-none');
                            $('#ttd-placeholder').addClass('d-none');
                            $('#btn-delete-ttd').removeClass('d-none');
                            showBootstrapAlert('success', response.message);
                            $('#modal-signature').modal('hide');
                        } else {
                            showBootstrapAlert('danger', response.message);
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
                if (confirm("Hapus Tanda Tangan? File tanda tangan akan dihapus permanen!")) {
                    $.ajax({
                        url: base_url + 'guru/delete_ttd',
                        type: 'POST',
                        data: { id_guru: idGuru },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status) {
                                $('#img-ttd-preview').addClass('d-none').attr('src', '');
                                $('#ttd-placeholder').removeClass('d-none');
                                $('#btn-delete-ttd').addClass('d-none');
                                showBootstrapAlert('success', response.message);
                            } else {
                                showBootstrapAlert('danger', response.message);
                            }
                        },
                        error: function () {
                            showBootstrapAlert('danger', 'Gagal menghapus tanda tangan');
                        }
                    });
                }
            });
            // --- End Signature Pad Logic ---
            $('#tgl_lahir').datetimepicker({
                icons:
                {
                    next: 'bi bi-angle-right',
                    previous: 'bi bi-angle-left'
                },
                format: 'Y-m-d',
                timepicker: false,
                scrollInput: false,
                scrollMonth: false,
                //disabledWeekDays: [0],
                widgetPositioning: {
                    horizontal: 'left',
                    vertical: 'bottom'
                }
            });

            $('#foto-profile').attr("data-default-file", base_url + src);
            var drEvent = $('.dropify').dropify({
                messages: {
                    'default': 'Seret foto kesini atau klik',
                    'replace': 'Seret atau klik<br>untuk mengganti foto',
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
                src = $(event.currentTarget).data('default-file');
                deleteImage(false);
                fotoProfile = '';
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

            $('#editFotoModal').on('hidden.bs.modal', function (e) {
                window.location.reload();
            });

            let selectedKtp = [];
            let selectedKk = [];
            let selectedSert = [];
            function pecahDokumen() {
                selectedKtp = [];
                selectedKk = [];
                selectedSert = [];

                for (const category in dokumen) {
                    if (dokumen.hasOwnProperty(category)) {
                        if (category === 'ktp') {
                            const url = base_url + dokumen?.ktp
                            selectedKtp.push({
                                id: crypto.randomUUID(), // Unique ID for each item
                                type: 'url',
                                url: url,
                                name: url?.substring(url?.lastIndexOf('/') + 1), // Extract filename for display/alt
                                category: category // Keep category info if useful
                            });
                        } else if (category === 'kk') {
                            const url = base_url + dokumen?.kk
                            selectedKk.push({
                                id: crypto.randomUUID(),
                                type: 'url',
                                url: url,
                                name: url?.substring(url?.lastIndexOf('/') + 1),
                                category: category
                            });
                        } else if (category === 'sertifikat') {
                            dokumen[category]?.forEach(url => {
                                selectedSert.push({
                                    id: crypto.randomUUID(),
                                    type: 'url',
                                    url: base_url + url,
                                    name: url.substring(url.lastIndexOf('/') + 1),
                                    category: category
                                });
                            });
                        }
                    }
                }
            }
            pecahDokumen();

            $('#submit-profil').click(function (e) {
                const formData = new FormData($('#formguru')[0]);
                if (selectedSert.length > 0) {
                    selectedSert.forEach((item, index) => {
                        if (item.type === 'file') {
                            formData.append(`serti[]`, item.file, item.name);
                        }
                    });
                }
                if (selectedKtp.length > 0 && selectedKtp[0].type === 'file') {
                    const itemKtp = selectedKtp[0];
                    formData.append(`ktp_id`, itemKtp.file, itemKtp.name);
                }
                if (selectedKk.length > 0 && selectedKk[0].type === 'file') {
                    const itemKk = selectedKk[0];
                    formData.append(`kk_id`, itemKk.file, itemKk.name);
                }

                $.ajax({
                    url: base_url + "guru/save",
                    type: "POST",
                    dataType: "JSON",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (data) {
                        console.log(data);
                        if (data.status) {
                            showBootstrapAlert('success', 'Profile berhasil disimpan');
                            //window.location.reload();
                            dokumen = data.dokumen
                            pecahDokumen();
                            renderThumbnails(thumbnailKtp, selectedKtp, addKtp, 1);
                            renderThumbnails(thumbnailKk, selectedKk, addKk, 2);
                            renderThumbnails(thumbnailSert, selectedSert, addSert, 3);
                        } else {
                            showBootstrapAlert('danger', (data?.errors?.nip || '') + " " + (data?.errors?.nama_guru || ''));
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        showBootstrapAlert('danger', 'Data Tidak Tersimpan');
                    }
                });
            });

            $('#updatelogin').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var dataPost = $(this).serialize();

                $('#editLoginModal').modal('hide').data('bs.modal', null);
                $('#editLoginModal').on('hidden', function () {
                    $(this).data('modal', null);
                });

                $.ajax({
                    url: base_url + "userguru/editlogin",
                    type: "POST",
                    dataType: "JSON",
                    data: dataPost,
                    success: function (data) {
                        console.log(data);
                        if (data.status) {
                            showBootstrapAlert('success', data.text);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            var html = '<ul>';
                            if (data.errors.username != null && data.errors.username !== "") {
                                html += '<li>' + data.errors.username + '</li>';
                            }
                            if (data.errors.old != null && data.errors.old !== "") {
                                html += '<li>' + data.errors.old + '</li>';
                            }
                            if (data.errors.new != null && data.errors.new !== "") {
                                html += '<li>' + data.errors.new + '</li>';
                            }
                            if (data.errors.new_confirm != null && data.errors.new_confirm !== "") {
                                html += '<li>' + data.errors.new_confirm + '</li>';
                            }
                            html += '</ul>';
                            showBootstrapAlert('danger', html);
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        showBootstrapAlert('danger', 'Data Tidak Tersimpan');
                    }
                });
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
                        fotoProfile = data.src;
                    },
                    error: function (e) {
                        console.log("error", e.responseText);
                        $.toast({
                            heading: "ERROR!!",
                            text: "file tidak terbaca",
                            icon: 'error',
                            showHideTransition: 'fade',
                            allowToastClose: true,
                            hideAfter: 5000,
                            position: 'top-right'
                        });
                    }
                });
            }

            $("#foto-profile").change(function () {
                var input = $(this)[0];
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.readAsDataURL(input.files[0]);

                    var form = new FormData($('#set-foto-profile')[0]);
                    uploadAttach(base_url + 'guru/uploadfile/' + idGuru, form);
                }
            });

            const ktpInput = document.getElementById('ktp_id');
            const kkInput = document.getElementById('kk_id');
            const sertInput = document.getElementById('sertifikat');

            const thumbnailKtp = document.getElementById('thumbnail_ktp_id');
            const thumbnailKk = document.getElementById('thumbnail_kk_id');
            const thumbnailSert = document.getElementById('thumbnail_sertifikat');

            const addKtp = document.getElementById('add_ktp_id');
            const addKk = document.getElementById('add_kk_id');
            const addSert = document.getElementById('add_sertifikat');

            const fullscreenPreviewOverlay = document.getElementById('fullscreenPreviewOverlay');
            const fullscreenImage = document.getElementById('fullscreenImage');
            const fullscreenCloseButton = document.getElementById('fullscreenCloseButton');
            const fullscreenDownloadButton = document.getElementById('fullscreenDownloadButton');
            const fullscreenDeleteButton = document.getElementById('fullscreenDeleteButton');

            const addBtn = $('.add-photo-button');
            addBtn.on('click', function () {
                const id = (this.id).replace('add_', '')
                $(`#${id}`).click();
            });

            ktpInput.addEventListener('change', function (event) {
                for (const file of this.files) {
                    selectedKtp.push({
                        id: crypto.randomUUID(), // Assign unique ID to new file
                        type: 'file',
                        file: file, // The actual File object
                        name: file.name // Original filename
                    });
                }
                renderThumbnails(thumbnailKtp, selectedKtp, addKtp, 1);
            });

            kkInput.addEventListener('change', function (event) {
                for (const file of this.files) {
                    selectedKk.push({
                        id: crypto.randomUUID(), // Assign unique ID to new file
                        type: 'file',
                        file: file, // The actual File object
                        name: file.name // Original filename
                    });
                }
                renderThumbnails(thumbnailKk, selectedKk, addKk, 2);
            });

            sertInput.addEventListener('change', function (event) {
                for (const file of this.files) {
                    selectedSert.push({
                        id: crypto.randomUUID(), // Assign unique ID to new file
                        type: 'file',
                        file: file, // The actual File object
                        name: file.name // Original filename
                    });
                }
                renderThumbnails(thumbnailSert, selectedSert, addSert, 3);
            });

            function renderThumbnails(thumbnail, files, btn, type) {
                if (thumbnail) {
                    const currentElements = Array.from(thumbnail?.children);
                    currentElements.forEach(el => {
                        if (!el.classList.contains('add-photo-button')) {
                            thumbnail?.removeChild(el);
                        }
                    });
                }

                if (type < 3) {
                    if (files?.length === 0) {
                        btn.classList.remove('d-none');
                    } else {
                        btn.classList.add('d-none')
                    }
                }

                if (files?.length === 0) {
                    btn.style.order = 0;
                    return;
                }

                files?.forEach((item, index) => {
                    const thumbnailItem = document.createElement('div');
                    thumbnailItem.className = 'thumbnail-item';
                    thumbnailItem.dataset.id = item.id;
                    const img = document.createElement('img');
                    let previewSrc = '';
                    if (item.type === 'file') {
                        previewSrc = URL.createObjectURL(item.file);
                        img.alt = `Thumbnail of ${item.name}`;
                        img.onload = () => URL.revokeObjectURL(previewSrc);
                    } else if (item.type === 'url') {
                        previewSrc = item.url;
                        img.alt = `Thumbnail of ${item.name}`;
                        img.onerror = function () {
                            img.src = 'https://placehold.co/100x100/eeeeee/aaaaaa?text=Error'; // Placeholder image
                            img.alt = 'Gambar tidak dapat dimuat';
                        };
                    }
                    img.src = previewSrc;
                    img.addEventListener('click', () => openFullscreenPreview(item));

                    const thumbBtnGroup = document.createElement('div');
                    thumbBtnGroup.className = 'thumbnail-btn-group';

                    const deleteButton = document.createElement('button');
                    deleteButton.type = 'button';
                    deleteButton.className = 'btn btn-circle-micro btn-danger mt-2';
                    deleteButton.innerHTML = '<i class="bi bi-trash text-sm"></i>';
                    deleteButton.title = 'Hapus foto ini';
                    deleteButton.addEventListener('click', (event) => {
                        event.stopPropagation(); // Prevent click from triggering fullscreen preview
                        // Find the actual index of the item to remove using its unique ID
                        const idToDelete = thumbnailItem.dataset.id;
                        const actualIndex = files.findIndex(f => f.id === idToDelete);
                        if (actualIndex !== -1) {
                            removeFile(actualIndex, type);
                        }
                    });

                    const downloadButtonThumb = document.createElement('a');
                    downloadButtonThumb.type = 'button'
                    downloadButtonThumb.className = 'btn btn-circle-micro btn-success';
                    downloadButtonThumb.innerHTML = '<i class="bi bi-download"></i>';
                    downloadButtonThumb.title = 'Download dokumen ini';
                    downloadButtonThumb.href = previewSrc; // Set href to the image source
                    downloadButtonThumb.download = item.name; // Set download attribute for filename
                    downloadButtonThumb.addEventListener('click', (event) => {
                        event.stopPropagation(); // Prevent click from triggering fullscreen preview
                    });
                    thumbBtnGroup.appendChild(downloadButtonThumb);
                    thumbBtnGroup.appendChild(deleteButton);

                    thumbnailItem.appendChild(img);
                    thumbnailItem.appendChild(thumbBtnGroup);
                    //thumbnailItem.appendChild(downloadButtonThumb);
                    if (type === 3) {
                        thumbnail?.insertBefore(thumbnailItem, btn);
                    } else {
                        thumbnail?.appendChild(thumbnailItem);
                    }
                });
                thumbnail?.appendChild(btn);
            }

            function openFullscreenPreview(item) { // Now accepts the entire item object
                let displaySrc = '';
                let fileName = item.name || 'image.jpg';
                if (item.type === 'file') {
                    displaySrc = URL.createObjectURL(item.file);
                    // Revoke the temporary URL when the fullscreen image is loaded
                    fullscreenImage.onload = () => {
                        URL.revokeObjectURL(displaySrc);
                    };
                    fullscreenImage.onerror = function () {
                        console.error(`Gagal memuat gambar layar penuh dari Blob URL: ${displaySrc}`);
                        fullscreenImage.src = 'https://placehold.co/800x600/eeeeee/aaaaaa?text=Gambar+Tidak+Tersedia';
                        fullscreenImage.alt = 'Gambar tidak dapat dimuat';
                    };
                } else if (item.type === 'url') {
                    displaySrc = item.url;
                    fullscreenImage.onload = () => { };
                    // Handle broken image links for fullscreen
                    fullscreenImage.onerror = function () {
                        console.error(`Gagal memuat gambar layar penuh dari URL: ${item.url}`);
                        fullscreenImage.src = 'https://placehold.co/800x600/eeeeee/aaaaaa?text=Gambar+Tidak+Tersedia'; // Placeholder image for fullscreen
                        fullscreenImage.alt = 'Gambar tidak dapat dimuat';
                    };
                }

                fullscreenImage.src = displaySrc; // Set the source for the fullscreen image
                fullscreenImage.alt = `Full-size preview of ${fileName}`;

                fullscreenDownloadButton.href = displaySrc;
                fullscreenDownloadButton.download = fileName;
                fullscreenDeleteButton.dataset.src = displaySrc;

                fullscreenPreviewOverlay.classList.add('visible');
            }

            function closeFullscreenPreview() {
                fullscreenPreviewOverlay.classList.remove('visible');
                fullscreenImage.src = ''; // Clear image source
                // Remove any temporary onload/onerror handlers to prevent memory leaks/unexpected behavior
                fullscreenImage.onload = null;
                fullscreenImage.onerror = null;
                fullscreenDownloadButton.href = '#'; // Reset download button href
                fullscreenDownloadButton.download = '';
            }

            fullscreenCloseButton.addEventListener('click', closeFullscreenPreview);
            fullscreenPreviewOverlay.addEventListener('click', (event) => {
                // Close if clicking on the overlay itself, not the image
                if (event.target === fullscreenPreviewOverlay) {
                    closeFullscreenPreview();
                }
            });

            renderThumbnails(thumbnailKtp, selectedKtp, addKtp, 1);
            renderThumbnails(thumbnailKk, selectedKk, addKk, 2);
            renderThumbnails(thumbnailSert, selectedSert, addSert, 3);

            function removeFile(indexToRemove, type) {
                if (confirm("Hapus dokumen? Dokumen akan dihapus permanen!")) {
                    if (type === 1) {
                        if (selectedKtp[indexToRemove].type === 'file') {
                            if (selectedKtp[indexToRemove]?.urlObject) {
                                URL.revokeObjectURL(selectedKtp[indexToRemove].urlObject);
                            }
                            selectedKtp.splice(indexToRemove, 1);
                            renderThumbnails(thumbnailKtp, selectedKtp, addKtp, type);
                        } else {
                            deleteDokumen(selectedKtp[indexToRemove].url);
                        }
                    } else if (type === 2) {
                        if (selectedKk[indexToRemove].type === 'file') {
                            if (selectedKk[indexToRemove]?.urlObject) {
                                URL.revokeObjectURL(selectedKk[indexToRemove].urlObject);
                            }
                            selectedKk.splice(indexToRemove, 1);
                            renderThumbnails(thumbnailKk, selectedKk, addKk, type);
                        } else {
                            deleteDokumen(selectedKk[indexToRemove].url);
                        }
                    } else if (type === 3) {
                        if (selectedSert[indexToRemove].type === 'file') {
                            if (selectedSert[indexToRemove]?.urlObject) {
                                URL.revokeObjectURL(selectedSert[indexToRemove].urlObject);
                            }
                            selectedSert.splice(indexToRemove, 1);
                            renderThumbnails(thumbnailSert, selectedSert, addSert, type);
                        } else {
                            deleteDokumen(selectedSert[indexToRemove].url);
                        }
                    }
                }
            }

            fullscreenDeleteButton.addEventListener('click', function () {
                const srcToDelete = this.dataset.src;
                if (confirm("Hapus dokumen? Dokumen akan dihapus permanen!")) {
                    deleteDokumen(srcToDelete);
                }
            });

            function deleteDokumen(src) {
                $.ajax({
                    data: { src: src },
                    type: "GET",
                    url: base_url + "guru/delete_dokumen/" + idGuru,
                    cache: false,
                    success: function (response) {
                        closeFullscreenPreview()
                        console.log(response);
                        //window.location.reload();
                        dokumen = response.dokumen
                        pecahDokumen();
                        renderThumbnails(thumbnailKtp, selectedKtp, addKtp, 1);
                        renderThumbnails(thumbnailKk, selectedKk, addKk, 2);
                        renderThumbnails(thumbnailSert, selectedSert, addSert, 3);
                    }
                });
            }
        });

        function deleteImage(fromBtn) {
            $.ajax({
                data: { src: src },
                type: "GET",
                url: base_url + "guru/deletefile/" + idGuru,
                cache: false,
                success: function (response) {
                    console.log(response);
                    if (fromBtn) {
                        window.location.reload();
                    }
                }
            });
        }
    </script>