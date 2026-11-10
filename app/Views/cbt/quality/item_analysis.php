<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-7">
                <h3 class="mb-0 fw-bold">Item Analysis Bank Soal</h3>
                <small class="text-muted">Indeks Kesulitan (p) &bull; Daya Beda (D) &bull; Tag Empiris</small>
            </div>
            <div class="col-sm-5 text-end">
                <a href="<?= base_url('cbt-quality') ?>" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
                <button class="btn btn-sm btn-primary" id="btn-run-analysis">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="spin-run"></span>
                    <i class="bi bi-play-circle me-1"></i>Jalankan Analisis
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content fade-in-up">
  <div class="container-fluid">

    <!-- Info bank -->
    <div class="card border-0 shadow-sm mb-4" id="card-bank-info" style="display:none">
      <div class="card-body py-3">
        <div class="row g-3 align-items-center">
          <div class="col-md-5">
            <h6 class="mb-1 fw-bold" id="bank-nama-mapel">—</h6>
            <small class="text-muted" id="bank-kode">—</small>
          </div>
          <div class="col-6 col-md-auto text-center">
            <div class="fw-bold fs-5 text-primary" id="bank-total-soal">—</div>
            <div class="text-muted" style="font-size:.75rem">Total Soal</div>
          </div>
          <div class="col-6 col-md-auto text-center">
            <div class="fw-bold fs-5 text-success" id="bank-sudah-analisis">—</div>
            <div class="text-muted" style="font-size:.75rem">Sudah Dianalisis</div>
          </div>
          <div class="col-6 col-md-auto text-center">
            <div class="fw-bold fs-5" id="bank-avg-p">—</div>
            <div class="text-muted" style="font-size:.75rem">Rerata p</div>
          </div>
          <div class="col-6 col-md-auto text-center">
            <div class="fw-bold fs-5" id="bank-avg-d">—</div>
            <div class="text-muted" style="font-size:.75rem">Rerata D</div>
          </div>
          <div class="col-6 col-md-auto text-center">
            <div class="fw-bold fs-5 text-danger" id="bank-perlu-revisi">—</div>
            <div class="text-muted" style="font-size:.75rem">Perlu Revisi</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alert belum ada data -->
    <div id="alert-no-data" class="alert alert-info d-none">
      <i class="bi bi-info-circle me-2"></i>Belum ada data item analysis.
      Klik <strong>Jalankan Analisis</strong> untuk menghitung statistik soal berdasarkan jadwal ujian terakhir.
    </div>

    <!-- Tabel soal -->
    <div class="card border-0 shadow-sm" id="card-soal-table" style="display:none">
      <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Statistik Butir Soal</h6>
        <div class="d-flex gap-2">
          <select id="filter-tag" class="form-select form-select-sm" style="width:140px">
            <option value="">Semua Tag</option>
            <option value="mudah">Mudah</option>
            <option value="sedang">Sedang</option>
            <option value="sulit">Sulit</option>
          </select>
          <select id="filter-kualitas" class="form-select form-select-sm" style="width:170px">
            <option value="">Semua Kualitas</option>
            <option value="revisi">Perlu Revisi (D &lt; 0.20)</option>
            <option value="baik">Baik (D ≥ 0.40)</option>
          </select>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0" id="tbl-soal">
            <thead class="table-light sticky-top">
              <tr>
                <th class="text-center" style="width:50px">No</th>
                <th class="text-center" style="width:60px">Jenis</th>
                <th>Cuplikan Soal</th>
                <th class="text-center" style="width:70px">
                  p <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                    title="Indeks Kesulitan: 0=sulit, 1=mudah. Ideal: 0.30-0.70"></i>
                </th>
                <th class="text-center" style="width:70px">
                  D <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip"
                    title="Daya Beda: &lt;0.20=jelek, 0.20-0.39=cukup, ≥0.40=baik"></i>
                </th>
                <th class="text-center" style="width:90px">Tag Empiris</th>
                <th class="text-center" style="width:90px">Level Kisi</th>
                <th class="text-center" style="width:70px">Dipakai</th>
                <th class="text-center" style="width:90px">Kualitas</th>
              </tr>
            </thead>
            <tbody id="tbody-soal">
              <tr><td colspan="9" class="text-center py-3 text-muted">Memuat data…</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Legenda -->
    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-3 small">
          <div><strong>Indeks Kesulitan (p):</strong>
            <span class="badge bg-success ms-1">p ≥ 0.70 Mudah</span>
            <span class="badge bg-warning text-dark ms-1">0.30 ≤ p &lt; 0.70 Sedang</span>
            <span class="badge bg-danger ms-1">p &lt; 0.30 Sulit</span>
          </div>
          <div><strong>Daya Beda (D):</strong>
            <span class="badge bg-success ms-1">D ≥ 0.40 Baik</span>
            <span class="badge bg-info ms-1">0.20 ≤ D &lt; 0.40 Cukup</span>
            <span class="badge bg-warning text-dark ms-1">D &lt; 0.20 Perlu Revisi</span>
            <span class="badge bg-danger ms-1">D &lt; 0 Buruk</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    var bankId = <?= (int) ($bank->id_bank ?? 0) ?>;
    var allRows = [];

    // Tooltip
    $('[data-bs-toggle="tooltip"]').each(function () { new bootstrap.Tooltip(this); });

    // --- Muat data awal ---
    function loadData() {
        $.getJSON('<?= base_url('cbt-quality/item-analysis') ?>/' + bankId, function (res) {
            if (!res.status) { return; }
            var d = res.data;

            // Info bank
            $('#bank-nama-mapel').text(d.bank?.nama_mapel ?? '-');
            $('#bank-kode').text(d.bank?.bank_kode ?? '-');
            var r = d.ringkasan ?? {};
            $('#bank-total-soal').text(r.total ?? 0);
            $('#bank-sudah-analisis').text(r.sudah_dianalisis ?? 0);
            $('#bank-avg-p').text(r.avg_p ? parseFloat(r.avg_p).toFixed(3) : '—');
            $('#bank-avg-d').text(r.avg_d ? parseFloat(r.avg_d).toFixed(3) : '—');
            $('#bank-perlu-revisi').text(r.soal_perlu_revisi ?? 0);
            $('#card-bank-info').show();

            allRows = d.soals ?? [];
            if (allRows.length === 0) {
                $('#alert-no-data').removeClass('d-none');
                $('#card-soal-table').hide();
            } else {
                $('#alert-no-data').addClass('d-none');
                renderTable(allRows);
                $('#card-soal-table').show();
            }
        });
    }

    function renderTable(rows) {
        var tagF   = $('#filter-tag').val();
        var qualF  = $('#filter-kualitas').val();
        var filtered = rows.filter(function (s) {
            if (tagF && s.tag_kesulitan !== tagF) return false;
            if (qualF === 'revisi' && (s.stat_d === null || parseFloat(s.stat_d) >= 0.20)) return false;
            if (qualF === 'baik'   && (s.stat_d === null || parseFloat(s.stat_d) < 0.40))  return false;
            return true;
        });

        var html = '';
        filtered.forEach(function (s, idx) {
            var p    = s.stat_p !== null ? parseFloat(s.stat_p) : null;
            var d    = s.stat_d !== null ? parseFloat(s.stat_d) : null;
            var pStr = p !== null ? p.toFixed(3) : '—';
            var dStr = d !== null ? d.toFixed(3) : '—';
            var pCls = p === null ? '' : (p >= 0.70 ? 'text-success fw-bold' : p < 0.30 ? 'text-danger fw-bold' : 'text-warning fw-bold');
            var dCls = d === null ? '' : (d < 0 ? 'text-danger fw-bold' : d < 0.20 ? 'text-warning fw-bold' : d >= 0.40 ? 'text-success fw-bold' : '');

            var tagBadge = s.tag_kesulitan
                ? (s.tag_kesulitan === 'mudah'  ? '<span class="badge bg-success">Mudah</span>'
                :  s.tag_kesulitan === 'sulit'  ? '<span class="badge bg-danger">Sulit</span>'
                :                                 '<span class="badge bg-warning text-dark">Sedang</span>')
                : '<span class="text-muted small">—</span>';

            var kualitas = d === null ? '<span class="text-muted">Belum</span>'
                : d < 0     ? '<span class="badge bg-danger">Buruk</span>'
                : d < 0.20  ? '<span class="badge bg-warning text-dark">Perlu Revisi</span>'
                : d < 0.40  ? '<span class="badge bg-info text-dark">Cukup</span>'
                :             '<span class="badge bg-success">Baik</span>';

            var jenisMap = {1:'PG',2:'Kompleks',3:'Jodohkan',4:'Isian',5:'Esai',6:'Urutan',7:'Cloze'};
            var jenisStr = jenisMap[s.jenis] || s.jenis;

            html += '<tr>'
                + '<td class="text-center text-muted small">' + s.nomor_soal + '</td>'
                + '<td class="text-center"><span class="badge bg-secondary">' + jenisStr + '</span></td>'
                + '<td class="small text-muted" style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">'
                +   (s.cuplikan_soal ? s.cuplikan_soal.replace(/<[^>]*>/g, '') : '—') + '</td>'
                + '<td class="text-center ' + pCls + '">' + pStr + '</td>'
                + '<td class="text-center ' + dCls + '">' + dStr + '</td>'
                + '<td class="text-center">' + tagBadge + '</td>'
                + '<td class="text-center"><code class="text-muted">' + (s.level_kisi || '—') + '</code></td>'
                + '<td class="text-center text-muted small">' + (s.stat_used_count ?? 0) + 'x</td>'
                + '<td class="text-center">' + kualitas + '</td>'
                + '</tr>';
        });
        $('#tbody-soal').html(html || '<tr><td colspan="9" class="text-center py-3 text-success">Tidak ada soal yang cocok filter ini</td></tr>');
    }

    $('#filter-tag, #filter-kualitas').on('change', function () { renderTable(allRows); });

    // --- Jalankan analisis ---
    $('#btn-run-analysis').on('click', function () {
        Swal.fire({
            icon: 'question', title: 'Jalankan Item Analysis?',
            text: 'Sistem akan menghitung indeks kesulitan (p) dan daya beda (D) berdasarkan jadwal ujian terakhir bank soal ini.',
            showCancelButton: true, confirmButtonText: 'Ya, Jalankan',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $('#spin-run').removeClass('d-none');
            $('#btn-run-analysis').prop('disabled', true);

            $.post('<?= base_url('cbt-quality/run-analysis') ?>/' + bankId)
              .done(function (res) {
                  Swal.fire({
                      icon: res.status ? 'success' : 'warning',
                      title: res.status ? 'Selesai' : 'Peringatan',
                      text: res.message,
                  }).then(function () { if (res.status) loadData(); });
              })
              .fail(function () { Swal.fire({ icon: 'error', text: 'Terjadi kesalahan jaringan.' }); })
              .always(function () {
                  $('#spin-run').addClass('d-none');
                  $('#btn-run-analysis').prop('disabled', false);
              });
        });
    });

    loadData();
});
</script>
<?= $this->endSection() ?>
