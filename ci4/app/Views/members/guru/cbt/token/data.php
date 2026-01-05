<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card card-default my-shadow mb-4">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title text-bold"><i class="bi bi-key me-2"></i>
                            <?= $subjudul ?>
                        </h3>
                        <button class="btn btn-sm btn-primary d-none ms-auto shadow-sm" id="refresh-token">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh Token
                        </button>
                    </div>
                    <div class="card-body text-center py-5">
                        <h6 class="text-muted mb-4 uppercase tracking-wider">TOKEN SAAT INI</h6>
                        <div class="p-4 bg-light rounded-pill border shadow-inner mb-2">
                            <h1 class="display-3 fw-bold text-primary m-0" id="token-view" style="letter-spacing: 5px;">
                                <?= $token->token ?? '- - - - - -' ?>
                            </h1>
                        </div>
                        <p class="text-muted small mt-3">
                            <i class="bi bi-info-circle me-1"></i> Token ini digunakan oleh siswa untuk masuk ke ujian.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let timerTokenView;
    $(document).ready(function () {
        if (typeof getToken === 'function') {
            getToken(function (result) {
                getGlobalToken();
            });
        }

        $('#refresh-token').click(function () {
            if (typeof getToken === 'function') {
                getToken(function (result) {
                    getGlobalToken();
                });
            }
        });
    });

    function getGlobalToken() {
        if (typeof globalToken !== 'undefined' && globalToken != null) {
            const viewToken = $('#token-view');
            if (viewToken.length) viewToken.text(globalToken.token);
            if (globalToken.auto == '1' && typeof adaJadwalUjian !== 'undefined' && adaJadwalUjian > 0) {
                $('#refresh-token').removeClass('d-none');
            }
        }
    }
</script>
<?= $this->endSection() ?>