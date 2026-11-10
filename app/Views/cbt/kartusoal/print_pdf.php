<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kisi-Kisi & Kartu Soal - <?= esc($kartu->judul ?? 'Untitled') ?></title>
    <style>
        /* Default Page Setup */
        @page {
            margin: 1.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }

        /* Typography */
        h1, h2, h3, h4 {
            margin: 0 0 10px 0;
            text-align: center;
        }
        
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        /* Tables layout */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .header-table {
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        /* Bordered tables - like Kisi-Kisi / Kartu Soal grids */
        .bordered-table {
            margin-bottom: 20px;
            page-break-inside: auto;
        }

        .bordered-table th, .bordered-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .bordered-table th {
            text-align: center;
            font-weight: bold;
            background-color: #e8e8e8;
        }

        .page-break {
            page-break-before: always;
        }
        
        .kartu-container {
            border: 2px solid #000;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .kartu-header {
            border-bottom: 2px solid #000;
            padding: 8px;
            background-color: #f2f2f2;
        }

        .kartu-body {
            width: 100%;
            border-collapse: collapse;
        }
        .kartu-body td {
            vertical-align: top;
        }

        .kartu-left {
            width: 38%;
            border-right: 2px solid #000;
            padding: 0;
        }

        .kartu-right {
            width: 62%;
            padding: 10px;
        }

        .kartu-left .box-field {
            border-bottom: 1px solid #000;
            padding: 6px 8px;
        }
        .kartu-left .box-field:last-child {
            border-bottom: none;
        }

        .box-title {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 3px;
            color: #333;
        }
        .box-content {
            font-size: 10pt;
        }

        /* Ranah badge */
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
        .ranah-sikap { background: #e3f2fd; border-color: #90caf9; }
        .ranah-keterampilan { background: #fff3e0; border-color: #ffcc80; }

        /* Level kognitif badge */
        .level-badge {
            display: inline-block;
            border: 1px solid #999;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 9pt;
            font-weight: bold;
        }
        .level-c1, .level-c2 { background: #dbeafe; border-color: #93c5fd; }
        .level-c3, .level-c4 { background: #fef3c7; border-color: #fcd34d; }
        .level-c5, .level-c6 { background: #fce7f3; border-color: #f9a8d4; }

        .sign-area {
            width: 100%;
            margin-top: 40px;
        }
        .sign-table {
            width: 100%;
            border: none;
        }
        .sign-table td {
            width: 33%;
            border: none;
            padding: 5px;
            vertical-align: top;
            text-align: center;
        }
        
        .sign-pad {
            height: 70px;
        }

        /* CP Info box */
        .cp-info-box {
            background: #f8f9fc;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px 8px;
            margin-bottom: 5px;
            font-size: 9pt;
        }
        .cp-info-box .cp-label {
            font-weight: bold;
            color: #555;
        }

        .soal-content img, .kartu-container img, img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 6px auto;
        }

    </style>
</head>
<body>

<?php
$schoolName = !empty($setting->sekolah) ? esc($setting->sekolah) : '.......................................';
$kepsek     = !empty($setting->kepsek) ? esc($setting->kepsek) : '.......................................';
$nipKepsek  = !empty($setting->nip_kepsek) ? esc($setting->nip_kepsek) : '.......................................';
$kepsekStr  = "NIP. " . $nipKepsek;

$guruName = esc($kartu->nama_guru ?? 'Administrator');
$guruNip  = esc($kartu->nip_guru ?? '-');

$jenisInfoLabels = [
    1 => 'Pilihan Ganda',
    2 => 'Pilihan Ganda Kompleks',
    3 => 'Menjodohkan',
    4 => 'Isian Singkat',
    5 => 'Uraian / Essay',
    6 => 'Urutan',
    7 => 'Isian Rumpang',
];

// Aggregate Bentuk Soal counts
$bentukSoalCounts = [];
foreach ($items as $it) {
    if (!isset($bentukSoalCounts[(int)$it->jenis_soal])) {
        $bentukSoalCounts[(int)$it->jenis_soal] = 0;
    }
    $bentukSoalCounts[(int)$it->jenis_soal]++;
}

$bentukSoalArr = [];
foreach ($bentukSoalCounts as $js => $count) {
    $bentukSoalArr[] = ($jenisInfoLabels[$js] ?? 'Lainnya') . ' (' . $count . ')';
}
$bentukSoalText = empty($bentukSoalArr) ? '-' : implode(', ', $bentukSoalArr);

$totalSoal   = count($items);
$kurikulum   = 'Kurikulum Merdeka (Sesuai Permendikbudristek No. 12 Tahun 2024)';
$alokasiWaktu = array_sum(array_map(fn($i) => (int)($i->estimasi_waktu ?? 0), $items));
$alokasiWaktuTxt = $alokasiWaktu > 0 ? $alokasiWaktu . ' Menit' : '- Menit';

// Level kognitif helper
$levelKognitifLabel = [
    'C1' => 'C1 – Mengingat',
    'C2' => 'C2 – Memahami',
    'C3' => 'C3 – Menerapkan',
    'C4' => 'C4 – Menganalisis',
    'C5' => 'C5 – Mengevaluasi',
    'C6' => 'C6 – Mencipta',
];
?>

<!-- ============================================================ -->
<!-- KISI KISI SECTION                                            -->
<!-- ============================================================ -->
<div class="kisi-kisi-section">
    <h3>KISI-KISI PENULISAN SOAL UJIAN</h3>
    <h4 style="font-size: 12pt; font-weight: normal; margin-bottom: 5px;"><?= esc($kartu->judul ?? '') ?></h4>
    
    <table class="header-table" style="margin-top:20px;">
        <tr>
            <td width="20%">Satuan Pendidikan</td>
            <td width="1%">:</td>
            <td width="35%"><b><?= $schoolName ?></b></td>
            
            <td width="15%">Kurikulum</td>
            <td width="1%">:</td>
            <td width="28%">Kurikulum Merdeka</td>
        </tr>
        <tr>
            <td>Mata Pelajaran</td>
            <td>:</td>
            <td><?= esc($mapel->nama_mapel ?? '-') ?></td>
            
            <td>Jenis Ujian</td>
            <td>:</td>
            <td><?= esc($jenis_ujian->nama_jenis ?? '-') ?></td>
        </tr>
        <tr>
            <td>Kelas / Semester</td>
            <td>:</td>
            <td><?= esc($kelas->nama_kelas ?? '-') ?> / <?= esc($smt_row->nama_smt ?? '-') ?></td>
            
            <td>Alokasi Waktu</td>
            <td>:</td>
            <td><?= $alokasiWaktuTxt ?></td>
        </tr>
        <tr>
            <td>Tahun Pelajaran</td>
            <td>:</td>
            <td><?= esc($tp_row->tahun ?? '-') ?></td>
            
            <td>Jumlah Soal</td>
            <td>:</td>
            <td><?= $totalSoal ?> Soal</td>
        </tr>
        <tr>
            <td>Penyusun</td>
            <td>:</td>
            <td><?= $guruName ?></td>
            
            <td>Bentuk Soal</td>
            <td>:</td>
            <td><?= $bentukSoalText ?></td>
        </tr>
    </table>

    <!-- Tabel Kisi-Kisi dengan kolom CP dan TP yang lengkap -->
    <table class="bordered-table">
        <thead>
            <tr>
                <th width="4%">No.</th>
                <th width="12%">Kode CP / TP</th>
                <th width="22%">Capaian & Tujuan Pembelajaran</th>
                <th width="16%">Materi Pokok / Kajian</th>
                <th width="22%">Indikator Soal</th>
                <th width="9%">Level<br>Kognitif</th>
                <th width="9%">Bentuk<br>Soal</th>
                <th width="6%">No<br>Soal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
            <tr><td colspan="8" class="text-center">Belum ada item soal</td></tr>
            <?php else: ?>
                <?php foreach ($items as $idx => $it): 
                    $noItem = (int) ($it->nomor ?? ($idx + 1));
                    $jnsL = $jenisInfoLabels[(int) ($it->jenis_soal ?? 1)] ?? 'Lainnya';
                    $lvl = strtoupper($it->level_kognitif ?? $it->tujuan_level_kognitif ?? '-');
                    $lvlLabel = $levelKognitifLabel[strtoupper($it->level_kognitif ?? $it->tujuan_level_kognitif ?? '')] ?? $lvl;
                    $tujuanKode = $it->tujuan_kode ?? ($it->kode ?? '');
                    $tujuanTeks = $it->tujuan ?? $it->tujuan_teks ?? '-';
                    $cpKode = $it->cp ?? $it->tujuan_kode_cp ?? '';
                    $materi = $it->materi ?? $it->tujuan_materi_pokok ?? '-';
                    $indikator = $it->indikator_soal ?? $it->topik ?? '-';
                ?>
                <tr>
                    <td class="text-center"><?= $noItem ?></td>
                    <td>
                        <?php if ($cpKode): ?>
                            <strong>CP:</strong> <?= esc($cpKode) ?><br>
                        <?php endif; ?>
                        <?php if ($tujuanKode): ?>
                            <strong>TP:</strong> <?= esc($tujuanKode) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($cpKode): ?>
                            <div style="font-size:9pt; color:#555; margin-bottom:3px;"><em>CP: <?= esc($cpKode) ?></em></div>
                        <?php endif; ?>
                        <strong>TP:</strong> <?= esc($tujuanTeks) ?>
                    </td>
                    <td><?= esc($materi) ?></td>
                    <td><?= esc($indikator) ?></td>
                    <td class="text-center text-bold"><?= $lvl ?></td>
                    <td class="text-center"><?= $jnsL ?></td>
                    <td class="text-center"><?= $noItem ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature for Kisi-Kisi -->
    <div class="sign-area">
        <table class="sign-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah
                    <div class="sign-pad"></div>
                    <strong><?= $kepsek ?></strong><br>
                    <?= $kepsekStr ?>
                </td>
                <td>
                    <!-- Koordinator Kurikulum (center) -->
                    Koordinator Kurikulum<br>
                    &nbsp;
                    <div class="sign-pad"></div>
                    <strong>____________________</strong><br>
                    NIP. -
                </td>
                <td>
                    <br>
                    Guru Mata Pelajaran
                    <div class="sign-pad"></div>
                    <strong><?= $guruName ?></strong><br>
                    NIP. <?= $guruNip ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- PAGE BREAK FOR KARTU SOAL DETAILS -->
<?php if (!empty($items)): ?>
<div class="page-break"></div>

<!-- ============================================================ -->
<!-- KARTU SOAL SECTION                                           -->
<!-- ============================================================ -->
<div class="kartu-soal-section">
    <h3 style="margin-bottom:5px;">KARTU SOAL UJIAN</h3>
    <h4 style="font-size: 11pt; font-weight: normal; margin-bottom: 20px;">
        <?= esc($mapel->nama_mapel ?? '-') ?> — <?= esc($kelas->nama_kelas ?? '-') ?> — <?= esc($tp_row->tahun ?? '-') ?> / <?= esc($smt_row->nama_smt ?? '-') ?>
    </h4>

    <?php foreach ($items as $idx => $it): 
        $noItem = (int) ($it->nomor ?? ($idx + 1));
        $jnsL = $jenisInfoLabels[(int) ($it->jenis_soal ?? 1)] ?? 'Lainnya';
        $lvl = strtoupper($it->level_kognitif ?? $it->tujuan_level_kognitif ?? '-');
        $lvlLower = 'level-' . strtolower($lvl);
        $lvlLabel = $levelKognitifLabel[strtoupper($it->level_kognitif ?? $it->tujuan_level_kognitif ?? '')] ?? $lvl;
        $tujuanKode = $it->tujuan_kode ?? ($it->kode ?? '');
        $tujuanTeks = $it->tujuan ?? $it->tujuan_teks ?? '-';
        $cpKode = $it->cp ?? $it->tujuan_kode_cp ?? '';
        $materi = $it->materi ?? $it->tujuan_materi_pokok ?? '-';
        $indikator = $it->indikator_soal ?? $it->topik ?? '-';
        // Ranah kompetensi detail
        $tujuanObj = null;
        if (!empty($it->id_tujuan) && !empty($tujuanMap)) {
            $tujuanObj = $tujuanMap[$it->id_tujuan] ?? null;
        }
    ?>

    <div class="kartu-container">
        <!-- Kartu Header -->
        <div class="kartu-header">
            <table style="width:100%; font-size: 10pt;" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="14%"><strong>Mata Pelajaran</strong></td>
                    <td width="1%">:</td>
                    <td width="31%"><?= esc($mapel->nama_mapel ?? '-') ?></td>
                    
                    <td width="12%"><strong>Jenis Soal</strong></td>
                    <td width="1%">:</td>
                    <td width="41%"><?= $jnsL ?></td>
                </tr>
                <tr>
                    <td><strong>Kelas / Smt</strong></td>
                    <td>:</td>
                    <td><?= esc($kelas->nama_kelas ?? '-') ?> / <?= esc($smt_row->nama_smt ?? '-') ?></td>
                    
                    <td><strong>Penyusun</strong></td>
                    <td>:</td>
                    <td><?= $guruName ?></td>
                </tr>
                <tr>
                    <td><strong>Tahun Pelajaran</strong></td>
                    <td>:</td>
                    <td><?= esc($tp_row->tahun ?? '-') ?></td>

                    <td><strong>No. Soal</strong></td>
                    <td>:</td>
                    <td><strong><?= $noItem ?></strong></td>
                </tr>
            </table>
        </div>

        <table class="kartu-body">
            <tr>
                <!-- Left Info column -->
                <td class="kartu-left">

                    <!-- Kode CP & TP -->
                    <?php if ($cpKode || $tujuanKode): ?>
                    <div class="box-field">
                        <div class="box-title">Referensi CP & TP:</div>
                        <div class="box-content" style="font-size: 9pt;">
                            <?php if ($cpKode): ?>
                                <strong>CP:</strong> <?= esc($cpKode) ?><br>
                            <?php endif; ?>
                            <?php if ($tujuanKode): ?>
                                <strong>Kode TP:</strong> <?= esc($tujuanKode) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tujuan Pembelajaran — Ranah Pengetahuan -->
                    <div class="box-field">
                        <div class="box-title">
                            <span class="ranah-badge ranah-pengetahuan">Pengetahuan</span>
                            Tujuan Pembelajaran:
                        </div>
                        <div class="box-content" style="font-size: 9pt;"><?= esc($tujuanTeks) ?></div>
                    </div>

                    <!-- Ranah Sikap (jika ada) -->
                    <?php 
                    $tpSikap = ($tujuanObj && !empty($tujuanObj->tp_sikap)) ? $tujuanObj->tp_sikap : null;
                    if ($tpSikap): ?>
                    <div class="box-field">
                        <div class="box-title">
                            <span class="ranah-badge ranah-sikap">Sikap</span>
                            Tujuan Pembelajaran:
                        </div>
                        <div class="box-content" style="font-size: 9pt;"><?= esc($tpSikap) ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Ranah Keterampilan (jika ada) -->
                    <?php 
                    $tpKeterampilan = ($tujuanObj && !empty($tujuanObj->tp_keterampilan)) ? $tujuanObj->tp_keterampilan : null;
                    if ($tpKeterampilan): ?>
                    <div class="box-field">
                        <div class="box-title">
                            <span class="ranah-badge ranah-keterampilan">Keterampilan</span>
                            Tujuan Pembelajaran:
                        </div>
                        <div class="box-content" style="font-size: 9pt;"><?= esc($tpKeterampilan) ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Materi -->
                    <div class="box-field">
                        <div class="box-title">Materi / Bahan Kajian:</div>
                        <div class="box-content"><?= esc($materi) ?></div>
                    </div>

                    <!-- Indikator Soal -->
                    <div class="box-field">
                        <div class="box-title">Indikator Soal:</div>
                        <div class="box-content" style="font-size: 9pt;"><?= esc($indikator) ?></div>
                    </div>

                    <!-- Level Kognitif + Estimasi -->
                    <div class="box-field" style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span class="box-title">Level: </span>
                            <span class="level-badge <?= $lvlLower ?>"><?= $lvl ?></span>
                            <span style="font-size: 8pt; color: #666;"> — <?= $lvlLabel ?></span>
                        </div>
                        <?php if (!empty($it->estimasi_waktu)): ?>
                        <div style="font-size: 8pt; color: #666;">
                            ⏱ <?= (int)$it->estimasi_waktu ?> mnt
                        </div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Right Question Box -->
                <td class="kartu-right">
                    <div style="margin-bottom: 10px;">
                        <strong style="font-size: 11pt; text-decoration: underline;">Butir Soal No. <?= $noItem ?></strong>
                        <span style="font-size: 9pt; color: #666; margin-left: 10px;">[<?= $jnsL ?>]</span>
                    </div>
                    <div class="soal-content" style="min-height: 140px; border-top: 1px dashed #ccc; padding-top: 8px;">
                        <?php 
                            // Cari butir soal yang cocok dari bank soal
                            $matchedSoals = array_filter($bank_soals ?? [], function($s) use ($it) {
                                // 1. Prioritas utama: Kecocokan ID Soal jika sudah terhubung (linked)
                                if (!empty($it->soal_id) && (int)$s->id_soal === (int)$it->soal_id) return true;
                                
                                // 2. Fallback: Kecocokan Nomor Soal & Jenis (untuk soal yang diinput manual dengan nomor yang sama)
                                if ((int)$s->nomor_soal === (int)$it->nomor && (int)$s->jenis === (int)$it->jenis_soal) return true;

                                // 3. Fallback: Kecocokan Tujuan ID jika kolom tersebut ada di tabel cbt_soal
                                if (isset($s->tujuan_id) && (int)$s->tujuan_id > 0 && (int)$s->tujuan_id === (int)$it->id_tujuan && (int)$s->jenis === (int)$it->jenis_soal) return true;

                                return false;
                            });

                            if (!empty($matchedSoals)): 
                                // Kartu bisa memiliki banyak paket/bank; cetak hanya 1 butir
                                // (prioritas: kecocokan soal_id → bank utama dengan id_bank terkecil).
                                $soalData = reset($matchedSoals);
                        ?>
                                <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dotted #ddd;">
                                    <div style="margin-top: 5px; line-height: 1.6;">
                                        <?= $soalData->soal ?>
                                        
                                        <?php if ((int)$soalData->jenis === 1): ?>
                                            <div style="margin-top: 10px; margin-left: 10px;">
                                                <?php foreach (['a', 'b', 'c', 'd', 'e'] as $optChar): 
                                                    $optStr = $soalData->{"opsi_{$optChar}"} ?? '';
                                                    if (!empty(trim(strip_tags($optStr))) || strpos($optStr, '<img') !== false):
                                                ?>
                                                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px; border:none;">
                                                    <tr>
                                                        <td width="25px" style="vertical-align: top; border:none;"><strong><?= strtoupper($optChar) ?>.</strong></td>
                                                        <td style="vertical-align: top; border:none;"><?= $optStr ?></td>
                                                    </tr>
                                                </table>
                                                <?php endif; endforeach; ?>
                                            </div>
                                        <?php elseif ((int)$soalData->jenis === 2): ?>
                                            <div style="margin-top: 10px; margin-left: 10px;">
                                                <?php 
                                                    $opsiArr = json_decode($soalData->opsi_a ?? '[]', true); 
                                                    if (is_array($opsiArr)) {
                                                        foreach ($opsiArr as $k => $v) {
                                                            $lbl = ($k !== (string)(int)$k) ? strtoupper($k) . "." : "—";
                                                            echo "<table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom: 3px; border:none;'>";
                                                            echo "<tr><td width='25px' style='vertical-align: top; border:none;'><strong>{$lbl}</strong></td><td style='vertical-align: top; border:none;'>{$v}</td></tr>";
                                                            echo "</table>";
                                                        }
                                                    }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php 
                                        $tipeSoal = (int)$soalData->jenis;
                                        if ($tipeSoal == 1 || $tipeSoal == 4) {
                                            echo "<div style='margin-top: 12px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci Jawaban:</strong> " . esc(strtoupper($soalData->jawaban)) . "</div>";
                                        } elseif ($tipeSoal == 5) {
                                            echo "<div style='margin-top: 12px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci Jawaban:</strong> " . $soalData->jawaban . "</div>";
                                        } elseif ($tipeSoal == 2) {
                                            $jwbnJson = @json_decode($soalData->jawaban, true) ?: [];
                                            $jwbnStrs = array_map('strtoupper', (array)$jwbnJson);
                                            echo "<div style='margin-top: 12px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci Jawaban:</strong> " . esc(implode(', ', $jwbnStrs)) . "</div>";
                                        } elseif ($tipeSoal == 3) {
                                            echo "<div style='margin-top: 12px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci:</strong> Pasangan (Terlampir pada format)</div>";
                                        } else {
                                            echo "<div style='margin-top: 12px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci:</strong> <em>Tersimpan dalam sistem</em></div>";
                                        }
                                    ?>
                                </div>
                        <?php 
                            elseif (!empty($it->butir_soal)): 
                        ?>
                                <div style="margin-bottom: 10px; padding-bottom: 10px;">
                                    <div style="margin-top: 5px; line-height: 1.6;">
                                        <?= $it->butir_soal ?>
                                    </div>
                                    <?php 
                                        $tipeSoal = (int)$it->jenis_soal;
                                        $jawaban = $it->jawaban_benar_json ?? '';
                                        if (is_string($jawaban)) {
                                            $jawaban = json_decode($jawaban, true);
                                        }
                                        
                                        if ($tipeSoal == 1 || $tipeSoal == 4) {
                                            $txt = is_array($jawaban) ? ($jawaban['raw'] ?? $jawaban['pilihan'] ?? '') : $jawaban;
                                            echo "<div style='margin-top: 8px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci Jawaban:</strong> " . esc($txt) . "</div>";
                                        } elseif ($tipeSoal == 5) {
                                            $txt = is_array($jawaban) ? ($jawaban['raw'] ?? '') : $jawaban;
                                            echo "<div style='margin-top: 8px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci Jawaban:</strong> " . $txt . "</div>";
                                        } elseif ($tipeSoal == 2) {
                                            $txt = is_array($jawaban) ? implode(', ', $jawaban) : $jawaban;
                                            echo "<div style='margin-top: 8px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci Jawaban:</strong> " . esc($txt) . "</div>";
                                        } else {
                                            echo "<div style='margin-top: 8px; padding: 4px 8px; background: #f0f9ff; border-left: 3px solid #0ea5e9;'><strong>Kunci:</strong> <em>Tersimpan dalam sistem</em></div>";
                                        }
                                    ?>
                                </div>
                        <?php
                            else: 
                        ?>
                            <div style="padding: 20px; text-align: center; color: #999; border: 1px dashed #ddd; border-radius: 4px; margin-top: 10px;">
                                <p style="font-style: italic; margin: 0;">[ Ruang Butir Soal ]</p>
                                <p style="font-size: 9pt; margin: 5px 0 0 0;"><em>Butir soal belum tersedia</em></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pembahasan / Catatan -->
                    <?php if (!empty($it->pembahasan) && trim(strip_tags($it->pembahasan)) !== ''): ?>
                    <div style="margin-top: 10px; padding: 6px 8px; background: #fefce8; border: 1px solid #fde68a; border-radius: 4px; font-size: 9pt;">
                        <strong>Pembahasan:</strong><br>
                        <?= $it->pembahasan ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($it->catatan)): ?>
                    <div style="margin-top: 8px; font-size: 8pt; color: #888;">
                        <em>Catatan: <?= esc($it->catatan) ?></em>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

</body>
</html>
