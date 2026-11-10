<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Content Header -->
<?= $this->include('guru/nilai/partials/_kit') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-5">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-calculator text-primary me-2"></i><?= esc($judul) ?>
                </h3>
                <p class="text-muted small mb-0 mt-1">
                    <?php if (isset($id_guru_selected) && ($is_admin || $is_panitia)): ?>
                        <span class="badge bg-warning text-dark me-1" title="Monitoring Mode">
                            <i class="bi bi-eye-fill me-1"></i>MONITORING: <?= (is_object($guru) ? $guru->nama_guru : (is_array($guru) ? ($guru['nama_guru'] ?? 'Guru') : 'Guru')) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($nama_mapel && $nama_kelas): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary border me-1"><?= esc($nama_mapel) ?></span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border"><?= esc($nama_kelas) ?></span>
                    <?php else: ?>
                        Rekap Nilai &amp; Kalkulasi Rapor Akhir Semester
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-sm-7">
                <div class="d-flex align-items-center justify-content-sm-end gap-2 flex-wrap">
                    <?php if (!empty($siswa)): ?>
                        <button class="btn btn-warning btn-sm shadow-sm fw-bold text-white" id="btnImportCbt" <?= (!$id_mapel || !$id_kelas) ? 'disabled' : '' ?>>
                            <i class="bi bi-lightning-charge-fill me-1"></i> Ambil Nilai Ujian
                        </button>
                        <div class="btn-group shadow-sm">
                            <button type="button" class="btn btn-outline-success btn-sm fw-bold dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-printer me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li><a class="dropdown-item small py-2" href="<?= base_url('guru/nilai/rekapExcel?mapel=' . $id_mapel . '&kelas=' . $id_kelas) ?>"><i class="bi bi-file-earmark-excel text-success me-2"></i> Download Excel</a></li>
                                <li><a class="dropdown-item small py-2" href="<?= base_url('guru/nilai/rekapPdf?mapel=' . $id_mapel . '&kelas=' . $id_kelas) ?>" target="_blank"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Download PDF</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-success btn-sm shadow-sm fw-bold" id="btnSaveAll">
                            <i class="bi bi-cloud-upload me-1"></i> Simpan Semua
                        </button>
                    <?php endif; ?>
                    <a href="<?= base_url('guru/nilai') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php
        $stat = $statistik;
        $hasStats = $stat && $stat->total_siswa > 0;
        ?>

        <?php if ($hasStats): ?>
        <!-- Statistics Dashboard -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card card-default my-shadow p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-bar-chart-fill fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stat->rata_rata ?: 0 ?></h4>
                            <span class="text-muted small">Rata-rata Kelas</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-default my-shadow p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stat->tertinggi ?: 0 ?></h4>
                            <span class="text-muted small">Nilai Tertinggi</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-default my-shadow p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-graph-down-arrow fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stat->terendah ?: 0 ?></h4>
                            <span class="text-muted small">Nilai Terendah</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card card-default my-shadow p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-patch-check-fill fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stat->tuntas ?: 0 ?><small class="text-muted fs-6">/<?= $stat->total_siswa ?></small></h4>
                            <span class="text-muted small">Tuntas (≥75)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Weight Config Bar -->
        <?php
        $first_na = !empty($nilai_akhir) ? reset($nilai_akhir) : null;
        $def_proses = $first_na ? $first_na->bobot_proses : 40;
        $def_sas = $first_na ? $first_na->bobot_sas : 20;
        $def_sts = $first_na ? ($first_na->bobot_sts ?? 0) : 20;
        $def_praktik = $first_na ? ($first_na->bobot_praktik ?? 0) : 20;
        $has_praktik_weight = $def_praktik > 0;
        ?>
        <div class="card card-default my-shadow mb-4">
            <div class="card-body p-3">
                <div class="row align-items-center g-3">
                    <div class="col-lg-auto">
                        <div class="d-flex align-items-center text-dark">
                            <i class="bi bi-sliders2-vertical me-2 text-primary"></i>
                            <span class="fw-bold small text-uppercase">Bobot Penilaian:</span>
                        </div>
                    </div>
                    <div class="col-lg">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="weight-item d-flex align-items-center bg-light px-2 py-1 rounded border">
                                <span class="fw-semibold text-muted small me-2" style="font-size: 0.7rem;">SUMATIF (LM)</span>
                                <div class="input-weight-wrapper">
                                    <input type="number" id="bobotProses" value="<?= $def_proses ?>" min="0" max="100">%
                                </div>
                            </div>
                            <div class="weight-sep text-muted opacity-50"><i class="bi bi-plus"></i></div>
                            <div class="weight-item d-flex align-items-center bg-light px-2 py-1 rounded border">
                                <span class="fw-semibold text-muted small me-2" style="font-size: 0.7rem;">STS</span>
                                <div class="input-weight-wrapper">
                                    <input type="number" id="bobotSts" value="<?= $def_sts ?>" min="0" max="100">%
                                </div>
                            </div>
                            <div class="weight-sep text-muted opacity-50"><i class="bi bi-plus"></i></div>
                            <div class="weight-item d-flex align-items-center bg-light px-2 py-1 rounded border">
                                <span class="fw-semibold text-muted small me-2" style="font-size: 0.7rem;">SAS</span>
                                <div class="input-weight-wrapper">
                                    <input type="number" id="bobotSas" value="<?= $def_sas ?>" min="0" max="100">%
                                </div>
                            </div>
                            
                            <div class="weight-sep text-muted opacity-50"><i class="bi bi-plus"></i></div>
                            <div class="weight-item d-flex align-items-center bg-light px-2 py-1 rounded border" id="wrapperBobotPraktik">
                                <div class="form-check form-switch me-2 mb-0" style="min-height: auto;">
                                    <input class="form-check-input" type="checkbox" id="usePraktik" <?= $has_praktik_weight ? 'checked' : '' ?> style="cursor: pointer; width: 2em;">
                                </div>
                                <span class="fw-semibold text-muted small me-2" style="font-size: 0.7rem;">PRAKTIK</span>
                                <div class="input-weight-wrapper">
                                    <input type="number" id="bobotPraktik" value="<?= $def_praktik ?>" min="0" max="100" <?= !$has_praktik_weight ? 'disabled' : '' ?>>%
                                </div>
                            </div>

                            <div class="weight-sep text-muted opacity-50"><i class="bi bi-pause" style="transform: rotate(90deg); display: inline-block;"></i></div>
                            <div class="badge bg-primary px-3 py-2 fw-bold" id="bobotTotal">100%</div>
                        </div>
                    </div>
                    <div class="col-lg-auto text-end">
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-primary btn-sm fw-bold shadow-sm" id="btnSaveBobot">
                                <i class="bi bi-save me-1"></i> Simpan Bobot
                            </button>
                            <button class="btn btn-outline-primary btn-sm fw-bold shadow-sm" id="btnRecalcAll">
                                <i class="bi bi-arrow-clockwise me-1"></i> Hitung Rapor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Section -->
        <div class="card card-default my-shadow mb-4">
            <?php if (empty($siswa)): ?>
                <div class="text-center py-5">
                    <div class="bg-light p-4 rounded-circle d-inline-block mb-3">
                        <i class="bi bi-people text-muted fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Tidak Ada Data Siswa</h5>
                    <p class="text-muted small">Pilih mata pelajaran dan kelas pada filter dashboard.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-modern align-middle mb-0" id="table-nilai-akhir">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th style="min-width: 200px;">Siswa</th>
                                <th class="text-center" style="width: 80px;">Sumatif</th>
                                <th class="text-center" style="width: 70px;">STS</th>
                                <th class="text-center" style="width: 70px;">Praktik</th>
                                <th class="text-center" style="width: 90px;">SAS (AS)</th>
                                <th class="text-center" style="width: 90px;">Rapor</th>
                                <th class="text-center" style="width: 60px;">Pred</th>
                                <th>Capaian Kompetensi (Deskripsi Rapor)</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php $no = 1; foreach ($siswa as $s): ?>
                                <?php
                                $id = $s->id_siswa;
                                $proc = isset($rekap_proses[$id]) ? $rekap_proses[$id] : ['total' => 0, 'count' => 0, 'highest_desc' => '', 'lowest_desc' => ''];
                                $avg_proc = $proc['count'] > 0 ? round($proc['total'] / $proc['count'], 0) : 0;
                                
                                $sts_data = isset($rekap_sts[$id]) ? $rekap_sts[$id] : ['nilai' => 0];
                                $avg_sts = $sts_data['nilai'] ?: 0;
                                
                                $praktik_data = isset($rekap_praktik[$id]) ? $rekap_praktik[$id] : ['nilai' => 0, 'judul' => ''];
                                $val_praktik = $praktik_data['nilai'] ?: 0;

                                $ex = isset($nilai_akhir[$id]) ? $nilai_akhir[$id] : null;
                                $sas = $ex ? $ex->nilai_sas : '';
                                $rapor = $ex ? $ex->nilai_rapor : '';

                                if ($ex && $ex->deskripsi_tinggi) {
                                    $desc_high = $ex->deskripsi_tinggi;
                                } else {
                                    $auto_desc = \App\Models\NilaiModel::generateDeskripsi($s->nama, $proc['highest_desc'] ?? '', $proc['lowest_desc'] ?? '', $avg_proc);
                                    $desc_high = $auto_desc['tinggi'];
                                }
                                
                                if ($ex && $ex->deskripsi_rendah) {
                                    $desc_low = $ex->deskripsi_rendah;
                                } else {
                                    $auto_desc = $auto_desc ?? \App\Models\NilaiModel::generateDeskripsi($s->nama, $proc['highest_desc'] ?? '', $proc['lowest_desc'] ?? '', $avg_proc);
                                    $desc_low = $auto_desc['rendah'];
                                }

                                if ($proc['count'] == 0 && !$ex) {
                                    $desc_high = ''; $desc_low = '';
                                }

                                $predikat = \App\Models\NilaiModel::getPredikat($rapor ?: $avg_proc);
                                ?>
                                <tr data-id="<?= $id ?>" class="student-row">
                                    <td class="text-center text-muted small fw-medium"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 bg-light text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle border" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                <?= substr($s->nama, 0, 1) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark lh-1 mb-1" style="font-size: 0.85rem;"><?= $s->nama ?></div>
                                                <div class="text-muted fs-xs">NISN: <?= $s->nisn ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="avg-bubble bg-primary bg-opacity-10 text-primary border-primary border-opacity-10 border"><?= $avg_proc ?: '—' ?></div>
                                        <input type="hidden" class="val-avg-proc" value="<?= $avg_proc ?>">
                                    </td>
                                    <td class="text-center">
                                        <div class="avg-bubble bg-info bg-opacity-10 text-info border-info border-opacity-10 border"><?= $avg_sts ? round($avg_sts, 0) : '—' ?></div>
                                        <input type="hidden" class="val-avg-sts" value="<?= $avg_sts ?>">
                                    </td>
                                    <td class="text-center">
                                        <div class="avg-bubble bg-purple bg-opacity-10 text-purple border-purple border-opacity-10 border cell-praktik" title="<?= isset($rekap_praktik[$id]) ? esc($rekap_praktik[$id]['judul']) : 'Belum ada nilai praktik' ?>">
                                            <?= $val_praktik ? round($val_praktik, 0) : '—' ?>
                                        </div>
                                        <input type="hidden" class="val-avg-praktik" value="<?= $val_praktik ?>">
                                    </td>
                                    <td class="p-2">
                                        <input type="number" 
                                            class="form-control sts-input-premium sas-input-premium text-center fw-bold val-sas"
                                            value="<?= $sas ?>" min="0" max="100" placeholder="0" data-siswa="<?= $id ?>">
                                    </td>
                                    <td class="p-2">
                                        <input type="number" 
                                            class="form-control sts-input-premium text-center fw-bold bg-light val-rapor"
                                            value="<?= $rapor ?>" readonly tabindex="-1">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-predikat badge-<?= ($rapor || $avg_proc > 0) ? $predikat['color'] : 'secondary' ?> shadow-sm">
                                            <?= ($rapor || $avg_proc > 0) ? $predikat['huruf'] : '—' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="desc-group d-flex flex-column gap-1">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-success bg-opacity-10 text-success border-0 px-2" title="Tertinggi"><i class="bi bi-graph-up fs-xs"></i></span>
                                                <textarea class="form-control modern-textarea val-desc-high" rows="1" placeholder="Capaian Tertinggi..."><?= esc($desc_high) ?></textarea>
                                            </div>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-danger bg-opacity-10 text-danger border-0 px-2" title="Perlu Bimbingan"><i class="bi bi-graph-down fs-xs"></i></span>
                                                <textarea class="form-control modern-textarea border-dashed val-desc-low" rows="1" placeholder="Perlu Bimbingan..."><?= esc($desc_low) ?></textarea>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <button class="btn btn-primary btn-sm rounded-circle btn-save-row shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Simpan Baris Ini">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                            <div class="row-save-indicator" id="indicator-<?= $id ?>">
                                                <i class="bi bi-check-circle-fill opacity-0"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Status Bar -->
                <div class="card-footer bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="save-status-pill shadow-sm" id="globalSaveStatus">
                            <i class="bi bi-info-circle me-1"></i> Klik simpan untuk per kelas atau per baris
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// (toastr default & tooltip disiapkan oleh kit bersama)
$(document).ready(function () {
    const ID_MAPEL = <?= $id_mapel ?>;
    const ID_KELAS = <?= $id_kelas ?>;
    const ID_GURU = <?= $id_guru_selected ?? 0 ?>;

    function getWeights() {
        let p = parseInt($('#bobotProses').val()) || 0;
        let sts = parseInt($('#bobotSts').val()) || 0;
        let s = parseInt($('#bobotSas').val()) || 0;
        let usePr = $('#usePraktik').is(':checked');
        let pr = usePr ? (parseInt($('#bobotPraktik').val()) || 0) : 0;
        
        let total = p + sts + s + pr;
        $('#bobotTotal').text(total + '%');
        $('#bobotTotal').attr('class', 'total-weight-badge ' + (total === 100 ? 'bg-success text-white' : 'bg-danger text-white'));
        return { proses: p, sts: sts, sas: s, praktik: pr, usePraktik: usePr };
    }

    $('#bobotProses, #bobotSts, #bobotSas, #bobotPraktik').on('input', function() { getWeights(); });
    
    $('#usePraktik').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('#bobotPraktik').prop('disabled', !isChecked);
        
        if (!isChecked) {
            $('#bobotPraktik').data('old-val', $('#bobotPraktik').val());
            $('#bobotPraktik').val(0);
        } else {
            let oldVal = $('#bobotPraktik').data('old-val') || 20;
            $('#bobotPraktik').val(oldVal == 0 ? 20 : oldVal);
        }

        $('.cell-praktik').toggleClass('opacity-50', !isChecked);
        getWeights();
        $('.student-row').each(function() { calculateRapor($(this)); });
    });

    // Initialize state
    if (!$('#usePraktik').is(':checked')) {
        $('.cell-praktik').addClass('opacity-50');
    }
    getWeights();
    // Trigger initial calculation for all rows on load
    $('.student-row').each(function() { calculateRapor($(this)); });

    function calculateRapor(row) {
        let w = getWeights();
        let avg = parseFloat(row.find('.val-avg-proc').val()) || 0;
        let sts = parseFloat(row.find('.val-avg-sts').val()) || 0;
        let sas = parseFloat(row.find('.val-sas').val()) || 0;
        let praktik = w.usePraktik ? (parseFloat(row.find('.val-avg-praktik').val()) || 0) : 0;
        
        let rapor = 0;
        let totalWeight = w.proses + w.sts + w.sas + w.praktik;
        if (totalWeight > 0) {
            // Use normalized calculation to handle cases where total weight != 100
            rapor = Math.round(
                ((avg * w.proses) + 
                 (sts * w.sts) + 
                 (sas * w.sas) + 
                 (praktik * w.praktik)) / totalWeight
            );
        }
        
        if (sas === 0 && sts === 0 && avg === 0 && praktik === 0) rapor = 0;
        
        row.find('.val-rapor').val(rapor);
        
        // Update predikat
        let predikat = getPredikat(rapor);
        let badge = row.find('.badge-predikat');
        badge.text(rapor > 0 ? predikat.huruf : '—');
        badge.attr('class', 'badge-predikat shadow-sm badge-' + (rapor > 0 ? predikat.color : 'secondary'));
        
        // Color SAS input
        let input = row.find('.val-sas');
        input.removeClass('grade-premium-a grade-premium-b grade-premium-c grade-premium-d');
        if (sas >= 90) input.addClass('grade-premium-a');
        else if (sas >= 80) input.addClass('grade-premium-b');
        else if (sas >= 70) input.addClass('grade-premium-c');
        else if (sas > 0) input.addClass('grade-premium-d');

        return rapor;
    }

    function getPredikat(val) {
        if (val >= 90) return {huruf: 'A', color: 'success'};
        if (val >= 80) return {huruf: 'B', color: 'primary'};
        if (val >= 70) return {huruf: 'C', color: 'warning'};
        return {huruf: 'D', color: 'danger'};
    }

    $('.val-sas').on('input change', function () { calculateRapor($(this).closest('tr')); });

    // Pilih isi cell SAS saat fokus agar cepat ditimpa
    $('.val-sas').on('focus', function () { $(this).select(); });

    // Navigasi baris untuk input SAS (Enter / panah bawah-atas)
    $('.val-sas').on('keydown', function (e) {
        if (['Enter', 'ArrowDown', 'ArrowUp'].indexOf(e.key) === -1) return;
        e.preventDefault();
        let row = $(this).closest('tr');
        let targetRow = e.key === 'ArrowUp' ? row.prevAll('tr:visible').first() : row.nextAll('tr:visible').first();
        if (targetRow.length) targetRow.find('.val-sas').focus().select();
    });

    $('#btnRecalcAll').on('click', function() {
        $('.student-row').each(function() { calculateRapor($(this)); });
        toastr.success('Nilai dikalkulasi ulang sesuai bobot.');
    });

    $('#btnSaveBobot').on('click', function() {
        let btn = $(this);
        let w = getWeights();
        
        if (w.proses + w.sts + w.sas + w.praktik !== 100) {
            Swal.fire('Perhatian', 'Total bobot harus 100%!', 'warning');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');
        
        $.ajax({
            url: "<?= base_url('guru/nilai/saveBobot') ?>",
            type: "POST",
            data: {
                id_mapel: ID_MAPEL,
                id_kelas: ID_KELAS,
                id_guru: ID_GURU,
                bobot_proses: w.proses,
                bobot_sts: w.sts,
                bobot_sas: w.sas,
                bobot_praktik: w.praktik
            },
            success: function (resp) {
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Bobot');
                if (resp.status) {
                    toastr.success(resp.message);
                    // Optionally recalculate all rows to be sure
                    $('.student-row').each(function() { calculateRapor($(this)); });
                } else {
                    Swal.fire('Gagal', resp.message, 'error');
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Bobot');
                if (xhr.status === 401) {
                    Swal.fire('Sesi Berakhir', 'Sesi Anda telah berakhir. Silakan login kembali.', 'warning').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', 'Sistem gagal menyimpan bobot. Silakan coba lagi.', 'error');
                }
            }
        });
    });

    $('.btn-save-row').on('click', function () { saveRow($(this).closest('tr'), $(this)); });

    function showStatus(id, state) {
        let indicator = $(`#indicator-${id}`);
        let icon = indicator.find('i');
        indicator.removeClass('saving saved error');
        icon.attr('class', 'bi');
        if (state === 'saving') { indicator.addClass('saving'); icon.addClass('bi-arrow-repeat spin'); }
        else if (state === 'saved') { 
            indicator.addClass('saved'); icon.addClass('bi-check-circle-fill'); 
            setTimeout(() => { if(indicator.hasClass('saved')) indicator.removeClass('saved'); }, 2000);
        }
        else if (state === 'error') { indicator.addClass('error'); icon.addClass('bi-exclamation-circle-fill'); }
    }

    function updateGlobalStatus(state) {
        let pill = $('#globalSaveStatus');
        pill.removeClass('saving saved error');
        if (state === 'saving') pill.addClass('saving').html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
        else if (state === 'saved') pill.addClass('saved').html('<i class="bi bi-cloud-check-fill me-2"></i>Berhasil disimpan');
        else if (state === 'error') pill.addClass('error').html('<i class="bi bi-cloud-slash-fill me-2"></i>Gagal sinkron');
    }

    function saveRow(row, btn) {
        let w = getWeights();
        let id_siswa = row.data('id');
        let sas = row.find('.val-sas').val();
        let rapor = row.find('.val-rapor').val();
        let desc_h = row.find('.val-desc-high').val();
        let desc_l = row.find('.val-desc-low').val();

        if (btn) btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        showStatus(id_siswa, 'saving');
        updateGlobalStatus('saving');

        $.ajax({
            url: "<?= base_url('guru/nilai/saveAkhir') ?>",
            type: "POST",
            data: {
                id_siswa: id_siswa, id_mapel: ID_MAPEL, id_guru: ID_GURU,
                nilai_sas: sas, nilai_rapor: rapor,
                bobot_proses: w.proses, bobot_sts: w.sts, bobot_sas: w.sas,
                bobot_praktik: w.praktik,
                deskripsi_tinggi: desc_h, deskripsi_rendah: desc_l
            },
            success: function (resp) {
                if (btn) btn.prop('disabled', false).html('<i class="bi bi-check2-circle"></i>');
                showStatus(id_siswa, 'saved');
                updateGlobalStatus('saved');
            },
            error: function () {
                if (btn) btn.prop('disabled', false).html('<i class="bi bi-check2-circle"></i>');
                showStatus(id_siswa, 'error');
                updateGlobalStatus('error');
            }
        });
    }

    $('#btnSaveAll').on('click', function() {
        let btn = $(this);
        let w = getWeights();
        let nilaiData = [];

        $('.student-row').each(function() {
            let row = $(this);
            calculateRapor(row);
            nilaiData.push({
                id_siswa: row.data('id'),
                nilai_sas: row.find('.val-sas').val() || 0,
                nilai_rapor: row.find('.val-rapor').val() || 0,
                deskripsi_tinggi: row.find('.val-desc-high').val(),
                deskripsi_rendah: row.find('.val-desc-low').val()
            });
        });

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');
        updateGlobalStatus('saving');

        $.ajax({
            url: "<?= base_url('guru/nilai/batchSaveAkhir') ?>",
            type: "POST",
            data: { 
                nilai_data: nilaiData, id_mapel: ID_MAPEL, id_guru: ID_GURU,
                bobot_proses: w.proses, bobot_sts: w.sts, bobot_sas: w.sas,
                bobot_praktik: w.praktik
            },
            success: function(resp) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Simpan Semua');
                updateGlobalStatus(resp.status ? 'saved' : 'error');
                $('.student-row').each(function() { showStatus($(this).data('id'), resp.status ? 'saved' : 'error'); });
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Simpan Semua');
                updateGlobalStatus('error');
            }
        });
    });

    // Initialize row colors
    $('.student-row').each(function() {
        let val = parseFloat($(this).find('.val-sas').val());
        let input = $(this).find('.val-sas');
        if (val >= 90) input.addClass('grade-premium-a');
        else if (val >= 80) input.addClass('grade-premium-b');
        else if (val >= 70) input.addClass('grade-premium-c');
        else if (val > 0) input.addClass('grade-premium-d');
    });

    // --- Import CBT Logic ---
    $('#btnImportCbt').on('click', function() {
        $('#modalJadwal').modal('show');
        $('#listJadwal').html('<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm"></div> Memuat jadwal...</td></tr>');
        
        $.get("<?= base_url('guru/nilai/getCbtJadwal') ?>", {
            mapel: ID_MAPEL,
            kelas: ID_KELAS
        }, function(resp) {
            if (resp.length === 0) {
                $('#listJadwal').html('<tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada jadwal ujian untuk mapel/kelas ini.</td></tr>');
            } else {
                let html = '';
                resp.forEach(j => {
                    html += `
                        <tr class="align-middle">
                            <td class="small fw-medium">
                                ${j.bank_kode}
                                ${j.has_katrol ? '<span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px; padding: 1px 4px;">KATROL</span>' : ''}
                            </td>
                            <td class="small text-muted">${j.tgl_mulai}</td>
                            <td class="text-end">
                                <button class="btn btn-primary btn-xs px-2 btn-pilih-jadwal" 
                                    data-id="${j.id_jadwal}"
                                    data-katrol="${j.has_katrol ? 1 : 0}">
                                    Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#listJadwal').html(html);
            }
        });
    });

    $(document).on('click', '.btn-pilih-jadwal', function() {
        let idJadwal = $(this).data('id');
        let hasKatrol = $(this).data('katrol') == 1;
        let btn = $(this);
        
        let importType = $('input[name="import_type"]:checked').val();
        
        // Validation: If user wants Katrol but schedule doesn't have it
        if (importType === 'katrol' && !hasKatrol) {
            Swal.fire({
                title: "Nilai Katrol Belum Diisi",
                text: "Jadwal ini belum memiliki hasil katrol tersimpan. Nilai yang akan ditarik adalah Nilai Asli.",
                icon: "info",
                confirmButtonColor: "#4f46e5"
            });
            importType = 'asli';
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.post("<?= base_url('guru/nilai/getCbtNilai') ?>", {
            id_jadwal: idJadwal,
            id_kelas: ID_KELAS,
            import_type: importType
        }, function(resp) {
            if (resp.status) {
                let meta = resp.meta || { dari_katrol: [], tanpa_katrol: [], skipped: [] };
                let count = 0;
                Object.keys(resp.data).forEach(idSiswa => {
                    let input = $(`.sas-input-premium[data-siswa="${idSiswa}"]`);
                    if (input.length) {
                        input.val(resp.data[idSiswa]);
                        input.removeClass('km-no-katrol');
                        if (meta.tanpa_katrol.indexOf(idSiswa) !== -1) input.addClass('km-no-katrol');
                        input.trigger('input'); // Trigger colorRow and calculateRapor
                        count++;
                    }
                });
                $('#modalJadwal').modal('hide');

                let msg = count + ' nilai berhasil ditarik dari hasil ujian';
                if (meta.skipped.length) msg += ', ' + meta.skipped.length + ' siswa di luar daftar dilewati';
                let title = 'Berhasil';
                let icon = 'success';
                if (meta.tanpa_katrol.length) {
                    msg += '. ' + meta.tanpa_katrol.length + ' siswa belum punya katrol, dipakai nilai asli (input diberi garis kuning).';
                    title = 'Berhasil dengan catatan';
                    icon = 'warning';
                }
                Swal.fire(title, msg, icon);
            } else {
                Swal.fire('Gagal', resp.message || 'Terjadi kesalahan saat menarik data.', 'error');
            }
            btn.prop('disabled', false).text('Pilih');
        }).fail(function() {
            Swal.fire('Error', 'Sistem gagal menghubungi server.', 'error');
            btn.prop('disabled', false).text('Pilih');
        });
    });
});
</script>

<!-- Modal Pilih Jadwal -->
<div class="modal fade" id="modalJadwal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-collection-play me-2 text-primary"></i>Pilih Jadwal Ujian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Silakan pilih dari jadwal ujian yang tersedia untuk menarik nilai otomatis.</p>

                <div class="mb-3 bg-light p-3 rounded-3">
                    <label class="small fw-bold text-muted text-uppercase d-block mb-2">Jenis Nilai yang Ditarik</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="import_type" id="type_asli" value="asli" checked>
                        <label class="form-check-label small" for="type_asli">Nilai Asli</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="import_type" id="type_katrol" value="katrol">
                        <label class="form-check-label small" for="type_katrol">Nilai Katrol</label>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th class="small py-2 border-0">Kode/Nama Bank</th>
                                <th class="small py-2 border-0">Tanggal</th>
                                <th class="border-0"></th>
                            </tr>
                        </thead>
                        <tbody id="listJadwal">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?><style>
    /* Sticky kolom No + Siswa pada tabel rapor akhir */
    #table-nilai-akhir td:nth-child(1), #table-nilai-akhir td:nth-child(2),
    #table-nilai-akhir thead th:nth-child(1), #table-nilai-akhir thead th:nth-child(2) { position: sticky; }
    #table-nilai-akhir td:nth-child(1), #table-nilai-akhir thead th:nth-child(1) { left: 0; z-index: 4; }
    #table-nilai-akhir td:nth-child(2), #table-nilai-akhir thead th:nth-child(2) { left: 50px; z-index: 4; box-shadow: 4px 0 6px -4px rgba(0,0,0,0.08); }
    #table-nilai-akhir thead th:nth-child(1), #table-nilai-akhir thead th:nth-child(2) { z-index: 8; background: #f8f9fa; }
    #table-nilai-akhir td:nth-child(1), #table-nilai-akhir td:nth-child(2) { background: inherit; }
</style>
