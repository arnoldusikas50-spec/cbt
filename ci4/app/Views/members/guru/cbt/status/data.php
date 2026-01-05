<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-default my-shadow mb-4">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title text-bold"><i class="bi bi-monitor me-2"></i>
                            <?= $subjudul ?>
                        </h3>
                        <button class="btn btn-danger btn-sm ms-auto shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#infoModal">
                            <i class="bi bi-info-circle me-1"></i> Info Error
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6 mx-auto">
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-success text-white px-4">TOKEN</span>
                                    <input id="token-input" class="form-control text-bold text-center fs-4 py-2"
                                        readonly="readonly" placeholder="- - - - - -" />
                                    <span id="interval" class="input-group-text bg-danger text-white d-none">-- : -- :
                                        --</span>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive mb-4" id="table-hari-ini"></div>
                        <div id="table-all-jadwal"></div>
                    </div>
                    <div class="overlay d-none" id="loading">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Info Error -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title" id="infoLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Kode Error
                    Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover m-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Kode</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 font-monospace text-bold">001</td>
                                <td>Token salah atau token belum digenerate</td>
                            </tr>
                            <tr>
                                <td class="ps-4 font-monospace text-bold">002</td>
                                <td>Harus reset izin</td>
                            </tr>
                            <tr>
                                <td class="ps-4 font-monospace text-bold">003</td>
                                <td>Harus reset waktu</td>
                            </tr>
                            <tr>
                                <td class="ps-4 font-monospace text-bold">004</td>
                                <td>Soal belum dibuat atau belum dipilih</td>
                            </tr>
                            <tr>
                                <td class="ps-4 font-monospace text-bold">005</td>
                                <td>Siswa menggunakan browser yang tidak mendukung</td>
                            </tr>
                            <tr>
                                <td class="ps-4 font-monospace text-bold">006</td>
                                <td>Internet down atau error dari database/server</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function formatTanggal(dateString) {
        const date = new Date(dateString);
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

    $(document).ready(function () {
        // In CI4 layouts/main, make sure global variables are available or provided
        const base_url = '<?= base_url() ?>/';

        // getToken logic should be in a global JS file or layouts/main
        // If it's not there yet, it might need to be ported.
        // For now, assume it's available via the _header scripts if used.

        if (typeof getToken === 'function') {
            getToken(function (result) {
                getGlobalToken();
            });
        }

        $('#refresh-token').click(function () {
            if (typeof getToken === 'function') {
                getToken(function (result) {
                    getGlobalToken();
                });
            }
        });

        function getGlobalToken() {
            if (typeof globalToken !== 'undefined' && globalToken != null) {
                const viewToken = $('#token-input');
                if (viewToken.length) viewToken.val(globalToken.token);
                if (globalToken.auto == '1' && typeof adaJadwalUjian !== 'undefined' && adaJadwalUjian > 0) {
                    $('#refresh-token').removeClass('d-none');
                }
            }
        }

        $.ajax({
            type: "GET",
            url: base_url + "cbtstatus/gettgljadwalujian",
            cache: false,
            success: function (response) {
                populateTable(response)
            }
        });
    });

    function populateTable(response) {
        const guru = response.guru
        const tglKeys = Object.keys(response?.jadwals || {});
        const keyHariIni = tglKeys.find(k => k === response.hari_ini);
        const jadwalHariIni = response.jadwals[keyHariIni] || [];
        const ruang_sesi = response.ruangs_sesi || [];

        if (jadwalHariIni.length > 0) {
            let tableNow = `<div class="table-responsive">
                            <table class="table table-bordered border-top m-0 shadow-sm">
                            <thead class="bg-primary text-white">
                            <tr><th colspan="6" class="text-center py-2">${formatTanggal(keyHariIni)}</th></tr>
                            <tr class="bg-light text-dark shadow-sm">
                                <th class="text-center align-middle">Ruang</th>
                                <th class="text-center align-middle">Sesi</th>
                                <th class="text-center align-middle">Jam Ke</th>
                                <th class="align-middle">Mapel / Bank Soal</th>
                                <th class="text-center align-middle">Role</th>
                                <th class="text-center align-middle">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>`;
            for (let i = 0; i < jadwalHariIni.length; i++) {
                const jadwal = jadwalHariIni[i];
                const idJadwal = jadwal.id_jadwal;
                const isPengampu = jadwal?.pengampu;

                for (const ruangSesi of ruang_sesi) {
                    const pengawas = response?.pengawas?.find(p => p.id_jadwal === idJadwal && p.id_ruang === ruangSesi.ruang_id && p.id_sesi === ruangSesi.sesi_id);
                    const gp = (pengawas?.id_guru || '').split(',');
                    const isPengawas = gp.includes(guru.id_guru);
                    let role = isPengampu ? `<span class="badge bg-success shadow-sm mb-1 px-3">Pengampu</span>` : ``;
                    if (role !== '') { role += `<br>` }
                    role += isPengawas ? `<span class="badge bg-info shadow-sm px-3">Pengawas</span>` : ``;

                    if (isPengawas || isPengampu) {
                        const tr = `<tr>
                                    <td class="text-center align-middle fw-bold">${ruangSesi.nama_ruang}</td>
                                    <td class="text-center align-middle">${ruangSesi.nama_sesi}</td>
                                    <td class="text-center align-middle"><span class="badge bg-light text-dark border shadow-sm">${pengawas?.jam_ke || '1'}</span></td>
                                    <td class="align-middle">
                                        <div class="fw-bold text-primary">${jadwal.bank?.mapel?.nama_mapel}</div>
                                        <div class="small text-muted">${jadwal.bank?.bank_kode}</div>
                                    </td>
                                    <td class="text-center align-middle">${role}</td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" data-id="${idJadwal}" data-ruang="${ruangSesi.ruang_id}" data-sesi="${ruangSesi.sesi_id}" onclick="detail(this)">
                                            <i class="bi bi-eye-fill me-1"></i> Lihat Status
                                        </button>
                                    </td>
                                </tr>`;
                        tableNow += tr;
                    }
                }
            }
            tableNow += `</tbody></table></div>`;
            $('#table-hari-ini').html(tableNow);
        } else {
            const tableNow = `<div class="alert alert-info border-0 shadow-sm text-center">
                                <i class="bi bi-info-circle-fill me-2"></i> Tidak ada jadwal ujian hari ini
                              </div>`;
            $('#table-hari-ini').html(tableNow);
        }

        const keySebelumnya = tglKeys.filter(k => k < response.hari_ini).sort().reverse();
        if (keySebelumnya.length > 0) {
            for (const tgl of keySebelumnya) {
                let tableAll = `<div class="mt-5 mb-4 table-responsive">
                            <table class="table table-bordered border-top m-0 shadow-sm">
                            <thead class="bg-secondary text-white">
                            <tr><th colspan="6" class="text-center py-2">${formatTanggal(tgl)}</th></tr>
                            <tr class="bg-light text-dark shadow-sm">
                                <th class="text-center align-middle">Ruang</th>
                                <th class="text-center align-middle">Sesi</th>
                                <th class="text-center align-middle">Jam Ke</th>
                                <th class="align-middle">Mapel / Bank Soal</th>
                                <th class="text-center align-middle">Role</th>
                                <th class="text-center align-middle">Aksi</th>
                            </tr>
                            </thead>
                            <tbody>`;
                const jadwals = response.jadwals[tgl];

                for (let i = 0; i < jadwals.length; i++) {
                    const jadwal = jadwals[i];
                    const idJadwal = jadwal.id_jadwal;
                    const isPengampu = jadwal?.pengampu;

                    for (const ruangSesi of ruang_sesi) {
                        const pengawas = response?.pengawas?.find(p => p.id_jadwal === idJadwal && p.id_ruang === ruangSesi.ruang_id && p.id_sesi === ruangSesi.sesi_id);
                        const gp = (pengawas?.id_guru || '').split(',');
                        const isPengawas = gp.includes(guru.id_guru);
                        let role = isPengampu ? `<span class="badge bg-success shadow-sm mb-1 px-3">Pengampu</span>` : ``;
                        if (role !== '') { role += `<br>` }
                        role += isPengawas ? `<span class="badge bg-info shadow-sm px-3">Pengawas</span>` : ``;

                        if (isPengawas || isPengampu) {
                            const tr = `<tr>
                                    <td class="text-center align-middle fw-bold">${ruangSesi.nama_ruang}</td>
                                    <td class="text-center align-middle">${ruangSesi.nama_sesi}</td>
                                    <td class="text-center align-middle"><span class="badge bg-light text-dark border shadow-sm">${pengawas?.jam_ke || '1'}</span></td>
                                    <td class="align-middle">
                                        <div class="fw-bold text-dark">${jadwal.bank?.mapel?.nama_mapel}</div>
                                        <div class="small text-muted">${jadwal.bank?.bank_kode}</div>
                                    </td>
                                    <td class="text-center align-middle">${role}</td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-light btn-sm rounded-pill px-4 border shadow-sm" data-id="${idJadwal}" data-ruang="${ruangSesi.ruang_id}" data-sesi="${ruangSesi.sesi_id}" onclick="detail(this)">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>`;
                            tableAll += tr;
                        }
                    }
                }

                tableAll += `</tbody></table></div>`;
                $('#table-all-jadwal').append(tableAll);
            }
        }
    }

    function detail(e) {
        window.location.href = '<?= site_url('cbtstatus/pengawas') ?>?' +
            'ruang=' + $(e).data('ruang') +
            '&sesi=' + $(e).data('sesi') +
            '&jadwal=' + $(e).data('id');
    }
</script>
<?= $this->endSection() ?>