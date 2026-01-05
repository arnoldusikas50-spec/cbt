<?php
$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$base_url .= "://" . $_SERVER['HTTP_HOST'];
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
$base_url = str_replace('installer/', '', $base_url);

?>

<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Error Database | CBT by Ikasmedia</title>

    <link rel="shortcut icon" href="<?= $base_url ?>assets/img/icongaruda.gif" type="image/x-icon">
    <link rel="stylesheet" href="<?= $base_url ?>assets/adminlte/dist/css/adminlte.min.css">

    <script src="<?= $base_url ?>assets/plugins/jquery/jquery.min.js"></script>

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #141824 0%, #1f2a40 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
        }

        .status-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 25px 80px rgba(7, 14, 28, 0.5);
            background: #fff;
            overflow: hidden;
        }

        .brand-mark {
            display: inline-flex;
            gap: 0.75rem;
            align-items: center;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.35rem;
            color: #4c5685;
        }

        .brand-mark img {
            width: 54px;
            height: auto;
            filter: grayscale(100%) brightness(0.25);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 1.1rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08rem;
        }

        .status-pill.success {
            background: rgba(56, 142, 60, 0.18);
            color: #1b5e20;
        }

        .status-pill.warning {
            background: rgba(255, 193, 7, 0.25);
            color: #8a6d00;
        }

        .status-pill.danger {
            background: rgba(229, 57, 53, 0.18);
            color: #b71c1c;
        }

        .info-grid {
            background: #f6f8fc;
            border-radius: 1.1rem;
            padding: 1.5rem;
        }

        .info-grid dl {
            margin: 0;
        }

        .info-grid dt {
            text-transform: uppercase;
            font-size: 0.76rem;
            letter-spacing: 0.1rem;
            color: #8b97b2;
        }

        .info-grid dd {
            font-size: 1.05rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 1.1rem;
        }

        .info-grid dd:last-child {
            margin-bottom: 0;
        }

        .status-placeholder {
            color: #536281;
        }

        .actions .btn {
            min-width: 180px;
        }
    </style>

</head>

<body>
    <div class="container py-5 my-4">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <div class="card status-card p-4 p-md-5">
                    <div class="text-center">
                        <span class="brand-mark">
                            <img src="<?= $base_url ?>assets/img/logo.png" alt="CBT by IKASMEDIA">
                            CBT by IKASMEDIA
                        </span>
                        <h1 class="h3 mt-4 mb-2">DATABASE ERROR</h1>
                        <p class="text-muted mb-4">Halaman ini hanya menampilkan status kesiapan database pastikan anda sudah mengkonfigurasi database dengan baik.</p>
                    </div>

                    <div id="status-panel" class="mt-3">
                        <div class="text-center status-placeholder">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p class="mb-0">Sedang memeriksa konfigurasi database...</p>
                        </div>
                    </div>

                    <div class="actions d-flex flex-column flex-sm-row gap-3 mt-4">
                        <button id="refresh-status" class="btn btn-outline-secondary flex-fill">Muat Ulang Status</button>
                        <a href="<?= $base_url ?>" class="btn btn-primary flex-fill">Buka Beranda CBT</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const STATUS_COPY = {
            1: {
                title: 'Database Belum Dibuat',
                description: 'Sistem belum menemukan database.',
                badge: 'warning'
            },
            2: {
                title: 'Database Belum Siap',
                description: 'Database ditemukan, namun struktur tabel inti belum tersedia. Import master.sql untuk melanjutkan.',
                badge: 'warning'
            },
            3: {
                title: 'Database Siap Digunakan',
                description: 'Seluruh tabel inti telah tersedia. Anda dapat menonaktifkan folder installer demi keamanan.',
                badge: 'success'
            }
        };

        const statusPanel = $('#status-panel');
        const refreshBtn = $('#refresh-status');

        function renderStatus(content) {
            statusPanel.html(content);
        }

        function renderError(message) {
            renderStatus(`
                <div class="alert alert-danger" role="alert">
                    <strong>Koneksi gagal.</strong>
                    <div>${message}</div>
                </div>
            `);
        }

        function renderState(response) {
            const { cond, message, data } = response;
            const copy = STATUS_COPY[cond] ?? {
                title: 'Status Tidak Dikenal',
                description: message || 'Respon server belum dikenali.',
                badge: 'danger'
            };

            const badgeClass = `status-pill ${copy.badge ?? 'danger'}`;

            const dbInfo = data && typeof data === 'object' ? `
                <div class="info-grid mt-4">
                    <dl class="row">
                        <div class="col-sm-6">
                            <dt>Host</dt>
                            <dd>${data.hostname || '-'}</dd>
                        </div>
                        <div class="col-sm-6">
                            <dt>Username</dt>
                            <dd>${data.username || '-'}</dd>
                        </div>
                        <div class="col-sm-6">
                            <dt>Database</dt>
                            <dd>${data.database || '-'}</dd>
                        </div>
                        <div class="col-sm-6">
                            <dt>Password</dt>
                            <dd>${data.password ? '••••••' : '(kosong)'}</dd>
                        </div>
                    </dl>
                </div>
            ` : '';

            renderStatus(`
                <div>
                    <div class="${badgeClass} mb-3">Status Database</div>
                    <h2 class="h4 mb-2">${copy.title}</h2>
                    <p class="text-muted">${copy.description}</p>
                    ${message && !STATUS_COPY[cond] ? `<div class="alert alert-info mt-3">${message}</div>` : ''}
                    ${dbInfo}
                </div>
            `);
        }

        function fetchStatus() {
            refreshBtn.prop('disabled', true).text('Memuat...');
            $.ajax({
                url: 'install.php',
                method: 'POST',
                data: { cond: 0 },
                success: function (res) {
                    try {
                        const response = JSON.parse(res);
                        renderState(response);
                    } catch (error) {
                        renderError('Respon server tidak valid.');
                    }
                },
                error: function () {
                    renderError('Tidak dapat menghubungi server. Pastikan layanan web berjalan.');
                },
                complete: function () {
                    refreshBtn.prop('disabled', false).text('Muat Ulang Status');
                }
            });
        }

        $(function () {
            fetchStatus();
            refreshBtn.on('click', function () {
                renderStatus(`
                    <div class="text-center status-placeholder">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="mb-0">Memeriksa ulang database...</p>
                    </div>
                `);
                fetchStatus();
            });
        });
    </script>

</body>

</html>