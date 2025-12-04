<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-warning navbar-light shadow">

    <?php
    $defaultAvatar = base_url('assets/img/user.png');
    $avatar = $defaultAvatar;

    if (!empty($user->foto)) {
        $photoPath = ltrim($user->foto, '/');
        $absolutePhoto = FCPATH . $photoPath;

        if (file_exists($absolutePhoto)) {
            $avatar = base_url($photoPath);
        }
    }

    $userName = $user->nama_lengkap ? $user->nama_lengkap : 'IKASMEDIA';
    $userRole = $user->jabatan ? $user->jabatan : 'Admin';
    ?>

    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
            <span class="nav-link px-2">
                <span >
                    TP: <?= isset($tp_active) ? $tp_active->tahun : 'Belum di set' ?> | Smt: <?= isset($smt_active) ? $smt_active->nama_smt : 'Belum di set' ?>
                </span>
            </span>
        </li>

    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item">
            <span class="nav-link px-2">
                <span class="badge badge-pill badge-danger">
                    <span id="live-clock" class="text-white text-right"></span>
                </span>
            </span>
        </li>
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="<?= $avatar ?>" class="user-image img-circle elevation-2 mr-2" alt="User Image">
                <div class="d-none d-md-block text-left">
                    <span class="d-block text-dark font-weight-bold"><?= $userName ?></span>
                </div>
                <i class="fas fa-angle-down text-dark ml-2"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0">
                <li class="user-header bg-dark text-center">
                    <div class="image mb-2">
                        <img src="<?= $avatar ?>" class="img-circle" alt="User Image" style="height:90px">
                    </div>
                    <p class="mb-0">
                        <span class="d-block"><?= $userName ?></span>
                        <small class="text-muted"><?= $userRole ?></small>
                    </p>
                </li>
                <li class="user-footer bg-yellow d-flex align-items-center w-100 px-0">
                    <a href="<?= base_url() ?>sekolah" class="btn btn-sm bg-teal rounded-0 border-0 mr-auto d-inline-flex align-items-center pl-3 pr-3 ml-2">
                        <i class="fas fa-user-cog nav-icon mr-1"></i> Profil
                    </a>
                    <a href="#" class="btn btn-sm bg-red rounded-0 border-0 ml-auto d-inline-flex align-items-center pl-3 pr-3 mr-2" role="button" onclick="logout()">
                        <i class="fas fa-sign-out-alt nav-icon mr-1"></i> Logout
                    </a>
                </li>
            </ul>
        </li>
    </ul>

</nav>