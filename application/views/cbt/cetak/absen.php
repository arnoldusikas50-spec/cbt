<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <?= form_open('', array('id' => 'set-kop')) ?>
            <div class="card my-shadow">
                <div class="card-header">
                    <h6 class="card-title">Setting Kop Daftar Kehadiran</h6>
                    <button type="submit" class="card-tools btn btn-sm bg-primary text-white">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="<?= base_url('cbtcetak') ?>" type="button" class="btn btn-sm btn-danger float-right mr-2">
                        <i class="fas fa-arrow-circle-left"></i><span
                            class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Header 1</label>
                                <input class="form-control" name="header_1" placeholder="Header 1"
                                       value="<?= isset($kop->header_1) ? $kop->header_1 : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Header 2</label>
                                <input class="form-control" name="header_2" placeholder="Header 2"
                                       value="<?= isset($kop->header_2) ? $kop->header_2 : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Header 3</label>
                                <input class="form-control" name="header_3" placeholder="Header 3"
                                       value="<?= isset($kop->header_3) ? $kop->header_3 : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Header 4</label>
                                <input class="form-control" name="header_4" placeholder="Header 4"
                                       value="<?= isset($kop->header_4) ? $kop->header_4 : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4 d-none">
                            <div class="form-group">
                                <label>Proktor</label>
                                <input id="input-proktor" class="form-control" name="proktor" placeholder="Proktor"
                                       value="<?= isset($kop->proktor) ? $kop->proktor : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-4 d-none">
                            <div class="form-group">
                                <label>Pengawas</label>
                                <input id="input-pengawas-1" class="form-control" name="pengawas_1"
                                       placeholder="Pengawas 1"
                                       value="<?= isset($kop->pengawas_1) ? $kop->pengawas_1 : '' ?>">
                            </div>
                        </div>
                        <div class="col-md-4 d-none">
                            <div class="form-group">
                                <label>Pengawas 2</label>
                                <input id="input-pengawas-2" class="form-control" name="pengawas_2"
                                       placeholder="Pengawas 2"
                                       value="<?= isset($kop->pengawas_2) ? $kop->pengawas_2 : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= form_close() ?>

            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6>Cetak Sekaligus</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3" id="by-mapel">
                            <label>Mapel</label>
                            <?php
                            echo form_dropdown('mapel', $mapel_jadwal, null, 'id="mapel" class="form-control"'); ?>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 pb-2 align-self-end">
                            <div id="loading-page" class="d-none">
                                <i class="fa fa-spinner fa-spin mr-2"></i> Memuat...
                            </div>
                            <div id="info-page" class="d-none"></div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3 align-self-end">
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
                        <h6>Cetak Per Ruang/Kelas</h6>
                    </div>
                    <div id="selector" class="card-tools btn-group">
                        <button type="button" class="btn active btn-primary">By Ruang</button>
                        <button type="button" class="btn btn-outline-primary">By Kelas</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 col-md-3 mb-3 d-none" id="by-kelas">
                            <label>Kelas</label>
                            <?php
                            echo form_dropdown('kelas', $kelas, null, 'id="kelas" class="form-control"'); ?>
                        </div>
                        <div class="col-6 col-md-3 mb-3" id="by-ruang">
                            <label>Ruang</label>
                            <?php
                            echo form_dropdown('ruang', $ruang, null, 'id="ruang" class="form-control"'); ?>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <label>Sesi</label>
                            <?php
                            echo form_dropdown('sesi', $sesi, null, 'id="sesi" class="form-control"'); ?>
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
                    <div class="text-right">
                        <button class="btn bg-success text-white" id="btn-print">
                            <i class="fa fa-print"></i><span class="ml-1">Cetak</span>
                        </button>
                    </div>
                    <br>
                    <div class="d-flex justify-content-center bg-gray-light" style="min-height: 300mm">
                        <div id="print-preview" class="m-4">
                        </div>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="spinner-grow"></div>
                </div>
            </div>

            <div id="all-print-preview" class="m-4 d-none"></div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script>
    var oldVal1 = "<?=isset($kop->header_1) ? $kop->header_1 : ""?>";
    var oldVal2 = "<?=isset($kop->header_2) ? $kop->header_2 : ""?>";
    var oldVal3 = "<?=isset($kop->header_3) ? $kop->header_3 : ""?>";
    var oldVal4 = "<?=isset($kop->header_4) ? $kop->header_4 : ""?>";
    var logoKanan = "<?=isset($kop->logo_kanan) ? base_url() . $kop->logo_kanan : ""?>";
    var logoKiri = "<?=isset($kop->logo_kiri) ? base_url() . $kop->logo_kiri : ""?>";
    var tandatangan = "<?= isset($kop->tanda_tangan) ? base_url() . $kop->tanda_tangan : ""?>";
    var proktor = "<?=isset($kop->proktor) ? $kop->proktor : ""?>";
    //var pengawas1 = "<?=isset($kop->pengawas_1) ? $kop->pengawas_1 : ""?>";
    //var pengawas2 = "<?=isset($kop->pengawas_2) ? $kop->pengawas_2 : ""?>";
    var printBy = 1;

    function handleNull(value) {
        if (value == null || value == "0" || value == "") return "-";
        else return value;
    }

    function handleTanggal(tgl) {
        var hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jum'at", "Sabtu"];
        var bulans = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        var ttl = "";
        if (handleNull(tgl) != "-") {
            var d = new Date(tgl);
            var curr_day = d.getDay();
            var curr_date = d.getDate();

            var curr_month = d.getMonth();
            var curr_year = d.getFullYear();

            ttl += hari[curr_day] + ', ' + curr_date + " " + bulans[curr_month] + " " + curr_year;
        }
        return ttl;
    }

    function buatTanggal() {
        var hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
        var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

        var d = new Date();
        var curr_day = d.getDay();
        var curr_date = d.getDate();

        var curr_month = d.getMonth();
        var curr_year = d.getFullYear();

        return hari[curr_day] + ", " + curr_date + "  " + bulan[curr_month] + " " + curr_year;
    }

    function createPrintPreview(data) {
        $('#print-preview').html('');
        const opsiTgl = $('#select-tgl');
        opsiTgl.addClass('d-none')
        const mplTgl = $('#mapel-tgl');
        mplTgl.html('');

        console.log(data);
        var sesi = data.info.sesi;
        var ruang = data.info.ruang;

        const alokasiJadwal = alokasiJam(data, false)

        const currJadwal = [];
        for (const tanggal in alokasiJadwal) {
            const arrJadwal = alokasiJadwal[tanggal]
            for (const jadwal of arrJadwal) {
                let match;
                if (printBy === 2) {
                    match = jadwal.kelas_id.includes(data.info.kelas.id_kelas) &&
                        jadwal.id_mapel === data.info.mapel &&
                        jadwal.sesi_id === sesi.id_sesi
                } else {
                    match = jadwal.id_mapel === data.info.mapel &&
                        jadwal.sesi_id === sesi.id_sesi &&
                        jadwal.ruang_id === ruang.id_ruang
                }

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

        var kelasTitle = printBy === 2 ? 'Kelas' : 'Ruang';
        var kelasVal = printBy === 2 ? data.info.kelas.nama_kelas : ruang.nama_ruang + ' (' + ruang.kode_ruang + ')';
        if (currJadwal.length === 0) {
            $('#print-preview').html(`<b>Tidak ada data jadwal untuk ${kelasTitle} `+kelasVal+' sesi '+sesi.nama_sesi+'</b>');
            $('#loading').addClass('d-none');
            return;
        }
        //console.log('jadwal', currJadwal);
        if (currJadwal.length > 1) {
            opsiTgl.removeClass('d-none')
            mplTgl.prepend("<option value='' selected='selected'>Pilih Tanggal</option>");
            for (const jadwal of currJadwal) {
                mplTgl.append(`<option value="${jadwal.jadwal.tgl_mulai}">${formatTanggal(jadwal.jadwal.tgl_mulai)}</option>`);
            }
        } else {
            const jadwal = currJadwal[0];
            buildTable(jadwal, data.info)
        }
        $('#loading').addClass('d-none');

        mplTgl.off('change').on('change', function () {
            const tgl = $(this).val();
            const jadwalSelected = currJadwal.find(e => e.jadwal.tgl_mulai === tgl)
            if (jadwalSelected) {
                buildTable(jadwalSelected, data.info)
            } else {
                $('#print-preview').html('<b>Tidak ada data jadwal untuk ${kelasTitle} '+kelasVal+' sesi '+sesi.nama_sesi+'</b>');
            }
        })
    }

    function buildTable(jadwal, info) {
        var sesi = info.sesi;
        var ruang = info.ruang;
        //var bagi = 25;
        jadwal.siswa.sort((a, b) => a?.nomor_peserta?.localeCompare(b?.nomor_peserta, undefined, {numeric: true}))

        var bagi = 28;
        var pages = Math.ceil(jadwal.siswa.length / bagi);
        console.log('page', pages);
        var kelasTitle = printBy === 2 ? 'Kelas' : 'Ruang';

        var kelasVal = printBy === 2 ? info.kelas.nama_kelas : jadwal.nama_ruang + ' (' + jadwal.kode_ruang + ')';
        document.title = 'Daftar Hadir ' + jadwal.kode + ' ' + kelasVal + ' ' + sesi.nama_sesi;
        var pengawas1 = '';
        var pengawas2 = '';
        var nip1 = '';
        var nip2 = '';
        if ($.isArray(jadwal.pengawas[0])) {
            pengawas1 = jadwal.pengawas.length > 0 && jadwal.pengawas[0].length > 0 ? jadwal.pengawas[0][0].nama_guru : '';
            pengawas2 = jadwal.pengawas.length > 0 && jadwal.pengawas[0].length > 1 ? jadwal.pengawas[0][1].nama_guru : '';
            nip1 = jadwal.pengawas.length > 0 && jadwal.pengawas[0].length > 0 ? jadwal.pengawas[0][0].nip : '';
            nip2 = jadwal.pengawas.length > 0 && jadwal.pengawas[0].length > 1 ? jadwal.pengawas[0][1].nip : '';
        } else {
            pengawas1 = jadwal.pengawas.length > 0 ? jadwal.pengawas[0].nama_guru : '';
            pengawas2 = jadwal.pengawas.length > 1 ? jadwal.pengawas[1].nama_guru : '';
            nip1 = jadwal.pengawas.length > 0 ? jadwal.pengawas[0].nip : '';
            nip2 = jadwal.pengawas.length > 1 ? jadwal.pengawas[1].nip : '';
        }

        var card = '';
        for (let a = 0; a < pages; a++) {
            let t = a * bagi;
            let end = (a + 1) < pages ? t + bagi : jadwal.siswa.length;

            card += '<div class="border my-shadow mb-3 p-4 bg-white">' +
                '<div style="-webkit-justify-content: center;justify-content: center;width: 200mm; height: 290mm;padding: 15px">';

            card += '<table style="width: 100%; border: 0;">' +
                '<tr>' +
                '<td style="width:15%;">' +
                '<img src="' + logoKiri + '" style="width:85px; height:85px; margin: 6px;">' +
                '</td>' +
                '<td style="width:70%; text-align: center;">' +
                '<div style="line-height: 1.1; font-family: \'Times New Roman\'; font-size: 14pt">' + oldVal1 + '</div>' +
                '<div style="line-height: 1.1; font-family: \'Times New Roman\'; font-size: 16pt"><b>' + oldVal2 + '</b></div>' +
                '<div style="line-height: 1.2; font-family: \'Times New Roman\'; font-size: 13pt">' + oldVal3 + '</div>' +
                '<div style="line-height: 1.2; font-family: \'Times New Roman\'; font-size: 12pt">' + oldVal4 + '</div>' +
                '</td>' +
                '<td style="width:15%;">' +
                '<img src="' + logoKanan + '" style="width:85px; height:85px; margin: 6px; border-style: none">' +
                '</td>' +
                '</tr>' +
                '</table>' +
                '<hr style="border: 1px solid; margin-bottom: 6px">' +
                '<div style="display: flex;align-items: stretch;">' +
                '<div style="flex: 50%">' +
                '<table style="width: 100%; border: 0;">' +
                '<tbody>' +
                '<tr style="line-height: 1.1;font-family: \'Times New Roman\';">' +
                '<td>' + kelasTitle + '</td><td>:</td><td>' + kelasVal + '</td>' +
                '</tr>' +
                '<tr style="line-height: 1.1;font-family: \'Times New Roman\';">' +
                '<td>Sesi</td><td>:</td><td>' + jadwal.nama_sesi + '</td>' +
                '</tr>' +
                '<tr style="line-height: 1.1;font-family: \'Times New Roman\';">' +
                '<td>Waktu</td><td>:</td><td>' + jadwal.dari + ' s/d ' + jadwal.sampai + '</td>' +
                '</tr>' +
                '</tbody>' +
                '</table>' +
                '</div>' +
                '  <div style="flex: 50%">' +
                '<table style="width: 100%; border: 0;">' +
                '<tbody>' +
                '<tr style="line-height: 1.1;font-family: \'Times New Roman\';">' +
                '<td>Hari/Tanggal</td><td>:</td><td>' + handleTanggal(jadwal.jadwal.tgl_mulai) + '</td>' +
                '</tr>' +
                '<tr style="line-height: 1.1;font-family: \'Times New Roman\';">' +
                '<td>Mata Pelajaran</td><td>:</td><td>' + jadwal.nama_mapel + '</td>' +
                '</tr>' +
                '</tbody>' +
                '</table>' +
                '</div>' +
                '</div>' +
                '<br>' +
                '<table style="width: 100%; border-collapse: collapse">' +
                '<tr style="font-family: \'Times New Roman\';">' +
                '<th style="border: 1px solid black; width: 40px; height: 40px; text-align: center;">No.</th>' +
                '<th style="border: 1px solid black; text-align: center; width: 200px">No Peserta</th>' +
                '<th style="border: 1px solid black; text-align: center;">Nama</th>' +
                '<th style="border: 1px solid black; text-align: center;">Kelas</th>' +
                '<th colspan="2" style="border: 1px solid black; text-align: center;">Tanda Tangan</th>' +
                '<th style="border: 1px solid black; text-align: center;width: 80px;">Ket.</th>' +
                '</tr>';

            for (let i = t; i < end; i++) {
                var genap = (i + 1) % 2 === 0;
                var forGenap = genap ? (i + 1) + '.' : '';
                var forGanjil = genap ? '' : (i + 1) + '.';
                card += '<tr style="font-family: \'Times New Roman\'; font-size: 10pt">' +
                    '<td class="ts" style="line-height: 1.1;border: 1px solid black; text-align: center">' + (i + 1) + '</td>' +
                    '<td class="ts" style="line-height: 1.1;border: 1px solid black; text-align: center; padding-left: 5px">' + (jadwal.siswa[i].nomor_peserta || "-") + '</td>' +
                    '<td class="ts" style="line-height: 1.1;border: 1px solid black; padding-left: 5px">' + jadwal.siswa[i]?.nama + '</td>' +
                    '<td class="ts" style="line-height: 1.1;border: 1px solid black;text-align: center;">' + jadwal.siswa[i]?.kode_kelas + '</td>' +
                    '<td class="ts" style="line-height: 1.1;border-bottom: 1px solid black;width:50px;padding-left: 5px">' + forGanjil + '</td>' +
                    '<td class="ts" style="line-height: 1.1;border-bottom: 1px solid black;width:100px;padding-left: 5px">' + forGenap + '</td>' +
                    '<td class="ts" style="line-height: 1.1;border: 1px solid black;"> </td>' +
                    '</tr>';
            }
            card += '</table>';
            if (a === (pages - 1)) {
                card += '</table>' +
                    '<br>' +
                    '<br>' +
                    '<table style="width: 100%">' +
                    '<tr style="line-height: 1.1;font-family: \'Times New Roman\';font-size: 10pt">' +
                    '<td style="width: 40%; text-align: left">' +
                    '<table style="border: 1px solid black; border-collapse: collapse">' +
                    '<tr>' +
                    '<td style="padding-left: 5px;">Jumlah peserta yang seharusnya hadir</td>' +
                    '<td>:</td>' +
                    '<td style="padding-right: 5px"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> orang</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td style="border-bottom: 1px solid black;padding-left: 5px;">Jumlah peserta yang tidak hadir</td>' +
                    '<td style="border-bottom: 1px solid black;">:</td>' +
                    '<td style="border-bottom: 1px solid black; padding-right: 5px"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> orang</td>' +
                    '</tr>' +
                    '<tr >' +
                    '<td style="padding-left: 5px;">Jumlah peserta hadir</td>' +
                    '<td>:</td>' +
                    '<td style="padding-right: 5px"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> orang</td>' +
                    '</tr>' +
                    '</table>' +
                    '</td>';
                if (pengawas2 == '') {
                    card += '<td style="width: 30%; padding-left: 10px"></td>';
                } else {
                    card += '<td style="width: 30%; padding-left: 10px">' +
                        'Pengawas 1' +
                        '<br>' +
                        '<br>' +
                        '<br>' +
                        '<br>' +
                        '<u>' + pengawas1 + '</u>' +
                        '<br>' +
                        'Nip:' + nip1 +
                        '</td>';
                }
                if (pengawas2 == '') {
                    card += '<td style="width: 30%">' +
                        'Pengawas' +
                        '<br>' +
                        '<br>' +
                        '<br>' +
                        '<br>' +
                        '<u>' + pengawas1 + '</u>' +
                        '<br>' +
                        'Nip:' + nip1 +
                        '</td>';
                } else {
                    card += '<td style="width: 30%">' +
                        'Pengawas 2' +
                        '<br>' +
                        '<br>' +
                        '<br>' +
                        '<br>' +
                        '<u>' + pengawas2 + '</u>' +
                        '<br>' +
                        'Nip:' + nip2 +
                        '</td>';
                }
                card += '</tr>' +
                    '</table>' +
                    '</div>' +
                    '</div>';
            }
            card += '</div></div>';
            card += '<div style="page-break-after: always"></div>';
        }

        if (jadwal.siswa.length > 0) {
            $('#print-preview').html(card);
        } else {
            $('#print-preview').html('<b>Tidak ada data siswa di ruangan '+jadwal?.ruang?.nama_ruang+' '+jadwal?.sesi?.nama_sesi+', atau nomor peserta belum diatur</b>');
        }

        if (pages > 1) {
            $('.ts').height('23px');
        } else {
            $('.ts').height('20px');
        }
    }

    function createRow(cells, multipage) {
        const row = document.createElement("tr");
        row.style.fontFamily = 'Times New Roman';
        cells.forEach(([content, align, padding, width], ind) => {
            const cell = document.createElement("td");
            cell.style.lineHeight = '1.1'
            if (align) cell.style.textAlign = align;
            if (padding) cell.style.paddingLeft = padding

            if (ind === 6) {
                cell.style.borderRight = '1px solid black';
            }
            if (width) {
                if (width === '50px') {
                    cell.style.borderLeft = '1px solid black';
                }
                cell.style.width = width
                cell.style.borderBottom = '1px solid black';
            } else {
                cell.style.borderBottom = '1px solid black';
                cell.style.borderLeft = '1px solid black';
            }
            cell.style.height = multipage ? '22px' : '25px'
            cell.textContent = content;
            row.appendChild(cell);
        });
        return row;
    }

    function populateAllPreview(data) {
        $('#all-print-preview').html('')

        const kop = data.kop.kop_absensi
        let allPages = 0;

        const alokasiSesi = alokasiJam(data, true)
        for (const key in alokasiSesi) {
            for (const group of alokasiSesi[key]) {
                const bagi = 28;
                const pages = Math.ceil(group.siswa.length / bagi);

                for (let a = 0; a < pages; a++) {
                    const t = a * bagi;
                    let end = (a + 1) < pages ? t + bagi : group.siswa.length;

                    const card = document.createElement('div');
                    card.className = 'mb-3 bg-white page-print';
                    card.style.cssText = "width: 210mm; height: 290mm;";

                    const table = document.createElement('table');
                    table.style.width = '100%';
                    table.style.border = '0';

                    const row = document.createElement('tr');
                    const leftCell = document.createElement('td');
                    leftCell.style.width = '15%';

                    const leftImg = document.createElement('img');
                    leftImg.src = logoKiri;
                    leftImg.style.cssText = 'width: 85px; height: 85px; margin: 6px;';
                    leftCell.appendChild(leftImg);

                    const centerCell = document.createElement('td');
                    centerCell.style.cssText = 'width: 70%; text-align: center;';
                    centerCell.innerHTML = `
            <div style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 14pt">${kop?.header_1 || 'Header 1'}</div>
            <div style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 16pt"><b>${kop?.header_2 || 'Header 2'}</b></div>
            <div style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 13pt">${kop?.header_3 || 'Header 3'}</div>
            <div style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 12pt">${kop?.header_4 || 'Header 4'}</div>`;

                    const rightCell = document.createElement('td');
                    rightCell.style.width = '15%';

                    const rightImg = document.createElement('img');
                    rightImg.src = logoKanan;
                    rightImg.style.cssText = 'width: 85px; height: 85px; margin: 6px; border-style: none;';
                    rightCell.appendChild(rightImg);

                    row.appendChild(leftCell);
                    row.appendChild(centerCell);
                    row.appendChild(rightCell);
                    table.appendChild(row);
                    card.appendChild(table);

                    const hr = document.createElement('hr');
                    hr.style.border = '1px solid'
                    hr.style.marginBottom = '6px'
                    hr.style.marginTop = '0'
                    card.appendChild(hr)

                    // Add additional dynamic content for each page
                    const flexContainer = document.createElement('div');
                    flexContainer.style.display = 'flex';
                    flexContainer.style.alignItems = 'stretch';

                    const leftSection = document.createElement('div');
                    leftSection.style.flex = '50%';

                    const leftTable = document.createElement('table');
                    leftTable.style.width = '100%';
                    leftTable.style.border = '0';

                    const leftTableBody = document.createElement('tbody');
                    leftTableBody.innerHTML = `
            <tr style="line-height: 1.1; font-family: 'Times New Roman';">
                <td>Ruang</td><td>:</td><td>${group.nama_ruang} (${group.kode_ruang})</td>
            </tr>
            <tr style="line-height: 1.1; font-family: 'Times New Roman';">
                <td>Sesi</td><td>:</td><td>${group.nama_sesi}</td>
            </tr>
            <tr style="line-height: 1.1; font-family: 'Times New Roman';">
                <td>Waktu</td><td>:</td><td>${group.dari} s/d ${group.sampai}</td>
            </tr>
        `;
                    leftTable.appendChild(leftTableBody);
                    leftSection.appendChild(leftTable);

                    const rightSection = document.createElement('div');
                    rightSection.style.flex = '50%';

                    const rightTable = document.createElement('table');
                    rightTable.style.width = '100%';
                    rightTable.style.border = '0';

                    const rightTableBody = document.createElement('tbody');
                    rightTableBody.innerHTML = `
            <tr style="line-height: 1.1; font-family: 'Times New Roman';">
                <td>Hari/Tanggal</td><td>:</td><td>${handleTanggal(group.jadwal.tgl_mulai)}</td>
            </tr>
            <tr style="line-height: 1.1; font-family: 'Times New Roman';">
                <td>Mata Pelajaran</td><td>:</td><td>${group.nama_mapel}</td>
            </tr>
        `;
                    rightTable.appendChild(rightTableBody);
                    rightSection.appendChild(rightTable);

                    flexContainer.appendChild(leftSection);
                    flexContainer.appendChild(rightSection);
                    card.appendChild(flexContainer);

                    //tabel siswa
                    let tableStatus = document.createElement("table");
                    tableStatus.classList.add('mb-3')
                    tableStatus.style.width = '99%'
                    tableStatus.style.borderCollapse = 'collapse';
                    //tableStatus.style.border = '1px solid black';

                    let tableHead = document.createElement("thead");
                    tableStatus.appendChild(tableHead)

                    let headerRow = document.createElement('tr');
                    headerRow.style.fontFamily = 'Times New Roman';
                    const headers = [
                        { text: "No.", width: 40},
                        { text: "No. Peserta", width: 200 },
                        { text: "Nama"},
                        { text: "Kelas"},
                        { text: "Tanda Tangan", cols: 2},
                        { text: "Ket.",  width: 80},
                    ];

                    headers.forEach((header, ind) => {
                        const th = document.createElement('th');
                        th.textContent = header.text;
                        th.style.textAlign = 'center';

                        th.style.border = '1px solid black';
                        //if (ind === 5) {}
                        th.style.height = '40px';
                        if (header.cols) th.colSpan = header.cols
                        if (header.width) th.style.width = `${header.width}px`;
                        headerRow.appendChild(th);
                    });
                    tableHead.appendChild(headerRow);

                    let tableBody = document.createElement("tbody");
                    tableStatus.appendChild(tableBody)

                    group.siswa.sort((a, b) => a.nama.localeCompare(b.nama, undefined, {numeric: true}))
                    group.siswa.sort((a, b) => a.id_kelas - b.id_kelas);
                    for (let i = t; i < end; i++) {
                        const item = group.siswa[i]
                        var genap = (i + 1) % 2 === 0;
                        var forGenap = genap ? (i + 1) + '.' : '';
                        var forGanjil = genap ? '' : (i + 1) + '.';

                        tableBody.appendChild(
                            createRow([
                                [(i+1), 'center'],
                                [item.nomor_peserta || "-", 'center', '5px'],
                                [item?.nama, null, '5px'],
                                [item?.kode_kelas, 'center'],
                                [forGanjil, null, '5px', '50px'],
                                [forGenap, null, '5px', '100px'],
                                [' ', 'center'],
                            ], pages > 1)
                        );
                    }
                    card.appendChild(tableStatus);

                    // footer
                    if (a === (pages - 1)) {
                        const footer = document.createElement('div');

                        const table = document.createElement('table');
                        table.style.width = '100%';

                        const row = document.createElement('tr');
                        row.style.cssText = "line-height: 1.1; font-family: 'Times New Roman'; font-size: 10pt";

                        // Kolom pertama
                        const leftCell = document.createElement('td');
                        leftCell.style.cssText = 'width: 44%; text-align: left';

                        const innerTable = document.createElement('table');
                        innerTable.style.cssText = 'border: 1px solid black; border-collapse: collapse';

                        const addRow = (label) => {
                            const tr = document.createElement('tr');

                            const tdLabel = document.createElement('td');
                            tdLabel.style.paddingLeft = '5px';
                            tdLabel.textContent = label;

                            const tdSeparator = document.createElement('td');
                            tdSeparator.textContent = ':';

                            const tdValue = document.createElement('td');
                            tdValue.style.paddingRight = '5px';
                            tdValue.innerHTML = '<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> orang';

                            tr.appendChild(tdLabel);
                            tr.appendChild(tdSeparator);
                            tr.appendChild(tdValue);

                            return tr;
                        };

                        innerTable.appendChild(addRow('Jumlah peserta yang seharusnya hadir'));
                        innerTable.appendChild(addRow('Jumlah peserta yang tidak hadir'));
                        innerTable.appendChild(addRow('Jumlah peserta hadir'));

                        leftCell.appendChild(innerTable);
                        row.appendChild(leftCell);

                        // Kolom kedua (Pengawas 1)
                        const pengawas1 = group.pengawas[0]?.nama_guru || 'Pengawas';
                        const nip1 = group.pengawas[0]?.nip || '';
                        const pengawas2 = group.pengawas[1]?.nama_guru || '';
                        const nip2 = group.pengawas[1]?.nip || '';

                        const middleCell = document.createElement('td');
                        middleCell.style.cssText = 'width: 28%; padding-left: 7px';
                        if (pengawas2) {
                            middleCell.innerHTML = `
                Pengawas 1<br><br><br><br>
                <u>${pengawas1}</u><br>
                Nip: ${nip1}
            `;
                        }
                        row.appendChild(middleCell);

                        // Kolom ketiga (Pengawas 2)
                        const rightCell = document.createElement('td');
                        rightCell.style.width = '28%';
                        if (pengawas2) {
                            rightCell.innerHTML = `
                Pengawas 2<br><br><br><br>
                <u>${pengawas2}</u><br>
                Nip: ${nip2}
            `;
                        } else {
                            rightCell.innerHTML = `
                Pengawas<br><br><br><br>
                <u>${pengawas1}</u><br>
                Nip: ${nip1}
            `;
                        }
                        row.appendChild(rightCell);

                        table.appendChild(row);
                        //container.appendChild(table);
                        card.appendChild(table);
                    }

                    const pageBreak = document.createElement('div');
                    pageBreak.style.pageBreakAfter = 'always';
                    card.appendChild(pageBreak);

                    $('#all-print-preview').append(card)
                    allPages ++
                }
            }
        };

        $('#loading-page').addClass('d-none');
        $('#info-page').removeClass('d-none');
        $('#info-page').text(`${allPages} Halaman`)
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
                    r.kelas_id.some(id => jadwal.bank_kelas.includes(id.toString()))
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
        const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
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
                            populateAllPreview(response)
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
            const docTitle = 'Daftar Hadir ' + $("#mapel option:selected").text();
            document.title = docTitle;
            $('#all-print-preview').print(docTitle);
        });

        opsiMapel.prepend("<option value='' selected='selected'>Pilih Mapel</option>");
        opsiMapel.change(function () {
            loadAllSiswaRuang($(this).val())
        });

        var opsiJadwal = $("#mapel-jadwal");
        var opsiRuang = $("#ruang");
        var opsiSesi = $("#sesi");
        var opsiKelas = $("#kelas");

        function loadSiswaRuang(ruang, sesi, jadwal) {
            var notempty = ruang && sesi && jadwal;
            console.log('ruang', ruang);

            if (notempty) {
                //$('#print-preview').addClass('d-none');
                $('#loading').removeClass('d-none');

                setTimeout(function () {
                    $.ajax({
                        type: "GET",
                        url: base_url + "cbtcetak/getruang?ruang=" + ruang + '&sesi=' + sesi + '&mapel=' + jadwal,
                        success: function (response) {
                            createPrintPreview(response);
                        },
                        error: function (xhr, status, error) {
                            $('#loading').addClass('d-none');
                            console.log("error", xhr.responseText);
                        }
                    });
                }, 500);
            }
        }

        function loadSiswaKelas(kelas, sesi, jadwal) {
            //$('#print-preview').addClass('d-none');
            var notempty = kelas && sesi && jadwal;
            if (notempty) {
                $('#loading').removeClass('d-none');
                setTimeout(function () {
                    $.ajax({
                        type: "GET",
                        url: base_url + "cbtcetak/getkelas?kelas=" + kelas + '&sesi=' + sesi + '&mapel=' + jadwal,
                        success: function (response) {
                            createPrintPreview(response);
                        },
                        error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                        }
                    });
                }, 500);
            }
        }

        opsiJadwal.prepend("<option value='' selected='selected'>Pilih Jadwal</option>");
        opsiRuang.prepend("<option value='' selected='selected'>Pilih Ruang</option>");
        opsiSesi.prepend("<option value='' selected='selected'>Pilih Sesi</option>");
        opsiKelas.prepend("<option value='' selected='selected'>Pilih Kelas</option>");

        opsiKelas.change(function () {
            loadSiswaKelas($(this).val(), opsiSesi.val(), opsiJadwal.val())
        });

        opsiRuang.change(function () {
            loadSiswaRuang($(this).val(), opsiSesi.val(), opsiJadwal.val())
        });

        opsiSesi.change(function () {
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

        $("#btn-print").click(function () {
            var kosong = printBy === 2 ? ($('#kelas').val() === '' || ($('#sesi').val() === '') || ($('#jadwal').val() === '')) : ($('#ruang').val() === '' || ($('#sesi').val() === '') || ($('#jadwal').val() === ''));
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

            console.log($(this).serialize());
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
                url: base_url + 'cbtcetak/savekop',
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
                            window.location.href = base_url + 'cbtcetak/absenpeserta'
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

            if (!$('#by-kelas').is(':hidden')) {
                $('#by-kelas').addClass('d-none');
                $('#by-ruang').removeClass('d-none');
                printBy = 1;
                loadSiswaRuang(opsiRuang.val(), opsiSesi.val(), opsiJadwal.val())
            } else {
                $('#by-kelas').removeClass('d-none');
                $('#by-ruang').addClass('d-none');
                printBy = 2;
                loadSiswaKelas(opsiKelas.val(), opsiSesi.val(), opsiJadwal.val())
            }
        });

        opsiMapel.select2({width: '100%', theme: 'bootstrap4'});
        opsiKelas.select2({width: '100%', theme: 'bootstrap4'});
        opsiRuang.select2({width: '100%', theme: 'bootstrap4'});
        opsiSesi.select2({width: '100%', theme: 'bootstrap4'});
        opsiJadwal.select2({width: '100%', theme: 'bootstrap4'});

    })

</script>
