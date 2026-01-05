<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>CBT Ujian -
        <?= esc($setting->nama_sekolah ?? 'CBT AKM') ?>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            color: white;
        }

        /* Header */
        .cbt-header {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .cbt-header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cbt-header .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .cbt-header .school-name {
            font-size: 1rem;
            font-weight: 600;
        }

        .cbt-header .subtitle {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Student Card */
        .student-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 20px;
            margin: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .student-details h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .student-details p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin: 0;
        }

        /* Exam List */
        .exam-list {
            padding: 0 20px 20px;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--secondary);
        }

        .exam-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .exam-card:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .exam-card.selesai {
            border-left: 4px solid #48bb78;
        }

        .exam-card.sedang {
            border-left: 4px solid #ed8936;
            animation: pulse 2s infinite;
        }

        .exam-card.belum {
            border-left: 4px solid #4299e1;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .exam-mapel {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .exam-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .exam-badge.selesai {
            background: rgba(72, 187, 120, 0.2);
            color: #68d391;
        }

        .exam-badge.sedang {
            background: rgba(237, 137, 54, 0.2);
            color: #f6ad55;
        }

        .exam-badge.belum {
            background: rgba(66, 153, 225, 0.2);
            color: #63b3ed;
        }

        .exam-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .exam-info i {
            margin-right: 8px;
            width: 16px;
            text-align: center;
        }

        .btn-exam {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-start {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
        }

        .btn-continue {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            color: white;
        }

        .btn-continue:hover {
            box-shadow: 0 10px 25px rgba(237, 137, 54, 0.4);
        }

        .btn-finished {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 40px;
            color: rgba(255, 255, 255, 0.6);
        }

        .loading .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.2);
            border-top-color: var(--secondary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Footer */
        .cbt-footer {
            text-align: center;
            padding: 20px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="cbt-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="logo">
                    <div class="logo-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <div class="school-name">
                        <?= esc($setting->nama_sekolah ?? 'CBT AKM') ?>
                    </div>
                    <div class="subtitle">Computer Based Test</div>
                </div>
            </div>
            <a href="<?= site_url('auth/logout') ?>" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Keluar
            </a>
        </div>
    </header>

    <!-- Student Card -->
    <div class="student-card">
        <div class="student-info">
                <div class="student-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="student-details">
                <h4 id="studentName">
                    <?= esc($siswa->nama ?? 'Memuat...') ?>
                </h4>
                <p>
                    <span id="studentNis">NIS:
                        <?= esc($siswa->nis ?? '-') ?>
                    </span> |
                    <span id="studentKelas">Kelas:
                        <?= esc($siswa->nama_kelas ?? '-') ?>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Exam List -->
    <div class="exam-list">
        <div class="section-title">
            <i class="bi bi-clipboard-data"></i>
            Daftar Ujian Hari Ini
        </div>

        <div id="examContainer">
            <div class="loading">
                <div class="spinner"></div>
                <p>Memuat daftar ujian...</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="cbt-footer">
        <p>&copy;
            <?= date('Y') ?>
            <?= esc($setting->nama_sekolah ?? 'CBT AKM') ?>
        </p>
        <p>Powered by CodeIgniter 4</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Load exams on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadExams();

            // Refresh every 30 seconds
            setInterval(loadExams, 30000);
        });

        async function loadExams() {
            try {
                const response = await fetch('<?= site_url('cbtsiswa/get_cbt_siswa') ?>');
                const data = await response.json();

                if (!data.status) {
                    showError(data.message || 'Gagal memuat data');
                    return;
                }

                renderExams(data.jadwals);
            } catch (error) {
                console.error('Error:', error);
                showError('Gagal memuat daftar ujian');
            }
        }

        function renderExams(jadwals) {
            const container = document.getElementById('examContainer');

            if (!jadwals || jadwals.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h5>Tidak Ada Ujian</h5>
                        <p>Tidak ada ujian yang tersedia untuk hari ini.</p>
                    </div>
                `;
                return;
            }

            let html = '';

            jadwals.forEach(jadwal => {
                const statusClass = jadwal.status_siswa;
                const statusText = {
                    'selesai': 'Selesai',
                    'sedang': 'Sedang Ujian',
                    'belum': 'Belum Mulai'
                }[statusClass] || 'Belum Mulai';

                let btnHtml = '';
                    if (statusClass === 'selesai') {
                    btnHtml = `<button class="btn-exam btn-finished" disabled>
                        <i class="bi bi-check-circle"></i> Ujian Selesai
                    </button>`;
                } else if (statusClass === 'sedang') {
                    btnHtml = `<button class="btn-exam btn-continue" onclick="startExam(${jadwal.id_jadwal})">
                        <i class="bi bi-play-circle"></i> Lanjutkan Ujian
                    </button>`;
                } else {
                    btnHtml = `<button class="btn-exam btn-start" onclick="startExam(${jadwal.id_jadwal})">
                        <i class="bi bi-rocket"></i> Mulai Ujian
                    </button>`;
                }

                html += `
                    <div class="exam-card ${statusClass}">
                        <div class="exam-header">
                            <div class="exam-mapel">${jadwal.nama_mapel || jadwal.bank_nama || 'Ujian'}</div>
                            <span class="exam-badge ${statusClass}">${statusText}</span>
                        </div>
                        <div class="exam-info">
                            <div><i class="bi bi-calendar-event"></i>${formatDate(jadwal.tgl_mulai)}</div>
                            <div><i class="bi bi-clock"></i>${jadwal.durasi || 60} menit</div>
                            <div><i class="bi bi-file-text"></i>${jadwal.bank_kode || '-'}</div>
                            <div><i class="bi bi-list-ol"></i>${jadwal.jml_soal || '-'} soal</div>
                        </div>
                        ${btnHtml}
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        async function startExam(idJadwal) {
            const result = await Swal.fire({
                title: 'Konfirmasi',
                html: `
                    <p>Anda akan memulai ujian ini.</p>
                    <p class="text-muted small">Pastikan koneksi internet stabil.</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-play-fill"></i> Mulai Ujian',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#11998e'
            });

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Mempersiapkan Ujian...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const formData = new FormData();
                formData.append('id_jadwal', idJadwal);
                formData.append('<?= csrf_token() ?>', csrfToken);

                const response = await fetch('<?= site_url('cbtsiswa/validasi_jadwal') ?>', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Memulai ujian...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect to exam page
                        window.location.href = '<?= site_url('cbtsiswa/ujian') ?>/' + idJadwal;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Tidak dapat memulai ujian'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan. Silakan coba lagi.'
                });
            }
        }

        function showError(message) {
            document.getElementById('examContainer').innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    <h5>Error</h5>
                    <p>${message}</p>
                    <button class="btn btn-outline-light btn-sm mt-3" onclick="loadExams()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi
                    </button>
                </div>
            `;
        }
    </script>
</body>

</html>