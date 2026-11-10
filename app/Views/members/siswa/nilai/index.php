<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 container-p-x main-content-overlap">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Header Card -->
            <div class="card card-modern border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="fw-bold mb-1">📊 Nilai & Rapor</h4>
                            <p class="text-muted mb-0">Lihat nilai dan rapor semester kamu</p>
                        </div>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-light border rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>

                    <!-- Info Siswa -->
                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Nama Lengkap</small>
                                <strong>
                                    <?= $siswa->nama ?? '-' ?>
                                </strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">NIS</small>
                                <strong>
                                    <?= $siswa->nis ?? '-' ?>
                                </strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Kelas</small>
                                <strong>
                                    <?= $siswa->nama_kelas ?? '-' ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tahun & Semester -->
            <div class="card card-modern border-0 mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Pelajaran</label>
                            <select class="form-select" id="filter-tp">
                                <?php foreach ($tp as $t): ?>
                                    <option value="<?= $t->id_tp ?>" <?= $t->id_tp == $tp_active->id_tp ? 'selected' : '' ?>>
                                        <?= $t->tahun ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Semester</label>
                            <select class="form-select" id="filter-smt">
                                <?php foreach ($smt as $s): ?>
                                    <option value="<?= $s->id_smt ?>" <?= $s->id_smt == $smt_active->id_smt ? 'selected' : '' ?>>
                                        <?= $s->nama_smt ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nilai Per Mapel -->
            <div class="card card-modern border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">📚 Nilai Per Mata Pelajaran</h5>

                    <div id="nilai-content">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3 text-muted">Memuat data nilai...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        loadNilai();

        $('#filter-tp, #filter-smt').change(function () {
            loadNilai();
        });
    });

    function loadNilai() {
        const tp = $('#filter-tp').val();
        const smt = $('#filter-smt').val();

        $('#nilai-content').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">Memuat data nilai...</p>
            </div>
        `);

        // Simulasi load data (ganti dengan AJAX sebenarnya)
        setTimeout(function () {
            // Sample data (ganti dengan AJAX call ke backend)
            const sampleData = [
                { id: 1, nama_mapel: 'Matematika', kkm: 70, nilai_harian: 85, nilai_pts: 80, nilai_pas: 90, nilai_akhir: 85 },
                { id: 2, nama_mapel: 'Bahasa Indonesia', kkm: 70, nilai_harian: 88, nilai_pts: 85, nilai_pas: 87, nilai_akhir: 87 },
                { id: 3, nama_mapel: 'Bahasa Inggris', kkm: 70, nilai_harian: 90, nilai_pts: 88, nilai_pas: 92, nilai_akhir: 90 },
                { id: 4, nama_mapel: 'IPA', kkm: 70, nilai_harian: 82, nilai_pts: 78, nilai_pas: 85, nilai_akhir: 82 },
                { id: 5, nama_mapel: 'IPS', kkm: 70, nilai_harian: 84, nilai_pts: 82, nilai_pas: 88, nilai_akhir: 85 },
            ];

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="fw-bold">No</th>
                                <th class="fw-bold">Mata Pelajaran</th>
                                <th class="text-center fw-bold">KKM</th>
                                <th class="text-center fw-bold">Harian</th>
                                <th class="text-center fw-bold">PTS</th>
                                <th class="text-center fw-bold">PAS</th>
                                <th class="text-center fw-bold">Akhir</th>
                                <th class="text-center fw-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (sampleData.length > 0) {
                sampleData.forEach((item, index) => {
                    const predicate = item.nilai_akhir >= item.kkm ?
                        '<span class="badge bg-success">Tuntas</span>' :
                        '<span class="badge bg-danger">Belum Tuntas</span>';

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <strong>${item.nama_mapel}</strong>
                            </td>
                            <td class="text-center">${item.kkm}</td>
                            <td class="text-center">${item.nilai_harian || '-'}</td>
                            <td class="text-center">${item.nilai_pts || '-'}</td>
                            <td class="text-center">${item.nilai_pas || '-'}</td>
                            <td class="text-center">
                                <strong class="fs-5">${item.nilai_akhir}</strong>
                                <br>${predicate}
                            </td>
                            <td class="text-center">
                                <a href="${base_url}nilai/detail/${item.id}" 
                                   class="btn btn-sm btn-primary rounded-pill">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html += `
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p>Belum ada data nilai</p>
                            </div>
                        </td>
                    </tr>
                `;
            }

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            if (sampleData.length > 0) {
                // Calculate average
                const total = sampleData.reduce((sum, item) => sum + item.nilai_akhir, 0);
                const average = (total / sampleData.length).toFixed(2);

                html += `
                    <div class="alert alert-success bg-success bg-opacity-10 border-0 mt-3">
                        <div class="row text-center">
                            <div class="col">
                                <small class="text-muted d-block">Rata-rata Nilai</small>
                                <h3 class="fw-bold mb-0">${average}</h3>
                            </div>
                        </div>
                    </div>
                `;
            }

            $('#nilai-content').html(html);
        }, 500);
    }
</script>
<?= $this->endSection() ?>