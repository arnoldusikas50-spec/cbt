<main class="app-main bg-white">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-9">
                    <h1><?= $subjudul ?></h1>
                </div>
                <div class="col-3">
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-end">
                        <i class="bi bi-arrow-left-circle"></i><span
                            class="d-none d-sm-inline-block ms-1">Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="col-lg-12 p-0">
                <div class="alert alert-danger shadow align-content-center" role="alert">
                    <strong>Catatan!</strong> untuk import data dari file excel/word, silahkan download templatenya
                    terlebih dahulu.
                </div>
            </div>

            <div class="card my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><b>Upload Soal <?= $bank->nama_mapel . " kelas " . $bank->bank_level ?></b>
                    </h6>
                    <input type="hidden" name="bank_id" id="formInput" class="form-control">
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="<?= base_url('uploads/import/format/format_soal_akm.docx') ?>"
                                class="btn-success btn mb-1 w-100"
                                download="Template Soal <?= $bank->nama_mapel ?> <?= $bank->bank_kode ?>">
                                <i class="bi bi-download"></i><span class="ms-2">Download Template</span>
                            </a>
                        </div>
                        <div class="col-md-8 mb-3">
                            <div class="row">
                                <div class="col-8">
                                    <?= form_open_multipart('', array('id' => 'formPreviewWord')); ?>
                                    <div class="custom-file">
                                        <input type="file" name="upload_file" class="custom-file-input input-sm"
                                            id="upload-word" accept=".doc, .docx">
                                        <label class="custom-file-label" for="upload-word">Upload Soal</label>
                                    </div>
                                    <?= form_close(); ?>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-primary w-100" onclick="getData()">
                                        <i class="bi bi-cloud-upload me-2"></i>Upload
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="file-preview" class="table-responsive" data-id="<?= $this->security->get_csrf_hash() ?>"
                        data-name="<?= $this->security->get_csrf_token_name() ?>">
                        <div class="alert alert-default-info align-content-center" role="alert">
                            Sebelum upload, pastikan anda telah mengisi format yang telah disediakan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>
    <?= form_open('create', array('id' => 'grouping')) ?>
    <div id="input-soal-word" class="d-none"></div>
    <?= form_close() ?>

    <script type="text/javascript"
        src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson-cell.js"></script>
    <script type="text/javascript"
        src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson-row.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/plugins/jquery-table2json/src/tabletojson.js"></script>
    <script src="<?= base_url() ?>/assets/app/js/mammoth.browser.min.js"></script>
    <script src="<?= base_url() ?>/assets/app/js/docx.js"></script>

    <script>
        const bank_id = '<?= $bank->id_bank ?>';
        const jenjang = '<?= $setting->jenjang ?>';
        $(function () {
            bsCustomFileInput.init();
        });

        let pg;
        let essai;
        let filename = '';
        $(document).ready(function () {
            ajaxcsrf();

            $('#upload-word').on('change', function (e) {
                //var form = new FormData($("#formPreviewWord")[0]);
                filename = $("#formPreviewWord").text();
                //preview(base_url + 'cbtbanksoal/previewword/'+bank_id, form, filename);
                parseWordDocxFile(e.target.files, '#file-preview');
            });
        });

        async function parseWordDocxFile(inputElement, showDiv) {
            const files = inputElement || [];
            if (!files.length) return;

            Swal.fire({
                title: "Mempersiapkan upload",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,

                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const file = files[0];

            const arrayBuffer = await file.arrayBuffer();
            const zip = await JSZip.loadAsync(arrayBuffer);

            // 1. Bersihkan document.xml
            const documentXmlPath = "word/document.xml";
            let documentXml = await zip.file(documentXmlPath).async("string");
            documentXml = documentXml
                .replace(/<w:altChunk[^>]*\/>/g, "")
                .replace(/<w:altChunk[\s\S]*?<\/w:altChunk>/g, "");
            zip.file(documentXmlPath, documentXml);

            // 2. Bersihkan document.xml.rels
            const relsPath = "word/_rels/document.xml.rels";
            if (zip.file(relsPath)) {
                let relsXml = await zip.file(relsPath).async("string");

                // Hapus Relationship ke altChunk
                relsXml = relsXml.replace(/<Relationship[^>]+Type="[^"]*aFChunk[^"]*"[^>]*\/>/g, "");

                zip.file(relsPath, relsXml);
            }

            // 3. Generate ulang file docx
            const cleanedDocxBlob = await zip.generateAsync({ type: "blob" });

            console.time();
            //var reader = new FileReader();

            const options = {
                styleMap: ["u => u", "strike => del"]
            };
            mammoth.convertToHtml({ arrayBuffer: await cleanedDocxBlob.arrayBuffer() }, options).then(function (resultObject) {
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
                        '<li>Refresh halaman ini terlebih dahulu, lalu upload kembali</li>' +
                        '</ul></div>';
                    $(showDiv).html(html)
                    swal.close();
                    return
                }

                $(showDiv).html(resultObject.value);
                setTimeout(function () {
                    let jmlSoalFix = 0
                    let jmlSoal = 0
                    let jmlDihapus = 0
                    let tanpaJns = 0;

                    $(showDiv).children().not("table").remove();
                    $(showDiv).children('table').each(function (i, v) {
                        console.log('tbls', i);
                        $(this).addClass('table table-bordered w-100 table-soal');

                        const $trs = $(this).find('tr'), headers = $trs.splice(0, 1); // header rows

                        let indexTr = 0;
                        let hide = false;
                        $trs.each(function (index, tr) {
                            let jawab;
                            const cekTbl = $(tr).parent().closest('td');
                            if (cekTbl.length === 0) {
                                $(this).addClass('tr-soal');
                                const $tds = $(this).find('td');
                                $tds.each(function (it, td) {
                                    const tdlength = $(td).closest('tr').hasClass('tr-soal');
                                    if (tdlength) $(td).addClass('td-soal');
                                    $(td).find('table').addClass('table table-bordered table-inner');
                                });

                                const rows = $(this).find("td:eq(0)").attr('rowspan');
                                const nomor = $(this).find("td:eq(0)").text().trim();
                                const soal = $(this).find("td:eq(1)").text().trim();
                                const jenis = $(this).find("td:eq(2)").text().trim();
                                if (rows != null) {
                                    //console.log(`jns:`+jenis,'nomor:' + nomor, 'soal:' + soal);
                                    if (nomor && soal && isNaN(parseInt(jenis))) {
                                        tanpaJns++;
                                    }
                                    const imgSoal = $(this).find("td:eq(1)").find('img').length
                                    indexTr = 1;
                                    if (jenis === '1' || jenis === '2') {
                                        jmlSoal++;
                                        const noSoal = soal === '' && imgSoal === 0
                                        const jawaban = $(this).find("td:eq(4)").text().trim();
                                        //console.log('soal', jenis, nomor, imgSoal)
                                        hide = jawaban.length === 0 && noSoal;
                                        if (hide) {
                                            $(this).remove();
                                            jmlDihapus++
                                        } else {
                                            jmlSoalFix++
                                        }
                                    } else if (jenis === '3') {
                                        jmlSoal++;
                                        const imgBaris = $(this).find("td:eq(4)").find('img').length
                                        const imgkolom = $(this).find("td:eq(6)").find('img').length

                                        const baris = $(this).find("td:eq(4)").text().trim();
                                        const kolom = $(this).find("td:eq(6)").text().trim();
                                        const jawabKode = $(this).find("td:eq(7)").text().trim();
                                        const jawab = $(this).find("td:eq(8)").text().trim();

                                        const noSoal = soal === '' && imgSoal === 0
                                        const noBaris = baris === '' && imgBaris === 0
                                        const noKolom = kolom === '' && imgkolom === 0
                                        const noJawaban = jawab === '' || jawabKode === ''

                                        hide = (noBaris && noKolom && noSoal) || noJawaban;
                                        if (hide) {
                                            $(this).remove();
                                            jmlDihapus++
                                        } else {
                                            jmlSoalFix++
                                        }
                                    }
                                } else {
                                    indexTr += 1;
                                    if (jenis === '4' || jenis === '5') {
                                        jmlSoal++;
                                        const imgSoal = $(this).find("td:eq(1)").find('img').length
                                        jawab = $(this).find("td:eq(3)").text().trim();
                                        const noSoal = soal === '' && imgSoal === 0
                                        hide = jawab === '' && noSoal;
                                        if (hide) {
                                            $(this).remove();
                                            jmlDihapus++
                                        } else {
                                            jmlSoalFix++
                                        }
                                    }
                                }
                                if (indexTr > 1 && hide) {
                                    $(this).remove();
                                }
                            }
                        });

                        $(this).find('p').each(function () {
                            const arabic = /[\u0600-\u06FF]/;
                            const string = $(this).text();
                            if (arabic.test(string)) {
                                $(this).css({
                                    'font-size': '16pt',
                                    'font-family': 'Calibri',
                                    'direction': 'rtl',
                                    'text-align': 'justify'
                                });
                            }
                        });
                    });

                    const attrId = document.getElementById("formInput");
                    attrId.setAttribute("value", bank_id);
                    swal.close();

                    setTimeout(function () {
                        //const rows = document.querySelectorAll("table:not(td table) tr");
                        const rows = document.querySelectorAll("table tr.tr-soal");

                        rows.forEach((row, index) => {
                            const td = row.querySelector("td[rowspan]");
                            if (td) {
                                const rowspan = parseInt(td.getAttribute("rowspan"));
                                const groupedRows = [];

                                for (let i = 0; i < rowspan; i++) {
                                    groupedRows.push(rows[index + i]);
                                }

                                let kunci = [];
                                let nomor = '0';
                                let jenis = null;

                                groupedRows.forEach((gr, index) => {
                                    const tds = gr.querySelectorAll("td.td-soal");
                                    if (index === 0) {
                                        nomor = tds[0]?.textContent.trim(); // Kolom nomor
                                        jenis = tds[2]?.textContent.trim(); // Kolom jenis
                                    }

                                    if (jenis === '1' || jenis === '2') {
                                        // Ambil kunci dari kolom terakhir
                                        const indexKunci = index === 0 ? 3 : 0
                                        const indexJawab = index === 0 ? 5 : 2
                                        //const indexKunci = index === 0 ? 5 : 2;
                                        const pilihan = tds[indexKunci]?.textContent.trim(); // Kolom huruf: A, B, C, D, E
                                        const indikator = tds[indexJawab]?.textContent.trim(); // Kolom terakhir (v atau kosong)

                                        if (indikator.toLowerCase() === "v") {
                                            kunci.push(pilihan);
                                        }
                                    } else if (jenis === '3') {
                                        // Ambil kunci dari kolom terakhir
                                        const indexKunci = index === 0 ? 7 : 4
                                        const indexJawab = index === 0 ? 8 : 5
                                        //const indexKunci = index === 0 ? 7 : 4;

                                        const pilihan = tds[indexKunci]?.textContent.trim();
                                        const indikator = tds[indexJawab]?.textContent.trim();

                                        if (pilihan && indikator) {
                                            kunci.push({ 'baris': pilihan, 'kolom': indikator });
                                        } else if (pilihan && !indikator || !pilihan && indikator) {
                                            kunci = [];
                                        }
                                    }
                                });

                                if (kunci.length === 0) {
                                    groupedRows.forEach(gr => gr.remove());
                                    jmlDihapus++
                                    //console.log(`Soal tipe ${jenis} nomor ${nomor} dihapus karena tidak ada kunci jawaban.`);
                                }
                            }
                        });

                        $(showDiv).prepend(`
                        <div class="alert alert-default-danger align-content-center" role="alert">
                            <ul>
                                <li>Total Soal komplit dan siap diupload: <b>${jmlSoalFix}</b></li>
                                <li>Soal yang tidak lengkap dan dihapus: <b>${jmlDihapus}</b></li>
                                <li>Soal tanpa jenis soal (kolom jenis kosong/tidak valid): <b>${tanpaJns}</b></li>
                            </ul>
                        </div>
                    `);

                    }, 1000)
                }, 500);
            });
        }

        function getData() {
            if (filename === '') {
                showDangerToast('Pilih File dulu');
                return;
            }
            Swal.fire({
                title: "Import soal ke database",
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            var tempJenis = "0";
            var index = 0;
            var $tables = $('.table-soal');
            var tbls = {};

            let formData = new FormData($('#grouping')[0])
            formData.append("id_bank", bank_id)

            $.each($tables, function (i, row) {
                var tbl = $(this).find('.tr-soal').get().map(function (row) {
                    return $(row).find('.td-soal').get().map(function (cell) {
                        return $(cell).html();
                    });
                });
                if (tbl[0] === undefined) return;
                //console.log('tabel1', tbl[0])
                var rowSoal = 0;
                if (i === index) {
                    var p = tbl?.[0]?.[2];
                    tempJenis = $(p).text().trim();
                    rowSoal = 0;

                    if (isNaN(parseInt(tempJenis))) {
                        p = tbl?.[1]?.[2];
                        tempJenis = $(p).text().trim();
                        rowSoal = 1;

                        if (isNaN(parseInt(tempJenis))) {
                            p = tbl?.[2]?.[2];
                            tempJenis = $(p).text().trim();
                            rowSoal = 2;
                        }
                    }
                }

                var myRows = {};
                var no1 = 0;
                var no3 = 0;
                for (let j = rowSoal; j < tbl.length; j++) {
                    var items = tbl[j];
                    myRows[j] = {};
                    if (tempJenis == "1" || tempJenis == "2") {
                        if (items.length === 6) {
                            var no = $(items[0]).text().trim();
                            no1 = no;
                            const imgCheck = $(items[1]).find('img').length > 0
                            var soalCek = $(items[1]).text().trim();
                            var ops = $(items[3]).text().trim();
                            var knc = $(items[5]).text().trim();

                            if (soalCek != "" || imgCheck) {
                                formData.append('soal[' + tempJenis + ']' + '[' + no + '][soal]', encodeURIComponent(removeUrl(items[1])));
                                formData.append('soal[' + tempJenis + ']' + '[' + no + '][opsi][' + ops + ']', encodeURIComponent(removeUrl(items[4])));

                                if (knc != "" && knc.toUpperCase() == "V") {
                                    formData.append('soal[' + tempJenis + ']' + '[' + no + '][kunci][]', ops);
                                }
                            }
                        } else {
                            myRows[j]['NO'] = no1;
                            myRows[j]['SOAL'] = '';
                            myRows[j]['JENIS'] = tempJenis;
                            var ops1 = $(items[0]).text().trim();

                            formData.append('soal[' + tempJenis + ']' + '[' + no1 + '][opsi][' + ops1 + ']', encodeURIComponent(removeUrl(items[1])));

                            var knc1 = $(items[2]).text().trim();
                            if (knc1 != "" && knc1.toUpperCase() == "V") {
                                formData.append('soal[' + tempJenis + ']' + '[' + no1 + '][kunci][]', ops1);
                            }
                        }
                    } else if (tempJenis == '3') {
                        if (items.length === 9) {
                            no3 = $(items[0]).text().trim(); // nomor

                            var kd_baris = $(items[3]).text().trim().toUpperCase();
                            var kd_kolom = $(items[5]).text().trim().toUpperCase();
                            var kd_kunci = $(items[7]).text().trim().toUpperCase();

                            formData.append('soal[' + tempJenis + ']' + '[' + no3 + '][soal]', encodeURIComponent(removeUrl(items[1])));

                            const imgBrs = $(items[4]).find('img').length > 0
                            var brs = $(items[4]).text().trim();
                            if (brs != "" || imgBrs) {
                                formData.append('soal[' + tempJenis + ']' + '[' + no3 + '][baris][' + kd_baris + ']', encodeURIComponent(removeUrl(items[4])));
                            }

                            const imgKlm = $(items[6]).find('img').length > 0
                            var klm = $(items[6]).text().trim();
                            if (klm != "" || imgKlm) {
                                formData.append('soal[' + tempJenis + ']' + '[' + no3 + '][kolom][' + kd_kolom + ']', encodeURIComponent(removeUrl(items[6])));
                            }

                            var kncs = $(items[8]).text().trim().toUpperCase();
                            if (kncs != "") {
                                formData.append('soal[' + tempJenis + ']' + '[' + no3 + '][kunci][' + kd_kunci + ']', kncs);
                            }
                        } else {
                            var kd_baris1 = $(items[0]).text().trim().toUpperCase();
                            var kd_kolom1 = $(items[2]).text().trim().toUpperCase();
                            var kd_kunci1 = $(items[4]).text().trim().toUpperCase();

                            const imgBrs1 = $(items[1]).find('img').length > 0
                            var brs1 = $(items[1]).text().trim();
                            if (brs1 != "" || imgBrs1) {
                                formData.append('soal[' + tempJenis + ']' + '[' + no3 + '][baris][' + kd_baris1 + ']', encodeURIComponent(removeUrl(items[1])));
                            }

                            const imgKlm1 = $(items[3]).find('img').length > 0
                            var klm1 = $(items[3]).text().trim();
                            if (klm1 != "" || imgKlm1) {
                                formData.append('soal[' + tempJenis + ']' + '[' + no3 + '][kolom][' + kd_kolom1 + ']', encodeURIComponent(removeUrl(items[3])));
                            }

                            var kncs1 = $(items[5]).text().trim().toUpperCase();
                            if (kncs1 != "") {
                                formData.append('soal[' + tempJenis + ']' + '[' + no3 + '][kunci][' + kd_kunci1 + ']', kncs1);
                            }
                        }
                    } else {
                        var no4 = $(items[0]).text().trim();
                        formData.append('soal[' + tempJenis + ']' + '[' + no4 + '][soal]', encodeURIComponent(removeUrl(items[1])));
                        if (tempJenis == '4') {
                            formData.append('soal[' + tempJenis + ']' + '[' + no4 + '][kunci]', $(items[3]).text().trim());
                        } else {
                            formData.append('soal[' + tempJenis + ']' + '[' + no4 + '][kunci]', encodeURIComponent(removeUrl(items[3])));
                        }
                    }
                    tbls[tempJenis] = myRows;
                }
                index++;
            });

            setTimeout(function () {
                //console.log('old_json', datapost);
                //console.log('form', Object.fromEntries(formData))
                sendData(formData);
            }, 500);
        }

        function sendData(datapost) {
            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: base_url + "cbtbanksoal/uploadsoal",
                method: "POST",
                processData: false,
                contentType: false,
                data: datapost,
                success: function (result) {
                    console.log("result", result);
                    Swal.fire({
                        title: "Sukses",
                        html: "Total isi soal: <b>" + result.total + "</b><br>Total soal diimport: <b>" + result.insert + "</b>",
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                    }).then(result => {
                        if (result.value) {
                            window.history.back()
                            //window.location.href = base_url + 'cbtbanksoal';
                        }
                    });
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

        function appendFileAndSubmit(id, imageURL, fileName) {
            var datapost = $('#grouping').serialize() + "&src=" + imageURL.replace(/\+/g, "%2B") + "&name=" + fileName;
            $.ajax({
                url: base_url + "cbtbanksoal/uploadsoalimage",
                data: datapost,
                type: "POST",
                error: function (err) {
                    console.error(err);
                },
                success: function (data) {
                    $(id).attr('src', base_url + data.src);
                    //$(id).attr('data-imgsrc', data.src);
                    //console.log(data);
                },
                complete: function () {
                    //console.log("Request finished.");
                }
            });
        }

        function removeUrl(str) {
            return str.replace(base_url, '');
        }

    </script>