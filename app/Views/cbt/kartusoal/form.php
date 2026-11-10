<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$uuid = $kartu->uuid ?? '';
$judulVal = $kartu->judul ?? '';
$descVal = $kartu->deskripsi ?? '';
$mapelIdVal = $kartu->mapel_id ?? '';
$kelasIdVal = $kartu->kelas_id ?? '';
$jenisIdVal = $kartu->id_jenis ?? '';
$tpIdVal = $kartu->tp_id ?? ($selected_tp_id ?? ($tp_active->id_tp ?? ''));
$smtIdVal = $kartu->smt_id ?? ($selected_smt_id ?? ($smt_active->id_smt ?? ''));
$isEdit = !empty($uuid);
$isMapelLocked = $isEdit && (string) session('role') !== '1';
$itemCount = (int) ($item_count ?? 0);
$wf = strtolower($kartu->workflow_status ?? 'draft');
$wfProps = [
    'approved' => ['Disetujui', 'success', 'check-circle-fill'],
    'submitted' => ['Menunggu Validasi', 'info', 'hourglass-split'],
    'revisi' => ['Perlu Revisi', 'warning', 'exclamation-circle-fill'],
    'draft' => ['Draft', 'secondary', 'pencil-fill']
][$wf] ?? ['Draft', 'secondary', 'pencil-fill'];
?>

<!-- Content Header (Page Header) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-journal-check text-primary me-2"></i>
                    <?= $isEdit ? 'Edit Data Master' : 'Buat' ?> Kartu Soal
                </h3>
                <p class="text-muted small mb-0 mt-1">Kelola data master kartu soal tanpa menginput butir kisi-kisi di halaman ini</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('cbtkartusoal') ?>" class="text-decoration-none">Kartu Soal</a>
                    </li>
                    <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Baru' ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Status Pill Badges & Top Action Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                    <i class="bi bi-inboxes me-1"></i><span id="itemCountBadge"><?= $itemCount ?></span> Butir Terkait
                </span>
                <span class="badge bg-info bg-opacity-10 text-info border px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                    <i class="bi bi-info-circle me-1"></i><?= $isEdit ? 'Mode Edit Kartu' : 'Mode Buat Kartu Baru' ?>
                </span>
                <span class="badge bg-<?= $wfProps[1] ?> bg-opacity-10 text-<?= $wfProps[1] ?> border px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                    <i class="bi bi-<?= $wfProps[2] ?> me-1"></i>Status: <?= $wfProps[0] ?>
                </span>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <a href="<?= site_url('cbtkartusoal') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
                <?php if ($isEdit): ?>
                    <a href="<?= site_url('cbtkartusoal/detail/' . $uuid) ?>" class="btn btn-primary btn-sm shadow-sm fw-bold" id="btnManageItems">
                        <i class="bi bi-list-check me-1"></i> Kelola Butir Soal & Kisi-kisi →
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-primary btn-sm shadow-sm fw-bold" disabled title="Simpan data master kartu soal terlebih dahulu" id="btnManageItemsDisabled">
                        <i class="bi bi-list-check me-1"></i> Kelola Butir Soal →
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Master Card -->
        <div class="card card-default my-shadow mb-4">
            <div class="card-header bg-light border-bottom">
                <h6 class="card-title fw-bold mb-0 text-dark">
                    <i class="bi bi-info-square me-2 text-primary"></i>Form Master Kartu Soal
                </h6>
            </div>

            <div class="card-body p-4">
                <form id="kartuForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="uuid" id="uuid" value="<?= esc($uuid) ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Mata Pelajaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-book text-primary"></i></span>
                                <select class="form-select" name="mapel_id" id="mapel_id" required <?= $isMapelLocked ? 'disabled' : '' ?>>
                                    <option value="">Pilih Mapel...</option>
                                    <?php foreach ($mapels as $m): ?>
                                        <option value="<?= (int) $m->id_mapel ?>" data-kode="<?= esc($m->kode ?? '') ?>" <?= $mapelIdVal == $m->id_mapel ? 'selected' : '' ?>>
                                            <?= esc($m->nama_mapel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($isMapelLocked): ?>
                                <input type="hidden" name="mapel_id" value="<?= esc($mapelIdVal) ?>">
                            <?php endif; ?>
                            <div class="invalid-feedback small" id="mapelError"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Kelas Target <span class="text-danger">*</span></label>
                            <select name="kelas_ids[]" id="select-kelas-kartu" class="form-select"
                                multiple="multiple" required data-placeholder="Pilih kelas..."></select>
                            <div class="text-danger small mt-1" id="kelasError" style="display:none;"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Jenis Ujian <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-card-checklist text-primary"></i></span>
                                <select class="form-select" name="id_jenis" id="id_jenis" required>
                                    <option value="">Pilih Jenis...</option>
                                    <?php foreach (($jenis_ujian ?? []) as $j): ?>
                                        <option value="<?= (int) ($j->id_jenis ?? 0) ?>" data-kode="<?= esc($j->kode_jenis ?? '') ?>" <?= (int) $jenisIdVal === (int) ($j->id_jenis ?? 0) ? 'selected' : '' ?>>
                                            <?= esc($j->nama_jenis ?? '-') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="invalid-feedback small" id="jenisError"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Tahun Pelajaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar3 text-primary"></i></span>
                                <select class="form-select" name="tp_id" id="tp_id" required>
                                    <option value="">Pilih Tahun...</option>
                                    <?php foreach (($tp_options ?? []) as $tpOpt): ?>
                                        <option value="<?= (int) ($tpOpt->id_tp ?? 0) ?>" <?= (int) $tpIdVal === (int) ($tpOpt->id_tp ?? 0) ? 'selected' : '' ?>>
                                            <?= esc($tpOpt->tahun ?? '-') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="invalid-feedback small" id="tpError"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Semester <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-bookmark-fill text-primary"></i></span>
                                <select class="form-select" name="smt_id" id="smt_id" required>
                                    <option value="">Pilih Semester...</option>
                                    <?php foreach (($smt_options ?? []) as $smtOpt): ?>
                                        <option value="<?= (int) ($smtOpt->id_smt ?? 0) ?>" <?= (int) $smtIdVal === (int) ($smtOpt->id_smt ?? 0) ? 'selected' : '' ?>>
                                            <?= esc($smtOpt->nama_smt ?? '-') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="invalid-feedback small" id="smtError"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Judul Kartu Soal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-pencil text-primary"></i></span>
                            <input type="text" class="form-control" name="judul" id="judul" value="<?= esc($judulVal) ?>" maxlength="150" placeholder="Contoh: Kartu Soal PAS Matematika Kelas 11 Ganjil" required>
                            <button type="button" class="btn btn-outline-primary" id="btnAutoJudul" title="Buat judul otomatis dari pilihan di atas">
                                <i class="bi bi-magic me-1"></i> Buat Otomatis
                            </button>
                        </div>
                        <div class="form-text text-muted small mt-1">Judul akan terisi otomatis dari pilihan Mapel, Jenis Ujian, Kelas Target, &amp; Periode di atas.</div>
                        <div class="invalid-feedback small" id="judulError"></div>
                    </div>

                    <?php if (session('role') === '1' || session('role') === '2'): ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Pembuat Kisi-kisi (Guru)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person-workspace text-primary"></i></span>
                            <select class="form-select select2" name="created_by" id="created_by">
                                <option value="">-- Gunakan User Saat Ini --</option>
                                <?php foreach (($gurus ?? []) as $g): ?>
                                    <option value="<?= (int) $g->id_user ?>" <?= (int) ($kartu->created_by ?? 0) === (int) $g->id_user ? 'selected' : '' ?>>
                                        <?= esc($g->nama_guru) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-text mt-1 text-muted small">Admin/Panitia dapat menetapkan guru penyusun kisi-kisi ini.</div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-1">Deskripsi / Catatan Tambahan</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3" placeholder="Deskripsi singkat tentang kisi-kisi kartu soal ini..."><?= esc($descVal) ?></textarea>
                    </div>

                    <div class="border-top pt-3 text-end d-flex justify-content-end gap-2 flex-wrap">
                        <?php if ($isEdit && session('role') === '1'): ?>
                            <button type="button" class="btn btn-outline-danger" id="btnDelete">
                                <i class="bi bi-trash me-1"></i> Hapus Kartu
                            </button>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSave">
                            <span class="spinner-border spinner-border-sm d-none" id="saveSpinner"></span>
                            <i class="bi bi-check-lg me-1" id="saveIcon"></i>
                            <span id="saveText"><?= $isEdit ? 'Simpan Perubahan Master' : 'Simpan & Lanjutkan' ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.KARTU_SOAL = {
        uuid:    <?= json_encode($uuid) ?>,
        isEdit:  <?= $isEdit ? 'true' : 'false' ?>,
        mapelId: <?= (int) ($kartu->mapel_id ?? 0) ?>,
        tpId:    <?= (int) ($tpIdVal ?? 0) ?>,
        smtId:   <?= (int) ($smtIdVal ?? 0) ?>,
        kelasIds:<?= json_encode(
            (function() use ($isEdit, $kartu) {
                if (!$isEdit) return [];
                if (!empty($kartu->kelas_ids)) {
                    $d = json_decode($kartu->kelas_ids, true);
                    return is_array($d) ? array_map('intval', $d) : [(int)($kartu->kelas_id ?? 0)];
                }
                return $kartu->kelas_id ? [(int)$kartu->kelas_id] : [];
            })()
        ) ?>
    };

    const BASE_URL = '<?= base_url() ?>';

    $(document).ready(function () {
        const selKelas = $('#select-kelas-kartu');
        const selMapel = $('#mapel_id');

        // Init Select2
        selKelas.select2({
            width: '100%',
            placeholder: 'Pilih kelas...',
            allowClear: true
        });

        function loadKelasByMapel(mapelId, selectedIds) {
            selKelas.empty().trigger('change');
            if (!mapelId) return;

            $.get(BASE_URL + 'cbtkartusoal/api/kelas-by-mapel?mapel_id=' + mapelId)
                .done(function (res) {
                    const list = res.data || [];
                    list.forEach(function (k) {
                        const id  = String(k.id_kelas || k.id || '');
                        const txt = (k.nama_kelas || k.nama || id) + (k.level_id ? ' (' + k.level_id + ')' : '');
                        const isSelected = (selectedIds || []).map(String).includes(id);
                        const opt = new Option(txt, id, isSelected, isSelected);
                        if (k.level_id) opt.dataset.level = k.level_id;
                        selKelas.append(opt);
                    });
                    selKelas.trigger('change');
                });
        }

        // On mapel change (create mode, or admin editing) — reload kelas
        selMapel.on('change', function () {
            const mapelId = $(this).val();
            if (!selMapel.prop('disabled')) {
                loadKelasByMapel(mapelId, []);
            }
        });

        // On page load: pre-load kelas for current mapel
        const initMapelId = selMapel.val() || (window.KARTU_SOAL.isEdit ? String(window.KARTU_SOAL.mapelId) : '');
        if (initMapelId) {
            loadKelasByMapel(initMapelId, window.KARTU_SOAL.kelasIds);
        }
    });
</script>
<script src="<?= base_url('assets/app/js/cbt/kartusoal/crud.js?v=' . date('YmdHis')) ?>"></script>
<?= $this->endSection() ?>
