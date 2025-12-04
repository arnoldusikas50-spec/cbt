<?php

?>
<div class="content-wrapper" style="margin-top: -1px;">
    <div class="sticky">
    </div>
    <section class="content overlap pt-4">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <div class="info-box bg-transparent shadow-none">
                        <img class="d-flex mr-2 rounded-circle" onerror="this.src='<?= base_url('assets/img/logo.png') ?>'" src="<?= base_url() . $setting->logo_kiri ?>" width="60" height="60">
                        <div class="info-box-content">
                                <span class="text-white d-inline-block text-truncate"
                                      style="font-size: 18pt; line-height: 1.3;max-width: 380px"><b><?= $setting->nama_aplikasi ?? 'GarudaCBT' ?></b></span>
                            <span class="text-white">C B T   A p p l i c a t i o n</span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="float-right mt-2 d-none d-md-inline-block">
                        <!-- <div class="float-left ml-4">
                           <img class="d-flex mr-2 rounded-circle" onerror="this.src='<?= base_url('assets/img/icongaruda.gif') ?>'" src="<?= base_url($siswa->foto) ?>" width="90" height="90" alt="foto">
                        </div>
                        <div class="float-right" style="line-height: 1.2">
                            <span class="text-white"><b><?= $siswa->nama ?></b></span>
                            <br>
                            <span class="text-white"><?= $siswa->nis ?></span>
                            <br>
                            <span class="text-white"><?= $siswa->nama_kelas ?></span>
                        </div> --> <!-- ikasmedia -->
                    </div>
                </div>
            </div>
        </div>
        <div class="container"
             style="-moz-user-select: none; -webkit-user-select: none; -ms-user-select:none; user-select:none;-o-user-select:none;"
             unselectable="on">
            <div class="row">
                <div class="col-12 d-none" id="approved">
                    <div class="card my-shadow">
                        <div class="card-header p-4">
                            <div class="card-title">
                                <span class="text-purple"><b>[<?= $siswa->nama ?>]</b></span>
                                <img class="d-flex mr-2 rounded-circle" onerror="this.src='<?= base_url('assets/img/siswa.png') ?>'" src="<?= base_url($siswa->foto) ?>" width="60" height="60" alt="foto"> <!-- ikasmedia -->
                                <br>
                                NOMOR:
                                <div id="nomor-soal" class="btn bg-primary no-hover ml-2 text-lg"></div>
                            </div>
                            <div class="card-tools">
                                <button class="btn btn-outline-danger btn-oval-sm no-hover">
                                    <span class='mr-4 d-none d-md-inline-block'><b>Sisa Waktu</b></span>
                                    <span id="timer" class="text-bold">00:00:00</span>
                                </button>
                                <button data-toggle="modal" data-target="#daftarModal"
                                        class="btn btn-primary btn-oval-sm">
                                    <span class="d-none d-md-inline-block mr-2"><b>Daftar Soal</b></span>
                                    <i class="fa fa-th"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="zoom-tool-bar"></div>
                            <div style="border: 1px solid; border-color: #D3D3D3;">
                                <div class="konten-soal-jawab">
                                    <div class="row p-2 mb-4 ml-1">
                                        <div id="konten-soal" class="table-responsive question"></div>
                                    </div>
                                    <?= form_open('jawab', array('id' => 'jawab')) ?>
                                    <input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?>">
                                    <input type="hidden" name="jadwal" value="<?= $id_jadwal ?? '' ?>">
                                    <input type="hidden" name="bank" value="<?= $id_bank ?? '' ?>">
                                    <div class="row p-3">
                                        <div id="konten-jawaban" class="col-12">
                                        </div>
                                    </div>
                                    <?= form_close() ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between bd-highlight">
                                <div class="bd-highlight">
                                    <button class="btn btn-danger btn-oval-sm" id="prev" onclick="prevSoal()">
                                        <i class="fa fa-arrow-circle-left"></i>
                                        <span class="ml-2 d-none d-md-inline-block"><b>Soal Sebelumnya</b></span>
                                    </button>
                                </div>
                                <div class="bd-highlight">
                                    <button class="btn btn-oval-sm btn-danger btn-disabled d-none" id="timer-selesai" disabled></button>
                                    <button class="btn btn-oval-sm" id="next" onclick="nextSoal()">
                                        <span id="text-next" class="mr-2 d-none d-md-inline-block"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="overlay" id="loading">
                            <!-- <div class="spinner-grow"></div>
                            <div class="pl-3">MEMUAT SOAL</div> -->
                            <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                            Load Bank Soal...
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 d-none" id="noapproved">
                    <div class="alert alert-default-danger p-4">
                        <h4><i class="icon fas fa-ban"></i> WARNING..!!</h4>
                        <div class="text-lg" id="err-msg"></div>
                        <div class="row">
                            <div class="col-12">
                                <button class="btn btn-danger float-right" onclick="kembali()">Kembali</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="daftarModal" tabindex="-1" role="dialog" aria-labelledby="daftarLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="daftarLabel">Daftar Nomor Soal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="konten-modal">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">TUTUP</button>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'up')) ?>
<input type="hidden" name="siswa" value="<?= $siswa->id_siswa ?? '' ?>">
<input type="hidden" name="jadwal" value="<?= $id_jadwal ?? '' ?>">
<input type="hidden" name="bank" value="<?= $id_bank ?? '' ?>">
<?= form_close() ?>

<script src="<?= base_url() ?>/assets/app/js/redirect.js"></script>
<script src="<?= base_url() ?>/assets/app/js/linker-list.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ElementQueries.js"></script>
<script src="<?= base_url() ?>/assets/plugins/element-queries/ResizeSensor.js"></script>
<script src="<?= base_url() ?>/assets/plugins/katex/katex.min.js"></script>
<script src="<?= base_url() ?>/assets/app/js/content-zoom-slider.js"></script>
<script src="<?= base_url() ?>/assets/app/js/mediaPlayer.js"></script>

<script>
    var elem = document.documentElement;
    history.pushState(null, null, '<?php echo $_SERVER["REQUEST_URI"]; ?>');
    window.addEventListener('popstate', function (event) {
        loadSoalNomor(1);
    });
    let infoBank;
    let nomorSoal = 0;
    let idSoal, idSoalSiswa, jenisSoal, modelSoal, typeSoal;
    let jawabanSiswa, jawabanBaru = null, jsonJawaban;
    let nav = 0;
    let soalTerjawab = 0, soalTotal = 0;
    let timerOut;
    let timerSelesai;
    let elapsed = '0';
    let jarak = 0;
    let h, m, s;
    var message = "Jangan menggunakan klik kanan!";

    let tick = 0;
    const _second = 1000,
        _minute = _second * 60,
        _hour = _minute * 60,
        _day = _hour * 24;
    let durasiUjian// = Number(infoBank.jadwal?.durasi_ujian);
    let dif;
    let fieldLinks;
    let zoomClicked = 1;
    var arrSize = [];
    let trying = 0;

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
                        resolve(respon); // Resolusi Promise ketika sukses
                    },
                    error: function (xhr, status, error) {
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 200)
        });
    }

    async function ajaxGet(url) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    type: "GET",
                    success: function (respon) {
                        resolve(respon); // Resolusi Promise ketika sukses
                    },
                    error: function (xhr, status, error) {
                        reject(error); // Rejeksi Promise ketika ada error
                    }
                });
            }, 100)
        });
    }

    $(document).ready(async function () {
        $(document).keydown(function (event) {
            //console.log('press', event.keyCode);
            var charCode = event.charCode || event.keyCode || event.which;
            if (charCode == 27 || charCode == 91 || charCode == 92) {
                return false;
            }
        });
        document.onmousedown = rtclickcheck;

        $('#jawab').on('submit', async function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let formData = new FormData($('#jawab')[0]);
            formData.append('timer', $('#timer').text())

            const jns = jsonJawaban['jenis']
            for (const key in jsonJawaban) {
                if ((jns === '2' || jns === '3') && key === 'jawaban_siswa') {
                    formData.append('data[' + key + ']', JSON.stringify(jsonJawaban[key]))
                } else {
                    formData.append('data[' + key + ']', jsonJawaban[key])
                }
            }

            try {
                const response = await ajaxPost('cbtsiswa/savejawaban', formData)
                console.log('onSubmit', response)
                soalTerjawab = response.soal_terjawab;
                await loadSoalNomor(nav);
            } catch (e) {
                showDangerToast('ERROR!');
                console.log(e);
            }
        });

        $(".konten-soal-jawab").contentZoomSlider({
            toolContainer: ".zoom-tool-bar",
        });

        await validasiJadwal();
    });

    function deteksiUkuranLayar(isOrientationchange) {
        //console.log('orientasi', isOrientationchange)
        //const type = screen.orientation.type
        const pelanggaran = '<?= $pelanggaran ?? '0' ?>';
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

    async function checkDialog(isOk) {
        if (isOk) {
            openFullscreen();
            setTimeout(function () {
                loadSoalNomor(1);
            }, 500);
        } else {
            trying ++;
            openDialog()
        }
    }

    function openDialog() {
        swal.fire({
            title: 'Peraturan Ujian',
            html: 'Kerjakan soal dengan serius,<br>jangan nyontek!' + (trying > 0 ? '<br><p class="text-danger">Anda harus menekan tombil YA</p>' : ''),
            allowEscapeKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'YA',
            allowOutsideClick: false,
        }).then(async (result) => {
            await checkDialog(result.value)
        });
    }

    async function validasiJadwal() {
        let formData = new FormData($('#up')[0]);
        const data = await ajaxPost('cbtsiswa/validasi_jadwal', formData)
        console.log('validasi', data);
        $('#loading').addClass('d-none');
        if (data.approved) {
            const pelanggaran = '<?= $pelanggaran ?? '0' ?>';
            if (pelanggaran != '1') {
                openDialog()
            } else {
                const toleransi = 10;
                const sWidth = screen.width - toleransi;
                const iWidth = window.innerWidth;
                if (iWidth < sWidth) {
                    await hitungPelanggaran('ringan', '2')
                } else {
                    openDialog()
                }
            }
        } else {
            $('#loading').addClass('d-none');
            $('#approved').addClass('d-none');
            $('#noapproved').removeClass('d-none');
            $('#err-msg').html(data.message);
        }
    }

    async function loadSoalNomor(nomor) {
        const toleransi = 10;
        const sWidth = screen.width - toleransi;
        const iWidth = window.innerWidth;
        const pelanggaran = '<?= $pelanggaran ?? '0' ?>';
        if (pelanggaran == '1' && iWidth < sWidth) {
            $('#loading').addClass('d-none');
            $('#approved').addClass('d-none');
            $('#noapproved').removeClass('d-none');
            $('#err-msg').html(`
                    <p class="text-danger">Tidak bisa dilanjutkan. Pastikan aplikasi berjalan dengan kondisi FullScreen!</p>
                `);
            return;
        }

        if (nomor == nomorSoal) {
            return;
        }

        let formData = new FormData($('#up')[0]);
        formData.append('nomor', nomor)
        formData.append('timer', $('#timer').text())
        if (soalTotal === 0 || nomor <= parseInt(soalTotal)) {
            try {
                const data = await ajaxPost('cbtsiswa/loadnomorsoal', formData)
                console.log('load soal', data);
                $('#loading').addClass('d-none');
                if (data.approved) {
                    const pelanggaran = '<?= $pelanggaran ?? '0' ?>';
                    if (pelanggaran != '1') {
                        $('#approved').removeClass('d-none');
                        $('#noapproved').addClass('d-none');
                        setKonten(data);
                    } else {
                        const toleransi = 10;
                        const sWidth = screen.width - toleransi;
                        const iWidth = window.innerWidth;
                        if (iWidth < sWidth) {
                            await hitungPelanggaran('ringan', '2')
                        } else {
                            $('#approved').removeClass('d-none');
                            $('#noapproved').addClass('d-none');
                            setKonten(data);
                        }
                    }
                } else {
                    $('#approved').addClass('d-none');
                    $('#noapproved').removeClass('d-none');
                    $('#err-msg').html(data.message + '<br/>' + data.error || '');
                }
            } catch (e) {
                showDangerToast('ERROR!');
                console.log(e);
            }
        } else {
            await selesai();
        }
    }

    function loadSoal(datas) {
        $('#daftarModal').modal('hide').data('bs.modal', null);
        $('#daftarModal').on('hidden', function () {
            $(this).data('modal', null);
        });

        nav = $(datas).data('nomorsoal');
        var jwb1 = jawabanSiswa;
        var jwb2 = jawabanBaru;
        if ($.isArray(jwb1) || jwb1 instanceof jQuery) {
            jwb1 = JSON.stringify(jwb1)
        }
        if (jwb2 != null && ($.isArray(jwb2) || jwb2 instanceof jQuery)) {
            jwb2 = JSON.stringify(jwb2)
        }

        if (jawabanBaru != null && jwb1 !== jwb2) {
            $('#jawab').submit();
        } else {
            loadSoalNomor(nav);
        }
    }

    function setKonten(data) {
        const media = data.media;
        //infoBank = data.bank;
        durasiUjian = data.jadwal.durasi_ujian
        //console.log('max_jawaban', data.max_jawaban);
        jarak = data.jarak;
        idSoal = data.soal_id;
        idSoalSiswa = data.soal_siswa_id;
        nomorSoal = parseInt(data.soal_nomor);
        jenisSoal = data.soal_jenis;
        soalTerjawab = data.soal_terjawab;
        soalTotal = data.soal_total;

        jsonJawaban = {};
        jawabanBaru = null;
        jawabanSiswa = data.soal_jawaban_siswa || '';
        if ($.isArray(jawabanSiswa)) jawabanSiswa.sort();

        if (nomorSoal === 1) {
            $('#prev').attr('disabled', 'disabled');
        } else {
            $('#prev').removeAttr('disabled');
        }

        $('#nomor-soal').html(nomorSoal);
        $('#konten-soal').html(data.soal_soal);
        $('#konten-soal').attr('data-id', idSoal);
        console.log('id_soal', idSoal)
        var jenis = data.soal_jenis;
        var html = '';
        if (jenis == "1") {
            $.each(data.soal_opsi, function (key, opsis) {
                if (opsis.valAlias != "" && opsis.opsi != "") {
                    html += '<label class="container-jawaban font-weight-normal">' + opsis.opsi +
                        '<input type="radio"' +
                        ' name="jawaban"' +
                        ' value="' + opsis.value.toUpperCase() + '"' +
                        ' data-jawabansiswa="' + opsis.value.toUpperCase() + '"' +
                        ' data-jawabanalias="' + opsis.valAlias.toUpperCase() + '"' +
                        ' onclick="submitJawaban(this)" ' + opsis.checked + '>' +
                        '<span class="checkmark shadow text-center align-middle">' + opsis.valAlias.toUpperCase() + '</span>' +
                        '</label>';
                }
            });
            $('#konten-jawaban').html(html);
        } else if (jenis == "2") {
            $.each(data.soal_opsi, function (key, opsis) {
                html += '<div class="custom-control custom-checkbox checkbox-xl">' +
                    '<input type="checkbox" class="check2 custom-control-input"' +
                    'id="check'+key+'"' +
                    ' name="jawaban"' +
                    ' value="' + opsis.value.toUpperCase() + '"' +
                    ' data-max="' + data.max_jawaban[0] + '"' +
                    ' data-jawabansiswa="' + opsis.value.toUpperCase() + '"' +
                    ' onclick="submitJawaban(this)" ' + opsis.checked + '>' +
                    '<label class="custom-control-label font-weight-normal" for="check'+key+'">'
                    + opsis.opsi +'</label>' +
                    '</div>'
            });
            $('#konten-jawaban').html(html);
        } else if (jenis == "3") {
            modelSoal = data.soal_opsi.model;
            typeSoal = data.soal_opsi.type;
            let konten = $('#konten-jawaban')
            konten.html('');

            const dataJawab = data.soal_opsi
            const copy = $.extend(true, {}, dataJawab);
            //console.log('test', copy)

            let arrData = [copy.tabel[0]]
            if (Array.isArray(copy.tbody)) {
                for (let i = 0; i < copy.tbody.length; i++) {
                    let val = copy.tbody[i]
                    for (let j = 0; j < val.length; j++) {
                        if (j === 0) val[j] = copy.tabel[i+1][0]
                    }
                    arrData.push(val)
                }
            } else {
                for (let i = 0; i < copy.tabel.length; i++) {
                    let val = copy.tbody[i]
                    if (val) {
                        for (let j = 0; j < val.length; j++) {
                            if (j === 0) val[j] = copy.tabel[i][0]
                        }
                        arrData.push(val)
                    }
                }
            }

            let keys = 0
            let dataMax = {}
            $.each(data.max_jawaban, function (key, val) {
                dataMax[keys] = val
                keys ++
            })

            let objJawaban = {
                jawaban: arrData,
                max: dataMax,
                model: modelSoal,
                type: typeSoal,
            }
            //console.log('obj', objJawaban)
            konten.linkerList({
                enableEditor: false,
                data: objJawaban,
                viewMode: '2',
                id: nomorSoal,
                callback: function (id, data, hasLinks, isOffset) {
                    if (isOffset !== '0') {
                        $.toast({
                            heading: 'Warning',
                            text: 'Maksimal <b>' + isOffset + ' jawaban',
                            showHideTransition: 'slide',
                            icon: 'error',
                            loaderBg: '#f2a654',
                            position: 'bottom-center'
                        })
                    } else {
                        submitJawaban(data)
                    }
                }
            });
        } else if (jenis == "4") {
            html += '<div class="pr-4">' +
                '<span class="">JAWABAN:</span><br>' +
                '<div class="row"><div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-4">'+
                '<input id="jawaban-isian" class="pl-1 form-control" type="text"' +
                ' name="jawaban" value="' + jawabanSiswa + '"' +
                ' placeholder="Tulis jawaban disini"/><br>' +
                '</div></div>' +
                '</div>';
            $('#konten-jawaban').html(html);

            $("#jawaban-isian").on('change keyup paste', function () {
                submitJawaban(null);
            });
        } else {
            html += '<div class="pr-4">' +
                '<label>JAWABAN:</label><br>' +
                '<textarea id="jawaban-essai" class="w-100 pl-1" type="text"' +
                ' name="jawaban" rows="4"' +
                ' placeholder="Tulis jawaban disini">' + jawabanSiswa + '</textarea><br>' +
                '</div>';
            $('#konten-jawaban').html(html);

            $('#jawaban-essai').summernote({
                placeholder: 'Tulis Jawaban disini, tidak dibolehkan copy paste!',
                tabsize: 2,
                minHeight: 100,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    //['insert', ['link', 'picture', 'math']],
                    ['cleaner',['cleaner']],
                ],
                callbacks: {
                    onKeyup: function(e) {
                        submitJawaban(null);
                    },
                    onChange: function(contents, $editable) {
                        submitJawaban(null);
                    }
                }
            });
        }

        $('#konten-modal').html(data.soal_modal);

        var $imgs = $('.konten-soal-jawab').find('img');
        $.each($imgs, function () {
            var curSrc = $(this).attr('src');
            if (!curSrc.includes("uploads")) return;
            var newSrc = '';
            if (curSrc.indexOf("http") === -1 && curSrc.indexOf("data:image") === -1) {
                newSrc = base_url + curSrc;
                $(this).attr('src', newSrc);
            } else if (curSrc.indexOf(base_url) === -1) {
                var pathUpload = 'uploads';
                var forReplace = curSrc.split(pathUpload);
                newSrc = base_url + pathUpload + forReplace[1];
                $(this).attr('src', newSrc);
				$(this).removeAttr('alt');
            }
            $(this).on('load', function () {
                if ($(this).height() > 50) {
                    $(this).addClass('img-fluid');
                }
            });
        });

        var pathUpload = 'uploads';
        $.each($('audio'), function (ind) {
            var curSrc = $(this).attr('src');
            if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
            } else {
                var forReplace = curSrc.split(pathUpload);
                $(this).attr('src', base_url + pathUpload + forReplace[1]);
            }

            const dataId = idSoal + '-'+ ind;
            const m_audio = media.find(item => item.id === dataId && item.type === 'audio');
            this.renderAudio({
                'idSoal': dataId,
                'played': m_audio?.play || 0,
                callback: function (idMedia, playCount) {
                    console.log(`Audio [${idMedia}] played: ${playCount}`);
                    savePlayed('audio', idSoal, idMedia, playCount)
                }
            });
        });

        $.each($('video'), function (ind) {
            var curSrc = $(this).attr('src');
            if (curSrc.indexOf("base64") > 0 || !curSrc.includes("uploads")) {
            } else {
                var forReplace = curSrc.split(pathUpload);
                $(this).attr('src', base_url + pathUpload + forReplace[1]);
            }

            const dataId = idSoal + '-'+ ind;
            const m_video = media.find(item => item.id === dataId && item.type === 'video');
            this.renderVideo({
                'idSoal': dataId,
                'played': m_video?.play || 0,
                callback: function (idMedia, playCount) {
                    console.log(`Video [${idMedia}] played: ${playCount}`);
                    savePlayed('video', idSoal, idMedia, playCount)
                }
            });
        });

        $('.check').change(function (e) {
            var row = $(e.target).closest('tr');
            var isChecked = $(row).find("input:checked");
            var max = $(e.target).data('max');
            if (isChecked.length > max) {
                $(e.target).prop('checked', !$(this).prop('checked'));
                $.toast({
                    heading: 'Warning',
                    text: 'Maksimal <b>' + max + ' jawaban',
                    showHideTransition: 'slide',
                    icon: 'error',
                    loaderBg: '#f2a654',
                    position: 'bottom-center'
                })
            } else {
                submitJawaban(null);
            }
        });

        if (!data.durasi) {
            window.location.href = base_url + 'cbtsiswa/cbt';
        } else {
            setElapsed(data.durasi);

            if (timerSelesai) {
                clearTimeout(timerSelesai);
                timerSelesai = null;
            }

            var next = $('#next');
            next.removeAttr('disabled');
            var txtnext = $('#text-next');
            $('#ic-btn').remove();

            if (soalTotal === nomorSoal && data.durasi.mulai != null && data.durasi.mulai != '0') {
                next.removeClass('btn-primary');
                next.addClass('btn-success');
                next.append('<i id="ic-btn" class="fa fa-check-circle"></i>');
                txtnext.html('<b>Selesai</b>');
                setTimerSelesai(next, data.durasi);
            } else {
                $('#timer-selesai').addClass('d-none');
                next.removeClass('d-none');
                next.removeClass('btn-success');
                next.addClass('btn-primary');
                next.append('<i id="ic-btn" class="fa fa-arrow-circle-right"></i>');
                txtnext.html('<b>Soal Berikutnya</b>');
            }
        }

        arrSize = [];
    }

    function setElapsed(durasi) {
        elapsed = durasi.lama_ujian == null || durasi.lama_ujian == '0' ? "00:00:00" : durasi.lama_ujian;
        createTimerCountdown(durasiUjian, elapsed.split(':'), async function (isOver, remaining, onGoing) {
            $('#timer').html(remaining);
            elapsed = onGoing;
            if (isOver) {
                $('#prev').attr('disabled', 'disabled');
                $('#next').attr('disabled', 'disabled');

                try {
                    let formData = new FormData($('#jawab')[0]);
                    formData.append('elapsed', elapsed)
                    formData.append('data', JSON.stringify(jsonJawaban))
                    const respon = await ajaxPost('cbtsiswa/savejawaban', formData)
                    console.log('onElapsed', respon)
                    if (respon.success) {
                        $('.konten-soal-jawab').html('');
                        dialogWaktu();
                    } else {
                        swal.fire({
                            title: "Sudah Habis",
                            text: "Waktu Ujian sudah habis, hubungi proktor",
                            icon: "warning",
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "OK"
                        }).then(result => {
                            if (result.value) {
                                window.location.href = base_url + 'cbtsiswa/cbt';
                            }
                        });
                    }
                } catch (e) {
                    console.log(e);
                }
            }
        })
    }

    function nextSoal() {
        $('#next').attr('disabled', 'disabled');
        $('#loading').removeClass('d-none');
        nav = (nomorSoal + 1);
        var jwb1 = jawabanSiswa;
        var jwb2 = jawabanBaru;
        if ($.isArray(jwb1) || jwb1 instanceof jQuery) {
            jwb1 = JSON.stringify(jwb1)
        }
        if (jwb2 != null && ($.isArray(jwb2) || jwb2 instanceof jQuery)) {
            jwb2 = JSON.stringify(jwb2)
        }

        if (jawabanBaru != null && jwb1 !== jwb2) {
            $('#jawab').submit();
        } else {
            loadSoalNomor(nav);
        }
    }

    function setTimerSelesai(next, durasi) {
        const perdetik = 1000;
        const permenit = 60 * perdetik;
        const perjam = 60 * permenit;
        const t_dur = Number(jarak) * (1000 * 60);

        const elapsed = (durasi.lama_ujian == null || durasi.lama_ujian == '0' ? "00:00:00" : durasi.lama_ujian).split(':');
        let t_jam = Number(elapsed[0]);
        let t_mnt = Number(elapsed[1]);
        let t_dtk = Number(elapsed[2]);

        const btnTimer = $('#timer-selesai');
        setTimer();

        function setTimer() {
            if (timerSelesai) {
                clearTimeout(timerSelesai);
                timerSelesai = null;
            }

            const elapsedMicro = (t_jam * perjam) + (t_mnt * permenit) + (t_dtk * perdetik);
            const t_remaining = t_dur - elapsedMicro;
            if (t_remaining <= 0) {
                next.removeClass('d-none');
                btnTimer.addClass('d-none');
            } else {
                // elapsed
                t_dtk++;
                if (t_dtk > 59) {
                    t_dtk = 0;
                    t_mnt++;
                }
                if (t_mnt > 59) {
                    t_mnt = 0;
                    t_jam++;
                }

                // remaining
                const r_jam = Math.floor(t_remaining / perjam);
                const r_mnt = Math.floor((t_remaining % perjam) / permenit);
                const r_dtk = Math.floor((t_remaining % permenit) / perdetik);
                next.addClass('d-none');
                btnTimer.removeClass('d-none');
                btnTimer.html(zeroPad(r_jam) + ':' + zeroPad(r_mnt) + ':' + zeroPad(r_dtk));

                timerSelesai = setTimeout(setTimer, 1000);
            }
        }
    }

    function prevSoal() {
        $('#prev').attr('disabled', 'disabled');
        $('#loading').removeClass('d-none');
        nav = (nomorSoal - 1);
        var jwb1 = jawabanSiswa;
        var jwb2 = jawabanBaru;
        if ($.isArray(jwb1) || jwb1 instanceof jQuery) {
            jwb1 = JSON.stringify(jwb1)
        }
        if (jwb2 != null && ($.isArray(jwb2) || jwb2 instanceof jQuery)) {
            jwb2 = JSON.stringify(jwb2)
        }

        if (jawabanBaru != null && jwb1 !== jwb2) {
            $('#jawab').submit();
        } else {
            loadSoalNomor(nav);
        }
    }

    function updateModal(jwb) {
        var badges = $('#konten-modal').find(`#badge${nomorSoal}`);
        var btn = $('#konten-modal').find(`#btn${nomorSoal}`);
        btn.removeClass("btn-outline-secondary");
        btn.addClass("btn-primary");
        if (jenisSoal == 1) {
            if (badges.length) {
                $(`#badge${nomorSoal}`).text(jwb)
            } else {
                var badge = '<div id="badge' + nomorSoal + '" class="badge badge-pill badge-success border border-dark text-yellow"' +
                    ' style="font-size:12pt; width: 30px; height: 30px; margin-top: -60px; margin-left: 30px;">' +
                    jwb +
                    '</div>';
                $(`#box${nomorSoal}`).append(badge);
            }
        } else {
            if (!badges.length) {
                var badge = '<div id="badge' + nomorSoal + '" class="badge badge-pill badge-success border border-dark"' +
                    ' style="font-size:12pt; width: 30px; height: 30px; margin-top: -60px; margin-left: 30px;">' +
                    '&check;</div>';
                $(`#box${nomorSoal}`).append(badge);
            }
        }
    }

    function submitJawaban(opsi) {
        var jawaban_Siswa = '', jawaban_Alias = '';
        if (jenisSoal == 1) {
            jawaban_Siswa = $(opsi).data('jawabansiswa');
            jawaban_Alias = $(opsi).data('jawabanalias');
        } else if (jenisSoal == 2) {
            var isChecked = $('#konten-jawaban').find("input:checked");
            var max = $(opsi).data('max');
            //console.log('max:'+max, 'checked:'+isChecked.length);
            if (isChecked.length > max) {
                $(opsi).prop('checked', !$(opsi).prop('checked'));
                $.toast({
                    heading: 'Warning',
                    text: 'Maksimal <b>' + max + ' jawaban',
                    showHideTransition: 'slide',
                    icon: 'error',
                    loaderBg: '#f2a654',
                    position: 'bottom-center'
                });
                return;
            } else {
                var selected = [];
                $('#konten-jawaban input:checked').each(function () {
                    selected.push($(this).val());
                });
                jawaban_Siswa = selected;
            }
        } else if (jenisSoal == 3) {
            jawaban_Siswa = opsi
        } else if (jenisSoal == 4){
            jawaban_Siswa = $('#jawaban-isian').val();
        } else {
            jawaban_Siswa = $('#jawaban-essai').summernote('code');
        }
        jawabanBaru = jawaban_Siswa;
        if (jenisSoal == 2) {
            if ($.isArray(jawabanBaru)) jawabanBaru.sort();
        }

        updateModal(jawaban_Alias);
        jsonJawaban = createJsonJawaban(jawaban_Alias, jawaban_Siswa);
        //console.log('getJawaban', jsonJawaban)
    }

    function createJsonJawaban(jawab_Alias, jawab_Siswa) {
        var item = {};
        item ["no_soal_alias"] = nomorSoal;
        item ["jawaban_alias"] = jawab_Alias;
        item ["jawaban_siswa"] = jawab_Siswa;
        item ["jenis"] = jenisSoal;
        item ["id_soal"] = idSoal;
        item ["id_soal_siswa"] = idSoalSiswa;
        //item ["id_jadwal"] = jadwal;
        //item ["id_bank"] = bank;
        //item ["id_siswa"] = siswa;

        return item;
    }

    function getDataTable() {
        return $('#table-jodohkan tr').get().map(function (row) {
            const $tables = [];

            $(row).find('th').get().map(function (cell) {
                var klm = $(cell).text().trim();
                $tables.push(klm == "" ? "#" : encode(klm));
            });

            $(row).find('td').get().map(function (cell) {
                if ($(cell).children('input').length > 0) {
                    $tables.push($(cell).find('input').prop("checked") === true ? "1" : "0");
                } else {
                    $tables.push(encode($(cell).text().trim()))
                }
            });

            return $tables;
        });
    }

    function convertTable(data) {
        const head = []
        const body = []
        $.each(data.tabel, function (idx, val) {
            if (idx === 0) {
                $.each(val, function (id, vl) {
                    if (vl !== "#") head.push(encode(vl))
                })
            } else {
                $.each(val, function (id, vl) {
                    if (id === 0) body.push(encode(vl))
                })
            }
        })
        var kanan = data.thead;
        var kiri = [];
        $.each(data.tbody, function (i, v) {
            kiri.push(encode(v.shift()));
        });
        kanan.shift();

        var linked = [];
        $.each(data.tbody, function (n, arv) {
            $.each(arv, function (t, v) {
                if (v == '1') {
                    var it = {};
                    it['from'] = encode(body[n]);
                    it['to'] = encode(head[t]);
                    linked.push(it);
                }
            });
        });
        var item = {};
        item['type'] = data.type;
        item['jawaban'] = [body, head];
        item['linked'] = linked;
        return item;
    }

    function convertTableToList(data) {
        const kanan = data.thead;
        //console.log('kanan', kanan);
        const kiri = [];
        $.each(data.tbody, function (i, v) {
            kiri.push(decode(v.shift()));
        });
        kanan.shift();
        //console.log('kiri', kiri);
        $.each(kanan, function (i, v) {
            kanan[i] = (decode(v));
        });

        var linked = [];
        $.each(data.tbody, function (n, arv) {
            $.each(arv, function (t, v) {
                if (v == '1') {
                    var it = {};
                    it['from'] = decode(kiri[n]);
                    it['to'] = decode(kanan[t]);
                    linked.push(it);
                }
            });
        });
        var item = {};
        item['type'] = data.type;
        item['jawaban'] = [kiri, kanan];
        item['linked'] = linked;
        //console.log('test', item);
        return item;
    }

    function getListData() {
        var kolom = [];
        var baris = [];
        $(".FL-left li").each(function () {
            baris.push(encode($(this).text()));
        });
        $(".FL-right li").each(function () {
            kolom.push(encode($(this).text()));
        });
        return [kolom, baris];
    }

    async function selesai() {
        if (soalTotal === soalTerjawab) {
            swal.fire({
                title: "Kamu yakin?",
                text: "Kamu akan menyelesaikan ujian",
                icon: "warning",
                allowEscapeKey: false,
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Selesaikan!"
            }).then(async result => {
                if (result.value) {
                    try {
                        let formData = new FormData($('#up')[0]);
                        const respon = await ajaxPost('cbtsiswa/selesaiujian', formData)
                        window.location.href = base_url + 'cbtsiswa/cbt';
                    } catch (e) {
                        swal.fire({
                            title: "Gagal",
                            text: "Tidak bisa menyelesaikan ujian",
                            icon: "error"
                        });
                    }
                } else {
                    $('#next').removeAttr('disabled');
                    $('#loading').addClass('d-none');
                }
            });
        } else {
            swal.fire({
                title: "BELUM SELESAI!",
                text: "Masih ada soal yang belum dikerjakan",
                icon: "error",
                confirmButtonColor: "#3085d6",
            }).then(result => {
                if (result.value) {
                    $('#next').removeAttr('disabled');
                    $('#loading').addClass('d-none');
                }
            });
        }
    }

    function dialogWaktu() {
        swal.fire({
            title: "Sudah Habis",
            text: "Waktu Ujian sudah habis, hubungi proktor",
            icon: "warning",
            allowEscapeKey: false,
            allowOutsideClick: false,
            confirmButtonColor: "#3085d6",
            confirmButtonText: "OK"
        }).then(result => {
            if (result.value) {
                window.location.href = base_url + 'cbtsiswa/cbt';
            }
        });
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

        return {h: endTime.getHours(), m: endTime.getMinutes(), s: endTime.getSeconds()}
    }

    function createTimerCountdown(durasi, elapsed, func) {
        const perdetik = 1000;
        const permenit = 60 * perdetik;
        const perjam = 60 * permenit;
        const t_dur = durasi * (1000 * 60);

        let t_jam = Number(elapsed[0]);
        let t_mnt = Number(elapsed[1]);
        let t_dtk = Number(elapsed[2]);

        testTimer();

        function testTimer() {
            if (timerOut) {
                clearTimeout(timerOut);
                timerOut = null;
            }

            const elapsedMicro = (t_jam * perjam) + (t_mnt * permenit) + (t_dtk * perdetik);
            const t_remaining = t_dur - elapsedMicro;
            if (t_remaining <= 0) {
                if (func && (typeof func == "function")) {
                    func(true, 'Waktu habis', zeroPad(t_jam) + ':' + zeroPad(t_mnt) + ':' + zeroPad(t_dtk));
                }
            } else {
                // elapsed
                t_dtk++;
                if (t_dtk > 59) {
                    t_dtk = 0;
                    t_mnt++;
                }
                if (t_mnt > 59) {
                    t_mnt = 0;
                    t_jam++;
                }

                // remaining
                const r_jam = Math.floor(t_remaining / perjam);
                const r_mnt = Math.floor((t_remaining % perjam) / permenit);
                const r_dtk = Math.floor((t_remaining % permenit) / perdetik);

                if (func && (typeof func == "function")) {
                    func(false,
                        zeroPad(r_jam) + ':' + zeroPad(r_mnt) + ':' + zeroPad(r_dtk),
                        zeroPad(t_jam) + ':' + zeroPad(t_mnt) + ':' + zeroPad(t_dtk)
                    );
                }
                timerOut = setTimeout(testTimer, 1000);
            }
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
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem = window.top.document.body;
            elem.msRequestFullscreen();
        }
    }

    function shouldLogViolation(pelanggaran) {
        return pelanggaran == '1';
    }

    document.addEventListener("visibilitychange", async () => {
        const reset = '<?= $reset_login ?? '0' ?>';
        if (document.hidden) {
            await hitungPelanggaran('berat', reset);
        }
    });

    async function leaveCbt() {
        let formData = new FormData($('#up')[0]);
        const response = await ajaxPost('cbtsiswa/leavecbt', formData);
        console.log('leave', response);

        if (response.success) {
            location.href = base_url + "logout";
        } else {
            swal.fire({
                title: "Gagal",
                text: "Tidak bisa meninggalkan ujian",
                icon: "error"
            });
        }
    }

    async function hitungPelanggaran(jenis, reset) {
        const pelanggaran = '<?= $pelanggaran ?? '0' ?>';

        if (pelanggaran != '1') {
            if (reset == '1') {
                await leaveCbt(); // paksa logout
            } else if (reset == '2') {
                $('#loading').addClass('d-none');
                $('#approved').addClass('d-none');
                $('#noapproved').removeClass('d-none');
                $('#err-msg').html(`
                    <p class="text-danger">Tidak bisa dilanjutkan. Pastikan aplikasi berjalan dengan kondisi FullScreen!</p>
                `);
            }
            return; // tidak dicatat pelanggaran
        }

        let formData = new FormData($('#up')[0]);
        formData.append('jenis', jenis);
        const response = await ajaxPost('cbtsiswa/hitung_pelanggaran', formData);
        console.log('pelanggaran', jenis, reset === '1', response);

        if (response.success) {
            if (reset == '1') {
                await leaveCbt(); // paksa logout
            } else if (reset == '2') {
                $('#loading').addClass('d-none');
                $('#approved').addClass('d-none');
                $('#noapproved').removeClass('d-none');
                $('#err-msg').html(`
                    <p class="text-danger">Tidak bisa dilanjutkan. Pastikan aplikasi berjalan dengan kondisi FullScreen!</p>
                `);
            } else {
                const count = response.pelanggaran.ringan + response.pelanggaran.sedang + response.pelanggaran.berat;
                swal.fire({
                    title: "Pelanggaran Terdeteksi",
                    html: `Kamu telah melakukan <span class="text-danger">${count}x pelanggaran</span>, nilai akan dikurangi`,
                    icon: "warning",
                    allowOutsideClick: false,
                    confirmButtonColor: "#3085d6",
                    allowEscapeKey: false,
                    confirmButtonText: "OK"
                }).then(() => {
                    openFullscreen();
                });
            }
        }
    }

    document.addEventListener('fullscreenchange', function () {
        if (document.fullscreenElement === null) {
            console.log('Exited fullscreen mode.');
            hitungPelanggaran('sedang', '0');
        } else {
            console.log('Entered or still in fullscreen mode.');
        }
    });

    async function savePlayed(type, idSoal, idMedia, playCount) {
        let formData = new FormData($('#jawab')[0]);
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
</script>
