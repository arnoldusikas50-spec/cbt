<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark"><?= esc($judul ?? 'Halaman') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">


        <section class="content">
            <div class="container-fluid">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">Peserta Ujian</h3>
                        <div class="d-flex align-items-center ms-auto">
                            <div id="selector" class="btn-group me-3" role="group">
                                <?php
                                if ($mode == null || $mode == '1') {
                                    $bgR = 'active btn-outline-primary';
                                    $bgK = 'btn-outline-primary';
                                } else {
                                    $bgR = 'btn-outline-primary';
                                    $bgK = 'active btn-outline-primary';
                                }
                                ?>
                                <a href="<?= base_url('cbtcetak/pesertaujian/1') ?>" class="btn <?= $bgR ?>">By
                                    Ruang</a>
                                <a href="<?= base_url('cbtcetak/pesertaujian/2') ?>" class="btn <?= $bgK ?>">By
                                    Kelas</a>
                            </div>

                            <button class="btn btn-success me-2" id="btn-print">
                                <i class="bi bi-printer me-1"></i> Cetak
                            </button>
                            <a href="<?= base_url('cbtcetak') ?>" class="btn btn-danger">
                                <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                            </a>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Jenis Ujian</label>
                                <?php
                                echo form_dropdown(
                                    'jenis',
                                    $ujian,
                                    null,
                                    'id="jenis" class="form-select"'
                                ); ?>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center bg-light border rounded" style="min-height: 300mm">
                            <div id="print-preview" class="m-4">
                                <?php
                                //echo '<pre>';
                                //print_r($siswa);
                                //echo '</pre>';
                                if ($mode == null || $mode == '1'):
                                    foreach ($siswa as $ruang => $sesi):
                                        foreach ($sesi as $ses => $sis): ?>
                                            <div class="border my-shadow mb-3 p-4 bg-white">
                                                <div class="pt-4"
                                                    style="-webkit-justify-content: center;justify-content: center;background: white;width: 210mm; min-height: 297mm;padding: 1mm">
                                                    <div style="display: flex; justify-content: center; align-items: center;">
                                                        <table style="width: 100%; border: 0;">
                                                            <tr>
                                                                <td style="width:15%;">
                                                                    <img src="<?= base_url() . $kop->logo_kiri ?>"
                                                                        style="width:85px; height:85px; margin: 6px;">
                                                                </td>
                                                                <td style="width:70%; text-align: center;">
                                                                    <div style="line-height: 1.1;font-size: 13pt">
                                                                        <?= $kop->sekolah ?>
                                                                    </div>
                                                                    <div style="line-height: 1.1;font-size: 16pt"><b>DAFTAR
                                                                            PESERTA</b>
                                                                    </div>
                                                                    <div style="line-height: 1.1;font-size: 14pt"
                                                                        class="jenis-ujian">
                                                                        Jenis
                                                                        Ujian
                                                                    </div>
                                                                    <div style="line-height: 1.1;font-size: 12pt">Tahun
                                                                        Pelajaran: <?= $tp_active->tahun ?? '-' ?>
                                                                </td>
                                                                <td style="width:15%;">
                                                                    <img src="<?= base_url() . $kop->logo_kanan ?>"
                                                                        style="width:85px; height:85px; margin: 6px; border-style: none">
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <hr class="m-0">
                                                    <?php
                                                    $se = $sesis[$sis[0]->kode_sesi] ?? null;
                                                    $nama_sesi = $se->nama_sesi ?? '';
                                                    $mulai_sesi = $se->waktu_mulai ?? '';
                                                    $akhir_sesi = $se->waktu_akhir ?? '';
                                                    ?>
                                                    <table style="margin-top: 8px">
                                                        <tr>
                                                            <td style="width: 100px">RUANG</td>
                                                            <td> : </td>
                                                            <td><b><?= $sis[0]->nama_ruang ?></b></td>
                                                        </tr>
                                                        <tr>
                                                            <td>SESI</td>
                                                            <td> : </td>
                                                            <td><b><?= $nama_sesi ?></b></td>
                                                        </tr>
                                                        <tr>
                                                            <td>WAKTU</td>
                                                            <td> : </td>
                                                            <td><b>
                                                                    <?= !empty($mulai_sesi) ? date('H:i', strtotime($mulai_sesi)) : '' ?>
                                                                    sampai
                                                                    <?= !empty($akhir_sesi) ? date('H:i', strtotime($akhir_sesi)) : '' ?></b>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table
                                                        style="width: 100%; border: 1px solid black;border-collapse: collapse; font-size: 10pt;margin-top: 8px">
                                                        <thead>
                                                            <tr>
                                                                <th
                                                                    style="border: 1px solid black; width: 40px; height: 50px; text-align: center;">
                                                                    NO
                                                                </th>
                                                                <th style="border: 1px solid black; text-align: center;">NO.
                                                                    PESERTA
                                                                </th>
                                                                <th style="border: 1px solid black; text-align: center;">NAMA
                                                                    PESERTA
                                                                </th>
                                                                <th style="border: 1px solid black; text-align: center;">KELAS
                                                                </th>
                                                                <th style="border: 1px solid black; text-align: center;">RUANG
                                                                </th>
                                                                <th style="border: 1px solid black; text-align: center;">SESI
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $no = 1;
                                                            foreach ($sis as $s): ?>
                                                                <tr>
                                                                    <td
                                                                        style="border: 1px solid black; text-align: center;height: 30px">
                                                                        <?= $no ?>
                                                                    </td>
                                                                    <td style="border: 1px solid black; text-align: center;">
                                                                        <?= $s->nomor_peserta ?? '-' ?>
                                                                    </td>
                                                                    <td style="border: 1px solid black; padding: 1px 0 1px 6px">
                                                                        <?= $s->nama ?>
                                                                    </td>
                                                                    <td style="border: 1px solid black; text-align: center;">
                                                                        <?= $s->nama_kelas ?>
                                                                    </td>
                                                                    <td style="border: 1px solid black; text-align: center;">
                                                                        <?= $s->nama_ruang ?>
                                                                    </td>
                                                                    <td style="border: 1px solid black; text-align: center;">
                                                                        <?= $s->nama_sesi ?>
                                                                    </td>
                                                                </tr>
                                                                <?php $no++; endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div style="page-break-after: always"></div>
                                        <?php endforeach; ?>
                                    <?php endforeach;
                                else:
                                    foreach ($siswa as $kelas => $sis): ?>
                                        <div class="border my-shadow mb-3 p-4 bg-white">
                                            <div class="pt-4"
                                                style="-webkit-justify-content: center;justify-content: center;background: white;width: 210mm; min-height: 297mm;padding: 1mm">
                                                <div style="display: flex; justify-content: center; align-items: center;">
                                                    <table style="width: 100%; border: 0;">
                                                        <tr>
                                                            <td style="width:15%;">
                                                                <img src="<?= base_url() . $kop->logo_kiri ?>"
                                                                    style="width:85px; height:85px; margin: 6px;">
                                                            </td>
                                                            <td style="width:70%; text-align: center;">
                                                                <div style="line-height: 1.1;font-size: 13pt">
                                                                    <?= $kop->sekolah ?>
                                                                </div>
                                                                <div style="line-height: 1.1;font-size: 16pt"><b>DAFTAR
                                                                        PESERTA</b>
                                                                </div>
                                                                <div style="line-height: 1.1;font-size: 14pt"
                                                                    class="jenis-ujian">
                                                                    Jenis
                                                                    Ujian
                                                                </div>
                                                                <div style="line-height: 1.1;font-size: 12pt">Tahun
                                                                    Pelajaran: <?= $tp_active->tahun ?? '-' ?>
                                                            </td>
                                                            <td style="width:15%;">
                                                                <img src="<?= base_url() . $kop->logo_kanan ?>"
                                                                    style="width:85px; height:85px; margin: 6px; border-style: none">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <hr class="m-0">
                                                <table>
                                                    <tr>
                                                        <td style="width: 100px">Kelas</td>
                                                        <td> :</td>
                                                        <td> <?= $sis[0]->nama_kelas ?></td>
                                                    </tr>
                                                    <?php foreach ($sesis as $sss): ?>
                                                        <tr>
                                                            <td><?= $sss->nama_sesi ?></td>
                                                            <td> :</td>
                                                            <td>
                                                                <?php
                                                                //var_dump($sss);
                                                                ?>
                                                                <?= !empty($sss->waktu_mulai) ? date('H:i', strtotime($sss->waktu_mulai)) : '' ?>
                                                                sampai
                                                                <?= !empty($sss->waktu_akhir) ? date('H:i', strtotime($sss->waktu_akhir)) : '' ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                                <table
                                                    style="width: 100%; border: 1px solid black;border-collapse: collapse; font-size: 10pt">
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                style="border: 1px solid black; width: 40px; height: 40px; text-align: center;">
                                                                NO
                                                            </th>
                                                            <th style="border: 1px solid black; text-align: center;">NO.
                                                                PESERTA
                                                            </th>
                                                            <th style="border: 1px solid black; text-align: center;">NAMA
                                                                PESERTA
                                                            </th>
                                                            <th style="border: 1px solid black; text-align: center;">KELAS
                                                            </th>
                                                            <th style="border: 1px solid black; text-align: center;">RUANG
                                                            </th>
                                                            <th style="border: 1px solid black; text-align: center;">SESI
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $no = 1;
                                                        foreach ($sis as $s): ?>
                                                            <tr>
                                                                <td style="border: 1px solid black; text-align: center;"><?= $no ?>
                                                                </td>
                                                                <td style="border: 1px solid black; text-align: center;">
                                                                    <?= $s->nomor_peserta ?? '-' ?>
                                                                </td>
                                                                <td style="border: 1px solid black;padding: 1px 0 1px 6px">
                                                                    <?= $s->nama ?>
                                                                </td>
                                                                <td style="border: 1px solid black; text-align: center;">
                                                                    <?= $s->nama_kelas ?>
                                                                </td>
                                                                <td style="border: 1px solid black; text-align: center;">
                                                                    <?= $s->nama_ruang ?>
                                                                </td>
                                                                <td style="border: 1px solid black; text-align: center;">
                                                                    <?= $s->nama_sesi ?>
                                                                </td>
                                                            </tr>
                                                            <?php $no++; endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div style="page-break-after: always"></div>
                                    <?php endforeach; endif; ?>
                            </div>
                        </div>
                        <div class="overlay d-none" id="loading">
                            <!-- <div class="spinner-grow"></div> -->
                            <button class="btn btn-primary" type="button" disabled>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                                Loading...
                            </button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <?= $this->endSection() ?>

    <?= $this->section('scripts') ?>
    <script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
    <script>
        var logoKanan = "<?= isset($kop->logo_kiri) ? base_url() . $kop->logo_kiri : "" ?>";
        var logoKiri = "<?= isset($kop->logo_kanan) ? base_url() . $kop->logo_kanan : "" ?>";
        var sklh = "<?= isset($kop->sekolah) ? $kop->sekolah : "" ?>";

        $(document).ready(function () {
            ajaxcsrf();
            var opsiJenis = $("#jenis");

            //opsiJenis.prepend("<option value="" selected='selected'>Pilih Jenis Ujian</option>");

            opsiJenis.change(function () {
                $('.jenis-ujian').text($("#jenis option:selected").text().toUpperCase());
            });

            opsiJenis.select2({ width: '100%', theme: 'bootstrap-5' });

            $("#btn-print").click(function () {
                if (opsiJenis.val() === '') {
                    Swal.fire({
                        title: "ERROR",
                        text: "Isi semua pilihan terlebih dulu",
                        icon: "error"
                    })
                } else {
                    $('#print-preview').print(document.title);
                }
            });
        })
    </script>
    <?= $this->endSection() ?>