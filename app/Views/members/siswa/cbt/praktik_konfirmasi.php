<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 container-p-x main-content-overlap fade-in-up pb-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 mt-4">
            
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-4">
                    <div class="text-center">
                        <i class="bi bi-clipboard-check fs-1 mb-2 d-block"></i>
                        <h3 class="fw-bold mb-0">Konfirmasi Ujian</h3>
                        <p class="mb-0 opacity-75"><?= esc($praktik->judul) ?></p>
                    </div>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <!-- Exam Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Mata Pelajaran</label>
                                <p class="fw-bold mb-0"><?= esc($praktik->nama_mapel) ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Jenis Ujian</label>
                                <p class="fw-bold mb-0">
                                    <span class="badge bg-info"><?= ucwords(str_replace('_', ' ', $praktik->jenis_praktik)) ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Waktu Mulai</label>
                                <p class="fw-bold mb-0"><?= date('d M Y H:i', strtotime($praktik->tanggal_mulai)) ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Waktu Selesai</label>
                                <p class="fw-bold mb-0"><?= date('d M Y H:i', strtotime($praktik->tanggal_selesai)) ?></p>
                            </div>
                            <?php if ($praktik->durasi_menit > 0): ?>
                            <div class="mb-3">
                                <label class="text-muted small mb-1">Durasi</label>
                                <p class="fw-bold mb-0"><?= $praktik->durasi_menit ?> menit</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <?php if ($praktik->instruksi): ?>
                    <div class="alert alert-info mb-4">
                        <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Instruksi Ujian</h6>
                        <div class="small"><?= $praktik->instruksi ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Security Notice -->
                    <div class="alert alert-warning mb-4">
                        <h6 class="fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Peraturan & Keamanan</h6>
                        <ul class="small mb-0">
                            <?php if ($praktik->wajib_fullscreen): ?>
                                <li>Ujian wajib dalam mode fullscreen. Jika keluar fullscreen, akan terdeteksi.</li>
                            <?php endif; ?>
                            <?php if ($praktik->batas_tab_browser): ?>
                                <li>Dilarang membuka tab atau aplikasi lain selama ujian.</li>
                            <?php endif; ?>
                            <?php if ($praktik->batas_copy_paste): ?>
                                <li>Copy-paste dinonaktifkan selama ujian.</li>
                            <?php endif; ?>
                            <?php if ($praktik->aktifkan_anti_cheat): ?>
                                <li>Sistem anti-cheat aktif. Aktivitas mencurigakan akan dicatat.</li>
                            <?php endif; ?>
                            <li>Jawaban tersimpan otomatis. Pastikan koneksi internet stabil.</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center">
                        <?php if ($can_start): ?>
                            <button type="button" class="btn btn-success btn-lg px-5 rounded-pill fw-bold shadow-sm" onclick="startExam()">
                                <i class="bi bi-play-circle me-2"></i>Mulai Ujian
                            </button>
                            <p class="text-muted small mt-3">Pastikan Anda sudah siap sebelum memulai.</p>
                        <?php elseif ($can_continue): ?>
                            <button type="button" class="btn btn-warning btn-lg px-5 rounded-pill fw-bold shadow-sm" onclick="startExam()">
                                <i class="bi bi-arrow-right-circle me-2"></i>Lanjutkan Ujian
                            </button>
                            <p class="text-muted small mt-3">Anda sedang mengerjakan ujian ini. Klik untuk melanjutkan.</p>
                        <?php else: ?>
                            <button type="button" class="btn btn-secondary btn-lg px-5 rounded-pill fw-bold" disabled>
                                <i class="bi bi-lock me-2"></i>Ujian Tidak Tersedia
                            </button>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?= base_url('cbtsiswa/praktik') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<form id="formMulai" method="post" action="<?= base_url('cbtsiswa/praktik/mulai') ?>">
    <input type="hidden" name="praktik_id" value="<?= $praktik->id ?>">
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function startExam() {
    Swal.fire({
        title: 'Konfirmasi Mulai',
        text: '<?= $can_continue ? "Lanjutkan ujian yang sedang berlangsung?" : "Anda akan memulai ujian. Pastikan koneksi internet stabil. Lanjutkan?" ?>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Mulai',
        cancelButtonText: 'Batal',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formMulai');
            const formData = new FormData(form);
            
            // Show loading
            Swal.fire({
                title: 'Memulai Ujian...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // AJAX submit
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    window.location.href = data.redirect;
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }
    });
}
</script>
<?= $this->endSection() ?>
