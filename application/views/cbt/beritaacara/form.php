<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <h1><?= $judul ?></h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?= form_open('cbtberitaacara/save', array('id' => 'formBerita')) ?>
            <input type="hidden" name="id_jadwal" value="<?= $jadwal->id_jadwal ?>">
            <input type="hidden" name="id_ruang" value="<?= $ruang->id_ruang ?>">
            <input type="hidden" name="id_sesi" value="<?= $sesi->id_sesi ?>">

            <div class="card card-default shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title">Detail Berita Acara</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Mata Pelajaran</dt>
                        <dd class="col-sm-9"><?= $jadwal->bank_kode ?></dd>
                        <dt class="col-sm-3">Waktu Pelaksanaan</dt>
                        <dd class="col-sm-9"><?= $jadwal->tgl_mulai ?></dd>
                        <dt class="col-sm-3">Ruang / Sesi</dt>
                        <dd class="col-sm-9"><?= $ruang->nama_ruang ?> / <?= $sesi->nama_sesi ?></dd>
                    </dl>

                    <div class="mb-3">
                        <label>Isi Berita Acara</label>
                        <textarea name="isi_berita" class="form-control"
                            rows="4"><?= $berita->isi_berita ?? 'Telah dilaksanakan ujian ' . $jadwal->bank_kode . ' pada tanggal ' . $jadwal->tgl_mulai . ' di Ruang ' . $ruang->nama_ruang . ' dengan tertib.' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Jumlah Hadir</label>
                                <input type="number" name="jml_hadir" class="form-control"
                                    value="<?= $berita->jumlah_peserta_hadir ?? 0 ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Jumlah Tidak Hadir</label>
                                <input type="number" name="jml_tidak_hadir" class="form-control"
                                    value="<?= $berita->jumlah_peserta_tidak_hadir ?? 0 ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Peserta Tidak Hadir (Sebutkan Nama/No Peserta)</label>
                        <textarea name="peserta_tidak_hadir" class="form-control"
                            rows="2"><?= $berita->peserta_tidak_hadir ?? '-' ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"><?= $berita->catatan ?? '-' ?></textarea>
                    </div>

                    <hr>
                    <div class="row">
                        <!-- Proktor -->
                        <div class="col-md-6">
                            <h5>Proktor</h5>
                            <div class="mb-3">
                                <label>Nama Proktor</label>
                                <input type="text" name="nama_proktor" class="form-control"
                                    value="<?= $berita->nama_proktor ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>NIP Proktor</label>
                                <input type="text" name="nip_proktor" class="form-control"
                                    value="<?= $berita->nip_proktor ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Tanda Tangan Proktor</label>
                                <div class="border rounded p-2 text-center" style="background: #f0f0f0;">
                                    <canvas id="padProktor" width="300" height="150" class="bg-white border"></canvas>
                                    <br>
                                    <button type="button" class="btn btn-sm btn-danger mt-1"
                                        onclick="clearProktor()">Hapus</button>
                                </div>
                                <textarea name="ttd_proktor" id="ttd_proktor"
                                    style="display:none;"><?= $berita->tanda_tangan_proktor ?? '' ?></textarea>
                            </div>
                        </div>

                        <!-- Pengawas -->
                        <div class="col-md-6">
                            <h5>Pengawas</h5>
                            <div class="mb-3">
                                <label>Nama Pengawas</label>
                                <input type="text" name="nama_pengawas" class="form-control"
                                    value="<?= $berita->nama_pengawas ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>NIP Pengawas</label>
                                <input type="text" name="nip_pengawas" class="form-control"
                                    value="<?= $berita->nip_pengawas ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label>Tanda Tangan Pengawas</label>
                                <div class="border rounded p-2 text-center" style="background: #f0f0f0;">
                                    <canvas id="padPengawas" width="300" height="150" class="bg-white border"></canvas>
                                    <br>
                                    <button type="button" class="btn btn-sm btn-danger mt-1"
                                        onclick="clearPengawas()">Hapus</button>
                                </div>
                                <textarea name="ttd_pengawas" id="ttd_pengawas"
                                    style="display:none;"><?= $berita->tanda_tangan_pengawas ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary float-end mt-3"><i class="bi bi-save"></i>
                        Simpan</button>
                    <a href="<?= base_url('cbtberitaacara') ?>" class="btn btn-default mt-3 me-2">Kembali</a>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
    </div>

    <script src="<?= base_url('assets/plugins/signature/signature_pad.min.js') ?>"></script>
    <script>
        var padProktor = new SignaturePad(document.getElementById('padProktor'));
        var padPengawas = new SignaturePad(document.getElementById('padPengawas'));

        // Load existing signature if any
        var existingProktor = document.getElementById('ttd_proktor').value;
        if (existingProktor) {
            padProktor.fromDataURL(existingProktor);
        }
        var existingPengawas = document.getElementById('ttd_pengawas').value;
        if (existingPengawas) {
            padPengawas.fromDataURL(existingPengawas);
        }

        function clearProktor() {
            padProktor.clear();
            document.getElementById('ttd_proktor').value = '';
        }

        function clearPengawas() {
            padPengawas.clear();
            document.getElementById('ttd_pengawas').value = '';
        }

        $('#formBerita').on('submit', function () {
            if (!padProktor.isEmpty()) {
                document.getElementById('ttd_proktor').value = padProktor.toDataURL();
            }
            if (!padPengawas.isEmpty()) {
                document.getElementById('ttd_pengawas').value = padPengawas.toDataURL();
            }
        });
    </script>