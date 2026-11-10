/**
 * Dashboard JavaScript - CI4 Version
 * Handles real-time updates, exam schedules, announcements, and activity logs
 */

// Wrap in IIFE to prevent duplicate declaration errors
(function () {
    'use strict';

    var halaman = 0;
    var adaJadwalUjian = '0';

    // Initialize
    $(document).ready(function () {
        // Check if there's exam schedule today
        adaJadwalUjian = localStorage.getItem('garudaCBT.ada_jadwal_ujian') || '0';

        // Load initial data
        loadJadwalUjian();
        loadLogs();
        getPosts();
        loadToken();

        // Real-time updates
        // Real-time updates with check
        setInterval(() => {
            if (!document.hidden) updateStats();
        }, 60000); // Relax to 60 seconds

        setInterval(() => {
            if (!document.hidden) loadToken();
        }, 30000); // Relax to 30 seconds

        // Setup form handlers
        setupFormHandlers();
    });

    // Dipakai dari view: getPosts() di dalam IIFE tidak bisa dipanggil lewat
    // onclick inline, jadi diekspos sebagai API global.
    window.loadMorePosts = getPosts;

    // Muat ulang seluruh widget (dipakai tombol "Segarkan" di view).
    window.refreshDashboard = function () {
        halaman = 0;
        $('#pengumuman').empty();
        $('#pengumuman-empty').addClass('d-none');
        loadJadwalUjian();
        loadLogs();
        loadToken();
        updateStats();
        getPosts();
    };

    /**
     * Load jadwal ujian hari ini
     */
    async function loadJadwalUjian() {
        try {
            // Show loading
            $('#table-loading').removeClass('d-none');
            
            const response = await ajaxGet('dashboard/get_status_siswa');
            if (response) {
                await createTableFromJSON(response);
                adaJadwalUjian = response.jadwals_ujian?.length || 0;
                localStorage.setItem('garudaCBT.ada_jadwal_ujian', adaJadwalUjian);
            }
        } catch (error) {
            console.error('Error loading jadwal ujian:', error);
            $('#table-container').html(`
                <div class="alert alert-warning m-3 text-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Tidak dapat memuat jadwal penilaian. Silakan refresh halaman.
                </div>
            `);
        } finally {
            // Hide loading
            $('#table-loading').addClass('d-none');
        }
    }

    /**
     * Update real-time statistics
     */
    async function updateStats() {
        try {
            const response = await ajaxGet('dashboard/get_realtime_stats');
            if (response) {
                // Update info boxes
                if (response.info_box) {
                    response.info_box.forEach(box => {
                        const id = '#total-' + box.title.toLowerCase().replace(/\s/g, '-');
                        $(id).html('<b>' + box.total + '</b>');
                    });
                }

                // Update ujian boxes
                if (response.ujian_box) {
                    response.ujian_box.forEach(box => {
                        const id = '#total-ujian-' + box.title.toLowerCase().replace(/\s/g, '-');
                        $(id).text(box.total);
                    });
                }
            }
        } catch (error) {
            console.error('Error updating stats:', error);
        }
    }

    /**
     * Load token
     */
    async function loadToken() {
        try {
            const response = await ajaxGet('dashboard/loadtoken');
            if (response && response.token) {
                $('#token-view').text(response.token);
            }
        } catch (error) {
            console.error('Error loading token:', error);
        }
    }

    /**
     * Load activity logs
     */
    async function loadLogs() {
        try {
            const response = await ajaxGet('dashboard/getlog?page=1&limit=8');
            if (response && response.logs && response.logs.length > 0) {
                let html = '<div class="activity-feed p-3">';
                response.logs.forEach((log, index) => {
                    const timeString = timeAgo(log.time);
                    const isLast = index === response.logs.length - 1;
                    
                    // Determine icon based on message title/content
                    let iconClass = 'bi-info-circle';
                    let iconBg = 'bg-info';
                    if (log.message.toLowerCase().includes('login')) {
                        iconClass = 'bi-box-arrow-in-right';
                        iconBg = 'bg-success';
                    } else if (log.message.toLowerCase().includes('logout')) {
                        iconClass = 'bi-box-arrow-right';
                        iconBg = 'bg-warning';
                    }

                    html += `
                        <div class="activity-item d-flex position-relative ${isLast ? '' : 'mb-4'}">
                            ${isLast ? '' : '<div class="activity-line" style="position: absolute; left: 15px; top: 32px; bottom: -24px; width: 2px; background: #eef2f7; z-index: 0;"></div>'}
                            <div class="activity-icon ${iconBg} shadow-sm text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 z-1" style="width: 32px; height: 32px; position: relative;">
                                <i class="bi ${iconClass}" style="font-size: 0.9rem;"></i>
                            </div>
                            <div class="activity-content ms-3 flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;">${log.user}</h6>
                                    <small class="text-muted fw-normal" style="font-size: 0.7rem;">${timeString}</small>
                                </div>
                                <p class="mb-0 text-secondary" style="font-size: 0.8rem; line-height: 1.4;">${log.message}</p>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                $('#log-list').html(html);
            } else {
                // Show empty state
                $('#log-list').html(`
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-activity text-muted fs-3"></i>
                        </div>
                        <p class="text-muted mb-0 small">Belum ada aktivitas terbaru</p>
                    </div>
                `);
            }
        } catch (error) {
            console.error('Error loading logs:', error);
            $('#log-list').html(`
                <div class="text-center py-4">
                    <p class="text-danger small mb-0"><i class="bi bi-exclamation-circle me-1"></i>Gagal memuat aktivitas</p>
                </div>
            `);
        }
    }

    /**
     * Load pengumuman/posts
     */
    async function getPosts() {
        halaman++;
        $('#loading-post').removeClass('d-none');
        $('#loadmore-post').addClass('d-none');
        // Hide empty state initially
        $('#pengumuman-empty').addClass('d-none');

        try {
            const response = await ajaxGet(`dashboard/getpost?page=${halaman}&limit=5`);
            if (response && response.posts && response.posts.length > 0) {
                // Always hide loading spinner when we have data
                $('#pengumuman-loading').addClass('d-none');
                
                // Render posts
                response.posts.forEach(post => {
                    const postHtml = createPostHTML(post);
                    $('#pengumuman').append(postHtml);
                });

                if (response.posts.length >= 5) {
                    $('#loadmore-post').removeClass('d-none');
                }
            } else {
                if (halaman === 1) {
                    // No posts at all - show empty state
                    $('#pengumuman-loading').addClass('d-none');
                    $('#pengumuman-empty').removeClass('d-none');
                }
            }
        } catch (error) {
            console.error('Error loading posts:', error);
            if (halaman === 1) {
                $('#pengumuman-loading').addClass('d-none');
                $('#pengumuman').html(`
                    <div class="alert alert-light border text-center py-5 rounded-4">
                        <i class="bi bi-exclamation-circle text-muted fs-2"></i>
                        <p class="text-muted mt-2 mb-0">Gagal memuat pengumuman. Silakan muat ulang halaman.</p>
                    </div>
                `);
            }
        } finally {
            $('#loading-post').addClass('d-none');
        }
    }

    /**
     * Create post HTML (Social Media Style)
     */
    function createPostHTML(post) {
        const commentsCount = post.comments_count || 0;
        const commentText = commentsCount > 0 ? `${commentsCount} Komentar` : '0 Komentar';
        
        return `
        <div class="card border-0 shadow-sm mb-4 post-card fade-in-up" id="post-${post.id_post}" style="border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-2">
                    <div class="position-relative">
                        <img class="rounded-circle me-3 border border-2 border-white shadow-sm" src="${post.author_photo}" alt="User" style="width: 48px; height: 48px; object-fit: cover;">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 12px; height: 12px; margin-right: 12px; margin-bottom: 2px;"></span>
                    </div>
                    <div class="lh-sm">
                        <div class="fw-bold text-dark fs-6 mb-0">${post.author}</div>
                        <div class="d-flex align-items-center mt-1">
                            <span class="text-muted small me-2"><i class="bi bi-clock me-1"></i>${formatDate(post.created_at || new Date())}</span>
                            <span class="badge bg-light text-secondary border px-2 border-opacity-10 rounded-pill" style="font-size: 0.65rem;">PENGUMUMAN</span>
                        </div>
                    </div>
                    <div class="ms-auto text-end">
                         <button class="btn btn-link text-muted p-0"><i class="bi bi-three-dots-vertical"></i></button>
                    </div>
                </div>
                
                <div class="mb-2 post-content text-dark" style="font-size: 1rem; line-height: 1.5; white-space: pre-wrap;">
                    ${post.content || post.text || ''}
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom border-light">
                    <div class="d-flex align-items-center">
                        <div class="stack-avatars d-flex me-2">
                             <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center border border-2 border-white shadow-sm" style="width: 22px; height: 22px; font-size: 10px; z-index: 1;"><i class="bi bi-hand-thumbs-up-fill"></i></div>
                        </div>
                        <span class="text-muted small">Disukai pengawas lainnya</span>
                    </div>
                    <a href="javascript:void(0)" onclick="toggleComments(${post.id_post})" class="text-decoration-none text-primary fw-bold small hover-underline">
                        <i class="bi bi-chat-left-text me-1"></i> ${commentText}
                    </a>
                </div>
                
                <div class="d-flex pt-3 pb-0 gap-2">
                    <button class="btn btn-light rounded-pill flex-grow-1 py-2 fw-semibold text-secondary transition-all like-btn" style="font-size: 0.85rem;">
                        <i class="bi bi-heart me-1"></i> Suka
                    </button>
                    <button class="btn btn-light rounded-pill flex-grow-1 py-2 fw-semibold text-secondary transition-all" onclick="toggleComments(${post.id_post})" style="font-size: 0.85rem;">
                        <i class="bi bi-reply me-1"></i> Balas
                    </button>
                    <button class="btn btn-light rounded-pill px-3 py-2 fw-semibold text-secondary transition-all" style="font-size: 0.85rem;">
                        <i class="bi bi-share"></i>
                    </button>
                </div>
                
                <div id="comments-section-${post.id_post}" class="d-none mt-4">
                    <div class="rounded-4 p-0">
                        <div id="comments-list-${post.id_post}"></div>
                        
                        <div id="loading-comments-${post.id_post}" class="text-center d-none my-3">
                            <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                        </div>
                        
                        <div id="load-more-comments-${post.id_post}" class="text-center d-none mt-2">
                             <a href="javascript:void(0)" onclick="loadComments(${post.id_post}, true)" class="text-decoration-none fw-bold small text-primary">Lihat komentar lainnya</a>
                        </div>
                    </div>

                    <!-- Comment Input -->
                    <div class="d-flex align-items-center mt-3 pt-3 border-top">
                         <img class="rounded-circle me-2 border shadow-sm" src="${base_url}assets/img/user.png" width="36" height="36" alt="Me" style="object-fit: cover;">
                         <div class="flex-grow-1 position-relative">
                             <input type="text" class="form-control rounded-pill border-0 shadow-sm px-4 py-2" 
                                    style="font-size: 0.9rem; background-color: #f8fafc;"
                                    placeholder="Tulis komentar..." 
                                    onkeypress="handleCommentSubmit(event, ${post.id_post}, this)">
                             <button class="btn btn-primary rounded-circle position-absolute end-0 top-50 translate-middle-y me-1 shadow-sm d-flex align-items-center justify-content-center" 
                                     onclick="submitComment(${post.id_post}, $(this).siblings('input'))"
                                     style="width: 32px; height: 32px; padding: 0;">
                                <i class="bi bi-arrow-right-short fs-4"></i>
                             </button>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    }

    /**
     * Toggle Comments Section
     */
    window.toggleComments = function (postId) {
        const section = $(`#comments-section-${postId}`);
        if (section.hasClass('d-none')) {
            section.removeClass('d-none');
            // Load comments if empty
            if ($(`#comments-list-${postId}`).children().length === 0) {
                loadComments(postId);
            }
        } else {
            section.addClass('d-none');
        }
    }

    /**
     * Load Comments for a Post
     */
    window.loadComments = async function (postId, append = false) {
        const list = $(`#comments-list-${postId}`);
        const loader = $(`#loading-comments-${postId}`);
        const loadMore = $(`#load-more-comments-${postId}`);

        let page = list.data('page') || 0;
        if (!append) page = 0;

        loader.removeClass('d-none');
        loadMore.addClass('d-none');

        try {
            // Using Pengumuman controller endpoint as Dashboard doesn't have it
            // Assuming access rights are handled by session
            const response = await ajaxGet(`pengumuman/getcomment/${postId}/${page}`);

            if (response && response.data) {
                if (!append) list.empty();

                response.data.forEach(comment => {
                    const commentHtml = createCommentHTML(comment);
                    if (append) list.append(commentHtml);
                    else list.prepend(commentHtml);
                });

                if (response.data.length >= 5) {
                    list.data('page', page + 1);
                    loadMore.removeClass('d-none');
                }
            }
        } catch (error) {
            console.error('Error loading comments:', error);
        } finally {
            loader.addClass('d-none');
        }
    }

    /**
     * Create Comment HTML (Modern Bubble Style)
     */
    function createCommentHTML(comment) {
        let userPhoto = base_url + 'assets/img/user.png';
        let userName = 'User';

        if (comment.dari_group == '1') { // Admin
            userName = 'Administrator';
            userPhoto = base_url + 'assets/img/user.png';
        } else if (comment.dari_group == '2') { // Guru
            userName = comment.nama_guru;
            userPhoto = comment.foto ? (comment.foto.startsWith('uploads/') ? base_url + comment.foto : base_url + 'uploads/profiles/' + comment.foto) : base_url + 'assets/img/guru.png';
        } else if (comment.dari_group == '3') { // Siswa
            userName = comment.nama_siswa;
            userPhoto = comment.foto_siswa ? (comment.foto_siswa.startsWith('uploads/') ? base_url + comment.foto_siswa : base_url + 'uploads/profiles/' + comment.foto_siswa) : base_url + 'assets/img/siswa.png';
        } else if (comment.dari_group == '4') { // Panitia
            userName = comment.nama_panitia;
            userPhoto = comment.foto_panitia ? (comment.foto_panitia.startsWith('uploads/') ? base_url + comment.foto_panitia : base_url + 'uploads/profiles/' + comment.foto_panitia) : base_url + 'assets/img/user.png';
        }

        return `
        <div class="comment-item d-flex mb-3 align-items-start">
            <img src="${userPhoto}" class="rounded-circle me-2 border shadow-sm" width="32" height="32" style="object-fit:cover">
            <div class="flex-grow-1" style="min-width: 0;">
                <div class="bg-light p-3 rounded-4 border-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small">${userName}</span>
                        <span class="text-muted small" style="font-size: 0.65rem;">${timeAgo(comment.tanggal)}</span>
                    </div>
                    <div class="text-secondary small" style="line-height: 1.5; word-wrap: break-word;">${comment.text}</div>
                </div>
                <div class="d-flex align-items-center ms-2 mt-1">
                     <button class="btn btn-link p-0 text-muted text-decoration-none small fw-bold me-3" style="font-size: 0.7rem;">Suka</button>
                     <button class="btn btn-link p-0 text-muted text-decoration-none small fw-bold" style="font-size: 0.7rem;">Balas</button>
                </div>
            </div>
        </div>
        `;
    }

    /**
     * Handle Inline Comment Submit
     */
    window.handleCommentSubmit = function (e, postId, input) {
        if (e.key === 'Enter') {
            submitComment(postId, $(input));
        }
    }

    window.submitComment = async function (postId, inputObj) {
        const text = inputObj.val();
        if (!text.trim()) return;

        inputObj.prop('disabled', true);

        // Prepare data with CSRF
        const data = {
            id_post: postId,
            text: text
        };
        if (typeof CSRF_NAME !== 'undefined' && typeof CSRF_HASH !== 'undefined') {
            data[CSRF_NAME] = CSRF_HASH;
        }

        try {
            const response = await $.ajax({
                url: base_url + 'pengumuman/savekomentar',
                type: 'POST',
                data: data
            });

            if (response.status && response.data && response.data.length > 0) {
                const newComment = response.data[0];
                const commentHtml = createCommentHTML(newComment);
                $(`#comments-list-${postId}`).prepend(commentHtml);
                inputObj.val('');
            } else {
                showToast('error', response.message || 'Gagal mengirim komentar');
            }
        } catch (error) {
            console.error(error);
            showToast('error', 'Gagal mengirim komentar');
        } finally {
            inputObj.prop('disabled', false);
            inputObj.focus();
        }
    }

    /**
     * Setup form handlers
     */
    function setupFormHandlers() {
        // Komentar form
        $('#komentar').on('submit', async function (e) {
            e.preventDefault();
            const formData = $(this).serialize();

            try {
                const response = await $.ajax({
                    url: base_url + 'dashboard/savekomentar',
                    type: 'POST',
                    data: formData
                });

                if (response.status) {
                    showToast('success', response.message || 'Komentar berhasil disimpan');
                    $('#komentarModal').modal('hide');
                    $(this)[0].reset();
                }
            } catch (error) {
                showToast('error', 'Gagal menyimpan komentar');
            }
        });

        // Balasan form
        $('#balasan').on('submit', async function (e) {
            e.preventDefault();
            const formData = $(this).serialize();

            try {
                const response = await $.ajax({
                    url: base_url + 'dashboard/savebalasan',
                    type: 'POST',
                    data: formData
                });

                if (response.status) {
                    showToast('success', response.message || 'Balasan berhasil disimpan');
                    $('#balasanModal').modal('hide');
                    $(this)[0].reset();
                }
            } catch (error) {
                showToast('error', 'Gagal menyimpan balasan');
            }
        });
    }

    /**
     * Create exam schedule table
     */
    async function createTableFromJSON(data) {
        const tableContainer = document.getElementById('table-container');
        const allSesi = data.ruangs || [];
        const jsonData = data.jadwals_ujian || [];

        // Validate data
        if (!validateData(allSesi, jsonData, tableContainer)) {
            return;
        }

        // Process and group data
        const groupedSchedules = processAndGroupData(data);

        // Sort
        groupedSchedules.sort((a, b) => {
            if (a.ruang_sesi.ruang_id !== b.ruang_sesi.ruang_id)
                return a.ruang_sesi.ruang_id - b.ruang_sesi.ruang_id;
            if (a.ruang_sesi.sesi_id !== b.ruang_sesi.sesi_id)
                return a.ruang_sesi.sesi_id - b.ruang_sesi.sesi_id;
            return a.jam_ke - b.jam_ke;
        });

        // Calculate rowspans
        const rowCounts = calculateRowCounts(groupedSchedules, 'ruang_sesi.ruang_id');
        const sesiRowCounts = calculateRowCounts(groupedSchedules, 'ruang_sesi.ruang_id', 'ruang_sesi.sesi_id');

        // Build table
        const tableHtml = buildTable(groupedSchedules, data, rowCounts, sesiRowCounts);
        tableContainer.innerHTML = tableHtml;
    }

    function validateData(allSesi, jsonData, container) {
        if (allSesi.length === 0 || jsonData.length === 0) {
            container.innerHTML = `
                <div class="alert alert-light border m-3 text-center py-4">
                    <i class="bi bi-calendar-x text-muted fs-1"></i>
                    <p class="text-muted mt-3 mb-0">Tidak ada jadwal penilaian hari ini</p>
                </div>
            `;
            return false;
        }
        return true;
    }

    function processAndGroupData(data) {
        const allSesi = data.ruangs || [];
        const jsonData = data.jadwals_ujian || [];
        const grouped = {};

        jsonData.forEach(jadwal => {
            const bank_kelas = jadwal.bank?.bank_kelas || [];
            const ruangFiltered = allSesi.filter(r =>
                r.kelas_id.some(id => bank_kelas.includes(id?.toString()))
            );

            ruangFiltered.forEach(ruang => {
                const siswaRuang = (data.sesisiswa || []).filter(s =>
                    s.ruang_id === ruang.ruang_id &&
                    s.sesi_id === ruang.sesi_id &&
                    bank_kelas.includes(s.kelas_id?.toString())
                );

                let belumLogin = 0, sedangLogin = 0, selesai = 0;
                siswaRuang.forEach(s => {
                    const cbt = (data.cbtsiswa || []).find(c =>
                        c.id_siswa === s.siswa_id &&
                        c.jadwal?.id_jadwal === jadwal.id_jadwal
                    );

                    if (!cbt || !cbt.mulai) belumLogin++;
                    else if (!cbt.selesai) sedangLogin++;
                    else selesai++;
                });

                const pengawasJadwal = data.pengawas?.[jadwal.id_jadwal]?.[ruang.ruang_id]?.[ruang.sesi_id] || {};
                const jamKe = pengawasJadwal.jam_ke || jadwal.jam_ke || '1';
                const key = `${ruang.ruang_id}-${ruang.sesi_id}-${jamKe}`;

                if (!grouped[key]) {
                    grouped[key] = {
                        key,
                        ruang_sesi: ruang,
                        jam_ke: jamKe,
                        jadwal: [],
                        pengawas: [],
                        peserta: 0,
                        blm_login: 0,
                        sdg_login: 0,
                        selesai: 0
                    };
                }

                grouped[key].jadwal.push(jadwal);
                const pengawasList = (pengawasJadwal.id_guru || '').split(',').filter(Boolean);
                pengawasList.forEach(id => {
                    const nama = data.gurus?.[id] || '';
                    if (nama && !grouped[key].pengawas.includes(nama)) {
                        grouped[key].pengawas.push(nama);
                    }
                });

                grouped[key].peserta += siswaRuang.length;
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

        const getValues = (item) => keys.map(key =>
            key.split('.').reduce((obj, prop) => obj?.[prop], item)
        );

        [...items, null].forEach(item => {
            const currentValues = item ? getValues(item) : null;
            const isSameGroup = prevValues && currentValues &&
                prevValues.every((val, i) => val === currentValues[i]);

            if (!isSameGroup) {
                if (prevValues !== null) {
                    counts.set(prevValues.join('-'), currentCount);
                }
                currentCount = 0;
            }

            if (item !== null) currentCount++;
            prevValues = currentValues;
        });

        return counts;
    }

    function buildTable(groupedSchedules, data, rowCounts, sesiRowCounts) {
        const headerHtml = `
        <thead class="table-light">
            <tr>
                <th class="text-center align-middle" rowspan="2">RUANG</th>
                <th class="text-center align-middle" rowspan="2">SESI</th>
                <th class="text-center align-middle" rowspan="2">JAM KE</th>
                <th class="text-center align-middle" rowspan="2">MAPEL<br>(BANK SOAL)</th>
                <th class="text-center align-middle" rowspan="2">KELAS</th>
                <th class="text-center align-middle" rowspan="2">PENGAWAS</th>
                <th class="text-center align-middle" rowspan="2">JML SISWA</th>
                <th class="text-center align-middle" colspan="3">STATUS SISWA</th>
            </tr>
            <tr>
                <th class="text-center align-middle">BLM<br/>LOGIN</th>
                <th class="text-center align-middle">SDG<br/>LOGIN</th>
                <th class="text-center align-middle">SELESAI</th>
            </tr>
        </thead>`;

        let bodyHtml = '<tbody>';
        const usedRuangIds = new Set();
        const usedSesiKeys = new Set();
        let curSesi = 0;
        let bgRow = '';
        let bold = false;

        groupedSchedules.forEach(group => {
            const { ruang_sesi, jam_ke, jadwal, pengawas, peserta, blm_login, sdg_login, selesai } = group;
            const isMyRoom = (data.my_rooms || []).includes(ruang_sesi.ruang_id?.toString()) || (data.my_rooms || []).includes(ruang_sesi.ruang_id);
            const myRoomStyle = isMyRoom ? 'table-warning border-primary' : '';

            let infoMapel = ``;
            jadwal.forEach(jad => {
                const kode = jad.bank?.bank_kode || '';
                const mapel = jad.bank?.mapel?.kode || '';
                infoMapel += `
                    <div class="mb-2">
                        <div class="fw-bold text-dark" style="font-size: 0.9rem; line-height: 1.2;">${mapel}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-code-square me-1"></i>${kode}
                        </div>
                    </div>`;
            });

            const bank_kelas = getUniqueClassNames(ruang_sesi, jadwal).join(', ');

            bodyHtml += `<tr class="${myRoomStyle}">`;

            // Ruang column
            const ruangId = ruang_sesi.ruang_id;
            if (!usedRuangIds.has(ruangId)) {
                const rowspanValue = rowCounts.get(String(ruangId)) || 1;
                bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle border-bottom-bold ${isMyRoom ? 'fw-bold text-primary' : ''}">${ruang_sesi.nama_ruang}</td>`;
                usedRuangIds.add(ruangId);
                bold = true;
            } else {
                bold = false;
            }

            const genap = curSesi % 2 === 0;
            const bBold = bold ? 'border-top-bold' : '';

            // Sesi column
            const sesiKey = `${ruang_sesi.ruang_id}-${ruang_sesi.sesi_id}`;
            if (!usedSesiKeys.has(sesiKey)) {
                bgRow = genap ? 'bg-light' : '';
                const rowspanValue = sesiRowCounts.get(sesiKey) || 1;
                bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-sesi ${bgRow} ${bBold}">${ruang_sesi.nama_sesi}</td>`;
                usedSesiKeys.add(sesiKey);
                curSesi++;
            }

            bodyHtml += `
            <td class="text-center align-middle td-jam ${bgRow} ${bBold}">${jam_ke}</td>
            <td class="align-middle td-mapel jadwal ${bgRow} ${bBold}">${infoMapel}</td>
            <td class="text-center align-middle ${bgRow} ${bBold}">${bank_kelas}</td>
            <td class="text-center align-middle td-pengawas ${bgRow} ${bBold}">${pengawas.join('<br>')}</td>
            <td class="text-center align-middle text-bold ${bgRow} ${bBold}">${peserta}</td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="badge bg-danger">${blm_login}</span></td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="badge bg-warning text-dark">${sdg_login}</span></td>
            <td class="text-center align-middle ${bBold} ${bgRow}"><span class="badge bg-success">${selesai}</span></td>
        </tr>`;
        });

        bodyHtml += '</tbody>';
        return `<table class="table table-bordered table-sm w-100">${headerHtml}${bodyHtml}</table>`;
    }

    function getUniqueClassNames(ruang_sesi, jadwal) {
        const banks_kelas = [];
        const classIds = ruang_sesi.kelas_id;

        jadwal.forEach(j => {
            classIds.forEach(klsId => {
                if (j.bank?.bank_kelas?.includes(klsId)) {
                    banks_kelas.push(ruang_sesi.nama_kelas[klsId] || '');
                }
            });
        });

        return [...new Set(banks_kelas)];
    }

    /**
     * Utility: AJAX GET
     */
    async function ajaxGet(url) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: base_url + url,
                type: "GET",
                success: resolve,
                error: (xhr, status, error) => {
                    console.error("AJAX Error:", status, error);
                    reject(error);
                }
            });
        });
    }

    /**
     * Utility: Format date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return date.toLocaleDateString('id-ID', options);
    }

    function timeAgo(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        // Timezone correction if needed, but assuming server sends UTC or correct offset usually
        // If server sends 'Y-m-d H:i:s' without TZ, browser assumes local. 
        // If server matches client time, diff works.

        let interval = Math.floor(seconds / 31536000);
        if (interval > 1) return interval + " thn";

        interval = Math.floor(seconds / 2592000);
        if (interval > 1) return interval + " bln";

        interval = Math.floor(seconds / 86400);
        if (interval > 1) return interval + " hr";
        if (interval === 1) return "Kemarin";

        interval = Math.floor(seconds / 3600);
        if (interval >= 1) return interval + " jam";

        interval = Math.floor(seconds / 60);
        if (interval >= 1) return interval + " mnt";

        return "Baru saja";
    }

    $(document).on('click', '.like-btn', function() {
        $(this).toggleClass('text-danger text-secondary');
        const icon = $(this).find('i');
        icon.toggleClass('bi-heart bi-heart-fill');
        
        // Add a small animation
        $(this).addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $(this).removeClass('animate__animated animate__pulse');
        }, 500);
    });

})();
