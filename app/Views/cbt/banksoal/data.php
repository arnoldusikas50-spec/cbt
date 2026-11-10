<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Set default values for optional variables
$gurus = $gurus ?? [];
$mapels = $mapels ?? [];
$levels = $levels ?? [];
$filters = $filters ?? [];
$id_guru = $id_guru ?? '';
$id_mapel = $id_mapel ?? '';
$id_level = $id_level ?? '';
$id_filter = $id_filter ?? '';
$is_panitia = $is_panitia ?? false;
// Admin/panitia tetap boleh mengedit bank soal walau sedang dipakai jadwal
// ujian aktif, jadi tombol edit/import tidak dikunci untuk mereka.
$is_admin = $is_admin ?? false;
?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <?php
        // Get deadline info dari setting global
        $deadline = ($setting && isset($setting->ba_waktu)) ? $setting->ba_waktu : '';
        
        $deadlineInfo = $deadline_info ?? null;
        
        $warningLevel = 'none';
        $isExpired = false;
        $remaining = null;
        
        if ($deadlineInfo && $deadlineInfo['has_deadline']) {
            $warningLevel = $deadlineInfo['warning_level'];
            $isExpired = $deadlineInfo['is_expired'];
            $deadline = $deadlineInfo['deadline_waktu'];
            $remaining = $deadlineInfo['remaining'] ?? null;
        }
        
        // Determine styling based on warning level
        $bgGradient = 'bg-gradient-primary';
        $iconColor = 'text-primary';
        $badgeClass = 'bg-light text-primary border border-primary';
        $alertIcon = 'bi-clock-history';
        
        if ($isExpired) {
            $bgGradient = 'bg-gradient-danger';
            $iconColor = 'text-danger';
            $badgeClass = 'bg-danger text-white';
            $alertIcon = 'bi-x-octagon-fill';
        } elseif ($warningLevel === 'danger') {
            $bgGradient = 'bg-gradient-danger';
            $iconColor = 'text-danger';
            $badgeClass = 'bg-danger text-white';
            $alertIcon = 'bi-exclamation-triangle-fill';
        } elseif ($warningLevel === 'warning') {
            $bgGradient = 'bg-gradient-warning';
            $iconColor = 'text-warning';
            $badgeClass = 'bg-warning text-dark';
            $alertIcon = 'bi-exclamation-circle-fill';
        } elseif ($warningLevel === 'info') {
            $bgGradient = 'bg-gradient-info';
            $iconColor = 'text-info';
            $badgeClass = 'bg-info text-white';
            $alertIcon = 'bi-info-circle-fill';
        }
        ?>
        <!-- Super Compact Deadline Card -->
        <style>
            .deadline-card {
                border-radius: 12px;
                overflow: hidden;
                border: none;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            }
            .deadline-header {
                background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                padding: 0.6rem 1rem;
                position: relative;
                color: white;
            }
            .deadline-header.is-expired { background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%); }
            .deadline-header.is-warning { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; }
            .deadline-header.is-danger { background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); }

            .deadline-icon-bg {
                position: absolute;
                right: -5px;
                top: 50%;
                transform: translateY(-50%);
                font-size: 3rem;
                opacity: 0.12;
                pointer-events: none;
            }

            .countdown-wrap {
                display: flex;
                gap: 8px;
                justify-content: center;
                margin: 0.75rem 0;
            }

            .countdown-block {
                background: #f8f9fa;
                border-radius: 8px;
                min-width: 55px;
                padding: 8px 4px;
                text-align: center;
                border: 1px solid rgba(0,0,0,0.04);
            }

            .countdown-num {
                font-size: 1.35rem;
                font-weight: 800;
                line-height: 1;
                margin-bottom: 2px;
                letter-spacing: -0.5px;
            }

            .countdown-label {
                font-size: 0.55rem;
                font-weight: 700;
                color: #6c757d;
                text-uppercase: uppercase;
                letter-spacing: 0.5px;
            }

            .countdown-separator {
                font-size: 1.2rem;
                font-weight: 700;
                color: #dee2e6;
                align-self: center;
                margin-top: -8px;
            }

            .deadline-badge {
                display: inline-flex;
                align-items: center;
                background: rgba(255, 255, 255, 0.2);
                padding: 2px 8px;
                border-radius: 50px;
                font-size: 0.65rem;
                font-weight: 700;
            }

            .info-notif {
                border-radius: 8px;
                padding: 8px 12px;
                font-size: 0.75rem;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            @media (max-width: 576px) {
                .countdown-block { min-width: 45px; }
                .countdown-num { font-size: 1.1rem; }
            }
        </style>

        <div class="card deadline-card mb-3" id="card-countdown" style="display: none;">
            <div class="deadline-header <?= $isExpired ? 'is-expired' : ($warningLevel === 'danger' ? 'is-danger' : ($warningLevel === 'warning' ? 'is-warning' : '')) ?>">
                <div class="deadline-icon-bg">
                    <i class="bi bi-alarm"></i>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <div class="flex-shrink-0 bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi <?= $alertIcon ?> fs-6"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="deadline-badge text-uppercase mb-0">
                            <i class="bi bi-clock-fill me-1"></i> DEADLINE
                        </div>
                        <h6 class="mb-0 fw-bold text-truncate" id="deadline-text" style="font-size: 0.85rem;">-</h6>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-2 px-3">
                <div class="<?= $isExpired ? 'd-none' : '' ?>" id="countdown-container">
                    <div class="countdown-wrap">
                        <div class="countdown-block">
                            <div class="countdown-num text-dark" id="days">00</div>
                            <div class="countdown-label">HARI</div>
                        </div>
                        <div class="countdown-separator">:</div>
                        <div class="countdown-block">
                            <div class="countdown-num text-dark" id="hours">00</div>
                            <div class="countdown-label">JAM</div>
                        </div>
                        <div class="countdown-separator">:</div>
                        <div class="countdown-block">
                            <div class="countdown-num text-dark" id="minutes">00</div>
                            <div class="countdown-label">MENIT</div>
                        </div>
                        <div class="countdown-separator">:</div>
                        <div class="countdown-block">
                            <div class="countdown-num text-danger" id="seconds">00</div>
                            <div class="countdown-label">DETIK</div>
                        </div>
                    </div>
                </div>

                <div class="<?= $isExpired ? '' : 'd-none' ?> text-center py-2" id="expired-container">
                    <h6 class="fw-bold text-danger mb-1"><i class="bi bi-lock-fill me-1"></i>Waktu Telah Habis</h6>
                    <p class="text-muted mb-0 small">Pengisian telah ditutup otomatis.</p>
                </div>

                <div class="info-notif mb-1 <?= $isExpired ? 'bg-danger-subtle text-danger' : ($warningLevel === 'warning' ? 'bg-warning-subtle text-warning-emphasis' : ($warningLevel === 'danger' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary')) ?>">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="fw-medium">
                        <?php if ($isExpired): ?>
                            Selesai! Tidak dapat ubah data.
                        <?php elseif ($warningLevel === 'danger'): ?>
                            Segera finalisasi Soal Anda!
                        <?php elseif ($warningLevel === 'warning'): ?>
                            Waktu 3 hari lagi. Ayo semangat!
                        <?php else: ?>
                            Selesaikan sebelum deadline
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="progress rounded-0" style="height: 4px; background-color: rgba(0,0,0,0.05);">
                <div id="deadline-progress" 
                     class="progress-bar <?= $isExpired ? 'bg-danger' : ($warningLevel === 'warning' ? 'bg-warning' : ($warningLevel === 'danger' ? 'bg-danger' : 'bg-primary')) ?> progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     style="width: 100%"></div>
            </div>
        </div>

        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">

                <div class="card-tools ms-auto">
                    <button type="button" onclick="location.reload()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <a href="<?= site_url('cbtbanksoal/addBank') ?>" class="btn btn-primary btn-sm ms-1 shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah
                    </a>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#openAllBankSoal"
                        class="btn btn-sm btn-success shadow-sm ms-1">
                        <i class="bi bi-copy me-1"></i> Copy
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#modalRestoreBank"
                        class="btn btn-sm btn-info shadow-sm ms-1 text-white">
                        <i class="bi bi-file-earmark-zip me-1"></i> Restore
                    </button>
                </div>
            </div>
            <div class="card-body">
                <h5 class="badge bg-danger text-white">Kode Warna Bank Soal:</h5>
                <div class="row ms-1">
                    <table class="w-100">
                        <tr>
                            <td class="p-1" style="width: 20px"><i class="bi bi-square-fill text-secondary"></i>
                            </td>
                            <td class="p-1">Tidak digunakan (bisa dihapus)</td>
                        </tr>
                        <tr>
                            <td class="p-1"><i class="bi bi-square-fill text-primary"></i></td>
                            <td class="p-1">Digunakan jadwal (non-aktifkan jadwal jika ingin diedit)</td>
                        </tr>
                        <tr>
                            <td class="p-1"><i class="bi bi-square-fill text-danger"></i></td>
                            <td class="p-1">Digunakan siswa (non-aktifkan jadwal jika ingin diedit)</td>
                        </tr>
                    </table>
                </div>
                <hr>
                <div class="row" id="row-filter">
                    <div class="col-12 col-md-6">
                        <table class="w-100">
                            <tr>
                                <td class="pe-2 text-bold">Filter:</td>
                                <td>
                                    <?= form_dropdown('f', $filters, $id_filter, 'id="filter" class="form-control select2"') ?>
                                </td>
                                <td id="select-guru" class="d-none ps-2">
                                    <?= form_dropdown('guru', $gurus, $id_guru, 'id="guru" class="sel form-control select2"') ?>
                                </td>
                                <td id="select-mapel" class="d-none ps-2">
                                    <?= form_dropdown('mapel', $mapels, $id_mapel, 'id="mapel" class="sel form-control select2"') ?>
                                </td>
                                <td id="select-level" class="d-none ps-2">
                                    <?= form_dropdown('level', $levels, $id_level, 'id="level" class="sel form-control select2"') ?>
                                </td>
                                <td id="select-subtes" class="d-none ps-2">
                                    <?= form_dropdown('subtes', $subtes_options ?? [], $id_subtes ?? '', 'id="subtes" class="sel form-control select2"') ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">

                            <?= csrf_field() ?>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="align-middle text-end">
                                        <button id="submit-hapus-btn" onclick="hapusTerpilih()" type="button"
                                            class="btn btn-danger shadow-sm" disabled>
                                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                                        </button>
                                    </td>
                                    <td class="align-middle text-center" style="width: 30px">
                                        <div class="form-check d-flex justify-content-center">
                                            <input style="width: 20px; height: 20px" class="form-check-input check-all"
                                                id="check-all" type="checkbox">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                    </div>
                </div>
                <div class="row" id="konten">
                    <?php if ($mode == '1'): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered dataTable w-100">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle p-0" style="width: 40px">No.</th>
                                        <th class="align-middle">Kode</th>
                                        <th class="align-middle">Mapel</th>
                                        <th class="align-middle">Kelas</th>
                                        <th class="align-middle text-center">KKM</th>
                                        <th class="align-middle text-center">Status</th>
                                        <th class="text-center align-middle">Aksi</th>
                                        <th class="text-center align-middle p-0" style="width: 50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $id_tp = $tp_active->id_tp;
                                    $id_smt = $smt_active->id_smt;
                                    if (isset($banks[$id_tp][$id_smt])):
                                        foreach ($banks[$id_tp][$id_smt] as $bank):
                                            $jumlahKelas = is_string($bank->bank_kelas) ? json_decode($bank->bank_kelas) : $bank->bank_kelas;
                                            $kelasbank = '';
                                            foreach ((array) $jumlahKelas as $j) {
                                                foreach ($kelas as $k) {
                                                    if (isset($j->kelas_id) && $j->kelas_id == $k->id_kelas) {
                                                        $kelasbank .= '<span class="badge bg-primary me-1">' . $k->nama_kelas . '</span> ';
                                                    }
                                                }
                                            }
                                            $bgRandom = 'text-secondary';
                                            $disable_edit = '';
                                            if ($bank->digunakan > 0) {
                                                $bgRandom = $bank->digunakan > 1 ? 'text-danger' : 'text-primary';
                                                $disable_edit = ($bank->digunakan > 1 && !$is_admin) ? 'disabled' : '';
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center align-middle"><?= $no++ ?></td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="bi bi-square-fill fs-4 me-2 <?= $bgRandom ?>"></i>
                                                        <span class="fw-bold"><?= $bank->bank_kode ?></span>
                                                    </div>
                                                    <?php
                                                    $target = (int) $bank->tampil_pg + (int) $bank->tampil_kompleks + (int) $bank->tampil_jodohkan + (int) $bank->tampil_isian + (int) $bank->tampil_esai;
                                                    if ($target > 0):
                                                        $actual = (int) $bank->total_pg + (int) $bank->total_kompleks + (int) $bank->total_jodohkan + (int) $bank->total_isian + (int) $bank->total_esai;
                                                        $percent = min(100, round(($actual / $target) * 100));
                                                        $pColor = $percent < 40 ? 'bg-danger' : ($percent < 80 ? 'bg-warning' : 'bg-success');
                                                        ?>
                                                        <div class="progress rounded-pill" style="height: 6px;">
                                                            <div class="progress-bar <?= $pColor ?>" style="width: <?= $percent ?>%">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted"
                                                            style="font-size: 0.65rem;"><?= $actual ?>/<?= $target ?>
                                                            (<?= $percent ?>%)</small>
                                                    <?php endif; ?>
                                                    <?php
                                                    $totalSoalBank = (int) ($bank->total_soal ?? 0);
                                                    $totalAi = (int) ($bank->total_ai ?? 0);
                                                    $totalManual = (int) ($bank->total_manual ?? 0);
                                                    if ($totalSoalBank > 0):
                                                        $pctAi = (int) round(($totalAi / $totalSoalBank) * 100);
                                                        $pctManual = 100 - $pctAi;
                                                        ?>
                                                        <div class="mt-2" title="Perbandingan soal hasil AI dan input manual">
                                                            <div class="progress rounded-pill" style="height: 6px;">
                                                                <div class="progress-bar bg-info" style="width: <?= $pctAi ?>%"
                                                                    title="Soal AI: <?= $totalAi ?> soal (<?= $pctAi ?>%)"></div>
                                                                <div class="progress-bar bg-secondary" style="width: <?= $pctManual ?>%"
                                                                    title="Input manual: <?= $totalManual ?> soal (<?= $pctManual ?>%)"></div>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-1 mt-1" style="font-size: 0.65rem;">
                                                                <span class="badge bg-info text-dark" title="Soal hasil generate AI: <?= $totalAi ?> dari <?= $totalSoalBank ?> soal">
                                                                    <i class="bi bi-stars me-1"></i>AI <?= $pctAi ?>%
                                                                </span>
                                                                <span class="badge bg-secondary" title="Soal input manual: <?= $totalManual ?> dari <?= $totalSoalBank ?> soal">
                                                                    <i class="bi bi-pencil me-1"></i>Manual <?= $pctManual ?>%
                                                                </span>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-warning"><i class="bi bi-journal-bookmark me-1"></i>
                                                        <?= $bank->nama_mapel ?></span><br>
                                                    <span class="badge bg-info"><i class="bi bi-person me-1"></i>
                                                        <?= $bank->nama_guru ?></span><br>
                                                    <span
                                                        class="badge bg-success"><?= $bank->soal_agama != '-' ? $bank->soal_agama : 'Umum' ?></span>
                                                    <?php if (!empty($bank->kartu_judul)): ?>
                                                        <?php
                                                        $paketText = !empty($bank->paket_label) ? 'Paket ' . $bank->paket_label . ' — ' : '';
                                                        ?>
                                                        <br><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill mt-1" title="Kartu Soal: <?= esc($bank->kartu_judul) ?>">
                                                            <i class="bi bi-credit-card me-1"></i><?= $paketText ?><?= esc($bank->kartu_judul) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($bank->subtes_code) && $bank->subtes_code !== 'GENERAL'): ?>
                                                        <br><span class="badge bg-primary bg-opacity-75 mt-1">
                                                            <i class="bi bi-diagram-2 me-1"></i><?= esc($bank->subtes_nama ?? $bank->subtes_code) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($bank->subtes_code) && $bank->subtes_code !== 'GENERAL'): ?>
                                                        <br><span class="badge bg-dark text-light mt-1" title="Jumlah soal aktif tampil (tampilkan=1) — soal aktif yang dibaca ujian">
                                                            <i class="bi bi-check2-circle me-1"></i><?= (int) ($bank->total_tampil ?? 0) ?>/<?= (int) ($bank->total_soal ?? 0) ?> aktif
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle"><?= $kelasbank ?></td>
                                                <td class="text-center align-middle">
                                                    <b><?= $bank->kkm ?? '-' ?></b>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge-action-status <?= $bank->status_soal == '1' ? '' : 'inactive' ?>">
                                                        <?= $bank->status_soal == '1' ? 'Selesai' : 'Belum Selesai' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-action-group justify-content-center">
                                                        <button type="button" onclick="editBank(<?= $bank->id_bank ?>)"
                                                            class="btn btn-action btn-action-edit <?= $disable_edit ?>" title="Edit Bank Soal">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </button>
                                                        <button type="button" onclick="importSoal(<?= $bank->id_bank ?>, <?= $bank->total_soal ?>)"
                                                            class="btn btn-action btn-action-warning <?= $disable_edit ?>" title="Import Soal">
                                                            <i class="bi bi-upload"></i>
                                                        </button>
                                                        <a href="<?= site_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                            class="btn btn-action btn-action-magic" title="<?= $bank->total_soal == 0 ? 'Buat Soal' : 'Kelola / Detail Soal' ?>">
                                                            <i class="bi <?= $bank->total_soal == 0 ? 'bi-plus-lg' : 'bi-magic' ?>"></i>
                                                        </a>
                                                        <a href="<?= site_url('cbtbanksoal/export_backup/' . $bank->id_bank) ?>"
                                                            class="btn btn-action btn-action-info" title="Backup Bank Soal (ZIP)">
                                                            <i class="bi bi-file-earmark-zip"></i>
                                                        </a>
                                                        <button type="button" onclick="getSoal(<?= $bank->id_bank ?>)"
                                                            class="btn btn-action btn-action-status" title="Download Naskah Soal Word ">
                                                            <i class="bi bi-download"></i>
                                                        </button>
                                                        <?php if ($is_admin && (int) ($bank->digunakan ?? 0) > 0): ?>
                                                            <span class="btn btn-action btn-action-warning" title="Dipakai pada jadwal ujian aktif. Perubahan langsung berlaku bagi peserta yang sedang ujian.">
                                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input type="checkbox" name="checked[]" value="<?= $bank->id_bank ?>"
                                                        class="check-bank" style="width: 20px; height: 20px">
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <!-- Grid Mode -->
                        <div class="row w-100">
                            <?php
                            $id_tp = $tp_active->id_tp;
                            $id_smt = $smt_active->id_smt;
                            if (isset($banks[$id_tp][$id_smt])):
                                foreach ($banks[$id_tp][$id_smt] as $bank):
                                    $bgRandom = 'bg-secondary';
                                    $disable_edit = '';
                                    if ($bank->digunakan > 0) {
                                        $bgRandom = $bank->digunakan > 1 ? 'bg-danger' : 'bg-primary';
                                        $disable_edit = ($bank->digunakan > 1 && !$is_admin) ? 'disabled' : '';
                                    }
                                    ?>
                                    <div class="col-md-4">
                                        <div class="card mb-3 border shadow-none" style="border-radius: 12px; overflow: hidden;">
                                            <div class="card-header <?= $bgRandom ?> text-white border-0">
                                                <h3 class="card-title fw-bold"><?= $bank->bank_kode ?></h3>
                                                <div class="card-tools ms-auto">
                                                    <input type="checkbox" name="checked[]" value="<?= $bank->id_bank ?>"
                                                        class="check-bank" style="width: 18px; height: 18px">
                                                </div>
                                            </div>
                                            <div class="card-body py-2">
                                                <ul class="list-group list-group-flush small">
                                                    <li class="list-group-item d-flex justify-content-between px-0">
                                                        <span>Guru</span><b><?= $bank->nama_guru ?></b>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0">
                                                        <span>Mapel</span><b><?= $bank->nama_mapel ?></b>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0">
                                                        <span>Agama</span><b><?= $bank->soal_agama != '-' ? $bank->soal_agama : 'Umum' ?></b>
                                                    </li>
                                                    <?php if (!empty($bank->kartu_judul)): ?>
                                                    <?php $paketTextG = !empty($bank->paket_label) ? 'Paket ' . $bank->paket_label . ' — ' : ''; ?>
                                                    <li class="list-group-item d-flex justify-content-between px-0">
                                                        <span>Kartu</span><b><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0" style="font-size: 0.7rem;"><i class="bi bi-credit-card me-1"></i><?= $paketTextG ?><?= esc($bank->kartu_judul) ?></span></b>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li class="list-group-item d-flex justify-content-between px-0">
                                                        <span>Soal</span><b><?= $bank->total_soal ?>/<?= (int) $bank->tampil_pg + (int) $bank->tampil_esai ?></b>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0">
                                                        <span>KKM</span><b><?= $bank->kkm ?? '-' ?></b>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0">
                                                        <span>Status</span>
                                                         <div class="text-end">
                                                             <span class="badge-action-status <?= $bank->status_soal == '1' ? '' : 'inactive' ?>">
                                                                 <?= $bank->status_soal == '1' ? 'Selesai' : 'Belum Selesai' ?>
                                                             </span>
                                                         </div>
                                                     </li>
                                                 </ul>
                                             </div>
                                             <div class="card-footer bg-light p-2 border-0">
                                                 <div class="btn-action-group justify-content-center w-100">
                                                     <button type="button" onclick="editBank(<?= $bank->id_bank ?>)"
                                                         class="btn btn-action btn-action-edit <?= $disable_edit ?>" title="Edit Bank Soal">
                                                         <i class="bi bi-pencil-fill"></i>
                                                     </button>
                                                     <button type="button" onclick="importSoal(<?= $bank->id_bank ?>, <?= $bank->total_soal ?>)"
                                                         class="btn btn-action btn-action-warning <?= $disable_edit ?>" title="Import Soal">
                                                         <i class="bi bi-upload"></i>
                                                     </button>
                                                     <a href="<?= site_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                         class="btn btn-action btn-action-magic" title="<?= $bank->total_soal == 0 ? 'Buat Soal' : 'Kelola / Detail Soal' ?>">
                                                         <i class="bi <?= $bank->total_soal == 0 ? 'bi-plus-lg' : 'bi-magic' ?>"></i>
                                                     </a>
                                                     <a href="<?= site_url('cbtbanksoal/export_backup/' . $bank->id_bank) ?>"
                                                         class="btn btn-action btn-action-info" title="Backup Bank Soal (ZIP)">
                                                         <i class="bi bi-file-earmark-zip"></i>
                                                     </a>
                                                     <button type="button" onclick="getSoal(<?= $bank->id_bank ?>)"
                                                          class="btn btn-action btn-action-status" title="Download Naskah Soal Word ">
                                                          <i class="bi bi-download"></i>
                                                      </button>
                                                      <?php if ($is_admin && (int) ($bank->digunakan ?? 0) > 0): ?>
                                                          <span class="btn btn-action btn-action-warning" title="Dipakai pada jadwal ujian aktif. Perubahan langsung berlaku bagi peserta yang sedang ujian.">
                                                              <i class="bi bi-exclamation-triangle-fill"></i>
                                                          </span>
                                                      <?php endif; ?>
                                                  </div>
                                               </div>
                                         </div>
                                    </div>
                                <?php endforeach; endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Copy Bank Soal -->
<div class="modal fade" id="openAllBankSoal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Copy Bank Soal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Fitur ini memungkinkan Anda menyalin bank soal dari tahun pelajaran atau
                    semester
                    sebelumnya.</p>
                <?php
                if (isset($banks) && count($banks) > 0):
                    $allBanks = [];
                    if (isset($tp) && is_array($tp)) {
                        foreach ($tp as $thn) {
                            if (isset($smt) && is_array($smt)) {
                                foreach ($smt as $sem) {
                                    if (isset($banks[$thn->id_tp]) && isset($banks[$thn->id_tp][$sem->id_smt])) {
                                        $isActive = ($thn->active == 1 && $sem->active == 1);
                                        $allBanks[] = [
                                            'tahun' => $thn->tahun . ' SMT ' . $sem->smt,
                                            'banks' => $banks[$thn->id_tp][$sem->id_smt],
                                            'is_active' => $isActive
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    usort($allBanks, function ($a, $b) {
                        return $b['tahun'] <=> $a['tahun'];
                    });

                    if (count($allBanks) > 0):
                        foreach ($allBanks as $allBank): ?>
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><?= esc($allBank['tahun']) ?></h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="width: 50px;">No.</th>
                                                    <th>Kode</th>
                                                    <th>Mapel</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                foreach ($allBank['banks'] as $ab): ?>
                                                    <tr>
                                                        <td class="text-center"><?= $no++ ?></td>
                                                        <td><?= esc($ab->bank_kode) ?></td>
                                                        <td><?= esc($ab->nama_mapel) ?></td>
                                                        <td class="text-center">
                                                            <button onclick="copy(<?= $ab->id_bank ?>)" type="button"
                                                                class="btn btn-sm btn-outline-success" title="Duplikat Bank Soal">
                                                                <i class="bi bi-copy me-1"></i> Copy
                                                            </button>
                                                            <?php if (!$allBank['is_active']): ?>
                                                                <button onclick="pindah(<?= $ab->id_bank ?>)" type="button"
                                                                    class="btn btn-sm btn-outline-info ms-1" title="Pindahkan ke TP Aktif">
                                                                    <i class="bi bi-arrow-right-circle me-1"></i> Pindah
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <div class="alert alert-info">Belum ada bank soal yang tersedia untuk disalin.</div>
                    <?php endif;
                else: ?>
                    <div class="alert alert-warning">Pilih filter atau 'Semua' untuk menampilkan bank soal yang bisa
                        disalin.</div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Center Notification -->
<div class="toast-container position-fixed top-50 start-50 translate-middle p-3" style="z-index: 99999">
    <div id="centerToast" class="toast border-0 shadow-lg rounded-4 overflow-hidden" role="alert" aria-live="assertive"
        aria-atomic="true" data-bs-autohide="false" style="min-width: 350px;">
        <div class="toast-header border-0 py-3 px-4">
            <strong class="me-auto fs-5" id="toast-title">Notifikasi</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body p-4 bg-white">
            <p id="toast-message" class="mb-4 fs-6 text-secondary"></p>
        </div>
    </div>
</div>

<div id="for-export" class="d-none"></div>

<script src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/docx_7.1.10.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
<script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>

<script>
    // Deadline dari server PHP
    var deadlineServer = '<?= $deadline ?>';
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

    $(document).ready(function () {
        // Init Countdown if deadline exists
        if (deadlineServer && deadlineServer != '' && deadlineServer != '0000-00-00 00:00:00') {
            $('#card-countdown').show();
            // Replace space with T for better cross-browser compatibility
            initCountdown(deadlineServer.replace(' ', 'T'));
        }

        // Tampilkan pesan error/sukses dari flashdata menggunakan Bootstrap Toast di tengah
        <?php if (session()->getFlashdata('swal_error')): ?>
            $('#toast-title').text('Gagal');
            $('#toast-message').text('<?= session()->getFlashdata('swal_error') ?>');
            $('#centerToast .toast-header').addClass('bg-danger text-white');
            $('#centerToast .btn-close').addClass('btn-close-white');
            var toastEl = document.getElementById('centerToast');
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        <?php endif; ?>

        <?php if (session()->getFlashdata('swal_success')): ?>
            $('#toast-title').text('Sukses');
            $('#toast-message').text('<?= session()->getFlashdata('swal_success') ?>');
            $('#centerToast .toast-header').addClass('bg-success text-white');
            $('#centerToast .btn-close').addClass('btn-close-white');
            var toastEl = document.getElementById('centerToast');
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        <?php endif; ?>

        function initCountdown(endTime) {
            var countDownDate = new Date(endTime).getTime();

            // Manual Format for consistent display
            var dayNames = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            var monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            
            var deadDate = new Date(endTime);
            var day = dayNames[deadDate.getDay()];
            var dateNum = deadDate.getDate();
            var month = monthNames[deadDate.getMonth()];
            var year = deadDate.getFullYear();
            var hoursDate = String(deadDate.getHours()).padStart(2, '0');
            var minutesDate = String(deadDate.getMinutes()).padStart(2, '0');
            
            var formattedDate = day + ", " + dateNum + " " + month + " " + year + " • " + hoursDate + "." + minutesDate;
            $('#deadline-text').text(formattedDate);

            var x = setInterval(function () {
                var now = new Date().getTime();
                var distance = countDownDate - now;

                if (distance < 0) {
                    clearInterval(x);
                    if (document.getElementById("countdown-container")) document.getElementById("countdown-container").classList.add("d-none");
                    if (document.getElementById("expired-container")) document.getElementById("expired-container").classList.remove("d-none");
                    
                    const elIds = ["days", "hours", "minutes", "seconds"];
                    elIds.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.innerText = "00";
                    });
                    
                    const progress = document.getElementById("deadline-progress");
                    if (progress) {
                        progress.style.width = "100%";
                        progress.classList.remove("bg-primary", "bg-warning", "bg-success");
                        progress.classList.add("bg-danger");
                    }
                    return;
                }

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update digits with padding
                const timeMap = {
                    "days": days,
                    "hours": hours,
                    "minutes": minutes,
                    "seconds": seconds
                };

                for (const [id, value] of Object.entries(timeMap)) {
                    const el = document.getElementById(id);
                    if (el) el.innerText = String(value).padStart(2, '0');
                }
            }, 1000);
        }

        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        $('#filter').on('change', function () {
            const type = $(this).val();
            $('#select-guru, #select-mapel, #select-level, #select-subtes').addClass('d-none');
            if (type == '1') $('#select-guru').removeClass('d-none');
            else if (type == '2') $('#select-mapel').removeClass('d-none');
            else if (type == '3') $('#select-level').removeClass('d-none');
            else if (type == '4') $('#select-subtes').removeClass('d-none');

            if (type == '0') {
                const currentUrl = new URL(window.location.href);
                const targetUrl = '<?= site_url('cbtbanksoal?type=0') ?>';
                if (currentUrl.toString() !== targetUrl) {
                    location.href = targetUrl;
                }
            }
        });

        $('.sel').on('change', function () {
            const id = $(this).val();
            const type = $('#filter').val();
            location.href = '<?= site_url('cbtbanksoal') ?>?type=' + type + '&id=' + id;
        });

        // Trigger change to show correct select if id_filter exists
        $('#filter').trigger('change');

        $('#check-all').on('change', function () {
            $('.check-bank').prop('checked', $(this).is(':checked'));
            updateHapusButton();
        });

        $(document).on('change', '.check-bank', function () {
            updateHapusButton();
        });

        function updateHapusButton() {
            const count = $('.check-bank:checked').length;
            $('#submit-hapus').prop('disabled', count === 0);
            $('#submit-hapus-btn').prop('disabled', count === 0);

        }
    });



    function importSoal(id, total) {
        $.ajax({
            url: '<?= site_url('cbtbanksoal/checkBankStatus/') ?>' + id,
            type: 'GET',
            success: function (response) {
                if (response.status) {
                    // Status OK (tidak digunakan), lanjutkan logika import
                    if (total > 0) {
                        Swal.fire({
                            title: 'Import Soal?',
                            text: 'Bank soal sudah berisi soal. Mengimport akan menghapus soal yang ada. Lanjutkan?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Lanjutkan'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.href = '<?= site_url('cbtbanksoal/import/') ?>' + id;
                            }
                        });
                    } else {
                        location.href = '<?= site_url('cbtbanksoal/import/') ?>' + id;
                    }
                } else {
                    // Status Gagal (sedang digunakan), tampilkan toast error
                    showCenterToast('error', response.message);
                }
            },
            error: function () {
                showCenterToast('error', 'Terjadi kesalahan sistem');
            }
        });
    }

    function pindah(id) {
        Swal.fire({
            title: 'Pindahkan Bank Soal?',
            text: 'Bank soal ini akan DIPINDAHKAN (Cut) ke Tahun Pelajaran & Semester Aktif.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0',
            confirmButtonText: 'Ya, Pindahkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    text: 'Sedang memindahkan bank soal...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.post('<?= site_url('cbtbanksoal/do_pindah_bank') ?>', {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                    ids: [id]
                }, function (resp) {
                    if (resp.status) {
                        Swal.fire('Sukses', resp.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', resp.message, 'error');
                    }
                }).fail(function () {
                    Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                });
            }
        });
    }

    function copy(id) {
        Swal.fire({
            title: 'Copy Bank Soal?',
            text: 'Bank soal ini akan dicopy ke tahun pelajaran dan semester aktif.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Copy'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    text: 'Sedang menyalin bank soal...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: '<?= site_url('cbtbanksoal/copybanksoal/') ?>' + id,
                    type: 'POST',
                    data: { [csrfName]: csrfHash },
                    success: function (data) {
                        if (data && data.status) {
                            Swal.fire('Sukses', data.message || 'Bank soal berhasil dicopy', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', (data && data.message) ? data.message : 'Gagal mengcopy bank soal', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    function hapus(id) {
        Swal.fire({
            title: 'Anda yakin?',
            text: 'Bank Soal dan soal-soal di dalamnya akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('cbtbanksoal/deleteBank') ?>',
                    type: 'POST',
                    data: {
                        id_bank: id,
                        [csrfName]: csrfHash
                    },
                    success: function (respon) {
                        if (respon.status) {
                            Swal.fire('Berhasil', respon.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', respon.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    function hapusTerpilih() {
        var checked = $('.check-bank:checked');
        if (checked.length === 0) return;

        Swal.fire({
            title: 'Hapus ' + checked.length + ' Bank Soal?',
            text: 'Bank Soal dan soal-soal di dalamnya akan terhapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Semua!'
        }).then((result) => {
            if (result.isConfirmed) {
                var ids = [];
                checked.each(function () {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Mohon Tunggu',
                    text: 'Sedang menghapus bank soal...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '<?= site_url('cbtbanksoal/deleteallbank') ?>',
                    type: 'POST',
                    data: {
                        checked: ids,
                        [csrfName]: csrfHash
                    },
                    success: function (respon) {
                        if (respon.status) {
                            Swal.fire('Berhasil', respon.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', respon.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    /**
     * Membersihkan teks HTML untuk Word Export (merapikan paragraf, gambar, dan spasi)
     */
    function cleanWordHtml(html) {
        if (!html) return '';
        let str = html.toString();
        const appBase = (typeof base_url !== 'undefined') ? base_url : '<?= base_url() ?>/';
        // 1. Fix relative image urls to absolute
        str = str.replace(/<img\b([^>]*?)\bsrc=["']([^"']+)["']/gi, function(match, prefix, src) {
            if (!src || src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:') || src.startsWith('//')) {
                return match;
            }
            let cleanSrc = src.replace(/^\.?\//, '');
            let finalSrc = cleanSrc.startsWith('uploads/') ? (appBase + cleanSrc) : (appBase + 'uploads/' + cleanSrc);
            return '<img' + prefix + 'src="' + finalSrc + '"';
        });
        // 2. Normalize img styling for docx (respect original size while capping max-width to printable margins)
        str = str.replace(/<img\b([^>]*?)>/gi, function(match, attrs) {
            if (/style=["'][^"']*["']/i.test(attrs)) {
                return match.replace(/style=["']([^"']*)["']/i, function(m, styleContent) {
                    let newStyle = styleContent
                        .replace(/max-width\s*:[^;]+;?/gi, '')
                        .replace(/height\s*:[^;]+;?/gi, '')
                        .trim();
                    if (newStyle && !newStyle.endsWith(';')) newStyle += ';';
                    newStyle += ' max-width: 460px; height: auto; object-fit: contain; margin: 4px 0; vertical-align: middle;';
                    return 'style="' + newStyle + '"';
                });
            } else {
                return '<img' + attrs + ' style="max-width: 460px; height: auto; object-fit: contain; margin: 4px 0; vertical-align: middle;">';
            }
        });
        // 3. Clean multiple consecutive empty paragraphs
        str = str.replace(/(<p[^>]*>\s*(<br\s*\/?>|&nbsp;|\s)*<\/p>\s*){2,}/gi, '<p style="margin: 3px 0;">&nbsp;</p>');
        return str;
    }

    /**
     * Membersihkan opsi jawaban (menghilangkan wrapper <p> tunggal agar sejajar huruf A. B. C.)
     */
    function cleanOptionWordHtml(html) {
        if (!html) return '';
        let str = cleanWordHtml(html).trim();
        // Jika hanya dibungkus 1 tag <p>...</p>, buka isinya agar sejajar
        if (str.startsWith('<p>') && str.endsWith('</p>') && (str.match(/<p>/g) || []).length === 1) {
            str = str.substring(3, str.length - 4).trim();
        }
        return str;
    }

    /**
     * Mengonversi semua gambar di dalam container ke Base64 canvas data URI
     * agar tersemat langsung ke dalam paket file DOCX (offline ready)
     */
    async function prepareImagesForWordExport($container) {
        const images = $container.find('img').toArray();
        for (const img of images) {
            try {
                if (img.src && !img.src.startsWith('data:image')) {
                    const base64 = await convertImgToBase64(img);
                    if (base64) {
                        img.src = base64;
                    }
                }
            } catch (e) {
                console.warn('Image convert warning:', e);
            }
        }
    }

    function convertImgToBase64(img) {
        return new Promise((resolve) => {
            if (img.complete && img.naturalWidth) {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                    return;
                } catch (e) {
                    // Fallback to cross-origin image load
                }
            }

            const newImg = new Image();
            newImg.crossOrigin = 'Anonymous';
            newImg.onload = function () {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = newImg.naturalWidth;
                    canvas.height = newImg.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(newImg, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                } catch (e) {
                    resolve(null);
                }
            };
            newImg.onerror = function () {
                resolve(null);
            };
            newImg.src = img.src;
        });
    }

    /**
     * Menghasilkan dokumen Word standar Naskah Ujian Nasional / Asesmen Sekolah Offline
     */
    function generateExamPaperHtml(result) {
        if (!result || !result.bank) return '';
        const bank = result.bank;
        const allSoals = result.soals || [];
        const kelasList = result.kelas || [];
        const setting = result.setting || {};
        const tp = result.tp || {};
        const smt = result.smt || {};

        // Ambil nama kelas
        let kelasNames = '';
        if (bank.bank_kelas && Array.isArray(bank.bank_kelas)) {
            kelasNames = bank.bank_kelas
                .map(jk => {
                    const found = kelasList.find(k => k.id_kelas?.toString() === jk.kelas_id?.toString());
                    return found ? found.nama_kelas : null;
                })
                .filter(Boolean)
                .join(', ');
        }
        if (!kelasNames && bank.bank_level) {
            kelasNames = 'Kelas ' + bank.bank_level;
        }

        // Filter soal aktif / ditampilkan
        let soals = allSoals.filter(s => s.tampilkan === '1' || s.tampilkan === 1);
        if (soals.length === 0) soals = allSoals;

        // Pisahkan per jenis
        const pgSoals = soals.filter(s => s.jenis == 1);
        const pgkSoals = soals.filter(s => s.jenis == 2);
        const matchSoals = soals.filter(s => s.jenis == 3);
        const isianSoals = soals.filter(s => s.jenis == 4);
        const esaiSoals = soals.filter(s => s.jenis == 5);
        const urutanSoals = soals.filter(s => s.jenis == 6);
        const clozeSoals = soals.filter(s => s.jenis == 7);

        // Info Kop Sekolah
        const namaSekolah = (setting.sekolah || setting.nama_sekolah || 'SATUAN PENDIDIKAN').toUpperCase();
        const alamatSekolah = [setting.alamat, setting.desa, setting.kecamatan, setting.kota, setting.provinsi].filter(Boolean).join(', ');
        const kontakSekolah = [
            setting.telp ? 'Telp: ' + setting.telp : '',
            setting.email ? 'Email: ' + setting.email : '',
            (setting.web || setting.website) ? 'Web: ' + (setting.web || setting.website) : ''
        ].filter(Boolean).join(' | ');

        let logoKiriHtml = '';
        let logoKiriSrc = setting.logo_kiri || setting.logo || '';
        if (logoKiriSrc) {
            if (!logoKiriSrc.startsWith('http') && !logoKiriSrc.startsWith('data:')) {
                logoKiriSrc = base_url + (logoKiriSrc.startsWith('/') ? logoKiriSrc.substring(1) : logoKiriSrc);
            }
            logoKiriHtml = `<img src="${logoKiriSrc}" style="max-height: 70px; max-width: 75px; object-fit: contain;" alt="Logo"/>`;
        }

        let logoKananHtml = '';
        let logoKananSrc = setting.logo_kanan || '';
        if (logoKananSrc) {
            if (!logoKananSrc.startsWith('http') && !logoKananSrc.startsWith('data:')) {
                logoKananSrc = base_url + (logoKananSrc.startsWith('/') ? logoKananSrc.substring(1) : logoKananSrc);
            }
            logoKananHtml = `<img src="${logoKananSrc}" style="max-height: 70px; max-width: 75px; object-fit: contain;" alt="Logo"/>`;
        }

        const tpTeks = tp.tahun || tp.nama_tp || '2025/2026';
        let smtTeks = smt.smt_nama || smt.nama_smt || '';
        if (!smtTeks) {
            smtTeks = (bank.id_smt == '1' || bank.id_smt == 1) ? 'SEMESTER GANJIL' : 'SEMESTER GENAP';
        } else {
            smtTeks = smtTeks.toUpperCase();
        }

        let kopHtml = '';
        let kopSekolahSrc = setting.kopsekolah || '';
        if (kopSekolahSrc && kopSekolahSrc.trim() !== '') {
            let appBase = (typeof base_url !== 'undefined') ? base_url : '<?= base_url() ?>/';
            if (!kopSekolahSrc.startsWith('http') && !kopSekolahSrc.startsWith('data:')) {
                kopSekolahSrc = appBase + (kopSekolahSrc.startsWith('/') ? kopSekolahSrc.substring(1) : kopSekolahSrc);
            }
            kopHtml = `
                <!-- HEADER BANNER KOP SEKOLAH DARI PENGATURAN -->
                <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px;">
                    <tr>
                        <td style="text-align: center; vertical-align: middle; border: none; padding: 0;">
                            <img src="${kopSekolahSrc}" style="width: 100%; max-width: 700px; height: auto;" alt="KOP SEKOLAH"/>
                        </td>
                    </tr>
                </table>
            `;
        } else {
            let header1Html = setting.header_1 ? `<div style="font-size: 10pt; font-weight: bold; text-transform: uppercase;">${setting.header_1}</div>` : '';
            let header2Html = setting.header_2 ? `<div style="font-size: 10.5pt; font-weight: bold; text-transform: uppercase;">${setting.header_2}</div>` : '';
            let namaSekolahHtml = `<div style="font-size: 13.5pt; font-weight: bold; text-transform: uppercase; margin: 2px 0;">${namaSekolah}</div>`;
            let alamatHtml = (setting.header_3 || alamatSekolah) ? `<div style="font-size: 9pt; font-style: italic; margin-bottom: 1px;">${setting.header_3 || alamatSekolah}</div>` : '';
            let kontakHtml = (setting.header_4 || kontakSekolah) ? `<div style="font-size: 8.5pt; color: #333;">${setting.header_4 || kontakSekolah}</div>` : '';

            kopHtml = `
                <!-- KOP SURAT RESMI SEKOLAH (TEKS + LOGO) -->
                <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 2px;">
                    <tr>
                        <td style="width: 80px; text-align: center; vertical-align: middle; border: none; padding: 2px;">
                            ${logoKiriHtml}
                        </td>
                        <td style="text-align: center; vertical-align: middle; border: none; padding: 2px;">
                            ${header1Html}
                            ${header2Html}
                            ${namaSekolahHtml}
                            ${alamatHtml}
                            ${kontakHtml}
                        </td>
                        <td style="width: 80px; text-align: center; vertical-align: middle; border: none; padding: 2px;">
                            ${logoKananHtml}
                        </td>
                    </tr>
                </table>

                <!-- GARIS GANDA PEMBATAS KOP -->
                <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px;">
                    <tr>
                        <td style="border-top: 2.5px solid #000; border-bottom: 1px solid #000; height: 3px; border-left: none; border-right: none; padding: 0;"></td>
                    </tr>
                </table>
            `;
        }

        let docHtml = `
        <div style="font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; line-height: 1.25;">
            ${kopHtml}

            <!-- JUDUL NASKAH SOAL -->
            <div style="text-align: center; margin-bottom: 10px;">
                <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; text-decoration: underline;">
                    NASKAH SOAL ASESMEN / UJIAN SEKOLAH TERTULIS
                </div>
                <div style="font-size: 10.5pt; font-weight: bold; margin-top: 2px;">
                    TAHUN PELAJARAN ${tpTeks} - ${smtTeks}
                </div>
            </div>

            <!-- TABEL IDENTITAS UJIAN & PESERTA (2 KOLOM BERSANDING) -->
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt;">
                <tr>
                    <td style="width: 50%; border: 1px solid #000; vertical-align: top; padding: 6px 8px;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr>
                                <td style="width: 120px; vertical-align: top; border: none; padding: 2px 0;">Mata Pelajaran</td>
                                <td style="width: 10px; vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${bank.nama_mapel || '-'}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Kelas / Tingkat</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">${kelasNames}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Alokasi Waktu</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">${bank.alokasi_waktu || 90} Menit</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Guru Pengampu</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">${bank.nama_guru || '-'}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; border: 1px solid #000; vertical-align: top; padding: 6px 8px;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr>
                                <td style="width: 110px; vertical-align: top; border: none; padding: 2px 0;">Hari / Tanggal</td>
                                <td style="width: 10px; vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Waktu / Sesi</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Nama Peserta</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">No. Ujian / Ruang</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- KOTAK PETUNJUK UMUM STANDAR UJIAN -->
            <div style="border: 1px solid #000; padding: 6px 10px; margin-bottom: 14px; font-size: 9pt; background-color: #fcfcfc;">
                <div style="font-weight: bold; margin-bottom: 3px;">PETUNJUK UMUM:</div>
                <ol style="margin: 0; padding-left: 18px;">
                    <li style="margin-bottom: 1px;">Isikan identitas Anda pada lembar jawaban yang telah disediakan secara lengkap dan benar.</li>
                    <li style="margin-bottom: 1px;">Periksa dan bacalah setiap butir soal dengan saksama sebelum Anda memberikan jawaban.</li>
                    <li style="margin-bottom: 1px;">Laporkan kepada pengawas ujian apabila terdapat lembar soal yang kurang jelas, rusak, atau tidak lengkap.</li>
                    <li style="margin-bottom: 1px;">Dahulukan menjawab soal-soal yang Anda anggap mudah.</li>
                    <li style="margin-bottom: 1px;">Tidak diperkenankan menggunakan kalkulator, ponsel (HP), atau alat bantu lainnya kecuali ada petunjuk khusus.</li>
                    <li style="margin-bottom: 1px;">Periksalah kembali seluruh lembar jawaban Anda sebelum diserahkan kepada pengawas ujian.</li>
                </ol>
            </div>
        `;

        // BAGIAN A: SOAL PILIHAN GANDA
        if (pgSoals.length > 0) {
            const jmlOpsi = parseInt(bank.opsi || 5);
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 14px; margin-bottom: 3px;">A. SOAL PILIHAN GANDA</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Pilihlah salah satu jawaban yang paling tepat dengan memberi tanda silang (X) atau menghitamkan bulatan pada huruf A, B, C, D, atau E pada lembar jawaban yang telah disediakan!
                </div>
            `;

            pgSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                const opsiA = cleanOptionWordHtml(s.opsi_a);
                const opsiB = cleanOptionWordHtml(s.opsi_b);
                const opsiC = cleanOptionWordHtml(s.opsi_c);
                const opsiD = cleanOptionWordHtml(s.opsi_d);
                const opsiE = cleanOptionWordHtml(s.opsi_e);

                let opsiRows = `
                    <tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">A.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiA}</td></tr>
                    <tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">B.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiB}</td></tr>
                    <tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">C.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiC}</td></tr>
                `;
                if (jmlOpsi >= 4 || opsiD) {
                    opsiRows += `<tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">D.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiD}</td></tr>`;
                }
                if (jmlOpsi >= 5 || opsiE) {
                    opsiRows += `<tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">E.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiE}</td></tr>`;
                }

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 4px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 2px;">
                                    ${opsiRows}
                                </table>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN B: SOAL PILIHAN GANDA KOMPLEKS
        if (pgkSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">B. SOAL PILIHAN GANDA KOMPLEKS</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Pilihlah jawaban yang benar (jawaban benar dapat lebih dari satu) dengan memberi tanda centang (✓) pada kotak di depan setiap pernyataan yang sesuai!
                </div>
            `;

            pgkSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                let opsis = s.opsi_a;
                if (typeof opsis === 'string') {
                    try { opsis = JSON.parse(opsis); } catch(e) { opsis = {}; }
                }
                opsis = opsis || {};

                let opsiRows = '';
                const keys = Object.keys(opsis).sort();
                keys.forEach(k => {
                    const txt = cleanOptionWordHtml(opsis[k]);
                    opsiRows += `
                        <tr>
                            <td style="width: 32px; vertical-align: top; border: none; padding: 2px 0; font-family: monospace; font-size: 10.5pt;">[ &nbsp; ]</td>
                            <td style="width: 22px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${k.toUpperCase()}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">${txt}</td>
                        </tr>
                    `;
                });

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 4px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 2px;">
                                    ${opsiRows}
                                </table>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN C: SOAL MENJODOHKAN
        if (matchSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">C. SOAL MENJODOHKAN</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Pasangkanlah pernyataan/pertanyaan pada Kolom Kiri dengan pilihan jawaban yang paling tepat pada Kolom Kanan!
                </div>
            `;

            matchSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                let rawJawaban = s.jawaban;
                if (typeof rawJawaban === 'string') {
                    try { rawJawaban = JSON.parse(rawJawaban); } catch(e) { rawJawaban = {}; }
                }
                const grid = rawJawaban?.jawaban || rawJawaban || [];
                
                let matchTableRows = '';
                if (Array.isArray(grid) && grid.length > 1) {
                    const rowHeaders = [];
                    const colHeaders = [];
                    if (grid[0] && Array.isArray(grid[0])) {
                        for (let c = 1; c < grid[0].length; c++) {
                            if (grid[0][c]) colHeaders.push(grid[0][c]);
                        }
                    }
                    for (let r = 1; r < grid.length; r++) {
                        if (grid[r] && grid[r][0]) rowHeaders.push(grid[r][0]);
                    }

                    const maxRows = Math.max(rowHeaders.length, colHeaders.length);
                    for (let m = 0; m < maxRows; m++) {
                        const leftText = rowHeaders[m] ? `<b>${m + 1}.</b> ${cleanOptionWordHtml(rowHeaders[m])}` : '';
                        const rightLetter = String.fromCharCode(65 + m);
                        const rightText = colHeaders[m] ? `<b>${rightLetter}.</b> ${cleanOptionWordHtml(colHeaders[m])}` : '';
                        matchTableRows += `
                            <tr>
                                <td style="width: 50%; border: 1px solid #000; padding: 4px 6px; vertical-align: top;">${leftText}</td>
                                <td style="width: 50%; border: 1px solid #000; padding: 4px 6px; vertical-align: top;">${rightText}</td>
                            </tr>
                        `;
                    }
                }

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 10px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 4px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                ${matchTableRows ? `
                                    <table style="width: 96%; border: 1px solid #000; border-collapse: collapse; font-size: 9.5pt; margin-top: 3px;">
                                        <thead>
                                            <tr style="background-color: #f2f2f2;">
                                                <th style="width: 50%; border: 1px solid #000; padding: 4px 6px; text-align: center;">Kolom A (Pernyataan / Soal)</th>
                                                <th style="width: 50%; border: 1px solid #000; padding: 4px 6px; text-align: center;">Kolom B (Pilihan Pasangan)</th>
                                            </tr>
                                        </thead>
                                        <tbody>${matchTableRows}</tbody>
                                    </table>
                                ` : ''}
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN D: SOAL ISIAN SINGKAT
        if (isianSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">D. SOAL ISIAN SINGKAT</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Isilah titik-titik di bawah ini dengan jawaban yang singkat, tepat, dan benar!
                </div>
            `;

            isianSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 3px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <div style="margin-top: 4px; font-style: italic; color: #333;">
                                    Jawaban: ................................................................................................................................................
                                </div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN E: SOAL URAIAN / ESSAY
        if (esaiSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">E. SOAL URAIAN / ESSAY</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Jawablah pertanyaan-pertanyaan berikut dengan uraian yang jelas, sistematis, dan terperinci pada lembar jawaban yang tersedia!
                </div>
            `;

            esaiSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 6px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN F: SOAL URUTAN (JIKA ADA)
        if (urutanSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">F. SOAL MENGURUTKAN (SEQUENCING)</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Susunlah butir-butir pernyataan/langkah berikut menjadi urutan yang runtut, logis, dan benar!
                </div>
            `;

            urutanSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                let parsed = s.opsi_a;
                if (typeof parsed === 'string') {
                    try { parsed = JSON.parse(parsed); } catch(e) { parsed = {}; }
                }
                const items = parsed?.items || [];
                let itemsList = '';
                items.forEach((it, iIdx) => {
                    itemsList += `<li>${cleanOptionWordHtml(it)}</li>`;
                });

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 10px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 3px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <ul style="margin: 2px 0 6px 18px; padding: 0;">${itemsList}</ul>
                                <div style="font-style: italic; color: #333;">
                                    Urutan yang benar: ..........................................................................................................
                                </div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN G: ISIAN RUMPANG (JIKA ADA)
        if (clozeSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">G. SOAL ISIAN RUMPANG (CLOZE TEST)</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Lengkapilah bagian kalimat/paragraf yang rumpang di bawah ini dengan kata atau frasa yang tepat!
                </div>
            `;

            clozeSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 3px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // ============================================================
        // HALAMAN 2: LEMBAR JAWABAN SISWA (STUDENT ANSWER SHEET)
        // ============================================================
        docHtml += `
            <div style="page-break-before: always; margin-top: 20px;"></div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 10px;">
                <tr>
                    <td style="text-align: center; padding: 6px; background-color: #f2f2f2;">
                        <div style="font-weight: bold; font-size: 11.5pt; text-transform: uppercase;">LEMBAR JAWABAN UJIAN TERTULIS (LJU)</div>
                        <div style="font-size: 9.5pt; font-weight: bold;">${namaSekolah} - TAHUN PELAJARAN ${tpTeks}</div>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt;">
                <tr>
                    <td style="width: 58%; border: 1px solid #000; padding: 6px 8px; vertical-align: top;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="width: 105px; padding: 2px 0;">Nama Siswa</td><td style="width: 10px;">:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">No. Peserta / NIS</td><td>:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">Kelas / Ruang</td><td>:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">Mata Pelajaran</td><td>:</td><td><b>${bank.nama_mapel || '-'}</b></td></tr>
                            <tr><td style="padding: 2px 0;">Hari / Tanggal</td><td>:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">Tanda Tangan</td><td>:</td><td>............................................................</td></tr>
                        </table>
                    </td>
                    <td style="width: 42%; border: 1px solid #000; padding: 6px 8px; vertical-align: top; text-align: center;">
                        <div style="font-weight: bold; margin-bottom: 3px; font-size: 9pt;">KOLOM PENILAIAN GURU</div>
                        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 8.5pt; margin-top: 2px;">
                            <tr style="background-color: #f2f2f2;">
                                <th style="border: 1px solid #000; padding: 3px;">Nilai PG</th>
                                <th style="border: 1px solid #000; padding: 3px;">Nilai Uraian</th>
                                <th style="border: 1px solid #000; padding: 3px;">Nilai Akhir</th>
                                <th style="border: 1px solid #000; padding: 3px;">Paraf Guru</th>
                            </tr>
                            <tr style="height: 38px;">
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 4px;">I. LEMBAR JAWABAN PILIHAN GANDA</div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 8.5pt; text-align: center; margin-bottom: 12px;">
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 1 - 10</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 11 - 20</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 21 - 30</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 31 - 40</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 41 - 50</th>
                </tr>
        `;

        const jmlOpsiLjk = parseInt(bank.opsi || 5);
        let ljkLetters = 'A &nbsp;B &nbsp;C &nbsp;D';
        if (jmlOpsiLjk >= 5) ljkLetters += ' &nbsp;E';

        for (let r = 1; r <= 10; r++) {
            docHtml += `<tr>`;
            for (let c = 0; c < 5; c++) {
                const qNum = r + (c * 10);
                docHtml += `
                    <td style="border: 1px solid #000; padding: 3px 4px; text-align: left;">
                        <b>${qNum < 10 ? '0' + qNum : qNum}.</b> [ ${ljkLetters} ]
                    </td>
                `;
            }
            docHtml += `</tr>`;
        }

        docHtml += `
            </table>

            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 4px;">II. LEMBAR JAWABAN ISIAN SINGKAT & URAIAN</div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 9pt;">
                <tr>
                    <td style="border: 1px solid #000; height: 160px; vertical-align: top; padding: 6px;">
                        <div style="font-style: italic; color: #888; margin-bottom: 4px;">Tuliskan jawaban Anda di bawah ini:</div>
                    </td>
                </tr>
            </table>

            <!-- ============================================================ -->
            <!-- HALAMAN 3: KUNCI JAWABAN & PEDOMAN PENSKORAN (PEGANGAN GURU) -->
            <!-- ============================================================ -->
            <div style="page-break-before: always; margin-top: 20px;"></div>
            <div style="text-align: center; margin-bottom: 10px;">
                <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; text-decoration: underline;">
                    KUNCI JAWABAN & PEDOMAN PENSKORAN
                </div>
                <div style="font-size: 9.5pt; font-weight: bold; margin-top: 2px;">
                    (DOKUMEN RAHASIA - KHUSUS PEGANGAN GURU / PENGUJI)
                </div>
                <div style="font-size: 9.5pt; margin-top: 2px;">
                    MATA PELAJARAN: <b>${bank.nama_mapel || '-'}</b> | KELAS: <b>${bank.bank_level || '-'}</b> | KODE BANK: <b>${bank.bank_kode || '-'}</b>
                </div>
            </div>
        `;

        // Rekap Kunci Pilihan Ganda (Grid 1-50)
        if (pgSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 10.5pt; margin-top: 10px; margin-bottom: 4px;">A. Kunci Jawaban Pilihan Ganda:</div>
                <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 8.5pt; text-align: center; margin-bottom: 12px;">
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                    </tr>
            `;

            const pgKeyMap = {};
            pgSoals.forEach(s => {
                pgKeyMap[s.nomor_soal] = (s.jawaban || '').toUpperCase();
            });

            const maxPgNum = Math.max(pgSoals.length, 10);
            const totalRowsGrid = Math.ceil(maxPgNum / 5);

            for (let gr = 0; gr < totalRowsGrid; gr++) {
                docHtml += `<tr>`;
                for (let gc = 0; gc < 5; gc++) {
                    const qn = (gr + 1) + (gc * totalRowsGrid);
                    const kVal = pgKeyMap[qn] || '-';
                    docHtml += `
                        <td style="border: 1px solid #000; padding: 3px; background-color: #fafafa; font-weight: bold;">${qn}</td>
                        <td style="border: 1px solid #000; padding: 3px; font-weight: bold; color: #004085;">${kVal}</td>
                    `;
                }
                docHtml += `</tr>`;
            }

            docHtml += `</table>`;
        }

        // Rekap Kunci Jenis Lainnya
        if (pgkSoals.length > 0 || matchSoals.length > 0 || isianSoals.length > 0 || esaiSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 10.5pt; margin-top: 10px; margin-bottom: 4px;">B. Kunci Jawaban Non-Pilihan Ganda & Rubrik Penilaian:</div>
                <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 12px;">
                    <thead>
                        <tr style="background-color: #f2f2f2; font-weight: bold;">
                            <th style="width: 45px; border: 1px solid #000; padding: 4px; text-align: center;">No</th>
                            <th style="width: 130px; border: 1px solid #000; padding: 4px; text-align: center;">Bentuk Soal</th>
                            <th style="border: 1px solid #000; padding: 4px; text-align: center;">Kunci Jawaban / Kriteria Penilaian</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            pgkSoals.forEach(s => {
                let ans = s.jawaban;
                if (Array.isArray(ans)) ans = ans.join(', ').toUpperCase();
                else if (typeof ans === 'string') ans = ans.toUpperCase();
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">PG Kompleks</td>
                        <td style="border: 1px solid #000; padding: 4px;"><b>${ans || '-'}</b></td>
                    </tr>
                `;
            });

            matchSoals.forEach(s => {
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">Menjodohkan</td>
                        <td style="border: 1px solid #000; padding: 4px;">Pasangan benar sesuai kunci kisi-kisi</td>
                    </tr>
                `;
            });

            isianSoals.forEach(s => {
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">Isian Singkat</td>
                        <td style="border: 1px solid #000; padding: 4px;"><b>${s.jawaban || '-'}</b></td>
                    </tr>
                `;
            });

            esaiSoals.forEach(s => {
                const jwb = s.jawaban ? cleanWordHtml(s.jawaban) : '';
                const pmb = s.pembahasan ? cleanWordHtml(s.pembahasan) : '';
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">Uraian / Essay</td>
                        <td style="border: 1px solid #000; padding: 4px;">
                            ${jwb ? `<div><b>Jawaban Model:</b> ${jwb}</div>` : ''}
                            ${pmb ? `<div style="margin-top: 2px; color: #444;"><b>Rubrik/Pembahasan:</b> ${pmb}</div>` : (jwb ? '' : 'Sesuai ketepatan uraian dan rubrik guru')}
                        </td>
                    </tr>
                `;
            });

            docHtml += `</tbody></table>`;
        }

        // Rekap Bobot Penilaian
        docHtml += `
            <div style="font-weight: bold; font-size: 10pt; margin-top: 10px; margin-bottom: 4px;">C. Rekap Bobot & Pedoman Penskoran:</div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 9pt; margin-bottom: 14px;">
                <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    <th style="border: 1px solid #000; padding: 4px;">Bentuk Soal</th>
                    <th style="border: 1px solid #000; padding: 4px;">Jumlah Soal</th>
                    <th style="border: 1px solid #000; padding: 4px;">Bobot (%)</th>
                    <th style="border: 1px solid #000; padding: 4px;">Poin per Nomor</th>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Pilihan Ganda</td>
                    <td style="border: 1px solid #000; padding: 3px;">${pgSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_pg || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_pg > 0 && pgSoals.length > 0) ? (bank.bobot_pg / pgSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">PG Kompleks</td>
                    <td style="border: 1px solid #000; padding: 3px;">${pgkSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_kompleks || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_kompleks > 0 && pgkSoals.length > 0) ? (bank.bobot_kompleks / pgkSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Menjodohkan</td>
                    <td style="border: 1px solid #000; padding: 3px;">${matchSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_jodohkan || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_jodohkan > 0 && matchSoals.length > 0) ? (bank.bobot_jodohkan / matchSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Isian Singkat</td>
                    <td style="border: 1px solid #000; padding: 3px;">${isianSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_isian || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_isian > 0 && isianSoals.length > 0) ? (bank.bobot_isian / isianSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Uraian / Essay</td>
                    <td style="border: 1px solid #000; padding: 3px;">${esaiSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_esai || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_esai > 0 && esaiSoals.length > 0) ? (bank.bobot_esai / esaiSoals.length).toFixed(2) : '-'}</td>
                </tr>
            </table>

            <!-- KOLOM TANDA TANGAN PENGESAHAN -->
            <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 20px; font-size: 10pt;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 4px;">
                        <div>Mengetahui,</div>
                        <div>Kepala Sekolah</div>
                        <div style="height: 50px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">${setting.kepsek || '................................................'}</div>
                        <div>NIP. ${setting.nip || setting.nip_kepsek || '-'}</div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 4px;">
                        <div>${setting.kota || '....................'}, .................................... 20...</div>
                        <div>Guru Mata Pelajaran</div>
                        <div style="height: 50px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">${bank.nama_guru || '................................................'}</div>
                        <div>NIP. ${bank.nip_guru || '-'}</div>
                    </td>
                </tr>
            </table>
        </div>
        `;

        return docHtml;
    }

    async function getSoal(id) {
        Swal.fire({
            title: "Menyiapkan Dokumen Word",
            html: "Sedang mengambil data dan menyusun naskah soal...<br><small class='text-muted'>Memproses butir soal, lembar jawaban, dan kunci jawaban</small>",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '<?= site_url('cbtbanksoal/get_detail') ?>',
            type: 'POST',
            data: { id_bank: id },
            dataType: 'json',
            success: async function (result) {
                if (!result || !result.bank) {
                    Swal.fire({
                        title: "Gagal Mengunduh",
                        text: (result && result.message) ? result.message : "Data bank soal tidak ditemukan.",
                        icon: "error"
                    });
                    return;
                }

                try {
                    const bank = result.bank;
                    const bankKode = (bank.bank_kode || 'Ujian').replace(/[/\\?%*:|"<>]/g, '-');
                    const mapel = (bank.nama_mapel || 'Mapel').replace(/[/\\?%*:|"<>]/g, '-');
                    const level = bank.bank_level || '';
                    const titleDoc = `Naskah_Soal_${bankKode}_${mapel}_Kls_${level}.docx`;

                    const forExportDiv = document.getElementById('for-export');
                    if (forExportDiv) {
                        forExportDiv.innerHTML = generateExamPaperHtml(result);
                    }

                    // Pre-convert all images inside #for-export to base64
                    await prepareImagesForWordExport($('#for-export'));

                    const contentDocument = $('#for-export').convertToHtmlFile('Naskah Soal Ujian', '');
                    const content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;

                    const converted = htmlDocx.asBlob(content, {
                        orientation: 'portrait',
                        size: 'A4',
                        margins: { top: 1134, bottom: 1134, left: 1417, right: 1417 }
                    });

                    saveAs(converted, titleDoc);
                    Swal.close();

                    Swal.fire({
                        title: "Unduhan Berhasil!",
                        html: `Naskah soal <b>${bankKode}</b> berhasil dibuat dalam format Word .`,
                        icon: "success",
                        timer: 3500,
                        showConfirmButton: false
                    });
                } catch (err) {
                    console.error("Error generating Word document:", err);
                    Swal.fire({
                        title: "Gagal Memproses Dokumen",
                        text: err.message || "Terjadi kesalahan saat menyusun dokumen Word.",
                        icon: "error"
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching detail:", error);
                Swal.fire({
                    title: "Kesalahan Jaringan / Server",
                    text: "Gagal memuat data bank soal dari server.",
                    icon: "error"
                });
            }
        });
    }

    function editBank(id) {
        $.ajax({
            url: '<?= site_url('cbtbanksoal/checkBankStatus/') ?>' + id,
            type: 'GET',
            success: function (response) {
                if (response.status) {
                    if (response.warning) {
                        // Admin tetap boleh mengedit, hanya diberi tahu risikonya.
                        showCenterToast('warning', response.warning);
                        setTimeout(function () {
                            window.location.href = '<?= site_url('cbtbanksoal/edit/') ?>' + id;
                        }, 1500);
                        return;
                    }
                    window.location.href = '<?= site_url('cbtbanksoal/edit/') ?>' + id;
                } else {
                    showCenterToast('error', response.message);
                }
            },
            error: function () {
                showCenterToast('error', 'Terjadi kesalahan sistem');
            }
        });
    }

    function showCenterToast(type, message) {
        var isSuccess = (type === 'success');
        var isWarning = (type === 'warning');
        $('#toast-title').text(isSuccess ? 'Sukses' : (isWarning ? 'Perhatian' : 'Gagal'));
        $('#toast-message').text(message);

        $('#centerToast .toast-header').removeClass('bg-success bg-danger bg-warning text-white text-dark');
        $('#centerToast .btn-close').removeClass('btn-close-white');

        if (isSuccess) {
            $('#centerToast .toast-header').addClass('bg-success text-white');
            $('#centerToast .btn-close').addClass('btn-close-white');
        } else if (isWarning) {
            $('#centerToast .toast-header').addClass('bg-warning text-dark');
        } else {
            $('#centerToast .toast-header').addClass('bg-danger text-white');
            $('#centerToast .btn-close').addClass('btn-close-white');
        }

        var toastEl = document.getElementById('centerToast');
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
</script>

<!-- Modal Restore Bank Soal -->
<div class="modal fade" id="modalRestoreBank" tabindex="-1" aria-labelledby="modalRestoreBankLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-gradient-info text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalRestoreBankLabel">
                    <i class="bi bi-file-earmark-zip me-2"></i>Restore Bank Soal (ZIP)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRestoreBank" action="<?= site_url('cbtbanksoal/do_restore') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Unggah berkas kompresi <b>.ZIP</b> hasil backup Bank Soal. Seluruh metadata, 7 jenis soal, kunci jawaban, dan berkas gambar akan di-restore secara utuh.
                    </div>

                    <div class="mb-3">
                        <label for="backup_file" class="form-label fw-bold">Pilih Berkas Backup (.zip) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="backup_file" name="backup_file" accept=".zip" required>
                    </div>

                    <div class="mb-3">
                        <label for="restore_id_mapel" class="form-label fw-bold">Mata Pelajaran Target (Opsional)</label>
                        <select name="id_mapel" id="restore_id_mapel" class="form-select">
                            <option value="">-- Otomatis Sesuai Manifest / Default --</option>
                            <?php if (!empty($mapels)): foreach ($mapels as $mapelId => $mapelVal): ?>
                                <option value="<?= is_object($mapelVal) ? $mapelVal->id_mapel : $mapelId ?>"><?= esc(is_object($mapelVal) ? $mapelVal->nama_mapel : $mapelVal) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <div class="form-text small">Jika dikosongkan, sistem akan mencoba mencocokkan kode mapel di dalam manifest backup.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 px-4 py-3">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white btn-sm rounded-pill px-4 fw-bold shadow-sm" id="btnDoRestore">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Mulai Restore
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formRestoreBank').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = $('#btnDoRestore');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses Restore...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Mulai Restore');
                if (res.status) {
                    $('#modalRestoreBank').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Restore Berhasil!',
                        text: res.message,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Restore',
                        text: res.message || 'Terjadi kesalahan saat restore.'
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Mulai Restore');
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Server',
                    text: 'Gagal terhubung ke server (' + xhr.status + ').'
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
