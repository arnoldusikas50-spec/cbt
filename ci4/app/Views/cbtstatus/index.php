<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="row g-4 mb-4">
    <!-- Filters -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Jadwal Ujian</label>
                        <select id="selectJadwal" class="form-select">
                            <option value="">-- Pilih Jadwal --</option>
                            <?php foreach ($jadwals ?? [] as $jadwal): ?>
                                <option value="<?= $jadwal->id_jadwal ?>">
                                    <?= esc($jadwal->nama_mapel ?? $jadwal->bank_nama) ?>
                                    (
                                    <?= date('d/m/Y', strtotime($jadwal->tgl_mulai)) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ruang</label>
                        <select id="selectRuang" class="form-select">
                            <option value="">Semua Ruang</option>
                            <?php foreach ($ruangs ?? [] as $ruang): ?>
                                <option value="<?= $ruang->id_ruang ?>">
                                    <?= esc($ruang->nama_ruang) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sesi</label>
                        <select id="selectSesi" class="form-select">
                            <option value="">Semua Sesi</option>
                            <?php foreach ($sesis ?? [] as $sesi): ?>
                                <option value="<?= $sesi->id_sesi ?>">
                                    <?= esc($sesi->nama_sesi) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" onclick="loadMonitoring()">
                            <i class="bi bi-arrow-repeat me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row g-4 mb-4" id="statsContainer" style="display: none;">
    <div class="col-md-3">
        <div class="info-box">
                <div class="info-icon info">
                <i class="bi bi-people"></i>
            </div>
            <div class="info-details">
                <h3 id="statTotal">0</h3>
                <p>Total Peserta</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
                <div class="info-icon" style="background: linear-gradient(135deg, #ed8936, #dd6b20);">
                <i class="bi bi-person-clock"></i>
            </div>
            <div class="info-details">
                <h3 id="statSedang">0</h3>
                <p>Sedang Ujian</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
                <div class="info-icon success">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="info-details">
                <h3 id="statSelesai">0</h3>
                <p>Selesai</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
                <div class="info-icon" style="background: linear-gradient(135deg, #718096, #4a5568);">
                <i class="bi bi-person-dash"></i>
            </div>
            <div class="info-details">
                <h3 id="statBelum">0</h3>
                <p>Belum Mulai</p>
            </div>
        </div>
    </div>
</div>

<!-- Tables -->
<div class="row g-4">
    <!-- Sedang Ujian -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-arrow-repeat text-warning me-2"></i>
                    Sedang Ujian
                </h5>
                <span class="badge bg-warning" id="badgeSedang">0</span>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Mulai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableSedang">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Pilih jadwal untuk melihat data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Selesai -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                    Selesai Ujian
                </h5>
                <span class="badge bg-success" id="badgeSelesai">0</span>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Durasi</th>
                            <th>Selesai</th>
                        </tr>
                    </thead>
                    <tbody id="tableSelesai">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Pilih jadwal untuk melihat data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let autoRefresh = null;
    const csrfToken = '<?= csrf_hash() ?>';

    document.getElementById('selectJadwal').addEventListener('change', function () {
        loadMonitoring();

        // Start auto-refresh
        if (this.value) {
            if (autoRefresh) clearInterval(autoRefresh);
            autoRefresh = setInterval(loadMonitoring, 10000); // Every 10 seconds
        } else {
            if (autoRefresh) clearInterval(autoRefresh);
        }
    });

    async function loadMonitoring() {
        const jadwal = document.getElementById('selectJadwal').value;
        const ruang = document.getElementById('selectRuang').value;
        const sesi = document.getElementById('selectSesi').value;

        if (!jadwal) {
            document.getElementById('statsContainer').style.display = 'none';
            return;
        }

        try {
            const url = new URL('<?= site_url('cbtstatus/get_siswa_ujian') ?>');
            url.searchParams.append('id_jadwal', jadwal);
            if (ruang) url.searchParams.append('id_ruang', ruang);
            if (sesi) url.searchParams.append('id_sesi', sesi);

            const response = await fetch(url);
            const data = await response.json();

            if (!data.status) {
                Swal.fire('Error', data.message || 'Gagal memuat data', 'error');
                return;
            }

            // Update stats
            document.getElementById('statsContainer').style.display = 'flex';
            document.getElementById('statTotal').textContent = data.stats.total || 0;
            document.getElementById('statSedang').textContent = data.stats.sedang_ujian || 0;
            document.getElementById('statSelesai').textContent = data.stats.selesai || 0;
            document.getElementById('statBelum').textContent = data.stats.belum_mulai || 0;

            document.getElementById('badgeSedang').textContent = data.stats.sedang_ujian || 0;
            document.getElementById('badgeSelesai').textContent = data.stats.selesai || 0;

            // Update tables
            renderSedangUjian(data.sedang_ujian || []);
            renderSelesai(data.selesai || []);

        } catch (error) {
            console.error('Error:', error);
        }
    }

    function renderSedangUjian(students) {
        const tbody = document.getElementById('tableSedang');

        if (!students.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada siswa sedang ujian</td></tr>';
            return;
        }

        tbody.innerHTML = students.map(s => `
            <tr>
                <td>
                    <strong>${s.nama || '-'}</strong><br>
                    <small class="text-muted">${s.nis || '-'}</small>
                </td>
                <td>${s.nama_kelas || '-'}</td>
                <td>${formatTime(s.mulai)}</td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="resetLogin(${s.id_cbt_siswa})" title="Reset Login">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button class="btn btn-sm btn-info" onclick="resetWaktu(${s.id_cbt_siswa})" title="Tambah Waktu">
                        <i class="bi bi-clock"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function renderSelesai(students) {
        const tbody = document.getElementById('tableSelesai');

        if (!students.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Belum ada siswa selesai</td></tr>';
            return;
        }

        tbody.innerHTML = students.map(s => `
            <tr>
                <td>
                    <strong>${s.nama || '-'}</strong><br>
                    <small class="text-muted">${s.nis || '-'}</small>
                </td>
                <td>${s.nama_kelas || '-'}</td>
                <td><span class="badge bg-success">${s.lama_ujian || '-'}</span></td>
                <td>${formatTime(s.selesai)}</td>
            </tr>
        `).join('');
    }

    function formatTime(datetime) {
        if (!datetime) return '-';
        const d = new Date(datetime);
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    async function resetLogin(idCbtSiswa) {
        const result = await Swal.fire({
            title: 'Reset Login?',
            text: 'Siswa akan bisa login kembali dari device lain.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal'
        });

        if (!result.isConfirmed) return;

        try {
            const formData = new FormData();
            formData.append('id_cbt_siswa', idCbtSiswa);
            formData.append('<?= csrf_token() ?>', csrfToken);

            const response = await fetch('<?= site_url('cbtstatus/reset_login') ?>', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status) {
                Swal.fire('Berhasil', data.message, 'success');
                loadMonitoring();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Terjadi kesalahan', 'error');
        }
    }

    async function resetWaktu(idCbtSiswa) {
        const { value: menit } = await Swal.fire({
            title: 'Tambah Waktu',
            input: 'number',
            inputLabel: 'Berapa menit?',
            inputValue: 15,
            inputAttributes: { min: 1, max: 60 },
            showCancelButton: true
        });

        if (!menit) return;

        try {
            const formData = new FormData();
            formData.append('id_cbt_siswa', idCbtSiswa);
            formData.append('tambah_menit', menit);
            formData.append('<?= csrf_token() ?>', csrfToken);

            const response = await fetch('<?= site_url('cbtstatus/reset_waktu') ?>', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status) {
                Swal.fire('Berhasil', data.message, 'success');
                loadMonitoring();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Terjadi kesalahan', 'error');
        }
    }
</script>
<?= $this->endSection() ?>