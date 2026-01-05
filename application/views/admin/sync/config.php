<style>
    /* Custom Checkmark for Radio Buttons */
    .container-jawaban input:checked~.checkmark {
        background-color: #0d6efd !important;
        /* Force Bootstrap Primary Blue */
        border-color: #0d6efd !important;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.4) !important;
    }

    .container-jawaban input:checked~.checkmark::before {
        content: "\F26B";
        /* Bootstrap Icon: bi-check-lg */
        font-family: "bootstrap-icons";
        color: #fff !important;
        font-size: 1.2rem;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
    }
</style>
<main class="app-main pt-4 bg-light">
    <!-- Header with Gradient Border -->
    <div class="content-header mb-4 illustration-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="m-0 font-weight-bold text-dark mb-1">
                        <i class="bi bi-sliders text-primary me-2"></i><?= $subjudul ?? 'Konfigurasi Sinkronisasi' ?>
                    </h4>
                    <small class="text-muted">Atur koneksi dan parameter sinkronisasi sistem.</small>
                </div>
                <a href="<?= base_url('sync') ?>"
                    class="btn btn-danger text-white font-weight-bold btn-sm px-4 rounded-pill shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form id="formConfig" autocomplete="off">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                    value="<?= $this->security->get_csrf_hash() ?>">

                <div class="row">
                    <!-- LEFT COLUMN: Main Settings -->
                    <div class="col-lg-8">

                        <!-- 1. TIPE SERVER -->
                        <div class="card border-0 shadow-sm mb-4 rounded-lg overflow-hidden">
                            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 ps-4">
                                <h6 class="font-weight-bold text-uppercase text-primary letter-spacing-1"
                                    style="letter-spacing:1px; font-size:0.75rem;">
                                    Mode Operasi
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label
                                            class="container-jawaban py-4 pe-4 ps-5 h-100 d-flex align-items-start shadow-none border"
                                            style="border-radius:12px; padding-left: 4.5rem !important;">
                                            <input type="radio" name="server_type" value="lokal" <?= (!$config || (is_object($config) && $config->server_type === 'lokal')) ? 'checked' : '' ?>>
                                            <span class="checkmark mt-1 shadow-sm"
                                                style="left:1.5rem; top: 1.5rem; transform: none;"></span>
                                            <div class="ps-2">
                                                <h6 class="font-weight-bold mb-2 text-dark">Server Lokal</h6>
                                                <p class="text-muted small mb-0" style="line-height:1.5;">Mode Client.
                                                    Digunakan di sekolah untuk sinkronisasi data dengan server pusat.
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label
                                            class="container-jawaban py-4 pe-4 ps-5 h-100 d-flex align-items-start shadow-none border"
                                            style="border-radius:12px; padding-left: 4.5rem !important;">
                                            <input type="radio" name="server_type" value="pusat" <?= ($config && is_object($config) && $config->server_type === 'pusat') ? 'checked' : '' ?>>
                                            <span class="checkmark mt-1 shadow-sm"
                                                style="left:1.5rem; top: 1.5rem; transform: none;"></span>
                                            <div class="ps-2">
                                                <h6 class="font-weight-bold mb-2 text-dark">Server Pusat</h6>
                                                <p class="text-muted small mb-0" style="line-height:1.5;">Mode Server
                                                    (Cloud). Menjadi pusat data dan penampung dari seluruh sekolah.</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. KONEKSI (Hanya tampil jika Lokal) -->
                        <div id="configLokal" class="card border-0 shadow-sm mb-4 rounded-lg">
                            <div class="card-header bg-white border-bottom-0 pt-4 ps-4">
                                <h6 class="font-weight-bold text-uppercase text-info letter-spacing-1"
                                    style="letter-spacing:1px; font-size:0.75rem;">
                                    Pengaturan Koneksi
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="alert alert-light border-start border-info shadow-sm mb-4">
                                    <div class="d-flex">
                                        <i class="bi bi-info-circle-fill text-info me-3 mt-1"></i>
                                        <small class="text-muted ms-2">Masukkan URL Server Pusat dan API Credential yang
                                            valid. Data ini digunakan untuk otentikasi saat sinkronisasi.</small>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center mb-4">
                                    <label class="col-sm-3 col-form-label font-weight-bold text-dark text-sm">URL Server
                                        Pusat <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <div class="input-group shadow-sm rounded-pill overflow-hidden">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-0 ps-3"><i
                                                        class="bi bi-globe text-primary"></i></span>
                                            </div>
                                            <input type="url" name="pusat_url" class="form-control border-0 bg-light"
                                                placeholder="https://cbt.ikasmedia.com"
                                                value="<?= isset($config->pusat_url) ? $config->pusat_url : '' ?>"
                                                style="height: 45px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center mb-4">
                                    <label class="col-sm-3 col-form-label font-weight-bold text-dark text-sm">API Key
                                        <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <div class="input-group shadow-sm rounded-pill overflow-hidden">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-0 ps-3"><i
                                                        class="bi bi-key text-warning"></i></span>
                                            </div>
                                            <input type="text" name="api_key" class="form-control border-0 bg-light"
                                                placeholder="Key Sekolah"
                                                value="<?= isset($config->api_key) ? $config->api_key : '' ?>"
                                                style="height: 45px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row align-items-center">
                                    <label class="col-sm-3 col-form-label font-weight-bold text-dark text-sm">API
                                        Secret</label>
                                    <div class="col-sm-9">
                                        <div class="input-group shadow-sm rounded-pill overflow-hidden">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-0 ps-3"><i
                                                        class="bi bi-shield-lock text-danger"></i></span>
                                            </div>
                                            <input type="password" name="api_secret"
                                                class="form-control border-0 bg-light" placeholder="Rahasia"
                                                value="<?= isset($config->api_secret) ? $config->api_secret : '' ?>"
                                                style="height: 45px;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-light border-0 text-muted pe-3"
                                                    onclick="togglePassword(this)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. OTOMATISASI -->
                        <div class="card border-0 shadow-sm mb-4 rounded-lg">
                            <div class="card-header bg-white border-bottom-0 pt-4 ps-4">
                                <h6 class="font-weight-bold text-uppercase text-success letter-spacing-1"
                                    style="letter-spacing:1px; font-size:0.75rem;">
                                    Jadwal & Background
                                </h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-center mb-4">
                                    <div class="col-md-8">
                                        <div class="custom-control custom-switch custom-switch-lg">
                                            <input type="checkbox" class="custom-control-input" id="autoSync"
                                                name="auto_sync" value="1" <?= ($config && is_object($config) && $config->auto_sync) ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold text-dark"
                                                for="autoSync" style="padding-top:2px;">Aktifkan Auto Sync</label>
                                            <p class="text-muted small mt-1 ps-1 mb-0">Sistem akan melakukan
                                                sinkronisasi otomatis di latar belakang.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mt-3 mt-md-0">
                                        <div class="input-group input-group-sm shadow-sm rounded-pill overflow-hidden">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-0 ps-3">Interval</span>
                                            </div>
                                            <input type="number" name="sync_interval"
                                                class="form-control border-0 text-center font-weight-bold text-primary"
                                                min="5" max="1440"
                                                value="<?= isset($config->sync_interval) ? $config->sync_interval : 60 ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-white border-0 pe-3">Menit</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="border-light">
                                <div class="custom-control custom-switch custom-switch-lg mt-3">
                                    <input type="checkbox" class="custom-control-input" id="syncStartup"
                                        name="sync_on_startup" value="1" <?= (!$config || (is_object($config) && $config->sync_on_startup)) ? 'checked' : '' ?>>
                                    <label class="custom-control-label font-weight-bold text-dark" for="syncStartup"
                                        style="padding-top:2px;">Sinkronisasi Saat Login</label>
                                    <p class="text-muted small mt-1 ps-1 mb-0">Cek pembaruan data setiap kali Admin
                                        login ke dashboard.</p>
                                </div>

                                <!-- Cron Job Setup -->
                                <div class="alert alert-light border mt-4 mb-0 rounded-lg">
                                    <h6 class="font-weight-bold text-dark mb-3">
                                        <i class="bi bi-clock-history text-success me-2"></i>Setup Cron Job
                                    </h6>
                                    <p class="text-muted small mb-2">Untuk sinkronisasi otomatis di background,
                                        tambahkan cron job:</p>

                                    <div class="bg-dark text-light p-3 rounded-lg mb-2"
                                        style="font-family: monospace; font-size: 0.75rem; overflow-x: auto;">
                                        <strong class="text-warning"># Setiap 30 menit (CLI):</strong><br>
                                        */30 * * * * cd <?= FCPATH ?> && php index.php sync_cron run<br><br>
                                        <strong class="text-warning"># Via HTTP dengan key:</strong><br>
                                        */30 * * * * wget -qO-
                                        "<?= base_url('sync_cron/run?key=AKM_CRON_SECRET_KEY_2026') ?>"
                                    </div>

                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Ganti <code>AKM_CRON_SECRET_KEY_2026</code> dengan key rahasia di
                                        <code>Sync_cron.php</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- SAVE BUTTON -->
                        <button type="submit"
                            class="btn btn-primary w-100 shadow-lg py-3 rounded-pill font-weight-bold text-uppercase letter-spacing-1 mb-5 transition-hover">
                            <i class="bi bi-save2 me-2"></i> Simpan Konfigurasi
                        </button>
                    </div>

                    <!-- RIGHT COLUMN: Tools -->
                    <div class="col-lg-4">

                        <!-- CONNECTION STATUS -->
                        <div class="card border-0 shadow mb-4 rounded-lg bg-gradient-light" id="connectionStatusCard">
                            <div class="card-body text-center p-4">
                                <h6 class="text-uppercase text-muted font-weight-bold letter-spacing-1 mb-4"
                                    style="letter-spacing: 1px; font-size: 0.75rem;">Status Koneksi</h6>

                                <div id="connectionStatusBody" class="py-2">
                                    <div class="mb-3">
                                        <div class="position-relative d-inline-block">
                                            <i class="bi bi-circle-fill text-black-50"
                                                style="font-size: 4rem; opacity: 0.1;"></i>
                                            <i class="bi bi-question position-absolute text-muted"
                                                style="top:50%; left:50%; transform:translate(-50%, -50%); font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted font-weight-bold mb-0">Belum diuji</p>
                                </div>

                                <button type="button"
                                    class="btn btn-white shadow-sm w-100 mt-4 rounded-pill font-weight-bold text-primary"
                                    onclick="testConnection()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Uji Koneksi
                                </button>
                            </div>
                        </div>

                        <!-- HELP SECTION -->
                        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                            <div class="card-header bg-white border-bottom p-4">
                                <h6 class="font-weight-bold text-dark m-0 d-flex align-items-center"><i
                                        class="bi bi-life-preserver mr-2 text-warning"></i> <span class="ms-2">Panduan
                                        Singkat</span></h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="accordion" id="helpAccordion">
                                    <div class="card border-0 mb-0">
                                        <div class="card-header bg-white border-0 p-0" id="headingLokal">
                                            <h2 class="mb-0">
                                                <button
                                                    class="btn btn-link w-100 text-start font-weight-bold text-dark py-3 px-4 d-flex justify-content-between align-items-center text-decoration-none"
                                                    type="button" data-bs-toggle="collapse" data-bs-target="#helpLokal">
                                                    Setup Server Lokal
                                                    <i class="bi bi-chevron-down small text-muted"></i>
                                                </button>
                                            </h2>
                                        </div>
                                        <div id="helpLokal" class="collapse show" data-bs-parent="#helpAccordion">
                                            <div class="card-body py-2 px-4 bg-light text-muted small">
                                                <ol class="ps-3 mb-2">
                                                    <li class="mb-1">Pilih tipe <b>Server Lokal</b>.</li>
                                                    <li class="mb-1">Isi URL Server Pusat (misal:
                                                        <code>https://cbt.pusat.com</code>).
                                                    </li>
                                                    <li class="mb-1">Masukkan API Key & Secret yang didapat dari Admin
                                                        Pusat.</li>
                                                    <li class="mb-1">Klik <b>Uji Koneksi</b> untuk verifikasi.</li>
                                                    <li>Simpan Konfigurasi.</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card border-0 mb-0">
                                        <div class="card-header bg-white border-top p-0" id="headingPusat">
                                            <h2 class="mb-0">
                                                <button
                                                    class="btn btn-link w-100 text-start font-weight-bold text-dark py-3 px-4 d-flex justify-content-between align-items-center text-decoration-none collapsed"
                                                    type="button" data-bs-toggle="collapse" data-bs-target="#helpPusat">
                                                    Setup Server Pusat
                                                    <i class="bi bi-chevron-down small text-muted"></i>
                                                </button>
                                            </h2>
                                        </div>
                                        <div id="helpPusat" class="collapse" data-bs-parent="#helpAccordion">
                                            <div class="card-body py-2 px-4 bg-light text-muted small">
                                                <p class="mb-2">Jika Anda adalah pengelola server pusat:</p>
                                                <ol class="ps-3 mb-2">
                                                    <li class="mb-1">Pilih tipe <b>Server Pusat</b>.</li>
                                                    <li class="mb-1">Simpan Konfigurasi.</li>
                                                    <li>Kelola data sekolah di menu Manajemen Sekolah.</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light text-center border-top-0 py-3">
                                <small class="text-muted d-block font-weight-bold">Modul Sinkronisasi v2.1</small>
                                <small class="text-black-50">&copy; 2026 Ikasmedia CBT</small>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
    const BASE_URL = '<?= base_url() ?>';

    $(document).ready(function () {
        toggleConfigSections();
        $('input[name="server_type"]').on('change', function () {
            toggleConfigSections();
        });
        $('#formConfig').on('submit', function (e) {
            e.preventDefault();
            saveConfig();
        });

        // Auto Check Connection if Local and URL is configured
        setTimeout(function () {
            const serverType = $('input[name="server_type"]:checked').val();
            const pusatUrl = $('input[name="pusat_url"]').val();
            if (serverType === 'lokal' && pusatUrl && pusatUrl.length > 5) {
                testConnection();
            }
        }, 800);
    });

    function toggleConfigSections() {
        let serverType = $('input[name="server_type"]:checked').val();
        if (serverType === 'pusat') {
            $('#configLokal').slideUp();
        } else {
            $('#configLokal').slideDown();
        }
    }

    function togglePassword(btn) {
        let input = $(btn).closest('.input-group').find('input');
        let icon = $(btn).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    }

    function testConnection() {
        $('#connectionStatusBody').html(`
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="text-primary font-weight-bold letter-spacing-1 small">MENGHUBUNGI SERVER...</p>
    `);

        $.ajax({
            url: BASE_URL + 'sync/test_connection',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    let schoolInfo = '';
                    if (res.data && res.data.school) {
                        schoolInfo = `
                        <div class="mt-3 pt-3 border-top w-100">
                            <h6 class="text-dark font-weight-bold mb-1">${res.data.school}</h6>
                            <span class="badge badge-soft-primary px-3 py-1 rounded-pill mt-1">NPSN: ${res.data.npsn || '-'}</span>
                        </div>
                    `;
                    }

                    $('#connectionStatusBody').html(`
                    <div class="mb-3 position-relative d-inline-block">
                         <div class="position-absolute bg-success rounded-circle" style="width: 4rem; height: 4rem; opacity:0.2; top:0; left:0;"></div>
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem; position:relative; z-index:1;"></i>
                    </div>
                    <h5 class="text-success font-weight-bold mb-1">TERHUBUNG!</h5>
                    <small class="text-muted d-block mb-1">Response: ${res.server_time || 'OK'}</small>
                    ${schoolInfo}
                `);
                    $('#connectionStatusCard').removeClass('border-danger border-warning').addClass('border-success').css('border-top', '5px solid #28a745');
                } else {
                    $('#connectionStatusBody').html(`
                    <div class="mb-3"><i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i></div>
                    <h5 class="text-danger font-weight-bold">GAGAL TERHUBUNG</h5>
                    <p class="text-muted small px-3 mt-2 mb-0">${res.message}</p>
                `);
                    $('#connectionStatusCard').removeClass('border-success border-warning').addClass('border-danger').css('border-top', '5px solid #dc3545');
                }
            },
            error: function () {
                $('#connectionStatusBody').html(`
                <div class="mb-3"><i class="bi bi-wifi-off text-warning" style="font-size: 4rem;"></i></div>
                <h5 class="text-warning font-weight-bold">KONEKSI ERROR</h5>
                <p class="text-muted small px-3 mt-2 mb-0">Tidak dapat menjangkau server pusat.</p>
            `);
                $('#connectionStatusCard').removeClass('border-success border-danger').addClass('border-warning').css('border-top', '5px solid #ffc107');
            }
        });
    }

    function saveConfig() {
        let formData = $('#formConfig').serialize();
        $.ajax({
            url: BASE_URL + 'sync/save_config',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#007bff'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Gagal menyimpan konfigurasi', 'error');
            }
        });
    }
</script>