<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    /* Styling gambar soal agar proporsional, seimbang, dan tidak distorsi */
    .question {
        font-size: 14px;
        line-height: 1.6;
        word-break: break-word;
    }
    .question img {
        max-width: 100% !important;
        height: auto !important;
        max-height: 460px;
        object-fit: contain;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
        margin: 4px 0;
        background-color: transparent;
    }
    /* Formula & ikon kecil dalam baris teks */
    .question p > img,
    .question span > img,
    .question li > img,
    .question td > img {
        max-width: 100%;
        height: auto;
        vertical-align: middle;
    }
    /* Opsi jawaban */
    .question ul, .question ol {
        margin-top: 6px;
        margin-bottom: 8px;
    }
    .question ul li, .question ol li {
        margin-bottom: 5px;
        line-height: 1.5;
    }
    /* Posisi Checkbox, Nomor Soal, dan Tombol Aksi di pojok kiri dan kanan atas (top-aligned) */
    #tbody-pg td,
    #tbody-pg2 td,
    #tbody-jodoh td,
    #tbody-isian td,
    #tbody-esai td,
    #tbody-urutan td,
    #tbody-cloze td {
        vertical-align: top !important;
        padding-top: 12px !important;
    }
    .qs-table-action-col {
        width: 60px;
        text-align: right;
        vertical-align: top !important;
        padding-top: 12px !important;
    }
    .qs-table-action-col .btn {
        display: block;
        margin-bottom: 6px;
    }
</style>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= esc($judul ?? 'Halaman') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title"><?= $subjudul ?></h3>
                <div class="card-tools">
                    <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-warning"
                        title="Reload">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button id="convert" class="btn btn-sm btn-primary" title="Download Detail Soal">
                        <i class="bi bi-download"></i> <span class="d-none d-md-inline ms-1">Download Word</span>
                    </button>
                    <a href="<?= base_url('cbtbanksoal/export_backup/' . $id_bank) ?>" class="btn btn-sm btn-info text-white" title="Backup Bank Soal (ZIP)">
                        <i class="bi bi-file-earmark-zip"></i> <span class="d-none d-md-inline ms-1">Backup Bank Soal</span>
                    </a>
                    <?php if (!empty($bank->kartu_soal_id)) : ?>
                        <a href="<?= base_url('cbtkartusoal/detail/' . $bank->kartu_soal_id) ?>" class="btn btn-sm btn-info"
                            title="Lihat Kartu Soal">
                            <i class="bi bi-card-list"></i> <span class="d-none d-md-inline ms-1">Lihat Kisi-kisi</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('cbtbanksoal/buatsoal/' . $id_bank) ?>" class="btn btn-sm btn-success"
                        title="Tambah/Edit Soal">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline ms-1">Tambah/Edit Soal</span>
                    </a>
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
                                <span class="float-end"><b id="info_kode_bank"></b></span>
                            </li>
                            <li class="list-group-item p-1"> Mata Pelajaran
                                <span class="float-end"><b id="info_nama_mapel"></b></span>
                            </li>
                            <li class="list-group-item p-1"> Guru
                                <span class="float-end">
                                    <b id="info_nama_guru"></b>
                                </span>
                            </li>
                            <li class="list-group-item p-1"> Kelas
                                <span class="float-end"><b id="info_kelasbank"></b></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item p-1"> Total Seharusnya
                                <span class="float-end"><b id="info_soal_seharusnya_tampil"></b></span>
                            </li>
                            <li class="list-group-item p-1"> Total Soal dibuat
                                <span class="float-end"><b id="info_soals"></b></span>
                            </li>
                            <li class="list-group-item p-1"> Total ditampilkan
                                <span class="float-end"><b id="info_total_soal_tampil"></b></span>
                            </li>
                            <li class="list-group-item p-1"> Ket.
                                <span class="float-end"><b id="info_status_soal"></b></span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-3 mt-2">
                        <div class="card shadow-none border">
                            <div class="card-body text-center p-2" id="status_pembuatan">
                                <b>Pembuatan Soal</b>
                                <br>
                                <span class="text-lg"><b id="status_status_soal"></b></span>
                                <br>
                                <span id="status_ket_soal"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-info-circle me-2"></i>Jika keterangan pembuatan soal sudah <b>SELESAI</b>
                            tapi tidak muncul di akun siswa,
                            klik tombol <span class="badge bg-primary"><i class="bi bi-floppy"></i> Simpan Soal
                                Terpilih</span> di bawah.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Sync Notification Area -->
        <div class="card shadow-sm mb-4 d-none" id="sync-notification-card">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0"><i class="bi bi-arrow-repeat me-2"></i>Status Sinkronisasi</h5>
            </div>
            <div class="card-body">
                <div id="sync-status-content">
                    <!-- Sync status akan diisi oleh JavaScript -->
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary" id="btn-check-sync" onclick="checkSyncStatus()">
                        <i class="bi bi-check-circle me-1"></i>Periksa Sinkronisasi
                    </button>
                    <button type="button" class="btn btn-sm btn-info d-none" id="btn-sync-kartu" onclick="syncWithKartu()">
                        <i class="bi bi-cloud-arrow-up me-1"></i>Sinkronkan dengan Kartu Soal
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4" id="tab-soal-area">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" id="badge_pg" href="#ganda"
                            data-bs-toggle="tab"></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" id="badge_pg2" href="#kompleks" data-bs-toggle="tab"></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" id="badge_jodohkan" href="#jodoh" data-bs-toggle="tab"></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" id="badge_isian" href="#isian" data-bs-toggle="tab"></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#essai" id="badge_essai" data-bs-toggle="tab"></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#urutan" id="badge_urutan" data-bs-toggle="tab"></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#cloze" id="badge_cloze" data-bs-toggle="tab"></a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane table-responsive active" id="ganda">
                        <table class="table table-bordered">
                            <tr class="table-dark text-center align-middle">
                                <th class="border-dark">Jenis Soal</th>
                                <th class="border-dark" colspan="2">Jumlah Soal</th>
                                <th class="border-dark">Bobot Nilai</th>
                                <th class="border-dark">Point Per-nomor</th>
                            </tr>
                            <tr class="table-warning">
                                <td rowspan="3" class="text-center align-middle border-dark">Pilihan Ganda</td>
                                <td class="border-dark">Seharusnya</td>
                                <td class="text-center border-dark" id="ganda_soal"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="ganda_bobot"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="ganda_point"></td>
                            </tr>
                            <tr class="table-warning">
                                <td class="border-dark">Telah dibuat</td>
                                <td class="text-center border-dark" id="ganda_total"></td>
                            </tr>
                            <tr class="table-warning">
                                <td class="border-dark">Ditampilkan</td>
                                <td class="text-center border-dark" id="ganda_tampil"></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="5" class="border-dark">
                                    Info :
                                    <ul id="ganda_info"></ul>
                                </td>
                            </tr>
                        </table>

                        <div id="ada_soal_pg">
                            <?= form_open('', array('id' => 'select-pg')) ?>
                            <table class="table draggable-table" id="table-pg">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-pg-all form-check-input cursor-pointer" id="all-pg"
                                                type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua PG</div>
                                                <div class="float-end">
                                                    <span>Jumlah PG terpilih: </span>
                                                    <span id="total-pg" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ms-3"
                                                        id="save-pg">
                                                        <i class="bi bi-floppy"></i>
                                                        <span class="d-none d-md-inline-block ms-1">Simpan Soal
                                                            Terpilih</span>
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
                            <tr class="table-dark text-center align-middle">
                                <th class="border-dark">Jenis Soal</th>
                                <th class="border-dark" colspan="2">Jumlah Soal</th>
                                <th class="border-dark">Bobot Nilai</th>
                                <th class="border-dark">Point Per-nomor</th>
                            </tr>
                            <tr class="table-success">
                                <td rowspan="3" class="text-center align-middle border-dark">PG Kompleks
                                </td>
                                <td class="border-dark">Seharusnya</td>
                                <td class="text-center border-dark" id="kompleks_soal"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="kompleks_bobot">
                                </td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="kompleks_point">
                                </td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Telah dibuat</td>
                                <td class="text-center border-dark" id="kompleks_total"></td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Ditampilkan</td>
                                <td class="text-center border-dark" id="kompleks_tampil"></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="5" class="border-dark">
                                    Info :
                                    <ul id="kompleks_info"></ul>
                                </td>
                            </tr>
                        </table>
                        <div id="ada_soal_pg2">
                            <?= form_open('', array('id' => 'select-pg2')) ?>
                            <table class="table" id="table-pg2">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-pg2-all form-check-input cursor-pointer" id="all-pg2"
                                                type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-end">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-pg2" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ms-3"
                                                        id="save-pg2">
                                                        <i class="bi bi-floppy"></i>
                                                        <span class="d-none d-md-inline-block ms-1">Simpan Soal
                                                            Terpilih</span>
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
                            <tr class="table-dark text-center align-middle">
                                <th class="border-dark">Jenis Soal</th>
                                <th class="border-dark" colspan="2">Jumlah Soal</th>
                                <th class="border-dark">Bobot Nilai</th>
                                <th class="border-dark">Point Per-nomor</th>
                            </tr>
                            <tr class="table-success">
                                <td rowspan="3" class="text-center align-middle border-dark">Menjodohkan</td>
                                <td class="border-dark">Seharusnya</td>
                                <td class="text-center border-dark" id="jodoh_soal"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="jodoh_bobot"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="jodoh_point"></td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Telah dibuat</td>
                                <td class="text-center border-dark" id="jodoh_total"></td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Ditampilkan</td>
                                <td class="text-center border-dark" id="jodoh_tampil"></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="5" class="border-dark">
                                    Info :
                                    <ul id="jodoh_info"></ul>
                                </td>
                            </tr>
                        </table>

                        <div id="ada_soal_jodoh">
                            <?= form_open('', array('id' => 'select-jodohkan')) ?>
                            <table class="table" id="table-jodohkan">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-jodohkan-all form-check-input cursor-pointer"
                                                id="all-jodohkan" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-end">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-jodohkan" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ms-3"
                                                        id="save-jodohkan">
                                                        <i class="bi bi-floppy"></i>
                                                        <span class="d-none d-md-inline-block ms-1">Simpan Soal
                                                            Terpilih</span>
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
                            <tr class="table-dark text-center align-middle">
                                <th class="border-dark">Jenis Soal</th>
                                <th class="border-dark" colspan="2">Jumlah Soal</th>
                                <th class="border-dark">Bobot Nilai</th>
                                <th class="border-dark">Point Per-nomor</th>
                            </tr>
                            <tr class="table-success">
                                <td rowspan="3" class="text-center align-middle border-dark">Isian Singkat</td>
                                <td class="border-dark">Seharusnya</td>
                                <td class="text-center border-dark" id="isian_soal"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="isian_bobot"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="isian_point"></td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Telah dibuat</td>
                                <td class="text-center border-dark" id="isian_total"></td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Ditampilkan</td>
                                <td class="text-center border-dark" id="isian_tampil"></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="5" class="border-dark">
                                    Info :
                                    <ul id="isian_info"></ul>
                                </td>
                            </tr>
                        </table>

                        <div id="ada_soal_isian">
                            <?= form_open('', array('id' => 'select-isian')) ?>
                            <table class="table" id="table-isian">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-isian-all form-check-input cursor-pointer"
                                                id="all-isian" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-end">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-isian" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ms-3"
                                                        id="save-isian">
                                                        <i class="bi bi-floppy"></i>
                                                        <span class="d-none d-md-inline-block ms-1">Simpan Soal
                                                            Terpilih</span>
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
                            <tr class="table-dark text-center align-middle">
                                <th class="border-dark">Jenis Soal</th>
                                <th class="border-dark" colspan="2">Jumlah Soal</th>
                                <th class="border-dark">Bobot Nilai</th>
                                <th class="border-dark">Point Per-nomor</th>
                            </tr>
                            <tr class="table-success">
                                <td rowspan="3" class="text-center align-middle border-dark">Uraian/Essai</td>
                                <td class="border-dark">Seharusnya</td>
                                <td class="text-center border-dark" id="esai_soal"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="esai_bobot"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="esai_point"></td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Telah dibuat</td>
                                <td class="text-center border-dark" id="esai_total"></td>
                            </tr>
                            <tr class="table-success">
                                <td class="border-dark">Ditampilkan</td>
                                <td class="text-center border-dark" id="esai_tampil"></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="5" class="border-dark">
                                    Info :
                                    <ul id="esai_info"></ul>
                                </td>
                            </tr>
                        </table>

                        <div id="ada_soal_esai">
                            <?= form_open('', array('id' => 'select-essai')) ?>
                            <table class="table" id="table-essai">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-essai-all form-check-input cursor-pointer"
                                                id="all-essai" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-end">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-essai" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ms-3"
                                                        id="save-essai">
                                                        <i class="bi bi-floppy"></i>
                                                        <span class="d-none d-md-inline-block ms-1">Simpan Soal
                                                            Terpilih</span>
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
                    <div class="tab-pane table-responsive" id="urutan">
                        <table class="table table-bordered">
                            <tr class="table-dark text-center align-middle">
                                <th class="border-dark">Jenis Soal</th>
                                <th class="border-dark" colspan="2">Jumlah Soal</th>
                                <th class="border-dark">Bobot Nilai</th>
                                <th class="border-dark">Point Per-nomor</th>
                            </tr>
                            <tr class="table-primary">
                                <td rowspan="3" class="text-center align-middle border-dark">Urutan</td>
                                <td class="border-dark">Seharusnya</td>
                                <td class="text-center border-dark" id="urutan_soal"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="urutan_bobot"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="urutan_point"></td>
                            </tr>
                            <tr class="table-primary">
                                <td class="border-dark">Telah dibuat</td>
                                <td class="text-center border-dark" id="urutan_total"></td>
                            </tr>
                            <tr class="table-primary">
                                <td class="border-dark">Ditampilkan</td>
                                <td class="text-center border-dark" id="urutan_tampil"></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="5" class="border-dark">
                                    Info :
                                    <ul id="urutan_info"></ul>
                                </td>
                            </tr>
                        </table>

                        <div id="ada_soal_urutan">
                            <?= form_open('', array('id' => 'select-urutan')) ?>
                            <table class="table" id="table-urutan">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-urutan-all form-check-input cursor-pointer"
                                                id="all-urutan" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-end">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-urutan" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ms-3"
                                                        id="save-urutan">
                                                        <i class="bi bi-floppy"></i>
                                                        <span class="d-none d-md-inline-block ms-1">Simpan Soal
                                                            Terpilih</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-urutan"></tbody>
                            </table>
                            <?= form_close() ?>
                        </div>
                        <div id="emp_soal_urutan" class="d-none">
                            <div class="alert alert-default-info shadow align-content-center" role="alert">
                                Tidak ada soal Urutan
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane table-responsive" id="cloze">
                        <table class="table table-bordered">
                            <tr class="table-dark text-center align-middle">
                                <th class="border-dark">Jenis Soal</th>
                                <th class="border-dark" colspan="2">Jumlah Soal</th>
                                <th class="border-dark">Bobot Nilai</th>
                                <th class="border-dark">Point Per-nomor</th>
                            </tr>
                            <tr class="table-info">
                                <td rowspan="3" class="text-center align-middle border-dark">Isian Rumpang</td>
                                <td class="border-dark">Seharusnya</td>
                                <td class="text-center border-dark" id="cloze_soal"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="cloze_bobot"></td>
                                <td rowspan="3" class="text-center align-middle border-dark" id="cloze_point"></td>
                            </tr>
                            <tr class="table-info">
                                <td class="border-dark">Telah dibuat</td>
                                <td class="text-center border-dark" id="cloze_total"></td>
                            </tr>
                            <tr class="table-info">
                                <td class="border-dark">Ditampilkan</td>
                                <td class="text-center border-dark" id="cloze_tampil"></td>
                            </tr>
                            <tr class="table-danger">
                                <td colspan="5" class="border-dark">
                                    Info :
                                    <ul id="cloze_info"></ul>
                                </td>
                            </tr>
                        </table>

                        <div id="ada_soal_cloze">
                            <?= form_open('', array('id' => 'select-cloze')) ?>
                            <table class="table" id="table-cloze">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="align-top">
                                            <input class="check-cloze-all form-check-input cursor-pointer"
                                                id="all-cloze" type="checkbox">
                                        </th>
                                        <th colspan="3">
                                            <div class="d-sm-flex justify-content-between">
                                                <div>Pilih Semua</div>
                                                <div class="float-end">
                                                    <span>Jumlah soal terpilih: </span>
                                                    <span id="total-cloze" class="text-lg"></span>
                                                    <button type="button" class="btn btn-sm btn-primary ms-3"
                                                        id="save-cloze">
                                                        <i class="bi bi-floppy"></i>
                                                        <span class="d-none d-md-inline-block ms-1">Simpan Soal
                                                            Terpilih</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-cloze"></tbody>
                            </table>
                            <?= form_close() ?>
                        </div>
                        <div id="emp_soal_cloze" class="d-none">
                            <div class="alert alert-default-info shadow align-content-center" role="alert">
                                Tidak ada soal Isian Rumpang
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="temp-list"></div>
        <div id="for-export" class="d-none"></div>
    </div>
</div>
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
    const arrJenis = ['', 'Pilihan Ganda', 'PG Kompleks', 'Menjodohkan', 'Isian Singkat', 'Uraian/Essai', 'Urutan', 'Isian Rumpang'];

    let jmlPgTampil;
    let jmlPg2Tampil;
    let jmlJodohkanTampil;
    let jmlIsianTampil;
    let jmlEssaiTampil;
    let jmlUrutanTampil;
    let jmlClozeTampil;
    let listJodohkan = {};
    let titleDoc = '';

    $(document).ready(function () {
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
                if (result.success === false) {
                    Swal.fire({
                        title: "Error",
                        text: result.message || 'Data bank soal tidak ditemukan atau sesi invalid.',
                        icon: "error"
                    }).then(() => {
                        window.location.href = base_url + 'cbtbanksoal';
                    });
                    return;
                }
                populateContent(result);

                var hash = window.location.hash;
                if (hash) {
                    var tabBox = document.querySelector('.nav-pills a[href="' + hash + '"]');
                    if (tabBox) {
                        var tab = new bootstrap.Tab(tabBox);
                        tab.show();
                    }
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
    });

    /**
     * Memperbaiki dan menormalkan URL gambar, audio, dan video soal agar selalu mutlak
     * dan tidak terkena 404 akibat relative path browser pada /cbtbanksoal/detail/...
     */
    function fixMediaUrl(html) {
        if (!html) return '';
        let str = html.toString();
        const appBase = (typeof base_url !== 'undefined') ? base_url : '<?= base_url() ?>/';
        
        // 1. Replace relative img src
        str = str.replace(/<img\b([^>]*?)\bsrc=["']([^"']+)["']/gi, function(match, prefix, src) {
            if (!src || src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:') || src.startsWith('//')) {
                return match;
            }
            let cleanSrc = src.replace(/^\.?\//, '');
            if (cleanSrc.startsWith('uploads/')) {
                return '<img' + prefix + 'src="' + appBase + cleanSrc + '"';
            } else if (cleanSrc.startsWith('bank_soal/')) {
                return '<img' + prefix + 'src="' + appBase + 'uploads/' + cleanSrc + '"';
            } else {
                return '<img' + prefix + 'src="' + appBase + 'uploads/bank_soal/' + cleanSrc + '"';
            }
        });

        // 2. Replace relative audio / video / source src
        str = str.replace(/<(audio|video|source)\b([^>]*?)\bsrc=["']([^"']+)["']/gi, function(match, tag, prefix, src) {
            if (!src || src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:') || src.startsWith('//')) {
                return match;
            }
            let cleanSrc = src.replace(/^\.?\//, '');
            if (cleanSrc.startsWith('uploads/')) {
                return '<' + tag + prefix + 'src="' + appBase + cleanSrc + '"';
            } else {
                return '<' + tag + prefix + 'src="' + appBase + 'uploads/' + cleanSrc + '"';
            }
        });

        return str;
    }

    /**
     * Badge penanda soal yang dibuat lewat generate AI.
     */
    function soalAiBadge(s) {
        const isAi = s && (s.ai_generated === 1 || s.ai_generated === '1' || s.ai_generated === true);
        if (!isAi) return '';
        return '<div class="mb-1"><span class="badge bg-info text-dark" title="Soal ini dihasilkan oleh AI">' +
            '<i class="bi bi-stars me-1"></i>Soal AI</span></div>';
    }

    function populateContent(result) {
        currentDetailData = result;
        const bank = result.bank;
        const soals = result.soals;
        const kelas = result.kelas;

        const bankKode = bank.bank_kode || 'Ujian';
        const mapel = bank.nama_mapel || 'Mapel';
        const level = bank.bank_level || 'Semua';
        titleDoc = `Naskah_Soal_${bankKode.replace(/[^a-zA-Z0-9_-]/g, '_')}_${mapel.replace(/[^a-zA-Z0-9_-]/g, '_')}_Kls_${level}.docx`;

        jmlPgTampil = bank.tampil_pg;
        jmlPg2Tampil = bank.tampil_kompleks;
        jmlJodohkanTampil = bank.tampil_jodohkan;
        jmlIsianTampil = bank.tampil_isian;
        jmlEssaiTampil = bank.tampil_esai;
        jmlUrutanTampil = bank.tampil_urutan || 0;
        jmlClozeTampil = bank.tampil_cloze || 0;

        // Filter berdasarkan jenis soal
        const soals_pg = soals.filter(s => s.jenis == 1);
        const soals_pg2 = soals.filter(s => s.jenis == 2);
        const soals_jodohkan = soals.filter(s => s.jenis == 3);
        const soals_isian = soals.filter(s => s.jenis == 4);
        const soals_essai = soals.filter(s => s.jenis == 5);

        const soals_urutan = soals.filter(s => s.jenis == 6);
        const soals_cloze = soals.filter(s => s.jenis == 7);

        const countDisplayed = (arr) => arr.filter(s => s.tampilkan == 1).length;

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

        const total_urutan_tampil = countDisplayed(soals_urutan);
        const badge_urutan = total_urutan_tampil < (bank.tampil_urutan || 0) ? badge_danger : badge_success;

        const total_cloze_tampil = countDisplayed(soals_cloze);
        const badge_cloze = total_cloze_tampil < (bank.tampil_cloze || 0) ? badge_danger : badge_success;

        const total_soal_tampil = countDisplayed(soals);
        const total_soal_seharusnya_tampil =
            Number(bank.tampil_pg) + Number(bank.tampil_kompleks) + Number(bank.tampil_jodohkan) +
            Number(bank.tampil_isian) + Number(bank.tampil_esai) + Number(bank.tampil_urutan || 0) + Number(bank.tampil_cloze || 0);

        const tampil_kurang = (
            Number(total_pg_tampil) + Number(total_pg2_tampil) + Number(total_jodohkan_tampil) +
            Number(total_isian_tampil) + Number(total_essai_tampil) + Number(total_urutan_tampil) + Number(total_cloze_tampil)
        ) < total_soal_seharusnya_tampil;

        const soal_kurang = (
            total_pg_tampil < bank.tampil_pg ||
            total_pg2_tampil < bank.tampil_kompleks ||
            total_jodohkan_tampil < bank.tampil_jodohkan ||
            total_isian_tampil < bank.tampil_isian ||
            total_essai_tampil < bank.tampil_esai ||
            total_urutan_tampil < (bank.tampil_urutan || 0) ||
            total_cloze_tampil < (bank.tampil_cloze || 0)
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

        $('#badge_pg').html(`Soal PG ${badge_pg}`)
        $('#badge_pg2').html(`PG Kompleks ${badge_pg2}`)
        $('#badge_jodohkan').html(`Menjodohkan ${badge_jodohkan}`)
        $('#badge_isian').html(`Isian Singkat ${badge_isian}`)
        $('#badge_essai').html(`Essai ${badge_essai}`)
        $('#badge_urutan').html(`Urutan ${badge_urutan}`)
        $('#badge_cloze').html(`Isian Rumpang ${badge_cloze}`)

        $('#info_kode_bank').html(bank.bank_kode);
        $('#info_nama_mapel').html(bank.nama_mapel);
        $('#info_kelasbank').html(kelasbank);
        $('#info_nama_guru').html(bank.nama_guru);
        $('#info_soal_seharusnya_tampil').html(total_soal_seharusnya_tampil);
        $('#info_soals').html(soals.length);
        $('#info_total_soal_tampil').html(total_soal_tampil);
        $('#info_status_soal').html(status_soal);
        $('#status_pembuatan').removeClass('bg-danger bg-success').addClass(bg_color)
        $('#status_status_soal').html(status_soal);
        $('#status_ket_soal').html(ket_soal);

        const totalSiswa = result.total_siswa;
        const disable_delete = bank.digunakan > 0 || totalSiswa > 0;
        $('#all-pg').attr('disabled', disable_delete);
        $('#all-pg2').attr('disabled', disable_delete);
        $('#all-jodohkan').attr('disabled', disable_delete);
        $('#all-isian').attr('disabled', disable_delete);
        $('#all-essai').attr('disabled', disable_delete);
        $('#all-urutan').attr('disabled', disable_delete);
        $('#all-cloze').attr('disabled', disable_delete);

        populateSoalPg(bank, soals_pg, totalSiswa);
        populateSoalKompleks(bank, soals_pg2, totalSiswa);
        populateSoalJodoh(bank, soals_jodohkan, totalSiswa);
        populateSoalIsian(bank, soals_isian, totalSiswa);
        populateSoalEssai(bank, soals_essai, totalSiswa);
        populateSoalUrutan(bank, soals_urutan, totalSiswa);
        populateSoalCloze(bank, soals_cloze, totalSiswa);

        const forExportDiv = document.getElementById('for-export');
        if (forExportDiv) {
            forExportDiv.innerHTML = generateExamPaperHtml(result);
        }

        allListener()
    }

    function populateSoalPg(bank, soals_pg, total_siswa) {
        if (!soals_pg) soals_pg = [];
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
            messages.push('Soal PILIHAN GANDA masih kurang, klik tombol <b>(<i class="bi bi-plus-lg"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
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
                const opsiA = fixMediaUrl((s.opsi_a || '').replace(/<p>|<\/p>/g, ''));
                const opsiB = fixMediaUrl((s.opsi_b || '').replace(/<p>|<\/p>/g, ''));
                const opsiC = fixMediaUrl((s.opsi_c || '').replace(/<p>|<\/p>/g, ''));
                const opsiD = fixMediaUrl((s.opsi_d || '').replace(/<p>|<\/p>/g, ''));
                const opsiE = fixMediaUrl((s.opsi_e || '').replace(/<p>|<\/p>/g, ''));

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
        <td class="text-center align-top" style="vertical-align: top !important; width: 40px; padding-top: 12px;">
            <input class="check-pg form-check-input cursor-pointer" id="${s.id_soal}" type="checkbox"
                   name="soal[]" value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td class="text-center align-top fw-bold" style="vertical-align: top !important; width: 35px; padding-top: 12px; white-space: nowrap;">${s.nomor_soal}.</td>
        <td class="question align-top" data-id="${s.id_soal}" style="vertical-align: top !important; padding-top: 12px;">
            ${soalAiBadge(s)}${fixMediaUrl(s.soal)}
            <br />
            <ul style="list-style-type: upper-alpha; padding-left: 20px;">
                ${opsiList}
            </ul>
            <p>Jawaban: <b>${(s.jawaban || '').toUpperCase()}</b></p>
        </td>
        <td class="qs-table-action-col align-top" style="vertical-align: top !important; width: 60px; text-align: right; padding-top: 12px;">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=1"
               type="button" class="btn btn-sm btn-warning mb-2" title="Edit Soal">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="1"
                    title="Hapus Soal"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="bi bi-trash"></i></button>
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
        if (!soals_pg2) soals_pg2 = [];
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
            messages.push('Soal PILIHAN GANDA KOMPLEKS masih kurang, klik tombol <b>(<i class="bi bi-plus-lg"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
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

        if (soals_pg2.length) {
            const tbody = document.querySelector('#tbody-pg2'); // pastikan tbody punya id ini
            soals_pg2.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';
                const opsi = s.opsi_a || {};
                const jawabanArray = s.jawaban || [];
                const jawaban = Array.isArray(jawabanArray) ? jawabanArray.filter(Boolean).join(', ').toUpperCase() : '';

                let opsiList = '';
                const abjadStart = 97; // ASCII a
                const abjadEnd = abjadStart + Object.keys(opsi).length;

                for (let i = abjadStart; i < abjadEnd; i++) {
                    const abjad = String.fromCharCode(i);
                    const teks = opsi[abjad] ? fixMediaUrl(opsi[abjad].replace(/<p>|<\/p>/g, '')) : 'null';
                    opsiList += `<li>${teks}</li>`;
                }

                const row = document.createElement('tr');
                row.innerHTML = `
        <td class="text-center align-top" style="vertical-align: top !important; width: 40px; padding-top: 12px;">
            <input class="check-pg2 form-check-input cursor-pointer"
                   id="${s.id_soal}" type="checkbox" name="soal[]"
                   value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td class="text-center align-top fw-bold" style="vertical-align: top !important; width: 35px; padding-top: 12px; white-space: nowrap;">${s.nomor_soal}.</td>
        <td class="question align-top" data-id="${s.id_soal}" style="vertical-align: top !important; padding-top: 12px;">
            ${soalAiBadge(s)}${fixMediaUrl(s.soal)}
            <br />
            <ul class="ps-3" style="list-style-type: upper-alpha">
                ${opsiList}
            </ul>
            <p>Jawaban: <b>${jawaban}</b></p>
        </td>
        <td class="qs-table-action-col align-top" style="vertical-align: top !important; width: 60px; text-align: right; padding-top: 12px;">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=2"
               type="button" class="btn btn-sm btn-warning mb-2" title="Edit Soal">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="2"
                    title="Hapus Soal"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="bi bi-trash"></i></button>
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
        if (!soals_jodohkan) soals_jodohkan = [];
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
            messages.push('Soal JODOHKAN masih kurang, klik tombol <b>(<i class="bi bi-plus-lg"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
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

                listJodohkan[`linker_${s.id_soal}`] = { 'no': s.nomor_soal, 'list': jawaban }
                const row = document.createElement('tr');
                row.innerHTML = `
        <td class="text-center align-top" style="vertical-align: top !important; width: 40px; padding-top: 12px;">
            <input class="check-jodohkan form-check-input cursor-pointer"
                   id="${s.id_soal}" type="checkbox" name="soal[]"
                   value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td class="text-center align-top fw-bold" style="vertical-align: top !important; width: 35px; padding-top: 12px; white-space: nowrap;">${s.nomor_soal}.</td>
        <td class="question align-top" data-id="${s.id_soal}" style="vertical-align: top !important; padding-top: 12px;">
            ${soalAiBadge(s)}${fixMediaUrl(s.soal)}
            <br />
            <div class="mb-2 mt-2"><b>Jawaban:</b></div>
            <div class="list-jodohkan" id="linker_${s.id_soal}" data-nomor="${s.nomor_soal}" data-id="${s.id_soal}">
            </div>
        </td>
        <td class="qs-table-action-col align-top" style="vertical-align: top !important; width: 60px; text-align: right; padding-top: 12px;">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=3"
               type="button" class="btn btn-sm btn-warning mb-2" title="Edit Soal">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="3"
                    title="Hapus Soal"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="bi bi-trash"></i></button>
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
        if (!soals_isian) soals_isian = [];
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
            messages.push('Soal ISIAN SINGKAT masih kurang, klik tombol <b>(<i class="bi bi-plus-lg"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
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
        <td class="text-center align-top" style="vertical-align: top !important; width: 40px; padding-top: 12px;">
            <input class="check-isian form-check-input cursor-pointer"
                    id="${s.id_soal}" type="checkbox" name="soal[]"
                    value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td class="text-center align-top fw-bold" style="vertical-align: top !important; width: 35px; padding-top: 12px; white-space: nowrap;">${s.nomor_soal}.</td>
        <td class="question align-top" data-id="${s.id_soal}" style="vertical-align: top !important; padding-top: 12px;">
            ${soalAiBadge(s)}${fixMediaUrl(s.soal)}
            <br />
            <p>Jawaban: <b>${fixMediaUrl(s.jawaban)}</b></p>
        </td>
        <td class="qs-table-action-col align-top" style="vertical-align: top !important; width: 60px; text-align: right; padding-top: 12px;">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=4"
               type="button" class="btn btn-sm btn-warning mb-2" title="Edit Soal">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="4"
                    title="Hapus Soal"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="bi bi-trash"></i></button>
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
        if (!soals_essai) soals_essai = [];
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
                'Soal URAIAN/ESSAY masih kurang, klik tombol <b>(<i class="bi bi-plus-lg"></i> Tambah/Edit Soal)</b> untuk menambahkan.'
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
        <td class="text-center align-top" style="vertical-align: top !important; width: 40px; padding-top: 12px;">
            <input class="check-essai form-check-input cursor-pointer"
                    id="${s.id_soal}" type="checkbox" name="soal[]"
                    value="${s.id_soal}" ${checked} ${dis}>
        </td>
        <td class="text-center align-top fw-bold" style="vertical-align: top !important; width: 35px; padding-top: 12px; white-space: nowrap;">${s.nomor_soal}.</td>
        <td class="question align-top" data-id="${s.id_soal}" style="vertical-align: top !important; padding-top: 12px;">
            ${soalAiBadge(s)}${fixMediaUrl(s.soal)}
            <br />
            <p>Jawaban: <b>${fixMediaUrl(s.jawaban)}</b></p>
        </td>
        <td class="qs-table-action-col align-top" style="vertical-align: top !important; width: 60px; text-align: right; padding-top: 12px;">
            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=5"
               type="button" class="btn btn-sm btn-warning mb-2" title="Edit Soal">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-danger"
                    type="button"
                    data-idsoal="${s.id_soal}"
                    data-nomor="${s.nomor_soal}"
                    data-jenis="5"
                    title="Hapus Soal"
                    onclick="hapusSoal(this)" ${dis}>
                <i class="bi bi-trash"></i></button>
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
        const appBase = (typeof base_url !== 'undefined') ? base_url : '<?= base_url() ?>/';
        
        $('img').each(function () {
            let curSrc = $(this).attr('src');
            if (!curSrc || curSrc.startsWith('data:image')) return;
            if (curSrc.includes('uploads/')) {
                let parts = curSrc.split('uploads/');
                let relativePath = parts[parts.length - 1].replace(/^\/+/, '');
                $(this).attr('src', appBase + 'uploads/' + relativePath);
            } else if (!curSrc.startsWith('http') && !curSrc.startsWith('//')) {
                $(this).attr('src', appBase + curSrc.replace(/^\/+/, ''));
            }
        });

        $.each($(`.question`), function () {
            const dataId = $(this).data('id');
            const audios = $(this).find('audio');
            const videos = $(this).find('video');

            $.each(audios, function (ind) {
                let curSrc = $(this).attr('src');
                if (curSrc && !curSrc.startsWith('data:') && curSrc.includes('uploads/')) {
                    let parts = curSrc.split('uploads/');
                    let relativePath = parts[parts.length - 1].replace(/^\/+/, '');
                    $(this).attr('src', appBase + 'uploads/' + relativePath);
                }

                if (typeof this.renderAudio === 'function') {
                    this.renderAudio({
                        idSoal: dataId + '-' + ind,
                        isPreview: true,
                        callback: function (idSoal, playCount) {
                            console.log(`Audio [${idSoal}] played: ${playCount}`);
                        }
                    });
                }
            });

            $.each(videos, function (ind) {
                let curSrc = $(this).attr('src');
                if (curSrc && !curSrc.startsWith('data:') && curSrc.includes('uploads/')) {
                    let parts = curSrc.split('uploads/');
                    let relativePath = parts[parts.length - 1].replace(/^\/+/, '');
                    $(this).attr('src', appBase + 'uploads/' + relativePath);
                }

                if (typeof this.renderVideo === 'function') {
                    this.renderVideo({
                        idSoal: dataId + '-' + ind,
                        isPreview: true,
                        callback: function (idSoal, playCount) {
                            console.log(`Video [${idSoal}] played: ${playCount}`);
                        }
                    });
                }
            });

        });

        let isMounted = false;
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            allListener();
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
        var urutanCount = $('#table-urutan .check-urutan').length;
        var clozeCount = $('#table-cloze .check-cloze').length;

        //console.log('count', jodohkanCount);

        var pgUnchecked = [];
        var pg2Unchecked = [];
        var jodohkanUnchecked = [];
        var isianUnchecked = [];
        var essaiUnchecked = [];
        var urutanUnchecked = [];
        var clozeUnchecked = [];

        function findUnchecked() {
            pgUnchecked = [];
            pg2Unchecked = [];
            jodohkanUnchecked = [];
            isianUnchecked = [];
            essaiUnchecked = [];
            urutanUnchecked = [];
            clozeUnchecked = [];

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

            $("#table-urutan .check-urutan:not(:checked)").each(function () {
                urutanUnchecked.push($(this).val());
            });

            $("#table-cloze .check-cloze:not(:checked)").each(function () {
                clozeUnchecked.push($(this).val());
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

            var urutanChecked = $("#table-urutan .check-urutan:checked").length;
            $('#total-urutan').html(`<b>${urutanChecked}</b>`);
            $("#all-urutan").prop({
                checked: urutanChecked === urutanCount,
                indeterminate: urutanChecked > 0 && urutanChecked < urutanCount
            });

            var clozeChecked = $("#table-cloze .check-cloze:checked").length;
            $('#total-cloze').html(`<b>${clozeChecked}</b>`);
            $("#all-cloze").prop({
                checked: clozeChecked === clozeCount,
                indeterminate: clozeChecked > 0 && clozeChecked < clozeCount
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

        $("#table-urutan").on("change", ".check-urutan", function () {
            findUnchecked();
        });

        $("#table-cloze").on("change", ".check-cloze", function () {
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

        $("#all-urutan").on("click", function () {
            if (this.checked) {
                $(".check-urutan").each(function () {
                    this.checked = true;
                    $("#all-urutan").prop("checked", true);
                });
            } else {
                $(".check-urutan").each(function () {
                    this.checked = false;
                    $("#all-urutan").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $("#all-cloze").on("click", function () {
            if (this.checked) {
                $(".check-cloze").each(function () {
                    this.checked = true;
                    $("#all-cloze").prop("checked", true);
                });
            } else {
                $(".check-cloze").each(function () {
                    this.checked = false;
                    $("#all-cloze").prop("checked", false);
                });
            }
            findUnchecked();
        });

        $('#save-pg').on('click', function (e) {
            let dataPost = new FormData($('#select-pg')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '1');
            dataPost.append('uncheck', JSON.stringify(pgUnchecked));

            // Kumpulkan semua checkbox yang dicentang
            let selectedSoal = [];
            $("#table-pg .check-pg:checked").each(function () {
                selectedSoal.push($(this).val());
            });

            var checked = selectedSoal.length;
            if (checked !== parseInt(jmlPgTampil)) {
                Swal.fire({
                    title: "Info",
                    html: `Soal Pilihan Ganda terpilih: ${checked} <br>Seharusnya: ${jmlPgTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost, selectedSoal);
            }
        });

        $('#save-pg2').on('click', function (e) {
            let dataPost = new FormData($('#select-pg2')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '2');
            dataPost.append('uncheck', JSON.stringify(pg2Unchecked));

            // Kumpulkan semua checkbox yang dicentang
            let selectedSoal = [];
            $("#table-pg2 .check-pg2:checked").each(function () {
                selectedSoal.push($(this).val());
            });

            var checked = selectedSoal.length;
            if (checked !== parseInt(jmlPg2Tampil)) {
                Swal.fire({
                    title: "Info",
                    html: `Soal Ganda Kompleks terpilih: ${checked} <br>Seharusnya: ${jmlPg2Tampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost, selectedSoal);
            }
        });

        $('#save-jodohkan').on('click', function (e) {
            let dataPost = new FormData($('#select-jodohkan')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '3');
            dataPost.append('uncheck', JSON.stringify(jodohkanUnchecked));

            // Kumpulkan semua checkbox yang dicentang
            let selectedSoal = [];
            $("#table-jodohkan .check-jodohkan:checked").each(function () {
                selectedSoal.push($(this).val());
            });

            var checked = selectedSoal.length;
            if (checked !== parseInt(jmlJodohkanTampil)) {
                Swal.fire({
                    title: "Info",
                    html: `Soal Menjodohkan terpilih: ${checked} <br>Seharusnya: ${jmlJodohkanTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost, selectedSoal);
            }
        });

        $('#save-isian').on('click', function (e) {
            let dataPost = new FormData($('#select-isian')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '4');
            dataPost.append('uncheck', JSON.stringify(isianUnchecked));

            // Kumpulkan semua checkbox yang dicentang
            let selectedSoal = [];
            $("#table-isian .check-isian:checked").each(function () {
                selectedSoal.push($(this).val());
            });

            var checked = selectedSoal.length;
            if (checked !== parseInt(jmlIsianTampil)) {
                Swal.fire({
                    title: "Info",
                    html: `Soal Isian terpilih: ${checked} <br>Seharusnya: ${jmlIsianTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost, selectedSoal);
            }
        });

        $('#save-essai').on('click', function (e) {
            let dataPost = new FormData($('#select-essai')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '5');
            dataPost.append('uncheck', JSON.stringify(essaiUnchecked));

            // Kumpulkan semua checkbox yang dicentang
            let selectedSoal = [];
            $("#table-essai .check-essai:checked").each(function () {
                selectedSoal.push($(this).val());
            });

            var checked = selectedSoal.length;
            if (checked !== parseInt(jmlEssaiTampil)) {
                Swal.fire({
                    title: "Info",
                    html: `Soal Uraian terpilih: ${checked} <br>Seharusnya: ${jmlEssaiTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost, selectedSoal);
            }
        });

        $('#save-urutan').on('click', function (e) {
            let dataPost = new FormData($('#select-urutan')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '6');
            dataPost.append('uncheck', JSON.stringify(urutanUnchecked));

            // Kumpulkan semua checkbox yang dicentang
            let selectedSoal = [];
            $("#table-urutan .check-urutan:checked").each(function () {
                selectedSoal.push($(this).val());
            });

            var checked = selectedSoal.length;
            if (checked !== parseInt(jmlUrutanTampil)) {
                Swal.fire({
                    title: "Info",
                    html: `Soal Urutan terpilih: ${checked} <br>Seharusnya: ${jmlUrutanTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost, selectedSoal);
            }
        });

        $('#save-cloze').on('click', function (e) {
            let dataPost = new FormData($('#select-cloze')[0])
            dataPost.append('id_bank', idBank);
            dataPost.append('jenis', '7');
            dataPost.append('uncheck', JSON.stringify(clozeUnchecked));

            // Kumpulkan semua checkbox yang dicentang
            let selectedSoal = [];
            $("#table-cloze .check-cloze:checked").each(function () {
                selectedSoal.push($(this).val());
            });

            var checked = selectedSoal.length;
            if (checked !== parseInt(jmlClozeTampil)) {
                Swal.fire({
                    title: "Info",
                    html: `Soal Isian Rumpang terpilih: ${checked} <br>Seharusnya: ${jmlClozeTampil}`,
                    icon: "error"
                });
            } else {
                saveSoalSelected(dataPost, selectedSoal);
            }
        });

        function saveSoalSelected(dataPost, selectedSoal) {
            // redundant soals are already in dataPost from FormData(form)
            // if we append again, it will be duplicated in PHP
            
            console.log('Sending data:', {
                id_bank: dataPost.get('id_bank'),
                jenis: dataPost.get('jenis'),
                soal: selectedSoal
            });

            Swal.fire({
                title: "Menyimpan Soal Terpilih",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
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
                    console.log('Response:', data);
                    const count = data.check > 0 ? data.check : '';
                    const jenis = dataPost.get('jenis');
                    let hash = '';
                    if (jenis === '1') hash = '#ganda';
                    else if (jenis === '2') hash = '#kompleks';
                    else if (jenis === '3') hash = '#jodoh';
                    else if (jenis === '4') hash = '#isian';
                    else if (jenis === '5') hash = '#essai';
                    else if (jenis === '6') hash = '#urutan';
                    else if (jenis === '7') hash = '#cloze';

                    if (data.bank_selesai === true) {
                        Swal.fire({
                            title: "🎉 Pembuatan Soal SELESAI!",
                            html: count + ' soal terpilih berhasil disimpan.<br><br><b class="text-success">Status Bank Soal telah diubah menjadi AKTIF.</b><br><small class="text-muted">Bank soal siap dijadwalkan untuk ujian.</small>',
                            icon: "success",
                            showCancelButton: false,
                            confirmButtonColor: "#28a745",
                            confirmButtonText: "OK"
                        }).then(result => {
                            if (result.value) {
                                if (hash) window.location.hash = hash;
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Sukses",
                            html: count + ' Soal terpilih berhasil disimpan',
                            icon: "success",
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "OK"
                        }).then(result => {
                            if (result.value) {
                                if (hash) window.location.hash = hash;
                                window.location.reload();
                            }
                        });
                    }
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    Swal.fire({
                        title: "Error",
                        text: err.Message || err.message || 'Terjadi kesalahan',
                        icon: "error"
                    });
                }
            });
        }

        $("#convert").click(async function (e) {
            e.preventDefault();
            if (!currentDetailData) {
                Swal.fire({
                    title: "Peringatan",
                    text: "Data bank soal sedang dimuat, silakan coba beberapa saat lagi.",
                    icon: "warning"
                });
                return;
            }

            Swal.fire({
                title: "Menyiapkan Dokumen Word",
                html: "Sedang menyusun naskah soal ...<br><small class='text-muted'>Memproses gambar dan lembar ujian offline</small>",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const forExportDiv = document.getElementById('for-export');
                if (forExportDiv) {
                    forExportDiv.innerHTML = generateExamPaperHtml(currentDetailData);
                }

                // Pre-convert all images inside #for-export to base64
                await prepareImagesForWordExport($('#for-export'));

                saveWord();
                Swal.close();
            } catch (err) {
                console.error("Error generating Word document:", err);
                Swal.fire({
                    title: "Gagal Mengunduh Word",
                    text: err.message || "Terjadi kesalahan saat memproses dokumen.",
                    icon: "error"
                });
            }
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
        var contentDocument = $('#for-export').convertToHtmlFile('Naskah Soal Ujian', '');
        var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
        
        var converted = htmlDocx.asBlob(content, {
            orientation: 'portrait',
            size: 'A4',
            margins: { top: 1134, bottom: 1134, left: 1417, right: 1417 }
        });
        saveAs(converted, titleDoc);

        const link = document.createElement('a');
        link.href = URL.createObjectURL(converted);
        link.download = titleDoc;
        link.appendChild(
            document.createTextNode('Klik di sini jika unduhan tidak dimulai secara otomatis'));
        const downloadArea = document.getElementById('download-area');
        downloadArea.innerHTML = '';
        downloadArea.appendChild(link);

        $('#alert-download').removeClass('d-none');

        $("#alert-download").fadeTo(8000, 500).slideUp(500, function () {
            $("#alert-download").slideUp(500);
        });
    }

    function hapusSoal(btn) {
        const id = $(btn).data('idsoal');
        const no = $(btn).data('nomor');
        const jn = $(btn).data('jenis');

        Swal.fire({
            title: "HAPUS ?",
            html: "Soal berikut akan dihapus<br>Nomor: <b>" + no + "</b><br>Jenis: <b>" + arrJenis[jn] + "</b>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
        }).then(result => {
            if (result.value) {
                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
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
                    dataType: "JSON",
                    success: function (result) {
                        console.log("result", result);
                        var isSuccess = result && result.status === true;
                        var tit = isSuccess ? 'BERHASIL' : 'GAGAL';
                        var msg = isSuccess ? 'berhasil' : (result.message || 'gagal');
                        var ic = isSuccess ? 'success' : 'error';
                        Swal.fire({
                            title: tit,
                            text: isSuccess ? "Soal berhasil dihapus" : msg,
                            icon: ic,
                            showCancelButton: false,
                            confirmButtonColor: "#3085d6",
                        }).then(result => {
                            if (result.value && isSuccess) {
                                window.location.reload();
                            }
                        });
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        let errMsg = 'Terjadi kesalahan';
                        try {
                            const err = JSON.parse(xhr.responseText);
                            errMsg = err.message || err.Message || errMsg;
                        } catch (e) {
                            errMsg = xhr.responseText || errMsg;
                        }
                        Swal.fire({
                            title: "Error",
                            text: errMsg,
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

        Swal.fire({
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
     * Membersihkan teks HTML untuk Word Export (merapikan paragraf, gambar, dan spasi)
     */
    function cleanWordHtml(html) {
        if (!html) return '';
        let str = html.toString();
        const appBase = (typeof base_url !== 'undefined') ? base_url : '<?= base_url() ?>/';
        // 1. Fix relative image urls to absolute
        str = str.replace(/<img\b([^>]*?)\bsrc=["']([^"']+)["']/gi, function(match, prefix, src) {
            if (!src || src.startsWith('http://') || src.startsWith('https://') || src.startsWith('data:') || src.startsWith('//')) {
                return match;
            }
            let cleanSrc = src.replace(/^\.?\//, '');
            let finalSrc = cleanSrc.startsWith('uploads/') ? (appBase + cleanSrc) : (appBase + 'uploads/' + cleanSrc);
            return '<img' + prefix + 'src="' + finalSrc + '"';
        });
        // 2. Normalize img styling for docx (respect original size while capping max-width to printable margins)
        str = str.replace(/<img\b([^>]*?)>/gi, function(match, attrs) {
            if (/style=["'][^"']*["']/i.test(attrs)) {
                return match.replace(/style=["']([^"']*)["']/i, function(m, styleContent) {
                    let newStyle = styleContent
                        .replace(/max-width\s*:[^;]+;?/gi, '')
                        .replace(/height\s*:[^;]+;?/gi, '')
                        .trim();
                    if (newStyle && !newStyle.endsWith(';')) newStyle += ';';
                    newStyle += ' max-width: 460px; height: auto; object-fit: contain; margin: 4px 0; vertical-align: middle;';
                    return 'style="' + newStyle + '"';
                });
            } else {
                return '<img' + attrs + ' style="max-width: 460px; height: auto; object-fit: contain; margin: 4px 0; vertical-align: middle;">';
            }
        });
        // 3. Clean multiple consecutive empty paragraphs
        str = str.replace(/(<p[^>]*>\s*(<br\s*\/?>|&nbsp;|\s)*<\/p>\s*){2,}/gi, '<p style="margin: 3px 0;">&nbsp;</p>');
        return str;
    }

    /**
     * Membersihkan opsi jawaban (menghilangkan wrapper <p> tunggal agar sejajar huruf A. B. C.)
     */
    function cleanOptionWordHtml(html) {
        if (!html) return '';
        let str = cleanWordHtml(html).trim();
        // Jika hanya dibungkus 1 tag <p>...</p>, buka isinya agar sejajar
        if (str.startsWith('<p>') && str.endsWith('</p>') && (str.match(/<p>/g) || []).length === 1) {
            str = str.substring(3, str.length - 4).trim();
        }
        return str;
    }

    /**
     * Mengonversi semua gambar di dalam container ke Base64 canvas data URI
     * agar tersemat langsung ke dalam paket file DOCX (offline ready)
     */
    async function prepareImagesForWordExport($container) {
        const images = $container.find('img').toArray();
        for (const img of images) {
            try {
                if (img.src && !img.src.startsWith('data:image')) {
                    const base64 = await convertImgToBase64(img);
                    if (base64) {
                        img.src = base64;
                    }
                }
            } catch (e) {
                console.warn('Image convert warning:', e);
            }
        }
    }

    function convertImgToBase64(img) {
        return new Promise((resolve) => {
            if (img.complete && img.naturalWidth) {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                    return;
                } catch (e) {
                    // Fallback to cross-origin image load
                }
            }

            const newImg = new Image();
            newImg.crossOrigin = 'Anonymous';
            newImg.onload = function () {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = newImg.naturalWidth;
                    canvas.height = newImg.naturalHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(newImg, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                } catch (e) {
                    resolve(null);
                }
            };
            newImg.onerror = function () {
                resolve(null);
            };
            newImg.src = img.src;
        });
    }

    /**
     * Menghasilkan dokumen Word standar Naskah Ujian Nasional / Asesmen Sekolah Offline
     * Lengkap dengan Kop Sekolah, Identitas Ujian/Siswa, Petunjuk Umum, Naskah Soal,
     * Lembar Jawaban Siswa (LJU), dan Lembar Kunci Jawaban & Rubrik Guru.
     */
    function generateExamPaperHtml(result) {
        if (!result || !result.bank) return '';
        const bank = result.bank;
        const allSoals = result.soals || [];
        const kelasList = result.kelas || [];
        const setting = result.setting || {};
        const tp = result.tp || {};
        const smt = result.smt || {};

        // Ambil nama kelas
        let kelasNames = '';
        if (bank.bank_kelas && Array.isArray(bank.bank_kelas)) {
            kelasNames = bank.bank_kelas
                .map(jk => {
                    const found = kelasList.find(k => k.id_kelas?.toString() === jk.kelas_id?.toString());
                    return found ? found.nama_kelas : null;
                })
                .filter(Boolean)
                .join(', ');
        }
        if (!kelasNames && bank.bank_level) {
            kelasNames = 'Kelas ' + bank.bank_level;
        }

        // Filter soal aktif / ditampilkan (jika tidak ada yang tampilkan=1, pakai semua)
        let soals = allSoals.filter(s => s.tampilkan === '1' || s.tampilkan === 1);
        if (soals.length === 0) soals = allSoals;

        // Pisahkan per jenis
        const pgSoals = soals.filter(s => s.jenis == 1);
        const pgkSoals = soals.filter(s => s.jenis == 2);
        const matchSoals = soals.filter(s => s.jenis == 3);
        const isianSoals = soals.filter(s => s.jenis == 4);
        const esaiSoals = soals.filter(s => s.jenis == 5);
        const urutanSoals = soals.filter(s => s.jenis == 6);
        const clozeSoals = soals.filter(s => s.jenis == 7);

        // Info Kop Sekolah
        const namaSekolah = (setting.sekolah || setting.nama_sekolah || 'SATUAN PENDIDIKAN').toUpperCase();
        const alamatSekolah = [setting.alamat, setting.desa, setting.kecamatan, setting.kota, setting.provinsi].filter(Boolean).join(', ');
        const kontakSekolah = [
            setting.telp ? 'Telp: ' + setting.telp : '',
            setting.email ? 'Email: ' + setting.email : '',
            (setting.web || setting.website) ? 'Web: ' + (setting.web || setting.website) : ''
        ].filter(Boolean).join(' | ');

        let logoKiriHtml = '';
        let logoKiriSrc = setting.logo_kiri || setting.logo || '';
        if (logoKiriSrc) {
            if (!logoKiriSrc.startsWith('http') && !logoKiriSrc.startsWith('data:')) {
                logoKiriSrc = base_url + (logoKiriSrc.startsWith('/') ? logoKiriSrc.substring(1) : logoKiriSrc);
            }
            logoKiriHtml = `<img src="${logoKiriSrc}" style="max-height: 70px; max-width: 75px; object-fit: contain;" alt="Logo"/>`;
        }

        let logoKananHtml = '';
        let logoKananSrc = setting.logo_kanan || '';
        if (logoKananSrc) {
            if (!logoKananSrc.startsWith('http') && !logoKananSrc.startsWith('data:')) {
                logoKananSrc = base_url + (logoKananSrc.startsWith('/') ? logoKananSrc.substring(1) : logoKananSrc);
            }
            logoKananHtml = `<img src="${logoKananSrc}" style="max-height: 70px; max-width: 75px; object-fit: contain;" alt="Logo"/>`;
        }

        const tpTeks = tp.tahun || tp.nama_tp || '2025/2026';
        let smtTeks = smt.smt_nama || smt.nama_smt || '';
        if (!smtTeks) {
            smtTeks = (bank.id_smt == '1' || bank.id_smt == 1) ? 'SEMESTER GANJIL' : 'SEMESTER GENAP';
        } else {
            smtTeks = smtTeks.toUpperCase();
        }

        let kopHtml = '';
        let kopSekolahSrc = setting.kopsekolah || '';
        if (kopSekolahSrc && kopSekolahSrc.trim() !== '') {
            let appBase = (typeof base_url !== 'undefined') ? base_url : '<?= base_url() ?>/';
            if (!kopSekolahSrc.startsWith('http') && !kopSekolahSrc.startsWith('data:')) {
                kopSekolahSrc = appBase + (kopSekolahSrc.startsWith('/') ? kopSekolahSrc.substring(1) : kopSekolahSrc);
            }
            kopHtml = `
                <!-- HEADER BANNER KOP SEKOLAH DARI PENGATURAN -->
                <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px;">
                    <tr>
                        <td style="text-align: center; vertical-align: middle; border: none; padding: 0;">
                            <img src="${kopSekolahSrc}" style="width: 100%; max-width: 700px; height: auto;" alt="KOP SEKOLAH"/>
                        </td>
                    </tr>
                </table>
            `;
        } else {
            let header1Html = setting.header_1 ? `<div style="font-size: 10pt; font-weight: bold; text-transform: uppercase;">${setting.header_1}</div>` : '';
            let header2Html = setting.header_2 ? `<div style="font-size: 10.5pt; font-weight: bold; text-transform: uppercase;">${setting.header_2}</div>` : '';
            let namaSekolahHtml = `<div style="font-size: 13.5pt; font-weight: bold; text-transform: uppercase; margin: 2px 0;">${namaSekolah}</div>`;
            let alamatHtml = (setting.header_3 || alamatSekolah) ? `<div style="font-size: 9pt; font-style: italic; margin-bottom: 1px;">${setting.header_3 || alamatSekolah}</div>` : '';
            let kontakHtml = (setting.header_4 || kontakSekolah) ? `<div style="font-size: 8.5pt; color: #333;">${setting.header_4 || kontakSekolah}</div>` : '';

            kopHtml = `
                <!-- KOP SURAT RESMI SEKOLAH (TEKS + LOGO) -->
                <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 2px;">
                    <tr>
                        <td style="width: 80px; text-align: center; vertical-align: middle; border: none; padding: 2px;">
                            ${logoKiriHtml}
                        </td>
                        <td style="text-align: center; vertical-align: middle; border: none; padding: 2px;">
                            ${header1Html}
                            ${header2Html}
                            ${namaSekolahHtml}
                            ${alamatHtml}
                            ${kontakHtml}
                        </td>
                        <td style="width: 80px; text-align: center; vertical-align: middle; border: none; padding: 2px;">
                            ${logoKananHtml}
                        </td>
                    </tr>
                </table>

                <!-- GARIS GANDA PEMBATAS KOP -->
                <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px;">
                    <tr>
                        <td style="border-top: 2.5px solid #000; border-bottom: 1px solid #000; height: 3px; border-left: none; border-right: none; padding: 0;"></td>
                    </tr>
                </table>
            `;
        }

        let docHtml = `
        <div style="font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; line-height: 1.25;">
            ${kopHtml}

            <!-- JUDUL NASKAH SOAL -->
            <div style="text-align: center; margin-bottom: 10px;">
                <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; text-decoration: underline;">
                    NASKAH SOAL ASESMEN / UJIAN SEKOLAH TERTULIS
                </div>
                <div style="font-size: 10.5pt; font-weight: bold; margin-top: 2px;">
                    TAHUN PELAJARAN ${tpTeks} - ${smtTeks}
                </div>
            </div>

            <!-- TABEL IDENTITAS UJIAN & PESERTA (2 KOLOM BERSANDING) -->
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt;">
                <tr>
                    <td style="width: 50%; border: 1px solid #000; vertical-align: top; padding: 6px 8px;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr>
                                <td style="width: 120px; vertical-align: top; border: none; padding: 2px 0;">Mata Pelajaran</td>
                                <td style="width: 10px; vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${bank.nama_mapel || '-'}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Kelas / Tingkat</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">${kelasNames}</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Alokasi Waktu</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">${bank.alokasi_waktu || 90} Menit</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Guru Pengampu</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">${bank.nama_guru || '-'}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; border: 1px solid #000; vertical-align: top; padding: 6px 8px;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr>
                                <td style="width: 110px; vertical-align: top; border: none; padding: 2px 0;">Hari / Tanggal</td>
                                <td style="width: 10px; vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Waktu / Sesi</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">Nama Peserta</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">No. Ujian / Ruang</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">:</td>
                                <td style="vertical-align: top; border: none; padding: 2px 0;">...................................................</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- KOTAK PETUNJUK UMUM STANDAR UJIAN -->
            <div style="border: 1px solid #000; padding: 6px 10px; margin-bottom: 14px; font-size: 9pt; background-color: #fcfcfc;">
                <div style="font-weight: bold; margin-bottom: 3px;">PETUNJUK UMUM:</div>
                <ol style="margin: 0; padding-left: 18px;">
                    <li style="margin-bottom: 1px;">Isikan identitas Anda pada lembar jawaban yang telah disediakan secara lengkap dan benar.</li>
                    <li style="margin-bottom: 1px;">Periksa dan bacalah setiap butir soal dengan saksama sebelum Anda memberikan jawaban.</li>
                    <li style="margin-bottom: 1px;">Laporkan kepada pengawas ujian apabila terdapat lembar soal yang kurang jelas, rusak, atau tidak lengkap.</li>
                    <li style="margin-bottom: 1px;">Dahulukan menjawab soal-soal yang Anda anggap mudah.</li>
                    <li style="margin-bottom: 1px;">Tidak diperkenankan menggunakan kalkulator, ponsel (HP), atau alat bantu lainnya kecuali ada petunjuk khusus.</li>
                    <li style="margin-bottom: 1px;">Periksalah kembali seluruh lembar jawaban Anda sebelum diserahkan kepada pengawas ujian.</li>
                </ol>
            </div>
        `;

        // BAGIAN A: SOAL PILIHAN GANDA
        if (pgSoals.length > 0) {
            const jmlOpsi = parseInt(bank.opsi || 5);
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 14px; margin-bottom: 3px;">A. SOAL PILIHAN GANDA</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Pilihlah salah satu jawaban yang paling tepat dengan memberi tanda silang (X) atau menghitamkan bulatan pada huruf A, B, C, D, atau E pada lembar jawaban yang telah disediakan!
                </div>
            `;

            pgSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                const opsiA = cleanOptionWordHtml(s.opsi_a);
                const opsiB = cleanOptionWordHtml(s.opsi_b);
                const opsiC = cleanOptionWordHtml(s.opsi_c);
                const opsiD = cleanOptionWordHtml(s.opsi_d);
                const opsiE = cleanOptionWordHtml(s.opsi_e);

                let opsiRows = `
                    <tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">A.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiA}</td></tr>
                    <tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">B.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiB}</td></tr>
                    <tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">C.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiC}</td></tr>
                `;
                if (jmlOpsi >= 4 || opsiD) {
                    opsiRows += `<tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">D.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiD}</td></tr>`;
                }
                if (jmlOpsi >= 5 || opsiE) {
                    opsiRows += `<tr><td style="width: 24px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">E.</td><td style="vertical-align: top; border: none; padding: 2px 0;">${opsiE}</td></tr>`;
                }

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 4px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 2px;">
                                    ${opsiRows}
                                </table>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN B: SOAL PILIHAN GANDA KOMPLEKS
        if (pgkSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">B. SOAL PILIHAN GANDA KOMPLEKS</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Pilihlah jawaban yang benar (jawaban benar dapat lebih dari satu) dengan memberi tanda centang (✓) pada kotak di depan setiap pernyataan yang sesuai!
                </div>
            `;

            pgkSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                let opsis = s.opsi_a;
                if (typeof opsis === 'string') {
                    try { opsis = JSON.parse(opsis); } catch(e) { opsis = {}; }
                }
                opsis = opsis || {};

                let opsiRows = '';
                const keys = Object.keys(opsis).sort();
                keys.forEach(k => {
                    const txt = cleanOptionWordHtml(opsis[k]);
                    opsiRows += `
                        <tr>
                            <td style="width: 32px; vertical-align: top; border: none; padding: 2px 0; font-family: monospace; font-size: 10.5pt;">[ &nbsp; ]</td>
                            <td style="width: 22px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${k.toUpperCase()}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">${txt}</td>
                        </tr>
                    `;
                });

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 4px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 2px;">
                                    ${opsiRows}
                                </table>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN C: SOAL MENJODOHKAN
        if (matchSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">C. SOAL MENJODOHKAN</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Pasangkanlah pernyataan/pertanyaan pada Kolom Kiri dengan pilihan jawaban yang paling tepat pada Kolom Kanan!
                </div>
            `;

            matchSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                let rawJawaban = s.jawaban;
                if (typeof rawJawaban === 'string') {
                    try { rawJawaban = JSON.parse(rawJawaban); } catch(e) { rawJawaban = {}; }
                }
                const grid = rawJawaban?.jawaban || rawJawaban || [];
                
                let matchTableRows = '';
                if (Array.isArray(grid) && grid.length > 1) {
                    const rowHeaders = [];
                    const colHeaders = [];
                    // Header Kolom B (index 0, cols 1..N)
                    if (grid[0] && Array.isArray(grid[0])) {
                        for (let c = 1; c < grid[0].length; c++) {
                            if (grid[0][c]) colHeaders.push(grid[0][c]);
                        }
                    }
                    // Header Kolom A (rows 1..N, col 0)
                    for (let r = 1; r < grid.length; r++) {
                        if (grid[r] && grid[r][0]) rowHeaders.push(grid[r][0]);
                    }

                    const maxRows = Math.max(rowHeaders.length, colHeaders.length);
                    for (let m = 0; m < maxRows; m++) {
                        const leftText = rowHeaders[m] ? `<b>${m + 1}.</b> ${cleanOptionWordHtml(rowHeaders[m])}` : '';
                        const rightLetter = String.fromCharCode(65 + m);
                        const rightText = colHeaders[m] ? `<b>${rightLetter}.</b> ${cleanOptionWordHtml(colHeaders[m])}` : '';
                        matchTableRows += `
                            <tr>
                                <td style="width: 50%; border: 1px solid #000; padding: 4px 6px; vertical-align: top;">${leftText}</td>
                                <td style="width: 50%; border: 1px solid #000; padding: 4px 6px; vertical-align: top;">${rightText}</td>
                            </tr>
                        `;
                    }
                }

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 10px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 4px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                ${matchTableRows ? `
                                    <table style="width: 96%; border: 1px solid #000; border-collapse: collapse; font-size: 9.5pt; margin-top: 3px;">
                                        <thead>
                                            <tr style="background-color: #f2f2f2;">
                                                <th style="width: 50%; border: 1px solid #000; padding: 4px 6px; text-align: center;">Kolom A (Pernyataan / Soal)</th>
                                                <th style="width: 50%; border: 1px solid #000; padding: 4px 6px; text-align: center;">Kolom B (Pilihan Pasangan)</th>
                                            </tr>
                                        </thead>
                                        <tbody>${matchTableRows}</tbody>
                                    </table>
                                ` : ''}
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN D: SOAL ISIAN SINGKAT
        if (isianSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">D. SOAL ISIAN SINGKAT</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Isilah titik-titik di bawah ini dengan jawaban yang singkat, tepat, dan benar!
                </div>
            `;

            isianSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 3px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <div style="margin-top: 4px; font-style: italic; color: #333;">
                                    Jawaban: ................................................................................................................................................
                                </div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN E: SOAL URAIAN / ESSAY
        if (esaiSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">E. SOAL URAIAN / ESSAY</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Jawablah pertanyaan-pertanyaan berikut dengan uraian yang jelas, sistematis, dan terperinci pada lembar jawaban yang tersedia!
                </div>
            `;

            esaiSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 12px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 6px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                                <div style="margin-top: 6px; border-bottom: 1px dotted #888; height: 18px;"></div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN F: SOAL URUTAN (JIKA ADA)
        if (urutanSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">F. SOAL MENGURUTKAN (SEQUENCING)</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Susunlah butir-butir pernyataan/langkah berikut menjadi urutan yang runtut, logis, dan benar!
                </div>
            `;

            urutanSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                let parsed = s.opsi_a;
                if (typeof parsed === 'string') {
                    try { parsed = JSON.parse(parsed); } catch(e) { parsed = {}; }
                }
                const items = parsed?.items || [];
                let itemsList = '';
                items.forEach((it, iIdx) => {
                    itemsList += `<li>${cleanOptionWordHtml(it)}</li>`;
                });

                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 10px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 3px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                                <ul style="margin: 2px 0 6px 18px; padding: 0;">${itemsList}</ul>
                                <div style="font-style: italic; color: #333;">
                                    Urutan yang benar: ..........................................................................................................
                                </div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // BAGIAN G: ISIAN RUMPANG (JIKA ADA)
        if (clozeSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 11pt; margin-top: 18px; margin-bottom: 3px;">G. SOAL ISIAN RUMPANG (CLOZE TEST)</div>
                <div style="font-size: 9.5pt; font-style: italic; margin-bottom: 8px;">
                    Petunjuk: Lengkapilah bagian kalimat/paragraf yang rumpang di bawah ini dengan kata atau frasa yang tepat!
                </div>
            `;

            clozeSoals.forEach((s, idx) => {
                const noSoal = s.nomor_soal || (idx + 1);
                docHtml += `
                    <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 8px; font-size: 11pt;">
                        <tr>
                            <td style="width: 26px; vertical-align: top; font-weight: bold; border: none; padding: 2px 0;">${noSoal}.</td>
                            <td style="vertical-align: top; border: none; padding: 2px 0;">
                                <div style="margin-bottom: 3px; text-align: justify;">${cleanWordHtml(s.soal)}</div>
                            </td>
                        </tr>
                    </table>
                `;
            });
        }

        // ============================================================
        // HALAMAN 2: LEMBAR JAWABAN SISWA (STUDENT ANSWER SHEET)
        // ============================================================
        docHtml += `
            <div style="page-break-before: always; margin-top: 20px;"></div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 10px;">
                <tr>
                    <td style="text-align: center; padding: 6px; background-color: #f2f2f2;">
                        <div style="font-weight: bold; font-size: 11.5pt; text-transform: uppercase;">LEMBAR JAWABAN UJIAN TERTULIS (LJU)</div>
                        <div style="font-size: 9.5pt; font-weight: bold;">${namaSekolah} - TAHUN PELAJARAN ${tpTeks}</div>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt;">
                <tr>
                    <td style="width: 58%; border: 1px solid #000; padding: 6px 8px; vertical-align: top;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="width: 105px; padding: 2px 0;">Nama Siswa</td><td style="width: 10px;">:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">No. Peserta / NIS</td><td>:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">Kelas / Ruang</td><td>:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">Mata Pelajaran</td><td>:</td><td><b>${bank.nama_mapel || '-'}</b></td></tr>
                            <tr><td style="padding: 2px 0;">Hari / Tanggal</td><td>:</td><td>............................................................</td></tr>
                            <tr><td style="padding: 2px 0;">Tanda Tangan</td><td>:</td><td>............................................................</td></tr>
                        </table>
                    </td>
                    <td style="width: 42%; border: 1px solid #000; padding: 6px 8px; vertical-align: top; text-align: center;">
                        <div style="font-weight: bold; margin-bottom: 3px; font-size: 9pt;">KOLOM PENILAIAN GURU</div>
                        <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 8.5pt; margin-top: 2px;">
                            <tr style="background-color: #f2f2f2;">
                                <th style="border: 1px solid #000; padding: 3px;">Nilai PG</th>
                                <th style="border: 1px solid #000; padding: 3px;">Nilai Uraian</th>
                                <th style="border: 1px solid #000; padding: 3px;">Nilai Akhir</th>
                                <th style="border: 1px solid #000; padding: 3px;">Paraf Guru</th>
                            </tr>
                            <tr style="height: 38px;">
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                                <td style="border: 1px solid #000;"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 4px;">I. LEMBAR JAWABAN PILIHAN GANDA</div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 8.5pt; text-align: center; margin-bottom: 12px;">
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 1 - 10</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 11 - 20</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 21 - 30</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 31 - 40</th>
                    <th style="border: 1px solid #000; padding: 3px; width: 20%;">No. 41 - 50</th>
                </tr>
        `;

        const jmlOpsiLjk = parseInt(bank.opsi || 5);
        let ljkLetters = 'A &nbsp;B &nbsp;C &nbsp;D';
        if (jmlOpsiLjk >= 5) ljkLetters += ' &nbsp;E';

        for (let r = 1; r <= 10; r++) {
            docHtml += `<tr>`;
            for (let c = 0; c < 5; c++) {
                const qNum = r + (c * 10);
                docHtml += `
                    <td style="border: 1px solid #000; padding: 3px 4px; text-align: left;">
                        <b>${qNum < 10 ? '0' + qNum : qNum}.</b> [ ${ljkLetters} ]
                    </td>
                `;
            }
            docHtml += `</tr>`;
        }

        docHtml += `
            </table>

            <div style="font-weight: bold; font-size: 10pt; margin-bottom: 4px;">II. LEMBAR JAWABAN ISIAN SINGKAT & URAIAN</div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 9pt;">
                <tr>
                    <td style="border: 1px solid #000; height: 160px; vertical-align: top; padding: 6px;">
                        <div style="font-style: italic; color: #888; margin-bottom: 4px;">Tuliskan jawaban Anda di bawah ini:</div>
                    </td>
                </tr>
            </table>

            <!-- ============================================================ -->
            <!-- HALAMAN 3: KUNCI JAWABAN & PEDOMAN PENSKORAN (PEGANGAN GURU) -->
            <!-- ============================================================ -->
            <div style="page-break-before: always; margin-top: 20px;"></div>
            <div style="text-align: center; margin-bottom: 10px;">
                <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; text-decoration: underline;">
                    KUNCI JAWABAN & PEDOMAN PENSKORAN
                </div>
                <div style="font-size: 9.5pt; font-weight: bold; margin-top: 2px;">
                    (DOKUMEN RAHASIA - KHUSUS PEGANGAN GURU / PENGUJI)
                </div>
                <div style="font-size: 9.5pt; margin-top: 2px;">
                    MATA PELAJARAN: <b>${bank.nama_mapel || '-'}</b> | KELAS: <b>${bank.bank_level || '-'}</b> | KODE BANK: <b>${bank.bank_kode || '-'}</b>
                </div>
            </div>
        `;

        // Rekap Kunci Pilihan Ganda (Grid 1-50)
        if (pgSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 10.5pt; margin-top: 10px; margin-bottom: 4px;">A. Kunci Jawaban Pilihan Ganda:</div>
                <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 8.5pt; text-align: center; margin-bottom: 12px;">
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                        <th style="border: 1px solid #000; padding: 3px; width: 10%;">No</th><th style="border: 1px solid #000; padding: 3px; width: 10%;">Kunci</th>
                    </tr>
            `;

            // Buat map kunci PG
            const pgKeyMap = {};
            pgSoals.forEach(s => {
                pgKeyMap[s.nomor_soal] = (s.jawaban || '').toUpperCase();
            });

            const maxPgNum = Math.max(pgSoals.length, 10);
            const totalRowsGrid = Math.ceil(maxPgNum / 5);

            for (let gr = 0; gr < totalRowsGrid; gr++) {
                docHtml += `<tr>`;
                for (let gc = 0; gc < 5; gc++) {
                    const qn = (gr + 1) + (gc * totalRowsGrid);
                    const kVal = pgKeyMap[qn] || '-';
                    docHtml += `
                        <td style="border: 1px solid #000; padding: 3px; background-color: #fafafa; font-weight: bold;">${qn}</td>
                        <td style="border: 1px solid #000; padding: 3px; font-weight: bold; color: #004085;">${kVal}</td>
                    `;
                }
                docHtml += `</tr>`;
            }

            docHtml += `</table>`;
        }

        // Rekap Kunci Jenis Lainnya
        if (pgkSoals.length > 0 || matchSoals.length > 0 || isianSoals.length > 0 || esaiSoals.length > 0) {
            docHtml += `
                <div style="font-weight: bold; font-size: 10.5pt; margin-top: 10px; margin-bottom: 4px;">B. Kunci Jawaban Non-Pilihan Ganda & Rubrik Penilaian:</div>
                <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 9.5pt; margin-bottom: 12px;">
                    <thead>
                        <tr style="background-color: #f2f2f2; font-weight: bold;">
                            <th style="width: 45px; border: 1px solid #000; padding: 4px; text-align: center;">No</th>
                            <th style="width: 130px; border: 1px solid #000; padding: 4px; text-align: center;">Bentuk Soal</th>
                            <th style="border: 1px solid #000; padding: 4px; text-align: center;">Kunci Jawaban / Kriteria Penilaian</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            pgkSoals.forEach(s => {
                let ans = s.jawaban;
                if (Array.isArray(ans)) ans = ans.join(', ').toUpperCase();
                else if (typeof ans === 'string') ans = ans.toUpperCase();
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">PG Kompleks</td>
                        <td style="border: 1px solid #000; padding: 4px;"><b>${ans || '-'}</b></td>
                    </tr>
                `;
            });

            matchSoals.forEach(s => {
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">Menjodohkan</td>
                        <td style="border: 1px solid #000; padding: 4px;">Pasangan benar sesuai kunci kisi-kisi</td>
                    </tr>
                `;
            });

            isianSoals.forEach(s => {
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">Isian Singkat</td>
                        <td style="border: 1px solid #000; padding: 4px;"><b>${s.jawaban || '-'}</b></td>
                    </tr>
                `;
            });

            esaiSoals.forEach(s => {
                const jwb = s.jawaban ? cleanWordHtml(s.jawaban) : '';
                const pmb = s.pembahasan ? cleanWordHtml(s.pembahasan) : '';
                docHtml += `
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">${s.nomor_soal}</td>
                        <td style="border: 1px solid #000; padding: 4px;">Uraian / Essay</td>
                        <td style="border: 1px solid #000; padding: 4px;">
                            ${jwb ? `<div><b>Jawaban Model:</b> ${jwb}</div>` : ''}
                            ${pmb ? `<div style="margin-top: 2px; color: #444;"><b>Rubrik/Pembahasan:</b> ${pmb}</div>` : (jwb ? '' : 'Sesuai ketepatan uraian dan rubrik guru')}
                        </td>
                    </tr>
                `;
            });

            docHtml += `</tbody></table>`;
        }

        // Rekap Bobot Penilaian
        docHtml += `
            <div style="font-weight: bold; font-size: 10pt; margin-top: 10px; margin-bottom: 4px;">C. Rekap Bobot & Pedoman Penskoran:</div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; font-size: 9pt; margin-bottom: 14px;">
                <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
                    <th style="border: 1px solid #000; padding: 4px;">Bentuk Soal</th>
                    <th style="border: 1px solid #000; padding: 4px;">Jumlah Soal</th>
                    <th style="border: 1px solid #000; padding: 4px;">Bobot (%)</th>
                    <th style="border: 1px solid #000; padding: 4px;">Poin per Nomor</th>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Pilihan Ganda</td>
                    <td style="border: 1px solid #000; padding: 3px;">${pgSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_pg || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_pg > 0 && pgSoals.length > 0) ? (bank.bobot_pg / pgSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">PG Kompleks</td>
                    <td style="border: 1px solid #000; padding: 3px;">${pgkSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_kompleks || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_kompleks > 0 && pgkSoals.length > 0) ? (bank.bobot_kompleks / pgkSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Menjodohkan</td>
                    <td style="border: 1px solid #000; padding: 3px;">${matchSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_jodohkan || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_jodohkan > 0 && matchSoals.length > 0) ? (bank.bobot_jodohkan / matchSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Isian Singkat</td>
                    <td style="border: 1px solid #000; padding: 3px;">${isianSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_isian || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_isian > 0 && isianSoals.length > 0) ? (bank.bobot_isian / isianSoals.length).toFixed(2) : '-'}</td>
                </tr>
                <tr style="text-align: center;">
                    <td style="border: 1px solid #000; padding: 3px; text-align: left;">Uraian / Essay</td>
                    <td style="border: 1px solid #000; padding: 3px;">${esaiSoals.length}</td>
                    <td style="border: 1px solid #000; padding: 3px;">${bank.bobot_esai || 0}%</td>
                    <td style="border: 1px solid #000; padding: 3px;">${(bank.bobot_esai > 0 && esaiSoals.length > 0) ? (bank.bobot_esai / esaiSoals.length).toFixed(2) : '-'}</td>
                </tr>
            </table>

            <!-- KOLOM TANDA TANGAN PENGESAHAN -->
            <table style="width: 100%; border: none; border-collapse: collapse; margin-top: 20px; font-size: 10pt;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 4px;">
                        <div>Mengetahui,</div>
                        <div>Kepala Sekolah</div>
                        <div style="height: 50px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">${setting.kepsek || '................................................'}</div>
                        <div>NIP. ${setting.nip || setting.nip_kepsek || '-'}</div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 4px;">
                        <div>${setting.kota || '....................'}, .................................... 20...</div>
                        <div>Guru Mata Pelajaran</div>
                        <div style="height: 50px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">${bank.nama_guru || '................................................'}</div>
                        <div>NIP. ${bank.nip_guru || '-'}</div>
                    </td>
                </tr>
            </table>
        </div>
        `;

        return docHtml;
    }

    function populateSoalUrutan(bank, soals_urutan, total_siswa) {
        if (!soals_urutan) soals_urutan = [];
        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;
        const total_urutan = soals_urutan.length;
        const total_urutan_tampil = countDisplayed(soals_urutan);
        const disable_delete = bank.digunakan > 0 || total_siswa > 0;
        const dis = disable_delete ? 'disabled' : '';

        $('#urutan_soal').text(bank.tampil_urutan || 0)
        $('#urutan_bobot').text(bank.bobot_urutan || 0)
        $('#urutan_point').text((bank.bobot_urutan > 0 && bank.tampil_urutan > 0) ? (bank.bobot_urutan / Math.max(total_urutan_tampil, bank.tampil_urutan)).toFixed(2) : 0)
        $('#urutan_total').text(total_urutan)
        $('#urutan_tampil').text(total_urutan_tampil)

        const messages = [];
        const tampil_target = bank.tampil_urutan || 0;

        if (total_urutan < tampil_target) messages.push('Soal URUTAN masih kurang, klik tombol <b>(<i class="bi bi-plus-lg"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
        if (total_urutan > 0 && tampil_target === '0') messages.push('Ada soal URUTAN tapi tidak ada yang ditampilkan.');
        if (total_urutan_tampil < tampil_target) messages.push('Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.');
        if (bank.digunakan > 0) messages.push('Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.');
        if (total_siswa > 0) messages.push('Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.');

        const listContainer = document.querySelector('#urutan_info');
        listContainer.innerHTML = '';
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.innerHTML = msg;
            listContainer.appendChild(li);
        });

        if (soals_urutan.length > 0) {
            $('#ada_soal_urutan').removeClass('d-none');
            $('#emp_soal_urutan').addClass('d-none');
            const tbody = document.querySelector('#tbody-urutan');
            tbody.innerHTML = '';
            soals_urutan.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';
                let items = [];
                try {
                    const parsed = JSON.parse(s.opsi_a);
                    if (parsed && parsed.items) items = parsed.items;
                } catch (e) { }

                let itemList = '';
                items.forEach((item, idx) => {
                    itemList += `<li>${fixMediaUrl(item)}</li>`;
                });

                const row = document.createElement('tr');
                row.innerHTML = `
                        <td class="text-center align-top" style="vertical-align: top !important; width: 40px; padding-top: 12px;">
                            <input class="check-urutan form-check-input cursor-pointer" id="${s.id_soal}" type="checkbox" name="soal[]" value="${s.id_soal}" ${checked} ${dis}>
                        </td>
                        <td class="text-center align-top fw-bold" style="vertical-align: top !important; width: 35px; padding-top: 12px; white-space: nowrap;">${s.nomor_soal}.</td>
                        <td class="question align-top" data-id="${s.id_soal}" style="vertical-align: top !important; padding-top: 12px;">
                            ${soalAiBadge(s)}${fixMediaUrl(s.soal)}
                            <br/>
                            <b>Item Urutan (Benar):</b>
                            <ol class="ps-3">
                                ${itemList}
                            </ol>
                        </td>
                        <td class="qs-table-action-col align-top" style="vertical-align: top !important; width: 60px; text-align: right; padding-top: 12px;">
                            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=6" type="button" class="btn btn-sm btn-warning mb-2" title="Edit Soal"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-sm btn-danger" type="button" data-idsoal="${s.id_soal}" data-nomor="${s.nomor_soal}" data-jenis="6" title="Hapus Soal" onclick="hapusSoal(this)" ${dis}><i class="bi bi-trash"></i></button>
                        </td>
                    `;
                tbody.appendChild(row);
            });
        } else {
            $('#ada_soal_urutan').addClass('d-none');
            $('#emp_soal_urutan').removeClass('d-none');
            const tbody = document.querySelector('#tbody-urutan');
            if (tbody) tbody.innerHTML = '';
        }
    }

    function populateSoalCloze(bank, soals_cloze, total_siswa) {
        if (!soals_cloze) soals_cloze = [];
        const countDisplayed = (arr) => arr.filter(s => s.tampilkan === '1').length;
        const total_cloze = soals_cloze.length;
        const total_cloze_tampil = countDisplayed(soals_cloze);
        const disable_delete = bank.digunakan > 0 || total_siswa > 0;
        const dis = disable_delete ? 'disabled' : '';

        $('#cloze_soal').text(bank.tampil_cloze || 0)
        $('#cloze_bobot').text(bank.bobot_cloze || 0)
        $('#cloze_point').text((bank.bobot_cloze > 0 && bank.tampil_cloze > 0) ? (bank.bobot_cloze / Math.max(total_cloze_tampil, bank.tampil_cloze)).toFixed(2) : 0)
        $('#cloze_total').text(total_cloze)
        $('#cloze_tampil').text(total_cloze_tampil)

        const messages = [];
        const tampil_target = bank.tampil_cloze || 0;

        if (total_cloze < tampil_target) messages.push('Soal ISIAN RUMPANG masih kurang, klik tombol <b>(<i class="bi bi-plus-lg"></i> Tambah/Edit Soal)</b> untuk menambahkan.');
        if (total_cloze > 0 && tampil_target === '0') messages.push('Ada soal ISIAN RUMPANG tapi tidak ada yang ditampilkan.');
        if (total_cloze_tampil < tampil_target) messages.push('Jumlah soal yang ditampilkan tidak sama dengan jumlah seharusnya.');
        if (bank.digunakan > 0) messages.push('Soal sudah dijadwalkan, tidak bisa menghapus dan memilih ulang butir soal.');
        if (total_siswa > 0) messages.push('Soal sedang digunakan oleh siswa, tidak bisa menghapus dan memilih ulang butir soal.');

        const listContainer = document.querySelector('#cloze_info');
        listContainer.innerHTML = '';
        messages.forEach(msg => {
            const li = document.createElement('li');
            li.innerHTML = msg;
            listContainer.appendChild(li);
        });

        if (soals_cloze.length > 0) {
            $('#ada_soal_cloze').removeClass('d-none');
            $('#emp_soal_cloze').addClass('d-none');
            const tbody = document.querySelector('#tbody-cloze');
            tbody.innerHTML = '';
            soals_cloze.forEach(s => {
                const checked = s.tampilkan === "1" ? 'checked' : '';
                let answers = {};
                try {
                    // Jawaban is stored as JSON string in 'jawaban' column for Cloze
                    answers = JSON.parse(s.jawaban);
                } catch (e) { }

                let answerList = '';
                if (answers) {
                    Object.keys(answers).forEach((key) => {
                        answerList += `<li>${key}: ${answers[key]}</li>`;
                    });
                }

                const row = document.createElement('tr');
                row.innerHTML = `
                        <td class="text-center align-top" style="vertical-align: top !important; width: 40px; padding-top: 12px;">
                            <input class="check-cloze form-check-input cursor-pointer" id="${s.id_soal}" type="checkbox" name="soal[]" value="${s.id_soal}" ${checked} ${dis}>
                        </td>
                        <td class="text-center align-top fw-bold" style="vertical-align: top !important; width: 35px; padding-top: 12px; white-space: nowrap;">${s.nomor_soal}.</td>
                        <td class="question align-top" data-id="${s.id_soal}" style="vertical-align: top !important; padding-top: 12px;">
                            ${soalAiBadge(s)}${fixMediaUrl(s.soal)}
                            <br/>
                            <b>Kunci Jawaban:</b>
                            <ul class="ps-3">
                                ${answerList}
                            </ul>
                        </td>
                        <td class="qs-table-action-col align-top" style="vertical-align: top !important; width: 60px; text-align: right; padding-top: 12px;">
                            <a href="${base_url}cbtbanksoal/buatsoal/${bank.id_bank}?no=${s.nomor_soal}&jns=7" type="button" class="btn btn-sm btn-warning mb-2" title="Edit Soal"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-sm btn-danger" type="button" data-idsoal="${s.id_soal}" data-nomor="${s.nomor_soal}" data-jenis="7" title="Hapus Soal" onclick="hapusSoal(this)" ${dis}><i class="bi bi-trash"></i></button>
                        </td>
                    `;
                tbody.appendChild(row);
            });
        } else {
            $('#ada_soal_cloze').addClass('d-none');
            $('#emp_soal_cloze').removeClass('d-none');
            const tbody = document.querySelector('#tbody-cloze');
            if (tbody) tbody.innerHTML = '';
        }
    }

    // Real-time Validation and Sync Functions
    let lastSyncStatus = null;
    let syncCheckInterval = null;

    function checkSyncStatus() {
        const bankId = idBank;
        if (!bankId) return;

        const syncCard = document.getElementById('sync-notification-card');
        const syncContent = document.getElementById('sync-status-content');
        const btnSync = document.getElementById('btn-sync-kartu');

        // Show loading state
        if (syncContent) {
            syncContent.innerHTML = '<div class="text-center"><i class="bi bi-arrow-repeat spin"></i> Memeriksa status sinkronisasi...</div>';
        }

        $.ajax({
            url: base_url + 'cbtbanksoal/api-check-sync/' + bankId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    lastSyncStatus = response.data;
                    renderSyncStatus(response.data);
                    
                    // Show sync button if needed
                    if (btnSync) {
                        if (response.data.needs_sync) {
                            btnSync.classList.remove('d-none');
                        } else {
                            btnSync.classList.add('d-none');
                        }
                    }
                } else {
                    if (syncContent) {
                        syncContent.innerHTML = '<div class="alert alert-warning">' + (response.message || 'Gagal memeriksa status') + '</div>';
                    }
                }
            },
            error: function(xhr) {
                console.error('Sync check error:', xhr);
                if (syncContent) {
                    syncContent.innerHTML = '<div class="alert alert-danger">Error memeriksa status sinkronisasi</div>';
                }
            }
        });
    }

    function renderSyncStatus(data) {
        const syncContent = document.getElementById('sync-status-content');
        const syncCard = document.getElementById('sync-notification-card');
        
        if (!syncContent || !syncCard) return;

        // Show the card
        syncCard.classList.remove('d-none');

        let html = '';
        
        // Kartu Soal Status
        if (data.has_kartu_soal) {
            const syncBadge = data.kartu_synced 
                ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Tersinkron</span>'
                : '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Perlu Sinkronisasi</span>';
            
            html += `
                <div class="mb-3">
                    <h6 class="fw-bold">Kartu Soal (Kisi-kisi)</h6>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        ${syncBadge}
                        <span class="text-muted">${data.kartu_judul || '-'}</span>
                    </div>
                </div>
            `;

            // Show quota comparison if available
            if (data.quota_comparison) {
                html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                html += '<thead class="table-light"><tr><th>Jenis</th><th>Kartu Soal</th><th>Bank Soal</th><th>Status</th></tr></thead><tbody>';
                
                const comparisons = data.quota_comparison;
                for (const [jenis, comp] of Object.entries(comparisons)) {
                    const statusIcon = comp.match 
                        ? '<i class="bi bi-check-circle text-success"></i>' 
                        : '<i class="bi bi-exclamation-triangle text-warning"></i>';
                    html += `<tr>
                        <td>${comp.label}</td>
                        <td>${comp.kartu}</td>
                        <td>${comp.bank}</td>
                        <td class="text-center">${statusIcon}</td>
                    </tr>`;
                }
                html += '</tbody></table></div>';
            }
        } else {
            html += `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Bank soal ini belum terhubung dengan Kartu Soal (Kisi-kisi). 
                    <a href="${base_url}cbtbanksoal/edit/${idBank}" class="alert-link">Edit Bank Soal</a> untuk menambahkan koneksi.
                </div>
            `;
        }

        // Validation Issues
        if (data.validation_issues && data.validation_issues.length > 0) {
            html += '<div class="mt-3"><h6 class="fw-bold text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Validasi Perlu Perhatian:</h6><ul class="list-group list-group-flush">';
            data.validation_issues.forEach(issue => {
                html += `<li class="list-group-item list-group-item-warning py-2">${issue}</li>`;
            });
            html += '</ul></div>';
        }

        // Last sync info
        if (data.last_sync) {
            html += `<div class="mt-2 text-muted small"><i class="bi bi-clock me-1"></i>Terakhir diperbarui: ${data.last_sync}</div>`;
        }

        syncContent.innerHTML = html;
    }

    function syncWithKartu() {
        const bankId = idBank;
        if (!bankId) return;

        Swal.fire({
            title: 'Sinkronkan dengan Kartu Soal?',
            text: 'Tindakan ini akan menyelaraskan jumlah soal dan bobot dengan Kartu Soal yang dipilih.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Sinkronkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'cbtbanksoal/api-sync-kartu',
                    type: 'POST',
                    data: { id_bank: bankId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Sinkronisasi berhasil',
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', response.message || 'Sinkronisasi gagal', 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error('Sync error:', xhr);
                        Swal.fire('Error', 'Terjadi kesalahan saat sinkronisasi', 'error');
                    }
                });
            }
        });
    }

    // Real-time validation on page load
    function initRealTimeValidation() {
        // Check sync status on page load if bank has kartu_soal_id
        const hasKartuSoal = document.querySelector('a[href*="cbtkartusoal/detail/"]');
        if (hasKartuSoal) {
            checkSyncStatus();
            
            // Set up periodic sync check (every 5 minutes)
            syncCheckInterval = setInterval(checkSyncStatus, 5 * 60 * 1000);
        }
    }

    // Initialize real-time validation when document is ready
    $(document).ready(function() {
        // Wait for bank data to be loaded
        $(document).on('bankDataLoaded', function(e, bank, soals) {
            window.currentBankData = bank;
            window.currentSoalsData = soals;
            initRealTimeValidation();
        });
    });
</script>

<!-- ================================================================
     PANEL ITEM ANALYSIS — Sprint 2-3
     Ditambahkan ke bawah halaman detail bank soal.
     Menampilkan statistik empiris (p, D) per soal setelah ujian.
================================================================ -->
<script>
(function () {
    'use strict';

    // idBank sudah dideklarasikan oleh script utama di atas
    var bankId = typeof idBank !== 'undefined' ? parseInt(idBank, 10) : 0;
    if (bankId <= 0) return;

    // Tambahkan tombol "Item Analysis" ke card-tools header
    $(document).ready(function () {
        var $tools = $('.card-tools').first();
        if ($tools.length) {
            $tools.prepend(
                '<button id="btn-toggle-analysis" class="btn btn-sm btn-outline-info me-1" title="Item Analysis Soal">' +
                '<i class="bi bi-graph-up me-1"></i>' +
                '<span class="d-none d-md-inline">Item Analysis</span>' +
                '</button>'
            );
        }

        // Sisipkan panel accordion setelah card utama
        var $panel = $(
            '<div class="card shadow-sm mb-4 d-none" id="card-item-analysis">' +
            '<div class="card-header bg-transparent d-flex justify-content-between align-items-center">' +
            '<h5 class="card-title mb-0 fw-bold">' +
            '<i class="bi bi-graph-up-arrow me-2 text-info"></i>Item Analysis Soal' +
            '</h5>' +
            '<div class="d-flex gap-2">' +
            '<select id="ia-filter-tag" class="form-select form-select-sm" style="width:130px">' +
            '<option value="">Semua Tag</option>' +
            '<option value="mudah">Mudah</option>' +
            '<option value="sedang">Sedang</option>' +
            '<option value="sulit">Sulit</option>' +
            '</select>' +
            '<select id="ia-filter-kualitas" class="form-select form-select-sm" style="width:160px">' +
            '<option value="">Semua Kualitas</option>' +
            '<option value="revisi">Perlu Revisi (D&lt;0.20)</option>' +
            '<option value="baik">Baik (D≥0.40)</option>' +
            '</select>' +
            '<button class="btn btn-sm btn-info text-white" id="btn-run-ia">' +
            '<span class="spinner-border spinner-border-sm d-none me-1" id="spin-ia"></span>' +
            '<i class="bi bi-play-fill me-1"></i>Hitung Ulang' +
            '</button>' +
            '</div>' +
            '</div>' +
            '<div class="card-body p-0" id="ia-summary-bar" style="background:#f8f9fa;border-bottom:1px solid #dee2e6;padding:12px 16px!important">' +
            '<div class="row g-3 text-center">' +
            '<div class="col-6 col-md-2"><div class="fw-bold text-primary fs-5" id="ia-total">—</div><div class="small text-muted">Soal Dianalisis</div></div>' +
            '<div class="col-6 col-md-2"><div class="fw-bold fs-5" id="ia-avg-p">—</div><div class="small text-muted">Rerata p</div></div>' +
            '<div class="col-6 col-md-2"><div class="fw-bold fs-5" id="ia-avg-d">—</div><div class="small text-muted">Rerata D</div></div>' +
            '<div class="col-6 col-md-2"><div class="fw-bold text-success fs-5" id="ia-baik">—</div><div class="small text-muted">Soal Baik</div></div>' +
            '<div class="col-6 col-md-2"><div class="fw-bold text-warning fs-5" id="ia-cukup">—</div><div class="small text-muted">Cukup</div></div>' +
            '<div class="col-6 col-md-2"><div class="fw-bold text-danger fs-5" id="ia-revisi">—</div><div class="small text-muted">Perlu Revisi</div></div>' +
            '</div>' +
            '</div>' +
            '<div class="card-body p-0">' +
            '<div class="table-responsive">' +
            '<table class="table table-sm table-hover align-middle mb-0" id="tbl-ia">' +
            '<thead class="table-light"><tr>' +
            '<th class="text-center" style="width:50px">No</th>' +
            '<th class="text-center" style="width:70px">Jenis</th>' +
            '<th>Cuplikan Soal</th>' +
            '<th class="text-center" style="width:80px">p</th>' +
            '<th class="text-center" style="width:80px">D</th>' +
            '<th class="text-center" style="width:90px">Tag Empiris</th>' +
            '<th class="text-center" style="width:85px">Level Kisi</th>' +
            '<th class="text-center" style="width:60px">Pakai</th>' +
            '<th class="text-center" style="width:95px">Kualitas</th>' +
            '</tr></thead>' +
            '<tbody id="tbody-ia"><tr><td colspan="9" class="text-center py-3 text-muted">Klik "Item Analysis" untuk memuat data.</td></tr></tbody>' +
            '</table>' +
            '</div>' +
            '</div>' +
            '<div class="card-footer bg-transparent small text-muted">' +
            '<strong>p</strong> = indeks kesulitan (0.30–0.70 ideal) &nbsp;|&nbsp; ' +
            '<strong>D</strong> = daya beda (&lt;0.20 perlu revisi, ≥0.40 baik) &nbsp;|&nbsp; ' +
            '<a href="<?= base_url('cbt-quality/item-analysis-view') ?>/' + bankId + '" class="text-info" target="_blank">' +
            '<i class="bi bi-box-arrow-up-right me-1"></i>Buka halaman penuh' +
            '</a>' +
            '</div>' +
            '</div>'
        );
        $panel.insertAfter('.card.shadow-sm.mb-4').first();
    });

    var allIaRows = [];
    var iaLoaded  = false;

    // Toggle panel
    $(document).on('click', '#btn-toggle-analysis', function () {
        var $card = $('#card-item-analysis');
        if ($card.hasClass('d-none')) {
            $card.removeClass('d-none');
            if (!iaLoaded) loadIaData();
        } else {
            $card.addClass('d-none');
        }
    });

    // Hitung ulang
    $(document).on('click', '#btn-run-ia', function () {
        Swal.fire({
            icon: 'question', title: 'Hitung Ulang Item Analysis?',
            text: 'Sistem akan menghitung ulang statistik soal dari jadwal ujian terakhir bank soal ini.',
            showCancelButton: true, confirmButtonText: 'Ya, Hitung',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            runAnalysis();
        });
    });

    // Filter
    $(document).on('change', '#ia-filter-tag, #ia-filter-kualitas', function () {
        renderIaTable(allIaRows);
    });

    function loadIaData() {
        $('#spin-ia').removeClass('d-none');
        $('#btn-run-ia').prop('disabled', true);

        $.getJSON('<?= base_url('cbt-quality/item-analysis') ?>/' + bankId, function (res) {
            if (!res.status) {
                $('#tbody-ia').html('<tr><td colspan="9" class="text-center text-danger py-2">' + (res.message || 'Gagal') + '</td></tr>');
                return;
            }
            var r = res.data.ringkasan || {};
            var rows = res.data.soals || [];

            $('#ia-total').text(r.sudah_dianalisis ?? 0);
            $('#ia-avg-p').text(r.avg_p ? parseFloat(r.avg_p).toFixed(3) : '—');
            $('#ia-avg-d').text(r.avg_d ? parseFloat(r.avg_d).toFixed(3) : '—');

            // Hitung distribusi kualitas
            var baik = 0, cukup = 0, revisi = 0;
            rows.forEach(function (s) {
                if (s.stat_d === null) return;
                var d = parseFloat(s.stat_d);
                if (d >= 0.40) baik++;
                else if (d >= 0.20) cukup++;
                else revisi++;
            });
            $('#ia-baik').text(baik);
            $('#ia-cukup').text(cukup);
            $('#ia-revisi').text(revisi);

            allIaRows = rows;
            iaLoaded  = true;

            if (rows.length === 0) {
                $('#tbody-ia').html(
                    '<tr><td colspan="9" class="text-center py-3 text-info">' +
                    '<i class="bi bi-info-circle me-1"></i>Belum ada data analisis. ' +
                    'Klik "Hitung Ulang" untuk menjalankan item analysis.' +
                    '</td></tr>'
                );
            } else {
                renderIaTable(rows);
            }
        }).fail(function () {
            $('#tbody-ia').html('<tr><td colspan="9" class="text-center text-danger py-2">Gagal memuat data analisis.</td></tr>');
        }).always(function () {
            $('#spin-ia').addClass('d-none');
            $('#btn-run-ia').prop('disabled', false);
        });
    }

    function renderIaTable(rows) {
        var tagF  = $('#ia-filter-tag').val();
        var qualF = $('#ia-filter-kualitas').val();

        var filtered = rows.filter(function (s) {
            if (tagF && s.tag_kesulitan !== tagF) return false;
            if (qualF === 'revisi' && (s.stat_d === null || parseFloat(s.stat_d) >= 0.20)) return false;
            if (qualF === 'baik'   && (s.stat_d === null || parseFloat(s.stat_d) < 0.40))  return false;
            return true;
        });

        var jenisMap = {1:'PG',2:'Kompleks',3:'Jodohkan',4:'Isian',5:'Esai',6:'Urutan',7:'Cloze'};
        var html = '';

        filtered.forEach(function (s) {
            var p = s.stat_p !== null ? parseFloat(s.stat_p) : null;
            var d = s.stat_d !== null ? parseFloat(s.stat_d) : null;
            var pStr = p !== null ? p.toFixed(3) : '—';
            var dStr = d !== null ? d.toFixed(3) : '—';
            var pCls = p === null ? '' : (p >= 0.70 ? 'text-success fw-bold' : p < 0.30 ? 'text-danger fw-bold' : 'text-warning fw-bold');
            var dCls = d === null ? '' : (d < 0 ? 'text-danger fw-bold' : d < 0.20 ? 'text-warning fw-bold' : d >= 0.40 ? 'text-success fw-bold' : '');

            var tagBadge = !s.tag_kesulitan ? '<span class="text-muted small">—</span>'
                : s.tag_kesulitan === 'mudah'  ? '<span class="badge bg-success">Mudah</span>'
                : s.tag_kesulitan === 'sulit'  ? '<span class="badge bg-danger">Sulit</span>'
                :                                '<span class="badge bg-warning text-dark">Sedang</span>';

            var kualitas = d === null ? '<span class="text-muted small">Belum</span>'
                : d < 0     ? '<span class="badge bg-danger">Buruk</span>'
                : d < 0.20  ? '<span class="badge bg-warning text-dark">Perlu Revisi</span>'
                : d < 0.40  ? '<span class="badge bg-info text-dark">Cukup</span>'
                :             '<span class="badge bg-success">Baik</span>';

            var cuplikan = (s.cuplikan_soal || '').replace(/<[^>]*>/g, '');

            html += '<tr>'
                + '<td class="text-center text-muted small">' + (s.nomor_soal || '—') + '</td>'
                + '<td class="text-center"><span class="badge bg-secondary">' + (jenisMap[s.jenis] || s.jenis) + '</span></td>'
                + '<td class="small text-muted" style="max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="' + cuplikan + '">' + cuplikan + '</td>'
                + '<td class="text-center ' + pCls + '">' + pStr + '</td>'
                + '<td class="text-center ' + dCls + '">' + dStr + '</td>'
                + '<td class="text-center">' + tagBadge + '</td>'
                + '<td class="text-center"><code class="text-muted small">' + (s.level_kisi || '—') + '</code></td>'
                + '<td class="text-center text-muted small">' + (s.stat_used_count || 0) + 'x</td>'
                + '<td class="text-center">' + kualitas + '</td>'
                + '</tr>';
        });

        $('#tbody-ia').html(html ||
            '<tr><td colspan="9" class="text-center text-success py-3"><i class="bi bi-check-circle me-1"></i>Tidak ada soal yang cocok filter ini.</td></tr>'
        );
    }

    function runAnalysis() {
        $('#spin-ia').removeClass('d-none');
        $('#btn-run-ia').prop('disabled', true);

        $.post('<?= base_url('cbt-quality/run-analysis') ?>/' + bankId)
          .done(function (res) {
              Swal.fire({
                  icon: res.status ? 'success' : 'warning',
                  title: res.status ? 'Selesai' : 'Peringatan',
                  text: res.message,
              }).then(function () {
                  if (res.status) {
                      iaLoaded = false;
                      loadIaData();
                  }
              });
          })
          .fail(function () { Swal.fire({ icon: 'error', text: 'Terjadi kesalahan jaringan.' }); })
          .always(function () {
              $('#spin-ia').addClass('d-none');
              $('#btn-run-ia').prop('disabled', false);
          });
    }

}());
</script><?= $this->endSection() ?>