<?php

?>



<style>
    /* Custom Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(24px);
        -ms-transform: translateX(24px);
        transform: translateX(24px);
    }
</style>

<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <button class="card-tools btn btn-danger btn-sm me-2 btn-toggle ms-auto" data-bs-toggle="modal"
                        data-bs-target="#infoModal"><i class="bi bi-info-circle me-1"></i> Info Error
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                    <span
                                        class="alert-default-success input-group-text text-center text-bold px-4">TOKEN</span>
                                </div>
                                <input id="token-input" class="form-control text-bold text-center text-blue"
                                    style="font-size: 34px; letter-spacing: 5px; height: 60px;" readonly="readonly" />
                                <div class="input-group-append" id="kolom-kanan">
                                    <span id="interval" class="input-group-text text-xs text-red d-none">-- : -- :
                                        --</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <label class="form-label" for="tgl">Hari</label>
                            <select name="tgl" id="tgl" class="form-control"></select>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <label class="form-label" for="printby">Filter By</label>
                            <div class="d-flex align-items-center" style="height: 38px;">
                                <span class="me-2" style="cursor: pointer;" onclick="$('#printby').click()">Ruang</span>
                                <label class="toggle-switch mb-0">
                                    <input type="checkbox" id="printby">
                                    <span class="slider"></span>
                                </label>
                                <span class="ms-2" style="cursor: pointer;" onclick="$('#printby').click()">Kelas</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3 d-none" id="by-kelas">
                            <label class="form-label" for="kelas">Kelas</label>
                            <select name="kelas" id="kelas" class="form-control">
                                <option value='' selected='selected'>Pilih Kelas</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-3 by-ruang">
                            <label class="form-label" for="ruang">Ruang</label>
                            <select name="ruang" id="ruang" class="form-control">
                                <option value='' selected='selected'>Pilih Ruang</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-3 by-ruang">
                            <label class="form-label" for="sesi">Sesi</label>
                            <select name="sesi" id="sesi" class="form-control">
                                <option value='' selected='selected'>Pilih Sesi</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row d-none" id="info">
                        <div class="col-12">
                            <div class="alert alert-default-info border-info">
                                <div id="info-penggunaan">
                                    <ul>
                                        <li>
                                            Gunakan tombol <span class="badge bg-primary pt-1 pb-1"><i
                                                    class="bi bi-arrow-repeat ms-1 me-1"></i> Refresh</span>
                                            untuk merefresh halaman
                                        </li>
                                        <li>
                                            <b>RESET WAKTU.</b> Jika siswa logout sebelum selesai dan tidak melanjutkan
                                            sampai waktu ujian habis maka akan ditolak, jika ingin melanjutkan maka
                                            harus reset waktu.
                                        </li>
                                        <li>
                                            <b>RESET WAKTU </b> hanya bisa dilakukan jika <b>Status Jadwal belum
                                                berakhir.</b> Indikator <i
                                                class="bi bi-check-circle text-success me-1 ms-1"></i>
                                        </li>
                                        <li>
                                            Aksi <b>RESET IZIN</b> untuk mengizinkkan siswa mengerjakan ujian di
                                            perangkat berbeda.
                                        </li>
                                        <li>
                                            Aksi <b>PAKSA SELESAI</b> untuk memaksa siswa menyelesaikan ujian.
                                        </li>
                                        <li>
                                            Aksi <b>ULANG</b> untuk mengulang ujian siswa dari awal.
                                        </li>
                                        <li>
                                            <span class="badge bg-success"><i class="bi bi-check ms-1 me-1"></i>
                                                Terapkan Aksi</span>
                                            untuk menerapkan aksi terpilih ke setiap siswa yang dipilih
                                        </li>
                                        <li>
                                            Untuk menarapkan semua AKSI ke siswa maka siswa harus merefresh browser jika
                                            sedang dalam halaman ujian
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="float-start mb-2 d-flex flex-row gap-2">
                                <div style="min-width: 180px">
                                    <select name="jadwal-by" id="jadwal-by" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <button type="button" class="btn btn-outline-primary align-bottom btn-sm"
                                    onclick="refreshStatus()" data-bs-toggle="tooltip" title="Refresh">
                                    <i class="bi bi-arrow-repeat me-1"></i> Refresh
                                </button>
                            </div>
                            <?php
                            $dnone = '';
                            ?>
                            <div class="float-end align-bottom mb-2 d-flex flex-row gap-2">
                                <div style="min-width: 100px">
                                    <input type="search" id="cari-status-siswa" class="form-control form-control-sm"
                                        placeholder="Cari Siswa...">
                                </div>
                                <button type="button" class="btn btn-success btn-sm <?= $dnone ?>"
                                    onclick="terapkanAksi()" data-bs-toggle="tooltip"
                                    title="Terapkan Aksi pada siswa terpilih">
                                    <i class="bi bi-check-circle me-1"></i> Terapkan Aksi
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped table-hover dataTable"
                            id="table-status">
                        </table>
                    </div>
                </div>
                <div class="overlay d-none" id="loading">
                    <!-- <div class="spinner-grow"></div> -->
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                        Loading...
                    </button>
                </div>
            </div>
        </div>
    </section>
    </div>

    <?= form_open('', array('id' => 'reset')) ?>
    <div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xs" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetLabel">Reset Waktu Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" id="id_cbt_siswa" name="id_cbt_siswa" value="">
                        <div class="icheck-success">
                            <input class="radio" type="radio" name="reset" id="reset1" value="1">
                            <label for="reset1">Reset Waktu dari awal</label>
                        </div>
                        <div class="icheck-success">
                            <input class="radio" type="radio" name="reset" id="reset2" value="2">
                            <label for="reset2">Lanjutkan sisa waktu</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">OK</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">BATAL</button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xs" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoLabel">Kode error siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <table class="table w-100">
                        <tr>
                            <td class="text-bold">001</td>
                            <td>:</td>
                            <td>Token salah atau token belum digenerate</td>
                        </tr>
                        <tr>
                            <td class="text-bold">002</td>
                            <td>:</td>
                            <td>Harus reset izin</td>
                        </tr>
                        <tr>
                            <td class="text-bold">003</td>
                            <td>:</td>
                            <td>Harus reset waktu</td>
                        </tr>
                        <tr>
                            <td class="text-bold">004</td>
                            <td>:</td>
                            <td>Soal belum dibuat atau belum dipilih</td>
                        </tr>
                        <tr>
                            <td class="text-bold">005</td>
                            <td>:</td>
                            <td>Siswa menggunakan browser yang tidak mendukung</td>
                        </tr>
                        <tr>
                            <td class="text-bold">006</td>
                            <td>:</td>
                            <td>Internet down atau error dari database/server</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pelanggaranModal" tabindex="-1" role="dialog" aria-labelledby="pelanggaranLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xs" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pelanggaranLabel">Pelanggaran Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <table class="table w-100 table-bordered">
                        <tr>
                            <th class="alert alert-default-primary">Jenis Pelanggaran</th>
                            <th class="alert alert-default-success">Jumlah</th>
                        </tr>
                        <tr>
                            <td class="alert alert-default-warning">
                                Pelanggaran Ringan
                                <ul>
                                    <li>
                                        Memulai ujian dengan tampilan tidak fullscreen
                                    </li>
                                </ul>
                            </td>
                            <td class="text-bold align-middle text-center text-red alert-default-secondary"
                                id="jml-ringan">
                            </td>
                        </tr>
                        <tr>
                            <td class="alert alert-default-danger">
                                Pelanggaran Sedang
                                <ul>
                                    <li>
                                        Mencoba keluar dari fullscreen
                                    </li>
                                </ul>
                            </td>
                            <td class="text-bold align-middle text-center text-red" id="jml-sedang"></td>
                        </tr>
                        <tr>
                            <td class="alert alert-danger">
                                Pelanggaran Berat
                                <ul>
                                    <li>
                                        Mencoba membuka tab baru
                                    </li>
                                    <li>
                                        Keluar dari aplikasi sebelum selesai
                                    </li>
                                    <li>
                                        Me-minimize browser
                                    </li>
                                </ul>
                            </td>
                            <td class="text-bold align-middle text-center text-red alert-default-secondary"
                                id="jml-berat">
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <?= form_open('', array('id' => 'post_data')) ?>
    <?= form_close(); ?>

    <script src="<?= base_url() ?>/assets/app/js/findMyData.js"></script>
    <script>
        let ruangs = {};
        let arrRuang = [];
        var is_admin = "<?= $is_admin ? '1' : '0' ?>";
        var isPanitia = "<?= $this->session->userdata('role') == '4' ? '1' : '0' ?>";
        var isPengawas = "<?= isset($panitia) && isset($panitia->jabatan) && $panitia->jabatan === 'Pengawas' ? '1' : '0' ?>";
        var pengawasRuang = "<?= isset($panitia) && isset($panitia->ruang) ? $panitia->ruang : '' ?>";
        var dnone = '';
        var printBy = '1';
        var url = '';

        var kelas;
        var tglJadwal, ruang, sesi;
        let responseJadwal;
        let jadwalSelected = 'all';

        $(document).ready(function () {
            ajaxcsrf();

            var opsiPrintBy = $("#printby");

            if (isPengawas === '1') {
                opsiPrintBy.prop('checked', false).trigger('change');
                opsiPrintBy.prop('disabled', true);
            }

            opsiPrintBy.change(function () {
                printBy = $(this).prop('checked') ? '2' : '1';
                if (printBy === '1') {
                    $('#by-kelas').addClass('d-none');
                    $('.by-ruang').removeClass('d-none');
                    if (ruang && sesi && tglJadwal) loadSiswaRuang(ruang, sesi, tglJadwal)
                } else {
                    $('#by-kelas').removeClass('d-none');
                    $('.by-ruang').addClass('d-none');
                    if (kelas && tglJadwal) loadSiswaKelas(kelas, tglJadwal)
                }
            });

            var idSiswa = '';
            var idCbtSiswa = '';
            $('#resetModal').on('show.bs.modal', function (e) {
                idSiswa = $(e.relatedTarget).data('siswa');
                idCbtSiswa = $(e.relatedTarget).data('cbt');
                $('#id_cbt_siswa').val(idCbtSiswa)
                //console.log('show', idSiswa, idCbtSiswa)
            });

            $('#reset').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                $('#resetModal').modal('hide').data('bs.modal', null);
                $('#resetModal').on('hidden', function () {
                    $(this).data('modal', null);
                });

                let formData = new FormData($('#reset')[0]);
                //formData.append('time_add', $('#add-time').val())
                //formData.append('id_siswa', idSiswa)

                $.ajax({
                    url: base_url + "cbtstatus/resettimer",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        //console.log(data.status);
                        if (data.status) refreshStatus();
                        else showDangerToast('tidak bisa mereset waktu');
                    }, error: function (xhr, status, error) {
                        console.log('error');
                    }
                });
            });

            $('#tgl').select2({ theme: 'bootstrap4', width: '100%' });
            $('#ruang').select2({ theme: 'bootstrap4', width: '100%' });
            $('#sesi').select2({ theme: 'bootstrap4', width: '100%' });
            $('#kelas').select2({ theme: 'bootstrap4', width: '100%' });

            var opsiJadwal = $("#jadwal-by");
            opsiJadwal.select2({ width: '100%', theme: 'bootstrap4' });
            opsiJadwal.change(function () {
                jadwalSelected = $(this).val();
                if (jadwalSelected === 'all') {
                    createPreview(responseJadwal, true);
                    return;
                }
                const filter = responseJadwal.jadwals.filter(jad => jad.id_jadwal === jadwalSelected);
                const newData = { ...responseJadwal, jadwals: filter.length > 0 ? filter : [] };
                createPreview(newData, true)
            })

            $('#pelanggaranModal').on('show.bs.modal', function (e) {
                const ringan = $(e.relatedTarget).data('ringan');
                const sedang = $(e.relatedTarget).data('sedang');
                const berat = $(e.relatedTarget).data('berat');
                console.log(ringan, sedang, berat)

                $(e.currentTarget).find('#jml-ringan').text(ringan + ' x');
                $(e.currentTarget).find('#jml-sedang').text(sedang + ' x');
                $(e.currentTarget).find('#jml-berat').text(berat + ' x');
            });

            $('#cari-status-siswa').on("input", function () {
                const searchTerm = $(this).val().toLowerCase();
                const result = simpleDataSearch(responseJadwal, {
                    arrayKeys: ['jadwals', 'sesisiswa'],
                    keys: ['siswa.nama', 'siswa.agama', 'peserta.nomor_peserta',
                        'ruang.kode_ruang', 'kelas.kode_kelas', 'sesi.kode_sesi',
                        'bank.bank_kode', 'bank.mapel.kode'],
                });
                let resName = result.search(searchTerm);
                if (resName.jadwals.length > 0 && resName.sesisiswa.length > 0) {
                    const newData = { ...responseJadwal, jadwals: resName.jadwals, sesisiswa: resName.sesisiswa };
                    createPreview(newData);
                    return;
                }

                if (resName.sesisiswa.length > 0) {
                    const newData = { ...responseJadwal, sesisiswa: resName.sesisiswa, jadwals: responseJadwal.jadwals };
                    createPreview(newData);
                    return;
                }

                if (resName.jadwals.length > 0) {
                    const newData = { ...responseJadwal, jadwals: resName.jadwals, sesisiswa: responseJadwal.sesisiswa };
                    createPreview(newData);
                    return;
                }

                const newData = { ...responseJadwal, jadwals: [], sesisiswa: [] };
                createPreview(newData);
            });

            getAllJadwal();
        });

        $(document).on('click', '.toggle-block', function (e) {
            e.preventDefault();
            const me = $(this);
            const action = me.data('action');
            const idSiswa = me.data('siswa');
            const nama = me.data('nama');
            const idCbt = me.data('cbt');
            const idJadwal = me.data('jadwal');

            let itemJadwal = {
                reset: [],
                force: [],
                ulang: [],
                blokir: [],
                unblokir: []
            };

            let payload = {
                id_jadwal: idJadwal,
                id_cbt_siswa: [idCbt]
            };

            let confirmText = '';
            if (action === 'blokir') {
                itemJadwal.blokir.push(payload);
                confirmText = 'Siswa ' + nama + ' akan diblokir dan tidak bisa melanjutkan ujian.';
            } else {
                itemJadwal.unblokir.push(payload);
                confirmText = 'Akses ujian siswa ' + nama + ' akan dibuka kembali.';
            }

            let formData = new FormData($('#post_data')[0]);
            formData.append("data", JSON.stringify(itemJadwal));

            Swal.fire({
                title: action === 'blokir' ? "Blokir Siswa?" : "Buka Blokir?",
                text: confirmText,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: action === 'blokir' ? "#d33" : "#3085d6",
                cancelButtonColor: "#757575",
                confirmButtonText: "Ya, Lanjutkan"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "cbtstatus/applyaction",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            refreshStatus();
                        }, error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            showDangerToast('Gagal mengubah status blokir');
                        }
                    });
                }
            });
        });

        function terapkanAksi() {
            const $rows = $('#table-status').find('tr:visible'), headers = $rows.splice(0, 2);
            let itemJadwal = {
                reset: [],
                reset: [],
                force: [],
                ulang: [],
                blokir: [],
                unblokir: []
            }
            let jmlSiswaReset = [];
            let jmlSiswaForce = [];
            let jmlSiswaUlang = [];
            let jmlSiswaBlokir = [];
            let jmlSiswaUnblokir = [];
            $rows.each((i, row) => {
                const $colReset = $(row).find('.input-reset');
                const $colForce = $(row).find('.input-force');
                const $colUlang = $(row).find('.input-ulang');
                const $colBlokir = $(row).find('.input-blokir');
                const $colUnblokir = $(row).find('.input-unblokir');

                if ($colReset.prop("checked") === true) {
                    const jadwal_id = $colReset.parent().data('jadwal')
                    const cbt_id = $colReset.parent().data('cbt')
                    const siswa_id = $colForce.parent().data('siswa')
                    if (!jmlSiswaReset.includes(siswa_id)) jmlSiswaReset.push(siswa_id)

                    const item = itemJadwal.force.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                    if (item) {
                        item.id_cbt_siswa.push(cbt_id)
                    } else {
                        let reset = {
                            id_jadwal: jadwal_id,
                            id_cbt_siswa: [cbt_id], // for cbt_siswa
                        }
                        itemJadwal.reset.push(reset)
                    }
                }
                if ($colForce.prop("checked") === true) {
                    const cbt_id = $colForce.parent().data('cbt')
                    const jadwal_id = $colForce.parent().data('jadwal')
                    const siswa_id = $colForce.parent().data('siswa')
                    if (!jmlSiswaForce.includes(siswa_id)) jmlSiswaForce.push(siswa_id)

                    const item = itemJadwal.force.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                    if (item) {
                        item.id_cbt_siswa.push(cbt_id)
                        item.id_siswa.push(siswa_id)
                    } else {
                        let force = {
                            id_jadwal: jadwal_id,
                            id_cbt_siswa: [cbt_id], // for cbt_siswa
                            id_siswa: [siswa_id]    // for log_ujian
                        }
                        itemJadwal.force.push(force)
                    }
                }
                if ($colUlang.prop("checked") === true) {
                    const jadwal_id = $colUlang.parent().data('jadwal')
                    const cbt_id = $colUlang.parent().data('cbt')
                    const siswa_id = $colUlang.parent().data('siswa')
                    if (!jmlSiswaUlang.includes(siswa_id)) jmlSiswaUlang.push(siswa_id)

                    const item = itemJadwal.force.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                    if (item) {
                        item.id_cbt_siswa.push(cbt_id)
                        item.id_siswa.push(siswa_id)
                    } else {
                        let ulang = {
                            id_jadwal: jadwal_id,
                            id_cbt_siswa: [cbt_id], // for cbt_siswa
                            id_siswa: [siswa_id]    // for log_ujian
                        }
                        itemJadwal.ulang.push(ulang)
                    }
                }
                // Add Blokir logic
                if ($colBlokir.prop("checked") === true) {
                    const jadwal_id = $colBlokir.parent().data('jadwal')
                    const cbt_id = $colBlokir.parent().data('cbt')
                    const siswa_id = $colBlokir.parent().data('siswa')
                    if (!jmlSiswaBlokir.includes(siswa_id)) jmlSiswaBlokir.push(siswa_id)

                    // reuse logic or create new
                    let blokir = {
                        id_jadwal: jadwal_id,
                        id_cbt_siswa: [cbt_id],
                    }
                    const item = itemJadwal.blokir.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                    if (item) {
                        item.id_cbt_siswa.push(cbt_id)
                    } else {
                        itemJadwal.blokir.push(blokir)
                    }
                }
                // Add Unblokir logic
                if ($colUnblokir.prop("checked") === true) {
                    const jadwal_id = $colUnblokir.parent().data('jadwal')
                    const cbt_id = $colUnblokir.parent().data('cbt')
                    const siswa_id = $colUnblokir.parent().data('siswa')
                    if (!jmlSiswaUnblokir.includes(siswa_id)) jmlSiswaUnblokir.push(siswa_id)

                    let unblokir = {
                        id_jadwal: jadwal_id,
                        id_cbt_siswa: [cbt_id],
                    }
                    const item = itemJadwal.unblokir.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
                    if (item) {
                        item.id_cbt_siswa.push(cbt_id)
                    } else {
                        itemJadwal.unblokir.push(unblokir)
                    }
                }
            })
            //console.log('jadwal', itemJadwal)
            var jmlReset = jmlSiswaReset.length === 0 ? '' : '<b>' + jmlSiswaReset.length + '</b> siswa akan direset<br>';
            var jmlForce = jmlSiswaForce.length === 0 ? '' : '<b>' + jmlSiswaForce.length + '</b> siswa akan dipaksa selesai<br>';
            var jmlUlang = jmlSiswaUlang.length === 0 ? '' : '<b>' + jmlSiswaUlang.length + '</b> siswa akan mengulang ujian<br>';
            var jmlBlokir = jmlSiswaBlokir.length === 0 ? '' : '<b>' + jmlSiswaBlokir.length + '</b> siswa akan diblokir<br>';
            var jmlUnblokir = jmlSiswaUnblokir.length === 0 ? '' : '<b>' + jmlSiswaUnblokir.length + '</b> siswa akan dibuka blokirnya';

            var resReset = jmlSiswaReset.length === 0 ? '' : '<b>' + jmlSiswaReset.length + '</b> siswa berhasil direset<br>';
            var resForce = jmlSiswaForce.length === 0 ? '' : '<b>' + jmlSiswaForce.length + '</b> siswa berhasil dipaksa selesai<br>';
            var resUlang = jmlSiswaUlang.length === 0 ? '' : '<b>' + jmlSiswaUlang.length + '</b> siswa harus mengulang ujian<br>';
            var resBlokir = jmlSiswaBlokir.length === 0 ? '' : '<b>' + jmlSiswaBlokir.length + '</b> siswa diblokir<br>';
            var resUnblokir = jmlSiswaUnblokir.length === 0 ? '' : '<b>' + jmlSiswaUnblokir.length + '</b> siswa dibuka blokirnya';

            if (itemJadwal.reset.length === 0 && itemJadwal.force.length === 0 &&
                itemJadwal.ulang.length === 0 && itemJadwal.blokir.length === 0 && itemJadwal.unblokir.length === 0) {
                showWarningToast('Silahkan pilih siswa terlebih dulu');
                return;
            }

            let formData = new FormData($('#post_data')[0]);
            formData.append("data", JSON.stringify(itemJadwal));
            Swal.fire({
                title: "Terapkan Aksi",
                html: jmlReset + jmlForce + jmlUlang + jmlBlokir + jmlUnblokir,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Apply"
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "cbtstatus/applyaction",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            Swal.fire({
                                title: "Sukses",
                                html: resForce + resReset + resUlang + resBlokir + resUnblokir,
                                icon: "success"
                            }).then(result => {
                                //console.log(data);
                                if (printBy === '1') {
                                    url = base_url + "cbtstatus/getstatussiswa?ruang=" + ruang + '&sesi=' + sesi + '&tgl=' + tglJadwal;
                                } else {
                                    url = base_url + "cbtstatus/getstatussiswa?kelas=" + kelas + '&tgl=' + tglJadwal;
                                }
                                refreshStatus();
                            });
                        }, error: function (xhr, status, error) {
                            console.log(xhr.responseText);
                            Swal.fire({
                                "title": "Error",
                                "html": xhr.responseText,
                                "icon": "error"
                            });
                        }
                    });
                }
            });
        }

        function refreshStatus() {
            $('#cari-status-siswa').val('')
            $('#loading').removeClass('d-none');
            setTimeout(function () {
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (response) {
                        responseJadwal = response;
                        //createPreview(response)
                        optionJadwal();
                    }
                });
            }, 500);
        }

        function optionJadwal() {
            var opsiJadwal = $("#jadwal-by");
            opsiJadwal.html('')
            opsiJadwal.append(`<option value="all" ${jadwalSelected === 'all' ? 'selected' : ''}>Semua Jadwal</option>`);
            for (const jadwal of responseJadwal.jadwals) {
                if (jadwal) {
                    opsiJadwal.append(`<option value="${jadwal.id_jadwal}" ${jadwalSelected === jadwal.id_jadwal ? 'selected' : ''}>${jadwal.bank?.bank_kode}</option>`);
                }
            }
            opsiJadwal.trigger('change')
        }

        function createPreview(data, isInit) {
            console.log('jadwal', data)
            const disabled = data.is_empty ? 'disabled' : '';

            const chkReset = '<br><input id="input-reset-all" class="check" type="checkbox" ' + disabled + '>';
            const chkForce = '<br><input id="input-force-all" class="check" type="checkbox" ' + disabled + '>';
            const chkUlang = '<br><input id="input-ulang-all" class="check" type="checkbox" ' + disabled + '>';
            //const chkBlokir = '<br><input id="input-blokir-all" class="check" type="checkbox" ' + disabled + '>';
            //const chkUnblokir = '<br><input id="input-unblokir-all" class="check" type="checkbox" ' + disabled + '>';

            let tbody = '<thead class="bg-light">' +
                '<tr>' +
                '<th rowspan="2" class="text-center align-middle">No.</th>' +
                '<th rowspan="2" class="text-center align-middle" style="min-width: 220px">Data Siswa</th>' +
                '<th colspan="2" class="text-center align-middle">Jadwal Ujian</th>' +
                '<th colspan="3" class="text-center align-middle">Status Siswa</th>' +
                '<th colspan="4" class="text-center align-middle ' + dnone + '">Aksi</th>' +
                '</tr>' +
                '<tr>' +
                '<th class="text-center align-middle">Mapel</th>' +
                '<th class="text-center align-middle">Kode & <br/>Status Jadwal</th>' +
                '<th class="text-center align-middle p-1">Mulai</th>' +
                '<th class="text-center align-middle">Durasi</th>' +
                '<th class="text-center align-middle">Fouls</th>' +
                '<th class="text-center align-middle ' + dnone + '">Reset<br/>Waktu/Blokir</th>' +
                '<th class="text-center align-middle ' + dnone + '">Reset<br/>Izin' +
                chkReset +
                '</th>' +
                '<th class="text-center align-middle ' + dnone + '">Paksa<br/>Selesai' +
                chkForce +
                '</th>' +
                '<th class="text-center align-middle ' + dnone + '">Ulang' +
                chkUlang +
                '</th>' +
                '</tr></thead><tbody>';

            const all_jadwals = (Array.isArray(data.jadwals) ? data.jadwals : []);
            if (Array.isArray(data.sesisiswa) && data.sesisiswa.length > 0) {
                data.sesisiswa.sort((a, b) => a.peserta?.nomor_peserta?.localeCompare(b.peserta?.nomor_peserta, undefined, { numeric: true }))
                let ind = 1;
                for (const sesiSiswa of data.sesisiswa) {
                    var genap = ind % 2 === 0;
                    const bgRow = genap ? '#e9ecef' : '#fff'

                    const idSiswa = sesiSiswa?.siswa_id ?? 'N/A';
                    var sesi = sesiSiswa?.sesi?.kode_sesi ?? 'N/A';
                    var ruang = sesiSiswa?.ruang?.kode_ruang ?? 'N/A';
                    var kelas = sesiSiswa?.kelas?.kode_kelas ?? 'N/A';
                    const nama = sesiSiswa?.siswa?.nama ?? '';
                    const agama = sesiSiswa?.siswa?.agama ?? '';

                    const bankSoals = all_jadwals.filter(jad =>
                        !(jad?.bank?.soal_agama != '-' && jad?.bank?.soal_agama != '0' && jad?.bank?.soal_agama != agama) &&
                        jad?.bank?.bank_kelas?.some(k => k?.kelas_id?.toString() == sesiSiswa?.kelas_id?.toString())
                    );

                    let rowsMapel = bankSoals.length || "1";
                    if (bankSoals.length > 0) {
                        let indJadwal = 0;
                        //console.log('bank', bankSoals)
                        bankSoals.sort((a, b) => a.jam_ke - b.jam_ke)
                        for (const bank of bankSoals) {
                            const akhirUjian = ((bank.tgl_selesai).split(' ')[0]).replaceAll('-', '')
                            const now = ((data.today).split(' ')[0]).replaceAll('-', '')
                            const tglBerkahir = now > akhirUjian
                            const tersedia = tglBerkahir ? '<i class="bi bi-x-circle text-danger me-1 ms-1"></i>' : '<i class="bi bi-check-circle text-success me-1 ms-1"></i>'
                            let rowJadwal = '<td class="align-middle text-center">' + bank.bank.mapel.kode + '</td>' +
                                '<td class="align-middle text-center"><div class="d-flex flex-row justify-content-between">' +
                                '<span>' + bank.bank.bank_kode + '</span>' +
                                '<span>' + tersedia + '</span></div></td>';

                            if (Array.isArray(sesiSiswa?.cbtsiswa) && sesiSiswa.cbtsiswa.length > 0) {
                                const cbtSiswa = sesiSiswa.cbtsiswa.find(cbt => cbt?.id_jadwal === bank?.id_jadwal);
                                if (cbtSiswa) {
                                    const hitungDurasi = kalkulasiWaktu(cbtSiswa)
                                    const durasi = hitungDurasi.durasi || '0m';
                                    var sudahSelesai = cbtSiswa.selesai || false;
                                    var loading = sudahSelesai ? '<i class="bi bi-check me-2"></i>' : '<i class="bi bi-spinner fa-spin me-2"></i>';

                                    // jika ingin selalu aktif tambahkan tanda //
                                    var disabledResetWaktu = !tglBerkahir && !sudahSelesai && cbtSiswa.reset_waktu === '0' ? '' : 'disabled';
                                    var disabledResetIzin = !sudahSelesai && cbtSiswa.reset_izin != null && cbtSiswa.reset_izin === '0' ? '' : 'disabled';
                                    var disabledSelesai = !sudahSelesai ? '' : 'disabled';
                                    var disabledUlang = sudahSelesai ? '' : 'disabled';
                                    var isBlocked = cbtSiswa.status === '9';
                                    var belumMulai = !cbtSiswa.mulai;
                                    var disabledBlokir = sudahSelesai || belumMulai ? 'disabled' : '';

                                    var btnBlokir = isBlocked
                                        ? '<button type="button" class="btn btn-success btn-xs toggle-block" data-action="unblokir" ' +
                                        'data-siswa="' + idSiswa + '" data-nama="' + nama + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '" ' + disabledBlokir + ' data-toggle="tooltip" title="Buka Blokir"><i class="bi bi-unlock"></i></button>'
                                        : '<button type="button" class="btn btn-danger btn-xs toggle-block" data-action="blokir" ' +
                                        'data-siswa="' + idSiswa + '" data-nama="' + nama + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '" ' + disabledBlokir + ' data-toggle="tooltip" title="Blokir"><i class="bi bi-slash-circle"></i></button>';

                                    // jika ingin selalu aktif hilangkan tanda //
                                    // var disabledResetWaktu = '';     // reset waktu selalu aktif
                                    // var disabledReset = '';          // reset izin selalu aktif
                                    // var disabledSelesai = '';        // paksa selesai selalu aktif
                                    // var disabledUlang = '';         // ulangi selalu aktif

                                    const mulai = () => {
                                        if (!bank?.tgl_mulai || !cbtSiswa?.mulai) return '- : -';
                                        const start = bank.tgl_mulai.replaceAll('-', '');
                                        const time = cbtSiswa?.mulai?.split(' ')[0].replaceAll('-', '');
                                        let susulan = time > start ? '<br /><span class="text-xs">susulan</span>' : '';
                                        const siswaMulai = new Date(cbtSiswa?.mulai)
                                        return `${zeroPad(siswaMulai.getHours(), 2)}:${zeroPad(siswaMulai.getMinutes(), 2)}${susulan}`;
                                    };

                                    const totalPelanggaran = function () {
                                        if (bank?.pelanggaran != '1') {
                                            return 0;
                                        } else {
                                            return (cbtSiswa.pelanggaran?.ringan || 0) + (cbtSiswa.pelanggaran?.sedang || 0) + (cbtSiswa.pelanggaran?.berat || 0);
                                        }
                                    }
                                    const btn = `<button class="btn ${bank?.pelanggaran != '1' ? 'btn-outline-secondary' : 'btn-danger'} btn-sm text-bold"
                                                data-bs-toggle="modal" data-bs-target="#pelanggaranModal"
                                                data-ringan="${cbtSiswa.pelanggaran?.ringan || 0}"
                                                data-sedang="${cbtSiswa.pelanggaran?.sedang || 0}"
                                                data-berat="${cbtSiswa.pelanggaran?.berat || 0}"
                                                ${bank?.pelanggaran != '1' ? 'disabled' : ''}>${totalPelanggaran()} x</button>`
                                    rowJadwal += '<td class="text-center align-middle">' + mulai() + '</td>' +
                                        '<td class="text-center align-middle">' + loading + durasi + '</td>' +
                                        '<td class="text-center align-middle">' + btn + '</td>' +
                                        '<td class="text-center align-middle ' + dnone + '">' +
                                        '<div class="d-flex justify-content-center gap-1">' +
                                        '	<button type="button" class="btn btn-warning btn-xs" ' +
                                        'data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" ' +
                                        'data-bs-toggle="modal" data-bs-target="#resetModal" ' + disabledResetWaktu + ' data-toggle="tooltip" title="Reset Waktu"><i class="bi bi-arrow-clockwise"></i></button>' +
                                        btnBlokir +
                                        '</div>' +
                                        '</td>' +
                                        '<td class="text-center text-success align-middle ' + dnone + '" data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '">' +
                                        '<input class="check input-reset" type="checkbox" ' + disabledResetIzin + '>' +
                                        '</td>' +
                                        '<td class="text-center text-danger align-middle ' + dnone + '" data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '">' +
                                        '<input class="check input-force" type="checkbox" ' + disabledSelesai + '>' +
                                        '</td>' +
                                        '<td class="text-center text-danger align-middle ' + dnone + '" data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" data-jadwal="' + cbtSiswa.id_jadwal + '">' +
                                        '<input class="check input-ulang" type="checkbox" ' + disabledUlang + '>' +
                                        '</td>' +
                                        '</tr>';
                                } else {
                                    rowJadwal += '<td colspan="7" class="align-middle text-center text-warning">Belum mengerjakan</td></tr>'
                                }
                            } else {
                                rowJadwal += '<td colspan="7" class="align-middle text-center text-warning">Belum mengerjakan</td></tr>'
                            }
                            if (indJadwal === 0) {
                                tbody += '<tr style="background: ' + bgRow + '" data-siswa="' + idSiswa + '">' +
                                    '<td rowspan="' + rowsMapel + '" class="text-center align-middle">' + ind + '</td>' +
                                    '<td rowspan="' + rowsMapel + '" class="align-middle">' +
                                    '<div class="d-flex flex-column">' +
                                    '<span class="font-weight-bold">' + nama + '</span>' +
                                    '<span>' + sesiSiswa?.peserta?.nomor_peserta + '</span>' +
                                    '<div class="m-0">';
                                if (agama) {
                                    tbody += '<span class="badge bg-success me-1">' + agama + '</span>';
                                }
                                tbody += '<span class="badge bg-danger me-1">' + kelas + '</span>' +
                                    '<span class="badge bg-warning me-1">' + ruang + '</span>' +
                                    '<span class="badge bg-primary">' + sesi + '</span>' +
                                    '</div></div>' +
                                    '</td>';
                                tbody += rowJadwal + '</tr>';
                                ind++
                            } else {
                                tbody += '<tr style="background: ' + bgRow + '">' + rowJadwal;
                            }
                            indJadwal++;
                        }
                    }
                }
            } else {
                const isEmpty = isInit ? 'Tidak ada siswa tergabung disini!' : 'Pencarian tidak ditemukan';
                tbody += '<tr><td colspan="12" class="text-center">' + isEmpty + '</td></tr>'
            }

            tbody += '</tbody>';
            $('#table-status').html(tbody);
            $('#info').removeClass('d-none');

            $('#loading').addClass('d-none');

            $("#input-reset-all").on("click", function () {
                if (this.checked) {
                    $(".input-reset").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = true;
                        }
                        $("#input-reset-all").prop("checked", true);
                    });
                } else {
                    $(".input-reset").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = false;
                        }
                        $("#input-reset-all").prop("checked", false);
                    });
                }
            });

            $("#input-force-all").on("click", function () {
                if (this.checked) {
                    $(".input-force").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = true;
                        }
                        $("#input-force-all").prop("checked", true);
                    });
                } else {
                    $(".input-force").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = false;
                        }
                        $("#input-force-all").prop("checked", false);
                    });
                }
            });

            $("#input-ulang-all").on("click", function () {
                if (this.checked) {
                    $(".input-ulang").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = true;
                        }
                    });
                } else {
                    $(".input-ulang").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = false;
                        }
                    });
                }
            });

            $("#input-blokir-all").on("click", function () {
                if (this.checked) {
                    $(".input-blokir").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = true;
                        }
                    });
                } else {
                    $(".input-blokir").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = false;
                        }
                    });
                }
            });

            $("#input-unblokir-all").on("click", function () {
                if (this.checked) {
                    $(".input-unblokir").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = true;
                        }
                    });
                } else {
                    $(".input-unblokir").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = false;
                        }
                    });
                }
            });

        }

        function getDetailJadwal(idJadwal) {
            $.ajax({
                type: "GET",
                url: base_url + "cbtstatus/getjadwalujianbyjadwal?id_jadwal=" + idJadwal,
                cache: false,
                success: function (response) {
                    var selKelas = $('#kelas');
                    selKelas.html('');
                    selKelas.append('<option value="">Pilih Kelas</option>');
                    $.each(response, function (k, v) {
                        if (v != null) {
                            selKelas.append('<option value="' + k + '">' + v + '</option>');
                        }
                    });
                }
            });
        }

        function getAllJadwal() {
            $.ajax({
                type: "GET",
                url: base_url + "cbtstatus/gettgljadwalujian",
                cache: false,
                success: function (response) {
                    setOptionJadwal(response)
                }
            });
        }

        function setOptionJadwal(data) {
            console.log('data', data)
            var opsiTgl = $("#tgl");
            var opsiRuang = $("#ruang");
            var opsiSesi = $("#sesi");
            var opsiKelas = $("#kelas");
            opsiTgl.html('');
            opsiTgl.append('<option value="">Pilih Tanggal</option>');
            if (Object.keys(data.jadwal).length === 0) {
                opsiTgl.append('<option value="">Belum ada jadwal</option>');
                return
            }
            for (const id in data.jadwal) {
                opsiTgl.append('<option value="' + id + '">' + data.jadwal[id] + '</option>');
            }

            opsiTgl.change(function () {
                opsiKelas.html('');
                opsiKelas.append('<option value="">Pilih Kelas</option>');
                let existKls = []
                for (const arrkelas of data.kelas[$(this).val()]) {
                    for (const idKls in arrkelas) {
                        if (!existKls.includes(idKls)) {
                            existKls.push(idKls)
                            opsiKelas.append('<option value="' + idKls + '">' + arrkelas[idKls] + '</option>');
                        }
                        //console.log('kls', arrkelas[idKls])
                    }
                }

                opsiRuang.html('');
                opsiRuang.append('<option value="">Pilih Ruang</option>');
                const allRuang = data.ruang[$(this).val()]
                let existRuang = []
                for (const idr in allRuang) {
                    //console.log('ruang', idr)
                    if (isPengawas === '1' && pengawasRuang !== '' && idr != pengawasRuang) {
                        continue;
                    }

                    if (!existRuang.includes(idr)) {
                        existRuang.push(idr)
                        opsiRuang.append('<option value="' + idr + '">' + allRuang[idr] + '</option>');
                    }
                }

                if (isPengawas === '1' && pengawasRuang !== '') {
                    opsiRuang.val(pengawasRuang).trigger('change');
                }

                opsiSesi.html('');
                opsiSesi.append('<option value="">Pilih Sesi</option>');
                const allSesi = data.sesi[$(this).val()]
                let existSesi = []
                for (const ids in allSesi) {
                    //console.log('sesi', ids)
                    if (!existSesi.includes(ids)) {
                        existSesi.push(ids)
                        opsiSesi.append('<option value="' + ids + '">' + allSesi[ids] + '</option>');
                    }
                }
            });
            opsiTgl.removeAttr('disabled')

            opsiKelas.change(function () {
                kelas = $(this).val();
                tglJadwal = opsiTgl.val();
                loadSiswaKelas(kelas, tglJadwal)
            });

            opsiRuang.change(function () {
                sesi = opsiSesi.val();
                ruang = $(this).val();
                tglJadwal = opsiTgl.val();
                loadSiswaRuang($(this).val(), sesi, tglJadwal)
            })

            opsiSesi.change(function () {
                sesi = $(this).val();
                ruang = opsiRuang.val();
                tglJadwal = opsiTgl.val();
                loadSiswaRuang(ruang, $(this).val(), tglJadwal)
            })
        }

        function loadSiswaRuang(ruang, sesi, tglJadwal) {
            var empty = ruang === '' || sesi === '' || tglJadwal === '';
            if (!empty) {
                url = base_url + "cbtstatus/getstatussiswa?ruang=" + ruang + '&sesi=' + sesi + '&tgl=' + tglJadwal;
                refreshStatus();
            } else {
                console.log('empty')
            }
        }

        function loadSiswaKelas(kelas, tglJadwal) {
            var empty = kelas === '' || tglJadwal === '';
            if (!empty) {
                url = base_url + "cbtstatus/getstatussiswa?kelas=" + kelas + '&tgl=' + tglJadwal;
                refreshStatus();
            } else {
                console.log('empty')
            }
        }

        function kalkulasiWaktu(durasi) {
            if (!durasi || !durasi.mulai) return durasi; // Handle jika durasi tidak valid

            const mulai = new Date(durasi.mulai).getTime();
            let lamanya = durasi.lama_ujian;

            // Jika lama_ujian tidak tersedia atau bukan dalam format yang benar
            if (!lamanya || !lamanya.includes(':')) {
                if (!durasi.selesai) return durasi; // Jika tidak ada waktu selesai, langsung return

                const selesai = new Date(durasi.selesai).getTime();
                if (isNaN(mulai) || isNaN(selesai) || selesai < mulai) return durasi; // Handle error tanggal

                const lama = (selesai - mulai) / 1000; // Konversi ke detik
                const jam = Math.floor(lama / 3600).toString().padStart(2, '0');
                const menit = Math.floor((lama % 3600) / 60).toString().padStart(2, '0');
                const detik = Math.floor(lama % 60).toString().padStart(2, '0');

                lamanya = `${jam}:${menit}:${detik}`;
            }

            // Konversi lama ujian ke format durasi yang lebih mudah dibaca
            const [h, m, s] = lamanya.split(':').map(Number);
            const ej = h ? `${h} jam ` : '';
            const em = m ? `${m} mnt ` : '';
            const ed = s ? `${s} dtk` : '';

            return {
                ...durasi,
                lama_ujian: lamanya,
                durasi: ej + em + ed || '0 dtk' // Pastikan tidak kosong
            };
        }

    </script>