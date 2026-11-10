/**
 * detail-manager.js
 * Kartu Soal Detail — Refactored AJAX Controller
 *
 * Features:
 *  - Auto-populate form fields from Tujuan Pembelajaran (data-* attributes + live API sync)
 *  - Real-time filter by TP & jenis soal via AJAX
 *  - Client-side & server-side duplicate detection (nomor)
 *  - Loading states, error handling, feedback visual
 *  - Auto-save for edit mode (dirty-state guard on close)
 *  - Soft-undo delete with 5-second countdown toast
 *  - AI Generate integration
 *  - Workflow actions (submit, approve, revisi)
 *  - Keyboard shortcut: Alt+N → Tambah Butir
 */

/* ============================================================
   BOOTSTRAP — guard against missing config
   ============================================================ */
$(function () {
    const cfg = window.KARTU_SOAL_DETAIL || {};
    if (!cfg.uuid) return;

    /* ============================================================
       STATE
       ============================================================ */
    const state = {
        page: 1,
        perPage: parseInt(localStorage.getItem('kartu_detail_per_page') || '25', 10),
        total: 0,
        totalPages: 1,
        search: '',
        items: [],          // current page items
        orderIds: [],       // all item IDs in order (for reorder)
        allNomors: [],      // ALL nomor values across ALL pages (for duplicate check)
        autoSaveTimer: null,
        suspendAuto: false,
        isSaving: false,
        isDirty: false,     // unsaved changes in modal
        aiPreviewItems: [],
        tujuanData: [],     // cache from API: full TP objects
        tujuanFetchedAt: 0, // timestamp of last successful TP fetch
        summaryJenis: cfg.summaryJenis || {},   // summary per jenis soal {id: count}
        filter: {
            tujuanId: 0,
            jenisSoal: 0,
        },
    };

    /* ============================================================
       DOM REFS
       ============================================================ */
    const $listLoading    = $('#listLoading');
    const $itemsTableWrap = $('#itemsTableWrap');
    const $itemsBody      = $('#itemsBody');
    const $emptyState     = $('#emptyState');
    const $paginationInfo = $('#paginationInfo');
    const $paginationBtns = $('#paginationButtons');
    const $listHint       = $('#listHint');
    const $filterBadge    = $('#filterActiveBadge');
    const $undoToast      = $('#undoDeleteToast');

    /* ============================================================
       UTILITIES
       ============================================================ */
    function esc(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function stripHtml(html) {
        if (!html) return '';
        const doc = new DOMParser().parseFromString(html, 'text/html');
        return doc.body.textContent || '';
    }

    function truncate(str, len) {
        str = String(str || '');
        return str.length > len ? str.substring(0, len) + '…' : str;
    }

    function buildQuery(params) {
        return Object.entries(params)
            .filter(([, v]) => v !== '' && v !== 0 && v !== null && v !== undefined)
            .map(([k, v]) => encodeURIComponent(k) + '=' + encodeURIComponent(v))
            .join('&');
    }

    /* ============================================================
       LOADING OVERLAY UTILITIES
       ============================================================ */
    function showGenerateLoader() {
        $('#generateLoadingOverlay').removeClass('d-none').addClass('d-flex');
    }

    function hideGenerateLoader() {
        $('#generateLoadingOverlay').removeClass('d-flex').addClass('d-none');
    }

    /* ============================================================
       LOADING & ERROR FEEDBACK
       ============================================================ */
    function showListLoading(show) {
        $listLoading.toggleClass('d-none', !show);
        if (show) {
            $itemsTableWrap.addClass('d-none');
            $emptyState.addClass('d-none');
        }
    }

    function showListError(msg) {
        showListLoading(false);
        $itemsBody.html(
            '<tr><td colspan="10" class="text-center text-danger p-4">' +
            '<i class="bi bi-exclamation-triangle me-2"></i>' + esc(msg) +
            '</td></tr>'
        );
        $itemsTableWrap.removeClass('d-none');
    }

    /* ============================================================
       FILTER BAR — TP & Jenis filter & Search
       ============================================================ */
    function applyFilter() {
        state.filter.tujuanId  = parseInt($('#filterTujuanId').val()  || '0', 10);
        state.filter.jenisSoal = parseInt($('#filterJenisSoal').val() || '0', 10);
        state.search           = ($('#filterSearch').val() || '').trim();
        state.page = 1;
        loadItems(1);
        updateFilterBadge();
    }

    function resetFilter() {
        $('#filterTujuanId').val('');
        $('#filterJenisSoal').val('');
        $('#filterSearch').val('');
        $('#btnClearSearch').addClass('d-none');
        state.filter.tujuanId  = 0;
        state.filter.jenisSoal = 0;
        state.search           = '';
        state.page = 1;
        loadItems(1);
        updateFilterBadge();
    }

    function updateFilterBadge() {
        const active = state.filter.tujuanId > 0 || state.filter.jenisSoal > 0 || state.search !== '';
        if ($filterBadge.length) {
            $filterBadge.toggleClass('d-none', !active);
        }
        if (active) {
            $listHint.html(
                '<span class="text-warning-emphasis fw-semibold"><i class="bi bi-funnel-fill me-1"></i>Filter aktif — menampilkan subset butir.</span>'
            );
        }
    }

    /* ============================================================
       DUPLICATE DETECTION
       ============================================================ */

    /** Client-side: check if nomor already exists in state.allNomors */
    function isDuplicateNomor(nomor) {
        const n = parseInt(nomor, 10);
        if (!n || isNaN(n)) return false;
        const editingId = parseInt($('#itemId').val() || '0', 10);
        return state.allNomors.some(function (entry) {
            return entry.nomor === n && entry.id_item !== editingId;
        });
    }

    /** Server-side: verify via API (returns Promise resolving to boolean) */
    function checkDuplicateNomorServer(nomor) {
        const n = parseInt(nomor, 10);
        if (!n || isNaN(n)) return Promise.resolve(false);
        return $.get(base_url + 'cbtkartusoal/api/items/' + cfg.uuid + '?check_duplicates=' + n)
            .then(function (res) {
                const dupes = res?.data?.duplicates || [];
                // If the only duplicate is the item being edited, ignore it
                const editingId = parseInt($('#itemId').val() || '0', 10);
                if (!editingId) return dupes.includes(n);
                // Server returns nomor list — cross-check with editing item
                return dupes.length > 0;
            })
            .catch(function () { return false; });
    }

    /* ============================================================
       TUJUAN DATA — fetch from API, cache, populate dropdowns
       ============================================================ */
    function fetchTujuan(force = false) {
        if (!cfg.uuid) return;
        // Guard: skip fetch if cache is fresh (< 5 minutes) and not forced
        const now = Date.now();
        const cacheAgeSec = (now - state.tujuanFetchedAt) / 1000;
        if (!force && state.tujuanData.length > 0 && cacheAgeSec < 300) {
            // Cache still valid — just re-render with existing data
            populateTujuanDropdown();
            populateFilterDropdown();
            return;
        }

        const $btn = $('#btnSyncTp');
        const origHtml = $btn.html();
        if (force) {
            $btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        }

        $.get(base_url + 'cbtkartusoal/api/tujuan/' + cfg.uuid)
            .done(function (res) {
                if (res?.status && Array.isArray(res?.data)) {
                    state.tujuanData = res.data;
                    state.tujuanFetchedAt = Date.now();
                    populateTujuanDropdown();
                    populateFilterDropdown();
                    if (force) {
                        Swal.fire({ title: 'Tersinkron', text: 'Data TP diperbarui.', icon: 'success', timer: 1200, showConfirmButton: false });
                    }
                }
            })
            .fail(function () {
                if (force) Swal.fire('Gagal', 'Gagal sinkronisasi data TP.', 'error');
            })
            .always(function () {
                if (force) { $btn.html(origHtml).prop('disabled', false); }
            });
    }

    function populateTujuanDropdown() {
        const $sel = $('#itemTujuan');
        const prev = $sel.val();
        $sel.empty().append('<option value="">Pilih TP...</option>');
        state.tujuanData.forEach(function (tp) {
            $sel.append(
                $('<option>')
                    .val(tp.id_tujuan)
                    .text(esc(tp.kode) + ' - ' + truncate(tp.tujuan, 60))
                    .attr({
                        'data-fase'            : tp.fase || '',
                        'data-kode-cp'         : tp.kode_cp || '',
                        'data-elemen-cp'       : tp.elemen_cp || '',
                        'data-materi-pokok'    : tp.materi_pokok || '',
                        'data-tujuan'          : tp.tujuan || '',
                        'data-tp-sikap'        : tp.tp_sikap || '',
                        'data-tp-keterampilan' : tp.tp_keterampilan || '',
                        'data-kktp'            : tp.kriteria_ketercapaian || '',
                        'data-level'           : tp.level_kognitif || '',
                    })
            );
        });
        if (prev) {
            $sel.val(prev);
            // Re-trigger auto-populate after rebuild so fields stay filled
            const opt = $sel[0]?.options[$sel[0]?.selectedIndex];
            if (opt && opt.value) { autoPopulateFromTp(opt, false); }
        }
        renderTpPreview();
    }

    function populateFilterDropdown() {
        const $sel = $('#filterTujuanId');
        if (!$sel.length) return;
        const prev = $sel.val();
        $sel.empty().append('<option value="">Semua TP</option>');
        state.tujuanData.forEach(function (tp) {
            $sel.append(
                $('<option>').val(tp.id_tujuan).text(esc(tp.kode) + ' — ' + truncate(tp.tujuan, 45))
            );
        });
        if (prev) { $sel.val(prev); }
    }

    /* ============================================================
       AUTO-POPULATE FIELDS FROM TP SELECTION
       ============================================================ */

    /**
     * Populate form fields from the selected TP.
     * Priority: state.tujuanData cache (from API) > DOM data-* attributes.
     * Fallbacks: CP → elemen_cp if kode_cp empty; Materi → tujuan text if materi_pokok empty.
     * Only fills EMPTY fields unless forceOverwrite = true.
     */
    function autoPopulateFromTp(opt, forceOverwrite = false) {
        if (!opt || !opt.value) return;

        const idTujuan = parseInt(opt.value, 10);
        const d = opt.dataset || {};

        // Prefer live API cache; fall back to DOM data-* attributes
        const tp = state.tujuanData.find(t => t.id_tujuan === idTujuan);

        const fase           = tp?.fase            || d.fase            || '';
        const kodeCp         = tp?.kode_cp         || d.kodeCp          || '';
        const elemenCp       = tp?.elemen_cp       || d.elemenCp        || '';
        const materiPokok    = tp?.materi_pokok    || d.materiPokok     || '';
        const tujuanTeks     = tp?.tujuan          || d.tujuan          || '';
        const levelKognitif  = tp?.level_kognitif  || d.level           || '';

        // Fase — always fill (read-only)
        $('#itemFase').val(fase);

        // CP field: use kode_cp; fallback to elemen_cp; fallback to kode_cp from option
        if (forceOverwrite || !$('#itemCp').val()) {
            const cpValue = kodeCp || elemenCp || '';
            $('#itemCp').val(cpValue);
        }

        // Materi: use materi_pokok; fallback to first 80 chars of tujuan
        if (forceOverwrite || !$('#itemMateri').val()) {
            const materiValue = materiPokok || truncate(stripHtml(tujuanTeks), 80);
            $('#itemMateri').val(materiValue);
        }

        // Level kognitif
        if (forceOverwrite || !$('#itemLevel').val()) {
            const lvl = levelKognitif.toUpperCase();
            if (['C1','C2','C3','C4','C5','C6'].includes(lvl)) {
                $('#itemLevel').val(lvl);
            }
        }

        // Indikator Soal — auto-generate only for new items with empty field
        const isNew = !parseInt($('#itemId').val() || '0', 10);
        if ((forceOverwrite || (isNew && !$('#itemIndikator').val())) && tujuanTeks) {
            const kko         = getKkoFromLevel(levelKognitif);
            const tujuanShort = truncate(stripHtml(tujuanTeks), 100);
            $('#itemIndikator').val('Peserta didik mampu ' + kko + ' ' + tujuanShort);
        }

        renderTpPreview();
        scheduleAutoSave();
    }

    function getKkoFromLevel(level) {
        const map = {
            'C1': 'menyebutkan',
            'C2': 'menjelaskan',
            'C3': 'menerapkan',
            'C4': 'menganalisis',
            'C5': 'mengevaluasi',
            'C6': 'merancang',
        };
        return map[(level || '').toUpperCase()] || 'memahami';
    }

    /* ============================================================
       TP DETAIL PREVIEW (in modal)
       ============================================================ */
    function renderTpPreview() {
        const idTujuan = parseInt($('#itemTujuan').val() || '0', 10);
        const $prev = $('#tpDetailPreview');

        if (!idTujuan) { $prev.addClass('d-none').html(''); return; }

        // Try live cache first, fallback to DOM option data
        const tp = state.tujuanData.find(t => t.id_tujuan === idTujuan);
        const opt = $('#itemTujuan option:selected')[0];

        const kodeCp       = tp?.kode_cp              || opt?.dataset?.kodeCp       || '';
        const elemenCp     = tp?.elemen_cp             || opt?.dataset?.elemenCp     || '';
        const materiPokok  = tp?.materi_pokok          || opt?.dataset?.materiPokok  || '';
        const tujuanTeks   = tp?.tujuan                || opt?.dataset?.tujuan       || '';
        const tpSikap      = tp?.tp_sikap              || opt?.dataset?.tpSikap      || '';
        const tpKetnplilan = tp?.tp_keterampilan       || opt?.dataset?.tpKeterampilan || '';
        const kktp         = tp?.kriteria_ketercapaian || opt?.dataset?.kktp         || '';

        let html = '<div class="fw-bold mb-1" style="color:#5b21b6;border-bottom:1px solid #ddd6fe;padding-bottom:3px;">Detail TP:</div>';
        if (kodeCp) {
            html += `<div class="mb-1"><span class="badge bg-primary-subtle text-primary border rounded-pill me-1">${esc(kodeCp)}</span>`;
            if (elemenCp) html += `<span class="badge bg-light text-dark border rounded-pill">${esc(elemenCp)}</span>`;
            html += '</div>';
        }
        if (materiPokok) html += `<div class="mb-1"><strong>Materi:</strong> ${esc(materiPokok)}</div>`;
        if (tujuanTeks)  html += `<div class="mb-1 text-dark"><strong>P:</strong> ${esc(tujuanTeks)}</div>`;
        if (tpSikap)     html += `<div class="mb-1 text-primary"><strong>S:</strong> ${esc(tpSikap)}</div>`;
        if (tpKetnplilan) html += `<div class="mb-1 text-warning-emphasis"><strong>K:</strong> ${esc(tpKetnplilan)}</div>`;
        if (kktp)        html += `<div class="mt-2 p-1 rounded" style="background:#fffbeb;border:1px solid #fde68a;"><strong>KKTP:</strong> ${esc(kktp)}</div>`;

        $prev.html(html).removeClass('d-none');
    }

    /* ============================================================
       MODAL — open / close
       ============================================================ */
    function openModal(item) {
        if (!cfg.canManage) return;
        state.suspendAuto = true;
        state.isDirty = false;
        clearValidation();

        const isEdit = !!item;
        $('#itemModalTitle').text(isEdit ? 'Edit Butir Soal' : 'Tambah Butir Soal');
        $('#itemId').val(item?.id_item || '');
        $('#itemJenis').val(String(item?.jenis_soal || 1));
        $('#itemLevel').val(item?.level_kognitif || '');
        $('#itemWaktu').val(item?.estimasi_waktu || 5);
        $('#itemMateri').val(item?.materi || '');
        $('#itemIndikator').val(item?.indikator_soal || '');
        $('#itemCp').val(item?.cp || '');
        $('#itemFase').val(item?.fase || '');

        // Auto-set next available nomor for new items
        if (!isEdit) {
            const nextNomor = state.allNomors.length > 0
                ? Math.max(...state.allNomors.map(e => e.nomor)) + 1
                : 1;
            $('#itemNomor').val(nextNomor);
            $('#nextNomorHint').text('Nomor berikutnya: ' + nextNomor);
        } else {
            $('#itemNomor').val(item?.nomor || '');
            $('#nextNomorHint').text('');
        }

        // Set TP dropdown
        const tpId = item?.id_tujuan || '';
        $('#itemTujuan').val(tpId);

        // Fetch TP only if cache is stale (guarded inside fetchTujuan)
        fetchTujuan(false);

        if (!isEdit) {
            const opt = $('#itemTujuan option:selected')[0];
            if (opt && opt.value) { autoPopulateFromTp(opt, false); }
        } else {
            renderTpPreview();
        }

        validateForm();
        setAutoSaveState(isEdit ? 'Auto-save aktif' : 'Manual Save', false);

        (bootstrap.Modal.getInstance(document.getElementById('itemModal')) || new bootstrap.Modal(document.getElementById('itemModal'))).show();
        setTimeout(() => { state.suspendAuto = false; }, 500);
    }

    function closeModal() {
        if (state.isDirty) {
            const editingId = parseInt($('#itemId').val() || '0', 10);
            if (!editingId) {
                // New item — just close
                state.isDirty = false;
            } else {
                // Show confirmation only for edits with unsaved changes
                Swal.fire({
                    title: 'Ada perubahan yang belum disimpan',
                    text: 'Tutup modal dan buang perubahan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, tutup',
                    cancelButtonText: 'Kembali',
                }).then(r => {
                    if (r.isConfirmed) {
                        state.isDirty = false;
                        _doCloseModal();
                    }
                });
                return;
            }
        }
        _doCloseModal();
    }

    function _doCloseModal() {
        const el = document.getElementById('itemModal');
        const m  = bootstrap.Modal.getInstance(el);
        if (m) { document.activeElement?.blur(); m.hide(); }
    }

    /* ============================================================
       FORM VALIDATION
       ============================================================ */
    function clearValidation() {
        $('#validationGlobal').text('');
        $('#itemNomor').removeClass('is-invalid');
    }

    function validateForm() {
        clearValidation();
        let valid = true;

        if (!$('#itemTujuan').val()) {
            $('#validationGlobal').text('Tujuan Pembelajaran wajib dipilih.');
            valid = false;
        }

        const nomor = parseInt($('#itemNomor').val() || '0', 10);
        if (nomor > 0 && isDuplicateNomor(nomor)) {
            $('#validationGlobal').text('No soal ' + nomor + ' sudah digunakan oleh butir lain.');
            $('#itemNomor').addClass('is-invalid');
            valid = false;
        }

        $('#btnSaveItem').prop('disabled', !valid);
        return valid;
    }

    /* ============================================================
       COLLECT PAYLOAD
       ============================================================ */
    function collectPayload() {
        return {
            [CSRF_NAME]       : CSRF_HASH,
            uuid              : cfg.uuid,
            id_item           : parseInt($('#itemId').val() || '0', 10),
            nomor             : parseInt($('#itemNomor').val() || '0', 10),
            jenis_soal        : parseInt($('#itemJenis').val() || '1', 10),
            id_tujuan         : $('#itemTujuan').val(),
            cp                : $('#itemCp').val() || '',
            materi            : $('#itemMateri').val() || '',
            indikator_soal    : $('#itemIndikator').val() || '',
            level_kognitif    : $('#itemLevel').val() || '',
            estimasi_waktu    : parseInt($('#itemWaktu').val() || '5', 10),
            catatan           : '',
            butir_soal        : '',
            opsi_json         : '{}',
            jawaban_benar_json: '{}',
            pembahasan        : '',
        };
    }

    /* ============================================================
       AUTO-SAVE
       ============================================================ */
    function setAutoSaveState(text, loading) {
        $('#autoSaveState').text(text || 'Manual Save');
        $('#autoSaveSpinner').toggleClass('d-none', !loading);
    }

    function scheduleAutoSave() {
        const idItem = parseInt($('#itemId').val() || '0', 10);
        if (!idItem || state.suspendAuto || state.isSaving) return;
        state.isDirty = true;
        setAutoSaveState('Perubahan terdeteksi…', false);
        clearTimeout(state.autoSaveTimer);
        state.autoSaveTimer = setTimeout(function () {
            if (!validateForm()) return;
            saveItem(true);
        }, 900);
    }

    /* ============================================================
       SAVE ITEM (add / update)
       ============================================================ */
    function saveItem(isAutoSave) {
        if (state.isSaving) return;
        if (!cfg.isEditable) {
            if (!isAutoSave) Swal.fire('Terkunci', 'Kembalikan ke draft/revisi untuk mengedit.', 'warning');
            return;
        }
        if (!validateForm()) return;

        const payload  = collectPayload();
        const isUpdate = payload.id_item > 0;
        const url      = base_url + (isUpdate ? 'cbtkartusoal/updateItem' : 'cbtkartusoal/addItem');

        state.isSaving = true;
        if (isAutoSave) {
            setAutoSaveState('Auto-save…', true);
        } else {
            $('#itemSaveSpinner').removeClass('d-none');
            $('#itemSaveText').text('Menyimpan…');
            $('#btnSaveItem').prop('disabled', true);
        }

        $.post(url, payload)
            .done(function (res) {
                if (!res?.status) {
                    if (!isAutoSave) Swal.fire('Gagal', res?.message || 'Gagal menyimpan butir.', 'error');
                    setAutoSaveState('Gagal simpan', false);
                    return;
                }
                if (isAutoSave) {
                    setAutoSaveState('Tersimpan otomatis ✓', false);
                    state.isDirty = false;
                    loadItems(state.page);
                    return;
                }
                state.isDirty = false;
                Swal.fire({ title: 'Berhasil', text: res?.message || 'Butir soal tersimpan.', icon: 'success', timer: 900, showConfirmButton: false });
                closeModal();
                loadItems(state.page);
            })
            .fail(function (xhr) {
                const msg = xhr?.responseJSON?.message || 'Gangguan koneksi.';
                if (!isAutoSave) Swal.fire('Gagal', msg, 'error');
                setAutoSaveState('Gagal simpan', false);
            })
            .always(function () {
                state.isSaving = false;
                if (!isAutoSave) {
                    $('#itemSaveSpinner').addClass('d-none');
                    $('#itemSaveText').text('Simpan Butir');
                    $('#btnSaveItem').prop('disabled', false);
                }
            });
    }

    /* ============================================================
       LOAD ITEMS — AJAX with filter & search support
       ============================================================ */
    function loadItems(page) {
        state.page = Math.max(1, page || 1);
        showListLoading(true);

        const params = { page: state.page, per_page: state.perPage };
        if (state.filter.tujuanId  > 0) params.tujuan_id  = state.filter.tujuanId;
        if (state.filter.jenisSoal > 0) params.jenis_soal = state.filter.jenisSoal;
        if (state.search !== '')       params.q          = state.search;

        const query = buildQuery(params);
        return $.get(base_url + 'cbtkartusoal/api/items/' + cfg.uuid + '?' + query)
            .done(function (res) {
                if (!res?.status) {
                    showListError(res?.message || 'Gagal memuat data.');
                    return;
                }
                const payload = res?.data || {};
                state.items      = payload.items || [];
                state.total      = parseInt(payload.meta?.total || 0, 10);
                state.totalPages = Math.max(1, parseInt(payload.meta?.total_pages || 1, 10));
                state.summaryJenis = payload.meta?.summary_jenis || {};
                state.meta = payload.meta || null;

                renderRows();
                renderPagination();
                updateSummary();
                refreshOrderIds();
                // Refresh cross-page nomor cache in background (non-blocking)
                refreshAllNomors();
            })
            .fail(function (xhr) {
                const msg = xhr?.status === 401 ? 'Sesi habis. Silakan login ulang.'
                    : xhr?.status === 403 ? 'Akses ditolak.'
                    : 'Gagal memuat butir soal (' + (xhr?.status || '?') + ').';
                showListError(msg);
            })
            .always(function () {
                showListLoading(false);
            });
    }

    /* ============================================================
       RENDER TABLE ROWS
       ============================================================ */
    function renderRows() {
        $itemsBody.empty();
        $('#checkAll').prop('checked', false);
        $('#btnBulkDelete').addClass('d-none').removeClass('fade-in');
        $('#listHint').removeClass('d-none');

        if (!state.items.length) {
            $itemsTableWrap.addClass('d-none');
            $emptyState.removeClass('d-none');
            const isFiltered = state.filter.tujuanId > 0 || state.filter.jenisSoal > 0 || state.search !== '';
            $emptyState.find('p').text(
                isFiltered
                    ? 'Tidak ada butir yang sesuai dengan pencarian atau filter aktif.'
                    : 'Tambahkan butir pertama atau gunakan AI Generator untuk mulai menyusun kartu soal.'
            );
            return;
        }

        state.items.forEach(function (item) {
            const fase   = item.fase || '-';
            const tpKode = item.tujuan_kode || '';
            const waktu  = parseInt(item.estimasi_waktu || 0, 10);

            // Level badge color by cognitive level
            const lvlColors = { C1: 'success', C2: 'info', C3: 'primary', C4: 'warning', C5: 'danger', C6: 'dark' };
            const lvl      = (item.level_kognitif || '').toUpperCase();
            const lvlColor = lvlColors[lvl] || 'secondary';

            const cpTp = (item.cp ? '<span class="badge bg-light text-dark border rounded-pill me-1 mb-1 font-monospace">' + esc(item.cp) + '</span><br>' : '')
                       + '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill me-1">Fase ' + esc(fase) + '</span>'
                       + (tpKode ? ' <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill">' + esc(tpKode) + '</span>' : '')
                       + (item.tujuan ? '<div class="text-muted small mt-1" style="line-height:1.35;">' + esc(truncate(item.tujuan, 75)) + '</div>' : '');

            const waktuCell = waktu > 0
                ? '<span class="text-dark small fw-semibold">' + waktu + '<span class="text-muted fw-normal ms-0.5" style="font-size:0.7rem;">m</span></span>'
                : '<span class="text-muted" style="font-size:0.75rem;">—</span>';

            // Check if item is used in bank soal (has bank_id and soal_id)
            const isUsedInBank = item.bank_id && item.soal_id;
            const bankBadge = isUsedInBank
                ? '<span class="badge bg-purple bg-opacity-10 text-purple border px-2 py-0.5 rounded-pill small d-block mt-1" style="background-color:#f3e8ff;color:#7e22ce;border-color:#e9d5ff;font-size:0.65rem;" title="Butir soal sudah dibuat menjadi Bank Soal"><i class="bi bi-box-seam me-1"></i>Bank</span>'
                : '';

            const nomorFormatted = item.nomor ? strPad(item.nomor, 2) : '-';

            const row = $(
                '<tr data-id="' + item.id_item + '">'
                + '<td class="text-center ps-3">'
                + (cfg.canManage && cfg.isEditable ? '<input type="checkbox" class="form-check-input check-item" value="' + item.id_item + '">' : '')
                + '</td>'
                + '<td class="text-center fw-bold font-monospace text-muted">' + nomorFormatted + bankBadge + '</td>'
                + '<td><div class="small">' + cpTp + '</div></td>'
                + '<td class="small fw-semibold text-dark">' + esc(item.materi || '-') + '</td>'
                + '<td class="small text-secondary">' + esc(truncate(item.indikator_soal || '-', 85)) + '</td>'
                + '<td class="text-center"><span class="badge bg-' + lvlColor + ' bg-opacity-10 text-' + lvlColor + ' border border-' + lvlColor + '-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size:0.75rem;">' + esc(item.level_kognitif || '-') + '</span></td>'
                + '<td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-semibold small">' + esc(item.jenis_label || '-') + '</span></td>'
                + '<td class="text-center fw-medium font-monospace text-muted">' + (item.bank_soal_nomor || '-') + '</td>'
                + '<td class="text-center d-none d-lg-table-cell">' + waktuCell + '</td>'
                + '<td class="text-center pe-3 aksi-cell"></td>'
                + '</tr>'
            );

            if (cfg.canManage && cfg.isEditable) {
                row.find('.aksi-cell').html(
                    '<div class="btn-action-group justify-content-center">'
                    + '<button type="button" class="btn btn-action btn-action-info btnPreview" data-bs-toggle="tooltip" title="Preview Soal"><i class="bi bi-eye-fill"></i></button>'
                    + '<button type="button" class="btn btn-action btn-action-edit btnEdit" data-bs-toggle="tooltip" title="Edit Butir"><i class="bi bi-pencil-fill"></i></button>'
                    + '<div class="btn-group">'
                    + '<button type="button" class="btn btn-action btn-action-magic dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Pindah / Hapus"><i class="bi bi-three-dots"></i></button>'
                    + '<ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:150px;">'
                    + '<li><a class="dropdown-item small btnMoveUp" href="javascript:void(0)"><i class="bi bi-arrow-up me-2"></i>Naikkan</a></li>'
                    + '<li><a class="dropdown-item small btnMoveDown" href="javascript:void(0)"><i class="bi bi-arrow-down me-2"></i>Turunkan</a></li>'
                    + '<li><hr class="dropdown-divider"></li>'
                    + '<li><a class="dropdown-item small text-danger btnDelete" href="javascript:void(0)"><i class="bi bi-trash-fill me-2"></i>Hapus</a></li>'
                    + '</ul>'
                    + '</div>'
                    + '</div>'
                );
            } else {
                row.find('.aksi-cell').html(
                    '<div class="btn-action-group justify-content-center">'
                    + '<button type="button" class="btn btn-action btn-action-info btnPreview" data-bs-toggle="tooltip" title="Preview Soal & Hierarki Indikator"><i class="bi bi-eye-fill"></i></button>'
                    + '</div>'
                );
            }

            $itemsBody.append(row);
        });

        // Initialize Bootstrap tooltips for dynamically generated action buttons
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            $itemsBody.find('[data-bs-toggle="tooltip"]').each(function() {
                new bootstrap.Tooltip(this);
            });
        }

        $itemsTableWrap.removeClass('d-none');
        $emptyState.addClass('d-none');
    }

    function strPad(val, len) {
        let s = String(val);
        while (s.length < len) s = '0' + s;
        return s;
    }

    /* ============================================================
       SUMMARY & PAGINATION
       ============================================================ */
    function updateSummary() {
        const meta = state.meta || {};
        const isFiltered = state.filter.tujuanId > 0 || state.filter.jenisSoal > 0 || state.search !== '';

        // Header selalu akurat: pakai total global dari server saat tanpa filter,
        // pakai total hasil filter saat filter aktif.
        if (isFiltered) {
            $('#summaryItemCount').text(state.total);
        } else if (meta.total_all !== undefined) {
            $('#summaryItemCount').text(meta.total_all);
        } else {
            $('#summaryItemCount').text(state.total);
        }
        if (meta.total_estimasi_all !== undefined && !isFiltered) {
            $('#summaryWaktu').text(meta.total_estimasi_all);
        } else if (!isFiltered && state.totalPages === 1) {
            const waktu = state.items.reduce((s, it) => s + parseInt(it.estimasi_waktu || 0, 10), 0);
            $('#summaryWaktu').text(waktu);
        }
        if (meta.tujuan_unique_all !== undefined && !isFiltered) {
            $('#summaryTujuan').text(meta.tujuan_unique_all);
        } else if (!isFiltered && state.totalPages === 1) {
            const uniqueTp = new Set(state.items.map(it => it.id_tujuan).filter(Boolean));
            $('#summaryTujuan').text(uniqueTp.size || 0);
        }

        // Update Dynamic Jenis Soal Badges
        const $wrapper = $('#summaryJenisWrapper');
        if (state.summaryJenis && Object.keys(state.summaryJenis).length > 0) {
            let html = '<div class="vr mx-1 d-none d-md-block opacity-25 summary-vr"></div>';
            
            // Replicate PHP logic for labels & icons
            const rawLabels = cfg.labels || {};
            const icons = {
                1: 'bi-list-check',
                2: 'bi-ui-checks',
                3: 'bi-distribute-vertical',
                4: 'bi-pencil-square',
                5: 'bi-journal-richtext',
                6: 'bi-sort-numeric-down',
                7: 'bi-textarea-t',
                8: 'bi-check2-circle'
            };

            Object.entries(state.summaryJenis).forEach(([id, count]) => {
                let label = rawLabels[id] || 'Lainnya';
                const icon = icons[id] || 'bi-question-circle';
                
                // Simplify label like PHP: replace "Pilihan Ganda Kompleks" with "PG Kmplx" and "Pilihan Ganda" with "PG"
                const displayLabel = label.replace('Pilihan Ganda Kompleks', 'PG Kmplx').replace('Pilihan Ganda', 'PG');
                
                html += `
                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-pill fw-medium summary-jenis-badge" 
                          id="summaryJenis_${id}" style="font-size: 0.75rem;" title="${label}">
                        <i class="bi ${icon} me-1 text-primary"></i>${displayLabel}: <strong class="text-dark" id="summaryJenisCount_${id}">${count}</strong>
                    </span>`;
            });
            $wrapper.html(html);
        } else {
            $wrapper.empty();
        }

        const filtered = state.filter.tujuanId > 0 || state.filter.jenisSoal > 0 || state.search !== '';
        if (!filtered) {
            $listHint.text('Menampilkan ' + state.total + ' butir soal.');
        }
        // Merge current-page nomors into cross-page nomor cache
        const pageNomors = state.items.map(it => ({ nomor: it.nomor, id_item: it.id_item }));
        const pageIds    = new Set(pageNomors.map(e => e.id_item));
        state.allNomors  = [
            ...state.allNomors.filter(e => !pageIds.has(e.id_item)),
            ...pageNomors,
        ];
    }

    function renderPagination() {
        const start = state.total === 0 ? 0 : ((state.page - 1) * state.perPage + 1);
        const end   = Math.min(state.total, state.page * state.perPage);
        $paginationInfo.html('Menampilkan <strong class="text-dark">' + start + '</strong>–<strong class="text-dark">' + end + '</strong> dari <strong class="text-dark">' + state.total + '</strong> butir');
        $paginationBtns.empty();

        if (state.totalPages <= 1) return;

        const goPage = function (p) {
            loadItems(p);
            // Scroll to top of table smoothly
            const $card = $('#itemsTableWrap').closest('.card');
            if ($card.length) {
                $('html, body').animate({ scrollTop: $card.offset().top - 72 }, 250);
            }
        };

        // First & Prev buttons
        const $first = $('<li class="page-item ' + (state.page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" title="Halaman Pertama"><i class="bi bi-chevron-double-left"></i></a></li>');
        if (state.page > 1) {
            $first.find('a').on('click', function(e) { e.preventDefault(); goPage(1); });
        }
        $paginationBtns.append($first);

        const $prev = $('<li class="page-item ' + (state.page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" title="Halaman Sebelumnya"><i class="bi bi-chevron-left"></i></a></li>');
        if (state.page > 1) {
            $prev.find('a').on('click', function(e) { e.preventDefault(); goPage(state.page - 1); });
        }
        $paginationBtns.append($prev);

        // Smart window for page numbers
        const startPage = Math.max(1, state.page - 2);
        const endPage = Math.min(state.totalPages, state.page + 2);

        if (startPage > 1) {
            const $p1 = $('<li class="page-item"><a class="page-link" href="javascript:void(0)">1</a></li>');
            $p1.find('a').on('click', function(e) { e.preventDefault(); goPage(1); });
            $paginationBtns.append($p1);
            if (startPage > 2) {
                $paginationBtns.append('<li class="page-item disabled"><span class="page-link border-0">…</span></li>');
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            const isActive = p === state.page;
            const $btn = $('<li class="page-item ' + (isActive ? 'active' : '') + '"><a class="page-link" href="javascript:void(0)">' + p + '</a></li>');
            if (!isActive) {
                $btn.find('a').on('click', (function (pg) {
                    return function (e) { e.preventDefault(); goPage(pg); };
                })(p));
            }
            $paginationBtns.append($btn);
        }

        if (endPage < state.totalPages) {
            if (endPage < state.totalPages - 1) {
                $paginationBtns.append('<li class="page-item disabled"><span class="page-link border-0">…</span></li>');
            }
            const $pN = $('<li class="page-item"><a class="page-link" href="javascript:void(0)">' + state.totalPages + '</a></li>');
            $pN.find('a').on('click', function(e) { e.preventDefault(); goPage(state.totalPages); });
            $paginationBtns.append($pN);
        }

        // Next & Last buttons
        const $next = $('<li class="page-item ' + (state.page >= state.totalPages ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" title="Halaman Selanjutnya"><i class="bi bi-chevron-right"></i></a></li>');
        if (state.page < state.totalPages) {
            $next.find('a').on('click', function(e) { e.preventDefault(); goPage(state.page + 1); });
        }
        $paginationBtns.append($next);

        const $last = $('<li class="page-item ' + (state.page >= state.totalPages ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" title="Halaman Terakhir"><i class="bi bi-chevron-double-right"></i></a></li>');
        if (state.page < state.totalPages) {
            $last.find('a').on('click', function(e) { e.preventDefault(); goPage(state.totalPages); });
        }
        $paginationBtns.append($last);
    }

    /* ============================================================
       ORDER IDs & NOMOR CACHE
       ============================================================ */
    function refreshOrderIds() {
        return $.get(base_url + 'cbtkartusoal/api/items/' + cfg.uuid + '?ids_only=1')
            .done(function (res) {
                state.orderIds = res?.data?.order || [];
            });
    }

    /**
     * Fetch all item nomor values across ALL pages for accurate duplicate detection.
     * Uses the lightweight ?all_nomors=1 endpoint (only nomor + id_item, no full payload).
     */
    function refreshAllNomors() {
        return $.get(base_url + 'cbtkartusoal/api/items/' + cfg.uuid + '?all_nomors=1')
            .done(function (res) {
                if (res?.status && Array.isArray(res?.data?.nomors)) {
                    state.allNomors = res.data.nomors;
                }
            });
    }

    function findItemById(idItem) {
        return state.items.find(it => parseInt(it.id_item, 10) === parseInt(idItem, 10)) || null;
    }

    function bulkDelete() {
        if (!cfg.canManage || !cfg.isEditable) return;
        
        let selected = [];
        $('.check-item:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            Swal.fire('Peringatan', 'Silakan pilih soal yang akan dihapus.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Hapus Terpilih?',
            text: `Anda akan menghapus ${selected.length} butir soal. Tindakan ini tidak dapat dikembalikan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) {
                $.post(base_url + 'cbtkartusoal/removeItem', {
                    [CSRF_NAME]: CSRF_HASH,
                    uuid: cfg.uuid,
                    ids: selected
                }, function(resp) {
                    if (resp.status) {
                        Swal.fire({ icon: 'success', title: 'Terhapus', text: resp.message, timer: 1500, showConfirmButton: false })
                            .then(() => loadItems(state.page));
                    } else {
                        Swal.fire('Gagal', resp.message, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem saat menghapus data.', 'error');
                });
            }
        });
    }

    window.bulkDelete = bulkDelete; // Expose to global for onclick

    function reorderByMove(itemId, dir) {
        const idx    = state.orderIds.findIndex(id => parseInt(id, 10) === parseInt(itemId, 10));
        if (idx < 0) return;
        const target = dir === 'up' ? idx - 1 : idx + 1;
        if (target < 0 || target >= state.orderIds.length) return;

        const tmp = state.orderIds[idx];
        state.orderIds[idx]    = state.orderIds[target];
        state.orderIds[target] = tmp;

        $.post(base_url + 'cbtkartusoal/reorder', { [CSRF_NAME]: CSRF_HASH, uuid: cfg.uuid, order: state.orderIds })
            .done(function (res) {
                if (!res?.status) { Swal.fire('Gagal', res?.message || 'Gagal mengubah urutan.', 'error'); refreshOrderIds(); return; }
                loadItems(state.page);
            })
            .fail(function () { Swal.fire('Gagal', 'Gagal mengubah urutan.', 'error'); refreshOrderIds(); });
    }

    /* ============================================================
       AI GENERATE
       ============================================================ */
    const levelMatch = function(tpLevel, kelasLevel) {
        if (!tpLevel || !kelasLevel) return true;
        const t = String(tpLevel).toUpperCase().trim();
        const k = String(kelasLevel).toUpperCase().trim();
        if (t === k) return true;
        
        const map = {
            '10': 'X', '11': 'XI', '12': 'XII',
            '7': 'VII', '8': 'VIII', '9': 'IX',
            '1': 'I', '2': 'II', '3': 'III', '4': 'IV', '5': 'V', '6': 'VI'
        };
        const revMap = {
            'X': '10', 'XI': '11', 'XII': '12',
            'VII': '7', 'VIII': '8', 'IX': '9',
            'I': '1', 'II': '2', 'III': '3', 'IV': '4', 'V': '5', 'VI': '6'
        };
        
        return map[t] === k || map[k] === t || revMap[t] === k || revMap[k] === t;
    };

    /* Satu modal AI bertab — helper buka + pilih tab */
    function showAiGeneratorModal(tabPaneId) {
        const modalEl = document.getElementById('aiGeneratorModal');
        if (!modalEl) return;
        if (tabPaneId) {
            const tabBtn = document.querySelector('[data-bs-target="#' + tabPaneId + '"]');
            if (tabBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                bootstrap.Tab.getOrCreateInstance(tabBtn).show();
            }
        }
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    }

    function openAiGenerateModal() {
        $('#aiQualityMessage').text('');
        
        // Auto-select dropdown based on current mapel/kelas if applicable
        const kelasLevel = cfg.kelasLevel || '';
        if (kelasLevel) {
            // Map roman to numeral for dropdown
            const revMap = { 'X': '10', 'XI': '11', 'XII': '12', 'VII': '7', 'VIII': '8', 'IX': '9' };
            const v = revMap[String(kelasLevel).toUpperCase().trim()] || kelasLevel;
            $('#aiFilterKelas').val(v);
        } else {
            $('#aiFilterKelas').val('');
        }
        $('#aiFilterSemester').val('');

        applyAiTpFilters();

        showAiGeneratorModal('tabAiPerTp');
    }

    function applyAiTpFilters() {
        $('#aiTpCheckboxList .filter-empty-msg').remove();
        
        const fKelas = $('#aiFilterKelas').val();
        const fSmt = $('#aiFilterSemester').val();
        let visibleCount = 0;

        $('.ai-tp-item').each(function() {
            const $item = $(this);
            const $chk = $item.find('.aiTpCheck');
            const tpKelas = $chk.data('tingkat') || '';
            const tpSmt = $item.data('smt') || '';
            
            let match = true;
            if (fKelas && String(tpKelas) !== String(fKelas)) match = false;
            if (fSmt && String(tpSmt) !== String(fSmt)) match = false;
            
            if (match) {
                $item.removeClass('d-none');
                $chk.prop('checked', true);
                visibleCount++;
            } else {
                $item.addClass('d-none');
                $chk.prop('checked', false);
            }
        });
        
        if (visibleCount === 0 && $('.aiTpCheck').length > 0) {
            $('#aiTpCheckboxList').append('<div class="small text-danger filter-empty-msg mt-2"><i class="bi bi-info-circle me-1"></i>Tidak ada TP spesifik untuk filter kelas/semester ini. <a href="#" id="btnShowAllTp" class="text-primary text-decoration-none fw-semibold">Reset Filter</a></div>');
            $('#btnShowAllTp').on('click', function(e) {
                e.preventDefault();
                $('#aiFilterKelas, #aiFilterSemester').val('');
                applyAiTpFilters();
            });
        }

        updateTpDetailPanel();
    }

    function setAiGenerateLoading(loading) {
        $('#btnGenerateAi').prop('disabled', loading);
        $('#aiGenerateSpinner').toggleClass('d-none', !loading);
        $('#aiGenerateText').html(loading ? 'Memproses AI…' : '<i class="bi bi-stars me-1"></i>Generate Kisi-kisi');
    }

    function setAiSaveLoading(loading) {
        $('#btnSaveAiGenerated').prop('disabled', loading);
        $('#aiSaveSpinner').toggleClass('d-none', !loading);
        $('#aiSaveText').html(loading ? 'Menyimpan…' : '<i class="bi bi-floppy me-1"></i>Simpan ke Kartu Soal');
    }

    function collectAiJenis() {
        const arr = [];
        $('.aiJenis:checked').each(function () { arr.push($(this).val()); });
        return arr;
    }

    function collectSelectedTpIds() {
        const ids = [];
        $('.aiTpCheck:checked').each(function () { ids.push(parseInt($(this).val(), 10)); });
        return ids;
    }

    function updateTpDetailPanel() {
        const $panel   = $('#aiSelectedTpDetail');
        const $content = $('#aiTpDetailContent');
        const checked  = $('.aiTpCheck:checked');
        
        $('#aiTpBadgeCount').text(checked.length);

        if (checked.length === 0) {
            $panel.addClass('d-none');
            return;
        }

        let html = '';
        checked.each(function () {
            const $cb   = $(this);
            const kode  = $cb.data('kode')  || '-';
            const cp    = $cb.data('cp')    || '';
            const materi = $cb.data('materi') || '';
            const fase  = $cb.data('fase')  || '';
            const tujuan = $cb.data('tujuan') || '';
            html += `<div class="mb-1 pb-1 border-bottom">
                <span class="badge bg-primary-subtle text-primary me-1">${esc(kode)}</span>
                ${fase ? `<span class="badge bg-info-subtle text-info me-1">Fase ${esc(fase)}</span>` : ''}
                ${cp   ? `<span class="badge bg-secondary-subtle text-secondary me-1">${esc(cp)}</span>` : ''}
                <div class="text-muted mt-1" style="font-size:0.72rem;">${esc(materi || tujuan).substring(0,120)}</div>
            </div>`;
        });

        $content.html(html);
        $panel.removeClass('d-none');
    }

    function renderAiPreviewList(items, meta, rejected) {
        state.aiPreviewItems = items || [];
        const $list  = $('#aiPreviewList');
        const $empty = $('#aiPreviewEmpty');
        const $meta  = $('#aiPreviewMeta');
        const $save  = $('#btnSaveAiGenerated');

        if (!state.aiPreviewItems.length) {
            $list.addClass('d-none').empty();
            $empty.removeClass('d-none');
            $meta.text('Tidak ada hasil valid.');
            $save.addClass('d-none');
            return;
        }

        // Build TP options HTML from live cache
        let tpOptionsHtml = '<option value="">Pilih TP…</option>';
        state.tujuanData.forEach(tp => {
            tpOptionsHtml += `<option value="${tp.id_tujuan}">${esc(tp.kode)} — ${esc(truncate(tp.tujuan, 50))}</option>`;
        });
        // Fallback: build from checked TP checkboxes if cache empty
        if (!state.tujuanData.length) {
            tpOptionsHtml = '<option value="">Pilih TP…</option>';
            $('.aiTpCheck').each(function () {
                const $cb = $(this);
                tpOptionsHtml += `<option value="${$cb.val()}">${esc($cb.data('kode') || $cb.val())}</option>`;
            });
        }

        let html = '';
        state.aiPreviewItems.forEach(function (item, idx) {
            const j = parseInt(item.jenis_soal || 1, 10);
            // Find TP name from cache or checkbox data
            let tpLabel = '';
            const tpData = state.tujuanData.find(t => t.id_tujuan === item.id_tujuan);
            if (tpData) {
                tpLabel = `<span class="badge bg-info-subtle text-info border-0 ms-1" style="font-size:0.65rem;">${esc(tpData.kode || '')}</span>`;
            }
            html += `<div class="card border mb-2 ai-preview-item" data-index="${idx}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <div><strong class="small">Kisi-kisi #${idx + 1}</strong>${tpLabel}</div>
                    <span class="badge bg-primary-subtle text-primary border-0 small">${esc(cfg.labels[j] || 'Unknown')}</span>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="small fw-semibold">Jenis Soal</label>
                            <select class="form-select form-select-sm ai-jenis">
                                ${[1,2,3,4,5,6,7].map(v => `<option value="${v}"${v===j?' selected':''}>${esc(cfg.labels[v]||v)}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-semibold">Tujuan Pembelajaran</label>
                            <select class="form-select form-select-sm ai-tujuan">${tpOptionsHtml}</select>
                        </div>
                        <div class="col-md-3">
                            <label class="small fw-semibold">Waktu (mnt)</label>
                            <input type="number" class="form-control form-control-sm ai-waktu" min="1" max="30" value="${esc(item.estimasi_waktu||2)}">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="small fw-semibold">Level Kognitif</label>
                            <select class="form-select form-select-sm ai-level">
                                ${['C1','C2','C3','C4','C5','C6'].map(v=>`<option value="${v}"${item.level_kognitif===v?' selected':''}>${v}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="small fw-semibold">Materi Pokok</label>
                            <input type="text" class="form-control form-control-sm ai-materi" value="${esc(item.materi||'')}">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="small fw-semibold">Indikator Soal</label>
                        <textarea class="form-control form-control-sm ai-indikator" rows="2">${esc(item.indikator_soal||'')}</textarea>
                    </div>
                    <div class="text-danger small ai-item-error"></div>
                </div>
            </div>`;
        });

        $list.html(html);
        $list.find('.ai-tujuan').each(function (i) {
            $(this).val(String(state.aiPreviewItems[i]?.id_tujuan || ''));
        });

        $empty.addClass('d-none');
        $list.removeClass('d-none');
        $save.removeClass('d-none');

        const rejCount   = Array.isArray(rejected) ? rejected.length : 0;
        const validCount = parseInt(meta?.valid_count || state.aiPreviewItems.length, 10);
        $meta.html(`<span class="badge bg-success-subtle text-success border-0 me-1">${validCount} valid</span>` +
            (rejCount > 0 ? `<span class="badge bg-danger-subtle text-danger border-0">${rejCount} ditolak</span>` : ''));
        if (rejCount > 0) {
            $('#aiQualityMessage').text('Sebagian ditolak quality gate. Tambahkan instruksi lebih spesifik.').addClass('text-danger').removeClass('text-info');
        }
    }

    function collectAiPreviewItems() {
        const items = [];
        $('#aiPreviewList .ai-preview-item').each(function () {
            const $c = $(this);
            items.push({
                jenis_soal     : parseInt($c.find('.ai-jenis').val() || '1', 10),
                id_tujuan      : parseInt($c.find('.ai-tujuan').val() || '0', 10),
                estimasi_waktu : parseInt($c.find('.ai-waktu').val()  || '2', 10),
                materi         : $c.find('.ai-materi').val()    || '',
                level_kognitif : $c.find('.ai-level').val()     || 'C4',
                indikator_soal : $c.find('.ai-indikator').val() || '',
            });
        });
        return items;
    }

    function validateAiPreviewItems(items) {
        $('#aiPreviewList .ai-item-error').text('');
        let valid = true;
        items.forEach(function (it, idx) {
            const errs = [];
            if (!it.id_tujuan)                              errs.push('Tujuan wajib.');
            if (stripHtml(it.materi).length < 3)            errs.push('Materi terlalu singkat.');
            if (stripHtml(it.indikator_soal).length < 10)   errs.push('Indikator terlalu singkat.');
            if (errs.length) {
                valid = false;
                $('#aiPreviewList .ai-preview-item').eq(idx).find('.ai-item-error').text(errs.join(' '));
            }
        });
        return valid;
    }

    function generateAiItems() {
        const jenis = collectAiJenis();
        if (!jenis.length) { Swal.fire('Validasi', 'Pilih minimal satu jenis soal.', 'warning'); return; }
        const selectedTpIds = collectSelectedTpIds();
        if (!selectedTpIds.length) { Swal.fire('Validasi', 'Pilih minimal satu Tujuan Pembelajaran.', 'warning'); return; }
        
        // Tampilkan loader overlay
        showGenerateLoader();
        setAiGenerateLoading(true);
        $('#aiQualityMessage').text('');
        $('#btnSaveAiGenerated').addClass('d-none');

        const postData = {
            [CSRF_NAME]       : CSRF_HASH,
            uuid              : cfg.uuid,
            jumlah_soal       : Math.max(1, Math.min(30, parseInt($('#aiJumlahSoal').val() || '5', 10))),
            tingkat_kesulitan : $('#aiKesulitan').val(),
            jenis_soal        : jenis,
            ai_model          : $('#aiModel').val(),
        };
        // Append selected TP IDs as array
        selectedTpIds.forEach(function (id, i) {
            postData['selected_tujuan_ids[' + i + ']'] = id;
        });

        $.post(base_url + 'cbtkartusoal/generate-ai', postData)
        .done(function (res) {
            if (!res?.status) {
                const waitSec = res?.data?.wait_seconds || 0;
                if (waitSec > 0) {
                    let timerInterval;
                    Swal.fire({
                        title: 'Limit Kuota Tercapai',
                        html: `<div class="text-center">
                            <p class="mb-2">${res.message}</p>
                            <div class="alert alert-warning py-2 small">
                                Silakan tunggu <b><span id="ai-countdown">${waitSec}</span></b> detik sebelum mencoba lagi.
                            </div>
                            <p class="small text-muted mb-0">Tips: Gunakan model <b>Flash 1.5</b> untuk limit yang lebih longgar.</p>
                        </div>`,
                        icon: 'warning',
                        timer: waitSec * 1000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const b = Swal.getHtmlContainer().querySelector('#ai-countdown');
                            let current = waitSec;
                            timerInterval = setInterval(() => {
                                current--;
                                if (b) b.textContent = current;
                                if (current <= 0) clearInterval(timerInterval);
                            }, 1000);
                        },
                        willClose: () => { clearInterval(timerInterval); }
                    });
                } else {
                    Swal.fire('Gagal Generate', res?.message || 'Gagal generate AI.', 'error');
                }
                renderAiPreviewList([], null, []);
                return;
            }
            const d = res.data || {};
            const meta = d.meta || {};
            renderAiPreviewList(d.items || [], meta, d.rejected || []);
            // Show TP usage info
            if (meta.tp_used !== undefined) {
                $('#aiQualityMessage').html(
                    meta.tp_used < meta.tp_total
                        ? `<i class="bi bi-funnel-fill me-1"></i>Menggunakan <strong>${meta.tp_used}</strong> dari ${meta.tp_total} TP yang tersedia.`
                        : ''
                ).removeClass('text-danger').addClass('text-info');
            }
        })
        .fail(function (xhr) { 
            const res = xhr?.responseJSON;
            const waitSec = res?.data?.wait_seconds || 0;
            if (waitSec > 0) {
                let timerInterval;
                Swal.fire({
                    title: 'Limit Kuota Tercapai',
                    html: `<div class="text-center">
                        <p class="mb-2">${res.message || 'API Limit exceeded'}</p>
                        <div class="alert alert-warning py-2 small">
                            Silakan tunggu <b><span id="ai-countdown-err">${waitSec}</span></b> detik sebelum mencoba lagi.
                        </div>
                    </div>`,
                    icon: 'warning',
                    timer: waitSec * 1000,
                    timerProgressBar: true,
                    didOpen: () => {
                        const b = Swal.getHtmlContainer().querySelector('#ai-countdown-err');
                        let current = waitSec;
                        timerInterval = setInterval(() => {
                            current--;
                            if (b) b.textContent = current;
                            if (current <= 0) clearInterval(timerInterval);
                        }, 1000);
                    },
                    willClose: () => { clearInterval(timerInterval); }
                });
            } else {
                Swal.fire('Gagal', res?.message || 'Gagal menghubungi AI.', 'error'); 
            }
        })
        .always(function () { 
            // Sembunyikan loader overlay baik sukses maupun gagal
            hideGenerateLoader();
            setAiGenerateLoading(false); 
        });
    }

    function saveAiGeneratedItems() {
        if (!cfg.isEditable) { Swal.fire('Terkunci', 'Kembalikan ke draft/revisi untuk menambah butir.', 'warning'); return; }
        const items = collectAiPreviewItems();
        if (!items.length) { Swal.fire('Info', 'Belum ada preview soal AI.', 'info'); return; }
        if (!validateAiPreviewItems(items)) { $('#aiQualityMessage').text('Masih ada butir yang belum memenuhi kualitas minimum.'); return; }

        setAiSaveLoading(true);
        $.post(base_url + 'cbtkartusoal/save-generated', { [CSRF_NAME]: CSRF_HASH, uuid: cfg.uuid, items: JSON.stringify(items) })
            .done(function (res) {
                if (!res?.status) {
                    let errHtml = '<p>' + (res?.message || 'Gagal menyimpan hasil AI.') + '</p>';
                    const srvErrs = res?.data?.errors || [];
                    if (srvErrs.length) errHtml += '<ul class="text-start small mt-2">' + srvErrs.map(e => '<li>' + esc(e) + '</li>').join('') + '</ul>';
                    Swal.fire({ title: 'Gagal', html: errHtml, icon: 'error' });
                    return;
                }
                Swal.fire({
                    title: 'Berhasil!',
                    text: res?.message || 'Soal AI tersimpan.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                }).then(function() {
                    const aiModal = document.getElementById('aiGeneratorModal');
                    bootstrap.Modal.getInstance(aiModal)?.hide();
                    loadItems(state.page);
                });
            })
            .fail(function (xhr) { Swal.fire('Gagal', xhr?.responseJSON?.message || 'Gagal menyimpan.', 'error'); })
            .always(function () { setAiSaveLoading(false); });
    }

    /* ============================================================
       WORKFLOW ACTIONS
       ============================================================ */
    function workflowPost(url, confirmOpts, extraPayload) {
        Swal.fire(confirmOpts).then(function (r) {
            if (!r.isConfirmed) return;
            const payload = Object.assign({ [CSRF_NAME]: CSRF_HASH, uuid: cfg.uuid }, extraPayload || {});
            if (r.value !== undefined) payload.note = r.value || '';
            $.post(base_url + url, payload)
                .done(function (res) {
                    if (res?.status) {
                        Swal.fire('Berhasil', res.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', res?.message || 'Operasi gagal.', 'error');
                    }
                })
                .fail(function () { Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error'); });
        });
    }

    /* ============================================================
       SOFT-UNDO DELETE TOAST
       ============================================================ */
    let _undoTimer = null;
    let _undoCommitFn = null;
    let _undoUndoFn   = null;

    function showUndoToast(message, onUndo, onCommit) {
        // Cancel any previously pending delete
        if (_undoTimer) {
            clearTimeout(_undoTimer);
            if (_undoCommitFn) _undoCommitFn();
        }
        _undoUndoFn   = onUndo;
        _undoCommitFn = onCommit;

        const $toast = $undoToast;
        $toast.find('#undoToastMsg').text(message);
        $toast.removeClass('d-none').addClass('show');

        let secs = 5;
        const $cnt = $toast.find('#undoCountdown');
        $cnt.text(secs);

        const tick = setInterval(function () {
            secs--;
            $cnt.text(secs);
            if (secs <= 0) clearInterval(tick);
        }, 1000);

        _undoTimer = setTimeout(function () {
            clearInterval(tick);
            $toast.removeClass('show').addClass('d-none');
            _undoTimer = null;
            if (_undoCommitFn) { _undoCommitFn(); _undoCommitFn = null; }
        }, 5500);

        $toast.find('#btnUndoDelete').off('click').on('click', function () {
            clearTimeout(_undoTimer); clearInterval(tick);
            _undoTimer = null;
            $toast.removeClass('show').addClass('d-none');
            if (_undoUndoFn) { _undoUndoFn(); _undoUndoFn = null; }
            _undoCommitFn = null;
        });
    }

    /* ============================================================
       EVENT LISTENERS
       ============================================================ */

    // Filter bar — debounced 300ms
    let _filterTimer = null;
    function scheduleFilter() {
        clearTimeout(_filterTimer);
        _filterTimer = setTimeout(applyFilter, 300);
    }
    $('#filterTujuanId, #filterJenisSoal').on('change', scheduleFilter);
    $('#filterSearch').on('input', function () {
        const val = $(this).val().trim();
        $('#btnClearSearch').toggleClass('d-none', !val);
        scheduleFilter();
    });
    $('#btnClearSearch').on('click', function () {
        $('#filterSearch').val('');
        $(this).addClass('d-none');
        applyFilter();
    });
    $('#btnResetFilter').on('click', function () { resetFilter(); });

    // Rows per page selector
    $('#detailPerPage').val(state.perPage);
    $('#detailPerPage').on('change', function () {
        state.perPage = parseInt($(this).val() || '25', 10);
        localStorage.setItem('kartu_detail_per_page', state.perPage);
        state.page = 1;
        loadItems(1);
    });

    // Keyboard shortcut: Alt+N → Tambah Butir
    $(document).on('keydown', function (e) {
        if (e.altKey && e.key === 'n' && cfg.canManage && cfg.isEditable) {
            e.preventDefault();
            openModal(null);
        }
    });

    // TP selection in modal → auto-populate
    $('#itemTujuan').on('change', function () {
        const opt = this.options[this.selectedIndex];
        autoPopulateFromTp(opt, false);
        renderTpPreview();
        validateForm();
        scheduleAutoSave();
    });

    // Sync TP button
    $('#btnSyncTp').on('click', function (e) { e.preventDefault(); fetchTujuan(true); });

    // Nomor change → duplicate check
    $('#itemNomor').on('input change', function () {
        validateForm();
        scheduleAutoSave();
    });

    // Other field changes → auto-save
    if (cfg.canManage) {
        $('#itemJenis').on('change', function () { scheduleAutoSave(); });
        $('#itemLevel, #itemWaktu, #itemMateri, #itemIndikator, #itemCp').on('input change', function () {
            validateForm();
            scheduleAutoSave();
        });

        $('#btnAddItem').on('click', function () { openModal(null); });
        $('#btnSaveItem').on('click', function () { saveItem(false); });
        $('#btnAutoReorder').on('click', function () {
            Swal.fire({
                title: 'Urutkan Nomor Soal?',
                html: 'Soal akan diurutkan berdasarkan <strong>jenis/bentuk soal</strong> terlebih dahulu, kemudian nomor soal akan disesuaikan secara berurutan.<br><br><span class="text-muted small">Contoh: Semua PG (No. 1-5), PG Kompleks (No. 6-10), dst.</span>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-sort-numeric-down"></i> Ya, Urutkan',
                cancelButtonText: 'Batal'
            }).then(function(r) {
                if (!r.isConfirmed) return;
                $.post(base_url + 'cbtkartusoal/auto-reorder', { [CSRF_NAME]: CSRF_HASH, uuid: cfg.uuid })
                .done(function (res) {
                    if (res?.status) {
                        Swal.fire({ title: 'Berhasil', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
                        loadItems(state.page);
                    } else {
                        Swal.fire('Gagal', res?.message || 'Gagal mengurutkan nomor.', 'error');
                    }
                })
                .fail(function () { Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error'); });
            });
        });

        $('#btnOpenAiGenerate').on('click', function () { openAiGenerateModal(); });
        $('#btnGenerateAi').on('click', function () { generateAiItems(); });
        $('#btnSaveAiGenerated').on('click', function () { saveAiGeneratedItems(); });

        // AI Model selection toggler
        $(document).on('click', '.ai-model-btn', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const model = $btn.data('model') || '';
            
            $('#aiModel').val(model);
            
            // Visual toggle
            $('.ai-model-btn').removeClass('btn-primary active shadow-sm').addClass('btn-outline-secondary');
            $btn.removeClass('btn-outline-secondary').addClass('btn-primary active shadow-sm');
            
            console.log('AI Model selected:', model);
        });

        // AI modal TP checkbox events
        $(document).on('change', '#aiFilterKelas, #aiFilterSemester', function() { applyAiTpFilters(); });
        $(document).on('change', '.aiTpCheck', function () { updateTpDetailPanel(); });
        $(document).on('click', '#btnSelectAllTp', function (e) {
            e.preventDefault();
            // Cuma check yang sedang ditampilah (ter-filter) saja
            $('.ai-tp-item:not(.d-none) .aiTpCheck').prop('checked', true);
            updateTpDetailPanel();
        });
        $(document).on('click', '#btnClearTp', function (e) {
            e.preventDefault();
            $('.aiTpCheck').prop('checked', false);
            updateTpDetailPanel();
        });

        // Table row actions
        $itemsBody.on('click', '.btnPreview', function () {
            const item = findItemById($(this).closest('tr').data('id'));
            if (!item) { Swal.fire('Info', 'Data butir tidak ditemukan.', 'info'); return; }
            showContohSoalModal(item);
        });

        $itemsBody.on('click', '.btnEdit', function () {
            const item = findItemById($(this).closest('tr').data('id'));
            if (!item) { Swal.fire('Info', 'Data butir tidak ditemukan. Muat ulang halaman.', 'info'); return; }
            openModal(item);
        });

        $itemsBody.on('click', '.btnDelete', function () {
            const $row    = $(this).closest('tr');
            const id      = parseInt($row.data('id') || '0', 10);
            const nomor   = $row.find('td:eq(1)').text().trim();
            if (!id) return;

            // Soft-undo: hide row immediately, give 5s window to cancel
            $row.addClass('opacity-50 pe-none'); // dim row while pending
            showUndoToast('Butir No ' + nomor + ' akan dihapus.', function onUndo() {
                // User clicked Batal — restore row
                $row.removeClass('opacity-50 pe-none');
            }, function onCommit() {
                // Timer expired or user confirmed — execute delete
                $.post(base_url + 'cbtkartusoal/removeItem', { [CSRF_NAME]: CSRF_HASH, uuid: cfg.uuid, id_item: id })
                    .done(function (res) {
                        if (!res?.status) {
                            $row.removeClass('opacity-50 pe-none');
                            Swal.fire('Gagal', res?.message || 'Gagal menghapus.', 'error');
                            return;
                        }
                        loadItems(state.page);
                    })
                    .fail(function () {
                        $row.removeClass('opacity-50 pe-none');
                        Swal.fire('Gagal', 'Gagal menghapus butir.', 'error');
                    });
            });
        });

        $itemsBody.on('click', '.btnMoveUp',   function () { reorderByMove($(this).closest('tr').data('id'), 'up'); });
        $itemsBody.on('click', '.btnMoveDown',  function () { reorderByMove($(this).closest('tr').data('id'), 'down'); });

        // Workflow
        $('#btnSubmitApproval').on('click', function () {
            let desc = 'Kartu soal akan dikirim untuk disetujui.';
            if (state.summaryJenis && Object.keys(state.summaryJenis).length > 0) {
                const items = [];
                Object.entries(state.summaryJenis).forEach(([id, count]) => {
                    const label = cfg.labels[id] || 'Lainnya';
                    const shortLabel = label.replace('Pilihan Ganda Kompleks', 'PG Kmplx').replace('Pilihan Ganda', 'PG');
                    items.push(`<strong>${count}</strong> ${shortLabel}`);
                });
                desc += `<div class="mt-3 p-2 bg-light border rounded small text-secondary">` +
                       `<i class="bi bi-info-circle me-1"></i>Ringkasan: ${items.join(' — ')}` +
                       `</div>`;
            }

            workflowPost('cbtkartusoal/submitForApproval', {
                title: 'Ajukan Validasi?', 
                html: desc,
                icon: 'question', 
                showCancelButton: true, 
                confirmButtonText: 'Ajukan', 
                cancelButtonText: 'Batal',
            });
        });

        $('#btnApproveKartu').on('click', function () {
            workflowPost('cbtkartusoal/approve', {
                title: 'Setujui Kartu Soal?', text: 'Kartu soal akan dikunci.',
                icon: 'success', input: 'textarea', inputPlaceholder: 'Catatan (opsional)…',
                showCancelButton: true, confirmButtonText: 'Setujui', cancelButtonText: 'Batal',
            });
        });

        $('#btnRejectKartu').on('click', function () {
            workflowPost('cbtkartusoal/returnForRevision', {
                title: 'Minta Revisi?', text: 'Kartu akan dikembalikan ke pembuat.',
                icon: 'warning', input: 'textarea', inputPlaceholder: 'Tulis alasan revisi…',
                inputValidator: v => !v ? 'Alasan wajib diisi!' : null,
                showCancelButton: true, confirmButtonText: 'Kembalikan', cancelButtonText: 'Batal',
            });
        });

        $('#btnCancelApproval').on('click', function () {
            Swal.fire({
                title: 'Batalkan Persetujuan?',
                html: '<p class="text-muted mb-2">Kartu soal akan dikembalikan ke status <strong>Draft</strong> dan dapat diedit kembali oleh pembuat.</p>' +
                      '<p class="text-danger small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini akan membatalkan persetujuan yang telah diberikan.</p>',
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Alasan Pembatalan (wajib)',
                inputPlaceholder: 'Tuliskan alasan mengapa persetujuan dibatalkan…',
                inputAttributes: { rows: 3 },
                inputValidator: function (v) {
                    if (!v || v.trim().length < 5) return 'Alasan minimal 5 karakter!';
                },
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-shield-x me-1"></i>Ya, Batalkan Persetujuan',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#dc3545',
                reverseButtons: true,
                focusCancel: true,
            }).then(function (r) {
                if (!r.isConfirmed) return;
                $.post(base_url + 'cbtkartusoal/cancel-approval', {
                    [CSRF_NAME]: CSRF_HASH,
                    uuid: cfg.uuid,
                    note: r.value || '',
                })
                .done(function (res) {
                    if (res?.status) {
                        Swal.fire({
                            title: 'Persetujuan Dibatalkan',
                            text: res.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', res?.message || 'Gagal membatalkan persetujuan.', 'error');
                    }
                })
                .fail(function (xhr) {
                    Swal.fire('Gagal', xhr?.responseJSON?.message || 'Terjadi kesalahan sistem.', 'error');
                });
            });
        });
    }

    /* ============================================================
       INIT
       ============================================================ */
    // Load TP data (will also populate filter dropdown)
    fetchTujuan(false);
    // Checkbox events for bulk delete
    $(document).on('change', '#checkAll', function() {
        $('.check-item').prop('checked', $(this).is(':checked')).trigger('change');
    });

    $(document).on('change', '.check-item', function() {
        let count = $('.check-item:checked').length;
        if (count > 0) {
            $('#btnBulkDelete').removeClass('d-none').addClass('fade-in');
            $('#listHint').addClass('d-none');
            $('#countChecked').text(count);
        } else {
            $('#btnBulkDelete').addClass('d-none').removeClass('fade-in');
            $('#listHint').removeClass('d-none');
            $('#checkAll').prop('checked', false);
        }
    });

    $(document).on('click', '#btnBulkDelete', function(e) {
        e.preventDefault();
        bulkDelete();
    });

    // Load first page of items
    loadItems(1);

    /* ============================================================
       PAKET BANKS — tombol "Lihat Soal Paket" & badge
       ============================================================ */
    const $btnLihatPaket = $('#btnLihatPaket');
    const refreshPaketBadge = function () {
        if (!$btnLihatPaket.length) return;
        const banks = cfg.paketBanks || [];
        $btnLihatPaket.toggle(banks.length > 0);
        $('#btnLihatPaketCount').text(banks.length);
    };
    if ($btnLihatPaket.length) {
        $btnLihatPaket.on('click', function () { openLihatPaketModal(); });
    }
    refreshPaketBadge();
    // Refresh daftar paket (baru dibuat / dihapus) dari server
    if (cfg.uuid) {
        $.get(base_url + 'cbtkartusoal/api/paket-banks/' + cfg.uuid)
            .done(function (res) {
                if (res && res.status && Array.isArray(res.data)) {
                    cfg.paketBanks = res.data;
                    refreshPaketBadge();
                }
            });
    }
});
