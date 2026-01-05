<?php


$tampil_pg = $jadwal->bank->tampil_pg ?? $jadwal->bank_soal->tampil_pg;
$tampil_kompleks = $jadwal->bank->tampil_kompleks ?? $jadwal->bank_soal->tampil_kompleks;
$tampil_jodohkan = $jadwal->bank->tampil_jodohkan ?? $jadwal->bank_soal->tampil_jodohkan;
$tampil_isian = $jadwal->bank->tampil_isian ?? $jadwal->bank_soal->tampil_isian;
$tampil_esai = $jadwal->bank->tampil_esai ?? $jadwal->bank_soal->tampil_esai;
$tampil_urutan = $jadwal->bank->tampil_urutan ?? $jadwal->bank_soal->tampil_urutan;
$tampil_cloze = $jadwal->bank->tampil_cloze ?? $jadwal->bank_soal->tampil_cloze;

$bobot_pg = $jadwal->bank->bobot_pg ?? $jadwal->bank_soal->bobot_pg;
$bobot_kompleks = $jadwal->bank->bobot_kompleks ?? $jadwal->bank_soal->bobot_kompleks;
$bobot_jodohkan = $jadwal->bank->bobot_jodohkan ?? $jadwal->bank_soal->bobot_jodohkan;
$bobot_isian = $jadwal->bank->bobot_isian ?? $jadwal->bank_soal->bobot_isian;
$bobot_esai = $jadwal->bank->bobot_esai ?? $jadwal->bank_soal->bobot_esai;
$bobot_urutan = $jadwal->bank->bobot_urutan ?? $jadwal->bank_soal->bobot_urutan;
$bobot_cloze = $jadwal->bank->bobot_cloze ?? $jadwal->bank_soal->bobot_cloze;

?>
<main class="app-main bg-white">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $judul ?></h1>
                </div>
                <div class="col-6">
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-end">
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
                <div class="card-body">
                    <?php
                    if (isset($is_rekap)) {
                        $cols = [
                            $jadwal->bank_soal->tampil_pg,
                            $jadwal->bank_soal->tampil_kompleks,
                            $jadwal->bank_soal->tampil_jodohkan,
                            $jadwal->bank_soal->tampil_isian,
                            $jadwal->bank_soal->tampil_esai,
                            $jadwal->bank_soal->tampil_urutan,
                            $jadwal->bank_soal->tampil_cloze
                        ];
                    } else {
                        $cols = [
                            $jadwal->bank->tampil_pg,
                            $jadwal->bank->tampil_kompleks,
                            $jadwal->bank->tampil_jodohkan,
                            $jadwal->bank->tampil_isian,
                            $jadwal->bank->tampil_esai,
                            $jadwal->bank->tampil_urutan,
                            $jadwal->bank->tampil_cloze
                        ];
                    }

                    $cols = array_filter($cols);
                    ?>
                    <div class="alert alert-default-danger">
                        <ul>
                            <li>
                                Halaman ini digunakan jika ingin menginput nilai tanpa koreksi.
                            </li>
                            <li>
                                <b>Nilai Pilihan Ganda</b> tidak bisa diinput manual.
                            </li>
                            <li>
                                Jika hasil siswa sudah dikoreksi dan diberi nilai, maka nilai di halaman ini akan
                                mengganti nilai hasil koreksi.
                            </li>
                            <li>
                                Nilai 0 di halaman ini tidak akan mengganti nilai hasil koreksi.
                            </li>
                        </ul>
                    </div>
                    <div class="alert alert-default-success border-success">
                        <h6><i class="icon bi bi-check"></i> Info Soal</h6>
                        <div class="row" id="info">
                            <div class="col-12 col-md-6 mb-2">
                                <table>
                                    <tr>
                                        <td>Jenis Penilaian</td>
                                        <td> :</td>
                                        <td><span
                                                class="text-bold"><?= $jadwal->jenis->nama_jenis ?? $jadwal->kode_jenis ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Mata Pelajaran</td>
                                        <td> :</td>
                                        <td><span
                                                class="text-bold"><?= $jadwal->bank->mapel->nama_mapel ?? $jadwal->nama_mapel ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Kelas</td>
                                        <td> :</td>
                                        <td><span class="text-bold"><?= $kelas->nama_kelas ?></span></td>
                                    </tr>
                                    <tr>
                                        <td>Pengampu</td>
                                        <td> :</td>
                                        <td><span
                                                class="text-bold"><?= $jadwal->bank->guru->nama_guru ?? $jadwal->nama_guru ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-12 col-md-6">
                                <table class="table table-bordered table-striped table-sm">
                                    <tr>
                                        <td colspan="<?= count($cols) ?>" class="text-center"
                                            style="border-color:black;">
                                            Jumlah Soal
                                        </td>
                                    </tr>
                                    <tr>
                                        <?php if ($tampil_pg != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                PG
                                            </td>
                                        <?php endif;
                                        if ($tampil_kompleks != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                PGK
                                            </td>
                                        <?php endif;
                                        if ($tampil_jodohkan != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                Jodohkan
                                            </td>
                                        <?php endif;
                                        if ($tampil_isian != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                Isian
                                            </td>
                                        <?php endif;
                                        if ($tampil_esai != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                Essai
                                            </td>
                                        <?php endif;
                                        if ($tampil_urutan != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                Urutan
                                            </td>
                                        <?php endif;
                                        if ($tampil_cloze != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                Cloze
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <?php if ($tampil_pg != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                <span class="text-bold" style="font-size: 16pt"><?= $tampil_pg ?></span>
                                            </td>
                                        <?php endif;
                                        if ($tampil_kompleks != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                <span class="text-bold"
                                                    style="font-size: 16pt"><?= $tampil_kompleks ?></span>
                                            </td>
                                        <?php endif;
                                        if ($tampil_jodohkan != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                <span class="text-bold"
                                                    style="font-size: 16pt"><?= $tampil_jodohkan ?></span>
                                            </td>
                                        <?php endif;
                                        if ($tampil_isian != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                <span class="text-bold" style="font-size: 16pt"><?= $tampil_isian ?></span>
                                            </td>
                                        <?php endif;
                                        if ($tampil_esai != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                <span class="text-bold" style="font-size: 16pt"><?= $tampil_esai ?></span>
                                            </td>
                                        <?php endif;
                                        if ($tampil_urutan != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                <span class="text-bold" style="font-size: 16pt"><?= $tampil_urutan ?></span>
                                            </td>
                                        <?php endif;
                                        if ($tampil_cloze != '0'): ?>
                                            <td class="text-center" style="border-color:black;">
                                                <span class="text-bold" style="font-size: 16pt"><?= $tampil_cloze ?></span>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm w-100" id="table-essai">
                            <tr>
                                <th class="text-center align-middle">
                                    No.
                                </th>
                                <th class="text-center align-middle">
                                    No. Peserta
                                </th>
                                <th class="align-middle">
                                    Nama
                                </th>
                                <?php if ($tampil_pg != '0'): ?>
                                    <th class="text-center align-middle">
                                        Nilai PG<br>
                                        Max. Point: <?= $bobot_pg ?>
                                    </th>
                                <?php endif;
                                if ($tampil_kompleks != '0'): ?>
                                    <th class="text-center align-middle">
                                        Nilai PG Kompleks<br>
                                        Max. Point: <?= $bobot_kompleks ?>
                                    </th>
                                <?php endif;
                                if ($tampil_jodohkan != '0'): ?>
                                    <th class="text-center align-middle">
                                        Nilai Menjodohkan<br>
                                        Max. Point: <?= $bobot_jodohkan ?>
                                    </th>
                                <?php endif;
                                if ($tampil_isian != '0'): ?>
                                    <th class="text-center align-middle">
                                        Nilai Isisan Singkat<br>
                                        Max.Point: <?= $bobot_isian ?>
                                    </th>
                                <?php endif;
                                if ($tampil_esai != '0'): ?>
                                    <th class="text-center align-middle">
                                        Nilai Essai<br>
                                        Max.Point: <?= $bobot_esai ?>
                                    </th>
                                <?php endif;
                                if ($tampil_urutan != '0'): ?>
                                    <th class="text-center align-middle">
                                        Nilai Urutan<br>
                                        Max.Point: <?= $bobot_urutan ?>
                                    </th>
                                <?php endif;
                                if ($tampil_cloze != '0'): ?>
                                    <th class="text-center align-middle">
                                        Nilai Cloze<br>
                                        Max.Point: <?= $bobot_cloze ?>
                                    </th>
                                <?php endif; ?>
                            </tr>
                            <?php
                            $no = 1;
                            $all_siswa = isset($is_rekap) ? $jadwal->rekapsiswa : $jadwal->sesisiswa;
                            foreach ($all_siswa as $val_siswa):
                                $siswa = $val_siswa->siswa;
                                $peserta = isset($is_rekap) ? ($val_siswa->no_peserta ?? '-') : ($val_siswa->peserta->nomor_peserta ?? '-');
                                $idSiswa = $siswa->id_siswa;
                                ?>
                                <tr class="nilai" data-idsiswa="<?= $idSiswa ?>">
                                    <td class="text-center align-middle"> <?= $no ?> </td>
                                    <td class="text-center align-middle"> <?= $peserta ?> </td>
                                    <td class="align-middle"> <?= $siswa->nama ?> </td>
                                    <?php
                                    if (isset($val_siswa->nilai_akhir)):
                                        $nilai = $val_siswa->nilai_akhir;
                                        if ($tampil_pg != '0'): ?>
                                            <td class="text-center text-success align-middle">
                                                <?= $nilai->pg_nilai ?? '' ?>
                                            </td>
                                        <?php endif;
                                        if ($tampil_kompleks != '0'): ?>
                                            <td class="text-center text-success align-middle">
                                                <?php if (isset($nilai->skor_kompleks['skor_asli'])): ?>
                                                    <div>
                                                        <span>Nilai Asal:</span>
                                                        <span class="text-bold"><?= $nilai->skor_kompleks['skor_asli'] ?? '' ?></span>
                                                    </div>
                                                    <input class="form-control npg2" name="input-pg2"
                                                        value="<?= $nilai->kompleks_nilai ?? '' ?>" type="number" min="0"
                                                        max="<?= $bobot_kompleks ?>" step="0.10" />
                                                <?php endif; ?>
                                            </td>
                                        <?php endif;
                                        if ($tampil_jodohkan != '0'): ?>
                                            <td class="text-success align-middle">
                                                <?php if (isset($nilai->skor_jodohkan['skor_asli'])): ?>
                                                    <div>
                                                        <span>Nilai Asal:</span>
                                                        <span class="text-bold"><?= $nilai->skor_jodohkan['skor_asli'] ?? '' ?></span>
                                                    </div>
                                                    <input class="form-control njodohkan" name="input-jodohkan"
                                                        value="<?= $nilai->jodohkan_nilai ?? '' ?>" type="number" min="0"
                                                        max="<?= $bobot_jodohkan ?>" step="0.10" />
                                                <?php endif; ?>
                                            </td>
                                        <?php endif;
                                        if ($tampil_isian != '0'): ?>
                                            <td class="text-center text-success align-middle">
                                                <?php if (isset($nilai->skor_isian['skor_asli'])): ?>
                                                    <div>
                                                        <span>Nilai Asal:</span>
                                                        <span class="text-bold"><?= $nilai->skor_isian['skor_asli'] ?? '' ?></span>
                                                    </div>
                                                    <input class="form-control nisian" name="input-isian"
                                                        value="<?= $nilai->isian_nilai ?? '' ?>" type="number" min="0"
                                                        max="<?= $bobot_isian ?>" step="0.10" />
                                                <?php endif; ?>
                                            </td>
                                        <?php endif;
                                        if ($tampil_esai != '0'): ?>
                                            <td class="text-center text-success align-middle">
                                                <?php if (isset($nilai->skor_essai['skor_asli'])): ?>
                                                    <div>
                                                        <span>Nilai Asal:</span>
                                                        <span class="text-bold"><?= $nilai->skor_essai['skor_asli'] ?></span>
                                                    </div>
                                                    <input class="form-control nessai" name="input-essai"
                                                        value="<?= $nilai->essai_nilai ?? '' ?>" type="number" min="0"
                                                        max="<?= $bobot_esai ?>" step="0.10" />
                                                <?php endif; ?>
                                            </td>
                                        <?php endif;
                                        if ($tampil_urutan != '0'): ?>
                                            <td class="text-center text-success align-middle">
                                                <?php if (isset($nilai->skor_urutan['skor_asli'])): ?>
                                                    <div>
                                                        <span>Nilai Asal:</span>
                                                        <span class="text-bold"><?= $nilai->skor_urutan['skor_asli'] ?></span>
                                                    </div>
                                                    <input class="form-control nurutan" name="input-urutan"
                                                        value="<?= $nilai->urutan_nilai ?? '' ?>" type="number" min="0"
                                                        max="<?= $bobot_urutan ?>" step="0.10" />
                                                <?php endif; ?>
                                            </td>
                                        <?php endif;
                                        if ($tampil_cloze != '0'): ?>
                                            <td class="text-center text-success align-middle">
                                                <?php if (isset($nilai->skor_cloze['skor_asli'])): ?>
                                                    <div>
                                                        <span>Nilai Asal:</span>
                                                        <span class="text-bold"><?= $nilai->skor_cloze['skor_asli'] ?></span>
                                                    </div>
                                                    <input class="form-control ncloze" name="input-cloze"
                                                        value="<?= $nilai->cloze_nilai ?? '' ?>" type="number" min="0"
                                                        max="<?= $bobot_cloze ?>" step="0.10" />
                                                <?php endif; ?>
                                            </td>
                                        <?php endif;
                                    else: ?>
                                        <td colspan="<?= count($cols) ?>" class="text-center align-middle">Belum mengerjakan
                                        </td>
                                    <?php endif ?>
                                </tr>
                                <?php $no++; endforeach; ?>
                        </table>
                    </div>
                    <button id="essai" class="float-end btn btn-sm btn-primary" onclick="simpan()">
                        <i class="bi bi-save me-2"></i>Simpan Nilai
                    </button>
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

    <?= form_open('update', array('id' => 'koreksi')) ?>
    <?= form_close() ?>

    <script>
        function simpan() {
            var loading = $(`#loading`);

            var maxpg2 = <?= $bobot_kompleks ?>;
            var maxjod = <?= $bobot_jodohkan ?>;
            var maxis = <?= $bobot_isian ?>;
            var maxes = <?= $bobot_esai ?>;
            var maxur = <?= $bobot_urutan ?>;
            var maxcl = <?= $bobot_cloze ?>;

            var $nilai = $(`#table-essai`).find('.nilai');
            var json = [];
            let idsSiswa = [];
            $.each($nilai, function () {
                const npg2 = $(this).find('.npg2').val();
                const njod = $(this).find('.njodohkan').val();
                const nis = $(this).find('.nisian').val();
                const nes = $(this).find('.nessai').val();
                const nur = $(this).find('.nurutan').val();
                const ncl = $(this).find('.ncloze').val();
                const id_siswa = $(this).data('idsiswa');
                idsSiswa.push(id_siswa);
                if (npg2 > maxpg2 || njod > maxjod || nis > maxis || nes > maxes || nur > maxur || ncl > maxcl) {
                    showDangerToast("Point persoal harus kurang dari nilai max.");
                    json = [];
                    return false;
                }

                let item = {};
                item['id_siswa'] = id_siswa;
                item['id_jadwal'] = '<?= $jadwal->id_jadwal ?>';
                item['kompleks_nilai'] = npg2;
                item['jodohkan_nilai'] = njod;
                item['isian_nilai'] = nis;
                item['essai_nilai'] = nes;
                item['urutan_nilai'] = nur;
                item['cloze_nilai'] = ncl;
                json.push(item);
            });

            let dataPost = new FormData($('#koreksi')[0])
            dataPost.append('jadwal', <?= $jadwal->id_jadwal ?>)
            dataPost.append('bank_soal', <?= $jadwal->id_bank ?>)
            dataPost.append('nilai', JSON.stringify(json))
            dataPost.append('id_siswa', JSON.stringify(idsSiswa))

            if (json.length > 0) {
                loading.removeClass('d-none');
                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
                            }
                });
                $.ajax({
                    url: base_url + "cbtnilai/simpannilaiinput",
                    type: "POST",
                    data: dataPost,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        console.log(data);
                        if (data.success > 0) {
                            Swal.fire({
                                title: "Berhasil",
                                text: "Nilai berhasil disimapn",
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            loading.addClass('d-none');
                            Swal.fire({
                                title: "Gagal",
                                text: 'Tidak ada nilai yang disimpan',
                                icon: "error"
                            });
                        }
                    }, error: function (xhr, status, error) {
                        loading.addClass('d-none');
                        console.log("error", xhr.responseText);
                        const err = JSON.parse(xhr.responseText)
                        Swal.fire({
                            title: "Error",
                            text: err.Message,
                            icon: "error"
                        });
                    }
                });
            }
        }
    </script>