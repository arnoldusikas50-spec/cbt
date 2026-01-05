<?php
// Set default value for alokasi_jadwal if not passed from controller
$alokasi_jadwal = $alokasi_jadwal ?? [];
?>

<main class="app-main pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title">Jadwal Ujian/Ulangan</h6>
                    <div class="card-tools ms-auto">
                        <a href="<?= base_url('cbtjadwal?mode=' . $mode) ?>" type="button" onclick=""
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </a>
                        <a href="<?= base_url('cbtjadwal/add/0') ?>" type="button"
                            class="btn btn-primary btn-sm ms-1 shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
                        </a>
                        <div class="btn-group">
                            <?php $activeList = $mode == '1' ? ' active' : ''; ?>
                            <?php $activeGrid = $mode == '2' ? ' active' : ''; ?>
                            <a href="<?= base_url('cbtjadwal?mode=1') ?>" type="button"
                                class="btn btn-sm btn-primary<?= $activeList ?>"><i class="bi bi-list"></i></a>
                            <a href="<?= base_url('cbtjadwal?mode=2') ?>" type="button"
                                class="btn btn-sm btn-primary<?= $activeGrid ?>"><i class="bi bi-grid-fill"></i></a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <a class="badge bg-danger text-white">Kode Warna Jadwal:</a>
                    <table class="w-100">
                        <tr>
                            <td class="p-1" style="width: 20px"><i class="bi bi-square-fill text-secondary"></i></td>
                            <td class="p-1">Tidak aktif</td>
                            <td class="p-1" style="width: 20px"><i class="bi bi-square-fill text-primary"></i></td>
                            <td class="p-1">Sedang dilaksanakan</td>
                        </tr>
                        <tr>
                            <td class="p-1"><i class="bi bi-square-fill text-warning"></i></td>
                            <td class="p-1">Belum ada Siswa Login dan Mengerjakan</td>
                            <td class="p-1"><i class="bi bi-square-fill text-danger"></i></td>
                            <td class="p-1">Selesai, belum rekap nilai</td>
                        </tr>
                        <tr>
                            <td class="p-1"><i class="bi bi-square-fill text-info"></i></td>
                            <td class="p-1">Belum waktu jadwal</td>
                            <td class="p-1"><i class="bi bi-square-fill text-success"></i></td>
                            <td class="p-1">Selesai, sudah rekap nilai</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row p-2" id="row-filter">
                <div class="col-12 col-md-6">
                    <table class="w-100">
                        <tr>
                            <td class="pe-2 text-bold">Filter:</td>
                            <td>
                                <?php echo form_dropdown(
                                    'f',
                                    $filters,
                                    $id_filter,
                                    'id="filter" class="form-control select2"'
                                ); ?>
                            </td>
                            <td id="select-guru" class="d-none">
                                <?php echo form_dropdown(
                                    'guru',
                                    $gurus,
                                    $id_guru,
                                    'id="guru" class="sel form-control select2"'
                                ); ?>
                            </td>
                            <td id="select-mapel" class="d-none">
                                <?php echo form_dropdown(
                                    'mapel',
                                    $mapels,
                                    $id_mapel,
                                    'id="mapel" class="sel form-control select2"'
                                ); ?>
                            </td>
                            <td id="select-level" class="d-none">
                                <?php echo form_dropdown(
                                    'level',
                                    $levels,
                                    $id_level,
                                    'id="level" class="sel form-control select2"'
                                ); ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-12 col-md-6">
                    <?= form_open('', array('id' => 'hapus_semua')) ?>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="align-middle text-end">
                                <button id="submit-hapus" type="submit" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash3-fill me-1"></i> Hapus Terpilih
                                </button>
                            </td>
                            <td class="align-middle text-center" style="width: 30px">
                                <input style="width: 20px; height: 20px" class="check-all" id="check-all"
                                    type="checkbox">
                            </td>
                        </tr>
                    </table>
                    <?= form_close() ?>
                </div>
            </div>

            <?php
            if (!isset($jadwals)): ?>
                <div class="alert alert-warning align-content-center pb-0 m-2" role="alert">
                    <ul>
                        <li>
                            Silakan pilih <b>Filter</b> untuk menampilkan JADWAL
                        </li>
                        <li>
                            Memilih filter <b>SEMUA</b> mungkin akan memakan waktu <span class="text-danger text-bold">lebih
                                lama</span>.
                        </li>
                    </ul>
                </div>
            <?php else:
                if (count($jadwals) === 0): ?>
                    <?php if (!isset($tp_active) || !isset($smt_active)): ?>
                        <div class="alert alert-warning align-content-center m-2" role="alert">
                            Tahun Pelajaran atau Semester belum di set
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning align-content-center m-2" role="alert">
                            Pilih Filter terlebih dahulu atau Belum ada jadwal penilaian untuk Tahun Pelajaran
                            <b><?= $tp_active->tahun ?></b>
                            Semester: <b><?= $smt_active->smt ?></b>
                        </div>
                    <?php endif; ?>
                <?php else:
                    ?>
                    <div id="konten-jadwal">
                        <?php
                        if ($mode == '1'):
                            foreach ($jadwals as $title => $arrjadwal):
                                ?>
                                <div class="card card-default my-shadow mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title"><?= $title ?></h3>
                                        <div class="card-tools ms-auto">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php foreach ($arrjadwal as $lvl => $sjadwal): ?>
                                        <div class="card-body" style="display: block;">
                                            <h6 class="text-bold">Kelas: <?= $lvl ?></h6>
                                            <table class="table table-bordered w-100 dataTable">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center align-middle pt-3 pb-3" style="width: 40px">No.</th>
                                                        <th class="d-none">Hari/Tanggal</th>
                                                        <th class="align-middle">Kode Bank Soal</th>
                                                        <th class="align-middle">Mapel</th>
                                                        <th class="align-middle">Kelas</th>
                                                        <th class="text-center align-middle">Status</th>
                                                        <th class="text-center align-middle"><span>Aksi</span></th>
                                                        <th class="text-center align-middle" style="width: 50px"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $nomer = 1;
                                                    $currTgl = '';
                                                    foreach ($sjadwal as $jadwal): ?>
                                                        <?php
                                                        $jumlahKelas = $jadwal->bank_kelas;
                                                        $kelasbank = '';
                                                        $no = 1;
                                                        foreach ($jumlahKelas as $j) {
                                                            foreach ($kelas as $k) {
                                                                if ($j->kelas_id == $k->id_kelas) {
                                                                    $kelasbank .= ' <span class="badge badge-btn bg-info">' . $k->nama_kelas . '</span> ';
                                                                    $no++;
                                                                }
                                                            }
                                                        }
                                                        $startDay = strtotime($jadwal->tgl_mulai ?? '');
                                                        $endDay = strtotime($jadwal->tgl_selesai ?? '');
                                                        $today = strtotime(date('Y-m-d'));

                                                        $hariMulai = new DateTime($jadwal->tgl_mulai);
                                                        $hariSampai = new DateTime($jadwal->tgl_selesai);

                                                        $enableEdit = true;
                                                        $sedangdikerjakan = 0;
                                                        $terpakai = true;
                                                        $deletable = true;
                                                        $bgRandom = 'text-info';
                                                        if ($jadwal->status == '0') {
                                                            $bgRandom = 'text-secondary';
                                                        } else {
                                                            if ($today < $startDay) {
                                                                //belum dimulai
                                                                $bgRandom = 'text-info';
                                                            } elseif ($today > $endDay) {
                                                                //selesai
                                                                $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                                $bgRandom = $terpakai ? 'text-danger' : 'text-warning';
                                                                if ($jadwal->rekap == '1') {
                                                                    $bgRandom = $terpakai ? 'text-success' : 'text-warning';
                                                                } else {
                                                                    //$deletable = $terpakai;
                                                                }
                                                            } else {
                                                                //sedang dilaksanakan
                                                                $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                                //$deletable = $terpakai;
                                                                $bgRandom = $terpakai ? 'text-primary' : 'text-warning';
                                                                $sedangdikerjakan = $terpakai ? 1 : 0;
                                                            }
                                                        }

                                                        $total_seharusnya = ($jadwal->tampil_pg + $jadwal->tampil_kompleks + $jadwal->tampil_jodohkan + $jadwal->tampil_isian + $jadwal->tampil_esai);
                                                        $total_soal = $jadwal->total_soal == 0 ? 'Soal belum dibuat' :
                                                            ($jadwal->total_soal < $total_seharusnya ? 'Soal belum selesai' :
                                                                'Jml. Soal: <b>' . $total_seharusnya . '</b>')

                                                            ?>
                                                        <tr>
                                                            <?php
                                                            if ($currTgl != $jadwal->tgl_mulai):
                                                                $currTgl = $jadwal->tgl_mulai; ?>
                                                                <td colspan="8"
                                                                    class="align-middle bg-light ps-3 text-bold py-2 border-0 text-primary"><i
                                                                        class="bi bi-calendar-event me-2"></i><?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                            <?php endif; ?>
                                                            <td class="text-center align-middle p-0"><?= $nomer ?></td>
                                                            <td class="align-middle d-none">
                                                                <?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?>
                                                            </td>
                                                            <td class="align-middle"><i
                                                                    class="bi bi-square-fill me-1 text-lg <?= $bgRandom ?>"></i><?= $jadwal->bank_kode ?>
                                                            </td>
                                                            <td class="align-middle"><?= $jadwal->nama_mapel ?>
                                                                <br /> <span
                                                                    class="badge bg-secondary text-white"><?= $jadwal->soal_agama != '-' ? $jadwal->soal_agama : 'Umum' ?></span>
                                                            </td>
                                                            <td class="align-middle"><?= $kelasbank ?></td>
                                                            <?php
                                                            $stt = $jadwal->status == '1' ? 'Aktif' : 'Non Aktif';
                                                            $act = $jadwal->status == '1' ? 'NonAktifkan' : 'Aktifkan';
                                                            $btn_bg = $jadwal->status == '1' ? 'btn-warning' : 'btn-success';
                                                            $badge = $jadwal->status == '1' ? 'bg-success' : 'bg-warning';
                                                            $disableDelete = $deletable ? '' : 'disabled';

                                                            // Check allocation status
                                                            $sudah_alokasi = isset($alokasi_jadwal[$jadwal->id_jadwal]);
                                                            $alokasi_badge = $sudah_alokasi ? 'bg-info' : 'bg-secondary';
                                                            $alokasi_text = $sudah_alokasi ? 'Sudah Alokasi' : 'Belum Alokasi';
                                                            ?>

                                                            <td class="text-center align-middle">
                                                                <div class="d-flex flex-column gap-1">
                                                                    <div class="badge <?= $badge ?> text-sm font-weight-light"><?= $stt ?>
                                                                    </div>
                                                                    <div class="badge <?= $alokasi_badge ?> text-sm"
                                                                        title="Status Alokasi Waktu">
                                                                        <i class="bi bi-clock me-1"></i><?= $alokasi_text ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                                                    <button type="button" class="btn btn-primary btn-sm shadow-sm text-white"
                                                                        data-bs-toggle="modal" data-bs-target="#detailModal"
                                                                        data-jenis="<?= $jadwal->kode_jenis ?>"
                                                                        data-kode="<?= $jadwal->bank_kode ?>"
                                                                        data-mulai="<?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?>"
                                                                        data-sampai="<?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_selesai ?? ''))) ?>"
                                                                        data-durasi="<?= $jadwal->durasi_ujian ?>"
                                                                        data-acaksoal="<?= $jadwal->acak_soal == '1' ? 'Ya' : 'Tidak' ?>"
                                                                        data-acakjawaban="<?= $jadwal->acak_opsi == '1' ? 'Ya' : 'Tidak' ?>"
                                                                        data-hasiltampil="<?= $jadwal->hasil_tampil == '1' ? 'Ya' : 'Tidak' ?>"
                                                                        data-pelanggaran="<?= $jadwal->pelanggaran == '1' ? 'Ya' : 'Tidak' ?>"
                                                                        data-token="<?= $jadwal->token == '1' ? 'Ya' : 'Tidak' ?>"
                                                                        data-reset="<?= $jadwal->reset_login == '1' ? 'Ya' : 'Tidak' ?>"
                                                                        data-status="<?= ($jadwal->status === '0') ? 'Non Aktif' : 'Aktif' ?>"
                                                                        data-rekap="<?= $jadwal->rekap == '1' ? 'Sudah' : 'Belum' ?>"
                                                                        data-total="<?= isset($total_siswa[$jadwal->id_jadwal]) ? $total_siswa[$jadwal->id_jadwal] : '0'; ?>"
                                                                        title="Detail">
                                                                        <i class="bi bi-eye-fill text-white"></i>
                                                                    </button>
                                                                    <a href="<?= $enableEdit ? base_url('cbtjadwal/add/' . $jadwal->id_jadwal . '?enable=' . $sedangdikerjakan) : '#' ?>"
                                                                        class="btn btn-warning btn-sm shadow-sm <?= $enableEdit ? '' : 'btn-disabled' ?>"
                                                                        title="Edit">
                                                                        <i class="bi bi-pencil-square text-white"></i>
                                                                    </a>
                                                                    <button class="btn btn-danger btn-sm shadow-sm"
                                                                        onclick="hapus(<?= $jadwal->id_jadwal ?>)" <?= $disableDelete ?>
                                                                        title="Hapus">
                                                                        <i class="bi bi-trash3-fill text-white"></i>
                                                                    </button>
                                                                    <button class="btn <?= $btn_bg ?> btn-sm shadow-sm"
                                                                        onclick="aktifkanJadwal(<?= $jadwal->id_jadwal ?>, <?= $jadwal->status ?>)"
                                                                        title="<?= $act ?>">
                                                                        <i class="bi bi-power text-white"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle p-0" style="width: 50px">
                                                                <input name="checked[]" value="<?= $jadwal->id_jadwal ?>" class="check-jadwal"
                                                                    type="checkbox">
                                                            </td>
                                                        </tr>
                                                        <?php $nomer++; endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else:
                            foreach ($jadwals as $title => $arrjadwal): ?>
                                <div class="card card-default my-shadow mb-4">
                                    <div class="card-header">
                                        <h3 class="card-title"><?= $title ?></h3>
                                        <div class="card-tools ms-auto">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php foreach ($arrjadwal as $lvl => $sjadwal): ?>
                                        <div class="card-body" style="display: block;">
                                            <div><b>Kelas: <?= $lvl ?></b></div>
                                            <div class="row mt-2">
                                                <?php foreach ($sjadwal as $jadwal): ?>
                                                    <?php
                                                    $jumlahKelas = $jadwal->bank_kelas;
                                                    $kelasbank = '';
                                                    $no = 1;
                                                    foreach ($jumlahKelas as $j) {
                                                        foreach ($kelas as $k) {
                                                            if ($j->kelas_id === $k->id_kelas) {
                                                                if ($no > 1) {
                                                                    $kelasbank .= ', ';
                                                                }
                                                                $kelasbank .= $k->nama_kelas;
                                                                $no++;
                                                            }
                                                        }
                                                    }
                                                    $startDay = strtotime($jadwal->tgl_mulai ?? '');
                                                    $endDay = strtotime($jadwal->tgl_selesai ?? '');
                                                    $today = strtotime(date('Y-m-d'));

                                                    //echo 'skrg='.$today . ' start=' . $startDay . ' end=' . $endDay;
                            
                                                    $hariMulai = new DateTime($jadwal->tgl_mulai);
                                                    $hariSampai = new DateTime($jadwal->tgl_selesai);

                                                    $enableEdit = true;
                                                    $sedangdikerjakan = 0;
                                                    $terpakai = true;//isset($jadwal->total_siswa) && $jadwal->total_siswa != null;
                                                    $deletable = true;
                                                    $bgRandom = 'bg-gradient-info';
                                                    if ($jadwal->status == '0') {
                                                        $bgRandom = 'bg-gradient-secondary';
                                                    } else {
                                                        if ($today < $startDay) {
                                                            //belum dimulai
                                                            $bgRandom = 'bg-gradient-info';
                                                        } elseif ($today > $endDay) {
                                                            //selesai
                                                            $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                            $bgRandom = $terpakai ? 'bg-gradient-danger' : 'bg-gradient-warning';
                                                            if ($jadwal->rekap == '1') {
                                                                $deletable = $terpakai;
                                                                $bgRandom = $terpakai ? 'bg-gradient-success' : 'bg-gradient-warning';
                                                            }
                                                            //$enableEdit = false;
                                                        } else {
                                                            //sedang dilaksanakan
                                                            $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                            $bgRandom = $terpakai ? 'bg-gradient-primary' : 'bg-gradient-warning';
                                                            $sedangdikerjakan = $terpakai ? 1 : 0;
                                                            $deletable = $terpakai;
                                                        }
                                                    }

                                                    $total_seharusnya = ($jadwal->tampil_pg + $jadwal->tampil_kompleks + $jadwal->tampil_jodohkan + $jadwal->tampil_isian + $jadwal->tampil_esai);
                                                    $total_soal = $jadwal->total_soal == 0 ? 'Soal belum dibuat' :
                                                        ($jadwal->total_soal < $total_seharusnya ? 'Soal belum selesai' :
                                                            'Jml. Soal: <b>' . $total_seharusnya . '</b>')
                                                        ?>
                                                    <div class="col-md-6 col-lg-4">
                                                        <?php
                                                        $disableDelete = $deletable ? '' : 'disabled';
                                                        ?>
                                                        <div class="small-box <?= $bgRandom ?>" style="text-align: left">
                                                            <div class="inner" style="line-height: 1.3">
                                                                <h6 class="crop-text-1"><b><?= $jadwal->nama_mapel ?></b>
                                                                </h6>
                                                                <span><?= $total_soal ?></span>
                                                                <br>
                                                                <span class="text-sm">Kelas: </span>
                                                                <span class="float-end"><b><?= $kelasbank ?></b></span>
                                                            </div>
                                                            <div class="icon">
                                                                <i class="bi bi-book"></i>
                                                            </div>
                                                            <div class="list-jadwal-ujian">
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Jenis</span>
                                                                    <span class="float-end"><b><?= $jadwal->kode_jenis ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Kode Soal</span>
                                                                    <span class="float-end"><b><?= $jadwal->bank_kode ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Agama</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->soal_agama != '-' ? $jadwal->soal_agama : 'Umum' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Mulai</span>
                                                                    <span
                                                                        class="float-end"><b><?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Sampai</span>
                                                                    <span
                                                                        class="float-end"><b><?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_selesai ?? ''))) ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Durasi</span>
                                                                    <span class="float-end"><b><?= $jadwal->durasi_ujian ?>
                                                                            menit</b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Acak Soal</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->acak_soal == '1' ? 'Ya' : 'Tidak' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Acak Jawaban</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->acak_opsi == '1' ? 'Ya' : 'Tidak' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Hasil Tampil</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->hasil_tampil == '1' ? 'Ya' : 'Tidak' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Gunakan Token</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->token == '1' ? 'Ya' : 'Tidak' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Reset Login</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->reset_login == '1' ? 'Ya' : 'Tidak' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Hitung Pelanggaran</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->pelanggaran == '1' ? 'Ya' : 'Tidak' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Status</span>
                                                                    <span
                                                                        class="float-end"><b><?= ($jadwal->status === '0') ? 'Non Aktif' : 'Aktif' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Sudah Rekap</span>
                                                                    <span
                                                                        class="float-end"><b><?= $jadwal->rekap == '1' ? 'Sudah' : 'Belum' ?></b></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Mengerjakan</span>
                                                                    <span class="float-end">
                                                                        <b><?= isset($total_siswa[$jadwal->id_jadwal]) ? $total_siswa[$jadwal->id_jadwal] : '0'; ?></b>
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex justify-content-between me-2 ms-2">
                                                                    <span>Alokasi Waktu</span>
                                                                    <span class="float-end">
                                                                        <?php
                                                                        $ada_alokasi = isset($alokasi_jadwal[$jadwal->id_jadwal]);
                                                                        $alokasi_class = $ada_alokasi ? 'text-success' : 'text-warning';
                                                                        ?>
                                                                        <b
                                                                            class="<?= $alokasi_class ?>"><?= $ada_alokasi ? 'Sudah' : 'Belum' ?></b>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <hr style="margin-top:0; margin-bottom: 0">
                                                            <div class="small-box-footer p-2">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <input name="checked[]" value="<?= $jadwal->id_jadwal ?>"
                                                                        class="check-jadwal" type="checkbox" style="width: 24px;height: 24px">
                                                                    <div class="gap-1 d-flex">
                                                                        <a href="<?= $enableEdit ? base_url('cbtjadwal/add/' . $jadwal->id_jadwal . '?enable=' . $sedangdikerjakan) : '#' ?>"
                                                                            class="btn btn-warning btn-sm shadow-sm <?= $enableEdit ? '' : 'btn-disabled' ?>">
                                                                            <i class="bi bi-pencil-square text-white"></i><span
                                                                                class="ms-1 text-white"> Edit</span>
                                                                        </a>
                                                                        <button class="btn btn-danger btn-sm shadow-sm"
                                                                            onclick="hapus(<?= $jadwal->id_jadwal ?>)" <?= $disableDelete ?>>
                                                                            <i class="bi bi-trash3-fill"></i><span class="ms-1"> Hapus</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach;
                        endif;
                endif;
            endif; ?>
            </div>
        </div>
    </section>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Detail Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless table-striped table-sm">
                        <tr>
                            <td>Jenis</td>
                            <td id="modal_kode_jenis" </td>
                        </tr>
                        <tr>
                            <td>Kode Soal</td>
                            <td id="modal_bank_kode"></td>
                        </tr>
                        <tr>
                            <td>Mulai</td>
                            <td id="modal_mulai"></td>
                        </tr>
                        <tr>
                            <td>Sampai</td>
                            <td id="modal_sampai"></td>
                        </tr>
                        <tr>
                            <td>Durasi</td>
                            <td id="modal_durasi"></td>
                        </tr>
                        <tr>
                            <td>Acak Soal</td>
                            <td id="modal_acak_soal"></td>
                        </tr>
                        <tr>
                            <td>Acak Jawaban</td>
                            <td id="modal_acak_opsi"></td>
                        </tr>
                        <tr>
                            <td>Hasil Tampil</td>
                            <td id="modal_hasil_tampil"></td>
                        </tr>
                        <tr>
                            <td>Gunakan Token</td>
                            <td id="modal_token"></td>
                        </tr>
                        <tr>
                            <td>Reset Login</td>
                            <td id="modal_reset"></td>
                        </tr>
                        <tr>
                            <td>Hitung Pelanggaran</td>
                            <td id="modal_pelanggaran"></td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td id="modal_status"></td>
                        </tr>
                        <tr>
                            <td>Sudah Rekap</td>
                            <td id="modal_rekap"></td>
                        </tr>
                        <tr>
                            <td>Mengerjakan</td>
                            <td id="modal_total"></td>
                        </tr>
                    </table>
                    <div style="text-align: left">
                        <!--
                    <div class="inner" style="line-height: 1.3">
                        <h6 class="crop-text-1"><b>nama_mapel</b></h6>
                        <span>total_soal</span>
                        <br>
                        <span class="text-sm">Kelas: </span>
                        <span class="float-end"><b>$kelasbank</b></span>
                    </div>
                    -->
                        <div class="row list-jadwal-ujian">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <?= form_open('', array('id' => 'check')) ?>
    <?= form_close(); ?>

    <script>
        var idFilter = '<?= $id_filter ?>';
        var idMapel = '<?= $id_mapel ?>';
        var idLevel = '<?= $id_level ?>';
        var idGuru = '<?= $id_guru ?>';
        var mode = '<?= $mode ?>';

        const infoRekap = <?= json_encode($rekaps) ?>;
        adaJadwalUjian = '<?= count($ada_ujian) ?>';
        localStorage.setItem('garudaCBT.ada_jadwal_ujian', adaJadwalUjian);

        $('.btn-disabled').click(function (e) {
            e.preventDefault();
        });

        function hapus(id) {
            Swal.fire({
                title: "Anda yakin?",
                text: "Jadwal Ujian akan dihapus!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Hapus!"
            }).then(result => {
                if (result.value) {
                    Swal.fire({
                        text: "Silahkan tunggu....",
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                                Swal.showLoading();
                            }
                    });
                    $.ajax({
                        url: base_url + 'cbtjadwal/deletejadwal?id_jadwal=' + id,
                        type: "GET",
                        success: function (respon) {
                            console.log(respon);
                            if (respon.status) {
                                Swal.fire({
                                    title: "Berhasil",
                                    text: "Jadwal Ujian berhasil dihapus",
                                    icon: "success"
                                }).then(result => {
                                    if (result.value) {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal",
                                    text: "Tidak bisa menghapus, " + respon.message,
                                    icon: "error"
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr, status, error)
                            const err = JSON.parse(xhr.responseText)
                            Swal.fire({
                                title: "Error",
                                text: err.Message,
                                icon: "error"
                            });
                        }
                    });
                }
            });
        }

        function aktifkanJadwal(id, status) {
            if (status == '1') {
                Swal.fire({
                    title: "Anda yakin?",
                    text: "Jadwal akan dinonaktifkan!",
                    icon: "info",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Nonaktifkan"
                }).then(result => {
                    if (result.value) {
                        aktifkan(id, status);
                    }
                })
            } else {
                aktifkan(id, status);
            }
        }

        function aktifkan(id, status) {
            $.ajax({
                url: base_url + 'cbtjadwal/aktifkan_jadwal',
                data: { id_jadwal: id, method: status },
                type: "POST",
                success: function (respon) {
                    location.reload();
                },
                error: function () {
                    Swal.fire({
                        title: "Gagal",
                        text: "Ada data yang sedang digunakan",
                        icon: "error"
                    });
                }
            });
        }

        $(document).ready(function () {
            ajaxcsrf();
            $('.jam').datetimepicker({
                datepicker: false,
                format: 'H:i',
                step: 15,
                minTime: '07:00',
                maxTime: '17:00'
            });

            $('.tgl').datetimepicker({
                i18n: {
                    id: {
                        months: [
                            'Januari', 'Februari', 'Maret', 'April', 'Mei',
                            'Juni', 'Juli', 'Agustus', 'September',
                            'Oktober', 'November', 'Desember'
                        ],
                        dayOfWeek: [
                            'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'
                        ]
                    }
                },
                icons:
                {
                    next: 'bi bi-chevron-right',
                    previous: 'bi bi-chevron-left'
                },
                timepicker: false,
                scrollInput: false,
                scrollMonth: false,
                format: 'Y-m-d',
                //disabledWeekDays: [0],
                widgetPositioning: {
                    horizontal: 'left',
                    vertical: 'bottom'
                }
            });

            var count = $('#konten-jadwal .check-jadwal').length;

            var selectedF = idFilter == '' ? 'selected' : '';
            var selectedM = idMapel == '' ? 'selected' : '';
            var selectedL = idLevel == '' ? 'selected' : '';

            var opsiFilter = $('#filter')
            var opsiGuru = $('#guru')
            var opsiMapel = $('#mapel')
            var opsiLevel = $('#level')

            opsiFilter.select2({ width: '100%', });
            opsiGuru.select2({ width: '100%', });
            opsiMapel.select2({ width: '100%', });
            opsiLevel.select2({ width: '100%', });

            opsiFilter.prepend("<option value='' " + selectedF + " disabled='disabled'>Filter berdasarkan:</option>");
            opsiMapel.prepend("<option value='' " + selectedM + " disabled='disabled'>Pilih mapel:</option>");
            opsiLevel.prepend("<option value='' " + selectedL + " disabled='disabled'>Pilih level:</option>");

            function onChangeFilter(type) {
                if (type == '1') {
                    $('#select-guru').removeClass('d-none');
                    $('#select-mapel').addClass('d-none');
                    $('#select-level').addClass('d-none');
                } else if (type == '2') {
                    $('#select-guru').addClass('d-none');
                    $('#select-mapel').removeClass('d-none');
                    $('#select-level').addClass('d-none');
                } else if (type == '3') {
                    $('#select-guru').addClass('d-none');
                    $('#select-mapel').addClass('d-none');
                    $('#select-level').removeClass('d-none');
                } else {
                    $('#select-guru').addClass('d-none');
                    $('#select-mapel').addClass('d-none');
                    $('#select-level').addClass('d-none');
                }
            }

            opsiFilter.on('change', function () {
                var type = $(this).val();
                console.log(type);
                if (type == '0' && idFilter != '0') {
                    window.location.href = base_url + 'cbtjadwal?type=0&mode=' + mode;
                } else {
                    onChangeFilter(type);
                }
            });

            $('.sel').on('change', function () {
                var id = $(this).val();
                window.location.href = base_url + 'cbtjadwal?id=' + id + '&type=' + opsiFilter.val() + '&mode=' + mode;
            });

            onChangeFilter(opsiFilter.val());

            var unchecked = [];
            var checked = [];

            function findUnchecked() {
                unchecked = [];
                checked = [];
                var count = $('#konten-jadwal .check-jadwal').length;

                $("#konten-jadwal .check-jadwal:not(:checked)").each(function () {
                    unchecked.push($(this).val());
                });
                $("#konten-jadwal .check-jadwal:checked").each(function () {
                    checked.push($(this).val());
                });
                var countChecked = $("#konten-jadwal .check-jadwal:checked").length;
                $("#check-all").prop("checked", countChecked == count);

                $("#submit-hapus").attr('disabled', countChecked == 0);
            }

            $("#konten-jadwal").on("change", ".check-jadwal", function () {
                findUnchecked();
            });

            $("#check-all").on("click", function () {
                if (count > 0) {
                    if (this.checked) {
                        $(".check-jadwal").each(function () {
                            this.checked = true;
                            $("#check-all").prop("checked", true);
                        });
                    } else {
                        $(".check-jadwal").each(function () {
                            this.checked = false;
                            $("#check-all").prop("checked", false);
                        });
                    }
                    findUnchecked()
                }
            });

            $('#hapus_semua').on('submit', function (e) {
                e.stopPropagation();
                e.preventDefault();
                e.stopImmediatePropagation();

                var dataPost = $(this).serialize() + '&checked=' + JSON.stringify(checked);
                console.log(dataPost);

                Swal.fire({
                    title: "Anda yakin?",
                    text: "Semua Bank Soal yang dipilih akan dihapus!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Hapus!"
                }).then(result => {
                    if (result.value) {
                        Swal.fire({
                            text: "Silahkan tunggu....",
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            url: base_url + 'cbtjadwal/deletealljadwal',
                            type: "POST",
                            data: dataPost,
                            success: function (respon) {
                                console.log(respon);
                                if (respon.status) {
                                    Swal.fire({
                                        title: "Berhasil",
                                        text: "Jadwal berhasil dihapus",
                                        icon: "success"
                                    }).then(result => {
                                        if (result.value) {
                                            window.location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Gagal",
                                        text: "Tidak bisa menghapus, " + respon.message,
                                        icon: "error"
                                    });
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText)
                                const err = JSON.parse(xhr.responseText)
                                Swal.fire({
                                    title: "Error",
                                    text: err.Message,
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });

            $('#detailModal').on('show.bs.modal', function (e) {
                var jenis = $(e.relatedTarget).data('jenis');
                var kode = $(e.relatedTarget).data('kode');
                var mulai = $(e.relatedTarget).data('mulai');
                var sampai = $(e.relatedTarget).data('sampai');
                var durasi = $(e.relatedTarget).data('durasi');
                var acak_soal = $(e.relatedTarget).data('acaksoal');
                var acak_jawaban = $(e.relatedTarget).data('acakjawaban');
                var hasil_tampil = $(e.relatedTarget).data('hasiltampil');
                var pelanggaran = $(e.relatedTarget).data('pelanggaran');
                var token = $(e.relatedTarget).data('token');
                var reset = $(e.relatedTarget).data('reset');
                var status = $(e.relatedTarget).data('status');
                var rekap = $(e.relatedTarget).data('rekap');
                var total = $(e.relatedTarget).data('total');

                $('#modal_kode_jenis').html('<b>' + jenis + '</b>');
                $('#modal_bank_kode').html('<b>' + kode + '</b>');
                $('#modal_mulai').html('<b>' + mulai + '</b>');
                $('#modal_sampai').html('<b>' + sampai + '</b>');
                $('#modal_durasi').html('<b>' + durasi + ' menit</b>');
                $('#modal_acak_soal').html('<b>' + acak_soal + '</b>');
                $('#modal_acak_opsi').html('<b>' + acak_jawaban + '</b>');
                $('#modal_hasil_tampil').html('<b>' + hasil_tampil + '</b>');
                $('#modal_pelanggaran').html('<b>' + pelanggaran + '</b>');
                $('#modal_token').html('<b>' + token + '</b>');
                $('#modal_reset').html('<b>' + reset + '</b>');
                $('#modal_status').html('<b>' + status + '</b>');
                $('#modal_rekap').html('<b>' + rekap + '</b>');
                $('#modal_total').html('<b>' + total + '</b>');
            });

            if (infoRekap.ids_jadwal.length > 0) {
                Swal.fire({
                    text: "Silahkan tunggu....",
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                                Swal.showLoading();
                            }
                });
                updateAgain(infoRekap.ids_jadwal)
            }
        })

        async function updateAgain(idsJadwal) {
            console.log('rekaps', infoRekap)
            let formData = new FormData($('#check')[0]);
            formData.append('ids_jadwal', JSON.stringify(idsJadwal))
            const checkRespons = await ajaxPost('cbtjadwal/get_all_norekap', formData)

            if (checkRespons.rekap.length > 0) {
                const result = await getAllItemsAndGroupByJadwalAndSiswa(checkRespons.rekap)

                const grouped = [];
                let lastJadwal = '0';
                result?.forEach((value) => {
                    const idJadwal = value.id_jadwal || '0';
                    const idBank = value.id_bank || '0';

                    if (lastJadwal !== idJadwal) {
                        lastJadwal = idJadwal;
                    }

                    for (const siswa of value.siswaGroup || []) {
                        const idSiswa = siswa.id_siswa || '0';

                        let jawaban = [];
                        let nilai = [];
                        const soal = siswa.items[0];
                        jawaban[idJadwal] = jawaban[idJadwal] || [];
                        jawaban[idJadwal].push({
                            id_jadwal: idJadwal,
                            id_bank: idBank,
                            id_soal: soal.id_soal || '',
                            jenis: soal.jenis_soal || '',
                            no_soal_alias: soal.no_soal_alias || '',
                            opsi_alias_a: soal.opsi_alias_a || '',
                            opsi_alias_b: soal.opsi_alias_b || '',
                            opsi_alias_c: soal.opsi_alias_c || '',
                            opsi_alias_d: soal.opsi_alias_d || '',
                            opsi_alias_e: soal.opsi_alias_e || '',
                            jawaban_alias: soal.jawaban_alias || '',
                            jawaban_siswa: soal.jawaban_siswa || '',
                            jawaban_benar: soal.jawaban_benar || ''
                        });

                        nilai[idJadwal] = nilai[idJadwal] || [];
                        nilai[idJadwal].push({
                            id_jadwal: idJadwal,
                            id_bank: idBank,
                            id_soal: soal.id_soal || '',
                            point_soal: soal.point_soal || '',
                            nilai_koreksi: soal.nilai_koreksi || '',
                            nilai_otomatis: soal.nilai_otomatis || ''
                        });

                        let nilaiSiswa = {}
                        if (soal.id_nilai) {
                            nilaiSiswa = {
                                "id_nilai": soal.id_nilai,
                                "pg_benar": soal.pg_benar,
                                "pg_nilai": soal.pg_nilai,
                                "essai_nilai": soal.essai_nilai,
                                "id_siswa": soal.id_siswa,
                                "id_jadwal": soal.id_jadwal,
                                "kompleks_nilai": soal.kompleks_nilai,
                                "jodohkan_nilai": soal.jodohkan_nilai,
                                "isian_nilai": soal.isian_nilai,
                                "dikoreksi": soal.dikoreksi
                            }
                        }

                        let newSoalSiswa = {
                            id_siswa: idSiswa,
                            id_jadwal: idJadwal,
                            id_bank: idBank,
                            jawaban: JSON.stringify(jawaban[lastJadwal] || []),
                            nilai: JSON.stringify(nilai[lastJadwal] || []),
                            nilai_input: JSON.stringify(nilaiSiswa || {}),
                            reset_waktu: soal.reset_waktu || 0,
                            reset_izin: soal.reset_izin || 0,
                        }

                        if (soal.status) newSoalSiswa['status'] = soal.status
                        if (soal.mulai) newSoalSiswa['mulai'] = soal.mulai
                        if (soal.selesai) newSoalSiswa['selesai'] = soal.selesai
                        if (soal.lama_ujian) newSoalSiswa['lama_ujian'] = soal.lama_ujian

                        grouped.push(newSoalSiswa)
                    }
                });

                console.log('grup', grouped);
                if (grouped.length > 0) {
                    await postConvertedRekapNilai(grouped)
                }
            } else {
                swal.close()
            }
        }

        async function getAllItemsAndGroupByJadwalAndSiswa(allItems) {
            return new Promise((resolve, reject) => {
                // Mengelompokkan pertama berdasarkan id_jadwal
                const groupedByJadwal = allItems.reduce((acc, item) => {
                    const id_jadwal = item.id_jadwal;
                    const id_bank = item.id_bank;

                    if (!acc[id_jadwal]) {
                        acc[id_jadwal] = {
                            id_bank,   // Menyimpan id_bank di root untuk setiap id_jadwal
                            siswaGroup: {} // Objek untuk menyimpan data id_siswa
                        };
                    }
                    const id_siswa = item.id_siswa;
                    if (!acc[id_jadwal].siswaGroup[id_siswa]) {
                        acc[id_jadwal].siswaGroup[id_siswa] = [];
                    }

                    acc[id_jadwal].siswaGroup[id_siswa].push(item);
                    return acc;
                }, {});

                // Mengubah hasil menjadi array
                const resultArray = Object.entries(groupedByJadwal).map(([id_jadwal, value]) => {
                    return {
                        id_jadwal,
                        id_bank: value.id_bank, // id_bank berada di root object
                        siswaGroup: Object.entries(value.siswaGroup).map(([id_siswa, items]) => ({
                            id_siswa,
                            items
                        }))
                    };
                });

                resolve(resultArray); // Mengembalikan array data yang sudah dikelompokkan
            });
        }

        async function postConvertedRekapNilai(data) {
            const pages = Math.ceil(data.length / 99)
            console.log('pages', pages)

            for (let i = 0; i < pages; i++) {
                let pagePost = i + 1;
                const jadwals = await paginate(data, pagePost, 99)
                let formData = new FormData($('#check')[0]);
                let index = 0;
                for (const rekap of jadwals) {
                    const ids = {
                        id_tp: rekap.id_tp || 0,
                        tp: rekap.tp || '',
                        id_smt: rekap.id_smt || 0,
                        smt: rekap.smt || '',
                        id_siswa: rekap.id_siswa || 0,
                        id_jadwal: rekap.id_jadwal || 0,
                        id_bank: rekap.id_bank || 0,
                        id_kelas: rekap.id_kelas || 0,
                        kelas: rekap.kelas || '',
                        mulai: rekap.mulai || '',
                        selesai: rekap.selesai || '',
                        lama_ujian: rekap.lama_ujian || 0,
                        status: rekap.status || 0,
                        nama_siswa: rekap.nama_siswa || '',
                        no_peserta: rekap.no_peserta || '',
                    };
                    formData.append(`nilai[${index}][ids]`, JSON.stringify(ids))
                    formData.append(`nilai[${index}][jawaban]`, rekap.jawaban || '[]')
                    formData.append(`nilai[${index}][nilai]`, rekap.nilai || '[]')
                    formData.append(`nilai[${index}][ruang_sesi]`, rekap.ruang_sesi)
                    formData.append(`nilai[${index}][nilai_input]`, rekap.nilai_input || '{}')
                    index++;
                }
                const result = await ajaxPost('migrate/convert_rekap_nilai', formData)
                console.log('result', result)
            }
            swal.close()
        }

        async function paginate(data, pageNumber, itemsPerPage) {
            const startIndex = (pageNumber - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            return data.slice(startIndex, endIndex);
        }

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
                            if (respon.success) {
                                resolve(respon); // Resolusi Promise ketika sukses
                            } else {
                                reject(respon.message); // Rejeksi Promise ketika ada error
                            }
                        },
                        error: function (xhr, status, error) {
                            reject(error); // Rejeksi Promise ketika ada error
                        }
                    });
                }, 500)
            });
        }

    </script>