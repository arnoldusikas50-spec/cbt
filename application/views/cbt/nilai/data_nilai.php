<?php


$colWidth = '';

$cols_name = ["PG", "PK", "JOD", "IS", "ES"];
?>

<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header align-items-center">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools d-none" id="group-btn">
                        <div class="d-flex flex-row">
                            <button type="button" id="mark-all"
                                    class="btn btn-success mr-1"
                                    data-toggle="tooltip"
                                    title="Tandai semua siswa sudah dikoreksi">
                                <i class="fa fa-check-double ml-1 mr-1"></i><span class="d-none d-md-inline-block ml-1"> Tandai sudah dikoreksi</span>
                            </button>
                            <button type="button" id="import-essai"
                                    class="btn btn-danger mr-1"
                                    data-toggle="tooltip"
                                    title="Input nilai manual tanpa koreksi">
                                <i class="fa fa-pencil ml-1 mr-1"></i><span class="d-none d-md-inline-block ml-1"> Input Nilai</span>
                            </button>
                            <button type="button" class="btn btn-danger mr-1" id="btn-katrol">
                                <i class="fa fa-balance-scale-right ml-1 mr-1"></i><span class="d-none d-sm-inline-block ml-1"> Katrol</span>
                            </button>
                            <div class="dropdown mr-1">
                                <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-file-excel ml-1 mr-1"></i><span class="d-none d-md-inline-block ml-1"> Excel</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <button id="download-nilai" class="btn dropdown-item" type="button"><i class="fas fa-download mr-2 text-red"></i>Nilai</button>
                                    <button id="download-detail" class="btn dropdown-item" type="button"><i class="fas fa-download mr-2 text-blue"></i>Detail</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $totalSiswa = isset($counts['siswa']) ? (int) $counts['siswa'] : (is_countable($siswas ?? []) ? count($siswas ?? []) : 0);
                    $totalSudahKoreksi = isset($counts['koreksi']) ? (int) $counts['koreksi'] : 0;
                    $totalPelanggaran = isset($counts['pelanggaran']) ? (int) $counts['pelanggaran'] : 0;
                    ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan Nilai CBT</h5>
                                <div class="text-muted">Lihat progres koreksi, input manual, dan pantau pengurangan nilai</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Peserta: <?= $totalSiswa ?></span>
                                <span class="badge badge-success px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Sudah Dikoreksi: <?= $totalSudahKoreksi ?></span>
                                <span class="badge badge-danger px-3 py-2 text-sm">Total Pelanggaran: <?= $totalPelanggaran ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengelolaan Nilai</h5>
                        <ul class="pl-3 mb-0">
                            <li>Gunakan filter kelas dan jadwal untuk memuat data peserta, lalu tekan <strong>Refresh</strong>.</li>
                            <li>Tombol <span class="badge badge-danger">Set Fouls</span> mengatur pengurangan nilai otomatis; gunakan tombol <strong>Foul</strong> di tabel untuk penyesuaian per siswa.</li>
                            <li>Unduh laporan nilai atau detail soal melalui menu Excel setelah koreksi selesai.</li>
                        </ul>
                    </div>

                    <div class="mb-3 row">
                        <div class="col-12">
                            
                        </div>
                        <div class="col-md-5 mb-3">
                            <div class="row">
                                <div class="col-6 <?= isset($is_rekap) ? 'd-none' : '' ?>" id="by-kelas">
                                    <label>Kelas</label>
                                    <?php
                                    echo form_dropdown('kelas', $kelas, $kelas_selected, 'id="kelas" class="form-control"'); ?>
                                </div>
                                <div class="col-6 <?= isset($is_rekap) ? 'd-none' : '' ?>">
                                    <label>Jadwal</label>
                                    <?php
                                    echo form_dropdown('jadwal', $jadwal ?? [], $jadwal_selected, 'id="jadwal" class="form-control"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-7 <?= isset($is_rekap) ? '' : 'col-md-6' ?> align-self-end text-right mb-3">
                            <button type="button" class="btn btn-primary btn-sm"
                                    onclick="refreshStatus()"
                                    data-toggle="tooltip"
                                    title="Refresh">
                                <i class="fa fa-sync"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="set-fouls"
                                    data-toggle="modal" data-target="#penguranganModal"
                                    title="Pengurangan Nilai">
                                <i class="fa fa-scissors"></i> Set Fouls
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" id="reset-fouls" onclick="resetFouls()">
                                <i class="fa fa-undo"></i> Reset</button>
                        </div>
                    </div>
                    <?php if (!isset($is_rekap)) { echo '<hr />'; } ?>
                    <div id="table-container"></div>
                </div>
                <div class="overlay" id="loading">
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
            <?= form_open('', array('id' => 'perbaikan-nilai')) ?>
            <div class="modal-body">
                <div class="form-group row">
                    <label for="nama_sesi" class="col-md-4 col-form-label">Nilai Tertinggi</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="ya" value="100"
                               placeholder="Nilai tertinggi yang diinginkan" required>
                        <small>diisi nilai puluhan maksimal 100, misal 80 sampai 100</small>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="kode_sesi" class="col-md-4 col-form-label">Nilai Terrendah</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="yb" value="60"
                               placeholder="Nilai terrendah yang diinginkan" required>
                        <small>diisi nilai puluhan dibawah nilai tertinggi, misal 60</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="convert">Katrol <i class="fa fa-arrow-right"></i>
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="penguranganModal" tabindex="-1" role="dialog" aria-labelledby="penguranganLabel" aria-hidden="true">
    <div class="modal-dialog modal-xs" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="penguranganLabel">Atur Pengurangan Nilai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Pengurangan Point akan diterapkan ke seluruh siswa yang melakukan pelanggaran (Pengurangan Point X jumlah pelanggaran).
                    tetapi masih bisa merubahnya di masing-masing siswa</p>
                <table class="table w-100 table-bordered">
                    <tr class="bg-success">
                        <th class="align-middle">Jenis Pelanggaran</th>
                        <th>Pengurangan Point</th>
                    </tr>
                    <tr class="alert alert-default-info">
                        <td class="align-middle">Pelanggaran Ringan</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm" value="0" step="0.5"
                                   min="0" id="p-ringan" style="max-width: 80px">
                        </td>
                    </tr>
                    <tr class="alert alert-default-warning">
                        <td class="align-middle">Pelanggaran Sedang</td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm" value="0" step="0.5"
                                   min="0" id="p-sedang" style="max-width: 80px">
                        </td>
                    </tr>
                   <tr class="alert alert-default-danger">
                        <td class="align-middle">Pelanggaran Berat</td>
                        <td class="align-middle">
                            <input type="number" class="form-control form-control-sm" value="0" step="0.5"
                                   min="0" id="p-berat" style="max-width: 80px">
                        </td>
                    </tr>
                </table>
                <p class="alert alert-default-success">
                    Pengaturan ini tidak akan diterapkan ke siswa yang sudah diatur secara manual.
                </p>
                <div class="icheck-cyan">
                    <input type="checkbox" id="reset-all" name="reset-all">
                    <label for="reset-all">Terapkan juga ke siswa yang sudah diatur manual</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                <button class="btn btn-primary" id="save-min">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pelanggaranModal" tabindex="-1" role="dialog" aria-labelledby="pelanggaranLabel" aria-hidden="true">
    <div class="modal-dialog modal-xs" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pelanggaranLabel">Pelanggaran Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table w-100 table-bordered">
                    <tr class="bg-light">
                        <th class="align-middle">Jenis Pelanggaran</th>
                        <th>Jml. Pelanggaran x Pengurangan Nilai</th>
                    </tr>
                    <tr>
                        <td>
                            Pelanggaran Ringan
                            <ul>
                                <li>
                                    Memulai ujian dengan tampilan tidak fullscreen
                                </li>
                            </ul>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-row align-items-center mb-2">
                                <span id="jml-ringan" class="text-nowrap text-bold"></span><span class="ml-1 text-bold">x</span>
                                <input type="number" class="form-control form-control-sm mx-2" value="" step="0.5"
                                       min="0" id="np-ringan" style="max-width: 80px">
                            </div>
                            <span class="mr-1">dikurangi</span>
                            <span id="t-ringan" class="text-danger text-bold">0</span> point
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Pelanggaran Sedang
                            <ul>
                                <li>
                                    Mencoba keluar dari fullscreen
                                </li>
                            </ul>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-row align-items-center mb-2">
                                <span id="jml-sedang" class="text-nowrap text-bold"></span><span class="ml-1 text-bold">x</span>
                                <input type="number" class="form-control form-control-sm mx-2" value="" step="0.5"
                                       min="0" id="np-sedang" style="max-width: 80px">
                            </div>
                            <span class="mr-1">dikurangi</span>
                            <span id="t-sedang" class="text-danger text-bold">0</span> point
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Pelanggaran Berat
                            <ul>
                                <li>
                                    Mencoba membuka tab baru
                                </li>
                                <li>
                                    Keluar dari aplikasi sebelum selesai
                                </li>
                                <li>
                                    Me-minimize browser
                                </li>
                            </ul>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-row align-items-center mb-2">
                                <span id="jml-berat" class="text-nowrap text-bold"></span><span class="ml-1 text-bold">x</span>
                                <input type="number" class="form-control form-control-sm mx-2" value="" step="0.5"
                                       min="0" id="np-berat" style="max-width: 80px">
                            </div>
                            <span class="mr-1">dikurangi</span>
                            <span id="t-berat" class="text-danger text-bold">0</span> point
                        </td>
                    </tr>
                    <tr class="bg-gray-light">
                        <td colspan="2">
                            <div>
                                Nilai Siswa: <span id="min-nilai" class="text-bold"></span>
                            </div>
                            <div>
                                Pengurangan Point:
                                <span class="text-bold text-danger" id="total-pengurangan"></span>
                            </div>
                            <div>
                                Nilai siswa setelah pengurangan:
                                <span class="text-bold text-danger" id="hasil-pengurangan"></span>
                            </div>
                        </td>
                    </tr>
                </table>
                <div>
                <small> Kode id siswa pada database
                    <a class="alert-default-danger">[ 
                <span id="min-idsiswa"></span>
                ]</a>
                </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="save-fouls">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'post_data')) ?>
<?= form_close(); ?>

<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/tableToExcel.js"></script>

<script>
    const limitAntrian = 500
    const limitPost = 20

    var colWidthMin = '5,30,35,15,10,15,10,5,5,10';
    var colWidth = '<?= $colWidth ?>';

    var idJadwal = '<?=$jadwal_selected?>';
    var idKelas = '<?=$kelas_selected?>';
    var isSelected = <?= $kelas_selected ?? 0?>;
    const isRekap = <?=isset($is_rekap) ? 1 : 0?>;
    var kodeMapel = '';
    var namaMapel = '';
    var jenisUjian = '';
    let arr_idSiswa = [];
    let arr_pelanggaran = [];
    let isBackFrom = false;

    let nilai_max = 0;//nilai siswa terbesar
    let nilai_min = 100;//nilai siswa terkecil
    let hasil_max = 100;//batas nilai terbesar
    let hasil_min = 60;//batas nilai terkecil
    let arrNilaiSiswa = []
    let isConverted = false
    let result;

    async function ajaxGet(url) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    type: "GET",
                    success: function (respon) {
                        resolve(respon); // Resolusi Promise ketika sukses
                    },
                    error: function (xhr, status, error) {
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 100)
        });
    }

    async function ajaxPost(url, formData) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    method: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (respon) {
                        if (respon.success) {
                            resolve(respon); // Resolusi Promise ketika sukses
                        } else {
                            reject(respon.message); // Rejeksi Promise ketika ada error
                        }
                    },
                    error: function (xhr, status, error) {
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 500)
        });
    }

    function refreshStatus() {
        const jadwal = isRekap ? idJadwal : $('#jadwal').val();
        const kelas = isRekap ? idKelas : $('#kelas').val()
        console.log('refresh', jadwal, kelas);
        if (jadwal && kelas) {
            let post = {
                'id_jadwal': jadwal,
                'id_kelas': kelas
            }
            getNilai(post)
        }
    }

    function lihatJawaban(idSiswa) {
        let post = {
            'id_jadwal': idJadwal,
            'id_kelas': isRekap ? idKelas : $("#kelas").val()
        }
        localStorage.setItem('garudaCBT.convert', JSON.stringify(post))
        localStorage.setItem('garudaCBT.last_url', 'input_nilai')
        if (isRekap) {
            window.location.href = base_url + 'cbtrekap/koreksi?id_siswa=' + idSiswa + '&id_jadwal=' + idJadwal;
        } else {
            window.location.href = base_url + 'cbtnilai/koreksi?id_siswa=' + idSiswa + '&id_jadwal=' + idJadwal;
        }
    }

    function getDetailJadwal(idJadwal) {
        $.ajax({
            type: "GET",
            url: base_url + "cbtstatus/getjadwalujianbyjadwal?id_jadwal=" + idJadwal,
            cache: false,
            success: function (response) {
                console.log(response);
                var selKelas = $('#kelas');
                selKelas.html('');
                selKelas.append('<option value="">Pilih Kelas</option>');
                $.each(response, function (k, v) {
                    if (v != null) {
                        selKelas.append('<option value="' + k + '">' + v + '</option>');
                    }
                });
            }
        });
    }

    function getKelasJadwal(idKelas) {
        $.ajax({
            type: "GET",
            url: base_url + "cbtstatus/getjadwalujianbykelas?id_kelas=" + idKelas,
            cache: false,
            success: function (response) {
                console.log(response);

                var selJadwal = $('#jadwal');
                selJadwal.html('');
                selJadwal.append('<option value="">Pilih Jadwal</option>');
                $.each(response, function (k, v) {
                    if (v != null) {
                        selJadwal.append('<option value="' + k + '">' + v + '</option>');
                    }
                });
                if (isBackFrom) {
                    selJadwal.val(idJadwal).trigger('change')
                    isBackFrom = false;
                }
            }
        });
    }

    function downloadExcel(detail) {
        $('#no-soal-tile').attr('data-exclude', '' + !detail);
        $('.no-soal').attr('data-exclude', '' + !detail);
        $('.no-soal-siswa').attr('data-exclude', '' + !detail);

        var width = $('#table-status').attr('data-cols-width');
        $('#table-status').attr('data-cols-width', detail ? colWidth : colWidthMin)

        var table = document.querySelector("#table-status");
        const nilaiDetail = detail ? 'Nilai Detail' : 'Nilai';
        if (table != null) {
            TableToExcel.convert(table, {
                name: `${nilaiDetail} ${jenisUjian} ${$("#kelas option:selected").text()} ${namaMapel}.xlsx`,
                sheet: {
                    name: "Sheet 1"
                }
            });
        } else {
            Swal.fire({
                title: "ERROR",
                text: "Isi JADWAL dan KELAS terlebih dulu",
                icon: "error"
            })
        }
    }

    async function getNilai(post) {
        isConverted = false;
        $('#group-btn').addClass('d-none');
        if (!isRekap && (!post || !post.id_jadwal)) {
            $('#loading').addClass('d-none');
            return;
        }
        document.getElementById('table-container').innerHTML = '';
        $('#loading').removeClass('d-none');
        let formData = new FormData($('#post_data')[0]);

        if (post) {
            for (const key in post) {
                formData.append(key, post[key]);
            }
        } else {
            formData.append(`id_jadwal`, idJadwal);
            formData.append(`id_kelas`, idKelas);
        }

        if (isRekap) {
            const response = await ajaxPost('cbtrekap/get_nilai_rekap', formData)
            console.log('responseRekapSiswa', response);

            idKelas = response.kelas_selected
            idJadwal = response.jadwal_selected
            result = {
                jadwal: {
                    ...response.rekap.jadwal,
                    bank: response.rekap.bank_soal,
                    sesisiswa: response.rekap.rekapsiswa,
                    soal_pg: response.rekap.soal_pg
                },
                nama_mapel: response.rekap.nama_mapel,
                kelas: response.kelas,
                nama_guru: response.rekap.nama_guru,
                kode_jenis: response.rekap.kode_jenis,
                durasi_siswa: response.durasi_siswa
            }
        } else {
            const response = await ajaxPost('cbtnilai/get_nilai_siswa', formData)
            console.log('responseCbtSiswa', response);

            idKelas = response.kelas_selected
            idJadwal = response.jadwal_selected
            result = response
        }
        await populateTable()
    }

    async function populateTable() {
        console.log('populateTable', result.jadwal);
        $('#set-fouls').attr('disabled', result.jadwal?.pelanggaran != '1');
        $('#reset-fouls').attr('disabled', result.jadwal?.pelanggaran != '1');
        const cols_name = ["PG", "PK", "JOD", "IS", "ES"];
        const durasiSiswa = result.durasi_siswa ?? []
        const siswas = result.jadwal.sesisiswa
        const info = result.jadwal.bank
        const kelas = result.kelas
        namaMapel = info.mapel?.nama_mapel ?? result.nama_mapel;
        kodeMapel = info.mapel?.kode_mapel ?? result.kode_mapel
        jenisUjian = result.jadwal.jenis?.kode_jenis ?? result.kode_jenis
        arr_pelanggaran = [];
        if (Array.isArray(siswas) && siswas.length > 0) {
            siswas.sort((a, b) => a?.nomor_peserta?.localeCompare(b?.nomor_peserta, undefined, {numeric: true}))
            siswas.sort((a, b) => a?.peserta?.nomor_peserta?.localeCompare(b?.peserta?.nomor_peserta, undefined, {numeric: true}))
            //siswas.sort((a, b) => a?.siswa?.nama?.localeCompare(b?.siswa?.nama, undefined, {numeric: true}))
            let cols = [info.tampil_pg, info.tampil_kompleks, info.tampil_jodohkan, info.tampil_isian, info.tampil_esai];
            cols = cols.filter(function (count){return count != 0});
            let rows = cols.length > 1 ? 1 : 2;

            colWidth = '5,15,35,15,10,15,10';
            colWidth += ',4'.repeat(info.tampil_pg);
            colWidth += ',5,5,10';

            let totalSoal = Number(info.tampil_pg) + Number(info.tampil_kompleks) + Number(info.tampil_jodohkan) + Number(info.tampil_isian) + Number(info.tampil_esai);
            let html = `<div class="table-responsive">
                        <table class="w-100 table table-sm table-bordered" id="table-status" data-cols-width="${colWidth}">
                            <tr>
                                <td class="alert-default-success" colspan="2">Mata Pelajaran</td>
                                <td class="alert-default-info" colspan="7">${namaMapel}</td>
                            </tr>
                            <tr>
                                <td class="alert-default-success" colspan="2">Kelas</td>
                                <td class="alert-default-info" colspan="7">${kelas[idKelas]}</td>
                            </tr>
                            <tr>
                                <td class="alert-default-warning" colspan="2">Guru</td>
                                <td class="alert-default-primary" colspan="7">${result.nama_guru ?? info.guru.nama_guru}</td>
                            </tr>
                            <tr>
                                <td class="alert-default-warning" colspan="2" style="width: 120px">Soal</td>
                                <td class="alert-default-primary" colspan="7">${info.bank_kode}</td>
                            </tr>
                            <tr>
                                <td class="alert-default-danger" colspan="2" class="align-top">Jumlah Soal</td>
                                <td class="alert-default-danger" colspan="7" class="align-top">${totalSoal}</td>
                            </tr>
                            <tr><td class="border-0" height="40"></tr>
                            <tr class="bg-light">
                                <th rowspan="2" class="text-center align-middle" width="40" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >No.</th>
                                <th rowspan="2" class="text-center align-middle" width="100" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >No. Peserta</th>
                                <th rowspan="2" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Nama</th>
                                <th rowspan="2" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true">Sesi</th>
                                <th rowspan="2" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true">Ruang</th>
                                <th rowspan="2" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true">Mulai</th>
                                <th rowspan="2" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true">Durasi</th>`;

            if (info.tampil_pg > 0) {
                html += `<th id="no-soal-tile" colspan="${info.tampil_pg}" class="text-center align-middle d-none" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Nomor Soal PG</th>
                 <th colspan="2" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >PG</th>`;
            }

            html += `<th colspan="${cols.length}" rowspan="${rows}" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Skor ${cols.length > 1 ? "" : cols_name[Object.keys(cols)[0]]}</th>
             <th colspan="3" class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Nilai</th>
             <th rowspan="2" class="text-center align-middle" data-exclude="true" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Aksi</th>
             </tr>
             <tr class="bg-light">`;

            for (let js = 0; js < info.tampil_pg; js++) {
                html += `<th class="no-soal text-center align-middle p-1 d-none" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >${js + 1}</th>`;
            }

            if (info.tampil_pg > 0) {
                html += `<th class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >B</th>
                 <th class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true">S</th>`;
            }

            if (rows === 1) {
                if (info.tampil_pg > 0) {
                    html += `<th class="text-center align-middle p-1" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >PG</th>`;
                }
                if (info.tampil_kompleks > 0) {
                    html += `<th class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >PK</th>`;
                }
                if (info.tampil_jodohkan > 0) {
                    html += `<th class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >JO</th>`;
                }
                if (info.tampil_isian > 0) {
                    html += `<th class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >IS</th>`;
                }
                if (info.tampil_esai > 0) {
                    html += `<th class="text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >ES</th>`;
                }
            }

            // Append additional score columns
            html += `<th class="alert-default-primary text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Asli</th>
             <th class="alert-default-success text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Katrol</th>
             <th class="alert-default-danger text-center align-middle" data-fill-color="b8daff" data-a-v="middle" data-a-h="center" data-b-a-s="thin" data-f-bold="true" >Minus</th>
             </tr>`;

            const hanyaPg = info.tampil_pg > 0 && info.tampil_kompleks == 0
                && info.tampil_jodohkan == 0 && info.tampil_isian == 0
                && info.tampil_esai == 0;

            arr_idSiswa = [];
            let no = 1;
            siswas.forEach((siswa) => {
                let idSiswa = siswa?.siswa?.id_siswa || siswa.id_siswa;
                let sudah_hadir = siswa?.durasi?.mulai || false;

                let disable = (!sudah_hadir || siswa?.durasi?.mulai_ujian?.includes('-')) ?? true;
                let disableKoreksi = disable || siswa?.durasi?.selesai === '';
                let disabled = disableKoreksi ? 'disabled' : '';

                const idDikoreksi = {
                    id_siswa: idSiswa,
                    memulai: disable ? '2' : (siswa?.nilai_akhir?.dikoreksi ? '1' : '0')
                }
                arr_idSiswa.push(idDikoreksi);

                const mulai = () => {
                    const start = result?.jadwal?.tgl_mulai?.replaceAll('-', '');
                    const time = siswa?.durasi?.mulai?.split(' ')[0].replaceAll('-', '');
                    const timeSiswa = new Date(siswa?.durasi?.mulai ?? '');
                    if (!isNaN(timeSiswa.getTime())) {
                        let susulan = '';
                        if (time > start) susulan = '<br /><span class="text-xs">susulan</span>';
                        return `${zeroPad(timeSiswa.getHours(), 2)}:${zeroPad(timeSiswa.getMinutes(), 2)} ${susulan}`;
                    } else {
                        return '- : -';
                    }
                };

                html += `<tr>
        <td class="text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin">${no}</td>
        <td class="text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin" style="min-width: 220px">${siswa?.no_peserta ?? siswa?.peserta?.nomor_peserta ?? '-'}</td>
        <td class="align-middle" data-a-v="middle" data-b-a-s="thin">${siswa?.siswa?.nama ?? siswa.nama_siswa}</td>
        <td class="text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin">${siswa?.sesi?.kode_sesi ?? siswa?.ruang_sesi?.sesi?.kode_sesi ?? '-'}</td>
        <td class="text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin">${siswa?.ruang?.kode_ruang ?? siswa?.ruang_sesi?.ruang?.kode_ruang ?? '-'}</td>
        <td class="text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin">${mulai()}</td>
        <td class="text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin">${siswa?.durasi?.status_ujian ?? ''}</td>`;

                let benar_pg = 0;
                let salah_pg = 0;
                let jawaban_pg = siswa?.nilai_akhir?.skor_pg?.detail ?? [];

                result?.jadwal?.soal_pg?.forEach((soal) => {
                    const num = soal.nomor_soal
                    const jwb = jawaban_pg.find(j => j.nomor === num);
                    let bg = '';
                    if (jwb) {
                        if (jwb?.benar) {
                            bg = 'data-fill-color="00E676"';
                            benar_pg++;
                        } else {
                            bg = 'data-fill-color="FF7043"';
                            salah_pg++;
                        }
                    }
                    html += `<td class="no-soal-siswa d-none" data-a-v="middle" data-a-h="center" data-b-a-s="thin" ${bg}>${jwb?.jawaban_siswa ?? ''}</td>`;
                });

                if (info.tampil_pg > 0) {
                    html += `<td class="text-success text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : benar_pg}</b></td>
                 <td class="text-danger text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : salah_pg}</b></td>
                 <td class="text-center text-info align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : (siswa?.nilai_akhir?.skor_pg?.skor_total ?? 0)}</b></td>`;
                }

                if (info.tampil_kompleks > 0) {
                    html += `<td class="text-center text-success align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : (siswa?.nilai_akhir?.skor_kompleks?.skor_total ?? 0)}</b></td>`;
                }

                if (info.tampil_jodohkan > 0) {
                    html += `<td class="text-center text-success align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : (siswa?.nilai_akhir?.skor_jodohkan?.skor_total ?? 0)}</b></td>`;
                }

                if (info.tampil_isian > 0) {
                    html += `<td class="text-center text-success align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : (siswa?.nilai_akhir?.skor_isian?.skor_total ?? 0)}</b></td>`;
                }

                if (info.tampil_esai > 0) {
                    html += `<td class="text-center text-success align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : (siswa?.nilai_akhir?.skor_essai?.skor_total ?? 0)}</b></td>`;
                }

                let iconKoreksi = '';
                if (hanyaPg || siswa?.nilai_akhir?.dikoreksi === '1') {
                    iconKoreksi = `<i title="Sudah dikoreksi" class="fa fa-check-circle ml-1"></i>`;
                } else {
                    iconKoreksi = `<i title="Belum dikoreksi" class="fa fa-warning text-warning ml-1"></i>`;
                }

                const pel = siswa?.pelanggaran
                if (pel) {
                    const siswaFoul = {
                        'id_siswa': idSiswa,
                        'id_cbt_siswa': siswa.id_cbt_siswa || '',
                        'id_rekap_siswa': siswa.id_rekap_siswa || '',
                        'pelanggaran': pel
                    }
                    arr_pelanggaran.push(siswaFoul);
                }

                const textColor = result.jadwal?.pelanggaran === '1' && pel?.t_total != null && pel?.t_total > 0 ? 'text-danger' : 'text-black';
                const totalMinus = result?.jadwal?.pelanggaran === '1' ? (pel?.t_total || 0) : 0
                const skorAsli = siswa?.nilai_akhir?.skor_total || 0;
                const skorTotal = skorAsli - totalMinus;
                let skorKatrol = '';

                if (isConverted) {
                    if (siswa?.durasi?.status_ujian) skorKatrol = (((hasil_max - hasil_min) / 100) * decimalFixed(skorTotal)) + hasil_min;
                    if (skorKatrol !== '') skorKatrol = decimalFixed(skorKatrol);
                } else {
                    if (decimalFixed(skorTotal) > nilai_max) {
                        nilai_max = decimalFixed(skorTotal);
                    }
                    if (decimalFixed(skorTotal) < nilai_min) {
                        nilai_min = decimalFixed(skorTotal);
                    }
                }

                let disableBtn = result.jadwal?.pelanggaran !== '1' ? 'disabled' : '';
                if (!isRekap && !siswa.id_cbt_siswa) {
                    disableBtn = 'disabled'
                }

                html += `<td class="text-center align-middle ${textColor}" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${disable ? '' : skorTotal}</b></td>
             <td class="text-center align-middle katrol ${textColor}" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${skorKatrol}</b></td>
             <td class="text-center align-middle" data-a-v="middle" data-a-h="center" data-b-a-s="thin"><b>${totalMinus}</b></td>
             <td class="text-center align-middle" data-exclude="true">
                 <button type="button" class="btn btn-xs btn-success mb-1 ${disabled}" onclick="lihatJawaban(${idSiswa})" title="Koreksi Jawaban Siswa" ${disabled}>Koreksi ${iconKoreksi}</button>
                 <button type="button" class="btn btn-xs btn-danger mb-1" data-toggle="modal" data-nilai="${skorAsli}"
                        data-target="#pelanggaranModal" data-siswa="${idSiswa}" ${disableBtn}>
                    <i class="fa fa-scissors"></i> Foul</button>
             </td>
         </tr>`;

                no++;
            });

            html += `</table></div>`;

            // Assuming you want to inject this into a specific div
            document.getElementById('table-container').innerHTML = html;

            if (isConverted) {
                $('#btn-katrol').html('<i class="fa fa-undo ml-1 mr-1"></i>' +
                    '<span class="d-none d-sm-inline-block ml-1"> Nilai Asli</span>')
            } else {
                $('#btn-katrol').html('<i class="fa fa-balance-scale-right ml-1 mr-1"></i>' +
                    '<span class="d-none d-sm-inline-block ml-1"> Katrol</span>')
            }
            $('#group-btn').removeClass('d-none');
        } else {
            const title = isRekap ? 'Belum ada rekap nilai:' : 'Tidak ada nilai Siswa:'
            const li_2 = isRekap ? 'Ulangi rekap jika semua siswa sudah selesai mengerjakan' : 'Refresh halaman jika siswa sedang mengerjakan'
            document.getElementById('table-container').innerHTML = `<div class="alert alert-default-warning align-content-center pb-0 m-2" role="alert">
                ${title}
                <ul>
                    <li>Ada siswa belum selesai mengerjakan</li>
                    <li>${li_2}</li>
                </ul>
            </div>`;
        }

        $('#loading').addClass('d-none');
    }

    function perbaikiNilai(fromModal) {
        if (!isConverted && !fromModal) {
            $('#perbaikanModal').modal('show')
            return
        }
        isConverted = !isConverted;
        populateTable()
    }

    function decimalFixed(num) {
        return Math.round(num * 100) / 100
    }

    $(document).ready(function () {
        ajaxcsrf();

        const loading = $('#loading');
        var opsiJadwal = $("#jadwal");
        var opsiKelas = $("#kelas");
        var selected = isSelected === 0 ? "selected='selected'" : "";
        opsiJadwal.prepend("<option value='' " + selected + " disabled>Pilih Jadwal</option>");
        opsiKelas.prepend("<option value='' " + selected + " disabled>Pilih Kelas</option>");

        opsiKelas.change(function () {
            getKelasJadwal($(this).val());
        });

        opsiJadwal.change(function () {
            let post = {
                'id_jadwal': isRekap ? idJadwal : $(this).val(),
                'id_kelas': isRekap ? idKelas : opsiKelas.val()
            }
            getNilai(post)
        });

        $('#btn-katrol').on('click', function (e) {
            perbaikiNilai(false)
        });

        $("#download-nilai").click(function (event) {
            downloadExcel(false)
        });

        $("#download-detail").click(function (event) {
            downloadExcel(true)
        });

        $('#perbaikan-nilai').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var table = document.querySelector("#table-status");
            var $inputs = $('#perbaikanModal :input');
            var values = {};
            $inputs.each(function () {
                if ($(this).val()) values[this.name] = $(this).val();
            });
            hasil_max = Number(values['ya']);
            hasil_min = Number(values['yb']);

            $('#perbaikanModal').modal('hide').data('bs.modal', null);
            $('#perbaikanModal').on('hidden', function () {
                $(this).data('modal', null);  // destroys modal
            });

            perbaikiNilai(true)

        });

        $("#import-essai").on("click", function () {
            var kls = opsiKelas.val();
            var jad = opsiJadwal.val();
            if (!isRekap && (kls = null || jad == null)) {
                return;
            }
            let post = {
                'id_jadwal': isRekap ? idJadwal : $("#jadwal").val(),
                'id_kelas': isRekap ? idKelas : $("#kelas").val()
            }
            localStorage.setItem('garudaCBT.convert', JSON.stringify(post))
            localStorage.setItem('garudaCBT.last_url', 'input_nilai')
            if (isRekap) {
                window.location.href = base_url + 'cbtrekap/inputessai?id_kelas=' + idKelas + '&id_jadwal=' + idJadwal;
            } else {
                window.location.href = base_url + 'cbtnilai/inputessai?id_kelas=' + opsiKelas.val() + '&id_jadwal=' + opsiJadwal.val();
            }
        });

        $('#mark-all').on('click', function () {
            swal.fire({
                title: "Tandai Semua",
                text: "Semua hasil siswa akan ditandai sudah dikoreksi",
                icon: "info",
                showCancelButton: true,
            }).then(result => {
                if (result.value) {
                    let dataPost = new FormData($('#post_data')[0])
                    dataPost.append('id_jadwal', idJadwal)
                    dataPost.append('id_siswa', JSON.stringify(arr_idSiswa))
                    loading.removeClass('d-none');
                    swal.fire({
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        onOpen: () => {
                            swal.showLoading();
                        }
                    });
                    $.ajax({
                        url: base_url + "cbtnilai/mark_all",
                        type: "POST",
                        data: dataPost,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            loading.addClass('d-none');
                            console.log('responseMarkAll', data);
                            if (data.success) {
                                refreshStatus()
                                swal.fire({
                                    title: "Berhasil",
                                    text: "Koreksi nilai berhasil disimpan",
                                    icon: "success"
                                });
                            } else {
                                swal.fire({
                                    title: "Gagal",
                                    text: 'Tidak ada nilai yang disimpan',
                                    icon: "error"
                                });
                            }
                        }, error: function (xhr, status, error) {
                            loading.addClass('d-none');
                            console.log("error", xhr.responseText);
                            const err = JSON.parse(xhr.responseText)
                            swal.fire({
                                title: "Error",
                                text: err.Message,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        })

        opsiKelas.select2({width: '100%', theme: 'bootstrap4'});
        opsiJadwal.select2({width: '100%', theme: 'bootstrap4'});

        let post;
        const lastUrl = localStorage.getItem('garudaCBT.last_url', '')
        if (lastUrl === 'input_nilai') {
            isBackFrom = true;
            post = localStorage.getItem('garudaCBT.convert', '')
            if (post) {
                localStorage.setItem('garudaCBT.last_url', '')
                post = JSON.parse(post)
                idJadwal = post.id_jadwal;
                idKelas = post.id_kelas;
                isSelected = idKelas;
                if (!isRekap) {
                    opsiKelas.val(idKelas).trigger('change')
                    //opsiJadwal.val(idJadwal)
                } else {
                    getNilai()
                }
            }
        } else {
            getNilai()
        }

        // set all
        $('#penguranganModal').on('show.bs.modal', function (e) {
            let temp = localStorage.getItem('garudaCBT.pelanggaran', null)
            if (temp) {
                temp = JSON.parse(temp);
                $(e.currentTarget).find('#p-ringan').val(temp.p_ringan);
                $(e.currentTarget).find('#p-sedang').val(temp.p_sedang);
                $(e.currentTarget).find('#p-berat').val(temp.p_berat);
            }
        });

        $('#save-min').click(function () {
            const pel = {
                'p_ringan': parseFloat($('#p-ringan').val()) || 0,
                'p_sedang': parseFloat($('#p-sedang').val()) || 0,
                'p_berat': parseFloat($('#p-berat').val()) || 0
            }
            localStorage.setItem('garudaCBT.pelanggaran', JSON.stringify(pel));
            $('#penguranganModal').modal('hide');
            const isReset = $('#reset-all').is(":checked");
            if (!result) {
                Swal.fire({
                    title: "Tidak ada data",
                    text: 'Silahkan pilih jadwal dan kelas terlebih dahulu',
                    icon: "warning"
                });
                return
            }
            savePengurangan(isReset)
        })
        // set per-siswa
        $('#pelanggaranModal').on('show.bs.modal', function (e) {
            const idSiswa = $(e.relatedTarget).data('siswa');
            const nilai = $(e.relatedTarget).data('nilai');
            const siswa = arr_pelanggaran.find(p => p.id_siswa == idSiswa);

            const ringan = siswa?.pelanggaran?.ringan || 0;
            const sedang = siswa?.pelanggaran?.sedang || 0;
            const berat = siswa?.pelanggaran?.berat || 0;

            const p_ringan = siswa?.pelanggaran?.p_ringan || 0;
            const p_sedang = siswa?.pelanggaran?.p_sedang || 0;
            const p_berat = siswa?.pelanggaran?.p_berat || 0;

            let temp = localStorage.getItem('garudaCBT.pelanggaran', null)
            if (temp) {
                temp = JSON.parse(temp);
            }

            const manual = siswa?.pelanggaran?.manual === 'ya';
            $(e.currentTarget).find('#np-ringan').val(manual ? p_ringan : (temp?.p_ringan || 0));
            $(e.currentTarget).find('#np-sedang').val(manual ? p_sedang : (temp?.p_sedang || 0));
            $(e.currentTarget).find('#np-berat').val(manual ? p_berat : (temp?.p_berat || 0));

            $(e.currentTarget).find('#jml-ringan').text(ringan);
            $(e.currentTarget).find('#jml-sedang').text(sedang);
            $(e.currentTarget).find('#jml-berat').text(berat);

            $(e.currentTarget).find('#min-idsiswa').text(idSiswa);
            $(e.currentTarget).find('#min-nilai').text(nilai);

            $('#np-ringan, #np-sedang, #np-berat').trigger('input')
        });

        $('#save-fouls').click(function () {
            const totalRingan = parseFloat($('#t-ringan').text()) || 0;
            const totalSedang = parseFloat($('#t-sedang').text()) || 0;
            const totalBerat = parseFloat($('#t-berat').text()) || 0;
            const totalMinus = totalRingan + totalSedang + totalBerat;

            const pel = {
                'p_ringan': parseFloat($('#np-ringan').val()),
                'p_sedang': parseFloat($('#np-sedang').val()),
                'p_berat': parseFloat($('#np-berat').val()),
                'manual' : 'ya',
                't_ringan' : totalRingan,
                't_sedang' : totalSedang,
                't_berat' : totalBerat,
                't_total' : totalMinus
            }
            $('#pelanggaranModal').modal('hide');
            savePengurangan(false, $('#min-idsiswa').text(), pel)
        })

        $('#np-ringan, #np-sedang, #np-berat').on('input', hitungTotalPengurangan);
    })

    function resetFouls() {
        const pel = {
            'p_ringan': 0,
            'p_sedang': 0,
            'p_berat': 0
        }

        swal.fire({
            title: "Reset Pelanggaran",
            text: "Semua Point penguranagan akan dihapus, nilai siswa akan dikembalikan ke nilai awal",
            icon: "info",
            showCancelButton: true,
        }).then(result => {
            if (result.value) {
                localStorage.setItem('garudaCBT.pelanggaran', JSON.stringify(pel));
                savePengurangan(true)
            }
        })
    }

    function savePengurangan(isReset, idSiswa, pel) {
        let arrSiswa = [];
        if (idSiswa) {
            let siswa;
            if (isRekap) {
                siswa = arr_pelanggaran.find(p => p.id_siswa === idSiswa && p.id_rekap_sisswa !== "");
            } else {
                siswa = arr_pelanggaran.find(p => p.id_siswa === idSiswa && p.id_cbt_sisswa !== "");
            }
            console.log('siswa', siswa)
            if (siswa) {
                const objBaru = {
                    ...siswa,
                    pelanggaran: {
                        ...pel,
                        berat: siswa.pelanggaran.berat,
                        sedang: siswa.pelanggaran.sedang,
                        ringan: siswa.pelanggaran.ringan
                    }
                };
                arrSiswa.push(objBaru);
            }
        } else {
            let temp = JSON.parse(localStorage.getItem('garudaCBT.pelanggaran', '{}'))

            arrSiswa = arr_pelanggaran.filter(p => (isReset && !p.manual) && p.id_cbt_siswa !== "");
            arrSiswa.forEach(p => {
                const totalRingan = (p.pelanggaran.ringan || 0) * temp.p_ringan;
                const totalSedang = (p.pelanggaran.sedang || 0) * temp.p_sedang;
                const totalBerat = (p.pelanggaran.berat || 0) * temp.p_berat;
                const totalMinus = totalRingan + totalSedang + totalBerat;

                p.pelanggaran['t_ringan'] = totalRingan;
                p.pelanggaran['t_sedang'] = totalSedang;
                p.pelanggaran['t_berat'] = totalBerat;
                p.pelanggaran['t_total'] = totalMinus;
                p.pelanggaran['p_ringan'] = temp.p_ringan;
                p.pelanggaran['p_sedang'] = temp.p_sedang;
                p.pelanggaran['p_berat'] = temp.p_berat;
                p.pelanggaran['manual'] = 'no'
            });
        }

        if (arrSiswa.length === 0) {
            Swal.fire({
                title: "Gagal",
                text: 'Tidak ada siswa yang dipilih',
                icon: "warning"
            });
            return;
        }

        let formData = new FormData($('#post_data')[0]);
        formData.append('arr_point', JSON.stringify(arrSiswa));
        formData.append('id_jadwal', idJadwal)
        formData.append('id_siswa', JSON.stringify(arr_idSiswa))

        $.ajax({
            url: base_url + "cbtnilai/save_pengurangan",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                console.log('responseSavePengurangan', data);
                if (data.success) {
                    refreshStatus();
                    Swal.fire({
                        title: "Berhasil",
                        text: "Pengurangan nilai berhasil disimpan",
                        icon: "success"
                    });
                } else {
                    Swal.fire({
                        title: "NOT CHANGED",
                        text: 'Nilai yang disimpan sama seperti sebelumnya',
                        icon: "warning"
                    });
                }
            }, error: function (xhr, status, error) {
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

    function hitungTotalPengurangan() {
        const nilRingan = parseFloat($('#np-ringan').val()) || 0;
        const jmlRingan = parseFloat($('#jml-ringan').text()) || 0;
        const totalRingan = nilRingan * jmlRingan;
        $('#t-ringan').text(totalRingan);

        const nilSedang = parseFloat($('#np-sedang').val()) || 0;
        const jmlSedang = parseFloat($('#jml-sedang').text()) || 0;
        const totalSedang = nilSedang * jmlSedang;
        $('#t-sedang').text(totalSedang);

        const nilBerat = parseFloat($('#np-berat').val()) || 0;
        const jmlBerat = parseFloat($('#jml-berat').text()) || 0;
        const totalBerat = nilBerat * jmlBerat;
        $('#t-berat').text(totalBerat);

        const totalMinus = totalRingan + totalSedang + totalBerat;

        const nilai = parseFloat($('#min-nilai').text()) || 0;
        const totalNilai = nilai - totalMinus;

        $('#total-pengurangan').text(totalMinus);
        $('#hasil-pengurangan').text(totalNilai);
    }

    function zeroPad(no) {
        return no < 10 ? '0' + no : no;
    }
</script>
