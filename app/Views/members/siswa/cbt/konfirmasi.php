<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<!-- Add Signature Pad -->
<script src="<?= base_url('assets/plugins/signature/signature_pad.min.js') ?>"></script>

    <script>
    function showBlockedOverlay(message) {
        // Hentikan semua proses ujian agar tidak ada lagi request ke server setelah diblokir
        try { if (typeof statusCheckInterval !== 'undefined' && statusCheckInterval) clearInterval(statusCheckInterval); } catch (e) {}
        try { if (typeof timerOut !== 'undefined' && timerOut) clearTimeout(timerOut); } catch (e) {}
        try { if (typeof reconnectInterval !== 'undefined' && reconnectInterval) clearInterval(reconnectInterval); } catch (e) {}

        // Ganti SELURUH isi body dengan pesan blokir agar tidak ada notifikasi lain yang muncul
        document.body.innerHTML = `
            <style>
                html, body { height:100%; margin:0; }
                body {
                    background: linear-gradient(135deg,#fef2f2 0%,#fee2e2 50%,#fecaca 100%);
                    display:flex; align-items:center; justify-content:center;
                    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
                    overflow:hidden;
                }
                .block-card {
                    border:none; border-radius:1.5rem; box-shadow:0 20px 60px rgba(220,38,38,0.18);
                    background:#fff; max-width:480px; width:100%;
                    animation:blockPop 0.4s cubic-bezier(0.18,0.89,0.32,1.28);
                }
                @keyframes blockPop { from{opacity:0;transform:scale(0.9) translateY(20px);} to{opacity:1;transform:scale(1) translateY(0);} }
                .icon-wrap {
                    width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#dc2626,#991b1b);
                    display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;
                    box-shadow:0 12px 30px rgba(220,38,38,0.35);animation:blockPulse 2s infinite;
                }
                @keyframes blockPulse { 0%,100%{transform:scale(1);} 50%{transform:scale(1.06);} }
                .icon-wrap i { font-size:3rem;color:#fff; }
                .title-block { color:#991b1b; letter-spacing:-0.5px; }
                .msg-box { background:#fef2f2;border:1px solid #fecaca;border-radius:1rem;padding:1rem 1.25rem; }
                .btn-refresh {
                    background:linear-gradient(135deg,#dc2626,#991b1b);border:none;border-radius:50rem;
                    padding:0.85rem 2.5rem;font-weight:700;box-shadow:0 8px 20px rgba(220,38,38,0.3);
                    transition:transform 0.15s ease,box-shadow 0.15s ease;
                }
                .btn-refresh:hover { transform:translateY(-2px);box-shadow:0 12px 28px rgba(220,38,38,0.4);background:linear-gradient(135deg,#dc2626,#991b1b); }
            </style>
            <div class="block-card p-5 text-center">
                <div class="icon-wrap"><i class="bi bi-shield-lock-fill"></i></div>
                <h2 class="fw-bold title-block mb-3">Akses Diblokir</h2>
                <div class="msg-box mb-4">
                    <p class="text-danger fw-semibold mb-0" style="font-size:1rem;">${message}</p>
                </div>
                <p class="text-muted small mb-4"><i class="bi bi-info-circle me-1"></i>Silakan hubungi pengawas untuk membuka blokir, lalu tekan tombol di bawah ini.</p>
                <button onclick="location.reload()" class="btn btn-refresh text-white btn-lg">
                    <i class="bi bi-arrow-clockwise me-2"></i> Refresh Halaman
                </button>
            </div>
        `;
    }
    </script>
    <?php
    if (isset($jadwal)) {
        // Controller sends $bank and $jadwal containing flattened mapel info
        // Adjust for view usage
        $bank_info = $bank ?? null;
        $mapel_info = (object) [
            'nama_mapel' => $jadwal->nama_mapel ?? $bank->nama_mapel ?? '-',
            'kode' => $jadwal->kode_mapel ?? $bank->kode_mapel ?? '-'
        ];

        // Override references used below
        $bank = $bank_info;
        $mapel = $mapel_info;
    }
    ?>

    <div class="container-fluid py-4 container-p-x main-content-overlap">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-7">

                <!-- Section 1: Konfirmasi Data Peserta -->
                <?php if (isset($jadwal)): ?>
                    <div id="konfirm_data" style="display:block;" class="fade-in-up">
                        <div class="card bg-white border rounded-4 shadow-sm overflow-hidden hover-scale-sm transition-all">
                            <div class="card-header bg-light bg-opacity-75 border-bottom py-4 px-4 text-center">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                                    <i class="bi bi-person-vcard fs-3"></i>
                                </div>
                                <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Konfirmasi Data</h4>
                                <p class="text-muted mb-0 small text-uppercase letter-spacing-1 fw-bold">Pastikan data di bawah ini adalah data Anda.</p>
                            </div>
                            <div class="card-body p-4 p-md-5">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="bg-primary bg-opacity-10 p-4 rounded-4 border border-primary border-opacity-25 text-center shadow-sm">
                                            <div class="row g-4 d-flex align-items-center">
                                                <div class="col-md-6 border-end-md border-primary border-opacity-10">
                                                    <small class="text-uppercase text-primary fw-bold letter-spacing-1 opacity-75">Mata Pelajaran</small>
                                                    <h5 class="fw-bold text-dark mt-1 mb-0 fs-5 line-clamp-2">
                                                        <?= esc($mapel->nama_mapel ?? '-') ?>
                                                    </h5>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-uppercase text-primary fw-bold letter-spacing-1 opacity-75">Kelas</small>
                                                    <h5 class="fw-bold text-dark mt-1 mb-0 fs-5 pb-1">
                                                        <span class="badge bg-white text-primary border border-primary border-opacity-25 shadow-sm px-3 py-2 fs-6"><?= esc($siswa->kode_kelas ?? '-') ?></span>
                                                    </h5>
                                                </div>
                                                <div class="col-12">
                                                    <hr class="my-0 border-primary border-opacity-25 opacity-100 border-2 border-dashed">
                                                </div>
                                                <div class="col-md-6 border-end-md border-primary border-opacity-10">
                                                    <small class="text-uppercase text-primary fw-bold letter-spacing-1 opacity-75">Nama Peserta</small>
                                                    <h5 class="fw-bold text-dark mt-1 mb-0 fs-5 line-clamp-2">
                                                        <?= esc($siswa->nama ?? '-') ?>
                                                    </h5>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-uppercase text-primary fw-bold letter-spacing-1 opacity-75">NISN</small>
                                                    <h5 class="fw-bold text-dark mt-1 mb-0 fs-5 font-monospace">
                                                        <?= esc($siswa->nisn ?? '-') ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4 pt-2">
                                        <div class="d-flex gap-3">
                                            <a href="<?= base_url('cbt') ?>"
                                                class="btn btn-light btn-lg rounded-pill w-100 fw-bold border text-secondary hover-scale shadow-sm transition-all">
                                                <i class="bi bi-arrow-left me-2"></i> Batal
                                            </a>
                                            <button type="button"
                                                class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-lg hover-scale transition-all border-0"
                                                onclick="konfirmasiData()" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                                                Lanjutkan <i class="bi bi-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Section 2: Form Verifikasi -->
                <?php if (isset($jadwal)): ?>
                    <?php if (($support ?? false) && ($valid ?? false)): ?>
                        <div id="step_verifikasi" style="display:none;" class="fade-in-up">
                            <div class="card bg-white border rounded-4 shadow-sm overflow-hidden hover-scale-sm transition-all">
                                <div class="card-header bg-light bg-opacity-75 border-bottom py-3 px-4">
                                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="letter-spacing: -0.5px;">
                                        <div class="icon-box bg-primary bg-opacity-10 text-primary me-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                                            <i class="bi bi-shield-check fs-5"></i>
                                        </div>
                                        Verifikasi Diri
                                    </h5>
                                </div>
                                <div class="card-body p-4 p-md-5">
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Ketik Nama Lengkap</label>
                                        <div class="input-group input-group-lg shadow-sm rounded-pill border overflow-hidden">
                                            <span class="input-group-text bg-light border-0 ps-4 text-muted">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <input type="text" form="konfir" class="form-control border-0 bg-light fs-6" placeholder="Sesuai kartu peserta..." required name="nama_peserta" id="nama_peserta">
                                        </div>
                                        <div id="nama_peserta_error" class="text-danger small mt-2 fw-medium ms-3"></div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Jenis Kelamin</label>
                                        <div class="input-group input-group-lg shadow-sm rounded-pill border overflow-hidden">
                                            <span class="input-group-text bg-light border-0 ps-4 text-muted">
                                                <i class="bi bi-gender-ambiguous"></i>
                                            </span>
                                            <select id="jenis_kelamin" name="jenis_kelamin" form="konfir" class="form-select border-0 bg-light fs-6" required>
                                                <option value="">Pilih...</option>
                                                <option value="L">Laki-Laki</option>
                                                <option value="P">Perempuan</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase letter-spacing-1">Tanggal Lahir</label>
                                        <div class="d-flex gap-2">
                                            <select class="form-select form-select-lg rounded-pill shadow-sm border bg-light fs-6 text-center px-1 px-sm-3" name="tgl" form="konfir" required style="flex: 1;">
                                                <option value="">Tgl</option>
                                                <?php for ($i = 1; $i <= 31; $i++) printf('<option value="%02d">%02d</option>', $i, $i); ?>
                                            </select>
                                            <select class="form-select form-select-lg rounded-pill shadow-sm border bg-light fs-6 text-center px-1 px-sm-3" name="bulan" form="konfir" required style="flex: 1.5;">
                                                <option value="">Bln</option>
                                                <?php
                                                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                                foreach ($months as $k => $m) printf('<option value="%02d">%s</option>', $k + 1, $m);
                                                ?>
                                            </select>
                                            <select class="form-select form-select-lg rounded-pill shadow-sm border bg-light fs-6 text-center px-1 px-sm-3" name="tahun" form="konfir" required style="flex: 1.2;">
                                                <option value="">Thn</option>
                                                <?php for ($i = 1990; $i <= date('Y'); $i++) printf('<option value="%d">%d</option>', $i, $i); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <?php if (($jadwal->token ?? '0') == '1'): ?>
                                        <div class="mt-4 p-4 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25 text-center position-relative overflow-hidden" id="token-input-box">
                                            <div class="position-absolute opacity-10" style="right: -10px; bottom: -20px; transform: rotate(-15deg);">
                                                <i class="bi bi-shield-lock-fill text-primary" style="font-size: 8rem;"></i>
                                            </div>
                                            <label class="form-label small fw-bold text-primary text-uppercase mb-3 d-block letter-spacing-1 position-relative z-1"><i class="bi bi-key-fill me-2 fs-6"></i>Token Ujian</label>
                                            <div class="d-flex justify-content-center">
                                                <div class="input-group rounded-pill overflow-hidden" style="max-width:280px; border: 2px solid #0d6efd; background-color: #fff;">
                                                    <input type="text" form="konfir" id="tokenInput"
                                                        class="form-control form-control-lg text-center fw-bold text-uppercase"
                                                        placeholder="KETIK TOKEN" name="token"
                                                        maxlength="8" autocomplete="off" spellcheck="false"
                                                        style="font-size: 1.2rem; letter-spacing: 3px; border: none !important; box-shadow: none !important; color: #0d6efd; background-color: #fff; padding: 0.6rem 1rem;">
                                                    <button type="button" id="btnPasteToken" onclick="pasteToken()" class="btn d-none align-items-center justify-content-center px-3" style="border:none; background:#fff; color:#0d6efd;" title="Tempel token">
                                                        <i class="bi bi-clipboard-plus fs-5"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="token-feedback" class="mt-2 position-relative z-1" style="min-height: 24px;"></div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-5 d-flex gap-3">
                                        <button type="button" onclick="history.go(0)" class="btn btn-light btn-lg rounded-pill w-100 fw-bold border text-secondary hover-scale shadow-sm transition-all">
                                            <i class="bi bi-arrow-left me-2"></i> Kembali
                                        </button>
                                        <button type="button" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-lg hover-scale transition-all border-0"
                                            onclick="goToThirdStep()" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                                            Lanjutkan <i class="bi bi-check-circle ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Tanda Tangan -->
                        <div id="step_ttd" style="display:none;" class="fade-in-up">
                            <form id="konfir" class="row g-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?? '' ?>">
                                <input type="hidden" name="jadwal" value="<?= $jadwal->id_jadwal ?? '' ?>">
                                <input type="hidden" name="bank" value="<?= $jadwal->bank->id_bank ?? '' ?>">

                                <div class="col-12">
                                    <div class="card bg-white border rounded-4 shadow-sm overflow-hidden d-flex flex-column hover-scale-sm transition-all">
                                        <div class="card-header bg-light bg-opacity-75 border-bottom py-3 px-4">
                                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="letter-spacing: -0.5px;">
                                                <div class="icon-box bg-info bg-opacity-10 text-info me-3 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-pen fs-5"></i>
                                                </div>
                                                Tanda Tangan
                                            </h5>
                                        </div>
                                        <div class="card-body p-4 p-md-5 d-flex flex-column">
                                            <div class="alert bg-warning bg-opacity-10 border border-warning border-opacity-25 text-warning-emphasis small py-3 px-3 rounded-4 mb-4 fw-medium shadow-sm d-flex align-items-center" role="alert">
                                                <div class="icon-box bg-warning text-white rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-exclamation-lg fs-5"></i>
                                                </div>
                                                <div style="line-height: 1.4;">Saya mengerjakan dengan jujur dan bertanggung jawab.</div>
                                            </div>

                                            <div class="signature-pad-wrapper flex-grow-1 p-3 bg-light rounded-4 border border-info border-opacity-25 d-flex flex-column position-relative shadow-inner" id="signature-pad">
                                                <span class="d-block small fw-bold text-muted mb-3 text-uppercase letter-spacing-1 text-center opacity-75">Area Tanda Tangan</span>
                                                <canvas class="signature-pad w-100 rounded-4 bg-white shadow-sm border border-secondary border-opacity-10 mx-auto cursor-crosshair" style="height: 200px; touch-action: none;"></canvas>
                                                <textarea style="display: none;" id="ttdku" name="ttdku"></textarea>

                                                <div class="d-flex justify-content-between mt-4">
                                                    <button type="button" data-action="clear" class="btn btn-light rounded-pill px-4 text-secondary fw-bold border shadow-sm hover-scale transition-all">
                                                        <i class="bi bi-eraser-fill me-1"></i> Hapus
                                                    </button>
                                                    <button type="button" data-action="simpan" class="btn btn-info rounded-pill px-4 fw-bold text-white shadow-sm hover-scale transition-all" style="background: linear-gradient(135deg, #0dcaf0 0%, #087990 100%); border: none;">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Simpan
                                                    </button>
                                                </div>
                                            </div>

                                            <hr class="my-5 border-light border-2 opacity-75">

                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <button type="button" onclick="goToSecondStep()" class="btn btn-light btn-lg w-100 rounded-pill fw-bold border text-secondary hover-scale transition-all shadow-sm">
                                                        <i class="bi bi-arrow-left me-1"></i> Kembali
                                                    </button>
                                                </div>
                                                <div class="col-md-8">
                                                    <button type="submit" id="btnMulai" class="btn btn-lg w-100 rounded-pill fw-bold text-white shadow-lg hover-scale transition-all position-relative overflow-hidden" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); letter-spacing: 1px; border: none; z-index: 10;" disabled>
                                                        <div class="d-flex align-items-center justify-content-center w-100 position-relative z-1">
                                                            <i class="bi bi-play-circle-fill me-2 fs-4"></i> <span class="fs-5">MULAI UJIAN</span>
                                                        </div>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Error State Inner -->
                        <div class="text-center py-5 fade-in-up">
                            <div class="d-inline-flex p-4 rounded-circle bg-danger bg-opacity-10 text-danger shadow-sm mb-4 border border-danger border-opacity-25">
                                <i class="bi bi-exclamation-triangle-fill display-5"></i>
                            </div>
                            <h4 class="fw-bold text-dark">Data Tidak Valid</h4>
                            <p class="text-muted mb-4">Pastikan Anda sudah login dengan benar.</p>
                            <a href="<?= base_url('cbt') ?>" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                Kembali ke Dashboard
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Error State External -->
                    <div class="text-center py-5 fade-in-up">
                        <div class="d-inline-flex p-4 rounded-circle bg-danger bg-opacity-10 text-danger shadow-sm mb-4 border border-danger border-opacity-25">
                            <i class="bi bi-slash-circle-fill display-5"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Akses Ditolak</h3>
                        <p class="text-muted mb-4"><?= esc($error ?? 'Ujian tidak dapat diakses saat ini.') ?></p>
                        <a href="<?= base_url('cbt') ?>" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            Kembali ke Dashboard
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        <div id="liveToast" class="toast align-items-center border-0" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold" id="toast-body">
                    <!-- Message here -->
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark">Data Sudah Benar?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-2 pb-4 text-muted">
                    Pastikan data diri Anda sudah sesuai.
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border flex-grow-1" data-bs-dismiss="modal">Cek Lagi</button>
                        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm flex-grow-1" id="confirmBtnModal" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">Ya, Lanjutkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Exam Warning Modal -->
    <div class="modal fade" id="activeExamModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                        </div>
                        <h5 class="modal-title fw-bold text-dark">Ujian Lain Aktif!</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-4">
                    <p class="text-muted fs-6 mb-0" id="activeExamMessage">
                        <!-- Message populated via JS -->
                    </p>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" onclick="location.href='<?= base_url('cbt') ?>'" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-sm py-2" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        Cek Daftar Ujian
                    </button>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .hover-scale { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .hover-scale:hover { transform: translateY(-2px); }
    .hover-scale-sm { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease; }
    .hover-scale-sm:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    @keyframes fadeInOut {
        0% { opacity:0; transform:translateX(-50%) translateY(-10px); }
        15% { opacity:1; transform:translateX(-50%) translateY(0); }
        85% { opacity:1; transform:translateX(-50%) translateY(0); }
        100% { opacity:0; transform:translateX(-50%) translateY(-10px); }
    }
    .letter-spacing-1 { letter-spacing: 1px; }
    .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05); }
    .cursor-pointer { cursor: pointer; }
    .cursor-crosshair { cursor: crosshair; }
    .focus-ring-primary:focus { outline: none; box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25) !important; border-color: #86b7fe; }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%       { transform: translateX(-8px); }
        40%       { transform: translateX(8px); }
        60%       { transform: translateX(-6px); }
        80%       { transform: translateX(6px); }
    }
    .shake-anim { animation: shake 0.5s ease-in-out; }
    .swal-no-round-icon .swal2-icon { border: none !important; background: transparent !important; box-shadow: none !important; width: auto !important; height: auto !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/app/js/redirect.js') ?>"></script>
<script>
    // Form Submission Logic
    // Toast Helper
    function showToast(message, type = 'info') {
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

        toastBody.textContent = message;
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    // Form Submission Logic
    $('#konfir').submit(function (e) {
        e.preventDefault();
        e.stopPropagation();
        ajaxcsrf();

        var namaPesertaInput = $('#nama_peserta').val().trim();
        var namaSiswa = "<?= addslashes($siswa->nama ?? '') ?>".trim();
        var btnMulai = $('#btnMulai');

        if (namaPesertaInput !== namaSiswa) {
            showToast("Nama tidak sesuai. Mohon ketik nama Anda: " + namaSiswa, 'warning');
            return;
        }

        if (!window.isSignatureSaved) {
            showToast("Silakan tanda tangan dan simpan terlebih dahulu.", 'warning');
            return;
        }

        // Show loading state
        var originalBtnHtml = btnMulai.html();
        btnMulai.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan Ujian...');

        $.ajax({
            type: 'POST',
            url: base_url + 'cbtsiswa/validasi_siswa',
            data: $(this).serialize(),
            success: function (data) {
                console.log('validasi_siswa response:', data);

                if (data.token === true) {
                    if (signaturePad.isEmpty()) {
                        showToast("Tanda tangan kosong.", 'error');
                        btnMulai.prop('disabled', false).html(originalBtnHtml);
                    } else {
                        if (data.izinkan === true) {
                            if (data.ada_waktu === true) {
                                var jmlSoal = 0;
                                if (typeof data.jml_soal === 'number') {
                                    jmlSoal = data.jml_soal;
                                } else if (Array.isArray(data.jml_soal)) {
                                    jmlSoal = data.jml_soal.length;
                                } else if (data.jml_soal) {
                                    jmlSoal = Object.keys(data.jml_soal).length;
                                }
                                console.log('jmlSoal:', jmlSoal, 'token_url:', data.token_url);

                                if (jmlSoal > 0) {
                                    var sigData = signaturePad.toDataURL('image/png');
                                    $('#ttdku').html(sigData);
                                    if (data.token_url) {
                                        window.location.href = base_url + 'cbtsiswa/penilaian/' + data.token_url;
                                    } else {
                                        showToast("Gagal mendapatkan token ujian. Silakan coba lagi.", 'error');
                                        btnMulai.prop('disabled', false).html(originalBtnHtml);
                                    }
                                } else {
                                    showToast("Soal belum tersedia. Hubungi proktor. (Error 004)", 'error');
                                    btnMulai.prop('disabled', false).html(originalBtnHtml);
                                }
                            } else {
                                const warnMsg = (data.warn && data.warn.msg) || "Waktu ujian telah berakhir.";
                                const warnSisa = data.warn && data.warn.sisa ? data.warn.sisa : '';
                                Swal.fire({
                                    title: '<span class="fw-bold text-dark">⏳ Waktu Ujian Telah Berakhir</span>',
                                    html: `<div style="font-size:1.05rem;color:#dc3545;" class="fw-semibold mb-1">${warnMsg}</div>
                                           ${warnSisa ? `<div class="small text-danger fw-semibold">${warnSisa}</div>` : ''}
                                           <hr class="my-3" style="border-color:#fecaca;">
                                           <div class="text-muted" style="font-size:0.9rem;">Silakan hubungi pengawas jika Anda memerlukan bantuan.</div>`,
                                    iconHtml: '<div style="width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#f97316,#dc2626);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(220,38,38,0.3);"><i class="bi bi-stopwatch-fill text-white" style="font-size:2rem;"></i></div>',
                                    showConfirmButton: true,
                                    confirmButtonText: '<i class="bi bi-arrow-left me-2"></i> Kembali ke Daftar Ujian',
                                    confirmButtonColor: '#dc2626',
                                    allowOutsideClick: false,
                                    customClass: {
                                        popup: 'rounded-4 shadow-lg border-0 swal-no-round-icon',
                                        confirmButton: 'rounded-pill px-5 fw-bold shadow-sm border-0'
                                    },
                                    buttonsStyling: true
                                }).then(function() {
                                    window.location.href = base_url + 'cbt';
                                });
                                btnMulai.prop('disabled', false).html(originalBtnHtml);
                            }
                        } else {
                            showToast("Akses ditolak. Anda terdeteksi login di perangkat lain.", 'error');
                            btnMulai.prop('disabled', false).html(originalBtnHtml);
                        }
                    }
                } else {
                    if (data.message && data.message.includes("diblokir")) {
                        showBlockedOverlay(data.message);
                    } else if (data.message && data.message.includes("memiliki sesi aktif")) {
                        $('#activeExamMessage').html(data.message);
                        var activeModal = new bootstrap.Modal(document.getElementById('activeExamModal'));
                        activeModal.show();
                    } else {
                        showToast(data.message || "Token salah. Silakan periksa kembali token ujian.", 'error');
                    }
                    btnMulai.prop('disabled', false).html(originalBtnHtml);
                }
            },
            error: function (xhr, status, error) {
                console.error('validasi_siswa error:', status, error, xhr.responseText);
                showToast("Gagal menghubungkan ke server.", 'error');
                btnMulai.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    // Signature Pad Init
    var wrapper = document.getElementById("signature-pad");
    var signaturePad;
    if (wrapper) {
        var canvas = wrapper.querySelector("canvas");
        var saveButton = wrapper.querySelector("[data-action=simpan]");
        var clearButton = wrapper.querySelector("[data-action=clear]");

        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            if (signaturePad) signaturePad.clear(); // Simply clear on resize for simplicity in this context or restore data if needed
        }
        window.addEventListener("resize", resizeCanvas);

        signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
        resizeCanvas();

        clearButton.addEventListener("click", function () {
            signaturePad.clear();
            window.isSignatureSaved = false;
            $('#btnMulai').prop('disabled', true);
            saveButton.innerHTML = '<i class="bi bi-check2 me-1"></i> Simpan';
            saveButton.disabled = false;
        });

        saveButton.addEventListener("click", function () {
            if (signaturePad.isEmpty()) {
                showToast("Silakan tanda tangan terlebih dahulu.", 'warning');
            } else {
                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: base_url + "cbtsiswa/save_signature",
                    data: {
                        signature_data: signaturePad.toDataURL('image/png'),
                        siswa: $('input[name="siswa"]').val(),
                        jadwal: $('input[name="jadwal"]').val(),
                        bank: $('input[name="bank"]').val()
                    },
                    type: "post",
                    dataType: "json",
                    success: function (response) {
                        if (response.status) {
                            window.isSignatureSaved = true;
                            $('#btnMulai').prop('disabled', false);
                            btn.html('<i class="bi bi-check-lg"></i> Tersimpan');
                            showToast("Tanda tangan tersimpan.", 'success');
                        } else {
                            btn.prop('disabled', false).html('<i class="bi bi-check2"></i> Simpan');
                            showToast(response.message || "Gagal menyimpan tanda tangan.", 'error');
                        }
                    },
                    error: function () {
                        btn.prop('disabled', false).html('<i class="bi bi-check2"></i> Simpan');
                        showToast("Gagal menyimpan tanda tangan.", 'error');
                    }
                });
            }
        });
    }

    // UI Helpers
    function konfirmasiData() {
        var myModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        myModal.show();

        // Handle Button Click
        $('#confirmBtnModal').off('click').on('click', function () {
            myModal.hide();
            $('#konfirm_data').slideUp(300);
            $('#step_verifikasi').delay(300).slideDown(300);
        });
    }

    // Constants from PHP
    const hasToken     = <?= (($jadwal->token ?? '0') == '1') ? 'true' : 'false' ?>;
    const jadwalId     = <?= (int) ($jadwal->id_jadwal ?? 0) ?>;
    const namaSiswaStr = "<?= addslashes($siswa->nama ?? '') ?>".trim();

    $(document).ready(function () {
        var storedToken = sessionStorage.getItem('lastCopiedToken');
        var storedTime = sessionStorage.getItem('lastCopiedTokenTime');
        var now = Date.now();
        var expiry = 60 * 1000;

        if (storedToken && storedTime && (now - parseInt(storedTime)) <= expiry) {
            $('#btnPasteToken').removeClass('d-none').addClass('d-flex');
        } else {
            sessionStorage.removeItem('lastCopiedToken');
            sessionStorage.removeItem('lastCopiedTokenTime');
        }
    });

    // Auto-uppercase & strip non-alphanumeric from token input
    $(document).on('input', '#tokenInput', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        setTokenFeedback('', '');
    });

    function pasteToken() {
        var storedToken = sessionStorage.getItem('lastCopiedToken');
        var storedTime = sessionStorage.getItem('lastCopiedTokenTime');
        var now = Date.now();
        var expiry = 60 * 1000;

        if (!storedToken || !storedTime || (now - parseInt(storedTime)) > expiry) {
            sessionStorage.removeItem('lastCopiedToken');
            sessionStorage.removeItem('lastCopiedTokenTime');
            var toast = document.createElement('div');
            toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill shadow-sm text-white small fw-semibold';
            toast.style.cssText = 'z-index:99999;background:#dc3545;font-size:0.8rem;animation:fadeInOut 1.5s ease forwards;';
            toast.textContent = 'Token tidak valid atau sudah kedaluwarsa. Salin ulang dari halaman jadwal.';
            document.body.appendChild(toast);
            setTimeout(function () { toast.remove(); }, 3000);
            return;
        }

        var input = document.getElementById('tokenInput');
        if (input) {
            input.value = storedToken;
            input.focus();
            input.setSelectionRange(storedToken.length, storedToken.length);
            setTokenFeedback('', '');
            var toast = document.createElement('div');
            toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill shadow-sm text-white small fw-semibold';
            toast.style.cssText = 'z-index:99999;background:#1877F2;font-size:0.8rem;animation:fadeInOut 1.5s ease forwards;';
            toast.textContent = 'Token ditempel!';
            document.body.appendChild(toast);
            setTimeout(function () { toast.remove(); }, 1500);
        }
    }

    function setTokenFeedback(msg, type) {
        const el = document.getElementById('token-feedback');
        if (!el) return;
        if (!msg) { el.innerHTML = ''; return; }
        const cls  = type === 'ok' ? 'text-success fw-semibold small' : 'text-danger fw-semibold small';
        const icon = type === 'ok' ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
        el.innerHTML = `<span class="${cls}"><i class="bi ${icon} me-1"></i>${msg}</span>`;
    }

    function goToSecondStep() {
        $('#step_ttd').slideUp(300);
        $('#step_verifikasi').delay(300).slideDown(300);
    }

    async function goToThirdStep() {
        // 1. Basic required fields — per-field check for informative feedback
        const emptyFields = [];

        const namaVal = $('#nama_peserta').val().trim();
        if (!namaVal) emptyFields.push({ label: 'Nama Lengkap', el: $('#nama_peserta') });

        const kelaminVal = $('#jenis_kelamin').val();
        if (!kelaminVal) emptyFields.push({ label: 'Jenis Kelamin', el: $('#jenis_kelamin') });

        const tglVal   = $('select[name="tgl"]').val();
        const bulanVal = $('select[name="bulan"]').val();
        const tahunVal = $('select[name="tahun"]').val();
        if (!tglVal || !bulanVal || !tahunVal) {
            const firstDate = !tglVal ? $('select[name="tgl"]') : !bulanVal ? $('select[name="bulan"]') : $('select[name="tahun"]');
            emptyFields.push({ label: 'Tanggal Lahir', el: firstDate });
        }

        if (emptyFields.length > 0) {
            const labels = emptyFields.map(f => f.label);
            let msg;
            if (labels.length === 1) {
                msg = `Kolom ${labels[0]} masih kosong.`;
            } else {
                const last = labels.pop();
                msg = `Kolom ${labels.join(', ')} dan ${last} masih kosong.`;
            }
            showToast(msg, "warning");
            emptyFields[0].el.focus();
            return;
        }

        // 2. Name match
        if (namaVal.toLowerCase() !== namaSiswaStr.toLowerCase()) {
            showToast("Nama tidak sesuai. Mohon ketik nama Anda: " + namaSiswaStr, 'warning');
            return;
        }

        // 3. Token validation via AJAX (if this jadwal requires a token)
        if (hasToken) {
            const tokenInput = $('#tokenInput');
            const tokenVal   = tokenInput.val().trim();

            if (!tokenVal) {
                showToast("Mohon ketik Token Ujian yang diberikan pengawas.", "warning");
                tokenInput.focus();
                return;
            }

            const btn         = $('button[onclick="goToThirdStep()"]');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memverifikasi token...');

            try {
                const response = await $.ajax({
                    url:      base_url + 'cbtsiswa/validasi_token',
                    type:     'POST',
                    dataType: 'json',
                    data: {
                        jadwal: jadwalId,
                        token:  tokenVal,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    }
                });

                btn.prop('disabled', false).html(originalHtml);

                if (response.valid) {
                    setTokenFeedback('Token benar!', 'ok');
                    setTimeout(function () {
                        $('#step_verifikasi').slideUp(300);
                        $('#step_ttd').delay(300).slideDown(300, function () { resizeCanvas(); });
                    }, 600);
                } else {
                    setTokenFeedback(response.message || 'Token salah. Periksa kembali.', 'err');
                    tokenInput.select().focus();
                    $('#token-input-box').addClass('shake-anim');
                    setTimeout(function () { $('#token-input-box').removeClass('shake-anim'); }, 600);
                }
            } catch (err) {
                btn.prop('disabled', false).html(originalHtml);
                showToast("Gagal menghubungkan ke server. Coba lagi.", 'error');
            }
            return;
        }

        // No token required — proceed directly
        $('#step_verifikasi').slideUp(300);
        $('#step_ttd').delay(300).slideDown(300, function () { resizeCanvas(); });
    }
</script>
<?= $this->endSection() ?>