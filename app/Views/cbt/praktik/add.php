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

    .fade-in-up { animation: fadeInUp 0.4s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    .card-premium { border: none; border-radius: 1.25rem; }
    .card-header-premium { background: transparent; border-bottom: 1px solid var(--cbt-border); padding: 1.25rem 1.5rem; }
    
    .input-premium {
        border-radius: 0.75rem;
        border: 1px solid #cbd5e1;
        padding: 0.6rem 1rem;
        transition: all 0.2s;
    }
    .input-premium:focus {
        border-color: var(--cbt-primary);
        box-shadow: 0 0 0 4px var(--cbt-primary-soft);
    }
    
    .form-label-premium {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.5rem;
    }

    .btn-action-premium {
        border-radius: 0.75rem;
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-action-premium:hover {
        transform: translateY(-2px);
    }

    .section-title-premium {
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--cbt-primary);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .section-title-premium::after {
        content: "";
        height: 1px;
        flex-grow: 1;
        background: linear-gradient(to right, var(--cbt-primary-soft), transparent);
    }

    .mapping-card {
        border: 1px solid var(--cbt-border);
        border-radius: 1rem;
        background: #f8fafc;
        padding: 1.5rem;
        transition: all 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .mapping-card:hover {
        border-color: var(--cbt-primary);
        background: #fff;
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.05);
    }

    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.75rem !important;
        border: 1px solid #cbd5e1 !important;
        min-height: 44px !important;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: var(--cbt-primary) !important;
        box-shadow: 0 0 0 4px var(--cbt-primary-soft) !important;
    }
</style>

<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-laptop text-primary me-2"></i>
                    <?= esc($judul ?? 'Buat Ujian Praktik') ?>
                </h3>
            </div>
            <div class="col-sm-6 text-sm-end">
                <a href="<?= site_url('cbtpraktik') ?>" class="btn btn-link text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content pt-3">
    <div class="container-fluid px-4">
        <?= form_open('cbtpraktik/save', ['id' => 'formCreatePraktik']) ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-journal-plus text-primary me-2"></i>Informasi Utama</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label-premium">Judul Ujian <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="input-premium form-control" placeholder="Contoh: Praktik Merakit PC, Algoritma Dasar, dll" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-premium">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="mapel" id="id_mapel" class="form-select select2" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <?php foreach($mapels as $id => $nama): ?>
                                        <option value="<?= $id ?>"><?= $nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-premium">Tujuan Pembelajaran (TP) <span class="text-danger">*</span></label>
                                <select name="tujuan_pembelajaran[]" id="tujuan_pembelajaran" class="form-select select2" multiple required data-placeholder="-- Pilih satu atau lebih TP --">
                                </select>
                                <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Pilih mapel terlebih dahulu</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label-premium">Deskripsi Singkat</label>
                                <textarea name="deskripsi" class="input-premium form-control" rows="2" placeholder="Jelaskan secara singkat tujuan ujian ini"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label-premium">Instruksi Lengkap Peserta</label>
                                <textarea name="instruksi" id="summernote" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-people text-primary me-2"></i>Peserta & Penilai</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mapping-card h-100">
                                    <label class="form-label-premium mb-3">Pilih Kelas Peserta <span class="text-danger">*</span></label>
                                    <select name="kelas[]" class="form-control select2" multiple required style="width:100%" data-placeholder="Pilih satu atau lebih kelas...">
                                        <?php foreach($kelas as $k): ?>
                                            <option value="<?= $k->id_kelas ?>"><?= $k->nama_kelas ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mapping-card h-100">
                                    <label class="form-label-premium mb-3">Assessor Tambahan (Opsional)</label>
                                    <select name="assessors[]" class="form-select select2" multiple style="width:100%" data-placeholder="Cari guru penilai...">
                                        <?php foreach($gurus as $g): ?>
                                            <option value="<?= $g->id_guru ?>"><?= $g->nama_guru ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted d-block mt-2">Biarkan kosong jika hanya Anda yang memberikan penilaian.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-gear text-primary me-2"></i>Pengaturan Ujian</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label-premium">Jenis Praktik <span class="text-danger">*</span></label>
                            <select name="jenis_praktik" class="form-select input-premium" required id="jenisPraktik">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="coding">Coding (Pemrograman)</option>
                                <option value="simulasi">Simulasi (Virtual)</option>
                                <option value="hands_on">Hands-on (Praktik Fisik)</option>
                                <option value="upload_file">Upload File/Dokumen</option>
                                <option value="mixed">Campuran (Mixed)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-premium">Format Pengumpulan</label>
                            <select name="format_pengumpulan" class="form-select input-premium" id="formatPengumpulan">
                                <option value="file">File Upload</option>
                                <option value="url">URL/Link</option>
                                <option value="teks">Teks/Essay</option>
                                <option value="kode">Kode Pemrograman</option>
                                <option value="screenshot">Screenshot</option>
                                <option value="mixed">Campuran</option>
                            </select>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <label class="form-label-premium">Bobot (%)</label>
                                <input type="number" name="bobot" class="input-premium form-control" value="100" min="1" max="100">
                            </div>
                            <div class="col-4">
                                <label class="form-label-premium">KKM / Target</label>
                                <input type="number" name="kkm" class="input-premium form-control" value="70" min="1" max="100">
                            </div>
                            <div class="col-4">
                                <label class="form-label-premium">Mode Penilaian</label>
                                <select name="kktp_mode" class="input-premium form-select">
                                    <option value="kkm">KKM (Nilai ≥ KKM)</option>
                                    <option value="kktp">KKTP (Predikat)</option>
                                </select>
                                <small class="text-muted mt-1 d-block">KKTP: M (86-100), C (66-85), L (41-65), BB (0-40)</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-premium">Durasi (Menit)</label>
                            <div class="input-group">
                                <input type="number" name="durasi_menit" class="input-premium form-control" value="0" min="0">
                                <span class="input-group-text bg-light border-start-0 px-3 fw-bold">MIN</span>
                            </div>
                            <small class="text-muted mt-1 d-block">Isi 0 untuk durasi tidak terbatas.</small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label-premium">Template Rubrik <span class="text-danger">*</span></label>
                            <select name="template_rubrik" id="template_rubrik" class="form-select select2" required>
                                <option value="">-- Pilih Template --</option>
                                <?php foreach($templates as $t): ?>
                                    <option value="<?= $t->id ?>"><?= esc($t->nama_template) ?> (<?= ucfirst($t->jenis_praktik) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($templates)): ?>
                                <div class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3">
                                    <p class="small text-muted mb-2"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>Belum ada template rubrik.</p>
                                    <a href="<?= base_url('cbtpraktik/templates/create') ?>" class="btn btn-sm btn-warning w-100" target="_blank">
                                        <i class="bi bi-plus-circle me-1"></i> Buat Template
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clock text-primary me-2"></i>Jadwal Pelaksanaan</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label-premium">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="tanggal_mulai" name="tanggal_mulai" class="input-premium form-control" required min="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="mb-0">
                            <label class="form-label-premium">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="tanggal_selesai" name="tanggal_selesai" class="input-premium form-control" required min="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>
                </div>

                <div class="card card-premium shadow-sm mb-4 fade-in-up" style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%) !important; border: none;">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-light btn-action-premium w-100 fw-bold mb-3 shadow-sm" id="btnSave" style="background-color: #ffffff !important; color: #4338ca !important; border: none;">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Simpan Ujian
                        </button>
                        <a href="<?= base_url('cbtpraktik') ?>" class="btn btn-link w-100 text-white text-decoration-none small fw-semibold opacity-75 shadow-none">
                            <i class="bi bi-x-lg me-1"></i> Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Premium styles have been integrated into the content section */
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Define guruId from PHP session data
const guruId = <?= json_encode($idAuthor ?? null) ?>;

$(document).ready(function() {
    $('.select2').select2({ 
        theme: 'bootstrap-5',
        width: '100%'
    });
    
    if ($('#summernote').length) {
        $('#summernote').summernote({ height: 150 });
    }

    $('#jenisPraktik').change(function() {
        const jenis = $(this).val();
        let format = 'file';
        if (jenis === 'coding') format = 'kode';
        else if (jenis === 'simulasi') format = 'url';
        $('#formatPengumpulan').val(format);
    });

    $('#id_mapel').change(function() {
        const mapelId = $(this).val();
        const mapelText = $(this).find('option:selected').text();
        
        if (mapelId) {
            $('#display_mapel').text(mapelText);
            $('#tujuan_pembelajaran').html('<option value="">Loading...</option>');
            console.log('Loading TP for mapel:', mapelId, 'guru:', guruId);
            $.ajax({
                url: '<?= site_url("cbtpraktik/getTPByMapel") ?>',
                type: 'GET',
                data: { mapel_id: mapelId, guru_id: guruId },
                dataType: 'json',
                success: function(res) {
                    console.log('TP Response:', res);
                    let html = '';
                    if (res && Array.isArray(res) && res.length > 0) {
                        res.forEach(function(tp) {
                            html += `<option value="${tp.id_tujuan}">${tp.kode ? tp.kode + ' - ' : ''}${tp.tujuan}</option>`;
                        });
                    } else {
                        // Keep it empty or add a disabled info option if needed, 
                        // but for multiple select, usually we just leave it empty.
                    }
                    $('#tujuan_pembelajaran').html(html).trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('TP Load Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    $('#tujuan_pembelajaran').html('<option value="" disabled>Error loading TP</option>');
                }
            });
        }
    });

    $('#formCreatePraktik').submit(function(e) {
        e.preventDefault();
        const btn = $('#btnSave');
        btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.message, 'success').then(() => {
                        window.location.href = '<?= base_url('cbtpraktik') ?>';
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Ujian');
                }
            }
        });
    });
    
    // Validasi Waktu Pelaksanaan
    const tglMulai = document.getElementById('tanggal_mulai');
    const tglSelesai = document.getElementById('tanggal_selesai');
    
    tglMulai.addEventListener('change', function() {
        if (this.value) {
            tglSelesai.min = this.value;
            if (tglSelesai.value && tglSelesai.value < this.value) {
                tglSelesai.value = this.value;
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
