<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<?php

?>

<div class="container-fluid py-4 container-p-x main-content-overlap pb-5 mb-5 pb-lg-4 mb-lg-0">

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Wrapper Card -->
            <div class="card card-modern border-0">
                <div class="card-body p-4 p-md-5">

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mb-4 gap-2">
                        <a href="<?= base_url('dashboard') ?>"
                            class="btn btn-light border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                            <i class="bi bi-arrow-left me-2 text-primary"></i> Kembali
                        </a>
                        <div class="d-flex gap-2">
                            <button onclick="window.location.reload()"
                                class="btn btn-light rounded-circle shadow-sm border hover-scale icon-box-sm d-flex align-items-center justify-content-center"
                                style="width: 42px; height: 42px;" title="Reload Data">
                                <i class="bi bi-arrow-clockwise fs-5"></i>
                            </button>
                            <button type="button" id="btnShowToken"
                                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm hover-scale d-none"
                                style="background: linear-gradient(135deg, #1877F2 0%, #0044cc 100%); border: none;">
                                <i class="bi bi-key-fill me-2"></i> Token
                            </button>
                        </div>
                    </div>



                    <!-- Section Title -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary"
                            style="width: 5px; height: 24px; border-radius: 4px; margin-right: 12px;"></div>
                        <h4 class="fw-bold m-0 text-dark">Daftar Ujian Hari Ini</h4>
                    </div>



                    <!-- Main Schedule Grid -->
                    <div class="mb-4">
                        <div class="row g-4" id="jadwal-content">
                            <!-- Injected via JS -->
                            <div class="col-12 text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Memuat jadwal...</p>
                            </div>
                        </div>
                    </div>

                    <!-- History Section -->
                    <div class="pt-4 border-top">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-box bg-danger bg-opacity-10 text-danger me-3 rounded-4 border border-danger border-opacity-10 d-flex justify-content-center align-items-center"
                                style="width: 48px; height: 48px;">
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0 text-dark">Riwayat Ujian & Susulan</h5>
                        </div>

                        <div id="history-data-container">
                            <!-- Desktop Table View -->
                            <div class="table-responsive rounded-4 border d-none d-lg-block">
                                <table class="table table-hover align-middle custom-table mb-0" id="table-desktop-history">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr class="text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                                            <th class="py-3 px-4 fw-bold rounded-start-3" style="width: 120px;">Waktu</th>
                                            <th class="py-3 px-4 fw-bold">Mata Pelajaran / Detail</th>
                                            <th class="py-3 px-4 fw-bold text-end rounded-end-3" style="width: 180px;">Status / Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        <!-- Rendered via JS -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Mobile/Tablet Card View -->
                            <div class="d-block d-lg-none">
                                <div class="row g-3" id="cards-mobile-history">
                                    <!-- Rendered via JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- End Wrapper Card Body -->
            </div> <!-- End Wrapper Card -->
        </div>
    </div>

</div>

<!-- Token Detail Modal (per-jadwal) -->
<div class="modal fade" id="token-detail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-0">
                <div class="p-3 text-center text-white rounded-top-4 position-relative overflow-hidden"
                    style="background: linear-gradient(135deg, #1877F2 0%, #0044cc 100%);">
                    <h3 class="fw-bold mb-1" id="token-detail-title">Token Ujian</h3>
                    <p class="opacity-75 mb-0" id="token-detail-subtitle">Gunakan token ini untuk masuk ke ujian</p>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="p-3 text-center bg-white rounded-bottom-4">
                    <div class="p-2 mb-2 bg-light rounded-3 border border-2 border-dashed border-secondary position-relative">
                        <div id="token-view" class="display-4 fw-bold font-monospace text-dark ls-sm">---</div>
                    </div>
                    <button onclick="copyTokenFromModal()"
                        class="btn btn-sm text-white w-100 py-2 rounded-pill fw-bold shadow-sm hover-scale"
                        style="background: linear-gradient(135deg, #1877F2 0%, #0044cc 100%); border: none;">
                        <i class="bi bi-clipboard me-2"></i> Salin Token
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ MOBILE BOTTOM NAV ══ -->
<nav class="position-fixed bottom-0 start-0 w-100 d-flex d-lg-none bg-white border-top shadow-lg" style="padding-bottom: env(safe-area-inset-bottom); z-index: 1040;">
    <div class="d-flex justify-content-around align-items-center w-100 py-2 px-1">
        <a href="<?= base_url('dashboard') ?>" id="bn-home" class="text-decoration-none d-flex flex-column align-items-center text-primary bg-primary bg-opacity-10 py-1 px-3 rounded-pill transition-all" style="min-width: 60px;">
            <i class="bi bi-house-door-fill fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Home</small>
        </a>

        <button onclick="logout()" class="btn btn-link text-decoration-none d-flex flex-column align-items-center text-secondary py-1 px-3 rounded-pill transition-all" style="min-width: 60px; box-shadow: none;">
            <i class="bi bi-box-arrow-right fs-5 mb-1"></i>
            <small class="fw-bold text-uppercase" style="font-size: 9px; letter-spacing: 0.5px;">Keluar</small>
        </button>
    </div>
</nav>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .custom-table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }
    .custom-table tbody td {
        border-bottom: 1px dashed #e2e8f0;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
        font-size: 0.95rem;
        background-color: transparent;
        transition: background-color 0.2s ease;
    }
    .custom-table tbody tr:hover td {
        background-color: rgba(248, 250, 252, 0.8);
    }
    .custom-table tbody tr:last-child td {
        border-bottom: none;
    }
    .hover-scale { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .hover-scale:hover { transform: translateY(-2px); }
    .hover-scale-sm { transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease; }
    .hover-scale-sm:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    @keyframes fadeInOut {
        0% { opacity:0; transform:translateX(-50%) translateY(-10px); }
        15% { opacity:1; transform:translateX(-50%) translateY(0); }
        85% { opacity:1; transform:translateX(-50%) translateY(0); }
        100% { opacity:0; transform:translateX(-50%) translateY(-10px); }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- CBT Helper Functions -->
<script>
    const tknSiswa = <?= (int) ($setting->tkn_siswa ?? 0) ?>; // 1 = token per mapel ditampilkan ke siswa

    // Helper function for AJAX GET requests
    async function ajaxGet(url) {
        try {
            const response = await fetch(base_url + url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (e) {
            console.error('AJAX Error:', e);
            throw e;
        }
    }

    // Store jadwal data globally for modal access
    let allJadwalData = [];

    $(document).ready(async function () {
        try {
            const data = await ajaxGet('cbtsiswa/get_cbt_siswa?id_siswa=' + <?= $siswa->id_siswa ?? 0 ?>);
            // Flatten all jadwal from today and before
            allJadwalData = [
                ...(data.jadwal_today ? Object.values(data.jadwal_today).flat() : []),
                ...(data.jadwal_before ? Object.values(data.jadwal_before).flat() : [])
            ];
            await populateDataSiswa(data);
        } catch (error) {
            console.error('Error loading CBT data:', error);
            $('#jadwal-content').html(`
                <div class="col-12 py-5">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error loading data: ${error.message}
                    </div>
                </div>
            `);
        }
    })

    async function populateDataSiswa(data) {
        const siswa = data.siswa;
        const hasAnyJadwal = (Object.keys(data.jadwal_today || {}).length > 0) || (Object.keys(data.jadwal_before || {}).length > 0);

        if (!hasAnyJadwal) {
            $('#jadwal-content').html(`
                <div class="col-12 py-5">
                    <div class="text-center p-5 rounded-4 border border-dashed text-muted" style="background: rgba(255,255,255,0.6);">
                        <div class="mb-3 display-1 opacity-25"><i class="bi bi-calendar-x"></i></div>
                        <h5 class="fw-bold text-secondary">Tidak Ada Ujian Aktif</h5>
                        <p class="text-muted">Jadwal ujian untuk hari ini belum tersedia.</p>
                    </div>
                </div>
            `);
        } else {
            $('#jadwal-content').html('');
            await populateJadwalHariIni(data.jadwal_today, siswa, data.today);
            await populateJadwalSebelumnya(data.jadwal_before, siswa, data.today, data.jadwal_today);
        }
    }



    async function populateJadwalHariIni(jadwals, siswa, skrg) {
        const container = document.getElementById('jadwal-content')
        if (Object.keys(jadwals).length > 0) {
            let html = '';
            let delay = 0.2;

            // Safe date parsing helper for YYYY-MM-DD HH:mm:ss
            const parseDate = (dateStr) => {
                if (!dateStr) return new Date();
                return new Date(dateStr.replace(/-/g, "/")); // Replacing - with / makes it safer for some browsers
            };

            const nowTime = parseDate(skrg);

            for (const tanggal in jadwals) {
                jadwals[tanggal].sort(sortByJam)

                // Pre-process: build lookup of all banks with normalized jam_ke for sequential checking
                const banksWithJamKe = jadwals[tanggal].map(b => ({
                    bank: b,
                    jamKe: Number(b.jadwal.jam_ke === '0' ? '1' : b.jadwal.jam_ke),
                    idJadwal: b.jadwal.id_jadwal
                }));



                for (const bank of jadwals[tanggal]) {
                    const jadwal = bank.jadwal;
                    const jamKe = jadwal.jam_ke === '0' ? '1' : jadwal.jam_ke;
                    const jamKeNum = Number(jamKe);
                    const cbtSiswa = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === jadwal.id_jadwal)

                    // Parse Times
                    const jadwalStart = parseDate(jadwal.tgl_mulai);
                    const jadwalEnd = parseDate(jadwal.tgl_selesai);

                    const timeToMinutes = (timeStr) => {
                        if (!timeStr) return 0;
                        const [h, m] = timeStr.split(':').map(Number);
                        return h * 60 + m;
                    };

                    const nowMinutes = timeToMinutes(skrg.split(' ')[1]);
                    let sesiMulai = 0, sesiSampai = 0;
                    if (siswa.sesisiswa && siswa.sesisiswa.sesi) {
                        sesiMulai = timeToMinutes(siswa.sesisiswa.sesi.waktu_mulai);
                        sesiSampai = timeToMinutes(siswa.sesisiswa.sesi.waktu_akhir);
                    }

                    // Sequential check: ALL exams with smaller jam_ke must be completed
                    // If all jam_ke are the same (e.g. all '1'), all exams can be done simultaneously
                    const prevJamBanks = banksWithJamKe.filter(b => b.jamKe < jamKeNum);
                    let selesaiSebelumnya = true;
                    if (prevJamBanks.length > 0) {
                        // Check if ALL exams with smaller jam_ke are completed
                        selesaiSebelumnya = prevJamBanks.every(prev => {
                            const prevCbt = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === prev.idJadwal);
                            return prevCbt && prevCbt.selesai != null;
                        });
                    }

                    // Calculate Total Soal
                    // Prioritas: 1. Setting Tampil (Sum), 2. Real Count (DB), 3. Manual Input

                    let totalSetting = parseInt(bank.tampil_pg || 0) +
                        parseInt(bank.tampil_kompleks || 0) +
                        parseInt(bank.tampil_jodohkan || 0) +
                        parseInt(bank.tampil_isian || 0) +
                        parseInt(bank.tampil_esai || 0) +
                        parseInt(bank.tampil_urutan || 0) +
                        parseInt(bank.tampil_cloze || 0);

                    let totalSoal = totalSetting;

                    if (totalSoal === 0) {
                        totalSoal = parseInt(bank.total_soal_real || 0);
                    }

                    if (totalSoal === 0) {
                        totalSoal = parseInt(bank.jml_soal || 0);
                    }

                    if (totalSoal === 0) totalSoal = '-';

                    let selesai, lanjutkan, resetWaktu;
                    if (cbtSiswa) {
                        selesai = cbtSiswa.selesai !== null;
                        resetWaktu = String(cbtSiswa.reset_waktu ?? "0");
                        lanjutkan = (cbtSiswa.mulai !== null && cbtSiswa.mulai.indexOf('0000') === -1) || resetWaktu !== "0";
                    } else {
                        selesai = false;
                        lanjutkan = false;
                        resetWaktu = "0";
                    }

                    // Theme Logic
                    let cardHeaderClass = 'bg-primary';
                    let btnText = 'MULAI KERJAKAN >';
                    let btnClass = 'btn-primary';
                    let disabled = false;
                    const tokenUrl = jadwal.token_url ?? jadwal.id_jadwal;
                    let action = `location.href='${base_url}cbtsiswa/konfirmasi/${tokenUrl}'`;
                    let statusLabel = 'TERSEDIA';

                    if (!lanjutkan && !selesai) {
                        if (nowTime < jadwalStart) {
                            cardHeaderClass = 'bg-secondary';
                            btnText = '<i class="bi bi-calendar-event me-2"></i>BELUM DIMULAI';
                            btnClass = 'btn-gradient-secondary opacity-75';
                            disabled = true;
                        } else if (nowTime > jadwalEnd) {
                            cardHeaderClass = 'bg-danger';
                            btnText = '<i class="bi bi-x-circle me-2"></i>WAKTU HABIS';
                            btnClass = 'btn-gradient-danger';
                            disabled = true;
                        } else {
                            // Check Sesi Limits
                            if (nowMinutes < sesiMulai) {
                                cardHeaderClass = 'bg-secondary';
                                btnText = '<i class="bi bi-clock me-2"></i>MENUNGGU SESI';
                                btnClass = 'btn-gradient-secondary opacity-75';
                                disabled = true;
                            } else if (nowMinutes > sesiSampai) {
                                cardHeaderClass = 'bg-danger';
                                btnText = '<i class="bi bi-door-closed me-2"></i>UJIAN DITUTUP';
                                btnClass = 'btn-gradient-danger';
                                disabled = true;
                            } else {
                                if (!selesaiSebelumnya) {
                                    cardHeaderClass = 'bg-secondary';
                                    btnText = '<i class="bi bi-hourglass-split me-2"></i>MENUNGGU WAKTU';
                                    btnClass = 'btn-gradient-secondary text-white opacity-75';
                                    disabled = true;
                                } else {
                                    cardHeaderClass = 'bg-primary';
                                    btnText = 'MULAI KERJAKAN <i class="bi bi-arrow-right ms-2"></i>';
                                    btnClass = 'btn-gradient-primary';
                                    disabled = false;
                                }
                            }
                        }
                    } else if (lanjutkan && !selesai) {
                        // Jika melanjutkan, cek apakah jadwal masih aktif
                        if (nowTime > jadwalEnd) {
                            cardHeaderClass = 'bg-danger';
                            btnText = '<i class="bi bi-x-circle me-2"></i>WAKTU HABIS';
                            btnClass = 'btn-gradient-danger';
                            disabled = true;
                        } else if (!selesaiSebelumnya) {
                            cardHeaderClass = 'bg-secondary';
                            btnText = '<i class="bi bi-hourglass-split me-2"></i>MENUNGGU WAKTU';
                            btnClass = 'btn-gradient-secondary text-white opacity-75';
                            disabled = true;
                        } else {
                            cardHeaderClass = 'bg-warning text-dark';
                            btnText = 'LANJUTKAN <i class="bi bi-arrow-right ms-2"></i>';
                            btnClass = 'btn-gradient-warning text-white';
                            disabled = false;
                        }
                    } else {
                        cardHeaderClass = 'bg-success';
                        btnText = '<i class="bi bi-check-circle-fill me-2"></i>SUDAH SELESAI';
                        btnClass = 'btn-gradient-success';
                        disabled = false;
                        action = `location.href='${base_url}cbt/hasil/${tokenUrl}'`;
                    }

                    // Determine Accent Color
                    let accentColor = '#4f46e5';
                    if (cardHeaderClass.includes('bg-secondary') || cardHeaderClass.includes('bg-dark')) accentColor = '#e2e8f0';
                    else if (cardHeaderClass.includes('bg-danger')) accentColor = '#fee2e2';
                    else if (cardHeaderClass.includes('bg-warning')) accentColor = '#fef3c7';
                    else if (cardHeaderClass.includes('bg-success')) accentColor = '#dcfce7';
                    else accentColor = '#e0e7ff'; // Primary Light

                    // Token hanya tampil di card yang aktif waktunya dan belum dikerjakan
                    const isActiveAndNotWorked = !disabled && !selesai;

                    // Render Card
                    html += `
                    <div class="col-md-6 col-lg-4 mb-4 fade-in-up" style="animation-delay: ${delay}s">
                         <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                            <!-- Header -->
                            <div class="card-header ${cardHeaderClass} text-white p-4 border-0 position-relative">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div style="max-width: 85%;">
                                        <h5 class="fw-bold mb-1 text-truncate" title="${bank.mapel.nama_mapel}" style="font-size: 1.1rem;">${bank.mapel.nama_mapel}</h5>
                                        <div class="small opacity-75">${bank.bank_kode}</div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <i class="bi bi-file-text fs-4 opacity-50"></i>
                                        <span class="badge bg-white bg-opacity-25 rounded-pill px-2 py-1" style="font-size: 0.7rem;">Jam ke-${jamKe}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Body -->
                            <div class="card-body px-4 pt-4 pb-2 position-relative">
                                <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background-color: ${accentColor};
                                    -webkit-mask-image: url('${base_url}/assets/img/logo-card.png');
                                    mask-image: url('${base_url}/assets/img/logo-card.png');
                                    -webkit-mask-size: 80%;
                                    mask-size: 80%;
                                    -webkit-mask-repeat: no-repeat;
                                    mask-repeat: no-repeat;
                                    -webkit-mask-position: right center;
                                    mask-position: right center;
                                    opacity: 0.40;
                                    z-index: 0;
                                    pointer-events: none;
                                "></div>
                                <div class="d-flex flex-column gap-3 position-relative z-1">
                                    <div class="d-flex align-items-center">
                                        <div class="text-muted" style="width: 24px;"><i class="bi bi-person"></i></div>
                                        <div class="small text-muted flex-shrink-0 me-3">Guru</div>
                                        <div class="small fw-bold text-dark text-end ms-auto text-truncate" style="max-width: 60%;">${bank.guru ? bank.guru.nama_guru : '-'}</div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="text-muted" style="width: 24px;"><i class="bi bi-clock"></i></div>
                                        <div class="small text-muted flex-shrink-0 me-3">Durasi</div>
                                        <div class="small fw-bold text-dark text-end ms-auto">${jadwal.durasi_ujian || jadwal.durasi || '-'} Menit</div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="text-muted" style="width: 24px;"><i class="bi bi-list-task"></i></div>
                                        <div class="small text-muted flex-shrink-0 me-3">Soal</div>
                                        <div class="small fw-bold text-dark text-end ms-auto">${totalSoal} Butir</div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="text-muted" style="width: 24px;"><i class="bi bi-award"></i></div>
                                        <div class="small text-muted flex-shrink-0 me-3">KKM</div>
                                        <div class="small fw-bold text-dark text-end ms-auto">${bank.kkm || '-'}</div>
                                    </div>
                                    ${(tknSiswa === 1 && isActiveAndNotWorked && jadwal.token == '1' && jadwal.token_value) ? `
                                    <div class="d-flex align-items-center pt-2 mt-1 border-top border-dashed cursor-pointer" onclick="copyTokenValue('${jadwal.token_value}')" title="Klik untuk salin token">
                                        <div class="text-primary" style="width: 24px;"><i class="bi bi-key-fill"></i></div>
                                        <div class="small text-primary fw-semibold flex-shrink-0 me-3">Token</div>
                                        <div class="text-end ms-auto d-flex align-items-center gap-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 font-monospace fw-bold" style="font-size: 1rem; letter-spacing: 3px;">${jadwal.token_value}</span>
                                            <i class="bi bi-clipboard text-primary opacity-60 small"></i>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                            
                            <!-- Footer Action -->
                            <div class="px-4 pb-4 pt-1 bg-white border-top-0">
                                <button ${disabled ? 'disabled' : ''} onclick="${action}" class="btn w-100 py-3 rounded-pill fw-bold ${btnClass}" style="letter-spacing: 0.5px;">
                                    ${btnText}
                                </button>
                            </div>
                         </div>
                    </div>
                    `;
                    delay += 0.1;
                }
            }
            container.innerHTML = html;
        } else {
            container.innerHTML = `
            <div class="col-12 py-5">
                <div class="text-center p-5 rounded-4 border border-dashed text-muted" style="background: rgba(255,255,255,0.6);">
                    <div class="mb-3 display-1 opacity-25"><i class="bi bi-calendar-x"></i></div>
                    <h5 class="fw-bold text-secondary">Tidak Ada Ujian Aktif</h5>
                    <p class="text-muted">Jadwal ujian untuk hari ini belum tersedia.</p>
                </div>
            </div>`;
        }
    }

    async function populateJadwalSebelumnya(jadwals, siswa, skrg, jadwalToday) {
        const desktopContainer = document.querySelector('#table-desktop-history tbody');
        const mobileContainer = document.getElementById('cards-mobile-history');
        
        if (Object.keys(jadwals).length > 0) {
            let desktopHtml = '';
            let mobileHtml = '';

            // Safe date parsing helper
            const parseDate = (dateStr) => {
                if (!dateStr) return new Date();
                return new Date(dateStr.replace(/-/g, "/"));
            };
            const nowTime = parseDate(skrg);
            const todayStr = skrg.split(' ')[0]; // YYYY-MM-DD

            // Check if there are unfinished exams in "Daftar Ujian Hari Ini"
            let ujianHariIniBelumSelesai = [];
            if (jadwalToday && Object.keys(jadwalToday).length > 0) {
                for (const tglKey in jadwalToday) {
                    for (const bankToday of jadwalToday[tglKey]) {
                        const cbtToday = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === bankToday.jadwal.id_jadwal);
                        const belumSelesai = !cbtToday || cbtToday.selesai == null;
                        if (belumSelesai) {
                            ujianHariIniBelumSelesai.push(bankToday.mapel?.nama_mapel || bankToday.bank_kode || 'Ujian');
                        }
                    }
                }
            }
            const adaUjianAktif = ujianHariIniBelumSelesai.length > 0;
            // Store to window scope so showBlockSusulan() can access it
            window._ujianBelumSelesai = ujianHariIniBelumSelesai;

            for (const tanggal in jadwals) {
                const tgl = new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long', year: "numeric", month: "long", day: "numeric" });
                
                // Desktop Header Row
                desktopHtml += `<tr class="bg-primary bg-opacity-10"><td colspan="3" class="px-3 px-md-4 py-3 border-bottom border-primary border-opacity-25 fw-bold text-primary small text-uppercase" style="letter-spacing: 0.5px;"><i class="bi bi-calendar-check me-2"></i> ${tgl}</td></tr>`;
                
                // Mobile Header
                mobileHtml += `<div class="col-12 mt-4 fade-in-up"><div class="d-flex align-items-center"><div class="bg-primary" style="width: 4px; height: 16px; border-radius: 4px; margin-right: 8px;"></div><h6 class="fw-bold mb-0 text-primary small text-uppercase" style="letter-spacing: 0.5px;">${tgl}</h6></div></div>`;

                // Sort jadwals
                jadwals[tanggal].sort(sortByJam);
                
                for (const bank of jadwals[tanggal]) {
                    const jadwal = bank.jadwal;
                    const cbtSiswa = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === jadwal.id_jadwal);
                    const tokenUrl = jadwal.token_url ?? jadwal.id_jadwal;

                    // Check dates
                    const selesaiDate = jadwal.tgl_selesai ? jadwal.tgl_selesai.split(' ')[0] : '';
                    const jadwalEnd = parseDate(jadwal.tgl_selesai);
                    const masihBerlaku = nowTime <= jadwalEnd; // tgl_selesai belum lewat

                    // Determine status
                    let sudahSelesai = cbtSiswa && cbtSiswa.selesai != null;
                    let sedangDikerjakan = cbtSiswa && cbtSiswa.lama_ujian != null && !sudahSelesai;

                    let statusHtml = '';
                    let statusLabelMobile = '';
                    if (sudahSelesai) {
                        statusHtml = `<div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 border border-success border-opacity-25 fw-bold text-nowrap"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>
                                        <a href="${base_url}cbt/hasil/${tokenUrl}" class="btn btn-sm btn-success bg-gradient rounded-pill px-4 py-2 fw-bold text-white shadow-sm hover-scale text-nowrap"><i class="bi bi-bar-chart-fill me-1"></i>Hasil Ujian</a>
                                      </div>`;
                        statusLabelMobile = `<div class="d-flex align-items-center gap-2"><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span><a href="${base_url}cbt/hasil/${tokenUrl}" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold text-white shadow-sm"><i class="bi bi-bar-chart-fill me-1"></i>Hasil</a></div>`;
                    } else if (masihBerlaku) {
                        if (adaUjianAktif) {
                            // Block susulan: ada ujian hari ini belum selesai
                            const btnLabel = sedangDikerjakan ? 'LANJUTKAN' : 'Kerjakan';
                            const btnIcon = sedangDikerjakan ? 'bi-arrow-right-circle' : 'bi-pencil-square';
                            statusHtml = `<button type="button" onclick="showBlockSusulan()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-bold opacity-75 text-nowrap"><i class="bi ${btnIcon} me-1"></i>${btnLabel}</button>`;
                            statusLabelMobile = statusHtml.replace('btn-sm', 'btn-sm w-100 mt-2');
                        } else if (sedangDikerjakan) {
                            // Lanjutkan
                            statusHtml = `<a href="${base_url}cbtsiswa/konfirmasi/${tokenUrl}" class="btn btn-sm btn-warning rounded-pill px-3 py-2 fw-bold text-white shadow-sm text-nowrap transition-all"><i class="bi bi-arrow-right-circle me-1"></i>LANJUTKAN</a>`;
                            statusLabelMobile = statusHtml.replace('btn-sm', 'btn-sm w-100 mt-2');
                        } else {
                            // Kerjakan susulan
                            statusHtml = `<a href="${base_url}cbtsiswa/konfirmasi/${tokenUrl}" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold text-white shadow-sm text-nowrap transition-all" style="background: linear-gradient(45deg, #0d6efd, #0b5ed7);"><i class="bi bi-pencil-square me-1"></i>Kerjakan</a>`;
                            statusLabelMobile = statusHtml.replace('btn-sm', 'btn-sm w-100 mt-2');
                        }
                    } else {
                        // Sudah lewat & belum dikerjakan
                        statusHtml = `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2 text-nowrap fw-bold"><i class="bi bi-x-circle me-1"></i>Dilewatkan</span>`;
                        statusLabelMobile = `<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1"><i class="bi bi-x-circle me-1"></i>Dilewatkan</span>`;
                    }

                    // Berlaku sampai info
                    let berlakuInfo = '';
                    if (!sudahSelesai && masihBerlaku) {
                        berlakuInfo = `<div class="bg-success bg-opacity-10 text-success rounded px-2 py-1 d-inline-block small mt-2 fw-medium"><i class="bi bi-clock me-1"></i>Tersedia s/d ${selesaiDate}</div>`;
                    }

                    // Row styling
                    const trClass = sudahSelesai ? 'bg-light bg-opacity-50' : '';
                    const hoverClass = sudahSelesai ? '' : 'user-select-none hover-shadow';
                    const jamKe = jadwal.jam_ke === '0' ? '1' : jadwal.jam_ke;
                    const durasi = jadwal.durasi_ujian || jadwal.durasi || '-';

                    desktopHtml += `
                        <tr class="${trClass} ${hoverClass} align-middle">
                        <td class="px-3 px-md-4 py-3">
                            <div class="d-flex flex-column align-items-center">
                                <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 2.2rem; height: 2.2rem; font-size: 1rem;">${jamKe}</span>
                                <div class="small text-muted mt-2 text-nowrap fw-medium" style="font-size: 0.75rem;"><i class="bi bi-stopwatch me-1"></i>${durasi}m</div>
                            </div>
                        </td>
                        <td class="px-2 px-md-4 py-3">
                            <span class="fw-bold d-block text-dark fs-6 mb-1 line-clamp-2">${bank.mapel.nama_mapel}</span>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="badge bg-light text-dark border px-2 py-1 shadow-sm" style="font-weight: 600; font-size: 0.7rem;">${jadwal.jenis?.nama_jenis || '-'}</span>
                                <span class="badge bg-light text-secondary border px-2 py-1 font-monospace shadow-sm" style="font-size: 0.7rem;">${bank.bank_kode}</span>
                            </div>
                            ${berlakuInfo}
                        </td>
                        <td class="px-3 px-md-4 py-3 text-end" style="min-width: 175px;">
                            ${statusHtml}
                        </td>
                     </tr>
                    `;

                    // Mobile view item
                    const op = sudahSelesai ? 'opacity-75' : '';
                    mobileHtml += `
                    <div class="col-12 col-md-6 fade-in-up">
                        <div class="card bg-white ${op} border rounded-4 shadow-sm hover-scale-sm transition-all h-100">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">${jamKe}</span>
                                        <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.7rem;">${jadwal.jenis?.nama_jenis || '-'}</span>
                                    </div>
                                    ${sudahSelesai || (!masihBerlaku) ? statusLabelMobile : ''}
                                </div>
                                
                                <h6 class="fw-bold text-dark mb-1 line-clamp-2">${bank.mapel.nama_mapel}</h6>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="text-secondary font-monospace small" style="font-size: 0.75rem;">Kode: ${bank.bank_kode}</span>
                                    <span class="text-secondary small ms-auto"><i class="bi bi-clock me-1"></i>${durasi}m</span>
                                </div>
                                
                                ${berlakuInfo ? `<div class="mb-2 w-100">${berlakuInfo}</div>` : ''}
                                ${!sudahSelesai && masihBerlaku ? `<div class="mt-2 text-end">${statusLabelMobile}</div>` : ''}
                            </div>
                        </div>
                    </div>`;
                }
            }
            desktopContainer.innerHTML = desktopHtml;
            mobileContainer.innerHTML = mobileHtml;
            
        } else {
            desktopContainer.innerHTML = `<tr><td colspan="3" class="text-center py-5"><div class="text-muted opacity-50 mb-2"><i class="bi bi-inbox fs-1"></i></div><div class="fw-bold text-muted">Belum ada riwayat ujian</div></td></tr>`;
            mobileContainer.innerHTML = `<div class="col-12 py-5 text-center"><div class="text-muted opacity-50 mb-2"><i class="bi bi-inbox fs-1"></i></div><div class="fw-bold text-muted">Belum ada riwayat ujian</div></div>`;
        }
    }

    function sortByJam(a, b) {
        var a_jam = a.jadwal.jam_ke;
        var b_jam = b.jadwal.jam_ke;
        return ((a_jam < b_jam) ? -1 : ((a_jam > b_jam) ? 1 : 0));
    }

    function showBlockSusulan() {
        const mapelList = (window._ujianBelumSelesai || []).map(m => `<li class="mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i><strong>${m}</strong></li>`).join('');
        Swal.fire({
            title: 'Perhatian!',
            html: `<p class="mb-3">Anda harus menyelesaikan ujian hari ini terlebih dahulu sebelum mengerjakan ujian susulan:</p>
                   <ul class="text-start list-unstyled px-3"><center>${mapelList}</center></ul>`,
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Ya, Mengerti',
            confirmButtonColor: '#3085d6',
            customClass: { popup: 'rounded-4' }
        });
    }

    async function ajaxGet(url) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    type: "GET",
                    success: function (respon) {
                        resolve(respon);
                    },
                    error: function (xhr, status, error) {
                        reject(error);
                    }
                });
            }, 100)
        });
    }

    // Token Logic
    // Token Logic
    let tokenResponse;
    let failCount = 0;

    // Show token modal with per-jadwal data
    window.showTokenModal = function (jadwalId) {
        const jadwal = window.allJadwalData?.find(j => j.id_jadwal?.toString() === jadwalId?.toString());
        if (!jadwal) {
            $('#token-view').text('Data tidak ditemukan');
            $('#token-detail-title').text('Token Ujian');
            $('#token-detail-subtitle').text('Silakan coba lagi');
            $('#token-detail-modal').modal('show');
            return;
        }
        const bankKode = jadwal.bank?.bank_kode || 'Ujian';
        const tokenValue = (jadwal.token == '1' || jadwal.token == '1') && jadwal.token_value
            ? jadwal.token_value : '---';

        $('#token-detail-title').text(`Token: ${bankKode}`);
        $('#token-detail-subtitle').text(tokenValue !== '---' ? 'Gunakan token ini untuk masuk ke ujian' : 'Ujian ini tidak menggunakan token');
        $('#token-view').text(tokenValue);
        $('#token-detail-modal').modal('show');
    };

    window.copyTokenFromModal = function () {
        const token = $('#token-view').text();
        if (token && token !== '---') {
            navigator.clipboard.writeText(token).then(function () {
                Swal.fire({ icon: 'success', title: 'Tersalin', text: 'Token: ' + token, timer: 1500, showConfirmButton: false });
            });
        }
    };

window.copyTokenValue = function (token) {
        if (token) {
            navigator.clipboard.writeText(token).then(function () {
                sessionStorage.setItem('lastCopiedToken', token);
                sessionStorage.setItem('lastCopiedTokenTime', Date.now().toString());
                var toast = document.createElement('div');
                toast.className = 'position-fixed top-0 start-50 translate-middle-x mt-2 px-3 py-1 rounded-pill shadow-sm text-white small fw-semibold';
                toast.style.cssText = 'z-index:99999;background:#1877F2;font-size:0.8rem;animation:fadeInOut 1.5s ease forwards;';
                toast.textContent = 'Token tersalin!';
                document.body.appendChild(toast);
                setTimeout(function () { toast.remove(); }, 1500);
            });
        }
    };

    $(document).ready(function () {
        // No automatic token loading needed
    });
    // Flash Message Handler for Active Exam
    <?php if (session()->getFlashdata('error')): ?>
        const flashError = "<?= addslashes(session()->getFlashdata('error')) ?>";
        if (flashError.toLowerCase().includes("sesi aktif") || flashError.toLowerCase().includes("ujian lain")) {
            Swal.fire({
                title: '<span class="fw-bold text-dark">Ujian Lain Aktif!</span>',
                html: `<div class="text-muted mb-3">${flashError}</div>`,
                icon: 'warning',
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#0d6efd',
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0',
                    confirmButton: 'rounded-pill px-5 fw-bold shadow-sm'
                },
                buttonsStyling: true,
                showCloseButton: true
            });
        }
    <?php endif; ?>
</script>
<?= $this->endSection() ?>