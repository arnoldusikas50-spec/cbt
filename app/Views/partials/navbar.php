<nav class="app-header navbar navbar-expand bg-body shadow">
    <?php
    $defaultAvatar = base_url('assets/img/logo.png');
    $avatar = $defaultAvatar;

    if (!empty($user->foto)) {
        $photoPath = ltrim($user->foto, '/');
        // Check if file exists relative to public/ (FCPATH)
        if (file_exists(FCPATH . $photoPath)) {
            $avatar = base_url($photoPath);
        }
    }

    $userSessName = session()->get('nama');
    $userName = !empty($user->nama_lengkap) ? $user->nama_lengkap : (!empty($userSessName) ? $userSessName : 'IKASMEDIA');
    $userRole = session()->get('role_name') ?? 'Admin';

    // Check Redis status
    $redisStatus = 'OFF';
    $redisClass = 'bg-secondary';
    try {
        if (class_exists('Redis')) {
            $redis = new Redis();
            if (@$redis->connect('127.0.0.1', 6379, 0.5)) {
                $redisStatus = 'ON';
                $redisClass = 'bg-success';
                $redis->close();
            }
        }
    } catch (Throwable $e) {
        // Redis not available
    }

    // Fallback load active TP & SMT if not explicitly passed by view data
    if (empty($tp_active) || empty($smt_active)) {
        try {
            $dashModelFallback = new \App\Models\DashboardModel();
            if (empty($tp_active)) {
                $tp_active = $dashModelFallback->getTahunActive();
            }
            if (empty($smt_active)) {
                $smt_active = $dashModelFallback->getSemesterActive();
            }
        } catch (Throwable $e) {
            // Silently handle DB fallback exception
        }
    }
    ?>

    <!-- Left navbar links -->
    <ul class="navbar-nav align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list fs-5"></i></a>
        </li>
        <?php 
        // Deterministic check for supervisor role with defensive guards
        $isSupervisor = false;
        if (isset($is_pengawas) && $is_pengawas) {
            $isSupervisor = true;
        } elseif (session()->get('is_login') && session()->get('role') == 4) { // 4 = Panitia
            try {
                $db = \Config\Database::connect();
                $builder = $db->table('master_panitia');
                
                // Ensure table exists and query executes successfully
                if ($db->tableExists('master_panitia')) {
                    $userId = session()->get('user_id');
                    if ($userId) {
                        $query = $builder->where('id_user', $userId)->get();
                        if ($query !== false) {
                            $pData = $query->getRow();
                            if ($pData && strtolower(trim($pData->jabatan ?? '')) === 'pengawas') {
                                $isSupervisor = true;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently fail to prevent 500 error in header
                $isSupervisor = false;
            }
        }
        
        if (!$isSupervisor): ?>
        <li class="nav-item d-none d-sm-block ms-2">
            <div class="d-flex align-items-center bg-light border rounded-pill px-3 py-1 shadow-xs">
                <small class="text-muted fw-bold text-uppercase me-2" style="font-size: 0.65rem;">Sistem Aktif:</small>
                <span class="badge bg-white text-dark shadow-xs border-0 py-1 px-2 me-1" style="font-size: 0.75rem;">
                    <i class="bi bi-calendar3 text-primary me-1"></i>
                    <?= (!empty($tp_active) && !empty($tp_active->tahun)) ? esc($tp_active->tahun) : 'Belum di set' ?>
                </span>
                <span class="badge bg-white text-dark shadow-xs border-0 py-1 px-2" style="font-size: 0.75rem;">
                    <i class="bi bi-bookmark-fill text-info me-1"></i> Smt:
                    <?= (!empty($smt_active) && !empty($smt_active->smt)) ? esc($smt_active->smt) : 'Belum di set' ?>
                </span>
                <span class="badge <?= $redisClass ?> text-white shadow-xs border-0 py-1 px-2 ms-1" style="font-size: 0.75rem;" title="Redis Status">
                    <i class="bi bi-hdd-stack-fill me-1"></i> Redis: <?= $redisStatus ?>
                </span>
            </div>
        </li>
        <?php endif; ?>
    </ul>

    <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item d-none d-md-block">
            <div class="nav-link">
                <div
                    class="px-3 py-1 bg-danger bg-gradient text-white shadow-sm rounded-pill d-flex align-items-center">
                    <i class="bi bi-clock-fill me-2"></i>
                    <span id="live-clock" class="fw-bold font-monospace"
                        style="min-width: 80px; text-align: center;"></span>
                </div>
            </div>
        </li>
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <img src="<?= $avatar ?>" class="user-image rounded-circle shadow-sm me-2" alt="User Image" onerror="this.src='<?= $defaultAvatar ?>'">
                <div class="d-none d-md-block text-start">
                    <span class="d-block text-dark fw-bold"><?= esc($userName) ?></span>
                </div>
                <i class="bi bi-chevron-down text-dark ms-2"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0">
                <li class="user-header bg-dark text-center">
                    <div class="image mb-2">
                        <img src="<?= $avatar ?>" class="rounded-circle" alt="User Image" style="height:90px" onerror="this.src='<?= $defaultAvatar ?>'">
                    </div>
                    <p class="mb-0">
                        <span class="d-block"><?= esc($userName) ?></span>
                        <small class="text-muted"><?= esc($userRole) ?></small>
                    </p>
                </li>
                <li class="user-footer bg-yellow d-flex align-items-center w-100 px-0">
                    <?php
                    $profilLink = site_url('settings');
                    if (strcasecmp($userRole ?? '', 'guru') === 0 || (session()->get('role') == 2)) {
                        $profilLink = site_url('guru');
                    } elseif (strcasecmp($userRole ?? '', 'panitia') === 0 || (session()->get('role') == 4)) {
                        $profilLink = site_url('panitia');
                    }
                    ?>
                    <a href="<?= $profilLink ?>"
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