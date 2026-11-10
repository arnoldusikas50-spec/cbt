<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-gear text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-default my-shadow mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-toggle-on me-2"></i><?= $subjudul ?></h6>
            </div>
            <div class="card-body">
                <div class="alert alert-default-info border-info d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill fs-4"></i>
                    <div>
                        Hanya siswa dengan status <b>Aktif</b> yang dapat login mengikuti ujian.
                        Lakukan aktivasi sebelum sesi dimulai. Siswa tanpa akun otomatis dibuat saat diaktifkan.
                    </div>
                </div>

                <div class="row g-3 mb-3 align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="ruang" class="form-label">Ruang Ujian</label>
                        <select name="ruang" id="ruang" class="form-control form-control-sm select2"></select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="sesi" class="form-label">Sesi Ujian</label>
                        <select name="sesi" id="sesi" class="form-control form-control-sm select2"></select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-aktifkan" class="btn btn-success btn-sm w-100" disabled>
                                <i class="bi bi-person-check me-1"></i>Aktifkan Terpilih (<span id="cnt-aktifkan">0</span>)
                            </button>
                            <button type="button" id="btn-nonaktifkan" class="btn btn-danger btn-sm w-100" disabled>
                                <i class="bi bi-ban me-1"></i>Nonaktifkan (<span id="cnt-nonaktifkan">0</span>)
                            </button>
                        </div>
                    </div>
                </div>

                <div id="empty-state" class="alert alert-default-danger border-danger d-none">
                    Belum ada siswa yang tergabung dalam sesi terpilih.
                </div>

                <div class="table-responsive d-none" id="table-wrapper">
                    <table id="users" class="w-100 table table-striped table-bordered table-hover">
                        <thead class="alert-default-primary">
                            <tr>
                                <th class="text-center align-middle" style="width: 40px">
                                    <input class="form-check-input" type="checkbox" id="check-all">
                                </th>
                                <th class="text-center" style="width: 40px">No</th>
                                <th class="text-center">NIS</th>
                                <th>Nama</th>
                                <th class="text-center">Kelas</th>
                                <th class="text-center">Username</th>
                                <th class="text-center">Password</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="overlay d-none" id="loading">
                <button class="btn btn-primary" type="button" disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Loading...
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    const arrRuang = JSON.parse('<?= json_encode($ruangs) ?>');
    let dt = null;
    let currentRuang = null;
    let currentSesi = null;
    let checkedSiswa = [];

    function setLoading(show) {
        $('#loading').toggleClass('d-none', !show);
    }

    function initRuangDropdown() {
        const opsiRuang = $("#ruang");
        opsiRuang.html('<option value="" selected>Pilih Ruang</option>');
        if (Object.keys(arrRuang).length > 0) {
            Object.keys(arrRuang).forEach(ruangId => {
                const first = arrRuang[ruangId][0];
                const label = first && first.kode_ruang ? first.kode_ruang : 'Ruang ' + ruangId;
                opsiRuang.append(`<option value="${ruangId}">${label}</option>`);
            });
        }
        opsiRuang.select2({ width: '100%', theme: 'bootstrap4' });
    }

    function initSesiDropdown() {
        const opsiSesi = $("#sesi");
        opsiSesi.html('<option value="" selected>Pilih Sesi</option>');
        if (currentRuang && arrRuang[currentRuang]) {
            arrRuang[currentRuang].forEach(sesi => {
                opsiSesi.append(`<option value="${sesi.sesi_id}">${sesi.kode_sesi || sesi.nama_sesi}</option>`);
            });
        }
        opsiSesi.select2({ width: '100%', theme: 'bootstrap4' });
    }

    function initTable() {
        if (dt) {
            dt.destroy();
        }
        dt = $('#users').DataTable({
            language: dt_lang_id || {},
            pageLength: 100,
            lengthMenu: [[50, 100, 200, -1], [50, 100, 200, 'Semua']],
            columnDefs: [
                { targets: [0, 8], orderable: false, searchable: false },
                { targets: [6, 7, 8], className: 'text-center align-middle' },
                { targets: [1, 2, 4, 5], className: 'text-center align-middle' }
            ],
            drawCallback: function () {
                updateSelectionUI();
            }
        });

        $('#users').on('change', '.check-siswa', function () {
            updateSelectionUI();
        });

        $('#users').on('click', '.btn-reset', function () {
            const btn = $(this);
            const username = btn.data('username');
            const nama = btn.data('nama');
            const id = btn.data('id');

            Swal.fire({
                title: 'Reset Login',
                text: 'Reset percobaan login untuk ' + nama + ' (' + username + ')?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Reset'
            }).then(result => {
                if (!result.value) return;
                setLoading(true);
                const fd = new FormData();
                fd.append('username', username);
                fd.append('nama', encodeURIComponent(nama));

                $.ajax({
                    url: base_url + 'usersiswa/reset_login',
                    type: 'POST',
                    processData: false,
                    contentType: false,
                    data: fd,
                    dataType: 'json',
                    success: function (resp) {
                        setLoading(false);
                        Swal.fire({
                            title: resp.status ? 'Sukses' : 'Gagal',
                            text: resp.status ? (nama + ' berhasil direset') : resp.msg,
                            icon: resp.status ? 'success' : 'error'
                        }).then(() => {
                            if (resp.status) loadSiswaRuang(currentRuang, currentSesi);
                        });
                    },
                    error: function () {
                        setLoading(false);
                        Swal.fire({ title: 'Gagal', text: 'Terjadi kesalahan jaringan.', icon: 'error' });
                    }
                });
            });
        });
    }

    function loadSiswaRuang(ruang, sesi) {
        if (!ruang || !sesi) return;
        currentRuang = ruang;
        currentSesi = sesi;
        setLoading(true);

        $.ajax({
            type: 'GET',
            url: base_url + 'cbtactivate/getsiswaruang?ruang=' + ruang + '&sesi=' + sesi,
            success: function (response) {
                setLoading(false);
                if (!response.status) {
                    Swal.fire({ title: 'Gagal', text: response.msg, icon: 'error' });
                    return;
                }
                renderTable(response.siswa);
            },
            error: function (xhr) {
                setLoading(false);
                Swal.fire({ title: 'Gagal', html: xhr.responseText, icon: 'error' });
            }
        });
    }

    function renderTable(siswas) {
        if (dt) dt.destroy();
        dt = null;

        $('#table-body').empty();
        const isEmpty = !siswas || siswas.length === 0;

        if (isEmpty) {
            $('#empty-state').removeClass('d-none');
            $('#table-wrapper').addClass('d-none');
            updateSelectionUI();
            return;
        }

        $('#empty-state').addClass('d-none');
        $('#table-wrapper').removeClass('d-none');

        let html = '';
        siswas.forEach((siswa, idx) => {
            let statusBadge = '';
            if (!siswa.has_account) {
                statusBadge = '<span class="badge bg-secondary">Belum Ada Akun</span>';
            } else if (siswa.user_active == '1') {
                statusBadge = '<span class="badge bg-success">Aktif</span>';
            } else {
                statusBadge = '<span class="badge bg-danger">Nonaktif</span>';
            }

            let passwordCell = '<span class="text-muted">-</span>';
            if (siswa.has_account) {
                if (siswa.password_default) {
                    passwordCell = '<kbd>' + siswa.password_default + '</kbd>';
                } else {
                    passwordCell = '<span class="text-muted">= username</span>';
                }
            }

            html += `<tr>
                <td class="text-center align-middle">
                    <input type="checkbox" name="ids[]" value="${siswa.siswa_id}" class="form-check-input check-siswa">
                </td>
                <td class="text-center align-middle">${idx + 1}</td>
                <td class="align-middle">${siswa.nis}</td>
                <td>
                    <div class="media d-flex h-100">
                        ${loadImage(siswa.foto)}
                        <div class="media-body ms-2 justify-content-center align-self-center">
                            ${siswa.nama}
                        </div>
                    </div>
                </td>
                <td class="align-middle">${siswa.nama_kelas || ''}</td>
                <td class="align-middle">${siswa.username}</td>
                <td>${passwordCell}</td>
                <td>${statusBadge}</td>
                <td>
                    <button type="button" class="btn btn-default btn-sm btn-reset"
                        ${siswa.reset == '0' ? 'disabled' : ''}
                        data-id="${siswa.siswa_id}"
                        data-username="${siswa.username}"
                        data-nama="${siswa.nama}"
                        data-bs-toggle="tooltip" title="${siswa.reset == '1' ? 'Reset percobaan login' : 'Tidak ada percobaan login'}">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </td>
            </tr>`;
        });

        $('#table-body').html(html);
        initTable();
        updateSelectionUI();
    }

    function updateSelectionUI() {
        checkedSiswa = [];
        $('#table-body tr .check-siswa:checked').each(function () {
            checkedSiswa.push($(this).val());
        });
        const total = $('#table-body tr .check-siswa').length;
        const countChecked = checkedSiswa.length;

        $('#cnt-aktifkan').text(countChecked);
        $('#cnt-nonaktifkan').text(countChecked);
        $('#btn-aktifkan').attr('disabled', countChecked === 0);
        $('#btn-nonaktifkan').attr('disabled', countChecked === 0);

        const checkAll = $('#check-all');
        checkAll.prop('checked', total > 0 && countChecked === total);
        checkAll.prop('indeterminate', countChecked > 0 && countChecked < total);
    }

    function confirmBulk(action) {
        if (checkedSiswa.length === 0) return;

        const isActivate = action === 'aktifkan';
        const label = isActivate ? 'Aktifkan' : 'Nonaktifkan';
        Swal.fire({
            title: label + ' ' + checkedSiswa.length + ' siswa?',
            text: isActivate
                ? 'Siswa yang dipilih akan diaktifkan. Akun yang belum ada akan dibuat otomatis.'
                : 'Siswa yang dipilih akan dinonaktifkan dan tidak bisa login.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isActivate ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, ' + label
        }).then(result => {
            if (!result.value) return;

            const fd = new FormData();
            checkedSiswa.forEach(id => fd.append('ids[]', id));
            fd.append('active', action);

            setLoading(true);
            $.ajax({
                url: base_url + 'cbtactivate/aktifkan_semua',
                type: 'POST',
                processData: false,
                contentType: false,
                data: fd,
                dataType: 'json',
                success: function (response) {
                    setLoading(false);
                    Swal.fire({
                        title: response.status ? 'Sukses' : 'Gagal',
                        text: response.msg,
                        icon: response.status ? 'success' : 'error'
                    }).then(() => {
                        if (response.status) loadSiswaRuang(currentRuang, currentSesi);
                    });
                },
                error: function () {
                    setLoading(false);
                    Swal.fire({ title: 'Gagal', text: 'Terjadi kesalahan jaringan.', icon: 'error' });
                }
            });
        });
    }

    function loadImage(srcImg) {
        let temp = document.createElement('div');
        temp.innerHTML = `<img class="avatar rounded-circle" src="${base_url + srcImg}" width="36" height="36">`;
        const defSrc = base_url + 'assets/img/siswa.png';
        const imgs = temp.getElementsByTagName('img');
        imgs[0].setAttribute('alt', 'foto');
        let src = imgs[0].getAttribute('src');
        if (src.startsWith(base_url + 'profiles')) {
            src = src.replace('profiles', 'foto_siswa');
            imgs[0].setAttribute('src', src);
        }
        imgs[0].setAttribute('onerror', 'this.src="' + defSrc + '"');
        return temp.innerHTML;
    }

    $(document).ready(function () {
        ajaxcsrf();
        initRuangDropdown();
        initSesiDropdown();
        initTable();

        $('#ruang').on('change', function () {
            currentRuang = $(this).val();
            initSesiDropdown();
            if (currentRuang && currentSesi) {
                loadSiswaRuang(currentRuang, currentSesi);
            }
        });

        $('#sesi').on('change', function () {
            currentSesi = $(this).val();
            if (currentRuang && currentSesi) {
                loadSiswaRuang(currentRuang, currentSesi);
            }
        });

        $('#check-all').on('change', function () {
            const checked = $(this).prop('checked');
            $('#table-body tr .check-siswa').prop('checked', checked);
            updateSelectionUI();
        });

        $('#btn-aktifkan').on('click', function () { confirmBulk('aktifkan'); });
        $('#btn-nonaktifkan').on('click', function () { confirmBulk('nonaktifkan'); });

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
<?= $this->endSection() ?>
