<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>CBT Installer</title>

    <link rel="shortcut icon" href="<?= base_url('assets/img/favicon.png') ?>" type="image/x-icon">

    <!-- Custom fonts for this template-->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/multi-step.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/multi-step-theme.css">
    <link href="<?= base_url() ?>/assets/plugins/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="<?= base_url() ?>/assets/app/css/animate.css" rel="stylesheet">
    <link href="<?= base_url() ?>/assets/app/css/install.css" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/select2/css/select2.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/adminlte/dist/css/adminlte.min.css">
    <!-- jQuery -->
    <script src="<?= base_url() ?>/assets/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?= base_url() ?>/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
    <!--
    <script src="<?= base_url() ?>/assets/app/js/multi-step.js"></script>
    -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

    <style>
        .progress-container {
            margin-top: 10px;
        }
        .progress-label {
            font-size: 14px;
            margin-bottom: 4px;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #ddd;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .progress-bar-inner {
            height: 100%;
            width: 0%;
            background-color: #4caf50;
            transition: width 0.3s;
        }
        .progress-bar-inner.secondary {
            background-color: #2196f3;
        }

        .gradient {
            background: linear-gradient(90deg, #5e83ab 0%, #6A21EA  100%); /* projectmodif */
        }
    </style>
</head>

<script type="text/javascript">
    let base_url = '<?=base_url()?>';
</script>

<body id="page-top">
