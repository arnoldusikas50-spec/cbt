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
        <div class="row">
            <div class="col-12">
                <div class="card card-default my-shadow mb-4">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title fw-bold">
                            <i class="bi bi-journal-text me-2 text-primary"></i>Catatan Untuk Kelas
                            <?= $guru->nama_kelas ?>
                        </h3>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-sm btn-primary shadow-sm rounded-pill px-4"
                                data-bs-toggle="modal" data-bs-target="#daftarModal">
                                <i class="bi bi-plus-lg me-1"></i> Buat Catatan Kelas
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive border rounded shadow-sm">
                            <table class="table table-hover align-middle m-0">
                                <thead class="bg-light small fw-bold text-uppercase">
                                    <tr>
                                        <th class="text-center py-3" style="width: 60px">No.</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Jenis</th>
                                        <th>Catatan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $arrLvl = [1 => 'Saran', 2 => 'Teguran', 3 => 'Peringatan', 4 => 'Sangsi'];
                                    $arrBadge = [1 => 'bg-info', 2 => 'bg-warning', 3 => 'bg-orange text-white', 4 => 'bg-danger'];

                                    if (!empty($catatan_kelas)):
                                        foreach ($catatan_kelas as $key => $value): ?>
                                            <tr>
                                                <td class="text-center fw-bold text-muted">
                                                    <?= ($key + 1) ?>
                                                </td>
                                                <td class="text-center small">
                                                    <?= date('d M Y', strtotime($value->tgl)) ?>
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge rounded-pill <?= $arrBadge[$value->level] ?? 'bg-secondary' ?> px-3">
                                                        <?= $arrLvl[$value->level] ?? 'Lainnya' ?>
                                                    </span>
                                                </td>
                                                <td class="small">
                                                    <?= nl2br(esc($value->text)) ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                        data-id="<?= $value->id_catatan ?>" onclick="hapus(this)" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-chat-dots d-block fs-1 mb-2 opacity-25"></i>
                                                Belum ada catatan untuk kelas ini
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card card-default my-shadow">
                    <div class="card-header bg-gradient-success text-white py-3">
                        <h3 class="card-title fw-bold fs-6">
                            <i class="bi bi-people-fill me-2"></i>Catatan Per-siswa
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small fw-bold text-uppercase">
                                    <tr>
                                        <th class="text-center py-3" style="width: 60px">No.</th>
                                        <th class="text-center">NIS</th>
                                        <th>Nama Siswa</th>
                                        <th class="text-center">Jumlah Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($catatan_siswa as $key => $value): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted">
                                                <?= ($key + 1) ?>
                                            </td>
                                            <td class="text-center small font-monospace">
                                                <?= $value->nis ?>
                                            </td>
                                            <td class="fw-medium">
                                                <?= $value->nama ?>
                                            </td>
                                            <td class="text-center">
                                                <button onclick="loadCatatanSiswa(<?= $value->id_siswa ?>)"
                                                    class="btn btn-sm <?= $value->jml_catatan > 0 ? 'btn-success shadow-sm' : 'btn-outline-secondary' ?> rounded-pill px-3">
                                                    <i class="bi bi-chat-left-text me-1"></i>
                                                    <?= $value->jml_catatan ?> Catatan
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
    </div>
</div>

<!-- Modal Create Note -->
<div class="modal fade" id="daftarModal" tabindex="-1" aria-labelledby="daftarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="daftarLabel">Buat Catatan Kelas
                    <?= $guru->nama_kelas ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formcatatan">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kelas" value="<?= $guru->wali_kelas ?>">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Jenis Catatan</label>
                            <select name="level" class="form-select select2 shadow-sm rounded-3" required>
                                <option value="" disabled selected>Pilih Jenis</option>
                                <option value="1">Saran</option>
                                <option value="2">Teguran</option>
                                <option value="3">Peringatan</option>
                                <option value="4">Sangsi</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Isi Catatan</label>
                            <textarea class="form-control shadow-sm rounded-3" name="text" rows="5"
                                placeholder="Tulis catatan di sini..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link link-secondary fw-bold text-decoration-none"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm rounded-pill">
                        <i class="bi bi-send me-1"></i> Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var idKelas = '<?= $guru->wali_kelas ?>';

    function loadCatatanSiswa(id) {
        window.location.href = base_url + 'walicatatan/siswa?id_siswa=' + id + '&id_kelas=' + idKelas;
    }

    function hapus(data) {
        var idCatatan = $(data).data('id');
        Swal.fire({
            title: "Hapus Catatan?",
            text: "Tindakan ini tidak dapat dibatalkan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal"
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'walicatatan/hapus/' + idCatatan,
                    method: "GET",
                    success: function (respon) {
                        if (respon) {
                            Swal.fire("Berhasil", "Catatan telah dihapus.", "success").then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire("Gagal", "Tidak bisa menghapus catatan", "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error", "Terjadi kesalahan pada server", "error");
                    }
                });
            }
        });
    }

    $(document).ready(function () {
        $('#formcatatan').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "walicatatan/savecatatankelas",
                type: "POST",
                data: $(this).serialize(),
                success: function (data) {
                    $('#daftarModal').modal('hide');
                    if (data) {
                        Swal.fire({
                            title: "Sukses",
                            text: "Catatan berhasil disimpan",
                            icon: "success",
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Error", "Catatan tidak tersimpan", "error");
                    }
                },
                error: function () {
                    Swal.fire("Error", "Terjadi kesalahan pada server", "error");
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>