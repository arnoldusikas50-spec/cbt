<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0fw-bold"><?= $judul ?></h1>
                </div>
                <div class="col-sm-6">
                    <button onclick="window.history.back()" type="button" class="btn btn-sm btn-danger shadow-sm float-end ms-2">
                        <i class="bi bi-arrow-left-square me-1"></i><span
                            class="d-none d-sm-inline-block">Kembali</span>
                    </button>
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Analisis</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title text-bold m-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i><?= $subjudul ?></h3>
                    <div class="card-tools ms-auto">
                        <button type="button" id="download-word" class="btn btn-sm btn-primary shadow-sm"
                            data-bs-toggle="tooltip" title="Download Word">
                            <i class="bi bi-file-word me-1"></i> Ekspor ke Word
                        </button>
                    </div>
                </div>
                <div class="card-body" id="print-word">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm table-striped mb-0">
                                <tr>
                                    <td class="fw-bold text-muted" style="width: 140px;">Bank Soal</td>
                                    <td class="fw-bold text-primary"><?= $info->bank_kode ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Mata Pelajaran</td>
                                    <td class="fw-bold"><?= $jadwal->nama_mapel ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Guru Pengampu</td>
                                    <td class="fw-bold"><?= $jadwal->guru ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Jumlah Soal</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-light text-dark border">PG: <b><?= $info->tampil_pg ?? 0 ?></b></span>
                                            <span class="badge bg-light text-dark border">Komp: <b><?= $info->tampil_kompleks ?? 0 ?></b></span>
                                            <span class="badge bg-light text-dark border">Jodoh: <b><?= $info->tampil_jodohkan ?? 0 ?></b></span>
                                            <span class="badge bg-light text-dark border">Isian: <b><?= $info->tampil_isian ?? 0 ?></b></span>
                                            <span class="badge bg-light text-dark border">Esai: <b><?= $info->tampil_esai ?? 0 ?></b></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm table-striped mb-0">
                                <tr>
                                    <td class="fw-bold text-muted" style="width: 150px;">Jenis Penilaian</td>
                                    <td class="fw-bold"><?= $jadwal->kode_jenis ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Kelas</td>
                                    <td class="fw-bold"><?= $kelas ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Jumlah Siswa</td>
                                    <td class="fw-bold text-success"><?= $siswa ?> Siswa</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Waktu Pelaksanaan</td>
                                    <td class="fw-bold">
                                        <?= singkat_tanggal(date('d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">TP / SMT</td>
                                    <td class="fw-bold"><?= $jadwal->tp ?> <span class="text-muted mx-1">/</span> <?= $jadwal->smt ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if (isset($soal_pg) && count($soal_pg) > 0): ?>
                            <div class="mt-4 mb-3 d-flex align-items-center">
                                <span class="badge bg-primary me-2 fs-6">1</span>
                                <h5 class="fw-bold m-0">Soal Pilihan Ganda</h5>
                            </div>
                            <div class="table-responsive border rounded bg-white">
                                <table class="table table-bordered table-hover mb-0 align-middle">
                                    <thead class="bg-light text-center text-uppercase text-xs font-weight-bold">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Soal & Statistika</th>
                                            <th style="width: 100px;">Kunci</th>
                                            <th style="width: 120px;">Respon Siswa</th>
                                            <th style="width: 250px;">Analisa Butir Soal</th>
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
                                                $analisa = $analisis[$idSoal] ?? null;
                                            }
                                            $jml_opsi = $info->opsi ?? 5;
                                            ?>
                                                <tr>
                                                    <td class="text-center fw-bold align-top py-3 bg-light border-end" rowspan="<?= $jml_opsi + 1 ?>"><?= $no ?></td>
                                                    <td class="align-top py-3 border-bottom-0" colspan="4">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="soal-text w-100"><?= $soal->soal ?></div>
                                                            <?php if (isset($analisis['reliabilitas']) && $no == 1): ?>
                                                                    <div class="card border ms-3 shadow-none mb-0" style="min-width: 200px; background: #f8f9fa;">
                                                                        <div class="card-body p-2 text-center">
                                                                            <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.7rem;">Reliabilitas Tes</small>
                                                                            <div class="mb-1">Alpha Cronbach</div>
                                                                            <span class="badge bg-primary fs-6 mb-1"><?= $analisis['reliabilitas']['alpha_cronbach'] ?></span>
                                                                            <div class="small fw-bold text-secondary"><?= $analisis['reliabilitas']['ket'] ?></div>
                                                                        </div>
                                                                    </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                        
                                                <?php for ($i = 0; $i < $jml_opsi; $i++):
                                                    $curr_opsi = $opsis[$i] ?? '';
                                                    $is_kunci = strtoupper($curr_opsi) == strtoupper($soal->jawaban ?? '');
                                                    $count_siswa = isset($jawaban_siswa['jawab_' . $curr_opsi]) ? count($jawaban_siswa['jawab_' . $curr_opsi]) : 0;

                                                    $row_bg = $is_kunci ? 'table-success' : '';
                                                    $badge_bg = $is_kunci ? 'bg-success' : 'bg-light text-dark border';
                                                    ?>
                                                    <tr class="<?= $row_bg ?>">
                                                        <td class="py-2 ps-4">
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge <?= $badge_bg ?> me-2 d-flex align-items-center justify-content-center" style="width:24px; height:24px;"><?= strtoupper($curr_opsi) ?></span>
                                                                <div class="flex-grow-1"><?= $soal->{'opsi_' . $curr_opsi} ?></div>
                                                            </div>
                                                        </td>
                                            
                                                        <td class="text-center align-middle py-2">
                                                            <?php if ($is_kunci): ?>
                                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-center align-middle py-2">
                                                            <?php if ($count_siswa > 0): ?>
                                                                    <span class="badge bg-secondary rounded-pill px-3"><?= $count_siswa ?></span>
                                                            <?php else: ?>
                                                                    <span class="text-muted small">-</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <?php if ($i === 0): ?>
                                                                <td rowspan="<?= $jml_opsi ?>" class="align-top bg-white border-start p-2">
                                                                    <?php if ($analisa):
                                                                        $tk = $analisa['tingkat_kesulitan'];
                                                                        $dp = $analisa['daya_pembeda'];

                                                                        // Color Logic
                                                                        $tk_badge = $tk >= 0.70 ? 'bg-success' : ($tk < 0.30 ? 'bg-danger' : 'bg-warning text-dark');
                                                                        $dp_badge = $dp >= 0.40 ? 'bg-primary' : ($dp < 0.20 ? 'bg-danger' : 'bg-success');
                                                                        ?>
                                                                            <div class="d-flex flex-column gap-2">
                                                                                <div class="d-flex justify-content-between align-items-center border px-2 py-1 rounded bg-light">
                                                                                    <span class="text-muted small">Kesukaran</span>
                                                                                    <div class="text-end lh-1">
                                                                                        <div class="fw-bold"><?= $tk ?></div>
                                                                                        <small class="badge <?= $tk_badge ?> rounded-pill" style="font-size: 0.6rem;"><?= $analisa['level_kesulitan'] ?></small>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex justify-content-between align-items-center border px-2 py-1 rounded bg-light">
                                                                                    <span class="text-muted small">Pembeda</span>
                                                                                    <div class="text-end lh-1">
                                                                                        <div class="fw-bold"><?= $dp ?></div>
                                                                                        <small class="badge <?= $dp_badge ?> rounded-pill" style="font-size: 0.6rem;"><?= $analisa['level_pembeda'] ?></small>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="d-flex justify-content-between align-items-center border px-2 py-1 rounded bg-light">
                                                                                    <span class="text-muted small">Validitas</span>
                                                                                    <div class="text-end lh-1">
                                                                                        <div class="fw-bold"><?= round($analisa['validitas'], 2) ?></div>
                                                                                        <small class="text-muted" style="font-size: 0.65rem;"><?= $analisa['level_validitas'] ?></small>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                    <?php else: ?>
                                                                            <div class="d-flex h-100 align-items-center justify-content-center text-muted small fst-italic">
                                                                                Belum dianalisis
                                                                            </div>
                                                                    <?php endif; ?>
                                                                </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endfor; ?>
                                                <tr class="table-group-divider border-top-2"></tr>
                                            <?php
                                            $no++;
                                        endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php endif; ?>

                    <?php if (isset($soal_urutan) && count($soal_urutan) > 0): ?>
                            <div class="mt-4 mb-3 d-flex align-items-center">
                                <span class="badge bg-primary me-2 fs-6">2</span>
                                <h5 class="fw-bold m-0">Soal Mengurutkan</h5>
                            </div>
                            <div class="table-responsive border rounded bg-white p-0">
                                <table class="table table-bordered table-hover mb-0 w-100">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Soal & Kunci Jawaban</th>
                                            <th style="width: 200px;">Analisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $no = 1;
                                    foreach ($soal_urutan as $soal):
                                        $analisa = $analisis[$soal->id_soal] ?? null;
                                        $kunci = $soal->jawaban;
                                        $kunciStr = '';
                                        if (is_array($kunci)) {
                                            $arrK = [];
                                            foreach ($kunci as $k) {
                                                $arrK[] = is_string($k) ? $k : ($k['teks'] ?? '?');
                                            }
                                            $kunciStr = implode(' <i class="bi bi-arrow-right mx-1 text-muted"></i> ', $arrK);
                                        }
                                        ?>
                                            <tr>
                                                <td class="text-center align-top py-3 fw-bold"><?= $no++ ?></td>
                                                <td class="p-3 align-top">
                                                    <div class="mb-3"><?= $soal->soal ?></div>
                                                    <div class="p-3 bg-light border rounded position-relative">
                                                        <span class="position-absolute top-0 start-0 badge bg-success m-2">Kunci</span>
                                                        <div class="mt-3"><?= $kunciStr ?></div>
                                                    </div>
                                                </td>
                                                <td class="p-2 align-top">
                                                    <?php if ($analisa):
                                                        $warna_tk = $analisa['tingkat_kesulitan'] >= 0.70 ? 'alert alert-success' : ($analisa['tingkat_kesulitan'] < 0.30 ? 'alert alert-danger' : 'alert alert-warning');
                                                        $warna_valid = $analisa['validitas'] >= 0.70 ? 'alert alert-primary' : ($analisa['validitas'] < 0.30 ? 'alert alert-danger' : 'alert alert-success');
                                                        $warna_dp = $analisa['daya_pembeda'] >= 0.70 ? 'alert alert-primary' : ($analisa['daya_pembeda'] < 0.20 ? 'alert alert-danger' : ($analisa['daya_pembeda'] > 0.39 ? 'alert alert-success' : 'alert alert-warning'));
                                                        ?>
                                                            <div class="<?= $warna_tk ?> p-2 mb-2 rounded shadow-sm">
                                                                <div class="small opacity-75">Kesukaran</div>
                                                                <div class="d-flex justify-content-between align-items-end">
                                                                    <span class="fw-bold"><?= $analisa['tingkat_kesulitan'] ?></span>
                                                                    <span class="small badge bg-white text-dark bg-opacity-75"><?= $analisa['level_kesulitan'] ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="<?= $warna_valid ?> p-2 mb-2 rounded shadow-sm">
                                                                <div class="small opacity-75">Validasi</div>
                                                                <div class="d-flex justify-content-between align-items-end">
                                                                    <span class="fw-bold"><?= round($analisa['validitas'], 2) ?></span>
                                                                    <span class="small badge bg-white text-dark bg-opacity-75"><?= $analisa['level_validitas'] ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="<?= $warna_dp ?> p-2 mb-0 rounded shadow-sm">
                                                                <div class="small opacity-75">Pembeda</div>
                                                                <div class="d-flex justify-content-between align-items-end">
                                                                    <span class="fw-bold"><?= round($analisa['daya_pembeda'], 2) ?></span>
                                                                    <span class="small badge bg-white text-dark bg-opacity-75"><?= $analisa['level_pembeda'] ?></span>
                                                                </div>
                                                            </div>
                                                    <?php else: ?>
                                                            <div class="text-center text-muted small">Belum dianalisis</div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php endif; ?>

                    <?php if (isset($soal_cloze) && count($soal_cloze) > 0): ?>
                            <div class="mt-4 mb-3 d-flex align-items-center">
                                <span class="badge bg-primary me-2 fs-6">3</span>
                                <h5 class="fw-bold m-0">Soal Isian Rumpang</h5>
                            </div>
                            <div class="table-responsive border rounded bg-white p-0">
                                <table class="table table-bordered table-hover mb-0 w-100">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Soal & Kunci Jawaban</th>
                                            <th style="width: 200px;">Analisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $no = 1;
                                    foreach ($soal_cloze as $soal):
                                        $analisa = $analisis[$soal->id_soal] ?? null;
                                        $kunci = $soal->jawaban;
                                        $kunciStr = '';
                                        if (is_array($kunci)) {
                                            $arrK = [];
                                            foreach ($kunci as $idx => $k) {
                                                $val = is_string($k) ? $k : ($k['jawaban'] ?? '?');
                                                $arrK[] = '<span class="badge bg-light text-dark border me-1">(' . ($idx + 1) . ')</span><span class="fw-bold text-primary">' . $val . '</span>';
                                            }
                                            $kunciStr = implode('<br>', $arrK);
                                        }
                                        ?>
                                            <tr>
                                                <td class="text-center align-top py-3 fw-bold"><?= $no++ ?></td>
                                                <td class="p-3 align-top">
                                                    <div class="mb-3"><?= $soal->soal ?></div>
                                                    <div class="p-3 bg-light border rounded">
                                                        <strong class="d-block mb-2 text-success">Kunci Jawaban:</strong>
                                                        <div class="ms-2"><?= $kunciStr ?></div>
                                                    </div>
                                                </td>
                                                <td class="p-2 align-top">
                                                    <?php if ($analisa):
                                                        $warna_tk = $analisa['tingkat_kesulitan'] >= 0.70 ? 'alert alert-success' : ($analisa['tingkat_kesulitan'] < 0.30 ? 'alert alert-danger' : 'alert alert-warning');
                                                        $warna_valid = $analisa['validitas'] >= 0.70 ? 'alert alert-primary' : ($analisa['validitas'] < 0.30 ? 'alert alert-danger' : 'alert alert-success');
                                                        $warna_dp = $analisa['daya_pembeda'] >= 0.70 ? 'alert alert-primary' : ($analisa['daya_pembeda'] < 0.20 ? 'alert alert-danger' : ($analisa['daya_pembeda'] > 0.39 ? 'alert alert-success' : 'alert alert-warning'));
                                                        ?>
                                                            <div class="<?= $warna_tk ?> p-2 mb-2 rounded shadow-sm">
                                                                <div class="small opacity-75">Kesukaran</div>
                                                                <div class="d-flex justify-content-between align-items-end">
                                                                    <span class="fw-bold"><?= $analisa['tingkat_kesulitan'] ?></span>
                                                                    <span class="small badge bg-white text-dark bg-opacity-75"><?= $analisa['level_kesulitan'] ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="<?= $warna_valid ?> p-2 mb-2 rounded shadow-sm">
                                                                <div class="small opacity-75">Validasi</div>
                                                                <div class="d-flex justify-content-between align-items-end">
                                                                    <span class="fw-bold"><?= round($analisa['validitas'], 2) ?></span>
                                                                    <span class="small badge bg-white text-dark bg-opacity-75"><?= $analisa['level_validitas'] ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="<?= $warna_dp ?> p-2 mb-0 rounded shadow-sm">
                                                                <div class="small opacity-75">Pembeda</div>
                                                                <div class="d-flex justify-content-between align-items-end">
                                                                    <span class="fw-bold"><?= round($analisa['daya_pembeda'], 2) ?></span>
                                                                    <span class="small badge bg-white text-dark bg-opacity-75"><?= $analisa['level_pembeda'] ?></span>
                                                                </div>
                                                            </div>
                                                    <?php else: ?>
                                                            <div class="text-center text-muted small">Belum dianalisis</div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php endif; ?>
                </div>
                <div class="overlay-wrapper d-none" id="loading">
                    <div class="overlay">
                        <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                        <div class="text-bold pt-2">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?= $this->endSection() ?>

<?php // Note: Script section starts here in original file, keeping it intact ?>

<?= $this->section('scripts') ?>
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
<?= $this->endSection() ?>