<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-file-earmark-person text-primary me-2"></i>
                    Hasil Ujian Praktik
                </h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('cbtpraktik/monitor/'.$praktik->id) ?>" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Monitoring
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Student & Exam Info -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                    <div class="card-body text-center p-4">
                        <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; background: var(--cbt-primary-gradient); color: white;">
                            <?= strtoupper($siswa->nama[0]) ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?= esc($siswa->nama) ?></h5>
                        <p class="text-muted small mb-3"><?= esc($siswa->nis) ?> • <?= esc($siswa->nama_kelas) ?></p>
                        <hr>
                        <div class="text-start">
                            <div class="mb-2">
                                <small class="text-muted d-block">Ujian:</small>
                                <span class="fw-bold"><?= esc($praktik->judul) ?></span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Mulai:</small>
                                <span class="fw-bold"><?= date('d M Y, H:i', strtotime($hasil->waktu_mulai)) ?></span>
                            </div>
                            <?php if ($hasil->waktu_submit): ?>
                            <div class="mb-2">
                                <small class="text-muted d-block">Selesai:</small>
                                <span class="fw-bold"><?= date('d M Y, H:i', strtotime($hasil->waktu_submit)) ?></span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted d-block">Durasi:</small>
                                <span class="fw-bold"><?= floor($hasil->durasi_pengerjaan_detik / 60) ?> Menit</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0" style="border-radius: 16px;">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-star me-2"></i>Penilaian</h6>
                    </div>
                    <div class="card-body p-4 text-center">
                        <?php $nilai = $hasil->nilai_akhir ?? 0; $kkmTuntas = ($praktik->kktp_mode ?? 'kkm') === 'kkm' && !empty($praktik->kkm) ? (float)$praktik->kkm : 66; $lulus = $nilai >= $kkmTuntas; $kktp = $nilai >= 86 ? 'M (Mahir)' : ($nilai >= 66 ? 'C (Cakap)' : ($nilai >= 41 ? 'L (Layak)' : ($nilai > 0 ? 'BB (Baru Berkembang)' : '-'))); ?>
                        <div class="display-4 fw-bold text-<?= $lulus ? 'success' : 'danger' ?>">
                            <?= number_format($nilai, 1) ?>
                        </div>
                        <div class="text-muted small mb-2">Nilai Akhir (<?= ($praktik->kktp_mode ?? 'kkm') === 'kktp' ? 'KKTP' : 'KKM' ?>: <?= ($praktik->kktp_mode ?? 'kkm') === 'kkm' ? $praktik->kkm : 'Cakap 66' ?>)</div>
                        <span class="badge bg-<?= $lulus ? 'success' : 'danger' ?> px-3 py-2 rounded-pill"><?= $kktp ?></span>
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-primary rounded-pill" onclick="window.location.href='<?= site_url('cbtpraktik/penilaian/'.$praktik->id) ?>'">
                                 <i class="bi bi-pencil-square me-2"></i> Beri/Ubah Nilai
                            </button>
                            <a href="<?= site_url('cbtpraktik/narasiRapor/'.$praktik->id.'/'.$siswa->id_siswa) ?>" class="btn btn-outline-success rounded-pill">
                                <i class="bi bi-file-text me-2"></i> Rapor Naratif
                            </a>
                            <a href="<?= site_url('cbtpraktik/remedial/'.$praktik->id) ?>" class="btn btn-outline-danger rounded-pill">
                                <i class="bi bi-arrow-repeat me-2"></i> Remedial
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission Detail -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2"></i>Jawaban & Pengumpulan</h6>
                        <span class="badge bg-<?= $hasil->status_pengerjaan === 'selesai' ? 'success' : 'warning' ?> rounded-pill px-3">
                            <?= strtoupper(str_replace('_', ' ', $hasil->status_pengerjaan)) ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($hasil->jawaban_teks): ?>
                            <div class="mb-4">
                                <label class="fw-bold text-muted small mb-2">JAWABAN TEKS / NARASI</label>
                                <div class="p-3 bg-light rounded border">
                                    <?= nl2br(esc($hasil->jawaban_teks)) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasil->kode_jawaban): ?>
                            <div class="mb-4">
                                <label class="fw-bold text-muted small mb-2">KODE PROGRAM (<?= strtoupper(esc($hasil->bahasa_pemrograman ?? 'PHP')) ?>)</label>
                                <pre class="p-3 bg-dark text-white rounded" style="font-family: 'Consolas', monospace; overflow-x: auto;"><code><?= esc($hasil->kode_jawaban) ?></code></pre>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasil->url_simulasi): ?>
                            <div class="mb-4">
                                <label class="fw-bold text-muted small mb-2">URL SIMULASI / PROJECT</label>
                                <div class="d-flex align-items-center p-3 border rounded bg-light">
                                    <i class="bi bi-link-45deg fs-4 me-3 text-primary"></i>
                                    <a href="<?= esc($hasil->url_simulasi) ?>" target="_blank" class="text-decoration-none fw-bold"><?= esc($hasil->url_simulasi) ?></a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($hasil->files)): ?>
                            <div class="mb-4">
                                <label class="fw-bold text-muted small mb-2">FILE LAMPIRAN</label>
                                <div class="row">
                                    <?php foreach ($hasil->files as $file): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center p-2 border rounded">
                                            <i class="bi bi-file-earmark-zip fs-4 me-2 text-primary"></i>
                                            <div class="text-truncate" title="<?= esc($file->file_name) ?>">
                                                <small class="d-block text-truncate"><?= esc($file->file_name) ?></small>
                                                <a href="<?= base_url($file->file_path) ?>" target="_blank" class="small text-decoration-none">Unduh File</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$hasil->jawaban_teks && !$hasil->kode_jawaban && !$hasil->url_simulasi && empty($hasil->files)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Belum ada data jawaban yang dikumpulkan.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Feedback Section -->
                <?php if ($hasil->feedback_guru): ?>
                <div class="card shadow-sm border-0" style="border-radius: 16px; background: #f0f9ff; border-left: 5px solid #0ea5e9 !important;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-info mb-2"><i class="bi bi-chat-left-dots me-2"></i>Feedback Guru</h6>
                        <p class="mb-0 text-dark"><?= nl2br(esc($hasil->feedback_guru)) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Self Assessment -->
                <?php if (!empty($selfAssessment)): ?>
                <div class="card shadow-sm border-0 mt-3" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-journal-check text-primary me-2"></i>Self-Assessment Siswa</h6>
                    </div>
                    <div class="card-body p-4">
                        <?php foreach ($selfAssessment as $sa): ?>
                        <div class="p-3 bg-light rounded-3 mb-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold">Nilai Diri: <?= esc($sa->nilai) ?></span>
                                <small class="text-muted"><?= date('d M Y H:i', strtotime($sa->created_at)) ?></small>
                            </div>
                            <?php if ($sa->refleksi): ?><p class="mb-1 small"><strong>Refleksi:</strong> <?= nl2br(esc($sa->refleksi)) ?></p><?php endif; ?>
                            <?php if ($sa->kesulitan): ?><p class="mb-1 small"><strong>Kesulitan:</strong> <?= nl2br(esc($sa->kesulitan)) ?></p><?php endif; ?>
                            <?php if ($sa->dimensi_ppp): ?><p class="mb-0 small"><strong>Dimensi PPP:</strong> <?= esc(is_array($sa->dimensi_ppp) ? implode(', ', $sa->dimensi_ppp) : $sa->dimensi_ppp) ?></p><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Observasi -->
                <?php if (!empty($observasi)): ?>
                <div class="card shadow-sm border-0 mt-3" style="border-radius: 16px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-eye text-success me-2"></i>Catatan Observasi</h6>
                    </div>
                    <div class="card-body p-4">
                        <?php foreach ($observasi as $obs): ?>
                        <div class="p-3 bg-light rounded-3 mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold small"><?= esc($obs->aspek) ?></span>
                                <span class="badge bg-success bg-opacity-10 text-success">Skor: <?= esc($obs->skor) ?></span>
                            </div>
                            <?php if ($obs->catatan): ?><p class="mb-1 small text-muted"><?= nl2br(esc($obs->catatan)) ?></p><?php endif; ?>
                            <?php if ($obs->dimensi_ppp): ?><p class="mb-0 small text-muted"><strong>Dimensi PPP:</strong> <?= esc(is_array($obs->dimensi_ppp) ? implode(', ', $obs->dimensi_ppp) : $obs->dimensi_ppp) ?></p><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    :root {
        --cbt-primary-gradient: linear-gradient(135deg, #6366f1, #4f46e5);
    }
</style>

<?= $this->endSection() ?>
