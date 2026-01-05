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
$isPengawas = isset($panitia) && isset($panitia->jabatan) && $panitia->jabatan === 'Pengawas';

?>

<style>
    .note-toolbar {
        text-align: left !important;
    }
</style>

<main class="app-main bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card-body">
                <div class="row">

                    <div class="col-md-12 <?= $colClass ?> mb-4">
                        <?php
                        $totalPengumuman = count($pengumumans);
                        $totalKomentar = array_sum(array_map(function ($p) {
                            return (int) ($p->jml ?? 0);
                        }, $pengumumans));
                        $runningCount = count(array_filter($runningEntries, fn($t) => !empty($t->text ?? null)));
                        ?>
                        <div class="mb-4">
                            <div
                                class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                <div>
                                    <h5 class="mb-1 font-weight-bold">Ringkasan Pengumuman</h5>
                                    <div class="text-muted">Kelola info penting, target penerima, dan komentar pengguna
                                    </div>
                                </div>
                                <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm gap-2">
                                    <span class="badge bg-primary px-3 py-2 text-sm">Total Post:
                                        <?= $totalPengumuman ?></span>
                                    <span class="badge bg-info px-3 py-2 text-sm">Total Komentar:
                                        <?= $totalKomentar ?></span>
                                    <span class="badge bg-success px-3 py-2 text-sm">Running Text Aktif:
                                        <?= $runningCount ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if (!isset($panitia)): ?>

                        <?php endif; ?>
                        <?php if ($showRunningText): ?>
                                <div class="col-12 col-md-4 mb-4">
                                    <?php $runningCount = count(array_filter($runningEntries, fn($t) => !empty($t->text ?? null))); ?>
                                    <div class="mb-3">
                                        <div class="bg-light border rounded p-3">
                                            <h6 class="mb-1 font-weight-bold">Ringkasan Running Text</h6>
                                            <div class="text-muted small">Siaran cepat di halaman siswa</div>
                                            <div class="mt-2">
                                                <span class="badge bg-primary px-3 py-2">Aktif: <?= $runningCount ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-default my-shadow border h-100">
                                        <div class="card-header">
                                            <h3 class="card-title">Running Text</h3>
                                            <div class="card-tools">
                                                <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#runningModal">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                                    title="Collapse">
                                                    <i class="bi bi-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="callout callout-info py-3 shadow-none">
                                                <h6 class="font-weight-bold mb-1"><i class="bi bi-info-circle me-2"></i>Panduan
                                                </h6>
                                                <ul class="ps-3 mb-0 small">
                                                    <li>Isi maksimal <strong>60 karakter</strong> per baris untuk keterbacaan.
                                                    </li>
                                                    <li>Gunakan 3-5 baris agar pesan inti tetap singkat.</li>
                                                    <li>Klik tombol <span class="badge bg-danger">hapus</span> untuk
                                                        mengosongkan
                                                        baris.</li>
                                                </ul>
                                            </div>
                                            <div class="editors">
                                                <?= form_open('', array('id' => 'formrunningtext')) ?>
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-sm border" id="tb-text">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 12%"
                                                                    class="text-center align-middle alert alert-default-info">
                                                                    Baris
                                                                </th>
                                                                <th class="align-middle alert alert-default-info">Konten</th>
                                                                <th style="width: 12%"
                                                                    class="text-center align-middle alert alert-default-info">
                                                                    Aksi
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($runningEntries as $text): ?>
                                                                    <tr>
                                                                        <td class="text-center align-middle font-weight-bold">
                                                                            #<?= $text->id_text ?></td>
                                                                        <td contenteditable="true" class="editable align-middle"
                                                                            data-placeholder="Tulis teks berjalan..."><?= $text->text ?>
                                                                        </td>
                                                                        <td class="text-center align-middle">
                                                                            <button type="button" class="btn btn-danger btn-sm"
                                                                                onclick="hapusText(this)"
                                                                                data-id="<?= $text->id_text ?>" title="Hapus baris">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <button type="submit" class="btn btn-primary d-block w-100 mt-3 shadow-sm">
                                                    <i class="bi bi-save me-1"></i> Simpan Running Text
                                                </button>
                                                <?= form_close(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-12">
                        <div class="card card-default my-shadow">
                            <div class="card-header">
                                <h3 class="card-title">Pengumuman</h3>
                                <div class="card-tools ms-auto d-flex align-items-center">
                                    <button type="button" onclick="window.location.reload()"
                                        class="btn btn-sm btn-warning ms-1">
                                        <i class="bi bi-arrow-repeat"></i> <span
                                            class="d-none d-sm-inline-block ms-1">Reload</span>
                                    </button>
                                    <?php if (!$isPengawas): ?>
                                            <button type="button" class="btn btn-sm btn-primary ms-1" data-bs-toggle="modal"
                                                data-bs-target="#pengumumanModal">
                                                <i class="bi bi-plus me-1"></i> Pengumuman
                                            </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-tool ms-1" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="bi bi-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php
                                //echo '<pre>';
                                //var_dump($posts);
                                //echo '</pre>';
                                foreach ($pengumumans as $pengumuman): ?>
                                        <div class="card card-widget widget-user-2 shadow-sm mb-4"
                                            style="border: 1px solid #ebedf2;">
                                            <div class="card-body p-0" id="parent<?= $pengumuman->id_post ?>">
                                                <div class="p-3 d-flex align-items-center bg-light border-bottom">
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($pengumuman->dari == '0'): ?>
                                                                <div class="rounded-circle bg-success shadow-sm d-flex align-items-center justify-content-center me-3 text-white"
                                                                    style="width: 45px; height: 45px; font-weight: bold; font-size: 1.2rem;">
                                                                    A</div>
                                                                <div class="overflow-hidden">
                                                                    <span class="fw-bold d-block text-dark">Administrator</span>
                                                                    <small
                                                                        class="text-muted"><?= buat_tanggal(date('D, d M Y H:i', strtotime($pengumuman->tanggal ?? ''))) ?></small>
                                                                </div>
                                                        <?php else: ?>
                                                                <img class="rounded-circle border shadow-sm me-3"
                                                                    src="<?= file_exists($pengumuman->foto) ? base_url() . $pengumuman->foto : base_url('assets/img/guru.png') ?>"
                                                                    style="width: 45px; height: 45px; object-fit: cover;" alt="User">
                                                                <div class="overflow-hidden">
                                                                    <span
                                                                        class="fw-bold d-block text-dark"><?= $pengumuman->nama_guru ?></span>
                                                                    <small class="text-muted"><?= $pengumuman->tanggal ?></small>
                                                                </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ms-auto flex-nowrap d-flex">
                                                        <?php if (!$isPengawas): ?>
                                                                <button type="button" class="btn btn-danger btn-sm shadow-sm"
                                                                    onclick="hapusPost(<?= $pengumuman->id_post ?>)"
                                                                    data-id="<?= $pengumuman->id_post ?>" title="Hapus">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="p-4 text-justify">
                                                    <?= $pengumuman->text ?>
                                                </div>

                                                <div class="card-footer bg-white p-2 border-top-0">
                                                    <div class="d-flex justify-content-between">
                                                        <?php if (!$isPengawas): ?>
                                                                <button type="button" class="btn btn-link btn-sm text-decoration-none"
                                                                    data-id="<?= $pengumuman->id_post ?>" data-bs-toggle="modal"
                                                                    data-bs-target="#komentarModal">
                                                                    <i class="bi bi-reply me-1"></i> <span
                                                                        class="text-sm font-weight-bold">Tulis Komentar</span>
                                                                </button>
                                                        <?php else: ?>
                                                                <div></div>
                                                        <?php endif; ?>
                                                        <button type="button" id="trigger<?= $pengumuman->id_post ?>"
                                                            class="btn btn-link btn-sm text-decoration-none text-muted action-collapse"
                                                            data-bs-toggle="collapse" aria-expanded="true"
                                                            aria-controls="collapse-<?= $pengumuman->id_post ?>"
                                                            href="#collapse-<?= $pengumuman->id_post ?>">
                                                            <span class="text-sm"><?= $pengumuman->jml ?> Komentar</span> <i
                                                                class="bi bi-comments ms-1"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="collapse-<?= $pengumuman->id_post ?>" class="collapse toggle-comment"
                                                style="background-color: #f8f9fa;" data-id="<?= $pengumuman->id_post ?>"
                                                data-bs-parent="#parent<?= $pengumuman->id_post ?>">
                                                <div id="loading<?= $pengumuman->id_post ?>" class="text-center d-none p-3">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </div>
                                                <div id="konten<?= $pengumuman->id_post ?>" class="p-3">
                                                </div>
                                                <?php if ($pengumuman->jml == '0'): ?>
                                                        <div class="text-center p-3 text-muted small">Belum ada komentar</div>
                                                <?php else: ?>
                                                        <div id="loadmore<?= $pengumuman->id_post ?>"
                                                            onclick="getComments(<?= $pengumuman->id_post ?>)"
                                                            class="text-center p-2 mb-2 pointer loadmore">
                                                            <small class="text-primary font-weight-bold" style="cursor: pointer">
                                                                Lihat komentar lainnya <i class="bi bi-chevron-down ms-1"></i>
                                                            </small>
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
        </div>
    </section>
    </div>

    <div class="modal fade" id="pengumumanModal" tabindex="-1" role="dialog" aria-labelledby="pengumumanLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pengumumanLabel">Tulis Info/Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <?= form_open('', array('id' => 'formpengumuman')) ?>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-2 col-form-label">Kepada</label>
                        <div class="col-md-10">
                            <select id="opsi-kepada" name="kepada[]" class="select2 form-control" multiple="multiple"
                                style="width:100%" required>
                                <option value='guru'>Semua Guru</option>
                                <option value='siswa'>Semua Siswa</option>
                                <?php foreach ($kelas as $key => $value): ?>
                                        <option class="opsi-kelas" value="<?= $key ?>"><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <textarea class="w-100" id="text-pengumuman" name="text" required></textarea>
                    </div>

                    <?php if ($is_admin): ?>
                            <input type="hidden" name="dari" value="0">
                    <?php elseif (isset($guru)): ?>
                            <input type="hidden" name="dari" value="<?= $guru->id_guru ?>">
                    <?php elseif (isset($panitia)): ?>
                            <input type="hidden" name="dari" value="<?= $panitia->id_panitia ?>">
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm ms-2">
                        <i class="bi bi-paper-plane me-1"></i> Kirim
                    </button>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="komentarModal" tabindex="-1" role="dialog" aria-labelledby="komentarLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="komentarLabel">Tulis Komentar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body d-flex align-items-start">
                    <img class="img-fluid img-circle rounded-circle flex-shrink-0"
                        src="<?= base_url('assets/img/siswa.png') ?>" alt="Alt Text"
                        style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="w-100 ms-3">
                        <?= form_open('create', array('id' => 'komentar')) ?>
                        <input type="hidden" id="id-post" name="id_post" value="">
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" name="text" placeholder="Tulis komentar ..." class="form-control"
                                required>
                            <button type="submit" class="btn btn-success shadow-sm px-3"><i
                                    class="bi bi-send-fill"></i></button>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="balasanModal" tabindex="-1" role="dialog" aria-labelledby="balasanLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="balasanLabel">Tulis Balasan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body d-flex align-items-start">
                    <img class="img-fluid img-circle rounded-circle flex-shrink-0"
                        src="<?= base_url('assets/img/siswa.png') ?>" alt="Alt Text"
                        style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="w-100 ms-3">
                        <?= form_open('create', array('id' => 'balasan')) ?>
                        <input type="hidden" id="id-comment" name="id_comment" value="">
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" name="text" placeholder="Tulis balasan ...." class="form-control"
                                required>
                            <button type="submit" class="btn btn-success shadow-sm px-3"><i
                                    class="bi bi-send-fill"></i></button>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                </div>
            </div>
        </div>
    </div>

    <script>
        var guru = '<?= isset($guru) ? $guru->id_guru : (isset($panitia) ? $panitia->id_panitia : "") ?>';
        var isPengawas = <?= $isPengawas ? 'true' : 'false' ?>;

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
                var avatar = v.dari == '0' ? '<div class="rounded-circle bg-primary shadow-sm d-flex align-items-center justify-content-center text-white me-2" style="width: 40px; height: 40px; min-width: 40px; font-weight: bold;">A</div>' : '<img class="rounded-circle border shadow-sm me-2" src="' + foto + '" style="width: 40px; height: 40px; min-width: 40px; object-fit: cover;" alt="Img">';
                comm += '<div class="d-flex mt-3" id="parent-reply' + v.id_comment + '">'
                    + avatar +
                    '    <div class="flex-grow-1 overflow-hidden">' +
                    '        <div class="bg-white border rounded shadow-xs p-2 px-3">' +
                    '            <div class="fw-bold text-xs text-info mb-1">' + dari + '</div>' +
                    '            <div class="comment-text text-dark">' + v.text + '</div>' +
                    '        </div>' +
                    '        <div class="mt-1 d-flex align-items-center text-xs">' +
                    '            <span class="text-muted me-3">' + createTime(v.tanggal) + '</span>' +
                    '            <span id="trigger-reply' + v.id_comment + '" class="fw-bold text-dark pointer me-3 action-collapse" data-bs-toggle="collapse" aria-expanded="true"' +
                    '                  aria-controls="collapse-reply' + v.id_comment + '"' +
                    '                  href="#collapse-reply' + v.id_comment + '"><i class="bi bi-comments me-1"></i>' + v.jml + ' balasan</span>';

                if (!isPengawas) {
                    comm += '            <span class="fw-bold text-primary pointer me-3 btn-toggle-reply"' +
                        '                  data-id="' + v.id_comment + '" data-bs-toggle="modal" data-bs-target="#balasanModal">' +
                        '                <i class="bi bi-reply me-1"></i> Balas</span>' +
                        '<span class="fw-bold text-danger pointer" onclick="hapusKomentar(' + v.id_comment + ')" data-id="' + v.id_comment + '"><i class="bi bi-trash me-1"></i> Hapus</span>';
                }

                comm += '</div>' +
                    '<div id="collapse-reply' + v.id_comment + '" class="ms-1 ps-3 border-start mt-2 collapse toggle-reply" data-id="' + v.id_comment + '" data-bs-parent="#parent-reply' + v.id_comment + '">';
                if (v.jml != '0') {
                    comm += '<div id="konten-reply' + v.id_comment + '"></div>' +
                        '<div id="loadmore-reply' + v.id_comment + '" onclick="getReplies(' + v.id_comment + ')" class="text-center mt-2 loadmore-reply">' +
                        '       <div class="btn btn-sm btn-primary border text-xs fw-bold">Muat balasan lainnya ...</div>' +
                        '</div>';
                }
                comm += '    <div id="loading-reply' + v.id_comment + '" class="text-center d-none mt-2">' +
                    '        <div class="spinner-border spinner-border-sm text-primary"></div>' +
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
                    var avatar = v.dari == '0' ? '<div class="rounded-circle bg-primary shadow-sm d-flex align-items-center justify-content-center text-white me-2" style="width: 32px; height: 32px; min-width: 32px; font-weight: bold; font-size: 0.8rem;">A</div>' : '<img class="rounded-circle border shadow-sm me-2" src="' + foto + '" style="width: 32px; height: 32px; min-width: 32px; object-fit: cover;" alt="Img">';
                    repl +=
                        '<div class="d-flex mt-3 media' + v.id_reply + '">'
                        + avatar +
                        '    <div class="flex-grow-1 overflow-hidden">' +
                        '        <div class="bg-gray-100 border rounded p-2 px-3" style="background-color: #f1f5f9;">' +
                        '            <div class="fw-bold text-xs text-dark mb-1">' + dari + '</div>' +
                        '            <div class="comment-text text-dark text-sm">' + v.text + '</div>' +
                        '        </div>' +
                        '        <div class="mt-1 d-flex align-items-center text-xs">' +
                        '            <span class="text-muted me-3">' + createTime(v.tanggal) + '</span>';

                    if (!isPengawas) {
                        repl += '            <span class="fw-bold text-danger pointer" onclick="hapusReply(' + v.id_reply + ')" data-id="' + v.id_reply + '">' +
                            '                <i class="bi bi-trash me-1"></i> Hapus' +
                            '            </span>';
                    }

                    repl += '        </div>' +
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
            Swal.fire({
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
                    Swal.fire({
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                                Swal.showLoading();
                            }
                    });
                    $.ajax({
                        url: base_url + "pengumuman/hapuspost/" + idPost,
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
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
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Pengumuman tidak dihapus",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            const err = JSON.parse(xhr.responseText)
                            Swal.fire({
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
            Swal.fire({
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
                    Swal.fire({
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                                Swal.showLoading();
                            }
                    });
                    $.ajax({
                        url: base_url + "pengumuman/hapuskomentar/" + idKomentar,
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Komentar dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Komentar tidak dihapus",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            const err = JSON.parse(xhr.responseText)
                            Swal.fire({
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
            Swal.fire({
                title: "Hapus Balasan",
                text: "Balasan ini akan dihapus",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "HAPUS"
            }).then(result => {
                if (result.value) {
                    Swal.fire({
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                                Swal.showLoading();
                            }
                    });
                    $.ajax({
                        url: base_url + "pengumuman/hapusbalasan/" + idReply,
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Balasan dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Balasan tidak dihapus",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            const err = JSON.parse(xhr.responseText)
                            Swal.fire({
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
                        '<button class="btn btn-default btn-circle-micro" data-id="' + v.id_text + '" onclick="edit(this)"><i class="bi bi-pencil"></i></button>' +
                        '<button class="btn btn-default btn-circle-micro" data-id="' + v.id_text + '" onclick="hapusText(this)"><i class="bi bi-trash"></i></button>' +
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
            $('#pengumumanModal .select2').select2({
                width: '100%',
                dropdownParent: $('#pengumumanModal')
            });

            $('#pengumumanModal').on('hidden.bs.modal', function () {
                $('#formpengumuman')[0].reset();
                $('#opsi-kepada').val(null).trigger('change');
                $('#text-pengumuman').summernote('reset');
            });

            $('#text-pengumuman').summernote({
                placeholder: 'Tulis Pengumuman',
                tabsize: 2,
                minHeight: 100,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['link', 'picture', 'hr']]
                ],
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

                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
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
                            Swal.fire({
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
                            Swal.fire({
                                title: "ERROR",
                                text: "Pengumuman Tidak Tersimpan",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        Swal.fire({
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
                    item["id_text"] = no;
                    item["text"] = text;
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