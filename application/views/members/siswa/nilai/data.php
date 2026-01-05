<style>
    .no-close .ui-dialog-titlebar-close {
        display: none;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper bg-transparent">
    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <!-- Floating Header Section -->
            <div class="main-content-overlap">
                <div class="row justify-content-center">
                    <div class="col-lg-10">

                        <!-- Main Card -->
                        <div class="card card-modern border-0 shadow-lg rounded-4 overflow-hidden">
                            <!-- Header Decoration -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none"
                                style="z-index: 0;">
                                <div style="
                                    position: absolute;
                                    top: -10%;
                                    right: -5%;
                                    width: 400px;
                                    height: 400px;
                                    background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, rgba(255,255,255,0) 70%);
                                    border-radius: 50%;
                                "></div>
                            </div>

                            <div class="card-body p-4 position-relative z-1 ">
                                <!-- Watermark -->
                                <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 100%;
                                    background-color: #e0e7ff;
                                    -webkit-mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    mask-image: url('<?= base_url('assets/img/logo-card.png') ?>');
                                    -webkit-mask-size: 50%;
                                    mask-size: 50%;
                                    -webkit-mask-repeat: no-repeat;
                                    mask-repeat: no-repeat;
                                    -webkit-mask-position: center;
                                    mask-position: center;
                                    opacity: 0.05;
                                    z-index: -1;
                                    pointer-events: none;
                                "></div>

                                <!-- Header Area -->
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-4 me-3">
                                            <i class="bi bi-award"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-1">Daftar Nilai</h5>
                                            <div class="text-muted small">Rekapitulasi hasil belajar anda</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="<?= base_url('dashboard') ?>"
                                            class="btn btn-primary border rounded-pill px-4 shadow-sm fw-bold d-flex align-items-center hover-scale">
                                            <i class="bi bi-arrow-left-circle-fill me-2 text-primary"></i> Kembali
                                        </a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <div class="card-title text-white">
                                                        NILAI HASIL MATERI
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div id="list-nilai-materi" class="table-responsive">
                                                        <?php
                                                        if (count($nilai_materi) > 0): ?>
                                                            <table class="table table-hover w-100" id="table-nilai-materi"
                                                                style="line-height: 1">
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
                                                                    foreach ($nilai_materi as $nil):
                                                                        ?>
                                                                        <tr onclick="showDialog(this)"
                                                                            data-text="<?= $nil->catatan ?>">
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
                                                                            <td><?= singkat_tanggal(date('D, d M Y', strtotime($nil->jadwal_materi ?? ''))) ?>
                                                                            </td>
                                                                            <td class="text-center text-lg">
                                                                                <b><?= $nil->nilai ?></b>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $no++; endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        <?php else: ?>
                                                            <p>Belum ada nilai.</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="card card-red">
                                                <div class="card-header">
                                                    <div class="card-title text-white">
                                                        NILAI HASIL TUGAS
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div id='list-nilai-tugas'>
                                                        <?php if (count($nilai_tugas) > 0): ?>
                                                            <table class="table table-hover w-100" id="table-nilai-tugas"
                                                                style="line-height: 1">
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
                                                                    foreach ($nilai_tugas as $nil):
                                                                        ?>
                                                                        <tr onclick="showDialog(this)"
                                                                            data-text="<?= $nil->catatan ?>">
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
                                                                            <td><?= singkat_tanggal(date('D, d M Y', strtotime($nil->jadwal_materi ?? ''))) ?>
                                                                            </td>
                                                                            <td class="text-center text-lg">
                                                                                <b><?= $nil->nilai ?></b>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $no++; endforeach; ?>
                                                                </tbody>
                                                            </table>
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
                                                    <div id='list-cbt'>
                                                        <table class="table w-100" id="table-nilai-ujian">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-center align-middle">NO</th>
                                                                    <th>Jenis Penilaian</th>
                                                                    <th>Mata Pelajaran</th>
                                                                    <th>Kode Penilaian</th>
                                                                    <th class="text-center">Nilai</th>
                                                                    <th class="text-center">Detail</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                if (count($jadwal) > 0):
                                                                    $no = 1;
                                                                    foreach ($jadwal as $j):
                                                                        $hanya_pg = $j->tampil_pg > 0 && $j->tampil_kompleks == 0 && $j->tampil_jodohkan == 0 && $j->tampil_isian == 0 && $j->tampil_esai == 0;
                                                                        $total = !$hanya_pg && isset($skor[$j->id_jadwal]) && isset($skor[$j->id_jadwal]->dikoreksi) && $skor[$j->id_jadwal]->dikoreksi == 0 ? '*' :
                                                                            ($j->hasil_tampil == '0' ? '**' : (isset($skor[$j->id_jadwal]) ? $skor[$j->id_jadwal]->skor_total : ''));
                                                                        ?>
                                                                        <tr>
                                                                            <td class="text-center"><?= $no ?></td>
                                                                            <td><?= $j->nama_jenis ?>
                                                                                <br><small><?= buat_tanggal(date('D, d M Y', strtotime($j->tgl_mulai ?? ''))) ?></small>
                                                                            </td>
                                                                            <td><?= $j->kode ?></td>
                                                                            <td><?= $j->bank_kode ?></td>
                                                                            <td class="text-center"><?= $total ?></td>
                                                                            <td class="text-center">
                                                                                <button type="button"
                                                                                    data-koreksi="<?= isset($skor[$j->id_jadwal]->dikoreksi) ? $skor[$j->id_jadwal]->dikoreksi : '0' ?>"
                                                                                    data-tampil="<?= $j->hasil_tampil ?>"
                                                                                    data-id="<?= $j->id_jadwal ?>"
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#detail-nilai"
                                                                                    class="btn btn-sm btn-primary">
                                                                                    Detail
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $no++; endforeach; else: ?>
                                                                    <tr>
                                                                        <td colspan="6" class="text-center">
                                                                            <div class="alert align-content-center alert-default-warning"
                                                                                role="alert">
                                                                                Belum ada jadwal ulangan/ujian
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                        <hr>
                                                        <span><i>Catatan:</i></span>
                                                        <br>
                                                        <small>
                                                            <b>(-)</b> Belum dikerjakan
                                                            <br><b>(*)</b> Menunggu hasil koreksi
                                                            <br><b>(**)</b> Hubungi Guru Pengampu jika ingin mengetahui
                                                            nilai
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr class="mt-auto">
<div class="col-md-12 text-center mt-3 pb-4">
    <div class="copyright"><small>COPYRIGHT &copy; <?= date('Y'); ?> | IKASMEDIA</small> </div>
</div>
<!-- End Content Wrapper -->

<div class="modal fade" id="detail-nilai" tabindex="-1" role="dialog" aria-labelledby="createModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Detail Hasil Ujian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    var arrbulan = ['', 'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    var arrhari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];
    var skores = JSON.parse('<?= json_encode($skor) ?>');
    var durasies = JSON.parse('<?= json_encode($durasi) ?>');
    var jadwals = JSON.parse('<?= json_encode($jadwal) ?>');

    $(document).ready(function () {
        $('#table-nilai-materi').DataTable();
        $('#table-nilai-tugas').DataTable();
        $('#table-nilai-ujian').DataTable();

        // Updated Modern Modal Content
        var konten = '';
        var sp = jadwal?.tgl_mulai?.split('-');
        var d = new Date(sp[0], sp[1] - 1, sp[2]);
        var tgl_jadwal = (arrhari[d.getDay()] || '') + ', ' + sp[2] + ' ' + (arrbulan[parseInt(sp[1])] || '') + ' ' + sp[0];

        let jam_mulai = '--:--';
        let jam_selesai = '--:--';
        let durasi = '--';

        if (dur && dur.mulai != null && dur.selesai != null) {
            var m = dur?.mulai?.split(' ')[1]?.split(':');
            jam_mulai = m[0] + ':' + m[1];

            var s = dur?.selesai?.split(' ')[1]?.split(':');
            jam_selesai = s[0] + ':' + s[1];

            if (dur.lama_ujian != null) {
                var l = dur?.lama_ujian?.split(':');
                if (l[0] !== '00') {
                    durasi = parseInt(l[0]) + ' jam ';
                } else {
                    durasi = '';
                }
                durasi += parseInt(l[1]) + ' menit';
            } else {
                var old_date_obj = new Date(dur.mulai).getTime();
                var new_date_obj = new Date(dur.selesai).getTime();
                let dr = (new_date_obj - old_date_obj) / 1000;
                dr /= 60;
                durasi = Math.round(dr) + ' menit';
            }
        }

        if (tampilNilai == '1') {
            const showPg = parseInt(jadwal.tampil_pg || '0') === 0 ? 'd-none' : '';
            const jmlPg = jadwal.tampil_pg || '0';
            const benarPg = skor.benar_pg || '0';
            const skorPg = skor.skor_pg || '0';

            const showKompleks = parseInt(jadwal.tampil_kompleks || '0') === 0 ? 'd-none' : '';
            const jmlKompleks = jadwal.tampil_kompleks || '0';
            const benarKompleks = skor.benar_kompleks || '0';
            const skorKompleks = skor.skor_kompleks || '0';

            const showJodohkan = parseInt(jadwal.tampil_jodohkan || '0') === 0 ? 'd-none' : '';
            const jmlJodohkan = jadwal.tampil_jodohkan || '0';
            const benarJodohkan = skor.benar_jodohkan || '0';
            const skorJodohkan = skor.skor_jodohkan || '0';

            const showIsian = parseInt(jadwal.tampil_isian || '0') === 0 ? 'd-none' : '';
            const jmlIsian = jadwal.tampil_isian || '0';
            const benarIsian = skor.benar_isian || '0';
            const skorIsian = dikoreksi ? (skor.skor_isian || '0') : 'sedang<br>dikoreksi';

            const showEsai = parseInt(jadwal.tampil_esai || '0') === 0 ? 'd-none' : '';
            const jmlEsai = jadwal.tampil_esai || '0';
            const skorEsai = dikoreksi ? (skor.skor_essai || '0') : 'sedang<br>dikoreksi';
            const jmlSkor = skor.skor_total || '0';

            konten = `<div class="text-start">
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-12">
                                                            <div class="d-flex align-items-center p-3 bg-light rounded-3 border">
                                                                <div class="icon-box-sm bg-white rounded-circle shadow-sm text-primary me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="bi bi-calendar-event-fill"></i>
                                                                </div>
                                                                <div>
                                                                    <small class="text-muted d-block text-xs text-uppercase fw-bold">Tanggal Pelaksanaan</small>
                                                                    <span class="fw-bold text-dark">${tgl_jadwal}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 border rounded-3 text-center bg-white h-100">
                                                                <small class="text-muted d-block text-xs mb-1">Mulai</small>
                                                                <span class="fw-bold fs-6 font-monospace text-dark">${jam_mulai}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 border rounded-3 text-center bg-white h-100">
                                                                <small class="text-muted d-block text-xs mb-1">Selesai</small>
                                                                <span class="fw-bold fs-6 font-monospace text-dark">${jam_selesai}</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="p-2 border rounded-3 text-center bg-light-success h-100">
                                                                <small class="text-success d-block text-xs mb-1">Durasi</small>
                                                                <span class="fw-bold fs-6 font-monospace text-success">${durasi}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive rounded-3 border-top">
                                                        <table class="table table-striped align-middle mb-0 text-sm w-100">
                                                            <thead class="bg-light text-secondary">
                                                                <tr>
                                                                    <th class="py-3 px-3 border-bottom-0 text-uppercase text-xs font-weight-bold">Tipe Soal</th>
                                                                    <th class="py-3 text-center border-bottom-0 text-uppercase text-xs font-weight-bold">Jml</th>
                                                                    <th class="py-3 text-center border-bottom-0 text-uppercase text-xs font-weight-bold">Benar</th>
                                                                    <th class="py-3 text-center border-bottom-0 text-uppercase text-xs font-weight-bold text-primary">Skor</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="border-top-0">
                                                                <tr class="${showPg}">
                                                                    <td class="px-3">Pilihan Ganda</td>
                                                                    <td class="text-center">${jmlPg}</td>
                                                                    <td class="text-center fw-bold text-success">${benarPg}</td>
                                                                    <td class="text-center fw-bold text-primary">${skorPg}</td>
                                                                </tr>
                                                                <tr class="${showKompleks}">
                                                                    <td class="px-3">PG Kompleks</td>
                                                                    <td class="text-center">${jmlKompleks}</td>
                                                                    <td class="text-center fw-bold text-success">${benarKompleks}</td>
                                                                    <td class="text-center fw-bold text-primary">${skorKompleks}</td>
                                                                </tr>
                                                                <tr class="${showJodohkan}">
                                                                    <td class="px-3">Menjodohkan</td>
                                                                    <td class="text-center">${jmlJodohkan}</td>
                                                                    <td class="text-center fw-bold text-success">${benarJodohkan}</td>
                                                                    <td class="text-center fw-bold text-primary">${skorJodohkan}</td>
                                                                </tr>
                                                                <tr class="${showIsian}">
                                                                    <td class="px-3">Isian Singkat</td>
                                                                    <td class="text-center">${jmlIsian}</td>
                                                                    <td class="text-center fw-bold text-success">${benarIsian}</td>
                                                                    <td class="text-center fw-bold text-primary">${skorIsian}</td>
                                                                </tr>
                                                                <tr class="${showEsai}">
                                                                    <td class="px-3">Uraian / Esai</td>
                                                                    <td class="text-center">${jmlEsai}</td>
                                                                    <td class="text-center text-muted text-xs">Correction</td>
                                                                    <td class="text-center fw-bold text-primary">${skorEsai}</td>
                                                                </tr>
                                                            </tbody>
                                                            <tfoot class="bg-primary bg-opacity-10">
                                                                <tr>
                                                                    <td colspan="3" class="text-end fw-bold py-3 text-primary text-uppercase text-xs">Total Perolehan Nilai</td>
                                                                    <td class="text-center fw-bold text-primary fs-5 py-3">${jmlSkor}</td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>`;
        } else {
            konten = `<div class="alert alert-default-warning align-content-center" role="alert">
                                                    Hubungi guru pengampu jika ingin mengetahui nilai.
                                                </div>`;
        }

        // Inject content
        var modalBody = $(this).find('.modal-body');
        modalBody.html(konten);

        console.log('jadwal', jadwals)
    });

    function showDialog(tr) {
        Swal.fire({
            title: "Catatan Guru",
            html: '<div class="w-100 border p-4">' + $(tr).data('text') + '</div>',
            confirmButtonText: "TUTUP"
        })
    }
</script>