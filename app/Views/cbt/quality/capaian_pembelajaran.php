<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><?= esc($judul) ?></h3>
                <small class="text-muted"><?= esc($subjudul) ?></small>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('cbt-quality') ?>">Kualitas</a></li>
                    <li class="breadcrumb-item active">Master CP</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content fade-in-up">
  <div class="container-fluid">

    <?php if (!($tabel_ada ?? true)): ?>
    <div class="alert alert-warning d-flex gap-3 align-items-start mb-4">
      <i class="bi bi-exclamation-triangle-fill fs-4 mt-1 flex-shrink-0"></i>
      <div>
        <strong>Tabel Capaian Pembelajaran belum tersedia.</strong><br>
        Jalankan migrasi database terlebih dahulu agar fitur ini aktif:
        <code class="ms-1">php spark migrate</code>
        <br>
        <small class="text-muted">Atau hubungi administrator untuk menjalankan migrasi via panel.</small>
      </div>
    </div>
    <?php endif; ?>

    <!-- Statistik ringkas -->
    <div class="row g-3 mb-4">
      <?php
      $faseLabel = ['D' => 'D (SMP)', 'E' => 'E (SMA X)', 'F' => 'F (SMA XI-XII)'];
      $faseCounts = [];
      foreach ($stats as $s) { $faseCounts[$s->fase] = (int)$s->jumlah; }
      ?>
      <?php foreach ($faseLabel as $f => $fl): ?>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <div class="display-6 fw-bold text-primary"><?= $faseCounts[$f] ?? 0 ?></div>
            <div class="text-muted small">Fase <?= esc($fl) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center">
          <div class="card-body py-3">
            <div class="display-6 fw-bold text-secondary"><?= array_sum(array_column($stats, 'jumlah')) ?></div>
            <div class="text-muted small">Total CP</div>
            <button class="btn btn-sm btn-outline-primary mt-2" id="btn-tambah-cp">
              <i class="bi bi-plus-lg me-1"></i>Tambah
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter & Tabel -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0 d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="bi bi-book-half me-2 text-primary"></i>Daftar Capaian Pembelajaran</h6>
        <div class="d-flex gap-2 flex-wrap">
          <!-- Filter form -->
          <form method="GET" class="d-flex gap-2 flex-wrap align-items-center" id="filter-form">
            <input type="text" name="q" value="<?= esc($q) ?>" placeholder="Cari kode/mapel/elemen…"
                   class="form-control form-control-sm" style="width:200px">
            <select name="fase" class="form-select form-select-sm" style="width:140px">
              <option value="">Semua Fase</option>
              <?php foreach ($fase_options as $fk => $fl): ?>
              <option value="<?= esc($fk) ?>" <?= $fase === $fk ? 'selected' : '' ?>><?= esc($fl) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <a href="<?= base_url('cbt-quality/capaian-pembelajaran') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
          </form>
          <!-- Seed button (admin only) -->
          <button class="btn btn-sm btn-outline-warning" id="btn-seed" title="Isi data sampel CP (hanya jika tabel kosong)">
            <i class="bi bi-database-add me-1"></i>Seed Sampel
          </button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:120px">Kode CP</th>
                <th>Mata Pelajaran</th>
                <th class="text-center" style="width:60px">Fase</th>
                <th>Elemen</th>
                <th class="d-none d-md-table-cell">Deskripsi</th>
                <th class="text-center" style="width:80px">Aktif</th>
                <th class="text-center" style="width:90px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($cp_list)): ?>
              <tr><td colspan="7" class="text-center py-4 text-muted">
                Belum ada data CP. Klik <strong>Tambah</strong> atau <strong>Seed Sampel</strong>.
              </td></tr>
              <?php else: ?>
              <?php foreach ($cp_list as $cp): ?>
              <tr>
                <td><code class="text-primary"><?= esc($cp->kode_cp) ?></code></td>
                <td><?= esc($cp->mata_pelajaran) ?></td>
                <td class="text-center"><span class="badge bg-primary"><?= esc($cp->fase) ?></span></td>
                <td><?= esc($cp->elemen) ?></td>
                <td class="d-none d-md-table-cell text-muted small" style="max-width:300px;white-space:normal">
                  <?= esc(mb_substr($cp->deskripsi_cp ?? '', 0, 120)) ?>…
                </td>
                <td class="text-center">
                  <?php if ($cp->is_active): ?>
                    <span class="badge bg-success">Aktif</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Nonaktif</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-primary btn-edit-cp"
                    data-id="<?= $cp->id_cp ?>"
                    data-kode="<?= esc($cp->kode_cp) ?>"
                    data-mapel="<?= esc($cp->mata_pelajaran) ?>"
                    data-fase="<?= esc($cp->fase) ?>"
                    data-elemen="<?= esc($cp->elemen) ?>"
                    data-deskripsi="<?= esc($cp->deskripsi_cp ?? '') ?>"
                    data-jenjang="<?= (int)$cp->jenjang ?>"
                    data-kelompok="<?= esc($cp->kelompok_mapel ?? '') ?>"
                    data-aktif="<?= (int)$cp->is_active ?>"
                    title="Edit"><i class="bi bi-pencil-fill"></i></button>
                  <button class="btn btn-sm btn-outline-danger btn-delete-cp"
                    data-id="<?= $cp->id_cp ?>" data-kode="<?= esc($cp->kode_cp) ?>"
                    title="Hapus"><i class="bi bi-trash3"></i></button>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php if ($pager): ?>
      <div class="card-footer bg-transparent border-0">
        <?= $pager->links('default', 'default_full') ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Modal Tambah/Edit CP -->
<div class="modal fade" id="modalCp" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-book-half me-2 text-primary"></i><span id="modal-cp-title">Tambah Capaian Pembelajaran</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="form-cp">
        <div class="modal-body">
          <input type="hidden" id="cp_id" name="id_cp" value="0">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Kode CP <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cp_kode" name="kode_cp"
                     placeholder="Contoh: MAT.D.1" maxlength="30">
              <div class="form-text">Unik per mata pelajaran & fase.</div>
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cp_mapel" name="mata_pelajaran"
                     placeholder="Contoh: Matematika" maxlength="100">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Fase <span class="text-danger">*</span></label>
              <select class="form-select" id="cp_fase" name="fase">
                <option value="A">A (SD 1-2)</option>
                <option value="B">B (SD 3-4)</option>
                <option value="C">C (SD 5-6)</option>
                <option value="D" selected>D (SMP)</option>
                <option value="E">E (SMA X)</option>
                <option value="F">F (SMA XI-XII)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Elemen / Domain <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cp_elemen" name="elemen"
                     placeholder="Contoh: Bilangan, Aljabar, Membaca…" maxlength="150">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Jenjang <span class="text-danger">*</span></label>
              <select class="form-select" id="cp_jenjang" name="jenjang">
                <option value="1">SD</option>
                <option value="2" selected>SMP</option>
                <option value="3">SMA/SMK</option>
                <option value="4">SMK</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Kelompok Mapel</label>
              <input type="text" class="form-control" id="cp_kelompok" name="kelompok_mapel"
                     placeholder="Umum / Kejuruan" maxlength="50">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Deskripsi CP <span class="text-danger">*</span></label>
              <textarea class="form-control" id="cp_deskripsi" name="deskripsi_cp" rows="4"
                        placeholder="Deskripsi lengkap Capaian Pembelajaran sesuai dokumen BSKAP…"></textarea>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select" id="cp_aktif" name="is_active">
                <option value="1" selected>Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4" id="btn-simpan-cp">
            <span class="spinner-border spinner-border-sm d-none me-1" id="spinner-cp"></span>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    var modal = new bootstrap.Modal(document.getElementById('modalCp'));

    // --- Buka modal tambah ---
    $('#btn-tambah-cp').on('click', function () {
        $('#modal-cp-title').text('Tambah Capaian Pembelajaran');
        $('#form-cp')[0].reset();
        $('#cp_id').val(0);
        modal.show();
    });

    // --- Buka modal edit ---
    $(document).on('click', '.btn-edit-cp', function () {
        var b = $(this).data();
        $('#modal-cp-title').text('Edit CP: ' + b.kode);
        $('#cp_id').val(b.id);
        $('#cp_kode').val(b.kode);
        $('#cp_mapel').val(b.mapel);
        $('#cp_fase').val(b.fase);
        $('#cp_elemen').val(b.elemen);
        $('#cp_deskripsi').val(b.deskripsi);
        $('#cp_jenjang').val(b.jenjang);
        $('#cp_kelompok').val(b.kelompok);
        $('#cp_aktif').val(b.aktif);
        modal.show();
    });

    // --- Simpan CP ---
    $('#form-cp').on('submit', function (e) {
        e.preventDefault();
        $('#spinner-cp').removeClass('d-none');
        $('#btn-simpan-cp').prop('disabled', true);

        $.post('<?= base_url('cbt-quality/capaian-pembelajaran/save') ?>', $(this).serialize())
          .done(function (res) {
              if (res.status) {
                  modal.hide();
                  Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                    .then(function () { location.reload(); });
              } else {
                  var errHtml = '';
                  if (res.errors) {
                      $.each(res.errors, function (k, v) { errHtml += '<li>' + v + '</li>'; });
                  }
                  Swal.fire({ icon: 'error', title: 'Gagal', html: '<ul>' + errHtml + '</ul>' || res.message });
              }
          })
          .fail(function () { Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan.' }); })
          .always(function () {
              $('#spinner-cp').addClass('d-none');
              $('#btn-simpan-cp').prop('disabled', false);
          });
    });

    // --- Hapus CP ---
    $(document).on('click', '.btn-delete-cp', function () {
        var id = $(this).data('id'), kode = $(this).data('kode');
        Swal.fire({
            icon: 'warning', title: 'Hapus CP?',
            text: 'Kode "' + kode + '" akan dihapus permanen. Pastikan tidak digunakan TP manapun.',
            showCancelButton: true, confirmButtonText: 'Ya, Hapus', confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.post('<?= base_url('cbt-quality/capaian-pembelajaran/delete') ?>', { id_cp: id })
              .done(function (res) {
                  if (res.status) {
                      Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(function () { location.reload(); });
                  } else {
                      Swal.fire({ icon: 'error', title: 'Tidak dapat dihapus', text: res.message });
                  }
              });
        });
    });

    // --- Seed data sampel ---
    $('#btn-seed').on('click', function () {
        Swal.fire({
            icon: 'question', title: 'Seed Data Sampel CP?',
            text: 'Menambahkan data CP sampel. Hanya berjalan jika tabel masih kosong.',
            showCancelButton: true, confirmButtonText: 'Ya, Seed',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post('<?= base_url('cbt-quality/capaian-pembelajaran/seed') ?>')
              .done(function (res) {
                  Swal.fire({ icon: res.status ? 'success' : 'info', text: res.message })
                    .then(function () { if (res.status) location.reload(); });
              });
        });
    });
});
</script>
<?= $this->endSection() ?>
