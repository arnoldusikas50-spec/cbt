<?= $this->extend('layouts/siswa') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/app/css/cbt-ujian.css?v=20250710') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .grid-soal-wrapper {
        display: grid !important;
        grid-template-columns: repeat(5, 1fr) !important;
        gap: 0.5rem;
        width: 100%;
    }

    /* Copyright footer di bagian bawah */
    #approved {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 150px);
    }

    #approved .card-footer.bg-white {
        margin-top: auto;
        border-radius: 0 0 20px 20px;
    }

    @media (min-width: 992px) {
        .grid-soal-wrapper {
            grid-template-columns: repeat(10, 1fr) !important;
        }
    }

    /* Fix visibility of answer letter when selected */
    .container-jawaban input:checked~.checkmark,
    .container-jawaban input:checked~.boxmark {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }

    @media (max-width: 576px) {
        .small-mobile {
            font-size: 0.85rem !important;
        }
        #remedial-banner .badge {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.75rem !important;
        }
        #remedial-banner h5 {
            font-size: 1rem !important;
        }
    }
</style>

<main>

    <div class="container-fluid py-1 py-md-4 px-1 px-md-3 main-content-overlap">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <!-- Wrapper for Not Approved / Error State (Dynamic) -->
                <div id="noapproved" class="d-none">
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-body p-5 text-center">

                            <!-- Dynamic Icon -->
                            <div class="mb-4" style="font-size: 6rem; line-height: 1;" id="err-icon">
                                ⏱
                            </div>

                            <!-- Dynamic Title -->
                            <h2 class="fw-bold mb-2" id="err-title">Waktu Habis!</h2>

                            <!-- Dynamic Message -->
                            <p class="text-muted mb-4" id="err-msg">
                                Waktu pengerjaan ujian telah selesai
                            </p>

                            <!-- Dynamic Status Badge -->
                            <div class="d-flex justify-content-center mb-4" id="err-badge-wrap">
                                <div id="err-badge"
                                    class="d-inline-flex align-items-center gap-2 bg-success bg-opacity-10 text-success rounded-pill px-4 py-2">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span class="fw-semibold">Jawaban telah disimpan</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div id="err-actions">
                                <a href="<?= base_url('cbtsiswa/cbt') ?>"
                                    class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold">
                                    <i class="bi bi-arrow-left me-2"></i> Kembali
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Main Exam Interface -->
                <div id="approved" class="d-none" data-jadwal-id="<?= $jadwal->id_jadwal ?? '' ?>" data-bank-id="<?= $jadwal->id_bank ?? '' ?>">
                    <!-- REMEDIAL BANNER -->
                    <div id="remedial-banner" class="d-none">
                        <div class="alert alert-info border-0 rounded-0 mb-0" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 5px solid #2196f3 !important;">
                            <div class="d-flex align-items-center gap-2 gap-sm-3 p-2 p-sm-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-arrow-repeat fs-4 fs-sm-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 mb-sm-1 fw-bold text-primary">Mode Remedial</h6>
                                    <p class="mb-2 text-dark small-mobile d-none d-sm-block">
                                        Anda sedang dalam mode remedial. Anda hanya dapat mengubah jawaban pada soal yang sebelumnya dijawab salah.
                                        <span class="d-block mt-1 small text-muted">
                                            Soal yang sudah dijawab benar akan ditandai dengan <span class="badge bg-success">✓ BENAR</span> dan tidak dapat diubah.
                                        </span>
                                    </p>
                                </div>
                            </div>


                        </div>
                    </div>

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
                                        class="btn btn-primary rounded-pill d-flex align-items-center justify-content-center gap-2 px-3 py-2 shadow-sm hover-scale"
                                        id="btn-info-mobile">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span class="d-none d-md-inline small fw-bold">INFO SOAL</span>
                                    </button>

                                    <!-- Timer -->
                                    <div class="flex-grow-0 d-flex justify-content-center">
                                        <div class="bg-white border text-dark rounded-pill px-3 py-2 shadow-none d-flex align-items-center gap-2"
                                            style="border-color: #cbd5e1 !important; min-width: 140px; justify-content: center;">
                                            <span class="small opacity-75 d-none d-md-inline fw-bold"
                                                style="font-size: 0.8rem;">SISA WAKTU:</span>
                                            <span id="timer"
                                                class="fw-bold font-monospace fs-6 text-danger">00:00:00</span>
                                        </div>
                                    </div>

                                    <!-- List Button -->
                                    <button data-bs-toggle="modal" data-bs-target="#daftarModal"
                                        class="btn btn-primary rounded-pill d-flex align-items-center justify-content-center gap-2 px-3 py-2 shadow-sm hover-scale">
                                        <i class="bi bi-grid-3x3-gap-fill"></i>
                                        <span class="d-none d-md-inline small fw-bold">DAFTAR SOAL</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Font Size & Subject -->
                        <div class="row align-items-center g-2 mt-0">
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
                                    <?= esc($nama_mapel ?? 'Mata Pelajaran') ?>
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
                                                <form id="jawab">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="siswa"
                                                        value="<?= $siswa->id_siswa ?? '' ?>">
                                                    <input type="hidden" name="jadwal"
                                                        value="<?= $jadwal->id_jadwal ?? '' ?>">
                                                    <input type="hidden" name="bank"
                                                        value="<?= $jadwal->id_bank ?? '' ?>">

                                                    <div id="konten-jawaban" class="w-100 h-100 overflow-auto"
                                                        style="max-height: 60vh;"></div>
                                                </form>
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
                            <button type="button" class="btn btn-warning text-white rounded-pill px-2 px-md-4 py-2 fw-bold"
                                onclick="raguRagu(event)">
                                <div class="form-check form-check-inline m-0 d-flex align-items-center gap-1" style="cursor: pointer;">
                                    <input class="form-check-input mt-0" type="checkbox" style="cursor: pointer;"
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
                            </div>
                        </div>
                    </div>

                    <!-- Copyright Footer -->
                    <div class="card-footer bg-white border-top py-2 text-center mt-auto">
                        <small class="text-muted fw-bold">
                            COPYRIGHT &copy; <?= date('Y'); ?> | IKASMEDIA
                        </small>
                    </div>

                </div>
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
                        style="max-width: 420px; width: 90%;">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger" style="width: 72px; height: 72px;">
                            <i class="bi bi-wifi-off fs-1"></i>
                        </div>
                        <h4 class="text-danger fw-bold mb-2">Koneksi Terputus!</h4>
                        <p class="text-muted small mb-3">Koneksi ke server ujian terputus. Jawaban Anda tetap aman di perangkat. Sistem sedang mencoba menghubungkan kembali secara otomatis.</p>
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3 text-secondary small">
                            <span class="spinner-border spinner-border-sm text-danger" role="status"></span>
                            <span>Mencoba menghubungkan kembali...</span>
                        </div>
                        <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm w-100 py-2" onclick="window.cbtManualReconnect && window.cbtManualReconnect()">
                            <i class="bi bi-arrow-repeat me-1"></i> Hubungkan Sekarang
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<!-- Modal Daftar Soal -->
<div class="modal fade" id="daftarModal" tabindex="-1" aria-labelledby="daftarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
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
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" onclick="location.reload()">Coba
                    Lagi / Refresh</button>
            </div>
        </div>
    </div>
</div>

<form id="up">
    <?= csrf_field() ?>
    <input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?? '' ?>">
    <input type="hidden" name="jadwal" value="<?= $jadwal->id_jadwal ?? '' ?>">
    <input type="hidden" name="bank" value="<?= $jadwal->id_bank ?? '' ?>">
    <input type="hidden" name="token" value="<?= session()->get('token') ?? '' ?>">
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const pelanggaran = '<?= $pelanggaran ?? '0' ?>';
    const resetLogin = '<?= $reset_login ?? '0' ?>';

    // Timer variabels from server
    var sisaWaktuServer = <?= $sisa_waktu ?? 3600 ?>; // Sisa waktu dalam detik dari server
    var durasiUjianServer = <?= ($jadwal->durasi ?? 60) * 60 ?>; // Durasi dalam detik
    var elapsedWaktuServer = <?= $elapsed_waktu ?? 0 ?>; // Waktu yang sudah berjalan

    // ── Server-sync timer: setiap kali getSoal() dipanggil, server mengirimkan
    // `durasi.sisa_waktu` yang authoritative. JS harus update countdown ke nilai ini.
    // Fungsi ini dipanggil dari cbt-ujian.js setelah response getSoal diterima.
    window.syncTimerFromServer = function(sisaDetik) {
        if (typeof sisaDetik === 'number' && sisaDetik >= 0) {
            sisaWaktuServer = sisaDetik;
            // Jika ada fungsi timer global yang terdaftar, panggil ulang
            if (typeof window.updateTimerDisplay === 'function') {
                window.updateTimerDisplay(sisaDetik);
            }
        }
    };

    // Inject Data Soal & Jawaban untuk Navigasi JS (Fix Modal Undefined)
    var cbt_questions = <?= json_encode($questions ?? []) ?>;
    var cbt_answers = <?= json_encode($answers ?? []) ?>;
    // Alias common names to ensure JS compatibility
    var questions = cbt_questions;
    var soalJson = cbt_questions;
    var list_soal = cbt_questions;
    var jawaban_siswa = cbt_answers;

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

<script src="<?= base_url('assets/app/js/redirect.js') ?>"></script>
<script src="<?= base_url('assets/js/cbt-remedial.js?v=20250709') ?>"></script>
<script>
    // Auto-initialize remedial mode if URL has remedial=1 parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('remedial')) {
        document.addEventListener('DOMContentLoaded', function() {
            const approvedDiv = document.getElementById('approved');
            if (approvedDiv) {
                const jadwalId = approvedDiv.dataset.jadwalId;
                const bankId = approvedDiv.dataset.bankId;
                if (jadwalId && bankId) {
                    // Show remedial banner
                    const banner = document.getElementById('remedial-banner');
                    if (banner) banner.classList.remove('d-none');
                    // Initialize remedial handler
                    cbtRemedial.init(jadwalId, bankId);
                }
            }
        });
    }
</script>
<script src="<?= base_url('assets/app/js/linker-list.js') ?>"></script>
<script src="<?= base_url('assets/plugins/element-queries/ElementQueries.js') ?>"></script>
<script src="<?= base_url('assets/plugins/element-queries/ResizeSensor.js') ?>"></script>
<script src="<?= base_url('assets/plugins/katex/katex.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/content-zoom-slider.js') ?>"></script>
<script src="<?= base_url('assets/app/js/mediaPlayer.js') ?>"></script>
<script src="<?= base_url('assets/plugins/mathjax/MathJax.js?config=TeX-MML-AM_SVG') ?>"></script>
<script src="<?= base_url('assets/app/js/cbt-ujian.js?v=20261020') ?>"></script>
<?= $this->endSection() ?>