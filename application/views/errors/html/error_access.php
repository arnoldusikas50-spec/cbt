<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Dibatasi</title>
    <style>
        :root {
            --primary: #991B1B; /* Red 800 */
            --bg: #FEF2F2; /* Red 50 */
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            border-top: 5px solid var(--primary);
        }

        .icon-container {
            margin-bottom: 20px;
        }
        
        h1 {
            color: var(--text-main);
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        p {
            color: var(--text-muted);
            margin: 0 0 30px 0;
            line-height: 1.5;
        }
        
        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid #FFF;
            border-bottom-color: var(--primary);
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background-color: #7F1D1D;
        }
    </style>
    <script>
        // Optional: Auto redirect after few seconds if intended
        setTimeout(function(){
            // window.location.href = "<?php echo base_url('auth'); ?>";
        }, 5000);
    </script>
</head>
<body>
    <div class="card">
        <div class="icon-container">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 22C17.5 22 22 17.5 22 12C22 6.5 17.5 2 12 2C6.5 2 2 6.5 2 12C2 17.5 6.5 22 12 22Z" stroke="#991B1B" stroke-width="1.5"/>
                <path d="M15 9L9 15" stroke="#991B1B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 9L15 15" stroke="#991B1B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <h1>Akses Terlarang</h1>
        <p>Anda tidak diizinkan mengakses halaman ini. Sistem mendeteksi aktivitas yang tidak sah atau sesi Anda telah habis.</p>
        
        <a href="<?php echo base_url(); ?>" class="btn">Kembali ke Halaman Utama</a>
    </div>
</body>
</html>
