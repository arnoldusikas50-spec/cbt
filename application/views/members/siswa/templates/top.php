<div class="header-fixed-banner">
    <div class="brand-section">
        <img src="<?= base_url() ?>assets/img/logo_cbt.png" class="brand-logo" alt="Logo">
    </div>

    <div class="user-section-header">
        <div class="user-info-box">
            <div class="name"><?= $siswa->nama ?></div>
            <div class="meta"><?= $siswa->nisn ?> - <?= $siswa->kode_kelas ?></div>
            <div>
                <a href="javascript:void(0)" onclick="logout()" class="btn-logout-custom shadow-sm">Logout</a>
            </div>
        </div>
        <div class="user-avatar-wrapper">
            <?php if (!file_exists(FCPATH . $siswa->foto) || $siswa->foto == ""): ?>
                <img src="<?= base_url() ?>assets/img/siswa_icon.png" alt="Student"
                    style="width: 85%; height: auto; padding: 5px;">
            <?php else: ?>
                <img src="<?= base_url() ?><?= $siswa->foto ?>" alt="Student">
            <?php endif; ?>
        </div>
    </div>
</div>