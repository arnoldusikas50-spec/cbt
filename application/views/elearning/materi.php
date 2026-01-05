<?php
/**

 * Date: 07/08/20
 * Time: 22:29
 */

$urlJenis = $jenis == "1" ? "materi" : "tugas";
$total = count($materi);
$all_materi = [];
$curr_materi = [];
foreach ($materi as $k => $m) {
    if ($m->smt == $smt_active->smt && $m->tahun == $tp_active->tahun) {
        $curr_materi[] = $m;
        //unset($materi[$k]);
    } else {
        $all_materi[] = $m;
        //unset($materi[$k]);
    }
}

$tglAllMateri = [];
?>

<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $judul ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php
            $days = [0, 1, 2, 3, 4, 5, 6];
            $disabledDay = [];
            //echo '<pre>';
            //var_dump($jadwal_mapel);
            //echo '</pre>';
            $latId = 0;
            foreach ($jadwal_mapel as $kmpl => $vmpl) {
                $hr = $vmpl->id_hari == 7 ? 0 : $vmpl->id_hari;
                $disabledDay[$vmpl->id_mapel][$vmpl->id_kelas] = $days;
                unset($disabledDay[$vmpl->id_mapel][$vmpl->id_kelas][$hr]);
            }
            ?>
            <div class="card card-default my-shadow mb-4 <?= count($jadwal_mapel) > 0 ? '' : 'd-none' ?>">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools ms-auto">
                        <a href="<?= base_url('elearning/' . $urlJenis . '?id=' . $id_guru) ?>" type="button" onclick=""
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </a>
                        <a href="<?= base_url('elearning/addmateri/' . $jenis) ?>" type="button"
                            class="btn btn-primary btn-sm ms-1">
                            <i class="bi bi-plus-circle"></i> Buat <?= $subjudul ?>
                        </a>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#openAll<?= $subjudul ?>"
                            class="btn btn-sm btn-success"><i class="bi bi-copy"></i> Copy <?= $subjudul ?>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-default-info align-content-center" role="alert">
                        Untuk mengcopy <?= $subjudul ?> dari tahun atau semester sebelumnya <b>ke
                            TP <?= $tp_active->tahun ?>
                            SMT <?= $smt_active->nama_smt ?></b>,
                        <ul>
                            <?php if ($is_admin): ?>
                                <li>
                                    Pilih Nama Guru
                                </li>
                            <?php endif; ?>
                            <li>
                                Klik <b><i class="bi bi-copy"></i> Copy <?= $subjudul ?></b>
                            </li>
                            <li>
                                Klik Aksi <b>Copy</b> untuk <?= $subjudul ?> yang akan dicopy
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <?php
                        $dnone = $is_admin ? '' : 'd-none';
                        $left = $is_admin ? 'text-end' : 'text-start';
                        $btnNone = count($curr_materi) > 0 ? '' : 'd-none';
                        ?>
                        <div class="col-md-6 mb-4 <?= $dnone ?>">
                            <label>Pilih Guru</label>
                            <?php echo form_dropdown(
                                'guru',
                                $gurus,
                                $id_guru,
                                'id="guru" class="select2 form-control" required'
                            ); ?>
                        </div>
                        <div class="col-6 <?= $left ?> <?= $btnNone ?>">
                            <button type="button" id="delete-all" data-count="<?= count($curr_materi) ?>"
                                class="btn btn-sm btn-danger mb-3"><i class="bi bi-trash"></i> Hapus
                                Semua <?= $subjudul ?>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        $arrIds = [];
                        if (count($curr_materi) > 0): ?>
                            <div class="col-12 table-responsive">
                                <table class="w-100 table table-bordered dataTable">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="text-center align-middle">No.</th>
                                            <th rowspan="2" class="text-center align-middle">Guru<br>Mapel</th>
                                            <th colspan="4" class="text-center align-middle"><?= $subjudul ?>
                                            </th>
                                            <th rowspan="2" class="text-center align-middle" style="width: 200px">Tanggal
                                            </th>
                                            <th rowspan="2" class="text-center align-middle" style="width: 110px">Status
                                            </th>
                                            <th rowspan="2" class="text-center align-middle" style="width: 100px">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center align-middle">Kode</th>
                                            <th class="text-center align-middle">Judul</th>
                                            <th colspan="2" class="text-center align-middle">Kelas</th>
                                        </tr>
                                    </thead>
                                    <?php $no = 1;
                                    foreach ($curr_materi as $value):
                                        $arr = $value->materi_kelas;
                                        $arrIds[] = $value->id_materi;
                                        $rows = max(count($arr), 1);
                                        $statusAktif = $value->status == '1';
                                        $stt = $statusAktif ? 'Aktif' : 'Non Aktif';
                                        $act = $statusAktif ? 'NonAktifkan' : 'Aktifkan';
                                        $btn_bg = $statusAktif ? 'btn-warning' : 'btn-success';
                                        $badge = $statusAktif ? 'bg-success' : 'bg-warning';
                                        for ($k = 0; $k < $rows; $k++):
                                            $kelas_id = $arr[$k] ?? null;
                                            ?>
                                            <tr>
                                                <?php if ($k === 0): ?>
                                                    <td rowspan="<?= $rows ?>" class="text-center"><?= $no ?></td>
                                                    <td rowspan="<?= $rows ?>"><?= $value->nama_guru ?><br><b><?= $value->kode ?></b>
                                                    </td>
                                                    <td rowspan="<?= $rows ?>"><?= $value->kode_materi ?></td>
                                                    <td rowspan="<?= $rows ?>"><?= $value->judul_materi ?></td>
                                                <?php endif; ?>

                                                <td class="text-center align-middle">
                                                    <b><?= $kelas_materi[$value->id_materi][$kelas_id] ?? '' ?></b>
                                                </td>
                                                <td class="align-middle text-center p-1">
                                                    <button class="btn btn-default" data-bs-toggle="modal"
                                                        data-bs-target="#openCalendar" data-jenis="<?= $jenis ?>"
                                                        data-materi="<?= $value->id_materi ?>" data-mapel="<?= $value->id_mapel ?>"
                                                        data-kelas="<?= $kelas_id ?>">
                                                        <i class="bi bi-calendar-check-o"></i>
                                                    </button>
                                                </td>
                                                <td class="p-0">
                                                    <table class="w-100 table-sm m-0">
                                                        <?php
                                                        if (!empty($jadwal_materi[$value->id_materi][$kelas_id])):
                                                            foreach ($jadwal_materi[$value->id_materi][$kelas_id] as $ind => $jtgl):
                                                                $tglAllMateri[$value->id_materi][$kelas_id][] = $jtgl->jadwal_materi;
                                                                $disabledBtn = $jtgl->jml_siswa == '0' ? '' : 'disabled';
                                                                $textColor = $jtgl->jml_siswa == '0' ? '' : ($k == 0 ? 'text-danger' : 'text-success');
                                                                $day = date('w', strtotime($jtgl->jadwal_materi ?? '')) ?: 7;
                                                                $jam_jadwal = array_filter($jadwal_mapel, function ($jadwal_m) use ($value, $kelas_id, $day) {
                                                                    return $jadwal_m->id_mapel == $value->id_mapel && $jadwal_m->id_kelas == $kelas_id && $jadwal_m->id_hari == $day;
                                                                });
                                                                $jam = count($jam_jadwal) > 0 ? array_values($jam_jadwal)[0] : null;
                                                                if ($jam):
                                                                    $ctgl = singkat_tanggal(date('D, d M Y', strtotime($jtgl->jadwal_materi ?? '')));
                                                                    $borderBtm = ($ind + 1) === count($jadwal_materi[$value->id_materi][$kelas_id]) ? ' border-bottom-0' : '';
                                                                    ?>
                                                                    <tr>
                                                                        <td
                                                                            class="py-0 px-1 border-left-0 border-right-0 border-top-0<?= $borderBtm ?>">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center w-100 m-1">
                                                                                <div class="text-sm <?= $textColor ?>">
                                                                                    <?= $ctgl ?><br><?= $jam->dari ?? "-" ?> ~
                                                                                    <?= $jam->sampai ?? "-" ?>
                                                                                </div>
                                                                                <div>
                                                                                    <button class="btn btn-sm" data-tgl="<?= $ctgl ?>"
                                                                                        data-id="<?= $jtgl->id_kjm ?>" onclick="hapusTgl(this)"
                                                                                        <?= $disabledBtn ?>>
                                                                                        <i class="bi bi-x-circle-o"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; endforeach; endif; ?>
                                                    </table>
                                                </td>

                                                <?php if ($k === 0): ?>
                                                    <td rowspan="<?= $rows ?>" class="text-center">
                                                        <div class="d-flex flex-column">
                                                            <div class="mb-1 badge <?= $badge ?> text-sm font-weight-light"><?= $stt ?>
                                                            </div>
                                                            <button class="btn btn-xs <?= $btn_bg ?>"
                                                                onclick="aktifkanMateri(<?= $value->id_materi ?>, <?= $value->status ?>)"><?= $act ?></button>
                                                        </div>
                                                    </td>
                                                    <td rowspan="<?= $rows ?>" class="text-center">
                                                        <a type="button" class="btn btn-sm btn-warning" title="Edit <?= $subjudul ?>"
                                                            href="<?= base_url('elearning/addmateri/' . $jenis . '/' . $value->id_materi) ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button onclick="hapus(<?= $value->id_materi ?>)" type="button"
                                                            class="btn btn-sm btn-danger" title="Hapus <?= $subjudul ?>">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endfor;
                                        $no++;
                                    endforeach; ?>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="col-12 p-0">
                                <?php if ($id_guru == ''): ?>
                                    <div class="alert alert-default-warning align-content-center" role="alert">
                                        Silahkan pilih guru terlebih dahulu
                                    </div>
                                <?php elseif ($no_mapel_guru == 0): ?>
                                    <div class="alert alert-default-warning align-content-center" role="alert">
                                        Belum ada <?= $subjudul ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-default-danger align-content-center" role="alert">
                                        <b>Jabatan guru belum diatur</b>,
                                        <?php
                                        $msg = $is_admin ? 'Silahkan atur jabatan guru terlebih dahulu' : 'Silakan hubungi Admin';
                                        echo $msg;
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="loading" class="overlay d-none">
                    <!-- <div class="spinner-grow"></div> -->
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                        Loading...
                    </button>
                </div>
            </div>
            <div class="card card-default my-shadow mb-4 <?= count($jadwal_mapel) == 0 ? '' : 'd-none' ?>">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                </div>
                <div class="card-body">
                    <div class="col-12 p-0">
                        <div class="alert alert-default-warning align-content-center" role="alert">
                            Jadwal Pelajaran kelas ini belum diatur
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <div class="modal fade" id="openAll<?= $subjudul ?>" tabindex="-1" role="dialog"
        aria-labelledby="open<?= $subjudul ?>Label" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header alert alert-default-success">
                    <h5 class="modal-title" id="open<?= $subjudul ?>Label">Semua <?= $subjudul ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <?php if (count($all_materi) > 0): ?>
                        <table id="tableEkstra"
                            class="w-100 table table-striped table-bordered table-hover table-head-fixed overflow-auto display nowrap dataTable"
                            style="max-height: 300px">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle p-0">No.</th>
                                    <th class="text-center align-middle">Judul</th>
                                    <th class="text-center align-middle">Mapel</th>
                                    <th class="text-center align-middle">Kelas</th>
                                    <th class="text-center align-middle">TP/SMT</th>
                                    <th class="text-center align-middle p-0" style="width: 100px"><span>Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($all_materi as $am):
                                    $arr = $am->materi_kelas;
                                    $skelas = '';
                                    for ($i = 0; $i < count($arr); $i++) {
                                        $skelas .= $kelas[$arr[$i]] ?? "-";
                                        if ($i < (count($arr) - 1)) {
                                            $skelas .= ', ';
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $no ?></td>
                                        <td><?= $am->judul_materi ?></td>
                                        <td><?= $am->kode ?></td>
                                        <td><?= $skelas ?></td>
                                        <td><?= $am->tahun . ' - ' . $am->nama_smt ?></td>
                                        <td>
                                            <button onclick="copy(<?= $am->id_materi ?>)" type="button"
                                                class="btn btn-sm btn-success"><i class="bi bi-copy"></i> Copy
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                    $no++;
                                endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-default-info align-content-center" role="alert">
                            Pilih Guru terlebih dahulu atau memang tidak ada <?= $subjudul ?> sebelumnya.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="openCalendar" tabindex="-1" role="dialog" aria-labelledby="openCalendarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="openCalendarLabel">Tanggal Mulai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="tgl"></div>
                    <hr>
                    <div id="empty" class="d-none mx-3">
                        <div class="alert alert-default-danger align-content-center" role="alert">
                            Jadwal Pelajaran belum diatur
                        </div>
                    </div>
                    <?= form_open('', array('id' => 'set-jadwal')) ?>
                    <div id="form-tgl" class="m-3">
                        <input type="hidden" name="id_kelas" value="">
                        <input type="hidden" name="jenis" value="">
                        <input type="hidden" name="id_materi" value="">
                        <input type="hidden" name="id_mapel" value="">
                        <input type="hidden" name="jadwal_materi" value="">
                        <div class="row">
                            <span class="col-3 text-bold">Mapel</span>
                            <div class="col-9" id="mapel">--</div>
                        </div>
                        <div class="row">
                            <span class="col-3 text-bold">Tanggal</span>
                            <div class="col-9" id="tanggal">--</div>
                        </div>
                        <div class="row">
                            <span class="col-3 text-bold">Waktu</span>
                            <div class="col-9" id="waktu">--</div>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-id="cancel" data-bs-dismiss="modal">Batal</button>
                    <!-- ikasmedia -->
                    <button type="button" class="btn btn-primary" id="btn-result" disabled data-id="ok"
                        onclick="simpanTgl(this)"><i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?= form_open('', array('id' => 'up')) ?>
    <?= form_close() ?>

    <script src="<?= base_url() ?>/assets/plugins/pignose/js/pignose.calendar.full.js"></script>
    <script>
        var jmlGuru = <?= count($gurus) ?>;
        var idGuru = '<?= $id_guru ?>';
        var subjudul = '<?= $subjudul ?>';
        var urlJenis = '<?= $jenis == "1" ? "materi" : "tugas" ?>';
        var jenis = '<?= $jenis ?>';

        const allTgl = JSON.parse('<?= json_encode($tglAllMateri) ?>');
        const tp = JSON.parse('<?= json_encode($tp_active) ?>');
        const smt = JSON.parse('<?= json_encode($smt_active) ?>');
        const jadwalMapel = JSON.parse('<?= json_encode($jadwal_mapel) ?>');
        const disableDays = JSON.parse('<?= json_encode($disabledDay) ?>');
        const settingKbm = JSON.parse('<?= json_encode($setting_kbm) ?>');

        const weekday = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
        let hariMapel = {}

        $(document).ready(function () {
            ajaxcsrf();
            // cari hari
            for (const mpl of jadwalMapel) {
                if (!hariMapel[mpl.id_mapel]) hariMapel[mpl.id_mapel] = {}
                if (!hariMapel[mpl.id_mapel][mpl.id_kelas]) hariMapel[mpl.id_mapel][mpl.id_kelas] = []
                hariMapel[mpl.id_mapel][mpl.id_kelas].push(Number(mpl.id_hari))
            }

            function getMateriGuru() {
                window.location.href = base_url + 'elearning/' + urlJenis + '?id=' + idGuru;
            }
            $('#guru').select2({ width: '100%', });
            $('#guru option[value="0"]').attr("disabled", "disabled");

            $('#guru').on('change', function () {
                idGuru = $(this).val();
                getMateriGuru();
            });

            $('#previewModal').on('show.bs.modal', function (e) {
                var src = $(e.relatedTarget).data('src');
                var size = $(e.relatedTarget).data('size');
                var type = $(e.relatedTarget).data('type');

                $('#download').attr('href', src).attr('download', 'file_materi.' + type.split('/')[1]);

                $("div").remove(".prev");
                if (type.match('image')) {
                    $('#media-preview').append('<div class="prev"><img src="' + src + '" alt=""></div>');
                } else if (type.match('video')) {
                    $('#media-preview').append('<div class="prev"><video src="' + src + '" controls="controls" preload="metadata" style="width: 100%; height: auto;"></div>');
                }
            });

            $('#delete-all').on('click', function (e) {
                var ids = <?= json_encode($arrIds) ?>;
                let formData = new FormData($('#up')[0]);
                formData.append('id_materi', ids.join(','));

                var count = $(this).data('count');
                if (count > 0) {
                    Swal.fire({
                        title: "Hapus Semua " + subjudul + "?",
                        html: count + " " + subjudul + " akan dihapus<br>Pastikan semua siswa yang mengerjakan sudah mendapat nilai",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Hapus Semua"
                    }).then(result => {
                        if (result.value) {
                            $.ajax({
                                url: base_url + 'elearning/delmateri',
                                type: "POST",
                                processData: false,
                                contentType: false,
                                data: formData,
                                success: function (respon) {
                                    if (respon) {
                                        Swal.fire({
                                            title: "Berhasil",
                                            text: count + " " + subjudul + " berhasil dihapus",
                                            icon: "success"
                                        }).then(result => {
                                            if (result.value) {
                                                window.location.href = base_url + 'elearning/' + urlJenis + '?id=' + idGuru;
                                            }
                                        })
                                    } else {
                                        Swal.fire({
                                            title: "Gagal",
                                            text: "Tidak bisa menghapus " + urlJenis,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function () {
                                    Swal.fire({
                                        title: "Gagal",
                                        text: "Ada data yang sedang digunakan",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        title: subjudul + " Kosong",
                        text: "Tidak ada materi yang bisa dihapus",
                        icon: "info"
                    });
                }
            });

            $('#openCalendar').on('show.bs.modal', function (e) {
                const idJenis = $(e.relatedTarget).data('jenis');
                const idMateri = $(e.relatedTarget).data('materi');
                const idMapel = $(e.relatedTarget).data('mapel');
                const idKelas = $(e.relatedTarget).data('kelas');

                const jadwals = jadwalMapel.filter(function (jad) {
                    return jad.id_kelas == idKelas && jad.id_mapel == idMapel
                })

                $("input[name='id_kelas']").val(idKelas);
                $("input[name='id_mapel']").val(idMapel);
                $("input[name='id_materi']").val(idMateri);
                $("input[name='jenis']").val(idJenis);

                console.log('hariMapel', hariMapel[idMapel], idKelas)

                if (hariMapel[idMapel] === undefined || hariMapel[idMapel][idKelas] === undefined) {
                    $('#empty').removeClass('d-none');
                    $('#btn-result').attr('disabled', '');
                    $('#tgl').pignoseCalendar({
                        lang: 'id',
                        format: 'YYYY-MM-DD',
                        disabledWeekdays: [0, 1, 2, 3, 4, 5, 6],
                    });
                } else {
                    const libur = settingKbm.libur === '7' ? 0 : Number(settingKbm.libur)
                    const dis = [Number(libur)];
                    for (let i = 0; i < 7; i++) {
                        if (!hariMapel[idMapel][idKelas].includes(i + 1) && !dis.includes(i + 1)) dis.push(i + 1)
                    }
                    $('#empty').addClass('d-none');
                    $('.xdsoft_datetimepicker').removeClass('d-none');
                    $('#btn-result').removeAttr('disabled');

                    const terisi = (Array.isArray(allTgl) && allTgl.length === 0) || allTgl[idMateri] === undefined ? [] : allTgl[idMateri][idKelas]
                    $('#tgl').pignoseCalendar({
                        lang: 'id',
                        format: 'YYYY-MM-DD',
                        select: onSelectHandler,
                        disabledWeekdays: dis,
                        disabledDates: terisi
                    });
                }

                function onSelectHandler(date, context) {
                    const idHari = new Date(date[0]).getDay()
                    const idTgl = date[0].format('YYYY-MM-DD')
                    const tglView = date[0].format('DD MMM YYYY')
                    setValues(idHari === 0 ? 7 : idHari, idTgl, weekday[idHari] + ', ' + tglView)
                }

                function setValues(idHari, idTgl, tglView) {
                    const jadwal = jadwals.find(function (jad) {
                        return jad.id_hari == idHari
                    })
                    $('#mapel').text(jadwal?.nama_mapel || '--');
                    $('#tanggal').text(tglView);
                    $('#waktu').text(jadwal?.dari || '--' + ' ~ ' + jadwal?.sampai || '--');
                    console.log('waktu', jadwal?.dari, jadwal?.sampai)

                    $("input[name='jadwal_materi']").val(idTgl);
                }
            });

            $('#openCalendar').on('hidden', function () {
                $(this).data('modal', null);
            });
            $('#openCalendar').on('hide.bs.modal', function () {
                $('#tgl').html('');
                $('#tanggal').text('--');
                $('#waktu').text('--');
                $('#mapel').text('--');
            })
        });

        function aktifkanMateri(id, status) {
            if (status == '1') {
                Swal.fire({
                    title: "Anda yakin?",
                    text: subjudul + " akan dinonaktifkan!",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Nonaktifkan"
                }).then(result => {
                    if (result.value) {
                        aktifkan(id, status);
                    }
                })
            } else {
                aktifkan(id, status);
            }
        }

        function aktifkan(id, status) {
            $.ajax({
                url: base_url + 'elearning/aktifkanmateri',
                data: { id_materi: id, method: status },
                type: "POST",
                success: function (respon) {
                    location.reload();
                },
                error: function () {
                    Swal.fire({
                        title: "Gagal",
                        text: "Ada data yang sedang digunakan",
                        icon: "error"
                    });
                }
            });
        }

        function generateDummy() {
            $.ajax({
                url: base_url + 'kbm/generateMateri',
                type: "GET",
                success: function (respon) {
                    console.log('res', respon)
                },
                error: function () {
                    Swal.fire({
                        title: "Gagal",
                        text: "Ada data yang sedang digunakan",
                        icon: "error"
                    });
                }
            });
        }

        function hapus(id) {
            Swal.fire({
                title: "Anda yakin?",
                html: subjudul + " akan dihapus!<br>Pastikan semua siswa yang mengerjakan sudah mendapat nilai",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'elearning/delmateri',
                        data: { id_materi: id },
                        type: "POST",
                        success: function (respon) {
                            if (respon.status) {
                                Swal.fire({
                                    title: "Berhasil",
                                    text: subjudul + " berhasil dihapus",
                                    icon: "success"
                                }).then(result => {
                                    if (result.value) {
                                        window.location.href = base_url + 'elearning/' + urlJenis + '?id=' + idGuru;
                                    }
                                })
                            } else {
                                Swal.fire({
                                    title: "Gagal",
                                    text: "Tidak bisa menghapus",
                                    icon: "error"
                                });
                            }
                            //reload_ajax();
                        },
                        error: function () {
                            Swal.fire({
                                title: "Gagal",
                                text: "Ada data yang sedang digunakan",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

        function copy(id) {
            Swal.fire({
                title: "Copi " + subjudul + "?",
                text: subjudul + " ini akan dicopy",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Copy"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'elearning/copymateri/' + id + '/' + jenis,
                        type: "GET",
                        success: function (respon) {
                            if (respon) {
                                Swal.fire({
                                    title: "Berhasil",
                                    text: subjudul + " berhasil dicopy",
                                    icon: "success"
                                }).then(result => {
                                    if (result.value) {
                                        window.location.href = base_url + 'elearning/' + urlJenis + '?id=' + idGuru;
                                    }
                                })
                            } else {
                                Swal.fire({
                                    title: "Gagal",
                                    text: "Tidak bisa mengcopy materi",
                                    icon: "error"
                                });
                            }
                            //reload_ajax();
                        },
                        error: function () {
                            Swal.fire({
                                title: "Gagal",
                                text: "Ada data yang sedang digunakan",
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

        function simpanTgl(btn) {
            const tgl = $('#tanggal').text();
            if (tgl === '--') return

            let formData = new FormData($('#set-jadwal')[0]);
            formData.append('id_smt', smt.id_smt)
            formData.append('id_tp', tp.id_tp)
            $('#openCalendar').modal('hide').data('bs.modal', null);

            $('#loading').removeClass('d-none');
            $.ajax({
                url: base_url + "elearning/savejadwalmateri",
                type: "POST",
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    $('#loading').addClass('d-none');
                    if (data.success) {
                        Swal.fire({
                            title: "Sukses",
                            text: "Jadwal materi berhasil disimpan",
                            icon: "success",
                            showCancelButton: false,
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "ERROR",
                            text: data.message,
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    Swal.fire({
                        title: "ERROR",
                        text: "Jadwal materi gagal disimpan",
                        icon: "error",
                        showCancelButton: false,
                    });
                }
            });
        }

        function hapusTgl(btn) {
            Swal.fire({
                title: "Anda yakin?",
                html: "Jadwal pada tanggal <b>" + $(btn).data('tgl') + "</b> akan diahpus",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(result => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    $.ajax({
                        url: base_url + "elearning/deljadwalmateri/" + $(btn).data('id'),
                        type: "GET",
                        success: function (data) {
                            $('#loading').addClass('d-none');
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Jadwal materi berhasil dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Jadwal materi gagal dihapus",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            Swal.fire({
                                title: "ERROR",
                                text: "Jadwal materi gagal dihapus",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            });
        }

    </script>