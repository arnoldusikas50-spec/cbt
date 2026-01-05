<!-- School Management (Server Pusat Only) -->
<main class="app-main pt-4">
    <section class="content">
        <div class="container-fluid">

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="shadow small-box grad-info">
                        <div class="inner">
                            <h3 class="mb-0 text-white">
                                <?= count($schools) ?>
                            </h3>
                            <span class="text-white">Total Sekolah</span>
                        </div>
                        <div class="icon">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="shadow small-box grad-success">
                        <div class="inner">
                            <h3 class="mb-0 text-white">
                                <?= count(array_filter($schools, function ($s) {
                                    return $s->is_active;
                                })) ?>
                            </h3>
                            <span class="text-white">Sekolah Aktif</span>
                        </div>
                        <div class="icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="shadow small-box grad-warning">
                        <div class="inner">
                            <h3 class="mb-0 text-white ">
                                <?= count(array_filter($schools, function ($s) {
                                    return $s->last_sync && strtotime($s->last_sync) > strtotime('-24 hours');
                                })) ?>
                            </h3>
                            <span class="text-white">Sync 24 Jam Terakhir</span>
                        </div>
                        <div class="icon">
                            <i class="bi bi-cloud-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schools Table -->
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-list-ul me-2"></i> Daftar Sekolah Terdaftar</h3>
                    <div class="card-tools ms-auto">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalAddSchool">
                            <i class="bi bi-plus-lg"></i> Tambah Sekolah
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="tableSchools">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>NPSN</th>
                                <th>Nama Sekolah</th>
                                <th>Kota</th>
                                <th>Last Sync</th>
                                <th>Status</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schools as $i => $school): ?>
                                <tr>
                                    <td>
                                        <?= $i + 1 ?>
                                    </td>
                                    <td>
                                        <?= $school->npsn ?: '-' ?>
                                    </td>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($school->nama_sekolah) ?>
                                        </strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= $school->school_uuid ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= $school->kota ?: '-' ?>
                                    </td>
                                    <td>
                                        <?php if ($school->last_sync): ?>
                                            <?= date('d/m/Y H:i', strtotime($school->last_sync)) ?>
                                            <br>
                                            <small class="text-muted">IP:
                                                <?= $school->ip_address ?: '-' ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Belum pernah</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($school->is_active): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-lg"></i> Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= base_url('sync/impersonate/' . $school->school_uuid) ?>"
                                                class="btn btn-purple btn-sm"
                                                onclick="return confirm('Masuk ke dashboard sekolah ini?')"
                                                title="Kelola / Login Admin Sekolah">
                                                <i class="bi bi-box-arrow-in-right"></i> Kelola
                                            </a>
                                            <?php if (isset($school->admin_count) && $school->admin_count > 0): ?>
                                                <button type="button" class="btn btn-outline-warning btn-sm"
                                                    onclick="editAdmin(<?= $school->id ?>, '<?= htmlspecialchars($school->nama_sekolah) ?>')"
                                                    title="Edit Akun Admin">
                                                    <i class="bi bi-person-gear"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="createAdmin(<?= $school->id ?>, '<?= htmlspecialchars($school->nama_sekolah) ?>')"
                                                    title="Buat Akun Admin">
                                                    <i class="bi bi-person-plus-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-outline-info btn-sm"
                                                onclick="showApiKey(<?= $school->id ?>, '<?= $school->api_key ?>')"
                                                title="Lihat API Key">
                                                <i class="bi bi-key"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-outline-<?= $school->is_active ? 'secondary' : 'success' ?> btn-sm"
                                                onclick="toggleStatus(<?= $school->id ?>)"
                                                title="<?= $school->is_active ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                <i
                                                    class="bi bi-<?= $school->is_active ? 'slash-circle' : 'check-lg' ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</main>

<!-- Modal Add School -->
<div class="modal fade" id="modalAddSchool">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="bi bi-building"></i> Tambah Sekolah Baru</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddSchool">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                    value="<?= $this->security->get_csrf_hash() ?>">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NPSN</label>
                                <input type="text" name="npsn" class="form-control"
                                    placeholder="Nomor Pokok Sekolah Nasional">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Sekolah <span class="text-danger">*</span></label>
                                <input type="text" name="nama_sekolah" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kota/Kabupaten</label>
                                <input type="text" name="kota" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Provinsi</label>
                                <input type="text" name="provinsi" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan & Generate API
                        Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Show API Key -->
<div class="modal fade" id="modalShowApiKey">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title"><i class="bi bi-key"></i> API Key</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>API Key</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="displayApiKey" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="copyToClipboard('displayApiKey')">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i>
                    Berikan API Key ini ke administrator server lokal sekolah yang bersangkutan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal New API Credentials -->
<div class="modal fade" id="modalNewCredentials">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title"><i class="bi bi-key"></i> Kredensial API Baru</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Penting!</strong> Simpan kredensial ini dengan aman. API Secret hanya ditampilkan
                    sekali!
                </div>
                <div class="form-group">
                    <label>School UUID</label>
                    <input type="text" class="form-control" id="newSchoolUuid" readonly>
                </div>
                <div class="form-group">
                    <label>API Key</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="newApiKey" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="copyToClipboard('newApiKey')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>API Secret</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="newApiSecret" readonly>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="copyToClipboard('newApiSecret')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="location.reload()">
                    <i class="bi bi-check-lg"></i> Saya Sudah Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Admin -->
<div class="modal fade" id="modalCreateAdmin">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="modalAdminTitle"><i class="bi bi-person-plus-fill"></i> Buat Admin Sekolah
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCreateAdmin">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                    value="<?= $this->security->get_csrf_hash() ?>">
                <input type="hidden" name="school_id" id="adminSchoolId">
                <input type="hidden" name="user_id" id="adminUserId">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <span id="adminActionText">Membuat</span> akun Administrator untuk sekolah: <br>
                        <strong id="adminSchoolName"></strong>
                    </div>
                    <div class="form-group mb-3">
                        <label>Username</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                            </div>
                            <input type="text" name="username" class="form-control" placeholder="Contoh: admin_sekolah1"
                                required autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            </div>
                            <input type="email" name="email" class="form-control" placeholder="email@sekolah.com"
                                required autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            </div>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter"
                                required minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Konfirmasi Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            </div>
                            <input type="password" name="password_confirm" class="form-control"
                                placeholder="Ulangi Password" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?= base_url() ?>';

    $(document).ready(function () {
        $('#tableSchools').DataTable({
            responsive: true,
            language: {
                "sEmptyTable": "Tidak ada data yang tersedia pada tabel ini",
                "sProcessing": "Sedang memproses...",
                "sLengthMenu": "Tampilkan _MENU_ entri",
                "sZeroRecords": "Tidak ditemukan data yang sesuai",
                "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "sInfoPostFix": "",
                "sSearch": "Cari:",
                "sUrl": "",
                "oPaginate": {
                    "sFirst": "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext": "Selanjutnya",
                    "sLast": "Terakhir"
                }
            }
        });

        $('#formAddSchool').on('submit', function (e) {
            e.preventDefault();
            registerSchool();
        });
    });

    function registerSchool() {
        let formData = $('#formAddSchool').serialize();

        Swal.fire({
            title: 'Mendaftarkan Sekolah...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: BASE_URL + 'sync/register_school',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                Swal.close();

                if (res.status === 'success') {
                    $('#modalAddSchool').modal('hide');

                    // Show new credentials
                    $('#newSchoolUuid').val(res.data.school_uuid);
                    $('#newApiKey').val(res.data.api_key);
                    $('#newApiSecret').val(res.data.api_secret);
                    $('#modalNewCredentials').modal('show');

                    $('#formAddSchool')[0].reset();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Gagal mendaftarkan sekolah', 'error');
            }
        });
    }

    function showApiKey(id, apiKey) {
        $('#displayApiKey').val(apiKey);
        $('#modalShowApiKey').modal('show');
    }

    function regenerateKey(id) {
        Swal.fire({
            title: 'Regenerate API Key?',
            text: 'API Key lama akan tidak berlaku lagi. Server lokal harus menggunakan key baru.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Regenerate',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + 'sync/regenerate_key/' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            $('#newSchoolUuid').val('-');
                            $('#newApiKey').val(res.api_key);
                            $('#newApiSecret').val(res.api_secret);
                            $('#modalNewCredentials').modal('show');
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal regenerate API Key', 'error');
                    }
                });
            }
        });
    }

    function createAdmin(id, name) {
        $('#adminSchoolId').val(id);
        $('#adminUserId').val('');
        $('#adminSchoolName').text(name);
        $('#modalAdminTitle').html('<i class="bi bi-person-plus-fill"></i> Buat Admin Sekolah');
        $('#adminActionText').text('Membuat');
        $('#formCreateAdmin')[0].reset();

        // Password Required
        $('input[name="password"]').attr('required', true).attr('placeholder', 'Minimal 8 karakter');
        $('input[name="password_confirm"]').attr('required', true).attr('placeholder', 'Ulangi Password');

        $('#modalCreateAdmin').modal('show');
    }

    function editAdmin(id, name) {
        $('#adminSchoolId').val(id);
        $('#adminSchoolName').text(name);
        $('#modalAdminTitle').html('<i class="bi bi-person-gear"></i> Edit Admin Sekolah');
        $('#adminActionText').text('Mengedit');

        // Password Optional
        $('input[name="password"]').removeAttr('required').attr('placeholder', 'Kosongkan jika tidak diubah');
        $('input[name="password_confirm"]').removeAttr('required').attr('placeholder', 'Kosongkan jika tidak diubah');

        // Load data
        $.ajax({
            url: BASE_URL + 'sync/get_admin_detail/' + id,
            type: 'GET',
            success: function (res) {
                if (res.status === 'success') {
                    $('#adminUserId').val(res.data.id);
                    $('input[name="username"]').val(res.data.username);
                    $('input[name="email"]').val(res.data.email);
                    $('#modalCreateAdmin').modal('show');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    }

    $('#formCreateAdmin').submit(function (e) {
        e.preventDefault();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.html('<i class="spinner-border spinner-border-sm"></i> Menyimpan...').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'sync/save_school_admin',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil',
                        text: res.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: res.message,
                        icon: 'error'
                    });
                    submitBtn.html(originalText).prop('disabled', false);
                }
            },
            error: function () {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    function toggleStatus(id) {
        $.ajax({
            url: BASE_URL + 'sync/toggle_school/' + id,
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Gagal mengubah status', 'error');
            }
        });
    }

    function copyToClipboard(elementId) {
        let element = document.getElementById(elementId);
        element.select();
        document.execCommand('copy');

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Disalin ke clipboard',
            showConfirmButton: false,
            timer: 1500
        });
    }
</script>