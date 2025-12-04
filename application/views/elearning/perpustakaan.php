<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <div class="card-tools">
                        <button type="button" onclick="window.location.reload()" class="btn btn-sm btn-warning">
                            <i class="fa fa-sync"></i> <span class="d-none d-sm-inline-block ml-1">Reload</span>
                        </button>
                        <button type="button" data-toggle="modal" data-target="#createPustakaModal"
                                class="btn btn-sm btn-primary" onclick="create(this)"><i
                                    class="fas fa-plus"></i><span class="d-none d-sm-inline-block ml-1">Tambah Link Perpustakaan</span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="prev-container">
                            <div class="prev-header">
                                <h3>Daftar Perpustakaan Digital</h3>
                                <div class="d-flex flex-row">
                                    <label class="align-self-center my-0 mr-2"><i class="fa fa-search"></i></label>
                                    <input id="input-search" type="search" class="form-control form-control-sm" placeholder="" aria-controls="users">
                                </div>
                            </div>
                            <ul class="prev-tab-list" id="tab-list">
                                <?php if (count($pustaka) > 0):
                                foreach ($pustaka as $key=>$lib) : ?>
                                    <li class="prev-tab-item">
                                        <div class="prev-tab-icon icon-tugas-belajar">
                                            <img src="https://www.google.com/s2/favicons?domain=<?=$lib->url?>"
                                                 alt="Tugas Belajar Icon">
                                        </div>
                                        <div class="prev-tab-content">
                                            <div class="prev-title"><?= $lib->title ?? $lib->siteName ?></div>
                                            <div class="prev-url-info">
                                                <div class="prev-desc"><?= $lib->description ?></div>
                                                <span class="prev-domain text-xs"><?= $lib->url ?></span>
                                                <span class="btn btn-xs btn-link"
                                                      onclick="window.open('<?=$lib->url?>', '_blank');">
                                                    <i class="fa fa-globe-americas"></i><span class="text-xs"> Kunjungi Web</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="">
                                            <button class="btn btn-more"
                                                    type="button" id="dropdownMenu2" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                                <button class="dropdown-item btn" type="button"
                                                        data-id="<?=$lib->id?>"
                                                        data-url="<?=$lib->url?>"
                                                        data-title="<?=$lib->title?>"
                                                        data-desc="<?=$lib->description?>"
                                                        data-sitename="<?=$lib->siteName?>"
                                                        onclick="edit(this)">
                                                    <a class="text-warning"><i class="fa fa-pencil text-yellow"></i> Edit</a>
                                                </button>
                                                <button class="dropdown-item btn" type="button"
                                                        data-id="<?=$lib->id?>" onclick="hapus(this)">
                                                    <a class="text-red"> <i class="fa fa-trash-o text-red"></i> Hapus</a>
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="prev-tab-item alert-default-warning">Belum ada link perpustakaan</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="createPustakaModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Link Perpustakaan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?= form_open('create', array('id' => 'create')) ?>
            <input type="hidden" id="id_url" name="id" value="">
            <input type="hidden" id="img_url" name="image" value="">
            <div class="modal-body">
                <div class="form-group row">
                    <label for="url_address" class="col-md-4 col-form-label">URL Address*</label>
                    <div class="col-md-8">
                        <input type="url" class="form-control" name="url_address"
                               id="url_address" placeholder="Link Perpustakaan" required>
                        <small class="alert-default-warning">Harus diawali dengan http/https</small>
                    </div>
                </div>
                <div id="info-web" class="d-none">
                    <p id="info-web-success"> Informasi di bawah ini didapatkan secara otomatis</p>
                    <div class="form-group row">
                        <label for="title" class="col-md-4 col-form-label">Judul Singkat*</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="title"
                                   id="title" placeholder="Judul Web Singkat" required disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="site_name" class="col-md-4 col-form-label">Nama Situs</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="site_name"
                                   id="site_name" placeholder="Nama Situs">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description" class="col-md-4 col-form-label">Deskripsi</label>
                        <div class="col-md-8">
                        <textarea class="form-control" id="description" name="description"
                                  placeholder="Deskkripsi"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'up')) ?>
<?= form_close() ?>

<script>
    let cur_info = {};
    $(document).ready(function () {
        $('#createPustakaModal').on('show.bs.modal', function (e) {
            const id = cur_info.id || null;
            const url = cur_info.url || '';
            const title = cur_info.title || '';
            const siteName = cur_info.siteName || '';
            const desc = cur_info.description || '';
            const image = cur_info.image || '';
            const isEdit = cur_info.isEdit || false;
            const message = cur_info.message || 'Informasi di bawah ini didapatkan secara otomatis';

            $(e.currentTarget).find('#id_url').val(id);
            $(e.currentTarget).find('#img_url').val(image);
            $(e.currentTarget).find('#url_address').val(url);
            $(e.currentTarget).find('#title').val(title);
            $(e.currentTarget).find('#site_name').val(siteName);
            $(e.currentTarget).find('#description').val(desc);

            if (!isEdit) {
                $('#title').attr('disabled', 'disabled');
                $('#info-web').addClass('d-none');
                return
            }
            $('#info-web-success').addClass('text-success').text(message);
            $('#title').removeAttr('disabled');
            $('#info-web').removeClass('d-none');
        });

        $('#create').submit('click', async function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const formData = new FormData($('#create')[0]);
            $('#createPustakaModal').modal('hide').data('bs.modal', null);

            if ($('#title').is(':disabled')) {
                await checkInfoWeb(formData)
            } else {
                saveWeb(formData)
            }
        });

        $('#input-search').quicksearch('#tab-list li');
    });

    function create(btn) {
        cur_info = {
            "title": '',
            "description": '',
            "image": '',
            "url": '',
            "siteName": ''
        };
        $('#createPustakaModal').modal('show')
    }

    function edit(btn) {
        cur_info = {
            "id": $(btn).data('id'),
            "title": $(btn).data('title'),
            "description": $(btn).data('desc'),
            "image": $(btn).data('image') || '',
            "url": $(btn).data('url'),
            "siteName": $(btn).data('sitename') || '',
            "isEdit": true,
        }
        $('#createPustakaModal').modal('show')
    }

    async function checkInfoWeb(formData) {
        swal.fire({
            title: "Memeriksa",
            text: "Memeriksa ketersediaan alamat web ...",
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });

        const webInfo = await ajaxPost('elearning/get_web_info', formData);
        const data = webInfo.info;
        swal.close();
        console.log('info', data)

        $('#title').removeAttr('disabled');
        let message = '';
        if (data.title === 'Just a moment...' || data.title === 'Tidak ada judul') {
            data.title = '';
            message = 'Judul tidak ditemukan, silahkan isi secara manual';
        } else {
            message = 'Situs web tersedia, silahkan periksa dan simpan';
        }

        cur_info = {
            "title": data.title || '',
            "description": data.description || '',
            "image": data.image || '',
            "url": data.url,
            "siteName": data.siteName || '',
            "isEdit": true,
            "message": message
        };

        $('#info-web').removeClass('d-none');
        $('#createPustakaModal').modal('show')
    }

    function saveWeb(formData) {
        swal.fire({
            title: "Menyimpan",
            text: "Silahkan tunggu....",
            allowEscapeKey: false,
            allowOutsideClick: false,
            onOpen: () => {
                swal.showLoading();
            }
        });

        $.ajax({
            url: base_url + "elearning/add_pustaka",
            type: "POST",
            processData: false,
            contentType: false,
            data: formData,
            success: function (response) {
                swal.fire({
                    title: response.status ? "Sukses" : "Gagal",
                    text: response.msg,
                    icon: response.status ? "success" : "error"
                }).then(result => {
                    window.location.reload()
                });
            }, error: function (xhr, status, error) {
                console.log("error", xhr.responseText);
                Swal.fire({
                    title: "Gagal",
                    html: xhr.responseText,
                    icon: "error"
                });
            }
        });
    }

    function launchWeb(btn) {
        const url = $(btn).data('url');
        window.open(url, '_blank');
    }

    function hapus(btn) {
        const id = $(btn).data('id');
        swal.fire({
            title: "Anda yakin?",
            text: "Data akan dihapus!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus!"
        }).then(result => {
            if (result.value) {
                $.ajax({
                    data: {id_url: id},
                    type: "GET",
                    url: base_url + "elearning/del_pustaka/",
                    cache: false,
                    success: function (response) {
                        console.log(response);
                        swal.fire({
                            title: response.status ? "Sukses" : "Gagal",
                            text: response.msg,
                            icon: response.status ? "success" : "error"
                        }).then(result => {
                            window.location.reload()
                        });
                    },
                    error: function (xhr, status, error) {
                        console.log("error", xhr.responseText);
                        Swal.fire({
                            title: "Gagal",
                            html: xhr.responseText,
                            icon: "error"
                        });
                    }
                });
            }
        });
    }

    async function ajaxPost(url, formData) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    method: "POST",
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (respon) {
                        if (respon.success) {
                            resolve(respon); // Resolusi Promise ketika sukses
                        } else {
                            reject(respon.message); // Rejeksi Promise ketika ada error
                        }
                    },
                    error: function (xhr, status, error) {
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 1000)
        });
    }

</script>


