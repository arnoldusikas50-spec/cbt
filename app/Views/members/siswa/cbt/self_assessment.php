<?= $this->extend('layouts/siswa') ?>
<?= $this->section('content') ?>
<div class="container-fluid py-4 container-p-x main-content-overlap fade-in-up pb-5 mb-5 pb-lg-4 mb-lg-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 70px; height: 70px; background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <i class="bi bi-journal-check text-white fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Self-Assessment</h4>
                    <p class="text-muted small mb-0">Refleksikan hasil belajarmu pada ujian praktik</p>
                    <p class="fw-semibold mt-2 mb-0"><?= esc($praktik->judul) ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-purple"></i>Form Refleksi Diri</h6>
                </div>
                <?= form_open('cbtsiswa/selfAssessment/' . $praktik->id, ['id' => 'formSelfAssessment']) ?>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Penilaian Diri (1-100)</label>
                        <input type="range" name="nilai_diri" class="form-range" min="0" max="100" step="1" id="nilaiDiriRange" value="<?= $assessment->nilai_diri ?? 50 ?>">
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">0</span>
                            <span class="fw-bold text-purple" id="nilaiDiriValue"><?= $assessment->nilai_diri ?? 50 ?></span>
                            <span class="small text-muted">100</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1">
                            <span>BB (0-40)</span>
                            <span>L (41-65)</span>
                            <span>C (66-85)</span>
                            <span>M (86-100)</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Apa yang sudah kamu kuasai dengan baik?</label>
                        <textarea name="refleksi" class="form-control" rows="3" placeholder="Contoh: Saya sudah mampu memahami alur kerja..."><?= esc($assessment->refleksi ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Kesulitan apa yang kamu hadapi?</label>
                        <textarea name="kesulitan" class="form-control" rows="3" placeholder="Contoh: Saya masih bingung dengan..."><?= esc($assessment->kesulitan ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Strategi apa yang akan kamu lakukan untuk memperbaiki?</label>
                        <textarea name="strategi" class="form-control" rows="3" placeholder="Contoh: Saya akan berlatih lagi dan bertanya pada guru..."><?= esc($assessment->strategi ?? '') ?></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Dimensi Profil Pelajar Pancasila yang terbentuk</label>
                        <div class="row g-2">
                            <?php $dimensiList = [
                                'beriman' => 'Beriman & Berakhlak Mulia',
                                'berkebinekaan' => 'Berkebinekaan Global',
                                'gotong_royong' => 'Gotong Royong',
                                'mandiri' => 'Mandiri',
                                'bernalar_kritis' => 'Bernalar Kritis',
                                'kreatif' => 'Kreatif',
                            ];
                            $terpilih = explode(',', $assessment->dimensi_ppp_teridentifikasi ?? '');
                            ?>
                            <?php foreach ($dimensiList as $key => $label): ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="dimensi_ppp[]" value="<?= $key ?>" id="ppp_<?= $key ?>" <?= in_array($key, $terpilih) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="ppp_<?= $key ?>"><?= $label ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 px-4 py-3">
                    <button type="submit" class="btn btn-purple px-5 rounded-pill shadow-sm w-100">
                        <i class="bi bi-check2-circle me-2"></i> Simpan Self-Assessment
                    </button>
                </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$('#nilaiDiriRange').on('input', function() {
    const val = $(this).val();
    let warna = '#6b7280';
    if (val >= 86) warna = '#22c55e';
    else if (val >= 66) warna = '#3b82f6';
    else if (val >= 41) warna = '#f59e0b';
    else warna = '#ef4444';
    $('#nilaiDiriValue').text(val).css('color', warna);
});

$('#formSelfAssessment').on('submit', function(e) {
    e.preventDefault();
    $.post($(this).attr('action'), $(this).serialize(), function(res) {
        if (res.status) {
            showSuccessToast('Self-assessment berhasil disimpan!');
        } else {
            showDangerToast(res.message || 'Gagal menyimpan');
        }
    }, 'json');
});
</script>
<?= $this->endSection() ?>
