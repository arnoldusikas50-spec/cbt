<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $judul ?></h1>
                </div>
                <div class="col-sm-6 text-end">
                    <button onclick="window.history.back();" type="button"
                        class="btn btn-sm btn-danger overflow-hidden shadow-sm">
                        <i class="bi bi-arrow-left-circle me-1"></i>
                        <span class="d-none d-sm-inline-block">Kembali</span>
                    </button>
                    <ol class="breadcrumb float-sm-right mt-2">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('cbtbanksoal') ?>">Bank Soal</a></li>
                        <li class="breadcrumb-item active"><?= $subjudul ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?= form_open('cbtbanksoal/save', ['id' => 'create']) ?>
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title text-bold">
                        <i class="bi bi-pencil-square me-2"></i>
                        <?= esc($subjudul) . ($bank->bank_kode ? ' - ' . esc($bank->bank_kode) : '') ?>
                    </h3>
                    <div class="card-tools ms-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                            <i class="bi bi-floppy me-1"></i> Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <input type="hidden" name="id_bank" value="<?= esc($bank->id_bank) ?>">
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label small fw-bold text-muted">Kode Bank Soal</label>
                                <input type="text" class="form-control" name="kode" maxlength="20"
                                    placeholder="Masukan Kode Bank Soal" value="<?= esc($bank->bank_kode) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label small fw-bold text-muted">Mata Pelajaran</label>
                                <?= form_dropdown('mapel', $mapel, $bank->bank_mapel_id, 'id="select-mapel" class="select2 form-control" required') ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label small fw-bold text-muted">Guru Pengampu</label>
                                <?= form_dropdown('guru', $gurus, $bank->bank_guru_id, 'id="select-guru" class="select2 form-control" required') ?>
                            </div>
                        </div>
                        <div class="col-md-1 col-3">
                            <div class="form-group mb-3">
                                <label class="form-label small fw-bold text-muted">Level</label>
                                <?php
                                $arrLevel = [];
                                foreach ($level as $l) {
                                    $arrLevel[$l->id_level] = $l->level;
                                }
                                echo form_dropdown('level', $arrLevel, $bank->bank_level, 'id="select-level" class="form-control" data-placeholder="Pilih Level" required'); ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-9">
                            <div class="form-group mb-3">
                                <label class="form-label small fw-bold text-muted">Pilih Kelas</label>
                                <select name="kelas[]" id="select-kelas" class="select2 form-control"
                                    multiple="multiple" required></select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <!-- Pilihan Ganda -->
                        <div class="col-12 col-md-7">
                            <div class="card h-100 border-0 shadow-sm" style="background-color: #f0f7ff;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary text-white rounded-circle p-1 me-2"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <small class="fw-bold">1</small>
                                        </div>
                                        <span class="fw-bold text-primary">Soal Pilihan Ganda</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label class="small text-muted">Jml. Soal</label>
                                                <input id="jml-pg" type='number' name='tampil_pg'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->tampil_pg) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-group">
                                                <label class="small text-muted">Bobot %</label>
                                                <input id="bobot-pg" type='number' name='bobot_pg'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->bobot_pg) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="form-group">
                                                <label class="small text-muted">Opsi Jawaban</label>
                                                <?php
                                                $opsis = ['' => 'Pilih Jml Opsi :', '3' => '3 (A, B, C)', '4' => '4 (A, B, C, D)', '5' => '5 (A, B, C, D, E)'];
                                                echo form_dropdown('opsi', $opsis, $bank->opsi, 'class="form-select form-select-sm" required'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ganda Kompleks -->
                        <div class="col-12 col-md-5">
                            <div class="card h-100 border-0 shadow-sm" style="background-color: #fffaf0;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning text-white rounded-circle p-1 me-2"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <small class="fw-bold">2</small>
                                        </div>
                                        <span class="fw-bold text-warning-emphasis">Soal Ganda Kompleks</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Jml. Soal</label>
                                                <input id="jml-pg2" type='number' name='tampil_kompleks'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->tampil_kompleks) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Bobot %</label>
                                                <input id="bobot-pg2" type='number' name='bobot_kompleks'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->bobot_kompleks) ?>" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Menjodohkan -->
                        <div class="col-12 col-md-4">
                            <div class="card h-100 border-0 shadow-sm" style="background-color: #fff5f5;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-danger text-white rounded-circle p-1 me-2"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <small class="fw-bold">3</small>
                                        </div>
                                        <span class="fw-bold text-danger">Soal Menjodohkan</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Jml. Soal</label>
                                                <input id="jml-jodohkan" type='number' name='tampil_jodohkan'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->tampil_jodohkan) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Bobot %</label>
                                                <input id="bobot-jodohkan" type='number' name='bobot_jodohkan'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->bobot_jodohkan) ?>" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Isian Singkat -->
                        <div class="col-12 col-md-4">
                            <div class="card h-100 border-0 shadow-sm" style="background-color: #f0fff4;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-success text-white rounded-circle p-1 me-2"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <small class="fw-bold">4</small>
                                        </div>
                                        <span class="fw-bold text-success">Soal Isian Singkat</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Jml. Soal</label>
                                                <input id="jml-isian" type='number' name='tampil_isian'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->tampil_isian) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Bobot %</label>
                                                <input id="bobot-isian" type='number' name='bobot_isian'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->bobot_isian) ?>" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Uraian -->
                        <div class="col-12 col-md-4">
                            <div class="card h-100 border-0 shadow-sm" style="background-color: #f7fafc;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-secondary text-white rounded-circle p-1 me-2"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                            <small class="fw-bold">5</small>
                                        </div>
                                        <span class="fw-bold text-secondary">Soal Uraian/Essai</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Jml. Soal</label>
                                                <input id="jml-essai" type='number' name='tampil_esai'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->tampil_esai) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Bobot %</label>
                                                <input id="bobot-essai" type='number' name='bobot_esai'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->bobot_esai) ?>" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Urutan -->
                        <div class="col-12 col-md-6">
                            <div class="card h-100 border-0 shadow-sm" style="background-color: #f3f0ff;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-indigo text-white rounded-circle p-1 me-2"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; background-color: #6610f2;">
                                            <small class="fw-bold">6</small>
                                        </div>
                                        <span class="fw-bold" style="color: #6610f2;">Soal Urutan</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Jml. Soal</label>
                                                <input id="jml-urutan" type='number' name='tampil_urutan'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->tampil_urutan ?? 0) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Bobot %</label>
                                                <input id="bobot-urutan" type='number' name='bobot_urutan'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->bobot_urutan ?? 0) ?>" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Isian Rumpang -->
                        <div class="col-12 col-md-6">
                            <div class="card h-100 border-0 shadow-sm" style="background-color: #e6f7ff;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-cyan text-white rounded-circle p-1 me-2"
                                            style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; background-color: #0dcaf0;">
                                            <small class="fw-bold">7</small>
                                        </div>
                                        <span class="fw-bold" style="color: #086375;">Soal Isian Rumpang</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Jml. Soal</label>
                                                <input id="jml-cloze" type='number' name='tampil_cloze'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->tampil_cloze ?? 0) ?>" required />
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="small text-muted">Bobot %</label>
                                                <input id="bobot-cloze" type='number' name='bobot_cloze'
                                                    class='form-control form-control-sm'
                                                    value="<?= esc($bank->bobot_cloze ?? 0) ?>" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                                <div class="card-body p-3 bg-light">
                                    <div class="row text-center align-items-center">
                                        <div class="col-6 border-end">
                                            <small class="text-uppercase fw-bold text-muted"
                                                style="font-size: 0.7rem;">Total Soal</small>
                                            <h3 class="mb-0 fw-bold text-primary" id="total-soal">0</h3>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-uppercase fw-bold text-muted"
                                                style="font-size: 0.7rem;">Total Bobot</small>
                                            <h3 class="mb-0 fw-bold text-success" id="total-bobot">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label small fw-bold text-muted">Mapel Agama</label>
                                <?= form_dropdown('soal_agama', $mapel_agama, $bank->soal_agama, 'class="form-select form-select-sm" required') ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label small fw-bold text-muted">Status Bank Soal</label>
                                <?php
                                $aktifs = ['' => 'Pilih Status :', '1' => 'Aktif', '0' => 'Non Aktif'];
                                echo form_dropdown('status', $aktifs, $bank->status, 'class="form-select form-select-sm" required'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
    </div>

    <script>
        const isAdmin = '<?= $is_admin ?>';
        const kelasSelected = <?= json_encode($bank->bank_kelas ?? []) ?>;
        let idGuru = '<?= $id_guru ?>';
        let idMapel = '<?= $bank->bank_mapel_id ?>';

        $(document).ready(function () {
            const selLevel = $('#select-level');
            const selKelas = $('#select-kelas');
            const selMapel = $('#select-mapel');
            const selGuru = $('#select-guru');

            selMapel.select2({ width: '100%' });
            selGuru.select2({ width: '100%' });
            selKelas.select2({ width: '100%' });

            function getGuruMapel(mapel) {
                if (isAdmin === '1') {
                    $.ajax({
                        url: "<?= base_url('cbtbanksoal/getgurumapel') ?>?id_mapel=" + mapel,
                        type: "GET",
                        success: function (data) {
                            let opts = '';
                            $.each(data, function (k, v) {
                                let selected = (idGuru == k) ? 'selected' : '';
                                opts += `<option value="${k}" ${selected}>${v}</option>`;
                            });
                            selGuru.html(opts).trigger('change');
                        }
                    });
                }
            }

            function getKelasLevel(level, mapel) {
                if (idGuru && mapel) {
                    $.ajax({
                        url: "<?= base_url('cbtbanksoal/getkelaslevel') ?>?level=" + level + "&id_guru=" + idGuru + '&mapel=' + mapel,
                        type: "GET",
                        success: function (data) {
                            selKelas.empty();
                            const selectedIds = kelasSelected.map(k => k.kelas_id);
                            $.each(data.kelas, function (i, item) {
                                const isSelected = selectedIds.includes(item.id_kelas);
                                selKelas.append(new Option(item.kode_kelas, item.id_kelas, isSelected, isSelected));
                            });
                            selKelas.trigger('change');
                        }
                    });
                }
            }

            selGuru.on('change', function () {
                idGuru = $(this).val();
                getKelasLevel(selLevel.val(), selMapel.val());
            });

            selLevel.on('change', function () {
                getKelasLevel($(this).val(), selMapel.val());
            });

            selMapel.on('change', function () {
                idMapel = $(this).val();
                getGuruMapel(idMapel);
            });

            // Initialize display
            getKelasLevel(selLevel.val(), selMapel.val());

            const valBobot = ['pg', 'pg2', 'jodohkan', 'isian', 'essai', 'urutan', 'cloze'].map(id => $(`#bobot-${id}`));
            const valSoal = ['pg', 'pg2', 'jodohkan', 'isian', 'essai', 'urutan', 'cloze'].map(id => $(`#jml-${id}`));

            function calculateTotals() {
                let totalB = 0;
                let totalS = 0;
                valBobot.forEach(el => totalB += parseInt(el.val() || 0));
                valSoal.forEach(el => totalS += parseInt(el.val() || 0));
                $('#total-bobot').text(totalB);
                $('#total-soal').text(totalS);
            }

            [...valBobot, ...valSoal].forEach(el => el.on('change keyup', calculateTotals));
            calculateTotals();

            $('#create').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (data) {
                        if (data.status) {
                            Swal.fire({
                                title: 'Sukses',
                                text: 'Bank soal berhasil disimpan',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.history.back();
                            });
                        } else {
                            Swal.fire('Gagal', 'Gagal menyimpan bank soal', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            });
        });
    </script>