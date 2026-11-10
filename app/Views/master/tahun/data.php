<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-calendar-check-fill text-primary me-2"></i>
                    <?= esc($judul ?? 'Tahun Pelajaran dan Semester') ?>
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

<div class="app-content pt-3">
    <div class="container-fluid px-2">
        <div class="card card-modern border-0 mb-4 overflow-hidden rounded-4 shadow-sm">
            <div class="card-header border-0 shadow-none d-flex align-items-center">
                <h6 class="card-title fw-bold text-primary mb-0"><i class="bi bi-calendar-range me-2"></i><?= esc($subjudul) ?></h6>
                <div class="card-tools ms-auto">
                    <button type="button" onclick="reload_ajax()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" data-from="add" data-bs-toggle="modal" data-bs-target="#createTahunModal"
                        class="btn btn-sm btn-primary ms-1 shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Tahun Pelajaran
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php $totalTp = count($tp); ?>

                <div class="callout callout-info border border-info-subtle mb-4 rounded-3">
                    <h6 class="mb-2 fw-bold text-info"><i class="bi bi-info-circle me-2"></i>Panduan Pengaturan</h6>
                    <ul class="ps-3 mb-0 text-sm text-secondary">
                        <li>Aktifkan tahun pelajaran yang sedang berjalan untuk memastikan data sinkron.</li>
                        <li>Gunakan tombol <span class="badge bg-primary">Tambah Tahun Pelajaran</span> untuk
                            menambah periode baru.</li>
                        <li>Tahun atau semester yang sedang aktif tidak dapat dihapus untuk menjaga konsistensi
                            data.</li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-md-7 mb-4 table-responsive">
                        <form id="edittp">
                            <?= csrf_field() ?>
                            <label class="fw-bold mb-2 text-dark"><i class="bi bi-calendar3 me-1 text-primary"></i> Tahun Pelajaran</label>
                            <table id="tahun" class="table table-sm table-striped table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="d-none">id</th>
                                        <th width="50" class="text-center">No.</th>
                                        <th class="text-center">Tahun Pelajaran</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tp as $key => $value): ?>
                                        <tr>
                                            <td class="d-none">
                                                <?= $value->id_tp ?>
                                            </td>
                                            <td class="text-center fw-bold text-secondary">
                                                <?= ($key + 1) ?>
                                            </td>
                                            <td class="text-center fw-bold">
                                                <?= esc($value->tahun) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($value->active): ?>
                                                    <span class="badge-action-status"><i class="bi bi-check-circle-fill me-1"></i> AKTIF</span>
                                                <?php else: ?>
                                                    <button type="button" data-id="<?= $value->id_tp ?>"
                                                        class="btn btn-action btn-action-warning btn-aktif w-100 py-1"
                                                        title="Aktifkan Tahun Pelajaran">
                                                        <i class="bi bi-power me-1"></i> AKTIFKAN
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-action-group justify-content-center">
                                                    <button type="button" data-id="<?= $value->id_tp ?>"
                                                        data-tahun="<?= $value->tahun ?>" data-from="edit"
                                                        data-bs-toggle="modal" data-bs-target="#createTahunModal"
                                                        class="btn btn-action btn-action-edit btn-edit <?= $value->active ? 'd-none' : '' ?>"
                                                        title="Edit Tahun Pelajaran">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <button type="button" data-id="<?= $value->id_tp ?>"
                                                        class="btn btn-action btn-action-delete btn-hapus <?= $value->active ? 'd-none' : '' ?>"
                                                        title="Hapus Tahun Pelajaran">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                    <div class="col-md-5">
                        <form id="editsmt">
                            <?= csrf_field() ?>
                            <label class="fw-bold mb-2 text-dark"><i class="bi bi-clock-history me-1 text-primary"></i> Semester</label>
                            <table id="semester" class="table table-sm table-striped table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="d-none">id</th>
                                        <th width="50" class="text-center">No.</th>
                                        <th class="text-center">Semester</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($smt as $key => $value): ?>
                                        <tr>
                                            <td class="d-none">
                                                <?= $value->id_smt ?>
                                            </td>
                                            <td class="text-center fw-bold text-secondary">
                                                <?= ($key + 1) ?>
                                            </td>
                                            <td class="text-center fw-bold">
                                                <?= esc($value->smt) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($value->active): ?>
                                                    <span class="badge-action-status"><i class="bi bi-check-circle-fill me-1"></i> AKTIF</span>
                                                <?php else: ?>
                                                    <button type="button" data-id="<?= $value->id_smt ?>"
                                                        class="btn btn-action btn-action-warning btn-aktif w-100 py-1"
                                                        title="Aktifkan Semester">
                                                        <i class="bi bi-power me-1"></i> AKTIFKAN
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createTahunModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary text-white" id="modalHeader">
                    <h5 class="modal-title" id="createModalLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Tahun
                        Pelajaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 row">
                        <label class="col-md-2 col-form-label">Tahun*</label>
                        <div class="col-md-10">
                            <input type="text" id="createtahun" name="tahun" class="form-control" required
                                placeholder="Contoh: 2023/2024">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <input type="hidden" id="editIdTahun" name="id_tahun">
                    <input type="hidden" id="method" name="method">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="reset" class="btn btn-warning shadow-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4" id="submit-tp">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript"
    src="<?= base_url('assets/plugins/jquery-table2json/lib/jquery.tabletojson.js') ?>"></script>
<script>
    $(document).ready(function () {
        $("#tahun").on("click", ".btn-aktif", function () {
            let id = $(this).data("id");
            let dataTahun = JSON.stringify($('#tahun').tableToJSON({
                ignoreColumns: [1, 3, 4] // Ignore No, Status, Aksi
            }).map(row => ({
                id: row.id,
                tp: row["Tahun Pelajaran"]
            })));

            Swal.fire({
                text: "Silahkan tunggu....",
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post(base_url + "datatahun/gantitahun", {
                active: id,
                tahun: dataTahun,
                [CSRF_NAME]: CSRF_HASH
            }, function (res) {
                Swal.fire({
                    title: res.status ? "Berhasil" : "Gagal",
                    text: res.msg,
                    icon: res.status ? "success" : "error"
                }).then(() => { if (res.status) location.reload(); });
            });
        });

        $("#semester").on("click", ".btn-aktif", function () {
            let id = $(this).data("id");
            let dataSmt = JSON.stringify($('#semester').tableToJSON({
                ignoreColumns: [1, 3]
            }).map(row => ({
                id: row.id,
                Semester: row.Semester
            })));

            $.post(base_url + "datatahun/gantisemester", {
                active: id,
                semester: dataSmt,
                [CSRF_NAME]: CSRF_HASH
            }, function (res) {
                Swal.fire({
                    title: res.status ? "Berhasil" : "Gagal",
                    text: res.msg,
                    icon: res.status ? "success" : "error"
                }).then(() => { if (res.status) location.reload(); });
            });
        });

        $('#createTahunModal').on('show.bs.modal', function (e) {
            let method = $(e.relatedTarget).data('from');
            $('#method').val(method);
            const modalHeader = $('#modalHeader');
            const modalLabel = $('#createModalLabel');

            if (method === 'edit') {
                modalHeader.removeClass('bg-primary').addClass('bg-info');
                modalLabel.html('<i class="bi bi-pencil me-2"></i>Edit Tahun Pelajaran');
                $('#editIdTahun').val($(e.relatedTarget).data('id'));
                $('#createtahun').val($(e.relatedTarget).data('tahun'));
            } else {
                modalHeader.removeClass('bg-info').addClass('bg-primary');
                modalLabel.html('<i class="bi bi-plus-circle me-2"></i>Tambah Tahun Pelajaran');
                $('#editIdTahun').val('');
                $('#createtahun').val('');
            }
        });

        $('#createForm').on('submit', function (e) {
            e.preventDefault();
            $.post(base_url + "datatahun/add", $(this).serialize(), function (res) {
                if (res.status) location.reload();
                else showDangerToast('Gagal memproses data');
            });
        });

        $("#tahun").on("click", ".btn-hapus", function () {
            let id = $(this).data("id");
            Swal.fire({
                title: 'Hapus Tahun?',
                text: 'Data yang berhubungan akan ikut terpengaruh.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Hapus"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(base_url + "datatahun/hapustahun", { hapus: id, [CSRF_NAME]: CSRF_HASH }, function (res) {
                        Swal.fire(res.status ? 'Berhasil' : 'Gagal', res.msg, res.status ? 'success' : 'error')
                            .then(() => { if (res.status) location.reload(); });
                    });
                }
            });
        });
    });

    function reload_ajax() {
        location.reload();
    }
</script>
<?= $this->endSection() ?>