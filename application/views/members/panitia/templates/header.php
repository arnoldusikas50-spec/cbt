<!DOCTYPE html>
<html>

<head>

    <!-- Meta Tag -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $judul ?></title>
    <meta name="renderer" content="webkit" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" name="viewport">
    <?php $logo_app = $setting->logo_kiri == null ? base_url() . 'assets/img/favicon.png' : base_url() . $setting->logo_kiri; ?>
    <link rel="shortcut icon" href="<?= $logo_app ?>" type="image/x-icon">

    <!-- v3 -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet"
        href="<?= base_url() ?>/assets/plugins/datatables-responsive/css/responsive.bootstrap5.min.css">
    <!-- Tempusdominus Bbootstrap 4 -->
    <!-- Tempusdominus removed due to 404 -->
    <!-- iCheck -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/jqvmap/jqvmap.min.css">

    <!-- Select2 -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet"
        href="<?= base_url() ?>/assets/plugins/select2-bootstrap5-theme/select2-bootstrap-5-theme.min.css">
    <!-- multi select -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/multiselect/css/multi-select.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Datetime picker -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/jquery-datetimepicker/jquery.datetimepicker.min.css">

    <!-- Toastr -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/jquery.toast.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/toastr/toastr.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/dropify/css/dropify.min.css">

    <!-- fonts -->

    <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/show.toast.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/adminlte/dist/css/adminlte.min.css">

    <!-- textarea editor -->
    <!-- summernote -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/summernote/summernote-lite.min.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/summernote/plugin/audio/summernote-audio.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/katex/katex.css">
    <!-- /texarea editor; -->
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/premium_adminlte.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/app/css/font-material.css">

    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/bootstrap-icon/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/plugins/pignose/css/pignose.calendar.css">

    <!-- jQuery -->
    <script src="<?= base_url() ?>/assets/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?= base_url() ?>/assets/plugins/jquery-ui/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/jexcel/css/jexcel.css">
    <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/jexcel/css/jsuites.css">
    <script src="<?= base_url() ?>/assets/plugins/jexcel/js/jexcel.js"></script>
    <script src="<?= base_url() ?>/assets/plugins/jexcel/js/jsuites.js"></script>

    <script defer src="<?= base_url() ?>/assets/plugins/katex/contrib/auto-render.min.js"
        onload="renderMathInElement(document.body);"></script>

    <link rel="stylesheet" type="text/css" href="<?= base_url() ?>/assets/app/css/new-upload.css">
    <style>
        .linker-list p {
            margin-bottom: .5rem;
            margin-top: .5rem;
        }
    </style>
</head>

<script type="text/javascript">
    let reloadCOunt = 0;
    let base_url = '<?= base_url() ?>';

    let globalToken;
    var adaJadwalUjian;

    let timerToken, timerUpdate;
    let timerTokenRemaining, timerTokenOnGoing;
    let forceGenerate = 0;

    function getToken(func) {
        $.ajax({
            url: base_url + "dashboard/checktokenjadwal",
            type: "GET",
            success: function (response) {
                console.log('getToken', response);
                globalToken = response.token;
                adaJadwalUjian = response.ada_ujian;

                createIntervalToken();
                if (func && (typeof func == "function")) {
                    func(response);
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr);
            }
        });
    }

    function createIntervalToken() {
        const durasi = Number(globalToken.jarak);
        const perdetik = 1000;
        const permenit = 60 * perdetik;
        const perjam = 60 * permenit;
        const t_dur = durasi * (1000 * 60);

        let updated = new Date(globalToken.updated);
        let time_c = new Date();
        let time_s = new Date(globalToken.now);
        let diff = time_s - time_c;

        updateViews();

        function updateViews() {
            if (timerToken) {
                clearTimeout(timerToken);
                timerToken = null;
            }
            let now = new Date();
            now.setMilliseconds(now.getMilliseconds() + diff);
            var rem = now.getTime() - updated.getTime();
            const t_remaining = t_dur - rem;

            const viewTimer = $('#interval');
            if (viewTimer.length) viewTimer.addClass('d-none');
            if (viewTimer.length) viewTimer.html(timerTokenRemaining);

            const inputToken = $('#token-input');
            if (inputToken.length) inputToken.val(globalToken.token);

            const viewToken = $('#token-view');
            if (viewToken.length) viewToken.text(globalToken.token);

            const infoTimer = $('#info-interval');
            if (infoTimer.length) infoTimer.addClass('d-none');

            console.log('globalToken', adaJadwalUjian);

            if (globalToken.auto == '1' && adaJadwalUjian > 0) {
                if (infoTimer.length) infoTimer.removeClass('d-none');
                if (viewTimer.length) viewTimer.removeClass('d-none');
                if (t_remaining < 0) {
                    timerTokenRemaining = 'Memuat token...';
                    getToken();
                } else {
                    const r_jam = Math.floor(t_remaining / perjam);
                    const r_mnt = Math.floor((t_remaining % perjam) / permenit);
                    const r_dtk = Math.floor((t_remaining % permenit) / perdetik);

                    timerTokenRemaining = zeroPad(r_jam) + ':' + zeroPad(r_mnt) + ':' + zeroPad(r_dtk);
                    timerToken = setTimeout(updateViews, 1000);
                }
            }
        }
    }

    function zeroPad(no) {
        return no < 10 ? '0' + no : no;
    }

    $(document).ready(function () {
        ajaxcsrf();
        getToken();
        // cek update
        const page = '<?= $this->uri->segment(1) ?>';
        if (page !== 'dashboard') {
            $.ajax({
                url: base_url + "dashboard/check_version",
                type: "GET",
                success: function (response) {
                    if (!response.is_latest) {
                        window.location.href = base_url + 'dashboard/need_update'
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr);
                }
            });
        }
    });

    function logout() {
        Swal.fire({
            title: "Logout",
            text: "Anda yakin ingin logout?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Logout!'
        }).then((result) => {
            if (result.value) {
                location.href = base_url + "logout";
            }
        });
    }

    function startTime() {
        var today = new Date();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);

        //document.getElementById('live-clock').innerHTML = h + ":" + m + ":" + s;
        $('#live-clock').html('<span class="text-lg">' + h + ':' + m + '</span>:' + s);
        setTimeout(startTime, 500);
    }

    function checkTime(i) {
        if (i < 10) {
            i = "0" + i
        }
        ;  // add zero in front of numbers < 10
        return i;
    }

    function buat_tanggal_indonesia(str) {
        str = str.replace("Jan", "Januari");
        str = str.replace("Feb", "Februari");
        str = str.replace("Mar", "Maret");
        str = str.replace("Apr", "April");
        str = str.replace("May", "Mei");
        str = str.replace("Jun", "Juni");
        str = str.replace("Jul", "Juli");
        str = str.replace("Aug", "Agustus");
        str = str.replace("Sep", "September");
        str = str.replace("Oct", "Oktober");
        str = str.replace("Nov", "Nopember");
        str = str.replace("Dec", "Desember");
        str = str.replace("Mon", "Senin");
        str = str.replace("Tue", "Selasa");
        str = str.replace("Wed", "Rabu");
        str = str.replace("Thu", "Kamis");
        str = str.replace("Fri", "Jumat");
        str = str.replace("Sat", "Sabtu");
        str = str.replace("Sun", "Minggu");
        return str;
    }

    function sanitizeJSON(unsanitized) {
        return unsanitized.replace(/\\/g, "\\\\").replace(/\n/g, "\\n").replace(/\r/g, "\\r").replace(/\t/g, "\\t").replace(/\f/g, "\\f").replace(/"/g, "\\\"").replace(/'/g, "\\\'").replace(/\&/g, "\\&");
    }

    var bulans = ['Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    var arrhari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];

    function stringToDate(dateStr) {
        var parts = dateStr.split("-");
        return new Date(parts[0], parts[1] - 1, parts[2])
    }

    function dateToString(date) {
        let year = date.getFullYear();
        //let month = (1 + date.getMonth()).toString().padStart(2, '0');
        const month = date.getMonth();
        let day = date.getDate().toString().padStart(2, '0');
        return day + ' ' + bulans[month] + ' ' + year;
    }

    function dateToStringDay(date) {
        let year = date.getFullYear();
        //let month = (1 + date.getMonth()).toString().padStart(2, '0');
        const month = date.getMonth();
        let day = date.getDate().toString().padStart(2, '0');
        return arrhari[date.getDay()] + ', ' + day + ' ' + bulans[month] + ' ' + year;
    }

    function formatTanggal(date) {
        var dateArray = date?.split(' ');
        if (dateArray == null || dateArray.length < 2) {
            return date;
        } else {
            var year = dateArray[0].split('-')[0],
                month = dateArray[0].split('-')[1],
                day = dateArray[0].split('-')[2],
                hour = dateArray[1].split(':')[0],
                minutes = dateArray[1].split(':')[1];

            var hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

            var d = new Date(month + "/" + day + "/" + year);
            var curr_day = d.getDay();
            var curr_date = d.getDate();

            var curr_month = d.getMonth();
            var curr_year = d.getFullYear();

            return hari[curr_day] + ", " + curr_date + "  " + bulan[curr_month] + " " + curr_year + " " + hour + ":" + minutes;
        }
    }

</script>

<?php

function buat_tanggal($str)
{
    $str = str_replace("Jan", "Januari", $str);
    $str = str_replace("Feb", "Februari", $str);
    $str = str_replace("Mar", "Maret", $str);
    $str = str_replace("Apr", "April", $str);
    $str = str_replace("May", "Mei", $str);
    $str = str_replace("Jun", "Juni", $str);
    $str = str_replace("Jul", "Juli", $str);
    $str = str_replace("Aug", "Agustus", $str);
    $str = str_replace("Sep", "September", $str);
    $str = str_replace("Oct", "Oktober", $str);
    $str = str_replace("Nov", "Nopember", $str);
    $str = str_replace("Dec", "Desember", $str);
    $str = str_replace("Mon", "Senin", $str);
    $str = str_replace("Tue", "Selasa", $str);
    $str = str_replace("Wed", "Rabu", $str);
    $str = str_replace("Thu", "Kamis", $str);
    $str = str_replace("Fri", "Jumat", $str);
    $str = str_replace("Sat", "Sabtu", $str);
    $str = str_replace("Sun", "Minggu", $str);
    return $str;
}

function singkat_tanggal($str)
{
    $str = str_replace("Jan", "Jan", $str);
    $str = str_replace("Feb", "Feb", $str);
    $str = str_replace("Mar", "Mar", $str);
    $str = str_replace("Apr", "Apr", $str);
    $str = str_replace("May", "Mei", $str);
    $str = str_replace("Jun", "Jun", $str);
    $str = str_replace("Jul", "Jul", $str);
    $str = str_replace("Aug", "Aug", $str);
    $str = str_replace("Sep", "Sep", $str);
    $str = str_replace("Oct", "Okt", $str);
    $str = str_replace("Nov", "Nov", $str);
    $str = str_replace("Dec", "Des", $str);
    $str = str_replace("Mon", "Sen", $str);
    $str = str_replace("Tue", "Sel", $str);
    $str = str_replace("Wed", "Rab", $str);
    $str = str_replace("Thu", "Kam", $str);
    $str = str_replace("Fri", "Jum", $str);
    $str = str_replace("Sat", "Sab", $str);
    $str = str_replace("Sun", "Min", $str);
    return $str;
}

?>

<body class="layout-fixed layout-navbar-fixed layout-footer-fixed sidebar-expand-lg bg-body-tertiary text-sm"
    spellcheck="false" onload="startTime()">
    <div class="app-wrapper">

        <!-- Navbar -->
        <?php require_once("navbar.php"); ?>

        <!-- Sidebar -->
        <?php require_once("sidebar.php"); ?>
        <!-- /.sidebar -->