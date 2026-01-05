<!DOCTYPE html>
<html>
<head>
    <title>Pakta Integritas</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        ol { line-height: 1.5; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px;">
        <button onclick="window.print()">Cetak PDF</button>
        <button onclick="window.location.href='<?= base_url('cbtberitaacara/pakta') ?>'">Kembali</button>
    </div>

    <div class="container">
        <div class="text-center">
            <h3 style="margin:0">PAKTA INTEGRITAS</h3>
            <h4 style="margin:0">PENYELENGGARAAN PENILAIAN BERBASIS KOMPUTER</h4>
            <br><hr><br>
        </div>

        <p>Dalam rangka pelaksanaan Ujian <b><?= $jadwal->bank_kode ?></b> Tahun Pelajaran <b><?= $tp->tahun ?></b>, saya:</p>

        <table>
            <tr><td width="150">Nama</td><td>: <?= $pakta->nama ?></td></tr>
            <tr><td>Jabatan</td><td>: <?= $pakta->role ?></td></tr>
        </table>

        <p>Menyatakan bahwa saya:</p>
        
        <!-- Display newline-separated text as list if possible, or just pre-wrap -->
        <div style="margin-left: 20px;">
            <?= nl2br($pakta->isi_pakta) ?>
        </div>
        
        <br>
        <p>Demikian pakta integritas ini saya buat untuk dipergunakan sebagaimana mestinya.</p>
        
        <br>
        <div style="float: right; text-align: center; width: 300px;">
            <?= tgl_indo($pakta->created_at) ?> <br>
            Yang Membuat Pernyataan, <br>
            <br>
            <?php if($pakta->tanda_tangan): ?>
                <img src="<?= $pakta->tanda_tangan ?>" style="height: 100px;">
            <?php else: ?>
                <br><br><br><br>
            <?php endif; ?>
            <br>
            ( <b><?= $pakta->nama ?></b> )
        </div>
        <div style="clear: both;"></div>
        
        <div style="margin-top: 50px;">
             <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=Pakta:<?= urlencode($pakta->nama) ?>.Role:<?= $pakta->role ?>.Valid" alt="QR Code">
        </div>
    </div>
</body>
</html>
