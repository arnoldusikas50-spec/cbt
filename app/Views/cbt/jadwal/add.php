<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
/**
 * Halaman Tambah/Edit Jadwal Ujian CBT (CI4 Port)
 */

// Set default values for new jadwal
$is_new = empty($jadwal->id_jadwal); // Logic matched
$default_durasi = $is_new ? 60 : $jadwal->durasi_ujian;
$default_jarak = $is_new ? 30 : $jadwal->jarak;
$default_tgl_mulai = $is_new ? date('Y-m-d') : $jadwal->tgl_mulai;
$default_tgl_selesai = $is_new ? '' : $jadwal->tgl_selesai;
// Handle active_jenis_id safely
$active_jenis = isset($active_jenis_id) ? $active_jenis_id : '';
$default_jenis = $is_new ? $active_jenis : $jadwal->id_jenis;

// Handle boolean strings '1'/'0' or booleans
$default_acak_soal = $is_new ? true : ($jadwal->acak_soal == 1);
$default_acak_opsi = $is_new ? true : ($jadwal->acak_opsi == 1);
$default_token = $is_new ? true : ($jadwal->token == 1);
$default_hasil = $is_new ? true : ($jadwal->hasil_tampil == 1);
$default_reset = $is_new ? false : ($jadwal->reset_login == 1);
$default_pelanggaran = $is_new ? false : ($jadwal->pelanggaran == 1);
$default_status = $is_new ? true : ($jadwal->status == 1);
$default_remedial = $is_new ? true : (($jadwal->enable_remedial ?? '1') == '1');
?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    <?= esc($judul ?? 'Tambah Jadwal Ujian') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Tambah Jadwal Ujian') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?= form_open('cbtjadwal/saveJadwal', array('id' => 'create')) ?>
        <input type="hidden" id="id-jadwal" name='id_jadwal' value="<?= $jadwal->id_jadwal ?>" />
        
        <div class="row">
            <!-- Kolom Kiri: Form Utama -->
            <div class="col-lg-8">
                <?php $disabled_option = $disable_opsi ? 'disabled="disabled"' : ''; ?>
                
                <!-- Card: Informasi Jadwal -->
                <div class="card card-default my-shadow mb-3">
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
                                    $mapel, // Assuming $mapel is passed as array
                                    isset($jadwal->id_mapel) ? $jadwal->id_mapel : '',
                                    $disabled_option . ' id="id-mapel" class="form-control form-control-sm select2" required'
                                ); ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Soal</label>
                                <select <?= $disabled_option ?> name="bank_id" id="bank-id" 
                                    class="form-control form-control-sm select2" required></select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jenis Penilaian</label>
                                <?= form_dropdown(
                                    'jenis_id',
                                    $jenis,
                                    $default_jenis,
                                    $disabled_option . ' id="jenis-id" class="form-control form-control-sm select2" required'
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
                <div class="card card-default my-shadow mb-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-gear me-2 text-secondary"></i>Opsi Ujian
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-soal">
                                    <input type="checkbox" id="check-soal" name="acak_soal" value="1" 
                                        <?= $default_acak_soal ? 'checked' : '' ?> <?= $disabled_option ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Acak Soal</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-opsi">
                                    <input type="checkbox" id="check-opsi" name="acak_opsi" value="1" 
                                        <?= $default_acak_opsi ? 'checked' : '' ?> <?= $disabled_option ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Acak Jawaban</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-token">
                                    <input type="checkbox" id="check-token" name="token" value="1" 
                                        <?= $default_token ? 'checked' : '' ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Gunakan Token</span>
                                </label>
                                <input type="hidden" id="token-value" name="token_value" value="<?= esc($jadwal->token_value ?? '') ?>">
                                <div id="token-display" class="mt-2 <?= $default_token ? '' : 'd-none' ?>">
                                    <div class="border rounded p-2 bg-light d-flex align-items-center justify-content-between">
                                        <span id="token-text" class="font-monospace fw-bold fs-5"><?= esc($jadwal->token_value ?? '') ?></span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToken()" title="Salin Token">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-1" onclick="regenerateToken()" title="Generate Ulang">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-hasil">
                                    <input type="checkbox" id="check-hasil" name="hasil_tampil" value="1" 
                                        <?= $default_hasil ? 'checked' : '' ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Tampilkan Hasil</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-login">
                                    <input type="checkbox" id="check-login" name="reset_login" value="1" 
                                        <?= $default_reset ? 'checked' : '' ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Reset Izin</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-pelanggaran">
                                    <input type="checkbox" id="check-pelanggaran" name="pelanggaran" value="1" 
                                        <?= $default_pelanggaran ? 'checked' : '' ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Hitung Pelanggaran</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-status">
                                    <input type="checkbox" id="check-status" name="status" value="1" 
                                        <?= $default_status ? 'checked' : '' ?> <?= $disabled_option ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Aktifkan Jadwal</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="custom-checkbox d-flex align-items-center cursor-pointer" for="check-remedial">
                                    <input type="checkbox" id="check-remedial" name="enable_remedial" value="1" 
                                        <?= $default_remedial ? 'checked' : '' ?> class="d-none">
                                    <span class="checkbox-box"></span>
                                    <span class="checkbox-label">Aktifkan Remedial</span>
                                </label>
                            </div>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <label class="form-label d-block mb-1" for="input-max-remedial">Batas Percobaan Remedial</label>
                                <input type="number" min="0" max="20" step="1" class="form-control" id="input-max-remedial"
                                    name="max_remedial" value="<?= htmlspecialchars($jadwal->max_remedial ?? 2) ?>">
                                <small class="text-muted">0 = unlimited (default 2)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Info & Tombol -->
            <div class="col-lg-4">
                <!-- Info Kelas -->
                <?php
                // Logic to display classes from bank_kelas
                $kelasbank = '';
                if (!empty($jadwal->bank_kelas)) {
                    foreach ($jadwal->bank_kelas as $j) {
                        foreach ($kelas as $k) {
                            if ($j->kelas_id == $k->id_kelas) {
                                $kelasbank .= '<span class="badge bg-info me-1 mb-1">' . $k->nama_kelas . '</span>';
                            }
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
                        <li class="mb-1">Bank soal dengan label <span class="badge bg-warning text-dark" style="font-size:0.7rem"><i class="bi bi-calendar-check me-1"></i>SUDAH TERJADWAL</span> tidak bisa dipilih lagi karena sudah memiliki jadwal.</li>
                        <li class="mb-1">Bank soal dengan label <span class="badge bg-secondary" style="font-size:0.7rem"><i class="bi bi-exclamation-circle me-1"></i>BELUM AKTIF</span> tidak bisa dipilih karena soal belum diaktifkan.</li>
                        <li class="mb-1">Ujian tampil di siswa antara <b>tanggal mulai</b> s.d. <b>expired</b>.</li>
                        <li class="mb-1"><b>Durasi minimal</b>: waktu minimum siswa harus mengerjakan.</li>
                        <li class="mb-1"><b>Reset Izin</b>: siswa auto logout jika buka tab baru.</li>
                        <li class="mb-1"><b>Hitung Pelanggaran</b>: pengurangan poin jika melanggar aturan.</li>
                        <li class="mb-1"><b>Aktifkan Remedial</b>: siswa bisa mengerjakan ulang soal yang salah.</li>
                    </ul>
                </div>

                <!-- Tombol Simpan -->
                <div class="d-grid">
                    <button name="tambahjadwal" class="btn btn-primary shadow-sm" type="submit">
                        <i class="bi bi-floppy me-1"></i>Simpan Jadwal
                    </button>
                    <a href="<?= base_url('cbtjadwal') ?>" class="btn btn-secondary shadow-sm mt-3">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <?= form_close() ?>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .custom-checkbox {
        cursor: pointer;
        user-select: none;
    }
    .custom-checkbox input[type="checkbox"] {
        display: none;
    }
    .checkbox-box {
        width: 24px;
        height: 24px;
        border: 2px solid #333;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        transition: all 0.2s ease;
        background: #fff;
        flex-shrink: 0;
    }
    .checkbox-box::after {
        content: '';
        width: 14px;
        height: 14px;
        background: transparent;
        border-radius: 3px;
        transition: all 0.2s ease;
    }
    .custom-checkbox input[type="checkbox"]:checked + .checkbox-box {
        border-color: #28a745;
        background: #fff;
    }
    .custom-checkbox input[type="checkbox"]:checked + .checkbox-box::after {
        content: '';
        width: 6px;
        height: 12px;
        border: solid #28a745;
        border-width: 0 3px 3px 0;
        transform: rotate(45deg);
        background: transparent;
        border-radius: 0;
        margin-bottom: 2px;
    }
    .custom-checkbox input[type="checkbox"]:disabled + .checkbox-box {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .checkbox-label {
        font-size: 0.95rem;
        color: #333;
    }
    .custom-checkbox:hover .checkbox-box {
        border-color: #28a745;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var digunakan = <?= $disable_opsi ? '1' : '0' ?>;
    var idBank = '<?= $jadwal->id_bank ?? '' ?>';
    let kelasBank = [];
    let allKelas = [];

    $(document).ready(function() {
        // CSRF Token handling if needed, though form_open handles header usually.
        // Assuming global ajaxcsrf() exists or we rely on automation.
        if (typeof ajaxcsrf === "function") {
            ajaxcsrf();
        }

        $('#jenis-id').select2({ width: '100%' });
        var selMapel = $('#id-mapel');
        selMapel.select2({ width: '100%' });
        var selec = idBank == '' ? 'selected' : '';
        selMapel.prepend('<option value="" ' + selec + '>Pilih Mata Pelajaran</option>');
        var selBank = $('#bank-id');
        function formatBankSoal(state) {
            if (!state.id) {
                return state.text;
            }
            var $element = $(state.element);
            var stat = $element.data('status');
            var terjadwal = $element.data('terjadwal');
            
            console.log('DEBUG formatBankSoal:', state.id, state.text, 'status:', stat, 'terjadwal:', terjadwal);
            
            var $state = $('<span>' + state.text + '</span>');
            
            if (terjadwal == 'true' || terjadwal === true) {
                $state.append(' <span class="badge bg-warning text-dark shadow-xs ms-1" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px;"><i class="bi bi-calendar-check me-1"></i>SUDAH TERJADWAL</span>');
            } else if (stat == '1') {
                $state.append(' <span class="badge bg-success shadow-xs ms-1" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px;"><i class="bi bi-check-circle me-1"></i>AKTIF</span>');
            } else {
                $state.append(' <span class="badge bg-secondary shadow-xs ms-1" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px;"><i class="bi bi-exclamation-circle me-1"></i>BELUM AKTIF</span>');
            }
            return $state;
        }

        selBank.select2({ 
            width: '100%',
            templateResult: formatBankSoal,
            templateSelection: formatBankSoal,
            escapeMarkup: function(m) { return m; }
        });

        $('.tgl').datetimepicker({
            timepicker: true, // CI3 said false? No, date('Y-m-d') was default. CI4 has H:i. 
            // CI3 had: 'format: Y-m-d' but inputs were text. 
            // CI4 controller allows full datetime strings. 
            // CI3: $default_tgl_mulai = date('Y-m-d').
            // Let's stick to Y-m-d H:i for better control as in CI4, 
            // unless user complains. CI3 View had `timepicker: false` (Line 249 CI3).
            // But `tgl_mulai` DB column is usually DATETIME.
            // I'll stick to CI3 behavior: Y-m-d. Wait.
            // CI3 line 252: format: 'Y-m-d'.
            // So CI3 ONLY allowed Date selection?
            // If so, exams start at 00:00?
            // I'll replicate CI3 exactly: Y-m-d.
            format: 'Y-m-d',
            timepicker: false,
            // scrollInput: false...
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
                        }).then(() => {
                           window.location.href = base_url + 'cbtjadwal'; 
                           // CI3 users window.history.back(), but robust redirect is often better.
                           // CI3: window.history.back() works because they came from list.
                        });
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
                    try { msg = JSON.parse(xhr.responseText).message; } catch(e) {}
                    Swal.fire({ title: "Error", text: msg, icon: "error" });
                }
            });
        });

        function getBankMapel(mapel) {
            if (!mapel) return;
            const idJadwal = $('#id-jadwal').val() || '0';
            $.ajax({
                url: base_url + "cbtjadwal/getbankmapel/" + mapel + "?id_jadwal=" + idJadwal,
                type: "GET",
                success: function(data) {
                    console.log('DEBUG - data.jadwal:', data.jadwal);
                    console.log('DEBUG - data.bank:', data.bank);
                    selBank.html('<option value="" ' + selec + '>Pilih Bank Soal</option>');
                    $.each(data.bank, function(i, v) {
                        var selected = i == idBank ? 'selected' : '';
                        const bankId = String(i);
                        const currentBankId = String(idBank);
                        const jadwalIds = data.jadwal ? data.jadwal.map(String) : [];
                        const used = jadwalIds.includes(bankId);
                        const stat = data.status && data.status[i] ? data.status[i] : '0';
                        let disable = '';
                        let text = v;
                        let terjadwal = 'false';
                        
                        if (used && bankId !== currentBankId) {
                            disable = ' disabled';
                            terjadwal = 'true';
                        } else if (stat === '0') {
                            disable = ' disabled';
                        }
                        
                        if (i !== '') {
                            selBank.append('<option value="' + i + '" data-status="' + stat + '" data-terjadwal="' + terjadwal + '" ' + selected + disable + '>' + text + '</option>');
                        }
                    });
                    // Refresh select2 untuk memperbarui tampilan
                    selBank.trigger('change');
                    allKelas = data.all_kelas || [];
                    kelasBank = data.kelas || [];
                    
                    // Specific fix: if editing, trigger change to show classes immediately?
                    // CI3 does NOT trigger immediately inside success, but calls getBankMapel at end.
                    // And `selMapel.on('change')` calls it.
                    // BUT initial load needs to populate list.
                    // Lines 353-354 CI3: Calls getBankMapel(selMapel.val())
                    // And inside success, it DOES NOT update info-kelas html.
                    // However, `selBank` logic does.
                    // We need to trigger selBank change or manually update if values exist.
                    if(idBank) {
                        // After options populated, trigger change?
                         // selBank.trigger('change');
                         // Or manually call logic:
                        let html = '';
                        const selected = kelasBank[idBank];
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
                    }
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
            } else {
                 $('#info-kelas').addClass('d-none');
                 $('#info-kelas-bank').empty();
            }
        });

        getBankMapel(selMapel.val());

        // ── Token management ─────────────────────────────────────
        function generateTokenString() {
            const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            let token = '';
            for (let i = 0; i < 6; i++) {
                token += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return token;
        }

        function updateTokenDisplay(token) {
            $('#token-text').text(token);
            $('#token-value').val(token);
            $('#token-display').removeClass('d-none');
        }

        function copyToken() {
            const token = $('#token-text').text();
            navigator.clipboard.writeText(token).then(() => {
                Swal.fire({ icon: 'success', title: 'Tersalin', text: 'Token: ' + token, timer: 1500, showConfirmButton: false });
            });
        }

        function regenerateToken() {
            const token = generateTokenString();
            updateTokenDisplay(token);
        }

        $('#check-token').on('change', function() {
            if ($(this).is(':checked')) {
                let token = $('#token-value').val();
                if (!token) {
                    token = generateTokenString();
                }
                updateTokenDisplay(token);
            } else {
                $('#token-display').addClass('d-none');
                $('#token-value').val('');
            }
        });

        // Generate initial token if new form with token checked
        if ($('#check-token').is(':checked') && !$('#token-value').val()) {
            const token = generateTokenString();
            updateTokenDisplay(token);
        }
    });
</script>
<?= $this->endSection() ?>
