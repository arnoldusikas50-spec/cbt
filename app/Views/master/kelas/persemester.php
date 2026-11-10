<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="content-header bg-white">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <?= $judul ?>
                </h1>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('datakelas') ?>" class="btn btn-sm btn-danger shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4">
                <div class="card-header py-2 bg-transparent">
                    <h6 class="mb-0 fw-bold">
                        <?= $subjudul ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Fitur Salin Semester</h6>
                        <p class="mb-2">Fitur ini digunakan untuk menyalin semua kelas dan siswa TP: <b>
                                <?= $tp_active->tahun ?>
                            </b> dari Semester 1 ke Semester 2.</p>
                        <ul class="mb-0 small">
                            <li>Hanya kelas yang <b>BELUM ADA</b> di semester 2 yang akan ditampilkan untuk disalin.
                            </li>
                            <li>Pilih kelas yang akan disalin dengan mencentang kotak di kolom Copy.</li>
                            <li>Klik tombol <b>Simpan</b> untuk mulai proses penyalinan data.</li>
                        </ul>
                    </div>

                    <?php if (count($kelas) == 0): ?>
                        <div class="alert alert-warning text-center py-4">
                            <i class="bi bi-exclamation-triangle display-4"></i>
                            <p class="mt-2 mb-0">Tidak ada data kelas di Semester 1 yang dapat disalin.</p>
                        </div>
                    <?php else: ?>
                        <form id="managekelas">
                            <?= csrf_field() ?>
                            <div class="d-flex justify-content-end mb-3">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm" id="save-all" disabled>
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover shadow-none">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center align-middle" style="width: 50px">No</th>
                                            <th class="align-middle px-3">Kelas Semester 1</th>
                                            <th class="align-middle px-3">Target Semester 2</th>
                                            <th class="align-middle px-3">Status</th>
                                            <th class="text-center align-middle" style="width: 80px">
                                                Copy<br>
                                                <input class="form-check-input mt-1" type="checkbox" id="check-all">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        <?php $no = 1;
                                        foreach ($kelas as $key => $kls): ?>
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <?= $no++ ?>
                                                </td>
                                                <td class="align-middle px-3 fw-semibold">
                                                    <?= $kls ?>
                                                </td>
                                                <td class="align-middle px-3">
                                                    <?= $kls ?>
                                                </td>
                                                <td class="align-middle px-3">
                                                    <?php if (in_array($kls, $kelas2)): ?>
                                                        <span class="badge bg-success py-2 px-3 rounded-pill"><i
                                                                class="bi bi-check-circle me-1"></i> Sudah Ada</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger py-2 px-3 rounded-pill"><i
                                                                class="bi bi-x-circle me-1"></i> Belum Ada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if (!in_array($kls, $kelas2)): ?>
                                                        <input type="checkbox" name="kelas_satu[]" value="<?= $key ?>"
                                                            class="form-check-input check-kelas">
                                                    <?php else: ?>
                                                        <i class="bi bi-check-lg text-success fs-5"></i>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const base_url = '<?= base_url() ?>/';
    $(document).ready(function () {
        $("#check-all").on("click", function () {
            $(".check-kelas").prop('checked', this.checked);
            updateSaveButton();
        });

        $("#table-body").on("change", ".check-kelas", function () {
            let allChecked = $(".check-kelas").length === $(".check-kelas:checked").length;
            $("#check-all").prop('checked', allChecked);
            $("#check-all").prop('indeterminate', $(".check-kelas:checked").length > 0 && !allChecked);
            updateSaveButton();
        });

        function updateSaveButton() {
            $("#save-all").prop('disabled', $(".check-kelas:checked").length === 0);
        }

        $('#managekelas').submit(function (e) {
            e.preventDefault();

            Swal.fire({
                title: "Salin Kelas?",
                text: "Kelas yang dipilih beserta daftar siswanya akan disalin ke Semester 2.",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Salin!"
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang menyalin...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: base_url + 'datakelas/copyallfromsmt1',
                        type: "POST",
                        data: $(this).serialize(),
                        success: function (data) {
                            if (data.success) {
                                Swal.fire("Berhasil", data.insert + " Kelas berhasil disalin", "success").then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire("Gagal", "Gagal menyalin data", "error");
                            }
                        },
                        error: function (xhr) {
                            Swal.fire("Error", "Gagal menghubungi server", "error");
                        }
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>