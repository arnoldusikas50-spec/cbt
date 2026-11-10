<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?? 'Student Portal' ?> -
        <?= $setting->nama_sekolah ?? 'Aplikasi Asesmen' ?>
    </title>

    <!-- Favicon -->
    <?php
    $logo = ($setting->logo_kiri ?? null) ? base_url($setting->logo_kiri) : base_url('assets/img/favicon.png');
    ?>
    <link rel="icon" href="<?= $logo ?>" type="image/png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Custom Frontend CSS (optional - uncomment when file exists) -->
    <!-- <link rel="stylesheet" href="<?= base_url('assets/frontend/css/style.css') ?>"> -->

    <!-- Additional Styles -->
    <?= $this->renderSection('styles') ?>

    <script>
        const base_url = "<?= base_url() ?>";
    </script>
</head>

<body>
    <!-- Main Content -->
    <?= $this->renderSection('content') ?>

    <!-- jQuery -->
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>

    <!-- Bootstrap Bundle -->
    <script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Custom Frontend JS (optional - uncomment when file exists) -->
    <!-- <script src="<?= base_url('assets/frontend/js/app.js') ?>"></script> -->

    <!-- Additional Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>

</html>