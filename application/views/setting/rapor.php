<?php
/**
 * Created by IntelliJ IDEA.
 * User: AbangAzmi
 * Date: 20/12/2020
 * Time: 11:45
 */

$satuan = [
    "1" => ["I ~ V", "VI"],
    "2" => ["VII-VIII", "IX"],
    "3" => ["X-XI", "XII"
    ]
];

$raporData = isset($rapor) && is_object($rapor) ? $rapor : null;

$formatDate = static function ($date) {
    if (!$date || $date === '0000-00-00') {
        return '-';
    }

    try {
        $dt = new DateTime($date);
        return $dt->format('d M Y');
    } catch (Exception $e) {
        return $date;
    }
};

$tglPts = $formatDate($raporData->tgl_rapor_pts ?? null);
$tglAkhirAwal = $formatDate($raporData->tgl_rapor_akhir ?? null);
$tglAkhirAkhir = $formatDate($raporData->tgl_rapor_kelas_akhir ?? null);

$bobotPh = isset($raporData->bobot_ph) ? (float)$raporData->bobot_ph : 0;
$bobotPts = isset($raporData->bobot_pts) ? (float)$raporData->bobot_pts : 0;
$bobotPas = isset($raporData->bobot_pas) ? (float)$raporData->bobot_pas : 0;
$totalBobot = $bobotPh + $bobotPts + $bobotPas;

$kkmValue = isset($raporData->kkm) ? (float)$raporData->kkm : 0;
$kkmTunggal = isset($raporData->kkm_tunggal) && (string)$raporData->kkm_tunggal === '1' ? 'Ya' : 'Tidak';
$nipKepsek = isset($raporData->nip_kepsek) && (string)$raporData->nip_kepsek === '1' ? 'Ditampilkan' : 'Disembunyikan';
$nipWalikelas = isset($raporData->nip_walikelas) && (string)$raporData->nip_walikelas === '1' ? 'Ditampilkan' : 'Disembunyikan';

?>

<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow mb-4 border-0">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                            <div class="mb-3 mb-lg-0">
                                <h5 class="mb-1 font-weight-bold">Ringkasan Pengaturan Rapor</h5>
                                <div class="text-muted">Pantau status jadwal cetak dan kelengkapan bobot penilaian di sini.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge badge-primary px-3 py-2 text-sm">Total Bobot: <?= number_format($totalBobot, 0) ?>%</span>
                                <span class="badge badge-success px-3 py-2 text-sm">KKM Saat Ini: <?= number_format($kkmValue, 0) ?></span>
                                <span class="badge badge-info px-3 py-2 text-sm">KKM Tunggal: <?= $kkmTunggal ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengaturan Rapor</h5>
                        <ul class="pl-3 mb-0">
                            <li>Pastikan jadwal rapor PTS dan rapor akhir sudah sesuai kalender akademik.</li>
                            <li>Total bobot PH + PTS + PAS harus <strong>100%</strong> agar perhitungan nilai valid.</li>
                            <li>Pilih opsi tampilkan NIP hanya jika nomor yang digunakan benar-benar NIP resmi.</li>
                            <li>Gunakan <strong>KKM Tunggal</strong> jika semua mapel memakai KKM yang sama.</li>
                        </ul>
                    </div>

                    <?= form_open('edit', ['id' => 'editsetting'], ['id_setting' => $raporData->id_setting ?? '']) ?>
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header" style="background: linear-gradient(135deg, #f6f9ff 0%, #dbe7ff 100%); color: #111;">
                                    <h6 class="mb-0">Jadwal & Identitas Rapor</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">Tgl Rapor PTS</label>
                                        <input type="text" name="tgl_rapor_pts" value="<?= $raporData->tgl_rapor_pts ?? '' ?>" class="form-control tgl" autocomplete="off" required>
                                        <small class="form-text text-muted">Terakhir diset: <?= $tglPts ?></small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">Tgl Rapor Akhir Kls <?= $satuan[$setting->jenjang][0] ?? '-' ?></label>
                                        <input type="text" name="tgl_rapor_akhir" value="<?= $raporData->tgl_rapor_akhir ?? '' ?>" class="form-control tgl" autocomplete="off" required>
                                        <small class="form-text text-muted">Terakhir diset: <?= $tglAkhirAwal ?></small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">Tgl Rapor Akhir Kls <?= $satuan[$setting->jenjang][1] ?? '-' ?></label>
                                        <input type="text" name="tgl_rapor_kelas_akhir" value="<?= $raporData->tgl_rapor_kelas_akhir ?? '' ?>" class="form-control tgl" autocomplete="off" required>
                                        <small class="form-text text-muted">Terakhir diset: <?= $tglAkhirAkhir ?></small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">Tampilkan NIP Kepala Sekolah</label>
                                        <?php echo form_dropdown('nip_kepsek', $kkm_drop, $raporData->nip_kepsek ?? '', 'class="form-control" required'); ?>
                                        <small class="form-text text-muted">Status saat ini: <?= $nipKepsek ?></small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold">Tampilkan NIP Walikelas</label>
                                        <?php echo form_dropdown('nip_walikelas', $kkm_drop, $raporData->nip_walikelas ?? '', 'class="form-control" required'); ?>
                                        <small class="form-text text-muted">Status saat ini: <?= $nipWalikelas ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header" style="background: linear-gradient(135deg, #eefbf3 0%, #d6f2e2 100%); color: #111;">
                                    <h6 class="mb-0">KKM & Bobot Penilaian</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">KKM Tunggal</label>
                                        <?php echo form_dropdown('kkm_tunggal', $kkm_drop, $raporData->kkm_tunggal ?? '', 'id="tunggal" class="form-control" required'); ?>
                                        <small class="form-text text-muted">Gunakan opsi "Ya" jika semua mapel memakai KKM yang sama.</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">KKM</label>
                                        <input type="number" name="kkm" value="<?= $raporData->kkm ?? '' ?>" class="form-control kkm" required>
                                        <small class="form-text text-muted">Nilai KKM tunggal yang diterapkan.</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">Bobot PH</label>
                                        <input type="number" name="bobot_ph" value="<?= $raporData->bobot_ph ?? '' ?>" class="form-control kkm" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold">Bobot PTS</label>
                                        <input type="number" name="bobot_pts" value="<?= $raporData->bobot_pts ?? '' ?>" class="form-control kkm" required>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-semibold">Bobot PAS</label>
                                        <input type="number" name="bobot_pas" value="<?= $raporData->bobot_pas ?? '' ?>" class="form-control kkm" required>
                                        <small class="form-text text-muted">Total bobot saat ini: <?= number_format($totalBobot, 0) ?>%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-right">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save mr-1"></i>Simpan Pengaturan</button>
                        </div>
                    </div>
                    <?= form_close() ?>
                </div>
                <div class="overlay d-none" id="loading">
                    <!-- <div class="spinner-grow"></div> -->
                     <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                            Loading...
                            </button>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function () {
        function onChangeKkm() {
            console.log('change');
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
                    months: [
                        'Januari', 'Februari', 'Maret', 'April', 'Mei',
                        'Juni', 'Juli', 'Agustus', 'September',
                        'Oktober', 'November', 'Desember'
                    ],
                    dayOfWeek: [
                        'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'
                    ]
                }
            },
            icons:
                {
                    next: 'fa fa-angle-right',
                    previous: 'fa fa-angle-left'
                },
            timepicker: false,
            scrollInput: false,
            scrollMonth: false,
            format: 'Y-m-d',
            //disabledWeekDays: [0],
            widgetPositioning: {
                horizontal: 'left',
                vertical: 'bottom'
            }
        }).change(function () {
            $(this).val(buat_tanggal_indonesia($(this).val()));
        });

        $('#editsetting').submit(function (e) {
            e.stopPropagation();
            e.preventDefault();
            e.stopImmediatePropagation();

            console.log($(this).serialize());
            $('#loading').removeClass('d-none');

            $.ajax({
                url: base_url + 'rapor/saveraporadmin',
                type: "POST",
                dataType: "json",
                data: $(this).serialize(),
                success: function (response) {
                    setTimeout(function () {
                        $('#loading').addClass('d-none');
                        if (response.status) {
                            showSuccessToast("Sukses")
                        } else {
                            showSuccessToast("gagal")
                        }
                    }, 500);
                }, error: function (xhr, status, error) {
                    $('#loading').addClass('d-none');
                    console.log("response:", xhr.responseText);
                    showDangerToast('gagal disimpan')
                }
            });
        });

        $('#tunggal').on('change', function () {
            onChangeKkm();
        });

    })
</script>

