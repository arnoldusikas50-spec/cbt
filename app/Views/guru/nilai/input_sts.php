<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Content Header -->
<?= $this->include('guru/nilai/partials/_kit') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
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
                        Input Nilai Sumatif Tengah Semester
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <?php if ($id_mapel && $id_kelas && !empty($siswa)): ?>
                    <button class="btn btn-warning btn-sm shadow-sm fw-bold text-white" id="btnImportCbt">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Ambil Nilai Ujian
                    </button>
                    <a href="<?= base_url('guru/nilai/download_sts?mapel=' . $id_mapel . '&kelas=' . $id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>" class="btn btn-outline-success btn-sm fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
                    </a>
                    <button class="btn btn-success btn-sm fw-bold shadow-sm btn-save-all" id="btnSaveAll">
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

<!-- Main Content -->
<div class="app-content">
    <div class="container-fluid px-3 px-lg-4">

        <!-- Filter Card -->
        <div class="km-table-card mb-4 p-3">
            <form method="get" action="<?= base_url('guru/nilai/sts') ?>" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-muted small mb-1">Mata Pelajaran</label>
                        <select name="mapel" class="form-select km-select" onchange="$('#filterForm').submit()">
                            <option value="">Pilih Mata Pelajaran...</option>
                            <?php foreach ($mapel_kelas as $mk): ?>
                                <option value="<?= $mk->id_mapel ?>" <?= $id_mapel == $mk->id_mapel ? 'selected' : '' ?>>
                                    <?= $mk->nama_mapel ?> (<?= $mk->kode_mapel ?? '-' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-muted small mb-1">Kelas & Tingkat</label>
                        <select name="kelas" class="form-select km-select" onchange="$('#filterForm').submit()">
                            <option value="">Pilih Kelas...</option>
                            <?php foreach ($mapel_kelas as $mk): ?>
                                <?php if ($mk->id_mapel == $id_mapel): ?>
                                    <?php foreach ($mk->kelas as $k): ?>
                                        <option value="<?= $k->id_kelas ?>" <?= $id_kelas == $k->id_kelas ? 'selected' : '' ?>>
                                            <?= $k->nama_kelas ?> (Tingkat <?= $k->tingkat ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="km-btn km-btn-primary w-100 py-2">
                            <i class="bi bi-funnel-fill me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($id_mapel && $id_kelas): ?>
            <!-- Tips Card -->
            <div class="km-tips-card mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="km-tips-icon">
                        <i class="bi bi-lightbulb-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1 text-primary">Panduan Penilaian Sumatif Tengah Semester</h6>
                        <div class="row g-2 small text-muted">
                            <div class="col-md-4">
                                <i class="bi bi-check2 text-success me-1"></i>Nilai otomatis tersimpan saat menekan <b>Enter</b>
                            </div>
                            <div class="col-md-4">
                                <i class="bi bi-lightning text-success me-1"></i>Gunakan tombol <b>Ambil Nilai Ujian</b> untuk sinkronisasi dari CBT
                            </div>
                            <div class="col-md-4">
                                <i class="bi bi-palette text-success me-1"></i>Rentang nilai: <b>0 - 100</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="km-table-card">
                        <?php if (empty($siswa)): ?>
                            <div class="km-empty-state">
                                <div class="km-empty-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h5 class="fw-bold mt-3">Tidak Ada Siswa</h5>
                                <p class="text-muted">Belum ada siswa yang terdaftar di kelas ini.</p>
                            </div>
                        <?php else: ?>
                            <!-- Grade Legend + Filter Siswa -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div class="km-legend mb-0">
                                <span class="km-legend-title"><i class="bi bi-palette me-1"></i>Kategori:</span>
                                <span class="km-legend-item"><span class="km-dot" style="background: #00c853;"></span>Sangat Baik (≥90)</span>
                                <span class="km-legend-item"><span class="km-dot" style="background: #2196f3;"></span>Baik (80-89)</span>
                                <span class="km-legend-item"><span class="km-dot" style="background: #ff9800;"></span>Cukup (70-79)</span>
                                <span class="km-legend-item"><span class="km-dot" style="background: #f44336;"></span>Perlu Bimbingan (&lt;70)</span>
                                </div>
                                <div class="position-relative ms-auto">
                                    <i class="bi bi-search search-icon-absolute"></i>
                                    <input type="text" id="kmSearchSiswa" class="form-control km-filter-input" placeholder="Cari nama / NIS siswa..." autocomplete="off">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 km-input-table" id="table-nilai">
                                    <thead>
                                        <tr class="km-thead-row">
                                            <th class="km-th-no km-sticky-no">No</th>
                                            <th class="km-th-nama km-sticky-nama">Informasi Siswa</th>
                                            <th class="km-th-avg" style="width: 150px;">Nilai STS</th>
                                            <th class="km-th-avg" style="width: 100px;">Predikat</th>
                                            <th class="km-th-no" style="width: 80px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($siswa as $s): ?>
                                            <?php
                                            $val = isset($nilai[$s->id_siswa]) ? $nilai[$s->id_siswa] : '';
                                            $valClass = '';
                                            $predikat = ['huruf' => '-', 'color' => 'secondary'];
                                            
                                            if ($val !== '' && $val !== null) {
                                                $v = floatval($val);
                                                $predikat = \App\Models\NilaiModel::getPredikat($v);
                                                if ($v >= 90) $valClass = 'km-grade-a';
                                                elseif ($v >= 80) $valClass = 'km-grade-b';
                                                elseif ($v >= 70) $valClass = 'km-grade-c';
                                                else $valClass = 'km-grade-d';
                                            }
                                            ?>
                                            <tr class="km-data-row" data-siswa="<?= $s->id_siswa ?>" row-filter="<?= strtolower(esc($s->nama . ' ' . ($s->nisn ?? ''))) ?>">
                                                <td class="text-center km-td-no km-sticky-no"><?= $no++ ?></td>
                                                <td class="km-td-nama km-sticky-nama">
                                                    <div class="d-flex align-items-center">
                                                        <div class="km-avatar me-2"><?= substr($s->nama, 0, 1) ?></div>
                                                        <div>
                                                            <div class="km-student-name"><?= $s->nama ?></div>
                                                            <div class="km-student-nis">NISN: <?= $s->nisn ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-0 km-td-input">
                                                    <input type="number" 
                                                        class="km-input-nilai <?= $valClass ?>"
                                                        data-siswa="<?= $s->id_siswa ?>" 
                                                        value="<?= $val ?>"
                                                        min="0" max="100" placeholder="—"
                                                        autocomplete="off">
                                                </td>
                                                <td class="text-center km-td-avg">
                                                    <span class="km-avg-badge badge bg-<?= $predikat['color'] ?>-subtle text-<?= $predikat['color'] ?> predikat-badge">
                                                        <?= $predikat['huruf'] ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="save-indicator-small" id="indicator-<?= $s->id_siswa ?>">
                                                        <i class="bi bi-check-circle-fill opacity-0"></i>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="kmNoResult" class="text-center text-muted small py-4" style="display:none;">
                                <i class="bi bi-search me-1"></i>Tidak ada siswa yang cocok dengan pencarian.
                            </div>

                            <!-- Notification Banner -->
                            <div id="notifBanner" class="km-notif-banner" style="display:none;">
                                <div class="km-notif-content">
                                    <i class="bi bi-check-circle-fill" id="notifIcon"></i>
                                    <span id="notifText">Tersimpan</span>
                                </div>
                                <button type="button" class="km-notif-close" onclick="$('#notifBanner').slideUp(200)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>

                            <!-- Save Status Bar -->
                            <div class="km-save-status" id="saveStatus">
                                <div class="km-save-indicator" id="saveIndicator">
                                    <i class="bi bi-cloud-check"></i>
                                    <span>Sinkronisasi aktif</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Blank State -->
            <div class="km-empty-state py-5">
                <div class="km-empty-icon" style="font-size: 4rem;">
                    <i class="bi bi-funnel"></i>
                </div>
                <h5 class="fw-bold mt-3">Pilih Filter Terlebih Dahulu</h5>
                <p class="text-muted">Gunakan panel filter di atas untuk memilih mata pelajaran dan kelas.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Pilih Jadwal -->
<div class="modal fade" id="modalJadwal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Pilih Jadwal Ujian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Tarik nilai otomatis dari hasil ujian CBT yang sudah selesai.</p>
                
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
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="small py-2 border-0">Bank Soal</th>
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

<script>
$(document).ready(function () {
    // (toastr default & tooltip disiapkan oleh kit bersama)

    const ID_MAPEL = <?= $id_mapel ?: 0 ?>;
    const ID_GURU = <?= $id_guru_selected ?? 0 ?>;

    // Color-code input based on value (kit helper)
    function colorCodeInput(input) { kmColorCode(input); }

    // Update Predikat Badge
    function updatePredikat(row, val) {
        let badge = row.find('.predikat-badge');
        let huruf = '-', color = 'secondary';
        
        if (val !== '' && !isNaN(val)) {
            let v = parseFloat(val);
            if (v >= 90) { huruf = 'A'; color = 'success'; }
            else if (v >= 80) { huruf = 'B'; color = 'primary'; }
            else if (v >= 70) { huruf = 'C'; color = 'warning'; }
            else { huruf = 'D'; color = 'danger'; }
        }
        
        badge.text(huruf);
        badge.removeClass('bg-success-subtle text-success bg-primary-subtle text-primary bg-warning-subtle text-warning bg-danger-subtle text-danger bg-secondary-subtle text-secondary');
        badge.addClass(`bg-${color}-subtle text-${color}`);
    }

    // Update save status indicator
    function showSaveStatus(type, msg) {
        let indicator = $('#saveIndicator');
        indicator.removeClass('saving saved error');
        if (type === 'saving') {
            indicator.addClass('saving').html('<span class="spinner-border spinner-border-sm me-1"></span><span>' + msg + '</span>');
        } else if (type === 'saved') {
            indicator.addClass('saved').html('<i class="bi bi-cloud-check me-1"></i><span>' + msg + '</span>');
        } else if (type === 'error') {
            indicator.addClass('error').html('<i class="bi bi-exclamation-triangle me-1"></i><span>' + msg + '</span>');
        }
    }

    // Show prominent banner notification
    function showBanner(type, msg, autoHide = true) {
        let banner = $('#notifBanner');
        let icon = $('#notifIcon');
        let text = $('#notifText');

        banner.removeClass('error info');
        if (type === 'success') {
            icon.attr('class', 'bi bi-check-circle-fill');
            banner.css('background', 'linear-gradient(135deg, #00c853, #00a344)');
        } else if (type === 'error') {
            banner.addClass('error');
            icon.attr('class', 'bi bi-x-circle-fill');
            banner.css('background', 'linear-gradient(135deg, #ff5252, #d32f2f)');
        } else if (type === 'info') {
            banner.addClass('info');
            icon.attr('class', 'bi bi-info-circle-fill');
            banner.css('background', 'linear-gradient(135deg, #448aff, #2962ff)');
        }

        text.text(msg);
        banner.stop().slideDown(300);

        if (autoHide) {
            setTimeout(function() {
                banner.slideUp(200);
            }, 4000);
        }
    }

    // Row indicator
    function showRowIndicator(id, state) {
        let indicator = $(`#indicator-${id}`);
        let icon = indicator.find('i');
        indicator.removeClass('saving saved error');
        icon.attr('class', 'bi');

        if (state === 'saving') {
            indicator.addClass('saving');
            icon.addClass('bi-arrow-repeat spin');
        } else if (state === 'saved') {
            indicator.addClass('saved');
            icon.addClass('bi-check-circle-fill');
            setTimeout(() => icon.addClass('opacity-0'), 2000);
        } else if (state === 'error') {
            indicator.addClass('error');
            icon.addClass('bi-exclamation-circle-fill');
        }
        icon.removeClass('opacity-0');
    }

    // Single value save on change
    $('.km-input-nilai').on('change', function () {
        let input = $(this);
        let rawVal = input.val();
        let val = parseFloat(rawVal);
        let id_siswa = input.data('siswa');
        let row = input.closest('tr');

        // Handle empty or invalid input - DELETE the value
        if (isNaN(val) || rawVal === '' || rawVal === null) {
            input.val('');
            colorCodeInput(input);
            updatePredikat(row, '');

            // Delete the value from database
            showSaveStatus('saving', 'Menghapus...');
            showRowIndicator(id_siswa, 'saving');
            $.ajax({
                url: "<?= base_url('guru/nilai/delete_sts') ?>",
                type: "POST",
                data: {
                    id_siswa: id_siswa,
                    id_mapel: ID_MAPEL
                },
                success: function (resp) {
                    if (resp.status) {
                        showSaveStatus('saved', 'Terhapus');
                        showRowIndicator(id_siswa, 'saved');
                        showBanner('info', 'Nilai STS dihapus');
                    } else {
                        showSaveStatus('error', 'Gagal hapus');
                        showRowIndicator(id_siswa, 'error');
                        showBanner('error', resp.message || 'Gagal menghapus nilai');
                    }
                },
                error: function () {
                    showSaveStatus('error', 'Gagal hapus');
                    showRowIndicator(id_siswa, 'error');
                    showBanner('error', 'Gagal terhubung ke server');
                }
            });
            return;
        }

        // Batasi rentang 0-100 sebelum dikirim
        let clamped = kmClamp(input);
        if (clamped !== '' && clamped !== null && clamped !== undefined) input.val(String(clamped));

        colorCodeInput(input);
        updatePredikat(row, input.val());
        showSaveStatus('saving', 'Menyimpan...');
        showRowIndicator(id_siswa, 'saving');

        $.ajax({
            url: "<?= base_url('guru/nilai/saveSts') ?>",
            type: "POST",
            data: {
                id_siswa: id_siswa,
                id_mapel: ID_MAPEL,
                id_guru: ID_GURU,
                nilai: input.val()
            },
            success: function (resp) {
                if (resp.status) {
                    showSaveStatus('saved', 'Tersimpan');
                    showBanner('success', 'Nilai STS berhasil disimpan');
                    showRowIndicator(id_siswa, 'saved');
                    input.addClass('km-flash-success');
                    setTimeout(() => input.removeClass('km-flash-success'), 600);
                } else {
                    showSaveStatus('error', 'Gagal');
                    showBanner('error', resp.message || 'Gagal menyimpan nilai');
                    showRowIndicator(id_siswa, 'error');
                    toastr.error(resp.message || 'Gagal menyimpan nilai');
                }
            },
            error: function (xhr, status, error) {
                showSaveStatus('error', 'Gagal');
                showBanner('error', 'Gagal terhubung ke server');
                showRowIndicator(id_siswa, 'error');
                toastr.error('Gagal menyimpan nilai: ' + error);
            }
        });
    });

    // Apply color on page load
    $('.km-input-nilai').each(function() { colorCodeInput($(this)); });

    // Pilih isi cell saat fokus agar cepat ditimpa
    $('.km-input-nilai').on('focus', function () { $(this).select(); });

    // Highlight kolom nilai yang sedang aktif
    function kmHighlightCol(td, on) {
        let t = $('#table-nilai');
        t.find('.km-col-active').removeClass('km-col-active');
        if (!on || !td) return;
        let idx = td.index();
        t.find('tbody td:nth-child(' + (idx + 1) + ')').addClass('km-col-active');
        t.find('thead > tr').children('th').eq(idx).addClass('km-col-active');
    }
    $('#table-nilai').on('focusin', '.km-input-nilai', function () { kmHighlightCol($(this).closest('td'), true); });
    $('#table-nilai').on('focusout', '.km-input-nilai', function () { kmHighlightCol(null, false); });

    // Navigasi keyboard antar baris (Enter / panah bawah-atas)
    $('.km-input-nilai').on('keydown', function (e) {
        if (['Enter', 'ArrowDown', 'ArrowUp'].indexOf(e.key) === -1) return;
        e.preventDefault();
        let row = $(this).closest('tr');
        let idx = $(this).closest('td').index();
        let targetRow = ['Enter', 'ArrowDown'].indexOf(e.key) !== -1
            ? row.nextAll('tr:visible').first()
            : row.prevAll('tr:visible').first();
        if (targetRow.length) targetRow.children('td').eq(idx).find('.km-input-nilai').focus();
    });

    // Filter siswa by nama/NIS
    $('#kmSearchSiswa').on('input', function () {
        kmFilterRows('#table-nilai', $(this).val(), '#kmNoResult');
    });

    // BATCH SAVE ALL
    $('#btnSaveAll').on('click', function() {
        let btn = $(this);
        let nilaiData = [];

        $('.km-input-nilai').each(function() {
            let v = $(this).val();
            if (v !== '' && v !== null) {
                nilaiData.push({ id_siswa: $(this).data('siswa'), nilai: v });
            }
        });

        if (nilaiData.length === 0) {
            toastr.info('Tidak ada nilai untuk disimpan');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
        showSaveStatus('saving', 'Menyimpan semua data...');

        $.ajax({
            url: "<?= base_url('guru/nilai/batchSaveSts') ?>",
            type: "POST",
            data: { nilai_data: nilaiData, id_mapel: ID_MAPEL, id_guru: ID_GURU },
            success: function(resp) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Simpan Semua');
                if (resp.status) {
                    showBanner('success', `${resp.result.saved} nilai STS berhasil disimpan`);
                    toastr.success(`${resp.result.saved} nilai berhasil disimpan`);
                    showSaveStatus('saved', `${resp.result.saved} nilai tersimpan`);
                    $('.km-input-nilai').each(function() { showRowIndicator($(this).data('siswa'), 'saved'); });
                } else {
                    showBanner('error', resp.message || 'Gagal menyimpan');
                    toastr.error(resp.message || 'Gagal menyimpan');
                    showSaveStatus('error', 'Gagal menyimpan');
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Simpan Semua');
                showBanner('error', 'Terjadi kesalahan server: ' + error);
                toastr.error('Terjadi kesalahan server');
                showSaveStatus('error', 'Error server');
            }
        });
    });

    // --- Import CBT Logic ---
    $('#btnImportCbt').on('click', function() {
        $('#modalJadwal').modal('show');
        $('#listJadwal').html('<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm"></div> Memuat jadwal...</td></tr>');
        
        $.get("<?= base_url('guru/nilai/getCbtJadwal') ?>", {
            mapel: "<?= $id_mapel ?>",
            kelas: "<?= $id_kelas ?>"
        }, function(resp) {
            if (resp.length === 0) {
                $('#listJadwal').html('<tr><td colspan="3" class="text-center py-4 text-muted small">Tidak ada jadwal ujian untuk mapel/kelas ini.</td></tr>');
            } else {
                let html = '';
                resp.forEach(j => {
                    html += `
                        <tr class="align-middle">
                            <td class="small fw-bold text-dark">
                                ${j.bank_kode}
                                ${j.has_katrol ? '<span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px; padding: 1px 4px;">KATROL</span>' : ''}
                            </td>
                            <td class="small text-muted" style="font-size: 0.75rem;">${j.tgl_mulai}</td>
                            <td class="text-end">
                                <button class="btn btn-primary btn-sm rounded-pill px-3 py-1 btn-pilih-jadwal" 
                                    data-id="${j.id_jadwal}" 
                                    data-katrol="${j.has_katrol ? 1 : 0}"
                                    style="font-size: 0.7rem;">
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
            id_kelas: "<?= $id_kelas ?>",
            import_type: importType
        }, function(resp) {
            if (resp.status) {
                let meta = resp.meta || { dari_katrol: [], tanpa_katrol: [], skipped: [] };
                let count = 0;
                Object.keys(resp.data).forEach(idSiswa => {
                    let input = $(`.km-input-nilai[data-siswa="${idSiswa}"]`);
                    if (input.length) {
                        input.val(resp.data[idSiswa]);
                        input.removeClass('km-no-katrol');
                        if (meta.tanpa_katrol.indexOf(idSiswa) !== -1) input.addClass('km-no-katrol');
                        colorCodeInput(input);
                        updatePredikat(input.closest('tr'), resp.data[idSiswa]);
                        count++;
                    }
                });
                $('#modalJadwal').modal('hide');

                let msg = count + ' nilai berhasil ditarik';
                if (meta.skipped.length) msg += ', ' + meta.skipped.length + ' siswa di luar daftar dilewati';
                let title = 'Berhasil';
                let icon = 'success';
                if (meta.tanpa_katrol.length) {
                    msg += '. ' + meta.tanpa_katrol.length + ' siswa belum punya katrol, dipakai nilai asli (kolom diberi garis kuning).';
                    title = 'Berhasil dengan catatan';
                    icon = 'warning';
                }
                Swal.fire(title, msg, icon);
            } else {
                Swal.fire('Gagal', resp.message || 'Gagal menarik data.', 'error');
            }
            btn.prop('disabled', false).text('Pilih');
        });
    });
});
</script>

<?= $this->endSection(); ?>
