<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $judul ?? 'Bank Soal' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><i
                                    class="bi bi-speedometer2 me-1"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active"><?= $judul ?? 'Bank Soal' ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php
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
                    <h3 class="card-title text-bold"><i class="bi bi-journal-text me-2"></i><?= $judul ?></h3>
                    <div class="card-tools ms-auto">
                        <button type="button" onclick="location.reload()" class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-repeat me-1"></i> Reload
                        </button>
                        <a href="<?= site_url('cbtbanksoal/addBank') ?>" class="btn btn-primary btn-sm ms-1 shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Bank Soal
                        </a>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#openAllBankSoal"
                            class="btn btn-sm btn-success shadow-sm ms-1">
                            <i class="bi bi-copy me-1"></i> Copy Bank Soal
                        </button>
                        <?php if (!$inGroup('panitia')): ?>
                            <button id="submit-hapus" class="btn btn-sm btn-danger shadow-sm ms-1" disabled>
                                <i class="bi bi-trash me-1"></i> Hapus Terpilih
                            </button>
                        <?php endif; ?>
                        <div class="btn-group">
                            <?php $activeGrid = $mode == '1' ? ' active' : ''; ?>
                            <?php $activeList = $mode == '2' ? ' active' : ''; ?>
                            <a href="<?= site_url('cbtbanksoal?mode=1' . ($id_filter != '' ? '&type=' . $id_filter : '') . (isset($id_guru) ? '&id=' . $id_guru : '')) ?>"
                                class="btn btn-sm btn-primary<?= $activeGrid ?>"><i class="bi bi-list"></i></a>
                            <a href="<?= site_url('cbtbanksoal?mode=2' . ($id_filter != '' ? '&type=' . $id_filter : '') . (isset($id_guru) ? '&id=' . $id_guru : '')) ?>"
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
                                        <?= form_dropdown('f', $filters, $id_filter, 'id="filter" class="form-control select2"') ?>
                                    </td>
                                    <td id="select-guru" class="d-none ps-2">
                                        <?= form_dropdown('guru', isset($gurus) ? $gurus : [], isset($id_guru) ? $id_guru : '', 'id="guru" class="sel form-control select2"') ?>
                                    </td>
                                    <td id="select-mapel" class="d-none ps-2">
                                        <?= form_dropdown('mapel', isset($mapels) ? $mapels : [], isset($id_mapel) ? $id_mapel : '', 'id="mapel" class="sel form-control select2"') ?>
                                    </td>
                                    <td id="select-level" class="d-none ps-2">
                                        <?= form_dropdown('level', isset($levels) ? $levels : [], isset($id_level) ? $id_level : '', 'id="level" class="sel form-control select2"') ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-12 col-md-6">
                            <form id="hapus_semua">
                                <?= csrf_field() ?>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="align-middle text-end">
                                            <button id="submit-hapus-btn" onclick="$('#hapus_semua').submit()"
                                                type="button" class="btn btn-danger shadow-sm" disabled>
                                                <i class="bi bi-trash me-1"></i> Hapus Terpilih
                                            </button>
                                        </td>
                                        <td class="align-middle text-center" style="width: 30px">
                                            <div class="form-check d-flex justify-content-center">
                                                <input style="width: 20px; height: 20px"
                                                    class="form-check-input check-all" id="check-all" type="checkbox">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                        </div>
                    </div>
                    <div class="row" id="konten">
                        <?php if ($mode == '1'): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered dataTable w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle p-0" style="width: 40px">No.</th>
                                            <th class="align-middle">Kode</th>
                                            <th class="align-middle">Mapel</th>
                                            <th class="align-middle">Kelas</th>
                                            <th class="align-middle text-center">Import</th>
                                            <th class="text-center align-middle">Aksi</th>
                                            <th class="text-center align-middle p-0" style="width: 50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $id_tp = $tp_active->id_tp;
                                        $id_smt = $smt_active->id_smt;
                                        if (isset($banks[$id_tp][$id_smt])):
                                            foreach ($banks[$id_tp][$id_smt] as $bank):
                                                $jumlahKelas = is_string($bank->bank_kelas) ? json_decode($bank->bank_kelas) : $bank->bank_kelas;
                                                $kelasbank = '';
                                                foreach ((array) $jumlahKelas as $j) {
                                                    foreach ($kelas as $k) {
                                                        if (isset($j->kelas_id) && $j->kelas_id == $k->id_kelas) {
                                                            $kelasbank .= '<span class="badge bg-primary me-1">' . $k->nama_kelas . '</span> ';
                                                        }
                                                    }
                                                }
                                                $bgRandom = 'text-secondary';
                                                $disable_edit = '';
                                                if ($bank->digunakan > 0) {
                                                    $bgRandom = $bank->digunakan > 1 ? 'text-danger' : 'text-primary';
                                                    $disable_edit = $bank->digunakan > 1 ? 'disabled' : '';
                                                }
                                                ?>
                                                <tr>
                                                    <td class="text-center align-middle"><?= $no++ ?></td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="bi bi-square-fill fs-4 me-2 <?= $bgRandom ?>"></i>
                                                            <span class="fw-bold"><?= $bank->bank_kode ?></span>
                                                        </div>
                                                        <?php
                                                        $target = (int) $bank->tampil_pg + (int) $bank->tampil_kompleks + (int) $bank->tampil_jodohkan + (int) $bank->tampil_isian + (int) $bank->tampil_esai;
                                                        if ($target > 0):
                                                            $actual = (int) $bank->total_pg + (int) $bank->total_kompleks + (int) $bank->total_jodohkan + (int) $bank->total_isian + (int) $bank->total_esai;
                                                            $percent = min(100, round(($actual / $target) * 100));
                                                            $pColor = $percent < 40 ? 'bg-danger' : ($percent < 80 ? 'bg-warning' : 'bg-success');
                                                            ?>
                                                            <div class="progress rounded-pill" style="height: 6px;">
                                                                <div class="progress-bar <?= $pColor ?>"
                                                                    style="width: <?= $percent ?>%"></div>
                                                            </div>
                                                            <small class="text-muted"
                                                                style="font-size: 0.65rem;"><?= $actual ?>/<?= $target ?>
                                                                (<?= $percent ?>%)</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="badge bg-warning"><i class="bi bi-journal-bookmark me-1"></i>
                                                            <?= $bank->nama_mapel ?></span><br>
                                                        <span class="badge bg-info"><i class="bi bi-person me-1"></i>
                                                            <?= $bank->nama_guru ?></span><br>
                                                        <span
                                                            class="badge bg-success"><?= $bank->soal_agama != '-' ? $bank->soal_agama : 'Umum' ?></span>
                                                    </td>
                                                    <td class="align-middle"><?= $kelasbank ?></td>
                                                    <td class="text-center align-middle">
                                                        <i
                                                            class="bi <?= $bank->total_soal == 0 ? 'bi-x-lg text-danger' : 'bi-check-lg text-success' ?>"></i>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <div class="btn-group">
                                                            <a href="<?= site_url('cbtbanksoal/edit/' . $bank->id_bank) ?>"
                                                                class="btn btn-warning btn-sm <?= $disable_edit ?>" title="Edit"><i
                                                                    class="bi bi-pencil"></i></a>
                                                            <button class="btn btn-info btn-sm <?= $disable_edit ?>"
                                                                onclick="importSoal(<?= $bank->id_bank ?>, <?= $bank->total_soal ?>)"
                                                                title="Import"><i class="bi bi-upload"></i></button>
                                                            <a href="<?= site_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                                class="btn btn-success btn-sm" title="Buat Soal"><i
                                                                    class="bi <?= $bank->total_soal == 0 ? 'bi-plus-lg' : 'bi-eye' ?>"></i></a>
                                                            <button class="btn btn-primary btn-sm"
                                                                onclick="getSoal(<?= $bank->id_bank ?>)" title="Download"><i
                                                                    class="bi bi-download"></i></button>
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <input type="checkbox" name="checked[]" value="<?= $bank->id_bank ?>"
                                                            class="check-bank" style="width: 20px; height: 20px">
                                                    </td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <!-- Grid Mode -->
                            <div class="row w-100">
                                <?php
                                $id_tp = $tp_active->id_tp;
                                $id_smt = $smt_active->id_smt;
                                if (isset($banks[$id_tp][$id_smt])):
                                    foreach ($banks[$id_tp][$id_smt] as $bank):
                                        $bgRandom = 'bg-secondary';
                                        $disable_edit = '';
                                        if ($bank->digunakan > 0) {
                                            $bgRandom = $bank->digunakan > 1 ? 'bg-danger' : 'bg-primary';
                                            $disable_edit = $bank->digunakan > 1 ? 'disabled' : '';
                                        }
                                        ?>
                                        <div class="col-md-4">
                                            <div class="card mb-3 border shadow-none">
                                                <div class="card-header <?= $bgRandom ?> text-white">
                                                    <h3 class="card-title"><?= $bank->bank_kode ?></h3>
                                                    <div class="card-tools ms-auto">
                                                        <input type="checkbox" name="checked[]" value="<?= $bank->id_bank ?>"
                                                            class="check-bank" style="width: 18px; height: 18px">
                                                    </div>
                                                </div>
                                                <div class="card-body py-2">
                                                    <ul class="list-group list-group-flush small">
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span>Guru</span><b><?= $bank->nama_guru ?></b>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span>Mapel</span><b><?= $bank->nama_mapel ?></b>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span>Agama</span><b><?= $bank->soal_agama != '-' ? $bank->soal_agama : 'Umum' ?></b>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between px-0">
                                                            <span>Soal</span><b><?= $bank->total_soal ?>/<?= (int) $bank->tampil_pg + (int) $bank->tampil_esai ?></b>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="card-footer bg-light p-2">
                                                    <div class="d-grid gap-1">
                                                        <div class="btn-group w-100">
                                                            <a href="<?= site_url('cbtbanksoal/edit/' . $bank->id_bank) ?>"
                                                                class="btn btn-warning btn-sm <?= $disable_edit ?>"><i
                                                                    class="bi bi-pencil me-1"></i> Edit</a>
                                                            <a href="<?= site_url('cbtbanksoal/detail/' . $bank->id_bank) ?>"
                                                                class="btn btn-success btn-sm"><i
                                                                    class="bi <?= $bank->total_soal == 0 ? 'bi-plus-lg' : 'bi-eye' ?> me-1"></i>
                                                                Detail</a>
                                                        </div>
                                                        <button class="btn btn-primary btn-sm"
                                                            onclick="getSoal(<?= $bank->id_bank ?>)"><i
                                                                class="bi bi-download me-1"></i> Download</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Copy Bank Soal -->
<div class="modal fade" id="openAllBankSoal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Copy Bank Soal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Fitur ini memungkinkan Anda menyalin bank soal dari tahun pelajaran atau
                    semester
                    sebelumnya.</p>
                <?php
                if (isset($banks) && count($banks) > 0):
                    $allBanks = [];
                    if (isset($tp) && is_array($tp)) {
                        foreach ($tp as $thn) {
                            if (isset($smt) && is_array($smt)) {
                                foreach ($smt as $sem) {
                                    if (isset($banks[$thn->id_tp]) && isset($banks[$thn->id_tp][$sem->id_smt])) {
                                        $allBanks[] = [
                                            'tahun' => $thn->tahun . ' SMT ' . $sem->smt,
                                            'banks' => $banks[$thn->id_tp][$sem->id_smt]
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    usort($allBanks, function ($a, $b) {
                        return $b['tahun'] <=> $a['tahun'];
                    });

                    if (count($allBanks) > 0):
                        foreach ($allBanks as $allBank): ?>
                            <div class="card mb-3 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 fw-bold"><?= esc($allBank['tahun']) ?></h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="width: 50px;">No.</th>
                                                    <th>Kode</th>
                                                    <th>Mapel</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                foreach ($allBank['banks'] as $ab): ?>
                                                    <tr>
                                                        <td class="text-center"><?= $no++ ?></td>
                                                        <td><?= esc($ab->bank_kode) ?></td>
                                                        <td><?= esc($ab->nama_mapel) ?></td>
                                                        <td class="text-center">
                                                            <button onclick="copy(<?= $ab->id_bank ?>)" type="button"
                                                                class="btn btn-sm btn-outline-success">
                                                                <i class="bi bi-copy me-1"></i> Copy
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <div class="alert alert-info">Belum ada bank soal yang tersedia untuk disalin.</div>
                    <?php endif;
                else: ?>
                    <div class="alert alert-warning">Pilih filter atau 'Semua' untuk menampilkan bank soal yang bisa
                        disalin.</div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        $('#filter').on('change', function () {
            const type = $(this).val();
            $('#select-guru, #select-mapel, #select-level').addClass('d-none');
            if (type == '1') $('#select-guru').removeClass('d-none');
            else if (type == '2') $('#select-mapel').removeClass('d-none');
            else if (type == '3') $('#select-level').removeClass('d-none');

            if (type == '0') {
                location.href = '<?= site_url('cbtbanksoal?type=0') ?>';
            }
        });

        $('.sel').on('change', function () {
            const id = $(this).val();
            const type = $('#filter').val();
            location.href = '<?= site_url('cbtbanksoal') ?>?type=' + type + '&id=' + id;
        });

        // Trigger change to show correct select if id_filter exists
        $('#filter').trigger('change');

        $('#check-all').on('change', function () {
            $('.check-bank').prop('checked', $(this).is(':checked'));
            updateHapusButton();
        });

        $(document).on('change', '.check-bank', function () {
            updateHapusButton();
        });

        function updateHapusButton() {
            const count = $('.check-bank:checked').length;
            $('#submit-hapus').prop('disabled', count === 0);
            $('#submit-hapus-btn').prop('disabled', count === 0);
        }
    });

    function importSoal(id, total) {
        if (total > 0) {
            Swal.fire({
                title: 'Import Soal?',
                text: 'Bank soal sudah berisi soal. Mengimport akan menghapus soal yang ada. Lanjutkan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Lanjutkan'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href = '<?= site_url('cbtbanksoal/import/') ?>' + id;
                }
            });
        } else {
            location.href = '<?= site_url('cbtbanksoal/import/') ?>' + id;
        }
    }

    function copy(id) {
        Swal.fire({
            title: 'Copy Bank Soal?',
            text: 'Bank soal ini akan dicopy ke tahun pelajaran dan semester aktif.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Copy'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Mohon Tunggu',
                    text: 'Sedang menyalin bank soal...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: '<?= site_url('cbtbanksoal/copybanksoal/') ?>' + id,
                    type: 'GET',
                    success: function (data) {
                        if (data) {
                            Swal.fire('Sukses', 'Bank soal berhasil dicopy', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', 'Gagal mengcopy bank soal', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    function hapus(id) {
        Swal.fire({
            title: 'Anda yakin?',
            text: 'Bank Soal dan soal-soal di dalamnya akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= site_url('cbtbanksoal/deleteBank') ?>',
                    type: 'GET',
                    data: { id_bank: id },
                    success: function (respon) {
                        if (respon.status) {
                            Swal.fire('Berhasil', respon.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', respon.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    function getSoal(id) {
        // Placeholder for download logic
        alert('Fitur download sedang dalam tahap pengembangan.');
    }
</script>