<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?= $judul ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default my-shadow">
                <div class="card-header with-border">
                    <h3 class="card-title"><?= $subjudul ?></h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-siswa" class="w-100 table table-striped table-bordered table-hover">
                            <thead>
                                <tr class="alert alert-default-primary">
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
                                if (count($siswas) > 0):
                                    foreach ($siswas as $idx => $siswa):
                                        $kls = $siswa->nama_kelas != null ? '<span class="badge rounded-pill bg-info">' . $siswa->nama_kelas . '</span>' : '';
                                        $agama = $siswa->agama ? '<span class="badge rounded-pill bg-info">' . $siswa->agama . '</span>' : '';

                                        $status = $siswa->active == "0" ? '<div class="badge rounded-pill bg-danger">Nonaktif</div>' : '<div class="badge rounded-pill bg-success">Aktif</div>';
                                        $foto = file_exists($siswa->foto) ? base_url($siswa->foto) : base_url('assets/img/siswa.png');
                                        ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= $idx + 1 ?></td>
                                            <td>
                                                <div class="media d-flex h-100">
                                                    <img class="avatar img-circle justify-content-center align-self-center"
                                                        src="<?= $foto ?>" width="50" height="50" alt="User Image">
                                                    <div class="media-body ms-2 justify-content-center align-self-center">
                                                        <?= $siswa->nama ?><br>
                                                        <?= $kls ?>
                                                        <?= $agama ?>
                                                        <span class="badge bg-info"><?= $siswa->jenis_kelamin ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class=" align-middle">
                                                <span class="badge bg-light"><?= $siswa->nis ?></span><br>
                                                <span class="badge bg-light"><?= $siswa->nisn ?></span>
                                            </td>
                                            <td>U: <?= $siswa->username ?><br>P: <?= $siswa->password ?></td>
                                            <td class="text-center align-middle"><?= $kls ?><br><?= $status ?></td>
                                            <td class=" align-middle">
                                                <div class="text-center">
                                                    <a class="btn btn-xs btn-warning"
                                                        href="<?= base_url('datasiswa/edit/' . $siswa->id_siswa) ?>">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center align-middle">Tidak ada data siswa</td>
                                    <tr>
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