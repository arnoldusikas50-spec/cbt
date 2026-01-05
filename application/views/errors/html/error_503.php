<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Unavailable</title>
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --bg: #f3f4f6;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }

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
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin: 1.5rem 0 1rem;
            color: var(--text-main);
        }

        p {
            font-size: 1.125rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: #F3E8FF;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
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
        }
        
        .code {
            margin-top: 2rem;
            font-family: monospace;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-box">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
            </svg>
        </div>

        <h1>System Unavailable</h1>
        <p>Sistem sedang sibuk atau dalam pemeliharaan. Silakan coba beberapa saat lagi.</p>
        
        <a href="javascript:location.reload()" class="btn">Refresh Halaman</a>

        <div class="code">Error Code: 503</div>
    </div>
</body>
</html>
