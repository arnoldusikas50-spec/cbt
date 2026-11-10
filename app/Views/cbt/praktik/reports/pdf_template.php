<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Praktik - <?= esc($laporan['info_praktik']['judul']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; }
        .info-section { margin-bottom: 20px; width: 100%; }
        .info-section td { padding: 3px 0; vertical-align: top; }
        .info-section td.label { width: 150px; font-weight: bold; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th, .data-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .data-table th { background-color: #f2f2f2; font-weight: bold; }
        .footer { margin-top: 30px; text-align: right; font-size: 11px; color: #777; }
        .ttd-container { margin-top: 50px; width: 100%; }
        .ttd-box { float: right; width: 250px; text-align: center; }
        .ttd-space { height: 70px; }
        .ttd-name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($setting->nama_sekolah ?? 'Lembaga Pendidikan') ?></h1>
        <h2>LAPORAN HASIL UJIAN PRAKTIK</h2>
    </div>

    <table class="info-section">
        <tr>
            <td class="label">Judul Ujian</td>
            <td>: <?= esc($laporan['info_praktik']['judul']) ?></td>
        </tr>
        <tr>
            <td class="label">Mata Pelajaran</td>
            <td>: <?= esc($laporan['info_praktik']['mapel'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="label">Mode</td>
            <td>: <?= strtoupper($laporan['info_praktik']['kktp_mode'] ?? 'KKM') ?></td>
        </tr>
        <tr>
            <td class="label">Tanggal Cetak</td>
            <td>: <?= $tanggal_cetak ?></td>
        </tr>
    </table>

    <h3>Ringkasan Statistik</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Total Peserta</th>
                <th>Sudah Submit</th>
                <th>Rata-rata Nilai</th>
                <th>Persentase Tuntas</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= count($laporan['data_siswa']) ?> Orang</td>
                <td><?= count(array_filter($laporan['data_siswa'], fn($s) => !empty($s['nilai']))) ?> Orang</td>
                <td><?= round($laporan['statistik']['nilai']['rata_rata'], 2) ?></td>
                <td><?= $laporan['statistik']['distribusi']['persen_lulus'] ?>%</td>
            </tr>
        </tbody>
    </table>

    <h3>Detail Hasil Siswa</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 100px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 80px;">Kelas</th>
                <th style="width: 60px;">Nilai</th>
                <th style="width: 50px;">Pred</th>
                <th style="width: 80px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($laporan['data_siswa'] as $s): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($s['nis']) ?></td>
                <td><?= esc($s['nama']) ?></td>
                <td><?= esc($s['kelas']) ?></td>
                <td style="text-align: center; font-weight: bold;"><?= $s['nilai'] ?: '-' ?></td>
                <td style="text-align: center;"><?= $s['singkatan'] ?? '-' ?></td>
                <td style="text-align: center;"><?= $s['lulus'] ? 'Tuntas' : 'Remedial' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="ttd-container">
        <div class="ttd-box">
            <p>Mengetahui,</p>
            <p>Guru Mata Pelajaran</p>
            <div class="ttd-space"></div>
            <p class="ttd-name"><?= esc($laporan['info_praktik']['guru'] ?? '-') ?></p>
            <p>NIP. <?= esc($laporan['info_praktik']['nip_guru'] ?? '-') ?></p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Dicetak otomatis oleh sistem CBT pada <?= date('d/m/Y H:i') ?>
    </div>
</body>
</html>