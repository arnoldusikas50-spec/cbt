<?php if (!isset($template) || !$template): ?>
    <div class="alert alert-danger">Data template rubrik tidak ditemukan.</div>
<?php else: ?>
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold text-primary mb-1"><?= esc($template->nama_template) ?></h5>
            <p class="text-muted small mb-3"><?= esc($template->deskripsi ?: 'Tidak ada deskripsi') ?></p>
            
            <div class="d-flex gap-3 flex-wrap">
                <span class="badge bg-light text-dark border"><i class="bi bi-tag me-1"></i><?= strtoupper($template->jenis_praktik) ?></span>
                <?php if ($template->is_public): ?>
                    <span class="badge bg-success"><i class="bi bi-globe me-1"></i>Publik</span>
                <?php else: ?>
                    <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Privat</span>
                <?php endif; ?>
                <span class="badge bg-info text-dark"><i class="bi bi-card-checklist me-1"></i><?= count($template->items) ?> Kriteria</span>
            </div>
        </div>
    </div>

    <h6 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-list-stars text-warning me-2"></i>Kriteria Penilaian</h6>
    
    <?php if (empty($template->items)): ?>
        <div class="text-center py-4 text-muted border rounded bg-light">
            <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
            Belum ada kriteria penilaian.
        </div>
    <?php else: ?>
        <div class="accordion" id="accordionKriteria">
            <?php foreach ($template->items as $index => $item): ?>
                <div class="accordion-item mb-2 border rounded shadow-sm">
                    <h2 class="accordion-header" id="heading<?= $item->id ?>">
                        <button class="accordion-button collapsed py-2 px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#collapse<?= $item->id ?>" aria-expanded="false" aria-controls="collapse<?= $item->id ?>" style="background-color: #f8fafc;">
                            <span class="badge bg-primary me-2"><?= $item->kode_kriteria ?></span>
                            <?= esc($item->nama_kriteria) ?>
                            <span class="ms-auto badge bg-warning text-dark me-3">Bobot: <?= $item->bobot_persen ?>%</span>
                        </button>
                    </h2>
                    <div id="collapse<?= $item->id ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $item->id ?>" data-bs-parent="#accordionKriteria">
                        <div class="accordion-body p-3 bg-white">
                            <?php if ($item->deskripsi): ?>
                                <p class="small text-muted mb-3"><i class="bi bi-info-circle me-1"></i><?= esc($item->deskripsi) ?></p>
                            <?php endif; ?>
                            
                            <?php 
                                $levels = json_decode($item->level_detail ?? '[]', true); 
                                if (!empty($levels)):
                            ?>
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="80" class="text-center small">Skor</th>
                                            <th class="small">Keterangan Tingkat Penilaian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($levels as $lvl): ?>
                                            <tr>
                                                <td class="text-center align-middle fw-bold small">
                                                    <?= $lvl['nilai_min'] ?> - <?= $lvl['nilai_max'] ?>
                                                </td>
                                                <td class="small"><?= esc($lvl['nama_level']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-muted small italic">Tidak ada rincian tingkatan skor.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
