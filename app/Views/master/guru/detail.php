<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;
        
        --color-success-50: #f0fdf4;
        --color-success-600: #16a34a;

        --color-info-50: #f0f9ff;
        --color-info-600: #0284c7;

        --color-warning-50: #fffbeb;
        --color-warning-600: #d97706;

        --color-danger-50: #fef2f2;
        --color-danger-100: #fee2e2;
        --color-danger-600: #dc2626;
        --color-danger-700: #b91c1c;

        --color-neutral-50: #f9fafb;
        --color-neutral-100: #f3f4f6;
        --color-neutral-200: #e5e7eb;
        --color-neutral-300: #d1d5db;
        --color-neutral-400: #9ca3af;
        --color-neutral-600: #4b5563;
        --color-neutral-700: #374151;

        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
        --radius-full: 9999px;

        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --transition-base: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc !important; }

    /* ============================================
       TYPOGRAPHY & HEADERS
       ============================================ */
    .text-primary-gradient {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    /* ============================================
       PREMIUM COMPONENTS
       ============================================ */
    .pas-card {
        background: white;
        border-radius: var(--radius-2xl);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .pas-card__header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--color-neutral-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--color-neutral-800);
    }

    .pas-card__title-icon {
        width: 40px; height: 40px;
        background: var(--color-primary-50);
        color: var(--color-primary-600);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }

    /* ============================================
       BUTTONS
       ============================================ */
    .pas-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 0.5rem; padding: 0.625rem 1.25rem;
        font-weight: 600; font-size: var(--font-size-sm);
        border-radius: var(--radius-lg); transition: var(--transition-base);
        cursor: pointer; border: 1px solid transparent; text-decoration: none !important;
    }

    .pas-btn--primary { background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
    .pas-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35); color: white; }

    .pas-btn--danger { background: var(--color-danger-600); color: white; }
    .pas-btn--danger:hover { background: var(--color-danger-700); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25); }

    .pas-btn--white { background: white; color: var(--color-neutral-700); border-color: var(--color-neutral-200); }
    .pas-btn--white:hover { background: var(--color-neutral-50); color: var(--color-neutral-900); }

    .alert-premium-danger {
        background: var(--color-danger-50); border: 1px solid var(--color-danger-100);
        color: var(--color-danger-600); border-radius: var(--radius-lg);
        padding: 1.5rem; display: flex; align-items: flex-start; gap: 1rem;
    }

    .detail-table th { background: var(--color-neutral-50); color: var(--color-neutral-600); font-weight: 700; width: 30%; }
</style>

<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark" style="font-family: 'Inter', sans-serif; letter-spacing: -1.5px;">
                    <span class="text-primary-gradient"><i class="bi bi-shield-exclamation me-2"></i></span>Integritas Penghapusan
                </h3>
                <p class="text-muted mb-0">Verifikasi beban kerja dan data sebelum penghapusan permanen</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('dataguru') ?>" class="text-decoration-none">Data Guru</a></li>
                    <li class="breadcrumb-item active">Detail Penghapusan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid">
        <div class="pas-card border-0 shadow-lg">
            <div class="pas-card__header">
                <div class="pas-card__title">
                    <div class="pas-card__title-icon bg-danger-soft text-danger">
                        <i class="bi bi-trash3-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Konfirmasi Data Guru</h6>
                        <span class="smaller text-muted fw-normal">Rincian penggunaan data pada sistem</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="alert-premium-danger mb-4 shadow-sm">
                    <i class="bi bi-exclamation-octagon-fill fs-3"></i>
                    <div>
                        <h6 class="fw-bold mb-2">Tindakan Destruktif Terdeteksi</h6>
                        <p class="mb-0 smaller">Anda sedang dalam proses menghapus entitas <b><?= esc($guru['detail']->nama_guru) ?></b>. Penghapusan ini tidak dapat dibatalkan dan akan berdampak pada beberapa modul terkait.</p>
                        
                        <div class="row mt-4 g-4">
                            <div class="col-md-6 border-end border-danger border-opacity-10">
                                <h6 class="smaller fw-bold text-uppercase tracking-wider mb-2">Dampak Sistem</h6>
                                <ul class="smaller mb-0 ps-3">
                                    <li>Materi ajar yang dibuat oleh guru ini akan hilang.</li>
                                    <li>Data Bank Soal terkait akan ikut terhapus secara kaskade.</li>
                                    <li>Akun login guru juga akan dinonaktifkan permanen.</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="smaller fw-bold text-uppercase tracking-wider mb-2">Rekomendasi</h6>
                                <ul class="smaller mb-0 ps-3">
                                    <li>Gunakan menu <b>Edit Profil</b> jika hanya ingin mengganti nama.</li>
                                    <li>Gunakan <b>User Management</b> untuk menonaktifkan akun sementara.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive rounded-3 border">
                    <table class="table mb-0 detail-table align-middle">
                        <tr>
                            <th class="ps-4 py-3">Nama Pendidik</th>
                            <td class="ps-3"><h5 class="fw-bold text-dark mb-0"><?= esc($guru['detail']->nama_guru) ?></h5></td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Rincian Penugasan</th>
                            <td class="ps-3 py-3">
                                <?php if (empty($guru['jabatan'])): ?>
                                    <span class="pas-badge pas-badge--neutral">Tidak Ada Tugas Aktif</span>
                                <?php else: ?>
                                    <div class="row g-3">
                                        <?php foreach ($guru['jabatan'] as $id_tp => $smts): 
                                            $tp_label = '';
                                            foreach($tp as $t) if($t->id_tp == $id_tp) $tp_label = $t->tahun;
                                        ?>
                                            <div class="col-md-12">
                                                <div class="p-3 bg-light rounded-3 border-start border-primary border-4 shadow-xs">
                                                    <h6 class="fw-bold text-primary mb-2 small text-uppercase tracking-wider"><?= $tp_label ?></h6>
                                                    <div class="row g-2">
                                                        <?php foreach ($smts as $id_smt => $jbtn): ?>
                                                            <div class="col-md-6">
                                                                <div class="bg-white p-2 rounded border shadow-sm smaller">
                                                                    <div class="fw-bold border-bottom pb-1 mb-1 text-muted">SEMESTER <?= $id_smt ?></div>
                                                                    <div class="text-dark fw-bold mb-1"><i class="bi bi-tag-fill me-1 text-primary"></i> <?= $jbtn->level . ' ' . ($jbtn->nama_kelas ?? '') ?></div>
                                                                    <?php if (!empty($jbtn->mapel_kelas)): ?>
                                                                        <div class="ms-3 text-muted">Pengampu Mapel:</div>
                                                                        <ul class="mb-0 ps-4">
                                                                            <?php foreach ($jbtn->mapel_kelas as $mk): ?>
                                                                                <li class="smaller"><?= esc($mk->nama_mapel) ?></li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-4 py-3">Statistik Konten</th>
                            <td class="ps-3">
                                <span class="badge bg-primary rounded-pill px-3 py-2 me-2 shadow-sm"><?= $guru['materi'] ?> Materi Ajar</span>
                                <span class="badge bg-info rounded-pill px-3 py-2 shadow-sm"><?= $guru['bank_soal'] ?> Bank Soal</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5">
                    <a href="<?= site_url('dataguru') ?>" class="pas-btn pas-btn--white px-5 py-3">
                        <i class="bi bi-arrow-left"></i> Batalkan & Kembali
                    </a>
                    <button class="pas-btn pas-btn--danger px-5 py-3 shadow-lg" onclick="hapus('<?= $id_guru ?>')">
                        <i class="bi bi-trash3-fill"></i> Paksa Hapus Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function hapus(idGuru) {
        Swal.fire({
            title: '<h4 class="fw-bold m-0">Validasi Terakhir</h4>',
            html: `Seluruh data terkait guru ini akan <b>HILANG PERMANEN</b> dari sistem. Lanjutkan?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc2626",
            cancelButtonColor: "#64748b",
            confirmButtonText: "Ya, Hapus Sekarang",
            cancelButtonText: "Batal",
            customClass: { popup: 'rounded-4', confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                $.ajax({
                    url: '<?= site_url("dataguru/forcedelete") ?>',
                    data: { id_guru: idGuru, <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    type: "POST",
                    success: function (res) {
                        Swal.close();
                        if (res.status) {
                            Swal.fire({ icon: "success", title: "Terhapus", text: "Data guru beserta relasinya telah dihapus", customClass: { popup: 'rounded-4' } }).then(() => {
                                window.location.href = '<?= site_url("dataguru") ?>';
                            });
                        } else {
                            Swal.fire({ icon: "error", title: "Operasi Gagal", text: res.message || "Terjadi kesalahan internal", customClass: { popup: 'rounded-4' } });
                        }
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire({ icon: "error", title: "Integrity Violation", text: "Data ini masih terhubung dengan log sistem aktif", customClass: { popup: 'rounded-4' } });
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
