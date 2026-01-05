<!DOCTYPE html>
<html>

<head>
    <title>Cetak Pakta Integritas</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h2,
        .header h3 {
            margin: 5px 0;
        }

        .content {
            margin: 20px 0;
            line-height: 1.5;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 10px 20px;
            font-size: 10pt;
            background: white;
        }

        .footer-table {
            width: 100%;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        @media print {
            @page {
                size: A4;
                margin: 2cm;
            }

            .footer {
                position: fixed;
                bottom: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <h2>PAKTA INTEGRITAS</h2>
        <h3>
            <?= esc($setting->nama_sekolah ?? 'SEKOLAH') ?>
        </h3>
        <p>
            <?= esc($setting->alamat ?? '') ?>
        </p>
    </div>

    <div class="content">
        <p>Saya yang bertanda tangan di bawah ini:</p>
        <table style="margin-left: 20px; margin-bottom: 20px;">
            <tr>
                <td width="150">Nama</td>
                <td>:
                    <?= esc($pakta->nama ?? '-') ?>
                </td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:
                    <?= esc($pakta->jabatan ?? '-') ?>
                </td>
            </tr>
            <tr>
                <td>Instansi/Sekolah</td>
                <td>:
                    <?= esc($pakta->instansi ?? '-') ?>
                </td>
            </tr>
        </table>

        <p>Menyatakan bahwa saya:</p>
        <ol>
            <li>Sanggup meningkatkan kualitas, kompetensi, dan kinerja secara profesional.</li>
            <li>Akan melaksanakan tugas dengan jujur, disiplin, dan bertanggung jawab.</li>
            <li>Tidak akan membocorkan soal, kunci jawaban, atau informasi rahasia lainnya terkait pelaksanaan ujian.
            </li>
            <li>Akan menjunjung tinggi integritas dan nama baik instansi.</li>
        </ol>

        <p>Demikian pakta integritas ini saya buat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.
        </p>

        <div style="margin-top: 50px; float: right; width: 300px; text-align: center;">
            <p>
                <?= esc($setting->kota ?? 'Kota') ?>,
                <?= date('d F Y') ?>
            </p>
            <br><br><br>
            <p><strong>(
                    <?= esc($pakta->nama ?? '..........................') ?> )
                </strong></p>
        </div>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td align="left">Dokumen ini ditandatangani secara digital</td>
                <td align="right">Dokumen ini dicetak pada:
                    <?= date('d-m-Y H:i:s') ?>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>