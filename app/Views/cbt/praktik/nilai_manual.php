<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clipboard-check text-primary me-2"></i>
                    Input Nilai Praktik Manual
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('cbtpraktik') ?>" class="text-decoration-none">Ujian Praktik</a>
                    </li>
                    <li class="breadcrumb-item active">Nilai Manual</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Modern Premium Banner -->
        <div class="banner-premium mb-4 shadow-sm py-3 px-4" style="background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); border-radius: 1.25rem; position: relative; overflow: hidden; z-index: 10;">
            <div class="banner-mesh" style="position: absolute; inset: 0; background: radial-gradient(circle at 70% 30%, rgba(255,255,255,0.15) 0%, transparent 60%); pointer-events: none;"></div>
            <div class="row align-items-center position-relative" style="z-index: 2;">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center mb-1">
                        <div class="bg-white bg-opacity-20 p-2 rounded-3 me-3 backdrop-blur shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; backdrop-filter: blur(8px);">
                            <i class="bi bi-clipboard-check text-white fs-4"></i>
                        </div>
                        <div class="d-flex flex-column">
                            <h5 class="fw-bold text-white mb-0" style="letter-spacing: -0.3px;">Input Nilai Praktik Manual</h5>
                            <span class="text-white opacity-80 small"><?= esc($praktik->judul) ?></span>
                        </div>
                    </div>
                    <p class="text-white opacity-80 small mb-0 mt-2">
                        <span class="fw-medium"><i class="bi bi-journal-bookmark me-1"></i> <?= esc($praktik->nama_mapel ?? '-') ?></span> 
                        <span class="mx-2">•</span> 
                        <span class="fw-medium"><i class="bi bi-people me-1"></i> <?= count($siswa) ?> Peserta</span>
                            <?php if($id_kelas_selected): ?>
                                <span class="mx-2">•</span> 
                                <?php 
                                    $nama_kelas_selected = '-';
                                    if ($id_kelas_selected && !empty($listKelas)) {
                                        foreach($listKelas as $k) {
                                            $curr_id = is_object($k) ? ($k->id_kelas ?? null) : ($k['id_kelas'] ?? null);
                                            if($curr_id == $id_kelas_selected) {
                                                $nama_kelas_selected = is_object($k) ? ($k->nama_kelas ?? '-') : ($k['nama_kelas'] ?? '-');
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                <span class="badge bg-white text-primary fw-bold" style="font-size: 0.75rem;">KELAS: <?= esc($nama_kelas_selected) ?></span>
                            <?php endif; ?>
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
                    <div class="d-inline-flex align-items-center bg-white bg-opacity-10 p-2 rounded-3 border border-white border-opacity-20 shadow-sm backdrop-blur" style="backdrop-filter: blur(8px);">
                        <div class="px-3 border-end border-white border-opacity-20">
                            <small class="text-white opacity-70 d-block text-uppercase fw-bold" style="font-size: 0.6rem;">Target</small>
                            <span class="fw-bold text-white fs-5"><?= ($kktp_mode ?? 'kkm') === 'kktp' ? 'KKTP' : 'KKM' ?>: <?= $kkm ?></span>
                        </div>
                        <div class="px-3">
                            <small class="text-white opacity-70 d-block text-uppercase fw-bold" style="font-size: 0.6rem;">Status Dinilai</small>
                            <span class="fw-bold text-warning fs-5" id="total_dinilai_counter"><?= $statistics->total_dinilai ?? 0 ?></span>
                            <span class="text-white opacity-70 small">/ <?= count($siswa) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nilai Form -->
        <div class="card border-0 shadow-sm fade-in-up rounded-4 overflow-hidden">
            <?php if(empty($rubrik)): ?>
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="bg-warning-subtle text-warning d-inline-flex p-4 rounded-circle mb-3 border border-warning-subtle shadow-sm">
                            <i class="bi bi-journal-x fs-1"></i>
                        </div>
                        <h4 class="fw-bold">Rubrik Belum Ditentukan</h4>
                        <p class="text-muted mx-auto" style="max-width: 500px;">Pilih template rubrik yang tersedia untuk mata pelajaran <strong><?= esc($praktik->nama_mapel) ?></strong> untuk memulai proses penilaian manual.</p>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-5">
                            <div class="p-4 bg-light rounded-4 border shadow-sm">
                                <?= form_open('cbtpraktik/update', ['class' => 'd-inline']) ?>
                                    <input type="hidden" name="id" value="<?= $praktik->id ?>">
                                    <input type="hidden" name="apply_template" value="1">
                                    <input type="hidden" name="judul" value="<?= $praktik->judul ?>">
                                    <input type="hidden" name="mapel" value="<?= $praktik->mapel_id ?>">
                                    <input type="hidden" name="status" value="<?= $praktik->status ?>">
                                    
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Pilih Template Rubrik</label>
                                    <div class="d-grid gap-3">
                                        <select name="template_rubrik" class="form-select select2" required>
                                            <option value="">-- Pilih Template --</option>
                                            <?php foreach($templates as $t): ?>
                                                <option value="<?= $t->id ?>"><?= esc($t->nama_template) ?> (<?= ucfirst($t->jenis_praktik) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary py-2 rounded-3 shadow-sm">
                                            <i class="bi bi-check-circle me-2"></i> Terapkan & Mulai Menilai
                                        </button>
                                    </div>
                                <?= form_close() ?>
                            </div>
                            
                            <div class="mt-4">
                                <a href="<?= site_url('cbtpraktik/edit/' . $praktik->id) ?>" class="btn btn-link text-decoration-none text-muted btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i> Atau buat rubrik kustom secara manual
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-header bg-white border-0 py-3 px-4 shadow-sm z-index-1">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="fw-bold text-dark mb-0">
                                <i class="bi bi-table text-primary me-2"></i>Daftar Nilai Siswa
                            </h5>
                        </div>
                        <div class="col-md-8 text-md-end mt-2 mt-md-0">
                            <div class="d-inline-flex align-items-center me-3">
                                <label class="form-label small fw-bold text-muted mb-0 me-2">PILIH KELAS:</label>
                                <select id="filterKelas" class="form-select form-select-sm border-primary-subtle rounded-3 fw-bold" style="width: 180px;">
                                    <option value="">-- Semua Kelas --</option>
                                    <?php foreach($listKelas as $kls): 
                                        $kls_id = is_object($kls) ? ($kls->id_kelas ?? null) : ($kls['id_kelas'] ?? null);
                                        $kls_nama = is_object($kls) ? ($kls->nama_kelas ?? '-') : ($kls['nama_kelas'] ?? '-');
                                        if (!$kls_id) continue;
                                    ?>
                                        <option value="<?= $kls_id ?>" <?= $id_kelas_selected == $kls_id ? 'selected' : '' ?>><?= esc($kls_nama) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                <a href="<?= base_url('cbtpraktik/nilaiManualPdf/' . $praktik->id . ($id_kelas_selected ? '?kelas=' . $id_kelas_selected : '')) ?>" target="_blank" class="btn btn-light btn-sm border">
                                    <i class="bi bi-printer me-1 text-secondary"></i> Cetak Format
                                </a>
                                <button type="button" class="btn btn-light btn-sm border" onclick="hitungSemuaNilai()">
                                    <i class="bi bi-calculator me-1"></i> Hitung Ulang
                                </button>
                                <a href="<?= base_url('cbtpraktik/exportReport/' . $praktik->id . ($id_kelas_selected ? '?kelas=' . $id_kelas_selected : '')) ?>" class="btn btn-light btn-sm border">
                                    <i class="bi bi-file-earmark-excel me-1 text-success"></i> Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?= form_open('cbtpraktik/simpanNilaiManual', ['id' => 'formNilaiManual']) ?>
                <input type="hidden" name="praktik_id" value="<?= $praktik->id ?>">
                
                <div class="table-container shadow-sm border-top">
                    <table class="table table-hover table-striped align-middle mb-0" id="tableNilaiManual">
                        <thead>
                            <tr>
                                <th class="text-center sticky-col" width="50" style="background: #f1f5f9; color: #475569; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">NO</th>
                                <th class="sticky-col-2" width="250" style="background: #f1f5f9; color: #475569; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">NAMA SISWA</th>
                                <th class="text-center" width="100" style="background: #f1f5f9; color: #475569; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">STATUS</th>
                                <th class="text-center" width="100" style="background: #f1f5f9; color: #475569; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">KELAS</th>
                                <?php foreach($rubrik as $r): ?>
                                <th class="text-center px-1" style="min-width: 100px; background: #f1f5f9;">
                                    <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 70px;">
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="fw-bold text-primary" style="font-size: 0.8rem; line-height: 1;"><?= esc($r->kode_kriteria ?? $r->id) ?></span>
                                            <button type="button" class="btn btn-link p-0 text-info" onclick="showRubrikInfo(<?= $r->id ?>)" style="font-size: 0.8rem; line-height: 1;">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </button>
                                        </div>
                                        <div class="text-dark small fw-medium mb-1 text-truncate w-100 px-1 text-center" style="font-size: 0.65rem; line-height: 1.2;" title="<?= esc($r->nama_kriteria) ?>">
                                            <?= esc($r->nama_kriteria) ?>
                                        </div>
                                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill" style="font-size: 0.6rem; padding: 2px 8px;">
                                            <?= $r->bobot_persen ?>%
                                        </span>
                                    </div>
                                </th>
                                <?php endforeach; ?>
                                <th class="text-center fw-bold" width="100" style="background: #e2e8f0; color: #1e293b; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">NILAI AKHIR</th>
                                <th class="text-center" width="80" style="background: #f1f5f9; color: #475569; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">PRED</th>
                                <th class="text-center" width="60" style="background: #f1f5f9; color: #475569; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($siswa)): ?>
                            <tr>
                                <td colspan="<?= 7 + count($rubrik) ?>" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                    Belum ada data siswa untuk ujian ini.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($siswa as $i => $s): 
                                $s_id = is_object($s) ? ($s->id_siswa ?? null) : ($s['id_siswa'] ?? null);
                                if (!$s_id) continue;
                                $hasil = $hasilBySiswa[$s_id] ?? null;
                                $rincian = [];
                                if ($hasil && $hasil->rincian_nilai_rubrik) {
                                    $rincian = json_decode($hasil->rincian_nilai_rubrik, true);
                                }
                                $rowClass = ($hasil && $hasil->status_penilaian == 'sudah_dinilai') ? 'row-dinilai' : '';
                            ?>
                            <tr data-siswa-id="<?= $s_id ?>" data-kelas="<?= is_object($s) ? ($s->id_kelas ?? '') : ($s['id_kelas'] ?? '') ?>" class="<?= $rowClass ?>">
                                    <td class="text-center text-muted sticky-col border-end"><?= $i + 1 ?></td>
                                    <td class="sticky-col-2 border-end">
                                        <div class="fw-bold text-dark text-uppercase" style="font-size: 0.8rem;"><?= esc(is_object($s) ? ($s->nama ?? '-') : ($s['nama'] ?? '-')) ?></div>
                                        <div class="text-muted small" style="font-size: 0.65rem;">NIS: <?= esc(is_object($s) ? ($s->nis ?? '-') : ($s['nis'] ?? '-')) ?></div>
                                        <input type="hidden" name="kelas[<?= $s_id ?>]" value="<?= is_object($s) ? ($s->id_kelas ?? '') : ($s['id_kelas'] ?? '') ?>">
                                    </td>
                                    <td class="text-center">
                                        <?php if ($hasil && $hasil->status_pengerjaan === 'selesai'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Sudah Kumpul
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill" style="font-size: 0.65rem;">
                                                <i class="bi bi-clock-history me-1"></i> Belum Kumpul
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fw-medium px-2 py-1"><?= esc(is_object($s) ? ($s->nama_kelas ?? '-') : ($s['nama_kelas'] ?? '-')) ?></span>
                                    </td>
                                    
                                    <?php foreach($rubrik as $r): 
                                        $kriteriaId = $r->id ?? $r->kode_kriteria;
                                        // Cast to string for consistent JSON key lookup (JSON may decode int keys as int or string)
                                        $nilai = $rincian[(string)$kriteriaId] ?? $rincian[(int)$kriteriaId] ?? '';
                                    ?>
                                    <td class="text-center p-2">
                                        <div class="input-wrapper mx-auto" style="max-width: 65px;">
                                            <input type="number" 
                                                name="nilai[<?= $s->id_siswa ?>][<?= $kriteriaId ?>]" 
                                                class="form-control form-control-sm text-center input-nilai py-1 px-1" 
                                                data-siswa="<?= $s->id_siswa ?>"
                                                data-bobot="<?= $r->bobot_persen ?>"
                                                min="0" max="100" 
                                                value="<?= $nilai ?>"
                                                placeholder="0">
                                        </div>
                                    </td>
                                    <?php endforeach; ?>
                                    
                                    <td class="text-center bg-light border-start border-end">
                                        <span class="fw-bold fs-5 nilai-akhir" id="nilaiAkhir<?= $s->id_siswa ?>">
                                            <?= $hasil->nilai_akhir ?? 0 ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge predikat-badge" id="predikat<?= $s->id_siswa ?>" style="min-width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">
                                            -
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-action-group justify-content-center">
                                            <?php if ($hasil && $hasil->status_pengerjaan === 'selesai'): ?>
                                                <button type="button" class="btn btn-action btn-action-info" onclick="bukaHasil(<?= $hasil->id ?>)" title="Buka Hasil Digital">
                                                    <i class="bi bi-file-earmark-code"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-action btn-action-primary" onclick="lihatDetail(<?= $s->id_siswa ?>)" title="Rincian Nilai">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer bg-white border-top p-4 shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="small fw-bold text-muted text-uppercase me-2" style="font-size: 0.65rem;">Predikat KKTP:</span>
                                <span class="badge px-3 py-2 rounded-pill small" style="background: #22c55e; color: white;">M (86-100) Mahir</span>
                                <span class="badge px-3 py-2 rounded-pill small" style="background: #3b82f6; color: white;">C (66-85) Cakap</span>
                                <span class="badge px-3 py-2 rounded-pill small" style="background: #f59e0b; color: white;">L (41-65) Layak</span>
                                <span class="badge px-3 py-2 rounded-pill small" style="background: #ef4444; color: white;">BB (0-40) Baru Berkembang</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <a href="<?= base_url('cbtpraktik') ?>" class="btn btn-light px-4 border rounded-3 shadow-sm">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary px-5 rounded-3 shadow-sm fw-bold" id="btnSimpan">
                                    <i class="bi bi-save2 me-2"></i> Simpan Penilaian
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?= form_close() ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal Hasil Digital -->
<div class="modal fade" id="modalHasilDigital" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-code text-info me-2"></i>Hasil Pengerjaan Digital</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="contentHasilDigital">
                <!-- Content via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2 text-muted">Memuat hasil...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Rubrik -->
<div class="modal fade" id="modalRubrikInfo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-info-circle-fill me-2"></i>Panduan Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1 d-block">Kriteria</label>
                    <h5 class="fw-bold text-dark mb-1" id="infoNamaKriteria">-</h5>
                    <p class="text-muted small mb-0" id="infoDeskripsiKriteria">-</p>
                </div>
                
                <label class="small fw-bold text-muted text-uppercase mb-2 d-block">Skala Penilaian</label>
                <div class="list-group list-group-flush border rounded-3 overflow-hidden" id="infoLevels">
                    <!-- Levels via JS -->
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
:root {
    --primary-soft: #f0f4ff;
    --primary-main: #6366f1;
    --primary-border: #e2e8f0;
}

.rounded-4 { border-radius: 1rem !important; }
.bg-primary-subtle { background-color: #f0f4ff !important; }
.bg-warning-subtle { background-color: #fffbeb !important; }
.bg-success-subtle { background-color: #f0fdf4 !important; }
.bg-danger-subtle { background-color: #fef2f2 !important; }
.bg-info-subtle { background-color: #f0f9ff !important; }

.fade-in-up { animation: fadeInUp 0.4s ease-out both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

/* Table Enhancements */
.table-container {
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: auto;
    position: relative;
    border-bottom: 1px solid #e2e8f0;
}

.table thead th {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #f8fafc;
    border-bottom: 2px solid #cbd5e1;
    padding: 1rem 0.5rem;
    color: #475569;
    box-shadow: inset 0 -1px 0 #cbd5e1;
}

.sticky-col {
    position: sticky !important;
    left: 0;
    z-index: 10 !important;
    background: #ffffff !important;
}
.sticky-col-2 {
    position: sticky !important;
    left: 50px;
    z-index: 10 !important;
    background: #ffffff !important;
    box-shadow: 2px 0 5px rgba(0,0,0,0.05) !important;
}

/* Header Sticky Overrides */
.table thead th.sticky-col, 
.table thead th.sticky-col-2 {
    z-index: 110 !important;
    background: #f8fafc !important;
}

.table tbody tr:hover td {
    background-color: #f1f5f9 !important;
}

.table tbody tr:hover .sticky-col,
.table tbody tr:hover .sticky-col-2 {
    background-color: #f1f5f9 !important;
}

.row-dinilai td {
    background-color: #fcfdfd;
}

/* Input Styling */
.input-nilai {
    height: 34px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.95rem;
    color: #1e293b;
    background: #ffffff;
    transition: all 0.2s;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
}

.input-nilai:focus {
    border-color: var(--primary-main);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    background: #ffffff;
    outline: none;
    z-index: 10;
}

.input-nilai.is-valid { border-color: #10b981; background-color: #f0fdf4; background-image: none !important; padding-right: 0.25rem !important; }
.input-nilai.is-invalid { border-color: #ef4444; background-color: #fef2f2; background-image: none !important; padding-right: 0.25rem !important; }

/* Chrome, Safari, Edge, Opera - Remove arrows */
.input-nilai::-webkit-outer-spin-button,
.input-nilai::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.input-nilai[type=number] { -moz-appearance: textfield; }

/* Badges & Buttons */
.predikat-badge { font-weight: 800; font-size: 0.75rem; border: 1px solid rgba(0,0,0,0.05); border-radius: 50px !important; }
.predikat-m { background-color: #22c55e; color: white; }
.predikat-cakap { background-color: #3b82f6; color: white; }
.predikat-l { background-color: #f59e0b; color: white; }
.predikat-bb { background-color: #ef4444; color: white; }

.btn-icon {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-icon:hover { transform: translateY(-2px); }

.nilai-akhir {
    color: var(--primary-main);
    font-weight: 800 !important;
    font-variant-numeric: tabular-nums;
    text-shadow: 0 1px 2px rgba(99, 102, 241, 0.1);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const KKM = <?= $kkm ?>;
const RUBRIK_DATA = <?= json_encode($rubrik) ?>;

// KKTP thresholds (Kurikulum Merdeka)
const PRED_M = 86;   // Mahir
const PRED_C = 66;   // Cakap
const PRED_L = 41;   // Layak
                    // < 41 = BB (Baru Berkembang)

function showRubrikInfo(rubrikId) {
    const r = RUBRIK_DATA.find(x => x.id == rubrikId);
    if (!r) return;

    $('#infoNamaKriteria').text(r.nama_kriteria);
    $('#infoDeskripsiKriteria').text(r.deskripsi || 'Tidak ada deskripsi kriteria.');
    
    let levelsHtml = '';
    if (r.levels && r.levels.length > 0) {
        r.levels.forEach(l => {
            levelsHtml += `
                <div class="list-group-item p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark">${l.nama_level}</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Skor: ${l.nilai_min} - ${l.nilai_max}</span>
                    </div>
                    <p class="small text-muted mb-0">${l.deskripsi || '-'}</p>
                </div>`;
        });
    } else {
        levelsHtml = '<div class="list-group-item text-center py-4 text-muted small">Tidak ada tingkatan skor yang didefinisikan.</div>';
    }
    
    $('#infoLevels').html(levelsHtml);
    const modal = new bootstrap.Modal(document.getElementById('modalRubrikInfo'));
    modal.show();
}

$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
    
    // Calculate all grades on page load
    hitungSemuaNilai();
    
    // Real-time calculation on input change
    $(document).on('input', '.input-nilai', function() {
        const siswaId = $(this).data('siswa');
        hitungNilaiSiswa(siswaId);
    });

    // Keyboard Navigation
    $(document).on('keydown', '.input-nilai', function(e) {
        const currentInput = $(this);
        const visibleInputs = $('.input-nilai:visible');
        const currentIndex = visibleInputs.index(currentInput);
        const rubrikCount = <?= count($rubrik) ?>;
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                visibleInputs.eq(currentIndex + rubrikCount).focus().select();
                break;
            case 'ArrowUp':
                e.preventDefault();
                visibleInputs.eq(currentIndex - rubrikCount).focus().select();
                break;
            case 'ArrowRight':
                if(this.selectionEnd === this.value.length) {
                    visibleInputs.eq(currentIndex + 1).focus().select();
                }
                break;
            case 'ArrowLeft':
                if(this.selectionStart === 0) {
                    visibleInputs.eq(currentIndex - 1).focus().select();
                }
                break;
            case 'Enter':
                e.preventDefault();
                visibleInputs.eq(currentIndex + rubrikCount).focus().select();
                break;
        }
    });

    // Filter Kelas
    $('#filterKelas').change(function() {
        const kelasId = $(this).val();
        let url = '<?= site_url("cbtpraktik/nilaiManual/" . $praktik->id) ?>';
        if(kelasId) {
            url += '?kelas=' + kelasId;
        }
        window.location.href = url;
    });

    // Highlight row on focus
    $(document).on('focus', '.input-nilai', function() {
        $(this).closest('tr').addClass('table-primary-subtle');
    }).on('blur', '.input-nilai', function() {
        $(this).closest('tr').removeClass('table-primary-subtle');
    });

    // Form submission
    $('#formNilaiManual').submit(function(e) {
        e.preventDefault();
        
        const btn = $('#btnSimpan');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i> Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disimpan',
                        text: res.message || 'Nilai berhasil diperbarui',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message || 'Gagal menyimpan nilai', 'error');
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});

function hitungNilaiSiswa(siswaId) {
    // Rumus SAMA dengan backend: weighted average atas kriteria TERISI
    // Σ(nilai×bobot) / Σ(bobot). Kriteria kosong diabaikan (bukan dihitung 0).
    let weighted = 0, sumBobot = 0, hasValue = false;

    $(`.input-nilai[data-siswa="${siswaId}"]`).each(function() {
        const rawVal = $(this).val();
        const bobot  = parseFloat($(this).data('bobot')) || 0;

        if (rawVal === '') {
            $(this).removeClass('is-valid is-invalid');
            return; // lewati kriteria kosong
        }

        const val = parseFloat(rawVal) || 0;
        hasValue = true;

        if (bobot > 0) { weighted += val * bobot; sumBobot += bobot; }

        if (val >= PRED_M) {
            $(this).addClass('is-valid').removeClass('is-invalid');
        } else if (val >= PRED_C) {
            $(this).removeClass('is-valid is-invalid');
        } else {
            $(this).addClass('is-invalid').removeClass('is-valid');
        }
    });

    const finalScore = sumBobot > 0 ? (weighted / sumBobot) : 0;

    $(`#nilaiAkhir${siswaId}`).text(sumBobot > 0 ? finalScore.toFixed(2) : '0');
    updatePredikat(siswaId, finalScore);

    if (hasValue) {
        $(`tr[data-siswa-id="${siswaId}"]`).addClass('row-dinilai');
    }
}

function updatePredikat(scoreId, score) {
    const badge = $(`#predikat${scoreId}`);
    badge.removeClass('predikat-m predikat-cakap predikat-l predikat-bb');
    
    if (score >= PRED_M) {
        badge.addClass('predikat-m').text('M');
    } else if (score >= PRED_C) {
        badge.addClass('predikat-cakap').text('C');
    } else if (score >= PRED_L) {
        badge.addClass('predikat-l').text('L');
    } else if (score > 0) {
        badge.addClass('predikat-bb').text('BB');
    } else {
        badge.text('-');
    }
}

function hitungSemuaNilai() {
    $('tr[data-siswa-id]').each(function() {
        const id = $(this).data('siswa-id');
        hitungNilaiSiswa(id);
    });
}

function bukaHasil(hasilId) {
    const modal = new bootstrap.Modal(document.getElementById('modalHasilDigital'));
    modal.show();
    
    $('#contentHasilDigital').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-info" role="status"></div>
            <p class="mt-2 text-muted">Memuat hasil...</p>
        </div>
    `);

    $.ajax({
        url: '<?= site_url("cbtpraktik/getHasilDetail") ?>',
        type: 'GET',
        data: { hasil_id: hasilId },
        dataType: 'json',
        success: function(res) {
            if(res.status) {
                const data = res.data;
                let filesHtml = '';
                if(data.files && data.files.length > 0) {
                    filesHtml = '<h6 class="fw-bold mt-4 mb-3">File Pengumpulan:</h6><div class="row g-2">';
                    data.files.forEach(f => {
                        filesHtml += `
                            <div class="col-md-4">
                                <a href="<?= base_url() ?>/${f.file_path}" target="_blank" class="card card-body p-2 text-decoration-none border shadow-sm h-100">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-text fs-3 me-2 text-info"></i>
                                        <div class="text-truncate">
                                            <div class="small fw-bold text-dark text-truncate">${f.nama_file}</div>
                                            <div class="text-muted" style="font-size: 0.6rem;">${(f.ukuran_kb/1024).toFixed(2)} MB</div>
                                        </div>
                                    </div>
                                </a>
                            </div>`;
                    });
                    filesHtml += '</div>';
                }

                $('#contentHasilDigital').html(`
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light border-0 rounded-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-muted small text-uppercase mb-3">Informasi Siswa</h6>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Nama Lengkap</small>
                                        <span class="fw-bold">${data.nama_siswa}</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Kelas</small>
                                        <span class="fw-bold">${data.nama_kelas}</span>
                                    </div>
                                    <div class="mb-0">
                                        <small class="text-muted d-block">Waktu Submit</small>
                                        <span class="fw-bold text-info">${data.waktu_submit}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h6 class="fw-bold mb-3">Jawaban / Hasil Praktik:</h6>
                            <div class="p-3 bg-dark text-light rounded-3 shadow-sm font-monospace mb-3" style="min-height: 150px; overflow-x: auto; white-space: pre-wrap;">
                                ${data.jawaban_teks || '<span class="text-muted italic">Tidak ada jawaban teks/kode</span>'}
                            </div>
                            ${filesHtml}
                        </div>
                    </div>
                `);
            } else {
                $('#contentHasilDigital').html(`<div class="alert alert-danger">${res.message}</div>`);
            }
        },
        error: function() {
            $('#contentHasilDigital').html('<div class="alert alert-danger">Gagal mengambil data dari server</div>');
        }
    });
}

function lihatDetail(siswaId) {
    const row = $(`tr[data-siswa-id="${siswaId}"]`);
    const nama = row.find('.fw-bold.text-dark').first().text().trim();
    const nis  = row.find('.text-muted.small').first().text().replace('NIS:', '').trim();
    const score = $(`#nilaiAkhir${siswaId}`).text().trim();
    const predikat = $(`#predikat${siswaId}`).text().trim();
    
    // Build per-kriteria table
    let kriteriaRows = '';
    RUBRIK_DATA.forEach(r => {
        const inputEl = $(`.input-nilai[data-siswa="${siswaId}"][name*="[${r.id}]"]`);
        const val = inputEl.val() !== '' ? parseFloat(inputEl.val()) || 0 : '-';
        const bobot = r.bobot_persen || 0;
        const kontribusi = val !== '-' ? ((val * bobot / 100)).toFixed(1) : '-';
        kriteriaRows += `<tr>
            <td class="text-start">${r.kode_kriteria || r.id}</td>
            <td class="text-start">${r.nama_kriteria}</td>
            <td class="text-center"><span class="badge bg-primary-subtle text-primary border">${bobot}%</span></td>
            <td class="text-center fw-bold">${val}</td>
            <td class="text-center text-success fw-bold">${kontribusi}</td>
        </tr>`;
    });
    
    const predikatClass = predikat === 'M' ? 'success' : predikat === 'C' ? 'info' : predikat === 'L' ? 'warning' : 'danger';
    
    Swal.fire({
        title: '<i class="bi bi-clipboard-data text-primary me-2"></i>Rincian Penilaian',
        html: `
            <div class="text-start">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                    <div>
                        <div class="fw-bold fs-6">${nama}</div>
                        <small class="text-muted">NIS: ${nis}</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold display-6 text-primary">${score}</div>
                        <span class="badge bg-${predikatClass} px-3">${predikat || '-'}</span>
                    </div>
                </div>
                <table class="table table-sm table-bordered table-hover mb-0" style="font-size: 0.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Kriteria</th>
                            <th class="text-center">Bobot</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody>${kriteriaRows}</tbody>
                    <tfoot>
                        <tr class="table-primary fw-bold">
                            <td colspan="4" class="text-end">Nilai Akhir (Weighted Average):</td>
                            <td class="text-center text-primary fs-6">${score}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>`,
        width: '600px',
        showCloseButton: true,
        confirmButtonText: 'Tutup',
        customClass: { popup: 'shadow-lg' }
    });
}
</script>
<?= $this->endSection() ?>
