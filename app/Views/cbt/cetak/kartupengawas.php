<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <?= esc($judul ?? 'Kartu Pengawas') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Cetak') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Filter & Action Card -->
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-funnel me-2"></i>Filter & Cetak</h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-warning me-2 box-shadow" data-bs-toggle="modal"
                                data-bs-target="#settingModal">
                                <i class="bi bi-gear me-1"></i> Setting Kartu
                            </button>
                            <a href="<?= base_url('userpanitia') ?>" class="btn btn-sm btn-light border">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 justify-content-center">
                            <div class="col-md-6 col-lg-4 text-center">
                                <button class="btn btn-primary px-4 my-shadow" id="btn-load">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Muat Data Pengawas
                                </button>
                                <button class="btn btn-success px-4 my-shadow d-none" id="btn-print">
                                    <i class="bi bi-printer me-1"></i> Cetak Kartu
                                </button>
                            </div>
                        </div>
                        <div class="border-top mt-3 pt-3 d-none" id="selector-container">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people me-2"></i>Pilih Pengawas yang Ingin Dicetak:</h6>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 11px;" id="btn-select-all">Pilih Semua</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 ms-1" style="font-size: 11px;" id="btn-deselect-all">Kosongkan</button>
                                </div>
                            </div>
                            <div class="row g-2 overflow-auto" style="max-height: 150px; padding: 2px 5px;" id="panitia-selector-list">
                                <!-- Checkboxes will be dynamically rendered here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-eye me-2"></i>Preview</h3>
                    </div>
                    <div class="card-body bg-light p-4 overflow-auto">
                        <div class="d-flex justify-content-center">
                            <div id="print-preview" class="bg-white shadow-sm"
                                style="min-width: 210mm; min-height: 297mm; padding: 1mm">
                                <!-- Default Placeholder Content -->
                                <div class="text-center text-muted pt-5 mt-5">
                                    <i class="bi bi-person-badge d-block fs-1 mb-2"></i>
                                    Klik tombol "Muat Data" untuk menampilkan kartu pengawas.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Setting Modal -->
<div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="settingModalLabel"><i class="bi bi-gear me-2"></i>Setting Kartu Pengawas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="set-kartu">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Header 1</label>
                        <textarea id="header-1" class="form-control" name="header_1" rows="2"
                            placeholder="Contoh: KARTU PENGAWAS UJIAN"
                            required><?= isset($kartu->header_1) ? $kartu->header_1 : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Header 2</label>
                        <textarea id="header-2" class="form-control" name="header_2" rows="2"
                            placeholder="Contoh: SUMATIF TENGAH SEMESTER"
                            required><?= isset($kartu->header_2) ? $kartu->header_2 : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Header 3</label>
                        <textarea id="header-3" class="form-control" name="header_3" rows="2"
                            placeholder="Contoh: TAHUN PELAJARAN 2023/2024"
                            required><?= isset($kartu->header_3) ? $kartu->header_3 : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Header 4</label>
                        <textarea id="header-4" class="form-control" name="header_4" rows="2" placeholder="Opsional"
                            required><?= isset($kartu->header_4) ? $kartu->header_4 : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Cetak</label>
                        <input id="input-tanggal" class="form-control" name="tanggal"
                            placeholder="Contoh: 1 Januari 2024"
                            value="<?= isset($kartu->tanggal) ? $kartu->tanggal : '' ?>" required>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                        <button type="button" onclick="submitKartu()" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* =====================================================
       KARTU PENGAWAS — Original 3x3 per A4
       ===================================================== */
    .kp-card {
        width: 6.3cm;
        height: 9.5cm;
        min-height: 9.5cm;
        background: white;
        font-family: 'Outfit', sans-serif;
        position: relative;
        overflow: hidden;
        margin: 4px;
        display: flex;
        flex-direction: row;
        color: #1e293b;
        box-sizing: border-box;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        background-color: #fff;
        flex-shrink: 0;
    }

    .kp-sidebar {
        width: 1.2cm;
        background: #00acee;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-top: 10px;
        color: white;
        position: relative;
        flex-shrink: 0;
    }

    .kp-logo-top {
        width: 25px;
        height: 25px;
        object-fit: contain;
        background: white;
        padding: 2px;
        border-radius: 50%;
        margin-bottom: 20px;
        flex-shrink: 0;
    }

    .kp-vertical-text {
        font-size: 16pt;
        font-weight: 900;
        letter-spacing: 3px;
        text-transform: uppercase;
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.9;
    }

    .kp-main {
        flex: 1;
        padding: 12px 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        position: relative;
        overflow: hidden;
    }

    .kp-header-center {
        text-align: center;
        margin-bottom: 8px;
        width: 100%;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 6px;
        flex-shrink: 0;
    }

    .kp-h1 {
        font-size: 7.5pt;
        font-weight: 800;
        text-transform: uppercase;
        margin: 0;
        color: #0f172a;
        line-height: 1.2;
    }

    .kp-h2 {
        font-size: 6.5pt;
        font-weight: 700;
        margin: 2px 0 0;
        color: #334155;
        white-space: nowrap;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .kp-h3 {
        font-size: 6.5pt;
        font-weight: 500;
        margin: 1px 0 0;
        color: #64748b;
        white-space: nowrap;
        text-transform: uppercase;
        line-height: 1.2;
    }

    /* kp-access-label removed */

    .kp-photo-frame {
        width: 2.8cm;
        height: 3.7cm;
        border: 2px solid white;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        margin-bottom: 8px;
        overflow: hidden;
        background: #f1f5f9;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }

    .kp-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 15%;
        display: block;
    }

    .kp-data-box {
        width: 100%;
        text-align: center;
        margin-top: 4px;
        margin-bottom: 6px;
        flex-shrink: 0;
    }

    .kp-name {
        font-size: 9.5pt;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 2px;
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
    }

    .kp-nip {
        font-size: 8.5pt;
        font-weight: 600;
        color: #334155;
        letter-spacing: 0.5px;
    }

    .kp-creds-box {
        margin-top: auto;
        width: 100%;
        background: #f0f9ff;
        border: 1.5px solid #00acee;
        padding: 6px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }

    .kp-qr-box {
        width: 48px;
        height: 48px;
        background: white;
        padding: 2px;
        border-radius: 4px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .kp-qr-box canvas,
    .kp-qr-box img {
        width: 100% !important;
        height: 100% !important;
    }

    .kp-cred-items {
        display: flex;
        flex-direction: column;
        gap: 2px;
        flex: 1;
        overflow: hidden;
    }

    .kp-cred-item {
        display: flex;
        flex-direction: column;
        text-align: left;
    }

    .kp-cred-label {
        color: #64748b;
        font-size: 6pt;
        text-transform: lowercase;
        font-weight: 500;
        line-height: 1;
    }

    .kp-cred-value {
        font-weight: 800;
        color: #2563eb;
        font-size: 10pt;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kp-cred-footer {
        font-size: 5pt;
        color: #475569;
        font-style: italic;
        margin-top: 4px;
        text-align: center;
        width: 100%;
        opacity: 0.8;
        flex-shrink: 0;
    }

    @media print {
        .kp-card {
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            page-break-inside: avoid;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .kp-sidebar {
            background-color: #00acee !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>assets/app/js/qrcode.min.js"></script>
<script src="<?= base_url() ?>assets/app/js/print-area.js"></script>

<script>
    var oldVal1 = <?= json_encode(isset($kartu->header_1) ? $kartu->header_1 : "") ?>;
    var oldVal2 = <?= json_encode(isset($kartu->header_2) ? $kartu->header_2 : "") ?>;
    var oldVal3 = <?= json_encode(isset($kartu->header_3) ? $kartu->header_3 : "") ?>;
    var oldVal4 = <?= json_encode(isset($kartu->header_4) ? $kartu->header_4 : "") ?>;
    var oldTgl = <?= json_encode(isset($kartu->tanggal) ? $kartu->tanggal : "") ?>;
    var oldKota = <?= json_encode($setting->kota) ?>;
    var logoKanan = "<?= base_url() . $setting->logo_kanan ?>";
    var logoKiri = "<?= base_url() . $setting->logo_kiri ?>";
    var tandatangan = "<?= base_url() . $setting->tanda_tangan ?>";
    var kepsek = <?= json_encode($setting->kepsek) ?>;
    var nip = <?= json_encode($setting->nip) ?>;
    var satuanPend = "<?= $setting->satuan_pendidikan ?>" === "2" ? "Madrasah" : "Sekolah";

    var globalPanitiaData = [];

    function submitKartu() {
        $('#set-kartu').submit();
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function generateQRCode(value, elementId) {
        try {
            var element = document.getElementById(elementId);
            if (!element) return;
            element.innerHTML = '';
            new QRCode(element, {
                text: value,
                width: 100,
                height: 100,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        } catch (e) {
            console.error("QR Code error:", e);
        }
    }

    function fitNames() {
        $('.kp-name').each(function () {
            let $el = $(this);
            let size = 9.5;
            $el.css('font-size', size + 'pt');
            while ($el[0].scrollWidth > $el[0].offsetWidth && size > 6) {
                size -= 0.5;
                $el.css('font-size', size + 'pt');
            }
        });
    }

    function renderSelectedCards() {
        var selectedData = [];
        $('.panitia-check:checked').each(function () {
            var index = $(this).data('index');
            selectedData.push(globalPanitiaData[index]);
        });

        if (selectedData.length === 0) {
            $("#print-preview").html('<div class="alert alert-info p-3 text-center">Pilih minimal satu pengawas untuk menampilkan preview kartu.</div>');
            $('#btn-print').addClass('d-none');
            return;
        }

        var konten = '';
        var cardsPerPage = 9; // 3x3 per page
        var pages = Math.ceil(selectedData.length / cardsPerPage);

        for (let a = 0; a < pages; a++) {
            var cardPage = '<div class="border mb-4 bg-white" style="width: 210mm; height: 297mm; padding: 5mm; margin: 0 auto; box-sizing: border-box; overflow: hidden; display: flex; flex-wrap: wrap; justify-content: flex-start;">';
            let startIndex = a * cardsPerPage;
            let endIndex = Math.min(startIndex + cardsPerPage, selectedData.length);

            for (let i = startIndex; i < endIndex; i++) {
                var p = selectedData[i];
                if (!p) continue;

                var cardHtml =
                    '<div class="kp-card">' +
                    '  <div class="kp-sidebar">' +
                    '    <img class="kp-logo-top" src="' + logoKiri + '" onerror="this.src=\'' + base_url + 'assets/img/logo.png\'">' +
                    '    <div class="kp-vertical-text">PENGAWAS</div>' +
                    '  </div>' +
                    '  <div class="kp-main">' +
                    '    <div class="kp-header-center">' +
                    '      <div class="kp-h1">' + escapeHtml(oldVal1) + '</div>' +
                    '      <div class="kp-h2">' + escapeHtml(oldVal2) + '</div>' +
                    '      <div class="kp-h3">' + escapeHtml(oldVal3) + '</div>' +
                    '    </div>' +
                    '    <div class="kp-photo-frame">' +
                    '      <img src="' + (p.foto ? base_url + p.foto : base_url + 'assets/img/user.png') + '" ' +
                    '           onerror="this.src=\'' + base_url + 'assets/img/user.png\'">' +
                    '    </div>' +
                    '    <div class="kp-data-box">' +
                    '      <div class="kp-name">' + escapeHtml(p.nama_panitia || '') + '</div>' +
                    '      <div class="kp-nip">' + (p.nip || '-') + '</div>' +
                    '    </div>' +
                    '    <div class="kp-creds-box">' +
                    '      <div class="kp-qr-box" id="qr-' + i + '"></div>' +
                    '      <div class="kp-cred-items">' +
                    '        <div class="kp-cred-item"><span class="kp-cred-label">username</span><span class="kp-cred-value">' + (p.username || '-') + '</span></div>' +
                    '        <div class="kp-cred-item"><span class="kp-cred-label">password</span><span class="kp-cred-value">' + (p.password || '-') + '</span></div>' +
                    '      </div>' +
                    '    </div>' +
                    '    <div class="kp-cred-footer">Mohon menjaga kerahasiaan kode akses ini</div>' +
                    '  </div>' +
                    '</div>';
                cardPage += cardHtml;
            }
            konten += cardPage + '</div><div style="page-break-after: always"></div>';
        }

        $("#print-preview").html(konten);

        setTimeout(function () {
            fitNames();
            for (let i = 0; i < selectedData.length; i++) {
                var p = selectedData[i];
                var qrData = (p && (p.username || p.id_panitia)) || '';
                if (qrData) {
                    generateQRCode(qrData, 'qr-' + i);
                }
            }
        }, 100);

        $('#btn-print').removeClass('d-none');
    }

    function createPrintPreview(response) {
        // Handle both nested and flat responses
        const resObj = response.data || response;
        const data = resObj.panitia;

        if (!data || data.length === 0) {
            const msg = response.message || "Data panitia tidak ditemukan.";
            $("#print-preview").html('<div class="alert alert-info p-3 text-center">' + escapeHtml(msg) + '</div>');
            return;
        }

        globalPanitiaData = data;

        // Show selector container and populate checklist
        $('#selector-container').removeClass('d-none');
        var checklistHtml = '';
        for (let i = 0; i < data.length; i++) {
            checklistHtml +=
                '<div class="col-sm-6 col-md-4 col-lg-3">' +
                '  <div class="form-check">' +
                '    <input class="form-check-input panitia-check" type="checkbox" value="' + data[i].id_panitia + '" id="chk-' + i + '" data-index="' + i + '" checked>' +
                '    <label class="form-check-label text-truncate" style="max-width: 100%; font-size: 0.85rem; cursor: pointer;" for="chk-' + i + '">' +
                '      ' + escapeHtml(data[i].nama_panitia) +
                '    </label>' +
                '  </div>' +
                '</div>';
        }
        $('#panitia-selector-list').html(checklistHtml);

        // Initially render all selected cards
        renderSelectedCards();
    }

    function loadPanitiaData(showLoadingModal = false) {
        if (showLoadingModal) {
            Swal.fire({
                text: "Memuat data panitia...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        } else {
            $("#print-preview").html(
                '<div class="text-center py-5 my-5 text-muted">' +
                '  <div class="spinner-border text-primary mb-2" role="status"></div>' +
                '  <div>Memuat data pengawas...</div>' +
                '</div>'
            );
        }

        $.ajax({
            type: "GET",
            url: base_url + "cbtcetak/getpanitia?v=" + new Date().getTime(),
            dataType: "json",
            success: function (response) {
                if (showLoadingModal) Swal.close();
                if (response.status === false) {
                    if (!showLoadingModal) {
                        $("#print-preview").html('<div class="alert alert-warning p-3 text-center">' + (response.message || 'Terjadi kesalahan') + '</div>');
                    } else {
                        Swal.fire('Info', response.message || 'Gagal memuat data', 'info');
                    }
                } else {
                    createPrintPreview(response);
                }
            },
            error: function (xhr) {
                if (showLoadingModal) {
                    Swal.close();
                    Swal.fire('Error', 'Gagal memuat data panitia (HTTP ' + xhr.status + ')', 'error');
                } else {
                    $("#print-preview").html('<div class="alert alert-danger p-3 text-center">Gagal memuat data pengawas. Silakan klik tombol Muat Data untuk mencoba kembali.</div>');
                }
            }
        });
    }

    $(document).ready(function () {
        // Auto-load data on page entry
        loadPanitiaData(false);

        $('#btn-load').click(function () {
            loadPanitiaData(true);
        });

        $(document).on('change', '.panitia-check', function () {
            renderSelectedCards();
        });

        $('#btn-select-all').click(function () {
            $('.panitia-check').prop('checked', true);
            renderSelectedCards();
        });

        $('#btn-deselect-all').click(function () {
            $('.panitia-check').prop('checked', false);
            renderSelectedCards();
        });

        $('#btn-print').click(function () {
            $('#print-preview').print(document.title);
        });

        $('#set-kartu').on('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                text: "Menyimpan pengaturan...",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            let form = new FormData($(this)[0]);
            $.ajax({
                url: base_url + 'cbtcetak/savekartu',
                type: 'POST',
                processData: false,
                contentType: false,
                data: form,
                success: function (response) {
                    $('#settingModal').modal('hide');
                    Swal.fire({
                        title: 'Sukses',
                        text: "Pengaturan berhasil disimpan",
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire('Error', 'Gagal menyimpan pengaturan', 'error');
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>