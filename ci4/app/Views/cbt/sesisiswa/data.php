<?php
$jenjang = $setting->jenjang ?? 1;
?>
<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= esc($judul) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('cbtcetak') ?>">Cetak</a></li>
                        <li class="breadcrumb-item active"><?= esc($subjudul) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title">
                        <?= esc($subjudul) ?>
                    </h6>
                    <div class="card-tools ms-auto">
                        <a type="button" href="<?= site_url('cbtsesisiswa?kls=' . $kelas_selected) ?>"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
                        <strong>Penting!</strong>
                        <ol class="mb-0">
                            <li>Atur <b>Ruang</b> dan <b>Sesi</b> harus dilakukan <b>sebelum pengaturan Alokasi Waktu
                                    dan Pengawas</b>.</li>
                            <?php if ($is_admin): ?>
                                <li>Anda harus <b>mengatur ulang Alokasi Waktu dan Pengawas</b> jika sebelumnya sudah
                                    diatur.</li>
                            <?php else: ?>
                                <li>Anda harus <b>mengatur ulang Alokasi Waktu</b> jika sebelumnya sudah diatur.</li>
                                <li>Guru Pengampu otomatis akan menjadi pengawas.</li>
                            <?php endif; ?>
                        </ol>
                    </div>

                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-12 col-md-3">
                            <label class="form-label">Pilih Kelas:</label>
                            <select id="dropdown-kelas" class="form-select select2">
                                <option value="0">Pilih Kelas</option>
                                <?php foreach ($kelas as $k): ?>
                                    <option value="<?= $k->id_kelas ?>" <?= $kelas_selected == $k->id_kelas ? 'selected' : '' ?>>
                                        <?= esc($k->nama_kelas) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (count($siswas) > 0): ?>
                            <div class="col-12 col-md-9 mt-3 mt-md-0 border-start ps-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Set Global (Semua Siswa Terpilih):</label>
                                    <button type="button" id="undo" class="btn btn-xs btn-outline-danger">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Undo
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <select id="g-ruang" class="form-select select2">
                                            <option value="0">Pilih Ruang</option>
                                            <?php foreach ($ruang as $r): ?>
                                                <option value="<?= $r->id_ruang ?>">
                                                    <?= esc($r->nama_ruang) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <select id="g-sesi" class="form-select select2">
                                            <option value="0">Pilih Sesi</option>
                                            <?php foreach ($sesi as $s): ?>
                                                <option value="<?= $s->id_sesi ?>">
                                                    <?= esc($s->nama_sesi) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($siswas) > 0): ?>
                        <div id="atur-by-siswa">
                            <form id="editsesisiswa">
                                <?= csrf_field() ?>
                                <div class="table-responsive mb-3">
                                    <table id="sesi-siswa" class="table table-sm table-bordered table-striped align-middle">
                                        <thead class="table-primary text-center">
                                            <tr>
                                                <th width="50">No.</th>
                                                <th>Nama Siswa</th>
                                                <th>NIS/NISN</th>
                                                <th width="120">Kelas</th>
                                                <th width="200">Ruang</th>
                                                <th width="200">Sesi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($siswas as $idx => $s): ?>
                                                <tr>
                                                    <td class="text-center font-monospace small">
                                                        <?= $idx + 1 ?>
                                                    </td>
                                                    <td>
                                                        <?= esc($s->nama) ?>
                                                    </td>
                                                    <td class="text-center small text-muted">
                                                        <?= esc($s->nis) ?>/
                                                        <?= esc($s->nisn) ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        foreach ($kelas as $k) {
                                                            if ($k->id_kelas == $kelas_selected)
                                                                echo esc($k->nama_kelas);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="px-2">
                                                        <select
                                                            name="ruang-sesi[<?= $s->siswa_id ?>][<?= $kelas_selected ?>][ruang]"
                                                            class="form-select form-select-sm ruangsiswa"
                                                            data-ruangid="<?= $s->siswa_id ?>">
                                                            <option value="0">Pilih Ruang</option>
                                                            <?php foreach ($ruang as $r): ?>
                                                                <option value="<?= $r->id_ruang ?>" <?= $s->ruang_id == $r->id_ruang ? 'selected' : '' ?>>
                                                                    <?= esc($r->nama_ruang) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td class="px-2">
                                                        <select
                                                            name="ruang-sesi[<?= $s->siswa_id ?>][<?= $kelas_selected ?>][sesi]"
                                                            class="form-select form-select-sm sesisiswa"
                                                            data-sesiid="<?= $s->siswa_id ?>">
                                                            <option value="0">Pilih Sesi</option>
                                                            <?php foreach ($sesi as $se): ?>
                                                                <option value="<?= $se->id_sesi ?>" <?= $s->sesi_id == $se->id_sesi ? 'selected' : '' ?>>
                                                                    <?= esc($se->nama_sesi) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end mb-5">
                                    <button type="submit" class="btn btn-primary px-4 shadow">
                                        <i class="bi bi-save me-2"></i> Simpan Pengaturan
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning border-0 shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= $kelas_selected == '0' ? 'Pilih kelas terlebih dahulu untuk melihat data siswa.' : 'Belum ada data siswa untuk kelas ini.' ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="overlay-loading"
                    class="overlay d-none position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center rounded bg-white bg-opacity-75"
                    style="z-index: 1000;">
                    <button class="btn btn-primary shadow" type="button" disabled>
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Memproses Data...
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    var jsonRuang = [];
    var jsonSesi = [];

    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        $('#dropdown-kelas').on('change', function () {
            var id = $(this).val();
            if (id != '0') {
                $('#overlay-loading').removeClass('d-none');
                window.location.href = base_url + 'cbtsesisiswa?kls=' + id;
            }
        });

        $('#g-ruang').on('change', function () {
            var val = $(this).val();
            const $ruangSelect = $('.ruangsiswa');
            if (jsonRuang.length === 0) {
                $ruangSelect.each((i, row) => {
                    jsonRuang.push({ id: $(row).data('ruangid'), val: $(row).val() });
                });
            }
            $ruangSelect.val(val).trigger('change');
        });

        $('#g-sesi').on('change', function () {
            var val = $(this).val();
            const $sesiSelect = $('.sesisiswa');
            if (jsonSesi.length === 0) {
                $sesiSelect.each((i, row) => {
                    jsonSesi.push({ id: $(row).data('sesiid'), val: $(row).val() });
                });
            }
            $sesiSelect.val(val).trigger('change');
        });

        $('#undo').on('click', function () {
            if (jsonRuang.length > 0) {
                $.each(jsonRuang, function (i, v) {
                    $('select[data-ruangid="' + v.id + '"]').val(v.val).trigger('change');
                });
            }
            if (jsonSesi.length > 0) {
                $.each(jsonSesi, function (i, v) {
                    $('select[data-sesiid="' + v.id + '"]').val(v.val).trigger('change');
                });
            }
            jsonRuang = [];
            jsonSesi = [];
        });

        $("#editsesisiswa").on("submit", function (e) {
            e.preventDefault();
            $('#overlay-loading').removeClass('d-none');

            $.post(base_url + "cbtsesisiswa/editsesisiswa", $(this).serialize() + "&kelas=" + $('#dropdown-kelas').val(), function (res) {
                $('#overlay-loading').addClass('d-none');
                if (res.status) {
                    showSuccessToast('Pengaturan ruang dan sesi berhasil disimpan');
                } else {
                    showDangerToast('Gagal menyimpan data');
                }
            }).fail(function () {
                $('#overlay-loading').addClass('d-none');
                showDangerToast('Terjadi kesalahan pada server');
            });
        });
    });
</script>