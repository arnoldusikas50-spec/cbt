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
                    <div class="alert alert-default-info border-info">
                        <div id="info-penggunaan">
                            Halaman ini untuk membatasi jumlah siswa yang aktif.
                            <ul>
                                <li>
                                    Lakukan aktivasi sebelum sesi dimulai
                                </li>
                                <li>
                                    Hanya siswa aktif yang bisa login
                                </li>
                                <li>
                                    Siswa yang dinonaktifkan tidak akan bisa login
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php
                    $dnone = count($ruangs) === 0;
                    ?>
                    <div class="by-ruang <?= $dnone ? 'd-none' : '' ?>">
                        <div class="row mb-4">
                            <div class="col-12 mb-2 d-flex flex-row justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-action-ruang btn-success btn-sm"
                                        data-action="aktifkan" data-bs-toggle="tooltip" title="Aktifkan Ruang" disabled>
                                        <i class="bi bi-person-check m-1"></i>
                                        <span class="d-none d-sm-inline-block">Aktifkan</span>
                                        <span class="text-bold" id="aktifkan-ruang">0</span>
                                        <span class="mx-1">Sesi</span>
                                    </button>
                                    <button type="button" class="btn btn-action-ruang btn-danger btn-sm"
                                        data-action="nonaktifkan" data-bs-toggle="tooltip" title="Nonaktifkan" disabled>
                                        <i class="bi bi-ban m-1"></i>
                                        <span class="d-none d-sm-inline-block">Nonaktifkan </span>
                                        <span class="mx-1 text-bold" id="nonaktifkan-ruang">0</span>
                                        <span class="mx-1">Sesi</span>
                                    </button>
                                </div>
                                <div class="btn-group">
                                    <button type="button" title="By Ruang/Sesi"
                                        class="btn btn-sm btn-primary btn-by-ruang active"><i
                                            class="bi bi-door-open"></i> Ruang</button>
                                    <button type="button" title="By Siswa"
                                        class="btn btn-sm btn-primary btn-by-siswa"><i class="bi bi-persons"></i>
                                        Siswa</button>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive mb-3">
                                    <?= form_open('', array('id' => 'bulkruang')); ?>
                                    <table class="table table-bordered table-striped w-100">
                                        <thead class="alert-default-info">
                                            <tr>
                                                <th class="text-center align-middle" style="width: 40px">
                                                    <input class="check my-checkbox" type="checkbox"
                                                        id="check-all-ruang">
                                                </th>
                                                <th class="text-center align-middle" style="width: 40px">No</th>
                                                <th class="align-middle">Ruang</th>
                                                <th class="text-center align-middle">Sesi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-body-ruang">
                                            <?php
                                            $idx = 1;
                                            foreach ($ruangs as $ru):
                                                foreach ($ru as $ses):
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <input class="check my-checkbox check-ruang-sesi" type="checkbox"
                                                                name="ids[]"
                                                                value="<?= $ses->ruang_id . '-' . $ses->sesi_id ?>">
                                                        </td>
                                                        <td class="text-center align-middle"><?= $idx ?></td>
                                                        <td class="align-middle">
                                                            <?= $ses->kode_ruang . ' (' . $ses->nama_ruang . ')' ?>
                                                        </td>
                                                        <td class="text-center align-middle"><?= $ses->kode_sesi ?></td>
                                                    </tr>
                                                    <?php $idx++; endforeach; endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="by-siswa d-none">
                        <div class="row mb-4">
                            <div class="col-12 col-sm-5 col-md-3 mb-3 order-1 order-sm-2">
                                <?php
                                echo form_dropdown(
                                    'ruang',
                                    $ruang,
                                    null,
                                    'id="ruang" class="form-control form-control-sm select2"'
                                ); ?>
                            </div>
                            <div class="col-12 col-sm-5 col-md-3 mb-3 order-2 order-sm-3">
                                <select name="sesi" id="sesi" class="form-control form-control-sm select2"></select>
                            </div>
                            <div class="col-12 col-sm-2 col-md-6 mb-3 order-0 order-sm-3">
                                <div class="text-end">
                                    <div class="btn-group">
                                        <button type="button" title="By Ruang/Sesi"
                                            class="btn btn-sm btn-primary btn-by-ruang active"><i
                                                class="bi bi-door-open"></i> Ruang</button>
                                        <button type="button" title="By Siswa"
                                            class="btn btn-sm btn-primary btn-by-siswa"><i class="bi bi-persons"></i>
                                            Siswa</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row d-none" id="table-siswa">
                            <div class="col-12 mb-2 d-flex flex-row justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-action btn-success btn-sm"
                                        data-action="aktifkan" data-bs-toggle="tooltip" title="Aktifkan" disabled>
                                        <i class="bi bi-person-check m-1"></i>
                                        <span class="d-none d-sm-inline-block">Aktifkan</span>
                                        <span class="mx-1 text-bold" id="nonaktifkan">0</span>
                                    </button>
                                    <button type="button" class="btn btn-action btn-danger btn-sm"
                                        data-action="nonaktifkan" data-bs-toggle="tooltip" title="Nonaktifkan" disabled>
                                        <i class="bi bi-ban m-1"></i>
                                        <span class="d-none d-sm-inline-block">Nonaktifkan </span>
                                        <span class="mx-1 text-bold" id="aktifkan">0</span>
                                    </button>
                                </div>
                                <div class="d-flex flex-row align-items-center" style="width: 120px">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        </div>
                                        <input type="search" id="cari-siswa" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive mb-3">
                                    <table id="users" class="w-100 table table-striped table-bordered table-hover">
                                        <thead class="alert-default-primary">
                                            <tr>
                                                <th class="text-center align-middle">
                                                    <input class="check my-checkbox" type="checkbox" id="check-all">
                                                </th>
                                                <th class="text-center" style="width: 40px">No.</th>
                                                <th class="text-center">NIS</th>
                                                <th>Nama</th>
                                                <th class="text-center">Kelas</th>
                                                <th class="text-center">Username</th>
                                                <th class="text-center">Password</th>
                                                <th class="text-center">Reset Login</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class=" <?= $dnone ? '' : 'd-none' ?>">
                        <div class="alert alert-default-danger border-danger">
                            Belum ada siswa yang tergabung dalam sesi
                        </div>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <!-- <div class="spinner-grow"></div> -->
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                        Loading...
                    </button>
                </div>
                <?= form_open('', array('id' => 'bulk')); ?><?= form_close() ?>
            </div>
        </div>
    </section>
    </div>

    <script>
        const arrRuang = JSON.parse('<?= json_encode($ruangs) ?>');
        let ruang, sesi, url, newResponse;

        let uncheckedRuang = [];
        let checkedRuang = [];

        let uncheckedSiswa = [];
        let checkedSiswa = [];

        let timerCari;

        $(document).ready(function () {
            ajaxcsrf();

            console.log('arr', arrRuang)
            const btnByRuang = $('.btn-by-ruang')
            const btnBySiswa = $('.btn-by-siswa')
            btnByRuang.on('click', function () {
                $('.by-ruang').removeClass('d-none')
                $('.by-siswa').addClass('d-none')

                $("#check-all").prop("checked", false);
                $('#check-all').prop('indeterminate', false)
                $('#aktifkan').text('0')
                $('#nonaktifkan').text('0')

                $("#check-all-ruang").prop("checked", false);
                $('#check-all-ruang').prop('indeterminate', false)
                $('#aktifkan-ruang').text('0')
                $('#nonaktifkan-ruang').text('0')

                $(".check-ruang-sesi").each(function () {
                    this.checked = false;
                });

                $(".check-siswa").each(function () {
                    this.checked = false;
                });

                findUncheckedRuang()
                findUnchecked()

                btnByRuang.addClass('active')
                btnBySiswa.removeClass('active')
            })
            btnBySiswa.on('click', function () {
                $('.by-ruang').addClass('d-none')
                $('.by-siswa').removeClass('d-none')

                $("#check-all").prop("checked", false);
                $('#check-all').prop('indeterminate', false)
                $('#aktifkan').text('0')
                $('#nonaktifkan').text('0')

                $("#check-all-ruang").prop("checked", false);
                $('#check-all-ruang').prop('indeterminate', false)
                $('#aktifkan-ruang').text('0')
                $('#nonaktifkan-ruang').text('0')

                $(".check-ruang-sesi").each(function () {
                    this.checked = false;
                });

                $(".check-siswa").each(function () {
                    this.checked = false;
                });

                findUncheckedRuang()
                findUnchecked()

                btnBySiswa.addClass('active')
                btnByRuang.removeClass('active')
            })

            var opsiRuang = $("#ruang");
            var opsiSesi = $("#sesi");

            opsiRuang.prepend("<option value='' selected='selected'>Pilih Ruang</option>");
            opsiSesi.prepend("<option value='' selected='selected'>Pilih Sesi</option>");

            opsiRuang.change(function () {
                opsiSesi.html("<option value='' selected='selected'>Pilih Sesi</option>");
                if ($(this).val()) {
                    $.each(arrRuang[$(this).val()], function (k, v) {
                        opsiSesi.append("<option value='" + k + "'>" + v.nama_sesi + "</option>");
                    })
                }
            });

            opsiSesi.change(function () {
                sesi = $(this).val();
                ruang = opsiRuang.val();
                loadSiswaRuang(ruang, $(this).val())
            })

            opsiRuang.select2({ width: '100%', theme: 'bootstrap4' });
            opsiSesi.select2({ width: '100%', theme: 'bootstrap4' });

            $("#table-body-ruang").on("change", ".check-ruang-sesi", function () {
                findUncheckedRuang();
            });

            $("#check-all-ruang").on("click", function () {
                const count = $('#table-body-ruang .check-ruang-sesi').length;
                if (count > 0) {
                    if (this.checked) {
                        $(".check-ruang-sesi").each(function () {
                            this.checked = true;
                        });
                    } else {
                        $(".check-ruang-sesi").each(function () {
                            this.checked = false;
                        });
                    }
                    findUncheckedRuang()
                }
            });

            $(".btn-action-ruang").on("click", function () {
                let action = $(this).data("action");

                Swal.fire({
                    title: action === 'aktifkan' ?
                        "Aktifan " + checkedRuang.length + " sesi" :
                        "Nonaktifkan " + checkedRuang.length + " sesi",
                    text: "",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Lanjutkan"
                }).then(result => {
                    if (result.value) {
                        $('#loading').removeClass('d-none');
                        Swal.fire({
                            title: action === 'aktifkan' ? "Mengaktifkan sesi terpilih" : "Menonaktifkan sesi terpilih",
                            text: "Silahkan tunggu....",
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        let formData = new FormData($('#bulkruang')[0]);
                        formData.append('active', action)

                        $.ajax({
                            url: base_url + "cbtactivate/aktifkan_sesi",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                $('#loading').addClass('d-none');
                                console.log("result", response);
                                Swal.fire({
                                    title: response.status ? "Sukses" : "Gagal",
                                    text: response.msg,
                                    icon: response.status ? "success" : "error"
                                }).then(result => {
                                    window.location.reload()
                                    //loadSiswaRuang($('#ruang').val(), $('#sesi').val())
                                });
                            },
                            error: function (xhr, status, error) {
                                console.log(xhr);
                                Swal.fire({
                                    title: "Gagal",
                                    html: xhr.responseText,
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });

        })

        function findUncheckedRuang() {
            uncheckedRuang = [];
            checkedRuang = [];
            const count = $('#table-body-ruang tr:visible .check-ruang-sesi').length;

            $("#table-body-ruang tr:visible .check-ruang-sesi:not(:checked)").each(function () {
                uncheckedRuang.push($(this).val());
            });
            $("#table-body-ruang tr:visible .check-ruang-sesi:checked").each(function () {
                checkedRuang.push($(this).val());
            });
            const countChecked = $("#table-body-ruang tr:visible .check-ruang-sesi:checked").length;

            if (countChecked === 0) {
                $("#check-all-ruang").prop("checked", false);
                $('#check-all-ruang').prop('indeterminate', false)
            } else if (countChecked === count) {
                $("#check-all-ruang").prop("checked", true);
                $('#check-all-ruang').prop('indeterminate', false)
            } else {
                $("#check-all-ruang").prop("checked", false);
                $('#check-all-ruang').prop('indeterminate', true)
            }
            $(".btn-action-ruang").attr('disabled', countChecked === 0);
            $('#aktifkan-ruang').text(checkedRuang.length)
            $('#nonaktifkan-ruang').text(checkedRuang.length)
        }

        function loadSiswaRuang(ruang, sesi) {
            var empty = ruang === '' || sesi === '';
            if (!empty) {
                url = base_url + "cbtactivate/getsiswaruang?ruang=" + ruang + '&sesi=' + sesi;
                refreshTable();
            } else {
                console.log('empty')
            }
        }

        function refreshTable() {
            $('#cari-siswa').val('')
            $('#loading').removeClass('d-none');
            setTimeout(function () {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (response) {
                        if (response.status) {
                            newResponse = response
                            createPreview(response.siswa)
                        } else {
                            Swal.fire({
                                title: "Gagal",
                                text: response.msg,
                                icon: "error"
                            }).then(result => {
                                $('#loading').addClass('d-none');
                                $('#check-all').prop("checked", false);
                                $('#check-all').prop('indeterminate', false)
                                $('#aktifkan').text('0')
                                $('#nonaktifkan').text('0')
                                $("#check-all-ruang").prop("checked", false);
                                $('#check-all-ruang').prop('indeterminate', false)
                                $('#aktifkan-ruang').text('0')
                                $('#nonaktifkan-ruang').text('0')
                            });
                        }
                    }
                });
            }, 500);
        }

        function createPreview(response) {
            $("#check-all").prop("checked", false);
            $('#check-all').prop('indeterminate', false)
            $('#aktifkan').text('0')
            $('#nonaktifkan').text('0')

            $("#check-all-ruang").prop("checked", false);
            $('#check-all-ruang').prop('indeterminate', false)
            $('#aktifkan-ruang').text('0')
            $('#nonaktifkan-ruang').text('0')

            console.log('response', response)
            var html = '';
            if (response.length > 0) {
                $.each(response, function (idx, siswa) {
                    response.sort((a, b) => a.nama.localeCompare(b.nama, undefined, { numeric: true }))
                    const kls = siswa.nama_kelas != null ? siswa.nama_kelas : '';
                    html += `<tr><td class="text-center align-middle">
                <input type="checkbox" name="ids[]" value="${siswa.id_siswa}" class="check check-siswa my-checkbox">
            </td>
            <td class="text-center align-middle">${idx + 1}</td>
            <td class="align-middle">${siswa.nis}</td>
            <td>
                <div class="media d-flex h-100">
                    ${loadImage(siswa.foto)}
                    <div class="media-body ms-2 justify-content-center align-self-center">
                       ${siswa.nama}<br />`
                    if (siswa.active == "0") {
                        html += `<span class="badge bg-danger">Nonaktif</span>`;
                    } else {
                        html += `<span class="badge bg-success">Aktif</span>`;
                    }
                    html += `</div></div>
            </td>
            <td class="text-center align-middle">${kls}</td>
            <td class="text-center align-middle">${siswa.username}</td>
            <td class="text-center align-middle">${siswa.password}</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-default btn-xs btn-reset" ${siswa.reset == '0' ? 'disabled' : ''} data-id="${siswa.id_siswa}" data-username="${siswa.username}" data-nama="${siswa.nama}" data-bs-toggle="tooltip" title="Reset Login"> <i class="bi bi-arrow-repeat text-xs text-red me-1 ms-1"></i></button>
            </td></tr>`;
                })
            } else {
                html += '<tr><td colspan="8" class="text-center align-middle">Tidak ada data siswa</td><tr>';
            }

            $('#table-siswa').removeClass('d-none')
            $('#table-body').html(html);
            $('#loading').addClass('d-none');

            $('#cari-siswa').quicksearch('table tbody tr')

            $("#table-body").on("change", ".check-siswa", function () {
                findUnchecked();
            });

            $("#check-all").on("click", function () {
                const count = $('#table-body .check-siswa').length;
                if (count > 0) {
                    if (this.checked) {
                        $(".check-siswa").each(function () {
                            this.checked = true;
                        });
                    } else {
                        $(".check-siswa").each(function () {
                            this.checked = false;
                        });
                    }
                    findUnchecked()
                }
            });

            $("#users").on("click", ".btn-reset", async function () {
                let username = $(this).data("username");
                let id = $(this).data("id");
                let nama = encodeURIComponent($(this).data("nama"));
                $('#loading').removeClass('d-none');

                let formActivate = new FormData($('#pager')[0])
                formActivate.append('id_siswa', id)
                formActivate.append('username', username)
                formActivate.append('nama', nama)
                const response = await ajaxPost('usersiswa/reset_login', formActivate)
                if (response.status) {
                    Swal.fire({
                        title: "Sukses",
                        html: "<b>" + decodeURIComponent(response.msg) + "<b>",
                        icon: "success"
                    }).then(result => {
                        loadSiswaRuang($('#ruang').val(), $('#sesi').val())
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        html: "<b>" + decodeURIComponent(response.msg) + "<b>",
                        icon: "error"
                    });
                }
                $('#loading').addClass('d-none');
            });

            $(".btn-action").on("click", function () {
                let action = $(this).data("action");

                Swal.fire({
                    title: action === 'aktifkan' ?
                        "Aktifan " + checkedSiswa.length + " siswa" :
                        "Nonaktifkan " + checkedSiswa.length + " siswa",
                    text: "",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Lanjutkan"
                }).then(result => {
                    if (result.value) {
                        $('#loading').removeClass('d-none');
                        Swal.fire({
                            title: action === 'aktifkan' ? "Mengaktifkan siswa terpilih" : "Menonaktifkan siswa terpilih",
                            text: "Silahkan tunggu....",
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        let formData = new FormData($('#bulk')[0])
                        for (const idsiswa of checkedSiswa) {
                            formData.append("ids[]", idsiswa)
                        }
                        formData.append('active', action)

                        $.ajax({
                            url: base_url + "cbtactivate/aktifkan_semua",
                            method: "POST",
                            processData: false,
                            contentType: false,
                            data: formData,
                            success: function (response) {
                                $('#loading').addClass('d-none');
                                //console.log("result", response);
                                Swal.fire({
                                    title: response.status ? "Sukses" : "Gagal",
                                    text: response.msg,
                                    icon: response.status ? "success" : "error"
                                }).then(result => {
                                    loadSiswaRuang($('#ruang').val(), $('#sesi').val())
                                });
                            },
                            error: function (xhr, status, error) {
                                console.log(xhr);
                                Swal.fire({
                                    title: "Gagal",
                                    html: xhr.responseText,
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });
        }

        function findUnchecked() {
            uncheckedSiswa = [];
            checkedSiswa = [];
            const count = $('#table-body tr:visible .check-siswa').length;

            $("#table-body tr:visible .check-siswa:not(:checked)").each(function () {
                uncheckedSiswa.push($(this).val());
            });
            $("#table-body tr:visible .check-siswa:checked").each(function () {
                checkedSiswa.push($(this).val());
            });
            const countChecked = $("#table-body tr:visible .check-siswa:checked").length;

            $("#check-all").prop({
                checked: countChecked === count,
                indeterminate: countChecked > 0 && countChecked < count
            });

            //console.log('checked', checkedSiswa.length, uncheckedSiswa.length)
            $(".btn-action").attr('disabled', countChecked === 0);
            $('#aktifkan').text(checkedSiswa.length)
            $('#nonaktifkan').text(checkedSiswa.length)
        }

        function loadImage(srcImg) {
            let temp = document.createElement('div')
            temp.innerHTML = `<img class="avatar img-circle justify-content-center align-self-center" src="${base_url + srcImg}" width="36" height="36">`
            const defSrc = base_url + 'assets/img/siswa.png'

            const imgs = temp.getElementsByTagName('img')
            imgs[0].setAttribute('alt', 'foto');
            let src = imgs[0].getAttribute('src');
            if (src.startsWith('profiles')) {
                src = src.replace('profiles', 'foto_siswa');
                imgs[0].setAttribute("src", src);
            }
            imgs[0].setAttribute('onerror', 'javascript:this.src="' + defSrc + '"');
            return temp.innerHTML
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
                            $('#loading').addClass('d-none');
                            console.log(xhr);
                            Swal.fire({
                                title: "Gagal",
                                html: xhr.responseText,
                                icon: "error"
                            });
                            reject(error);
                        }
                    });
                }, 500)
            });
        }

    </script>