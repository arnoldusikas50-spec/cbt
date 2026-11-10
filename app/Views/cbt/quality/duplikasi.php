<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h3 class="mb-0 fw-bold"><?= esc($judul) ?></h3>
                <small class="text-muted"><?= esc($subjudul) ?></small>
            </div>
            <div class="col-sm-5 text-end">
                <a href="<?= base_url('cbt-quality') ?>" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left me-1"></i>Dashboard
                </a>
                <button class="btn btn-sm btn-outline-warning" id="btn-rehash-missing"
                    <?= $tanpa_hash === 0 ? 'disabled title="Semua soal sudah punya hash"' : '' ?>>
                    <span class="spinner-border spinner-border-sm d-none me-1" id="spin-rehash"></span>
                    <i class="bi bi-hash me-1"></i>Rehash <?= $tanpa_hash ?> Soal
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content fade-in-up">
  <div class="container-fluid">

    <?php if ($tanpa_hash > 0): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i>
      <div>
        <strong><?= $tanpa_hash ?> soal</strong> belum punya fingerprint hash.
        Deteksi duplikasi tidak akurat sebelum semua soal di-hash.
        Klik <strong>Rehash</strong> untuk memprosesnya.
      </div>
    </div>
    <?php endif; ?>

    <!-- Rehash per bank -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent border-0">
        <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Rehash per Bank Soal</h6>
      </div>
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label small fw-semibold">Pilih Bank Soal</label>
            <select id="select-bank-rehash" class="form-select form-select-sm">
              <option value="">— Pilih bank soal —</option>
              <?php foreach ($banks as $b): ?>
              <option value="<?= $b->id_bank ?>">
                [<?= esc($b->bank_kode) ?>] <?= esc($b->nama_mapel ?? '-') ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-sm btn-primary" id="btn-rehash-bank">
              <span class="spinner-border spinner-border-sm d-none me-1" id="spin-rehash-bank"></span>
              <i class="bi bi-arrow-repeat me-1"></i>Rehash Bank Ini
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Laporan duplikasi -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-copy me-2 text-danger"></i>Soal Duplikat Lintas Bank</h6>
        <button class="btn btn-sm btn-outline-primary" id="btn-load-laporan">
          <span class="spinner-border spinner-border-sm d-none me-1" id="spin-laporan"></span>
          <i class="bi bi-search me-1"></i>Muat Laporan
        </button>
      </div>
      <div class="card-body p-0">
        <div id="hasil-laporan">
          <div class="text-center py-4 text-muted">
            <i class="bi bi-search fs-3 d-block mb-2"></i>
            Klik <strong>Muat Laporan</strong> untuk mencari duplikasi soal.
          </div>
        </div>
      </div>
    </div>

    <!-- Penjelasan -->
    <div class="card border-0 bg-light mt-3">
      <div class="card-body py-3 small text-muted">
        <strong>Cara Kerja Deteksi Duplikasi:</strong>
        Sistem menggunakan <em>bag-of-words fingerprint</em> (MD5) — teks soal dinormalisasi
        (strip HTML, lowercase, hapus stopword, urutkan kata) lalu di-hash.
        Soal dengan hash identik dianggap duplikat.
        <br><strong>Keterbatasan:</strong> Parafrase (kalimat berbeda, makna sama) tidak terdeteksi.
        Untuk deteksi kemiripan penuh diperlukan model NLP.
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {

    // --- Rehash semua yang belum punya hash ---
    $('#btn-rehash-missing').on('click', function () {
        Swal.fire({
            icon: 'question', title: 'Rehash Soal?',
            text: 'Sistem akan menghitung fingerprint untuk semua soal yang belum diproses (maks 500 per batch).',
            showCancelButton: true, confirmButtonText: 'Ya, Jalankan',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $('#spin-rehash').removeClass('d-none');
            $('#btn-rehash-missing').prop('disabled', true);

            $.post('<?= base_url('cbt-quality/rehash-missing') ?>', { limit: 500 })
              .done(function (res) {
                  Swal.fire({ icon: res.status ? 'success' : 'warning', text: res.message })
                    .then(function () { if (res.status) location.reload(); });
              })
              .fail(function () { Swal.fire({ icon: 'error', text: 'Terjadi kesalahan.' }); })
              .always(function () {
                  $('#spin-rehash').addClass('d-none');
                  $('#btn-rehash-missing').prop('disabled', false);
              });
        });
    });

    // --- Rehash per bank ---
    $('#btn-rehash-bank').on('click', function () {
        var bankId = $('#select-bank-rehash').val();
        if (!bankId) { Swal.fire({ icon: 'warning', text: 'Pilih bank soal terlebih dahulu.' }); return; }

        $('#spin-rehash-bank').removeClass('d-none');
        $('#btn-rehash-bank').prop('disabled', true);

        $.post('<?= base_url('cbt-quality/rehash-bank') ?>/' + bankId)
          .done(function (res) {
              Swal.fire({ icon: res.status ? 'success' : 'warning', text: res.message });
          })
          .fail(function () { Swal.fire({ icon: 'error', text: 'Terjadi kesalahan.' }); })
          .always(function () {
              $('#spin-rehash-bank').addClass('d-none');
              $('#btn-rehash-bank').prop('disabled', false);
          });
    });

    // --- Muat laporan duplikasi ---
    $('#btn-load-laporan').on('click', function () {
        $('#spin-laporan').removeClass('d-none');
        $('#btn-load-laporan').prop('disabled', true);

        $.getJSON('<?= base_url('cbt-quality/laporan-duplikasi') ?>', function (res) {
            if (!res.status) {
                $('#hasil-laporan').html('<div class="p-3 text-danger">Gagal: ' + res.message + '</div>');
                return;
            }
            var laporan = res.data.laporan ?? [];
            if (laporan.length === 0) {
                $('#hasil-laporan').html(
                    '<div class="text-center py-4 text-success">' +
                    '<i class="bi bi-check-circle fs-3 d-block mb-2"></i>' +
                    '<strong>Tidak ditemukan duplikasi soal lintas bank!</strong></div>'
                );
                return;
            }

            var html = '<div class="alert alert-warning m-3">'
                + '<i class="bi bi-exclamation-triangle me-2"></i>'
                + '<strong>' + laporan.length + ' kelompok duplikasi</strong> ditemukan. '
                + 'Tinjau dan pertimbangkan untuk merevisi atau menghapus soal yang identik.'
                + '</div>';

            html += '<div class="table-responsive"><table class="table table-sm table-hover mb-0">'
                + '<thead class="table-light"><tr>'
                + '<th>#</th><th>Ditemukan Di</th><th class="text-center">Jml Bank</th>'
                + '<th class="text-center">Jml Soal</th><th>Cuplikan</th>'
                + '</tr></thead><tbody>';

            laporan.forEach(function (row, idx) {
                html += '<tr>'
                    + '<td class="text-muted small">' + (idx + 1) + '</td>'
                    + '<td class="small">' + esc(row.lokasi ?? '-') + '</td>'
                    + '<td class="text-center"><span class="badge bg-danger">' + row.jml_bank + '</span></td>'
                    + '<td class="text-center">' + row.jml_soal + '</td>'
                    + '<td class="small text-muted" style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">'
                    +   esc((row.cuplikan || '').replace(/<[^>]*>/g, '')) + '</td>'
                    + '</tr>';
            });
            html += '</tbody></table></div>';
            $('#hasil-laporan').html(html);
        })
        .fail(function () {
            $('#hasil-laporan').html('<div class="p-3 text-danger">Terjadi kesalahan jaringan.</div>');
        })
        .always(function () {
            $('#spin-laporan').addClass('d-none');
            $('#btn-load-laporan').prop('disabled', false);
        });
    });

    function esc(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s || ''));
        return d.innerHTML;
    }
});
</script>
<?= $this->endSection() ?>
