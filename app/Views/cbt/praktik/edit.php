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
                    <i class="bi bi-pencil-square text-warning me-2"></i>
                    <?= esc($judul ?? 'Edit Ujian Praktik') ?>
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
        <?= form_open('cbtpraktik/update/' . $praktik->id, ['id' => 'formEditPraktik']) ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-journal-check text-warning me-2"></i>Ubah Informasi Utama</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label-premium">Judul Ujian <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="input-premium form-control" value="<?= esc($praktik->judul) ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-premium">Mata Pelajaran <span class="text-danger">*</span></label>
                                <select name="mapel" id="id_mapel" class="form-select select2" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <?php foreach($mapels as $id => $nama): ?>
                                        <option value="<?= $id ?>" <?= ($praktik->mapel_id ?? '') == $id ? 'selected' : '' ?>><?= $nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-premium">Tujuan Pembelajaran (TP) <span class="text-danger">*</span></label>
                                <select name="tujuan_pembelajaran[]" id="tujuan_pembelajaran" class="form-select select2" multiple required data-placeholder="-- Pilih satu atau lebih TP --">
                                </select>
                                <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Pilih mapel untuk memuat daftar TP</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label-premium">Deskripsi Singkat</label>
                                <textarea name="deskripsi" class="input-premium form-control" rows="2" placeholder="Jelaskan secara singkat tujuan ujian ini"><?= esc($praktik->deskripsi ?? '') ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label-premium">Instruksi Lengkap Peserta</label>
                                <textarea name="instruksi" id="summernote" class="form-control"><?= $praktik->instruksi ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-people text-warning me-2"></i>Peserta & Penilai</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mapping-card h-100">
                                    <label class="form-label-premium mb-3">Pilih Kelas Peserta <span class="text-danger">*</span></label>
                                    <select name="kelas[]" class="form-control select2" multiple required style="width:100%" data-placeholder="Pilih kelas...">
                                        <?php 
                                        $selectedKelas = json_decode($praktik->kelas_id ?? '[]', true);
                                        if (!is_array($selectedKelas)) $selectedKelas = [];
                                        foreach($kelas as $k): 
                                        ?>
                                            <option value="<?= $k->id_kelas ?>" <?= in_array($k->id_kelas, $selectedKelas) ? 'selected' : '' ?>><?= $k->nama_kelas ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mapping-card h-100">
                                    <label class="form-label-premium mb-3">Assessor Tambahan (Opsional)</label>
                                    <select name="assessors[]" class="form-select select2" multiple style="width:100%" data-placeholder="Cari guru penilai...">
                                        <?php 
                                        $selectedAssessors = json_decode($praktik->assessors ?? '[]', true);
                                        if (!is_array($selectedAssessors)) $selectedAssessors = [];
                                        foreach($gurus as $g): 
                                        ?>
                                            <option value="<?= $g->id_guru ?>" <?= in_array($g->id_guru, $selectedAssessors) ? 'selected' : '' ?>><?= $g->nama_guru ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-gear text-warning me-2"></i>Pengaturan Ujian</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label-premium">Jenis Praktik <span class="text-danger">*</span></label>
                            <select name="jenis_praktik" class="form-select input-premium" required id="jenisPraktik">
                                <option value="coding" <?= $praktik->jenis_praktik === 'coding' ? 'selected' : '' ?>>Coding (Pemrograman)</option>
                                <option value="simulasi" <?= $praktik->jenis_praktik === 'simulasi' ? 'selected' : '' ?>>Simulasi (Virtual)</option>
                                <option value="hands_on" <?= $praktik->jenis_praktik === 'hands_on' ? 'selected' : '' ?>>Hands-on (Praktik Fisik)</option>
                                <option value="upload_file" <?= $praktik->jenis_praktik === 'upload_file' ? 'selected' : '' ?>>Upload File/Dokumen</option>
                                <option value="mixed" <?= $praktik->jenis_praktik === 'mixed' ? 'selected' : '' ?>>Campuran (Mixed)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-premium">Format Pengumpulan</label>
                            <select name="format_pengumpulan" class="form-select input-premium">
                                <option value="file" <?= ($praktik->format_pengumpulan ?? 'file') === 'file' ? 'selected' : '' ?>>File Upload</option>
                                <option value="url" <?= ($praktik->format_pengumpulan ?? '') === 'url' ? 'selected' : '' ?>>URL/Link</option>
                                <option value="teks" <?= ($praktik->format_pengumpulan ?? '') === 'teks' ? 'selected' : '' ?>>Teks/Essay</option>
                                <option value="kode" <?= ($praktik->format_pengumpulan ?? '') === 'kode' ? 'selected' : '' ?>>Kode Pemrograman</option>
                            </select>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <label class="form-label-premium">Bobot (%)</label>
                                <input type="number" name="bobot" class="input-premium form-control" value="<?= $praktik->bobot ?>" min="1" max="100">
                            </div>
                            <div class="col-4">
                                <label class="form-label-premium">KKM / Target</label>
                                <input type="number" name="kkm" class="input-premium form-control" value="<?= $praktik->kkm ?>" min="1" max="100">
                            </div>
                            <div class="col-4">
                                <label class="form-label-premium">Mode Penilaian</label>
                                <select name="kktp_mode" class="input-premium form-select">
                                    <option value="kkm" <?= ($praktik->kktp_mode ?? 'kkm') === 'kkm' ? 'selected' : '' ?>>KKM (Nilai ≥ KKM)</option>
                                    <option value="kktp" <?= ($praktik->kktp_mode ?? '') === 'kktp' ? 'selected' : '' ?>>KKTP (Predikat M/C/L/BB)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label-premium">Durasi (Menit)</label>
                            <div class="input-group">
                                <input type="number" name="durasi_menit" class="input-premium form-control" value="<?= $praktik->durasi_menit ?>" min="0">
                                <span class="input-group-text bg-light border-start-0 px-3 fw-bold">MIN</span>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label-premium">Template Rubrik <span class="text-danger">*</span></label>
                            <select name="template_rubrik" id="template_rubrik" class="form-select select2" required>
                                <option value="">-- Pilih Template --</option>
                                <?php foreach($templates as $t): ?>
                                    <option value="<?= $t->id ?>" <?= ($praktik->template_id ?? '') == $t->id ? 'selected' : '' ?>><?= esc($t->nama_template) ?> (<?= ucfirst($t->jenis_praktik) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="apply_template" id="applyTemplate" value="1" style="cursor: pointer;">
                                    <label class="form-check-label small fw-bold text-white" for="applyTemplate" style="cursor: pointer;">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Terapkan ulang template
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Mencentang ini akan menghapus kriteria rubrik yang sudah ada dan menggantinya dengan yang baru dari template.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-premium shadow-sm mb-4 fade-in-up">
                    <div class="card-header-premium">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clock text-warning me-2"></i>Jadwal Pelaksanaan</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label-premium">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="tanggal_mulai" name="tanggal_mulai" class="input-premium form-control" value="<?= date('Y-m-d\TH:i', strtotime($praktik->tanggal_mulai)) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label-premium">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="tanggal_selesai" name="tanggal_selesai" class="input-premium form-control" value="<?= date('Y-m-d\TH:i', strtotime($praktik->tanggal_selesai)) ?>" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label-premium">Status Ujian</label>
                            <select name="status" class="form-select input-premium">
                                <option value="draft" <?= $praktik->status === 'draft' ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                                <option value="aktif" <?= $praktik->status === 'aktif' ? 'selected' : '' ?>>Aktif (Berlangsung)</option>
                                <option value="selesai" <?= $praktik->status === 'selesai' ? 'selected' : '' ?>>Selesai (Arsip)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card card-premium shadow-sm mb-4 fade-in-up" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; border: none;">
                    <div class="card-body p-4 text-center">
                        <button type="submit" class="btn btn-light btn-action-premium w-100 fw-bold mb-3 shadow-sm" id="btnUpdate" style="background-color: #ffffff !important; color: #92400e !important; border: none;">
                            <i class="bi bi-check-circle-fill me-2"></i> Perbarui Ujian
                        </button>
                        <a href="<?= base_url('cbtpraktik') ?>" class="btn btn-link text-white text-decoration-none small fw-semibold opacity-90 shadow-none">
                            <i class="bi bi-x-lg me-1"></i> Batalkan Perubahan
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?= form_close() ?>
    </div>
</div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .fade-in-up { animation: fadeInUp 0.4s ease-out both; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    .mapping-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #f8fafc;
        padding: 0.75rem 1rem;
        transition: all 0.2s;
    }
    .mapping-card:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.05);
    }
    .select2-container--bootstrap-5 .select2-selection--multiple {
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.5rem !important;
        background-color: #ffffff !important;
        min-height: 38px !important;
        padding: 2px 6px !important;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection--multiple {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1) !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #e0e7ff !important;
        border: none !important;
        color: #4338ca !important;
        border-radius: 4px !important;
        padding: 0px 8px !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
        margin-top: 4px !important;
        margin-bottom: 0px !important;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: #4338ca !important;
        margin-right: 3px !important;
        font-size: 0.7rem !important;
    }
    .badge-premium {
        background: #6366f1;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.select2').select2({ 
            theme: 'bootstrap-5',
            width: '100%'
        });
        if($('#summernote').length) {
            $('#summernote').summernote({ height: 150 });
        }

        function fetchTP(mapelId, selectedTP = null) {
            if (mapelId) {
                $('#tujuan_pembelajaran').html('<option value="">Loading...</option>');
                
                // Parse selectedTP if it's a JSON string
                let selectedArray = [];
                if (selectedTP) {
                    try {
                        selectedArray = JSON.parse(selectedTP);
                        if (!Array.isArray(selectedArray)) {
                            selectedArray = [selectedTP];
                        }
                    } catch (e) {
                        selectedArray = [selectedTP];
                    }
                }

                $.ajax({
                    url: '<?= site_url("cbtpraktik/getTPByMapel") ?>',
                    type: 'GET',
                    data: { mapel_id: mapelId, guru_id: '<?= $praktik->created_by ?>' },
                    dataType: 'json',
                    success: function(res) {
                        let html = '';
                        res.forEach(function(tp) {
                            const isSelected = selectedArray.includes(tp.id_tujuan.toString()) || selectedArray.includes(parseInt(tp.id_tujuan)) ? 'selected' : '';
                            html += `<option value="${tp.id_tujuan}" ${isSelected}>${tp.kode ? tp.kode + ' - ' : ''}${tp.tujuan}</option>`;
                        });
                        $('#tujuan_pembelajaran').html(html).trigger('change');
                    }
                });
            }
        }

        $('#id_mapel').change(function() {
            const mapelId = $(this).val();
            const mapelText = $(this).find('option:selected').text();
            
            if (mapelId) {
                $('#display_mapel').text(mapelText);
                fetchTP(mapelId);
            }
        });

        // Initial fetch
        const initialMapel = $('#id_mapel').val();
        const initialTP = `<?= $praktik->tujuan_pembelajaran ?? "" ?>`;
        if (initialMapel) {
            fetchTP(initialMapel, initialTP);
        }

        $('#formEditPraktik').submit(function(e) {
            e.preventDefault();
            var btn = $('#btnUpdate');
            btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Menyimpan...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if(res.status) {
                        Swal.fire('Berhasil', res.message, 'success').then(() => {
                            window.location.href = '<?= base_url('cbtpraktik') ?>';
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Update Praktik');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Update Praktik');
                }
        });
    });

    // Validasi Waktu Pelaksanaan
    const tglMulai = document.getElementById('tanggal_mulai');
    const tglSelesai = document.getElementById('tanggal_selesai');
    
    tglMulai.addEventListener('input', function() {
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
