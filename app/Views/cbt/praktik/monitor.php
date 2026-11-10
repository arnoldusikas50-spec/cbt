<?= $this->extend('layouts/main') ?>
<?php $kkmMnt = (($praktik->kktp_mode ?? 'kkm') === 'kkm' && !empty($praktik->kkm)) ? (float)$praktik->kkm : 66; ?>

<?= $this->section('styles') ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    
    .km-table-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .km-tips-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }

    .km-input-table { border-collapse: separate; border-spacing: 0; width: 100%; }
    .km-thead-row th {
        background: #f8fafc !important;
        border-bottom: 2px solid #f1f5f9 !important;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        padding: 0.75rem 0.5rem !important;
        text-align: center;
    }
    .km-data-row:hover { background: #fcfcfc; }
    .km-data-row td { border-bottom: 1px solid #f1f5f9 !important; padding: 0.75rem 0.5rem; }

    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
    
    .fade-in-up { animation: fadeInUp 0.5s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .fw-extrabold { font-weight: 800; }

    .pulse { animation: pulse-animation 2s infinite; }
    @keyframes pulse-animation { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

    .btn-refresh-minimal {
        border: 1.5px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .btn-refresh-minimal:hover { border-color: #00695c; color: #00695c; background: #f0fdfa; }

    .avatar-mini {
        width: 32px; height: 32px; border-radius: 8px;
        background: #f1f5f9; color: #475569;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.75rem;
    }

    .grid-card-siswa {
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 1rem;
        transition: all 0.2s;
        background: #fff;
    }
    .grid-card-siswa:hover { border-color: #00695c; transform: translateY(-3px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid ps-3 ps-lg-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold" style="color: #00695c;">
                    <i class="bi bi-display me-2"></i>Monitoring Ujian
                </h3>
                <p class="text-muted small mb-0">
                    <span class="badge bg-dark bg-opacity-75 me-1"><?= esc($praktik->judul) ?></span>
                    <span class="badge bg-secondary"><?= esc($praktik->nama_mapel) ?></span>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <a href="<?= site_url('cbtpraktik/observasi/'.$praktik->id) ?>" class="btn btn-outline-info shadow-sm px-3 rounded-pill btn-sm">
                    <i class="bi bi-eye me-1"></i> Observasi
                </a>
                <a href="<?= site_url('cbtpraktik/remedial/'.$praktik->id) ?>" class="btn btn-outline-danger shadow-sm px-3 rounded-pill btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Remedial
                </a>
                <a href="<?= site_url('cbtpraktik/p5/'.$praktik->id) ?>" class="btn btn-outline-primary shadow-sm px-3 rounded-pill btn-sm" style="border-color:#7c3aed; color:#7c3aed;">
                    <i class="bi bi-stars me-1"></i> P5
                </a>
                <a href="<?= site_url('cbtpraktik/analisis/'.$praktik->id) ?>" class="btn btn-outline-warning shadow-sm px-3 rounded-pill btn-sm">
                    <i class="bi bi-graph-up-arrow me-1"></i> Analisis
                </a>
                <a href="<?= site_url('cbtpraktik/nilaiManual/'.$praktik->id) ?>" class="btn btn-outline-primary shadow-sm px-3 rounded-pill btn-sm">
                    <i class="bi bi-pencil-square me-1"></i> Nilai Manual
                </a>
                <a href="<?= base_url('cbtpraktik/exportReport/' . $praktik->id . ($id_kelas_selected ? '?kelas=' . $id_kelas_selected : '')) ?>" class="btn btn-success shadow-sm px-3 rounded-pill btn-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Ekspor
                </a>
                <a href="<?= site_url('cbtpraktik') ?>" class="btn btn-outline-secondary shadow-sm px-3 rounded-pill btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid px-3 px-lg-4">
        
        <!-- Summary Stats -->
        <div class="row g-3 mb-4 fade-in-up">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.6rem; letter-spacing: 0.05em;">Total Siswa</div>
                            <h4 class="fw-extrabold mb-0 text-dark counter-siswa"><?= $statistics->total_peserta ?? 0 ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.6rem; letter-spacing: 0.05em;">Selesai</div>
                            <h4 class="fw-extrabold mb-0 text-dark counter-selesai"><?= $statistics->total_selesai ?? 0 ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-info bg-opacity-10 text-info">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.6rem; letter-spacing: 0.05em;">Rerata Nilai</div>
                            <h4 class="fw-extrabold mb-0 text-dark counter-rata"><?= number_format($statistics->rata_nilai ?? 0, 1) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 km-table-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="km-tips-icon me-3 bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted fw-bold" style="font-size: 0.6rem; letter-spacing: 0.05em;">Sedang Kerja</div>
                            <h4 class="fw-extrabold mb-0 text-dark counter-kerja"><?= $statistics->total_sedang_mengerjakan ?? 0 ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls & Filter -->
        <div class="row g-3 mb-4 align-items-center fade-in-up">
            <div class="col-md-auto">
                <div class="bg-white p-2 px-3 rounded-pill shadow-xs border d-flex gap-3 align-items-center">
                    <div class="small fw-bold"><span class="status-dot bg-success"></span><span class="d-none d-lg-inline">SELESAI</span></div>
                    <div class="small fw-bold"><span class="status-dot bg-warning"></span><span class="d-none d-lg-inline">KERJA</span></div>
                    <div class="small fw-bold"><span class="status-dot bg-secondary"></span><span class="d-none d-lg-inline">BELUM</span></div>
                </div>
            </div>
            <div class="col-md-auto">
                <select id="filterKelas" class="form-select form-select-sm rounded-pill border-0 shadow-xs px-3 fw-bold text-primary" style="width: 160px; height: 38px;">
                    <option value="">Semua Kelas</option>
                    <?php foreach($listKelas as $kls): 
                        $kls_id = is_object($kls) ? $kls->id_kelas : $kls['id_kelas'];
                        $kls_nama = is_object($kls) ? $kls->nama_kelas : $kls['nama_kelas'];
                    ?>
                        <option value="<?= $kls_id ?>" <?= $id_kelas_selected == $kls_id ? 'selected' : '' ?>><?= esc($kls_nama) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md">
                <div class="position-relative">
                    <i class="bi bi-search position-absolute" style="left: 15px; top: 10px; color: #94a3b8; font-size: 0.8rem;"></i>
                    <input type="text" id="searchSiswa" class="form-control rounded-pill border-0 shadow-xs px-5" style="height: 38px; font-size: 0.85rem;" placeholder="Cari Siswa...">
                </div>
            </div>
            <div class="col-md-auto">
                <div class="d-flex gap-2">
                    <div class="btn-group shadow-xs" role="group" style="border-radius: 10px; overflow: hidden;">
                        <button type="button" class="btn btn-light btn-sm px-3" id="viewGrid" title="Grid"><i class="bi bi-grid-fill"></i></button>
                        <button type="button" class="btn btn-light btn-sm px-3 active" id="viewTable" title="Tabel"><i class="bi bi-list-task"></i></button>
                    </div>
                    <button type="button" class="btn-refresh-minimal" onclick="refreshData()">
                        <i class="bi bi-arrow-repeat me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Monitoring Content -->
        <div class="km-table-card mb-5 fade-in-up">
            <!-- Table View -->
            <div id="monitoringTableContent">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 km-input-table" id="mainTable">
                        <thead>
                            <tr class="km-thead-row">
                                <th style="width: 50px;">No</th>
                                <th class="text-start ps-3">Siswa</th>
                                <th style="width: 120px;">Kelas</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 120px;">Mulai</th>
                                <th style="width: 100px;">Skor</th>
                                <th style="width: 100px;">Log</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($siswa as $s): ?>
                            <?php 
                                $res = $progress[$s->id_siswa] ?? null;
                                $status = $res->status_pengerjaan ?? 'belum_mulai';
                                $badgeClass = match($status) {
                                    'selesai' => 'bg-success-subtle text-success',
                                    'sedang_mengerjakan' => 'bg-warning-subtle text-warning',
                                    default => 'bg-light text-muted'
                                };
                            ?>
                            <tr class="km-data-row siswa-row" data-siswa-id="<?= $s->id_siswa ?>" data-nama="<?= strtolower($s->nama) ?>" data-nis="<?= $s->nis ?>">
                                <td class="text-center text-muted small"><?= $no++ ?></td>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-mini me-2"><?= substr($s->nama, 0, 1) ?></div>
                                        <div>
                                            <div class="fw-bold text-dark small"><?= esc($s->nama) ?></div>
                                            <div class="text-muted" style="font-size: 0.65rem;">NIS: <?= esc($s->nis) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center small"><?= esc($s->nama_kelas) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $badgeClass ?> rounded-pill px-2 py-1" style="font-size: 0.65rem; text-transform: uppercase;">
                                        <?= str_replace('_', ' ', $status) ?>
                                    </span>
                                </td>
                                <td class="text-center small text-muted">
                                    <?= !empty($res->waktu_mulai) ? date('H:i:s', strtotime($res->waktu_mulai)) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($status === 'selesai'): ?>
<?php $lulus = ($res->nilai_akhir ?? 0) >= $kkmMnt; ?>
                                        <span class="fw-bold text-<?= $lulus ? 'success' : 'danger' ?>">
                                            <?= number_format($res->nilai_akhir ?? 0, 1) ?>
                                        </span>
                                        <?php if ($res->nilai_akhir >= 86): ?><span class="badge bg-success ms-1" style="font-size:0.55rem;">M</span>
                                        <?php elseif ($res->nilai_akhir >= 66): ?><span class="badge bg-info ms-1" style="font-size:0.55rem;">C</span>
                                        <?php elseif ($res->nilai_akhir >= 41): ?><span class="badge bg-warning ms-1" style="font-size:0.55rem;">L</span>
                                        <?php elseif ($res->nilai_akhir > 0): ?><span class="badge bg-danger ms-1" style="font-size:0.55rem;">BB</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-danger border border-danger border-opacity-10 rounded-pill" style="font-size: 0.65rem;">0 x</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="<?= site_url('cbtpraktik/hasil/'.$praktik->id.'/'.$s->id_siswa) ?>" class="btn btn-light btn-sm rounded-pill px-3 py-1" style="font-size: 0.7rem;" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= site_url('cbtpraktik/narasiRapor/'.$praktik->id.'/'.$s->id_siswa) ?>" class="btn btn-outline-success btn-sm rounded-pill px-2 py-1" style="font-size: 0.6rem;" title="Rapor Naratif" target="_blank">
                                            <i class="bi bi-file-text"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Grid View (Hidden by Default) -->
            <div id="monitoringGridContent" class="d-none p-4">
                <div class="row g-3">
                    <?php foreach ($siswa as $s): ?>
                    <?php 
                        $res = $progress[$s->id_siswa] ?? null;
                        $status = $res->status_pengerjaan ?? 'belum_mulai';
                        $color = match($status) { 'selesai' => 'success', 'sedang_mengerjakan' => 'warning', default => 'secondary' };
                    ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 siswa-card" data-siswa-id="<?= $s->id_siswa ?>" data-nama="<?= strtolower($s->nama) ?>" data-nis="<?= $s->nis ?>">
                        <div class="grid-card-siswa shadow-xs">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="avatar-mini bg-<?= $color ?> bg-opacity-10 text-<?= $color ?>">
                                    <?= substr($s->nama, 0, 1) ?>
                                </div>
                                <span class="status-dot bg-<?= $color ?> <?= $status === 'sedang_mengerjakan' ? 'pulse' : '' ?>"></span>
                            </div>
                            <div class="fw-bold text-dark small text-truncate mb-1"><?= esc($s->nama) ?></div>
                            <div class="text-muted mb-2" style="font-size: 0.65rem;"><?= esc($s->nama_kelas) ?></div>
                            
                            <?php if ($status === 'selesai'): ?>
                                <?php $lulus = ($res->nilai_akhir ?? 0) >= $kkmMnt; ?>
                                <div class="text-center p-1 rounded bg-light border-0">
                                    <span class="fw-bold text-<?= $lulus ? 'success' : 'danger' ?>" style="font-size: 0.85rem;">
                                        <?= number_format($res->nilai_akhir ?? 0, 1) ?>
                                    </span>
                                    <?php if ($res->nilai_akhir >= 86): ?><span class="badge bg-success ms-1" style="font-size:0.55rem;">M</span>
                                    <?php elseif ($res->nilai_akhir >= 66): ?><span class="badge bg-info ms-1" style="font-size:0.55rem;">C</span>
                                    <?php elseif ($res->nilai_akhir >= 41): ?><span class="badge bg-warning ms-1" style="font-size:0.55rem;">L</span>
                                    <?php elseif ($res->nilai_akhir > 0): ?><span class="badge bg-danger ms-1" style="font-size:0.55rem;">BB</span>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($status === 'sedang_mengerjakan'): ?>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" style="width: 100%"></div>
                                </div>
                            <?php else: ?>
                                <div class="text-center small text-muted italic" style="font-size: 0.65rem;">Belum mulai</div>
                            <?php endif; ?>
                            <div class="mt-2 text-center">
                                <a href="<?= site_url('cbtpraktik/hasil/'.$praktik->id.'/'.$s->id_siswa) ?>" class="text-decoration-none small text-primary">Detail</a>
                                <?php if ($status === 'selesai'): ?>
                                <span class="text-muted mx-1">|</span>
                                <a href="<?= site_url('cbtpraktik/narasiRapor/'.$praktik->id.'/'.$s->id_siswa) ?>" class="text-decoration-none small text-success" target="_blank">Rapor</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cheating Alert Minimal -->
        <div class="km-table-card border-danger border-opacity-20 mb-5 p-4 fade-in-up" id="cheatingAlert" style="display: none;">
            <div class="d-flex align-items-center mb-3">
                <div class="km-tips-icon me-3 bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <h6 class="fw-extrabold text-danger mb-0">Potensi Kecurangan Terdeteksi</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm km-input-table">
                    <thead>
                        <tr class="km-thead-row">
                            <th class="text-start ps-3">Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Total</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="cheatingList"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let refreshInterval;
let MONITOR_KKM = <?= (($praktik->kktp_mode ?? 'kkm') === 'kkm' && !empty($praktik->kkm)) ? (float)$praktik->kkm : 66 ?>;

function refreshData() {
    const $btn = $('.btn-refresh-minimal');
    $btn.find('i').addClass('spinner-border spinner-border-sm border-0').removeClass('bi-arrow-repeat');
    
    const kelasId = $('#filterKelas').val();
    let url = '<?= base_url("cbtpraktik/getMonitorData") ?>?praktik_id=<?= $praktik->id ?>';
    if(kelasId) url += '&kelas=' + kelasId;
    
    $.get(url, function(res) {
        if (res.status) {
            if (res.kkm) MONITOR_KKM = res.kkm;
            updateProgressCards(res.progress);
            updateProgressTable(res.progress);
            updateCheatingAlert(res.violations);
            if (res.statistics) {
                $('.counter-siswa').text(res.statistics.total_peserta);
                $('.counter-selesai').text(res.statistics.total_selesai);
                $('.counter-rata').text(parseFloat(res.statistics.rata_nilai).toFixed(1));
                $('.counter-kerja').text(res.statistics.total_sedang_mengerjakan);
            }
        }
    }, 'json').always(() => {
        setTimeout(() => { $btn.find('i').removeClass('spinner-border spinner-border-sm border-0').addClass('bi-arrow-repeat'); }, 500);
    });
}

$('#filterKelas').change(function() {
    const kelasId = $(this).val();
    let url = '<?= site_url("cbtpraktik/monitor/" . $praktik->id) ?>';
    if(kelasId) url += '?kelas=' + kelasId;
    window.location.href = url;
});

function updateProgressCards(progress) {
    $('.siswa-card').each(function() {
        const siswaId = $(this).data('siswa-id');
        const data = progress[siswaId];
        if (data) {
            const status = data.status_pengerjaan;
            const color = status === 'selesai' ? 'success' : (status === 'sedang_mengerjakan' ? 'warning' : 'secondary');
            const $dot = $(this).find('.status-dot');
            $dot.attr('class', 'status-dot bg-' + color);
            if(status === 'sedang_mengerjakan') $dot.addClass('pulse');
            
            if (status === 'selesai' && data.nilai_akhir !== null) {
                const score = parseFloat(data.nilai_akhir);
                const isPass = score >= MONITOR_KKM;
                const predikat = score >= 86 ? 'M' : score >= 66 ? 'C' : score >= 41 ? 'L' : 'BB';
                const predikatBadge = score > 0 ? `<span class="badge bg-${isPass ? 'success' : 'danger'} ms-1" style="font-size:0.55rem;">${predikat}</span>` : '';
                const scoreHtml = `<div class="text-center p-1 rounded bg-light border-0"><span class="fw-bold text-${isPass ? 'success' : 'danger'}" style="font-size: 0.85rem;">${score.toFixed(1)}</span>${predikatBadge}</div>`;
                $(this).find('.progress, .text-center').replaceWith(scoreHtml);
            }
        }
    });
}

function updateProgressTable(progress) {
    $('.siswa-row').each(function() {
        const siswaId = $(this).data('siswa-id');
        const data = progress[siswaId];
        if (data) {
            const status = data.status_pengerjaan;
            const badgeClass = status === 'selesai' ? 'bg-success-subtle text-success' : (status === 'sedang_mengerjakan' ? 'bg-warning-subtle text-warning' : 'bg-light text-muted');
            $(this).find('.badge').first().attr('class', 'badge rounded-pill px-2 py-1 ' + badgeClass).text(status.replace('_', ' '));
            if (data.waktu_mulai) $(this).find('td:nth-child(5)').text(data.waktu_mulai.split(' ')[1]);
            if (status === 'selesai' && data.nilai_akhir !== null) {
                const score = parseFloat(data.nilai_akhir);
                const isPass = score >= MONITOR_KKM;
                const predikat = score >= 86 ? 'M' : score >= 66 ? 'C' : score >= 41 ? 'L' : 'BB';
                const predikatBadge = score > 0 ? `<span class="badge bg-${isPass ? 'success' : 'danger'} ms-1" style="font-size:0.55rem;">${predikat}</span>` : '';
                $(this).find('td:nth-child(6)').html(`<span class="fw-bold text-${isPass ? 'success' : 'danger'}">${score.toFixed(1)}</span>${predikatBadge}`);
            }
        }
    });
}

function updateCheatingAlert(violations) {
    const alertBox = $('#cheatingAlert');
    if (violations && violations.length > 0) {
        alertBox.show();
        let html = '';
        violations.forEach(v => {
            html += `<tr class="km-data-row"><td class="ps-3 fw-bold small">${v.nama_siswa}</td><td class="text-center small">${v.nama_kelas}</td><td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10" style="font-size:0.6rem;">${v.jenis_pelanggaran.replace(/_/g, ' ')}</span></td><td class="text-center fw-bold small">${v.total_pelanggaran}</td><td class="text-center text-muted small">${v.waktu_akhir}</td></tr>`;
            $(`.siswa-row[data-siswa-id="${v.siswa_id}"] td:nth-child(7) span`).text(v.total_pelanggaran + ' x');
        });
        $('#cheatingList').html(html);
    } else { alertBox.hide(); }
}

$('#searchSiswa').on('keyup', function() {
    const val = $(this).val().toLowerCase();
    $('.siswa-card, .siswa-row').each(function() {
        const name = $(this).data('nama');
        const nis = $(this).data('nis');
        if (name.includes(val) || nis.toString().includes(val)) $(this).show(); else $(this).hide();
    });
});

$('#viewGrid').on('click', function() { $(this).addClass('active'); $('#viewTable').removeClass('active'); $('#monitoringGridContent').removeClass('d-none'); $('#monitoringTableContent').addClass('d-none'); });
$('#viewTable').on('click', function() { $(this).addClass('active'); $('#viewGrid').removeClass('active'); $('#monitoringGridContent').addClass('d-none'); $('#monitoringTableContent').removeClass('d-none'); });

$(document).ready(function() {
    refreshData();
    refreshInterval = setInterval(refreshData, 15000);
});

$(window).on('beforeunload', function() { clearInterval(refreshInterval); });
</script>
<?= $this->endSection() ?>
