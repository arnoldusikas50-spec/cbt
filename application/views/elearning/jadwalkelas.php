<?php


$arrHari = ['0' =>'Tidak ada', "7"=>"Minggu", "1"=>"Senin", "2"=>"Selasa", "3"=>"Rabu", "4"=>"Kamis", "5"=>"Jum'at", "6"=>"Sabtu"];

if (!is_array($setting_kbm->istirahat)) {
    $setting_kbm->istirahat = [];
}

?>
<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools">
                        <button type="button" onclick="reload()" class="btn btn-sm btn-warning">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <?php if ($is_admin) : ?>
                            <button id="copy-jadwal" class="btn btn-sm btn-success" disabled onclick="copyJadwal()">
                                <i class="fas fa-copy"></i> <span class="d-none d-sm-inline-block ml-1">Copy Jadwal</span>
                            </button>
                            <button id="edit-jadwal" class="btn btn-sm btn-primary" data-toggle="modal" disabled data-target="#createJadwalModal">
                                <i class="fas fa-cog nav-icon"></i> <span class="d-none d-sm-inline-block ml-1">Set Jadwal</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($kelas) === 0) : ?>
                    <div class="row">
                        <div class="col-12 p-0" id="kelas-empty">
                            <div class="alert alert-default-warning shadow align-content-center" role="alert">
                                Belum ada data kelas untuk Tahun Pelajaran <b><?= $tp_active->tahun ?></b>
                                Semester:
                                <b><?= $smt_active->smt ?></b>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-default-warning align-content-center" role="alert">
                            <ul class="m-0">
                                <?php if ($is_admin) : ?>
                                    <li><b>Copy Jadwal</b> akan menghapus semua jadwal yang ada dan menggantinya
                                        dengan jadwal hasil copy dari semester terdahulu.
                                    </li>
                                    <li><b>Set Jadwal</b> tidak menghapus jadwal yang sudah ada, hanya menyusun ulang jam
                                        pelajaran sesuai dengan jam yang ditentukan.
                                    </li>
                                <?php else: ?>
                                    <li>Jadwal Pelajaran diatur oleh ADMIN.</li>
                                    <li>Hubungi ADMIN jika jadwal masih kosong</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-2">
                                <div id="arr-kelas"></div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 alert alert-default-info align-content-center" id="alert" role="alert">
                                Silakan pilih hari
                            </div>
                        </div>
                        <div class="d-none">
                            <table class="my-timetable w-100 border-bottom">
                                <thead>
                                <tr class="alert-primary">
                                    <th colspan="2" class="text-center frozen-header text-white py-2" style="width: 80px; min-width: 80px; max-width: 80px;">JAM</th>
                                    <?php
                                    foreach ($kelas as $key => $value) :?>
                                        <th class="align-middle text-center" style="min-width: 100px;" data-kelas="<?=$key?>"><?=$value?></th>
                                    <?php endforeach;?>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div id="table-jadwal" class="d-none"></div>

                        <?php if ($is_admin) :?>
                            <div class="card alert-default-success d-none" id="reformat">
                        <div class="card-body">
                            <p>Jika jadwal harian di setiap kelas ada kolom tidak digunakan maka bisa dihapus dengan Reformat Jadwal</p>
                            <div class="float-right">
                                <button type="button" onclick="reformat()" class="btn btn-sm btn-primary">
                                    <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reformat Jadwal</span>
                                </button>
                            </div>
                        </div>
                    </div>
                        <?php endif; ?>
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

<div class="modal fade" id="createJadwalModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <?= form_open('setJadwal', array('id' => 'setjadwal')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Buat Jadwal Kelas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="inputs">
                <div class="form-group row" id="hari_libur">
                    <label for="hari_libur" class="col-md-4 col-form-label">Hari Libur *</label>
                    <div class="col-md-8">
                        <?php
                        echo form_dropdown(
                            'hari_libur',
                            $arrHari,
                            $setting_kbm->libur ?? '',
                            'class="form-control select2" required'
                        ); ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="jam_mulai" class="col-md-4 col-form-label align-self-center">Jam KBM *</label>
                    <div class="col-md-4 col-6">
                        Dari:
                        <input id="jam_mulai" type="text" name="jam_mulai" class="form-control form-control-sm jam-kbm"
                               value="" autocomplete="off"
                               placeholder="Jam Mulai" required>
                    </div>
                    <div class="col-md-4 col-6">
                        Sampai:
                        <input id="jam_selesai" type="text" name="jam_selesai" class="form-control form-control-sm jam-kbm"
                               value="" autocomplete="off"
                               placeholder="Jam Selesai" required>
                    </div>
                    <span class="col-md-4"></span>
                    <small class="col-md-8 alert-default-warning">Jam Mulai dibatasi jika ada jadwal pada jam pertama</small>
                </div>
                <div class="form-group row">
                    <label class="col-md-4">Format Jadwal</label>
                    <div class="col-md-8">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="format" id="formatted" class="custom-control-input">
                            <label for="formatted" class="custom-control-label" style="font-weight: normal; line-height: 1.2">Berlaku untuk semua kelas pada hari dan jam yang kosong. data yang sudah terisi tidak akan berubah</label>
                        </div>
                    </div>
                </div>
                <div id="format-jadwal" class="d-none">
                    <div class="form-group row">
                        <label for="upacara" class="col-md-4 col-form-label">Upacara Bendera</label>
                        <div class="col-md-4 col-4">
                            Hari:
                            <?php
                            echo form_dropdown(
                                'hari_upacara',
                                $arrHari,
                                $setting_kbm->upacara->id_hari ?? '',
                                'class="form-control select2"'
                            ); ?>
                        </div>
                        <div class="col-md-2 col-4">
                            Jam ke:
                            <select name="jam_upacara" class="form-control form-control-sm select2">
                                <option value="1" <?= ($setting_kbm->upacara->jam_ke ?? '0') === '1' ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= ($setting_kbm->upacara->jam_ke ?? '0') === '2' ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= ($setting_kbm->upacara->jam_ke ?? '0') === '3' ? 'selected' : '' ?>>3</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-4">
                            Durasi:
                            <input type="number" class="form-control form-control-sm"
                                   value="<?= $setting_kbm->upacara->durasi ?? '0' ?>" name="durasi_upacara">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="jam_mapel" class="col-md-4 col-form-label">Durasi Mapel *</label>
                        <div class="col-md-8">
                            <?php
                            $menits[''] = 'Durasi mapel';
                            $jam = 60;
                            for ($i = 0; $i < 3; $i++) {
                                $menits[$jam] = $jam . ' menit';
                                $jam += 30;
                            }

                            echo form_dropdown(
                                'jam_mapel',
                                $menits,
                                $setting_kbm->kbm_jam_pel ?? '10',
                                'id="jam_mapel" class="form-control"'
                            ); ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="jml_mapel" class="col-md-4 col-form-label">Jumlah Mapel *</label>
                        <div class="col-md-8">
                            <input id="jml_mapel" type="number" class="form-control" name="jml_mapel"
                                   value="<?= $setting_kbm->kbm_jml_mapel_hari ?? '0' ?>"
                                   placeholder="Jml Mapel">
                            <small>Termasuk Upacara dan jumlah istirahat</small>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="jum_ist" class="col-md-4 col-form-label">Jml. Istirahat *</label>
                        <div class="col-md-8">
                            <?php
                            $kali[''] = 'Jml Istirahat';
                            $kl = 1;
                            for ($k = 0; $k < 4; $k++) {
                                $kali[$kl] = $kl . ' kali';
                                $kl += 1;
                            }

                            echo form_dropdown(
                                'jum_ist',
                                $kali,
                                count($setting_kbm->istirahat),
                                'id="jum_ist" class="form-control" placeholder="Jml Istirahat"'
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
        <?= form_close() ?>
    </div>
</div>

<div class="modal fade" id="hariJadwalModal" tabindex="-1" role="dialog" aria-labelledby="hariModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <?= form_open('setMapel', array('id' => 'setMapel')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hariModalLabel">Jadwal Hari</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="konten-input"></div>
                <input type="hidden" id="kelas" name="kelas" value="">
                <input type="hidden" id="hari" name="hari" value="">
                <input type="hidden" id="jadwal" name="jadwal" value="">

                <input type="hidden" id="id_kbm" name="id_kbm" value="">
                <input type="hidden" id="kbm_mulai" name="kbm_mulai" value="">
                <input type="hidden" id="kbm_selesai" name="kbm_selesai" value="">
                <input type="hidden" id="kbm_libur" name="kbm_libur" value="">
                <div>
                    <ul>
                        <li><b>JAM dibatasi</b> jika jam sebelumnya dan sesudahnya sudah terisi</li>
                        <li>Jika ingin <b>menambahkan batas JAM</b>, hapus terlebih dahulu jadwal sebelumnya atau sesudahnya</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="hapus-jadwal">Hapus</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>
        </div>
        <?= form_close() ?>
    </div>
</div>

<div class="modal fade" id="hapusDialog" tabindex="-1" role="dialog" aria-labelledby="hapusDialogLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customDialogLabel">Pilih Opsi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="mb-0"><strong>Hapus Jadwal:</strong></p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="del-type" id="radio1" value="r1">
                    <label class="form-check-label" for="radio1" id="r1-label"></label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="del-type" id="radio2" value="r2">
                    <label class="form-check-label" for="radio2">Semua jadwal dalam seminggu</label>
                </div>
                <div class="d-none">
                    <p class="mt-3 mb-0"><strong>Istirahat:</strong></p>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ist-type" id="radio3" value="r3">
                        <label class="form-check-label" for="radio3">Biarkan jam Istirahat</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ist-type" id="radio4" value="r4">
                        <label class="form-check-label" for="radio4">Hapus jam istirahat</label>
                    </div>
                </div>

                <div class="mb-4 mt-1">
                    <span class='text-danger text-bold'>Hapus jadwal</span>
                    <span class='text-danger'>di atas hanya berlaku untuk jadwal tanpa MAPEL. Atur jadwal ke <b>Tanpa Mapel</b> agar bisa dihapus</span>
                </div>
                <div>
                    <div class="icheck-cyan">
                        <input type="checkbox" id="reset-all" name="reset-all">
                        <label for="reset-all">Hapus juga jadwal yang terisi Mapel</label>
                    </div>
                    <p>Semua jadwal akan dikosongkan!</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="hapusJadwalHarian()">Hapus</button>
            </div>

        </div>
    </div>
</div>

<script>
    const dataConvert = JSON.parse(JSON.stringify(<?= isset($all_jadwal) ? json_encode($all_jadwal) : '{}'?>));
    const allKbm = JSON.parse(JSON.stringify(<?= isset($setting_kbm) ? json_encode($setting_kbm) : '{}'?>));
    let istirahat = allKbm.istirahat || [];
    let isAdmin = true;
    let jmlMapel = 0;

    let settingKbm = JSON.parse('<?= json_encode($setting_kbm) ?>');
    let kelas = JSON.parse(JSON.stringify(<?= isset($kelas) ? json_encode($kelas) : '{}'?>));
    let arrMapel = {};
    let jadwalKbm = {};
    let times = [];
    let tp, smt = {}
    let idHari = [];

    let arrDataJadwal = []
    let arrKBM = []
    let arrStart = []
    let idJadwal = '';
    const arrHari = { "7": "Minggu", "1": "Senin", "2": "Selasa", "3": "Rabu", "4": "Kamis", "5": "Jum'at", "6": "Sabtu" };

    function onChangeJmlMapel(s) {
        jmlMapel = s;
        onChangeJmlIst($('#jum_ist').val());
    }

    function onChangeJmlIst(jml) {
        var iid = 1;
        var inputgroup = $('#format-jadwal');
        if (jml > 1) {
            $('#ist1').text('Istirahat 1');
            $('#dur1').text('Durasi Istirahat 1');
        } else {
            $('#ist1').text('Istirahat');
            $('#dur1').text('Durasi Istirahat');
        }

        for (let o = 1; o < 5; o++) {
            $("#input_ist" + o).remove();
            $("#input_dur" + o).remove();
        }

        for (let i = 0; i < jml; i++) {
            var num = jml === '1' ? '' : iid;
            var val = i + 1 > istirahat.length ? '' : istirahat[i].dur;
            var se = i + 1 > istirahat.length ? 0 : istirahat[i].ist;

            var inputDurasi = '<div class="card mb-2" id="input_ist' + iid + '">' +
                '<div class="card-body bg-gray-light p-1">' +
                '<div class="row justify-content-between">' +
                '<div class="col-6">' +
                '<div class="form-group row mb-0">' +
                '<label id="ist' + iid + '" class="col-md-5 col-form-label">Istirahat ' + num + '</label>' +
                '<div class="col-md-7">' +
                '<select name="ist' + iid + '" id="ist' + (i + 1) + '" class="form-control" required="">';
            for (let j = 0; j < jmlMapel; j++) {
                if (j === 0) {
                    inputDurasi += '<option value="">Jam Istirahat</option>';
                } else {
                    if (j == se) {
                        inputDurasi += '<option value="' + j + '" selected>Jam ke ' + j + '</option>';
                    } else {
                        inputDurasi += '<option value="' + j + '" >Jam ke ' + j + '</option>';
                    }
                }
            }
            inputDurasi += '</select>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="col-5">' +
                '<div class="form-group row mb-0" id="input_dur' + iid + '">' +
                '<label id="dur' + iid + '" class="col-md-5 col-form-label">Durasi</label>' +
                '<div class="col-md-7">' +
                '<input type="number" id="dur_ist' + iid + '" class="form-control" name="dur_ist' + iid + '"' +
                'value="' + val + '" placeholder="Menit" required>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';

            iid++;
            inputgroup.append(inputDurasi);
        }
    }

    function getOffset(el) {
        const rect = el.getBoundingClientRect();
        return {
            left: rect.left + window.scrollX,
            top: rect.top + window.scrollY,
            right: rect.right + window.scrollX,
            bottom: rect.bottom + window.scrollY,
        };
    }

    $(document).ready(function ($) {
        onChangeJmlIst(istirahat.length);
        jmlMapel = allKbm.kbm_jml_mapel_hari || 0;
        onChangeJmlMapel(jmlMapel);
        $('#jum_ist').on('change', function () {
            onChangeJmlIst($(this).val());
        });

        $('#jml_mapel').on('change', function () {
            onChangeJmlMapel($(this).val());
        });

        $("#formatted").on('change', function () {
            var checked = $(this).prop('checked') == true;
            if (checked) {
                $('#jml_mapel').attr('required', '')
                $('#jam_mapel').attr('required', '')
                $('#jum_ist').attr('required', '')
                $('#format-jadwal').removeClass('d-none')
            } else {
                $('#jml_mapel').removeAttr('required')
                $('#jam_mapel').removeAttr('required')
                $('#jum_ist').removeAttr('required')
                $('#format-jadwal').addClass('d-none')
                istirahat = allKbm.istirahat || []
                $('#jum_ist').val(istirahat.length)
                onChangeJmlIst(istirahat.length)
            }
        });

        createHari();

        $('.select2').select2({
            width: '100%',
            dropdownParent: $("#createJadwalModal")
        });

        $('#setjadwal').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let formData = new FormData($('#setjadwal')[0]);
            formData.append('id_smt', smt.id_smt)
            formData.append('id_tp', tp.id_tp)

            $('#createJadwalModal').modal('hide');

            swal.fire({
                title: "Simpan Jadwal",
                text: "Pengaturan KBM akan disimpan",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Simpan"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'elearning/setJadwal',
                        type: "POST",
                        processData: false,
                        contentType: false,
                        data: formData,
                        success: function (data) {
                            swal.fire({
                                title: "Sukses",
                                text: "Jadwal Pelajaran berhasil disimpan",
                                icon: "success",
                                showCancelButton: false,
                            }).then(result => {
                                reload();
                            });
                        },
                        error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            swal.fire({
                                title: "ERROR",
                                text: "Data Tidak Tersimpan",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            })
        });

        $('#setMapel').submit('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const dari = $("input[name=dari]").val();
            const sampai = $("input[name=sampai]").val();

            if (dari >= sampai) {
                swal.fire({
                    title: "Peringatan",
                    text: "Jam Mulai harus lebih kecil dari Jam Selesai",
                    icon: "warning",
                    showCancelButton: false,
                });
                return false;
            }

            let formData = new FormData($('#setMapel')[0]);
            formData.append('smt', smt.id_smt)
            formData.append('tp', tp.id_tp)

            $('#hariJadwalModal').modal('hide');

            $.ajax({
                url: base_url + 'elearning/setMapel',
                type: "POST",
                dataType: "JSON",
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    if (data.status) {
                        swal.fire({
                            title: "Sukses",
                            text: "Jadwal Pelajaran berhasil disimpan",
                            icon: "success",
                            showCancelButton: false,
                        }).then(result => {
                            reload();
                        });
                    } else {
                        swal.fire({
                            title: "ERROR",
                            text: "Data Tidak Tersimpan",
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    swal.fire({
                        title: "ERROR",
                        text: "Data Tidak Tersimpan",
                        icon: "error",
                        showCancelButton: false,
                    });
                }
            });
        });

        $('#hapus-jadwal').on('click', function () {
            $('#hariJadwalModal').modal('hide');
            setTimeout(function () {hapusJadwalMapel(idJadwal)}, 200)
        })
    });

    function createHari() {
        console.log('settingKbm', settingKbm)
        if (Object.keys(kelas).length > 0) {
            $('#arr-kelas').html('')
            let newArrIdHari = [];
            const keys = Object.keys(arrHari);
            const idLibur = keys.indexOf(settingKbm.libur ?? '7');
            keys.slice(idLibur).forEach(value => {
                newArrIdHari.push(value);
            });
            keys.slice(0, idLibur).forEach(value => {
                newArrIdHari.push(value);
            });
            newArrIdHari.shift();
            for (const idhari of newArrIdHari) {
                const btnStyle = idHari[1] != undefined && idHari[1] === idhari ? 'btn-success' : 'btn-outline-success'
                $('#arr-kelas').append(`<button class="m-1 btn btn-kelas ${btnStyle}" data-id="btn-${idhari}">
                    ${arrHari[idhari].substring(0,3)}<span class="d-md-inline d-none">${arrHari[idhari].substring(3)}</span></button>`)
            }

            $('.btn-kelas').on('click', function (e) {
                idHari = $(this).data('id').split('-')
                $('.btn-kelas').removeClass('btn-success')
                $('.btn-kelas').addClass('btn-outline-success')

                $(this).removeClass('btn-outline-success')
                $(this).addClass('btn-success')

                reload();
            })
        } else {
            //$('#alert').removeClass('d-none')
        }
    }

    function hapusJadwalMapel(idJadwal) {
        swal.fire({
            title: "Hapus Mapel",
            text: "Mata Pelajaran akan dihapus",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f6052a",
            confirmButtonText: "Hapus"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });
                setTimeout(function () {
                    $.ajax({
                        url: base_url + 'elearning/delMapel/'+idJadwal,
                        success: function (data) {
                            if (data.status) {
                                swal.fire({
                                    title: "Sukses",
                                    text: "Jadwal Pelajaran berhasil disimpan",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    reload();
                                });
                            } else {
                                swal.fire({
                                    title: "ERROR",
                                    text: "Data Tidak Tersimpan",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            swal.fire({
                                title: "ERROR",
                                text: "Data Tidak Tersimpan",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }, 200)
            }
        })
    }

    function createTable() {
        const arrKelas = [];
        document.querySelectorAll('.my-timetable thead tr th[data-kelas]').forEach(th => {
            arrKelas.push(th.getAttribute('data-kelas'));
        });
        console.log('test', arrKelas);
        const tableContainer = document.createElement('div');
        tableContainer.className = 'table-responsive mb-3';

        arrStart = []
        if (arrKelas.length > 0) {
            const table = document.createElement('table');
            table.className = 'my-timetable w-100 border-bottom';
            table.id = 'tbl';

            // Membuat thead
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            headerRow.className = 'alert-primary';

            const headerTh1 = document.createElement('th');
            headerTh1.colSpan = 2;
            headerTh1.style.width = '80px';
            headerTh1.style.minWidth = '80px';
            headerTh1.style.maxWidth = '80px';
            headerTh1.className = 'text-center frozen-header text-white py-2';
            headerTh1.textContent = 'JAM';
            headerRow.appendChild(headerTh1);

            // Menambahkan kolom header untuk setiap kelas
            for (const kelasKey of arrKelas) {
                const value = kelas[kelasKey]
                const th = document.createElement('th');
                th.className = 'align-middle text-center';
                th.style.minWidth = '100px';
                th.textContent = value;
                headerRow.appendChild(th);
            }

            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Membuat tbody
            const tbody = document.createElement('tbody');
            let idRow = 0;
            const jadwalRows = [];

            times.forEach(jam => {
                const startTime = jam.start || '';
                arrStart.push(startTime)
                const rowSpanJam = jam.span || 12;

                if (idRow === 0) {
                    const firstRow = document.createElement('tr');
                    firstRow.className = `row${idRow}`;

                    const td0 = document.createElement('td');
                    td0.className = 'column-time text-center align-top text-xs text-bold times p-0 frozen-column';
                    td0.rowSpan = rowSpanJam;
                    td0.textContent = startTime;
                    firstRow.appendChild(td0);

                    const td1 = document.createElement('td');
                    td1.className = 'frozen-column-2';
                    td1.style.width = '30px';
                    td1.style.height = '10px';
                    firstRow.appendChild(td1);

                    for (let i = 2; i <= arrKelas.length + 1; i++) {
                        const td = document.createElement('td');
                        td.className = `row0`;
                        firstRow.appendChild(td);
                    }

                    tbody.appendChild(firstRow);
                } else {
                    const minute = startTime.split(':')[1];
                    const row = document.createElement('tr');
                    row.className = `row${idRow}`;
                    if (idRow % 2 !== 0) row.setAttribute('data-time', startTime);

                    if (minute % 30 === 0) {
                        const td0 = document.createElement('td');
                        td0.className = 'column-time text-center align-top text-xs text-bold times p-0 frozen-column';
                        td0.rowSpan = rowSpanJam;
                        td0.textContent = startTime;
                        row.appendChild(td0);
                    }

                    const td1 = document.createElement('td');
                    td1.className = 'frozen-column-2';
                    td1.style.width = '30px';
                    td1.style.height = '6px';
                    row.appendChild(td1);

                    tbody.appendChild(row);
                }

                // Tambahkan data jadwal di kolom
                const scheduleRow = document.createElement('tr');
                scheduleRow.className = `row${idRow + 1}`;
                if (idRow % 2 !== 0) scheduleRow.setAttribute('data-time', startTime);

                const td1 = document.createElement('td');
                td1.className = 'frozen-column-3';
                td1.style.width = '30px';
                td1.style.height = '6px';
                scheduleRow.appendChild(td1);

                for (const kelasKey of arrKelas) {
                    const kls = kelas[kelasKey]
                    const idk = kelasKey;
                    if (!jadwalRows[idk] || jadwalRows[idk] === 0) {
                        const kbm = jadwalKbm[idk] || {};
                        const arr = jadwalKbm[idk]?.detail?.[startTime] || null;
                        if (arr?.id_hari === '2' && arr?.id_mapel === '-1') console.log('arr', arr);
                        const rowSpan = arr ? arr.rows * 2 : 2;
                        jadwalRows[idk] = rowSpan;

                        const td = document.createElement('td');
                        td.className = `align-middle row${idRow + 1} column${idk} text-xs border-right`;
                        td.rowSpan = rowSpan;
                        td.setAttribute('data-dari', startTime)
                        td.setAttribute('data-pos', idRow)
                        td.setAttribute('data-kelas', idk)

                        const div = document.createElement('div');
                        div.className = 'h-100 pb-1 add-mapel';
                        div.style.padding = '0 .1rem';
                        div.setAttribute('data-pos', idRow)
                        div.setAttribute('data-mapel', '0')
                        div.setAttribute('data-kelas', idk)
                        div.setAttribute('data-dari', startTime)
                        div.setAttribute('data-hari', idHari[1])
                        div.setAttribute('data-idkbm', kbm.id_kbm || '')
                        div.setAttribute('data-kbmmulai', kbm.kbm_jam_mulai || settingKbm.mulai)
                        div.setAttribute('data-kbmselesai', kbm.kbm_jam_selesai || settingKbm.selesai)
                        div.setAttribute('data-kbmlibur', kbm.libur || settingKbm.libur)

                        if (arr) {
                            div.setAttribute('data-dari', arr.dari)
                            div.setAttribute('data-sampai', arr.sampai)
                            div.setAttribute('data-kelas', arr.id_kelas)
                            div.setAttribute('data-mapel', arr.id_mapel)
                            div.setAttribute('data-hari', arr.id_hari)
                            div.setAttribute('data-id', arr.id_jadwal)
                            const alertDiv = document.createElement('div');
                            const flex = rowSpan > 6 ? 'flex-column justify-content-center' : 'flex-row justify-content-between';
                            let classAlert = ` p-1 h-100 m-0 d-flex ${flex} alert`;
                            if (arr.id_mapel === '-1') {
                                classAlert += ' alert-default-warning';
                            } else if (arr.id_mapel === '-2') {
                                classAlert += ' alert-default-success';
                            } else if (arr.kode) {
                                classAlert += ' alert-default-info';
                            } else {
                                classAlert += ' alert-default-secondary';
                            }
                            alertDiv.className = classAlert;//`alert alert-default-${arr.id_mapel === '-1' ? 'warning' : (arr.kode ? 'info' : 'secondary')} p-1 h-100 m-0 d-flex ${flex}`;

                            if (arr.id_mapel === '-1') {
                                alertDiv.innerHTML = `<div class="text-xs ml-1">Istirahat</div>
                                    <div class="d-inline-block mx-1 text-nowrap">${arr.dari} - ${arr.sampai}</div>`;
                            } else if (arr.id_mapel === '-2') {
                                alertDiv.innerHTML = `<div class="text-xs ml-1 text-bold text-nowrap">Upacara</div>
                                    <div class="d-inline-block mx-1 text-nowrap">${arr.dari} - ${arr.sampai}</div>`;
                            } else {
                                if (arr.kode) {
                                    alertDiv.innerHTML = `<div class="text-bold ml-1">${arr.kode}</div>
                                    <div class="d-inline-block mx-1 text-nowrap">${arr.dari} - ${arr.sampai}</div>`;
                                }
                            }
                            div.appendChild(alertDiv);
                        }

                        td.appendChild(div);
                        scheduleRow.appendChild(td);
                    }
                }

                tbody.appendChild(scheduleRow);

                // Update jumlah baris
                for (const idks of arrKelas) {
                    if (jadwalRows[idks]) jadwalRows[idks] -= 2;
                }

                idRow += 2;
            });

            table.appendChild(tbody);
            tableContainer.appendChild(table);
            $('#alert').addClass('d-none')
        } else {
            tableContainer.append(`<div class="alert alert-default-warning shadow align-content-center" role="alert">
                    Belum ada data kelas untuk Tahun Pelajaran <b>${tp.tahun}</b> Semester:<b>${smt.smt}</b>
                 </div>`);
        }

        const element = document.getElementById("table-jadwal");
        element.classList.remove("d-none");

        let oldH3Element =  document.querySelector('div.table-responsive')
        if(oldH3Element) oldH3Element.parentNode.removeChild(oldH3Element)
        element.appendChild(tableContainer);
        // Append the table container to the body or any desired container

        $('#loading').addClass('d-none')
        $('#edit-jadwal').removeAttr('disabled')
        $('#copy-jadwal').removeAttr('disabled')
        //$('#alert').addClass('d-none')

        // jadwal global
        $('#createJadwalModal').on('show.bs.modal', function (e) {
            if (settingKbm.libur === '0') settingKbm.libur = '7'
            $("#hari_libur select").val(settingKbm.libur).change();
            $("#jam_mulai").val(settingKbm.mulai);
            $("#jam_selesai").val(settingKbm.selesai);
            $('#jam_mulai').datetimepicker({
                datepicker: false,
                format: 'H:i',
                step: 15,
                minTime: '05:00',
                maxTime: settingKbm.mulai
            });
            $('#jam_selesai').datetimepicker({
                datepicker: false,
                format: 'H:i',
                step: 15,
                minTime: '07:00',
                maxTime: '18:00'
            });
        })

        if (isAdmin) {
            $('#arr-kelas').append(`<button class="m-1 btn btn-danger float-right" data-toggle="modal" data-target="#hapusDialog">
                                    <i class="far fa-trash-alt"></i><span class="d-sm-inline d-none"> Hapus</span></button>`)

            $('#reformat').removeClass('d-none');
            $('#hapusDialog').on('show.bs.modal', function (e) {
                const title = 'Hapus Jadwal Hari ' + arrHari[idHari[1]] + ' saja';
                $('#r1-label').text(title);
            })
        }

        document.addEventListener('click', eventListener);
    }

    function getMaxTime(data) {
        let maxTime = "00:00";

        Object.values(data).forEach(entry => {
            if (entry.sampai > maxTime) {
                maxTime = entry.sampai;
            }
        });

        return maxTime;
    }

    function reload() {
        if (idHari.length === 0) return
        $('#loading').removeClass('d-none')
        document.removeEventListener('click', eventListener);
        setTimeout(function () {
            $.ajax({
                type: "GET",
                url: base_url + "elearning/hari/" + idHari[1],
                success: function (response) {
                    console.log('response', response)
                    isAdmin = response.is_admin;
                    arrMapel = response.mapels;
                    settingKbm = response.setting_kbm;
                    jadwalKbm = response.jadwal_kbm;
                    times = response.times;
                    kelas = response.kelas;
                    tp = response.tp_active;
                    smt = response.smt_active;
                    if (times.length > 0) {
                        createHari()
                        createTable();
                    }
                },
                error: function (xhr, status, error) {
                    $('#loading').addClass('d-none')
                    console.log("error", xhr.responseText);
                }
            });
        }, 200)
    }

    function sortTableHorizontallyByHeader(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        if (!thead || !tbody) return;

        const headerRow = thead.rows[0];
        const headers = Array.from(headerRow.cells);

        // Buat array: nilai header dan index kolom awal
        const sortedHeader = headers.map((cell, index) => ({
            value: cell.textContent.trim(),
            originalIndex: index
        }));

        // Urutkan berdasarkan nilai header (bisa diubah ke numeric jika perlu)
        sortedHeader.sort((a, b) => a.value.localeCompare(b.value));

        const sortedIndices = sortedHeader.map(h => h.originalIndex);

        // Reorder header
        const newHeaderCells = sortedIndices.map(i => headers[i].cloneNode(true));
        headerRow.innerHTML = '';
        newHeaderCells.forEach(cell => headerRow.appendChild(cell));

        // Reorder setiap row di tbody
        Array.from(tbody.rows).forEach(row => {
            const cells = Array.from(row.cells);
            const newCells = sortedIndices.map(i => cells[i].cloneNode(true));
            row.innerHTML = '';
            newCells.forEach(cell => row.appendChild(cell));
        });
    }

    function eventListener(event) {
        if (!isAdmin) return
        if (event.target.closest('.add-mapel')) {
            const div = event.target.closest('.add-mapel');

            const kelasId = div.getAttribute('data-kelas');
            const dari = div.getAttribute('data-dari');
            const hari = div.getAttribute('data-hari');
            const sampai = div.getAttribute('data-sampai');
            const mpl = div.getAttribute('data-mapel');
            idJadwal = div.getAttribute('data-id');
            const divPos = parseInt(div.getAttribute('data-pos'), 10);
            const kbmId = div.getAttribute('data-idkbm')
            const kbmMulai = div.getAttribute('data-kbmmulai')
            const kbmSelesai = div.getAttribute('data-kbmselesai')
            const kbmLibur = div.getAttribute('data-kbmlibur')

            const tds = document.querySelectorAll(`td.column${kelasId}`);

            let storedTimes = [];
            let lastTime = null;
            Array.from(tds).some(td => {
                const tdKelas = td.getAttribute('data-kelas');
                if (tdKelas === kelasId) {
                    const tdPos = parseInt(td.getAttribute('data-pos'), 10);
                    if (tdPos <= divPos) {
                        const dataMulai = td.getAttribute('data-dari');
                        if (dataMulai) {
                            if (td.getAttribute('rowspan') === "2") {
                                storedTimes.push(dataMulai)
                            } else {
                                if (dataMulai !== dari) storedTimes = []
                            }
                        }
                    } else { //if (tdPos > divPos) {
                        const dataMulai = td.getAttribute('data-dari');
                        if (dataMulai) {
                            const [jam, menit] = dataMulai.split(':').map(Number);
                            if (lastTime) {
                                const [lastJam, lastMenit] = lastTime.split(':').map(Number);
                                const timeDifference = (jam * 60 + menit) - (lastJam * 60 + lastMenit);
                                if (timeDifference === 5) {
                                    storedTimes.push(dataMulai);
                                } else if (timeDifference > 5) {
                                    return true;
                                }
                            } else {
                                storedTimes.push(dataMulai);
                            }
                            lastTime = dataMulai;
                        }
                    }
                    return false;
                }
            });
            const filterNumbers = (min, max) => {
                return function (a) {
                    return a >= min && a <= max;
                };
            }
            let startEnds = arrStart.filter(filterNumbers(dari, sampai))
            for (const startEnd of startEnds) {
                if (!storedTimes.includes(startEnd)) storedTimes.push(startEnd)
            }
            storedTimes.sort(function (a, b) {
                return a.localeCompare(b);
            });

            const kontenInput = $('#konten-input')
            kontenInput.html('')
            let input = `<div class="card bg-light">
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2" id="select-mapel">
                                Mapel:
                                <select name="mapel" class="form-control form-control-sm select2" data-placeholder="Pilih Mapel" required>
                                    <option value="-1">Istirahat</option>
                                    <option value="-2">Upacara</option>`;

            const mapelGuru = isAdmin ? arrMapel : arrMapel[kelasId];
            for (const id in mapelGuru) {
                input += id === '' ? `<option value="0">${mapelGuru[id]}</option>` : `<option value="${id}">${mapelGuru[id]}</option>`;
            }
            input += `<option value="0">Tanpa Mapel</option>`;
            input += `</select>
                            </div>
                            <div class="col-6 col-md-3 mb-2">Dari:
                                <input type="text" name="dari" value="${dari}" class="form-control form-control-sm jam">
                            </div>
                            <div class="col-6 col-md-3 mb-2">Sampai:
                                <input type="text" name="sampai" value="${sampai || dari}" class="form-control form-control-sm jam">
                            </div>
                        </div>
                    </div>
                </div>`;
            kontenInput.prepend(input)

            $('#kelas').val(kelasId)
            $('#hari').val(hari)
            $('#jadwal').val(idJadwal)
            if (idJadwal) {
                $('#hapus-jadwal').removeAttr('disabled')
            } else {
                $('#hapus-jadwal').attr('disabled', 'disabled')
            }

            $('#id_kbm').val(kbmId)
            $('#kbm_mulai').val(kbmMulai)
            $('#kbm_selesai').val(kbmSelesai)
            $('#kbm_libur').val(kbmLibur)

            $('.select2').select2({
                width: '100%',
                dropdownParent: $("#hariJadwalModal")
            });
            $('#select-mapel select').val(mpl).change()
            $('.jam').datetimepicker({
                datepicker: false,
                format: 'H:i',
                step: 5,
                allowTimes: storedTimes,
                minTime: '07:00',
                maxTime: '17:00'
            });

            $('#hariJadwalModal').modal('show');
            isShow = true;
        }
    }

    function hapusJadwalHarian() {
        const type = document.querySelector('input[name="del-type"]:checked')?.value;
        //const ist = document.querySelector('input[name="ist-type"]:checked')?.value;
        //console.log('type', type, ist)
        console.log('hapus', arrHari[idHari[1]])
        if (!type) { // || !ist) {
            swal.fire({
                title: "ERROR",
                text: "Pilih Type Hapus Jadwal",
                icon: "error",
                showCancelButton: false,
            });
            return;
        }
        const isReset = $('#reset-all').is(":checked");
        let formData = new FormData($('#setjadwal')[0]);
        formData.append('id_smt', smt.id_smt)
        formData.append('id_tp', tp.id_tp)
        formData.append('type', type)
        formData.append('all', isReset)
        formData.append('id_hari', idHari[1])

        $('#hapusDialog').modal('hide');
        const title = type === 'r1' ? 'Hapus Jadwal Hari ' + arrHari[idHari[1]] : 'Hapus Semua Jadwal';
        swal.fire({
            title: title,
            text: "Jadwal tanpa MAPEL akan dihapus",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f6052a",
            confirmButtonText: "Hapus"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });

                setTimeout(function () {
                    $.ajax({
                        url: base_url + 'elearning/delJadwal',
                        type: "POST",
                        processData: false,
                        contentType: false,
                        data: formData,
                        success: function (data) {
                            if (data.status) {
                                swal.fire({
                                    title: "Sukses",
                                    text: "Jadwal Pelajaran berhasil dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    reload();
                                });
                            } else {
                                swal.fire({
                                    title: "ERROR",
                                    text: "Data Tidak Tersimpan",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            swal.fire({
                                title: "ERROR",
                                text: "Data Tidak Tersimpan",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }, 200)
            }
        })
    }

    function reformat() {
        swal.fire({
            title: "Reformat Jadwal",
            text: "Jadwal akan diformat",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f6052a",
            confirmButtonText: "Format"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    title: "Menyalin Jadwal",
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });

                let formData = new FormData($('#setjadwal')[0]);
                formData.append('id_smt', smt.id_smt)
                formData.append('id_tp', tp.id_tp)

                $.ajax({
                    url: base_url + 'elearning/reformat_jadwal',
                    type: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (data) {
                        console.log("format", data);
                        if (data.status) {
                            swal.fire({
                                title: "Sukses",
                                text: "Jadwal Pelajaran berhasil diformat",
                                icon: "success",
                                showCancelButton: false,
                            }).then(result => {
                                reload();
                            });
                        } else {
                            swal.fire({
                                title: "ERROR",
                                text: "Gagal reformat",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        swal.fire({
                            title: "ERROR",
                            text: xhr.responseText,
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                });
            }
        })
    }

    function copyJadwal() {
        const sm = smt.id_smt;
        const smtLalu = sm === '1' ? '2' : '1'; // 1 = Genap, 2 = Ganjil
        const tahun = tp.tahun?.split('/');
        const thnLalu = sm === '1' ? (parseInt(tahun[0]) - 1) + '/' + tahun[0] : tahun[0] + '/' + tahun[1];
        console.log('tp', thnLalu, smtLalu)
        swal.fire({
            title: "Copy Jadwal dari SMT sebelumnya",
            html: "<span class='text-danger text-bold'>HATI-HATI!..<br /> Jadwal yang sudah ada akan dihapus!</span>" +
                "<br /><span class='text-danger'> Pastikan jadwal sudah kosong sebelum melakukan copy.</span>" +
                "<br /> Copy JADWAL bisa berhasil jika <b>kode kelas</b> sama dengan smt sebelumnya.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f6052a",
            confirmButtonText: "Copy"
        }).then(result => {
            if (result.value) {
                swal.fire({
                    title: "Menyalin Jadwal",
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    onOpen: () => {
                        swal.showLoading();
                    }
                });

                let formData = new FormData($('#setjadwal')[0]);
                formData.append('smt', smt.id_smt)
                formData.append('tp', tp.id_tp)
                formData.append('thnLalu', thnLalu)
                formData.append('smtLalu', smtLalu)

                $.ajax({
                    url: base_url + 'elearning/copy_jadwal',
                    type: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (data) {
                        if (data.status) {
                            swal.fire({
                                title: "Sukses",
                                text: "Jadwal Pelajaran berhasil disalin",
                                icon: "success",
                                showCancelButton: false,
                            }).then(result => {
                                reload();
                            });
                        } else {
                            swal.fire({
                                title: "ERROR",
                                text: "Gagal menyalin jadwal",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        swal.fire({
                            title: "ERROR",
                            text: xhr.responseText,
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                });
            }
        })
    }
</script>
