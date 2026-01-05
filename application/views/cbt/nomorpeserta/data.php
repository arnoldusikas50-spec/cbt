<?php

?>

<main class="app-main pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Pilih Kelas Penilaian</label>
                            <?php
                            echo form_dropdown(
                                'kelas[]',
                                $kelas,
                                null,
                                'id="kelas" class="form-select" multiple="multiple" data-placeholder="Pilih kelas penilaian"'
                            ); ?>
                            <div class="mt-2 text-info small">
                                <i class="bi bi-info-circle me-1"></i> Pilih semua kelas yang akan melaksanakan
                                penilaian
                            </div>
                        </div>
                        <div class="col-md-6 d-none" id="generate">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#nomorModal">
                                    <i class="bi bi-cog me-1"></i> Generate Nomor
                                </button>
                                <button type="button" class="btn btn-danger shadow-sm" onclick="resetNumber()">
                                    <i class="bi bi-trash me-1"></i> Hapus Nomor
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12 d-none" id="for-save">
                            <div class="text-end">
                                <?= form_open('setNomor', array('id' => 'setNomor')); ?>
                                <button type="button" class="btn btn-sm btn-warning shadow-sm" onclick="undoNumber()">
                                    <i class="bi bi-undo me-1"></i> Batalkan perubahan
                                </button>
                                <button type="submit" class="btn btn-sm btn-primary shadow-sm ms-2">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                                <?= form_close() ?>
                            </div>
                        </div>
                        <table class="table table-bordered mt-2 col-12 dataTable" id="table-nomor">
                        </table>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <!-- <div class="spinner-grow"></div> -->
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                        Loading...
                    </button>
                </div>
            </div>
        </div>
    </section>
    </div>

    <div class="modal fade" id="nomorModal" tabindex="-1" role="dialog" aria-labelledby="nomorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nomorModalLabel">Generate Nomor Peserta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 mb-3-sm">
                        <!-- 1 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check1" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <div class="input-group-text" style="width: 180px">
                                        <span class="ms-2">Sparator</span>
                                    </div>
                                </div>
                                <input id="input1" class="form-control form-control-sm text-bold" value="-"
                                    type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small>Pemisah antara tiap kode, seperti: <b> - _ / </b> dan lainnya</small>
                        </div>
                        <!-- 2 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check2" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt2" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input2" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info2"></small>
                        </div>
                        <!-- 3 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check3" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt3" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input3" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info3"></small>
                        </div>
                        <!-- 4 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check4" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt4" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input4" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info4"></small>
                        </div>
                        <!-- 5 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check5" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt5" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input5" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info5"></small>
                        </div>
                        <!-- 6 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check6" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt6" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input6" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info6"></small>
                        </div>
                        <!-- 7 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check7" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt7" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input7" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info7"></small>
                        </div>
                        <!-- 8 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check8" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt8" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input8" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info8"></small>
                        </div>
                        <!-- 9 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check9" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt9" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input9" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info9"></small>
                        </div>
                        <!-- 10 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check10" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt10" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input10" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info10"></small>
                        </div>
                        <!-- 11 -->
                        <div class="d-flex flex-row align-items-center">
                            <div class="me-3">
                                <input id="check11" class="my-checkbox align-self-center" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <select id="opt11" class="form-control form-control-sm for-option"
                                        style="background-color: #e9ecef;width: 180px;">
                                    </select>
                                </div>
                                <input id="input11" class="form-control form-control-sm text-bold" type="text" />
                            </div>
                        </div>
                        <div class="mb-2 ms-5">
                            <small id="info11"></small>
                        </div>
                    </div>
                    <div class="alert alert-default-primary">
                        <strong>Catatan:</strong>
                        <ul>
                            <li>Silahkan cheklist kode yang akan digunakan, lalu pilih opsi KODE.</li>
                            <li>Jika ingin menambahkan kode khusus seperti kode ujian seperti <b>ASAT-</b>XX-XX, maka
                                bisa
                                memilih OPSi apa saja <b>selain</b> Kode Tahun, Nomor Urut dan Nomor Unik.</li>
                            <li>Pilihan Kode yang <b>tidak di-checklist</b> maka tidak akan digenerate.</li>
                            <li>Input Kode kosong tidak akan digenerate.</li>
                            <li>Nomor peserta ujian akan dibuat sesuai urutan siswa pada kelas yang dipilih.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border px-4" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary shadow-sm px-4" onclick="generateNomorUjian()">
                        <i class="bi bi-cog me-1"></i> Generate Nomor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var arrKelas = JSON.parse('<?= json_encode($kelas) ?>');
        var tahun = '<?= $tp_active->tahun ?>';
        var thn = tahun.split('/');
        var smt = '<?= $smt_active->id_smt ?>';

        const opsi = {
            "2": "Kode Tahun",
            "3": "Kode Provinsi",
            "4": "Kode Kota/Kab",
            "5": "Kode Sekolah",
            "6": "Kode Jenjang",
            "7": "Kode Tingkat Kls",
            "8": "NIS",
            "9": "NISN",
            "10": "Nomor Urut",
            "11": "Nomor Unik"
        }

        const twoDigitYear = smt == '1' ? thn[0].substring(2, 4) : thn[1].substring(2, 4);
        const values = {
            "2": twoDigitYear,
            "3": "",
            "4": "",
            "5": "",
            "6": "",
            "7": "Tingkat Kelas",
            "8": "NIS siswa",
            "9": "NISN siswa",
            "10": "001",
            "11": "8"
        }
        const info = {
            "2": "2 digit tahun sekarang",
            "3": "Silahkan isi dengan Kode Provinsi Anda",
            "4": "Silahkan isi dengan Kode Kota/Kab",
            "5": "Silahkan isi dengan Kode Sekolah",
            "6": "SD/MI = 01, SMP/MTs = 02, SMA/MA = 03  atau sesuai jenjang sekolah",
            "7": "Otomatis diambil dari tingkat kelas siswa",
            "8": "NIS diambil dari data siswa jika tersedia",
            "9": "NISN diambil dari data siswa jika tersedia",
            "10": "Diisi dengan nomor awal misalnya 0001, nomor otomatis diurutkan",
            "11": "Biasanya diisi dengan angka 8 dan diurutkan secara menurun sampai angka 2"
        }

        function sortByName(a, b) {
            var aName = a.nama.toLowerCase();
            var bName = b.nama.toLowerCase();
            return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
        }

        function sortByNumber(a, b) {
            if (a.nomor === '') return 1;
            if (b.nomor === '') return -1;

            return a.nomor.localeCompare(b.nomor, 'en', { numeric: true });
        }

        function pad(str, max) {
            str = str.toString();
            return str.length < max ? pad("0" + str, max) : str;
        }

        var arrayNomor = [];
        var original = [];

        function getNomorPeserta(arr) {
            if (arr.length > 0) {
                $('#loading').removeClass('d-none');
                setTimeout(function () {
                    let dataPost = new FormData($('#setNomor')[0])
                    dataPost.append('kelas', JSON.stringify(arr))
                    $.ajax({
                        url: base_url + "cbtnomorpeserta/getsiswakelas",
                        type: "POST",
                        data: dataPost,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            original = response;
                            var newArr = [];
                            $.map(response.siswa, function (value, index) {
                                var item = {};
                                item['id'] = value.id_siswa;
                                item['username'] = value.username;
                                item['nama'] = value.nama;
                                item['nis'] = value.nis;
                                item['nisn'] = value.nisn;
                                item['level'] = value.level;
                                item['kelas'] = value.nama_kelas;
                                item['nomor'] = response.nomor[value.id_siswa] != null ? response.nomor[value.id_siswa]?.nomor_peserta : '';

                                newArr.push(item);
                            });
                            newArr.sort(sortByNumber);
                            arrayNomor = newArr;
                            createPreview();
                        }
                    })
                }, 500);
            } else {
                $('#generate').addClass('d-none');
            }
        }

        function resetNumber() {
            Swal.fire({
                title: "Hapus Nomor Peserta",
                html: "Nomor Peserta akan dihapus dari database dan tidak bisa dikembalikan, Anda harus membuat ulang nomor peserta",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Hapus"
            }).then(result => {
                if (result.value) {
                    var val = $('#kelas').val();

                    if (val.length > 0) {
                        $('#loading').removeClass('d-none');
                        setTimeout(function () {
                            var arrStr = encodeURIComponent(JSON.stringify(val));
                            $.ajax({
                                url: base_url + "cbtnomorpeserta/resetnomor?kelas=" + arrStr,
                                type: "GET",
                                //data: $(this).serialize() + "&siswa=" + JSON.stringify(arrayNomor),
                                success: function (data) {
                                    if (data.status) {
                                        getNomorPeserta(val)
                                    } else {
                                        Swal.fire({
                                            title: "ERROR",
                                            text: "Data Tidak Tersimpan",
                                            icon: "error",
                                            showCancelButton: false,
                                        });
                                    }
                                }, error: function (xhr, status, error) {
                                    const err = JSON.parse(xhr.responseText)
                                    Swal.fire({
                                        title: "Error",
                                        text: err.Message,
                                        icon: "error"
                                    });
                                }
                            });
                        });
                    }
                }
            });
        }

        function createNumber() {
            $('#loading').removeClass('d-none');
            setTimeout(function () {
                if (arrayNomor.length > 0) {
                    var thn = tahun.split('/');

                    var newArrNomor = [];
                    var n = 1;
                    $.map(arrayNomor, function (value, index) {
                        var item = {};
                        item['id'] = value.id;
                        item['username'] = value.username;
                        item['nama'] = value.nama;
                        item['level'] = value.level;
                        item['kelas'] = value.kelas;
                        item['nomor'] = thn[0].substring(2, 4) +
                            thn[1].substring(2, 4) + '.' +
                            pad(value.level, 2) + '.' +
                            pad(n, 3);

                        n++;
                        newArrNomor.push(item);
                    });
                    arrayNomor = newArrNomor;
                    createPreview();
                }
            }, 500);
        }

        function generateUnikPattern(total) {
            const urutan = [8, 7, 6, 5, 4, 3, 2];
            const result = [];
            let i = 0;
            while (result.length < total) {
                result.push(urutan[i % urutan.length]);
                i++;
            }
            return result;
        }

        /**
         * Generate nomor ujian berdasarkan pilihan kode yang di-checklist
         */
        function generateNomorUjian() {
            arrayNomor.sort(sortByNumber);
            const totalSiswa = arrayNomor.length;
            const sparator = $('#check1').is(':checked') ? ($('#input1').val() || '-') : ' ';

            let nomorAwal = 1;
            for (let j = 2; j <= 11; j++) {
                if (!$(`#check${j}`).is(':checked')) continue;
                const selectedVal = $(`#opt${j}`).val();
                if (selectedVal === "10") {
                    const val = parseInt($(`#input${j}`).val());
                    if (!isNaN(val)) {
                        nomorAwal = val;
                    }
                    break;
                }
            }

            // Bangun pola unik untuk kode 11 (Nomor Unik)
            const nomorUnik = generateUnikPattern(totalSiswa);

            const level = {
                "1": "01", // Kelas 1
                "2": "02", // Kelas 2
                "3": "03", // Kelas 3
                "4": "04", // Kelas 4
                "5": "05", // Kelas 5
                "6": "06", // Kelas 6
                "7": "07", // Kelas 7
                "8": "08", // Kelas 8
                "9": "09", // Kelas 9
                "10": "10", // Kelas 10
                "11": "11", // Kelas 11
                "12": "12"  // Kelas 12
            };

            // Bangun nomor ujian berdasarkan format dari input form
            arrayNomor.forEach((siswa, i) => {
                let nomor = [];

                for (let j = 2; j <= 11; j++) {
                    if (!$(`#check${j}`).is(':checked')) continue;

                    const kode = $(`#opt${j}`).val();
                    const inputEl = $(`#input${j}`);
                    let val = inputEl.val().trim();
                    if (!val) continue;

                    // Gunakan default dari `values` jika input kosong
                    if (val === '' && values[kode]) {
                        val = values[kode];
                    }

                    switch (kode) {
                        case "7": // NIS
                            val = siswa.level || '[tingkat]';
                            break;
                        case "8": // NIS
                            val = siswa.nis || '[nis]';
                            break;
                        case "9": // NISN
                            val = siswa.nisn || '[nisn]';
                            break;
                        case "10": // Nomor Urut
                            const lengthPadding = val.length > 0 ? val.length : 3;
                            val = (nomorAwal + i).toString().padStart(lengthPadding, '0');
                            break;
                        case "11": // Nomor Unik
                            val = nomorUnik[i].toString();
                            break;
                    }

                    nomor.push(val);
                }

                siswa.nomor = nomor.join(sparator);
            });

            $('#nomorModal').modal('hide');
            createPreview();
        }

        function undoNumber() {
            var newArr = [];
            $.map(original.siswa, function (value, index) {
                var item = {};
                item['id'] = value.id_siswa;
                item['username'] = value.username;
                item['nama'] = value.nama;
                item['nis'] = value.nis;
                item['nisn'] = value.nisn;
                item['level'] = value.level;
                item['kelas'] = value.nama_kelas;
                item['nomor'] = original.nomor[value.id_siswa] != null ? original.nomor[value.id_siswa]?.nomor_peserta : '';

                newArr.push(item);
            });
            newArr.sort(sortByNumber);
            arrayNomor = newArr;

            createPreview();
        }

        function createPreview() {
            $('#generate').removeClass('d-none');
            $('#table-nomor').html('');
            if (arrayNomor.length === 0) {
                var tbody = '<tr class="alert-default-warning">' +
                    '<td class="text-center align-middle">Tidak ada siswa di kelas ini</td>' +
                    '</tr>';
                $('#table-nomor').html(tbody);
                $('#loading').addClass('d-none');
                return;
            }
            var tbody = '<thead>' +
                '<tr>' +
                '<th class="text-center align-middle" width="40" height="50">No.</th>' +
                '<th class="text-center align-middle" width="100">USERNAME</th>' +
                '<th class="text-center align-middle">NAMA</th>' +
                '<th class="text-center align-middle">KELAS</th>' +
                '<th class="text-center align-middle">NO. PESERTA UJIAN</th>' +
                '</tr></thead><tbody>';

            for (let i = 0; i < arrayNomor.length; i++) {
                tbody += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td class="text-center">' + arrayNomor[i].username + '</td>' +
                    '<td>' + arrayNomor[i].nama + '</td>' +
                    '<td class="text-center">' + arrayNomor[i].kelas + '</td>' +
                    '<td class="text-center editable" id="' + arrayNomor[i].id + '">' + arrayNomor[i].nomor + '</td>' +
                    '</tr>';
            }

            tbody += '</tbody>';
            $('#table-nomor').html(tbody);
            $('#loading').addClass('d-none');

            $('.editable').attr('contentEditable', true);
            $('#for-save').removeClass('d-none');
        }

        $(document).ready(function () {
            ajaxcsrf();

            var opsiKelas = $("#kelas");
            opsiKelas.select2({ width: '100%', });

            if (Object.keys(arrKelas).length > 0) {
                opsiKelas.prepend("<option value='Semua'>Semua Level Kelas</option>");
            }

            opsiKelas.change(function () {
                var val = $(this).val();
                getNomorPeserta(val);
            });

            $(".my-checkbox").on("click", function () {
                const id = $(this).attr('id').slice(-1);
                if ($(this).is(":checked")) {
                    $(`#input${id}`).removeAttr('disabled');
                } else {
                    $(`#input${id}`).attr('disabled', 'disabled');
                }
            });

            $('.for-option').append('<option value="">Pilih Kode</option>');

            for (const value in opsi) {
                $('.for-option').append('<option value="' + value + '">' + opsi[value] + '</option>');
            }

            $('.for-option').each(function (ind, val) {
                val.selectedIndex = ind + 1;
                const id = ind + 2;
                $(`#info${id}`).text(info[id]);
                $(`#input${id}`).val(values[id]);

                if (id === 8 || id === 9 || id === 7) {
                    $(`#input${id}`).attr('disabled', 'disabled');
                }
            });

            $('.for-option').change(function () {
                const id = $(this).attr('id').split('opt')[1];
                const val = $(this).val();
                $(`#input${id}`).val(values[val]);
                if (val == '8' || val == '9' || val == '7') {
                    $(`#input${id}`).attr('disabled', 'disabled');
                } else {
                    $(`#input${id}`).removeAttr('disabled');
                }

                if (val != '') {
                    $(`#info${id}`).text(info[val]);
                } else {
                    $(`#info${id}`).text('');
                }
            });

            $('#setNomor').submit('click', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (arrayNomor.length > 0) {

                    const $rows1 = $('#table-nomor').find('tr'), headers1 = $rows1.splice(0, 1);
                    $rows1.each((i, row) => {
                        const id = $(row).find('td.editable').attr('id');
                        const nomor = $(row).find('td.editable').text().trim();
                        for (let j = 0; j < arrayNomor.length; j++) {
                            if (arrayNomor[j].id == id) {
                                arrayNomor[j].nomor = nomor;
                            }
                        }
                    });

                    Swal.fire({
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                                Swal.showLoading();
                            }
                    });
                    $.ajax({
                        url: base_url + "cbtnomorpeserta/savenomor",
                        type: "POST",
                        dataType: "JSON",
                        data: $(this).serialize() + "&siswa=" + JSON.stringify(arrayNomor),
                        success: function (data) {
                            if (data.status) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Nomor Peserta berhasil disimpan",
                                    icon: "success",
                                    showCancelButton: false,
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    html: "Data Tidak Tersimpan<br>Nomor tidak boleh sama",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            const err = JSON.parse(xhr.responseText)
                            Swal.fire({
                                title: "Error",
                                text: err.Message,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        });
    </script>