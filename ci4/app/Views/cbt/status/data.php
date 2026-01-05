<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <?= $judul ?? 'Monitoring CBT' ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Monitoring</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter -->
            <div class="card card-default my-shadow mb-4">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Pilih Jadwal Ujian</label>
                                <select id="selectJadwal" class="form-control select2" style="width:100%">
                                    <option value="">-- Pilih Jadwal --</option>
                                    <?php foreach ($jadwals ?? [] as $jadwal): ?>
                                        <option value="<?= $jadwal->id_jadwal ?>">
                                            <?= esc($jadwal->nama_mapel ?? $jadwal->bank_nama ?? '-') ?>
                                            (<?= date('d/m/Y', strtotime($jadwal->tgl_mulai ?? 'now')) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Ruang</label>
                                <select id="selectRuang" class="form-control select2">
                                    <option value="">Semua Ruang</option>
                                    <?php foreach ($ruangs ?? [] as $ruang): ?>
                                        <option value="<?= is_object($ruang) ? $ruang->id_ruang : '' ?>">
                                            <?= is_object($ruang) ? esc($ruang->nama_ruang) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Sesi</label>
                                <select id="selectSesi" class="form-control select2">
                                    <option value="">Semua Sesi</option>
                                    <?php foreach ($sesis ?? [] as $sesi): ?>
                                        <option value="<?= is_object($sesi) ? $sesi->id_sesi : '' ?>">
                                            <?= is_object($sesi) ? esc($sesi->nama_sesi) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 mt-3 mt-md-0">
                            <button class="btn btn-primary w-100 shadow-sm" onclick="loadMonitoring()">
                                <i class="bi bi-arrow-repeat me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row" id="statsContainer" style="display: none;">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3 id="statTotal">0</h3>
                            <p>Total Peserta</p>
                        </div>
                        <div class="icon"><i class="bi bi-people"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning shadow-sm">
                        <div class="inner">
                            <h3 id="statSedang">0</h3>
                            <p>Sedang Ujian</p>
                        </div>
                        <div class="icon"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h3 id="statSelesai">0</h3>
                            <p>Selesai</p>
                        </div>
                        <div class="icon"><i class="bi bi-check2-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary shadow-sm">
                        <div class="inner">
                            <h3 id="statBelum">0</h3>
                            <p>Belum Mulai</p>
                        </div>
                        <div class="icon"><i class="bi bi-person-dash"></i></div>
                    </div>
                </div>
            </div>

            <!-- Tables -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card card-warning my-shadow h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-record-circle me-2"></i>Sedang Ujian</h3>
                            <div class="card-tools ms-auto">
                                <span class="badge bg-warning text-dark" id="badgeSedang">0</span>
                            </div>
                        </div>
                        <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3 py-2 text-center" style="width: 50px">No.</th>
                                        <th class="py-2">Siswa</th>
                                        <th class="py-2">Kelas</th>
                                        <th class="py-2 text-center">Mulai</th>
                                        <th class="py-2 text-center" style="width: 100px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableSedang">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> Pilih jadwal terlebih dahulu
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card card-success my-shadow h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-check-circle me-2"></i>Selesai</h3>
                            <div class="card-tools ms-auto">
                                <span class="badge bg-success" id="badgeSelesai">0</span>
                            </div>
                        </div>
                        <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-sm table-striped table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3 py-2 text-center" style="width: 50px">No.</th>
                                        <th class="py-2">Siswa</th>
                                        <th class="py-2">Kelas</th>
                                        <th class="py-2 text-center">Durasi</th>
                                        <th class="py-2 text-center">Selesai</th>
                                    </tr>
                                </thead>
                                <tbody id="tableSelesai">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="bi bi-info-circle me-1"></i> Pilih jadwal terlebih dahulu
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>




<script>
    var autoRefresh = null;

    $(document).ready(function () {
        $('.select2').select2();

        $('#selectJadwal').on('change', function () {
            loadMonitoring();

            if (this.value) {
                if (autoRefresh) clearInterval(autoRefresh);
                autoRefresh = setInterval(loadMonitoring, 10000);
            } else {
                if (autoRefresh) clearInterval(autoRefresh);
            }
        });
    });

    function loadMonitoring() {
        var jadwal = $('#selectJadwal').val();
        if (!jadwal) {
            $('#statsContainer').hide();
            return;
        }

        $.ajax({
            url: '<?= site_url('cbtstatus/get_siswa_ujian') ?>',
            type: 'GET',
            data: { id_jadwal: jadwal },
            dataType: 'json',
            success: function (res) {
                if (!res.status) {
                    showToast('error', res.message);
                    return;
                }

                $('#statsContainer').show();
                $('#statTotal').text(res.stats.total || 0);
                $('#statSedang').text(res.stats.sedang_ujian || 0);
                $('#statSelesai').text(res.stats.selesai || 0);
                $('#statBelum').text(res.stats.belum_mulai || 0);

                $('#badgeSedang').text(res.stats.sedang_ujian || 0);
                $('#badgeSelesai').text(res.stats.selesai || 0);

                renderSedang(res.sedang_ujian || []);
                renderSelesai(res.selesai || []);
            }
        });
    }

    function renderSedang(data) {
        var html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> Tidak ada siswa sedang ujian</td></tr>';
        } else {
            data.forEach(function (s, i) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td><strong>' + (s.nama || '-') + '</strong><br><small class="text-muted">' + (s.nis || '-') + '</small></td>' +
                    '<td>' + (s.nama_kelas || '-') + '</td>' +
                    '<td class="text-center"><span class="badge bg-light text-dark shadow-sm border">' + formatTime(s.mulai) + '</span></td>' +
                    '<td class="text-center">' +
                    '<button class="btn btn-warning btn-xs me-1 shadow-sm" onclick="resetLogin(' + s.id_cbt_siswa + ')" title="Reset Login"><i class="bi bi-arrow-clockwise"></i></button> ' +
                    '<button class="btn btn-info btn-xs shadow-sm" onclick="resetWaktu(' + s.id_cbt_siswa + ')" title="Tambah Waktu"><i class="bi bi-alarm"></i></button>' +
                    '</td>' +
                    '</tr>';
            });
        }
        $('#tableSedang').html(html);
    }

    function renderSelesai(data) {
        var html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> Belum ada siswa selesai</td></tr>';
        } else {
            data.forEach(function (s, i) {
                html += '<tr>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td><strong>' + (s.nama || '-') + '</strong><br><small class="text-muted">' + (s.nis || '-') + '</small></td>' +
                    '<td>' + (s.nama_kelas || '-') + '</td>' +
                    '<td class="text-center"><span class="badge bg-success shadow-sm">' + (s.lama_ujian || '-') + '</span></td>' +
                    '<td class="text-center"><span class="badge bg-light text-dark shadow-sm border">' + formatTime(s.selesai) + '</span></td>' +
                    '</tr>';
            });
        }
        $('#tableSelesai').html(html);
    }


    function formatTime(datetime) {
        if (!datetime) return '-';
        var d = new Date(datetime);
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    function resetLogin(id) {
        Swal.fire({
            title: 'Reset Login?',
            text: 'Siswa akan bisa login kembali dari device lain',
            icon: 'question',
            showCancelButton: true
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post('<?= site_url('cbtstatus/reset_login') ?>', { id_cbt_siswa: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                    res.status ? showToast('success', res.message) : showToast('error', res.message);
                    loadMonitoring();
                }, 'json');
            }
        });
    }

    function resetWaktu(id) {
        Swal.fire({
            title: 'Tambah Waktu',
            input: 'number',
            inputLabel: 'Berapa menit?',
            inputValue: 15,
            showCancelButton: true
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                $.post('<?= site_url('cbtstatus/reset_waktu') ?>', { id_cbt_siswa: id, tambah_menit: result.value, [CSRF_NAME]: CSRF_HASH }, function (res) {
                    res.status ? showToast('success', res.message) : showToast('error', res.message);
                    loadMonitoring();
                }, 'json');
            }
        });
    }
</script>