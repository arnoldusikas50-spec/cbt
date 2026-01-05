<!-- Content Wrapper. Contains page content -->
<main class="app-main bg-light">
    <div class="container-fluid ps-0 pe-0 pb-0 pt-4" style="background-color: rgba(255,255,255,0.7)">
        <div class="row m-0">
            <?php
            $colors = ['bg-gradient-primary', 'bg-gradient-success', 'bg-gradient-info', 'bg-gradient-warning', 'bg-gradient-danger', 'bg-gradient-indigo', 'bg-gradient-purple', 'bg-gradient-teal', 'bg-gradient-pink', 'bg-gradient-navy'];
            $i = 0;
            foreach ($info_box as $info):
                // Custom Color Mapping based on Title
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
                <div class="col-6 col-lg-2 mb-3">
                    <div class="small-box <?= $bgClass ?> shadow-sm h-100 position-relative overflow-hidden"
                        style="border-radius: 15px;">
                        <div class="inner p-3 text-white">
                            <h3 class="fw-bold mb-1" style="font-size: 2.2rem;"><?= $info->total; ?></h3>
                            <p class="text-uppercase font-weight-bold mb-0 opacity-75"
                                style="letter-spacing: 0.5px; font-size: 0.75rem;"><?= $info->title; ?></p>
                        </div>
                        <div class="icon">
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
                            <i class="bi bi-<?= $biIcon ?> text-white opacity-25"
                                style="top: 10px; right: 10px; font-size: 4rem;"></i>
                        </div>
                        <div class="small-box-footer bg-dark bg-opacity-10 py-1"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary my-shadow">
                        <!-- <div class="card-header">
                            <h5 class="text-center">Penilaian</h5>
                        </div> -->
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($ujian_box as $info): ?>
                                    <div class="col-6 col-md-3 col-lg-2" style="min-height: 60px">
                                        <div class="info-box border p-1" style="min-height: 60px">
                                            <div class="info-box-content p-1 text-danger">
                                                <span class="info-box-text text-sm"><?= $info->title; ?></span>
                                                <h5 class="info-box-number m-0"><?= $info->total; ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-6 col-md-3 col-lg-2" style="min-height: 60px">
                                    <div class="info-box border p-1" style="min-height: 60px">
                                        <div class="info-box-content p-1 text-primary">
                                            <span class="info-box-text">Token
                                                <small class="float-end d-none" id="interval">-- : -- :
                                                    --</small></span>
                                            <div>
                                                <h5 class="info-box-number m-0" id="token-view">
                                                    <?= $token->token != null ? $token->token : '- - - - - -' ?>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-center alert alert-default-danger"><b>JADWAL PENILAIAN HARI INI</b>
                                    </h6>
                                    <div class="col-12 table-responsive" id="table-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- E-Learning Widget Removed -->
                <div class="col-12">
                    <div class="card card-danger my-shadow mb-3">
                        <div class="card-header"><b>INFO/PENGUMUMAN</b></div>
                        <div class="card-body">
                            <div class="konten-pengumuman">
                                <div id="pengumuman">
                                </div>
                                <div id="loading-post" class="text-center d-none">
                                    <br />
                                    <div class="spinner-border spinner-border-sm" role="status"></div> Loading....
                                </div>
                                <div id="loadmore-post" onclick="getPosts()" class="text-center mt-4 loadmore d-none">
                                    <div class="btn btn-default">Muat Pengumuman lainnya ...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="komentarModal" tabindex="-1" role="dialog" aria-labelledby="komentarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title fw-bold ps-2" id="komentarLabel">
                        <i class="bi bi-chat-dots-fill me-2 text-primary"></i>Tulis Komentar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <?= form_open('create', array('id' => 'komentar')) ?>
                    <input type="hidden" id="id-post" name="id_post" value="">

                    <div class="d-flex align-items-start">
                        <div class="me-3 text-center">
                            <img class="img-fluid rounded-circle shadow-sm border"
                                src="<?= base_url('assets/img/guru.png') ?>" alt="Avatar"
                                style="width: 45px; height: 45px; object-fit: cover;">
                        </div>
                        <div class="w-100">
                            <textarea name="text" class="form-control bg-light border-0 text-sm" rows="3"
                                placeholder="Tulis komentar Anda di sini..." required
                                style="resize: none; border-radius: 0.8rem; padding: 12px;"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        <button type="button" class="btn btn-primary rounded-pill px-3" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-send-fill me-2"></i>Komentari
                        </button>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="balasanModal" tabindex="-1" role="dialog" aria-labelledby="balasanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title fw-bold ps-2" id="balasanLabel">
                        <i class="bi bi-reply-fill me-2 text-primary"></i>Tulis Balasan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <?= form_open('create', array('id' => 'balasan')) ?>
                    <input type="hidden" id="id-comment" name="id_comment" value="">

                    <div class="d-flex align-items-start">
                        <div class="me-3 text-center">
                            <img class="img-fluid rounded-circle shadow-sm border"
                                src="<?= base_url('assets/img/guru.png') ?>" alt="Avatar"
                                style="width: 45px; height: 45px; object-fit: cover;">
                        </div>
                        <div class="w-100">
                            <textarea name="text" class="form-control bg-light border-0 text-sm" rows="3"
                                placeholder="Tulis balasan Anda..." required
                                style="resize: none; border-radius: 0.8rem; padding: 12px;"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                        <button type="button" class="btn btn-primary rounded-pill px-3" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-reply-fill me-2"></i>Balas
                        </button>
                    </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url() ?>/assets/app/js/ajax_request.js" type="text/javascript"></script>
    <script src="<?= base_url() ?>/assets/app/js/rowspanizer.js"></script>
    <script>
        let timerTokenView;
        var halaman = 0;
        var idGuru = "<?= $guru->id_guru ?>";

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
                        dari = v.nama_guru;
                        foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/guru.png';
                        avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="40px" height="40px">';
                    } else {
                        dari = v.nama_siswa;
                        foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                        avatar = '<img class="img-circle border" src="' + foto + '" alt="Img" width="40px" height="40px">';
                    }
                }

                comm += '<div class="media mt-1" id="parent-reply' + v.id_comment + '">'
                    + avatar +
                    '    <div class="w-100 ms-2">' +
                    '        <div class="media-body border ps-3 bg-light" style="border-radius: 20px">' +
                    '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                    '            <div class="comment-text pb-1">' + v.text + '</div>' +
                    '        </div>' +
                    '        <div class="ms-2">' +
                    '            <span class="btn-sm me-2 text-muted">' + createTime(v.tanggal) + '</span>' +
                    '            <span id="trigger-reply' + v.id_comment + '" class="btn btn-sm me-2 text-muted action-collapse" data-bs-toggle="collapse" aria-expanded="true"' +
                    '                              aria-controls="collapse-reply' + v.id_comment + '"' +
                    '                              href="#collapse-reply' + v.id_comment + '"><b>' + v.jml + ' balasan</b></span>' +
                    '            <span class="btn btn-sm me-2 text-muted btn-toggle-reply"' +
                    '                  data-id="' + v.id_comment + '" data-bs-toggle="modal" data-bs-target="#balasanModal">' +
                    '                <i class="bi bi-reply-fill"></i> <b>Balas</b></span>';
                if (v.dari_group === '2' && v.dari === idGuru) {
                    comm += '            <span class="btn btn-sm text-muted text-red" data-id="' + v.id_comment + '">' +
                        '                <i class="bi bi-trash me-1"></i> Hapus' +
                        '            </span>';
                }
                comm += '        </div>' +
                    '<div id="collapse-reply' + v.id_comment + '" class="p-2 collapse toggle-reply" data-id="' + v.id_comment + '" data-bs-parent="#parent-reply' + v.id_comment + '">';
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
                            '<div class="btn-circle-sm btn-primary media-left pt-1 me-2" style="width: 37px">A</div>';
                    } else {
                        if (v.dari_group == '2') {
                            dari = v.nama_guru;
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
                        '        <div class="media-body border ps-3" style="border-radius: 17px; background-color: #dee2e6">' +
                        '            <span class="text-xs text-muted"><b>' + dari + '</b></span>' +
                        '            <div class="comment-text">' + v.text +
                        '            </div>' +
                        '        </div>' +
                        '        <div class="ms-2">' +
                        '            <small class="btn-sm me-2 text-muted">' + createTime(v.tanggal) + '</small>';
                    if (v.dari_group === '2' && v.dari === idGuru) {
                        repl += '            <span class="btn btn-sm text-muted" data-id="' + v.id_reply + '">' +
                            '                <i class="bi bi-trash me-1"></i> Hapus' +
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
                        $(`#loading${id}`).addClass('d-none');
                        if ($count.data('count') > 0) {
                            $(`#loadmore${id}`).removeClass('d-none');
                        }
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
                        $(`#loading-reply${id}`).addClass('d-none');
                        if ($count.data('count') > 0) {
                            $(`#loadmore-reply${id}`).removeClass('d-none');
                        }
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
                        avatar = v.foto != null ?
                            `<img class="rounded-circle shadow-sm border" src="${v.foto}" alt="Img" width="48" height="48" style="object-fit: cover;">` :
                            `<div class="rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 48px; height: 48px; font-size: 1.2rem;">A</div>`;
                    } else {
                        if (v.dari_group == '2') {
                            dari = v.nama_guru;
                            foto = v.foto != null ? base_url + v.foto : base_url + 'assets/img/guru.png';
                            avatar = `<img class="rounded-circle shadow-sm border" src="${foto}" alt="Img" width="48" height="48" style="object-fit: cover;">`;
                        } else {
                            dari = v.nama_siswa;
                            foto = v.foto_siswa != null ? base_url + v.foto_siswa : base_url + 'assets/img/siswa.png';
                            avatar = `<img class="rounded-circle shadow-sm border" src="${foto}" alt="Img" width="48" height="48" style="object-fit: cover;">`;
                        }
                    }

                    card += `
                    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px; overflow: hidden; transition: transform 0.2s;">
                        <div class="card-body p-4" id="parent${v.id_post}">
                            <div class="d-flex align-items-center mb-3">
                                ${avatar}
                                <div class="ms-3">
                                    <h6 class="fw-bold mb-0 text-dark">${dari}</h6>
                                    <small class="text-muted d-block">${createTime(v.tanggal)}</small>
                                </div>
                                <div class="ms-auto">
                                    ${(v.dari_group === '2' && v.dari === idGuru) ?
                            `<button type="button" class="btn btn-sm btn-primary text-danger rounded-circle p-2" data-id="${v.id_post}" title="Hapus Postingan" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-trash"></i>
                                        </button>` : ''}
                                </div>
                            </div>
                            
                            <div class="mb-4 post-content text-dark" style="font-size: 1rem; line-height: 1.6; color: #2d3748;">
                                ${v.text}
                            </div>
                            
                            <div class="d-flex align-items-center pt-3 border-top gap-2">
                                <button type="button" class="btn btn-primary rounded-pill px-4 btn-sm fw-bold shadow-sm" 
                                        data-id="${v.id_post}" data-bs-toggle="modal" data-bs-target="#komentarModal">
                                    <i class="bi bi-chat-text-fill me-2"></i>Tulis Komentar
                                </button>
                                
                                <button type="button" id="trigger${v.id_post}" class="btn btn-primary rounded-pill px-4 btn-sm text-secondary fw-bold border action-collapse"
                                        data-bs-toggle="collapse" aria-expanded="true" aria-controls="collapse-${v.id_post}" href="#collapse-${v.id_post}">
                                    <i class="bi bi-chat-dots me-2"></i>${v.jml} Komentar
                                </button>
                            </div>
                        </div>

                        <div id="collapse-${v.id_post}" class="collapse toggle-comment bg-light" data-id="${v.id_post}" data-bs-parent="#parent${v.id_post}">
                            <div class="px-4 py-3 border-top">
                                <div id="konten${v.id_post}"></div>
                                <div id="loading${v.id_post}" class="text-center d-none py-3">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                </div>
                                ${v.jml == '0' ? '<div class="text-center text-muted small py-2">Belum ada komentar</div>' :
                            `<div id="loadmore${v.id_post}" onclick="getComments(${v.id_post})" class="text-center mt-3 mb-2 loadmore">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Muat komentar lainnya...</button>
                                    </div>`
                        }
                            </div>
                        </div>
                    </div>`;
                })
            } else {
                card = `
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body text-center py-5">
                       <img src="${base_url}assets/img/no-data.svg" alt="Empty" style="width: 120px; opacity: 0.5;" class="mb-3"> 
                       <h6 class="text-muted fw-bold">Tidak ada pengumuman hari ini</h6>
                    </div>
                </div>`;
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
                    dataType: "JSON",
                    timeout: 10000,
                    success: function (response) {
                        //console.log("result", response);
                        halaman += 1;

                        if (response && response.length === 5) {
                            $(`#loadmore-post`).removeClass('d-none');
                        }
                        $(`#loading-post`).addClass('d-none');
                        if (response) {
                            addPosts(response);
                        }
                    }, error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        $(`#loading-post`).addClass('d-none');
                        if (halaman > 0) {
                            $(`#loadmore-post`).removeClass('d-none');
                        }
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
                    data: { page: 1, limit: 10, filter: ['2', '3'] },
                    method: "GET",
                    success: function (data) {
                        //console.log(data);
                        if (data.logs.length > 0) {
                            var ul = '<ul class="products-list product-list-in-card ps-2 pe-2">';
                            $.each(data.logs, function (key, value) {
                                const nama_lengkap = value?.first_name === value?.last_name ? value?.first_name : value?.first_name + ' ' + value?.last_name
                                var nama = value.id_group === '1' ? value.first_name : nama_lengkap;
                                var clr = colorBg[value.log_type];
                                var tgl = formatTanggal(value.log_time);//new Date('02/12/2018');
                                const guru = data.guru.find(g => g.id === value.id_user);
                                const siswa = data.siswa.find(s => s.id === value.id_user);
                                const admin = data.admins.find(a => a.id === value.id_user);
                                const fotoUser = guru ? guru.foto :
                                    siswa ? siswa.foto :
                                        admin ? admin.foto : null;
                                const foto = base_url + (fotoUser ? fotoUser : '');
                                const initial = nama.charAt(0).toUpperCase();
                                ul += '  <li class="item">' +
                                    '    <div class="media" style="line-height: 1.3">' +
                                    '      <div data-siswa="' + initial + '" class="d-flex justify-content-center align-items-center bg-' + clr + ' rounded-circle" style="width: 50px; height: 50px">' +
                                    '        <img class="avatar-log img-circle" src="' + foto + '" width="44" height="44"  alt=""/></div>' +
                                    //'<button class="btn btn-circle-sm btn-'+clr+' media-left">' + nama.charAt(0).toUpperCase()+'</button>' +
                                    '      <div class="media-body ms-2">' +
                                    '        <span>' + nama + '</span>' +
                                    '        <br />' +
                                    '        <span class="text-' + clr + ' text-sm">' + value.log_desc + '</span>' +
                                    '        <br />' +
                                    '        <span class="text-xs text-muted">' + tgl + '</span>' +
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
                ajaxGet(base_url + 'dashboard/get_status_siswa', function (error, response) {
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
                    const pengawasList = (pengawasJadwal.id_guru || '').split(',').filter(Boolean);
                    //if (!grouped[key].jam_ke.includes(pengawasJadwal.jam_ke)) {
                    //    grouped[key].jam_ke.push(pengawasJadwal.jam_ke)
                    //}
                    for (const string of pengawasList) {
                        if (!grouped[key].pengawas.includes(data.gurus[string] || '')) {
                            grouped[key].pengawas.push(data.gurus[string] || '');
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
        <thead class="bg-white">
            <tr class="text-uppercase text-xs font-weight-bold text-secondary">
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Ruang</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Sesi</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Jam Ke</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Mapel<br>(Bank Soal)</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Kelas</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Pengawas</th>
                <th class="text-center align-middle border-bottom" rowspan="2" style="background-color: #f8f9fc;">Jml Siswa</th>
                <th class="text-center align-middle" colspan="3" style="background-color: #f8f9fc;">Status Siswa</th>
            </tr>
            <tr class="text-xs font-weight-bold text-secondary">
                <th class="text-center align-middle border-bottom" style="background-color: #f8f9fc; border-top: 1px solid #e3e6f0;">Blm Login</th>
                <th class="text-center align-middle border-bottom" style="background-color: #f8f9fc; border-top: 1px solid #e3e6f0;">Sdg Login</th>
                <th class="text-center align-middle border-bottom" style="background-color: #f8f9fc; border-top: 1px solid #e3e6f0;">Selesai</th>
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
                    // Enhanced Mapel styling
                    infoMapel += `
                        <div class="mb-1">
                            <span class="fw-bold text-dark">${jad.bank.mapel.kode}</span>
                            <span class="badge bg-light text-dark border border-secondary shadow-sm ms-1">${jad.bank.bank_kode}</span>
                        </div>`;
                }
                const bank_kelas = getUniqueClassNames(ruang_sesi, jadwal).map(k => `<span class="badge bg-purple bg-gradient-purple text-white me-1 mb-1">${k}</span>`).join('');
                // Using map to wrap classes in badges

                const idsGuru = jadwal.map(j => j.bank.bank_guru_id).join(',');

                bodyHtml += `<tr class="bg-white">`;
                // Ruang column with rowspan
                const ruangId = ruang_sesi.ruang_id;
                if (!usedRuangIds.has(ruangId)) {
                    const rowspanValue = rowCounts.get(String(ruangId)) || 1;
                    bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle border-bottom-bold font-weight-bold text-dark bg-white shadow-sm" style="border-right: 1px solid #dee2e6;">${ruang_sesi.nama_ruang}</td>`;
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
                    bgRow = genap ? 'bg-light' : '';
                    const rowspanValue = sesiRowCounts.get(sesiKey) || 1;
                    bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-sesi ${bBold} bg-white" style="border-right: 1px solid #dee2e6;">${ruang_sesi.nama_sesi}</td>`;
                    usedSesiKeys.add(sesiKey);
                    curSesi++;
                }

                // Data Rows with improved readability
                bodyHtml += `
            <td class="text-center align-middle td-jam ${bBold}">${jam_ke}</td>
            <td class="align-middle td-mapel jadwal ${bBold}">${infoMapel}</td>
            <td class="text-center align-middle ${bBold}">${bank_kelas}</td>
            <td class="text-center align-middle td-pengawas ${bBold}" data-guru="${idsGuru}">${pengawas.length > 0 ? pengawas.join('<br>') : '<span class="text-muted text-xs text-italic">Belum ada</span>'}</td>
            <td class="text-center align-middle text-bold ${bBold}" style="font-size: 1.1em;">${peserta > 0 ? peserta : '-'}</td>
            
            <td class="text-center align-middle ${bBold}"><span class="badge bg-danger shadow-sm" style="min-width: 35px; padding: 6px; font-size: 0.9em;">${blm_login}</span></td>
            <td class="text-center align-middle ${bBold}"><span class="badge bg-info shadow-sm" style="min-width: 35px; padding: 6px; font-size: 0.9em;">${sdg_login}</span></td>
            <td class="text-center align-middle ${bBold}"><span class="badge bg-success shadow-sm" style="min-width: 35px; padding: 6px; font-size: 0.9em;">${selesai}</span></td>
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