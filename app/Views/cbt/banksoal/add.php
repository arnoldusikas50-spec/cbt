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

            <?= form_open('cbtbanksoal/save', ['id' => 'create']) ?>
            <div class="card card-primary card-outline shadow-sm mb-4">
                <div class="card-header border-0 shadow-none">
                    <h3 class="card-title fw-bold text-primary">
                        <i class="bi bi-pencil-square me-2"></i>
                        <?= esc($subjudul) . ($bank->bank_kode ? ' - ' . esc($bank->bank_kode) : '') ?>
                    </h3>
                    <div class="card-tools ms-auto">
                        <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger overflow-hidden shadow-sm">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                            <i class="bi bi-floppy me-1"></i> Simpan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($schedule_warning)): ?>
                        <div class="alert alert-warning border-0 shadow-sm mb-3" id="schedule-warning">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Perhatian:</strong> <?= esc($schedule_warning) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (empty($setting->jenjang) || (int) $setting->jenjang === 0): ?>
                        <div class="alert alert-warning border-0 shadow-sm">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Jenjang pendidikan belum diatur</strong> pada
                            <a href="<?= base_url('settings') ?>" class="alert-link">Pengaturan</a>,
                            karena itu semua level (1&ndash;12) ditampilkan agar tetap bisa dipilih.
                            Sebaiknya lengkapi Jenjang &amp; Satuan Pendidikan terlebih dahulu.
                        </div>
                    <?php endif; ?>
                    <div class="row g-3">
                        <input type="hidden" name="id_bank" value="<?= esc($bank->id_bank) ?>">

                        <!-- Kode Bank -->
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-upc-scan me-1 text-primary"></i> Kode Bank Soal
                            </label>
                            <input type="text" class="form-control" name="kode" maxlength="20"
                                placeholder="Cth: PAS-BIO-2024" value="<?= esc($bank->bank_kode) ?>" required>
                        </div>

                        <!-- Mata Pelajaran -->
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-book me-1 text-primary"></i> Mata Pelajaran
                            </label>
                            <?= form_dropdown('mapel', $mapel, $bank->bank_mapel_id, 'id="select-mapel" class="form-select select2" required') ?>
                        </div>

                        <!-- Guru Pengampu -->
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-badge me-1 text-primary"></i> Guru Pengampu
                            </label>
                            <?= form_dropdown('guru', $gurus, $bank->bank_guru_id, 'id="select-guru" class="form-select select2" required') ?>
                        </div>

                        <!-- Level -->
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-layers me-1 text-primary"></i> Level
                            </label>
                            <?php
                            $arrLevel = [];
                            foreach ($level as $l) {
                                $arrLevel[$l->level] = 'Level ' . $l->level;
                            }
                            echo form_dropdown('level', $arrLevel, $bank->bank_level, 'id="select-level" class="form-select select2" data-placeholder="Pilih Level" required'); ?>
                        </div>

                        <!-- Jenis Ujian -->
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-clipboard-data me-1 text-primary"></i> Jenis Ujian
                            </label>
                            <?php
                            $arrJenis = [];
                            foreach (($jenis ?? []) as $j) {
                                if ((string) ($j->status ?? '1') !== '1') {
                                    continue;
                                }
                                $arrJenis[$j->id_jenis] = $j->nama_jenis;
                            }
                            echo form_dropdown('jenis', $arrJenis, $bank->bank_jenis_id ?? '', 'id="select-jenis" class="form-select select2" required');
                            ?>
                        </div>

                        <!-- Sub-tes -->
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-diagram-2 me-1 text-primary"></i> Sub-tes
                            </label>
                            <?php
                            $arrSubtes = [
                                'GENERAL'   => 'General / Mata Pelajaran Reguler',
                                'BIN'       => 'Bahasa Indonesia',
                                'MTK'       => 'Matematika',
                                'BING'      => 'Bahasa Inggris',
                                'FIS'       => 'Fisika',
                                'KIM'       => 'Kimia',
                                'BIO'       => 'Biologi',
                                'GEO'       => 'Geografi',
                                'EKO'       => 'Ekonomi',
                                'SEJ'       => 'Sejarah',
                                'SOS'       => 'Sosiologi',
                                'PPN'       => 'Pendidikan Pancasila',
                                'INF'       => 'Informatika',
                                'KEAHLIAN'  => 'Mapel Keahlian SMK',
                                'VERBAL'    => 'Penalaran Verbal (Analogi, Analitis, Sinonim)',
                                'KUANTITATIF' => 'Penalaran Kuantitatif (Deret & Aljabar)',
                                'LOGIS'     => 'Penalaran Logis & Spasial (Silogisme, Pola)',
                                'LITERASI'  => 'Literasi Membaca (Bahasa Indonesia / Inggris)',
                                'NUMERASI'  => 'Literasi Numerasi & Data'
                            ];
                            echo form_dropdown('subtes_code', $arrSubtes, $bank->subtes_code ?? 'GENERAL', 'id="select-subtes" class="form-select select2" required');
                            ?>
                        </div>

                        <!-- KKM -->
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-award me-1 text-primary"></i> KKM
                            </label>
                            <input type="number" class="form-control" name="kkm" placeholder="Cth: 75"
                                value="<?= esc($bank->kkm ?? '') ?>" required>
                        </div>

                        <!-- Pilih Kelas (Full Width) -->
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-people-fill me-1 text-primary"></i> Pilih Kelas Peserta
                                    </label>
                                    <select name="kelas[]" id="select-kelas" class="form-select select2"
                                        multiple="multiple" required data-placeholder="Pilih kelas..."></select>
                                    <div class="form-text mt-2">
                                        <i class="bi bi-info-circle me-1"></i> Kelas akan muncul otomatis setelah Anda memilih <b>Level</b>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pilih Kartu Soal (Full Width) -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="background-color: #f8f9fa;">
                                <div class="card-body p-3">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-collection me-1 text-primary"></i> Pilih Kartu Soal (Rencana)
                                    </label>
                                    <select name="kartu_soal" id="select-kartu" class="form-select select2" <?= !empty($force_kartu_workflow) ? 'required' : '' ?>>
                                        <option value=""><?= !empty($force_kartu_workflow) ? '-- Wajib pilih Kartu Soal --' : '-- Pilih Kartu Soal (opsional) --' ?></option>
                                        <?php $mapelLookup = is_array($mapel ?? null) ? $mapel : []; ?>
                                        <?php foreach (($kartu_soal ?? []) as $ks): ?>
                                            <?php
                                            $mapelName = (string) ($ks->nama_mapel ?? '');
                                            if ($mapelName === '' && !empty($ks->mapel_id)) {
                                                $mapelName = (string) ($mapelLookup[(int) $ks->mapel_id] ?? '');
                                            }
                                            $jenisName = $ks->nama_jenis ?? '';
                                            $periodeLabel = trim((string) (($tp_active->tahun ?? '-') . ' ' . ($smt_active->nama_smt ?? '-')));
                                            $label = esc($ks->judul ?? '-');
                                            if ($mapelName !== '') {
                                                $label .= ' (' . esc($mapelName) . ')';
                                            }
                                            if ($jenisName !== '') {
                                                $label .= ' - ' . esc($jenisName);
                                            }
                                            $label .= ' [' . esc($periodeLabel) . ']';
                                            ?>
                                            <option
                                                value="<?= (int) ($ks->id_kartu ?? 0) ?>"
                                                data-mapel-id="<?= (int) ($ks->mapel_id ?? 0) ?>"
                                                data-jenis-id="<?= (int) ($ks->id_jenis ?? 0) ?>"
                                                data-tp-id="<?= (int) ($ks->tp_id ?? 0) ?>"
                                                data-smt-id="<?= (int) ($ks->smt_id ?? 0) ?>"
                                                <?= (int) ($bank->kartu_soal_id ?? 0) === (int) ($ks->id_kartu ?? 0) ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAmbilDariKartu" title="Isi kuota dari kartu soal">
                                            <i class="bi bi-download me-1"></i>Ambil dari Kartu
                                        </button>
                                        <div class="form-text mb-0">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <?php if (!empty($force_kartu_workflow)): ?>
                                                Alur wajib guru: <b>Tujuan Pembelajaran -> Kartu Soal -> Bank Soal</b>. Anda harus memilih kartu soal yang valid.
                                            <?php else: ?>
                                                Pilih kartu soal untuk menghubungkan bank ini dengan rencana soal yang sudah dibuat.
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($force_kartu_workflow)): ?>
                        <div class="col-12">
                            <div class="alert alert-warning border-0 shadow-sm mb-0" id="workflow-alert">
                                <div class="fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Alur Penyusunan Soal</div>
                                <div class="small mt-1" id="workflow-text">Memeriksa kesiapan struktur pembelajaran...</div>
                            </div>
                        </div>
                        <?php endif; ?>
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
                                                    class='form-control form-control-sm bg-light'
                                                    value="<?= esc($bank->tampil_pg) ?>" readonly required />
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
                                                    class='form-control form-control-sm bg-light'
                                                    value="<?= esc($bank->tampil_kompleks) ?>" readonly required />
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
                                                    class='form-control form-control-sm bg-light'
                                                    value="<?= esc($bank->tampil_jodohkan) ?>" readonly required />
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
                                                    class='form-control form-control-sm bg-light'
                                                    value="<?= esc($bank->tampil_isian) ?>" readonly required />
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
                                                    class='form-control form-control-sm bg-light'
                                                    value="<?= esc($bank->tampil_esai) ?>" readonly required />
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
                                                    class='form-control form-control-sm bg-light'
                                                    value="<?= esc($bank->tampil_urutan ?? 0) ?>" readonly required />
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
                                                    class='form-control form-control-sm bg-light'
                                                    value="<?= esc($bank->tampil_cloze ?? 0) ?>" readonly required />
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
    </div>
</div>

<script>
    const isAdmin = '<?= $is_admin ?>';
    const forceWorkflow = '<?= !empty($force_kartu_workflow) ? '1' : '0' ?>';
    const kelasSelected = <?= json_encode($bank->bank_kelas ?? []) ?>;
    let idGuru = '<?= $bank->bank_guru_id ?: ($id_guru ?? '') ?>';
    let idMapel = '<?= $bank->bank_mapel_id ?? '' ?>';
    const activeTpId = '<?= (int) ($tp_active->id_tp ?? 0) ?>';
    const activeSmtId = '<?= (int) ($smt_active->id_smt ?? 0) ?>';

    $(document).ready(function () {
        let initialAutoFillDone = false;
        const selLevel = $('#select-level');
        const selKelas = $('#select-kelas');
        const selMapel = $('#select-mapel');
        const selGuru = $('#select-guru');
        const selKartu = $('#select-kartu');
        const selJenis = $('#select-jenis');
        const selSubtes = $('#select-subtes');
        const workflowAlert = $('#workflow-alert');
        const workflowText = $('#workflow-text');
        const submitBtn = $('#create button[type="submit"]');
        const fieldMap = {
            mapel: selMapel,
            jenis: selJenis,
            kartu_soal: selKartu
        };

        selMapel.select2({ width: '100%' });
        selGuru.select2({ width: '100%' });
        selLevel.select2({ width: '100%' });
        selJenis.select2({ width: '100%' });
        selSubtes.select2({ width: '100%' });
        selKelas.select2({ width: '100%' });
        selKartu.select2({ width: '100%', placeholder: forceWorkflow === '1' ? '-- Wajib pilih Kartu Soal --' : '-- Pilih Kartu Soal (opsional) --' });

        function clearFieldErrors() {
            Object.values(fieldMap).forEach(function ($el) {
                if (!$el || !$el.length) return;
                $el.removeClass('is-invalid');
                $el.next('.select2-container').find('.select2-selection').removeClass('is-invalid');
            });
        }

        function clearFieldError(field) {
            const $el = fieldMap[field];
            if (!$el || !$el.length) return;
            $el.removeClass('is-invalid');
            $el.next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }

        function applyFieldErrors(errors) {
            clearFieldErrors();
            if (!errors) return;
            Object.keys(errors).forEach(function (key) {
                const $el = fieldMap[key];
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid');
                $el.next('.select2-container').find('.select2-selection').addClass('is-invalid');
            });
        }

        // Pastikan idGuru terisi dari dropdown jika belum ada
        if (!idGuru) {
            idGuru = selGuru.val();
        }
        if (!idMapel) {
            idMapel = selMapel.val();
        }

        function renderKartuOptions(items, currentValue) {
            const fallbackText = forceWorkflow === '1' ? '-- Wajib pilih Kartu Soal --' : '-- Pilih Kartu Soal (opsional) --';
            selKartu.empty();
            selKartu.append(new Option(fallbackText, '', false, !currentValue));
            (items || []).forEach(function (o) {
                const val = String(o.id_kartu || '');
                const option = new Option(String(o.label || o.judul || '-'), val, false, val === String(currentValue || ''));
                option.dataset.mapelId = String(o.mapel_id || '');
                option.dataset.jenisId = String(o.id_jenis || '');
                option.dataset.tpId = String(o.tp_id || '');
                option.dataset.smtId = String(o.smt_id || '');
                selKartu.append(option);
            });
            selKartu.trigger('change.select2');

            if (forceWorkflow === '1' && (!items || items.length === 0)) {
                workflowAlert.removeClass('alert-success').addClass('alert-warning');
                workflowText.text('Belum ada Kartu Soal yang cocok untuk kombinasi mapel dan jenis ujian ini.');
                submitBtn.prop('disabled', true);
            }
        }

        function loadKartuOptionsByContext() {
            const selectedMapel = String(selMapel.val() || '');
            const selectedJenis = String(selJenis.val() || '');
            const currentValue = String(selKartu.val() || '');

            $.ajax({
                url: "<?= base_url('cbtbanksoal/getkartuoptions') ?>",
                type: "GET",
                dataType: "json",
                data: {
                    mapel_id: selectedMapel,
                    jenis_id: selectedJenis,
                    current_kartu_id: currentValue,
                    tp_id: activeTpId,
                    smt_id: activeSmtId
                },
                success: function (res) {
                    if (res && res.status) {
                        renderKartuOptions(res.data || [], currentValue);
                    } else {
                        renderKartuOptions([], '');
                    }
                    checkWorkflowReady();
                },
                error: function () {
                    renderKartuOptions([], '');
                    checkWorkflowReady();
                }
            });
        }

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
            // Endpoint getKelasLevel mengabaikan id_guru dan selalu mengirim SEMUA kelas
            // pada level tersebut, jadi kelas tidak boleh bergantung pada guru terpilih.
            if (!level) {
                selKelas.empty().append(new Option('Pilih Level terlebih dahulu', '', true, true));
                selKelas.trigger('change');
                return;
            }

            $.ajax({
                url: "<?= base_url('cbtbanksoal/getkelaslevel') ?>?level=" + encodeURIComponent(level) + "&id_guru=" + encodeURIComponent(idGuru || '') + '&mapel=' + encodeURIComponent(mapel || ''),
                type: "GET",
                dataType: "json",
                success: function (data) {
                    selKelas.empty();
                    const rows = data.kelas || [];
                    if (rows.length === 0) {
                        selKelas.append(new Option('Belum ada kelas pada level ini', '', true, true));
                        selKelas.trigger('change');
                        return;
                    }
                    const selectedIds = kelasSelected.map(k => k.kelas_id);
                    $.each(rows, function (i, item) {
                        const isSelected = selectedIds.includes(item.id_kelas);
                        selKelas.append(new Option(item.kode_kelas, item.id_kelas, isSelected, isSelected));
                    });
                    selKelas.trigger('change');
                },
                error: function () {
                    selKelas.empty().append(new Option('Gagal memuat daftar kelas', '', true, true));
                    selKelas.trigger('change');
                }
            });
        }

        function checkWorkflowReady() {
            if (forceWorkflow !== '1') return;

            if (!idMapel) {
                workflowAlert.removeClass('alert-success alert-info').addClass('alert-warning');
                workflowText.html('<i class="bi bi-exclamation-triangle me-1"></i> Pilih mapel terlebih dahulu.');
                submitBtn.prop('disabled', true);
                return;
            }

            $.ajax({
                url: "<?= base_url('cbtbanksoal/checkworkflow') ?>?mapel_id=" + idMapel + "&kartu_id=" + (selKartu.val() || '') + "&jenis_id=" + (selJenis.val() || ''),
                type: "GET",
                success: function (res) {
                    const d = res.data || {};
                    const tp = d.tujuan_count || 0;
                    const kTotal = d.kartu_count || 0;
                    const kApproved = d.kartu_approved_count || 0;
                    const banks = d.bank_count || 0;
                    const soals = d.soal_count || 0;

                    // Build step progress
                    let steps = '<div class="d-flex flex-wrap gap-2 mt-2 small">';
                    steps += '<span class="badge ' + (tp > 0 ? 'bg-success' : 'bg-secondary') + '"><i class="bi bi-check-circle me-1"></i>TP: ' + tp + '</span>';
                    steps += '<span class="badge ' + (kApproved > 0 ? 'bg-success' : (kTotal > 0 ? 'bg-warning text-dark' : 'bg-secondary')) + '"><i class="bi bi-card-checklist me-1"></i>Kartu: ' + kApproved + '/' + kTotal + ' approved</span>';
                    steps += '<span class="badge ' + (banks > 0 ? 'bg-success' : 'bg-secondary') + '"><i class="bi bi-archive me-1"></i>Bank: ' + banks + '</span>';
                    steps += '<span class="badge ' + (soals > 0 ? 'bg-success' : 'bg-secondary') + '"><i class="bi bi-pencil-square me-1"></i>Soal: ' + soals + '</span>';
                    steps += '</div>';

                    if (res.status) {
                        if (!selKartu.val()) {
                            workflowAlert.removeClass('alert-success alert-danger').addClass('alert-info');
                            workflowText.html('<i class="bi bi-arrow-right-circle me-1"></i> ' + (res.message || 'Pilih Kartu Soal untuk melanjutkan.') + steps);
                            submitBtn.prop('disabled', true);
                            return;
                        }
                        workflowAlert.removeClass('alert-warning alert-danger alert-info').addClass('alert-success');
                        workflowText.html('<i class="bi bi-check2-all me-1"></i> ' + (res.message || 'Workflow siap.') + ' Bank soal siap disimpan.' + steps);
                        submitBtn.prop('disabled', false);
                    } else {
                        // Show guidance based on what's missing
                        let guidance = res.message || 'Struktur pembelajaran belum siap.';
                        if (tp <= 0) {
                            guidance += ' <a href="<?= base_url('guru/tujuan') ?>" class="alert-link">Buat Tujuan Pembelajaran →</a>';
                        } else if (kTotal <= 0) {
                            guidance += ' <a href="<?= base_url('cbtkartusoal/create') ?>" class="alert-link">Buat Kartu Soal →</a>';
                        } else if (kApproved <= 0) {
                            guidance += ' <a href="<?= base_url('cbtkartusoal') ?>" class="alert-link">Kelola Kartu Soal →</a>';
                        }
                        workflowAlert.removeClass('alert-success alert-info').addClass('alert-warning');
                        workflowText.html('<i class="bi bi-exclamation-triangle me-1"></i> ' + guidance + steps);
                        submitBtn.prop('disabled', true);
                    }
                },
                error: function () {
                    workflowAlert.removeClass('alert-success alert-info').addClass('alert-danger');
                    workflowText.html('<i class="bi bi-x-circle me-1"></i> Gagal memeriksa alur workflow. Coba refresh halaman.');
                    submitBtn.prop('disabled', true);
                }
            });
        }

        selGuru.on('change', function () {
            idGuru = $(this).val();
            getKelasLevel(selLevel.val(), selMapel.val());
        });

        selLevel.on('change', function () {
            getKelasLevel($(this).val(), selMapel.val());
        });

        selMapel.on('change', function () {
            clearFieldError('mapel');
            idMapel = $(this).val();
            getGuruMapel(idMapel);
            // Untuk guru, juga reload kelas saat mapel berubah
            getKelasLevel(selLevel.val(), idMapel);
            loadKartuOptionsByContext();
            syncSubtesWithMapel();
        });

        // Auto-sinkron Sub-tes mengikuti Mata Pelajaran terpilih.
        const MAPEL_SUBTES_RULES = [
            [/bahasa.*indonesia|\bb indo\b|\bbindo\b/, 'BIN'],
            [/bahasa.*inggris|\bb inggris\b|binggris|\bbing\b/, 'BING'],
            [/matematika|\bmtk\b/, 'MTK'],
            [/fisika/, 'FIS'],
            [/kimia/, 'KIM'],
            [/biologi/, 'BIO'],
            [/geografi/, 'GEO'],
            [/ekonomi/, 'EKO'],
            [/sejarah/, 'SEJ'],
            [/sosiologi/, 'SOS'],
            [/pancasila|ppkn/, 'PPN'],
            [/informatika|\btik\b|teknologi informasi/, 'INF']
        ];

        function subtesKodeFromMapel(text) {
            const s = String(text || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
            if (!s) return null;
            for (const [re, kode] of MAPEL_SUBTES_RULES) {
                if (re.test(s)) return kode;
            }
            return null;
        }

        function syncSubtesWithMapel() {
            if (!selSubtes.length) return;
            const kode = subtesKodeFromMapel(selMapel.find('option:selected').text());
            const target = kode || 'GENERAL';
            if (selSubtes.val() !== target) {
                selSubtes.val(target).trigger('change.select2');
            }
        }

        if (selJenis.length) {
            selJenis.on('change', function () {
                clearFieldError('jenis');
                loadKartuOptionsByContext();
            });
        }

        function fillKuotaFromKartu() {
            const kartuId = selKartu.val();
            if (!kartuId) {
                Swal.fire('Informasi', 'Pilih kartu soal terlebih dahulu.', 'info');
                return;
            }
            $.ajax({
                url: "<?= base_url('cbtbanksoal/api-get-kartu-summary') ?>",
                type: "GET",
                data: { kartu_id: kartuId },
                beforeSend: function () {
                    Swal.fire({ title: 'Mengambil data...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
                },
                success: function(res) {
                    Swal.close();
                    if (res.status && res.data) {
                        const counts = {
                            'pg': parseInt(res.data[1] || 0),
                            'pg2': parseInt(res.data[2] || 0),
                            'jodohkan': parseInt(res.data[3] || 0),
                            'isian': parseInt(res.data[4] || 0),
                            'essai': parseInt(res.data[5] || 0),
                            'urutan': parseInt(res.data[6] || 0),
                            'cloze': parseInt(res.data[7] || 0)
                        };

                        let activeTypes = [];
                        for (const [key, val] of Object.entries(counts)) {
                            $('#jml-' + key).val(val);
                            if (val > 0) activeTypes.push(key);
                        }

                        const isNewBank = !$('input[name="id_bank"]').val();
                        if (activeTypes.length > 0 && (isNewBank || initialAutoFillDone)) {
                            let baseWeight = Math.floor(100 / activeTypes.length);
                            let remainder = 100 % activeTypes.length;
                            for (const key of Object.keys(counts)) {
                                if (counts[key] > 0) {
                                    let weight = baseWeight + (remainder > 0 ? 1 : 0);
                                    remainder--;
                                    $('#bobot-' + key).val(weight);
                                } else {
                                    $('#bobot-' + key).val(0);
                                }
                            }
                        }

                        initialAutoFillDone = true;
                        calculateTotals();
                    } else {
                        Swal.fire('Gagal', res?.message || 'Gagal mengambil data kartu.', 'warning');
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire('Gagal', 'Kesalahan jaringan.', 'error');
                }
            });
        }

        selKartu.on('change change.select2', function () {
            clearFieldError('kartu_soal');
            checkWorkflowReady();
            fillKuotaFromKartu();
        });

        $('#btnAmbilDariKartu').on('click', fillKuotaFromKartu);

        // Initialize display
        loadKartuOptionsByContext();
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
                        clearFieldErrors();
                        Swal.fire({
                            title: 'Sukses',
                            text: data.message || 'Bank soal berhasil disimpan',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.history.back();
                        });
                    } else {
                        applyFieldErrors(data.errors || {});
                        Swal.fire('Peringatan Workflow', data.message || 'Gagal menyimpan bank soal', 'warning');
                    }
                },
                error: function () {
                    clearFieldErrors();
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                }
            });
        });
    });
</script><?= $this->endSection() ?>
