<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <?= esc($judul ?? 'Kartu Meja') ?>
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
                <!-- Filter Card -->
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="bi bi-funnel me-2"></i>Filter Data</h3>
                        <div class="card-tools">
                            <a href="<?= base_url('cbtcetak') ?>" class="btn btn-sm btn-light border">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 justify-content-center">
                            <div class="col-12 text-center mb-2">
                                <div class="btn-group" role="group" id="selector">
                                    <button type="button" class="btn btn-outline-primary active">By Kelas</button>
                                    <button type="button" class="btn btn-outline-primary">By Ruang</button>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div id="by-kelas" class="form-group">
                                    <label class="form-label fw-bold">Pilih Kelas</label>
                                    <?= form_dropdown(
                                        'kelas',
                                        $kelas,
                                        null,
                                        'id="kelas" class="form-select"'
                                    ); ?>
                                </div>
                                <div id="by-ruang" class="form-group d-none">
                                    <label class="form-label fw-bold">Pilih Ruang</label>
                                    <?= form_dropdown(
                                        'ruang',
                                        $ruang,
                                        null,
                                        'id="ruang" class="form-select"'
                                    ); ?>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 d-flex flex-column justify-content-end pb-2">
                                <div class="form-check form-switch mb-2 ms-md-4">
                                    <input class="form-check-input" type="checkbox" id="toggle-qr" checked>
                                    <label class="form-check-label fw-bold ms-2" for="toggle-qr">Tampilkan QR Code</label>
                                </div>
                                <div class="form-check form-switch mb-2 ms-md-4">
                                    <input class="form-check-input" type="checkbox" id="toggle-foto" checked>
                                    <label class="form-check-label fw-bold ms-2" for="toggle-foto">Tampilkan Foto Profil</label>
                                </div>
                                <div class="form-check form-switch ms-md-4">
                                    <input class="form-check-input" type="checkbox" id="toggle-kredensial" checked>
                                    <label class="form-check-label fw-bold ms-2" for="toggle-kredensial">Tampilkan User/Password</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title"><i class="bi bi-eye me-2"></i>Preview Cetak</h3>
                        <button class="btn btn-success text-white" id="btn-print">
                            <i class="bi bi-printer me-1"></i> Cetak
                        </button>
                    </div>
                    <div class="card-body bg-light p-4 overflow-auto">
                        <div class="d-flex justify-content-center">
                            <div id="print-preview" class="bg-white shadow-sm"
                                style="min-width: 210mm; min-height: 297mm; padding: 1mm">
                                <!-- Preview content will be loaded here -->
                                <div class="text-center text-muted pt-5 mt-5">
                                    <i class="bi bi-arrow-up-circle d-block fs-1 mb-2"></i>
                                    Silahkan pilih Kelas atau Ruang di atas untuk menampilkan kartu.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .km-card {
        width: 6.2cm;
        min-height: 9.2cm;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: white;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        position: relative;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: inline-block;
        margin: 5px;
    }

    .km-accent {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 4px;
        background: #3b82f6;
    }

    .km-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 80px;
    }

    .km-logo {
        width: 45px;
        height: 45px;
        object-fit: contain;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
    }

    .km-header-text {
        text-align: center;
        width: 100%;
        padding: 0;
    }

    .km-h1 {
        font-size: 7pt;
        font-weight: 700;
        color: #000000;
        margin-bottom: 2px;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .km-h2 {
        font-size: 8pt;
        font-weight: 800;
        color: #111111;
        margin-bottom: 2px;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .km-h3 {
        font-size: 8pt;
        font-weight: 600;
        color: #222222;
        line-height: 1.2;
    }

    .km-meja-section {
        padding: 5px 0;
        text-align: center;
        background: #ffffff;
    }

    .km-meja-label {
        font-size: 8pt;
        font-weight: 700;
        color: #333333;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .km-meja-no {
        font-size: 24pt;
        font-weight: 900;
        color: #000000;
        line-height: 1;
        margin-top: 4px;
        margin-bottom: 4px;
    }

    .km-info-badge {
        font-size: 8pt;
        font-weight: 700;
        background: transparent;
        color: #000000;
        padding: 3px 10px;
        border: 1px solid #000000;
        border-radius: 4px;
        display: inline-block;
        margin-top: 6px;
    }

    .km-visuals {
        display: flex;
        justify-content: center;
        gap: 15px;
        padding: 5px 10px;
        background: #fdfdfd;
        border-top: 1px dashed #e2e8f0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .km-qr {
        width: 70px;
        height: 70px;
        border: 1px solid #f1f5f9;
        padding: 4px;
        background: white;
    }

    .km-photo {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #f1f5f9;
    }

    .km-footer {
        padding: 10px;
        text-align: center;
    }

    .km-nama {
        font-size: 11pt;
        font-weight: 800;
        color: #000000;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 8px;
    }

    .km-credentials {
        background: transparent;
        color: #000000;
        padding: 6px;
        border: 1px solid #aaa;
        border-radius: 4px;
        font-size: 9pt;
        font-weight: 700;
        letter-spacing: 0.5px;
        display: block;
        margin: 0 auto;
        width: 90%;
    }



    @media print {
        .km-card {
            box-shadow: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/qrcode.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script>
    var oldVal1 = <?= json_encode(isset($kartu->header_1) ? $kartu->header_1 : 'KARTU MEJA UJIAN') ?>;
    var oldVal2 = <?= json_encode(isset($kartu->header_2) ? $kartu->header_2 : 'ASSESMEN BERBASIS KOMPUTER') ?>;
    var oldVal3 = <?= json_encode(isset($kartu->header_3) ? $kartu->header_3 : '') ?>;
    var oldVal4 = <?= json_encode(isset($kartu->header_4) ? $kartu->header_4 : '') ?>;
    var logoKiri = "<?= base_url($setting->logo_kiri) ?>";
    var logoKanan = "<?= base_url($setting->logo_kanan) ?>";

    var printBy = 1;

    function submitKartuMeja() {
        $('#set-kartu-meja').submit();
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
                width: 70,
                height: 70,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (e) {
            console.error("QR Code generation error:", e);
        }
    }

    var lastResponse = null;

    function createPrintPreview(response) {
        lastResponse = response;
        const rData = response.data || response;
        const data = rData.siswa;
        var konten = '';

        if (data && data.length > 0) {
            var cardsPerPage = 9; // 3x4 grid per page
            var pages = Math.ceil(data.length / cardsPerPage);

            for (let a = 0; a < pages; a++) {
                var card = '<div class="border my-shadow mb-3 pt-3 bg-white"><div class="pt-2" ' +
                    'style="display: flex;-webkit-justify-content: center;justify-content: center;background: white;width: 210mm; height: 297mm;padding: 5mm">';

                var table = '<table style="border-collapse: collapse;">';

                let startIndex = a * cardsPerPage;
                let endIndex = Math.min(startIndex + cardsPerPage, data.length);

                for (let i = startIndex; i < endIndex; i++) {
                    var setSiswa = data[i]?.set_siswa === '1';
                    var ruang = setSiswa ? data[i]?.ruang_kelas : data[i]?.kode_ruang;
                    var sesi = setSiswa ? data[i]?.sesi_kelas : data[i]?.kode_sesi;
                    var noMeja = (i + 1).toString().padStart(3, '0');

                    if ((i - startIndex) % 3 === 0) {
                        table += '<tr>';
                    }

                    var showQR = $('#toggle-qr').is(':checked');
                    var showFoto = $('#toggle-foto').is(':checked');
                    var showKredensial = $('#toggle-kredensial').is(':checked');

                    var visualsHtml = '';
                    var cardMinHeight = '9.2cm';
                    if (showQR || showFoto) {
                        visualsHtml = '  <div class="km-visuals">' +
                            (showQR ? '    <div class="km-qr" id="barcode-' + i + '"></div>' : '') +
                            (showFoto ? '    <img class="km-photo" src="' + (data[i]?.foto ? base_url + data[i]?.foto : base_url + 'assets/img/profile-default.png') + '" ' +
                                '         style="' + (showQR ? '' : 'width: 90px; height: 90px;') + '" ' +
                                '         onerror="this.onerror=null;this.src=\'' + base_url + 'assets/img/profile-default.png\'">' : '') +
                            '  </div>';
                    } else {
                        cardMinHeight = 'auto';
                    }

                    var td = '<td style="padding: 10px; vertical-align: top;">' +
                        '<div class="km-card" style="min-height: ' + cardMinHeight + ';">' +
                        '  <div class="km-accent"></div>' +
                        '  <div class="km-header">' +
                        '    <img class="km-logo" src="' + logoKiri + '" onerror="this.style.display=\'none\'">' +
                        '    <div class="km-header-text">' +
                        '      <div class="km-h2">' + escapeHtml(oldVal2) + '</div>' +
                        '      <div class="km-h1">' + escapeHtml(oldVal3) + '</div>' +
                        '    </div>' +
                        '  </div>' +
                        '  <div class="km-meja-section">' +
                        '    <div class="km-meja-label">No. Meja</div>' +
                        '    <div class="km-meja-no">' + noMeja + '</div>' +
                        '    <div class="km-info-badge">RUANG ' + escapeHtml(ruang) + ' | ' + escapeHtml(data[i]?.nama_kelas || '') + '</div>' +
                        '  </div>' +
                        visualsHtml +
                        '  <div class="km-footer">' +
                        '    <div class="km-nama">' + escapeHtml(data[i]?.nama || '') + '</div>' +
                        (showKredensial ? '    <div class="km-credentials">U: ' + escapeHtml(data[i]?.username || data[i]?.nisn || '') + '<br>' + 'P: ' + escapeHtml(data[i]?.password || '') + '</div>' : '') +
                        '  </div>' +
                        '</div>' +
                        '</td>';

                    table += td;

                    if ((i - startIndex) % 3 === 2 || i === endIndex - 1) {
                        table += '</tr>';
                    }
                }

                table += '</table>';
                card += table + '</div></div>';
                konten += card + '<div style="page-break-after: always"></div>';
            }

            setTimeout(function () {
                for (let i = 0; i < data.length; i++) {
                    var qrData = data[i]?.username || data[i]?.nomor_peserta || '';
                    if (qrData) {
                        generateQRCode(qrData, 'barcode-' + i);
                    }
                }
            }, 100);

        } else {
            konten = '<div class="mb-3 pt-3">' +
                '<div class="pt-4 text-bold" style="display: flex;-webkit-justify-content: center;justify-content: center;width: 210mm; height: 297mm;padding: 1mm">' +
                'Tidak ada data siswa, atau nomor peserta belum diatur</div>' +
                '</div>';
        }

        $("#print-preview").html(konten);
    }

    $(document).ready(function () {
        ajaxcsrf();

        $("#header-1").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal1) return;
            oldVal1 = currentVal;
            $('#prev-header-1').text(currentVal);
        });

        $("#header-2").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal2) return;
            oldVal2 = currentVal;
            $('#prev-header-2').text(currentVal);
        });

        $("#header-3").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal3) return;
            oldVal3 = currentVal;
            $('#prev-header-3').text(currentVal);
        });

        $("#header-4").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal4) return;
            oldVal4 = currentVal;
            $('#prev-header-4').text(currentVal);
        });



        $('#set-kartu-meja').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let form = new FormData($('#set-kartu-meja')[0]);
            $.ajax({
                url: base_url + 'cbtcetak/savekartumeja',
                type: 'POST',
                processData: false,
                contentType: false,
                data: form,
                success: function (response) {
                    Swal.fire({
                        title: 'Sukses',
                        text: "Setting kartu meja berhasil disimpan",
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                    }).then(result => {
                        if (result.value) {
                            window.location.href = base_url + 'cbtcetak/kartumeja';
                        }
                    });
                },
                error: function (xhr, error, status) {
                    const err = JSON.parse(xhr.responseText);
                    Swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        });

        function loadSiswaKelas(kelas) {
            $.ajax({
                type: "GET",
                url: base_url + "cbtcetak/getsiswakelas?kelas=" + kelas,
                success: function (response) {
                    createPrintPreview(response);
                }
            });
        }

        function loadSiswaRuang(ruang) {
            $.ajax({
                type: "GET",
                url: base_url + "cbtcetak/getsiswaruang?ruang=" + ruang,
                success: function (response) {
                    createPrintPreview(response);
                }
            });
        }

        $("#kelas").prepend("<option value='' selected='selected' disabled>Pilih Kelas</option>");
        $("#ruang").prepend("<option value='' selected='selected' disabled>Pilih Ruang</option>");

        $("#kelas").change(function () {
            loadSiswaKelas($(this).val());
        });

        $("#ruang").change(function () {
            loadSiswaRuang($(this).val());
        });

        $('#kelas').select2({ width: '100%', theme: 'bootstrap4' });
        $('#ruang').select2({ width: '100%', theme: 'bootstrap4' });

        $("#btn-print").click(function () {
            if (!$('#by-kelas').is(':hidden') && $('#kelas').val() === '') {
                Swal.fire({
                    title: "ERROR",
                    text: "Pilih kelas dulu",
                    icon: "error"
                });
            } else if (!$('#by-ruang').is(':hidden') && $('#ruang').val() === '') {
                Swal.fire({
                    title: "ERROR",
                    text: "Pilih ruang dulu",
                    icon: "error"
                });
            } else {
                $('#print-preview').print(document.title);
            }
        });

        $('#selector button').click(function () {
            $(this).addClass('active').siblings().removeClass('active');

            if (!$('#by-kelas').is(':hidden')) {
                $('#by-kelas').addClass('d-none');
                $('#by-ruang').removeClass('d-none');
                printBy = 2;
                $("#print-preview").html('');
            } else {
                $('#by-kelas').removeClass('d-none');
                $('#by-ruang').addClass('d-none');
                printBy = 1;
                $("#print-preview").html('');
            }
        });

        $('#toggle-qr, #toggle-foto, #toggle-kredensial').change(function () {
            if (lastResponse) {
                createPrintPreview(lastResponse);
            }
        });
    });
</script>
<?= $this->endSection() ?>