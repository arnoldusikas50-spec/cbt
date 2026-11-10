<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
.stat-card-modern {
    transition: transform .2s ease, box-shadow .2s ease;
    border: 1px solid rgba(0,0,0,0.06) !important;
}
.stat-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,0.08) !important;
}
.icon-box-shape {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.nav-tabs-sleek {
    background: #f8fafc;
    padding: 0.35rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    display: inline-flex;
    gap: 0.25rem;
}
.nav-tabs-sleek .nav-link {
    border: none !important;
    border-radius: 8px !important;
    color: #64748b;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.55rem 1.25rem;
    transition: all 0.2s ease;
}
.nav-tabs-sleek .nav-link.active {
    background: #1a237e !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(26, 35, 126, 0.25);
}
.nav-tabs-sleek .nav-link:hover:not(.active) {
    background-color: #e2e8f0;
    color: #1a237e;
}
.table-laporan thead th {
    font-size: 0.72rem;
    letter-spacing: 0.6px;
    font-weight: 700;
    text-transform: uppercase;
    background-color: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 0.75rem 0.6rem;
}
.table-laporan tbody td {
    padding: 0.7rem 0.6rem;
    vertical-align: middle;
}
.pas-form-select {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.85rem;
    padding: 0.35rem 0.75rem;
    background-color: white;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>
                    Manajemen Mata Pelajaran
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Mata Pelajaran</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Sleek Nav Tabs Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <ul class="nav nav-tabs nav-tabs-sleek border-0" id="mapelTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="direktori-tab" data-bs-toggle="tab" href="#tab-direktori" role="tab">
                        <i class="bi bi-journals me-1.5"></i>Direktori Mapel
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="kelompok-tab" data-bs-toggle="tab" href="#tab-kelompok" role="tab">
                        <i class="bi bi-folder2-open me-1.5"></i>Kelompok Mapel
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="pembagian-tab" data-bs-toggle="tab" href="#tab-pembagian" role="tab">
                        <i class="bi bi-diagram-3-fill me-1.5"></i>Pembagian per Kelas
                    </a>
                </li>
            </ul>

            <div class="text-muted fw-semibold small">
                <i class="bi bi-info-circle me-1 text-primary"></i> Kelola mapel, kelompok, &amp; alokasi guru pengampu per kelas
            </div>
        </div>

        <div class="tab-content" id="mapelTabContent">

            <!-- TAB 1: DIREKTORI MATA PELAJARAN -->
            <div class="tab-pane fade show active" id="tab-direktori" role="tabpanel">
                <div class="card card-primary card-outline shadow-sm mb-4">
                    <div class="card-header border-0 shadow-none d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                        <h3 class="card-title fw-bold text-primary mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-journal-text me-1"></i>Daftar Mata Pelajaran
                        </h3>
                        <div class="card-tools ms-auto d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-sync-dapodik" data-entitas="mapel">
                                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Sync Dapodik
                            </button>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#createMapelModal" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Mapel
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <!-- Filter Bar -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 bg-light p-2.5 rounded-3 border">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <button id="hapusterpilih" onclick="bulk_delete()" type="button" class="btn btn-sm btn-danger rounded-pill px-3 d-none">
                                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                                </button>
                                <div class="d-flex align-items-center">
                                    <label class="small fw-bold text-muted me-2 mb-0 text-uppercase" style="font-size: .7rem;">Kategori Kumer:</label>
                                    <select id="kumer-filter-mapel" class="pas-form-select">
                                        <option value="all">Semua Kategori</option>
                                        <option value="wajib">Mapel Wajib (Umum)</option>
                                        <option value="pilihan">Mapel Pilihan (Fase F)</option>
                                        <option value="mulok">Muatan Lokal (Mulok)</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="small fw-bold text-muted me-2 mb-0 text-uppercase" style="font-size: .7rem;">Status Dapodik:</label>
                                    <select id="dapodik-filter-mapel" class="pas-form-select">
                                        <option value="all">Semua Data</option>
                                        <option value="synced">Terhubung Dapodik</option>
                                        <option value="local">Hanya Data Lokal</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <form id="bulk">
                            <?= csrf_field() ?>
                            <div class="table-responsive">
                                <table id="tableMapel" class="table table-bordered table-hover align-middle mb-0 w-100 table-laporan">
                                    <thead>
                                        <tr>
                                            <th class="ps-3 text-center" width="35">
                                                <input type="checkbox" class="select_all form-check-input shadow-none">
                                            </th>
                                            <th class="text-center" width="55">Urut</th>
                                            <th>Mata Pelajaran</th>
                                            <th class="text-center" width="110">Kode</th>
                                            <th class="text-center" width="110">Kelompok</th>
                                            <th class="text-center" width="140">Kategori Kurmer</th>
                                            <th class="text-center" width="90">Status</th>
                                            <th class="text-center" width="110">Aksi</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 2: KELOMPOK & SUB KELOMPOK -->
            <div class="tab-pane fade" id="tab-kelompok" role="tabpanel">
                <div class="card card-secondary card-outline shadow-sm mb-4">
                    <div class="card-header border-0 shadow-none py-3">
                        <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-folder2-open me-1 text-secondary"></i>Kelompok &amp; Sub-Kelompok Mapel
                        </h3>
                    </div>
                    <div class="card-body p-3">
                        <?php
                        $kelompok_mapel = $kelompok_mapel ?? [];
                        $totalKelompok = count(array_filter((array) $kelompok_mapel, fn($km) => isset($km->id_parent) && $km->id_parent == 0));
                        ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border rounded-3 h-100 shadow-sm">
                                    <div class="card-header border-bottom bg-light py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-bold text-dark small"><i class="bi bi-folder-fill text-primary me-1"></i>Kelompok Utama (<?= $totalKelompok ?>)</h6>
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#editKelompokModal" class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="bi bi-plus-lg me-1"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive mb-0">
                                            <table id="tableKelompok" class="table table-bordered table-hover align-middle mb-0 w-100 table-laporan">
                                                <thead>
                                                    <tr>
                                                        <th>Kategori</th>
                                                        <th class="text-center" width="75">Kode</th>
                                                        <th>Nama Kelompok</th>
                                                        <th class="text-center" width="90">Aksi</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border rounded-3 h-100 shadow-sm">
                                    <div class="card-header border-bottom bg-light py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-bold text-dark small"><i class="bi bi-folder2-open text-info me-1"></i>Sub-Kelompok</h6>
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#editSubKelompokModal" class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="bi bi-plus-lg me-1"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive mb-0">
                                            <table id="tableSubKelompok" class="table table-bordered table-hover align-middle mb-0 w-100 table-laporan">
                                                <thead>
                                                    <tr>
                                                        <th class="ps-3 text-center" width="75">Kode</th>
                                                        <th>Nama Sub</th>
                                                        <th>Induk</th>
                                                        <th class="text-center" width="90">Aksi</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: PEMBAGIAN MAPEL PER KELAS (KURIKULUM MERDEKA) -->
            <div class="tab-pane fade" id="tab-pembagian" role="tabpanel">
                <div class="card card-info card-outline shadow-sm mb-4">
                    <div class="card-header border-0 shadow-none d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                        <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-diagram-3-fill me-1 text-info"></i>Pembagian Mapel &amp; Guru per Kelas
                        </h3>
                        <div class="card-tools ms-auto">
                            <div class="d-flex align-items-center gap-2">
                                <label class="fw-bold text-muted mb-0 text-nowrap small text-uppercase"><i class="bi bi-easel2-fill text-primary me-1"></i> Pilih Rombel:</label>
                                <select id="select-kelas-pembagian" class="form-select form-select-sm fw-bold border-primary text-primary rounded-3" style="min-width: 190px;">
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($kelases as $k): ?>
                                        <option value="<?= $k->id_kelas ?>"><?= esc($k->nama_kelas) ?> (Level <?= esc($k->level_id) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3">

                        <div id="pembagian-empty-state" class="text-center py-5">
                            <i class="bi bi-arrow-up-circle-fill text-primary display-4 d-block mb-2 opacity-50"></i>
                            <h6 class="fw-bold text-dark mb-1">Pilih Kelas Terlebih Dahulu</h6>
                            <p class="text-muted small mb-0">Pilih salah satu rombel/kelas di kanan atas untuk mengelola alokasi mapel Wajib &amp; Mapel Pilihan.</p>
                        </div>

                        <div id="pembagian-content" class="d-none">

                            <!-- Stats Ringkasan Rombel -->
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <div class="p-2 px-3 bg-light rounded-3 border">
                                        <div class="small text-muted fw-bold text-uppercase" style="font-size: .68rem;">MAPEL WAJIB (UMUM)</div>
                                        <div class="fs-5 fw-bold text-primary" id="cnt-wajib">0 mapel</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 px-3 bg-light rounded-3 border border-warning">
                                        <div class="small text-warning fw-bold text-uppercase" style="font-size: .68rem;">MAPEL PILIHAN (FASE F)</div>
                                        <div class="fs-5 fw-bold text-warning" id="cnt-pilihan">0 mapel</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 px-3 bg-light rounded-3 border border-success">
                                        <div class="small text-success fw-bold text-uppercase" style="font-size: .68rem;">DIALOKASIKAN GURU</div>
                                        <div class="fs-5 fw-bold text-success" id="cnt-assigned">0 mapel</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Mapel Wajib -->
                            <div class="card border mb-3 rounded-3 shadow-sm">
                                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-dark small">
                                        <span class="badge bg-primary me-2">WAJIB</span> Mata Pelajaran Umum / Wajib Rombel
                                    </h6>
                                    <span class="text-muted small">Diikuti oleh seluruh siswa rombel</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle mb-0 w-100 table-laporan" id="tbl-wajib">
                                            <thead>
                                                <tr>
                                                    <th width="75" class="text-center">Kode</th>
                                                    <th>Nama Mata Pelajaran</th>
                                                    <th class="text-center" width="110">Kelompok</th>
                                                    <th>Status Alokasi &amp; Guru Pengampu</th>
                                                    <th width="90" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Mapel Pilihan (Kurikulum Merdeka) -->
                            <div class="card border mb-3 rounded-3 border-start border-warning border-4 shadow-sm">
                                <div class="card-header bg-warning bg-opacity-10 border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-dark small">
                                        <span class="badge bg-warning text-dark me-2">PILIHAN KURMER</span> Mata Pelajaran Pilihan (Kurikulum Merdeka Fase F)
                                    </h6>
                                    <span class="text-muted small">Mapel pilihan kelompok MIPA/IPS/Bahasa/Vokasi</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle mb-0 w-100 table-laporan" id="tbl-pilihan">
                                            <thead>
                                                <tr>
                                                    <th width="75" class="text-center">Kode</th>
                                                    <th>Nama Mata Pelajaran Pilihan</th>
                                                    <th class="text-center" width="110">Kelompok</th>
                                                    <th>Status Alokasi &amp; Guru Pengampu</th>
                                                    <th width="90" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Muatan Lokal -->
                            <div class="card border mb-3 rounded-3 shadow-sm">
                                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold mb-0 text-dark small">
                                        <span class="badge bg-info me-2">MULOK</span> Muatan Lokal / Ekstrakurikuler Wajib
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle mb-0 w-100 table-laporan" id="tbl-mulok">
                                            <thead>
                                                <tr>
                                                    <th width="75" class="text-center">Kode</th>
                                                    <th>Nama Muatan Lokal</th>
                                                    <th class="text-center" width="110">Kelompok</th>
                                                    <th>Status Alokasi &amp; Guru Pengampu</th>
                                                    <th width="90" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Modal Kelompok Utama -->
<div class="modal fade" id="editKelompokModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="create-kelompok">
                <?= csrf_field() ?>
                <div class="modal-header border-0 px-4 py-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold mb-0"><i class="bi bi-folder-plus me-2"></i>Kelompok Utama</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="id_kel_mapel" name="id_kel_mapel">
                    <input type="hidden" name="id_parent" value="0">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Kode Kelompok <span class="text-danger">*</span></label>
                        <input type="text" id="createkodekel" name="kode_kel_mapel" class="form-control rounded-3" required placeholder="Contoh: A, B, C">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Nama Kelompok <span class="text-danger">*</span></label>
                        <input type="text" id="createnamakel" name="nama_kel_mapel" class="form-control rounded-3" required placeholder="Contoh: Kelompok Umum">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Kategori <span class="text-danger">*</span></label>
                        <select id="kategori" name="kategori" class="form-select rounded-3" required>
                            <?php foreach ($kategori as $kat): ?>
                                <option value="<?= $kat ?>"><?= $kat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sub Kelompok -->
<div class="modal fade" id="editSubKelompokModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="create-sub-kelompok">
                <?= csrf_field() ?>
                <div class="modal-header border-0 px-4 py-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold mb-0"><i class="bi bi-folder-plus me-2"></i>Sub Kelompok Mata Pelajaran</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="id_kel_sub" name="id_kel_mapel">
                    <input type="hidden" id="kategori_sub" name="kategori">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Kode Sub Kelompok <span class="text-danger">*</span></label>
                        <input type="text" id="createkodesub" name="kode_kel_mapel" class="form-control rounded-3" required placeholder="Contoh: A1, B1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Nama Sub Kelompok <span class="text-danger">*</span></label>
                        <input type="text" id="createnamasub" name="nama_kel_mapel" class="form-control rounded-3" required placeholder="Contoh: Peminatan IPA">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Kelompok Utama <span class="text-danger">*</span></label>
                        <select id="id_parent_sub" name="id_parent" class="form-select rounded-3" required>
                            <option value="">Pilih Kelompok Utama</option>
                            <?php foreach ((array) $kelompok_mapel as $km): ?>
                                <?php if (isset($km->id_parent) && $km->id_parent == 0): ?>
                                    <option value="<?= $km->id_kel_mapel ?>" data-kategori="<?= esc($km->kategori ?? '') ?>"><?= $km->nama_kel_mapel ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Create / Edit Mapel -->
<div class="modal fade" id="createMapelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="form-create-mapel">
                <?= csrf_field() ?>
                <div class="modal-header border-0 px-4 py-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold mb-0" id="mapelModalTitle"><i class="bi bi-journal-plus me-2"></i>Tambah Mata Pelajaran</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_id_mapel" name="id_mapel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-1">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                            <input type="text" id="nama_mapel" name="nama_mapel" class="form-control rounded-3" required placeholder="Nama Mapel (misal: Bahasa Indonesia)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-1">Kode Mapel <span class="text-danger">*</span></label>
                            <input type="text" id="kode_mapel" name="kode_mapel" class="form-control rounded-3" required placeholder="Kode singkat (misal: BINDO)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-1">Urutan Rapor</label>
                            <input type="number" id="urutan_tampil" name="urutan_tampil" class="form-control rounded-3" value="1" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-1">Kelompok Mapel</label>
                            <select id="mapel_kelompok" name="kelompok" class="form-select rounded-3">
                                <option value="">Pilih Kelompok Mapel</option>
                                <?php foreach ((array) $kelompok as $klpId => $klpName): ?>
                                    <option value="<?= $klpId ?>"><?= $klpName ?> (<?= $klpId ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase mb-1">Status Mapel</label>
                            <select id="mapel_status" name="status" class="form-select rounded-3">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-save me-1"></i> Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Alokasi Mapel & Guru Kelas -->
<div class="modal fade" id="alokasiMapelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="form-alokasi-mapel">
                <?= csrf_field() ?>
                <input type="hidden" id="alokasi_id_kelas" name="id_kelas">
                <input type="hidden" id="alokasi_id_mapel" name="id_mapel">
                <input type="hidden" id="alokasi_is_assign" name="is_assign" value="1">
                <div class="modal-header border-0 px-4 py-3 bg-primary text-white">
                    <h6 class="modal-title fw-bold mb-0" id="alokasiModalTitle"><i class="bi bi-person-plus-fill me-2"></i>Alokasikan Guru Pengampu</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Mata Pelajaran</label>
                        <input type="text" id="alokasi_nama_mapel" class="form-control bg-light rounded-3" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-1">Pilih Guru Pengampu <span class="text-danger">*</span></label>
                        <select id="alokasi_id_guru" name="id_guru" class="form-select rounded-3" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($gurus as $g): ?>
                                <option value="<?= $g->id_guru ?>"><?= esc($g->nama_guru) ?> (<?= esc($g->kode_guru ?? '-') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-save me-1"></i> Simpan Alokasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var table;
    var jsonkelompokMapel = <?= json_encode((array) $kelompok_mapel) ?>;

    $(document).ready(function () {
        var dt_lang_id = {
            processing: "Sedang memproses...",
            search: "Cari Mapel:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
            zeroRecords: "Tidak ditemukan data yang sesuai",
            paginate: {
                first: '<i class="bi bi-chevron-double-left"></i>',
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
                last: '<i class="bi bi-chevron-double-right"></i>'
            }
        };

        // Table Kelompok Utama
        $("#tableKelompok").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('datamapel/getdatakelompok') ?>",
                type: "POST",
                data: { [CSRF_NAME]: CSRF_HASH }
            },
            dom: "t",
            columns: [
                { data: "kategori" },
                { data: "kode_kel_mapel", className: "text-center" },
                { data: "nama_kel_mapel" },
                { data: null, className: "text-center", orderable: false }
            ],
            columnDefs: [
                {
                    targets: 3,
                    render: function (data, type, row) {
                        return `<div class="btn-action-group justify-content-center">
                            <button type="button" class="btn btn-action btn-action-edit btn-edit-kel"
                                data-id="${row.id_kel_mapel}" data-nama="${row.nama_kel_mapel}" data-kode="${row.kode_kel_mapel}" data-kategori="${row.kategori}"
                                data-bs-toggle="modal" data-bs-target="#editKelompokModal" data-bs-toggle="tooltip" title="Edit Kelompok">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button type="button" onclick="hapusKelompok(${row.id_kel_mapel})" class="btn btn-action btn-action-delete" data-bs-toggle="tooltip" title="Hapus Kelompok">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>`;
                    }
                }
            ],
            language: dt_lang_id
        });

        // Table Sub Kelompok
        $("#tableSubKelompok").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('datamapel/getdatasubkelompok') ?>",
                type: "POST",
                data: { [CSRF_NAME]: CSRF_HASH }
            },
            dom: "t",
            columns: [
                { data: "kode_kel_mapel", className: "text-center" },
                { data: "nama_kel_mapel" },
                { data: "id_parent" },
                { data: null, className: "text-center", orderable: false }
            ],
            columnDefs: [
                {
                    targets: 2,
                    render: function (data) {
                        var kel = jsonkelompokMapel[data];
                        return kel ? kel.nama_kel_mapel : '-';
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, row) {
                        return `<div class="btn-action-group justify-content-center">
                            <button type="button" class="btn btn-action btn-action-edit btn-edit-sub"
                                data-id="${row.id_kel_mapel}" data-nama="${row.nama_kel_mapel}" data-kode="${row.kode_kel_mapel}" data-utama="${row.id_parent}" data-kategori="${row.kategori}"
                                data-bs-toggle="modal" data-bs-target="#editSubKelompokModal" data-bs-toggle="tooltip" title="Edit Sub-Kelompok">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button type="button" onclick="hapusKelompok(${row.id_kel_mapel})" class="btn btn-action btn-action-delete" data-bs-toggle="tooltip" title="Hapus Sub-Kelompok">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>`;
                    }
                }
            ],
            language: dt_lang_id
        });

        // Table Mapel Main
        table = $("#tableMapel").DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "<?= base_url('datamapel/read') ?>",
                type: "POST",
                data: function (d) {
                    d[CSRF_NAME] = CSRF_HASH;
                    d.dapodik_filter = $('#dapodik-filter-mapel').val();
                    d.kumer_filter = $('#kumer-filter-mapel').val();
                }
            },
            columns: [
                { data: "id_mapel", className: "text-center" },
                { data: "urutan_tampil", className: "text-center" },
                { data: "nama_mapel" },
                { data: "kode", className: "text-center" },
                { data: "kelompok", className: "text-center" },
                { data: "kumer_type", className: "text-center" },
                { data: "status", className: "text-center" },
                { data: null, className: "text-center", orderable: false }
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    render: function (data) {
                        return `<input type="checkbox" name="checked[]" class="check_item form-check-input shadow-none" value="${data}">`;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, row) {
                        var dapoBadge = row.is_dapo
                            ? `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-2" title="ID Dapodik: ${row.dapo_id}"><i class="bi bi-cloud-check-fill me-1"></i>Dapodik</span>`
                            : `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 ms-2"><i class="bi bi-hdd me-1"></i>Lokal</span>`;
                        return `<div class="fw-bold text-dark">${data} ${dapoBadge}</div>`;
                    }
                },
                {
                    targets: 5,
                    render: function (data) {
                        if (data === 'PILIHAN') {
                            return `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1"><i class="bi bi-stars me-1"></i>PILIHAN (Fase F)</span>`;
                        } else if (data === 'MULOK') {
                            return `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1"><i class="bi bi-tree me-1"></i>MULOK</span>`;
                        }
                        return `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1"><i class="bi bi-check-circle me-1"></i>WAJIB</span>`;
                    }
                },
                {
                    targets: 6,
                    render: function (data, type, row) {
                        var isChecked = (data == 1 || data == '1') ? 'checked' : '';
                        return `<div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input toggle-status-mapel" type="checkbox" data-id="${row.id_mapel}" data-status="${data}" ${isChecked}>
                        </div>`;
                    }
                },
                {
                    targets: 7,
                    render: function (data, type, row) {
                        return `<div class="btn-action-group justify-content-center">
                            <button type="button" class="btn btn-action btn-action-edit btn-edit-mapel"
                                data-id="${row.id_mapel}" data-nama="${row.nama_mapel}" data-kode="${row.kode}" data-kelompok="${row.kelompok}" data-urutan="${row.urutan_tampil}" data-status="${row.status}"
                                data-bs-toggle="modal" data-bs-target="#createMapelModal" data-bs-toggle="tooltip" title="Edit Mapel">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button type="button" class="btn btn-action btn-action-delete" onclick="hapusMapel(${row.id_mapel})" data-bs-toggle="tooltip" title="Hapus Mapel">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>`;
                    }
                }
            ],
            language: dt_lang_id
        });

        // Filter Handlers
        $('#dapodik-filter-mapel, #kumer-filter-mapel').on('change', function () {
            table.ajax.reload();
        });

        // Checkbox Handlers
        $('.select_all').on('click', function () {
            $('.check_item').prop('checked', this.checked);
            toggleBulkButton();
        });

        $(document).on('change', '.check_item', function () {
            toggleBulkButton();
        });

        function toggleBulkButton() {
            var len = $('.check_item:checked').length;
            if (len > 0) {
                $('#hapusterpilih').removeClass('d-none');
            } else {
                $('#hapusterpilih').addClass('d-none');
            }
        }

        // Toggle Status Mapel
        $(document).on('change', '.toggle-status-mapel', function () {
            var $el = $(this);
            var id = $el.data('id');
            $.post("<?= base_url('datamapel/toggle_status') ?>", { id_mapel: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                if (res && res.status) {
                    $el.prop('checked', res.new_status == '1');
                } else {
                    $el.prop('checked', !$el.prop('checked'));
                    Swal.fire('Gagal', res && res.message ? res.message : 'Gagal mengubah status', 'error');
                }
            }).fail(function () {
                $el.prop('checked', !$el.prop('checked'));
                Swal.fire('Error', 'Gagal terhubung ke server', 'error');
            });
        });

        // Edit Single Mapel Event
        $(document).on('click', '.btn-edit-mapel', function () {
            $('#edit_id_mapel').val($(this).data('id'));
            $('#nama_mapel').val($(this).data('nama'));
            $('#kode_mapel').val($(this).data('kode'));
            $('#urutan_tampil').val($(this).data('urutan'));
            $('#mapel_kelompok').val($(this).data('kelompok'));
            $('#mapel_status').val($(this).data('status'));
            $('#mapelModalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Mata Pelajaran');
        });

        $('[data-bs-target="#createMapelModal"]').on('click', function () {
            $('#edit_id_mapel').val('');
            $('#form-create-mapel')[0].reset();
            $('#mapelModalTitle').html('<i class="bi bi-journal-plus me-2"></i>Tambah Mata Pelajaran');
        });

        // Edit Kelompok Utama Event
        $(document).on('click', '.btn-edit-kel', function () {
            $('#id_kel_mapel').val($(this).data('id'));
            $('#createkodekel').val($(this).data('kode'));
            $('#createnamakel').val($(this).data('nama'));
            $('#kategori').val($(this).data('kategori'));
        });

        // Edit Sub Kelompok Event
        $(document).on('click', '.btn-edit-sub', function () {
            $('#id_kel_sub').val($(this).data('id'));
            $('#createkodesub').val($(this).data('kode'));
            $('#createnamasub').val($(this).data('nama'));
            $('#id_parent_sub').val($(this).data('utama'));
            $('#kategori_sub').val($(this).data('kategori'));
        });

        // Auto update kategori_sub when parent kel is selected
        $('#id_parent_sub').on('change', function () {
            var selectedOpt = $(this).find('option:selected');
            $('#kategori_sub').val(selectedOpt.data('kategori') || '');
        });

        // Submit Form Kelompok Utama
        $('#create-kelompok').on('submit', function (e) {
            e.preventDefault();
            $.post("<?= base_url('datamapel/addkelompokmapel') ?>", $(this).serialize(), function (res) {
                if (res && res.status) {
                    Swal.fire('Berhasil', res.message || 'Kelompok mapel berhasil disimpan', 'success');
                    $('#editKelompokModal').modal('hide');
                    location.reload();
                } else {
                    Swal.fire('Gagal', (res && res.message) ? res.message : 'Gagal menyimpan kelompok', 'error');
                }
            });
        });

        // Submit Form Sub Kelompok
        $('#create-sub-kelompok').on('submit', function (e) {
            e.preventDefault();
            $.post("<?= base_url('datamapel/addkelompokmapel') ?>", $(this).serialize(), function (res) {
                if (res && res.status) {
                    Swal.fire('Berhasil', res.message || 'Sub kelompok mapel berhasil disimpan', 'success');
                    $('#editSubKelompokModal').modal('hide');
                    location.reload();
                } else {
                    Swal.fire('Gagal', (res && res.message) ? res.message : 'Gagal menyimpan sub kelompok', 'error');
                }
            });
        });

        // Submit Form Create/Edit Mapel
        $('#form-create-mapel').on('submit', function (e) {
            e.preventDefault();
            var editId = $('#edit_id_mapel').val();
            var targetUrl = editId ? "<?= base_url('datamapel/update') ?>" : "<?= base_url('datamapel/create') ?>";
            $.post(targetUrl, $(this).serialize(), function (res) {
                if (res && res.status) {
                    Swal.fire('Berhasil', res.message || 'Data mata pelajaran berhasil disimpan', 'success');
                    $('#createMapelModal').modal('hide');
                    reload_ajax();
                } else {
                    Swal.fire('Gagal', (res && res.message) ? res.message : 'Gagal menyimpan data mapel', 'error');
                }
            });
        });

        // Handler Pembagian Kelas
        $('#select-kelas-pembagian').on('change', function () {
            var kId = $(this).val();
            if (!kId) {
                $('#pembagian-content').addClass('d-none');
                $('#pembagian-empty-state').removeClass('d-none');
                return;
            }
            loadPembagianKelas(kId);
        });

        function loadPembagianKelas(kId) {
            $.post("<?= base_url('datamapel/get_mapel_kelas') ?>", { id_kelas: kId, [CSRF_NAME]: CSRF_HASH }, function (res) {
                if (!res.status) {
                    Swal.fire('Error', res.message || 'Gagal memuat data pembagian kelas', 'error');
                    return;
                }

                $('#pembagian-empty-state').addClass('d-none');
                $('#pembagian-content').removeClass('d-none');

                var d = res.detail;
                $('#cnt-wajib').text(d.total_wajib + ' mapel');
                $('#cnt-pilihan').text(d.total_pilihan + ' mapel');
                $('#cnt-assigned').text(d.total_assigned + ' mapel');

                renderPembagianTable('#tbl-wajib tbody', d.wajib, kId);
                renderPembagianTable('#tbl-pilihan tbody', d.pilihan, kId);
                renderPembagianTable('#tbl-mulok tbody', d.mulok, kId);
            });
        }

        function renderPembagianTable(selector, list, kId) {
            var html = '';
            if (!list || list.length === 0) {
                html = `<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada mata pelajaran di kategori ini</td></tr>`;
            } else {
                list.forEach(function (m) {
                    var teacherBadges = '';
                    if (m.teachers && m.teachers.length > 0) {
                        m.teachers.forEach(function (t) {
                            teacherBadges += `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-1 mb-1 px-2 py-1">
                                <i class="bi bi-person-fill me-1"></i>${t.nama_guru}
                                <a href="javascript:void(0)" onclick="unassignMapelKelas(${kId}, ${m.id_mapel}, ${t.id_guru})" class="text-danger ms-1" title="Hapus Alokasi Guru"><i class="bi bi-x-circle-fill"></i></a>
                            </span>`;
                        });
                    } else {
                        teacherBadges = `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="bi bi-exclamation-circle me-1"></i>Belum Ada Guru Pengampu</span>`;
                    }

                    html += `<tr>
                        <td class="text-center font-monospace fw-bold text-primary">${m.kode ?? '-'}</td>
                        <td class="fw-bold text-dark">${m.nama_mapel}</td>
                        <td class="text-center"><span class="badge bg-light text-dark border">${m.kelompok}</span></td>
                        <td>${teacherBadges}</td>
                        <td class="text-center">
                            <div class="btn-action-group justify-content-center">
                                <button type="button" class="btn btn-action btn-action-magic" onclick="openAlokasiModal(${kId}, ${m.id_mapel}, '${m.nama_mapel}')" data-bs-toggle="tooltip" title="Alokasikan Guru">
                                    <i class="bi bi-person-plus-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });
            }
            $(selector).html(html);
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                $(selector).find('[data-bs-toggle="tooltip"]').each(function() {
                    new bootstrap.Tooltip(this);
                });
            }
        }

        window.openAlokasiModal = function(kId, mId, mNama) {
            $('#alokasi_id_kelas').val(kId);
            $('#alokasi_id_mapel').val(mId);
            $('#alokasi_nama_mapel').val(mNama);
            $('#alokasi_id_guru').val('');
            $('#alokasiMapelModal').modal('show');
        };

        window.unassignMapelKelas = function(kId, mId, gId) {
            Swal.fire({
                title: 'Hapus Alokasi Guru?',
                text: "Guru pengampu mapel ini akan dihapus dari kelas terpilih.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'btn btn-danger rounded-pill px-4 fw-bold', cancelButton: 'btn btn-light rounded-pill px-4 fw-bold border' }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= base_url('datamapel/save_mapel_kelas') ?>", {
                        id_kelas: kId,
                        id_mapel: mId,
                        id_guru: gId,
                        is_assign: 0,
                        [CSRF_NAME]: CSRF_HASH
                    }, function (res) {
                        if (res.status) {
                            Swal.fire('Berhasil', res.message, 'success');
                            loadPembagianKelas(kId);
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    });
                }
            });
        };

        $('#form-alokasi-mapel').on('submit', function (e) {
            e.preventDefault();
            var kId = $('#alokasi_id_kelas').val();
            $.post("<?= base_url('datamapel/save_mapel_kelas') ?>", $(this).serialize(), function (res) {
                if (res && res.status) {
                    Swal.fire('Berhasil', res.message || 'Alokasi mapel berhasil disimpan', 'success');
                    $('#alokasiMapelModal').modal('hide');
                    loadPembagianKelas(kId);
                } else {
                    Swal.fire('Gagal', (res && res.message) ? res.message : 'Gagal mengalokasikan mapel', 'error');
                }
            });
        });

    });

    function reload_ajax() {
        if (table) table.ajax.reload(null, false);
    }

    function hapusKelompok(id) {
        Swal.fire({
            title: 'Hapus Kelompok?',
            text: "Data kelompok mapel yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger rounded-pill px-4 fw-bold', cancelButton: 'btn btn-light rounded-pill px-4 fw-bold border' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("<?= base_url('datamapel/hapuskelompok') ?>", { id_kel: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                    if (res.status) {
                        Swal.fire('Terhapus', 'Kelompok mapel berhasil dihapus', 'success');
                        location.reload();
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menghapus kelompok', 'error');
                    }
                });
            }
        });
    }

    function hapusMapel(id) {
        Swal.fire({
            title: 'Hapus Mata Pelajaran?',
            text: "Data mata pelajaran ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger rounded-pill px-4 fw-bold', cancelButton: 'btn btn-light rounded-pill px-4 fw-bold border' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("<?= base_url('datamapel/delete') ?>", { 'checked[]': id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                    if (res.status) {
                        Swal.fire('Terhapus', 'Mata pelajaran berhasil dihapus', 'success');
                        reload_ajax();
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menghapus mata pelajaran', 'error');
                    }
                });
            }
        });
    }

    function bulk_delete() {
        Swal.fire({
            title: 'Hapus Mapel Terpilih?',
            text: "Data mata pelajaran terpilih akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger rounded-pill px-4 fw-bold', cancelButton: 'btn btn-light rounded-pill px-4 fw-bold border' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("<?= base_url('datamapel/delete') ?>", $('#bulk').serialize(), function (res) {
                    if (res.status) {
                        Swal.fire('Terhapus', 'Mata pelajaran terpilih berhasil dihapus', 'success');
                        reload_ajax();
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menghapus mata pelajaran', 'error');
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>