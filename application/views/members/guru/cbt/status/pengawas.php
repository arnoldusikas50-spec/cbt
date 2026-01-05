<?php


$is_pengawas = in_array($guru->id_guru, $ids_pengawas);

?>

<main class="app-main bg-white">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-sm-flex justify-content-between mb-2">
                <h1><?= $judul ?></h1>
                <button onclick="window.history.back();" type="button" class="btn btn-sm btn-danger float-end">
                    <i class="bi bi-arrow-left-square"></i><span class="d-none d-sm-inline-block ms-1">Kembali</span>
                </button>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <button class="card-tools btn btn-danger btn-sm me-2 btn-toggle ms-auto" data-bs-toggle="modal"
                        data-bs-target="#infoModal"><i class="bi bi-info-circle me-1"></i> Info Error
                    </button>
                </div>
                <?php
                $dnone = $is_pengawas ? '' : 'd-none';
                ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="alert-default-success input-group-text text-center">TOKEN</span>
                                </div>
                                <input id="token-input" class="form-control text-bold text-center"
                                    readonly="readonly" />
                                <div class="input-group-append" id="kolom-kanan">
                                    <span id="interval" class="input-group-text text-xs d-none">-- : -- : --</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row" id="info">
                        <div class="col-12">
                            <div class="alert alert-default-info border-info">
                                <h6><i class="icon bi bi-check"></i> Informasi</h6>
                                <div id="info-penggunaan">
                                    <ul>
                                        <li>
                                            Gunakan tombol <span class="badge bg-success pt-1 pb-1"><i
                                                    class="bi bi-arrow-repeat ms-1 me-1"></i> Refresh</span>
                                            untuk merefresh halaman
                                        </li>
                                        <li>
                                            <b>RESET WAKTU.</b> Jika siswa logout sebelum selesai dan tidak melanjutkan
                                            sampai waktu ujian habis maka akan ditolak, jika ingin melanjutkan maka
                                            harus reset waktu.
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
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-default-success border-success">
                                <h6><i class="icon bi bi-info-circle"></i> Info Ujian</h6>
                                <div id="info-ujian">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-success align-bottom mb-2" onclick="refreshStatus()"
                                data-bs-toggle="tooltip" title="Refresh">
                                <i class="bi bi-arrow-repeat ms-1 me-1"></i> Refresh
                            </button>
                            <div class="float-end align-bottom mb-2">
                                <label><input type="search" id="cari-status-siswa" class="form-control form-control-sm"
                                        placeholder="Cari">
                                </label>
                                <button type="button" class="ms-2 btn btn-success <?= $dnone ?>"
                                    onclick="terapkanAksi()" data-bs-toggle="tooltip"
                                    title="Terapkan Aksi pada siswa terpilih">
                                    <i class="bi bi-check ms-1 me-1"></i> Terapkan Aksi
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped" id="table-status"></table>
                    </div>
                </div>
                <div class="overlay" id="loading">
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
        <div class="modal-dialog modal-xs" role="document">
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

    <script type="text/javascript" src="<?= base_url() ?>/assets/plugins/multiselect/js/jquery.quicksearch.js"></script>
    <script src="<?= base_url() ?>/assets/app/js/ajax_request.js" type="text/javascript"></script>

    <script>
        const isPengawas = '<?= $is_pengawas ? "1" : "0" ?>';
        const jadwalId = '<?= $jadwal_id ?>';
        const ruangId = '<?= $ruang_id ?>';
        const sesiId = '<?= $sesi_id ?>';
        const dnone = isPengawas == "1" ? '' : 'd-none';

        $(document).ready(function () {
            var idSiswa = '';
            var idCbtSiswa = '';

            $('#resetModal').on('show.bs.modal', function (e) {
                idSiswa = $(e.relatedTarget).data('siswa');
                idCbtSiswa = $(e.relatedTarget).data('cbt');
                $('#id_cbt_siswa').val(idCbtSiswa)
            });

            $('#reset').on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                $('#resetModal').modal('hide').data('bs.modal', null);
                $('#resetModal').on('hidden', function () {
                    $(this).data('modal', null);
                });

                let formData = new FormData($('#reset')[0]);

                $.ajax({
                    url: base_url + "cbtstatus/resettimer",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (data) {
                        console.log(data.status);
                        if (data.status) refreshStatus();
                        else showDangerToast('tidak bisa mereset waktu');
                    }, error: function (xhr, status, error) {
                        console.log('error');
                    }
                });
            });

            getToken(function (result) {
                getGlobalToken();
            });

            $('#refresh-token').click(function () {
                getToken(function (result) {
                    getGlobalToken();
                });
            });

            function getGlobalToken() {
                if (globalToken != null) {
                    const viewToken = $('#token-input');
                    if (viewToken.length) viewToken.val(globalToken.token);
                    if (globalToken.auto == '1' && adaJadwalUjian > 0) {
                        $('#refresh-token').removeClass('d-none')
                    }
                }
            }

            $('#pelanggaranModal').on('show.bs.modal', function (e) {
                const ringan = $(e.relatedTarget).data('ringan');
                const sedang = $(e.relatedTarget).data('sedang');
                const berat = $(e.relatedTarget).data('berat');
                console.log(ringan, sedang, berat)

                $(e.currentTarget).find('#jml-ringan').text(ringan + ' x');
                $(e.currentTarget).find('#jml-sedang').text(sedang + ' x');
                $(e.currentTarget).find('#jml-berat').text(berat + ' x');
            });

            loadSiswaRuang()
        });

        function terapkanAksi() {
            const $rows = $('#table-status').find('tr:visible'), headers = $rows.splice(0, 2);
            let itemJadwal = {
                reset: [],
                force: [],
                ulang: [],
            }
            let jmlSiswaReset = [];
            let jmlSiswaForce = [];
            let jmlSiswaUlang = [];

            $rows.each((i, row) => {
                const $colReset = $(row).find('.input-reset');
                const $colForce = $(row).find('.input-force');
                const $colUlang = $(row).find('.input-ulang');
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

                    const item = itemJadwal.ulang.find(j => j.id_jadwal?.toString() === jadwal_id?.toString())
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
            });

            const jmlReset = jmlSiswaReset.length === 0 ? '' : '<b>' + jmlSiswaReset.length + '</b> siswa akan direset<br>';
            const jmlForce = jmlSiswaForce.length === 0 ? '' : '<b>' + jmlSiswaForce.length + '</b> siswa akan dipaksa selesai<br>';
            const jmlUlang = jmlSiswaUlang.length === 0 ? '' : '<b>' + jmlSiswaUlang.length + '</b> siswa akan mengulang ujian';

            const resReset = jmlSiswaReset.length === 0 ? '' : '<b>' + jmlSiswaReset.length + '</b> siswa berhasil direset<br>';
            const resForce = jmlSiswaForce.length === 0 ? '' : '<b>' + jmlSiswaForce.length + '</b> siswa berhasil dipaksa selesai<br>';
            const resUlang = jmlSiswaUlang.length === 0 ? '' : '<b>' + jmlSiswaUlang.length + '</b> siswa harus mengulang ujian';

            if (itemJadwal.reset.length === 0 && itemJadwal.force.length === 0 && itemJadwal.ulang.length === 0) {
                showWarningToast('Silahkan pilih siswa terlebih dulu');
                return;
            }

            let formData = new FormData($('#reset')[0]);
            formData.append("data", JSON.stringify(itemJadwal));

            Swal.fire({
                title: "Terapkan Aksi",
                html: jmlReset + jmlForce + jmlUlang,
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
                            console.log('success', data);
                            Swal.fire({
                                title: "Sukses",
                                html: resForce + resReset + resUlang,
                                icon: "success"
                            }).then(result => {
                                //window.location.reload()
                                loadSiswaRuang()
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
            window.location.reload()
        }

        function loadSiswaRuang() {
            ajaxGet(`${base_url}cbtstatus/get_status_ruang?ruang=${ruangId}&sesi=${sesiId}&jadwal=${jadwalId}`,
                function (error, response) {
                    $('#loading').addClass('d-none')
                    if (error) {
                        console.log('error', error);
                    } else {
                        populateTable(response)
                    }
                });
        }

        function populateTable(data) {
            console.log('data', data)
            const jadwal = data.jadwal

            let htmlInfo = `<table class="table table-sm">
        <thead>
            <tr>
                <th class="text-center align-middle">Jam Ke</th>
                <th>Mapel</th>
                <th>Soal Agama</th>
                <th>Pengampu</th>
                <th>Jml. Soal</th>
                <th>Pengawas</th>
            </tr>
        </thead><tbody>`;

            const bankSoal = jadwal.bank
            const pengawas = jadwal.pengawas?.find(p => p.id_ruang === ruangId && p.id_sesi === sesiId)
            const totalSoal = (Number(bankSoal.tampil_pg) + Number(bankSoal.tampil_kompleks)) +
                (Number(bankSoal.tampil_jodohkan) + Number(bankSoal.tampil_isian)) +
                Number(bankSoal.tampil_esai);
            htmlInfo += `<tr>
                <td class="text-center align-middle">${pengawas?.jam_ke || '1'}</td>
                <td>${bankSoal.mapel.kode}</td>
                <td>${bankSoal.soal_agama}</td>
                <td>${bankSoal.guru.nama_guru}</td>
                <td>${totalSoal} soal</td>
                <td>${data.pengawas.length > 0 ? data.pengawas.map(p => p.nama_guru).join(', ') : "Tidak diatur"}</td>`;
            htmlInfo += `</tbody></table>`

            const infoUjian = document.getElementById("info-ujian");
            infoUjian.innerHTML = htmlInfo;

            const disabled = data.is_empty ? 'disabled' : '';
            let tbody = '<thead class="alert-light">' +
                '<tr>' +
                '<th rowspan="2" class="text-center align-middle alert-default-secondary">No.</th>' +
                '<th rowspan="2" class="text-center align-middle alert-default-primary" style="min-width: 220px">Data Siswa</th>' +
                '<th colspan="2" class="text-center align-middle alert-default-info">Jadwal Ujian</th>' +
                '<th colspan="3" class="text-center align-middle alert-default-warning">Status Siswa</th>' +
                '<th colspan="5" class="text-center align-middle alert-default-secondary ' + dnone + '">Aksi</th>' +
                '</tr>' +
                '<tr>' +
                '<th class="text-center align-middle alert-default-warning">Mapel</th>' +
                '<th class="text-center align-middle alert-default-danger">Kode & <br/>Status Jadwal</th>' +
                '<th class="text-center align-middle p-1 alert-default-info">Mulai</th>' +
                '<th class="text-center align-middle alert-default-success">Durasi</th>' +
                '<th class="text-center align-middle alert-default-danger">Fouls</th>' +
                '<th class="text-center align-middle alert-default-danger' + dnone + '">Blokir</th>' +
                '<th class="text-center align-middle alert-default-primary' + dnone + '">Reset<br/>Waktu</th>' +
                '<th class="text-center align-middle alert-default-warning' + dnone + '">Reset<br/>Izin' +
                '<br><input id="input-reset-all" class="check" type="checkbox" ' + disabled + '>' +
                '</th>' +
                '<th class="text-center align-middle alert-default-danger ' + dnone + '">Paksa<br/>Selesai' +
                '<br><input id="input-force-all" class="check" type="checkbox" ' + disabled + '>' +
                '</th>' +
                '<th class="text-center align-middle alert-default-success ' + dnone + '">Ulang' +
                '<br><input id="input-ulang-all" class="check" type="checkbox" ' + disabled + '>' +
                '</th>' +
                '</tr></thead><tbody>';

            let ind = 1;
            if (data.sesisiswa.length > 0) {
                const filterSiswa = data.sesisiswa.filter(sis =>
                    !(bankSoal.soal_agama !== '-' && bankSoal.soal_agama !== '0' && bankSoal.soal_agama !== sis.siswa.agama)
                    && bankSoal.bank_kelas.some(k => k.kelas_id?.toString() === sis.kelas_id?.toString()))

                filterSiswa.sort((a, b) => a.siswa.nama.localeCompare(b.siswa.nama))

                for (const sesiSiswa of filterSiswa) {
                    const idSiswa = sesiSiswa?.siswa_id;
                    var sesi = sesiSiswa?.sesi?.kode_sesi;
                    var ruang = sesiSiswa?.ruang?.kode_ruang;
                    var kelas = sesiSiswa?.kelas?.kode_kelas || '';
                    const nama = sesiSiswa?.siswa?.nama || '';
                    const agama = sesiSiswa?.siswa?.agama;

                    const akhirUjian = ((jadwal.tgl_selesai).split(' ')[0]).replaceAll('-', '')
                    const now = ((data.today).split(' ')[0]).replaceAll('-', '')
                    const tglBerkahir = now > akhirUjian
                    const tersedia = tglBerkahir ? '<i class="bi bi-x-circle text-danger me-1 ms-1"></i>' : '<i class="bi bi-check-circle text-success me-1 ms-1"></i>'
                    let rowJadwal = '<td class="align-middle text-center">' + bankSoal.mapel.kode + '</td>' +
                        '<td class="align-middle text-center"><div class="d-flex flex-row justify-content-between">' +
                        '<span>' + bankSoal.bank_kode + '</span>' +
                        '<span>' + tersedia + '</span></div></td>';

                    const cbtSiswa = sesiSiswa?.cbtsiswa?.[jadwal.id_jadwal] ?? null;
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

                        // jika ingin selalu aktif hilangkan tanda //
                        // var disabledResetWaktu = '';     // reset waktu selalu aktif
                        // var disabledReset = '';          // reset izin selalu aktif
                        // var disabledSelesai = '';        // paksa selesai selalu aktif
                        // var disabledUlang = '';         // ulangi selalu aktif

                        const mulai = () => {
                            const start = new Date(bankSoal.tgl_mulai)
                            const time = new Date(cbtSiswa?.mulai)
                            if (time) {
                                let susulan = '';
                                if (time > start) susulan = '<br /><span class="text-xs">susulan</span>'
                                return `${zeroPad(time.getHours(), 2)}:${zeroPad(time.getMinutes(), 2)}${susulan}`
                            } else {
                                return '- -  :  - -';
                            }
                        }

                        const totalPelanggaran = (cbtSiswa.pelanggaran?.ringan || 0) + (cbtSiswa.pelanggaran?.sedang || 0) + (cbtSiswa.pelanggaran?.berat || 0);
                        const btn = `<button class="btn btn-danger btn-sm text-bold" data-bs-toggle="modal" data-bs-target="#pelanggaranModal" data-ringan="${cbtSiswa.pelanggaran?.ringan || 0}" data-sedang="${cbtSiswa.pelanggaran?.sedang || 0}" data-berat="${cbtSiswa.pelanggaran?.berat || 0}">${totalPelanggaran} x</button>`

                        const isBlocked = cbtSiswa.status == '9';
                        const btnBlockColor = isBlocked ? 'btn-success' : 'btn-danger';
                        const btnBlockIcon = isBlocked ? 'fa-unlock' : 'fa-ban';
                        const btnBlockTitle = isBlocked ? 'Buka Blokir' : 'Blokir';

                        rowJadwal += '<td class="text-center align-middle">' + mulai() + '</td>' +
                            '<td class="text-center align-middle">' + loading + durasi + '</td>' +
                            '<td class="text-center align-middle">' + btn + '</td>' +
                            '<td class="text-center align-middle ' + dnone + '">' +
                            '<button type="button" class="btn ' + btnBlockColor + ' btn-xs px-2" ' +
                            'onclick="toggleBlock(\'' + cbtSiswa.id_cbt_siswa + '\', ' + (isBlocked ? 'true' : 'false') + ')" ' +
                            'title="' + btnBlockTitle + '">' +
                            '<i class="fa ' + btnBlockIcon + ' text-xs text-white"></i></button>' +
                            '</td>' +
                            '<td class="text-center align-middle ' + dnone + '">' +
                            '	<button type="button" class="btn btn-default btn-xs px-2" ' +
                            'data-siswa="' + idSiswa + '" data-cbt="' + cbtSiswa.id_cbt_siswa + '" ' +
                            'data-bs-toggle="modal" data-bs-target="#resetModal" ' + disabledResetWaktu + '> <i class="bi bi-refresh text-xs text-primary"></i> </button>' +
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
                        rowJadwal += '<td colspan="8" class="align-middle text-center text-warning">Belum mengerjakan</td></tr>'
                    }
                    tbody += '<tr data-siswa="' + idSiswa + '">' +
                        '<td class="text-center align-middle">' + ind + '</td>' +
                        '<td class="text-center align-middle">' +
                        '<div class="d-flex flex-column">' +
                        '<span class="font-weight-bold">' + nama + '</span>' +
                        '<span>' + sesiSiswa?.peserta?.nomor_peserta + '</span>' +
                        '<div class="m-0">';
                    if (agama) {
                        tbody += '<span class="badge bg-primary me-1">' + agama + '</span>';
                    }
                    tbody += '<span class="badge bg-primary me-1">' + kelas + '</span>' +
                        '<span class="badge bg-primary me-1">' + ruang + '</span>' +
                        '<span class="badge bg-primary">' + sesi + '</span></div>' +
                        '</td>';
                    tbody += rowJadwal + '</tr>';

                    ind++;
                }
            } else {
                tbody += '<tr><td colspan="12" class="text-center">Tidak ada siswa tergabung disini!</td></tr>'
            }
            tbody += '</tbody>';
            $('#table-status').html(tbody);

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
                        $("#input-ulang-all").prop("checked", true);
                    });
                } else {
                    $(".input-ulang").each(function () {
                        if (!$(this).prop('disabled')) {
                            this.checked = false;
                        }
                        $("#input-ulang-all").prop("checked", false);
                    });
                }
            });

            $('#cari-status-siswa').quicksearch('#table-status tbody tr');
        }

        function toggleBlock(idCbtSiswa, isBlocked) {
            let action = isBlocked ? 'unblokir' : 'blokir';
            let actionText = isBlocked ? 'Buka Blokir' : 'Blokir';
            let data = {};
            data[action] = [{
                id_cbt_siswa: [idCbtSiswa]
            }];

            let formData = new FormData($('#reset')[0]);
            formData.append("data", JSON.stringify(data));

            Swal.fire({
                title: actionText + " siswa?",
                text: "Apakah anda yakin ingin melakukan tindakan ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, " + actionText
            }).then(result => {
                if (result.value) {
                    $.ajax({
                        url: base_url + "cbtstatus/applyaction",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            loadSiswaRuang();
                            Swal.fire({
                                title: "Sukses",
                                text: "Berhasil " + actionText,
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function (xhr, status, error) {
                            Swal.fire({
                                title: "Error",
                                html: xhr.responseText,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

        function kalkulasiWaktu(durasi) {
            // reset waktu bisa dilakukan sebelum tanggal ujian berakhir
            // jika tgl ujian sudah berakhir, silakan tambahkan tgl akhir ujian
            let mulai = new Date(durasi.mulai).getTime();
            let lamanya = durasi.lama_ujian;
            if (!lamanya || !lamanya.includes(':')) {
                const selesai = new Date(durasi.selesai).getTime();
                const lama = selesai - mulai;
                lamanya = new Date(lama).toISOString().substring(11, 19); // Format H:i:s
            }
            durasi.lama_ujian = lamanya

            const elap = lamanya ? lamanya.split(':') : [];
            const ej = elap[0] === "00" ? '' : Number(elap[0]) + ' jam ';
            const em = elap[1] === "00" ? '' : Number(elap[1]) + ' mnt ';
            const ed = elap[2] === "00" ? '' : Number(elap[2]) + ' dtk';
            if (ej === '' && em === '') {
                durasi.durasi = ed;
            } else {
                durasi.durasi = ej + em;
            }
            return durasi;
        }

        function zeroPad(no) {
            return no < 10 ? '0' + no : no;
        }
    </script>