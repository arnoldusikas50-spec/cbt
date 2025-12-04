<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4">
                <div class="card-header py-3">
                    <div class="card-title">
                        <h6><b><?= $mapel['nama_mapel'] ?></b></h6>
                    </div>

                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-right">
                        <i class="fas fa-arrow-circle-left"></i><span
                                class="d-none d-sm-inline-block ml-1">Kembali</span>
                    </button>
                   <button type="button" id="download-excel" class="btn btn-sm btn-info float-right mr-2" data-toggle="tooltip" title="Download Excel Nilai Akhir">
                        <i class="fa fa-file-excel ml-1 mr-1"></i><span class="d-none d-sm-inline-block ml-1">Ekspor ke Excel</span>
                    </button>

                </div>
                <div class="card-body">
                    <?php if ($kkm == null): ?>
                        <div class="alert alert-default-danger align-content-center" role="alert">
                            Download template tidak tersedia, Anda harus mengisi KKM terlebih dahulu di menu <b>DATA
                                RAPOR > KKM DAN BOBOT</b>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div type="button" class="btn btn-primary w-100" onclick="processDownload(<?= $mapel['id_mapel'] ?>, <?= $kelas['id_kelas'] ?>)">
                                    <i class="fa fa-download"></i> <span class="ml-1">Download Template</span>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-8">
                                        <?= form_open_multipart('', array('id' => 'uploadfile')); ?>
                                        <div class="custom-file">
                                            <input type="file" name="upload_file" class="custom-file-input"
                                                   id="custom-file">
                                            <label class="custom-file-label" for="customFile">Pilih file excel</label>
                                        </div>
                                        <?= form_close(); ?>
                                    </div>

                                    <div class="col-4">
                                        <button id="upload" class="btn btn-success w-100">
                                            <i class="fa fa-upload"></i> <span class="ml-1">Upload</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <?php
                        if ($kkm == null) :?>
                            <div class="alert alert-default-warning shadow align-content-center" role="alert">
                                KKM dan Bobot belum diatur
                            </div>
                        <?php endif; ?>
                        <?php
                        $multi = $setting_rapor->kkm_tunggal == "0" ? "MULTI" : "TUNGGAL";
                        $isi = $kkm->kkm;
                        $d = 0;
                        $dsd = $isi - 1;
                        $c = $isi;
                        $csd = floor($isi + (100 - $isi) / 3);
                        $b = $csd + 1;
                        $bsd = floor($b + (100 - $b) / 2);
                        $a = $bsd + 1;
                        $asd = 100;
                        ?>
                        <table class="table w-100 table-bordered border-dark">
                            <tr class="bg-light text-center">
                                <td style="width: 20%">KKM</td>
                                <td style="width: 20%">Jenis KKM</td>
                                <td style="width: 40%">Interval Predikat Berdasarkan KKM</td>
                                <td style="width: 20%">Bobot Nilai PAS</td>
                            </tr>
                            <tr class="text-center text-md text-dark">
                                <td><?= $kkm->kkm ?></td>
                                <td><?= $multi ?></td>
                                <td>
                                    <span class="bg-danger badge p-1">0 ~ <?= $dsd ?> : D</span>
                                    <span class="bg-warning badge p-1"><?= $c . ' ~ ' . $csd ?> : C</span>
                                    <span class="bg-blue badge p-1"><?= $b . ' ~ ' . $bsd ?> : B</span>
                                    <span class="bg-success badge p-1"><?= $a ?> ~ 100 : A</span>
                                </td>
                                <td><?= $kkm->bobot_pas ?> %</td>
                            </tr>
                        </table>
                        <div id="t-siswa" class="w-100"></div>
                        <?= form_open('', array('id' => 'uploadnilai')) ?>
                        <input type="hidden" name="id_kelas" class="form-control" value="<?= $mapel['id_mapel'] ?>">
                        <input type="hidden" name="id_mapel" class="form-control" value="<?= $kelas['id_kelas'] ?>">
                        <div class="row mt-3 mb-3">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-primary mt-3 mb-3">
                                    <i class="fa fa-save mr-1"></i>Simpan
                                </button>
                            </div>
                        </div>
                        <div id="for-upload" class="d-none row"></div>
                        <?= form_close() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?= form_open('', array('id' => 'uploadpas')); ?>
    <input type="hidden" name="id_mapel" class="form-control" value="<?= $mapel['id_mapel'] ?>">
    <input type="hidden" name="id_kelas" class="form-control" value="<?= $kelas['id_kelas'] ?>">
    <?= form_close(); ?>

</div>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/exceljs.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/js-excel-template.min.js"></script>

<script>
    window.guruKepsek = <?= json_encode([
        'nama' => $setting->kepsek ?? '',
        'nip'  => $setting->nip ?? ''
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    window.guruMapelAktif = <?= json_encode([
        'nama' => $guru->nama_lengkap ?? $guru->nama_guru ?? '',
        'nip'  => $guru->nip ?? ''
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<script type="text/javascript" src="<?= base_url() ?>/assets/plugins/jexcel/js/jexcel.js"></script>
<script type="text/javascript" src="<?= base_url() ?>/assets/plugins/jexcel/js/jsuites.js"></script>
<script>
    var arrSiswa = JSON.parse(JSON.stringify(<?= json_encode($siswa)?>));
    var arrNilai = JSON.parse(JSON.stringify(<?= json_encode($nilai)?>));
    var kkmGuru = JSON.parse(JSON.stringify(<?= json_encode($kkm)?>));
    var kkmSetting = JSON.parse(JSON.stringify(<?= json_encode($setting_rapor)?>));
    var idMapel = '<?=$mapel['id_mapel']?>';
    var idKelas = '<?=$kelas['id_kelas']?>';
    var namaMapel = '<?=$mapel['nama_mapel']?>';
    var namaKelas = '<?=$kelas['nama_kelas']?>';
    var tpActive = '<?=$tp_active->id_tp?>';
    var smtActive = '<?=$smt_active->id_smt?>';

    var isi;
    var bobotPH, bobotPTS, bobotPAS;
    if (kkmSetting.kkm_tunggal == '1') {
        isi = parseInt(kkmSetting.kkm);
        bobotPH = parseInt(kkmSetting.bobot_ph);
        bobotPTS = parseInt(kkmSetting.bobot_pts);
        bobotPAS = parseInt(kkmSetting.bobot_pas);
    } else {
        isi = parseInt(kkmGuru.kkm);
        bobotPH = parseInt(kkmGuru.bobot_ph);
        bobotPTS = parseInt(kkmGuru.bobot_pts);
        bobotPAS = parseInt(kkmGuru.bobot_pas);
    }
    var pre_d = 0;
    var pre_dsd = isi - 1;
    var pre_c = isi;
    var pre_csd = Math.floor(isi + (100 - isi) / 3);
    var pre_b = pre_csd + 1;
    var pre_bsd = Math.floor(pre_b + (100 - pre_b) / 2);
    var pre_a = pre_bsd + 1;
    var pre_asd = 100;

    var arrIndic = ["P1", "P2", "P3", "P4", "P5", "P6", "P7", "P8"];
    var cols = [];

    $(function () {
        bsCustomFileInput.init();
    });

    $(document).ready(function () {
        console.log('a:' + pre_a + ' b:' + pre_b + ' bd:' + pre_bsd + ' c:' + pre_c + ' cd:' + pre_csd + ' dd:' + pre_dsd);
        console.log('ph:' + bobotPH + ' PTS:' + bobotPTS + ' pas:' + bobotPAS);
        var dataSiswa = [];
        var row = 1;
        $.each(arrSiswa, function (i, v) {
            var noInduk = v.nisn == null || v.nisn == '' ? v.nis : v.nisn;
            var nilai = arrNilai[v.id_siswa];
            dataSiswa.push(
                [
                    noInduk, v.nama, nilai.nhar, nilai.npts, nilai.npas,
                    '=IFERROR(ROUND((((C' + row + '*' + bobotPH + ')/100)+((D' + row + '*' + bobotPTS + ')/100)+((E' + row + '*' + bobotPAS + ')/100)),0),"")',
                    '=IF(F' + row + '>' + pre_bsd + ',"A",IF(F' + row + '>' + pre_csd + ',"B",IF(F' + row + '>' + pre_dsd + ',"C",IF(F' + row + '<' + pre_c + ',"D",""))))',
                    nilai.p_deskripsi, nilai.k_rata_rata, nilai.k_predikat, nilai.k_deskripsi, v.id_siswa
                ]
            );
            row++;
        });

        var tableSiswa = $('#t-siswa');

        var arrCol = [];

        var pno = 1;
        var kno = 1;
        var char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for (let i = 0; i < 12; i++) {
            var item = {};

            if (i === 0) {
                item['title'] = 'NIS/NISN\n' + char.charAt(i);
                item['width'] = 160;
            } else if (i === 1) {
                item['title'] = 'NAMA SISWA\n' + char.charAt(i);
                item['width'] = 250;
            } else if (i === 2) {
                item['title'] = 'RPH\n' + char.charAt(i);
                item['width'] = 50;
                pno++;
            } else if (i === 3) {
                item['title'] = 'PTS\n' + char.charAt(i);
                item['width'] = 50;
            } else if (i === 4) {
                item['title'] = 'PAS\n' + char.charAt(i);
                item['width'] = 50;
            } else if (i === 5) {
                item['title'] = 'AKHIR\n' + char.charAt(i);
                item['width'] = 70;
            } else if (i === 6) {
                item['title'] = 'PRED\n' + char.charAt(i);
                item['width'] = 70;
            } else if (i === 7) {
                item['title'] = 'DESKRIPSI\n' + char.charAt(i);
                item['width'] = 300;
                item['wordWrap'] = true;
            } else if (i === 8) {
                item['title'] = 'NILAI\n' + char.charAt(i);
                item['width'] = 50;
            } else if (i === 9) {
                item['title'] = 'PRED\n' + char.charAt(i);
                item['width'] = 50;
            } else if (i === 10) {
                item['title'] = 'DESKRIPSI\n' + char.charAt(i);
                item['width'] = 300;
                item['wordWrap'] = true;
            } else if (i === 11) {
                item['title'] = 'ID';
                item['width'] = 1;
            }
            item['colspan'] = '1';

            arrCol.push(item);
        }

        tableSiswa.jexcel({
            data: dataSiswa,
            minDimensions: [12],
            //defaultColWidth: 100,
            tableOverflow: true,
            tableWidth: '' + tableSiswa.width() + 'px',
            tableHeight: (80 * dataSiswa.length) + 'px',
            search: true,
            freezeColumns: 2,
            //rowResize: true,
            columnResize: false,
            columns: arrCol,
            /*[
            {width: 100},
            {width: 300},       ],*/
            nestedHeaders: [
                [
                    {
                        title: 'DATA SISWA',
                        colspan: '2',
                    },
                    {
                        title: 'NILAI PENGETAHUAN',
                        colspan: '6',
                    },
                    {
                        title: 'NILAI KETERAMPILAN',
                        colspan: '4',
                    },
                ],
            ],
            updateTable: function (instance, cell, col, row, val, label, cellName) {
                if (col === 0) {
                    cell.className = '';
                    cell.style.backgroundColor = '#f8d7da';
                    cell.style.textAlign = 'center';
                    cell.classList.add('readonly');
                }

                if (col === 1) {
                    cell.className = '';
                    cell.style.backgroundColor = '#f8d7da';
                    cell.style.textAlign = 'left';
                    cell.classList.add('readonly');
                }

                if (col === 4) {
                    cell.style.backgroundColor = '#b9f6ca';
                }


                if (col === 7 || col === 10 || col === 11) {
                    cell.className = '';
                    cell.style.backgroundColor = '#fff3cd';
                    cell.classList.add('readonly');
                    cell.style.fontSize = 'small';
                }

                if (col === 2 || col === 3 || col === 5 || col === 6 || col === 8 || col === 9) {
                    cell.className = '';
                    cell.style.backgroundColor = '#ffebee';
                    cell.classList.add('readonly');
                }

                if (col === 5 || col === 6 || col === 8 || col === 9) {
                    cell.style.fontWeight = '600'
                }
            },
            onchange: function (instance, cell, col, row, value, label) {
                //var cellName = jexcel.getColumnNameFromId([col,row]);
                //console.log(col, cell);
            }
        });

        function cellRef(cel) {
            var x = cel.replace(/[A-Za-z]/g, "");
            x = x.charCodeAt(0) - 65;
            var y = cel.replace(/\D/g, "");
            y = y - 1;

            return x + "," + y;
        }

        function toNumber(value) {
            if (value === null || value === undefined) return null;
            var cleaned = String(value).replace(/[^0-9,.-]/g, '').replace(',', '.');
            if (cleaned === '') return null;
            var parsed = parseFloat(cleaned);
            return isNaN(parsed) ? null : parsed;
        }

        function toText(value) {
            return value === null || value === undefined ? '' : String(value).trim();
        }

        function hitungNilaiAkhir(nilaiHarian, nilaiPTS, nilaiPAS) {
            var ph = nilaiHarian === null ? 0 : nilaiHarian;
            var pts = nilaiPTS === null ? 0 : nilaiPTS;
            var pas = nilaiPAS === null ? 0 : nilaiPAS;
            if (nilaiHarian === null && nilaiPTS === null && nilaiPAS === null) {
                return null;
            }
            var total = ((ph * bobotPH) / 100) + ((pts * bobotPTS) / 100) + ((pas * bobotPAS) / 100);
            return Math.round(total);
        }

        function getPredikat(nilai) {
            if (nilai === null || nilai === undefined || nilai === '') {
                return '';
            }
            if (nilai > pre_bsd) return 'A';
            if (nilai > pre_csd) return 'B';
            if (nilai > pre_dsd) return 'C';
            if (nilai < pre_c) return 'D';
            return '';
        }

        $("#download-excel").on("click", async function () {
            const namaMapel = window.namaMapel || namaMapel;
            const namaKelas = window.namaKelas || namaKelas;

            const tableData = $('#t-siswa').jexcel('getData');

            if (!tableData || tableData.length === 0) {
                swal.fire({
                    title: "ERROR",
                    text: "DATA TIDAK TERSEDIA",
                    icon: "error"
                });
                return;
            }

            const dataset = tableData.map(function (row, idx) {
                const nilaiHarian = toNumber(row[2]);
                const nilaiPTS = toNumber(row[3]);
                const nilaiPAS = toNumber(row[4]);
                const nilaiAkhirManual = toNumber(row[5]);
                let nilaiAkhir = hitungNilaiAkhir(nilaiHarian, nilaiPTS, nilaiPAS);
                if (nilaiAkhir === null && nilaiAkhirManual !== null) {
                    nilaiAkhir = nilaiAkhirManual;
                }

                const predPengetahuan = getPredikat(nilaiAkhir);

                return {
                    no: idx + 1,
                    nis: toText(row[0]),
                    nama: toText(row[1]),
                    harian: nilaiHarian,
                    pts: nilaiPTS,
                    pas: nilaiPAS,
                    akhir: nilaiAkhir,
                    predPengetahuan: predPengetahuan || toText(row[6]),
                    deskPengetahuan: toText(row[7]),
                    nilaiKeterampilan: toNumber(row[8]),
                    predKeterampilan: toText(row[9]),
                    deskKeterampilan: toText(row[10])
                };
            });

            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet('Nilai Akhir');

                worksheet.pageSetup = {
                    paperSize: 9,
                    orientation: 'landscape',
                    margins: {
                        left: 0.6, right: 0.6,
                        top: 0.7, bottom: 0.7,
                        header: 0.3, footer: 0.3
                    },
                    fitToPage: true,
                    fitToWidth: 1,
                    fitToHeight: 1
                };

                const columnSettings = [
                    { header: 'No.', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'NISN', width: 14, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'Nama Siswa', width: 28, alignment: { horizontal: 'left', vertical: 'middle', wrapText: true } },
                    { header: 'Rerata Harian', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'PTS', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'PAS', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'Nilai Akhir', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'Pred', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'Deskripsi Pengetahuan', width: 40, alignment: { horizontal: 'left', vertical: 'top', wrapText: true } },
                    { header: 'NK', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'Pred', width: 5, alignment: { horizontal: 'center', vertical: 'middle' } },
                    { header: 'Deskripsi Ketrampilan', width: 40, alignment: { horizontal: 'left', vertical: 'top', wrapText: true } }
                ];

                columnSettings.forEach(function (col, index) {
                    const column = worksheet.getColumn(index + 1);
                    column.width = col.width;
                    column.alignment = col.alignment;
                });

                const lastColumnLetter = worksheet.getColumn(columnSettings.length).letter;

                worksheet.mergeCells(`A1:${lastColumnLetter}1`);
                worksheet.getCell('A1').value = 'LAPORAN NILAI AKHIR SISWA';
                worksheet.getCell('A1').font = { bold: true, size: 16 };
                worksheet.getCell('A1').alignment = { horizontal: 'center', vertical: 'middle' };

                worksheet.mergeCells(`A2:${lastColumnLetter}2`);
                worksheet.getCell('A2').value = `Mata Pelajaran: ${namaMapel}`;
                worksheet.getCell('A2').font = { bold: true, size: 12 };
                worksheet.getCell('A2').alignment = { horizontal: 'center', vertical: 'middle' };

                worksheet.mergeCells(`A3:${lastColumnLetter}3`);
                worksheet.getCell('A3').value = `Kelas: ${namaKelas}`;
                worksheet.getCell('A3').font = { bold: true, size: 12 };
                worksheet.getCell('A3').alignment = { horizontal: 'center', vertical: 'middle' };

                worksheet.addRow([]);

                const groupingRow = worksheet.addRow(['NO', 'DATA SISWA', '', 'NILAI PENGETAHUAN', '', '', '', '', '', 'NILAI KETERAMPILAN', '', '']);
                worksheet.mergeCells('B5:C5');
                worksheet.mergeCells('D5:I5');
                worksheet.mergeCells('J5:L5');
                groupingRow.font = { bold: true };
                groupingRow.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                groupingRow.eachCell(function (cell) {
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'D3D3D3' }
                    };
                });

                const headerRow = worksheet.addRow(columnSettings.map(function (col) { return col.header; }));
                headerRow.font = { bold: true };
                headerRow.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                headerRow.eachCell(function (cell) {
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: { argb: 'E0E0E0' }
                    };
                });

                dataset.forEach(function (item) {
                    worksheet.addRow([
                        item.no,
                        item.nis,
                        item.nama,
                        item.harian,
                        item.pts,
                        item.pas,
                        item.akhir,
                        item.predPengetahuan,
                        item.deskPengetahuan,
                        item.nilaiKeterampilan,
                        item.predKeterampilan,
                        item.deskKeterampilan
                    ]);
                });

                const signatureStartRow = worksheet.lastRow.number + 2;

                const namaKepala = window.guruKepsek?.nama ?? '';
                const nipKepala = window.guruKepsek?.nip ?? '';
                const namaGuruMapel = window.guruMapelAktif?.nama ?? '';
                const nipGuruMapel = window.guruMapelAktif?.nip ?? '';

                worksheet.mergeCells(`A${signatureStartRow}:C${signatureStartRow}`);
                worksheet.getCell(`A${signatureStartRow}`).value = 'Mengetahui,';
                worksheet.getCell(`A${signatureStartRow}`).alignment = { horizontal: 'center' };

                worksheet.mergeCells(`J${signatureStartRow}:L${signatureStartRow}`);
                worksheet.getCell(`J${signatureStartRow}`).value = `Kelas ${namaKelas}, ${new Date().toLocaleDateString('id-ID')}`;
                worksheet.getCell(`J${signatureStartRow}`).alignment = { horizontal: 'center' };

                const rowKepala = signatureStartRow + 1;
                worksheet.mergeCells(`A${rowKepala}:C${rowKepala}`);
                worksheet.getCell(`A${rowKepala}`).value = 'Kepala Sekolah';

                worksheet.mergeCells(`J${rowKepala}:L${rowKepala}`);
                worksheet.getCell(`J${rowKepala}`).value = 'Guru Mata Pelajaran';
                worksheet.getCell(`J${rowKepala}`).alignment = { horizontal: 'center' };

                const rowName = rowKepala + 4;
                worksheet.mergeCells(`A${rowName}:C${rowName}`);
                worksheet.getCell(`A${rowName}`).value = namaKepala ? namaKepala : '(________________________)';

                worksheet.mergeCells(`J${rowName}:L${rowName}`);
                worksheet.getCell(`J${rowName}`).value = namaGuruMapel ? namaGuruMapel : '(________________________)';
                worksheet.getCell(`J${rowName}`).alignment = { horizontal: 'center' };

                worksheet.mergeCells(`A${rowName + 1}:C${rowName + 1}`);
                worksheet.getCell(`A${rowName + 1}`).value = nipKepala ? `NIP. ${nipKepala}` : 'NIP. __________';

                worksheet.mergeCells(`J${rowName + 1}:L${rowName + 1}`);
                worksheet.getCell(`J${rowName + 1}`).value = nipGuruMapel ? `NIP. ${nipGuruMapel}` : 'NIP. __________';
                worksheet.getCell(`J${rowName + 1}`).alignment = { horizontal: 'center' };

                const startBorder = 5;
                const lastDataRow = 6 + dataset.length;
                for (let rowNumber = startBorder; rowNumber <= lastDataRow; rowNumber++) {
                    const row = worksheet.getRow(rowNumber);
                    row.eachCell(function (cell) {
                        cell.border = {
                            top: { style: 'thin' },
                            left: { style: 'thin' },
                            bottom: { style: 'thin' },
                            right: { style: 'thin' }
                        };
                    });
                }

                worksheet.getColumn(7).eachCell({ includeEmpty: true }, function (cell, rowNumber) {
                    if (rowNumber >= 7 && rowNumber <= dataset.length + 6) {
                        cell.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: 'B3E5FC' }
                        };
                        cell.font = Object.assign({}, cell.font, { bold: true });
                    }
                });

                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                saveAs(blob, `Nilai_Akhir_${namaMapel}_${namaKelas}.xlsx`);
            } catch (error) {
                console.error('excel-export-error', error);
                swal.fire({
                    title: "ERROR",
                    text: "Gagal menghasilkan file Excel",
                    icon: "error"
                });
            }
        });

        $('#uploadnilai').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            swal.fire({
                title: "Menyimpan nilai akhir",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            var tbl = $('table.jexcel tr').get().map(function (row) {
                return $(row).find('td').get().map(function (cell) {
                    return $(cell).html();
                });
            });
            tbl.shift();
            tbl.shift();
            var inputs = '';
            $.each(tbl, function (idx, s) {
                var idSiswa = s[12];
                inputs += '<input type="text" name="siswa[' + idSiswa + '][id_nilai_akhir]" value="' + idMapel + idKelas + idSiswa + tpActive + smtActive + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][id_siswa]" value="' + idSiswa + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][id_tp]" value="' + tpActive + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][id_smt]" value="' + smtActive + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][id_mapel]" value="' + idMapel + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][id_kelas]" value="' + idKelas + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][nilai]" value="' + s[5] + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][akhir]" value="' + s[6] + '" class="form-control col-1">';
                inputs += '<input type="text" name="siswa[' + idSiswa + '][predikat]" value="' + s[7] + '" class="form-control col-1">';
            });
            $('#for-upload').html(inputs);

            $.ajax({
                type: "POST",
                url: base_url + 'rapor/importpas',
                data: $(this).serialize(),
                cache: false,
                success: function (data) {
                    console.log(data);
                    swal.fire({
                        title: "Sukses",
                        html: "<b>" + data + "<b> nilai berhasil disimpan",
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    }).then(result => {
                        if (result.value) {
                            window.location.href = base_url + 'rapor/inputpas/' + idMapel + '/' + idKelas
                        }
                    });
                },
                error: function (e) {
                    console.log("error", e.responseText);
                    swal.fire({
                        title: "Error",
                        html: "Gagal menyimpan",
                        icon: "error",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    })
                }
            });
        });

        $('#custom-file').on('change', async function (e) {
            var files = e.target.files || [];
            if (!files.length) return;

            const jsonData = await getDataFromExcel(files[0])
            console.log('parse', jsonData)
        });

        $('#upload').on('click', function (e) {
            const input = $('#custom-file')
            const files = input[0].files
            //console.log('files',files)
            if (!files.length) {
                swal.fire({
                    title: "INFO",
                    text: 'Tidak ada file untuk diupload',
                    icon: "info"
                });
            } else {
                parseFile(files[0]);
            }
        })
    });

    async function downloadTemplate(res) {
        const response = await fetch(base_url + 'uploads/import/format/template_pas_client.xlsx')
        const arrayBuffer = await response.arrayBuffer()
        const excelTemplate = await JsExcelTemplate.fromArrayBuffer(arrayBuffer)
        excelTemplate.set("mapel", namaMapel);
        excelTemplate.set("kelas", namaKelas);
        excelTemplate.set("siswa", res.siswa);

        const blob = await excelTemplate.toBlob()
        saveAs(blob, `Nilai Akhir ${namaMapel} ${namaKelas}.xlsx`);
    }

    async function processDownload(idMapel, idKelas) {
        $.ajax({
            url: base_url + "rapor/downloadtemplatepas/"+idMapel+"/"+idKelas,
            method: "GET",
            success: function (result) {
                console.log("result", result);
                downloadTemplate(result)
            }, error: function (xhr, status, error) {
                console.log("error", xhr.responseText);
                const err = JSON.parse(xhr.responseText)
                swal.fire({
                    title: "Error",
                    text: err,
                    icon: "error"
                });
            }
        });
    }

    function getDataFromExcel(file) {
        return new Promise((resolve, reject) => {
            const wb = new ExcelJS.Workbook();
            const reader = new FileReader()
            reader.onload = async () => {
                try {
                    const buffer = reader.result;
                    wb.xlsx.load(buffer).then(workbook => {
                        let dataFiles = {}
                        workbook.eachSheet((sheet, id) => {
                            let cols = {
                                'name': sheet.name,
                                'header': [],
                                'rows': []
                            }
                            let head = []
                            sheet.eachRow({includeEmpty: true}, (row, rowIndex) => {
                                let obj = {}
                                for (let i = 0; i < row.values.length; i++) {
                                    if (rowIndex === 5) {
                                        let val = row.values[i] ? (row.values[i] === 'N I S N' ? 'NISN' : row.values[i].replaceAll(' ', '').replaceAll('.', '')) : ''
                                        if (val.includes('/')) val = val.split('/')[1]
                                        head.push(val.toLowerCase())
                                        if (row.values[i]) {
                                            let h = {
                                                label: row.values[i],
                                                value: val.toLowerCase(),
                                            }
                                            cols.header.push(h)
                                        }
                                    } else if (rowIndex > 5){
                                        obj[head[i]] = row.values[i]
                                    }
                                }
                                cols.rows.push(obj)
                            })
                            cols.rows = removeEmptyObjects(cols.rows)
                            dataFiles[sheet.name] = cols
                        })
                        resolve(dataFiles)
                    })
                } catch (err) {
                    reject(err)
                }
            }
            reader.onerror = (error) => {
                reject(error)
            };
            reader.readAsArrayBuffer(file)
        });
    }

    function removeEmptyObjects(array) {
        return array.filter(element => {
            delete element.undefined
            return Object.keys(element).length !== 0;
        });
    }

    function uploadNilaiPAS(jsonData) {
        swal.fire({
            title: "Menyimpan nilai PAS",
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });

        let formData = new FormData($('#uploadpas')[0])

        const nilaiSiswa = jsonData.NILAI.rows
        nilaiSiswa.forEach(function (siswa, ind) {
            for (const key in siswa) {
                if (key && key !== 'no') {
                    formData.append('siswa['+ind+']['+key+']', siswa[key])
                }
            }
        })

        $.ajax({
            type: "POST",
            url: base_url + 'rapor/uploadnilaipas/',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function (data) {
                console.log(data);
                window.location.href = base_url + 'rapor/inputpas/' + idMapel + '/' + idKelas
            },
            error: function (e) {
                console.log("error", e.responseText);
                showDangerToast(e.responseText);
            }
        });
    }

    async function parseFile(file) {
        const jsonData = await getDataFromExcel(file)
        console.log('parse', jsonData)
        if (jsonData) {
            swal.fire({
                title: "UPLOAD",
                html: "Nilai yang sudah ada akan ditimpa. Lanjutkan?",
                icon: "warning",
                showCancelButton: 'Batal',
                confirmButtonColor: "#3085d6",
                confirmButtonText: "OK"
            }).then(result => {
                if (result.value) {
                    uploadNilaiPAS(jsonData)
                }
            });
        }
    }

</script>
