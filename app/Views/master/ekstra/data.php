<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="card card-default my-shadow mb-4">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-star me-2"></i><?= esc($judul ?? 'Data Ekstrakurikuler') ?></h6>
                <div class="card-tools ms-auto">
                    <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#createEkstraModal"
                        class="btn btn-sm btn-primary shadow-sm ms-1">
                        <i class="bi bi-plus me-1"></i> Tambah Ekskul
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php
                $totalEkskul = is_array($ekskul) ? count($ekskul) : 0;
                $totalKelas = is_array($kelas) ? count($kelas) : 0;
                ?>
                <div class="mb-3">
                    <span class="badge bg-secondary px-3 py-2">Total Ekskul: <?= $totalEkskul ?> | Total Kelas:
                        <?= $totalKelas ?></span>
                </div>

                <div class="row">
                    <!-- Left Side: Ekskul List -->
                    <div class="col-lg-6 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-list me-2"></i>Daftar Ekstrakurikuler</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="tableEkstra"
                                        class="w-100 table table-striped table-bordered table-hover table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th width="40" class="align-middle text-center p-0">No.</th>
                                                <th>Nama Ekskul</th>
                                                <th>Kode</th>
                                                <th width="100" class="align-middle text-center p-0">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Class Assignments -->
                    <div class="col-lg-6 mb-3">
                        <form id="saveAssignment">
                            <?= csrf_field() ?>
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold"><i class="bi bi-door-closed me-2"></i>Tentukan Kelas Ekskul
                                    </h6>
                                    <button type="submit" class="btn btn-success btn-sm" id="btn-save-assignment">
                                        <i class="bi bi-save me-1"></i>Simpan
                                    </button>
                                </div>

                                <div class="card-body">
                                    <?php if ($totalKelas > 0): ?>
                                        <?php foreach ($kelas as $key => $kls): ?>
                                            <?php
                                            $ids_selected = [];
                                            if (isset($ekskul_kelas[$key]) && !empty($ekskul_kelas[$key])) {
                                                $ids = $ekskul_kelas[$key][0]->ekstra;
                                                foreach ($ids as $id) {
                                                    $ids_selected[] = is_array($id) ? $id['ekstra'] : $id->ekstra;
                                                }
                                            }
                                            ?>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Kelas <?= $kls ?></label>
                                                <?= form_dropdown(
                                                    'ekskul' . $key . '[]',
                                                    $ekskul,
                                                    $ids_selected,
                                                    'id="ekskul' . $key . '" class="select2 form-select" multiple="multiple" data-placeholder="Pilih Ekskul"'
                                                ) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="alert alert-warning small py-2 mb-0" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>Belum ada data kelas
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createEkstraModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCreate">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createModalLabel">Tambah Ekstrakurikuler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Ekstrakurikuler*</label>
                        <input type="text" name="nama_ekstra" class="form-control" required
                            placeholder="Contoh: Pramuka">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Kode*</label>
                        <input type="text" name="kode_ekstra" class="form-control" required placeholder="Contoh: PRMK">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" id="btn-create-submit">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editEkstraModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formUpdate">
                <?= csrf_field() ?>
                <input type="hidden" name="id_ekstra" id="editIdEkstra">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editModalLabel">Edit Ekstrakurikuler</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Ekstrakurikuler*</label>
                        <input type="text" name="nama_ekstra" id="namaEdit" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Kode*</label>
                        <input type="text" name="kode_ekstra" id="kodeEdit" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" id="btn-update-submit">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const kelases = <?= json_encode($kelas) ?>;
    let table;

    function reload_ajax() {
        table.ajax.reload(null, false);
    }

    function deleteItem(id) {
        Swal.fire({
            title: "Hapus Ekskul?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal"
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= site_url('dataekstra/delete/') ?>" + id,
                    type: "GET",
                    dataType: "JSON",
                    success: function (res) {
                        if (res.status) {
                            showSuccessToast(res.message);
                            reload_ajax();
                        } else {
                            Swal.fire({
                                title: "Gagal Hapus",
                                html: res.total || res.message,
                                icon: "error"
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        showDangerToast('Terjadi kesalahan server');
                    }
                });
            }
        });
    }

    $(document).ready(function () {
        ajaxcsrf();

        $('.select2').select2({ width: '100%' });

        const arrKelas = [];
        Object.keys(kelases).forEach(key => {
            arrKelas.push({ kls_id: key });
        });

        table = $("#tableEkstra").DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "<?= site_url('dataekstra/read') ?>",
                type: "POST",
                data: function (d) {
                    d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                }
            },
            columns: [
                {
                    data: "id_ekstra",
                    className: "text-center",
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: "nama_ekstra" },
                { data: "kode_ekstra" },
                {
                    data: null,
                    className: "text-center",
                    render: function (data) {
                        return `
                            <div class="btn-action-group justify-content-center">
                                <button type="button" class="btn btn-action btn-action-edit" 
                                    onclick="editEkstra(${data.id_ekstra}, '${data.nama_ekstra}', '${data.kode_ekstra}')" 
                                    title="Edit Ekstrakurikuler">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <button type="button" class="btn btn-action btn-action-delete" 
                                    onclick="deleteItem(${data.id_ekstra})" 
                                    title="Hapus Ekstrakurikuler">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            ordering: false,
            info: false,
            paging: false,
            searching: true,
            language: {
                processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div>'
            }
        });

        $('#formCreate').on('submit', function (e) {
            e.preventDefault();
            const btn = $('#btn-create-submit');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "<?= site_url('dataekstra/create') ?>",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (res) {
                    if (res.status) {
                        showSuccessToast('Berhasil menambahkan ekskul');
                        $('#createEkstraModal').modal('hide');
                        $('#formCreate')[0].reset();
                        table.ajax.reload();
                    } else {
                        showDangerToast(res.message || 'Gagal menyimpan data');
                    }
                },
                error: function () {
                    showDangerToast('Terjadi kesalahan server');
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                }
            });
        });

        $('#formUpdate').on('submit', function (e) {
            e.preventDefault();
            const btn = $('#btn-update-submit');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "<?= site_url('dataekstra/update') ?>",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function (res) {
                    if (res.status) {
                        showSuccessToast('Berhasil mengubah ekskul');
                        $('#editEkstraModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        showDangerToast(res.message || 'Gagal mengupdate data');
                    }
                },
                error: function () {
                    showDangerToast('Terjadi kesalahan server');
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Perubahan');
                }
            });
        });

        $('#saveAssignment').on('submit', function (e) {
            e.preventDefault();
            const btn = $('#btn-save-assignment');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "<?= site_url('dataekstra/save') ?>",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize() + '&kelas=' + JSON.stringify(arrKelas),
                success: function (res) {
                    if (res.status) {
                        showSuccessToast('Assignment kelas berhasil disimpan');
                    } else {
                        showDangerToast('Gagal menyimpan assignment');
                    }
                },
                error: function () {
                    showDangerToast('Terjadi kesalahan server');
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                }
            });
        });
    });

    function editEkstra(id, nama, kode) {
        $('#editIdEkstra').val(id);
        $('#namaEdit').val(nama);
        $('#kodeEdit').val(kode);
        $('#editEkstraModal').modal('show');
    }
</script>
<?= $this->endSection() ?>