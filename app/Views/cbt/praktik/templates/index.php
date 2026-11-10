<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-list-check text-primary me-2"></i>
                   Template Rubrik
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Template Rubrik</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Debug Info -->
        <?php if (isset($debug_templates_count)): ?>
        <div class="alert alert-info">
            <strong>Debug:</strong> isAdmin=<?= $debug_is_admin ? 'true' : 'false' ?>, 
            UserID=<?= $debug_user_id ?>, 
            Templates Count=<?= $debug_templates_count ?>
        </div>
        <?php endif; ?>
        
        <div class="card card-primary card-outline shadow-sm fade-in-up">
            <div class="card-header border-0 shadow-none py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="card-title fw-bold">
                            <i class="bi bi-grid-3x3-gap me-2"></i>Daftar Template Penilaian
                        </h3>
                    </div>
                    <div class="col-auto d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-light border-start-0" id="searchTemplate" placeholder="Cari template...">
                        </div>
                        <a href="<?= site_url('cbtpraktik/templates/create') ?>" class="btn btn-primary btn-sm shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Buat Template
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100 dataTable mb-0" id="tableTemplates">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" width="60">No</th>
                                <th>Nama Template</th>
                                <th class="text-center">Jenis Praktik</th>
                                <th class="text-center">Dibuat Oleh</th>
                                <th class="text-center">Akses</th>
                                <th class="text-center" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    Belum ada template rubrik yang tersedia.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($templates as $i => $t): ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= esc($t->nama_template) ?></div>
                                    <small class="text-muted"><?= esc($t->deskripsi ?: 'Tidak ada deskripsi') ?></small>
                                    <div class="mt-1">
                                        <?php if ($t->item_count > 0): ?>
                                            <span class="badge bg-info text-white"><i class="bi bi-list-check me-1"></i><?= $t->item_count ?> Kriteria</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Belum ada kriteria</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        <?= strtoupper($t->jenis_praktik) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small class="d-block fw-bold"><?= esc($t->nama_guru ?: 'System/Admin') ?></small>
                                    <small class="text-muted"><?= date('d M Y', strtotime($t->created_at)) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge-action-status <?= $t->is_public ? '' : 'inactive' ?>">
                                        <?= $t->is_public ? 'Publik' : 'Privat' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-action-group justify-content-center">
                                        <button type="button" class="btn btn-action btn-action-magic" onclick="viewDetail(<?= $t->id ?>)" data-bs-toggle="tooltip" title="Lihat Detail Template">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <?php if (isset($isAdmin) && ($isAdmin || $t->created_by == $user->id)): ?>
                                        <a href="<?= site_url('cbtpraktik/templates/edit/' . $t->id) ?>" class="btn btn-action btn-action-edit" data-bs-toggle="tooltip" title="Edit Template">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <button type="button" class="btn btn-action btn-action-delete" onclick="deleteTemplate(<?= $t->id ?>, '<?= esc($t->nama_template) ?>')" data-bs-toggle="tooltip" title="Hapus Template">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                        <?php endif; ?>
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

<!-- Modal Detail Template -->
<div class="modal fade" id="modalDetailTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-info-circle text-info me-2"></i>Detail Template Rubrik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3 pb-4" id="modalDetailContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 bg-light" style="border-radius: 0 0 1rem 1rem;">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.fade-in-up { animation: fadeInUp 0.4s ease-out both; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    $('#searchTemplate').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#tableTemplates tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function deleteTemplate(id, name) {
    console.log('Delete clicked for ID:', id, 'Name:', name);
    Swal.fire({
        title: 'Hapus Template?',
        text: "Anda akan menghapus template '" + name + "'. Tindakan ini tidak dapat dibatalkan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            var deleteUrl = '<?= site_url('cbtpraktik/templates/delete/') ?>' + id;
            console.log('Sending delete request to:', deleteUrl);
            $.ajax({
                url: deleteUrl,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    console.log('Delete response:', res);
                    if (res.status) {
                        Swal.fire('Terhapus!', res.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete AJAX error:', xhr, status, error);
                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus: ' + error, 'error');
                }
            });
        }
    });
}

function viewDetail(id) {
    $('#modalDetailContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Memuat data...</p>
        </div>
    `);
    $('#modalDetailTemplate').modal('show');
    
    $.ajax({
        url: '<?= site_url('cbtpraktik/templates_detail/') ?>' + id,
        type: 'GET',
        success: function(html) {
            if(html && typeof html === 'object' && html.status === false) {
                $('#modalDetailContent').html('<div class="alert alert-danger">' + html.message + '</div>');
            } else {
                $('#modalDetailContent').html(html);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#modalDetailContent').html('<div class="alert alert-danger">Gagal memuat data detail. Terjadi kesalahan server.</div>');
        }
    });
}
</script>
<?= $this->endSection() ?>
