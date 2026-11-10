<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$searchId = array_search('1', array_column($tp, 'active'));
?>

<div class="content-header bg-white">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $judul ?></h1>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('datakelas') ?>" class="btn btn-sm btn-danger shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4">
                <div class="card-header py-2 bg-transparent">
                    <h6 class="mb-0 fw-bold"><?= $subjudul ?></h6>
                </div>
                <div class="card-body">
                    <?php if ($tpBefore): ?>
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Fitur Kenaikan Kelas</h6>
                            <p class="mb-2">Gunakan fitur ini untuk menaikkan siswa dari Tahun Pelajaran:
                                <b><?= $tpBefore->tahun ?></b> ke <b><?= $tp_active->tahun ?></b>.</p>
                            <ul class="mb-0 small">
                                <li>Pilih kelas asal dari TP sebelumnya.</li>
                                <li>Pilih mode kenaikan (Persiswa, Perkelas, atau Semua Kelas).</li>
                                <li>Siswa yang di rapor ditandai "Tidak Naik" akan tetap di kelas lamanya secara otomatis.
                                </li>
                            </ul>
                        </div>

                        <div class="row align-items-end mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Kelas TP <?= $tpBefore->tahun ?></label>
                                <select id="opsi-kelas-lama" class="form-select select2">
                                    <option value="" disabled <?= empty($kelas_selected) ? 'selected' : '' ?>>Pilih Kelas
                                        Asal...</option>
                                    <?php foreach ($kelas_lama as $id => $name): ?>
                                        <option value="<?= $id ?>" <?= $id == $kelas_selected ? 'selected' : '' ?>><?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if (!empty($siswas) && !empty($kelases)): ?>
                                <div class="col-md-8 text-end">
                                    <label class="form-label fw-bold small d-block">Mode Kenaikan</label>
                                    <div id="mode-selector" class="btn-group shadow-sm">
                                        <button type="button"
                                            class="btn btn-outline-primary <?= ($type_selected ?? 'persiswa') == 'persiswa' ? 'active' : '' ?>"
                                            data-mode="persiswa">Persiswa</button>
                                        <button type="button"
                                            class="btn btn-outline-primary <?= ($type_selected ?? '') == 'perkelas' ? 'active' : '' ?>"
                                            data-mode="perkelas">Perkelas</button>
                                        <button type="button"
                                            class="btn btn-outline-primary <?= ($type_selected ?? '') == 'allkelas' ? 'active' : '' ?>"
                                            data-mode="allkelas">Semua Kelas</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($kelas_selected)): ?>
                            <div class="tab-content">
                                <!-- Tab Persiswa -->
                                <div id="tab-persiswa"
                                    class="mode-tab <?= ($type_selected ?? 'persiswa') == 'persiswa' ? '' : 'd-none' ?>">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover" id="tbl-siswa">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-center align-middle" style="width: 50px">No</th>
                                                    <th class="text-center align-middle" style="width: 150px">NIS / NISN</th>
                                                    <th class="align-middle px-3">Nama Siswa</th>
                                                    <th class="text-center align-middle">Status Rapor</th>
                                                    <th class="text-center align-middle" style="width: 250px">
                                                        Target Kelas Baru
                                                        <select class="form-select form-select-sm mt-1" id="opsi-all-kelas">
                                                            <option value="0">Set Semua...</option>
                                                            <?php foreach ($kelases as $id => $name): ?>
                                                                <option value="<?= $id ?>"><?= $name ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                foreach ($siswas as $siswa):
                                                    $promoted = $siswa_kelas_baru[$siswa->id_siswa] ?? null;
                                                    $is_naik = $siswa->naik ?? '1';
                                                    ?>
                                                    <tr data-id="<?= $siswa->id_siswa ?>">
                                                        <td class="text-center align-middle"><?= $no++ ?></td>
                                                        <td class="text-center align-middle small"><?= $siswa->nis ?> /
                                                            <?= $siswa->nisn ?></td>
                                                        <td class="align-middle px-3 fw-semibold"><?= $siswa->nama ?></td>
                                                        <td class="text-center align-middle">
                                                            <?php if ($is_naik == '1'): ?>
                                                                <span class="badge bg-success">Naik</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">TIDAK NAIK</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?php if ($is_naik == '0'): ?>
                                                                <input type="text" class="form-control form-control-sm bg-light"
                                                                    value="Tetap di <?= $siswa->nama_kelas ?>" disabled>
                                                                <input type="hidden" class="sel-target-kelas"
                                                                    value="<?= $siswa->id_kelas ?>">
                                                            <?php else: ?>
                                                                <select class="form-select form-select-sm sel-target-kelas">
                                                                    <option value="0">Pilih Kelas...</option>
                                                                    <?php foreach ($kelases as $id => $name): ?>
                                                                        <option value="<?= $id ?>" <?= ($promoted && $promoted->nama_kelas == $name) ? 'selected' : '' ?>><?= $name ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-primary px-5 shadow-sm btn-save-promotion"
                                            data-mode="persiswa">
                                            <i class="bi bi-save me-1"></i> Simpan Kenaikan Persiswa
                                        </button>
                                    </div>
                                </div>

                                <!-- Tab Perkelas -->
                                <div id="tab-perkelas"
                                    class="mode-tab <?= ($type_selected ?? '') == 'perkelas' ? '' : 'd-none' ?>">
                                    <div class="alert alert-warning border-0 shadow-sm mb-4">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Fitur ini akan menaikkan <b>SELURUH SISWA</b> di kelas ini yang status rapor-nya "Naik"
                                        ke kelas baru yang sama.
                                    </div>
                                    <div class="row justify-content-center">
                                        <div class="col-md-6 border rounded p-4 bg-light">
                                            <label class="form-label fw-bold">Naikkan Seluruh Siswa ke Kelas:</label>
                                            <select id="bulk-target-kelas" class="form-select select2">
                                                <option value="0">Pilih Kelas Baru...</option>
                                                <?php foreach ($kelases as $id => $name): ?>
                                                    <option value="<?= $id ?>"><?= $name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button"
                                                class="btn btn-primary w-100 mt-3 shadow-sm btn-save-promotion"
                                                data-mode="perkelas">
                                                <i class="bi bi-save me-1"></i> Simpan Kenaikan Perkelas
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab Semua Kelas -->
                                <div id="tab-allkelas"
                                    class="mode-tab <?= ($type_selected ?? '') == 'allkelas' ? '' : 'd-none' ?>">
                                    <div class="table-responsive">
                                        <form id="form-allkelas">
                                            <table class="table table-bordered table-striped">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="text-center align-middle" style="width: 50px">No</th>
                                                        <th class="align-middle px-3">Kelas TP <?= $tpBefore->tahun ?></th>
                                                        <th class="align-middle px-3">Target Kelas TP <?= $tp_active->tahun ?>
                                                        </th>
                                                        <th class="text-center align-middle" style="width: 80px">
                                                            Pilih<br>
                                                            <input type="checkbox" class="form-check-input"
                                                                id="check-all-kelas">
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $no = 1;
                                                    foreach ($kelas_lama as $id_lama => $name_lama):
                                                        $id_baru = $all_kelas_baru[$id_lama] ?? '0';
                                                        $name_baru = $kelases[$id_baru] ?? '-';
                                                        ?>
                                                        <tr>
                                                            <td class="text-center align-middle"><?= $no++ ?></td>
                                                            <td class="align-middle px-3"><?= $name_lama ?></td>
                                                            <td class="align-middle px-3 fw-bold">
                                                                <?= $name_baru != '-' ? $name_baru : '?? (Level Tidak Sesuai)' ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php if ($id_baru != '0'): ?>
                                                                    <input type="checkbox" name="kelas_baru[<?= $id_lama ?>]"
                                                                        value="<?= $id_baru ?>"
                                                                        class="form-check-input check-per-kelas">
                                                                <?php else: ?>
                                                                    <i class="bi bi-x-circle text-danger"></i>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </form>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-primary px-5 shadow-sm btn-save-promotion"
                                            data-mode="allkelas">
                                            <i class="bi bi-save me-1"></i> Simpan Semua Kelas Terpilih
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted fst-italic border rounded bg-light">
                                <i class="bi bi-arrow-up-circle fs-1 d-block mb-3"></i>
                                Silakan pilih kelas asal terlebih dahulu untuk memulai proses kenaikan.
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            Tidak ada data Tahun Pelajaran sebelumnya. Proses kenaikan tidak dapat dilakukan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const base_url = '<?= base_url() ?>/';
    const klsSelected = '<?= $kelas_selected ?? '' ?>';
    let currentMode = '<?= $type_selected ?? 'persiswa' ?>';

    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap-5' });

        $('#opsi-kelas-lama').change(function () {
            let id = $(this).val();
            window.location.href = `${base_url}datakelas/kenaikan?kelas=${id}&type=${currentMode}`;
        });

        $('#mode-selector button').click(function () {
            let mode = $(this).data('mode');
            currentMode = mode;
            $('#mode-selector button').removeClass('active');
            $(this).addClass('active');
            $('.mode-tab').addClass('d-none');
            $(`#tab-${mode}`).removeClass('d-none');
        });

        $('#opsi-all-kelas').change(function () {
            let val = $(this).val();
            $('.sel-target-kelas').not(':disabled').val(val);
        });

        $('#check-all-kelas').change(function () {
            $('.check-per-kelas').prop('checked', this.checked);
        });

        $('.btn-save-promotion').click(function () {
            let mode = $(this).data('mode');
            let data = {};

            if (mode === 'persiswa') {
                let items = [];
                $('#tbl-siswa tbody tr').each(function () {
                    let idSiswa = $(this).data('id');
                    let targetKls = $(this).find('.sel-target-kelas').val();
                    let targetName = $(this).find('.sel-target-kelas option:selected').text();

                    if (targetKls != "0") {
                        items.push({
                            id_siswa: idSiswa,
                            nama_kls_baru: targetName,
                            id_kls_lama: klsSelected
                        });
                    }
                });
                if (items.length === 0) return Swal.fire("Peringatan", "Pilih minimal satu kelas baru untuk siswa!", "warning");
                data = { mode: 'persiswa', kelas: JSON.stringify(items) };
            }
            else if (mode === 'perkelas') {
                let targetId = $('#bulk-target-kelas').val();
                let targetName = $('#bulk-target-kelas option:selected').text();
                if (targetId == "0") return Swal.fire("Peringatan", "Pilih kelas target!", "warning");

                let items = [];
                $('#tbl-siswa tbody tr').each(function () {
                    items.push({
                        id_siswa: $(this).data('id'),
                        nama_kls_baru: targetName,
                        id_kls_lama: klsSelected
                    });
                });
                data = { mode: 'perkelas', kelas: JSON.stringify(items) };
            }
            else if (mode === 'allkelas') {
                if ($('.check-per-kelas:checked').length === 0) return Swal.fire("Peringatan", "Pilih minimal satu kelas!", "warning");
                data = $('#form-allkelas').serialize();
                saveAllKelas(data);
                return;
            }

            savePromotion(data);
        });

        function savePromotion(dataPost) {
            Swal.fire({
                title: 'Sedang memproses...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: base_url + 'datakelas/naikkelas',
                type: "POST",
                data: dataPost,
                success: function (res) {
                    if (res) {
                        Swal.fire("Berhasil", "Data kenaikan berhasil disimpan", "success").then(() => location.reload());
                    } else {
                        Swal.fire("Gagal", "Gagal menyimpan data", "error");
                    }
                }
            });
        }

        function saveAllKelas(dataPost) {
            Swal.fire({
                title: "Naikkan Semua Kelas?",
                text: "Siswa di kelas terpilih akan dinaikkan ke kelas baru secara otomatis.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Naikkan!"
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: base_url + 'datakelas/naikkan_semua',
                        type: "POST",
                        data: dataPost,
                        success: function (res) {
                            Swal.fire("Berhasil", res.res + " siswa berhasil dinaikkan", "success").then(() => location.reload());
                        }
                    });
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .mode-tab.d-none {
        display: none !important;
    }
</style>
<?= $this->endSection() ?>