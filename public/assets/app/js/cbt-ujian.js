var elem = document.documentElement;
const raguStates = {};
history.pushState(null, null, window.location.href);
window.addEventListener('popstate', function (event) {
    loadSoalNomor(1);
});
let infoBank;
let nomorSoal = 0;
// idSoal/jenisSoal hanya diisi di setKonten(). Nilai default WAJIB bisa
// dideteksi sebagai "belum ada soal" supaya request yang tidak valid
// tidak pernah dikirim ke server (dulu menghasilkan id_soal="undefined" → 422).
let idSoal = 0, idSoalSiswa = 0, jenisSoal = '', modelSoal, typeSoal;
let tipeSoalAktif = 'pg'; // pg | pgk | pgkk (dari cbt_soal.tipe_soal)
let jawabanSiswa, jawabanBaru = null, jsonJawaban;
let nav = 0;
let soalTerjawab = 0, soalTotal = 0;
let timerOut;
let timerSelesai;
let statusCheckInterval;
let elapsed = '0';
let jarak = 0;
let h, m, s;

let tick = 0;
const _second = 1000,
    _minute = _second * 60,
    _hour = _minute * 60,
    _day = _hour * 24;
let durasiUjian;
let dif;
let fieldLinks;
let zoomClicked = 1;
var arrSize = [];
let trying = 0;
const soalCache = {};

/**
 * True hanya jika ada soal yang benar-benar sudah dimuat.
 * Dipakai sebagai guard sebelum menyimpan jawaban / toggling ragu.
 */
function soalAktifValid() {
    const id = parseInt(idSoal, 10);
    return Number.isFinite(id) && id > 0 && jenisSoal !== '' && jenisSoal !== null && jenisSoal !== undefined;
}
const warningSound = new Audio(base_url + 'assets/sounds/warning.ogg');
const successSound = new Audio(base_url + 'assets/sounds/success.ogg');

// ============ FULLSCREEN UTILITIES ============


function closeFullscreen() {
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    }
}

function isExamBrowserEnv() {
    return !!(
        window.isExamBrowser ||
        window.TKA_EXAM_BROWSER ||
        window.EXAM_BROWSER ||
        window.exambrowser ||
        window.Exambro ||
        window.Android ||
        (typeof navigator !== 'undefined' && navigator.userAgent && (
            navigator.userAgent.indexOf('TKAExamBrowser') !== -1 ||
            navigator.userAgent.indexOf('TKA-Exam') !== -1 ||
            navigator.userAgent.indexOf('ExamBrowser') !== -1 ||
            navigator.userAgent.indexOf('Exambro') !== -1 ||
            navigator.userAgent.indexOf('CandyExam') !== -1 ||
            navigator.userAgent.indexOf('CandyExambro') !== -1 ||
            navigator.userAgent.indexOf('ExambroClient') !== -1 ||
            navigator.userAgent.indexOf('Electron') !== -1
        ))
    );
}

function isFullscreen() {
    if (isExamBrowserEnv()) return true;
    return !!(document.fullscreenElement
        || document.webkitFullscreenElement
        || document.mozFullScreenElement
        || document.msFullscreenElement);
}

// Auto-enforce fullscreen when user exits it
document.addEventListener('fullscreenchange', handleFullscreenChange);
document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
document.addEventListener('mozfullscreenchange', handleFullscreenChange);
document.addEventListener('MSFullscreenChange', handleFullscreenChange);

function handleFullscreenChange() {
    if (isExamBrowserEnv()) return;
    // Always try to stay in fullscreen during exam
    if (!isFullscreen() && document.getElementById('approved') && !document.getElementById('approved').classList.contains('d-none')) {
        // Show warning and re-request fullscreen after delay
        console.log('Fullscreen exited, re-requesting...');
        setTimeout(() => {
            openFullscreen();
        }, 300);
    }
}

// Try to enter fullscreen on first user interaction
let fullscreenRequested = false;
function tryEnterFullscreen() {
    if (isExamBrowserEnv()) return;
    if (!fullscreenRequested && !isFullscreen()) {
        openFullscreen();
        fullscreenRequested = true;
    }
}

// Listen for user gestures to trigger fullscreen (required by browsers)
document.addEventListener('click', function onFirstClick() {
    tryEnterFullscreen();
    document.removeEventListener('click', onFirstClick);
}, { once: true });

document.addEventListener('keydown', function onFirstKey() {
    tryEnterFullscreen();
    document.removeEventListener('keydown', onFirstKey);
}, { once: true });
// ============ END FULLSCREEN UTILITIES ============

/**
 * Show dynamic error card with customizable content
 * @param {string} type - 'timeout' | 'fullscreen' | 'error' | 'blocked'
 * @param {string} message - Custom message to display
 * @param {boolean} showBackButton - Whether to show back button
 */
function showErrorCard(type = 'error', message = '', showBackButton = true) {
    document.getElementById('loading').classList.add('d-none');
    document.getElementById('approved').classList.add('d-none');
    document.getElementById('noapproved').classList.remove('d-none');

    const errIcon = document.getElementById('err-icon');
    const errTitle = document.getElementById('err-title');
    const errMsg = document.getElementById('err-msg');
    const errBadge = document.getElementById('err-badge');
    const errActions = document.getElementById('err-actions');

    // Remove existing color classes from title
    errTitle.classList.remove('text-dark', 'text-warning', 'text-danger', 'text-success');

    switch (type) {
        case 'timeout':
            errIcon.innerHTML = '⏱';
            errTitle.innerHTML = 'Waktu Habis!';
            errTitle.classList.add('text-dark');
            errMsg.innerHTML = message || 'Waktu pengerjaan ujian telah selesai';
            errBadge.className = 'd-inline-flex align-items-center gap-2 bg-success bg-opacity-10 text-success rounded-pill px-4 py-2';
            errBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i><span class="fw-semibold">Jawaban telah disimpan</span>';
            break;

        case 'fullscreen':
            errIcon.innerHTML = '🖥️';
            errTitle.innerHTML = 'Mode Layar Penuh Diperlukan';
            errTitle.classList.add('text-warning');
            errMsg.innerHTML = message || '<p class="text-danger">Tidak bisa dilanjutkan. Pastikan aplikasi berjalan dengan kondisi FullScreen!</p>';
            errBadge.className = 'd-inline-flex align-items-center gap-2 bg-warning bg-opacity-10 text-warning rounded-pill px-4 py-2';
            errBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i><span class="fw-semibold">Mode fullscreen diperlukan</span>';
            errActions.innerHTML = `
                <button class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold me-2" onclick="openFullscreen(); setTimeout(() => location.reload(), 500);">
                    <i class="bi bi-fullscreen me-2"></i> Layar Penuh
                </button>
                <a href="${base_url}cbtsiswa/cbt" class="btn btn-outline-secondary btn-lg rounded-pill px-4 py-3 fw-bold">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>
            `;
            return; // Don't override actions

        case 'error':
        default:
            errIcon.innerHTML = '⚠️';
            errTitle.innerHTML = 'Terjadi Kesalahan';
            errTitle.classList.add('text-danger');
            errMsg.innerHTML = message || 'Terjadi kesalahan saat memproses permintaan';
            errBadge.className = 'd-inline-flex align-items-center gap-2 bg-danger bg-opacity-10 text-danger rounded-pill px-4 py-2';
            errBadge.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i><span class="fw-semibold">Silakan hubungi pengawas</span>';
            break;
    }

    // Update actions based on showBackButton
    if (showBackButton) {
        errActions.innerHTML = `
            <a href="${base_url}cbtsiswa/cbt" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        `;
    }
}

async function ajaxPost(url, formData) {
    try {
        const response = await fetch(base_url + url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) {
            // Log the actual response body for debugging
            const errorText = await response.text();
            console.error('HTTP Error ' + response.status + ' Response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}, body: ${errorText.substring(0, 200)}`);
        }
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (err) {
            console.error('Server responded with non-JSON:', text);
            throw new Error('Server Error (HTML Response)');
        }
    } catch (e) {
        // Check if it's a network error
        if (!navigator.onLine || e.message.includes('NetworkError') || e.message.includes('Failed to fetch')) {
            const offlineOverlay = document.getElementById('offline-overlay');
            if (offlineOverlay) offlineOverlay.classList.remove('d-none');
        }
        throw e;
    }
}

async function ajaxGet(url) {
    try {
        const response = await fetch(base_url + url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (e) {
        // Check if it's a network error
        if (!navigator.onLine || e.message.includes('NetworkError') || e.message.includes('Failed to fetch')) {
            const offlineOverlay = document.getElementById('offline-overlay');
            if (offlineOverlay) offlineOverlay.classList.remove('d-none');
        }
        throw e;
    }
}

// ============ INFO SOAL MODAL ============
window.toggleInfo = function() {
    // Determine info based on jenisSoal (1: PG, 2: PGK, 3: Menjodohkan, 4: Isian Singkat, 5: Uraian, 6: Urutan, 7: Rumpang)
    let title = "Informasi Soal";
    let icon = "bi-info-circle-fill";
    let color = "#2563eb";
    let content = "";
    
    switch (jenisSoal) {
        case "1":
            title = "Pilihan Ganda";
            icon = "bi-ui-radios";
            content = `
                <div class="info-step"><div class="info-step-num">1</div><div>Pilih **satu** jawaban yang paling tepat di antara pilihan yang tersedia.</div></div>
                <div class="info-step"><div class="info-step-num">2</div><div>Klik pada teks opsi atau tombol radio untuk memilih jawaban.</div></div>
                <div class="info-step"><div class="info-step-num">3</div><div>Jawaban akan langsung tersimpan secara otomatis.</div></div>`;
            break;
        case "2":
            if (tipeSoalAktif === 'pgkk') {
                title = "PG Kompleks Benar/Salah (PGKK)";
                icon = "bi-ui-checks-grid";
                color = "#0d9488";
                content = `
                    <div class="info-step"><div class="info-step-num">1</div><div>Untuk setiap pernyataan, tentukan apakah pernyataan tersebut **Benar** atau **Salah**.</div></div>
                    <div class="info-step"><div class="info-step-num">2</div><div>Klik pilihan Benar atau Salah pada setiap pernyataan.</div></div>
                    <div class="info-step"><div class="info-step-num">3</div><div>Jawaban tersimpan otomatis. Pastikan semua pernyataan telah dinilai.</div></div>`;
                break;
            }
            title = "Pilihan Ganda Kompleks";
            icon = "bi-ui-checks";
            color = "#16a34a";
            content = `
                <div class="info-step"><div class="info-step-num">1</div><div>Pilih **lebih dari satu** jawaban yang dianggap benar.</div></div>
                <div class="info-step"><div class="info-step-num">2</div><div>Klik pada kotak centang (checkbox) untuk memilih jawaban.</div></div>
                <div class="info-step"><div class="info-step-num">3</div><div>Jika mencapai batas maksimal pilihan, Anda harus membatalkan (uncheck) satu pilihan sebelum memilih yang lain.</div></div>`;
            break;
        case "3":
            title = "Menjodohkan (Matching)";
            icon = "bi-bezier2";
            color = "#9333ea";
            content = `
                <div class="info-step"><div class="info-step-num">1</div><div>Pasangkan pernyataan di sisi kiri dengan jawaban yang sesuai di sisi kanan.</div></div>
                <div class="info-step"><div class="info-step-num">2</div><div>Klik salah satu pernyataan di sisi kiri, kemudian klik jawaban di sisi kanan untuk membuat garis penghubung.</div></div>
                <div class="info-step"><div class="info-step-num">3</div><div>Untuk membatalkan pasangan, klik garis penghubung, atau klik tombol "Hapus Jawaban" jika tersedia.</div></div>`;
            break;
        case "4":
            title = "Isian Singkat";
            icon = "bi-input-cursor-text";
            color = "#d97706";
            content = `
                <div class="info-step"><div class="info-step-num">1</div><div>Ketik jawaban singkat Anda pada kolom isian yang tersedia.</div></div>
                <div class="info-step"><div class="info-step-num">2</div><div>Perhatikan ejaan dan aturan penulisan yang diminta.</div></div>
                <div class="info-step"><div class="info-step-num">3</div><div>Jawaban otomatis tersimpan setelah Anda selesai mengetik (debounce).</div></div>`;
            break;
        case "5":
            title = "Uraian / Esai";
            icon = "bi-card-text";
            color = "#0891b2";
            content = `
                <div class="info-step"><div class="info-step-num">1</div><div>Tuliskan jawaban Anda secara rinci pada editor teks yang disediakan.</div></div>
                <div class="info-step"><div class="info-step-num">2</div><div>Gunakan fitur formatting editor (Bold, Italic, List) jika diperlukan untuk memperjelas jawaban.</div></div>
                <div class="info-step"><div class="info-step-num">3</div><div>Pastikan Anda membaca ulang jawaban sebelum berpindah soal.</div></div>`;
            break;
        case "6":
            title = "Mengurutkan (Ordering)";
            icon = "bi-sort-down";
            color = "#be123c";
            content = `
                <div class="info-step"><div class="info-step-num">1</div><div>Pindahkan item ke atas atau ke bawah untuk membentuk urutan yang benar.</div></div>
                <div class="info-step"><div class="info-step-num">2</div><div>Anda dapat melakukan "Drag & Drop" (tahan lalu geser) pada item.</div></div>
                <div class="info-step"><div class="info-step-num">3</div><div>Atau gunakan tombol <b>↑</b> (Naik) dan <b>↓</b> (Turun) pada setiap item.</div></div>`;
            break;
        case "7":
            title = "Isian Rumpang (Cloze Test)";
            icon = "bi-view-list";
            color = "#0f766e";
            content = `
                <div class="info-step"><div class="info-step-num">1</div><div>Lengkapi bagian kalimat yang rumpang/kosong.</div></div>
                <div class="info-step"><div class="info-step-num">2</div><div>Ketik jawaban atau pilih dari opsi dropdown yang disediakan di dalam kalimat.</div></div>
                <div class="info-step"><div class="info-step-num">3</div><div>Pastikan semua bagian kosong telah terisi dengan benar.</div></div>`;
            break;
        default:
            title = "Keterangan Soal";
            content = `<p class="text-center text-muted">Arahkan kursor Anda ke soal atau opsi jawaban dan pilih jawaban yang dirasa paling tepat.</p>`;
    }

    // Periksa apakah ada info spesifik tambahan dari backend (jika ada)
    const pnl = document.getElementById('examketerangancontent');
    if (pnl && pnl.innerHTML.trim() !== "") {
        content += `<hr><div class="mt-3"><strong>Info Tambahan:</strong><br>${pnl.innerHTML}</div>`;
    }

    // Modal Builder HTML
    const modalHtml = `
    <div class="custom-info-modal-backdrop" id="customInfoModalBackdrop" onclick="if(event.target === this) document.getElementById('customInfoModal').remove(); this.remove();">
        <div class="custom-info-modal" id="customInfoModal" onclick="event.stopPropagation()">
            <!-- Custom Styles Injected Inline -->
            <style>
                .custom-info-modal-backdrop {
                    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                    background: rgba(0,0,0,0.6);
                    backdrop-filter: blur(5px);
                    z-index: 1060;
                    display: flex; align-items: center; justify-content: center;
                    animation: fadeIn 0.2s ease-out forwards;
                }
                .custom-info-modal {
                    background: #fff;
                    width: 90%; max-width: 450px;
                    border-radius: 1.25rem;
                    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
                    overflow: hidden;
                    transform: scale(0.9);
                    opacity: 0;
                    animation: modalPopUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
                }
                .custom-info-header {
                    padding: 1.5rem 1.5rem 1rem;
                    border-bottom: 1px solid #f1f5f9;
                    display: flex; align-items: center; justify-content: space-between;
                }
                .custom-info-icon-wrapper {
                    width: 48px; height: 48px;
                    border-radius: 50%;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 1.5rem;
                    color: white;
                    background: ${color};
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
                }
                .custom-info-body {
                    padding: 1.5rem;
                    background: #f8fafc;
                }
                .info-step {
                    display: flex;
                    align-items: flex-start;
                    margin-bottom: 1rem;
                    color: #475569;
                    font-size: 0.95rem;
                    line-height: 1.5;
                }
                .info-step:last-child { margin-bottom: 0; }
                .info-step-num {
                    flex-shrink: 0;
                    width: 24px; height: 24px;
                    border-radius: 50%;
                    background: ${color}20; /* 20% opacity */
                    color: ${color};
                    display: flex; align-items: center; justify-content: center;
                    font-weight: 700;
                    font-size: 0.8rem;
                    margin-right: 12px;
                    margin-top: 2px;
                }
                .custom-info-footer {
                    padding: 1rem 1.5rem;
                    background: #fff;
                    border-top: 1px solid #f1f5f9;
                    text-align: center;
                }
                .btn-custom-close {
                    background: ${color};
                    color: white;
                    border: none;
                    border-radius: 9999px;
                    padding: 0.6rem 2rem;
                    font-weight: 600;
                    transition: all 0.2s;
                }
                .btn-custom-close:hover {
                    filter: brightness(0.9);
                    transform: translateY(-1px);
                }
                @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
                @keyframes modalPopUp { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
            </style>

            <div class="custom-info-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="custom-info-icon-wrapper">
                        <i class="bi ${icon}"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: #1e293b;">${title}</h5>
                        <div class="small text-muted fw-medium mt-1">Panduan Mengerjakan</div>
                    </div>
                </div>
                <button type="button" class="btn-close" style="align-self: flex-start;" onclick="document.getElementById('customInfoModalBackdrop').remove()"></button>
            </div>
            
            <div class="custom-info-body">
                ${content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')}
            </div>
            
            <div class="custom-info-footer">
                <button type="button" class="btn-custom-close" onclick="document.getElementById('customInfoModalBackdrop').remove()">Mengerti</button>
            </div>
        </div>
    </div>`;

    // Remove existing if any
    const existingModal = document.getElementById('customInfoModalBackdrop');
    if (existingModal) existingModal.remove();

    // Inject to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
};

document.addEventListener("DOMContentLoaded", async function () {
    document.addEventListener("keydown", function (event) {
        var charCode = event.charCode || event.keyCode || event.which;
        if (charCode == 27 || charCode == 91 || charCode == 92) {
            return false;
        }
    });

    // Connection Status Handler
    // Connection Status Handler & Auto-Reconnect
    // Connection Status Handler & Auto-Reconnect
    let reconnectInterval;

    async function checkServerReachable() {
        try {
            // Ping current page with random param to avoid cache
            // Use GET instead of HEAD for better compatibility
            // Adding a timestamp ensures the browser doesn't just return a cached "200 OK"
            const pingUrl = window.location.href.split('#')[0] + (window.location.href.includes('?') ? '&' : '?') + '_ping=' + new Date().getTime();

            const response = await fetch(pingUrl, {
                method: 'GET',
                cache: 'no-store',
                headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
            });

            if (!response.ok) return false;

            // Check content for PHP errors (DB down scenario)
            const text = await response.text();
            if (text.includes('mysqli_sql_exception') ||
                text.includes('Fatal error') ||
                text.includes('A Database Error Occurred') ||
                text.includes('refused it')) {
                return false;
            }

            return true;
        } catch (e) {
            console.error('Ping failed:', e);
            return false;
        }
    }

    function handleDisconnect() {
        const offlineOverlay = document.getElementById('offline-overlay');
        if (offlineOverlay) offlineOverlay.classList.remove('d-none');

        if (!reconnectInterval) {
            console.log('Connection lost. Starting reconnect timer...');
            reconnectInterval = setInterval(async () => {
                // Force check server regardless of navigator status
                const isReachable = await checkServerReachable();
                if (isReachable) {
                    handleReconnect();
                } else {
                    console.log('Server still unreachable...');
                }
            }, 3000); // Check every 3 seconds
        }
    }

    function handleReconnect() {
        const offlineOverlay = document.getElementById('offline-overlay');
        if (offlineOverlay) offlineOverlay.classList.add('d-none');

        if (reconnectInterval) {
            clearInterval(reconnectInterval);
            reconnectInterval = null;
        }

        // Resume actions
        let currentNomor = document.getElementById('nomor-soal');
        if (currentNomor) {
            const nav = currentNomor.textContent;
            showSuccessToast('Koneksi tersambung kembali!');
            setTimeout(() => {
                loadSoalNomor(nav);
            }, 500);
        }
        console.log('Reconnected successfully!');
    }

    window.cbtManualReconnect = async function() {
        showToast('Memeriksa koneksi ke server...', 'info');
        const isReachable = await checkServerReachable();
        if (isReachable) {
            handleReconnect();
        } else {
            showToast('Server belum dapat dijangkau. Coba beberapa saat lagi.', 'warning');
        }
    };

    // Initial check
    checkServerReachable().then(isOnline => {
        if (!isOnline) handleDisconnect();
    });

    window.addEventListener('online', async () => {
        const isReachable = await checkServerReachable();
        if (isReachable) handleReconnect();
        else handleDisconnect();
    });

    window.addEventListener('offline', handleDisconnect);

    document.onmousedown = rtclickcheck;

    // Violation Detection: Window Blur (Tab Switch / Minimized)
    window.addEventListener('blur', function () {
        if (typeof pelanggaran !== 'undefined' && pelanggaran === '1') {
            hitungPelanggaran('ringan', '2');
        }
    });

    // Violation Detection: Visibility Change
    document.addEventListener("visibilitychange", function () {
        if (document.hidden && typeof pelanggaran !== 'undefined' && pelanggaran === '1') {
            hitungPelanggaran('ringan', '2');
        }
    });

    document.getElementById('plus').addEventListener('click', function () {
        document.getElementById('box-soal').style.fontSize = '16pt';
        document.querySelectorAll('.container-jawaban').forEach(el => el.style.fontSize = '16pt');
        document.querySelectorAll('.font-resizer-btn').forEach(el => el.classList.remove('active'));
        this.classList.add('active');
    });

    document.getElementById('normal').addEventListener('click', function () {
        document.getElementById('box-soal').style.fontSize = '14pt';
        document.querySelectorAll('.container-jawaban').forEach(el => el.style.fontSize = '12pt');
        document.querySelectorAll('.font-resizer-btn').forEach(el => el.classList.remove('active'));
        this.classList.add('active');
    });

    document.getElementById('minus').addEventListener('click', function () {
        document.getElementById('box-soal').style.fontSize = '12pt';
        document.querySelectorAll('.container-jawaban').forEach(el => el.style.fontSize = '10pt');
        document.querySelectorAll('.font-resizer-btn').forEach(el => el.classList.remove('active'));
        this.classList.add('active');
    });

    document.getElementById('jawab').addEventListener('submit', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        // ── GUARD: soal belum/gagal dimuat ─────────────────────────────────
        // idSoal & jenisSoal masih undefined → jangan kirim request apa pun
        // (dulu terkirim data[id_soal]="undefined" → server balas 422 dan
        //  siswa terjebak karena navigasi terhenti).
        if (!soalAktifValid()) {
            console.warn('submit: dilewati, belum ada soal aktif (idSoal=' + idSoal + ')');
            if (nav > 0) {
                await loadSoalNomor(nav);
            }
            return;
        }

        let formData = new FormData(this);
        formData.append('timer', document.getElementById('timer').innerText);

        // Selalu tambahkan id_soal dan jenis soal ke form data
        formData.append('data[id_soal]', idSoal);
        formData.append('data[jenis]', jenisSoal);

        // Auto-populate jsonJawaban if it's empty (e.g. user navigating without clicking an option)
        if (!jsonJawaban || Object.keys(jsonJawaban).length === 0) {
            submitJawaban(); 
        }

        if (jsonJawaban) {
            const jns = jsonJawaban['jenis']
            for (const key in jsonJawaban) {
                if ((jns === '2' || jns === '3') && key === 'jawaban_siswa') {
                    formData.append('data[' + key + ']', JSON.stringify(jsonJawaban[key]))
                } else {
                    formData.append('data[' + key + ']', jsonJawaban[key])
                }
            }
        }

        try {
            // Check if remedial mode is active via global instance
            const isRemedial = typeof cbtRemedial !== 'undefined' && cbtRemedial.isRemedialMode;
            const targetUrl = isRemedial ? 'cbtsiswa/save_jawaban_remedial' : 'cbtsiswa/savejawaban';


            // If remedial, ensure we send necessary params
            if (isRemedial) {
                formData.append('id_soal', idSoal);
            }

            const response = await ajaxPost(targetUrl, formData)
            console.log('onSubmit', response)

            if (response.status === false) {
                showToast(response.message || "Gagal menyimpan jawaban", 'error');
                if (response.expired) {
                    setTimeout(() => window.location.href = base_url + "cbtsiswa/cbt", 1500);
                }
                return;
            }

            if (jsonJawaban && jsonJawaban.jawaban_siswa !== undefined) {
                if (soalCache[nomorSoal]) {
                    soalCache[nomorSoal].soal_jawaban_siswa = jsonJawaban.jawaban_siswa;
                    if (jsonJawaban.jawaban_alias !== undefined) {
                        soalCache[nomorSoal].soal_jawaban_alias = jsonJawaban.jawaban_alias;
                    }
                }
            }

            soalTerjawab = response.soal_terjawab;
            await loadSoalNomor(nav);
        } catch (e) {
            showDangerToast('ERROR!');
            console.log(e);
        }
    });

    // Zoom Slider (Vanilla JS)
    if (typeof initContentZoomSlider === 'function') {
        initContentZoomSlider(".konten-soal-jawab", {
            toolContainer: ".zoom-tool-bar",
            step: 5,
            min: 50,
            max: 200,
            zoom: 100,
        });
    }

    await validasiJadwal();
});

function updateModalState() {
    for (const [key, data] of Object.entries(soalCache)) {
        if (!data) continue;
        let isAnswered = false;
        let jwb = data.soal_jawaban_siswa;

        // Handle stored object wrapper if present
        if (jwb && jwb.jawaban) jwb = jwb.jawaban;
        // Handle JSON string wrapper if present
        if (typeof jwb === 'string') {
            try {
                const parsed = JSON.parse(jwb);
                // Check if it was valid JSON and use it
                if (parsed) jwb = parsed;
            } catch (e) { }
        }

        if (jwb) {
            if (Array.isArray(jwb)) {
                isAnswered = jwb.length > 0;
            } else {
                isAnswered = jwb !== '';
            }
        }

        const boxEl = document.getElementById('box' + key);

        if (boxEl) {
            // 1. Cleanup all state classes
            boxEl.classList.remove(
                'btn-outline-secondary',
                'btn-primary',
                'btn-success',
                'btn-warning',
                'text-white',
                'text-dark',
                'border-primary',
                'border-success',
                'border-warning',
                'selected' // remove old selected class if present
            );

            // 2. Determine State
            // nomorSoal is global int, key is string/int
            const isActive = (parseInt(key) === parseInt(nomorSoal));
            const isRagu = data.soal_ragu && (data.soal_ragu == '1' || data.soal_ragu === true);

            // 3. Apply Classes based on priority: Active > Ragu > Answered > Default
            if (isActive) {
                boxEl.classList.add('btn-primary', 'text-white', 'border-primary');
            } else if (isRagu) {
                boxEl.classList.add('btn-warning', 'text-dark', 'border-warning');
            } else if (isAnswered) {
                boxEl.classList.add('btn-success', 'text-white', 'border-success');
            } else {
                boxEl.classList.add('btn-outline-secondary');
            }

            // 4. Handle Badge Logic (Only if Answered)
            let badgeContent = '';
            if (isAnswered) {
                if (data.soal_jawaban_alias) {
                    badgeContent = data.soal_jawaban_alias;
                } else {
                    // Fallback logic: Try to use student answer if it's a letter A-E, otherwise checkmark
                    const val = (typeof data.soal_jawaban_siswa === 'string') ? data.soal_jawaban_siswa.toUpperCase() : '';
                    if (data.soal_jenis === '1' && /^[A-E]$/.test(val)) {
                        badgeContent = val;
                    } else {
                        badgeContent = '<i class="bi bi-check-lg"></i>';
                    }
                }
            }

            if (badgeContent) {
                const badgeId = 'badge-status-' + key;
                let badgeEl = document.getElementById(badgeId);
                if (!badgeEl) badgeEl = document.getElementById('badge' + key); // fallback

                if (!badgeEl) {
                    badgeEl = document.createElement('span');
                    badgeEl.id = badgeId;
                    badgeEl.className = 'position-absolute top-0 start-100 translate-middle badge rounded-circle bg-dark d-flex align-items-center justify-content-center';
                    badgeEl.style.cssText = 'width: 20px; height: 20px; font-size: 0.65rem; padding: 0; border: 2px solid #fff;';
                    badgeEl.innerHTML = badgeContent;
                    boxEl.appendChild(badgeEl);
                } else {
                    badgeEl.innerHTML = badgeContent;
                    // Ensure badge style is correct if using fallback legacy badge
                    badgeEl.className = 'position-absolute top-0 start-100 translate-middle badge rounded-circle bg-dark d-flex align-items-center justify-content-center';
                    badgeEl.style.cssText = 'width: 20px; height: 20px; font-size: 0.65rem; padding: 0; border: 2px solid #fff;';
                }
            } else {
                // If not answered (or answer cleared), remove badge if exists
                const badgeId = 'badge-status-' + key;
                let badgeEl = document.getElementById(badgeId);
                if (!badgeEl) badgeEl = document.getElementById('badge' + key);
                if (badgeEl) badgeEl.remove();
            }
        }
    }
}

function deteksiUkuranLayar(isOrientationchange) {
    if (pelanggaran != '1') {
        return;
    }

    const toleransi = 10;
    const sWidth = screen.width - toleransi;
    const sHeight = screen.height - toleransi;
    const iWidth = window.innerWidth;
    const iHeight = window.innerHeight;
    if (!isOrientationchange && iWidth < sWidth) {
        hitungPelanggaran('ringan', '2')
    }
}

function checkDialog(isOk) {
    if (isOk) {
        // Show exam header and footer after user confirms
        const examHeader = document.getElementById('exam-header');
        if (examHeader) {
            examHeader.classList.remove('d-none');
        }
        const examFooter = document.getElementById('exam-footer');
        if (examFooter) {
            examFooter.classList.remove('d-none');
        }

        openFullscreen();
        setTimeout(function () {
            const idJadwal = document.querySelector('input[name="jadwal"]')?.value || '0';
            const idSiswa = document.querySelector('input[name="siswa"]')?.value || '0';
            const lastNo = localStorage.getItem('cbt_last_no_' + idJadwal + '_' + idSiswa) || localStorage.getItem('cbt_last_no_' + idJadwal) || 1;
            loadSoalNomor(lastNo);
        }, 500);
    } else {
        trying++;
        openDialog()
    }
}

function openDialog() {
    var myModal = new bootstrap.Modal(document.getElementById('modalRules'));
    myModal.show();

    $('#btnAgreeRules').off('click').on('click', function () {
        myModal.hide();
        checkDialog(true);
    });
}

function BlokirKeluar() {
    showInfoModal("MAAF!", "Anda tidak diijinkan <b>logout</b>. Selesaikan semua soal lalu klik tombol Selesai.", "warning");
    openFullscreen();
}


function startStatusCheck() {
    if (statusCheckInterval) clearInterval(statusCheckInterval);

    console.log('Starting background status check...');
    statusCheckInterval = setInterval(async () => {
        // Only run check if exam is currently active/visible
        if (document.getElementById('approved') && !document.getElementById('approved').classList.contains('d-none')) {
            try {
                // Background check using current question endpoint
                // We use FormData to satisfy required fields if any, but main goal is status check
                let bgForm = new FormData(document.getElementById('up'));
                bgForm.append('nomor', nomorSoal);
                const timerEl = document.getElementById('timer');
                if (timerEl) bgForm.append('timer', timerEl.innerText);

                const response = await fetch(base_url + 'cbtsiswa/loadnomorsoal', {
                    method: 'POST',
                    body: bgForm
                });

                const data = await response.json();

                if (data) {
                    if (data.error === 'blocked') {
                        $('#blockedMsg').html(data.message + "<br><br><b>Silahkan hubungi pengawas untuk membuka blokir.</b>");
                        new bootstrap.Modal(document.getElementById('modalBlocked')).show();
                        clearInterval(statusCheckInterval);
                    } else if (data.error === 'finished') {
                        showInfoModal("UJIAN SELESAI", data.message, "warning");
                        setTimeout(() => { window.location.href = base_url + "cbtsiswa/cbt"; }, 2000);
                        clearInterval(statusCheckInterval);
                    }
                }
            } catch (e) {
                console.log('Status check failed (network/server error):', e);
            }
        }
    }, 30000); // Check every 30 seconds
}

async function validasiJadwal() {
    let formData = new FormData(document.getElementById('up'));
    const data = await ajaxPost('cbtsiswa/validasi_jadwal', formData)
    console.log('validasi', data);
    document.getElementById('loading').classList.add('d-none');

    // Helper to get last question number from localStorage
    const getLastSoalNo = () => {
        // Jika elapsedWaktuServer == 0, artinya ini baru pertama kali mulai (atau di-reset oleh guru).
        // Wajib mulai dari nomor 1 dan abaikan localStorage yang mungkin tersisa dari ujian sebelumnya.
        if (typeof elapsedWaktuServer !== 'undefined' && elapsedWaktuServer === 0) {
            return 1;
        }

        const idJadwal = document.querySelector('input[name="jadwal"]')?.value || '0';
        const idSiswa = document.querySelector('input[name="siswa"]')?.value || '0';
        const lastNo = localStorage.getItem('cbt_last_no_' + idJadwal + '_' + idSiswa)
            || localStorage.getItem('cbt_last_no_' + idJadwal)
            || 1;

        return parseInt(lastNo) || 1;
    };

    if (data.approved) {
        // Always try to enter fullscreen when exam starts
        openFullscreen();
        startStatusCheck();

        if (pelanggaran != '1') {
            document.getElementById('approved').classList.remove('d-none');
            document.getElementById('noapproved').classList.add('d-none');
            // Resume from last position (localStorage)
            loadSoalNomor(getLastSoalNo());
        } else {
            // Robust Fullscreen Check
            // 1. Check API first (Most reliable) including vendor prefixes
            const isFullscreenApi = document.fullscreenElement !== null
                || document.webkitFullscreenElement !== null
                || document.mozFullScreenElement !== null
                || document.msFullscreenElement !== null;

            // 2. Check Width (Fallback)
            // Increase tolerance to 30px to account for scrollbars
            const toleransi = 30;
            const sWidth = screen.width - toleransi;
            // REMOVED HEIGHT CHECK: Browser UI (tabs/address bar) makes innerHeight < screen.height even when maximized
            const iWidth = window.innerWidth;

            // Check width only
            const isWidthOk = iWidth >= sWidth;

            console.log('Fullscreen Check:', {
                api: isFullscreenApi,
                width: isWidthOk,
                screenW: screen.width,
                innerW: window.innerWidth,
                minW: sWidth
            });

            // STRICT API Check (Ignore Width)
            if (!isFullscreenApi) {
                // If violation checking is enabled and not fullscreen
                await hitungPelanggaran('ringan', '2');
            } else {
                // Fullscreen OK, start exam
                document.getElementById('approved').classList.remove('d-none');
                document.getElementById('noapproved').classList.add('d-none');
                // Resume from last position (localStorage)
                loadSoalNomor(getLastSoalNo());
            }
        }
    } else {
        document.getElementById('loading').classList.add('d-none');
        // Handle Blocked
        if (data.error === 'blocked') {
            $('#blockedMsg').html(data.message);
            var myModal = new bootstrap.Modal(document.getElementById('modalBlocked'));
            myModal.show();
        } else {
            document.getElementById('approved').classList.add('d-none');
            document.getElementById('noapproved').classList.remove('d-none');
            document.getElementById('err-msg').innerHTML = data.message;
        }
    }
}

async function prefetchSoal(nomor) {
    if (nomor > soalTotal || soalCache[nomor]) return;
    try {
        let formData = new FormData(document.getElementById('up'));
        formData.append('nomor', nomor);
        formData.append('timer', document.getElementById('timer').innerText);

        // Silent request
        fetch(base_url + 'cbtsiswa/loadnomorsoal', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(respon => {
                if (respon && respon.approved) {
                    soalCache[nomor] = respon;
                }
            })
            .catch(e => { });

    } catch (e) {
        // Silent fail
    }
}

var isLoadingSoal = false;

async function loadSoalNomor(nomor, force = false) {
    if (isLoadingSoal && !force) {
        return;
    }

    // Lock navigation
    isLoadingSoal = true;

    // Show loading overlay
    document.getElementById('loading').classList.remove('d-none');

    try {
        // STRICT Fullscreen Check inside navigation
        if (pelanggaran == '1') {
            const isFullscreenApi = document.fullscreenElement !== null
                || document.webkitFullscreenElement !== null
                || document.mozFullScreenElement !== null
                || document.msFullscreenElement !== null;

            if (!isFullscreenApi) {
                // Not in API fullscreen mode
                // Try to auto-fix first
                openFullscreen();

                // Double check if openFullscreen worked immediatey (unlikely async) 
                // but we should block the view until the event listener unblocks it
                showErrorCard('fullscreen');
                return;
            }
        }

        if (nomor == nomorSoal && !force) {
            document.getElementById('loading').classList.add('d-none');
            return;
        }

        if (soalTotal === 0 || nomor <= parseInt(soalTotal)) {
            let data;
            let isCached = false;

            if (soalCache[nomor]) {
                data = soalCache[nomor];
                isCached = true;
                document.getElementById('loading').classList.add('d-none');
                console.log('Loaded from cache:', nomor);

                // Background update for realtime monitoring
                let bgForm = new FormData(document.getElementById('up'));
                bgForm.append('nomor', nomor);
                bgForm.append('timer', document.getElementById('timer').innerText);

                fetch(base_url + 'cbtsiswa/loadnomorsoal', {
                    method: 'POST',
                    body: bgForm
                })
                    .then(response => response.json())
                    .then(respon => {
                        if (respon && respon.error === 'finished') {
                            showInfoModal("UJIAN SELESAI", respon.message, "warning");
                            setTimeout(() => { window.location.href = base_url + "cbtsiswa/cbt"; }, 2000);
                        } else if (respon && respon.error === 'blocked') {
                            $('#blockedMsg').html(respon.message + "<br><br><b>Silahkan hubungi pengawas untuk membuka blokir.</b>");
                            new bootstrap.Modal(document.getElementById('modalBlocked')).show();
                        }
                    })
                    .catch(e => console.error(e));

            } else {
                let formData = new FormData(document.getElementById('up'));
                formData.append('nomor', nomor)
                formData.append('timer', document.getElementById('timer').innerText)
                data = await ajaxPost('cbtsiswa/loadnomorsoal', formData)
                if (data && data.approved) {
                    soalCache[nomor] = data;
                }
                console.log('Loaded from server:', data);
                document.getElementById('loading').classList.add('d-none');
            }

            if (data.approved) {
                // Exam content approved
                document.getElementById('approved').classList.remove('d-none');
                document.getElementById('noapproved').classList.add('d-none');
                setKonten(data, isCached);

                // Prefetch next question
                prefetchSoal(nomor + 1);
            } else {
                document.getElementById('approved').classList.add('d-none');
                // Handle Blocked
                if (data.error === 'blocked') {
                    $('#blockedMsg').html(data.message + "<br><br><b>Silahkan hubungi pengawas untuk membuka blokir.</b>");
                    new bootstrap.Modal(document.getElementById('modalBlocked')).show();
                } else if (data.error === 'finished') {
                    showInfoModal("UJIAN SELESAI", data.message, "warning");
                    setTimeout(() => { window.location.href = base_url + "cbtsiswa/cbt"; }, 2000);
                } else {
                    document.getElementById('loading').classList.add('d-none');
                    document.getElementById('noapproved').classList.remove('d-none');
                    document.getElementById('err-msg').innerHTML = data.message + '<br/>' + (data.error || '');
                }
            }
        } else {
            // Must release lock before calling selesai, 
            // but selesai has its own async flows and usually ends the test
            // We set isLoadingSoal to false just in case user cancels finishing.
            isLoadingSoal = false;
            await selesai();
            return;
        }
    } catch (e) {
        document.getElementById('loading').classList.add('d-none');
        showDangerToast('ERROR!');
        console.log(e);
    } finally {
        if (nomor <= parseInt(soalTotal)) {
            isLoadingSoal = false;
        }
    }
}

function loadSoal(datas) {
    const modalEl = document.getElementById('daftarModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.hide();
    } else if (typeof jQuery !== 'undefined') {
        $('#daftarModal').modal('hide'); // Fallback
    }

    // Use dataset for vanilla JS access, handled safely
    nav = datas.dataset.nomorsoal;

    var jwb1 = jawabanSiswa;
    var jwb2 = jawabanBaru;

    // Arrays in JS are objects, need deep comparison or stringify
    if (Array.isArray(jwb1) || (typeof jQuery !== 'undefined' && jwb1 instanceof jQuery)) {
        jwb1 = JSON.stringify(jwb1)
    }
    if (jwb2 != null && (Array.isArray(jwb2) || (typeof jQuery !== 'undefined' && jwb2 instanceof jQuery))) {
        jwb2 = JSON.stringify(jwb2)
    }

    if (jawabanBaru != null && jwb1 !== jwb2) {
        document.getElementById('jawab').requestSubmit();
    } else {
        loadSoalNomor(nav);
    }
}

function setKonten(data, isCached = false) {
    const media = data.media;
    durasiUjian = data.jadwal.durasi_ujian
    jarak = data.jarak;
    idSoal = data.soal_id;
    idSoalSiswa = data.soal_siswa_id;
    nomorSoal = parseInt(data.soal_nomor);
    const idJadwal = document.querySelector('input[name="jadwal"]')?.value || '0';
    const idSiswa = document.querySelector('input[name="siswa"]')?.value || '0';
    localStorage.setItem('cbt_last_no_' + idJadwal + '_' + idSiswa, nomorSoal);
    jenisSoal = data.soal_jenis;
    tipeSoalAktif = data.tipe_soal || 'pg';
    soalTerjawab = data.soal_terjawab;
    soalTotal = data.soal_total;

    jsonJawaban = {};
    jawabanBaru = null;
    jawabanSiswa = data.soal_jawaban_siswa || '';

    // Fix: Parse JSON string if detected (Critical for Type 2 PG Kompleks)
    if (typeof jawabanSiswa === 'string' && (jawabanSiswa.includes('[') || jawabanSiswa.includes('{'))) {
        try {
            const parsed = JSON.parse(jawabanSiswa);
            if (parsed) jawabanSiswa = parsed;
        } catch (e) { }
    }

    if (Array.isArray(jawabanSiswa)) jawabanSiswa.sort();

    // Update Ragu Checkbox State
    const chkRagu = document.getElementById('c_ragu_ragu');
    if (chkRagu) {
        chkRagu.checked = (data.soal_ragu == '1' || data.soal_ragu === true);
    }

    const prevBtn = document.getElementById('prev');
    if (nomorSoal === 1) {
        prevBtn.setAttribute('disabled', 'disabled');
    } else {
        prevBtn.removeAttribute('disabled');
    }

    const nextBtn = document.getElementById('next');
    const textNext = document.getElementById('text-next');
    if (nomorSoal === soalTotal) {
        nextBtn.classList.remove('btn-primary');
        nextBtn.classList.add('btn-success');
        textNext.innerHTML = '<b class="d-none d-md-inline-block me-2">Selesai</b> <i class="bi bi-check-circle"></i>';
    } else {
        nextBtn.removeAttribute('disabled');
        nextBtn.classList.remove('btn-success', 'btn-secondary');
        nextBtn.classList.add('btn-primary');
        textNext.innerHTML = '<b class="d-none d-md-inline-block me-2">Soal Berikutnya</b> <i class="bi bi-chevron-right"></i>';
    }

    document.getElementById('nomor-soal').innerHTML = nomorSoal;
    const kontenSoal = document.getElementById('konten-soal');

    // For type 7 (Cloze), hide the left panel since everything is in the right panel
    if (data.soal_jenis == '7') {
        kontenSoal.innerHTML = '';
        kontenSoal.style.display = 'none';
        kontenSoal.parentElement.style.display = 'none'; // Hide the column
    } else {
        kontenSoal.innerHTML = data.soal_soal;
        kontenSoal.style.display = '';
        kontenSoal.parentElement.style.display = '';
    }
    kontenSoal.setAttribute('data-id', idSoal);



    console.log('id_soal', idSoal)
    var jenis = data.soal_jenis;
    var html = '';
    const kontenJawaban = document.getElementById('konten-jawaban');

    if (jenis == "1") {
        data.soal_opsi.forEach(function (opsis) {
            if (opsis.valAlias != "" && opsis.opsi != "") {
                let isChecked = (jawabanSiswa && opsis.value.toUpperCase() === jawabanSiswa.toUpperCase()) ? 'checked' : '';
                html += '<label class="container-jawaban font-weight-normal">' + opsis.opsi +
                    '<input type="radio"' +
                    ' name="jawaban"' +
                    ' value="' + opsis.value.toUpperCase() + '"' +
                    ' data-jawabansiswa="' + opsis.value.toUpperCase() + '"' +
                    ' data-jawabanalias="' + opsis.valAlias.toUpperCase() + '"' +
                    ' onclick="submitJawaban(this)" ' + isChecked + '>' +
                    '<span class="checkmark shadow text-center align-middle">' + opsis.valAlias.toUpperCase() + '</span>' +
                    '</label>';
            }
        });
        kontenJawaban.innerHTML = html;
    } else if (jenis == "2" && tipeSoalAktif === 'pgkk') {
        // --- PGKK: PILIHAN GANDA KOMPLEKS BENAR/SALAH (format TKA) ---
        // Setiap pernyataan dinilai Benar/Salah; jawaban disimpan sbg objek {huruf:'B'|'S'}.
        const jawabanMap = (jawabanSiswa && typeof jawabanSiswa === 'object' && !Array.isArray(jawabanSiswa))
            ? jawabanSiswa : {};
        data.soal_opsi.forEach(function (opsis) {
            const huruf = (opsis.value || '').toUpperCase();
            const prev = jawabanMap[huruf] || jawabanMap[opsis.value] || '';
            const bSel = (String(prev).toUpperCase() === 'B') ? 'checked' : '';
            const sSel = (String(prev).toUpperCase() === 'S') ? 'checked' : '';
            html += '<div class="pgkk-item mb-2 border rounded p-2">' +
                '<div class="pgkk-statement font-weight-normal mb-1">' + opsis.opsi + '</div>' +
                '<div class="d-flex gap-3 ps-1">' +
                '<label class="mb-0"><input type="radio" name="pgkk_' + huruf + '" value="B" data-pgkk-letter="' + huruf + '" onclick="submitJawaban(this)" ' + bSel + '> <b>Benar</b></label>' +
                '<label class="mb-0"><input type="radio" name="pgkk_' + huruf + '" value="S" data-pgkk-letter="' + huruf + '" onclick="submitJawaban(this)" ' + sSel + '> <b>Salah</b></label>' +
                '</div>' +
                '</div>';
        });
        kontenJawaban.innerHTML = html;
    } else if (jenis == "2") {
        data.soal_opsi.forEach(function (opsis) {
            let isChecked = '';
            if (Array.isArray(jawabanSiswa)) {
                if (jawabanSiswa.includes(opsis.value.toUpperCase())) isChecked = 'checked';
            }
            html += '<label class="container-jawaban font-weight-normal">' + opsis.opsi +
                '<input type="checkbox"' +
                ' name="jawaban"' +
                ' value="' + opsis.value.toUpperCase() + '"' +
                ' data-max="' + (data.soal_opsi && data.soal_opsi.length > 1 ? data.soal_opsi.length - 1 : 1) + '"' +
                ' data-jawabansiswa="' + opsis.value.toUpperCase() + '"' +
                ' onclick="submitJawaban(this)" ' + isChecked + '>' +
                '<span class="boxmark shadow text-center align-middle"></span>' +
                '</label>';
        });
        kontenJawaban.innerHTML = html;
    } else if (jenis == "3") {
        // --- MATCHING LOGIC: Supports List (SVG) and Table (Checkbox/Radio) ---
        kontenJawaban.innerHTML = '';

        let leftItems = [];
        let rightItems = [];

        // Get model and type from data
        const matchModel = data.soal_opsi.model || '1'; // 1=List, 2=Table
        const matchType = data.soal_opsi.type || '2';   // 1=Multiple answers per row, 2=Single answer per row

        // Check if server already provided leftItems/rightItems
        if (data.soal_opsi.leftItems && data.soal_opsi.leftItems.length > 0) {
            leftItems = data.soal_opsi.leftItems;
        }
        if (data.soal_opsi.rightItems && data.soal_opsi.rightItems.length > 0) {
            rightItems = data.soal_opsi.rightItems;
        }

        // Apply remedial UI if active
        if (typeof cbtRemedial !== 'undefined' && cbtRemedial.isRemedialMode) {
            setTimeout(() => cbtRemedial.applyToSoal(), 50);
        }

        // Fallback: Parse from tabel if not provided
        if (leftItems.length === 0 || rightItems.length === 0) {
            const tabel = data.soal_opsi.tabel || [];
            const baris = data.soal_opsi.baris || [];

            // Populate Left Items (Pernyataan/Questions)
            if (Array.isArray(tabel) && tabel.length > 0) {
                tabel.forEach((row, idx) => {
                    let txt = Array.isArray(row) ? row[0] : row;
                    if (txt && idx > 0) { // row 0 is Header
                        leftItems.push({ id: 'L' + idx, text: txt, key: String(idx) });
                    }
                });
            }

            // Populate Right Items (Jawaban/Answer Headers from tabel[0])
            if (Array.isArray(tabel) && tabel.length > 0 && Array.isArray(tabel[0])) {
                const headerRow = tabel[0];
                const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                for (let i = 1; i < headerRow.length; i++) {
                    rightItems.push({
                        id: 'R' + (i - 1),
                        text: headerRow[i],
                        key: alphabet[i - 1] || String(i - 1)
                    });
                }
            } else if (Array.isArray(baris) && baris.length > 0) {
                // Legacy fallback: Use baris array
                const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                baris.forEach((item, idx) => {
                    rightItems.push({ id: 'R' + idx, text: item, key: alphabet[idx] || String(idx) });
                });
            }
        }

        // Validation
        if (leftItems.length === 0 || rightItems.length === 0) {
            console.warn('Matching data incomplete:', data.soal_opsi);
            let debugInfo = '<br><small>Debug: left=' + leftItems.length + ', right=' + rightItems.length;
            if (data.soal_opsi._debug) {
                debugInfo += '<br>Keys: ' + JSON.stringify(data.soal_opsi._debug.jawabanData_keys);
                debugInfo += '<br>Type: ' + data.soal_opsi._debug.jawabanData_type;
                debugInfo += '<br>Raw: ' + (data.soal_opsi._debug.raw_jawaban_string || '').substring(0, 200);
            }
            debugInfo += '</small>';
            kontenJawaban.innerHTML = '<div class="alert alert-danger">Gagal memuat format soal menjodohkan.' + debugInfo + '</div>';
        } else {
            // Parse existing answers
            let currentAnswers = {};
            if (jawabanSiswa) {
                try {
                    let parsed = (typeof jawabanSiswa === 'string') ? JSON.parse(jawabanSiswa) : jawabanSiswa;
                    if (parsed && parsed.jawaban) parsed = parsed.jawaban;
                    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                        currentAnswers = parsed;
                    }
                } catch (e) {
                    console.error('Matching Answer Parsing Error:', e);
                }
            }

            // Initialize appropriate view
            const onChange = function (finalAnswers) {
                const submitObj = {
                    jawaban: finalAnswers,
                    model: matchModel,
                    type: matchType
                };
                submitJawaban(submitObj);
            };

            if (matchModel === '2') {
                // TABLE MODE - pass tabel data for initial state
                const tabelData = data.soal_opsi.tabel || [];
                initMatchingTable(kontenJawaban, leftItems, rightItems, currentAnswers, matchType, tabelData, onChange);
            } else {
                // LIST MODE (SVG lines)
                initMatchingGame(kontenJawaban, leftItems, rightItems, currentAnswers, onChange);
            }
        }
    } else if (jenis == "4") {
        html += '<div class="pe-4">' +
            '<span class="">JAWABAN:</span><br>' +
            '<div class="row"><div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-4">' +
            '<input id="jawaban-isian" class="ps-1 form-control" type="text"' +
            ' name="jawaban" value="' + jawabanSiswa + '"' +
            ' placeholder="Tulis jawaban disini"/><br>' +
            '</div></div>' +
            '</div>';
        kontenJawaban.innerHTML = html;

        const inputIsian = document.getElementById("jawaban-isian");
        ['change', 'keyup', 'paste'].forEach(evt =>
            inputIsian.addEventListener(evt, function () {
                submitJawaban(null);
            })
        );
    } else if (jenis == "6") {
        // --- TYPE 6: URUTAN (ORDERING/SEQUENCING) ---
        kontenJawaban.innerHTML = '';

        // Fix: Handle both {items: []} and direct array []
        const items = Array.isArray(data.soal_opsi) ? data.soal_opsi : (data.soal_opsi?.items || []);
        let currentOrder = [];
        let hasExistingAnswer = false;

        // Parse existing answer
        if (jawabanSiswa) {
            try {
                const parsed = (typeof jawabanSiswa === 'string') ? JSON.parse(jawabanSiswa) : jawabanSiswa;
                if (Array.isArray(parsed) && parsed.length > 0) {
                    currentOrder = parsed;
                    hasExistingAnswer = true;
                }
            } catch (e) {
                console.error('Ordering answer parse error:', e);
            }
        }

        // If no answer yet, use original order
        if (currentOrder.length === 0) {
            currentOrder = items.map((_, idx) => idx);
        }

        console.log('Ordering items:', items); // Add this line for debugging
        console.log('Current order:', currentOrder); // Add this line for debugging

        initOrderingGame(kontenJawaban, items, currentOrder, function (newOrder) {
            submitJawaban(newOrder);
        }, hasExistingAnswer);

    } else if (jenis == "7") {
        // --- TYPE 7: ISIAN RUMPANG (CLOZE TEST) ---
        kontenJawaban.innerHTML = '';

        // Parse blanks from soal text: [[blank_1]], [[blank_2]], [[1]], [[2]], etc.
        // Server sends the question text in data.soal_soal
        let soalText = data.soal_soal || data.soal || '';
        const blanks = data.soal_opsi?.blanks || {};

        // Parse existing answers
        let answers = {};
        if (jawabanSiswa) {
            try {
                const parsed = (typeof jawabanSiswa === 'string') ? JSON.parse(jawabanSiswa) : jawabanSiswa;
                if (parsed && typeof parsed === 'object') {
                    answers = parsed;
                }
            } catch (e) {
                console.error('Cloze answer parse error:', e);
            }
        }

        initClozeTest(kontenJawaban, soalText, blanks, answers, function (newAnswers) {
            submitJawaban(newAnswers);
        });

    } else {
        // Quill Editor for Essay (Type 5 and fallback)
        html += '<div class="pe-4">' +
            '<label>JAWABAN:</label><br>' +
            '<div id="quill-editor" style="height: 200px; background: #fff;">' + jawabanSiswa + '</div>' +
            '<input type="hidden" id="jawaban-essai" name="jawaban">' +
            '</div>';
        kontenJawaban.innerHTML = html;
        document.getElementById('jawaban-essai').value = jawabanSiswa;

        if (typeof Quill !== 'undefined') {
            const quill = new Quill('#quill-editor', {
                theme: 'snow',
                placeholder: 'Tulis Jawaban disini...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['clean']
                    ]
                }
            });



            let timeout;
            quill.on('text-change', function () {
                document.getElementById('jawaban-essai').value = quill.root.innerHTML;
                clearTimeout(timeout);
                timeout = setTimeout(() => submitJawaban(null), 1000); // 1s debounce
            });
        }
    }

    // Build modal HTML from cache or initial server data
    const buildModalHtml = () => {
        // Use soalTotal if available, otherwise count from questions array
        const total = soalTotal || (typeof cbt_questions !== 'undefined' ? cbt_questions.length : 0);
        if (!total || total === 0) return '';

        // Build lookup from initial server data (jawaban_siswa is object keyed by id_soal)
        // cbt_answers format: { id_soal: { jawaban: 'A', ragu: '0', ... }, ... }
        const initialAnswers = (typeof cbt_answers !== 'undefined') ? cbt_answers :
            (typeof jawaban_siswa !== 'undefined') ? jawaban_siswa : {};
        const questionList = (typeof cbt_questions !== 'undefined') ? cbt_questions :
            (typeof questions !== 'undefined') ? questions : [];

        let html = '<div class="grid-soal-wrapper">';
        for (let i = 1; i <= total; i++) {
            const cachedData = soalCache[i];
            let btnClass = 'btn-outline-secondary';
            let badgeContent = i;
            let isAnswered = false;
            let isRagu = false;
            let answerValue = null;

            if (cachedData) {
                // Use cached data (already navigated to this question)
                isRagu = cachedData.soal_ragu == '1' || cachedData.soal_ragu === true;
                answerValue = cachedData.soal_jawaban_siswa;
            } else {
                // Use initial server data (not yet navigated after refresh)
                // Get id_soal from questions array (0-indexed)
                const questionData = questionList[i - 1];
                if (questionData) {
                    const idSoal = questionData.id_soal;
                    const answerData = initialAnswers[idSoal];
                    if (answerData) {
                        answerValue = answerData.jawaban;
                        isRagu = answerData.ragu == '1';
                    }
                }
            }

            // Parse JSON answer if needed
            if (typeof answerValue === 'string' && (answerValue.includes('[') || answerValue.includes('{'))) {
                try { answerValue = JSON.parse(answerValue); } catch (e) { }
            }

            isAnswered = answerValue && (Array.isArray(answerValue) ? answerValue.length > 0 : answerValue !== '');

            // Determine button class
            if (i === nomorSoal) {
                btnClass = 'btn-primary text-white border-primary';
            } else if (isRagu) {
                btnClass = 'btn-warning text-dark border-warning';
            } else if (isAnswered) {
                btnClass = 'btn-success text-white border-success';
            }

            // Add badge for answered questions
            if (isAnswered) {
                const alias = (cachedData && cachedData.soal_jawaban_alias) || answerValue;
                const badge = (typeof alias === 'string' && /^[A-E]$/i.test(alias))
                    ? alias.toUpperCase()
                    : '<i class="bi bi-check-lg"></i>';
                badgeContent = i + '<span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-dark d-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:0.65rem;padding:0;border:2px solid #fff;">' + badge + '</span>';
            }

            html += '<button type="button" class="btn ' + btnClass + ' position-relative" ';
            html += 'style="width:45px;height:45px;font-weight:bold;padding:0;" ';
            html += 'id="box' + i + '" data-nomorsoal="' + i + '" onclick="loadSoal(this)">';
            html += badgeContent;
            html += '</button>';
        }
        html += '</div>';

        // Legend
        html += '<div class="mt-4 d-flex flex-wrap justify-content-center gap-3 small">';
        html += '<div class="d-flex align-items-center gap-1"><span class="badge border border-secondary text-secondary bg-white" style="width:20px;height:20px;">&nbsp;</span> Belum</div>';
        html += '<div class="d-flex align-items-center gap-1"><span class="badge bg-success" style="width:20px;height:20px;">&nbsp;</span> Jawab</div>';
        html += '<div class="d-flex align-items-center gap-1"><span class="badge bg-warning" style="width:20px;height:20px;">&nbsp;</span> Ragu</div>';
        html += '<div class="d-flex align-items-center gap-1"><span class="badge bg-primary" style="width:20px;height:20px;">&nbsp;</span> Aktif</div>';
        html += '</div>';

        return html;
    };

    document.getElementById('konten-modal').innerHTML = buildModalHtml();
    updateModalState();

    // Sync Ragu States
    if (!isCached) {
        // Update global state from fresh server data
        const listItems = document.querySelectorAll('.question-popup ul li');
        listItems.forEach(function (li) {
            let id = li.id; // box1, box2
            if (id) {
                let num = id.replace('box', '');
                if (li.classList.contains('ragu')) {
                    raguStates[num] = true;
                } else {
                    raguStates[num] = false;
                }
            }
        });
    } else {
        // Apply global state to stale cached UI
        Object.keys(raguStates).forEach(function (num) {
            const isRagu = raguStates[num];
            const box = document.getElementById('box' + num);
            if (box) {
                if (isRagu) {
                    box.classList.add('ragu');
                } else {
                    box.classList.remove('ragu');
                }
            }
        });
    }

    // Handle Images
    const imgs = document.querySelectorAll('.konten-soal-jawab img');
    imgs.forEach(function (img) {
        var curSrc = img.getAttribute('src');
        if (!curSrc.includes("uploads")) return;
        var newSrc = '';
        if (curSrc.indexOf("http") === -1 && curSrc.indexOf("data:image") === -1) {
            newSrc = base_url + curSrc;
            img.setAttribute('src', newSrc);
        } else if (curSrc.indexOf(base_url) === -1) {
            var pathUpload = 'uploads';
            var forReplace = curSrc.split(pathUpload);
            newSrc = base_url + pathUpload + forReplace[1];
            img.setAttribute('src', newSrc);
            img.removeAttribute('alt');
        }
        img.addEventListener('load', function () {
            if (this.height > 50) {
                this.classList.add('img-fluid');
            }
        });

        img.addEventListener('error', function () {
            if (this.dataset.handled) return;
            this.dataset.handled = true;

            const div = document.createElement('div');
            div.className = 'd-inline-flex flex-column align-items-center justify-content-center p-3 border bg-light rounded text-secondary m-1';
            div.style.minWidth = '120px';
            div.style.backgroundColor = '#f8f9fa';
            div.innerHTML = '<i class="bi bi-card-image fs-1 text-muted mb-1"></i><span style="font-size:0.75rem; color: #6c757d;">Gambar tidak ditemukan</span>';

            if (this.parentNode) this.parentNode.replaceChild(div, this);
        });
    });

    // Handle Audio
    var pathUpload = 'uploads';
    const audios = document.querySelectorAll('audio');
    audios.forEach((audio, ind) => {
        var curSrc = audio.getAttribute('src');
        if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
        } else {
            var forReplace = curSrc.split(pathUpload);
            audio.setAttribute('src', base_url + pathUpload + forReplace[1]);
        }

        const dataId = idSoal + '-' + ind;
        const m_audio = media.find(item => item.id === dataId && item.type === 'audio');

        // Assuming renderAudio is a jquery plugin extension or custom prototype
        // Use native prototype extension
        if (typeof audio.renderAudio === 'function') {
            audio.renderAudio({
                'idSoal': dataId,
                'played': m_audio?.play || 0,
                callback: function (idMedia, playCount) {
                    console.log(`Audio [${idMedia}] played: ${playCount}`);
                    savePlayed('audio', idSoal, idMedia, playCount)
                }
            });
        }
    });

    // Handle Video
    const videos = document.querySelectorAll('video');
    videos.forEach((video, ind) => {
        var curSrc = video.getAttribute('src');
        if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
        } else {
            var forReplace = curSrc.split(pathUpload);
            video.setAttribute('src', base_url + pathUpload + forReplace[1]);
        }

        const dataId = idSoal + '-' + ind;
        const m_video = media.find(item => item.id === dataId && item.type === 'video');

        // Assuming renderAudio is actually renderVideo or a shared plugin. Original code used renderAudio on 'this' in video loop??
        // Checking original code: 'this.renderAudio({...})'. 'this' inside $.each refers to the DOM element.
        // renderAudio is likely a jQuery plugin attached to $.fn.
        // Use native prototype extension and Correct Method for Video
        if (typeof video.renderVideo === 'function') {
            video.renderVideo({
                'idSoal': dataId,
                'played': m_video?.play || 0,
                callback: function (idMedia, playCount) {
                    console.log(`Video [${idMedia}] played: ${playCount}`);
                    savePlayed('video', idSoal, idMedia, playCount)
                }
            });
        }
    });

    // Initialize Image Zoom
    initImageZoom();

    if (!isCached) {
        if (data.durasi.reset_waktu == '1' || data.durasi.reset_waktu == '2' || data.durasi.reset_waktu == '3') {
            const remaining = data.durasi.lama_ujian;
            if (remaining != '00:00:00') {
                const timeParts = remaining.split(":");
                elapsed = timeParts[0] + ":" + timeParts[1] + ":" + timeParts[2];
            } else {
                elapsed = '00:00:00';
            }
        } else {
            const remaining = data.interval;
            const jam = remaining.jam !== undefined ? remaining.jam : (remaining.h !== undefined ? remaining.h : 0);
            const menit = remaining.menit !== undefined ? remaining.menit : (remaining.i !== undefined ? remaining.i : 0);
            const detik = remaining.detik !== undefined ? remaining.detik : (remaining.s !== undefined ? remaining.s : 0);
            elapsed = zeroPad(jam) + ":" + zeroPad(menit) + ":" + zeroPad(detik);
        }
        //console.log('durasi', elapsed, durasiUjian);

        createTimerCountdown(durasiUjian, elapsed.split(':'), function (isSelesai, time, elapsed) {
            const timerEl = document.getElementById('timer');
            const timerSelesaiEl = document.getElementById('timer-selesai');
            const nextBtn = document.getElementById('next');
            const prevBtn = document.getElementById('prev');
            const textNext = document.getElementById('text-next');

            if (isSelesai) {
                timerEl.innerHTML = time;
                dialogWaktu();
                timerSelesaiEl.innerHTML = 'Waktu Ujian Habis';
                timerSelesaiEl.classList.remove('d-none');
                nextBtn.classList.add('d-none');
                prevBtn.classList.add('disabled');
            } else {
                timerEl.innerHTML = time;

                // Beep Logic: Alert exactly at 5 minutes remaining
                try {
                    const tParts = time.split(':');
                    const tH = parseInt(tParts[0]);
                    const tM = parseInt(tParts[1]);
                    const tS = parseInt(tParts[2]);
                    const totalRemaining = (tH * 3600) + (tM * 60) + tS;

                    if (totalRemaining === 300) {
                        warningSound.play().catch(e => { });
                        showToast("Waktu ujian tinggal 5 menit!", "warning");
                    }
                } catch (e) { }

                if (nomorSoal === soalTotal && jarak > 0) {
                    const [h, m, s] = elapsed.split(':').map(Number);
                    const currentSeconds = (h * 3600) + (m * 60) + s;
                    const targetSeconds = jarak * 60;

                    if (currentSeconds < targetSeconds) {
                        const diff = targetSeconds - currentSeconds;
                        const remM = Math.floor(diff / 60);
                        const remS = Math.floor(diff % 60);

                        nextBtn.setAttribute('disabled', true);
                        nextBtn.classList.remove('btn-success');
                        nextBtn.classList.add('btn-secondary');
                        textNext.innerHTML = `<b>Tunggu ${remM}:${remS < 10 ? '0' + remS : remS}</b>`;
                    } else {
                        if (nextBtn.classList.contains('btn-secondary')) {
                            nextBtn.removeAttribute('disabled');
                            nextBtn.classList.remove('btn-secondary');
                            nextBtn.classList.add('btn-success');
                            textNext.innerHTML = '<b class="d-none d-md-inline-block me-2">Selesai</b> <i class="bi bi-check-circle"></i>';
                        }
                    }
                }
            }
        });
    }

    // Sync font size for answers based on current box-soal setting
    const currentFontSize = document.getElementById('box-soal').style.fontSize || '12pt';
    document.querySelectorAll('.container-jawaban').forEach(el => el.style.fontSize = currentFontSize);

    if (window.MathJax) {
        if (MathJax.Hub && MathJax.Hub.Queue) {
            MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
        }
    }
}

function submitJawaban(jwb) {
    // ── GUARD: jangan buat payload jawaban bila soal belum dimuat ──────
    // Tanpa guard ini, cabang fallback di bawah menghasilkan jsonJawaban
    // berisi id_soal/jenis = undefined yang terkirim ke server (422).
    if (!soalAktifValid()) {
        jsonJawaban = {};
        return;
    }

    if (jenisSoal === '1') {
        if (jwb) {
            jawabanBaru = jwb.dataset.jawabansiswa;
            jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': jawabanBaru, 'jawaban_alias': jwb.dataset.jawabanalias };
        } else {
            const chk = document.querySelector('#konten-jawaban input[type="radio"]:checked');
            if (chk) {
                jawabanBaru = chk.dataset.jawabansiswa;
                jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': jawabanBaru, 'jawaban_alias': chk.dataset.jawabanalias };
            }
        }
    } else if (jenisSoal === '2' && tipeSoalAktif === 'pgkk') {
        // PGKK Benar/Salah: kumpulkan pernyataan yg sudah dinilai → objek {huruf:'B'|'S'}
        let peta = {};
        document.querySelectorAll('#konten-jawaban input[type="radio"][data-pgkk-letter]:checked').forEach(function (r) {
            peta[r.dataset.pgkkLetter] = r.value;
        });
        jawabanBaru = peta;
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': peta, 'jawaban_alias': '' }
    } else if (jenisSoal === '2') {
        const max = jwb ? jwb.dataset.max : 99;
        let selected = [];
        document.querySelectorAll('#konten-jawaban input:checked').forEach(function (chk) {
            selected.push(chk.value);
        });

        if (jwb && selected.length > max) {
            jwb.checked = false; // Uncheck the last click
            const msg = "Anda tidak diperbolehkan memilih semua pilihan jawaban! (Maksimal " + max + " pilihan)";
            if (typeof showCbtWarning === 'function') {
                showCbtWarning(msg);
            } else {
                alert(msg);
            }
            return; // Stop submission
        }

        jawabanBaru = selected;
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': jawabanBaru, 'jawaban_alias': '' }
    } else if (jenisSoal === '3') {
        jawabanBaru = jwb || jawabanSiswa || null;
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': jawabanBaru, 'jawaban_alias': '' }
    } else if (jenisSoal === '4') {
        jawabanBaru = document.getElementById('jawaban-isian').value;
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': jawabanBaru, 'jawaban_alias': '' }
    } else if (jenisSoal === '5') {
        // Essay type
        const essaiEl = document.getElementById('jawaban-essai');
        jawabanBaru = essaiEl ? essaiEl.value : '';
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': jawabanBaru, 'jawaban_alias': '' }
    } else if (jenisSoal === '6') {
        // Urutan (Ordering) type - jwb is the new order array from callback
        if (jwb && Array.isArray(jwb)) {
            jawabanBaru = jwb;
        } else {
            // Fallback: collect from DOM using .ordering-item
            let orderedItems = [];
            document.querySelectorAll('#konten-jawaban .ordering-item').forEach(function (item) {
                orderedItems.push(parseInt(item.dataset.idx));
            });
            jawabanBaru = orderedItems;
        }
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': JSON.stringify(jawabanBaru), 'jawaban_alias': '' }
    } else if (jenisSoal === '7') {
        // Cloze type - jwb is the answers object from callback
        if (jwb && typeof jwb === 'object' && !Array.isArray(jwb)) {
            jawabanBaru = jwb;
        } else {
            // Fallback: collect from DOM using .cloze-blank
            let clozeAnswers = {};
            document.querySelectorAll('#konten-jawaban input.cloze-blank').forEach(function (input) {
                const blankId = input.dataset.blank;
                if (blankId) clozeAnswers[blankId] = input.value;
            });
            jawabanBaru = clozeAnswers;
        }
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': JSON.stringify(jawabanBaru), 'jawaban_alias': '' }
    } else {
        // Fallback for unknown types
        const fallbackEl = document.getElementById('jawaban-essai');
        jawabanBaru = fallbackEl ? fallbackEl.value : '';
        jsonJawaban = { 'jenis': jenisSoal, 'id_soal': idSoal, 'jawaban_siswa': jawabanBaru, 'jawaban_alias': '' }
    }

    var nextBtn = document.getElementById('next');
    var prevBtn = document.getElementById('prev');
    nextBtn.removeAttribute('disabled');
    prevBtn.removeAttribute('disabled');
}

function prevSoal() {
    if (nomorSoal > 1) {
        nav = nomorSoal - 1;
        document.getElementById('jawab').requestSubmit();
    }
}

function nextSoal() {
    nav = nomorSoal + 1;
    document.getElementById('jawab').requestSubmit();
}

async function doFinish() {
    const loadingEl = document.getElementById('loading');
    const nextBtn = document.getElementById('next');

    if (nextBtn) nextBtn.setAttribute('disabled', 'disabled');
    if (loadingEl) loadingEl.classList.remove('d-none');

    try {
        let formData = new FormData(document.getElementById('up'));
        const respon = await ajaxPost('cbtsiswa/selesaiujian', formData);

        if (respon.status || respon.success || respon.redirect) {
            successSound.play().catch(function (e) { });
            setTimeout(function () {
                window.location.href = base_url + 'cbtsiswa/cbt';
            }, 1000);
        } else {
            throw new Error(respon.message || 'Gagal finish');
        }
    } catch (e) {
        if (loadingEl) loadingEl.classList.add('d-none');
        if (nextBtn) nextBtn.removeAttribute('disabled');
        // If critical error, still try to redirect after a delay
        showToast("Ujian selesai. Mengalihkan...", 'info');
        setTimeout(function () {
            window.location.href = base_url + 'cbtsiswa/cbt';
        }, 2000);
    }
}

async function selesai(force = false) {
    // Recalculate answered count from actual data (not stale variable)
    const total = parseInt(soalTotal) || (typeof cbt_questions !== 'undefined' ? cbt_questions.length : 0);

    // Count answered from both cache and initial server data
    let terjawab = 0;
    const initialAnswers = (typeof cbt_answers !== 'undefined') ? cbt_answers :
        (typeof jawaban_siswa !== 'undefined') ? jawaban_siswa : {};
    const questionList = (typeof cbt_questions !== 'undefined') ? cbt_questions :
        (typeof questions !== 'undefined') ? questions : [];

    for (let i = 1; i <= total; i++) {
        const cachedData = soalCache[i];
        let answerValue = null;

        if (cachedData) {
            answerValue = cachedData.soal_jawaban_siswa;
        } else {
            // Check initial server data
            const questionData = questionList[i - 1];
            if (questionData) {
                const idSoal = questionData.id_soal;
                const answerData = initialAnswers[idSoal];
                if (answerData) {
                    answerValue = answerData.jawaban;
                }
            }
        }

        // Parse JSON if needed
        if (typeof answerValue === 'string' && (answerValue.includes('[') || answerValue.includes('{'))) {
            try { answerValue = JSON.parse(answerValue); } catch (e) { }
        }

        if (answerValue && (Array.isArray(answerValue) ? answerValue.length > 0 : answerValue !== '')) {
            terjawab++;
        }
    }

    console.log('Selesai check: total=' + total + ', terjawab=' + terjawab);

    // Remove loading overlay if it was triggered
    const loadingEl = document.getElementById('loading');

    // Force finish (Time Up)
    if (force) {
        await doFinish();
        return;
    }

    // Count ragu from buttons in the modal (looking for warning class)
    const raguEls = document.querySelectorAll('.grid-soal-wrapper .btn-warning');
    const raguCount = raguEls.length;

    if (terjawab >= total) {
        if (raguCount > 0) {
            if (loadingEl) loadingEl.classList.add('d-none');
            // Re-enable next button if it was disabled
            const nextBtn = document.getElementById('next');
            if (nextBtn) nextBtn.removeAttribute('disabled');

            showToast("Masih ada " + raguCount + " soal yang ditandai ragu-ragu!", 'warning');
            return;
        }

        const nextBtn = document.getElementById('next');

        let timerText = document.getElementById('timer').innerText;
        let textMsg = "Apakah anda benar-benar yakin ingin mengakhiri ujian ini?";

        const timeParts = timerText.split(':');
        if (timeParts.length === 3) {
            const h = parseInt(timeParts[0]);
            const m = parseInt(timeParts[1]);
            if (h > 0 || m >= 15) {
                textMsg = "Masih ada waktu ujian " + (h > 0 ? h + " jam " : "") + m + " menit. " + textMsg;
            }
        }

        $('#finishMsg').text(textMsg);
        var myModal = new bootstrap.Modal(document.getElementById('modalConfirmFinish'));
        myModal.show();

        // Reset state if modal is closed without finishing
        document.getElementById('modalConfirmFinish').addEventListener('hidden.bs.modal', function () {
            if (nextBtn) nextBtn.removeAttribute('disabled');
            if (loadingEl) loadingEl.classList.add('d-none');
        }, { once: true });

        $('#btnRealFinish').off('click').on('click', async function () {
            myModal.hide();
            await doFinish();
        });

    } else {
        if (loadingEl) loadingEl.classList.add('d-none'); // Hide loading

        // Re-enable next button logic to avoid stuck state
        const nextBtn = document.getElementById('next');
        // if(nextBtn) nextBtn.removeAttribute('disabled'); // Don't re-enable if logic requires wait

        const belumDijawab = total - terjawab;
        const msg = isNaN(belumDijawab) ? "Masih ada soal yang belum dikerjakan." : "Masih ada " + belumDijawab + " soal yang belum dikerjakan.";
        showInfoModal("BELUM SELESAI!", msg, "error");
    }
}

function dialogWaktu() {
    showInfoModal("Waktu Habis", "Waktu Ujian sudah habis. Jawaban Anda akan disimpan otomatis.", "warning");

    // Auto-save last state before redirecting
    // We try to submit the current open question answer if any
    const form = document.getElementById('jawab');
    if (form) {
        // Trigger submit handler manually if possible, or just force finish
        // But since selesaiUjian is critical, we'll call selesai() which handles final submission
        selesai(true);
    } else {
        selesai(true);
    }
}

function rtclickcheck(keyp) {
    if (navigator.appName == "Netscape" && keyp.which == 3) {
        alert(message);
        return false;
    }

    if (navigator.appVersion.indexOf("MSIE") != -1 && event.button == 2) {
        alert(message);
        return false;
    }
}

function getMinutes(startTime) {
    var endTime = new Date();
    endTime.setHours(endTime.getHours() - startTime.getHours());
    endTime.setMinutes(endTime.getMinutes() - startTime.getMinutes());
    endTime.setSeconds(endTime.getSeconds() - startTime.getSeconds());

    return { h: endTime.getHours(), m: endTime.getMinutes(), s: endTime.getSeconds() }
}

// CATATAN: definisi raguRagu() yang aktif berada di bagian
// "RAGU-RAGU FUNCTIONALITY" di bawah file ini. Versi sebelumnya (yang memakai
// event parameter dan men-toggle checkbox secara manual) sudah dihapus karena
// ter-override oleh deklarasi yang lebih akhir dan tidak pernah terpakai.

function createTimerCountdown(durasi, elapsed, func) {
    // Convert duration minutes to milliseconds
    const durationMs = durasi * 60 * 1000;

    // Parse elapsed time [h, m, s] to milliseconds
    let t_jam = Number(elapsed[0]);
    let t_mnt = Number(elapsed[1]);
    let t_dtk = Number(elapsed[2]);
    const elapsedInitialMs = (t_jam * 3600 + t_mnt * 60 + t_dtk) * 1000;

    // Calculate Target End Time based on local machine time
    // This handles tab throttling/backgrounding accurately
    const startTime = Date.now();
    const targetEndTime = startTime + (durationMs - elapsedInitialMs);

    testTimer();

    function testTimer() {
        if (timerOut) {
            clearTimeout(timerOut);
            timerOut = null;
        }

        const now = Date.now();
        const timeRemainingMs = targetEndTime - now;
        const currentElapsedMs = durationMs - timeRemainingMs;

        if (timeRemainingMs <= 0) {
            if (typeof func === "function") {
                func(true, 'Waktu habis', formatTime(durationMs));
            }
        } else {
            // Formatting remaining time for display
            const r_jam = Math.floor(timeRemainingMs / (1000 * 60 * 60));
            const r_mnt = Math.floor((timeRemainingMs % (1000 * 60 * 60)) / (1000 * 60));
            const r_dtk = Math.floor((timeRemainingMs % (1000 * 60)) / 1000);

            // Formatting elapsed time for display
            const e_jam = Math.floor(currentElapsedMs / (1000 * 60 * 60));
            const e_mnt = Math.floor((currentElapsedMs % (1000 * 60 * 60)) / (1000 * 60));
            const e_dtk = Math.floor((currentElapsedMs % (1000 * 60)) / 1000);

            // Warning 5 menit (Trigger only once when minute hits 5)
            if (r_jam === 0 && r_mnt === 5 && r_dtk === 0) {
                if (typeof warningSound !== 'undefined') warningSound.play().catch(e => { });
                showToast("Waktu Ujian tinggal 5 menit lagi!", 'warning');
            }

            if (typeof func === "function") {
                func(false,
                    zeroPad(r_jam) + ':' + zeroPad(r_mnt) + ':' + zeroPad(r_dtk),
                    zeroPad(e_jam) + ':' + zeroPad(e_mnt) + ':' + zeroPad(e_dtk)
                );
            }

            // Sync next tick to align with second boundary
            const nextTick = 1000 - (Date.now() % 1000);
            timerOut = setTimeout(testTimer, nextTick);
        }
    }

    function formatTime(ms) {
        const h = Math.floor(ms / (1000 * 60 * 60));
        const m = Math.floor((ms % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((ms % (1000 * 60)) / 1000);
        return zeroPad(h) + ':' + zeroPad(m) + ':' + zeroPad(s);
    }
}

function zeroPad(no) {
    return no < 10 ? '0' + no : no;
}

function encode(str) {
    var decoded = decodeURIComponent(str)
    var isEncoded = decoded !== str
    var encoded = encodeURIComponent(str)
    if (isEncoded) {
        return str
    } else {
        return encoded
    }
}

function decode(str) {
    var decoded = decodeURIComponent(str)
    var encoded = encodeURIComponent(decoded)
    var isEncoded = encoded === str
    if (isEncoded) {
        return decoded
    } else {
        return str
    }
}

function kembali() {
    location.href = base_url + "cbtsiswa/cbt";
}

function openFullscreen(elem = document.documentElement) {
    if (!elem) return;
    
    let promise;
    try {
        if (elem.requestFullscreen) {
            promise = elem.requestFullscreen();
        } else if (elem.webkitRequestFullscreen) { /* Safari */
            promise = elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) { /* IE11 */
            promise = elem.msRequestFullscreen();
        } else if (elem.mozRequestFullScreen) { /* Firefox */
            promise = elem.mozRequestFullScreen();
        }

        if (promise && promise.catch) {
            promise.catch(err => {
                // Not a gesture or permission check failed? That's fine, we'll wait for the user to click.
                const isGestureError = err.name === 'NotAllowedError' 
                    || err.name === 'TypeError'
                    || err.message.includes('user gesture')
                    || err.message.includes('Permissions check failed');

                if (isGestureError) {
                    console.log('Fullscreen requires user gesture, waiting for interaction...');
                } else {
                    console.error(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
                }
            });
        }
    } catch (e) {
        console.log('Fullscreen request failed:', e.message);
    }
}

function shouldLogViolation(pelanggaran) {
    return pelanggaran == '1';
}

document.addEventListener("visibilitychange", async () => {
    //const reset = '<?= $reset_login ?? '0' ?>';
    if (document.hidden) {
        await hitungPelanggaran('berat', resetLogin);
    }
});

async function leaveCbt() {
    let formData = new FormData(document.getElementById('up'));
    const response = await ajaxPost('cbtsiswa/leavecbt', formData);
    console.log('leave', response);

    if (response.success) {
        location.href = base_url + "logout";
    } else {
        showToast("Tidak bisa meninggalkan ujian.", 'error');
    }
}

async function hitungPelanggaran(jenis, reset) {
    if (pelanggaran != '1') {
        return; // Tidak aktif, abaikan
    }

    let formData = new FormData(document.getElementById('up'));
    formData.append('jenis', jenis);
    const response = await ajaxPost('cbtsiswa/hitung_pelanggaran', formData);
    console.log('pelanggaran', jenis, reset === '1', response);

    if (response.success) {
        if (reset == '1') {
            await leaveCbt(); // paksa logout
        } else if (reset == '2') {
            openFullscreen();
            showErrorCard('fullscreen');
        } else {
            // Safe access to violation counts with defaults
            const p = response.pelanggaran || {};
            const count = (parseInt(p.ringan) || 0) + (parseInt(p.sedang) || 0) + (parseInt(p.berat) || 0);

            warningSound.play().catch(function (e) { });

            showToast(
                `<div>Pelanggaran Terdeteksi! (${count}x)</div><div class="small mt-1">Nilai kamu akan dikurangi.</div>
            <button class="btn btn-sm btn-primary text-warning mt-2 w-100 fw-bold border-0 shadow-none" onclick="openFullscreen()">Layar Penuh</button>`,
                "warning",
                false
            );

            // Try to force fullscreen immediately if possible
            openFullscreen();
        }
    }
}

document.addEventListener('fullscreenchange', function () {
    if (isExamBrowserEnv()) return;
    if (document.fullscreenElement === null) {
        console.log('Exited fullscreen mode.');
        if (pelanggaran == '1') {
            warningSound.play().catch(function (e) { });

            // STRICT MODE: Block exam immediately when exiting fullscreen
            // Calls hitungPelanggaran with reset='2' to show Blocking Error Card
            hitungPelanggaran('ringan', '2');

            showToast(
                `<div><strong>Anda keluar Fullscreen!</strong></div><div class="small">Ujian dihentikan sementara.</div>`,
                "warning",
                false
            );
        }
    } else {
        console.log('Entered fullscreen mode.');
        // Restore view if we were blocked
        if (!document.getElementById('noapproved').classList.contains('d-none')) {
            document.getElementById('noapproved').classList.add('d-none');
            document.getElementById('approved').classList.remove('d-none');

            // Optional: Reload current question to Ensure rendering is correct
            // if (nomorSoal > 0) loadSoalNomor(nomorSoal, true);
        }
    }
});

async function savePlayed(type, idSoal, idMedia, playCount) {
    let formData = new FormData(document.getElementById('jawab'));
    formData.append('soal', idSoal)
    formData.append('media', idMedia)
    formData.append('play', playCount)
    formData.append('type', type)
    const response = await ajaxPost('cbtsiswa/save_played', formData)
    console.log('onSubmit', response)
}

window.addEventListener('resize', () => {
    deteksiUkuranLayar(false)
});

window.addEventListener("orientationchange", () => {
    console.log("Orientasi berubah ke:", screen.orientation.type);
    deteksiUkuranLayar(true)
});

/* --- MATCHING GAME HELPERS (Native JS) --- */
function injectMatchingStyles() {
    if (document.getElementById('matching-styles')) return;
    const css = `
        .matching - wrapper { display: flex; flex - direction: column;
}
        .matching - header {
    padding: 12px 15px; margin - bottom: 15px;
    background: #fff; border: 1px solid #dee2e6; border - radius: 8px;
    font - weight: 600; color: #343a40; font - size: 16px;
    box - shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
}
        .matching - list { border: none; display: flex; flex - direction: column; gap: 10px; background: transparent; }
        
        .matching - item {
    display: flex; align - items: stretch; min - height: 64px; cursor: pointer;
    border: 1px solid #dee2e6; border - radius: 8px;
    transition: transform 0.1s, box - shadow 0.1s;
    background - color: #fff; position: relative;
    overflow: hidden;
    box - shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
}
        .matching - item:hover { transform: translateY(-1px); box - shadow: 0 4px 8px rgba(0, 0, 0, 0.06); }
        
        .match - content { flex: 1; padding: 12px 16px; display: flex; align - items: center; font - size: 15px; line - height: 1.4; color: #212529; font - weight: 500; }
        .match - action { width: 64px; display: flex; align - items: center; justify - content: center; flex - shrink: 0; }
        
        .left - item.match - action { border - left: 1px solid rgba(0, 0, 0, 0.06); background: #f8f9fa; }
        .right - item.match - action { order: -1; border - right: 1px solid rgba(0, 0, 0, 0.06); background: #f8f9fa; }
        
        .check - circle {
    width: 32px; height: 32px; border: 2px solid #adb5bd; border - radius: 50 %;
    display: flex; align - items: center; justify - content: center;
    background: #fff; color: transparent; font - size: 16px;
}
        
        .matching - item.paired { color: #000!important; }
        
        .connections { pointer - events: none; position: absolute; top: 0; left: 0; width: 100 %; height: 100 %; z - index: 10; }
`;
    const style = document.createElement('style');
    style.id = 'matching-styles';
    style.innerHTML = css;
    document.head.appendChild(style);
}

var match_selectedLeft = null;
var match_answers = {};
var match_callback = null;

function injectMatchingStylesFixed() {
    if (document.getElementById('matching-styles-fixed')) return;
    const css = `
        .matching-wrapper { display: flex; flex-direction: column; gap: 0; box-sizing: border-box; width: 100%; }
        .matching-header { 
            padding: 0; margin-bottom: 15px; 
            background: transparent; border: none; border-radius: 0;
            font-weight: 600; color: #495057; font-size: 16px;
            text-align: center;
            box-shadow: none;
        }
        .matching-columns { display: flex; gap: 8px; width: 100%; }
        .matching-col-left, .matching-col-right { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
        .matching-list { border: none; display: flex; flex-direction: column; gap: 8px; background: transparent; }
        
        .matching-item { 
            display: flex; align-items: stretch; min-height: 52px; cursor: pointer; 
            border: 1px solid #dee2e6; border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s; 
            background-color: #fff; position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .matching-item:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        
        .match-content { 
            flex: 1; padding: 10px 12px; display: flex; align-items: center; 
            font-size: 13px; line-height: 1.4; color: #333; font-weight: 500;
            min-width: 0; /* Allow text to wrap */
            word-wrap: break-word; overflow-wrap: break-word;
        }
        .match-content img { max-width: 50px; max-height: 40px; object-fit: contain; margin-right: 8px; flex-shrink: 0; }
        
        .match-action { 
            width: 40px; display: flex; align-items: center; justify-content: center; 
            flex-shrink: 0; background-color: #f8f9fa; 
        }
        
        .left-item .match-action { border-left: 1px solid #dee2e6; }
        .right-item .match-action { order: -1; border-right: 1px solid #dee2e6; }
        
        .check-circle { 
            width: 22px; height: 22px; border: 2px solid #adb5bd; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            background: #fff; color: transparent; font-size: 12px;
            transition: all 0.2s;
        }
        .check-circle.checked { background: #28a745; border-color: #28a745; color: #fff; }
        
        /* Force Text Black on paired */
        .matching-item.paired { color: #000 !important; }
        .matching-item.paired .check-circle { background: #28a745; border-color: #28a745; color: #fff; }
        
        .connections { pointer-events: none; position: absolute; top:0; left:0; width:100%; height:100%; z-index: 10; }

        /* Spacer for SVG lines */
        .match-spacer { width: 60px; flex-shrink: 0; transition: width 0.3s; position: relative; }

        /* MOBILE RESPONSIVE */
        @media (max-width: 768px) {
            .match-spacer { width: 30px; }
            .matching-item { min-height: 44px; }
            .match-action { width: 32px; }
            .match-content { padding: 8px 10px; font-size: 12px; }
            .match-content img { max-width: 36px; max-height: 30px; }
            .check-circle { width: 18px; height: 18px; }
        }
        
        @media (max-width: 576px) {
            .match-spacer { width: 20px; }
            .matching-item { min-height: 40px; border-radius: 6px; }
            .match-action { width: 28px; }
            .match-content { padding: 6px 8px; font-size: 11px; line-height: 1.3; }
            .match-content img { max-width: 28px; max-height: 24px; margin-right: 4px; }
            .check-circle { width: 16px; height: 16px; border-width: 1.5px; }
            .matching-header { padding: 8px 12px; font-size: 13px; margin-bottom: 8px; }
            .matching-columns { gap: 4px; }
        }

        /* TABLE MODE STYLES */
        .matching-table-container { overflow-x: auto; }
        .matching-table { 
            width: 100%; border-collapse: collapse; 
            background: #fff; border-radius: 8px; overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .matching-table th, .matching-table td { 
            border: 1px solid #dee2e6; padding: 12px 10px; vertical-align: middle; 
        }
        .matching-table th { 
            background: #f1f5f9; 
            color: #475569; font-weight: 600; text-align: center; 
            border-bottom: 2px solid #cbd5e1;
        }
        .matching-table th:first-child { text-align: left; min-width: 200px; }
        .matching-table th:not(:first-child) { width: 70px; min-width: 60px; } /* Narrow checkbox columns */
        .matching-table td:first-child { font-weight: 500; background: #f8f9fa; }
        .matching-table td:not(:first-child) { text-align: center; width: 70px; }
        .matching-table tr:hover td { background-color: #e3f2fd; }
        .matching-table input[type="checkbox"], .matching-table input[type="radio"] {
            width: 22px; height: 22px; cursor: pointer; accent-color: #667eea;
        }
        @media (max-width: 576px) {
            .matching-table th, .matching-table td { padding: 8px 6px; font-size: 12px; }
            .matching-table th:not(:first-child), .matching-table td:not(:first-child) { width: 50px; min-width: 40px; }
            .matching-table input[type="checkbox"], .matching-table input[type="radio"] { width: 18px; height: 18px; }
        }
    `;
    const style = document.createElement('style');
    style.id = 'matching-styles-fixed';
    style.innerHTML = css;
    document.head.appendChild(style);
}

// =====================================================
// TABLE MODE: Matching with Checkbox/Radio in Table
// =====================================================
function initMatchingTable(container, leftItems, rightItems, initialAnswers, matchType, tabelData, onChange) {
    injectMatchingStylesFixed();

    // matchType: '1' = Multiple per row (checkbox), '2' = Single per row (radio)
    const inputType = matchType === '1' ? 'checkbox' : 'radio';

    // Build Table HTML
    let html = '<div class="matching-table-container"><table class="matching-table">';

    // Header Row
    html += '<thead><tr><th>Pernyataan</th>';
    rightItems.forEach(item => {
        html += `<th>${item.text}</th>`;
    });
    html += '</tr></thead><tbody>';

    // Data Rows
    leftItems.forEach((leftItem, rowIdx) => {
        html += `<tr data-row="${leftItem.id}">`;
        html += `<td>${leftItem.text}</td>`;

        rightItems.forEach((rightItem, colIdx) => {
            // Check if this cell is checked
            let isChecked = false;

            // Check from initialAnswers (jawaban siswa) only
            if (initialAnswers && initialAnswers[leftItem.id]) {
                if (matchType === '1') {
                    const ans = initialAnswers[leftItem.id];
                    isChecked = Array.isArray(ans) ? ans.includes(rightItem.id) : ans === rightItem.id;
                } else {
                    isChecked = initialAnswers[leftItem.id] === rightItem.id;
                }
            }
            // DO NOT fallback to tabelData - that contains answer keys, not student answers

            const checked = isChecked ? 'checked' : '';
            const name = inputType === 'radio' ? `match_row_${leftItem.id}` : `match_${leftItem.id}_${rightItem.id}`;

            html += `<td>
                <input type="${inputType}" name="${name}" 
                       data-left="${leftItem.id}" data-right="${rightItem.id}" 
                       ${checked}>
            </td>`;
        });
        html += '</tr>';
    });

    html += '</tbody></table></div>';
    html += '<p class="text-center text-muted fst-italic mt-3 small">*Pilih jawaban yang sesuai untuk setiap pernyataan.</p>';

    container.innerHTML = html;

    // Event Listeners
    const inputs = container.querySelectorAll('.matching-table input');
    inputs.forEach(input => {
        input.addEventListener('change', function () {
            // Collect all answers
            const answers = {};

            leftItems.forEach(leftItem => {
                if (matchType === '1') {
                    // Multiple: Collect all checked for this row
                    const checkedInputs = container.querySelectorAll(`input[data-left="${leftItem.id}"]:checked`);
                    if (checkedInputs.length > 0) {
                        const vals = [];
                        checkedInputs.forEach(ci => vals.push(ci.dataset.right));
                        answers[leftItem.id] = vals.length === 1 ? vals[0] : vals;
                    }
                } else {
                    // Single: Get the one checked radio
                    const checkedRadio = container.querySelector(`input[name="match_row_${leftItem.id}"]:checked`);
                    if (checkedRadio) {
                        answers[leftItem.id] = checkedRadio.dataset.right;
                    }
                }
            });

            if (onChange) onChange(answers);
        });
    });
}

const MATCH_PALETTE = [
    '#90caf9', // Blue 200
    '#a5d6a7', // Green 200
    '#ef9a9a', // Red 200
    '#ffcc80', // Orange 200
    '#ce93d8', // Purple 200
    '#80deea', // Cyan 200
    '#f48fb1', // Pink 200
    '#bcaaa4', // Brown 200
    '#b0bec5', // Blue Grey 200
    '#e6ee9c'  // Lime 200
];

function match_getColorForPair(leftId) {
    let num = 0;
    if (leftId && typeof leftId === 'string') {
        num = parseInt(leftId.replace(/\D/g, '')) || 0;
        if (num === 0) {
            for (let i = 0; i < leftId.length; i++) num += leftId.charCodeAt(i);
        }
    }
    return MATCH_PALETTE[num % MATCH_PALETTE.length];
}

function initMatchingGame(container, leftItems, rightItems, initialAnswers, onChange) {
    injectMatchingStylesFixed();
    match_answers = initialAnswers || {};
    match_callback = onChange;
    match_selectedLeft = null;

    // Generic Placeholders
    let html = `
        <div class="matching-wrapper" id="matchArea" style="min-height: 400px; user-select: none; position: relative;">
            <svg class="connections" id="svgLines"></svg>
            
            <div class="matching-columns">
                <div class="matching-col-left" id="colLeft"></div>
                
                <div class="match-spacer"></div>
                
                <div class="matching-col-right" id="colRight"></div>
            </div>
            
            <p class="text-center text-muted fst-italic mt-3 small">*Klik item item kiri, lalu klik item kanan untuk menghubungkan.</p>
        </div>
    `;
    container.innerHTML = html;

    renderMatchingItems(leftItems, rightItems);
    match_drawLines();

    // Robust Redraw: Wait until visible/layout ready
    const tryRedraw = (attempts = 0) => {
        if (attempts > 20) return; // give up after 4s
        const rect = container.getBoundingClientRect();
        // Check visibility or zero dimension
        if (container.offsetParent === null || (rect.width === 0 && rect.height === 0)) {
            setTimeout(() => tryRedraw(attempts + 1), 200);
            return;
        }
        match_updateUI();
        match_drawLines();
    };
    setTimeout(() => tryRedraw(), 50);

    if (window.ResizeObserver) {
        new ResizeObserver(match_drawLines).observe(container);
    } else {
        window.addEventListener('resize', match_drawLines);
    }
}

function renderMatchingItems(leftItems, rightItems) {
    const colLeft = document.getElementById('colLeft');
    const colRight = document.getElementById('colRight');

    // Headers & List Containers
    colLeft.innerHTML = `
        <div class="matching-header">Penjelasan</div>
        <div class="matching-list" id="listLeft"></div>
    `;
    colRight.innerHTML = `
        <div class="matching-header">Jawaban</div>
        <div class="matching-list" id="listRight"></div>
    `;

    const listLeft = document.getElementById('listLeft');
    const listRight = document.getElementById('listRight');

    leftItems.forEach(item => {
        const div = document.createElement('div');
        div.className = 'matching-item left-item';
        div.dataset.val = item.id;
        div.innerHTML = `
            <div class="match-content">${item.text}</div>
            <div class="match-action">
                <div class="check-circle"><i class="bi bi-check"></i></div>
            </div>
        `;
        div.onclick = () => match_handleLeft(item.id);
        listLeft.appendChild(div);
    });

    rightItems.forEach(item => {
        const div = document.createElement('div');
        div.className = 'matching-item right-item';
        div.dataset.val = item.id;
        // Right side: Action box FIRST, then Content
        div.innerHTML = `
            <div class="match-action">
                <div class="check-circle"><i class="bi bi-check"></i></div>
            </div>
            <div class="match-content justify-content-start">${item.text}</div>
        `;
        div.onclick = () => match_handleRight(item.id);
        listRight.appendChild(div);
    });

    match_updateUI();

    // Re-initialize zoom for newly rendered images
    if (typeof initImageZoom === 'function') {
        initImageZoom();
    }
}

function match_handleLeft(id) {
    match_selectedLeft = id;
    match_updateUI();
}

function match_handleRight(id) {
    // Skenario 1: User sedang memilih item Kiri
    if (match_selectedLeft) {
        // Cek apakah item Kanan ini sudah berpasangan dengan item Kiri yang SEDANG dipilih?
        if (match_answers[match_selectedLeft] === id) {
            // Jika ya, klik ulang = Hapus (Toggle Off)
            delete match_answers[match_selectedLeft];
        } else {
            // Jika tidak, set pasangan baru (Override)
            match_answers[match_selectedLeft] = id;
        }
        match_selectedLeft = null;
    }
    // Skenario 2: User TIDAK memilih item Kiri, tapi klik item Kanan yang sudah ada pasangan
    else {
        // Cari LeftID yang menunjuk ke RightID ini
        let leftIdFound = null;
        for (const [key, val] of Object.entries(match_answers)) {
            if (val === id) {
                leftIdFound = key;
                break;
            }
        }

        if (leftIdFound) {
            // Jika ketemu, hapus hubungan
            delete match_answers[leftIdFound];
        }
    }

    match_updateUI();
    match_drawLines();
    if (match_callback) match_callback(match_answers);
}

function match_remove(leftId) {
    delete match_answers[leftId];
    match_updateUI();
    match_drawLines();
    if (match_callback) match_callback(match_answers);
}

function match_updateUI() {
    // 1. Reset all styles
    document.querySelectorAll('.matching-item').forEach(el => {
        el.classList.remove('selected', 'paired');
        el.classList.add('bg-white');

        el.style.backgroundColor = '';
        el.style.color = '';
        el.style.borderColor = '';

        const circle = el.querySelector('.check-circle');
        if (circle) {
            circle.style.backgroundColor = '';
            circle.style.borderColor = '';
            circle.style.color = '';
        }
    });

    // 2. Apply Paired Colors (Soft Pastel for Box, Black Text)
    Object.entries(match_answers).forEach(([leftId, rightId]) => {
        const color = match_getColorForPair(leftId);

        const applyStyle = (sel) => {
            const el = document.querySelector(sel);
            if (el) {
                el.classList.add('paired');
                el.classList.remove('bg-white');

                // Pastel Background with Dark Text
                el.style.setProperty('background-color', color, 'important');
                el.style.setProperty('border-color', color, 'important');
                // Force Black Text
                el.style.color = '#000';

                // Circle Styling
                const circle = el.querySelector('.check-circle');
                if (circle) {
                    circle.style.backgroundColor = 'rgba(255,255,255,0.6)';
                    circle.style.borderColor = '#fff';
                    circle.style.color = '#555';
                }
            }
        };

        // Correct Selectors
        applyStyle(`#colLeft .matching-item[data-val="${leftId}"]`);
        applyStyle(`#colRight .matching-item[data-val="${rightId}"]`);
    });

    // 3. Highlight Selection
    if (match_selectedLeft) {
        const el = document.querySelector(`#colLeft .matching-item[data-val="${match_selectedLeft}"]`);
        if (el) {
            el.classList.add('selected');
            if (!match_answers[match_selectedLeft]) {
                el.style.setProperty('background-color', '#e3f2fd', 'important');
                el.style.color = '#212529';
                el.style.borderColor = '#90caf9';
                const circle = el.querySelector('.check-circle');
                if (circle) circle.style.borderColor = '#90caf9';
            }
        }
    }
}

function match_drawLines() {
    const svg = document.getElementById('svgLines');
    const area = document.getElementById('matchArea');
    if (!svg || !area) return;

    svg.innerHTML = '';
    const areaRect = area.getBoundingClientRect();

    Object.entries(match_answers).forEach(([leftId, rightId]) => {
        // Get Parents first
        const leftParent = document.querySelector(`#colLeft .matching-item[data-val="${leftId}"]`);
        const rightParent = document.querySelector(`#colRight .matching-item[data-val="${rightId}"]`);

        if (leftParent && rightParent) {
            // Try to target Action Box (Desktop), Fallback to Parent (Mobile/Hidden)
            let leftTarget = leftParent.querySelector('.match-action');
            let rightTarget = rightParent.querySelector('.match-action');

            // Using offsetParent to check visibility (null if display:none)
            if (!leftTarget || leftTarget.offsetParent === null) leftTarget = leftParent;
            if (!rightTarget || rightTarget.offsetParent === null) rightTarget = rightParent;

            const lRect = leftTarget.getBoundingClientRect();
            const rRect = rightTarget.getBoundingClientRect();

            // Connect Inner Edges
            // For Left Item: Connect to Right Edge
            const x1 = lRect.right - areaRect.left;
            const y1 = lRect.top + lRect.height / 2 - areaRect.top;

            // For Right Item: Connect to Left Edge
            const x2 = rRect.left - areaRect.left;
            const y2 = rRect.top + rRect.height / 2 - areaRect.top;

            const color = match_getColorForPair(leftId);

            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', x1); line.setAttribute('y1', y1);
            line.setAttribute('x2', x2); line.setAttribute('y2', y2);
            line.setAttribute('stroke', color);
            line.setAttribute('stroke-width', '2');
            svg.appendChild(line);
        }
    });
}

// =====================================================
// TYPE 6: URUTAN (ORDERING/SEQUENCING) - Drag & Drop
// =====================================================
function initOrderingGame(container, items, currentOrder, onChange, hasExistingAnswer = false) {
    // Inject styles
    if (!document.getElementById('ordering-styles')) {
        const css = `
            .ordering-container { max-width: 600px; margin: 0 auto; }
            .ordering-header { 
                background: #f1f5f9; padding: 12px 15px; border-radius: 8px 8px 0 0;
                font-weight: 600; color: #475569; margin-bottom: 0; border: 1px solid #dee2e6;
                transition: all 0.3s ease;
            }
            .ordering-header.answered { 
                background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
                color: #155724; border-color: #28a745;
            }
            .ordering-header .status-badge {
                float: right; font-size: 12px; padding: 2px 8px;
                border-radius: 12px; background: #6c757d; color: #fff;
            }
            .ordering-header.answered .status-badge {
                background: #28a745;
            }
            .ordering-list { 
                list-style: none; padding: 0; margin: 0;
                border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 8px 8px;
                background: #fff; transition: border-color 0.3s ease;
            }
            .ordering-list.answered { border-color: #28a745; }
            .ordering-item { 
                display: flex; align-items: center; padding: 12px 15px;
                border-bottom: 1px solid #e9ecef; cursor: grab; 
                transition: background 0.2s, transform 0.2s;
                background: #fff;
            }
            .ordering-item:last-child { border-bottom: none; border-radius: 0 0 8px 8px; }
            .ordering-item:hover { background: #f8f9fa; }
            .ordering-item.dragging { 
                opacity: 0.5; background: #e3f2fd; transform: scale(1.02);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .ordering-item.drag-over { 
                border-top: 3px solid #667eea; padding-top: 9px;
            }
            .ordering-number { 
                width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                font-weight: 700; font-size: 14px; margin-right: 12px; flex-shrink: 0;
                transition: all 0.3s ease;
            }
            .ordering-number.answered { 
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
            }
            .ordering-content { flex: 1; font-size: 15px; color: #333; word-wrap: break-word; overflow-wrap: break-word; line-height: 1.5; padding-right: 10px; }
            .ordering-handle { 
                color: #adb5bd; font-size: 18px; margin-left: 10px; cursor: grab;
            }
            .ordering-hint {
                text-align: center; color: #6c757d; font-style: italic;
                padding: 10px; font-size: 13px; transition: color 0.3s ease;
            }
            .ordering-hint.answered { color: #28a745; font-weight: 500; }
        `;
        const style = document.createElement('style');
        style.id = 'ordering-styles';
        style.innerHTML = css;
        document.head.appendChild(style);
    }
    // Track if user has made changes
    let hasChanged = false;

    // Build HTML
    let html = `
        <div class="ordering-container">
            <div class="ordering-header" id="orderingHeader">
                <i class="bi bi-sort-down me-2"></i> Urutkan dari atas ke bawah
                <span class="status-badge" id="orderingStatus">Belum dijawab</span>
            </div>
            <ul class="ordering-list" id="orderingList">
    `;

    currentOrder.forEach((idx, pos) => {
        const text = items[idx] || `Item ${idx + 1}`;
        html += `
            <li class="ordering-item" draggable="true" data-idx="${idx}">
                <span class="ordering-number">${pos + 1}</span>
                <span class="ordering-content">${text}</span>
                <span class="ordering-handle"><i class="bi bi-grip-vertical"></i></span>
            </li>
        `;
    });

    html += `
            </ul>
            <p class="ordering-hint" id="orderingHint">*Seret item untuk mengubah urutan (Gunakan ikon garis di kanan untuk HP)</p>
        </div>
    `;

    container.innerHTML = html;

    // Drag & Drop Logic
    const list = document.getElementById('orderingList');
    const header = document.getElementById('orderingHeader');
    const status = document.getElementById('orderingStatus');
    const hint = document.getElementById('orderingHint');
    let draggedItem = null;

    // Function to mark as answered
    function markAsAnswered() {
        hasChanged = true;
        header.classList.add('answered');
        list.classList.add('answered');
        hint.classList.add('answered');
        status.textContent = '✓ Sudah diurutkan';
        list.querySelectorAll('.ordering-number').forEach(num => {
            num.classList.add('answered');
        });
    }

    // If there's an existing answer, mark as answered on initial load
    if (hasExistingAnswer) {
        markAsAnswered();
    }

    list.querySelectorAll('.ordering-item').forEach(item => {
        item.addEventListener('dragstart', function (e) {
            draggedItem = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            list.querySelectorAll('.ordering-item').forEach(i => i.classList.remove('drag-over'));
            updateNumbers();
            markAsAnswered();
            triggerChange();
        });

        item.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (this !== draggedItem) {
                this.classList.add('drag-over');
            }
        });

        item.addEventListener('dragleave', function () {
            this.classList.remove('drag-over');
        });

        item.addEventListener('drop', function (e) {
            e.preventDefault();
            if (this !== draggedItem) {
                // Insert before or after
                const allItems = [...list.querySelectorAll('.ordering-item')];
                const draggedIdx = allItems.indexOf(draggedItem);
                const targetIdx = allItems.indexOf(this);

                if (draggedIdx < targetIdx) {
                    this.after(draggedItem);
                } else {
                    this.before(draggedItem);
                }
            }
            this.classList.remove('drag-over');
        });

        // --- MOBILE TOUCH SUPPORT ---
        let touchEl = null;

        // Only allow dragging via Handle to prevent scroll blocking on the whole list
        const handle = item.querySelector('.ordering-handle');
        if (handle) {
            handle.addEventListener('touchstart', function (e) {
                // Prevent default to stop scrolling immediately
                e.preventDefault();
                touchEl = item;
                item.classList.add('dragging');
                document.body.style.overflow = 'hidden'; // Lock global scroll
            }, { passive: false });

            handle.addEventListener('touchmove', function (e) {
                if (!touchEl) return;
                e.preventDefault();

                const touch = e.touches[0];
                const target = document.elementFromPoint(touch.clientX, touch.clientY);

                if (target) {
                    const targetRow = target.closest('.ordering-item');
                    list.querySelectorAll('.ordering-item').forEach(i => i.classList.remove('drag-over'));

                    if (targetRow && targetRow !== touchEl) {
                        targetRow.classList.add('drag-over');
                    }
                }
            }, { passive: false });

            handle.addEventListener('touchend', function (e) {
                if (!touchEl) return;

                const touch = e.changedTouches[0];
                const target = document.elementFromPoint(touch.clientX, touch.clientY);

                if (target) {
                    const targetRow = target.closest('.ordering-item');
                    if (targetRow && targetRow !== touchEl) {
                        // Swap Logic
                        const allItems = [...list.querySelectorAll('.ordering-item')];
                        const draggedIdx = allItems.indexOf(touchEl);
                        const targetIdx = allItems.indexOf(targetRow);

                        if (draggedIdx < targetIdx) {
                            targetRow.after(touchEl);
                        } else {
                            targetRow.before(touchEl);
                        }
                    }
                }

                // Cleanup
                touchEl.classList.remove('dragging');
                list.querySelectorAll('.ordering-item').forEach(i => i.classList.remove('drag-over'));
                document.body.style.overflow = ''; // Restore scroll
                updateNumbers();
                markAsAnswered();
                triggerChange();
                touchEl = null;
            }, { passive: false });
        }
    });

    function updateNumbers() {
        list.querySelectorAll('.ordering-item').forEach((item, idx) => {
            const numEl = item.querySelector('.ordering-number');
            numEl.textContent = idx + 1;
            if (hasChanged) numEl.classList.add('answered');
        });
    }

    function triggerChange() {
        const newOrder = [];
        list.querySelectorAll('.ordering-item').forEach(item => {
            newOrder.push(parseInt(item.dataset.idx));
        });
        if (onChange) onChange(newOrder);
    }
}

// =====================================================
// TYPE 7: ISIAN RUMPANG (CLOZE TEST) - Fill in Blanks
// =====================================================
function initClozeTest(container, soalText, blanks, answers, onChange) {
    // Inject styles
    if (!document.getElementById('cloze-styles')) {
        const css = `
            .cloze-header {
                background: #f1f5f9; padding: 12px 15px; border-radius: 8px 8px 0 0;
                font-weight: 600; color: #475569; margin-bottom: 0; border: 1px solid #dee2e6;
                border-bottom: none; transition: all 0.3s ease;
            }
            .cloze-header.answered { 
                background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
                color: #155724; border-color: #28a745;
            }
            .cloze-header .status-badge {
                float: right; font-size: 12px; padding: 2px 8px;
                border-radius: 12px; background: #6c757d; color: #fff;
            }
            .cloze-header.answered .status-badge { background: #28a745; }
            .cloze-container { 
                background: #fff; border: 1px solid #dee2e6; border-radius: 0 0 8px 8px; 
                padding: 20px; line-height: 2; font-size: 16px;
                transition: border-color 0.3s ease;
            }
            .cloze-container.answered { border-color: #28a745; }
            .cloze-blank { 
                display: inline-block; min-width: 80px; border: none;
                border-bottom: 4px solid #667eea; background: #f8f9fa;
                padding: 4px 10px; margin: 0 4px; font-size: 15px;
                text-align: center; transition: all 0.2s;
                border-radius: 4px 4px 0 0;
            }
            .cloze-blank:focus { 
                outline: none; background: #e3f2fd; 
                border-bottom-color: #764ba2;
            }
            .cloze-blank.filled { background: #d4edda; border-bottom-color: #28a745; }
            .cloze-hint {
                text-align: center; color: #6c757d; font-style: italic;
                padding: 10px 0 0 0; font-size: 13px; border-top: 1px dashed #dee2e6;
                margin-top: 15px; transition: color 0.3s ease;
            }
            .cloze-hint.answered { color: #28a745; font-weight: 500; }
        `;
        const style = document.createElement('style');
        style.id = 'cloze-styles';
        style.innerHTML = css;
        document.head.appendChild(style);
    }

    // Parse soal text and replace [[blank_id]] with input fields
    // Pattern: [[blank_1]], [[blank_2]], [[1]], [[2]], etc.
    let totalBlanks = 0;
    let processedText = soalText.replace(/\[\[([^\]]+)\]\]/g, function (match, blankId) {
        totalBlanks++;
        const currentValue = answers[blankId] || '';
        const width = Math.max(80, (currentValue.length + 5) * 10);
        return `<input type="text" class="cloze-blank ${currentValue ? 'filled' : ''}" 
                       data-blank="${blankId}" 
                       value="${currentValue}" 
                       style="width: ${width}px;"
                       placeholder="...">`;
    });

    const html = `
        <div class="cloze-header" id="clozeHeader">
            <i class="bi bi-pencil-square me-2"></i> Isi bagian yang kosong
            <span class="status-badge" id="clozeStatus">0 / ${totalBlanks} terisi</span>
        </div>
        <div class="cloze-container" id="clozeContainer">
            ${processedText}
        </div>
        <p class="cloze-hint" id="clozeHint">*Isi bagian yang kosong dengan jawaban yang benar</p>
    `;

    container.innerHTML = html;

    // Get elements
    const clozeHeader = document.getElementById('clozeHeader');
    const clozeContainer = document.getElementById('clozeContainer');
    const clozeHint = document.getElementById('clozeHint');
    const clozeStatus = document.getElementById('clozeStatus');

    // Event listeners
    const inputs = container.querySelectorAll('.cloze-blank');
    let debounceTimer;

    // Function to update status
    function updateStatus() {
        let filledCount = 0;
        inputs.forEach(input => {
            if (input.value.trim().length > 0) filledCount++;
        });

        clozeStatus.textContent = `${filledCount} / ${totalBlanks} terisi`;

        if (filledCount > 0) {
            clozeHeader.classList.add('answered');
            clozeContainer.classList.add('answered');
            clozeHint.classList.add('answered');
            if (filledCount === totalBlanks) {
                clozeStatus.textContent = `✓ Semua terisi (${filledCount}/${totalBlanks})`;
            }
        } else {
            clozeHeader.classList.remove('answered');
            clozeContainer.classList.remove('answered');
            clozeHint.classList.remove('answered');
        }
    }

    // Initial status update
    updateStatus();

    inputs.forEach(input => {
        input.addEventListener('input', function () {
            // Auto-resize
            this.style.width = Math.max(80, (this.value.length + 5) * 10) + 'px';

            // Toggle filled class
            this.classList.toggle('filled', this.value.length > 0);

            // Update status
            updateStatus();

            // Debounce save
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(triggerChange, 800);
        });

        input.addEventListener('blur', triggerChange);
    });

    function triggerChange() {
        const newAnswers = {};
        inputs.forEach(input => {
            const blankId = input.dataset.blank;
            const value = input.value.trim();
            if (value) {
                newAnswers[blankId] = value;
            }
        });
        if (onChange) onChange(newAnswers);
    }
}

// Global Anti-Cheat (Disable Copy-Paste, Right Click, Drag-Drop, External Links)
(function () {
    // Inline toast function to ensure it always works
    function showAntiCheatToast(message) {
        // Try showCbtWarning first
        if (typeof window.showCbtWarning === 'function') {
            window.showCbtWarning(message);
            return;
        }
        // Try showWarningToast as fallback
        if (typeof window.showWarningToast === 'function') {
            window.showWarningToast(message);
            return;
        }
        // Inline toast as last resort
        const existing = document.getElementById('anticheat-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'anticheat-toast';
        toast.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 99999;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000; padding: 15px 25px; border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); font-weight: 600;
            font-size: 14px; animation: slideIn 0.3s ease;
        `;
        toast.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${message}`;
        document.body.appendChild(toast);

        // Add animation keyframes if not exists
        if (!document.getElementById('anticheat-toast-style')) {
            const style = document.createElement('style');
            style.id = 'anticheat-toast-style';
            style.textContent = `@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }`;
            document.head.appendChild(style);
        }

        setTimeout(() => toast.remove(), 3000);
    }

    const events = ['contextmenu', 'copy', 'paste', 'cut', 'drag', 'drop'];
    events.forEach(evt => {
        document.addEventListener(evt, function (e) {
            e.preventDefault();
            if (evt === 'contextmenu') {
                showAntiCheatToast('Klik kanan tidak diperbolehkan!');
            }
            if (evt === 'paste') {
                showAntiCheatToast('Fitur Paste tidak diperbolehkan!');
            }
            if (evt === 'copy') {
                showAntiCheatToast('Fitur Copy tidak diperbolehkan!');
            }
        }, { capture: true });
    });

    // Disable External Links
    document.addEventListener('click', function (e) {
        const link = e.target.tagName === 'A' ? e.target : e.target.closest('a');
        if (link && link.href && !link.href.includes('javascript') && !link.href.includes('#')) {
            e.preventDefault();
        }
    }, { capture: true });
})();

// =====================================================
// RAGU-RAGU FUNCTIONALITY
// =====================================================
function raguRagu(e) {
    const chk = document.getElementById('c_ragu_ragu');
    if (!chk) return;

    if (!e) e = window.event;

    // Jika klik datang dari checkbox itu sendiri, checkbox sudah ter-toggle
    // secara native oleh browser — jangan toggle lagi (akan membatalkan).
    if (!e || e.target !== chk) {
        chk.checked = !chk.checked;
    }

    // Determine status
    const isRagu = chk.checked ? '1' : '0';

    // 1. Update Local Cache
    if (typeof soalCache !== 'undefined' && soalCache[nomorSoal]) {
        soalCache[nomorSoal].soal_ragu = isRagu;
    }

    // 2. Update Modal Navigation UI immediately
    if (typeof updateModalState === 'function') {
        updateModalState();
    }

    // ── GUARD: soal belum dimuat → jangan kirim request ────────────────
    // Dulu request dikirim tanpa id_soal (id_soal=0 → server balas 422).
    if (!soalAktifValid()) {
        console.warn('raguRagu: dilewati, belum ada soal aktif (idSoal=' + idSoal + ')');
        return;
    }

    // 3. Sync to Server
    // Pakai endpoint khusus ragu (tidak menyimpan ulang jawaban siswa)
    try {
        let formData = new FormData(document.getElementById('up'));
        formData.append('soal', idSoal);
        formData.append('ragu', isRagu);

        ajaxPost('cbtsiswa/toggle_ragu', formData).then(response => {
            console.log('Ragu status saved:', response);
            if (!response || response.status === false || response.success === false) {
                showToast((response && response.message) || "Gagal menyimpan status ragu", 'error');
            }
        });
    } catch (e) {
        console.error('Error saving ragu:', e);
    }
}

// =====================================================
// IMAGE ZOOM FUNCTIONALITY
// =====================================================
function initImageZoom() {
    // Inject Styles if not exists
    if (!document.getElementById('zoom-styles')) {
        const css = `
            .zoomable { cursor: zoom-in; transition: opacity 0.2s; }
            .zoomable:hover { opacity: 0.9; }
            
            #zoom-lightbox {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.9); z-index: 99999;
                display: flex; align-items: center; justify-content: center;
                opacity: 0; transition: opacity 0.3s ease;
            }
            #zoom-lightbox.visible { opacity: 1; }
            
            #zoom-lightbox img {
                max-width: 95%; max-height: 95%;
                object-fit: contain;
                transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                cursor: zoom-in;
                transform-origin: center center;
            }
            
            .zoom-level-1 { transform: scale(1.5); cursor: zoom-in !important; }
            .zoom-level-2 { transform: scale(2.2); cursor: zoom-in !important; }
            .zoom-level-3 { transform: scale(3.0); cursor: zoom-out !important; }
            
            #zoom-controls {
                position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%);
                display: flex; gap: 15px; background: rgba(255,255,255,0.15);
                padding: 10px 20px; border-radius: 30px; backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.2);
            }
            .zoom-btn {
                width: 44px; height: 44px; border-radius: 50%; border: none;
                background: rgba(0,0,0,0.5); color: white;
                font-size: 20px; display: flex; align-items: center; justify-content: center;
                cursor: pointer; transition: all 0.2s;
            }
            .zoom-btn:hover { background: rgba(255,255,255,0.9); color: #000; transform: scale(1.1); }
            
            #zoom-close {
                position: absolute; top: 20px; right: 20px;
                width: 44px; height: 44px; border-radius: 50%;
                background: rgba(255,255,255,0.2); color: white;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer; font-size: 28px; transition: all 0.2s; backdrop-filter: blur(5px);
            }
            #zoom-close:hover { background: #e74c3c; transform: rotate(90deg); }
        `;
        const style = document.createElement('style');
        style.id = 'zoom-styles';
        style.innerHTML = css;
        document.head.appendChild(style);
    }

    // Attach to images
    // Target both question content and answer content (for matching items etc)
    const images = document.querySelectorAll('.konten-soal-jawab img, #konten-jawaban img, .matching-item img');
    images.forEach(img => {
        // Skip small icons or mathjax sometimes
        if (img.width < 50 && img.height < 50) return;

        img.classList.add('zoomable');
        // Prevent generic right click prevention from blocking this click if needed
        // but our lightbox uses left click

        // Remove old listeners? No easy way without cloning.
        // We use a property flag
        if (img.dataset.zoomAttached) return;

        img.addEventListener('click', function (e) {
            e.stopPropagation();
            // Get high res src if available (data-original or something), else src
            openZoomLightbox(this.src);
        });
        img.dataset.zoomAttached = "true";
    });
}

function openZoomLightbox(src) {
    // Remove existing lightbox
    const existing = document.getElementById('zoom-lightbox');
    if (existing) existing.remove();

    const lightbox = document.createElement('div');
    lightbox.id = 'zoom-lightbox';

    lightbox.innerHTML = `
        <img src="${src}" id="zoom-img" draggable="false">
        <div id="zoom-close"><i class="bi bi-x"></i></div>
        <div id="zoom-controls">
            <button class="zoom-btn" id="btn-zoom-out" title="Zoom Out"><i class="bi bi-dash"></i></button>
            <button class="zoom-btn" id="btn-zoom-reset" title="Reset"><i class="bi bi-arrows-fullscreen"></i></button>
            <button class="zoom-btn" id="btn-zoom-in" title="Zoom In"><i class="bi bi-plus"></i></button>
        </div>
    `;

    document.body.appendChild(lightbox);

    // Animation frame for transition
    requestAnimationFrame(() => lightbox.classList.add('visible'));

    const imgInfo = {
        element: document.getElementById('zoom-img'),
        level: 0, // 0=fit, 1=1.5x, 2=2x, 3=3x
        maxLevel: 3
    };

    // Zoom Logic
    const updateZoom = () => {
        imgInfo.element.className = ''; // remove all zoom classes
        if (imgInfo.level > 0) imgInfo.element.classList.add(`zoom-level-${imgInfo.level}`);

        // Update cursor
        if (imgInfo.level >= imgInfo.maxLevel) {
            imgInfo.element.style.cursor = 'zoom-out';
        } else {
            imgInfo.element.style.cursor = 'zoom-in';
        }
    };

    // Click Image to Zoom Cycle
    imgInfo.element.addEventListener('click', function (e) {
        e.stopPropagation();
        imgInfo.level++;
        if (imgInfo.level > imgInfo.maxLevel) imgInfo.level = 0;
        updateZoom();
    });

    // Buttons
    document.getElementById('btn-zoom-in').onclick = (e) => {
        e.stopPropagation();
        if (imgInfo.level < imgInfo.maxLevel) {
            imgInfo.level++;
            updateZoom();
        }
    };

    document.getElementById('btn-zoom-out').onclick = (e) => {
        e.stopPropagation();
        if (imgInfo.level > 0) {
            imgInfo.level--;
            updateZoom();
        }
    };

    document.getElementById('btn-zoom-reset').onclick = (e) => {
        e.stopPropagation();
        imgInfo.level = 0;
        updateZoom();
    };

    // Close Logic
    const closeLightbox = () => {
        lightbox.classList.remove('visible');
        setTimeout(() => lightbox.remove(), 300);
    };

    document.getElementById('zoom-close').onclick = closeLightbox;
    lightbox.onclick = (e) => {
        if (e.target.id !== 'zoom-controls' && !e.target.closest('#zoom-controls')) {
            closeLightbox();
        }
    };
}
