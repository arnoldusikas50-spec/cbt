<main class="app-main bg-white pt-4">


    <section class="content">
        <div class="container-fluid">
            <div class="card my-shadow">
                <div class="card-header">
                    <div class="card-title">
                        <h6><?= $subjudul . ' ' . $kelas->nama_kelas ?></h6>
                    </div>
                    <a href="<?= base_url('datakelas') ?>" type="button" class="btn btn-sm btn-danger float-end me-2">
                        <i class="bi bi-arrow-left-square"></i><span
                            class="d-none d-sm-inline-block ms-1">Kembali</span>
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <td style="width: 160px" class="alert-default-primary">
                                Nama Kelas
                            </td>
                            <td>
                                <?= $kelas->nama_kelas ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="alert-default-danger">
                                Kode Kelas
                            </td>
                            <td>
                                <?= $kelas->kode_kelas ?>
                            </td>
                        </tr>
                        <?php if ($setting->jenjang == '3'): ?>
                            <tr>
                                <td class="alert-default-warning">
                                    Jurusan
                                </td>
                                <td>
                                    <?= $kelas->nama_jurusan ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="alert-default-info">
                                Level Kelas
                            </td>
                            <td>
                                <?= $kelas->level ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="alert-default-danger">
                                Wali Kelas
                            </td>
                            <td>
                                <?= $kelas->nama_guru ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="alert-default-success">
                                Jumlah Siswa
                            </td>
                            <td>
                                <?= count($siswas) ?>
                            </td>
                        </tr>
                    </table>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Siswa Kelas <?= $kelas->nama_kelas ?></h6>
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th class="text-center align-middle alert-default-primary">No</th>
                                    <th class="text-center align-middle alert-default-primary">N I S</th>
                                    <th class="text-center align-middle alert-default-primary">Nama</th>
                                </tr>
                                <?php
                                $no = 1;
                                foreach ($siswas as $siswa): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?= $no ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $siswa->nis ?>
                                        </td>
                                        <td>
                                            <?= $siswa->nama ?>
                                        </td>
                                    </tr>
                                    <?php $no++; endforeach; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Struktur Organisasi Kelas <?= $kelas->nama_kelas ?></h6><a
                                class="alert-default-warning">(Diisi oleh wali kelas)</a>
                            <?php
                            //echo '<pre>';
                            //var_dump(array_search('119', array_column($siswas, 'id_siswa')));
                            //var_dump($struktur);
                            //echo '</pre>';
                            $arrJabatan = [
                                'Wali Kelas',
                                'Ketua Kelas',
                                'Wakil Ketua',
                                'Sekretaris I',
                                'Sekretaris II',
                                'Bendahara I',
                                'Bendahara II',
                                'Sie Upacara',
                                'Sie Ekstrakurikuler',
                                'Sie Olahraga',
                                'Sie Keagamaan',
                                'Sie Keamanan',
                                'Sie Ketertiban',
                                'Sie Kebersihan',
                                'Sie Keindahan',
                                'Sie Kesehatan',
                                'Sie Kekeluargaan',
                                'Sie Humas'
                            ];
                            $arrSiswa = [
                                '',
                                $struktur->ketua,
                                $struktur->wakil_ketua,
                                $struktur->sekretaris_1,
                                $struktur->sekretaris_2,
                                $struktur->bendahara_1,
                                $struktur->bendahara_2,
                                $struktur->sie_upacara,
                                $struktur->sie_ekstrakurikuler,
                                $struktur->sie_olahraga,
                                $struktur->sie_keagamaan,
                                $struktur->sie_keamanan,
                                $struktur->sie_ketertiban,
                                $struktur->sie_kebersihan,
                                $struktur->sie_keindahan,
                                $struktur->sie_kesehatan,
                                $struktur->sie_kekeluargaan,
                                $struktur->sie_humas
                            ];
                            ?>
                            <table class="table table-bordered">
                                <tr class="alert-default-primary">
                                    <th class="text-center align-middle">No</th>
                                    <th class="text-center align-middle">Jabatan</th>
                                    <th class="text-center align-middle">Nama Siswa</th>
                                </tr>
                                <?php
                                $no = 0;
                                foreach ($arrJabatan as $j):
                                    $key = array_search($arrSiswa[$no], array_column($siswas, 'id_siswa'));
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no + 1 ?></td>
                                        <td>
                                            <?= $j ?>
                                        </td>
                                        <td>
                                            <?= $key !== false ? $siswas[$key]->nama : ($no === 0 ? $kelas->nama_guru : '') ?>
                                        </td>
                                    </tr>
                                    <?php $no++; endforeach; ?>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>