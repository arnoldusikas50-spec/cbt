<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6">
                    <button onclick="window.history.back()" type="button" class="btn btn-sm btn-danger float-end">
                        <i class="bi bi-arrow-left-square"></i><span
                            class="d-none d-sm-inline-block ms-1">Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools ms-auto">
                        <button type="button" id="download-word" class="btn btn-primary align-text-bottom"
                            data-bs-toggle="tooltip" title="Download Word">
                            <i class="bi bi-file-word ms-1 me-1"></i> Ekspor ke Word
                        </button>
                    </div>
                </div>
                <div class="card-body" id="print-word">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <td colspan="1" style="min-width: 160px;">Soal</td>
                                    <td colspan="2"><b><?= $info->bank_kode ?></b></td>
                                </tr>
                                <tr>
                                    <td colspan="1">Mata Pelajaran</td>
                                    <td colspan="2"><b><?= $jadwal->nama_mapel ?></b></td>
                                </tr>
                                <tr>
                                    <td colspan="1">Guru Pengampu</td>
                                    <td colspan="2"><b><?= $jadwal->guru ?></b></td>
                                </tr>
                                <tr>
                                    <td colspan="1">Jumlah Soal</td>
                                    <td colspan="2">
                                        PG:<b><?= $info->tampil_pg ?></b>,
                                        PGKompleks:<b><?= $info->tampil_kompleks ?></b>,
                                        Menjodohkan:<b><?= $info->tampil_jodohkan ?></b>,
                                        IsianSingkat:<b><?= $info->tampil_isian ?></b>,
                                        Uraian:<b><?= $info->tampil_esai ?></b>,
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <td colspan="1" style="min-width: 160px;">Jenis Penilaian</td>
                                    <td colspan="2"><b><?= $jadwal->kode_jenis ?></b></td>
                                </tr>
                                <tr>
                                    <td colspan="1">Kelas</td>
                                    <td colspan="2"><b><?= $kelas ?></b></td>
                                </tr>
                                <tr>
                                    <td colspan="1">Jumlah Siswa</td>
                                    <td colspan="2"><b><?= $siswa ?></b></td>
                                </tr>
                                <tr>
                                    <td colspan="1">Waktu Pelaksanaan</td>
                                    <td colspan="2">
                                        <b><?= singkat_tanggal(date('d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?></b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="1">TP / SMT</td>
                                    <td colspan="2"><b><?= $jadwal->tp ?></b> smt: <b><?= $jadwal->smt ?></b></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php
                    if (count($soal_pg) > 0): ?>
                        <p class="mt-4 text-bold">1. Soal Pilihan Ganda</p>
                        <div class="card-body p-0 table-responsive">
                            <div class="row">
                                <div class="col-12">
                                    <table class="w-100">
                                        <thead>
                                            <tr>
                                                <th
                                                    style="border: 1px solid black;border-collapse: collapse; text-align: center;min-width: 40px;background-color: #f2f4f5;">
                                                    NO.</th>
                                                <th
                                                    style="border: 1px solid black;border-collapse: collapse; text-align: center;min-width: 200px;background-color: #f2f4f5;">
                                                    SOAL</th>
                                                <th style="border: 1px solid black;border-collapse: collapse; text-align: center;min-width: 160px;background-color: #f2f4f5;"
                                                    colspan="2">OPSI</th>
                                                <th
                                                    style="border: 1px solid black;border-collapse: collapse; text-align: center;min-width: 100px;background-color: #f2f4f5;">
                                                    JAWABAN<br />SISWA</th>
                                                <th
                                                    style="border: 1px solid black;border-collapse: collapse; text-align: center;min-width: 160px;background-color: #f2f4f5;">
                                                    Indeks Kesukaran Distraktor</th>
                                                <th
                                                    style="border: 1px solid black;border-collapse: collapse; text-align: center;min-width: 90px;background-color: #f2f4f5;">
                                                    ANALISA</th>
                                                <th
                                                    style="border: 1px solid black;border-collapse: collapse; text-align: center;min-width: 95px;background-color: #f2f4f5;">
                                                    RELIABILITAS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $no = 1;
                                            foreach ($soal_pg as $soal):
                                                $idSoal = $soal->id_soal;
                                                $jawaban_siswa = $jawab_siswa[$idSoal] ?? null;
                                                $analisa = null;
                                                $opsis = ['a', 'b', 'c', 'd', 'e'];
                                                if ($jawab_siswa) {
                                                    $analisa = $analisis[$idSoal];
                                                }
                                                for ($i = 0; $i < $info->opsi; $i++):
                                                    ?>
                                                    <tr>
                                                        <?php if ($i === 0): ?>
                                                            <td rowspan="<?= $info->opsi ?>" class=""
                                                                style="border: 1px solid black;border-collapse: collapse;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                                <?= $no ?></td>
                                                            <td rowspan="<?= $info->opsi ?>" class=""
                                                                style="border: 1px solid black;border-collapse: collapse;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                                <?= $soal->soal ?>
                                                                <p>JAWABAN: <b><?= strtoupper($soal->jawaban ?? '') ?></b></p>
                                                            </td>
                                                        <?php endif;
                                                        $bg = 'table-danger';
                                                        if (strtoupper($opsis[$i] ?? '') == strtoupper($soal->jawaban ?? '')) {
                                                            $bg = 'table-success';
                                                        }
                                                        ?>
                                                        <td class="<?= $bg ?>"
                                                            style="border: 1px solid black;border-collapse: collapse;border-right:0;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                            <?= strtoupper($opsis[$i] ?? '') ?>.</td>
                                                        <td class="<?= $bg ?>"
                                                            style="border: 1px solid black;border-collapse: collapse;border-left:0;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                            <?= $soal->{'opsi_' . $opsis[$i] ?? ''} ?></td>
                                                        <td class="<?= $bg ?>"
                                                            style="border: 1px solid black;border-collapse: collapse;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                            <?= isset($jawaban_siswa['jawab_' . $opsis[$i]]) ?
                                                                count($jawaban_siswa['jawab_' . $opsis[$i]]) : 0 ?> siswa
                                                        </td>
                                                        <td class=""
                                                            style="border: 1px solid black;border-collapse: collapse;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                            <?php if ($analisa):
                                                                $analisa_opsi = $analisa["distraktor"][strtoupper($opsis[$i] ?? '')];
                                                                ?>
                                                                <span>Atas: <b><?= $analisa_opsi['IKD_top'] ?></b></span><br />
                                                                <span>Bawah: <b><?= $analisa_opsi['IKD_bottom'] ?></b></span><br />
                                                                <span>Total: <b><?= $analisa_opsi['IKD_total'] ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <?php if ($i === 0): ?>
                                                            <td rowspan="<?= $info->opsi ?>" class=""
                                                                style="border: 1px solid black;border-collapse: collapse;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                                <?php
                                                                if ($analisa):
                                                                    if ($analisa['tingkat_kesulitan'] >= 0.70) {
                                                                        $warna = 'alert-default-success';
                                                                    } else if ($analisa['tingkat_kesulitan'] < 0.30) {
                                                                        $warna = 'alert-default-danger';
                                                                    } else {
                                                                        $warna = 'alert-default-warning';
                                                                    }

                                                                    if ($analisa['validitas'] >= 0.70) {
                                                                        $warna_valid = 'alert-default-primary';
                                                                    } else if ($analisa['validitas'] < 0.30) {
                                                                        $warna_valid = 'alert-default-danger';
                                                                    } else {
                                                                        $warna_valid = 'alert-default-success';
                                                                    }

                                                                    if ($analisa['daya_pembeda'] >= 0.70) {
                                                                        $warna_daya = 'alert-default-primary';
                                                                    } else if ($analisa['daya_pembeda'] > 0.39 && $analisa['daya_pembeda'] < 0.70) {
                                                                        $warna_daya = 'alert-default-success';
                                                                    } else if ($analisa['daya_pembeda'] < 0.20) {
                                                                        $warna_daya = 'alert-default-danger';
                                                                    } else {
                                                                        $warna_daya = 'alert-default-warning';
                                                                    }
                                                                    ?>
                                                                    <p class="alert <?= $warna ?> p-1">
                                                                        Kesukaran:<br>
                                                                        <b><?= $analisa['tingkat_kesulitan'] ?></b>
                                                                        <?= $analisa['level_kesulitan'] ?>
                                                                    </p>
                                                                    <p class="alert <?= $warna_valid ?> p-1">
                                                                        Validasi:<br>
                                                                        <b><?= round($analisa['validitas'], 2) ?></b>
                                                                        <?= $analisa['level_validitas'] ?>
                                                                    </p>
                                                                    <p class="alert <?= $warna_daya ?> p-1">
                                                                        Pembeda:<br>
                                                                        <b><?= round($analisa['daya_pembeda'], 2) ?></b>
                                                                        <?= $analisa['level_pembeda'] ?>
                                                                    </p>
                                                                <?php endif; ?>
                                                            </td>
                                                            <?php if ($no === 1): ?>
                                                                <td rowspan="<?= $info->opsi * count($soal_pg) ?>" class=""
                                                                    style="border: 1px solid black;border-collapse: collapse;vertical-align: top;padding-left: 6px; padding-right: 6px;">
                                                                    <?php if ($analisa): ?>
                                                                        <p class="alert p-1">
                                                                            Alpha Cronbach:
                                                                            <b><?= $analisis['reliabilitas']['alpha_cronbach'] ?></b>
                                                                        </p>
                                                                        <p class="alert p-1">
                                                                            KR 20: <b><?= $analisis['reliabilitas']['kr20'] ?></b>
                                                                        </p>
                                                                        <p>
                                                                            <?= $analisis['reliabilitas']['ket'] ?>
                                                                        </p>
                                                                    <?php endif; ?>
                                                                </td>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endfor;
                                                $no++; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif;
                    if (isset($soal_urutan) && count($soal_urutan) > 0): ?>
                        <p class="mt-4 text-bold">2. Soal Mengurutkan</p>
                        <div class="card-body p-0 table-responsive">
                            <table class="w-100 table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center bg-light" style="width: 50px">NO.</th>
                                    <th class="text-center bg-light">SOAL & KUNCI</th>
                                    <th class="text-center bg-light" style="width: 200px;">ANALISA</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $no=1; foreach($soal_urutan as $soal):
                                    $analisa = $analisis[$soal->id_soal] ?? null;
                                    $kunci = $soal->jawaban; 
                                    $kunciStr = '';
                                    if(is_array($kunci)) {
                                        $arrK = [];
                                        foreach($kunci as $k) {
                                            $arrK[] = is_string($k) ? $k : ($k['teks'] ?? '?');
                                        }
                                        $kunciStr = implode(' <i class="bi bi-arrow-right"></i> ', $arrK);
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center align-top p-2"><?= $no++ ?></td>
                                        <td class="p-2 align-top">
                                            <?= $soal->soal ?>
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <b>Kunci:</b><br><?= $kunciStr ?>
                                            </div>
                                        </td>
                                        <td class="p-2 align-top">
                                            <?php if ($analisa):
                                                $warna = $analisa['tingkat_kesulitan'] >= 0.70 ? 'alert-default-success' : ($analisa['tingkat_kesulitan'] < 0.30 ? 'alert-default-danger' : 'alert-default-warning');
                                                $warna_valid = $analisa['validitas'] >= 0.70 ? 'alert-default-primary' : ($analisa['validitas'] < 0.30 ? 'alert-default-danger' : 'alert-default-success');
                                                $warna_daya = $analisa['daya_pembeda'] >= 0.70 ? 'alert-default-primary' : ($analisa['daya_pembeda'] < 0.20 ? 'alert-default-danger' : ($analisa['daya_pembeda'] > 0.39 ? 'alert-default-success' : 'alert-default-warning'));
                                                ?>
                                                <div class="alert <?= $warna ?> p-1 mb-1">
                                                    <small>Kesukaran:</small><br>
                                                    <b><?= $analisa['tingkat_kesulitan'] ?></b> <?= $analisa['level_kesulitan'] ?>
                                                </div>
                                                <div class="alert <?= $warna_valid ?> p-1 mb-1">
                                                    <small>Validasi:</small><br>
                                                    <b><?= round($analisa['validitas'], 2) ?></b> <?= $analisa['level_validitas'] ?>
                                                </div>
                                                <div class="alert <?= $warna_daya ?> p-1 mb-0">
                                                    <small>Pembeda:</small><br>
                                                    <b><?= round($analisa['daya_pembeda'], 2) ?></b> <?= $analisa['level_pembeda'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif;
                    if (isset($soal_cloze) && count($soal_cloze) > 0): ?>
                        <p class="mt-4 text-bold">3. Soal Isian Rumpang</p>
                        <div class="card-body p-0 table-responsive">
                            <table class="w-100 table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center bg-light" style="width: 50px">NO.</th>
                                    <th class="text-center bg-light">SOAL & KUNCI</th>
                                    <th class="text-center bg-light" style="width: 200px;">ANALISA</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $no=1; foreach($soal_cloze as $soal):
                                    $analisa = $analisis[$soal->id_soal] ?? null;
                                    $kunci = $soal->jawaban;
                                    $kunciStr = '';
                                    if(is_array($kunci)) {
                                        $arrK = [];
                                        foreach($kunci as $idx => $k) {
                                            $val = is_string($k) ? $k : ($k['jawaban'] ?? '?');
                                            $arrK[] = "(".($idx+1).") ".$val;
                                        }
                                        $kunciStr = implode(', ', $arrK);
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-center align-top p-2"><?= $no++ ?></td>
                                        <td class="p-2 align-top">
                                            <?= $soal->soal ?>
                                            <div class="mt-2 p-2 bg-light border rounded">
                                                <b>Kunci:</b><br><?= $kunciStr ?>
                                            </div>
                                        </td>
                                        <td class="p-2 align-top">
                                            <?php if ($analisa):
                                                $warna = $analisa['tingkat_kesulitan'] >= 0.70 ? 'alert-default-success' : ($analisa['tingkat_kesulitan'] < 0.30 ? 'alert-default-danger' : 'alert-default-warning');
                                                $warna_valid = $analisa['validitas'] >= 0.70 ? 'alert-default-primary' : ($analisa['validitas'] < 0.30 ? 'alert-default-danger' : 'alert-default-success');
                                                $warna_daya = $analisa['daya_pembeda'] >= 0.70 ? 'alert-default-primary' : ($analisa['daya_pembeda'] < 0.20 ? 'alert-default-danger' : ($analisa['daya_pembeda'] > 0.39 ? 'alert-default-success' : 'alert-default-warning'));
                                                ?>
                                                <div class="alert <?= $warna ?> p-1 mb-1">
                                                    <small>Kesukaran:</small><br>
                                                    <b><?= $analisa['tingkat_kesulitan'] ?></b> <?= $analisa['level_kesulitan'] ?>
                                                </div>
                                                <div class="alert <?= $warna_valid ?> p-1 mb-1">
                                                    <small>Validasi:</small><br>
                                                    <b><?= round($analisa['validitas'], 2) ?></b> <?= $analisa['level_validitas'] ?>
                                                </div>
                                                <div class="alert <?= $warna_daya ?> p-1 mb-0">
                                                    <small>Pembeda:</small><br>
                                                    <b><?= round($analisa['daya_pembeda'], 2) ?></b> <?= $analisa['level_pembeda'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>
        </div>
    </section>
    </div>


    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
    <script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>
    <script>
        $(document).ready(function () {
            ajaxcsrf();

            $('table tbody tr img').each(function () {
                var curSrc = $(this).attr('src');
                console.log('src', curSrc);
                if (curSrc.indexOf("http") === -1 && curSrc.indexOf("data:image") === -1) {
                    $(this).attr('src', base_url + curSrc);
                } else if (curSrc.indexOf(base_url) === -1) {
                    var pathUpload = 'uploads';
                    var forReplace = curSrc.split(pathUpload);
                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                }
                $(this).css('max-width', '400px')
            });

            $("#download-word").click(function (event) {
                event.preventDefault();
                var contentDocument = $('#print-word').convertToHtmlFile('analisis', '');
                var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
                var converted = htmlDocx.asBlob(content, {
                    orientation: 'landscape',
                    size: 'A4',
                    margins: { top: 700, bottom: 700, left: 1000, right: 1000 }
                });
                saveAs(converted, `Analisis <?= $jadwal->kode_jenis ?> <?= $jadwal->kode_mapel ?>.docx`);
            });
        });
    </script>