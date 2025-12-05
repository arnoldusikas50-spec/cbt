<div class="content-wrapper bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                Cetak Kartu Meja
                            </div>
                            <button type="button" class="btn btn-sm btn-danger float-right" onclick="history.back();">
                                <i class="fas fa-arrow-circle-left"></i><span
                                        class="d-none d-sm-inline-block ml-1">Kembali</span>
                            </button>
                        </div>
                        <div class="card-body pb-4">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="card-tools btn-group" id="selector">
                                        <button type="button" class="btn active btn-primary">By Kelas</button>
                                        <button type="button" class="btn btn-outline-primary">By Ruang</button>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="input-group" id="by-kelas">
                                        <label>Kelas</label>
                                        <?php
                                        echo form_dropdown(
                                            'kelas',
                                            $kelas,
                                            null,
                                            'id="kelas" class="form-control"'
                                        ); ?>
                                    </div>
                                    <div class="input-group d-none" id="by-ruang">
                                        <label>Ruang</label>
                                        <?php
                                        echo form_dropdown(
                                            'ruang',
                                            $ruang,
                                            null,
                                            'id="ruang" class="form-control"'
                                        ); ?>
                                    </div>
                                </div>
                                <div class="col-4">

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                Preview Cetak
                            </div>
                                    <button class="btn bg-success text-white float-right" id="btn-print">
                                        <i class="fa fa-print"></i><span class="ml-1">Cetak</span>
                                    </button>

                        </div>
                        <div class="card-body pb-4">
                            <div class="d-flex justify-content-center bg-gray-light" style="min-height: 300mm">
                                <div id="print-preview" class="m-2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Include QRCode library -->
<script src="<?= base_url() ?>/assets/app/js/qrcode.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/print-area.js"></script>
<script>
    var oldVal1 = "<?= isset($kartu->header_1) ? $kartu->header_1 : 'KARTU MEJA UJIAN' ?>";
    var oldVal2 = "<?= isset($kartu->header_2) ? $kartu->header_2 : 'ASSESMEN BERBASIS KOMPUTER' ?>";
    var oldVal3 = "<?= isset($kartu->header_3) ? $kartu->header_3 : '' ?>";
    var oldVal4 = "<?= isset($kartu->header_4) ? $kartu->header_4 : '' ?>";
    var oldTgl = "<?= isset($kartu->tanggal) ? $kartu->tanggal : '' ?>";
    var printBy = 1;

    function submitKartuMeja() {
        $('#set-kartu-meja').submit();
    }

    function generateQRCode(value, elementId) {
        try {
            // Clear previous QR code
            var element = document.getElementById(elementId);
            element.innerHTML = '';
            
            // Generate new QR code
            new QRCode(element, {
                text: value,
                width: 80,
                height: 80,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (e) {
            console.error("QR Code generation error:", e);
        }
    }

    function createPrintPreview(response) {
        const data = response.siswa;
        var konten = '';
        
        if (data.length > 0) {
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
                    
                    var td = '<td style="padding: 8px; vertical-align: top;">' +
                        '<div style="width: 6cm; border: 2px solid black; background: white; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">' +
                        '<table style="width: 100%; border-collapse: collapse;">' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 4px; background: #f0f0f0; font-size: 13pt; font-weight: 600; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">' + oldVal1 + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 3px; background: #f0f0f0; font-size: 8pt; font-weight: 500; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">' + oldVal2 + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 2px; font-size: 6pt; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">' + oldVal3 + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 2px; font-size: 6pt; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">' + oldVal4 + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 4px; border-top: 1px solid black; font-size: 12pt; font-weight: 700; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">MEJA NO: ' + noMeja + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 3px; font-size: 9pt; font-weight: 500; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">RUANG ' + ruang + ' / KELAS ' + data[i]?.nama_kelas + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td style="text-align: center; padding: 6px; border-top: 1px solid black;">' +
                        '<table style="width: 100%; border-collapse: collapse;">' +
                        '<tr>' +
                        '<td style="text-align: center; padding: 2px; width: 50%;">' +
                        '<div id="barcode-' + i + '" style="width: 80px; height: 80px; margin: 0 auto;"></div>' +
                        '</td>' +
                        '<td style="text-align: center; padding: 2px; width: 50%;">' +
                        '<img src="' + (data[i]?.foto ? (base_url + data[i]?.foto) : (base_url + 'assets/img/siswa_.png')) + '" style="width: 80px; height: 80px; object-fit: cover; border-radius: 3px;" alt="Foto">' +
                        '</td>' +
                        '</tr>' +
                        '</table>' +
                        '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 3px; font-size: 5pt; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">-</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 4px; border-top: 1px solid black; font-size: calc(6cm / 20); font-weight: 600; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + data[i]?.nama + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 3px; font-size: 10pt; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;"><b>U : ' + data[i]?.nisn + ' | P : ' + data[i]?.password + '</b></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td colspan="3" style="text-align: center; padding: 3px; border-top: 1px solid black; font-size: 8pt; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">' + oldTgl + '</td>' +
                        '</tr>' +
                        '</table>' +
                        '</div>' +
                        '</td>';
                    
                    table += td;
                    
                    if ((i - startIndex) % 3 === 3 || i === endIndex - 1) {
                        // Fill empty cells if needed
                        var remainingCells = 3 - ((i - startIndex) % 4);
                        for (let j = 0; j < remainingCells && (i - startIndex + j + 1) < 4; j++) {
                            table += '<td style="padding: 8px;"></td>';
                        }
                        table += '</tr>';
                    }
                }
                
                table += '</table>';
                card += table + '</div></div>';
                konten += card + '<div style="page-break-after: always"></div>';
            }
            
            // Generate QR codes after DOM is updated
            setTimeout(function() {
                for (let i = 0; i < data.length; i++) {
                    // Use username for QR code to integrate with login system
                    var qrData = data[i]?.username || data[i]?.nomor_peserta || '';
                    if (qrData) {
                        generateQRCode(qrData, 'barcode-' + i);
                    } else {
                        console.warn('No username or nomor_peserta found for student at index', i);
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
        // Generate initial QR code for preview
        generateQRCode("001", "barcode-preview");

        $("#header-1").on("change keyup paste", function () {
            var currentVal = $(this).val();
            if (currentVal === oldVal1) return;
            oldVal1 = currentVal;
            $('#prev-header-1').html(currentVal);
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

        $('#input-tanggal').on('input', function (e) {
            var tgl = $(this).val();
            $('#prev-tanggal').text(tgl);
            oldTgl = tgl;
        });

        $('#set-kartu-meja').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
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
                    swal.fire({
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
                    swal.fire({
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

        $('#kelas').select2({width: '100%', theme: 'bootstrap4'});
        $('#ruang').select2({width: '100%', theme: 'bootstrap4'});

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
            $(this).addClass('active').siblings().addClass('btn-outline-primary').removeClass('active btn-primary');

            if (!$('#by-kelas').is(':hidden')) {
                $('#by-kelas').addClass('d-none');
                $('#by-ruang').removeClass('d-none');
                printBy = 2;
                // Clear preview when switching
                $("#print-preview").html('');
            } else {
                $('#by-kelas').removeClass('d-none');
                $('#by-ruang').addClass('d-none');
                printBy = 1;
                // Clear preview when switching
                $("#print-preview").html('');
            }
        });
    });
</script>
