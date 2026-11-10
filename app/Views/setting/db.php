<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<style>
    .backup-progress {
        height: 30px;
        border-radius: 15px;
        overflow: hidden;
    }
    .backup-progress .progress-bar {
        font-size: 0.8rem;
        line-height: 30px;
        transition: width 0.4s ease;
    }
    .backup-btn-group .btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.5rem 1.25rem;
    }
    .backup-table th {
        background: #f0f4ff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
        color: #475569;
    }
</style>

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-database-gear text-primary me-2"></i>
                    <?= esc($judul ?? 'Backup & Restore') ?>
                </h3>
                <p class="text-muted small mb-0 fw-medium">Kelola backup database dan file uploads</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Backup & Restore</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-4">
                <!-- Action Buttons -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 backup-btn-group">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary shadow-sm" onclick="processBackup()">
                            <i class="bi bi-database-fill me-1"></i>Backup Semua
                        </button>
                        <button class="btn btn-info text-white shadow-sm" onclick="processBackupDb()">
                            <i class="bi bi-hdd-fill me-1"></i>Backup DB Saja
                        </button>
                        <button class="btn btn-success shadow-sm" onclick="processBackupFile()">
                            <i class="bi bi-folder-fill me-1"></i>Backup File Saja
                        </button>
                    </div>
                    <button class="btn btn-outline-danger shadow-sm" onclick="hapusBackup()" <?= empty($list) ? 'disabled' : '' ?>>
                        <i class="bi bi-trash3 me-1"></i>Hapus Semua
                    </button>
                </div>

                <!-- Progress -->
                <div class="progress backup-progress shadow-sm mb-3 d-none" id="backupProgress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                        role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                        style="width: 0%;">0%</div>
                </div>

                <!-- Info -->
                <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 small">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
                        <div>
                            <strong class="d-block mb-1">Panduan Backup:</strong>
                            <ol class="mb-0 ps-3">
                                <li>Jangan refresh/menutup halaman selama proses backup berlangsung</li>
                                <li>Jika backup gagal, gunakan cpanel/phpmyadmin untuk membackup database</li>
                                <li>Untuk backup file (gambar, foto, dll) backup manual folder Uploads</li>
                                <li class="text-danger fw-semibold">File backup mungkin dienkripsi (tergantung konfigurasi)</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <hr class="my-3">

                <h6 class="fw-bold mb-3">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Backup
                </h6>

                <?php if (empty($list)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        <p class="mb-0">Belum ada backup tersimpan.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 backup-table">
                            <thead>
                                <tr>
                                    <th class="text-center" width="50">No.</th>
                                    <th>Database/File</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Tanggal Backup</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                usort($list, function ($a, $b) { return $b['tgl'] <=> $a['tgl']; });
                                foreach ($list as $key => $value): ?>
                                    <tr>
                                        <td class="text-center fw-medium"><?= ($key + 1) ?></td>
                                        <td class="fw-medium"><?= esc($value['nama'] . '.' . $value['type']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3 py-1"><?= esc($value['size']) ?></span>
                                        </td>
                                        <td class="text-center"><?= date('d M Y H:i', $value['tgl']) ?></td>
                                        <td class="text-center">
                                            <div class="btn-action-group justify-content-center">
                                                <a href="<?= base_url('dbmanager/download/' . $value['src']) ?>"
                                                    download="<?= $value['src'] ?>"
                                                    class="btn btn-action btn-action-warning"
                                                    title="Download File Backup">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <button type="button" onclick="restore('<?= $value['src'] ?>')"
                                                    class="btn btn-action btn-action-magic"
                                                    title="Restore Database / File">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                                <button type="button" onclick="hapus('<?= $value['src'] ?>')"
                                                    class="btn btn-action btn-action-delete"
                                                    title="Hapus File Backup">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    const baseUrl = '<?= base_url() ?>'.replace(/\/+$/, '');

    function getCsrfData() {
        return { [csrfName]: csrfHash };
    }

    function showProgress(pct, msg) {
        const $bar = $('#backupProgress');
        $bar.removeClass('d-none');
        const $pb = $bar.find('.progress-bar');
        $pb.css('width', pct + '%').attr('aria-valuenow', pct).text(pct + '% ' + (msg || ''));
    }

    function processBackupDb() {
        Swal.fire({ title: "Backup Database", text: "Silahkan tunggu...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        showProgress(10, 'Memulai Backup Database...');
        $.ajax({
            type: "POST", url: baseUrl + '/dbmanager/backupdb', data: getCsrfData(),
            success: function (res) {
                if (res.csrf_hash) csrfHash = res.csrf_hash;
                showProgress(100, res.message || 'Selesai');
                Swal.fire({ title: "Berhasil", text: res.message || 'Backup berhasil', icon: "success" }).then(() => location.reload());
            },
            error: function (xhr) { Swal.fire("Gagal", xhr.statusText, "error"); }
        });
    }

    function processBackupFile() {
        Swal.fire({ title: "Backup File", text: "Silahkan tunggu...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        showProgress(10, 'Memulai Backup File...');
        $.ajax({
            type: "POST", url: baseUrl + '/dbmanager/backupdata', data: getCsrfData(),
            success: function (res) {
                if (res.csrf_hash) csrfHash = res.csrf_hash;
                showProgress(100, res.message || 'Selesai');
                Swal.fire({ title: "Berhasil", text: res.message || 'Backup berhasil', icon: "success" }).then(() => location.reload());
            },
            error: function (xhr) { Swal.fire("Gagal", xhr.statusText, "error"); }
        });
    }

    function backupDataInternal() {
        $.ajax({
            type: "POST", url: baseUrl + '/dbmanager/backupdata', data: getCsrfData(),
            success: function (res) {
                if (res.csrf_hash) csrfHash = res.csrf_hash;
                showProgress(100, res.message || 'Selesai');
                Swal.fire({ title: "Berhasil", text: "Semua file berhasil dibackup", icon: "success" }).then(() => location.reload());
            },
            error: function (xhr) { Swal.fire("Gagal", xhr.statusText, "error"); }
        });
    }

    function processBackup() {
        Swal.fire({ title: "Backup Database & File", text: "Silahkan tunggu...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        showProgress(5, 'Memulai...');
        $.ajax({
            type: "POST", url: baseUrl + '/dbmanager/backupdb', data: getCsrfData(),
            success: function (res) {
                if (res.csrf_hash) csrfHash = res.csrf_hash;
                showProgress(50, res.message || 'DB selesai');
                backupDataInternal();
            },
            error: function (xhr) { Swal.fire("Gagal", xhr.statusText, "error"); }
        });
    }

    function hapusBackup() {
        Swal.fire({
            title: "Hapus Semua Backup?",
            html: "<span class='text-danger fw-bold'>Semua backup akan dihapus dan tidak bisa dikembalikan!</span>",
            icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Hapus!"
        }).then(result => {
            if (!result.value) return;
            Swal.fire({ title: "Menghapus...", text: "Silahkan tunggu", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: baseUrl + '/dbmanager/del_backup', type: "POST", data: getCsrfData(),
                success: function (res) {
                    if (res.csrf_hash) csrfHash = res.csrf_hash;
                    if (res.status) {
                        Swal.fire({ title: "Berhasil", text: res.message || "Berhasil dihapus", icon: "success" }).then(() => location.reload());
                    } else {
                        Swal.fire({ title: "Gagal", text: res.message, icon: "error" });
                    }
                },
                error: function () { Swal.fire({ title: "Error", text: "Terjadi kesalahan", icon: "error" }); }
            });
        });
    }

    function hapus(src) {
        Swal.fire({
            title: "Anda yakin?",
            html: "File <b>" + src + "</b> akan dihapus!",
            icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
            confirmButtonText: "Hapus!"
        }).then(result => {
            if (!result.value) return;
            Swal.fire({ text: "Silahkan tunggu...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: baseUrl + '/dbmanager/hapusbackup', type: "POST", data: { src: src, ...getCsrfData() },
                success: function (res) {
                    if (res.csrf_hash) csrfHash = res.csrf_hash;
                    if (res.status) {
                        Swal.fire({ title: "Berhasil", text: res.message, icon: "success" }).then(() => location.reload());
                    } else {
                        Swal.fire({ title: "Gagal", text: res.message, icon: "error" });
                    }
                },
                error: function () { Swal.fire({ title: "Error", text: "Terjadi kesalahan", icon: "error" }); }
            });
        });
    }

    function restore(src) {
        Swal.fire({
            title: "Restore Backup",
            html: "Anda yakin ingin merestore <b>" + src + "</b>?<br><br><span class='text-danger'><b>PERINGATAN:</b><br>1. Data saat ini akan ditimpa.<br>2. Pastikan sudah backup terlebih dahulu.<br>3. Proses tidak dapat dibatalkan.</span>",
            icon: "warning", showCancelButton: true, confirmButtonColor: "#3085d6", cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Restore!"
        }).then(result => {
            if (!result.value) return;
            Swal.fire({ title: "Merestore...", text: "Mohon tunggu", allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: baseUrl + '/dbmanager/restore', type: "POST", data: { src: src, ...getCsrfData() },
                success: function (res) {
                    if (res.csrf_hash) csrfHash = res.csrf_hash;
                    if (res.status) {
                        Swal.fire({ title: "Berhasil", text: res.message, icon: "success" }).then(() => location.reload());
                    } else {
                        Swal.fire({ title: "Gagal", text: res.message, icon: "error" });
                    }
                },
                error: function () { Swal.fire({ title: "Error", text: "Terjadi kesalahan server", icon: "error" }); }
            });
        });
    }

    $(document).ready(function () {
        showProgress(0, '');
    });
</script>
<?= $this->endSection(); ?>