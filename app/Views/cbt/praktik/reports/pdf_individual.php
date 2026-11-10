<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Praktik - <?= esc($laporan['siswa']['nama']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; }
        .info-section { margin-bottom: 20px; width: 100%; border-collapse: collapse; }
        .info-section td { padding: 4px 0; vertical-align: top; }
        .info-section td.label { width: 150px; font-weight: bold; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table th, .data-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .data-table th { background-color: #f2f2f2; font-weight: bold; }
        .score-section { margin-top: 30px; text-align: center; border: 2px solid #3498db; padding: 20px; border-radius: 10px; }
        .score-value { font-size: 48px; font-weight: bold; color: #3498db; }
        .score-label { font-size: 16px; margin-top: 5px; color: #555; }
        .footer { margin-top: 50px; width: 100%; }
        .ttd-box { float: right; width: 250px; text-align: center; }
        .ttd-space { height: 80px; }
        .ttd-name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN HASIL UJIAN PRAKTIK</h1>
        <h2>INDIVIDUAL</h2>
    </div>

    <table class="info-section">
        <tr>
            <td class="label">Nama Siswa</td>
            <td>: <?= esc($laporan['siswa']['nama']) ?></td>
        </tr>
        <tr>
            <td class="label">NIS</td>
            <td>: <?= esc($laporan['siswa']['nis']) ?></td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>: <?= esc($laporan['siswa']['kelas']) ?></td>
        </tr>
        <tr>
            <td colspan="2"><hr style="border: 0.5px solid #eee;"></td>
        </tr>
        <tr>
            <td class="label">Judul Ujian</td>
            <td>: <?= esc($laporan['praktik']['judul']) ?></td>
        </tr>
        <tr>
            <td class="label">Mata Pelajaran</td>
            <td>: <?= esc($laporan['praktik']['mapel'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="label">Tanggal Cetak</td>
            <td>: <?= $tanggal_cetak ?></td>
        </tr>
    </table>

    <h3>Rincian Penilaian</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Kriteria Penilaian</th>
                <th style="width: 100px; text-align: center;">Nilai</th>
                <th style="width: 80px; text-align: center;">Predikat</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($laporan['rubriks'] as $rubrik): 
                $rincianNilai = $laporan['rincian_nilai'][$rubrik->id]['nilai'] ?? 0;
                $catatan = $laporan['rincian_nilai'][$rubrik->id]['catatan'] ?? '';
                $predikat = $rincianNilai >= 86 ? 'M' : ($rincianNilai >= 66 ? 'C' : ($rincianNilai >= 41 ? 'L' : ($rincianNilai > 0 ? 'BB' : '-')));
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td>
                    <strong><?= esc($rubrik->nama_kriteria) ?></strong><br>
                    <span style="font-size: 10px; color: #666;"><?= esc($rubrik->deskripsi ?? '-') ?></span>
                </td>
                <td style="text-align: center; font-weight: bold; font-size: 14px;"><?= $rincianNilai ?></td>
                <td style="text-align: center; font-weight: bold;"><?= $predikat ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="score-section">
        <div class="score-value"><?= $laporan['hasil']['nilai_akhir'] ?></div>
        <div class="score-label">NILAI AKHIR</div>
        <div style="margin-top: 5px; font-size: 14px; color: #555;">
            Predikat: <strong><?= $laporan['hasil']['predikat'] ?></strong>
        </div>
        <div style="margin-top: 10px; font-weight: bold; font-size: 18px; color: <?= $laporan['hasil']['lulus'] ? '#27ae60' : '#e74c3c' ?>;">
            <?= $laporan['hasil']['lulus'] ? 'TUNTAS' : 'BELUM TUNTAS' ?>
        </div>
    </div>

    <?php if (!empty($laporan['analisis'])): 
        $analisis = $laporan['analisis'];
        $deskripsi = is_object($analisis) ? ($analisis->deskripsi_kualitatif ?? '') : ($analisis['kesimpulan'] ?? '');
    ?>
    <h3>Analisis Pencapaian</h3>
    <p><?= esc($deskripsi) ?></p>
    <?php endif; ?>

    <div class="footer">
        <div class="ttd-box">
            <p>Mengetahui,</p>
            <p>Guru Mata Pelajaran</p>
            <div class="ttd-space"></div>
            <p class="ttd-name"><?= esc($laporan['praktik']['guru'] ?? '-') ?></p>
            <p>NIP. <?= esc($laporan['praktik']['nip_guru'] ?? '-') ?></p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>