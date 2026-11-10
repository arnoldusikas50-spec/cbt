<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Paket <?= esc($paket_letter) ?> — <?= esc($kartu->judul ?? 'Kartu Soal') ?></title>
    <style>
        @page { margin: 1.5cm; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #000;
        }

        h1, h2, h3, h4 { margin: 0 0 8px 0; text-align: center; }

        table { width: 100%; border-collapse: collapse; font-size: 10pt; }

        .header-table td { vertical-align: top; padding: 2px 0; }

        .bordered-table { margin-bottom: 18px; page-break-inside: auto; }
        .bordered-table th, .bordered-table td { border: 1px solid #000; padding: 5px 7px; vertical-align: top; }
        .bordered-table th { text-align: center; font-weight: bold; background-color: #e8e8e8; }

        .page-break { page-break-before: always; }

        /* Kartu soal container per butir */
        .soal-container {
            border: 2px solid #000;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .soal-header {
            border-bottom: 2px solid #000;
            padding: 7px 8px;
            background-color: #f2f2f2;
        }
        .soal-body { width: 100%; border-collapse: collapse; }
        .soal-body td { vertical-align: top; }
        .soal-left {
            width: 38%;
            border-right: 2px solid #000;
            padding: 0;
        }
        .soal-right { width: 62%; padding: 10px; }

        .box-field { border-bottom: 1px solid #000; padding: 5px 7px; }
        .box-field:last-child { border-bottom: none; }
        .box-title { font-weight: bold; font-size: 9pt; margin-bottom: 2px; color: #333; }
        .box-content { font-size: 10pt; }

        .ranah-badge {
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 8pt;
            font-weight: bold;
            margin-right: 3px;
        }
        .ranah-pengetahuan { background: #e8f5e9; border-color: #a5d6a7; }
        .ranah-sikap       { background: #e3f2fd; border-color: #90caf9; }
        .ranah-keterampilan{ background: #fff3e0; border-color: #ffcc80; }

        .level-badge { display: inline-block; border: 1px solid #999; border-radius: 3px; padding: 1px 6px; font-size: 9pt; font-weight: bold; }
        .level-c1, .level-c2 { background: #dbeafe; border-color: #93c5fd; }
        .level-c3, .level-c4 { background: #fef3c7; border-color: #fcd34d; }
        .level-c5, .level-c6 { background: #fce7f3; border-color: #f9a8d4; }

        .kunci-box { margin-top: 10px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9; font-size: 10pt; }

        .sign-area { width: 100%; margin-top: 40px; }
        .sign-table { width: 100%; border: none; }
        .sign-table td { width: 33%; border: none; padding: 5px; vertical-align: top; text-align: center; }
        .sign-pad { height: 70px; }

        .paket-badge {
            display: inline-block;
            background: #1d4ed8;
            color: #fff;
            font-weight: bold;
            font-size: 12pt;
            padding: 3px 16px;
            border-radius: 4px;
            letter-spacing: 1px;
        }

        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .soal-content img, img { max-width: 100%; height: auto; display: block; margin: 6px auto; }
        .soal-content table { border-collapse: collapse; font-size: 9pt; }
        .soal-content table td, .soal-content table th { border: 1px solid #000; padding: 3px 6px; }
        .soal-content svg { max-width: 100%; height: auto; display: block; margin: 4px auto; }
    </style>
</head>
<body>

<?php
$schoolName = !empty($setting->sekolah) ? esc($setting->sekolah) : '.......................................';
$kepsek     = !empty($setting->kepsek)  ? esc($setting->kepsek)  : '.......................................';
$nipKepsek  = !empty($setting->nip_kepsek) ? esc($setting->nip_kepsek) : '.......................................';
$kepsekStr  = 'NIP. ' . $nipKepsek;

$guruName = esc($kartu->nama_guru ?? 'Administrator');
$guruNip  = esc($kartu->nip_guru  ?? '-');

$levelKognitifLabel = [
    'C1' => 'C1 – Mengingat',
    'C2' => 'C2 – Memahami',
    'C3' => 'C3 – Menerapkan',
    'C4' => 'C4 – Menganalisis',
    'C5' => 'C5 – Mengevaluasi',
    'C6' => 'C6 – Mencipta',
];

$totalSoal = count($soals ?? []);
?>

<!-- ============================================================ -->
<!-- HALAMAN COVER / RINGKASAN PAKET                              -->
<!-- ============================================================ -->
<h3>KARTU SOAL UJIAN</h3>
<h4 style="font-size: 12pt; font-weight: normal; margin-bottom: 4px;"><?= esc($kartu->judul ?? '') ?></h4>
<div style="text-align:center; margin-bottom: 18px;">
    <span class="paket-badge">PAKET <?= esc($paket_letter) ?></span>
    <?php if (!empty($paket_label)): ?>
        <div style="font-size:9pt; color:#555; margin-top:4px;"><?= esc($paket_label) ?></div>
    <?php endif; ?>
</div>

<table class="header-table" style="margin-bottom:20px;">
    <tr>
        <td width="20%">Satuan Pendidikan</td><td width="1%">:</td><td width="35%"><b><?= $schoolName ?></b></td>
        <td width="15%">Kurikulum</td><td width="1%">:</td><td width="28%">Kurikulum Merdeka</td>
    </tr>
    <tr>
        <td>Mata Pelajaran</td><td>:</td><td><?= esc($mapel->nama_mapel ?? '-') ?></td>
        <td>Jenis Ujian</td><td>:</td><td><?= esc($jenis_ujian->nama_jenis ?? '-') ?></td>
    </tr>
    <tr>
        <td>Kelas / Semester</td><td>:</td><td><?= esc($kelas->nama_kelas ?? '-') ?> / <?= esc($smt_row->nama_smt ?? '-') ?></td>
        <td>Paket Soal</td><td>:</td><td><b>Paket <?= esc($paket_letter) ?></b></td>
    </tr>
    <tr>
        <td>Tahun Pelajaran</td><td>:</td><td><?= esc($tp_row->tahun ?? '-') ?></td>
        <td>Jumlah Soal</td><td>:</td><td><?= $totalSoal ?> Soal</td>
    </tr>
    <tr>
        <td>Penyusun</td><td>:</td><td><?= $guruName ?></td>
        <td>Kode Bank</td><td>:</td><td style="font-size:9pt; color:#444;"><?= esc($bank->bank_kode ?? '-') ?></td>
    </tr>
</table>

<!-- Ringkasan Distribusi Soal per Jenis -->
<?php
$jenisCounts = [];
foreach ($soals ?? [] as $s) {
    $jns = (int) ($s->jenis ?? 1);
    $jenisCounts[$jns] = ($jenisCounts[$jns] ?? 0) + 1;
}
if (!empty($jenisCounts)): ?>
<table class="bordered-table" style="margin-bottom:20px;">
    <thead>
        <tr>
            <th colspan="2" style="text-align:center; background:#d1d5db;">Distribusi Butir Soal — Paket <?= esc($paket_letter) ?></th>
        </tr>
        <tr>
            <th width="70%">Jenis / Bentuk Soal</th>
            <th width="30%">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($jenisCounts as $jns => $cnt): ?>
        <tr>
            <td><?= esc($jenis_soal_labels[$jns] ?? 'Lainnya') ?></td>
            <td class="text-center"><?= $cnt ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>Total</strong></td>
            <td class="text-center"><strong><?= $totalSoal ?></strong></td>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<!-- Tanda tangan cover -->
<div class="sign-area">
    <table class="sign-table">
        <tr>
            <td>
                Mengetahui,<br>Kepala Sekolah
                <div class="sign-pad"></div>
                <strong><?= $kepsek ?></strong><br>
                <?= $kepsekStr ?>
            </td>
            <td>
                Koordinator Kurikulum<br>&nbsp;
                <div class="sign-pad"></div>
                <strong>____________________</strong><br>NIP. -
            </td>
            <td>
                <br>Guru Mata Pelajaran
                <div class="sign-pad"></div>
                <strong><?= $guruName ?></strong><br>
                NIP. <?= $guruNip ?>
            </td>
        </tr>
    </table>
</div>

<!-- ============================================================ -->
<!-- KARTU SOAL PER BUTIR                                         -->
<!-- ============================================================ -->
<?php if (!empty($soals)): ?>
<div class="page-break"></div>

<h3 style="margin-bottom:4px;">KARTU SOAL — PAKET <?= esc($paket_letter) ?></h3>
<h4 style="font-size:11pt; font-weight:normal; margin-bottom:18px;">
    <?= esc($mapel->nama_mapel ?? '-') ?> — <?= esc($kelas->nama_kelas ?? '-') ?> — <?= esc($tp_row->tahun ?? '-') ?> / <?= esc($smt_row->nama_smt ?? '-') ?>
</h4>

<?php foreach ($soals as $soal):
    $noSoal   = (int) ($soal->nomor_soal ?? 0);
    $jenisSoal = (int) ($soal->jenis ?? 1);
    $jnsLabel  = $jenis_soal_labels[$jenisSoal] ?? 'Lainnya';

    // Cross-reference ke kisi-kisi: cari item dengan nomor & jenis sama
    $kisiItem = $kisi_map[$noSoal][$jenisSoal]
             ?? $kisi_map[$noSoal][array_key_first($kisi_map[$noSoal] ?? [])]
             ?? null;

    $lvl         = strtoupper($kisiItem->level_kognitif ?? $kisiItem->tujuan_level_kognitif ?? '-');
    $lvlLower    = 'level-' . strtolower($lvl);
    $lvlLabel    = $levelKognitifLabel[$lvl] ?? $lvl;
    $tujuanKode  = $kisiItem->tujuan_kode ?? ($kisiItem->kode ?? '');
    $tujuanTeks  = $kisiItem->tujuan ?? $kisiItem->tujuan_teks ?? '-';
    $cpKode      = $kisiItem->cp ?? $kisiItem->tujuan_kode_cp ?? '';
    $materi      = $kisiItem->materi ?? $kisiItem->tujuan_materi_pokok ?? '-';
    $indikator   = $kisiItem->indikator_soal ?? $kisiItem->topik ?? '-';
    $estimasi    = (int) ($kisiItem->estimasi_waktu ?? 0);

    $tujuanObj = null;
    if (!empty($kisiItem->id_tujuan) && !empty($tujuan_map)) {
        $tujuanObj = $tujuan_map[(int) $kisiItem->id_tujuan] ?? null;
    }

    $tpSikap        = ($tujuanObj && !empty($tujuanObj->tp_sikap)) ? $tujuanObj->tp_sikap : null;
    $tpKeterampilan = ($tujuanObj && !empty($tujuanObj->tp_keterampilan)) ? $tujuanObj->tp_keterampilan : null;
?>
<div class="soal-container">
    <!-- Header -->
    <div class="soal-header">
        <table style="width:100%; font-size:10pt;" cellpadding="0" cellspacing="0">
            <tr>
                <td width="14%"><strong>Mata Pelajaran</strong></td><td width="1%">:</td>
                <td width="31%"><?= esc($mapel->nama_mapel ?? '-') ?></td>
                <td width="12%"><strong>Jenis Soal</strong></td><td width="1%">:</td>
                <td width="41%"><?= $jnsLabel ?></td>
            </tr>
            <tr>
                <td><strong>Kelas / Smt</strong></td><td>:</td>
                <td><?= esc($kelas->nama_kelas ?? '-') ?> / <?= esc($smt_row->nama_smt ?? '-') ?></td>
                <td><strong>Penyusun</strong></td><td>:</td>
                <td><?= $guruName ?></td>
            </tr>
            <tr>
                <td><strong>Tahun Pelajaran</strong></td><td>:</td>
                <td><?= esc($tp_row->tahun ?? '-') ?></td>
                <td><strong>Paket / No. Soal</strong></td><td>:</td>
                <td><strong>Paket <?= esc($paket_letter) ?> / No. <?= $noSoal ?></strong></td>
            </tr>
        </table>
    </div>

    <table class="soal-body">
        <tr>
            <!-- Kolom kiri: metadata kisi-kisi -->
            <td class="soal-left">
                <?php if ($cpKode || $tujuanKode): ?>
                <div class="box-field">
                    <div class="box-title">Referensi CP &amp; TP:</div>
                    <div class="box-content" style="font-size:9pt;">
                        <?php if ($cpKode): ?><strong>CP:</strong> <?= esc($cpKode) ?><br><?php endif; ?>
                        <?php if ($tujuanKode): ?><strong>Kode TP:</strong> <?= esc($tujuanKode) ?><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="box-field">
                    <div class="box-title"><span class="ranah-badge ranah-pengetahuan">Pengetahuan</span> Tujuan Pembelajaran:</div>
                    <div class="box-content" style="font-size:9pt;"><?= esc($tujuanTeks) ?></div>
                </div>

                <?php if ($tpSikap): ?>
                <div class="box-field">
                    <div class="box-title"><span class="ranah-badge ranah-sikap">Sikap</span> Tujuan Pembelajaran:</div>
                    <div class="box-content" style="font-size:9pt;"><?= esc($tpSikap) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($tpKeterampilan): ?>
                <div class="box-field">
                    <div class="box-title"><span class="ranah-badge ranah-keterampilan">Keterampilan</span> Tujuan Pembelajaran:</div>
                    <div class="box-content" style="font-size:9pt;"><?= esc($tpKeterampilan) ?></div>
                </div>
                <?php endif; ?>

                <div class="box-field">
                    <div class="box-title">Materi / Bahan Kajian:</div>
                    <div class="box-content"><?= esc($materi) ?></div>
                </div>

                <div class="box-field">
                    <div class="box-title">Indikator Soal:</div>
                    <div class="box-content" style="font-size:9pt;"><?= esc($indikator) ?></div>
                </div>

                <div class="box-field" style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <span class="box-title">Level: </span>
                        <span class="level-badge <?= $lvlLower ?>"><?= $lvl ?></span>
                        <span style="font-size:8pt; color:#666;"> — <?= $lvlLabel ?></span>
                    </div>
                    <?php if ($estimasi > 0): ?>
                    <div style="font-size:8pt; color:#666;">&#9200; <?= $estimasi ?> mnt</div>
                    <?php endif; ?>
                </div>
            </td>

            <!-- Kolom kanan: butir soal dari bank -->
            <td class="soal-right">
                <div style="margin-bottom:10px;">
                    <strong style="font-size:11pt; text-decoration:underline;">Butir Soal No. <?= $noSoal ?></strong>
                    <span style="font-size:9pt; color:#666; margin-left:10px;">[<?= $jnsLabel ?>]</span>
                </div>

                <div class="soal-content" style="min-height:140px; border-top:1px dashed #ccc; padding-top:8px;">
                    <?php if (!empty($soal->soal)): ?>
                    <div style="margin-top:5px; line-height:1.6;">
                        <?= $soal->soal ?>

                        <?php if ($jenisSoal === 1): ?>
                        <!-- Pilihan Ganda -->
                        <div style="margin-top:10px; margin-left:10px;">
                            <?php foreach (['a','b','c','d','e'] as $optChar):
                                $optStr = $soal->{"opsi_{$optChar}"} ?? '';
                                if (!empty(trim(strip_tags($optStr))) || strpos((string)$optStr, '<img') !== false): ?>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:3px; border:none;">
                                <tr>
                                    <td width="25px" style="vertical-align:top; border:none;"><strong><?= strtoupper($optChar) ?>.</strong></td>
                                    <td style="vertical-align:top; border:none;"><?= $optStr ?></td>
                                </tr>
                            </table>
                            <?php endif; endforeach; ?>
                        </div>

                        <?php elseif ($jenisSoal === 2): ?>
                        <!-- PG Kompleks -->
                        <div style="margin-top:10px; margin-left:10px;">
                            <?php
                            $opsiArr = json_decode($soal->opsi_a ?? '[]', true);
                            if (is_array($opsiArr)) {
                                foreach ($opsiArr as $k => $v) {
                                    $lbl = ($k !== (string)(int)$k) ? strtoupper($k) . '.' : '—';
                                    echo "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom:3px;border:none;'>";
                                    echo "<tr><td width='25px' style='vertical-align:top;border:none;'><strong>{$lbl}</strong></td><td style='vertical-align:top;border:none;'>{$v}</td></tr>";
                                    echo "</table>";
                                }
                            }
                            ?>
                        </div>

                        <?php elseif ($jenisSoal === 3): ?>
                        <!-- Menjodohkan -->
                        <?php
                        $jodohData = json_decode($soal->jawaban ?? $soal->opsi_a ?? '{}', true);
                        $kiri  = $jodohData['kiri']  ?? $jodohData['model']  ?? [];
                        $kanan = $jodohData['kanan']  ?? $jodohData['tabel']  ?? [];
                        if (!empty($kiri) || !empty($kanan)): ?>
                        <table style="width:100%; margin-top:8px; font-size:9pt;" class="bordered-table">
                            <tr>
                                <th>Pernyataan</th><th>Pasangan</th>
                            </tr>
                            <?php
                            $maxLen = max(count((array)$kiri), count((array)$kanan));
                            $kiriArr  = array_values((array)$kiri);
                            $kananArr = array_values((array)$kanan);
                            for ($ri = 0; $ri < $maxLen; $ri++): ?>
                            <tr>
                                <td><?= esc($kiriArr[$ri] ?? '') ?></td>
                                <td><?= esc($kananArr[$ri] ?? '') ?></td>
                            </tr>
                            <?php endfor; ?>
                        </table>
                        <?php endif; ?>

                        <?php elseif ($jenisSoal === 7): ?>
                        <!-- Isian Rumpang: teks soal sudah berisi ___ -->
                        <?php endif; // end jenis switch ?>
                    </div>

                    <!-- Kunci Jawaban -->
                    <?php
                    $jawaban = $soal->jawaban ?? '';
                    if ($jenisSoal === 1 || $jenisSoal === 4 || $jenisSoal === 7) {
                        echo "<div class='kunci-box'><strong>Kunci Jawaban:</strong> " . esc(strtoupper((string)$jawaban)) . "</div>";
                    } elseif ($jenisSoal === 5) {
                        echo "<div class='kunci-box'><strong>Kunci Jawaban:</strong> " . $jawaban . "</div>";
                    } elseif ($jenisSoal === 2) {
                        $jwArr = @json_decode((string)$jawaban, true) ?: [];
                        $jwStr = implode(', ', array_map('strtoupper', (array)$jwArr));
                        echo "<div class='kunci-box'><strong>Kunci Jawaban:</strong> " . esc($jwStr) . "</div>";
                    } elseif ($jenisSoal === 6) {
                        $ordArr = @json_decode((string)$jawaban, true) ?: [];
                        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $ordStr  = implode(' → ', array_map(fn($i) => $letters[$i] ?? ($i+1), (array)$ordArr));
                        echo "<div class='kunci-box'><strong>Urutan Benar:</strong> " . esc($ordStr) . "</div>";
                    } else {
                        echo "<div class='kunci-box'><strong>Kunci:</strong> <em>Tersimpan dalam sistem</em></div>";
                    }
                    ?>

                    <?php if (!empty($soal->pembahasan) && trim(strip_tags((string)$soal->pembahasan)) !== ''): ?>
                    <div style="margin-top:10px; padding:6px 8px; background:#fefce8; border:1px solid #fde68a; border-radius:4px; font-size:9pt;">
                        <strong>Pembahasan:</strong><br>
                        <?= $soal->pembahasan ?>
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div style="padding:20px; text-align:center; color:#999; border:1px dashed #ddd; margin-top:10px; border-radius:4px;">
                        <em>[ Butir soal belum tersedia ]</em>
                    </div>
                    <?php endif; // !empty soal->soal ?>
                </div>
            </td>
        </tr>
    </table>
</div>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>
