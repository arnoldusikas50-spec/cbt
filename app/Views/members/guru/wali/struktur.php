<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form id="form-struktur" action="<?= base_url('walistruktur/save') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">

                    <div class="card card-default my-shadow border-0">
                        <div class="card-header bg-gradient-primary text-white py-3 d-flex align-items-center">
                            <h3 class="card-title fw-bold">
                                <i class="bi bi-diagram-3-fill me-2"></i>
                                <?= $subjudul ?>
                            </h3>
                            <div class="ms-auto">
                                <button type="submit" class="btn btn-sm btn-light rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <?php
                                $inputs = [
                                    'ketua' => 'Ketua Kelas',
                                    'wakil_ketua' => 'Wakil Ketua',
                                    'sekretaris_1' => 'Sekretaris 1',
                                    'sekretaris_2' => 'Sekretaris 2',
                                    'bendahara_1' => 'Bendahara 1',
                                    'bendahara_2' => 'Bendahara 2',
                                    'sie_ekstrakurikuler' => 'Sie Ekstrakurikuler',
                                    'sie_upacara' => 'Sie Upacara',
                                    'sie_olahraga' => 'Sie Olahraga',
                                    'sie_keagamaan' => 'Sie Keagamaan',
                                    'sie_keamanan' => 'Sie Keamanan',
                                    'sie_ketertiban' => 'Sie Ketertiban',
                                    'sie_kebersihan' => 'Sie Kebersihan',
                                    'sie_keindahan' => 'Sie Keindahan',
                                    'sie_kesehatan' => 'Sie Kesehatan',
                                    'sie_kekeluargaan' => 'Sie Kekeluargaan',
                                    'sie_humas' => 'Sie Humas'
                                ];

                                foreach ($inputs as $id => $label): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group mb-0">
                                            <label class="form-label small fw-bold text-muted mb-2">
                                                <i class="bi bi-person-fill me-1"></i>
                                                <?= $label ?>
                                            </label>
                                            <select name="<?= $id ?>" id="<?= $id ?>" class="form-select select2"
                                                data-placeholder="Pilih <?= $label ?>">
                                                <?php foreach ($siswas as $val => $txt): ?>
                                                    <option value="<?= $val ?>" <?= (isset($struktur->$id) && $struktur->$id == $val) ? 'selected' : '' ?>>
                                                        <?= $txt ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-light p-3 text-center d-md-none">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#form-struktur').on('submit', function (e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            var oldText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: $(this).serialize(),
                success: function (data) {
                    if (data) {
                        Swal.fire({
                            title: "Berhasil",
                            text: "Struktur organisasi kelas telah disimpan",
                            icon: "success",
                            confirmButtonColor: "#3085d6"
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Gagal", "Data tidak berhasil disimpan", "error");
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    Swal.fire("Error", "Terjadi kesalahan pada server", "error");
                },
                complete: function () {
                    btn.prop('disabled', false).html(oldText);
                }
            });
        });
    });
</script>

<style>
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.5rem;
        border-color: #dee2e6;
        padding-top: 0.2rem;
        padding-bottom: 0.2rem;
    }

    .form-label {
        font-size: 0.85rem;
    }
</style>

<?= $this->endSection() ?>