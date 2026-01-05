<!DOCTYPE html>
<html>

<head>
    <title>Cetak Berita Acara</title>
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
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data td {
            padding: 5px;
            vertical-align: top;
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
        <h2>BERITA ACARA PELAKSANAAN UJIAN</h2>
        <h3>
            <?= esc($setting->nama_sekolah ?? 'SEKOLAH') ?>
        </h3>
        <p>
            <?= esc($setting->alamat ?? '') ?>
        </p>
    </div>

    <div class="content">
        <p>Pada hari ini <strong>
                <?= $hari ?? '-' ?>
            </strong> tanggal <strong>
                <?= $tanggal ?? '-' ?>
            </strong>, telah dilaksanakan ujian sekolah dengan rincian sebagai berikut:</p>

        <table class="data">
            <tr>
                <td width="200">Mata Pelajaran</td>
                <td>:
                    <?= esc($berita->nama_mapel ?? '-') ?>
                </td>
            </tr>
            <tr>
                <td>Kelas/Sesi</td>
                <td>:
                    <?= esc($berita->nama_kelas ?? 'Semua') ?> /
                    <?= esc($berita->nama_sesi ?? '-') ?>
                </td>
            </tr>
            <tr>
                <td>Tanggal Ujian</td>
                <td>:
                    <?= date('d/m/Y', strtotime($berita->tgl_ujian ?? 'now')) ?>
                </td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>:
                    <?= date('H:i', strtotime($berita->waktu_mulai)) ?> -
                    <?= date('H:i', strtotime($berita->waktu_selesai)) ?> WIB
                </td>
            </tr>
            <tr>
                <td>Jumlah Peserta</td>
                <td>:
                    <?= esc($berita->jumlah_peserta ?? '-') ?> Orang
                </td>
            </tr>
            <tr>
                <td>Hadir</td>
                <td>:
                    <?= esc($berita->jumlah_hadir ?? '-') ?> Orang
                </td>
            </tr>
            <tr>
                <td>Tidak Hadir</td>
                <td>:
                    <?= esc($berita->jumlah_absen ?? '-') ?> Orang
                </td>
            </tr>
            <tr>
                <td>Catatan Pelaksanaan</td>
                <td>:
                    <?= nl2br(esc($berita->catatan ?? '-')) ?>
                </td>
            </tr>
        </table>

        <div style="margin-top: 50px; width: 100%;">
            <table style="width: 100%;">
                <tr>
                    <td align="center" width="50%">
                        Pengawas 1<br><br><br><br>
                        (.............................................)
                    </td>
                    <td align="center" width="50%">
                        Pengawas 2<br><br><br><br>
                        (.............................................)
                    </td>
                </tr>
            </table>
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