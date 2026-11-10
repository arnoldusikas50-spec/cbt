<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian - <?= $tanggal ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        .kop-surat img {
            max-width: 100%;
            height: auto;
        }
        .kop-text h2 {
            font-size: 14pt;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .kop-text p {
            margin: 0;
            font-size: 10pt;
        }
        .judul {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .section {
            margin-bottom: 8px;
        }
        .section.signature-section {
            page-break-inside: avoid;
            page-break-before: auto;
            margin-top: 10px;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-size: 10.5pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 6px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .indent-table {
            margin-left: 15px;
        }
        .indent-table td {
            border: none;
            padding: 3px 8px;
        }
        .signature-section {
            margin-top: 30px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border: none;
            table-layout: fixed; /* Ensures columns are equal width */
            margin-top: 10px;
        }
        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 5px;
            overflow: hidden; /* Prevents content from pushing width */
        }
        .signature-line {
            border-bottom: 1.5px solid #000;
            display: inline-block;
            width: 85%; /* Use percentage of cell width for safety */
            min-width: 160px;
            max-width: 200px;
            margin-bottom: 5px;
            font-weight: bold;
            padding-bottom: 2px;
            white-space: nowrap;
        }
        .signature-space {
            height: 70px; /* Adjusted slightly for better fit */
        }
        .footer-signature {
            text-align: center;
            margin-top: 25px;
        }
        .footer-signature .signature-line {
            width: 40%;
            min-width: 220px;
            max-width: 300px;
        }
        .no-border td, .no-border table {
            border: none;
        }
    </style>
</head>
<body>
    <!-- Kop Surat -->
    <?php if (!empty($setting->kopsekolah)): ?>
        <div class="kop-surat">
            <img src="<?= base_url($setting->kopsekolah) ?>" alt="Kop Surat">
        </div>
    <?php else: ?>
        <div class="kop-surat kop-text">
            <h2><?= $setting->sekolah ?? 'NAMA SEKOLAH' ?></h2>
            <p><?= ($setting->alamat ?? '') . ', ' . ($setting->kecamatan ?? '') . ', ' . ($setting->kota ?? '') ?></p>
        </div>
    <?php endif; ?>

    <div class="judul">Laporan Pelaksanaan Ujian</div>

    <!-- Section A: Identitas -->
    <div class="section">
        <div class="section-title">A. Identitas Kegiatan</div>
        <table class="indent-table no-border">
            <tr>
                <td width="200">Nama Sekolah</td>
                <td width="20">:</td>
                <td><strong><?= $setting->sekolah ?? '-' ?></strong></td>
            </tr>
            <tr>
                <td>Hari/Tanggal</td>
                <td>:</td>
                <td><?= $tanggal_format ?></td>
            </tr>
            <tr>
                <td>Waktu Pelaksanaan</td>
                <td>:</td>
                <td><?= ($ringkasan['jam_mulai'] ?? '-') !== '-' ? ($ringkasan['jam_mulai'] . ' - ' . ($ringkasan['jam_selesai'] ?? '-')) : '-' ?></td>
            </tr>
            <tr>
                <td>Tempat/Ruang</td>
                <td>:</td>
                <td><?= !empty($ringkasan['ruang']) ? implode(', ', $ringkasan['ruang']) : '-' ?></td>
            </tr>
            <tr>
                <td>Jenis Ujian</td>
                <td>:</td>
                <td><strong><?= $jenis_ujian ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- Section B: Data Mata Pelajaran -->
    <div class="section">
        <div class="section-title">B. Data Mata Pelajaran</div>
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th width="100">Jumlah Peserta</th>
                    <th width="80">Jumlah Sesi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($mapel as $m): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= esc($m['nama_mapel'] ?? '-') ?></td>
                    <td><?= !empty($m['kelas_display']) ? esc($m['kelas_display']) : '-' ?></td>
                    <td class="text-center"><?= (int) ($m['jml_peserta'] ?? 0) ?></td>
                    <td class="text-center"><?= (int) ($m['jml_sesi'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($mapel)): ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada mata pelajaran terjadwal</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><strong>Total Mata Pelajaran Hari Ini: <?= count($mapel) ?></strong></p>
    </div>

    <!-- Section C: Rekap Kehadiran -->
    <div class="section">
        <div class="section-title">C. Rekap Kehadiran Peserta (Per Sesi Ujian)</div>
        <table>
            <thead>
                <tr>
                    <th width="35">No</th>
                    <th>Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th width="95">Ruang / Sesi</th>
                    <th width="55">Hadir</th>
                    <th width="55">T. Hadir</th>
                    <th width="60">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($rekap as $r): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= !empty($r['kelas_display']) ? esc($r['kelas_display']) : '-' ?></td>
                    <td><?= esc($r['nama_mapel'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($r['nama_ruang'] ?? '-') ?><br><small><?= esc($r['nama_sesi'] ?? '') ?></small></td>
                    <td class="text-center"><?= $r['jml_hadir'] === null ? '-' : (int) $r['jml_hadir'] ?></td>
                    <td class="text-center"><?= $r['jml_tidak_hadir'] === null ? '-' : (int) $r['jml_tidak_hadir'] ?></td>
                    <td class="text-center"><strong><?= $r['jml_seharusnya'] === null ? '-' : (int) $r['jml_seharusnya'] ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rekap)): ?>
                <tr>
                    <td colspan="7" class="text-center">Data Berita Acara belum diisi</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Section D: Daftar Siswa Tidak Hadir -->
    <div class="section">
        <div class="section-title">D. Daftar Siswa Tidak Hadir (Detail)</div>
        <table>
            <thead>
                <tr>
                    <th width="35">No</th>
                    <th>Nama Siswa</th>
                    <th width="90">Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th width="80">Ruang</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no_absent = 1; foreach ($tidak_hadir as $t): ?>
                <tr>
                    <td class="text-center"><?= $no_absent++ ?></td>
                    <td><?= esc($t['nama'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($t['kelas'] ?? '-') ?></td>
                    <td><?= esc($t['nama_mapel'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($t['nama_ruang'] ?? '-') ?></td>
                    <td><?= esc($t['alasan'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tidak_hadir)): ?>
                <tr>
                    <td colspan="6" class="text-center">- Tidak ada siswa yang absen -</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        $ringkas_alasan = $ringkasan['alasan_summary'] ?? [];
        $bagian_alasan = [];
        if (($ringkas_alasan['Sakit'] ?? 0) > 0) {
            $bagian_alasan[] = 'Sakit: ' . (int) $ringkas_alasan['Sakit'] . ' orang';
        }
        if (($ringkas_alasan['Ijin'] ?? 0) > 0) {
            $bagian_alasan[] = 'Ijin: ' . (int) $ringkas_alasan['Ijin'] . ' orang';
        }
        if (($ringkas_alasan['Tanpa Berita'] ?? 0) > 0) {
            $bagian_alasan[] = 'Tanpa Berita: ' . (int) $ringkas_alasan['Tanpa Berita'] . ' orang';
        }
        if (($ringkas_alasan['Lainnya'] ?? 0) > 0) {
            $bagian_alasan[] = 'Lainnya: ' . (int) $ringkas_alasan['Lainnya'] . ' orang';
        }
        ?>
        <p style="font-size: 9.5pt; margin: 2px 0 8px 25px;">
            <strong>Rekap alasan ketidakhadiran:</strong>
            <?= $bagian_alasan ? esc(implode(' | ', $bagian_alasan)) : 'Tidak ada keterangan ketidakhadiran.' ?>
        </p>
    </div>

    <!-- Section E: Data Pengawas -->
    <div class="section">
        <div class="section-title">E. Data Pengawas Ujian</div>
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Nama Pengawas</th>
                    <th>Mata Pelajaran</th>
                    <th width="90">Ruang</th>
                </tr>
            </thead>
            <tbody>
                <?php $no_p = 1; foreach ($pengawas as $p): ?>
                <tr>
                    <td class="text-center"><?= $no_p++ ?></td>
                    <td>
                        <?= esc($p['nama_guru'] ?? '-') ?><br>
                        <small>NIP. <?= esc($p['nip'] ?? '-') ?></small>
                    </td>
                    <td><?= esc($p['nama_mapel'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($p['nama_ruang'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pengawas)): ?>
                <tr>
                    <td colspan="4" class="text-center">Data pengawas tidak ditemukan</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Section F: Pelaksanaan Ujian -->
    <div class="section">
        <div class="section-title">F. Pelaksanaan Ujian</div>
        <table class="indent-table no-border">
            <tr>
                <td width="200">Ujian dimulai pukul</td>
                <td width="20">:</td>
                <td><?= $ringkasan['jam_mulai'] ?? '-' ?></td>
            </tr>
            <tr>
                <td>Ujian selesai pukul</td>
                <td>:</td>
                <td><?= $ringkasan['jam_selesai'] ?? '-' ?></td>
            </tr>
            <tr>
                <td>Jumlah sesi ujian</td>
                <td>:</td>
                <td><?= (int) ($ringkasan['jml_sesi'] ?? 0) ?> sesi (<?= (int) ($ringkasan['sesi_terisi'] ?? 0) ?> sesi sudah diisi berita acara)</td>
            </tr>
            <tr>
                <td>Jumlah peserta hadir</td>
                <td>:</td>
                <td><?= (int) ($ringkasan['total_hadir'] ?? 0) ?> orang dari <?= (int) ($ringkasan['total_peserta'] ?? 0) ?> peserta</td>
            </tr>
            <tr>
                <td>Sistem</td>
                <td>:</td>
                <td>CBT (Computer Based Test)</td>
            </tr>
            <tr>
                <td>Kondisi pelaksanaan</td>
                <td>:</td>
                <td><?= $ringkasan['kondisi'] ?? 'Lancar dan tertib' ?></td>
            </tr>
        </table>
    </div>

    <!-- Section G: Kendala -->
    <div class="section">
        <div class="section-title">G. Kendala Selama Ujian</div>
        <table>
            <thead>
                <tr>
                    <th width="35">No</th>
                    <th width="70">Waktu</th>
                    <th width="130">Tempat</th>
                    <th>Kendala</th>
                    <th>Solusi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no_k = 1; foreach ($kendala as $k): ?>
                <tr>
                    <td class="text-center"><?= $no_k++ ?></td>
                    <td class="text-center"><?= esc($k['waktu'] ?? '-') ?></td>
                    <td class="text-center"><?= esc($k['nama_ruang'] ?? '-') ?><br><small><?= esc($k['nama_mapel'] ?? '') ?></small></td>
                    <td><?= esc($k['kendala'] ?? '-') ?></td>
                    <td><?= esc($k['solusi'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($kendala)): ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada kendala berarti</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Section H: Kesimpulan -->
    <div class="section">
        <div class="section-title">H. Kesimpulan</div>
        <table class="indent-table no-border">
            <tr>
                <td width="200">Tingkat kehadiran</td>
                <td width="20">:</td>
                <td><?= $ringkasan['persentase_hadir'] ?? 0 ?>%</td>
            </tr>
            <tr>
                <td>Pelaksanaan</td>
                <td>:</td>
                <td><strong><?= ($ringkasan['kondisi'] ?? 'Lancar dan tertib') === 'Terkendala' ? 'Terkendala' : 'Lancar' ?></strong></td>
            </tr>
            <tr>
                <td>Catatan penting</td>
                <td>:</td>
                <td><?= esc($ringkasan['catatan'] ?? 'Ujian berjalan lancar dan tertib.') ?></td>
            </tr>
        </table>
    </div>

    <!-- Section I: Tanda Tangan -->
    <div class="section signature-section">
        <table class="signature-table" style="page-break-inside: avoid;">
            <tr>
                <td></td>
                <td></td>
                <td>
                    <p style="margin: 0;"><?= $kota ?>, <?= $tanggal_ttd ?></p>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 5px;">
                    <p style="margin: 0 0 5px 0;">Pengawas 1,</p>
                    <div class="signature-space"></div>
                    <span class="signature-line"><?= esc($pengawas[0]['nama_guru'] ?? '') ?></span><br>
                    <small>NIP. <?= esc($pengawas[0]['nip'] ?? '-') ?></small>
                </td>
                <td style="padding-top: 5px;">
                    <p style="margin: 0 0 5px 0;">Pengawas 2,</p>
                    <div class="signature-space"></div>
                    <span class="signature-line"><?= esc($pengawas[1]['nama_guru'] ?? '') ?></span><br>
                    <small>NIP. <?= esc($pengawas[1]['nip'] ?? '-') ?></small>
                </td>
                <td style="padding-top: 5px;">
                    <p style="margin: 0 0 5px 0;">Proktor,</p>
                    <div class="signature-space"></div>
                    <span class="signature-line"><?= $setting->proktor ?? '' ?></span><br>
                    <small>NIP. <?= $setting->nip_proktor ?? '-' ?></small>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-signature" style="page-break-inside: avoid; margin-top: 10px;">
        <p style="margin: 0;">Mengetahui,</p>
        <p style="margin: 0 0 5px 0;">Kepala Sekolah / Ketua Panitia</p>
        <div class="signature-space"></div>
        <span class="signature-line"><?= $setting->kepsek ?? '' ?></span><br>
        <small>NIP. <?= $setting->nip ?? '-' ?></small>
    </div>

    <?php if (!empty($lampiran_foto)): ?>
    <!-- Lampiran Foto -->
    <div style="page-break-before: always;">
        <div class="section">
            <div class="section-title">Lampiran: Foto Dokumentasi Pelaksanaan Ujian</div>
            <p style="font-size: 11px; color: #666; margin-bottom: 10px;">Berikut adalah foto-foto dokumentasi dari berita acara pelaksanaan ujian:</p>
            
            <table style="width: 100%; border-collapse: separate; border-spacing: 10px;">
                <?php 
                $no_foto = 1; 
                $total_foto = count($lampiran_foto);
                $cols = 2; // 2 columns
                $rows_per_page = 3; // 3 rows = 6 photos per page
                
                for ($i = 0; $i < $total_foto; $i += $cols): 
                    // Add page break after every 6 photos (3 rows x 2 cols)
                    $page_break = ($i > 0 && $i % ($cols * $rows_per_page) == 0) ? 'page-break-before: always;' : '';
                ?>
                <tr style="<?= $page_break ?>">
                    <?php for ($j = 0; $j < $cols; $j++): 
                        $index = $i + $j;
                        if ($index >= $total_foto) {
                            echo '<td style="width: 50%;"></td>';
                            continue;
                        }
                        $foto = $lampiran_foto[$index];
                    ?>
                    <td style="width: 50%; vertical-align: top; padding: 5px;">
                        <div style="border: 1px solid #ddd; padding: 5px; background: #f9f9f9; text-align: center; min-height: 100px;">
                            <?php
                            $imgPath = FCPATH . 'uploads/laporan/' . $foto['file'];
                            if (file_exists($imgPath) && is_file($imgPath)):
                                // Resize image to reduce memory usage
                                $imgInfo = getimagesize($imgPath);
                                if ($imgInfo) {
                                    $mime = $imgInfo['mime'];
                                    list($width, $height) = $imgInfo;
                                    // Target max 400px width/height for PDF
                                    $maxDim = 400;
                                    if ($width > $maxDim || $height > $maxDim) {
                                        $ratio = $width / $height;
                                        if ($width > $height) {
                                            $newWidth = $maxDim;
                                            $newHeight = $maxDim / $ratio;
                                        } else {
                                            $newHeight = $maxDim;
                                            $newWidth = $maxDim * $ratio;
                                        }
                                        // Create resized image
                                        $srcImage = ($mime == 'image/png') ? imagecreatefrompng($imgPath) : imagecreatefromjpeg($imgPath);
                                        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
                                        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                                        // Output to buffer
                                        ob_start();
                                        if ($mime == 'image/png') {
                                            imagepng($dstImage, null, 6);
                                        } else {
                                            imagejpeg($dstImage, null, 75);
                                        }
                                        $imgData = base64_encode(ob_get_clean());
                                        imagedestroy($srcImage);
                                        imagedestroy($dstImage);
                                    } else {
                                        // Small enough, use original
                                        $imgData = base64_encode(file_get_contents($imgPath));
                                    }
                                } else {
                                    $mime = 'image/jpeg';
                                    $imgData = base64_encode(file_get_contents($imgPath));
                                }
                            ?>
                            <img src="data:<?= $mime ?>;base64,<?= $imgData ?>" 
                                 style="max-width: 100%; max-height: 180px; width: auto; height: auto;" 
                                 alt="Foto <?= $no_foto ?>">
                            <?php else: ?>
                            <div style="padding: 20px; color: #999; font-size: 10px;">File tidak ditemukan: <?= esc($foto['file']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top: 5px; font-size: 9px; text-align: left;">
                            <strong>Gambar <?= $no_foto ?>.</strong> <?= esc($foto['keterangan']) ?> <br>
                            <span style="color: #666;"><?= esc($foto['mapel']) ?> | <?= esc($foto['ruang']) ?></span>
                        </div>
                    </td>
                    <?php $no_foto++; endfor; ?>
                </tr>
                <?php endfor; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>
