<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tujuan Pembelajaran - <?= esc($mapel_nama) ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        .header-subtitle {
            font-size: 12pt;
            margin: 0 0 5px 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #777;
            padding: 6px 8px;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-nowrap { white-space: nowrap; }
        .text-muted { color: #666; font-size: 8pt; }
        
        .level-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8pt;
            text-align: center;
            min-width: 25px;
        }
        .level-C1, .level-C2 { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .level-C3, .level-C4 { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .level-C5, .level-C6 { background-color: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8; }
        
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-name {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-nip {
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="header-title">DAFTAR TUJUAN PEMBELAJARAN (TP)</h1>
        <h2 class="header-subtitle"><?= esc($sekolah->sekolah ?? 'NAMA SEKOLAH') ?></h2>
        <div style="font-size: 10pt;">Tahun Pelajaran: <?= esc($tp) ?> - Semester: <?= esc($smt) ?></div>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Mata Pelajaran</strong></td>
            <td width="2%">:</td>
            <td width="33%"><?= esc($mapel_nama) ?></td>
            
            <td width="15%"><strong>Nama Guru</strong></td>
            <td width="2%">:</td>
            <td width="33%"><?= esc($guru->nama_guru ?? '-') ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="16%">Mata Pelajaran</th>
                <th width="8%">Kelas</th>
                <th width="8%">Kode</th>
                <th>Rumusan Tujuan Pembelajaran</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($list)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">
                        <em>Tidak ada rincian data Tujuan Pembelajaran yang ditemukan.</em>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($list as $i => $item): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= esc($item->nama_mapel ?: '-') ?></td>
                        <td class="text-center">
                            <?= $item->tingkat > 0 ? 'Kls ' . $item->tingkat : '-' ?>
                        </td>
                        <td class="text-center"><strong><?= esc($item->kode ?: '-') ?></strong></td>
                        <td>
                            <div style="font-weight: bold;">
                                <?= nl2br(esc($item->tujuan)) ?>
                            </div>
                            <?php if (!empty($item->materi_pokok)): ?>
                                <div style="margin-top: 5px; font-size: 8pt;">
                                    <em>Topik/Materi: <?= esc($item->materi_pokok) ?></em>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <div>............................., <?= date('d F Y') ?></div>
            <div>Guru Mata Pelajaran</div>
            <div class="signature-name"><?= esc($guru->nama_guru ?? '_______________________') ?></div>
            <div class="signature-nip">NIP. <?= esc($guru->nip ?? '_______________________') ?></div>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
