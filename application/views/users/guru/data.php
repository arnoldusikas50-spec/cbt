<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-header with-border">
                    <h3 class="card-title">Master <?= $subjudul ?></h3>
                    <div class="card-tools">
                        <button type="button" onclick="getDataGuru()" class="btn btn-sm btn-warning">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button type="button" class="btn btn-action btn-success btn-sm" data-action="aktifkan"
                                data-toggle="tooltip" title="Aktifkan">
                            <i class="fa fa-users m-1"></i><span
                                    class="d-none d-sm-inline-block ml-1">Aktifkan Semua</span>
                        </button>
                        <button type="button" class="btn btn-action btn-danger btn-sm" data-action="nonaktifkan"
                                data-toggle="tooltip" title="Nonaktifkan">
                            <i class="fa fa-ban m-1"></i><span
                                    class="d-none d-sm-inline-block ml-1">Nonaktifkan Semua</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $tpLabel = isset($tp_active->tahun) ? $tp_active->tahun : '-';
                    $smtLabel = isset($smt_active->smt) ? $smt_active->smt : '-';
                    ?>
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan User Guru</h5>
                                <div class="text-muted">Tahun Pelajaran <?= $tpLabel ?> · Semester <?= $smtLabel ?></div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Guru: <span id="badge-total-guru">0</span></span>
                                <span class="badge badge-success px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Aktif: <span id="badge-total-aktif">0</span></span>
                                <span class="badge badge-warning px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Nonaktif: <span id="badge-total-nonaktif">0</span></span>
                                <span class="badge badge-info px-3 py-2 text-sm">Perlu Reset Login: <span id="badge-total-reset">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Manajemen User Guru</h5>
                        <ul class="pl-3 mb-0">
                            <li>Gunakan tombol <strong>Reload</strong> untuk memuat ulang daftar guru terbaru sebelum melakukan aksi massal.</li>
                            <li>Tekan tombol <strong>Aktifkan/Nonaktifkan Semua</strong> untuk mengubah status bulk; pastikan tidak ada username duplikat.</li>
                            <li>Manfaatkan tombol <strong>Reset Login</strong> untuk menghapus percobaan login yang gagal sebelum memberikan akses ulang.</li>
                        </ul>
                    </div>

                    <div class="table-responsive" id="table-guru">
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
<?= form_open('', array('id' => 'up')) ?>
<?= form_close(); ?>

<script src="<?=base_url()?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
<script type="text/javascript">
    const user_id = '<?=$user->id?>';
    let table;

    $(document).ready(function() {
        ajaxcsrf();

        $("#users").on("click", ".btn-reset", function() {
            $('#loading').removeClass('d-none');
            let username = $(this).data("username");
            let nama = $(this).data("nama");
            $.ajax({
                url: base_url + "userguru/reset_login?username="+ username,
                type: "GET",
                success: function (response) {
                    $('#loading').addClass('d-none');
                    if (response.msg) {
                        if (response.status) {
                            swal.fire({
                                title: "Sukses",
                                html: "<b>"+nama+ response.msg+"<b>",
                                icon: "success"
                            });
                            getDataGuru()
                        } else {
                            swal.fire({
                                title: "Error",
                                html: "<b>"+nama + response.msg+"<b>",
                                icon: "error"
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading').addClass('d-none');
                    console.log(xhr);
                    Swal.fire({
                        title: "Gagal",
                        html: xhr.responseText,
                        icon: "error"
                    });
                }
            });
        });

        $(".btn-action").on("click", function() {
            console.log('click');
            let action = $(this).data("action");
            swal.fire({
                title: action === 'aktifkan' ? "Aktifan Semua Guru" : "Nonaktifkan Semua Guru",
                text: "",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Lanjutkan"
            }).then(async result => {
                if (result.value) {
                    $('#loading').removeClass('d-none');
                    let formActivate = new FormData($('#up')[0])
                    formActivate.append('is_active', action)
                    await ajaxPost(base_url + "userguru/aktifkansemua", formActivate, function(error, response) {
                        $('#loading').addClass('d-none');
                        if (error) {
                            console.log('error', error.message);
                        } else {
                            console.log('response', response);
                            if (response.status) {
                                updateUser()
                            }
                        }
                    });
                }
            });

        });

        getDataGuru()
    });

    async function updateUser() {
        let formActivate = new FormData($('#up')[0])
        await ajaxPost(base_url + "userguru/update_iduser", formActivate, function (error, response) {
            if (error) {
                console.log('error', error.message);
            } else {
                console.log('response', response);
                if (response.status) {
                    swal.fire({
                        title: response.status ? "Sukses" : "Gagal",
                        html: response.msg,
                        icon: response.status ? "success" : "error"
                    }).then(result => {
                        getDataGuru()
                    });
                }
            }
        });
    }

    function getDataGuru() {
        $('#loading').removeClass('d-none');
        ajaxGet(base_url + 'userguru/data', function(error, response) {
            if (error) {
                console.log('error', error.message);
            } else {
                console.log('response', response);
                populateTable(response)
            }
        });
    }

    function populateTable(data) {
        const tableContainer = document.getElementById("table-guru");
        tableContainer.innerHTML = '';

        const table = document.createElement("table");
        table.id = "users";
        table.className = "w-100 table table-sm table-striped table-bordered table-hover";

        const thead = document.createElement("thead");
        const headerRow = document.createElement("tr");
        const headers = ["No.", "Nama", "Username", "Password", "Jabatan", "Reset Login", "Status", "Aksi"];

        headers.forEach(headerText => {
            const th = document.createElement("th");
            if (headerText === 'No.') th.style.maxWidth = '40px'
            th.className = "text-center align-middle alert-default-primary";
            th.textContent = headerText;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement("tbody");
        let num = 1;
        data.forEach(guru => {
            const row = document.createElement("tr");
            // nomor
            const cellNum = document.createElement("td");
            cellNum.className = "text-center align-middle";
            cellNum.textContent = num.toString();
            row.appendChild(cellNum);

            // nama
            const cellName = document.createElement("td");
            cellName.className = "align-middle";
            cellName.innerHTML = `<div class="media d-flex h-100">
                    <img class="avatar img-circle justify-content-center align-self-center" src="${guru.foto}" width="40" height="40" alt="User Image">
                        <div class="media-body ml-2 justify-content-center align-self-center">${guru.nama_guru}</div>
                </div>`;
            row.appendChild(cellName);

            // Username
            const cellUsername = document.createElement("td");
            cellUsername.className = "text-center align-middle";
            cellUsername.textContent = guru.username;
            row.appendChild(cellUsername);

            // password
            const cellPass = document.createElement("td");
            cellPass.className = "text-center align-middle";
            cellPass.textContent = guru.password;
            row.appendChild(cellPass);

            // Jabatan
            const cellLvl = document.createElement("td");
            cellLvl.className = "text-center align-middle";
            cellLvl.textContent = guru.level;
            row.appendChild(cellLvl);

            // Reset
            const cellReset = document.createElement("td");
            cellReset.className = "text-center align-middle";
            const resetButton = document.createElement("button");
            resetButton.type = "button";
            const no_reset = guru['reset'] === '0'
            resetButton.className = `btn btn-reset btn-default btn-xs text-red ${no_reset ? 'btn-disabled' : ''}`;
            resetButton.setAttribute("data-toggle", "tooltip");
            resetButton.setAttribute("title", "Reset Login");
            resetButton.onclick = () => resetLogin(guru.id, guru.id_guru, guru.nama_guru, guru.username);
            if (no_reset) {
                resetButton.setAttribute("disabled", "disabled");
            }
            const icon = document.createElement("i");
            icon.className = "fa fa-sync m-1";
            resetButton.appendChild(icon)
            cellReset.appendChild(resetButton);
            row.appendChild(cellReset);

            // Status
            const cellStatus = document.createElement("td");
            cellStatus.className = `text-bold text-center align-middle ${guru.active === "1" ? 'text-success' : 'text-danger'}`;
            cellStatus.textContent = guru.active === "1" ? 'Aktif' : 'Nonaktif';
            row.appendChild(cellStatus);

            // Aksi
            const cellAksi = document.createElement("td");
            cellAksi.className = "text-center align-middle";

            const aksiButton = document.createElement("button");
            aksiButton.type = "button";
            aksiButton.className = `btn btn-sm`;
            aksiButton.textContent = guru.active ==="1" ? ' Nonaktifkan ' : ' Aktifkan '
            aksiButton.onclick = () => aktifkan(guru.id, guru.id_guru, guru.nama_guru, guru.active ==="1" ? 'nonaktifkan' : 'aktifkan');
            aksiButton.setAttribute("data-toggle", "tooltip");

            const iconAksi = document.createElement("i");
            if (guru.active ==="1") {
                aksiButton.setAttribute("title", "Nonaktifkan");
                aksiButton.classList.add('btn-danger')
                aksiButton.classList.add('btn-aktif')
                iconAksi.className = "fa fa-ban";
            } else {
                aksiButton.setAttribute("title", "Aktifkan");
                aksiButton.classList.add('btn-success')
                aksiButton.classList.add('btn-nonaktif')
                iconAksi.className = "fa fa-user-plus";
            }
            aksiButton.prepend(iconAksi)
            cellAksi.appendChild(aksiButton);
            row.appendChild(cellAksi);

            tbody.appendChild(row);
            num ++;
        });

        table.appendChild(tbody);
        document.getElementById("table-guru").appendChild(table);

        $("#users").DataTable();
        $('#loading').addClass('d-none');
    }

    function aktifkan(id, id_guru, nama_guru, isActive) {
        console.log(id, id_guru, nama_guru, isActive)
        $('#loading').removeClass('d-none');
        let formActivate = new FormData($('#up')[0])
        formActivate.append('is_active', isActive)
        if (id) formActivate.append('id', id)
        formActivate.append('id_guru', id_guru)
        formActivate.append('nama', nama_guru)
        ajaxPost('userguru/activate', formActivate, function(error, response) {
            $('#loading').addClass('d-none');
            if (error) {
                swal.fire({
                    title: "Error",
                    html: error.message,
                    icon: "error"
                });
            } else {
                console.log('response_e', response);
                if (response.status) {
                    swal.fire({
                        title: "Sukses",
                        text: response.msg,
                        icon: "success"
                    }).then(result => {
                        getDataGuru()
                    });
                } else {
                    let errs = response.msg;
                    if (response.users) {
                        for (const user of response.users) {
                            errs += `<br><b>- ${user.nama_guru}</b>`;
                        }
                    }

                    swal.fire({
                        title: "Error",
                        html: errs,
                        icon: "error"
                    });
                }
            }
        })
    }

    function resetLogin(id, id_guru, nama_guru, username) {
        $('#loading').removeClass('d-none');
        let formActivate = new FormData($('#up')[0])
        formActivate.append('username', username)
        formActivate.append('id', id)
        formActivate.append('id_guru', id_guru)
        formActivate.append('nama', nama_guru)
        ajaxPost('userguru/reset_login', formActivate, function(error, response) {
            $('#loading').addClass('d-none');
            if (error) {
                swal.fire({
                    title: "Error",
                    text: error.message,
                    icon: "error"
                });
            } else {
                console.log('response', response);
                if (response.status) {
                    swal.fire({
                        title: "Sukses",
                        text: response.msg,
                        icon: "success"
                    }).then(result => {
                        getDataGuru()
                    });
                } else {
                    swal.fire({
                        title: "Error",
                        text: response.msg,
                        icon: "error"
                    });
                }
            }
        })
    }
</script>