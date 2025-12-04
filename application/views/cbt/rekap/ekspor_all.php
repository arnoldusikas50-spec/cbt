<?php

$totalRekap = isset($all_rekap) ? (int) $all_rekap : 0;
$tahunAktif = isset($tp_active->tahun) ? $tp_active->tahun : '-';
$semesterAktif = isset($smt_active->smt) ? $smt_active->smt : '-';

$countJenis = 0;
if (isset($jenis) && is_array($jenis)) {
    foreach ($jenis as $tahunJenis) {
        if (is_array($tahunJenis)) {
            foreach ($tahunJenis as $smtJenis) {
                if (is_array($smtJenis)) {
                    $countJenis += count($smtJenis);
                }
            }
        }
    }
}

$countKelas = 0;
if (isset($kelas) && is_array($kelas)) {
    foreach ($kelas as $tahunKelas) {
        if (is_array($tahunKelas)) {
            foreach ($tahunKelas as $smtKelas) {
                if (is_array($smtKelas)) {
                    foreach ($smtKelas as $jenisKelas) {
                        if (is_array($jenisKelas)) {
                            $countKelas += count($jenisKelas);
                        }
                    }
                }
            }
        }
    }
}

?>

<div class="content-wrapper bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <br>
                    <small><i class="alert-default-warning">untuk semua jadwal ujian/ulangan yang sudah direkap</i></small>
                    <a href="<?= base_url('cbtrekap') ?>" type="button" class="btn btn-sm btn-danger float-right">
                        <i class="fas fa-arrow-circle-left"></i><span
                            class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan Ekspor Rekap</h5>
                                <div class="text-muted">Tahun Pelajaran <?= $tahunAktif ?> · Semester <?= $semesterAktif ?></div>

                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Jadwal Direkap: <?= $totalRekap ?></span>
                                <span class="badge badge-success px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Variasi Penilaian: <?= $countJenis ?></span>
                                <span class="badge badge-info px-3 py-2 text-sm">Kelas Tersedia: <?= $countKelas ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Ekspor Rekap</h5>
                        <ul class="pl-3 mb-0">
                            <li>Pilih Tahun, Semester, jenis penilaian, dan kelas untuk memuat data rekap yang ingin diekspor.</li>
                            <li>Gunakan tombol <strong>Perbaikan Nilai</strong> bila perlu melakukan konversi (katrol) sebelum unduhan.</li>
                            <li>Export tersedia dalam format Excel maupun Word; pastikan data sudah tampil sebelum mengekspor.</li>
                        </ul>
                    </div>

                    <?php
                    if (isset($all_rekap)) :
                    if ($all_rekap == 0) : ?>
                        <?php if (!isset($tp_active) || !isset($smt_active)) : ?>
                            <div class="col-12">
                                <div class="alert alert-default-warning shadow align-content-center" role="alert">
                                    Tahun Pelajaran atau Semester belum di set
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-default-warning shadow align-content-center" role="alert">
                                    Belum ada nilai rekap
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-3 mb-2">
                                <label>Tahun</label>
                                <?php
                                echo form_dropdown(
                                    'tahun',
                                    $tahuns,
                                    isset($tp_active) ? $tp_active->tahun : '',
                                    'id="opsi-tahun" class="form-control"'
                                ); ?>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 mb-2">
                                <label>Semester</label>
                                <?php
                                echo form_dropdown(
                                    'smt',
                                    $semester,
                                    isset($smt_active) ? $smt_active->nama_smt : '',
                                    'id="opsi-semester" class="form-control"'
                                ); ?>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 mb-2">
                                <label for="jenis">Penilaian</label>
                                <select name="jenis" id="jenis" class="form-control"></select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3 mb-2" id="by-kelas">
                                <label for="kelas">Kelas</label>
                                <select name="kelas" id="kelas" class="form-control"></select>
                            </div>
                            <div class="col-12 col-md-6 mb-2" id="by-mapel">
                                <label for="opsi-mapel">Mapel</label>
                                <select name="mapel" id="opsi-mapel" class="form-control"></select>
                            </div>
                        </div>
                        <hr>
                        <div class="row d-none" id="info">
                            <div class="col-12 mb-3">
                                <div class="float-right">
                                    <button type="button" id="rollback"
                                            class="btn btn-warning align-text-bottom d-none">
                                        <i class="fa fa-undo ml-1 mr-1"></i> Nilai Asli
                                    </button>
                                    <button type="button" id="convert" class="btn btn-danger align-text-bottom"
                                            data-toggle="modal" data-target="#perbaikanModal">
                                        <i class="fa fa-star-half-alt ml-1 mr-1"></i> Perbaikan Nilai
                                    </button>
                                    <button type="button" id="download-excel" class="btn btn-success align-text-bottom"
                                            data-toggle="tooltip"
                                            title="Download Excel">
                                        <i class="fa fa-file-excel ml-1 mr-1"></i> Ekspor ke Excel
                                    </button>
                                    <button type="button" id="download-word" class="btn btn-primary align-text-bottom"
                                            data-toggle="tooltip"
                                            title="Download Word">
                                        <i class="fa fa-file-word ml-1 mr-1"></i> Ekspor ke Word
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="for-word" class="table-responsive">
                            <br>
                            <table class="table table-sm" id="preview" style="font-size: 11pt; width: 100%;">
                            </table>
                        </div>
                        <table class="table d-none" id="table-status" style="font-size: 11pt; width: 100%;"
                               data-cols-width="5,30,35,10">
                        </table>

                        <table class="d-none" id="keterangan">
                            <tbody>
                            <tr>
                                <td colspan="2">Keterangan:</td>
                            </tr>
                            <tr>
                                <td class="border border-secondary" style="width: 20px; background: #76e0ff"></td>
                                <td class="pl-1">KKM Terlampaui</td>
                            </tr>
                            <tr>
                                <td class="border border-secondary" style="width: 20px; background: #83ffb5"></td>
                                <td class="pl-1">KKM Tercapai</td>
                            </tr>
                            <tr>
                                <td class="border border-secondary" style="width: 20px; background: #f8f9fa"></td>
                                <td class="pl-1">Tidak ada nilai</td>
                            </tr>
                            <tr>
                                <td class="border border-secondary" style="width: 20px; background: #FFFFB7"></td>
                                <td class="pl-1">Remidi</td>
                            </tr>
                            </tbody></table>
                    <?php endif;
                    else: ?>
                        <div class="col-12">
                            <div class="alert alert-default-warning shadow align-content-center" role="alert">
                                Belum ada nilai rekap
                            </div>
                        </div>
                    <?php endif; ?>
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
    </section>
</div>
<div class="modal fade" id="perbaikanModal" tabindex="-1" role="dialog" aria-labelledby="perbaikanModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="perbaikanModalLabel">Perbaikan Nilai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Nilai Tertinggi</label>
                    <div class="col-md-8">
                        <input type="text" id="ya" class="form-control" name="ya" value="100"
                               placeholder="Nilai tertinggi yang diinginkan" required>
                        <small>diisi nilai puluhan maksimal 100, misal 80 sampai 100</small>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-4 col-form-label">Nilai Terrendah</label>
                    <div class="col-md-8">
                        <input type="text" id="yb" class="form-control" name="yb" value="60"
                               placeholder="Nilai terrendah yang diinginkan" required>
                        <small>diisi nilai puluhan dibawah nilai tertinggi, misal 60</small>
                    </div>
                </div>
                <div class="form-group row mt-4">
                    <label class="col-md-4 col-form-label">KKM</label>
                    <div class="col-md-8">
                        <input type="text" id="kkm" class="form-control" name="kkm" value="70" placeholder="KKM"
                               required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-convert"><i class="fa fa-arrow-right"></i>
                    Konversi
                </button>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'bulk')) ?>
<?= form_close(); ?>

<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
<script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/tableToExcel.js"></script>

<script>
    var newData = '';
    var oldData = '';
    var mplSelected = '0';
    var kodeMapel = 'Semua Mapel';

    var dataNilai;
    var nilai_max = 100;//nilai siswa terbesar
    var nilai_min = 0;//nilai siswa terkecil
    var hasil_max = 100;//batas nilai terbesar
    var hasil_min = 60;//batas nilai terkecil
    var kkm = 70;
    let isConvert = false;
    var orientation = 'potrait';

    var jeniss = <?= json_encode($jenis ?? (object)[]) ?>;
    var kelass = <?= json_encode($kelas ?? (object)[]) ?>;
    var titleDownload = 'REKAP NILAI ASLI';

    function refreshStatus(url) {
        $('#table-status').html('');
        $('#info').addClass('d-none');
        $('#loading').removeClass('d-none');

        setTimeout(function () {
            $.ajax({
                type: "GET",
                url: url,
                success: function (response) {
                    console.log('refresh', response);
                    $('#loading').addClass('d-none');
                    if (response.rekap_jadwal.length === 0) {
                        $('#opsi-mapel').html('');
                    } else {
                        dataNilai = response;
                        createPreview(false)
                    }
                }
            });
        }, 500);
    }

    function createPreview(convert) {
        isConvert = convert;
        if (convert) {
            titleDownload = 'REKAP NILAI KATROL';
            $('#rollback').removeClass('d-none');
            $('#convert').addClass('d-none');
        } else {
            titleDownload = 'REKAP NILAI ASLI';
            $('#convert').removeClass('d-none');
            $('#rollback').addClass('d-none');
        }
        var arrMapel = dataNilai.rekap_jadwal;
        arrMapel.sort((a, b) => a.id_jadwal - b.id_jadwal);
        arrMapel.sort((a, b) => new Date(a.tgl_mulai) - new Date(b.tgl_mulai));

        let mpl;
        let tglJadwalMulai = new Date()
        let tglJadwalSelesai = new Date()
        if (mplSelected === '0') {
            mpl = 'Semua Mata Pelajaran';
            for (const mapel of arrMapel) {
                if (tglJadwalMulai > new Date(mapel.tgl_mulai)) {
                    tglJadwalMulai = new Date(mapel.tgl_mulai)
                }
                if (tglJadwalSelesai < new Date(mapel.tgl_selesai)) {
                    tglJadwalSelesai = new Date(mapel.tgl_selesai)
                }
            }
        } else {
            const jadwalSelected = arrMapel.find(m => m.id_jadwal === mplSelected)
            mpl = jadwalSelected.nama_mapel
            if (jadwalSelected.bank_soal.soal_agama !== '-' && jadwalSelected.bank_soal.soal_agama !== '0') {
                mpl += ' ('+ jadwalSelected.bank_soal.soal_agama + ')';
            }
        }

        if (oldData !== newData) {
            oldData = newData;
            var opsis = '<option value="0" selected="selected">Semua Jadwal</option>';
            for (let i = 0; i < arrMapel.length; i++) {
                let agm = null
                if (arrMapel[i].bank_soal.soal_agama !== '-' && arrMapel[i].bank_soal.soal_agama !== '0') {
                    agm = arrMapel[i].bank_soal.soal_agama
                }

                var selected = mplSelected == arrMapel[i].id_jadwal ? 'selected' : '';
                opsis += '<option value="' + arrMapel[i].id_jadwal + '" ' + selected + '>'
                    + arrMapel[i].kode_mapel
                    + ' (' + arrMapel[i].kode_bank + ' | '
                    + ( agm ? agm + ' )' : 'Umum)')
                    + '</option>';
            }
            $('#opsi-mapel').html(opsis);

            function formatState (state) {
                if (!state.id) {
                    return state.text;
                }
                if (state.element.value === '0') {
                    return $(`<span>Semua Mata Pelajaran<span>`);
                } else {
                    const val = arrMapel.find(m => m.id_jadwal === state.element.value);
                    const mulai = new Date(val?.tgl_mulai).toLocaleDateString('id-ID', {
                        year: "numeric",
                        month: "short",
                        day: "2-digit",
                    })
                    const prop = mulai + ' | ' + val?.kode_bank + ' | ' + (val?.bank_soal.soal_agama !== '-' && val?.bank_soal.soal_agama !== '0' ? val?.bank_soal.soal_agama : 'Umum')
                    return $(
                        `<span>${val?.kode_mapel || '-'}<span><br /><small>${prop}</small>`
                    );
                }
            }

            $('#opsi-mapel').select2({
                width: '100%',
                theme: "bootstrap4",
                templateResult: formatState
            });
        }

        const mulai = tglJadwalMulai.toLocaleDateString('id-ID', {
            year: "numeric",
            month: "short",
            day: "2-digit",
        })
        let selesai = tglJadwalSelesai.toLocaleDateString('id-ID', {
            year: "numeric",
            month: "short",
            day: "2-digit",
        })
        const waktuJadwal = mulai === selesai ? mulai : mulai + ' ~ ' + selesai
        const pelaksanaan = mplSelected === '0' ? waktuJadwal : mulai

        var rows = mplSelected === '0' ? '3' : '1';
        const cols = mplSelected === '0' ? arrMapel.length : '1'
        $('.table-header').remove();


        var tinfo = '<table class="table-header"><tr>' +
            '     <td colspan="2" style="width: 120px">Jenis Ujian</td>' +
            '     <td colspan="5">: ' + $("#jenis option:selected").text() + '</td>' +
            ' </tr>' +
            ' <tr>' +
            '     <td colspan="2">Mata Pelajaran</td>' +
            '     <td colspan="5">: ' + mpl + '</td>' +
            ' </tr>' +
            ' <tr>' +
            '     <td colspan="2">Kelas</td>' +
            '     <td colspan="5">: ' + $("#kelas option:selected").text() + '</td>' +
            ' </tr>' +
            ' <tr>' +
            '     <td colspan="2">Pelaksanaan</td>' +
            '     <td colspan="5">: ' + pelaksanaan + '</td>' +
            ' </tr>' +
            ' <tr></tr></table>';
        $('#for-word').prepend(tinfo);

        var thead = '<tr>' +
            '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="width:40px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;background-color: lightgrey;">No.</th>' +
            '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;min-width: 220px">No. Peserta</th>' +
            '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;min-width: 270px"">Nama</th>';

        if (rows > 1) {
            thead += '<th colspan="' + cols + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">Nilai</th>' +
                '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">Jumlah</th>' +
                '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">Rata-rata</th>' +
                '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">Rank</th>' +
                '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">Ket.</th>' +
                '</tr>';
        } else {
            thead += '<th colspan="' + cols + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">Nilai</th>' +
                '<th rowspan="' + rows + '" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">Ket.</th>' +
                '</tr>';
        }

        dataNilai.siswa.sort((a, b) => a.siswa.nama.localeCompare(b.siswa.nama))
        if (mplSelected === '0') {
            let headerMapel = '';
            let headerBank = '';
            for (let m = 0; m < arrMapel.length; m++) {
                let agm = null
                if (arrMapel[m].bank_soal.soal_agama !== '-' && arrMapel[m].bank_soal.soal_agama !== '0') {
                    agm = '(' +arrMapel[m].bank_soal.soal_agama + ')'
                }
                headerMapel += '<th class="p-1 text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">'
                    + arrMapel[m].kode_mapel + (agm ? ' <br/>' + agm : '') +'</th>';
                headerBank += '<th class="p-1 text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="medium" data-f-bold="true" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;background-color: lightgrey;">'
                    + arrMapel[m].kode_bank +'</th>';
            }
            thead += '<tr class="head">'+headerMapel+'</tr>';
            thead += '<tr class="head">'+headerBank+'</tr>';
        }

        var tbody = '';
        var nos = 1;
        //console.log('allSiswa', dataNilai.siswa)
        $.each(dataNilai.siswa, function (ind, v) {
            if (mplSelected === '0') {
                tbody += '<tr>' +
                    '<td data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse; text-align: center;">' + nos + '</td>' +
                    '<td data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse; text-align: center;">' + v?.peserta?.nomor_peserta + '</td>' +
                    '<td data-a-v="middle" data-a-h="left" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse;">' + v.siswa.nama + '</td>';

                let jumlahNilai = 0;
                let mapelKecuali = 0;
                for (const rekap of arrMapel) {
                    const nilaiSiswa = rekap.rekapsiswa.find(rs => rs.id_jadwal === rekap.id_jadwal && rs.id_siswa === v.siswa_id)

                    const pel = nilaiSiswa?.pelanggaran
                    const skorAsli = nilaiSiswa?.nilai_akhir?.skor_total || 0;
                    const totalMinus = rekap?.jadwal?.pelanggaran === '1' ? (pel?.t_total || 0) : 0
                    let skor1 = skorAsli - totalMinus;
                    if (convert) {
                        if (skor1 > 0)
                            skor1 = (((hasil_max - hasil_min) / 100) * decimalFixed(skor1)) + hasil_min;
                    } else {
                        if (decimalFixed(skor1) > nilai_max) {
                            nilai_max = decimalFixed(skor1);
                        }
                        if (decimalFixed(skor1) < nilai_max) {
                            nilai_min = decimalFixed(skor1);
                        }
                    }

                    jumlahNilai += decimalFixed(skor1 ?? 0);
                    let nilaiJadwal1 = removeZeroDecimal(decimalFixed(skor1));
                    var bgFill1 = nilaiJadwal1 == kkm ? '83ffb5' : (nilaiJadwal1 > kkm ? 'a1e4f8' : 'FFFFB7');
                    if (rekap.bank_soal.soal_agama !== '-' && rekap.bank_soal.soal_agama !== '0'
                    && rekap.bank_soal.soal_agama !== v.siswa.agama) {
                        bgFill1 = 'f8f9fa';
                        nilaiJadwal1 = '-'
                        mapelKecuali ++;
                    }
                    tbody += '<td data-a-v="middle" data-t="n" data-a-h="center" data-b-a-s="thin" class="align-middle" data-fill-color="'+bgFill1+'" style="border: 1px solid black;border-collapse: collapse; text-align: center;background-color: #'+bgFill1+';">' + nilaiJadwal1 + '</td>';
                }
                const mplBagi = rows > 1 ? arrMapel.length - mapelKecuali : 1
                let rata2 = decimalFixed(jumlahNilai / mplBagi);
                let ketNilai = rata2 == kkm ? 'Tercapai' : (rata2 > kkm ? 'Terlampaui' : 'Remidi');
                let bgFill = rata2 == kkm ? '83ffb5' : (rata2 > kkm ? 'a1e4f8' : 'FFFFB7');
                if (rows > 1) {
                    tbody += '<td class="align-middle" data-t="n" data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse;text-align: center">' + removeZeroDecimal(decimalFixed(jumlahNilai)) + '</td>';
                    tbody += '<td class="total align-middle" data-t="n" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-fill-color="'+bgFill+'" style="border: 1px solid black;border-collapse: collapse;text-align: center;background-color: #'+bgFill+';"><b>' + rata2 + '</b></td>';
                    tbody += '<td class="align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse;text-align: center"></td>';
                }
                tbody += '<td class="align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-fill-color="'+bgFill+'" style="border: 1px solid black;border-collapse: collapse;text-align: center;background-color: #'+bgFill+';">' + ketNilai + '</td>';
                tbody += '</tr>';
                nos += 1;
            } else {
                let jumlahNilai = 0;
                let mapelKecuali = 0;

                const rekap = arrMapel.find(j => j.id_jadwal === mplSelected)
                if (rekap.bank_soal.soal_agama !== '-' && rekap.bank_soal.soal_agama !== '0' && rekap.bank_soal.soal_agama !== v.siswa.agama) {
                    return
                }

                const nilaiSiswa = rekap?.rekapsiswa.find(rs => rs.id_jadwal === mplSelected && rs.id_siswa === v.siswa_id)
                console.log('nilai', nilaiSiswa)
                let skor2 = nilaiSiswa?.nilai_akhir?.skor_total;
                if (convert) {
                    if (skor2 > 0) {
                        skor2 = (((hasil_max - hasil_min) / 100) * decimalFixed(skor2)) + hasil_min;
                    } else {
                        skor2 = 0;
                    }
                } else {
                    if (decimalFixed(skor2) > nilai_max) {
                        nilai_max = decimalFixed(skor2);
                    }
                    if (decimalFixed(skor2) < nilai_max) {
                        nilai_min = decimalFixed(skor2);
                    }
                }

                jumlahNilai += decimalFixed(skor2 ?? 0);
                let nilaiJadwal2 = removeZeroDecimal(decimalFixed(skor2));
                let bgFill2 = nilaiJadwal2 == kkm ? '83ffb5' : (nilaiJadwal2 > kkm ? 'a1e4f8' : 'FFFFB7');
                if (rekap.bank_soal.soal_agama !== '-' && rekap.bank_soal.soal_agama !== '0'
                    && rekap.bank_soal.soal_agama !== v.siswa.agama) {
                    bgFill2 = 'f8f9fa';
                    nilaiJadwal2 = '-'
                    mapelKecuali ++;
                }
                //if (!(rekap.bank_soal.soal_agama !== '-' && rekap.bank_soal.soal_agama !== '0' && rekap.bank_soal.soal_agama !== nilaiSiswa?.siswa.agama)) {}
                tbody += '<tr>' +
                    '<td data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse; text-align: center;">' + nos + '</td>' +
                    '<td data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse; text-align: center;">' + v?.peserta?.nomor_peserta + '</td>' +
                    '<td data-a-v="middle" data-a-h="left" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse;">' + v.siswa.nama + '</td>';
                tbody += '<td data-a-v="middle" data-a-h="center" data-b-a-s="thin" class="align-middle" data-fill-color="'+bgFill2+'" style="border: 1px solid black;border-collapse: collapse; text-align: center;background-color: #'+bgFill2+';">' + nilaiJadwal2 + '</td>';
                const mplBagi = rows > 1 ? arrMapel.length - mapelKecuali : 1
                let rata2 = decimalFixed(jumlahNilai / mplBagi);
                let ketNilai = rata2 == kkm ? 'Tercapai' : (rata2 > kkm ? 'Terlampaui' : 'Remidi');
                let bgFill = rata2 == kkm ? '83ffb5' : (rata2 > kkm ? 'a1e4f8' : 'FFFFB7');
                if (rows > 1) {
                    tbody += '<td class="align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse;text-align: center">' + removeZeroDecimal(decimalFixed(jumlahNilai)) + '</td>';
                    tbody += '<td class="total align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-fill-color="'+bgFill+'" style="border: 1px solid black;border-collapse: collapse;text-align: center;background-color: #'+bgFill+';"><b>' + rata2 + '</b></td>';
                    tbody += '<td class="align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="border: 1px solid black;border-collapse: collapse;text-align: center"></td>';
                }
                tbody += '<td class="align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-fill-color="'+bgFill+'" style="border: 1px solid black;border-collapse: collapse;text-align: center;background-color: #'+bgFill+';">' + ketNilai + '</td>';
                tbody += '</tr>';
                nos += 1;
            }
        });

        $('#preview').html('<thead>' + thead + '</thead>' + tbody);

        var texcel = ' <tr>' +
            '     <td colspan="2" style="width: 120px">Jenis Ujian</td>' +
            '     <td colspan="5">:' + $("#jenis option:selected").text() + '</td>' +
            ' </tr>' +
            ' <tr>' +
            '     <td colspan="2">Mata Pelajaran</td>' +
            '     <td colspan="5">:' + mpl + '</td>' +
            ' </tr>' +
            ' <tr>' +
            '     <td colspan="2">Kelas</td>' +
            '     <td colspan="5">:' + $("#kelas option:selected").text() + '</td>' +
            ' </tr>' +
            ' <tr>' +
            '     <td colspan="2">Pelaksanaan</td>' +
            '     <td colspan="5">: ' + pelaksanaan + '</td>' +
            ' </tr>' +
            ' <tr></tr>';
        texcel += thead + tbody;

        $('#table-status').html(texcel);
        $('#info').removeClass('d-none');
        $('#loading').addClass('d-none');
        $('#keterangan').removeClass('d-none');

        $(".total")
            .map(function () {
                return $(this).text()
            })
            .get()
            .sort(function (a, b) {
                return a - b
            })
            .reduce(function (a, b) {
                if (b != a[0]) a.unshift(b);
                return a
            }, [])
            .forEach((v, i) => {
                $('.total').filter(function () {
                    return $(this).text() == v;
                }).next().text(i + 1);
            });

        orientation = rows > 1 ? 'landscape' : 'potrait';
    }

    $(document).ready(function () {
        ajaxcsrf();

        var opsiKelas = $("#kelas");
        var opsiJenis = $('#jenis');
        var opsiTahun = $('#opsi-tahun');
        var opsiSmt = $('#opsi-semester');
        var opsiMapel = $('#opsi-mapel');

        opsiKelas.prepend("<option value='' selected='selected'>Pilih Kelas</option>");
        opsiJenis.prepend("<option value='' selected='selected'>Pilih Jenis</option>");

        function reload(kls, jenis, thn, smt, mpl) {
            var empty = jenis === '' || kls === '' || thn === '' || smt === '' || jenis == null || kls == null || thn == null || smt == null;
            var dataPost = 'kelas=' + kls + '&jenis=' + jenis + '&tahun=' + thn + '&smt=' + smt + '&mapel=' + mpl;
            newData = 'kelas=' + kls + '&jenis=' + jenis + '&tahun=' + thn + '&smt=' + smt;

            console.log(dataPost);
            if (!empty) {
                refreshStatus(base_url + "cbtrekap/getnilaikelas?" + dataPost);
            } else {
                console.log('empty')
            }
        }

        function changeJenis(thn, smt) {
            opsiJenis.html('');
            changeKelas(thn, smt, null);
            if (jeniss[thn] != null && jeniss[thn][smt] != null) {
                opsiJenis.append("<option value='' selected='selected'>Pilih Jenis</option>");
                $.each(jeniss[thn][smt], function (k, j) {
                    opsiJenis.append("<option value='" + k + "'>" + j + "</option>")
                });
            } else {
                opsiJenis.append("<option value='' selected='selected'>Tidak ada penilaian</option>");
            }
        }

        function changeKelas(thn, smt, jenis) {
            opsiKelas.html('');
            if (jenis != null && kelass[thn][smt][jenis] != null) {
                opsiKelas.append("<option value='' selected='selected'>Pilih Kelas</option>");
                $.each(kelass[thn][smt][jenis], function (k, j) {
                    opsiKelas.append("<option value='" + k + "'>" + j + "</option>")
                });
            } else {
                opsiKelas.append("<option value='' selected='selected'>Tidak ada kelas</option>");
            }
        }

        opsiTahun.change(function () {
            $("#opsi-mapel select").val("0");
            changeJenis($(this).val(), opsiSmt.val())
        });

        opsiSmt.on('change', function () {
            $("#opsi-mapel select").val("0");
            changeJenis(opsiTahun.val(), $(this).val())
        });

        opsiJenis.on('change', function () {
            $("#opsi-mapel select").val("0");
            changeKelas(opsiTahun.val(), opsiSmt.val(), $(this).val())
        });

        opsiKelas.change(function () {
            $("#opsi-mapel select").val("0");
            reload($(this).val(), opsiJenis.val(), opsiTahun.val(), opsiSmt.val(), '0');
        });

        opsiMapel.on('change', function () {
            mplSelected = $(this).val();
            kodeMapel = $("#opsi-mapel option:selected").text();
            //reload(opsiKelas.val(), opsiJenis.val(), opsiTahun.val(), opsiSmt.val(), $(this).val());
            createPreview(isConvert)
        });

        opsiTahun.select2({width: '100%', theme: "bootstrap4",});
        opsiSmt.select2({width: '100%', theme: "bootstrap4",});
        opsiJenis.select2({width: '100%', theme: "bootstrap4",});
        opsiKelas.select2({width: '100%', theme: "bootstrap4",});
        opsiMapel.select2({width: '100%', theme: "bootstrap4",});

        $("#download-word").click(function (event) {
            event.preventDefault();
            var contentDocument = $('#for-word').convertToHtmlFile('detail', '');
            var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
            var converted = htmlDocx.asBlob(content, {
                orientation: orientation,
                size: 'A4',
                margins: {top: 700, bottom: 700, left: 1000, right: 1000}
            });
            saveAs(converted, `${titleDownload} ${$("#jenis option:selected").text()} ${kodeMapel} ` +
                `${$("#kelas option:selected").text()} ${$("#opsi-tahun option:selected").text()} smt ${$("#opsi-semester option:selected").text()}.docx`);
        });

        $("#download-excel").click(function (event) {
            var table = document.querySelector("#table-status");
            TableToExcel.convert(table, {
                name: `${titleDownload} ${$("#jenis option:selected").text()} ${kodeMapel} ${$("#kelas option:selected").text()} ${$("#opsi-tahun option:selected").text()} smt ${$("#opsi-semester option:selected").text()}.xlsx`,
                sheet: {
                    name: "Sheet 1"
                }
            });
        });

        $('#perbaikanModal').on('show.bs.modal', function (e) {
            $('#ya').val(hasil_max);
            $('#yb').val(hasil_min);
            $('#kkm').val(kkm);
        });

        $('#btn-convert').click(function (e) {
            hasil_max = parseInt($('#ya').val());
            hasil_min = parseInt($('#yb').val());
            //console.log(hasil_max, hasil_min);
            //console.log(nilai_max, nilai_min);
            kkm = parseInt($('#kkm').val());

            $('#perbaikanModal').modal('hide').data('bs.modal', null);
            $('#perbaikanModal').on('hidden', function () {
                $(this).data('modal', null);
            });
            $('#loading').removeClass('d-none');
            createPreview(true);
        });

        $('#rollback').click(function (e) {
            createPreview(false);
        });

        changeJenis(opsiTahun.val(), opsiSmt.val());
    });

    function decimalFixed(num) {
        if (isNaN(num)) return 0;
        return Math.round(num * 100) / 100
    }

    function removeZeroDecimal(num) {
        if (isNaN(num)) return '0';
        let dec = (num.toFixed(2))
        return dec.replace('.00', '')
    }

</script>
