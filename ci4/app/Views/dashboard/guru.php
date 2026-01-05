<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="app-main">
    <div class="container-fluid pt-4">
        <!-- Info Boxes -->
        <div class="row m-0">
            <?php
            $colors = ['bg-gradient-primary', 'bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-danger', 'bg-gradient-indigo', 'bg-gradient-purple', 'bg-gradient-teal', 'bg-gradient-pink', 'bg-gradient-navy'];
            $i = 0;
            foreach ($info_box as $info):
                $mapTitleColor = [
                    'Siswa' => 'bg-gradient-blue',
                    'Rombel' => 'bg-gradient-indigo',
                    'Guru' => 'bg-gradient-teal',
                    'Wali Kelas' => 'bg-gradient-orange',
                    'Mapel' => 'bg-gradient-success',
                    'Ekstrakurikuler' => 'bg-gradient-cyan',
                    'Arsip Rapor' => 'bg-gradient-maroon',
                    'Jadwal' => 'bg-gradient-danger'
                ];
                $bgClass = isset($mapTitleColor[$info->title]) ? $mapTitleColor[$info->title] : $colors[$i % count($colors)];
                $i++;
                ?>
                <div class="col-6 col-md-3 col-lg-2 mb-4">
                    <div class="info-box <?= $bgClass ?> shadow-sm h-100 position-relative overflow-hidden border-0"
                        style="border-radius: 12px; min-height: 100px;">
                        <div class="info-box-content text-white">
                            <span class="info-box-text text-uppercase fw-bold opacity-75"
                                style="font-size: 0.7rem; letter-spacing: 0.5px;"><?= $info->title; ?></span>
                            <span class="info-box-number fw-bold" style="font-size: 1.8rem;"><?= $info->total; ?></span>
                        </div>
                        <div class="position-absolute" style="top: 10px; right: 10px; opacity: 0.2;">
                            <?php
                            $iconMap = [
                                'users' => 'people-fill',
                                'chalkboard-teacher' => 'person-video3',
                                'book' => 'journal-bookmark-fill',
                                'user' => 'person-fill',
                                'graduation-cap' => 'mortarboard-fill',
                                'university' => 'bank2',
                                'tags' => 'tags-fill',
                                'list-alt' => 'list-task',
                                'tasks' => 'list-check',
                                'envelope' => 'envelope-fill',
                                'comments' => 'chat-dots-fill',
                                'clipboard-list' => 'clipboard-data',
                                'cogs' => 'gears',
                                'cog' => 'gear-fill',
                                'desktop' => 'pc-display',
                                'server' => 'hdd-network',
                                'bullhorn' => 'megaphone-fill',
                                'pie-chart' => 'pie-chart-fill',
                                'home' => 'house-door-fill',
                                'print' => 'printer-fill',
                                'user-tie' => 'person-badge-fill',
                                'id-badge' => 'person-vcard-fill',
                                'address-card' => 'person-vcard-fill',
                                'running' => 'person-walking',
                                'child' => 'person-arms-up',
                                'bell' => 'bell-fill',
                                'shapes' => 'collection-fill',
                                'clock' => 'clock-fill',
                                'calendar-check' => 'calendar-check-fill',
                                'calendar-alt' => 'calendar-date-fill',
                                'edit' => 'pencil-square',
                            ];
                            $biIcon = isset($iconMap[$info->icon]) ? $iconMap[$info->icon] : $info->icon;
                            ?>
                            <i class="bi bi-<?= $biIcon ?>" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-white my-shadow mb-4 border-0">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold m-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Penilaian &
                                Token</h5>
                            <button class="btn btn-sm btn-light border" id="refresh-token">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Token
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <?php foreach ($ujian_box as $info): ?>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <div class="info-box mb-3 bg-light shadow-none border" style="min-height: 70px;">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted small"><?= $info->title; ?></span>
                                            <span
                                                class="info-box-number text-danger h5 mb-0 fw-bold"><?= $info->total; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="info-box mb-3 bg-primary text-white shadow-none border-0"
                                    style="min-height: 70px;">
                                    <div class="info-box-content">
                                        <span class="info-box-text small opacity-75">Token Aktif <small
                                                class="float-end" id="interval"></small></span>
                                        <span class="info-box-number h5 mb-0 fw-bold" id="token-view">
                                            <?= $token->token != null ? $token->token : '- - - - - -' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="alert alert-info border-0 shadow-sm text-center py-2 mb-4">
                                <b class="text-uppercase tracking-wider" style="font-size: 0.8rem;"><i
                                        class="bi bi-clock-fill me-2"></i>JADWAL PENILAIAN HARI INI</b>
                            </div>
                            <div class="table-responsive" id="table-container">
                                <!-- Table populated by JS -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                                    <span class="ms-2 text-muted">Memuat jadwal...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card card-white my-shadow mb-3 border-0">
                    <div class="card-header border-0">
                        <h5 class="fw-bold m-0"><i class="bi bi-megaphone me-2 text-danger"></i>Informasi & Pengumuman
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="konten-pengumuman px-4 pb-4">
                            <div id="pengumuman">
                                <!-- Announcements populated by JS -->
                            </div>
                            <div id="loading-post" class="text-center d-none py-4">
                                <div class="spinner-border text-danger" role="status"></div>
                                <div class="mt-2 text-muted small">Memuat pengumuman...</div>
                            </div>
                            <div id="loadmore-post" onclick="getPosts()" class="text-center mt-3 loadmore d-none">
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-4">Muat Pengumuman
                                    Lainnya</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Comments & Replies -->
<div class="modal fade" id="komentarModal" tabindex="-1" aria-labelledby="komentarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="komentarLabel">
                    <i class="bi bi-chat-dots-fill me-2 text-primary"></i>Tulis Komentar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="komentar">
                    <?= csrf_field() ?>
                    <input type="hidden" id="id-post" name="id_post" value="">
                    <div class="d-flex align-items-start gap-3">
                        <div class="user-avatar" style="width: 45px; height: 45px; flex-shrink: 0;">
                            <img class="img-fluid rounded-circle shadow-sm border"
                                src="<?= base_url('assets/img/guru.png') ?>" alt="Avatar">
                        </div>
                        <div class="w-100">
                            <textarea name="text" class="form-control bg-light border-0" rows="4"
                                placeholder="Bagikan komentar Anda..." required style="border-radius: 12px;"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Kirim
                            Komentar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="balasanModal" tabindex="-1" aria-labelledby="balasanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="balasanLabel">
                    <i class="bi bi-reply-fill me-2 text-primary"></i>Balas Komentar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="balasan">
                    <?= csrf_field() ?>
                    <input type="hidden" id="id-comment" name="id_comment" value="">
                    <div class="d-flex align-items-start gap-3">
                        <div class="user-avatar" style="width: 45px; height: 45px; flex-shrink: 0;">
                            <img class="img-fluid rounded-circle shadow-sm border"
                                src="<?= base_url('assets/img/guru.png') ?>" alt="Avatar">
                        </div>
                        <div class="w-100">
                            <textarea name="text" class="form-control bg-light border-0" rows="3"
                                placeholder="Tulis balasan Anda..." required style="border-radius: 12px;"></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Kirim Balasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let timerTokenView;
    var halaman = 0;
    var idGuru = "<?= $guru->id_guru ?>";
    const base_url = '<?= base_url() ?>/';

    $(document).ready(function () {
        getPosts();

        if (typeof getToken === 'function') {
            getToken(() => getGlobalToken());
        }

        $('#refresh-token').click(function () {
            if (typeof getToken === 'function') getToken(() => getGlobalToken());
        });

        // Load Realtime Stats
        setTimeout(function () {
            $.ajax({
                url: base_url + 'dashboard/get_status_siswa',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    createTableFromJSON(response);
                },
                error: function () {
                    $('#table-container').html('<div class="alert alert-warning text-center">Gagal memuat jadwal penilaian</div>');
                }
            });
        }, 500);
    });

    function getGlobalToken() {
        if (typeof globalToken !== 'undefined' && globalToken != null) {
            const viewToken = $('#token-view');
            if (viewToken.length) viewToken.text(globalToken.token);
            // interval timer logic could be added if needed, similar to CI3
        }
    }

    // Reuse the same logic for table and posts from CI3 but cleaned up for BS5
    function createTime(d) {
        const date = new Date(d);
        const options = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
        return date.toLocaleTimeString('id-ID', options) + ', ' + date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function addPosts(response) {
        let card = '';
        if (response && response.length > 0) {
            response.forEach(v => {
                let dari = v.dari == '0' ? 'Admin' : (v.dari_group == '2' ? v.nama_guru : v.nama_siswa);
                let foto = v.dari == '0' ? base_url + 'assets/img/favicon.png' : (v.dari_group == '2' ? (v.foto ? base_url + v.foto : base_url + 'assets/img/guru.png') : (v.foto_siswa ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png'));

                card += `
                <div class="card mb-3 border shadow-none" style="border-radius: 12px; border-left: 5px solid #667eea !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <img class="rounded-circle border" src="${foto}" width="40" height="40" style="object-fit: cover;">
                            <div class="ms-2">
                                <h6 class="fw-bold m-0 small">${dari}</h6>
                                <small class="text-muted" style="font-size: 0.7rem;">${createTime(v.tanggal)}</small>
                            </div>
                        </div>
                        <div class="post-content bg-white p-2 rounded small" style="color: #444; line-height: 1.5;">
                            ${v.text}
                        </div>
                        <div class="d-flex justify-content-end mt-2 gap-2">
                            <button class="btn btn-link btn-xs text-decoration-none text-primary fw-bold" data-id="${v.id_post}" data-bs-toggle="modal" data-bs-target="#komentarModal">
                                <i class="bi bi-chat-text me-1"></i> Komentar (${v.jml})
                            </button>
                        </div>
                    </div>
                </div>`;
            });
        }
        $('#pengumuman').html(card || '<div class="text-center py-4 text-muted">Belum ada pengumuman hari ini.</div>');
    }

    function getPosts() {
        $('#loading-post').removeClass('d-none');
        $.ajax({
            url: base_url + "pengumuman/getpost/" + halaman,
            type: "GET",
            dataType: "json",
            success: function (response) {
                halaman += 1;
                addPosts(response);
                $('#loading-post').addClass('d-none');
                if (response && response.length === 5) $('#loadmore-post').removeClass('d-none');
            },
            error: function () {
                $('#loading-post').addClass('d-none');
            }
        });
    }

    // Ported Table JSON logic
    async function createTableFromJSON(data) {
        const container = document.getElementById('table-container');
        if (!data.ruangs || !data.jadwals_ujian || data.jadwals_ujian.length === 0) {
            container.innerHTML = '<div class="alert alert-light border text-center text-muted m-0">Tidak ada jadwal penilaian aktif hari ini</div>';
            return;
        }

        // Simplified table rendering for brevity and performance
        let html = `<table class="table table-sm table-bordered m-0 small shadow-sm">
            <thead class="bg-light text-center align-middle fw-bold text-uppercase">
                <tr>
                    <th rowspan="2">Ruang / Sesi</th>
                    <th rowspan="2">Mapel (Bank Soal)</th>
                    <th rowspan="2">Kelas</th>
                    <th rowspan="2">Jml</th>
                    <th colspan="3">Status</th>
                </tr>
                <tr>
                    <th class="bg-danger bg-opacity-10 text-danger">Blm</th>
                    <th class="bg-info bg-opacity-10 text-info">Sdg</th>
                    <th class="bg-success bg-opacity-10 text-success">Sls</th>
                </tr>
            </thead>
            <tbody>`;

        // We'll perform a basic grouping for display
        data.jadwals_ujian.forEach(jadwal => {
            const targetClasses = (jadwal.bank?.bank_kelas || []).map(String);
            const ruangs = data.ruangs.filter(r => r.kelas_id.some(id => targetClasses.includes(String(id))));

            ruangs.forEach(ruang => {
                const siswaRuang = data.sesisiswa.filter(s => s.ruang_id === ruang.ruang_id && s.sesi_id === ruang.sesi_id && targetClasses.includes(String(s.kelas_id)));

                let blm = 0, sdg = 0, sls = 0;
                siswaRuang.forEach(s => {
                    const cbt = data.cbtsiswa.find(c => c.id_siswa === s.siswa_id && c.jadwal?.id_jadwal === jadwal.id_jadwal);
                    if (!cbt || !cbt.mulai) blm++;
                    else if (!cbt.selesai) sdg++;
                    else sls++;
                });

                html += `<tr class="align-middle">
                    <td class="text-center fw-bold text-dark pe-3">
                        <div class="div">${ruang.nama_ruang}</div>
                        <div class="small fw-normal text-muted">Sesi: ${ruang.nama_sesi}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-primary">${jadwal.bank.mapel.kode}</div>
                        <div class="text-muted smaller">${jadwal.bank.bank_kode}</div>
                    </td>
                    <td class="text-center">
                        ${targetClasses.map(tid => `<span class="badge bg-light text-dark border me-1">${ruang.nama_kelas[tid] || tid}</span>`).join('')}
                    </td>
                    <td class="text-center fw-bold">${siswaRuang.length}</td>
                    <td class="text-center text-danger">${blm}</td>
                    <td class="text-center text-info">${sdg}</td>
                    <td class="text-center text-success fw-bold">${sls}</td>
                </tr>`;
            });
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }
</script>
<?= $this->endSection() ?>