<?php

$colClass = isset($col) ? $col : '';
$runningEntries = [];
if (isset($running_text)) {
    if (is_array($running_text)) {
        $runningEntries = $running_text;
    } elseif ($running_text instanceof Traversable) {
        $runningEntries = iterator_to_array($running_text);
    }
}
$showRunningText = $is_admin && isset($setting->running_text) && $setting->running_text == '1';

?>

<div class="content-wrapper bg-white pt-4">
    <!--
	<section class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1><?= $judul ?></h1>
				</div>
			</div>
		</div>
	</section>
	-->
    <section class="content">
        <div class="container">
            <div class="row">

                <div class="col-12 <?= $colClass ?> mb-4">
                    <?php
                    $totalPengumuman = count($pengumumans);
                    $totalKomentar = array_sum(array_map(function($p){ return (int) ($p->jml ?? 0); }, $pengumumans));
                    $runningCount = count(array_filter($runningEntries, fn($t) => !empty($t->text ?? null)));
                    ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan Pengumuman</h5>
                                <div class="text-muted">Kelola info penting, target penerima, dan komentar pengguna</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Post: <?= $totalPengumuman ?></span>
                                <span class="badge badge-info px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Komentar: <?= $totalKomentar ?></span>
                                <span class="badge badge-success px-3 py-2 text-sm">Running Text Aktif: <?= $runningCount ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengelolaan Pengumuman</h5>
                        <ul class="pl-3 mb-0">
                            <li>Pilih penerima pengumuman dengan multi-select, lalu gunakan editor untuk konten kaya.</li>
                            <li>Gunakan kolom komentar untuk diskusi; hapus komentar atau balasan bila diperlukan.</li>
                            <li>Running text muncul di layar siswa—isi maksimal lima baris agar pesan tetap terbaca.</li>
                        </ul>
                    </div>

                    <div class="card my-shadow border">
                        <?= form_open('', array('id' => 'formpengumuman')) ?>
                        <div class="card-header alert alert-default-success">
                            Tulis Info/Pengumuman
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-2">
                                    <p>Kepada:</p>
                                </div>
                                <div class="col-10">
                                    <select id="opsi-kepada" name="kepada[]" class="select2 form-control"
                                            multiple="multiple" required>
                                        <option value='guru'>Semua Guru</option>
                                        <option value='siswa'>Semua Siswa</option>
                                        <?php foreach ($kelas as $key => $value) : ?>
                                            <option class="opsi-kelas" value="<?= $key ?>"><?= $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <textarea class="w-100" id="text-pengumuman" name="text" required></textarea>

                            <?php if ($is_admin): ?>
                                <input type="hidden" name="dari" value="0">
                            <?php else: ?>
                                <input type="hidden" name="dari" value="<?= $guru->id_guru ?>">
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary float-right">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                        </div>
                        <?= form_close(); ?>
                <?php if ($showRunningText) : ?>
                    <div class="col-12 col-md-4 mb-4">
                        <?php $runningCount = count(array_filter($runningEntries, fn($t) => !empty($t->text ?? null))); ?>
                        <div class="mb-3">
                            <div class="bg-light border rounded p-3">
                                <h6 class="mb-1 font-weight-bold">Ringkasan Running Text</h6>
                                <div class="text-muted small">Siaran cepat di halaman siswa</div>
                                <div class="mt-2">
                                    <span class="badge badge-primary px-3 py-2">Aktif: <?= $runningCount ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card my-shadow border h-100">
                            <div class="card-header alert alert-default-info d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">Running Text</span>
                                <button class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#runningModal">
                                    <i class="fa fa-edit"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="callout callout-info py-3">
                                    <h6 class="font-weight-bold mb-1"><i class="fas fa-info-circle mr-2"></i>Panduan</h6>
                                    <ul class="pl-3 mb-0 small">
                                        <li>Isi maksimal <strong>60 karakter</strong> per baris untuk keterbacaan.</li>
                                        <li>Gunakan 3-5 baris agar pesan inti tetap singkat.</li>
                                        <li>Klik tombol <span class="badge badge-danger">hapus</span> untuk mengosongkan baris.</li>
                                    </ul>
                                </div>
                                <div class="editors">
                                    <?= form_open('', array('id' => 'formrunningtext')) ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm border" id="tb-text">
                                            <thead class="bg-light">
                                            <tr>
                                                <th style="width: 12%" class="text-center align-middle">Baris</th>
                                                <th class="align-middle">Konten</th>
                                                <th style="width: 12%" class="text-center align-middle">Aksi</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($runningEntries as $text) : ?>
                                                <tr>
                                                    <td class="text-center align-middle font-weight-bold">#<?= $text->id_text ?></td>
                                                    <td contenteditable="true" class="editable align-middle" data-placeholder="Tulis teks berjalan..."><?= $text->text ?></td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="hapusText(this)" data-id="<?= $text->id_text ?>" title="Hapus baris">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block mt-3">
                                        <i class="fa fa-save"></i> Simpan Running Text
                                    </button>
                                    <?= form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                </div>

                <div class="col-md-12">
                    <div class="card my-shadow">
                        <div class="card-header alert alert-default-danger">
                            <div class="card-title">
                                <?= $subjudul ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            //echo '<pre>';
                            //var_dump($posts);
                            //echo '</pre>';
                            foreach ($pengumumans as $pengumuman) : ?>
                                <div class="card">
                                    <div class="card-body" id="parent<?= $pengumuman->id_post ?>">
                                        <div class="media">
                                            <?php if ($pengumuman->dari == '0') : ?>
                                                <span class="btn-circle btn-success media-left pt-1">A</span>
                                                <div class="media-body ml-3">
                                                    <span class="font-weight-bold">Admin</b></span>
                                                    <br/>
                                                    <span class="text-gray"><?= buat_tanggal(date('D, d M Y H:i', strtotime($pengumuman->tanggal ?? ''))) ?>
                                            </span>
                                                </div>
                                            <?php else: ?>
                                                <img class="img-circle media-left"
                                                     src="<?= file_exists($pengumuman->foto) ? base_url() . $pengumuman->foto : base_url('assets/img/guru.png') ?>"
                                                     width="50" height="50"/>
                                                <div class="media-body ml-3">
                                                    <span class="font-weight-bold"><?= $pengumuman->nama_guru ?></span>
                                                    <br/>
                                                    <span class="text-gray"> <?= $pengumuman->tanggal ?> </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <?= $pengumuman->text ?>
                                        </div>
                                        <div class="text-muted">
                                            <button type="button" class="btn btn-primary btn-sm mr-2 btn-toggle"
                                                    data-id="<?= $pengumuman->id_post ?>" data-toggle="modal"
                                                    data-target="#komentarModal"><i class="fas fa-reply mr-1"></i> Tulis
                                                komentar
                                            </button>
                                            <button type="button" id="trigger<?= $pengumuman->id_post ?>"
                                                    class="btn btn-success btn-sm mr-2 action-collapse"
                                                    data-toggle="collapse" aria-expanded="true"
                                                    aria-controls="collapse-<?= $pengumuman->id_post ?>"
                                                    href="#collapse-<?= $pengumuman->id_post ?>">
                                                <i class="fa fa-commenting-o mr-1"></i><?= $pengumuman->jml ?> komentar
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="hapusPost(<?= $pengumuman->id_post ?>)"
                                                    data-id="<?= $pengumuman->id_post ?>">
                                                <i class="fa fa-trash mr-1"></i> Hapus
                                            </button>

                                        </div>
                                    </div>
                                    <div id="collapse-<?= $pengumuman->id_post ?>" class="p-2 collapse toggle-comment"
                                         data-id="<?= $pengumuman->id_post ?>"
                                         data-parent="#parent<?= $pengumuman->id_post ?>">
                                        <hr class="m-0">
                                        <div id="konten<?= $pengumuman->id_post ?>" class="p-4">
                                        </div>
                                        <div id="loading<?= $pengumuman->id_post ?>" class="text-center d-none">
                                            <!-- <div class="spinner-grow"></div> -->
                     <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                            Loading...
                            </button>
                                        </div>
                                        <?php if ($pengumuman->jml == '0') : ?>
                                            <div class="text-center">Tidak ada komentar</div>
                                        <?php else: ?>
                                            <div id="loadmore<?= $pengumuman->id_post ?>"
                                                 onclick="getComments(<?= $pengumuman->id_post ?>)"
                                                 class="text-center mt-4 loadmore">
                                                <div class="btn btn-default">Muat komentar lainnya ...</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="komentarModal" tabindex="-1" role="dialog" aria-labelledby="komentarLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="komentarLabel">Tulis Komentar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img class="img-fluid img-circle img-sm" src="<?= base_url('assets/img/siswa.png') ?>" alt="Alt Text">
                <div class="img-push">
                    <?= form_open('create', array('id' => 'komentar')) ?>
                    <input type="hidden" id="id-post" name="id_post" value="">
                    <div class="input-group">
                        <input type="text" name="text" placeholder="Tulis komentar ..."
                               class="form-control form-control-sm" required>
                        <span class="input-group-append">
                                <button type="submit" class="btn btn-success btn-sm">Komentari</button>
                            </span>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="balasanModal" tabindex="-1" role="dialog" aria-labelledby="balasanLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="balasanLabel">Tulis Balasan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img class="img-fluid img-circle img-sm" src="<?= base_url('assets/img/siswa.png') ?>" alt="Alt Text">
                <div class="img-push">
                    <?= form_open('create', array('id' => 'balasan')) ?>
                    <input type="hidden" id="id-comment" name="id_comment" value="">
                    <div class="input-group">
                        <input type="text" name="text" placeholder="Tulis balasan ...."
                               class="form-control form-control-sm" required>
                        <span class="input-group-append">
                                <button type="submit" class="btn btn-success btn-sm">Balas</button>
                            </span>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button> <!-- ikasmedia -->
            </div>
        </div>
    </div>
</div>

<script>
    var guru = '<?=isset($guru) ? $guru->id_guru : ""?>';

    function createTime(d) {
        var date = new Date(d);

        var jam = date.getHours();
        var menit = date.getMinutes();
        var sJam;
        var sMenit;

        if (jam < 10) sJam = '0' + jam;
        else sJam = '' + jam;

        if (menit < 10) sMenit = '0' + menit;
        else sMenit = '' + menit;

        var hari = daysdifference(d);
        var time;

        if (hari === 0) {
            time = sJam + ':' + sMenit;
        } else if (hari === 1) {
            time = 'kemarin ' + sJam + ':' + sMenit;
        } else {
            time = jQuery.timeago(d) + ', ' + sJam + ':' + sMenit;
        }
        return time;
    }

    function daysdifference(last) {
        var startDay = new Date(last);
        var endDay = new Date();

        var millisBetween = startDay.getTime() - endDay.getTime();
        var days = millisBetween / (1000 * 3600 * 24);

        return Math.round(Math.abs(days));
    }

    function addComments(id, comments, append) {
        var comm = '';
        $.each(comments, function (i, v) {
            var dari = v.dari_group === '1' ? 'Admin' : (v.dari_group === '2' ? v.nama_guru : v.nama_siswa);
            var foto = v.dari_group === '2' ? (v.foto != null ? base_url + v.foto : base_url + 'assets/img/guru.png') :
                (v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png');
            var avatar = v.dari == '0' ? '<div class="btn-circle-sm btn-primary media-left pt-1" style="width: 43px; height: 40px">A</div>' : '<img class="img-circle border" src="' + foto + '" alt="Img" width="40px" height="40px">';
            comm += '<div class="media mt-1" id="parent-reply' + v.id_comment + '">'
                + avatar +
                '    <div class="w-100 ml-2">' +
                '        <div class="media-body border pl-3 bg-light" style="border-radius: 20px">' +
                '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                '            <div class="comment-text pb-1">' + v.text + '</div>' +
                '        </div>' +
                '        <div class="ml-2">' +
                '            <span class="btn-sm mr-2 text-muted">' + createTime(v.tanggal) + '</span>' +
                '            <span id="trigger-reply' + v.id_comment + '" class="btn btn-sm mr-2 text-muted action-collapse" data-toggle="collapse" aria-expanded="true"' +
                '                              aria-controls="collapse-reply' + v.id_comment + '"' +
                '                              href="#collapse-reply' + v.id_comment + '"><b>' + v.jml + ' balasan</b></span>' +
                '            <span class="btn btn-sm mr-2 text-muted btn-toggle-reply"' +
                '                  data-id="' + v.id_comment + '" data-toggle="modal" data-target="#balasanModal">' +
                '                <i class="fas fa-reply"></i> <b>Balas</b></span>' +
                '<span class="btn btn-sm text-muted" onclick="hapusKomentar(' + v.id_comment + ')" data-id="' + v.id_comment + '"><i class="fa fa-trash mr-1"></i> Hapus</span>' +
                '</div>' +
                '<div id="collapse-reply' + v.id_comment + '" class="p-2 collapse toggle-reply" data-id="' + v.id_comment + '" data-parent="#parent-reply' + v.id_comment + '">';
            if (v.jml != '0') {
                comm += '<div id="konten-reply' + v.id_comment + '"></div>' +
                    '<div id="loadmore-reply' + v.id_comment + '" onclick="getReplies(' + v.id_comment + ')" class="text-center mb-3 loadmore-reply">' +
                    '       <div class="btn btn-default">Muat balasan lainnya ...</div>' +
                    '</div>';
            }
            comm += '    <div id="loading-reply' + v.id_comment + '" class="text-center d-none">' +
                '        <div class="spinner-border text-primary"></div>' +
                '    </div>' +
                '</div>' +
                '    </div>' +
                '</div>';
        });

        if (append) {
            $(`#konten${id}`).append(comm);
        } else {
            $(`#konten${id}`).prepend(comm);
        }

        $('.toggle-reply').on('shown.bs.collapse', function (e) {
            var konten = $(this);
            var id = konten.data('id');
            var list = $(this).find('.media').length;
            if (list === 0) $(`#loadmore-reply${id}`).click();
        });
    }

    function addReplies(id, replies, append) {
        console.log('replies', replies);
        var repl = '';
        $.each(replies, function (i, v) {
            var sudahAda = $(`.media${v.id_reply}`).length;
            if (!sudahAda) {
                var dari = v.dari_group == '1' ? 'Admin' : (v.dari_group == '2' ? v.nama_guru : v.nama_siswa);
                var foto = v.dari_group == '2' ? (v.foto != null ? base_url + v.foto : base_url + 'assets/img/siswa.png') :
                    (v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png');
                var avatar = v.dari == '0' ? '<div class="btn-circle-sm btn-primary media-left pt-1 mr-2" style="width: 37px">A</div>' : '<img class="img-circle mr-2 border" src="' + foto + '" alt="Img" width="35px" height="35px">';
                repl +=
                    '<div class="media mt-1 media' + v.id_reply + '">'
                    + avatar +
                    '    <div class="w-100">' +
                    '        <div class="media-body border pl-3" style="border-radius: 17px; background-color: #dee2e6">' +
                    '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                    '            <div class="comment-text">' + v.text +
                    '            </div>' +
                    '        </div>' +
                    '        <div class="ml-2">' +
                    '            <small class="btn-sm mr-2 text-muted">' + createTime(v.tanggal) + '</small>' +
                    '            <span class="btn btn-sm text-muted" onclick="hapusReply(' + v.id_reply + ')" data-id="' + v.id_reply + '">' +
                    '                <i class="fa fa-trash mr-1"></i> Hapus' +
                    '            </span>' +
                    '        </div>' +
                    '    </div>' +
                    '</div>';
            }
        });

        if (append) {
            $(`#konten-reply${id}`).append(repl);
        } else {
            $(`#konten-reply${id}`).prepend(repl);
        }
        console.log('added', 'reply' + id);
    }

    function getComments(id) {
        //console.log("url", base_url + "pengumuman/getcomment/" + id + "/" + page);
        $(`#loading${id}`).removeClass('d-none');
        $(`#loadmore${id}`).addClass('d-none');
        var $count = $(`#loadmore${id}`), page = $count.data('count');
        if (!page) page = 0;

        setTimeout(function () {
            $.ajax({
                url: base_url + "pengumuman/getcomment/" + id + "/" + page,
                type: "GET",
                success: function (response) {
                    //console.log('page', page);
                    console.log("result", response);
                    page += 1;
                    currentPage = page;
                    $count.data('count', page);

                    if (response.length === 5) {
                        $(`#loadmore${id}`).removeClass('d-none');
                    }
                    $(`#loading${id}`).addClass('d-none');
                    addComments(id, response, true)
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            });
        }, 500);
    }

    function getReplies(id) {
        $(`#loading-reply${id}`).removeClass('d-none');
        $(`#loadmore-reply${id}`).addClass('d-none');
        var $count = $(`#loadmore-reply${id}`), page = $count.data('count');
        if (!page) page = 0;

        setTimeout(function () {
            $.ajax({
                url: base_url + "pengumuman/getreplies/" + id + "/" + page,
                type: "GET",
                success: function (response) {
                    //console.log('page', page);
                    console.log("result", response);
                    page += 1;
                    currentPage = page;
                    $count.data('count', page);

                    //n >= start && n <= end
                    if (response.length === 5) {
                        $(`#loadmore-reply${id}`).removeClass('d-none');
                    }
                    $(`#loading-reply${id}`).addClass('d-none');
                    addReplies(id, response, true)
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            });
        }, 500);
    }

    function hapusPost(idPost) {
        swal.fire({
            title: "Hapus Pengumuman",
            text: "Pengumuman ini akan dihapus",
            icon: "info",
            showCancelButton: true,
            howCancelButton: true,
		    confirmButtonColor: "#3085d6",
		    cancelButtonColor: "#d33",
            confirmButtonText: "HAPUS"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });
                $.ajax({
                    url: base_url + "pengumuman/hapuspost/" + idPost,
                    type: "GET",
                    success: function (data) {
                        console.log("result", data);
                        if (data) {
                            swal.fire({
                                title: "Sukses",
                                text: "Pengumuman dihapus",
                                icon: "success",
                                showCancelButton: false,
                            }).then(result => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            swal.fire({
                                title: "ERROR",
                                text: "Pengumuman tidak dihapus",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }

    function hapusKomentar(idKomentar) {
        swal.fire({
            title: "Hapus Komentar",
            text: "Komentar ini akan dihapus",
            icon: "info",
            //showCancelButton: true,
            howCancelButton: true,
            confirmButtonColor: "#3085d6",
		    cancelButtonColor: "#d33",
            confirmButtonText: "HAPUS"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });
                $.ajax({
                    url: base_url + "pengumuman/hapuskomentar/" + idKomentar,
                    type: "GET",
                    success: function (data) {
                        console.log("result", data);
                        if (data) {
                            swal.fire({
                                title: "Sukses",
                                text: "Komentar dihapus",
                                icon: "success",
                                showCancelButton: false,
                            }).then(result => {
                                if (result.value) {
                                }
                            });
                        } else {
                            swal.fire({
                                title: "ERROR",
                                text: "Komentar tidak dihapus",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }

    function hapusReply(idReply) {
        swal.fire({
            title: "Hapus Balasan",
            text: "Balasan ini akan dihapus",
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
		    cancelButtonColor: "#d33",
            confirmButtonText: "HAPUS"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });
                $.ajax({
                    url: base_url + "pengumuman/hapusbalasan/" + idReply,
                    type: "GET",
                    success: function (data) {
                        console.log("result", data);
                        if (data) {
                            swal.fire({
                                title: "Sukses",
                                text: "Balasan dihapus",
                                icon: "success",
                                showCancelButton: false,
                            }).then(result => {
                                if (result.value) {
                                }
                            });
                        } else {
                            swal.fire({
                                title: "ERROR",
                                text: "Balasan tidak dihapus",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }

    /*
    function loadListText() {
        $.ajax({
            url: base_url + "pengumuman/getrunningtext",
            type: "GET",
            success: function (response) {
                console.log('page', response);
                createListText(response);
            }, error: function (xhr, status, error) {
                console.log("error", xhr.responseText);
            }
        });
    }
    */

    function createListText(data) {
        var html = '<table class="table w-100">';
        if (data.running_text.length > 0) {
            $.each(data.running_text, function (i, v) {
                html += '<tr> ' +
                    '<td>' + v.text + '</td>' +
                    '<td>' +
                    '<button class="btn btn-default btn-circle-micro" data-id="' + v.id_text + '" onclick="edit(this)"><i class="fa fa-pencil"></i></button>' +
                    '<button class="btn btn-default btn-circle-micro" data-id="' + v.id_text + '" onclick="hapusText(this)"><i class="fa fa-trash"></i></button>' +
                    '</td> ' +
                    '</tr>';
            });
        }
        html += '</table>';
        $('#list-text').html(html);
    }

    function hapusText(e) {
        var id = $(e).data('id');
        console.log(id);

        $.ajax({
            url: base_url + "pengumuman/hapusrunningtext/" + id,
            type: "GET",
            success: function (response) {
                console.log('page', response);
                if (!response) {
                    showDangerToast('Tidak bisa menghapus')
                } else {
                    loadListText()
                }
            }, error: function (xhr, status, error) {
                console.log("error", xhr.responseText);
                showDangerToast('Tidak bisa menghapus')
            }
        });
    }

    $(document).ready(function () {
        $('.editable').attr('contentEditable', true);
        console.log('guru', guru);
        $('.select2').select2({width: '100%', });

        $('#text-pengumuman').summernote({
            placeholder: 'Tulis Pengumuman',
            tabsize: 2,
            minHeight: 100,
        });

        $('.toggle-comment').on('shown.bs.collapse', function (e) {
            var konten = $(this);
            var id = konten.data('id');
            var list = $(this).find('.media').length;
            if (list === 0) $(`#loadmore${id}`).click();
        });

        $('#formpengumuman').submit('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            console.log("data:", $(this).serialize());

            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            $.ajax({
                url: base_url + "pengumuman/save",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (data) {
                    console.log("result", data);
                    if (data) {
                        swal.fire({
                            title: "Sukses",
                            text: "Pengumuman berhasil disimpan",
                            icon: "success",
                            showCancelButton: false,
                        }).then(result => {
                            if (result.value) {
                                window.location.href = base_url + 'pengumuman';
                            }
                        });
                    } else {
                        swal.fire({
                            title: "ERROR",
                            text: "Pengumuman Tidak Tersimpan",
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        $('#komentarModal').on('show.bs.modal', function (e) {
            var id = $(e.relatedTarget).data('id');
            $("#id-post").val(id);

            var isVisible = $(`#collapse-${id}`).hasClass('show');
            if (!isVisible) {
                $(`#trigger${id}`).click();
            }
        });

        $('#balasanModal').on('show.bs.modal', function (e) {
            var id = $(e.relatedTarget).data('id');
            $("#id-comment").val(id);

            var isVisible = $(`#collapse-reply${id}`).hasClass('show');
            if (!isVisible) {
                $(`#trigger-reply${id}`).click();
            }
        });

        $('#komentar').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            console.log("data", $(this).serialize());
            var id = $(this).find('input[name=id_post]').val();

            $.ajax({
                url: base_url + "pengumuman/savekomentar",
                data: $(this).serialize(),
                method: 'POST',
                dataType: "JSON",
                success: function (response) {
                    console.log("result", response);
                    $('#komentarModal').modal('hide').data('bs.modal', null);
                    $('#komentarModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    addComments(id, response, false)
                    //window.location.href = base_url + 'pengumuman';
                },
                error: function (xhr, status, error) {
                    $('#komentarModal').modal('hide').data('bs.modal', null);
                    $('#komentarModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    showDangerToast('Error, komentar tidak terkirim');
                }
            });
        });

        $('#balasan').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            console.log("data", $(this).serialize());
            var id = $(this).find('input[name=id_comment]').val();

            $.ajax({
                url: base_url + "pengumuman/savebalasan",
                data: $(this).serialize(),
                method: 'POST',
                dataType: "JSON",
                success: function (response) {
                    console.log("result", response);
                    $('#balasanModal').modal('hide').data('bs.modal', null);
                    $('#balasanModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    //window.location.href = base_url + 'pengumuman';
                    addReplies(id, response, false)
                },
                error: function (xhr, status, error) {
                    $('#balasanModal').modal('hide').data('bs.modal', null);
                    $('#balasanModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    showDangerToast('Error, balasan tidak terkirim');
                }
            });
        });

        $('#formrunningtext').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var no = 1;
            var jsonObj = [];
            const $rows = $('#tb-text').find('tr');
            $rows.each((i, row) => {
                var id_text = no;
                const text = $(row).find('.editable').text();

                let item = {};
                item ["id_text"] = no;
                item ["text"] = text;
                jsonObj.push(item);
                no++;
            });

            var dataPost = $(this).serialize() + "&text=" + JSON.stringify(jsonObj);
            console.log(dataPost);

            $.ajax({
                url: base_url + "pengumuman/saverunningtext",
                type: "POST",
                data: dataPost,
                success: function (response) {
                    console.log('updates', response);
                    if (response.status[0]) {
                        window.location.reload();
                        //loadListText();
                        //$('#textarea-running').val('');
                    } else {
                        showDangerToast('Tidak bisa menyimpan')
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    showDangerToast('Error, Tidak bisa menyimpan')
                }
            });
        });

    })
</script>
