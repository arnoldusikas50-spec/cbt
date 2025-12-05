<?php

?>
<?php if ($this->auth->in_group('panitia')): ?>
    <style>
        button[onclick="hapusSoal(this)"] { display: none !important; }
    </style>
<?php endif; ?>

<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card">
            </div>

            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools">
                        <button type="button" onclick="window.location.reload()"
                                class="btn btn-sm btn-warning">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button id="convert" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> <span
                                    class="d-none d-sm-inline-block ml-1">Download Detail Soal</span>
                        </button>
                        <a href="<?= base_url('cbtbanksoal/buatsoal/' . $id_bank) ?>"
                           type="button" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> <span
                                    class="d-none d-sm-inline-block ml-1">Tambah/Edit Soal</span>
                        </a>

                <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-right ml-2">
                    <i class="fas fa-arrow-circle-left"></i><span
                            class="d-none d-sm-inline-block ml-1">Kembali</span>
                </button>
                    </div>
                </div>
                <div id="tabel-konten" class="card-body">
                    <div id="alert-download" class="alert alert-success align-content-center d-none" role="alert">
                        <div id="download-area"></div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-8">
                            <ul class="list-group list-group-unbordered">
                                <li class="list-group-item p-1"> Kode Bank Soal
                                    <span class="float-right"><b id="info_kode_bank"></b></span>
                                </li>
                                <li class="list-group-item p-1"> Mata Pelajaran
                                    <span class="float-right"><b id="info_nama_mapel"></b></span>
                                </li>
                                <li class="list-group-item p-1"> Guru
                                    <span class="float-right">
											<b id="info_nama_guru"></b>
										</span>
                                </li>
                                <li class="list-group-item p-1"> Kelas
                                    <span class="float-right"><b id="info_kelasbank"></b></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-3 col-md-4">
                            <ul class="list-group list-group-unbordered">
                                <li class="list-group-item p-1"> Total Seharusnya
                                    <span class="float-right"><b id="info_soal_seharusnya_tampil"></b></span>
                                </li>
                                <li class="list-group-item p-1"> Total Soal dibuat
                                    <span class="float-right"><b id="info_soals"></b></span>
                                </li>
                                <li class="list-group-item p-1"> Total ditampilkan
                                    <span class="float-right"><b id="info_total_soal_tampil"></b></span>
                                </li>
                                <li class="list-group-item p-1"> Ket.
                                    <span class="float-right"><b id="info_status_soal"></b></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-3 mt-2">
                            <div class="card">
                                <div class="card-body text-center p-2" id="status_pembuatan">
                                    <b>Pembuatan Soal</b>
                                    <br>
                                    <span class="text-lg"><b id="status_status_soal"></b></span>
                                    <br>
                                    <span id="status_ket_soal"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-1">
                            <div class="alert alert-default-danger text-md">
                                Jika keterangan pembuatan soal sudah <b>SELESAI</b> tapi tidak muncul di akun siswa, klik
                                tombol
                                <span class="badge badge-btn badge-primary">
                                <i class="fa fa-save"></i> <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                            </span>
                                di bawah
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-default my-shadow mb-4" id="tab-soal-area">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item alert-default-primary"><a class="nav-link active" id="badge_pg" href="#ganda" data-toggle="tab"></a></li>
                        <li class="nav-item alert-default-info"><a class="nav-link" id="badge_pg2" href="#kompleks" data-toggle="tab"></a></li>
                        <li class="nav-item alert-default-danger"><a class="nav-link" id="badge_jodohkan" href="#jodoh" data-toggle="tab"></a></li>
                        <li class="nav-item alert-default-warning"><a class="nav-link" id="badge_isian" href="#isian" data-toggle="tab"></a></li>
                        <li class="nav-item alert-default-success"><a class="nav-link" href="#essai" id="badge_essai" data-toggle="tab"></a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane table-responsive active" id="ganda">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="alert alert-default-dark text-center align-middle">
                                        <th class="border border-dark">Jenis Soal</th>
                                        <th class="border border-dark" colspan="2">Jumlah Soal</th>
                                        <th class="border border-dark">Bobot Nilai</th>
                                        <th class="border border-dark">Point Per-nomor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border border-dark">Pilihan Ganda</td>
                                    <td class="border border-dark">Seharusnya</td>
                                    <td class="text-center border border-dark" id="ganda_soal"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="ganda_bobot"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="ganda_point"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Telah dibuat</td>
                                    <td class="text-center border border-dark" id="ganda_total"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Ditampilkan</td>
                                    <td class="text-center border border-dark" id="ganda_tampil"></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border border-dark">
                                        Info :
                                        <ul id="ganda_info"></ul>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <div id="ada_soal_pg">
                                <?= form_open('', array('id' => 'select-pg')) ?>
                                <table class="table draggable-table" id="table-pg">
                                    <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-pg-all my-checkbox" id="all-pg" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua PG</div>
                                                <div class="float-right">
                                                    <span>Jumlah PG terpilih: </span>
                                                    <span id="total-pg" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ml-3" id="save-pg">
                                                        <i class="fa fa-save"></i>
                                                        <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody-pg"></tbody>
                                </table>
                                <?= form_close() ?>
                            </div>
                            <div id="emp_soal_pg" class="d-none">
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Pilihan Ganda
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane table-responsive" id="kompleks">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="alert alert-default-dark text-center align-middle">
                                        <th class="border border-dark">Jenis Soal</th>
                                        <th class="border border-dark" colspan="2">Jumlah Soal</th>
                                        <th class="border border-dark">Bobot Nilai</th>
                                        <th class="border border-dark">Point Per-nomor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border border-dark">Pil. Ganda Kompleks</td>
                                    <td class="border border-dark">Seharusnya</td>
                                    <td class="text-center border border-dark" id="kompleks_soal"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="kompleks_bobot"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="kompleks_point"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Telah dibuat</td>
                                    <td class="text-center border border-dark" id="kompleks_total"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Ditampilkan</td>
                                    <td class="text-center border border-dark" id="kompleks_tampil"></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border border-dark">
                                        Info :
                                        <ul id="kompleks_info"></ul>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div id="ada_soal_pg2">
                                <?= form_open('', array('id' => 'select-pg2')) ?>
                                <table class="table" id="table-pg2">
                                    <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-pg2-all my-checkbox" id="all-pg2" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-right">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-pg2" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ml-3" id="save-pg2">
                                                        <i class="fa fa-save"></i>
                                                        <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody-pg2"></tbody>
                                </table>
                                <?= form_close() ?>
                            </div>
                            <div id="emp_soal_pg2" class="d-none">
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Pilihan Ganda Kompleks
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane table-responsive" id="jodoh">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="alert alert-default-dark text-center align-middle">
                                        <th class="border border-dark">Jenis Soal</th>
                                        <th class="border border-dark" colspan="2">Jumlah Soal</th>
                                        <th class="border border-dark">Bobot Nilai</th>
                                        <th class="border border-dark">Point Per-nomor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border border-dark">Menjodohkan</td>
                                    <td class="border border-dark">Seharusnya</td>
                                    <td class="text-center border border-dark" id="jodoh_soal"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="jodoh_bobot"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="jodoh_point"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Telah dibuat</td>
                                    <td class="text-center border border-dark" id="jodoh_total"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Ditampilkan</td>
                                    <td class="text-center border border-dark" id="jodoh_tampil"></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border border-dark">
                                        Info :
                                        <ul id="jodoh_info"></ul>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <div id="ada_soal_jodoh">
                                <?= form_open('', array('id' => 'select-jodohkan')) ?>
                                <table class="table" id="table-jodohkan">
                                    <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-jodohkan-all my-checkbox" id="all-jodohkan" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-right">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-jodohkan" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ml-3" id="save-jodohkan">
                                                        <i class="fa fa-save"></i>
                                                        <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody-jodoh"></tbody>
                                </table>
                                <?= form_close() ?>
                            </div>
                            <div class="d-none" id="emp_soal_jodoh">
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Menjodohkan
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane table-responsive" id="isian">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="alert alert-default-dark text-center align-middle">
                                        <th class="border border-dark">Jenis Soal</th>
                                        <th class="border border-dark" colspan="2">Jumlah Soal</th>
                                        <th class="border border-dark">Bobot Nilai</th>
                                        <th class="border border-dark">Point Per-nomor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border border-dark">Isian Singkat</td>
                                    <td class="border border-dark">Seharusnya</td>
                                    <td class="text-center border border-dark" id="isian_soal"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="isian_bobot"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="isian_point"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Telah dibuat</td>
                                    <td class="text-center border border-dark" id="isian_total"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Ditampilkan</td>
                                    <td class="text-center border border-dark" id="isian_tampil"></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border border-dark">
                                        Info :
                                        <ul id="isian_info"></ul>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <div id="ada_soal_isian">
                                <?= form_open('', array('id' => 'select-isian')) ?>
                                <table class="table" id="table-isian">
                                    <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-isian-all my-checkbox" id="all-isian" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-right">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-isian" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ml-3" id="save-isian">
                                                        <i class="fa fa-save"></i>
                                                        <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody-isian"></tbody>
                                </table>

                                <?= form_close() ?>
                            </div>
                            <div id="emp_soal_isian" class="d-none">
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Isian Singkat
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane table-responsive" id="essai">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="alert alert-default-dark text-center align-middle">
                                        <th class="border border-dark">Jenis Soal</th>
                                        <th class="border border-dark" colspan="2">Jumlah Soal</th>
                                        <th class="border border-dark">Bobot Nilai</th>
                                        <th class="border border-dark">Point Per-nomor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr class="alert alert-default-success">
                                    <td rowspan="3" class="text-center align-middle border border-dark">Uraian/Essai</td>
                                    <td class="border border-dark">Seharusnya</td>
                                    <td class="text-center border border-dark" id="esai_soal"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="esai_bobot"></td>
                                    <td rowspan="3" class="text-center align-middle border border-dark" id="esai_point"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Telah dibuat</td>
                                    <td class="text-center border border-dark" id="esai_total"></td>
                                </tr>
                                <tr class="alert alert-default-success">
                                    <td class="border border-dark">Ditampilkan</td>
                                    <td class="text-center border border-dark" id="esai_tampil"></td>
                                </tr>
                                <tr class="alert alert-default-danger">
                                    <td colspan="5" class="border border-dark">
                                        Info :
                                        <ul id="esai_info"></ul>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <div id="ada_soal_esai">
                                <?= form_open('', array('id' => 'select-essai')) ?>
                                <table class="table" id="table-essai">
                                    <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-essai-all my-checkbox" id="all-essai" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-right">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-essai" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ml-3" id="save-essai">
                                                        <i class="fa fa-save"></i>
                                                        <span class="d-none d-md-inline-block ml-1">Simpan Soal Terpilih</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody-esai"></tbody>
                                </table>
                                <?= form_close() ?>
                            </div>
                            <div id="emp_soal_esai" class="d-none">
                                <div class="alert alert-default-info shadow align-content-center" role="alert">
                                    Tidak ada soal Uraian
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="temp-list"></div>
            <div id="for-export" class="d-none"></div>
        </div>
    </section>
</div>

<?= form_open('', array('id' => 'up')) ?>
<?= form_close() ?>

<script src="<?= base_url() ?>/assets/app/js/docx_7.1.10.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
<script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>

<script src="<?= base_url() ?>/assets/app/js/linker-list.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>
<script src="<?= base_url() ?>/assets/app/js/mediaPlayer.js"></script>

<script>
    const idBank = '<?= $id_bank ?>';
    const arrJenis = ['', 'Pilihan Ganda', 'PG Kompleks', 'Menjodohkan', 'Isian Singkat', 'Uraian/Essai'];

    let jmlPgTampil;
    let jmlPg2Tampil;
    let jmlJodohkanTampil;
    let jmlIsianTampil;
    let jmlEssaiTampil;
    let listJodohkan = {};
    let titleDoc = '';

    $(document).ready(function () {
        // Initialize Bootstrap tabs
        $('a[data-toggle="tab"]').tab();
        
        let formData = new FormData($('#up')[0]);
        formData.append('id_bank', idBank);
        $.ajax({
            url: base_url + "cbtbanksoal/get_detail",
            method: "POST",
            processData: false,
            contentType: false,
            data: formData,
            success: function (result) {
                console.log("result", result);
                populateContent(result);
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

    function populateContent(result) {
        const bank = result.bank;
        const soals = result.soals;
        const kelas = result.kelas;

        const bankKode = bank.bank_kode;
        const mapel = bank.nama_mapel;
        const level = bank.bank_level;
        titleDoc = `Soal ${bankKode} ${mapel} Kls ${level}.docx`

        jmlPgTampil = bank.tampil_pg;
        jmlPg2Tampil = bank.tampil_kompleks;
        jmlJodohkanTampil = bank.tampil_jodohkan;
        jmlIsianTampil = bank.tampil_isian;
        jmlEssaiTampil = bank.tampil_esai;

        // Filter berdasarkan jenis soal
        const soals_pg = soals.filter(s => s.jenis === "1");
        const soals_pg2 = soals.filter(s => s.jenis === "2");
        const soals_jodohkan = soals.filter(s => s.jenis === "3");
        const soals_isian = soals.filter(s => s.jenis === "4");
        const soals_essai = soals.filter(s => s.jenis === "5");

        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;

        const badge_success = '<span class="text-lg"><i class="bi-check-circle"></i></span>';
        const badge_danger = '<span class="text-lg text-danger"><i class="bi-exclamation-circle"></i></span>';

        const total_pg_tampil = countDisplayed(soals_pg);
        const badge_pg = total_pg_tampil < bank.tampil_pg ? badge_danger : badge_success;

        const total_pg2_tampil = countDisplayed(soals_pg2);
        const badge_pg2 = total_pg2_tampil < bank.tampil_kompleks ? badge_danger : badge_success;

        const total_jodohkan_tampil = countDisplayed(soals_jodohkan);
        const badge_jodohkan = total_jodohkan_tampil < bank.tampil_jodohkan ? badge_danger : badge_success;

        const total_isian_tampil = countDisplayed(soals_isian);
        const badge_isian = total_isian_tampil < bank.tampil_isian ? badge_danger : badge_success;

        const total_essai_tampil = countDisplayed(soals_essai);
        const badge_essai = total_essai_tampil < bank.tampil_esai ? badge_danger : badge_success;

        const total_soal_tampil = countDisplayed(soals);
        const total_soal_seharusnya_tampil =
            Number(bank.tampil_pg) + Number(bank.tampil_kompleks) + Number(bank.tampil_jodohkan) +
            Number(bank.tampil_isian) + Number(bank.tampil_esai);

        const tampil_kurang = (
            Number(total_pg_tampil) + Number(total_pg2_tampil) + Number(total_jodohkan_tampil) +
            Number(total_isian_tampil) + Number(total_essai_tampil)
        ) < total_soal_seharusnya_tampil;

        const soal_kurang = (
            total_pg_tampil < bank.tampil_pg ||
            total_pg2_tampil < bank.tampil_kompleks ||
            total_jodohkan_tampil < bank.tampil_jodohkan ||
            total_isian_tampil < bank.tampil_isian ||
            total_essai_tampil < bank.tampil_esai
        );

        const status_soal = soal_kurang || tampil_kurang ? 'Belum Selesai' : 'SELESAI';
        const ket_soal = soals.length < total_soal_seharusnya_tampil
            ? 'pembuatan soal masih kurang'
            : (soal_kurang || tampil_kurang
                ? 'soal yang ditampilkan tidak sama dengan seharusnya'
                : 'soal sudah cukup dan siap digunakan');

        const bg_color = soal_kurang || tampil_kurang ? 'bg-danger' : 'bg-success';

        // Proses kelas
        let kelasbank = bank.bank_kelas
            .map(jk => {
                const found = kelas.find(k => k.id_kelas?.toString() === jk.kelas_id?.toString());
                return found ? found.nama_kelas : null;
            })
            .filter(nama => nama !== null)
            .join(', ');

        $('#badge_pg').html(`Pilihan Ganda ${badge_pg}`)
        $('#badge_pg2').html(`Pilihan Ganda Kompleks ${badge_pg2}`)
        $('#badge_jodohkan').html(`Menjodohkan ${badge_jodohkan}`)
        $('#badge_isian').html(`Isian Singkat ${badge_isian}`)
        $('#badge_essai').html(`Essai/Uraian ${badge_essai}`)

        $('#info_kode_bank').html(bank.bank_kode);
        $('#info_nama_mapel').html(bank.nama_mapel);
        $('#info_kelasbank').html(kelasbank);
        $('#info_nama_guru').html(bank.nama_guru);
        $('#info_soal_seharusnya_tampil').html(total_soal_seharusnya_tampil);
        $('#info_soals').html(soals.length);
        $('#info_total_soal_tampil').html(total_soal_tampil);
        $('#info_status_soal').html(status_soal);
        $('#status_pembuatan').addClass(bg_color)
        $('#status_status_soal').html(status_soal);
        $('#status_ket_soal').html(ket_soal);

        const totalSiswa = result.total_siswa;
        const disable_delete = bank.digunakan > 0 || totalSiswa > 0;
        $('#all-pg').attr('disabled', disable_delete);
        $('#all-pg2').attr('disabled', disable_delete);
        $('#all-jodohkan').attr('disabled', disable_delete);
        $('#all-isian').attr('disabled', disable_delete);
        $('#all-essai').attr('disabled', disable_delete);

        populateSoalPg(bank, soals_pg, totalSiswa);
        populateSoalKompleks(bank, soals_pg2, totalSiswa);
        populateSoalJodoh(bank, soals_jodohkan, totalSiswa);
        populateSoalIsian(bank, soals_isian, totalSiswa);
        populateSoalEssai(bank, soals_essai, totalSiswa);

        const forExportDiv = document.getElementById('for-export');
        const multipleChoiceHtml = generateMultipleChoiceTable(soals_pg);
        const complexMultipleChoiceHtml = generateComplexMultipleChoiceTable(soals_pg2);
        const matchingHtml = generateMatchingTable(soals_jodohkan);
        const shortAnswerHtml = generateShortAnswerTable(soals_isian);
        const essayHtml = generateEssayTable(soals_essai);

        forExportDiv.innerHTML =
            multipleChoiceHtml +
            complexMultipleChoiceHtml +
            matchingHtml +
            shortAnswerHtml +
            essayHtml;

        // Refresh tabs after content is populated
        $('a[data-toggle="tab"]').tab('show');
        
        // Restore active tab from localStorage if exists
        const savedTab = localStorage.getItem('banksoal_active_tab');
        if (savedTab) {
            $(`a[href="${savedTab}"]`).tab('show');
            localStorage.removeItem('banksoal_active_tab'); // Clear after use
        }
        
        allListener()
    }

    function populateSoalPg(bank, soals_pg, total_siswa) {
        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;

        const total_pg = soals_pg.length;
        const total_pg_tampil = countDisplayed(soals_pg);
        const disable_delete = bank.digunakan > 0 || total_siswa > 0;
        const dis = disable_delete ? 'disabled' : '';

        $('#ganda_soal').text(bank.tampil_pg)
        $('#ganda_bobot').text(bank.bobot_pg)
        $('#ganda_point').text(bank.bobot_pg > 0 ? (bank.bobot_pg / Math.max(total_pg_tampil, bank.tampil_pg)).toFixed(2) : 0)
        $('#ganda_total').text(total_pg)
        $('#ganda_tampil').text(total_pg_tampil)

        const messages = [];

        if (total_pg < bank.tampil_pg) {
            messages.push('Soal PILIHAN GANDA masih kurang, klik tombol <b>(<i class="fas fa-plus"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
        }

        if (total_pg > 0 && bank.tampil_pg === '0') {
            messages.push('Ada soal PILIHAN GANDA tapi tidak ada yang ditampilkan.');
        }

        if (total_pg_tampil < bank.tampil_pg) {
            messages.push('Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.');
        }

        if (bank.digunakan > 0) {
            messages.push('Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        if (total_siswa > 0) {
            messages.push('Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        const listContainer = document.querySelector('#ganda_info');
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.innerHTML = msg;
            listContainer.appendChild(li);
        });

        if (soals_pg.length > 0) {
            const tbody = document.querySelector('#tbody-pg');
            soals_pg.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';
                const opsiA = (s.opsi_a || '').replace(/<p>|<\/p>/g, '');
                const opsiB = (s.opsi_b || '').replace(/<p>|<\/p>/g, '');
                const opsiC = (s.opsi_c || '').replace(/<p>|<\/p>/g, '');
                const opsiD = (s.opsi_d || '').replace(/<p>|<\/p>/g, '');
                const opsiE = (s.opsi_e || '').replace(/<p>|<\/p>/g, '');

                let opsiList = `
        <li>${opsiA}</li>
        <li>${opsiB}</li>
        <li>${opsiC}</li>
    `;

                if (bank.opsi === '4') {
                    opsiList += `<li>${opsiD}</li>`;
                } else if (bank.opsi === '5') {
                    opsiList += `<li>${opsiD}</li><li>${opsiE}</li>`;
                }

                const row = document.createElement('tr');
                row.innerHTML = `
        <td class="text-center">
            <input class="check-pg my-checkbox" id="${s.id_soal}" type="checkbox"
                   name="soal[]" value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td style="text-align: center;">${s.nomor_soal}.</td>
        <td class="question" data-id="${s.id_soal}">
            ${s.soal}
            <br />
            <ul style="list-style-type: upper-alpha; padding-left: 20px;">
                ${opsiList}
            </ul>
            <p>Jawaban: <b>${(s.jawaban || '').toUpperCase()}</b></p>
        </td>
        <td style="text-align: right; width: 60px">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=1"
               type="button" class="btn btn-sm btn-warning mb-2">
                <i class="fas fa-pencil-alt"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="1"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="fa fa-trash"></i></button>
        </td>
    `;
                tbody.appendChild(row);
            });
        } else {
            $('#ada_soal_pg').addClass('d-none');
            $('#emp_soal_pg').removeClass('d-none');
        }
    }

    function populateSoalKompleks(bank, soals_pg2, total_siswa) {
        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;

        const total_pg2 = soals_pg2.length;
        const total_pg2_tampil = countDisplayed(soals_pg2);
        const disable_delete = bank.digunakan > 0 || total_siswa > 0;
        const dis = disable_delete ? 'disabled' : '';

        $('#kompleks_soal').text(bank.tampil_kompleks)
        $('#kompleks_bobot').text(bank.bobot_kompleks)
        $('#kompleks_point').text(bank.bobot_kompleks > 0 ? (bank.bobot_kompleks / Math.max(total_pg2_tampil, bank.tampil_kompleks)).toFixed(2) : 0)
        $('#kompleks_total').text(total_pg2)
        $('#kompleks_tampil').text(total_pg2_tampil)

        const messages = [];

        if (total_pg2 < bank.tampil_kompleks) {
            messages.push('Soal PILIHAN GANDA KOMPLEKS masih kurang, klik tombol <b>(<i class="fas fa-plus"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
        }

        if (total_pg2 > 0 && bank.tampil_kompleks === '0') {
            messages.push('Ada soal PILIHAN GANDA KOMPLEKS tapi tidak ada yang ditampilkan.');
        }

        if (total_pg2_tampil < bank.tampil_kompleks) {
            messages.push('Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.');
        }

        if (bank.digunakan > 0) {
            messages.push('Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        if (total_siswa > 0) {
            messages.push('Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        const listContainer = document.querySelector('#kompleks_info');
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.innerHTML = msg;
            listContainer.appendChild(li);
        });

        if (soals_pg2.length){
            const tbody = document.querySelector('#tbody-pg2'); // pastikan tbody punya id ini
            soals_pg2.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';
                const opsi = s.opsi_a;
                const jawabanArray = s.jawaban;
                const jawaban = jawabanArray.filter(Boolean).join(', ').toUpperCase();

                let opsiList = '';
                const abjadStart = 97; // ASCII a
                const abjadEnd = abjadStart + Object.keys(opsi).length;

                for (let i = abjadStart; i < abjadEnd; i++) {
                    const abjad = String.fromCharCode(i);
                    const teks = opsi[abjad] ? opsi[abjad].replace(/<p>|<\/p>/g, '') : 'null';
                    opsiList += `<li>${teks}</li>`;
                }

                const row = document.createElement('tr');
                row.innerHTML = `
        <td style="text-align: center;">
            <input class="check-pg2 my-checkbox"
                   id="${s.id_soal}" type="checkbox" name="soal[]"
                   value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td style="text-align: center;">${s.nomor_soal}.</td>
        <td class="question" data-id="${s.id_soal}">
            ${s.soal}
            <br />
            <ul class="pl-3" style="list-style-type: upper-alpha">
                ${opsiList}
            </ul>
            <p>Jawaban: <b>${jawaban}</b></p>
        </td>
        <td style="text-align: right; width: 60px">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=2"
               type="button" class="btn btn-sm btn-warning mb-2">
                <i class="fas fa-pencil-alt"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="2"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="fa fa-trash"></i></button>
        </td>
    `;
                tbody.appendChild(row);
            });
        } else {
            $('#ada_soal_pg2').addClass('d-none');
            $('#emp_soal_pg2').removeClass('d-none');
        }
    }

    function populateSoalJodoh(bank, soals_jodohkan, total_siswa) {
        listJodohkan = {};
        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;

        const total_jodohkan = soals_jodohkan.length;
        const total_jodohkan_tampil = countDisplayed(soals_jodohkan);
        const disable_delete = bank.digunakan > 0 || total_siswa > 0;
        const dis = disable_delete ? 'disabled' : '';

        $('#jodoh_soal').text(bank.tampil_jodohkan)
        $('#jodoh_bobot').text(bank.bobot_jodohkan)
        $('#jodoh_point').text(bank.bobot_jodohkan > 0 ? (bank.bobot_jodohkan / Math.max(total_jodohkan_tampil, bank.tampil_jodohkan)).toFixed(2) : 0)
        $('#jodoh_total').text(total_jodohkan)
        $('#jodoh_tampil').text(total_jodohkan_tampil)

        const messages = [];

        if (total_jodohkan < bank.tampil_jodohkan) {
            messages.push('Soal JODOHKAN masih kurang, klik tombol <b>(<i class="fas fa-plus"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
        }

        if (total_jodohkan > 0 && bank.tampil_jodohkan === '0') {
            messages.push('Ada soal PILIHAN GANDA tapi tidak ada yang ditampilkan.');
        }

        if (total_jodohkan_tampil < bank.tampil_jodohkan) {
            messages.push('Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.');
        }

        if (bank.digunakan > 0) {
            messages.push('Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        if (total_siswa > 0) {
            messages.push('Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        const listContainer = document.querySelector('#jodoh_info');
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.innerHTML = msg;
            listContainer.appendChild(li);
        });

        if (soals_jodohkan.length) {
            const tbody = document.querySelector('#tbody-jodoh');
            soals_jodohkan.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';
                const jawaban = s.jawaban || { jawaban: [] };

                listJodohkan[`linker_${s.id_soal}`] = {'no' : s.nomor_soal, 'list' : jawaban}
                const row = document.createElement('tr');
                row.innerHTML = `
        <td style="text-align: center;">
            <input class="check-jodohkan my-checkbox"
                   id="${s.id_soal}" type="checkbox" name="soal[]"
                   value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td style="text-align: center;">${s.nomor_soal}.</td>
        <td class="question" data-id="${s.id_soal}">
            ${s.soal}
            <br />
            <div class="mb-2 mt-2"><b>Jawaban:</b></div>
            <div class="list-jodohkan" id="linker_${s.id_soal}" data-nomor="${s.nomor_soal}" data-id="${s.id_soal}">
            </div>
        </td>
        <td style="text-align: right; width: 60px">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=3"
               type="button" class="btn btn-sm btn-warning mb-2">
                <i class="fas fa-pencil-alt"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="3"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="fa fa-trash"></i></button>
        </td>
    `;
                tbody.appendChild(row);
            });
        } else {
            $('#ada_soal_jodoh').addClass('d-none');
            $('#emp_soal_jodoh').removeClass('d-none');
        }
    }

    function populateSoalIsian(bank, soals_isian, total_siswa) {
        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;
        const total_isian = soals_isian.length;
        const total_isian_tampil = countDisplayed(soals_isian);
        const disable_delete = bank.digunakan > 0 || total_siswa > 0;
        const dis = disable_delete ? 'disabled' : '';

        $('#isian_soal').text(bank.tampil_isian)
        $('#isian_bobot').text(bank.bobot_isian)
        $('#isian_point').text(bank.bobot_isian > 0 ? (bank.bobot_isian / Math.max(total_isian_tampil, bank.tampil_isian)).toFixed(2) : 0)
        $('#isian_total').text(total_isian)
        $('#isian_tampil').text(total_isian_tampil)

        const messages = [];

        if (total_isian < bank.tampil_isian) {
            messages.push('Soal ISIAN SINGKAT masih kurang, klik tombol <b>(<i class="fas fa-plus"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
        }

        if (total_isian > 0 && bank.tampil_isian === '0') {
            messages.push('Ada soal PILIHAN GANDA tapi tidak ada yang ditampilkan.');
        }

        if (total_isian_tampil < bank.tampil_isian) {
            messages.push('Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.');
        }

        if (bank.digunakan > 0) {
            messages.push('Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        if (total_siswa > 0) {
            messages.push('Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        const listContainer = document.querySelector('#isian_info');
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.innerHTML = msg;
            listContainer.appendChild(li);
        });

        if (soals_isian.length > 0) {
            const tbody = document.querySelector('#tbody-isian');

            soals_isian.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';

                const row = document.createElement('tr');
                row.innerHTML = `
        <td style="text-align: center;">
            <input class="check-isian my-checkbox"
                   id="${s.id_soal}" type="checkbox" name="soal[]"
                   value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td style="text-align: center;">${s.nomor_soal}.</td>
        <td class="question" data-id="${s.id_soal}">
            ${s.soal}
            <br />
            <p>Jawaban: <b>${s.jawaban}</b></p>
        </td>
        <td style="text-align: right; width: 60px">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=4"
               type="button" class="btn btn-sm btn-warning mb-2">
                <i class="fas fa-pencil-alt"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="4"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="fa fa-trash"></i></button>
        </td>
    `;
                tbody.appendChild(row);
            });
        } else {
            $('#ada_soal_isian').addClass('d-none');
            $('#emp_soal_isian').removeClass('d-none');
        }
    }

    function populateSoalEssai(bank, soals_essai, total_siswa) {
        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;
        const total_esai = soals_essai.length;
        const total_esai_tampil = countDisplayed(soals_essai);
        const disable_delete = bank.digunakan > 0 || total_siswa > 0;
        const dis = disable_delete ? 'disabled' : '';

        $('#esai_soal').text(bank.tampil_esai)
        $('#esai_bobot').text(bank.bobot_esai)
        $('#esai_point').text(bank.bobot_esai > 0 ? (bank.bobot_esai / Math.max(total_esai_tampil, bank.tampil_esai)).toFixed(2) : 0)
        $('#esai_total').text(total_esai)
        $('#esai_tampil').text(total_esai_tampil)

        const messages = [];

        if (total_esai < bank.tampil_esai) {
            messages.push(
                'Soal URAIAN/ESSAY masih kurang, klik tombol <b>(<i class="fas fa-plus"></i> Tambah/Edit Soal)</b> untuk menambahkan.'
            );
        }

        if (total_esai > 0 && bank.tampil_esai === '0') {
            messages.push('Ada soal PILIHAN GANDA tapi tidak ada yang ditampilkan.');
        }

        if (total_esai_tampil < bank.tampil_esai) {
            messages.push('Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.');
        }

        if (bank.digunakan > 0) {
            messages.push('Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        if (total_siswa > 0) {
            messages.push('Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.');
        }

        const listContainer = document.querySelector('#esai_info');
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.innerHTML = msg;
            listContainer.appendChild(li);
        });

        if (soals_essai.length > 0) {
            const tbody = document.querySelector('#tbody-esai');

            soals_essai.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';

                const row = document.createElement('tr');
                row.innerHTML = `
        <td style="text-align: center;">
            <input class="check-essai my-checkbox"
                   id="${s.id_soal}" type="checkbox" name="soal[]"
                   value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td style="text-align: center;">${s.nomor_soal}.</td>
        <td class="question" data-id="${s.id_soal}">
            ${s.soal}
            <br />
            <p>Jawaban: <b>${s.jawaban}</b></p>
        </td>
        <td style="text-align: right; width: 60px">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=5"
               type="button" class="btn btn-sm btn-warning mb-2">
                <i class="fas fa-pencil-alt"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="5"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="fa fa-trash"></i></button>
        </td>
    `;
                tbody.appendChild(row);
            });
        } else {
            $('#ada_soal_esai').addClass('d-none');
            $('#emp_soal_esai').removeClass('d-none');
        }
    }

    function allListener() {
        var pathUpload = 'uploads';
        var $imgs = $('img');
        $.each($imgs, function () {
            var curSrc = $(this).attr('src');
            if (curSrc?.indexOf("base64") > 0 || !curSrc?.includes("uploads")) {
            } else {
                var forReplace = curSrc?.split(pathUpload);
                $(this).attr('src', base_url + pathUpload + forReplace[1]);
            }
        });

        $.each($(`.question`), function () {
            const dataId = $(this).data('id');
            const audios = $(this).find('audio');
            const videos = $(this).find('video');

            $.each(audios, function (ind) {
                var curSrc = $(this).attr('src');
                if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
                } else {
                    var forReplace = curSrc.split(pathUpload);
                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                }

                this.renderAudio({
                    idSoal: dataId + '-'+ ind,
                    isPreview: true,
                    callback: function (idSoal, playCount) {
                        console.log(`Audio [${idSoal}] played: ${playCount}`);
                    }
                });
            });

            $.each(videos, function (ind) {
                var curSrc = $(this).attr('src');
                if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
                } else {
                    var forReplace = curSrc.split(pathUpload);
                    $(this).attr('src', base_url + pathUpload + forReplace[1]);
                }

                this.renderVideo({
                    idSoal: dataId + '-'+ ind,
                    isPreview: true,
                    callback: function (idSoal, playCount) {
                        console.log(`Video [${idSoal}] played: ${playCount}`);
                    }
                });
            });

        });

        let isMounted = false;
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            if ($(this).attr('href') === "#jodoh" && !isMounted) {
                for (const id in listJodohkan) {
                    if (listJodohkan.hasOwnProperty(id)) {
                        const item = listJodohkan[id];
                        if ($(`#${id}`).children().length > 1) return
                        $(`#${id}`).linkerList({
                            enableSelect: false,
                            enableEditor: false,
                            data: item.list,
                            id: item.no,
                            onInit: function (msg) {
                                console.log('res', msg)
                                isMounted = true;
                            }
                        });
                    }
                }
            }
        })

        var pgCount = $('#table-pg .check-pg').length;
        var pg2Count = $('#table-pg2 .check-pg2').length;
        var jodohkanCount = $('#table-jodohkan .check-jodohkan').length;
        var isianCount = $('#table-isian .check-isian').length;
        var essaiCount = $('#table-essai .check-essai').length;

        //console.log('count', jodohkanCount);

        var pgUnchecked = [];
        var pg2Unchecked = [];
        var jodohkanUnchecked = [];
        var isianUnchecked = [];
        var essaiUnchecked = [];

        function findUnchecked() {
            pgUnchecked = [];
            pg2Unchecked = [];
            jodohkanUnchecked = [];
            isianUnchecked = [];
            essaiUnchecked = [];

            $("#table-pg .check-pg:not(:checked)").each(function () {
                pgUnchecked.push($(this).val());
            });

            $("#table-pg2 .check-pg2:not(:checked)").each(function () {
                pg2Unchecked.push($(this).val());
            });

            $("#table-jodohkan .check-jodohkan:not(:checked)").each(function () {
                jodohkanUnchecked.push($(this).val());
            });

            $("#table-isian .check-isian:not(:checked)").each(function () {
                isianUnchecked.push($(this).val());
            });

            $("#table-essai .check-essai:not(:checked)").each(function () {
                essaiUnchecked.push($(this).val());
            });

            var pgchecked = $("#table-pg .check-pg:checked").length;
            $('#total-pg').html(`${pgchecked}`);
            $("#all-pg").prop({
                checked: pgchecked === pgCount,
                indeterminate: pgchecked > 0 && pgchecked < pgCount
            });

            var pg2checked = $("#table-pg2 .check-pg2:checked").length;
            $('#total-pg2').html(`${pg2checked}`);
            $("#all-pg2").prop({
                checked: pg2checked === pg2Count,
                indeterminate: pg2checked > 0 && pg2checked < pg2Count
            });

            var jodohkanchecked = $("#table-jodohkan .check-jodohkan:checked").length;
            $('#total-jodohkan').html(`<b>${jodohkanchecked}</b>`);
            $("#all-jodohkan").prop({
                checked: jodohkanchecked === jodohkanCount,
                indeterminate: jodohkanchecked > 0 && jodohkanchecked < jodohkanCount
            });

            var isianchecked = $("#table-isian .check-isian:checked").length;
            $('#total-isian').html(`<b>${isianchecked}</b>`);
            $("#all-isian").prop({
                checked: isianchecked === isianCount,
                indeterminate: isianchecked > 0 && isianchecked < isianCount
            });

            var essaiChecked = $("#table-essai .check-essai:checked").length;
            $('#total-essai').html(`<b>${essaiChecked}</b>`);
            $("#all-essai").prop({
                checked: essaiChecked === essaiCount,
                indeterminate: essaiChecked > 0 && essaiChecked < essaiCount
            });
        }

        findUnchecked();

        $("#table-pg").on("change", ".check-pg", function () {
            findUnchecked();
        });

        $("#table-pg2").on("change", ".check-pg2", function () {
            findUnchecked();
        });

        $("#table-jodohkan").on("change", ".check-jodohkan", function () {
            findUnchecked();
        });

        $("#table-isian").on("change", ".check-isian", function () {
            findUnchecked();
        });

        $("#table-essai").on("change", ".check-essai", function () {
            findUnchecked();
        });

        $("#all-pg").on("click", function () {
            if (this.checked) {
                $(".check-pg").each(function () {
                    this.checked = true;
                    $("#all-pg").prop("checked", true);
                });
            } else {
                $(".check-pg").each(function () {
                    this.checked = false;
                    $("#all-pg").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $("#all-pg2").on("click", function () {
            if (this.checked) {
                $(".check-pg2").each(function () {
                    this.checked = true;
                    $("#all-pg2").prop("checked", true);
                });
            } else {
                $(".check-pg2").each(function () {
                    this.checked = false;
                    $("#all-pg2").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $("#all-jodohkan").on("click", function () {
            if (this.checked) {
                $(".check-jodohkan").each(function () {
                    this.checked = true;
                    $("#all-jodohkan").prop("checked", true);
                });
            } else {
                $(".check-jodohkan").each(function () {
                    this.checked = false;
                    $("#all-jodohkan").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $("#all-isian").on("click", function () {
            if (this.checked) {
                $(".check-isian").each(function () {
                    this.checked = true;
                    $("#all-isian").prop("checked", true);
                });
            } else {
                $(".check-isian").each(function () {
                    this.checked = false;
                    $("#all-isian").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $("#all-essai").on("click", function () {
            if (this.checked) {
                $(".check-essai").each(function () {
                    this.checked = true;
                    $("#all-essai").prop("checked", true);
                });
            } else {
                $(".check-essai").each(function () {
                    this.checked = false;
                    $("#all-essai").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $('#save-pg').on('click', function (e) {
            //var dataPost = $('#select-pg').serialize() + "&uncheck=" + JSON.stringify(pgUnchecked);
            let dataPost = new FormData($('#select-pg')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '1');
            dataPost.append('uncheck', JSON.stringify(pgUnchecked));

            var checked = $("#table-pg .check-pg:checked").length;
            if (checked !== parseInt(jmlPgTampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Pilihan Ganda terpilih: ${checked} <br>Seharusnya: ${jmlPgTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        $('#save-pg2').on('click', function (e) {
            //var dataPost = $('#select-pg2').serialize() + "&uncheck=" + JSON.stringify(pg2Unchecked);
            let dataPost = new FormData($('#select-pg2')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '2');
            dataPost.append('uncheck', JSON.stringify(pg2Unchecked));

            var checked = $("#table-pg2 .check-pg2:checked").length;
            if (checked !== parseInt(jmlPg2Tampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Ganda Kompleks terpilih: ${checked} <br>Seharusnya: ${jmlPg2Tampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        $('#save-jodohkan').on('click', function (e) {
            //var dataPost = $('#select-jodohkan').serialize() + "&uncheck=" + JSON.stringify(jodohkanUnchecked);
            let dataPost = new FormData($('#select-jodohkan')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '3');
            dataPost.append('uncheck', JSON.stringify(jodohkanUnchecked));

            var checked = $("#table-jodohkan .check-jodohkan:checked").length;
            if (checked !== parseInt(jmlJodohkanTampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Menjodohkan terpilih: ${checked} <br>Seharusnya: ${jmlJodohkanTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        $('#save-isian').on('click', function (e) {
            //var dataPost = $('#select-isian').serialize() + "&uncheck=" + JSON.stringify(isianUnchecked);
            let dataPost = new FormData($('#select-isian')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '4');
            dataPost.append('uncheck', JSON.stringify(isianUnchecked));

            var checked = $("#table-isian .check-isian:checked").length;
            if (checked !== parseInt(jmlIsianTampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Isian terpilih: ${checked} <br>Seharusnya: ${jmlIsianTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        $('#save-essai').on('click', function (e) {
            //var dataPost = $('#select-essai').serialize() + "&uncheck=" + JSON.stringify(essaiUnchecked);
            let dataPost = new FormData($('#select-essai')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '5');
            dataPost.append('uncheck', JSON.stringify(essaiUnchecked));

            var checked = $("#table-essai .check-essai:checked").length;
            if (checked !== parseInt(jmlEssaiTampil)) {
                swal.fire({
                    title: "Info",
                    html: `Soal Uraian terpilih: ${checked} <br>Seharusnya: ${jmlEssaiTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost);
            }
        });

        function saveSoalSelected(dataPost) {
            swal.fire({
                title: "Menyimpan Soal Terpilih",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            $.ajax({
                url: base_url + "cbtbanksoal/saveSelected",
                type: "POST",
                dataType: "JSON",
                data: dataPost,
                processData: false,
                contentType: false,
                success: function (data) {
                    const count = data.check > 0 ? data.check : '';
                    swal.fire({
                        title: "Sukses",
                        html: count +' Soal terpilih berhasil disimpan',
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    }).then(result => {
                        if (result.value) {
                            // Save current active tab before reload
                            const activeTab = $('.tab-pane.active').attr('id');
                            if (activeTab) {
                                localStorage.setItem('banksoal_active_tab', '#' + activeTab);
                            }
                            window.location.reload();
                        }
                    });
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
        }

        $("#convert").click(async function (e) {
            e.preventDefault();
            $(async function () {
                const $imgs = $('img');
                const loadImage = (img, index) => {
                    return new Promise((resolve) => {
                        const obj = { pos: index };

                        $(img).on('error', function () {
                            obj.error = true;
                            resolve(obj);
                        }).on('load', function () {
                            obj.error = false;
                            resolve(obj);
                        });

                        // Trigger load/error handler if image is already loaded
                        if (img.complete) {
                            obj.error = !img.naturalWidth;
                            resolve(obj);
                        }
                    });
                };

                const promises = $imgs.map((i, img) => {
                    console.log('src', img.src);
                    return loadImage(img, i);
                }).get(); // Convert jQuery object to array

                const results = await Promise.all(promises);

                results.forEach((res, i) => {
                    console.log('img result', i, res);
                });

                saveWord(); // Called after all images are processed
            });
        });

        const tables = document.querySelectorAll('.draggable-table');
        tables.forEach(table => {
            table.querySelectorAll("tr").forEach(row => {
                row.setAttribute("draggable", "true");
                row.addEventListener("dragstart", start);
                row.addEventListener("dragover", dragover);
                row.addEventListener("drop", drop);
            });
        })
    }

    function saveWord() {
        var contentDocument = $('#for-export').convertToHtmlFile('detail', '');
        var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
        //var content = contentDocument.documentElement.innerHTML;
        //console.log('convert', content)
        var converted = htmlDocx.asBlob(content, {
            orientation: 'landscape',
            size: 'A4',
            margins: {top: 700, bottom: 700, left: 1000, right: 1000}
        });
        saveAs(converted, titleDoc);

        const link = document.createElement('a');
        link.href = URL.createObjectURL(converted);
        link.download = titleDoc;
        link.appendChild(
            document.createTextNode('Click here if your download has not started automatically'));
        const downloadArea = document.getElementById('download-area');
        downloadArea.innerHTML = '';
        downloadArea.appendChild(link);

        $('#alert-download').removeClass('d-none');

        $("#alert-download").fadeTo(10000, 500).slideUp(500, function () {
            $("#alert-download").slideUp(500);
        });

    }

    function hapusSoal(btn) {
        const id = $(btn).data('idsoal');
        const no = $(btn).data('nomor');
        const jn = $(btn).data('jenis');

        swal.fire({
            title: "HAPUS ?",
            html: "Soal berikut akan dihapus<br>Nomor: <b>" + no + "</b><br>Jenis: <b>" + arrJenis[jn] + "</b>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
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

                let formData = new FormData($('#up')[0]);
                formData.append('soal_id', id);
                $.ajax({
                    url: base_url + "cbtbanksoal/hapussoal",
                    method: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (result) {
                        console.log("result", result);
                        var tit = result ? 'BERHASIL' : 'GAGAL';
                        var msg = result ? 'berhasil' : 'gagal';
                        var ic = result ? 'success' : 'error';
                        swal.fire({
                            title: tit,
                            text: "Soal " + msg + " dihapus",
                            icon: ic,
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                        }).then(result => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
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
            }
        })
    }

    let row; // Menyimpan referensi baris yang sedang di-drag
    let targetRow; // Menyimpan referensi target baris

    function start(event) {
        row = event.target.closest("tr"); // Ambil baris yang sedang di-drag
        event.dataTransfer.setData("text/plain", ""); // Diperlukan untuk drag & drop
    }

    function dragover(event) {
        event.preventDefault();
        let tempTarget = event.target.closest("tr"); // Ambil target baris sementara

        if (!tempTarget || tempTarget === row) return;

        targetRow = tempTarget; // Simpan target yang valid untuk diproses saat drop
    }

    function drop(event) {
        event.preventDefault();

        if (!targetRow || targetRow === row) return;

        let tbody = targetRow.parentNode;
        let children = Array.from(tbody.children);

        if (children.indexOf(targetRow) > children.indexOf(row)) {
            targetRow.after(row);
        } else {
            targetRow.before(row);
        }

        targetRow = null; // Reset targetRow setelah drop

        let table = event.target.closest("table");
        let rows = table.querySelectorAll("tr");
        let ids = [];
        rows.forEach(function (row) {
            let input = row.getElementsByTagName("input")[0];
            if (!isNaN(Number(input.id))) {
                ids.push(Number(input.id));
            }
        })
        console.log('num', ids)

        swal.fire({
            title: "Pindah nomor soal",
            html: "Nomor soal akan diurutkan secara otomatis",
            icon: "warning",
            showCancelButton: false,
            confirmButtonColor: "#3085d6",
            confirmButtonText: "OK"
        }).then(result => {
            console.log('test')
            let formData = new FormData($('#up')[0]);
            formData.append('soal_id', ids.join(','));
            $.ajax({
                url: base_url + 'cbtbanksoal/resetNumber',
                method: "POST",
                processData: false,
                contentType: false,
                data: formData,
                success: function (respon) {
                    window.location.reload();
                },
                error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            })
        })
    }

    /**
     * Generates an HTML table for multiple-choice questions (Pilihan Ganda).
     * @param {Array<Object>} soals_pg - An array of objects, where each object represents a multiple-choice question.
     * Each question object should have properties like `nomor_soal`, `soal`, `opsi_a`, `opsi_b`, etc., and `jawaban`.
     * @returns {string} The HTML string for the multiple-choice questions table.
     */
    function generateMultipleChoiceTable(soals_pg) {
        const rows1 = soals_pg.length > 50 ? soals_pg.length : 50;
        let html = `
        <p><b>I. Soal Pilihan Ganda</b></p>
        <table class="table-soal" style="width:100%; font-size: 11pt; border: 1px solid black; border-collapse: collapse; border-spacing: 0; page-break-after: always">
            <thead>
                <tr style="background-color: lightgrey">
                    <th style="width:60px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">NO</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;">SOAL</th>
                    <th style="width:100px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold">JENIS</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold">OPSI</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold">JAWABAN</th>
                    <th style="border: 1px solid black;border-collapse: collapse;  text-align: center; font-weight: bold; padding: 12px">KUNCI</th>
                </tr>
            </thead>
            <tbody>
    `;

        for (let i = 0; i < rows1; i++) {
            const s = soals_pg[i] || {
                nomor_soal: i + 1,
                soal: '',
                opsi_a: '',
                opsi_b: '',
                opsi_c: '',
                opsi_d: '',
                opsi_e: '',
                jawaban: ''
            };

            html += `
            <tr>
                <td rowspan="5" style="border: 1px solid black;vertical-align: top;text-align: center;">${s.nomor_soal}</td>
                <td rowspan="5" style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.soal}</td>
                <td rowspan="5" style="border: 1px solid black;vertical-align: top;text-align: center;">1</td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">A</td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.opsi_a}</td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${(s.jawaban || '').toLowerCase() === 'a' ? 'v' : ''}</td>
            </tr>
        `;

            let post_char = 98; // 'b'
            for (let a = 0; a < 4; a++) {
                const abjad = String.fromCharCode(post_char);
                html += `
                <tr>
                    <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${abjad.toUpperCase()}</td>
                    <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s['opsi_' + abjad] || ''}</td>
                    <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${(s.jawaban || '').toLowerCase() === abjad ? 'v' : ''}</td>
                </tr>
            `;
                post_char++;
            }
        }

        html += `
            </tbody>
        </table>
        <p>&nbsp;</p>
    `;

        return html;
    }

    /**
     * Generates an HTML table for complex multiple-choice questions (Pilihan Ganda Kompleks).
     * Assumes 'unserialize' is available or provides a mock for demonstration.
     * In a real browser environment, you'd need a library or a custom function to handle PHP's serialize format.
     * For this example, we'll assume `s.opsi_a` and `s.jawaban` are already parsed JSON objects/arrays.
     * @param {Array<Object>} soals_pg2 - An array of objects, where each object represents a complex multiple-choice question.
     * Each question object should have properties like `nomor_soal`, `soal`, `opsi_a` (an object/array of options), and `jawaban` (an array of correct answers).
     * @returns {string} The HTML string for the complex multiple-choice questions table.
     */
    function generateComplexMultipleChoiceTable(soals_pg2) {
        const rows2 = soals_pg2.length > 10 ? soals_pg2.length : 10;
        let html = `
        <p><b>II. Soal Pilihan Ganda Kompleks</b></p>
        <table class="table-soal" style="width:100%; font-size: 11pt; border: 3px solid black; border-collapse: collapse; border-spacing: 0; page-break-after: always">
            <thead>
                <tr style="border-bottom: 3px solid black; background-color: lightgrey">
                    <th style="width:40px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">NO</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold;">SOAL</th>
                    <th style="width:60px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold; padding: 12px">JENIS</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold; padding: 12px">OPSI</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold; padding: 12px">JAWABAN</th>
                    <th style="border: 1px solid black;border-collapse: collapse;  text-align: center; font-weight: bold; padding: 12px">KUNCI</th>
                </tr>
            </thead>
            <tbody>
    `;

        for (let sp = 0; sp < rows2; sp++) {
            // In a real scenario, if PHP's serialize was used, you'd need a JavaScript unserialize equivalent.
            // For this example, we'll assume soals_pg2 contains directly accessible objects/arrays.
            const s = soals_pg2[sp] || {
                jawaban: [], // Assuming an array of answers like ['a', 'c']
                nomor_soal: sp + 1,
                soal: '',
                opsi_a: {a: '', b: '', c: ''} // Assuming an object like {a: 'Opsi A', b: 'Opsi B'}
            };

            const opsis = s.opsi_a || {a: '', b: '', c: ''};
            const rows = Object.keys(opsis).length || 3;
            const jawabs = s.jawaban || [];
            const bg = s.nomor_soal % 2 === 0 ? '#FFFFFF' : '#F2F2F2';

            const opsiKeys = Object.keys(opsis).sort(); // Ensure options are processed in order (a, b, c, etc.)

            html += `
            <tr style="border-top: 3px solid black;background-color: ${bg}">
                <td rowspan="${rows}" style="border: 1px solid black;vertical-align: top;text-align: center;">${s.nomor_soal}</td>
                <td rowspan="${rows}" style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.soal}</td>
                <td rowspan="${rows}" style="border: 1px solid black;vertical-align: top;text-align: center;">2</td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center; width: 50px">${opsiKeys[0] ? opsiKeys[0].toUpperCase() : ''}</td>
                <td style="border: 1px solid black;vertical-align: top;padding-left: 6px;">${opsis[opsiKeys[0]] || ''}</td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">${jawabs.includes(opsiKeys[0]) ? 'v' : ''}</td>
            </tr>
        `;

            for (let i = 1; i < opsiKeys.length; i++) {
                const abjad = opsiKeys[i];
                html += `
                <tr style="background-color: ${bg}">
                    <td style="border: 1px solid black;vertical-align: top;text-align: center;">${abjad.toUpperCase()}</td>
                    <td style="border: 1px solid black;vertical-align: top;padding-left: 6px">${opsis[abjad] || ''}</td>
                    <td style="border: 1px solid black;vertical-align: top;text-align: center;">${jawabs.includes(abjad) ? 'v' : ''}</td>
                </tr>
            `;
            }
        }

        html += `
            </tbody>
        </table>
        <p>&nbsp;</p>
    `;
        return html;
    }

    /**
     * Generates an HTML table for matching questions (Soal Menjodohkan).
     * Assumes 'jawaban' in soals_jodohkan is already a parsed JavaScript object/array.
     * @param {Array<Object>} soals_jodohkan - An array of objects, where each object represents a matching question.
     * Each question object should have properties like `nomor_soal`, `soal`, and `jawaban`.
     * The `jawaban` property is expected to be an object with a `jawaban` key, which is an array of arrays representing the matching grid.
     * @returns {string} The HTML string for the matching questions table.
     */
    function generateMatchingTable(soals_jodohkan) {
        const rows3 = Math.max(soals_jodohkan.length, 10);
        let html = `
        <p><b>III. Soal Menjodohkan</b></p>
        <table class="table-soal" style="width:100%; font-size: 11pt; border: 3px solid black; border-collapse: collapse; border-spacing: 0; page-break-after: always">
            <thead>
                <tr style="border-bottom: 3px solid black; background-color: lightgrey">
                    <th rowspan="2" style="width:40px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">NO</th>
                    <th rowspan="2" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold; width: 300px">SOAL</th>
                    <th rowspan="2" style="width:60px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">JENIS</th>
                    <th colspan="2" style="border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">BARIS</th>
                    <th colspan="2" style="width:100px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">KOLOM</th>
                    <th colspan="2" style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold; width: 200px">KUNCI</th>
                </tr>
                <tr style="background-color: lightgrey;font-weight: bold;">
                    <th style="border: 1px solid black;vertical-align: top;text-align: center;width:60px;">KODE</th>
                    <th style="border: 1px solid black;vertical-align: top;text-align: center;width:140px; ">NAMA BARIS</th>
                    <th style="border: 1px solid black;vertical-align: top;text-align: center;width:60px;">KODE</th>
                    <th style="border: 1px solid black;vertical-align: top;text-align: center;width:140px; ">NAMA KOLOM</th>
                    <th style="border: 1px solid black;vertical-align: top;text-align: center;width:60px;">KODE BARIS</th>
                    <th style="border: 1px solid black;vertical-align: top;text-align: center;width:140px; ">KODE KOLOM</th>
                </tr>
            </thead>
            <tbody>
    `;

        for (let sj = 0; sj < rows3; sj++) {
            const s = soals_jodohkan[sj] || { jawaban: null, nomor_soal: sj + 1, soal: '' };
            const count = sj + 1; // Used for background color logic
            const bg = count % 2 === 0 ? '#FFFFFF' : '#F2F2F2';

            // Assuming s.jawaban is already a JavaScript object/array, not a PHP serialized string
            const jawaban = s.jawaban || { jawaban: [] };
            // Ensure that nested arrays in jawaban.jawaban are indeed arrays
            if (jawaban.jawaban && Array.isArray(jawaban.jawaban)) {
                jawaban.jawaban = jawaban.jawaban.map(item => Array.isArray(item) ? item : Object.values(item));
            }

            const jml_baris = jawaban.jawaban ? jawaban.jawaban.length : 0;
            const jml_kolom = (jml_baris > 0 && jawaban.jawaban[0]) ? jawaban.jawaban[0].length : 0;
            const rows = (jml_kolom > 0 && jml_baris > 0) ? Math.max((jml_kolom - 1), (jml_baris - 1)) : 5;

            const jwb_benar = {};
            if (jawaban.jawaban) {
                jawaban.jawaban.forEach((jb, kk) => {
                    // Skip the header row (index 0) if it's the structure
                    if (kk === 0) return;

                    jb.forEach((item, ki) => {
                        // Skip the header column (index 0) if it's the structure
                        if (ki === 0) return;

                        if (item === '1' || item === 1) { // Check for both string '1' and number 1
                            if (!jwb_benar[kk]) {
                                jwb_benar[kk] = [];
                            }
                            jwb_benar[kk].push(String.fromCharCode(ki + 64)); // Convert column index to A, B, C...
                        }
                    });
                });
            }
            // console.log(`Question ${s.nomor_soal}, Jawaban:`, jawaban);
            // console.log(`Question ${s.nomor_soal}, Jwb Benar:`, jwb_benar);


            html += `
            <tr style="border-top: 3px solid black;background-color: ${bg}">
                <td rowspan="${rows}" style="border: 1px solid black;vertical-align: top;text-align: center;">${s.nomor_soal}</td>
                <td rowspan="${rows}" style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.soal || ''}</td>
                <td rowspan="${rows}" style="border: 1px solid black;vertical-align: top;text-align: center;">3</td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                    ${(jawaban.jawaban && jawaban.jawaban[1] && (jawaban.jawaban[1][0] !== "" && jawaban.jawaban[1][0] !== undefined && jawaban.jawaban[1][0] !== null)) ? '1' : '&nbsp;'}
                </td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                    ${jawaban.jawaban && jawaban.jawaban[1] ? (jawaban.jawaban[1][0] || '') : ''}
                </td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                    ${(jawaban.jawaban && jawaban.jawaban[0] && (jawaban.jawaban[0][1] !== "" && jawaban.jawaban[0][1] !== undefined && jawaban.jawaban[0][1] !== null)) ? 'A' : ''}
                </td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                    ${jawaban.jawaban && jawaban.jawaban[0] ? (jawaban.jawaban[0][1] || '') : ''}
                </td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">
                    ${(jawaban.jawaban && jawaban.jawaban[1] && (jawaban.jawaban[1][0] !== "" && jawaban.jawaban[1][0] !== undefined && jawaban.jawaban[1][0] !== null)) ? '1' : ''}
                </td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">
                    ${jwb_benar[1] ? jwb_benar[1].join(', ') : ''}
                </td>
            </tr>
        `;

            let post_char_code = 66; // 'B' for column letters
            for (let i = 1; i < rows; i++) {
                const abjad = String.fromCharCode(post_char_code);
                const index = i + 1; // Corresponds to PHP's $index
                const rowCode = (jawaban.jawaban && jawaban.jawaban[index] && (jawaban.jawaban[index][0] !== "" && jawaban.jawaban[index][0] !== undefined && jawaban.jawaban[index][0] !== null)) ? index : '&nbsp;';
                const rowName = jawaban.jawaban && jawaban.jawaban[index] ? (jawaban.jawaban[index][0] || '') : '';
                const colCode = (jawaban.jawaban && jawaban.jawaban[0] && (jawaban.jawaban[0][index] !== "" && jawaban.jawaban[0][index] !== undefined && jawaban.jawaban[0][index] !== null)) ? abjad : '';
                const colName = jawaban.jawaban && jawaban.jawaban[0] ? (jawaban.jawaban[0][index] || '') : '';

                html += `
                <tr style="background-color: ${bg}">
                    <td style="border: 1px solid black;vertical-align: top;text-align: center;">${rowCode}</td>
                    <td style="border: 1px solid black;vertical-align: top;text-align: center;">${rowName}</td>
                    <td style="border: 1px solid black;vertical-align: top;text-align: center;">${colCode}</td>
                    <td style="border: 1px solid black;vertical-align: top;text-align: center;">${colName}</td>
                    <td style="border: 1px solid black;vertical-align: top;text-align: center;">${rowCode === '&nbsp;' ? '' : rowCode}</td>
                    <td style="border: 1px solid black;vertical-align: top;padding-left: 6px">
                        ${jwb_benar[index] ? jwb_benar[index].join(', ') : ''}
                    </td>
                </tr>
            `;
                post_char_code++;
            }
        }

        html += `
            </tbody>
        </table>
        <p>&nbsp;</p>
    `;
        return html;
    }

    /**
     * Generates an HTML table for short answer questions (Soal Isian Singkat).
     * @param {Array<Object>} soals_isian - An array of objects, where each object represents a short answer question.
     * Each question object should have properties like `nomor_soal`, `soal`, and `jawaban`.
     * @returns {string} The HTML string for the short answer questions table.
     */
    function generateShortAnswerTable(soals_isian) {
        const rows4 = Math.max(soals_isian.length, 10);
        let html = `
        <p><b>IV. Soal Isian Singkat</b></p>
        <table class="table-soal" style="width:100%; font-size: 11pt; border: 1px solid black; border-collapse: collapse; border-spacing: 0; page-break-after: always">
            <thead>
                <tr style="background-color: lightgrey">
                    <th style="width:40px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">NO</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold; width: 300px">SOAL</th>
                    <th style="width:60px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">JENIS</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold; width: 200px">JAWABAN BENAR</th>
                </tr>
            </thead>
            <tbody>
    `;

        for (let si = 0; si < rows4; si++) {
            const s = soals_isian[si] || { jawaban: '', nomor_soal: si + 1, soal: '' };

            html += `
            <tr>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">${s.nomor_soal}</td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.soal || ''}</td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">4</td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.jawaban || ''}</td>
            </tr>
        `;
        }

        html += `
            </tbody>
        </table>
        <p>&nbsp;</p>
    `;
        return html;
    }

    /**
     * Generates an HTML table for essay questions (Soal Essai).
     * @param {Array<Object>} soals_essai - An array of objects, where each object represents an essay question.
     * Each question object should have properties like `nomor_soal`, `soal`, and `jawaban`.
     * @returns {string} The HTML string for the essay questions table.
     */
    function generateEssayTable(soals_essai) {
        const rows5 = Math.max(soals_essai.length, 10);
        let html = `
        <p><b>V. Soal Essai</b></p>
        <table class="table-soal" style="width:100%; font-size: 11pt; border: 1px solid black; border-collapse: collapse; border-spacing: 0;">
            <thead>
                <tr style="background-color: lightgrey">
                    <th style="width:40px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">NO</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold; width: 300px">SOAL</th>
                    <th style="width:60px; border: 1px solid black;border-collapse: collapse; text-align: center;font-weight: bold;">JENIS</th>
                    <th style="border: 1px solid black;border-collapse: collapse; text-align: center; font-weight: bold; width: 200px">JAWABAN BENAR</th>
                </tr>
            </thead>
            <tbody>
    `;

        // The PHP loop was using $rows4 instead of $rows5, assuming a typo and correcting to $rows5
        for (let se = 0; se < rows5; se++) {
            const s = soals_essai[se] || { jawaban: '', nomor_soal: se + 1, soal: '' };

            html += `
            <tr>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">${s.nomor_soal}</td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.soal || ''}</td>
                <td style="border: 1px solid black;vertical-align: top;text-align: center;">5</td>
                <td style="border: 1px solid black; vertical-align: top; padding-left: 6px">${s.jawaban || ''}</td>
            </tr>
        `;
        }

        html += `
            </tbody>
        </table>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
    `;
        return html;
    }
</script>
