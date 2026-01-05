</main>
<!-- /.app-main -->

<!-- Main Footer -->
<footer class="app-footer bg-light border-top">
    <strong>CBT</strong> [v4.0-CI4] | AKM Migration
    <div class="float-end d-none d-sm-inline-block">
        <strong>PHP version :</strong> <?= phpversion() ?>
    </div>
</footer>

</div>
<!-- ./app-wrapper -->

<!-- Required JS -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap5.min.js') ?>"></script>

<!-- Datatables Buttons -->
<script src="<?= base_url('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/pdfmake/pdfmake.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/pdfmake/vfs_fonts.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>

<!-- Sparkline -->
<script src="<?= base_url('assets/plugins/sparklines/sparkline.js') ?>"></script>

<!-- Bootstrap 5 -->
<script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- ChartJS -->
<script src="<?= base_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<!-- moment -->
<script src="<?= base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/moment/moment-with-locales.min.js') ?>"></script>
<!-- Tempusdominus Bootstrap 5 -->
<script src="<?= base_url('assets/plugins/tempus-dominus/tempus-dominus.min.js') ?>"></script>
<!-- Summernote -->
<script src="<?= base_url('assets/plugins/summernote/summernote-lite.min.js') ?>"></script>

<!-- overlayScrollbars -->
<script src="<?= base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>
<!-- Toastr -->
<script src="<?= base_url('assets/plugins/toastr/toastr.min.js') ?>"></script>
<!-- Select2 -->
<script src="<?= base_url('assets/plugins/select2/js/select2.full.min.js') ?>"></script>
<!-- multi select -->
<script src="<?= base_url('assets/plugins/multiselect/js/jquery.multi-select.js') ?>"></script>

<!-- AdminLTE App -->
<script src="<?= base_url('assets/adminlte/dist/js/adminlte.js') ?>"></script>
<!-- datetimepicker -->
<script src="<?= base_url('assets/plugins/jquery-datetimepicker/jquery.datetimepicker.full.js') ?>"></script>
<!-- TimeAgo -->
<script src="<?= base_url('assets/plugins/jquery-timeago/jquery.timeago.js') ?>"></script>
<!-- App JS -->
<script src="<?= base_url('assets/app/js/show.toast.js') ?>"></script>

<script>
    function ajaxcsrf() {
        var csrf = {};
        csrf['<?= csrf_token() ?>'] = '<?= csrf_hash() ?>';
        $.ajaxSetup({
            "data": csrf
        });
    }

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
                location.href = "<?= site_url('auth/logout') ?>";
            }
        });
    }

    $(document).ready(function () {
        <?php if (session()->getFlashdata('success')): ?>
            showSuccessToast('<?= session()->getFlashdata('success') ?>');
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            showDangerToast('<?= session()->getFlashdata('error') ?>');
        <?php endif; ?>
    });

    function showToast(type, msg) {
        if (type === 'success') showSuccessToast(msg);
        else if (type === 'error') showDangerToast(msg);
        else showInfoToast(msg);
    }
</script>
</body>

</html>
