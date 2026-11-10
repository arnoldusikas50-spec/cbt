// detail-analysis.js
// Widget analisis kualitas kisi-kisi (Bloom + ketercakupan materi).
// Kebutuhan: jQuery, Bootstrap, `base_url` (layout), `window.KARTU_SOAL_DETAIL.uuid`.
(function () {
    'use strict';

    var UUID = (window.KARTU_SOAL_DETAIL || {}).uuid || '';

    // ---- Bloom Validator ----
    function muatBloom() {
        var tipe = $('#bloom-tipe-ujian').val() || 'uts';
        $('#spin-bloom').removeClass('d-none');
        $('#btn-muat-bloom').prop('disabled', true);

        $.getJSON(base_url + 'cbtkartusoal/api/analisis-distribusi/' + UUID + '?tipe=' + tipe)
          .done(function (res) {
              if (!res.status) {
                  $('#bloom-rekomendasi').html('<div class="alert alert-danger small">' + (res.message || 'Gagal memuat') + '</div>');
                  return;
              }
              var d = res.data;
              var persen = d.persentase || {};
              var target = d.target || {};
              var total  = d.total_item || 0;
              var aktual = d.distribusi || {};

              ['C1','C2','C3','C4','C5','C6'].forEach(function (lv) {
                  var pct = parseFloat(persen[lv] || 0);
                  var tgt = parseFloat(target[lv] || 0);
                  var n   = parseInt(aktual[lv] || 0);
                  $('#bloom-n-' + lv).text(n);
                  $('#bloom-pct-' + lv).text(pct.toFixed(1) + '%');
                  $('#bloom-bar-' + lv).css('width', pct + '%');
                  $('#bloom-target-' + lv).text('target ' + tgt + '%');
              });

              var hots = parseFloat(d.skor_hots || 0);
              var lots = parseFloat((persen['C1']||0)) + parseFloat((persen['C2']||0));
              var mots = parseFloat(persen['C3'] || 0);
              $('#bloom-sum-lots').text(lots.toFixed(1) + '%');
              $('#bloom-sum-mots').text(mots.toFixed(1) + '%');
              $('#bloom-sum-hots').text(hots.toFixed(1) + '%')
                .removeClass('text-success text-danger')
                .addClass(hots >= 40 ? 'text-success' : 'text-danger');

              // Badge di header
              var badgeCls = d.valid ? 'bg-success' : 'bg-warning text-dark';
              $('#bloom-badge').attr('class', 'ms-2 badge ' + badgeCls)
                               .text(d.ringkasan || '');

              // Rekomendasi
              var rekom = d.rekomendasi || [];
              var html = '';
              if (rekom.length === 0) {
                  html = '<div class="alert alert-success small py-2 mb-0">' +
                         '<i class="bi bi-check-circle me-1"></i>Distribusi Bloom memenuhi target untuk ' +
                         (d.tipe_label || tipe) + '.</div>';
              } else {
                  rekom.forEach(function (r) {
                      html += '<div class="alert alert-warning small py-2 mb-1">' +
                              '<i class="bi bi-exclamation-triangle me-1"></i>' +
                              (r.pesan || r.message || '') + '</div>';
                  });
              }
              $('#bloom-rekomendasi').html(html);
          })
          .fail(function () {
              $('#bloom-rekomendasi').html('<div class="alert alert-danger small">Gagal memuat analisis. Coba lagi.</div>');
          })
          .always(function () {
              $('#spin-bloom').addClass('d-none');
              $('#btn-muat-bloom').prop('disabled', false);
          });
    }

    $('#btn-muat-bloom').on('click', muatBloom);
    $('#bloom-tipe-ujian').on('change', muatBloom);

    // ---- Validasi Materi ----
    $('#btn-muat-materi').on('click', function () {
        $('#spin-materi').removeClass('d-none');
        $('#btn-muat-materi').prop('disabled', true);

        $.getJSON(base_url + 'cbtkartusoal/api/validasi-materi/' + UUID)
          .done(function (res) {
              if (!res.status) {
                  $('#materi-content').html('<div class="alert alert-danger small">' + (res.message || 'Gagal') + '</div>');
                  return;
              }
              var d = res.data;

              // Badge TP coverage
              var pct    = d.pct_tp_cover || 0;
              var badgeCls = pct >= 70 ? 'bg-success' : 'bg-warning text-dark';
              $('#materi-badge').attr('class', 'ms-2 badge ' + badgeCls)
                                .text(d.tp_tercover + '/' + d.tp_total + ' TP (' + pct + '%)');

              var html = '<div class="row g-3">';

              // Ketercakupan TP
              html += '<div class="col-md-4">'
                    + '<div class="card border-0 bg-light text-center p-3">'
                    + '<div class="fs-4 fw-bold ' + (pct >= 70 ? 'text-success' : 'text-warning') + '">'
                    + d.tp_tercover + ' / ' + d.tp_total + '</div>'
                    + '<div class="small text-muted">TP Tercakup</div>'
                    + '<div class="progress mt-2" style="height:8px"><div class="progress-bar ' + (pct>=70?'bg-success':'bg-warning') + '" style="width:' + pct + '%"></div></div>'
                    + '<div class="small mt-1 text-muted">' + pct + '%</div>'
                    + '</div></div>';

              // Distribusi materi
              html += '<div class="col-md-8"><h6 class="small fw-semibold mb-2">Distribusi per Materi Pokok</h6>';
              var total = d.total_soal || 1;
              (d.per_materi || []).forEach(function (m) {
                  var mpct = parseFloat((m.jumlah / total * 100).toFixed(1));
                  var barCls = mpct > 40 ? 'bg-warning' : 'bg-primary';
                  html += '<div class="mb-1">'
                        + '<div class="d-flex justify-content-between small">'
                        + '<span class="text-truncate" style="max-width:200px" title="' + m.materi + '">' + m.materi + '</span>'
                        + '<span class="text-muted">' + m.jumlah + ' (' + mpct + '%)</span>'
                        + '</div>'
                        + '<div class="progress" style="height:6px"><div class="progress-bar ' + barCls + '" style="width:' + mpct + '%"></div></div>'
                        + '</div>';
              });
              html += '</div>';

              html += '</div>';

              // Peringatan
              if ((d.warnings || []).length > 0) {
                  html += '<div class="mt-3">';
                  d.warnings.forEach(function (w) {
                      html += '<div class="alert alert-warning small py-2 mb-1">'
                            + '<i class="bi bi-exclamation-triangle me-1"></i>' + w.pesan + '</div>';
                  });
                  html += '</div>';
              } else {
                  html += '<div class="alert alert-success small py-2 mt-3">'
                        + '<i class="bi bi-check-circle me-1"></i>Ketercakupan materi baik.</div>';
              }

              $('#materi-content').html(html);
          })
          .fail(function () {
              $('#materi-content').html('<div class="alert alert-danger small">Gagal memuat analisis. Coba lagi.</div>');
          })
          .always(function () {
              $('#spin-materi').addClass('d-none');
              $('#btn-muat-materi').prop('disabled', false);
          });
    });

    // Auto-load analisis saat accordion dibuka pertama kali (Bloom & Materi)
    document.getElementById('collapseBloom')
      .addEventListener('show.bs.collapse', function () {
          if ($('#bloom-badge').text() === '—') muatBloom();
      });
    document.getElementById('collapseMateri')
      .addEventListener('show.bs.collapse', function () {
          if ($('#materi-badge').text() === '—') $('#btn-muat-materi').trigger('click');
      });

}());
