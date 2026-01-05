<main class="app-main bg-white">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $title ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <?php foreach ($breadcrumb as $item): ?>
                            <li class="breadcrumb-item">
                                <?php if (isset($item['link'])): ?>
                                    <a href="<?= $item['link'] ?>"><?= $item['title'] ?></a>
                                <?php else: ?>
                                    <?= $item['title'] ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-file-alt"></i> <?= $subtitle ?>
                    </h3>
                    <div class="card-tools ms-auto">
                        <button type="button" class="btn btn-tool btn-sm" data-card-widget="collapse"
                            data-bs-toggle="tooltip" title="Collapse">
                            <i class="bi bi-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool btn-sm" data-card-widget="maximize"
                            data-bs-toggle="tooltip" title="Maximize">
                            <i class="bi bi-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                                <i class="bi bi-plus"></i> Tambah Jenis Dokumen
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table-jenis-dokumen" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Kode</th>
                                    <th>Nama Jenis</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modalFormLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalFormLabel">Form Jenis Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <form id="form-jenis-dokumen">
                    <div class="modal-body">
                        <input type="hidden" name="id_jenis_dokumen" id="id_jenis_dokumen">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="kode_jenis">Kode Jenis <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="kode_jenis"
                                        name="kode_jenis" maxlength="20" placeholder="Contoh: BA" required>
                                    <small class="form-text text-muted">Maksimal 20 karakter, akan otomatis huruf
                                        besar</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nama_jenis">Nama Jenis <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="nama_jenis"
                                        name="nama_jenis" maxlength="100" placeholder="Contoh: Berita Acara" required>
                                    <small class="form-text text-muted">Maksimal 100 karakter</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control form-control-sm" id="deskripsi" name="deskripsi"
                                        rows="3" maxlength="500" placeholder="Deskripsi jenis dokumen..."></textarea>
                                    <small class="form-text text-muted">Maksimal 500 karakter</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn-save">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus jenis dokumen ini?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Batal
                    </button>
                    <button type="button" class="btn btn-danger" id="btn-confirm-delete">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let table;
            let base_url = '<?= base_url(); ?>';

            // Initialize DataTable
            table = $('#table-jenis-dokumen').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: base_url + 'jenis_dokumen/data',
                    type: 'POST'
                },
                columns: [
                    { data: 'id_jenis_dokumen', name: 'id_jenis_dokumen', className: 'text-center', orderable: false },
                    { data: 'kode_jenis', name: 'kode_jenis' },
                    { data: 'nama_jenis', name: 'nama_jenis' },
                    { data: 'deskripsi', name: 'deskripsi' },
                    { data: 'status', name: 'is_active', className: 'text-center', orderable: false },
                    { data: 'action', name: 'action', className: 'text-center', orderable: false }
                ],
                order: [[1, 'asc']],
                pageLength: 25,
                responsive: true,
                language: {
                    url: base_url + 'assets/plugins/datatables/indonesia.json'
                }
            });

            // Add button click
            $('#btn-add').click(function () {
                resetForm();
                $('#modalFormLabel').text('Tambah Jenis Dokumen');
                $('#modal-form').modal('show');
            });

            // Edit button click
            $(document).on('click', '.btn-edit', function () {
                let id = $(this).data('id');

                $.ajax({
                    url: base_url + 'jenis_dokumen/edit/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            $('#modalFormLabel').text('Edit Jenis Dokumen');
                            $('#id_jenis_dokumen').val(response.data.id_jenis_dokumen);
                            $('#kode_jenis').val(response.data.kode_jenis);
                            $('#nama_jenis').val(response.data.nama_jenis);
                            $('#deskripsi').val(response.data.deskripsi);
                            $('#modal-form').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat mengambil data'
                        });
                    }
                });
            });

            // Toggle status button click
            $(document).on('click', '.btn-toggle', function () {
                let id = $(this).data('id');

                $.ajax({
                    url: base_url + 'jenis_dokumen/toggle_status/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat mengubah status'
                        });
                    }
                });
            });

            // Delete button click
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                $('#btn-confirm-delete').data('id', id);
                $('#modal-delete').modal('show');
            });

            // Confirm delete
            $('#btn-confirm-delete').click(function () {
                let id = $(this).data('id');

                $.ajax({
                    url: base_url + 'jenis_dokumen/delete/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $('#modal-delete').modal('hide');
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        $('#modal-delete').modal('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menghapus data'
                        });
                    }
                });
            });

            // Form submit
            $('#form-jenis-dokumen').submit(function (e) {
                e.preventDefault();

                let id = $('#id_jenis_dokumen').val();
                let url = id ? base_url + 'jenis_dokumen/update' : base_url + 'jenis_dokumen/add';

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            $('#modal-form').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                        } else {
                            if (response.errors) {
                                // Show field-specific errors
                                Object.keys(response.errors).forEach(function (key) {
                                    let errorElement = $('[name="' + key + '"]').next('.error-message');
                                    if (errorElement.length === 0) {
                                        errorElement = $('<div class="error-message text-danger small"></div>');
                                        $('[name="' + key + '"]').after(errorElement);
                                    }
                                    errorElement.html(response.errors[key]);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menyimpan data'
                        });
                    }
                });
            });

            // Reset form function
            function resetForm() {
                $('#form-jenis-dokumen')[0].reset();
                $('#id_jenis_dokumen').val('');
                $('.error-message').remove();
            }

            // Clear errors on input
            $('input, textarea').on('input', function () {
                $(this).next('.error-message').remove();
            });

            // Auto uppercase kode_jenis
            $('#kode_jenis').on('input', function () {
                $(this).val($(this).val().toUpperCase());
            });
        });
    </script>