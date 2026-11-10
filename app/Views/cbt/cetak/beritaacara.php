<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <section class="content">
            <div class="container-fluid">


                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">Berita Acara</h3>
                        <div class="d-flex align-items-center ms-auto">
                            <div id="selector" class="btn-group me-3" role="group">
                                <button type="button" class="btn active btn-outline-primary">By Ruang</button>
                                <button type="button" class="btn btn-outline-primary">By Kelas</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 col-sm-6 d-none" id="by-kelas">
                                <label class="form-label">Kelas</label>
                                <?php
                                echo form_dropdown(
                                    'kelas',
                                    $kelas,
                                    null,
                                    'id="kelas" class="form-select"'
                                ); ?>
                            </div>
                            <div class="col-md-3 col-sm-6" id="by-ruang">
                                <label class="form-label">Ruang</label>
                                <?php
                                echo form_dropdown(
                                    'ruang',
                                    $ruang,
                                    null,
                                    'id="ruang" class="form-select"'
                                ); ?>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Sesi</label>
                                <?php
                                echo form_dropdown(
                                    'sesi',
                                    $sesi,
                                    null,
                                    'id="sesi" class="form-select"'
                                ); ?>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Jadwal</label>
                                <?php
                                echo form_dropdown('mapel', $mapel_jadwal, null, 'id="mapel-jadwal" class="form-select"');
                                ?>
                            </div>
                            <div class="col-md-3 col-sm-6 d-none" id="select-tgl">
                                <label class="form-label">Tanggal</label>
                                <select name="tanggal" id="mapel-tgl" class="form-select select2"></select>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="flex-grow-1 me-3">
                                <div class="alert alert-danger d-none mb-0" id="empty-jadwal">
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="<?= base_url('cbtcetak') ?>" class="btn btn-danger me-2">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                                </a>
                                <button class="btn btn-success text-white" id="btn-print">
                                    <i class="bi bi-printer me-1"></i> Cetak
                                </button>
                            </div>
                        </div>
                        <div class="p-4 bg-light border rounded">
                            <div class="d-flex justify-content-center align-items-center">
                                <div id="print-preview" style="width: 21cm; min-height: 29cm;"
                                    class="bg-white shadow-sm p-5">
                                    <?php if (isset($setting->kopsekolah) && $setting->kopsekolah != ''): ?>
                                        <img src="<?= base_url() . $setting->kopsekolah ?>"
                                            style="width: 100%; margin-bottom: 2px;">
                                    <?php else: ?>
                                        <table id="table-header-print" style="width: 100%; border: 0;">
                                            <tr>
                                                <td style="width:15%;">
                                                    <img alt="logo kiri" id="prev-logo-kanan-print"
                                                        src="<?= isset($setting->logo_kiri) ? base_url() . $setting->logo_kiri : '' ?>"
                                                        style="width:85px; height:85px; margin: 6px;">
                                                </td>
                                                <td style="width:70%; text-align: center;">
                                                    <div
                                                        style="line-height: 1.1; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif; font-size: 14pt">
                                                        <?= isset($setting->header_1) ? $setting->header_1 : strtoupper($setting->sekolah) ?>
                                                    </div>
                                                    <div
                                                        style="line-height: 1.1; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif; font-size: 16pt">
                                                        <b><?= isset($setting->header_2) ? $setting->header_2 : strtoupper($setting->kota) ?></b>
                                                    </div>
                                                    <div
                                                        style="line-height: 1.2; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif; font-size: 13pt">
                                                        <?= isset($setting->header_3) ? $setting->header_3 : $setting->alamat . ' ' . $setting->kecamatan ?>
                                                    </div>
                                                    <div
                                                        style="line-height: 1.2; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif; font-size: 12pt">
                                                        <?= isset($setting->header_4) ? $setting->header_4 : 'Telp: ' . $setting->telp ?>
                                                    </div>
                                                </td>
                                                <td style="width:15%;">
                                                    <img alt="logo kanan" id="prev-logo-kiri-print"
                                                        src="<?= isset($setting->logo_kanan) ? base_url() . $setting->logo_kanan : '' ?>"
                                                        style="width:85px; height:85px; margin: 6px; border-style: none">
                                                </td>
                                            </tr>
                                        </table>
                                        <hr style="border: 1px solid; margin-bottom: 6px">
                                    <?php endif; ?>


                                    <br>
                                    <h3
                                        style="text-align: center; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;">
                                        BERITA ACARA </h3>
                                    <div
                                        style="text-align: justify; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;">
                                        Pada hari ini <span class="editable bg-gray-light" id="edit-hari"
                                            style="display: inline-block;min-width: 20px"><?= singkat_tanggal(date('D')) ?></span>
                                        tanggal <span class="editable bg-gray-light" id="edit-tanggal"
                                            style="display: inline-block;min-width: 20px"><?= date('d') ?></span>
                                        bulan <span class="editable bg-gray-light" id="edit-bulan"
                                            style="display: inline-block;min-width: 20px"><?= singkat_tanggal(date('M')) ?></span>
                                        tahun <span class="editable bg-gray-light" id="edit-tahun"
                                            style="display: inline-block;min-width: 20px"><?= date('Y') ?></span>
                                        telah diselenggarakan <span class="editable bg-gray-light" id="edit-jenis-ujian"
                                            style="display: inline-block;min-width: 20px">............................................</span>
                                        untuk Mata Pelajaran <span class="editable bg-gray-light" id="edit-mapel"
                                            style="display: inline-block;min-width: 20px">.....................................</span>
                                        dari pukul <span class="editable bg-gray-light" id="edit-waktu-mulai"
                                            style="display: inline-block;min-width: 20px">.............</span>
                                        sampai dengan pukul <span class="editable bg-gray-light" id="edit-waktu-akhir"
                                            style="display: inline-block;min-width: 20px">...........</span>
                                    </div>
                                    <br>
                                    <table
                                        style="width: 100%;font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;">
                                        <tr>
                                            <td style="width: 30px;">1.</td>
                                            <td style="width: 30%;">
                                                Pada Sekolah
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-nama_sekolah">
                                                <?= isset($kop->sekolah) ? $kop->sekolah : '' ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td id="title-ruang">
                                                Ruang
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-ruang">
                                                .................................................................
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>Sesi</td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-sesi">
                                                .................................................................
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                Jumlah Peserta Seharusnya
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-jml-peserta">
                                                .................................................................
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                Jumlah Peserta Hadir
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-hadir">
                                                .................................................................
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                Jumlah Peserta Tidak Hadir
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-tidak-hadir">
                                                .................................................................
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                Nomor Peserta Tidak Hadir
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-username">
                                                .................................................................
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 12px">2.</td>
                                            <td style="padding-top: 12px" colspan="3">
                                                Catatan selama <span class="editable bg-gray-light" id="edit-nama-ujian"
                                                    style="display: inline-block;min-width: 20px">.......</span>
                                                berlangsung :
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td colspan="3"
                                                style="height: 100px; border: 1px solid black; padding: 12px"
                                                class="editable bg-gray-light" id="edit-catatan">
                                                Pelaksanaan ujian berjalan dengan aman, lancar dan tertib.<br>
                                                Terdapat siswa an. .... yang tidak hadir karena ....<br>
                                                Catatan lain yang perlu .....</td>
                                        </tr>
                                    </table>
                                    <br>
                                    <div id="berita-ttd">
                                        <table
                                            style="width:90%; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;">
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th style="text-align: center">TTD</th>
                                                <th></th>
                                            </tr>
                                            <tr>
                                                <td style="width: 30px;">1.</td>
                                                <td>Pengawas</td>
                                                <td>:</td>
                                                <td style="padding-top: 12px" class="editable bg-gray-light"
                                                    id="edit-pengawas1">
                                                    <?= isset($profile->nama_lengkap) && $profile->nama_lengkap != '' ? $profile->nama_lengkap : '_________________________' ?>
                                                </td>
                                                <td style="padding-left: 20px" rowspan="2">1. <div alt="ttd pengawas"
                                                        style="font-family: 'Verdana'; font-size: 16pt; line-height: 2; background: url('<?= isset($profile->ttd) && $profile->ttd != '' ? base_url() . $profile->ttd : base_url() . '/assets/img/signature.png' ?>') no-repeat center !important; background-size:  100px 60px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                                        _______________
                                                    </div>
                                                </td>
                                                <td rowspan="2"></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    NIP.
                                                </td>
                                                <td>:</td>
                                                <td class="editable bg-gray-light">
                                                    <?= isset($profile->nip) && $profile->nip != '' ? $profile->nip : '_________________________' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 12px">2.</td>
                                                <td style="padding-top: 12px">Proktor</td>
                                                <td style="padding-top: 12px">:</td>
                                                <td style="padding-top: 12px" class="editable bg-gray-light"
                                                    id="edit-proktor">
                                                    <?= isset($kop->proktor) && $kop->proktor != '' ? $kop->proktor : '_________________________' ?>
                                                </td>
                                                <td style="padding-left: 20px" rowspan="2">2. <div alt="ttd proktor"
                                                        style="font-family: 'Verdana'; font-size: 16pt; line-height: 2; background: url('<?= isset($kop->ttdproktor) && $kop->ttdproktor != '' ? base_url() . $kop->ttdproktor : base_url() . '/assets/img/signature.png' ?>') no-repeat center !important; background-size:  100px 60px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                                        _______________
                                                    </div>
                                                </td>
                                                <td rowspan="2"></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    NIP.
                                                </td>
                                                <td>:</td>
                                                <td class="editable bg-gray-light">
                                                    <?= isset($kop->nip_proktor) && $kop->nip_proktor != '' ? $kop->nip_proktor : '_________________________' ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top: 12px">3.</td>
                                                <td style="padding-top: 12px">
                                                    Kepala Sekolah
                                                </td>
                                                <td style="padding-top: 12px">:</td>
                                                <td style="padding-top: 12px" class="editable bg-gray-light">
                                                    <?= isset($kop->kepsek) && $kop->kepsek != '' ? $kop->kepsek : '_________________________' ?>
                                                </td>
                                                <td style="padding-left: 20px" rowspan="2">3. <div alt="ttd kepsek"
                                                        style="font-family: 'Verdana'; font-size: 16pt; line-height: 2; background: url('<?= isset($kop->tanda_tangan) && $kop->tanda_tangan != '' ? base_url() . $kop->tanda_tangan : base_url() . '/assets/img/signature.png' ?>') no-repeat center !important; background-size: 100px 60px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                                                        _______________
                                                    </div>
                                                </td>
                                                <td rowspan="2" style="vertical-align: bottom">

                                                </td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td>
                                                    NIP.
                                                </td>
                                                <td>:</td>
                                                <td class="editable bg-gray-light">
                                                    <?= isset($kop->nip) && $kop->nip != '' ? $kop->nip : '_________________________' ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <br>
                                    <br>
                                    <!--<small>*) Tanda tangan dalam dokumen ini dibuat secara digital</small>-->
                                    <div id="qrcode-single-view"
                                        style="display: flex; justify-content: center; align-items: center;"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="overlay d-none" id="loading">
                        <!-- <div class="spinner-grow"></div> -->
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                            Loading...
                        </button>
                    </div>
                </div>

                <div id="all-print-preview" class="m-4 d-none"></div>
            </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/qrcode.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>

<script>
    var allJadwal = JSON.parse(JSON.stringify(<?= json_encode($jadwal) ?>));

    const kepsek = <?= json_encode(isset($kop->kepsek) && $kop->kepsek != "" ? $kop->kepsek : "_________________________") ?>;
    const nip = <?= json_encode(isset($kop->nip) && $kop->nip != "" ? $kop->nip : "_________________________") ?>;
    var oldVal1 = <?= json_encode($kop->header_1 ?? "") ?>;
    var oldVal2 = <?= json_encode($kop->header_2 ?? "") ?>;
    var oldVal3 = <?= json_encode($kop->header_3 ?? "") ?>;
    var oldVal4 = <?= json_encode($kop->header_4 ?? "") ?>;
    var printBy = 1;
    let arrSiswa = [];
    const activeGuru = <?= json_encode(isset($guru) ? $guru : (isset($panitia) ? $panitia : null)) ?>;

    function handleNull(value) {
        if (value == null || value == "0" || value == "") return "-";
        else return value;
    }

    function handleTanggal(tgl) {
        var hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jum'at", "Sabtu"];
        var bulans = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        if (handleNull(tgl) !== "-") {
            var d = new Date(tgl);
            var curr_day = d.getDay();
            var curr_date = d.getDate();

            var curr_month = d.getMonth();
            var curr_year = d.getFullYear();

            return {
                HARI: hari[curr_day],
                TANGGAL: curr_date,
                BULAN: bulans[curr_month],
                TAHUN: curr_year,
            }
        }
        return null
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    function submitKop() {
        $('#set-kop').submit();
    }

    function populateBeritaAcara(data) {
        console.log("response", data);
        $('#all-print-preview').html('')

        const kop = data.kop.kop_berita
        let allPages = 0;

        const alokasiSesi = alokasiJam(data, true)

        for (const key in alokasiSesi) {
            for (const group of alokasiSesi[key]) {
                // Membuat elemen utama div
                const printPreview = document.createElement("div");
                printPreview.className = 'mb-3 bg-white page-print';
                printPreview.style.width = "21cm";
                printPreview.style.minHeight = "29cm";
                printPreview.style.pageBreakAfter = 'always'

                // Membuat tabel header
                const tableHeader = document.createElement("table");
                tableHeader.style.width = "100%";
                tableHeader.style.border = "0";

                // Baris tabel header
                const headerRow = document.createElement("tr");

                // Kolom logo kiri
                const logoKiriTd = document.createElement("td");
                logoKiriTd.style.width = "15%";
                const logoKiriImg = document.createElement("img");
                logoKiriImg.alt = "logo kiri";
                logoKiriImg.src = base_url + kop?.logo_kiri;
                logoKiriImg.style.width = "85px";
                logoKiriImg.style.height = "85px";
                logoKiriImg.style.margin = "6px";
                logoKiriTd.appendChild(logoKiriImg);

                // Kolom teks tengah
                const centerTd = document.createElement("td");
                centerTd.style.width = "70%";
                centerTd.style.textAlign = "center";

                const header1Div = document.createElement("div");
                header1Div.style.lineHeight = "1.1";
                header1Div.style.fontFamily = "'Segoe UI'";
                header1Div.style.fontSize = "14pt";
                header1Div.textContent = kop?.header_1 || 'Header 1';
                centerTd.appendChild(header1Div);

                const header2Div = document.createElement("div");
                header2Div.style.lineHeight = "1.1";
                header2Div.style.fontFamily = "'Segoe UI'";
                header2Div.style.fontSize = "16pt";
                header2Div.style.fontWeight = "bold";
                header2Div.textContent = kop?.header_2 || 'Header 2';
                centerTd.appendChild(header2Div);

                const header3Div = document.createElement("div");
                header3Div.style.lineHeight = "1.2";
                header3Div.style.fontFamily = "'Segoe UI'";
                header3Div.style.fontSize = "13pt";
                header3Div.textContent = kop?.header_3 || 'Header 3';
                centerTd.appendChild(header3Div);

                const header4Div = document.createElement("div");
                header4Div.style.lineHeight = "1.2";
                header4Div.style.fontFamily = "'Segoe UI'";
                header4Div.style.fontSize = "12pt";
                header4Div.textContent = kop?.header_4 || 'Header 4';
                centerTd.appendChild(header4Div);

                // Kolom logo kanan
                const logoKananTd = document.createElement("td");
                logoKananTd.style.width = "15%";
                const logoKananImg = document.createElement("img");
                logoKananImg.alt = "logo kanan";
                logoKananImg.src = base_url + kop?.logo_kanan;
                logoKananImg.style.width = "85px";
                logoKananImg.style.height = "85px";
                logoKananImg.style.margin = "6px";
                logoKananImg.style.borderStyle = "none";
                logoKananTd.appendChild(logoKananImg);

                // Menambahkan kolom ke baris header
                headerRow.appendChild(logoKiriTd);
                headerRow.appendChild(centerTd);
                headerRow.appendChild(logoKananTd);

                // Menambahkan baris ke tabel header
                tableHeader.appendChild(headerRow);

                // Menambahkan tabel header ke elemen utama
                printPreview.appendChild(tableHeader);

                // Menambahkan garis horizontal
                const hr = document.createElement("hr");
                hr.style.border = "1px solid";
                hr.style.marginBottom = "50px"
                printPreview.appendChild(hr);

                // Membuat elemen teks paragraf
                const tgl = handleTanggal(group.jadwal.tgl_mulai)
                const paragraph = document.createElement("div");
                paragraph.style.marginBottom = "12px";
                paragraph.style.textAlign = "justify";
                paragraph.style.fontFamily = "'Segoe UI'";
                paragraph.innerHTML = `Pada hari ini <b>${tgl?.HARI}</b> tanggal <b>${tgl?.TANGGAL}</b> bulan <b>${tgl?.BULAN}</b> tahun <b>${tgl?.TAHUN}</b>
                        telah diselenggarakan <b>${group.jadwal.nama_jenis}</b> untuk Mata Pelajaran <b>${group.jadwal.nama_mapel}</b>
                        dari pukul <b>${group.dari}</b> sampai dengan pukul <b>${group.sampai}</b>`;
                printPreview.appendChild(paragraph);

                // Membuat tabel data
                const dataTable = document.createElement("table");
                dataTable.style.width = "100%";
                dataTable.style.fontFamily = "'Segoe UI'";

                const all_siswa_ruang = data.siswa.filter(e => group.ids_siswa.includes(e.id_siswa));

                // Baris data
                const rowsData = [
                    { number: "1.", label: "Pada Sekolah", content: kop?.sekolah || 'Nama Sekolah' },
                    { number: "", label: "Ruang", content: group.nama_ruang },
                    { number: "", label: "Sesi", content: group.nama_sesi },
                    { number: "", label: "Jumlah Peserta Seharusnya", content: all_siswa_ruang.length },
                    { number: "", label: "Jumlah Peserta Hadir", content: "................................................................." },
                    { number: "", label: "Jumlah Peserta Tidak Hadir", content: "................................................................." },
                    { number: "", label: "Nomor Peserta Tidak Hadir", content: "................................................................." }
                ];

                // Menambahkan baris ke tabel data
                rowsData.forEach(row => {
                    const tr = document.createElement("tr");

                    const tdNumber = document.createElement("td");
                    tdNumber.style.width = "30px";
                    tdNumber.textContent = row.number;

                    const tdLabel = document.createElement("td");
                    tdLabel.style.width = "30%";
                    tdLabel.textContent = row.label;

                    const tdColon = document.createElement("td");
                    tdColon.textContent = ":";

                    const tdContent = document.createElement("td");
                    tdContent.textContent = row.content;

                    tr.appendChild(tdNumber);
                    tr.appendChild(tdLabel);
                    tr.appendChild(tdColon);
                    tr.appendChild(tdContent);
                    dataTable.appendChild(tr);
                });

                // Menambahkan catatan
                const noteRow = document.createElement("tr");
                const noteTd1 = document.createElement("td");
                noteTd1.style.paddingTop = "12px";
                noteTd1.textContent = "2.";

                const noteTd2 = document.createElement("td");
                noteTd2.colSpan = "3";
                noteTd2.style.paddingTop = "12px";
                noteTd2.innerHTML = `Catatan selama <b>${group.jadwal.nama_jenis}</b> berlangsung :`;

                const noteTd3 = document.createElement("td");
                noteTd3.colSpan = "3";
                noteTd3.style.height = "100px";
                noteTd3.style.border = "1px solid black";
                noteTd3.style.padding = "12px";
                noteTd3.innerHTML = 'Pelaksanaan ujian berjalan dengan aman, lancar dan tertib.<br>Terdapat siswa an. .... yang tidak hadir karena ....<br>Catatan lain yang perlu .....';

                noteRow.appendChild(noteTd1);
                noteRow.appendChild(noteTd2);
                dataTable.appendChild(noteRow);

                const noteRow2 = document.createElement("tr");
                noteRow2.appendChild(document.createElement("td")); // Kosong
                noteRow2.appendChild(noteTd3);
                dataTable.appendChild(noteRow2);

                // Menambahkan tabel ke elemen utama
                printPreview.appendChild(dataTable);

                // Tabel Tanda Tangan
                const signatureTable = document.createElement("table");
                signatureTable.style.width = "90%";
                signatureTable.style.fontFamily = "'Segoe UI'";
                signatureTable.style.marginTop = '100px'

                // Data tanda tangan
                let pengawas1, nip1, ttd1;

                if (activeGuru) {
                    pengawas1 = activeGuru.nama_guru;
                    nip1 = activeGuru.nip;
                    ttd1 = activeGuru.ttd ? base_url + activeGuru.ttd : null;
                } else {
                    pengawas1 = group.pengawas[0]?.nama_guru || 'Pengawas';
                    nip1 = group.pengawas[0]?.nip || '_________________________';
                    ttd1 = group.pengawas[0]?.ttd ? base_url + group.pengawas[0].ttd : null;
                }

                let signatureData = [
                    { number: "1.", label: "Pengawas 1", content: pengawas1, signatureId: "1.", img: ttd1 },
                    { number: "", label: "NIP.", content: nip1 },
                ];
                const proktor = kop?.proktor || 'Proktor';
                const nipProktor = kop?.nip_proktor || '_________________________';
                const ttdProktor = kop?.ttdproktor ? base_url + kop.ttdproktor : null;

                signatureData.push({ number: "2.", label: "Proktor", content: proktor, signatureId: "2.", img: ttdProktor })
                signatureData.push({ number: "", label: "NIP.", content: nipProktor })

                const numKepsek = "3.";
                const ttdKepsek = kop?.tanda_tangan ? base_url + kop.tanda_tangan : null;

                signatureData.push({ number: numKepsek, label: "Kepala Sekolah", content: kop?.kepsek, signatureId: numKepsek, img: ttdKepsek })
                signatureData.push({ number: "", label: "NIP.", content: kop?.nip || "_________________________" })

                const headSignature = document.createElement("tr");
                for (let i = 0; i < 5; i++) {
                    const thHead = document.createElement("th");
                    if (i === 4) {
                        thHead.textContent = "TTD"
                        thHead.style.textAlign = "center"
                    } else {
                        thHead.textContent = ""
                    }
                    headSignature.appendChild(thHead)
                }
                signatureTable.appendChild(headSignature);

                signatureData.forEach((row, index) => {
                    const tr = document.createElement("tr");

                    const tdNumber = document.createElement("td");
                    tdNumber.style.paddingTop = index % 2 === 0 ? "12px" : "0";
                    tdNumber.textContent = row.number;

                    const tdLabel = document.createElement("td");
                    tdLabel.style.paddingTop = index % 2 === 0 ? "12px" : "0";
                    tdLabel.textContent = row.label;

                    const tdColon = document.createElement("td");
                    tdColon.style.paddingTop = index % 2 === 0 ? "12px" : "0";
                    tdColon.textContent = ":";

                    const tdContent = document.createElement("td");
                    tdContent.style.paddingTop = index % 2 === 0 ? "12px" : "0";
                    tdContent.textContent = row.content;

                    tr.appendChild(tdNumber);
                    tr.appendChild(tdLabel);
                    tr.appendChild(tdColon);
                    tr.appendChild(tdContent);
                    if (row.signatureId) {
                        const signId = document.createElement("td");
                        signId.rowSpan = 2
                        signId.style.paddingLeft = '20px'
                        if (row.img) {
                            signId.innerHTML = `${row.signatureId} <div style="font-family: 'Verdana'; font-size: 16pt; line-height: 2; background: url('${row.img}') no-repeat center !important; background-size: 100px 60px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;">_________________________</div>`;
                        } else {
                            signId.textContent = row.signatureId + ' _________________________';
                        }
                        tr.appendChild(signId);
                    }

                    signatureTable.appendChild(tr);
                });
                // Menambahkan tabel tanda tangan ke elemen utama
                printPreview.appendChild(signatureTable);

                const qrDivContainer = document.createElement("div");
                qrDivContainer.style.display = "flex";
                qrDivContainer.style.justifyContent = "center";
                qrDivContainer.style.alignItems = "center";
                qrDivContainer.style.marginTop = "20px";

                const qrDiv = document.createElement("div");
                const tglGroup = handleTanggal(group.jadwal.tgl_mulai);
                var text = "Mata Pelajaran: " + group.jadwal.nama_mapel + "\\nTanggal: " + tglGroup.HARI + ", " + tglGroup.TANGGAL + " " + tglGroup.BULAN + " " + tglGroup.TAHUN;
                new QRCode(qrDiv, {
                    text: text,
                    width: 100,
                    height: 100,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });

                qrDivContainer.appendChild(qrDiv);
                printPreview.appendChild(qrDivContainer);

                // Menambahkan elemen utama ke body
                $('#all-print-preview').append(printPreview)
                allPages++;

            }
        }


        $('#loading-page').addClass('d-none');
        $('#info-page').removeClass('d-none');
        $('#info-page').text(`${allPages} Halaman`)

        document.title = 'Berita Acara ' + $('#mapel').find('option:selected').text() + '.pdf';
    }

    $(document).ready(function () {
        ajaxcsrf();

        var opsiMapel = $("#mapel");
        function loadAllSiswaRuang(mapel) {
            if (mapel) {
                $('#loading-page').removeClass('d-none');
                $('#info-page').addClass('d-none');
                setTimeout(function () {
                    $.ajax({
                        type: "GET",
                        url: base_url + "cbtcetak/getalljadwal?mapel=" + mapel,
                        success: function (response) {
                            populateBeritaAcara(response)
                        },
                        error: function (xhr, status, error) {
                            $('#loading-page').addClass('d-none');
                            $('#info-page').removeClass('d-none');
                            $('#info-page').text(`Error`)
                            console.log("error", xhr.responseText);
                        }
                    });
                }, 500);
            }
        }

        opsiMapel.prepend("<option value='' selected='selected'>Pilih Mapel</option>");
        opsiMapel.change(function () {
            loadAllSiswaRuang($(this).val())
        });


        var opsiJadwal = $("#mapel-jadwal");
        var opsiRuang = $("#ruang");
        var opsiSesi = $("#sesi");
        var opsiKelas = $("#kelas");
        const opsiTgl = $('#select-tgl');
        const mplTgl = $('#mapel-tgl');
        const empty = $('#empty-jadwal');

        $('.editable').attr('contentEditable', true);

        let currJadwal = [];
        let info;
        function loadSiswaRuang(ruang, sesi, jadwal) {
            currJadwal = [];
            opsiTgl.addClass('d-none')
            empty.addClass('d-none');
            mplTgl.html('');
            var notempty = ruang && sesi && jadwal;
            if (notempty) {
                $('#loading').removeClass('d-none');
                $.ajax({
                    type: "GET",
                    url: base_url + "cbtcetak/getruang?ruang=" + ruang + '&sesi=' + sesi + '&mapel=' + jadwal,
                    success: function (response) {
                        console.log('respon', response);
                        info = response.info;
                        $('#loading').addClass('d-none');
                        arrSiswa = response.siswa;
                        var sesi = response.info.sesi;
                        var ruang = response.info.ruang;

                        const alokasiJadwal = alokasiJam(response, false)
                        for (const tanggal in alokasiJadwal) {
                            const arrJadwal = alokasiJadwal[tanggal]
                            for (const jadwal of arrJadwal) {
                                const match = jadwal.id_mapel === response.info.mapel &&
                                    jadwal.sesi_id === sesi.id_sesi &&
                                    jadwal.ruang_id === ruang.id_ruang

                                if (match) {
                                    const found = currJadwal.find(e => e.id_mapel === jadwal.id_mapel &&
                                        e.sesi_id === jadwal.sesi_id && e.ruang_id === jadwal.ruang_id &&
                                        e.jadwal.tgl_mulai === jadwal.jadwal.tgl_mulai)
                                    if (!found) {
                                        currJadwal.push(jadwal)
                                    }
                                }
                            }
                        }

                        if (currJadwal.length === 0) {
                            const mapel = $("#mapel-jadwal option:selected").text();
                            var kelasVal = ruang.nama_ruang + ' (' + ruang.kode_ruang + ')';
                            empty.empty()
                                .append('Tidak ada jadwal ')
                                .append($('<b>').text(mapel))
                                .append(' untuk Ruang ')
                                .append($('<b>').text(kelasVal))
                                .append(' sesi ')
                                .append($('<b>').text(sesi.nama_sesi));
                            empty.removeClass('d-none');
                            return;
                        }

                        if (currJadwal.length > 1) {
                            opsiTgl.removeClass('d-none')
                            mplTgl.prepend("<option value='' selected='selected'>Pilih Tanggal</option>");
                            for (const jadwal of currJadwal) {
                                mplTgl.append(`<option value="${jadwal.jadwal.tgl_mulai}">${formatTanggal(jadwal.jadwal.tgl_mulai)}</option>`);
                            }
                        } else {
                            const jadwal = currJadwal[0];
                            applyJadwal(jadwal)
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#loading').addClass('d-none');
                        console.log("error", xhr.responseText);
                    }
                });
            }
        }

        function loadSiswaKelas(kelas, sesi, jadwal) {
            var notempty = kelas && sesi && jadwal;
            if (notempty) {
                $('#loading').removeClass('d-none');
                $.ajax({
                    type: "GET",
                    url: base_url + "cbtcetak/getkelas?kelas=" + kelas + '&sesi=' + sesi + '&mapel=' + jadwal,
                    success: function (response) {
                        $('#loading').addClass('d-none');
                        console.log('respon', response);

                        arrSiswa = response.siswa;
                        var sesi = response.info.sesi;
                        var ruang = response.info.ruang;

                        const alokasiJadwal = alokasiJam(response, false)
                        for (const tanggal in alokasiJadwal) {
                            const arrJadwal = alokasiJadwal[tanggal]
                            for (const jadwal of arrJadwal) {
                                const match = jadwal.kelas_id.includes(response.info.kelas.id_kelas) &&
                                    jadwal.id_mapel === response.info.mapel &&
                                    jadwal.sesi_id === sesi.id_sesi

                                if (match) {
                                    const found = currJadwal.find(e => e.id_mapel === jadwal.id_mapel &&
                                        e.sesi_id === jadwal.sesi_id && e.ruang_id === jadwal.ruang_id &&
                                        e.jadwal.tgl_mulai === jadwal.jadwal.tgl_mulai)
                                    if (!found) {
                                        currJadwal.push(jadwal)
                                    }
                                }
                            }
                        }

                        if (currJadwal.length === 0) {
                            const mapel = $("#mapel-jadwal option:selected").text();
                            var kelasVal = response.info.kelas.nama_kelas;
                            empty.empty()
                                .append('Tidak ada jadwal ')
                                .append($('<b>').text(mapel))
                                .append(' untuk Ruang ')
                                .append($('<b>').text(kelasVal))
                                .append(' sesi ')
                                .append($('<b>').text(sesi.nama_sesi));
                            empty.removeClass('d-none');
                            return;
                        }
                        if (currJadwal.length > 1) {
                            opsiTgl.removeClass('d-none')
                            mplTgl.prepend("<option value='' selected='selected'>Pilih Tanggal</option>");
                            for (const jadwal of currJadwal) {
                                mplTgl.append(`<option value="${jadwal.jadwal.tgl_mulai}">${formatTanggal(jadwal.jadwal.tgl_mulai)}</option>`);
                            }
                        } else {
                            const jadwal = currJadwal[0];
                            applyJadwal(jadwal)
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#loading').addClass('d-none');
                        console.log("error", xhr.responseText);
                    }
                });
            }
        }

        opsiJadwal.prepend("<option value='' selected='selected'>Pilih Jadwal</option>");
        opsiRuang.prepend("<option value='' selected='selected'>Pilih Ruang</option>");
        opsiSesi.prepend("<option value='' selected='selected'>Pilih Sesi</option>");
        opsiKelas.prepend("<option value='' selected='selected'>Pilih Kelas</option>");


        opsiKelas.change(function () {
            $('#edit-ruang').text($("#kelas option:selected").text());
            loadSiswaKelas($(this).val(), opsiSesi.val(), opsiJadwal.val())
        });

        opsiRuang.change(function () {
            $('#edit-ruang').text($("#ruang option:selected").text());
            loadSiswaRuang($(this).val(), opsiSesi.val(), opsiJadwal.val())
        });

        opsiSesi.change(function () {
            $('#edit-sesi').text($("#sesi option:selected").text());
            if (printBy === 1) {
                loadSiswaRuang(opsiRuang.val(), $(this).val(), opsiJadwal.val())
            } else {
                loadSiswaKelas(opsiKelas.val(), $(this).val(), opsiJadwal.val())
            }
        });

        opsiJadwal.change(function () {
            if (printBy === 1) {
                loadSiswaRuang(opsiRuang.val(), opsiSesi.val(), $(this).val())
            } else {
                loadSiswaKelas(opsiKelas.val(), opsiSesi.val(), $(this).val())
            }
        });

        mplTgl.off('change').on('change', function () {
            const tgl = $(this).val();
            const jadwalSelected = currJadwal.find(e => e.jadwal.tgl_mulai === tgl)
            if (jadwalSelected) {
                applyJadwal(jadwalSelected, info)
            }
        })

        $("#btn-print").click(function () {
            var kosong = printBy === 2
                ? (opsiKelas.val() === '' || opsiSesi.val() === '' || opsiJadwal.val() === '')
                : (opsiRuang.val() === '' || opsiSesi.val() === '' || opsiJadwal.val() === '');
            if (kosong) {
                Swal.fire({
                    title: "ERROR",
                    text: "Isi semua pilihan terlebih dulu",
                    icon: "error"
                })
            } else {
                $('#print-preview').print(document.title);
            }
        });

        $("#header-1").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal1) {
                return;
            }
            oldVal1 = currentVal;
        });

        $("#header-2").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal2) {
                return;
            }
            oldVal2 = currentVal;
        });

        $("#header-3").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal3) {
                return;
            }
            oldVal3 = currentVal;
        });

        $("#header-4").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal4) {
                return;
            }
            oldVal4 = currentVal;
        });

        $('#set-kop').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            Swal.fire({
                text: "Silahkan tunggu....",
                button: false,
                closeOnClickOutside: false,
                closeOnEsc: false,
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let form = new FormData($('#set-kop')[0]);
            $.ajax({
                url: base_url + 'cbtcetak/savekopberita',
                type: 'POST',
                processData: false,
                contentType: false,
                data: form,
                success: function (response) {
                    console.log(response);
                    Swal.fire({
                        title: 'Sukses',
                        text: "Template KOP berhasil disimpan",
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                    }).then(result => {
                        if (result.value) {
                            window.location.href = base_url + 'cbtcetak/beritaacara'
                        }
                    });
                },
                error: function (xhr, error, status) {
                    console.log(xhr.responseText);
                    const err = JSON.parse(xhr.responseText)
                    Swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        $('#selector button').click(function () {
            $(this).addClass('active').siblings().removeClass('active');
            console.log('change')
            if (!$('#by-kelas').is(':hidden')) {
                $('#by-kelas').addClass('d-none');
                $('#by-ruang').removeClass('d-none');
                printBy = 1;
                $('#title-ruang').text('Ruang');
                loadSiswaRuang(opsiRuang.val(), opsiSesi.val(), opsiJadwal.val())
            } else {
                $('#by-kelas').removeClass('d-none');
                $('#by-ruang').addClass('d-none');
                $('#title-ruang').text('Kelas');
                printBy = 2;
                loadSiswaKelas(opsiKelas.val(), opsiSesi.val(), opsiJadwal.val())
            }
        });

        opsiKelas.select2({ width: '100%', theme: 'bootstrap-5' });
        opsiRuang.select2({ width: '100%', theme: 'bootstrap-5' });
        opsiSesi.select2({ width: '100%', theme: 'bootstrap-5' });
        opsiJadwal.select2({ width: '100%', theme: 'bootstrap-5' });

    })

    function applyJadwal(jadwal) {
        const tgl = handleTanggal(jadwal.jadwal.tgl_mulai);
        //console.log('jadwal', jadwal)
        const all_siswa_ruang = arrSiswa.filter(e => jadwal.ids_siswa.includes(e.id_siswa));

        $('#edit-hari').empty().append($('<b>').text(tgl.HARI));
        $('#edit-tanggal').empty().append($('<b>').text(tgl.TANGGAL));
        $('#edit-bulan').empty().append($('<b>').text(tgl.BULAN));
        $('#edit-tahun').empty().append($('<b>').text(tgl.TAHUN));
        $('#edit-jml-peserta').empty().append($('<b>').text(all_siswa_ruang.length));

        $('#edit-jenis-ujian').empty().append($('<b>').text(jadwal.jadwal.nama_jenis));
        $('#edit-nama-ujian').empty().append($('<b>').text(jadwal.jadwal.nama_jenis));
        $('#edit-waktu-mulai').empty().append($('<b>').text(jadwal.dari));
        $('#edit-waktu-akhir').empty().append($('<b>').text(jadwal.sampai));
        $('#edit-mapel').empty().append($('<b>').text(jadwal.jadwal.nama_mapel));

        // previewTTD(jadwal.pengawas);
        document.title = 'Berita Acara ' + jadwal.jadwal.kode + ' ' + $('#edit-ruang').text() + ' ' + $('#edit-sesi').text();

        var qrcodeContainer = document.getElementById("qrcode-single-view");
        if (qrcodeContainer) {
            qrcodeContainer.innerHTML = "";
            var text = "Mata Pelajaran: " + jadwal.jadwal.nama_mapel + "\nTanggal: " + tgl.HARI + ", " + tgl.TANGGAL + " " + tgl.BULAN + " " + tgl.TAHUN;
            new QRCode(qrcodeContainer, {
                text: text,
                width: 100,
                height: 100,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

    }

    function alokasiJam(data, is_all) {
        const timeToMinutes = (time) => {
            if (!time || !time.includes(':')) return 0;
            const [h, m] = time.split(':').map(Number);
            return h * 60 + m;
        };

        const minutesToTime = (totalMinutes) => {
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        };

        const sesis = Array.isArray(data.all_ruang) ? data.all_ruang : [];

        const results = processAndGroupData(data, is_all);
        const resultsArray = {};
        for (const resultsKey in results) {
            resultsArray[resultsKey] = [];
            const alokasi = results[resultsKey];
            const maxJamKe = Math.max(...alokasi.alokasi.map(m => Number(m.jam_ke)));

            const mapelByJamKe = {};
            alokasi.alokasi.forEach(mapel => {
                const jamKe = mapel.jam_ke;
                if (!mapelByJamKe[jamKe]) {
                    mapelByJamKe[jamKe] = [];
                }
                mapelByJamKe[jamKe].push({
                    ...mapel,
                    ...alokasi.ruang_sesi,
                    siswa: alokasi.siswaRuang
                });
            });

            sesis.forEach(sesi => {
                const totalDuration = timeToMinutes(sesi.waktu_akhir) - timeToMinutes(sesi.waktu_mulai);

                const durationPerSlot = totalDuration / maxJamKe;

                for (let i = 1; i <= maxJamKe; i++) {
                    const jamKe = String(i);

                    const startMinutes = timeToMinutes(sesi.waktu_mulai) + (i - 1) * durationPerSlot;
                    const endMinutes = startMinutes + durationPerSlot;

                    const start_time = minutesToTime(startMinutes);
                    const end_time = minutesToTime(endMinutes);

                    if (mapelByJamKe[jamKe]) {
                        mapelByJamKe[jamKe].forEach(mapel => {
                            const found = resultsArray[resultsKey].find(m => m.jadwal?.id_jadwal === mapel.jadwal.id_jadwal && m?.jam_ke === mapel.jam_ke);
                            if (!found) {
                                resultsArray[resultsKey].push({
                                    ...mapel,
                                    dari: start_time,
                                    sampai: end_time,
                                    durasi: durationPerSlot
                                });
                            }
                        });
                    }
                }
            });

        }
        //console.log('result', resultsArray)
        return resultsArray;
    }

    function processAndGroupData(data, is_all) {
        const allSesi = data.all_ruang || [];
        const jsonData = data.all_tgl || [];
        const grouped = {};

        for (const tgl in jsonData) {
            const jadwals = jsonData[tgl];
            jadwals.forEach(jadwal => {
                jadwal.bank_kelas = jadwal.bank_kelas.map(String)
                const ruangFiltered = allSesi.filter(r =>
                    r.kelas_id.some(id => jadwal.bank_kelas.includes(id?.toString()))
                );

                ruangFiltered.forEach(ruang => {
                    // siswa dalam sesi/ruang ini
                    const siswaRuang = data.siswa.filter(s => {
                        if (is_all) {
                            return s.ruang_id === ruang.ruang_id &&
                                s.sesi_id === ruang.sesi_id &&
                                jadwal.bank_kelas.includes(s.id_kelas)
                        } else {
                            if (printBy === 2) {
                                return s.sesi_id === ruang.sesi_id &&
                                    jadwal.bank_kelas.includes(s.id_kelas)
                            } else {
                                return s.ruang_id === ruang.ruang_id &&
                                    s.sesi_id === ruang.sesi_id &&
                                    jadwal.bank_kelas.includes(s.id_kelas)
                            }
                        }
                    })

                    const key = `${tgl}-${ruang.ruang_id}-${ruang.sesi_id}`;
                    if (!grouped[key]) {
                        grouped[key] = {
                            key: key,
                            ruang_sesi: ruang,
                            siswaRuang: siswaRuang,
                            jam_ke: [],
                            id_mapel: [],
                            alokasi: [],
                        };
                    }
                    //grouped[key].jadwal.push(jadwal);
                    if (!grouped[key].id_mapel.includes(jadwal.id_mapel)) {
                        grouped[key].id_mapel.push(jadwal.id_mapel);
                    }
                    const pengawasJadwal = data.all_pengawas.find(p =>
                        p.id_jadwal === jadwal.id_jadwal &&
                        p.id_ruang === ruang.ruang_id &&
                        p.id_sesi === ruang.sesi_id
                    ) || { guru: [], id_guru: '', jam_ke: '' };
                    if (!grouped[key].jam_ke.includes(pengawasJadwal.jam_ke)) {
                        grouped[key].jam_ke.push(pengawasJadwal.jam_ke)
                    }
                    if (!grouped[key].alokasi.find(a => a.id_mapel === jadwal.id_mapel && a.jam_ke === jadwal.jam_ke)) {
                        grouped[key].alokasi.push({
                            id_mapel: jadwal.id_mapel,
                            nama_mapel: jadwal.nama_mapel,
                            jam_ke: pengawasJadwal.jam_ke,
                            pengawas: pengawasJadwal.pengawas,
                            jadwal: jadwal,
                        });
                    }
                });
            });
        }
        console.log('group', grouped)
        return grouped;
    }

    function formatTanggal(dateString) {
        const date = new Date(dateString);
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

</script>
<?= $this->endSection() ?>