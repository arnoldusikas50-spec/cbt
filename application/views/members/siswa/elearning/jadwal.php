<?php


$arrHari = ["7" => "Minggu", "1" => "Senin", "2" => "Selasa", "3" => "Rabu", "4" => "Kamis", "5" => "Jum'at", "6" => "Sabtu"];
?>

<main class="app-main" style="margin-top: 30px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-0">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- <?php $this->load->view('members/siswa/templates/top'); ?> --> <!-- ikasmedia -->
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header alert-default-danger">
                            <h5 class="text-center">
                                Jadwal Pelajaran Kelas <?= $siswa->nama_kelas ?>
                                <br>Tahun Pelajaran <?= $tp_active->tahun ?> Semester <?= $smt_active->smt ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="table-jadwal" class="d-none"></div>
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
            </div>
        </div>
    </section>
    </div>

    <script>
        $(document).ready(function ($) {
            setTimeout(function () {
                $.ajax({
                    url: base_url + 'kbm/get_jadwal_harian',
                    type: "GET",
                    success: async function (data) {
                        await createTable(data);
                        $('#loading').addClass('d-none');
                    }, error: function (xhr, status, error) {
                        $('#loading').addClass('d-none');
                        Swal.fire({
                            title: "ERROR",
                            text: xhr.responseText,
                            icon: "error",
                            showCancelButton: false,
                        });
                    }
                });
            }, 300);
        })

        async function createTable(data) {
            console.log('data', data)
            const jadwalKbm = data.jadwal_kbm;
            const adaJadwal = Object.keys(jadwalKbm?.detail || []).length > 0;
            const times = data.times;
            const arrHari = { "7": "Minggu", "1": "Senin", "2": "Selasa", "3": "Rabu", "4": "Kamis", "5": "Jum'at", "6": "Sabtu" };
            let idLibur = '7';
            let newArrIdHari = [];
            for (const idHari in arrHari) {
                const libur = jadwalKbm.libur > '0' ? jadwalKbm.libur : '7';
                if (idHari === libur) {
                    idLibur = idHari;
                } else {
                    newArrIdHari.push(idHari);
                }
            }
            const tableContainer = document.createElement('div');
            tableContainer.className = 'table-responsive mb-3';
            const weeks = Object.keys(newArrIdHari)
            let arrStart = []
            if (adaJadwal && weeks.length > 0) {
                const table = document.createElement('table');
                table.className = 'my-timetable w-100 border-bottom dataTable';
                table.id = 'tbl';

                // Membuat thead
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                headerRow.className = 'bg-light';

                const headerTh1 = document.createElement('th');
                headerTh1.colSpan = 2;
                headerTh1.style.width = '80px';
                headerTh1.style.minWidth = '80px';
                headerTh1.style.maxWidth = '80px';
                headerTh1.className = 'text-center frozen-header text-white py-2';
                headerTh1.textContent = 'JAM';
                headerRow.appendChild(headerTh1);

                // Menambahkan kolom header untuk setiap newArrIdHari
                for (const hariKey of newArrIdHari) {
                    const value = arrHari[hariKey]
                    const th = document.createElement('th');
                    th.className = 'align-middle text-center';
                    th.style.minWidth = '100px';
                    th.textContent = value;
                    headerRow.appendChild(th);
                }

                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Membuat tbody
                const tbody = document.createElement('tbody');
                let idRow = 0;
                const jadwalRows = [];

                times.forEach(jam => {
                    const startTime = jam.start || '';
                    arrStart.push(startTime)
                    const rowSpanJam = jam.span || 12;

                    if (idRow === 0) {
                        const firstRow = document.createElement('tr');
                        firstRow.className = `row${idRow}`;

                        const td0 = document.createElement('td');
                        td0.className = 'column-time text-center align-top text-xs text-bold times p-0 frozen-column';
                        td0.rowSpan = rowSpanJam;
                        td0.textContent = startTime;
                        firstRow.appendChild(td0);

                        const td1 = document.createElement('td');
                        td1.className = 'frozen-column-2';
                        td1.style.width = '30px';
                        td1.style.height = '10px';
                        firstRow.appendChild(td1);

                        for (let i = 2; i <= weeks.length + 1; i++) {
                            const td = document.createElement('td');
                            td.className = `row0`;
                            firstRow.appendChild(td);
                        }

                        tbody.appendChild(firstRow);
                    } else {
                        const minute = startTime.split(':')[1];
                        const row = document.createElement('tr');
                        row.className = `row${idRow}`;
                        if (idRow % 2 !== 0) row.setAttribute('data-time', startTime);

                        if (minute % 30 === 0) {
                            const td0 = document.createElement('td');
                            td0.className = 'column-time text-center align-top text-xs text-bold times p-0 frozen-column';
                            td0.rowSpan = rowSpanJam;
                            td0.textContent = startTime;
                            row.appendChild(td0);
                        }

                        const td1 = document.createElement('td');
                        td1.className = 'frozen-column-2';
                        td1.style.width = '30px';
                        td1.style.height = '6px';
                        row.appendChild(td1);

                        tbody.appendChild(row);
                    }

                    // Tambahkan data jadwal di kolom
                    const scheduleRow = document.createElement('tr');
                    scheduleRow.className = `row${idRow + 1}`;
                    if (idRow % 2 !== 0) scheduleRow.setAttribute('data-time', startTime);

                    const td1 = document.createElement('td');
                    td1.className = 'frozen-column-3';
                    td1.style.width = '30px';
                    td1.style.height = '6px';
                    scheduleRow.appendChild(td1);

                    for (const idh of newArrIdHari) {
                        if (!jadwalRows[idh] || jadwalRows[idh] === 0) {
                            const arr = jadwalKbm.detail[idh]?.[startTime] || null;
                            const rowSpan = arr ? arr.rows * 2 : 2;
                            jadwalRows[idh] = rowSpan;

                            const td = document.createElement('td');
                            td.className = `align-middle row${idRow + 1} column${idh} text-xs border-right`;
                            td.rowSpan = rowSpan;
                            td.setAttribute('data-dari', startTime)
                            td.setAttribute('data-pos', idRow)
                            td.setAttribute('data-kelas', idh)

                            const div = document.createElement('div');
                            div.className = 'h-100 pb-1 add-mapel';
                            div.style.padding = '0 .1rem';
                            if (arr) {
                                const alertDiv = document.createElement('div');
                                const flex = rowSpan > 6 ? 'flex-column justify-content-center' : 'flex-row justify-content-between'
                                let classAlert = ` p-1 h-100 m-0 d-flex ${flex} alert`;
                                if (arr.id_mapel === '-1') {
                                    classAlert += ' alert-default-warning';
                                } else if (arr.id_mapel === '-2') {
                                    classAlert += ' alert-default-success';
                                } else if (arr.kode) {
                                    classAlert += ' alert-default-info';
                                } else {
                                    classAlert += ' alert-default-secondary';
                                }
                                alertDiv.className = classAlert;

                                if (arr.id_mapel === '-1') {
                                    alertDiv.innerHTML = `<div class="text-xs ms-1">Istirahat</div>
                                    <div class="d-inline-block mx-1 text-nowrap">${arr.dari} - ${arr.sampai}</div>`;
                                } else if (arr.id_mapel === '-2') {
                                    alertDiv.innerHTML = `<div class="text-xs ms-1 text-bold text-nowrap">Upacara</div>
                                    <div class="d-inline-block mx-1 text-nowrap">${arr.dari} - ${arr.sampai}</div>`;
                                } else {
                                    if (arr.kode) {
                                        alertDiv.innerHTML = `<div class="text-bold ms-1">${arr.kode}</div>
                                    <div class="d-inline-block mx-1 text-nowrap">${arr.dari} - ${arr.sampai}</div>`;
                                    }
                                }
                                div.appendChild(alertDiv);
                            }

                            td.appendChild(div);
                            scheduleRow.appendChild(td);
                        }
                    }

                    tbody.appendChild(scheduleRow);

                    // Update jumlah baris
                    for (const idks of newArrIdHari) {
                        if (jadwalRows[idks]) jadwalRows[idks] -= 2;
                    }

                    idRow += 2;
                });

                table.appendChild(tbody);
                tableContainer.appendChild(table);
            } else {
                tableContainer.innerHTML = `<div class="alert alert-default-warning shadow align-content-center" role="alert">
                    Belum ada jadwal pelajaran
                 </div>`;
            }

            const element = document.getElementById("table-jadwal");
            element.classList.remove("d-none");

            let oldH3Element = document.querySelector('div.table-responsive')
            if (oldH3Element) oldH3Element.parentNode.removeChild(oldH3Element)
            element.appendChild(tableContainer);
        }

    </script>