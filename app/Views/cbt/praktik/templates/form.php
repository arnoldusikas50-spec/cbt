<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    :root {
        --cbt-primary: #6366f1;
        --cbt-primary-soft: rgba(99, 102, 241, 0.1);
        --cbt-success: #10b981;
        --cbt-warning: #f59e0b;
        --cbt-danger: #ef4444;
        --cbt-border: #e2e8f0;
    }

    .border-dashed { border-style: dashed !important; }
    .btn-xs { padding: 0.125rem 0.5rem; font-size: 0.75rem; }
    .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .fade-in-up { animation: fadeInUp 0.4s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    
    .card-premium { border: none; border-radius: 1.25rem; }
    .card-header-premium { background: transparent; border-bottom: 1px solid var(--cbt-border); padding: 1.25rem 1.5rem; }
    
    .kriteria-card { 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        border: 1px solid var(--cbt-border);
        border-radius: 1rem;
        background: #fff;
        margin-bottom: 1.25rem;
        position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .kriteria-card:hover { 
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08) !important;
        border-color: var(--cbt-primary);
    }
    .kriteria-number {
        position: absolute;
        left: -12px;
        top: -12px;
        width: 28px;
        height: 28px;
        background: var(--cbt-primary);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        z-index: 2;
        font-size: 0.9rem;
    }

    .level-item {
        transition: all 0.2s ease;
        border: 1px solid var(--cbt-border);
        background: #ffffff;
        padding: 0.5rem !important;
    }
    .level-item:hover {
        border-color: var(--cbt-primary);
        background: #fdfdfd !important;
        box-shadow: 0 2px 4px -1px rgba(0,0,0,0.05) !important;
    }
    
    .input-premium {
        border-radius: 0.5rem;
        border: 1.5px solid #e2e8f0;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s;
        background-color: #f8fafc;
        font-size: 0.875rem;
    }
    .input-premium:focus {
        background-color: #fff;
        border-color: var(--cbt-primary);
        box-shadow: 0 0 0 3px var(--cbt-primary-soft);
    }
    
    .form-label-premium {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-action-premium {
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-weight: 700;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.875rem;
    }

    .total-bobot-container {
        background: #fff;
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        border: 1.5px solid var(--cbt-border);
    }

    .levels-container {
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 1rem;
        border: 1.5px dashed #cbd5e1;
    }

    .btn-add-level {
        background: #fff;
        border: 1.25px solid var(--cbt-primary);
        color: var(--cbt-primary);
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
        border-radius: 0.5rem;
    }

    .level-badge-input {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.25rem 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        min-width: 120px;
    }
    .level-badge-input:focus-within {
        border-color: var(--cbt-primary);
    }
    .level-badge-input label {
        font-size: 0.6rem;
        font-weight: 800;
        color: #94a3b8;
        margin-bottom: 0;
    }
    .level-badge-input input {
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 700;
        color: var(--cbt-primary);
        width: 35px;
        outline: none;
        font-size: 0.9rem;
    }

    .kriteria-header {
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }

    .btn-remove-kriteria {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fee2e2;
        color: #ef4444;
        border: none;
        font-size: 0.8rem;
    }
</style>

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                    <?= $isEdit ? 'Edit' : 'Buat' ?> Template Rubrik
                </h3>
            </div>
            <div class="col-sm-6 text-sm-end">
                <a href="<?= site_url('cbtpraktik/templates') ?>" class="btn btn-link text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form id="formTemplate" action="<?= $isEdit ? site_url('cbtpraktik/templates/update/' . $template->id) : site_url('cbtpraktik/templates/save') ?>" method="POST">
            <div class="row">
                <!-- Main Template Info -->
                <div class="col-lg-4">
                    <div class="card card-premium shadow-sm mb-4 fade-in-up">
                        <div class="card-header-premium">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-info-circle text-primary me-2"></i>Info Utama
                            </h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <label class="form-label-premium">Nama Template <span class="text-danger">*</span></label>
                                <input type="text" name="nama_template" class="form-control input-premium" placeholder="Misal: Rubrik Praktik PHP Dasar" value="<?= $isEdit ? esc($template->nama_template) : '' ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-premium">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control input-premium" rows="2" placeholder="Jelaskan penggunaan rubrik ini"><?= $isEdit ? esc($template->deskripsi) : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-premium">Jenis Praktik</label>
                                <select name="jenis_praktik" class="form-select input-premium">
                                    <option value="semua" <?= $isEdit && $template->jenis_praktik == 'semua' ? 'selected' : '' ?>>Semua Jenis</option>
                                    <option value="coding" <?= $isEdit && $template->jenis_praktik == 'coding' ? 'selected' : '' ?>>Coding</option>
                                    <option value="simulasi" <?= $isEdit && $template->jenis_praktik == 'simulasi' ? 'selected' : '' ?>>Simulasi</option>
                                    <option value="hands_on" <?= $isEdit && $template->jenis_praktik == 'hands_on' ? 'selected' : '' ?>>Hands-on</option>
                                    <option value="upload_file" <?= $isEdit && $template->jenis_praktik == 'upload_file' ? 'selected' : '' ?>>Upload File</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-premium">Mata Pelajaran</label>
                                <?php echo form_dropdown('mapel_id', $mapels ?? [], $isEdit ? ($template->mapel_id ?? '') : '', 'class="form-select select2" style="width:100%"'); ?>
                            </div>
                            
                            <?php if (isset($isAdmin) && $isAdmin): ?>
                            <div class="mb-3">
                                <label class="form-label-premium text-warning">Guru Pembuat (Admin Akses)</label>
                                <?php echo form_dropdown('created_by', $gurus ?? [], $isEdit ? ($template->created_by ?? '') : ($user->id ?? ''), 'class="form-select select2" style="width:100%"'); ?>
                            </div>
                            <?php endif; ?>

                            <div class="mb-0">
                                <label class="form-label-premium">Aksesibilitas</label>
                                <div class="p-2 bg-light rounded-3 border">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1" <?= $isEdit && $template->is_public ? 'checked' : '' ?> style="cursor: pointer;">
                                        <label class="form-check-label fw-semibold small" for="is_public" style="cursor: pointer;">Jadikan Publik</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-premium shadow-sm fade-in-up border-start border-4 border-info">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-info mb-3"><i class="bi bi-lightbulb me-2"></i>Panduan Cepat</h6>
                            <ul class="list-unstyled mb-0 d-grid gap-2">
                                <li class="d-flex gap-2 small">
                                    <i class="bi bi-check2-circle text-success"></i>
                                    <span>Buat minimal 1 <strong>Kriteria</strong> (Contoh: Logika Program).</span>
                                </li>
                                <li class="d-flex gap-2 small">
                                    <i class="bi bi-check2-circle text-success"></i>
                                    <span>Pastikan total <strong>Bobot</strong> pas 100%.</span>
                                </li>
                                <li class="d-flex gap-2 small">
                                    <i class="bi bi-check2-circle text-success"></i>
                                    <span>Gunakan <strong>Level Skor</strong> untuk memandu penilaian.</span>
                                </li>
                            </ul>
                            <div class="mt-4 p-3 bg-info bg-opacity-10 rounded-3 border border-info border-opacity-25">
                                <p class="mb-0 text-info small">
                                    <i class="bi bi-magic me-1 fw-bold"></i>
                                    <strong>Tips AI:</strong> Gunakan tombol <strong>AI Generate</strong> untuk membuat draf rubrik secara instan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rubric Criteria Management -->
                <div class="col-lg-8">
                    <div class="card card-premium shadow-sm mb-4 fade-in-up">
                        <div class="card-header-premium d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-list-stars text-primary me-2"></i>Kriteria & Pembobotan
                            </h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-action-premium shadow-sm" onclick="addKriteria()">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Kriteria
                                </button>
                                <button type="button" class="btn btn-success btn-action-premium shadow-sm" onclick="showAiGenerateModal()">
                                    <i class="bi bi-magic me-1"></i> AI Generate
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div id="rubricItemsContainer">
                                <!-- Rubric Items will be appended here -->
                            </div>
                            <div id="emptyRubricState" class="text-center py-5 <?= $isEdit && !empty($template->items) ? 'd-none' : '' ?>">
                                <div class="bg-light d-inline-flex p-4 rounded-circle mb-3">
                                    <i class="bi bi-journal-plus fs-1 text-muted opacity-50"></i>
                                </div>
                                <h5 class="fw-bold text-muted">Belum ada kriteria</h5>
                                <p class="text-muted">Klik 'Tambah Kriteria' untuk mulai merancang rubrik penilaian.</p>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-4 pt-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-4 bg-light rounded-4">
                                <div class="total-bobot-container">
                                    <span class="fw-bold text-muted small text-uppercase">Total Bobot:</span>
                                    <span class="h4 fw-bold mb-0" id="totalBobotBadge">0</span><span class="h4 fw-bold mb-0">%</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-action-premium px-5 shadow-sm" id="btnSaveTemplate">
                                        <i class="bi bi-save me-2"></i> Simpan Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Template for Rubric Item -->
<template id="kriteriaTemplate">
    <div class="kriteria-card p-3" data-id="{{id}}">
        <div class="kriteria-number"></div>
        <div class="kriteria-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-primary mb-0 small"><i class="bi bi-tag-fill me-1"></i>Kriteria Penilaian</h6>
            <button type="button" class="btn-remove-kriteria" onclick="removeKriteria('{{id}}')" title="Hapus Kriteria">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label-premium">Kode</label>
                <input type="text" class="form-control input-premium kriteria-kode" value="{{kode}}" placeholder="K01">
            </div>
            <div class="col-md-7">
                <label class="form-label-premium">Nama Kriteria</label>
                <input type="text" class="form-control input-premium kriteria-nama" value="{{nama}}" placeholder="Misal: Kerapihan Rangkaian">
            </div>
            <div class="col-md-3">
                <label class="form-label-premium">Bobot (%)</label>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control input-premium kriteria-bobot fw-bold text-primary" value="{{bobot}}" min="1" max="100">
                    <span class="input-group-text bg-white border-2 border-start-0 px-2 fw-bold text-primary" style="border-radius: 0 0.5rem 0.5rem 0; border-color: #e2e8f0; font-size: 0.8rem;">%</span>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label-premium">Deskripsi / Indikator Penilaian</label>
                <textarea class="form-control input-premium kriteria-deskripsi" rows="1" placeholder="Detail kriteria penilaian">{{deskripsi}}</textarea>
            </div>
            <div class="col-12 mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="fw-bold text-dark small text-uppercase" style="font-size: 0.65rem;">
                        <i class="bi bi-bar-chart-steps text-primary me-1"></i>Tingkatan Skor
                    </span>
                    <button type="button" class="btn-add-level shadow-sm" onclick="addLevel('{{id}}')">
                        <i class="bi bi-plus-lg me-1"></i> Skor
                    </button>
                </div>
                <div class="levels-container d-grid gap-2">
                    <!-- Levels will be here -->
                </div>
            </div>
        </div>
    </div>
</template>

<template id="levelTemplate">
    <div class="level-item d-flex gap-2 align-items-center bg-white border rounded-3" data-level="{{num}}">
        <div class="d-flex align-items-center gap-1 bg-light p-1 rounded-2 border">
            <div class="level-badge-input">
                <label>MIN</label>
                <input type="number" class="level-score-min" value="{{min}}" placeholder="0">
            </div>
            <div class="text-muted fw-bold small">/</div>
            <div class="level-badge-input">
                <label>MAX</label>
                <input type="number" class="level-score-max" value="{{max}}" placeholder="100">
            </div>
        </div>
        <div class="flex-grow-1">
            <input type="text" class="form-control level-label border-0 bg-transparent px-2 fw-semibold text-dark" style="font-size: 0.85rem;" value="{{label}}" placeholder="Deskripsi tingkatan nilai...">
        </div>
        <button type="button" class="btn btn-link text-danger p-1 me-1" onclick="$(this).parent().remove()" title="Hapus Level">
            <i class="bi bi-trash3-fill" style="font-size: 0.9rem;"></i>
        </button>
    </div>
</template>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let kriteriaCount = 0;

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({ theme: 'bootstrap4' });
    
    <?php if ($isEdit && !empty($template->items)): ?>
        <?php foreach ($template->items as $item): ?>
            populateKriteria({
                kode: '<?= esc($item->kode_kriteria) ?>',
                nama: '<?= esc($item->nama_kriteria) ?>',
                bobot: '<?= esc($item->bobot_persen) ?>',
                deskripsi: '<?= esc($item->deskripsi) ?>',
                levels: <?= $item->level_detail ? $item->level_detail : '[]' ?>
            });
        <?php endforeach; ?>
    <?php endif; ?>

    updateTotalBobot();

    $(document).on('input', '.kriteria-bobot', function() {
        updateTotalBobot();
    });

    $('#formTemplate').submit(function(e) {
        e.preventDefault();
        
        let valid = true;
        let totalBobot = 0;
        let items = [];

        $('.kriteria-card').each(function() {
            let card = $(this);
            let item = {
                kode: card.find('.kriteria-kode').val(),
                nama: card.find('.kriteria-nama').val(),
                bobot: parseFloat(card.find('.kriteria-bobot').val()) || 0,
                deskripsi: card.find('.kriteria-deskripsi').val(),
                levels: []
            };

            if (!item.nama || !item.kode) {
                valid = false;
                card.addClass('border-danger');
                return false;
            }

            totalBobot += item.bobot;

            card.find('.level-item').each(function() {
                let lvl = $(this);
                item.levels.push({
                    level: lvl.index() + 1,
                    nilai_min: lvl.find('.level-score-min').val(),
                    nilai_max: lvl.find('.level-score-max').val(),
                    nama_level: lvl.find('.level-label').val()
                });
            });

            items.push(item);
        });

        if (!valid) {
            Swal.fire('Oops!', 'Mohon lengkapi seluruh kriteria dan kode.', 'warning');
            return;
        }

        if (totalBobot !== 100) {
            Swal.fire('Error Bobot', 'Total bobot kriteria harus tepat 100%. Saat ini: ' + totalBobot + '%', 'error');
            return;
        }

        if (items.length === 0) {
            Swal.fire('Oops!', 'Tambahkan minimal 1 kriteria penilaian.', 'warning');
            return;
        }

        $('#btnSaveTemplate').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i> Menyimpan...');

        let formData = new FormData(this);
        formData.append('items_raw', JSON.stringify(items));

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                console.log('Save response:', res);
                if (res.debug_info) {
                    console.log('Debug info:', res.debug_info);
                }
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success').then(() => {
                        window.location.href = '<?= site_url('cbtpraktik/templates') ?>';
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                    $('#btnSaveTemplate').prop('disabled', false).html('<i class="bi bi-save me-2"></i> Simpan Template');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
                console.error('Response Text:', xhr.responseText);
                let errorMsg = 'Terjadi kesalahan sistem.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) errorMsg = response.message;
                    if (response.file) errorMsg += '\nFile: ' + response.file;
                    if (response.line) errorMsg += '\nLine: ' + response.line;
                } catch(e) {
                    errorMsg = xhr.responseText || error;
                }
                Swal.fire('Error', errorMsg, 'error');
                $('#btnSaveTemplate').prop('disabled', false).html('<i class="bi bi-save me-2"></i> Simpan Template');
            }
        });
    });
});

function addKriteria() {
    kriteriaCount++;
    const id = 'k' + kriteriaCount;
    let html = $('#kriteriaTemplate').html()
                .replaceAll('{{id}}', id)
                .replace('{{kode}}', 'K' + (kriteriaCount < 10 ? '0' + kriteriaCount : kriteriaCount))
                .replace('{{nama}}', '')
                .replace('{{bobot}}', '0')
                .replace('{{deskripsi}}', '');
    
    $('#rubricItemsContainer').append(html);
    $('#emptyRubricState').addClass('d-none');
    
    updateCriteriaNumbers();
    
    // Add default levels
    addLevel(id, 0, 70, 'Cukup');
    addLevel(id, 71, 90, 'Baik');
    addLevel(id, 91, 100, 'Sangat Baik');
}

function updateCriteriaNumbers() {
    $('.kriteria-card').each(function(index) {
        $(this).find('.kriteria-number').text(index + 1);
    });
}

function populateKriteria(data) {
    kriteriaCount++;
    const id = 'k' + kriteriaCount;
    let html = $('#kriteriaTemplate').html()
                .replaceAll('{{id}}', id)
                .replace('{{kode}}', data.kode)
                .replace('{{nama}}', data.nama)
                .replace('{{bobot}}', data.bobot)
                .replace('{{deskripsi}}', data.deskripsi);
    
    $('#rubricItemsContainer').append(html);
    
    const container = $(`.kriteria-card[data-id="${id}"] .levels-container`);
    if (data.levels && data.levels.length > 0) {
        data.levels.forEach((lvl, i) => {
            let lhtml = $('#levelTemplate').html()
                .replace('{{num}}', i + 1)
                .replace('{{min}}', lvl.nilai_min)
                .replace('{{max}}', lvl.nilai_max)
                .replace('{{label}}', lvl.nama_level);
            container.append(lhtml);
        });
    }
    updateCriteriaNumbers();
}

function addLevel(id, min = '', max = '', label = '') {
    const container = $(`.kriteria-card[data-id="${id}"] .levels-container`);
    const num = container.find('.level-item').length + 1;
    let html = $('#levelTemplate').html()
                .replace('{{num}}', num)
                .replace('{{min}}', min)
                .replace('{{max}}', max)
                .replace('{{label}}', label);
    container.append(html);
}

function removeKriteria(id) {
    $(`.kriteria-card[data-id="${id}"]`).remove();
    if ($('#rubricItemsContainer .kriteria-card').length === 0) {
        $('#emptyRubricState').removeClass('d-none');
    }
    updateCriteriaNumbers();
    updateTotalBobot();
}

function updateTotalBobot() {
    let total = 0;
    $('.kriteria-bobot').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    
    const badge = $('#totalBobotBadge');
    const container = $('.total-bobot-container');
    badge.text(total);
    
    container.removeClass('valid invalid');
    if (total === 100) {
        container.addClass('valid');
        badge.parent().removeClass('text-muted text-danger text-warning').addClass('text-success');
    } else if (total > 100) {
        container.addClass('invalid');
        badge.parent().removeClass('text-muted text-success text-warning').addClass('text-danger');
    } else {
        badge.parent().removeClass('text-muted text-success text-danger').addClass('text-warning');
    }
}

// AI Generate Functions
function showAiGenerateModal() {
    const mapelNama = $('select[name="mapel_id"] option:selected').text();
    const jenisPraktik = $('select[name="jenis_praktik"] option:selected').text();
    
    const defaultPrompt = `Buatkan rubrik penilaian praktik ${jenisPraktik} untuk mata pelajaran ${mapelNama}. ` +
        `Sertakan kriteria seperti: Persiapan, Prosedur/Pelaksanaan, Hasil Kerja, dan Keselamatan/K3.`;
    
    Swal.fire({
        title: '<i class="bi bi-magic text-success"></i> AI Auto Generate Rubrik',
        html: `
            <div class="text-start">
                <label class="form-label small text-muted mb-2">Deskripsikan praktik yang akan dinilai:</label>
                <textarea id="aiPrompt" class="form-control" rows="5" placeholder="Contoh: Buatkan rubrik penilaian praktik merakit komputer untuk SMK jurusan TKJ...">${defaultPrompt}</textarea>
                <small class="text-muted mt-2 d-block">
                    <i class="bi bi-info-circle me-1"></i>
                    AI akan menghasilkan 4-5 kriteria dengan bobot yang sesuai (total 100%)
                </small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-magic me-1"></i> Generate',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
        width: '600px',
        preConfirm: () => {
            const prompt = $('#aiPrompt').val();
            if (!prompt || prompt.trim() === '') {
                Swal.showValidationMessage('Mohon isi deskripsi praktik');
                return false;
            }
            return prompt;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            generateRubricWithAI(result.value);
        }
    });
}

function generateRubricWithAI(prompt) {
    Swal.fire({
        title: 'Menghasilkan Rubrik...',
        text: 'AI sedang membuat kriteria penilaian',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '<?= site_url('cbtpraktik/templates_generate_ai') ?>',
        type: 'POST',
        data: { 
            prompt: prompt,
            jenis_praktik: $('select[name="jenis_praktik"]').val()
        },
        dataType: 'json',
        success: function(res) {
            if (res.status && res.data) {
                // Clear existing rubric items
                $('#rubricItemsContainer').empty();
                kriteriaCount = 0;
                
                // Add generated items
                res.data.forEach((item, index) => {
                    setTimeout(() => {
                        populateKriteriaFromAI(item);
                        if (index === res.data.length - 1) {
                            updateTotalBobot();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: `${res.data.length} kriteria telah dibuat oleh AI`,
                                confirmButtonColor: '#198754'
                            });
                        }
                    }, index * 100);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Tidak dapat menghasilkan rubrik'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan sistem saat menghubungi AI'
            });
        }
    });
}

function populateKriteriaFromAI(data) {
    kriteriaCount++;
    const id = 'k' + kriteriaCount;
    let html = $('#kriteriaTemplate').html()
                .replaceAll('{{id}}', id)
                .replace('{{kode}}', data.kode)
                .replace('{{nama}}', data.nama)
                .replace('{{bobot}}', data.bobot)
                .replace('{{deskripsi}}', data.deskripsi);
    
    $('#rubricItemsContainer').append(html);
    $('#emptyRubricState').addClass('d-none');
    
    updateCriteriaNumbers();
    
    const container = $(`.kriteria-card[data-id="${id}"] .levels-container`);
    if (data.levels && data.levels.length > 0) {
        data.levels.forEach((lvl, i) => {
            let lhtml = $('#levelTemplate').html()
                .replace('{{num}}', i + 1)
                .replace('{{min}}', lvl.min)
                .replace('{{max}}', lvl.max)
                .replace('{{label}}', lvl.label);
            container.append(lhtml);
        });
    } else {
        // Default levels
        addLevel(id, 0, 60, 'Kurang');
        addLevel(id, 61, 80, 'Cukup');
        addLevel(id, 81, 90, 'Baik');
        addLevel(id, 91, 100, 'Sangat Baik');
    }
}
</script>
<?= $this->endSection() ?>
