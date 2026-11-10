<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="app-content-header">
    <div class="container-fluid ps-3 ps-lg-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold" style="color: #0891b2;">
                    <i class="bi bi-binoculars me-2"></i>Observasi Siswa
                </h3>
                <p class="text-muted small mb-0">
                    <span class="badge bg-dark bg-opacity-75 me-1"><?= esc($praktik->judul) ?></span>
                    <span class="badge bg-info"><?= count($siswa) ?> siswa</span>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= site_url('cbtpraktik') ?>" class="btn btn-outline-secondary shadow-sm px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid px-3 px-lg-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-eye me-2"></i>Catat Observasi</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Siswa</th>
                                <th>Kelas</th>
                                <th>Aspek Observasi</th>
                                <th class="text-center">Skor</th>
                                <th>Dimensi PPP</th>
                                <th>Catatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($siswa as $s): 
                                $obs = $observasiBySiswa[$s->id_siswa] ?? null;
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold small"><?= esc($s->nama) ?></td>
                                <td class="small"><?= esc($s->nama_kelas ?? '-') ?></td>
                                <td class="small"><?= esc($obs->aspek_observasi ?? '-') ?></td>
                                <td class="text-center fw-bold"><?= $obs->skor_observasi ?? '-' ?></td>
                                <td><span class="badge" style="font-size:0.6rem; background:#7c3aed1a; color:#7c3aed; border:1px solid #7c3aed26;"><?= esc($obs->dimensi_ppp ?? '-') ?></span></td>
                                <td class="small text-muted" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= esc($obs->catatan_observasi ?? '-') ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-cyan rounded-pill px-3" onclick="observasiSiswa(<?= $s->id_siswa ?>, '<?= esc($s->nama) ?>')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Observasi -->
<div class="modal fade" id="modalObservasi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <?= form_open('cbtpraktik/simpanObservasi', ['id' => 'formObservasi']) ?>
            <input type="hidden" name="praktik_id" value="<?= $praktik->id ?>">
            <input type="hidden" name="hasil_id" id="obsHasilId">
            <input type="hidden" name="siswa_id" id="obsSiswaId">
            <div class="modal-header bg-cyan text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-eye me-2"></i>Observasi: <span id="obsNamaSiswa"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Aspek Observasi</label>
                    <input type="text" name="aspek_observasi" class="form-control" placeholder="Contoh: Kerjasama tim, Inisiatif" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Skor (0-100)</label>
                        <input type="number" name="skor_observasi" class="form-control" min="0" max="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Dimensi PPP</label>
                        <select name="dimensi_ppp" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="beriman">Beriman & Berakhlak Mulia</option>
                            <option value="berkebinekaan">Berkebinekaan Global</option>
                            <option value="gotong_royong">Gotong Royong</option>
                            <option value="mandiri">Mandiri</option>
                            <option value="bernalar_kritis">Bernalar Kritis</option>
                            <option value="kreatif">Kreatif</option>
                        </select>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Catatan Observasi</label>
                    <textarea name="catatan_observasi" class="form-control" rows="3" placeholder="Deskripsikan hasil observasi..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-4">
                <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-cyan px-4 shadow-sm">Simpan <i class="bi bi-save ms-1"></i></button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function observasiSiswa(siswaId, nama) {
    $('#obsSiswaId').val(siswaId);
    $('#obsNamaSiswa').text(nama);
    $.get('<?= site_url("cbtpraktik/getMonitorData") ?>', { praktik_id: <?= $praktik->id ?> }, function(data) {
        if (data.progress) {
            const h = data.progress.find(h => h.siswa_id == siswaId);
            if (h && h.id) $('#obsHasilId').val(h.id);
        }
    }, 'json');
    new bootstrap.Modal(document.getElementById('modalObservasi')).show();
}

$('#formObservasi').on('submit', function(e) {
    e.preventDefault();
    $.post($(this).attr('action'), $(this).serialize(), function(res) {
        if (res.status) {
            showSuccessToast(res.message || 'Observasi berhasil disimpan');
            setTimeout(() => location.reload(), 1000);
        } else {
            showDangerToast(res.message || 'Gagal');
        }
    }, 'json');
});
</script>
<?= $this->endSection() ?>
