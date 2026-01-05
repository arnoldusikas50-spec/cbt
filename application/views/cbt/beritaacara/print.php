<!DOCTYPE html>
<html>
<head>
    <title>Berita Acara</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        .table-border td, .table-border th { border: 1px solid black; padding: 5px; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-4 { margin-bottom: 20px; }
        .mt-4 { margin-top: 20px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px;">
        <button onclick="window.print()">Cetak PDF</button>
        <button onclick="window.location.href='<?= base_url('cbtberitaacara') ?>'">Kembali</button>
    </div>

    <div class="container">
        <!-- Header / Kop -->
        <div class="text-center mb-4">
            <h3 style="margin:0">BERITA ACARA PELAKSANAAN</h3>
            <h4 style="margin:0">PENILAIAN BERBASIS KOMPUTER</h4>
            <br>
        </div>

        <p>Pada hari ini <b><?= hari_indo($jadwal->tgl_mulai) ?></b> tanggal <b><?= date('d', strtotime($jadwal->tgl_mulai)) ?></b> bulan <b><?= bulan_indo($jadwal->tgl_mulai) ?></b> tahun <b><?= date('Y', strtotime($jadwal->tgl_mulai)) ?></b>, telah dilaksanakan Penilaian:</p>

        <table>
            <tr><td width="200">Mata Pelajaran</td><td>: <?= $jadwal->bank_kode ?></td></tr>
            <tr><td>Tahun Pelajaran</td><td>: <?= $tp->tahun ?></td></tr>
            <tr><td>Semester</td><td>: <?= $smt->nama_smt ?></td></tr>
            <tr><td>Ruang / Sesi</td><td>: <?= $ruang->nama_ruang ?> / <?= $sesi->nama_sesi ?></td></tr>
            <tr><td>Waktu</td><td>: <?= $jadwal->tgl_mulai ?></td></tr>
        </table>

        <br>
        <table class="table-border">
            <tr>
                <th>Jumlah Peserta Seharusnya</th>
                <th>Jumlah Hadir</th>
                <th>Jumlah Tidak Hadir</th>
            </tr>
            <tr>
                <td class="text-center"><?= $berita->jumlah_peserta_hadir + $berita->jumlah_peserta_tidak_hadir ?></td>
                <td class="text-center"><?= $berita->jumlah_peserta_hadir ?></td>
                <td class="text-center"><?= $berita->jumlah_peserta_tidak_hadir ?></td>
            </tr>
        </table>

        <br>
        <div>
            <b>Catatan selama pelaksanaan ujian:</b>
            <div style="border: 1px solid black; padding: 10px; min-height: 50px;">
                <?= $berita->catatan ? $berita->catatan : '-' ?>
            </div>
        </div>
        
        <br>
        <div>
            <b>Peserta yang tidak hadir:</b>
            <div style="border: 1px solid black; padding: 10px; min-height: 50px;">
                <?= $berita->peserta_tidak_hadir ? $berita->peserta_tidak_hadir : '-' ?>
            </div>
        </div>

        <br>
        <p>Demikian Berita Acara ini dibuat dengan sesungguhnya.</p>

        <br>
        <table style="border: none;">
            <tr>
                <td class="text-center" width="50%">
                    Proktor<br>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=Proktor:<?= urlencode($berita->nama_proktor) ?>.Valid" alt="QR Code"><br>
                    <u><?= $berita->nama_proktor ?></u><br>
                    NIP. <?= $berita->nip_proktor ?>
                </td>
                <td class="text-center" width="50%">
                    Pengawas<br>
                    <?php if($berita->tanda_tangan_pengawas): ?>
                        <img src="<?= $berita->tanda_tangan_pengawas ?>" style="height: 80px;"><br>
                    <?php else: ?>
                        <br><br><br>
                    <?php endif; ?>
                    <u><?= $berita->nama_pengawas ?></u><br>
                    NIP. <?= $berita->nip_pengawas ?>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
