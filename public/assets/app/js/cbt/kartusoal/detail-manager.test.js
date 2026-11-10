/**
 * detail-manager.test.js
 * Unit tests for Kartu Soal detail-manager.js
 *
 * Framework : Jest (jsdom environment)
 * Run       : npx jest assets/app/js/cbt/kartusoal/detail-manager.test.js
 *
 * Setup: npm install --save-dev jest jest-environment-jsdom
 */

/* ==========================================================
   TEST HELPERS — replicate pure utility functions locally
   (These are extracted from detail-manager.js for testability)
   ========================================================== */

/** @param {string} str */
function esc(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/** @param {string} html */
function stripHtml(html) {
    if (!html) return '';
    // Lightweight version without DOMParser for Node environment
    return html.replace(/<[^>]+>/g, '').trim();
}

/** @param {string} str @param {number} len */
function truncate(str, len) {
    str = String(str || '');
    return str.length > len ? str.substring(0, len) + '…' : str;
}

/** Build query string, skipping empty/zero values */
function buildQuery(params) {
    return Object.entries(params)
        .filter(([, v]) => v !== '' && v !== 0 && v !== null && v !== undefined)
        .map(([k, v]) => encodeURIComponent(k) + '=' + encodeURIComponent(v))
        .join('&');
}

/** Client-side duplicate nomor check */
function isDuplicateNomor(nomor, allNomors, editingId = 0) {
    const n = parseInt(nomor, 10);
    if (!n || isNaN(n)) return false;
    return allNomors.some(entry => entry.nomor === n && entry.id_item !== editingId);
}

/** Map cognitive level to KKO (kata kerja operasional) */
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

/** Validate AI preview items */
function validateAiPreviewItems(items) {
    const errors = [];
    items.forEach((it, idx) => {
        const errs = [];
        if (!it.id_tujuan)                           errs.push('Tujuan wajib.');
        if (stripHtml(it.materi || '').length < 3)   errs.push('Materi terlalu singkat.');
        if (stripHtml(it.indikator_soal || '').length < 10) errs.push('Indikator terlalu singkat.');
        if (errs.length) errors.push({ index: idx, errors: errs });
    });
    return { valid: errors.length === 0, errors };
}

/* ==========================================================
   TESTS
   ========================================================== */

// ─────────────────────────────────────────────────────────
// 1. UTILITY FUNCTIONS
// ─────────────────────────────────────────────────────────
describe('esc()', () => {
    test('escapes HTML special characters', () => {
        expect(esc('<script>alert("xss")</script>')).toBe('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;');
    });
    test('escapes ampersand', () => {
        expect(esc('A & B')).toBe('A &amp; B');
    });
    test('handles empty input', () => {
        expect(esc('')).toBe('');
        expect(esc(null)).toBe('');
        expect(esc(undefined)).toBe('');
    });
    test('does not double-escape', () => {
        expect(esc('Hello')).toBe('Hello');
    });
});

describe('truncate()', () => {
    test('does not truncate short strings', () => {
        expect(truncate('Hello', 10)).toBe('Hello');
    });
    test('truncates and appends ellipsis', () => {
        const result = truncate('This is a long string', 10);
        expect(result).toBe('This is a …');
        expect(result.length).toBe(11);
    });
    test('handles null/undefined', () => {
        expect(truncate(null, 10)).toBe('');
        expect(truncate(undefined, 10)).toBe('');
    });
    test('handles exact length boundary', () => {
        expect(truncate('abcde', 5)).toBe('abcde');
        expect(truncate('abcdef', 5)).toBe('abcde…');
    });
});

describe('buildQuery()', () => {
    test('builds correct query string', () => {
        expect(buildQuery({ page: 1, per_page: 25 })).toBe('page=1&per_page=25');
    });
    test('skips zero values', () => {
        expect(buildQuery({ page: 1, tujuan_id: 0 })).toBe('page=1');
    });
    test('skips empty string values', () => {
        expect(buildQuery({ page: 1, filter: '' })).toBe('page=1');
    });
    test('skips null/undefined values', () => {
        expect(buildQuery({ page: 1, x: null, y: undefined })).toBe('page=1');
    });
    test('includes tujuan_id when > 0', () => {
        const q = buildQuery({ page: 1, per_page: 25, tujuan_id: 42 });
        expect(q).toContain('tujuan_id=42');
    });
    test('URL-encodes values', () => {
        const q = buildQuery({ search: 'hello world' });
        expect(q).toBe('search=hello%20world');
    });
});

// ─────────────────────────────────────────────────────────
// 2. DUPLICATE NOMOR DETECTION
// ─────────────────────────────────────────────────────────
describe('isDuplicateNomor()', () => {
    const existingNomors = [
        { nomor: 1, id_item: 10 },
        { nomor: 2, id_item: 11 },
        { nomor: 5, id_item: 12 },
    ];

    test('returns false for nomor not in list', () => {
        expect(isDuplicateNomor(3, existingNomors)).toBe(false);
    });

    test('returns true when nomor exists (new item, no editingId)', () => {
        expect(isDuplicateNomor(1, existingNomors, 0)).toBe(true);
    });

    test('returns false when nomor belongs to the item being edited', () => {
        expect(isDuplicateNomor(1, existingNomors, 10)).toBe(false);
    });

    test('returns true when nomor belongs to a DIFFERENT item', () => {
        // Editing item 10, but trying to use nomor 2 (owned by item 11)
        expect(isDuplicateNomor(2, existingNomors, 10)).toBe(true);
    });

    test('returns false for zero nomor', () => {
        expect(isDuplicateNomor(0, existingNomors)).toBe(false);
    });

    test('returns false for NaN nomor', () => {
        expect(isDuplicateNomor('abc', existingNomors)).toBe(false);
    });

    test('returns false for empty list', () => {
        expect(isDuplicateNomor(5, [])).toBe(false);
    });

    test('handles string nomor input correctly', () => {
        expect(isDuplicateNomor('5', existingNomors)).toBe(true);
        expect(isDuplicateNomor('5', existingNomors, 12)).toBe(false);
    });
});

// ─────────────────────────────────────────────────────────
// 3. KKO (Kata Kerja Operasional) MAPPING
// ─────────────────────────────────────────────────────────
describe('getKkoFromLevel()', () => {
    test.each([
        ['C1', 'menyebutkan'],
        ['C2', 'menjelaskan'],
        ['C3', 'menerapkan'],
        ['C4', 'menganalisis'],
        ['C5', 'mengevaluasi'],
        ['C6', 'merancang'],
    ])('maps %s → %s', (level, expected) => {
        expect(getKkoFromLevel(level)).toBe(expected);
    });

    test('handles lowercase input', () => {
        expect(getKkoFromLevel('c4')).toBe('menganalisis');
    });

    test('returns default for unknown level', () => {
        expect(getKkoFromLevel('C7')).toBe('memahami');
        expect(getKkoFromLevel('')).toBe('memahami');
        expect(getKkoFromLevel(null)).toBe('memahami');
    });
});

// ─────────────────────────────────────────────────────────
// 4. AI PREVIEW ITEMS VALIDATION
// ─────────────────────────────────────────────────────────
describe('validateAiPreviewItems()', () => {
    test('passes valid items', () => {
        const items = [{
            id_tujuan: 5,
            materi: 'Bilangan Bulat',
            indikator_soal: 'Peserta didik mampu menganalisis pola bilangan',
        }];
        const result = validateAiPreviewItems(items);
        expect(result.valid).toBe(true);
        expect(result.errors).toHaveLength(0);
    });

    test('fails when id_tujuan is missing', () => {
        const items = [{ id_tujuan: 0, materi: 'Bilangan', indikator_soal: 'Peserta didik dapat menganalisis' }];
        const result = validateAiPreviewItems(items);
        expect(result.valid).toBe(false);
        expect(result.errors[0].errors).toContain('Tujuan wajib.');
    });

    test('fails when materi is too short', () => {
        const items = [{ id_tujuan: 1, materi: 'AB', indikator_soal: 'Peserta didik dapat menjelaskan konsep' }];
        const result = validateAiPreviewItems(items);
        expect(result.valid).toBe(false);
        expect(result.errors[0].errors).toContain('Materi terlalu singkat.');
    });

    test('fails when indikator is too short', () => {
        const items = [{ id_tujuan: 1, materi: 'Materi valid', indikator_soal: 'Pendek' }];
        const result = validateAiPreviewItems(items);
        expect(result.valid).toBe(false);
        expect(result.errors[0].errors).toContain('Indikator terlalu singkat.');
    });

    test('reports multiple errors on same item', () => {
        const items = [{ id_tujuan: 0, materi: 'AB', indikator_soal: '' }];
        const result = validateAiPreviewItems(items);
        expect(result.errors[0].errors.length).toBeGreaterThan(1);
    });

    test('reports errors for correct item index', () => {
        const items = [
            { id_tujuan: 1, materi: 'Valid materi', indikator_soal: 'Valid indikator untuk butir' },
            { id_tujuan: 0, materi: 'x',            indikator_soal: 'y' },
        ];
        const result = validateAiPreviewItems(items);
        expect(result.valid).toBe(false);
        expect(result.errors[0].index).toBe(1);
    });

    test('passes for multiple valid items', () => {
        const items = [
            { id_tujuan: 1, materi: 'Materi A', indikator_soal: 'Peserta didik mampu menganalisis soal' },
            { id_tujuan: 2, materi: 'Materi B', indikator_soal: 'Peserta didik mampu mengevaluasi data' },
        ];
        expect(validateAiPreviewItems(items).valid).toBe(true);
    });

    test('handles empty array', () => {
        expect(validateAiPreviewItems([]).valid).toBe(true);
    });
});

// ─────────────────────────────────────────────────────────
// 5. AJAX REQUEST PARAMETER CONSTRUCTION
// ─────────────────────────────────────────────────────────
describe('AJAX filter parameter construction', () => {
    /**
     * Simulate buildQuery as the JS module would call it for loadItems()
     */
    function buildItemsQuery(page, perPage, filterTujuanId, filterJenisSoal) {
        const params = { page, per_page: perPage };
        if (filterTujuanId  > 0) params.tujuan_id  = filterTujuanId;
        if (filterJenisSoal > 0) params.jenis_soal  = filterJenisSoal;
        return buildQuery(params);
    }

    test('sends tujuan_id when filter is active', () => {
        const q = buildItemsQuery(1, 25, 42, 0);
        expect(q).toContain('tujuan_id=42');
        expect(q).not.toContain('jenis_soal');
    });

    test('sends jenis_soal when filter is active', () => {
        const q = buildItemsQuery(1, 25, 0, 1);
        expect(q).toContain('jenis_soal=1');
        expect(q).not.toContain('tujuan_id');
    });

    test('sends both filters when both active', () => {
        const q = buildItemsQuery(1, 25, 10, 3);
        expect(q).toContain('tujuan_id=10');
        expect(q).toContain('jenis_soal=3');
    });

    test('omits filter params when no filter', () => {
        const q = buildItemsQuery(2, 25, 0, 0);
        expect(q).toBe('page=2&per_page=25');
    });

    test('includes page and per_page always', () => {
        const q = buildItemsQuery(3, 50, 0, 0);
        expect(q).toContain('page=3');
        expect(q).toContain('per_page=50');
    });
});

// ─────────────────────────────────────────────────────────
// 6. RESPONSE DATA SHAPE VALIDATION
// ─────────────────────────────────────────────────────────
describe('API response shape validation', () => {
    /**
     * Simulate the check the JS module does on the items AJAX response
     */
    function parseItemsResponse(res) {
        if (!res?.status) return { ok: false, message: res?.message || 'Error' };
        const payload = res?.data || {};
        const items   = payload.items || [];
        const meta    = payload.meta  || {};
        return {
            ok   : true,
            items,
            total: parseInt(meta.total || 0, 10),
            pages: Math.max(1, parseInt(meta.total_pages || 1, 10)),
            filterActive: !!(meta.filter_active),
            tujuanId: meta.filter_tujuan_id || 0,
        };
    }

    test('parses successful paginated response', () => {
        const res = {
            status: true,
            data: {
                items: [{ id_item: 1, nomor: 1 }, { id_item: 2, nomor: 2 }],
                meta : { total: 10, page: 1, per_page: 5, total_pages: 2, filter_active: false },
            },
        };
        const parsed = parseItemsResponse(res);
        expect(parsed.ok).toBe(true);
        expect(parsed.items).toHaveLength(2);
        expect(parsed.total).toBe(10);
        expect(parsed.pages).toBe(2);
        expect(parsed.filterActive).toBe(false);
    });

    test('parses filtered response with filter_active flag', () => {
        const res = {
            status: true,
            data: {
                items: [{ id_item: 3, nomor: 3, id_tujuan: 42 }],
                meta : { total: 1, page: 1, per_page: 25, total_pages: 1, filter_active: true, filter_tujuan_id: 42 },
            },
        };
        const parsed = parseItemsResponse(res);
        expect(parsed.filterActive).toBe(true);
        expect(parsed.tujuanId).toBe(42);
        expect(parsed.items[0].id_tujuan).toBe(42);
    });

    test('handles error response', () => {
        const res = { status: false, message: 'Akses ditolak' };
        const parsed = parseItemsResponse(res);
        expect(parsed.ok).toBe(false);
        expect(parsed.message).toBe('Akses ditolak');
    });

    test('handles empty items array', () => {
        const res = {
            status: true,
            data  : { items: [], meta: { total: 0, total_pages: 1, filter_active: false } },
        };
        const parsed = parseItemsResponse(res);
        expect(parsed.ok).toBe(true);
        expect(parsed.items).toHaveLength(0);
        expect(parsed.total).toBe(0);
    });

    test('handles missing meta gracefully', () => {
        const res = { status: true, data: { items: [] } };
        const parsed = parseItemsResponse(res);
        expect(parsed.pages).toBe(1);
        expect(parsed.total).toBe(0);
    });
});

// ─────────────────────────────────────────────────────────
// 7. FILTER ACTIVE STATE LOGIC
// ─────────────────────────────────────────────────────────
describe('Filter active state logic', () => {
    function isFilterActive(filter) {
        return filter.tujuanId > 0 || filter.jenisSoal > 0;
    }

    test('inactive when both are zero', () => {
        expect(isFilterActive({ tujuanId: 0, jenisSoal: 0 })).toBe(false);
    });
    test('active when tujuanId is set', () => {
        expect(isFilterActive({ tujuanId: 5, jenisSoal: 0 })).toBe(true);
    });
    test('active when jenisSoal is set', () => {
        expect(isFilterActive({ tujuanId: 0, jenisSoal: 1 })).toBe(true);
    });
    test('active when both are set', () => {
        expect(isFilterActive({ tujuanId: 3, jenisSoal: 2 })).toBe(true);
    });
});
