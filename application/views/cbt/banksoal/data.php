<?php
$allBanksIds = [];
?>
<main class="app-main pt-4">

    <section class="content">
        <div class="container-fluid">
            <!-- Countdown Card -->
            <?php
            // Menggunakan field 'ba_waktu' dari tabel setting sesuai yang ada di Pengaturan
            $deadline = ($setting && isset($setting->ba_waktu)) ? $setting->ba_waktu : '';
            ?>
            <div class="card border-0 shadow-sm mb-4 overflow-hidden" id="card-countdown"
                style="display: none; border-radius: 12px;">
                <div class="card-body p-4 bg-white">
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-4 mb-lg-0 text-center text-lg-start">
                            <div class="d-inline-block d-lg-block">
                                <span
                                    class="badge bg-light text-primary border border-primary px-3 py-2 rounded-pill mb-2 shadow-sm">
                                    <i class="bi bi-stopwatch me-1"></i> DEADLINE PENGISIAN
                                </span>
                            </div>
                            <h4 class="fw-bold text-dark mb-1" id="deadline-text"
                                style="font-family: 'Segoe UI', sans-serif;">-</h4>
                            <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>Selesaikan penyusunan
                                bank soal sebelum waktu habis.</p>
                        </div>
                        <div class="col-lg-6">
                            <div id="countdown-timer" class="row g-2 justify-content-center justify-content-lg-end">
                                <div class="col-auto">
                                    <div class="text-center p-3 bg-primary bg-gradient text-white shadow rounded-3"
                                        style="min-width: 85px;">
                                        <h2 class="fw-bold mb-0 lh-1 display-6" id="days" style="font-size: 2rem;">00
                                        </h2>
                                        <small class="text-uppercase fw-bold"
                                            style="font-size: 0.6rem; letter-spacing: 1px;">Hari</small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="text-center p-3 bg-primary bg-gradient text-white shadow rounded-3"
                                        style="min-width: 85px;">
                                        <h2 class="fw-bold mb-0 lh-1 display-6" id="hours" style="font-size: 2rem;">00
                                        </h2>
                                        <small class="text-uppercase fw-bold"
                                            style="font-size: 0.6rem; letter-spacing: 1px;">Jam</small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="text-center p-3 bg-primary bg-gradient text-white shadow rounded-3"
                                        style="min-width: 85px;">
                                        <h2 class="fw-bold mb-0 lh-1 display-6" id="minutes" style="font-size: 2rem;">00
                                        </h2>
                                        <small class="text-uppercase fw-bold"
                                            style="font-size: 0.6rem; letter-spacing: 1px;">Menit</small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="text-center p-3 bg-primary bg-gradient text-white shadow rounded-3"
                                        style="min-width: 85px;">
                                        <h2 class="fw-bold mb-0 lh-1 display-6" id="seconds" style="font-size: 2rem;">00
                                        </h2>
                                        <small class="text-uppercase fw-bold"
                                            style="font-size: 0.6rem; letter-spacing: 1px;">Detik</small>
                                    </div>
                                </div>
                            </div>
                            <div id="expired-message" class="text-center d-none">
                                <div class="alert alert-danger border-0 shadow-sm d-inline-block px-5 py-3 rounded-3">
                                    <i class="bi bi-exclamation-octagon-fill fs-1 d-block mb-2"></i>
                                    <h5 class="fw-bold text-uppercase mb-0">Waktu Habis</h5>
                                    <small>Pengisian bank soal telah ditutup.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar"
                        style="width: 100%"></div>
                </div>
            </div>

            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                    <div class="card-tools ms-auto">
                        <a href="<?= base_url('cbtbanksoal?id=' . $id_guru . '&mode=' . $mode) ?>" type="button"
                            onclick="" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </a>
                        <a href="<?= base_url('cbtbanksoal/addBank') ?>" type="button"
                            class="btn btn-primary btn-sm ms-1 shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Bank Soal
                        </a>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#openAllBankSoal"
                            class="btn btn-sm btn-success shadow-sm ms-1"><i class="bi bi-copy me-1"></i> Copy Bank Soal
                        </button>
                        <div class="btn-group">
                            <?php $activeGrid = $mode == '1' ? ' active' : ''; ?>
                            <?php $activeList = $mode == '2' ? ' active' : ''; ?>
                            <a href="<?= base_url('cbtbanksoal?mode=1&id=' . $id_guru) ?>" type="button"
                                class="btn btn-sm btn-primary<?= $activeGrid ?>"><i class="bi bi-list"></i></a>
                            <a href="<?= base_url('cbtbanksoal?mode=2&id=' . $id_guru) ?>" type="button"
                                class="btn btn-sm btn-primary<?= $activeList ?>"><i class="bi bi-grid-fill"></i></a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="badge bg-danger text-white">Kode Warna Bank Soal:</h5>
                    <div class="row ms-1">
                        <table class="w-100">
                            <tr>
                                <td class="p-1" style="width: 20px"><i class="bi bi-square-fill text-secondary"></i>
                                </td>
                                <td class="p-1">Tidak digunakan (bisa dihapus)</td>
                            </tr>
                            <tr>
                                <td class="p-1"><i class="bi bi-square-fill text-primary"></i></td>
                                <td class="p-1">Digunakan jadwal (non-aktifkan jadwal jika ingin diedit)</td>
                            </tr>
                            <tr>
                                <td class="p-1"><i class="bi bi-square-fill text-danger"></i></td>
                                <td class="p-1">Digunakan siswa (non-aktifkan jadwal jika ingin diedit)</td>
                            </tr>
                        </table>
                    </div>
                    <hr>
                    <div class="row" id="row-filter">
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
                                            <i class="bi bi-trash"></i> Hapus terpilih
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
                    <div class="row" id="konten">
                        <?php
                        if ($mode == '1'):
                            if (isset($banks[$tp_active->id_tp][$smt_active->id_smt]) && count($banks[$tp_active->id_tp][$smt_active->id_smt]) > 0): ?>
                                <table class="w-100 table table-striped table-bordered dataTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle p-0">No.</th>
                                            <th class="align-middle">Kode</th>
                                            <th class="align-middle">Mapel</th>
                                            <th class="align-middle">Kelas</th>
                                            <th class="align-middle">Sudah<br />Import</th>
                                            <th class="text-center align-middle p-0"><span>Aksi</span></th>
                                            <th class="text-center align-middle p-0" style="width: 50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($banks[$tp_active->id_tp][$smt_active->id_smt] as $bank): ?>
                                            <?php
                                            $jumlahKelas = $bank->bank_kelas;
                                            $jks = [];
                                            $kelasbank = '';
                                            foreach ((array) $jumlahKelas as $j) {
                                                foreach ($kelas as $k) {
                                                    if ((isset($j->kelas_id) && isset($k->id_kelas)) && $j->kelas_id == $k->id_kelas) {
                                                        $kelasbank .= '<span class="badge badge-btn bg-primary">' . $k->nama_kelas . '</span> ';
                                                    }
                                                }
                                            }
                                            $terpakai = true;
                                            $bgRandom = 'text-danger';
                                            $disable_edit = 'btn-disabled';
                                            if ($bank->digunakan == '0') {
                                                $bgRandom = 'text-secondary';
                                                $disable_edit = '';
                                            } else {
                                                $terpakai = isset($total_siswa[$bank->id_bank]);
                                                $bgRandom = $terpakai ? 'text-danger' : 'text-primary';
                                                $disable_edit = $terpakai ? 'btn-disabled' : $disable_edit;
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center align-middle"><?= $no ?></td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="bi bi-square-fill fs-4 me-2 <?= $bgRandom ?>"></i>
                                                        <span class="fw-bold"><?= $bank->bank_kode ?></span>
                                                    </div>
                                                    <?php
                                                    $target = (int) $bank->tampil_pg + (int) $bank->tampil_kompleks + (int) $bank->tampil_jodohkan + (int) $bank->tampil_isian + (int) $bank->tampil_esai + (int) ($bank->tampil_urutan ?? 0) + (int) ($bank->tampil_cloze ?? 0);
                                                    if ($target > 0) {
                                                        $actual = min((int) $bank->total_pg, (int) $bank->tampil_pg) +
                                                            min((int) $bank->total_kompleks, (int) $bank->tampil_kompleks) +
                                                            min((int) $bank->total_jodohkan, (int) $bank->tampil_jodohkan) +
                                                            min((int) $bank->total_isian, (int) $bank->tampil_isian) +
                                                            min((int) $bank->total_esai, (int) $bank->tampil_esai) +
                                                            min((int) $bank->total_urutan, (int) ($bank->tampil_urutan ?? 0)) +
                                                            min((int) $bank->total_cloze, (int) ($bank->tampil_cloze ?? 0));
                                                        $percent = round(($actual / $target) * 100);
                                                        $pColor = 'bg-danger';
                                                        if ($percent > 40)
                                                            $pColor = 'bg-warning';
                                                        if ($percent > 70)
                                                            $pColor = 'bg-info';
                                                        if ($percent == 100)
                                                            $pColor = 'bg-success';
                                                        ?>
                                                        <div class="d-flex flex-column v-stack gap-1">
                                                            <div class="progress rounded-pill"
                                                                style="height: 6px; width: 100%; min-width: 100px; background-color: #f0f0f0;">
                                                                <div class="progress-bar <?= $pColor ?> rounded-pill" role="progressbar"
                                                                    style="width: <?= $percent ?>%" aria-valuenow="<?= $percent ?>"
                                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                            <small class="text-muted d-flex justify-content-between"
                                                                style="font-size: 0.65rem;">
                                                                <span><i class="bi bi-graph-up me-1"></i>Progres</span>
                                                                <span><?= $actual ?>/<?= $target ?> (<?= $percent ?>%)</span>
                                                            </small>
                                                        </div>
                                                    <?php } ?>
                                                </td>
                                                <td class="align-middle"><span class="badge bg-warning ms-2"><i
                                                            class="bi bi-journal-bookmark me-1"></i> <?= $bank->nama_mapel ?>
                                                    </span> <br> <span class="badge bg-info ms-2"><i class="bi bi-person me-1"></i>
                                                        <?= $bank->nama_guru ?></span>
                                                    <br /> <span
                                                        class="badge bg-success ms-2"><?= $bank->soal_agama != '-' ? $bank->soal_agama : 'Umum' ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle"><?= $kelasbank ?></td>
                                                <td class="text-center w-auto">
                                                    <?php if ($bank->total_soal == 0): ?>
                                                        <i class="bi bi-x-lg text-danger"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-check-lg text-success"></i>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-center w-auto">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="<?= $disable_edit == ''
                                                            ? base_url('cbtbanksoal/editBank?id_bank='
                                                                . $bank->id_bank . '&id_guru='
                                                                . $bank->id_guru) : 'javascript:void(0)' ?>"
                                                            class="btn btn-warning btn-sm shadow-sm <?= $disable_edit ?>"
                                                            style="<?= $disable_edit == '' ? '' : 'cursor: not-allowed' ?>"
                                                            data-bs-toggle="tooltip" title="Edit Bank Soal">
                                                            <i class="bi bi-pencil text-white"></i>
                                                        </a>
                                                        <button data-total="<?= $bank->total_soal ?>"
                                                            data-id="<?= $bank->id_bank ?>" onclick="importSoal(this)" type="button"
                                                            <?= $disable_edit == '' ? '' : 'disabled' ?>
                                                            class="btn btn-info btn-sm shadow-sm <?= $disable_edit ?>"
                                                            data-bs-toggle="tooltip" title="Import Soal">
                                                            <i class="bi bi-upload text-white"></i>
                                                        </button>
                                                        <a href="<?= base_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                            type="button" class="btn btn-success btn-sm shadow-sm"
                                                            data-bs-toggle="tooltip"
                                                            title="<?= $bank->total_soal == 0 ? 'Buat Soal' : 'Detail Soal' ?>">
                                                            <i
                                                                class="bi <?= $bank->total_soal == 0 ? 'bi-plus-lg' : 'bi-search' ?> text-white"></i>
                                                        </a>
                                                        <button data-id="<?= $bank->id_bank ?>"
                                                            data-mapel="<?= $bank->nama_mapel ?>"
                                                            data-level="<?= $bank->bank_level ?>"
                                                            data-essai="<?= $bank->tampil_esai ?>" onclick="getSoal(this)"
                                                            type="button" class="btn btn-primary btn-sm shadow-sm"
                                                            data-bs-toggle="tooltip" title="Download">
                                                            <i class="bi bi-download text-white"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input name="checked[]" value="<?= $bank->id_bank ?>" class="check-bank"
                                                        type="checkbox" style="width: 20px;height: 20px">
                                                </td>
                                            </tr>
                                            <?php
                                            $no++;
                                        endforeach; ?>
                                    </tbody>
                                </table>
                            <?php elseif (isset($banks[0]) && isset($banks[0][0]) && count($banks[0][0]) > 0): ?>
                                <table class="w-100 table table-striped table-bordered dataTable" style="height: 300px">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle p-0" style="width: 50px"></th>
                                            <th class="text-center align-middle p-0">No.</th>
                                            <th class="align-middle">Kode</th>
                                            <th class="align-middle">Mapel</th>
                                            <th class="align-middle">Kelas</th>
                                            <th class="text-center align-middle p-0"><span>Aksi</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($banks[0][0] as $bank): ?>
                                            <?php
                                            $jumlahKelas = $bank->bank_kelas;
                                            $jks = [];

                                            $kelasbank = '';
                                            foreach ($jumlahKelas as $j) {
                                                foreach ($kelas as $k) {
                                                    if ((isset($j->kelas_id) && isset($k->id_kelas)) && $j->kelas_id == $k->id_kelas) {
                                                        $kelasbank .= '<span class="badge badge-btn bg-primary">' . $k->nama_kelas . '</span> ';
                                                    }
                                                }
                                            }
                                            $terpakai = true;
                                            $bgRandom = 'bg-gradient-maroon';
                                            $disable_edit = 'btn-disabled';
                                            if ($bank->digunakan == '0') {
                                                $bgRandom = 'bg-gradient-gray';
                                                $disable_edit = '';
                                            } else {
                                                $terpakai = isset($total_siswa[$bank->id_bank]);
                                                $bgRandom = $terpakai ? 'bg-gradient-maroon' : 'bg-gradient-yellow';
                                                $disable_edit = $terpakai ? 'btn-disabled' : $disable_edit;
                                            }
                                            ?>
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <input name="checked[]" value="<?= $bank->id_bank ?>" class="check-bank"
                                                        type="checkbox" style="width: 20px;height: 20px">

                                                </td>
                                                <td class="text-center align-middle"><?= $no ?></td>
                                                <td class="align-middle"><?= $bank->bank_kode ?></td>
                                                <td class="align-middle"><?= $bank->nama_mapel ?>
                                                    <br /> <span
                                                        class="badge bg-light"><?= $jadwal->soal_agama != '-' ? $jadwal->soal_agama : 'Umum' ?></span>
                                                </td>
                                                <td class="align-middle"><?= $kelasbank ?></td>
                                                <td class="align-middle">
                                                    <span data-bs-toggle="tooltip" title="Edit Bank Soal">
                                                        <a type="button" href="<?= $disable_edit == ''
                                                            ? base_url('cbtbanksoal/editBank?id_bank='
                                                                . $bank->id_bank . '&id_guru='
                                                                . $bank->id_guru) : 'javascript:void(0)' ?>"
                                                            class="btn btn-warning btn-sm mb-1 <?= $disable_edit ?>"
                                                            style="<?= $disable_edit == '' ? '' : 'cursor: not-allowed' ?>">
                                                            <i class="bi bi-pencil"></i></a>
                                                    </span>
                                                    <span data-bs-toggle="tooltip" title="Import Soal">
                                                        <button data-total="<?= $bank->total_soal ?>"
                                                            data-id="<?= $bank->id_bank ?>" onclick="importSoal(this)" type="button"
                                                            <?= $disable_edit == '' ? '' : 'disabled' ?>
                                                            class="btn btn-warning btn-sm mb-1 <?= $disable_edit ?>">
                                                            <i class="bi bi-upload"></i> Import Soal</button>
                                                    </span>

                                                    <span data-bs-toggle="tooltip" title="Buat Soal">
                                                        <a href="<?= base_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                            type="button" class="btn btn-success">
                                                            <?php if ($bank->total_soal == 0): ?>
                                                                <i class="bi bi-plus-lg"></i> Buat Soal
                                                            <?php else: ?>
                                                                <i class="bi bi-search"></i> Detail Soal
                                                            <?php endif; ?>
                                                        </a>
                                                    </span>

                                                    <button data-id="<?= $bank->id_bank ?>" data-mapel="<?= $bank->nama_mapel ?>"
                                                        data-level="<?= $bank->bank_level ?>" data-essai="<?= $bank->tampil_esai ?>"
                                                        onclick="getSoal(this)" type="button" class="btn btn-primary">
                                                        <i class="bi bi-download me-1"> </i>Download<br>
                                                    </button>

                                                </td>
                                            </tr>
                                            <?php
                                            $no++;
                                        endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="col-12 p-0">
                                    <?php
                                    if ($id_filter == ''): ?>
                                        <div class="alert alert-warning align-content-center pb-0" role="alert">
                                            <ul>
                                                <li>
                                                    Silakan pilih <b>Filter</b> untuk menampilkan BANK SOAL
                                                </li>
                                                <li>
                                                    Memilih filter <b>SEMUA</b> mungkin akan memakan waktu <span
                                                        class="text-danger text-bold">lebih lama</span>.
                                                </li>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning align-content-center" role="alert">
                                            Belum ada BANK SOAL
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif;
                        else:
                            if (isset($banks[$tp_active->id_tp]) && isset($banks[$tp_active->id_tp][$smt_active->id_smt]) && count($banks[$tp_active->id_tp][$smt_active->id_smt]) > 0):
                                foreach ($banks[$tp_active->id_tp][$smt_active->id_smt] as $bank):
                                    $terpakai = true;
                                    $bgRandom = 'bg-gradient-danger';
                                    $disable_edit = 'btn-disabled';
                                    if ($bank->digunakan == '0') {
                                        $bgRandom = 'bg-gradient-secondary';
                                        $disable_edit = '';
                                    } else {
                                        $terpakai = isset($total_siswa[$bank->id_bank]);
                                        $bgRandom = $terpakai ? 'bg-gradient-danger' : 'bg-gradient-primary';
                                        $disable_edit = $terpakai ? 'btn-disabled' : $disable_edit;
                                    }
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border mb-4">
                                            <div class="card-header border-bottom-0 <?= $bgRandom ?>">
                                                <h3 class="card-title mt-1"><b><?= $bank->bank_kode ?></b></h3>
                                                <div class="card-tools ms-auto">

                                                    <span data-bs-toggle="tooltip" title="Edit Bank Soal">
                                                        <a href="<?= $disable_edit == ''
                                                            ? base_url('cbtbanksoal/editBank?id_bank='
                                                                . $bank->id_bank . '&id_guru='
                                                                . $bank->id_guru) : 'javascript:void(0)' ?>"
                                                            class="btn btn-warning me-1 <?= $disable_edit ?>"
                                                            style="<?= $disable_edit == '' ? '' : 'cursor: not-allowed' ?>">
                                                            <i class="bi bi-pencil"></i></a>
                                                    </span>
                                                    <button class="btn btn-default">
                                                        <input name="checked[]" value="<?= $bank->id_bank ?>"
                                                            class="check-bank float-start" type="checkbox"
                                                            style="width: 20px;height: 20px">
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body pt-0">
                                                <?php
                                                $jumlahKelas = $bank->bank_kelas;
                                                $jks = [];

                                                $kelasbank = '';
                                                $no = 1;
                                                foreach ($jumlahKelas as $j) {
                                                    foreach ($kelas as $k) {
                                                        if ((isset($j->kelas_id) && isset($k->id_kelas)) && $j->kelas_id == $k->id_kelas) {
                                                            if ($no > 1) {
                                                                $kelasbank .= ', ';
                                                            }
                                                            $kelasbank .= $k->nama_kelas;
                                                            $no++;
                                                        }
                                                    }
                                                }
                                                ?>
                                                <ul class="list-group list-group-unbordered">
                                                    <li class="list-group-item p-1"> Guru
                                                        <span class="float-end"><b><?= $bank->nama_guru ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Mapel
                                                        <span class="float-end"><b><?= $bank->bank_kode ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Kelas
                                                        <span class="float-end"><b><?= $kelasbank ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Agama
                                                        <span
                                                            class="float-end"><b><?= $bank->soal_agama != '-' ? $bank->soal_agama : 'Umum' ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Jumlah Soal
                                                        <span class="float-end">
                                                            <b><?= $bank->total_soal == 0 ? 'Belum dibuat' : ($bank->total_soal < ($bank->tampil_pg + $bank->tampil_esai) ? 'Belum selesai' : $bank->tampil_pg + $bank->tampil_kompleks + $bank->tampil_jodohkan + $bank->tampil_isian + $bank->tampil_esai) ?></b>
                                                        </span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Dibuat
                                                        <span
                                                            class="float-end"><b><?= singkat_tanggal(date('d M Y - H:i', strtotime($bank->date ?? ''))) ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Status
                                                        <span class="float-end">
                                                            <b><?= ($bank->status == '0') ? 'Non Aktif' : 'Aktif' ?></b>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="card-footer">
                                                <div class="row mb-2">
                                                    <span class="col-6 text-start" data-bs-toggle="tooltip" title="Buat Soal">
                                                        <button data-total="<?= $bank->total_soal ?>"
                                                            data-id="<?= $bank->id_bank ?>" onclick="importSoal(this)" type="button"
                                                            <?= $disable_edit == '' ? '' : 'disabled' ?>
                                                            class="btn btn-warning <?= $disable_edit ?>">
                                                            <i class="bi bi-upload"></i> Import Soal</button>
                                                    </span>

                                                    <span class="col-6 text-end" data-bs-toggle="tooltip" title="Buat Soal">
                                                        <a href="<?= base_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                            type="button" class="btn btn-success">
                                                            <?php if ($bank->total_soal == 0): ?>
                                                                <i class="bi bi-plus-lg"></i> Buat Soal
                                                            <?php else: ?>
                                                                <i class="bi bi-search"></i> Detail Soal
                                                            <?php endif; ?>
                                                        </a>
                                                    </span>
                                                </div>
                                                <div class="row">
                                                    <button data-id="<?= $bank->id_bank ?>" data-mapel="<?= $bank->nama_mapel ?>"
                                                        data-level="<?= $bank->bank_level ?>" data-essai="<?= $bank->tampil_esai ?>"
                                                        onclick="getSoal(this)" type="button" class="btn btn-primary w-100">
                                                        <i class="bi bi-download me-1"></i> Download Soal Untuk
                                                        Siswa<br>
                                                        <small><i>untuk keperluan ujian kertas</i></small>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach;
                            elseif (isset($banks[0]) && isset($banks[0][0]) && count($banks[0][0]) > 0):
                                foreach ($banks[0][0] as $bank): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card border mb-4">
                                            <div class="card-header border-bottom-0">
                                                <h3 class="card-title mt-1"><b><?= $bank->bank_kode ?></b></h3>
                                                <div class="card-tools ms-auto">
                                                    <span data-bs-toggle="tooltip" title="Edit Bank Soal">
                                                        <a type="button"
                                                            href="<?= base_url('cbtbanksoal/editBank?id_bank=' . $bank->id_bank . '&id_guru=' . $bank->id_guru) ?>"
                                                            class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil"></i></a>
                                                    </span>
                                                    <button class="btn btn-danger">
                                                        <input name="checked[]" value="<?= $bank->id_bank ?>"
                                                            class="check-bank float-start" type="checkbox"
                                                            style="width: 20px;height: 20px">
                                                    </button>
                                                    <!--
                                                <button onclick="hapus(<?= $bank->id_bank ?>)" type="button"
                                                        class="btn-sm btn btn-danger" data-bs-toggle="tooltip"
                                                        title="Hapus Bank Soal">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                -->
                                                </div>
                                            </div>
                                            <div class="card-body pt-0">
                                                <?php
                                                $jumlahKelas = $bank->bank_kelas;
                                                $jks = [];

                                                $kelasbank = '';
                                                $no = 1;
                                                foreach ($jumlahKelas as $j) {
                                                    foreach ($kelas as $k) {
                                                        if ((isset($j->kelas_id) && isset($k->id_kelas)) && $j->kelas_id == $k->id_kelas) {
                                                            if ($no > 1) {
                                                                $kelasbank .= ', ';
                                                            }
                                                            $kelasbank .= $k->nama_kelas;
                                                            $no++;
                                                        }
                                                    }
                                                }
                                                ?>
                                                <ul class="list-group list-group-unbordered">
                                                    <li class="list-group-item p-1"> Guru
                                                        <span class="float-end"><b><?= $bank->nama_guru ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Mapel
                                                        <span class="float-end"><b><?= $bank->kode ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Kelas
                                                        <span class="float-end"><b><?= $kelasbank ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Agama
                                                        <span
                                                            class="float-end"><b><?= $bank->soal_agama != '-' ? $bank->soal_agama : 'Umum' ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Jumlah Soal
                                                        <span class="float-end">
                                                            <b><?= $bank->total_soal == 0 ? 'Belum dibuat' : ($bank->total_soal < ($bank->tampil_pg + $bank->tampil_esai) ? 'Belum selesai' : $bank->tampil_pg + $bank->tampil_kompleks + $bank->tampil_jodohkan + $bank->tampil_isian + $bank->tampil_esai) ?></b>
                                                        </span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Dibuat
                                                        <span
                                                            class="float-end"><b><?= singkat_tanggal(date('d M Y - H:i', strtotime($bank->date ?? ''))) ?></b></span>
                                                    </li>
                                                    <li class="list-group-item p-1"> Status
                                                        <span class="float-end">
                                                            <b><?= ($bank->status == '0') ? 'Non Aktif' : 'Aktif' ?></b>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="card-footer">
                                                <div class="row mb-2">
                                                    <span class="col-6 text-start" data-bs-toggle="tooltip" title="Buat Soal">
                                                        <a href="javascript:void(0)" data-total="<?= $bank->total_soal ?>"
                                                            data-id="<?= $bank->id_bank ?>" onclick="importSoal(this)" type="button"
                                                            class="btn btn-warning">
                                                            <i class="bi bi-upload"></i> Import Soal
                                                        </a>
                                                    </span>

                                                    <span class="col-6 text-end" data-bs-toggle="tooltip" title="Buat Soal">
                                                        <a href="<?= base_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                            type="button" class="btn btn-success">
                                                            <?php if ($bank->total_soal == 0): ?>
                                                                <i class="bi bi-plus-lg"></i> Buat Soal
                                                            <?php else: ?>
                                                                <i class="bi bi-search"></i> Detail Soal
                                                            <?php endif; ?>
                                                        </a>
                                                    </span>
                                                </div>
                                                <div class="row">
                                                    <button data-id="<?= $bank->id_bank ?>" data-mapel="<?= $bank->nama_mapel ?>"
                                                        data-level="<?= $bank->bank_level ?>" data-essai="<?= $bank->tampil_esai ?>"
                                                        onclick="getSoal(this)" type="button" class="btn btn-primary w-100">
                                                        <i class="bi bi-download me-1"></i> Download Soal Untuk
                                                        Siswa<br>
                                                        <small><i>untuk keperluan ujian kertas</i></small>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach;
                            else: ?>
                                <div class="col-12 p-0">
                                    <?php
                                    if ($id_filter == ''): ?>
                                        <div class="alert alert-warning align-content-center pb-0" role="alert">
                                            <ul>
                                                <li>
                                                    Silakan pilih <b>Filter</b> untuk menampilkan BANK SOAL
                                                </li>
                                                <li>
                                                    Memilih filter <b>SEMUA</b> mungkin akan memakan waktu <span
                                                        class="text-danger text-bold">lebih lama</span>.
                                                </li>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning align-content-center" role="alert">
                                            Belum ada BANK SOAL
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; endif; ?>
                    </div>
                </div>
            </div>

            <div id="for-siswa" class="d-none" style="width: 100%">
                <table id="table-header-print" style="width: 100%">
                    <tr>
                        <td>
                            <img id="prev-logo-kiri-print" src="<?= base_url() . $setting->logo_kiri ?>" width="80px"
                                height="80px">
                        </td>
                        <td class="text-center" style="text-align: center;">
                            <div class="text-center" id="jenis-ujian"
                                style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 16pt"></div>
                            <div class="text-center"
                                style="line-height: 1.1; font-family: 'Times New Roman'; font-size: 14pt">
                                <b><?= $setting->sekolah ?></b>
                            </div>
                            <div class="text-center"
                                style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 13pt">
                                <b>KECAMATAN
                                    <?= strtoupper($setting->kecamatan ?? '') . ' KOTA ' . strtoupper($setting->kota ?? '') ?></b>
                            </div>
                            <div class="text-center"
                                style="line-height: 1.2; font-family: 'Times New Roman'; font-size: 12pt"><b>TAHUN
                                    PELAJARAN:
                                    <?= strtoupper($tp_active->tahun ?? '') . ' SEMESTER ' . strtoupper($smt_active->smt ?? '') ?></b>
                            </div>
                        </td>
                        <td>
                            <img id="prev-logo-kanan-print" src="<?= base_url() . $setting->logo_kanan ?>" width="80px"
                                height="80px">
                        </td>
                    </tr>
                </table>
                <hr style="border: 1px solid; margin-bottom: 6px; margin-top: 0px">
                <br>
                <p><b>I. Soal Pilihan Ganda</b></p>
                <ol id="list-pg">
                </ol>

                <p>&nbsp;</p>
                <p><b>II. Soal Pilihan Ganda Kompleks</b></p>
                <ol id="list-pg2">
                </ol>

                <p>&nbsp;</p>
                <p><b>II. Soal Menjodohkan</b></p>
                <ol id="list-jodohkan">
                </ol>

                <p>&nbsp;</p>
                <p><b>II. Soal Isian Singkat</b></p>
                <ol id="list-isian">
                </ol>

                <p>&nbsp;</p>
                <p><b>II. Soal Uraian / Essai</b></p>
                <ol id="list-essai">
                </ol>

                <table id="jawaban-soal-siswa">
                </table>
            </div>

        </div>
    </section>
    </div>

    <div class="modal fade" id="openAllBankSoal" tabindex="-1" role="dialog" aria-labelledby="openBankSoalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <?php
                    if ($id_filter == '1') {
                        $guruName = isset($gurus[$id_guru]) ? $gurus[$id_guru] : '';
                        $title = 'Bank Soal (' . $guruName . ')';
                    } elseif ($id_filter == '2') {
                        $mapelName = isset($mapels[$id_mapel]) ? $mapels[$id_mapel] : '';
                        $title = 'Bank Soal (' . $mapelName . ')';
                    } elseif ($id_filter == '3') {
                        $levelName = isset($levels[$id_level]) ? $levels[$id_level] : '';
                        $title = 'Bank Soal Kelas ' . $levelName;
                    } else {
                        $title = 'Semua Bank Soal';
                    }
                    ?>
                    <h5 class="modal-title" id="openBankSoalLabel"><?= $title ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                    </button>
                </div>
                <div class="modal-body">
                    <?php
                    //var_dump($banks);
                    $allBanks = [];
                    foreach ($tp as $thn) {
                        foreach ($smt as $sem) {
                            if (isset($banks[$thn->id_tp]) && isset($banks[$thn->id_tp][$sem->id_smt])) {
                                $allBanks[] = [
                                    'tahun' => $thn->tahun . ' SMT ' . $sem->smt,
                                    'banks' => $banks[$thn->id_tp][$sem->id_smt]
                                ];
                            }
                            /*
                            if ($thn->id_tp != $tp_active->id_tp && $sem->id_smt != $smt_active->id_smt) {
                                if (isset($banks[$thn->id_tp]) && isset($banks[$thn->id_tp][$sem->id_smt])) {
                                    //$arrBank[] = $banks[$thn->id_tp][$sem->id_smt];
                                    $allBanks[] = [
                                        'tahun' => $thn->tahun . ' SMT ' . $sem->smt,
                                        'banks' => $banks[$thn->id_tp][$sem->id_smt]
                                    ];
                                }
                            }
                            */
                        }
                    }
                    usort($allBanks, function ($a, $b) {
                        return $b['tahun'] <=> $a['tahun'];
                    });
                    //echo '<pre>';
                    //var_dump($allBanks);
                    //echo '</pre>';
                    
                    if (count($allBanks) > 0):
                        foreach ($allBanks as $allBank): ?>
                            <p><?= $allBank['tahun'] ?></p>
                            <div class="table-responsive">
                                <table id="tableEkstra"
                                    class="w-100 table table-striped table-bordered table-hover table-head-fixed overflow-auto display nowrap"
                                    style="height: 300px">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle p-0">No.</th>
                                            <th>Kode</th>
                                            <th>Mapel</th>
                                            <th>Kelas</th>
                                            <th>TP/SMT</th>
                                            <th class="text-center align-middle p-0" style="width: 100px"><span>Aksi</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($allBank['banks'] as $ab):
                                            ?>
                                            <tr>
                                                <td><?= $no ?></td>
                                                <td><?= $ab->bank_kode ?></td>
                                                <td><?= $ab->nama_mapel ?></td>
                                                <td><?= $ab->bank_level ?></td>
                                                <td><?= $allBank['tahun'] ?></td>
                                                <td>
                                                    <button onclick="copy(<?= $ab->id_bank ?>)" type="button"
                                                        class="btn btn-sm btn-success"><i class="bi bi-copy"></i> Copy
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                            $no++;
                                        endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; else: ?>
                        <div class="alert alert-default-info align-content-center" role="alert">
                            Menu Copy <?= $judul ?> ini tampil jika Filter dipilih terlebih dahulu atau tidak ada
                            <?= $judul ?>
                            sebelumnya.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary border px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/FileSaver.min.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/jquery.wordexport.js"></script>
    <script type="text/javascript" src="<?= base_url() ?>/assets/app/js/html-docx.js"></script>
    <script src="<?= base_url() ?>/assets/app/js/convert-area.js"></script>

    <script>
        var idFilter = '<?= $id_filter ?>';
        var idMapel = '<?= $id_mapel ?>';
        var idLevel = '<?= $id_level ?>';
        var idGuru = '<?= $id_guru ?>';
        var mode = '<?= $mode ?>';
        // Ambil deadline dari server (PHP)
        var deadlineServer = '<?= $deadline ?>';

        $(document).ready(function () {
            ajaxcsrf();

            // Init Countdown if deadline exists
            if (deadlineServer && deadlineServer != '' && deadlineServer != '0000-00-00 00:00:00') {
                $('#card-countdown').show();
                // Replace space with T for better cross-browser compatibility
                initCountdown(deadlineServer.replace(' ', 'T'));
            }

            var count = $('#konten .check-bank').length;
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

            function initCountdown(endTime) {
                var countDownDate = new Date(endTime).getTime();

                // Update deadline info text
                var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                var deadDate = new Date(endTime);
                $('#deadline-text').text(deadDate.toLocaleDateString('id-ID', options));

                var x = setInterval(function () {
                    var now = new Date().getTime();
                    var distance = countDownDate - now;

                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Add leading zeros
                    document.getElementById("days").innerText = days < 10 ? "0" + days : days;
                    document.getElementById("hours").innerText = hours < 10 ? "0" + hours : hours;
                    document.getElementById("minutes").innerText = minutes < 10 ? "0" + minutes : minutes;
                    document.getElementById("seconds").innerText = seconds < 10 ? "0" + seconds : seconds;

                    if (distance < 0) {
                        clearInterval(x);
                        document.getElementById("countdown-timer").classList.add("d-none");
                        document.getElementById("expired-message").classList.remove("d-none");
                        document.getElementById("days").innerText = "00";
                        document.getElementById("hours").innerText = "00";
                        document.getElementById("minutes").innerText = "00";
                        document.getElementById("seconds").innerText = "00";
                    }
                }, 1000);
            }

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
                    window.location.href = base_url + 'cbtbanksoal?type=0&mode=' + mode;
                } else {
                    onChangeFilter(type);
                }
            });

            $('.sel').on('change', function () {
                var id = $(this).val();
                window.location.href = base_url + 'cbtbanksoal?id=' + id + '&type=' + opsiFilter.val() + '&mode=' + mode;
            });
            /*
            $('#guru').on('change', function () {
                idGuru = $(this).val();
                window.location.href = base_url + 'cbtbanksoal?id=' + idGuru;
            });
            */

            onChangeFilter(opsiFilter.val());

            var unchecked = [];
            var checked = [];

            function findUnchecked() {
                unchecked = [];
                checked = [];

                $("#konten .check-bank:not(:checked)").each(function () {
                    unchecked.push($(this).val());
                });
                $("#konten .check-bank:checked").each(function () {
                    checked.push($(this).val());
                });
                var countChecked = $("#konten .check-bank:checked").length;
                $("#check-all").prop("checked", countChecked == count);

                $("#submit-hapus").attr('disabled', countChecked == 0);
            }

            $("#konten").on("change", ".check-bank", function () {
                findUnchecked();
            });

            $("#check-all").on("click", function () {
                if (count > 0) {
                    if (this.checked) {
                        $(".check-bank").each(function () {
                            this.checked = true;
                            $("#check-all").prop("checked", true);
                        });
                    } else {
                        $(".check-bank").each(function () {
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

                var dataPost = $(this).serialize() + "&ids=" + JSON.stringify(checked);
                console.log(dataPost);
                if (checked.length == 0) {
                    Swal.fire({
                        title: "",
                        text: "Pilih Bank Soal yang akan dihapus",
                        icon: "warning"
                    });
                    return;
                }

                Swal.fire({
                    title: "Anda yakin?",
                    text: "Semua Bank Soal yang saat ini ditampilkan akan dihapus!",
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
                            url: base_url + 'cbtbanksoal/deleteallbank',
                            type: "POST",
                            data: dataPost,
                            success: function (respon) {
                                if (respon.status) {
                                    Swal.fire({
                                        title: "Berhasil",
                                        text: "Semua Bank soal berhasil dihapus",
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
                            error: function () {
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
            })
        });

        function filterBy() {
            console.log('find', $('#filter-by').val());
            $('.card').show();
            var filter = $('#filter-by').val(); // get the value of the input, which we filter on
            $('#konten').find(".card-body:not(:contains(" + filter + "))").parent().parent().css('display', 'none');
        }

        function getSoal(e) {
            var id = $(e).data('id');
            var level = $(e).data('level');
            var mapel = $(e).data('mapel');
            var jmlEssai = $(e).data('essai');
            $.ajax({
                url: base_url + 'cbtbanksoal/getsoalsiswa/' + id,
                type: "GET",
                success: function (respon) {
                    console.log(respon);
                    if (respon.soal.length > 0) {
                        var pg1 = '';
                        var pg2 = '';
                        var jodoh = '';
                        var isian = '';
                        var essais = '';

                        var jwbnPgSiswa = '<tr>' +
                            '    <td colspan="3" class="pt-2">Jawaban Pilihan Ganda</td>' +
                            '</tr>';
                        var jwbnPg2Siswa = '<tr>' +
                            '    <td colspan="3" class="pt-2">Jawaban Pilihan Ganda Kompleks</td>' +
                            '</tr>';
                        var jwbnJodohkanSiswa = '<tr>' +
                            '    <td colspan="3">Jawaban Menjodohkan</td>' +
                            '</tr>';
                        var jwbnIsianSiswa = '<tr>' +
                            '    <td colspan="3">Jawaban Isian Singkat</td>' +
                            '</tr>';
                        var jwbnEssaiSiswa = '<tr>' +
                            '    <td colspan="3">Jawaban Uraian</td>' +
                            '</tr>';

                        $.each(respon.soal, function (i, v) {
                            if (v.jenis == '1') {
                                jwbnPgSiswa += '<tr>' +
                                    '<td class="align-top" style="width: 30px"><b>' + v.nomor_soal + '.</b><td>' +
                                    '<td>' + v.jawaban + '<td>' +
                                    '</tr>';

                                pg1 += '<li style="margin-bottom: 12px">' +
                                    v.soal +
                                    '<ol type="a">' +
                                    '<li>' + v.opsi_a + '</li>' +
                                    '<li>' + v.opsi_b + '</li>' +
                                    '<li>' + v.opsi_c + '</li>' +
                                    '<li>' + v.opsi_d + '</li>';
                                if ('<?= $setting->jenjang ?>' == '3') {
                                    pg1 += '<li>' + v.opsi_e + '</li>';
                                }
                                pg1 += '</ol>' + '</li>';

                            } else if (v.jenis == '2') {
                                jwbnPg2Siswa += '<tr>' +
                                    '<td class="align-top" style="width: 30px"><b>' + v.nomor_soal + '.</b><td>' +
                                    '<td>' + v.jawaban + '<td>' +
                                    '</tr>';

                                pg2 += '<li style="padding-bottom: 12px">' + v.soal +
                                    '<ol type="a">';
                                $.each(v.opsi_a, function (abjad, opsi) {
                                    pg2 += '<li>' + opsi + '</li>';
                                });
                                pg2 += '</ol>' + '</li>';
                            } else if (v.jenis == '3') {
                                var jawaban = '<table class="mb-3" style="width:100%; border: 1px solid black; border-collapse: collapse; border-spacing: 0;">';
                                $.each(v.jawaban.jawaban, function (i, kolom) {
                                    var bgkol = i == 0 ? 'lightgrey' : 'white';
                                    jawaban += '<tr style="background: ' + bgkol + '">';
                                    $.each(kolom, function (row, td) {
                                        var val = td == "1" ? "✔" : (td == "0" || td == "#" ? "" : td);
                                        var bg = row == 0 ? ' background: lightgrey;' : '';
                                        jawaban += '<td style="border: 1px solid black;border-collapse: collapse; text-align: center;' + bg + '">' + val + '</td>';
                                    });
                                    jawaban += '</tr>';
                                });
                                jawaban += '</table>';
                                jwbnJodohkanSiswa += '<tr>' +
                                    '<td class="align-top" style="width: 30px"><b>' + v.nomor_soal + '.</b><td>' +
                                    '<td>'
                                    + jawaban +
                                    '<td>' +
                                    '</tr>';

                                jodoh += '<li style="padding-bottom: 12px">' + v.soal +
                                    '<table style="width:100%; border: 1px solid black; border-collapse: collapse; border-spacing: 0;">';
                                $.each(v.jawaban.jawaban, function (i, kolom) {
                                    var bgkol = i == 0 ? 'lightgrey' : 'white';
                                    jodoh += '<tr style="background: ' + bgkol + '">';
                                    $.each(kolom, function (row, td) {
                                        var val = td == "0" || td == "1" || td == "#" ? "" : td;
                                        var bg = row == 0 ? ' background: lightgrey;' : '';
                                        jodoh += '<td style="border: 1px solid black;border-collapse: collapse; text-align: center;' + bg + '">' + val + '</td>';
                                    });
                                    jodoh += '</tr>';
                                });
                                jodoh += '</table></li>';
                            } else if (v.jenis == '4') {
                                jwbnIsianSiswa += '<tr>' +
                                    '<td class="align-top" style="width: 30px"><b>' + v.nomor_soal + '.</b><td>' +
                                    '<td>' + v.jawaban + '<td>' +
                                    '</tr>';
                                isian += '<li style="padding-bottom: 12px; display: flex; align-items: baseline;">' +
                                    '<span style="margin-right: 12px; padding: 0;">' + v.soal + '</span>' +
                                    '<span style="flex: 1; overflow: hidden; margin: 0; padding: 0;">.............' +
                                    '......................................</span></li>';
                            } else if (v.jenis == '5') {
                                jwbnEssaiSiswa += '<tr>' +
                                    '<td class="align-top" style="width: 30px"><b>' + v.nomor_soal + '.</b><td>' +
                                    '<td>' + v.jawaban + '<td>' +
                                    '</tr>';
                                essais += '<li style="padding-bottom: 24px;"><section style="display: flex; align-items: baseline; margin-bottom: 8px">' +
                                    '<span style="margin-right: 12px; padding: 0;">' + v.soal + '</span>' +
                                    '<span style="flex: 1; overflow: hidden;">.............' +
                                    '.............................................................................' +
                                    '.............................................................................' +
                                    '.............................................................................</span></section>';
                                for (let j = 0; j < 5; j++) {
                                    essais += '<section style="display: flex; align-items: baseline; margin-bottom: 8px">' +
                                        '<span style="flex: 1; overflow: hidden;">.............' +
                                        '.............................................................................' +
                                        '.............................................................................' +
                                        '.............................................................................' +
                                        '.......................' +
                                        '</span></section>';
                                }
                                essais += '</li>';
                            } else {
                                //
                            }
                        });

                        $('#list-pg').html(pg1);
                        $('#list-pg2').html(pg2);
                        $('#list-jodohkan').html(jodoh);
                        $('#list-isian').html(isian);
                        $('#list-essai').html(essais);

                        $('#jawaban-soal-siswa').html(jwbnPgSiswa + jwbnPg2Siswa + jwbnJodohkanSiswa + jwbnIsianSiswa + jwbnEssaiSiswa);

                        $('#list-essai p').css({ padding: 0, margin: 0 });

                        $('#for-siswa img').each(function () {
                            var curSrc = $(this).attr('src');
                            if (curSrc.indexOf("http") == -1 && curSrc.indexOf("data:image") == -1) {
                                $(this).attr('src', base_url + curSrc);
                            } else if (curSrc.indexOf(base_url) == -1) {
                                var pathUpload = 'uploads';
                                var forReplace = curSrc.split(pathUpload);
                                $(this).attr('src', base_url + pathUpload + forReplace[1]);
                            }
                            /*
                            var curSrc = $(this).attr('src');
                            if (curSrc.indexOf("http") == -1 && curSrc.indexOf("data:image") == -1) {
                                $(this).attr('src', base_url + curSrc);
                            }
                            */
                        });

                        setTimeout(function () {
                            $("#for-siswa").wordExport(`Soal ${mapel} Kls ${level}`);
                        }, 500);

                    } else {
                        Swal.fire({
                            title: "Gagal",
                            text: "Tidak bisa mendownload soal",
                            icon: "error"
                        });
                    }
                },
                error: function () {
                    const err = JSON.parse(xhr.responseText)
                    Swal.fire({
                        title: "Error",
                        text: err.Message,
                        icon: "error"
                    });
                }
            });
        }

        function exportWord(mapel, level) {
            var contentDocument = $('#for-siswa').convertToHtmlFile('ekspor', '');
            var content = '<!DOCTYPE html>' + contentDocument.documentElement.outerHTML;
            var converted = htmlDocx.asBlob(
                content, {
                orientation: 'portrait',
                size: 'A4',
                margins: {
                    top: 700,
                    bottom: 700,
                    left: 700,
                    right: 700
                }
            });

            saveAs(converted, 'Soal ' + mapel + ' Kls ' + level + '.docx');
        }

        function importSoal(btn) {
            var idBank = $(btn).data('id');
            var total = $(btn).data('total');
            if (total > 0) {
                Swal.fire({
                    title: "Import Soal?",
                    html: "Soal sudah ada, mengimport soal baru akan menghapus soal terdahulu.<br>Lanjutkan import soal?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Lanjut"
                }).then(result => {
                    if (result.value) {
                        window.location.href = base_url + 'cbtbanksoal/importsoal/' + idBank;
                    }
                });
            } else {
                window.location.href = base_url + 'cbtbanksoal/importsoal/' + idBank;
            }
        }

        function hapus(id) {
            Swal.fire({
                title: "Anda yakin?",
                text: "Bank Soal akan dihapus!",
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
                        url: base_url + 'cbtbanksoal/deleteBank?id_bank=' + id,
                        //data: {id_bank: id},
                        type: "GET",
                        success: function (respon) {
                            if (respon.status) {
                                Swal.fire({
                                    title: "Berhasil",
                                    text: "Bank soal berhasil dihapus",
                                    icon: "success"
                                }).then(result => {
                                    if (result.value) {
                                        window.location.reload();
                                        //window.location.href = base_url + 'cbtbanksoal?id=' + idGuru;
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
                        error: function () {
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

        function copy(id) {
            Swal.fire({
                title: "Copy Bank Soal?",
                html: " Bank Soal ini akan dicopy." + "<br /> Periksa kembali kelas bank soal setelah copy.",
                icon: "info",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Copy"
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
                        url: base_url + 'cbtbanksoal/copybanksoal/' + id,
                        type: "GET",
                        success: function (respon) {
                            if (respon) {
                                Swal.fire({
                                    title: "Berhasil",
                                    text: "Bank Soal berhasil dicopy",
                                    icon: "success"
                                }).then(result => {
                                    if (result.value) {
                                        window.location.reload();
                                    }
                                })
                            } else {
                                Swal.fire({
                                    title: "Gagal",
                                    text: "Tidak bisa mengcopy materi",
                                    icon: "error"
                                });
                            }
                        },
                        error: function () {
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

    </script>