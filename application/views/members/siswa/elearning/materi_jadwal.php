<?php

?>
<div class="content-wrapper" style="margin-top: 30px;">
    <div class="sticky">
    </div>
    <section class="content overlap">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- <?php $this->load->view('members/siswa/templates/top'); ?> --> <!-- ikasmedia -->
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header alert-default-success">
                            <h6 class="text-center">
                                <?= strtoupper($judul ?? '') ?> HARI INI<br/><?= buat_tanggal(date('D, d M Y')) ?>
                            </h6>
                        </div>
                        <div class="card-body" style="min-height: 300px">
                            <div class="row" id="jadwal-container">
                            </div>
                        </div>
                        <div class="overlay" id="loading-jadwal">
                            <!-- <div class="spinner-grow"></div> -->
                     <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"> </span>
                            Loading...
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header alert-default-danger">
                            <h5 class="text-center">
                                <?= strtoupper($judul ?? '') ?> TERDAHULU
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="dates-wrapper">
                                <span class="scroll-button scroll-left" onclick="scrollKiri()">&#10094;</span>
                                <div id="dates" class="dates-container"></div>
                                <span class="scroll-button scroll-right" onclick="scrollKanan()">&#10095;</span>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 text-center alert-default-warning">
                                    <p class="text-bold" id="tgl-detail">Silakan pilih tanggal</p>
                                </div>
                                <div class="col-12 table-responsive d-none" id="tbl-konten">
                                </div>
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

<?= form_open('', array('id' => 'getjadwal')); ?><?= form_close() ?>

<script>
    const jenis = '<?=$jenis?>',
        id_kelas = '<?=$siswa->id_kelas?>',
        id_siswa = '<?=$siswa->id_siswa?>',
        tp = '<?=$tp_active->id_tp?>',
        smt = '<?=$smt_active->id_smt?>',
        subjudul = '<?=$subjudul?>';
    let tglSelected, blnSelected, monthSelected, thnSelected, weekSelected;

    let libur = '';
    const tempMateri = 'garudaCBT.materi_siswa'

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
        var w = $(window).width();
        setWidth(w);

        $(window).on('resize', function () {
            setWidth($(this).width());
        });

        const data = await ajaxGet('kbm/get_jadwal?siswa='+id_siswa+'&jenis='+jenis+'&kelas='+id_kelas);
        libur = data.libur;
        await populateJadwal(data.jadwal_materi)
        generateDates()

        const temp = localStorage.getItem(tempMateri);
        if (temp) {
            const materi = JSON.parse(temp);
            tglSelected = materi.tgl;
            blnSelected = materi.bln;
            monthSelected = materi.mnt;
            thnSelected = materi.thn;
            weekSelected = materi.wek;

            const previouslySelected = document.querySelectorAll('.date-box');
            if (previouslySelected) {
                for (const previouslySelectedElement of previouslySelected) {
                    if (previouslySelectedElement.id === `${tglSelected}-${monthSelected}-${thnSelected}`) {
                        previouslySelectedElement.classList.add('active');
                    }
                }
            }
            if (tglSelected && blnSelected && monthSelected && thnSelected && weekSelected) {
                loadMateriByTgl(tglSelected, blnSelected, monthSelected, thnSelected, weekSelected)
            }
        }
    });

    async function populateJadwal(jadwalMateri) {
        const judul = jenis == '1' ? 'Materi' : 'Tugas'
        const container = document.querySelector("#jadwal-container");
        if (Object.keys(jadwalMateri.detail).length > 0) {
            const materies = o => Object.keys(o).sort().reduce((r, k) => (r[k] = o[k], r), {})
            const details = materies(jadwalMateri.detail);
            let sampai = '0';
            for (const jam in details) {
                const jadwal = jadwalMateri.detail[jam]

                if (jam < sampai) {
                    // skip overlapping time slots
                    continue;
                }
                console.log('cbtSiswa', jadwal)
                if (sampai < jadwal.sampai) sampai = jadwal.sampai;

                const isIstirahat = jadwal.id_mapel === "-1";
                const adaMapel = jadwal.mapel;
                const adaMateri = jadwal.materi.length > 0;
                const cardBorder = isIstirahat
                    ? "alert-default-danger" //ikasmedia
                    : adaMapel
                        ? "alert-light"
                        : "alert-default-warning";
                const rows = jadwal.materi.length;
                const textClass = adaMateri ? "text-bold" : "text-black-50";

                let html = `
      <div class="col-md-6 col-lg-4">
        <div class="alert border ${cardBorder}">
          <h5>${jadwal.dari} ~ ${jadwal.sampai}</h5>
          <div style="min-height: 90px;">
            <table class="table table-sm table-borderless mb-1 ${adaMateri ? "border-bottom" : ""}">
              <tr>
                <td colspan="3" class="align-top p-0 text-bold">
                  ${isIstirahat ? "Istirahat" : adaMapel || "Belum diatur"}
                </td>
              </tr>`;

                if (adaMateri && !isIstirahat) {
                    jadwal.materi.forEach((materi, index) => {
                        const numMtr = rows === 1 ? '' : `${index + 1}.`;
                        html+= `<tr>`
                        if (index === 0) {
                            html+= `<td rowspan="${rows}" class="align-top p-0 text-sm" style="min-width: 60px;width: 60px">${judul}</td>
                                    <td class="align-top p-0 text-sm" style="min-width: 14px;width: 14px">${numMtr}</td>`;
                        } else {
                            html += `<td class="align-top p-0 text-sm">${index + 1}</td>`;

                        }
                        html += `<td class="align-top p-0 text-sm pb-1 ${textClass}">${materi.judul_materi}</td></tr>`;
                    });
                } else {
                    html += `
        <tr>
          <td class="align-top p-0 text-sm" style="min-width: 60px;width: 60px">${judul}</td>
          <td class="align-top p-0 text-sm"></td>
          <td class="align-top p-0 text-sm ${textClass}">
            ${isIstirahat ? "Istirahat" : "Belum diatur"}
          </td>
        </tr>`;
                }

                html += `
            </table>
            <div class="row justify-content-end">`;

                const dari = (jadwal.dari).replace(':', '')
                const disableBtn = Number(jadwal.now) < Number(dari);
                let disabled = disableBtn ? 'disabled' : ''

                if (!isIstirahat && adaMapel && adaMateri) {
                    jadwal.materi.forEach((materi, index) => {
                        const logEntry = jadwal.log.find((log) => log.id_materi === materi.id_kjm);
                        let btnStatus, dataStatus, textContent, nilai = "0", msgGuru = "-";
                        console.log('logEntry', logEntry)

                        const selesai = logEntry?.finish_time;
                        const adaNilai = logEntry?.nilai;

                        if (!disableBtn) {
                            if (logEntry) {
                                if (logEntry?.ulang === '1') {
                                    btnStatus = "btn-danger";
                                    dataStatus = "3";
                                    textContent = `Ulangi ${judul} ${rows === 1 ? '' : index + 1}`;
                                } else if (selesai && (!adaNilai || adaNilai === '0')) {
                                    btnStatus = "btn-info";
                                    nilai = logEntry.nilai || "-";
                                    dataStatus = "2";
                                    textContent = `${judul} ${rows === 1 ? '' : index + 1} selesai, menunggu dikoreksi`;
                                } else if (adaNilai && adaNilai !== '0') {
                                    btnStatus = "btn-success";
                                    dataStatus = "2";
                                    textContent = `Nilai ${judul} ${rows === 1 ? '' : index + 1}`;
                                    nilai = logEntry.nilai;
                                    msgGuru = logEntry.catatan;
                                } else {
                                    btnStatus = "btn-warning";
                                    dataStatus = "1";
                                    textContent = `Lanjutkan ${judul} ${rows === 1 ? '' : index + 1}`;
                                }
                            } else {
                                btnStatus = "btn-danger";
                                dataStatus = "0";
                                textContent = `Buka ${judul} ${rows === 1 ? '' : index + 1}`;
                            }
                        } else {
                            btnStatus = "btn-dark btn-disabled";
                            dataStatus = "0";
                            textContent = `${judul} ${rows === 1 ? '' : index + 1} Belum Mulai`;
                        }

                        html += `
          <div>
            <button
              data-status="${dataStatus}"
              data-nilai="${nilai}"
              data-id="${materi.id_kjm}"
              data-msg="${msgGuru}"
              class="btn ${btnStatus} btn-sm m-1 text-xs"
              onclick="showDetail(this, true)" ${disabled}>
              ${textContent}
            </button>
          </div>`;
                    });
                }

                html += `
            </div>
          </div>
        </div>
      </div>`;

                container.innerHTML += html;
            }
        } else {
            container.innerHTML = `
    <div class="alert alert-default-info w-100">
      <span>Belum ada Jadwal ${judul} hari ini</span>
    </div>`;
        }
        const loading = document.getElementById('loading-jadwal');
        loading.classList.add('d-none')
    }

    function generateDates() {
        const datesContainer = document.getElementById('dates');
        const now = new Date();
        // Mendapatkan jumlah hari dalam bulan ini
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        const dayNames = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];

        for (let i = -30; i <= 1; i++) {
            const date = new Date(now);
            date.setDate((now.getDate()-1) + i);

            const dayName = dayNames[date.getDay()];
            const day = date.getDate();
            const month = date.getMonth();
            const year = now.getFullYear();
            const monthName = monthNames[month];

            const tgl = ('0' + day).slice(-2)
            const bulan = ('0' + (month + 1)).slice(-2)
            const thn = year.toString()
            const dateBox = document.createElement('div');
            dateBox.id = `${tgl}-${bulan}-${thn}`;

            dateBox.setAttribute('data-weekday', date.getDay().toString())
            dateBox.setAttribute('data-date', tgl)
            dateBox.setAttribute('data-month', bulan)
            dateBox.setAttribute('data-monthname', monthName)
            dateBox.setAttribute('data-year', thn)
            dateBox.className = 'date-box mx-1 px-2 py-1 btn text-center';
            dateBox.innerHTML = `<b>${dayName}</b><br /><small>${day} ${monthName}</small>`;
            if (date.toDateString() === now.toDateString()) {
                dateBox.classList.add('btn-outline-secondary');
                dateBox.classList.add('btn-disabled');
                dateBox.setAttribute('disabled', 'disabled')
            } else if (date.getDay() === Number(libur)) {
                dateBox.classList.add('btn-outline-danger');
                dateBox.classList.add('btn-disabled');
                dateBox.setAttribute('disabled', 'disabled')
            } else {
                dateBox.classList.add('btn-outline-primary');
            }
            datesContainer.appendChild(dateBox);
        }

        document.querySelectorAll('.date-box:not(.btn-disabled)').forEach(function (dateBox) {
            dateBox.addEventListener('click', handleDateClick);
        });
    }

    function setWidth(width) {
        let w;
        if (width <= 750) {
            w = '';
        } else if (width <= 975) {
            w = 300;
        } else if (width <= 1182) {
            w = 260;
        } else {
            w = 320;
        }
        $(".nama-mapel").css("width", w);
    }

    function scrollKiri() {
        const datesContainer = document.getElementById('dates');
        datesContainer.scrollBy({left: -150, behavior: 'smooth'});
    }

    function scrollKanan() {
        const datesContainer = document.getElementById('dates');
        datesContainer.scrollBy({left: 150, behavior: 'smooth'});
    }

    function handleDateClick(event) {
        const clickedElement = event.currentTarget;
        clickedElement.classList.add('active');

        const selectedDate = clickedElement.getAttribute('data-date');
        let selectedWeekday = clickedElement.getAttribute('data-weekday');
        const selectedMonth = clickedElement.getAttribute('data-month');
        const selectedYear = clickedElement.getAttribute('data-year');
        const selectedBulan = clickedElement.getAttribute('data-monthname');
        if (selectedWeekday === '0') selectedWeekday = '7';

        loadMateriByTgl(selectedDate, selectedBulan, selectedMonth, selectedYear, selectedWeekday)
    }

    function loadMateriByTgl(selectedDate, selectedBulan, selectedMonth, selectedYear, selectedWeekday) {
        tglSelected = selectedDate;
        blnSelected = selectedBulan;
        monthSelected = selectedMonth;
        thnSelected = selectedYear;
        weekSelected = selectedWeekday;

        const previouslySelected = document.querySelectorAll('.date-box.active');
        if (previouslySelected) {
            for (const previouslySelectedElement of previouslySelected) {
                if (previouslySelectedElement.id !== `${selectedDate}-${selectedMonth}-${selectedYear}`) {
                    previouslySelectedElement.classList.remove('active');
                }
            }
        }

        document.getElementById('tgl-detail').textContent = selectedDate + ' ' + selectedBulan + ' ' + selectedYear
        const loading = document.getElementById('loading');
        loading.classList.remove('d-none')

        let formData = new FormData(document.querySelector('form'));
        formData.append('tgl', selectedYear + '-' + selectedMonth + '-' + selectedDate)
        formData.append('jenis', jenis)
        formData.append('id_kelas', id_kelas)
        formData.append('id_hari', selectedWeekday)
        formData.append('id_siswa', id_siswa)
        formData.append('tp', tp)
        formData.append('smt', smt)

        document.querySelectorAll('.detail-nilai').forEach(function (dateBox) {
            dateBox.removeEventListener('click', showDetail);
        });

        setTimeout(function () {
            $.ajax({
                url: base_url + 'kbm/materi_tugas_harian',
                type: "POST",
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    loading.classList.add('d-none')
                    const tblKonten = document.getElementById('tbl-konten');
                    tblKonten.replaceChild(generateTable(data), tblKonten.childNodes[0]);
                    tblKonten.classList.remove('d-none')
                    listenClick()
                }, error: function (xhr, status, error) {
                    loading.classList.add('d-none')
                    console.log("error", xhr.responseText);
                    swal.fire({
                        title: "ERROR",
                        text: "Terjadi kesalahan, silakan refresh halaman",
                        icon: "error",
                        showCancelButton: false,
                    });
                }
            });
        }, 500)

    }

    function generateTable(data) {
        console.log('data', data)
        // Create table and header
        const table = document.createElement('table');
        table.classList = 'table table-sm w-100'
        const headerRow = document.createElement('tr');
        headerRow.classList = 'alert alert-default-primary'
        const headers = [
            {midth: '200px', text: 'Waktu'},
            {midth: '100px', text: 'Mata Pelajaran'},
            {midth: '200px', text: jenis === '1' ? 'Materi' : 'Tugas'},
            {midth: '100px', text: 'Status'}
        ];

        headers.forEach((headerText, index) => {
            const header = document.createElement('th');
            header.textContent = headerText.text;
            header.style.minWidth = headerText.midth
            if (index === 0) header.classList = 'text-center'
            headerRow.appendChild(header);
        });
        table.appendChild(headerRow);

        let waktu = Object.keys(data.detail);
        waktu.sort();
        // Populate rows
        for (const jam of waktu) {
            const entry = data.detail[jam];
            if (Array.isArray(entry.materi) && entry.materi.length === 0) {
                const row = document.createElement('tr');

                // Waktu
                const cellWaktu = document.createElement('td');
                cellWaktu.className = 'text-center align-middle text-monospace text-bold'
                cellWaktu.textContent = entry.dari + ' ~ ' + entry.sampai;
                row.appendChild(cellWaktu);

                // Mata Pelajaran
                const cellMapel = document.createElement('td');
                cellMapel.className = 'align-middle text-sm'
                cellMapel.textContent = entry.id_mapel === '-1' ? 'Istirahat' : (entry.kode || '-');
                row.appendChild(cellMapel);

                // Materi
                const cellMateri = document.createElement('td');
                cellMateri.textContent = '-';
                row.appendChild(cellMateri);

                // Status Siswa
                const cellStatus = document.createElement('td');
                cellStatus.textContent = '-';
                row.appendChild(cellStatus);

                table.appendChild(row);
            } else {
                if (entry.materi != null && Object.keys(entry.materi).length > 0) {
                    entry.materi.forEach((materiItem, index) => {
                        const row = document.createElement('tr');

                        // Waktu
                        const cellWaktu = document.createElement('td');
                        cellWaktu.className = 'text-center align-middle text-monospace text-bold'
                        if (index === 0) {
                            cellWaktu.textContent = entry.dari + ' ~ ' + entry.sampai;
                            cellWaktu.rowSpan = entry.materi.length;
                        }
                        if (index === 0) {
                            row.appendChild(cellWaktu);
                        }

                        // Mata Pelajaran
                        const cellMapel = document.createElement('td');
                        cellMapel.className = 'align-middle text-sm'
                        if (index === 0) {
                            cellMapel.textContent = entry.id_mapel === '-1' ? 'Istirahat' : (entry.kode || '-');
                            cellMapel.rowSpan = entry.materi.length;
                        }
                        if (index === 0) row.appendChild(cellMapel);

                        // Materi
                        const cellMateri = document.createElement('td');
                        cellMateri.textContent = materiItem.judul_materi;
                        row.appendChild(cellMateri);

                        // Status Siswa
                        const cellStatus = document.createElement('td');
                        const logEntry = entry.log.find(log => log.id_materi == materiItem.id_kjm);
                        const btnStatus = document.createElement('button');
                        const adaNilai = logEntry?.nilai;
                        const selesai = logEntry?.finish_time;
                        if (logEntry) {
                            if (logEntry?.ulang === '1') {
                                btnStatus.setAttribute('data-status', '3') // ulangi
                                btnStatus.setAttribute('data-id', logEntry?.id_materi)
                                btnStatus.className = 'btn btn-danger btn-sm detail-nilai'
                                btnStatus.textContent = 'Ulangi'
                            } else if (selesai && (!adaNilai || adaNilai === '0')) {
                                btnStatus.setAttribute('data-status', '2') // selesai
                                btnStatus.setAttribute('data-id', logEntry?.id_materi)
                                btnStatus.className = 'btn btn-info btn-sm detail-nilai'
                                btnStatus.textContent = 'Blm dikoreksi'
                            } else if (adaNilai && adaNilai !== '0') {
                                btnStatus.setAttribute('data-status', '2') // selesai
                                btnStatus.setAttribute('data-nilai', logEntry?.nilai)
                                btnStatus.setAttribute('data-msg', logEntry?.catatan)
                                btnStatus.setAttribute('data-id', logEntry?.id_materi)
                                btnStatus.className = 'btn btn-success btn-sm detail-nilai'
                                btnStatus.textContent = 'Nilai'
                            } else {
                                btnStatus.setAttribute('data-status', '1') // lanjutkan
                                btnStatus.setAttribute('data-id', logEntry?.id_materi)
                                btnStatus.className = 'btn btn-warning btn-sm detail-nilai'
                                btnStatus.textContent = 'Lanjutkan'
                            }
                        } else {
                            btnStatus.setAttribute('data-status', '0') // kerjakan
                            btnStatus.setAttribute('data-id', materiItem.id_kjm)
                            btnStatus.className = 'btn btn-danger btn-sm detail-nilai'
                            btnStatus.textContent = 'Kerjakan'
                        }
                        cellStatus.appendChild(btnStatus)
                        row.appendChild(cellStatus);

                        table.appendChild(row);
                    });
                }
            }
        }

        return table;
    }

    function listenClick() {
        document.querySelectorAll('.detail-nilai').forEach(function (dateBox) {
            dateBox.addEventListener('click', showDetail);
        });
    }

    function showDetail(e, tmp) {
        const clickedBtn = tmp ? e : e.currentTarget;
        const idKjm = clickedBtn.getAttribute('data-id');
        const nilai = clickedBtn.getAttribute('data-nilai') || '-';
        const status = clickedBtn.getAttribute('data-status');
        const msg = clickedBtn.getAttribute('data-msg') || (nilai !== '-' ? 'Tidak ada catatan' : 'Menunggu penilaian');
        console.log('detail', nilai)
        // status: 0=blm dikerjakan, 1=blm selesai, 2=selesai, 3=ulangi\
        const html = function () {
            if (nilai === "-") {
                return 'Belum ada nilai, menunggu dikoreksi';
            } else {
                return '<table class="table table-sm table-borderless w-100">' +
                    '    <tr>' +
                    '        <th class="text-sm">NILAI</th>' +
                    '        <th class="text-sm text-left">Catatan Guru</th>' +
                    '    </tr>' +
                    '    <tr>' +
                    '        <td class="text-lg text-bold">' + nilai + '</td>' +
                    '        <td class="text-left">' + msg + '</td>' +
                    '    </tr>' +
                    '</table>';
            }
        }

        if (status === '2') {
            swal.fire({
                title: "Nilai <?= $judul ?? '' ?>",
                html: html(),
                confirmButtonText: "TUTUP"
            })
        } else {
            localStorage.setItem(tempMateri, JSON.stringify({
                jns: jenis,
                thn: thnSelected,
                mnt: monthSelected,
                bln: blnSelected,
                tgl: tglSelected,
                wek: weekSelected
            }));

            window.location.href = base_url + 'kbm/buka' + subjudul + '/'+ idKjm
        }
    }
</script>
