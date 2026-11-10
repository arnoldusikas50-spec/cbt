<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f5f7fb;
            color: #1f2a37;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(31, 42, 55, 0.15);
            max-width: 520px;
            text-align: center;
        }
        .code {
            font-size: 80px;
            font-weight: 600;
            color: #e11d48;
            margin: 0;
        }
        h1 {
            margin: 10px 0 20px;
            font-size: 28px;
        }
        p {
            margin: 0 0 30px;
            color: #6b7280;
        }
        a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2563eb;
            color: #fff;
            padding: 12px 26px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        }
        small {
            display: block;
            margin-top: 20px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <p class="code">403</p>
        <h1>Akses Ditolak</h1>
        <p><?= esc($message ?? 'Anda tidak memiliki hak akses ke halaman ini.') ?></p>
        <a href="javascript:history.back()">Kembali</a>
        <small>CBT Sync System</small>
    </div>
</body>
</html>
