<?php


?>

<div class="content-wrapper" style="margin-top: -20px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-3"> <!-- ikasmedia -->
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- <?php $this->load->view('members/siswa/templates/top'); ?> --> <!-- ikasmedia -->
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow mt-5">
                        <div class="card-header alert-default-primary">
                            <div class="media card-title"><?=$judul?></div>
                        </div>
                        <div class="card-body">
                            <?= form_open('', array('id' => 'post')) ?>
                            <input type="hidden" name="id_siswa" value="<?=$siswa->id_siswa?>">
                            <input type="hidden" name="id_kelas" value="<?=$siswa->id_kelas?>">
                            <div class="row">
                                <div class="col-6 col-md-4 col-lg-3 mb-2">
                                    <label>Tahun:</label>
                                    <select name="tp" id="opsi-tahun" class="form-control">
                                        <option value="" selected="selected" disabled="disabled">Pilih Tahun
                                        </option>
                                        <?php foreach ($tp as $tahun) : ?>
                                            <option value="<?= $tahun->id_tp ?>"><?= $tahun->tahun ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4 col-lg-3 mb-2">
                                    <label>Semester:</label>
                                    <select name="smt" id="opsi-semester" class="form-control" disabled>
                                        <option value="" selected="selected" disabled="disabled">Pilih Semester</option>
                                        <?php foreach ($smt as $sm) : ?>
                                            <option value="<?= $sm->id_smt ?>"><?= $sm->smt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 col-lg-3 mb-2" id="input-bulan">
                                    <label>Bulan:</label>
                                    <select name="bulan" id="opsi-bulan" class="form-control" disabled>
                                        <option value="" selected="selected" disabled="disabled">Pilih Bulan</option>
                                    </select>
                                </div>
                            </div>
                            <?= form_close(); ?>
                            <div id="tp-warning" class="d-none text-xs text-danger">Anda harus memilih Tahun Pelajaran dan Semester yang sudah terlewati</div>
                            <div class="alert alert-default-info mt-4">Arsip hanya menampilkan Materi/Tugas sebelum hari ini</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <span class="card-title">Arsip</span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 d-none" id="filter-by">
                                <div class="col-6 pl-3">
                                    <label>Filter Mapel:</label>
                                    <?php echo form_dropdown(
                                        '',
                                        $mapels,
                                        null,
                                        'id="filter-mapel" class="form-control form-control-sm"'
                                    ); ?>
                                </div>
                                <div class="col-6 pr-3">
                                    <label>Filter Hari:</label>
                                    <?php
                                    $arrHari = [
                                        "1"=> "Senin",
                                        "2" => "Selasa",
                                        "3" => "Rabu",
                                        "4" => "Kamis",
                                        "5" => "Jum'at",
                                        "6" => "Sabtu",
                                        "7"=>"Minggu"
                                    ];
                                    echo form_dropdown(
                                        '',
                                        $arrHari,
                                        null,
                                        'id="filter-hari" class="form-control form-control-sm"'
                                    ); ?>
                                </div>
                                <!--
                                <div class="col text-right pr-3">
                                    <div>
                                        Cari
                                        <input type="text" class="form-control form-control-sm w-50 d-inline-block" id="cariMapel" placeholder="Cari Mata Pelajaran">
                                    </div>
                                </div>
                                -->
                            </div>
                            <div class="col-12 table-responsive">
                                <div id="info-empty" class="alert alert-default-info">Detail Materi dan Tugas dalam sebulan akan ditampilkan disini</div>
                                <table id="tableMapel" class="w-100 table table-bordered table-sm d-none">
                                    <thead>
                                    <tr>
                                        <th class="text-center align-middle border-left-bold border-rigt-bold border-top-bold alert-default-danger" colspan="6" style="height: 50px">Detail Arsip</th>
                                    </tr>
                                    </thead>
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
            </div>
        </div>
    </section>
</div>

<script type="text/javascript" src="<?= base_url() ?>/assets/plugins/jquery-datetimepicker/jquery.datetimepicker.full.js"></script>
<script>
    let blnSelected, smtSelected, tpSelected;
    const arrBulan = {
        '1': [
            ['07', "Juli"],
            ['08', "Agustus"],
            ['09', "September"],
            ['10', "Oktober"],
            ['11', "Nopember"],
            ['12', "Desember"]
        ],
        '2': [
            ['01', "Januari"],
            ['02', "Februari"],
            ['03', "Maret"],
            ['04', "April"],
            ['05', "Mei"],
            ['06', "Juni"]
        ]
    };
    const tempArsip = 'garudaCBT.arsip_materi'
    let arrArsip = [];

    $(document).ready(function () {
        const opsiThn = $('#opsi-tahun');
        const opsiSmt = $('#opsi-semester');
        const opsiBln = $('#opsi-bulan');
        const temp = localStorage.getItem(tempArsip);

        if (temp) {
            const data = JSON.parse(temp);
            tpSelected = data.thn;
            smtSelected = data.smt;
            blnSelected = data.bln;

            // Atur tahun
            opsiThn.val(tpSelected).trigger('change');

            // Atur semester
            opsiSmt.prop('disabled', false);
            opsiSmt.val(smtSelected).trigger('change');

            // Kosongkan dan isi ulang opsi bulan sesuai semester
            const now = new Date();
            const tahunSekarang = String(now.getFullYear());
            const bulanSekarang = String(now.getMonth() + 1).padStart(2, '0');

            const tahun = $('#opsi-tahun option:selected').text().split('/'); // 2025/2026
            const smt = smtSelected;
            const tahunTerpilih = smt === '1' ? tahun[0] : tahun[1];

            opsiBln.empty();
            opsiBln.append(`<option value="" selected="selected" disabled="disabled">Pilih Bulan</option>`);
            arrBulan[smt].forEach(([kode, nama]) => {
                let disabledAttr = '';

                if (parseInt(tahunTerpilih) > parseInt(tahunSekarang)) {
                    // Tahun di masa depan → semua bulan disabled
                    disabledAttr = 'disabled';
                } else if (tahunTerpilih === tahunSekarang && kode > bulanSekarang) {
                    // Tahun sekarang → bulan di masa depan disabled
                    disabledAttr = 'disabled';
                }

                opsiBln.append(`<option value="${kode}" ${disabledAttr}>${nama}</option>`);
            });

            // Atur bulan
            opsiBln.prop('disabled', false);
            opsiBln.val(blnSelected).trigger('change');

            // Panggil getArsip jika semua nilai tersedia
            if (tpSelected && smtSelected && blnSelected) {
                getArsip();
            }
        }

        opsiThn.change(function () {
            opsiSmt.prop('disabled', $(this).val() === '');
            if (tpSelected === $(this).val()) return;
            tpSelected = $(this).val();
            if (!tpSelected) return;

            opsiSmt.trigger('change');
        });

        opsiSmt.change(function () {
            //if (smtSelected === $(this).val() && tpSelected) return;
            smtSelected = $(this).val();
            if (!smtSelected) return;

            opsiBln.prop('disabled', $(this).val() === '');
            const now = new Date();
            const tahunSekarang = String(now.getFullYear());
            const bulanSekarang = String(now.getMonth() + 1).padStart(2, '0');
            const smtSekarang = now.getMonth() < 6 ? '2' : '1';

            const smt = smtSelected;
            const today = () => {
                let ynow = now.getFullYear();
                if (smt === '2') {
                    ynow += 1; // Semester 2 berarti tahun berikutnya
                }
                return ynow;
            }

            const tp = $('#opsi-tahun option:selected').text().split('/');
            const tahunTerpilih = smt === '1' ? tp[0] : tp[1];

            if (parseInt(tahunTerpilih) > today() || (parseInt(tahunTerpilih) === today() && smtSekarang !== smt)) {
                $('#tp-warning').removeClass('d-none');
            } else {
                $('#tp-warning').addClass('d-none');
            }
            // Isi ulang opsi bulan sesuai semester
            opsiBln.empty();
            opsiBln.append(`<option value="" selected="selected" disabled="disabled">Pilih Bulan</option>`);
            arrBulan[smt].forEach(([kode, nama]) => {
                let disabledAttr = '';
                if (parseInt(tahunTerpilih) > parseInt(tahunSekarang)) {
                    disabledAttr = 'disabled';
                } else if (tahunTerpilih === tahunSekarang && kode > bulanSekarang) {
                    disabledAttr = 'disabled';
                }
                opsiBln.append(`<option value="${kode}" ${disabledAttr}>${nama}</option>`);
            });
        });

        opsiBln.change(function () {
            if (blnSelected === $(this).val()) return;
            blnSelected = $(this).val();
            if (!blnSelected) return;

            getArsip();
        });

        const filterMpl = $('#filter-mapel');
        const filterHari = $('#filter-hari');

        filterMpl.prepend('<option selected></option>');
        filterMpl.select2({
            width: '100%',
            theme: 'bootstrap4',
            allowClear: true,
            placeholder: 'Pilih Mapel'});
        filterMpl.change(function () {
            if (!$(this).val() && !filterHari.val()) {
                generateTable(arrArsip, false);
                return;
            }
            const result = filterJadwal($(this).val(), $('#filter-hari').val(), arrArsip)
            generateTable(result, true);
        });

        filterHari.prepend('<option selected></option>');
        filterHari.select2({width: '100%', theme: 'bootstrap4', allowClear: true, placeholder: 'Pilih Hari'});
        filterHari.change(function () {
            if (!$(this).val() && !filterMpl.val()) {
                generateTable(arrArsip, false);
                return;
            }
            const result = filterJadwal($('#filter-mapel').val(), $(this).val(), arrArsip)
            generateTable(result, true);
        });
    });

    function getWeekCountInMonth(year, month, startOnMonday = false) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0); // hari terakhir dalam bulan

        const firstDayOfWeek = startOnMonday ? (firstDay.getDay() || 7) : firstDay.getDay(); // 0 = Minggu, 1 = Senin, ...
        const totalDays = lastDay.getDate();

        // Hitung jumlah minggu: jumlah hari + offset hari di awal bulan, dibagi 7 lalu dibulatkan ke atas
        const daysInFirstWeek = 7 - (firstDayOfWeek === 0 ? 0 : firstDayOfWeek);
        const remainingDays = totalDays - daysInFirstWeek;
        const fullWeeks = Math.ceil(remainingDays / 7);

        return 1 + fullWeeks;
    }

    function getArsip() {
        localStorage.setItem(tempArsip, JSON.stringify({
            thn: tpSelected,
            smt: smtSelected,
            bln: blnSelected,
        }));

        const tp = $('#opsi-tahun option:selected').text().split('/');
        const smt = $('#opsi-semester').val();
        const year = smt === '1' ? tp[0] : tp[1];
        const bulan = $('#opsi-bulan').val();

        let formData = new FormData($('#post')[0]);
        //formData.append('tgl', `${year}-${bulan}-${tglSelected}`);
        //console.log(Object.fromEntries(formData.entries()));

        $.ajax({
            url: base_url + 'kbm/load_absensi_bulanan',
            type: "POST",
            processData: false,
            contentType: false,
            data: formData,
            success: function (data) {
                if (!data) {
                    $('#loading').addClass('d-none');
                    $('#tbl-konten').html('<div class="alert alert-default-warning">Tidak ada jadwal pelajaran pada tanggal yang dipilih.</div>');
                    return
                }
                mergeJson(data);
            }, error: function (xhr, status, error) {
                $('#loading').addClass('d-none');
                swal.fire({
                    title: "ERROR",
                    text: xhr.responseText,
                    icon: "error",
                    showCancelButton: false,
                });
            }
        });
    }

    function mergeJson(data) {
        let jadwals = data.jadwal_mapel || []
        const materis = data.jadwal_materi || {}
        const logs = data.logs

        for (const jadwalHarian of jadwals) {
            jadwalHarian.jadwal.sort(function (a, b) {
                return a.dari.localeCompare(b.dari);
            });
            let is_empty = [];
            for (const jadwal of jadwalHarian.jadwal) {
                const materiTugas = materis[jadwalHarian.tanggal] || {}
                let materi = [], tugas = []
                if (Object.keys(materiTugas).length > 0) {
                    if (materiTugas[1]) {
                        materi = materiTugas[1].filter(function (m) {
                            return m.id_mapel === jadwal.id_mapel
                        })
                    }
                    if (materiTugas[2]) {
                        tugas = materiTugas[2].filter(function (m) {
                            return m.id_mapel === jadwal.id_mapel
                        })
                    }
                }

                for (const materiElement of materi) {
                    const hadir = logs[1]?.[jadwalHarian.tanggal] ?? []
                    if (hadir && hadir.length > 0) {
                        materiElement['kehadiran'] = hadir.find(function (log) {
                            return log.id_kjm === materiElement.id_kjm
                        })
                    }
                }
                jadwal['materi'] = materi

                for (const tugasElement of tugas) {
                    const hadir = logs[2]?.[jadwalHarian.tanggal] ?? []
                    if (hadir && hadir.length > 0) {
                        tugasElement['kehadiran'] = hadir.find(function (log) {
                            return log.id_kjm === tugasElement.id_kjm
                        })
                    }
                }
                jadwal['tugas'] = tugas
                is_empty.push(materi.length === 0 && tugas.length === 0);
            }
            jadwalHarian['empty'] = is_empty;
        }
        arrArsip = jadwals;
        generateTable(jadwals, false)
    }

    function generateTable(data, isFilter, showEmptyDateRow = true) {
        console.log('data', data);
        let oldTbody = document.querySelector('tbody#tbodyMapel');
        if (oldTbody) oldTbody.remove();

        const tahun = $('#opsi-tahun option:selected').text();
        const smt = $('#opsi-semester option:selected').text();
        const bulan = $('#opsi-bulan option:selected').text();
        const now = new Date();
        now.setDate(now.getDate() - 1);

        const table = document.getElementById('tableMapel');
        const tbody = document.createElement('tbody');
        tbody.setAttribute('id', 'tbodyMapel');

        let countMateriTugas = 0;

        if (data && data.length > 0) {
            data.forEach(day => {
                const tglJadwal = new Date(day.tanggal);
                if (tglJadwal < now && !day.is_libur) {

                    // 🔍 Cek apakah hari ini ada materi/tugas
                    const hasMateriTugas = day.jadwal.some(j =>
                        (j.id_mapel !== '-1' && j.id_mapel !== '-2') &&
                        ((j.materi && j.materi.length > 0) || (j.tugas && j.tugas.length > 0))
                    );

                    // Buat header tanggal
                    const tglLengkap = `${day.hari}, ${new Date(day.tanggal).toLocaleDateString('id-ID', {
                        day: '2-digit', month: 'long', year: 'numeric'
                    })}`;

                    const trTanggal = document.createElement('tr');
                    trTanggal.classList.add('group', 'bg-gray-light');
                    trTanggal.innerHTML = `
                    <th rowspan="2" class="text-sm pl-2 align-middle border-left-bold border-rigt-bold border-top-bold" colspan="2">${tglLengkap}</th>
                    <th colspan="2" class="text-sm text-center align-middle border-rigt-bold border-top-bold alert alert-default-success">Materi</th>
                    <th colspan="2" class="text-sm text-center align-middle border-rigt-bold border-top-bold alert alert-default-primary">Tugas</th>`;

                    const trSubTanggal = document.createElement('tr');
                    trSubTanggal.classList.add('group', 'bg-gray-light');
                    trSubTanggal.innerHTML = `
                    <th class="text-sm text-center align-middle">Status</th>
                    <th class="text-sm text-center align-middle border-rigt-bold">Aksi</th>
                    <th class="text-sm text-center align-middle">Status</th>
                    <th class="text-sm text-center align-middle border-rigt-bold">Aksi</th>`;

                    tbody.appendChild(trTanggal);
                    tbody.appendChild(trSubTanggal);
                    countMateriTugas++;

                    // Kalau kosong tapi tetap mau tampilkan baris info
                    if (!hasMateriTugas && showEmptyDateRow) {
                        const trKosong = document.createElement('tr');
                        trKosong.innerHTML = `
                        <td colspan="6" class="text-center text-muted border-left-bold border-rigt-bold border-bottom-bold alert alert-default-warning">
                            Tidak ada materi/tugas
                        </td>`;
                        tbody.appendChild(trKosong);
                        return; // lanjut ke hari berikutnya
                    }

                    // Render isi tabel (kalau ada data)
                    let nomor = 1;
                    day.jadwal.forEach((item, index) => {
                        if (item.id_mapel === '-1' || item.id_mapel === '-2') return;

                        const leng = Math.max(item.materi.length, item.tugas.length);
                        for (let i = 0; i < leng; i++) {
                            const materi = item.materi[i];
                            const tugas = item.tugas[i];
                            const hadirMateri = materi?.kehadiran;
                            const adaNilaiMateri = hadirMateri?.nilai;
                            const selesaiMateri = hadirMateri?.finish_time;
                            let ketMateri = '', btnAksiMateri = '';
                            if (hadirMateri && hadirMateri.finish_time) {
                                if (hadirMateri.ulang === '1') {
                                    ketMateri = `<span class="text-danger text-bold text-xs text-nowrap">Harus diulang</span>`;
                                    btnAksiMateri = `<button data-type="1" data-status="3" data-id="${materi.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                } else if (selesaiMateri && (!adaNilaiMateri || adaNilaiMateri === '0')) {
                                    ketMateri = `<span class="text-info text-bold text-xs text-nowrap">Blm dikoreksi</span>`;
                                    btnAksiMateri = `<button data-type="1" data-status="2" data-id="${materi.id_kjm}" class="btn btn-info btn-sm detail-nilai">Lihat</button>`;
                                } else if (adaNilaiMateri && adaNilaiMateri !== '0') {
                                    ketMateri = `<span class="text-success text-bold text-xs">Selesai</span>`;
                                    btnAksiMateri = `<button data-type="1" data-status="2" data-msg="${hadirMateri?.catatan}" data-nilai="${hadirMateri?.nilai}" data-id="${materi.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                } else {
                                    ketMateri = `<span class="text-warning text-bold text-xs text-nowrap">Blm selesai</span>`;
                                    btnAksiMateri = `<button data-type="1" data-status="1" data-id="${materi.id_kjm}" class="btn btn-warning btn-sm detail-nilai">Lanjutkan</button>`;
                                }
                            } else if (materi) {
                                if (adaNilaiMateri) {
                                    ketMateri = `<span class="text-success text-bold text-xs">Selesai</span>`;
                                    btnAksiMateri = `<button data-type="1" data-status="2" data-msg="${hadirMateri?.catatan}" data-nilai="${hadirMateri?.nilai}" data-id="${materi.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                } else {
                                    ketMateri = `<span class="text-xs text-danger text-bold text-nowrap">Blm dikerjakan</span>`;
                                    btnAksiMateri = `<button data-type="1" data-status="0" data-id="${materi.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                }
                            }

                            const hadirTugas = tugas?.kehadiran;
                            const adaNilaiTugas = hadirTugas?.nilai;
                            const selesaiTugas = hadirTugas?.finish_time;
                            let ketTugas = '', btnAksiTugas = '';
                            if (hadirTugas && hadirTugas.finish_time) {
                                if (hadirTugas.ulang === '1') {
                                    ketTugas = `<span class="text-danger text-bold text-xs text-nowrap">Harus diulang</span>`;
                                    btnAksiTugas = `<button data-type="2" data-status="3" data-id="${tugas.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                } else if (selesaiTugas && (!adaNilaiTugas || adaNilaiTugas === '0')) {
                                    ketTugas = `<span class="text-info text-bold text-xs text-nowrap">Blm dikoreksi</span>`;
                                    btnAksiTugas = `<button data-type="2" data-status="2" data-id="${tugas.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                } else if (adaNilaiTugas && adaNilaiTugas !== '0') {
                                    ketTugas = `<span class="text-success text-bold text-xs text-nowrap">Selesai</span>`;
                                    btnAksiTugas = `<button data-type="2" data-status="2" data-msg="${hadirTugas?.catatan}" data-nilai="${hadirTugas?.nilai}" data-id="${tugas.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                } else {
                                    ketTugas = `<span class="text-warning text-bold text-xs text-nowrap">Blm selesai</span>`;
                                    btnAksiTugas = `<button data-type="2" data-status="1" data-id="${tugas.id_kjm}" class="btn btn-warning btn-sm detail-nilai">Lanjutkan</button>`;
                                }
                            } else if (tugas) {
                                if (adaNilaiTugas) {
                                    ketTugas = `<span class="text-success text-bold text-xs text-nowrap">Selesai</span>`;
                                    btnAksiTugas = `<button data-type="2" data-status="2" data-msg="${hadirTugas?.catatan}" data-nilai="${hadirTugas?.nilai}" data-id="${tugas.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                } else {
                                    ketTugas = `<span class="text-danger text-bold text-xs text-nowrap">Blm dikerjakan</span>`;
                                    btnAksiTugas = `<button data-type="2" data-status="0" data-id="${tugas.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                }
                            }

                            const tr = document.createElement('tr');
                            const borderBottom = index === day.jadwal.length - 1 ? 'border-bottom-bold' : '';

                            let html = '';
                            if (i === 0) {
                                html += `
                                <td rowspan="${leng}" class="text-center border-left-bold ${borderBottom}">${nomor}</td>
                                <td rowspan="${leng}" class="border-rigt-bold ${borderBottom}">
                                    <span class="mapel">${item.kode || '-'}</span>
                                    <br>
                                    <small class="waktu">${item.dari + ' ~ ' + item.sampai}</small>
                                </td>`;
                            }

                            html += `
                            <td class="kode-materi align-middle ${borderBottom}">
                                <div class="d-flex flex-column"><span>${materi?.kode_materi || ''}</span>${ketMateri}</div>
                            </td>
                            <td class="text-center align-middle border-rigt-bold ${borderBottom}">${btnAksiMateri}</td>
                            <td class="kode-tugas align-middle ${borderBottom}">
                                <div class="d-flex flex-column"><span>${tugas?.kode_materi || ''}</span>${ketTugas}</div>
                            </td>
                            <td class="text-center align-middle border-rigt-bold ${borderBottom}">${btnAksiTugas}</td>`;

                            tr.innerHTML = html;
                            tbody.appendChild(tr);
                        }
                        nomor++;
                    });

                } else {
                    $('#info-empty').text(`Arsip hanya untuk Materi/Tugas yang dijadwalkan sebelum hari ini`);
                }
            });

            if (countMateriTugas > 0) {
                table.appendChild(tbody);
                table.classList.remove('d-none');
                $('#info-empty').addClass('d-none');
                $('#filter-by').removeClass('d-none');
            } else {
                table.classList.add('d-none');
                $('#info-empty').removeClass('d-none');
                if (isFilter) {
                    const mapel = $('#filter-mapel option:selected').text();
                    const hari = $('#filter-hari option:selected').text();
                    const sMapel = mapel ? `untuk Mapel ${mapel}` : '';
                    const sHari = hari ? `pada Hari ${hari}` : '';
                    $('#info-empty').text(`Tidak ada Materi dan Tugas ${sMapel} ${sHari}`);
                } else {
                    $('#filter-by').addClass('d-none');
                    $('#info-empty').text(`Tidak ada Materi dan Tugas di Tahun Pelajaran ${tahun}, Semester ${smt}, bulan ${bulan}`);
                }
            }
        } else {
            $('#info-empty').text(`Tidak ada Materi dan Tugas di Tahun Pelajaran ${tahun}, Semester ${smt}, bulan ${bulan}`);
        }

        $('.detail-nilai').on('click', function (e) {
            showDetail(e);
        });
    }

    /*
    function generateTable(data, isFilter) {
        console.log('data', data);
        let oldH3Element =  document.querySelector('tbody#tbodyMapel')
        if(oldH3Element) oldH3Element.parentNode.removeChild(oldH3Element)
        const tahun = $('#opsi-tahun option:selected').text();
        const smt = $('#opsi-semester option:selected').text();
        const bulan = $('#opsi-bulan option:selected').text();
        const now = new Date();
        now.setDate(now.getDate() - 1);

        const table = document.getElementById('tableMapel');
        const tbody = document.createElement('tbody');
        tbody.setAttribute('id', 'tbodyMapel')
        if (data && data.length > 0) {
            let countMateriTugas = 0;
            data.forEach(day => {
                const tglJadwal = new Date(day.tanggal);
                if (tglJadwal < now) {
                    if (!day.is_libur) {
                        const trTanggal = document.createElement('tr');
                        trTanggal.classList.add('group', 'bg-gray-light');
                        const tglLengkap = `${day.hari}, ${new Date(day.tanggal).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                        })}`;
                        trTanggal.innerHTML = `
                    <th rowspan="2" class="text-sm pl-2 align-middle border-left-bold border-rigt-bold border-top-bold" colspan="2">${tglLengkap}</th>
                    <th colspan="2" class="text-sm text-center align-middle border-rigt-bold border-top-bold" data-value="status-materi">Materi</th>
                    <th colspan="2" class="text-sm text-center align-middle border-rigt-bold border-top-bold" data-value="status-tugas">Tugas</th>`;

                        const trSubTanggal = document.createElement('tr');
                        trSubTanggal.classList.add('group', 'bg-gray-light');
                        trSubTanggal.innerHTML = `
                    <th class="text-sm text-center align-middle">Status</th>
                    <th class="text-sm text-center align-middle border-rigt-bold">Aksi</th>
                    <th class="text-sm text-center align-middle">Status</th>
                    <th class="text-sm text-center align-middle border-rigt-bold">Aksi</th>`;

                        let nomor = 1;
                        day.jadwal.forEach((item, index) => {
                            const adaMateri = item.materi.length > 0;
                            const adaTugas = item.tugas.length > 0;
                            const leng = Math.max(item.materi.length, item.tugas.length);

                            if (item.id_mapel !== '-1' && item.id_mapel !== '-2') {
                                if (index === 0) {
                                    tbody.appendChild(trTanggal)
                                    tbody.appendChild(trSubTanggal);
                                    countMateriTugas++;
                                }
                                for (let i = 0; i < leng; i++) {
                                    const materi = item.materi[i]
                                    const hadirMateri = materi?.kehadiran

                                    const tugas = item.tugas[i]
                                    const hadirTugas = tugas?.kehadiran

                                    //const statusMateri = createKetSiswa(hadirMateri, item.sampai);
                                    let btnAksiMateri = ``;
                                    let ketMateri = ``;
                                    if (hadirMateri) {
                                        if (hadirMateri.finish_time) {
                                            if (hadirMateri.nilai != null) {
                                                if (hadirMateri.nilai !== '0') {
                                                    ketMateri = `<span class="text-success text-bold text-xs">Selesai</span>`
                                                    btnAksiMateri = `<button data-type="1" data-status="2" data-msg="${hadirMateri?.catatan}" data-nilai="${hadirMateri?.nilai}" data-id="${materi.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                                } else {
                                                    ketMateri = `<span class="text-danger text-bold text-xs text-nowrap">Harus diulang</span>`;
                                                    btnAksiMateri = `<button data-type="1" data-status="3" data-id="${materi.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                                }
                                            } else {
                                                ketMateri = `<span class="text-info text-bold text-xs text-nowrap">Blm dikoreksi</span>`;
                                                btnAksiMateri = `<button data-type="1" data-status="2" data-id="${materi.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                            }
                                        } else {
                                            ketMateri = `<span class="text-warning text-bold text-xs text-nowrap">Blm selesai</span>`;
                                            btnAksiMateri = `<button data-type="1" data-status="1" data-id="${materi.id_kjm}" class="btn btn-warning btn-sm detail-nilai">Lanjutkan</button>`;
                                        }
                                    } else {
                                        if (materi) {
                                            ketMateri = `<span class="text-xs text-danger text-bold text-nowrap">Blm dikerjakan</span>`
                                            btnAksiMateri = `<button data-type="1" data-status="0" data-id="${materi.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                        }
                                    }

                                    //const statusTugas = createKetSiswa(hadirTugas, item.sampai);
                                    let btnAksiTugas = ``;
                                    let ketTugas = '';
                                    if (hadirTugas) {
                                        if (hadirTugas.finish_time) {
                                            if (hadirTugas.nilai != null) {
                                                if (hadirTugas.nilai !== '0') {
                                                    ketTugas = `<span class="text-success text-bold text-xs text-nowrap">Selesai</span>`
                                                    btnAksiTugas = `<button data-type="2" data-status="2" data-msg="${hadirTugas?.catatan}" data-nilai="${hadirTugas?.nilai}" data-id="${tugas.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                                } else {
                                                    ketTugas = `<span class="text-danger text-bold text-xs text-nowrap">Harus diulang</span>`;
                                                    btnAksiTugas = `<button data-type="2" data-status="3" data-id="${tugas.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                                }
                                            } else {
                                                ketTugas = `<span class="text-info text-bold text-xs text-nowrap">Blm dikoreksi</span>`;
                                                btnAksiTugas = `<button data-type="2" data-status="2" data-id="${tugas.id_kjm}" class="btn btn-success btn-sm detail-nilai">Lihat</button>`;
                                            }
                                        } else {
                                            ketTugas = `<span class="text-warning text-bold text-xs text-nowrap">Blm selesai</span>`;
                                            btnAksiTugas = `<button data-type="2" data-status="1" data-id="${tugas.id_kjm}" class="btn btn-warning btn-sm detail-nilai">Lanjutkan</button>`;
                                        }
                                    } else {
                                        if (tugas) {
                                            ketTugas = `<span class="text-danger text-bold text-xs text-nowrap">Blm dikerjakan</span>`
                                            btnAksiTugas = `<button data-type="2" data-status="0" data-id="${tugas.id_kjm}" class="btn btn-danger btn-sm detail-nilai">Kerjakan</button>`;
                                        }
                                    }

                                    const tr = document.createElement('tr');
                                    let html = ``;
                                    const borderBottom = index === day.jadwal.length - 1 ? 'border-bottom-bold' : ''
                                    if (i === 0) {
                                        html += `
                        <td rowspan="${leng}" class="text-center border-left-bold ${borderBottom}">${nomor}</td>
                        <td rowspan="${leng}" class="border-rigt-bold ${borderBottom}">
                            <span class="mapel">${item.kode || '-'}</span>
                            <br>
                            <small class="waktu">${item.dari + ' ~ ' + item.sampai}</small>
                        </td>`;
                                    }
                                    html += `
                        <td class="kode-materi align-middle ${borderBottom}"><div class="d-flex flex-column"><span>${materi?.kode_materi || ''}</span>${ketMateri}</div></td>
                        <td class="text-center align-middle border-rigt-bold ${borderBottom}">${btnAksiMateri}</td>
                        <td class="kode-tugas align-middle ${borderBottom}"><div class="d-flex flex-column"><span>${tugas?.kode_materi || ''}</span>${ketTugas}</div></td>
                        <td class="text-center align-middle border-rigt-bold ${borderBottom}">${btnAksiTugas}</td>`;
                                    tr.innerHTML = html;
                                    tbody.appendChild(tr);
                                }
                            }
                            nomor++;
                        });
                    }
                } else {
                    $('#info-empty').text(`Arsip hanya untuk Materi/Tugas yang dijadwalkan sebelum hari ini`);
                }
            });
            console.log('count', countMateriTugas, 'filter', isFilter)
            if (countMateriTugas > 0) {
                table.appendChild(tbody)
                table.classList.remove('d-none')
                $('#info-empty').addClass('d-none');
                $('#filter-by').removeClass('d-none')
            } else {
                table.classList.add('d-none')
                $('#info-empty').removeClass('d-none');
                if (isFilter) {
                    const mapel = $('#filter-mapel option:selected').text();
                    const hari = $('#filter-hari option:selected').text();
                    const sMapel = mapel ? `untuk Mapel ${mapel}` : '';
                    const sHari = hari ? `pada Hari ${hari}` : '';
                    $('#info-empty').text(`Tidak ada Materi dan Tugas ${sMapel} ${sHari}`);
                } else {
                    $('#filter-by').addClass('d-none')
                    $('#info-empty').text(`Tidak ada Materi dan Tugas di Tahun Pelajaran ${tahun}, smt ${smt}, bulan ${bulan}`);
                }
            }
        } else {
            $('#info-empty').text(`Tidak ada Materi dan Tugas di Tahun Pelajaran ${tahun}, smt ${smt}, bulan ${bulan}`);
        }
        $('.detail-nilai').on('click', function (e) {
            showDetail(e);
        })
    }
     */

    function createKetSiswa(logs, sampai) {
        //console.log(logs)
        let mulai = logs ? logs.log_time : null;
        let selesai = logs ? logs.finish_time : null;

        let diff = null;
        if (selesai) {
            let jamJadwal = new Date(`${logs.jadwal_materi} ${sampai}`);
            let jamSiswa = new Date(mulai);

            let interval = Math.abs(jamSiswa - jamJadwal);
            let days = Math.floor(interval / (1000 * 60 * 60 * 24));
            interval -= days * (1000 * 60 * 60 * 24);

            let hours = Math.floor(interval / (1000 * 60 * 60));
            interval -= hours * (1000 * 60 * 60);

            let minutes = Math.floor(interval / (1000 * 60));
            interval -= minutes * (1000 * 60);

            let seconds = Math.floor(interval / 1000);

            let totalMinutes = (days * 24 * 60) + (hours * 60) + minutes;

            let timeJadwal = new Date(`${logs.jadwal_materi} ${sampai}`).getTime();
            let timeSiswa = new Date(mulai).getTime();

            diff = {
                days: days,
                hari: days,
                jam: hours,
                menit: minutes,
                detik: seconds,
                total: totalMinutes,
                interval: (timeSiswa - timeJadwal) > 0 ? -1 : 1, // Approximation for %r%H:%i:%s
                terlambat: (timeSiswa - timeJadwal) > 0
            };
        }
        return diff
    }

    function showDetail(e) {
        const clickedBtn = e.target;
        const jenis = clickedBtn.getAttribute('data-type');
        const idKjm = clickedBtn.getAttribute('data-id');
        const subjudul = jenis === '1' ? 'materi' : 'tugas';

        window.location.href = base_url + 'kbm/buka' + subjudul + '/'+ idKjm
    }

    function filterJadwal(filterMapel, filterHari, data) {
        let res = [];
        if (filterMapel) {
            res = filterByIdMapel(filterMapel, data);
        }
        if (filterHari) {
            res = filterByHari(filterHari, res.length > 0 ? res : data);
        }
        return res;
    }

    function filterByIdMapel(idMapel, data) {
        return data
            .map(item => {
                const jadwalFiltered = item.jadwal.filter(j => j.id_mapel === idMapel);
                return jadwalFiltered.length > 0 ? { ...item, jadwal: jadwalFiltered } : null;
            })
            .filter(Boolean);
    }

    function filterByHari(idHari, data) {
        return data.filter(item => item.id_hari === idHari);
    }

</script>
