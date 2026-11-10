<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="app-content-header">
    <div class="container-fluid ps-3 ps-lg-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold" style="color: #7c3aed;">
                    <i class="bi bi-stars me-2"></i>Projek P5
                </h3>
                <p class="text-muted small mb-0">
                    <span class="badge bg-dark bg-opacity-75 me-1"><?= esc($praktik->judul) ?></span>
                    <span class="badge bg-secondary"><?= esc($praktik->nama_mapel ?? '-') ?></span>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <button class="btn btn-primary shadow-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalP5">
                    <i class="bi bi-plus-lg me-1"></i> Buat Projek P5
                </button>
                <a href="<?= site_url('cbtpraktik') ?>" class="btn btn-outline-secondary shadow-sm px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid px-3 px-lg-4">
        <?php if (empty($projekList)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-stars display-1 text-muted opacity-25"></i>
                <h5 class="mt-3 text-muted">Belum ada Projek P5</h5>
                <p class="text-muted small">Buat projek penguatan profil pelajar pancasila untuk ujian praktik ini.</p>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalP5">
                    <i class="bi bi-plus-lg me-1"></i> Buat Projek Baru
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-3 mb-4">
            <?php foreach ($projekList as $projek): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0"><?= esc($projek->judul_projek) ?></h6>
                            <span class="badge bg-<?= $projek->status === 'aktif' ? 'success' : ($projek->status === 'selesai' ? 'info' : 'secondary') ?> rounded-pill">
                                <?= $projek->status ?>
                            </span>
                        </div>
                        <p class="small text-muted mb-2"><?= esc(substr($projek->deskripsi_projek ?? '-', 0, 100)) ?></p>
                        <div class="mb-2">
                            <span class="badge" style="font-size:0.6rem; background: #7c3aed1a; color:#7c3aed; border:1px solid #7c3aed26;">
                                <?= esc($dimensiPpp[$projek->dimensi_ppp] ?? $projek->dimensi_ppp ?? '-') ?>
                            </span>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="nilaiP5(<?= $projek->id ?>, '<?= esc($projek->judul_projek) ?>')">
                                <i class="bi bi-pencil-square me-1"></i> Nilai
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($nilaiList)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>Hasil Penilaian P5</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Siswa</th>
                                <th>Kelas</th>
                                <th>Projek</th>
                                <th class="text-center">Nilai</th>
                                <th class="text-center">Predikat</th>
                                <th>Dimensi PPP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nilaiList as $n): ?>
                            <tr>
                                <td class="ps-4 fw-bold small"><?= esc($n->nama_siswa ?? '-') ?></td>
                                <td class="small"><?= esc($n->nama_kelas ?? '-') ?></td>
                                <td class="small"><?= esc($n->judul_projek ?? '-') ?></td>
                                <td class="text-center fw-bold"><?= $n->nilai_akhir_p5 ?? '-' ?></td>
                                <td class="text-center">
                                    <?php $p = $n->predikat_p5 ?? ''; $warna = $p === 'Mahir' ? 'success' : ($p === 'Cakap' ? 'info' : ($p === 'Layak' ? 'warning' : 'danger')); ?>
                                    <span class="badge bg-<?= $warna ?> bg-opacity-10 text-<?= $warna ?> border border-<?= $warna ?> border-opacity-10">
                                        <?= $p ?>
                                    </span>
                                </td>
                                <td class="small"><?= esc($n->dimensi_ppp_tercapai ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Buat/Edit P5 -->
<div class="modal fade" id="modalP5" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <?= form_open('cbtpraktik/p5Save', ['id' => 'formP5']) ?>
            <input type="hidden" name="id" id="p5Id">
            <input type="hidden" name="praktik_id" value="<?= $praktik->id ?>">
            <div class="modal-header text-white border-0 rounded-top-4" style="background:#7c3aed;">
                <h5 class="modal-title fw-bold"><i class="bi bi-stars me-2"></i>Projek P5</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Projek</label>
                    <input type="text" name="judul_projek" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi</label>
                    <textarea name="deskripsi_projek" class="form-control" rows="3"></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Dimensi PPP</label>
                        <select name="dimensi_ppp" class="form-control" required>
                            <option value="">-- Pilih Dimensi --</option>
                            <?php foreach ($dimensiPpp as $key => $val): ?>
                            <option value="<?= $key ?>"><?= esc($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Elemen</label>
                        <input type="text" name="elemen_ppp" class="form-control" placeholder="Contoh: Akhlak kepada alam">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Sub-elemen (JSON)</label>
                    <textarea name="sub_elemen_ppp" class="form-control" rows="2" placeholder='["Sub-elemen 1", "Sub-elemen 2"]'></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Rubrik P5 (JSON)</label>
                    <textarea name="rubrik_p5" class="form-control" rows="2" placeholder='[{"kriteria":"...", "BB":"...", "L":"...", "C":"...", "M":"..."}]'></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Draft</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-4">
                <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan <i class="bi bi-save ms-1"></i></button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal Nilai P5 -->
<div class="modal fade" id="modalNilaiP5" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <?= form_open('cbtpraktik/p5SimpanNilai', ['id' => 'formNilaiP5']) ?>
            <input type="hidden" name="p5_id" id="nilaiP5Id">
            <input type="hidden" name="praktik_id" value="<?= $praktik->id ?>">
            <div class="modal-header text-white border-0 rounded-top-4" style="background:#7c3aed;">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Nilai P5: <span id="judulProjekNilai"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Siswa</label>
                    <select name="siswa_id" class="form-control" id="siswaSelect" required>
                        <option value="">-- Pilih Siswa --</option>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nilai Observasi</label>
                        <input type="number" name="nilai_observasi" class="form-control" min="0" max="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nilai Akhir P5</label>
                        <input type="number" name="nilai_akhir_p5" class="form-control" min="0" max="100" required>
                        <small class="text-muted">KKTP: M(86-100) C(66-85) L(41-65) BB(0-40)</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Dimensi PPP Tercapai</label>
                    <select name="dimensi_ppp_tercapai" class="form-control">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($dimensiPpp as $key => $val): ?>
                        <option value="<?= $key ?>"><?= esc($val) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Observasi</label>
                    <textarea name="catatan_observasi" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Refleksi Siswa</label>
                    <textarea name="refleksi_siswa" class="form-control" rows="2" placeholder="Isian dari siswa tentang pengalaman P5"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-4">
                <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success px-4 shadow-sm">Simpan Nilai <i class="bi bi-check-circle ms-1"></i></button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var siswaData = <?= json_encode(array_map(function($s) {
    return ['id_siswa' => $s->id_siswa, 'nama' => $s->nama, 'nis' => $s->nis];
}, $siswaList)) ?>;

function nilaiP5(id, judul) {
    $('#nilaiP5Id').val(id);
    $('#judulProjekNilai').text(judul);

    var $select = $('#siswaSelect');
    $select.empty().append('<option value="">-- Pilih Siswa --</option>');
    siswaData.forEach(function(s) {
        $select.append('<option value="' + s.id_siswa + '">' + s.nama + ' (' + s.nis + ')</option>');
    });

    new bootstrap.Modal(document.getElementById('modalNilaiP5')).show();
}

$('#formP5').on('submit', function(e) {
    e.preventDefault();
    $.post($(this).attr('action'), $(this).serialize(), function(res) {
        if (res.status) {
            showSuccessToast('Projek P5 berhasil disimpan');
            setTimeout(() => location.reload(), 1000);
        } else {
            showDangerToast(res.message || 'Gagal menyimpan');
        }
    }, 'json');
});

$('#formNilaiP5').on('submit', function(e) {
    e.preventDefault();
    $.post($(this).attr('action'), $(this).serialize(), function(res) {
        if (res.status) {
            showSuccessToast('Nilai P5 berhasil disimpan');
            setTimeout(() => location.reload(), 1000);
        } else {
            showDangerToast(res.message || 'Gagal menyimpan');
        }
    }, 'json');
});
</script>
<?= $this->endSection() ?>
