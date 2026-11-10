$(document).ready(function () {
    const state = window.KARTU_SOAL || {};
    const $kartuForm = $('#kartuForm');
    const $btnSave = $('#btnSave');
    const $saveSpinner = $('#saveSpinner');
    const $saveText = $('#saveText');
    const $mapel = $('#mapel_id');
    const $jenis = $('#id_jenis');
    const $tp = $('#tp_id');
    const $smt = $('#smt_id');
    const $judul = $('#judul');
    const $selKelas = $('#select-kelas-kartu');
    const $btnAutoJudul = $('#btnAutoJudul');

    if (!$kartuForm.length) {
        return;
    }

    let userHasEditedTitle = state.isEdit && !!($judul.val() || '').trim();

    $judul.on('input', function () {
        userHasEditedTitle = !!$(this).val().trim();
    });

    /** Generate automatic title based on form selections (singkat/abbreviated) */
    function generateAutoTitle() {
        const $mapelOpt = $mapel.find('option:selected');
        const mapelText = $mapelOpt.val() && !$mapelOpt.text().startsWith('Pilih') ? $mapelOpt.text().trim() : '';
        const mapelKode = $mapelOpt.data('kode') || '';
        const $jenisOpt = $jenis.find('option:selected');
        const jenisText = $jenisOpt.val() && !$jenisOpt.text().startsWith('Pilih') ? $jenisOpt.text().trim() : '';
        const jenisKode = $jenisOpt.data('kode') || '';
        const tpText = $tp.val() && $tp.find('option:selected').text() ? $tp.find('option:selected').text().trim() : '';
        const smtText = $smt.val() && $smt.find('option:selected').text() ? $smt.find('option:selected').text().trim() : '';

        let kelasArr = [];
        let levels = [];
        if ($selKelas.length) {
            $selKelas.find('option:selected').each(function () {
                const $o = $(this);
                let txt = $o.text().trim();
                txt = txt.replace(/\s*\(\d+\)$/, '').trim();
                const lvl = $o.data('level') || '';
                const rom = romanTingkat(lvl);
                if (txt && !txt.startsWith('Pilih')) {
                    kelasArr.push(rom || txt);
                    if (rom) levels.push(rom);
                }
            });
        }

        let kelasStr = '';
        if (kelasArr.length > 0) {
            const dedupLevels = [...new Set(levels)];
            if (dedupLevels.length > 0) {
                kelasStr = 'Kelas ' + dedupLevels.join(', ');
            } else {
                kelasStr = 'Kelas ' + [...new Set(kelasArr)].join(', ');
            }
        }

        let smtLabel = '';
        if (smtText && !smtText.startsWith('Pilih')) {
            if (/I\b|1|ganjil/i.test(smtText) && !/II\b|2|genap/i.test(smtText)) {
                smtLabel = 'Ganjil';
            } else if (/II\b|2|genap/i.test(smtText)) {
                smtLabel = 'Genap';
            } else {
                smtLabel = smtText;
            }
        }

        // Tahun singkat 2 digit, misal "2026/2027" -> "26/27"
        let tpShort = '';
        if (tpText && !tpText.startsWith('Pilih')) {
            const m = tpText.match(/(\d{4})\s*\/\s*(\d{4})/);
            tpShort = m ? (m[1].slice(-2) + '/' + m[2].slice(-2)) : tpText;
        }

        let parts = ['Kartu Soal'];
        if (jenisKode) parts.push(jenisKode);        // singkatan jenis penilaian: PH, ATS, SAS…
        else if (jenisText) parts.push(jenisText);
        if (mapelKode) parts.push(mapelKode);        // singkatan nama mapel: MTK, BIO, IPA…
        else if (mapelText && !mapelText.startsWith('Pilih')) parts.push(mapelText);
        if (kelasStr) parts.push(kelasStr);

        let tpSmtPart = [];
        if (smtLabel) tpSmtPart.push(smtLabel);
        if (tpShort) tpSmtPart.push(tpShort);
        if (tpSmtPart.length > 0) {
            parts.push('(' + tpSmtPart.join(' ') + ')');
        }

        return parts.length > 1 ? parts.join(' ') : '';
    }

    /** Konversi id_level ke romawi: 10 -> X, 11 -> XI, 12 -> XII */
    function romanTingkat(levelId) {
        const map = { '10': 'X', '11': 'XI', '12': 'XII' };
        return map[String(levelId)] || '';
    }

    function tryAutoFillTitle(force = false) {
        if (state.isEdit && !force) return;
        if (userHasEditedTitle && !force) return;

        const autoTitle = generateAutoTitle();
        if (autoTitle) {
            $judul.val(autoTitle);
            $judul.removeClass('is-invalid');
            $('#judulError').text('');
        }
    }

    // Auto-update title when form selections change
    $mapel.add($jenis).add($tp).add($smt).on('change', function () {
        tryAutoFillTitle();
    });

    $selKelas.on('change', function () {
        tryAutoFillTitle();
    });

    $btnAutoJudul.on('click', function () {
        userHasEditedTitle = false;
        tryAutoFillTitle(true);
    });

    // Auto-fill title on initial load if creating new and title is empty
    if (!state.isEdit && !$judul.val().trim()) {
        setTimeout(function () {
            tryAutoFillTitle();
        }, 250);
    }

    function swalError(message) {
        Swal.fire({ title: 'Gagal', text: message || 'Terjadi kesalahan.', icon: 'error' });
    }

    /** Read selected kelas IDs from Select2 multi-select */
    function getSelectedKelasIds() {
        return $selKelas.length ? ($selKelas.val() || []) : [];
    }

    function showValidationErrors(errors) {
        $('#judulError').text(errors?.judul || '');
        $('#mapelError').text(errors?.mapel_id || '');

        const kelasMsg = errors?.kelas_id || errors?.kelas_ids || '';
        const $kelasErr = $('#kelasError');
        $kelasErr.text(kelasMsg).toggle(!!kelasMsg);

        $('#jenisError').text(errors?.id_jenis || '');
        $('#tpError').text(errors?.tp_id    || '');
        $('#smtError').text(errors?.smt_id  || '');

        $('#judul').toggleClass('is-invalid', !!errors?.judul);
        $('#mapel_id').toggleClass('is-invalid', !!errors?.mapel_id);
        $('#id_jenis').toggleClass('is-invalid', !!errors?.id_jenis);
        $('#tp_id').toggleClass('is-invalid', !!errors?.tp_id);
        $('#smt_id').toggleClass('is-invalid', !!errors?.smt_id);
    }

    function setSaveLoading(loading) {
        if (loading) {
            $btnSave.prop('disabled', true);
            $saveSpinner.removeClass('d-none');
            $saveText.text('Menyimpan...');
            return;
        }
        $btnSave.prop('disabled', false);
        $saveSpinner.addClass('d-none');
        $saveText.text(state.isEdit ? 'Simpan Perubahan' : 'Simpan dan Lanjutkan');
    }

    $kartuForm.on('submit', function (e) {
        e.preventDefault();
        setSaveLoading(true);
        showValidationErrors({});

        const kelasIds = getSelectedKelasIds();
        if (!kelasIds.length) {
            showValidationErrors({ kelas_id: 'Pilih minimal satu kelas.' });
            swalError('Pilih minimal satu kelas terlebih dahulu.');
            setSaveLoading(false);
            return;
        }

        // Use FormData so kelas_ids[] array is properly serialized
        const fd = new FormData();
        fd.append(CSRF_NAME, CSRF_HASH);
        fd.append('uuid',      $('#uuid').val() || '');
        fd.append('judul',     $('#judul').val() || '');
        fd.append('deskripsi', $('#deskripsi').val() || '');
        fd.append('mapel_id',  $mapel.val() || '');
        fd.append('id_jenis',  $jenis.val() || '');
        fd.append('tp_id',     $tp.val() || '');
        fd.append('smt_id',    $smt.val() || '');
        
        const $createdBy = $('#created_by');
        if ($createdBy.length) {
            fd.append('created_by', $createdBy.val() || '');
        }
        
        if (kelasIds.length) {
            kelasIds.forEach(function (kid) { fd.append('kelas_ids[]', kid); });
        }

        $.ajax({
            url: base_url + 'cbtkartusoal/save',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false
        })
            .done(function (res) {
                if (!res?.status) {
                    showValidationErrors(res?.errors || {});
                    swalError(res?.message || 'Gagal menyimpan kartu.');
                    return;
                }

                const uuid      = res?.data?.uuid || $('#uuid').val();
                const detailUrl = uuid ? (base_url + 'cbtkartusoal/detail/' + uuid) : null;
                const isCreate  = !!(res?.data?.is_create);

                if (isCreate) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Data master kartu soal tersimpan. Lanjut kelola butir soal.',
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(function () {
                        if (detailUrl) window.location.href = detailUrl;
                        else location.reload();
                    });
                    return;
                }

                Swal.fire({
                    title: 'Perubahan Tersimpan',
                    text: res?.message || 'Data master kartu soal berhasil diperbarui.',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Kelola Butir Soal',
                    cancelButtonText: 'Tetap di Halaman Ini',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed && detailUrl) window.location.href = detailUrl;
                });
            })
            .fail(function (xhr) {
                const errMsg    = xhr?.responseJSON?.message || 'Gangguan koneksi ke server.';
                const errDetail = xhr?.responseJSON?.errors  || {};
                showValidationErrors(errDetail);
                swalError(errMsg);
            })
            .always(function () {
                setSaveLoading(false);
            });
    });

    $('#btnDelete').on('click', function () {
        if (!state.uuid) return;
        Swal.fire({
            title: 'Hapus kartu soal?',
            text: 'Kartu dan seluruh item di dalamnya akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.post(base_url + 'cbtkartusoal/delete', {
                [CSRF_NAME]: CSRF_HASH,
                uuid: state.uuid
            })
                .done(function (res) {
                    if (!res?.status) { swalError(res?.message || 'Gagal menghapus kartu.'); return; }
                    window.location.href = base_url + 'cbtkartusoal';
                })
                .fail(function (xhr) {
                    swalError(xhr?.responseJSON?.message || 'Gangguan koneksi ke server.');
                });
        });
    });
});
