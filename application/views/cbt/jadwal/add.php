<?php
/**
 * Halaman Tambah/Edit Jadwal Ujian CBT
 */

// Set default values for new jadwal
$is_new = empty($jadwal->id_jadwal);
$default_durasi = $is_new ? 60 : $jadwal->durasi_ujian;
$default_jarak = $is_new ? 30 : $jadwal->jarak;
$default_tgl_mulai = $is_new ? date('Y-m-d') : $jadwal->tgl_mulai;
$default_tgl_selesai = $is_new ? '' : $jadwal->tgl_selesai;
$default_jenis = $is_new ? (isset($active_jenis_id) ? $active_jenis_id : '') : $jadwal->id_jenis;
$default_acak_soal = $is_new ? true : ($jadwal->acak_soal == 1);
$default_acak_opsi = $is_new ? true : ($jadwal->acak_opsi == 1);
$default_token = $is_new ? true : ($jadwal->token === '1');
$default_hasil = $is_new ? true : ($jadwal->hasil_tampil === '1');
$default_reset = $is_new ? false : ($jadwal->reset_login === '1');
$default_pelanggaran = $is_new ? false : ($jadwal->pelanggaran === '1');
$default_status = $is_new ? true : ($jadwal->status === '1');
?>

<main class="app-main bg-light pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h3><?= $judul ?></h3>
                </div>
                <div class="col-sm-6 text-end">
                    <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger">
                        <i class="bi bi-arrow-left-circle me-1"></i>Kembali
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?= form_open('create', array('id' => 'create')) ?>
            <input type="hidden" id="id-jadwal" name='id_jadwal' value="<?= $jadwal->id_jadwal ?>" />
            
            <div class="row">
                <!-- Kolom Kiri: Form Utama -->
                <div class="col-lg-8">
                    <?php $disabled_option = $disable_opsi ? 'disabled="disabled"' : ''; ?>
                    
                    <!-- Card: Informasi Jadwal -->
                    <div class="card my-shadow mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Jadwal
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mata Pelajaran</label>
                                    <?= form_dropdown(
                                        'mapel',
                                        $mapel,
                                        isset($jadwal->id_mapel) ? $jadwal->id_mapel : '',
                                        $disabled_option . ' id="id-mapel" class="form-control form-control-sm" required'
                                    ); ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Soal</label>
                                    <select <?= $disabled_option ?> name="bank_id" id="bank-id" 
                                        class="form-control form-control-sm" required></select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jenis Penilaian</label>
                                    <?= form_dropdown(
                                        'jenis_id',
                                        $jenis,
                                        $default_jenis,
                                        $disabled_option . ' id="jenis-id" class="form-control form-control-sm" required'
                                    ); ?>
                                </div>
                                <div class="col-6 col-md-4 mb-3">
                                    <label class="form-label"><i class="bi bi-calendar-event me-1"></i>Tanggal Mulai</label>
                                    <input type="text" id="tgl-mulai" name="tgl_mulai" value="<?= $default_tgl_mulai ?>"
                                        class="tgl form-control form-control-sm" autocomplete="off" required />
                                </div>
                                <div class="col-6 col-md-4 mb-3">
                                    <label class="form-label"><i class="bi bi-calendar-x me-1"></i>Tanggal Expired</label>
                                    <input type="text" id="tgl-selesai" name="tgl_selesai" value="<?= $default_tgl_selesai ?>"
                                        class="tgl form-control form-control-sm" autocomplete="off" required />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Durasi Ujian (menit)</label>
                                    <input type="number" id="durasi-ujian" name="durasi_ujian" 
                                        class="form-control form-control-sm" value="<?= $default_durasi ?>" 
                                        required min="1" />
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Durasi Minimal (menit)</label>
                                    <input type="number" id="jarak" name="jarak" 
                                        class="form-control form-control-sm" value="<?= $default_jarak ?>" 
                                        required min="0" />
                                </div>
                            </div>


                        </div>
                    </div>

                    <!-- Card: Opsi Ujian -->
                    <div class="card my-shadow mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="bi bi-gear me-2 text-secondary"></i>Opsi Ujian
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="check-soal" name="acak_soal" value="1" 
                                            <?= $default_acak_soal ? 'checked' : '' ?> <?= $disabled_option ?> />
                                        <label class="custom-control-label" for="check-soal">Acak Soal</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="check-opsi" name="acak_opsi" value="1" 
                                            <?= $default_acak_opsi ? 'checked' : '' ?> <?= $disabled_option ?> />
                                        <label class="custom-control-label" for="check-opsi">Acak Jawaban</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="check-token" name="token" value="1" 
                                            <?= $default_token ? 'checked' : '' ?> />
                                        <label class="custom-control-label" for="check-token">Gunakan Token</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="check-hasil" name="hasil_tampil" value="1" 
                                            <?= $default_hasil ? 'checked' : '' ?> />
                                        <label class="custom-control-label" for="check-hasil">Tampilkan Hasil</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="check-login" name="reset_login" value="1" 
                                            <?= $default_reset ? 'checked' : '' ?> />
                                        <label class="custom-control-label" for="check-login">Reset Izin</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="check-pelanggaran" name="pelanggaran" value="1" 
                                            <?= $default_pelanggaran ? 'checked' : '' ?> />
                                        <label class="custom-control-label" for="check-pelanggaran">Hitung Pelanggaran</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="check-status" name="status" value="1" 
                                            <?= $default_status ? 'checked' : '' ?> <?= $disabled_option ?> />
                                        <label class="custom-control-label" for="check-status">Aktifkan Jadwal</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Info & Tombol -->
                <div class="col-lg-4">
                    <!-- Info Kelas -->
                    <?php
                    $jumlahKelas = $jadwal->bank_kelas;
                    $kelasbank = '';
                    foreach ($jumlahKelas as $j) {
                        foreach ($kelas as $k) {
                            if ($j->kelas_id == $k->id_kelas) {
                                $kelasbank .= '<span class="badge bg-info me-1 mb-1">' . $k->nama_kelas . '</span>';
                            }
                        }
                    }
                    $dnone = $kelasbank === '' ? 'd-none' : '';
                    ?>
                    <div class="alert alert-default-primary <?= $dnone ?> mb-3" id="info-kelas">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-people-fill me-1"></i>Kelas Terdaftar
                        </h6>
                        <div id="info-kelas-bank"><?= $kelasbank ?></div>
                        <hr class="my-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>Edit Bank Soal untuk mengubah kelas
                        </small>
                    </div>

                    <!-- Panduan -->
                    <div class="alert alert-default-warning mb-3">
                        <h6 class="fw-bold mb-2"><i class="bi bi-lightbulb me-1"></i>Panduan</h6>
                        <ul class="ps-3 mb-0" style="font-size: 0.85rem;">
                            <?php if ($jadwal->id_jadwal != "") : ?>
                                <li class="mb-1">Beberapa opsi <b>tidak bisa diedit</b> karena ujian sedang berlangsung.</li>
                            <?php endif; ?>
                            <li class="mb-1">Ujian tampil di siswa antara <b>tanggal mulai</b> s.d. <b>expired</b>.</li>
                            <li class="mb-1"><b>Durasi minimal</b>: waktu minimum siswa harus mengerjakan.</li>
                            <li class="mb-1"><b>Reset Izin</b>: siswa auto logout jika buka tab baru.</li>
                            <li class="mb-1"><b>Hitung Pelanggaran</b>: pengurangan poin jika melanggar aturan.</li>
                        </ul>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="d-grid">
                        <button name="tambahjadwal" class="btn btn-primary shadow-sm">
                            <i class="bi bi-floppy me-1"></i>Simpan Jadwal
                        </button>
                    </div>
                </div>
            </div>
            
            <?= form_close() ?>
        </div>
    </section>
</main>

<script>
    var digunakan = <?= $disable_opsi ? '1' : '0' ?>;
    var idBank = '<?= $jadwal->id_bank ?>';
    let kelasBank = [];
    let allKelas = [];

    $(document).ready(function() {
        ajaxcsrf();

        $('#jenis-id').select2({ width: '100%' });
        var selMapel = $('#id-mapel');
        selMapel.select2({ width: '100%' });
        var selec = idBank == '' ? 'selected' : '';
        selMapel.prepend('<option value="" ' + selec + '>Pilih Mata Pelajaran</option>');
        var selBank = $('#bank-id');
        selBank.select2({ width: '100%' });

        $('.tgl').datetimepicker({
            icons: {
                next: 'bi bi-chevron-right',
                previous: 'bi bi-chevron-left'
            },
            timepicker: false,
            scrollInput: false,
            scrollMonth: false,
            format: 'Y-m-d',
            widgetPositioning: {
                horizontal: 'left',
                vertical: 'bottom'
            }
        });

        function reEnable(disable) {
            if (digunakan == '1') {
                $('#id-guru').attr('disabled', disable);
                $('#bank-id').prop('disabled', disable);
                $('#jenis-id').prop('disabled', disable);
                $('#check-soal').prop('disabled', disable);
                $('#check-opsi').prop('disabled', disable);
                $('#check-token').prop('disabled', disable);
                $('#check-hasil').prop('disabled', disable);
                $('#check-login').prop('disabled', disable);
                $('#check-status').prop('disabled', disable);
                $('#check-pelanggaran').prop('disabled', disable);
            }
        }

        $('#create').submit(function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            reEnable(false);

            Swal.fire({
                text: "Menyimpan jadwal...",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: base_url + "cbtjadwal/saveJadwal",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                success: function(data) {
                    reEnable(true);
                    if (data.success) {
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Jadwal berhasil disimpan",
                            icon: "success"
                        }).then(() => window.history.back());
                    } else {
                        Swal.fire({
                            title: "Gagal",
                            text: data.message,
                            icon: "error"
                        });
                    }
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan';
                    try { msg = JSON.parse(xhr.responseText).Message; } catch(e) {}
                    Swal.fire({ title: "Error", text: msg, icon: "error" });
                }
            });
        });

        function getBankMapel(mapel) {
            if (!mapel) return;
            $.ajax({
                url: base_url + "cbtjadwal/getbankmapel/" + mapel,
                type: "GET",
                success: function(data) {
                    selBank.html('<option value="" ' + selec + '>Pilih Bank Soal</option>');
                    $.each(data.bank, function(i, v) {
                        var selected = i === idBank ? 'selected' : '';
                        const used = data.jadwal.includes(i);
                        const disable = used && i !== idBank ? ' disabled' : '';
                        const tag = used ? ' (terjadwal)' : '';
                        if (i !== '') selBank.append('<option value="' + i + '" ' + selected + disable + '>' + v + tag + '</option>');
                    });
                    allKelas = data.all_kelas || [];
                    kelasBank = data.kelas || [];
                }
            });
        }

        selMapel.on('change', function() {
            getBankMapel($(this).val());
        });

        selBank.on('change', function() {
            let html = '';
            const selected = kelasBank[$(this).val()];
            if (selected) {
                selected.forEach(k => {
                    const kls = allKelas.find(x => x.id_kelas == k.kelas_id);
                    if (kls) html += '<span class="badge bg-info me-1 mb-1">' + kls.nama_kelas + '</span>';
                });
            }
            if (html !== '') {
                $('#info-kelas').removeClass('d-none');
                $('#info-kelas-bank').html(html);
            }
        });

        getBankMapel(selMapel.val());
    });
</script>
