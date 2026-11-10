<?php
function getPredikatKktp(float $score): string {
    if ($score >= 86) return 'M';
    if ($score >= 66) return 'C';
    if ($score >= 41) return 'L';
    if ($score > 0)   return 'BB';
    return '-';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Penilaian Praktik – <?= esc($praktik->judul) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111;
            padding: 15px 20px;
        }

        /* ── Header ── */
        .header-wrap {
            border-bottom: 2.5px solid #111;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .header-sub {
            text-align: center;
            font-size: 10px;
        }
        .header-sekolah {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* ── Info row ── */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 4px;
            font-size: 10px;
        }
        .info-table td.label { font-weight: bold; white-space: nowrap; width: 1%; }
        .info-table td.sep   { width: 8px; }

        /* ── Rubrik legend ── */
        .legend-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .legend-table td {
            border: 1px solid #999;
            padding: 3px 5px;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
        }
        .legend-table .lhd {
            background: #e8e8e8;
            font-weight: bold;
            font-size: 10px;
        }

        /* ── Main data table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            border: 1px solid #555;
            padding: 4px 3px;
            vertical-align: middle;
        }
        .data-table thead th {
            background-color: #d9d9d9;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
        }
        .data-table thead th.th-krit {
            font-size: 8.5px;
            line-height: 1.3;
        }
        .data-table tbody td {
            font-size: 9.5px;
        }
        .data-table tbody td.tc { text-align: center; }
        .data-table tbody td.tr { text-align: right;  }
        .data-table tbody tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .data-table tbody td.score-cell {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
        }
        .data-table td.akhir-cell {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            background-color: #eef3ff;
        }
        .data-table td.predikat-cell {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        /* ── Footer ── */
        .footer-wrap {
            margin-top: 20px;
            width: 100%;
        }
        .ttd-box {
            float: right;
            width: 220px;
            text-align: center;
            font-size: 10px;
            border: 1px solid #ccc;
            padding: 8px 10px 0 10px;
        }
        .ttd-space { height: 50px; }
        .ttd-name  { border-top: 1px solid #555; padding-top: 3px; }

        .legend-keterangan {
            font-size: 8.5px;
            margin-top: 8px;
            color: #444;
        }
        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>

    <!-- ═══ HEADER ═══ -->
    <div class="header-wrap">
        <?php if (!empty($setting->nama_sekolah)): ?>
        <div class="header-sekolah"><?= esc($setting->nama_sekolah) ?></div>
        <?php endif; ?>
        <div class="header-title">Lembar Penilaian Ujian Praktik</div>
        <div class="header-sub">
            <b><?= esc($praktik->judul) ?></b>
            &nbsp;|&nbsp; Mata Pelajaran: <b><?= esc($praktik->nama_mapel ?? '-') ?></b>
        </div>
    </div>

    <!-- ═══ INFO UJIAN ═══ -->
    <table class="info-table">
        <tr>
            <td class="label">Kelas</td>
            <td class="sep">:</td>
            <td><?= empty($id_kelas_selected) ? 'Semua Kelas' : esc($nama_kelas_selected ?? 'Semua Kelas') ?></td>
            <td width="40%"></td>
            <td class="label">Target</td>
            <td class="sep">:</td>
            <td><?= strtoupper($praktik->kktp_mode ?? 'KKM') ?>: <?= $praktik->kkm ?? 75 ?></td>
        </tr>
        <tr>
            <td class="label">Total Peserta</td>
            <td class="sep">:</td>
            <td><?= count($siswa) ?> Orang</td>
            <td></td>
            <td class="label">Tanggal Cetak</td>
            <td class="sep">:</td>
            <td><?= date('d F Y') ?></td>
        </tr>
    </table>

    <!-- ═══ RUBRIK LEGEND ═══ -->
    <?php if (!empty($rubrik)): ?>
    <table class="legend-table">
        <tr>
            <td class="lhd" style="width:80px;">Kriteria</td>
            <?php foreach($rubrik as $r): ?>
            <td class="lhd"><?= esc($r->kode_kriteria) ?></td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td class="lhd" style="background:#f0f0f0;">Nama</td>
            <?php foreach($rubrik as $r): ?>
            <td><?= esc($r->nama_kriteria) ?></td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td class="lhd" style="background:#f0f0f0;">Bobot</td>
            <?php foreach($rubrik as $r): ?>
            <td><b><?= $r->bobot_persen ?>%</b></td>
            <?php endforeach; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- ═══ TABEL NILAI ═══ -->
    <?php
    $colCount  = count($rubrik);
    $baseWidth = 3 + 8 + 18 + 8; // No+NIS+Nama+Kelas = 37%
    $kritWidth = $colCount > 0 ? round((43 / $colCount), 1) : 0;
    // remaining: 20% for akhir+pred+catatan
    ?>
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%"  style="font-size:8px;">No</th>
                <th width="8%"  style="font-size:8px;">NIS</th>
                <th width="18%">Nama Siswa</th>
                <th width="8%"  style="font-size:8px;">Kelas</th>
                <?php foreach($rubrik as $r): ?>
                <th width="<?= $kritWidth ?>%" class="th-krit">
                    <b><?= esc($r->kode_kriteria) ?></b><br>
                    (<?= $r->bobot_persen ?>%)
                </th>
                <?php endforeach; ?>
                <th width="7%"  style="font-size:8px;">Nilai Akhir</th>
                <th width="5%"  style="font-size:8px;">Pred</th>
                <th width="8%"  style="font-size:8px;">Catatan Guru</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($siswa)): ?>
            <tr>
                <td colspan="<?= 7 + $colCount ?>" class="tc">Data siswa tidak tersedia</td>
            </tr>
            <?php else: ?>
                <?php foreach($siswa as $i => $s):
                    $s_id    = is_object($s) ? ($s->id_siswa ?? null) : ($s['id_siswa'] ?? null);
                    $s_nis   = is_object($s) ? ($s->nis ?? '-')       : ($s['nis'] ?? '-');
                    $s_nama  = is_object($s) ? ($s->nama ?? '-')      : ($s['nama'] ?? '-');
                    $s_kelas = is_object($s) ? ($s->nama_kelas ?? '-'): ($s['nama_kelas'] ?? '-');

                    $hasil   = $hasilBySiswa[$s_id] ?? null;
                    $rincian = [];
                    if ($hasil && $hasil->rincian_nilai_rubrik) {
                        $rincian = json_decode($hasil->rincian_nilai_rubrik, true) ?? [];
                    }
                    $nilai_akhir = $hasil ? (float)($hasil->nilai_akhir ?? 0) : 0;
                    $predikat    = $nilai_akhir > 0 ? getPredikatKktp($nilai_akhir) : '-';
                ?>
                <tr>
                    <td class="tc"><?= $i + 1 ?></td>
                    <td><?= esc($s_nis) ?></td>
                    <td><?= esc($s_nama) ?></td>
                    <td class="tc" style="font-size:8.5px;"><?= esc($s_kelas) ?></td>
                    <?php foreach($rubrik as $r):
                        $kid   = $r->id ?? $r->kode_kriteria;
                        $nval  = $rincian[(string)$kid] ?? $rincian[(int)$kid] ?? '';
                        $ndisp = ($nval !== '') ? number_format((float)$nval, 0) : '';
                    ?>
                    <td class="score-cell"><?= $ndisp ?></td>
                    <?php endforeach; ?>
                    <td class="akhir-cell"><?= $nilai_akhir > 0 ? number_format($nilai_akhir, 1) : '' ?></td>
                    <td class="predikat-cell"><?= $predikat ?></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ═══ KETERANGAN PREDIKAT ═══ -->
    <div class="legend-keterangan">
        Predikat KKTP: M (86-100) Mahir &nbsp;|&nbsp;
        C (66-85) Cakap &nbsp;|&nbsp;
        L (41-65) Layak &nbsp;|&nbsp;
        BB (0-40) Baru Berkembang
    </div>

    <!-- ═══ TANDA TANGAN ═══ -->
    <div class="footer-wrap clearfix">
        <div class="ttd-box">
            <div><?= date('d F Y') ?></div>
            <div>Guru Mata Pelajaran,</div>
            <div class="ttd-space"></div>
            <div class="ttd-name" style="font-weight: bold; text-decoration: underline;">
                <?= esc($praktik->nama_guru ?? '_________________________') ?>
            </div>
            <div style="margin-top: 3px;">
                NIP. <?= esc($praktik->nip_guru ?? '_________________________') ?>
            </div>
        </div>
    </div>

</body>
</html>
