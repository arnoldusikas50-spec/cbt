<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Token Ujian' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Token</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card card-primary my-shadow border-0 h-100">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="bi bi-key-fill me-2"></i>Token Saat Ini</h3>
                        </div>
                        <div class="card-body text-center d-flex flex-column justify-content-center py-5">
                            <?php if ($token && $token->token): ?>
                                <h1 class="display-1 fw-bold text-primary mb-4" id="currentToken"
                                    style="letter-spacing: 5px;">
                                    <?= esc($token->token) ?>
                                </h1>
                                <div>
                                    <span
                                        class="badge bg-<?= ($token->status ?? '0') === '1' ? 'success' : 'danger' ?> shadow-sm rounded-pill px-3 py-2"
                                        id="tokenStatus">
                                        <i
                                            class="bi bi-<?= ($token->status ?? '0') === '1' ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                        <?= ($token->status ?? '0') === '1' ? 'TOKEN AKTIF' : 'TOKEN NONAKTIF' ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <h1 class="display-1 fw-bold text-muted mb-4" id="currentToken">------</h1>
                                <p class="text-muted"><i class="bi bi-info-circle me-1"></i> Belum ada token aktif</p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-center bg-white border-top-0 pb-4">
                            <button type="button" class="btn btn-primary shadow-sm px-4 rounded-pill me-2 mb-2"
                                onclick="generateToken()">
                                <i class="bi bi-arrow-repeat me-1"></i> Generate Token Baru
                            </button>
                            <?php if ($token && $token->status === '1'): ?>
                                <button type="button" class="btn btn-danger shadow-sm px-4 rounded-pill mb-2"
                                    onclick="toggleToken('0')">
                                    <i class="bi bi-power me-1"></i> Nonaktifkan
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-success shadow-sm px-4 rounded-pill mb-2"
                                    onclick="toggleToken('1')">
                                    <i class="bi bi-check-lg me-1"></i> Aktifkan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card card-info my-shadow border-0 h-100">
                        <div class="card-header">
                            <h3 class="card-title text-bold"><i class="bi bi-info-circle me-2"></i>Informasi & Keamanan
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="callout callout-info border-info my-shadow-sm mb-4">
                                <h5 class="text-info fw-bold"><i class="bi bi-lightbulb me-2"></i>Cara Menggunakan Token
                                </h5>
                                <ol class="ps-3 mb-0 text-muted">
                                    <li class="mb-1">Token digunakan siswa saat akan memulai ujian</li>
                                    <li class="mb-1">Berikan token kepada pengawas setiap sesi</li>
                                    <li class="mb-1">Token dapat diubah sewaktu-waktu</li>
                                    <li>Nonaktifkan token jika sesi ujian selesai</li>
                                </ol>
                            </div>

                            <div class="callout callout-warning border-warning my-shadow-sm">
                                <h5 class="text-warning fw-bold"><i class="bi bi-shield-check me-2"></i>Keamanan</h5>
                                <p class="mb-0 text-muted">
                                    Pastikan token hanya diberikan kepada siswa yang berhak mengikuti ujian.
                                    Ganti token secara berkala untuk mencegah penyalahgunaan dan menjaga integritas
                                    ujian.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    function generateToken() {
        ajaxcsrf();
        Swal.fire({
            title: 'Generate Token Baru?',
            text: 'Token lama akan diganti dengan token baru',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Generate!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-primary shadow-sm me-2',
                cancelButton: 'btn btn-secondary shadow-sm'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('cbttoken/generate') ?>',
                    type: 'POST',
                    data: { [CSRF_NAME]: CSRF_HASH },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status) {
                            $('#currentToken').text(res.data.token);
                            showSuccessToast('Token baru: ' + res.data.token);
                            setTimeout(() => { location.reload(); }, 1500);
                        } else {
                            showDangerToast(res.message);
                        }
                    }
                });
            }
        });
    }

    function toggleToken(status) {
        ajaxcsrf();
        $.ajax({
            url: '<?= site_url('cbttoken/toggle') ?>',
            type: 'POST',
            data: { status: status, [CSRF_NAME]: CSRF_HASH },
            dataType: 'json',
            success: function (res) {
                if (res.status) {
                    showSuccessToast(res.message);
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    showDangerToast(res.message);
                }
            }
        });
    }
</script>