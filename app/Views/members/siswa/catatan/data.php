<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 container-p-x main-content-overlap fade-in-up pb-5 mb-5 pb-lg-4 mb-lg-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Main Card -->
            <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <!-- Header Decoration -->
                <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 0;">
                    <div style="position: absolute; top: -10%; right: -5%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(234, 88, 12, 0.08) 0%, rgba(255,255,255,0) 70%); border-radius: 50%;"></div>
                </div>

                <div class="card-body p-0 position-relative z-1">
                    <!-- Top header section -->
                    <div class="p-3 p-md-4 border-bottom bg-white d-flex justify-content-between align-items-center mb-0">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-warning bg-gradient text-white rounded-circle shadow-sm me-2 me-md-3 d-flex align-items-center justify-content-center border border-warning" style="width: 48px; height: 48px;">
                                <i class="bi bi-journal-text fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0 fs-5 fs-md-4" style="letter-spacing: -0.5px;"><?= esc($judul ?? 'Catatan Siswa') ?></h5>
                                <div class="text-muted small mt-1 fw-medium text-uppercase letter-spacing-1 d-none d-sm-block" style="font-size: 0.75rem;"><?= esc($subjudul ?? 'Daftar catatan dari guru untuk anda') ?></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('dashboard') ?>"
                                class="btn btn-primary rounded-pill px-3 px-md-4 py-2 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                <i class="bi bi-arrow-left me-0 me-md-2"></i> 
                                <span class="d-none d-md-inline">Kembali</span>
                            </a>
                        </div>
                    </div>

                    <div class="row g-0">
                        <!-- Left Layout: List -->
                        <div class="col-lg-5 col-xl-4 bg-light bg-opacity-50 border-end" style="max-height: 700px; overflow-y: auto;">
                            <!-- Legend Header -->
                            <div class="bg-white p-3 border-bottom shadow-sm z-1 position-sticky top-0">
                                <h6 class="small fw-bold text-muted text-uppercase letter-spacing-1 mb-3">Keterangan Level</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1"><i class="bi bi-circle-fill me-1 small"></i>Saran</div>
                                    <div class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 rounded-pill px-2 py-1"><i class="bi bi-circle-fill me-1 small"></i>Teguran</div>
                                    <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1"><i class="bi bi-circle-fill me-1 small"></i>Peringatan</div>
                                    <div class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 rounded-pill px-2 py-1"><i class="bi bi-circle-fill me-1 small"></i>Sangsi</div>
                                </div>
                            </div>

                            <div class="list-group list-group-flush" id="list-catatan">
                                <?php if (!empty($catatan) && count((array)$catatan) > 0): ?>
                                    <?php foreach ($catatan as $cat):
                                        $for = ($cat->type ?? '') === '1' ? 'Semua siswa kelas ' . ($siswa->nama_kelas ?? '') : ($siswa->nama ?? '');
                                        $readed1 = ($cat->type ?? '') === '1' && (!empty($cat->reading) && is_array($cat->reading) && in_array($siswa->id_siswa ?? '', $cat->reading));
                                        $readed2 = ($cat->type ?? '') === '2' && ($cat->readed ?? '0') !== '0';
                                        $isUnread = !($readed1 || $readed2);
                                        
                                        $levelColors = [
                                            '1' => 'text-success',
                                            '2' => 'text-warning', 
                                            '3' => 'text-danger',
                                            '4' => 'text-dark'
                                        ];
                                        $levelColor = $levelColors[$cat->level ?? ''] ?? 'text-secondary';
                                        $levelNames = ['1' => 'Saran', '2' => 'Teguran', '3' => 'Peringatan', '4' => 'Sangsi'];
                                        $levelName = $levelNames[$cat->level ?? ''] ?? 'Info';
                                    ?>
                                        <a href="javascript:void(0)" class="list-group-item list-group-item-action border-bottom p-4 py-3 cursor-pointer transition-all hover-bg-light catatan-item <?= $isUnread ? 'bg-warning bg-opacity-10' : 'bg-white' ?>"
                                            data-table="<?= esc($cat->table ?? '') ?>"
                                            data-id="<?= esc($cat->id_catatan ?? '') ?>">
                                            <div class="d-flex gap-3 align-items-start">
                                                <img class="rounded-circle object-fit-cover shadow-sm border border-secondary border-opacity-25" style="width: 48px; height: 48px;"
                                                    src="<?= base_url($cat->foto_guru ?? 'assets/img/guru.png') ?>"
                                                    onerror="this.src='<?= base_url('assets/img/guru.png') ?>'" />
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate me-2"><?= esc($cat->nama_guru ?? '') ?></h6>
                                                        <i class="bi bi-circle-fill <?= $levelColor ?>" style="font-size: 10px;" title="<?= $levelName ?>" data-bs-toggle="tooltip"></i>
                                                    </div>
                                                    <p class="small text-muted mb-2 text-truncate"><?= esc($for) ?></p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-muted opacity-75"><i class="bi bi-clock me-1"></i><?= buat_tanggal(date('D, d M Y H:i', strtotime($cat->tgl ?? ''))) ?></span>
                                                        <span class="badge rounded-pill isreaded <?= $isUnread ? 'bg-warning text-dark border border-warning' : 'bg-light text-muted border' ?>">
                                                            <?= $isUnread ? '<i class="bi bi-envelope-x me-1"></i> Baru' : '<i class="bi bi-envelope-open me-1"></i> Dibaca' ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 px-4 my-5">
                                        <div class="d-inline-flex p-4 rounded-circle bg-light text-muted border border-dashed mb-3">
                                            <i class="bi bi-journal-x fs-1"></i>
                                        </div>
                                        <h5 class="text-muted fw-bold">Tidak ada catatan</h5>
                                        <p class="text-muted small">Belum ada catatan dari guru untuk saat ini.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Right Layout: Content Detail (Desktop Only) -->
                        <div class="col-lg-7 col-xl-8 d-none d-lg-flex flex-column position-relative bg-white" style="min-height: 500px;">
                            
                            <!-- Empty State Desktop -->
                            <div id="empty-state" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center p-5 text-center fade-in-up">
                                <div class="bg-light rounded-circle p-4 mb-3 border border-dashed text-primary opacity-50">
                                    <i class="bi bi-journal-check" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="fw-bold text-muted">Pilih Catatan</h4>
                                <p class="text-muted">Pilih salah satu catatan dari daftar di sebelah kiri untuk melihat isi detailnya.</p>
                            </div>

                            <!-- Detail Content Area Desktop -->
                            <div id="detail" class="d-none h-100 flex-column flex-grow-1 overflow-hidden fade-in-up">
                                <div class="p-4 p-md-5 bg-white border-bottom shadow-sm z-1">
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="foto-guru" class="rounded-circle object-fit-cover shadow-sm border border-secondary border-opacity-25" style="width: 64px; height: 64px;"
                                            src="<?= base_url('assets/img/guru.png') ?>" />
                                        <div>
                                            <h4 id="nama-guru" class="fw-bold text-dark mb-1">Nama Guru</h4>
                                            <p id="jabatan-guru" class="text-primary fw-bold text-uppercase letter-spacing-1 small mb-0">Jabatan</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 p-md-5 flex-grow-1 overflow-y-auto bg-light bg-opacity-10 text-dark position-relative">
                                     <div id="isi" class="fs-6 lh-lg text-break"></div>
                                </div>
                            </div>

                            <!-- Loading Overlay Desktop -->
                            <div id="overlay" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 z-3 d-flex flex-column align-items-center justify-content-center" style="backdrop-filter: blur(4px);">
                                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                                <h6 class="mt-3 fw-bold text-primary">Memuat Catatan...</h6>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Modal Detail (Bootstrap Modal) -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-light border-0 pb-3">
                <div class="d-flex align-items-center gap-3 w-100">
                    <img id="foto-guru-modal" class="rounded-circle object-fit-cover shadow-sm border border-secondary border-opacity-25" style="width: 48px; height: 48px;" src="<?= base_url('assets/img/guru.png') ?>" />
                    <div class="flex-grow-1">
                        <h6 id="nama-guru-modal" class="fw-bold text-dark mb-0">Nama Guru</h6>
                        <small id="jabatan-guru-modal" class="text-primary fw-bold letter-spacing-1 text-uppercase">Jabatan</small>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4 bg-light bg-opacity-10 position-relative">
                <div id="isi-modal" class="fs-6 lh-base text-break text-dark"></div>
                <!-- Loading Overlay Modal -->
                <div id="overlay-modal" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 z-3 flex-column align-items-center justify-content-center" style="backdrop-filter: blur(4px);">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 bg-light bg-opacity-10">
                 <button type="button" class="btn btn-primary rounded-pill w-100 fw-bold shadow-sm" data-bs-dismiss="modal">Tutup Catatan</button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MOBILE BOTTOM NAV ══ -->
<nav class="position-fixed bottom-0 start-0 w-100 d-flex d-lg-none bg-white border-top shadow-lg" style="padding-bottom: env(safe-area-inset-bottom); z-index: 1040;">
    <div class="d-flex justify-content-around align-items-center w-100 py-2 px-1">
        <a href="<?= base_url('dashboard') ?>" id="bn-home" class="text-decoration-none d-flex flex-column align-items-center text-primary bg-primary bg-opacity-10 py-1 px-3 rounded-pill transition-all" style="min-width: 60px;">
            <i class="bi bi-house-door-fill fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Home</small>
        </a>

        <button onclick="logout()" class="btn btn-link text-decoration-none d-flex flex-column align-items-center text-secondary py-1 px-3 rounded-pill transition-all" style="min-width: 60px; box-shadow: none;">
            <i class="bi bi-box-arrow-right fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Keluar</small>
        </button>
    </div>
</nav>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .cursor-pointer { cursor: pointer; }
    .hover-bg-light:hover { background-color: #f8f9fa !important; }
    .transition-all { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .fade-in-up { animation: fadeInUp 0.4s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .letter-spacing-1 { letter-spacing: 1px; }
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-2px); }
    
    /* Scrollbar */
    .list-group::-webkit-scrollbar,
    .overflow-y-auto::-webkit-scrollbar { width: 6px; }
    .list-group::-webkit-scrollbar-track,
    .overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
    .list-group::-webkit-scrollbar-thumb,
    .overflow-y-auto::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    .list-group:hover::-webkit-scrollbar-thumb,
    .overflow-y-auto:hover::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let itemsClicked = [];
    let fotoGuru;
    
    $(document).ready(function () {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Bootstrap Modal Instance
        var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

        function viewDetail(data, table, id) {
            var detail = data.detail;
            var reading = data.reading;
            var mapel = detail.nama_mapel == null ? ('Wali Kelas ' + detail.nama_kelas) : ('Guru ' + detail.nama_mapel);
            
            // On mobile view (lg breakpoint is 992px in BS5)
            if ($(window).width() < 992) {
                $('#nama-guru-modal').html(detail.nama_guru);
                $('#jabatan-guru-modal').html(mapel);
                $('#isi-modal').html(detail.text);
                $('#foto-guru-modal').attr('src', fotoGuru);
                $('#overlay-modal').removeClass('d-flex').addClass('d-none');
            } else {
                $('#nama-guru').html(detail.nama_guru);
                $('#jabatan-guru').html(mapel);
                $('#isi').html(detail.text);
                $('#detail').removeClass('d-none').addClass('d-flex');
                $('#empty-state').removeClass('d-flex').addClass('d-none');
                $('#foto-guru').attr('src', fotoGuru);
                $('#overlay').removeClass('d-flex').addClass('d-none');
            }

            // Marking as Read
            var ada = reading && reading.length > 0 && $.inArray(detail.id_siswa, reading) > -1;
            var readed;
            if (detail.type === '1') readed = ada;
            else readed = detail.readed !== '0';

            const clicked = $.inArray(table + '-' + detail.id_catatan, itemsClicked) > -1;

            if (!readed && !clicked) {
                itemsClicked.push(table + '-' + id);
                $.ajax({
                    url: base_url + 'siswa/readed/' + table + '/' + detail.id_catatan,
                    type: 'GET',
                    success: function (response) {
                        console.log('Catatan dibaca');
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }
        }

        $('.catatan-item').click(function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var table = $(this).data('table');
            fotoGuru = $(this).find('img').attr('src');

            // Handling UI states
            $('.catatan-item').removeClass('bg-light border-start border-4 border-primary');
            $(this).removeClass('bg-warning bg-opacity-10 bg-white').addClass('bg-light border-start border-4 border-primary');
            $(this).find('span.isreaded').html('<i class="bi bi-envelope-open me-1"></i> Dibaca').removeClass('bg-warning text-dark border-warning').addClass('bg-light text-muted border-secondary');

            if ($(window).width() < 992) {
                // Show modal first with loader
                $('#overlay-modal').removeClass('d-none').addClass('d-flex');
                detailModal.show();
            } else {
                $('#empty-state').removeClass('d-flex').addClass('d-none');
                $('#detail').removeClass('d-none').addClass('d-flex');
                $('#overlay').removeClass('d-none').addClass('d-flex');
            }

            $.ajax({
                url: base_url + 'siswa/detailcatatan/' + table + '/' + id,
                type: 'GET',
                success: function (response) {
                    viewDetail(response, table, id);
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    $('#overlay').removeClass('d-flex').addClass('d-none');
                    $('#overlay-modal').removeClass('d-flex').addClass('d-none');
                }
            });
        });

        $(`img`).on('error', function () {
            $(this).attr('src', base_url + 'assets/img/guru.png');
        });
    });
</script>
<?= $this->endSection() ?>