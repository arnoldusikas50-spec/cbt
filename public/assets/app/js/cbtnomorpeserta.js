/**
 * CBT Nomor Peserta - JavaScript
 * Migrated from CI3 to CI4
 */

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
};

const info = {
    "2": "2 digit tahun sekarang",
    "3": "Silahkan isi dengan Kode Provinsi Anda",
    "4": "Silahkan isi dengan Kode Kota/Kab",
    "5": "Silahkan isi dengan Kode Sekolah",
    "6": "SD/MI = 01, SMP/MTs = 02, SMA/MA = 03 atau sesuai jenjang sekolah",
    "7": "Otomatis diambil dari tingkat kelas siswa",
    "8": "NIS diambil dari data siswa jika tersedia",
    "9": "NISN diambil dari data siswa jika tersedia",
    "10": "Diisi dengan nomor awal misalnya 0001, nomor otomatis diurutkan",
    "11": "Biasanya diisi dengan angka 8 dan diurutkan secara menurun sampai angka 2"
};

let arrayNomor = [];
let original = [];
let values = {};

// Initialize values based on tahun and smt
function initValues() {
    const thn = tahun.split('/');
    const twoDigitYear = smt == '1' ? thn[0].substring(2, 4) : thn[1].substring(2, 4);

    values = {
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
    };
}

function sortByName(a, b) {
    const aName = a.nama.toLowerCase();
    const bName = b.nama.toLowerCase();
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

function getNomorPeserta(arr) {
    if (arr.length > 0) {
        $('#loading').removeClass('d-none');
        setTimeout(function () {
            let dataPost = new FormData($('#setNomor')[0]);
            dataPost.append('kelas', JSON.stringify(arr));

            $.ajax({
                url: base_url + "cbtnomorpeserta/getsiswakelas",
                type: "POST",
                data: dataPost,
                processData: false,
                contentType: false,
                success: function (response) {
                    original = response;
                    const newArr = [];

                    $.map(response.siswa, function (value, index) {
                        const item = {
                            id: value.id_siswa,
                            username: value.username,
                            nama: value.nama,
                            nis: value.nis,
                            nisn: value.nisn,
                            level: value.level,
                            kelas: value.nama_kelas,
                            nomor: response.nomor[value.id_siswa] != null ? response.nomor[value.id_siswa]?.nomor_peserta : ''
                        };
                        newArr.push(item);
                    });

                    newArr.sort(sortByNumber);
                    arrayNomor = newArr;
                    createPreview();
                }
            });
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
        confirmButtonText: "Hapus",
        cancelButtonText: "Batal"
    }).then(result => {
        if (result.isConfirmed) {
            let val = $('#kelas').val();

            // Jika user pilih "Semua", convert ke array of all kelas IDs
            if (val && val.includes('Semua')) {
                val = Object.keys(arrKelas).filter(key => key !== 'Semua');
            }

            if (val.length > 0) {
                $('#loading').removeClass('d-none');
                setTimeout(function () {
                    const arrStr = encodeURIComponent(JSON.stringify(val));
                    $.ajax({
                        url: base_url + "cbtnomorpeserta/resetnomor?kelas=" + arrStr,
                        type: "GET",
                        success: function (data) {
                            if (data.status) {
                                getNomorPeserta(val);
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Data Tidak Tersimpan",
                                    icon: "error"
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            const err = JSON.parse(xhr.responseText);
                            Swal.fire({
                                title: "Error",
                                text: err.Message,
                                icon: "error"
                            });
                        }
                    });
                }, 500);
            }
        }
    });
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

    const nomorUnik = generateUnikPattern(totalSiswa);

    arrayNomor.forEach((siswa, i) => {
        let nomor = [];

        for (let j = 2; j <= 11; j++) {
            if (!$(`#check${j}`).is(':checked')) continue;

            const kode = $(`#opt${j}`).val();
            const inputEl = $(`#input${j}`);
            let val = inputEl.val().trim();

            if (!val) continue;

            switch (kode) {
                case "7": // Tingkat Kelas
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
    const newArr = [];

    $.map(original.siswa, function (value, index) {
        const item = {
            id: value.id_siswa,
            username: value.username,
            nama: value.nama,
            nis: value.nis,
            nisn: value.nisn,
            level: value.level,
            kelas: value.nama_kelas,
            nomor: original.nomor[value.id_siswa] != null ? original.nomor[value.id_siswa]?.nomor_peserta : ''
        };
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
        const tbody = '<tr class="table-warning">' +
            '<td class="text-center align-middle">Tidak ada siswa di kelas ini</td>' +
            '</tr>';
        $('#table-nomor').html(tbody);
        $('#loading').addClass('d-none');
        return;
    }

    let tbody = '<thead class="table-light">' +
        '<tr>' +
        '<th class="text-center align-middle" width="40">No.</th>' +
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
    initValues();

    const opsiKelas = $("#kelas");
    opsiKelas.select2({ width: '100%' });

    if (Object.keys(arrKelas).length > 0) {
        opsiKelas.prepend("<option value='Semua'>Semua Level Kelas</option>");
    }

    opsiKelas.change(function () {
        let val = $(this).val();

        // Jika user pilih "Semua", convert ke array of all kelas IDs
        if (val && val.includes('Semua')) {
            val = Object.keys(arrKelas).filter(key => key !== 'Semua');
        }

        getNomorPeserta(val);
    });

    $(".form-check-input").on("click", function () {
        const id = $(this).attr('id').replace('check', '');
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
        const id = $(this).attr('id').replace('opt', '');
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

    $('#setNomor').submit(function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        if (arrayNomor.length > 0) {
            const $rows1 = $('#table-nomor').find('tr');
            const headers1 = $rows1.splice(0, 1);

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
                data: $(this).serialize() + "&siswa=" + encodeURIComponent(JSON.stringify(arrayNomor)),
                success: function (data) {
                    if (data.status) {
                        Swal.fire({
                            title: "Sukses",
                            text: "Nomor Peserta berhasil disimpan",
                            icon: "success"
                        });
                    } else {
                        Swal.fire({
                            title: "ERROR",
                            html: "Data Tidak Tersimpan<br>Nomor tidak boleh sama",
                            icon: "error"
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                    const err = JSON.parse(xhr.responseText);
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
