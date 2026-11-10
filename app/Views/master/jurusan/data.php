<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>



<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-diagram-3 text-primary me-2"></i>
                    <?= esc($judul ?? 'Data Jurusan') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data Jurusan') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="app-content">
    <div class="container-fluid">

        <!-- Card Data Jurusan -->
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary"><i class="bi bi-diagram-3 me-2"></i>Daftar Jurusan / Program Keahlian</h3>
                <div class="card-tools ms-auto d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-warning" id="btnReload">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </button>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" id="btnTambah">
                        <i class="bi bi-plus me-1"></i> Tambah Jurusan
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- DataTable -->
                <div class="table-responsive">
                    <table id="tableJurusan" class="w-100 table table-striped table-bordered table-hover dataTable">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">Kode Jurusan</th>
                                <th width="25%">Nama Jurusan</th>
                                <th>Mata Pelajaran Peminatan</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data dimuat via DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Tambah/Edit Jurusan -->
<div class="modal fade" id="modalJurusan" tabindex="-1" role="dialog" aria-labelledby="modalJurusanLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalJurusanLabel">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Jurusan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formJurusan">
                <div class="modal-body">
                    <input type="hidden" name="id_jurusan" id="id_jurusan">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="kode_jurusan" class="form-label">Kode Jurusan</label>
                        <input type="text" class="form-control" id="kode_jurusan" name="kode_jurusan"
                            placeholder="Contoh: IPA, IPS, TKJ" maxlength="20">
                    </div>

                    <div class="mb-3">
                        <label for="nama_jurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan" required
                            placeholder="Contoh: Ilmu Pengetahuan Alam">
                    </div>

                    <div class="mb-3">
                        <label for="mapel_peminatan" class="form-label">Mata Pelajaran Peminatan</label>
                        <select class="form-select select2" id="mapel_peminatan" name="mapel_peminatan[]" multiple="multiple">
                            <?php foreach ($mapel_peminatan as $mapel): ?>
                                <option value="<?= $mapel['id_mapel'] ?>"><?= esc($mapel['nama_mapel']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4" id="btnSimpan">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        // =============================================
        // DataTables Initialization
        // =============================================
        var table = $('#tableJurusan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= site_url('datajurusan/data') ?>",
                type: "POST",
                data: function (d) {
                    d.<?= csrf_token() ?> = '<?= csrf_hash() ?>';
                },
                error: function (xhr, error, code) {
                    console.error('DataTables AJAX Error:', error);
                }
            },
            columns: [
                { data: "no", orderable: false, className: "text-center" },
                { data: "kode_jurusan" },
                { data: "nama_jurusan" },
                { data: "mapel_peminatan", orderable: false },
                { data: "status", orderable: false, className: "text-center" },
                { data: "aksi", orderable: false, className: "text-center" }
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            responsive: true,
            autoWidth: false,
            order: [[2, 'asc']]
        });

        // =============================================
        // Select2 Initialization (inside modal)
        // =============================================
        $('#modalJurusan').on('shown.bs.modal', function () {
            if (typeof $.fn.select2 !== 'undefined') {
                $('#mapel_peminatan').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Pilih Mapel Peminatan...',
                    dropdownParent: $('#modalJurusan .modal-content'),
                    allowClear: true
                });
            }
        });

        // =============================================
        // Button: Tambah Jurusan
        // =============================================
        $('#btnTambah').on('click', function () {
            resetForm();
            $('#modalJurusanLabel').html('<i class="bi bi-plus-circle me-2"></i> Tambah Jurusan');
            openModal();
        });

        // =============================================
        // Button: Reload Data
        // =============================================
        $('#btnReload').on('click', function () {
            table.ajax.reload(null, false);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data jurusan dimuat ulang',
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        });

        // =============================================
        // Button: Toggle Status Jurusan (Aktif/Nonaktif)
        // =============================================
        $(document).on('click', '.btn-toggle-status', function () {
            var id = $(this).data('id');
            var status = $(this).data('status');

            $.ajax({
                url: '<?= site_url('datajurusan/toggle_status') ?>',
                type: 'POST',
                data: {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                    id_jurusan: id,
                    status: status
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status) {
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Diperbarui',
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan pada server.'
                    });
                }
            });
        });

        // =============================================
        // Form Submit (Save/Update)
        // =============================================
        $('#formJurusan').on('submit', function (e) {
            e.preventDefault();

            var isEdit = $('#id_jurusan').val() !== '';
            var url = isEdit ? '<?= site_url('datajurusan/update') ?>' : '<?= site_url('datajurusan/save') ?>';
            var formData = new FormData(this);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function () {
                    $('#btnSimpan').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                },
                success: function (response) {
                    if (response.status) {
                        closeModal();
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan pada server.'
                    });
                },
                complete: function () {
                    $('#btnSimpan').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Simpan');
                }
            });
        });

        // =============================================
        // Button: Edit Jurusan
        // =============================================
        $(document).on('click', '.btn-edit', function () {
            var id = $(this).data('id');

            $.ajax({
                url: '<?= site_url('datajurusan/get_data/') ?>' + id,
                type: 'GET',
                dataType: 'json',
                beforeSend: function () {
                    Swal.fire({
                        title: 'Memuat data...',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });
                },
                success: function (response) {
                    Swal.close();
                    if (response.status) {
                        var data = response.data;
                        $('#id_jurusan').val(data.id_jurusan);
                        $('#kode_jurusan').val(data.kode_jurusan);
                        $('#nama_jurusan').val(data.nama_jurusan);

                        if (data.mapel_peminatan && data.mapel_peminatan.length > 0) {
                            var mapelIds = data.mapel_peminatan.map(function (item) {
                                return item.id_mapel;
                            });
                            $('#mapel_peminatan').val(mapelIds).trigger('change');
                        } else {
                            $('#mapel_peminatan').val(null).trigger('change');
                        }

                        $('#modalJurusanLabel').html('<i class="bi bi-pencil-square me-2"></i> Edit Jurusan');
                        openModal();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function (xhr) {
                    Swal.close();
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mengambil data.'
                    });
                }
            });
        });

        // =============================================
        // Button: Hapus Jurusan
        // =============================================
        $(document).on('click', '.btn-hapus', function () {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data jurusan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= site_url('datajurusan/delete') ?>',
                        type: 'POST',
                        data: {
                            <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                            id_jurusan: id
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status) {
                                table.ajax.reload(null, false);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: response.message
                                });
                            }
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Terjadi kesalahan saat menghapus data.'
                            });
                        }
                    });
                }
            });
        });

        function resetForm() {
            $('#formJurusan')[0].reset();
            $('#id_jurusan').val('');
            $('#mapel_peminatan').val(null).trigger('change');
        }

        function openModal() {
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalJurusan'));
            modal.show();
        }

        function closeModal() {
            var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalJurusan'));
            modal.hide();
        }
    });
</script>
<?= $this->endSection() ?>