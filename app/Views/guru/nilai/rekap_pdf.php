<!DOCTYPE html>
<html>
<head>
    <title>Rekap Nilai - <?= $kelas->nama_kelas ?></title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .info { margin-bottom: 20px; }
        .info table { border: none; }
        .info td { padding: 2px 5px; }
        .table-data { width: 100%; border-collapse: collapse; margin-block: 10px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 6px; text-align: center; }
        .table-data th { background-color: #f2f2f2; font-weight: bold; font-size: 8pt; }
        .text-start { text-align: left !important; }
        .footer { margin-top: 30px; }
        .footer-table { width: 100%; border: none; }
        .footer-table td { text-align: center; width: 33%; }
        .sig-space { height: 60px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAPITULASI NILAI AKHIR SEMESTER</h2>
        <div style="font-size: 11pt; font-weight: bold;"><?= $setting->nama_sekolah ?? 'NAMA SEKOLAH' ?></div>
    </div>

    <div class="info">
        <table class="info-table">
            <tr>
                <td style="width: 120px;">Mata Pelajaran</td>
                <td>: <?= $mapel->nama_mapel ?></td>
                <td style="width: 120px;">Tahun Pelajaran</td>
                <td>: <?= $tp->tahun ?></td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>: <?= $kelas->nama_kelas ?></td>
                <td>Semester</td>
                <td>: <?= $smt->nama_smt ?></td>
            </tr>
            <?php
            $first_na = !empty($nilai_akhir) ? reset($nilai_akhir) : null;
            $b_proc = $first_na ? $first_na->bobot_proses : 40;
            $b_sts = $first_na ? ($first_na->bobot_sts ?? 0) : 20;
            $b_sas = $first_na ? $first_na->bobot_sas : 20;
            $b_praktik = $first_na ? ($first_na->bobot_praktik ?? 0) : 20;
            ?>
            <tr>
                <td>Bobot Penilaian</td>
                <td colspan="3">: Sumatif (<?= $b_proc ?>%), STS (<?= $b_sts ?>%), SAS (<?= $b_sas ?>%), Praktik (<?= $b_praktik ?>%)</td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 80px;">NISN</th>
                <th style="width: 60px;">Rerata<br>LM</th>
                <th style="width: 60px;">Nilai<br>STS</th>
                <?php if($b_praktik > 0): ?>
                    <th style="width: 60px;">Nilai<br>Praktik</th>
                <?php endif; ?>
                <th style="width: 60px;">Nilai<br>SAS</th>
                <th style="width: 60px;">Nilai<br>Rapor</th>
                <th style="width: 40px;">Pred</th>
                <th>Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($siswa as $s): ?>
                <?php 
                $id = $s->id_siswa;
                $avg_p = isset($rekap_proses[$id]) && $rekap_proses[$id]['count'] > 0 ? round($rekap_proses[$id]['total']/$rekap_proses[$id]['count']) : 0;
                $sts = $rekap_sts[$id] ?? 0;
                $praktik_val = $rekap_praktik[$id] ?? 0;
                $na = $nilai_akhir[$id] ?? null;
                $sas = $na ? $na->nilai_sas : 0;
                $rapor = $na ? $na->nilai_rapor : 0;
                $pred = \App\Models\NilaiModel::getPredikat($rapor ?: $avg_p);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="text-start"><?= $s->nama ?></td>
                    <td><?= $s->nisn ?></td>
                    <td><?= $avg_p ?></td>
                    <td><?= $sts ?></td>
                    <?php if($b_praktik > 0): ?>
                        <td><?= $praktik_val ?></td>
                    <?php endif; ?>
                    <td><?= $sas ?></td>
                    <td><?= $rapor ?></td>
                    <td><?= $pred['huruf'] ?></td>
                    <td class="text-start" style="font-size: 7pt;">
                        <?php if($na): ?>
                            <strong>Tinggi:</strong> <?= esc($na->deskripsi_tinggi) ?><br>
                            <strong>Rendah:</strong> <?= esc($na->deskripsi_rendah) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    Mengetahui,<br>Kepala Sekolah
                    <div class="sig-space"></div>
                    <strong><?= $setting->kepala_sekolah ?? '(...........................)' ?></strong><br>
                    NIP. <?= $setting->nip_kepala ?? '-' ?>
                </td>
                <td></td>
                <td>
                    <?= $setting->kota ?? 'Kota' ?>, <?= date('d F Y') ?><br>Guru Mata Pelajaran
                    <div class="sig-space"></div>
                    <strong><?= $guru->nama_guru ?? ($user->nama_lengkap ?? 'Guru') ?></strong><br>
                    NIP. <?= $guru->nip ?? ($guru->username ?? '-') ?>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
