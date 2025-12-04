<?php

?>
<div class="content-wrapper" style="margin-top: 30px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-0">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <?php $this->load->view('members/siswa/templates/top'); ?>
                </div>
            </div>
            <div class="container-fluid h-100">
                <div class="row h-100 justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card my-shadow">
                            <div class="card-body">
                                <?php
                                if ($support && $valid) : ?>
                                    <h3 class="text-center">KONFIRMASI</h3>
                                    <h5 class="text-center">
                                        <b><?= $jadwal->jenis->kode_jenis . ' | ' . $jadwal->tp->tahun . ' | ' . $jadwal->smt->smt ?></b>
                                    </h5>
                                    <br>
                                        <!-- ikasmedia -->
                                    <center> <img class="d-flex mr-2 rounded-circle" onerror="this.src='<?= base_url('assets/img/siswa.png') ?>'" src="<?= base_url($siswa->foto) ?>" width="120" height="120" alt="foto">
                                    <br>
                                    <b><?= $siswa->nama ?></b>
                                    <br>
                                    <b>[<?= $siswa->nama_kelas ?>]</b>
                                    </center>
                                    <?php

                                    $kelasbank = '';
                                    $no = 1;
                                    foreach ($jadwal->bank->bank_kelas as $j) {
                                        foreach ($kelas as $k) {
                                            if ($j->kelas_id === $k->id_kelas) {
                                                if ($no > 1) {
                                                    $kelasbank .= ', ';
                                                }
                                                $kelasbank .= $k->nama_kelas;
                                                $no++;
                                            }
                                        }
                                    }

                                    ?>
                                    <?= form_open('', array('id' => 'konfir')) ?>
                                    <input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
                                    <input type="hidden" name="jadwal" value="<?= $jadwal->id_jadwal ?>">
                                    <input type="hidden" name="bank" value="<?= $jadwal->bank->id_bank ?>">
                                    <ul class="list-group list-group-unbordered">
                                        <li class="list-group-item p-1"> Mata Pelajaran
                                            <span class="float-right"><b><?= $jadwal->bank->mapel->nama_mapel ?></b></span>
                                        </li>
                                        <li class="list-group-item p-1"> Kelas
                                            <span class="float-right"><b><?= $kelasbank ?></b></span>
                                        </li>
                                        <li class="list-group-item p-1"> Durasi Waktu
                                            <span class="float-right"><b><?= $jadwal->durasi_ujian ?> Menit</b></span>
                                        </li>
                                        <!--
									<li class="list-group-item p-1"> Status
										<span class="float-right">
											<b><?= ($bank->status === '0') ? 'Non Aktif' : 'Aktif' ?></b>
										</span>
									</li>
									-->
                                        <li class="list-group-item p-1"> Jumlah Soal
                                            <span class="float-right">
											<b><?= $jadwal->bank->tampil_pg + $jadwal->bank->tampil_kompleks + $jadwal->bank->tampil_jodohkan + $jadwal->bank->tampil_isian + $jadwal->bank->tampil_esai ?></b>
										</span>
                                        </li>
                                        <?php if ($jadwal->token === '1') : ?>
                                            <li class="list-group-item p-1"><span
                                                        class="text-danger"><b>Token</b></span>
                                                <div class="float-right" style="width: 100px">
                                                    <input type='text' id="input-token" class="form-control form-control-sm text-center" name='token'
                                                           placeholder="Token"/>
                                                </div>
                                            </li>
                                        <?php endif; ?>
                                    </ul>

                                    <div class="alert alert-default-info">
                                        <h5>Pengawas Ujian:</h5>
                                        <ul>
                                            <?php
                                            foreach ($pengawas as $pws) :?>
                                                <li><?= $pws->nama_guru ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <br>
                                    <span class="float-right" data-toggle="tooltip" title="MULAI">
                                        <button id="load-soal" type="submit" class="btn btn-success">MULAI</button>
                                    </span>
                                    <?= form_close(); ?>

                                <?php elseif (!$valid) : ?>
                                    <div class="alert alert-default-danger text-center p-2">
                                        <h3><i class="icon fas fa-ban"></i> WARNING..!!</h3>
                                        <div class="text-lg">
                                            Ujian tidak bisa dilanjutkan, harus reset izin!.
                                            <br>
                                            Hubungi proktor
                                        </div>
                                    </div>
                                <small>Refresh halaman ini jika sudah diizinkan</small>
                                <?php elseif (!$support): ?>
                                    <div class="alert alert-default-danger text-center p-2">
                                        <h3><i class="icon fas fa-ban"></i> WARNING..!!</h3>
                                        <div class="text-lg">
                                            Browser yang digunakan tidak mendukung
                                            <br>
                                            Silahkan gunakan browser lain dengan versi terbaru
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>/assets/app/js/redirect.js"></script>
<script>
    $('#konfir').submit(function (e) {
        e.stopPropagation();
        e.preventDefault();

        swal.fire({
            title: "Membuka Soal",
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });
        //console.log($(this).serialize());
        var jadwal = $(this).find('input[name="jadwal"]').val();
        $.ajax({
            type: 'POST',
            url: base_url + 'cbtsiswa/validasi_siswa',
            data: $(this).serialize(),
            success: function (data) {
                console.log(data);
                // jika menggunakan token, cek token
                if (data.token === true) {
                    // token ok
                    // cek browser dulu
                    if (data.support === false) {
                        // browser tidak support
                        // siswa stop disini
                        swal.fire({
                            "title": "Error",
                            "html": "Browser tidak mendukung!<br>Gunakan browser Chrome, atau Mozilla<br>005",
                            "icon": "error"
                        });
                    } else {
                        // browser OK
                        // cek izin ujian
                        if (data.izinkan === true) {
                            // diizinkan
                            // cek sisa waktu
                            if (data.ada_waktu === true) {
                                // masih ada waktu
                                // cek apakah ada soal?
                                const jml_soal = data.jml_soal;
                                let ada_soal = false;
                                if (jml_soal.length > 0) {
                                    ada_soal = jml_soal[0].counted_rows > 0;
                                }
                                if (ada_soal > 0) {
                                    // ada soal
                                    // siswa masuk halaman ujian
                                    window.location.href = base_url + 'cbtsiswa/penilaian/' + jadwal;
                                } else {
                                    // soal belum dibuat
                                    swal.fire({
                                        "title": "Error",
                                        "html": "Tidak ada soal ujian<br>Hubungi proktor<br>004",
                                        "icon": "error"
                                    });
                                }
                            } else {
                                // siswa logout ditengah ujian dan tidak melanjutkan sampai waktu ujian habis
                                // admin harus reset waktu
                                swal.fire({
                                    "title": "Error",
                                    "html": data.warn.msg + "<br>Hubungi proktor<br>003",
                                    "icon": "error"
                                });
                            }
                        } else {
                            // ditengah ujian, siswa ganti hape/komputer
                            // siswa tidak diizinkan ujian
                            // admin perlu reset izin
                            swal.fire({
                                "title": "Error",
                                "html": "Anda sedang mengerjakan ujian di perangkat lain<br>Hubungi proktor<br>002",
                                "icon": "error"
                            });
                        }
                    }
                } else {
                    // token salah, atau token tidak dibuat oleh admin
                    swal.fire({
                        "title": "Error",
                        "html": "TOKEN salah!<br>Hubungi proktor<br>001",
                        "icon": "error"
                    });
                }
            }, error: function (xhr, error, status) {
                swal.fire({
                    "title": "Error",
                    "html": "Coba kembali ke beranda, lalu ulangi lagi<br>006",
                    "icon": "error"
                });
                console.log(xhr.responseText);
            }
        });
    });
    //console.log('mnt', getMinutes('2023-01-30 11:30:30'));

    function getMinutes(d) {
        var startTime = new Date(d);
        var endTime = new Date();
        endTime.setHours(endTime.getHours() - startTime.getHours());
        endTime.setMinutes(endTime.getMinutes() - startTime.getMinutes());
        endTime.setSeconds(endTime.getSeconds() - startTime.getSeconds());

        return {h: endTime.getHours(), m: endTime.getMinutes(), s: endTime.getSeconds()}
    }

</script>
