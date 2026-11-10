<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 container-p-x main-content-overlap fade-in-up pb-5 mb-5 pb-lg-4 mb-lg-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- ══ HEADER ══ -->
            <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden mb-4 position-relative">
                <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 0;">
                    <div style="position: absolute; top: -10%; right: -5%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, rgba(255,255,255,0) 70%); border-radius: 50%;"></div>
                </div>
                
                <div class="card-body p-4 p-md-5 text-center position-relative z-1">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="bi bi-check-circle-fill text-white fs-1"></i>
                    </div>
                    <h2 class="fw-black text-dark mb-2" style="letter-spacing: -1px;">Hasil Ujian Praktik</h2>
                    <p class="text-muted fw-semibold mb-0"><?= esc($praktik->judul) ?></p>
                </div>
            </div>

            <!-- ══ SCORE CARD ══ -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-body p-4">
                    <?php
                    $nilai = $hasil->nilai_akhir ?? 0;
                    $kkm = $praktik->kkm ?? 70;
                    $lulus = $nilai >= 66;
                    $predikat = $nilai >= 86 ? 'Mahir (M)' : ($nilai >= 66 ? 'Cakap (C)' : ($nilai >= 41 ? 'Layak (L)' : ($nilai > 0 ? 'Baru Berkembang (BB)' : '-')));
                    $statusBg = $lulus ? '#22c55e' : '#ef4444';
                    $statusText = $lulus ? 'TUNTAS' : 'BELUM TUNTAS';
                    ?>
                    
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light rounded-4">
                                <small class="text-muted text-uppercase fw-bold letter-spacing-1 d-block mb-2">Nilai Akhir</small>
                                <div class="display-2 fw-black text-primary" style="letter-spacing: -2px;">
                                    <?= number_format($nilai, 2) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light rounded-4">
                                <small class="text-muted text-uppercase fw-bold letter-spacing-1 d-block mb-2">Predikat KKTP</small>
                                <div class="display-6 fw-black" style="letter-spacing: -2px; color: <?= $statusBg ?>;">
                                    <?= $predikat ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light rounded-4">
                                <small class="text-muted text-uppercase fw-bold letter-spacing-1 d-block mb-2">KKM</small>
                                <div class="display-2 fw-black text-dark" style="letter-spacing: -2px;">
                                    <?= $kkm ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <span class="badge px-4 py-2 fs-5 fw-bold" style="background: <?= $statusBg ?>; border-radius: 50px;">
                            <i class="bi bi-<?= $lulus ? 'check-circle' : 'exclamation-circle' ?> me-2"></i><?= $statusText ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ══ DETAIL PENILAIAN ══ -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-list-check text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Detail Penilaian</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($rubrik)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 fw-semibold text-muted small text-uppercase letter-spacing-1">Kriteria</th>
                                    <th class="px-4 py-3 fw-semibold text-muted small text-uppercase letter-spacing-1 text-center" width="100">Bobot</th>
                                    <th class="px-4 py-3 fw-semibold text-muted small text-uppercase letter-spacing-1 text-center" width="120">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rubrik as $r): 
                                    $kId = $r->id ?? $r->kode_kriteria;
                                    $nilaiKriteria = $rincian_nilai[$kId] ?? 0;
                                ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark"><?= esc($r->nama_kriteria) ?></div>
                                        <?php if ($r->deskripsi): ?>
                                            <small class="text-muted d-block mt-1"><?= esc($r->deskripsi) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge bg-light text-dark border"><?= $r->bobot_persen ?>%</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="fw-bold fs-5 <?= $nilaiKriteria > 0 ? 'text-success' : 'text-muted' ?>">
                                            <?= number_format($nilaiKriteria, 2) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-info-circle me-2"></i>Detail rubrik tidak tersedia
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ══ FEEDBACK ══ -->
            <?php if (!empty($hasil->feedback_guru)): ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-chat-left-text text-info"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Feedback dari Assessor</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-light rounded-4">
                        <?= nl2br(esc($hasil->feedback_guru)) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ══ SELF-ASSESSMENT ══ -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                        <i class="bi bi-journal-check text-white fs-4"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Refleksi Diri</h5>
                    <p class="text-muted small mb-3">Evaluasi hasil belajarmu melalui self-assessment</p>
                    <a href="<?= base_url('cbtsiswa/selfAssessment/' . $praktik->id) ?>" class="btn btn-purple px-4 rounded-pill shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Isi Self-Assessment
                    </a>
                </div>
            </div>

            <!-- ══ ACTION BUTTON ══ -->
            <div class="text-center">
                <a href="<?= base_url('cbtsiswa/praktik') ?>" class="btn btn-outline-secondary btn-lg px-5 rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Ujian
                </a>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
