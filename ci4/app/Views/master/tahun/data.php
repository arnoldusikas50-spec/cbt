<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <?= esc($subjudul) ?>
                    </div>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <button type="button" onclick="location.reload()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </button>
                        <button type="button" data-from="add" data-bs-toggle="modal" data-bs-target="#createTahunModal"
                            class="btn btn-sm btn-primary shadow-sm"><i class="bi bi-plus me-1"></i><span
                                class="d-none d-sm-inline-block text-nowrap">Tambah Tahun Pelajaran</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php $totalTp = count($tp); ?>
                    <div class="mb-4">
                        <div
                            class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Pengaturan Tahun & Semester</h5>
                                <div class="text-muted">Kelola tahun pelajaran aktif beserta semesternya</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-primary px-3 py-2 text-sm">Total Tahun:
                                    <?= $totalTp ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="bi bi-info-circle me-2"></i>Panduan Pengaturan</h5>
                        <ul class="ps-3 mb-0">
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
                                <label>Tahun Pelajaran</label>
                                <table id="tahun" class="table table-sm table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="d-none">id</th>
                                            <th height="50" class="text-center align-middle border-0">No.</th>
                                            <th class="text-center align-middle border-0">Tahun Pelajaran</th>
                                            <th class="text-center align-middle border-0">Status</th>
                                            <th class="text-center align-middle border-0">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tp as $key => $value): ?>
                                            <tr>
                                                <td class="d-none">
                                                    <?= $value->id_tp ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= ($key + 1) ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?= esc($value->tahun) ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if ($value->active): ?>
                                                        <span class="badge bg-success shadow-sm px-3"><i
                                                                class="bi bi-check me-1"></i> AKTIF</span>
                                                    <?php else: ?>
                                                        <button type="button" data-id="<?= $value->id_tp ?>"
                                                            class="btn btn-sm btn-primary btn-aktif w-100">AKTIFKAN</button>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" data-id="<?= $value->id_tp ?>"
                                                            data-tahun="<?= $value->tahun ?>" data-from="edit"
                                                            data-bs-toggle="modal" data-bs-target="#createTahunModal"
                                                            class="btn btn-sm btn-info text-white btn-edit shadow-sm <?= $value->active ? 'd-none' : '' ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button" data-id="<?= $value->id_tp ?>"
                                                            class="btn btn-sm btn-danger btn-hapus shadow-sm <?= $value->active ? 'd-none' : '' ?>">
                                                            <i class="bi bi-trash"></i>
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
                                <label>Semester</label>
                                <table id="semester" class="table table-sm table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="d-none">id</th>
                                            <th height="50" class="text-center align-middle border-0">No.</th>
                                            <th class="text-center align-middle border-0">Semester</th>
                                            <th class="text-center align-middle border-0">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($smt as $key => $value): ?>
                                            <tr>
                                                <td class="d-none">
                                                    <?= $value->id_smt ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= ($key + 1) ?>
                                                </td>
                                                <td class="text-center">
                                                    <?= esc($value->smt) ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if ($value->active): ?>
                                                        <span class="badge bg-success shadow-sm px-3"><i
                                                                class="bi bi-check me-1"></i> AKTIF</span>
                                                    <?php else: ?>
                                                        <button type="button" data-id="<?= $value->id_smt ?>"
                                                            class="btn btn-sm btn-warning btn-aktif w-100">AKTIFKAN</button>
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
    </section>
</main>

<div class="modal fade" id="createTahunModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="createForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Tahun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <div class="modal-footer">
                    <input type="hidden" id="editIdTahun" name="id_tahun">
                    <input type="hidden" id="method" name="method">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" id="submit-tp">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= base_url('assets/plugins/jquery-table2json/src/tabletojson.js') ?>"></script>
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
            if (method === 'edit') {
                $('#createModalLabel').text('Edit Tahun Pelajaran');
                $('#editIdTahun').val($(e.relatedTarget).data('id'));
                $('#createtahun').val($(e.relatedTarget).data('tahun'));
            } else {
                $('#createModalLabel').text('Tambah Tahun Pelajaran');
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
</script>