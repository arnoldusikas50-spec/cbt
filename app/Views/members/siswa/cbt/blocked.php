<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anda Diblokir</title>
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-icon/bootstrap-icons.min.css') ?>">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 50%, #fecaca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow: hidden;
        }

        .block-card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(220, 38, 38, 0.18);
            background: #fff;
            max-width: 480px;
            width: 100%;
            animation: popIn 0.4s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }

        @keyframes popIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .icon-wrap {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 12px 30px rgba(220, 38, 38, 0.35);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 12px 30px rgba(220, 38, 38, 0.35);
            }

            50% {
                transform: scale(1.06);
                box-shadow: 0 16px 40px rgba(220, 38, 38, 0.45);
            }
        }

        .icon-wrap i {
            font-size: 3rem;
            color: #fff;
        }

        .title-block {
            color: #991b1b;
            letter-spacing: -0.5px;
        }

        .msg-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
        }

        .btn-refresh {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border: none;
            border-radius: 50rem;
            padding: 0.85rem 2.5rem;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-refresh:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(220, 38, 38, 0.4);
            background: linear-gradient(135deg, #dc2626, #991b1b);
        }

        .btn-refresh:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <div class="block-card p-5 text-center">
        <div class="icon-wrap">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2 class="fw-bold title-block mb-3">Akses Diblokir</h2>
        <div class="msg-box mb-4">
            <p class="text-danger fw-semibold mb-0" style="font-size: 1rem;"><?= esc($message ?? '') ?></p>
        </div>
        <p class="text-muted small mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Silakan hubungi pengawas untuk membuka blokir, lalu tekan tombol di bawah ini.
        </p>
        <button onclick="location.reload()" class="btn btn-refresh text-white btn-lg">
            <i class="bi bi-arrow-clockwise me-2"></i> Refresh Halaman
        </button>
    </div>
</body>

</html>
