<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <h1><?= $judul ?></h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('cbtberitaacara/pakta_create') ?>" method="GET">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Pilih Jadwal Ujian</label>
                                <select name="jadwal" class="form-control" required>
                                    <option value="">Pilih Jadwal</option>
                                    <?php foreach ($jadwal as $j): ?>
                                        <option value="<?= $j->id_jadwal ?>"><?= $j->bank_kode ?> - <?= $j->tgl_mulai ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Isi Pakta</button>
                            </div>
                        </div>
                    </form>

                    <hr>
                    <h5>Riwayat Pakta Integritas Anda</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-pakta">
                            <thead>
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th>Jadwal Ujian</th>
                                    <th>Tanggal Ujian</th>
                                    <th>Sebagai</th>
                                    <th>Tanggal Tanda Tangan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($paktas as $p): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= $p->bank_kode ?></td>
                                        <td><?= $p->tgl_mulai ? tgl_indo($p->tgl_mulai) : '-' ?></td>
                                        <td><?= $p->role ?></td>
                                        <td><?= $p->created_at ? tgl_indo($p->created_at) : '-' ?></td>
                                        <td class="text-center">
                                            <button
                                                onclick="window.open('<?= base_url('cbtberitaacara/pakta_cetak/' . $p->id_pakta_integritas) ?>', '_blank')"
                                                class="btn btn-sm btn-primary">
                                                <i class="bi bi-print"></i> Cetak
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </div>