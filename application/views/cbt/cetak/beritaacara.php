<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow">
                <div class="card-header">
                    <h6 class="card-title">Setting Kop</h6>
                    <button class="card-tools btn btn-sm bg-primary text-white" onclick="submitKop()">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                     <a href="<?= base_url('cbtcetak') ?>" type="button" class="btn btn-sm btn-danger float-right mr-2">
                        <i class="fas fa-arrow-circle-left"></i><span
                            class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>                   
                </div>
                <div class="card-body">
                    <?= form_open('', array('id' => 'set-kop')) ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Header 1</label>
                                <textarea id="header-1" class="form-control" name="header_1" rows="2"
                                          placeholder="Header baris 1"
                                          required><?= isset($kop->header_1) ? $kop->header_1 : '' ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Header 2</label>
                                <textarea id="header-2" class="form-control" name="header_2" rows="2"
                                          placeholder="Header baris 2"
                                          required><?= isset($kop->header_2) ? $kop->header_2 : '' ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Header 3</label>
                                <textarea id="header-3" class="form-control" name="header_3" rows="2"
                                          placeholder="Header baris 3"
                                          required><?= isset($kop->header_3) ? $kop->header_3 : '' ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Header 4</label>
                                <textarea id="header-4" class="form-control" name="header_4" rows="2"
                                          placeholder="Header baris 4"
                                          required><?= isset($kop->header_4) ? $kop->header_4 : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>

            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6>Cetak Sekaligus</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-sm-5 mb-3" id="by-mapel">
                            <label>Mapel</label>
                            <?php
                            echo form_dropdown('mapel', $mapel_jadwal, null, 'id="mapel" class="form-control"'); ?>
                        </div>
                        <div class="col-sm-3 col-6 mb-3 pb-2 align-self-end">
                            <div id="loading-page" class="d-none">
                                <i class="fa fa-spinner fa-spin mr-2"></i> Memuat...
                            </div>
                            <div id="info-page" class="d-none"></div>
                        </div>
                        <div class="col-sm-4 col-6 mb-3 align-self-end text-right">
                            <button class="btn bg-success text-white" id="btn-print-all">
                                <i class="fa fa-print"></i><span class="ml-1">Cetak Semua</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6>Cetak</h6>
                    </div>
                    <div id="selector" class="card-tools btn-group">
                        <button type="button" class="btn active btn-primary">By Ruang</button>
                        <button type="button" class="btn btn-outline-primary">By Kelas</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-sm-3 d-none" id="by-kelas">
                            <label>Kelas</label>
                            <?php
                            echo form_dropdown(
                                'kelas',
                                $kelas,
                                null,
                                'id="kelas" class="form-control"'
                            ); ?>
                        </div>
                        <div class="col-6 col-sm-3" id="by-ruang">
                            <label>Ruang</label>
                            <?php
                            echo form_dropdown(
                                'ruang',
                                $ruang,
                                null,
                                'id="ruang" class="form-control"'
                            ); ?>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label>Sesi</label>
                            <?php
                            echo form_dropdown(
                                'sesi',
                                $sesi,
                                null,
                                'id="sesi" class="form-control"'
                            ); ?>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <label>Jadwal</label>
                            <?php
                            echo form_dropdown('mapel', $mapel_jadwal, null, 'id="mapel-jadwal" class="form-control"');
                            ?>
                        </div>
                        <div class="col-6 col-md-3 mb-3 d-none" id="select-tgl">
                            <label>Tanggal</label>
                            <select name="tanggal" id="mapel-tgl" class="form-control select2"></select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <div class="flex-grow-1 mr-3">
                            <div class="alert alert-default-danger d-none" id="empty-jadwal">
                            </div>
                        </div>
                        <div class="text-right">
                            <button class="btn bg-success text-white" id="btn-print">
                                <i class="fa fa-print"></i><span class="ml-1">Cetak</span>
                            </button>
                        </div>
                    </div>
                    <br>
                    <div class="p-4 bg-gray-light">
                        <div style="display: flex; justify-content: center; align-items: center;">
                            <div id="print-preview" style="width: 21cm; min-height: 29cm;" class="border my-shadow p-5 bg-white">
                                <table id="table-header-print"
                                       style="width: 100%; border: 0;">
                                    <tr>
                                        <td style="width:15%;">
                                            <img alt="logo kiri" id="prev-logo-kanan-print"
                                                 src="<?= isset($kop->logo_kiri) ? base_url() . $kop->logo_kiri : '' ?>"
                                                 style="width:85px; height:85px; margin: 6px;">
                                        </td>
                                        <td style="width:70%; text-align: center;">
                                            <div style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 14pt"><?= isset($kop->header_1) ? $kop->header_1 : '' ?></div>
                                            <div style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 16pt">
                                                <b><?= isset($kop->header_2) ? $kop->header_2 : '' ?></b></div>
                                            <div style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 13pt"><?= isset($kop->header_3) ? $kop->header_3 : '' ?></div>
                                            <div style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 12pt"><?= isset($kop->header_4) ? $kop->header_4 : '' ?></div>
                                        </td>
                                        <td style="width:15%;">
                                            <img alt="logo kanan" id="prev-logo-kiri-print"
                                                 src="<?= isset($kop->logo_kanan) ? base_url() . $kop->logo_kanan : '' ?>"
                                                 style="width:85px; height:85px; margin: 6px; border-style: none">
                                        </td>
                                    </tr>
                                </table>
                                <hr style="border: 1px solid; margin-bottom: 6px">
                                <br>
                                <br>
                                <div style="text-align: justify; font-family: 'Times New Roman'">
                                    Pada hari ini <span class="editable bg-gray-light" id="edit-hari"
                                                        style="display: inline-block;min-width: 20px"><?= buat_tanggal(date('D')) ?></span>
                                    tanggal <span class="editable bg-gray-light" id="edit-tanggal"
                                                  style="display: inline-block;min-width: 20px"><?= buat_tanggal(date('d')) ?></span>
                                    bulan <span class="editable bg-gray-light" id="edit-bulan"
                                                style="display: inline-block;min-width: 20px"><?= buat_tanggal(date('M')) ?></span>
                                    tahun <span class="editable bg-gray-light" id="edit-tahun"
                                                style="display: inline-block;min-width: 20px"><?= buat_tanggal(date('Y')) ?></span>
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
                                <table style="width: 100%;font-family: 'Times New Roman';">
                                    <tr>
                                        <td style="width: 30px;">1.</td>
                                        <td style="width: 30%;">
                                            Pada Sekolah/Madrasah
                                        </td>
                                        <td>:</td>
                                        <td class="editable bg-gray-light"
                                            id="edit-nama_sekolah"><?= isset($kop->sekolah) ? $kop->sekolah : '' ?></td>
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
                                        <td colspan="3" style="height: 100px; border: 1px solid black; padding: 12px"
                                            class="editable bg-gray-light" id="edit-catatan"></td>
                                    </tr>
                                </table>
                                <br>
                                <br>
                                <br>
                                <br>
                                <div id="berita-ttd">
                                    <table style="width:90%; font-family: 'Times New Roman';">
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th style="text-align: center">TTD</th>
                                        </tr>
                                        <tr>
                                            <td style="width: 30px;">1.</td>
                                            <td>Pengawas 1</td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light" id="edit-pengawas1">_________________________</td>
                                            <td style="padding-left: 20px" rowspan="2">1. _________________________</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                NIP/NUPTK
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light">_________________________</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 12px">2.</td>
                                            <td style="padding-top: 12px">Pengawas 2</td>
                                            <td style="padding-top: 12px">:</td>
                                            <td style="padding-top: 12px" class="editable bg-gray-light" id="edit-pengawas2">_________________________</td>
                                            <td style="padding-left: 20px" rowspan="2">2. _________________________</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                NIP/NUPTK
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light">_________________________</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 12px">3.</td>
                                            <td style="padding-top: 12px">
                                                Kepala Sekolah
                                            </td>
                                            <td style="padding-top: 12px">:</td>
                                            <td style="padding-top: 12px"
                                                class="editable bg-gray-light"><?= isset($kop->kepsek) && $kop->kepsek != '' ? $kop->kepsek : '_________________________' ?></td>
                                            <td style="padding-left: 20px" rowspan="2">3. _________________________</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                NIP/NUPTK
                                            </td>
                                            <td>:</td>
                                            <td class="editable bg-gray-light"><?= isset($kop->nip) && $kop->nip != '' ? $kop->nip : '_________________________' ?></td>
                                        </tr>
                                    </table>
                                </div>
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
    </section>
</div>

<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>

<script>
    var allJadwal = JSON.parse(JSON.stringify(<?= json_encode($jadwal) ?>));

    const kepsek = "<?= isset($kop->kepsek) && $kop->kepsek != "" ? $kop->kepsek : "_________________________" ?>";
    const nip = "<?= isset($kop->nip) && $kop->nip != "" ? $kop->nip : "_________________________" ?>";
    var oldVal1 = "<?=$kop->header_1 ?? ""?>";
    var oldVal2 = "<?=$kop->header_2 ?? ""?>";
    var oldVal3 = "<?=$kop->header_3 ?? ""?>";
    var oldVal4 = "<?=$kop->header_4 ?? ""?>";
    var printBy = 1;

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
                //printPreview.style.padding = "1rem"
                printPreview.style.pageBreakAfter = 'always'
                //<div style="page-break-after: always"></div>

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
                header1Div.style.fontFamily = "'Times New Roman'";
                header1Div.style.fontSize = "14pt";
                header1Div.textContent = kop?.header_1 || 'Header 1'; // Ganti dengan data dinamis
                centerTd.appendChild(header1Div);

                const header2Div = document.createElement("div");
                header2Div.style.lineHeight = "1.1";
                header2Div.style.fontFamily = "'Times New Roman'";
                header2Div.style.fontSize = "16pt";
                header2Div.style.fontWeight = "bold";
                header2Div.textContent = kop?.header_2 || 'Header 2'; // Ganti dengan data dinamis
                centerTd.appendChild(header2Div);

                const header3Div = document.createElement("div");
                header3Div.style.lineHeight = "1.2";
                header3Div.style.fontFamily = "'Times New Roman'";
                header3Div.style.fontSize = "13pt";
                header3Div.textContent = kop?.header_3 || 'Header 3'; // Ganti dengan data dinamis
                centerTd.appendChild(header3Div);

                const header4Div = document.createElement("div");
                header4Div.style.lineHeight = "1.2";
                header4Div.style.fontFamily = "'Times New Roman'";
                header4Div.style.fontSize = "12pt";
                header4Div.textContent = kop?.header_4 || 'Header 4'; // Ganti dengan data dinamis
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
                hr.style.marginBottom = "6px";
                hr.style.marginBottom = "50px"
                printPreview.appendChild(hr);

                // Membuat elemen teks paragraf
                const tgl = handleTanggal(group.jadwal.tgl_mulai)
                const paragraph = document.createElement("div");
                paragraph.style.marginBottom = "12px";
                paragraph.style.textAlign = "justify";
                paragraph.style.fontFamily = "'Times New Roman'";
                paragraph.innerHTML = `Pada hari ini <b>${tgl?.HARI}</b> tanggal <b>${tgl?.TANGGAL}</b> bulan <b>${tgl?.BULAN}</b> tahun <b>${tgl?.TAHUN}</b>
                        telah diselenggarakan <b>${group.jadwal.nama_jenis}</b> untuk Mata Pelajaran <b>${group.jadwal.nama_mapel}</b>
                        dari pukul <b>${group.dari}</b> sampai dengan pukul <b>${group.sampai}</b>`;
                printPreview.appendChild(paragraph);

                // Membuat tabel data
                const dataTable = document.createElement("table");
                dataTable.style.width = "100%";
                dataTable.style.fontFamily = "'Times New Roman'";

                // Baris data
                const rowsData = [
                    {number: "1.", label: "Pada Sekolah/Madrasah", content: kop?.sekolah || 'Nama Sekolah'},
                    {
                        number: "",
                        label: "Ruang",
                        content: group.nama_ruang
                    },
                    {
                        number: "",
                        label: "Sesi",
                        content: group.nama_sesi
                    },
                    {
                        number: "",
                        label: "Jumlah Peserta Seharusnya",
                        content: group.siswa.length
                    },
                    {
                        number: "",
                        label: "Jumlah Peserta Hadir",
                        content: "................................................................."
                    },
                    {
                        number: "",
                        label: "Jumlah Peserta Tidak Hadir",
                        content: "................................................................."
                    },
                    {
                        number: "",
                        label: "Nomor Peserta Tidak Hadir",
                        content: "................................................................."
                    }
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
                signatureTable.style.fontFamily = "'Times New Roman'";
                signatureTable.style.marginTop = '100px'

                // Data tanda tangan
                const pengawas1 = group.pengawas[0]?.nama_guru || 'Pengawas';
                const nip1 = group.pengawas[0]?.nip || '_________________________';
                let signatureData = [
                    {number: "1.", label: "Pengawas 1", content: pengawas1, signatureId: "1."},
                    {number: "", label: "NIP/NUPTK", content: nip1},
                ];
                const pengawas2 = group.pengawas[1]?.nama_guru || '';
                const nip2 = group.pengawas[1]?.nip || '_________________________';
                if (pengawas2) {
                    signatureData.push({number: "2.", label: "Pengawas 2", content: pengawas2, signatureId: "2."})
                    signatureData.push({number: "", label: "NIP/NUPTK", content: nip2})
                }
                signatureData.push({number: pengawas2 ? "3." : "2.", label: "Kepala Sekolah", content: kop?.kepsek, signatureId: pengawas2 ? "3." : "2."})
                signatureData.push({number: "", label: "NIP/NUPTK", content: kop?.nip || "_________________________"})

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
                        signId.textContent = row.signatureId + ' _________________________';
                        tr.appendChild(signId);
                    }
                    signatureTable.appendChild(tr);
                });
                // Menambahkan tabel tanda tangan ke elemen utama
                printPreview.appendChild(signatureTable);

                // Menambahkan elemen utama ke body
                $('#all-print-preview').append(printPreview)
                allPages ++;

            }
        }
        /*
        const kelTanggal = [];
        for (const jadwal of data.jadwal) {
            const jadwalExist = kelTanggal.find(e => e.tgl === jadwal.tgl_mulai)

            let arrkls = [];
            for (const bankKelas of jadwal.bank_kelas) {
                arrkls.push(bankKelas.kelas_id)
            }
            let siswaJadwal = data.siswa.filter(s => arrkls.includes(s.kelas_id))
            siswaJadwal.sort((a, b) => a.kelas_id - b.kelas_id);
            siswaJadwal.sort((a, b) => a.ruang_id - b.ruang_id);
            siswaJadwal.sort((a, b) => a.sesi_id - b.sesi_id);

            if (!jadwalExist) {
                let tglJadwal = {
                    'tgl': jadwal.tgl_mulai,
                    'jadwal': jadwal,
                    'siswa': siswaJadwal
                }
                kelTanggal.push(tglJadwal)
            } else {
                jadwalExist.siswa = [...jadwalExist.siswa, ...siswaJadwal]
            }
        }

        let allPages = 0;
        for (const tglJadwal of kelTanggal) {
            const jadwal = tglJadwal.jadwal;
            let siswa = tglJadwal.siswa
            const groupedData = siswa.reduce((acc, item) => {
                const groupKey = `${item.ruang_id}-${item.sesi_id}`;
                if (!acc[groupKey]) {
                    acc[groupKey] = [];
                }
                acc[groupKey].push(item);
                return acc;
            }, {});

            const seenKeys = new Set();
            const filtered = data.all_tgl[tglJadwal.tgl]?.filter((item) => {
                const currentKey = `${item.tgl_mulai}_${item.jam_ke}_${item.bank_mapel_id}`;
                if (seenKeys.has(currentKey)) {
                    return false;
                }
                seenKeys.add(currentKey);
                return true;
            });

            const alokasiSesi = alokasiJam(data.all_sesi, filtered)
            Object.entries(groupedData).forEach(([key, group]) => {
                const ruangSesi = key.split('-')
                const ruang = data.ruang.find(r => r.id_ruang === ruangSesi[0])
                const sesi = data.sesi.find(r => r.id_sesi === ruangSesi[1])
                let pengawasJadwal = data.pengawas.find(p => p.jadwal === jadwal.id_jadwal && p.ruang === ruangSesi[0] && p.sesi === ruangSesi[1])
                const alokasi = alokasiSesi[sesi.id_sesi]?.waktuSesi.find(a => a.idJadwal === jadwal.id_jadwal && a.jamKe === jadwal.jam_ke)

            });
        }
         */
        $('#loading-page').addClass('d-none');
        $('#info-page').removeClass('d-none');
        $('#info-page').text(`${allPages} Halaman`)

        document.title = 'Berita Acara '+$('#mapel').find('option:selected').text()+'.pdf';
    }

    $(document).ready(function () {
        ajaxcsrf();

        console.log('jadwal', allJadwal)
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

        $("#btn-print-all").click(async function () {
            if (!opsiMapel.val()) {
                Swal.fire({
                    title: "ERROR",
                    text: "Isi Mapel terlebih dulu",
                    icon: "error"
                })
                return
            }
            $('#all-print-preview').print(document.title);
        });

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
                        info = response.info;
                        $('#loading').addClass('d-none');
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
                            empty.html(`Tidak ada jadwal <b>${mapel}</b> untuk Ruang <b>${kelasVal}</b> sesi <b>${sesi.nama_sesi}</b>`);
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
                            empty.html(`Tidak ada jadwal <b>${mapel}</b> untuk Ruang <b>${kelasVal}</b> sesi <b>${sesi.nama_sesi}</b>`);
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
            //var kelasTitle = printBy === 2 ? 'Kelas' : 'Ruang';
            //var kelasVal = printBy === 2 ? info.kelas.nama_kelas : info.ruang.nama_ruang + ' (' + info.ruang.kode_ruang + ')';

            var kosong = printBy === 2 ? (opsiKelas.val() === '' || (opsiSesi.val() === '') || (opsiMapel.val() === '')) : (opsiRuang.val() === '' || (opsiSesi.val() === '') || (opsiMapel.val() === ''));
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

            swal.fire({
                text: "Silahkan tunggu....",
                button: false,
                closeOnClickOutside: false,
                closeOnEsc: false,
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
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
                    swal.fire({
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
                    swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        $('#selector button').click(function () {
            $(this).addClass('active').siblings().addClass('btn-outline-primary').removeClass('active btn-primary');
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

        opsiKelas.select2({width: '100%', theme: 'bootstrap4'});
        opsiRuang.select2({width: '100%', theme: 'bootstrap4'});
        opsiSesi.select2({width: '100%', theme: 'bootstrap4'});
        opsiJadwal.select2({width: '100%', theme: 'bootstrap4'});

    })

    function applyJadwal(jadwal) {
        const tgl = handleTanggal(jadwal.jadwal.tgl_mulai);
        console.log('jadwal', jadwal)

        $('#edit-hari').html('<b>' + tgl.HARI + '</b>');
        $('#edit-tanggal').html('<b>' + tgl.TANGGAL + '</b>');
        $('#edit-bulan').html('<b>' + tgl.BULAN + '</b>');
        $('#edit-tahun').html('<b>' + tgl.TAHUN + '</b>');
        $('#edit-jml-peserta').html('<b>' + jadwal.siswa.length + '</b>');

        $('#edit-jenis-ujian').html('<b>' + jadwal.jadwal.nama_jenis + '</b>');
        $('#edit-nama-ujian').html('<b>' + jadwal.jadwal.nama_jenis + '<b>');
        $('#edit-waktu-mulai').html('<b>' + jadwal.dari + '</b>');
        $('#edit-waktu-akhir').html('<b>' + jadwal.sampai + '</b>');
        $('#edit-mapel').html('<b>' + jadwal.jadwal.nama_mapel + '</b>');

        previewTTD(jadwal.pengawas);
        document.title = 'Berita Acara ' + jadwal.jadwal.kode + ' ' + $('#edit-ruang').text() + ' ' + $('#edit-sesi').text();

    }

    function previewTTD(pengawas) {
        var nomor = 1;
        var pengawas1 = pengawas.length > 0 ? pengawas[0].nama_guru : '';
        var pengawas2 = pengawas.length > 1 ? pengawas[1].nama_guru : '';
        var nip1 = pengawas.length > 0 ? pengawas[0].nip : '_________________________';
        var nip2 = pengawas.length > 1 ? pengawas[1].nip : '_________________________';
        var title_p1 = pengawas2 === '' ? 'Pengawas' : 'Pengawas 1';

        var table = '<table style="width:90%; font-family: \'Times New Roman\';">' +
        ' <tr>' +
        ' <th></th>' +
        ' <th></th>' +
        ' <th></th> <th></th> <th style="text-align: center">TTD</th>' +
        ' </tr>' +

        ' <tr>' +
        ' <td style="width: 30px;">'+nomor+'.</td>' +
        ' <td>'+title_p1+'</td>' +
        ' <td>:</td>' +
        ' <td class="editable bg-gray-light" id="edit-pengawas1">'+pengawas1+'</td>' +
        ' <td style="padding-left: 20px" rowspan="2">1. _________________________</td>' +
        ' </tr>' +

        ' <tr>' +
        ' <td></td>' +
        ' <td>NIP/NUPTK </td>' +
        '         <td>:</td>' +
        ' <td class="editable bg-gray-light">'+nip1+'</td>' +
        ' </tr>';
        nomor +=1;
        if (pengawas2 !== '') {
            table += ' <tr>' +
                ' <td style="padding-top: 12px">'+nomor+'.</td>' +
                ' <td style="padding-top: 12px">Pengawas 2</td>' +
                ' <td style="padding-top: 12px">:</td>' +
                ' <td style="padding-top: 12px" class="editable bg-gray-light" id="edit-pengawas2">'+pengawas2+'</td>' +
                ' <td style="padding-left: 20px" rowspan="2">'+nomor+'. _________________________</td>' +
                '</tr>' +

                ' <tr>' +
                ' <td></td>' +
                ' <td>NIP/NUPTK </td>' +
                '         <td>:</td>' +
                ' <td class="editable bg-gray-light">'+nip2+'</td>' +
                ' </tr>';
            nomor +=1;
        }
        table += ' <tr>' +
        ' <td style="padding-top: 12px">'+nomor+'.</td>' +
        ' <td style="padding-top: 12px">Kepala Sekolah</td>' +
        '         <td style="padding-top: 12px">:</td>' +
        ' <td style="padding-top: 12px"class="editable bg-gray-light">'+kepsek+'</td>' +
        '         <td style="padding-left: 20px" rowspan="2">'+nomor+'. _________________________</td>' +
        ' </tr>' +

        ' <tr>' +
        ' <td></td>' +
        ' <td>NIP/NUPTK </td>' +
        '         <td>:</td>' +
        ' <td class="editable bg-gray-light">'+nip+'</td>' +
        ' </tr>' +
        ' </table>';

        $('#berita-ttd').html(table)
        $('.editable').attr('contentEditable', true);
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
