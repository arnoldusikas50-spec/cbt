<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <?= esc($judul ?? 'Detail Data') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('datakelas') ?>" class="text-decoration-none">
                            Data Kelas
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title fw-bold">
                    <i class="bi bi-info-circle me-2"></i>
                    <?= $subjudul . ' ' . ($kelas->nama_kelas ?? '-') ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('datakelas') ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mb-0">
                                <tr>
                                    <td style="width: 200px" class="fw-bold bg-body-tertiary">Nama Kelas</td>
                                    <td><?= $kelas->nama_kelas ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold bg-body-tertiary">Kode Kelas</td>
                                    <td><?= $kelas->kode_kelas ?? '-' ?></td>
                                </tr>
                                <?php if ($setting->jenjang == '3'): ?>
                                    <tr>
                                        <td class="fw-bold bg-body-tertiary">Jurusan</td>
                                        <td><?= $kelas->nama_jurusan ?? '-' ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="fw-bold bg-body-tertiary">Level Kelas</td>
                                    <td><?= $kelas->level ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold bg-body-tertiary">Wali Kelas</td>
                                    <td><?= $kelas->nama_guru ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold bg-body-tertiary text-success">Jumlah Siswa</td>
                                    <td class="fw-bold text-success"><?= count($siswas) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <div class="card card-outline card-info h-100">
                            <div class="card-header">
                                <h3 class="card-title fw-bold">
                                    <i class="bi bi-people me-2"></i>Daftar Siswa
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th class="text-center" style="width: 50px">No</th>
                                                <th class="text-center" style="width: 150px">NIS / NISN</th>
                                                <th>Nama Siswa</th>
                                                <th class="text-center" style="width: 80px">L/P</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($siswas) > 0): ?>
                                                <?php $no = 1;
                                                foreach ($siswas as $siswa): ?>
                                                    <tr>
                                                        <td class="text-center align-middle"><?= $no++ ?></td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge bg-secondary"><?= $siswa->nis ?></span>
                                                            <span class="badge bg-light text-dark border"><?= $siswa->nisn ?></span>
                                                        </td>
                                                        <td class="align-middle fw-medium"><?= $siswa->nama ?></td>
                                                        <td class="text-center align-middle"><?= $siswa->jenis_kelamin ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted fst-italic">
                                                        <i class="bi bi-emoji-frown fs-4 d-block mb-2"></i>
                                                        Belum ada siswa di kelas ini
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card card-outline card-success h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title fw-bold">
                                    <i class="bi bi-diagram-3 me-2"></i>Struktur Organisasi
                                </h3>
                                <div class="card-tools">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Wali Kelas</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <?php
                                    $arrJabatan = [
                                        'wali_kelas' => 'Wali Kelas',
                                        'ketua' => 'Ketua Kelas',
                                        'wakil_ketua' => 'Wakil Ketua',
                                        'sekretaris_1' => 'Sekretaris I',
                                        'sekretaris_2' => 'Sekretaris II',
                                        'bendahara_1' => 'Bendahara I',
                                        'bendahara_2' => 'Bendahara II',
                                        'sie_upacara' => 'Sie Upacara',
                                        'sie_ekstrakurikuler' => 'Sie Ekstrakurikuler',
                                        'sie_olahraga' => 'Sie Olahraga',
                                        'sie_keagamaan' => 'Sie Keagamaan',
                                        'sie_keamanan' => 'Sie Keamanan',
                                        'sie_ketertiban' => 'Sie Ketertiban',
                                        'sie_kebersihan' => 'Sie Kebersihan',
                                        'sie_keindahan' => 'Sie Keindahan',
                                        'sie_kesehatan' => 'Sie Kesehatan',
                                        'sie_kekeluargaan' => 'Sie Kekeluargaan',
                                        'sie_humas' => 'Sie Humas'
                                    ];
                                    ?>
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px">No</th>
                                                <th>Jabatan</th>
                                                <th>Nama / Petugas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1;
                                            foreach ($arrJabatan as $key => $j): ?>
                                                <tr>
                                                    <td class="text-center align-middle"><?= $no++ ?></td>
                                                    <td class="align-middle fw-semibold text-secondary"><?= $j ?></td>
                                                    <td class="align-middle">
                                                        <?php
                                                        if ($key === 'wali_kelas') {
                                                            echo '<span class="fw-bold text-success">' . ($kelas->nama_guru ?? '-') . '</span>';
                                                        } else {
                                                            $siswa_id = $struktur->$key ?? null;
                                                            $found = false;
                                                            if ($siswa_id) {
                                                                foreach ($siswas as $s) {
                                                                    if ($s->id_siswa == $siswa_id) {
                                                                        echo $s->nama;
                                                                        $found = true;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            if (!$found)
                                                                echo '<span class="text-muted fst-italic small">-</span>';
                                                        }
                                                        ?>
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
    </div>
</div>
<?= $this->endSection() ?>