<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terjadi Kesalahan (500)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Be Vietnam Pro', sans-serif;
            background: radial-gradient(circle at top, #eef2ff, #f8fafc 45%);
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .card {
            background: #fff;
            border-radius: 28px;
            padding: 48px 40px;
            box-shadow: 0 35px 80px rgba(15, 23, 42, 0.2);
            max-width: 560px;
            text-align: center;
        }

        .status {
            font-size: 88px;
            font-weight: 600;
            margin: 0;
            background: linear-gradient(120deg, #2563eb, #9333ea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h1 {
            margin: 12px 0 16px;
            font-size: 30px;
        }

        p {
            margin: 0 0 28px;
            color: #475569;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 26px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
        }

        .primary {
            background: #2563eb;
            color: #fff;
        }

        .secondary {
            border: 1px solid #cbd5f5;
            color: #2563eb;
        }

        small {
            display: block;
            margin-top: 32px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="card">
        <p class="status">500</p>
        <h1>Oops, terjadi kesalahan.</h1>
        <p><?= esc($message ?? 'Server mengalami gangguan. Silakan coba beberapa saat lagi atau hubungi administrator.') ?>
        </p>
        <div class="actions">
            <a href="javascript:location.reload()" class="primary">Muat Ulang</a>
            <a href="javascript:history.back()" class="secondary">Kembali</a>
        </div>
        <small>-- IKASMEDIA --</small>
    </div>
</body>

</html>