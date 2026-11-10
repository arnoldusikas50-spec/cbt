<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<!-- Anti-cheat notice -->
<div id="antiCheatNotice" class="alert alert-warning alert-dismissible fade show fixed-top m-3 shadow-lg" style="z-index: 9999; display: none;">
    <i class="bi bi-shield-exclamation me-2"></i>
    <strong>Perhatian!</strong> Sistem anti-cheat aktif. Aktivitas mencurigakan akan tercatat.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Time warning -->
<div id="timeWarning" class="alert alert-danger alert-dismissible fade show fixed-top m-3 shadow-lg" style="z-index: 9999; display: none;">
    <i class="bi bi-clock-history me-2"></i>
    <strong>Peringatan!</strong> Waktu ujian tersisa kurang dari 5 menit.
</div>

<div class="container-fluid py-4 main-content-overlap fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Header Card with Progress -->
            <div class="card border-0 shadow-lg rounded-4 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h4 class="fw-bold mb-0"><?= esc($judul) ?></h4>
                            <small class="opacity-75"><?= esc($praktik->nama_mapel) ?></small>
                            <?php if ($hasil->mode_pengumpulan === 'portofolio'): ?>
                                <span class="badge bg-warning ms-2">Mode Portofolio</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <!-- Progress Indicator -->
                            <div class="bg-white bg-opacity-10 rounded-pill px-3 py-1">
                                <small class="text-white-75">Progress:</small>
                                <span class="fw-bold" id="progressText">Menunggu Penilaian</span>
                            </div>
                            <?php if ($sisa_waktu > 0): ?>
                            <div class="text-center bg-white text-dark rounded-pill px-4 py-2">
                                <div class="small text-muted">Sisa Waktu</div>
                                <div class="fw-bold fs-5" id="timer">--:--:--</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-body p-4 p-md-5">
                            
                            <?= form_open('cbtsiswa/praktik/submit', ['id' => 'formUjian', 'class' => 'needs-validation']) ?>
                            <input type="hidden" name="praktik_id" value="<?= $praktik->id ?>">
                            <input type="hidden" name="hasil_id" value="<?= $hasil->id ?>">
                            
                            <!-- Flexible Instructions -->
                            <?php if ($praktik->instruksi): ?>
                            <div class="alert alert-info mb-4 border-0 rounded-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="icon-box bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="bi bi-lightbulb text-info fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-2">Instruksi Ujian</h6>
                                        <div class="small"><?= $praktik->instruksi ?></div>
                                        <?php if ($praktik->tipe_instruksi === 'terbuka'): ?>
                                            <div class="mt-2 p-2 bg-white bg-opacity-50 rounded-3">
                                                <small class="text-muted">
                                                    <i class="bi bi-stars me-1"></i>
                                                    <strong>Kreativitas diperbolehkan!</strong> Anda bebas mengeksplorasi solusi sesuai pemahaman Anda.
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($praktik->panduan_kreativitas): ?>
                            <div class="alert alert-warning border-0 rounded-4 mb-4">
                                <h6 class="fw-bold"><i class="bi bi-palette me-2"></i>Panduan Kreativitas</h6>
                                <div class="small"><?= $praktik->panduan_kreativitas ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Answer Input Based on Type -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-pencil-square me-2"></i>Jawaban Anda:
                                </h6>
                                
                                <?php if ($hasil->mode_pengumpulan === 'portofolio'): ?>
                                    <!-- Portfolio Mode -->
                                    <div class="alert alert-primary border-0 rounded-4 mb-4">
                                        <h6 class="fw-bold"><i class="bi bi-collection me-2"></i>Mode Portofolio</h6>
                                        <p class="small mb-2">Tambahkan berbagai item untuk mendukung jawaban Anda:</p>
                                        <ul class="small mb-0">
                                            <li>File dokumentasi</li>
                                            <li>Link referensi</li>
                                            <li>Gambar/screenshot</li>
                                            <li>Catatan proses kerja</li>
                                        </ul>
                                    </div>
                                    <div id="portfolioItems" class="mb-3">
                                        <!-- Portfolio items populated by JS -->
                                    </div>
                                    <button type="button" class="btn btn-outline-primary rounded-pill" onclick="addPortfolioItem()">
                                        <i class="bi bi-plus-circle me-1"></i>Tambah Item Portofolio
                                    </button>
                                    
                                <?php elseif ($praktik->jenis_praktik === 'coding'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Bahasa Pemrograman</label>
                                        <select name="bahasa_pemrograman" class="form-select w-auto">
                                            <option value="python">Python</option>
                                            <option value="php">PHP</option>
                                            <option value="javascript">JavaScript</option>
                                            <option value="java">Java</option>
                                            <option value="cpp">C++</option>
                                        </select>
                                    </div>
                                    <textarea name="kode_jawaban" id="codeEditor" class="form-control font-monospace" rows="15" 
                                              placeholder="// Tulis kode Anda di sini..."><?= esc($hasil->kode_jawaban ?? '') ?></textarea>
                                    <small class="text-muted">Kode akan dievaluasi otomatis berdasarkan kriteria yang ditentukan.</small>
                                    
                                <?php elseif ($praktik->jenis_praktik === 'simulasi'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">URL Hasil Simulasi</label>
                                        <input type="url" name="url_simulasi" class="form-control" 
                                               placeholder="https://..." value="<?= esc($hasil->url_simulasi ?? '') ?>">
                                        <small class="text-muted">Masukkan URL hasil simulasi Anda</small>
                                    </div>
                                    
                                <?php elseif ($praktik->jenis_praktik === 'upload_file'): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Upload File</label>
                                        <input type="file" name="file_praktik" id="filePraktik" class="form-control">
                                        <small class="text-muted">Format yang diizinkan: PDF, DOC, DOCX, ZIP (Max 10MB)</small>
                                        <div id="fileList" class="mt-2"></div>
                                    </div>
                                    
                                <?php else: ?>
                                    <textarea name="jawaban_teks" id="jawabanEditor" class="form-control" rows="10"
                                              placeholder="Tulis jawaban Anda di sini..."><?= esc($hasil->jawaban_teks ?? '') ?></textarea>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Additional Notes -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-chat-left-text me-1"></i>Catatan Proses Kerja (Opsional)
                                </label>
                                <textarea name="catatan_siswa" class="form-control" rows="3" 
                                          placeholder="Ceritakan proses kerja Anda, kesulitan yang dihadapi, atau insight yang didapat..."><?= esc($hasil->catatan_siswa ?? '') ?></textarea>
                                <small class="text-muted">Catatan ini membantu guru memahami proses berpikir Anda.</small>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-outline-secondary rounded-pill me-2" onclick="saveDraft()">
                                        <i class="bi bi-save me-1"></i>Simpan Draft
                                    </button>
                                    <?php if ($hasil->sisa_kesempatan_revisi > 0): ?>
                                        <span class="badge bg-info">
                                            <i class="bi bi-arrow-repeat me-1"></i><?= $hasil->sisa_kesempatan_revisi ?> kesempatan revisi tersisa
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-danger rounded-pill" onclick="confirmSubmit()">
                                        <i class="bi bi-check-circle me-1"></i>Selesai & Submit
                                    </button>
                                </div>
                            </div>
                            
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Feedback & Assessment -->
                <div class="col-lg-4">
                    <!-- Real-time Feedback Panel -->
                    <div class="card border-0 shadow-lg rounded-4 mb-4">
                        <div class="card-header bg-success text-white py-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots me-2"></i>Umpan Balik Guru</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="feedbackPanel" class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                <div class="list-group-item text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                                    <small>Belum ada umpan balik.<br>Feedback akan muncul saat guru memberi penilaian.</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <button type="button" class="btn btn-outline-success btn-sm w-100 rounded-pill" onclick="refreshFeedback()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Perbarui Feedback
                            </button>
                        </div>
                    </div>

                    <!-- Assessment Dimensions -->
                    <?php if (!empty($rubriks)): ?>
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-header bg-info text-white py-3">
                            <h6 class="fw-bold mb-0"><i class="bi bi-check2-all me-2"></i>Dimensi Penilaian</h6>
                        </div>
                        <div class="card-body">
                            <div class="small">
                                <?php foreach ($rubriks as $r): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded-3">
                                        <div>
                                            <div class="fw-semibold"><?= esc($r->nama_kriteria) ?></div>
                                            <small class="text-muted">
                                                <?php if (!empty($r->dimensi_penilaian)): ?>
                                                    <span class="badge bg-white text-info border">
                                                        <?= ucwords(str_replace('_', ' ', $r->dimensi_penilaian)) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-info"><?= $r->bobot_persen ?>%</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const praktikId = <?= $praktik->id ?>;
const sisaWaktu = <?= $sisa_waktu ?>;
let countdownTimer;
let antiCheatInterval;

// Timer countdown
function startTimer() {
    if (sisaWaktu <= 0) return;
    
    let remaining = sisaWaktu;
    
    countdownTimer = setInterval(() => {
        remaining--;
        
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;
        
        document.getElementById('timer').textContent = 
            `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        // Warning when 5 minutes left
        if (remaining === 300) {
            document.getElementById('timeWarning').style.display = 'block';
            Swal.fire({
                title: 'Peringatan Waktu!',
                text: 'Sisa waktu ujian kurang dari 5 menit. Segera selesaikan!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
        
        // Auto submit when time's up
        if (remaining <= 0) {
            clearInterval(countdownTimer);
            Swal.fire({
                title: 'Waktu Habis!',
                text: 'Waktu ujian telah berakhir. Jawaban akan otomatis disubmit.',
                icon: 'warning',
                allowOutsideClick: false
            }).then(() => {
                document.getElementById('formUjian').submit();
            });
        }
    }, 1000);
}

// Anti-cheat monitoring
function initAntiCheat() {
    // Show notice
    document.getElementById('antiCheatNotice').style.display = 'block';
    setTimeout(() => {
        document.getElementById('antiCheatNotice').style.display = 'none';
    }, 5000);
    
    <?php if ($praktik->wajib_fullscreen): ?>
    const isEb = !!(
        window.isExamBrowser ||
        window.TKA_EXAM_BROWSER ||
        window.EXAM_BROWSER ||
        window.exambrowser ||
        window.Exambro ||
        window.Android ||
        (typeof navigator !== 'undefined' && navigator.userAgent && (
            navigator.userAgent.indexOf('TKAExamBrowser') !== -1 ||
            navigator.userAgent.indexOf('TKA-Exam') !== -1 ||
            navigator.userAgent.indexOf('ExamBrowser') !== -1 ||
            navigator.userAgent.indexOf('Exambro') !== -1 ||
            navigator.userAgent.indexOf('CandyExam') !== -1 ||
            navigator.userAgent.indexOf('Electron') !== -1
        ))
    );
    if (!isEb) {
        // Request fullscreen
        document.documentElement.requestFullscreen?.() || 
        document.documentElement.webkitRequestFullscreen?.() ||
        document.documentElement.mozRequestFullScreen?.();
        
        // Detect fullscreen exit
        document.addEventListener('fullscreenchange', () => {
            if (!document.fullscreenElement) {
                logActivity('keluar_fullscreen', { timestamp: new Date().toISOString() });
                Swal.fire({
                    title: 'Peringatan!',
                    text: 'Anda keluar dari mode fullscreen. Aktivitas ini akan dicatat.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        });
    }
    <?php endif; ?>
    
    <?php if ($praktik->batas_copy_paste): ?>
    // Disable copy-paste
    document.addEventListener('copy', (e) => {
        e.preventDefault();
        logActivity('copy_paste', { action: 'copy' });
        Swal.fire('Dilarang!', 'Copy dinonaktifkan selama ujian.', 'warning');
    });
    
    document.addEventListener('paste', (e) => {
        e.preventDefault();
        logActivity('copy_paste', { action: 'paste' });
        Swal.fire('Dilarang!', 'Paste dinonaktifkan selama ujian.', 'warning');
    });
    
    document.addEventListener('cut', (e) => {
        e.preventDefault();
        logActivity('copy_paste', { action: 'cut' });
    });
    <?php endif; ?>
    
    // Detect tab switching
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            logActivity('buka_tab_baru', { timestamp: new Date().toISOString() });
        }
    });
    
    // Periodic server sync
    antiCheatInterval = setInterval(() => {
        checkServerTime();
    }, 30000); // Every 30 seconds
}

// Log activity to server
function logActivity(tipe, detail = null) {
    $.post('<?= base_url('cbtsiswa/praktik/logActivity') ?>', {
        praktik_id: praktikId,
        tipe_aktivitas: tipe,
        detail: detail ? JSON.stringify(detail) : null
    });
}

// Check server time
function checkServerTime() {
    $.get('<?= base_url('cbtsiswa/praktik/checkTime') ?>?praktik_id=' + praktikId, (res) => {
        if (res.ended) {
            clearInterval(countdownTimer);
            Swal.fire({
                title: 'Ujian Berakhir!',
                text: res.message,
                icon: 'warning',
                allowOutsideClick: false
            }).then(() => {
                window.location.href = '<?= base_url('cbtsiswa/praktik') ?>';
            });
        } else if (res.warning) {
            document.getElementById('timeWarning').style.display = 'block';
        }
    }, 'json');
}

// Save draft
function saveDraft() {
    const formData = new FormData(document.getElementById('formUjian'));
    
    $.ajax({
        url: '<?= base_url('cbtsiswa/praktik/saveDraft') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: (res) => {
            if (res.status) {
                Swal.fire({
                    title: 'Draft Disimpan',
                    text: 'Jawaban tersimpan sebagai draft.',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }
    });
}

// Confirm submit
function confirmSubmit() {
    Swal.fire({
        title: 'Submit Ujian?',
        text: 'Pastikan semua jawaban sudah lengkap. Setelah submit, Anda tidak bisa mengubah jawaban.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Submit',
        cancelButtonText: 'Periksa Lagi',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            submitExam();
        }
    });
}

// Submit exam
function submitExam() {
    const formData = new FormData(document.getElementById('formUjian'));
    
    $.ajax({
        url: '<?= base_url('cbtsiswa/praktik/submit') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: (res) => {
            if (res.status) {
                Swal.fire({
                    title: 'Ujian Selesai!',
                    text: 'Jawaban berhasil disubmit.',
                    icon: 'success',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = res.redirect || '<?= base_url('cbtsiswa/praktik') ?>';
                });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: () => {
            Swal.fire('Error', 'Gagal mengirim jawaban. Coba lagi atau hubungi pengawas.', 'error');
        }
    });
}

// File upload handler
<?php if ($praktik->jenis_praktik === 'upload_file'): ?>
document.getElementById('filePraktik')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('praktik_id', praktikId);
    formData.append('hasil_id', <?= $hasil->id ?>);
    formData.append('file_praktik', file);
    
    $.ajax({
        url: '<?= base_url('cbtsiswa/praktik/uploadFile') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: (res) => {
            if (res.status) {
                Swal.fire({
                    title: 'File Uploaded',
                    text: res.message,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                updateFileList();
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        }
    });
});

function updateFileList() {
    // Fetch and display uploaded files
}
<?php endif; ?>

// Portfolio Mode Functions
<?php if ($hasil->mode_pengumpulan === 'portofolio'): ?>
function addPortfolioItem() {
    const types = [
        { id: 'file', icon: 'bi-file-earmark', label: 'File' },
        { id: 'url', icon: 'bi-link-45deg', label: 'URL/Link' },
        { id: 'text', icon: 'bi-text-paragraph', label: 'Catatan Teks' },
        { id: 'image', icon: 'bi-image', label: 'Gambar' }
    ];
    
    Swal.fire({
        title: 'Tambah Item Portofolio',
        html: `
            <div class="row g-2">
                ${types.map(t => `
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-primary w-100" onclick="selectPortfolioType('${t.id}')">
                            <i class="bi ${t.icon} fs-4 d-block mb-2"></i>
                            ${t.label}
                        </button>
                    </div>
                `).join('')}
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true
    });
}

function selectPortfolioType(type) {
    Swal.close();
    
    const templates = {
        file: `
            <div class="mb-3">
                <label class="form-label">Judul Item</label>
                <input type="text" id="itemTitle" class="form-control" placeholder="Contoh: Dokumentasi Proses">
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea id="itemDesc" class="form-control" rows="2" placeholder="Jelaskan isi file ini..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Pilih File</label>
                <input type="file" id="portfolioFile" class="form-control">
            </div>
        `,
        url: `
            <div class="mb-3">
                <label class="form-label">Judul Item</label>
                <input type="text" id="itemTitle" class="form-control" placeholder="Contoh: Link Demo">
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea id="itemDesc" class="form-control" rows="2" placeholder="Jelaskan link ini..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">URL</label>
                <input type="url" id="itemUrl" class="form-control" placeholder="https://...">
            </div>
        `,
        text: `
            <div class="mb-3">
                <label class="form-label">Judul Item</label>
                <input type="text" id="itemTitle" class="form-control" placeholder="Contoh: Catatan Proses">
            </div>
            <div class="mb-3">
                <label class="form-label">Isi Catatan</label>
                <textarea id="itemText" class="form-control" rows="5" placeholder="Tulis catatan Anda di sini..."></textarea>
            </div>
        `,
        image: `
            <div class="mb-3">
                <label class="form-label">Judul Item</label>
                <input type="text" id="itemTitle" class="form-control" placeholder="Contoh: Screenshot Hasil">
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea id="itemDesc" class="form-control" rows="2" placeholder="Jelaskan gambar ini..."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Upload Gambar</label>
                <input type="file" id="portfolioImage" class="form-control" accept="image/*">
            </div>
        `
    };
    
    Swal.fire({
        title: `Tambah ${type === 'file' ? 'File' : type === 'url' ? 'URL' : type === 'image' ? 'Gambar' : 'Catatan'}`,
        html: templates[type],
        showCancelButton: true,
        confirmButtonText: 'Tambahkan',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            return {
                title: document.getElementById('itemTitle')?.value,
                desc: document.getElementById('itemDesc')?.value || document.getElementById('itemText')?.value,
                url: document.getElementById('itemUrl')?.value,
                type: type
            };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value?.title) {
            // Save portfolio item via AJAX
            savePortfolioItem(result.value);
        }
    });
}

function savePortfolioItem(itemData) {
    $.ajax({
        url: '<?= base_url('cbtsiswa/praktik/addPortfolioItem') ?>',
        type: 'POST',
        data: {
            hasil_id: <?= $hasil->id ?>,
            ...itemData
        },
        dataType: 'json',
        success: (res) => {
            if (res.status) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Item portofolio ditambahkan.',
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false
                });
                refreshPortfolioItems();
            }
        }
    });
}

function refreshPortfolioItems() {
    $.get('<?= base_url('cbtsiswa/praktik/getPortfolioItems/' . $hasil->id) ?>', (res) => {
        if (res.items) {
            displayPortfolioItems(res.items);
        }
    });
}

function displayPortfolioItems(items) {
    const container = document.getElementById('portfolioItems');
    if (!container) return;
    
    if (items.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-collection fs-2 mb-2 d-block"></i>
                <small>Belum ada item.<br>Tambahkan untuk memperkaya jawaban Anda.</small>
            </div>
        `;
        return;
    }
    
    container.innerHTML = items.map(item => `
        <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3 mb-2">
            <div class="icon-box bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                <i class="bi ${getItemIcon(item.tipe_item)}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">${item.judul_item}</div>
                <small class="text-muted">${item.deskripsi_item || ''}</small>
            </div>
            ${item.is_highlight ? '<span class="badge bg-warning">⭐ Highlight</span>' : ''}
        </div>
    `).join('');
    
    updateProgress(items.length);
}

function getItemIcon(type) {
    const icons = {
        file: 'bi-file-earmark',
        url: 'bi-link-45deg',
        teks: 'bi-text-paragraph',
        gambar: 'bi-image',
        video: 'bi-camera-video'
    };
    return icons[type] || 'bi-file';
}
<?php endif; ?>

// Feedback System
function refreshFeedback() {
    $.get('<?= base_url('cbtsiswa/praktik/getFeedback/' . $hasil->id) ?>', (res) => {
        displayFeedback(res.feedback || []);
    });
}

function displayFeedback(feedbacks) {
    const panel = document.getElementById('feedbackPanel');
    if (!panel) return;
    
    if (feedbacks.length === 0) {
        panel.innerHTML = `
            <div class="list-group-item text-center text-muted py-4">
                <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                <small>Belum ada umpan balik.<br>Feedback akan muncul saat guru memberi penilaian.</small>
            </div>
        `;
        return;
    }
    
    panel.innerHTML = feedbacks.map(f => {
        const icons = {
            positif: 'bi-hand-thumbs-up text-success',
            perbaikan: 'bi-tools text-warning',
            pertanyaan: 'bi-question-circle text-info',
            saran: 'bi-lightbulb text-primary',
            motivasi: 'bi-heart text-danger'
        };
        
        return `
            <div class="list-group-item ${f.telah_dibaca ? '' : 'bg-light border-start border-3 border-success'}">
                <div class="d-flex gap-3">
                    <div class="flex-shrink-0">
                        <i class="bi ${icons[f.tipe_umpan_balik] || 'bi-chat'} fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <small class="fw-bold text-capitalize">${f.tipe_umpan_balik}</small>
                            <small class="text-muted" style="font-size: 10px;">${new Date(f.created_at).toLocaleDateString()}</small>
                        </div>
                        <p class="small mb-1">${f.isi_umpan_balik}</p>
                        ${f.dibalas_oleh_siswa ? 
                            `<small class="text-muted"><i class="bi bi-check2"></i> Anda membalas</small>` :
                            `<button type="button" class="btn btn-sm btn-link p-0" onclick="replyToFeedback(${f.id})">Balas</button>`
                        }
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function replyToFeedback(feedbackId) {
    Swal.fire({
        title: 'Balas Umpan Balik',
        input: 'textarea',
        inputPlaceholder: 'Tulis balasan Anda...',
        showCancelButton: true,
        confirmButtonText: 'Kirim',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.post('<?= base_url('cbtsiswa/praktik/replyFeedback') ?>', {
                feedback_id: feedbackId,
                balasan: result.value
            }, (res) => {
                if (res.status) {
                    Swal.fire('Terkirim!', 'Balasan Anda telah dikirim.', 'success');
                    refreshFeedback();
                }
            });
        }
    });
}

// Progress Tracker
function updateProgress(itemCount) {
    const bar = document.getElementById('progressBar');
    const text = document.getElementById('progressText');
    
    if (!bar || !text) return;
    
    const progress = Math.min(itemCount * 20, 100); // 20% per item, max 100%
    bar.style.width = `${progress}%`;
    
    const labels = [
        'Mulai Mengerjakan',
        'Proses Pengumpulan',
        'Hampir Selesai',
        'Siap Submit',
        'Lengkap!'
    ];
    text.textContent = labels[Math.min(itemCount, 4)] || 'Proses...';
}

// Initialize
$(document).ready(() => {
    startTimer();
    initAntiCheat();
    refreshFeedback();
    
    <?php if ($hasil->mode_pengumpulan === 'portofolio'): ?>
    refreshPortfolioItems();
    <?php endif; ?>
    
    // Auto-refresh feedback every 2 minutes
    setInterval(refreshFeedback, 120000);
});

// Cleanup
$(window).on('beforeunload', () => {
    clearInterval(countdownTimer);
    clearInterval(antiCheatInterval);
});
</script>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.fade-in-up { animation: fadeInUp 0.4s ease-out both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
#codeEditor { font-family: 'Courier New', monospace; background: #f8f9fa; }
</style>
<?= $this->endSection() ?>
