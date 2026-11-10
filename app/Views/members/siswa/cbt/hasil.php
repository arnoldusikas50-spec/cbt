<?= $this->extend('layouts/siswa') ?>
<?= $this->section('content') ?>
<style>
    /* Entrance animation */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.5s ease both; }
    .fade-up-2 { animation: fadeUp 0.5s ease 0.1s both; }
    .fade-up-3 { animation: fadeUp 0.5s ease 0.2s both; }

    .hover-scale { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .hover-scale:hover { transform: translateY(-2px); }



    /* Remedial banner */
    .remedial-banner {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 5px solid #2196f3;
    }
    .attempt-pill {
        display: inline-block;
        background: rgba(33, 150, 243, 0.15);
        color: #1565c0;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 0.2rem 0.7rem;
        border-radius: 1rem;
    }
    .btn-remedial {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        border: none;
        color: #fff;
        font-weight: 700;
        padding: 0.85rem 2rem;
        border-radius: 2rem;
        transition: all 0.25s;
    }
    .btn-remedial:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(33, 150, 243, 0.35);
        color: #fff;
    }
    .btn-remedial:disabled { background: #b0bec5; transform: none; box-shadow: none; }

    /* Exam info */
    .exam-info { background: #f8f9fa; border-radius: 0.75rem; }
    .exam-info .label { font-size: 0.75rem; color: #868e96; }
    .exam-info .value { font-weight: 600; color: #343a40; }

    /* Detail jawaban */
    .question-item {
        border-radius: 0.6rem;
        margin-bottom: 0.75rem;
        padding: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .question-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }
    .question-correct   { background: #e8f5e9; border-left: 4px solid #4caf50; }
    .question-wrong     { background: #ffebee; border-left: 4px solid #f44336; }
    .question-unanswered{ background: #fff3e0; border-left: 4px solid #ff9800; }

    /* Toggle Icon Animation */
    .toggle-icon { transition: transform 0.3s ease; }
    .collapsed .toggle-icon { transform: rotate(-90deg); }
</style>

<div class="container-fluid py-4 container-p-x main-content-overlap pb-5 mb-5 pb-lg-4 mb-lg-0">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <!-- Main Result Card -->
            <div class="card card-modern border-0 shadow-sm rounded-4 mb-4 fade-up-2">
                <div class="card-body p-3 p-sm-4 p-md-5">
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mb-4 gap-2 flex-wrap">
                        <a href="<?= base_url('cbt') ?>"
                            class="btn btn-light border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                            <i class="bi bi-arrow-left me-2 text-primary"></i> Kembali
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($soal_status)): ?>
                                <button type="button" onclick="toggleDetailJawaban()" class="btn btn-light border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                    <i class="bi bi-list-ul me-2 text-primary"></i> Detail Jawaban
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="text-center mb-4 fade-up">
                        <h4 class="fw-bold text-dark mb-1">Hasil Ujian</h4>
                        <p class="text-muted small mb-4"><?= esc($subjudul) ?></p>

                        <?php
                        $nilai = $hasil['nilai'] ?? 0;
                        $kkm = $hasil['kkm'] ?? 70;
                        $isLulus = $nilai >= $kkm;
                        $totalSoal = $hasil['total_soal'] ?? 0;
                        $jumlahBenar = $hasil['jumlah_benar'] ?? 0;
                        $jumlahSalah = $hasil['jumlah_salah'] ?? 0;
                        $jumlahKosong = ($totalSoal - $jumlahBenar - $jumlahSalah);
                        ?>

                        <!-- Super Minimal Score -->
                        <div class="mb-3">
                            <span class="display-1 fw-bolder text-dark" style="line-height: 1;"><?= number_format($nilai, 0) ?></span>
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <?php if ($isLulus): ?>
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i> LULUS (KKM: <?= $kkm ?>)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                                    <i class="bi bi-x-circle-fill me-1"></i> BELUM LULUS (KKM: <?= $kkm ?>)
                                </span>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- Super Compact Stats (Mobile friendly grid) -->
                    <div class="row g-2 mb-4 mx-auto fade-up-2" style="max-width: 400px;">
                        <div class="col-6 col-sm-3">
                            <div class="p-2 bg-light rounded-3 text-center">
                                <div class="fs-5 fw-bold text-dark"><?= $totalSoal ?></div>
                                <div class="text-muted" style="font-size: 0.65rem; text-transform: uppercase;">Soal</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 bg-success bg-opacity-10 rounded-3 text-center">
                                <div class="fs-5 fw-bold text-success"><?= $jumlahBenar ?></div>
                                <div class="text-success" style="font-size: 0.65rem; text-transform: uppercase;">Benar</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 bg-danger bg-opacity-10 rounded-3 text-center">
                                <div class="fs-5 fw-bold text-danger"><?= $jumlahSalah ?></div>
                                <div class="text-danger" style="font-size: 0.65rem; text-transform: uppercase;">Salah</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 bg-secondary bg-opacity-10 rounded-3 text-center">
                                <div class="fs-5 fw-bold text-secondary"><?= max(0, $jumlahKosong) ?></div>
                                <div class="text-secondary" style="font-size: 0.65rem; text-transform: uppercase;">Kosong</div>
                            </div>
                        </div>
                    </div>

                    <!-- Super Minimalist Time Info -->
                    <div class="text-center text-muted fade-up-2 mb-5" style="font-size: 0.8rem;">
                        <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2 gap-sm-3">
                            <span><i class="bi bi-calendar-event me-1"></i> <?= date('d M Y, H:i', strtotime($cbt_siswa->mulai)) ?></span>
                            <span class="d-none d-sm-inline opacity-50">•</span>
                            <span><i class="bi bi-stopwatch me-1"></i> Durasi: <?= $cbt_siswa->lama_ujian ?? '-' ?></span>
                        </div>
                    </div>

                        <!-- Remedial Section -->
                        <?php if (!$isLulus && $remedial_enabled && !$remedial_exhausted): ?>
                            <div class="remedial-banner rounded-4 p-3 p-sm-4 mb-4 shadow-sm border">
                                <div class="d-flex align-items-start gap-3 flex-wrap">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-arrow-repeat fs-1 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <h5 class="fw-bold text-primary mb-0">Tersedia Remedial!</h5>
                                            <?php if ($sisa_remedial !== null): ?>
                                                <span class="attempt-pill">Sisa <?= $sisa_remedial ?> / <?= $max_remedial ?> percobaan</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-3 text-secondary">
                                            Anda dapat mengerjakan ulang soal yang dijawab salah.
                                            Soal yang sudah benar akan tetap ditampilkan namun terkunci & tidak dapat diubah.
                                        </p>
                                        <button type="button" class="btn btn-remedial" id="btnMulaiRemedial" onclick="mulaiRemedial()">
                                            <i class="bi bi-arrow-repeat me-2"></i> Mulai Remedial
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php elseif (!$isLulus && $remedial_enabled && $remedial_exhausted): ?>
                            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                                <div>Batas maksimal remedial (<?= $max_remedial ?>x) telah tercapai. Silakan hubungi guru/pengawas jika membutuhkan tambahan.</div>
                            </div>
                        <?php elseif (!$isLulus && !$remedial_enabled): ?>
                            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                                <div>Remedial tidak tersedia untuk ujian ini. Silakan hubungi guru/pengawas.</div>
                            </div>
                        <?php endif; ?>

                        <!-- Spacer to replace bottom action buttons -->

                    </div>
                </div>

                <!-- Question Details -->
                <?php if (!empty($soal_status)): ?>
                    <div id="collapseDetail" style="display: none;">
                        <div class="card card-modern border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-3 p-sm-4">
                                <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Daftar Soal & Status Jawaban</h6>
                                <div class="row">
                                <?php foreach ($soal_status as $index => $soal): ?>
                                    <?php
                                    $statusClass = '';
                                    $statusIcon = '';
                                    $statusText = '';
                                    if ($soal['is_benar']) {
                                        $statusClass = 'question-correct';
                                        $statusIcon = 'check-circle-fill';
                                        $statusText = 'Benar';
                                    } elseif ($soal['jawaban_siswa']) {
                                        $statusClass = 'question-wrong';
                                        $statusIcon = 'x-circle-fill';
                                        $statusText = 'Salah';
                                    } else {
                                        $statusClass = 'question-unanswered';
                                        $statusIcon = 'exclamation-circle-fill';
                                        $statusText = 'Tidak dijawab';
                                    }
                                    ?>
                                    <div class="col-12 col-md-6">
                                        <div class="question-item <?= $statusClass ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <span class="badge bg-dark mb-2">Soal <?= $index + 1 ?></span>
                                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                                        <?= strip_tags(substr($soal['soal'], 0, 100)) ?>...
                                                    </div>
                                                </div>
                                                <i class="bi bi-<?= $statusIcon ?> fs-5"></i>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Status: <span class="fw-semibold"><?= $statusText ?></span>

                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- ══ MOBILE BOTTOM NAV ══ -->
<nav class="position-fixed bottom-0 start-0 w-100 d-flex d-lg-none bg-white border-top shadow-lg" style="padding-bottom: env(safe-area-inset-bottom); z-index: 1040;">
    <div class="d-flex justify-content-around align-items-center w-100 py-2 px-1">
        <a href="<?= base_url('dashboard') ?>" id="bn-home" class="text-decoration-none d-flex flex-column align-items-center text-primary bg-primary bg-opacity-10 py-1 px-3 rounded-pill transition-all" style="min-width: 60px;">
            <i class="bi bi-house-door-fill fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Home</small>
        </a>

        <button onclick="logout()" class="btn btn-link text-decoration-none d-flex flex-column align-items-center text-secondary py-1 px-3 rounded-pill transition-all" style="min-width: 60px; box-shadow: none;">
            <i class="bi bi-box-arrow-right fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Keluar</small>
        </button>
    </div>
</nav>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function mulaiRemedial() {
    // Konfirmasi bertingkat (2x) agar siswa tidak menekan tombol secara tidak sengaja
    Swal.fire({
        title: 'Mulai Remedial?',
        html: 'Anda akan memulai <b>sesi remedial</b> untuk memperbaiki jawaban yang <b>salah</b>.<br>' +
              'Soal yang sudah <b>benar akan terkunci</b> dan tidak dapat diubah.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1976d2',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Lanjut',
        cancelButtonText: 'Batal'
    }).then((step1) => {
        if (!step1.isConfirmed) return;

        Swal.fire({
            title: 'Konfirmasi Akhir',
            text: 'Setelah sesi remedial dimulai, Anda tidak dapat membatalkan dan soal yang benar akan tetap terkunci. Yakin ingin melanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Mulai Remedial',
            cancelButtonText: 'Tidak'
        }).then((step2) => {
            if (step2.isConfirmed) {
                doStartRemedial();
            }
        });
    });
}

function doStartRemedial() {
    const btn = document.getElementById('btnMulaiRemedial');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Memuat...';
    }

    fetch('<?= base_url('cbt/mulai_remedial') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            jadwal: '<?= $jadwal->id_jadwal ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status && data.redirect) {
            window.location.href = data.redirect;
        } else {
            Swal.fire('Gagal!', data.message || 'Gagal memulai remedial', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Mulai Remedial';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'Terjadi kesalahan saat memulai remedial', 'error');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Mulai Remedial';
        }
    });
}

function toggleDetailJawaban() {
    const detail = document.getElementById('collapseDetail');
    if (detail.style.display === 'none') {
        detail.style.display = 'block';
        setTimeout(() => detail.scrollIntoView({behavior: 'smooth', block: 'start'}), 100);
    } else {
        detail.style.display = 'none';
    }
}
</script>
<?= $this->endSection() ?>
