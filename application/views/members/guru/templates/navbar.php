<!-- Navbar -->
<nav class="app-header navbar navbar-expand bg-body shadow">

    <?php
    $defaultAvatar = base_url('assets/img/guru.png');
    $avatar = $defaultAvatar;

    if (isset($guru->foto) && !empty($guru->foto)) {
        $photoPath = ltrim($guru->foto, '/');
        $absolutePhoto = FCPATH . $photoPath;

        if (file_exists($absolutePhoto)) {
            $avatar = base_url($photoPath);
        }
    }

    $userName = isset($guru->nama_lengkap) && $guru->nama_lengkap ? $guru->nama_lengkap : 'IKASMEDIA';
    $userRole = isset($guru->jabatan) && $guru->jabatan ? $guru->jabatan : 'Guru';
    ?>

    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a>
        </li>
        <li class="nav-item">
            <span class="nav-link px-2">
                <span>
                    TP: <?= isset($tp_active) ? $tp_active->tahun : 'Belum di set' ?> | Smt:
                    <?= isset($smt_active) ? $smt_active->nama_smt : 'Belum di set' ?>
                </span>
            </span>
        </li>

    </ul>

    <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
            <span class="nav-link px-2">
                <span class="badge rounded-pill bg-danger">
                    <span id="live-clock" class="text-white text-end"></span>
                </span>
            </span>
        </li>
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <img src="<?= $avatar ?>" class="user-image rounded-circle shadow-sm me-2" alt="User Image">
                <div class="d-none d-md-block text-start">
                    <span class="d-block text-dark fw-bold"><?= $userName ?></span>
                </div>
                <i class="bi bi-chevron-down text-dark ms-2"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0">
                <li class="user-header bg-dark text-center">
                    <div class="image mb-2">
                        <img src="<?= $avatar ?>" class="rounded-circle" alt="User Image" style="height:90px">
                    </div>
                    <p class="mb-0">
                        <span class="d-block"><?= $userName ?></span>
                        <small class="text-muted"><?= $userRole ?></small>
                    </p>
                </li>
                <li class="user-footer bg-yellow d-flex align-items-center w-100 px-0">
                    <a href="<?= base_url() ?>guru"
                        class="btn btn-sm bg-teal rounded-0 border-0 me-auto d-inline-flex align-items-center ps-3 pe-3 ms-2">
                        <i class="bi bi-gear-wide-connected nav-icon me-1"></i> Profil
                    </a>
                    <a href="#"
                        class="btn btn-sm bg-red rounded-0 border-0 ms-auto d-inline-flex align-items-center ps-3 pe-3 me-2"
                        role="button" onclick="logout()">
                        <i class="bi bi-box-arrow-right nav-icon me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </li>
    </ul>

</nav>