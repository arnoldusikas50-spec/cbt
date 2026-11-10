<!DOCTYPE html>
<html>

<head>
    <title>Berita Acara -
        <?= isset($content->mapel_nama) ? esc($content->mapel_nama) : 'Ujian' ?> -
        <?= isset($content->ruang_nama) ? esc($content->ruang_nama) : '' ?> -
        <?= isset($content->sesi_nama) ? esc($content->sesi_nama) : '' ?> -
        <?= date('d-m-Y') ?>
    </title>
    <?php if (isset($is_pdf) && $is_pdf): ?>
        <style>
            /* CSS for Dompdf */
            @page {
                margin: 1cm 2cm 3.5cm 2cm;
            }

            body {
                font-family: 'Segoe UI', system-ui, sans-serif;
                font-size: 10pt;
                line-height: 1.3;
                color: #000;
                margin: 0;
                padding: 0;
            }

            .kop-surat {
                border-bottom: 3px double #000;
                padding-bottom: 5px;
                margin-bottom: 10px;
            }

            .kop-table {
                width: 100%;
                border-collapse: collapse;
            }

            .kop-table td {
                vertical-align: middle;
            }

            .kop-logo img {
                width: 65px;
                height: 65px;
            }

            .kop-text {
                text-align: center;
            }

            .kop-line1 {
                font-size: 11pt;
                font-weight: bold;
            }

            .kop-line2 {
                font-size: 10pt;
            }

            .kop-line3 {
                font-size: 14pt;
                font-weight: bold;
            }

            .kop-line4 {
                font-size: 9pt;
                font-style: italic;
            }

            .kop-line5 {
                font-size: 8pt;
            }

            .title {
                text-align: center;
                font-size: 13pt;
                font-weight: bold;
                margin: 10px 0 10px;
                text-decoration: underline;
            }

            .paragraph {
                text-align: justify;
                margin-bottom: 5px;
                text-indent: 30px;
            }

            .content-table {
                width: 100%;
                margin-bottom: 5px;
            }

            .content-table td {
                padding: 1px 0;
                vertical-align: top;
            }

            .num-col {
                width: 25px;
            }

            .label-col {
                width: 180px;
            }

            .colon-col {
                width: 15px;
                text-align: center;
            }

            .catatan-box {
                border: 1px solid #000;
                padding: 5px 10px;
                min-height: 60px;
                margin: 5px 0 10px 25px;
            }

            .absen-table {
                width: 100%;
                border-collapse: collapse;
                margin: 4px 0 10px 25px;
                font-size: 9.5pt;
            }

            .absen-table th,
            .absen-table td {
                border: 1px solid #000;
                padding: 3px 5px;
            }

            .absen-table th {
                background-color: #f0f0f0;
                text-align: center;
            }



            .signature-section {
                margin-top: 10px;
            }

            .signature-table {
                width: 100%;
                border-collapse: collapse;
            }

            .signature-table td {
                padding: 2px 0;
                vertical-align: top;
            }

            .sig-left {
                width: 55%;
            }

            .sig-right {
                width: 45%;
                text-align: center;
            }

            .sig-header {
                font-weight: bold;
                text-align: center;
            }

            .sig-num {
                width: 20px;
            }

            .sig-label {
                width: 90px;
            }

            .sig-colon {
                width: 10px;
            }

            .sig-box {
                height: 40px;
                width: 150px;
                margin: 0 auto;
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                border-bottom: 1px solid #000;
            }

            .footer-fixed {
                position: fixed;
                bottom: -20px;
                left: 0px;
                right: 0px;
                height: 40px;
                font-size: 9pt;
                font-style: italic;
                border-top: 1px solid #ccc;
                padding-top: 5px;
            }

            .footer-table {
                width: 100%;
            }

            .no-print,
            .action-btn {
                display: none;
            }

            * {
                box-sizing: border-box;
            }
        </style>
    <?php else: ?>
        <style>
            /* CSS for Browser Preview */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
                font-size: 12pt;
                line-height: 1.4;
                color: #000;
            }

            @media screen {
                html {
                    background: #525659;
                }

                body {
                    width: 21cm;
                    min-height: 29.7cm;
                    margin: 15px auto;
                    padding: 1.5cm 2cm;
                    background: #fff;
                    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
                }

                .footer-fixed {
                    position: absolute;
                    bottom: 1.5cm;
                    left: 2cm;
                    right: 2cm;
                    display: none;
                    /* Hide footer in preview */
                }
            }

            .action-btn {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 10px 20px;
                background: #1565C0;
                color: white !important;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                text-decoration: none;
                font-family: sans-serif;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                z-index: 9999;
                display: inline-block;
            }

            .action-btn:hover {
                background: #0D47A1;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                html,
                body {
                    width: 100% !important;
                    height: auto !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: none;
                    box-shadow: none;
                    font-size: 10pt;
                    line-height: 1.3;
                }

                @page {
                    size: A4;
                    margin: 1.5cm 2cm;
                }
            }

            /* Styling elements shared for preview (extracted for brevity in else block) follows existing classes */
            .kop-surat {
                text-align: center;
                border-bottom: 3px double #000;
                padding-bottom: 8px;
                margin-bottom: 15px;
            }

            .kop-table {
                width: 100%;
                border-collapse: collapse;
            }

            .kop-table td {
                vertical-align: middle;
            }

            .kop-logo {
                width: 80px;
            }

            .kop-logo img {
                width: 70px;
                height: 70px;
            }

            .kop-text {
                text-align: center;
            }

            .kop-line1 {
                font-size: 11pt;
                font-weight: bold;
            }

            .kop-line2 {
                font-size: 10pt;
            }

            .kop-line3 {
                font-size: 14pt;
                font-weight: bold;
            }

            .kop-line4 {
                font-size: 9pt;
                font-style: italic;
            }

            .kop-line5 {
                font-size: 8pt;
            }

            .title {
                text-align: center;
                font-size: 14pt;
                font-weight: bold;
                margin: 10px 0 10px;
                text-decoration: underline;
            }

            .paragraph {
                text-align: justify;
                margin-bottom: 5px;
                text-indent: 40px;
            }

            .content-table {
                width: 100%;
                margin-bottom: 5px;
            }

            .content-table td {
                padding: 2px 0;
                vertical-align: top;
            }

            .num-col {
                width: 25px;
            }

            .label-col {
                width: 200px;
            }

            .colon-col {
                width: 15px;
                text-align: center;
            }

            .catatan-box {
                border: 1px solid #000;
                padding: 10px 15px;
                min-height: 80px;
                margin: 8px 0 15px 25px;
            }

            .signature-section {
                margin-top: 15px;
            }

            .signature-table {
                width: 100%;
                border-collapse: collapse;
            }

            .signature-table td {
                padding: 5px 0;
                vertical-align: top;
            }

            .sig-left {
                width: 55%;
            }

            .sig-right {
                width: 45%;
                text-align: center;
            }

            .sig-header {
                font-weight: bold;
                text-align: center;
            }

            .sig-num {
                width: 25px;
            }

            .sig-label {
                width: 100px;
            }

            .sig-colon {
                width: 15px;
            }

            .footer-fixed {
                font-size: 9pt;
                font-style: italic;
                border-top: 1px solid #ccc;
                padding-top: 5px;
                width: 100%;
            }

            .footer-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
            }

            .footer-table td {
                border: none;
                padding: 0;
                vertical-align: top;
            }


            /* Header / Kop Surat */
            .kop-surat {
                text-align: center;
                border-bottom: 3px double #000;
                padding-bottom: 8px;
                margin-bottom: 15px;
            }

            .kop-table {
                width: 100%;
                border-collapse: collapse;
            }

            .kop-table td {
                vertical-align: middle;
            }

            .kop-logo {
                width: 80px;
            }

            .kop-logo img {
                width: 70px;
                height: 70px;
            }

            .kop-text {
                text-align: center;
            }

            .kop-line1 {
                font-size: 11pt;
                font-weight: bold;
            }

            .kop-line2 {
                font-size: 10pt;
            }

            .kop-line3 {
                font-size: 14pt;
                font-weight: bold;
            }

            .kop-line4 {
                font-size: 9pt;
                font-style: italic;
            }

            .kop-line5 {
                font-size: 8pt;
            }

            /* Title */
            .title {
                text-align: center;
                font-size: 14pt;
                font-weight: bold;
                margin: 10px 0 10px;
                text-decoration: underline;
            }

            /* Paragraph */
            .paragraph {
                text-align: justify;
                margin-bottom: 5px;
                text-indent: 40px;
            }

            /* Main Content Table */
            .content-table {
                width: 100%;
                margin-bottom: 5px;
            }

            .content-table td {
                padding: 2px 0;
                vertical-align: top;
            }

            .num-col {
                width: 25px;
            }

            .label-col {
                width: 200px;
            }

            .colon-col {
                width: 15px;
                text-align: center;
            }

            .dots {
                letter-spacing: 1px;
            }

            /* Catatan Box */
            .catatan-box {
                border: 1px solid #000;
                padding: 10px 15px;
                min-height: 80px;
                margin: 8px 0 15px 25px;
            }

            /* Signature Section */
            .signature-section {
                margin-top: 15px;
            }

            .signature-table {
                width: 100%;
                border-collapse: collapse;
            }

            .signature-table td {
                padding: 5px 0;
                vertical-align: top;
            }

            .sig-left {
                width: 55%;
            }

            .sig-right {
                width: 45%;
                text-align: center;
            }

            .sig-header {
                font-weight: bold;
                text-align: center;
            }

            .sig-num {
                width: 25px;
            }

            .sig-label {
                width: 100px;
            }

            .sig-colon {
                width: 15px;
            }

            .sig-box {
                height: 40px;
                width: 150px;
                margin: 0 auto;
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
                border-bottom: 1px solid #000;
            }



            @media print {

                .no-print,
                .action-btn {
                    display: none !important;
                }

                body {
                    padding: 0;
                }
            }

            /* Footer Fixed - table based */
            .footer-fixed {
                font-size: 9pt;
                font-style: italic;
                border-top: 1px solid #ccc;
                padding-top: 5px;
                width: 100%;
            }

            .footer-table {
                width: 100%;
                border-collapse: collapse;
                border: none;
            }

            .footer-table td {
                border: none;
                padding: 0;
                vertical-align: top;
            }



            @media print {
                .footer-fixed {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: white;
                }
            }
        </style>
    <?php endif; ?>
</head>

<body>
    <?php if (!isset($is_pdf)): ?>
        <a href="<?= current_url() . '?type=pdf' ?>" class="action-btn">
            Download PDF
        </a>
    <?php endif; ?>
    <?php
    // Helper variables
    $hari_arr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan_arr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $waktu = isset($content->waktu_ujian) ? strtotime($content->waktu_ujian) : time();
    $hari = $hari_arr[date('w', $waktu)];
    $tanggal = date('d', $waktu);
    $bulan = $bulan_arr[date('n', $waktu) - 1];
    $tahun = date('Y', $waktu);
    $tgl_cetak = date('d') . ' ' . $bulan_arr[date('n') - 1] . ' ' . date('Y H:i');

    $jam_mulai_val = isset($content->jam_mulai) && $content->jam_mulai ? $content->jam_mulai : date('H:i', $waktu);
    $jam_selesai_val = isset($content->jam_selesai) && $content->jam_selesai ? $content->jam_selesai : '..........';
    ?>

    <!-- Kop Surat -->
    <?php if (isset($setting) && $setting): ?>
        <?php if (isset($setting->kopsekolah) && $setting->kopsekolah != ''): ?>
            <!-- Kop Surat dari Gambar -->
            <div style="margin-bottom: 15px;">
                <img src="<?= base_url($setting->kopsekolah) ?>" style="width: 100%;" alt="Kop Surat">
            </div>
        <?php else: ?>
            <!-- Kop Surat dari Text -->
            <div class="kop-surat">
                <table class="kop-table">
                    <tr>
                        <td class="kop-logo">
                            <?php if (isset($setting->logo_kiri) && $setting->logo_kiri): ?>
                                <img src="<?= base_url($setting->logo_kiri) ?>" alt="Logo">
                            <?php endif; ?>
                        </td>
                        <td class="kop-text">
                            <div class="kop-line1">
                                <?= isset($setting->header_1) ? strtoupper(esc($setting->header_1)) : 'PEMERINTAH DAERAH' ?>
                            </div>
                            <div class="kop-line2">
                                <?= isset($setting->header_2) ? strtoupper(esc($setting->header_2)) : 'DINAS PENDIDIKAN DAN KEBUDAYAAN' ?>
                            </div>
                            <div class="kop-line3">
                                <?= isset($setting->sekolah) ? strtoupper(esc($setting->sekolah)) : 'NAMA SEKOLAH' ?>
                            </div>
                            <div class="kop-line4">
                                <?= isset($setting->header_3) ? esc($setting->header_3) : (isset($setting->alamat) ? 'Alamat: ' . esc($setting->alamat) : '') ?>
                            </div>
                            <div class="kop-line5">
                                <?= isset($setting->header_4) ? esc($setting->header_4) : '' ?>
                            </div>
                        </td>
                        <td class="kop-logo" style="text-align: right;">
                            <?php if (isset($setting->logo_kanan) && $setting->logo_kanan): ?>
                                <img src="<?= base_url($setting->logo_kanan) ?>" alt="Logo">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Title -->
    <div class="title">BERITA ACARA</div>

    <!-- Opening Paragraph -->
    <p class="paragraph">
        Pada hari ini <b>
            <?= $hari ?>
        </b> tanggal <b>
            <?= $tanggal ?>
        </b> bulan <b>
            <?= $bulan ?>
        </b> tahun
        <b>
            <?= $tahun ?>
        </b> telah diselenggarakan
        <b>
            <?= isset($jenis_ujian->nama_jenis) ? esc($jenis_ujian->nama_jenis) : '........................................' ?>
        </b>
        untuk Mata Pelajaran
        <b>
            <?= isset($content->mapel_nama) ? esc($content->mapel_nama) : '........................................' ?>
        </b>
        dari pukul <b>
            <?= $jam_mulai_val ?>
        </b> sampai dengan pukul <b>
            <?= $jam_selesai_val ?>
        </b>
    </p>

    <!-- Point 1: Info Table -->
    <table class="content-table">
        <tr>
            <td class="num-col">1.</td>
            <td class="label-col">Pada Sekolah</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($setting->sekolah) ? strtoupper(esc($setting->sekolah)) : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Ruang</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->ruang_nama) && $content->ruang_nama ? esc($content->ruang_nama) : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Sesi</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->sesi_nama) && $content->sesi_nama ? esc($content->sesi_nama) : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Kelas Peserta</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->kelas_terlibat) && is_array($content->kelas_terlibat) && count($content->kelas_terlibat) > 0
                    ? esc(implode(', ', $content->kelas_terlibat))
                    : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Durasi Ujian</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->durasi_ujian) && (int) $content->durasi_ujian > 0
                    ? (int) $content->durasi_ujian . ' menit'
                    : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Jam Ke</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->jam_ke) && (int) $content->jam_ke > 0
                    ? 'Jam ke-' . (int) $content->jam_ke
                    : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Jumlah Peserta Seharusnya</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->jml_seharusnya) && $content->jml_seharusnya ? esc($content->jml_seharusnya) . ' orang' : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Jumlah Peserta Hadir</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->jml_hadir) && $content->jml_hadir ? esc($content->jml_hadir) . ' orang' : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Jumlah Peserta Tidak Hadir</td>
            <td class="colon-col">:</td>
            <td>
                <?= isset($content->jml_tidak_hadir) && $content->jml_tidak_hadir ? esc($content->jml_tidak_hadir) . ' orang' : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Nomor Peserta Tidak Hadir</td>
            <td class="colon-col">:</td>
            <td>
                <?php if (isset($content->siswa_tidak_hadir) && is_array($content->siswa_tidak_hadir) && count($content->siswa_tidak_hadir) > 0): ?>
                    <?php
                    $no_peserta_arr = [];
                    foreach ($content->siswa_tidak_hadir as $s) {
                        $no_peserta_arr[] = esc($s->no_peserta ?? '-') . ' (' . esc($s->nama ?? '-') . ')';
                    }
                    echo implode(', ', $no_peserta_arr);
                    ?>
                <?php else: ?>
                    .................................................................
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Rincian Ketidakhadiran</td>
            <td class="colon-col">:</td>
            <td>
                <?php
                $ringkas = $content->alasan_summary ?? null;
                if (is_array($ringkas)):
                    $parts = [];
                    if (($ringkas['Sakit'] ?? 0) > 0) {
                        $parts[] = 'Sakit: ' . (int) $ringkas['Sakit'] . ' orang';
                    }
                    if (($ringkas['Ijin'] ?? 0) > 0) {
                        $parts[] = 'Ijin: ' . (int) $ringkas['Ijin'] . ' orang';
                    }
                    if (($ringkas['Tanpa Berita'] ?? 0) > 0) {
                        $parts[] = 'Tanpa Berita: ' . (int) $ringkas['Tanpa Berita'] . ' orang';
                    }
                    if (($ringkas['Lainnya'] ?? 0) > 0) {
                        $parts[] = 'Lainnya: ' . (int) $ringkas['Lainnya'] . ' orang';
                    }
                    echo $parts ? esc(implode(' | ', $parts)) : '.................................................................';
                else:
                    echo '.................................................................';
                endif; ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Pengawas Ujian</td>
            <td class="colon-col">:</td>
            <td>
                <?php if (isset($content->pengawas) && is_array($content->pengawas) && count($content->pengawas) > 0): ?>
                    <?php
                    $nama_pengawas = [];
                    foreach ($content->pengawas as $pg) {
                        $nama_pengawas[] = esc($pg->nama_guru ?? '-');
                    }
                    echo implode(', ', $nama_pengawas);
                    ?>
                <?php else: ?>
                    ..................................................................
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Tabel rincian siswa tidak hadir -->
    <?php if (isset($content->siswa_tidak_hadir) && is_array($content->siswa_tidak_hadir) && count($content->siswa_tidak_hadir) > 0): ?>
        <table class="absen-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Nama Siswa</th>
                    <th style="width: 110px;">Nomor Peserta</th>
                    <th style="width: 110px;">Kelas</th>
                    <th style="width: 120px;">Alasan</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no_absen = 1; foreach ($content->siswa_tidak_hadir as $s): ?>
                <tr>
                    <td style="text-align: center;"><?= $no_absen++ ?></td>
                    <td><?= esc($s->nama ?? '-') ?></td>
                    <td style="text-align: center;"><?= esc($s->no_peserta ?? '-') ?></td>
                    <td style="text-align: center;"><?= esc($s->kelas ?? '-') ?></td>
                    <td style="text-align: center;"><?= esc($s->alasan ?? '-') ?></td>
                    <td><?= esc($s->keterangan ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Point 2: Catatan -->
    <table class="content-table">
        <tr>
            <td class="num-col">2.</td>
            <td colspan="3">Catatan selama
                <b>
                    <?= isset($jenis_ujian->nama_jenis) ? esc($jenis_ujian->nama_jenis) : '.......' ?>
                </b>
                berlangsung :
            </td>
        </tr>
    </table>
    <div class="catatan-box">
        <?php if (isset($content->uraian) && $content->uraian): ?>
            <?= nl2br(esc($content->uraian)) ?><br>
        <?php endif; ?>

        <?php if (isset($content->kendala) && $content->kendala): ?>
            <?= nl2br(esc($content->kendala)) ?><br>
        <?php endif; ?>

        <?php if (isset($content->solusi) && $content->solusi): ?>
            <strong>Tindak Lanjut : </strong>
            <?= nl2br(esc($content->solusi)) ?>
        <?php endif; ?>

        <?php if (
            (!isset($content->uraian) || !$content->uraian) &&
            (!isset($content->kendala) || !$content->kendala) &&
            (!isset($content->solusi) || !$content->solusi)
        ): ?>
            Pelaksanaan ujian berjalan dengan aman, lancar dan tertib.
        <?php endif; ?>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td class="sig-left"></td>
                <td class="sig-right sig-header">TTD</td>
            </tr>
            <!-- Pengawas -->
            <tr>
                <td class="sig-left">
                    <table style="width: 100%;">
                        <tr>
                            <td class="sig-num">1.</td>
                            <td class="sig-label">Pengawas</td>
                            <td class="sig-colon">:</td>
                            <td>
                                <?php
                                if (isset($uploader->nama_lengkap) && $uploader->nama_lengkap) {
                                    echo esc($uploader->nama_lengkap);
                                } elseif (isset($uploader->nama_guru) && $uploader->nama_guru) {
                                    echo esc($uploader->nama_guru);
                                } elseif (isset($uploader->nama_panitia) && $uploader->nama_panitia) {
                                    echo esc($uploader->nama_panitia);
                                } elseif (isset($uploader->first_name) && $uploader->first_name) {
                                    echo esc($uploader->first_name) . ' ' . esc($uploader->last_name ?? '');
                                } else {
                                    echo '_________________________';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>NIP.</td>
                            <td class="sig-colon">:</td>
                            <td>
                                <?= isset($uploader->nip) && $uploader->nip ? esc($uploader->nip) : '_________________________' ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="sig-right">
                    <div></div>
                    <?php if (isset($uploader->ttd) && $uploader->ttd): ?>
                        <div class="sig-box" style="background-image: url('<?= base_url($uploader->ttd) ?>');"></div>
                    <?php else: ?>
                        <div class="sig-box"></div>
                    <?php endif; ?>
                </td>
            </tr>
            <!-- Proktor -->
            <tr>
                <td class="sig-left">
                    <table style="width: 100%;">
                        <tr>
                            <td class="sig-num">2.</td>
                            <td class="sig-label">Proktor</td>
                            <td class="sig-colon">:</td>
                            <td>
                                <?= isset($setting->proktor) && $setting->proktor ? esc($setting->proktor) : '_________________________' ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>NIP.</td>
                            <td class="sig-colon">:</td>
                            <td>
                                <?= isset($setting->nip_proktor) && $setting->nip_proktor ? esc($setting->nip_proktor) : '_________________________' ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="sig-right">
                    <div></div>
                    <?php if (isset($setting->ttdproktor) && $setting->ttdproktor): ?>
                        <div class="sig-box" style="background-image: url('<?= base_url($setting->ttdproktor) ?>');"></div>
                    <?php else: ?>
                        <div class="sig-box"></div>
                    <?php endif; ?>
                </td>
            </tr>
            <!-- Kepala Sekolah -->
            <tr>
                <td class="sig-left">
                    <table style="width: 100%;">
                        <tr>
                            <td class="sig-num">3.</td>
                            <td class="sig-label">Kepala Sekolah</td>
                            <td class="sig-colon">:</td>
                            <td>
                                <?= isset($setting->kepsek) && $setting->kepsek ? esc($setting->kepsek) : '_________________________' ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>NIP.</td>
                            <td class="sig-colon">:</td>
                            <td>
                                <?= isset($setting->nip) && $setting->nip ? esc($setting->nip) : '_________________________' ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="sig-right">
                    <div></div>
                    <?php if (isset($setting->tanda_tangan) && $setting->tanda_tangan): ?>
                        <div class="sig-box" style="background-image: url('<?= base_url($setting->tanda_tangan) ?>');">
                        </div>
                    <?php else: ?>
                        <div class="sig-box"></div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-fixed">
        <table class="footer-table">
            <tr>
                <td align="left">Dokumen ini ditandatangani secara digital</td>
                <td align="right">dicetak pada : <?= $tgl_cetak ?></td>
            </tr>
        </table>
    </div>

</body>

</html>