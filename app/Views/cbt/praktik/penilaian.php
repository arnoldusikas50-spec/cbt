<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-check2-square text-primary me-2"></i>
                    Penilaian Ujian Praktik
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('cbtpraktik') ?>">Ujian Praktik</a></li>
                    <li class="breadcrumb-item active">Penilaian</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Header Info -->
        <div class="card shadow-sm mb-4 fade-in-up">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-1"><?= esc($praktik->judul) ?></h5>
                        <p class="text-muted mb-0">
                            <span class="badge bg-info"><?= esc($praktik->nama_mapel) ?></span>
                            <span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $praktik->jenis_praktik)) ?></span>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group">
                            <a href="<?= base_url('cbtpraktik/exportExcel/' . $praktik->id) ?>" class="btn btn-success btn-sm">
                                <i class="bi bi-file-excel me-1"></i> Export Excel
                            </a>
                            <a href="<?= base_url('cbtpraktik/monitor/' . $praktik->id) ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-eye me-1"></i> Monitoring
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-2 col-6">
                <div class="card bg-primary text-white text-center shadow-sm">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $statistics->total_peserta ?? 0 ?></h4>
                        <small>Total Peserta</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-success text-white text-center shadow-sm">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $statistics->total_selesai ?? 0 ?></h4>
                        <small>Selesai</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-info text-white text-center shadow-sm">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= $statistics->total_dinilai ?? 0 ?></h4>
                        <small>Sudah Dinilai</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-warning text-dark text-center shadow-sm">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= ($statistics->total_selesai ?? 0) - ($statistics->total_dinilai ?? 0) ?></h4>
                        <small>Belum Dinilai</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-secondary text-white text-center shadow-sm">
                    <div class="card-body py-3">
                        <h4 class="mb-0"><?= number_format($statistics->rata_nilai ?? 0, 1) ?></h4>
                        <small>Rata-rata</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="card bg-dark text-white text-center shadow-sm">
                    <div class="card-body py-3">
<h4 class="mb-0"><?= $praktik->kkm ?></h4>
                    <small><?= ($praktik->kktp_mode ?? 'kkm') === 'kktp' ? 'KKTP (Tuntas ≥ Cakap 66)' : 'KKM (Tuntas ≥ KKM)' ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rubrik Info -->
        <?php if (!empty($rubrik)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Kriteria Penilaian (Rubrik)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Kode</th>
                                <th>Kriteria</th>
                                <th class="text-center">Bobot (%)</th>
                                <th class="text-center">Tipe</th>
                                <th>Level Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rubrik as $r): ?>
                            <tr>
                                <td><?= esc($r->kode_kriteria) ?></td>
                                <td><?= esc($r->nama_kriteria) ?></td>
                                <td class="text-center"><?= $r->bobot_persen ?>%</td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $r->tipe_penilaian === 'otomatis' ? 'success' : ($r->tipe_penilaian === 'hybrid' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($r->tipe_penilaian) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($r->levels)): ?>
                                        <?php foreach ($r->levels as $level): ?>
                                            <span class="badge bg-light text-dark border me-1">
                                                <?= $level->level ?>: <?= $level->nama_level ?> (<?= $level->nilai_min ?>-<?= $level->nilai_max ?>)
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Hasil Table -->
        <div class="card shadow-sm fade-in-up">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i>Daftar Hasil Peserta</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100 mb-0" id="tableHasil">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Nama Siswa</th>
                                <th class="text-center">Kelas</th>
                                <th class="text-center">Waktu Submit</th>
                                <th class="text-center">Durasi</th>
                                <th class="text-center">Nilai</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($hasil)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada hasil pengumpulan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($hasil as $k => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $k + 1 ?></td>
                                    <td>
                                        <div class="fw-bold"><?= esc($row->nama_siswa) ?></div>
                                        <small class="text-muted"><?= esc($row->nis) ?></small>
                                        <?php if ($row->total_pelanggaran > 0): ?>
                                            <span class="badge bg-danger ms-1" title="Pelanggaran">
                                                <i class="bi bi-exclamation-triangle"></i> <?= $row->total_pelanggaran ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= esc($row->nama_kelas) ?></td>
                                    <td class="text-center">
                                        <?= $row->waktu_submit ? date('d M Y H:i', strtotime($row->waktu_submit)) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row->durasi_pengerjaan_detik > 0): ?>
                                            <?= floor($row->durasi_pengerjaan_detik / 60) ?>m <?= $row->durasi_pengerjaan_detik % 60 ?>s
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row->nilai_akhir !== null): 
                                            $kktpPred = $row->nilai_akhir >= 86 ? 'M' : ($row->nilai_akhir >= 66 ? 'C' : ($row->nilai_akhir >= 41 ? 'L' : 'BB'));
                                            $kkmTuntas = ($praktik->kktp_mode ?? 'kkm') === 'kkm' && !empty($praktik->kkm) ? (float)$praktik->kkm : 66;
                                            $warna = $row->nilai_akhir >= $kkmTuntas ? 'success' : 'danger';
                                        ?>
                                            <span class="badge bg-<?= $warna ?> fs-6">
                                                <?= number_format($row->nilai_akhir, 1) ?>
                                            </span>
                                            <span class="badge bg-<?= $warna ?> bg-opacity-25 text-<?= $warna ?> ms-1"><?= $kktpPred ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row->status_penilaian === 'sudah_dinilai'): ?>
                                            <span class="badge bg-success">Sudah Dinilai</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Belum Dinilai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-primary btn-sm" 
                                                onclick="openNilaiModal(<?= $row->id ?>)" title="Beri Nilai">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penilaian -->
<div class="modal fade" id="modalNilai" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Form Penilaian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalNilaiContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
<?php $kkmTuntas = ($praktik->kktp_mode ?? 'kkm') === 'kkm' && !empty($praktik->kkm) ? (float)$praktik->kkm : 66; ?>
const MODAL_KKM = <?= $kkmTuntas ?>;
function openNilaiModal(hasilId) {
    $.get('<?= base_url('cbtpraktik/getHasilDetail') ?>?hasil_id=' + hasilId, function(res) {
        if (res.status) {
            let content = buildNilaiForm(res.data, res.rubriks);
            $('#modalNilaiContent').html(content);
            $('#modalNilai').modal('show');
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    }, 'json');
}

function buildNilaiForm(data, rubriks) {
    currentRubriks = rubriks || [];
    let rincian = {};
    try { rincian = data.rincian_nilai_rubrik ? JSON.parse(data.rincian_nilai_rubrik) : {}; } catch(e) { rincian = {}; }

    let html = '<form id="formNilai">';
    html += '<input type="hidden" name="hasil_id" value="' + data.id + '">';
    
    html += '<div class="mb-3">';
    html += '<h6 class="fw-bold">' + data.nama_siswa + '</h6>';
    html += '<small class="text-muted">' + data.nis + ' - ' + data.nama_kelas + '</small>';
    html += '</div>';
    
    if (data.kode_jawaban) {
        html += '<div class="mb-3">';
        html += '<label class="form-label fw-bold">Kode Jawaban:</label>';
        html += '<pre class="bg-light p-2 rounded"><code>' + escapeHtml(data.kode_jawaban) + '</code></pre>';
        html += '</div>';
    }
    
    if (data.jawaban_teks) {
        html += '<div class="mb-3">';
        html += '<label class="form-label fw-bold">Jawaban Teks:</label>';
        html += '<div class="bg-light p-2 rounded">' + escapeHtml(data.jawaban_teks) + '</div>';
        html += '</div>';
    }
    
    if (currentRubriks.length > 0) {
        html += '<hr><h6 class="fw-bold mb-3">Penilaian per Kriteria</h6>';
        currentRubriks.forEach(function(r) {
            let val = '';
            let rv = rincian[r.id];
            if (rv !== undefined) {
                val = (typeof rv === 'object' && rv !== null) ? (rv.nilai ?? rv.nilai_sementara ?? '') : rv;
            }
            html += '<div class="mb-3">';
            html += '<label class="form-label">' + r.kode_kriteria + ' - ' + r.nama_kriteria + ' (' + r.bobot_persen + '%)</label>';
            html += '<input type="number" name="nilai_rubrik[' + r.id + ']" class="form-control input-kriteria" data-bobot="' + r.bobot_persen + '" min="0" max="100" step="0.1" placeholder="0-100" value="' + val + '">';
            html += '</div>';
        });
    }
    
    html += '<div class="mb-3">';
    html += '<label class="form-label fw-bold d-flex justify-content-between align-items-center">';
    html += '<span>Nilai Akhir <small class="text-muted fw-normal">(otomatis dari kriteria)</small></span>';
    html += '<span id="badgeKktp" class="badge bg-secondary">-</span>';
    html += '</label>';
    html += '<input type="number" name="nilai_manual" id="nilaiManual" class="form-control form-control-lg" min="0" max="100" step="0.1" required>';
    html += '</div>';
    
    html += '<div class="mb-3">';
    html += '<label class="form-label">Feedback/Catatan</label>';
    html += '<textarea name="feedback" class="form-control" rows="3">' + (data.feedback_guru ? escapeHtml(data.feedback_guru) : '') + '</textarea>';
    html += '</div>';
    
    html += '<div class="d-flex justify-content-between">';
    html += '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>';
    html += '<button type="submit" class="btn btn-primary">Simpan Nilai</button>';
    html += '</div>';
    
    html += '</form>';

    // Prefill final nilai, lalu hitung otomatis saat kriteria diubah
    if (data.nilai_akhir !== null && data.nilai_akhir !== undefined) {
        $('#nilaiManual').val(parseFloat(data.nilai_akhir).toFixed(1));
    }
    hitungOtomatis();
    return html;
}

// Hitung nilai akhir (weighted average) — sama dengan rumus backend
let currentRubriks = [];
function hitungOtomatis() {
    if ($('.input-kriteria').length === 0) return; // tanpa kriteria: biarkan input manual
    let weighted = 0, sumBobot = 0;
    $('.input-kriteria').each(function() {
        const v = parseFloat($(this).val());
        const b = parseFloat($(this).data('bobot')) || 0;
        if (b > 0) { weighted += (isNaN(v) ? 0 : v) * b; sumBobot += b; }
    });
    const final = sumBobot > 0 ? (weighted / sumBobot) : 0;
    $('#nilaiManual').val(sumBobot > 0 ? final.toFixed(2) : '');

    let pred = '-', warna = 'secondary';
    if (final > 0) {
        if (final >= 86) { pred = 'M (Mahir)'; warna = 'success'; }
        else if (final >= 66) { pred = 'C (Cakap)'; warna = 'info'; }
        else if (final >= 41) { pred = 'L (Layak)'; warna = 'warning'; }
        else { pred = 'BB (Baru Berkembang)'; warna = 'danger'; }
        pred += final >= MODAL_KKM ? ' • TUNTAS' : ' • BELUM TUNTAS';
        if (final < MODAL_KKM) warna = 'danger';
    }
    $('#badgeKktp').removeClass('bg-secondary bg-success bg-info bg-warning bg-danger')
        .addClass('bg-' + warna).text(pred);
}
$(document).on('input', '.input-kriteria', hitungOtomatis);

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

$(document).on('submit', '#formNilai', function(e) {
    e.preventDefault();
    $.ajax({
        url: '<?= base_url('cbtpraktik/simpanPenilaian') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.status) {
                Swal.fire('Berhasil', res.message, 'success').then(() => {
                    $('#modalNilai').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
