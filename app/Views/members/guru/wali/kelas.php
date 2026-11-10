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
                <div class="card card-default my-shadow">
                    <div class="card-header d-flex align-items-center bg-gradient-primary text-white py-3">
                        <h3 class="card-title fw-bold">
                            <i class="bi bi-people-fill me-2"></i><?= $subjudul ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-siswa" class="table table-hover align-middle w-100">
                                <thead class="bg-light small fw-bold text-uppercase">
                                    <tr>
                                        <th class="text-center py-3" style="width: 50px">No.</th>
                                        <th>Siswa</th>
                                        <th>NIS & NISN</th>
                                        <th>Akun Login</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($siswas)):
                                        foreach ($siswas as $idx => $siswa):
                                            $foto = (!empty($siswa->foto) && file_exists(FCPATH . $siswa->foto))
                                                ? base_url($siswa->foto)
                                                : base_url('assets/img/siswa.png');

                                            $statusBadge = ($siswa->active == "1")
                                                ? '<span class="badge rounded-pill bg-success px-3">Aktif</span>'
                                                : '<span class="badge rounded-pill bg-danger px-3">Nonaktif</span>';

                                            $genderBadge = ($siswa->jenis_kelamin == "L")
                                                ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">Laki-laki</span>'
                                                : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Perempuan</span>';
                                            ?>
                                            <tr>
                                                <td class="text-center fw-bold text-muted"><?= $idx + 1 ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="position-relative">
                                                            <img src="<?= $foto ?>" class="rounded-circle shadow-sm border"
                                                                width="45" height="45" style="object-fit: cover;"
                                                                alt="<?= $siswa->nama ?>">
                                                        </div>
                                                        <div class="ms-3">
                                                            <div class="fw-bold mb-1"><?= $siswa->nama ?></div>
                                                            <div class="small text-muted d-flex gap-2">
                                                                <?= $genderBadge ?>
                                                                <?php if ($siswa->agama): ?>
                                                                    <span
                                                                        class="badge bg-info-subtle text-info border border-info-subtle"><?= $siswa->agama ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small fw-medium mb-1"><i class="bi bi-card-text me-1"></i>
                                                        <?= $siswa->nis ?></div>
                                                    <div class="small text-muted"><i class="bi bi-person-badge me-1"></i>
                                                        <?= $siswa->nisn ?></div>
                                                </td>
                                                <td>
                                                    <div class="small mb-1"><span class="text-muted">User:</span> <code
                                                            class="bg-light px-1 rounded"><?= $siswa->username ?></code></div>
                                                    <div class="small"><span class="text-muted">Pass:</span> <code
                                                            class="bg-light px-1 rounded"><?= $siswa->password ?></code></div>
                                                </td>
                                                <td class="text-center">
                                                    <?= $statusBadge ?>
                                                    <?php if ($siswa->nama_kelas): ?>
                                                        <div class="small mt-1 text-primary fw-bold"><?= $siswa->nama_kelas ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a class="btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm"
                                                        href="<?= base_url('walisiswa/edit/' . $siswa->id_siswa) ?>">
                                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-person-x d-block fs-1 mb-2 opacity-25"></i>
                                                Tidak ada data siswa ditemukan
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#table-siswa').DataTable({
            "order": [[0, "asc"]],
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Cari siswa...",
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "<i class='bi bi-chevron-right'></i>",
                    "previous": "<i class='bi bi-chevron-left'></i>"
                }
            },
            "dom": '<"d-flex flex-column flex-md-row justify-content-between p-3"<"mb-2 mb-md-0"l><"ms-auto"f>>rt<"d-flex flex-column flex-md-row justify-content-between p-3"<"mb-2 mb-md-0"i><"ms-auto"p>>'
        });
    });
</script>

<?= $this->endSection() ?>