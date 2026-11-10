var table;
var currentGroupedSchedules = [];

$(document).ready(async function () {
    ajaxcsrf();
    var opsiJenis = $("#jenis");
    var opsiTgl = $("#tanggal");
    var searchInput = $("#search-pengawas");
    var btnReset = $("#btn-reset-pengawas");
    var btnAuto = $("#btn-auto");
    var btnReload = $("#btn-reload");
    var btnSave = $("#btn-submit-save");

    // Inisialisasi awal
    opsiTgl.prepend("<option value=''>-- Pilih Tanggal Ujian --</option>");
    opsiJenis.val('').trigger('change');

    // Event Listeners
    opsiJenis.change(function () {
        opsiTgl.empty();
        opsiTgl.prepend("<option value=''>-- Pilih Tanggal Ujian --</option>");
        searchInput.val('').attr('disabled', true);
        btnReset.attr('disabled', true);
        btnAuto.attr('disabled', true);
        hideMainViews();

        if ($(this).val() !== '') {
            getTglJadwal();
        }
    });

    opsiTgl.change(function () {
        if ($(this).val() !== '') {
            getAllJadwal();
        } else {
            hideMainViews();
        }
    });

    btnReload.on('click', function () {
        if (opsiTgl.val() !== '') {
            getAllJadwal();
        } else {
            ShowToast('info', 'Pilih jenis penilaian dan tanggal ujian terlebih dahulu.');
        }
    });

    // Realtime Search Filter
    searchInput.on('keyup input', function () {
        const query = $(this).val().toLowerCase().trim();
        const $rows = $('#table-pengawas tbody tr');
        let visibleCount = 0;

        $rows.each(function () {
            const text = $(this).text().toLowerCase();
            if (!query || text.includes(query)) {
                $(this).removeClass('d-none');
                visibleCount++;
            } else {
                $(this).addClass('d-none');
            }
        });

        $('#table-row-count').text(visibleCount + ' Alokasi' + (query ? ' (filtered)' : ''));
    });

    // Reset All Pengawas Action
    btnReset.on('click', function () {
        if ($('.pengawas').length === 0) return;

        Swal.fire({
            title: "Kosongkan Pengawas?",
            text: "Semua pengawas yang terpilih di tabel ini akan dikosongkan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Kosongkan",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $('.pengawas').val([]).trigger('change');
                ShowToast('info', 'Semua pilihan pengawas dikosongkan. Klik Simpan untuk menyimpan.');
            }
        });
    });

    function hideMainViews() {
        $('#table-container').html('');
        $('#empty-state').removeClass('d-none');
        btnSave.attr('disabled', true);
        searchInput.val('').attr('disabled', true);
        btnReset.attr('disabled', true);
        btnAuto.attr('disabled', true);
        $('#table-row-count').text('0 Alokasi');
        $('#status-pengawas').html('<i class="bi bi-shield-check text-success me-1"></i>Belum ada alokasi pengawas');
    }

    function getTglJadwal() {
        var jenis = opsiJenis.val();
        var url = base_url + 'cbtpengawas/get_tanggal?jenis=' + jenis;
        var spinner = $('#tanggal-spinner');

        spinner.removeClass('d-none');
        $.ajax({
            url: url,
            type: "GET",
            success: function (respon) {
                spinner.addClass('d-none');
                opsiTgl.empty().append("<option value=''>-- Pilih Tanggal Ujian --</option>");
                for (const tgl in respon) {
                    const mulai = new Date(tgl).toLocaleDateString('id-ID', {
                        weekday: "long",
                        year: "numeric",
                        month: "short",
                        day: "2-digit",
                    });
                    opsiTgl.append(`<option value="${tgl}">${mulai}</option>`);
                }
            },
            error: function (xhr, status, error) {
                spinner.addClass('d-none');
                console.error("Error get_tanggal:", error);
            }
        });
    }

    function getAllJadwal() {
        var jenis = opsiJenis.val();
        var tanggal = opsiTgl.val();

        if (jenis === "" || tanggal === "") return;

        let formData = new FormData($('#savepengawas')[0]);
        formData.append('jenis', jenis);
        formData.append('tanggal', tanggal);

        Swal.fire({
            title: "Memuat Data Pengawas",
            text: "Silakan tunggu sebentar...",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: base_url + 'cbtpengawas/get_pengawas',
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: async function (respon) {
                await createTableFromJSON(respon);
                setTimeout(() => {
                    Swal.close();
                }, 300);
            },
            error: function (xhr, status, error) {
                console.log("Error get_pengawas:", xhr.responseText);
                Swal.fire({
                    title: "Gagal Memuat Data",
                    text: "Terjadi kesalahan saat memuat data pengawas.\n" + (xhr.statusText || error),
                    icon: "error"
                });
            }
        });
    }

    $('#savepengawas').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var jenis = opsiJenis.val();
        if (jenis === "") return;

        const origText = btnSave.html();
        btnSave.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Menyimpan...').attr('disabled', true);

        Swal.fire({
            title: "Menyimpan Pengawas Ujian",
            text: "Memproses alokasi pengawas ke sistem...",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const tables = $('#table-pengawas');
        var jsonObj = [];
        const $rows = tables.find('tbody tr');

        $rows.each((i, row) => {
            const tdMapel = $(row).find('.jadwal');
            if (tdMapel.length > 0) {
                const ruang = tdMapel.attr('data-ruang');
                const sesi = tdMapel.attr('data-sesi');
                const idJadwals = tdMapel.attr('data-id');
                const jadwals = idJadwals ? idJadwals.split(',') : [];
                const selectPengawas = $(row).find('.pengawas');
                const rawGuru = selectPengawas.val();
                const guru = Array.isArray(rawGuru) ? rawGuru : (rawGuru ? [rawGuru] : []);

                for (const jadwal of jadwals) {
                    if (jadwal) {
                        jsonObj.push({
                            "jadwal": jadwal,
                            "ruang": ruang,
                            "sesi": sesi,
                            "guru": guru
                        });
                    }
                }
            }
        });

        var dataPost = $(this).serialize() + "&jenis=" + encodeURIComponent(jenis) +
            "&tanggal=" + encodeURIComponent(opsiTgl.val() || '') +
            "&data=" + encodeURIComponent(JSON.stringify(jsonObj));

        setTimeout(() => {
            $.ajax({
                url: base_url + "cbtpengawas/savepengawas",
                type: "POST",
                dataType: "JSON",
                data: dataPost,
                success: function (data) {
                    btnSave.html(origText).removeAttr('disabled');
                    if (data.status) {
                        Swal.fire({
                            title: "Alokasi Pengawas Disimpan",
                            html: `<div class="text-secondary small">Data pengawas ujian berhasil diperbarui.</div>
                                   <div class="mt-3 p-3 bg-light rounded-3 d-flex justify-content-around">
                                       <div><span class="badge bg-success me-1">Baru</span> <b>${data.insert ? data.insert.length : 0}</b></div>
                                       <div><span class="badge bg-info me-1">Diupdate</span> <b>${data.update ? data.update.length : 0}</b></div>
                                       <div><span class="badge bg-danger me-1">Dihapus</span> <b>${data.delete ? data.delete : 0}</b></div>
                                   </div>`,
                            icon: "success",
                            confirmButtonColor: "#3b82f6",
                            confirmButtonText: "Selesai"
                        }).then(result => {
                            if (result.value) {
                                getAllJadwal();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Info Simpan",
                            text: data.message || "Tidak ada perubahan data pengawas yang disimpan.",
                            icon: "info"
                        });
                    }
                },
                error: function (xhr, status, error) {
                    btnSave.html(origText).removeAttr('disabled');
                    console.log("Error save:", xhr.responseText);
                    Swal.fire({
                        title: "Gagal Menyimpan",
                        text: "Terjadi kesalahan saat menyimpan data pengawas.",
                        icon: "error"
                    });
                }
            });
        }, 600);
    });

    $('#btn-auto').on('click', function (e) {
        e.preventDefault();

        Swal.fire({
            title: "Generate Otomatis Pengawas",
            text: "Sistem akan mengisi pengawas berdasarkan Guru Pengampu Mapel dan Panitia Ruang...",
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const tdp = $('.td-pengawas');
        let changesCount = 0;

        tdp.each((ind, td) => {
            const jadwal_guru = ($(td).data('guru') || '').toString();
            const panitia_guru = ($(td).data('panitia') || '').toString();

            const splited = jadwal_guru.split(',').filter(Boolean);
            const splitedPanitia = panitia_guru.split(',').filter(Boolean);

            const select = $(td).find('select.pengawas');
            let oldVal = select.val() || [];
            if (!Array.isArray(oldVal)) oldVal = [oldVal];

            let newVal = [...oldVal];
            let changed = false;

            for (const guru of splited) {
                if (guru && !newVal.includes(guru)) {
                    newVal.push(guru);
                    changed = true;
                }
            }

            for (const pg of splitedPanitia) {
                if (pg && !newVal.includes(pg)) {
                    newVal.push(pg);
                    changed = true;
                }
            }

            if (changed) {
                select.val(newVal).trigger('change');
                changesCount++;
            }
        });

        setTimeout(() => {
            Swal.close();
            if (changesCount > 0) {
                ShowToast('success', changesCount + ' alokasi ruang berhasil terisi otomatis.');
            } else {
                ShowToast('info', 'Semua pengawas sudah teralokasi.');
            }
        }, 400);
    });
});

/**
 * Main function to create and populate the exam schedule table from JSON data.
 */
async function createTableFromJSON(data) {
    const tableContainer = document.getElementById('table-container');
    const allSesi = data.ruangs || [];
    const jsonData = data.jadwals_ujian || [];

    if (!validateData(allSesi, jsonData, tableContainer)) {
        $('#btn-auto').attr('disabled', true);
        $('#btn-submit-save').attr('disabled', true);
        $('#empty-state').addClass('d-none');
        $('#table-row-count').text('0 Alokasi');
        $('#status-pengawas').html('<i class="bi bi-exclamation-triangle text-warning me-1"></i>Tidak ada jadwal penilaian pada filter ini');
        return;
    }

    // 1. Proses data menjadi daftar terkelompok
    currentGroupedSchedules = processDataToGroupedList(jsonData, allSesi, data);

    // 2. Urutkan daftar
    currentGroupedSchedules.sort((a, b) => {
        if (a.ruang_id !== b.ruang_id) return a.ruang_id - b.ruang_id;
        if (a.sesi_id !== b.sesi_id) return a.sesi_id - b.sesi_id;
        return a.jam_ke - b.jam_ke;
    });

    // 3. Hitung rowspan untuk Ruang dan Sesi
    const ruangRowCounts = calculateRowCounts(currentGroupedSchedules, 'ruang_id');
    const sesiRowCounts = calculateRowCounts(currentGroupedSchedules, 'ruang_id', 'sesi_id');

    // 4. Render tabel
    const tableHtml = buildTable(currentGroupedSchedules, data, ruangRowCounts, sesiRowCounts);
    tableContainer.innerHTML = tableHtml;

    // 5. Inisialisasi UI components & Stats
    initializeUI(currentGroupedSchedules);
}

function validateData(allSesi, jsonData, container) {
    if (allSesi.length === 0 || jsonData.length === 0) {
        container.innerHTML = `<div class="alert alert-warning border-0 shadow-sm text-center my-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2 text-warning"></i>
            <span class="fw-bold">Tidak ada jadwal penilaian untuk filter yang dipilih.</span>
        </div>`;
        return false;
    }
    return true;
}

function calculateRowCounts(items, ...keys) {
    const counts = new Map();
    let prevValues = null;
    let currentCount = 0;

    const getValues = (item) => keys.map(key => item?.[key]);

    [...items, null].forEach(item => {
        const currentValues = item ? getValues(item) : null;
        const isSameGroup = prevValues && currentValues &&
            prevValues.every((val, i) => val === currentValues[i]);

        if (!isSameGroup) {
            if (prevValues !== null) {
                const key = prevValues.join('-');
                counts.set(key, currentCount);
            }
            currentCount = 0;
        }

        if (item !== null) {
            currentCount++;
        }
        prevValues = currentValues;
    });

    return counts;
}

function processDataToGroupedList(jsonData, allSesi, data) {
    const grouped = {};
    const kelasMap = buildKelasMap(data);

    jsonData.forEach(jadwal => {
        jadwal.bank.bank_kelas = (jadwal.bank.bank_kelas || []).map(String);
        const ruangFiltered = allSesi.filter(r =>
            Array.isArray(r.kelas_id) && r.kelas_id.some(id => jadwal.bank.bank_kelas.includes(String(id)))
        );

        ruangFiltered.forEach(ruang => {
            const pengawasJadwal = data.pengawas && data.pengawas[jadwal.id_jadwal] && data.pengawas[jadwal.id_jadwal][ruang.ruang_id] && data.pengawas[jadwal.id_jadwal][ruang.ruang_id][ruang.sesi_id]
                ? data.pengawas[jadwal.id_jadwal][ruang.ruang_id][ruang.sesi_id]
                : {};

            // Jam ke mengikuti jadwal (sumber kebenaran), samakan dengan perhitungan server
            const jamJadwal = (jadwal.jam_ke && jadwal.jam_ke > 0) ? jadwal.jam_ke : 1;

            const key = `${ruang.ruang_id}-${ruang.sesi_id}-${jamJadwal}`;
            if (!grouped[key]) {
                grouped[key] = {
                    ruang_id: ruang.ruang_id,
                    nama_ruang: ruang.nama_ruang,
                    sesi_id: ruang.sesi_id,
                    nama_sesi: ruang.nama_sesi,
                    jam_ke: jamJadwal,
                    jadwals: [],
                    pengawas: (pengawasJadwal.id_guru || '').split(',').filter(Boolean),
                    panitiaIds: data.panitia_ruang && data.panitia_ruang[ruang.ruang_id] ? data.panitia_ruang[ruang.ruang_id].join(',') : '',
                    bank_guru_ids: []
                };
            }

            grouped[key].jadwals.push(jadwal);
            
            const guruIds = (jadwal.bank.bank_guru_id || '').split(',').filter(Boolean);
            guruIds.forEach(gid => {
                if (!grouped[key].bank_guru_ids.includes(gid)) {
                    grouped[key].bank_guru_ids.push(gid);
                }
            });

            // Gabungkan pengawas dari semua jadwal pada slot yang sama
            // (biar tampilan sama dengan yang akan disimpan)
            (pengawasJadwal.id_guru || '').split(',').filter(Boolean).forEach(pid => {
                if (!grouped[key].pengawas.includes(pid)) {
                    grouped[key].pengawas.push(pid);
                }
            });
        });
    });

    return Object.values(grouped);
}

function buildKelasMap(data) {
    const kelasMap = {};
    if (data && data.kelases) {
        if (Array.isArray(data.kelases)) {
            data.kelases.forEach(k => {
                if (k.id_kelas && k.nama_kelas) kelasMap[k.id_kelas] = k.nama_kelas;
                else kelasMap[k.id_kelas] = k.kode_kelas || k.nama_kelas;
            });
        } else {
            Object.assign(kelasMap, data.kelases);
        }
    }
    return kelasMap;
}

function buildTable(groupedSchedules, data, ruangRowCounts, sesiRowCounts) {
    const headerHtml = `
<thead class="table-light text-center">
    <tr>
        <th class="align-middle">TANGGAL</th>
        <th class="align-middle">RUANG</th>
        <th class="align-middle">SESI</th>
        <th class="align-middle" style="width: 60px;">JAM</th>
        <th class="align-middle">MAPEL / BANK SOAL</th>
        <th class="align-middle">KELAS TARGET</th>
        <th class="align-middle" style="width: 120px;">STATUS</th>
        <th class="align-middle" style="min-width: 320px;">PENGAWAS</th>
    </tr>
</thead>`;

    let bodyHtml = '<tbody id="table-body">';
    const usedRuangKeys = new Set();
    const usedSesiKeys = new Set();
    let currentRow = 0;

    const kelasMap = buildKelasMap(data);

    groupedSchedules.forEach((item, index) => {
        const { ruang_id, nama_ruang, sesi_id, nama_sesi, jam_ke, jadwals, pengawas, panitiaIds, bank_guru_ids } = item;
        const tgl = formatTanggal(jadwals[0].tgl_mulai);

        let mapelHtml = '';
        let kelasSet = new Set();
        let idsJadwalArr = [];

        jadwals.forEach(jad => {
            idsJadwalArr.push(jad.id_jadwal);
            mapelHtml += `
                <div class="mb-1">
                    <div class="fw-bold small">
                        <i class="bi bi-book text-primary me-1"></i>${escapeHtml(jad.bank.mapel.nama_mapel)}
                    </div>
                    <div class="mt-1">
                        <span class="badge bg-light border text-secondary fw-normal">${escapeHtml(jad.bank.bank_kode)}</span>
                    </div>
                </div>`;

            const ruangObj = data.ruangs.find(r => r.ruang_id === ruang_id && r.sesi_id === sesi_id);
            if (ruangObj) {
                ruangObj.kelas_id.forEach(kid => {
                    if (jad.bank.bank_kelas.includes(String(kid))) {
                        kelasSet.add(kelasMap[kid] || kid);
                    }
                });
            }
        });

        const bank_kelas = Array.from(kelasSet)
            .map(k => `<span class="badge bg-secondary">${escapeHtml(k)}</span>`)
            .join(' ');
        const idsJadwalStr = idsJadwalArr.join(',');
        const optionGuru = createPengawasSelect(data.gurus, pengawas, index);
        const isTerisi = pengawas.length > 0;
        const statusBadgeHtml = isTerisi
            ? `<span class="badge bg-success" id="status-badge-${index}"><i class="bi bi-check-circle-fill me-1"></i>Terisi (${pengawas.length})</span>`
            : `<span class="badge bg-warning text-dark" id="status-badge-${index}"><i class="bi bi-exclamation-circle-fill me-1"></i>Belum Terisi</span>`;

        bodyHtml += `<tr id="row-alokasi-${index}">`;

        if (currentRow === 0) {
            bodyHtml += `<td rowspan="${groupedSchedules.length}" class="text-center align-middle bg-white fw-bold small border-end">
                <span class="badge bg-primary"><i class="bi bi-calendar-event me-1"></i>${tgl}</span>
            </td>`;
        }

        const ruangKey = String(ruang_id);
        if (!usedRuangKeys.has(ruangKey)) {
            const rowspanValue = ruangRowCounts.get(ruangKey) || 1;
            bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-ruang">
                <span class="fw-bold small"><i class="bi bi-door-open text-primary me-1"></i>${escapeHtml(nama_ruang)}</span>
            </td>`;
            usedRuangKeys.add(ruangKey);
        }

        const sesiKey = `${ruang_id}-${sesi_id}`;
        if (!usedSesiKeys.has(sesiKey)) {
            const rowspanValue = sesiRowCounts.get(sesiKey) || 1;
            const displaySesi = nama_sesi
                ? `<span class="fw-semibold small text-secondary"><i class="bi bi-clock text-info me-1"></i>${escapeHtml(nama_sesi)}</span>`
                : '<span class="text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Belum diatur</span>';
            bodyHtml += `<td rowspan="${rowspanValue}" class="text-center align-middle td-sesi">${displaySesi}</td>`;
            usedSesiKeys.add(sesiKey);
        }

        bodyHtml += `
            <td class="text-center align-middle td-jam">
                <span class="badge bg-light border text-dark">${jam_ke}</span>
            </td>
            <td class="align-middle td-mapel jadwal" data-ruang="${ruang_id}" data-sesi="${sesi_id}" data-id="${idsJadwalStr}">
                ${mapelHtml}
            </td>
            <td class="text-center align-middle">${bank_kelas || '-'}</td>
            <td class="text-center align-middle td-status">${statusBadgeHtml}</td>
            <td class="td-pengawas" data-guru="${bank_guru_ids.join(',')}" data-panitia="${panitiaIds}" data-row-index="${index}">
                ${optionGuru}
            </td>
        </tr>`;

        currentRow++;
    });

    bodyHtml += '</tbody>';
    return `<table class="table table-bordered table-sm table-striped align-middle w-100 mb-0" id="table-pengawas">${headerHtml}${bodyHtml}</table>`;
}

function initializeUI(schedules) {
    $('#empty-state').addClass('d-none');
    $('#search-pengawas').removeAttr('disabled');
    $('#btn-reset-pengawas').removeAttr('disabled');
    $('#btn-submit-save').removeAttr('disabled');

    if (schedules.length > 0) {
        $('#btn-auto').removeAttr('disabled');
    }

    // Init Select2 dengan bootstrap-5 theme
    $('.select2').select2({ width: '100%', theme: 'bootstrap-5' });

    // Listen to changes on pengawas selects to update stats & row badges dynamically
    $('.pengawas').off('change.updateStats').on('change.updateStats', function () {
        const tdParent = $(this).closest('.td-pengawas');
        const rowIndex = tdParent.data('row-index');
        const selectedVal = $(this).val() || [];
        const count = selectedVal.length;

        const badgeElem = $(`#status-badge-${rowIndex}`);
        if (count > 0) {
            badgeElem.attr('class', 'badge bg-success')
                .html(`<i class="bi bi-check-circle-fill me-1"></i>Terisi (${count})`);
        } else {
            badgeElem.attr('class', 'badge bg-warning text-dark')
                .html(`<i class="bi bi-exclamation-circle-fill me-1"></i>Belum Terisi`);
        }

        updateLiveStats();
    });

    updateLiveStats();
}

function updateLiveStats() {
    const totalRuang = $('#table-pengawas tbody tr').length;
    let terisiCount = 0;

    $('.pengawas').each(function () {
        const val = $(this).val();
        if (val && val.length > 0) {
            terisiCount++;
        }
    });

    const kosongCount = Math.max(0, totalRuang - terisiCount);

    $('#table-row-count').text(totalRuang + ' Alokasi');

    if (totalRuang === 0) {
        $('#status-pengawas').html('<i class="bi bi-shield-check text-success me-1"></i>Belum ada alokasi pengawas');
        return;
    }

    const pesan = kosongCount > 0
        ? `${terisiCount} dari ${totalRuang} alokasi sudah terisi pengawas, ${kosongCount} alokasi belum terisi`
        : `Semua ${totalRuang} alokasi sudah terisi pengawas`;

    $('#status-pengawas').html(`<i class="bi bi-shield-check text-success me-1"></i>${pesan}`);
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function createPengawasSelect(gurus, selectedPengawas, rowIndex) {
    let options = '';
    for (const id_guru in gurus) {
        if (id_guru !== '0' && gurus[id_guru]) {
            const isSelected = selectedPengawas.includes(String(id_guru)) ? 'selected' : '';
            options += `<option value="${escapeHtml(id_guru)}" ${isSelected}>${escapeHtml(gurus[id_guru])}</option>`;
        }
    }
    return `<select name="pengawas[]" class="select2 form-control form-control-sm pengawas" multiple="multiple" data-placeholder="Pilih pengawas..." data-row-index="${rowIndex}">${options}</select>`;
}

function formatTanggal(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

function ShowToast(type, msg) {
    if (typeof showToast === 'function') {
        showToast(type, msg);
    } else {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        Toast.fire({
            icon: type,
            title: msg
        });
    }
}
