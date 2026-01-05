<link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/cbt-ujian.css?v=<?= time() ?>">

<main>

    <div class="container-fluid py-1 py-md-4 px-1 px-md-3 main-content-overlap">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <!-- Wrapper for Not Approved / Error State (Time's Up) -->
                <div id="noapproved" class="d-none">
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-body p-5 text-center">

                            <!-- Illustration -->
                            <div class="mb-4" style="font-size: 6rem; line-height: 1;">
                                ?
                            </div>

                            <!-- Title -->
                            <h2 class="fw-bold text-dark mb-2">Waktu Habis!</h2>

                            <!-- Message -->
                            <p class="text-muted mb-4" id="err-msg">
                                Waktu pengerjaan ujian telah selesai
                            </p>

                            <!-- Status Badge -->
                            <div class="d-flex justify-content-center mb-4">
                                <div
                                    class="d-inline-flex align-items-center gap-2 bg-success bg-opacity-10 text-success rounded-pill px-4 py-2">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span class="fw-semibold">Jawaban telah disimpan</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div>
                                <a href="<?= base_url('cbtsiswa/cbt') ?>"
                                    class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold">
                                    <i class="bi bi-arrow-left me-2"></i> Kembali
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Main Exam Interface -->
                <div id="approved" class="d-none">
                    <div class="card border-0 shadow rounded-4 position-relative">

                        <!-- Watermark Logo (top-right corner) -->
                        <img src="<?= base_url('assets/img/logo-card.png') ?>" alt=""
                            class="position-absolute top-0 end-0"
                            style="width: 400px; height: auto; opacity: 0.05; filter: brightness(0.3); pointer-events: none; z-index: 10;">

                        <!-- Header -->
                        <div class="card-header bg-white p-3 p-md-4 border-bottom">
                            <!-- Row 1: Soal No & Buttons -->
                            <div class="row align-items-center g-2 mb-2">
                                <!-- Question Title -->
                                <!-- Question Title -->
                                <div class="col-12 col-md-4 text-center text-md-start">
                                    <h4 class="mb-0 fw-bold text-dark">
                                        Soal nomor <span id="nomor-soal" class="text-primary fs-3"></span>
                                    </h4>
                                </div>

                                <!-- Toolbar (Info, Timer, List) -->
                                <div class="col-12 col-md-8">
                                    <div
                                        class="d-flex align-items-center justify-content-center justify-content-md-end gap-2 gap-md-2">
                                        <!-- Info Button -->
                                        <button onclick="toggleInfo()"
                                            class="btn btn-primary btn-md-outline-primary rounded-circle rounded-md-pill d-flex align-items-center justify-content-center gap-2 p-2 px-md-3 py-md-2 text-white text-md-primary border-0 border-md"
                                            id="btn-info-mobile">
                                            <i class="bi bi-info-circle-fill fs-5"></i>
                                            <span class="d-none d-md-inline small fw-bold text-uppercase">Informasi
                                                Soal</span>
                                        </button>

                                        <!-- Timer -->
                                        <div class="flex-grow-0 d-flex justify-content-center">
                                            <div class="bg-white border text-dark rounded-pill px-3 py-2 shadow-none d-flex align-items-center gap-2"
                                                style="border-color: #cbd5e1 !important;">
                                                <span class="small opacity-75 d-none d-md-inline fw-bold"
                                                    style="font-size: 0.8rem;">Sisa Waktu :</span>
                                                <span id="timer" class="fw-bold font-monospace fs-6">00:00:00</span>
                                            </div>
                                        </div>

                                        <!-- List Button -->
                                        <button data-bs-toggle="modal" data-bs-target="#daftarModal"
                                            class="btn btn-primary btn-md-outline-primary rounded-circle rounded-md-pill d-flex align-items-center justify-content-center gap-2 p-2 px-md-3 py-md-2 text-white text-md-primary border-0 border-md">
                                            <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                                            <span class="d-none d-md-inline small fw-bold">Daftar Soal</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-0 opacity-10">

                            <!-- Row 2: Font Size & Subject -->
                            <div class="row align-items-center g-2 mt-0">
                                <!-- Font Resizer -->
                                <!-- Font Resizer -->
                                <div
                                    class="col-12 col-md-6 d-flex align-items-center justify-content-center justify-content-md-start gap-2 gap-md-3 text-secondary">
                                    <span class="small fw-bold text-nowrap d-none d-md-inline">Ukuran font soal:</span>
                                    <div class="d-flex align-items-end gap-3 font-resizer-group">
                                        <span role="button" id="minus" class="font-resizer-btn fs-6 text-muted">A</span>
                                        <span role="button" id="normal"
                                            class="font-resizer-btn fs-5 text-dark active">A</span>
                                        <span role="button" id="plus" class="font-resizer-btn fs-4 text-dark">A</span>
                                    </div>
                                </div>

                                <!-- Subject Name -->
                                <div class="col-12 col-md-6 text-center text-md-end mt-1 mt-md-0">
                                    <h6 class="mb-0 fw-bold text-secondary text-uppercase mata-pelajaran">
                                        <?= $nama_mapel ?? 'Mata Pelajaran' ?>
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <!-- Question Body -->
                        <div class="card-body p-0 position-relative bg-white min-vh-50">
                            <!-- Info Panel (Hidden by default) -->
                            <div id="pnlKeteranganSoal"
                                class="alert alert-info border-0 rounded-0 mb-0 d-none bg-info bg-opacity-10">
                                <div id="examketerangancontent" class="p-3"></div>
                            </div>

                            <!-- Question Content -->
                            <div id="pnlSoal" class="p-1 p-md-4">
                                <div id="examcontent">
                                    <div id="box-soal" style="font-size:12pt;">
                                        <div class="konten-soal-jawab">
                                            <div class="row">
                                                <!-- Left Column: Question Text / Stimulus -->
                                                <div class="col-12 col-lg-6 pe-lg-4 position-relative mb-4 mb-lg-0">
                                                    <div id="konten-soal" class="question-content h-100 overflow-auto"
                                                        style="max-height: 60vh;"></div>
                                                    <!-- Vertical Divider for Desktop -->
                                                    <div class="d-none d-lg-block position-absolute end-0 top-0 h-100 border-end border-2 border-dotted"
                                                        style="border-right-style: dotted !important;" opacity="0.3">
                                                    </div>
                                                </div>

                                                <!-- Right Column: Answers -->
                                                <div class="col-12 col-lg-6 ps-lg-4">
                                                    <?= form_open('jawab', array('id' => 'jawab')) ?>
                                                    <input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
                                                    <input type="hidden" name="jadwal" value="<?= $id_jadwal ?? '' ?>">
                                                    <input type="hidden" name="bank" value="<?= $id_bank ?? '' ?>">

                                                    <div id="konten-jawaban" class="w-100 h-100 overflow-auto"
                                                        style="max-height: 60vh;"></div>
                                                    <?= form_close() ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Navigation -->
                        <div class="card-footer bg-light p-2 p-md-4 border-top">
                            <div class="d-flex justify-content-between align-items-center gap-1 gap-md-2">

                                <button class="btn btn-danger rounded-pill px-2 px-md-4 py-2 fw-bold" id="prev"
                                    onclick="prevSoal()">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="bi bi-chevron-left"></i>
                                        <span class="d-none d-md-inline">Soal sebelumnya</span>
                                    </span>
                                </button>

                                <!-- Ragu-ragu -->
                                <button class="btn btn-warning text-white rounded-pill px-2 px-md-4 py-2 fw-bold"
                                    onclick="raguRagu()">
                                    <div class="form-check form-check-inline m-0 d-flex align-items-center gap-1">
                                        <input class="form-check-input mt-0 pointer-events-none" type="checkbox"
                                            id="c_ragu_ragu" value="">
                                        <span class="d-none d-md-inline">Ragu-ragu</span>
                                    </div>
                                </button>

                                <!-- Next / Finish -->
                                <div> <!-- wrapper for grouping -->
                                    <button class="btn btn-danger rounded-pill px-3 fw-bold d-none" id="timer-selesai"
                                        disabled>
                                        Waktu Habis
                                    </button>
                                    <button class="btn btn-primary rounded-pill px-2 px-md-4 py-2 fw-bold" id="next"
                                        onclick="nextSoal()">
                                        <div class="d-flex align-items-center gap-1">
                                            <span id="text-next" class="d-flex align-items-center">
                                                <span class="d-none d-md-inline me-2">Soal berikutnya</span>
                                                <i class="bi bi-chevron-right"></i>
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Copyright -->
                        <div class="card-footer bg-white border-top py-2 text-center">
                            <small class="text-muted fw-bold">
                                COPYRIGHT &copy; <?= date('Y'); ?> | IKASMEDIA
                            </small>
                        </div>

                    </div>
                </div>

                <!-- Loading & Overlays -->
                <div id="loading"
                    class="position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex flex-column align-items-center justify-content-center"
                    style="z-index: 9999;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                    <div class="mt-3 fw-bold text-primary">Memuat Soal...</div>
                </div>

                <div id="offline-overlay"
                    class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white bg-opacity-95 d-flex align-items-center justify-content-center"
                    style="z-index: 10000;">
                    <div class="text-center p-4 rounded-4 shadow-lg border border-danger bg-white"
                        style="max-width: 400px;">
                        <i class="bi bi-wifi-off fs-1 text-danger mb-3 d-block"></i>
                        <h4 class="text-danger fw-bold">Koneksi Terputus!</h4>
                        <p class="text-muted mb-3">Koneksi ke server terputus. Harap menunggu, sistem akan mencoba
                            terhubung kembali.</p>
                        <div class="spinner-grow text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>


<div class="modal fade" id="daftarModal" tabindex="-1" role="dialog" aria-labelledby="daftarLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="daftarLabel">Daftar Nomor Soal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="konten-modal">
                </div>
            </div>

        </div>
    </div>
</div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-bold" id="toast-body">
                <!-- Message here -->
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Modal: Peraturan Ujian (Rules) -->
<div class="modal fade" id="modalRules" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <img src="<?= base_url('assets/img/welcome-asesmen.jpg') ?>" class="img-fluid rounded-3 mb-4"
                    style="max-height: 200px;">
                <h4 class="fw-bold mb-3">Peraturan Ujian</h4>
                <div class="text-start bg-light p-3 rounded-3 mb-4">
                    <div class="d-flex gap-3 mb-3">
                        <span
                            class="badge rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 24px; height: 24px;">1</span>
                        <div class="small fw-semibold">Selesaikan Asesmen dengan <span
                                class="badge bg-success">JUJUR</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <span
                            class="badge rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 24px; height: 24px;">2</span>
                        <div class="small fw-semibold">Kamu akan <span
                                class="badge bg-warning text-dark">DIALIHKAN</span>
                            ke mode fullscreen!</div>
                    </div>
                    <div class="d-flex gap-3">
                        <span
                            class="badge rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 24px; height: 24px;">3</span>
                        <div class="small fw-semibold">Selama Asesmen <span class="badge bg-danger">DILARANG
                                KELUAR</span>
                            mode fullscreen!</div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold" id="btnAgreeRules">
                    YA, SAYA MENGERTI
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Konfirmasi Selesai (Finish) -->
<div class="modal fade" id="modalConfirmFinish" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Selesai Ujian?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 d-flex align-items-center gap-3 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                    <div>
                        <div class="fw-bold">Peringatan Penting!</div>
                        <div class="small">Anda tidak akan bisa mengubah jawaban setelah ini.</div>
                    </div>
                </div>
                <p class="text-muted mb-0" id="finishMsg">Apakah anda benar-benar yakin ingin mengakhiri ujian ini?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold"
                    data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" id="btnRealFinish">
                    Ya, Akhiri Ujian
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Generic Info/Warning -->
<div class="modal fade" id="modalInfo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-info-circle text-primary display-3" id="infoIcon"></i>
                </div>
                <h5 class="fw-bold mb-2" id="infoTitle">Informasi</h5>
                <p class="text-muted small mb-4" id="infoText">pesan...</p>
                <button type="button" class="btn btn-primary rounded-pill w-100 fw-bold"
                    data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Blocked -->
<div class="modal fade" id="modalBlocked" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-5 text-center">
                <i class="bi bi-shield-lock-fill text-danger display-1 mb-3"></i>
                <h3 class="fw-bold text-danger mb-2">DIBLOKIR PENGAWAS</h3>
                <div class="bg-decoration-none p-3 rounded bg-light mb-4 text-muted small" id="blockedMsg"></div>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold"
                    onclick="location.reload()">Coba Lagi / Refresh</button>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'up')) ?>
<input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?? '' ?>">
<input type="hidden" name="jadwal" value="<?= $id_jadwal ?? '' ?>">
<input type="hidden" name="bank" value="<?= $id_bank ?? '' ?>">
<?= form_close() ?>

<script>
    const pelanggaran = '<?= $pelanggaran ?? '0' ?>';
    const resetLogin = '<?= $reset_login ?? '0' ?>';

    // Toast Helper
    function showToast(message, type = 'info', autohide = true) {
        var toastEl = document.getElementById('liveToast');
        var toastBody = document.getElementById('toast-body');

        toastEl.className = 'toast align-items-center border-0';
        if (type === 'success') {
            toastEl.classList.add('text-white', 'bg-success');
        } else if (type === 'error') {
            toastEl.classList.add('text-white', 'bg-danger');
        } else if (type === 'warning') {
            toastEl.classList.add('text-dark', 'bg-warning');
        } else {
            toastEl.classList.add('bg-white');
        }

        toastBody.innerHTML = message;
        var toast = new bootstrap.Toast(toastEl, { autohide: autohide });
        toast.show();
    }

    // Modal Helpers
    function showInfoModal(title, text, type = 'info') {
        $('#infoTitle').text(title);
        $('#infoText').html(text);

        var iconClass = 'bi-info-circle text-primary';
        if (type === 'warning') iconClass = 'bi-exclamation-triangle text-warning';
        if (type === 'error') iconClass = 'bi-x-circle text-danger';
        if (type === 'success') iconClass = 'bi-check-circle text-success';

        $('#infoIcon').attr('class', 'bi ' + iconClass + ' display-3');
        var myModal = new bootstrap.Modal(document.getElementById('modalInfo'));
        myModal.show();
    }

    function toggleInfo() {
        var pnl = document.getElementById('pnlKeteranganSoal');
        var content = document.getElementById('examketerangancontent');
        if (pnl && content && content.innerHTML.trim() !== "") {
            pnl.classList.toggle('d-none');
        } else {
            showToast('Tidak ada informasi tambahan untuk soal ini.', 'info');
        }
    }
</script>

<script src="<?= base_url() ?>/assets/app/js/redirect.js"></script>
<script src="<?= base_url() ?>/assets/app/js/linker-list.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>
<script src="<?= base_url() ?>/assets/plugins/katex/katex.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/content-zoom-slider.js"></script>
<script src="<?= base_url() ?>/assets/app/js/mediaPlayer.js"></script>
<script src="<?= base_url() ?>/assets/plugins/mathjax/MathJax.js?config=TeX-MML-AM_CHTML"></script>
<script src="<?= base_url() ?>/assets/app/js/cbt-ujian.js?v=<?= time() ?>"></script>