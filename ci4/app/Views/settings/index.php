<main class="app-main bg-white pt-4">
    <section class="content">
        <div class="container-fluid">

            <div class="row g-4">
                <!-- Pengaturan Sekolah -->
                <div class="col-lg-6">
                    <div class="card card-default my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-building text-primary me-2"></i>Pengaturan Sekolah
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="formSekolah">
                                <input type="hidden" name="type" value="sekolah">
                                <div class="mb-3">
                                    <label class="form-label">Nama Sekolah</label>
                                    <input type="text" name="nama_sekolah" class="form-control"
                                        value="<?= esc($setting->nama_sekolah ?? '') ?>">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">NPSN</label>
                                        <input type="text" name="npsn" class="form-control"
                                            value="<?= esc($setting->npsn ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Telepon</label>
                                        <input type="text" name="telepon" class="form-control"
                                            value="<?= esc($setting->telepon ?? '') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?= esc($setting->email ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control"
                                        value="<?= esc($setting->website ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control"
                                        rows="2"><?= esc($setting->alamat ?? '') ?></textarea>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kota</label>
                                        <input type="text" name="kota" class="form-control"
                                            value="<?= esc($setting->kota ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Provinsi</label>
                                        <input type="text" name="provinsi" class="form-control"
                                            value="<?= esc($setting->provinsi ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kepala Sekolah</label>
                                        <input type="text" name="kepala_sekolah" class="form-control"
                                            value="<?= esc($setting->kepala_sekolah ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">NIP Kepsek</label>
                                        <input type="text" name="nip_kepsek" class="form-control"
                                            value="<?= esc($setting->nip_kepsek ?? '') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Logo Sekolah</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <?php if (!empty($setting->logo)): ?>
                                        <small class="text-muted d-block mt-2">Logo saat ini:
                                            <?= esc($setting->logo) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i>Simpan Pengaturan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan TP & Semester & CBT -->
                <div class="col-lg-6">
                    <div class="card card-default my-shadow mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-calendar-check text-success me-2"></i>Tahun Pelajaran Aktif
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="formTp">
                                <input type="hidden" name="type" value="tp">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Tahun Pelajaran</label>
                                    <select name="tp_aktif" class="form-select">
                                        <?php foreach ($tp_list ?? [] as $tp): ?>
                                            <option value="<?= is_object($tp) ? $tp->id_tp : '' ?>" <?= ($tp_active && is_object($tp) && $tp->id_tp == $tp_active->id_tp) ? 'selected' : '' ?>>
                                                <?= is_object($tp) ? esc($tp->tahun) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i>Aktifkan
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card card-default my-shadow mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-clock-history text-warning me-2"></i>Semester Aktif
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="formSmt">
                                <input type="hidden" name="type" value="smt">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Semester</label>
                                    <select name="smt_aktif" class="form-select">
                                        <?php foreach ($smt_list ?? [] as $smt): ?>
                                            <option value="<?= is_object($smt) ? $smt->id_smt : '' ?>" <?= ($smt_active && is_object($smt) && $smt->id_smt == $smt_active->id_smt) ? 'selected' : '' ?>>
                                                Semester
                                                <?= is_object($smt) ? esc($smt->smt) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-warning shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i>Aktifkan
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card card-default my-shadow">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="bi bi-pc-display text-info me-2"></i>Pengaturan CBT
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="formCbt">
                                <input type="hidden" name="type" value="cbt">
                                <div class="mb-3">
                                    <label class="form-label">Durasi Default (menit)</label>
                                    <input type="number" name="durasi_default" class="form-control"
                                        value="<?= esc($setting->durasi_default ?? 60) ?>">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="acak_soal" id="acakSoal"
                                            <?= ($setting->acak_soal ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="acakSoal">Acak Urutan Soal</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="acak_jawaban"
                                            id="acakJawaban" <?= ($setting->acak_jawaban ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="acakJawaban">Acak Urutan Jawaban</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="tampil_nilai"
                                            id="tampilNilai" <?= ($setting->tampil_nilai ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="tampilNilai">Tampilkan Nilai Setelah
                                            Ujian</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-info shadow-sm text-white">
                                    <i class="bi bi-check-circle me-1"></i>Simpan Pengaturan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<script>
    const csrfToken = '<?= csrf_hash() ?>';

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('<?= csrf_token() ?>', csrfToken);

            try {
                const res = await fetch('<?= site_url('settings/update') ?>', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => {
                        if (this.id === 'formTp' || this.id === 'formSmt') {
                            setTimeout(() => location.reload(), 1000);
                        }
                    });
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            }
        });
    });
</script>