<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>
<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-pencil-square text-primary me-2"></i><?= esc($judul) ?>
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
                        Input Nilai Per Tujuan Pembelajaran
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0 d-flex gap-2 justify-content-sm-end flex-wrap">
                <?php if (!empty($list_tp) && !empty($siswa)): ?>
                    <a href="<?= base_url('guru/nilai/download_proses?mapel=' . $id_mapel . '&kelas=' . $id_kelas . (isset($id_guru_selected) ? '&id_guru=' . $id_guru_selected : '')) ?>" class="btn btn-outline-success btn-sm fw-bold shadow-sm">
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

<?= $this->include('guru/nilai/partials/_kit') ?>

<!-- Main Content -->
<div class="app-content">
    <div class="container-fluid">

        <!-- Tips Guidance Card -->
        <div class="card card-default my-shadow mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-lightbulb-fill fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1 text-dark">Panduan Penilaian Sumatif Lingkup Materi</h6>
                        <div class="row g-2 small text-muted">
                            <div class="col-md-4">
                                <i class="bi bi-check2-circle text-success me-1"></i>Nilai otomatis tersimpan saat berpindah kolom
                            </div>
                            <div class="col-md-4">
                                <i class="bi bi-arrows-move text-primary me-1"></i>Navigasi: <strong>Enter</strong> bawah, <strong>&uarr;&darr;&larr;&rarr;</strong> antar cell
                            </div>
                            <div class="col-md-4">
                                <i class="bi bi-palette text-info me-1"></i>Rentang nilai: <strong>0 - 100</strong>, warna cell = kategori capaian
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-default my-shadow mb-4">
            <div class="card-body p-3">
                <?php if (empty($list_tp)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal-plus text-muted display-4 mb-3"></i>
                        <h5 class="fw-bold text-dark mb-2">Tujuan Pembelajaran Belum Ada</h5>
                        <p class="text-muted small mb-3">Silakan input TP terlebih dahulu di menu <strong>Tujuan Pembelajaran</strong> agar kolom nilai muncul.</p>
                        <a href="<?= base_url('guru/tujuan') ?>" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Tujuan Pembelajaran
                        </a>
                    </div>
                <?php elseif (empty($siswa)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted display-4 mb-3"></i>
                        <h5 class="fw-bold text-dark mb-2">Tidak Ada Siswa</h5>
                        <p class="text-muted small mb-0">Belum ada siswa yang terdaftar di kelas ini.</p>
                    </div>
                <?php else: ?>
                    <!-- Grade Legend + Filter Siswa -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="d-flex flex-wrap align-items-center gap-2 p-2 bg-light rounded border">
                            <span class="small fw-bold text-dark me-2"><i class="bi bi-palette me-1"></i>Kategori:</span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1"><span class="km-dot me-1" style="background: #00c853;"></span>Sangat Baik (≥90)</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1"><span class="km-dot me-1" style="background: #2196f3;"></span>Baik (80-89)</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1"><span class="km-dot me-1" style="background: #ff9800;"></span>Cukup (70-79)</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1"><span class="km-dot me-1" style="background: #f44336;"></span>Perlu Bimbingan (&lt;70)</span>
                        </div>
                        <div class="position-relative ms-auto">
                            <i class="bi bi-search search-icon-absolute"></i>
                            <input type="text" id="kmSearchSiswa" class="form-control km-filter-input" placeholder="Cari nama / NIS siswa..." autocomplete="off">
                        </div>
                    </div>

                    <div class="table-responsive" id="kmTableWrap">
                        <table class="table table-hover align-middle mb-0 km-input-table" id="table-nilai">
                            <thead>
                                <tr class="km-thead-row">
                                    <th rowspan="2" class="km-th-no km-sticky-no">No</th>
                                    <th rowspan="2" class="km-th-nama km-sticky-nama">Nama Siswa</th>
                                    <?php foreach ($list_tp as $tp): ?>
                                        <th class="km-th-tp" title="<?= esc($tp->tujuan) ?>" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <div class="km-tp-code"><?= esc($tp->kode ?? 'TP') ?></div>
                                            <div class="km-tp-desc"><?= esc($tp->tujuan) ?></div>
                                        </th>
                                    <?php endforeach; ?>
                                    <th rowspan="2" class="km-th-avg">Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($siswa as $s): ?>
                                    <?php
                                    // Calculate existing average
                                    $total_val = 0;
                                    $count_val = 0;
                                    foreach ($list_tp as $tp) {
                                        $v = isset($nilai[$s->id_siswa][$tp->id_tujuan]) ? floatval($nilai[$s->id_siswa][$tp->id_tujuan]) : 0;
                                        if ($v > 0) {
                                            $total_val += $v;
                                            $count_val++;
                                        }
                                    }
                                    $avg = 0;
                                    if ($count_val > 0) {
                                        $avg_raw = $total_val / $count_val;
                                        $avg = floor($avg_raw) == $avg_raw ? number_format($avg_raw, 0) : number_format($avg_raw, 1);
                                    }
                                    $predikat = \App\Models\NilaiModel::getPredikat(floatval($avg));
                                    ?>
                                    <tr class="km-data-row" data-siswa="<?= $s->id_siswa ?>" row-filter="<?= strtolower(esc($s->nama . ' ' . $s->nis . ' ' . ($s->nisn ?? ''))) ?>">
                                        <td class="text-center km-td-no km-sticky-no"><?= $no++ ?></td>
                                        <td class="km-td-nama km-sticky-nama">
                                            <div class="km-student-name"><?= esc($s->nama) ?></div>
                                            <div class="km-student-nis"><?= esc($s->nis) ?></div>
                                        </td>
                                        <?php foreach ($list_tp as $tp): ?>
                                            <?php
                                            $val = isset($nilai[$s->id_siswa][$tp->id_tujuan]) ? $nilai[$s->id_siswa][$tp->id_tujuan] : '';
                                            $valClass = '';
                                            if ($val !== '' && $val !== null) {
                                                $v = round(floatval($val));
                                                $val = $v;
                                                if ($v >= 90) $valClass = 'km-grade-a';
                                                elseif ($v >= 80) $valClass = 'km-grade-b';
                                                elseif ($v >= 70) $valClass = 'km-grade-c';
                                                else $valClass = 'km-grade-d';
                                            }
                                            ?>
                                            <td class="p-0 km-td-input">
                                                <input type="number" 
                                                    class="km-input-nilai <?= $valClass ?>"
                                                    data-siswa="<?= $s->id_siswa ?>" 
                                                    data-tp="<?= $tp->id_tujuan ?>" 
                                                    value="<?= $val ?>"
                                                    min="0" max="100" placeholder="—"
                                                    autocomplete="off">
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="text-center km-td-avg">
                                            <span class="km-avg-badge badge bg-<?= $predikat['color'] ?>-subtle text-<?= $predikat['color'] ?>">
                                                <?= $avg > 0 ? $avg : '—' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="kmNoResult" class="text-center text-muted small py-4" style="display:none;">
                            <i class="bi bi-search me-1"></i>Tidak ada siswa yang cocok dengan pencarian.
                        </div>
                    </div>

                    <!-- Notification Banner -->
                    <div id="notifBanner" class="km-notif-banner mt-3" style="display:none;">
                        <div class="km-notif-content">
                            <i class="bi bi-check-circle-fill" id="notifIcon"></i>
                            <span id="notifText">Tersimpan</span>
                        </div>
                        <button type="button" class="km-notif-close" onclick="$('#notifBanner').slideUp(200)">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <!-- Save Status Bar -->
                    <div class="km-save-status mt-2" id="saveStatus">
                        <div class="km-save-indicator" id="saveIndicator">
                            <i class="bi bi-cloud-check"></i>
                            <span>Semua tersimpan</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // (toastr default & tooltip disiapkan oleh kit bersama)

    const ID_MAPEL = <?= $id_mapel ?>;
    const ID_GURU = <?= $id_guru_selected ?? 0 ?>;

    // Color-code input based on value (kit helper)
    function colorCodeInput(input) { kmColorCode(input); }

    // Recalculate row average
    function recalcAvg(row) {
        let total = 0, count = 0;
        row.find('.km-input-nilai').each(function() {
            let v = parseFloat($(this).val());
            if (!isNaN(v) && v > 0) { total += v; count++; }
        });
        let avg_raw = count > 0 ? (total / count) : 0;
        let avg = avg_raw > 0 ? (Math.floor(avg_raw) === avg_raw ? avg_raw : avg_raw.toFixed(1)) : 0;
        let avg_num = parseFloat(avg);
        let badge = row.find('.km-avg-badge');
        badge.text(avg > 0 ? avg : '—');
        
        badge.removeClass('bg-success-subtle text-success bg-primary-subtle text-primary bg-warning-subtle text-warning bg-danger-subtle text-danger');
        if (avg_num >= 90) badge.addClass('bg-success-subtle text-success');
        else if (avg_num >= 80) badge.addClass('bg-primary-subtle text-primary');
        else if (avg_num >= 70) badge.addClass('bg-warning-subtle text-warning');
        else if (avg_num > 0) badge.addClass('bg-danger-subtle text-danger');
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

    // Single value save on change
    $('.km-input-nilai').on('change', function () {
        let input = $(this);
        let id_siswa = input.data('siswa');
        let id_tujuan = input.data('tp');

        // Batasi rentang 0-100 sebelum dikirim
        let clamped = kmClamp(input);
        input.val(clamped === '' || clamped === null || clamped === undefined ? '' : String(clamped));
        let val = input.val();

        colorCodeInput(input);
        recalcAvg(input.closest('tr'));

        // Kolom dikosongkan -> hapus nilai tersimpan
        if (val === '' || val === null || val === undefined) {
            showSaveStatus('saving', 'Menghapus...');
            $.post("/guru/nilai/delete_proses", {
                id_siswa: id_siswa,
                id_tp: id_tujuan,
                id_mapel: ID_MAPEL,
                id_guru: ID_GURU
            }, function (res) {
                if (res.status) {
                    showSaveStatus('saved', 'Nilai dihapus');
                } else {
                    showSaveStatus('error', 'Gagal menghapus');
                    toastr.error(res.message || 'Gagal menghapus nilai');
                }
            }, 'json').fail(function() {
                showSaveStatus('error', 'Koneksi terputus');
                toastr.error('Koneksi terputus. Silakan coba lagi.');
            });
            return;
        }

        showSaveStatus('saving', 'Menyimpan...');

        $.post("/guru/nilai/save_proses", {
            id_siswa: id_siswa,
            id_tp: id_tujuan,
            id_tujuan: id_tujuan,
            id_mapel: ID_MAPEL,
            nilai: val,
            id_guru: ID_GURU
        }, function (res) {
            if (res.status) {
                showSaveStatus('saved', 'Semua tersimpan');
                input.addClass('km-flash-success');
                setTimeout(function() { input.removeClass('km-flash-success'); }, 600);
            } else {
                showSaveStatus('error', 'Gagal menyimpan');
                toastr.error(res.message || 'Gagal menyimpan nilai');
            }
        }, 'json').fail(function() {
            showSaveStatus('error', 'Koneksi terputus');
            toastr.error('Koneksi terputus. Silakan coba lagi.');
        });
    });

    // Color code all inputs on page load
    $('.km-input-nilai').each(function() {
        colorCodeInput($(this));
    });

    // Pilih isi cell saat fokus agar cepat ditimpa
    $('.km-input-nilai').on('focus', function () { $(this).select(); });

    // Highlight kolom TP yang sedang aktif
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

    // Navigasi keyboard antar cell
    $('.km-input-nilai').on('keydown', function (e) {
        if (['Enter', 'ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight'].indexOf(e.key) === -1) return;
        e.preventDefault();
        let row = $(this).closest('tr');
        let idx = $(this).closest('td').index() - 2; // offset kolom No + Nama
        let target = null;
        if (e.key === 'Enter' || e.key === 'ArrowDown') {
            target = row.nextAll('tr:visible').first().find('td.km-td-input').eq(idx).find('.km-input-nilai');
        } else if (e.key === 'ArrowUp') {
            target = row.prevAll('tr:visible').first().find('td.km-td-input').eq(idx).find('.km-input-nilai');
        } else if (e.key === 'ArrowRight') {
            target = $(this).closest('td.km-td-input').nextAll('td.km-td-input').first().find('.km-input-nilai');
        } else if (e.key === 'ArrowLeft') {
            target = $(this).closest('td.km-td-input').prevAll('td.km-td-input').first().find('.km-input-nilai');
        }
        if (target && target.length) target.focus();
    });

    // Filter siswa by nama/NIS
    $('#kmSearchSiswa').on('input', function () {
        kmFilterRows('#table-nilai', $(this).val(), '#kmNoResult');
    });

    // Save all button click
    $('#btnSaveAll').on('click', function() {
        let data = [];
        $('.km-input-nilai').each(function() {
            let input = $(this);
            let val = input.val();
            if (val !== '' && val !== null) {
                data.push({
                    id_siswa: input.data('siswa'),
                    id_tp: input.data('tp'),
                    id_tujuan: input.data('tp'),
                    nilai: val
                });
            }
        });

        if (data.length === 0) {
            toastr.warning('Tidak ada data nilai untuk disimpan');
            return;
        }

        let btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
        showSaveStatus('saving', 'Menyimpan semua nilai...');

        $.ajax({
            url: "/guru/nilai/batch_save_proses",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                id_mapel: ID_MAPEL,
                id_guru: ID_GURU,
                nilai_data: data
            }),
            dataType: "json",
            success: function(res) {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Simpan Semua');
                if (res.status) {
                    showSaveStatus('saved', 'Semua tersimpan');
                    showBanner('success', res.message || 'Semua nilai berhasil disimpan!');
                    toastr.success(res.message || 'Semua nilai berhasil disimpan!');
                } else {
                    showSaveStatus('error', 'Gagal menyimpan');
                    showBanner('error', res.message || 'Gagal menyimpan nilai');
                    toastr.error(res.message || 'Gagal menyimpan nilai');
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Simpan Semua');
                showSaveStatus('error', 'Koneksi terputus');
                showBanner('error', 'Terjadi kesalahan sistem atau koneksi terputus');
                toastr.error('Terjadi kesalahan sistem atau koneksi terputus');
            }
        });
    });
});
</script>

<?= $this->endSection(); ?>
