<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= esc($subjudul) ?></h1>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="<?= site_url('cbtbanksoal') ?>" class="btn btn-sm btn-danger shadow-sm">
                        <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                    </a>
                    <ol class="breadcrumb float-sm-right mt-2">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('cbtbanksoal') ?>">Bank Soal</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Bank Info Card -->
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title text-bold"><i class="bi bi-info-circle me-2"></i>Informasi Bank Soal</h3>
                    <div class="card-tools ms-auto">
                        <button onclick="window.location.reload()" class="btn btn-sm btn-warning shadow-sm me-2">
                            <i class="bi bi-arrow-repeat me-1"></i> Refresh
                        </button>
                        <button id="btn-download" class="btn btn-sm btn-primary shadow-sm">
                            <i class="bi bi-download me-1"></i> Download / Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6 border-end-md">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small">Kode Bank</span>
                                    <span class="fw-bold">
                                        <?= esc($bank->bank_kode) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small">Mata Pelajaran</span>
                                    <span class="fw-bold">
                                        <?= esc($bank->nama_mapel) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small">Guru Pengampu</span>
                                    <span class="fw-bold">
                                        <?= esc($bank->nama_guru) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted small">ID Bank</span>
                                    <span class="badge bg-light text-dark border">
                                        <?= esc($bank->id_bank) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small">Level / Kelas</span>
                                    <span class="fw-bold">
                                        <?= esc($bank->bank_level) ?> /
                                        <?php
                                        $kelas_names = [];
                                        if ($bank->bank_kelas) {
                                            $decoded_kelas = is_string($bank->bank_kelas) ? json_decode($bank->bank_kelas) : $bank->bank_kelas;
                                            foreach ($decoded_kelas as $k) {
                                                $kelas_names[] = $k->kelas_id; // For now just show ID or code if available
                                            }
                                        }
                                        echo implode(', ', $kelas_names);
                                        ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small">Total Soal Dibuat</span>
                                    <span class="fw-bold">
                                        <?= count($soals) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small">Status</span>
                                    <span
                                        class="badge <?= $bank->status == '1' ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                        <?= $bank->status == '1' ? 'Aktif' : 'Non-Aktif' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Tab Card -->
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white p-0 overflow-hidden" style="border-radius: 12px 12px 0 0;">
                    <ul class="nav nav-tabs nav-justified border-0" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-3 border-0 border-bottom-3" id="pg-tab"
                                data-bs-toggle="tab" data-bs-target="#tab-pg" type="button" role="tab">
                                <i class="bi bi-list-ol me-1"></i> PG
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 border-0 border-bottom-3" id="kompleks-tab"
                                data-bs-toggle="tab" data-bs-target="#tab-kompleks" type="button" role="tab">
                                <i class="bi bi-list-check me-1"></i> Kompleks
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 border-0 border-bottom-3" id="jodohkan-tab"
                                data-bs-toggle="tab" data-bs-target="#tab-jodohkan" type="button" role="tab">
                                <i class="bi bi-intersect me-1"></i> Jodohkan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 border-0 border-bottom-3" id="isian-tab" data-bs-toggle="tab"
                                data-bs-target="#tab-isian" type="button" role="tab">
                                <i class="bi bi-pencil-square me-1"></i> Isian
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-3 border-0 border-bottom-3" id="essai-tab" data-bs-toggle="tab"
                                data-bs-target="#tab-essai" type="button" role="tab">
                                <i class="bi bi-chat-left-dots me-1"></i> Essai
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- PG -->
                        <div class="tab-pane fade show active" id="tab-pg" role="tabpanel">
                            <?php $pg_soals = array_filter($soals, function ($s) {
                                return $s->jenis == '1';
                            }); ?>
                            <?php if (empty($pg_soals)): ?>
                                <div class="text-center py-5 text-muted small">
                                    <i class="bi bi-folder2-open display-4 opacity-25 d-block mb-3"></i> Belum ada soal
                                    pilihan ganda.
                                </div>
                            <?php else: ?>
                                <?php foreach ($pg_soals as $s): ?>
                                    <div class="question-item mb-4 pb-4 border-bottom last-child-no-border">
                                        <div class="d-flex gap-3">
                                            <div class="fw-bold text-primary">
                                                <?= $s->nomor_soal ?>.
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="mb-3">
                                                    <?= $s->soal ?>
                                                </div>
                                                <div class="ps-3 border-start border-primary border-3">
                                                    <div class="mb-1 <?= $s->jawaban == 'A' ? 'fw-bold text-success' : '' ?>">A.
                                                        <?= $s->opsi_a ?>
                                                    </div>
                                                    <div class="mb-1 <?= $s->jawaban == 'B' ? 'fw-bold text-success' : '' ?>">B.
                                                        <?= $s->opsi_b ?>
                                                    </div>
                                                    <div class="mb-1 <?= $s->jawaban == 'C' ? 'fw-bold text-success' : '' ?>">C.
                                                        <?= $s->opsi_c ?>
                                                    </div>
                                                    <div class="mb-1 <?= $s->jawaban == 'D' ? 'fw-bold text-success' : '' ?>">D.
                                                        <?= $s->opsi_d ?>
                                                    </div>
                                                    <?php if (!empty($s->opsi_e)): ?>
                                                        <div class="mb-0 <?= $s->jawaban == 'E' ? 'fw-bold text-success' : '' ?>">E.
                                                            <?= $s->opsi_e ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Kompleks -->
                        <div class="tab-pane fade" id="tab-kompleks" role="tabpanel">
                            <?php $komp_soals = array_filter($soals, function ($s) {
                                return $s->jenis == '2';
                            }); ?>
                            <?php if (empty($komp_soals)): ?>
                                <div class="text-center py-5 text-muted small">
                                    <i class="bi bi-folder2-open display-4 opacity-25 d-block mb-3"></i> Belum ada soal PG
                                    kompleks.
                                </div>
                            <?php else: ?>
                                <?php foreach ($komp_soals as $s): ?>
                                    <div class="question-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex gap-3">
                                            <div class="fw-bold text-primary">
                                                <?= $s->nomor_soal ?>.
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="mb-3">
                                                    <?= $s->soal ?>
                                                </div>
                                                <div class="ps-3">
                                                    <?php
                                                    $opsis = is_string($s->opsi_a) ? json_decode($s->opsi_a, true) : (array) $s->opsi_a;
                                                    $kuncis = is_string($s->jawaban) ? json_decode($s->jawaban, true) : (array) $s->jawaban;
                                                    if ($opsis):
                                                        foreach ($opsis as $key => $val):
                                                            $is_correct = in_array(strtolower($key), array_map('strtolower', $kuncis));
                                                            ?>
                                                            <div
                                                                class="d-flex gap-2 mb-1 <?= $is_correct ? 'text-success fw-bold' : '' ?>">
                                                                <span>
                                                                    <?= strtoupper($key) ?>.
                                                                </span>
                                                                <span>
                                                                    <?= $val ?>
                                                                </span>
                                                                <?php if ($is_correct): ?><i class="bi bi-check-lg ms-auto"></i>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Jodohkan -->
                        <div class="tab-pane fade" id="tab-jodohkan" role="tabpanel">
                            <?php $jodoh_soals = array_filter($soals, function ($s) {
                                return $s->jenis == '3';
                            }); ?>
                            <?php if (empty($jodoh_soals)): ?>
                                <div class="text-center py-5 text-muted small">
                                    <i class="bi bi-folder2-open display-4 opacity-25 d-block mb-3"></i> Belum ada soal
                                    menjodohkan.
                                </div>
                            <?php else: ?>
                                <?php foreach ($jodoh_soals as $s): ?>
                                    <div class="question-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex gap-3">
                                            <div class="fw-bold text-primary">
                                                <?= $s->nomor_soal ?>.
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="mb-3">
                                                    <?= $s->soal ?>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <?php
                                                        $data = is_string($s->jawaban) ? json_decode($s->jawaban, true) : (array) $s->jawaban;
                                                        if (isset($data['jawaban'])):
                                                            foreach ($data['jawaban'] as $row_idx => $row):
                                                                ?>
                                                                <tr class="<?= $row_idx == 0 ? 'bg-light fw-bold' : '' ?>">
                                                                    <?php foreach ($row as $cell_idx => $cell): ?>
                                                                        <td class="text-center p-2">
                                                                            <?php
                                                                            if ($row_idx > 0 && $cell_idx > 0) {
                                                                                echo $cell == '1' ? '<span class="badge bg-success"><i class="bi bi-check"></i></span>' : '';
                                                                            } else {
                                                                                echo $cell;
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    <?php endforeach; ?>
                                                                </tr>
                                                            <?php endforeach; endif; ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Isian -->
                        <div class="tab-pane fade" id="tab-isian" role="tabpanel">
                            <?php $isian_soals = array_filter($soals, function ($s) {
                                return $s->jenis == '4';
                            }); ?>
                            <?php if (empty($isian_soals)): ?>
                                <div class="text-center py-5 text-muted small">
                                    <i class="bi bi-folder2-open display-4 opacity-25 d-block mb-3"></i> Belum ada soal
                                    isian singkat.
                                </div>
                            <?php else: ?>
                                <?php foreach ($isian_soals as $s): ?>
                                    <div class="question-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex gap-3">
                                            <div class="fw-bold text-primary">
                                                <?= $s->nomor_soal ?>.
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="mb-2">
                                                    <?= $s->soal ?>
                                                </div>
                                                <div class="alert alert-light border border-dashed py-2 mb-0">
                                                    <small class="text-muted d-block mb-1">Kunci Jawaban:</small>
                                                    <strong class="text-danger">
                                                        <?= esc($s->jawaban) ?>
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Essai -->
                        <div class="tab-pane fade" id="tab-essai" role="tabpanel">
                            <?php $essai_soals = array_filter($soals, function ($s) {
                                return $s->jenis == '5';
                            }); ?>
                            <?php if (empty($essai_soals)): ?>
                                <div class="text-center py-5 text-muted small">
                                    <i class="bi bi-folder2-open display-4 opacity-25 d-block mb-3"></i> Belum ada soal
                                    essai.
                                </div>
                            <?php else: ?>
                                <?php foreach ($essai_soals as $s): ?>
                                    <div class="question-item mb-4 pb-4 border-bottom">
                                        <div class="d-flex gap-3">
                                            <div class="fw-bold text-primary">
                                                <?= $s->nomor_soal ?>.
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="mb-2">
                                                    <?= $s->soal ?>
                                                </div>
                                                <div class="alert alert-light border border-dashed py-2 mb-0">
                                                    <small class="text-muted d-block mb-1">Pedoman Jawaban:</small>
                                                    <div class="small">
                                                        <?= $s->jawaban ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .last-child-no-border:last-child {
        border-bottom: none !important;
    }

    .border-bottom-3.active {
        border-bottom: 3px solid var(--bs-primary) !important;
    }

    .nav-tabs .nav-link {
        color: var(--bs-secondary);
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        color: var(--bs-primary);
    }

    @media print {

        .app-header,
        .app-sidebar,
        .card-header,
        .nav-tabs,
        footer,
        .btn-sm {
            display: none !important;
        }

        .app-main {
            padding-top: 0 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .tab-pane {
            display: block !important;
            opacity: 1 !important;
        }

        .question-item {
            page-break-inside: avoid;
        }
    }
</style>

<script>
    $(document).ready(function () {
        $('#btn-download').on('click', function () {
            window.print();
        });
    });
</script>