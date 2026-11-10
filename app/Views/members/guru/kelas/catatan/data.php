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
                            <i class="bi bi-journal-text me-2 text-primary"></i>
                            <?= $subjudul ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 mb-2">
                                <label class="form-label fw-bold">Mata Pelajaran</label>
                                <select id="opsi-mapel" class="form-select select2 shadow-sm rounded-pill">
                                    <option value="" disabled <?= empty($mapel_selected) ? 'selected' : '' ?>>Pilih Mapel
                                    </option>
                                    <?php foreach ($mapel as $id => $nama): ?>
                                        <option value="<?= $id ?>" <?= $mapel_selected == $id ? 'selected' : '' ?>>
                                            <?= $nama ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-bold">Kelas</label>
                                <select id="opsi-kelas" class="form-select select2 shadow-sm rounded-pill">
                                    <option value="" disabled <?= empty($kelas_selected) ? 'selected' : '' ?>>Pilih Kelas
                                    </option>
                                    <?php
                                    $available_kelas = (!empty($mapel_selected) && isset($kelas[$mapel_selected])) ? $kelas[$mapel_selected] : [];
                                    foreach ($available_kelas as $id => $nama): ?>
                                        <option value="<?= $id ?>" <?= $kelas_selected == $id ? 'selected' : '' ?>>
                                            <?= $nama ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                                    <div
                                        class="card-header bg-gradient-primary text-white d-flex align-items-center py-3">
                                        <h5 class="card-title mb-0 fs-6 fw-bold">
                                            <i class="bi bi-people-fill me-2"></i>Catatan Perkelas
                                        </h5>
                                        <?php $disabled = !empty($cat_kelas) || (!empty($mapel_selected) && !empty($kelas_selected)) ? '' : 'disabled'; ?>
                                        <button id="create-note" type="button"
                                            class="btn btn-sm btn-light text-primary shadow-sm rounded-pill ms-auto fw-bold px-3"
                                            data-bs-toggle="modal" data-bs-target="#daftarModal" <?= $disabled ?>>
                                            <i class="bi bi-plus-lg me-1"></i> Buat Catatan Kelas
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="konten-catatankelas">
                                            <?php if (isset($cat_kelas)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle mb-0">
                                                        <thead class="bg-light small fw-bold text-uppercase">
                                                            <tr>
                                                                <th class="text-center py-3" style="width: 60px">No.</th>
                                                                <th class="text-center">Tanggal</th>
                                                                <th class="text-center">Jenis</th>
                                                                <th>Catatan</th>
                                                                <th class="text-center">Keterangan</th>
                                                                <th class="text-center">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (count($cat_kelas) > 0):
                                                                $arrLvl = [1 => 'Saran', 2 => 'Teguran', 3 => 'Peringatan', 4 => 'Sangsi'];
                                                                $arrBadge = [1 => 'bg-info', 2 => 'bg-warning', 3 => 'bg-orange text-white', 4 => 'bg-danger'];
                                                                $no = 1;
                                                                foreach ($cat_kelas as $value): ?>
                                                                    <tr>
                                                                        <td class="text-center fw-bold text-muted">
                                                                            <?= $no++ ?>
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
                                                                            <span
                                                                                class="badge bg-light text-primary border border-primary px-3 rounded-pill">
                                                                                <i class="bi bi-eye me-1"></i>
                                                                                <?= count($value->reading) ?> Siswa
                                                                            </span>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                                                data-id="<?= $value->id_catatan ?>"
                                                                                onclick="hapus(this)" title="Hapus">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach;
                                                            else: ?>
                                                                <tr>
                                                                    <td colspan="6" class="text-center py-5 text-muted">
                                                                        <i
                                                                            class="bi bi-journal-x d-block fs-1 mb-2 opacity-25"></i>
                                                                        Belum ada catatan untuk kelas ini
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="p-5 text-center text-muted">
                                                    <i
                                                        class="bi bi-arrow-up-circle d-block fs-1 mb-2 opacity-25 text-primary"></i>
                                                    Silakan pilih Mata Pelajaran dan Kelas terlebih dahulu
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                    <div
                                        class="card-header bg-gradient-success text-white d-flex align-items-center py-3">
                                        <h5 class="card-title mb-0 fs-6 fw-bold">
                                            <i class="bi bi-person-badge me-2"></i>Catatan Persiswa
                                        </h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="konten-catatansiswa">
                                            <?php if (isset($cat_siswa)): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle mb-0">
                                                        <thead class="bg-light small fw-bold text-uppercase">
                                                            <tr>
                                                                <th class="text-center py-3" style="width: 60px">No.</th>
                                                                <th class="text-center">NIS</th>
                                                                <th>Nama Lengkap</th>
                                                                <th class="text-center">Jumlah Catatan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (count($cat_siswa) > 0):
                                                                $no = 1;
                                                                foreach ($cat_siswa as $value): ?>
                                                                    <tr>
                                                                        <td class="text-center fw-bold text-muted text-sm">
                                                                            <?= $no++ ?>
                                                                        </td>
                                                                        <td class="text-center small font-monospace">
                                                                            <?= $value->nis ?>
                                                                        </td>
                                                                        <td class="fw-medium">
                                                                            <?= $value->nama ?>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <button
                                                                                onclick="loadCatatanSiswa(<?= $value->id_siswa ?>)"
                                                                                class="btn btn-sm <?= $value->jml_catatan > 0 ? 'btn-success shadow-sm' : 'btn-outline-secondary' ?> rounded-pill px-3">
                                                                                <i class="bi bi-chat-left-text me-1"></i>
                                                                                <?= $value->jml_catatan ?> Catatan
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach;
                                                            else: ?>
                                                                <tr>
                                                                    <td colspan="4" class="text-center py-5 text-muted">
                                                                        <i
                                                                            class="bi bi-people d-block fs-1 mb-2 opacity-25"></i>
                                                                        Tidak ada siswa di kelas ini
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="p-5 text-center text-muted">
                                                    <i
                                                        class="bi bi-arrow-up-circle d-block fs-1 mb-2 opacity-25 text-success"></i>
                                                    Silakan pilih Mata Pelajaran dan Kelas untuk melihat daftar siswa
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                <h5 class="modal-title fw-bold" id="daftarLabel">Buat Catatan Kelas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formcatatan">
                <?= csrf_field() ?>
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
                    <input type="hidden" name="id_mapel" id="input-mapel" value="<?= $mapel_selected ?>">
                    <input type="hidden" name="id_kelas" id="input-kelas" value="<?= $kelas_selected ?>">
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
    var arrKelas = <?= json_encode($kelas) ?>;
    var mplSelected = '<?= $mapel_selected ?>';
    var klsSelected = '<?= $kelas_selected ?>';

    function loadCatatanSiswa(id) {
        window.location.href = base_url + 'kelascatatan/siswa?id=' + id + '&mapel=' + $('#opsi-mapel').val() + '&kelas=' + $('#opsi-kelas').val();
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
                    url: base_url + 'kelascatatan/hapus/' + idCatatan,
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

    function reloadPage(mapel, kls) {
        if (mapel && kls) {
            window.location.href = base_url + 'kelascatatan?mapel=' + mapel + '&kelas=' + kls;
        }
    }

    $(document).ready(function () {
        var selKelas = $('#opsi-kelas');
        var selMapel = $('#opsi-mapel');

        selMapel.on('change', function () {
            var idMapel = $(this).val();
            selKelas.html('<option value="" disabled selected>Pilih Kelas</option>');
            if (arrKelas[idMapel]) {
                $.each(arrKelas[idMapel], function (index, value) {
                    if (value) selKelas.append('<option value="' + index + '">' + value + '</option>');
                });
            }
        });

        selKelas.change(function () {
            reloadPage(selMapel.val(), $(this).val());
        });

        $('#formcatatan').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: base_url + "kelascatatan/savecatatankelas",
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