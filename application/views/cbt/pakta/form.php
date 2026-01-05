<main class="app-main bg-white pt-4">
    <section class="content-header">
        <div class="container-fluid">
            <h1><?= $judul ?></h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?= form_open('cbtberitaacara/pakta_save', array('id' => 'formPakta')) ?>
            <input type="hidden" name="id_jadwal" value="<?= $jadwal->id_jadwal ?>">

            <div class="card card-default shadow mb-4">
                <div class="card-header">
                    <h3 class="card-title">PAKTA INTEGRITAS</h3>
                </div>
                <div class="card-body">
                    <p>Dalam rangka pelaksanaan Ujian <?= $jadwal->bank_kode ?>, saya yang bertanda tangan di bawah ini:
                    </p>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">Nama</label>
                        <div class="col-sm-10">
                            <input type="text" name="nama" class="form-control"
                                value="<?= $pakta->nama ?? $user->first_name . ' ' . $user->last_name ?>">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-2 col-form-label">Jabatan (Role)</label>
                        <div class="col-sm-10">
                            <select name="role" class="form-control">
                                <option value="Pengawas" <?= ($pakta->role ?? '') == 'Pengawas' ? 'selected' : '' ?>>
                                    Pengawas</option>
                                <option value="Proktor" <?= ($pakta->role ?? '') == 'Proktor' ? 'selected' : '' ?>>Proktor
                                </option>
                                <option value="Teknisi" <?= ($pakta->role ?? '') == 'Teknisi' ? 'selected' : '' ?>>Teknisi
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Menyatakan bahwa saya:</label>
                        <textarea name="isi_pakta" class="form-control"
                            rows="10"><?= $pakta->isi_pakta ?? "1. Sanggup meningkatkan kualitas, kredibilitas, dan akuntabilitas pelaksanaan ujian untuk meningkatkan mutu pendidikan.\n2. Sanggup melaksanakan tugas sesuai dengan Prosedur Operasional Standar (POS) Penyelenggaraan Ujian dan mensukseskan pelaksanaannya.\n3. Sanggup menjaga keamanan dan kerahasiaan bahan ujian.\n4. Sanggup melaksanakan kewajiban dan tanggung jawab yang diberikan dengan penuh dedikasi dan tanggung jawab." ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Tanda Tangan</label>
                        <div class="border rounded p-2 text-center" style="background: #f0f0f0; width: 320px;">
                            <canvas id="pad" width="300" height="150" class="bg-white border"></canvas>
                            <br>
                            <button type="button" class="btn btn-sm btn-danger mt-1" onclick="clearPad()">Hapus</button>
                        </div>
                        <input type="hidden" name="ttd" id="ttd" value="<?= $pakta->tanda_tangan ?? '' ?>">
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan & Cetak</button>
                    <a href="<?= base_url('cbtberitaacara/pakta') ?>" class="btn btn-default">Kembali</a>
                </div>
            </div>
            <?= form_close() ?>
        </div>
    </section>
    </div>

    <script src="<?= base_url('assets/plugins/signature/signature_pad.min.js') ?>"></script>
    <script>
        var pad = new SignaturePad(document.getElementById('pad'));

        var existing = document.getElementById('ttd').value;
        if (existing) {
            pad.fromDataURL(existing);
        }

        function clearPad() {
            pad.clear();
            document.getElementById('ttd').value = '';
        }

        $('#formPakta').on('submit', function () {
            if (!pad.isEmpty()) {
                document.getElementById('ttd').value = pad.toDataURL();
            }
        });
    </script>