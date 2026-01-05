<?php
/** @var object $tp_active */
/** @var object $smt_active */
/** @var array $kelas */
/** @var array $ruang */
/** @var array $sesi */
/** @var string $subjudul */
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $judul ?? 'Nomor Peserta' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Nomor Peserta</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title">
                        <?= esc($subjudul) ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold">Pilih Kelas Penilaian</label>
                            <select id="kelas" name="kelas[]" class="form-select select2" multiple="multiple"
                                data-placeholder="Pilih kelas penilaian">
                                <?php if (!empty($kelas)): ?>
                                    <option value="Semua">Semua Level Kelas</option>
                                    <?php foreach ($kelas as $k): ?>
                                        <option value="<?= $k->id_kelas ?>">
                                            <?= esc($k->nama_kelas) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="mt-2 text-info small">
                                <i class="bi bi-info-circle me-1"></i> Pilih semua kelas yang akan melaksanakan
                                penilaian
                            </div>
                        </div>
                        <div class="col-md-6 d-none" id="generate">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#nomorModal">
                                    <i class="bi bi-gear me-1"></i> Generate Nomor
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
                            <div class="text-end mb-3">
                                <form id="setNomor" style="display:inline-block">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn btn-sm btn-warning shadow-sm"
                                        onclick="undoNumber()">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Batalkan perubahan
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary shadow-sm ms-2">
                                        <i class="bi bi-save me-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="table-nomor">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <button class="btn btn-primary shadow" type="button" disabled>
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Loading...
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Generate -->
<div class="modal fade" id="nomorModal" tabindex="-1" aria-labelledby="nomorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nomorModalLabel">Generate Nomor Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <!-- 1 - Separator -->
                    <div class="d-flex flex-row align-items-center mb-3">
                        <div class="me-3">
                            <input id="check1" class="form-check-input" type="checkbox" checked />
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text" style="width: 180px">Separator</span>
                            <input id="input1" class="form-control fw-bold" value="-" type="text" />
                        </div>
                    </div>
                    <small class="d-block ms-5 mb-3 text-muted">Pemisah antara tiap kode (contoh: -, _, /)</small>

                    <?php for ($i = 2; $i <= 11; $i++): ?>
                        <div class="d-flex flex-row align-items-center mb-1">
                            <div class="me-3">
                                <input id="check<?= $i ?>" class="form-check-input part-check" type="checkbox" checked />
                            </div>
                            <div class="input-group input-group-sm">
                                <select id="opt<?= $i ?>" class="form-select for-option bg-light" style="max-width: 180px;">
                                    <option value="">Pilih Kode</option>
                                </select>
                                <input id="input<?= $i ?>" class="form-control fw-bold" type="text" />
                            </div>
                        </div>
                        <small id="info<?= $i ?>" class="d-block ms-5 mb-3 text-muted small"></small>
                    <?php endfor; ?>
                </div>

                <div class="alert alert-info border-0 shadow-sm mt-3">
                    <strong>Catatan:</strong>
                    <ul class="mb-0 small">
                        <li>Pastikan kode yang ingin digunakan sudah diberi tanda centang.</li>
                        <li>Setiap kolom input dapat dikustomisasi, kecuali kolom yang otomatis mengambil data siswa
                            (NIS, NISN, Tingkat).</li>
                        <li>Format nomor akan dibuat sesuai urutan siswa alfabetis.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary" onclick="generateNomorUjian()">
                    <i class="bi bi-gear-wide-connected me-1"></i> Mulai Generate
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const tp_tahun = '<?= $tp_active->tahun ?>';
    const smt_id = '<?= $smt_active->id_smt ?>';
    const thn_parts = tp_tahun.split('/');
    const twoDigitYear = smt_id == '1' ? thn_parts[0].substring(2, 4) : thn_parts[1].substring(2, 4);

    const opsiOptions = {
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

    const initialValues = {
        "2": twoDigitYear,
        "3": "",
        "4": "",
        "5": "",
        "6": "",
        "7": "[tingkat]",
        "8": "[nis]",
        "9": "[nisn]",
        "10": "001",
        "11": "8"
    };

    const infoHints = {
        "2": "2 digit tahun akademik",
        "3": "Kode Provinsi (misal: 02)",
        "4": "Kode Kota/Kabupaten",
        "5": "Kode Unik Sekolah",
        "6": "SD:01, SMP:02, SMA:03",
        "7": "Otomatis dari tingkat kelas",
        "8": "Nomor Induk Siswa",
        "9": "Nomor Induk Siswa Nasional",
        "10": "Nomor urut otomatis (contoh: 001)",
        "11": "Digit cek unik otomatis"
    };

    let arrayNomor = [];
    let originalData = null;

    function sortByNumber(a, b) {
        if (!a.nomor) return 1;
        if (!b.nomor) return -1;
        return a.nomor.localeCompare(b.nomor, undefined, { numeric: true });
    }

    function getNomorPeserta(kelasIds) {
        if (!kelasIds || kelasIds.length === 0) {
            $('#generate, #for-save').addClass('d-none');
            $('#table-nomor').html('');
            return;
        }

        $('#loading').removeClass('d-none');
        $.ajax({
            url: base_url + "cbtnomorpeserta/getsiswakelas",
            type: "POST",
            data: {
                kelas: JSON.stringify(kelasIds),
                [csrfName]: csrfHash
            },
            success: function (res) {
                originalData = res;
                arrayNomor = res.siswa.map(s => ({
                    id: s.id_siswa,
                    username: s.username || '-',
                    nama: s.nama,
                    nis: s.nis,
                    nisn: s.nisn,
                    level: s.level,
                    kelas: s.nama_kelas,
                    nomor: res.nomor[s.id_siswa] ? res.nomor[s.id_siswa].nomor_peserta : ''
                }));

                arrayNomor.sort(sortByNumber);
                renderTable();
            }
        });
    }

    function renderTable() {
        $('#generate').removeClass('d-none');

        let html = `<thead>
            <tr class="table-dark">
                <th class="text-center" width="50">No.</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Kelas</th>
                <th class="text-center">Nomor Peserta</th>
            </tr>
        </thead><tbody>`;

        if (arrayNomor.length === 0) {
            html += `<tr><td colspan="5" class="text-center italic py-4">Tidak ada data siswa ditemukan</td></tr>`;
        } else {
            arrayNomor.forEach((item, i) => {
                html += `<tr>
                    <td class="text-center small text-muted">${i + 1}</td>
                    <td class="font-monospace">${item.username}</td>
                    <td>${item.nama}</td>
                    <td>${item.kelas}</td>
                    <td class="text-center fw-bold text-primary editable" id="${item.id}" contentEditable="true">${item.nomor}</td>
                </tr>`;
            });
            $('#for-save').removeClass('d-none');
        }

        html += '</tbody>';
        $('#table-nomor').html(html);
        $('#loading').addClass('d-none');
    }

    function generateNomorUjian() {
        // Sort alphabetically first for sequence
        arrayNomor.sort((a, b) => a.nama.localeCompare(b.nama));

        const separator = $('#check1').is(':checked') ? $('#input1').val() : '';
        let startSequence = 1;

        // Pattern for "Nomor Unik" (Mock pattern 8,7,6,5,4,3,2)
        const uniquePattern = [8, 7, 6, 5, 4, 3, 2];

        arrayNomor.forEach((siswa, i) => {
            let build = [];
            for (let j = 2; j <= 11; j++) {
                if (!$(`#check${j}`).is(':checked')) continue;

                const type = $(`#opt${j}`).val();
                let val = $(`#input${j}`).val().trim();

                switch (type) {
                    case "7": val = (siswa.level || '0').toString().padStart(2, '0'); break;
                    case "8": val = siswa.nis || ''; break;
                    case "9": val = siswa.nisn || ''; break;
                    case "10":
                        const padLen = val.length > 0 ? val.length : 3;
                        const startVal = parseInt(val) || 1;
                        val = (startVal + i).toString().padStart(padLen, '0');
                        break;
                    case "11":
                        val = uniquePattern[i % uniquePattern.length].toString();
                        break;
                }
                if (val !== "") build.push(val);
            }
            siswa.nomor = build.join(separator);
        });

        $('#nomorModal').modal('hide');
        renderTable();
    }

    function resetNumber() {
        Swal.fire({
            title: "Hapus Semua Nomor?",
            text: "Nomor peserta pada kelas terpilih akan dikosongkan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: "Ya, Hapus!"
        }).then(result => {
            if (result.isConfirmed) {
                $('#loading').removeClass('d-none');
                const selectedKelas = $('#kelas').val();
                $.getJSON(base_url + "cbtnomorpeserta/resetnomor", { kelas: JSON.stringify(selectedKelas) }, function (res) {
                    if (res.status) {
                        getNomorPeserta(selectedKelas);
                    }
                });
            }
        });
    }

    function undoNumber() {
        if (!originalData) return;
        arrayNomor = originalData.siswa.map(s => ({
            id: s.id_siswa,
            username: s.username || '-',
            nama: s.nama,
            nis: s.nis,
            nisn: s.nisn,
            level: s.level,
            kelas: s.nama_kelas,
            nomor: originalData.nomor[s.id_siswa] ? originalData.nomor[s.id_siswa].nomor_peserta : ''
        }));
        arrayNomor.sort(sortByNumber);
        renderTable();
    }

    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        $('#kelas').on('change', function () {
            getNomorPeserta($(this).val());
        });

        // Initialize Options in Modal
        $('.for-option').each(function (idx) {
            const $sel = $(this);
            $.each(opsiOptions, function (val, text) {
                $sel.append(new Option(text, val));
            });

            const step = idx + 2;
            $sel.val(step.toString());
            $(`#input${step}`).val(initialValues[step]);
            $(`#info${step}`).text(infoHints[step]);

            if (["7", "8", "9"].includes(step.toString())) {
                $(`#input${step}`).prop('disabled', true);
            }
        });

        $('.for-option').on('change', function () {
            const id = $(this).attr('id').replace('opt', '');
            const val = $(this).val();
            $(`#input${id}`).val(initialValues[val] || '');
            $(`#info${id}`).text(infoHints[val] || '');
            $(`#input${id}`).prop('disabled', ["7", "8", "9"].includes(val));
        });

        $('.part-check').on('change', function () {
            const id = $(this).attr('id').replace('check', '');
            $(`#opt${id}, #input${id}`).prop('disabled', !$(this).is(':checked'));
        });

        $('#setNomor').on('submit', function (e) {
            e.preventDefault();

            // Sync current editable values back to array
            $('#table-nomor .editable').each(function () {
                const id = $(this).attr('id');
                const text = $(this).text().trim();
                const found = arrayNomor.find(p => p.id == id);
                if (found) found.nomor = text;
            });

            Swal.fire({
                title: 'Menyimpan...',
                didOpen: () => { Swal.showLoading(); }
            });

            $.post(base_url + "cbtnomorpeserta/savenomor", {
                siswa: JSON.stringify(arrayNomor),
                [csrfName]: csrfHash
            }, function (res) {
                if (res.status) {
                    Swal.fire('Berhasil', 'Nomor peserta telah disimpan', 'success');
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menyimpan data', 'error');
                }
            });
        });
    });
</script>