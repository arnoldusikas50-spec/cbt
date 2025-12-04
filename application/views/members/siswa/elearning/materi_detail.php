<?php


$ada_nilai = $logs != null && $logs->nilai != null && $logs->nilai != '0';
?>

<div class="content-wrapper" style="margin-top: 30px;">
    <div class="sticky">
    </div>
    <section class="content overlap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- <?php $this->load->view('members/siswa/templates/top'); ?> --> <!-- ikasmedia -->
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="media card-title" style="line-height: 1.2">
                                <?php
                                $avatar = file_exists($materi->foto) ? $materi->foto : 'assets/img/guru.png';
                                ?>
                                <img class="img-circle media-left" src="<?= base_url($avatar) ?>" width="60"
                                     height="60"/>
                                <div class="media-body ml-4">
                                    <span><b><?= $materi->nama_guru ?></b></span>
                                    <br/>
                                    <span class="text-sm"> <?= $materi->nama_mapel ?> </span>
                                    <br/>
                                    <span class="text-sm">Kelas <?= $siswa->nama_kelas ?> </span>
                                </div>
                            </div>
                        </div>
                        <?php
                        $dom = new DOMDocument();
                        @$dom->loadHTML($materi->isi_materi);
                        $images = $dom->getElementsByTagName('img');
                        foreach ($images as $image) {
                            $curSrc = $image->getAttribute('src');
                            if (strpos($curSrc, 'http') !== false) {
                                $pathUpload = 'uploads';
                                $forReplace = explode($pathUpload, $curSrc);
                                $image->setAttribute('src', base_url() . $pathUpload . $forReplace[1]);
                            } else {
                                $image->setAttribute('src', base_url() . $curSrc);
                            }
                        }
                        $isi_materi = $dom->saveHTML();
                        ?>
                        <div class="card-body">
                            <?php if ($ada_nilai) : ?>
                                <div class="col-lg-12 p-0">
                                    <div class="alert align-content-center alert-default-primary" role="alert">
                                        <div class="row">
                                            <div class="col-12 col-md-6 mb-3">
                                                <?= $judul ?> ini sudah dikerjakan dan sudah mendapat nilai.
                                                <br>Hanya untuk melihat saja dan tidak bisa dikerjakan ulang!
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <table class="table table-borderless w-100 table-sm border border-primary">
                                                    <tr>
                                                        <td class="align-middle">Dikerjakan</td>
                                                        <td class="align-middle">: <?= $logs->log_time != null ? singkat_tanggal(date('d M Y - H:i', strtotime($logs->log_time ?? ''))) : '-' ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="align-middle">Nilai</td>
                                                        <td class="align-middle text-bold">: <?= $ada_nilai ? $logs->nilai : '-' ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="">
                                                            <span class="text-bold"><i>Catatan Guru:</i></span>
                                                            <br><?= $logs->catatan ?? '-' ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <h3 class="text-center"><?= $materi->judul_materi ?></h3>
                            <div class="text-justify"><?= $isi_materi ?></div>
                        </div>
                    </div>
                </div>
                <?php
                $files = $materi->file ?? [];
                if (count($files) > 0) : ?>
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="media card-title text-bold">File Pendukung</div>
                        </div>
                        <div class="card-body">
                            <ul class="clearfix media-list">
                                <?php foreach ($files as $file) :
                                    $src = $file->src ?? '';
                                    $name = htmlspecialchars($file->name ?? 'Unknown');
                                    $temp = explode('.', $src);
                                    $extension = strtolower(end($temp));

                                    $path = 'uploads/materi/';
                                    $filename = rawurlencode(basename($file->src));
                                    $full_url = base_url($path . $filename);
                                    ?>
                                    <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
                                    <li>
                                        <img src="<?= base_url($src) ?>" data-target="#view-modal" data-toggle="modal"
                                             data-src="<?= base_url($src) ?>" data-type="<?= $file->type ?? '' ?>"/>
                                        <div class="title-thumb"><?= $name ?></div>
                                    </li>
                                <?php elseif (in_array($extension, ['mp4', 'avi', 'mpeg', 'mpg'])): ?>
                                    <li>
                                        <img src="<?= base_url('/assets/app/img/icon_play_black.png') ?>"
                                             onClick="parent.open('<?= $full_url ?>')">
                                        <div class="title-thumb"><?= $name ?></div>
                                    </li>
                                <?php elseif (in_array($extension, ['xls', 'xlsx'])): ?>
                                    <li>
                                        <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                            <img src="<?= base_url('/assets/app/img/excel-icon.png') ?>" onclick="dialogDownload()">
                                            <div class="title-thumb"><?= $name ?></div>
                                        </a>
                                    </li>
                                <?php elseif (in_array($extension, ['doc', 'docx'])): ?>
                                    <li>
                                        <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                            <img src="<?= base_url('/assets/app/img/word-icon.png') ?>" onclick="dialogDownload()">
                                            <div class="title-thumb"><?= $name ?></div>
                                        </a>
                                    </li>
                                <?php elseif ($extension == 'pdf'): ?>
                                    <li>
                                        <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                            <img src="<?= base_url('/assets/app/img/pdf-icon.png') ?>" onclick="dialogDownload()">
                                            <div class="title-thumb"><?= $name ?></div>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                            <img src="<?= base_url('/assets/app/img/document-icon.svg') ?>" style="padding: 8px"
                                                 onclick="dialogDownload()">
                                            <div class="title-thumb"><?= $name ?></div>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-body">
                            <div id="input-materi" class="row">
                                <div class="col-12 mb-3">
                                    <?php if ($ada_nilai) :?>
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title text-bold">Hasil <?= $judul ?> siswa</h6>
                                            </div>
                                            <div class="card-body">
                                                <div><?= $logs != null ? $logs->text : '' ?></div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?= form_open('', array('id' => 'formhasil')) ?>
                                        <label>Hasil <?= $judul ?> siswa</label>
                                        <textarea id="text-materi" name='isi_materi' class='editor'
                                                  data-id="<?= $this->security->get_csrf_hash() ?>"
                                                  data-name="<?= $this->security->get_csrf_token_name() ?>"
                                                  rows='10' cols='80'
                                                  style='width:100%;'><?= $logs != null ? $logs->text : '' ?></textarea>
                                        <button type="submit" class="btn btn-primary float-right ml-1"><i
                                                    class="fa fa-upload"></i> KIRIM
                                        </button>
                                        <?= form_close(); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <div class="card card-default">
                                        <?php if (!$ada_nilai) : ?>
                                            <div class="card-header">
                                                <h6 class="card-title text-bold">Tambahkan File</h6>
                                            </div>
                                            <div class="card-body">
                                                <?= form_open_multipart('', array('id' => 'formfile')) ?>
                                                <div class="form-group">
                                                    <ul id="media-upload" class="clearfix media-list">
                                                        <li class="myupload">
														<span>
															<i class="fa fa-plus" aria-hidden="true"></i>
															<input name="file_uploads" type="file" id="picupload"
                                                                   class="picupload">
                                                            <input type="hidden" name="max-size" value="2048">
														</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <?= form_close(); ?>
                                            </div>
                                            <div class="card-footer">
                                                <i class="fa fa-info-circle"></i> File yang akan ditambahkan harus diberi
                                                nama siswa!
                                                <br>File yang bisa ditambahkan:
                                                <br>
                                                <table class="ml-4">
                                                    <tr>
                                                        <td>Gambar</td>
                                                        <td> :</td>
                                                        <td>jpg, png, bmp, gif</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Video</td>
                                                        <td> :</td>
                                                        <td>mp4, avi, mpeg</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Suara</td>
                                                        <td> :</td>
                                                        <td>mp3, wav</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Dokumen</td>
                                                        <td> :</td>
                                                        <td>pdf, word, excel, power point</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="overlay d-none"><i class="fas fa-2x fa-sync-alt fa-spin mr-4"></i>
                                                Memuat file ...
                                            </div>
                                        <?php else: ?>
                                            <div class="card-header">
                                                <h6 class="card-title text-bold">File Siswa</h6>
                                            </div>
                                            <div class="card-body">
                                                <ul class="clearfix media-list">
                                                    <?php foreach ($logs->file as $file) :
                                                        $src = $file->src ?? '';
                                                        $name = htmlspecialchars($file->name ?? 'Unknown');
                                                        $temp = explode('.', $src);
                                                        $extension = strtolower(end($temp));

                                                        $path = 'uploads/materi/';
                                                        $filename = rawurlencode(basename($file->src));
                                                        $full_url = base_url($path . $filename);
                                                        ?>
                                                        <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) : ?>
                                                        <li>
                                                            <img src="<?= base_url($src) ?>" data-target="#view-modal" data-toggle="modal"
                                                                 data-src="<?= base_url($src) ?>" data-type="<?= $file->type ?? '' ?>"/>
                                                            <div class="title-thumb"><?= $name ?></div>
                                                        </li>
                                                    <?php elseif (in_array($extension, ['mp4', 'avi', 'mpeg', 'mpg'])): ?>
                                                        <li>
                                                            <img src="<?= base_url('/assets/app/img/icon_play_black.png') ?>"
                                                                 onClick="parent.open('<?= $full_url ?>')">
                                                            <div class="title-thumb"><?= $name ?></div>
                                                        </li>
                                                    <?php elseif (in_array($extension, ['xls', 'xlsx'])): ?>
                                                        <li>
                                                            <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                                                <img src="<?= base_url('/assets/app/img/excel-icon.png') ?>" onclick="dialogDownload()">
                                                                <div class="title-thumb"><?= $name ?></div>
                                                            </a>
                                                        </li>
                                                    <?php elseif (in_array($extension, ['doc', 'docx'])): ?>
                                                        <li>
                                                            <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                                                <img src="<?= base_url('/assets/app/img/word-icon.png') ?>" onclick="dialogDownload()">
                                                                <div class="title-thumb"><?= $name ?></div>
                                                            </a>
                                                        </li>
                                                    <?php elseif ($extension == 'pdf'): ?>
                                                        <li>
                                                            <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                                                <img src="<?= base_url('/assets/app/img/pdf-icon.png') ?>" onclick="dialogDownload()">
                                                                <div class="title-thumb"><?= $name ?></div>
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <a href="<?= base_url($src) ?>" download="<?= $name ?>">
                                                                <img src="<?= base_url('/assets/app/img/document-icon.svg') ?>" style="padding: 8px"
                                                                     onclick="dialogDownload()">
                                                                <div class="title-thumb"><?= $name ?></div>
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="view-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lihat File</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div id="konten-dialog" class="col-12"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var adaNilai = '<?=$ada_nilai?>' == '1';
    var logMateri = JSON.parse(JSON.stringify(<?= json_encode($logs) ?>));
    var logMulai = '<?= $logs != null && ($logs->ulang == null || ($logs->ulang != null && $logs->ulang == '0')) && $logs->log_time != null ? $logs->log_time : '' ?>';
    var logSelesai = '<?= $logs != null && $logs->finish_time != null ? $logs->finish_time : '' ?>';
    var idSiswa = '<?= $siswa->id_siswa ?>';
    var idKjm = '<?= $materi->id_kjm ?>';
    var mapel = '<?= $materi->id_mapel ?>';

    var dataFiles = [];
    var arrFileAttach = logMateri != null && logMateri.file != null ? logMateri.file : [];
    //console.log('files', arrFileAttach);
    dataFiles = $.merge(dataFiles, arrFileAttach);

    $(document).ready(function () {
        ajaxcsrf();
        $('.editor').summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                //['insert', ['link', 'picture', 'video', 'file', 'removeFile']],
                //['view', ['fullscreen', 'codeview', 'help']],
            ],
            placeholder: 'Tulis keterangan disini',
            tabsize: 2,
            minHeight: 200,
        });

        console.log('logMulai', logMulai);
        if (logMulai === '') {
            setTimeout(function () {
                $.ajax({
                    type: "GET",
                    url: base_url + "kbm/savelogmateri?id_siswa=" + idSiswa + '&id_kjm=' + idKjm + '&mapel=' + mapel,
                    success: function (response) {
                        console.log(response);
                    }
                });
            }, 1000);
        }

        createPreviewFile();

        $('#formhasil').submit(function (e) {
            e.stopPropagation();
            e.preventDefault();

            if (adaNilai) return;

            swal.fire({
                text: "Mengirim hasil <?= $judul ?> ...",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            var update = logSelesai === '' ? '0' : '1';
            var dataUpload = $(this).serialize() + '&update=' + update + '&id_siswa=' + idSiswa + '&id_kjm=' + idKjm + '&mapel=' + mapel + '&attach=' + JSON.stringify(dataFiles);

            $.ajax({
                type: 'POST',
                url: base_url + 'kbm/savefilemateriselesai',
                data: dataUpload,
                success: function (data) {
                    console.log('res', data)
                    if (data.status === 'error') {
                        showDangerToast(data.status);
                    } else {
                        setTimeout(function () {
                            window.history.back();
                        }, 1000);
                    }
                }, error: function (data) {
                    showDangerToast('Gagal menyimpan');
                    console.log(data);
                }
            });
        });

        $('body').on('click', '.remove-pic', function () {
            $(this).parent().parent().parent().remove();
            var removeItem = $(this).attr('data-id');

            for (var i = 0; i < dataFiles.length; i++) {
                var cur = dataFiles[i];
                if (cur.name === removeItem) {
                    dataFiles.splice(i, 1);
                    saveFileToDb();
                    deleteImage(cur.src);
                    break;
                }
            }
            console.log(dataFiles);
        });

        $("#picupload").on('change', function (e) {
            if (adaNilai) return;
            var form = new FormData($("#formfile")[0]);
            var maxSize = $("#formfile").find('input[name="max-size"]').val();
            uploadAttach(base_url + 'kbm/uploadfile', form, maxSize);
            //createPreviewFile($(this), e)
        });

        $('#view-modal').on('show.bs.modal', function (e) {
            if (adaNilai) return;
            var src = $(e.relatedTarget).data('src');
            var type = $(e.relatedTarget).data('type');
            var html = '';
            if (type.match('image')) {
                html = '<img src="' + src + '" class="img-fluid"/>';
            } else if (type.match('video')) {
                html = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="' + src + '"></iframe></div>';
            } else {
                html = '<img src="' + base_url + '"/assets/app/img/document_file.png"></div>';
            }
            $('#konten-dialog').html(html);
        });
    });

    function progressHandlingFunction(e) {
        if (e.lengthComputable) {
            //Log current progress
            console.log((e.loaded / e.total * 100) + '%');

            //Reset progress on complete
            if (e.loaded === e.total) {
                console.log("Upload finished.");
            }
        }
    }

    function saveFileToDb() {
        if (adaNilai) return;
        var update = logSelesai === '' ? '0' : '1';
        var dataUpload = $('#formhasil').serialize() + '&update=' + update + '&id_siswa=' + idSiswa + '&id_kjm=' + idKjm + '&attach=' + JSON.stringify(dataFiles);
        console.log(dataUpload);

        $.ajax({
            type: 'POST',
            url: base_url + 'kbm/savefilemateriselesai',
            data: dataUpload,
            success: function (data) {
                showSuccessToast(data.status);
                createPreviewFile();
            }, error: function (data) {
                showDangerToast('Gagal meyimpan gambar');
                console.log(data);
            }
        });
    }

    function uploadAttach(action, data, maxsize) {
        if (adaNilai) return;
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
                console.log('result', data.filename);
                //dataFiles.push(data.src);
                //$('#files-attach').val(JSON.stringify(names_files));
                if (data.status) {
                    var item = {};
                    item ['size'] = data.size;
                    item ["type"] = data.type;
                    item ["src"] = data.src;
                    item ["name"] = data.filename;
                    dataFiles.push(item);
                    console.log(data.type);
                    saveFileToDb();
                } else {
                    swal.fire({
                        title: "Gagal",
                        html: data.src,
                        icon: "error",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "OK"
                    });
                }
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

    function deleteImage(src) {
        if (adaNilai) return;
        $.ajax({
            data: {src: src},
            type: "POST",
            url: base_url + "kbm/deletefile",
            cache: false,
            success: function (response) {
                console.log(response);
            }
        });
    }

    function dialogDownload() {
        showSuccessToast("File telah didownload")
    }

    function createPreviewFile(/*elem, event*/) {
        //var files = event.target.files;
        for (let j = 0; j < dataFiles.length; j++) {
            let file = dataFiles[j];
            console.log('preview', file);
            //names_files.push(elem.get(0).files[j].name);
            var div = document.createElement("li");
            div.setAttribute("data-name", file.name);
            var fsrc = file.src.split('.');
            var ext = fsrc[fsrc.length - 1];

            if (!$('li[data-name="' + file.name + '"]').length) {
                if (file.type.match('image')) {
                    div.innerHTML = "<img src='" + base_url + file.src + "'/>" +
                        "<div  class='post-thumb'>" +
                        "<div class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.name + "' class='remove-pic'>" +
                        "<i class='fa fa-times' aria-hidden='true'></i></a>" +
                        "</div>" +
                        "</div>" +
                        "<div class='title-thumb'>" + file.name + "." + ext + "</div>";
                    $("#media-upload").prepend(div);
                } else if (file.type.match('video')) {
                    div.innerHTML = "<video src='" + base_url + file.src + "'></video>" +
                        "<div class='post-thumb'>" +
                        "<div  class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.name + "' class='remove-pic'>" +
                        "<i class='fa fa-times' aria-hidden='true'></i></a>" +
                        "</div>" +
                        "</div>" +
                        "<div class='title-thumb'>" + file.name + "." + ext + "</div>";
                    $("#media-upload").prepend(div);
                } else {
                    var icon = base_url;
                    var style = '';
                    if (ext === 'doc' || ext === 'docx') {
                        icon += '/assets/app/img/word-icon.png';
                    } else if (ext === 'xls' || ext === 'xlsx') {
                        icon += '/assets/app/img/excel-icon.png';
                    } else if (ext === 'pdf') {
                        icon += '/assets/app/img/pdf-icon.png';
                    } else {
                        icon += '/assets/app/img/document-icon.svg';
                        style = "style='padding: 10px'";
                    }
                    div.innerHTML = "<img src='" + icon + "' " + style + ">" +
                        "<div class='post-thumb'>" +
                        "<div  class='inner-post-thumb'>" +
                        "<a href='javascript:void(0);' data-id='" + file.name + "' class='remove-pic'>" +
                        "<i class='fa fa-times' aria-hidden='true'></i></a>" +
                        "</div>" +
                        "</div>" +
                        "<div class='title-thumb'>" + file.name + "." + ext + "</div>";
                    $("#media-upload").prepend(div);
                }
            }
        }

        $(".remove-pic").click(function () {
            console.log("First Way: " + $(this).data('id'));
            //$(this).parent().parent().parent().remove();
            var elm = $(this).parent().parent().parent();
            var removeItem = $(this).data('id');
            for (var i = 0; i < dataFiles.length; i++) {
                var cur = dataFiles[i];
                if (cur.name === removeItem) {
                    //dataFiles.splice(i, 1);
                    deleteImage(i, elm, cur.src);
                    break;
                }
            }
        });
    }

    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };

</script>
