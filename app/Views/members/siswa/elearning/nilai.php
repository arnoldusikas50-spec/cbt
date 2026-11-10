<?= $this->extend('layouts/siswa') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4 container-p-x main-content-overlap pb-5 mb-5 pb-lg-4 mb-lg-0">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Main Card -->
            <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                <!-- Header Decoration -->
                <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 0;">
                    <div style="position: absolute; top: -10%; right: -5%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, rgba(255,255,255,0) 70%); border-radius: 50%;"></div>
                </div>

                <div class="card-body p-3 p-md-4 position-relative z-1">
                    <!-- Watermark -->
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #e0e7ff; -webkit-mask-image: url('<?= base_url('assets/img/logo-card.png') ?>'); mask-image: url('<?= base_url('assets/img/logo-card.png') ?>'); -webkit-mask-size: 50%; mask-size: 50%; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-position: center; mask-position: center; opacity: 0.05; z-index: -1; pointer-events: none;"></div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success bg-opacity-10 text-success rounded-4 me-3 d-flex align-items-center justify-content-center border border-success border-opacity-10" style="width: 48px; height: 48px;">
                                <i class="bi bi-trophy-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0 fs-5 fs-md-4"><?= esc($judul ?? 'Daftar Nilai') ?></h5>
                                <div class="text-muted small d-none d-sm-block mt-1"><?= esc($subjudul ?? 'Rekap nilai hasil ujian CBT') ?></div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('dashboard') ?>"
                                class="btn btn-primary rounded-pill px-3 px-md-4 py-2 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                <i class="bi bi-arrow-left me-0 me-md-2"></i> 
                                <span class="d-none d-md-inline">Kembali</span>
                            </a>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card bg-light border-0 rounded-3 mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" id="searchInput" class="form-control border-start-0 rounded-end-pill shadow-none" placeholder="Cari mapel / jenis...">
                                    </div>
                                </div>
                                <div class="col-12 col-md-8 d-flex align-items-center gap-2 overflow-auto" id="filterChips">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill flex-shrink-0 active filter-chip" data-filter="all" onclick="setFilter('all')">
                                        <i class="bi bi-grid-fill me-1"></i> Semua <span class="badge bg-primary rounded-pill ms-1" id="count-all">0</span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success rounded-pill flex-shrink-0 filter-chip" data-filter="selesai" onclick="setFilter('selesai')">
                                        <i class="bi bi-check-circle-fill me-1"></i> Selesai <span class="badge bg-success rounded-pill ms-1" id="count-selesai">0</span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning rounded-pill flex-shrink-0 filter-chip" data-filter="belum" onclick="setFilter('belum')">
                                        <i class="bi bi-clock-fill me-1"></i> Belum <span class="badge bg-warning text-dark rounded-pill ms-1" id="count-belum">0</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div id="loading-state" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Memuat data nilai...</p>
                    </div>

                    <!-- Empty State -->
                    <div id="empty-state" class="text-center py-5 d-none">
                        <div class="d-inline-flex flex-column align-items-center text-muted opacity-50">
                            <i class="bi bi-inbox fs-1 mb-2"></i>
                            <span>Belum ada data nilai / jadwal ujian</span>
                        </div>
                    </div>

                    <!-- Data Container -->
                    <div id="data-container" class="d-none">
                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-lg-block">
                            <table class="table table-hover align-middle custom-table mb-0" id="table-nilai">
                                <thead class="bg-light bg-opacity-50">
                                    <tr>
                                        <th class="text-center py-3 rounded-start-3" style="width: 60px;">No</th>
                                        <th class="py-3">Jenis Ujian</th>
                                        <th class="py-3">Mata Pelajaran</th>
                                        <th class="text-center py-3">Kode Bank</th>
                                        <th class="text-center py-3 rounded-end-3" style="width: 140px;">Detail Nilai</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-desktop">
                                    <!-- Rendered via JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile/Tablet Card View -->
                        <div class="d-block d-lg-none">
                            <div class="row g-3" id="cards-mobile">
                                <!-- Rendered via JS -->
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 d-none" id="note-section">
                        <p class="text-warning-emphasis small mb-0 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <span><strong>Catatan:</strong> Tombol [**] berarti nilai belum ditampilkan. Hubungi Guru Pengampu untuk mengetahui nilai.</span>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Nilai (Bootstrap Modal) -->
<div class="modal fade" id="modalNilaiDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-1">
                <div class="d-flex flex-column">
                    <h5 class="modal-title fw-bold text-dark mb-0">Detail Nilai Ujian</h5>
                    <small class="text-muted" id="modal-subtitle">Memuat data...</small>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 px-4 pb-4" id="modal-body">
                <!-- Data populated by JS -->
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
    /* Custom Styles specific to this page but adhering to global variables */
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

    .hover-scale {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-scale:hover {
        transform: translateY(-2px);
    }
    
    .hover-scale-sm {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s ease;
    }

    .hover-scale-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
    }

    .filter-chip.active {
        background-color: var(--bs-primary);
        color: white;
        border-color: var(--bs-primary);
    }
    
    .filter-chip.active .badge {
        background-color: rgba(255,255,255,0.2) !important;
        color: white !important;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    
    .transition-all {
        transition: all 0.3s ease;
    }

    /* Score Ring */
    .score-ring-container {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .score-ring-bg {
        fill: none;
        stroke: #f1f5f9;
        stroke-width: 12;
    }
    .score-ring-fill {
        fill: none;
        stroke-width: 12;
        stroke-linecap: round;
        transform: rotate(-90deg);
        transform-origin: center;
        transition: stroke-dashoffset 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        filter: drop-shadow(0 0 4px rgba(0,0,0,0.1));
    }
    .score-ring-text {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    .score-ring-value {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1;
        font-family: 'Inter', sans-serif;
    }
    .score-ring-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        margin-top: 2px;
        color: #94a3b8;
        text-transform: uppercase;
    }
    
    .modal-nilai-card {
        background: white;
        border-radius: 1.25rem;
        padding: 1rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const arrbulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const arrhari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
    let jadwals = [];
    let currentFilter = 'all';
    let currentSearch = '';
    const modalNilai = new bootstrap.Modal(document.getElementById('modalNilaiDetail'));

    $(document).ready(async function () {
        try {
            const response = await $.get(base_url + 'kbm/get_all_nilai/');
            populateData(response);
        } catch (e) {
            console.error('Error:', e);
            Swal.fire({title:'Error', text:'Gagal memuat data nilai', icon:'error', confirmButtonColor:'#0d6efd'});
            $('#loading-state').addClass('d-none');
        }
    });

    // Search
    $('#searchInput').on('input', function() {
        currentSearch = this.value.toLowerCase().trim();
        renderFiltered();
    });

    function setFilter(filter) {
        currentFilter = filter;
        document.querySelectorAll('.filter-chip').forEach(c => {
            if(c.dataset.filter === filter) {
                c.classList.add('active', 'text-white');
            } else {
                c.classList.remove('active', 'text-white');
            }
        });
        renderFiltered();
    }

    function populateData(data) {
        $('#loading-state').addClass('d-none');
        
        if (!data?.all_jadwal || data.all_jadwal.length === 0) {
            $('#empty-state').removeClass('d-none');
            return;
        }

        $('#data-container').removeClass('d-none');
        $('#note-section').removeClass('d-none');

        jadwals = data.all_jadwal.map(j => {
            const log = j.cbtsiswa?.[0];
            j._blmMulai = !log?.mulai;
            j._blmSelesai = !log?.selesai;
            j._siswaId = data.siswa?.id_siswa;
            j._searchText = [
                j.jenis?.nama_jenis,
                j.bank?.mapel?.kode,
                j.bank?.mapel?.nama_mapel,
                j.bank?.bank_kode
            ].filter(Boolean).join(' ').toLowerCase();
            return j;
        });

        // Stats
        const total = jadwals.length;
        const selesai = jadwals.filter(j => !j._blmMulai && !j._blmSelesai).length;
        const belum = total - selesai;
        
        $('#count-all').text(total);
        $('#count-selesai').text(selesai);
        $('#count-belum').text(belum);

        renderFiltered();
    }

    function getFilteredJadwals() {
        return jadwals.filter(j => {
            if (currentFilter === 'selesai' && (j._blmMulai || j._blmSelesai)) return false;
            if (currentFilter === 'belum' && !j._blmMulai && !j._blmSelesai) return false;
            if (currentSearch && !j._searchText.includes(currentSearch)) return false;
            return true;
        });
    }

    function renderFiltered() {
        const filtered = getFilteredJadwals();
        renderDesktopTable(filtered);
        renderMobileCards(filtered);
    }

    function renderDesktopTable(list) {
        const tbody = document.getElementById('tbody-desktop');
        if (!list.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted opacity-50"><i class="bi bi-search fs-3 d-block mb-2"></i>Tidak ada data ditemukan</td></tr>`;
            return;
        }
        let html = '';
        list.forEach((j, i) => {
            const detail = getDetailBadge(j);
            html += `
                <tr>
                    <td class="text-center fw-bold text-secondary">${i + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="icon-box-sm bg-light text-secondary rounded-circle me-3" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-tag"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark">${j.jenis?.nama_jenis || '-'}</span>
                                <small class="text-muted d-flex align-items-center mt-1">
                                    <i class="bi bi-calendar4 me-1"></i> ${formatDate(j.tgl_mulai)}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark mb-1 text-wrap line-clamp-2" style="max-width: 250px;">
                            ${j.bank?.mapel?.nama_mapel || '-'}
                        </div>
                        <span class="badge bg-light text-secondary border px-2 py-1">
                            ${j.bank?.mapel?.kode || '-'}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace shadow-sm">
                            ${j.bank?.bank_kode || '-'}
                        </span>
                    </td>
                    <td class="text-center">${detail}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function renderMobileCards(list) {
        const container = document.getElementById('cards-mobile');
        if (!list.length) {
            container.innerHTML = `
                <div class="col-12 text-center py-5 text-muted opacity-50">
                    <i class="bi bi-search fs-1 mb-2"></i>
                    <p>Tidak ada data ditemukan</p>
                </div>`;
            return;
        }
        let html = '';
        list.forEach((j, i) => {
            const detail = getDetailBadge(j, true);
            const mapelName = j.bank?.mapel?.nama_mapel || j.bank?.mapel?.kode || '-';
            const jenisName = j.jenis?.nama_jenis || '-';
            html += `
                <div class="col-12 col-md-6">
                    <div class="card bg-white border rounded-4 shadow-sm h-100 hover-scale-sm transition-all">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 align-self-start fw-medium" style="font-size: 0.75rem;">
                                        ${jenisName}
                                    </span>
                                    <span class="badge bg-light text-secondary border px-2 py-1 align-self-start mt-1" style="font-size: 0.7rem;">
                                        Kode: <span class="text-dark font-monospace">${j.bank?.bank_kode || '-'}</span>
                                    </span>
                                </div>
                                <div>${detail}</div>
                            </div>
                            
                            <h6 class="fw-bold text-dark mb-2 line-clamp-2">${mapelName}</h6>
                            
                            <div class="d-flex align-items-center text-muted small bg-light rounded-3 p-2">
                                <div class="bg-white rounded-circle d-flex justify-content-center align-items-center shadow-sm me-2" style="width: 28px; height: 28px;">
                                    <i class="bi bi-calendar4 text-primary" style="font-size: 0.8rem;"></i>
                                </div>
                                <span class="fw-medium text-dark flex-grow-1" style="font-size: 0.8rem;">${formatDate(j.tgl_mulai)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function getDetailBadge(j, isMobile = false) {
        const sizeClass = isMobile ? 'font-size: 0.75rem;' : '';
        
        if (j._blmMulai) {
            return `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill shadow-sm" style="${sizeClass}"><i class="bi bi-dash-circle me-1"></i> Belum</span>`;
        }
        if (j._blmSelesai) {
            return `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill shadow-sm" style="${sizeClass}"><i class="bi bi-hourglass-split me-1"></i> Proses</span>`;
        }
        if (j.hasil_tampil === '0') {
            return `<span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill shadow-sm" style="${sizeClass}"><i class="bi bi-eye-slash-fill me-1"></i> **</span>`;
        }
        return `<button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-sm fw-bold hover-scale" data-jadwal="${j.id_jadwal}" data-siswa="${j._siswaId}" onclick="showNilaiModal(this)">
            <i class="bi bi-eye-fill me-1"></i> Detail
        </button>`;

    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    // Modal Logic
    async function showNilaiModal(btn) {
        const jadwalId = $(btn).data('jadwal');
        const siswa = $(btn).data('siswa');

        document.getElementById('modal-subtitle').textContent = 'Memuat data...';
        document.getElementById('modal-body').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3 fw-medium">Memuat data nilai...</p>
            </div>
        `;
        
        modalNilai.show();

        try {
            const response = await $.get(base_url + 'kbm/get_nilai_jadwal/', { jadwal: jadwalId, siswa: siswa });
            if (response.success) {
                renderNilaiModal(response);
            } else {
                document.getElementById('modal-body').innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                        <p class="text-dark fw-bold mt-2">${response.message || 'Gagal memuat nilai'}</p>
                    </div>`;
            }
        } catch (e) {
            document.getElementById('modal-body').innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-wifi-off text-danger fs-1"></i>
                    <p class="text-dark fw-bold mt-2">Terjadi kesalahan server</p>
                </div>`;
        }
    }

    function renderNilaiModal(response) {
        const j = response.jadwal;
        if (!j?.sesisiswa?.durasi) {
            document.getElementById('modal-body').innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                    <p class="text-dark fw-bold mt-2">Data tidak lengkap</p>
                </div>`;
            return;
        }

        const dur = j.sesisiswa.durasi;
        const skor = j.sesisiswa.nilai_akhir || {};
        const tampil = j.hasil_tampil === '1';
        const dikoreksi = skor.dikoreksi == '1';

        const sp = j.tgl_mulai?.split('-') || [];
        const tglJadwal = sp.length >= 3 ? `${arrhari[new Date(sp[0], sp[1] - 1, sp[2]).getDay()]}, ${sp[2]} ${arrbulan[parseInt(sp[1])]} ${sp[0]}` : '-';
        document.getElementById('modal-subtitle').textContent = j.bank?.mapel?.nama_mapel || j.jenis?.nama_jenis || '';

        const jamMulai = dur.mulai?.split(' ')[1]?.substring(0, 5) || '--:--';
        const jamSelesai = dur.selesai?.split(' ')[1]?.substring(0, 5) || '--:--';
        const durasi = dur.durasi || '--';

        if (!tampil) {
            document.getElementById('modal-body').innerHTML = `
                <div class="text-center py-5">
                    <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-warning bg-opacity-10 mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-eye-slash-fill text-warning fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Nilai Belum Ditampilkan</h5>
                    <p class="text-muted small">Hubungi guru pengampu untuk mengetahui nilai.</p>
                </div>`;
            return;
        }

        const scoreVal = parseFloat(skor.skor_asli) || 0;
        const scorePercent = Math.min(scoreVal, 100);
        const radius = 58;
        const circumference = 2 * Math.PI * radius;
        const offset = circumference - (scorePercent / 100) * circumference;
        const scoreColor = scoreVal >= 75 ? '#198754' : scoreVal >= 50 ? '#ffc107' : '#dc3545';

        const types = [
            { name: 'Pilihan Ganda', key: 'pg', jml: j.bank?.tampil_pg, benar: skor.pg_benar, skor: skor.skor_pg?.skor_total },
            { name: 'PG Kompleks', key: 'kompleks', jml: j.bank?.tampil_kompleks, benar: skor.skor_kompleks?.jml_benar, skor: skor.skor_kompleks?.skor_asli },
            { name: 'Menjodohkan', key: 'jodohkan', jml: j.bank?.tampil_jodohkan, benar: skor.skor_jodohkan?.jml_benar, skor: skor.skor_jodohkan?.skor_asli },
            { name: 'Isian Singkat', key: 'isian', jml: j.bank?.tampil_isian, benar: skor.skor_isian?.jml_benar, skor: dikoreksi ? skor.skor_isian?.skor_asli : '<i class="text-warning">dikoreksi</i>' },
            { name: 'Uraian/Esai', key: 'esai', jml: j.bank?.tampil_esai, benar: '-', skor: dikoreksi ? skor.skor_essai?.skor_asli : '<i class="text-warning">dikoreksi</i>' },
            { name: 'Mengurutkan', key: 'urutan', jml: j.bank?.tampil_urutan, benar: skor.skor_urutan?.jml_benar, skor: skor.skor_urutan?.skor_asli },
            { name: 'Isian Rumpang', key: 'cloze', jml: j.bank?.tampil_cloze, benar: skor.skor_cloze?.jml_benar, skor: skor.skor_cloze?.skor_asli },
        ].filter(t => parseInt(t.jml) > 0);

        const typeRows = types.map(t => `
            <tr>
                <td class="fw-medium text-dark">${t.name}</td>
                <td class="text-center text-muted">${t.jml}</td>
                <td class="text-center fw-bold text-success">${t.benar ?? 0}</td>
                <td class="text-center fw-bold text-primary">${t.skor ?? 0}</td>
            </tr>
        `).join('');

        const html = `
            <div class="modal-nilai-content">
                <!-- Session Info Cards -->
                <div class="row g-2 mb-4">
                    <div class="col-12">
                        <div class="modal-nilai-card bg-light bg-opacity-50 border-0 flex-row d-flex align-items-center mb-2">
                            <div class="icon-box-sm bg-white rounded-3 shadow-sm text-primary me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-calendar-check-fill fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Hari & Tanggal Ujian</div>
                                <div class="fw-bold text-dark">${tglJadwal}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="modal-nilai-card text-center py-3">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">Mulai</div>
                            <div class="fw-bold text-dark fs-6">${jamMulai}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="modal-nilai-card text-center py-3">
                            <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">Selesai</div>
                            <div class="fw-bold text-dark fs-6">${jamSelesai}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="modal-nilai-card text-center py-3 border-success border-opacity-25 bg-success bg-opacity-10">
                            <div class="text-success small fw-bold text-uppercase mb-1" style="font-size: 0.6rem; letter-spacing: 0.5px;">Lama</div>
                            <div class="fw-bold text-success fs-6">${durasi}</div>
                        </div>
                    </div>
                </div>

                <!-- Score Breakdown Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 py-3 border-0 text-secondary fw-bold text-uppercase" style="font-size: 0.7rem;">Tipe Soal</th>
                                    <th class="text-center py-3 border-0 text-secondary fw-bold text-uppercase" style="font-size: 0.7rem;">Jml</th>
                                    <th class="text-center py-3 border-0 text-secondary fw-bold text-uppercase" style="font-size: 0.7rem;">Benar</th>
                                    <th class="text-center pe-3 py-3 border-0 text-secondary fw-bold text-uppercase" style="font-size: 0.7rem;">Skor</th>
                                </tr>
                            </thead>
                            <tbody>${typeRows}</tbody>
                            <tfoot class="bg-primary bg-opacity-10">
                                <tr>
                                    <td colspan="3" class="ps-3 py-3 fw-bold text-primary">TOTAL NILAI AKHIR</td>
                                    <td class="text-center pe-3 py-3 fw-bold text-primary fs-5">${scoreVal}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                ${j.hasil_tampil === '1' && dikoreksi ? '' : `
                    <div class="alert alert-info border-0 shadow-none rounded-4 mt-3 mb-0 d-flex p-3">
                        <i class="bi bi-info-circle-fill me-3 fs-4 text-info"></i>
                        <span class="small py-1">Beberapa tipe soal sedang dalam proses koreksi manual oleh guru pengampu.</span>
                    </div>
                `}

                <!-- Remedial Action -->
                ${j.enable_remedial === '1' && scoreVal < (parseFloat(j.kkm) || 75) ? `
                    <div class="mt-4 pt-3 border-top">
                        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 rounded-4 mb-3 p-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-circle-fill me-3 fs-3 text-warning"></i>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Kesempatan Remedial!</h6>
                                    <p class="small text-muted mb-0">Nilai Anda belum mencapai KKM. Ingin memperbaikinya sekarang?</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-warning w-100 py-3 rounded-pill fw-bold" onclick="mulaiRemedialInline(${j.id_jadwal})">
                            <i class="bi bi-arrow-repeat me-2"></i> MULAI REMEDIAL
                        </button>
                    </div>
                ` : ''}
            </div>
        `;

        document.getElementById('modal-body').innerHTML = html;
        
        // Timeout to animate stroke dashoffset after DOM load
        setTimeout(() => {
            const circle = document.getElementById('scoreCircleFill');
            if(circle) {
                circle.style.strokeDashoffset = offset;
            }
        }, 100);
    }

    async function mulaiRemedialInline(idJadwal) {
        // Cek dulu batas remedial sebelum menawarkan sesi
        try {
            const cek = await $.post(base_url + 'cbt/cek_remedial', { jadwal: idJadwal });
            if (cek && cek.remedial_exhausted) {
                Swal.fire('Batas Tercapai', cek.message || 'Batas maksimal remedial telah tercapai.', 'warning');
                return;
            }
        } catch (e) { /* lanjutkan ke konfirmasi */ }

        // Konfirmasi bertingkat (2x) agar siswa tidak menekan tombol secara tidak sengaja
        Swal.fire({
            title: 'Mulai Remedial?',
            html: 'Anda akan memulai <b>sesi remedial</b> untuk memperbaiki jawaban yang <b>salah</b>.<br>' +
                  'Soal yang sudah <b>benar akan terkunci</b> dan tidak dapat diubah.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Lanjut',
            cancelButtonText: 'Batal'
        }).then((step1) => {
            if (!step1.isConfirmed) return;

            Swal.fire({
                title: 'Konfirmasi Akhir',
                text: 'Setelah sesi remedial dimulai, Anda tidak dapat membatalkan dan soal yang benar akan tetap terkunci. Yakin ingin melanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Mulai Remedial',
                cancelButtonText: 'Tidak'
            }).then(async (step2) => {
                if (!step2.isConfirmed) return;

                // Show loading
                Swal.fire({
                    title: 'Menyiapkan Sesi...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const res = await $.post(base_url + 'cbt/mulai_remedial', { jadwal: idJadwal });
                    if (res.status) {
                        location.href = res.redirect;
                    } else {
                        Swal.fire('Gagal!', res.message || 'Gagal memulai remedial', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                }
            });
        });
    }

</script>
<?= $this->endSection() ?>