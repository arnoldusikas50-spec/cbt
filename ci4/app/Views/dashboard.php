<?php
// Load templates
echo view('_templates/dashboard/_header', get_defined_vars());
?>

<!-- Content Main -->
<main class="app-main">
    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><?= $judul ?? 'Dashboard' ?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $judul ?? 'Dashboard' ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Welcome Message (Simple) -->
            <?php if (isset($user)): ?>
                <div class="callout callout-info shadow-sm mb-4">
                    <h5>Selamat Datang, <?= esc($user->nama_lengkap ?? session()->get('first_name')) ?>!</h5>
                    <p>
                        Anda login sebagai <strong><?= esc($user->role_name ?? 'User') ?></strong>.
                        Tahun Pelajaran: <strong><?= esc($tp_active->tahun ?? '-') ?></strong>,
                        Semester: <strong><?= esc($smt_active->smt ?? '-') ?></strong>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Stats Boxes -->
            <div class="row">
                <?php foreach ($info_box ?? [] as $box): ?>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon text-bg-<?= esc($box['box']) ?> shadow-sm">
                                <i class="bi bi-<?= esc($box['icon'] ?? 'app-indicator') ?>"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= esc($box['title']) ?></span>
                                <span class="info-box-number"><?= number_format($box['total'] ?? 0) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Ujian Statistics -->
            <?php if (!empty($ujian_box)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="mb-3"><i class="bi bi-mortarboard-fill me-2"></i>Status Pelaksanaan Ujian</h5>
                    </div>
                    <?php foreach ($ujian_box as $box): ?>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box shadow-sm border-start border-4 border-<?= esc($box['box']) ?>">
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted small fw-bold"><?= esc($box['title']) ?></span>
                                    <span class="info-box-number fs-4"><?= number_format($box['total'] ?? 0) ?></span>
                                </div>
                                <span class="info-box-icon text-<?= esc($box['box']) ?> opacity-25">
                                    <i class="bi bi-<?= esc($box['icon'] ?? 'display') ?>"></i>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php
// Load footer
echo view('_templates/dashboard/_footer', get_defined_vars());
?>