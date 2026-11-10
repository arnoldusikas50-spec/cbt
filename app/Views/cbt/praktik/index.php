<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header (Page Header) -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-laptop text-primary me-2"></i>
                    <?= esc($judul ?? 'Ujian Praktik') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Daftar Ujian Praktik') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        


        <!-- Main Card Container -->
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary"><i class="bi bi-list-ul me-2"></i>Daftar Ujian Praktik</h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-1">
                    <a href="<?= base_url('cbtpraktik/templates') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-grid-3x3-gap me-1"></i> Template Rubrik
                    </a>
                    <button type="button" class="btn btn-sm btn-warning" onclick="location.reload()">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <a href="<?= base_url('cbtpraktik/create') ?>" class="btn btn-sm btn-primary shadow-sm">
                        <i class="bi bi-plus me-1"></i> Buat Praktik Baru
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablePraktik" class="w-100 table table-striped table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th class="text-center align-middle" width="50">No</th>
                                <th class="align-middle">Judul & Mapel</th>
                                <th class="text-center align-middle">Kelas Target</th>
                                <th class="text-center align-middle">Jenis Praktik</th>
                                <th class="align-middle">Jadwal Pelaksanaan</th>
                                <th class="text-center align-middle">Status</th>
                                <th class="text-center align-middle" width="220">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($praktik)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox display-4 d-block mb-3 text-muted"></i>
                                        <h6 class="fw-bold text-dark mb-1">Belum Ada Data Ujian Praktik</h6>
                                        <p class="small text-muted mb-0">Klik tombol <strong>Buat Praktik Baru</strong> di atas untuk membuat ujian praktik.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($praktik as $k => $row): ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted font-monospace"><?= $k + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($row->judul) ?></div>
                                        <div class="mt-1">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border me-1">
                                                <i class="bi bi-book me-1"></i><?= esc($row->nama_mapel ?? $mapels[$row->mapel_id] ?? '-') ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $kelasIds = json_decode($row->kelas_id ?? '[]', true);
                                        if (is_array($kelasIds) && !empty($kelasIds)):
                                            foreach ($kelasIds as $kid):
                                                if (isset($kelas[$kid])):
                                        ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border me-1"><?= esc($kelas[$kid]->nama_kelas ?? $kid) ?></span>
                                        <?php 
                                                endif;
                                            endforeach;
                                        else:
                                            echo '<span class="text-muted small">—</span>';
                                        endif;
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $badgeClass = 'bg-warning text-dark';
                                        $icon = 'bi-hammer';
                                        if ($row->jenis_praktik === 'coding') { $badgeClass = 'bg-primary'; $icon = 'bi-code-slash'; }
                                        elseif ($row->jenis_praktik === 'simulasi') { $badgeClass = 'bg-success'; $icon = 'bi-display'; }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> bg-opacity-10 border border-primary-subtle px-3 py-1.5 fw-bold" style="font-size: 0.75rem;">
                                            <i class="bi <?= $icon ?> me-1"></i><?= esc(ucfirst($row->jenis_praktik)) ?>
                                        </span>
                                    </td>
                                    <td class="small">
                                        <div class="text-muted">
                                            <i class="bi bi-calendar-event me-1 text-primary"></i>
                                            <?= date('d M Y, H:i', strtotime($row->tanggal_mulai)) ?>
                                        </div>
                                        <div class="text-muted mt-0.5">
                                            <i class="bi bi-flag me-1 text-danger"></i>
                                            <?= date('d M Y, H:i', strtotime($row->tanggal_selesai)) ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-action-status <?= $row->status === 'aktif' ? '' : 'inactive' ?>">
                                            <?= esc(ucfirst($row->status)) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-action-group justify-content-center">
                                            <a href="<?= base_url('cbtpraktik/nilaiManual/' . $row->id) ?>" class="btn btn-action btn-action-status" data-bs-toggle="tooltip" title="Input Nilai Manual">
                                                <i class="bi bi-clipboard-check-fill"></i>
                                            </a>
                                            <a href="<?= base_url('cbtpraktik/penilaian/' . $row->id) ?>" class="btn btn-action btn-action-magic" data-bs-toggle="tooltip" title="Penilaian Rubrik">
                                                <i class="bi bi-check2-circle"></i>
                                            </a>
                                            <a href="<?= base_url('cbtpraktik/analisisRemedial/' . $row->id) ?>" class="btn btn-action btn-action-warning" data-bs-toggle="tooltip" title="Analisis Remedial & Rekomendasi">
                                                <i class="bi bi-graph-up-arrow"></i>
                                            </a>
                                            <a href="<?= base_url('cbtpraktik/monitor/' . $row->id) ?>" class="btn btn-action btn-action-magic" data-bs-toggle="tooltip" title="Monitoring">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="<?= base_url('cbtpraktik/edit/' . $row->id) ?>" class="btn btn-action btn-action-edit" data-bs-toggle="tooltip" title="Edit Ujian">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <button type="button" class="btn btn-action btn-action-delete btn-delete" 
                                                    data-id="<?= $row->id ?>" 
                                                    data-judul="<?= esc($row->judul) ?>"
                                                    data-bs-toggle="tooltip" title="Hapus Ujian">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
$(document).ready(function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var judul = $(this).data('judul');
        
        Swal.fire({
            title: 'Hapus Ujian Praktik?',
            text: "Anda akan menghapus '" + judul + "'. Data nilai yang sudah masuk akan ikut terhapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('cbtpraktik/delete/') ?>/' + id;
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
