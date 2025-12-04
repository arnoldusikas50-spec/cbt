<?php

?>
<div class="content-wrapper" style="margin-top: 30px;">
    <div class="sticky">
    </div>
    <section class="content overlap p-0">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <?php
                    $cbt_setting = [];
                   $this->load->view('members/siswa/templates/top'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <div class="text-center text-red alert alert-default-primary"><strong>INFO ULANGAN/UJIAN</strong></div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="card border d-none" id="info-siswa">
                                        <!-- ikasmedia -->
                                        <div class="card-header border-bottom-0 alert-default-info"> 
                                            <center> <img class="d-flex mr-2 rounded-circle" onerror="this.src='<?= base_url('assets/img/siswa.png') ?>'" src="<?= base_url($siswa->foto) ?>" width="120" height="120" alt="foto"> </center>
                                        <br>
                                            <h6 class="text-center"><strong><?= $siswa->nama ?></strong></h6>
                                            <h6 class="text-center"><strong>[<?= $siswa->nama_kelas ?>] - [<?= $siswa->agama ?>]</strong></h6>                                            
                                        </div>
                                        <div class="card-body pt-0">
                                            <ul class="list-group list-group-unbordered"></ul>
                                        </div>
                                    </div>
                                    <div class="alert alert-default-warning d-none" id="no-sesisiswa">
                                        <div class="text-center">Anda belum terdaftar sebagai peserta ujian</div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <h6 class="text-center">
                                <div class="text-center alert alert-primary"> JADWAL PENILAIAN HARI INI<br/><?= buat_tanggal(date('D, d M Y')) ?></div> <!-- ikasmedia -->
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="jadwal-content">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card my-shadow">
                        <div class="card-header">
                            <h6 class="text-center">
                                <div class="text-center alert alert-default-danger">JADWAL PENILAIAN SEBELUMNYA / UJIAN SUSULAN<div> <!-- ikasmedia -->
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row table-responsive">
                                <table class="table table-sm" id="table-container"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(async function () {
        const data = await ajaxGet('cbtsiswa/get_cbt_siswa?id_siswa=' + <?=$siswa->id_siswa?>);
        console.log('cbtSiswa', data)
        await populateDataSiswa(data)
    })

    async function populateDataSiswa(data) {
        const siswa = data.siswa
        const listContainer = document.querySelector('.list-group');
        const arrTitle = ['No. Peserta', 'Ruang', 'Sesi', 'Dari', 'Sampai'];
        const arrSub = [
            siswa.sesisiswa?.peserta?.nomor_peserta ?? '#',
            siswa.sesisiswa?.ruang?.nama_ruang ?? '#',
            siswa.sesisiswa?.sesi?.nama_sesi ?? '#',
            siswa.sesisiswa?.sesi?.waktu_mulai?.slice(0, -3) ?? '#',
            siswa.sesisiswa?.sesi?.waktu_akhir?.slice(0, -3) ?? '#'
        ];

        // Generate list items
        arrTitle.forEach((title, index) => {
            const listItem = `
        <li class="list-group-item p-1">
            ${title}
            <span class="float-right"><b>${arrSub[index]}</b></span>
        </li>
    `;
            listContainer.innerHTML += listItem;
        });

        console.log('count', arrSub.includes('#'))
        if (arrSub.includes('#')) {
            $('#no-sesisiswa').removeClass('d-none')
        } else {
            $('#info-siswa').removeClass('d-none')
            await populateJadwalHariIni(data.jadwal_today, siswa, data.today)
            await populateJadwalSebelumnya(data.jadwal_before, siswa, data.today)
        }
    }

    async function populateJadwalHariIni(jadwals, siswa, skrg) {
        let arrJadwalSelesai = []
        const tableContainer = document.getElementById('jadwal-content')
        if (Object.keys(jadwals).length > 0) {
            let konten = '';
            for (const tanggal in jadwals) {
                const tgl = new Date(tanggal).toLocaleDateString('id-ID', {
                    year: "numeric",
                    month: "short",
                    day: "2-digit",
                })
                jadwals[tanggal].sort(sortByJam)
                let indJam = 1;
                let jadwalSebelumnya;
                for (const bank of jadwals[tanggal]) {
                    const jadwal = bank.jadwal;
                    const jamKe = jadwal.jam_ke === '0' ? '1' : jadwal.jam_ke;
                    const cbtSiswa = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === jadwal.id_jadwal)

                    const startDay = (jadwal.tgl_mulai).replaceAll('-', '');
                    const endDay = (jadwal.tgl_selesai).replaceAll('-', '');
                    const today = (skrg.split(' ')[0]).replaceAll('-', '');

                    const sesiMulai = (siswa.sesisiswa.sesi.waktu_mulai).replaceAll(':', '');
                    const sesiSampai = (siswa.sesisiswa.sesi.waktu_akhir).replaceAll(":", '');
                    const now = (skrg.split(' ')[1]).replaceAll(':', '');

                    const jamSebelumnya = jadwalSebelumnya?.jam_ke;
                    const idSebelumnya = jadwalSebelumnya?.id_jadwal
                    const cbtSebelumnya = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === idSebelumnya)
                    console.log('sebelumnya', jamKe, jamSebelumnya)
                    const selesaiSebelumnya = jamSebelumnya == '0' || jamSebelumnya === jamKe ? true : cbtSebelumnya?.selesai

                    let selesai, lanjutkan, resetWaktu, bg;
                    if (cbtSiswa) {
                        selesai = cbtSiswa.selesai !== null;
                        lanjutkan = cbtSiswa.lama_ujian !== null;
                        resetWaktu = cbtSiswa.reset_waktu;
                        if (lanjutkan && !selesai) {
                            if (indJam > 1 && !selesaiSebelumnya) {
                                bg = 'bg-gradient-danger';
                            } else {
                                bg = 'bg-gradient-warning';
                            }
                        } else {
                            bg = selesai ? 'bg-gradient-success' : 'bg-gradient-danger';
                        }
                    } else {
                        selesai = false;
                        lanjutkan = false;
                        resetWaktu = "0";
                        bg = 'bg-gradient-danger';
                    }

                    let jadwalCard = `
                <div class="jadwal-cbt col-md-6 col-lg-4">
                    <div class="card border">
                        <div class="card-header alert alert-default-primary ">
                            <div class="card-toosl text-center">
                                <!-- <b>Jam ke: ${jamKe}</b> -->
                                <b>${bank.bank_kode}</b><br>
                                <b><i class="fa fa-clock-o text-gray mr-1"></i>${jadwal.durasi_ujian} menit</b>

                            </div>
                            <!-- <div class="card-tools">
                                <b><i class="fa fa-clock-o text-gray mr-1"></i>${jadwal.durasi_ujian} menit</b>
                            </div> -->

                        </div>
                        <div class="card-body p-0">
                            <div class="small-box ${bg} mb-0">
                                <div class="inner text-center">
                                    <h6 class="crop-text-1 m-0"><b>${bank.mapel.nama_mapel}</b></h6><br>
                                    <!-- <small class="m-0">${bank.bank_kode}</small> -->
                                    <h5>[${jadwal.jenis.nama_jenis}]</h5>
                                </div>
                                
                                <!-- <div class="icon">
                                    <i class="fas fa-book-open"></i>
                                </div> -->

                                <hr style="margin-top:0; margin-bottom: 0">`;
                    if (!lanjutkan && resetWaktu === "0" && !selesai) {
                        if (today < startDay) {
                            jadwalCard += `<div class="status small-box-footer p-2"><b>HUBUNGI PENGAWAS</b></div>`;
                        } else if (today > endDay) {
                            jadwalCard += `<div class="status small-box-footer p-2"><b>WAKTU UJIAN BERAKHIR</b></div>`;
                        } else {
                            if (now < sesiMulai) {
                                jadwalCard += `<div class="status small-box-footer p-2"><b>HUBUNGI PENGAWAS</b></div>`;
                            } else if (now > sesiSampai) {
                                jadwalCard += `<div class="status small-box-footer p-2"><b>UJIAN DITUTUP</b></div>`;
                            } else {
                                if (indJam > 1 && !selesaiSebelumnya) {
                                    jadwalCard += `<button class="btn-block btn status text-white small-box-footer p-2 btn-disabled" disabled>
                                    <b>MENUNGGU</b></button>`;
                                } else {
                                    jadwalCard += `<button class="btn btn-block status text-white small-box-footer p-2" onclick="location.href='${base_url}cbtsiswa/konfirmasi/${jadwal.id_jadwal}'"><b>MULAI UJIAN</b></button>`;
                                }
                            }
                        }
                    } else if (lanjutkan && !selesai) {
                        if (indJam > 1 && !selesaiSebelumnya) {
                            jadwalCard += `<button class="btn-block btn status text-white small-box-footer p-2 btn-disabled" disabled>
                                    <b>MENUNGGU</b></button>`;
                        } else {
                            jadwalCard += `<button class="btn btn-block status small-box-footer p-2 text-white" onclick="location.href='${base_url}cbtsiswa/konfirmasi/${jadwal.id_jadwal}'"><b>LANJUTKAN</b></button>`;
                        }
                    } else {
                        jadwalCard += `<div class="btn status small-box-footer p-2"><b>SUDAH SELESAI</b></div>`;
                    }

                    jadwalCard += `</div></div></div></div>`;
                    konten += jadwalCard;
                    indJam ++;
                    jadwalSebelumnya = jadwal;
                }
            }
            tableContainer.innerHTML = konten;
        } else {
            tableContainer.innerHTML = `
            <div class="col-12 alert alert-default-warning">
                <div class="text-center">Tidak ada jadwal penilaian hari ini.</div>
            </div>`;
        }
    }

    async function populateJadwalSebelumnya(jadwals, siswa, skrg) {
        const tableContainer = document.getElementById('table-container')
        if (Object.keys(jadwals).length > 0) {
            let tableHTML = '';
            for (const tanggal in jadwals) {
                const tgl = new Date(tanggal).toLocaleDateString('id-ID', {
                    year: "numeric",
                    month: "short",
                    day: "2-digit",
                })

                tableHTML += `
            <tr>
                <td colspan="5" class="tgl-ujian text-center bg-secondary" data-tgl="${tanggal}">
                    ${tgl}
                </td>
            </tr>
            <tr>
                <th class="text-center align-middle">Jam ke</th>
                <th class="align-middle">Durasi</th>
                <th class="align-middle">Mapel</th>
                <th class="align-middle d-none d-md-block">Jenis Penilaian</th>
                <th class="align-middle">Status</th>
            </tr>
        `;
                let indJam = 1;
                let jadwalSebelumnya;
                jadwals[tanggal].sort(sortByJam)
                for (const bank of jadwals[tanggal]) {
                    const jadwal = bank.jadwal;
                    const jamKe = jadwal.jam_ke === '0' ? '1' : jadwal.jam_ke;
                    const cbtSiswa = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === jadwal.id_jadwal)

                    const startDay = (jadwal.tgl_mulai).replaceAll('-', '');
                    const endDay = (jadwal.tgl_selesai).replaceAll('-', '');
                    const today = (skrg.split(' ')[0]).replaceAll('-', '');

                    const sesiMulai = (siswa.sesisiswa.sesi.waktu_mulai).replaceAll(':', '');
                    const sesiSampai = (siswa.sesisiswa.sesi.waktu_akhir).replaceAll(":", '');
                    const now = (skrg.split(' ')[1]).replaceAll(':', '');

                    const jamSebelumnya = jadwalSebelumnya?.jam_ke;
                    const idSebelumnya = jadwalSebelumnya?.id_jadwal
                    const cbtSebelumnya = siswa.cbtsiswa.find(cbt => cbt.id_jadwal === idSebelumnya)
                    const selesaiSebelumnya = jamSebelumnya == '0' || jamSebelumnya === jamKe ? true : cbtSebelumnya?.selesai
                    //const selesaiSebelumnya = jamSebelumnya === jamKe ? true : cbtSebelumnya?.selesai

                    let selesai, lanjutkan, resetWaktu, bg;
                    if (cbtSiswa) {
                        selesai = cbtSiswa.selesai !== null;
                        lanjutkan = cbtSiswa.lama_ujian !== null;
                        resetWaktu = cbtSiswa.reset_waktu;
                        if (lanjutkan && !selesai) {
                            if (indJam > 1 && !selesaiSebelumnya) {
                                bg = 'bg-gradient-danger';
                            } else {
                                bg = 'bg-gradient-warning';
                            }
                        } else {
                            bg = selesai ? 'bg-gradient-success' : 'bg-gradient-danger';
                        }
                    } else {
                        selesai = false;
                        lanjutkan = false;
                        resetWaktu = "0";
                        bg = 'btn-danger';
                    }

                    let status = '';
                    if (!lanjutkan && resetWaktu === "0" && !selesai) {
                        if (today < startDay) {
                            status = `<button id="${jadwal.id_jadwal}" class="status-table btn btn-sm btn-disabled ${bg}" data-tgl="${jadwal.tgl_mulai}" data-jamke="${jadwal.jam_ke}">
                        <b>HUBUNGI PENGAWAS</b>
                    </button>`;
                        } else if (today > endDay) {
                            status = `<button id="${jadwal.id_jadwal}" class="status-table btn btn-sm btn-disabled ${bg}" data-tgl="${jadwal.tgl_mulai}" data-jamke="${jadwal.jam_ke}">
                        <b>WAKTU UJIAN BERAKHIR</b>
                    </button>`;
                        } else {
                            if (now < sesiMulai) {
                                status = `<button id="${jadwal.id_jadwal}" class="status-table btn btn-sm btn-disabled ${bg}" data-tgl="${jadwal.tgl_mulai}" data-jamke="${jadwal.jam_ke}">
                            <b>${siswa.sesisiswa.sesi.nama_sesi?.toUpperCase() ?? ''} HUBUNGI PENGAWAS</b>
                        </button>`;
                            } else if (now > sesiSampai) {
                                status = `<button id="${jadwal.id_jadwal}" class="status-table btn btn-sm btn-disabled ${bg}" data-tgl="${jadwal.tgl_mulai}" data-jamke="${jadwal.jam_ke}">
                            <b>${siswa.sesisiswa.sesi.nama_sesi?.toUpperCase() ?? ''} UJIAN DITUTUP</b>
                        </button>`;
                            } else {
                                if (indJam > 1 && !selesaiSebelumnya) {
                                    status = `<button id="${jadwal.id_jadwal}" class="status-table btn-disabled ${bg}" disabled>
                                    <b>MENUNGGU</b></button>`;
                                } else {
                                    status = `<button id="${jadwal.id_jadwal}" class="status-table btn btn-sm ${bg}" onclick="location.href='${base_url}cbtsiswa/konfirmasi/${jadwal.id_jadwal}'">
                                <b>MULAI REMEDIAL</b></button>`;
                                }
                            }
                        }
                    } else if (lanjutkan && !selesai) {
                        if (indJam > 1 && !selesaiSebelumnya) {
                            status = `<button id="${jadwal.id_jadwal}" class="status-table btn-disabled ${bg}" disabled><b>MENUNGGU</b></button>`;
                        } else {
                            status = `<button id="${jadwal.id_jadwal}" class="status-table btn btn-sm ${bg}" onclick="location.href='${base_url}cbtsiswa/konfirmasi/${jadwal.id_jadwal}'"><b>LANJUTKAN</b></button>`;
                        }
                    } else {
                        status = `<button id="${jadwal.id_jadwal}" class="status-table btn btn-sm btn-disabled ${bg}" data-tgl="${jadwal.tgl_mulai}" data-jamke="${jadwal.jam_ke}"><b>SUDAH SELESAI</b></button>`;
                    }

                    tableHTML += `
                <tr>
                    <td class="text-center">${jamKe}</td>
                    <td class="text-left">${jadwal.durasi_ujian} menit</td>
                    <td class="align-middle">${bank.mapel.nama_mapel}
                        <br /><small>${bank.bank_kode}</small>
                        <br /><small class="d-block d-md-none">${jadwal.jenis.nama_jenis}</small>
                    </td>
                    <td class="d-none d-md-block">${jadwal.jenis.nama_jenis}</td>
                    <td>${status}</td>
                </tr>`;

                    indJam ++;
                    jadwalSebelumnya = jadwal;
                }
            }
            tableContainer.innerHTML = tableHTML;
        } else {
            tableContainer.innerHTML = `
            <div class="col-12 alert alert-default-warning">
                <div class="text-center">Tidak ada jadwal penilaian sebelumnya.</div>
            </div>`;
        }
    }

    function sortByJam(a, b) {
        var a_jam = a.jadwal.jam_ke;
        var b_jam = b.jadwal.jam_ke;
        return ((a_jam < b_jam) ? -1 : ((a_jam > b_jam) ? 1 : 0));
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
</script>