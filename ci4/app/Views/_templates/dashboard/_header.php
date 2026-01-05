<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $judul ?> | CBT by Ikasmedia</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <?php
    $logo_app = ($setting->logo_kiri == null) ? base_url('assets/img/favicon.png') : base_url('' . $setting->logo_kiri);
    ?>
    <link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">

    <link rel="stylesheet" href="<?= base_url('assets/app/css/google-fonts.css') ?>">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css') ?>">
    <link rel="stylesheet"
        href="<?= base_url('assets/plugins/datatables-responsive/css/responsive.bootstrap5.min.css') ?>">

    <!-- JQVMap -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/jqvmap/jqvmap.min.css') ?>">
    <!-- multi select -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/multiselect/css/multi-select.css') ?>">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
    <!-- Datetime picker -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/jquery-datetimepicker/jquery.datetimepicker.min.css') ?>">
    <!-- Tempusdominus bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/tempus-dominus/tempus-dominus.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>">
    <link rel="stylesheet"
        href="<?= base_url('assets/plugins/select2-bootstrap5-theme/select2-bootstrap-5-theme.min.css') ?>">
    <!-- Toastr -->
    <link rel="stylesheet" href="<?= base_url('assets/app/css/jquery.toast.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/toastr/toastr.min.css') ?>">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/dropify/css/dropify.min.css') ?>">

    <link rel="stylesheet" href="<?= base_url('assets/app/css/show.toast.css') ?>">

    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/adminlte.min.css') ?>">

    <!-- summernote -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-lite.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/plugin/audio/summernote-audio.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/katex/katex.css') ?>">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/app/css/premium_adminlte.css') ?>">

    <link rel="stylesheet" href="<?= base_url('assets/plugins/DualSelectList/css/bala.DualSelectList.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/app/css/weekCalendar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap-icon/bootstrap-icons.css') ?>">

    <link href="<?= base_url('assets/plugins/ios-switch/component-custom-switch.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/app/css/player.css') ?>" rel="stylesheet">

    <!-- jQuery -->
    <script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>

    <script type="text/javascript"
        src="<?= base_url('assets/plugins/DualSelectList/js/bala.DualSelectList.jquery.js') ?>"></script>

    <script defer src="<?= base_url('assets/plugins/katex/contrib/auto-render.min.js') ?>"
        onload="renderMathInElement(document.body);"></script>
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/app/css/stylised.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/contextmenu/jquery.contextmenu.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/plugins/fields-linker/fieldsLinker.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/plugins/pignose/css/pignose.calendar.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/app/css/new-upload.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/app/css/custom-correction.css') ?>">

    <style>
        page {
            background: white;
            display: block;
            margin: 0 auto;
            margin-bottom: 0.5cm;
            box-shadow: none;
        }

        page[size="A4"][layout="potrait"] {
            width: 21cm;
            height: 29.7cm;
        }

        page[size="A4"] {
            width: 29.7cm;
            height: 21cm;
        }

        .linker-list p {
            margin-bottom: .5rem;
            margin-top: .5rem;
        }
    </style>
</head>
<script>
    let base_url = '<?= base_url() ?>';
    let globalToken;
    let adaJadwalUjian;
    let CSRF_NAME = '<?= csrf_token() ?>';
    let CSRF_HASH = '<?= csrf_hash() ?>';

    $(document).ready(function () {
        const page = '<?= uri_string() ?>';
        // check version omitted or adapted if needed
    });
</script>
<script src="<?= base_url('assets/app/js/generate.js') ?>"></script>
<script type="text/javascript">
    let tp_active = '<?= isset($tp_active) ? $tp_active->tahun : "" ?>';
    let smt_active = '<?= isset($smt_active) ? $smt_active->smt : "" ?>';
    let id_tp_active = '<?= isset($tp_active) ? $tp_active->id_tp : "" ?>';
    let id_smt_active = '<?= isset($smt_active) ? $smt_active->id_smt : "" ?>';

    function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        $('#live-clock').html('<span class="text-lg">' + h + ':' + m + '</span>:' + s);
        setTimeout(startTime, 1000);
    }

    function checkTime(i) {
        if (i < 10) { i = "0" + i }
        return i;
    }

    function buat_tanggal_indonesia(str) {
        if (!str) return "";
        str = str.replace("Jan", "Januari").replace("Feb", "Februari").replace("Mar", "Maret").replace("Apr", "April")
            .replace("May", "Mei").replace("Jun", "Juni").replace("Jul", "Juli").replace("Aug", "Agustus")
            .replace("Sep", "September").replace("Oct", "Oktober").replace("Nov", "Nopember").replace("Dec", "Desember");
        str = str.replace("Mon", "Senin").replace("Tue", "Selasa").replace("Wed", "Rabu").replace("Thu", "Kamis")
            .replace("Fri", "Jumat").replace("Sat", "Sabtu").replace("Sun", "Minggu");
        return str;
    }
</script>

<body class="layout-fixed layout-navbar-fixed layout-footer-fixed sidebar-expand-lg bg-body-tertiary text-sm"
    onload="startTime()">
    <div class="app-wrapper">