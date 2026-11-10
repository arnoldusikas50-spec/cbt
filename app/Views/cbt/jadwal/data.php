<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Set default value if not passed
$alokasi_jadwal = $alokasi_jadwal ?? [];
?>

<!-- Content Header -->
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold text-dark">
                    <i class="bi-[icon] text-primary me-2"></i>
                    <?= esc($judul ?? 'Halaman') ?>
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?= site_url('dashboard') ?>" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><?= esc($subjudul ?? 'Data') ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header border-0 shadow-none">
                <h3 class="card-title fw-bold text-primary"><i
                        class="bi bi-calendar-event me-2"></i><?= esc($subjudul) ?></h3>
                <div class="card-tools ms-auto">
                    <a href="<?= base_url('cbtjadwal?mode=' . $mode) ?>" type="button" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Reload
                    </a>
                    <a href="<?= base_url('cbtjadwal/add/0') ?>" type="button"
                        class="btn btn-sm btn-primary shadow-sm ms-1">
                        <i class="bi bi-plus me-1"></i> Tambah Jadwal
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
                <a class="badge bg-danger text-white mb-2">Kode Warna Jadwal:</a>
                <div class="row">
                    <div class="col-md-6">
                        <table class="w-100 table-sm text-sm">
                            <tr>
                                <td style="width: 25px"><i class="bi bi-square-fill text-secondary"></i></td>
                                <td>Tidak aktif</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-square-fill text-primary"></i></td>
                                <td>Sedang dilaksanakan / Aktif</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-square-fill text-info"></i></td>
                                <td>Belum waktu jadwal</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="w-100 table-sm text-sm">
                            <tr>
                                <td style="width: 25px"><i class="bi bi-square-fill text-warning"></i></td>
                                <td>Belum ada Siswa Login</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-square-fill text-danger"></i></td>
                                <td>Selesai, belum rekap nilai</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-square-fill text-success"></i></td>
                                <td>Selesai, sudah rekap nilai</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row align-items-center mb-1">
            <div class="col-auto pe-0">
                <span class="text-dark">Filter:</span>
            </div>
            <div class="col">
                <?php echo form_dropdown('f', $filters, $id_filter, 'id="filter" class="form-control select2"'); ?>
            </div>
            <div class="col-md-4 col-sm-12 d-flex justify-content-end align-items-center mt-2 mt-md-0">
                <?= form_open('', array('id' => 'hapus_semua', 'class' => 'd-flex align-items-center')) ?>
                <button id="submit-hapus" type="submit" class="btn btn-danger btn-sm shadow-sm me-2" disabled>
                    <i class="bi bi-trash3-fill me-1"></i>Hapus Terpilih
                </button>
                <div class="form-check">
                    <input class="form-check-input check-all" type="checkbox" id="check-all"
                        style="width: 24px; height: 24px; cursor: pointer;">
                </div>
                <?= form_close() ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-auto" style="width: 50px"></div>
            <div class="col">
                <div id="select-guru" class="d-none">
                    <?php echo form_dropdown('guru', $gurus ?? [], $id_guru, 'id="guru" class="sel form-control select2" style="width:100%"'); ?>
                </div>
                <div id="select-mapel" class="d-none">
                    <?php echo form_dropdown('mapel', $mapels ?? [], $id_mapel, 'id="mapel" class="sel form-control select2" style="width:100%"'); ?>
                </div>
                <div id="select-level" class="d-none">
                    <?php echo form_dropdown('level', $levels ?? [], $id_level, 'id="level" class="sel form-control select2" style="width:100%"'); ?>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>

        <?php if (!isset($jadwals)): ?>
            <div class="alert alert-warning m-2" role="alert">
                Pilih filter untuk melihat jadwal.
            </div>
        <?php else:
            if (empty($jadwals)): ?>
                <div class="alert alert-warning m-2" role="alert">
                    Belum ada jadwal penilaian.
                </div>
            <?php else: ?>
                <div id="konten-jadwal">
                    <?php if ($mode == '1'): ?>
                        <?php foreach ($jadwals as $title => $arrjadwal): ?>
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
                                    <div class="card-body p-0">
                                        <div class="p-2 bg-light font-weight-bold">Kelas: <?= $lvl ?></div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover w-100 dataTable">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" width="40">No.</th>
                                                        <th>Kode Bank Soal</th>
                                                        <th>Mapel</th>
                                                        <th>Kelas</th>
                                                        <th class="text-center">KKM</th>
                                                        <th class="text-center">Token</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Aksi</th>
                                                        <th width="40"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $nomer = 1;
                                                    $currTgl = '';
                                                    foreach ($sjadwal as $jadwal):
                                                        if ($currTgl != $jadwal->tgl_mulai):
                                                            $currTgl = $jadwal->tgl_mulai; ?>
                                                            <tr>
                                                                <td colspan="9"
                                                                    class="align-middle bg-light ps-3 text-bold py-2 border-0 text-primary">
                                                                    <i
                                                                        class="bi bi-calendar-event me-2"></i><?= singkat_tanggal(date('D, d M Y', strtotime($jadwal->tgl_mulai ?? ''))) ?>
                                                                </td>
                                                            </tr>
                                                        <?php endif;
                                                        $kelasbank = '';
                                                        foreach ($jadwal->bank_kelas as $j) {
                                                            foreach ($kelas as $k) {
                                                                if ($j->kelas_id == $k->id_kelas) {
                                                                    $kelasbank .= ' <span class="badge bg-info">' . $k->nama_kelas . '</span> ';
                                                                }
                                                            }
                                                        }

                                                        $startDay = strtotime($jadwal->tgl_mulai ?? '');
                                                        $endDay = strtotime($jadwal->tgl_selesai ?? '');
                                                        $today = strtotime(date('Y-m-d'));

                                                        // Color Logic
                                                        $bgRandom = 'text-info';
                                                        $stt = $jadwal->status == '1' ? 'Aktif' : 'Non Aktif';
                                                        $badge = $jadwal->status == '1' ? 'bg-success' : 'bg-secondary';
                                                        $enableEdit = true;
                                                        $sedangdikerjakan = 0;

                                                        if ($jadwal->status == '0') {
                                                            $bgRandom = 'text-secondary';
                                                        } else {
                                                            if ($today < $startDay) {
                                                                $bgRandom = 'text-info';
                                                            } elseif ($today > $endDay) {
                                                                $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                                $bgRandom = $terpakai ? 'text-danger' : 'text-warning';
                                                                if ($jadwal->rekap == '1') {
                                                                    $bgRandom = 'text-success';
                                                                }
                                                            } else {
                                                                $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                                $bgRandom = $terpakai ? 'text-primary' : 'text-warning';
                                                                $sedangdikerjakan = $terpakai ? 1 : 0;
                                                            }
                                                        }

                                                        // Allocation Check
                                                        $sudah_alokasi = isset($alokasi_jadwal[(int)$jadwal->id_jadwal]);
                                                        $alokasi_badge = $sudah_alokasi ? 'bg-info' : 'bg-secondary';
                                                        $alokasi_text = $sudah_alokasi ? 'Sudah Alokasi' : 'Belum Alokasi';
                                                        ?>
                                                        <tr>
                                                            <td class="text-center"><?= $nomer++ ?></td>
                                                            <td><i class="bi bi-square-fill me-1 <?= $bgRandom ?>"></i>
                                                                <?= $jadwal->bank_kode ?></td>
                                                            <td><?= $jadwal->nama_mapel ?></td>
                                                            <td><?= $kelasbank ?></td>
                                                            <td class="text-center">
                                                                <b><?= $jadwal->kkm ?? '-' ?></b>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if (!empty($jadwal->token_value)): ?>
                                                                    <code class="font-monospace user-select-all" style="cursor:pointer" onclick="copyTokenValue('<?= esc($jadwal->token_value) ?>')" title="Klik untuk salin"><?= esc($jadwal->token_value) ?></code>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge-action-status <?= $jadwal->status == '1' ? '' : 'inactive' ?>"><?= $stt ?></span><br>
                                                                <span class="badge <?= $alokasi_badge ?> mt-1"><?= $alokasi_text ?></span>
                                                            </td>
                                                            <td class="text-center text-nowrap">
                                                                <div class="btn-action-group justify-content-center">
                                                                    <button type="button" class="btn btn-action btn-action-warning"
                                                                        onclick="aktifkanJadwal(<?= $jadwal->id_jadwal ?>, <?= $jadwal->status ?>)"
                                                                        title="<?= $jadwal->status == '1' ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' ?>">
                                                                        <i class="bi bi-power"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-action btn-action-magic"
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
                                                                        data-token-value="<?= esc($jadwal->token_value ?? '') ?>"
                                                                        data-reset="<?= $jadwal->reset_login == '1' ? 'Ya' : 'Tidak' ?>"
                                                                        data-status="<?= ($jadwal->status === '0') ? 'Non Aktif' : 'Aktif' ?>"
                                                                        data-rekap="<?= $jadwal->rekap == '1' ? 'Sudah' : 'Belum' ?>"
                                                                        data-total="<?= isset($total_siswa[$jadwal->id_jadwal]) ? $total_siswa[$jadwal->id_jadwal] : '0'; ?>"
                                                                        data-kkm="<?= $jadwal->kkm ?? '-' ?>"
                                                                        title="Detail Jadwal">
                                                                        <i class="bi bi-eye-fill"></i>
                                                                    </button>
                                                                    <a href="<?= base_url('cbtjadwal/add/' . $jadwal->id_jadwal . '?enable=' . $sedangdikerjakan) ?>"
                                                                        class="btn btn-action btn-action-edit" title="Edit Jadwal">
                                                                        <i class="bi bi-pencil-fill"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-action btn-action-delete"
                                                                        onclick="hapus(<?= $jadwal->id_jadwal ?>)" title="Hapus Jadwal">
                                                                        <i class="bi bi-trash-fill"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <input name="checked[]" value="<?= $jadwal->id_jadwal ?>" class="check-jadwal" type="checkbox">
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($jadwals as $title => $arrjadwal): ?>
                            <div class="card card-default my-shadow mb-4">
                                <div class="card-header">
                                    <h3 class="card-title"><?= $title ?></h3>
                                </div>
                                <?php foreach ($arrjadwal as $lvl => $sjadwal): ?>
                                    <div class="card-body p-2">
                                        <div class="row">
                                            <?php foreach ($sjadwal as $jadwal):
                                                $deletable = $deletable ?? true;
                                                $total_seharusnya = ($jadwal->tampil_pg + $jadwal->tampil_kompleks + $jadwal->tampil_jodohkan + $jadwal->tampil_isian + $jadwal->tampil_esai);
                                                $total_soal = $jadwal->total_soal == 0 ? 'Soal belum dibuat' :
                                                    ($jadwal->total_soal < $total_seharusnya ? 'Soal belum selesai' :
                                                        'Jml. Soal: <b>' . $total_seharusnya . '</b>');
                                                $kelasbank = '';
                                                foreach ($jadwal->bank_kelas as $j) {
                                                    foreach ($kelas as $k) {
                                                        if ($j->kelas_id == $k->id_kelas) {
                                                            $kelasbank .= ' <span class="badge bg-info">' . $k->nama_kelas . '</span> ';
                                                        }
                                                    }
                                                }
                                                $startDay = strtotime($jadwal->tgl_mulai ?? '');
                                                $endDay = strtotime($jadwal->tgl_selesai ?? '');
                                                $today = strtotime(date('Y-m-d'));
                                                $bgRandom = 'text-info';
                                                $enableEdit = true;
                                                $sedangdikerjakan = 0;
                                                if ($jadwal->status == '0') {
                                                    $bgRandom = 'text-secondary';
                                                } elseif ($today > $endDay) {
                                                    $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                    $bgRandom = $terpakai ? 'text-danger' : 'text-warning';
                                                    if ($jadwal->rekap == '1') {
                                                        $bgRandom = 'text-success';
                                                    }
                                                } elseif ($today >= $startDay) {
                                                    $terpakai = isset($total_siswa[$jadwal->id_jadwal]);
                                                    $bgRandom = $terpakai ? 'text-primary' : 'text-warning';
                                                    $sedangdikerjakan = $terpakai ? 1 : 0;
                                                }
                                            ?>
                                            <!-- DEBUG -->
                                            <!-- echo '<!-- DEBUG: id_jadwal=' . $jadwal->id_jadwal . ' sudah_alokasi=' . ($sudah_alokasi ? 'yes' : 'no') . ' alokasi_jadwal keys=' . implode(',', array_keys($alokasi_jadwal ?? [])) . ' -->'; -->
                                            <div class="col-md-6 col-lg-4">
                                                    <?php $disableDelete = $deletable ? '' : 'disabled'; ?>
                                                    <div class="small-box <?= $bgRandom ?>" style="text-align: left">
                                                        <div class="inner" style="line-height: 1.3">
                                                            <h6 class="crop-text-1"><b><?= $jadwal->nama_mapel ?></b></h6>
                                                            <div class="d-flex justify-content-between">
                                                                <span><?= $total_soal ?></span>
                                                                <span>KKM: <b><?= $jadwal->kkm ?? '-' ?></b></span>
                                                            </div>
                                                            <span class="text-sm">Kelas: </span>
                                                            <span class="float-end"><b><?= $kelasbank ?></b></span>
                                                        </div>
                                                        <div class="icon"><i class="bi bi-book"></i></div>

                                                        <div class="small-box-footer p-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <button type="button" class="btn btn-outline-light btn-sm"
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
                                                                    data-kkm="<?= $jadwal->kkm ?? '-' ?>"
                                                                    title="Detail">
                                                                    Detail <i class="bi bi-arrow-right-circle"></i>
                                                                </button>

                                                                 <div class="btn-action-group">
                                                                     <a href="<?= $enableEdit ? base_url('cbtjadwal/add/' . $jadwal->id_jadwal . '?enable=' . $sedangdikerjakan) : '#' ?>"
                                                                         class="btn btn-action btn-action-edit <?= $enableEdit ? '' : 'disabled' ?>" title="Edit Jadwal">
                                                                         <i class="bi bi-pencil-fill"></i>
                                                                     </a>
                                                                     <button type="button" class="btn btn-action btn-action-delete"
                                                                         onclick="hapus(<?= $jadwal->id_jadwal ?>)" title="Hapus Jadwal">
                                                                         <i class="bi bi-trash-fill"></i>
                                                                     </button>
                                                                 </div></div>
                                                            </div>

                                                            <?php
                                                            $ada_alokasi = isset($alokasi_jadwal[(int)$jadwal->id_jadwal]);
                                                            $alokasi_class = $ada_alokasi ? 'text-white' : 'text-white-50';
                                                            ?>
                                                            <div class="mt-2 text-sm text-end border-top pt-1">
                                                                <span class="<?= $alokasi_class ?>">
                                                                    <i class="bi bi-clock"></i>
                                                                    <?= $ada_alokasi ? 'Sudah Alokasi' : 'Belum Alokasi' ?>
                                                                </span>
                                                                <div class="float-start">
                                                                    <input name="checked[]" value="<?= $jadwal->id_jadwal ?>"
                                                                        class="check-jadwal" type="checkbox" style="width: 20px;height: 20px">
                                                                </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Detail Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <td>KKM</td>
                        <td id="modal_kkm"></td>
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
                <button type="button" class="btn btn-primary border px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= form_open('', array('id' => 'check')) ?>
<?= form_close(); ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var idFilter = '<?= $id_filter ?? '' ?>';
    var idMapel = '<?= $id_mapel ?? '' ?>';
    var idLevel = '<?= $id_level ?? '' ?>';
    var idGuru = '<?= $id_guru ?? '' ?>';
    var mode = '<?= $mode ?? "1" ?>';
    const infoRekap = <?= json_encode($rekaps ?? []) ?>;

    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        // Filter handlers
        $('#filter').change(function () {
            var val = $(this).val();
            $('#select-guru, #select-mapel, #select-level').addClass('d-none');
            if (val == '1') $('#select-guru').removeClass('d-none');
            else if (val == '2') $('#select-mapel').removeClass('d-none');
            else if (val == '3') $('#select-level').removeClass('d-none');
            else if (val == '0') window.location.href = base_url + 'cbtjadwal?type=0&mode=' + mode;
        });

        $('#guru').change(function () { window.location.href = base_url + 'cbtjadwal?type=1&id=' + $(this).val() + '&mode=' + mode; });
        $('#mapel').change(function () { window.location.href = base_url + 'cbtjadwal?type=2&id=' + $(this).val() + '&mode=' + mode; });
        $('#level').change(function () { window.location.href = base_url + 'cbtjadwal?type=3&id=' + $(this).val() + '&mode=' + mode; });

        // Initial Visibility
        if (idFilter == '1') $('#select-guru').removeClass('d-none');
        else if (idFilter == '2') $('#select-mapel').removeClass('d-none');
        else if (idFilter == '3') $('#select-level').removeClass('d-none');

        // Check All
        $('#check-all').click(function () {
            $('.check-jadwal').prop('checked', this.checked);
            $('#submit-hapus').prop('disabled', !this.checked);
        });

        $(document).on('change', '.check-jadwal', function () {
            if ($('.check-jadwal:checked').length > 0) $('#submit-hapus').prop('disabled', false);
            else $('#submit-hapus').prop('disabled', true);
        });

        // Bulk Delete
        $('#hapus_semua').submit(function (e) {
            e.preventDefault();
            var checkedIds = [];
            $('.check-jadwal:checked').each(function () { checkedIds.push($(this).val()); });

            Swal.fire({
                title: 'Hapus Terpilih?',
                text: "Jadwal yang dipilih akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: base_url + 'cbtjadwal/deleteAllJadwal',
                        type: "POST",
                        data: { checked: JSON.stringify(checkedIds) },
                        success: function (respon) {
                            if (respon.status) {
                                Swal.fire('Berhasil', 'Data berhasil dihapus', 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Gagal', respon.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // Modal Population
        $('#detailModal').on('show.bs.modal', function (e) {
            var btn = $(e.relatedTarget);
            $('#modal_kode_jenis').html('<b>' + btn.data('jenis') + '</b>');
            $('#modal_bank_kode').html('<b>' + btn.data('kode') + '</b>');
            $('#modal_mulai').html('<b>' + btn.data('mulai') + '</b>');
            $('#modal_sampai').html('<b>' + btn.data('sampai') + '</b>');
            $('#modal_durasi').html('<b>' + btn.data('durasi') + ' menit</b>');
            $('#modal_acak_soal').html('<b>' + btn.data('acaksoal') + '</b>');
            $('#modal_acak_opsi').html('<b>' + btn.data('acakjawaban') + '</b>');
            $('#modal_hasil_tampil').html('<b>' + btn.data('hasiltampil') + '</b>');
            $('#modal_pelanggaran').html('<b>' + btn.data('pelanggaran') + '</b>');
            $('#modal_token').html('<b>' + btn.data('token') + '</b>' + (btn.data('tokenValue') ? ' <code class="ms-2">' + btn.data('tokenValue') + '</code>' : ''));
            $('#modal_reset').html('<b>' + btn.data('reset') + '</b>');
            $('#modal_status').html('<b>' + btn.data('status') + '</b>');
            $('#modal_kkm').html('<b>' + btn.data('kkm') + '</b>');
            $('#modal_rekap').html('<b>' + btn.data('rekap') + '</b>');
            $('#modal_total').html('<b>' + btn.data('total') + '</b>');
        });
    });

    function hapus(id) {
        Swal.fire({
            title: 'Anda yakin?',
            text: "Jadwal akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'cbtjadwal/deleteJadwal',
                    data: { id_jadwal: id },
                    success: function (respon) {
                        if (respon.status) {
                            Swal.fire('Berhasil', 'Jadwal dihapus', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', respon.message, 'error');
                        }
                    }
                });
            }
        });
    }

    function aktifkanJadwal(id, status) {
        var txt = status == 1 ? 'nonaktifkan' : 'aktifkan';
        Swal.fire({
            title: 'Konfirmasi',
            text: "Anda akan meng-" + txt + " jadwal ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'cbtjadwal/aktifkan_jadwal',
                    type: 'POST',
                    data: { id_jadwal: id, method: status },
                    success: function (respon) {
                        location.reload();
                    }
                });
            }
        });
    }

    function copyTokenValue(e, token) {
        if (e && e.stopPropagation) e.stopPropagation();
        navigator.clipboard.writeText(token).then(function() {
            Swal.fire({ icon: 'success', title: 'Tersalin', text: 'Token: ' + token, timer: 1500, showConfirmButton: false });
        }).catch(function() {
            // Fallback
            var ta = document.createElement('textarea');
            ta.value = token;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            Swal.fire({ icon: 'success', title: 'Tersalin', timer: 1500, showConfirmButton: false });
        });
    }
</script>
<?= $this->endSection() ?>