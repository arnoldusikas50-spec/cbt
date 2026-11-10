<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$satuan = [
    "1" => ["I ~ V", "VI"],
    "2" => ["VII-VIII", "IX"],
    "3" => [
        "X-XI",
        "XII"
    ]
];
?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark"><?= esc($judul ?? 'Halaman') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>" class="text-decoration-none"><i
                                class="bi bi-house-door me-1"></i>Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm mb-4 position-relative">
            <div class="card-body">
                <div class="alert alert-info align-content-center" role="alert">
                    <span><b>KKM</b></span>
                    <ul>
                        <li>KKM Tunggal: mengatur semua mapel mempunyai KKM yang sama</li>
                        <li>Total BOBOT harus 100</li>
                        <li>Jangan lupa untuk menyimpan perubahan</li>
                    </ul>
                    <span><b>TAMPILKAN NIP</b></span>
                    <ul class="m-0">
                        <li>Pilih <b>YA</b> jika NIP kepala sekolah / walikelas diisi NIP</li>
                        <li>Jika NIP kepala sekolah / walikelas diisi NUPTK atau nomor lain selain NIP maka sebaiknya
                            pilih <b>TIDAK</b> ditampilkan</li>
                    </ul>
                </div>
                <hr>
                <?= form_open('rapor/saveraporadmin', array('id' => 'editsetting'), array('id_setting' => $rapor != null ? $rapor->id_setting : '')) ?>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">Tgl Rapor PTS</label>
                            <div class="col-7">
                                <input type="text" name="tgl_rapor_pts"
                                    value="<?= $rapor != null ? $rapor->tgl_rapor_pts : '' ?>" class="form-control tgl"
                                    autocomplete="off" required>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">Tgl Rapor Akhir Kls
                                <?= $satuan[$setting->jenjang][0] ?></label>
                            <div class="col-7">
                                <input type="text" name="tgl_rapor_akhir"
                                    value="<?= $rapor != null ? $rapor->tgl_rapor_akhir : '' ?>"
                                    class="form-control tgl" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">Tgl Rapor Akhir Kls
                                <?= $satuan[$setting->jenjang][1] ?></label>
                            <div class="col-7">
                                <input type="text" name="tgl_rapor_kelas_akhir"
                                    value="<?= $rapor != null ? $rapor->tgl_rapor_kelas_akhir : '' ?>"
                                    class="form-control tgl" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-5 col-form-label">Tampilkan NIP Kepala Sekolah</label>
                            <div class="col-7">
                                <?php
                                echo form_dropdown(
                                    'nip_kepsek',
                                    $kkm_drop,
                                    $rapor != null && isset($rapor->nip_kepsek) ? $rapor->nip_kepsek : '',
                                    'class="form-control" required'
                                ); ?>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-5 col-form-label">Tampilkan NIP Walikelas</label>
                            <div class="col-7">
                                <?php
                                echo form_dropdown(
                                    'nip_walikelas',
                                    $kkm_drop,
                                    $rapor != null && isset($rapor->nip_walikelas) ? $rapor->nip_walikelas : '',
                                    'class="form-control" required'
                                ); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1"></div>
                    <div class="col-md-5">
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">KKM Tunggal</label>
                            <div class="col-7">
                                <?php
                                echo form_dropdown(
                                    'kkm_tunggal',
                                    $kkm_drop,
                                    $rapor != null ? $rapor->kkm_tunggal : '',
                                    'id="tunggal" class="form-select" required'
                                ); ?>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">KKM</label>
                            <div class="col-7">
                                <input type="number" name="kkm" value="<?= $rapor != null ? $rapor->kkm : '' ?>"
                                    class="form-control kkm" required>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">Bobot PH</label>
                            <div class="col-7">
                                <input type="number" name="bobot_ph"
                                    value="<?= $rapor != null ? $rapor->bobot_ph : '' ?>" class="form-control kkm"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">Bobot PTS</label>
                            <div class="col-7">
                                <input type="number" name="bobot_pts"
                                    value="<?= $rapor != null ? $rapor->bobot_pts : '' ?>" class="form-control kkm"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-5 col-form-label">Bobot PAS</label>
                            <div class="col-7">
                                <input type="number" name="bobot_pas"
                                    value="<?= $rapor != null ? $rapor->bobot_pas : '' ?>" class="form-control kkm"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class='btn btn-primary'><i class='bi bi-save me-1'></i>
                            Simpan
                        </button>
                    </div>
                </div>
                <?= form_close() ?>
            </div>
            <div class="overlay d-none position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75"
                id="loading" style="z-index: 10;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const csrfName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';
        const baseUrl = '<?= base_url() ?>'.replace(/\/+$/, '');

        function onChangeKkm() {
            var tunggal = $('#tunggal').val();
            if (tunggal == '1') {
                $('.kkm').removeAttr('readonly');
            } else {
                $('.kkm').attr('readonly', 'true');
            }
        }
        onChangeKkm();

        $.datetimepicker.setLocale('id');
        $('.tgl').datetimepicker({
            i18n: {
                id: {
                    months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    dayOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']
                }
            },
            icons: { next: 'bi bi-angle-right', previous: 'bi bi-angle-left' },
            timepicker: false,
            scrollInput: false,
            scrollMonth: false,
            format: 'Y-m-d',
            widgetPositioning: { horizontal: 'left', vertical: 'bottom' }
        }).change(function () {
            if (typeof buat_tanggal_indonesia === 'function') {
                $(this).val(buat_tanggal_indonesia($(this).val()));
            }
        });

        $('#editsetting').submit(function (e) {
            e.preventDefault();
            $('#loading').removeClass('d-none');

            const formData = $(this).serialize() + '&' + csrfName + '=' + csrfHash;

            $.ajax({
                url: baseUrl + '/rapor/saveRaporAdmin',
                type: "POST",
                dataType: "json",
                data: formData,
                success: function (response) {
                    if (response.csrf_hash) csrfHash = response.csrf_hash;
                    $('#loading').addClass('d-none');
                    if (response.status) {
                        showSuccessToast("Pengaturan rapor berhasil disimpan");
                    } else {
                        showDangerToast(response.message || "Gagal menyimpan");
                    }
                },
                error: function () {
                    $('#loading').addClass('d-none');
                    showDangerToast('Gagal menyimpan pengaturan rapor');
                }
            });
        });

        $('#tunggal').on('change', function () { onChangeKkm(); });
    });
</script>
<?= $this->endSection() ?>