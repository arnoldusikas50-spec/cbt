<style>
    .exam-header-banner {
        background: #00238f;
        background-image: url('<?= base_url() ?>assets/front_assets/images/header-bg.png');
        background-repeat: no-repeat;
        background-position: left;
        background-size: auto 100%;
        min-height: 200px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0, 35, 143, 0.3);
        position: relative;
        overflow: hidden;
    }

    .exam-header-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: -50%;
        width: 200%;
        height: 100%;
        background: url('<?= base_url() ?>assets/front_assets/images/header-bg.png') no-repeat left center;
        background-size: auto 100%;
        opacity: 0.15;
        pointer-events: none;
    }

    @keyframes gradientShift {

        0%,
        100% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }
    }

    .exam-logo-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 1;
    }

    .exam-logo-section img {
        height: 50px;
        width: auto;
        filter: brightness(0) invert(1);
        transition: transform 0.3s ease;
    }

    .exam-logo-section img:hover {
        transform: scale(1.05);
    }

    .exam-user-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 1;
    }

    .exam-user-info {
        text-align: right;
        color: #fff;
    }

    .exam-user-info .user-name {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .exam-user-info .user-meta {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 8px;
    }

    .exam-avatar {
        width: 55px;
        height: 55px;
        background: linear-gradient(135deg, #3a8ee6 0%, #5ba3f5 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .exam-avatar:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    .exam-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .btn-exam-logout {
        background: rgba(255, 255, 255, 0.95);
        color: #00238f;
        border: none;
        border-radius: 25px;
        padding: 6px 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-decoration: none !important;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-exam-logout:hover {
        background: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        color: #00238f;
    }

    @media (max-width: 768px) {
        .exam-header-banner {
            padding: 0.75rem 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .exam-logo-section {
            flex: 0 0 auto;
        }

        .exam-logo-section img {
            height: 35px;
        }

        .exam-user-section {
            flex: 1;
            justify-content: flex-end;
        }

        .exam-user-info .user-name {
            font-size: 0.8rem;
        }

        .exam-user-info .user-meta {
            font-size: 0.7rem;
            margin-bottom: 5px;
        }

        .exam-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
        }

        .btn-exam-logout {
            padding: 4px 10px;
            font-size: 0.65rem;
        }
    }

    @media (max-width: 480px) {
        .exam-header-banner {
            padding: 0.6rem 0.75rem;
            justify-content: center;
        }

        .exam-logo-section {
            display: none;
        }

        .exam-user-section {
            width: 100%;
            justify-content: center;
        }

        .exam-user-info {
            text-align: center;
            flex: 1;
        }

        .exam-user-info .user-name {
            font-size: 0.75rem;
        }

        .exam-user-info .user-meta {
            font-size: 0.65rem;
            margin-bottom: 4px;
        }

        .exam-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
        }

        .btn-exam-logout {
            padding: 3px 8px;
            font-size: 0.6rem;
        }
    }
</style>

<div class="exam-header-banner">
    <div class="exam-logo-section">
        <img src="<?= base_url() ?>assets/img/logo.png" alt="Logo">
    </div>

    <div class="exam-user-section">
        <div class="exam-user-info">
            <div class="user-name"><?= $siswa->nama ?></div>
            <div class="user-meta"><?= $siswa->nisn ?> - <?= $siswa->kode_kelas ?></div>
            <a href="javascript:void(0)" onclick="BlokirKeluar()" class="btn-exam-logout">Logout</a>
        </div>
        <div class="exam-avatar">
            <?php if (!file_exists(FCPATH . $siswa->foto) || $siswa->foto == ""): ?>
                <img src="<?= base_url() ?>assets/img/siswa_icon.png" alt="Student"
                    style="width: 80%; height: auto; padding: 5px;">
            <?php else: ?>
                <img src="<?= base_url() ?><?= $siswa->foto ?>" alt="Student">
            <?php endif; ?>
        </div>
    </div>
</div>