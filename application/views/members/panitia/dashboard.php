<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="background-color: #f5f7fa;">
    <!-- Welcome Header Section -->


    <section class="content" style="padding-top: 30px;">
        <div class="container-fluid">
            <!-- Statistik Utama 4 Kolom -->
            <div class="row mb-4">
                <!-- Total Siswa -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 rounded-15 shadow-sm card-stat hover-scale" style="transition: all 0.3s ease; overflow: hidden;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Siswa</p>
                                    <h2 class="mb-1 font-weight-bold" id="total-siswa" style="font-size: 2.2rem; color: #667eea;">0</h2>
                                    <small class="text-success font-weight-500"><i class="fas fa-arrow-up"></i> Siswa Aktif</small>
                                </div>
                                <div class="text-center p-3 rounded-circle" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-users text-dark" style="font-size: 28px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Rombel -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 rounded-15 shadow-sm card-stat hover-scale" style="transition: all 0.3s ease; overflow: hidden;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Rombel</p>
                                    <h2 class="mb-1 font-weight-bold" id="total-rombel" style="font-size: 2.2rem; color: #f5576c;">0</h2>
                                    <small class="text-danger font-weight-500"><i class="fas fa-layer-group"></i> Kelas Aktif</small>
                                </div>
                                <div class="text-center p-3 rounded-circle" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-school text-dark" style="font-size: 28px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Guru -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 rounded-15 shadow-sm card-stat hover-scale" style="transition: all 0.3s ease; overflow: hidden;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Guru</p>
                                    <h2 class="mb-1 font-weight-bold" id="total-guru" style="font-size: 2.2rem; color: #ffa502;">0</h2>
                                    <small class="text-warning font-weight-500"><i class="fas fa-chalkboard-user"></i> Pendidik</small>
                                </div>
                                <div class="text-center p-3 rounded-circle" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-chalkboard-user text-dark" style="font-size: 28px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jadwal Hari Ini -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 rounded-15 shadow-sm card-stat hover-scale" style="transition: all 0.3s ease; overflow: hidden;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">Jadwal Hari Ini</p>
                                    <h2 class="mb-1 font-weight-bold" id="jadwal-hari-ini" style="font-size: 2.2rem; color: #00bcd4;">0</h2>
                                    <small class="text-info font-weight-500"><i class="fas fa-calendar"></i> Ujian Berlangsung</small>
                                </div>
                                <div class="text-center p-3 rounded-circle" style="background: linear-gradient(135deg, #00bcd4 0%, #00acc1 100%); width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-calendar-alt text-dark" style="font-size: 28px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row mb-4">
                <!-- Jadwal Ujian Hari Ini (8 kolom) -->
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 rounded-15 shadow-sm">
                        <div class="card-header border-0 pt-4 pb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="text-dark font-weight-bold mb-0">
                                    <i class="fas fa-calendar-check"></i> Jadwal Ujian Hari Ini
                                </h5>
                                <span class="badge badge-light" style="font-size: 0.75rem; padding: 6px 12px;"><i class="fas fa-sync-alt"></i> Live</span>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            <div id="jadwal-ujian-container" class="table-responsive">
                                <table class="table table-sm table-borderless table-hover mb-0">
                                    <thead style="border-bottom: 2px solid #f0f0f0;">
                                        <tr>
                                            <th class="text-center" style="color: #667eea; font-weight: 600;">Jam</th>
                                            <th style="color: #667eea; font-weight: 600;">Bank Soal</th>
                                            <th class="text-center" style="color: #667eea; font-weight: 600;">Ruang</th>
                                            <th class="text-center" style="color: #667eea; font-weight: 600;">Peserta</th>
                                            <th class="text-center" style="color: #667eea; font-weight: 600;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="jadwal-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle"></i> Tidak ada jadwal ujian hari ini
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Kanan (4 kolom) -->
                <div class="col-lg-4 mb-4">
                    <!-- Token CBT -->
                    <div class="card border-0 rounded-15 shadow-sm mb-3">
                        <div class="card-header border-0 pt-4 pb-3" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); border-radius: 15px 15px 0 0;">
                            <h5 class="text-dark font-weight-bold mb-0">
                                <i class="fas fa-key"></i> Token CBT
                            </h5>
                        </div>
                        <div class="card-body text-center pt-4 pb-4">
                            <div class="display-4 font-weight-bold mb-3" id="token-display" style="letter-spacing: 3px; font-family: 'Courier New'; color: #f5576c;">
                                <?= isset($token->token) && $token->token ? $token->token : '------' ?>
                            </div>
                            <small class="text-muted d-block mb-3">Token berlaku untuk sistem penilaian</small>
                            <button class="btn btn-sm" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%); color: white; border: none; font-weight: 600;" onclick="refreshToken()">
                                <i class="fas fa-redo"></i> Refresh Token
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Pengumuman & Aktivitas -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 rounded-15 shadow-sm">
                        <div class="card-header border-0 pt-4 pb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="text-dark font-weight-bold mb-0">
                                    <i class="fas fa-bullhorn"></i> Pengumuman Terbaru
                                </h5>
                                <a href="<?= base_url('pengumuman') ?>" class="text-dark small" style="opacity: 0.9;">Lihat Semua →</a>
                            </div>
                        </div>
                        <div class="card-body pt-4" id="pengumuman-container">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card border-0 rounded-15 shadow-sm">
                        <div class="card-header border-0 pt-4 pb-3" style="background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%); border-radius: 15px 15px 0 0;">
                            <h5 class="text-dark font-weight-bold mb-0">
                                <i class="fas fa-history"></i> Aktivitas Terbaru
                            </h5>
                        </div>
                        <div class="card-body pt-4" id="aktivitas-container">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Rounded Cards */
.rounded-15 {
    border-radius: 15px !important;
    overflow: hidden;
}

/* Card Stats */
.card-stat {
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

.card-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1) !important;
}

.hover-scale {
    transform: translateY(0);
    transition: all 0.3s ease;
}

.hover-scale:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}

/* Card Header */
.card-header {
    background-attachment: fixed;
}

/* Task Items */
.task-item {
    transition: all 0.2s ease;
}

.task-item:hover {
    background-color: #f9f9f9;
    padding-left: 10px;
    border-radius: 5px;
}

/* Header Text */
.text-dark-80 {
    color: rgba(255, 255, 255, 0.8);
}

/* Table Enhancement */
.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f9f9f9;
}

/* Badge Styling */
.badge {
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 20px;
}

/* Pengumuman & Aktivitas Items */
.pengumuman-item, .aktivitas-item {
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
}

.pengumuman-item:hover, .aktivitas-item:hover {
    padding-left: 10px;
    background-color: #f9f9f9;
}

.pengumuman-item:last-child, .aktivitas-item:last-child {
    border-bottom: none;
}

/* Button Styling */
.btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .content-header {
        height: auto !important;
    }
    
    .card-stat {
        margin-bottom: 15px;
    }
}
</style>

<script>
const base_url = "<?= base_url() ?>";
let idpanitia = "<?= isset($panitia->id_panitia) ? $panitia->id_panitia : '0' ?>";

// Update jam dan tanggal
function updateTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('id-ID');
    const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    document.getElementById('current-time').textContent = timeStr;
    document.getElementById('current-date').textContent = dateStr;
}

// Global var for initial data
const initialInfoBox = <?= isset($info_box) ? json_encode($info_box) : '[]' ?>;
const initialUjianBox = <?= isset($ujian_box) ? json_encode($ujian_box) : '[]' ?>;

// Populate stat boxes
function populateStats(infoBox, ujianBox) {
    const stats = infoBox || [];
    const ujianStats = ujianBox || [];

    if (stats.length > 0) {
        stats.forEach(stat => {
            if (stat.title.includes('Siswa')) {
                const el = document.getElementById('total-siswa');
                if(el) el.textContent = stat.total;
            }
            if (stat.title.includes('Rombel')) {
                const el = document.getElementById('total-rombel');
                if(el) el.textContent = stat.total;
            }
            if (stat.title.includes('Guru')) {
                 const el = document.getElementById('total-guru');
                 if(el) el.textContent = stat.total;
            }
        });
    }

    if (ujianStats.length > 0) {
        ujianStats.forEach(stat => {
            if (stat.title.includes('Ruang')) {
                const el = document.getElementById('stat-ruang');
                if(el) el.textContent = stat.total;
            }
            if (stat.title.includes('Sesi')) {
                const el = document.getElementById('stat-sesi');
                if(el) el.textContent = stat.total;
            }
            if (stat.title.includes('Bank')) {
                const el = document.getElementById('stat-bank');
                if(el) el.textContent = stat.total;
            }
            if (stat.title.includes('Jadwal')) {
                const el = document.getElementById('stat-jadwal');
                if(el) el.textContent = stat.total;
            }
        });
    }
}

function loadRealtimeStats() {
    $.ajax({
        url: base_url + 'dashboard/get_realtime_stats',
        method: 'GET',
        dataType: 'JSON',
        success: function(response) {
            populateStats(response.info_box, response.ujian_box);
        }
    });
}

// Load jadwal ujian hari ini
function loadJadwalUjian() {
    $.ajax({
        url: base_url + 'dashboard/get_status_siswa',
        method: 'GET',
        dataType: 'JSON',
        success: function(response) {
            const jadwals = response.jadwals_ujian || [];
            const tbody = document.getElementById('jadwal-tbody');
            
            if (jadwals.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle"></i> Tidak ada jadwal ujian hari ini</td></tr>';
                document.getElementById('jadwal-hari-ini').textContent = '0';
                return;
            }

            document.getElementById('jadwal-hari-ini').textContent = jadwals.length;
            let html = '';

            jadwals.forEach((jadwal, idx) => {
                const mapel = jadwal.bank?.mapel?.nama_mapel || 'N/A';
                const bankKode = jadwal.bank?.bank_kode || 'N/A';
                html += `
                    <tr>
                        <td class="text-center"><small class="badge badge-info">${jadwal.jam_ke || '-'}</small></td>
                        <td>
                            <strong>${mapel}</strong>
                            <br><small class="text-muted">${bankKode}</small>
                        </td>
                        <td class="text-center">-</td>
                        <td class="text-center"><span class="badge badge-warning">-</span></td>
                        <td class="text-center"><span class="badge badge-secondary">Terjadwal</span></td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        },
        error: function() {
            document.getElementById('jadwal-tbody').innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-circle"></i> Gagal memuat data</td></tr>';
        }
    });
}

    // Load pengumuman
    function loadPengumuman() {
        $.ajax({
            url: base_url + 'dashboard/getPengumuman/2', // Fixed: loadPengumuman -> getPengumuman
            method: 'GET',
            dataType: 'JSON',
            success: function(response) {
                const container = document.getElementById('pengumuman-container');
                if (!response || response.length === 0) {
                    container.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.5;"></i><p class="mt-3">Tidak ada pengumuman</p></div>';
                    return;
                }

                let html = '';
                response.slice(0, 3).forEach((post, idx) => {
                    const tanggal = new Date(post.tanggal).toLocaleDateString('id-ID');
                    const teks = post.text.substring(0, 80);
                    html += `
                        <div class="pengumuman-item">
                            <div class="d-flex">
                                <div class="mr-3">
                                    <div class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border-radius: 50%;">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 font-weight-bold" style="color: #333;">${teks}${post.text.length > 80 ? '...' : ''}</h6>
                                    <small class="text-muted"><i class="fas fa-calendar-alt"></i> ${tanggal}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
            },
            error: function() {
                document.getElementById('pengumuman-container').innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-circle" style="font-size: 2rem; opacity: 0.5;"></i><p class="mt-3">Gagal memuat pengumuman</p></div>';
            }
        });
    }

    // Load aktivitas
    function loadAktivitas() {
        $.ajax({
            url: base_url + 'dashboard/getlog',
            method: 'GET',
            data: { page: 1, limit: 5 }, // Removed filter to show all activities
            dataType: 'JSON',
        success: function(response) {
            const container = document.getElementById('aktivitas-container');
            if (!response.logs || response.logs.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.5;"></i><p class="mt-3">Tidak ada aktivitas</p></div>';
                return;
            }

            let html = '';
            response.logs.slice(0, 5).forEach(log => {
                const tanggal = new Date(log.log_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const jam = new Date(log.log_time).toLocaleDateString('id-ID');
                html += `
                    <div class="aktivitas-item">
                        <div class="d-flex">
                            <div class="mr-2">
                                <i class="fas fa-circle" style="color: #00bcd4; font-size: 0.5rem; margin-top: 5px;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1" style="color: #555; font-size: 0.95rem;">${log.log_desc}</p>
                                <small class="text-muted"><i class="fas fa-clock"></i> ${tanggal}</small>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        },
        error: function() {
            document.getElementById('aktivitas-container').innerHTML = '<div class="text-center text-danger py-5"><i class="fas fa-exclamation-circle" style="font-size: 2rem; opacity: 0.5;"></i><p class="mt-3">Gagal memuat aktivitas</p></div>';
        }
    });
}


// Refresh token
function refreshToken() {
    $.ajax({
        url: base_url + 'dashboard/checkTokenJadwal',
        method: 'GET',
        dataType: 'JSON',
        success: function(response) {
            if (response.token && response.token.token) {
                document.getElementById('token-display').textContent = response.token.token;
                showSuccessToast('Token berhasil diperbarui');
            }
        },
        error: function() {
            showDangerToast('Gagal memperbarui token');
        }
    });
}

// Initialize
$(document).ready(function() {
    updateTime();
    setInterval(updateTime, 1000);
    
    populateStats(initialInfoBox, initialUjianBox);
    loadJadwalUjian();
    loadPengumuman();
    loadAktivitas();
    
    // Refresh data stats setiap 30 detik
    setInterval(loadRealtimeStats, 30 * 1000);

    // Refresh data setiap 5 menit
    setInterval(loadJadwalUjian, 5 * 60 * 1000);
    setInterval(loadPengumuman, 10 * 60 * 1000);
    setInterval(loadAktivitas, 10 * 60 * 1000);
});
</script>


</div>

<div class="modal fade" id="komentarModal" tabindex="-1" role="dialog" aria-labelledby="komentarLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="komentarLabel">Tulis Komentar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img class="img-fluid img-circle img-sm" src="<?= base_url('assets/img/panitia.png') ?>" alt="Alt Text">
                <div class="img-push">
                    <?= form_open('create', array('id' => 'komentar')) ?>
                    <input type="hidden" id="id-post" name="id_post" value="">
                    <div class="input-group">
                        <input type="text" name="text" placeholder="Tulis komentar ..."
                               class="form-control form-control-sm" required>
                        <span class="input-group-append">
                                <button type="submit" class="btn btn-success btn-sm">Komentari</button>
                            </span>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="balasanModal" tabindex="-1" role="dialog" aria-labelledby="balasanLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="balasanLabel">Tulis Balasan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img class="img-fluid img-circle img-sm" src="<?= base_url('assets/img/panitia.png') ?>" alt="Alt Text">
                <div class="img-push">
                    <?= form_open('create', array('id' => 'balasan')) ?>
                    <input type="hidden" id="id-comment" name="id_comment" value="">
                    <div class="input-group">
                        <input type="text" name="text" placeholder="Tulis balasan ...."
                               class="form-control form-control-sm" required>
                        <span class="input-group-append">
                                <button type="submit" class="btn btn-success btn-sm">Balas</button>
                            </span>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script src="<?=base_url()?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
<script src="<?= base_url() ?>/assets/app/js/rowspanizer.js"></script>
<script>
    let timerTokenView;
    var halaman = 0;
    var idpanitia = "<?=$panitia->id_panitia?>";

    function createTime(d) {
        var date = new Date(d);

        var jam = date.getHours();
        var menit = date.getMinutes();
        var sJam;
        var sMenit;

        if (jam < 10) sJam = '0' + jam;
        else sJam = '' + jam;

        if (menit < 10) sMenit = '0' + menit;
        else sMenit = '' + menit;

        var hari = daysdifference(d);
        var time;

        if (hari === 0) {
            time = sJam + ':' + sMenit;
        } else if (hari === 1) {
            time = 'kemarin ' + sJam + ':' + sMenit;
        } else {
            time = jQuery.timeago(d) + ', ' + sJam + ':' + sMenit;
        }
        return time;
    }

    function daysdifference(last) {
        var startDay = new Date(last);
        var endDay = new Date();

        var millisBetween = startDay.getTime() - endDay.getTime();
        var days = millisBetween / (1000 * 3600 * 24);

        return Math.round(Math.abs(days));
    }

    function addComments(id, comments, append) {
        var comm = '';
        $.each(comments, function (i, v) {
            var dari, foto, avatar;
            if (v.dari == '0') {
                dari = 'Admin';
                avatar = v.foto != null ? '<img class="img-circle border" src="' + v.foto + '" alt="Img" width="40px" height="40px">' :
                    '<div class="btn-circle-sm btn-primary media-left pt-1" style="width: 43px; height: 40px">A</div>'
            } else {
                if (v.dari_group == '2') {
                    dari = v.nama_panitia;
                    foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/panitia.png';
                    avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="40px" height="40px">';
                } else {
                    dari = v.nama_siswa;
                    foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                    avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="40px" height="40px">';
                }
            }

            comm += '<div class="media mt-1" id="parent-reply' + v.id_comment + '">'
                + avatar +
                '    <div class="w-100 ml-2">' +
                '        <div class="media-body border pl-3 bg-light" style="border-radius: 20px">' +
                '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                '            <div class="comment-text pb-1">' + v.text + '</div>' +
                '        </div>' +
                '        <div class="ml-2">' +
                '            <span class="btn-sm mr-2 text-muted">' + createTime(v.tanggal) + '</span>' +
                '            <span id="trigger-reply' + v.id_comment + '" class="btn btn-sm mr-2 text-muted action-collapse" data-toggle="collapse" aria-expanded="true"' +
                '                              aria-controls="collapse-reply' + v.id_comment + '"' +
                '                              href="#collapse-reply' + v.id_comment + '"><b>' + v.jml + ' balasan</b></span>' +
                '            <span class="btn btn-sm mr-2 text-muted btn-toggle-reply"' +
                '                  data-id="' + v.id_comment + '" data-toggle="modal" data-target="#balasanModal">' +
                '                <i class="fas fa-reply"></i> <b>Balas</b></span>';
            if (v.dari_group === '2' && v.dari === idpanitia) {
                comm += '            <span class="btn btn-sm text-muted text-red" data-id="' + v.id_comment + '">' +
                    '                <i class="fa fa-trash mr-1"></i> Hapus' +
                    '            </span>';
            }
            comm += '        </div>' +
                '<div id="collapse-reply' + v.id_comment + '" class="p-2 collapse toggle-reply" data-id="' + v.id_comment + '" data-parent="#parent-reply' + v.id_comment + '">';
            if (v.jml != '0') {
                comm += '<div id="konten-reply' + v.id_comment + '"></div>' +
                    '<div id="loadmore-reply' + v.id_comment + '" onclick="getReplies(' + v.id_comment + ')" class="text-center mb-3 loadmore-reply">' +
                    '       <div class="btn btn-default">Muat balasan lainnya ...</div>' +
                    '</div>';
            }
            comm += '    <div id="loading-reply' + v.id_comment + '" class="text-center d-none">' +
                '        <div class="spinner-border text-red"></div>' +
                '    </div>' +
                '</div>' +
                '    </div>' +
                '</div>';
        });

        if (append) {
            $(`#konten${id}`).append(comm);
        } else {
            $(`#konten${id}`).prepend(comm);
        }

        $('.toggle-reply').on('shown.bs.collapse', function (e) {
            var konten = $(this);
            var id = konten.data('id');
            var list = $(this).find('.media').length;
            if (list === 0) $(`#loadmore-reply${id}`).click();
        });
    }

    function addReplies(id, replies, append) {
        //console.log('replies', replies);
        var repl = '';
        $.each(replies, function (i, v) {
            var sudahAda = $(`.media${v.id_reply}`).length;
            if (!sudahAda) {
                var dari, foto, avatar;
                if (v.dari == '0') {
                    dari = 'Admin';
                    avatar = v.foto != null ? '<img class="img-circle border" src="' + v.foto + '" alt="Img" width="35px" height="35px">' :
                        '<div class="btn-circle-sm btn-primary media-left pt-1 mr-2" style="width: 37px">A</div>';
                } else {
                    if (v.dari_group == '2') {
                        dari = v.nama_panitia;
                        foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/siswa.png';
                        avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="35px" height="35px">';
                    } else {
                        dari = v.nama_siswa;
                        foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                        avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="35px" height="35px">';
                    }
                }

                repl +=
                    '<div class="media mt-1 media' + v.id_reply + '">'
                    + avatar +
                    '    <div class="w-100">' +
                    '        <div class="media-body border pl-3" style="border-radius: 17px; background-color: #dee2e6">' +
                    '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                    '            <div class="comment-text">' + v.text +
                    '            </div>' +
                    '        </div>' +
                    '        <div class="ml-2">' +
                    '            <small class="btn-sm mr-2 text-muted">' + createTime(v.tanggal) + '</small>';
                if (v.dari_group === '2' && v.dari === idpanitia) {
                    repl += '            <span class="btn btn-sm text-muted" data-id="' + v.id_reply + '">' +
                        '                <i class="fa fa-trash mr-1"></i> Hapus' +
                        '            </span>';
                }
                repl += '        </div>' +
                    '    </div>' +
                    '</div>';
            }
        });

        if (append) {
            $(`#konten-reply${id}`).append(repl);
        } else {
            $(`#konten-reply${id}`).prepend(repl);
        }
        //console.log('added', 'reply' + id);
    }

    function getComments(id) {
        $(`#loading${id}`).removeClass('d-none');
        $(`#loadmore${id}`).addClass('d-none');
        var $count = $(`#loadmore${id}`), page = $count.data('count');
        if (!page) page = 0;

        setTimeout(function () {
            $.ajax({
                url: base_url + "pengumuman/getcomment/" + id + "/" + page,
                type: "GET",
                success: function (response) {
                    //console.log('page', page);
                    //console.log("result", response);
                    page += 1;
                    currentPage = page;
                    $count.data('count', page);

                    if (response.length === 5) {
                        $(`#loadmore${id}`).removeClass('d-none');
                    }
                    $(`#loading${id}`).addClass('d-none');
                    addComments(id, response, true)
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            });
        }, 500);
    }

    function getReplies(id) {
        $(`#loading-reply${id}`).removeClass('d-none');
        $(`#loadmore-reply${id}`).addClass('d-none');
        var $count = $(`#loadmore-reply${id}`), page = $count.data('count');
        if (!page) page = 0;

        setTimeout(function () {
            $.ajax({
                url: base_url + "pengumuman/getreplies/" + id + "/" + page,
                type: "GET",
                success: function (response) {
                    //console.log('page', page);
                    //console.log("result", response);
                    page += 1;
                    currentPage = page;
                    $count.data('count', page);

                    //n >= start && n <= end
                    if (response.length === 5) {
                        $(`#loadmore-reply${id}`).removeClass('d-none');
                    }
                    $(`#loading-reply${id}`).addClass('d-none');
                    addReplies(id, response, true)
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            });
        }, 500);
    }

    function addPosts(response) {
        var card = '';

        if (response.length > 0) {
            $.each(response, function (i, v) {
                var dari, foto, avatar;
                if (v.dari == '0') {
                    dari = 'Admin';
                    avatar = v.foto != null ? '<img class="img-circle border" src="' + v.foto + '" alt="Img" width="50px" height="50px">' :
                        '<div class="btn-circle btn-primary media-left pt-1">A</div>';
                } else {
                    if (v.dari_group == '2') {
                        dari = v.nama_panitia;
                        foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/siswa.png';
                        avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="50px" height="50px">';
                    } else {
                        dari = v.nama_siswa;
                        foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                        avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="50px" height="50px">';
                    }
                }

                card += '<div class="card">' +
                    '    <div class="card-body" id="parent' + v.id_post + '">' +
                    '        <div class="media">' +
                    avatar +
                    '                <div class="media-body ml-3">' +
                    '                    <span class="font-weight-bold"><b>' + dari + '</b></span>' +
                    '                    <br/>' +
                    '                    <span class="text-gray">' + createTime(v.tanggal) + '</span>' +
                    '                </div>' +
                    '        </div>' +
                    '        <div class="mt-2">' + v.text + '</div>' +
                    '        <div class="text-muted">' +
                    '            <button type="button" class="btn btn-primary btn-sm mr-2 btn-toggle"' +
                    '                    data-id="' + v.id_post + '" data-toggle="modal"' +
                    '                    data-target="#komentarModal"><i class="fas fa-reply mr-1"></i> Tulis komentar' +
                    '            </button>' +
                    '            <button type="button" id="trigger' + v.id_post + '" class="btn btn-success btn-sm mr-2 action-collapse"' +
                    '                    data-toggle="collapse" aria-expanded="true"' +
                    '                    aria-controls="collapse-' + v.id_post + '"' +
                    '                    href="#collapse-' + v.id_post + '">' +
                    '                <i class="fa fa-commenting-o mr-1"></i>' + v.jml + ' komentar' +
                    '            </button>';
                if (v.dari_group === '2' && v.dari === idpanitia) {
                    card += '            <button type="button" class="btn btn-danger btn-sm" data-id="' + v.id_post + '">' +
                        '                <i class="fa fa-trash mr-1"></i> Hapus' +
                        '            </button>';
                }
                card += '        </div>' +
                    '    </div>' +
                    '    <div id="collapse-' + v.id_post + '" class="p-2 collapse toggle-comment"' +
                    '         data-id="' + v.id_post + '" data-parent="#parent' + v.id_post + '">' +
                    '        <hr class="m-0">' +
                    '        <div id="konten' + v.id_post + '" class="p-4">' +
                    '        </div>' +
                    '        <div id="loading' + v.id_post + '" class="text-center d-none">' +
                    '            <div class="spinner-border text-red"></div>' +
                    '        </div>';
                if (v.jml == '0') {
                    card += '<div class="text-center">Tidak ada komentar</div>';
                } else {
                    card += '<div id="loadmore' + v.id_post + '"' +
                        '     onclick="getComments(' + v.id_post + ')"' +
                        '     class="text-center mt-4 loadmore">' +
                        '    <div class="btn btn-default">Muat komentar lainnya ...</div>' +
                        '</div>';
                }
                card += '</div>' +
                    '</div>';
            })
        } else {
            card = '<div class="card card-default">' +
                '<div class="card-body text-center">' +
                ' <p>Tidak ada pengumuman</p>' +
                '</div></div>';
        }

        $('#pengumuman').html(card);

        $('.toggle-comment').on('shown.bs.collapse', function (e) {
            var konten = $(this);
            var id = konten.data('id');
            var list = $(this).find('.media').length;
            if (list === 0) $(`#loadmore${id}`).click();
        });

        $('#komentarModal').on('show.bs.modal', function (e) {
            var id = $(e.relatedTarget).data('id');
            $("#id-post").val(id);

            var isVisible = $(`#collapse-${id}`).hasClass('show');
            if (!isVisible) {
                $(`#trigger${id}`).click();
            }
        });

        $('#balasanModal').on('show.bs.modal', function (e) {
            var id = $(e.relatedTarget).data('id');
            $("#id-comment").val(id);

            var isVisible = $(`#collapse-reply${id}`).hasClass('show');
            if (!isVisible) {
                $(`#trigger-reply${id}`).click();
            }
        });

        $('#komentar').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            //console.log("data", $(this).serialize());
            var id = $(this).find('input[name=id_post]').val();

            $.ajax({
                url: base_url + "pengumuman/savekomentar",
                data: $(this).serialize(),
                method: 'POST',
                dataType: "JSON",
                success: function (response) {
                    //console.log("result", response);
                    $('#komentarModal').modal('hide').data('bs.modal', null);
                    $('#komentarModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    addComments(id, response, false)
                    //window.location.href = base_url + 'pengumuman';
                },
                error: function (xhr, status, error) {
                    $('#komentarModal').modal('hide').data('bs.modal', null);
                    $('#komentarModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    showDangerToast('Error, komentar tidak terkirim');
                }
            });
        });

        $('#balasan').on('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            //console.log("data", $(this).serialize());
            var id = $(this).find('input[name=id_comment]').val();

            $.ajax({
                url: base_url + "pengumuman/savebalasan",
                data: $(this).serialize(),
                method: 'POST',
                dataType: "JSON",
                success: function (response) {
                    //console.log("result", response);
                    $('#balasanModal').modal('hide').data('bs.modal', null);
                    $('#balasanModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    //window.location.href = base_url + 'pengumuman';
                    addReplies(id, response, false)
                },
                error: function (xhr, status, error) {
                    $('#balasanModal').modal('hide').data('bs.modal', null);
                    $('#balasanModal').on('hidden', function () {
                        $(this).data('modal', null);
                    });
                    showDangerToast('Error, balasan tidak terkirim');
                }
            });
        });

    }

    function getPosts() {
        $(`#loading-post`).removeClass('d-none');
        $(`#loadmore-post`).addClass('d-none');

        setTimeout(function () {
            $.ajax({
                url: base_url + "pengumuman/getpost/" + halaman,
                type: "GET",
                success: function (response) {
                    //console.log("result", response);
                    halaman += 1;

                    if (response.length === 5) {
                        $(`#loadmore-post`).removeClass('d-none');
                    }
                    $(`#loading-post`).addClass('d-none');
                    addPosts(response)
                }, error: function (xhr, status, error) {
                    console.log("error", xhr.responseText);
                }
            });
        }, 500);
    }

    function getGlobalToken() {
        if (globalToken != null) {
            const viewToken = $('#token-view');
            if (viewToken.length) viewToken.text(globalToken.token);
            if (globalToken.auto == '1' && adaJadwalUjian > 0) {
                $('#refresh-token').removeClass('d-none')
            }
        }
    }

    $(document).ready(function () {
        var colorBg = ['success', 'success', 'secondary', 'primary', 'warning', 'danger',
            'primary', "warning", "primary", "success", "primary", "success", "warning"
        ];

        function load_log() {
            $.ajax({
                url: base_url + "dashboard/getlog",
                data: {page: 1, limit: 10, filter: ['2','3']},
                method: "GET",
                success: function (data) {
                    //console.log(data);
                    if (data.logs.length > 0) {
                        var ul = '<ul class="products-list product-list-in-card pl-2 pr-2">';
                        $.each(data.logs, function (key, value) {
                            const nama_lengkap = value?.first_name === value?.last_name ? value?.first_name : value?.first_name +' '+ value?.last_name
                            var nama = value.id_group === '1' ? value.first_name : nama_lengkap;
                            var clr = colorBg[value.log_type];
                            var tgl = formatTanggal(value.log_time);//new Date('02/12/2018');
                            const panitia = data.panitia.find(g => g.id === value.id_user);
                            const siswa = data.siswa.find(s => s.id === value.id_user);
                            const admin = data.admins.find(a => a.id === value.id_user);
                            const fotoUser = panitia ? panitia.foto :
                                siswa ? siswa.foto :
                                    admin ? admin.foto : null;
                            const foto = base_url + (fotoUser ? fotoUser : '');
                            const initial = nama.charAt(0).toUpperCase();
                            ul += '  <li class="item">' +
                                '    <div class="media" style="line-height: 1.3">' +
                                '      <div data-siswa="'+initial+'" class="d-flex justify-content-center align-items-center bg-'+clr+' rounded-circle" style="width: 50px; height: 50px">' +
                                '        <img class="avatar-log img-circle" src="'+foto+'" width="44" height="44"  alt=""/></div>' +
                                //'<button class="btn btn-circle-sm btn-'+clr+' media-left">' + nama.charAt(0).toUpperCase()+'</button>' +
                                '      <div class="media-body ml-2">' +
                                '        <span>'+nama+'</span>' +
                                '        <br />' +
                                '        <span class="text-'+clr+' text-sm">'+value.log_desc+'</span>' +
                                '        <br />' +
                                '        <span class="text-xs text-muted">'+tgl+'</span>' +
                                '      </div>' +
                                '    </div>' +
                                '  </li>';

                        });
                        ul += '</ul>';
                        $('#log-list').html(ul);

                        $(".avatar-log").each(async function (i, v) {
                            const loaded = () => {
                                return new Promise((resolve) => {
                                    let obj = { pos: i };

                                    $(v)
                                        .on("error", function () {
                                            obj.error = true;
                                            const div = $(this).parent();
                                            const initial = div.data('siswa')
                                            $(this).remove()
                                            div.html(`<span class="text-lg">${initial}</span>`);
                                            resolve(obj);
                                        })
                                        .on("load", function () {
                                            obj.error = false;

                                            // ==== Resize gambar dengan rasio asli ====
                                            const canvas = document.createElement("canvas");
                                            const ctx = canvas.getContext("2d");

                                            const targetW = 100; // lebar tetap
                                            const ratio = v.naturalHeight / v.naturalWidth;
                                            const targetH = Math.round(targetW * ratio);

                                            canvas.width = targetW;
                                            canvas.height = targetH;

                                            ctx.drawImage(v, 0, 0, targetW, targetH);

                                            // Convert ke PNG
                                            v.src = canvas.toDataURL("image/png");

                                            resolve(obj);
                                        });
                                });
                            };

                            await loaded();
                        });
                    }
                }
            });
        }

        load_log();

        getPosts();


        getToken(function (result) {
            getGlobalToken();
        });


        $('#refresh-token').click(function () {
            getToken(function (result) {
                getGlobalToken();
            });
        });

        setTimeout(function () {
            ajaxGet(base_url + 'dashboard/get_status_siswa', function(error, response) {
                if (error) {
                    console.log('error', error.message);
                } else {
                    createTableFromJSON(response);
                }
            })
        }, 500)

    });

    async function createTableFromJSON(data) {
        const tableContainer = document.getElementById('table-container');
        const allSesi = data.ruangs || [];
        const jsonData = data.jadwals_ujian || [];

        // 1. Validate and handle empty data
        if (!validateData(allSesi, jsonData, tableContainer)) {
            return;
        }

        // 2. Process and group the data
        const groupedSchedules = processAndGroupData(data);

        // 3. Sort the final list for correct rowspans
        groupedSchedules.sort((a, b) => {
            if (a.ruang_sesi.ruang_id !== b.ruang_sesi.ruang_id) return a.ruang_sesi.ruang_id - b.ruang_sesi.ruang_id;
            if (a.ruang_sesi.sesi_id !== b.ruang_sesi.sesi_id) return a.ruang_sesi.sesi_id - b.ruang_sesi.sesi_id;
            return a.jam_ke - b.jam_ke;
        });

        // 4. Calculate rowspans for 'ruang' and 'sesi' columns
        const rowCounts = calculateRowCounts(groupedSchedules, 'ruang_sesi.ruang_id');
        const sesiRowCounts = calculateRowCounts(groupedSchedules, 'ruang_sesi.ruang_id', 'ruang_sesi.sesi_id');

        // 5. Build and render the HTML table
        const tableHtml = buildTable(groupedSchedules, data, rowCounts, sesiRowCounts);
        tableContainer.innerHTML = tableHtml;
    }

    function validateData(allSesi, jsonData, container) {
        if (allSesi.length === 0 || jsonData.length === 0) {
            container.innerHTML = `<table class="w-100 table-bordered"><tr><td class="alert alert-default-warning text-center">Tidak ada jadwal penilaian hari ini</td></tr></table>`;
            return false;
        }
        return true;
    }

    function processAndGroupData(data) {
        const allSesi = data.ruangs || [];
        const jsonData = data.jadwals_ujian || [];
        const grouped = {};

        jsonData.forEach(jadwal => {
            jadwal.bank.bank_kelas = jadwal.bank.bank_kelas.map(String)
            const ruangFiltered = allSesi?.filter(r =>
                r.kelas_id.some(id => jadwal.bank.bank_kelas.includes(id?.toString()))
            );

            ruangFiltered.forEach(ruang => {
                // siswa dalam sesi/ruang ini
                const siswaRuang = data.sesisiswa.filter(s =>
                    s.ruang_id === ruang.ruang_id &&
                    s.sesi_id === ruang.sesi_id &&
                    jadwal.bank.bank_kelas.includes(s.kelas_id)
                );
                const jumlahSiswa = siswaRuang.length;

                let belumLogin = 0, sedangLogin = 0, selesai = 0;
                siswaRuang.forEach(s => {
                    // cari cbtsiswa untuk siswa ini
                    const cbt = data.cbtsiswa.find(c =>
                        c.id_siswa === s.siswa_id &&
                        c.jadwal.id_jadwal === jadwal.id_jadwal
                    );

                    if (!cbt || cbt.mulai === null) {
                        belumLogin++;
                    } else if (cbt.selesai === null) {
                        sedangLogin++;
                    } else {
                        selesai++;
                    }
                });
                const pengawasJadwal = data.pengawas[jadwal.id_jadwal]?.[ruang.ruang_id]?.[ruang.sesi_id] || {};
                const key = `${ruang.ruang_id}-${ruang.sesi_id}-${pengawasJadwal.jam_ke || '1'}`;
                if (!grouped[key]) {
                    grouped[key] = {
                        key: key,
                        ruang_sesi: ruang,
                        jam_ke: pengawasJadwal.jam_ke || '1',
                        jadwal: [],
                        pengawas: [],
                        peserta: 0,
                        blm_login: 0,
                        sdg_login: 0,
                        selesai: 0
                    };
                }
                grouped[key].jadwal.push(jadwal);
                const pengawasList = (pengawasJadwal.id_panitia || '').split(',').filter(Boolean);
                //if (!grouped[key].jam_ke.includes(pengawasJadwal.jam_ke)) {
                //    grouped[key].jam_ke.push(pengawasJadwal.jam_ke)
                //}
                for (const string of pengawasList) {
                    if (!grouped[key].pengawas.includes(data.panitias[string] || '')) {
                        grouped[key].pengawas.push(data.panitias[string] || '');
                    }
                }
                grouped[key].peserta += jumlahSiswa;
                grouped[key].blm_login += belumLogin;
                grouped[key].sdg_login += sedangLogin;
                grouped[key].selesai += selesai;
            });
        });

        return Object.values(grouped);
    }

    function calculateRowCounts(items, ...keys) {
        const counts = new Map();
        let prevValues = null;
        let currentCount = 0;

        // Use a deep equality check for dynamic keys
        const getValues = (item) => keys.map(key => {
            return key.split('.').reduce((obj, prop) => obj?.[prop], item);
        });

        [...items, null].forEach(item => {
            const currentValues = item ? getValues(item) : null;

            const isSameGroup = prevValues && currentValues &&
                prevValues.every((val, i) => val === currentValues[i]);

            if (!isSameGroup) {
                if (prevValues !== null) {
                    const key = prevValues.join('-');
                    counts.set(key, currentCount);
                }
                currentCount = 0;
            }

            if (item !== null) {
                currentCount++;
            }
            prevValues = currentValues;
        });

        return counts;
    }

    function buildTable(groupedSchedules, data, rowCounts, sesiRowCounts) {
        const headerHtml = `
        <thead>
            <tr class="bg-light">
                <th class="text-center align-middle alert-default-primary" rowspan="2">RUANG</th>
                <th class="text-center align-middle alert-default-warning" rowspan="2">SESI</th>
                <th class="text-center align-middle alert-default-secondary" rowspan="2">JAM KE</th>
                <th class="text-center align-middle alert-default-success" rowspan="2">MAPEL<br>(BANK SOAL)</th>
                <th class="text-center align-middle alert-default-primary" rowspan="2">KELAS</th>
                <th class="text-center align-middle alert-default-danger" rowspan="2">PENGAWAS</th>
                <th class="text-center align-middle alert-default-warning" rowspan="2">JML SISWA</th>
                <th class="text-center align-middle alert-default-primary" colspan="3">STATUS SISWA</th>
            </tr>
            <tr class="bg-light">
                <th class="text-center align-middle alert-default-danger">BLM<br/>LOGIN</th>
                <th class="text-center align-middle alert-default-info">SDG<br/>LOGIN</th>
                <th class="text-center align-middle alert-default-success">SELESAI</th></tr></thead>
            </tr>
        </thead>`;

        let bodyHtml = '<tbody>';
        const usedRuangIds = new Set();
        const usedSesiKeys = new Set();
        let currentRow = 0;
        let curSesi = 0;
        let bgRow = '';
        let bold = false;

        groupedSchedules.forEach(group => {
            const { ruang_sesi, jam_ke, jadwal, pengawas, peserta, blm_login, sdg_login, selesai } = group;
            let infoMapel = ``;
            for (const jad of jadwal) {
                infoMapel += `${jad.bank.mapel.kode} (<span class="badge badge-light">${jad.bank.bank_kode}</span>)<br/>`;
            }
            const bank_kelas = getUniqueClassNames(ruang_sesi, jadwal).join(', ');
            //const bank_kode = jadwal.map(j => `<span class="badge badge-light">${j.bank.bank_kode}</span>`).join('<br>');
            const idspanitia = jadwal.map(j => j.bank.bank_panitia_id).join(',');

            bodyHtml += `<tr>`;
            // Ruang column with rowspan
            const ruangId = ruang_sesi.ruang_id;
            if (!usedRuangIds.has(ruangId)) {
                const rowspanValue = rowCounts.get(String(ruangId)) || 1;
                bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle border-bottom-bold">${ruang_sesi.nama_ruang}</td>`;
                usedRuangIds.add(ruangId);
                bold = true
            } else {
                bold = false
            }

            var genap = curSesi % 2 === 0;
            const bBold = bold ? 'border-top-bold' : '';
            // Sesi column with rowspan
            const sesiKey = `${ruang_sesi.ruang_id}-${ruang_sesi.sesi_id}`;
            if (!usedSesiKeys.has(sesiKey)) {
                bgRow = genap ? 'bg-gray-light' : '';
                const rowspanValue = sesiRowCounts.get(sesiKey) || 1;
                bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-sesi ${bgRow} ${bBold}">${ruang_sesi.nama_sesi}</td>`;
                usedSesiKeys.add(sesiKey);
                curSesi++;
            }

            bodyHtml += `
            <td class="text-center align-middle td-jam ${bgRow} ${bBold}">${jam_ke}</td>
            <td class="align-middle td-mapel jadwal ${bgRow} ${bBold}">${infoMapel}</td>
            <td class="text-center align-middle ${bgRow} ${bBold}">${bank_kelas}</td>
            <td class="text-center align-middle td-pengawas ${bgRow} ${bBold}" data-panitia="${idspanitia}">${pengawas.join('<br>')}</td>
            <td class="text-center align-middle text-bold ${bgRow} ${bBold}">${peserta}</td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="text-bold text-danger">${blm_login}</span></td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="text-bold text-info">${sdg_login}</span></td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="text-bold text-success">${selesai}</span></td>
        </tr>`;

            currentRow++;
        });

        bodyHtml += '</tbody>';
        return `<table class="table table-bordered table-sm w-100">${headerHtml}${bodyHtml}</table>`;
    }

    function getUniqueClassNames(ruang_sesi, jadwal) {
        const banks_kelas = [];
        const classIds = ruang_sesi.kelas_id;

        for (const j of jadwal) {
            for (const klsId of classIds) {
                if (j.bank?.bank_kelas?.includes(klsId)) {
                    banks_kelas.push(ruang_sesi.nama_kelas[klsId] || '');
                }
            }
        }

        return [...new Set(banks_kelas)];
    }

    function formatTanggal(dateString) {
        const date = new Date(dateString);
        const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }

</script>
