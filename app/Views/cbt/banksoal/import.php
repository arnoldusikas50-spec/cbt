<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark"><?= esc($judul ?? 'Halaman') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill fs-3 me-3"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Catatan Penting!</h6>
                Untuk import data dari file Word (.docx), silakan download templatenya terlebih dahulu. 
                <span class="fw-bold">Pastikan format tabel tidak berubah.</span>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-body-tertiary">
                <h6 class="card-title fw-bold mb-0">
                    <i class="bi bi-cloud-upload me-2"></i>Upload Soal: <?= esc($bank->nama_mapel) ?> (Level <?= esc($bank->bank_level) ?>)
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <a href="<?= base_url('uploads/import/format/format_soal_akm.docx') ?>"
                            class="btn btn-outline-success w-100 py-2 shadow-sm"
                            download="Template Soal <?= esc($bank->nama_mapel) ?>.docx">
                            <i class="bi bi-download me-2"></i> Download Template
                        </a>
                    </div>
                    <div class="col-md-5">
                        <form id="formPreviewWord">
                            <div class="input-group">
                                <input type="file" name="upload_file" class="form-control" id="upload-word" accept=".doc, .docx">
                                <label class="input-group-text bg-light border-start-0" for="upload-word"><i class="bi bi-file-earmark-word text-primary"></i></label>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100 py-2 shadow-sm" onclick="getData()">
                            <i class="bi bi-cloud-arrow-up me-2"></i> Upload Ke Database
                        </button>
                    </div>
                </div>

                <div id="file-preview" class="p-4 border rounded bg-light overflow-auto position-relative" style="min-height: 400px; max-height: 800px;">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted py-5" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none;">
                        <i class="bi bi-file-earmark-text display-1 mb-3 opacity-25"></i>
                        <p class="mb-0">Belum ada file yang dipilih untuk dipreview.</p>
                        <small class="text-muted">Pilih file .docx pada form di atas.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="grouping" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="id_bank" value="<?= $bank->id_bank ?>">
</form>

<!-- Dependencies -->
<script src="<?= base_url('assets/plugins/jquery-table2json/src/tabletojson-cell.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-table2json/src/tabletojson-row.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-table2json/lib/jquery.tabletojson.js') ?>"></script>
<script src="<?= base_url('assets/app/js/mammoth.browser.min.js') ?>"></script>
<script src="<?= base_url('assets/app/js/docx.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
    const bank_id = '<?= $bank->id_bank ?>';
    let filename = '';

    $(document).ready(function () {
        $('#upload-word').on('change', function (e) {
            filename = $(this).val();
            if (this.files && this.files.length > 0) {
                // Clear placeholder
                $('#file-preview').find('.d-flex').fadeOut();
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

            // Clean document.xml from altChunks
            const documentXmlPath = "word/document.xml";
            if(zip.file(documentXmlPath)) {
                let documentXml = await zip.file(documentXmlPath).async("string");
                documentXml = documentXml
                    .replace(/<w:altChunk[^>]*\/>/g, "")
                    .replace(/<w:altChunk[\s\S]*?<\/w:altChunk>/g, "");
                zip.file(documentXmlPath, documentXml);
            }

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
                    let tanpaJns = 0;

                    $(showDiv).children().not("table").remove();
                    $(showDiv).children('table').each(function () {
                        $(this).addClass('table table-bordered w-100 table-soal bg-white align-middle shadow-sm');
                        const $trs = $(this).find('tr');

                        $trs.each(function () {
                            const cekTbl = $(this).parent().closest('td');
                            if (cekTbl.length === 0) {
                                $(this).addClass('tr-soal');
                                $(this).find('td').addClass('td-soal p-2');
                            }
                        });
                    });

                    // Analyze counts (simplified logic)
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
                            <h6 class="fw-bold mb-2"><i class="bi bi-check-circle text-success me-2"></i>Hasil Analisa Preview:</h6>
                            <ul class="mb-0 small ps-3">
                                <li>Estimasi soal terbaca: <b>${jmlSoalFix}</b></li>
                                <li>Baris tanpa jenis soal (mungkin header): <b>${tanpaJns}</b></li>
                            </ul>
                        </div>
                    `);
                    Swal.close();
                }, 500);
            });
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Gagal Membaca File',
                text: 'Pastikan file yang diupload adalah format .docx yang valid.',
                icon: 'error'
            });
            $('#file-preview').find('.d-flex').fadeIn();
        }
    }

    function getData() {
        if (filename === '') {
            Swal.fire('Info', 'Pilih file Word terlebih dahulu.', 'info');
            return;
        }

        Swal.fire({
            title: 'Mengimpor Soal',
            html: 'Sedang memproses dan mengunggah ke database...<br><small class="text-muted">Jangan tutup halaman ini.</small>',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Use setTimeout to allow UI to render Swal before heavy processing
        setTimeout(() => {
            const formData = new FormData($('#grouping')[0]);
            let totalProcessed = 0;

            $('.table-soal').each(function (i) {
                const $tbl = $(this);
                let currentRows = [];
                
                // Extract text from rows
                $tbl.find('.tr-soal').each(function() {
                    const $cells = $(this).find('.td-soal');
                    const rowData = $cells.map(function() { return $(this).html(); }).get();
                    if(rowData.length > 0) currentRows.push(rowData);
                });

                if (currentRows.length === 0) return;

                // Determine question type from first valid row
                let tempJenis = "";
                for (let scan = 0; scan < Math.min(5, currentRows.length); scan++) {
                    let j = $(currentRows[scan][2] || '').text().trim(); // Column index 2 is usually Jenis
                    if (!isNaN(parseInt(j))) {
                        tempJenis = j;
                        break;
                    }
                }

                if (tempJenis === "") return;

                // Process rows based on type
                // Track current question number across rows (Word template: one row per option,
                // only the first row of a question holds the question text; rows for B-E have
                // empty soal column but must still be imported as options).
                let currentNo = null;

                currentRows.forEach(items => {
                    const cleanItems = items.map(it => $('<div>'+it+'</div>').text().trim()); // Clean HTML for logic checks
                    
                    if (tempJenis == "1" || tempJenis == "2") { // PG or PG Kompleks
                        // Accept rows with at least 6 cells; option letter in col 4 (index 3),
                        // option text in col 5 (index 4). Rows B-E have empty soal cell but
                        // still carry the option letter and text.
                        if (items.length >= 6) {
                            const no = cleanItems[0];
                            const soal = items[1];
                            const ops = cleanItems[3];
                            const jaw = items[4];
                            const knc = cleanItems[5]; // V or blank

                            // Track current question number when a row carries one
                            if (no) {
                                currentNo = no;
                                // Append soal text only when available (first row of question)
                                if (soal || $(items[1]).find('img').length > 0) {
                                    formData.append(`soal[${tempJenis}][${currentNo}][soal]`, encodeURIComponent(removeUrl(soal)));
                                }
                            }

                            // Only process if ops looks like an option letter (A-E)
                            if (currentNo && /^[A-E]$/i.test(ops)) {
                                formData.append(`soal[${tempJenis}][${currentNo}][opsi][${ops.toUpperCase()}]`, encodeURIComponent(removeUrl(jaw)));
                                if (knc && knc.toUpperCase() === "V") {
                                    formData.append(`soal[${tempJenis}][${currentNo}][kunci][]`, ops.toUpperCase());
                                }
                                totalProcessed++;
                            }
                        }
                    } else if (tempJenis == "3") { // Menjodohkan
                        if (items.length >= 9) {
                            const no3 = cleanItems[0];
                            if(no3) {
                                formData.append(`soal[${tempJenis}][${no3}][soal]`, encodeURIComponent(removeUrl(items[1])));
                                const kdBars = cleanItems[3]; // KD Baris
                                const kdKolm = cleanItems[5]; // KD Kolom
                                const kdKunc = cleanItems[7]; // Kunci/Jawaban
                                // Only append if content exists
                                if(items[4]) formData.append(`soal[${tempJenis}][${no3}][baris][${kdBars}]`, encodeURIComponent(removeUrl(items[4])));
                                if(items[6]) formData.append(`soal[${tempJenis}][${no3}][kolom][${kdKolm}]`, encodeURIComponent(removeUrl(items[6])));
                                if (cleanItems[8]) formData.append(`soal[${tempJenis}][${no3}][kunci][${kdKunc}]`, cleanItems[8]);
                                totalProcessed++;
                            }
                        }
                    } else { // Isian/Essay (Type 4 or 5)
                        const no = cleanItems[0];
                        if (no && !isNaN(parseInt(no))) {
                            formData.append(`soal[${tempJenis}][${no}][soal]`, encodeURIComponent(removeUrl(items[1])));
                            formData.append(`soal[${tempJenis}][${no}][kunci]`, encodeURIComponent(removeUrl(items[3] || '')));
                            totalProcessed++;
                        }
                    }
                });
            });

            if (totalProcessed === 0) {
                 Swal.fire('Gagal', 'Tidak ada data soal yang valid ditemukan untuk diupload.', 'warning');
                 return;
            }

            // AJAX Upload
            $.ajax({
                url: "<?= base_url('cbtbanksoal/uploadsoal') ?>",
                method: "POST",
                processData: false,
                contentType: false,
                data: formData,
                success: function (result) {
                    Swal.fire({
                        title: 'Berhasil',
                        html: `Proses Selesai.<br>Total data terkirim: <b>${result.total || totalProcessed}</b><br>Berhasil disimpan: <b>${result.insert || '0'}</b>`,
                        icon: 'success'
                    }).then(() => {
                        window.location.href = "<?= base_url('cbtbanksoal') ?>";
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', 'Terjadi kesalahan saat mengunggah ke server: ' + xhr.statusText, 'error');
                }
            });
        }, 500);
    }

    function removeUrl(str) {
        if (!str) return '';
        // Create regex to safely replace base_url
        const baseUrl = '<?= base_url() ?>';
        return str.replace(new RegExp(baseUrl, 'g'), '');
    }
</script>
<?= $this->endSection() ?>