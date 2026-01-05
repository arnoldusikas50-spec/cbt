<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Error</title>
    <style>
        :root {
            --primary: #EF4444;
            --primary-dark: #DC2626;
            --secondary: #64748b;
            --bg: #fef2f2;
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
            border: 1px solid rgba(239, 68, 68, 0.1);
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
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- SVG Database Error Icon -->
        <svg class="illustration" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 6V18C4 20.2091 7.58172 22 12 22C16.4183 22 20 20.2091 20 18V6" stroke="#EF4444" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
            <path d="M4 6C4 8.20914 7.58172 10 12 10C16.4183 10 20 8.20914 20 6" stroke="#EF4444" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
            <path d="M4 6C4 3.79086 7.58172 2 12 2C16.4183 2 20 3.79086 20 6" stroke="#EF4444" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
            <path d="M4 12C4 14.2091 7.58172 16 12 16C16.4183 16 20 14.2091 20 12" stroke="#EF4444" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
            <!-- X mark overlay -->
            <path
                d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM16 13H8V11H16V13Z"
                fill="white" fill-opacity="0.01" />
            <!-- Just a creative DB broken icon concept -->
        </svg>

        <h1>Database Error</h1>
        <p>
            <?php echo $heading; ?><br>
            <?php echo $message; ?>
        </p>

        <a href="javascript:history.back()" class="btn">Kembali</a>
    </div>
</body>

</html>