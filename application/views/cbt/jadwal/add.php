<?php

?>

<div class="content-wrapper bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <?= form_open('create', array('id' => 'create')) ?>
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6><?= $judul ?></h6>
                    </div>
                    <div class="card-tools">
                        <input type="hidden" id="id-jadwal" name='id_jadwal' value="<?= $jadwal->id_jadwal ?>"
                               class='form-control d-none'/>
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-right ml-2">
                        <i class="fas fa-arrow-circle-left"></i><span
                                class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </button>
                        <button name='tambahjadwal' class='btn btn-primary btn-sm'><i class='fas fa-save'></i> Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $disabled_option = $disable_opsi ? 'disabled="disabled"' : '';
                    ?>
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label>Mata Pelajaran</label>
                            <?php
                            echo form_dropdown(
                                'mapel',
                                $mapel,
                                isset($jadwal->id_mapel) ? $jadwal->id_mapel : '',
                                $disabled_option . ' id="id-mapel" class="form-control form-control-sm" required'
                            ); ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Bank Soal</label>
                            <select <?= $disabled_option ?> name="bank_id" id="bank-id"
                                                            class="form-control form-control-sm" required=""></select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Jenis</label>
                            <?php
                            echo form_dropdown(
                                'jenis_id',
                                $jenis,
                                $jadwal->id_jenis,
                                $disabled_option . ' id="jenis-id" class="form-control form-control-sm" required'
                            ); ?>
                        </div>
                        <div class='col-6 col-md-3 mb-3'>
                            <label>Tanggal Mulai</label>
                            <input type='text' id="tgl-mulai" name='tgl_mulai' value="<?= $jadwal->tgl_mulai ?>"
                                   class='tgl form-control form-control-sm' autocomplete='off' required='true'/>
                        </div>
                        <div class='col-6 col-md-3 mb-3'>
                            <label>Tanggal Expired</label>
                            <input type='text' id="tgl-selesai" name='tgl_selesai' value="<?= $jadwal->tgl_selesai ?>"
                                   class='tgl form-control form-control-sm'
                                   autocomplete='off' required='true'/>
                        </div>
                        <div class='col-6 col-md-3 mb-3'>
                            <div class='form-group'>
                                <label>Durasi (menit)</label>
                                <input type='number' id="durasi-ujian" name='durasi_ujian'
                                       class='form-control form-control-sm' value="<?= $jadwal->durasi_ujian ?>"
                                       required='true'/>
                            </div>
                        </div>
                        <div class='col-6 col-md-3 mb-3'>
                            <div class='form-group'>
                                <label>Durasi minimal (mnt)</label>
                                <input type='number' id="durasi-ujian" name='jarak'
                                       class='form-control form-control-sm' value="<?= $jadwal->jarak ?>"
                                       required='true'/>
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='row'>
                            <div class="col-md-8 mb-3">
                                <div class="row">
                                    <div class='col-6'>
                                        <div class="icheck-cyan">
                                            <input type='checkbox' id="check-soal" name='acak_soal'
                                                   value='1' <?= $jadwal->acak_soal == 1 ? 'checked="checked"' : '' ?> <?= $disabled_option ?>/>
                                            <label for="check-soal">Acak Soal</label>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class="icheck-cyan">
                                            <input type='checkbox' id="check-opsi" name='acak_opsi'
                                                   value='1' <?= $jadwal->acak_opsi == 1 ? 'checked="checked"' : '' ?> <?= $disabled_option ?>/>
                                            <label for="check-opsi">Acak Jawaban</label>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class="icheck-cyan">
                                            <input type='checkbox' id="check-token" name='token'
                                                   value='1' <?= $jadwal->token === '1' ? 'checked="checked"' : '' ?> />
                                            <label for="check-token">Gunakan Token</label>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class="icheck-cyan">
                                            <input type='checkbox' id="check-hasil" name='hasil_tampil'
                                                   value='1' <?= $jadwal->hasil_tampil === '1' ? 'checked="checked"' : '' ?> />
                                            <label for="check-hasil">Tampilkan Hasil</label>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class="icheck-cyan">
                                            <input type='checkbox' id="check-login" name='reset_login'
                                                   value='1' <?= $jadwal->reset_login === '1' ? 'checked="checked"' : '' ?> />
                                            <label for="check-login">Reset Izin [auto log out]</label>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class="icheck-cyan">
                                            <input type='checkbox' id="check-pelanggaran" name='pelanggaran'
                                                   value='1' <?= $jadwal->pelanggaran === '1' ? 'checked="checked"' : '' ?>/>
                                            <label for="check-pelanggaran">Hitung Pelanggaran</label>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class="icheck-cyan">
                                            <input type='checkbox' id="check-status" name='status'
                                                   value='1' <?= $jadwal->status === '1' ? 'checked="checked"' : '' ?> <?= $disabled_option ?>/>
                                            <label for="check-status">Aktif</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <?php
                                $jumlahKelas = $jadwal->bank_kelas;
                                $kelasbank = '';
                                $no = 1;
                                foreach ($jumlahKelas as $j) {
                                    foreach ($kelas as $k) {
                                        if ($j->kelas_id == $k->id_kelas) {
                                            $kelasbank .= ' <span class="badge badge-btn badge-info">' . $k->nama_kelas . '</span> ';
                                            $no++;
                                        }
                                    }
                                }
                                $dnone = $kelasbank === '' ? 'd-none' : '';
                                ?>
                                <div class="alert alert-default-primary m-0 <?= $dnone ?>" id="info-kelas">
                                    <span class="text-bold">Jadwal Kelas:</span>
                                    <br>
                                    <div id="info-kelas-bank" class="mb-2"><?= $kelasbank ?></div>
                                    <p class="text-xs m-0">Untuk merubah kelas silahkan edit <b>Bank Soal</b></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <ul class="alert alert-default-warning">
                        <?php
                        if ($jadwal->id_jadwal != "") :
                            ?>
                            <li>
                                Beberapa opsi <b>tidak bisa diedit</b> karena jadwal ujian <b>sedang berlangsung</b>.
                            </li>
                        <?php endif; ?>
                        <li>
                            Jadwal ujian akan ditampilkan di siswa pada rentang waktu antara <b>tanggal mulai</b> dan
                            <b>tanggal expired</b>.
                        </li>
                        <li>
                            <b>Durasi minimal</b> untuk mengizinkan siswa menyelesaikan ujian pada menit yg ditentukan.
                        </li>
                        <li><b>Reset Izin</b> jika aktif maka siswa tidak bisa mengerjakan ujian di beberapa komputer
                            kecuali setelah diizinkan. Siswa juga akan otomatis logut jika membuka tab baru di browser.
                        </li>
                        <li><b>Hitung Pelanggaran</b> jika aktif maka pelanggaran akan dihitung dan siswa terkena
                            pengurangan point sebanyak pelanggaran yang dilakukan siswa. <b>Siswa akan diberi notifikasi
                                ketika melakukan pelanggaran</b>.
                            <p class="m-0"><span class="text-bold font-italic">Pelanggaran meliputi:</span>
                                Pelanggaran BERAT (membuka tab baru, keluar aplikasi sebelum selesai, me-minimize browser),
                                Pelanggaran SEDANG (me-nonaktifkan fullscreen) dan
                                Pelanggaran RINGAN (memulai ujian dengan tampilan tidak fullscreen) </p>
                        </li>
                    </ul>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
</div>

<script>
    var digunakan = <?= $disable_opsi ? '1' : '0' ?>;
    var idBank = '<?=$jadwal->id_bank?>';
    let kelasBank = [];
    let allKelas = [];
    $(document).ready(function () {
        ajaxcsrf();
        //console.log('used',digunakan);

        $('#jenis-id').select2({width: '100%', });
        var selMapel = $('#id-mapel');
        selMapel.select2({width: '100%', });
        var selec = idBank == '' ? 'selected' : '';
        selMapel.prepend('<option value="" ' + selec + '>Pilih Mata Pelajaran</option>');
        var selBank = $('#bank-id');
        selBank.select2({width: '100%', });

        $('.tgl').datetimepicker({
            icons:
                {
                    next: 'fa fa-angle-right',
                    previous: 'fa fa-angle-left'
                },
            timepicker: false,
            scrollInput: false,
            scrollMonth: false,
            format: 'Y-m-d',
            //disabledWeekDays: [0],
            widgetPositioning: {
                horizontal: 'left',
                vertical: 'bottom'
            }
        });

        function reEnable(disable) {
            if (digunakan == '1') {
                $('#id-guru').attr('disabled', disable);
                $('#bank-id').prop('disabled', disable);
                $('#jenis-id').prop('disabled', disable);
                $('#check-soal').prop('disabled', disable);
                $('#check-opsi').prop('disabled', disable);
                $('#check-token').prop('disabled', disable);
                $('#check-hasil').prop('disabled', disable);
                $('#check-login').prop('disabled', disable);
                $('#check-status').prop('disabled', disable);
                $('#check-pelanggaran').prop('disabled', disable);
            }
        }

        $('#create').submit('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            reEnable(false);
            console.log("data:", $(this).serialize());

            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            $.ajax({
                url: base_url + "cbtjadwal/saveJadwal",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (data) {
                    console.log(data);
                    reEnable(true);
                    $('#tambahjadwal').modal('hide').data('bs.modal', null);
                    $('#tambahjadwal').on('hidden', function () {
                        $(this).data('modal', null);  // destroys modal
                    });

                    if (data.success) {
                        swal.fire({
                            title: "Sukses",
                            text: "Jadwal berhasil disimpan",
                            icon: "success",
                            showCancelButton: false,
                        }).then(result => {
                            if (result.value) {
                                window.history.back()
                                //window.location.href = base_url + 'cbtjadwal';
                            }
                        });
                    } else {
                        swal.fire({
                            title: "ERROR",
                            text: data.message,
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        function getBankMapel(mapel) {
            if (!mapel) return
            $.ajax({
                url: base_url + "cbtjadwal/getbankmapel/" + mapel,
                type: "GET",
                success: function (data) {
                    console.log('bank', data);
                    selBank.html('<option value="" ' + selec + '>Pilih Bank Soal:</option>');
                    $.each(data.bank, function (i, v) {
                        var selected = i === idBank ? 'selected' : '';
                        const digunakan = data.jadwal.includes(i);
                        const disable = digunakan && i !== idBank ? ' disabled="disabled"': '';
                        const terjadwal = digunakan ? ' (terjadwal)' : '';
                        if (i !== '') selBank.append('<option value="' + i + '" ' + selected + disable + '>' + v + ' ' + terjadwal + '</option>');
                    });
                    allKelas = data.all_kelas || [];
                    kelasBank = data.kelas || [];
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            });
        }

        selMapel.on('change', function () {
            getBankMapel($(this).val());
        });

        selBank.on('change', function () {
            let kelas = '';
            let no = 1;
            console.log('kelas', kelasBank[$(this).val()])
            kelasBank[$(this).val()].forEach(k => {
                const kls = allKelas.find(x => x.id_kelas == k.kelas_id);
                if (kls) {
                    kelas += ` <span class="badge badge-btn badge-info">${kls.nama_kelas}</span> `;
                    no++;
                }
            });
            console.log(kelas)
            if (kelas !== '') {
                $('#info-kelas').removeClass('d-none');
                $('#info-kelas-bank').html(kelas);
            }
        });

        getBankMapel(selMapel.val());
    });
</script>
