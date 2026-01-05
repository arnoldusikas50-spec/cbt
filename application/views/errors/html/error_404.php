<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 Page Not Found</title>
    <style>
        :root {
            --primary: #3B82F6;
            --primary-dark: #2563EB;
            --secondary: #64748b;
            --bg: #eff6ff;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }

        .container {
            max-width: 600px;
            width: 100%;
            text-align: center;
            background: var(--card-bg);
            padding: 3rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.1);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .illustration {
            width: 100%;
            height: 180px;
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        p {
            font-size: 1.125rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background-color: var(--primary);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .code {
            font-family: monospace;
            color: var(--primary);
            font-size: 0.875rem;
            margin-top: 2rem;
            background: #eff6ff;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- SVG Ghost Icon -->
        <svg class="illustration" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C7.5 2 4 5 4 9C4 13.5 12 22 12 22C12 22 20 13.5 20 9C20 5 16.5 2 12 2Z" fill="#DBEAFE" />
            <circle cx="12" cy="9" r="3" fill="#3B82F6" />
            <path d="M12 22C12 22 5 13 5 9C5 5.5 8 3 12 3C16 3 19 5.5 19 9C19 13 12 22 12 22Z" stroke="#2563EB"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>

        <h1>404 Page Not Found</h1>
        <p>
            Maaf, halaman yang Anda cari tidak dapat ditemukan.<br>
            Mungkin halaman telah dipindahkan atau dihapus.
        </p>

        <a href="/" class="btn">Kembali ke Beranda</a>

        <div style="display:block; margin-top:1rem;"></div>
        <div class="code">Error Code: 404</div>
    </div>
</body>

</html>