<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    /* ── Print-specific styling ── */
    @media print {
        .no-print, .navbar, .sidebar, .btn-no-print, .dataTables_wrapper, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .dt-buttons {
            display: none !important;
        }
        body {
            background: #ffffff !important;
            font-size: 11px;
        }
        .app-wrapper, .app-main, .content-wrapper, .main-header, .main-sidebar {
            display: block !important;
            position: static !important;
            overflow: visible !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 0 !important;
        }
        .card-header {
            background: #fff !important;
            color: #000 !important;
            border-bottom: 2px solid #333 !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            font-size: 10px !important;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 3px 4px !important;
            background: #fff !important;
        }
        .text-primary, .text-success, .fw-bold {
            color: #000 !important;
        }
        .table-dark {
            background: #eee !important;
        }
        .table-dark th {
            color: #000 !important;
            background: #eee !important;
        }
        .print-title {
            text-align: center;
            margin-bottom: 1rem;
        }
        .print-title h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .print-title p {
            margin: 2px 0;
            font-size: 11px;
        }
        .print-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .print-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    }

    /* ── Screen styling ── */
    .filter-card {
        border-radius: 8px;
    }
    .leger-header {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .leger-logo {
        width: 48px;
        height: 48px;
        object-fit: contain;
        border-radius: 4px;
    }
    .text-wrap-th {
        white-space: normal !important;
    }
    .nilai-cell {
        text-align: center;
        font-weight: 600;
    }
    .nilai-empty {
        color: #adb5bd;
    }
    .btn-print-action {
        background: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-print-action:hover {
        background: #0b5ed7;
        border-color: #0a58ca;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$setting   = $setting ?? null;
$namaSekolah = !empty($setting) && !empty($setting->nama_sekolah) ? $setting->nama_sekolah : (isset($setting->sekolah) ? $setting->sekolah : 'Sekolah');
$alamatSekolah = !empty($setting) && !empty($setting->alamat) ? $setting->alamat : '';
$telpSekolah   = !empty($setting) && !empty($setting->telp) ? $setting->telp : '';
$logoSekolah   = !empty($setting) && !empty($setting->logo_kiri) ? base_url($setting->logo_kiri) : base_url('assets/img/favicon.png');
$tahunLabel  = $tp_active ? $tp_active->tahun : '-';
$smtLabel    = $smt_active ? $smt_active->smt : '-';
$kelasLabel  = $kelas ? $kelas->nama_kelas : '-';
$idTpSel  = isset($id_tp_sel)  ? (int)$id_tp_sel  : 0;
$idSmtSel = isset($id_smt_sel) ? (int)$id_smt_sel : 0;
$idKelasSel = isset($id_kelas_sel) ? (int)$id_kelas_sel : 0;
$idMapelSel = isset($id_mapel_sel) ? (int)$id_mapel_sel : 0;
?>

<div class="card card-outline card-primary shadow-sm no-print">
    <div class="card-header bg-primary text-white">
        <div class="leger-header">
            <?php if (!empty($logoSekolah)): ?>
                <img src="<?= esc($logoSekolah) ?>" alt="Logo" class="leger-logo">
            <?php endif; ?>
            <div>
                <h4 class="card-title mb-0">
                    <i class="bi bi-journal-text me-2"></i> Ledger Nilai Akademik Kelas
                </h4>
                <small class="text-light opacity-80"><?= esc($kelasLabel) ?> &middot; <?= esc($tahunLabel) ?> Semester <?= esc($smtLabel) ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">

        <!-- ── Filter Form ── -->
        <div class="filter-card card card-sm mb-3 p-3" style="border:1px solid #dee2e6; background:#f8f9fa;">
            <form method="get" action="<?= base_url('guru/nilai/cetakleger') ?>" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold text-sm">Tahun Pelajaran</label>
                    <select name="id_tp" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($tp as $t): ?>
                            <option value="<?= (int)$t->id_tp ?>" <?= $idTpSel == $t->id_tp ? 'selected' : '' ?>>
                                <?= esc($t->tahun ?? $t->tahun_pelajaran ?? '-') ?> <?= isset($t->active) && $t->active ? '(Aktif)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-sm">Semester</label>
                    <select name="id_smt" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($smt as $s): ?>
                            <option value="<?= (int)$s->id_smt ?>" <?= $idSmtSel == $s->id_smt ? 'selected' : '' ?>>
                                <?= esc($s->smt ?? $s->nama_smt ?? '-') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-sm">Kelas</label>
                    <select name="id_kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua / Pilih Kelas --</option>
                        <?php foreach ($allKelas as $k): ?>
                            <option value="<?= (int)$k->id_kelas ?>" <?= $idKelasSel == $k->id_kelas ? 'selected' : '' ?>>
                                <?= esc($k->nama_kelas) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-sm">Mata Pelajaran</label>
                    <select name="id_mapel" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Mapel --</option>
                        <?php
                        $allMapelsForFilter = $mapels ?? [];
                        foreach ($allMapelsForFilter as $m): ?>
                            <option value="<?= (int)$m->id_mapel ?>" <?= $idMapelSel == $m->id_mapel ? 'selected' : '' ?>>
                                <?= esc($m->kode ?? substr($m->nama_mapel, 0, 5)) ?> - <?= esc($m->nama_mapel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <a href="<?= base_url('guru/nilai/cetakleger') . '?id_tp=' . $idTpSel . '&id_smt=' . $idSmtSel . '&id_kelas=' . $idKelasSel ?>" class="btn btn-outline-secondary btn-sm w-auto">
                        <i class="bi bi-print me-1"></i> Cetak
                    </a>
                    <button type="button" class="btn btn-success btn-sm w-auto btn-print-action" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </form>
        </div>

        <!-- ── Table Container ── -->
        <div class="table-responsive">
            <?php if (empty($siswas)): ?>
                <div class="no-data py-5">
                    <i class="bi bi-info-circle fs-1 opacity-25"></i>
                    <p class="mt-3 fw-bold">Tidak ada data siswa</p>
                    <p class="text-sm opacity-70">Belum ada siswa terdaftar di kelas ini untuk tahun <?= esc($tahunLabel) ?> semester <?= esc($smtLabel) ?>.</p>
                </div>
            <?php elseif (empty($mapels)): ?>
                <div class="no-data py-5">
                    <i class="bi bi-book fs-1 opacity-25"></i>
                    <p class="mt-3 fw-bold">Tidak ada data mata pelajaran</p>
                    <p class="text-sm opacity-70">Belum ada mata pelajaran yang tersedia untuk ditampilkan.</p>
                </div>
            <?php else: ?>
                <table id="legerTable" class="table table-bordered table-striped table-hover table-sm text-sm" style="width:100%; white-space: nowrap;">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="align-middle text-center text-wrap-th" style="width: 40px;">No</th>
                            <th rowspan="2" class="align-middle text-center text-wrap-th">NIS / NISN</th>
                            <th rowspan="2" class="align-middle text-center text-wrap-th">Nama Siswa</th>
                            <th rowspan="2" class="align-middle text-center text-wrap-th" style="width: 50px;">L/P</th>
                            <?php if (!empty($kelompoks)): ?>
                                <?php foreach ($kelompoks as $kel): ?>
                                    <?php
                                        $mapelKel = [];
                                        foreach ($mapels as $m) {
                                            if ($m->kelompok == $kel->kode_kel_mapel) $mapelKel[] = $m;
                                        }
                                        if (count($mapelKel) > 0):
                                    ?>
                                    <th colspan="<?= count($mapelKel) ?>" class="text-center"><?= esc($kel->nama_kel_mapel) ?></th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <th colspan="<?= count($mapels) ?>" class="text-center">Mata Pelajaran</th>
                            <?php endif; ?>
                            <th rowspan="2" class="align-middle text-center text-wrap-th" style="width: 70px;">Jml Nilai</th>
                            <th rowspan="2" class="align-middle text-center text-wrap-th" style="width: 70px;">Rata-Rata</th>
                            <th rowspan="2" class="align-middle text-center text-wrap-th" style="width: 60px;">Peringkat</th>
                        </tr>
                        <tr>
                            <?php if (!empty($kelompoks)): ?>
                                <?php foreach ($kelompoks as $kel): ?>
                                    <?php foreach ($mapels as $m): ?>
                                        <?php if ($m->kelompok == $kel->kode_kel_mapel): ?>
                                            <th class="text-center text-wrap-th" title="<?= esc($m->nama_mapel) ?>">
                                                <?= esc($m->kode ?? substr($m->nama_mapel, 0, 5)) ?>
                                            </th>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($mapels as $m): ?>
                                    <th class="text-center text-wrap-th" title="<?= esc($m->nama_mapel) ?>">
                                        <?= esc($m->kode ?? substr($m->nama_mapel, 0, 5)) ?>
                                    </th>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    foreach ($siswas as $siswa):
                        $stats = $siswaStats[$siswa->id_siswa] ?? ['jml'=>0,'cnt'=>0,'avg'=>0,'nilai'=>[]];
                        $mapelNilai = $stats['nilai'] ?? [];
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= esc($siswa->nis) ?> <?= !empty($siswa->nisn) ? '<br><small>'.esc($siswa->nisn).'</small>' : '' ?></td>
                        <td><?= esc($siswa->nama) ?></td>
                        <td class="text-center"><?= esc($siswa->jenis_kelamin ?? '-') ?></td>

                        <?php if (!empty($kelompoks)): ?>
                            <?php foreach ($kelompoks as $kel): ?>
                                <?php foreach ($mapels as $m): ?>
                                    <?php if ($m->kelompok == $kel->kode_kel_mapel): ?>
                                        <?php
                                            $val = isset($mapelNilai[$m->id_mapel]) ? $mapelNilai[$m->id_mapel] : null;
                                            $isEmpty = ($val === null || $val === '');
                                        ?>
                                        <td class="text-center <?= $isEmpty ? 'nilai-empty' : 'nilai-cell' ?>">
                                            <?= $isEmpty ? '-' : number_format((float)$val, 0, ',', '.') ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($mapels as $m): ?>
                                <?php
                                    $val = isset($mapelNilai[$m->id_mapel]) ? $mapelNilai[$m->id_mapel] : null;
                                    $isEmpty = ($val === null || $val === '');
                                ?>
                                <td class="text-center <?= $isEmpty ? 'nilai-empty' : 'nilai-cell' ?>">
                                    <?= $isEmpty ? '-' : number_format((float)$val, 0, ',', '.') ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <td class="text-center fw-bold"><?= number_format((float)$stats['jml'], 0, ',', '.') ?></td>
                        <td class="text-center fw-bold text-primary"><?= $stats['cnt'] > 0 ? number_format((float)$stats['avg'], 2, ',', '.') : '-' ?></td>
                        <td class="text-center fw-bold text-success"><?= $ranks[$siswa->id_siswa] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var table = $('#legerTable').DataTable({
            "responsive": true,
            "scrollX": true,
            "autoWidth": false,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "pageLength": -1,
            "dom": "<'row'<'col-md-4'l><'col-md-4 text-center'B><'col-md-4'f>>" +
                   "<'row'<'col-md-12'tr>>" +
                   "<'row'<'col-md-5'i><'col-md-7'p>>",
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                    className: 'btn btn-success btn-sm mt-1',
                    filename: 'Ledger_Nilai_<?= esc($kelasLabel) ?>_<?= esc($tahunLabel) ?>_Smt<?= esc($smtLabel) ?>',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm mt-1',
                    filename: 'Ledger_Nilai_<?= esc($kelasLabel) ?>_<?= esc($tahunLabel) ?>_Smt<?= esc($smtLabel) ?>',
                    orientation: 'landscape',
                    pageSize: 'LEGAL',
                    exportOptions: { columns: ':visible' }
                },
                {
                    extend: 'print',
                    text: '<i class="bi bi-printer"></i> Print',
                    className: 'btn btn-primary btn-sm mt-1',
                    exportOptions: { columns: ':visible' },
                    customize: function (win) {
                        $(win.document.body)
                            .css('font-size', '10px')
                            .find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                }
            ],
            language: dt_lang_id
        });
    });
</script>
<?= $this->endSection() ?>