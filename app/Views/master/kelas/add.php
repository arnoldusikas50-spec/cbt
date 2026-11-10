<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$jjs = [];
// Jika edit mode dan ada siswakelas, gunakan itu untuk menandai siswa terpilih.
// PENTING: jangan pakai $siswa sebagai variabel loop — $siswa dipakai lagi
// di bawah sebagai daftar seluruh siswa dari controller (termasuk yang belum
// punya rombel). Jika tertimpa, daftar "Siswa Tersedia" jadi kosong.
if (isset($siswakelas) && !empty($siswakelas)) {
    foreach ($siswakelas as $sk) {
        $jjs[] = $sk->id_siswa;
    }
}
// Fallback ke jumlah_siswa dari kelas (jika ada)
if (empty($jjs) && isset($kelas->jumlah_siswa) && !empty($kelas->jumlah_siswa)) {
    $jumlahSiswa = is_string($kelas->jumlah_siswa) ? json_decode($kelas->jumlah_siswa) : $kelas->jumlah_siswa;
    if (is_array($jumlahSiswa)) {
        foreach ($jumlahSiswa as $js) {
            if (isset($js->id)) {
                $jjs[] = $js->id;
            }
        }
    }
}
?>

<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
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

<!-- Main Content -->
<div class="app-content">
    <div class="container-fluid">
        <form id="create">
            <?= csrf_field() ?>
            <?php if (isset($id_kelas)): ?>
                <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">
            <?php endif; ?>

            <div class="card card-primary card-outline shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title fw-bold">
                        <i class="bi bi-door-closed me-2"></i>
                        <?= esc($subjudul ?? 'Form Kelas') ?>
                    </h3>
                    <div class="card-tools">
                        <a href="<?= site_url('datakelas') ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-left me-1"></i>Kembali
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary ms-1">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 row">
                                <label for="nama_kelas" class="col-md-3 col-form-label">Nama Kelas*</label>
                                <div class="col-md-9">
                                    <input type="text" name="nama_kelas" value="<?= $kelas->nama_kelas ?? '' ?>"
                                        id="nama_kelas" class="form-control" placeholder="Contoh: X 1" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="kode_kelas" class="col-md-3 col-form-label">Kode Kelas*</label>
                                <div class="col-md-9">
                                    <input type="text" name="kode_kelas" value="<?= $kelas->kode_kelas ?? '' ?>"
                                        id="kode_kelas" class="form-control" placeholder="Contoh: X-1" required>
                                </div>
                            </div>
                            <?php if ($setting->jenjang == '3'): ?>
                                <div class="mb-3 row">
                                    <label for="jurusan_id" class="col-md-3 col-form-label">Jurusan</label>
                                    <div class="col-md-9">
                                        <?= form_dropdown('jurusan_id', $jurusan, $kelas->jurusan_id ?? '', 'class="form-select select2" id="jurusan_id"') ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 row">
                                <label for="level_id" class="col-md-3 col-form-label">Level*</label>
                                <div class="col-md-9">
                                    <?= form_dropdown('level_id', $level, $kelas->level_id ?? '', 'class="form-select select2" id="level_id" required') ?>
                                </div>
                            </div>
                            <?php if (isset($guru) && is_array($guru) && count($guru) > 0): ?>
                                <div class="mb-3 row">
                                    <label for="guru_id" class="col-md-3 col-form-label">Wali Kelas</label>
                                    <div class="col-md-9">
                                        <?= form_dropdown('guru_id', $guru, $kelas->guru_id ?? '', 'class="form-select select2" id="guru_id"') ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3 fw-bold text-secondary"><i class="bi bi-people me-2"></i>Daftar Siswa</h5>
                            <div class="alert alert-info border-0 shadow-sm mb-3" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill fs-4 me-2"></i>
                                    <div>
                                        <strong>Catatan:</strong> Pilih siswa yang akan masuk dalam kelas ini. Gunakan pencarian untuk mempercepat.
                                    </div>
                                </div>
                            </div>

                            <?php
                            // Daftar siswa dipisah dua:
                            //  - $siswaBelumRombel : siswa aktif yang belum punya rombel pada
                            //    TP/SMT aktif (boleh langsung dimasukkan ke kelas ini),
                            //  - $all_siswa        : gabungan keduanya untuk <select>.
                            // Siswa yang sudah punya rombel LAIN tidak ditampilkan (pindah
                            // kelas dilakukan dari rombel asalnya).
                            $siswaBelumRombel = [];
                            $all_siswa = [];
                            $label = static fn ($s) => $s->nama . ' (' . ($s->nis ?: '-') . ')';

                            if (isset($siswa) && is_array($siswa)) {
                                foreach ($siswa as $s) {
                                    if (!is_object($s) || !isset($s->id_siswa)) {
                                        continue;
                                    }
                                    $idSiswa = (int) $s->id_siswa;
                                    $idKelasSiswa = (int) ($s->id_kelas ?? 0);
                                    $isDiKelasIni = isset($id_kelas) && $idKelasSiswa === (int) $id_kelas;

                                    if ($idKelasSiswa === 0) {
                                        $siswaBelumRombel[$idSiswa] = $label($s);
                                    }
                                    if ($idKelasSiswa === 0 || $isDiKelasIni) {
                                        $all_siswa[$idSiswa] = $label($s);
                                    }
                                }
                            }

                            // Siswa yang sudah tercatat di kelas ini (pasti tampil & terpilih)
                            if (isset($siswakelas) && is_array($siswakelas)) {
                                foreach ($siswakelas as $sk) {
                                    if (is_object($sk) && isset($sk->id_siswa)) {
                                        $all_siswa[(int) $sk->id_siswa] = $label($sk);
                                    }
                                }
                            }

                            // Urutan: yang belum punya rombel dulu agar mudah ditemukan.
                            $idsBelum = array_keys($siswaBelumRombel);
                            $idsLain = array_diff(array_keys($all_siswa), $idsBelum);
                            $jmlBelum = count($idsBelum);
                            ?>

                            <?php if ($jmlBelum > 0): ?>
                                <div class="alert alert-warning border-0 shadow-sm py-2 px-3 mb-2 d-flex flex-wrap align-items-center gap-2">
                                    <i class="bi bi-person-exclamation fs-5"></i>
                                    <div style="font-size:.85rem;">
                                        <strong><?= $jmlBelum ?> siswa belum punya rombel</strong> pada tahun/semester aktif
                                        dan bisa langsung dimasukkan ke kelas ini.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <select name="siswa[]" class="form-control" id="jumlah_siswa" multiple="multiple">
                                <?php foreach ($idsBelum as $id): ?>
                                    <option value="<?= $id ?>" <?= in_array($id, $jjs) ? 'selected' : '' ?>>
                                        <?= $all_siswa[$id] ?> — belum ada rombel
                                    </option>
                                <?php endforeach; ?>
                                <?php foreach ($idsLain as $id): ?>
                                    <option value="<?= $id ?>" <?= in_array($id, $jjs) ? 'selected' : '' ?>>
                                        <?= $all_siswa[$id] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Load Library for MultiSelect -->
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/multiselect/css/multi-select.css') ?>">
<style>
    .ms-container .ms-list {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    .ms-container .ms-selectable li.ms-hover, .ms-container .ms-selection li.ms-hover {
        background-color: var(--bs-primary);
        color: #fff;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/plugins/multiselect/js/jquery.multi-select.js') ?>"></script>
<script src="<?= base_url('assets/plugins/multiselect/js/jquery.quicksearch.js') ?>"></script>

<script>
    // base_url is already defined in main layout
    let s = ""; // To store unselected students

    $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        $('#jumlah_siswa').multiSelect({
            selectableHeader: "<div class='fw-bold mb-2 text-center text-primary bg-body-tertiary py-2 border rounded'>Siswa Tersedia</div><input type='text' class='form-control form-control-sm mb-2' autocomplete='off' placeholder='Cari Siswa...'>",
            selectionHeader: "<div id='totalsiswa' class='fw-bold mb-2 text-center text-success bg-body-tertiary py-2 border rounded'>Siswa Terpilih: 0</div><input type='text' class='form-control form-control-sm mb-2' autocomplete='off' placeholder='Cari Siswa...'>",
            afterInit: function (container) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString).on('keydown', function (e) {
                    if (e.which === 40) {
                        that.$selectableUl.focus();
                        return false;
                    }
                });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString).on('keydown', function (e) {
                    if (e.which == 40) {
                        that.$selectionUl.focus();
                        return false;
                    }
                });

                updateTotal();
            },
            afterSelect: function (values) {
                updateTotal();
                this.qs1.cache();
                this.qs2.cache();
            },
            afterDeselect: function (values) {
                updateTotal();
                this.qs1.cache();
                this.qs2.cache();
            }
        });

        function updateTotal() {
            let count = $('#jumlah_siswa option:selected').length;
            $('#totalsiswa').text("Siswa Terpilih: " + count);

            // Re-calculate unselected
            s = "";
            $.each($('#jumlah_siswa option'), function () {
                if (!$(this).prop('selected')) {
                    s += "&unselect%5B%5D=" + $(this).val();
                }
            });
        }

        $('#create').submit(function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Sedang menyimpan...',
                text: 'Mohon tunggu sejenak',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: base_url + "datakelas/save",
                type: "POST",
                data: $(this).serialize() + s,
                success: function (data) {
                    if (data.status) {
                        Swal.fire({
                            title: "Berhasil",
                            text: "Data kelas berhasil disimpan",
                            icon: "success",
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            window.location.href = base_url + 'datakelas';
                        });
                    } else {
                        Swal.fire({
                            title: "Gagal", 
                            text: data.message || "Gagal menyimpan data", 
                            icon: "warning",
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        title: "Error", 
                        text: "Gagal menghubungi server", 
                        icon: "error",
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>