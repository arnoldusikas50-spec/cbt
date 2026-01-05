<?php

?>

<main class="app-main bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header d-flex align-items-center">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-primary d-none shadow-sm" id="refresh-token">
                            <i class="bi bi-arrow-repeat me-1"></i> Refresh Token
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="text-center">TOKEN SAAT INI</h6>
                    <h1 class="text-center" id="token-view"><?= $token->token ?></h1>
                </div>
            </div>
        </div>
    </section>
    </div>

    <script>
        let timerTokenView;
        $(document).ready(function () {
            getToken(function (result) {
                getGlobalToken();
            });

            $('#refresh-token').click(function () {
                getToken(function (result) {
                    getGlobalToken();
                });
            });
        });

        function getGlobalToken() {
            if (globalToken != null) {
                const viewToken = $('#token-view');
                if (viewToken.length) viewToken.text(globalToken.token);
                if (globalToken.auto == '1' && adaJadwalUjian > 0) {
                    $('#refresh-token').removeClass('d-none')
                }
            }
        }

    </script>