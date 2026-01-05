<!-- Content Wrapper -->
<div class="app-main">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Alokasi Waktu' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('cbtalokasi') ?>">Alokasi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title"><b>
                            <?= $subjudul ?? 'Detail Alokasi Waktu Ujian' ?>
                        </b></h3>
                    <div class="btn-group card-tools ms-auto">
                        <a href="<?= base_url('cbtalokasi') ?>" type="button" class="btn btn-sm btn-primary">By
                            Mapel</a>
                        <a href="#" type="button" class="btn btn-sm btn-primary active">Detail</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info shadow align-content-center" role="alert">
                        <strong>Penting!</strong>
                        <ol class="mb-0">
                            <li>Mengatur urutan aktif jadwal ujian sesuai urutan jam</li>
                            <li>Contoh <b>Jam ke</b> 2, maka jadwal mapel akan aktif setelah jam pertama selesai
                                dikerjakan oleh siswa</li>
                            <li>Jika semua <b>Jam ke</b> diatur ke 0 atau 1 maka semua jadwal yang aktif akan bisa
                                dikerjakan oleh siswa</li>
                            <li>Halaman ini berlaku jika semua jadwal telah diaktifkan</li>
                            <li>Jika halaman ini tidak diatur/tidak disimpan, maka jadwal ujian mengikuti aturan di MENU
                                <b>JADWAL</b>
                            </li>
                        </ol>
                    </div>
                    <br>
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="mb-3">
                                <label for="jenis">Jenis Penilaian</label>
                                <select name="jenis" id="jenis" class="form-control select2">
                                    <option value="">-- Pilih Jenis --</option>
                                    <?php if (isset($jenis) && is_array($jenis)): ?>
                                        <?php foreach ($jenis as $key => $value): ?>
                                            <option value="<?= $key ?>">
                                                <?= $value ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="mb-3">
                                <label for="tanggal">Tanggal</label>
                                <select name="tanggal" id="tanggal" class="form-control select2">
                                    <option value="">Pilih Tanggal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" id="table-container"></div>
                    <form id="savealokasi">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary shadow mt-3 float-end d-none">
                            <i class="bi bi-save me-2"></i>Simpan Alokasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="<?= base_url() ?>/assets/app/js/jquery.rowspanizer.js"></script>
<script>
    const id_tp = '<?= $tp_active->id_tp ?? '' ?>';
    const id_smt = '<?= $smt_active->id_smt ?? '' ?>';

    $(document).ready(async function () {
        var opsiJenis = $("#jenis");
        var opsiTgl = $("#tanggal");

        opsiJenis.change(function () {
            opsiTgl.empty();
            opsiTgl.prepend("<option value=''>Pilih Tanggal</option>");
            getTglJadwal();
        });

        opsiTgl.change(function () {
            getAllJadwal();
        });

        function getTglJadwal() {
            var jenis = opsiJenis.val();
            var url = base_url + 'cbtalokasi/getTanggal?jenis=' + jenis;
            if (jenis) {
                $.ajax({
                    url: url,
                    type: "GET",
                    success: function (respon) {
                        for (const tgl in respon) {
                            const mulai = new Date(tgl).toLocaleDateString('id-ID', {
                                year: "numeric",
                                month: "long",
                                day: "2-digit",
                            });
                            opsiTgl.append(`<option value="${tgl}">${mulai}</option>`);
                        }
                    }
                });
            }
        }

        function getAllJadwal() {
            var jenis = opsiJenis.val();
            var tanggal = opsiTgl.val();
            if (jenis && tanggal) {
                Swal.fire({
                    text: "Memuat data jadwal....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: base_url + 'cbtalokasi/getAlokasi',
                    type: "POST",
                    data: { jenis: jenis, tanggal: tanggal, [CSRF_NAME]: CSRF_HASH },
                    success: async function (respon) {
                        if (respon.status) {
                            await createTableFromJSON(respon.data);
                            Swal.close();
                        } else {
                            Swal.fire({ title: "Error", text: respon.message, icon: "error" });
                        }
                    },
                    error: function () {
                        Swal.fire({ title: "Error", text: "Terjadi kesalahan server", icon: "error" });
                    }
                });
            }
        }

        $('#savealokasi').on('submit', function (e) {
            e.preventDefault();

            var jenis = opsiJenis.val();
            if (!jenis) return;

            Swal.fire({
                text: "Menyimpan alokasi....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const tables = $('#table-alokasi');
            var jsonObj = [];
            const $rows1 = $(tables).find('tr'), headers1 = $rows1.splice(0, 1);
            $rows1.each((i, row) => {
                const jam_ke = $(row).find('input[name="jamke"]').val();
                const node = $(row).find('.jadwal');
                const ruang = node.data('ruang');
                const sesi = node.data('sesi');
                const idJadwals = (node.data('id')).toString();
                const jadwals = idJadwals?.split(',');
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

            $.ajax({
                url: base_url + "cbtalokasi/saveAlokasi",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize() + "&alokasi=" + JSON.stringify(jsonObj),
                success: function (data) {
                    if (data.status) {
                        Swal.fire({ title: "Sukses", text: "Alokasi berhasil disimpan", icon: "success" })
                            .then(() => { getAllJadwal(); });
                    } else {
                        Swal.fire({ title: "Info", text: data.message || "Gagal menyimpan", icon: "info" });
                    }
                },
                error: function () {
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
            tableContainer.innerHTML = `<table class="table table-bordered"><tr><td class="text-center bg-warning">Tidak ada jadwal untuk tanggal ini</td></tr></table>`;
            $('button[type="submit"]').addClass('d-none');
            return;
        }

        const flatList = [];
        jsonData.forEach(jadwal => {
            const klss = (jadwal.bank.bank_kelas || []).map(String);
            const rFiltered = allSesi.filter(r => r.kelas_id.some(id => klss.includes(id?.toString())));

            rFiltered.forEach(ruang => {
                const peng = data.pengawas[jadwal.id_jadwal]?.[ruang.ruang_id]?.[ruang.sesi_id] || {};
                flatList.push({
                    ruang_id: ruang.ruang_id,
                    nama_ruang: ruang.nama_ruang,
                    sesi_id: ruang.sesi_id,
                    nama_sesi: ruang.nama_sesi,
                    kelas_id: ruang.kelas_id,
                    nama_kelas: ruang.nama_kelas,
                    jam_ke: peng.jam_ke || jadwal.jam_ke || '1',
                    jadwal: jadwal
                });
            });
        });

        flatList.sort((a, b) => {
            if (a.ruang_id !== b.ruang_id) return a.ruang_id - b.ruang_id;
            if (a.sesi_id !== b.sesi_id) return a.sesi_id - b.sesi_id;
            return a.jam_ke - b.jam_ke;
        });

        const ruangCounts = new Map();
        const sesiCounts = new Map();
        flatList.forEach(item => {
            ruangCounts.set(item.ruang_id, (ruangCounts.get(item.ruang_id) || 0) + 1);
            const sKey = `${item.ruang_id}-${item.sesi_id}`;
            sesiCounts.set(sKey, (sesiCounts.get(sKey) || 0) + 1);
        });

        let html = `<table class="table table-bordered table-sm w-100" id="table-alokasi">
            <thead>
                <tr class="bg-light text-center">
                    <th>HARI/TANGGAL</th>
                    <th>RUANG</th>
                    <th>SESI</th>
                    <th>MAPEL / BANK</th>
                    <th>KELAS</th>
                    <th>JAM KE</th>
                </tr>
            </thead>
            <tbody>`;

        const usedRuang = new Set();
        const usedSesi = new Set();
        flatList.forEach((item, i) => {
            html += `<tr>`;
            if (i === 0) html += `<td rowspan="${flatList.length}" class="text-center align-middle">${formatTanggal(item.jadwal.tgl_mulai)}</td>`;

            if (!usedRuang.has(item.ruang_id)) {
                html += `<td rowspan="${ruangCounts.get(item.ruang_id)}" class="text-center align-middle">${item.nama_ruang}</td>`;
                usedRuang.add(item.ruang_id);
            }

            const sKey = `${item.ruang_id}-${item.sesi_id}`;
            if (!usedSesi.has(sKey)) {
                html += `<td rowspan="${sesiCounts.get(sKey)}" class="text-center align-middle">${item.nama_sesi}</td>`;
                usedSesi.add(sKey);
            }

            const uniqueKls = [...new Set(item.jadwal.bank.bank_kelas.filter(id => item.kelas_id.includes(id)).map(id => item.nama_kelas[id]))];
            html += `<td class="text-center align-middle jadwal" data-ruang="${item.ruang_id}" data-sesi="${item.sesi_id}" data-id="${item.jadwal.id_jadwal}">
                ${item.jadwal.bank.nama_mapel}<br><span class="badge bg-light">${item.jadwal.bank.bank_kode}</span>
            </td>
            <td class="text-center align-middle">${uniqueKls.join(', ')}</td>
            <td class="text-center align-middle">
                <input class="form-control form-control-sm text-center" type="number" min="1" name="jamke" value="${item.jam_ke}" style="width: 60px; margin: 0 auto;">
            </td>
            </tr>`;
        });

        html += `</tbody></table>`;
        tableContainer.innerHTML = html;
        $('button[type="submit"]').removeClass('d-none');
    }

    function formatTanggal(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
</script>