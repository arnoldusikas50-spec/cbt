<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="app-content-header">
    <div class="container-fluid ps-3 ps-lg-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold" style="color: #00695c;">
                    <i class="bi bi-file-text me-2"></i>Rapor Naratif
                </h3>
                <p class="text-muted small mb-0">Kurikulum Merdeka — Laporan Capaian Pembelajaran</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button class="btn btn-success shadow-sm px-3 rounded-pill" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak
                </button>
                <a href="<?= site_url('cbtpraktik') ?>" class="btn btn-outline-secondary shadow-sm px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5" style="max-width: 900px; margin: 0 auto;">
            <!-- Kop -->
            <div class="text-center border-bottom pb-4 mb-4">
                <h4 class="fw-bold mb-1"><?= esc($setting->nama_sekolah ?? 'LAPORAN HASIL BELAJAR') ?></h4>
                <p class="text-muted small mb-0"><?= esc($setting->alamat ?? '') ?></p>
                <hr class="mt-3 mb-0" style="border-top: 2px solid #00695c;">
            </div>

            <!-- Identitas -->
            <table class="table table-sm table-borderless mb-4" style="font-size: 0.9rem;">
                <tr>
                    <td style="width: 140px;"><strong>Nama Siswa</strong></td>
                    <td>: <?= esc($laporan['identitas_siswa']['nama'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td><strong>NIS</strong></td>
                    <td>: <?= esc($laporan['identitas_siswa']['nis'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td><strong>Kelas</strong></td>
                    <td>: <?= esc($laporan['identitas_siswa']['kelas'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td><strong>Mata Pelajaran</strong></td>
                    <td>: <?= esc($laporan['informasi_praktik']['mapel'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td><strong>Judul Praktik</strong></td>
                    <td>: <?= esc($laporan['informasi_praktik']['judul'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td><strong>Guru</strong></td>
                    <td>: <?= esc($laporan['informasi_praktik']['guru'] ?? '-') ?></td>
                </tr>
            </table>

            <!-- CP / ATP -->
            <?php if (!empty($laporan['capaian_pembelajaran'])): ?>
            <div class="mb-4 p-3 bg-light rounded-3">
                <strong class="d-block mb-1">Capaian Pembelajaran:</strong>
                <p class="small text-muted mb-0"><?= esc($laporan['capaian_pembelajaran']) ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($laporan['alur_tujuan_pembelajaran'])): ?>
            <div class="mb-4 p-3 bg-light rounded-3">
                <strong class="d-block mb-1">Alur Tujuan Pembelajaran:</strong>
                <p class="small text-muted mb-0"><?= esc($laporan['alur_tujuan_pembelajaran']) ?></p>
            </div>
            <?php endif; ?>

            <!-- Capaian Akhir -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded-3">
                        <small class="text-muted d-block">Nilai Akhir</small>
                        <span class="display-6 fw-bold" style="color: <?= $laporan['kktp']['warna'] ?? '#333' ?>;">
                            <?= $laporan['nilai_akhir'] ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded-3">
                        <small class="text-muted d-block">Predikat KKTP</small>
                        <span class="badge px-4 py-2 fs-6 fw-bold" style="background: <?= $laporan['kktp']['warna'] ?? '#6b7280' ?>; color: white;">
                            <?= $laporan['kktp']['predikat'] ?> (<?= $laporan['kktp']['singkatan'] ?>)
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded-3">
                        <small class="text-muted d-block">Ketercapaian</small>
                        <span class="fw-bold fs-5 text-<?= $laporan['kktp']['nilai'] >= 66 ? 'success' : 'danger' ?>">
                            <?= $laporan['kktp']['nilai'] >= 66 ? 'TUNTAS' : 'BELUM TUNTAS' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Rincian Per Kriteria -->
            <?php if (!empty($laporan['rincian_per_kriteria'])): ?>
            <div class="mb-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-list-check me-2"></i>Rincian Per Kriteria</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Kriteria</th>
                                <th class="text-center">Nilai</th>
                                <th class="text-center">Predikat</th>
                                <th>Dimensi PPP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan['rincian_per_kriteria'] as $r): ?>
                            <tr>
                                <td><?= esc($r['kriteria']) ?></td>
                                <td class="text-center fw-bold"><?= $r['nilai'] ?></td>
                                <td class="text-center">
                                    <span class="badge px-3" style="background: <?= $r['warna'] ?? '#6b7280' ?>; color: white;">
                                        <?= $r['predikat'] ?> (<?= $r['singkatan'] ?>)
                                    </span>
                                </td>
                                <td class="small"><?= esc($r['dimensi'] ?? 'umum') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Dimensi PPP -->
            <?php if (!empty($laporan['dimensi_ppp_teridentifikasi'])): ?>
            <div class="mb-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-stars me-2" style="color:#7c3aed;"></i>Profil Pelajar Pancasila</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php $dimensiLabel = [
                        'beriman' => 'Beriman & Berakhlak Mulia',
                        'berkebinekaan' => 'Berkebinekaan Global',
                        'gotong_royong' => 'Gotong Royong',
                        'mandiri' => 'Mandiri',
                        'bernalar_kritis' => 'Bernalar Kritis',
                        'kreatif' => 'Kreatif',
                    ]; ?>
                    <?php foreach ($laporan['dimensi_ppp_teridentifikasi'] as $d): ?>
                    <span class="badge px-3 py-2 rounded-pill" style="background:#7c3aed1a; color:#7c3aed; border:1px solid #7c3aed26;">
                        <?= $dimensiLabel[$d] ?? $d ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Deskripsi Naratif -->
            <div class="mb-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-chat-quote me-2"></i>Deskripsi Naratif</h6>
                <div class="p-3 bg-light rounded-3">
                    <?php $deskripsi = nl2br(esc($laporan['deskripsi_naratif'] ?? '')); ?>
                    <p class="mb-0" style="font-size: 0.9rem; line-height: 1.7; text-align: justify;">
                        <?= $deskripsi ?: 'Belum ada deskripsi naratif.' ?>
                    </p>
                </div>
            </div>

            <!-- Rekomendasi -->
            <?php if (!empty($laporan['rekomendasi'])): ?>
            <div class="mb-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-lightbulb me-2 text-warning"></i>Rekomendasi</h6>
                <div class="p-3 bg-warning bg-opacity-10 rounded-3">
                    <p class="small mb-0"><?= nl2br(esc($laporan['rekomendasi'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tanda Tangan -->
            <div class="row mt-5">
                <div class="col-6 text-center">
                    <p class="small text-muted mb-5">Mengetahui,<br>Kepala Sekolah</p>
                    <div style="height: 60px;"></div>
                    <p class="fw-bold mb-0"><?= esc($setting->kepsek ?? '__________________') ?></p>
                    <p class="small text-muted">NIP. <?= esc($setting->nip_kepsek ?? '-') ?></p>
                </div>
                <div class="col-6 text-center">
                    <p class="small text-muted mb-5"><?= date('d F Y') ?>,<br>Guru Mata Pelajaran</p>
                    <div style="height: 60px;"></div>
                    <p class="fw-bold mb-0"><?= esc($laporan['informasi_praktik']['guru'] ?? '__________________') ?></p>
                    <p class="small text-muted">NIP. <?= esc($laporan['informasi_praktik']['nip_guru'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .app-content-header, .btn, .app-footer, .main-header { display: none !important; }
    .app-content { padding-top: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    body { background: white !important; }
</style>
<?= $this->endSection() ?>
