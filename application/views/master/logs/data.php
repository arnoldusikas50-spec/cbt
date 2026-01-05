<main class="app-main bg-white pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary my-shadow fade-in-up mb-4">
                <div class="card-header">
                    <div class="card-title">
                        Log Aktivitas
                    </div>
                    <div class="card-tools ms-auto d-flex align-items-center">
                        <button onclick="window.history.back();" type="button"
                            class="btn btn-sm btn-outline-secondary ms-1">
                            <i class="bi bi-arrow-left-square"></i><span
                                class="d-none d-sm-inline-block ms-1">Kembali</span>
                        </button>
                        <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-warning ms-1"><i
                                class="bi bi-arrow-repeat"></i> <span
                                class="d-none d-sm-inline-block ms-1">Reload</span>
                        </button>
                        <?php if ($is_admin): ?>
                            <button type="button" onclick="hapusLogAktivitas()" class="btn btn-sm btn-danger ms-1">
                                <i class="bi bi-trash"></i> Hapus semua
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php
                    $totalLogs = $counts['total'] ?? 0;
                    $totalGuru = $counts['guru'] ?? 0;
                    $totalSiswa = $counts['siswa'] ?? 0;
                    $totalAdmin = $counts['admin'] ?? 0;
                    ?>
                    <div class="mb-3 p-3">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan Aktivitas Pengguna</h5>
                                <div class="text-muted">Pantau interaksi admin, guru, dan siswa dalam sistem</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm gap-2">
                                <span class="badge bg-primary px-3 py-2 text-sm">Total Log:
                                    <?= $totalLogs ?></span>
                                <span class="badge bg-info px-3 py-2 text-sm">Log Guru:
                                    <?= $totalGuru ?></span>
                                <span class="badge bg-success px-3 py-2 text-sm">Log Siswa:
                                    <?= $totalSiswa ?></span>
                                <?php if ($is_admin): ?>
                                    <span class="badge bg-warning px-3 py-2 text-sm">Log Admin: <?= $totalAdmin ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 row">
                        <div class="col-6">
                            <label>
                                Filter
                            </label>
                            <select name="filterby" id="filterby" class="form-control form-control-sm"
                                multiple="multiple">
                                <?php if ($is_admin): ?>
                                    <option value="1">Admin</option>
                                <?php endif; ?>
                                <option value="2">Guru</option>
                                <option value="3">Siswa</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>
                                Search
                            </label>
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-sm" type="text" id="query">
                                <div class="input-group-append ms-1">
                                    <button class="btn btn-primary" id="searchby">
                                        <i class="bi bi-search me-1"></i> Cari</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 60px;height: 40px"></th>
                                    <th class="align-middle">User</th>
                                    <th class="align-middle">Aktivitas</th>
                                    <?php if ($is_admin): ?>
                                        <th class="text-center align-middle">Aksi</th>
                                        <th class="text-center"><button class="btn btn-sm btn-danger"
                                                id="del-selected">Hapus
                                                Terpilih</button></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="log-list"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <script>
        var colorBg = ['success', 'success', 'secondary', 'primary', 'warning', 'danger',
            'primary', "warning", "primary", "success", "primary", "success", "warning"
        ];
        const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;
        let limit = 10;
        let filter = isAdmin ? ['1', '2', '3'] : ['2', '3'];
        let loaded = 0;
        let total = 0;
        let search = '';

        $(document).ready(function () {
            load_log();

            var filterBy = $("#filterby");
            filterBy.select2({ width: '100%', placeholder: "Pilih filter", allowClear: true });

            filterBy.on("select2:clear", function (evt) {
                $(this).on("select2:opening.cancelOpen", function (evt) {
                    evt.preventDefault();
                    $(this).off("select2:opening.cancelOpen");
                });
            });

            filterBy.change(function () {
                filter = $(this).val();
                loaded = 0;
                load_log()
            });

            $('#query').on('input', function (e) {
                search = $(this).val();
                if (search.trim().length === 0) {
                    loaded = 0;
                    load_log()
                }
            });

            $('#searchby').on('click', function () {
                search = $('#query').val();
                loaded = 0;
                load_log()
            });

            $('#del-selected').on('click', function () {
                const checked = $("#log-list tr:visible .my-checkbox:checked");
                if (checked.length === 0) {
                    Swal.fire({
                        title: "Peringatan",
                        text: "Tidak ada data yang dipilih",
                        icon: "warning",
                        showCancelButton: false,
                    });
                    return;
                }

                Swal.fire({
                    title: "Hapus Log Terpilih",
                    text: "Log aktifitas yang dipilih akan dihapus",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonText: "HAPUS"
                }).then(result => {
                    if (result.value) {
                        const ids = [];
                        checked.each(function () {
                            ids.push($(this).val());
                        });
                        console.log('ids', ids);

                        $.ajax({
                            url: base_url + "dashboard/hapuslog/selected",
                            type: "GET",
                            data: { ids: ids },
                            success: function (data) {
                                console.log("result", data);
                                if (data) {
                                    Swal.fire({
                                        title: "Sukses",
                                        text: "Log dihapus",
                                        icon: "success",
                                        showCancelButton: false,
                                    }).then(result => {
                                        loaded -= ids.length;
                                        $('#loaded').text(`${loaded}/${total}`);
                                        checked.each(function () {
                                            const tr = $(this).closest('tr');
                                            tr.css('background-color', '#f8d7da')
                                                .children('td') // ambil semua cell
                                                .wrapInner('<div style="display:block;" />') // bungkus isi cell
                                                .children()
                                                .slideUp(200, function () {
                                                    tr.remove();
                                                });
                                        });
                                    });
                                } else {
                                    Swal.fire({
                                        title: "ERROR",
                                        text: "Gagal menghapus log",
                                        icon: "error",
                                        showCancelButton: false,
                                    });
                                }
                            }, error: function (xhr, status, error) {
                                console.log("error", xhr.responseText);
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Gagal menghapus log",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        });
                    }
                });
            });
        });

        function formatTanggal(date) {
            var dateArray = date.split(' '),
                year = dateArray[0].split('-')[0],
                month = dateArray[0].split('-')[1],
                day = dateArray[0].split('-')[2],
                hour = dateArray[1].split(':')[0],
                minutes = dateArray[1].split(':')[1];

            var hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

            var d = new Date(month + "/" + day + "/" + year);
            var curr_day = d.getDay();
            var curr_date = d.getDate();

            var curr_month = d.getMonth();
            var curr_year = d.getFullYear();

            return hari[curr_day] + ", " + curr_date + "  " + bulan[curr_month] + " " + curr_year + " " + hour + ":" + minutes;
        }

        function load_log(page = 1) {
            if (page > 1 && loaded > 0) {
                $('#next-page').html('<td class="alert-default-warning align-middle text-center py-3" colspan="5">' +
                    '<i class="bi bi-spinner fa-spin"></i> Memuat data selanjutnya...</td>');
            } else {
                $('#log-list').html('<tr><td class="alert-default-warning align-middle text-center py-3" colspan="5">' +
                    '<i class="bi bi-spinner fa-spin"></i> Memuat data...</td></tr>');
            }
            setTimeout(function () {
                $.ajax({
                    url: base_url + "dashboard/getlog",
                    data: { 'page': page, 'limit': limit, 'filter': filter, 'search': search },
                    method: "GET",
                    success: function (data) {
                        addList(page, data)
                    }
                });
            }, 500);
        }

        function addList(page, data) {
            const len = data.logs.length;
            loaded += len;
            if (len > 0) {
                let body = '';
                $.each(data.logs, function (key, value) {
                    body += `<tr data-log="${value.id_log}">`;

                    const nama_lengkap = value?.first_name === value?.last_name ? value?.first_name : value?.first_name + ' ' + value?.last_name
                    var nama = value.id_group === '1' ? value.first_name : nama_lengkap;
                    var clr = colorBg[value.log_type];
                    var tgl = formatTanggal(value.log_time);//new Date('02/12/2018');
                    const guru = data.guru.find(g => g.id === value.id_user);
                    const siswa = data.siswa.find(s => s.id === value.id_user);
                    const admin = data.admins.find(a => a.id === value.id_user);
                    const fotoUser = guru ? guru.foto :
                        siswa ? siswa.foto :
                            admin ? admin.foto : null;
                    const foto = base_url + (fotoUser ? fotoUser : '');
                    const initial = nama.charAt(0).toUpperCase();

                    body += `<td>
                            <div data-siswa="${initial}" class="d-flex justify-content-center align-items-center bg-${clr} rounded-circle" style="width: 50px; height: 50px">
                            <img class="avatar-log img-circle" src="${foto}" width="44" height="44" alt="" /></div></td>`;
                    body += `<td class="align-middle">${nama}</td>`;
                    body += `<td class="align-middle">
                            <span class="text-${clr} text-sm">${value.log_desc}</span>
                            <br /><span class="text-xs text-muted">${tgl}</span>
                         </td>`;
                    if (isAdmin) {
                        body += `<td class="align-middle text-center"><button class="btn btn-sm btn-danger" onclick="hapusLog(this)"><i class="bi bi-trash"></i></button></td>`;
                        body += `<td class="align-middle text-center"><input class="check my-checkbox" type="checkbox" name="check[]" value="${value.id_log}"></td>`;
                    }
                    body += '</tr>'
                })

                total = data.total;
                body += `<tr id="next-page" class="bg-light"><td class="align-middle text-center py-3" colspan="4">
                        <div class="d-flex flex-row">
                            <span id="loaded">${loaded}/${total}</span>`;
                if (loaded < total) {
                    body += `<span style="cursor: pointer;" id="load-page" class="mx-auto">
                            <i class="bi bi-arrow-down"></i> Muat data selanjutnya</span>`;
                } else {
                    body += `<span class="mx-auto alert alert-default-success"> Semua data sudah dimuat</span>`;

                }
                body += `</div></td><tr>`;

                if (page > 1) {
                    $('#next-page').remove();
                    $('#log-list').append(body);
                } else {
                    $('#log-list').html(body);
                }

                $(".avatar-log").each(async function (i, v) {
                    const loaded = () => {
                        return new Promise((resolve) => {
                            let obj = { pos: i };

                            $(v)
                                .on("error", function () {
                                    obj.error = true;
                                    const div = $(this).parent();
                                    const initial = div.data('siswa')
                                    $(this).remove()
                                    div.html(`<span class="text-lg">${initial}</span>`);
                                    resolve(obj);
                                })
                                .on("load", function () {
                                    obj.error = false;

                                    // ==== Resize gambar dengan rasio asli ====
                                    const canvas = document.createElement("canvas");
                                    const ctx = canvas.getContext("2d");

                                    const targetW = 100; // lebar tetap
                                    const ratio = v.naturalHeight / v.naturalWidth;
                                    const targetH = Math.round(targetW * ratio);

                                    canvas.width = targetW;
                                    canvas.height = targetH;

                                    ctx.drawImage(v, 0, 0, targetW, targetH);

                                    // Convert ke PNG
                                    v.src = canvas.toDataURL("image/png");

                                    resolve(obj);
                                });
                        });
                    };

                    await loaded();
                });

                $('#load-page').off('click').on('click', function () {
                    const nextPage = page + 1;
                    load_log(nextPage);
                });
            } else {
                $('#log-list').html('<tr><td class="align-middle text-center py-3" colspan="4">Tidak ada data</td></tr>');
            }
        }

        function hapusLog(e) {
            const tr = $(e).closest('tr');
            const id = tr.data('log');
            console.log('log', id);

            Swal.fire({
                title: "Hapus Log",
                text: "Log aktifitas akan dihapus",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "HAPUS"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + `dashboard/hapuslog/${id}`,
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Log dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    loaded -= 1;
                                    $('#loaded').text(`${loaded}/${total}`);
                                    tr.css('background-color', '#f8d7da')
                                        .children('td') // ambil semua cell
                                        .wrapInner('<div style="display:block;" />') // bungkus isi cell
                                        .children()
                                        .slideUp(200, function () {
                                            tr.remove();
                                        });
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Gagal menghapus log",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            Swal.fire({
                                title: "ERROR",
                                text: "Gagal menghapus log",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            });
        }

        function hapusLogAktivitas() {
            Swal.fire({
                title: "Hapus Log",
                text: "Semua log aktifitas akan dihapus",
                icon: "info",
                showCancelButton: true,
                confirmButtonText: "HAPUS"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "dashboard/hapuslog/all",
                        type: "GET",
                        success: function (data) {
                            console.log("result", data);
                            if (data) {
                                Swal.fire({
                                    title: "Sukses",
                                    text: "Semua log dihapus",
                                    icon: "success",
                                    showCancelButton: false,
                                }).then(result => {
                                    if (result.value) {
                                        window.location.reload(true);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "ERROR",
                                    text: "Gagal menghapus log",
                                    icon: "error",
                                    showCancelButton: false,
                                });
                            }
                        }, error: function (xhr, status, error) {
                            console.log("error", xhr.responseText);
                            Swal.fire({
                                title: "ERROR",
                                text: "Gagal menghapus log",
                                icon: "error",
                                showCancelButton: false,
                            });
                        }
                    });
                }
            });
        }

    </script>