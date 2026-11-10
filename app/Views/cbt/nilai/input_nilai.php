<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php

// Handle both bank and bank_soal structures
$bank = $jadwal->bank ?? $jadwal->bank_soal ?? (object) [];

$tampil_pg = $bank->tampil_pg ?? 0;
$tampil_kompleks = $bank->tampil_kompleks ?? 0;
$tampil_jodohkan = $bank->tampil_jodohkan ?? 0;
$tampil_isian = $bank->tampil_isian ?? 0;
$tampil_esai = $bank->tampil_esai ?? 0;
$tampil_urutan = $bank->tampil_urutan ?? 0;
$tampil_cloze = $bank->tampil_cloze ?? 0;

$bobot_pg = $bank->bobot_pg ?? 0;
$bobot_kompleks = $bank->bobot_kompleks ?? 0;
$bobot_jodohkan = $bank->bobot_jodohkan ?? 0;
$bobot_isian = $bank->bobot_isian ?? 0;
$bobot_esai = $bank->bobot_esai ?? 0;
$bobot_urutan = $bank->bobot_urutan ?? 0;
$bobot_cloze = $bank->bobot_cloze ?? 0;

?>
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
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Input Nilai') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h3 class="card-title fw-bold">
                    <i class="bi bi-pencil-square me-2 text-primary"></i> Input Nilai
                </h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary shadow-sm" onclick="simpan()">
                        <i class="bi bi-save me-2"></i>Simpan Nilai
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php
                $cols = [
                    $tampil_pg,
                    $tampil_kompleks,
                    $tampil_jodohkan,
                    $tampil_isian,
                    $tampil_esai,
                    $tampil_urutan,
                    $tampil_cloze
                ];

                $cols = array_filter($cols);
                ?>
                <div class="alert alert-light-danger border-danger shadow-sm mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2 fs-5"></i>
                        <h5 class="fw-bold mb-0">Perhatian!</h5>
                    </div>
                    <ul class="mb-0 small text-secondary">
                        <li>Halaman ini digunakan jika ingin menginput nilai tanpa koreksi otomatis.</li>
                        <li><b>Nilai Pilihan Ganda</b> tidak bisa diinput manual di halaman ini.</li>
                        <li>Jika hasil siswa sudah dikoreksi dan diberi nilai, maka nilai di halaman ini akan
                            <b>mengganti</b> nilai hasil koreksi.
                        </li>
                        <li>Mengisi nilai 0 di halaman ini tidak akan mengganti nilai hasil koreksi sebelumnya.</li>
                    </ul>
                </div>

                <div class="alert alert-light-success border-success shadow-sm mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-info-circle-fill text-success me-2 fs-5"></i>
                        <h6 class="fw-bold mb-0">Info Soal</h6>
                    </div>

                    <div class="row" id="info">
                        <div class="col-12 col-md-6 mb-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-secondary" width="120">Jenis Penilaian</td>
                                    <td width="10">:</td>
                                    <td class="fw-bold text-dark">
                                        <?= $jadwal->jenis->nama_jenis ?? $jadwal->kode_jenis ?? '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Mata Pelajaran</td>
                                    <td>:</td>
                                    <td class="fw-bold text-dark">
                                        <?= $bank->mapel->nama_mapel ?? $jadwal->nama_mapel ?? '-' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Kelas</td>
                                    <td>:</td>
                                    <td class="fw-bold text-dark"><?= $kelas->nama_kelas ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Pengampu</td>
                                    <td>:</td>
                                    <td class="fw-bold text-dark">
                                        <?= $bank->guru->nama_guru ?? $jadwal->nama_guru ?? '-' ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center align-middle mb-0"
                                    style="font-size: 0.9rem;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th colspan="<?= count($cols) ?>">Jumlah Soal per Jenis</th>
                                        </tr>
                                        <tr>
                                            <?php if ($tampil_pg != '0'): ?>
                                                <th>PG</th><?php endif; ?>
                                            <?php if ($tampil_kompleks != '0'): ?>
                                                <th>PGK</th><?php endif; ?>
                                            <?php if ($tampil_jodohkan != '0'): ?>
                                                <th>Jodoh</th><?php endif; ?>
                                            <?php if ($tampil_isian != '0'): ?>
                                                <th>Isian</th><?php endif; ?>
                                            <?php if ($tampil_esai != '0'): ?>
                                                <th>Esai</th><?php endif; ?>
                                            <?php if ($tampil_urutan != '0'): ?>
                                                <th>Urutan</th><?php endif; ?>
                                            <?php if ($tampil_cloze != '0'): ?>
                                                <th>Cloze</th><?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <?php if ($tampil_pg != '0'): ?>
                                                <td><span class="badge bg-primary fs-6"><?= $tampil_pg ?></span></td>
                                            <?php endif; ?>
                                            <?php if ($tampil_kompleks != '0'): ?>
                                                <td><span class="badge bg-primary fs-6"><?= $tampil_kompleks ?></span></td>
                                            <?php endif; ?>
                                            <?php if ($tampil_jodohkan != '0'): ?>
                                                <td><span class="badge bg-primary fs-6"><?= $tampil_jodohkan ?></span></td>
                                            <?php endif; ?>
                                            <?php if ($tampil_isian != '0'): ?>
                                                <td><span class="badge bg-primary fs-6"><?= $tampil_isian ?></span></td>
                                            <?php endif; ?>
                                            <?php if ($tampil_esai != '0'): ?>
                                                <td><span class="badge bg-primary fs-6"><?= $tampil_esai ?></span></td>
                                            <?php endif; ?>
                                            <?php if ($tampil_urutan != '0'): ?>
                                                <td><span class="badge bg-primary fs-6"><?= $tampil_urutan ?></span></td>
                                            <?php endif; ?>
                                            <?php if ($tampil_cloze != '0'): ?>
                                                <td><span class="badge bg-primary fs-6"><?= $tampil_cloze ?></span></td>
                                            <?php endif; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sm w-100 align-middle"
                        id="table-essai">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="50">No.</th>
                                <th style="min-width: 200px;">Peserta Didik</th>
                                <?php if ($tampil_pg != '0'): ?>
                                    <th>Nilai PG<br><span class="badge bg-secondary">Max: <?= $bobot_pg ?></span></th>
                                <?php endif;
                                if ($tampil_kompleks != '0'): ?>
                                    <th style="min-width: 150px;">Nilai PGK<br><span class="badge bg-secondary">Max:
                                            <?= $bobot_kompleks ?></span></th>
                                <?php endif;
                                if ($tampil_jodohkan != '0'): ?>
                                    <th style="min-width: 150px;">Nilai Jodohkan<br><span class="badge bg-secondary">Max:
                                            <?= $bobot_jodohkan ?></span></th>
                                <?php endif;
                                if ($tampil_isian != '0'): ?>
                                    <th style="min-width: 150px;">Nilai Isian<br><span class="badge bg-secondary">Max:
                                            <?= $bobot_isian ?></span></th>
                                <?php endif;
                                if ($tampil_esai != '0'): ?>
                                    <th style="min-width: 150px;">Nilai Esai<br><span class="badge bg-secondary">Max:
                                            <?= $bobot_esai ?></span></th>
                                <?php endif;
                                if ($tampil_urutan != '0'): ?>
                                    <th style="min-width: 150px;">Nilai Urutan<br><span class="badge bg-secondary">Max:
                                            <?= $bobot_urutan ?></span></th>
                                <?php endif;
                                if ($tampil_cloze != '0'): ?>
                                    <th style="min-width: 150px;">Nilai Cloze<br><span class="badge bg-secondary">Max:
                                            <?= $bobot_cloze ?></span></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $all_siswa = isset($is_rekap) ? $jadwal->rekapsiswa : $jadwal->sesisiswa;
                            foreach ($all_siswa as $val_siswa):
                                $siswa = $val_siswa->siswa;
                                $peserta = isset($is_rekap) ? ($val_siswa->no_peserta ?? '-') : ($val_siswa->peserta->nomor_peserta ?? '-');
                                $idSiswa = $siswa->id_siswa;
                                ?>
                                <tr class="nilai" data-idsiswa="<?= $idSiswa ?>">
                                    <td class="text-center align-middle"><?= $no ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">
                                                <?= $siswa->nama ?>
                                            </span>
                                            <span
                                                class="badge bg-warning text-dark align-self-start font-monospace mt-1 shadow-sm"><?= $peserta ?></span>
                                        </div>
                                    </td>
                                    <?php
                                    if (isset($val_siswa->nilai_akhir)):
                                        $nilai = $val_siswa->nilai_akhir;
                                        if ($tampil_pg != '0'): ?>
                                            <td class="text-center text-success fw-bold"><?= $nilai->skor_pg->skor_total ?? '' ?></td>
                                        <?php endif;
                                        if ($tampil_kompleks != '0'): ?>
                                            <td class="text-center">
                                                <div class="row gx-1 align-items-center justify-content-center">
                                                    <?php if (isset($nilai->skor_kompleks->skor_asli)): ?>
                                                        <div class="col-auto"><small
                                                                class="text-muted">Asal:</small><br><strong><?= $nilai->skor_kompleks->skor_asli ?? '' ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="col"><input
                                                            class="form-control form-control-sm text-center npg2 border-primary"
                                                            name="input-pg2" value="<?= $nilai->skor_kompleks->skor_total ?? '' ?>"
                                                            type="number" min="0" max="<?= $bobot_kompleks ?>" step="0.10" /></div>
                                                </div>
                                            </td>
                                        <?php endif;
                                        if ($tampil_jodohkan != '0'): ?>
                                            <td class="text-center">
                                                <div class="row gx-1 align-items-center justify-content-center">
                                                    <?php if (isset($nilai->skor_jodohkan->skor_asli)): ?>
                                                        <div class="col-auto"><small
                                                                class="text-muted">Asal:</small><br><strong><?= $nilai->skor_jodohkan->skor_asli ?? '' ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="col"><input
                                                            class="form-control form-control-sm text-center njodohkan border-primary"
                                                            name="input-jodohkan" value="<?= $nilai->skor_jodohkan->skor_total ?? '' ?>"
                                                            type="number" min="0" max="<?= $bobot_jodohkan ?>" step="0.10" /></div>
                                                </div>
                                            </td>
                                        <?php endif;
                                        if ($tampil_isian != '0'): ?>
                                            <td class="text-center">
                                                <div class="row gx-1 align-items-center justify-content-center">
                                                    <?php if (isset($nilai->skor_isian->skor_asli)): ?>
                                                        <div class="col-auto"><small
                                                                class="text-muted">Asal:</small><br><strong><?= $nilai->skor_isian->skor_asli ?? '' ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="col"><input
                                                            class="form-control form-control-sm text-center nisian border-primary"
                                                            name="input-isian" value="<?= $nilai->skor_isian->skor_total ?? '' ?>"
                                                            type="number" min="0" max="<?= $bobot_isian ?>" step="0.10" /></div>
                                                </div>
                                            </td>
                                        <?php endif;
                                        if ($tampil_esai != '0'): ?>
                                            <td class="text-center">
                                                <div class="row gx-1 align-items-center justify-content-center">
                                                    <?php if (isset($nilai->skor_essai->skor_asli)): ?>
                                                        <div class="col-auto"><small
                                                                class="text-muted">Asal:</small><br><strong><?= $nilai->skor_essai->skor_asli ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="col"><input
                                                            class="form-control form-control-sm text-center nessai border-primary"
                                                            name="input-essai" value="<?= $nilai->skor_essai->skor_total ?? '' ?>"
                                                            type="number" min="0" max="<?= $bobot_esai ?>" step="0.10" /></div>
                                                </div>
                                            </td>
                                        <?php endif;
                                        if ($tampil_urutan != '0'): ?>
                                            <td class="text-center">
                                                <div class="row gx-1 align-items-center justify-content-center">
                                                    <?php if (isset($nilai->skor_urutan->skor_asli)): ?>
                                                        <div class="col-auto"><small
                                                                class="text-muted">Asal:</small><br><strong><?= $nilai->skor_urutan->skor_asli ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="col"><input
                                                            class="form-control form-control-sm text-center nurutan border-primary"
                                                            name="input-urutan" value="<?= $nilai->skor_urutan->skor_total ?? '' ?>"
                                                            type="number" min="0" max="<?= $bobot_urutan ?>" step="0.10" /></div>
                                                </div>
                                            </td>
                                        <?php endif;
                                        if ($tampil_cloze != '0'): ?>
                                            <td class="text-center">
                                                <div class="row gx-1 align-items-center justify-content-center">
                                                    <?php if (isset($nilai->skor_cloze->skor_asli)): ?>
                                                        <div class="col-auto"><small
                                                                class="text-muted">Asal:</small><br><strong><?= $nilai->skor_cloze->skor_asli ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="col"><input
                                                            class="form-control form-control-sm text-center ncloze border-primary"
                                                            name="input-cloze" value="<?= $nilai->skor_cloze->skor_total ?? '' ?>"
                                                            type="number" min="0" max="<?= $bobot_cloze ?>" step="0.10" /></div>
                                                </div>
                                            </td>
                                        <?php endif;
                                    else: ?>
                                        <td colspan="<?= count($cols) + 1 ?>" class="text-center text-muted fst-italic py-3">
                                            Belum mengerjakan</td>
                                    <?php endif ?>
                                </tr>
                                <?php $no++; endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-end">
                    <button class="btn btn-primary shadow-sm" onclick="simpan()">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </div>

            <div class="overlay d-none d-flex align-items-center justify-content-center bg-white bg-opacity-75"
                id="loading" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 50;">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-primary fw-bold">Memproses Data...</div>
                </div>
            </div>
        </div>
    </div>
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
        let inputsValid = true;

        $.each($nilai, function () {
            const npg2 = parseFloat($(this).find('.npg2').val()) || 0;
            const njod = parseFloat($(this).find('.njodohkan').val()) || 0;
            const nis = parseFloat($(this).find('.nisian').val()) || 0;
            const nes = parseFloat($(this).find('.nessai').val()) || 0;
            const nur = parseFloat($(this).find('.nurutan').val()) || 0;
            const ncl = parseFloat($(this).find('.ncloze').val()) || 0;
            const id_siswa = $(this).data('idsiswa');

            idsSiswa.push(id_siswa);

            // Check validation
            // Note: Use loose comparison or proper checks if inputs are empty
            const checkMax = (val, max, label) => {
                if (val > max) {
                    Swal.fire({
                        title: "Error Input",
                        text: `Nilai ${label} tidak boleh melebihi max point (${max})`,
                        icon: "error"
                    });
                    inputsValid = false;
                    return false;
                }
                return true;
            };

            if (!checkMax(npg2, maxpg2, 'PG Kompleks')) return false;
            if (!checkMax(njod, maxjod, 'Jodohkan')) return false;
            if (!checkMax(nis, maxis, 'Isian')) return false;
            if (!checkMax(nes, maxes, 'Esai')) return false;
            if (!checkMax(nur, maxur, 'Urutan')) return false;
            if (!checkMax(ncl, maxcl, 'Cloze')) return false;

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

        if (!inputsValid) return;

        let dataPost = new FormData($('#koreksi')[0])
        dataPost.append('jadwal', <?= $jadwal->id_jadwal ?>)
        dataPost.append('bank_soal', <?= $jadwal->id_bank ?>)
        dataPost.append('nilai', JSON.stringify(json))
        dataPost.append('id_siswa', JSON.stringify(idsSiswa))

        if (json.length > 0) {
            loading.removeClass('d-none');
            // Swal loading already handled by overlay, but can keep Swal for consistency
            // Swal.fire({ ... }); 

            $.ajax({
                url: base_url + "cbtnilai/simpannilaiinput",
                type: "POST",
                data: dataPost,
                processData: false,
                contentType: false,
                success: function (data) {
                    console.log(data);
                    loading.addClass('d-none'); // Hide overlay

                    if (data.success > 0) {
                        Swal.fire({
                            title: "Berhasil",
                            text: "Nilai berhasil disimpan",
                            icon: "success",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else if (data.success === 0) {
                        Swal.fire({
                            title: "Berhasil",
                            text: "Tidak ada perubahan nilai yang disimpan",
                            icon: "info",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: "Gagal",
                            text: 'Terjadi kesalahan saat menyimpan nilai',
                            icon: "error"
                        });
                    }
                }, error: function (xhr, status, error) {
                    loading.addClass('d-none');
                    console.log("error", xhr.responseText);
                    let errMsg = "Terjadi kesalahan server";
                    try {
                        const err = JSON.parse(xhr.responseText);
                        errMsg = err.Message || errMsg;
                    } catch (e) { }

                    Swal.fire({
                        title: "Error",
                        text: errMsg,
                        icon: "error"
                    });
                }
            });
        }
    }
</script>
<?= $this->endSection() ?>