<?php


?>

<div class="content-wrapper" style="margin-top: 30px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-0">
        <div class="container">
             <!-- <?php $this->load->view('members/siswa/templates/top'); ?> --> <!-- ikasmedia -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <div class="card-title text-white">
                                Kehadiran Hari Ini
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-center text-bold"><?=$today?></p>
                            <?php
                            if ($kbm == null) : ?>
                                <div class="col-lg-12 p-0">
                                    <div class="alert align-content-center" role="alert">
                                        Jadwal belum dibuat.
                                    </div>
                                </div>
                            <?php else:
                                if (count($jadwals) > 0) :?>
                                <div class="table-responsive">
                                    <table class="table table-sm w-100 table-bordered">
                                        <thead>
                                        <tr>
                                            <th rowspan="3" class="text-center align-middle alert-default-info">No.</th>
                                            <th rowspan="3" class="text-center align-middle alert-default-primary">Mata Pelajaran</th>
                                            <th class="text-center align-middle alert-info" colspan="4">Kehadiran</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-center alert-default-success">Materi</th>
                                            <th colspan="2" class="text-center alert-default-danger">Tugas</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center alert-default-warning" style="width: 120px">Status</th>
                                            <th class="text-center alert-default-secondary">Keterangan</th>
                                            <th class="text-center alert-default-warning" style="width: 120px">Status</th>
                                            <th class="text-center alert-default-secondary">Keterangan</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($jadwals as $num=>$jadwal) :
                                            $ada_materi = count($jadwal->materi) > 0;
                                            $ada_tugas = count($jadwal->tugas) > 0;
                                            $leng = max(count($jadwal->materi), count($jadwal->tugas));
                                            $mapel = $jadwal->id_mapel ? ($jadwal->id_mapel == '-1' ? 'Istirahat' : $jadwal->nama_mapel) : '-';
                                            if (!$ada_materi && !$ada_tugas) :
                                                $bgClass = $jadwal->id_mapel == '-1' ? 'bg-gray-light': ''?>
                                                <tr>
                                                    <td class="text-center <?= $bgClass ?>"><?=$num +1?></td>
                                                    <td class="<?= $bgClass ?>">
                                                        <span><?= $mapel ?></span>
                                                        <br />
                                                        <span class="text-sm"><?= $jadwal->dari .' ~ '. $jadwal->sampai?></span>
                                                    </td>
                                                    <td class="<?= $bgClass ?>"></td>
                                                    <td class="<?= $bgClass ?>"></td>
                                                    <td class="<?= $bgClass ?>"></td>
                                                    <td class="<?= $bgClass ?>"></td>
                                                </tr>
                                            <?php else: ?>
                                                <?php for ($i = 0; $i < $leng; $i++) :
                                                    $materi = $jadwal->materi[$i] ?? [];
                                                    $hadirMateri = $materi->status ?? null;
                                                    $bgMtr = $materi ? "" : "bg-gray-light";

                                                    $tugas = $jadwal->tugas[$i] ?? [];
                                                    $hadirTugas = $tugas->status ?? null;
                                                    $bgTgs = $tugas ? "" : "bg-gray-light"; ?>
                                                    <tr>
                                                        <?php if ($i == 0):?>
                                                            <td rowspan="<?=$leng?>" class="text-center"><?=$num +1?></td>
                                                            <td rowspan="<?=$leng?>">
                                                                <span><?= $mapel ?></span>
                                                                <br />
                                                                <span class="text-sm"><?= $jadwal->dari .' ~ '. $jadwal->sampai?></span>
                                                            </td>
                                                        <?php endif; ?>
                                                        <td class="text-center">
                                                            <?= $hadirMateri->status ?? ''; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?= $hadirMateri && $hadirMateri->terlambat ? $hadirMateri->ket : ''; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?= $hadirTugas->status ?? ''; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?= $hadirTugas && $hadirTugas->terlambat ? $hadirTugas->ket : ''; ?>
                                                        </td>
                                                    </tr>
                                                <?php endfor;?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else : ?>
                                    <div class="col-lg-12 p-0">
                                        <div class="alert align-content-center" role="alert">
                                            Tidak ada jadwal hari ini
                                        </div>
                                    </div>
                                <?php endif; endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card card-indigo">
                        <div class="card-header">
                            <div class="card-title text-white">
                                Detail Kehadiran
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap" id="months"></div>
                            <div class="row mt-3">
                                <div class="col-12 table-responsive">
                                    <p class="text-bold text-center" id="tgl-detail">Silakan pilih Bulan</p>
                                    <table id="tableMapel" class="w-100 table table-bordered table-sm d-none">
                                        <thead>
                                        <tr a class="alert-default-secondary">
                                            <th class="text-center align-middle">No.</th>
                                            <th class="text-center align-middle">Mata Pelajaran</th>
                                            <th class="text-center align-middle" colspan="4">Kehadiran</th>
                                        </tr>
                                        </thead>
                                        <tbody id="tbodyMapel">
                                        </tbody>
                                    </table>
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
    const arrBulan = JSON.parse('<?= json_encode($bulans)?>');
    const id_kelas = '<?=$siswa->id_kelas?>', id_siswa = '<?=$siswa->id_siswa?>',
        tp = '<?=$tp_active->id_tp?>', smt = '<?=$smt_active->id_smt?>';

    $(document).ready(function () {
        generateDates()
    });

    function generateDates() {
        const datesContainer = document.getElementById('months');
        const keysBulan = Object.keys(arrBulan);
        keysBulan.sort(function (b, c) {
            return b.localeCompare(c)
        })
        for (const keybulan of keysBulan) {
            const bulan = arrBulan[keybulan];
            const dateBox = document.createElement('div');
            dateBox.setAttribute('data-month', keybulan)
            dateBox.setAttribute('data-monthname', bulan)
            dateBox.className = 'date-box p-1 m-1 btn btn-xs text-center btn-outline-primary';
            dateBox.innerHTML = `<span class="text-sm">${bulan.substring(0, 3)}</span><span class="text-sm d-md-inline d-none">${bulan.substring(3)}</span>`;

            datesContainer.appendChild(dateBox);
        }
        document.querySelectorAll('.date-box:not(.btn-disabled)').forEach(function (dateBox) {
            dateBox.addEventListener('click', handleDateClick);
        });
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
        const previouslySelected = document.querySelector('.date-box.btn-primary');
        if (previouslySelected) {
            previouslySelected.classList.remove('btn-primary')
            previouslySelected.classList.add('btn-outline-primary')
        }

        const clickedElement = event.currentTarget;
        clickedElement.classList.remove('btn-outline-primary')
        clickedElement.classList.add('btn-primary')

        const selectedMonth = clickedElement.getAttribute('data-month');
        document.getElementById('tgl-detail').textContent = 'Bulan ' + clickedElement.getAttribute('data-monthname')

        const loading = document.getElementById('loading');
        loading.classList.remove('d-none')

        let formData = new FormData(document.querySelector('form'));
        formData.append('bulan', selectedMonth)
        formData.append('id_kelas', id_kelas)
        formData.append('id_siswa', id_siswa)
        formData.append('tp', tp)
        formData.append('smt', smt)
        setTimeout(function () {
            $.ajax({
                url: base_url + 'kbm/load_absensi_bulanan',
                type: "POST",
                processData: false,
                contentType: false,
                data: formData,
                success: function (data) {
                    loading.classList.add('d-none')
                    mergeJson(data);
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

    function mergeJson(data) {
        console.log('res', data)
        let jadwals = data.jadwal_mapel || []
        const materis = data.jadwal_materi || {}
        const logs = data.logs

        for (const jadwalHarian of jadwals) {
            jadwalHarian.jadwal.sort(function (a, b) {
                return a.dari.localeCompare(b.dari);
            });
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
            }
        }
        let oldH3Element =  document.querySelector('tbody#tbodyMapel')
        if(oldH3Element) oldH3Element.parentNode.removeChild(oldH3Element)
        generateTable(jadwals)
    }

    function generateTable(data) {
        const table = document.getElementById('tableMapel');
        const tbody = document.createElement('tbody');
        tbody.setAttribute('id', 'tbodyMapel')
        if (data && data.length > 0) {
            data.forEach(day => {
                if (!day.is_libur) {
                    const trTanggal = document.createElement('tr');
                    trTanggal.classList.add('group', 'alert-default-info');
                    const tglLengkap = `${day.hari}, ${new Date(day.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}`;
                    trTanggal.innerHTML = `
                    <th rowspan="2" class="text-sm pl-2 border align-middle tanggal-lengkap" colspan="2">${tglLengkap}</th>
                    <th colspan="2" class="text-sm text-center align-middle alert-default-success" data-value="status-materi">Materi</th>
                    <th colspan="2" class="text-sm text-center align-middle alert-default-danger" data-value="status-tugas">Tugas</th>`;
                    tbody.appendChild(trTanggal)

                    const trSubTanggal = document.createElement('tr');
                    trSubTanggal.classList.add('group', 'alert-default-info');
                    trSubTanggal.innerHTML = `
                    <th class="text-sm text-center align-middle">Status</th>
                    <th class="text-sm text-center align-middle">Keterangan</th>
                    <th class="text-sm text-center align-middle">Status</th>
                    <th class="text-sm text-center align-middle">Keterangan</th>`;
                    tbody.appendChild(trSubTanggal);

                    let nomor = 1;
                    if (!day.jadwal || day.jadwal.length === 0) {
                        const tr = document.createElement('tr');
                        tr.classList.add('text-center', 'bg-gray-light');
                        tr.innerHTML = `<td colspan="6">Tidak ada jadwal di tgl. ${tglLengkap}</td>`;
                        tbody.appendChild(tr);
                        return
                    }
                    day.jadwal.forEach(item => {
                        const adaMateri = item.materi.length > 0;
                        const adaTugas = item.tugas.length > 0;
                        const leng = Math.max(item.materi.length, item.tugas.length);
                        if (!adaMateri && !adaTugas) {
                            const tr = document.createElement('tr');
                            const bgClass = item.id_mapel === '-1' ?"bg-gray-light" : ""
                            tr.innerHTML = `
                        <td class="text-center ${bgClass}">${nomor}</td>
                        <td class="${bgClass}">
                            <span class="mapel">${item.id_mapel === '-1' ? 'Istirahat' : (item.nama_mapel || '-')}</span>
                            <br>
                            <small class="waktu">${item.dari + ' ~ ' + item.sampai}</small>
                        </td>
                        <td class="text-center align-middle bg-gray-light"></td>
                        <td class="text-center align-middle bg-gray-light"></td>
                        <td class="text-center align-middle bg-gray-light"></td>
                        <td class="text-center align-middle bg-gray-light"></td>`;
                            tbody.appendChild(tr);
                        } else {
                            //console.log('item', item)
                            for (let i = 0; i < leng; i++) {
                                const materi = item.materi[i]
                                const hadirMateri = materi?.kehadiran
                                let bgMtr = materi ? "" : "bg-gray-light"

                                const tugas = item.tugas[i]
                                const hadirTugas = tugas?.kehadiran
                                let bgTgs = tugas  ? "" : "bg-gray-light"

                                const statusMateri = createKetSiswa(hadirMateri, item.sampai);
                                let ketMateri = '';
                                if (hadirMateri) {
                                    if (hadirMateri.finish_time) {
                                        if (hadirMateri.nilai != null) {
                                            if (hadirMateri.nilai !== '0') {
                                                if (statusMateri && statusMateri.terlambat) {
                                                    ketMateri = 'Terlambat ';
                                                    bgMtr = "alert-default-warning"
                                                } else {
                                                    ketMateri = 'Selesai';
                                                    bgMtr = "alert-default-success"
                                                }
                                            } else {
                                                ketMateri = 'Ulangi'
                                                bgMtr = "alert-default-danger"
                                            }
                                        } else {
                                            if (statusMateri && statusMateri.terlambat) {
                                                ketMateri = 'Terlambat ';
                                                bgMtr = "alert-default-warning"
                                            } else {
                                                ketMateri = 'Selesai';
                                                bgMtr = "alert-default-success"
                                            }
                                        }
                                    } else {
                                        ketMateri = 'Blm selesai';
                                        bgMtr = "alert-default-warning"
                                    }
                                } else {
                                    if (materi) {
                                        ketMateri = 'Blm dikerjakan'
                                        bgMtr = "alert-default-danger"
                                    }
                                }

                                const statusTugas = createKetSiswa(hadirTugas, item.sampai);
                                let ketTugas = '';
                                if (hadirTugas) {
                                    if (hadirTugas.finish_time) {
                                        if (hadirTugas.nilai != null) {
                                            console.log('sttugas', hadirTugas.nilai)
                                            if (hadirTugas.nilai !== '0') {
                                                if (statusTugas && statusTugas.terlambat) {
                                                    ketTugas = 'Terlambat ';
                                                    bgTgs = "alert-default-warning"
                                                } else {
                                                    ketTugas = 'Selesai';
                                                    bgTgs = "alert-default-success"
                                                }
                                            } else {
                                                ketTugas = 'Ulangi'
                                                bgTgs = "alert-default-danger"
                                            }
                                        } else {
                                            if (statusTugas && statusTugas.terlambat) {
                                                ketTugas = 'Terlambat ';
                                                bgTgs = "alert-default-warning"
                                            } else {
                                                ketTugas = 'Selesai';
                                                bgTgs = "alert-default-success"
                                            }
                                        }
                                    } else {
                                        ketTugas = 'Blm selesai';
                                        bgTgs = "alert-default-warning"
                                    }
                                } else {
                                    if (tugas) {
                                        ketTugas = 'Blm dikerjakan'
                                        bgTgs = "alert-default-danger"
                                    }
                                }

                                if (i === 0) {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                        <td rowspan="${leng}" class="text-center">${nomor}</td>
                        <td rowspan="${leng}">
                            <span class="mapel">${item.nama_mapel || '-'}</span>
                            <br>
                            <small class="waktu">${item.dari + ' ~ ' + item.sampai}</small>
                        </td>
                        <td class="text-center align-middle status-materi ${bgMtr}">${statusMateri ? '&check;' : ''}</td>
                        <td class="text-center align-middle keterangan-materi ${bgMtr}">${ketMateri}</td>
                        <td class="text-center align-middle status-tugas ${bgTgs}">${statusTugas ? '&check;':''}</td>
                        <td class="text-center align-middle keterangan-tugas ${bgTgs}">${ketTugas}</td>`;
                                    tbody.appendChild(tr);
                                } else {
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                        <td class="text-center align-middle status-materi ${bgMtr}">${statusMateri ? '&check;' : ''}</td>
                        <td class="text-center align-middle keterangan-materi ${bgMtr}">${ketMateri}</td>
                        <td class="text-center align-middle status-tugas ${bgTgs}">${statusTugas ? '&check;':''}</td>
                        <td class="text-center align-middle keterangan-tugas ${bgTgs}">${ketTugas}</td>`;
                                    tbody.appendChild(tr);
                                }
                            }
                        }
                        nomor ++;
                    });
                }
            });
        } else {
            const trTanggal = document.createElement('tr');
            trTanggal.classList.add('group', 'alert-default-info');
            trTanggal.innerHTML = `<td class="text-sm pl-2 border align-middle tanggal-lengkap text-center" colspan="6">Tidak ada data untuk ditampilkan</td>`;
            tbody.appendChild(trTanggal)
        }
        table.appendChild(tbody)
        table.classList.remove('d-none')
    }

    function listenClick() {
        document.querySelectorAll('.detail-nilai').forEach(function (dateBox) {
            dateBox.addEventListener('click', showDetail);
        });
    }

    function showDetail(e, tmp) {
        const clickedBtn = tmp ? e : e.currentTarget;
        const idKjm = clickedBtn.getAttribute('data-id');
        const nilai = clickedBtn.getAttribute('data-nilai');
        const status = clickedBtn.getAttribute('data-status');
        const msg = clickedBtn.getAttribute('data-msg') || 'Tidak ada catatan';
        console.log('detail', status)
        // status: 0=blm dikerjakan, 1=blm selesai, 2=selesai, 3=ulangi

        if (status === '2') {
            swal.fire({
                title: "Nilai <?= $judul ?? '' ?>",
                html: '<table class="table table-sm table-borderless w-100">' +
                    '    <tr>' +
                    '        <th class="text-sm">NILAI</th>' +
                    '        <th class="text-sm text-left">Catatan Guru</th>' +
                    '    </tr>' +
                    '    <tr>' +
                    '        <td class="text-lg text-bold">' + nilai + '</td>' +
                    '        <td class="text-left">' + msg + '</td>' +
                    '    </tr>' +
                    '</table>',
                confirmButtonText: "TUTUP"
            })
        } else {
            window.location.href = base_url + 'kbm/buka' + subjudul + '/'+ idKjm
        }
    }

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
</script>