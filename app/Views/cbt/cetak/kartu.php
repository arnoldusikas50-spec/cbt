<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $setting_rapor = $setting_rapor ?? null; ?>
<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <?= esc($judul ?? 'Kartu Peserta') ?>
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
                            <div class="col-12 text-center mt-3">
                                <button class="btn btn-success px-4 my-shadow" id="btn-print">
                                    <i class="bi bi-printer me-1"></i> Cetak Kartu
                                </button>
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

<!-- Setting Modal -->
<div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="settingModalLabel"><i class="bi bi-gear me-2"></i>Setting Kartu Peserta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="set-kartu">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Header 1</label>
                        <textarea id="header-1" class="form-control" name="header_1" rows="2"
                            placeholder="Contoh: KARTU PESERTA UJIAN"
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitKartu()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .kp-card {
        width: 10.2cm;
        height: 6.8cm;
        min-height: 6.8cm;
        border: 1px solid #334155;
        background: white;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        position: relative;
        overflow: hidden;
        margin: 4px;
        display: flex;
        flex-direction: column;
        color: #1e293b;
        box-sizing: border-box;
        border-radius: 8px;
    }

    .kp-header {
        display: flex;
        align-items: center;
        padding: 6px 12px;
        border-bottom: 1px solid #f1f5f9;
        background: linear-gradient(to right, #ffffff, #f8fafc);
        min-height: 60px;
    }

    .kp-logo {
        width: 45px;
        height: 45px;
        object-fit: contain;
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.05));
    }

    .kp-header-text {
        flex: 1;
        text-align: center;
        padding: 0 8px;
    }

    .kp-h1 {
        font-size: 7pt;
        font-weight: 500;
        color: #0f172a;
        margin: 0;
        line-height: 1.1;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kp-h2 {
        font-size: 7.5pt;
        font-weight: 500;
        color: #0f172a;
        margin: 0;
        line-height: 1.1;
    }

    .kp-h3 {
        font-size: 9pt;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.3;
        text-transform: uppercase;
    }

    .kp-h4 {
        font-size: 5.5pt;
        font-weight: 400;
        color: #475569;
        margin: 0;
        line-height: 1.1;
    }

    .kp-body {
        display: block;
        padding: 6px 12px;
        height: 110px;
        position: relative;
    }

    .kp-content {
        float: left;
        width: 250px;
        margin-right: 85px;
    }

    .kp-row {
        margin-bottom: 1px;
        font-size: 8.5pt;
        clear: both;
    }

    .kp-label {
        float: left;
        width: 80px;
        color: #64748b;
        font-weight: 500;
    }

    .kp-sep {
        float: left;
        width: 10px;
        color: #cbd5e1;
    }

    .kp-value {
        float: left;
        width: 155px;
        font-weight: 600;
        color: #0f172a;
    }

    .member-name {
        font-size: 9.5pt !important;
        font-weight: 800;
        color: #1d4ed8 !important;
    }

    .kp-creds {
        padding: 0;
        background: transparent;
        border: none;
        display: flex;
        flex-direction: column;
        gap: 2px;
        box-shadow: none;
    }

    .kp-cred-item {
        font-size: 8pt;
        line-height: 1;
    }

    .kp-cred-label {
        color: #1e293b;
        font-size: 7pt;
        text-transform: lowercase;
        font-weight: 500;
        display: block;
        margin-bottom: 1px;
    }

    .kp-cred-val {
        font-weight: 800;
        color: #1d4ed8;
        font-family: 'Inter', sans-serif;
        font-size: 12pt;
        line-height: 1;
    }

    .kp-sidebar {
        position: absolute;
        right: 12px;
        top: 0px;
        width: 70px;
        text-align: center;
        z-index: 10;
    }

    .kp-photo {
        width: 70px;
        height: 90px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        padding: 1px;
        background: white;
    }

    .kp-footer {
        clear: both;
        display: flex;
        align-items: center;
        padding: 0 12px 6px;
        height: 80px;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        gap: 12px;
    }

    .kp-qr-box {
        width: 60px;
        height: 60px;
        background: white;
        padding: 0;
    }

    .kp-signature {
        text-align: center;
        font-size: 7pt;
        line-height: 1.1;
        width: 180px;
        margin-left: auto;
        position: relative;
    }

    .kp-sig-city {
        color: #475569;
        font-weight: 500;
        font-size: 6.5pt;
        margin-bottom: 2px;
    }

    .kp-sig-title {
        color: #64748b;
        font-size: 7pt;
        margin-bottom: 5px;
    }

    .kp-sig-img {
        height: 45px;
        width: auto;
        display: block;
        margin: -10px auto -25px;
        object-fit: contain;
        position: relative;
        z-index: 2;
        mix-blend-mode: multiply;
        pointer-events: none;
    }

    .kp-sig-name {
        font-weight: 800;
        color: #0f172a;
        text-decoration: underline;
        display: block;
        font-size: 7.5pt;
        margin-top: 5px;
    }

    .kp-sig-nip {
        font-size: 6.5pt;
        color: #64748b;
        margin-top: 0px;
    }

    @media print {
        .kp-card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .kp-creds {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/qrcode.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>

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
    var printBy = 1;

    var raporSetting = <?= json_encode($setting_rapor) ?>;
    var nipKepsek = (raporSetting == null || raporSetting.nip_kepsek == 1) ? nip : " -";

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
                width: 60,
                height: 60,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        } catch (e) {
            console.error("QR Code error:", e);
        }
    }

    function createPrintPreview(response) {
        const rData = response.data || response;
        const data = rData.siswa;

        if (!data) {
            $("#print-preview").html('<div class="alert alert-danger p-3 text-center">Data siswa tidak ditemukan.</div>');
            return;
        }

        var konten = '';
        if (data.length > 0) {
            var cardsPerPage = 8;
            var pages = Math.ceil(data.length / cardsPerPage);

            for (let a = 0; a < pages; a++) {
                var cardPage = '<div class="border my-shadow mb-4 pt-4 bg-white" style="width: 210mm; height: 297mm; padding: 2mm; margin: 0 auto; box-sizing: border-box; overflow: hidden;">';
                var grid = '<div style="display: block; width: 100%;">';
                let startIndex = a * cardsPerPage;
                let endIndex = Math.min(startIndex + cardsPerPage, data.length);

                for (let i = startIndex; i < endIndex; i++) {
                    var s = data[i];
                    if (!s) continue;

                    var setSiswa = s.set_siswa === '1';
                    var ruang = setSiswa ? s.ruang_kelas : s.kode_ruang;
                    var sesi = setSiswa ? s.sesi_kelas : s.kode_sesi;

                    var cardHtml = '<div style="float: left; padding: 4px; box-sizing: border-box; width: 50%;">' +
                        '<div class="kp-card">' +
                        '  <div class="kp-header">' +
                        '    <img class="kp-logo" src="' + logoKiri + '" onerror="this.style.visibility=\'hidden\'">' +
                        '    <div class="kp-header-text">' +
                        '      <div class="kp-h1">' + escapeHtml(oldVal1) + '</div>' +
                        '      <div class="kp-h2">' + escapeHtml(oldVal2) + '</div>' +
                        '      <div class="kp-h3">' + escapeHtml(oldVal3) + '</div>' +
                        '      <div class="kp-h4">' + escapeHtml(oldVal4) + '</div>' +
                        '    </div>' +
                        '    <img class="kp-logo" src="' + logoKanan + '" onerror="this.style.visibility=\'hidden\'">' +
                        '  </div>' +
                        '  <div class="kp-body">' +
                        '    <div class="kp-content">' +
                        '      <div class="kp-row"><div class="kp-label">No. Peserta</div><div class="kp-sep">:</div><div class="kp-value">' + (s.nomor_peserta || '-') + '</div></div>' +
                        '      <div class="kp-row"><div class="kp-label">Nama</div><div class="kp-sep">:</div><div class="kp-value member-name" style="font-size: 9pt">' + escapeHtml(s.nama || '') + '</div></div>' +
                        '      <div class="kp-row"><div class="kp-label">NIS / NISN</div><div class="kp-sep">:</div><div class="kp-value">' + (s.nis || '') + ' / ' + (s.nisn || '') + '</div></div>' +
                        '      <div class="kp-row"><div class="kp-label">Kelas</div><div class="kp-sep">:</div><div class="kp-value">' + escapeHtml(s.nama_kelas || '') + '</div></div>' +
                        '      <div class="kp-row"><div class="kp-label">Ruang/Sesi</div><div class="kp-sep">:</div><div class="kp-value">' + escapeHtml(ruang || '') + ' / ' + escapeHtml(sesi || '') + '</div></div>' +
                        '    </div>' +
                        '    <div class="kp-sidebar">' +
                        '      <img class="kp-photo" src="' + (s.foto ? base_url + s.foto : base_url + 'assets/img/profile-default.png') + '" ' +
                        '           onerror="this.onerror=null;this.src=\'' + base_url + 'assets/img/profile-default.png\'">' +
                        '    </div>' +
                        '  </div>' +
                        '  <div class="kp-footer">' +
                        '    <div class="kp-qr-box" id="qr-' + i + '"></div>' +
                        '    <div class="kp-creds">' +
                        '      <div class="kp-cred-item"><div class="kp-cred-label">Username</div><div class="kp-cred-val">' + escapeHtml(s.username || '') + '</div></div>' +
                        '      <div class="kp-cred-item"><div class="kp-cred-label">Password</div><div class="kp-cred-val">' + escapeHtml(s.password || '') + '</div></div>' +
                        '    </div>' +
                        '    <div class="kp-signature">' +
                        '      <div class="kp-sig-city">' + escapeHtml(oldKota) + ', ' + escapeHtml(oldTgl) + '</div>' +
                        '      <div class="kp-sig-title">Kepala ' + escapeHtml(satuanPend) + '</div>' +
                        '      <img class="kp-sig-img" src="' + tandatangan + '" onerror="this.style.display=\'none\'">' +
                        '      <div class="kp-sig-name">' + escapeHtml(kepsek || '-') + '</div>' +
                        (nipKepsek && nipKepsek !== " -" ? '<div class="kp-sig-nip">NIP. ' + escapeHtml(nipKepsek) + '</div>' : '') +
                        '    </div>' +
                        '  </div>' +
                        '</div>';
                    grid += cardHtml + '</div>';
                }
                grid += '<div style="clear: both;"></div></div>';
                konten += cardPage + grid + '</div><div style="page-break-after: always"></div>';
            }

            setTimeout(function () {
                for (let i = 0; i < data.length; i++) {
                    var s = data[i];
                    var qrData = (s && (s.username || s.nomor_peserta)) || '';
                    if (qrData) {
                        generateQRCode(qrData, 'qr-' + i);
                    }
                }
            }, 100);

        } else {
            var info = rData.info;
            const namaKelas = (info && info.kelas && info.kelas.nama_kelas) || 'Terpilih';
            konten = '<div class="text-center text-muted pt-5 mt-5">Tidak ada data siswa di kelas ' + namaKelas + '</div>';
        }

        $("#print-preview").html(konten);
    }


    $(document).ready(function () {
        $("#header-1").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal1) {
                return;
            }

            oldVal1 = currentVal;
            $('#prev-header-1').html(currentVal);
        });
        $("#header-2").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal2) {
                return;
            }

            oldVal2 = currentVal;
            $('#prev-header-2').text(currentVal);
        });
        $("#header-3").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal3) {
                return;
            }

            oldVal3 = currentVal;
            $('#prev-header-3').text(currentVal);
        });
        $("#header-4").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal4) {
                return;
            }

            oldVal4 = currentVal;
            $('#prev-header-4').text(currentVal);
        });

        $('#input-tanggal').on('input', function (e) {
            var tgl = $(this).val();
            $('#prev-tanggal').text(tgl);
            oldTgl = tgl;
        });

        $('#set-kartu').on('submit', function (e) {
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
            let form = new FormData($('#set-kartu')[0]);
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
                        text: "Header kartu berhasil disimpan",
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                    }).then(result => {
                        window.location.reload();
                    });
                },
                error: function (xhr, error, status) {
                    const err = JSON.parse(xhr.responseText)
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
            if ($('#selector button.active').text().includes('Kelas') && $('#kelas').val() === '') {
                Swal.fire({
                    title: "ERROR",
                    text: "Pilih kelas dulu",
                    icon: "error"
                })
            } else if ($('#selector button.active').text().includes('Ruang') && $('#ruang').val() === '') {
                Swal.fire({
                    title: "ERROR",
                    text: "Pilih ruang dulu",
                    icon: "error"
                })
            } else {
                $('#print-preview').print(document.title);
            }
        });

        $('#selector button').click(function () {
            $(this).addClass('active').siblings().removeClass('active');

            if ($(this).text().includes('Kelas')) {
                $('#by-kelas').removeClass('d-none');
                $('#by-ruang').addClass('d-none');
                printBy = 1;
                // Clear preview when switching
                $("#print-preview").html('<div class="text-center text-muted pt-5 mt-5"><i class="bi bi-arrow-up-circle d-block fs-1 mb-2"></i>Silahkan pilih Kelas di atas untuk menampilkan kartu.</div>');

            } else {
                $('#by-kelas').addClass('d-none');
                $('#by-ruang').removeClass('d-none');
                printBy = 2;
                // Clear preview when switching
                $("#print-preview").html('<div class="text-center text-muted pt-5 mt-5"><i class="bi bi-arrow-up-circle d-block fs-1 mb-2"></i>Silahkan pilih Ruang di atas untuk menampilkan kartu.</div>');
            }
        });
    })
</script>
<?= $this->endSection() ?>