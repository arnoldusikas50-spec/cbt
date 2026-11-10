<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
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
$isPengawas = isset($is_pengawas) ? $is_pengawas : (isset($panitia) && isset($panitia->jabatan) && strtolower($panitia->jabatan) === 'pengawas');
// Hanya admin yang boleh tulis/edit/hapus pengumuman. Guru/panitia hanya baca (+ komentar).
$canEditPengumuman = isset($is_admin) && $is_admin;

?>

<!-- Tribute.js for @mention feature -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tributejs@5.1.3/dist/tribute.css">
<script src="https://cdn.jsdelivr.net/npm/tributejs@5.1.3/dist/tribute.min.js"></script>

<style>
    .note-toolbar {
        text-align: left !important;
    }
    
    /* Tribute.js @Mention Styling (Facebook-style) */
    .tribute-container {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        max-height: 280px;
        overflow-y: auto;
        min-width: 260px;
    }
    
    .tribute-container ul {
        list-style: none;
        margin: 0;
        padding: 8px 0;
    }
    
    .tribute-container li {
        padding: 8px 16px;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    
    .tribute-container li:hover,
    .tribute-container li.highlight {
        background: linear-gradient(135deg, #f0f8ff 0%, #e6f2ff 100%);
    }
    
    .tribute-container li.no-match {
        cursor: default;
    }
    
    /* Mention tag styling */
    .mention {
        display: inline;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .mention:hover {
        background-color: rgba(13, 110, 253, 0.2) !important;
    }
    
    /* Comment styling like Facebook */
    .comment-text .mention {
        color: #1877f2;
        font-weight: 600;
    }
    
    /* Hover underline effect */
    .hover-underline:hover {
        text-decoration: underline;
    }
    
    .pointer {
        cursor: pointer;
    }
</style>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-megaphone text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 <?= $colClass ?> mb-4">
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
                                    <?php if ($canEditPengumuman): ?>
                                        <button type="button" class="btn btn-sm btn-primary ms-1" onclick="tambahPost()">
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
                                                                class="text-muted"><?= date('D, d M Y H:i', strtotime($pengumuman->tanggal ?? '')) ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <img class="rounded-circle border shadow-sm me-3"
                                                            src="<?= file_exists(FCPATH . ($pengumuman->foto ?? '')) ? base_url() . $pengumuman->foto : base_url('assets/img/guru.png') ?>"
                                                            style="width: 45px; height: 45px; object-fit: cover;" alt="User">
                                                        <div class="overflow-hidden">
                                                            <span
                                                                class="fw-bold d-block text-dark"><?= $pengumuman->nama_guru ?></span>
                                                            <small class="text-muted"><?= $pengumuman->tanggal ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="ms-auto flex-nowrap d-flex gap-1">
                                                    <?php if ($canEditPengumuman): ?>
                                                        <button type="button" class="btn btn-warning btn-sm shadow-sm"
                                                            onclick="editPost(<?= $pengumuman->id_post ?>)"
                                                            data-id="<?= $pengumuman->id_post ?>" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
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
                                                <div id="empty-comment-<?= $pengumuman->id_post ?>" class="text-center p-3 text-muted small">Belum ada komentar</div>
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
</div>
</div>
</div>
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
                <input type="hidden" id="id-post-pengumuman" name="id_post" value="">
                <div class="mb-3 row">
                    <label class="col-md-2 col-form-label">Kepada</label>
                    <div class="col-md-10">
                        <select id="opsi-kepada" name="kepada[]" class="select2 form-control" multiple="multiple"
                            style="width:100%" required>
                            <option value='guru'>Semua Guru</option>
                            <option value='siswa'>Semua Siswa</option>
                            <?php foreach ($kelas as $value): ?>
                                <option class="opsi-kelas" value="<?= $value->id_kelas ?>"><?= $value->nama_kelas ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Isi Pengumuman</label>
                    <textarea class="form-control" id="text-pengumuman" name="text" required rows="5"></textarea>
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
                <button type="submit" id="btn-submit-pengumuman" class="btn btn-primary px-4 shadow-sm ms-2">
                    <i class="bi bi-paper-plane me-1"></i> <span>Kirim</span>
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="komentarModal" tabindex="-1" aria-labelledby="komentarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="komentarLabel">
                    <i class="bi bi-chat-dots me-2"></i> Tulis Komentar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open('create', array('id' => 'komentar')) ?>
            <div class="modal-body p-4">
                <input type="hidden" id="id-post" name="id_post" value="">
                <div class="d-flex align-items-start">
                    <img class="rounded-circle border shadow-sm flex-shrink-0 me-3"
                        src="<?= base_url('assets/img/guru.png') ?>" alt="Avatar"
                        style="width: 48px; height: 48px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <textarea name="text" id="commentText" rows="3" 
                            class="form-control border-0 bg-light rounded-3" 
                            placeholder="Tulis komentar... Gunakan @nama untuk mention" 
                            style="resize: none;" required></textarea>
                        <div class="form-text small text-muted mt-1">
                            <i class="bi bi-at"></i> Ketik @ untuk mention seseorang
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-send me-1"></i> Kirim
                </button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<div class="modal fade" id="balasanModal" tabindex="-1" aria-labelledby="balasanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-info text-white py-3">
                <h5 class="modal-title fw-bold" id="balasanLabel">
                    <i class="bi bi-reply me-2"></i> Tulis Balasan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <?= form_open('create', array('id' => 'balasan')) ?>
            <div class="modal-body p-4">
                <input type="hidden" id="id-comment" name="id_comment" value="">
                <div class="d-flex align-items-start">
                    <img class="rounded-circle border shadow-sm flex-shrink-0 me-3"
                        src="<?= base_url('assets/img/guru.png') ?>" alt="Avatar"
                        style="width: 48px; height: 48px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <textarea name="text" id="replyText" rows="3" 
                            class="form-control border-0 bg-light rounded-3" 
                            placeholder="Tulis balasan... Gunakan @nama untuk mention" 
                            style="resize: none;" required></textarea>
                        <div class="form-text small text-muted mt-1">
                            <i class="bi bi-at"></i> Ketik @ untuk mention seseorang
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-info rounded-pill px-4 text-white">
                    <i class="bi bi-reply me-1"></i> Balas
                </button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var guru = '<?= isset($guru) ? $guru->id_guru : (isset($panitia) ? $panitia->id_panitia : "") ?>';
    var isPengawas = <?= $isPengawas ? 'true' : 'false' ?>;
    var isAdmin = <?= $canEditPengumuman ? 'true' : 'false' ?>;

    function createTime(d) {
        var date = new Date(d);
        var jam = date.getHours();
        var menit = date.getMinutes();
        var sJam = jam < 10 ? '0' + jam : jam;
        var sMenit = menit < 10 ? '0' + menit : menit;

        var hari = daysdifference(d);
        var time;

        if (hari === 0) {
            time = sJam + ':' + sMenit;
        } else if (hari === 1) {
            time = 'kemarin ' + sJam + ':' + sMenit;
        } else {
            time = timeAgo(d) + ', ' + sJam + ':' + sMenit;
        }
        return time;
    }

    function timeAgo(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        let interval = Math.floor(seconds / 31536000);
        if (interval > 1) return interval + " tahun lalu";

        interval = Math.floor(seconds / 2592000);
        if (interval > 1) return interval + " bulan lalu";

        interval = Math.floor(seconds / 86400);
        if (interval > 1) return interval + " hari lalu";

        interval = Math.floor(seconds / 3600);
        if (interval >= 1) return interval + " jam lalu";

        interval = Math.floor(seconds / 60);
        if (interval >= 1) return interval + " menit lalu";

        return "baru saja";
    }

    function daysdifference(last) {
        var startDay = new Date(last);
        var endDay = new Date();

        var millisBetween = startDay.getTime() - endDay.getTime();
        var days = millisBetween / (1000 * 3600 * 24);

        return Math.round(Math.abs(days));
    }

    function addComments(id, comments, append) {
        console.log('Adding comments for post:', id, comments);
        
        if (!comments || comments.length === 0) {
            console.log('No comments to add');
            return;
        }
        
        // Remove "Belum ada komentar" placeholder
        $(`#empty-comment-${id}`).remove();
        
        // Expand the collapse if it's closed
        var collapseEl = document.getElementById(`collapse-${id}`);
        if (collapseEl && !collapseEl.classList.contains('show')) {
            var bsCollapse = new bootstrap.Collapse(collapseEl, {toggle: true});
        }
        
        // Update comment counter in trigger button
        var currentCount = parseInt($(`#trigger${id} .text-sm`).text()) || 0;
        $(`#trigger${id} .text-sm`).text((currentCount + comments.length) + ' Komentar');

        var comm = '';
        $.each(comments, function (i, v) {
            // Determine name based on dari_group: 1=Admin, 2=Guru, 3=Siswa, 4=Panitia
            var dari = 'User';
            if (v.dari == '0' || v.dari_group == '1') {
                dari = 'Administrator';
            } else if (v.dari_group == '2') {
                dari = v.nama_guru || 'Guru';
            } else if (v.dari_group == '3') {
                dari = v.nama_siswa || 'Siswa';
            } else if (v.dari_group == '4') {
                dari = v.nama_panitia || 'Panitia';
            }
            
            // Determine foto based on dari_group
            var foto = base_url + 'assets/img/guru.png';
            if (v.dari_group == '2' && v.foto) {
                foto = base_url + v.foto;
            } else if (v.dari_group == '3' && v.foto_siswa) {
                foto = base_url + v.foto_siswa;
            } else if (v.dari_group == '4' && v.foto_panitia) {
                foto = base_url + v.foto_panitia;
            }
            
            var avatar = (v.dari == '0' || v.dari_group == '1') 
                ? '<div class="rounded-circle bg-success shadow-sm d-flex align-items-center justify-content-center text-white me-2" style="width: 40px; height: 40px; min-width: 40px; font-weight: bold;">A</div>' 
                : '<img class="rounded-circle border shadow-sm me-2" src="' + foto + '" style="width: 40px; height: 40px; min-width: 40px; object-fit: cover;" alt="Img">';
            comm += '<div class="d-flex mt-3 mb-2 ps-2 pe-2" id="parent-reply' + v.id_comment + '">'
                + avatar +
                '    <div class="flex-grow-1 position-relative">' +
                '        <div class="d-inline-block bg-light px-3 py-2 rounded-3 shadow-sm" style="background-color: #f0f2f5 !important; border-radius: 18px !important; min-width: 150px;">' +
                '            <div class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">' + dari + '</div>' +
                '            <div class="comment-text text-dark" style="font-size: 0.9rem;">' + v.text + '</div>' +
                '        </div>' +
                '        <div class="d-flex align-items-center mt-1 ms-2" style="font-size: 0.75rem;">' +
                '            <span class="text-muted fw-bold me-3 cursor-pointer">' + createTime(v.tanggal) + '</span>';

            if (true) {
                comm += '            <span class="fw-bold text-dark pointer me-3 btn-toggle-reply hover-underline"' +
                    '                  data-id="' + v.id_comment + '" data-bs-toggle="modal" data-bs-target="#balasanModal">' +
                    '                Balas</span>';
                if (isAdmin) {
                    comm += '<span class="fw-bold text-danger pointer hover-underline" onclick="hapusKomentar(' + v.id_comment + ')" data-id="' + v.id_comment + '">Hapus</span>';
                }
            }

            comm += '        </div>' +
                '        <!-- Reply Section -->' + 
                '        <div class="mt-1">' +
                '            <span id="trigger-reply' + v.id_comment + '" class="fw-bold text-muted pointer action-collapse d-flex align-items-center" data-bs-toggle="collapse" aria-expanded="true"' +
                '                  aria-controls="collapse-reply' + v.id_comment + '"' +
                '                  href="#collapse-reply' + v.id_comment + '" style="font-size: 0.8rem;">' + 
                (v.jml != '0' ? '<i class="bi bi-arrow-return-right me-2"></i> ' + v.jml + ' balasan' : '') + 
                '            </span>' +
                '        </div>' +
                '        <div id="collapse-reply' + v.id_comment + '" class="ms-1 ps-0 mt-1 collapse toggle-reply" data-id="' + v.id_comment + '" data-bs-parent="#parent-reply' + v.id_comment + '">';

            if (v.jml != '0') {
                comm += '<div id="konten-reply' + v.id_comment + '"></div>' +
                    '<div id="loadmore-reply' + v.id_comment + '" onclick="getReplies(' + v.id_comment + ')" class="mt-1 loadmore-reply">' +
                    '       <span class="fw-bold text-primary pointer small">Lihat balasan lainnya...</span>' +
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
                    '<div class="d-flex mt-2 mb-1 media' + v.id_reply + '">'
                    + avatar +
                    '    <div class="flex-grow-1 position-relative">' +
                    '        <div class="d-inline-block bg-light px-3 py-2 rounded-3 shadow-sm" style="background-color: #f0f2f5 !important; border-radius: 18px !important;">' +
                    '            <div class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">' + dari + '</div>' +
                    '            <div class="comment-text text-dark" style="font-size: 0.9rem;">' + v.text + '</div>' +
                    '        </div>' +
                    '        <div class="d-flex align-items-center mt-1 ms-2" style="font-size: 0.75rem;">' +
                    '            <span class="text-muted fw-bold me-3">' + createTime(v.tanggal) + '</span>';

                if (isAdmin) {
                    repl += '            <span class="fw-bold text-danger pointer hover-underline" onclick="hapusReply(' + v.id_reply + ')" data-id="' + v.id_reply + '">' +
                        '                Hapus' +
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
                    // response is {status: true, data: [...]}
                    console.log("result", response);
                    page += 1;
                    currentPage = page;
                    $count.data('count', page);

                    if (response.data && response.data.length === 5) {
                        $(`#loadmore${id}`).removeClass('d-none');
                    }
                    $(`#loading${id}`).addClass('d-none');
                    if (response.data) {
                        addComments(id, response.data, true)
                    }
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
                    // response is {status: true, data: [...]}
                    console.log("result", response);
                    page += 1;
                    currentPage = page;
                    $count.data('count', page);

                    //n >= start && n <= end
                    if (response.data && response.data.length === 5) {
                        $(`#loadmore-reply${id}`).removeClass('d-none');
                    }
                    $(`#loading-reply${id}`).addClass('d-none');
                    if (response.data) {
                        addReplies(id, response.data, true)
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            });
        }, 500);
    }

    function tambahPost() {
        if (!isAdmin) {
            showDangerToast('Hanya Administrator yang dapat membuat pengumuman');
            return;
        }
        $('#id-post-pengumuman').val('');
        $('#pengumumanLabel').text('Tulis Info/Pengumuman');
        $('#btn-submit-pengumuman span').text('Kirim');
        $('#opsi-kepada').val(null).trigger('change');
        $('#text-pengumuman').summernote('code', '');
        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('pengumumanModal'));
        modal.show();
    }

    function editPost(idPost) {
        if (!isAdmin) {
            showDangerToast('Hanya Administrator yang dapat mengubah pengumuman');
            return;
        }
        $.ajax({
            url: base_url + "pengumuman/getdetail/" + idPost,
            type: "GET",
            dataType: "json",
            beforeSend: function () {
                showInfoToast('Memuat data pengumuman...');
            },
            success: function (response) {
                if (response.status && response.data) {
                    var post = response.data;
                    $('#id-post-pengumuman').val(post.id_post);
                    $('#pengumumanLabel').text('Edit Pengumuman');
                    $('#btn-submit-pengumuman span').text('Simpan Perubahan');

                    // Isi penerima (kepada bisa JSON array / string)
                    var kepada = post.kepada;
                    try {
                        var parsed = JSON.parse(kepada);
                        kepada = Array.isArray(parsed) ? parsed : [parsed];
                    } catch (e) {
                        kepada = kepada ? [kepada] : [];
                    }
                    $('#opsi-kepada').val(kepada).trigger('change');

                    // Isi konten summernote
                    $('#text-pengumuman').summernote('code', post.text || '');

                    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('pengumumanModal'));
                    modal.show();
                } else {
                    showDangerToast(response.message || 'Data pengumuman tidak ditemukan');
                }
            },
            error: function (xhr) {
                console.log("error", xhr.responseText);
                showDangerToast('Gagal memuat data pengumuman');
            }
        });
    }

    function hapusPost(idPost) {
        if (!isAdmin) {
            showDangerToast('Hanya Administrator yang dapat menghapus pengumuman');
            return;
        }
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
                    type: "POST", // CHANGED TO POST FOR CI4
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
            title: "Hapus Komentar?",
            text: "Komentar dan semua balasannya akan dihapus permanen.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="bi bi-x-lg me-1"></i> Batal',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({
                    text: "Menghapus komentar...",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    url: base_url + "pengumuman/hapuskomentar/" + idKomentar,
                    type: "POST",
                    success: function (response) {
                        if (response.status) {
                            $(`#parent-reply${idKomentar}`).fadeOut(300, function() {
                                $(this).remove();
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Komentar berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', response.message || 'Komentar tidak dapat dihapus', 'error');
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                    }
                });
            }
        });
    }

    function hapusReply(idReply) {
        Swal.fire({
            title: "Hapus Balasan?",
            text: "Balasan ini akan dihapus permanen.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
            cancelButtonText: '<i class="bi bi-x-lg me-1"></i> Batal',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({
                    text: "Menghapus balasan...",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                $.ajax({
                    url: base_url + "pengumuman/hapusbalasan/" + idReply,
                    type: "POST",
                    success: function (response) {
                        if (response.status) {
                            $(`.media${idReply}`).fadeOut(300, function() {
                                $(this).remove();
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Balasan berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', response.message || 'Balasan tidak dapat dihapus', 'error');
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                    }
                });
            }
        });
    }

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
            type: "POST", // CHANGED TO POST FOR CI4
            success: function (response) {
                console.log('page', response);
                if (!response) {
                    showDangerToast('Tidak bisa menghapus')
                } else {
                    window.location.reload();
                }
            }, error: function (xhr, status, error) {
                console.log("error", xhr.responseText);
            }
        });
    }

    $(document).ready(function () {
        $('.action-collapse').click(function () {
            $(this).toggleClass('collapsed');
        });

        // Reset modal pengumuman ke mode tambah saat ditutup
        $('#pengumumanModal').on('hidden.bs.modal', function () {
            $('#id-post-pengumuman').val('');
            $('#pengumumanLabel').text('Tulis Info/Pengumuman');
            $('#btn-submit-pengumuman span').text('Kirim');
        });

        $('#opsi-kepada').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#pengumumanModal'),
            placeholder: "Pilih Penerima Pengumuman",
            allowClear: true,
            width: '100%'
        });

        $('#text-pengumuman').summernote({
            tabsize: 2,
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'help']]
            ]
        });

        $('.editable').on('blur', function () {
            // handle edit blur
        });

        // =============================================
        // Tribute.js - @Mention Feature (Facebook-style)
        // =============================================
        if (typeof Tribute !== 'undefined') {
            var tribute = new Tribute({
                trigger: '@',
                iframe: null,
                selectClass: 'highlight',
                containerClass: 'tribute-container',
                menuShowMinLength: 1,
                menuItemLimit: 8,
                fillAttr: 'name',
                lookup: 'name',
                noMatchTemplate: '<li class="no-match px-3 py-2 text-muted">Tidak ditemukan</li>',
                selectTemplate: function (item) {
                    return '<span class="mention bg-primary-subtle text-primary fw-semibold px-1 rounded" data-id="' + item.original.id + '">@' + item.original.name + '</span>';
                },
                menuItemTemplate: function (item) {
                    return '<div class="d-flex align-items-center py-1">' +
                        '<img src="' + item.original.avatar + '" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">' +
                        '<div>' +
                        '<div class="fw-semibold small">' + item.string + '</div>' +
                        '<div class="text-muted" style="font-size: 0.75rem;">' + item.original.type + '</div>' +
                        '</div>' +
                        '</div>';
                },
                values: function (text, cb) {
                    if (text.length < 1) {
                        cb([]);
                        return;
                    }
                    $.ajax({
                        url: base_url + 'pengumuman/getusers',
                        data: { q: text },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status && response.data) {
                                cb(response.data);
                            } else {
                                cb([]);
                            }
                        },
                        error: function() {
                            cb([]);
                        }
                    });
                }
            });

            // Attach Tribute to comment and reply textareas
            tribute.attach(document.getElementById('commentText'));
            tribute.attach(document.getElementById('replyText'));
            console.log('Tribute.js @mention initialized');
        } else {
            console.warn('Tribute.js not loaded. @mention feature disabled.');
        }

        $('#formpengumuman').submit(function (e) {
            e.stopPropagation();
            e.preventDefault();

            // Manual validation for summernote
            if ($('#text-pengumuman').summernote('isEmpty')) {
                showDangerToast('Isi pengumuman tidak boleh kosong');
                return;
            }

            var idPost = $('#id-post-pengumuman').val();
            var saveUrl = idPost ? base_url + "pengumuman/update" : base_url + "pengumuman/save";

            $.ajax({
                url: saveUrl,
                type: 'POST',
                data: $(this).serialize(),
                success: function (data) {
                    //console.log(data);
                    if (data) {
                        window.location.reload();
                    } else {
                        showDangerToast('gagal menyimpan');
                    }
                }, error: function (xhr, status, error) {
                    console.log('error', xhr.responseText);
                }
            });
        });

        $('#formrunningtext').submit(function (e) {
            e.stopPropagation();
            e.preventDefault();
            var data = [];
            $('#tb-text tbody tr').each(function () {
                var row = {};
                row.id_text = $(this).find('td:eq(0)').text().replace('#', '').trim();
                row.text = $(this).find('td:eq(1)').text().trim();
                data.push(row);
            });

            console.log(data);
            $.ajax({
                url: base_url + "pengumuman/saverunningtext",
                type: 'POST',
                data: { text: JSON.stringify(data) },
                success: function (data) {
                    console.log(data);
                    if (data) {
                        window.location.reload();
                    } else {
                        showDangerToast('gagal menyimpan');
                    }
                }, error: function (xhr, status, error) {
                    console.log('error', xhr.responseText);
                }
            });
        });

        $('#komentar').submit(function (e) {
            e.stopPropagation();
            e.preventDefault();
            
            const $form = $(this);
            const idPost = $form.find('#id-post').val();
            const text = $form.find('textarea[name="text"]').val();
            
            if (!text || text.trim() === '') {
                showDangerToast('Komentar tidak boleh kosong');
                return;
            }
            
            console.log('Submitting comment:', { id_post: idPost, text: text });

            // Build form data with CSRF
            var formData = {
                id_post: idPost,
                text: text,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            };

            $.ajax({
                url: base_url + "pengumuman/savekomentar",
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $form.find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...');
                },
                success: function (response) {
                    console.log('Comment response:', response);
                    if (response.status) {
                        // Blur active element to prevent ARIA focus error
                        if (document.activeElement) {
                            document.activeElement.blur();
                        }
                        addComments(idPost, response.data, false);
                        
                        // Hide modal using Bootstrap 5 API
                        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('komentarModal'));
                        modal.hide();
                        
                        // Reset form
                        $form[0].reset();
                        
                        showSuccessToast('Komentar berhasil ditambahkan');
                    } else {
                        showDangerToast(response.message || 'Gagal menyimpan komentar');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Comment error:', xhr.responseText);
                    showDangerToast('Terjadi kesalahan: ' + error);
                },
                complete: function() {
                    $form.find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-send me-1"></i> Kirim');
                }
            });
        });

        $('#balasan').submit(function (e) {
            e.stopPropagation();
            e.preventDefault();
            
            const $form = $(this);
            const idComment = $form.find('#id-comment').val();
            const text = $form.find('textarea[name="text"]').val();
            
            if (!text || text.trim() === '') {
                showDangerToast('Balasan tidak boleh kosong');
                return;
            }
            
            console.log('Submitting reply:', { id_comment: idComment, text: text });

            // Build form data with CSRF
            var formData = {
                id_comment: idComment,
                text: text,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            };

            $.ajax({
                url: base_url + "pengumuman/savebalasan",
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $form.find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...');
                },
                success: function (response) {
                    console.log('Reply response:', response);
                    if (response.status) {
                        // Blur active element to prevent ARIA focus error
                        if (document.activeElement) {
                            document.activeElement.blur();
                        }
                        addReplies(idComment, response.data, false);
                        
                        // Hide modal using Bootstrap 5 API
                        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('balasanModal'));
                        modal.hide();
                        
                        // Reset form
                        $form[0].reset();
                        
                        showSuccessToast('Balasan berhasil ditambahkan');
                    } else {
                        showDangerToast(response.message || 'Gagal menyimpan balasan');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Reply error:', xhr.responseText);
                    showDangerToast('Terjadi kesalahan: ' + error);
                },
                complete: function() {
                    $form.find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-reply me-1"></i> Balas');
                }
            });
        });

        $('#komentarModal').on('show.bs.modal', function (e) {
            var button = $(e.relatedTarget);
            var id = button.data('id');
            $(this).find('input[name="id_post"]').val(id);
        });

        $('#balasanModal').on('show.bs.modal', function (e) {
            var button = $(e.relatedTarget);
            var id = button.data('id');
            $(this).find('input[name="id_comment"]').val(id);
        });

    });

</script>
<?= $this->endSection() ?>