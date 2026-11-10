<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    /* Select2 Bootstrap 4 Theme Fix for Input Groups */
    .input-group > .select2-container--bootstrap4 {
        flex: 1 1 auto;
        width: 1% !important;
    }

    .input-group > .select2-container--bootstrap4 .select2-selection {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-left: none !important;
        height: calc(1.5em + 0.75rem + 2px) !important;
        display: flex;
        align-items: center;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        padding-top: 0 !important;
        line-height: inherit !important;
    }
</style>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-file-earmark-pdf-fill text-primary me-2"></i>
                    <?= esc($judul ?? 'Laporan') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-speedometer2 me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Arsip Laporan') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Summary Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex align-items-center position-relative z-1">
                            <div class="bg-grey bg-opacity-10 p-3 rounded-3 me-3">
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Arsip Laporan</h5>
                                <p class="text-muted mb-0 small">Dokumen administrasi ujian</p>
                            </div>
                            <div class="ms-auto d-none d-md-flex gap-2">
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary border-opacity-10">
                                    <i class="bi bi-database me-1"></i> Total:
                                    <?= $summary['total'] ?>
                                </span>
                                <span
                                    class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill border border-info border-opacity-10">
                                    <i class="bi bi-file-earmark-richtext me-1"></i> Berita Acara:
                                    <?= $summary['jenis']['Berita Acara'] ?? 0 ?>
                                </span>
                                <span
                                    class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill border border-success border-opacity-10">
                                    <i class="bi bi-shield-check me-1"></i> Pakta:
                                    <?= $summary['jenis']['Pakta Integritas'] ?? 0 ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex d-md-none flex-wrap gap-2 mt-3 z-1 position-relative">
                            <span
                                class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary border-opacity-10">
                                <i class="bi bi-database me-1"></i> Total:
                                <?= $summary['total'] ?>
                            </span>
                        </div>
                        <div class="position-absolute top-0 end-0 h-100 w-25 bg-gradient-light opacity-25"
                            style="transform: skewX(-20deg);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title">
                    <?= esc($subjudul) ?>
                </h3>
                <div class="card-tools ms-auto">
                    <div class="btn-group shadow-sm">
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-pencil me-1"></i> Buat Dokumen
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item btn-upload-type" href="#" data-jenis="Berita Acara"><i
                                        class="bi bi-file-text me-2"></i>Berita Acara</a></li>
                            <li>
                                <?php if ($has_pakta_integritas): ?>
                                    <a class="dropdown-item disabled" href="#" style="cursor: not-allowed; opacity: 0.5;"
                                        data-bs-toggle="tooltip" title="Anda sudah memiliki Pakta Integritas">
                                        <i class="bi bi-shield-check me-2"></i>Pakta Integritas
                                        <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Sudah Ada</span>
                                    </a>
                                <?php else: ?>
                                    <a class="dropdown-item btn-upload-type" href="#" data-jenis="Pakta Integritas">
                                        <i class="bi bi-shield-check me-2"></i>Pakta Integritas
                                    </a>
                                <?php endif; ?>
                            </li>
                            <li><a class="dropdown-item btn-upload-type" href="#" data-jenis="Daftar Hadir Peserta"><i
                                        class="bi bi-list-check me-2"></i>Daftar Hadir</a></li>
                            <li><a class="dropdown-item btn-upload-type" href="#" data-jenis="Laporan Pelaksanaan"><i
                                        class="bi bi-journal-text me-2"></i>Laporan Pelaksanaan</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item btn-upload-type" href="#" data-jenis="Lain-Lain"><i
                                        class="bi bi-archive me-2"></i>Dokumen Lain</a></li>
                        </ul>
                    </div>
                    <a href="<?= site_url('laporan/harian') ?>" class="btn btn-warning btn-sm ms-2 shadow-sm ">
                        <i class="bi bi-file-earmark-medical me-1"></i> Laporan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Jenis Dokumen</label>
                        <select id="filterJenis" class="form-select form-select-sm shadow-sm">
                            <option value="">Semua Dokumen</option>
                            <option value="Berita Acara">Berita Acara</option>
                            <option value="Pakta Integritas">Pakta Integritas</option>
                            <option value="Daftar Hadir Peserta">Daftar Hadir Peserta</option>
                            <option value="Laporan Pelaksanaan">Laporan Pelaksanaan</option>
                            <option value="Lain-Lain">Lain-Lain</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Pengawas</label>
                        <select id="filterUploader" class="form-select form-select-sm shadow-sm">
                            <option value="">Semua Pengawas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status Dokumen</label>
                        <select id="filterStatus" class="form-select form-select-sm shadow-sm">
                            <option value="">Semua Status</option>
                            <option value="Checking">Checking</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table id="tableLaporan" class="table table-striped table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th width="20" class="text-center align-middle">No</th>
                                <th class="align-middle">Judul</th>
                                <th class="align-middle">Keterangan</th>
                                <th class="align-middle">Tanggal Upload</th>
                                <th class="align-middle">Uploader</th>
                                <th class="align-middle text-center">Status</th>
                                <th width="120" class="text-center align-middle">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach ($laporans as $row):
                                $stt = $row->status;
                                $status_badge = '<span class="badge bg-warning">Checking</span>';
                                if ($stt == 1) {
                                    $title = 'Disetujui oleh ' . ($row->approver_first ?? '') . ' pada ' . date('d-m-Y H:i', strtotime($row->tgl_approval));
                                    $status_badge = '<span class="badge bg-success" data-bs-toggle="tooltip" title="' . $title . '">Disetujui</span>';
                                } elseif ($stt == 2) {
                                    $title = 'Alasan: ' . $row->alasan_correction;
                                    $status_badge = '<span class="badge bg-danger" data-bs-toggle="tooltip" title="' . $title . '">Perlu Perbaikan</span>';
                                }
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <?= $no++ ?>
                                    </td>
                                    <td>
                                        <?= esc($row->judul) ?><br>
                                        <badge class="badge bg-success">
                                            <?= esc($row->jenis) ?>
                                        </badge>
                                    </td>
                                    <td>
                                        <?= esc($row->keterangan) ?>
                                    </td>
                                    <td>
                                        <?= date('d-m-Y H:i', strtotime($row->created_at)) ?>
                                    </td>
                                    <td>
                                        <?= esc($row->first_name . ' ' . $row->last_name) ?>
                                    </td>
                                    <td class="text-center">
                                        <?= $status_badge ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-info shadow-sm btn-preview"
                                                data-file="<?= $row->file ?>" data-id="<?= $row->id_laporan ?>"
                                                data-jenis="<?= $row->jenis ?>" data-bs-toggle="tooltip" title="Preview">
                                                <i class="bi bi-eye text-white"></i>
                                            </button>
                                            <?php if ($row->jenis == 'Berita Acara'): ?>
                                                <button class="btn btn-sm btn-primary shadow-sm btn-photos"
                                                    data-id="<?= $row->id_laporan ?>" data-file1="<?= $row->file ?>" 
                                                    data-file2="<?= $row->file_2 ?>" data-file3="<?= $row->file_3 ?>"
                                                    data-bs-toggle="tooltip" title="Lihat Foto">
                                                    <i class="bi bi-camera text-white"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($stt != 1 && ($row->jenis == 'Berita Acara' || $row->jenis == 'Pakta Integritas')): ?>
                                                <?php
                                                $can_edit = false;
                                                if ($is_admin || (!$is_pengawas && isset($panitia) && $panitia->jabatan != 'Pengawas')) {
                                                    $can_edit = true;
                                                } elseif ($row->id_user == $user->id) {
                                                    $can_edit = true;
                                                }
                                                if ($can_edit): ?>
                                                    <button class="btn btn-sm btn-secondary shadow-sm btn-edit"
                                                        data-id="<?= $row->id_laporan ?>" data-jenis="<?= $row->jenis ?>"
                                                        data-bs-toggle="tooltip" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if (!$is_pengawas && ($is_admin || ($panitia && $panitia->jabatan != 'Pengawas'))): ?>
                                                <?php if ($stt != 1): ?>
                                                    <button class="btn btn-sm btn-success shadow-sm btn-approve"
                                                        data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip" title="Approve">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning shadow-sm btn-reject"
                                                        data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip" title="Tolak">
                                                        <i class="bi bi-x text-white"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-danger shadow-sm btn-delete"
                                                    data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <?php if ($stt != 1): ?>
                                                    <button class="btn btn-sm btn-danger shadow-sm btn-delete"
                                                        data-id="<?= $row->id_laporan ?>" data-bs-toggle="tooltip" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($stt == 2): ?>
                                                    <button class="btn btn-sm btn-primary shadow-sm btn-reason"
                                                        data-reason="<?= esc($row->alasan_correction) ?>" data-bs-toggle="tooltip"
                                                        title="Lihat Alasan">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Photos -->
<div class="modal fade" id="modalPhotos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white p-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-camera me-2"></i>Foto Dokumentasi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light bg-opacity-50">
                <div id="photoContainer" class="row g-3 justify-content-center">
                    <!-- Photos will be loaded here via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('modals') ?>

<?= form_open_multipart('laporan/save_berita_acara', ['id' => 'formBeritaAcara', 'class' => 'needs-validation']) ?>
<!-- Modal Berita Acara -->
<div class="modal fade" id="modalBeritaAcara" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white p-4" style="border-radius: 20px 20px 0 0;">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Form Berita Acara</h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <input type="hidden" name="id_laporan" id="editBeritaAcaraId">
            
            <div class="modal-body p-4">
                <!-- Section 1: Session Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Informasi Sesi & Ruang</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Mata Pelajaran</label>
                        <input type="hidden" name="mapel_nama" id="inputMapelNama">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-journal-check"></i></span>
                            <select name="mapel" id="selectMapel" class="form-select" required>
                                <option value="">Pilih Mata Pelajaran</option>
                            </select>
                        </div>
                        <span class="small text-muted d-block mt-1" id="info-mapel">
                            <i class="bi bi-info-circle me-1"></i>Daftar mata pelajaran mengikuti tanggal ujian yang dipilih.
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Tanggal Ujian</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar3"></i></span>
                            <input type="date" name="waktu_ujian" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Ruang</label>
                        <input type="hidden" name="ruang_nama" id="inputRuangNama">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-door-open"></i></span>
                            <select name="ruang" id="selectRuang" class="form-select" required>
                                <option value="">Pilih Ruang</option>
                            </select>
                        </div>
                        <span class="small text-muted d-block mt-1" id="info-ruang-sesi">
                            <i class="bi bi-info-circle me-1"></i>Daftar ruang mengikuti jadwal ujian mata pelajaran &amp; tanggal terpilih.
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Sesi</label>
                        <input type="hidden" name="sesi_nama" id="inputSesiNama">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-clock-history"></i></span>
                            <select name="sesi" id="selectSesi" class="form-select" required>
                                <option value="">Pilih Sesi</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Waktu Mulai</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-play-circle"></i></span>
                            <input type="text" name="jam_mulai" class="form-control jam-picker" placeholder="HH:mm" required autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Waktu Selesai</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-stop-circle"></i></span>
                            <input type="text" name="jam_selesai" class="form-control jam-picker" placeholder="HH:mm" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Attendance statistics -->
                <div class="card border-0 bg-light shadow-none mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-people text-primary me-2"></i>Statistik Kehadiran</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bg-white p-3 rounded-4 border">
                                    <label class="small text-muted fw-bold d-block mb-1">Seharusnya Hadir</label>
                                    <input type="number" name="jml_seharusnya" id="jmlSeharusnya" class="form-control border-0 p-0 fs-5 fw-bold" required min="0" placeholder="0">
                                    <span class="small text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Diisi otomatis dari data alokasi ruang/sesi, dapat disesuaikan bila tidak sesuai kenyataan</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-white p-3 rounded-4 border">
                                    <label class="small text-muted fw-bold d-block mb-1">Hadir (Nyata)</label>
                                    <input type="number" name="jml_hadir" id="jmlHadir" class="form-control border-0 p-0 fs-5 fw-bold text-success" required min="0" placeholder="0" readonly>
                                    <span class="small text-muted d-block mt-1"><i class="bi bi-calculator me-1"></i>Seharusnya dikurangi tidak hadir</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-white p-3 rounded-4 border bg-opacity-50">
                                    <label class="small text-muted fw-bold d-block mb-1">Tidak Hadir</label>
                                    <input type="number" name="jml_tidak_hadir" id="jmlTidakHadir" class="form-control border-0 p-0 fs-5 fw-bold text-danger" readonly required min="0" placeholder="0">
                                    <span class="small text-muted d-block mt-1"><i class="bi bi-calculator me-1"></i>Otomatis dari jumlah siswa terpilih</span>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2 mt-1" id="ringkasan-alasan-wrap" style="display:none;">
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2" id="ringkasan-alasan"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Student Details & Notes -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Peserta Yang Tidak Hadir (Pilih Nama)</label>
                    <div id="info-siswa-ruang" class="alert alert-light border small mb-2 py-2 px-3">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        <span class="text-muted">Pilih <b>Ruang</b> dan <b>Sesi</b> terlebih dahulu untuk memuat daftar peserta pada sesi tersebut.</span>
                    </div>
                    <select name="siswa_tidak_hadir[]" id="selectSiswaTidakHadir" class="form-select select2-siswa" multiple="multiple" data-placeholder="Cari dan pilih siswa yang tidak hadir..." style="width: 100%;"></select>

                    <!-- Tabel keterangan alasan ketidakhadiran -->
                    <div class="mt-3" id="wrap-tabel-alasan" style="display:none;">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-x text-primary me-2"></i>Keterangan Ketidakhadiran</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" id="tabelAlasan">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Nama Siswa</th>
                                        <th width="110">No. Peserta</th>
                                        <th width="110">Kelas</th>
                                        <th width="150">Alasan</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="body-tabel-alasan"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Uraian / Ringkasan Pelaksanaan</label>
                    <textarea name="uraian" class="form-control" rows="3" placeholder="Contoh: Pada hari ini telah dilaksanakan ujian dengan kondisi berjalan tertib..." required style="border-radius: 12px;"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Kendala / Kejadian Khusus</label>
                        <textarea name="kendala" class="form-control" rows="2" placeholder="Biarkan kosong jika tidak ada kendala" style="border-radius: 12px;"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-muted">Solusi / Tindak Lanjut</label>
                        <textarea name="solusi" class="form-control" rows="2" placeholder="Langkah yang diambil saat ada kendala" style="border-radius: 12px;"></textarea>
                    </div>
                </div>

                <!-- Section 4: Documentation -->
                <div>
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-camera text-primary me-2"></i>Foto Dokumentasi (Max 3)</h6>
                    <div class="row g-3">
                        <?php for($i=1; $i<=3; $i++): ?>
                        <div class="col-md-4">
                            <div class="upload-box p-3 rounded-4 text-center position-relative" style="min-height: 160px; border: 2px dashed #6c757d; background: #f8f9fa; transition: all 0.3s;" onmouseover="this.style.borderColor='#0d6efd'; this.style.background='#e7f1ff';" onmouseout="this.style.borderColor='#6c757d'; this.style.background='#f8f9fa';">
                                <input type="file" name="foto_<?= $i ?>" id="inputFoto<?= $i ?>" class="form-control form-control-sm foto-input position-absolute top-0 start-0 w-100 h-100 opacity-0" accept="image/*" <?= $i==1 ? 'required' : '' ?> data-preview="previewFoto<?= $i ?>" style="cursor: pointer; z-index: 2;">

                                <div id="previewFoto<?= $i ?>" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="pointer-events: none;">
                                    <div class="preview-placeholder">
                                        <div class="bg-grey bg-opacity-10 rounded-circle p-3 mb-2 d-inline-flex">
                                            <i class="bi bi-cloud-arrow-up fs-3 text-primary"></i>
                                        </div>
                                        <p class="small fw-semibold text-dark mb-0"><?= $i==1 ? 'Foto Wajib *' : 'Foto Optional' ?></p>
                                        <p class="small text-muted">Klik untuk upload</p>
                                    </div>
                                    <img src="" class="img-thumbnail d-none" style="max-height: 120px; max-width: 100%; border-radius: 10px; pointer-events: auto;">
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer p-4 bg-light border-0" style="border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" id="btnSimpanBerita">
                    <i class="bi bi-save me-1"></i> <span class="btn-text">Simpan Berita Acara</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= form_open('laporan/save_pakta_integritas', ['id' => 'formPaktaIntegritas']) ?>
<!-- Modal Pakta Integritas -->
<div class="modal fade" id="modalPaktaIntegritas" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-success text-white p-4" style="border-radius: 20px 20px 0 0;">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Form Pakta Integritas</h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <input type="hidden" name="id_laporan" id="editPaktaIntegritasId">
            
            <div class="modal-body p-4">
                <?php if ($is_pengawas): ?>
                <div class="alert alert-info bg-opacity-10 border-info border-opacity-20 d-flex mb-4" style="border-radius: 15px;">
                    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                    <p class="small mb-0 text-info">
                        <strong>Informasi:</strong> Data pribadi di bawah ini diambil otomatis dari profil Anda. 
                        Jika terdapat kesalahan, silakan perbarui melalui menu <strong>Profil Panitia</strong> agar data sinkron di seluruh sistem.
                    </p>
                </div>
                <?php endif; ?>

                <!-- Section 1: Personal Data -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge text-success me-2"></i>Data Pribadi Pengawas</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required style="border-radius: 10px;"
                                value="<?= isset($panitia->nama_panitia) ? esc($panitia->nama_panitia) : esc($user->nama_lengkap ?? (($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))) ?>"
                                <?= $is_pengawas ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">NIP / NIK</label>
                            <input type="text" name="nip" class="form-control" style="border-radius: 10px;"
                                value="<?= isset($panitia->nip) ? esc($panitia->nip) : '' ?>"
                                <?= $is_pengawas ? 'readonly' : '' ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Tugas / Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control" required style="border-radius: 10px;"
                                value="<?= isset($panitia->jabatan) ? esc(ucwords($panitia->jabatan)) : ($is_admin ? 'Administrator' : '') ?>"
                                <?= $is_pengawas ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Asal Instansi <span class="text-danger">*</span></label>
                            <input type="text" name="instansi" class="form-control" required style="border-radius: 10px;"
                                value="<?= isset($panitia->instansi) ? esc($panitia->instansi) : (isset($setting->nama_sekolah) ? esc($setting->nama_sekolah) : '') ?>"
                                <?= $is_pengawas ? 'readonly' : '' ?>>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">No. Handphone (WhatsApp)</label>
                            <input type="text" name="no_hp" class="form-control" style="border-radius: 10px;"
                                value="<?= isset($panitia->no_hp) ? esc($panitia->no_hp) : '' ?>"
                                <?= $is_pengawas ? 'readonly' : '' ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Alamat Email</label>
                            <input type="email" name="email" class="form-control" style="border-radius: 10px;"
                                value="<?= isset($panitia->email) ? esc($panitia->email) : (isset($user->email) ? esc($user->email) : '') ?>"
                                <?= $is_pengawas ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>

                <hr class="opacity-10 mb-4">

                <!-- Section 2: Duty Location -->
                <div>
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt text-success me-2"></i>Tempat & Waktu Bertugas</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" style="border-radius: 10px;"
                                value="<?= isset($setting->provinsi) ? esc($setting->provinsi) : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Kabupaten / Kota</label>
                            <input type="text" name="kota" class="form-control" style="border-radius: 10px;"
                                value="<?= isset($setting->kota) ? esc($setting->kota) : '' ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Tempat Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="text" name="tempat" class="form-control" required style="border-radius: 10px;"
                                value="<?= isset($setting->nama_sekolah) ? esc($setting->nama_sekolah) : '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted">Rentang Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                            <input type="text" name="tanggal_pelaksanaan" class="form-control" required style="border-radius: 10px;"
                                placeholder="Contoh: 10 s/d 14 Januari 2026">
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-success bg-opacity-10 border-success border-opacity-20 mt-3 mb-0" style="border-radius: 15px;">
                    <div class="form-check d-flex align-items-start">
                        <input class="form-check-input me-3 mt-1" type="checkbox" id="setujuPakta" name="setuju" style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
                        <label class="form-check-label small text-success mb-0" for="setujuPakta" style="cursor: pointer; line-height: 1.5;">
                            Saya menyatakan berkomitmen untuk menjaga integritas dan kerahasiaan pelaksanaan ujian sesuai peraturan yang berlaku.
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer p-4 bg-light border-0" style="border-radius: 0 0 20px 20px;">
                <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success px-4 py-2 rounded-pill shadow-sm" id="btnSimpanPakta">
                    <i class="bi bi-save me-1"></i> <span class="btn-text">Simpan Pakta Integritas</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= form_open_multipart('laporan/upload', ['id' => 'formUpload']) ?>
<!-- Modal Upload -->
<div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-light p-4" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold" id="modalUploadTitle">Upload Dokumen Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="jenis" id="inputJenis">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Judul Laporan / Dokumen</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-type"></i></span>
                        <input type="text" name="judul" class="form-control" required placeholder="Masukkan judul yang jelas">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Keterangan Singkat</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Tambahkan catatan jika diperlukan" style="border-radius: 12px;"></textarea>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-bold text-muted">File Dokumen (PDF)</label>
                    <div class="p-3 bg-light rounded-4 border" style="border-style: dashed !important;">
                        <input type="file" name="file_laporan" class="form-control" accept="application/pdf" required>
                        <small class="d-block mt-2 text-muted text-xs">Pastikan file dalam format PDF dengan ukuran maksimal 5MB.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-4 border-0">
                <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm" id="btnSimpan">
                    <i class="bi bi-cloud-upload me-1"></i> Simpan Dokumen
                </button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<!-- Modal Preview -->
<div class="modal fade" id="modalPreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 90% !important;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header">
                <h5 class="modal-title">Preview Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 h-100">
                <iframe id="previewFrame" src="" width="100%" height="100%" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<?= form_open('laporan/reject', ['id' => 'formReject']) ?>
<!-- Modal Reject -->
<div class="modal fade" id="modalReject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_laporan" id="rejectId">
                <div class="mb-3">
                    <label>Alasan Perbaikan</label>
                    <textarea name="alasan" class="form-control" required rows="3"
                        placeholder="Tuliskan alasan penolakan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Kirim Revisi</button>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        // Init Timepicker 24 Jam
        $('.jam-picker').datetimepicker({
            datepicker: false,
            format: 'H:i',
            step: 15,
            formatTime: 'H:i',
            scrollInput: false
        });

        if (typeof ajaxcsrf === 'function') {
            ajaxcsrf();
        } else {
            console.log('ajaxcsrf not defined');
        }

        function showBootstrapAlert(type, message) {
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
            const bgClass = type === 'success' ? 'bg-success text-white' : 'bg-danger text-white';
            showToast(type, message);
        }

        // Debugging
        console.log('Laporan JS Loaded');

        // Helper to show modal safely (Bootstrap 5)
        function showModal(id) {
            var el = document.getElementById(id);
            if (el) {
                var modal = bootstrap.Modal.getInstance(el);
                if (!modal) {
                    modal = new bootstrap.Modal(el);
                }
                modal.show();
            } else {
                console.error('Modal element not found:', id);
            }
        }

        function hideModal(id) {
            var el = document.getElementById(id);
            if (el) {
                var modal = bootstrap.Modal.getInstance(el);
                if (modal) modal.hide();
            }
        }

        // Button handlers (Moved to top/delegated to ensure functionality)
        $(document).on('click', '.btn-upload-type', function (e) {
            e.preventDefault();
            var jenis = $(this).data('jenis');
            console.log('Button clicked:', jenis);

            if (jenis == 'Berita Acara') {
                $('#editBeritaAcaraId').val('');
                var beritaForm = document.getElementById('formBeritaAcara');
                if (beritaForm) beritaForm.reset();
                $('#selectMapel').val('').trigger('change');
                muatMapelUjian();
                $('#selectRuang').empty().html('<option value="">Pilih Ruang</option>');
                $('#selectSesi').empty().html('<option value="">Pilih Sesi</option>');
                daftarRuangSesi = [];
                // Reset data kehadiran
                daftarSiswaRuang = {};
                alasanTerpilih = {};
                $('#selectSiswaTidakHadir').empty();
                $('#body-tabel-alasan').empty();
                $('#wrap-tabel-alasan').hide();
                $('#ringkasan-alasan').html('');
                $('#ringkasan-alasan-wrap').hide();
                $('#jmlSeharusnya').val('');
                $('#jmlHadir').val('');
                $('#jmlTidakHadir').val('');
                $('#info-siswa-ruang').removeClass('alert-danger').addClass('alert-light')
                    .html('<i class="bi bi-info-circle text-primary me-1"></i><span class="text-muted">Pilih <b>Ruang</b> dan <b>Sesi</b> terlebih dahulu untuk memuat daftar peserta pada sesi tersebut.</span>');
                // Clear file previews
                $('.foto-input').val('');
                $('[id^="previewFoto"]').hide().find('img').attr('src', '');

                showModal('modalBeritaAcara');
            } else if (jenis == 'Pakta Integritas') {
                $('#editPaktaIntegritasId').val('');
                var paktaForm = document.getElementById('formPaktaIntegritas');
                if (paktaForm) paktaForm.reset();

                // Reset checkbox and disable submit button
                $('#setujuPakta').prop('checked', false);
                $('#btnSimpanPakta').prop('disabled', true).addClass('opacity-50');

                showModal('modalPaktaIntegritas');
            }
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        try {
            var table = $('#tableLaporan').DataTable({
                "pageLength": 10,
                "order": [[3, "desc"]],
                "columnDefs": [{
                    "searchable": false,
                    "orderable": false,
                    "targets": 0
                }]
            });

            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
            }).draw();

            table.column(4).data().unique().sort().each(function (d, j) {
                var text = $('<div>').html(d).text();
                if (text) {
                    $('#filterUploader').append('<option value="' + text + '">' + text + '</option>');
                }
            });

            $('#filterJenis').on('change', function () {
                table.column(1).search(this.value).draw();
            });

            $('#filterUploader').on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                table.column(4).search(val ? '^' + val + '$' : '', true, false).draw();
            });

            $('#filterStatus').on('change', function () {
                table.column(5).search(this.value).draw();
            });
        } catch (e) {
            console.error('DataTable initialization error:', e);
        }

        // Auto-show reminder for Pengawas without Pakta
        <?php if ($is_pengawas && !$has_pakta_integritas): ?>
            setTimeout(function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Reminder: Pakta Integritas',
                    text: 'Anda belum membuat Pakta Integritas. Setiap pengawas diwajibkan membuat Pakta Integritas sebagai komitmen dalam pelaksanaan ujian.',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-shield-check me-1"></i> Buat Sekarang',
                    cancelButtonText: 'Nanti Saja',
                    allowOutsideClick: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.value === true) {
                        Swal.close();
                        setTimeout(function () {
                            var modalEl = document.getElementById('modalPaktaIntegritas');
                            if (modalEl) {
                                var paktaModal = new bootstrap.Modal(modalEl);
                                paktaModal.show();
                            }
                        }, 300);
                    }
                });
            }, 1000);
        <?php endif; ?>

        // Upload type buttons


        // Select2
        $('.select2').select2({ theme: 'bootstrap4', dropdownParent: $('#modalBeritaAcara') });
        $('.select2-siswa').select2({ theme: 'bootstrap4', dropdownParent: $('#modalBeritaAcara') });

        // Store mapel/ruang/sesi names
        $('#selectMapel').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            $('#inputMapelNama').val(selectedOption.data('nama'));
            alasanTerpilih = {};
            muatRuangSesi();
        });

        $('input[name="waktu_ujian"]').on('change', function () {
            alasanTerpilih = {};
            muatMapelUjian();
        });

        $('#selectRuang').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            $('#inputRuangNama').val(selectedOption.data('nama'));
            alasanTerpilih = {};
            muatSesiRuang();
            muatSiswaRuang();
        });

        $('#selectSesi').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            $('#inputSesiNama').val(selectedOption.data('nama'));
            alasanTerpilih = {};
            muatSiswaRuang();
        });

        // Daftar mata pelajaran sesuai tanggal ujian
        function muatMapelUjian() {
            var tanggal = $('input[name="waktu_ujian"]').val();
            var selMapel = $('#selectMapel');
            var sebelumnya = selMapel.val();

            selMapel.empty().html('<option value="">Pilih Mata Pelajaran</option>');
            $('#inputMapelNama').val('');

            if (!tanggal) {
                $('#info-mapel').html('<i class="bi bi-info-circle me-1"></i>Pilih <b>Tanggal Ujian</b> terlebih dahulu.');
                return;
            }

            $('#info-mapel').html('<i class="bi bi-hourglass-split me-1"></i>Memuat mata pelajaran...');

            $.get(base_url + 'laporan/get_mapel_ujian', { tanggal: tanggal }, function (res) {
                if (!res || !res.status) {
                    $('#info-mapel').html('<i class="bi bi-exclamation-triangle text-danger me-1"></i>' + ((res && res.msg) || 'Gagal memuat mata pelajaran.'));
                    return;
                }

                var opsi = '<option value="">Pilih Mata Pelajaran</option>';
                (res.mapel || []).forEach(function (m) {
                    opsi += '<option value="' + m.id_mapel + '" data-nama="' + $('<div>').text(m.nama_mapel).html() + '">'
                        + $('<div>').text(m.nama_mapel + (m.kode ? ' (' + m.kode + ')' : '') + ' - ' + m.jml_jadwal + ' jadwal').html()
                        + '</option>';
                });
                selMapel.html(opsi);

                if (sebelumnya && selMapel.find('option[value="' + sebelumnya + '"]').length) {
                    selMapel.val(sebelumnya).trigger('change');
                } else {
                    $('#info-mapel').html((res.mapel && res.mapel.length)
                        ? '<i class="bi bi-check-circle text-success me-1"></i>' + res.mapel.length + ' mata pelajaran memiliki jadwal pada tanggal ini.'
                        : '<i class="bi bi-exclamation-triangle text-warning me-1"></i>Tidak ada mata pelajaran yang memiliki jadwal pada tanggal ini.');
                }
            }, 'json').fail(function () {
                $('#info-mapel').html('<i class="bi bi-exclamation-triangle text-danger me-1"></i>Gagal memuat daftar mata pelajaran.');
            });
        }

        // Daftar ruang + sesi sesuai jadwal mata pelajaran & tanggal
        var daftarRuangSesi = [];

        function muatRuangSesi() {
            var mapel = $('#selectMapel').val();
            var tanggal = $('input[name="waktu_ujian"]').val();
            var selRuang = $('#selectRuang');
            var selSesi = $('#selectSesi');

            daftarRuangSesi = [];
            selRuang.empty().html('<option value="">Pilih Ruang</option>');
            selSesi.empty().html('<option value="">Pilih Sesi</option>');
            $('#inputRuangNama').val('');
            $('#inputSesiNama').val('');

            if (!mapel || !tanggal) {
                $('#info-ruang-sesi').html('<i class="bi bi-info-circle me-1"></i>Pilih <b>Mata Pelajaran</b> dan <b>Tanggal Ujian</b> terlebih dahulu.');
                return;
            }

            $('#info-ruang-sesi').html('<i class="bi bi-hourglass-split me-1"></i>Memuat ruang & sesi yang memiliki jadwal...');

            $.get(base_url + 'laporan/get_ruang_sesi', { mapel: mapel, tanggal: tanggal }, function (res) {
                if (!res || !res.status) {
                    $('#info-ruang-sesi').html('<i class="bi bi-exclamation-triangle text-danger me-1"></i>' + ((res && res.msg) || 'Gagal memuat data ruang.'));
                    return;
                }

                daftarRuangSesi = res.ruang || [];
                var grup = {};
                daftarRuangSesi.forEach(function (r) {
                    if (!grup[r.id_ruang]) {
                        grup[r.id_ruang] = { nama: r.nama_ruang, kode: r.kode_ruang, total: 0, sesis: [] };
                    }
                    grup[r.id_ruang].sesis.push(r);
                    grup[r.id_ruang].total += r.jml_peserta;
                });

                var opsi = '<option value="">Pilih Ruang</option>';
                Object.keys(grup).forEach(function (id) {
                    var g = grup[id];
                    opsi += '<option value="' + id + '" data-nama="' + $('<div>').text(g.nama).html() + '">'
                        + $('<div>').text(g.nama + (g.kode ? ' (' + g.kode + ')' : '') + ' - ' + g.total + ' peserta').html()
                        + '</option>';
                });
                selRuang.html(opsi);

                if (daftarRuangSesi.length === 0) {
                    $('#info-ruang-sesi').html('<i class="bi bi-exclamation-triangle text-warning me-1"></i>' + (res.pesan || 'Tidak ada ruang yang memiliki jadwal pada mata pelajaran & tanggal ini.'));
                } else {
                    $('#info-ruang-sesi').html('<i class="bi bi-check-circle text-success me-1"></i>' + daftarRuangSesi.length + ' sesi ujian tersedia untuk mapel & tanggal ini.');
                }
            }, 'json').fail(function () {
                $('#info-ruang-sesi').html('<i class="bi bi-exclamation-triangle text-danger me-1"></i>Gagal memuat data ruang.');
            });
        }

        function muatSesiRuang() {
            var ruang = $('#selectRuang').val();
            var selSesi = $('#selectSesi');
            selSesi.empty().html('<option value="">Pilih Sesi</option>');
            $('#inputSesiNama').val('');

            if (!ruang) return;

            var opsi = '<option value="">Pilih Sesi</option>';
            daftarRuangSesi.filter(function (r) { return String(r.id_ruang) === String(ruang); })
                .forEach(function (r) {
                    opsi += '<option value="' + r.id_sesi + '" data-nama="' + $('<div>').text(r.nama_sesi).html() + '">'
                        + $('<div>').text(r.nama_sesi + (r.kode_sesi ? ' (' + r.kode_sesi + ')' : '') + ' - ' + r.jml_peserta + ' peserta').html()
                        + '</option>';
                });
            selSesi.html(opsi);

            // Isi otomatis jam pelaksanaan dari waktu sesi
            if (!ruang) return;
            var sesiTerpilih = daftarRuangSesi.filter(function (r) {
                return String(r.id_ruang) === String(ruang);
            });
            $('input[name="jam_mulai"]').val(sesiTerpilih[0] ? (sesiTerpilih[0].waktu_mulai || '') : '');
            $('input[name="jam_selesai"]').val(sesiTerpilih[0] ? (sesiTerpilih[0].waktu_akhir || '') : '');
        }

        // Daftar siswa tidak hadir berubah -> perbarui tabel alasan & angka kehadiran
        $('#selectSiswaTidakHadir').on('change', function () {
            hitungKehadiran();
            renderTabelAlasan();
            ringkasAlasan();
        });

        // Alasan / keterangan per siswa
        $(document).on('change', '.alasan-siswa', function () {
            var $tr = $(this).closest('tr');
            var perluKeterangan = ($(this).val() === 'Lainnya');
            $tr.find('.keterangan-siswa').prop('required', perluKeterangan);
            if (perluKeterangan) {
                $tr.find('.keterangan-siswa').addClass('is-invalid');
            } else {
                $tr.find('.keterangan-siswa').removeClass('is-invalid');
            }
        });

        $(document).on('change', '.alasan-siswa, .keterangan-siswa', function () {
            var id = $(this).closest('tr').data('siswa');
            if (!id) return;
            var row = $('#body-tabel-alasan tr').filter(function () { return $(this).data('siswa') == id; });
            alasanTerpilih[id] = {
                alasan: row.find('.alasan-siswa').val() || 'Tanpa Berita',
                keterangan: row.find('.keterangan-siswa').val() || ''
            };
            ringkasAlasan();
        });

        // Image preview and compress
        $(document).on('change', '.foto-input', function (e) {
            var fileInput = this;
            var previewId = $(this).data('preview');
            var container = $(this).closest('.upload-box');
            var file = this.files[0];
            
            if (file && file.type.match(/image.*/)) {
                // Show loading state if needed
                container.find('.preview-placeholder p').text('Compressing...');
                
                var reader = new FileReader();
                reader.onload = function (readerEvent) {
                    var img = new Image();
                    img.onload = function () {
                        var canvas = document.createElement('canvas');
                        var max_size = 1280;
                        var width = img.width;
                        var height = img.height;

                        if (width > height) {
                            if (width > max_size) {
                                height = Math.round(height *= max_size / width);
                                width = max_size;
                            }
                        } else {
                            if (height > max_size) {
                                width = Math.round(width *= max_size / height);
                                height = max_size;
                            }
                        }
                        
                        canvas.width = width;
                        canvas.height = height;
                        var ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        
                        var dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                        
                        container.find('.preview-placeholder').addClass('d-none');
                        container.find('.preview-placeholder p').text('Upload Foto *');
                        container.find('img').removeClass('d-none').attr('src', dataUrl);
                        
                        // Replace the file in the input with the compressed blob
                        canvas.toBlob(function(blob) {
                            var newFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                                type: 'image/jpeg', 
                                lastModified: Date.now()
                            });
                            var dataTransfer = new DataTransfer();
                            dataTransfer.items.add(newFile);
                            fileInput.files = dataTransfer.files;
                        }, 'image/jpeg', 0.7);
                    }
                    img.src = readerEvent.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                container.find('.preview-placeholder').removeClass('d-none');
                container.find('.preview-placeholder p').text('Upload Foto *');
                container.find('img').addClass('d-none').attr('src', '');
            }
        });

        // ============================================================
        // Kehadiran: peserta dimuat dari alokasi ruang + sesi
        // ============================================================
        var daftarSiswaRuang = {};
        var alasanTerpilih = {};   // { id_siswa: {alasan, keterangan} }

        var ALASAN_LIST = ['Sakit', 'Ijin', 'Tanpa Berita', 'Lainnya'];

        function hitungKehadiran() {
            var total = parseInt($('#jmlSeharusnya').val()) || 0;
            var absent = $('#selectSiswaTidakHadir').val() ? $('#selectSiswaTidakHadir').val().length : 0;
            $('#jmlTidakHadir').val(absent);
            $('#jmlHadir').val(Math.max(0, total - absent));
        }

        function ringkasAlasan() {
            var hitung = { 'Sakit': 0, 'Ijin': 0, 'Tanpa Berita': 0, 'Lainnya': 0 };
            Object.keys(alasanTerpilih).forEach(function (id) {
                var a = alasanTerpilih[id].alasan || 'Tanpa Berita';
                if (hitung[a] === undefined) hitung[a] = 0;
                hitung[a]++;
            });

            var html = '';
            var warna = { 'Sakit': 'danger', 'Ijin': 'warning', 'Tanpa Berita': 'secondary', 'Lainnya': 'info' };
            Object.keys(hitung).forEach(function (a) {
                if (hitung[a] > 0) {
                    html += '<span class="badge bg-' + warna[a] + '">' + a + ': ' + hitung[a] + ' orang</span>';
                }
            });
            $('#ringkasan-alasan').html(html);
            $('#ringkasan-alasan-wrap').toggle(html !== '');
        }

        function renderTabelAlasan() {
            var ids = $('#selectSiswaTidakHadir').val() || [];
            var tbody = $('#body-tabel-alasan');
            tbody.empty();

            if (ids.length === 0) {
                $('#wrap-tabel-alasan').hide();
                return;
            }

            ids.forEach(function (id, i) {
                var s = daftarSiswaRuang[id] || { nama: 'Siswa #' + id, no_peserta: '-', kelas: '-' };
                var awal = alasanTerpilih[id] || { alasan: 'Tanpa Berita', keterangan: '' };

                var tr = $('<tr>');
                tr.append('<td class="text-center">' + (i + 1) + '</td>');
                tr.append('<td>' + $('<div>').text(s.nama).html() + '</td>');
                tr.append('<td class="text-center">' + $('<div>').text(s.no_peserta).html() + '</td>');
                tr.append('<td class="text-center">' + $('<div>').text(s.kelas).html() + '</td>');

                var tdAlasan = $('<td>');
                var selAlasan = $('<select class="form-select form-select-sm alasan-siswa" name="absen[' + id + '][alasan]">');
                ALASAN_LIST.forEach(function (a) {
                    selAlasan.append($('<option>').attr('value', a).text(a));
                });
                selAlasan.val(awal.alasan || 'Tanpa Berita');
                tdAlasan.append(selAlasan);
                tr.append(tdAlasan);

                var tdKet = $('<td>');
                var inpKet = $('<input type="text" class="form-control form-control-sm keterangan-siswa" name="absen[' + id + '][keterangan]">');
                inpKet.val(awal.keterangan || '');
                tdKet.append(inpKet);
                tr.append(tdKet);

                tr.data('siswa', id);
                tbody.append(tr);
            });

            $('#wrap-tabel-alasan').show();
        }

        function muatSiswaRuang(callback) {
            var ruang = $('#selectRuang').val();
            var sesi = $('#selectSesi').val();

            if (!ruang || !sesi) {
                daftarSiswaRuang = {};
                $('#selectSiswaTidakHadir').empty();
                $('#info-siswa-ruang').removeClass('alert-danger').addClass('alert-light')
                    .html('<i class="bi bi-info-circle text-primary me-1"></i><span class="text-muted">Pilih <b>Ruang</b> dan <b>Sesi</b> terlebih dahulu untuk memuat daftar peserta pada sesi tersebut.</span>');
                hitungKehadiran();
                renderTabelAlasan();
                if (typeof callback === 'function') callback(false);
                return;
            }

            $('#info-siswa-ruang').removeClass('alert-danger').addClass('alert-light')
                .html('<i class="bi bi-hourglass-split text-primary me-1"></i><span class="text-muted">Memuat daftar peserta...</span>');

            $.get(base_url + 'laporan/get_siswa_ruang', { ruang: ruang, sesi: sesi }, function (res) {
                if (!res || !res.status) {
                    daftarSiswaRuang = {};
                    $('#selectSiswaTidakHadir').empty();
                    $('#info-siswa-ruang').removeClass('alert-light').addClass('alert-danger')
                        .html('<i class="bi bi-exclamation-triangle text-danger me-1"></i><span>' + ((res && res.msg) || 'Gagal memuat data peserta.') + '</span>');
                    hitungKehadiran();
                    renderTabelAlasan();
                    if (typeof callback === 'function') callback(false);
                    return;
                }

                daftarSiswaRuang = {};
                var options = '';
                (res.siswa || []).forEach(function (s) {
                    daftarSiswaRuang[s.id] = s;
                    options += '<option value="' + s.id + '">' + $('<div>').text(s.nama + ' - ' + s.no_peserta + (s.kelas ? ' (' + s.kelas + ')' : '')).html() + '</option>';
                });

                var sel = $('#selectSiswaTidakHadir');
                var sebelumnya = sel.val() || [];
                sel.empty().html(options);
                if (sebelumnya.length > 0) {
                    var valid = sebelumnya.filter(function (id) { return daftarSiswaRuang[id]; });
                    sel.val(valid).trigger('change');
                }

                $('#jmlSeharusnya').val(res.jml_seharusnya || 0);
                $('#info-siswa-ruang').removeClass('alert-danger').addClass('alert-light')
                    .html('<i class="bi bi-people text-primary me-1"></i><span class="text-muted">Tersedia <b>' + (res.jml_seharusnya || 0) + '</b> peserta pada ruang/sesi ini' + ((res.kelas && res.kelas.length) ? ' (kelas: ' + $('<div>').text(res.kelas.join(', ')).html() + ')' : '') + '.</span>');

                hitungKehadiran();
                renderTabelAlasan();
                if (typeof callback === 'function') callback(true);
            }, 'json').fail(function () {
                $('#info-siswa-ruang').removeClass('alert-light').addClass('alert-danger')
                    .html('<i class="bi bi-exclamation-triangle text-danger me-1"></i><span>Gagal memuat daftar peserta.</span>');
                if (typeof callback === 'function') callback(false);
            });
        }

        // Checkbox validation for Pakta Integritas
        $(document).on('change', '#setujuPakta', function() {
            if ($(this).is(':checked')) {
                $('#btnSimpanPakta').prop('disabled', false).removeClass('opacity-50');
            } else {
                $('#btnSimpanPakta').prop('disabled', true).addClass('opacity-50');
            }
        });

        // Form submissions
        $('#formBeritaAcara').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status) {
                        hideModal('modalBeritaAcara');
                        showBootstrapAlert('success', response.msg);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showBootstrapAlert('error', response.msg);
                    }
                }
            });
        });

        $('#formPaktaIntegritas').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.status) {
                        hideModal('modalPaktaIntegritas');
                        showBootstrapAlert('success', response.msg);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showBootstrapAlert('error', response.msg);
                    }
                }
            });
        });

        $('#formUpload').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status) {
                        hideModal('modalUpload');
                        showBootstrapAlert('success', response.msg);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showBootstrapAlert('error', response.msg);
                    }
                }
            });
        });

        // Edit button handler
        $(document).on('click', '.btn-edit', function () {
            var id = $(this).data('id');
            var jenis = $(this).data('jenis');

            // Fetch laporan data
            $.get(base_url + 'laporan/get_laporan/' + id, function (response) {
                if (response.status && response.data) {
                    var data = response.data;
                    var content = response.content;

                    if (jenis == 'Berita Acara') {
                        // Populate Berita Acara form
                        $('#editBeritaAcaraId').val(data.id_laporan);

                        if (content) {
                            $('#selectMapel').val(content.mapel).trigger('change');
                            $('#inputMapelNama').val(content.mapel_nama);
                            $('input[name="waktu_ujian"]').val(content.waktu_ujian);
                            $('input[name="jam_mulai"]').val(content.jam_mulai);
                            $('input[name="jam_selesai"]').val(content.jam_selesai);

                            $('#selectRuang').val(content.ruang).trigger('change');
                            $('#inputRuangNama').val(content.ruang_nama);

                            $('#selectSesi').val(content.sesi).trigger('change');
                            $('#inputSesiNama').val(content.sesi_nama);

                                                        // Pulihkan keterangan alasan ketidakhadiran
                            var alasanSimpan = {};
                            if (content.siswa_tidak_hadir && content.siswa_tidak_hadir.length > 0) {
                                content.siswa_tidak_hadir.forEach(function (s) {
                                    alasanSimpan[s.id] = {
                                        alasan: s.alasan || 'Tanpa Berita',
                                        keterangan: s.keterangan || ''
                                    };
                                });
                            }
                            alasanTerpilih = alasanSimpan;

                            // Muat daftar mata pelajaran, ruang & sesi sesuai jadwal
                            muatMapelUjian();
                            muatSiswaRuang(function () {
                                var ids = Object.keys(alasanSimpan);
                                if (ids.length > 0) {
                                    $('#selectSiswaTidakHadir').val(ids).trigger('change');
                                } else {
                                    hitungKehadiran();
                                    renderTabelAlasan();
                                }
                                ringkasAlasan();
                            });

$('textarea[name="uraian"]').val(content.uraian);
                            $('textarea[name="kendala"]').val(content.kendala);
                            $('textarea[name="solusi"]').val(content.solusi);
                        }

                        // Display existing photo previews
                        var fotoFiles = [data.file, data.file_2, data.file_3];
                        for (var i = 0; i < 3; i++) {
                            var previewId = 'previewFoto' + (i + 1);
                            var container = $('#' + previewId).closest('.upload-box');
                            if (fotoFiles[i]) {
                                container.find('.preview-placeholder').addClass('d-none');
                                container.find('img').removeClass('d-none').attr('src', base_url + 'uploads/laporan/' + fotoFiles[i]);
                            } else {
                                container.find('.preview-placeholder').removeClass('d-none');
                                container.find('img').addClass('d-none').attr('src', '');
                            }
                        }

                        // Change form action to update
                        $('#formBeritaAcara').attr('action', base_url + 'laporan/update_berita_acara');
                        $('#btnSimpanBerita .btn-text').text('Update Berita Acara');
                        // Make photo uploads not required for edit
                        $('#inputFoto1').prop('required', false);

                        showModal('modalBeritaAcara');

                    } else if (jenis == 'Pakta Integritas') {
                        // Populate Pakta Integritas form
                        $('#editPaktaIntegritasId').val(data.id_laporan);

                        if (content) {
                            $('input[name="nama"]').val(content.nama);
                            $('input[name="nip"]').val(content.nip);
                            $('input[name="jabatan"]').val(content.jabatan);
                            $('input[name="instansi"]').val(content.instansi);
                            $('input[name="no_hp"]').val(content.no_hp);
                            $('input[name="email"]').val(content.email);
                            $('input[name="provinsi"]').val(content.provinsi);
                            $('input[name="kota"]').val(content.kota);
                            $('input[name="tempat"]').val(content.tempat);
                            $('input[name="tanggal_pelaksanaan"]').val(content.tanggal_pelaksanaan);
                        }

                        // Change form action to update
                        $('#formPaktaIntegritas').attr('action', base_url + 'laporan/update_pakta_integritas');
                        $('#btnSimpanPakta .btn-text').text('Update Pakta Integritas');

                        showModal('modalPaktaIntegritas');
                    }
                } else {
                    showBootstrapAlert('error', response.msg || 'Gagal memuat data');
                }
            }, 'json').fail(function () {
                showBootstrapAlert('error', 'Terjadi kesalahan saat memuat data');
            });
        });

        // Preview
        $(document).on('click', '.btn-preview', function () {
            var id = $(this).data('id');
            var jenis = $(this).data('jenis');
            var url = '';

            if (jenis == 'Berita Acara') {
                url = base_url + 'laporan/cetak_berita_acara/' + id;
            } else if (jenis == 'Pakta Integritas') {
                url = base_url + 'laporan/cetak_pakta_integritas/' + id;
            } else {
                var file = $(this).data('file');
                url = base_url + 'uploads/laporan/' + file;
            }

            $('#previewFrame').attr('src', url);
            showModal('modalPreview');
        });

        // View Photos
        $(document).on('click', '.btn-photos', function () {
            var files = [
                $(this).data('file1'),
                $(this).data('file2'),
                $(this).data('file3')
            ];
            
            var html = '';
            var hasPhoto = false;
            
            files.forEach(function(file, index) {
                if (file && file !== '') {
                    hasPhoto = true;
                    html += `
                        <div class="col-md-4 text-center">
                            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 15px;">
                                <div class="position-relative">
                                    <img src="${base_url}uploads/laporan/${file}" class="img-fluid" style="height: 200px; width: 100%; object-fit: cover; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                    <div class="position-absolute bottom-0 end-0 p-2">
                                        <button class="btn btn-xs btn-light rounded-circle shadow-sm" onclick="window.open('${base_url}uploads/laporan/${file}', '_blank')">
                                            <i class="bi bi-arrows-fullscreen small"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 py-2 small fw-bold text-muted">
                                    Foto ${index + 1}
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
            
            if (!hasPhoto) {
                html = `
                    <div class="col-12 text-center py-5">
                        <div class="bg-white p-5 rounded-4 shadow-sm mx-auto" style="max-width: 400px;">
                            <i class="bi bi-image fs-1 text-muted opacity-25 d-block mb-3"></i>
                            <h6 class="fw-bold text-muted mb-1">Belum Ada Foto</h6>
                            <p class="small text-muted mb-0">Dokumentasi foto belum diunggah untuk berita acara ini.</p>
                        </div>
                    </div>
                `;
            }
            
            $('#photoContainer').html(html);
            showModal('modalPhotos');
        });

        // Delete
        $(document).on('click', '.btn-delete', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Dokumen?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(base_url + 'laporan/delete', { id_laporan: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        if (res.status) {
                            showBootstrapAlert('success', res.msg);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showBootstrapAlert('error', res.msg);
                        }
                    }, 'json');
                }
            });
        });

        // Approve
        $(document).on('click', '.btn-approve', function () {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Setujui Dokumen?',
                text: 'Dokumen yang sudah disetujui tidak dapat diubah',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(base_url + 'laporan/approve', { id_laporan: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        if (res.status) {
                            showBootstrapAlert('success', res.msg);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showBootstrapAlert('error', res.msg);
                        }
                    }, 'json');
                }
            });
        });

        // Reject
        $(document).on('click', '.btn-reject', function () {
            var id = $(this).data('id');
            $('#rejectId').val(id);
            showModal('modalReject');
        });

        $('#formReject').on('submit', function (e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.status) {
                        hideModal('modalReject');
                        showBootstrapAlert('success', response.msg);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showBootstrapAlert('error', response.msg);
                    }
                }
            });
        });

        // Show reason
        $(document).on('click', '.btn-reason', function () {
            var reason = $(this).data('reason');
            Swal.fire({
                title: 'Alasan Perbaikan',
                text: reason,
                icon: 'info'
            });
        });
    });
</script>
<?= $this->endSection() ?>