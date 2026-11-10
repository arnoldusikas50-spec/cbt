<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="app-content-header fade-in-up">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h3 class="mb-0 fw-bold"><?= esc($judul) ?></h3>
                <small class="text-muted"><?= esc($subjudul) ?></small>
            </div>
            <div class="col-sm-6 text-end">
                <span class="badge bg-primary fs-6">
                    <?= esc($tp_active->tahun ?? '-') ?> &bull; <?= esc($smt_active->nama_smt ?? '-') ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="app-content fade-in-up">
  <div class="container-fluid">

    <!-- Loading state -->
    <div id="loading-state" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Memuat data kualitas bank soal...</p>
    </div>

    <!-- Dashboard content (hidden until loaded) -->
    <div id="dashboard-content" style="display:none">

      <!-- Baris 1: Kartu statistik utama -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
              <div class="display-6 fw-bold text-primary" id="stat-bank-total">—</div>
              <div class="text-muted small mt-1">Total Bank Soal</div>
              <div class="mt-1"><span class="badge bg-success" id="stat-bank-siap">—</span> <small class="text-muted">siap</small></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
              <div class="display-6 fw-bold text-success" id="stat-kartu-approved">—</div>
              <div class="text-muted small mt-1">Kisi-kisi Approved</div>
              <div class="mt-1"><span class="badge bg-warning text-dark" id="stat-kartu-pending">—</span> <small class="text-muted">pending</small></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
              <div class="display-6 fw-bold" id="stat-hots-pct" style="color:#6366f1">—%</div>
              <div class="text-muted small mt-1">Proporsi HOTS (C4–C6)</div>
              <div class="mt-1 small text-muted">Target ≥ 40%</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
              <div class="display-6 fw-bold text-danger" id="stat-soal-revisi">—</div>
              <div class="text-muted small mt-1">Soal Perlu Revisi</div>
              <div class="mt-1 small text-muted">Daya beda D &lt; 0.20</div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">

        <!-- Distribusi Bloom -->
        <div class="col-md-5">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Distribusi Taksonomi Bloom</h6>
              <select id="tipe-ujian-select" class="form-select form-select-sm w-auto">
                <?php foreach ($tipe_ujian as $t): ?>
                  <option value="<?= esc($t['value']) ?>"><?= esc($t['label']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="card-body">
              <div id="bloom-bars">
                <?php foreach (['C1'=>'secondary','C2'=>'info','C3'=>'primary','C4'=>'warning','C5'=>'danger','C6'=>'success'] as $lv => $color): ?>
                <div class="mb-2">
                  <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold"><?= $lv ?></span>
                    <span id="bloom-pct-<?= $lv ?>" class="text-muted">—%</span>
                  </div>
                  <div class="progress" style="height:12px;border-radius:6px">
                    <div id="bloom-bar-<?= $lv ?>" class="progress-bar bg-<?= $color ?>" style="width:0%" role="progressbar"></div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="mt-3 pt-2 border-top small text-center">
                <span class="text-muted">LOTS: </span><strong id="sum-lots">—</strong>
                &nbsp;|&nbsp;
                <span class="text-muted">MOTS: </span><strong id="sum-mots">—</strong>
                &nbsp;|&nbsp;
                <span class="text-muted">HOTS: </span><strong id="sum-hots" class="text-success">—</strong>
              </div>
            </div>
          </div>
        </div>

        <!-- Soal perlu revisi -->
        <div class="col-md-7">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
              <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Soal dengan Daya Beda Rendah (D &lt; 0.20)</h6>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="tbl-soal-revisi">
                  <thead class="table-light">
                    <tr>
                      <th>Bank</th>
                      <th>Mapel</th>
                      <th class="text-center">No</th>
                      <th class="text-center">p</th>
                      <th class="text-center">D</th>
                      <th class="text-center">Tag</th>
                    </tr>
                  </thead>
                  <tbody><tr><td colspan="6" class="text-center py-3 text-muted">Memuat…</td></tr></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /row -->

      <!-- Guru belum input TP -->
      <div class="row g-3 mt-1">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold"><i class="bi bi-person-exclamation me-2 text-danger"></i>Guru Belum Input Tujuan Pembelajaran</h6>
              <a href="<?= base_url('cbt-quality/capaian-pembelajaran') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-book me-1"></i>Master CP
              </a>
            </div>
            <div class="card-body" id="guru-belum-tp">
              <span class="text-muted">Memuat…</span>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /#dashboard-content -->
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    var TARGET_HOTS = { uts: 55, uas: 65, us: 70, anbk: 80, ulangan_harian: 40 };

    function loadSummary() {
        $.getJSON('<?= base_url('cbt-quality/summary') ?>', function (res) {
            $('#loading-state').hide();
            if (!res.status) { alert('Gagal memuat data: ' + res.message); return; }
            var d = res.data;

            // Kartu statistik
            $('#stat-bank-total').text(d.banks?.total_bank ?? 0);
            $('#stat-bank-siap').text(d.banks?.bank_siap ?? 0);
            $('#stat-kartu-approved').text(d.kartus?.kartu_approved ?? 0);
            $('#stat-kartu-pending').text(d.kartus?.kartu_pending ?? 0);

            var hotsPct = parseFloat(d.hots_pct ?? 0);
            $('#stat-hots-pct').text(hotsPct.toFixed(1) + '%')
              .removeClass('text-danger text-success')
              .addClass(hotsPct >= 40 ? 'text-success' : 'text-danger');
            $('#stat-soal-revisi').text(d.soal_perlu_revisi?.length ?? 0);

            // Distribusi Bloom
            var bm = d.bloom_map ?? {};
            var total = d.total_item_kisi ?? 1;
            var lots = 0, mots = 0, hots = 0;
            ['C1','C2','C3','C4','C5','C6'].forEach(function (lv) {
                var n   = bm[lv] ?? 0;
                var pct = total > 0 ? (n / total * 100).toFixed(1) : 0;
                $('#bloom-pct-' + lv).text(pct + '%');
                $('#bloom-bar-' + lv).css('width', pct + '%');
                if (lv <= 'C2') lots += parseFloat(pct);
                else if (lv === 'C3') mots += parseFloat(pct);
                else hots += parseFloat(pct);
            });
            $('#sum-lots').text(lots.toFixed(1) + '%');
            $('#sum-mots').text(mots.toFixed(1) + '%');
            $('#sum-hots').text(hots.toFixed(1) + '%');

            // Soal perlu revisi
            var rows = '';
            (d.soal_perlu_revisi ?? []).forEach(function (s) {
                var tagBadge = s.tag_kesulitan === 'sulit'  ? '<span class="badge bg-danger">Sulit</span>'
                             : s.tag_kesulitan === 'mudah'  ? '<span class="badge bg-success">Mudah</span>'
                             : '<span class="badge bg-warning text-dark">Sedang</span>';
                var dClass   = parseFloat(s.stat_d) < 0 ? 'text-danger fw-bold' : 'text-warning fw-bold';
                rows += '<tr>'
                  + '<td><small>' + esc(s.bank_kode ?? '-') + '</small></td>'
                  + '<td><small>' + esc(s.nama_mapel ?? '-') + '</small></td>'
                  + '<td class="text-center">' + (s.nomor_soal ?? '-') + '</td>'
                  + '<td class="text-center">' + (s.stat_p ?? '—') + '</td>'
                  + '<td class="text-center ' + dClass + '">' + (s.stat_d ?? '—') + '</td>'
                  + '<td class="text-center">' + tagBadge + '</td>'
                  + '</tr>';
            });
            $('#tbl-soal-revisi tbody').html(rows || '<tr><td colspan="6" class="text-center text-success py-3">✓ Tidak ada soal dengan daya beda rendah</td></tr>');

            // Guru belum TP
            var chips = '';
            (d.guru_belum_tp ?? []).forEach(function (g) {
                chips += '<span class="badge bg-light text-dark border me-1 mb-1 p-2">'
                       + '<i class="bi bi-person me-1"></i>' + esc(g.nama_guru) + '</span>';
            });
            $('#guru-belum-tp').html(chips || '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Semua guru sudah input TP</span>');

            $('#dashboard-content').show();
        }).fail(function () {
            $('#loading-state').html('<div class="alert alert-danger">Gagal memuat data. Coba refresh halaman.</div>');
        });
    }

    function esc(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s || ''));
        return d.innerHTML;
    }

    loadSummary();

    // Refresh otomatis setiap 5 menit
    setInterval(loadSummary, 300000);
});
</script>
<?= $this->endSection() ?>
