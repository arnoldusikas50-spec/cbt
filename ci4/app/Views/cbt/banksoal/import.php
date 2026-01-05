<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= esc($judul) ?></h1>
                </div>
                <div class="col-sm-6 text-end">
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger shadow-sm">
                        <i class="bi bi-arrow-left-circle me-1"></i> Kembali
                    </button>
                    <ol class="breadcrumb float-sm-right mt-2">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('cbtbanksoal') ?>">Bank Soal</a></li>
                        <li class="breadcrumb-item active">Import</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-default-info border-info my-shadow-sm mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Catatan!</strong> Untuk import data dari file Word (.docx), silakan download templatenya
                        terlebih dahulu. Pastikan format tabel tidak berubah.
                    </div>
                </div>
            </div>

            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title text-bold">
                        <i class="bi bi-cloud-upload me-2"></i>
                        Upload Soal: <?= esc($bank->nama_mapel) ?> (Level <?= esc($bank->bank_level) ?>)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4">
                            <a href="<?= base_url('uploads/import/format/format_soal_akm.docx') ?>"
                                class="btn btn-outline-success w-100 py-2 shadow-sm rounded-3"
                                download="Template Soal <?= esc($bank->nama_mapel) ?>.docx">
                                <i class="bi bi-download me-2"></i> Download Template
                            </a>
                        </div>
                        <div class="col-md-5">
                            <form id="formPreviewWord">
                                <div class="input-group">
                                    <input type="file" name="upload_file" class="form-control" id="upload-word"
                                        accept=".doc, .docx">
                                    <label class="input-group-text bg-light" for="upload-word"><i
                                            class="bi bi-file-earmark-word"></i></label>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100 py-2 shadow-sm rounded-3"
                                onclick="getData()">
                                <i class="bi bi-cloud-arrow-up me-2"></i> Upload Ke Database
                            </button>
                        </div>
                    </div>

                    <div id="file-preview" class="mt-4 p-3 border rounded-3 bg-light min-vh-50 overflow-auto">
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark-text display-1 d-block mb-3 opacity-25"></i>
                            <p>Belum ada file yang dipilih untuk dipreview.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <form id="grouping" class="d-none">
        <?= csrf_field() ?>
        <input type="hidden" name="id_bank" value="<?= $bank->id_bank ?>">
    </form>
</div>

<!-- Dependencies -->
<script src="<?= base_url('assets/plugins/jquery-table2json/src/tabletojson-cell.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-table2json/src/tabletojson-row.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-table2json/src/tabletojson.js') ?>"></script>
<script src="<?= base_url('assets/app/js/mammoth.browser.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/docx.js') ?>"></script>
<!-- JSZip is also needed for the cleanedDocxBlob logic -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
    const bank_id = '<?= $bank->id_bank ?>';
    let filename = '';

    $(document).ready(function () {
        $('#upload-word').on('change', function (e) {
            filename = $(this).val();
            if (this.files && this.files.length > 0) {
                parseWordDocxFile(this.files, '#file-preview');
            }
        });
    });

    async function parseWordDocxFile(files, showDiv) {
        if (!files.length) return;

        Swal.fire({
            title: 'Mempersiapkan Preview',
            text: 'Sedang membaca file Word...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const file = files[0];
            const arrayBuffer = await file.arrayBuffer();
            const zip = await JSZip.loadAsync(arrayBuffer);

            // Clean document.xml from altChunks that might break mammoth
            const documentXmlPath = "word/document.xml";
            let documentXml = await zip.file(documentXmlPath).async("string");
            documentXml = documentXml
                .replace(/<w:altChunk[^>]*\/>/g, "")
                .replace(/<w:altChunk[\s\S]*?<\/w:altChunk>/g, "");
            zip.file(documentXmlPath, documentXml);

            const relsPath = "word/_rels/document.xml.rels";
            if (zip.file(relsPath)) {
                let relsXml = await zip.file(relsPath).async("string");
                relsXml = relsXml.replace(/<Relationship[^>]+Type="[^"]*aFChunk[^"]*"[^>]*\/>/g, "");
                zip.file(relsPath, relsXml);
            }

            const cleanedDocxBlob = await zip.generateAsync({ type: "blob" });
            const options = { styleMap: ["u => u", "strike => del"] };

            mammoth.convertToHtml({ arrayBuffer: await cleanedDocxBlob.arrayBuffer() }, options).then(function (result) {
                if (result.value === "") {
                    $(showDiv).html('<div class="alert alert-warning">File Word tidak terbaca atau kosong. Silakan periksa kembali file Anda.</div>');
                    Swal.close();
                    return;
                }

                $(showDiv).html(result.value);

                // Process preview content
                setTimeout(function () {
                    let jmlSoalFix = 0;
                    let jmlSoal = 0;
                    let jmlDihapus = 0;
                    let tanpaJns = 0;

                    $(showDiv).children().not("table").remove();
                    $(showDiv).children('table').each(function () {
                        $(this).addClass('table table-bordered w-100 table-soal bg-white');
                        const $trs = $(this).find('tr');

                        $trs.each(function () {
                            const cekTbl = $(this).parent().closest('td');
                            if (cekTbl.length === 0) {
                                $(this).addClass('tr-soal');
                                $(this).find('td').addClass('td-soal');
                            }
                        });
                    });

                    // Analyze counts (simplified version of the original logic)
                    $('.tr-soal').each(function () {
                        const $tds = $(this).find('td');
                        if ($tds.length >= 3) {
                            const nomor = $tds.eq(0).text().trim();
                            const jenis = $tds.eq(2).text().trim();
                            if (nomor && !isNaN(parseInt(nomor))) {
                                if (jenis && !isNaN(parseInt(jenis))) jmlSoalFix++;
                                else tanpaJns++;
                            }
                        }
                    });

                    $(showDiv).prepend(`
                        <div class="alert alert-light border shadow-sm mb-3">
                            <ul class="mb-0 small">
                                <li>Estimasi soal terbaca: <b>${jmlSoalFix}</b></li>
                                <li>Soal tanpa jenis (mungkin header atau tidak valid): <b>${tanpaJns}</b></li>
                            </ul>
                        </div>
                    `);
                    Swal.close();
                }, 800);
            });
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Gagal membaca file: ' + error.message, 'error');
        }
    }

    function getData() {
        if (filename === '') {
            Swal.fire('Info', 'Pilih file Word terlebih dahulu.', 'info');
            return;
        }

        Swal.fire({
            title: 'Mengimpor Soal',
            text: 'Sedang memproses dan mengunggah ke database...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const $tables = $('.table-soal');
        const formData = new FormData($('#grouping')[0]);

        $tables.each(function (i) {
            const tbl = $(this).find('.tr-soal').get().map(function (row) {
                return $(row).find('.td-soal').get().map(function (cell) {
                    return $(cell).html();
                });
            });

            if (!tbl || tbl.length === 0) return;

            // Determine question type from the table (typically in the header or first data row)
            let tempJenis = "";
            for (let scan = 0; scan < Math.min(3, tbl.length); scan++) {
                let j = $(tbl[scan][2] || '').text().trim();
                if (!isNaN(parseInt(j))) {
                    tempJenis = j;
                    break;
                }
            }

            if (tempJenis === "") return;

            let no3 = 0; // for matching
            for (let j = 0; j < tbl.length; j++) {
                const items = tbl[j];
                const cleanItems = items.map(it => $(it).text().trim());

                if (tempJenis == "1" || tempJenis == "2") {
                    if (items.length === 6) {
                        const no = cleanItems[0];
                        const soal = items[1];
                        const ops = cleanItems[3];
                        const jaw = items[4];
                        const knc = cleanItems[5];

                        if (soal || $(items[1]).find('img').length > 0) {
                            formData.append(`soal[${tempJenis}][${no}][soal]`, encodeURIComponent(removeUrl(soal)));
                            formData.append(`soal[${tempJenis}][${no}][opsi][${ops}]`, encodeURIComponent(removeUrl(jaw)));
                            if (knc.toUpperCase() === "V") {
                                formData.append(`soal[${tempJenis}][${no}][kunci][]`, ops);
                            }
                        }
                    } else if (items.length === 3) {
                        // Continued options for PG
                        const ops = cleanItems[0];
                        const jaw = items[1];
                        const knc = cleanItems[2];
                        // Find matching number from previous row (simplified)
                        // In reality, we need to track current question number
                    }
                } else if (tempJenis == "3") {
                    // Matching logic...
                    if (items.length === 9) {
                        no3 = cleanItems[0];
                        formData.append(`soal[${tempJenis}][${no3}][soal]`, encodeURIComponent(removeUrl(items[1])));
                        const kdBars = cleanItems[3];
                        const kdKolm = cleanItems[5];
                        const kdKunc = cleanItems[7];
                        formData.append(`soal[${tempJenis}][${no3}][baris][${kdBars}]`, encodeURIComponent(removeUrl(items[4])));
                        formData.append(`soal[${tempJenis}][${no3}][kolom][${kdKolm}]`, encodeURIComponent(removeUrl(items[6])));
                        if (cleanItems[8]) formData.append(`soal[${tempJenis}][${no3}][kunci][${kdKunc}]`, cleanItems[8]);
                    }
                } else {
                    const no = cleanItems[0];
                    if (no && !isNaN(parseInt(no))) {
                        formData.append(`soal[${tempJenis}][${no}][soal]`, encodeURIComponent(removeUrl(items[1])));
                        formData.append(`soal[${tempJenis}][${no}][kunci]`, encodeURIComponent(removeUrl(items[3] || '')));
                    }
                }
            }
        });

        // Finally send
        $.ajax({
            url: "<?= base_url('cbtbanksoal/uploadsoal') ?>",
            method: "POST",
            processData: false,
            contentType: false,
            data: formData,
            success: function (result) {
                Swal.fire({
                    title: 'Berhasil',
                    html: `Total soal diproses: <b>${result.total}</b><br>Soal berhasil diimport: <b>${result.insert}</b>`,
                    icon: 'success'
                }).then(() => {
                    window.history.back();
                });
            },
            error: function (xhr) {
                Swal.fire('Error', 'Terjadi kesalahan saat mengunggah: ' + xhr.statusText, 'error');
            }
        });
    }

    function removeUrl(str) {
        if (!str) return '';
        return str.replace('<?= base_url() ?>', '');
    }
</script>