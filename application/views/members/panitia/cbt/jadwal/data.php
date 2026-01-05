<?php
/** @var array $jadwals */
/** @var string $mode */
/** @var array $filters */
/** @var string $id_filter */
/** @var array $gurus */
/** @var array $mapels */
/** @var array $levels */
?>

<main class="app-main pt-4">

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title">Jadwal Ujian/Ulangan</h6>
                    <div class="card-tools ms-auto d-flex align-items-center gap-2">
                        <a href="<?= base_url('cbtjadwal?mode=' . $mode) ?>" type="button"
                            class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline-block ms-1">Reload</span>
                        </a>
                        <div class="btn-group">
                            <?php $activeList = $mode == '1' ? ' active' : ''; ?>
                            <?php $activeGrid = $mode == '2' ? ' active' : ''; ?>
                            <a href="<?= base_url('cbtjadwal?mode=1') ?>" type="button"
                                class="btn btn-sm btn-primary<?= $activeList ?>"><i class="bi bi-list"></i></a>
                            <a href="<?= base_url('cbtjadwal?mode=2') ?>" type="button"
                                class="btn btn-sm btn-primary<?= $activeGrid ?>"><i class="bi bi-th-large"></i></a>
                        </div>
                        <button type="button" class="btn btn-tool ms-1" data-card-widget="collapse" title="Collapse">
                            <i class="bi bi-minus"></i>
                        </button>
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
                <div class="col-12 col-md-12">
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
                                                <i class="bi bi-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php foreach ($arrjadwal as $lvl => $sjadwal): ?>
                                        <div class="card-body" style="display: block;">
                                            <h6 class="text-bold">Kelas: <?= $lvl ?></h6>
                                            <table class="table table-bordered w-100">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center align-middle pt-3 pb-3" style="width: 40px">No.</th>
                                                        <th class="d-none">Hari/Tanggal</th>
                                                        <th class="align-middle">Kode Bank Soal</th>
                                                        <th class="align-middle">Mapel</th>
                                                        <th class="align-middle">Kelas</th>
                                                        <th class="text-center align-middle">Status</th>
                                                        <th class="text-center align-middle">Detail</th>
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

                                                        $terpakai = true;
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
                                                                }
                                                            } else {
                                                                //sedang dilaksanakan
                                                                $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                                $bgRandom = $terpakai ? 'text-primary' : 'text-warning';
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
                                                                <td colspan="7" class="align-middle bg-light ps-3 text-bold">
                                                                    <?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?>
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
                                                                    class="badge bg-light"><?= $jadwal->soal_agama != '-' ? $jadwal->soal_agama : 'Umum' ?></span>
                                                            </td>
                                                            <td class="align-middle"><?= $kelasbank ?></td>
                                                            <?php
                                                            $stt = $jadwal->status == '1' ? 'Aktif' : 'Non Aktif';
                                                            $badge = $jadwal->status == '1' ? 'bg-success' : 'bg-warning';
                                                            ?>

                                                            <td class="text-center align-middle">
                                                                <div class="mb-1 badge <?= $badge ?> text-sm font-weight-light"><?= $stt ?>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <a type="button" class="btn btn-primary btn-sm ms-1 text-white"
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
                                                                    data-total="<?= isset($total_siswa[$jadwal->id_jadwal]) ? $total_siswa[$jadwal->id_jadwal] : '0'; ?>">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>
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
                                                <i class="bi bi-minus"></i>
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

                                                    $terpakai = true;
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
                                                                $bgRandom = $terpakai ? 'bg-gradient-success' : 'bg-gradient-warning';
                                                            }
                                                        } else {
                                                            //sedang dilaksanakan
                                                            $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                            $bgRandom = $terpakai ? 'bg-gradient-primary' : 'bg-gradient-warning';
                                                        }
                                                    }

                                                    $total_seharusnya = ($jadwal->tampil_pg + $jadwal->tampil_kompleks + $jadwal->tampil_jodohkan + $jadwal->tampil_isian + $jadwal->tampil_esai);
                                                    $total_soal = $jadwal->total_soal == 0 ? 'Soal belum dibuat' :
                                                        ($jadwal->total_soal < $total_seharusnya ? 'Soal belum selesai' :
                                                            'Jml. Soal: <b>' . $total_seharusnya . '</b>')
                                                        ?>
                                                    <div class="col-md-6 col-lg-4">
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
                            <td id="modal_kode_jenis"></td>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var idFilter = '<?= $id_filter ?>';
        var idMapel = '<?= $id_mapel ?>';
        var idLevel = '<?= $id_level ?>';
        var idGuru = '<?= $id_guru ?>';
        var mode = '<?= $mode ?>';

        $(document).ready(function () {
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
        })
    </script>