<style>
    .no-close .ui-dialog-titlebar-close {
        display: none;
    }
</style>
<div class="container-fluid py-4 container-p-x main-content-overlap">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Main Card -->
            <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden">
                <!-- Header Decoration -->
                <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 0;">
                    <div style="
                                    position: absolute;
                                    top: -10%;
                                    right: -5%;
                                    width: 400px;
                                    height: 400px;
                                    background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, rgba(255,255,255,0) 70%);
                                    border-radius: 50%;
                                "></div>
                </div>

                <div class="card-body p-4 position-relative z-1 ">
                    <!-- Watermark -->
                    <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background-color: #e0e7ff;
                                    -webkit-mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    -webkit-mask-size: 50%;
                                    mask-size: 50%;
                                    -webkit-mask-repeat: no-repeat;
                                    mask-repeat: no-repeat;
                                    -webkit-mask-position: center;
                                    mask-position: center;
                                    opacity: 0.05;
                                    z-index: -1;
                                    pointer-events: none;
                                "></div>

                    <!-- Header Area -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-4 me-3">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Catatan Siswa</h5>
                                <div class="text-muted small">Daftar catatan dari guru untuk anda</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('dashboard') ?>"
                                class="btn btn-primary border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                <i class="bi bi-arrow-left-circle-fill me-2 text-primary"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <!-- List Catatan (Left Column) -->
                        <div class="col-lg-4 col-md-5 mb-3">
                            <div class="card my-shadow mb-3">
                                <div class="card-header p-3"
                                    style="background: linear-gradient(120deg, #a1c4fd 0%, #c2e9fb 100%); color: white;">
                                    <h6 class="m-0 font-weight-bold">Daftar Catatan</h6>
                                </div>
                                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                                    <?php if (count($catatan) > 0): ?>
                                        <ul class="list-group list-group-flush" id="list-catatan">
                                            <?php foreach ($catatan as $cat):
                                                $for = $cat->type === '1' ? 'semua siswa kelas ' . $siswa->nama_kelas : $siswa->nama;
                                                $readed1 = $cat->type === '1' && ($cat->reading && in_array($cat->id_siswa, $cat->reading));
                                                $readed2 = $cat->type === '2' && $cat->readed !== '0';
                                                $bg_li = $readed1 || $readed2 ? 'list-group-item-light' : 'list-group-item-warning';
                                                ?>
                                                <li class="list-group-item list-group-item-action <?= $bg_li ?> p-3 pointer"
                                                    data-table="<?= $cat->table ?>" data-id="<?= $cat->id_catatan ?>">
                                                    <div class="media">
                                                        <img class="img-circle me-3" src="<?= base_url($cat->foto_guru) ?>"
                                                            width="40" height="40"
                                                            onerror="this.src='<?= base_url('assets/img/guru.png') ?>'" />
                                                        <div class="media-body">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <h6 class="mt-0 mb-0 font-weight-bold text-dark">
                                                                    <?= $cat->nama_guru ?>
                                                                </h6>
                                                                <?php
                                                                $levelColors = ['1' => 'text-success', '2' => 'text-warning', '3' => 'text-pink', '4' => 'text-danger'];
                                                                $color = $levelColors[$cat->level] ?? 'text-secondary';
                                                                ?>
                                                                <i class="bi bi-circle-fill text-xs <?= $color ?>"></i>
                                                            </div>
                                                            <small class="text-secondary"><?= $for ?></small>
                                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                                <small
                                                                    class="text-muted"><?= buat_tanggal(date('D, d M Y H:i', strtotime($cat->tgl ?? ''))) ?></small>
                                                                <small class="text-muted isreaded font-italic">
                                                                    <?= $readed1 || $readed2 ? 'sudah dibaca' : 'belum dibaca' ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="text-center p-4">
                                            <img src="<?= base_url() ?>assets/app/img/img-empty-state.png" width="80"
                                                class="mb-3">
                                            <p class="text-muted text-sm">Tidak ada catatan</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-body p-3">
                                    <p class="text-sm font-weight-bold mb-2">Keterangan Level:</p>
                                    <div class="d-flex flex-wrap">
                                        <div class="me-3 mb-1"><i class="bi bi-circle-fill text-success text-xs"></i>
                                            <span class="text-xs">Saran</span>
                                        </div>
                                        <div class="me-3 mb-1"><i class="bi bi-circle-fill text-warning text-xs"></i>
                                            <span class="text-xs">Teguran</span>
                                        </div>
                                        <div class="me-3 mb-1"><i class="bi bi-circle-fill text-pink text-xs"></i>
                                            <span class="text-xs">Peringatan</span>
                                        </div>
                                        <div class="me-3 mb-1"><i class="bi bi-circle-fill text-danger text-xs"></i>
                                            <span class="text-xs">Sangsi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Catatan (Right Column) -->
                        <div class="col-lg-8 col-md-7 d-none d-md-block">
                            <div class="card my-shadow" style="min-height: 500px">
                                <div class="card-header border-bottom-0">
                                    <h6 class="m-0 font-weight-bold text-dark">Detail Catatan</h6>
                                </div>
                                <div class="card-body position-relative">
                                    <div id="empty-state" class="text-center mt-5">
                                        <img src="<?= base_url() ?>assets/app/img/catatan.png" width="100"
                                            class="mb-3 opacity-50" style="opacity: 0.5">
                                        <p class="text-muted">Pilih catatan dari daftar di sebelah kiri
                                            untuk
                                            melihat detail.</p>
                                    </div>

                                    <div id="detail" class="d-none">
                                        <div class="media mb-4 align-items-center border-bottom pb-3">
                                            <img id="foto-guru" class="img-circle me-3"
                                                src="<?= base_url('/assets/img/guru.png') ?>" width="60" height="60" />
                                            <div class="media-body">
                                                <h5 id="nama-guru" class="mt-0 mb-1 font-weight-bold text-dark">
                                                    Nama Guru</h5>
                                                <span id="jabatan-guru" class="text-muted">Jabatan</span>
                                            </div>
                                        </div>
                                        <div id="isi" class="text-justify text-dark"
                                            style="font-size: 1.1em; line-height: 1.6;"></div>
                                    </div>

                                    <!-- Loading Overlay -->
                                    <div class="overlay d-none d-flex justify-content-center align-items-center position-absolute w-100 h-100"
                                        style="top:0; left:0; background: rgba(255,255,255,0.8); z-index: 10;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
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
</div>
<hr class="mt-auto">
<div class="col-md-12 text-center mt-3 pb-4">
    <div class="copyright"><small>COPYRIGHT &copy; <?= date('Y'); ?> | IKASMEDIA</small> </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="daftarLabel">Detail Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <div class="card-header">
                    <div class="media" style="line-height: 1.2">
                        <img id="foto-guru-modal" class="img-circle media-left"
                            src="<?= base_url('/assets/img/guru.png') ?>" width="50" height="50" />
                        <div class="media-body ms-4">
                            <span id="nama-guru-modal" class="text-lg"><b>Nama Guru</b></span>
                            <br />
                            <span id="jabatan-guru-modal"> Jabatan </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="isi-modal" class="text-justify"></div>
                </div>
                <!-- Loading Overlay Modal -->
                <div class="overlay d-none d-flex justify-content-center align-items-center position-absolute w-100 h-100"
                    style="top:0; left:0; background: rgba(255,255,255,0.8); z-index: 10;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<script>
    let itemsClicked = [];
    let fotoGuru;
    $(document).ready(function () {
        function screenSize() {
            var w = $(document).innerWidth();
            return (w < 768) ? 'xs' : ((w < 992) ? 'sm' : ((w < 1200) ? 'md' : 'lg'));
        }

        function viewDetail(data, table, id) {
            //console.log('size',screenSize());
            var detail = data.detail;
            var reading = data.reading;
            var mapel = detail.nama_mapel == null ? detail.jabatan + ' ' + detail.nama_kelas : 'Guru ' + detail.nama_mapel;
            if (screenSize() === 'xs') {
                $('#nama-guru-modal').html(detail.nama_guru);
                $('#jabatan-guru-modal').html(mapel);
                $('#isi-modal').html(detail.text);
                $('#detailModal').modal('show');
                $('#foto-guru-modal').attr('src', fotoGuru)
            } else {
                $('#nama-guru').html(detail.nama_guru);
                $('#jabatan-guru').html(mapel);
                $('#isi').html(detail.text);
                $('#detail').removeClass('d-none');
                $('#empty-state').addClass('d-none');
                $('#foto-guru').attr('src', fotoGuru)
            }
            $('.overlay').addClass('d-none');

            var ada = reading.length > 0 && $.inArray(detail.id_siswa, reading) > -1;
            var readed;
            if (detail.type === '1') readed = ada;
            else readed = detail.readed !== '0';
            console.log('siswa', detail.id_siswa);
            console.log('readed', readed);

            const clicked = $.inArray(table + '-' + detail.id_catatan, itemsClicked) > -1;

            if (!readed && !clicked) {
                itemsClicked.push(table + '-' + id);
                $.ajax({
                    url: base_url + 'siswa/readed/' + table + '/' + detail.id_catatan,
                    type: 'GET',
                    success: function (response) {
                        console.log('read', response);
                    },
                    error: function (xhr, error, status) {
                        console.log(xhr.responseText);
                    }
                });
            }

            console.log('detail', detail);
        }


        $('ul li').click(function (e) {
            var id = $(this).data('id');
            var table = $(this).data('table');
            fotoGuru = $(this).find('img').attr('src'); // Adjusted selector

            $.ajax({
                url: base_url + 'siswa/detailcatatan/' + table + '/' + id,
                type: 'GET',
                beforeSend: function () {
                    $('.overlay').removeClass('d-none');
                },
                success: function (response) {
                    console.log('response', response);
                    viewDetail(response, table, id);
                },
                error: function (xhr, error, status) {
                    console.log(xhr.responseText);
                    $('.overlay').addClass('d-none');
                }
            });
            $('ul li').removeClass('list-group-item-light list-group-item-warning active');
            $(this).removeClass('list-group-item-warning'); // Remove warning
            $(this).addClass('list-group-item-light'); // Mark as read style

            // Note: We might want a selected state style

            $(this).find('small.isreaded').html('sudah dibaca');
        });

    });

    $(`.img-circle`).each(function () {
        $(this).on("error", function () {
            $(this).attr("src", base_url + 'assets/img/guru.png');
        });
    });

</script>