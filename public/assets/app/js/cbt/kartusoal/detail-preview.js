/**
 * detail-preview.js
 * Preview & paket-soal helpers untuk halaman Detail Kartu Soal.
 * Diekstrak dari detail-manager.js (fungsi global mandiri).
 * Kebutuhan: jQuery, Bootstrap, Swal, `base_url` (layout), `window.KARTU_SOAL_DETAIL`.
 */
/**
 * Show "Detail Indikator & Contoh Soal" modal (Presisi dengan Desain Gambar)
 */
function showContohSoalModal(item) {
    if (!item) return;

    // 1. Hierarki Indikator
    const elemen = item.tujuan_elemen_cp || '—';
    const subelemen = item.tujuan_materi_pokok || '—';
    const kompetensi = (item.tujuan_kode ? item.tujuan_kode + ' — ' : '') + (item.tujuan || '—');
    const indikator = item.indikator_soal || '—';

    $('#prev_elemen').text(elemen);
    $('#prev_subelemen').text(subelemen);
    $('#prev_kompetensi').text(kompetensi);
    $('#prev_indikator').text(indikator);

    // 2. Metadata Soal
    const kodeSoal = item.soal_id ? ('SOAL-ID-' + item.soal_id) : ('ITEM-NO-' + (item.nomor || item.id_item));
    const bentukSoal = item.jenis_label || 'Pilihan Ganda';

    $('#prev_kode_soal').text(kodeSoal);
    $('#prev_bentuk_soal').text('Bentuk Soal : ' + bentukSoal);

    // 3. Teks Soal & Stimulus
    let butirText = (item.butir_soal || item.soal || '').trim();
    if (!butirText) {
        butirText = '<div class="alert alert-warning py-2 px-3 mb-0 rounded-3 text-center small"><i class="bi bi-info-circle me-1"></i>Belum ada butir soal spesifik yang dibuat untuk Indikator ini.</div>';
    }
    $('#prev_butir_soal').html(butirText);

    // 4. Pilihan Jawaban (Options)
    const $optsContainer = $('#prev_options_container').empty();
    const jenisId = parseInt(item.jenis_soal, 10) || (item.jenis_soal ? parseInt(item.jenis_soal, 10) : 1);
    const opsi = item.opsi_json || {};
    const jawaban = item.jawaban_benar_json;

    if (jenisId === 3) {
        /* Menjodohkan */
        const jodoh = renderJodohViewHtml(unpackJodohData({ jawaban: jawaban, opsi: opsi }));
        if (jodoh) {
            $optsContainer.html(jodoh);
        } else if (item.pembahasan) {
            $optsContainer.append('<div class="p-3 rounded-3 bg-light border"><strong class="small text-muted d-block mb-1">Pembahasan / Kunci:</strong><div>' + item.pembahasan + '</div></div>');
        } else {
            $optsContainer.html('<small class="text-muted fst-italic">Data pasangan menjodohkan belum diatur pada item kisi-kisi ini.</small>');
        }
    } else if (jenisId === 1 || jenisId === 2) {
        // PG / PGK
        const opsiNorm = (jenisId === 2) ? normalizePreviewOpsi({ opsi: opsi }) : buildOpsiKeyList(opsi);
        let keys = Object.keys(opsiNorm);
        if (keys.length > 0) {
            keys.forEach(key => {
                const isCorrect = isBankOptionCorrect(key, jawaban);
                let optVal = opsiNorm[key];
                let optText = typeof optVal === 'object' ? (optVal.teks || optVal.text || JSON.stringify(optVal)) : String(optVal);

                const $optCard = $(
                    '<div class="p-3 rounded-3 border bg-white d-flex align-items-start gap-3 text-dark transition-all" style="border-color: ' + (isCorrect ? '#22c55e' : '#e2e8f0') + ' !important; ' + (isCorrect ? 'background: #f0fdf4 !important;' : '') + '">' +
                    '  <div class="rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 24px; height: 24px; border-color: ' + (isCorrect ? '#22c55e' : '#cbd5e1') + ' !important; background: ' + (isCorrect ? '#22c55e' : '#fff') + '; color: ' + (isCorrect ? '#fff' : '#64748b') + '; font-size: 0.75rem; font-weight: bold;">' +
                    '    ' + (isCorrect ? '<i class="bi bi-check-lg"></i>' : String(key).toUpperCase()) +
                    '  </div>' +
                    '  <div class="flex-grow-1" style="font-size: 0.9rem; line-height: 1.5;">' + (optText || '-') + '</div>' +
                    '</div>'
                );
                $optsContainer.append($optCard);
            });
            if (jenisId === 2 && jawaban) {
                const kunciTxt = formatJawabanText(jawaban);
                if (kunciTxt) $optsContainer.append('<div class="small text-success mt-2 transition-all"><i class="bi bi-check2-square me-1"></i><strong>Kunci:</strong> ' + kunciTxt + '</div>');
            }
        } else {
            $optsContainer.html('<small class="text-muted fst-italic">Pilihan jawaban belum diatur pada item kisi-kisi ini.</small>');
        }
    } else {
        const jkunci = formatJawabanText(jawaban);
        if (jkunci) {
            $optsContainer.append('<div class="p-3 rounded-3 bg-light border"><strong class="small text-muted d-block mb-1">Kunci Jawaban:</strong><div class="text-dark">' + jkunci + '</div></div>');
        }
        if (item.pembahasan) {
            $optsContainer.append('<div class="p-3 rounded-3 bg-light border mt-2"><strong class="small text-muted d-block mb-1">Pembahasan / Kunci:</strong><div>' + item.pembahasan + '</div></div>');
        }
        if (!jkunci && !item.pembahasan) {
            $optsContainer.html('<small class="text-muted fst-italic">Belum ada data jawaban pada item kisi-kisi ini.</small>');
        }
    }

    // 5. Tab per paket (hanya tampil jika ada paket/bank yang dibuat dari kartu ini)
    const pakets = (window.KARTU_SOAL_DETAIL && window.KARTU_SOAL_DETAIL.paketBanks) || [];
    const $tabBar    = $('#prevPaketTabs');
    const $kartuSec  = $('#prevKartuSection');
    const $paketSec  = $('#prevPaketSection');
    // nomor bank = bank_soal_nomor (per-jenis) bila item sudah tertaut; fallback ke nomor kisi
    $('#prevPaketNomor').val(parseInt(item.bank_soal_nomor || '0', 10) > 0 ? item.bank_soal_nomor : (item.nomor || item.id_item || 0));
    $('#prevPaketJenis').val(item.jenis_soal || 1);

    if ($tabBar.length && pakets.length > 0) {
        let tabHtml = '<button type="button" class="btn btn-sm btn-prev-tab btn-primary fw-semibold tab-prev-active" data-pre-view="kartu" data-bank-id="0">'
            + '<i class="bi bi-journal-text me-1"></i>Kisi-kisi</button>';
        pakets.forEach(function (pk) {
            const label     = pk.bank_label || pk.bank_nama || pk.bank_kode || ('Paket ' + (pk.letter || '?'));
            const letter    = pk.letter || '?';
            const totalText = pk.total_tampil != null && pk.total_soal != null
                ? (pk.total_tampil === pk.total_soal
                    ? pk.total_soal
                    : (pk.total_tampil + '/' + pk.total_soal))
                : (pk.total_soal || '');
            const tooltip   = (pk.bank_nama || pk.bank_kode || '') + (pk.bank_kode ? ' [' + pk.bank_kode + ']' : '');
            tabHtml += '<button type="button" class="btn btn-sm btn-outline-secondary btn-prev-tab"'
                + ' data-pre-view="paket"'
                + ' data-bank-id="' + parseInt(pk.id_bank || 0, 10) + '"'
                + ' data-letter="' + sdkEsc(letter) + '"'
                + ' data-bank-label="' + sdkEsc(label) + '"'
                + ' title="' + sdkEsc(tooltip) + '">'
                + '<i class="bi bi-box-seam me-1"></i>Paket ' + sdkEsc(letter)
                + (totalText ? ' <span class="badge bg-light text-dark border ms-1">' + totalText + '</span>' : '')
                + '</button>';
        });
        $tabBar.html(tabHtml).removeClass('d-none');
        $kartuSec.removeClass('d-none');
        $paketSec.addClass('d-none');
    } else if ($tabBar.length) {
        $tabBar.addClass('d-none').empty();
        $kartuSec.removeClass('d-none');
    }

    const prevModalEl = document.getElementById('modalPreviewContohSoal');
    if (prevModalEl) {
        if (window.bootstrap && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(prevModalEl).show();
        else $('#modalPreviewContohSoal').modal('show');
    }
}

// Klik tab di modal preview contoh soal (Kisi-kisi vs Paket X)
$(document).on('click', '.btn-prev-tab', function () {
    const preView   = $(this).data('preView');
    const bankId    = parseInt($(this).data('bankId') || '0', 10);
    const nomor     = parseInt($('#prevPaketNomor').val() || '0', 10);
    const jenis     = parseInt($('#prevPaketJenis').val() || '0', 10);
    const letter    = $(this).data('letter') || '?';
    const bankLabel = $(this).data('bankLabel') || ('Paket ' + letter);

    const $tabBar   = $('#prevPaketTabs');
    const $kartuSec = $('#prevKartuSection');
    const $paketSec = $('#prevPaketSection');

    $tabBar.find('.btn-prev-tab')
        .removeClass('btn-primary tab-prev-active')
        .addClass('btn-outline-secondary');
    $(this).removeClass('btn-outline-secondary').addClass('btn-primary tab-prev-active');

    if (preView === 'kartu') {
        $kartuSec.removeClass('d-none');
        $paketSec.addClass('d-none');
        return;
    }

    $kartuSec.addClass('d-none');
    $paketSec.removeClass('d-none');
    const $body = $('#prevPaketBody');
    $body.html('<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Memuat soal Paket ' + sdkEsc(letter) + ' nomor ' + nomor + '...</div>');

    // Find paket info for URL
    const pakets = (window.KARTU_SOAL_DETAIL && window.KARTU_SOAL_DETAIL.paketBanks) || [];
    const paketInfo = pakets.find(function (p) { return parseInt(p.id_bank, 10) === bankId; }) || {};

    $.get(base_url + 'cbtkartusoal/api/bank/' + bankId + '/soal/' + nomor + (jenis > 0 ? ('?jenis=' + jenis) : ''))
        .done(function (res) {
            if (!res || !res.status || !res.data) {
                $body.html('<div class="alert alert-warning py-2 px-3 mb-0 small"><i class="bi bi-info-circle me-1"></i>Soal nomor ' + nomor + ' belum tersedia di Paket ' + sdkEsc(letter) + '.</div>');
                return;
            }
            // Prepend paket header strip
            const detailUrl = paketInfo.url_detail || (base_url + 'cbtbanksoal/detail/' + bankId);
            const headerHtml = '<div class="d-flex align-items-center gap-2 mb-3 px-1 flex-wrap">'
                + '<span class="badge rounded-pill" style="background:#3b82f6;font-size:12px;padding:5px 12px;"><i class="bi bi-box-seam me-1"></i>Paket ' + sdkEsc(letter) + '</span>'
                + '<span class="fw-semibold text-dark small flex-grow-1">' + sdkEsc(bankLabel) + '</span>'
                + '<a href="' + sdkEsc(detailUrl) + '" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" title="Buka Bank Soal Paket ' + sdkEsc(letter) + '"><i class="bi bi-box-arrow-up-right"></i> Detail Bank</a>'
                + '</div>';
            $body.html(headerHtml + renderBankSoalView(res.data, { showAnswer: true }));
        })
        .fail(function () {
            $body.html('<div class="alert alert-danger py-2 px-3 mb-0 small">Gagal memuat soal dari Paket ' + sdkEsc(letter) + '.</div>');
        });
});

function sdkEsc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
window.sdkEsc = sdkEsc;

/**
 * Render detail satu soal bank (dipakai di tab paket preview & modal "Lihat Soal Semua Paket")
 */
function isBankOptionCorrect(key, jawaban) {
    if (jawaban === null || jawaban === undefined) return false;
    if (Array.isArray(jawaban)) return jawaban.length ? jawaban.indexOf(key) !== -1 : false;
    if (typeof jawaban === 'object') {
        // bentukan importFromBank: {"raw": "a"} atau {"raw": ["a","c"]}
        if (Object.prototype.hasOwnProperty.call(jawaban, 'raw') && Object.keys(jawaban).length === 1) {
            return isBankOptionCorrect(key, jawaban['raw']);
        }
        const v = jawaban[key];
        if (v === true || v === 1 || v === '1' || v === 'true' || v === 'on') return true;
        if (v === false || v === 0 || v === '0' || v === 'false') return false;
        if (v && typeof v === 'string' && String(v).toLowerCase().trim() === String(key).toLowerCase()) return true;
        return false;
    }
    return String(jawaban).toLowerCase().trim() === String(key).toLowerCase();
}

function pkT(v) {
    if (v === null || v === undefined) return '';
    if (typeof v === 'string') return sdkEsc(v);
    if (typeof v === 'object') return sdkEsc(JSON.stringify(v));
    return sdkEsc(String(v));
}

/* Normalisasi opsi ke bentuk {key: text}. Menangani map, array, array [{text:..}]. */
function buildOpsiKeyList(opsi) {
    const out = {};
    if (Array.isArray(opsi)) {
        const letters = 'abcdefghijklmnopqrstuvwxyz';
        opsi.forEach(function (el, i) {
            if (el === null || el === undefined) return;
            const key = letters[i] || ('p' + (i + 1));
            if (typeof el === 'object') out[key] = (el.text !== undefined ? el.text : (el.teks !== undefined ? el.teks : JSON.stringify(el)));
            else out[key] = String(el);
        });
        return out;
    }
    if (opsi && typeof opsi === 'object') {
        Object.keys(opsi).forEach(function (k) {
            out[k] = opsi[k];
        });
    }
    return out;
}

/* Buka opsi PG Kompleks yang ter-bungkus dalam satu kunci (mis. opsi_a = {a:..,b:..}) → {a:'..',b:'..'}. */
function normalizePreviewOpsi(soal) {
    const raw = (soal && soal.opsi) || {};
    if (!Array.isArray(raw) && typeof raw === 'object') {
        const keys = Object.keys(raw);
        if (keys.length === 1 && raw[keys[0]] && typeof raw[keys[0]] === 'object') {
            const inner = raw[keys[0]];
            const probe = (Array.isArray(inner) ? inner : (Object.keys(inner).length ? inner : null));
            if (probe) return buildOpsiKeyList(probe);
        }
    }
    return buildOpsiKeyList(raw);
}

/* Ambil struktur Menjodohkan dari bank/opsi: buka wrapper ber-kunci-tunggal {a:{model,tabel,...}}. */
function unpackJodohData(soal) {
    let data = (soal && soal.jawaban);
    if (data === null || data === undefined) data = (soal && soal.opsi);
    if (data && typeof data === 'object' && !Array.isArray(data)) {
        const keys = Object.keys(data);
        if (keys.length === 1 && data[keys[0]] && typeof data[keys[0]] === 'object') {
            const inner = data[keys[0]];
            if (Array.isArray(inner) || inner.model || inner.jawaban || inner.tabel || inner.kiri || inner.kanan || inner.baris) {
                data = inner;
            }
        }
    }
    return data;
}

/* Ringkas kunci jawaban (string / array / map boolean / {raw:...}) jadi teks bersih. */
function formatJawabanText(jawaban) {
    if (jawaban === null || jawaban === undefined || jawaban === '') return '';
    if (typeof jawaban === 'string') return jawaban;
    if (typeof jawaban === 'object' && Object.prototype.hasOwnProperty.call(jawaban, 'raw') && Object.keys(jawaban).length === 1) {
        return formatJawabanText(jawaban['raw']);
    }
    if (Array.isArray(jawaban)) {
        const txt = jawaban.map(k => String(k).toUpperCase()).join(', ');
        return /^[A-Z](, [A-Z])*$/.test(txt) ? txt : JSON.stringify(jawaban);
    }
    if (typeof jawaban === 'object') {
        const benar = [];
        Object.keys(jawaban).forEach(function (k) {
            const v = jawaban[k];
            if (v === true || v === 1 || v === '1' || (v && (v.correct === true || v.benar === true))) benar.push(String(k).toUpperCase());
        });
        if (benar.length) return benar.join(', ');
        return JSON.stringify(jawaban);
    }
    return String(jawaban);
}

function kunciBox(kunciTxt) {
    if (!kunciTxt) return '';
    return '<div class="p-3 rounded-3 bg-light border"><strong class="small text-muted d-block mb-1">Kunci Jawaban:</strong><div class="text-dark">' + pkT(kunciTxt) + '</div></div>';
}

/**
 * Render struktur Menjodohkan (jenis 3).
 *  - tabel  : baris[0] = header (kolom 0 = pernyataan, kolom 1.. = opsi kanan);
 *             sel bernilai 1/true = pasangan benar.
 *  - kiri/kanan : dua kolom daftar (fallback).
 *  - baris      : daftar opsi kanan polos (fallback).
 */
function renderJodohViewHtml(data) {
    if (typeof data === 'string' && data.trim()) {
        const t = data.trim();
        try { data = JSON.parse(t); } catch (e) {
            try { data = JSON.parse(t.replace(/&quot;/g, '"')); } catch (e2) { data = null; }
        }
    }
    if (!data || typeof data !== 'object') return '';
    let html = '';
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const tabel = (Array.isArray(data.jawaban) && data.jawaban.length) ? data.jawaban
                : (Array.isArray(data.tabel) && data.tabel.length) ? data.tabel : null;

    if (tabel && Array.isArray(tabel[0])) {
        const header = tabel[0];
        const rightTexts = [];
        const rightKeys = [];
        for (let i = 1; i < header.length; i++) {
            if (header[i] === null || header[i] === undefined || header[i] === '') continue;
            rightKeys.push(alphabet[rightTexts.length]);
            rightTexts.push(header[i]);
        }
        html += '<div class="table-responsive"><table class="table table-bordered table-sm align-middle mb-0"><thead><tr class="table-light">';
        html += '<th class="text-start" style="min-width:220px;word-break:break-word;">' + pkT(header[0] !== undefined && header[0] !== null ? header[0] : 'Pernyataan') + '</th>';
        rightKeys.forEach(function (k, idx) {
            html += '<th class="text-center" style="min-width:120px;word-break:break-word;">' + k + '. ' + pkT(rightTexts[idx]) + '</th>';
        });
        html += '</tr></thead><tbody>';
        for (let r = 1; r < tabel.length; r++) {
            const row = Array.isArray(tabel[r]) ? tabel[r] : [];
            html += '<tr><td class="fw-semibold" style="word-break:break-word;">' + pkT(row[0] !== undefined ? row[0] : '') + '</td>';
            rightKeys.forEach(function (k, idx) {
                const cell = row[idx + 1];
                const val = (cell === null || cell === undefined) ? '' : String(cell).trim().toLowerCase();
                const correct = (val === '1' || val === 'true' || val === '✓');
                html += '<td class="text-center"><span class="badge ' + (correct ? 'bg-success' : 'bg-light text-muted border') + '"><i class="bi ' + (correct ? 'bi-check-lg' : 'bi-x-lg') + '"></i></span></td>';
            });
            html += '</tr>';
        }
        html += '</tbody></table></div>';
        if (rightTexts.length) {
            html += '<div class="small text-muted mt-2 d-flex flex-wrap gap-2">' + rightKeys.map(function (k, i) {
                return '<span><strong>' + k + '</strong>. ' + pkT(rightTexts[i]) + '</span>';
            }).join('') + '</div>';
        }
        return html;
    }

    if (Array.isArray(data.kiri) && Array.isArray(data.kanan)) {
        html += '<div class="row g-2">';
        html += '<div class="col-md-6"><div class="small fw-semibold text-muted mb-1">Pernyataan</div>';
        data.kiri.forEach(function (t, i) {
            html += '<div class="border rounded-2 px-2 py-1 mb-1 bg-white">' + (i + 1) + '. ' + pkT(t) + '</div>';
        });
        html += '</div><div class="col-md-6"><div class="small fw-semibold text-muted mb-1">Jawaban</div>';
        data.kanan.forEach(function (t, i) {
            html += '<div class="border rounded-2 px-2 py-1 mb-1 bg-white">' + alphabet[i] + '. ' + pkT(t) + '</div>';
        });
        html += '</div></div>';
        return html;
    }

    if (Array.isArray(data.baris)) {
        html += '<div class="d-flex flex-column gap-1"><div class="small fw-semibold text-muted">Opsi Jawaban:</div>';
        data.baris.forEach(function (t, i) {
            html += '<div class="border rounded-2 px-2 py-1 bg-white">' + alphabet[i] + '. ' + pkT(t) + '</div>';
        });
        html += '</div>';
        return html;
    }

    return '';
}

function renderBankSoalView(soal, opts) {
    if (!soal) return '';
    opts = opts || {};
    const showAnswer = opts.showAnswer !== false;
    const jenisLabels = ['', 'Pilihan Ganda', 'Pilihan Ganda Kompleks', 'Menjodohkan', 'Isian Singkat', 'Uraian / Essay', 'Urutan', 'Isian Rumpang', 'True / False'];
    const jenis = parseInt(soal.jenis || 1, 10) || 1;
    const jenisLabel = jenisLabels[jenis] || 'Soal';
    const teksSoal = String(soal.soal || '').trim();
    const notTampil = parseInt(soal.tampilkan === undefined ? 1 : soal.tampilkan, 10) !== 1;

    let html = '<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">'
        + '<span class="badge bg-dark-subtle text-dark px-3 rounded-pill fw-semibold">Nomor ' + (soal.nomor_soal || '?') + '</span>'
        + '<span class="badge px-3 rounded-pill border fw-semibold" style="background:#ffedd5;color:#c2410c;border-color:#fed7aa !important;">' + pkT(jenisLabel) + '</span>'
        + (notTampil ? '<span class="badge bg-danger-subtle text-danger border-0"><i class="bi bi-eye-slash me-1"></i>Tidak Tampil</span>' : '')
        + '</div>'
        + '<div class="soal-content text-dark mb-3" style="line-height:1.6;font-size:0.95rem;">'
        + (teksSoal ? teksSoal : '<span class="text-muted fst-italic">(Teks soal kosong)</span>')
        + '</div>';

    if (jenis === 3) {
        /* Menjodohkan */
        const jodoh = renderJodohViewHtml(unpackJodohData(soal));
        if (jodoh) html += '<div class="p-3 rounded-3 border bg-white">' + jodoh + '</div>';
        else if (showAnswer && soal.jawaban) html += kunciBox(formatJawabanText(soal.jawaban));
        else html += '<div class="text-muted fst-italic small p-2">Belum ada data pasangan menjodohkan disetorkan.</div>';
    } else if (jenis === 6) {
        /* Urutan — tampilkan item sesuai urutan benar */
        const opsi = normalizePreviewOpsi(soal);
        const items = Object.keys(opsi).map(k => opsi[k]);
        let order = [];
        if (Array.isArray(soal.jawaban)) order = soal.jawaban;
        else if (typeof soal.jawaban === 'string') {
            try { const p = JSON.parse(soal.jawaban); if (Array.isArray(p)) order = p; } catch (e) {}
        }
        const hasOrder = order.length === items.length && items.length > 0;
        const shown = hasOrder ? order.map(idx => (items[idx] !== undefined ? items[idx] : items[order.indexOf(idx)])) : items;
        if (shown.length) {
            html += '<div class="d-flex flex-column gap-2">' + shown.map(function (it, i) {
                return '<div class="p-2 rounded-3 border bg-white d-flex align-items-start gap-3"><div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;font-size:0.75rem;font-weight:bold;color:#fff;">' + (i + 1) + '</div><div class="flex-grow-1 small" style="line-height:1.5;">' + pkT(it) + '</div></div>';
            }).join('') + '</div>';
        } else if (showAnswer && soal.jawaban) html += kunciBox(formatJawabanText(soal.jawaban));
    } else if (jenis === 1 || jenis === 2) {
        /* PG & PG Kompleks */
        const opsi = normalizePreviewOpsi(soal);
        const keys = Object.keys(opsi);
        if (keys.length) {
            html += '<div class="d-flex flex-column gap-2">';
            keys.forEach(function (key) {
                const correct = showAnswer && isBankOptionCorrect(key, soal.jawaban);
                html += '<div class="p-3 rounded-3 border bg-white d-flex align-items-start gap-3 text-dark" style="border-color:' + (correct ? '#22c55e' : '#e2e8f0') + ' !important' + (correct ? ';background:#f0fdf4 !important' : '') + '">'
                    + '<div class="rounded-circle border d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;border-color:' + (correct ? '#22c55e' : '#cbd5e1') + ' !important;background:' + (correct ? '#22c55e' : '#fff') + ';color:' + (correct ? '#fff' : '#64748b') + ';font-size:0.75rem;font-weight:bold;">'
                    + (correct ? '<i class="bi bi-check-lg"></i>' : String(key).toUpperCase())
                    + '</div>'
                    + '<div class="flex-grow-1" style="font-size:0.9rem;line-height:1.5;">' + (pkT(opsi[key]) || '-') + '</div>'
                    + '</div>';
            });
            html += '</div>';
            if (showAnswer && jenis === 2 && soal.jawaban) {
                const kunciTxt = formatJawabanText(soal.jawaban);
                if (kunciTxt) html += '<div class="small text-success mt-2"><i class="bi bi-check2-square me-1"></i><strong>Kunci:</strong> ' + pkT(kunciTxt) + '</div>';
            }
        } else {
            html += '<div class="text-muted fst-italic small p-2">Belum ada pilihan jawaban disetorkan.</div>';
        }
    } else if (showAnswer && soal.jawaban) {
        /* Isian Singkat / Uraian / Rumpang / TF */
        html += kunciBox(formatJawabanText(soal.jawaban));
    }
    if (showAnswer && String(soal.deskripsi || '').trim()) {
        html += '<div class="p-3 rounded-3 mt-2" style="background:#fffbeb;border:1px solid #fde68a;"><strong class="small text-warning-emphasis d-block mb-1">Deskripsi / Pembahasan:</strong><div class="text-dark small" style="line-height:1.5;">' + soal.deskripsi + '</div></div>';
    }
    return html;
}

window.isBankOptionCorrect = isBankOptionCorrect;
window.renderBankSoalView = renderBankSoalView;
window.renderJodohViewHtml = renderJodohViewHtml;
window.normalizePreviewOpsi = normalizePreviewOpsi;
window.unpackJodohData = unpackJodohData;
window.formatJawabanText = formatJawabanText;
window.buildOpsiKeyList = buildOpsiKeyList;

/**
 * Modal "Lihat Soal Semua Paket" — bandingkan soal antar paket dari kartu yang sama.
 */
function openLihatPaketModal(pakets) {
    pakets = (pakets && pakets.length) ? pakets
        : ((window.KARTU_SOAL_DETAIL && window.KARTU_SOAL_DETAIL.paketBanks) || []);
    if (!pakets.length) {
        Swal.fire('Info', 'Belum ada paket soal yang dibuat dari kartu ini.', 'info');
        return;
    }
    const $tabs = $('#lapaketTabs');
    const $body = $('#lapaketBody');
    if ($tabs.length) {
        let html = '<span class="text-muted small fw-semibold me-1"><i class="bi bi-box-seam me-1"></i>Paket:</span>';
        pakets.forEach(function (pk, i) {
            const bankId  = parseInt(pk.id_bank || pk.bank_id || 0, 10);
            const letter  = pk.letter || String.fromCharCode(65 + i);
            const total   = (pk.total_soal === null || pk.total_soal === undefined) ? '' : (' <span class="badge bg-white bg-opacity-25 rounded-pill ms-1">' + pk.total_soal + '</span>');
            html += '<button type="button" class="btn btn-sm lapaket-tab ' + (i === 0 ? 'btn-primary' : 'btn-outline-secondary') + '" data-bank-id="' + bankId + '" data-letter="' + String(letter).replace(/"/g, '') + '" title="' + String(pk.bank_kode || pk.kode || '').replace(/"/g, '') + '">'
                + '<i class="bi bi-box-seam me-1"></i>Paket ' + String(letter).replace(/"/g, '') + total
                + '</button>';
        });
        $tabs.html(html);
    }
    $body.html('<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Memuat soal paket...</div>');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalLihatPaketSoal')).show();
    const first = pakets[0];
    if (first) loadPaketSoalBank(parseInt(first.id_bank || first.bank_id || 0, 10), first.letter || '?');
}

function loadPaketSoalBank(bankId, letter) {
    const $body = $('#lapaketBody');
    $body.html('<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2" role="status"></span>Memuat soal Paket ' + String(letter || '?').replace(/"/g, '') + '...</div>');
    $.get(base_url + 'cbtkartusoal/api/bank/' + bankId + '/soal-full')
        .done(function (res) {
            if (!res || !res.status) {
                $body.html('<div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>' + (res?.message ? String(res.message) : 'Gagal memuat soal paket.') + '</div>');
                return;
            }
            const soals    = (res.data && res.data.soals) ? res.data.soals : [];
            const bankInfo = (res.data && res.data.bank) ? res.data.bank : {};
            if (!soals.length) {
                $body.html('<div class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>Belum ada soal di paket ini.</div>');
                return;
            }
            let allHtml = '<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">'
                + '<div><span class="badge bg-primary-subtle text-primary border"><i class="bi bi-box-seam me-1"></i>' + String(bankInfo.bank_kode || '').replace(/"/g, '') + '</span>'
                + '<span class="text-muted small ms-2">' + soals.length + ' soal</span></div>'
                + '<a class="btn btn-sm btn-outline-primary" target="_blank" href="' + base_url + 'cbtbanksoal/detail/' + bankId + '"><i class="bi bi-box-arrow-up-right me-1"></i>Buka di Bank Soal</a>'
                + '</div>';
            soals.forEach(function (s) {
                allHtml += '<div class="card border-0 shadow-sm mb-3"><div class="card-body p-3">'
                    + renderBankSoalView(s, { showAnswer: true })
                    + '</div></div>';
            });
            $body.html(allHtml);
        })
        .fail(function () {
            $body.html('<div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle me-1"></i>Gagal menghubungi server saat memuat soal paket.</div>');
        });
}

$(document).on('click', '.lapaket-tab', function () {
    const $tabs = $('#lapaketTabs');
    $tabs.find('.lapaket-tab').removeClass('btn-primary').addClass('btn-outline-secondary');
    $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
    loadPaketSoalBank(parseInt($(this).data('bankId') || '0', 10), $(this).data('letter') || '?');
});

window.openLihatPaketModal = openLihatPaketModal;
window.loadPaketSoalBank = loadPaketSoalBank;

window.showContohSoalModal = showContohSoalModal;
