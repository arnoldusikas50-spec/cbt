<main class="app-main bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <br>
            <div class="col-lg-12 p-0">
                <div class="alert alert-default-danger shadow align-content-center" role="alert">
                    <strong>Catatan!</strong> untuk import data dari file excel, silahkan download templatenya terlebih
                    dahulu.
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4 col-md-5">
                    <div class="card my-shadow mb-4">
                        <div class="card-header">
                            <h6 class="card-title">Data Siswa</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1 text-bold">Download</p>
                            <div class="row">
                                <?php
                                if (count($kelas) > 0):
                                    foreach ($kelas as $id => $kls): ?>
                                        <div class="col-6 col-sm-4 col-md-6 text-center mb-3">
                                            <div onclick="download(this)" data-kelas="<?= $kls ?>" data-id="<?= $id ?>"
                                                class="card-tools btn-success btn btn-block ms-auto">
                                                <i class="bi bi-download"></i>
                                                <span class="ms-2"><?= $kls ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                else: ?>
                                    <div class="alert alert-default-warning align-content-center" role="alert">Belum ada
                                        data
                                        siswa dan kelas
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p class="mb-1 text-bold">Upload .xlsx</p>
                            <div class="row">
                                <div class="custom-file">
                                    <input type="file" name="upload_file" class="custom-file-input"
                                        accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                        id="input-file-events-excel">
                                    <label class="custom-file-label" for="input-file-events-excel">Update Data
                                        (Excel)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card my-shadow mb-4">
                        <div class="card-header">
                            <h6 class="card-title">Foto Siswa</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1 text-bold">Download</p>
                            <div class="row">
                                <?php
                                if (count($kelas) > 0):
                                    foreach ($kelas as $id => $kls): ?>
                                        <div class="col-6 col-sm-4 col-md-6 text-center mb-3">
                                            <div onclick="downloadWord(this)" data-kelas="<?= $kls ?>" data-id="<?= $id ?>"
                                                class="card-tools btn-success btn btn-block ms-auto">
                                                <i class="bi bi-download"></i>
                                                <span class="ms-2"><?= $kls ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                else: ?>
                                    <div class="alert alert-default-warning align-content-center" role="alert">Belum ada
                                        data
                                        siswa dan kelas
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="mb-1 text-bold">Upload .docx</p>
                            <div class="row">
                                <div class="custom-file">
                                    <input type="file" name="upload_file"
                                        accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                        class="custom-file-input" id="input-file-events-word">
                                    <label class="custom-file-label" for="input-file-events-word">Update Foto
                                        (Word)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-md-7">
                    <div class="card my-shadow mb-4">

                        <?= form_open('', array('id' => 'formUpload')); ?>
                        <div class="card-header">
                            <h6 class="card-title">Preview</h6>
                            <a href="<?= base_url('datasiswa') ?>" type="button"
                                class="btn btn-sm btn-danger float-end ms-2">
                                <i class="bi bi-arrow-left-square"></i><span
                                    class="d-none d-sm-inline-block ms-1">Kembali</span>
                            </a>
                            <button id="submit-excel" name="preview" type="submit"
                                class="btn btn-sm btn-primary card-tools me-2 ms-auto" disabled="disabled">
                                <i class="bi bi-cloud-upload-alt me-2"></i>Upload
                            </button>



                        </div>
                        <?= form_close(); ?>


                        <div class="card-body">
                            <div id="info-error"></div>
                            <div class="table-responsive" id="file-preview">
                                <p class="text-center alert alert-default-warning">Pastikan anda telah mengisi format
                                    yang telah disediakan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row alert alert-default-info" id="download-tbl">
                <div style="display: flex;flex-direction: column">
                    <p style="font-weight: bold; font-size: large" id="doc-title">Update Foto Siswa</p>
                    <ul>
                        <li>
                            <span>Jangan merubah format tabel!</span>
                        </li>
                        <li>
                            <span>Hanya kolom NIS dan FOTO yang bisa diedit, jangan merubah isi kolom lain!</span>
                        </li>
                        <li>
                            <span>Jika NIS kosong, isi NIS dengan angka unik (jangan ada yang sama) </span>
                        </li>
                        <li>
                            <span>Gunakan format EXCEL untuk merubah data siswa selain FOTO</span>
                        </li>
                    </ul>
                </div>
                <table
                    style="width:100%; font-size: 11pt; line-height: 1.3; border: 1px solid black; border-collapse: collapse; border-spacing: 0; page-break-after: always">
                    <thead>
                        <tr id="header-table" data-height="60" style="background-color: lightgrey"></tr>
                    </thead>
                    <tbody id="body-table">
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    </div>

    <script src="<?= base_url() ?>/assets/app/js/mammoth.browser.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/exceljs.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/js-excel-template.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>
    <script>
        const typeImport = '<?= $tipe ?>';
        let dataSiswa = [];
        let keysExcel = 0;
        let isUploadType;

        $(document).ready(function () {
            ajaxcsrf();
            bsCustomFileInput.init()

            $('#input-file-events-excel').on('change', async function (e) {
                var files = e.target.files || [];
                if (!files.length) return;

                $('#file-preview').html('<span class="text-center alert alert-default-warning">Pastikan anda telah mengisi format yang telah disediakan.</span>')

                const jsonData = await getDataFromExcel(files[0])
                console.log('json', jsonData)
                $('#submit-excel').attr('disabled', 'disabled');
                $('#download-tbl').html('')
                dataSiswa = [];
                isUploadType = null;

                let tbl = $(' <table class="mb-4 table table-sm table-striped table-bordered nowrap w-100"></table>')
                createTable(jsonData[jsonData.sheets[0]], tbl)
            });

            $('#input-file-events-word').on('change', async function (e) {
                var files = e.target.files || [];
                if (!files.length) return;

                $('#file-preview').html('<span class="text-center alert alert-default-warning">Pastikan anda telah mengisi format yang telah disediakan.</span>')

                $('#submit-excel').attr('disabled', 'disabled');
                $('#download-tbl').html('')
                dataSiswa = [];
                isUploadType = null;
                parseWordDocxFile(files[0])
            });

            $('#formUpload').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const limit = 900 / keysExcel
                uploadData(limit);
                return false;
            });

        });

        function parseWordDocxFile(file) {
            var showDiv = $('#file-preview')
            $('#info-error').html('')

            Swal.fire({
                title: "Memuat file",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            console.time();
            var reader = new FileReader();

            reader.onloadend = function (event) {
                var arrayBuffer = reader.result;
                mammoth.convertToHtml({ arrayBuffer: arrayBuffer }).then(function (resultObject) {
                    //console.log('file', resultObject)
                    if (resultObject.value === "") {
                        let html = '<div class="alert alert-default-warning" role="alert">' +
                            '<p class="mb-1 text-bold">Warning</p> <ul>';
                        for (const msg of resultObject.messages) {
                            html += '<li>' + msg.message + '</li>'
                        }
                        html += '</ul>' +
                            '<p class="mb-1 text-bold">File Word tidak terbaca,</p>' +
                            '<ul>' +
                            '<li>Edit dan save kembali file word</li>' +
                            '<li>Jika masih tidak terbaca, lakukan <b>Save As</b> untuk membuat salinan</li>' +
                            '</ul></div>';
                        $('#info-error').html(html)
                    }
                    showDiv.html(resultObject.value);
                    setTimeout(function () {
                        showDiv.children().not("table").remove();
                        showDiv.children('table').each(function (i, v) {
                            $(this).addClass('table table-bordered w-100 table-soal');
                            const $trs = $(this).find('tr'), headers = $trs.splice(0, 1); // header rows
                            $trs.each(function (index, tr) {
                                var cekTbl = $(tr).parent().closest('td');
                                if (cekTbl.length === 0) {
                                    var id = $(this).find("td:eq(1)").text().trim();
                                    var nis = $(this).find("td:eq(2)").text().trim();
                                    var nama = $(this).find("td:eq(3)").text().trim();
                                    var foto = $(this).find("td:eq(5)").find('img');
                                    foto.each(function (ind, img) {
                                        $(img).attr('width', '100')
                                    });
                                    if (id && nis) {
                                        let siswa = {};
                                        siswa['id_siswa'] = id;
                                        siswa['nis'] = nis;
                                        siswa['nama'] = nama;
                                        if (foto.length > 0) {
                                            const ftsiswa = $(foto[0]).attr('src')
                                            const ext = ftsiswa.substring("data:image/".length, ftsiswa.indexOf(";base64"))
                                            siswa['foto'] = ftsiswa.split(';base64')[1];
                                            siswa['ext'] = ext;
                                        }
                                        dataSiswa.push(siswa)
                                        $('#submit-excel').removeAttr('disabled');
                                    } else {
                                        $(this).remove();
                                    }
                                }
                            });
                        });
                        swal.close();
                        isUploadType = 'word'
                        keysExcel = 5;
                    }, 500);
                });
            };
            reader.readAsArrayBuffer(file);
        }

        function createTable(list, selector) {
            Swal.fire({
                title: "Memuat file",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let cols = Headers(list.header, selector);
            let len = list.rows.length;
            if (list.rows.length > 50) len = 50;
            for (let i = 0; i < len; i++) {
                let row = $('<tr/>');
                for (let colIndex = 0; colIndex < cols.length; colIndex++) {
                    let val = list.rows[i][cols[colIndex]] || '';
                    if (colIndex === 3 || colIndex > 8) {
                        row.append($('<td/>').html(val));
                    } else {
                        row.append($('<td class="text-center" />').html(val));
                    }
                }
                $(selector).append(row);
            }
            $('#file-preview').prepend(selector)

            if (list.rows.length > 0) {
                $('#submit-excel').removeAttr('disabled');
                const remapped = remapSiswa(list.rows);
                dataSiswa = remapped.data
                keysExcel = remapped.length
                isUploadType = 'excel'

                setTimeout(function () {
                    swal.close();
                }, 1000)

                // cek data required
                /*
                let arrNisnDup = [];
                let arrNisDup = [];
                let arrUserDup = [];
                $.each(data.errors, function (idx, o) {
                    console.log(idx, o);
                    if (o.nisn !== "") arrNisnDup.push("<br />" + idx + ": " +o.nisn);
                    if (o.nis !== "") arrNisDup.push("<br />" + idx + ": " +o.nis);
                    if (o.username !== "") arrUserDup.push("<br />" + idx + ": " +o.username);
                });
                let err = arrNisnDup.length ? `<b>${arrNisnDup.join("' ")}</b>` : '';
                err += arrNisDup.length ? `<br /><b>${arrNisDup.join("' ")}</b>` : '';
                err += arrUserDup.length ? `<br /><b>${arrUserDup.join("' ")}</b>` : '';
                Swal.fire({
                    title: "Gagal",
                    html: 'cek kembali siswa berikut: <br />'+err+'',
                    icon: "error"
                }).then(result => {});
                 */

            } else {
                $('#submit-excel').attr('disabled', 'disabled');
            }
        }

        function Headers(list, selector) {
            let columns = [];
            let header = $('<tr/>');

            for (let i = 0; i < list.length; i++) {
                let row = list[i];
                for (let k in row) {
                    if (k === 'label') {
                        header.append($('<th class="text-center align-middle" />').html(row[k]));
                    } else {
                        if ($.inArray(row[k], columns) == -1) {
                            columns.push(row[k]);
                        }
                    }
                }
            }
            $(selector).append(header);
            return columns;
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
                                if (!dataFiles['sheets']) dataFiles['sheets'] = []
                                dataFiles['sheets'].push(sheet.name)
                                let cols = {
                                    'name': sheet.name,
                                    'header': [],
                                    'rows': []
                                }
                                //let head = []
                                sheet.eachRow({ includeEmpty: true }, (row, rowIndex) => {
                                    let obj = {}
                                    for (let i = 0; i < row.values.length; i++) {
                                        if (rowIndex === 1) {
                                            if (row.values[i]) {
                                                let val = row.values[i] || 'val-' + i;
                                                if (isRichValue(row.values[i])) {
                                                    val = richToString(row.values[i])
                                                }
                                                if (val && val.includes('|')) val = val.split('|')[0]
                                                let h = {
                                                    label: val,
                                                    value: i//key,
                                                }
                                                cols.header.push(h)
                                            }
                                        } else {
                                            let val = row.values[i] || '';
                                            if (isRichValue(row.values[i])) {
                                                val = richToString(row.values[i])
                                            }
                                            if (i === 2 || i === 3 || i === 14 || i === 19 || i === 20 || i === 30 || i === 36 || i === 42) {
                                                val = val.toString().replace("'", "")
                                                console.log('index', i, val)
                                            }
                                            obj[i] = val
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

        function isRichValue(value) {
            return Boolean(value && Array.isArray(value.richText));
        }

        function richToString(rich) {
            return rich.richText.map(({ text }) => text).join('|');
        }

        async function downloadTemplate(kls, res) {
            const response = await fetch(base_url + 'uploads/import/format/format_update_siswa.xlsx')
            const arrayBuffer = await response.arrayBuffer()
            const excelTemplate = await JsExcelTemplate.fromArrayBuffer(arrayBuffer)
            excelTemplate.set("siswa", res.siswa);

            const blob = await excelTemplate.toBlob()
            saveAs(blob, `Update Data Siswa ${kls}.xlsx`);
        }

        function download(btn) {
            const idKls = $(btn).data('id');
            const kls = $(btn).data('kelas')
            //console.log('download', idKls)

            $.ajax({
                url: base_url + "datasiswa/downloaddata/" + idKls,
                method: "GET",
                success: function (result) {
                    console.log("result", result);
                    //createTable(kls, result.data)
                    downloadTemplate(kls, result)
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

        function downloadWord(btn) {
            const idKls = $(btn).data('id');
            const kls = $(btn).data('kelas')
            //console.log('download', idKls)

            $.ajax({
                url: base_url + "datasiswa/downloaddata/" + idKls,
                method: "GET",
                success: function (result) {
                    if (result.siswa) {
                        prosesResult(result.siswa, kls)
                    } else {

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

        function prosesResult(dataSiswa, kelas) {
            Swal.fire({
                text: "Menyiapkan data foto kelas " + kelas + " ....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const headerTbl = [
                { title: "No.", key: 'number', isCenter: true, width: '60px' },
                { title: "ID", key: 'id_siswa', isCenter: true, width: '60px' },
                { title: "NIS", key: "nis", isNumber: true, isCenter: true, width: 'auto' },
                { title: "NAMA SISWA", key: "nama", width: 'auto' },
                { title: "JENIS KELAMIN (L/P)", key: "jenis_kelamin", isCenter: true, width: 'auto' },
                { title: "FOTO", key: "foto", isCenter: true, width: '100px' },
            ]

            let thead = '';
            let tbody = '';
            headerTbl.forEach(function (header) {
                console.log(header)
                thead += '<th style="width:' + header.width + '; border: 1px solid black;border-collapse: collapse;' +
                    ' text-align: center;font-weight: bold;" class="text-center align-middle">' +
                    '<span style="margin: 0; padding: 4px">' + header.title + '</span>' +
                    '</th>';
            })
            dataSiswa.forEach(function (siswa, ind) {
                tbody += '<tr>';
                headerTbl.forEach(function (header) {
                    if (header.key === 'number') {
                        tbody += '<td style="border: 1px solid black;text-align: center;padding: 4px">' +
                            (ind + 1) +
                            '</td>';
                    } else {
                        if (header.key === 'foto') {
                            tbody += '<td id="avatar-' + siswa.nis + '" style="border: 1px solid black;text-align: center;padding: 4px">' +
                                '           <img class="avatar" ' +
                                '                src="' + siswa.foto + '" alt="User Image" style="max-width: 60px; height: auto"> ' +
                                '</td>';
                        } else {
                            const center = header.isCenter ? 'text-align: center;' : ''
                            tbody += '<td style="border: 1px solid black;padding: 4px;' + center + '">' + siswa[header.key] + '</td>';
                        }
                    }
                })
                tbody += '</tr>';
            })

            $('#header-table').html(thead)
            $('#body-table').html(tbody)

            let count = 0
            $(`.avatar`).each(async function (i, v) {
                const loaded = () => {
                    return new Promise((resolve, reject) => {
                        let obj = { pos: i }
                        $(v).on("error", function () {
                            obj.error = true
                            const td = $(this).parent();
                            td.html('<span>&nbsp;&nbsp;</span>')
                            resolve(obj)
                        }).on('load', function () {
                            obj.error = false
                            // ==== Resize gambar dengan rasio asli ====
                            const canvas = document.createElement("canvas");
                            const ctx = canvas.getContext("2d");

                            const targetW = 100; // lebar tetap
                            const ratio = v.naturalHeight / v.naturalWidth;
                            const targetH = Math.round(targetW * ratio);

                            canvas.width = targetW;
                            canvas.height = targetH;

                            ctx.drawImage(v, 0, 0, targetW, targetH);

                            // Replace src dengan hasil resize
                            v.src = canvas.toDataURL("image/png");
                            resolve(obj)
                        })
                    })
                }
                const imgs = await loaded()
                count++
                if (count === dataSiswa.length) {
                    saveWord(kelas)
                }
            });
        }

        function saveWord(kelas) {
            $('#doc-title').text(`Update Foto Siswa Kelas ${kelas}`)
            swal.close();
            var contentDocument = $('#download-tbl').convertToHtmlFile('detail', '');
            var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
            var converted = htmlDocx.asBlob(content, {
                size: 'A4',
                margins: { top: 700, bottom: 700, left: 1000, right: 1000 }
            });
            saveAs(converted, `Foto Siswa Kelas ${kelas}.docx`);
        }

        function onRemoved() {
            $(".dropify-filename-inner").text("");
        }

        const mapping = [
            { "key": "2", "value": "nisn" },
            { "key": "3", "value": "nis" },
            { "key": "4", "value": "nama" },
            { "key": "5", "value": "jenis_kelamin" },
            { "key": "6", "value": "username" },
            { "key": "7", "value": "password" },
            { "key": "8", "value": "kelas_awal" },
            { "key": "9", "value": "tahun_masuk" },
            { "key": "10", "value": "sekolah_asal" },
            { "key": "11", "value": "tempat_lahir" },
            { "key": "12", "value": "tanggal_lahir" },
            { "key": "13", "value": "agama" },
            { "key": "14", "value": "hp" },
            { "key": "15", "value": "email" },
            { "key": "16", "value": "anak_ke" },
            { "key": "17", "value": "status_keluarga" },
            { "key": "18", "value": "alamat" },
            { "key": "19", "value": "rt" },
            { "key": "20", "value": "rw" },
            { "key": "21", "value": "kelurahan" },
            { "key": "22", "value": "kecamatan" },
            { "key": "23", "value": "kabupaten" },
            { "key": "24", "value": "provinsi" },
            { "key": "25", "value": "kode_pos" },
            { "key": "26", "value": "nama_ayah" },
            { "key": "27", "value": "tgl_lahir_ayah" },
            { "key": "28", "value": "pendidikan_ayah" },
            { "key": "29", "value": "pekerjaan_ayah" },
            { "key": "30", "value": "nohp_ayah" },
            { "key": "31", "value": "alamat_ayah" },
            { "key": "32", "value": "nama_ibu" },
            { "key": "33", "value": "tgl_lahir_ibu" },
            { "key": "34", "value": "pendidikan_ibu" },
            { "key": "35", "value": "pekerjaan_ibu" },
            { "key": "36", "value": "nohp_ibu" },
            { "key": "37", "value": "alamat_ibu" },
            { "key": "38", "value": "nama_wali" },
            { "key": "39", "value": "tgl_lahir_wali" },
            { "key": "40", "value": "pendidikan_wali" },
            { "key": "41", "value": "pekerjaan_wali" },
            { "key": "42", "value": "nohp_wali" },
            { "key": "43", "value": "alamat_wali" },
            { "key": "44", "value": "id_siswa" },
        ]

        function remapSiswa(array) {
            let result = [];
            let keys = 0;
            let reqs = [];
            for (const data of array) {
                let siswa = {}
                for (const key in data) {
                    if (key && data[key] !== "") {
                        const map = mapping.find(function (o) {
                            return o.key === key;
                        });
                        if (map) siswa[map.value] = data[key]
                    }
                }

                const keySiswa = Object.keys(siswa);
                const keysLength = keySiswa.length;
                if (keysLength > 0) {
                    if (keySiswa.includes('nama') && keySiswa.includes('nis')
                        && keySiswa.includes('nisn') && keySiswa.includes('jenis_kelamin')
                        && keySiswa.includes('username') && keySiswa.includes('password')) {
                        if (keysLength > keys) keys = keysLength
                        result.push(siswa)
                    } else {
                        reqs.push(siswa)
                    }
                }
            }
            return {
                data: result,
                length: keys,
                error: reqs
            }
        }

        async function uploadData(limit) {
            const pages = Math.ceil(dataSiswa.length / limit)
            console.log('pages', pages)
            if (!page) return

            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            let uploaded = 0;
            for (let i = 0; i < pages; i++) {
                let pagePost = i + 1;
                const siswas = await paginate(pagePost, limit)
                console.log('siswa', siswas)

                let formDataSiswa = new FormData($('#formUpload')[0])
                let url = '';
                if (siswas.length > 0 && isUploadType) {
                    let inds = 0;
                    let ids = [];
                    for (const siswa of siswas) {
                        for (const key in siswa) {
                            ids.push(siswa.id_siswa)
                            formDataSiswa.append(`siswa[${siswa.id_siswa}][${key}]`, siswa[key])
                        }
                        inds++;
                    }
                    if (isUploadType === 'excel') {
                        formDataSiswa.append(`ids`, JSON.stringify(ids))
                        url = "datasiswa/updateall"
                    } else {
                        url = "datasiswa/update_foto"
                    }
                }

                try {
                    const data = await ajaxPost(url, formDataSiswa)
                    uploaded += data.user
                    console.log("response:", uploaded);
                    if (uploaded >= dataSiswa.length) {
                        if (data.status) {
                            Swal.fire({
                                title: "Berhasil",
                                text: "Data siswa berhasil diimpor",
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.history.back();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                html: 'Gagal menyimpan data',
                                icon: "error"
                            });
                        }
                    }
                } catch (error) {
                    console.log("Error occurred: ", error);
                    Swal.fire({
                        title: "Gagal",
                        text: 'Gagal menyimpan data',
                        icon: "error"
                    });
                }
            }
        }

        async function paginate(pageNumber, itemsPerPage) {
            const startIndex = (pageNumber - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            return dataSiswa.slice(startIndex, endIndex);
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
                            resolve(respon);
                        },
                        error: function (xhr, status, error) {
                            reject(error);
                        }
                    });
                }, 500)
            });
        }

    </script>