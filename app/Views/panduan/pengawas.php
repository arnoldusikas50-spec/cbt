<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<!-- Google Font: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --guide-primary: #2563eb;
        --guide-text: #1f2937;
        --guide-text-muted: #6b7280;
        --guide-bg: #f9fafb;
        --guide-border: #e5e7eb;
        --guide-card: #ffffff;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background-color: var(--guide-bg);
        color: var(--guide-text);
        line-height: 1.6;
    }

    .guide-card {
        background: var(--guide-card);
        border: 1px solid var(--guide-border);
        border-radius: 12px;
    }

    .guide-nav-pill {
        display: flex;
        align-items: center;
        padding: 0.875rem 1rem;
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--guide-text-muted);
        border-radius: 8px;
        text-decoration: none;
        margin-bottom: 0.5rem;
        transition: background-color 0.15s ease;
    }

    .guide-nav-pill:hover {
        background: var(--guide-bg);
        color: var(--guide-text);
    }

    .guide-nav-pill.active {
        background: var(--guide-primary);
        color: #fff;
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        background: var(--guide-bg);
        color: var(--guide-primary);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-right: 1rem;
    }

    .workflow-card {
        background: var(--guide-card);
        border: 1px solid var(--guide-border);
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    
    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
        margin-right: 0.75rem;
        flex-shrink: 0;
    }
    
    .step-badge.prep { background: #dcfce7; color: #166534; }
    .step-badge.exec { background: #dbeafe; color: #1e40af; }
    .step-badge.finish { background: #fef3c7; color: #92400e; }
    
    .checklist {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .checklist li {
        position: relative;
        padding-left: 1.5rem;
        margin-bottom: 0.5rem;
        line-height: 1.5;
        color: var(--guide-text-muted);
        font-size: 0.9rem;
    }
    
    .checklist li::before {
        content: '\F26E';
        font-family: 'bootstrap-icons';
        position: absolute;
        left: 0;
        top: 0.15rem;
        font-size: 0.75rem;
        color: var(--guide-primary);
    }
    
    .action-table {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--guide-border);
        background: var(--guide-card);
    }
    
    .action-table thead th {
        background: var(--guide-bg);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: var(--guide-text-muted);
        padding: 0.875rem 1rem;
        border: none;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .info-item {
        background: var(--guide-card);
        border: 1px solid var(--guide-border);
        border-radius: 10px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .info-item-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.875rem;
        font-size: 1rem;
        background: var(--guide-bg);
        color: var(--guide-primary);
    }
    
    .timeline {
        position: relative;
        padding-left: 1.75rem;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0.4rem;
        bottom: 1rem;
        width: 2px;
        background: var(--guide-border);
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.25rem;
        top: 0.4rem;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--guide-card);
        border: 2px solid var(--guide-primary);
    }

    .timeline-item.prep::before { border-color: #16a34a; }
    .timeline-item.exec::before { border-color: var(--guide-primary); }
    .timeline-item.finish::before { border-color: #ca8a04; }
    
    .doc-card {
        background: var(--guide-card);
        border: 1px solid var(--guide-border);
        border-radius: 10px;
        padding: 1.5rem;
        height: 100%;
    }
    
    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }

    .photo-guideline-box {
        border: 1px dashed var(--guide-border);
        border-radius: 10px;
        padding: 1.25rem;
        text-align: center;
    }

    .photo-item i {
        font-size: 1.5rem;
        color: var(--guide-text-muted);
        margin-bottom: 0.5rem;
        display: block;
    }

    .nav-tabs-wrapper {
        background: var(--guide-card);
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid var(--guide-border);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-semibold text-dark fs-5">
                    <i class="bi bi-journal-richtext text-primary me-2"></i>Panduan Pengawas
                </h3>
                <p class="text-muted small mb-0">Instruksi operasional & administrasi pengawasan</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent small">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Panduan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid px-4">

        <div class="row g-3">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="sticky-top" style="top: 1rem;">
                    <div class="nav-tabs-wrapper mb-3">
                        <div class="nav flex-column nav-pills" id="guide-tabs" role="tablist">
                            <a class="guide-nav-pill active" id="tab-alur-link" data-bs-toggle="pill" href="#tab-alur" role="tab">
                                <i class="bi bi-clock-history me-2"></i> Alur Kerja
                            </a>
                            <a class="guide-nav-pill" id="tab-profil-link" data-bs-toggle="pill" href="#tab-profil" role="tab">
                                <i class="bi bi-person-badge me-2"></i> Profil & TTD
                            </a>
                            <a class="guide-nav-pill" id="tab-monitoring-link" data-bs-toggle="pill" href="#tab-monitoring" role="tab">
                                <i class="bi bi-display me-2"></i> Monitoring
                            </a>
                            <a class="guide-nav-pill" id="tab-laporan-link" data-bs-toggle="pill" href="#tab-laporan" role="tab">
                                <i class="bi bi-file-earmark-pdf me-2"></i> Administrasi
                            </a>
                        </div>
                    </div>
                    
                    <div class="guide-card p-3">
                        <h6 class="fw-semibold text-dark mb-2 small">Bantuan</h6>
                        <p class="small text-muted mb-3">Hubungi tim teknis jika ada kendala.</p>
                        <a href="#" class="btn btn-primary btn-sm w-100"><i class="bi bi-whatsapp me-1"></i>Tim Proktor</a>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="col-lg-9">
                <div class="tab-content" id="guide-tabs-content">
                    
                    <!-- Tab 1: Alur Kerja -->
                    <div class="tab-pane fade show active" id="tab-alur" role="tabpanel">
                        <div class="guide-card p-4">
                            <h5 class="fw-semibold text-dark mb-4 pb-3 border-bottom">Timeline Pengawasan</h5>
                            
                            <div class="timeline">
                                <div class="timeline-item prep">
                                    <div class="workflow-card">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="step-badge prep">1</span>
                                            <h6 class="fw-semibold text-dark mb-0">Tahap Persiapan</h6>
                                            <span class="badge bg-success bg-opacity-10 text-success ms-auto small">H-1 / 30m Sebelum</span>
                                        </div>
                                        <ul class="checklist">
                                            <li>Login ke aplikasi dan pastikan <strong>Profil & TTD Digital</strong> sudah diisi.</li>
                                            <li>Siapkan <strong>Pakta Integritas</strong> di menu Laporan.</li>
                                            <li>Cek kesiapan perangkat (PC/Laptop/Tablet) di ruangan masing-masing.</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="timeline-item exec">
                                    <div class="workflow-card">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="step-badge exec">2</span>
                                            <h6 class="fw-semibold text-dark mb-0">Tahap Pelaksanaan</h6>
                                            <span class="badge bg-primary bg-opacity-10 text-primary ms-auto small">Berlangsung</span>
                                        </div>
                                        <ul class="checklist">
                                            <li>Bagikan kartu login dan awasi proses login siswa.</li>
                                            <li>Buka menu <strong>Monitoring Ujian</strong> untuk memantau pengerjaan.</li>
                                            <li>Lakukan aksi <strong>Reset Login</strong> jika siswa terputus dari jaringan.</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="timeline-item finish">
                                    <div class="workflow-card">
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="step-badge finish">3</span>
                                            <h6 class="fw-semibold text-dark mb-0">Tahap Penyelesaian</h6>
                                            <span class="badge bg-warning bg-opacity-10 text-warning ms-auto small">Akhir Sesi</span>
                                        </div>
                                        <ul class="checklist">
                                            <li>Pastikan semua siswa sudah menekan tombol <strong>Selesai</strong>.</li>
                                            <li>Isi dan kirim <strong>Berita Acara</strong> melalui menu Laporan.</li>
                                            <li>Upload bukti foto pelaksanaan ujian di ruangan tersebut.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Profil & TTD -->
                    <div class="tab-pane fade" id="tab-profil" role="tabpanel">
                        <div class="guide-card p-4">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="feature-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div>
                                    <h5 class="fw-semibold text-dark mb-0">Identitas & Tanda Tangan</h5>
                                    <p class="text-muted mb-0 small">Profil tersinkron otomatis dengan dokumen laporan</p>
                                </div>
                            </div>
                            
                            <div class="alert alert-primary border-0 rounded-3 p-3 mb-4 small">
                                <i class="bi bi-lightbulb me-2"></i>
                                <strong>Tips:</strong> Gunakan Tanda Tangan Digital yang presisi agar laporan PDF terlihat otentik.
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="bi bi-pencil-square"></i>
                                        </div>
                                        <h6 class="fw-semibold text-dark mb-2">Mengisi Profil</h6>
                                        <ol class="small text-muted ps-3 mb-0">
                                            <li class="mb-1">Klik menu <strong>Profile</strong> di sidebar.</li>
                                            <li class="mb-1">Lengkapi Nama, NIP, HP, dan Alamat.</li>
                                            <li class="mb-1">Atur Jabatan sebagai <strong>Pengawas</strong>.</li>
                                            <li>Klik <strong>Simpan Perubahan</strong>.</li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-item-icon">
                                            <i class="bi bi-vector-pen"></i>
                                        </div>
                                        <h6 class="fw-semibold text-dark mb-2">Membuat TTD</h6>
                                        <ol class="small text-muted ps-3 mb-0">
                                            <li class="mb-1">Navigasi ke bagian <strong>TTD Digital</strong>.</li>
                                            <li class="mb-1">Klik <strong>Buat Tanda Tangan</strong>.</li>
                                            <li class="mb-1">Goreskan TTD pada kanvas.</li>
                                            <li>Klik <strong>Simpan TTD</strong>.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Monitoring -->
                    <div class="tab-pane fade" id="tab-monitoring" role="tabpanel">
                        <div class="guide-card p-4">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="feature-icon">
                                    <i class="bi bi-display"></i>
                                </div>
                                <div>
                                    <h5 class="fw-semibold text-dark mb-0">Monitoring Ujian</h5>
                                    <p class="text-muted mb-0 small">Kendalikan sesi ujian secara real-time</p>
                                </div>
                            </div>
                            
                            <div class="action-table mb-4">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Aksi</th>
                                            <th>Skenario Penggunaan</th>
                                            <th class="text-center">Resiko</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3">
                                                <span class="action-btn bg-primary bg-opacity-10 text-primary">
                                                    <i class="bi bi-arrow-clockwise"></i> Reset Login
                                                </span>
                                            </td>
                                            <td class="small text-muted">Siswa terputus koneksi atau ingin pindah perangkat.</td>
                                            <td class="text-center align-middle"><span class="badge bg-success">Rendah</span></td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3">
                                                <span class="action-btn bg-warning bg-opacity-10 text-warning">
                                                    <i class="bi bi-check-circle"></i> Selesai Paksa
                                                </span>
                                            </td>
                                            <td class="small text-muted">Waktu habis namun siswa tidak menekan tombol selesai.</td>
                                            <td class="text-center align-middle"><span class="badge bg-warning text-dark">Sedang</span></td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3">
                                                <span class="action-btn bg-danger bg-opacity-10 text-danger">
                                                    <i class="bi bi-trash-fill"></i> Hapus Jawaban
                                                </span>
                                            </td>
                                            <td class="small text-danger">Menghapus seluruh progress. Hanya untuk error data.</td>
                                            <td class="text-center align-middle"><span class="badge bg-danger">Tinggi</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <h6 class="fw-semibold text-dark mb-3 small">Indikator Status Peserta:</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center p-2 border rounded-3">
                                        <i class="bi bi-circle-fill text-warning me-2 small"></i>
                                        <div>
                                            <span class="small fw-medium">Belum Login</span>
                                            <p class="small text-muted mb-0">Siswa belum masuk</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center p-2 border rounded-3">
                                        <i class="bi bi-circle-fill text-primary me-2 small"></i>
                                        <div>
                                            <span class="small fw-medium">Mengerjakan</span>
                                            <p class="small text-muted mb-0">Siswa aktif</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center p-2 border rounded-3">
                                        <i class="bi bi-circle-fill text-success me-2 small"></i>
                                        <div>
                                            <span class="small fw-medium">Selesai</span>
                                            <p class="small text-muted mb-0">Jawaban terkirim</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-laporan" role="tabpanel">
                        <div class="guide-card p-4">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="feature-icon">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div>
                                    <h5 class="fw-semibold text-dark mb-0">Administrasi Pelaporan</h5>
                                    <p class="text-muted mb-0 small">Dokumentasi resmi pelaksanaan ujian</p>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <!-- Pakta Integritas -->
                                <div class="col-md-6">
                                    <div class="doc-card">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary text-white me-3" style="background: #2563eb;">
                                                <i class="bi bi-shield-lock"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <h6 class="fw-semibold mb-0">Pakta Integritas</h6>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary small">1x per periode</span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="small text-muted mb-3">Pernyataan pengawas untuk menjaga kejujuran ujian. Data TTD otomatis tersemat.</p>
                                        <div class="bg-light p-2 rounded-3 mt-auto">
                                            <span class="small fw-medium text-dark d-block mb-1">Langkah:</span>
                                            <ol class="small text-muted ps-3 mb-0">
                                                <li class="mb-1">Klik <strong>Tambah Pakta</strong></li>
                                                <li class="mb-1">Isi data instansi</li>
                                                <li>Klik <strong>Simpan</strong></li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>

                                <!-- Berita Acara -->
                                <div class="col-md-6">
                                    <div class="doc-card">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-success text-white me-3" style="background: #16a34a;">
                                                <i class="bi bi-file-earmark-check"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <h6 class="fw-semibold mb-0">Berita Acara</h6>
                                                    <span class="badge bg-success bg-opacity-10 text-success small">Setiap sesi</span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="small text-muted mb-3">Laporan aktivitas ruangan. Wajib diisi setelah ujian selesai.</p>
                                        <div class="bg-light p-2 rounded-3 mt-auto">
                                            <span class="small fw-medium text-dark d-block mb-1">Isi Data:</span>
                                            <ul class="small text-muted ps-3 mb-0">
                                                <li class="mb-1">Jumlah siswa hadir</li>
                                                <li class="mb-1">Siswa tidak hadir</li>
                                                <li class="mb-1">Hambatan teknis</li>
                                                <li>Upload foto bukti</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ketentuan Foto -->
                            <h6 class="fw-semibold text-dark mb-3 small">Ketentuan Foto Laporan:</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="photo-guideline-box">
                                        <i class="bi bi-people text-muted fs-4 mb-2 d-inline-block"></i>
                                        <h6 class="fw-semibold mb-1 small">Siswa Mengerjakan</h6>
                                        <p class="text-muted small mb-0">Foto dari depan ruangan</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="photo-guideline-box">
                                        <i class="bi bi-door-open text-muted fs-4 mb-2 d-inline-block"></i>
                                        <h6 class="fw-semibold mb-1 small">Suasana Ruangan</h6>
                                        <p class="text-muted small mb-0">Foto panoramic ruang</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="photo-guideline-box">
                                        <i class="bi bi-clipboard-check text-muted fs-4 mb-2 d-inline-block"></i>
                                        <h6 class="fw-semibold mb-1 small">Cek Absensi</h6>
                                        <p class="text-muted small mb-0">Daftar hadir siswa</p>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Tab transition smoothing
        $('#guide-tabs a').click(function(e) {
            e.preventDefault();
            $(this).tab('show');
            setTimeout(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        });

        // Ensure current tab is highlighted on load if hash present
        const hash = window.location.hash;
        if (hash) {
            $('.nav-pills a[href="' + hash + '"]').tab('show');
        }
    });
</script>
<?= $this->endSection() ?>
