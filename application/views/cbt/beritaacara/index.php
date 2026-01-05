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
            <div class="card card-default my-shadow mb-4">
                <div class="card-header">
                    <h6 class="card-title"><?= $subjudul ?></h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('cbtberitaacara/create') ?>" method="GET">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Jadwal Ujian</label>
                                <select name="jadwal" class="form-control" required>
                                    <option value="">Pilih Jadwal</option>
                                    <?php foreach ($jadwal as $j): ?>
                                        <option value="<?= $j->id_jadwal ?>"><?= $j->bank_kode ?> - <?= $j->tgl_mulai ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Ruang</label>
                                <select name="ruang" class="form-control" required>
                                    <option value="">Pilih Ruang</option>
                                    <?php foreach ($ruang as $r): ?>
                                        <option value="<?= $r->id_ruang ?>"><?= $r->nama_ruang ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Sesi</label>
                                <select name="sesi" class="form-control" required>
                                    <option value="">Pilih Sesi</option>
                                    <?php foreach ($sesi as $s): ?>
                                        <option value="<?= $s->id_sesi ?>"><?= $s->nama_sesi ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Buat / Edit</button>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <h5>Riwayat Berita Acara</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-berita">
                            <thead>
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Ruang</th>
                                    <th>Sesi</th>
                                    <th>Tanggal Pelaksanaan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($beritas as $b): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= $b->bank_kode ?></td>
                                        <td><?= $b->nama_ruang ?></td>
                                        <td><?= $b->nama_sesi ?></td>
                                        <td><?= tgl_indo($b->tgl_mulai) ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('cbtberitaacara/cetak/' . $b->id_berita_acara) ?>"
                                                class="btn btn-sm btn-primary" target="_blank">
                                                <i class="bi bi-print"></i> Cetak
                                            </a>
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