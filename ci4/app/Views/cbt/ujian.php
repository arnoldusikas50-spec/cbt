<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>Ujian:
        <?= esc($subjudul) ?>
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #11998e;
            --secondary: #38ef7d;
            --dark-bg: #0f0c29;
            --card-bg: rgba(255, 255, 255, 0.08);
            --text-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #1a1a2e;
            color: var(--text-color);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .exam-header {
            height: 60px;
            background: rgba(30, 30, 50, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 10;
        }

        .exam-title {
            font-weight: 600;
            font-size: 1.1rem;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .timer-badge {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-family: monospace;
            font-size: 1.2rem;
            box-shadow: 0 0 10px rgba(229, 62, 62, 0.4);
        }

        .timer-badge.warning {
            background: linear-gradient(135deg, #d69e2e, #b7791f);
        }

        /* Layout */
        .exam-container {
            flex: 1;
            display: flex;
            overflow: hidden;
            position: relative;
        }

        /* Sidebar (Numbers) */
        .exam-sidebar {
            width: 320px;
            background: #161625;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            z-index: 9;
        }

        .sidebar-header {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-weight: 600;
            background: #1e1e2d;
        }

        .number-grid {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            align-content: start;
        }

        .nav-btn {
            aspect-ratio: 1;
            border: 1px solid #333;
            background: #252535;
            color: #fff;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .nav-btn:hover {
            background: #303040;
        }

        .nav-btn.active {
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(56, 239, 125, 0.3);
        }

        .nav-btn.answered {
            background: var(--primary);
            border-color: var(--primary);
        }

        .nav-btn.ragu {
            background: #d69e2e;
            border-color: #d69e2e;
        }

        .nav-btn .badge-ragu {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            display: none;
        }

        .nav-btn.ragu .badge-ragu {
            display: block;
        }

        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }

        /* Main Content */
        .exam-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #1a1a2e;
        }

        .question-area {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .question-card {
            background: #252535;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .q-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-bottom: 10px;
            color: #aaa;
        }

        .q-image {
            max-width: 100%;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #444;
        }

        /* Options */
        .options-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }

        .option-item {
            background: rgba(0, 0, 0, 0.2);
            border: 2px solid #333;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: start;
            gap: 12px;
        }

        .option-item:hover {
            background: rgba(56, 239, 125, 0.05);
            border-color: #444;
        }

        .option-item.selected {
            border-color: var(--primary);
            background: rgba(17, 153, 142, 0.15);
            box-shadow: 0 0 15px rgba(17, 153, 142, 0.1);
        }

        .option-marker {
            width: 28px;
            height: 28px;
            background: #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .option-item.selected .option-marker {
            background: var(--primary);
            color: white;
        }

        /* Footer Controls */
        .exam-controls {
            height: 70px;
            background: #1e1e2d;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }

        .ragu-check {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .ragu-check input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #d69e2e;
        }

        .btn-nav {
            min-width: 120px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-prev {
            background: #333;
            color: #ccc;
            border: 1px solid #444;
        }

        .btn-next {
            background: var(--primary);
            color: #fff;
            border: none;
        }

        .btn-finish {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
            border: none;
        }

        /* Loader */
        .loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(26, 26, 46, 0.9);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .exam-sidebar {
                position: fixed;
                right: 0;
                top: 60px;
                bottom: 0;
                transform: translateX(100%);
            }

            .exam-sidebar.open {
                transform: translateX(0);
            }

            .toggle-sidebar {
                display: block;
            }

            .exam-controls {
                padding: 0 15px;
            }

            .btn-nav {
                min-width: auto;
                padding: 10px 15px;
            }

            .btn-text {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?php
    // Prepare data
    // Remove keys for security equivalent (though View source still sees it if not careful, unset here ensures PHP view output doesn't have it)
    $cleanQuestions = [];
    foreach ($questions as $q) {
        $clean = clone $q;
        unset($clean->jawaban);
        unset($clean->kunci); // Just in case
        $cleanQuestions[] = $clean;
    }
    ?>

    <!-- Header -->
    <header class="exam-header">
        <div class="d-flex align-items-center gap-3">
                <div class="logo-icon bg-gradient-primary text-white rounded p-1">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="exam-title">
                <?= esc($jadwal->nama_mapel ?? $jadwal->bank_nama) ?>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-light btn-sm d-md-none" onclick="toggleSidebar()">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </button>
            <div id="timer" class="timer-badge">00:00:00</div>
        </div>
    </header>

    <div class="exam-container">
        <!-- Main Area -->
        <main class="exam-main">
            <div id="questionContainer" class="question-area">
                <!-- Content injected by JS -->
            </div>

            <footer class="exam-controls">
                <button id="btnPrev" class="btn btn-nav btn-prev" onclick="navStep(-1)">
                    <i class="bi bi-chevron-left"></i> <span class="btn-text">Sebelumnya</span>
                </button>

                <label class="ragu-check">
                    <input type="checkbox" id="checkRagu" onchange="toggleRagu()">
                    <span class="text-warning"><i class="bi bi-flag"></i> Ragu-ragu</span>
                </label>

                <button id="btnNext" class="btn btn-nav btn-next" onclick="navStep(1)">
                    <span class="btn-text">Selanjutnya</span> <i class="bi bi-chevron-right"></i>
                </button>

                <button id="btnFinish" class="btn btn-nav btn-finish d-none" onclick="finishExam()">
                    <i class="bi bi-check-circle"></i> <span class="btn-text">Selesai</span>
                </button>
            </footer>
        </main>

        <!-- Sidebar -->
        <aside id="sidebar" class="exam-sidebar">
            <div class="sidebar-header">
                Daftar Soal
                <span class="float-end badge bg-secondary">
                    <?= count($questions) ?> Soal
                </span>
            </div>
            <div class="number-grid" id="navGrid">
                <!-- Grid items injected by JS -->
            </div>
            <div class="sidebar-footer">
                <small class="text-muted d-block mb-2">Klik nomor untuk navigasi</small>
                <div class="d-flex justify-content-center gap-2 small">
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-primary rounded-circle"
                            style="width:10px;height:10px"></span> Isi</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-warning rounded-circle"
                            style="width:10px;height:10px"></span> Ragu</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-dark border rounded-circle"
                            style="width:10px;height:10px"></span> Kosong</span>
                </div>
            </div>
        </aside>
    </div>

    <!-- Loading Overlay -->
    <div id="loader" class="loader-overlay d-none">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-white">Menyimpan jawaban...</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const QUESTIONS = <?= json_encode($cleanQuestions) ?>;
        const ANSWERS = <?= json_encode($answers) ?>; // Object {id_soal: {data}}
        const ID_JADWAL = <?= $jadwal->id_jadwal ?>;
        const BASE_URL = '<?= base_url() ?>';
        const CSRF_NAME = '<?= csrf_token() ?>';
        let CSRF_HASH = '<?= csrf_hash() ?>';
        let SISA_WAKTU = <?= $sisa_waktu ?>;

        let currentIdx = 0;
        let timerInterval;

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            initGrid();
            loadQuestion(0);
            startTimer();
            setupKeyboard();
        });

        // Timer Logic
        function startTimer() {
            const timerEl = document.getElementById('timer');

            function tick() {
                if (SISA_WAKTU <= 0) {
                    clearInterval(timerInterval);
                    timerEl.innerHTML = "00:00:00";
                    autoSubmit();
                    return;
                }

                SISA_WAKTU--;

                const h = Math.floor(SISA_WAKTU / 3600).toString().padStart(2, '0');
                const m = Math.floor((SISA_WAKTU % 3600) / 60).toString().padStart(2, '0');
                const s = (SISA_WAKTU % 60).toString().padStart(2, '0');

                timerEl.textContent = `${h}:${m}:${s}`;

                if (SISA_WAKTU < 300) { // 5 min warning
                    timerEl.classList.add('warning');
                }
            }

            tick();
            timerInterval = setInterval(tick, 1000);
        }

        // Navigation
        function initGrid() {
            const grid = document.getElementById('navGrid');
            grid.innerHTML = '';

            QUESTIONS.forEach((q, idx) => {
                const btn = document.createElement('button');
                btn.className = `nav-btn`;
                btn.id = `nav-${idx}`;
                btn.textContent = idx + 1;
                btn.onclick = () => loadQuestion(idx);

                // Check status
                const ans = ANSWERS[q.id_soal];
                if (ans) {
                    if (ans.jawaban) btn.classList.add('answered');
                    if (ans.ragu == '1') btn.classList.add('ragu');
                }

                // Ragu marker
                const badge = document.createElement('span');
                badge.className = 'badge-ragu';
                btn.appendChild(badge);

                grid.appendChild(btn);
            });
            updateNavActive();
        }

        function updateNavActive() {
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            const btn = document.getElementById(`nav-${currentIdx}`);
            if (btn) {
                btn.classList.add('active');
                btn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function loadQuestion(idx) {
            if (idx < 0 || idx >= QUESTIONS.length) return;
            currentIdx = idx;

            const q = QUESTIONS[idx];
            const ans = ANSWERS[q.id_soal] || {};

            const container = document.getElementById('questionContainer');

            // Generate HTML
            let optionsHtml = '';
            const opsis = ['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];
            const labels = ['A', 'B', 'C', 'D', 'E'];

            opsis.forEach((key, i) => {
                if (q[key]) {
                    const isSelected = ans.jawaban === key; // e.g., 'opsi_a'
                    const label = labels[i];

                    optionsHtml += `
                        <div class="option-item ${isSelected ? 'selected' : ''}" onclick="selectOption('${key}')">
                            <div class="option-marker">${label}</div>
                            <div class="option-text">${q[key]}</div>
                        </div>
                    `;
                }
            });

            const content = `
                <div class="question-card">
                    <div class="q-badge">Soal No. ${idx + 1}</div>
                    <div class="question-text mb-3">
                        ${q.soal}
                    </div>
                    ${q.file ? `<img src="${BASE_URL}/uploads/bank_soal/${q.file}" class="q-image">` : ''}
                    
                    <div class="options-list">
                        ${optionsHtml}
                    </div>
                </div>
            `;

            container.innerHTML = content;

            // Update controls
            document.getElementById('checkRagu').checked = (ans.ragu == '1');

            const btnPrev = document.getElementById('btnPrev');
            const btnNext = document.getElementById('btnNext');
            const btnFinish = document.getElementById('btnFinish');

            if (idx === 0) btnPrev.style.visibility = 'hidden'; else btnPrev.style.visibility = 'visible';

            if (idx === QUESTIONS.length - 1) {
                btnNext.classList.add('d-none');
                btnFinish.classList.remove('d-none');
            } else {
                btnNext.classList.remove('d-none');
                btnFinish.classList.add('d-none');
                btnFinish.classList.add('d-none');
            }

            updateNavActive();
        }

        function navStep(step) {
            loadQuestion(currentIdx + step);
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        // Interaction
        window.selectOption = async function (key) {
            const q = QUESTIONS[currentIdx];

            // Update UI immediately (optimistic)
            document.querySelectorAll('.option-item').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');

            // Update local state
            if (!ANSWERS[q.id_soal]) ANSWERS[q.id_soal] = {};
            ANSWERS[q.id_soal].jawaban = key;
            ANSWERS[q.id_soal].id_soal = q.id_soal; // ensure ID exists

            // Update nav grid
            const navBtn = document.getElementById(`nav-${currentIdx}`);
            navBtn.classList.add('answered');

            // Send to server
            await saveAnswer(q.id_soal, key, document.getElementById('checkRagu').checked);
        };

        window.toggleRagu = async function () {
            const q = QUESTIONS[currentIdx];
            const isRagu = document.getElementById('checkRagu').checked;

            // Update local
            if (!ANSWERS[q.id_soal]) ANSWERS[q.id_soal] = {};
            ANSWERS[q.id_soal].ragu = isRagu ? '1' : '0';

            // Update nav
            const navBtn = document.getElementById(`nav-${currentIdx}`);
            if (isRagu) navBtn.classList.add('ragu'); else navBtn.classList.remove('ragu');

            // Send to server
            // Note: If no answer yet, we just toggle ragu. 
            // Better to use specific endpoint or same save endpoint
            await saveAnswer(q.id_soal, ANSWERS[q.id_soal].jawaban || '', isRagu);
        };

        async function saveAnswer(idSoal, jawaban, ragu) {
            const formData = new FormData();
            formData.append('id_jadwal', ID_JADWAL);
            formData.append('id_soal', idSoal);
            formData.append('jawaban', jawaban);
            formData.append('ragu', ragu ? '1' : '0');
            formData.append(CSRF_NAME, CSRF_HASH);

            try {
                // Silent save usually, but can show small indicator
                const res = await fetch('<?= site_url('cbtsiswa/save_jawaban') ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                // Update CSRF for next request if rotated? CI4 csrf rotation might be an issue for concurrent requests.
                // If rotation is on, we need data.token. For now assume safe or single stream.
            } catch (e) {
                console.error('Save failed', e);
                // Optionally show error toast
            }
        }

        // Finishing
        window.finishExam = function () {
            // Check if all answered
            const answeredCount = Object.values(ANSWERS).filter(a => a.jawaban).length;
            const total = QUESTIONS.length;

            Swal.fire({
                title: 'Selesaikan Ujian?',
                html: `
                    <p>Anda telah menjawab <b>${answeredCount}</b> dari <b>${total}</b> soal.</p>
                    ${answeredCount < total ? '<p class="text-danger">Masih ada soal yang belum dijawab!</p>' : ''}
                    <p>Setelah selesai, Anda tidak dapat mengubah jawaban lagi.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesai!',
                cancelButtonText: 'Belum',
                confirmButtonColor: '#e53e3e'
            }).then(res => {
                if (res.isConfirmed) submitFinish();
            });
        };

        function submitFinish() {
            Swal.fire({ title: 'Menyimpan...', didOpen: () => Swal.showLoading() });

            const formData = new FormData();
            formData.append('id_jadwal', ID_JADWAL);
            formData.append(CSRF_NAME, CSRF_HASH);

            fetch('<?= site_url('cbtsiswa/selesai_ujian') ?>', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire('Berhasil', 'Ujian selesai.', 'success').then(() => {
                            window.location.href = data.redirect;
                        });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(e => Swal.fire('Error', 'Terjadi kesalahan koneksi', 'error'));
        }

        function autoSubmit() {
            Swal.fire({
                title: 'Waktu Habis!',
                text: 'Ujian akan dikumpulkan otomatis.',
                icon: 'warning',
                allowOutsideClick: false,
                showConfirmButton: false,
                timer: 3000
            }).then(() => {
                submitFinish();
            });
        }

        // Keyboard support
        function setupKeyboard() {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') navStep(1);
                if (e.key === 'ArrowLeft') navStep(-1);
            });
        }
    </script>
</body>

</html>