<?php

?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="margin-top: 30px;">
    <!-- Main content -->
    <div class="sticky">
    </div>
    <section class="content overlap p-0">
        <div class="container">
           <!-- <?php $this->load->view('members/siswa/templates/top'); ?> --> <!-- ikasmedia -->
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="card card-primary" style="min-height: 200px">
                        <div class="card-header">
                            <div class="card-title text-white">
                                NILAI HASIL MATERI
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="list-nilai-materi" class="table-responsive">
                                <?php
                                if (count($nilai_materi) > 0):?>
                                    <table class="table table-hover w-100" id="table-nilai-materi" style="line-height: 1">
                                        <thead>
                                        <tr>
                                            <th class="text-center">NO</th>
                                            <th>Materi</th>
                                            <th>Tanggal</th>
                                            <th class="text-center">Nilai</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($nilai_materi as $nil) :
                                            ?>
                                            <tr onclick="showDialog(this)" data-text="<?= $nil->catatan ?>">
                                                <td class="text-center"><?= $no ?></td>
                                                <td>
                                                    <?= $nil->kode ?>
                                                    <br>
                                                    <small>
                                                        <?= $nil->kode_materi ?>
                                                    </small>
                                                    <br>
                                                    <small>
                                                        <?= $nil->judul_materi ?>
                                                    </small>
                                                </td>
                                                <td><?= singkat_tanggal(date('D, d M Y', strtotime($nil->jadwal_materi ?? ''))) ?></td>
                                                <td class="text-center"><b><?= $nil->nilai == 0 ? '*' : ($nil->nilai ?? '*') ?></b></td>
                                            </tr>
                                            <?php $no++; endforeach; ?>
                                        </tbody>
                                    </table>
                                <hr />
                                <p>(*) Belum dinilai</p>
                                <?php else: ?>
                                    <p>Belum ada nilai.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card card-red" style="min-height: 200px">
                        <div class="card-header">
                            <div class="card-title text-white">
                                NILAI HASIL TUGAS
                            </div>
                        </div>
                        <div class="card-body">
                            <div id='list-nilai-tugas' class="table-responsive">
                                <?php
                                if (count($nilai_tugas) > 0): ?>
                                    <table class="table table-hover w-100" id="table-nilai-tugas" style="line-height: 1">
                                        <thead>
                                        <tr>
                                            <th class="text-center">NO</th>
                                            <th>Tugas</th>
                                            <th>Tanggal</th>
                                            <th class="text-center">Nilai</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($nilai_tugas as $nil) :
                                            ?>
                                            <tr onclick="showDialog(this)" data-text="<?= $nil->catatan ?>">
                                                <td class="text-center"><?= $no ?></td>
                                                <td>
                                                    <?= $nil->kode ?>
                                                    <br>
                                                    <small>
                                                        <?= $nil->kode_materi ?>
                                                    </small>
                                                    <br>
                                                    <small>
                                                        <?= $nil->judul_materi ?>
                                                    </small>
                                                </td>
                                                <td><?= singkat_tanggal(date('D, d M Y', strtotime($nil->jadwal_materi ?? ''))) ?></td>
                                                <td class="text-center"><b><?= $nil->nilai ?? '*' ?></b></td>
                                            </tr>
                                            <?php $no++; endforeach; ?>
                                        </tbody>
                                    </table>
                                    <hr />
                                    <p>(*) Belum dinilai</p>
                                <?php else: ?>
                                    <p>Belum ada nilai.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card card-purple">
                        <div class="card-header">
                            <div class="card-title text-white">
                                NILAI HASIL ULANGAN/UJIAN
                            </div>
                        </div>
                        <div class="card-body">
                            <div id='list-cbt' class="table-responsive d-none">
                                <table class="table table-sm w-100" id="table-nilai-ujian">
                                    <thead>
                                    <tr a class="alert alert-default-success">
                                        <th class="text-center align-middle">NO</th>
                                        <th>Jenis Penilaian</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Kode Penilaian</th>
                                        <th class="text-center">Nilai</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                                <hr>
                                <span><strong><i>Catatan:</i></strong></span>
                                <br>
                                <small a class="alert-default-warning">
                                    <!-- <b>(-)</b> Belum dikerjakan
                                    <br><b>(*)</b> Menunggu hasil koreksi -->
                                    <br><b>(**)</b> Hubungi Guru Pengampu jika ingin mengetahui nilai
                                </small>
                            </div>
                            <div class="d-none" id="empty">
                                <p>Belum ada jadwal ulangan/ujian.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="detail-nilai" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Detail Hasil Ujian</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="w-100 text-xs">
                    <tr>
                        <td>Tgl. Pelaksanaan</td>
                        <td id="jwaktu">:</td>
                    </tr>
                    <tr>
                        <td>Mulai</td>
                        <td id="jmulai">:</td>
                    </tr>
                    <tr>
                        <td>Selesai</td>
                        <td id="jselesai">:</td>
                    </tr>
                    <tr>
                        <td>Waktu pengerjaan</td>
                        <td id="jdurasi">:</td>
                    </tr>
                </table>
                <hr>
                <div id="alert" class="alert alert-default-warning align-content-center" role="alert">
                    Hubungi guru pengampu jika ingin mengetahui nilai.
                </div>
                <table id="table-detail-soal" class="w-100 table-striped text-xs">
                    <tr>
                        <th class="border-bottom"></th>
                        <th class="text-center border-bottom">JML. SOAL</th>
                        <th class="text-center border-bottom">BENAR</th>
                        <th class="text-center border-bottom">SKOR</th>
                    </tr>
                    <tr id="tpg">
                        <td>Soal Pilihan Ganda</td>
                        <td class="text-center" id="jpg"></td>
                        <td class="text-center" id="bpg"></td>
                        <td class="text-center" id="spg"></td>
                    </tr>
                    <tr id="tpg2">
                        <td>Soal PG Kompleks</td>
                        <td class="text-center" id="jpg2"></td>
                        <td class="text-center" id="bpg2"></td>
                        <td class="text-center" id="spg2"></td>
                    </tr>
                    <tr id="tjod">
                        <td>Soal Menjodohkan</td>
                        <td class="text-center" id="jjod"></td>
                        <td class="text-center" id="bjod"></td>
                        <td class="text-center" id="sjod"></td>
                    </tr>
                    <tr id="tis">
                        <td>Soal Isian</td>
                        <td class="text-center" id="jis"></td>
                        <td class="text-center" id="bis"></td>
                        <td class="text-center" id="sis"></td>
                    </tr>
                    <tr id="tes">
                        <td>Soal Uraian</td>
                        <td class="text-center" id="jes"></td>
                        <td class="text-center" id="bes"></td>
                        <td class="text-center" id="ses"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center border-top"><b>TOTAL SKOR</b></td>
                        <td class="text-center border-top" id="jskor"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    var arrbulan = ['', 'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    var arrhari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
    let jadwals = [];

    async function ajaxGet(url, data) {
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                $.ajax({
                    url: base_url + url,
                    type: "GET",
                    data: data,
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

    function populateTable(data) {
        $('#list-cbt').addClass('d-none');
        $('#empty').removeClass('d-none');

        if (!data.all_jadwal) return

        jadwals = data.all_jadwal.filter(jadwal =>
            !(jadwal.bank?.soal_agama !== '-' && jadwal.bank?.soal_agama !== '0' && jadwal.bank?.soal_agama !== data.siswa?.agama)
            && data.bank_kelas[jadwal.id_jadwal]?.includes(data?.siswa?.id_kelas?.toString())
        );

        const tableBody = document.querySelector("#table-nilai-ujian tbody");
        if (jadwals.length > 0) {
            $('#list-cbt').removeClass('d-none');
            $('#empty').addClass('d-none');
            let no = 1;

            jadwals.forEach((j) => {
                const log = j.cbtsiswa?.length > 0 ? j?.cbtsiswa[0] : null;
                const blmMulai = log == null || !log?.mulai;
                const blmSelesai = log == null || !log?.selesai;

                let detail = ''
                if (blmMulai) {
                    detail = '<button type="button" class="btn btn-sm btn-danger"> Belum Ujian </button>';
                } else if (blmSelesai) {
                    detail = '<button type="button" class="btn btn-sm btn-warning"> Belum Selesai </button>';
                } else {
                    if (j.hasil_tampil === "0") {
                        detail = '**'
                    } else {
                        detail = `<button type="button"
                                    data-jadwal="${j.id_jadwal}"
                                    data-siswa="${data.siswa.id_siswa}"
                                    onclick="getNilai(this)"
                                    class="btn btn-sm ${blmSelesai ? 'btn-danger' : 'btn-primary'} ${blmSelesai ? 'btn-disabled' : ''}" ${blmSelesai ? 'disabled' : ''}>
                                        Detail
                                 </button>`
                    }
                }
                const row = `
      <tr>
        <td class="text-center">${no}</td>
        <td>${j.jenis.nama_jenis}
          <br><small>${formatDate(j.tgl_mulai)}</small>
        </td>
        <td>${j.bank?.mapel.kode}</td>
        <td>${j.bank?.bank_kode}</td>
        <td class="text-center">${detail}</td>
      </tr>
    `;
                tableBody.innerHTML += row;
                no++;
            });
            $('#table-nilai-ujian').DataTable();
        } else {
            tableBody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center">
        <div class="alert align-content-center alert-default-warning" role="alert">
          Belum ada jadwal ulangan/ujian
        </div>
      </td>
    </tr>
  `;
        }
    }

    function formatDate(dateString) {
        const options = { weekday: "short", day: "2-digit", month: "short", year: "numeric" };
        return new Date(dateString).toLocaleDateString("id-ID", options);
    }

    $(document).ready(async function () {
        $('#table-nilai-materi').DataTable();
        $('#table-nilai-tugas').DataTable();

        const response = await ajaxGet('kbm/get_all_nilai/')
        console.log('res', response)
        populateTable(response)
    });

    async function getNilai(e) {
        const jadwalId = $(e).data('jadwal');
        const siswa = $(e).data('siswa');
        console.log('jadwalId', jadwalId, 'siswa', siswa)
        if (jadwalId && siswa) {
            swal.fire({
                title: "Nilai Detail",
                text: "Memuat nilai ...",
                allowEscapeKey: false,
                allowOutsideClick: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });

            const response = await ajaxGet('kbm/get_nilai_jadwal/', {
                jadwal: jadwalId,
                siswa: siswa,
            });

            if (response.success) {
                setTimeout(function () {
                    swal.hideLoading();
                    setDialog(response)
                }, 500);
            } else {
                swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message || 'Gagal mendapatkan data nilai.',
                });
            }

        }
    }

    function setDialog(response) {
        const jadwal = response.jadwal;
        const id = jadwal.id_jadwal;
        const tampilNilai = jadwal?.hasil_tampil === '1';
        const dikoreksi = jadwal?.sesisiswa?.nilai_akhir?.dikoreksi == '1';

        var dur = jadwal?.sesisiswa?.durasi
        var skor = jadwal?.sesisiswa?.nilai_akhir;

        var sp = jadwal.tgl_mulai?.split('-');
        var d = new Date(sp[0], sp[1] - 1, sp[2]);
        const tgl_jadwal = ': ' + arrhari[d.getDay()] + ', ' + sp[2] + ' ' + arrbulan[parseInt(sp[1])] + ' ' + sp[0];

        const blmSelesai = !dur.selesai
        let jam_mulai = ': --:--';
        let jam_selesai = ': --:--';
        let durasi = ': --';
        if (!blmSelesai) {
            var mnt = dur.mulai?.split(' ')[1];
            const m = mnt?.split(':')
            jam_mulai = ': ' + (m[0] || '--') + ':' + (m[1] || '--');

            var scn = dur.selesai?.split(' ')[1];
            console.log('sls', scn)
            const s = scn?.split(':')
            jam_selesai = ': ' + (s[0] || '--') + ':' + (s[1] || '--');
            durasi = ': ' + dur.durasi;
        }

        const $status = $('.swal2-content');
        let konten = '';
        if (tampilNilai) {
            const showPg = parseInt(jadwal.bank?.tampil_pg || '0') === 0 ? 'd-none' : '';
            const jmlPg = jadwal.bank?.tampil_pg || '0';
            const benarPg = skor.pg_benar || '0';
            const skorPg = skor.skor_pg?.skor_total || '0';

            const showKompleks = parseInt(jadwal.bank?.tampil_kompleks || '0') === 0 ? 'd-none' : '';
            const jmlKompleks = jadwal.bank?.tampil_kompleks || '0';
            const benarKompleks = skor.skor_kompleks?.jml_benar || '0';
            const skorKompleks = skor.skor_kompleks?.skor_asli || '0';

            const showJodohkan = parseInt(jadwal.bank?.tampil_jodohkan || '0') === 0 ? 'd-none' : '';
            const jmlJodohkan = jadwal.bank?.tampil_jodohkan || '0';
            const benarJodohkan = skor.skor_jodohkan?.jml_benar || '0';
            const skorJodohkan = skor.skor_jodohkan?.skor_asli || '0';

            const showIsian = parseInt(jadwal.bank?.tampil_isian || '0') === 0 ? 'd-none' : '';
            const jmlIsian = jadwal.bank?.tampil_isian || '0';
            const benarIsian = skor.skor_isian?.jml_benar || '0';
            const skorIsian = dikoreksi ? (skor.skor_isian?.skor_asli || '0') : 'sedang<br>dikoreksi';

            const showEsai = parseInt(jadwal.bank?.tampil_esai || '0') === 0 ? 'd-none' : '';
            const jmlEsai = jadwal.bank?.tampil_esai || '0';
            //const benarEsai = skor.benar_esai || '0';
            const skorEsai = dikoreksi ? (skor.skor_essai?.skor_asli || '0') : 'sedang<br>dikoreksi';
            const jmlSkor = skor.skor_asli || '0';

            konten = `<div class="text-left">
                <table class="w-100 text-xs">
                    <tr>
                        <td>Tgl. Pelaksanaan</td>
                        <td class="text-bold">${tgl_jadwal}</td>
                    </tr>
                    <tr>
                        <td>Mulai</td>
                        <td class="text-bold">${jam_mulai}</td>
                    </tr>
                    <tr>
                        <td>Selesai</td>
                        <td class="text-bold">${jam_selesai}</td>
                    </tr>
                    <tr>
                        <td>Waktu pengerjaan</td>
                        <td class="text-bold">${durasi}</td>
                    </tr>
                </table>
                <hr>
                <table class="w-100 table-striped text-xs">
                    <tr>
                        <th class="border-bottom"></th>
                        <th class="text-center border-bottom">JML. SOAL</th>
                        <th class="text-center border-bottom">BENAR</th>
                        <th class="text-center border-bottom">SKOR</th>
                    </tr>
                    <tr class="${showPg}">
                        <td>Soal Pilihan Ganda</td>
                        <td class="text-center">${jmlPg}</td>
                        <td class="text-center">${benarPg}</td>
                        <td class="text-center text-bold">${skorPg}</td>
                    </tr>
                    <tr class="${showKompleks}">
                        <td>Soal PG Kompleks</td>
                        <td class="text-center">${jmlKompleks}</td>
                        <td class="text-center">${benarKompleks}</td>
                        <td class="text-center text-bold">${skorKompleks}</td>
                    </tr>
                    <tr class="${showJodohkan}">
                        <td>Soal Menjodohkan</td>
                        <td class="text-center">${jmlJodohkan}</td>
                        <td class="text-center">${benarJodohkan}</td>
                        <td class="text-center text-bold">${skorJodohkan}</td>
                    </tr>
                    <tr class="${showIsian}">
                        <td>Soal Isian</td>
                        <td class="text-center">${jmlIsian}</td>
                        <td class="text-center">${benarIsian}</td>
                        <td class="text-center text-bold">${skorIsian}</td>
                    </tr>
                    <tr class="${showEsai}">
                        <td>Soal Uraian</td>
                        <td class="text-center">${jmlEsai}</td>
                        <td class="text-center">Penilaian Guru</td>
                        <td class="text-center text-bold">${skorEsai}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-center border-top text-bold">TOTAL SKOR</td>
                        <td class="text-center border-top text-md text-bold">${jmlSkor}</td>
                    </tr>
                </table>
            </div>`;
        } else {
            konten = `<div class="alert alert-default-warning align-content-center" role="alert">
                    Hubungi guru pengampu jika ingin mengetahui nilai.
                </div>`;
        }
        $status.html(konten)
    }

    function showDialog(tr) {
        swal.fire({
            title: "Catatan Guru",
            html: '<div class="w-100 border p-4">' + $(tr).data('text') + '</div>',
            confirmButtonText: "TUTUP"
        })
    }
</script>
