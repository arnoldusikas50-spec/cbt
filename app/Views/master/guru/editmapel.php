<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --color-primary-50: #eef2ff;
        --color-primary-100: #e0e7ff;
        --color-primary-500: #6366f1;
        --color-primary-600: #4f46e5;
        --color-primary-700: #4338ca;
        
        --color-success-50: #f0fdf4;
        --color-success-600: #16a34a;

        --color-info-50: #f0f9ff;
        --color-info-600: #0284c7;

        --color-neutral-50: #f9fafb;
        --color-neutral-100: #f3f4f6;
        --color-neutral-200: #e5e7eb;
        --color-neutral-300: #d1d5db;
        --color-neutral-400: #9ca3af;
        --color-neutral-500: #6b7280;
        --color-neutral-600: #4b5563;
        --color-neutral-700: #374151;

        --radius-lg: 0.75rem;
        --radius-xl: 1rem;
        --radius-2xl: 1.5rem;
        --radius-full: 9999px;

        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        
        --font-size-xs: 0.75rem;
        --font-size-sm: 0.875rem;
        --transition-base: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body { font-family: 'Inter', sans-serif !important; background-color: #f8fafc !important; }

    /* ============================================
       TYPOGRAPHY & HEADERS
       ============================================ */
    .text-primary-gradient {
        background: linear-gradient(135deg, var(--color-primary-600) 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }

    /* ============================================
       PREMIUM COMPONENTS
       ============================================ */
    .pas-card {
        background: white;
        border-radius: var(--radius-2xl);
        border: 1px solid var(--color-neutral-200);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .pas-card__header {
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--color-neutral-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
    }

    .pas-card__title {
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--color-neutral-800);
    }

    .pas-card__title-icon {
        width: 40px; height: 40px;
        background: var(--color-primary-50);
        color: var(--color-primary-600);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }

    /* ============================================
       BUTTONS
       ============================================ */
    .pas-btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 0.5rem; padding: 0.625rem 1.25rem;
        font-weight: 600; font-size: var(--font-size-sm);
        border-radius: var(--radius-lg); transition: var(--transition-base);
        cursor: pointer; border: 1px solid transparent; text-decoration: none !important;
    }

    .pas-btn--primary { background: linear-gradient(135deg, var(--color-primary-600) 0%, var(--color-primary-700) 100%); color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
    .pas-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35); color: white; }

    .pas-btn--white { background: white; color: var(--color-neutral-700); border-color: var(--color-neutral-200); }
    .pas-btn--white:hover { background: var(--color-neutral-50); color: var(--color-neutral-900); }

    .pas-btn--danger-soft { background: var(--color-danger-50); color: var(--color-danger-600); }
    .pas-btn--danger-soft:hover { background: var(--color-danger-600); color: white; }

    .alert-premium {
        background: var(--color-info-50); border: 1px solid rgba(14, 165, 233, 0.2);
        color: var(--color-info-600); border-radius: var(--radius-lg);
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;
    }

    /* ============================================
       FORM CONTROLS
       ============================================ */
    .pas-form-select {
        border-radius: var(--radius-lg);
        border: 1px solid var(--color-neutral-200);
        padding: 0.75rem 1rem;
        background-color: var(--color-neutral-50);
        font-size: var(--font-size-sm);
        transition: var(--transition-base);
        width: 100%;
        outline: none;
    }
    .pas-form-select:focus {
        border-color: var(--color-primary-400);
        background-color: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .mapping-card {
        border: 1px solid var(--color-neutral-200);
        border-radius: var(--radius-xl);
        background: white;
        transition: var(--transition-base);
    }
    .mapping-card:hover { border-color: var(--color-primary-300); transform: translateY(-2px); box-shadow: var(--shadow-md); }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid var(--color-neutral-200) !important;
        border-radius: var(--radius-lg) !important;
        background-color: var(--color-neutral-50) !important;
        min-height: 48px !important;
        padding: 4px 8px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: var(--color-primary-400) !important;
        background-color: white !important;
    }
</style>

<div class="app-content-header pb-0">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark" style="font-family: 'Inter', sans-serif; letter-spacing: -1.5px;">
                    <span class="text-primary-gradient"><i class="bi bi-briefcase-fill me-2"></i></span>Penugasan Guru
                </h3>
                <p class="text-muted mb-0">Management jabatan fungsional dan mapping mata pelajaran</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('dataguru') ?>" class="text-decoration-none">Guru</a></li>
                    <li class="breadcrumb-item active">Edit Penugasan</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-4">
    <div class="container-fluid">
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="alert-premium">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div class="smaller">Tentukan jabatan utama dan mata pelajaran yang diampu oleh <b><?= esc($guru->nama_guru) ?></b> pada Tahun Pelajaran <b><?= $tp_active->tahun ?></b>.</div>
                </div>
            </div>
        </div>

        <form id="editjabatan">
            <?= csrf_field() ?>
            <input type="hidden" name="id_guru" value="<?= $guru->id_guru ?>">
            
            <div class="pas-card">
                <div class="pas-card__header">
                    <div class="pas-card__title">
                        <div class="pas-card__title-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Konfigurasi Tugas & Jabatan</h6>
                            <span class="smaller text-muted fw-normal">Pilih jabatan fungsional dan beban mengajar</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= site_url('dataguru') ?>" class="pas-btn pas-btn--white">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="pas-btn pas-btn--white text-info border-info-subtle" onclick="openCopyModal()" title="Salin penugasan dari semester sebelumnya">
                            <i class="bi bi-copy me-1"></i> Salin dari Semester Lain
                        </button>
                        <button type="submit" class="pas-btn pas-btn--primary">
                            <i class="bi bi-save-fill"></i> Simpan Penugasan
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Left Pillar: Jabatan & Mapel Selection -->
                        <div class="col-lg-5">
                            <div class="pas-card bg-light border-0 p-4 mb-4">
                                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-star-fill text-warning me-2"></i>Jabatan Utama</h6>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">Pilih Jabatan</label>
                                    <?= form_dropdown('level', $levels, $guru->id_level, 'id="level" class="pas-form-select" required') ?>
                                </div>
                                <div id="div-wali" class="<?= $guru->id_level == '4' ? '' : 'd-none' ?>">
                                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">Penugasan Wali Kelas</label>
                                    <?= form_dropdown('kelas_wali', $kelass, $guru->id_kelas, 'id="kelas_wali" class="pas-form-select"') ?>
                                </div>
                            </div>

                            <div class="pas-card bg-light border-0 p-4 mb-0">
                                <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-folder-fill text-primary me-2"></i>Beban Mengajar</h6>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">Pilihan Mata Pelajaran</label>
                                    <?= form_dropdown('mapel[]', $mapels, array_column($guru->mapel_kelas, 'id_mapel'), 'id="mapel" class="form-control select2" multiple="multiple" data-placeholder="Ketik nama mapel..."') ?>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wider mb-2">Kegiatan Ekstrakurikuler</label>
                                    <?= form_dropdown('ekstra[]', $ekskul, array_column($guru->ekstra_kelas, 'id_ekstra'), 'id="ekstra" class="form-control select2" multiple="multiple" data-placeholder="Ketik nama ekstra..."') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right Pillar: Mapping Rows -->
                        <div class="col-lg-7">
                            <div class="pas-card border-0 bg-light p-4 h-100" style="min-height: 400px;">
                                <h6 class="fw-bold mb-4 text-dark"><i class="bi bi-grid-3x3-gap-fill text-info me-2"></i>Pemetaan Kelas</h6>
                                <div id="mapping-container">
                                    <div id="input-mapel" class="mb-2"></div>
                                    <div id="input-ekstra"></div>
                                    
                                    <div id="empty-mapping" class="text-center text-muted m-auto py-5 opacity-50">
                                        <i class="bi bi-layers display-3 d-block mb-3"></i>
                                        <p class="fw-bold mb-0">Belum Ada Mapping</p>
                                        <span class="smaller">Pilih mata pelajaran atau ekstrakurikuler di panel kiri untuk mengatur pembagian kelas.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Copy Penugasan Dari Semester Lain -->
<div class="modal fade" id="modalCopyMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px; background: rgba(14, 165, 233, 0.12); color: #0284c7;">
                        <i class="bi bi-copy fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Salin Data Mengajar Guru</h5>
                        <span class="smaller text-muted">Ambil beban mengajar &amp; penugasan dari semester lain</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formCopyAssignment">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">Pilih Periode Sumber (Asal Data)</label>
                        <select id="copy_source_period" class="form-select pas-form-select" required>
                            <option value="">— Pilih Tahun Pelajaran &amp; Semester —</option>
                            <?php foreach ($all_periods as $p): ?>
                                <?php if ($p['id_tp'] != $tp_active->id_tp || $p['id_smt'] != $smt_active->id_smt): ?>
                                    <option value="<?= $p['id_tp'] ?>_<?= $p['id_smt'] ?>">
                                        TP <?= esc($p['tahun']) ?> — Semester <?= esc($p['smt']) ?> (<?= esc($p['nama_smt']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3" style="border-radius: 0.75rem;">
                        <label class="form-label small fw-bold text-dark mb-2">Komponen yang Disalin:</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="copy_opt_mapel" checked style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark mb-0" for="copy_opt_mapel" style="cursor: pointer;">
                                <i class="bi bi-book text-primary me-1"></i>Beban Mengajar (Mapel &amp; Pemetaan Kelas)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="copy_opt_ekstra" checked style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark mb-0" for="copy_opt_ekstra" style="cursor: pointer;">
                                <i class="bi bi-balloon text-success me-1"></i>Kegiatan Ekstrakurikuler
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="copy_opt_jabatan" checked style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark mb-0" for="copy_opt_jabatan" style="cursor: pointer;">
                                <i class="bi bi-person-badge text-warning me-1"></i>Jabatan Utama &amp; Wali Kelas
                            </label>
                        </div>
                    </div>

                    <div class="alert-premium py-2 px-3 mb-0" style="font-size: 0.8rem;">
                        <i class="bi bi-info-circle-fill fs-6"></i>
                        <div>Data akan diterapkan ke tampilan form. Klik <strong>Simpan Penugasan</strong> setelah memeriksa data.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="pas-btn pas-btn--primary rounded-pill px-4 fw-bold" onclick="executeCopyAssignment()">
                    <i class="bi bi-download me-1"></i>Salin &amp; Terapkan ke Form
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const kelass = <?= json_encode($kelass) ?>;
    const initialMapels = <?= json_encode($guru->mapel_kelas) ?>;
    const initialEkstras = <?= json_encode($guru->ekstra_kelas) ?>;

    function createMappingRow(type, id, name, selected = []) {
        const title = type === 'mapel' ? 'Mata Pelajaran' : 'Ekstrakurikuler';
        const prefix = type === 'mapel' ? 'kelasmapel' : 'kelasekstra';
        const namePrefix = type === 'mapel' ? 'nama_mapel' : 'nama_ekstra';
        const badgeColor = type === 'mapel' ? 'primary' : 'success';
        const selectedArr = (selected || []).map(String);

        let options = '';
        Object.entries(kelass).forEach(([val, label]) => {
            const isSelected = selectedArr.includes(String(val)) ? 'selected' : '';
            options += `<option value="${val}" ${isSelected}>${label}</option>`;
        });

        return `
            <div class="mapping-card p-3 mb-3 mapping-row shadow-sm" id="row-${type}-${id}">
                <div class="d-flex align-items-center mb-2 gap-2">
                    <span class="badge bg-${badgeColor} rounded-pill bg-opacity-10 text-${badgeColor} px-3 py-2 small fw-bold">${title}</span>
                    <span class="fw-bold text-dark">${name}</span>
                </div>
                <input type="hidden" name="${namePrefix}${id}" value="${name}">
                <select name="${prefix}${id}[]" class="form-control select2-mapping" multiple="multiple" data-placeholder="Pilih satu atau beberapa kelas...">
                    ${options}
                </select>
            </div>
        `;
    }

    $(document).ready(function () {
        $('.select2').select2({ width: '100%' });

        function updateEmptyState() {
            if ($('.mapping-row').length === 0) {
                $('#empty-mapping').show();
            } else {
                $('#empty-mapping').hide();
            }
        }

        // Handle initial data
        initialMapels.forEach(m => {
            const selected = m.kelas_mapel ? m.kelas_mapel.map(k => k.kelas) : [];
            $('#input-mapel').append(createMappingRow('mapel', m.id_mapel, m.nama_mapel, selected));
        });

        initialEkstras.forEach(e => {
            const selected = e.kelas_ekstra ? e.kelas_ekstra.map(k => k.kelas) : [];
            $('#input-ekstra').append(createMappingRow('ekstra', e.id_ekstra, e.nama_ekstra, selected));
        });

        $('.select2-mapping').select2({ width: '100%' });
        updateEmptyState();

        // Level change handling
        $('#level').change(function () {
            if ($(this).val() == '4') {
                $('#div-wali').removeClass('d-none').hide().fadeIn();
            } else {
                $('#div-wali').fadeOut(() => { $('#div-wali').addClass('d-none'); $('#kelas_wali').val(''); });
            }
        });

        // Mapel selection
        $('#mapel').on('select2:select', function (e) {
            const data = e.params.data;
            $('#input-mapel').append(createMappingRow('mapel', data.id, data.text));
            $(`#row-mapel-${data.id} .select2-mapping`).select2({ width: '100%' });
            updateEmptyState();
        });

        $('#mapel').on('select2:unselect', function (e) {
            $(`#row-mapel-${e.params.data.id}`).fadeOut(() => { $(`#row-mapel-${e.params.data.id}`).remove(); updateEmptyState(); });
        });

        // Ekstra selection
        $('#ekstra').on('select2:select', function (e) {
            const data = e.params.data;
            $('#input-ekstra').append(createMappingRow('ekstra', data.id, data.text));
            $(`#row-ekstra-${data.id} .select2-mapping`).select2({ width: '100%' });
            updateEmptyState();
        });

        $('#ekstra').on('select2:unselect', function (e) {
            $(`#row-ekstra-${e.params.data.id}`).fadeOut(() => { $(`#row-ekstra-${e.params.data.id}`).remove(); updateEmptyState(); });
        });

        // Form Submission
        $('#editjabatan').submit(function (e) {
            e.preventDefault();
            Swal.fire({
                title: '<h4 class="fw-bold m-0">Simpan Perubahan?</h4>',
                text: 'Penugasan jabatan dan pemetaan kelas akan segera diperbarui.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                customClass: { popup: 'rounded-4', confirmButton: 'rounded-pill px-4', cancelButton: 'rounded-pill px-4' }
            }).then(res => {
                if (res.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: '<?= site_url("dataguru/savejabatan") ?>',
                        type: 'POST',
                        data: $(this).serialize(),
                        success: (res) => {
                            Swal.close();
                            if (res.status) {
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: "Data penugasan guru telah disimpan", customClass: { popup: 'rounded-4' } });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || "Gagal memperbarui data", customClass: { popup: 'rounded-4' } });
                            }
                        }
                    });
                }
            });
        });
    });

    function openCopyModal() {
        $('#modalCopyMapel').modal('show');
    }

    function executeCopyAssignment() {
        const periodVal = $('#copy_source_period').val();
        if (!periodVal) {
            Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Silakan pilih Periode Sumber terlebih dahulu.', customClass: { popup: 'rounded-4' } });
            return;
        }

        const parts = periodVal.split('_');
        const idTp = parts[0];
        const idSmt = parts[1];
        const copyMapel = $('#copy_opt_mapel').is(':checked');
        const copyEkstra = $('#copy_opt_ekstra').is(':checked');
        const copyJabatan = $('#copy_opt_jabatan').is(':checked');

        if (!copyMapel && !copyEkstra && !copyJabatan) {
            Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Silakan pilih minimal 1 komponen yang akan disalin.', customClass: { popup: 'rounded-4' } });
            return;
        }

        Swal.fire({
            title: 'Mengambil Data...',
            text: 'Sedang mengunduh data penugasan dari semester sumber.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.get('<?= site_url("dataguru/get_assignment_source") ?>', {
            id_guru: '<?= $guru->id_guru ?>',
            id_tp: idTp,
            id_smt: idSmt
        }, function(res) {
            Swal.close();
            if (res.status && res.data) {
                const data = res.data;

                // 1. Salin Jabatan Utama & Wali Kelas
                if (copyJabatan) {
                    if (data.id_level) {
                        $('#level').val(data.id_level).trigger('change');
                        if (data.id_level == '4' && data.id_kelas) {
                            $('#kelas_wali').val(data.id_kelas);
                        }
                    }
                }

                // 2. Salin Beban Mengajar (Mapel & Pemetaan Kelas)
                if (copyMapel) {
                    $('#input-mapel').empty();
                    const mapelIds = [];
                    if (data.mapel_kelas && data.mapel_kelas.length > 0) {
                        data.mapel_kelas.forEach(m => {
                            mapelIds.push(m.id_mapel);
                            const selected = m.kelas_mapel ? m.kelas_mapel.map(k => String(k.kelas)) : [];
                            $('#input-mapel').append(createMappingRow('mapel', m.id_mapel, m.nama_mapel, selected));
                        });
                    }
                    $('#mapel').val(mapelIds).trigger('change.select2');
                    $('#input-mapel .select2-mapping').select2({ width: '100%' });
                }

                // 3. Salin Ekstrakurikuler
                if (copyEkstra) {
                    $('#input-ekstra').empty();
                    const ekstraIds = [];
                    if (data.ekstra_kelas && data.ekstra_kelas.length > 0) {
                        data.ekstra_kelas.forEach(e => {
                            ekstraIds.push(e.id_ekstra);
                            const selected = e.kelas_ekstra ? e.kelas_ekstra.map(k => String(k.kelas)) : [];
                            $('#input-ekstra').append(createMappingRow('ekstra', e.id_ekstra, e.nama_ekstra, selected));
                        });
                    }
                    $('#ekstra').val(ekstraIds).trigger('change.select2');
                    $('#input-ekstra .select2-mapping').select2({ width: '100%' });
                }

                // Update Empty State UI
                if (typeof updateEmptyState === 'function') {
                    updateEmptyState();
                } else if ($('.mapping-row').length === 0) {
                    $('#empty-mapping').show();
                } else {
                    $('#empty-mapping').hide();
                }

                $('#modalCopyMapel').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Data Disalin ke Form!',
                    text: 'Data mengajar dari semester sumber berhasil diterapkan ke form. Silakan periksa kembali dan klik "Simpan Penugasan".',
                    confirmButtonColor: '#4f46e5',
                    customClass: { popup: 'rounded-4', confirmButton: 'rounded-pill px-4' }
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal Menyalin', text: res.message || 'Data penugasan tidak ditemukan.', customClass: { popup: 'rounded-4' } });
            }
        }).fail(function() {
            Swal.close();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.', customClass: { popup: 'rounded-4' } });
        });
    }
</script>
<?= $this->endSection() ?>