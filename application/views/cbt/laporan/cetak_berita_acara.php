<!DOCTYPE html>
<html>

<head>
    <title>Berita Acara - <?= isset($content->mapel_nama) ? $content->mapel_nama : 'Ujian' ?> -
        <?= isset($content->ruang_nama) ? $content->ruang_nama : '' ?> -
        <?= isset($content->sesi_nama) ? $content->sesi_nama : '' ?> - <?= date('d-m-Y') ?>
    </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            width: 21cm;
            min-height: 29.7cm;
            margin: 15px auto;
            padding: 1.5cm 2cm;
            background: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }

        html {
            background: #525659;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #1565C0;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }

        .print-btn:hover {
            background: #0D47A1;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            html,
            body {
                background: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
                width: auto;
                min-height: auto;
            }

            @page {
                size: A4;
                margin: 0.5cm 1cm 1cm 1.5cm;
            }
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
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Print Button */
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #1565C0;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-print:hover {
            background: #0D47A1;
        }

        @media print {
            .btn-print {
                display: none !important;
            }

            body {
                padding: 20px 40px;
            }

            .sig-box {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* Footer Fixed */
        .footer-fixed {
            font-size: 9pt;
            font-style: italic;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            display: flex;
            justify-content: space-between;
        }

        @media screen {
            body {
                position: relative;
            }

            .footer-fixed {
                position: absolute;
                bottom: 1.5cm;
                left: 2cm;
                right: 2cm;
            }
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
</head>

<body>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak</button>
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
                                <?= isset($setting->header_1) ? strtoupper($setting->header_1) : 'PEMERINTAH DAERAH' ?>
                            </div>
                            <div class="kop-line2">
                                <?= isset($setting->header_2) ? strtoupper($setting->header_2) : 'DINAS PENDIDIKAN DAN KEBUDAYAAN' ?>
                            </div>
                            <div class="kop-line3">
                                <?= isset($setting->sekolah) ? strtoupper($setting->sekolah) : 'NAMA SEKOLAH' ?>
                            </div>
                            <div class="kop-line4">
                                <?= isset($setting->header_3) ? $setting->header_3 : (isset($setting->alamat) ? 'Alamat: ' . $setting->alamat : '') ?>
                            </div>
                            <div class="kop-line5"><?= isset($setting->header_4) ? $setting->header_4 : '' ?></div>
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
        Pada hari ini <b><?= $hari ?></b> tanggal <b><?= $tanggal ?></b> bulan <b><?= $bulan ?></b> tahun
        <b><?= $tahun ?></b> telah diselenggarakan
        <b><?= isset($jenis_ujian->nama_jenis) ? $jenis_ujian->nama_jenis : '........................................' ?></b>
        untuk Mata Pelajaran
        <b><?= isset($content->mapel_nama) ? $content->mapel_nama : '........................................' ?></b>
        dari pukul <b><?= $jam_mulai_val ?></b> sampai dengan pukul <b><?= $jam_selesai_val ?></b>
    </p>

    <!-- Point 1: Info Table -->
    <table class="content-table">
        <tr>
            <td class="num-col">1.</td>
            <td class="label-col">Pada Sekolah</td>
            <td class="colon-col">:</td>
            <td><?= isset($setting->sekolah) ? strtoupper($setting->sekolah) : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Ruang</td>
            <td class="colon-col">:</td>
            <td><?= isset($content->ruang_nama) && $content->ruang_nama ? $content->ruang_nama : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Sesi</td>
            <td class="colon-col">:</td>
            <td><?= isset($content->sesi_nama) && $content->sesi_nama ? $content->sesi_nama : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Jumlah Peserta Seharusnya</td>
            <td class="colon-col">:</td>
            <td><?= isset($content->jml_seharusnya) && $content->jml_seharusnya ? $content->jml_seharusnya . ' orang' : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Jumlah Peserta Hadir</td>
            <td class="colon-col">:</td>
            <td><?= isset($content->jml_hadir) && $content->jml_hadir ? $content->jml_hadir . ' orang' : '.................................................................' ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>Jumlah Peserta Tidak Hadir</td>
            <td class="colon-col">:</td>
            <td><?= isset($content->jml_tidak_hadir) && $content->jml_tidak_hadir ? $content->jml_tidak_hadir . ' orang' : '.................................................................' ?>
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
                        $no_peserta_arr[] = $s->no_peserta . ' (' . $s->nama . ')';
                    }
                    echo implode(', ', $no_peserta_arr);
                    ?>
                <?php else: ?>
                    .................................................................
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Point 2: Catatan -->
    <table class="content-table">
        <tr>
            <td class="num-col">2.</td>
            <td colspan="3">Catatan selama
                <b><?= isset($jenis_ujian->nama_jenis) ? $jenis_ujian->nama_jenis : '.......' ?></b>
                berlangsung :
            </td>
        </tr>
    </table>
    <div class="catatan-box">
        <?php if (isset($content->uraian) && $content->uraian): ?>
            <?= nl2br(htmlspecialchars($content->uraian)) ?><br>
        <?php endif; ?>

        <?php if (isset($content->kendala) && $content->kendala): ?>
            <?= nl2br(htmlspecialchars($content->kendala)) ?><br>
        <?php endif; ?>

        <?php if (isset($content->solusi) && $content->solusi): ?>
            <strong>Tindak Lanjut : </strong>
            <?= nl2br(htmlspecialchars($content->solusi)) ?>
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
                                    echo $uploader->nama_lengkap;
                                } elseif (isset($uploader->nama_guru) && $uploader->nama_guru) {
                                    echo $uploader->nama_guru;
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
                            <td><?= isset($uploader->nip) && $uploader->nip ? $uploader->nip : '_________________________' ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="sig-right">
                    <div>1.</div>
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
                            <td><?= isset($setting->proktor) && $setting->proktor ? $setting->proktor : '_________________________' ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>NIP.</td>
                            <td class="sig-colon">:</td>
                            <td><?= isset($setting->nip_proktor) && $setting->nip_proktor ? $setting->nip_proktor : '_________________________' ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="sig-right">
                    <div>2.</div>
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
                            <td><?= isset($setting->kepsek) && $setting->kepsek ? $setting->kepsek : '_________________________' ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>NIP.</td>
                            <td class="sig-colon">:</td>
                            <td><?= isset($setting->nip) && $setting->nip ? $setting->nip : '_________________________' ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="sig-right">
                    <div>3.</div>
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
        <div>Dokumen ini ditandatangani secara digital</div>
        <div>dicetak pada : <?= $tgl_cetak ?></div>
    </div>

</body>

</html>