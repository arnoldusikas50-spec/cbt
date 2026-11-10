<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 container-p-x main-content-overlap">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Header -->
            <div class="card card-modern border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-1">📖 Detail Nilai Mata Pelajaran</h4>
                            <p class="text-muted mb-0" id="mapel-name">Loading...</p>
                        </div>
                        <a href="<?= base_url('nilai') ?>" class="btn btn-light border rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Nilai -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3"
                                style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-pencil-square fs-3"></i>
                            </div>
                            <small class="text-muted d-block">Nilai Harian</small>
                            <h3 class="fw-bold mb-0" id="nilai-harian">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3"
                                style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-clipboard-check fs-3"></i>
                            </div>
                            <small class="text-muted d-block">PTS</small>
                            <h3 class="fw-bold mb-0" id="nilai-pts">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3"
                                style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-file-earmark-text fs-3"></i>
                            </div>
                            <small class="text-muted d-block">PAS</small>
                            <h3 class="fw-bold mb-0" id="nilai-pas">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                        <div class="card-body text-center p-4">
                            <div class="icon-box bg-success bg-opacity-25 text-success rounded-circle mx-auto mb-3"
                                style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-trophy fs-3"></i>
                            </div>
                            <small class="text-success fw-bold d-block">NILAI AKHIR</small>
                            <h2 class="fw-bold mb-0 text-success" id="nilai-akhir">-</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Nilai -->
            <div class="card card-modern border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">📝 Rincian Penilaian</h5>

                    <div id="detail-content">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3 text-muted">Memuat rincian...</p>
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
    const idMapel = <?= $id_mapel ?? 0 ?>;

    $(document).ready(function () {
        loadDetailNilai();
    });

    function loadDetailNilai() {
        // Simulasi data (ganti dengan AJAX call)
        setTimeout(function () {
            const sampleDetail = {
                nama_mapel: 'Matematika',
                nama_guru: 'Budi Santoso, S.Pd',
                kkm: 70,
                nilai_harian: 85,
                nilai_pts: 80,
                nilai_pas: 90,
                nilai_akhir: 85,
                rincian_harian: [
                    { tanggal: '2026-01-05', keterangan: 'Ulangan Harian 1 - Aljabar', nilai: 88 },
                    { tanggal: '2026-01-12', keterangan: 'Ulangan Harian 2 - Geometri', nilai: 82 },
                    { tanggal: '2026-01-19', keterangan: 'Tugas Proyek', nilai: 85 }
                ],
                catatan_guru: 'Siswa menunjukkan perkembangan yang baik. Pertahankan!'
            };

            // Update header
            $('#mapel-name').text(sampleDetail.nama_mapel);
            $('#nilai-harian').text(sampleDetail.nilai_harian);
            $('#nilai-pts').text(sampleDetail.nilai_pts);
            $('#nilai-pas').text(sampleDetail.nilai_pas);
            $('#nilai-akhir').text(sampleDetail.nilai_akhir);

            // Build detail HTML
            let html = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">Guru Pengampu</small>
                        <p class="fw-bold mb-0">${sampleDetail.nama_guru}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted">KKM</small>
                        <p class="fw-bold mb-0">${sampleDetail.kkm}</p>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold mb-3">Rincian Nilai Harian</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-center">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            sampleDetail.rincian_harian.forEach(item => {
                html += `
                    <tr>
                        <td>${item.tanggal}</td>
                        <td>${item.keterangan}</td>
                        <td class="text-center">
                            <span class="badge ${item.nilai >= sampleDetail.kkm ? 'bg-success' : 'bg-danger'}">
                                ${item.nilai}
                            </span>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            if (sampleDetail.catatan_guru) {
                html += `
                    <div class="alert alert-info bg-info bg-opacity-10 border-0 mt-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-chat-left-text me-2"></i>Catatan Guru</h6>
                        <p class="mb-0">${sampleDetail.catatan_guru}</p>
                    </div>
                `;
            }

            $('#detail-content').html(html);
        }, 500);
    }
</script>
<?= $this->endSection() ?>