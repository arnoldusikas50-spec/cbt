<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pakta Integritas -
        <?= isset($content->nama) ? $content->nama : (isset($uploader->nama_lengkap) ? $uploader->nama_lengkap : 'Pengawas') ?>
        - <?= isset($tp_active->tahun) ? $tp_active->tahun : date('Y') ?> - <?= date('d-m-Y') ?>
    </title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 1.8cm 1.5cm 1.8cm;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
        }

        /* Style khusus tampilan layar (Preview) */
        @media screen {
            html {
                background: #525659;
            }

            body {
                width: 21cm;
                min-height: 29.7cm;
                margin: 15px auto;
                padding: 1.5cm 2cm;
                background: #fff;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            }
        }

        /* Style saat dicetak (Print) */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: transparent;
            }

            .no-print {
                display: none !important;
            }
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            margin-bottom: 1px;
            letter-spacing: 1px;
        }

        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
        }

        .intro {
            margin: 10px 0;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 0;
            vertical-align: top;
        }

        .info-table .label {
            width: 150px;
        }

        .info-table .colon {
            width: 15px;
            text-align: center;
        }

        .section-title {
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        .declaration {
            margin: 15px 0;
            text-align: justify;
        }

        .declaration-list {
            margin: 5px 0;
            padding-left: 25px;
        }

        .declaration-list li {
            margin-bottom: 5px;
            text-align: justify;
            padding-left: 5px;
        }

        .closing {
            margin: 15px 0;
            text-align: justify;
        }

        .signature-section {
            margin-top: 30px;
            text-align: right;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 250px;
        }

        .signature-date {
            margin-bottom: 5px;
        }

        .signature-title {
            margin-bottom: 40px;
        }

        .signature-name {
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }

        .signature-ttd {
            font-family: 'Brush Script MT', 'Segoe Script', cursive;
            font-size: 24pt;
            color: #1565C0;
            margin: 5px 0;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #1565C0;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #0D47A1;
        }

        /* Footer Fixed */
        .footer-fixed {
            font-size: 9pt;
            font-style: italic;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            display: flex;
            justify-content: space-between;
        }

        @media screen {
            body {
                position: relative;
            }

            .footer-fixed {
                position: absolute;
                bottom: 1.5cm;
                left: 1.8cm;
                right: 1.8cm;
            }
        }

        @media print {
            .footer-fixed {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
            }
        }
    </style>
</head>

<body>
    <?php
    // Helper Tanggal Indo
    $bulan_arr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tgl_cetak = date('d') . ' ' . $bulan_arr[date('n') - 1] . ' ' . date('Y H:i');
    ?>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ Cetak
    </button>

    <div class="container">
        <div class="header">
            <h1>PAKTA INTEGRITAS</h1>
            <h2>PELAKSANAAN <span>
                    <?= isset($jenis_ujian->nama_jenis) ? strtoupper($jenis_ujian->nama_jenis) : 'UJIAN' ?>
                </span></h2>
            <h2>TAHUN PELAJARAN <span>
                    <?= isset($tp_active->tahun) ? $tp_active->tahun : date('Y') . '/' . (date('Y') + 1) ?>
                </span></h2>
        </div>

        <div class="intro">
            <p>Saya yang bertanda tangan di bawah ini :</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nama</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->nama) ? $content->nama : (isset($uploader->nama_lengkap) ? $uploader->nama_lengkap : '_________________________') ?>
                </td>
            </tr>
            <tr>
                <td class="label">NIP/NIK</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->nip) ? $content->nip : (isset($uploader->nip) && $uploader->nip ? $uploader->nip : '-') ?>
                </td>
            </tr>
            <tr>
                <td class="label">Tugas/Jabatan</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->jabatan) ? $content->jabatan : (isset($uploader->jabatan) ? $uploader->jabatan : '_________________________') ?>
                </td>
            </tr>
            <tr>
                <td class="label">Asal Instansi</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->instansi) ? $content->instansi : (isset($setting->nama_sekolah) ? $setting->nama_sekolah : '_________________________') ?>
                </td>
            </tr>
            <tr>
                <td class="label">No. Handphone</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->no_hp) ? $content->no_hp : (isset($uploader->no_hp) ? $uploader->no_hp : '_________________________') ?>
                </td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->email) ? $content->email : (isset($uploader->email) ? $uploader->email : '_________________________') ?>
                </td>
            </tr>
        </table>

        <p class="section-title">Bertugas di :</p>

        <table class="info-table">
            <tr>
                <td class="label">Provinsi</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->provinsi) ? $content->provinsi : (isset($setting->provinsi) ? $setting->provinsi : '_________________________') ?>
                </td>
            </tr>
            <tr>
                <td class="label">Kabupaten/Kota</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->kota) ? $content->kota : (isset($setting->kota) ? $setting->kota : '_________________________') ?>
                </td>
            </tr>
            <tr>
                <td class="label">Tempat Pelaksanaan</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->tempat) ? $content->tempat : (isset($setting->nama_sekolah) ? $setting->nama_sekolah : '_________________________') ?>
                </td>
            </tr>
            <tr>
                <td class="label">Tanggal Pelaksanaan</td>
                <td class="colon">:</td>
                <td>
                    <?= isset($content->tanggal_pelaksanaan) ? $content->tanggal_pelaksanaan : '_________________________' ?>
                </td>
            </tr>
        </table>

        <div class="declaration">
            <p><strong>Dengan ini menyatakan bahwa :</strong></p>
            <ol class="declaration-list">
                <li>Sanggup meningkatkan kualitas, kredibilitas, dan akuntabilitas pelaksanaan Asesmen demi meningkatkan
                    mutu pendidikan nasional.</li>
                <li>Sanggup melaksanakan tugas sesuai Prosedur Operasi Standar Penyelenggaraan Asesmen.</li>
                <li>Sanggup menjaga keamanan dan kerahasiaan bahan
                    <?= isset($jenis_ujian->nama_jenis) ? $jenis_ujian->nama_jenis : 'Asesmen' ?> Tahun
                    <?= date('Y') ?>
                </li>
                <li>Sanggup melaksanakan
                    <?= isset($jenis_ujian->nama_jenis) ? $jenis_ujian->nama_jenis : 'Asesmen' ?>
                    <?= date('Y') ?> secara jujur dan penuh tanggung jawab.
                </li>
            </ol>
        </div>

        <div class="closing">
            <p>Demikian pakta integritas ini saya buat dengan sebenar-benarnya tanpa ada unsur paksaan dari pihak
                manapun.</p>
            <p>Apabila saya melanggar hal-hal yang telah dinyatakan dalam pakta integritas ini, saya bersedia dikenakan
                sanksi sesuai dengan hukum dan ketentuan peraturan perundang-undangan yang berlaku.</p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-date">
                    <?= isset($setting->kota) ? $setting->kota : '...................' ?>,
                    <?= isset($content->tanggal_ttd) ? $content->tanggal_ttd : date('d') . ' ' . $bulan_arr[date('n') - 1] . ' ' . date('Y') ?>
                </div>
                <div class="signature-title">
                    <?= isset($content->jabatan) ? $content->jabatan : (isset($uploader->jabatan) ? $uploader->jabatan : 'Pengawas') ?>,
                </div>

                <?php if (isset($uploader->ttd) && $uploader->ttd): ?>
                    <img src="<?= base_url($uploader->ttd) ?>" style="max-height: 60px; max-width: 150px;">
                <?php else: ?>
                    <div class="signature-ttd">
                        <?= isset($setting->nama_sekolah) ? preg_replace('/[^a-zA-Z]/', '', strtolower(substr($setting->nama_sekolah, 0, 10))) : 'ttd' ?>
                    </div>
                <?php endif; ?>

                <div class="signature-name">
                    (
                    <?= isset($content->nama) ? $content->nama : (isset($uploader->nama_lengkap) ? $uploader->nama_lengkap : '_________________________') ?>
                    )
                </div>
                <div>NIP.
                    <?= isset($content->nip) ? $content->nip : (isset($uploader->nip) && $uploader->nip ? $uploader->nip : '-') ?>
                </div>
            </div>
        </div>

        <div class="footer-fixed">
            <div>Dokumen ini ditandatangani secara digital</div>
            <div>dicetak pada : <?= $tgl_cetak ?></div>
        </div>
    </div>
</body>

</html>