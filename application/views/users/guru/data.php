<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $judul ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom">
                    <h3 class="card-title">Master <?= $subjudul ?></h3>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <button type="button" onclick="getDataGuru()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </button>
                        <button type="button" class="btn btn-action btn-success btn-sm shadow-sm" data-action="aktifkan"
                            data-bs-toggle="tooltip" title="Aktifkan Semua">
                            <i class="bi bi-persons me-1"></i> Aktifkan Semua
                        </button>
                        <button type="button" class="btn btn-action btn-danger btn-sm shadow-sm"
                            data-action="nonaktifkan" data-bs-toggle="tooltip" title="Nonaktifkan Semua">
                            <i class="bi bi-ban me-1"></i> Nonaktifkan Semua
                        </button>
                    </div>
                </div>
                <div class="card-body">
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

    <script src="<?= base_url() ?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
    <script type="text/javascript">
        const user_id = '<?= $user->id ?>';
        let table;

        $(document).ready(function () {
            ajaxcsrf();

            $("#users").on("click", ".btn-reset", function () {
                $('#loading').removeClass('d-none');
                let username = $(this).data("username");
                let nama = $(this).data("nama");
                $.ajax({
                    url: base_url + "userguru/reset_login?username=" + username,
                    type: "GET",
                    success: function (response) {
                        $('#loading').addClass('d-none');
                        if (response.msg) {
                            if (response.status) {
                                Swal.fire({
                                    title: "Sukses",
                                    html: "<b>" + nama + response.msg + "<b>",
                                    icon: "success"
                                });
                                getDataGuru()
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    html: "<b>" + nama + response.msg + "<b>",
                                    icon: "error"
                                });
                            }
                        }
                    },
                    error: function (xhr, status, error) {
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

            $(".btn-action").on("click", function () {
                console.log('click');
                let action = $(this).data("action");
                Swal.fire({
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
                        await ajaxPost(base_url + "userguru/aktifkansemua", formActivate, function (error, response) {
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
                        Swal.fire({
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
            ajaxGet(base_url + 'userguru/data', function (error, response) {
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
            table.className = "w-100 table table-hover table-striped border-bottom";

            const thead = document.createElement("thead");
            thead.className = "bg-primary text-white";
            const headerRow = document.createElement("tr");
            const headers = ["No.", "Nama", "Username", "Password", "Jabatan", "Reset Login", "Status", "Aksi"];

            headers.forEach((headerText, index) => {
                const th = document.createElement("th");
                if (headerText === 'No.') th.style.maxWidth = '40px'
                th.className = "text-center align-middle border-0 py-3";
                if (index === 0) th.classList.add("rounded-start");
                if (index === headers.length - 1) th.classList.add("rounded-end");

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
                cellName.innerHTML = `<div class="d-flex align-items-center h-100">
                    <img class="avatar rounded-circle justify-content-center align-self-center" src="${guru.foto}" width="40" height="40" alt="User Image">
                        <div class="ms-2 justify-content-center align-self-center">${guru.nama_guru}</div>
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
                resetButton.className = `btn btn-reset btn-primary rounded-pill border btn-sm shadow-sm ${no_reset ? 'btn-disabled' : ''}`;
                resetButton.setAttribute("data-bs-toggle", "tooltip");
                resetButton.setAttribute("title", "Reset Login");
                resetButton.onclick = () => resetLogin(guru.id, guru.id_guru, guru.nama_guru, guru.username);
                if (no_reset) {
                    resetButton.setAttribute("disabled", "disabled");
                }
                const icon = document.createElement("i");
                icon.className = "bi bi-arrow-repeat text-danger";
                resetButton.appendChild(icon)
                cellReset.appendChild(resetButton);
                row.appendChild(cellReset);

                // Status
                const cellStatus = document.createElement("td");
                cellStatus.className = `text-center align-middle`;
                const badge = document.createElement("span");
                badge.className = `badge ${guru.active === "1" ? 'bg-success' : 'bg-danger'}`;
                badge.textContent = guru.active === "1" ? 'Aktif' : 'Nonaktif';
                cellStatus.appendChild(badge);
                row.appendChild(cellStatus);

                // Aksi
                const cellAksi = document.createElement("td");
                cellAksi.className = "text-center align-middle";

                const aksiButton = document.createElement("button");
                aksiButton.type = "button";
                aksiButton.className = `btn btn-sm shadow-sm rounded-pill px-3`;
                //aksiButton.textContent = guru.active ==="1" ? ' Nonaktifkan ' : ' Aktifkan '
                aksiButton.onclick = () => aktifkan(guru.id, guru.id_guru, guru.nama_guru, guru.active === "1" ? 'nonaktifkan' : 'aktifkan');
                aksiButton.setAttribute("data-bs-toggle", "tooltip");

                const iconAksi = document.createElement("i");
                if (guru.active === "1") {
                    aksiButton.setAttribute("title", "Nonaktifkan");
                    aksiButton.classList.add('btn-danger')
                    iconAksi.className = "bi bi-ban";
                } else {
                    aksiButton.setAttribute("title", "Aktifkan");
                    aksiButton.classList.add('btn-success')
                    iconAksi.className = "bi bi-person-plus";
                }
                aksiButton.appendChild(iconAksi)
                cellAksi.appendChild(aksiButton);
                row.appendChild(cellAksi);

                tbody.appendChild(row);
                num++;
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
            ajaxPost('userguru/activate', formActivate, function (error, response) {
                $('#loading').addClass('d-none');
                if (error) {
                    Swal.fire({
                        title: "Error",
                        html: error.message,
                        icon: "error"
                    });
                } else {
                    console.log('response_e', response);
                    if (response.status) {
                        Swal.fire({
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

                        Swal.fire({
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
            ajaxPost('userguru/reset_login', formActivate, function (error, response) {
                $('#loading').addClass('d-none');
                if (error) {
                    Swal.fire({
                        title: "Error",
                        text: error.message,
                        icon: "error"
                    });
                } else {
                    console.log('response', response);
                    if (response.status) {
                        Swal.fire({
                            title: "Sukses",
                            text: response.msg,
                            icon: "success"
                        }).then(result => {
                            getDataGuru()
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: response.msg,
                            icon: "error"
                        });
                    }
                }
            })
        }
    </script>