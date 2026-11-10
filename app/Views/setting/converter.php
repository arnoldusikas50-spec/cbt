<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-database-gear text-primary me-2"></i><?= $judul ?? 'Database Converter' ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Converter</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="app-content">
    <div class="container-fluid">
        
        <!-- Info Alert -->
        <div class="alert alert-light border border-info border-start-4 shadow-sm fade-in-up mb-4">
            <div class="d-flex align-items-start">
                <div class="symbol symbol-40px me-3 mt-1">
                    <span class="symbol-label bg-light-info text-info rounded-circle p-2">
                        <i class="bi bi-info-circle fs-4"></i>
                    </span>
                </div>
                <div>
                     <h5 class="alert-heading fw-bold text-dark mb-1">Informasi Konversi</h5>
                     <p class="mb-0 text-muted">Fitur ini digunakan untuk mengkonversi gambar yang tersimpan sebagai <b>Base64</b> (teks panjang) di dalam database menjadi file fisik di folder <code>uploads</code>. Proses ini direkomendasikan untuk mengurangi ukuran database dan meningkatkan performa aplikasi secara signifikan.</p>
                </div>
            </div>
        </div>

        <div class="card card-default my-shadow mb-4 fade-in-up">
            <div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center py-3">
                <h6 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-table text-primary me-2"></i>Daftar Tabel Database
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 30%;">Nama Tabel</th>
                                <th class="text-center" style="width: 15%;">Total Row</th>
                                <th class="text-center" style="width: 35%;">Status Konversi</th>
                                <th class="text-end pe-4" style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tables as $table => $count): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark font-monospace">
                                        <i class="bi bi-hdd-stack me-2 text-muted"></i><?= $table ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-3"><?= number_format($count, 0, ',', '.') ?></span>
                                    </td>
                                    <td class="text-center px-4">
                                        <!-- Progress Bar Container -->
                                        <div class="d-flex flex-column align-items-center justify-content-center" id="status_container_<?= $table ?>">
                                            <span class="text-muted small mb-1 d-none" id="status_text_<?= $table ?>">Menunggu waktu...</span>
                                            
                                            <div class="progress w-100 d-none shadow-sm" id="progress_bar_container_<?= $table ?>" style="height: 18px; border-radius: 10px;">
                                                <div id="progress_bar_<?= $table ?>" 
                                                     class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                                     role="progressbar" 
                                                     style="width: 0%; border-radius: 10px;" 
                                                     aria-valuenow="0" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">0%</div>
                                            </div>
                                            
                                            <span id="finish_badge_<?= $table ?>" class="d-none badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i>Selesai
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-primary btn-sm btn-convert shadow-sm px-3 fw-medium rounded-pill"
                                            id="btn_convert_<?= $table ?>"
                                            onclick="convertTable('<?= $table ?>', <?= $count ?>)">
                                            <i class="bi bi-magic me-1"></i> Mulai
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($tables)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle display-4 d-block mb-3 text-success"></i>
                                        Tidak ada tabel yang perlu dikonversi saat ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?= form_open('', array('id' => 'convert', 'class' => 'd-none')); ?><?= form_close() ?>
        </div>
    </div>
</div>

<script>
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    const baseUrl = '<?= base_url() ?>'.replace(/\/+$/, '');

    function checkRemaining(table) {
        return new Promise((resolve) => {
            const fd = new FormData();
            fd.append('table', table);
            fd.append(csrfName, csrfHash);
            $.ajax({
                url: baseUrl + '/dbconvert/remaining',
                type: 'POST',
                processData: false,
                contentType: false,
                data: fd,
                success: function (response) {
                    if (response.csrf_hash) csrfHash = response.csrf_hash;
                    resolve(response.success ? (response.remaining || 0) : null);
                },
                error: function () { resolve(null); }
            });
        });
    }

    function refreshStatus(table, count) {
        checkRemaining(table).then(function (remaining) {
            if (remaining === null) return;
            const statusTxt = $('#status_text_' + table);
            const finishBadge = $('#finish_badge_' + table);
            const btn = $('#btn_convert_' + table);

            if (remaining === 0 && count > 0) {
                finishBadge.removeClass('d-none');
                statusTxt.addClass('d-none');
            } else if (remaining > 0 && remaining < count) {
                statusTxt.removeClass('d-none').removeClass('text-danger')
                    .text('Sisa ' + remaining + ' baris belum dikonversi');
                btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Lanjutkan')
                   .removeClass('btn-primary').addClass('btn-info text-white');
            }
        });
    }

    $(function () {
        <?php foreach ($tables as $table => $count): ?>
        refreshStatus('<?= $table ?>', <?= $count ?>);
        <?php endforeach; ?>
    });

    function convertTable(table, count) {
        if (count === 0) {
            Swal.fire("Info", "Tabel kosong, tidak ada data untuk dikonversi.", "info");
            return;
        }

        const limit = 50;
        const totalPages = Math.ceil(count / limit);
        const delay = 300;
        let page = 1;
        let retryCount = 0;
        const maxRetries = 3;

        const btn = $('#btn_convert_' + table);
        const prgContainer = $('#progress_bar_container_' + table);
        const prgBar = $('#progress_bar_' + table);
        const statusTxt = $('#status_text_' + table);
        const finishBadge = $('#finish_badge_' + table);

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Proses...');
        prgContainer.removeClass('d-none');
        statusTxt.removeClass('d-none').removeClass('text-danger').text('Mempersiapkan data...');
        finishBadge.addClass('d-none');

        function sendPageData() {
            if (page > totalPages) {
                checkRemaining(table).then(function (remaining) {
                    btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Ulangi')
                       .removeClass('btn-primary btn-danger').addClass('btn-info text-white');
                    prgContainer.addClass('d-none');
                    statusTxt.addClass('d-none');
                    if (remaining !== null && remaining > 0) {
                        statusTxt.removeClass('d-none').text('Sisa ' + remaining + ' baris belum dikonversi. Klik "Ulangi" untuk melanjutkan.');
                        Swal.fire({ title: "Sebagian Selesai", text: `Masih ada ${remaining} baris berisi gambar. Ulangi untuk menyelesaikan.`, icon: "warning" });
                        return;
                    }
                    finishBadge.removeClass('d-none');
                    Swal.fire({ title: "Selesai!", text: `Konversi tabel ${table} berhasil.`, icon: "success", timer: 3000, showConfirmButton: false });
                });
                return;
            }

            const percent = Math.min(Math.round((page / totalPages) * 100), 100);
            prgBar.css('width', percent + '%').text(percent + '%').attr('aria-valuenow', percent);
            statusTxt.text(`Batch ${page} dari ${totalPages}...`);

            const fd = new FormData();
            fd.append('table', table);
            fd.append('limit', limit);
            fd.append('offset', (page - 1) * limit);
            fd.append(csrfName, csrfHash);

            $.ajax({
                url: baseUrl + '/dbconvert/convert',
                type: 'POST',
                processData: false,
                contentType: false,
                data: fd,
                success: function (response) {
                    if (response.csrf_hash) csrfHash = response.csrf_hash;
                    if (response.success === false) {
                        statusTxt.addClass('text-danger').text('Gagal: ' + (response.message || 'Error'));
                        btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Lanjutkan').removeClass('btn-primary btn-info').addClass('btn-danger');
                        return;
                    }
                    retryCount = 0;
                    page++;
                    setTimeout(sendPageData, delay);
                },
                error: function () {
                    if (retryCount >= maxRetries) {
                        statusTxt.addClass('text-danger').text(`Gagal pada batch ${page}. Proses dihentikan.`);
                        btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Lanjutkan').removeClass('btn-primary btn-info').addClass('btn-danger');
                        return;
                    }
                    retryCount++;
                    statusTxt.text(`Gagal batch ${page}. Ulang (${retryCount}/${maxRetries})...`);
                    setTimeout(sendPageData, 3000);
                }
            });
        }

        Swal.fire({
            title: "Mulai Konversi?",
            text: `Proses ${count} baris dari tabel ${table}. Mungkin memakan waktu.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, Mulai",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                sendPageData();
            } else {
                btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Mulai');
                prgContainer.addClass('d-none');
                statusTxt.addClass('d-none');
            }
        });
    }
</script>
<?= $this->endSection(); ?>
