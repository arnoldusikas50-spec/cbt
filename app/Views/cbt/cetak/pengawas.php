<?php
function date_sort($a, $b)
{
    return strtotime($a ?? '') - strtotime($b ?? '');
}

$allowedDates = [];
?>
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
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0"><?= $subjudul ?></h3>
                <div class="d-flex align-items-center ms-auto gap-2">
                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="tooltip" title="Print"
                        onclick="print()">
                        <i class="bi bi-printer me-1"></i> Print/PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Export As Word"
                        onclick="exportWord()">
                        <i class="bi bi-file-word me-1"></i> Word
                    </button>
                    <!-- Excel button commented out as in original -->
                    <a href="<?= base_url('cbtcetak') ?>" class="btn btn-sm btn-danger">
                        <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3 col-6">
                        <label for="jenis" class="form-label">Jenis</label>
                        <?php
                        echo form_dropdown('jenis', $jenis, $jenis_selected, 'id="jenis" class="form-select"'); ?>
                    </div>
                    <div class="col-md-2 col-4">
                        <label for="filter" class="form-label">Filter</label>
                        <?php
                        echo form_dropdown('filter', $filter, $filter_selected, 'id="filter" class="form-select"'); ?>
                    </div>
                    <?php
                    $dnone = $filter_selected == '0' ? 'd-none' : '' ?>
                    <div class='col-md-2 col-4 <?= $dnone ?>' id="tgl-dari">
                        <label for="dari" class="form-label">Dari</label>
                        <input type='text' id="dari" name='dari' value="<?= $dari_selected ?>" class='tgl form-control'
                            autocomplete='off' />
                    </div>
                    <div class='col-md-2 col-4 <?= $dnone ?>' id="tgl-sampai">
                        <label for="sampai" class="form-label">Sampai</label>
                        <input type='text' id="sampai" name='sampai' class='tgl form-control'
                            value="<?= $sampai_selected ?>" autocomplete='off' />
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center bg-light border rounded py-4">
                <div id="print-preview">
                    <?php
                    $none = count($ruang) > 0 ? '' : 'd-none';
                    if (count($ruang) > 0):
                        //$list_ruang = array_keys($jadwals_ruang);
                        foreach ($ruang as $rng): ?>
                            <div style="background: white; width: 210mm; min-height: 297mm; padding: 1mm;white-space: nowrap;"
                                class="border my-shadow m-4 p-5">
                                <table id="table-header-print" style="width: 100%; border: 0;">
                                    <tr>
                                        <td style="width:15%;">
                                            <img alt="logo kiri" id="prev-logo-kanan-print" width="85" height="85"
                                                src="<?= isset($setting->logo_kiri) ? base_url() . $setting->logo_kiri : '' ?>"
                                                style="width:85px; height:85px; margin: 6px;">
                                        </td>
                                        <td style="width:70%; text-align: center;">
                                            <div style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 14pt">
                                                <?= $setting->sekolah ?>
                                            </div>
                                            <div style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 16pt">
                                                <b>DAFTAR PENGAWAS</b>
                                            </div>
                                            <div style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 13pt">
                                                <?= isset($jenis_ujian) ? strtoupper(($jenis_ujian->nama_jenis ?? '') . ' (' . ($jenis_ujian->kode_jenis ?? '') . ')') : '' ?>
                                            </div>
                                            <div style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 12pt">
                                                Tahun Pelajaran: <?= $tp_active->tahun ?? '-' ?></div>
                                        </td>
                                        <td style="width:15%;">
                                            <img alt="logo kanan" id="prev-logo-kiri-print"
                                                src="<?= isset($setting->logo_kanan) ? base_url() . $setting->logo_kanan : '' ?>"
                                                width="85" height="85"
                                                style="width:85px; height:85px; margin: 6px; border-style: none">
                                        </td>
                                    </tr>
                                </table>
                                <hr style="border: 1px solid; margin-bottom: 6px; margin-top: 2px">
                                <p style="font-size: large; font-weight: bold"><?= $rng->nama_ruang ?></p>
                                <table class="tbl-pengawas" style="width: 100%; border-collapse: collapse">
                                    <thead>
                                        <tr style="font-family: 'Times New Roman'; font-size: 12pt">
                                            <th style="border: 1px solid black; height: 40px; text-align: center;">Hari
                                                & Tanggal
                                            </th>
                                            <th style="border: 1px solid black; height: 40px; text-align: center;">
                                                Sesi
                                            </th>
                                            <th style="border: 1px solid black; height: 40px; text-align: center;">
                                                Jam Ke
                                            </th>
                                            <th style="border: 1px solid black; height: 40px; text-align: center;">
                                                Mata Pelajaran
                                            </th>
                                            <th style="border: 1px solid black; height: 40px; text-align: center;">
                                                Pengawas
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $byTanggal = [];
                                        foreach ($rng->sesi as $sesi) {
                                            foreach ($sesi->jadwals as $jad) {
                                                $tanggal = $jad->tgl_mulai ?? '';
                                                $byTanggal[$tanggal][$sesi->sesi_id]['sesi'] = $sesi;

                                                $pengawas_jadwal = $pengawas[$jad->id_jadwal][$rng->ruang_id][$sesi->sesi_id] ?? (object) [];
                                                $guru = $pengawas_jadwal->id_guru ?? '';

                                                $nama_guru = explode(',', $guru);
                                                $nama_pengawas = [];
                                                foreach ($nama_guru as $ng) {
                                                    if ($ng != '') {
                                                        $nama_pengawas[] = $gurus[$ng] ?? '';
                                                    }
                                                }
                                                $jad->nama_pengawas = $nama_pengawas;
                                                $jad->jam_ke = $pengawas_jadwal->jam_ke ?? '1';

                                                $byTanggal[$tanggal][$sesi->sesi_id]['jadwals'][$jad->id_jadwal] = $jad;
                                            }
                                        }

                                        foreach ($byTanggal as $tanggal => $listSesi):
                                            $rowspanTanggal = array_sum(array_map(function ($s) {
                                                return count($s['jadwals']);
                                            }, $listSesi));
                                            $firstTanggal = true;

                                            foreach ($listSesi as $sesi_id => $dataSesi):
                                                $sesi = $dataSesi['sesi'];
                                                $jadwals = $dataSesi['jadwals'];

                                                usort($jadwals, function ($a, $b) {
                                                    return ($a->jam_ke ?? 0) <=> ($b->jam_ke ?? 0);
                                                });

                                                $rowspanSesi = count($jadwals);
                                                $firstSesi = true;

                                                foreach ($jadwals as $jdw):
                                                    ?>
                                                    <tr style="font-family: 'Times New Roman'; font-size: 11pt">
                                                        <?php if ($firstTanggal): ?>
                                                            <td rowspan="<?= $rowspanTanggal ?>"
                                                                style="border: 1px solid black; text-align: center;">
                                                                <?= str_replace(',', '<br>', singkat_tanggal(date('D,d M Y', strtotime($tanggal)))) ?>
                                                            </td>
                                                        <?php endif; ?>

                                                        <?php if ($firstSesi): ?>
                                                            <td rowspan="<?= $rowspanSesi ?>"
                                                                style="border: 1px solid black; text-align: center;">
                                                                <?= $sesi->nama_sesi ?>
                                                            </td>
                                                        <?php endif; ?>

                                                        <td style="border: 1px solid black;text-align: center;"><?= $jdw->jam_ke ?>
                                                        </td>
                                                        <td style="border: 1px solid black; padding-left: 4px">
                                                            <?= $jdw->nama_mapel ?>
                                                        </td>

                                                        <td style="border: 1px solid black; text-align: center;">
                                                            <?= implode('<br>', $jdw->nama_pengawas ?? []) ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $firstTanggal = false;
                                                    $firstSesi = false;
                                                endforeach;
                                            endforeach;
                                        endforeach;
                                        ?>
                                    </tbody>
                                </table>
                                <br>
                                <div id="konten-copy" class="d-none"></div>
                            </div>
                            <div style="page-break-after: always"></div>
                        <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="overlay d-none" id="loading">
                <div class="spinner-grow"></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
<script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/jquery.wordexport.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/tableToExcel.js"></script>
<script>
    var docTitle = "<?= $judul ?>" + " <?= isset($jenis[$jenis_selected]) ? $jenis[$jenis_selected] : '' ?>";

    $(document).ready(function () {
        // Init Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        ajaxcsrf();

        var allowed = JSON.parse('<?= json_encode($allowedDates ?? []) ?>');
        //console.log(allowed);
        $('.tgl').datetimepicker({
            icons:
            {
                next: 'bi bi-chevron-right',
                previous: 'bi bi-chevron-left'
            },
            timepicker: false,
            scrollInput: false,
            scrollMonth: false,
            format: 'Y-m-d',
            //disabledWeekDays: [0],
            //allowDates: allowed,
            formatDate: 'Y-m-d',
            widgetPositioning: {
                horizontal: 'left',
                vertical: 'bottom'
            }
        });

        var opsiJenis = $("#jenis");
        var opsiFilter = $("#filter");
        var opsiDari = $("#dari");
        var opsiSampai = $("#sampai");

        opsiFilter.change(function () {
            if ($(this).val() == '0') {
                $('#tgl-dari').addClass('d-none');
                $('#tgl-sampai').addClass('d-none');
                var jenis = opsiJenis.val();
                var url = base_url + 'cbtcetak/pengawas?jenis=' + jenis + '&filter=0';
                if (jenis != "") {
                    $('#loading').removeClass('d-none');
                    window.location.href = url;
                }
            } else {
                $('#tgl-dari').removeClass('d-none');
                $('#tgl-sampai').removeClass('d-none');
            }
        });

        var old = "<?= $jenis_selected ?>";
        opsiJenis.change(function () {
            //var jj = $(this).val();
            //if (jj != "" && jj !== old) {
            getAllJadwal();
            //window.location.href = base_url + 'cbtalokasi?jenis=' + jj + '&level=' + opsiLevel.val();
            //}
        });

        var dariold = "<?= $dari_selected ?>";
        opsiDari.change(function () {
            var dari = $(this).val();
            if (dari != "" && dari !== dariold) {
                getAllJadwal();
            }
        });

        var sampaiold = "<?= $sampai_selected ?>";
        opsiSampai.change(function () {
            var sampai = $(this).val();
            if (sampai != "" && sampai !== sampaiold) {
                getAllJadwal();
            }
        });

        opsiJenis.select2({ width: '100%', theme: 'bootstrap-5' });

        function getAllJadwal() {
            var dari = opsiDari.val();
            var sampai = opsiSampai.val();
            var jenis = opsiJenis.val();
            var fil = opsiFilter.val();

            var tglKosong = fil == '1' && (dari == "" || sampai == "");
            var url = base_url + 'cbtcetak/pengawas?jenis=' + jenis + '&filter=' + opsiFilter.val() + '&dari=' + dari + '&sampai=' + sampai;
            if (jenis != "" && !tglKosong) {
                $('#loading').removeClass('d-none');
                window.location.href = url;
            }
        }

        $('#selector button').click(function () {
            $(this).addClass('active').siblings().addClass('btn-outline-primary').removeClass('active btn-primary');

            if (!$('#by-kelas').is(':hidden')) {
                $('#by-kelas').addClass('d-none');
                $('#by-ruang').removeClass('d-none');
            } else {
                $('#by-kelas').removeClass('d-none');
                $('#by-ruang').addClass('d-none');
            }
        });
    });

    function print() {
        var title = document.title;
        document.title = docTitle;
        $('#print-preview').print(docTitle);
        document.title = title;
    }

    function exportWord() {
        var contentDocument = $('#print-preview').convertToHtmlFile(docTitle, '');
        var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
        //console.log('css', content);
        var converted = htmlDocx.asBlob(content, {
            //orientation: 'landscape',
            size: 'A4',
            margins: { top: 700, bottom: 700, left: 1000, right: 1000 }
        });
        saveAs(converted, docTitle + '.docx');
    }

    function exportExcel() {
        /*
        var title = $('#jdl').html();
        var trsAtas = $('table#atas tbody').html();
        var trsHead = $('table#log-nilai thead').html();
        var trsBody = $('table#log-nilai tbody').html();
        var copy = '<table id="excel" style="font-size: 11pt;" data-cols-width="'+colWidth+'"><tbody>' +
            '<tr>' +
            '<td colspan="'+ (numCol+9) +'" data-a-v="middle" data-a-h="center" data-f-bold="true">' + title + '</td>' +
            '</tr>' +
            trsAtas +
            trsHead +
            trsBody +
            '<tr>' +
            '<td colspan="'+ (numCol+9) +'" data-a-v="middle"">' + catatan + '</td>' +
            '</tr>' +
            '</tbody>';
            */
        $('#konten-copy').html(copy);


        var table = document.querySelector("#excel");
        TableToExcel.convert(table, {
            name: docTitle + '.xlsx',
            sheet: {
                name: "Sheet 1"
            }
        });
    }

</script>
<?= $this->endSection() ?>