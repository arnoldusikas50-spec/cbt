<?php
/**
 * View Redesign: CBT Siswa Dashboard
 * Framework: Bootstrap 5
 * Style: Modern, Clean, Dashboard-style
 */
?>



<div class="container-fluid py-4 container-p-x main-content-overlap">

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Wrapper Card -->
            <div class="card card-modern border-0">
                <div class="card-body p-4 p-md-5">

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mb-4 gap-2">
                        <a href="<?= base_url('dashboard') ?>"
                            class="btn btn-light border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                            <i class="bi bi-arrow-left-circle-fill me-2 text-primary"></i> Kembali
                        </a>
                        <div class="d-flex gap-2">
                            <button onclick="window.location.reload()"
                                class="btn btn-light rounded-circle shadow-sm border hover-scale icon-box-sm d-flex align-items-center justify-content-center"
                                style="width: 42px; height: 42px;" title="Reload Data">
                                <i class="bi bi-arrow-clockwise fs-5"></i>
                            </button>
                            <button type="button"
                                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm hover-scale"
                                data-bs-toggle="modal" data-bs-target="#token_notif" style="background: linear-gradient(135deg, #1877F2 0%, #0044cc 100%); border: none;">
                                <i class="bi bi-key-fill me-2"></i> Token
                            </button>
                        </div>
                    </div>

                    <!-- Stats & Header -->
                    <div id="stats-container" class="row g-2 g-md-4 mb-4 mb-md-5">
                        <!-- Injected via JS -->
                    </div>

                    <!-- Section Title -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary"
                            style="width: 5px; height: 24px; border-radius: 4px; margin-right: 12px;"></div>
                        <h4 class="fw-bold m-0 text-dark">Daftar Ujian Hari Ini</h4>
                    </div>

                    <div class="alert alert-warning border-0 bg-warning bg-opacity-10 shadow-none rounded-3 d-none mb-4"
                        id="no-sesisiswa">
                        <div class="d-flex p-3">
                            <div class="flex-shrink-0 me-3">
                                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Tidak Ada Jadwal Sesi</h5>
                                <p class="mb-0 text-muted">Anda tidak terdaftar dalam sesi ujian aktif manapun. Mohon
                                    hubungi proktor atau admin sekolah.</p>
                            </div>
                        </div>
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

                    <!-- History Section (Moved inside, card removed) -->
                    <div class="pt-4 border-top">
                        <div class="d-flex align-items-center mb-4">
                            <div class="icon-box bg-light-danger text-danger me-3 rounded-circle border"
                                style="background: #fee2e2;">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Riwayat Ujian & Susulan</h5>
                        </div>

                        <div class="table-responsive rounded-4 border">
                            <table class="table table-hover align-middle mb-0" id="table-container">
                                <thead class="bg-light">
                                    <tr class="text-secondary text-uppercase"
                                        style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <th class="py-3 px-4 fw-bold">Waktu</th>
                                        <th class="py-3 px-4 fw-bold">Mapel</th>
                                        <th class="py-3 px-4 fw-bold text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0 bg-white">
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div> <!-- End Wrapper Card Body -->
            </div> <!-- End Wrapper Card -->
        </div>
    </div>

</div>

<!-- Modern Token Modal -->
<div class="modal fade" id="token_notif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-0">
                <div class="p-5 text-center text-white rounded-top-4 position-relative overflow-hidden"
                    style="background: linear-gradient(135deg, #1877F2 0%, #0044cc 100%);">
                    <div class="position-absolute top-0 start-0 w-100 h-100"
                        style="background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48ZyBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9IkQwIDQwaDQwVjBIOHYzMmgzMnoiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvZz48L3N2Zz4='); opacity: 0.4;">
                    </div>
                    <i class="bi bi-shield-lock display-3 mb-3 d-block opacity-75"></i>
                    <h3 class="fw-bold mb-1">Token Ujian</h3>
                    <p class="opacity-75 mb-0">Gunakan token ini untuk masuk ke ujian</p>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-4"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="p-5 text-center bg-white rounded-bottom-4">
                    <div
                        class="p-4 mb-4 bg-light rounded-3 border border-2 border-dashed border-secondary position-relative">
                        <?php if ($setting->tkn_siswa == 1): ?>
                            <div id="token-view" class="display-4 fw-bold font-monospace text-dark ls-sm">Loading...</div>
                        <?php else: ?>
                            <h5 class="text-muted fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Token di Proktor</h5>
                        <?php endif; ?>
                    </div>
                    <?php if ($setting->tkn_siswa == 1): ?>
                        <button onclick="loadToken()"
                            class="btn text-white w-100 py-3 rounded-pill fw-bold shadow-sm hover-scale"
                            style="background: linear-gradient(135deg, #1877F2 0%, #0044cc 100%); border: none;">
                            <i class="bi bi-arrow-clockwise me-2"></i> Perbarui Token
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(async function () {
        const data = await ajaxGet('cbtsiswa/get_cbt_siswa?id_siswa=' + <?= $siswa->id_siswa ?>);
        console.log('cbtSiswa', data)
        await populateDataSiswa(data)
    })

    async function populateDataSiswa(data) {
        const siswa = data.siswa;
        let totalUjian = 0;
        let selesai = 0;

        // Helper to count exams
        const countExams = (jadwals) => {
            if (!jadwals) return;
            for (const dateKey in jadwals) {
                totalUjian += jadwals[dateKey].length;
                jadwals[dateKey].forEach(item => {
                    const cbtSiswa = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === item.jadwal.id_jadwal);
                    if (cbtSiswa && cbtSiswa.selesai != null) {
                        selesai++;
                    }
                });
            }
        };

        countExams(data.jadwal_today);
        countExams(data.jadwal_before);

        const rataRata = "-"; // Placeholder

        const statsHtml = `
        <div class="col-4">
            <div class="card border-0 bg-white shadow-sm rounded-4 h-100">
                <div class="card-body p-2 p-md-3 d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-start text-center text-md-start">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mb-1 mb-md-0 me-0 me-md-3" style="width: 36px; height: 36px; font-size: 1rem;">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase d-none d-md-block" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Ujian</div>
                        <div class="text-muted small fw-bold text-uppercase d-block d-md-none" style="font-size: 0.6rem; letter-spacing: 0.5px;">Total</div>
                        <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4">${totalUjian}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 bg-white shadow-sm rounded-4 h-100">
                <div class="card-body p-2 p-md-3 d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-start text-center text-md-start">
                    <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle mb-1 mb-md-0 me-0 me-md-3" style="width: 36px; height: 36px; font-size: 1rem;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Selesai</div>
                        <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4">${selesai}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 bg-white shadow-sm rounded-4 h-100">
                <div class="card-body p-2 p-md-3 d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-start text-center text-md-start">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle mb-1 mb-md-0 me-0 me-md-3" style="width: 36px; height: 36px; font-size: 1rem;">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Rata-rata</div>
                        <h4 class="fw-bold mb-0 text-dark fs-5 fs-md-4">${rataRata}</h4>
                    </div>
                </div>
            </div>
        </div>
        `;

        $('#stats-container').html(statsHtml);

        if (totalUjian === 0) {
            $('#no-sesisiswa').removeClass('d-none');
        } else {
            $('#no-sesisiswa').addClass('d-none');
            await populateJadwalHariIni(data.jadwal_today, siswa, data.today);
            await populateJadwalSebelumnya(data.jadwal_before, siswa, data.today);
        }
    }

    async function populateJadwalHariIni(jadwals, siswa, skrg) {
        const container = document.getElementById('jadwal-content')
        if (Object.keys(jadwals).length > 0) {
            let html = '';
            let delay = 0.2;

            for (const tanggal in jadwals) {
                jadwals[tanggal].sort(sortByJam)
                let indJam = 1;
                let jadwalSebelumnya;

                for (const bank of jadwals[tanggal]) {
                    const jadwal = bank.jadwal;
                    const jamKe = jadwal.jam_ke === '0' ? '1' : jadwal.jam_ke;
                    const cbtSiswa = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === jadwal.id_jadwal)

                    const startDay = (jadwal.tgl_mulai).replaceAll('-', '');
                    const endDay = (jadwal.tgl_selesai).replaceAll('-', '');
                    const today = (skrg.split(' ')[0]).replaceAll('-', '');
                    const sesiMulai = (siswa.sesisiswa.sesi.waktu_mulai).replaceAll(':', '');
                    const sesiSampai = (siswa.sesisiswa.sesi.waktu_akhir).replaceAll(":", '');
                    const now = (skrg.split(' ')[1]).replaceAll(':', '');
                    const jamSebelumnya = jadwalSebelumnya?.jam_ke;
                    const idSebelumnya = jadwalSebelumnya?.id_jadwal;
                    const cbtSebelumnya = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === idSebelumnya);
                    const selesaiSebelumnya = jamSebelumnya == '0' || jamSebelumnya === jamKe ? true : cbtSebelumnya?.selesai;

                    // Calculate Total Soal
                    let totalSoal = parseInt(bank.tampil_pg || 0) + parseInt(bank.tampil_kompleks || 0) + parseInt(bank.tampil_jodohkan || 0) + parseInt(bank.tampil_isian || 0) + parseInt(bank.tampil_esai || 0);
                    if (totalSoal === 0) totalSoal = bank.jml_soal || '-';

                    let selesai, lanjutkan, resetWaktu;
                    if (cbtSiswa) {
                        selesai = cbtSiswa.selesai !== null;
                        lanjutkan = cbtSiswa.lama_ujian !== null;
                        resetWaktu = cbtSiswa.reset_waktu;
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
                    let action = `location.href='${base_url}cbtsiswa/konfirmasi/${jadwal.id_jadwal}'`;
                    let statusLabel = 'TERSEDIA';

                    if (!lanjutkan && resetWaktu === "0" && !selesai) {
                        if (today < startDay) {
                            cardHeaderClass = 'bg-secondary';
                            btnText = '<i class="bi bi-calendar-event me-2"></i>BELUM DIMULAI';
                            btnClass = 'btn-gradient-secondary opacity-75';
                            disabled = true;
                        } else if (today > endDay) {
                            cardHeaderClass = 'bg-danger';
                            btnText = '<i class="bi bi-x-circle me-2"></i>WAKTU HABIS';
                            btnClass = 'btn-gradient-danger';
                            disabled = true;
                        } else {
                            if (now < sesiMulai) {
                                cardHeaderClass = 'bg-secondary';
                                btnText = '<i class="bi bi-clock me-2"></i>MENUNGGU SESI';
                                btnClass = 'btn-gradient-secondary opacity-75';
                                disabled = true;
                            } else if (now > sesiSampai) {
                                cardHeaderClass = 'bg-danger';
                                btnText = '<i class="bi bi-door-closed me-2"></i>UJIAN DITUTUP';
                                btnClass = 'btn-gradient-danger';
                                disabled = true;
                            } else {
                                if (indJam > 1 && !selesaiSebelumnya) {
                                    cardHeaderClass = 'bg-secondary';
                                    btnText = '<i class="bi bi-hourglass-split me-2"></i>MENUNGGU ANTRIAN';
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
                        if (indJam > 1 && !selesaiSebelumnya) {
                            cardHeaderClass = 'bg-secondary';
                            btnText = '<i class="bi bi-hourglass-split me-2"></i>MENUNGGU ANTRIAN';
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
                        btnClass = 'btn-gradient-success opacity-75';
                        disabled = true;
                    }

                    // Determine Accent Color
                    let accentColor = '#4f46e5';
                    if (cardHeaderClass.includes('bg-secondary') || cardHeaderClass.includes('bg-dark')) accentColor = '#e2e8f0';
                    else if (cardHeaderClass.includes('bg-danger')) accentColor = '#fee2e2';
                    else if (cardHeaderClass.includes('bg-warning')) accentColor = '#fef3c7';
                    else if (cardHeaderClass.includes('bg-success')) accentColor = '#dcfce7';
                    else accentColor = '#e0e7ff'; // Primary Light

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
                                    <i class="bi bi-file-text fs-4 opacity-50"></i>
                                </div>
                            </div>
                            
                            <!-- Body -->
                            <div class="card-body p-4 position-relative">
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
                                        <div class="small text-muted flex-shrink-0 me-3">Waktu</div>
                                        <div class="small fw-bold text-dark text-end ms-auto">${siswa.sesisiswa.sesi.waktu_mulai?.slice(0, 5)} - ${siswa.sesisiswa.sesi.waktu_akhir?.slice(0, 5)}</div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="text-muted" style="width: 24px;"><i class="bi bi-list-task"></i></div>
                                        <div class="small text-muted flex-shrink-0 me-3">Soal</div>
                                        <div class="small fw-bold text-dark text-end ms-auto">${totalSoal} Butir</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer Action -->
                            <div class="p-3 bg-white border-top-0">
                                <button ${disabled ? 'disabled' : ''} onclick="${action}" class="btn w-100 py-3 rounded-pill fw-bold ${btnClass}" style="letter-spacing: 0.5px;">
                                    ${btnText}
                                </button>
                            </div>
                         </div>
                    </div>
                    `;
                    delay += 0.1;
                    indJam++;
                    jadwalSebelumnya = jadwal;
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

    async function populateJadwalSebelumnya(jadwals, siswa, skrg) {
        const container = document.getElementById('table-container').querySelector('tbody');
        if (Object.keys(jadwals).length > 0) {
            let html = '';
            for (const tanggal in jadwals) {
                const tgl = new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long', year: "numeric", month: "long", day: "numeric" });
                html += `<tr class="bg-light"><td colspan="3" class="px-4 py-2 border-bottom fw-bold text-primary small text-uppercase"><i class="bi bi-calendar3 me-2"></i> ${tgl}</td></tr>`;
                jadwals[tanggal].sort(sortByJam);
                for (const bank of jadwals[tanggal]) {
                    const jadwal = bank.jadwal;
                    html += `
                        <tr>
                        <td class="px-4 py-3">
                            <span class="fw-bold text-dark">${jadwal.jam_ke === '0' ? '1' : jadwal.jam_ke}</span>
                            <div class="small text-muted">${jadwal.durasi_ujian} Menit</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="fw-bold d-block text-dark">${bank.mapel.nama_mapel}</span>
                            <span class="badge bg-light text-secondary border">${jadwal.jenis.nama_jenis}</span>
                            <span class="small text-muted ms-2">${bank.bank_kode}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge bg-secondary rounded-pill">Selesai</span>
                        </td>
                     </tr>
                        `;
                }
            }
            container.innerHTML = html;
        } else {
            container.innerHTML = `<tr><td colspan="3" class="text-center py-5"><div class="text-muted opacity-50 mb-2"><i class="bi bi-inbox fs-1"></i></div><div>Belum ada riwayat ujian</div></td></tr>`;
        }
    }

    function sortByJam(a, b) {
        var a_jam = a.jadwal.jam_ke;
        var b_jam = b.jadwal.jam_ke;
        return ((a_jam < b_jam) ? -1 : ((a_jam > b_jam) ? 1 : 0));
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
    let tokenResponse;
    $(document).ready(function () {
        window.loadToken = function () {
            if ($('#token-view').length) $('#token-view').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            $.ajax({
                url: base_url + "siswamod/cektokenmod/",
                type: "GET",
                dataType: "json",
                success: function (response) {
                    tokenResponse = response;
                    let tokenValue = (response && response.token) ? response.token : ((typeof response === 'string') ? response : 'ERROR');
                    $('#token-view').html(tokenValue);
                },
                error: function (xhr, status, error) {
                    $('#token-view').html('<span class="text-danger">ERROR</span>');
                }
            });
        }
        loadToken();
        setInterval(loadToken, 30000);
    });
</script>