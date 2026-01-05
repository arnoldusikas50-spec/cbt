<main class="app-main bg-white pt-4">

    <section class="content-header">
        <div class="container-fluid">
            <div class="d-sm-flex justify-content-between align-items-center mb-2">
                <h1><?= $subjudul ?></h1>
                <a href="<?= base_url('datasiswa') ?>" class="btn btn-sm btn-danger">
                    <i class="bi bi-arrow-left-square me-1"></i>
                    <span class="d-none d-sm-inline-block">Kembali</span>
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="col-lg-12 p-0">
                <div class="alert alert-default-danger shadow align-content-center" role="alert">
                    <strong>Catatan!</strong> untuk import data dari file excel, silahkan download templatenya terlebih
                    dahulu.
                </div>
            </div>

            <div class="card my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title">File Excel</h6>
                </div>
                <div class="card-body excel">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-4 mb-3">
                            <?php $url = 'uploads/import/format/format_siswa.xlsx'; ?>
                            <a href="<?= base_url() . $url ?>" class="btn-success btn w-100">
                                <i class="bi bi-download"></i><span class="ms-2">Download Template</span>
                            </a>
                        </div>
                        <div class="col-8 col-md-5 mb-3">
                            <div class="custom-file">
                                <input type="file" name="upload_file" class="custom-file-input"
                                    accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                    id="input-file-events-excel">
                                <label class="custom-file-label" for="input-file-events-excel">Pilih file Excel</label>
                            </div>
                        </div>
                        <div class="col-4 col-md-3 mb-3">
                            <?= form_open('', array('id' => 'formUpload')); ?>
                            <button id="submit-excel" name="preview" type="submit"
                                class="btn btn-primary card-tools w-100 ms-auto" disabled="disabled">
                                <i class="bi bi-upload me-2"></i> Up<span class="d-none d-sm-inline-block">load</span>
                            </button>
                            <?= form_close(); ?>
                        </div>
                    </div>

                    <?php if (isset($kelas)): ?>
                        <div>
                            <p class="text-md"><i>Siswa akan otomatis masuk ke <b>Kelas: <?= $kelas->nama_kelas ?></b></i>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-default-danger d-none" id="error-excel"></div>

                    <p class="mt-3 mb-1 text-bold">Preview</p>
                    <div class="pb-3 border-top">
                        <div class="table-responsive">
                            <table id="tableprev" class="mb-4 table table-sm table-striped table-bordered nowrap w-100">
                            </table>
                            <div class="mx-2 alert alert-default-warning" id="file-preview">Pastikan anda telah mengisi
                                format yang telah disediakan.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/exceljs.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/excel/js-excel-template.min.js"></script>

    <script>
        const idKelasAktif = '<?= isset($kelas) ? $kelas->id_kelas : 0 ?>';
        let dataSiswa = [];
        let keysExcel = 45;
        let errorSiswa = [];

        $(document).ready(function () {
            ajaxcsrf();
            bsCustomFileInput.init()

            $('#input-file-events-excel').on('change', async function (e) {
                var files = e.target.files || [];
                if (!files.length) return;

                $('#tableprev').html('')
                const jsonData = await getDataFromExcel(files[0])
                createTable(jsonData[jsonData.sheets[0]], '#tableprev')
            });

            $('#formUpload').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const limit = 900 / keysExcel
                uploadData(limit);
                return false;
            });

        });

        function onRemoved() {
            $(".dropify-filename-inner").text("");
        }

        function createTable(list, selector) {
            errorSiswa = []
            //console.log('len', list.rows.length)
            if (list.rows.length === 0) {
                $('#submit-excel').attr('disabled', 'disabled');
                Swal.fire({
                    title: "Tidak ada data",
                    text: "File tidak berisi data siswa",
                    icon: "warning"
                });

                return;
            }
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

            const valRequired = [2, 3, 4, 5, 6, 7]
            for (let i = 0; i < len; i++) {
                let novalid = 0;
                for (const number of valRequired) {
                    if (!list.rows[i][number]) {
                        novalid++
                    }
                }

                if (novalid === 0) {
                    let row = $('<tr/>')
                    for (let colIndex = 0; colIndex < cols.cols.length; colIndex++) {
                        let val = list.rows[i][cols.cols[colIndex]] || "";
                        //console.log('col', colIndex, val)
                        if (colIndex === 3 || colIndex > 8) {
                            row.append($('<td/>').html(val));
                        } else {
                            let isValid = true;
                            if (colIndex === 5 && val.length < 5) {
                                isValid = false;
                                //console.log(list.rows[i][4], val)
                                errorSiswa.push({
                                    'nama': list.rows[i][4],
                                    'username': val,
                                })
                            } else if (colIndex === 6 && val.length < 5) {
                                isValid = false;
                                //console.log(list.rows[i][4], val)
                                errorSiswa.push({
                                    'nama': list.rows[i][4],
                                    'password': val
                                })
                            }
                            row.append($(`<td class="text-center ${isValid ? '' : 'bg-danger'}" />`).html(val));
                        }
                    }
                    $(selector).append(row);
                }
            }

            dataSiswa = [] //new FormData($('#formUpload')[0])
            if (list.rows.length > 0) {
                console.log('siswa', list.rows)
                const remapped = remapSiswa(list.rows);
                dataSiswa = remapped.data
                dataSiswa.sort((a, b) => a.nama?.localeCompare(b.nama, undefined, { numeric: true }))
                console.log('remap', remapped)
                if (remapped.error.length > 0) {
                    let errorInfo = `WARNING`
                    errorInfo += `<ul>
                        <li>Total data dari file Excel: <b>${list.rows.length}</b></li>
                        <li>Siswa yang akan diupload: <b>${remapped.data.length}</b></li>
                        <li>Siswa dihilangkan karena tidak lengkap: <b>${remapped.error.length}</b></li>
                        <li>Data tidak valid: <b>${list.rows.length - remapped.data.length}</b></li>
                    </ul>`;
                    $('#error-excel').html(errorInfo).removeClass('d-none')
                }
                if (dataSiswa.length > 0) {
                    $(selector).prepend(cols.header);
                    $('#submit-excel').removeAttr('disabled');
                }

                setTimeout(function () {
                    swal.close();
                }, 500)
            } else {
                $('#submit-excel').attr('disabled', 'disabled');
            }
        }

        function Headers(list, selector) {
            let columns = [];
            let title = $('<tr/>');

            for (let i = 0; i < list.length; i++) {
                let row = list[i];
                for (let k in row) {
                    if (k === 'label') {
                        title.append($('<th class="text-center align-middle" />').html(row[k]));
                    } else {
                        if ($.inArray(row[k], columns) == -1) {
                            columns.push(row[k]);
                        }
                    }
                }
            }
            //$(selector).append(header);
            return {
                cols: columns,
                header: title
            }
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
                                                console.log('val', val)
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
                                            } else if (i === 13) {
                                                val = capitalizeFirstLetter(val);
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

        const mapping = [
            { "key": "2", "value": "nisn", "req": true, "min": 6, "max": 30 },
            { "key": "3", "value": "nis", "req": true, "min": 6, "max": 20 },
            { "key": "4", "value": "nama", "req": true, "min": 6, "max": 30 },
            { "key": "5", "value": "jenis_kelamin", "req": true },
            { "key": "6", "value": "username", "req": true, "min": 5 },
            { "key": "7", "value": "password", "req": true, "min": 5 },
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
                    const map = mapping.find(function (o) {
                        return o.key === key;
                    });
                    if (map) {
                        siswa[map.value] = data[key]
                    }
                }

                console.log('keys', siswa)
                const keySiswa = Object.keys(siswa);
                const keysLength = keySiswa.length;
                if (keysLength > 0) {
                    if (keySiswa.includes('nama') && keySiswa.includes('nis')
                        && keySiswa.includes('nisn') && keySiswa.includes('jenis_kelamin')
                        && keySiswa.includes('username') && keySiswa.includes('password')) {
                        if (siswa.nisn.length > 4 && siswa.nis.length > 4 && siswa.nama.length > 0
                            || siswa.username.length > 0 || siswa.password.length > 0) {
                            if (keysLength > keys) keys = keysLength
                            siswa['foto'] = siswa.nis || '00'
                            siswa['uid'] = generateUUID()
                            keys += 2;
                            result.push(siswa)
                        }
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
            console.log('pages', limit, pages, keysExcel)
            if (pages === 0) return

            if (errorSiswa.length > 0) {
                Swal.fire({
                    title: "Gagal",
                    text: `ada ${errorSiswa.length} Username dan Password kurang dari 5 karakter`,
                    icon: "error"
                });
                return
            }
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
                console.log('paginate', siswas)
                let formDataSiswa = new FormData($('#formUpload')[0])
                formDataSiswa.append('id_kelas', idKelasAktif)
                const url = "datasiswa/do_import";
                if (siswas.length > 0) {
                    let inds = 0;
                    let ids = [];
                    for (const siswa of siswas) {
                        for (const key in siswa) {
                            ids.push(siswa.id_siswa)
                            formDataSiswa.append(`siswa[${inds}][${key}]`, siswa[key])
                        }
                        inds++;
                    }
                }

                try {
                    const data = await ajaxPost(url, formDataSiswa)
                    uploaded += data.user
                    console.log("response:", data);
                    if (data.status) {
                        if (uploaded >= dataSiswa.length) {
                            Swal.fire({
                                title: "Berhasil",
                                text: "Data siswa berhasil diimpor",
                                icon: "success"
                            }).then(result => {
                                if (result.value) {
                                    window.history.back();
                                }
                            });
                        }
                    } else {
                        console.log(data.errors);
                        let er = '<table class="table table-sm table-borderless">';
                        if (data.wrong) {
                            er += '<tr><td>Anda menggunakan Excel untuk update data siswa</td></tr>'
                        } else {
                            er += '<tr><td colspan="2" class="text-start">cek kembali nomor berikut:</td></tr>'
                            for (const num in data.errors) {
                                const o = data.errors[num]
                                er += "<tr><td class='text-sm text-center'>" + num + ".</td><td class='text-start text-sm'>";
                                if (o.nama !== "") er += " " + o.nama
                                if (o.nisn !== "") er += " " + o.nisn
                                if (o.nis !== "") er += " " + o.nis
                                if (o.username !== "") er += " " + o.username
                                if (o.password !== "") er += " " + o.password
                                er += "</td></tr>"
                            }
                        }
                        er += '</table>';
                        Swal.fire({
                            title: "Gagal",
                            html: er,
                            icon: "error"
                        }).then(result => { });
                        break;
                    }
                } catch (error) {
                    console.log("Error occurred: ", error);
                    Swal.fire({
                        title: "Gagal",
                        text: 'Gagal menyimpan data',
                        icon: "error"
                    });
                    break
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

        function generateUUID() {
            return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4))).toString(16)
            );
        }

        function capitalizeFirstLetter(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.toLowerCase().slice(1);
        }

    </script>