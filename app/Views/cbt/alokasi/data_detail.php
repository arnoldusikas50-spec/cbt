<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
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
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h3 class="card-title"><b><?= $subjudul ?? 'Detail Alokasi Waktu Ujian' ?></b></h3>
                <div class="btn-group card-tools ms-auto">
                    <a href="<?= base_url('cbtalokasi') ?>" type="button" class="btn btn-sm btn-primary">By Mapel</a>
                    <a href="#" type="button" class="btn btn-sm btn-primary active">Detail</a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info shadow-sm align-content-center" role="alert">
                    <div class="d-flex">
                        <i class="bi bi-info-circle me-3 mt-1 fs-5"></i>
                        <div>
                            <strong>Penting!</strong>
                            <ol class="mb-0 mt-1">
                                <li>Mengatur urutan aktif jadwal ujian sesuai urutan jam</li>
                                <li>Contoh <b>Jam ke</b> 2, maka jadwal mapel akan aktif setelah jam pertama selesai
                                    dikerjakan oleh siswa</li>
                                <li>Jika semua <b>Jam ke</b> diatur ke 0 atau 1 maka semua jadwal yang aktif akan bisa
                                    dikerjakan oleh siswa</li>
                                <li>Halaman ini berlaku jika semua jadwal telah diaktifkan</li>
                                <li>Jika halaman ini tidak diatur/tidak disimpan, maka jadwal ujian mengikuti aturan di
                                    MENU <b>JADWAL</b></li>
                            </ol>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row mb-3">
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="mb-3">
                            <label for="jenis" class="form-label fw-bold">Jenis Penilaian</label>
                            <select name="jenis" id="jenis" class="form-select form-select-sm">
                                <option value="">-- Pilih Jenis --</option>
                                <?php if (isset($jenis) && is_array($jenis)): ?>
                                    <?php foreach ($jenis as $key => $value): ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="mb-3">
                            <label for="tanggal" class="form-label fw-bold">Tanggal</label>
                            <select name="tanggal" id="tanggal" class="form-select form-select-sm">
                                <option value="">Pilih Tanggal</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" id="table-container"></div>
                <form id="savealokasi">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary mt-3 float-end d-none ms-auto px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i> Simpan Alokasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url() ?>/assets/app/js/jquery.rowspanizer.js"></script>
<script>
    const id_tp = '<?= $tp_active->id_tp ?? "" ?>';
    const id_smt = '<?= $smt_active->id_smt ?? "" ?>';

    $(document).ready(async function () {
        ajaxcsrf();
        var opsiJenis = $("#jenis");
        var opsiTgl = $("#tanggal");

        opsiJenis.change(function () {
            opsiTgl.empty();
            opsiTgl.append("<option value=''>Pilih Tanggal</option>");
            getTglJadwal();
        });

        opsiTgl.change(function () {
            getAllJadwal();
        });

        function getTglJadwal() {
            var jenis = opsiJenis.val();
            if (jenis) {
                $.ajax({
                    url: base_url + 'cbtalokasi/getTanggal?jenis=' + jenis,
                    type: "GET",
                    success: function (respon) {
                        if (respon.status) {
                            for (const tgl in respon.data) {
                                opsiTgl.append(`<option value="${tgl}">${respon.data[tgl]}</option>`);
                            }
                        }
                    }
                });
            }
        }

        function getAllJadwal() {
            var jenis = opsiJenis.val();
            var tanggal = opsiTgl.val();
            if (jenis && tanggal) {
                let formData = new FormData($('#savealokasi')[0]);
                formData.append('jenis', jenis);
                formData.append('tanggal', tanggal);

                Swal.fire({
                    text: "Memuat data jadwal. Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: base_url + 'cbtalokasi/getAlokasi',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: async function (respon) {
                        if (respon.status) {
                            await createTableFromJSON(respon.data);
                        }
                        Swal.close();
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({ title: "Error", text: "Terjadi kesalahan server", icon: "error" });
                    }
                });
            }
        }

        $('#savealokasi').on('submit', function (e) {
            e.stopPropagation();
            e.preventDefault();
            e.stopImmediatePropagation();

            var jenis = opsiJenis.val();
            if (jenis == "") return;

            Swal.fire({
                text: "Menyimpan alokasi waktu ujian. Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const tables = $('#table-alokasi');
            var jsonObj = [];
            const $rows = $(tables).find('tbody tr');

            $rows.each((i, row) => {
                const jam_ke = $(row).find('input[name="jamke"]').val();
                const target = $(row).find('.jadwal');
                if (target.length === 0) return;

                const ruang = target.data('ruang');
                const sesi = target.data('sesi');
                const idJadwals = String(target.data('id'));
                const jadwals = idJadwals.split(',');

                for (const jadwal of jadwals) {
                    const id_pengawas = id_tp + '' + id_smt + '' + jadwal + '' + ruang + '' + sesi;
                    jsonObj.push({
                        id_pengawas: id_pengawas,
                        id_jadwal: jadwal,
                        id_sesi: sesi,
                        id_tp: id_tp,
                        id_smt: id_smt,
                        id_ruang: ruang,
                        jam_ke: jam_ke
                    });
                }
            });

            var dataPost = $(this).serialize() + "&alokasi=" + JSON.stringify(jsonObj);

            $.ajax({
                url: base_url + "cbtalokasi/saveAlokasi",
                type: "POST",
                dataType: "JSON",
                data: dataPost,
                success: function (data) {
                    if (data.status) {
                        Swal.fire({ title: "Sukses", html: "Alokasi waktu berhasil disimpan", icon: "success" })
                            .then(result => { if (result.value) getAllJadwal(); });
                    } else {
                        Swal.fire({ title: "Info", text: data.message || "Tidak ada data yang disimpan", icon: "info" });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({ title: "Error", text: "Terjadi kesalahan server", icon: "error" });
                }
            });
        });
    });

    async function createTableFromJSON(data) {
        const tableContainer = document.getElementById('table-container');
        const allSesi = data.ruangs || [];
        const jsonData = data.jadwals_ujian || [];

        if (allSesi.length === 0 || jsonData.length === 0) {
            tableContainer.innerHTML = `<table class="w-100 table-bordered"><tr><td class="text-center bg-warning p-2">Tidak ada jadwal penilaian hari ini</td></tr></table>`;
            return;
        }

        const flatList = [];
        jsonData.forEach(jadwal => {
            const bankKelas = Array.isArray(jadwal.bank?.bank_kelas) ? jadwal.bank.bank_kelas : [];
            const ruangFiltered = allSesi.filter(r =>
                (Array.isArray(r.kelas_id) ? r.kelas_id : []).some(id => bankKelas.includes(String(id)))
            );

            ruangFiltered.forEach(ruang => {
                const pengawasJadwal = data.pengawas[jadwal.id_jadwal]?.[ruang.ruang_id]?.[ruang.sesi_id] || {};
                let jk = pengawasJadwal.jam_ke || jadwal.jam_ke || '1';
                flatList.push({
                    ruang_id: ruang.ruang_id,
                    nama_ruang: ruang.nama_ruang,
                    sesi_id: ruang.sesi_id,
                    nama_sesi: ruang.nama_sesi,
                    kelas_id: ruang.kelas_id,
                    nama_kelas: ruang.nama_kelas,
                    jam_ke: jk,
                    jadwal: jadwal,
                });
            });
        });

        flatList.sort((a, b) => {
            if (a.ruang_id !== b.ruang_id) return a.ruang_id - b.ruang_id;
            return a.sesi_id - b.sesi_id;
        });

        const calculateRowCounts = (items, ...keys) => {
            const counts = new Map();
            let prevKey = null;
            let count = 0;
            items.forEach((item, idx) => {
                const curKey = keys.map(k => String(item[k])).join('-');
                if (curKey === prevKey) {
                    count++;
                } else {
                    if (prevKey !== null) counts.set(prevKey, count);
                    prevKey = curKey;
                    count = 1;
                }
                if (idx === items.length - 1) counts.set(curKey, count);
            });
            return counts;
        };

        const ruangRowCounts = calculateRowCounts(flatList, 'ruang_id');
        const sesiRowCounts = calculateRowCounts(flatList, 'ruang_id', 'sesi_id');

        let html = `<table class="table table-bordered table-sm w-100 mt-3" id="table-alokasi">
            <thead class="bg-light">
                <tr>
                    <th class="text-center align-middle">HARI/TANGGAL</th>
                    <th class="text-center align-middle">RUANG</th>
                    <th class="text-center align-middle">SESI</th>
                    <th class="text-center align-middle">MAPEL<br>BANK SOAL</th>
                    <th class="text-center align-middle">KELAS</th>
                    <th class="text-center align-middle">JAM KE</th>
                </tr>
            </thead><tbody>`;

        const usedRuang = new Set();
        const usedSesi = new Set();
        let curSesiIdx = 0;

        flatList.forEach((item, idx) => {
            const { ruang_id, nama_ruang, sesi_id, nama_sesi, kelas_id, nama_kelas, jam_ke, jadwal } = item;
            const tgl = new Date(jadwal.tgl_mulai).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

            const bankKelasIds = Array.isArray(jadwal.bank?.bank_kelas) ? jadwal.bank.bank_kelas : [];
            const relevantClasses = bankKelasIds.filter(id => (Array.isArray(kelas_id) ? kelas_id : []).includes(id)).map(id => nama_kelas[id] || '');
            const bank_kelas = [...new Set(relevantClasses)].join(', ');

            html += `<tr>`;
            if (idx === 0) html += `<td rowspan="${flatList.length}" class="text-center align-middle">${tgl}</td>`;

            const ruangKey = String(ruang_id);
            if (!usedRuang.has(ruangKey)) {
                html += `<td rowspan="${ruangRowCounts.get(ruangKey)}" class="text-center align-middle fw-bold">${nama_ruang}</td>`;
                usedRuang.add(ruangKey);
            }

            const sesiKey = `${ruang_id}-${sesi_id}`;
            const bgRow = curSesiIdx % 2 === 0 ? 'bg-light' : '';
            if (!usedSesi.has(sesiKey)) {
                html += `<td rowspan="${sesiRowCounts.get(sesiKey)}" class="text-center align-middle ${bgRow}">${nama_sesi}</td>`;
                usedSesi.add(sesiKey);
                curSesiIdx++;
            }

            html += `
                <td class="text-center align-middle jadwal ${bgRow}" data-ruang="${ruang_id}" data-sesi="${sesi_id}" data-id="${jadwal.id_jadwal}">
                    ${jadwal.bank.nama_mapel}<br><span class="badge bg-secondary">${jadwal.bank.bank_kode}</span>
                </td>
                <td class="text-center align-middle ${bgRow}">${bank_kelas}</td>
                <td class="align-middle ${bgRow}">
                    <input class="form-control form-control-sm text-center" type="number" min="1" name="jamke" value="${jam_ke}" style="width:60px; margin:0 auto;">
                </td>
            </tr>`;
        });

        html += `</tbody></table>`;
        tableContainer.innerHTML = html;
        $('#savealokasi button[type="submit"]').removeClass('d-none');
    }
</script>
<?= $this->endSection() ?>