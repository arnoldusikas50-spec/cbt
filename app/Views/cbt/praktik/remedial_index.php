<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="app-content-header">
    <div class="container-fluid ps-3 ps-lg-4">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold" style="color: #dc2626;">
                    <i class="bi bi-arrow-repeat me-2"></i>Remedial Terstruktur
                </h3>
                <p class="text-muted small mb-0">
                    <span class="badge bg-dark bg-opacity-75 me-1"><?= esc($praktik->judul) ?></span>
                    <span class="badge bg-<?= $totalRemedial > 0 ? 'danger' : 'success' ?>">
                        <?= $totalRemedial ?> siswa remedial
                    </span>
                </p>
            </div>
            <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                <a href="<?= site_url('cbtpraktik/analisisRemedial/' . $praktik->id) ?>" class="btn btn-outline-primary shadow-sm px-3 rounded-pill me-1">
                    <i class="bi bi-graph-up me-1"></i> Analisis
                </a>
                <a href="<?= site_url('cbtpraktik') ?>" class="btn btn-outline-secondary shadow-sm px-3 rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid px-3 px-lg-4">
        <?php if (empty($remedialList)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle display-1 text-success opacity-25"></i>
                <h5 class="mt-3 text-muted">Tidak ada remedial</h5>
                <p class="text-muted small">Semua siswa telah mencapai target ketuntasan.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Daftar Remedial</h6>
                    </div>
                    <div class="col-auto">
                        <select id="filterStatus" class="form-select form-select-sm border rounded-pill" style="width:auto;">
                            <option value="">Semua Status</option>
                            <option value="dijadwalkan">Dijadwalkan</option>
                            <option value="dikerjakan">Dikerjakan</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Siswa</th>
                                <th>NIS</th>
                                <th>Nilai Awal</th>
                                <th>Status</th>
                                <th>Jenis</th>
                                <th>Tgl Remedial</th>
                                <th>Nilai Remedial</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($remedialList as $r): 
                                $statusWarna = match($r->status_remedial) {
                                    'selesai' => 'success',
                                    'dikerjakan' => 'info',
                                    'dijadwalkan' => 'warning',
                                    'dibatalkan' => 'secondary',
                                    default => 'secondary'
                                };
                            ?>
                            <tr class="remedial-row" data-status="<?= $r->status_remedial ?>">
                                <td class="ps-4 fw-bold small"><?= esc($r->nama_siswa) ?></td>
                                <td class="small"><?= esc($r->nis) ?></td>
                                <td class="text-center fw-bold text-danger"><?= $r->nilai_awal ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusWarna ?> bg-opacity-10 text-<?= $statusWarna ?> border border-<?= $statusWarna ?> border-opacity-10 rounded-pill">
                                        <?= $r->status_remedial ?>
                                    </span>
                                </td>
                                <td class="small"><?= esc($r->jenis_remedial ?? '-') ?></td>
                                <td class="small"><?= $r->tanggal_remedial ? date('d/m/Y', strtotime($r->tanggal_remedial)) : '-' ?></td>
                                <td class="text-center fw-bold <?= ($r->nilai_remedial ?? 0) >= 66 ? 'text-success' : 'text-muted' ?>">
                                    <?= $r->nilai_remedial ?? '-' ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="editRemedial(<?= $r->id ?>)">
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
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Remedial -->
<div class="modal fade" id="modalRemedial" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <?= form_open('cbtpraktik/updateRemedial', ['id' => 'formRemedial']) ?>
            <input type="hidden" name="id" id="remedialId">
            <div class="modal-header bg-danger text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Update Remedial</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status_remedial" class="form-control">
                        <option value="dijadwalkan">Dijadwalkan</option>
                        <option value="dikerjakan">Sedang Dikerjakan</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nilai Remedial</label>
                    <input type="number" name="nilai_remedial" class="form-control" min="0" max="100" step="0.01">
                    <small class="text-muted">KKTP: M(86-100) C(66-85) L(41-65) BB(0-40). Isi hanya jika sudah selesai.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Remedial</label>
                    <input type="date" name="tanggal_remedial" class="form-control">
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold">Catatan</label>
                    <textarea name="catatan_remedial" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 rounded-bottom-4">
                <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger px-4 shadow-sm">Simpan <i class="bi bi-save ms-1"></i></button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$('#filterStatus').on('change', function() {
    const val = $(this).val();
    $('.remedial-row').each(function() {
        if (!val || $(this).data('status') === val) $(this).show();
        else $(this).hide();
    });
});

function editRemedial(id) {
    $('#remedialId').val(id);
    // Load data via AJAX
    $.get('<?= site_url("cbtpraktik/getAuditTrail") ?>/' + id, function(res) {
        // Fallback: just show modal and let user fill
    });
    new bootstrap.Modal(document.getElementById('modalRemedial')).show();
}

$('#formRemedial').on('submit', function(e) {
    e.preventDefault();
    $.post($(this).attr('action'), $(this).serialize(), function(res) {
        if (res.status) {
            showSuccessToast(res.message || 'Remedial berhasil diupdate');
            setTimeout(() => location.reload(), 1000);
        } else {
            showDangerToast(res.message || 'Gagal');
        }
    }, 'json');
});
</script>
<?= $this->endSection() ?>
