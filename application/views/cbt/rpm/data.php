<!-- CSS Dependencies -->
<link rel="stylesheet" href="<?= base_url('assets/app/css/rpm.css') ?>">

<!-- Content Wrapper. Contains page content -->
<main class="app-main pt-4">
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- FULL WIDTH PREVIEW & TOOLS -->
                <div class="col-md-12">
                    <!-- TOOLS & ACTION BAR -->
                    <div class="card card-outline card-purple mb-3" style="border-top-color: #6f42c1;">
                        <div class="card-header pb-0 border-bottom-0">
                            <h3 class="card-title font-weight-bold text-purple" style="color: #6f42c1;">
                                Magic AI RPM Generator by IKASMEDIA
                            </h3>
                            <div class="card-tools ms-auto">
                                <button id="btn-api-key" class="btn btn-warning" title="Set API Key">
                                    <i class="b bi-key"></i>
                                </button>
                                <button type="button" class="btn btn-info" onclick="showGuide()">
                                    <i class="bi bi-question-circle"></i> Panduan
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 10px;">

                                <!-- MAIN CONFIG BUTTON -->
                                <button type="button" class="btn bg-gradient-primary shadow-sm font-weight-bold"
                                    data-bs-toggle="modal" data-bs-target="#configModal">
                                    <i class="bi bi-cog me-1"></i> Generate RPM
                                </button>

                                <div class="vr mx-2 bg-secondary" style="width: 1px; height: 30px;"></div>

                                <!-- Core Magic Tools -->
                                <button type="button" id="btn-atp"
                                    class="btn btn-outline-primary btn-sm shadow-sm magic-btn-top fw-bold"><i
                                        class="bi bi-diagram-3-fill me-1"></i> ATP</button>
                                <button type="button" id="btn-materi"
                                    class="btn btn-outline-success btn-sm shadow-sm magic-btn-top fw-bold"><i
                                        class="bi bi-book-half me-1"></i> Materi</button>
                                <button type="button" id="btn-lkpd"
                                    class="btn btn-outline-warning btn-sm shadow-sm magic-btn-top fw-bold text-dark"><i
                                        class="bi bi-clipboard-check-fill me-1"></i> LKPD</button>
                                <button type="button" id="btn-quiz"
                                    class="btn btn-danger btn-sm shadow-sm magic-btn-top fw-bold"><i
                                        class="bi bi-lightning-charge-fill me-1"></i> Quiz HOTS</button>
                                <button type="button" id="btn-akm"
                                    class="btn btn-outline-info btn-sm shadow-sm magic-btn-top fw-bold text-dark"><i
                                        class="bi bi-puzzle-fill me-1"></i> Soal AKM</button>
                                <button type="button" id="btn-rubric"
                                    class="btn btn-outline-secondary btn-sm shadow-sm magic-btn-top fw-bold"><i
                                        class="bi bi-list-check me-1"></i> Rubrik</button>

                                <div class="vr mx-2 bg-secondary" style="width: 1px; height: 30px;"></div>

                                <!-- Additional Tools -->
                                <button type="button" id="btn-misconception"
                                    class="btn btn-danger btn-sm shadow-sm magic-btn-top fw-bold"><i
                                        class="bi bi-exclamation-diamond-fill me-1"></i> Miskonsepsi</button>
                                <button type="button" id="btn-hook"
                                    class="btn btn-outline-warning btn-sm shadow-sm magic-btn-top fw-bold text-dark"><i
                                        class="bi bi-lightbulb-fill me-1"></i> Pemantik</button>
                                <button type="button" id="btn-ice-breaking"
                                    class="btn btn-outline-info btn-sm shadow-sm magic-btn-top fw-bold text-dark"><i
                                        class="bi bi-joystick me-1"></i> Ice Break</button>
                                <button type="button" id="btn-illustrator"
                                    class="btn btn-outline-primary btn-sm shadow-sm magic-btn-top fw-bold"><i
                                        class="bi bi-stars me-1"></i> Visual AI</button>
                            </div>
                        </div>
                    </div>

                    <!-- PREVIEW AREA -->
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header d-flex p-0 align-items-center">
                            <h3 class="card-title p-3 font-weight-bold">
                                <i class="bi bi-file-text me-1"></i> Preview Dokumen
                            </h3>
                            <div class="ms-auto p-2 d-flex align-items-center">
                                <span id="doc-status" class="text-muted small me-3">Menunggu input...</span>

                                <div class="btn-group ms-2">
                                    <div class="btn-group me-1">
                                        <button type="button"
                                            class="btn btn-sm btn-default font-weight-bold dropdown-toggle shadow-sm"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="bi bi-file-word text-primary me-1"></i> Download Word
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item font-weight-bold" href="#" id="dw-all">
                                                <i class="bi bi-layer-group me-2 text-primary"></i> Gabungan (All in
                                                One)
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" id="dw-rpm">
                                                <i class="bi bi-file-contract me-2 text-secondary"></i> Hanya RPM Utama
                                            </a>
                                            <a class="dropdown-item" href="#" id="dw-materi">
                                                <i class="bi bi-book-open me-2 text-success"></i> Hanya Materi Ajar
                                            </a>
                                            <a class="dropdown-item" href="#" id="dw-lkpd">
                                                <i class="bi bi-pencil-ruler me-2 text-warning"></i> Hanya LKPD
                                            </a>
                                            <a class="dropdown-item" href="#" id="dw-quiz">
                                                <i class="bi bi-check-double me-2 text-danger"></i> Hanya Kuis/Soal
                                            </a>
                                            <a class="dropdown-item" href="#" id="dw-lain">
                                                <i class="bi bi-folder-plus me-2 text-info"></i> Lainnya (Rubrik/Media)
                                            </a>
                                        </div>
                                    </div>
                                    <button id="download-pdf" class="btn btn-sm btn-default font-weight-bold">
                                        <i class="bi bi-file-pdf text-danger me-1"></i> PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-secondary"
                            style="background-color: #6c757d !important; min-height: 800px; overflow-y: auto;">
                            <div id="preview-container">
                                <div class="paper-sheet">
                                    <div class="text-center text-muted" style="margin-top: 10cm;">
                                        <i class="bi bi-file-earmark-plus mb-3 text-gray-300"
                                            style="color: #cbd5e1; font-size: 3em;"></i>
                                        <p>Silakan klik tombol <strong>"Data & Konfigurasi"</strong> di atas<br>untuk
                                            mulai membuat dokumen.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
    <!-- /.content-wrapper -->

    <!-- CONFIG FORM MODAL -->
    <div class="modal fade" id="configModal" tabindex="-1" role="dialog" aria-labelledby="configModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title font-weight-bold text-white" id="configModalLabel">
                        <i class="bi bi-pencil me-2"></i> Konfigurasi RPM
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <form id="rpp-form">
                        <!-- SECTION 1: IDENTITAS UTAMA -->
                        <h6 class="font-weight-bold text-primary mb-3"><i class="bi bi-school me-2"></i>Identitas
                            Pembelajaran</h6>
                        <div class="row">
                            <input type="text" class="form-control" id="nama-sekolah"
                                value="<?= $setting->sekolah ?? 'Nama Sekolah' ?>" hidden>
                            <input type="text" class="form-control" id="nama-kepsek"
                                value="<?= $setting->kepsek ?? '' ?>" hidden>
                            <input type="text" class="form-control" id="nip-kepsek" value="<?= $setting->nip ?? '' ?>"
                                hidden>
                            <input type="text" class="form-control" id="nip-guru" value="<?= $guru->nip ?? '' ?>"
                                hidden>
                            <input type="text" class="form-control" id="db-api-key" value="<?= $guru->api_key ?? '' ?>"
                                hidden>
                            <div class="col-md-6">
                                <label>Guru Mata Pelajaran</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    </div>
                                    <select class="form-control" id="nama-guru">
                                        <option value="" disabled selected>Pilih Guru</option>
                                        <?php if (!empty($all_gurus)): ?>
                                            <?php foreach ($all_gurus as $g): ?>
                                                <option value="<?= $g->nama_guru ?>" data-nip="<?= $g->nip ?>"
                                                    <?= (isset($guru->nama_guru) && $guru->nama_guru == $g->nama_guru) ? 'selected' : '' ?>>
                                                    <?= $g->nama_guru ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Mata Pelajaran</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="bi bi-book"></i></span>
                                        </div>
                                        <select class="form-control" id="mapel" required>
                                            <option value="" disabled selected>Pilih Mata Pelajaran</option>
                                            <?php if (!empty($all_mapels)): ?>
                                                <?php foreach ($all_mapels as $id => $nama_mapel): ?>
                                                    <option value="<?= $nama_mapel ?>" <?= (isset($guru->mapel) && $guru->mapel == $nama_mapel) ? 'selected' : '' ?>>
                                                        <?= $nama_mapel ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Kelas / Fase</label>
                                    <select class="form-control" id="kelas" required>
                                        <option value="" disabled selected>Pilih Fase</option>
                                        <option value="X / Fase E">X / Fase E (kelas 10 SMA/SMK)</option>
                                        <option value="XI / Fase F">XI / Fase F (kelas 11 SMA/SMK)</option>
                                        <option value="XII / Fase F">XII / Fase F (kelas 12 SMA/SMK)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Alokasi Waktu</label>
                                    <input type="text" class="form-control" id="alokasi-waktu"
                                        placeholder="Contoh: 2 Pertemuan (6 JP)" required>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- SECTION 2: MATERI & KONTEKS -->
                        <h6 class="font-weight-bold text-success mb-3"><i class="bi bi-bullseye me-2"></i>Materi &
                            Konteks</h6>
                        <div class="mb-3">
                            <label>Materi Pokok / Topik</label>
                            <input type="text" class="form-control" id="materi-pokok"
                                placeholder="Topik utama yang akan diajarkan..." required>
                        </div>
                        <div class="mb-3">
                            <label>Potensi / Kearifan Lokal <span class="badge bg-warning ms-1">Wajib</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                </div>
                                <input type="text" class="form-control" id="potensi-lokal"
                                    placeholder="Contoh: Tenun Ikat, Pertanian Jagung, dll..." required>
                            </div>
                            <small class="text-muted">Konteks daerah untuk personalisasi pembelajaran.</small>
                        </div>

                        <hr>

                        <!-- SECTION 3: DATA PERSONIL -->
                        <h6 class="font-weight-bold text-info mb-3"><i class="bi bi-people me-2"></i>Tempat & Tanggal
                            Surat</h6>
                        <div class="mb-3 mt-3 bg-light p-2 rounded">
                            <div class="row">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <input type="text" class="form-control" id="kota"
                                        value="<?= $setting->kota ?? '' ?>" placeholder="Kota/Kab">
                                </div>
                                <div class="col-md-8">
                                    <input type="date" class="form-control" id="tanggal-surat"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" id="generate-button"
                                class="btn btn-primary font-weight-bold shadow-sm">
                                <i class="bi bi-magic me-2"></i> Simpan & Generate
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- HELP MODAL -->
    <div class="modal fade" id="guideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Panduan Fitur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <p>1. Klik tombol <strong>"Generate RPM"</strong> untuk mengisi data dasar RPM.</p>
                    <p>2. Klik <strong>"Simpan & Generate RPM Utama"</strong> untuk membuat kerangka dokumen.</p>
                    <p>3. Gunakan tombol-tombol <strong>Magic Tools</strong> di toolbar atas untuk menambahkan konten
                        ATP, Materi, Quiz, dll secara otomatis.</p>
                    <p>4. Untuk membuat API Key, Kunjungi <a href="https://aistudio.google.com/api-keys"
                            target="_blank">https://aistudio.google.com/api-keys</a> login menggunakan akun belajar.id
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="loading-modal" class="loading-overlay d-none">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
        <h4 class="mt-4 font-weight-bold text-dark" id="loading-text">Sedang Memproses...</h4>
        <p class="text-muted" id="loading-subtext">Mohon tunggu sebentar...</p>
    </div>


    <!-- Script to handle Guru Selection Change -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const guruSelect = document.getElementById('nama-guru');
            const nipInput = document.getElementById('nip-guru');

            if (guruSelect && nipInput) {
                guruSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const nip = selectedOption.getAttribute('data-nip');
                    if (nip) {
                        nipInput.value = nip;
                    } else {
                        nipInput.value = '';
                    }
                });
            }
        });
    </script>

    <!-- CSRF Token Injection -->
    <script>
        const CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
        const CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';
    </script>

    <!-- Main App Script -->
    <script src="<?= base_url('assets/app/js/rpm.js') ?>"></script>