<div class="content-wrapper bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-header with-border">
                    <h3 class="card-title"><?= $subjudul ?></h3>
                </div>
                <div class="card-body">
                    <?php
                    $totalSiswa = is_countable($siswas ?? []) ? count($siswas ?? []) : 0;
                    $totalAktif = 0;
                    $totalNonaktif = 0;
                    $totalLaki = 0;
                    $totalPerempuan = 0;

                    if (!empty($siswas) && is_iterable($siswas)) {
                        foreach ($siswas as $siswa) {
                            if (($siswa->active ?? '0') == '1') {
                                $totalAktif++;
                            } else {
                                $totalNonaktif++;
                            }
                            $jk = strtoupper(trim($siswa->jenis_kelamin ?? ''));
                            if ($jk === 'L') {
                                $totalLaki++;
                            } elseif ($jk === 'P') {
                                $totalPerempuan++;
                            }
                        }
                    }
                    ?>

                    <div class="mb-4">
                        <div class="bg-light border rounded p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h5 class="mb-1 font-weight-bold">Ringkasan Siswa Wali Kelas</h5>
                                <div class="text-muted">Pantau status akun siswa dan info login secara cepat</div>
                            </div>
                            <div class="mt-3 mt-md-0 d-flex flex-column flex-sm-row text-sm">
                                <span class="badge badge-primary px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Total Siswa: <?= $totalSiswa ?></span>
                                <span class="badge badge-success px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Aktif: <?= $totalAktif ?></span>
                                <span class="badge badge-warning px-3 py-2 text-sm mr-sm-2 mb-2 mb-sm-0">Nonaktif: <?= $totalNonaktif ?></span>
                                <span class="badge badge-info px-3 py-2 text-sm">L <?= $totalLaki ?> · P <?= $totalPerempuan ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info shadow-sm border border-info mb-4">
                        <h5 class="mb-2 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Panduan Pengelolaan Siswa</h5>
                        <ul class="pl-3 mb-0">
                            <li>Gunakan menu <strong>Edit</strong> untuk memperbarui data siswa atau mereset kredensial login.</li>
                            <li>Pastikan status siswa <span class="badge badge-success">Aktif</span> sebelum penilaian daring dimulai.</li>
                            <li>Manfaatkan filter DataTable (pencarian/urutkan) untuk menemukan siswa secara cepat.</li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table id="table-siswa" class="w-100 table table-striped table-bordered table-hover">
                            <thead>
                            <tr class = "alert alert-default-primary">
                                <th class="text-center align-middle" width="40" height="40">No.</th>
                                <th class="align-middle">Siswa</th>
                                <th class="align-middle">NIS & NISN</th>
                                <th class="align-middle">Username<br>Password</th>
                                <th class="text-center align-middle">Status</th>
                                <th class="text-center align-middle">Aksi</th>
                            </tr>
                            </thead>
                            <tbody id="table-body">
                            <?php
                            if (count($siswas) > 0) :
                                foreach ($siswas as $idx=>$siswa) :
                                    $kls = $siswa->nama_kelas != null ? '<span class="badge badge-pill badge-info">'.$siswa->nama_kelas.'</span>' : '';
                                    $agama = $siswa->agama ? '<span class="badge badge-pill badge-info">'.$siswa->agama.'</span>' : '';

                                    $status = $siswa->active == "0"  ? '<div class="badge badge-pill badge-danger">Nonaktif</div>' : '<div class="badge badge-pill badge-success">Aktif</div>';
                                    $foto = file_exists($siswa->foto) ? base_url($siswa->foto) : base_url('assets/img/siswa.png');
                                    ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= $idx +1 ?></td>
                                        <td>
                                            <div class="media d-flex h-100">
                                                <img class="avatar img-circle justify-content-center align-self-center"
                                                     src="<?=$foto?>" width="50" height="50" alt="User Image">
                                                <div class="media-body ml-2 justify-content-center align-self-center">
                                                    <?=$siswa->nama?><br>
                                                    <?=$kls?>
                                                    <?=$agama?>
                                                    <span class="badge badge-info"><?=$siswa->jenis_kelamin?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class=" align-middle">
                                            <span class="badge badge-light"><?=$siswa->nis?></span><br>
                                            <span class="badge badge-light"><?=$siswa->nisn?></span>
                                        </td>
                                        <td>U: <?=$siswa->username?><br>P: <?=$siswa->password?></td>
                                        <td class="text-center align-middle"><?=$kls?><br><?=$status?></td>
                                        <td class=" align-middle">
                                            <div class="text-center">
                                                <a class="btn btn-xs btn-warning" href="<?=base_url('datasiswa/edit/'.$siswa->id_siswa)?>">
                                                    <i class="fa fa-pencil-alt"></i> Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center align-middle">Tidak ada data siswa</td><tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
    </section>
</div>

<script>
    $(document).ready(function () {
        /*
        $(`.avatar`).each(function () {
            $(this).on("error", function () {
                var src = $(this).attr('src').replace('profiles', 'foto_siswa');
                $(this).attr("src", src);
                $(this).on("error", function () {
                    $(this).attr("src", base_url + 'assets/img/siswa.png');
                });
            });
        });
         */
        $('#table-siswa').dataTable()
    })
</script>